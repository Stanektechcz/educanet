/* EDUCANET v81 · prohlížečová část tools/v81_visual_audit.php (Chrome přes playwright-core, dočasné úložiště auditu, fiktivní žáci).
   Režim „checks“: ikony ze spritu se vykreslí (currentColor, aria-hidden), hlavička stránky (h1 + účel + primární akce), přepínač hustoty
   (Komfortní/Kompaktní) mění třídu a pamatuje si ji po načtení, nápověda „Jak to funguje“ jde zavřít a po načtení zůstane zavřená,
   toast po POST se ukáže (s JS) a inline hláška zůstane bez JS, prefers-reduced-motion vypne přechody, skeleton se nahradí obsahem.
   Režim „shots“: snímky JPEG ≤ 150 kB (Domů žáka, Co řešit dnes, Dnešní hodina × 375/1280/1920 × světlý/tmavý) s předponou souboru.
   Použití: node v81_visual_browser.cjs <base-url> <klíč-učitele> <jméno-žáka-3A> <checks|shots|all> [adresář-snímků] [předpona]. Výstup: JSON. */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const [BASE, KEY, STUDENT, MODE = 'checks', SHOTS = '', PREFIX = 'v81-'] = process.argv.slice(2);
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const SHOT_PAGES = [
  { id: 's-domu', who: 's', url: '/?view=dashboard' },
  { id: 't-dnes', who: 't', url: '/teacher.php?tab=attention' },
  { id: 't-hodina', who: 't', url: '/teacher.php?tab=hodina&class=class_3a' },
];
const SHOT_WIDTHS = [[375, 812], [1280, 800], [1920, 1080]];

async function shoot(pg, file) {
  for (const q of [55, 45, 35, 28, 22, 16, 12]) {
    await pg.screenshot({ path: file, type: 'jpeg', quality: q, fullPage: true });
    if (fs.statSync(file).size <= 150 * 1024) return fs.statSync(file).size;
  }
  for (const q of [50, 35, 25]) {
    await pg.screenshot({ path: file, type: 'jpeg', quality: q });
    if (fs.statSync(file).size <= 150 * 1024) return fs.statSync(file).size;
  }
  return fs.statSync(file).size;
}

async function login(ctx, who) {
  const pg = await ctx.newPage();
  if (who === 't') {
    await pg.goto(BASE + '/teacher.php');
    await pg.fill('input[name=teacher_name]', 'Audit Vzhled');
    await pg.fill('input[name=teacher_key]', KEY);
    await Promise.all([pg.waitForNavigation(), pg.click('form button[type=submit]')]);
  } else {
    await pg.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
  }
  await pg.close();
}

/** Stav stránky po načtení: ikony, hlavička, přepínač hustoty, nápověda, toasty, pohyb. */
function probe() {
  const vis = (el) => { if (!el) return false; const s = getComputedStyle(el); const r = el.getBoundingClientRect(); return s.display !== 'none' && s.visibility !== 'hidden' && r.width > 0 && r.height > 0; };
  const icons = [...document.querySelectorAll('svg.i81')];
  const iconOk = icons.filter((s) => s.getAttribute('aria-hidden') === 'true' && s.querySelector('use') && s.getBoundingClientRect().width >= 12).length;
  const head = document.querySelector('.v81-head');
  const h1 = head && head.querySelector('h1');
  const purpose = head && head.querySelector('.v81-head__lead');
  const primary = head ? head.querySelectorAll('.v81-head__actions .ds-btn--primary, .v81-head__actions .btn.primary').length : 0;
  const toggle = document.querySelector('[data-v81-density-wrap]');
  const hint = document.querySelector('[data-v81-hint]');
  const tr = [...document.querySelectorAll('.h81-tile, .v81-lift, .student-primary-cta, .ds-btn')].slice(0, 30).map((el) => { const s = getComputedStyle(el); return s.transitionProperty === 'none' ? '0s' : s.transitionDuration; });
  const anim = tr.filter((d) => d.split(',').some((x) => parseFloat(x) >= 0.01)).length;   // 0,01 s a méně = vypnuto (starší vrstvy dávají 1e-05 s)
  return {
    icons: icons.length, iconOk, head: !!head, h1: !!(h1 && h1.textContent.trim()), purpose: !!(purpose && purpose.textContent.trim()), primary,
    toggle: !!toggle && vis(toggle), toggleText: toggle ? toggle.textContent.replace(/\s+/g, ' ').trim().slice(0, 60) : '',
    density: document.documentElement.getAttribute('data-v81-density') || '',
    hint: !!hint && vis(hint), toasts: document.querySelectorAll('.v81-toast').length, flashVisible: [...document.querySelectorAll('.teacher-flash, .notice')].filter(vis).length,
    skeleton: document.querySelectorAll('.ds-skeleton').length, animated: anim,
    hscroll: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
  };
}

