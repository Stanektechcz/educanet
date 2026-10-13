/* EDUCANET v78 · design systém – chování komponent (vanilla, bez závislostí, bez innerHTML).
   Záložky (role tab, šipky/Home/End), <dialog> a drawer (data-ds-open / data-ds-close, klik na pozadí), toast (data-ds-toast). */
(function () {
  'use strict';
  var TOAST_MS = 5000;

  function initTabs(root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    function select(tab, focus) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        var panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
      });
      if (focus) tab.focus();
    }
    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { select(tab, false); });
      tab.addEventListener('keydown', function (e) {
        var next = null;
        if (e.key === 'ArrowRight') next = tabs[(i + 1) % tabs.length];
        else if (e.key === 'ArrowLeft') next = tabs[(i - 1 + tabs.length) % tabs.length];
        else if (e.key === 'Home') next = tabs[0];
        else if (e.key === 'End') next = tabs[tabs.length - 1];
        if (next) { e.preventDefault(); select(next, true); }
      });
    });
    var current = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0];
    if (current) select(current, false);
  }

  function toast(text) {
    var region = document.querySelector('.ds-toasts');
    if (!region || !text) return;
    var box = document.createElement('div');
    box.className = 'ds-toast';
    var msg = document.createElement('span');
    msg.textContent = text;
    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'ds-btn ds-btn--icon';
    close.setAttribute('aria-label', 'Zavřít oznámení');
    close.textContent = '×';
    close.addEventListener('click', function () { box.remove(); });
    box.appendChild(msg);
    box.appendChild(close);
    region.appendChild(box);
    window.setTimeout(function () { box.remove(); }, TOAST_MS);
  }

  document.addEventListener('click', function (e) {
    var el = e.target instanceof Element ? e.target : null;
    if (!el) return;
    var opener = el.closest('[data-ds-open]');
    if (opener) {
      var dlg = document.getElementById(opener.getAttribute('data-ds-open'));
      if (dlg && typeof dlg.showModal === 'function') { e.preventDefault(); dlg.showModal(); }
      return;
    }
    var closer = el.closest('[data-ds-close]');
    if (closer) {
      var d = closer.closest('dialog');
      if (d) { e.preventDefault(); d.close(closer.getAttribute('data-ds-close') || ''); }
      return;
    }
    if (el.tagName === 'DIALOG' && el.open && el.matches('.ds-dialog, .ds-drawer')) {
      var r = el.getBoundingClientRect();
      if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) el.close('');
      return;
    }
    var t = el.closest('[data-ds-toast]');
    if (t) toast(t.getAttribute('data-ds-toast'));
  });

  document.querySelectorAll('[data-ds-tabs]').forEach(initTabs);
  window.ds78Toast = toast;
})();
