/* EDUCANET v80.2 · prohlížečová část tools/v80_2_a11y_audit.php (Chrome přes playwright-core, dočasné úložiště auditu, fiktivní žáci).
   Hlavní stránky učitele i žáka × 11 šířek × světlý/tmavý: vodorovný posun, cíle ≥ 44 px, písmo ≥ 14 px, kontrast vykresleného textu (vzorky),
   lang, hierarchie nadpisů, landmarky, kladný tabindex, tmavé pozadí; reflow 200 % (640 × 512) a 400 % (320 × 256 CSS px) bez posunu
   a s pevnými lištami ≤ 50 % výšky; pořadí a viditelnost fokusu (Tab) na 1280 a 390 px. Volitelně snímky JPEG ≤ 150 kB.
   Použití: node v80_2_a11y_browser.cjs <base-url> <klíč-učitele> <jméno-žáka-3A> <json-stránek> [adresář-snímků]. Výstup: JSON na stdout. */
'use strict';
const fs = require('fs');
let chromium;
try { ({ chromium } = require('playwright-core')); } catch (e) { console.log(JSON.stringify({ skip: 'playwright-core není dostupné (NODE_PATH)' })); process.exit(0); }
const [BASE, KEY, STUDENT, PAGES_JSON, SHOTS = ''] = process.argv.slice(2);
const CHROME = process.env.EDUCANET_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (!fs.existsSync(CHROME)) { console.log(JSON.stringify({ skip: 'Chrome nenalezen: ' + CHROME })); process.exit(0); }
const WIDTHS = [[320, 800], [375, 812], [390, 844], [414, 896], [768, 1024], [834, 1112], [1024, 768], [1280, 800], [1366, 600], [1440, 900], [1920, 1080]];
const ZOOMS = [[640, 512, 200], [320, 256, 400]];
// ladění: V802_WIDTHS=390,1280 omezí šířky (kontrola počtu měření pak záměrně selže)
const ONLY_W = (process.env.V802_WIDTHS || '').split(',').filter(Boolean).map(Number);
const SHOT_WIDTHS = [[375, 812], [768, 1024], [1280, 800], [1920, 1080]];
const PAGES = JSON.parse(fs.readFileSync(PAGES_JSON, 'utf8'));   // [{id, who: t|s|anon, url, shots: bool}]