async function checks(b, out) {
  const ctxT = await b.newContext({ reducedMotion: 'no-preference' });
  const ctxS = await b.newContext({ reducedMotion: 'no-preference' });
  await login(ctxT, 't');
  await login(ctxS, 's');
  const errs = (pg, id) => pg.on('pageerror', (e) => out.errors.push(id + ': ' + String(e).slice(0, 120)));
  // 1) hlavní stránky: ikony a hlavička
  for (const [id, ctx, url] of [['s-domu', ctxS, '/?view=dashboard'], ['t-dnes', ctxT, '/teacher.php?tab=attention'], ['t-hodina', ctxT, '/teacher.php?tab=hodina&class=class_3a'],
    ['t-vysledky', ctxT, '/teacher.php?tab=class_results&class=class_3a'], ['t-streda', ctxT, '/teacher.php?tab=streda77'], ['s-pokrok', ctxS, '/?view=pokrok'], ['s-ulohy', ctxS, '/?view=ulohy']]) {
    const pg = await ctx.newPage(); errs(pg, id);
    await pg.setViewportSize({ width: 1280, height: 800 });
    await pg.goto(BASE + url, { waitUntil: 'load' });
    out.pages.push({ id, ...(await pg.evaluate(probe)) });
    await pg.close();
  }
  // 2) přepínač hustoty: klik → kompaktní, po načtení zůstává; zpět na komfortní
  for (const [id, url] of [['t-vysledky', '/teacher.php?tab=class_results&class=class_3a'], ['t-streda', '/teacher.php?tab=streda77']]) {
    const pg = await ctxT.newPage(); errs(pg, id);
    await pg.setViewportSize({ width: 1280, height: 800 });
    await pg.goto(BASE + url, { waitUntil: 'load' });
    const before = await pg.evaluate(probe);
    let after = null; let reload = null; let back = null; let rowH = [];
    if (before.toggle) {
      rowH.push(await pg.evaluate(() => { const r = document.querySelector('#t68-main table tbody tr'); return r ? r.getBoundingClientRect().height : 0; }));
      await pg.click('[data-v81-density="compact"]');
      after = await pg.evaluate(probe);
      rowH.push(await pg.evaluate(() => { const r = document.querySelector('#t68-main table tbody tr'); return r ? r.getBoundingClientRect().height : 0; }));
      await pg.reload({ waitUntil: 'load' });
      reload = await pg.evaluate(probe);
      await pg.click('[data-v81-density="comfortable"]');
      back = await pg.evaluate(probe);
    }
    out.density.push({ id, before: before.density, toggle: before.toggle, text: before.toggleText, after: after && after.density, reload: reload && reload.density, back: back && back.density, rowH });
    await pg.close();
  }
  // 3) nápověda učitele: zavřít → po načtení zavřená; Domů má nativní details (visual_qa_2026_10_29.cjs).
  for (const [id, ctx, url] of [['t-dnes', ctxT, '/teacher.php?tab=attention']]) {
    const pg = await ctx.newPage(); errs(pg, id);
    await pg.setViewportSize({ width: 1280, height: 800 });
    await pg.goto(BASE + url, { waitUntil: 'load' });
    const first = await pg.evaluate(probe);
    let closed = null; let reload = null; let focusOk = false;
    if (first.hint) {
      await pg.click('[data-v81-hint] [data-v81-hint-close]');
      closed = await pg.evaluate(probe);
      focusOk = await pg.evaluate(() => document.activeElement && document.activeElement !== document.body);
      await pg.reload({ waitUntil: 'load' });
      reload = await pg.evaluate(probe);
    }
    out.hints.push({ id, first: first.hint, closed: closed && closed.hint, reload: reload && reload.hint, focusOk });
    await pg.close();
  }
  // 4) toast po POST: přepínač vzhledu v cockpitu (POST → přesměrování → hláška) s JS a bez JS
  {
    const pg = await ctxT.newPage(); errs(pg, 'toast');
    await pg.setViewportSize({ width: 1280, height: 800 });
    await pg.goto(BASE + '/teacher.php?tab=attention', { waitUntil: 'load' });
    await Promise.all([pg.waitForNavigation(), pg.click('form.t68-theme button[value=system]')]);
    await pg.waitForTimeout(400);
    const withJs = await pg.evaluate(probe);
    const live = await pg.evaluate(() => { const r = document.querySelector('.v81-toasts'); return r ? (r.getAttribute('role') || '') + '/' + (r.getAttribute('aria-live') || '') : ''; });
    out.toast.push({ js: true, toasts: withJs.toasts, flashVisible: withJs.flashVisible, live });
    await pg.close();
    const ctxN = await b.newContext({ javaScriptEnabled: false });
    await login(ctxN, 't');
    const pn = await ctxN.newPage();
    await pn.goto(BASE + '/teacher.php?tab=attention', { waitUntil: 'load' });
    await Promise.all([pn.waitForNavigation(), pn.click('form.t68-theme button[value=system]')]);
    const noJs = await pn.evaluate(probe);
    out.toast.push({ js: false, toasts: noJs.toasts, flashVisible: noJs.flashVisible, hint: noJs.hint, toggle: noJs.toggle });
    await ctxN.close();
  }
  // 5) prefers-reduced-motion: žádné přechody na dlaždicích, kartách a tlačítkách
  {
    const ctxR = await b.newContext({ reducedMotion: 'reduce' });
    await login(ctxR, 's');
    const pg = await ctxR.newPage();
    await pg.goto(BASE + '/?view=dashboard', { waitUntil: 'load' });
    const r = await pg.evaluate(probe);
    const pg2 = await ctxS.newPage();
    await pg2.goto(BASE + '/?view=dashboard', { waitUntil: 'load' });
    const n = await pg2.evaluate(probe);
    out.motion = { reduce: r.animated, normal: n.animated };
    await ctxR.close();
  }
  await ctxT.close(); await ctxS.close();
}

