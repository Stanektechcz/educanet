/* EDUCANET v51 · krokový dotazník a učitelské nástroje dotazníku */
(() => {
  'use strict';
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  // Posuvníky 1–10
  document.querySelectorAll('[data-u51-range]').forEach((input) => {
    const out = input.parentElement.querySelector('output');
    const sync = () => { if (out) out.textContent = input.value; input.style.setProperty('--u51-fill', ((input.value - 1) / 9 * 100) + '%'); };
    input.addEventListener('input', sync); sync();
  });

  const form = document.querySelector('[data-u51-wizard]');
  if (form) {
    const steps = [...form.querySelectorAll('[data-u51-step]')];
    const prev = form.querySelector('[data-u51-prev]');
    const next = form.querySelector('[data-u51-next]');
    const submit = form.querySelector('[data-u51-submit]');
    const title = form.querySelector('[data-u51-title]');
    const counter = document.querySelector('[data-u51-counter]');
    const bar = document.querySelector('[data-u51-progress] .u51-bar i');
    const seatInput = form.querySelector('[data-u51-seat-input]');
    const draftKey = 'u51-intake-draft:' + location.pathname;
    let current = 0;

    const clearError = (step) => step.querySelectorAll('.u51-step-error').forEach((el) => el.remove());
    const showError = (step, text, focus) => {
      clearError(step);
      const box = document.createElement('div');
      box.className = 'u51-step-error';
      box.setAttribute('role', 'alert');
      box.textContent = text;
      step.querySelector('.u51-step-head')?.after(box);
      (focus || box).scrollIntoView({ behavior: 'smooth', block: 'center' });
      focus?.focus?.({ preventScroll: true });
    };

    const validate = (step) => {
      clearError(step);
      if (step.querySelector('.u51-room') && seatInput && !seatInput.value) {
        showError(step, EduI18n.tr('Vyber prosím své místo v učebně.'), step.querySelector('.u51-room'));
        return false;
      }
      const radios = new Set();
      for (const field of step.querySelectorAll('input[required], select[required], textarea[required]')) {
        if (field.type === 'hidden') continue;
        if (field.type === 'radio') {
          if (radios.has(field.name)) continue;
          radios.add(field.name);
          if (!step.querySelector(`input[type="radio"][name="${CSS.escape(field.name)}"]:checked`)) {
            showError(step, EduI18n.tr('Odpověz prosím na všechny otázky.'), field.closest('fieldset'));
            return false;
          }
          continue;
        }
        if (field.type === 'checkbox' && !field.checked) { showError(step, EduI18n.tr('Potvrď prosím souhlas na konci kroku.'), field.closest('label')); return false; }
        if (field.type === 'file' && !field.files?.length) { showError(step, EduI18n.tr('Nahraj prosím hotový plakát.'), field.closest('label')); return false; }
        if (!field.checkValidity()) { showError(step, EduI18n.tr('Doplň prosím povinné údaje.'), field); return false; }
      }
      return true;
    };

    const show = (index, scroll = true) => {
      current = Math.max(0, Math.min(steps.length - 1, index));
      steps.forEach((step, i) => { step.hidden = i !== current; });
      const last = current === steps.length - 1;
      prev.style.visibility = current === 0 ? 'hidden' : 'visible';
      next.hidden = last;
      submit.hidden = !last;
      const label = EduI18n.tr('Krok {n} z {total}', { n: current + 1, total: steps.length });
      if (counter) counter.textContent = label;
      if (title) title.textContent = steps[current].dataset.title || '';
      if (bar) bar.style.width = Math.round((current + 1) / steps.length * 100) + '%';
      if (scroll) form.closest('.u51-wizard-wrap')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      try { sessionStorage.setItem(draftKey + ':step', String(current)); } catch (_) {}
    };

    next.addEventListener('click', () => { if (validate(steps[current])) show(current + 1); });
    prev.addEventListener('click', () => show(current - 1));

    form.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' || current === steps.length - 1) return;
      if (event.target instanceof HTMLTextAreaElement || event.target instanceof HTMLButtonElement) return;
      event.preventDefault();
      if (validate(steps[current])) show(current + 1);
    });

    form.addEventListener('submit', (event) => {
      if (current !== steps.length - 1) { event.preventDefault(); return; }
      for (let i = 0; i < steps.length; i++) {
        if (!validate(steps[i])) { event.preventDefault(); show(i); validate(steps[i]); return; }
      }
      submit.disabled = true;
      submit.textContent = EduI18n.tr('Odesílám…');
      try { sessionStorage.removeItem(draftKey); sessionStorage.removeItem(draftKey + ':step'); } catch (_) {}
    });

    // Výběr místa
    form.querySelectorAll('[data-u51-seat]').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (btn.disabled) return;
        form.querySelectorAll('[data-u51-seat].selected').forEach((b) => b.classList.remove('selected'));
        btn.classList.add('selected');
        seatInput.value = btn.dataset.u51Seat || '';
        const label = form.querySelector('[data-u51-seat-label]');
        if (label) label.textContent = btn.dataset.label || '';
        clearError(btn.closest('[data-u51-step]'));
      });
    });

    // Plakát: název, velikost a náhled
    const file = form.querySelector('[data-u51-file]');
    if (file) {
      const name = form.querySelector('[data-u51-file-name]');
      const preview = form.querySelector('[data-u51-file-preview]');
      file.addEventListener('change', () => {
        const f = file.files?.[0];
        if (!f) { name.textContent = EduI18n.tr('Vybrat hotový plakát'); preview.hidden = true; return; }
        const size = f.size >= 1048576 ? (f.size / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(f.size / 1024)) + ' kB';
        name.textContent = f.name + ' · ' + size;
        file.setCustomValidity(f.size > 12 * 1048576 ? EduI18n.tr('Soubor může mít maximálně 12 MB.') : '');
        if (f.type.startsWith('image/')) { preview.src = URL.createObjectURL(f); preview.hidden = false; } else { preview.hidden = true; }
      });
    }

    // Přetažení souboru do pole
    const drop = form.querySelector('.u51-upload');
    if (drop && file) {
      ['dragenter', 'dragover'].forEach((type) => drop.addEventListener(type, (e) => { e.preventDefault(); drop.classList.add('drag'); }));
      ['dragleave', 'drop'].forEach((type) => drop.addEventListener(type, () => drop.classList.remove('drag')));
      drop.addEventListener('drop', (e) => {
        e.preventDefault();
        if (!e.dataTransfer?.files?.length) return;
        file.files = e.dataTransfer.files;
        file.dispatchEvent(new Event('change'));
      });
    }

    // Počítadlo zodpovězených otázek
    const quiz = form.querySelector('[data-u51-quiz]');
    if (quiz) {
      const count = quiz.querySelector('[data-u51-quiz-count]');
      const update = () => { count.textContent = String(new Set([...quiz.querySelectorAll('input[type="radio"]:checked')].map((r) => r.name)).size); };
      quiz.addEventListener('change', update); update();
    }

    // Rozepsané odpovědi přežijí obnovení stránky (soubor ani místo se neukládají).
    const saveDraft = () => {
      const data = {};
      for (const el of form.elements) {
        if (!el.name || ['csrf', 'action', 'seat_id'].includes(el.name) || el.type === 'file') continue;
        if ((el.type === 'checkbox' || el.type === 'radio')) { if (el.checked) (data[el.name] ||= []).push(el.value); }
        else data[el.name] = el.value;
      }
      try { sessionStorage.setItem(draftKey, JSON.stringify(data)); } catch (_) {}
    };
    const serverError = document.querySelector('[data-u51-server-error]');
    if (!serverError) {
      try {
        const data = JSON.parse(sessionStorage.getItem(draftKey) || 'null');
        if (data) for (const el of form.elements) {
          if (!el.name || !(el.name in data) || el.type === 'file') continue;
          if (el.type === 'checkbox' || el.type === 'radio') el.checked = [].concat(data[el.name]).includes(el.value);
          else if (el.type !== 'hidden') el.value = data[el.name];
        }
        form.querySelectorAll('[data-u51-range]').forEach((i) => i.dispatchEvent(new Event('input')));
      } catch (_) {}
    }
    form.addEventListener('input', saveDraft);
    form.addEventListener('change', saveDraft);

    let start = 0;
    if (serverError) {
      start = serverError.dataset.u51ErrorKind === 'seat' ? 0 : steps.length - 1;
    } else {
      try { start = Number(sessionStorage.getItem(draftKey + ':step') || 0); } catch (_) {}
    }
    show(start, false);
  }

  // Učitel: editor učebny (jedna buňka = lavice se 2 místy)
  const editor = document.querySelector('[data-u51-seat-editor]');
  if (editor) {
    const hidden = document.querySelector('[data-u51-seat-map]');
    const rowsInput = document.querySelector('[data-u51-rows]');
    const colsInput = document.querySelector('[data-u51-cols]');
    const state = new Map();
    try {
      for (const seat of JSON.parse(hidden.value || '[]')) {
        const key = `${seat.row}:${seat.desk || Math.ceil(seat.col / 2)}`;
        state.set(key, Boolean(state.get(key)) || Boolean(seat.active));
      }
    } catch (_) {}
    const dims = () => ({ rows: Math.max(1, Math.min(12, +rowsInput.value || 8)), desks: Math.max(1, Math.min(10, +colsInput.value || 6)) });
    const render = () => {
      const { rows, desks } = dims();
      editor.style.setProperty('--u51-cols', desks);
      editor.replaceChildren();
      const seats = [];
      for (let r = 1; r <= rows; r++) for (let d = 1; d <= desks; d++) {
        const key = `${r}:${d}`;
        if (!state.has(key)) state.set(key, true);
        const active = state.get(key);
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'u51-desk u51-desk-edit' + (active ? '' : ' off');
        b.title = EduI18n.tr('Řada {row} · Lavice {desk}', { row: r, desk: d });
        b.replaceChildren();
        if (active) {
          const l = document.createElement('span'); l.className = 'u51-seat'; l.textContent = 'L';
          const p = document.createElement('span'); p.className = 'u51-seat'; p.textContent = 'P';
          b.append(l, p);
        } else {
          const small = document.createElement('small'); small.textContent = '—';
          b.append(small);
        }
        b.addEventListener('click', () => { state.set(key, !state.get(key)); render(); });
        editor.appendChild(b);
        for (let s = 0; s < 2; s++) {
          const col = (d - 1) * 2 + s + 1;
          // Hodnota label je uložená data (seznam míst v učebně) – zůstává česky, i18n se týká jen zobrazení.
          seats.push({ id: `r${r}c${col}`, row: r, col, desk: d, side: s ? 'right' : 'left', label: `Řada ${r} · Lavice ${d} · ${s ? 'vpravo' : 'vlevo'}`, active });
        }
      }
      hidden.value = JSON.stringify(seats);
    };
    const each = (fn) => { const { rows, desks } = dims(); for (let r = 1; r <= rows; r++) for (let d = 1; d <= desks; d++) fn(`${r}:${d}`, d, desks); };
    document.querySelector('[data-u51-seat-all]')?.addEventListener('click', () => { each((k) => state.set(k, true)); render(); });
    document.querySelector('[data-u51-seat-aisle]')?.addEventListener('click', () => {
      each((k, d, desks) => { const mid = Math.ceil(desks / 2); state.set(k, desks % 2 === 0 ? !(d === mid || d === mid + 1) : d !== mid); });
      render();
    });
    rowsInput.addEventListener('input', render);
    colsInput.addEventListener('input', render);
    render();
  }

  // Učitel: hledání v tabulce a profily v dialogu
  const search = document.querySelector('[data-u51-search]');
  search?.addEventListener('input', () => {
    const q = search.value.toLocaleLowerCase('cs-CZ').trim();
    document.querySelectorAll('[data-u51-row]').forEach((row) => { row.hidden = q !== '' && !row.textContent.toLocaleLowerCase('cs-CZ').includes(q); });
  });
  document.querySelectorAll('[data-u51-open]').forEach((btn) => btn.addEventListener('click', () => document.getElementById(btn.dataset.u51Open)?.showModal()));
  document.querySelectorAll('dialog.u51-dialog').forEach((dialog) => {
    dialog.addEventListener('click', (e) => { if (e.target === dialog || e.target.closest('[data-u51-close]')) dialog.close(); });
  });
  document.querySelectorAll('[data-u51-print]').forEach((btn) => btn.addEventListener('click', () => window.print()));

  // Krokový režim dlouhých výukových stránek: úvod zůstává, každá další sekce je samostatný krok.
  const stepView = [...document.body.classList].find((c) => ['view-case_study', 'view-lesson_kit'].includes(c));
  const main = document.querySelector('main');
  if (stepView && main) {
    const children = [...main.children].filter((el) => !['SCRIPT', 'STYLE'].includes(el.tagName) && !el.matches('.notice, .button-row, .v507-page-shell'));
    const intro = children[0];
    const blocks = children.slice(1);
    const tail = [...main.children].filter((el) => el.matches('.button-row'));
    if (blocks.length >= 2) {
      const key = 'u51-steps:' + location.search;
      const titleOf = (el, i) => (el.querySelector('h2, h3, figcaption, summary')?.textContent || EduI18n.tr('Krok {n}', { n: i + 1 })).trim().replace(/\s+/g, ' ').slice(0, 42);
      const bar = document.createElement('nav');
      bar.className = 'u51-lesson-steps';
      bar.setAttribute('aria-label', EduI18n.tr('Kroky'));
      const barTrack = document.createElement('div'); barTrack.className = 'u51-bar';
      barTrack.appendChild(document.createElement('i'));
      const list = document.createElement('ol');
      bar.append(barTrack, list);
      let current = 0;
      const nav = document.createElement('div');
      nav.className = 'u51-wizard-nav u51-lesson-nav';
      const prevBtn = document.createElement('button'); prevBtn.type = 'button'; prevBtn.className = 'btn secondary';
      const label = document.createElement('span'); label.className = 'u51-step-title';
      const nextBtn = document.createElement('button'); nextBtn.type = 'button'; nextBtn.className = 'btn primary';
      nav.append(prevBtn, label, nextBtn);
      prevBtn.textContent = EduI18n.tr('Zpět');
      nextBtn.textContent = EduI18n.tr('Pokračovat');
      const go = (i, scroll = true) => {
        current = Math.max(0, Math.min(blocks.length - 1, i));
        blocks.forEach((el, n) => { el.hidden = n !== current; });
        list.querySelectorAll('li').forEach((li, n) => { li.className = n === current ? 'current' : (n < current ? 'done' : ''); });
        bar.querySelector('.u51-bar i').style.width = Math.round((current + 1) / blocks.length * 100) + '%';
        label.textContent = EduI18n.tr('Krok {n} z {total}', { n: current + 1, total: blocks.length });
        prevBtn.style.visibility = current === 0 ? 'hidden' : 'visible';
        const last = current === blocks.length - 1;
        nextBtn.hidden = last;
        tail.forEach((el) => { el.hidden = !last; });
        try { sessionStorage.setItem(key, String(current)); } catch (_) {}
        if (scroll) bar.scrollIntoView({ behavior: 'smooth', block: 'start' });
      };
      blocks.forEach((el, i) => {
        el.classList.add('u51-lesson-step');
        const li = document.createElement('li');
        const liBtn = document.createElement('button'); liBtn.type = 'button';
        const liNum = document.createElement('b'); liNum.textContent = String(i + 1);
        const liLabel = document.createElement('span'); liLabel.textContent = titleOf(el, i);
        liBtn.append(liNum, liLabel);
        li.appendChild(liBtn);
        liBtn.addEventListener('click', () => go(i));
        list.appendChild(li);
      });
      intro.after(bar);
      blocks[blocks.length - 1].after(nav);
      tail.slice().reverse().forEach((el) => nav.after(el));
      prevBtn.addEventListener('click', () => go(current - 1));
      nextBtn.addEventListener('click', () => go(current + 1));
      let saved = 0;
      try { saved = Number(sessionStorage.getItem(key) || 0); } catch (_) {}
      go(saved, false);
    }
  }
})();
