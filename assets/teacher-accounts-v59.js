/* v59 · AUTHZ58-07 – učitelské účty: potvrzení akcí, tisk kartičky, výběr předmětu jen u zaškrtnuté třídy.
 * Bez vkládání HTML – mění se jen atributy a volá se confirm()/print(). */
(function () {
  'use strict';

  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    var button = event.submitter || form.querySelector('[data-t59-confirm]');
    var message = button && button.getAttribute ? button.getAttribute('data-t59-confirm') : null;
    if (message && !window.confirm(message)) event.preventDefault();
  });

  document.addEventListener('click', function (event) {
    var target = event.target instanceof Element ? event.target.closest('[data-t59-print]') : null;
    if (target) { event.preventDefault(); window.print(); }
  });

  function syncSubject(checkbox) {
    var row = checkbox.closest('.t59-class-row');
    var select = row ? row.querySelector('select') : null;
    if (select) select.disabled = !checkbox.checked;
  }

  function init() {
    document.querySelectorAll('[data-t59-class]').forEach(function (checkbox) {
      syncSubject(checkbox);
      checkbox.addEventListener('change', function () { syncSubject(checkbox); });
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
