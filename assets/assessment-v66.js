/* EDUCANET v66 · tlačítko „Tisk / PDF“ v cockpitu (bez knihoven, bez blokování renderu – skript je defer). */
(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var target = event.target instanceof Element ? event.target.closest('[data-a66-print]') : null;
    if (target) { event.preventDefault(); window.print(); }
  });
})();
