/* EDUCAnet v44 · Learning Studio & Visual Reasoning */
(() => {
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const post = async (params) => {
    const res = await fetch(location.pathname + location.search, {
      method: 'POST', credentials: 'same-origin',
      headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8','X-Requested-With':'XMLHttpRequest'},
      body: new URLSearchParams({csrf, ...params})
    });
    const data = await res.json().catch(() => ({ok:false,error:EduI18n.tr('Neplatná odpověď serveru.')}));
    if (!res.ok || data.ok === false) throw new Error(data.error || EduI18n.tr('Akce se nepodařila.'));
    return data;
  };
  const text = (value) => String(value ?? '').replace(/[<>]/g, '');

  document.querySelectorAll('[data-v44-studio]').forEach(studio => {
    const lesson = studio.dataset.lesson || '0';

    studio.querySelectorAll('[data-v44-representations]').forEach(deck => {
      const tabs = [...deck.querySelectorAll('[data-v44-rep-tab]')];
      const panels = [...deck.querySelectorAll('[data-v44-rep-panel]')];
      tabs.forEach(tab => tab.addEventListener('click', () => {
        const id = tab.dataset.v44RepTab || '';
        tabs.forEach(t => { const active=t===tab; t.classList.toggle('active',active); t.setAttribute('aria-selected',active?'true':'false'); });
        panels.forEach(p => p.hidden = p.dataset.v44RepPanel !== id);
      }));
    });

    const diff = studio.querySelector('[data-v44-difference]');
    if (diff) {
      const range = diff.querySelector('[data-v44-diff-range]');
      const stage = diff.querySelector('.v44-diff-stage');
      const out = diff.querySelector('[data-v44-diff-output]');
      range?.addEventListener('input', () => {
        const value = Number(range.value || 50);
        stage?.style.setProperty('--reveal', value + '%');
        if (out) out.textContent = EduI18n.tr('{value} % funkčního modelu', {value: value});
      });
    }

    studio.querySelectorAll('[data-v44-stage]').forEach(button => button.addEventListener('click', async () => {
      const stage = button.dataset.v44Stage || '';
      const feedback = studio.querySelector(`[data-v44-stage-status="${stage}"]`);
      try {
        await post({action:'v44_stage_complete',lesson_number:lesson,stage});
        button.classList.add('done');
        if (feedback) feedback.textContent = EduI18n.tr('✓ uloženo');
        const gps = studio.querySelector('[data-v44-gps]');
        if (gps && stage === 'experiment') {
          const pending = gps.querySelector('.v44-gps-track .current');
          if (pending) { pending.classList.remove('current'); pending.classList.add('done'); pending.querySelector('i')?.replaceChildren(document.createTextNode('✓')); const next=pending.nextElementSibling; next?.classList.add('current'); }
        }
      } catch (e) { if (feedback) feedback.textContent = e.message; }
    }));

    studio.querySelectorAll('[data-v44-snapshot]').forEach(button => button.addEventListener('click', async () => {
      const phase = button.dataset.v44Snapshot || 'before';
      const feedback = studio.querySelector('[data-v44-snapshot-feedback]');
      const sequence = [...document.querySelectorAll('[data-cv43-model] [data-cv43-sequence] button')]
        .map(b => b.textContent.replace(/^\d+\.\s*/, '').trim()).filter(Boolean);
      if (!sequence.length) {
        if (feedback) { feedback.hidden=false; feedback.textContent=EduI18n.tr('Nejdřív sestav model v části Build the Model výše.'); }
        return;
      }
      try {
        const d = await post({action:'cv43_model_submit',lesson_number:lesson,sequence:JSON.stringify(sequence),phase});
        if (feedback) { feedback.hidden=false; feedback.textContent=phase==='before'?EduI18n.tr('Model PŘED byl uložen. {why}',{why:text(d.why||'')}):EduI18n.tr('Model PO byl uložen. {why}',{why:text(d.why||'')}); if(EduI18n.locale&&EduI18n.locale!=='cs')feedback.setAttribute('lang','cs');else feedback.removeAttribute('lang'); }
        const article = button.closest('article');
        article?.querySelector('strong')?.replaceChildren(document.createTextNode(sequence.join(' → ')));
      } catch (e) { if (feedback) { feedback.hidden=false; feedback.textContent=e.message; } }
    }));

    const teach = studio.querySelector('[data-v44-teachback]');
    teach?.querySelector('[data-v44-teachback-submit]')?.addEventListener('click', async () => {
      const input = teach.querySelector('[data-v44-teachback-text]');
      const feedback = teach.querySelector('[data-v44-teachback-feedback]');
      const score = teach.querySelector('[data-v44-teachback-score]');
      try {
        const d = await post({action:'v44_teachback',lesson_number:lesson,text:input?.value||''});
        if (score) score.textContent = `${Number(d.score||0)}%`;
        teach.querySelector('.v44-score-ring')?.style.setProperty('--score', `${Number(d.score||0)}%`);
        if (feedback) { feedback.hidden=false; feedback.className='v44-inline-feedback '+(d.state==='strong'?'ok':d.state==='developing'?'mid':'bad'); feedback.replaceChildren(); const strongEl=document.createElement('strong'); strongEl.textContent=d.state==='strong'?EduI18n.tr('Silný mentální model'):d.state==='developing'?EduI18n.tr('Model se skládá'):EduI18n.tr('Ještě chybí vztah'); feedback.appendChild(strongEl); feedback.appendChild(document.createElement('br')); const fbSpan=document.createElement('span'); if(EduI18n.locale&&EduI18n.locale!=='cs')fbSpan.setAttribute('lang','cs'); fbSpan.appendChild(document.createTextNode(text(d.feedback||''))); feedback.appendChild(fbSpan); }
      } catch (e) { if (feedback) { feedback.hidden=false; feedback.className='v44-inline-feedback bad'; feedback.textContent=e.message; } }
    });

    const memory = studio.querySelector('[data-v44-memory]');
    memory?.querySelector('[data-v44-memory-save]')?.addEventListener('click', async () => {
      const feedback = memory.querySelector('[data-v44-memory-feedback]');
      const values = Object.fromEntries([...memory.querySelectorAll('[data-v44-memory-field]')].map(el => [el.dataset.v44MemoryField, el.value]));
      try {
        const d = await post({action:'v44_memory_save',lesson_number:lesson,...values});
        memory.querySelector('[data-memory-sentence]')?.replaceChildren(document.createTextNode(d.sentence||''));
        memory.querySelector('[data-memory-trap]')?.replaceChildren(document.createTextNode(d.trap||''));
        memory.querySelector('[data-memory-cue]')?.replaceChildren(document.createTextNode(d.cue||''));
        if (feedback) { feedback.hidden=false; feedback.textContent=EduI18n.tr('✓ Memory Snapshot uložený'); }
      } catch (e) { if (feedback) { feedback.hidden=false; feedback.textContent=e.message; } }
    });
    memory?.querySelector('[data-v44-print]')?.addEventListener('click', () => {
      memory.classList.add('print-focus');
      window.print();
      setTimeout(() => memory.classList.remove('print-focus'), 150);
    });

    const replay = studio.querySelector('[data-v44-replay]');
    if (replay) {
      const steps = [...replay.querySelectorAll('[data-v44-replay-step]')];
      const out = replay.querySelector('[data-v44-replay-time]');
      let index=0, left=90, timer=null;
      const show=(i)=>{index=Math.max(0,Math.min(steps.length-1,i));steps.forEach((s,n)=>s.classList.toggle('active',n===index));};
      const draw=()=>{if(out)out.textContent=`${String(Math.floor(left/60)).padStart(2,'0')}:${String(left%60).padStart(2,'0')}`;if(left<=60&&left>30)show(1);if(left<=30)show(2);};
      replay.querySelector('[data-v44-replay-start]')?.addEventListener('click',()=>{
        if (timer) clearInterval(timer); left=90; show(0); draw();
        if (reduceMotion) return;
        timer=setInterval(()=>{left=Math.max(0,left-1);draw();if(left===0){clearInterval(timer);timer=null;}},1000);
      });
      replay.querySelector('[data-v44-replay-next]')?.addEventListener('click',()=>show((index+1)%steps.length));
    }
  });

  document.querySelectorAll('[data-v44-teacher-board]').forEach(board => {
    const cards=[...board.querySelectorAll('.v44-reveal-board article')];
    let revealed=1;
    const draw=()=>cards.forEach((c,i)=>c.classList.toggle('revealed',i<revealed));
    board.querySelector('[data-v44-reveal-next]')?.addEventListener('click',()=>{revealed=Math.min(cards.length,revealed+1);draw();});
    board.querySelector('[data-v44-reveal-reset]')?.addEventListener('click',()=>{revealed=1;draw();});
    board.querySelector('[data-v44-present]')?.addEventListener('click',()=>{
      board.classList.toggle('presentation');
      board.scrollIntoView({behavior:reduceMotion?'auto':'smooth',block:'start'});
      if (board.classList.contains('presentation') && document.fullscreenEnabled) board.requestFullscreen?.().catch(()=>{});
    });
  });
})();
