/* EDUCANET v73 · Tisk hodiny (tlačítko Vytisknout) a Projektor (úseky ← →, časovač mezerníkem, F celá obrazovka).
   Bez JS stránky fungují dál: tisk přes Ctrl+P, úseky přes kotvy (:target), čas konce úseku je vypsaný serverem. Žádná data se neodesílají. */
(function () {
  'use strict';
  Array.prototype.forEach.call(document.querySelectorAll('[data-lp73-print]'), function (b) {
    b.hidden = false;
    b.addEventListener('click', function () { window.print(); });
  });
  var root = document.querySelector('[data-pj73]');
  if (!root) return;
  var segs = Array.prototype.slice.call(root.querySelectorAll('.pj73-seg'));
  var links = Array.prototype.slice.call(root.querySelectorAll('.pj73-steps a'));
  var live = root.querySelector('[data-pj73-live]');
  var offset = Date.now() - (parseInt(root.getAttribute('data-now'), 10) || 0) * 1000;
  var shown = parseInt(root.getAttribute('data-current'), 10) || 0;
  var timer = { seg: -1, end: 0, left: 0 };
  root.classList.add('pj73-js');

  function indexOfHash() {
    var id = location.hash.slice(1);
    for (var i = 0; i < segs.length; i++) if (segs[i].id === id) return i;
    return -1;
  }
  function show(i, focus) {
    if (i < 0 || i >= segs.length) return;
    shown = i;
    segs.forEach(function (s, k) { s.classList.toggle('is-shown', k === i); });
    links.forEach(function (a, k) { if (k === i) a.setAttribute('aria-current', 'step'); else a.removeAttribute('aria-current'); });
    if (focus) segs[i].focus();
  }
  function fmt(ms) {
    var s = Math.max(0, Math.round(ms / 1000));
    var m = Math.floor(s / 60);
    s = s % 60;
    return m + ':' + (s < 10 ? '0' : '') + s;
  }
  function render() {
    if (timer.seg < 0) return;
    var out = segs[timer.seg] && segs[timer.seg].querySelector('[data-pj73-left]');
    if (!out) return;
    var left = timer.end ? timer.end - Date.now() : timer.left;
    out.textContent = left > 0 ? 'Zbývá ' + fmt(left) + (timer.end ? '' : ' (pozastaveno)') : 'Čas úseku vypršel';
    segs[timer.seg].classList.toggle('is-over', left <= 0);
    if (left <= 0 && timer.end) {
      timer.end = 0;
      timer.left = 0;
      if (live) live.textContent = 'Čas úseku vypršel.';
    }
  }
  function toggle() {
    var now = Date.now();
    if (timer.seg === shown && timer.end) { timer.left = timer.end - now; timer.end = 0; }
    else if (timer.seg === shown && timer.left > 0) { timer.end = now + timer.left; }
    else {
      var min = parseInt(segs[shown].querySelector('[data-pj73-timer]').getAttribute('data-minutes'), 10) || 1;
      timer = { seg: shown, end: now + min * 60000, left: 0 };
    }
    render();
  }
  segs.forEach(function (s, i) {
    var t = s.querySelector('[data-pj73-timer]');
    var ends = t && parseInt(t.getAttribute('data-ends'), 10);
    if (ends) timer = { seg: i, end: ends * 1000 + offset, left: 0 };
    var r = s.querySelector('.pj73-refresh');
    if (r) r.hidden = true;
  });
  var start = indexOfHash();
  show(start >= 0 ? start : shown, false);
  window.addEventListener('hashchange', function () { var i = indexOfHash(); if (i >= 0) show(i, true); });
  Array.prototype.forEach.call(root.querySelectorAll('[data-pj73-toggle],[data-pj73-full]'), function (b) { b.hidden = false; });
  var tg = root.querySelector('[data-pj73-toggle]');
  if (tg) tg.addEventListener('click', toggle);
  function full() {
    if (document.fullscreenElement) { if (document.exitFullscreen) document.exitFullscreen(); }
    else if (root.requestFullscreen) root.requestFullscreen().catch(function () {});
  }
  var fb = root.querySelector('[data-pj73-full]');
  if (fb) fb.addEventListener('click', full);
  document.addEventListener('keydown', function (ev) {
    if (ev.altKey || ev.ctrlKey || ev.metaKey) return;
    var tag = (ev.target && ev.target.tagName) || '';
    if (/^(INPUT|TEXTAREA|SELECT)$/.test(tag)) return;
    if (ev.key === 'ArrowRight' || ev.key === 'PageDown') { ev.preventDefault(); show(shown + 1, true); }
    else if (ev.key === 'ArrowLeft' || ev.key === 'PageUp') { ev.preventDefault(); show(shown - 1, true); }
    else if (ev.key === ' ' && !/^(A|BUTTON)$/.test(tag)) { ev.preventDefault(); toggle(); }
    else if (ev.key === 'f' || ev.key === 'F') { full(); }
  });
  render();
  window.setInterval(render, 1000);
})();
