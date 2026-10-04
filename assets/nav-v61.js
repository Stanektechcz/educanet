/* EDUCANET v61 · podmenu horní navigace. Tlačítko přepíná aria-expanded, Esc zavře a vrátí fokus, šipky procházejí položky.
   Bez tohoto skriptu funguje podmenu přes :hover / :focus-within (viz nav-v61.css). Skript jen přepíná třídy a atributy, žádné vkládání HTML. */
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
