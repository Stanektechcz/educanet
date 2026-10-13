/* EDUCANET v80 · prohlížečová část tools/v80_learning_audit.php (Chrome přes playwright-core, dočasné úložiště auditu, fiktivní žák ze student_directory.php).
   8 stránek (Můj pokrok, cvičení: seznam + podsítě + chmod + barvy + specificita, opakování, úloha s nápovědou) × 11 šířek × světlý/tmavý:
   vodorovný posun, cíle ≥ 44 px a písmo ≥ 14 px v obsahu v80, tmavé pozadí podle zařízení. Klávesnice a živé pomůcky (aria-live) u všech cvičení,
   nápověda klávesnicí, viditelný fokus. Volitelně snímky 375/1280 × světlý/tmavý (JPEG ≤ 150 kB).
   Použití: node v80_learning_browser.cjs <base-url> <jméno-žáka-3A> [adresář-snímků]. Výstup: JSON na stdout. */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const BASE = process.argv[2], STUDENT = process.argv[3] || '', SHOTS = process.argv[4] || '';
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const WIDTHS = [[320, 800], [375, 812], [390, 844], [414, 896], [768, 1024], [834, 1112], [1024, 768], [1280, 800], [1366, 600], [1440, 900], [1920, 1080]];
const PAGES = {
  pokrok: '/?view=pokrok', cviceni: '/?view=cviceni', subnet: '/?view=cviceni&w=subnet', chmod: '/?view=cviceni&w=chmod', color: '/?view=cviceni&w=color',
  spec: '/?view=cviceni&w=spec', opakovat: '/?view=opakovat', uloha: '/?view=ulohy&uloha=net_subnet&krok=2',
};
const SHOT_PAGES = { pokrok: 'muj-pokrok', chmod: 'cviceni-chmod', color: 'cviceni-barvy', subnet: 'cviceni-podsite', spec: 'cviceni-specificita', opakovat: 'opakovani', uloha: 'uloha-napoveda' };
const SHOT_SIZES = [[375, 812], [1280, 800]];

function probe() {
  const W = document.documentElement.clientWidth;
  const vis = (el) => { const s = getComputedStyle(el); const r = el.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 1 && r.height > 1 && !el.closest('[hidden]'); };
  const roots = [...document.querySelectorAll('.lr80-page, .lr80-hints, .lr80-why, .lr80-due, .lr80-links')];
  const small = [];
  for (const root of roots) {
    for (const el of root.querySelectorAll('a, button, select, summary, input:not([type=hidden])')) {
      const target = el.matches('input[type=checkbox], input[type=radio]') ? el.closest('label') || el : el;
      if (!vis(target)) continue;
      const r = target.getBoundingClientRect();
      if (r.height < 43.5 || r.width < 43.5) small.push((target.textContent || target.className || target.tagName).trim().slice(0, 24) + ':' + Math.round(r.width) + 'x' + Math.round(r.height));
    }
  }
  const tiny = [];
  for (const root of roots) {
    for (const el of [root, ...root.querySelectorAll('*')]) {
      if (el.closest('.ds-sr, .sr-only') || !vis(el)) continue;
      if (![...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim() !== '')) continue;
      const f = parseFloat(getComputedStyle(el).fontSize);
      if (f < 13.95) tiny.push((el.className || el.tagName) + ':' + f);
    }
  }
  const lum = (c) => { const m = c.match(/\d+(\.\d+)?/g); if (!m) return 1; const [r, g, b] = m.slice(0, 3).map((v) => { v = +v / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }); return 0.2126 * r + 0.7152 * g + 0.0722 * b; };
  const bgOf = (el) => { while (el) { const c = getComputedStyle(el).backgroundColor; if (c && !/rgba\(0, 0, 0, 0\)|transparent/.test(c)) return c; el = el.parentElement; } return 'rgb(255,255,255)'; };
  const card = document.querySelector('.lr80-page .ds-card, .lr80-hints');
  const heading = document.querySelector('.lr80-page h1, .lr80-hints h3, main h2');
  return { W, hscroll: document.documentElement.scrollWidth > W + 1, small: small.slice(0, 5), smallCount: small.length, tiny: tiny.slice(0, 5), tinyCount: tiny.length,
    darkBg: lum(bgOf(document.body)) < 0.2 && (!card || lum(bgOf(card)) < 0.2), lightText: !!heading && lum(getComputedStyle(heading).color) > 0.5 };
}

async function shoot(pg, file) {
  for (const q of [55, 45, 35, 25]) {
    await pg.screenshot({ path: file, type: 'jpeg', quality: q, fullPage: true });
    if (fs.statSync(file).size <= 150 * 1024) return fs.statSync(file).size;
  }
  await pg.screenshot({ path: file, type: 'jpeg', quality: 25 });
  return fs.statSync(file).size;
}

