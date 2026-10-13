/* EDUCANET v78.1 · navigace cockpitu: drawer pod 1024 px (Menu / Více) s fokusem uvnitř, Esc a inert zbytku stránky,
   „Zpět“ na předchozí stránku cockpitu (zachová třídu, filtr, týden) a odznak „Komu pomoct“ z karty Dnešní hodiny. Bez innerHTML. */
(function () {
  'use strict';
  var side = document.getElementById('t68-side');
  var root = document.documentElement;
  var wide = window.matchMedia('(min-width: 1024px)');
  var opener = null;

  function openers() { return Array.prototype.slice.call(document.querySelectorAll('[data-n781-open]')); }
  function isOpen() { return root.classList.contains('n781-open'); }
  function outside() { return Array.prototype.slice.call(document.querySelectorAll('.t68-top, .t68-main, .n781-bottom')); }
  function focusables() {
    return Array.prototype.slice.call(side.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), summary'))
      .filter(function (el) { return el.getClientRects().length > 0; });
  }
  function setOpen(on, restore) {
    root.classList.toggle('n781-open', on);
    if (on) {
      side.setAttribute('role', 'dialog'); side.setAttribute('aria-modal', 'true'); side.setAttribute('aria-labelledby', 'n781-title');
    } else {
      side.removeAttribute('role'); side.removeAttribute('aria-modal'); side.removeAttribute('aria-labelledby');
    }
    outside().forEach(function (el) { el.inert = on; });
    openers().forEach(function (o) { o.setAttribute('aria-expanded', on ? 'true' : 'false'); });
    if (on) {
      var cur = side.querySelector('a[aria-current="page"]') || focusables()[0];
      if (cur) cur.focus();
    } else {
      if (location.hash === '#t68-side') history.replaceState(null, '', location.pathname + location.search);
      if (restore !== false && opener) opener.focus();
    }
  }

  if (side) {
    document.addEventListener('click', function (e) {
      var t = e.target instanceof Element ? e.target : null;
      if (!t) return;
      var o = t.closest('[data-n781-open]');
      if (o) {
        if (wide.matches) return;
        e.preventDefault();
        if (isOpen()) { setOpen(false); } else { opener = o; setOpen(true); }
        return;
      }
      if (!isOpen()) return;
      if (t.closest('[data-n781-close]')) { e.preventDefault(); setOpen(false); return; }
      if (t.closest('[data-n781-search]')) { setOpen(false, false); return; }   // paleta ⌘K si fokus vezme sama
      if (!side.contains(t)) setOpen(false);
    });
    document.addEventListener('keydown', function (e) {
      if (!isOpen()) return;
      if (e.key === 'Escape') { e.preventDefault(); setOpen(false); return; }
      if (e.key !== 'Tab') return;
      var f = focusables();
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1], a = document.activeElement;
      if (e.shiftKey && (a === first || !side.contains(a))) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && (a === last || !side.contains(a))) { e.preventDefault(); first.focus(); }
    });
    var onWide = function () { if (wide.matches && isOpen()) setOpen(false, false); };
    if (wide.addEventListener) wide.addEventListener('change', onWide); else if (wide.addListener) wide.addListener(onWide);
    if (location.hash === '#t68-side' && !wide.matches) { opener = document.querySelector('[data-n781-open]'); setOpen(true); }
  }

  var back = document.querySelector('[data-n781-back]');
  if (back && document.referrer) {
    try {
      var ref = new URL(document.referrer);
      if (ref.origin === location.origin && /\/teacher\.php$/.test(ref.pathname) && ref.href !== location.href) {
        back.href = ref.href;
        back.addEventListener('click', function (e) { if (history.length > 1) { e.preventDefault(); history.back(); } });
      }
    } catch (err) { /* neplatný referrer – zůstane záložní odkaz */ }
  }

  var card = document.querySelector('[data-n781-help]');
  if (card) {
    var n = parseInt(card.getAttribute('data-n781-help'), 10) || 0;
    var label = card.getAttribute('data-n781-help-label') || '';
    document.querySelectorAll('a[data-n781-tab="hodina"]').forEach(function (link) {
      var b = link.querySelector('[data-n781-b="hodina"]');
      if (n <= 0) { if (b) b.remove(); return; }
      if (!b) {
        b = document.createElement('b');
        b.className = 't68-badge';
        b.setAttribute('data-n781-b', 'hodina');
        link.appendChild(b);
      }
      b.textContent = String(n);
      if (label) b.setAttribute('aria-label', label);
    });
  }
})();
