(function () {
  'use strict';

  // v60.2: progresivní vylepšení (bez JS vše funguje). Hlášky dodává server v data-*, sem jen přes textContent.

  // Po uložení / akci (zpráva ve stavu) přesune fokus na ni, po přechodu na záložku na nadpis profilu.
  function focusStart() {
    var target = document.querySelector('.p60-flash');
    if (!target && new URLSearchParams(window.location.search).has('tab')) target = document.getElementById('p60-panel-title');
    if (!target) return;
    target.setAttribute('tabindex', '-1');
    target.focus({ preventScroll: false });
  }

  function charCount(value) { return Array.from(value).length; }

  // Počitadlo znaků: přepíše jen úvodní číslo (text včetně jazyka dodává server).
  function initCounters(root) {
    root.querySelectorAll('[data-p60-count-for]').forEach(function (counter) {
      var field = document.getElementById(counter.getAttribute('data-p60-count-for'));
      if (!field) return;
      var max = parseInt(counter.getAttribute('data-max') || '0', 10);
      function update() {
        var length = charCount(field.value);
        counter.textContent = counter.textContent.replace(/^\d+/, String(length));
        counter.classList.toggle('is-near', max > 0 && length >= max * 0.9);
      }
      field.addEventListener('input', update);
      update();
    });
  }

  // Štítky (dovednosti, zájmy): server uloží jen prvních N položek o nejvýš M znacích – upozorníme dopředu.
  function initTags(root, form) {
    root.querySelectorAll('[data-p60-tags]').forEach(function (field) {
      var maxItems = parseInt(field.getAttribute('data-p60-tags'), 10);
      var maxLen = parseInt(field.getAttribute('data-p60-taglen'), 10);
      var error = document.getElementById(field.id + '-error');
      if (!error) return;
      function check() {
        var items = field.value.split(/[,;\n]+/).map(function (s) { return s.trim(); }).filter(Boolean);
        var message = '';
        if (items.length > maxItems) message = (form.getAttribute('data-msg-tags-many') || '').replace('{max}', String(maxItems));
        else if (items.some(function (s) { return charCount(s) > maxLen; })) message = (form.getAttribute('data-msg-tag-long') || '').replace('{max}', String(maxLen));
        error.textContent = message;
        field.setAttribute('aria-invalid', message ? 'true' : 'false');
      }
      field.addEventListener('input', check);
      check();
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

  // Živý náhled motta v kartě „Takto tě vidí spolužáci“.
  function initPreview(root) {
    var out = root.querySelector('[data-p60-preview="headline"]');
    var field = root.querySelector('[data-p60-live="headline"]');
    if (!out || !field) return;
    field.addEventListener('input', function () {
      out.textContent = field.value.trim() || out.getAttribute('data-empty') || '';
    });
  }

  // Neuložené změny: hláška vedle lepivého tlačítka + upozornění při odchodu ze stránky.
  function initDirty(root, form) {
    var status = root.querySelector('[data-p60-status]');
    var dirty = false;
    var submitting = false;
    function mark() {
      dirty = true;
      if (status) status.textContent = form.getAttribute('data-dirty-text') || '';
    }
    form.addEventListener('input', mark);
    form.addEventListener('change', mark);
    form.addEventListener('submit', function () { submitting = true; });
    window.addEventListener('beforeunload', function (event) {
      if (!dirty || submitting) return;
      event.preventDefault();
      event.returnValue = '';
    });
  }

  function initSettings(root) {
    var form = root.querySelector('[data-p60-form]');
    initCounters(root);
    initPickLimits(root);
    initPreview(root);
    if (form) { initTags(root, form); initDirty(root, form); }
  }

  // Filtr sbírky odznaků: Všechny / Získané / Zamčené + hlášení počtu zobrazených.
  function initFilter(filter) {
    var section = filter.closest('section');
    if (!section) return;
    var cards = Array.from(section.querySelectorAll('.b60-card, .b60-row'));
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
        section.querySelectorAll('[data-b60-more]').forEach(function (more) { more.open = mode === 'locked'; });
        buttons.forEach(function (other) { other.setAttribute('aria-pressed', other === button ? 'true' : 'false'); });
        if (status) status.textContent = (status.getAttribute('data-tpl') || '').replace('{shown}', String(shown)).replace('{total}', String(cards.length));
      });
    });
  }

  function init() {
    focusStart();
    var settings = document.querySelector('[data-p60-settings]');
    if (settings) initSettings(settings);
    document.querySelectorAll('[data-b60-filter]').forEach(initFilter);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
