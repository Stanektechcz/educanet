/* EDUCANET v78.1 · prohlížečová část tools/v78_1_nav_audit.php (Chrome přes playwright-core, jen čtení, dočasné úložiště auditu).
   Měří DOM na 11 šířkách × světlý/tmavý: vodorovný posun, cíle navigace ≥ 44 px, písmo ≥ 14 px, viditelnost (spodní lišta / Menu / boční panel),
   aria-current; klávesnice: drawer (Více 375 px, Menu 834 px) – fokus zůstává uvnitř, Esc zavře a vrátí fokus. Výstup: JSON na stdout.
   Použití: node v78_1_nav_browser.cjs <base-url> <klíč-učitele> [adresář-snímků]   (Chrome: EDUCANET_CHROME, jinak výchozí cesta) */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const BASE = process.argv[2], KEY = process.argv[3], SHOTS = process.argv[4] || '';
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const WIDTHS = [[320, 800], [375, 812], [390, 844], [414, 896], [768, 1024], [834, 1112], [1024, 768], [1280, 800], [1366, 600], [1440, 900], [1920, 1080]];
const SHOT_W = [375, 768, 1280, 1920];
const TEACHER_PAGES = { home: '/teacher.php?tab=attention', hub: '/teacher.php?tab=sekce&sekce=vyuka&class=class_3a', hodina: '/teacher.php?tab=hodina&class=class_3a' };

function probe() {
  const W = document.documentElement.clientWidth;
  const vis = (el) => { if (!el) return false; const s = getComputedStyle(el); const r = el.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 0 && r.height > 0; };
  const navSel = '.n781-tree a, .n781-tree button, .n781-tree summary, .n781-bottom a, .n781-back, .n781-menu, .t68-crumbs a, .ui-bottomnav a';
  let small = [], fonts = 0;
  for (const el of document.querySelectorAll(navSel)) {
    if (!vis(el) || el.closest('details:not([open]) > :not(summary)')) continue;
    const r = el.getBoundingClientRect();
    if (r.height < 43.5 || r.width < 43.5) small.push((el.className || el.tagName) + ':' + Math.round(r.width) + 'x' + Math.round(r.height));
    for (const t of el.querySelectorAll('span, b, strong')) if (vis(t) && parseFloat(getComputedStyle(t).fontSize) < 13.9) fonts++;
    if (parseFloat(getComputedStyle(el).fontSize) < 13.9) fonts++;
  }
  const tree = document.querySelector('.n781-tree');
  return {
    W, scrollW: document.documentElement.scrollWidth, hscroll: document.documentElement.scrollWidth > W + 1,
    small: small.slice(0, 8), smallCount: small.length, fontsBelow14: fonts,
    bottom: vis(document.querySelector('.n781-bottom')), menuBtn: vis(document.querySelector('.n781-menu')), side: vis(document.querySelector('.t68-side')),
    studentBottom: vis(document.querySelector('.ui-bottomnav')),
    currentInTree: tree ? tree.querySelectorAll('[aria-current="page"]').length : -1,
    currentInBottom: document.querySelectorAll('.n781-bottom [aria-current]').length,
    studentCurrent: document.querySelectorAll('.ui-bottomnav [aria-current="page"]').length,
    theme: document.documentElement.getAttribute('data-theme') || '',
    navBg: tree ? getComputedStyle(document.querySelector('.n781-bottom') || tree).backgroundColor : '',
  };
}