function probe(detail) {
  const cut = detail ? 400 : 6;
  const W = document.documentElement.clientWidth;
  const H = window.innerHeight;
  const vis = (el) => {
    if (el.closest('[hidden], [aria-hidden="true"], .sr-only, .ds-sr, .c70-sr, .visually-hidden, template, noscript')) return false;
    const s = getComputedStyle(el); const r = el.getBoundingClientRect();
    return s.display !== 'none' && s.visibility !== 'hidden' && parseFloat(s.opacity) > 0.05 && r.width > 1 && r.height > 1;
  };
  const name = (el) => ((el.getAttribute('aria-label') || el.textContent || el.getAttribute('title') || el.getAttribute('name') || el.className || el.tagName) + '').replace(/\s+/g, ' ').trim().slice(0, 28);
  const where = (el) => { const sec = el.parentElement && el.parentElement.closest('[id]'); const cls = typeof el.className === 'string' && el.className.trim() ? '.' + el.className.trim().split(/\s+/).slice(0, 2).join('.') : ''; const p = el.parentElement; const pc = p && typeof p.className === 'string' && p.className.trim() ? p.tagName.toLowerCase() + '.' + p.className.trim().split(/\s+/)[0] + ' > ' : ''; return pc + el.tagName.toLowerCase() + cls + (sec ? ' in #' + sec.id : ''); };
  // 1) cíle dotyku ≥ 44 × 44 (výjimka WCAG 2.5.8 „inline“: odkaz v běžném textu odstavce/položky; skryté prvky se nepočítají)
  const small = [];
  for (const el of document.querySelectorAll('a[href], button, select, summary, textarea, input:not([type=hidden]), [role=button], [role=tab], [tabindex]:not([tabindex="-1"])')) {
    const target = el.matches('input[type=checkbox], input[type=radio]') ? (el.closest('label') || (el.id && document.querySelector('label[for="' + CSS.escape(el.id) + '"]')) || el) : el;
    if (!vis(target) || el.disabled) continue;
    const r = target.getBoundingClientRect();
    const block = el.closest('p, li, dd, td, figcaption, blockquote, .ds-help, small');
    const inline = el.tagName === 'A' && block && block.textContent.trim().length > (el.textContent || '').trim().length + 8;
    if (!inline && (r.height < 43.5 || r.width < 43.5)) small.push(name(target) + ' ' + Math.round(r.width) + '×' + Math.round(r.height) + ' ' + where(target));
  }
  // 2) písmo ≥ 14 px a 3) kontrast vykresleného textu (vzorky: prvky s vlastním textem, nejvýš 600)
  const tiny = [];
  const lowC = [];
  let sampled = 0;
  const parse = (c) => { const m = (c || '').match(/[\d.]+/g); if (!m || m.length < 3) return null; return { r: +m[0], g: +m[1], b: +m[2], a: m.length > 3 ? +m[3] : 1 }; };
  const lum = (c) => { const f = (v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }; return 0.2126 * f(c.r) + 0.7152 * f(c.g) + 0.0722 * f(c.b); };
  const blend = (top, bottom) => ({ r: top.r * top.a + bottom.r * (1 - top.a), g: top.g * top.a + bottom.g * (1 - top.a), b: top.b * top.a + bottom.b * (1 - top.a), a: 1 });
  const bgOf = (el) => {
    const layers = [];
    for (let n = el; n; n = n.parentElement) {
      const s = getComputedStyle(n);
      if (s.backgroundImage && s.backgroundImage !== 'none') return null;   // obrázek/gradient pod textem – kontrast nelze spolehlivě spočítat
      const c = parse(s.backgroundColor);
      if (c && c.a > 0) { layers.push(c); if (c.a >= 0.99) break; }
    }
    let out = { r: 255, g: 255, b: 255, a: 1 };
    for (let i = layers.length - 1; i >= 0; i--) out = blend(layers[i], out);
    return out;
  };
  for (const el of document.body.querySelectorAll('*')) {
    if (['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEMPLATE', 'svg', 'SVG', 'OPTION'].includes(el.tagName)) continue;
    if (![...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim() !== '')) continue;
    if (!vis(el)) continue;
    const s = getComputedStyle(el);
    const fs = parseFloat(s.fontSize);
    const inactive = !!el.closest(':disabled, [aria-disabled="true"], [inert]');   // neaktivní prvky: výjimka WCAG 1.4.3
    if (fs < 13.95) tiny.push(name(el) + ' ' + fs + 'px ' + where(el));
    if (sampled >= 600 || inactive) continue;
    const bg = bgOf(el);
    const fg = parse(s.color);
    if (!bg || !fg) continue;
    sampled++;
    const f = blend({ ...fg, a: fg.a * Math.min(1, parseFloat(s.opacity) || 1) }, bg);
    const L1 = lum(f); const L2 = lum(bg);
    const ratio = (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05);
    const large = fs >= 23.95 || (fs >= 18.6 && parseInt(s.fontWeight, 10) >= 700);
    if (ratio < (large ? 3 : 4.5) - 0.02) lowC.push(name(el) + ' ' + ratio.toFixed(2) + ' ' + where(el));
  }
  // 4) struktura: lang, nadpisy, landmarky, tabindex
  // nadpisy pro čtečky: i vizuálně skrytý nadpis (sr-only) se počítá, jen ne display:none / hidden / aria-hidden
  const exposed = (el) => !el.closest('[hidden], [aria-hidden="true"], template, noscript') && getComputedStyle(el).display !== 'none' && getComputedStyle(el).visibility !== 'hidden' && ![...function* () { for (let n = el.parentElement; n; n = n.parentElement) yield n; }()].some((n) => getComputedStyle(n).display === 'none');
  const hs = [...document.querySelectorAll('h1, h2, h3, h4, h5, h6')].filter(exposed).map((h) => +h.tagName[1]);
  const skips = [];
  for (let i = 1; i < hs.length; i++) if (hs[i] > hs[i - 1] + 1) skips.push('h' + hs[i - 1] + '→h' + hs[i]);
  const navs = [...document.querySelectorAll('nav, [role=navigation]')].filter(vis);
  const mains = [...document.querySelectorAll('main, [role=main]')].filter((m) => getComputedStyle(m).display !== 'none');
  const outside = [...document.body.children].filter((c) => vis(c) && !c.matches('main, header, footer, nav, aside, [role], script, style, dialog, .ds-toasts, [data-ui61-bottomnav]') && !c.querySelector('main') && c.textContent.trim().length > 40).map((c) => c.tagName.toLowerCase() + (c.className ? '.' + String(c.className).split(' ')[0] : ''));
  const lumBody = (() => { const b = bgOf(document.body) || { r: 255, g: 255, b: 255 }; return lum(b); })();
  const mainEl = mains[0] || document.body;
  const mainBg = bgOf(mainEl.querySelector('section, article, .ds-card, .card') || mainEl);
  const wide = [];
  if (detail && document.documentElement.scrollWidth > W + 1) for (const el of document.body.querySelectorAll('*')) { const r = el.getBoundingClientRect(); const p = el.parentElement ? el.parentElement.getBoundingClientRect() : null; if (r.right > W + 1 && r.width > 0 && (!p || p.right <= W + 1) && wide.length < 6) wide.push(where(el) + ' →' + Math.round(r.right)); }
  return {
    wide,
    W, H, hscroll: document.documentElement.scrollWidth > W + 1, scrollW: document.documentElement.scrollWidth,
    small: small.slice(0, cut), smallCount: small.length, tiny: tiny.slice(0, cut), tinyCount: tiny.length, lowC: lowC.slice(0, cut), lowCount: lowC.length, sampled,
    lang: document.documentElement.getAttribute('lang') || '', h1: hs.filter((h) => h === 1).length, firstH: hs[0] || 0, skips: skips.slice(0, 4),
    mains: mains.length, navs: navs.length, navUnlabeled: navs.length > 1 ? navs.filter((n) => !n.getAttribute('aria-label') && !n.getAttribute('aria-labelledby')).length : 0, outside: outside.slice(0, 3),
    posTab: document.querySelectorAll('[tabindex]:not([tabindex="0"]):not([tabindex^="-"])').length,
    darkBg: lumBody < 0.2 && (!mainBg || lum(mainBg) < 0.2), title: document.title.trim() !== '',
  };
}

