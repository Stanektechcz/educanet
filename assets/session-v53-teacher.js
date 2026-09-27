/* EDUCANET v53 · promítnutí kódu hodiny na tabuli */
(() => {
  'use strict';
  const box = document.querySelector('[data-s53-projection]');
  if (!box) return;
  const open = () => { box.hidden = false; box.requestFullscreen?.().catch(() => {}); };
  const close = () => { box.hidden = true; if (document.fullscreenElement) document.exitFullscreen?.(); };
  document.querySelector('[data-s53-project]')?.addEventListener('click', open);
  box.querySelector('[data-s53-projection-close]')?.addEventListener('click', close);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !box.hidden) close(); });
})();
