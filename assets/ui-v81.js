/* EDUCANET v81 · progresivní vylepšení (vanilla, prvky jen přes createElement/textContent, bez sítě):
   1) toast po POST – hláška ze serveru ([data-v81-toast]) se ukáže jako oznámení (role=status), inline hláška zůstává bez JS,
   2) nápověda „Jak to funguje“ – ukáže se, dokud ji uživatel nezavře (localStorage jen „zavřeno“, v try/catch),
   3) přepínač hustoty tabulek Komfortní / Kompaktní (localStorage v try/catch, atribut data-v81-density na <html>). */
(function () {
  'use strict';
  var TOAST_MS = 6000;
  function store(key, val) {
    try {
      if (val === undefined) return window.localStorage.getItem(key);
      if (val === null) window.localStorage.removeItem(key); else window.localStorage.setItem(key, val);
    } catch (e) { /* úložiště nedostupné: volba platí jen do obnovení stránky */ }
    return null;
  }
  function icon(name) {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('class', 'i81');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('focusable', 'false');
    var use = document.createElementNS(ns, 'use');
    var sprite = document.documentElement.getAttribute('data-v81-sprite') || 'assets/icons-v81.svg';
    use.setAttribute('href', sprite + '#i-' + name);
    svg.appendChild(use);
    return svg;
  }

  // 1) toasty
  function region() {
    var r = document.querySelector('.v81-toasts');
    if (r) return r;
    r = document.createElement('div');
    r.className = 'v81-toasts ds78';
    r.setAttribute('role', 'status');
    r.setAttribute('aria-live', 'polite');
    document.body.appendChild(r);
    return r;
  }
  function toast(text, kind, closeLabel) {
    if (!text) return;
    var box = document.createElement('div');
    box.className = 'v81-toast is-' + (kind === 'error' ? 'error' : 'ok');
    box.appendChild(icon(kind === 'error' ? 'error' : (kind === 'info' ? 'info' : 'ok')));
    var msg = document.createElement('span');
    msg.textContent = text;
    box.appendChild(msg);
    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'v81-toast__close';
    close.setAttribute('aria-label', closeLabel || 'Zavřít oznámení');
    close.appendChild(icon('close'));
    var timer = 0;
    var remove = function () { window.clearTimeout(timer); if (box.parentNode) box.parentNode.removeChild(box); };
    var arm = function () { window.clearTimeout(timer); timer = window.setTimeout(remove, TOAST_MS); };
    close.addEventListener('click', remove);
    box.addEventListener('mouseenter', function () { window.clearTimeout(timer); });
    box.addEventListener('mouseleave', arm);
    box.addEventListener('focusin', function () { window.clearTimeout(timer); });
    box.appendChild(close);
    var r = region();
    // vložení až po vykreslení oblasti, aby ji čtečka ohlásila
    window.setTimeout(function () { r.appendChild(box); arm(); }, 60);
  }
  document.querySelectorAll('[data-v81-toast]').forEach(function (el) {
    var kind = el.getAttribute('data-v81-toast');
    var text = (el.getAttribute('data-v81-toast-text') || el.textContent || '').replace(/\s+/g, ' ').trim();
    toast(text, kind, el.getAttribute('data-v81-close'));
    if (kind !== 'error') el.classList.add('v81-toasted');   // chyba zůstává i v textu stránky
  });
  window.v81Toast = toast;

  // 2) nápověda při první návštěvě
  document.querySelectorAll('[data-v81-hint]').forEach(function (hint) {
    var key = 'v81-hint-' + hint.getAttribute('data-v81-hint');
    if (store(key) === '1') return;
    hint.hidden = false;
    var btn = hint.querySelector('[data-v81-hint-close]');
    if (!btn) return;
    btn.addEventListener('click', function () {
      store(key, '1');
      hint.hidden = true;
      var next = document.querySelector('main h1, #main-content, #t68-main');
      if (next) { if (!next.hasAttribute('tabindex')) next.setAttribute('tabindex', '-1'); next.focus(); }
    });
  });

  // 3) hustota tabulek (dvě tlačítka s aria-pressed)
  document.querySelectorAll('[data-v81-density-wrap]').forEach(function (wrap) {
    var btns = wrap.querySelectorAll('[data-v81-density]');
    var key = 'v81-density-' + (wrap.getAttribute('data-v81-who') || 'x');
    var paint = function (compact) {
      if (compact) document.documentElement.setAttribute('data-v81-density', 'compact'); else document.documentElement.removeAttribute('data-v81-density');
      btns.forEach(function (b) { b.setAttribute('aria-pressed', (b.getAttribute('data-v81-density') === 'compact') === compact ? 'true' : 'false'); });
    };
    paint(store(key) === 'compact');
    wrap.hidden = false;
    btns.forEach(function (b) {
      b.addEventListener('click', function () {
        var compact = b.getAttribute('data-v81-density') === 'compact';
        store(key, compact ? 'compact' : null);
        paint(compact);
      });
    });
  });
})();
