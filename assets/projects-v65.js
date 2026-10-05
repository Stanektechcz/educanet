/* v65 · pomocné prvky formulářů projektů: počítadlo znaků, nápověda vět, součet bodů, tisk. Jen textContent / value (nic se nevkládá jako HTML). */
(function () {
  'use strict';
  document.querySelectorAll('textarea[data-p65-max]').forEach(function (area) {
    var out = document.getElementById(area.getAttribute('data-p65-count') || '');
    var max = parseInt(area.getAttribute('data-p65-max') || '0', 10);
    var sync = function () { if (out) out.textContent = area.value.length + ' / ' + max; };
    area.addEventListener('input', sync);
    sync();
  });
  document.querySelectorAll('button[data-p65-starter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var area = document.getElementById(btn.getAttribute('data-p65-target') || '');
      if (!area) return;
      var text = btn.getAttribute('data-p65-starter') || '';
      area.value = area.value === '' ? text : area.value.replace(/\s+$/, '') + ' ' + text;
      area.focus();
      area.dispatchEvent(new Event('input'));
    });
  });
  document.querySelectorAll('form[data-p65-split]').forEach(function (form) {
    var out = form.querySelector('[data-p65-sum]');
    var inputs = form.querySelectorAll('input[type=number]');
    var sync = function () {
      var sum = 0;
      inputs.forEach(function (i) { sum += parseInt(i.value || '0', 10) || 0; });
      if (out) out.textContent = String(sum) + ' / 100';
    };
    inputs.forEach(function (i) { i.addEventListener('input', sync); });
    sync();
  });
  document.querySelectorAll('button[data-p65-print]').forEach(function (btn) {
    btn.classList.add('on');
    btn.addEventListener('click', function () { window.print(); });
  });
})();
