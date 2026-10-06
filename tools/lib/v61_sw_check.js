'use strict';
// EDUCANET v61 · kontrola service workeru v Node (bez prohlížeče): načte sw.js do izolovaného kontextu s atrapami
// self/caches/fetch a ověří chování (přednačtení, necachování HTML a POST, offline stránka labu).
// Použití: node tools/lib/v61_sw_check.js <cesta k sw.js>   → vypíše JSON {"checks":[{"name":…,"ok":…}]}
const fs = require('fs');
const vm = require('vm');

const swPath = process.argv[2];
const source = fs.readFileSync(swPath, 'utf8');
const listeners = {};
const store = new Map();          // cache name -> Map(url -> response)
const puts = [];
const fetched = [];
const ORIGIN = 'https://school.example';

function makeResponse(body, init) {
  init = init || {};
  const headers = new Map(Object.entries(init.headers || {}).map(([k, v]) => [k.toLowerCase(), v]));
  return { ok: init.status === undefined || (init.status >= 200 && init.status < 300), status: init.status || 200, body,
    headers: { get: (k) => headers.get(String(k).toLowerCase()) || null },
    json: async () => JSON.parse(body), clone() { return makeResponse(body, init); } };
}
function absolute(u) { return new URL(typeof u === 'string' ? u : u.url, ORIGIN + '/').href; }

const cacheApi = (name) => {
  if (!store.has(name)) store.set(name, new Map());
  const m = store.get(name);
  return {
    add: async (u) => { const url = absolute(u); const r = await fetchMock(url); if (!r.ok) throw new Error('add ' + url); m.set(url, r); puts.push(url); },
    addAll: async (list) => { for (const u of list) { const url = absolute(u); const r = await fetchMock(url); if (!r.ok) throw new Error('addAll ' + url); m.set(url, r); puts.push(url); } },
    put: async (req, resp) => { m.set(absolute(req), resp); puts.push(absolute(req)); },
    match: async (req, opts) => {
      const url = absolute(req);
      if (m.has(url)) return m.get(url);
      if (opts && opts.ignoreSearch) { const p = new URL(url).pathname; for (const [k, v] of m) if (new URL(k).pathname === p) return v; }
      return undefined;
    },
    keys: async () => Array.from(m.keys()).map((k) => ({ url: k })),
    delete: async (k) => m.delete(typeof k === 'string' ? k : k.url),
  };
};

let precacheBody = JSON.stringify({ version: 1, urls: ['assets/app.css?v=1790000001', 'assets/app.js?v=1790000002', 'https://evil.example/x.js?v=1', 'assets/../../etc/passwd?v=1'] });
let precacheOk = true;
let offline = false;
async function fetchMock(input) {
  const url = absolute(input);
  fetched.push(url);
  if (offline) throw new TypeError('offline');
  if (url.includes('view=precache')) return precacheOk ? makeResponse(precacheBody, { headers: { 'Content-Type': 'application/json' } }) : makeResponse('x', { status: 503 });
  return makeResponse('asset:' + url, { headers: { 'Content-Type': 'text/plain' } });
}

const sandbox = {
  URL, Response: function (body, init) { return makeResponse(body, init); }, Promise, console,
  location: new URL(ORIGIN + '/sw.js'),
  caches: { match: async (req) => { for (const n of store.keys()) { const r = await cacheApi(n).match(req); if (r) return r; } return undefined; }, open: async (n) => cacheApi(n), keys: async () => Array.from(store.keys()), delete: async (n) => store.delete(n) },
  fetch: fetchMock,
};
sandbox.Response.error = () => makeResponse('error', { status: 0 });
sandbox.self = { addEventListener: (t, fn) => { listeners[t] = fn; }, registration: { scope: ORIGIN + '/' }, skipWaiting: async () => {}, clients: { claim: async () => {} } };
Object.assign(sandbox, { self: sandbox.self });
vm.createContext(sandbox);
vm.runInContext(source, sandbox);

const checks = [];
const ok = (name, cond) => checks.push({ name, ok: !!cond });

