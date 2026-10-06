// v56.1: HTML stránky se NEcachují (sdílené školní počítače) – offline jen statické assety.
// v58: offline trénink labu (OPS-01) – jediná cachovaná stránka je statická lab-offline.html bez PHP a osobních dat;
//      assety s verzí podle filemtime (asset_url, DAT-05): nová verze nahradí starou položku téže cesty.
// v61: verze cache zvýšena – přednačtení klíčových assetů podle ?view=precache (skutečné URL s ?v=<čas změny>).
// v68: verze cache zvýšena (nový rámec cockpitu, tmavý režim, vyřazené pohledy); HTML se dál necachuje.
const CACHE="educanet-v68-ui";
const SHELL=["assets/app.css?v=46","assets/mastery.css?v=46","assets/cognitive-v43.css?v=46","assets/learning-studio-v44.css?v=46","assets/visual-simulation-v45.css?v=46","assets/student-coach-v47.css?v=47.2","assets/visual-practical-v48.css?v=48","assets/visual-labs-3a-v48-1.css?v=48.1","assets/app.js?v=46","assets/cognitive-v43.js?v=46","assets/learning-studio-v44.js?v=46","assets/visual-simulation-v45.js?v=46","assets/student-coach-v47.js?v=47.2","assets/visual-practical-v48.js?v=48","assets/visual-labs-3a-v48-1.js?v=48.1","assets/hands-on-v50.css?v=50","assets/hands-on-v50.js?v=50","assets/student-ui-v50-7-7.css?v=51.0","assets/student-ui-v50-7-7.js?v=51.0","assets/ui-v51.css?v=51.0","assets/ui-v51.js?v=51.0","assets/tutorial-v52.css?v=52.0","assets/tutorial-v52.js?v=54.1","assets/session-v53.css?v=53.0","assets/brand-v54.css?v=54.1","assets/student-v55.css?v=55.1","assets/student-v55.js?v=55.1","assets/learning-v56.css?v=56.1","assets/learning-v56.js?v=56.1","assets/auth-v54.js?v=54.0","assets/vendor/gsap-3.15.0/gsap.min.js","assets/vendor/gsap-3.15.0/Flip.min.js","assets/vendor/gsap-3.15.0/TextPlugin.min.js","assets/one-task-v50-5.css?v=50.5","assets/one-task-v50-7-7.css?v=50.7.7","assets/one-task-v50-5.js?v=50.5","manifest.webmanifest","assets/icon-192.png","assets/icon-512.png"];
// v58 OPS-01: offline pískoviště Linux Labu (bez PHP, bez přihlášení, bez osobních dat).
const OFFLINE_LAB=["lab-offline.html","assets/lab-offline-v58.css?v=58.0","assets/lab-offline-v58.js?v=58.0","assets/linux-core-v58.js?v=58.0","assets/linux-core-cmds-v58.js?v=58.0","assets/lab-manual-v58.json?v=58.0","assets/lab-offline-i18n-v59.json?v=59.0"];
const OFFLINE_HTML='<!doctype html><html lang="cs"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>EDUCANET · offline</title><body style="font-family:system-ui,sans-serif;padding:32px;color:#12212b"><div lang="cs"><h1>Jsi offline</h1><p>EDUCANET potřebuje připojení k internetu. Zkontroluj síť a stránku obnov.</p><p><a href="lab-offline.html">Offline trénink Linuxu</a> funguje i bez sítě (postup se neukládá).</p></div><div lang="en"><h1>You are offline</h1><p>EDUCANET needs an internet connection. Check your network and reload the page.</p><p><a href="lab-offline.html">Offline Linux practice</a> works even without a network (progress is not saved).</p></div><div lang="uk"><h1>Ти офлайн</h1><p>EDUCANET потребує підключення до інтернету. Перевір мережу і онови сторінку.</p><p><a href="lab-offline.html">Офлайн-тренування Linux</a> працює навіть без мережі (прогрес не зберігається).</p></div></body></html>';
const offlineResponse=()=>new Response(OFFLINE_HTML,{status:503,headers:{'Content-Type':'text/html; charset=utf-8','Cache-Control':'no-store'}});
// v61: klíčové assety (CSS/JS všech stránek žáka) se přednačtou přesně pod URL, které stránky žádají (asset_url() = ?v=<čas změny>).
// Seznam dodává index.php?view=precache (veřejný, neosobní JSON). Když není dostupný (offline při instalaci, vynucená změna hesla),
// použije se statický SHELL. HTML ani data žáka se nepředčítají; chyba jednoho assetu instalaci neshodí, jen offline lab je povinný.
const PRECACHE_URL=/^assets\/[A-Za-z0-9_.\/-]+\.(css|js)\?v=[A-Za-z0-9._-]+$/;
const precacheList=()=>fetch('index.php?view=precache',{credentials:'same-origin',cache:'no-store'})
  .then(r=>r.ok&&(r.headers.get('content-type')||'').includes('json')?r.json():null)
  .then(j=>Array.isArray(j&&j.urls)?j.urls.filter(u=>typeof u==='string'&&PRECACHE_URL.test(u)):[])
  .catch(()=>[]);
const installAll=c=>precacheList().then(urls=>{
  const rest=SHELL.filter(u=>!PRECACHE_URL.test(u));
  const fixed=c.addAll(rest.concat(OFFLINE_LAB));
  const dynamic=urls.length?urls:SHELL.filter(u=>PRECACHE_URL.test(u));
  return fixed.then(()=>Promise.all(dynamic.map(u=>c.add(u).catch(()=>null))));
});
self.addEventListener('install',e=>e.waitUntil(caches.open(CACHE).then(installAll).then(()=>self.skipWaiting())));
self.addEventListener('activate',e=>e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim())));
self.addEventListener('fetch',e=>{
  if(e.request.method!=='GET') return;
  const u=new URL(e.request.url);
  if(u.origin!==location.origin) return;
  if(u.pathname.endsWith('/teacher.php') || u.pathname.includes('/progress.php')) return;
  // Statika: cache-first podle celé URL; nová verze (?v=filemtime) smaže starší položky téže cesty; offline záloha bez ohledu na verzi.
  if(u.pathname.endsWith('/lab-offline.html') || u.pathname.includes('/assets/') || u.pathname.endsWith('/manifest.webmanifest')) {
    e.respondWith(caches.open(CACHE).then(c=>c.match(e.request).then(hit=>hit||fetch(e.request).then(resp=>{
      if(resp.ok){const clone=resp.clone();c.keys().then(keys=>Promise.all(keys.filter(k=>{const ku=new URL(k.url);return ku.pathname===u.pathname&&ku.search!==u.search;}).map(k=>c.delete(k)))).then(()=>c.put(e.request,clone));}
      return resp;
    }).catch(()=>c.match(e.request,{ignoreSearch:true}).then(r=>r||Response.error())))));
    return;
  }
  // Navigace a HTML: jen síť, nic se neukládá. Offline: na lab nabídneme offline trénink, jinak obecnou stránku bez cizích dat.
  if(e.request.mode==='navigate' || (e.request.headers.get('accept')||'').includes('text/html')){
    const isLabRoute=(u.pathname.endsWith('/')||u.pathname.endsWith('/index.php'))&&/(^|[?&])view=lab(&|$)/.test(u.search);
    e.respondWith(fetch(e.request).catch(()=>isLabRoute
      ? caches.match(new URL('lab-offline.html',self.registration.scope).href).then(r=>r||offlineResponse())
      : offlineResponse()));
    return;
  }
});
