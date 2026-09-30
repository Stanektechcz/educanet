(function () {
  'use strict';

  // v60: progresivní vylepšení profilu. Bez JS zůstává vše funkční (formulář se odešle, filtr je skrytý).

  // Po přechodu na záložku (?tab=…) přesune fokus na nadpis, aby čtečka ohlásila novou záložku.
  function focusPanelTitle() {
    var params = new URLSearchParams(window.location.search);
    if (!params.has('tab')) return;
    var title = document.getElementById('p60-panel-title');
    if (title) title.focus();
  }

  // Počitadlo znaků: přepíše jen úvodní číslo (text včetně jazyka dodává server).
  function initCounters(root) {
    root.querySelectorAll('[data-p60-count-for]').forEach(function (counter) {
      var field = document.getElementById(counter.getAttribute('data-p60-count-for'));
      if (!field) return;
      var max = parseInt(counter.getAttribute('data-max') || '0', 10);
      function update() {
        var length = Array.from(field.value).length;
        counter.textContent = counter.textContent.replace(/^\d+/, String(length));
        counter.classList.toggle('is-near', max > 0 && length >= max * 0.9);
      }
      field.addEventListener('input', update);
      update();
    });
  }

  // Limit výběru (max 3 odznaky / 5 skills): po dosažení limitu se nevybrané volby zablokují.
  function initPickLimits(root) {
    root.querySelectorAll('[data-p60-max]').forEach(function (group) {
      var max = parseInt(group.getAttribute('data-p60-max') || '0', 10);
      var boxes = Array.from(group.querySelectorAll('input[type="checkbox"]'));
      function apply() {
        var checked = boxes.filter(function (box) { return box.checked; }).length;
        boxes.forEach(function (box) { box.disabled = !box.checked && checked >= max; });
      }
      group.addEventListener('change', apply);
      apply();
    });
  }

  // Upozornění na neuložené změny (aria-live oblast vedle lepivého tlačítka).
  function initDirty(root) {
    var form = root.querySelector('[data-p60-form]');
    var status = root.querySelector('[data-p60-status]');
    if (!form || !status) return;
    function mark() { status.textContent = form.getAttribute('data-dirty-text') || ''; }
    form.addEventListener('input', mark, { once: true });
    form.addEventListener('change', mark, { once: true });
  }

  // Filtr sbírky odznaků: Všechny / Získané / Zamčené + hlášení počtu zobrazených.
  function initFilter(filter) {
    var section = filter.closest('section');
    if (!section) return;
    var cards = Array.from(section.querySelectorAll('.b60-card'));
    var buttons = Array.from(filter.querySelectorAll('[data-b60-show]'));
    var status = filter.querySelector('[data-b60-status]');
    filter.hidden = false;
    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var mode = button.getAttribute('data-b60-show');
        var shown = 0;
        cards.forEach(function (card) {
          var visible = mode === 'all' || card.getAttribute('data-b60-state') === mode;
          card.hidden = !visible;
          if (visible) shown += 1;
        });
        buttons.forEach(function (other) { other.setAttribute('aria-pressed', other === button ? 'true' : 'false'); });
        if (status) {
          status.textContent = (status.getAttribute('data-tpl') || '')
            .replace('{shown}', String(shown)).replace('{total}', String(cards.length));
        }
      });
    });
  }

  function init() {
    focusPanelTitle();
    var settings = document.querySelector('[data-p60-settings]');
    if (settings) { initCounters(settings); initPickLimits(settings); initDirty(settings); }
    document.querySelectorAll('[data-b60-filter]').forEach(initFilter);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