function dispatch(type, ev) {
  let promise = null;
  ev.waitUntil = (p) => { promise = p; };
  ev.respondWith = (p) => { ev._response = Promise.resolve(p); };
  listeners[type](ev);
  return promise || ev._response || Promise.resolve();
}
const req = (url, method, extra) => Object.assign({ url: absolute(url), method: method || 'GET', mode: 'cors', headers: { get: () => '' } }, extra || {});

(async () => {
  ok('sw: cache má verzi v61 nebo novější (v69)', /educanet-v(61|68|69)/.test(source));
  await dispatch('install', {});
  const cacheName = Array.from(store.keys())[0];
  const cached = Array.from(store.get(cacheName).keys());
  ok('install: přednačte verzované URL z ?view=precache', cached.some((u) => u.endsWith('assets/app.css?v=1790000001')) && cached.some((u) => u.endsWith('assets/app.js?v=1790000002')));
  ok('install: cizí a podezřelé URL ze seznamu se nepředčítají', !cached.some((u) => u.includes('evil.example') || u.includes('passwd')));
  ok('install: offline stránka labu a její assety jsou v cache', cached.some((u) => u.endsWith('/lab-offline.html')) && cached.some((u) => u.includes('lab-offline-v58.js')));
  ok('install: žádná HTML stránka aplikace se nepředčítá', !cached.some((u) => /index\.php|teacher\.php|progress\.php/.test(u) && !u.includes('view=precache')) && !cached.some((u) => u.includes('view=precache')));

  // Záloha: seznam nedostupný → statický SHELL, instalace nespadne.
  store.clear(); puts.length = 0; precacheOk = false;
  let failed = false;
  try { await dispatch('install', {}); } catch (e) { failed = true; }
  ok('install: bez ?view=precache použije statický SHELL a nespadne', !failed && Array.from(store.get(Array.from(store.keys())[0]).keys()).some((u) => u.includes('assets/app.css')));
  precacheOk = true;

  // Fetch: POST a HTML se necachují, asset ano, offline navigace na lab dává lab-offline.html.
  puts.length = 0; store.clear(); await dispatch('install', {}); puts.length = 0;
  const post = { request: req('/index.php?view=dashboard', 'POST') };
  await dispatch('fetch', post);
  ok('fetch: POST se nezachytává (service worker nevolá respondWith)', post._response === undefined);
  const nav = { request: req('/index.php?view=dashboard', 'GET', { mode: 'navigate' }) };
  await dispatch('fetch', nav); const navResp = await nav._response;
  ok('fetch: navigace (HTML se žákovskými daty) se do cache neukládá', navResp && !puts.some((u) => u.includes('view=dashboard')));
  const teacher = { request: req('/teacher.php?tab=overview', 'GET', { mode: 'navigate' }) };
  await dispatch('fetch', teacher);
  ok('fetch: učitelské stránky service worker vůbec nezachytává', teacher._response === undefined);
  const asset = { request: req('/assets/new.css?v=1790000009') };
  await dispatch('fetch', asset); await asset._response;
  ok('fetch: asset s ?v= se uloží (cache-first)', puts.some((u) => u.endsWith('assets/new.css?v=1790000009')));
  offline = true;
  const labNav = { request: req('/index.php?view=lab', 'GET', { mode: 'navigate' }) };
  await dispatch('fetch', labNav); const labResp = await labNav._response;
  ok('offline: navigace na lab vrací lab-offline.html z cache', labResp && String(labResp.body).includes('lab-offline.html'));
  const otherNav = { request: req('/index.php?view=dashboard', 'GET', { mode: 'navigate' }) };
  await dispatch('fetch', otherNav); const otherResp = await otherNav._response;
  ok('offline: ostatní stránky dostanou obecnou offline stránku (503, bez dat)', otherResp && otherResp.status === 503 && String(otherResp.body).includes('offline'));
  console.log(JSON.stringify({ checks }));
})().catch((e) => { console.log(JSON.stringify({ checks, error: String(e && e.stack || e) })); process.exit(1); });
