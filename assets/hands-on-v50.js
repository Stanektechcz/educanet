(() => {
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const withTransition = (fn) => {
    if (!reduceMotion && document.startViewTransition) document.startViewTransition(fn);
    else fn();
  };

  document.querySelectorAll('[data-v50-topology]').forEach((root) => {
    const nodes = [...root.querySelectorAll('.v50-node')];
    const edges = [...root.querySelectorAll('.v50-edge')];
    const inspector = root.querySelector('[data-v50-inspector]');
    const layerButtons = [...root.querySelectorAll('[data-layer]')];
    const selectLayer = (layer) => withTransition(() => {
      layerButtons.forEach((b) => b.classList.toggle('active', b.dataset.layer === layer));
      [...nodes, ...edges].forEach((el) => {
        const show = layer === 'all' || el.dataset.layer === layer;
        el.classList.toggle('v50-is-hidden', !show);
      });
    });
    layerButtons.forEach((b) => b.addEventListener('click', () => selectLayer(b.dataset.layer || 'all')));

    const inspect = (node) => {
      nodes.forEach((n) => n.classList.toggle('active', n === node));
      if (!inspector) return;
      const strong = inspector.querySelector('strong');
      const span = inspector.querySelector('span');
      if (strong) strong.textContent = (node.dataset.label || EduI18n.tr('Uzel')) + ' · ' + (node.dataset.layer || '');
      if (span) span.textContent = node.dataset.meta || EduI18n.tr('Bez dalších metadat.');
    };
    nodes.forEach((node) => {
      node.addEventListener('click', () => inspect(node));
      node.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); inspect(node); } });
    });

    const play = root.querySelector('[data-packet-play]');
    const packet = root.querySelector('.v50-packet');
    play?.addEventListener('click', async () => {
      if (!packet || reduceMotion) return;
      const ids = (root.dataset.packetPath || '').split(',').filter(Boolean);
      const pathNodes = ids.map((id) => root.querySelector(`[data-node="${CSS.escape(id)}"]`)).filter(Boolean);
      if (pathNodes.length < 2) return;
      play.disabled = true;
      packet.style.opacity = '1';
      for (const node of pathNodes) {
        const t = node.getAttribute('transform') || '';
        const m = t.match(/translate\(([-.\d]+)\s+([-.\d]+)\)/);
        if (!m) continue;
        inspect(node);
        const anim = packet.animate([{ cx: packet.getAttribute('cx'), cy: packet.getAttribute('cy') }, { cx: m[1], cy: m[2] }], { duration: 520, easing: 'cubic-bezier(.2,.8,.2,1)', fill: 'forwards' });
        await anim.finished.catch(() => {});
        packet.setAttribute('cx', m[1]); packet.setAttribute('cy', m[2]);
      }
      packet.style.opacity = '0'; nodes.forEach((n) => n.classList.remove('active')); play.disabled = false;
    });
  });

  document.querySelectorAll('[data-command-fill]').forEach((button) => {
    button.addEventListener('click', () => {
      const form = button.closest('.v50-card')?.querySelector('.v50-command-form');
      const input = form?.querySelector('input[name="command"]');
      if (!input) return;
      input.value = button.dataset.commandFill || '';
      input.focus();
    });
  });

  document.querySelectorAll('[data-php-lab]').forEach((lab) => {
    const steps = [...lab.querySelectorAll('[data-trace-step]')];
    const button = lab.querySelector('[data-trace-next]');
    let index = -1;
    const show = (next) => {
      index = Math.max(0, Math.min(steps.length - 1, next));
      steps.forEach((s, i) => s.classList.toggle('active', i === index));
      if (button) button.textContent = index >= steps.length - 1 ? EduI18n.tr('Znovu od začátku ↻') : EduI18n.tr('Další krok →');
    };
    if (steps.length) show(0);
    button?.addEventListener('click', () => show(index >= steps.length - 1 ? 0 : index + 1));
  });
})();