function overlay() {
  const H = window.innerHeight;
  const pinned = (el) => { const p = getComputedStyle(el).position; return p === 'fixed' || p === 'sticky'; };
  const spans = [];
  for (const el of document.querySelectorAll('body *')) {
    if (!pinned(el)) continue;
    const s = getComputedStyle(el);
    if (s.display === 'none' || s.visibility === 'hidden' || el.closest('dialog:not([open])')) continue;
    let up = el.parentElement; let nested = false;
    while (up && up !== document.body) { if (pinned(up)) { nested = true; break; } up = up.parentElement; }
    if (nested) continue;
    const r = el.getBoundingClientRect();
    if (r.width < 50 || r.height < 1) continue;
    // jen lišty přilepené k hornímu/dolnímu okraji viewportu (lepicí hlavička tabulky uvnitř obsahu se nepočítá)
    if (!(r.top <= 1 || r.bottom >= H - 1)) continue;
    spans.push([Math.max(0, r.top), Math.min(H, r.bottom)]);
  }
  spans.sort((a, b) => a[0] - b[0]);
  let covered = 0; let end = -1;
  for (const [a, b] of spans) { if (b <= end) continue; covered += b - Math.max(a, end); end = b; }
  return { W: document.documentElement.clientWidth, H, hscroll: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1, covered: Math.round(covered), share: +(covered / H).toFixed(2) };
}