async function shots(b, out) {
  for (const scheme of ['light', 'dark']) {
    const ctxs = { t: await b.newContext({ colorScheme: scheme, reducedMotion: 'reduce' }), s: await b.newContext({ colorScheme: scheme, reducedMotion: 'reduce' }) };
    await login(ctxs.t, 't'); await login(ctxs.s, 's');
    for (const p of SHOT_PAGES) {
      const pg = await ctxs[p.who].newPage();
      for (const [w, h] of SHOT_WIDTHS) {
        await pg.setViewportSize({ width: w, height: h });
        await pg.goto(BASE + p.url, { waitUntil: 'load' });
        const file = SHOTS + '/' + PREFIX + p.id + '-' + w + '-' + scheme + '.jpg';
        const size = await shoot(pg, file);
        out.shots.push({ file: file.split('/').pop(), size });
        out.maxShot = Math.max(out.maxShot, size);
      }
      await pg.close();
    }
    for (const c of Object.values(ctxs)) await c.close();
  }
}

(async () => {
  const out = { pages: [], density: [], hints: [], toast: [], motion: null, shots: [], maxShot: 0, errors: [] };
  const b = await chromium.launch({ executablePath: CHROME });
  try {
    if (MODE === 'checks' || MODE === 'all') await checks(b, out);
    if ((MODE === 'shots' || MODE === 'all') && SHOTS) await shots(b, out);
  } catch (e) {
    out.error = String(e && e.message || e).slice(0, 300);
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})();
