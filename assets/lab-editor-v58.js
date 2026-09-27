// EDUCANET v58 · Editor úrovní Linux Labu (TCH-01) – přidávání řádků formuláře (soubory, generátory, kontroly).
// Čistě progresivní vylepšení: bez JS jde formulář odeslat s předvyplněným počtem řádků beze změny funkčnosti.
// Jen DOM API (cloneNode/querySelectorAll), žádné innerHTML se vstupem od uživatele.
(function () {
  'use strict';

  var GROUPS = {
    file: { container: '[data-lab58e-file-rows]', row: '.lab58e-file-row', prefix: 'files' },
    gen: { container: '[data-lab58e-gen-rows]', row: '.lab58e-gen-row', prefix: 'generators' },
    check: { container: '[data-lab58e-check-rows]', row: '.lab58e-check-row', prefix: 'checks' }
  };

  function nextIndex(container, prefix) {
    var max = -1;
    container.querySelectorAll('[name^="' + prefix + '["]').forEach(function (el) {
      var m = /\[(\d+)]/.exec(el.getAttribute('name') || '');
      if (m) max = Math.max(max, parseInt(m[1], 10));
    });
    return max + 1;
  }

  function reindexClone(clone, prefix, idx) {
    clone.querySelectorAll('[name]').forEach(function (el) {
      el.setAttribute('name', el.getAttribute('name').replace(/\[\d+]/, '[' + idx + ']'));
      if (el.tagName === 'TEXTAREA') { el.textContent = ''; }
      else if (el.tagName === 'SELECT') { el.selectedIndex = 0; }
      else { el.value = ''; }
      // v58 A11Y-A10: opakovaná pole mají popisek jen v placeholderu – klon zdědí i aria-label
      // předchozího řádku, takže poslední číslo v něm přepíšeme na novou (1-based) pozici.
      if (el.hasAttribute('aria-label')) {
        el.setAttribute('aria-label', el.getAttribute('aria-label').replace(/\d+(?!.*\d)/, String(idx + 1)));
      }
    });
  }

  function addRow(kind) {
    var cfg = GROUPS[kind];
    if (!cfg) return;
    var container = document.querySelector(cfg.container);
    if (!container) return;
    var rows = container.querySelectorAll(cfg.row);
    var last = rows[rows.length - 1];
    if (!last) return;
    var idx = nextIndex(container, cfg.prefix);
    var clone = last.cloneNode(true);
    reindexClone(clone, cfg.prefix, idx);
    container.appendChild(clone);
    var firstField = clone.querySelector('input, select, textarea');
    if (firstField) firstField.focus();
  }

  document.addEventListener('click', function (event) {
    var btn = event.target.closest ? event.target.closest('[data-lab58e-add]') : null;
    if (!btn) return;
    event.preventDefault();
    addRow(btn.getAttribute('data-lab58e-add'));
  });
})();
