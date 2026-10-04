/* v61 · Přehled třídy: „Vybrat vše“ pro hromadné potvrzení hlášení (bez něj formulář funguje, jen ručně). */
(function () {
  'use strict';
  document.querySelectorAll('[data-ov61-bulk]').forEach(function (form) {
    var all = form.querySelector('[data-ov61-all]');
    var boxes = form.querySelectorAll('input[name="ids[]"]');
    if (!all) return;
    all.addEventListener('change', function () {
      boxes.forEach(function (box) { box.checked = all.checked; });
    });
    boxes.forEach(function (box) {
      box.addEventListener('change', function () {
        var checked = 0;
        boxes.forEach(function (b) { if (b.checked) checked += 1; });
        all.checked = checked === boxes.length;
      });
    });
  });
})();
