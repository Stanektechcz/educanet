/* EDUCANET v80.1 · prohlížečová část tools/v80_1_teacher_audit.php (Chrome přes playwright-core, dočasné úložiště auditu, fiktivní žáci ze student_directory.php).
   4 stránky (Mapa třídy, mapa jako tabulka, Můj pokrok s „Od učitele“, Linux příkazy) × 11 šířek × světlý/tmavý: vodorovný posun stránky, cíle ≥ 44 px a písmo ≥ 14 px
   v obsahu v80.1, tmavé pozadí podle zařízení. Interakce mapy (lepicí hlavička a sloupec, klik na buňku, výběr, fráze) a menu účtu žáka (klon draweru a jazyka).
   Volitelně snímky JPEG ≤ 150 kB. Použití: node v80_1_teacher_browser.cjs <base-url> <klíč-učitele> <jméno-žáka-3A> [adresář-snímků]. Výstup: JSON na stdout. */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const [BASE, KEY, STUDENT, SHOTS = ''] = process.argv.slice(2);
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const WIDTHS = [[320, 800], [375, 812], [390, 844], [414, 896], [768, 1024], [834, 1112], [1024, 768], [1280, 800], [1366, 600], [1440, 900], [1920, 1080]];
const PAGES = {
  mapa: ['t', '/teacher.php?tab=mapa801&class=class_3a', '.cm801'],
  tabulka: ['t', '/teacher.php?tab=mapa801&class=class_3a&zdroj=ulohy&zobrazeni=tabulka', '.cm801'],
  pokrok: ['s', '/?view=pokrok', '#ts801-od-ucitele, .lr80-next'],
  prikazy: ['s', '/?view=prikazy', ''],
};

function probe(sel) {
  const W = document.documentElement.clientWidth;
  const vis = (el) => { const s = getComputedStyle(el); const r = el.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 1 && r.height > 1 && !el.closest('[hidden]'); };
  const roots = sel ? [...document.querySelectorAll(sel)] : [];
  const small = [];
  const tiny = [];
  for (const root of roots) {
    for (const el of root.querySelectorAll('a, button, select, summary, textarea, input:not([type=hidden])')) {
      const target = el.matches('input[type=checkbox], input[type=radio]') ? el.closest('label') || el : el;
      if (!vis(target)) continue;
      const r = target.getBoundingClientRect();
      const inline = el.tagName === 'A' && el.closest('p, li') && !el.closest('td, th');   // odkaz v běžném textu (WCAG 2.5.8 výjimka „inline“)
      if (!inline && (r.height < 43.5 || r.width < 43.5)) small.push((target.textContent || target.className || target.tagName).trim().slice(0, 24) + ':' + Math.round(r.width) + 'x' + Math.round(r.height));
    }
    for (const el of [root, ...root.querySelectorAll('*')]) {
      if (el.closest('.c70-sr, .ds-sr, .sr-only') || !vis(el)) continue;
      if (![...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim() !== '')) continue;
      const f = parseFloat(getComputedStyle(el).fontSize);
      if (f < 13.95) tiny.push((el.className || el.tagName) + ':' + f);
    }
  }
  const lum = (c) => { const m = c.match(/\d+(\.\d+)?/g); if (!m) return 1; const [r, g, b] = m.slice(0, 3).map((v) => { v = +v / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }); return 0.2126 * r + 0.7152 * g + 0.0722 * b; };
  const bgOf = (el) => { while (el) { const c = getComputedStyle(el).backgroundColor; if (c && !/rgba\(0, 0, 0, 0\)|transparent/.test(c)) return c; el = el.parentElement; } return 'rgb(255,255,255)'; };
  const card = document.querySelector('.cm801-wrap, .lr80-next, main');
  return { W, hscroll: document.documentElement.scrollWidth > W + 1, small: small.slice(0, 5), smallCount: small.length, tiny: tiny.slice(0, 5), tinyCount: tiny.length,
    darkBg: lum(bgOf(document.body)) < 0.2 && (!card || lum(bgOf(card)) < 0.2) };
}

async function shoot(pg, file) {
  for (const q of [60, 50, 40, 30, 22]) {
    await pg.screenshot({ path: file, type: 'jpeg', quality: q, fullPage: true });
    if (fs.statSync(file).size <= 150 * 1024) return fs.statSync(file).size;
  }
  await pg.screenshot({ path: file, type: 'jpeg', quality: 25 });
  return fs.statSync(file).size;
}

async function loginTeacher(ctx) {
  const pg = await ctx.newPage();
  await pg.goto(BASE + '/teacher.php');
  await pg.fill('input[name=teacher_name]', 'Audit Mapa');
  await pg.fill('input[name=teacher_key]', KEY);
  await Promise.all([pg.waitForNavigation(), pg.click('form button[type=submit]')]);
  await pg.close();
}
async function loginStudent(ctx) {
  const pg = await ctx.newPage();
  await pg.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
  await pg.close();
}

