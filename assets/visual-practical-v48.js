(() => {
  'use strict';
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  const jsonFetch = async (url) => {
    const res = await fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  };

  const lab = document.querySelector('[data-v48-lab]');
  if (lab) {
    const live = lab.querySelector('[data-v48-live]');
    const url = lab.dataset.v48StateUrl || '';
    const applyState = (state) => {
      if (!live || !state) return;
      const active = Boolean(state.active);
      live.hidden = !active;
      live.classList.toggle('active', active);
      const phase = live.querySelector('[data-v48-live-phase]');
      const prompt = live.querySelector('[data-v48-live-prompt]');
      if (phase) phase.textContent = state.label || state.phase || EduI18n.tr('živá hodina');
      if (prompt) prompt.textContent = state.prompt || '';
    };
    if (url && live) {
      const poll = async () => { try { const data = await jsonFetch(url); applyState(data.state || data); } catch (_) {} };
      window.setInterval(poll, 6000);
    }

    const form = lab.querySelector('[data-v48-build-form]');
    if (form) {
      const canvas = form.querySelector('[data-v48-build-canvas]');
      const inputs = form.querySelector('[data-v48-build-inputs]');
      const submit = form.querySelector('[data-v48-build-submit]');
      const tokens = [...form.querySelectorAll('[data-v48-build-token]')];
      let sequence = [];
      const render = () => {
        if (canvas) {
          canvas.replaceChildren();
          if (!sequence.length) {
            const empty = document.createElement('span'); empty.textContent = EduI18n.tr('Sem sestav model…'); canvas.appendChild(empty);
          } else {
            sequence.forEach((value, index) => {
              const chip = document.createElement('button'); chip.type = 'button'; chip.className = 'v48-build-chip'; chip.dataset.index = String(index); const chipNum = document.createElement('i'); chipNum.textContent = String(index + 1); const chipText = document.createElement('span'); chipText.textContent = value; if (EduI18n.locale && EduI18n.locale !== 'cs') chipText.setAttribute('lang', 'cs'); chip.append(chipNum, chipText); chip.addEventListener('click', () => { sequence.splice(index, 1); render(); }); canvas.appendChild(chip);
            });
          }
        }
        if (inputs) {
          inputs.replaceChildren();
          sequence.forEach((value) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'sequence[]'; input.value = value; inputs.appendChild(input); });
        }
        tokens.forEach((button) => { button.disabled = sequence.includes(button.dataset.v48BuildToken || ''); });
        if (submit) submit.disabled = sequence.length !== tokens.length;
      };
      tokens.forEach((button) => button.addEventListener('click', () => { const value = button.dataset.v48BuildToken || ''; if (value && !sequence.includes(value)) { sequence.push(value); render(); } }));
      form.querySelector('[data-v48-build-undo]')?.addEventListener('click', () => { sequence.pop(); render(); });
      form.querySelector('[data-v48-build-reset]')?.addEventListener('click', (event) => { event.preventDefault(); sequence = []; render(); });
      render();
    }
  }

  const teacher = document.querySelector('[data-v48-teacher]');
  if (teacher) {
    const url = teacher.dataset.v48PollUrl || '';
    const applyTeacher = (data) => {
      if (!data) return;
      const summary = data.summary || data;
      const state = summary.state || {};
      const counts = summary.counts || {};
      const status = teacher.querySelector('[data-v48-teacher-status]');
      if (status) { status.textContent = state.active ? EduI18n.tr('živě') : EduI18n.tr('připraveno'); status.classList.toggle('active', Boolean(state.active)); }
      Object.entries(counts).forEach(([key, count]) => { const node = teacher.querySelector(`[data-v48-count="${CSS.escape(key)}"]`); if (node) node.textContent = `${count} / ${summary.students || 0}`; });
      const phase = teacher.querySelector('[data-v48-current-phase]');
      const prompt = teacher.querySelector('[data-v48-current-prompt]');
      if (phase) phase.textContent = state.label || state.phase || EduI18n.tr('Predikce');
      if (prompt) prompt.textContent = state.prompt || '';
    };
    if (url) {
      const poll = async () => { try { const data = await jsonFetch(url); applyTeacher(data); } catch (_) {} };
      window.setInterval(poll, 5000);
    }
  }
})();
