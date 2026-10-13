/* EDUCANET v79 · prohlížečová část tools/v79_stepper_audit.php (Chrome přes playwright-core, dočasné úložiště auditu, fiktivní data).
   Měří DOM 9 stránek krokových postupů na 11 šířkách: vodorovný posun, cíle ≥ 44 px (pruh kroků, tlačítka, pole kroku, souhrn chyb),
   kompaktní stepper pod 480 px kontejneru. Klávesnice: Reflexe – Enter na „Další“ bez stavu (kontrola v JS, fokus na souhrn), mezerník,
   Enter, fokus na nadpis kroku, Zpět drží data. Volitelně snímky 375 / 1280 × světlý / tmavý. Výstup: JSON na stdout.
   Použití: node v79_stepper_browser.cjs <base-url> <klíč-učitele> <jméno-žáka-3A> [adresář-snímků] */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const BASE = process.argv[2], KEY = process.argv[3], STUDENT = process.argv[4] || '', SHOTS = process.argv[5] || '';
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const WIDTHS = [[320, 800], [375, 812], [390, 844], [414, 896], [768, 1024], [834, 1112], [1024, 768], [1280, 800], [1366, 600], [1440, 900], [1920, 1080]];
const TEACHER = {
  'priprava-1': '/teacher.php?tab=priprava79&class=class_3a&krok=1',
  'priprava-4': '/teacher.php?tab=priprava79&class=class_3a&lesson=5&krok=4',
  'reflexe-1': '/teacher.php?tab=reflexe73&class=class_3a&lesson=7',
  'hromadne-2': '/teacher.php?tab=hromadne74&f=1&rozsah=trida&class=class_3a&co_lekce=1&co_svp=1&krok=2',
  'opakovani-2': '/teacher.php?tab=opakovani75&class=class_3a&od=5&do=16',
  'identita-1': '/teacher.php?tab=identita&krok=1',
};
const STUDENT_PAGES = { 'uloha-1': '/?view=ulohy&uloha=net_ports', 'uloha-2': '/?view=ulohy&uloha=lnx_chmod&krok=2', 'portfolio-1': '/?view=portfolio' };
const SHOT = { 'priprava-4': 'priprava', 'reflexe-1': 'reflexe', 'hromadne-2': 'hromadne', 'opakovani-2': 'opakovani', 'identita-1': 'identita', 'uloha-2': 'uloha', 'portfolio-1': 'portfolio' };

function probe() {
  const W = document.documentElement.clientWidth;
  const vis = (el) => { if (!el) return false; const s = getComputedStyle(el); const r = el.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 0 && r.height > 0; };
  const sel = '.sp79 a, .sp79-nav a, .sp79-nav button, .sp79-errs a, .sp79-edit, .sp79-resume a, [data-sp79] select, [data-sp79] textarea, '
    + '[data-sp79] input:not([type=hidden]):not([type=radio]):not([type=checkbox]), [data-sp79] label:has(> input[type=radio]), [data-sp79] label:has(> input[type=checkbox])';
  const small = [];
  for (const el of document.querySelectorAll(sel)) {
    if (!vis(el) || el.closest('[hidden]')) continue;
    const r = el.getBoundingClientRect();
    if (r.height < 43.5 || r.width < 43.5) small.push((el.id || el.className || el.tagName) + ':' + Math.round(r.width) + 'x' + Math.round(r.height));
  }
  const bar = document.querySelector('.sp79-bar');
  return {
    W, hscroll: document.documentElement.scrollWidth > W + 1, small: small.slice(0, 6), smallCount: small.length,
    stepper: vis(document.querySelector('.ds78.sp79')), barWidth: bar ? Math.round(bar.getBoundingClientRect().width) : 0,
    mini: vis(document.querySelector('.sp79-mini')), list: vis(document.querySelector('.sp79-bar .ds-stepper')),
  };
}