async function interact(b) {
  const ctx = await b.newContext({ viewport: { width: 1366, height: 600 } });
  await loginTeacher(ctx);
  const pg = await ctx.newPage();
  await pg.goto(BASE + PAGES.mapa[1]);
  const out = await pg.evaluate(() => {
    const w = document.querySelector('.cm801-wrap');
    w.scrollLeft = 600; w.scrollTop = 200;
    const wr = w.getBoundingClientRect();
    const rowTh = document.querySelector('.cm801-table tbody tr:nth-child(4) th[scope=row]');
    const head = document.querySelector('.cm801-table thead th:nth-child(5)');
    return { stickyCol: Math.abs(rowTh.getBoundingClientRect().left - (wr.left + 48)) < 3 && w.scrollLeft > 0, stickyHead: w.scrollTop > 0 && Math.abs(head.getBoundingClientRect().top - wr.top) < 3 };
  });
  await pg.click('[data-cm801-all="1"]');
  const rows = await pg.$$eval('[data-cm801-pick]', (a) => a.length);
  out.selectAll = (await pg.textContent('[data-cm801-count]')).includes(String(rows)) && (await pg.$$eval('[data-cm801-pick]:checked', (a) => a.length)) === rows;
  await pg.click('[data-cm801-weak]');
  out.weak = await pg.evaluate(() => [...document.querySelectorAll('[data-cm801-pick]')].every((p) => p.checked === !!p.closest('tr').querySelector('.cm801-c.is-low')));
  const radio = await pg.$('input[name=fraze][data-kind=fix]');
  await pg.click('label[for="' + (await radio.getAttribute('id')) + '"]');
  out.phrase = (await pg.inputValue('#cm801-text')) === (await radio.getAttribute('data-text')) && (await pg.inputValue('#cm801-druh')) === 'fix';
  await Promise.all([pg.waitForNavigation(), pg.click('.cm801-table tbody tr:first-child td.cm801-c a')]);
  out.cellNav = pg.url().includes('tab=student360') && pg.url().includes('student=class_3a');
  await ctx.close();
  return out;
}

async function menu(b) {
  const ctx = await b.newContext({ viewport: { width: 390, height: 844 } });
  await loginStudent(ctx);
  const pg = await ctx.newPage();
  const errors = [];
  pg.on('pageerror', (e) => errors.push(String(e)));
  await pg.goto(BASE + '/?view=prikazy');
  await pg.click('.v56-menu-button');
  const res = await pg.evaluate(() => {
    const d = document.querySelector('.nav61-drawer');
    const hrefs = [...d.querySelectorAll('a')].map((a) => a.getAttribute('href'));
    const bar = [...document.querySelectorAll('.ui-bottomnav a')].map((a) => a.getAttribute('href'));
    return { labels: d.querySelectorAll('.v56-menu-label').length, links: hrefs.length, cviceni: hrefs.includes('?view=cviceni') && hrefs.includes('?view=opakovat'),
      bottomDup: hrefs.some((h) => bar.includes(h)), lang: !!document.querySelector('[data-nav61-lang] .edu-lang-switcher select'), open: document.querySelector('.v56-menu').classList.contains('is-open') };
  });
  res.errors = errors.length;
  if (SHOTS) res.shot = await shoot(pg, SHOTS + '/mapa801-zak-menu-390.jpg');
  await ctx.close();
  return res;
}

(async () => {
  const out = { rows: [], shots: [], maxShot: 0 };
  const b = await chromium.launch({ executablePath: CHROME });
  try {
    for (const scheme of ['light', 'dark']) {
      const ctxs = {};
      for (const who of ['t', 's']) { ctxs[who] = await b.newContext({ colorScheme: scheme }); await (who === 't' ? loginTeacher : loginStudent)(ctxs[who]); }
      for (const [name, [who, url, sel]] of Object.entries(PAGES)) {
        const pg = await ctxs[who].newPage();
        for (const [w, h] of WIDTHS) {
          await pg.setViewportSize({ width: w, height: h });
          await pg.goto(BASE + url, { waitUntil: 'load' });
          const r = await pg.evaluate(probe, sel);
          out.rows.push({ page: name, width: w, scheme, ...r });
          const shotWanted = SHOTS && ((name === 'mapa' && ((w === 1366 && scheme === 'light') || (w === 1920 && scheme === 'dark') || (w === 390 && scheme === 'light')))
            || (name === 'tabulka' && w === 1366 && scheme === 'light') || (name === 'pokrok' && (w === 375 || (w === 1280 && scheme === 'light'))));
          if (shotWanted) {
            const file = SHOTS + '/mapa801-' + name + '-' + w + '-' + scheme + '.jpg';
            const size = await shoot(pg, file);
            out.shots.push(file);
            out.maxShot = Math.max(out.maxShot, size);
          }
        }
        await pg.close();
      }
      for (const c of Object.values(ctxs)) await c.close();
    }
    out.interact = await interact(b);
    out.menu = await menu(b);
    if (out.menu.shot) { out.shots.push('menu'); out.maxShot = Math.max(out.maxShot, out.menu.shot); }
  } catch (e) {
    out.error = String(e && e.message || e).slice(0, 300);
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})();
