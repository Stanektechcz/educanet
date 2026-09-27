(() => {
  'use strict';
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  const root = document.querySelector('[data-v481-deep-lab]');
  if (root) {
    root.querySelectorAll('[data-v481-fault-bank]').forEach((bank) => {
      const tabs = [...bank.querySelectorAll('[data-v481-fault-tab]')];
      const panels = [...bank.querySelectorAll('[data-v481-fault-panel]')];
      const activate = (index) => {
        tabs.forEach((tab, i) => tab.classList.toggle('active', i === index));
        panels.forEach((panel, i) => { panel.hidden = i !== index; });
      };
      tabs.forEach((tab, i) => tab.addEventListener('click', () => activate(i)));
      panels.forEach((panel) => {
        panel.querySelector('[data-v481-fault-reveal]')?.addEventListener('click', (event) => {
          const answer = panel.querySelector('[data-v481-fault-answer]');
          if (!answer) return;
          answer.hidden = !answer.hidden;
          event.currentTarget.textContent = answer.hidden ? EduI18n.tr('Odhalit root cause až po hypotéze') : EduI18n.tr('Skrýt řešení a zkusit znovu');
        });
      });
    });

    root.querySelectorAll('[data-v481-sequence]').forEach((widget) => {
      const id = widget.dataset.taskId || '';
      const bank = [...widget.querySelectorAll('[data-v481-sequence-token]')];
      const canvas = widget.querySelector('[data-v481-sequence-canvas]');
      const inputs = widget.querySelector('[data-v481-sequence-inputs]');
      const reset = widget.querySelector('[data-v481-sequence-reset]');
      let sequence = [];
      const render = () => {
        if (canvas) {
          canvas.replaceChildren();
          if (!sequence.length) {
            const empty = document.createElement('span'); empty.textContent = EduI18n.tr('Poskládej pořadí…'); canvas.appendChild(empty);
          } else {
            sequence.forEach((value, index) => {
              const chip = document.createElement('button'); chip.type = 'button'; chip.className = 'v481-sequence-chip';
              const num = document.createElement('i'); num.textContent = String(index + 1);
              const text = document.createElement('span'); text.textContent = value; if (EduI18n.locale && EduI18n.locale !== 'cs') text.setAttribute('lang', 'cs');
              chip.append(num, text);
              chip.addEventListener('click', () => { sequence.splice(index, 1); render(); });
              canvas.appendChild(chip);
            });
          }
        }
        if (inputs) {
          inputs.replaceChildren();
          sequence.forEach((value) => {
            const input = document.createElement('input'); input.type = 'hidden'; input.name = `answers[${id}][]`; input.value = value; inputs.appendChild(input);
          });
        }
        bank.forEach((button) => { button.disabled = sequence.includes(button.dataset.v481SequenceToken || ''); });
      };
      bank.forEach((button) => button.addEventListener('click', () => {
        const value = button.dataset.v481SequenceToken || '';
        if (value && !sequence.includes(value)) { sequence.push(value); render(); }
      }));
      reset?.addEventListener('click', () => { sequence = []; render(); });
      render();
    });

    root.querySelector('[data-v481-lab-form]')?.addEventListener('submit', (event) => {
      const emptySequence = [...root.querySelectorAll('[data-v481-sequence]')].some((widget) => !widget.querySelector('input[type="hidden"]'));
      if (emptySequence) {
        event.preventDefault();
        const first = [...root.querySelectorAll('[data-v481-sequence]')].find((widget) => !widget.querySelector('input[type="hidden"]'));
        first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        first?.classList.add('needs-attention');
        window.setTimeout(() => first?.classList.remove('needs-attention'), 1800);
      }
    });
  }

  const teacher = document.querySelector('[data-v481-teacher-extension]');
  const parent = document.querySelector('[data-v48-teacher]');
  if (teacher && parent) {
    const url = parent.dataset.v48PollUrl || '';
    if (url) {
      const poll = async () => {
        try {
          const response = await fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          if (!response.ok) return;
          const data = await response.json();
          const deep = data?.summary?.deep || data?.deep;
          if (!deep) return;
          const attempted = teacher.querySelector('[data-v481-attempted]');
          const completed = teacher.querySelector('[data-v481-completed]');
          const average = teacher.querySelector('[data-v481-average]');
          if (attempted) attempted.textContent = String(deep.attempted || 0);
          if (completed) completed.textContent = String(deep.completed || 0);
          if (average) average.textContent = `${deep.avg_percent || 0} %`;
        } catch (_) {}
      };
      window.setInterval(poll, 6000);
    }
  }
})();