async function keyboard(p, w, h, openerSel) {
  await p.setViewportSize({ width: w, height: h });
  await p.goto(BASE + TEACHER_PAGES.home, { waitUntil: 'load' });
  await p.focus(openerSel);
  await p.keyboard.press('Enter');
  await p.waitForTimeout(50);
  const opened = await p.evaluate(() => document.documentElement.classList.contains('n781-open') && document.getElementById('t68-side').getAttribute('aria-modal') === 'true'
    && document.getElementById('t68-side').contains(document.activeElement));
  let escaped = 0;
  for (let i = 0; i < 45; i++) { await p.keyboard.press('Tab'); if (!(await p.evaluate(() => document.getElementById('t68-side').contains(document.activeElement)))) escaped++; }
  for (let i = 0; i < 8; i++) { await p.keyboard.press('Shift+Tab'); if (!(await p.evaluate(() => document.getElementById('t68-side').contains(document.activeElement)))) escaped++; }
  const inert = await p.evaluate(() => document.querySelector('.t68-main').inert === true);
  await p.keyboard.press('Escape');
  await p.waitForTimeout(50);
  const closed = await p.evaluate((sel) => !document.documentElement.classList.contains('n781-open') && document.activeElement === document.querySelector(sel)
    && document.querySelector('.t68-main').inert === false && document.querySelector(sel).getAttribute('aria-expanded') === 'false', openerSel);
  return { width: w, opened, escaped, inert, closed };
}

(async () => {
  const b = await chromium.launch({ executablePath: CHROME, headless: true });
  const out = { teacher: [], student: [], keyboard: [], shots: [] };
  try {
    const ctx = await b.newContext({ viewport: { width: 1280, height: 800 }, reducedMotion: 'reduce' });
    const p = await ctx.newPage();
    await p.goto(BASE + '/teacher.php');
    await p.fill('input[name="teacher_name"]', 'Audit Navigace');
    await p.fill('input[name="teacher_key"]', KEY);
    await Promise.all([p.waitForNavigation(), p.click('form button[type="submit"]')]);
    for (const scheme of ['light', 'dark']) {
      await p.emulateMedia({ colorScheme: scheme, reducedMotion: 'reduce' });
      for (const [w, h] of WIDTHS) {
        await p.setViewportSize({ width: w, height: h });
        for (const [name, url] of Object.entries(TEACHER_PAGES)) {
          await p.goto(BASE + url, { waitUntil: 'load' });
          const r = await p.evaluate(probe);
          Object.assign(r, { page: name, width: w, height: h, scheme });
          out.teacher.push(r);
          if (SHOTS && name === 'home' && SHOT_W.includes(w)) {
            const file = SHOTS + '/nav-teacher-' + w + '-' + scheme + '.jpg';
            await p.screenshot({ path: file, type: 'jpeg', quality: 55, fullPage: false });
            out.shots.push(file);
          }
        }
      }
    }
    await p.emulateMedia({ colorScheme: 'light', reducedMotion: 'reduce' });
    out.keyboard.push(await keyboard(p, 375, 812, '.n781-bottom a[data-n781-open]'));
    out.keyboard.push(await keyboard(p, 834, 1112, '.n781-menu'));
    if (SHOTS) {   // otevřený drawer (Více) na mobilu
      await p.setViewportSize({ width: 375, height: 812 });
      await p.goto(BASE + TEACHER_PAGES.home, { waitUntil: 'load' });
      await p.click('.n781-bottom a[data-n781-open]');
      await p.screenshot({ path: SHOTS + '/nav-teacher-375-drawer.jpg', type: 'jpeg', quality: 55 });
      out.shots.push(SHOTS + '/nav-teacher-375-drawer.jpg');
    }
    const sctx = await b.newContext({ viewport: { width: 375, height: 812 }, reducedMotion: 'reduce' });
    const s = await sctx.newPage();
    await s.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent('Audit Navigace'));
    for (const scheme of ['light', 'dark']) {
      await s.emulateMedia({ colorScheme: scheme, reducedMotion: 'reduce' });
      for (const [w, h] of WIDTHS) {
        await s.setViewportSize({ width: w, height: h });
        await s.goto(BASE + '/?view=dashboard', { waitUntil: 'load' });
        const r = await s.evaluate(probe);
        Object.assign(r, { page: 'student-dashboard', width: w, height: h, scheme });
        out.student.push(r);
        if (SHOTS && SHOT_W.includes(w)) {
          const file = SHOTS + '/nav-student-' + w + '-' + scheme + '.jpg';
          await s.screenshot({ path: file, type: 'jpeg', quality: 55, fullPage: false });
          out.shots.push(file);
        }
      }
    }
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})().catch((e) => { console.log(JSON.stringify({ error: String(e && e.message || e) })); process.exit(1); });
