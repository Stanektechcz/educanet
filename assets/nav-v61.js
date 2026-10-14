/* EDUCANET v61 · podmenu horní navigace. Tlačítko přepíná aria-expanded, Esc zavře a vrátí fokus, šipky procházejí položky.
   Bez tohoto skriptu funguje podmenu přes :hover / :focus-within (viz nav-v61.css). Skript jen přepíná třídy a atributy, žádné vkládání HTML. */
/* v80.1: sdílené značky menu účtu – drawer (mobil) a přepínač jazyka se klonují z horní lišty a patičky (cloneNode, žádné HTML z řetězců). */
(function () {
  'use strict';
  var drawer = document.querySelector('.nav61-drawer[data-nav61-skip]');
  if (drawer && !drawer.firstChild) {
    var skip = (drawer.getAttribute('data-nav61-skip') || '').split(' ');
    [].slice.call(document.querySelectorAll('.nav61-main li.nav61-group')).forEach(function (group) {
      var label = group.querySelector('.nav61-label');
      var links = [].slice.call(group.querySelectorAll('.nav61-panel a')).filter(function (a) { return skip.indexOf(a.getAttribute('href')) === -1; });
      if (!links.length) return;
      var head = document.createElement('span');
      head.className = drawer.getAttribute('data-nav61-head') || 'v56-menu-label';
      head.textContent = label ? label.textContent : '';
      drawer.appendChild(head);
      links.forEach(function (a) { drawer.appendChild(a.cloneNode(true)); });
    });
  }
  var langSlot = document.querySelector('[data-nav61-lang]');
  var langForm = document.querySelector('footer .edu-lang-switcher');
  if (langSlot && langForm && !langSlot.firstChild) langSlot.appendChild(langForm.cloneNode(true));
})();
(function () {
  'use strict';
  var groups = [].slice.call(document.querySelectorAll('li.nav61-group'));
  if (!groups.length) return;
  document.documentElement.classList.add('nav61-js');

  function trigger(group) { return group.querySelector('.nav61-trigger'); }
  function links(group) { return [].slice.call(group.querySelectorAll('.nav61-panel a')); }
  function setOpen(group, open) {
    group.classList.toggle('is-open', open);
    trigger(group).setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  function closeAll(except) {
    groups.forEach(function (g) { if (g !== except && g.classList.contains('is-open')) setOpen(g, false); });
  }

  groups.forEach(function (group) {
    var btn = trigger(group);
    btn.addEventListener('click', function () {
      var open = !group.classList.contains('is-open');
      closeAll(group);
      setOpen(group, open);
    });
    group.addEventListener('keydown', function (ev) {
      var items = links(group);
      var pos = items.indexOf(document.activeElement);
      if (ev.key === 'Escape' && group.classList.contains('is-open')) {
        setOpen(group, false);
        btn.focus();
        ev.preventDefault();
      } else if (ev.key === 'ArrowDown' && items.length) {
        if (!group.classList.contains('is-open')) { closeAll(group); setOpen(group, true); }
        items[pos < 0 ? 0 : Math.min(pos + 1, items.length - 1)].focus();
        ev.preventDefault();
      } else if (ev.key === 'ArrowUp' && pos >= 0) {
        (pos === 0 ? btn : items[pos - 1]).focus();
        ev.preventDefault();
      }
    });
    group.addEventListener('focusout', function (ev) {
      if (ev.relatedTarget && !group.contains(ev.relatedTarget)) setOpen(group, false);
    });
  });

  document.addEventListener('click', function (ev) {
    if (!ev.target.closest || !ev.target.closest('li.nav61-group')) closeAll(null);
  });
})();
