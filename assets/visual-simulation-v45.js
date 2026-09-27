(()=>{
  'use strict';
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
  const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
  const csrf=()=>document.querySelector('meta[name="csrf-token"]')?.content||'';
  const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));
  const sameValues=(a,b)=>Object.keys(a).every(k=>Number(a[k])===Number(b[k]));
  const esc=s=>String(s).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
  const scoreParam=(p,v)=>clamp(100-(Math.abs(v-Number(p.target))/Math.max(.0001,Number(p.max)-Number(p.min)))*180,0,100);
  const evaluate=(spec,values)=>{
    const scores={};
    spec.params.forEach(p=>scores[p.id]=scoreParam(p,Number(values[p.id]??p.value)));
    const arr=Object.values(scores),avg=arr.reduce((a,b)=>a+b,0)/Math.max(1,arr.length),spread=arr.length?Math.max(...arr)-Math.min(...arr):0,min=arr.length?Math.min(...arr):0;
    const quality=Math.round(avg),clarity=Math.round(clamp(avg*.78+(100-spread)*.22,0,100)),resilience=Math.round(clamp(avg*.62+min*.38,0,100)),risk=Math.round(clamp(100-resilience,0,100));
    const ordered=Object.entries(scores).sort((a,b)=>a[1]-b[1]);
    return {scores,metrics:{quality,clarity,resilience,risk},ready:quality>=72&&risk<=38,weakest:ordered[0]?.[0]||null,strongest:ordered.at(-1)?.[0]||null};
  };
  const format=(p,v)=>Number(v).toFixed(Number(p.step)<1?1:0)+(p.unit||'');
  const signed=n=>(n>0?'+':'')+n;
  const markContent=el=>{if(!el)return;if(EduI18n.locale&&EduI18n.locale!=='cs')el.setAttribute('lang','cs');else el.removeAttribute('lang');};
  const post=async data=>{
    const body=new URLSearchParams({...data,csrf:csrf()});
    const r=await fetch(location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8','X-Requested-With':'XMLHttpRequest'},body});
    const j=await r.json().catch(()=>({ok:false,error:EduI18n.tr('Server nevrátil JSON.')}));
    if(!r.ok||!j.ok)throw new Error(j.error||EduI18n.tr('Uložení se nepodařilo.'));
    return j;
  };

  document.querySelectorAll('[data-v45-sim]').forEach(root=>{
    let spec={},saved={};
    try{spec=JSON.parse(root.querySelector('[data-v45-spec]')?.textContent||'{}');saved=JSON.parse(root.querySelector('[data-v45-state]')?.textContent||'{}');}catch(e){return;}
    if(!spec.params?.length)return;

    const teacher=root.hasAttribute('data-v45-teacher');
    const controls=[...root.querySelectorAll('[data-v45-control]')];
    const byId=Object.fromEntries(spec.params.map(p=>[p.id,p]));
    const values=Object.fromEntries(spec.params.map(p=>[p.id,Number(p.value)]));
    const baseline={...values};
    let currentScenario='manual',motion=!reduced,slots={...(saved.slots||{})},lastEvaluation=evaluate(spec,values),scenarioWalk=null,spotlightIndex=-1;
    let history=[{values:{...values},label:EduI18n.tr('Výchozí problém'),metrics:{...lastEvaluation.metrics}}],historyIndex=0;
    const metricEls=Object.fromEntries([...root.querySelectorAll('[data-v45-metric]')].map(el=>[el.dataset.v45Metric,el]));
    const feedbackTitle=root.querySelector('[data-v45-feedback-title]'),feedbackCopy=root.querySelector('[data-v45-feedback-copy]'),scenarioLabel=root.querySelector('[data-v45-scenario-label]');
    const historyList=root.querySelector('[data-v45-history-list]'),undoBtn=root.querySelector('[data-v45-undo]'),redoBtn=root.querySelector('[data-v45-redo]');
    const causeEl=root.querySelector('[data-v45-cause]'),causeDetail=root.querySelector('[data-v45-cause-detail]'),weakestEl=root.querySelector('[data-v45-weakest]'),weakestDetail=root.querySelector('[data-v45-weakest-detail]'),tradeoffEl=root.querySelector('[data-v45-tradeoff]'),tradeoffDetail=root.querySelector('[data-v45-tradeoff-detail]');
    const setFeedback=(title,copy)=>{if(feedbackTitle){feedbackTitle.textContent=title;markContent(feedbackTitle);}if(feedbackCopy){feedbackCopy.textContent=copy;markContent(feedbackCopy);}};

    const syncControls=()=>controls.forEach(inp=>{
      const p=byId[inp.dataset.v45Control];if(!p)return;
      inp.value=String(values[p.id]);
      inp.closest('label')?.querySelector('output')?.replaceChildren(document.createTextNode(format(p,values[p.id])));
    });

    const applyVisual=ev=>{
      root.style.setProperty('--v45-quality',ev.metrics.quality+'%');root.style.setProperty('--v45-risk',ev.metrics.risk+'%');
      const design=root.querySelector('[data-v45-design-stage]');
      if(design){
        const v=id=>Number(values[id]??byId[id]?.value??0);
        const spacing=v('spacing')||v('gap')||16,contrast=v('contrast')||5,width=v('viewport')||v('line_width')*8||720,font=v('font_size')||Math.max(26,52-(v('density')||50)*.18),line=v('line_height')||1.45;
        design.style.setProperty('--sim-gap',clamp(spacing,6,48)+'px');design.style.setProperty('--sim-contrast',String(clamp(contrast/5,.45,1.8)));design.style.setProperty('--sim-width',clamp(width,280,1100)+'px');design.style.setProperty('--sim-font',clamp(font,18,70)+'px');design.style.setProperty('--sim-line',String(clamp(line,1,2.1)));
        design.style.setProperty('--sim-radius',clamp(10+(ev.metrics.quality*.08),10,20)+'px');design.style.setProperty('--sim-focus',(v('focus')||55)+'%');design.style.setProperty('--sim-crop',(v('crop')||35)+'%');design.style.setProperty('--sim-scale',String(clamp((v('scale')||100)/100,.5,5)));design.style.setProperty('--sim-duration',(v('duration')||280)+'ms');design.style.setProperty('--sim-distance',(v('distance')||32)+'px');design.style.setProperty('--sim-density',(v('density')||50)/100);design.style.setProperty('--sim-error',(v('error')||50)/100);design.style.setProperty('--sim-token',(v('token_use')||50)/100);design.style.setProperty('--sim-drift',(v('drift')||30)/100);
        design.classList.toggle('is-ready',ev.ready);design.classList.toggle('is-risk',ev.metrics.risk>55);
        design.querySelectorAll('[data-v45-stage-state]').forEach(state=>state.textContent=ev.ready?EduI18n.tr('funkční varianta'):ev.metrics.risk>55?EduI18n.tr('riziková varianta'):EduI18n.tr('rozpracovaná varianta'));
        design.querySelectorAll('[data-v45-stage-proof]').forEach(proof=>proof.textContent=ev.ready?EduI18n.tr('Model drží i při kombinaci hlavních parametrů. Teď ho naruš what-if scénářem.'):EduI18n.tr('Najdi parametr s největší odchylkou od cíle a změň jen ten.'));
      }
      const sys=root.querySelector('[data-v45-system-stage]');
      if(sys){
        sys.style.setProperty('--sim-health',ev.metrics.quality/100);sys.style.setProperty('--sim-risk',ev.metrics.risk/100);sys.classList.toggle('is-ready',ev.ready);sys.classList.toggle('is-risk',ev.metrics.risk>55);
        const nodes=[...sys.querySelectorAll('[data-v45-node]')],weakIdx=Math.max(0,spec.params.findIndex(p=>p.id===ev.weakest))%Math.max(1,nodes.length);
        nodes.forEach((n,i)=>{n.classList.toggle('weak',i===weakIdx&&(ev.scores[ev.weakest]??100)<55);n.style.opacity=String(clamp(.52+ev.metrics.quality/210+i*.025,.55,1));});
        const packet=sys.querySelector('[data-v45-packet]');if(packet&&motion&&!root.classList.contains('v45-frozen')){packet.getAnimations().forEach(a=>a.cancel());packet.animate([{transform:'translateX(0)',opacity:.25},{transform:'translateX(745px)',opacity:1}],{duration:clamp(800+ev.metrics.risk*20,900,3200),iterations:1,easing:'ease-in-out'});}
      }
    };

    const renderMetrics=(ev,prev=null)=>{
      Object.entries(ev.metrics).forEach(([id,val])=>{
        const el=metricEls[id];if(!el)return;
        const strong=el.querySelector('strong'),bar=el.querySelector('em'),delta=el.querySelector('[data-v45-metric-delta]');
        if(strong)strong.textContent=val+'%';if(bar)bar.style.width=val+'%';
        const d=prev?val-Number(prev.metrics[id]??val):0;if(delta)delta.textContent=prev?(d===0?EduI18n.tr('beze změny'):EduI18n.tr('{delta} b.',{delta:signed(d)})):'—';
        el.classList.toggle('bad',id==='risk'?val>50:val<55);el.classList.toggle('good',id==='risk'?val<35:val>75);el.classList.toggle('changed',!!prev&&d!==0);
      });
    };

    const renderCausal=(ev,changedId=null,oldValues=null,prev=null)=>{
      const weak=byId[ev.weakest];
      if(weakestEl){weakestEl.textContent=weak?.label||'—';markContent(weakestEl);}
      if(weakestDetail&&weak)weakestDetail.textContent=EduI18n.tr('Skóre {score} / 100 · aktuálně {current}, cíl {target}.',{score:Math.round(ev.scores[weak.id]),current:format(weak,values[weak.id]),target:format(weak,weak.target)});
      if(changedId&&byId[changedId]){
        const p=byId[changedId],before=oldValues?scoreParam(p,Number(oldValues[changedId])):scoreParam(p,Number(values[changedId])),after=scoreParam(p,Number(values[changedId])),move=after-before;
        if(causeEl){causeEl.textContent=p.label;markContent(causeEl);}
        if(causeDetail)causeDetail.textContent=EduI18n.tr('{before} → {after}. Pohyb {direction}.',{before:format(p,oldValues?.[changedId]??values[changedId]),after:format(p,values[changedId]),direction:move>1?EduI18n.tr('směrem k cíli'):move<-1?EduI18n.tr('od cíle'):EduI18n.tr('má zatím malý efekt')});
      }
      if(prev){
        const dq=ev.metrics.quality-prev.metrics.quality,dr=ev.metrics.risk-prev.metrics.risk;
        let title=EduI18n.tr('Stabilní změna'),detail=EduI18n.tr('Změna nepřinesla výrazný kompromis mezi funkčností a rizikem.');
        if(dq>0&&dr>0){title=EduI18n.tr('Výkon za cenu rizika');detail=EduI18n.tr('Funkčnost {dq} b., ale riziko {dr} b. Rozhodni, zda je kompromis přijatelný.',{dq:signed(dq),dr:signed(dr)});}
        else if(dq<0&&dr<0){title=EduI18n.tr('Bezpečnější, ale slabší');detail=EduI18n.tr('Riziko {dr} b., funkčnost {dq} b. Zvaž, co je v tomto scénáři prioritou.',{dq:signed(dq),dr:signed(dr)});}
        else if(dq>0&&dr<=0){title=EduI18n.tr('Čisté zlepšení');detail=EduI18n.tr('Funkčnost {dq} b. a riziko {dr} b. Ověř, zda efekt drží i v jiném scénáři.',{dq:signed(dq),dr:signed(dr)});}
        else if(dq<0&&dr>=0){title=EduI18n.tr('Dvojitý problém');detail=EduI18n.tr('Funkčnost {dq} b. a riziko {dr} b. Vrať se k příčině.',{dq:signed(dq),dr:signed(dr)});}
        if(tradeoffEl)tradeoffEl.textContent=title;if(tradeoffDetail)tradeoffDetail.textContent=detail;
      }
    };

    const update=(changedId=null,oldValues=null)=>{
      const prev=oldValues?evaluate(spec,oldValues):lastEvaluation,ev=evaluate(spec,values);
      renderMetrics(ev,oldValues?prev:null);applyVisual(ev);renderCausal(ev,changedId,oldValues,oldValues?prev:null);
      root.querySelectorAll('[data-v45-control-wrap]').forEach(w=>w.classList.toggle('is-active',w.dataset.v45ControlWrap===changedId));
      if(changedId&&byId[changedId]){
        const p=byId[changedId],before=oldValues?scoreParam(p,Number(oldValues[changedId])):0,after=scoreParam(p,Number(values[changedId])),improved=after>before+1,worse=after<before-1;
        setFeedback(improved?EduI18n.tr('{label}: změna pomohla',{label:p.label}):worse?EduI18n.tr('{label}: změna zvýšila odchylku',{label:p.label}):EduI18n.tr('{label}: malá změna',{label:p.label}),EduI18n.tr('{hint} Aktuálně {current}. Funkčnost {quality} %, riziko {risk} %.',{hint:p.hint,current:format(p,values[changedId]),quality:ev.metrics.quality,risk:ev.metrics.risk}));
      }
      lastEvaluation=ev;return ev;
    };

    const renderHistory=()=>{
      if(undoBtn)undoBtn.disabled=historyIndex<=0;if(redoBtn)redoBtn.disabled=historyIndex>=history.length-1;
      if(!historyList)return;
      const visible=history.slice(Math.max(0,history.length-8)).reverse();
      historyList.replaceChildren(...visible.map((h,i)=>{
        const li=document.createElement('li');li.className=history.length-1-i===historyIndex?'is-current':'';
        const span=document.createElement('span');span.textContent=h.label;markContent(span);
        const b=document.createElement('b');b.textContent=h.metrics.quality+'%';
        const small=document.createElement('small');small.textContent=EduI18n.tr('riziko {risk}%',{risk:h.metrics.risk});
        li.append(span,b,small);return li;
      }));
    };
    const commit=label=>{
      const current=history[historyIndex];if(current&&sameValues(current.values,values)){current.label=label;current.metrics={...evaluate(spec,values).metrics};renderHistory();return;}
      history=history.slice(0,historyIndex+1);history.push({values:{...values},label,metrics:{...evaluate(spec,values).metrics}});if(history.length>24)history.shift();historyIndex=history.length-1;renderHistory();
    };
    const restoreHistory=idx=>{
      if(idx<0||idx>=history.length)return;const old={...values};historyIndex=idx;Object.assign(values,history[idx].values);syncControls();currentScenario='history';if(scenarioLabel){scenarioLabel.textContent=history[idx].label;markContent(scenarioLabel);}setFeedback(EduI18n.tr('Vráceno v experimentu.'),history[idx].label);update(Object.keys(values).find(k=>values[k]!==old[k])||null,old);renderHistory();
    };

    const renderSlots=()=>{
      ['a','b'].forEach(slot=>{
        const card=root.querySelector(`[data-v45-slot-card="${slot}"]`),box=root.querySelector(`[data-v45-slot-metrics="${slot}"]`),s=slots[slot];if(!card||!box)return;
        const st=card.querySelector('strong');if(st)st.textContent=s?EduI18n.tr('uloženo'):EduI18n.tr('zatím prázdná');
        if(s){
          const qEl=document.createElement('span'),rEl=document.createElement('span');
          qEl.appendChild(document.createTextNode(EduI18n.tr('funkčnost ')));const qb=document.createElement('b');qb.textContent=s.metrics.quality+'%';qEl.appendChild(qb);
          rEl.appendChild(document.createTextNode(EduI18n.tr('riziko ')));const rb=document.createElement('b');rb.textContent=s.metrics.risk+'%';rEl.appendChild(rb);
          box.replaceChildren(qEl,rEl);
        }else{box.replaceChildren();}
        const mini=root.querySelector(`[data-v45-mini="${slot}"]`);if(mini&&s){mini.style.setProperty('--mini-quality',s.metrics.quality+'%');mini.style.setProperty('--mini-risk',s.metrics.risk+'%');}
      });
      const a=slots.a,b=slots.b,title=root.querySelector('[data-v45-delta-title]'),copy=root.querySelector('[data-v45-delta-copy]'),detail=root.querySelector('[data-v45-ab-detail]'),table=root.querySelector('[data-v45-ab-table]'),count=root.querySelector('[data-v45-ab-count]');
      if(!title||!copy)return;
      if(!a?.metrics||!b?.metrics){title.textContent=EduI18n.tr('Ulož A a B');copy.textContent=EduI18n.tr('Potom vysvětli, který konkrétní parametr změnil výsledek.');if(detail)detail.hidden=true;return;}
      const dq=b.metrics.quality-a.metrics.quality,dr=b.metrics.risk-a.metrics.risk,changed=spec.params.filter(p=>Number(a.params?.[p.id])!==Number(b.params?.[p.id]));
      title.textContent=EduI18n.tr('B: {dq} funkčnost · {dr} riziko',{dq:signed(dq),dr:signed(dr)});
      copy.textContent=dq>0&&dr<=0?EduI18n.tr('B zlepšila funkčnost bez navýšení rizika. Ověř, která změna je hlavní příčinou.'):dq>0?EduI18n.tr('B zlepšila výsledek, ale za cenu vyššího rizika. Je tento kompromis přijatelný?'):EduI18n.tr('B není automaticky lepší. Vrať se k rozdílům parametrů a hledej příčinu.');
      if(detail)detail.hidden=false;if(count)count.textContent=EduI18n.tr('{n} {word}',{n:changed.length,word:changed.length===1?EduI18n.tr('změna'):EduI18n.tr('změny')});
      if(table){
        const rows=spec.params.map(p=>{
          const av=Number(a.params?.[p.id]??p.value),bv=Number(b.params?.[p.id]??p.value),d=bv-av;
          const row=document.createElement('div');row.className=d!==0?'is-changed':'';
          const strong=document.createElement('strong');strong.textContent=p.label;markContent(strong);
          const spanA=document.createElement('span');spanA.textContent='A '+format(p,av);
          const spanB=document.createElement('span');spanB.textContent='B '+format(p,bv);
          const bEl=document.createElement('b');bEl.textContent=d===0?EduI18n.tr('beze změny'):(d>0?'+':'')+format({...p,unit:p.unit||''},d);
          row.append(strong,spanA,spanB,bEl);return row;
        });
        const metricRow=document.createElement('div');metricRow.className='is-metric';
        const metricStrong=document.createElement('strong');metricStrong.textContent=EduI18n.tr('Funkčnost / riziko');
        const metricA=document.createElement('span');metricA.textContent='A '+a.metrics.quality+'% / '+a.metrics.risk+'%';
        const metricB=document.createElement('span');metricB.textContent='B '+b.metrics.quality+'% / '+b.metrics.risk+'%';
        const metricDelta=document.createElement('b');metricDelta.textContent=signed(dq)+' / '+signed(dr);
        metricRow.append(metricStrong,metricA,metricB,metricDelta);
        table.replaceChildren(...rows,metricRow);
      }
    };

    const resetScenarioWalk=()=>{scenarioWalk=null;const coach=root.querySelector('[data-v45-scenario-coach]');if(coach)coach.hidden=true;root.querySelectorAll('[data-v45-scenario-card]').forEach(c=>c.classList.remove('is-running'));};
    const renderScenarioWalk=()=>{
      const coach=root.querySelector('[data-v45-scenario-coach]');if(!coach||!scenarioWalk){if(coach)coach.hidden=true;return;}
      coach.hidden=false;const total=scenarioWalk.steps.length,next=scenarioWalk.steps[scenarioWalk.index];
      const title=root.querySelector('[data-v45-scenario-step-title]'),copy=root.querySelector('[data-v45-scenario-step-copy]'),progress=root.querySelector('[data-v45-scenario-progress]'),btn=root.querySelector('[data-v45-scenario-next]');
      if(progress)progress.textContent=`${scenarioWalk.index} / ${total}`;
      if(next){const p=byId[next.id];if(title){title.textContent=EduI18n.tr('Další: {label}',{label:p.label});markContent(title);}if(copy)copy.textContent=EduI18n.tr('{before} → {after}. Nejdřív předpověz směr dopadu, pak pokračuj.',{before:format(p,values[p.id]),after:format(p,next.value)});if(btn)btn.textContent=scenarioWalk.index===0?EduI18n.tr('Spustit první změnu'):EduI18n.tr('Další změna');}
      else{if(title){title.textContent=EduI18n.tr('Scénář dokončen');title.removeAttribute('lang');}if(copy)copy.textContent=EduI18n.tr('Porovnej výsledek s výchozím stavem a pojmenuj změnu s největším dopadem.');if(btn)btn.textContent=EduI18n.tr('Hotovo');}
    };
    const startScenarioWalk=w=>{
      resetScenarioWalk();const steps=spec.params.filter(p=>Number(values[p.id])!==Number(w.values?.[p.id])).map(p=>({id:p.id,value:Number(w.values[p.id])}));
      scenarioWalk={id:w.id,title:w.title,steps,index:0};root.querySelector(`[data-v45-scenario-card="${CSS.escape(w.id)}"]`)?.classList.add('is-running');currentScenario='step:'+w.id;if(scenarioLabel){scenarioLabel.textContent=EduI18n.tr('Krokování: {title}',{title:w.title});markContent(scenarioLabel);}setFeedback(EduI18n.tr('Scénář je připraven.'),EduI18n.tr('Před každým krokem vyslov predikci: co se změní a proč.'));renderScenarioWalk();
    };

    controls.forEach(inp=>{
      inp.addEventListener('input',()=>{const old={...values},id=inp.dataset.v45Control,p=byId[id];values[id]=Number(inp.value);inp.closest('label')?.querySelector('output')?.replaceChildren(document.createTextNode(format(p,values[id])));currentScenario='manual';if(scenarioLabel){scenarioLabel.textContent=EduI18n.tr('Ruční experiment');scenarioLabel.removeAttribute('lang');}update(id,old);});
      inp.addEventListener('change',()=>{const p=byId[inp.dataset.v45Control];commit(`${p.label}: ${format(p,values[p.id])}`);resetScenarioWalk();});
    });

    root.querySelectorAll('[data-v45-set-target]').forEach(btn=>btn.addEventListener('click',()=>{const id=btn.dataset.v45SetTarget,p=byId[id],old={...values};values[id]=Number(p.target);syncControls();currentScenario='manual';if(scenarioLabel){scenarioLabel.textContent=EduI18n.tr('Ruční experiment · cíl');scenarioLabel.removeAttribute('lang');}update(id,old);commit(EduI18n.tr('{label}: nastaven cíl',{label:p.label}));resetScenarioWalk();}));
    root.querySelector('[data-v45-reset]')?.addEventListener('click',()=>{const old={...values};Object.assign(values,baseline);syncControls();currentScenario='manual';if(scenarioLabel){scenarioLabel.textContent=EduI18n.tr('Výchozí problém');scenarioLabel.removeAttribute('lang');}setFeedback(EduI18n.tr('Resetováno.'),EduI18n.tr('Teď změň jen jednu proměnnou a popiš její efekt.'));update(Object.keys(values).find(k=>values[k]!==old[k])||null,old);commit(EduI18n.tr('Reset na výchozí problém'));resetScenarioWalk();});
    undoBtn?.addEventListener('click',()=>restoreHistory(historyIndex-1));redoBtn?.addEventListener('click',()=>restoreHistory(historyIndex+1));
    root.querySelector('[data-v45-history-clear]')?.addEventListener('click',()=>{history=[{values:{...values},label:EduI18n.tr('Nový začátek experimentu'),metrics:{...evaluate(spec,values).metrics}}];historyIndex=0;renderHistory();});

    root.querySelectorAll('[data-v45-scenario]').forEach(btn=>btn.addEventListener('click',()=>{const w=spec.what_if.find(x=>x.id===btn.dataset.v45Scenario);if(!w)return;const old={...values};Object.assign(values,w.values);syncControls();currentScenario=w.id;if(scenarioLabel){scenarioLabel.textContent=w.title;markContent(scenarioLabel);}const changedIds=Object.keys(values).filter(k=>values[k]!==old[k]);update(changedIds.length===1?changedIds[0]:null,old);if(changedIds.length>1){if(causeEl){causeEl.textContent=EduI18n.tr('Kombinovaný zásah');causeEl.removeAttribute('lang');}if(causeDetail)causeDetail.textContent=EduI18n.tr('Scénář změnil {n} parametrů současně. Pro čistou kauzalitu použij režim Krokovat.',{n:changedIds.length});}setFeedback(w.title,w.text+(changedIds.length>1?' '+EduI18n.tr('Pro určení příčiny použij Krokovat.'):''));commit(EduI18n.tr('Scénář: {title}',{title:w.title}));resetScenarioWalk();}));
    root.querySelectorAll('[data-v45-scenario-step]').forEach(btn=>btn.addEventListener('click',()=>{const w=spec.what_if.find(x=>x.id===btn.dataset.v45ScenarioStep);if(w)startScenarioWalk(w);}));
    root.querySelector('[data-v45-scenario-next]')?.addEventListener('click',()=>{if(!scenarioWalk)return;if(scenarioWalk.index>=scenarioWalk.steps.length){resetScenarioWalk();return;}const step=scenarioWalk.steps[scenarioWalk.index],p=byId[step.id],old={...values};values[step.id]=step.value;scenarioWalk.index++;syncControls();update(step.id,old);commit(EduI18n.tr('What-if {i}/{total}: {label}',{i:scenarioWalk.index,total:scenarioWalk.steps.length,label:p.label}));renderScenarioWalk();});
    root.querySelector('[data-v45-scenario-cancel]')?.addEventListener('click',resetScenarioWalk);

    root.querySelector('[data-v45-motion]')?.addEventListener('click',e=>{motion=!motion;e.currentTarget.textContent=motion?EduI18n.tr('Motion: auto'):EduI18n.tr('Motion: omezený');root.classList.toggle('v45-motion-off',!motion);});
    root.querySelectorAll('[data-v45-fullscreen]').forEach(b=>b.addEventListener('click',()=>document.fullscreenElement?document.exitFullscreen?.():root.requestFullscreen?.()));

    root.querySelectorAll('[data-v45-save]').forEach(btn=>btn.addEventListener('click',async()=>{
      const slot=btn.dataset.v45Save,fb=root.querySelector('[data-v45-save-feedback]'),note=root.querySelector('[data-v45-evidence-note]')?.value?.trim()||'';btn.disabled=true;if(fb)fb.textContent=EduI18n.tr('ukládám…');
      try{
        if(teacher){const ev=evaluate(spec,values);slots[slot]={params:{...values},metrics:{...ev.metrics},ready:ev.ready,scenario:currentScenario,note};if(fb)fb.textContent=EduI18n.tr('✓ Varianta je zamknutá pouze v tomto projektoru.');}
        else{const r=await post({action:'v45_sim_save',lesson_number:String(spec.lesson_number),slot,scenario:currentScenario,params:JSON.stringify(values),note});slots[slot]={params:r.params,metrics:r.metrics,ready:r.ready,scenario:currentScenario,note:r.note||note};if(fb)fb.textContent=r.ready?EduI18n.tr('✓ Varianta je uložená a splňuje evidence checkpoint.'):EduI18n.tr('✓ Uloženo. Ještě zkus snížit riziko nebo zvýšit funkčnost.');}
        renderSlots();
      }catch(err){if(fb)fb.textContent=err.message;}finally{btn.disabled=false;}
    }));
    root.querySelectorAll('[data-v45-load]').forEach(btn=>btn.addEventListener('click',()=>{const s=slots[btn.dataset.v45Load];if(!s?.params)return;const old={...values};Object.assign(values,s.params);syncControls();currentScenario='compare-'+btn.dataset.v45Load;const slotLabel=btn.dataset.v45Load.toUpperCase();if(scenarioLabel){scenarioLabel.textContent=EduI18n.tr('Načtená varianta {slot}',{slot:slotLabel});scenarioLabel.removeAttribute('lang');}setFeedback(EduI18n.tr('Varianta {slot} načtena.',{slot:slotLabel}),EduI18n.tr('Teď změň jednu proměnnou a sleduj, zda dokážeš vysvětlit rozdíl.'));update(Object.keys(values).find(k=>values[k]!==old[k])||null,old);commit(EduI18n.tr('Načtena varianta {slot}',{slot:slotLabel}));resetScenarioWalk();}));
    root.querySelector('[data-v45-swap]')?.addEventListener('click',()=>{[slots.a,slots.b]=[slots.b,slots.a];renderSlots();});

    const freeze=root.querySelector('[data-v45-freeze]');freeze?.addEventListener('click',()=>{root.classList.toggle('v45-frozen');freeze.textContent=root.classList.contains('v45-frozen')?EduI18n.tr('Pokračovat'):'Freeze & ask';});
    const phaseCopy={
      predict:[EduI18n.tr('PREDIKCE'),EduI18n.tr('Který parametr podle třídy změní výsledek nejvíc?'),EduI18n.tr('Neukazuj čísla. Nech žáky formulovat očekávaný směr změny a důvod.')],
      discuss:[EduI18n.tr('DISKUSE'),EduI18n.tr('Co se po zásahu skutečně změnilo?'),EduI18n.tr('Čísla jsou stále skrytá. Porovnejte pozorovaný vizuální stav a hledejte příčinu.')],
      reveal:['REVEAL',EduI18n.tr('Sedí predikce s evidencí?'),EduI18n.tr('Odhal metriky a feedback. Ptejte se, co data dokazují a co z nich naopak tvrdit nelze.')]
    };
    const setTeacherPhase=phase=>{
      if(!teacher||!phaseCopy[phase])return;root.dataset.v45TeacherPhase=phase;root.querySelectorAll('[data-v45-phase]').forEach(b=>b.classList.toggle('is-active',b.dataset.v45Phase===phase));
      root.classList.toggle('v45-teacher-revealed',phase==='reveal');root.querySelector('.v45-feedback')?.classList.toggle('teacher-hidden',phase!=='reveal');
      const [k,t,c]=phaseCopy[phase],ke=root.querySelector('[data-v45-cue-kicker]'),te=root.querySelector('[data-v45-cue-title]'),ce=root.querySelector('[data-v45-cue-copy]');if(ke)ke.textContent=k;if(te)te.textContent=t;if(ce)ce.textContent=c;
    };
    root.querySelectorAll('[data-v45-phase]').forEach(btn=>btn.addEventListener('click',()=>setTeacherPhase(btn.dataset.v45Phase)));
    const setSpotlight=idx=>{const wraps=[...root.querySelectorAll('[data-v45-control-wrap]')];if(!wraps.length)return;spotlightIndex=(idx+wraps.length)%wraps.length;root.classList.add('v45-has-spotlight');wraps.forEach((w,i)=>w.classList.toggle('is-spotlight',i===spotlightIndex));wraps[spotlightIndex].scrollIntoView?.({block:'nearest',behavior:reduced?'auto':'smooth'});};
    root.querySelector('[data-v45-spotlight]')?.addEventListener('click',()=>setSpotlight(spotlightIndex+1));
    root.querySelector('[data-v45-teacher-reset]')?.addEventListener('click',()=>{const old={...values};Object.assign(values,baseline);slots={};syncControls();renderSlots();setTeacherPhase('predict');root.classList.remove('v45-frozen','v45-has-spotlight');if(freeze)freeze.textContent='Freeze & ask';root.querySelectorAll('[data-v45-control-wrap]').forEach(w=>w.classList.remove('is-spotlight'));spotlightIndex=-1;currentScenario='manual';if(scenarioLabel){scenarioLabel.textContent=EduI18n.tr('Výchozí problém');scenarioLabel.removeAttribute('lang');}update(Object.keys(values).find(k=>values[k]!==old[k])||null,old);resetScenarioWalk();});
    if(teacher){root.tabIndex=0;root.addEventListener('click',e=>{if(!/INPUT|TEXTAREA|BUTTON|SELECT/.test(e.target.tagName))root.focus({preventScroll:true});});root.addEventListener('keydown',e=>{if(/INPUT|TEXTAREA|SELECT/.test(e.target.tagName))return;const key=e.key.toLowerCase();if(key==='p')setTeacherPhase('predict');else if(key==='d')setTeacherPhase('discuss');else if(key==='r')setTeacherPhase('reveal');else if(key==='f')freeze?.click();else if(e.key==='ArrowRight'){e.preventDefault();setSpotlight(spotlightIndex+1);}else if(e.key==='ArrowLeft'){e.preventDefault();setSpotlight(spotlightIndex<0?spec.params.length-1:spotlightIndex-1);}else if(['1','2','3'].includes(e.key)){const w=spec.what_if[Number(e.key)-1];if(w)root.querySelector(`[data-v45-scenario-step="${CSS.escape(w.id)}"]`)?.click();}});setTeacherPhase('predict');}

    syncControls();renderSlots();renderHistory();update();
  });
})();