async function keyboard(b) {
  const k = {};
  const ctx = await b.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce' });
  const p = await ctx.newPage();
  await p.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
  const out = (sel) => p.evaluate((s) => (document.querySelector(s) || {}).textContent || '', sel);
  await p.goto(BASE + PAGES.chmod, { waitUntil: 'load' });
  const before = await out('[data-w80-perm]');
  await p.focus('[data-w80-bit]');
  await p.keyboard.press('Space');
  await p.waitForTimeout(300);
  k.chmodSpace = (await out('[data-w80-perm]')) !== before && (await out('[data-w80-perm]')).includes('400') && await p.evaluate(() => document.querySelector('[data-w80-bit]').checked);
  await p.focus('[data-w80-oct]');
  await p.keyboard.press('Control+A');
  await p.keyboard.type('750');
  await p.waitForTimeout(300);
  k.chmodOct = await p.evaluate(() => [...document.querySelectorAll('[data-w80-bit]')].map((x) => (x.checked ? '1' : '0')).join('') === '111101000') && (await out('[data-w80-perm]')).includes('rwxr-x---');
  await p.focus('[data-w80-bit]');
  await p.keyboard.press('Shift+Tab');
  await p.keyboard.press('Tab');
  k.focusRing = await p.evaluate(() => { const a = document.activeElement; const s = getComputedStyle(a); return !!a && (s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) >= 2 || s.boxShadow !== 'none'); });
  k.live = await p.evaluate(() => document.querySelectorAll('output[aria-live="polite"]').length >= 1 && document.getElementById('lr80-w-result').getAttribute('aria-live') === 'polite');
  await p.goto(BASE + PAGES.color, { waitUntil: 'load' });
  await p.focus('[data-w80-range="g"]');
  for (let i = 0; i < 5; i++) await p.keyboard.press('ArrowRight');
  await p.waitForTimeout(300);
  k.colorArrow = (await p.inputValue('#w80-g')) === '5' && (await out('[data-w80-color]')).includes('#000500');
  await p.goto(BASE + PAGES.subnet, { waitUntil: 'load' });
  await p.focus('#w80-pfx');
  await p.keyboard.press('Control+A');
  await p.keyboard.type('26');
  await p.waitForTimeout(300);
  k.subnet = (await out('[data-w80-calc]')).includes('255.255.255.192') && (await out('[data-w80-calc]')).includes('64');
  await p.goto(BASE + PAGES.spec, { waitUntil: 'load' });
  await p.focus('#w80-sa');
  await p.keyboard.type('0,1,2');
  await p.focus('#w80-sb');
  await p.keyboard.type('0,2,0');
  await p.waitForTimeout(300);
  k.duel = /\bB\b/.test(await out('[data-w80-duel]'));
  await p.goto(BASE + '/?view=ulohy&uloha=lnx_chmod&krok=2', { waitUntil: 'load' });
  await p.focus('.lr80-hint-form button');
  await Promise.all([p.waitForNavigation(), p.keyboard.press('Enter')]);
  k.hintKey = (await p.content()).includes('Nápověda 1 ze 3');
  await ctx.close();
  return k;
}

(async () => {
  const b = await chromium.launch({ executablePath: CHROME, headless: true });
  const out = { rows: [], shots: [], maxShot: 0 };
  try {
    for (const scheme of ['light', 'dark']) {
      const ctx = await b.newContext({ viewport: { width: 375, height: 812 }, colorScheme: scheme, reducedMotion: 'reduce' });
      const s = await ctx.newPage();
      await s.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
      for (const [w, h] of WIDTHS) {
        for (const [name, url] of Object.entries(PAGES)) {
          await s.setViewportSize({ width: w, height: h });
          await s.goto(BASE + url, { waitUntil: 'load' });
          const r = await s.evaluate(probe);
          out.rows.push(Object.assign(r, { page: name, width: w, height: h, scheme }));
        }
      }
      if (SHOTS) {
        for (const [w, h] of SHOT_SIZES) {
          for (const [name, slug] of Object.entries(SHOT_PAGES)) {
            await s.setViewportSize({ width: w, height: h });
            await s.goto(BASE + PAGES[name] + (name === 'uloha' ? '#lr80-hints' : ''), { waitUntil: 'load' });
            const file = SHOTS + '/learning-' + slug + '-' + w + '-' + scheme + '.jpg';
            const size = await shoot(s, file);
            out.maxShot = Math.max(out.maxShot, size);
            out.shots.push(file);
          }
        }
      }
      await ctx.close();
    }
    out.keyboard = await keyboard(b);
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})().catch((e) => { console.log(JSON.stringify({ error: String(e && e.message || e) })); process.exit(1); });
