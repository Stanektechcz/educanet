(() => {
  'use strict';
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  const root = document.querySelector('[data-one-task]');
  if (!root) return;
  const primary = document.querySelector('[data-ot-primary]');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const taskId = root.getAttribute('data-task-id') || '';
  const title = document.querySelector('.ot-task-head h1')?.textContent?.trim() || '';
  const saveState = document.querySelector('[data-ot-save-state] span');
  const help = document.querySelector('[data-ot-help]');
  let busy = false;

  const postForm = async (url, params) => {
    const body = params instanceof URLSearchParams ? params : new URLSearchParams(params || {});
    if (!body.has('csrf')) body.set('csrf', csrf);
    const res = await fetch(url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body});
    let data = null;
    try { data = await res.json(); } catch (_) { data = {ok:false, error:(EduI18n.tr('Server nevrátil platnou odpověď.'))}; }
    if (!res.ok || !data?.ok) throw new Error(data?.error || (EduI18n.tr('Krok se nepodařilo uložit.')));
    return data;
  };

  const event = (kind) => {
    if (!csrf || !taskId) return;
    const body = new URLSearchParams({csrf, action:'v505_event', task_id:taskId, kind});
    fetch(location.pathname + location.search, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body, keepalive:true}).catch(() => {});
  };

  const markSuccess = (message) => {
    if (message === undefined) message = EduI18n.tr('Hotovo. Krok je uložený.');
    const feedback = document.querySelector('[data-ot-feedback]');
    if (feedback) { feedback.hidden = false; feedback.classList.remove('error'); feedback.classList.add('ok'); feedback.textContent = message; }
    if (primary) { primary.disabled = false; primary.dataset.state = 'success'; primary.textContent = EduI18n.tr('Další krok →'); }
    root.dataset.stage = '3';
    document.querySelectorAll('.ot-stagebar > div').forEach((node,i) => { node.classList.toggle('done', i < 3); node.classList.toggle('active', i === 3); const icon=node.querySelector('i'); if(icon) icon.textContent=i<3?'✓':String(i+1); });
    event('passed');
  };

  const markError = (message) => {
    const feedback = document.querySelector('[data-ot-feedback]');
    if (feedback) { feedback.hidden = false; feedback.classList.remove('ok'); feedback.classList.add('error'); feedback.textContent = message; }
    if (primary) { primary.disabled = false; primary.dataset.state = ''; primary.textContent = EduI18n.tr('Opravit a ověřit znovu →'); }
    event('retry');
  };

  const handleCourse = async () => {
    const node = document.querySelector('[data-ot-course]');
    if (!node) return false;
    if (primary?.dataset.state === 'success') { location.reload(); return true; }
    const checks = Array.from(document.querySelectorAll('[data-ot-task]'));
    if (checks.length && checks.some(x => !x.checked)) { markError(EduI18n.tr('Nejdřív dokonči všechny části tohoto jediného kroku.')); return true; }
    const answer = document.querySelector('[data-ot-answer]:checked');
    const kind = node.dataset.kind || 'manual';
    if (kind === 'quiz' && !answer) { markError(EduI18n.tr('Vyber jednu odpověď a potom ji ověř.')); return true; }
    const body = new URLSearchParams({csrf, action:node.dataset.action || 'course_lesson_step', step:node.dataset.step || '', tasks_done:String(checks.filter(x=>x.checked).length)});
    if (node.dataset.lesson) body.set('lesson', node.dataset.lesson);
    if (answer) body.set('answer', answer.value);
    try {
      primary.disabled = true; primary.textContent = EduI18n.tr('Ověřuji…');
      const data = await postForm(node.dataset.endpoint || 'progress.php', body);
      if (data.correct === false) { markError(data.explanation || (EduI18n.tr('Tento závěr ještě nesedí. Vrať se k důkazům a zkus to znovu.'))); return true; }
      markSuccess(EduI18n.tr('✓ Správně. Další krok je připravený.'));
    } catch (e) { markError(e.message || (EduI18n.tr('Krok se nepodařilo ověřit.'))); }
    return true;
  };

  const handleKb = async () => {
    const node = document.querySelector('[data-ot-kb]');
    if (!node) return false;
    if (primary?.dataset.state === 'success') { location.reload(); return true; }
    const phase = node.dataset.phase || 'visual';
    const topic = node.dataset.topic || '';
    const body = new URLSearchParams({csrf, topic});
    if (phase === 'check') {
      const answer = document.querySelector('[data-ot-answer]:checked');
      if (!answer) { markError(EduI18n.tr('Vyber jednu odpověď a potom ji ověř.')); return true; }
      body.set('action','kb_check'); body.set('answer',answer.value);
    } else { body.set('action','kb_step'); body.set('step',phase); }
    try {
      primary.disabled = true; primary.textContent = EduI18n.tr('Ověřuji…');
      const data = await postForm(node.dataset.endpoint || 'progress.php', body);
      if (data.correct === false) { markError(data.explanation || (EduI18n.tr('Ještě to nesedí. Zkus si princip vysvětlit jinak.'))); return true; }
      markSuccess(EduI18n.tr('✓ Krok vysvětlení je hotový.'));
    } catch (e) { markError(e.message || (EduI18n.tr('Krok se nepodařilo uložit.'))); }
    return true;
  };

  if (primary && primary.tagName === 'BUTTON') {
    primary.addEventListener('click', async () => {
      if (busy) return; busy = true;
      try {
        if (await handleCourse()) return;
        if (await handleKb()) return;
        const form = document.querySelector('[data-ot-form]');
        if (form) {
          if (!form.reportValidity()) return;
          primary.disabled = true; primary.textContent = EduI18n.tr('Ukládám…');
          form.requestSubmit();
        }
      } finally { busy = false; }
    });
  }

  const openHelp = () => { if (!help) return; help.hidden = false; event('help'); const close=help.querySelector('[data-ot-help-close]'); close?.focus(); };
  const closeHelp = () => { if (help) help.hidden = true; };
  document.querySelector('[data-ot-help-open]')?.addEventListener('click', openHelp);
  document.querySelector('[data-ot-help-close]')?.addEventListener('click', closeHelp);

  document.querySelectorAll('[data-ot-fill-command]').forEach(btn => btn.addEventListener('click', () => {
    const input = document.querySelector('input[name="command"]'); if (input) { input.value = btn.dataset.otFillCommand || ''; input.dispatchEvent(new Event('input',{bubbles:true})); input.focus(); }
  }));

  const draftForm = document.querySelector('[data-ot-draft-form]');
  let draftTimer = 0;
  const saveDraft = async () => {
    if (!draftForm || !taskId) return;
    const data = {};
    new FormData(draftForm).forEach((value,key) => { if (!['csrf','action','task_id','task_type','return_url','lesson','skill','path','record','topic'].includes(key)) data[key]=String(value); });
    if (saveState) saveState.textContent = EduI18n.tr('Ukládám…');
    const body = new URLSearchParams({csrf, action:'v505_draft_save', task_id:taskId, payload:JSON.stringify(data), task_url:location.search, task_title:title});
    try { await postForm(location.pathname + location.search, body); if (saveState) saveState.textContent=(EduI18n.tr('Uloženo')); }
    catch (_) { if (saveState) saveState.textContent=(EduI18n.tr('Uložení se nezdařilo')); }
  };
  if (draftForm) draftForm.addEventListener('input', () => { clearTimeout(draftTimer); draftTimer = setTimeout(saveDraft, 650); });

  document.querySelector('[data-ot-return]')?.addEventListener('click', () => event('returned'));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeHelp();
    if (e.key === '?' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName || '')) { e.preventDefault(); openHelp(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && primary) { e.preventDefault(); primary.click(); }
  });
})();
