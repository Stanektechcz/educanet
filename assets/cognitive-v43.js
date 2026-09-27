/* EDUCAnet v43 · Cognitive Visualization Engine */
(() => {
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const reduceBySystem = matchMedia('(prefers-reduced-motion: reduce)').matches;
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
  const transition = (fn) => {
    if (!reduceBySystem && document.startViewTransition) document.startViewTransition(fn);
    else fn();
  };

  document.querySelectorAll('[data-cv43-lab]').forEach(root => {
    let confidence = 0;
    let activeLayer = 0;
    const nodes = [...root.querySelectorAll('[data-cv43-node]')];
    const layers = [...root.querySelectorAll('[data-cv43-layer]')];
    const timeline = root.querySelector('[data-cv43-timeline]');
    const timelineSteps = [...root.querySelectorAll('[data-cv43-timeline-step]')];
    const stageCopy = root.querySelector('[data-cv43-stage-copy]');
    const family = root.dataset.family || '';
    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    const staticMode = reduceBySystem || Boolean(connection?.saveData) || (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2);
    root.dataset.performance = staticMode ? 'static' : ((navigator.hardwareConcurrency || 4) >= 8 ? 'rich' : 'interactive');

    const focus = (idx) => {
      activeLayer = Math.max(0, Math.min(nodes.length - 1, idx));
      nodes.forEach((node,i) => { node.classList.toggle('is-active', i === activeLayer); node.classList.toggle('is-muted', false); });
      layers.forEach((b,i) => b.classList.toggle('active', i === activeLayer));
      if (stageCopy && layers[activeLayer]) stageCopy.textContent = layers[activeLayer].querySelector('small')?.textContent || layers[activeLayer].textContent || '';
    };
    if (nodes.length) focus(0);

    layers.forEach((button,i) => button.addEventListener('click', () => transition(() => focus(i))));
    root.querySelectorAll('[data-cv43-vocab]').forEach(button => button.addEventListener('click', () => {
      const layerId = button.dataset.layer || '';
      const idx = layers.findIndex(x => x.dataset.cv43Layer === layerId);
      if (idx >= 0) { transition(() => focus(idx)); root.querySelector('.cv43-visual-stage')?.scrollIntoView({behavior:root.classList.contains('motion-reduced')?'auto':'smooth',block:'center'}); }
    }));

    root.querySelectorAll('[data-cv43-confidence]').forEach(button => button.addEventListener('click', () => {
      confidence = Number(button.dataset.cv43Confidence || 0);
      root.querySelectorAll('[data-cv43-confidence]').forEach(x => x.classList.toggle('active', x === button));
    }));
    root.querySelectorAll('[data-cv43-predict]').forEach(button => button.addEventListener('click', async () => {
      const feedback = root.querySelector('[data-cv43-predict-feedback]');
      if (!confidence) { if(feedback){feedback.hidden=false;feedback.className='cv43-feedback bad';feedback.textContent=EduI18n.tr('Nejdřív označ jistotu. Neovlivňuje známku, pomáhá rozlišit náhodnou chybu od pevného misconception.');} return; }
      root.querySelectorAll('[data-cv43-predict]').forEach(x => x.disabled = true);
      try {
        const d = await post({action:'cv43_prediction',lesson_number:root.dataset.lesson||'0',answer:button.dataset.cv43Predict||'-1',confidence:String(confidence)});
        button.classList.add(d.correct ? 'correct' : 'wrong');
        if(feedback){feedback.hidden=false;feedback.className='cv43-feedback '+(d.correct?'ok':'bad');feedback.replaceChildren();const strong=document.createElement('strong');strong.textContent=d.correct?EduI18n.tr('Hypotéza sedí'):EduI18n.tr('Ještě odděl vrstvy problému');feedback.appendChild(strong);feedback.appendChild(document.createElement('br'));const whySpan=document.createElement('span');if(EduI18n.locale&&EduI18n.locale!=='cs')whySpan.setAttribute('lang','cs');whySpan.appendChild(document.createTextNode(String(d.why||'').replace(/[<>]/g,'')));feedback.appendChild(whySpan);}
        if (d.correct && nodes.length) transition(() => focus(Math.min(1,nodes.length-1)));
      } catch (e) { if(feedback){feedback.hidden=false;feedback.className='cv43-feedback bad';feedback.textContent=e.message;} root.querySelectorAll('[data-cv43-predict]').forEach(x => x.disabled = false); }
    }));

    const renderTimeline = (idx) => {
      idx = Math.max(0,Math.min(timelineSteps.length-1,idx));
      timelineSteps.forEach((b,i)=>b.classList.toggle('active',i===idx));
      const count=root.querySelector('[data-cv43-timeline-count]'); if(count) count.textContent=`${idx+1} / ${timelineSteps.length}`;
      const title=root.querySelector('[data-cv43-timeline-title]'); if(title) title.textContent=timelineSteps[idx]?.querySelector('strong')?.textContent||'';
      if(timeline) timeline.value=String(idx);
      if(nodes.length) focus(Math.min(nodes.length-1, Math.floor(idx * nodes.length / Math.max(1,timelineSteps.length))));
    };
    timeline?.addEventListener('input',()=>renderTimeline(Number(timeline.value||0)));
    timelineSteps.forEach((button,i)=>button.addEventListener('click',()=>transition(()=>renderTimeline(i))));

    root.querySelectorAll('[data-cv43-zoom]').forEach(button=>button.addEventListener('click',()=>{
      root.dataset.zoom=button.dataset.cv43Zoom||'overview';root.querySelectorAll('[data-cv43-zoom]').forEach(x=>x.classList.toggle('active',x===button));
    }));

    root.querySelectorAll('[data-cv43-compare]').forEach(button=>button.addEventListener('click',()=>{
      const mode=button.dataset.cv43Compare;root.querySelectorAll('[data-cv43-compare]').forEach(x=>x.classList.toggle('active',x===button));root.querySelectorAll('[data-cv43-compare-panel]').forEach(p=>p.hidden=p.dataset.cv43ComparePanel!==mode);
    }));

    const design = root.querySelector('[data-cv43-design-preview]');
    const controls = [...root.querySelectorAll('[data-cv43-control]')];
    if (design && controls.length) {
      const baseline = Object.fromEntries(controls.map(c=>[c.dataset.cv43Control,Number(c.value)]));
      const setStyle=(id,val)=>{
        if(id==='gap')design.style.setProperty('--lab-gap',val+'px');
        if(id==='title')design.style.setProperty('--lab-title',val+'px');
        if(id==='width')design.style.setProperty('--lab-width',val+'px');
        if(id==='radius')design.style.setProperty('--lab-radius',val+'px');
        if(id==='contrast')design.style.setProperty('--lab-contrast',String(val/10));
        if(id==='focus')design.style.setProperty('--lab-focus',val+'%');
        if(id==='duration')design.style.setProperty('--lab-duration',val+'ms');
        if(id==='distance')design.style.setProperty('--lab-distance',val+'px');
        if(id==='quality')design.dataset.quality=String(val);
      };
      const updateQuality=()=>{
        let score=0;controls.forEach(c=>{const v=Number(c.value),t=Number(c.dataset.target||v),range=Math.max(1,Number(c.max)-Number(c.min));score+=Math.max(0,1-Math.abs(v-t)/range*2);});score=score/controls.length;
        const q=root.querySelector('[data-cv43-quality]');if(q)q.textContent=score>.82?EduI18n.tr('funkční varianta'):score>.55?EduI18n.tr('blíží se funkčnímu modelu'):EduI18n.tr('model před úpravou');
      };
      controls.forEach(c=>c.addEventListener('input',()=>{const id=c.dataset.cv43Control,val=Number(c.value),unit=c.dataset.unit||'';setStyle(id,val);c.closest('label')?.querySelector('[data-cv43-output]')?.replaceChildren(document.createTextNode(String(val)+unit));const ch=root.querySelector('[data-cv43-change]');if(ch){ch.textContent=EduI18n.tr('Změna: {param} → {val}{unit}. Co se změnilo v hierarchii nebo chování?',{param:c.closest('label')?.querySelector('strong')?.textContent||id,val:val,unit:unit});if(EduI18n.locale&&EduI18n.locale!=='cs')ch.setAttribute('lang','cs');else ch.removeAttribute('lang');}updateQuality();}));
      root.querySelector('[data-cv43-before]')?.addEventListener('click',()=>{controls.forEach(c=>{const v=baseline[c.dataset.cv43Control];setStyle(c.dataset.cv43Control,v);});design.classList.add('is-before');});
      root.querySelector('[data-cv43-after]')?.addEventListener('click',()=>{controls.forEach(c=>setStyle(c.dataset.cv43Control,Number(c.value)));design.classList.remove('is-before');updateQuality();});
      root.querySelector('[data-cv43-target]')?.addEventListener('click',()=>{controls.forEach(c=>{c.value=c.dataset.target||c.value;setStyle(c.dataset.cv43Control,Number(c.value));const out=c.closest('label')?.querySelector('[data-cv43-output]');if(out)out.textContent=String(c.value)+(c.dataset.unit||'');});design.classList.remove('is-before');updateQuality();});
      updateQuality();
    }

    const model = root.querySelector('[data-cv43-model]');
    if (model) {
      let sequence=[];
      try { const saved=JSON.parse(model.dataset.saved||'[]'); if(Array.isArray(saved)) sequence=saved.map(String); } catch (_) {}
      const bank=[...model.querySelectorAll('[data-cv43-token]')], seq=model.querySelector('[data-cv43-sequence]'), feedback=model.querySelector('[data-cv43-model-feedback]');
      const redraw=()=>{if(!seq)return;seq.replaceChildren();if(!sequence.length){const s=document.createElement('span');s.className='cv43-placeholder';s.textContent=EduI18n.tr('Tvůj model se objeví tady…');seq.appendChild(s);return;}sequence.forEach((token,i)=>{const b=document.createElement('button');b.type='button';b.textContent=`${i+1}. ${token}`;b.addEventListener('click',()=>{sequence.splice(i,1);bank.find(x=>x.dataset.cv43Token===token)?.removeAttribute('disabled');redraw();});seq.appendChild(b);});};
      bank.forEach(b=>{if(sequence.includes(b.dataset.cv43Token||''))b.disabled=true;b.addEventListener('click',()=>{sequence.push(b.dataset.cv43Token||'');b.disabled=true;redraw();});});
      model.querySelector('[data-cv43-model-reset]')?.addEventListener('click',()=>{sequence=[];bank.forEach(b=>b.disabled=false);redraw();if(feedback)feedback.hidden=true;});
      model.querySelector('[data-cv43-model-check]')?.addEventListener('click',async()=>{if(!sequence.length){if(feedback){feedback.hidden=false;feedback.className='cv43-feedback bad';feedback.textContent=EduI18n.tr('Nejdřív sestav model.');}return;}try{const d=await post({action:'cv43_model_submit',lesson_number:root.dataset.lesson||'0',sequence:JSON.stringify(sequence),phase:'current'});if(feedback){feedback.hidden=false;feedback.className='cv43-feedback '+(d.correct?'ok':'bad');feedback.textContent=d.why||'';if(EduI18n.locale&&EduI18n.locale!=='cs')feedback.setAttribute('lang','cs');else feedback.removeAttribute('lang');}if(!d.correct&&Number.isInteger(d.first_diff)){const bad=seq?.children?.[d.first_diff];bad?.classList.add('wrong');}}catch(e){if(feedback){feedback.hidden=false;feedback.className='cv43-feedback bad';feedback.textContent=e.message;}}});
      redraw();
    }

    root.querySelectorAll('[data-cv43-clue]').forEach(button=>button.addEventListener('click',()=>{const p=root.querySelector(`[data-cv43-clue-text="${button.dataset.cv43Clue}"]`);if(p)p.hidden=!p.hidden;}));

    const motionButton=root.querySelector('[data-cv43-motion]');
    const stored=localStorage.getItem('educanet.cv43.motion');
    const reduced=reduceBySystem||stored==='reduced';
    root.classList.toggle('motion-reduced',reduced);
    if(motionButton) motionButton.textContent=reduced?EduI18n.tr('Motion: omezený'):EduI18n.tr('Motion: auto');
    motionButton?.addEventListener('click',()=>{const now=!root.classList.contains('motion-reduced');root.classList.toggle('motion-reduced',now);localStorage.setItem('educanet.cv43.motion',now?'reduced':'auto');motionButton.textContent=now?EduI18n.tr('Motion: omezený'):EduI18n.tr('Motion: auto');});
  });
})();
