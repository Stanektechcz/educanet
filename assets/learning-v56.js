/* EDUCANET v56 · moderní dropdowny v menu a filtr v materiálech */
(function () {
  'use strict';

  // -------------------------------------------------------------------
  // Dropdowny (disclosure): klik otevře, klik jinam / odchod fokusu / Escape zavře,
  // šipky procházejí. Bez rolí menu – panel je obyčejný seznam odkazů.
  // -------------------------------------------------------------------
  function setupDropdowns() {
    var menus = [].slice.call(document.querySelectorAll('[data-v56-menu]'));
    if (!menus.length) return;

    var closeAll = function (except) {
      menus.forEach(function (menu) {
        if (menu === except) return;
        menu.classList.remove('is-open');
        var btn = menu.querySelector('[data-v56-menu-button]');
        if (btn) btn.setAttribute('aria-expanded', 'false');
      });
    };

    // Jen viditelné položky (odkazy .v55-mobile-only jsou na desktopu skryté a nejdou zaměřit).
    var visibleItems = function (panel) {
      return [].slice.call(panel.querySelectorAll('a, button')).filter(function (el) {
        return el.getClientRects().length > 0;
      });
    };

    menus.forEach(function (menu) {
      var button = menu.querySelector('[data-v56-menu-button]');
      var panel = menu.querySelector('[data-v56-menu-panel]');
      if (!button || !panel) return;
      button.setAttribute('aria-expanded', 'false');
      if (!panel.id) panel.id = 'v56-menu-panel-' + menus.indexOf(menu);
      button.setAttribute('aria-controls', panel.id);

      button.addEventListener('click', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        var open = menu.classList.contains('is-open');
        closeAll(menu);
        menu.classList.toggle('is-open', !open);
        button.setAttribute('aria-expanded', open ? 'false' : 'true');
        if (!open) {
          var first = visibleItems(panel)[0];
          if (first) first.focus({ preventScroll: true });
        }
      });

      // Fokus odešel mimo menu (Tab / Shift+Tab) → zavřít.
      menu.addEventListener('focusout', function (ev) {
        if (!menu.classList.contains('is-open')) return;
        var to = ev.relatedTarget;
        if (to && menu.contains(to)) return;
        menu.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
      });

      menu.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') {
          if (!menu.classList.contains('is-open')) return;
          ev.preventDefault();
          menu.classList.remove('is-open');
          button.setAttribute('aria-expanded', 'false');
          button.focus({ preventScroll: true });
          return;
        }
        if (ev.key !== 'ArrowDown' && ev.key !== 'ArrowUp') return;
        var target = ev.target;
        if (target && (['SELECT', 'INPUT', 'TEXTAREA'].indexOf(target.tagName) !== -1 || (target.closest && target.closest('.edu-lang-switcher')))) return;
        var items = visibleItems(panel);
        if (!items.length) return;
        ev.preventDefault();
        var i = items.indexOf(document.activeElement);
        var next = ev.key === 'ArrowDown' ? i + 1 : i - 1;
        if (next < 0) next = items.length - 1;
        if (next >= items.length) next = 0;
        items[next].focus({ preventScroll: true });
      });
    });

    document.addEventListener('click', function (ev) {
      if (ev.target.closest && ev.target.closest('[data-v56-menu]')) return;
      closeAll(null);
    });
  }

  // -------------------------------------------------------------------
  // Filtr v materiálech
  // -------------------------------------------------------------------
  function setupFilter() {
    var input = document.querySelector('[data-v56-filter]');
    if (!input) return;
    var items = [].slice.call(document.querySelectorAll('[data-v56-item]'));
    if (!items.length) return;
    input.addEventListener('input', function () {
      var uiLocale = (window.EduI18n && window.EduI18n.locale) || 'cs';
      var q = input.value.toLocaleLowerCase(uiLocale === 'cs' ? 'cs-CZ' : (uiLocale === 'uk' ? 'uk-UA' : 'en-GB')).trim();
      items.forEach(function (item) {
        var hay = item.getAttribute('data-v56-item') || '';
        item.classList.toggle('is-hidden', q !== '' && hay.indexOf(q) === -1);
      });
    });
  }

  function init() {
    setupDropdowns();
    setupFilter();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
