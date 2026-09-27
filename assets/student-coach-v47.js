var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
(()=>{
  const T=window.EduI18n;
  const storageKey='educanetCoachTimerEnd';
  const timer=document.querySelector('[data-coach-timer]');
  const value=timer?.querySelector('[data-coach-timer-value]');
  let ticker=null;
  const fmt=s=>`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
  const stop=()=>{if(ticker)clearInterval(ticker);ticker=null;localStorage.removeItem(storageKey);if(timer)timer.hidden=true;};
  const render=()=>{
    if(!timer||!value)return;
    const end=Number(localStorage.getItem(storageKey)||0);
    if(!end){timer.hidden=true;return;}
    const left=Math.max(0,Math.ceil((end-Date.now())/1000));
    timer.hidden=false;value.textContent=fmt(left);
    if(left<=0){stop();timer.hidden=false;value.textContent=T?EduI18n.tr('Hotovo'):'Hotovo';timer.querySelector('span').textContent=T?EduI18n.tr('Dej si krátkou pauzu nebo uzavři dnešní plán.'):'Dej si krátkou pauzu nebo uzavři dnešní plán.';}
  };
  const start=mins=>{localStorage.setItem(storageKey,String(Date.now()+Math.max(1,mins)*60000));if(ticker)clearInterval(ticker);render();ticker=setInterval(render,1000);};
  document.querySelectorAll('[data-coach-timer-start]').forEach(b=>b.addEventListener('click',()=>start(Number(b.dataset.coachTimerStart||25))));
  document.querySelectorAll('[data-coach-item-timer]').forEach(b=>b.addEventListener('click',()=>start(Number(b.dataset.coachItemTimer||5))));
  document.querySelector('[data-coach-timer-stop]')?.addEventListener('click',stop);
  if(Number(localStorage.getItem(storageKey)||0)>Date.now()){render();ticker=setInterval(render,1000);}
  document.addEventListener('keydown',e=>{if(e.altKey&&e.key.toLowerCase()==='s'&&!/input|textarea|select/i.test(document.activeElement?.tagName||'')){window.location.href='?view=study';}});
})();

// EDUCANET v47.1 · generation-first, fading and comfortable break cues.
(()=>{
  const T=window.EduI18n;
  const gen=document.querySelector('[data-generation-note]');
  if(gen){
    const key='educanetGeneration:'+String(gen.dataset.generationKey||'default');
    gen.value=sessionStorage.getItem(key)||'';
    gen.addEventListener('input',()=>sessionStorage.setItem(key,gen.value));
  }
  document.querySelector('[data-reveal-scaffold]')?.addEventListener('click',()=>{
    const scaffold=document.querySelector('[data-scaffold]');if(scaffold){scaffold.hidden=false;scaffold.scrollIntoView({behavior:'smooth',block:'start'});}
  });
  document.querySelectorAll('[data-reveal-step]').forEach(btn=>btn.addEventListener('click',()=>{
    const li=btn.closest('li');const ans=li?.querySelector('.faded-answer');if(ans){ans.hidden=false;btn.hidden=true;}
  }));
  let breakShown=false;
  const checkBreak=()=>{
    const end=Number(localStorage.getItem('educanetCoachTimerEnd')||0);if(!end||breakShown)return;
    const totalStart=Number(sessionStorage.getItem('educanetCoachTimerStartedAt')||0);
    if(totalStart&&Date.now()-totalStart>=20*60000){breakShown=true;document.body.dispatchEvent(new CustomEvent('educanet:study-break'));}
  };
  document.querySelectorAll('[data-coach-timer-start],[data-coach-item-timer]').forEach(btn=>btn.addEventListener('click',()=>{sessionStorage.setItem('educanetCoachTimerStartedAt',String(Date.now()));breakShown=false;}));
  setInterval(checkBreak,30000);
  document.body.addEventListener('educanet:study-break',()=>{
    const msg=document.createElement('div');msg.className='notice';msg.style.position='fixed';msg.style.right='18px';msg.style.bottom='18px';msg.style.zIndex='9999';
    const msgTitle=document.createElement('strong');msgTitle.textContent=T?EduI18n.tr('Krátká pauza?'):'Krátká pauza?';
    msg.appendChild(msgTitle);msg.appendChild(document.createElement('br'));
    msg.appendChild(document.createTextNode(T?EduI18n.tr('20 minut fokusu je za tebou. Protáhni se, podívej se mimo obrazovku a vrať se, až budeš chtít.'):'20 minut fokusu je za tebou. Protáhni se, podívej se mimo obrazovku a vrať se, až budeš chtít.'));
    document.body.appendChild(msg);setTimeout(()=>msg.remove(),9000);
  });
})();

// EDUCANET v47.2 · short corrective loop after an incorrect answer.
(()=>{
  const T=window.EduI18n;
  const root=document.querySelector('[data-corrective-cycle]');if(!root)return;
  const finish=root.querySelector('[data-corrective-finish]');
  const outcome=root.querySelector('[data-corrective-outcome]');
  const transfer=root.querySelector('[data-corrective-transfer]');
  const result=root.querySelector('[data-corrective-result]');
  const setReady=(value)=>{if(outcome)outcome.value=value;if(finish)finish.disabled=false;};
  if(transfer){
    transfer.querySelector('[data-corrective-check]')?.addEventListener('click',()=>{
      const selected=transfer.querySelector('input[name="v472_transfer_choice"]:checked');
      if(!selected){if(result){result.hidden=false;result.className='corrective-transfer-result needs-work';result.textContent=T?EduI18n.tr('Nejdřív zvol odpověď.'):'Nejdřív zvol odpověď.';}return;}
      const ok=Number(selected.value)===Number(transfer.dataset.correct);
      if(result){
        result.hidden=false;result.className='corrective-transfer-result '+(ok?'ok':'needs-work');
        result.textContent='';
        const rStrong=document.createElement('strong');
        rStrong.textContent=ok?(T?EduI18n.tr('✓ Přenos funguje.'):'✓ Přenos funguje.'):(T?EduI18n.tr('↻ Ještě jednou porovnej rozhodující podmínku.'):'↻ Ještě jednou porovnej rozhodující podmínku.');
        result.appendChild(rStrong);
        result.appendChild(document.createTextNode(' '+(ok?(T?EduI18n.tr('Stejný princip jsi poznal/a i v nové situaci.'):'Stejný princip jsi poznal/a i v nové situaci.'):String(transfer.dataset.why||(T?EduI18n.tr('Vrať se k principu a kontrastu výše.'):'Vrať se k principu a kontrastu výše.')))));
      }
      setReady(ok?'correct':'needs_review');
    });
  }
  const note=root.querySelector('[data-corrective-transfer-note]');
  if(note){
    const sync=()=>{if(note.value.trim().length>=12)setReady('self_checked');else if(finish)finish.disabled=true;};
    note.addEventListener('input',sync);sync();
  }
})();
