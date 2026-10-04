/* EDUCANET v63 · výukové cesty: drobná vylepšení (stránka funguje i bez JS).
   1) po posunu řádku Parsonovy úlohy se fokus vrátí na přesunuté tlačítko (id z fragmentu URL),
   2) počítadlo znaků reflexní věty. Žádná data se neposílají ani neukládají, jen textContent. */
(function () {
  'use strict';
  var hash = window.location.hash;
  if (hash && /^#p63-(up|down)-\d{1,2}$/.test(hash)) {
    var target = document.getElementById(hash.slice(1));
    if (target && typeof target.focus === 'function') target.focus();
  } else if (hash === '#p63-result') {
    var result = document.getElementById('p63-result');
    if (result && typeof result.focus === 'function') result.focus();
  }
  var field = document.querySelector('[data-p63-count]');
  var counter = document.querySelector('[data-p63-counter]');
  if (field && counter) {
    var max = parseInt(field.getAttribute('maxlength') || '200', 10);
    var update = function () { counter.textContent = field.value.length + ' / ' + max; };
    field.addEventListener('input', update);
    update();
  }
})();
