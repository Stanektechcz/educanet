/* EDUCANET v75 · tlačítko „Vytisknout nebo uložit jako PDF“ na stránkách k tisku (portfolio, opakování). Bez knihoven, nic neodesílá. */
(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var target = event.target;
    if (target && target.closest && target.closest('[data-pr75-print]')) {
      event.preventDefault();
      window.print();
    }
  });
})();
