/* EDUCANET v79.1 · prohlížečová část tools/v79_1_content_audit.php (Chrome přes playwright-core, dočasné úložiště auditu, fiktivní data).
   6 stránek (Dnešní hodina, Přehled třídy, Schvalování, Výsledky třídy, žák Úlohy a Portfolio) × 11 šířek × světlý/tmavý: vodorovný posun,
   cíle ≥ 44 px a písmo ≥ 14 px v obsahu v79.1, kontextový panel na xl, sbalitelné sekce pod 768 px, tabulka → karty / lepicí hlavička,
   tmavé Úlohy a Portfolio. Paměť sbalení (localStorage) i stav bez úložiště. Volitelně snímky 375/768/1280/1920 × světlý/tmavý (JPEG ≤ 150 kB).
   Použití: node v79_1_content_browser.cjs <base-url> <klíč-učitele> <jméno-žáka-3A> [adresář-snímků]. Výstup: JSON na stdout. */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const BASE = process.argv[2], KEY = process.argv[3], STUDENT = process.argv[4] || '', SHOTS = process.argv[5] || '';
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const WIDTHS = [[320, 800], [375, 812], [390, 844], [414, 896], [768, 1024], [834, 1112], [1024, 768], [1280, 800], [1366, 600], [1440, 900], [1920, 1080]];
const PAGES = {
  hodina: ['t', '/teacher.php?tab=hodina&class=class_3a', '.lt791 .c70-table'],
  prehled: ['t', '/teacher.php?tab=prehled&class=class_3a', '.dt791-table'],
  schvalovani: ['t', '/teacher.php?tab=schvalovani&class=class_3a', '.dt791-table'],
  vysledky: ['t', '/teacher.php?tab=class_results&class=class_3a', '.class-results-table'],
  ulohy: ['s', '/?view=ulohy&uloha=net_ports', ''],
  portfolio: ['s', '/?view=portfolio', ''],
};
const SHOT_PAGES = { hodina: ['hodina', ''], prehled: ['tabulka-prehled', '#ov61-zaci'], schvalovani: ['tabulka-schvalovani', '#lc72-tab'], vysledky: ['tabulka-vysledky', '#class-results-table'], ulohy: ['zak-ulohy', ''] };
const SHOT_SIZES = [[375, 812], [768, 1024], [1280, 800], [1920, 1080]];

function probe(tableSel) {
  const W = document.documentElement.clientWidth;
  const vis = (el) => { const s = getComputedStyle(el); const r = el.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 1 && r.height > 1 && !el.closest('[hidden]'); };
  const targets = '.lt791-hero a, .lt791-hero button, .lt791-hero select, .lt791-tg, .lt791-t14 a, .dt791 a, .dt791 button, .dt791 input:not([type=hidden]), .ux791-empty a, '
    + '.class-results-table th a, .result-row-actions a, .dt791-pager-only a, .in74-card a.btn, .l75-empty a';
  const small = [];
  for (const el of document.querySelectorAll(targets)) {
    if (!vis(el)) continue;
    const r = el.getBoundingClientRect();
    if (r.height < 43.5 || r.width < 43.5) small.push((el.textContent || el.className || el.tagName).trim().slice(0, 24) + ':' + Math.round(r.width) + 'x' + Math.round(r.height));
  }
  const tiny = [];
  for (const root of document.querySelectorAll('.lt791, .dt791, .ux791-empty, .class-results-table, .ind791, .l75-empty')) {
    for (const el of [root, ...root.querySelectorAll('*')]) {
      if (el.closest('.ds-sr, .sr-only, .c70-sr') || !vis(el)) continue;
      const own = [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim() !== '');
      if (!own) continue;
      const fs = parseFloat(getComputedStyle(el).fontSize);
      if (fs < 13.95) tiny.push((el.className || el.tagName) + ':' + fs);
    }
  }
  const ctx = document.querySelector('.lt791-ctx'), plan = document.querySelector('[data-lt791-sec="plan"]');
  let ctxRight = null;
  if (ctx && plan) { const a = ctx.getBoundingClientRect(), b = plan.getBoundingClientRect(); ctxRight = a.left >= b.right - 1 && Math.abs(a.top - b.top) < 40; }
  let table = null;
  const t = tableSel ? document.querySelector(tableSel) : null;
  if (t) {
    const tr = t.querySelector('tbody tr');
    const first = tr && tr.firstElementChild;
    const th = t.querySelector('thead th');
    table = { cards: !!tr && getComputedStyle(tr).display === 'block', stickyHead: !!th && getComputedStyle(th).position === 'sticky', stickyFirst: !!first && getComputedStyle(first).position === 'sticky' };
  }
  const lum = (c) => { const m = c.match(/\d+(\.\d+)?/g); if (!m) return 1; const [r, g, b] = m.slice(0, 3).map((v) => { v = +v / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }); return 0.2126 * r + 0.7152 * g + 0.0722 * b; };
  const bgOf = (el) => { while (el) { const c = getComputedStyle(el).backgroundColor; if (c && !/rgba\(0, 0, 0, 0\)|transparent/.test(c)) return c; el = el.parentElement; } return 'rgb(255,255,255)'; };
  const heading = document.querySelector('main h1, main h2, h1, h2');
  const step = document.querySelector('.ds78.sp79');
  const darkBg = lum(bgOf(document.body)) < 0.2 && (!step || lum(bgOf(step)) < 0.2);
  const lightText = !!heading && lum(getComputedStyle(heading).color) > 0.5;
  return { W, hscroll: document.documentElement.scrollWidth > W + 1, small: small.slice(0, 5), smallCount: small.length, tiny: tiny.slice(0, 5), tinyCount: tiny.length,
    ctxRight, toggles: document.querySelectorAll('.lt791-tg').length, table, darkBg, lightText };
}

