/* EDUCANET v80.1 · Mapa třídy: počet vybraných žáků (aria-live), Vybrat všechny / zrušit / kdo potřebuje pomoc,
   vybraná fráze se zkopíruje do pole textu (lze ji upravit) a nastaví druh zprávy. Bez sítě, bez innerHTML, bez úložiště.
   Bez JS vše funguje: zaškrtávátka, fráze (pošle se, když pole textu zůstane prázdné) a tlačítka formuláře. */
(function () {
  'use strict';
  var form = document.querySelector('.cm801-form');
  if (!form) return;
  var picks = [].slice.call(form.querySelectorAll('[data-cm801-pick]'));
  var out = form.querySelector('[data-cm801-count]');
  var text = form.querySelector('#cm801-text');
  var kind = form.querySelector('#cm801-druh');
  var auto = '';

  function label(n) {
    if (!out) return '';
    if (n === 0) return out.getAttribute('data-t-zero');
    var key = n === 1 ? 'data-t-one' : (n >= 2 && n <= 4 ? 'data-t-few' : 'data-t-many');
    return (out.getAttribute(key) || '').replace('{n}', String(n));
  }
  function count() {
    var n = picks.filter(function (p) { return p.checked; }).length;
    if (out) out.textContent = label(n);
  }
  picks.forEach(function (p) { p.addEventListener('change', count); });

  var bar = form.querySelector('[data-cm801-pickall]');
  if (bar) {
    bar.hidden = false;
    [].slice.call(bar.querySelectorAll('[data-cm801-all]')).forEach(function (btn) {
      btn.addEventListener('click', function () {
        var on = btn.getAttribute('data-cm801-all') === '1';
        picks.forEach(function (p) { p.checked = on; });
        count();
      });
    });
    var weak = bar.querySelector('[data-cm801-weak]');
    if (weak) weak.addEventListener('click', function () {
      picks.forEach(function (p) {
        var row = p.closest('tr');
        p.checked = !!(row && row.querySelector('.cm801-c.is-low'));
      });
      count();
    });
  }

  [].slice.call(form.querySelectorAll('input[name="fraze"]')).forEach(function (radio) {
    radio.addEventListener('change', function () {
      if (!radio.checked || !text) return;
      if (text.value === '' || text.value === auto) {
        auto = radio.getAttribute('data-text') || '';
        text.value = auto;
      }
      if (kind && radio.getAttribute('data-kind')) kind.value = radio.getAttribute('data-kind');
    });
  });
  count();
})();