async function keyboard(p) {
  const out = {};
  await p.setViewportSize({ width: 1280, height: 800 });
  await p.goto(BASE + '/teacher.php?tab=reflexe73&class=class_3a&lesson=8', { waitUntil: 'load' });
  const url0 = p.url();
  await p.focus('.sp79-nav button[value="sp79_next"]');
  await p.keyboard.press('Enter');
  await p.waitForTimeout(80);
  out.blocked = p.url() === url0;
  out.summaryFocused = await p.evaluate(() => document.activeElement && document.activeElement.id === 'sp79-errors' && !document.getElementById('sp79-errors').hidden);
  out.invalid = await p.evaluate(() => document.getElementById('lr73-state-cele').getAttribute('aria-invalid') === 'true');
  await p.focus('#lr73-state-cast');
  await p.keyboard.press('Space');
  await p.focus('.sp79-nav button[value="sp79_next"]');
  await Promise.all([p.waitForNavigation(), p.keyboard.press('Enter')]);
  out.step2 = /krok=2/.test(p.url()) && await p.evaluate(() => /Krok 2 ze 4/.test(document.querySelector('.sp79-count').textContent));
  out.headingFocused = await p.evaluate(() => document.activeElement && document.activeElement.id === 'sp79-h2');
  await p.focus('.sp79-nav button[value="sp79_back"]');
  await Promise.all([p.waitForNavigation(), p.keyboard.press('Enter')]);
  out.backKept = /krok=1/.test(p.url()) && await p.evaluate(() => document.getElementById('lr73-state-cast').checked);
  return out;
}

(async () => {
  const b = await chromium.launch({ executablePath: CHROME, headless: true });
  const out = { rows: [], keyboard: {}, shots: [] };
  try {
    const ctx = await b.newContext({ viewport: { width: 1280, height: 800 }, reducedMotion: 'reduce' });
    const p = await ctx.newPage();
    await p.goto(BASE + '/teacher.php');
    await p.fill('input[name="teacher_name"]', 'Audit Stepper');
    await p.fill('input[name="teacher_key"]', KEY);
    await Promise.all([p.waitForNavigation(), p.click('form button[type="submit"]')]);
    const sctx = await b.newContext({ viewport: { width: 375, height: 812 }, reducedMotion: 'reduce' });
    const s = await sctx.newPage();
    await s.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
    const pages = [...Object.entries(TEACHER).map(([n, u]) => [p, n, u]), ...Object.entries(STUDENT_PAGES).map(([n, u]) => [s, n, u])];
    for (const [w, h] of WIDTHS) {
      for (const [pg, name, url] of pages) {
        await pg.setViewportSize({ width: w, height: h });
        await pg.goto(BASE + url, { waitUntil: 'load' });
        const r = await pg.evaluate(probe);
        out.rows.push(Object.assign(r, { page: name, width: w, height: h }));
      }
    }
    if (SHOTS) {
      for (const scheme of ['light', 'dark']) {
        for (const pg of [p, s]) await pg.emulateMedia({ colorScheme: scheme, reducedMotion: 'reduce' });
        for (const w of [375, 1280]) {
          for (const [pg, name, url] of pages) {
            if (!SHOT[name]) continue;
            await pg.setViewportSize({ width: w, height: w === 375 ? 812 : 800 });
            await pg.goto(BASE + url, { waitUntil: 'load' });
            await pg.evaluate(() => { const t = document.getElementById('sp79-top'); if (t) window.scrollTo(0, Math.max(0, t.getBoundingClientRect().top + window.scrollY - 96)); });
            const file = SHOTS + '/stepper-' + SHOT[name] + '-' + w + '-' + scheme + '.jpg';
            await pg.screenshot({ path: file, type: 'jpeg', quality: 50 });
            out.shots.push(file);
          }
        }
      }
      for (const pg of [p, s]) await pg.emulateMedia({ colorScheme: 'light', reducedMotion: 'reduce' });
    }
    out.keyboard = await keyboard(p);
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})().catch((e) => { console.log(JSON.stringify({ error: String(e && e.message || e) })); process.exit(1); });
