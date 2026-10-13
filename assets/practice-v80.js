/* EDUCANET v80 · interaktivní cvičení: živé pomůcky (maska a blok, rwx ↔ osmičkově, náhled barvy, porovnání specificity).
   Progresivní vylepšení: formulář funguje i bez JS a odpověď kontroluje server. Žádná síť, žádné innerHTML, texty jen z data-t (přeložené na serveru). */
(function () {
  'use strict';
  function fill(t, map) { return String(t || '').replace(/\{(\w+)\}/g, function (m, k) { return Object.prototype.hasOwnProperty.call(map, k) ? String(map[k]) : m; }); }
  var timers = {};
  function say(el, text, key) {
    if (!el) return;
    clearTimeout(timers[key]);
    timers[key] = setTimeout(function () { el.textContent = text; }, 120);
  }
  function each(root, sel, fn) { Array.prototype.forEach.call(root.querySelectorAll(sel), fn); }
  function hex2(n) { return (n < 16 ? '0' : '') + n.toString(16); }
  function clamp(v, lo, hi) { v = parseInt(v, 10); return isNaN(v) ? lo : Math.min(hi, Math.max(lo, v)); }

  function subnet(form) {
    var input = form.querySelector('[data-w80-prefix]'), out = form.querySelector('[data-w80-calc]');
    if (!input || !out) return;
    input.addEventListener('input', function () {
      var p = clamp(input.value, 0, 32), m = [], i, k = Math.min(3, Math.floor(p / 8));
      for (i = 0; i < 4; i++) { var bits = Math.max(0, Math.min(8, p - 8 * i)); m.push(256 - Math.pow(2, 8 - bits)); }
      say(out, fill(out.getAttribute('data-t'), { mask: m.join('.'), size: Math.pow(2, 32 - p), o: k + 1, step: Math.pow(2, 8 - (p - 8 * k)) }), 'pfx');
    });
  }

  function chmod(form) {
    var boxes = form.querySelectorAll('[data-w80-bit]'), out = form.querySelector('[data-w80-perm]'), oct = form.querySelector('[data-w80-oct]');
    if (!boxes.length || !out) return;
    var letters = 'rwxrwxrwx';
    function mode() { var n = 0; Array.prototype.forEach.call(boxes, function (b) { if (b.checked) n |= parseInt(b.getAttribute('data-w80-bit'), 10); }); return n; }
    function show(n, fromOct) {
      var sym = '', i;
      for (i = 0; i < 9; i++) sym += (n & (1 << (8 - i))) ? letters.charAt(i) : '-';
      var o = ('00' + n.toString(8)).slice(-3);
      say(out, fill(out.getAttribute('data-t'), { oct: o, sym: sym }), 'perm');
      if (oct && !fromOct) oct.value = o;
    }
    Array.prototype.forEach.call(boxes, function (b) { b.addEventListener('change', function () { show(mode(), false); }); });
    if (oct) oct.addEventListener('input', function () {
      var v = oct.value.trim();
      if (!/^0?[0-7]{3}$/.test(v)) return;
      var n = parseInt(v.slice(-3), 8);
      Array.prototype.forEach.call(boxes, function (b) { b.checked = (n & parseInt(b.getAttribute('data-w80-bit'), 10)) !== 0; });
      show(n, true);
    });
    show(mode(), false);
    if (oct && mode() === 0) oct.value = '';
  }

  function color(form) {
    var out = form.querySelector('[data-w80-color]'), sw = form.querySelector('[data-w80-swatch]');
    if (!out) return;
    function val(c) { var el = form.querySelector('[data-w80-chan="' + c + '"]'); return el ? clamp(el.value, 0, 255) : 0; }
    function update() {
      var r = val('r'), g = val('g'), b = val('b'), hex = '#' + hex2(r) + hex2(g) + hex2(b);
      if (sw) sw.style.background = hex;
      say(out, fill(out.getAttribute('data-t'), { hex: hex, r: r, g: g, b: b }), 'color');
    }
    each(form, '[data-w80-chan]', function (num) {
      var range = form.querySelector('[data-w80-range="' + num.getAttribute('data-w80-chan') + '"]');
      num.addEventListener('input', function () { if (range) range.value = clamp(num.value, 0, 255); update(); });
      if (range) range.addEventListener('input', function () { num.value = range.value; update(); });
    });
    update();
  }

  function duel(form) {
    var out = form.querySelector('[data-w80-duel]');
    if (!out) return;
    function triple(side) {
      var el = form.querySelector('[data-w80-spec="' + side + '"]'), m = el ? /^\(?\s*(\d+)\s*[,;\- ]\s*(\d+)\s*[,;\- ]\s*(\d+)\s*\)?$/.exec(el.value) : null;
      return m ? [+m[1], +m[2], +m[3]] : null;
    }
    function update() {
      var a = triple('a'), b = triple('b'), i, win = '';
      if (!a || !b) { say(out, out.getAttribute('data-empty'), 'duel'); return; }
      for (i = 0; i < 3 && !win; i++) if (a[i] !== b[i]) win = a[i] > b[i] ? 'A' : 'B';
      say(out, win ? fill(out.getAttribute('data-t'), { x: win }) : out.getAttribute('data-tie'), 'duel');
    }
    each(form, '[data-w80-spec]', function (el) { el.addEventListener('input', update); });
  }

  function init() {
    each(document, 'form[data-w80]', function (form) {
      var kind = form.getAttribute('data-w80');
      if (kind === 'subnet') subnet(form);
      else if (kind === 'chmod') chmod(form);
      else if (kind === 'color') color(form);
      else if (kind === 'spec') duel(form);
    });
    var res = document.getElementById('lr80-w-result');
    if (res && res.textContent.trim() !== '' && location.hash === '#lr80-w-result') res.focus();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
}());
