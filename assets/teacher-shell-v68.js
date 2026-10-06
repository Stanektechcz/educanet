/* EDUCANET v68 · rámec cockpitu: Esc zavře menu na mobilu, správa fokusu po otevření, sbalení bočního panelu. Bez JS menu funguje (details). */
(function () {
  'use strict';
  var frame = document.querySelector('[data-t68-frame]');
  var menu = document.querySelector('details.t68-menu');
  if (menu) {
    document.addEventListener('keydown', function (ev) {
      if (ev.key !== 'Escape' || !menu.open) return;
      menu.open = false;
      var summary = menu.querySelector('summary');
      if (summary) summary.focus();
    });
    menu.addEventListener('toggle', function () {
      if (!menu.open) return;
      var current = menu.querySelector('a[aria-current="page"]') || menu.querySelector('.t68-nav a');
      if (current) current.focus();
    });
  }
  var toggle = document.querySelector('[data-t68-collapse]');
  if (frame && toggle) {
    var KEY = 't68_side_collapsed';
    var set = function (collapsed) {
      frame.classList.toggle('is-collapsed', collapsed);
      toggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
      toggle.textContent = collapsed ? 'Zobrazit panel' : 'Sbalit panel';
    };
    var stored = null;
    try { stored = window.localStorage.getItem(KEY); } catch (e) { stored = null; }
    set(stored === '1');
    toggle.hidden = false;
    toggle.addEventListener('click', function () {
      var next = !frame.classList.contains('is-collapsed');
      set(next);
      try { window.localStorage.setItem(KEY, next ? '1' : '0'); } catch (e) { /* úložiště nemusí být dostupné */ }
    });
  }
})();