async function focusWalk(pg, steps) {
  await pg.evaluate(() => { document.activeElement && document.activeElement.blur && document.activeElement.blur(); window.scrollTo(0, 0); });
  const seen = [];
  for (let i = 0; i < steps; i++) {
    await pg.keyboard.press('Tab');
    const f = await pg.evaluate(() => {
      const el = document.activeElement;
      if (!el || el === document.body || el === document.documentElement) return { body: true };
      const s = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      const ow = parseFloat(s.outlineWidth) || 0;
      // datum/čas: Tab prochází i vnitřní části nativního pole (den, měsíc, rok, ikona kalendáře) – zvýraznění kreslí prohlížeč
      const nativeParts = el.matches('input[type=date], input[type=time], input[type=datetime-local], input[type=month], input[type=week]');
      const ring = (s.outlineStyle !== 'none' && ow >= 1.5) || (s.boxShadow && s.boxShadow !== 'none') || nativeParts;
      const all = [...document.querySelectorAll('*')];
      const inView = r.bottom > 0 && r.top < window.innerHeight && r.right > 0 && r.left < document.documentElement.clientWidth;
      return { idx: all.indexOf(el), ring, inView, size: r.width > 0 && r.height > 0, label: ((el.getAttribute('aria-label') || el.textContent || el.tagName) + '').replace(/\s+/g, ' ').trim().slice(0, 24), inDialog: !!el.closest('dialog[open]') };
    });
    seen.push(f);
  }
  const real = seen.filter((f) => !f.body);
  let back = 0;
  // návrat na první prvek (konec stránky → začátek) je cyklus, ne skok zpět; krátká stránka se za 25 kroků může protočit víckrát
  for (let i = 1; i < real.length; i++) if (real[i].idx < real[i - 1].idx && real[i].idx !== real[0].idx && !real[i].inDialog && !real[i - 1].inDialog) back++;
  const noRing = real.filter((f) => !f.ring || !f.size).map((f) => f.label);
  const hidden = real.filter((f) => !f.inView).map((f) => f.label);
  // cyklus přes konec stránky zpět na začátek je v pořádku (1× návrat); víc návratů = nelogické pořadí
  const out = { count: real.length, back, noRing: noRing.slice(0, 5), noRingCount: noRing.length, hidden: hidden.slice(0, 5), hiddenCount: hidden.length };
  if (process.env.V802_DETAIL) out.seq = real.map((f) => f.idx + ':' + f.label + (f.ring ? '' : '[!]'));
  return out;
}

async function shoot(pg, file) {
  for (const q of [55, 45, 35, 28, 22]) {
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
  if (who === 'anon') return;
  const pg = await ctx.newPage();
  if (who === 't') {
    await pg.goto(BASE + '/teacher.php');
    await pg.fill('input[name=teacher_name]', 'Audit Přístupnost');
    await pg.fill('input[name=teacher_key]', KEY);
    await Promise.all([pg.waitForNavigation(), pg.click('form button[type=submit]')]);
  } else {
    await pg.goto(BASE + '/?class=class_3a&student=' + encodeURIComponent(STUDENT));
  }
  await pg.close();
}

(async () => {
  const out = { rows: [], zoom: [], focus: [], shots: [], maxShot: 0, errors: [] };
  const b = await chromium.launch({ executablePath: CHROME });
  try {
    for (const scheme of ['light', 'dark']) {
      const ctxs = {};
      for (const who of ['t', 's', 'anon']) { ctxs[who] = await b.newContext({ colorScheme: scheme, reducedMotion: 'reduce' }); await login(ctxs[who], who); }
      for (const p of PAGES) {
        const pg = await ctxs[p.who].newPage();
        pg.on('pageerror', (e) => out.errors.push(p.id + ': ' + String(e).slice(0, 120)));
        for (const [w, h] of WIDTHS.filter(([x]) => ONLY_W.length === 0 || ONLY_W.includes(x))) {
          await pg.setViewportSize({ width: w, height: h });
          const resp = await pg.goto(BASE + p.url, { waitUntil: 'load' });
          const r = await pg.evaluate(probe, !!process.env.V802_DETAIL);
          out.rows.push({ page: p.id, width: w, scheme, status: resp ? resp.status() : 0, url: pg.url().replace(BASE, ''), ...r });
        }
        if (scheme === 'light') {
          for (const [w, h, z] of ZOOMS) {
            await pg.setViewportSize({ width: w, height: h });
            await pg.goto(BASE + p.url, { waitUntil: 'load' });
            out.zoom.push({ page: p.id, zoom: z, ...(await pg.evaluate(overlay)) });
          }
          for (const [w, h] of [[1280, 800], [390, 844]]) {
            await pg.setViewportSize({ width: w, height: h });
            await pg.goto(BASE + p.url, { waitUntil: 'load' });
            out.focus.push({ page: p.id, width: w, ...(await focusWalk(pg, 25)) });
          }
        }
        if (SHOTS && p.shots) {
          for (const [w, h] of SHOT_WIDTHS) {
            await pg.setViewportSize({ width: w, height: h });
            await pg.goto(BASE + p.url, { waitUntil: 'load' });
            const file = SHOTS + '/e802-' + p.id + '-' + w + '-' + scheme + '.jpg';
            const size = await shoot(pg, file);
            out.shots.push({ file: file.split('/').pop(), size });
            out.maxShot = Math.max(out.maxShot, size);
          }
        }
        await pg.close();
      }
      for (const c of Object.values(ctxs)) await c.close();
    }
  } catch (e) {
    out.error = String(e && e.message || e).slice(0, 300);
  } finally {
    await b.close();
  }
  console.log(JSON.stringify(out));
})();
