(()=>{
  'use strict';
  const n=(row,key,fallback=0)=>{const v=Number(row.dataset[key]);return Number.isFinite(v)?v:fallback;};
  const ruleMatch=(row,rule)=>{
    if(!rule||!rule.field)return true;
    const f=rule.field,op=rule.op||'gte',want=String(rule.value??'').trim();
    let actual;
    const map={mastery:'mastery',avg_grade:'grade',inactive_days:'inactiveDays',score_drop:'scoreDrop',active_tasks:'taskActive',returned:'returned',published:'published',tests:'tests',labs:'labs'};
    if(f==='marker') actual=String(row.dataset.marker||'none');
    else if(f==='status') actual=String(row.dataset.status||'');
    else if(f==='priority') actual=String(row.dataset.priority||'');
    else if(f==='task_status'){
      const active=n(row,'taskActive'),done=n(row,'taskDone'),total=n(row,'taskTotal');
      actual=active>0?'active':done>0?'done':total===0?'no_task':'all';
    } else if(f==='task_due'){
      actual=n(row,'taskOverdue')>0?'overdue':n(row,'taskToday')>0?'today':n(row,'taskNext3')>0?'next3':n(row,'taskNext7')>0?'next7':n(row,'taskNoDue')>0?'no_due':'all';
    } else actual=n(row,map[f]||f,0);
    if(typeof actual==='number'){
      const w=Number(want);if(!Number.isFinite(w))return true;
      return op==='gte'?actual>=w:op==='lte'?actual<=w:op==='gt'?actual>w:op==='lt'?actual<w:op==='neq'?actual!==w:actual===w;
    }
    return op==='neq'?String(actual)!==want:String(actual)===want;
  };
  const filterbar=document.querySelector('[data-class-filterbar]');
  const table=document.querySelector('[data-class-results-table]');
  if(filterbar&&table){
    let rules=[];try{rules=JSON.parse(filterbar.dataset.smartRules||'[]');}catch(_){rules=[];}
    const mode=filterbar.dataset.smartMode==='any'?'any':'all';
    const applySmart=()=>{
      if(!rules.length)return;
      const rows=[...table.querySelectorAll('[data-result-row]')];
      rows.forEach(row=>{
        if(row.hidden)return;
        const ok=mode==='any'?rules.some(r=>ruleMatch(row,r)):rules.every(r=>ruleMatch(row,r));
        if(!ok){row.hidden=true;const c=row.querySelector('[data-row-select]');if(c)c.checked=false;}
      });
      const visible=rows.filter(r=>!r.hidden);
      const visibleCount=document.querySelector('[data-visible-count]');if(visibleCount)visibleCount.textContent=String(visible.length);
      const count=document.querySelector('[data-bulk-count]');const checks=rows.map(r=>r.querySelector('[data-row-select]')).filter(Boolean);if(count)count.textContent=String(checks.filter(c=>c.checked).length);
      const selectVisible=document.querySelector('[data-select-visible]');if(selectVisible){const vcs=visible.map(r=>r.querySelector('[data-row-select]')).filter(Boolean),sel=vcs.filter(c=>c.checked).length;selectVisible.checked=vcs.length>0&&sel===vcs.length;selectVisible.indeterminate=sel>0&&sel<vcs.length;}
      const preset=filterbar.dataset.activePreset||'';if(preset)document.querySelectorAll('[data-saved-filter-item]').forEach(item=>item.classList.toggle('active',item.dataset.filterId===preset));
    };
    ['input','change'].forEach(ev=>filterbar.addEventListener(ev,()=>queueMicrotask(applySmart)));
    document.querySelector('[data-class-filter-clear]')?.addEventListener('click',()=>{rules=[];filterbar.dataset.activePreset='';const u=new URL(location.href);u.searchParams.delete('preset');history.replaceState(null,'',u.pathname+'?'+u.searchParams.toString());queueMicrotask(applySmart);});
    queueMicrotask(applySmart);
  }

  document.querySelectorAll('[data-bulk-form]').forEach(form=>form.addEventListener('submit',event=>{
    const action=event.submitter?.value||'';
    const count=form.querySelectorAll('[data-row-select]:checked').length;
    const guarded=['teacher_bulk_task_due','teacher_bulk_task_priority','teacher_bulk_remind','teacher_bulk_note','teacher_bulk_resource','teacher_bulk_intervention'];
    if(!guarded.includes(action))return;
    if(!count){event.preventDefault();return;}
    const labels={teacher_bulk_task_due:'změnit termín aktivních úkolů',teacher_bulk_task_priority:'změnit prioritu aktivních úkolů',teacher_bulk_remind:'připomenout aktivní úkoly',teacher_bulk_note:'přidat týmovou poznámku',teacher_bulk_resource:'přiřadit materiál',teacher_bulk_intervention:'vytvořit intervenci'};
    if(action==='teacher_bulk_note'&&!form.querySelector('[name="note_text"]')?.value.trim()){event.preventDefault();form.querySelector('[name="note_text"]')?.focus();return;}
    if(action==='teacher_bulk_resource'&&!form.querySelector('[name="resource_ref"]')?.value.trim()){event.preventDefault();form.querySelector('[name="resource_ref"]')?.focus();return;}
    if(action==='teacher_bulk_intervention'&&!form.querySelector('[name="intervention_title"]')?.value.trim()){event.preventDefault();form.querySelector('[name="intervention_title"]')?.focus();return;}
    if(!confirm(`Opravdu ${labels[action]} pro ${count} vybraných studentů?`))event.preventDefault();
  }));

  const builder=document.querySelector('[data-smart-filter-builder]');
  if(builder){
    const list=builder.querySelector('[data-rule-list]');
    if(list){
      const add=document.createElement('button');add.type='button';add.className='text-button ops-add-rule';add.textContent='+ Přidat pravidlo';list.after(add);
      add.addEventListener('click',()=>{
        const rows=[...list.querySelectorAll('[data-rule-row]')];if(rows.length>=8)return;
        const clone=rows[0]?.cloneNode(true);if(!clone)return;clone.querySelectorAll('select,input').forEach(el=>el.value='');const op=clone.querySelector('[name="rule_op[]"]');if(op)op.value='gte';list.appendChild(clone);if(rows.length+1>=8)add.disabled=true;
      });
    }
  }



  document.querySelectorAll('[data-intervention-template]').forEach(select=>{
    select.addEventListener('change',()=>{
      const option=select.selectedOptions?.[0];if(!option?.dataset.template)return;
      let t={};try{t=JSON.parse(option.dataset.template||'{}');}catch(_){return;}
      const form=select.closest('[data-intervention-form]');if(!form)return;
      const set=(name,value,force=false)=>{const el=form.querySelector(`[name="${name}"]`);if(!el)return;if(force||!String(el.value||'').trim())el.value=value??'';};
      set('intervention_title',t.title);set('intervention_problem',t.problem);set('intervention_success',t.success);set('intervention_material',t.material);set('intervention_micro_task',t.micro_task);set('intervention_checkpoint',t.checkpoint);set('intervention_retry',t.retry);
      const due=form.querySelector('[name="intervention_review_due"]');if(due&&!due.value&&Number(t.review_days)>0){const d=new Date();d.setHours(12,0,0,0);d.setDate(d.getDate()+Number(t.review_days));due.value=d.toISOString().slice(0,10);}
      const task=form.querySelector('[name="create_student_task"]');if(task&&t.create_task)task.checked=true;
    });
  });
  document.querySelectorAll('[data-copy-message]').forEach(button=>button.addEventListener('click',async()=>{
    const box=document.querySelector('[data-message-preview]');if(!box)return;const text=String(box.value||box.textContent||'');
    try{await navigator.clipboard.writeText(text);const old=button.textContent;button.textContent='Zkopírováno ✓';button.classList.add('ops-copy-ok');setTimeout(()=>{button.textContent=old;button.classList.remove('ops-copy-ok');},1600);}catch(_){box.focus();box.select();document.execCommand?.('copy');}
  }));

  const backdrop=document.querySelector('[data-command-backdrop]'),input=document.querySelector('[data-command-input]'),results=document.querySelector('[data-command-results]');
  if(backdrop&&input&&results){
    const items=()=>[...results.querySelectorAll('[data-command-item]')].filter(i=>!i.hidden);let active=0;
    const paint=()=>items().forEach((i,x)=>i.classList.toggle('active',x===active));
    const open=()=>{backdrop.hidden=false;active=0;input.value='';[...results.querySelectorAll('[data-command-item]')].forEach(i=>i.hidden=false);paint();setTimeout(()=>input.focus(),0);};
    const close=()=>{backdrop.hidden=true;};
    document.querySelectorAll('[data-command-open]').forEach(b=>b.addEventListener('click',open));document.querySelectorAll('[data-command-close]').forEach(b=>b.addEventListener('click',close));
    document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();backdrop.hidden?open():close();return;}if(backdrop.hidden)return;if(e.key==='Escape'){e.preventDefault();close();}else if(e.key==='ArrowDown'){e.preventDefault();active=Math.min(items().length-1,active+1);paint();}else if(e.key==='ArrowUp'){e.preventDefault();active=Math.max(0,active-1);paint();}else if(e.key==='Enter'){const i=items()[active];if(i){e.preventDefault();location.href=i.href;}}});
    input.addEventListener('input',()=>{const q=(input.value||'').toLocaleLowerCase('cs').trim();[...results.querySelectorAll('[data-command-item]')].forEach(i=>i.hidden=!!q&&!String(i.dataset.search||'').toLocaleLowerCase('cs').includes(q));active=0;paint();});
    backdrop.addEventListener('click',e=>{if(e.target===backdrop)close();});
  }
})();
