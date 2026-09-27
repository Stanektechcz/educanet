/* EDUCANET v54 · přepínání přihlášení / nový účet */
(() => {
  'use strict';
  const tabs = [...document.querySelectorAll('[data-auth54-tab]')];
  const panes = [...document.querySelectorAll('[data-auth54-pane]')];
  if (!tabs.length) return;
  const show = (name) => {
    tabs.forEach((t) => { const on = t.dataset.auth54Tab === name; t.classList.toggle('active', on); t.setAttribute('aria-selected', on ? 'true' : 'false'); });
    panes.forEach((p) => { const on = p.dataset.auth54Pane === name; p.hidden = !on; p.classList.toggle('active', on); });
    if (location.hash !== '#' + name) history.replaceState(null, '', '#' + name);
    panes.find((p) => !p.hidden)?.querySelector('input:not([type=hidden])')?.focus({ preventScroll: true });
  };
  tabs.forEach((t) => t.addEventListener('click', () => show(t.dataset.auth54Tab)));
  const hash = (location.hash || '').replace('#', '');
  if (hash === 'register' || hash === 'local-register') show('register');
})();
