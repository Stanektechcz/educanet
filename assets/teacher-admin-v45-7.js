(()=>{
  'use strict';

  const rubricForm=document.querySelector('[data-rubric-form]');
  if(rubricForm){
    const inputs=[...rubricForm.querySelectorAll('[data-rubric-score]')];
    const score=rubricForm.querySelector('[data-score]');
    const suggested=rubricForm.querySelector('[data-suggested]');
    const max=Math.max(1,Number(rubricForm.dataset.max||1));
    const grade=points=>points/max>=.92?1:points/max>=.80?2:points/max>=.64?3:points/max>=.48?4:5;
    const update=()=>{const total=inputs.reduce((sum,input)=>sum+Math.max(0,Number(input.value||0)),0);if(score)score.textContent=String(total);if(suggested)suggested.textContent='návrh '+grade(total);};
    inputs.forEach(input=>input.addEventListener('input',update,{passive:true}));
    update();
  }

  document.querySelectorAll('[data-ml-template]').forEach(button=>button.addEventListener('click',()=>{
    const form=document.querySelector('[data-ml-authoring-form]');if(!form)return;
    try{
      const template=JSON.parse(button.dataset.mlTemplate||'{}');
      const set=(name,value)=>{const field=form.querySelector(`[name="${name}"]`);if(field)field.value=value??'';};
      set('title',template.title);set('brief',template.brief);(template.choices||[]).forEach((value,index)=>set('choice_'+index,value));set('correct',template.correct??0);set('why',template.why);
      form.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});
    }catch(_){/* malformed templates are ignored */}
  }));

  const navGroups=[...document.querySelectorAll('.teacher-nav-group')];
  navGroups.forEach(group=>group.addEventListener('toggle',()=>{
    if(!group.open)return;
    navGroups.forEach(other=>{if(other!==group)other.open=false;});
  }));
  document.addEventListener('click',event=>{
    if(navGroups.some(group=>group.contains(event.target)))return;
    navGroups.forEach(group=>{group.open=false;});
  });
  document.addEventListener('keydown',event=>{
    if(event.key!=='Escape')return;
    navGroups.forEach(group=>{group.open=false;});
  });

  const resultTable=document.querySelector('[data-class-results-table]');
  if(resultTable){
    const form=resultTable.closest('[data-bulk-form]');
    const tbody=resultTable.querySelector('tbody');
    const rows=[...resultTable.querySelectorAll('[data-result-row]')];
    const search=document.querySelector('[data-class-result-search]');
    const sort=document.querySelector('[data-class-result-sort]');
    const status=document.querySelector('[data-class-result-status]');
    const priority=document.querySelector('[data-class-result-priority]');
    const taskStatus=document.querySelector('[data-class-task-status]');
    const taskDue=document.querySelector('[data-class-task-due]');
    const classJump=document.querySelector('[data-admin-class-jump]');
    const clearFilters=document.querySelector('[data-class-filter-clear]');
    const empty=document.querySelector('[data-class-results-empty]');
    const savedPanel=document.querySelector('[data-saved-filter-panel]');
    const saveFilterForm=savedPanel?.querySelector('[data-save-filter-form]');
    const savedItems=[...(savedPanel?.querySelectorAll('[data-saved-filter-item]')||[])];
    const selectVisible=document.querySelector('[data-select-visible]');
    const clearSelection=document.querySelector('[data-clear-selection]');
    const count=document.querySelector('[data-bulk-count]');
    const visibleCount=document.querySelector('[data-visible-count]');
    const selectionButtons=[...document.querySelectorAll('[data-requires-selection]')];
    const checks=rows.map(row=>row.querySelector('[data-row-select]')).filter(Boolean);
    const total=rows.length;
    const text=value=>(value||'').toLocaleLowerCase('cs');
    const num=(row,key,fallback)=>{const value=Number(row.dataset[key]);return Number.isFinite(value)?value:fallback;};
    const priorityRank={high:3,medium:2,normal:1};
    const compare=(a,b,mode)=>{
      if(mode==='name')return text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
      if(mode==='project_desc')return num(b,'project',-1)-num(a,'project',-1)||text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
      if(mode==='grade_asc')return num(a,'grade',99)-num(b,'grade',99)||text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
      if(mode==='mastery_desc')return num(b,'mastery',-1)-num(a,'mastery',-1)||text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
      if(mode==='activity_desc')return num(b,'activity',0)-num(a,'activity',0)||text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
      if(mode==='priority')return (priorityRank[b.dataset.priority]||1)-(priorityRank[a.dataset.priority]||1)||num(b,'attention',0)-num(a,'attention',0)||text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
      return num(b,'attention',0)-num(a,'attention',0)||text(a.dataset.name).localeCompare(text(b.dataset.name),'cs');
    };
    const selectedCount=()=>checks.filter(check=>check.checked).length;
    const visibleRows=()=>rows.filter(row=>!row.hidden);
    const syncSelection=()=>{
      const selected=selectedCount();
      if(count)count.textContent=String(selected);
      selectionButtons.forEach(button=>button.disabled=selected===0);
      if(clearSelection)clearSelection.disabled=selected===0;
      const visible=visibleRows();
      const visibleChecks=visible.map(row=>row.querySelector('[data-row-select]')).filter(Boolean);
      const selectedVisible=visibleChecks.filter(check=>check.checked).length;
      if(selectVisible){selectVisible.checked=visibleChecks.length>0&&selectedVisible===visibleChecks.length;selectVisible.indeterminate=selectedVisible>0&&selectedVisible<visibleChecks.length;selectVisible.disabled=visibleChecks.length===0;}
    };
    const syncSavedFilters=()=>{
      const currentClass=classJump?.value||savedPanel?.dataset.currentClass||'';
      const currentStatus=status?.value||'all';
      const currentPriority=priority?.value||'all';
      const currentTaskStatus=taskStatus?.value||'all';
      const currentTaskDue=taskDue?.value||'all';
      savedItems.forEach(item=>item.classList.toggle('active',item.dataset.filterClass===currentClass&&item.dataset.filterStatus===currentStatus&&item.dataset.filterPriority===currentPriority&&(item.dataset.filterTaskStatus||'all')===currentTaskStatus&&(item.dataset.filterTaskDue||'all')===currentTaskDue));
      if(saveFilterForm){
        const classField=saveFilterForm.querySelector('[data-save-filter-class]');
        const statusField=saveFilterForm.querySelector('[data-save-filter-status]');
        const priorityField=saveFilterForm.querySelector('[data-save-filter-priority]');
        const taskStatusField=saveFilterForm.querySelector('[data-save-filter-task-status]');
        const taskDueField=saveFilterForm.querySelector('[data-save-filter-task-due]');
        const preview=saveFilterForm.querySelector('[data-save-filter-preview]');
        if(classField)classField.value=currentClass;if(statusField)statusField.value=currentStatus;if(priorityField)priorityField.value=currentPriority;if(taskStatusField)taskStatusField.value=currentTaskStatus;if(taskDueField)taskDueField.value=currentTaskDue;
        if(preview){
          const classLabel=classJump?.selectedOptions?.[0]?.textContent?.trim()||currentClass;
          const statusLabels={all:'Všechny stavy',attention:'Prověřit',returned:'Vráceno',ok:'V pořádku',no_result:'Bez výsledku'};
          const priorityLabels={all:'Všechny priority',high:'Vysoká',medium:'Střední',normal:'Běžná'};
          const taskStatusLabels={all:'Všechny úkoly',active:'Aktivní úkol',done:'Dokončený úkol',no_task:'Bez úkolu'};
          const taskDueLabels={all:'Všechny termíny',overdue:'Po termínu',today:'Termín dnes',next3:'Do 3 dnů',next7:'Do 7 dnů',no_due:'Bez termínu'};
          preview.textContent=`${classLabel} · ${statusLabels[currentStatus]||currentStatus} · ${priorityLabels[currentPriority]||currentPriority} · ${taskStatusLabels[currentTaskStatus]||currentTaskStatus} · ${taskDueLabels[currentTaskDue]||currentTaskDue}`;
        }
      }
    };
    const syncUrl=()=>{
      const url=new URL(location.href);
      const set=(key,value,defaultValue='all')=>{if(value&&value!==defaultValue)url.searchParams.set(key,value);else url.searchParams.delete(key);};
      set('status',status?.value||'all');set('priority',priority?.value||'all');set('task_status',taskStatus?.value||'all');set('task_due',taskDue?.value||'all');set('sort',sort?.value||'priority','priority');
      const q=(search?.value||'').trim();if(q)url.searchParams.set('q',q);else url.searchParams.delete('q');
      history.replaceState(null,'',url.pathname+'?'+url.searchParams.toString());
    };
    const update=()=>{
      const query=text(search?.value).trim();
      const mode=sort?.value||'priority';
      const statusMode=status?.value||'all';
      const priorityMode=priority?.value||'all';
      const taskStatusMode=taskStatus?.value||'all';
      const taskDueMode=taskDue?.value||'all';
      rows.sort((a,b)=>compare(a,b,mode)).forEach(row=>tbody.appendChild(row));
      let visible=0;
      rows.forEach(row=>{
        const searchText=text(row.dataset.search||row.dataset.name);
        const matchesQuery=!query||searchText.includes(query);
        const matchesStatus=statusMode==='all'||(statusMode==='attention'?row.dataset.attention==='1':row.dataset.status===statusMode);
        const matchesPriority=priorityMode==='all'||row.dataset.priority===priorityMode;
        const activeTasks=num(row,'taskActive',0),doneTasks=num(row,'taskDone',0),totalTasks=num(row,'taskTotal',0);
        const matchesTaskStatus=taskStatusMode==='all'||(taskStatusMode==='active'&&activeTasks>0)||(taskStatusMode==='done'&&doneTasks>0)||(taskStatusMode==='no_task'&&totalTasks===0);
        const matchesTaskDue=taskDueMode==='all'||(taskDueMode==='overdue'&&num(row,'taskOverdue',0)>0)||(taskDueMode==='today'&&num(row,'taskToday',0)>0)||(taskDueMode==='next3'&&num(row,'taskNext3',0)>0)||(taskDueMode==='next7'&&num(row,'taskNext7',0)>0)||(taskDueMode==='no_due'&&num(row,'taskNoDue',0)>0);
        const show=matchesQuery&&matchesStatus&&matchesPriority&&matchesTaskStatus&&matchesTaskDue;
        row.hidden=!show;
        if(show)visible++;else{const check=row.querySelector('[data-row-select]');if(check)check.checked=false;}
      });
      if(empty)empty.hidden=visible!==0;
      if(visibleCount)visibleCount.textContent=String(visible);
      syncSelection();syncSavedFilters();syncUrl();
    };
    const params=new URLSearchParams(location.search);
    const setFromParam=(control,key,allowed)=>{const value=params.get(key);if(control&&value&&allowed.includes(value))control.value=value;};
    setFromParam(status,'status',['all','attention','returned','ok','no_result']);
    setFromParam(priority,'priority',['all','high','medium','normal']);
    setFromParam(taskStatus,'task_status',['all','active','done','no_task']);
    setFromParam(taskDue,'task_due',['all','overdue','today','next3','next7','no_due']);
    setFromParam(sort,'sort',['priority','attention','name','project_desc','grade_asc','mastery_desc','activity_desc']);
    if(search&&params.get('q'))search.value=params.get('q')||'';

    classJump?.addEventListener('change',()=>{
      const params=new URLSearchParams({tab:'class_results',class:classJump.value});
      if((status?.value||'all')!=='all')params.set('status',status.value);
      if((priority?.value||'all')!=='all')params.set('priority',priority.value);
      if((taskStatus?.value||'all')!=='all')params.set('task_status',taskStatus.value);
      if((taskDue?.value||'all')!=='all')params.set('task_due',taskDue.value);
      location.href='?'+params.toString();
    });
    search?.addEventListener('input',update,{passive:true});
    sort?.addEventListener('change',update,{passive:true});
    status?.addEventListener('change',update,{passive:true});
    priority?.addEventListener('change',update,{passive:true});
    taskStatus?.addEventListener('change',update,{passive:true});
    taskDue?.addEventListener('change',update,{passive:true});
    clearFilters?.addEventListener('click',()=>{if(search)search.value='';if(status)status.value='all';if(priority)priority.value='all';if(taskStatus)taskStatus.value='all';if(taskDue)taskDue.value='all';if(sort)sort.value='priority';update();});
    checks.forEach(check=>check.addEventListener('change',syncSelection,{passive:true}));
    selectVisible?.addEventListener('change',()=>{visibleRows().forEach(row=>{const check=row.querySelector('[data-row-select]');if(check)check.checked=selectVisible.checked;});syncSelection();});
    clearSelection?.addEventListener('click',()=>{checks.forEach(check=>check.checked=false);syncSelection();});
    form?.addEventListener('submit',event=>{
      const submitter=event.submitter;
      const action=submitter?.value||'';
      if(['teacher_bulk_mark','teacher_bulk_export','teacher_bulk_assign_task'].includes(action)&&selectedCount()===0){event.preventDefault();return;}
      if(action==='teacher_bulk_assign_task'){
        const title=form.querySelector('[name="task_title"]');
        if(!title||!title.value.trim()){event.preventDefault();title?.focus();title?.setCustomValidity('Doplňte název úkolu.');title?.reportValidity();setTimeout(()=>title?.setCustomValidity(''),0);return;}
        if(!confirm(`Přiřadit úkol ${selectedCount()} vybraným studentům?`))event.preventDefault();
      }
    });
    update();
  }

})();