async function memory(b) {
  const out = {};
  const ctx = await b.newContext({ viewport: { width: 375, height: 812 }, reducedMotion: 'reduce' });
  const p = await ctx.newPage();
  await login(p);
  const url = BASE + PAGES.hodina[1];
  await p.goto(url, { waitUntil: 'load' });
  const sel = '[data-lt791-sec="materialy"] .lt791-tg';
  await p.click(sel);
  out.collapsed = await p.evaluate((s) => { const t = document.querySelector(s); return t.getAttribute('aria-expanded') === 'false' && document.getElementById(t.getAttribute('aria-controls')).hidden; }, sel);
  await p.reload({ waitUntil: 'load' });
  out.remembered = await p.evaluate((s) => document.querySelector(s).getAttribute('aria-expanded') === 'false', sel);
  await p.click(sel);
  await p.setViewportSize({ width: 1280, height: 800 });
  await p.goto(url, { waitUntil: 'load' });
  out.desktopOpen = await p.evaluate(() => document.querySelectorAll('.lt791-tg').length === 0 && [...document.querySelectorAll('[data-lt791-sec] h2')].every((h) => h.offsetParent !== null));
  await ctx.close();
  const nctx = await b.newContext({ viewport: { width: 375, height: 812 }, reducedMotion: 'reduce' });
  await nctx.addInitScript(() => { Object.defineProperty(window, 'localStorage', { configurable: true, get() { throw new Error('úložiště zablokované'); } }); });
  const n = await nctx.newPage();
  const errors = [];
  n.on('pageerror', (e) => errors.push(String(e)));
  await login(n);
  await n.goto(url, { waitUntil: 'load' });
  out.noStorageOpen = await n.evaluate(() => { const t = [...document.querySelectorAll('.lt791-tg')]; return t.length > 0 && t.every((x) => x.getAttribute('aria-expanded') === 'true'); });
  await n.click('[data-lt791-sec="plan"] .lt791-tg');
  out.noStorageNoError = errors.length === 0 && await n.evaluate(() => document.querySelector('[data-lt791-sec="plan"] .lt791-tg').getAttribute('aria-expanded') === 'false');
  await nctx.close();
  return out;
}

async function login(p) {
  await p.goto(BASE + '/teacher.php');
  await p.fill('input[name="teacher_name"]', 'Audit Obsah');
  await p.fill('input[name="teacher_key"]', KEY);
  await Promise.all([p.waitForNavigation(), p.click('form button[type="submit"]')]);
}

async function shoot(pg, file) {
  for (const q of [50, 40, 30, 22]) {
    await pg.screenshot({ path: file, type: 'jpeg', quality: q });
    if (fs.statSync(file).size <= 150 * 1024) return fs.statSync(file).size;
  }
  return fs.statSync(file).size;
}

(async () => {
  const b = await chromium.launch({ executablePath: CHROME, headless: true });
  const out = { rows: [], shots: [], maxShot: 0 };
  try {
    for (const scheme of ['light', 'dark']) {
      const tctx = await b.newContext({ viewport: { width: 1280, height: 800 }, colorScheme: scheme, reducedMotion: 'reduce' });
      const t = await tctx.newPage();
      await login(t);
      const sctx = await b.newContext({ viewport: { width: 375, height: 812 }, colorScheme: scheme, reducedMotion: 'reduce' });
      const s = await sctx.newPage();
      await s.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
      for (const [w, h] of WIDTHS) {
        for (const [name, [who, url, tableSel]] of Object.entries(PAGES)) {
          const pg = who === 't' ? t : s;
          await pg.setViewportSize({ width: w, height: h });
          await pg.goto(BASE + url, { waitUntil: 'load' });
          const r = await pg.evaluate(probe, tableSel);
          out.rows.push(Object.assign(r, { page: name, width: w, height: h, scheme }));
        }
      }
      if (SHOTS) {
        for (const [w, h] of SHOT_SIZES) {
          for (const [name, [slug, anchor]] of Object.entries(SHOT_PAGES)) {
            const [who, url] = PAGES[name];
            const pg = who === 't' ? t : s;
            await pg.setViewportSize({ width: w, height: h });
            await pg.goto(BASE + url, { waitUntil: 'load' });
            if (anchor) await pg.evaluate((a) => { const el = document.querySelector(a); if (el) window.scrollTo(0, Math.max(0, el.getBoundingClientRect().top + window.scrollY - 90)); }, anchor);
            const file = SHOTS + '/content-' + slug + '-' + w + '-' + scheme + '.jpg';
            const size = await shoot(pg, file);
            out.maxShot = Math.max(out.maxShot, size);
            out.shots.push(file);
          }
        }
      }
      await tctx.close();
      await sctx.close();
    }
    out.memory = await memory(b);
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})().catch((e) => { console.log(JSON.stringify({ error: String(e && e.message || e) })); process.exit(1); });
