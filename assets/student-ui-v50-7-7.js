/* EDUCANET v50.7.3 · consolidated student UI runtime */
var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
/* ---- source: student-ux-v50-2.js ---- */
(() => {
  const dropdowns = [...document.querySelectorAll('[data-nav-dropdown]')];
  dropdowns.forEach((details) => {
    details.addEventListener('toggle', () => {
      if (!details.open) return;
      dropdowns.forEach((other) => { if (other !== details) other.open = false; });
    });
  });
  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-nav-dropdown]')) return;
    dropdowns.forEach((details) => { details.open = false; });
  });
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    dropdowns.forEach((details) => { details.open = false; });
  });

  const dashboardMore = document.querySelector('[data-dashboard-more]');
  if (dashboardMore) {
    dashboardMore.addEventListener('toggle', () => {
      dashboardMore.querySelectorAll('[data-lazy-dashboard]').forEach((node) => {
        if (dashboardMore.open) node.removeAttribute('hidden');
      });
    });
  }
})();

/* ---- source: zero-friction-v50-6.js ---- */
(()=>{
  'use strict';
  const menu=document.querySelector('[data-main-menu]');
  if(menu){
    menu.addEventListener('click',event=>{
      const link=event.target.closest('a');
      if(!link)return;
      menu.classList.remove('open');
      const toggle=document.querySelector('[data-menu-toggle]');
      if(toggle)toggle.setAttribute('aria-expanded','false');
    });
  }
  document.querySelectorAll('details.v506-progressive').forEach(details=>{
    details.addEventListener('toggle',()=>{
      if(details.open) details.setAttribute('data-opened','1');
      else details.removeAttribute('data-opened');
    });
  });
})();

/* ---- source: unified-page-shell-v50-7.js ---- */
(()=>{
  'use strict';
  const shell=document.querySelector('[data-v507-page-shell]');
  if(!shell)return;
  const body=document.body;
  body.classList.add('v507-shell-active');
  const view=shell.dataset.view||'';
  const primary=shell.querySelector('[data-v507-primary]');

  const primaryWork=(selectors)=>{
    for(const sel of selectors){const el=document.querySelector(sel);if(el){el.id='v507-primary-work';el.classList.add('v507-current-work');return el;}}
    return null;
  };

  const rules={
    course_lesson:{primary:['#guided-current-step','.guided-lesson-focus']},
    next_lesson:{primary:['#guided-current-step','.guided-lesson-focus']},
    skill_detail:{primary:[]},
    kb_lesson:{primary:['.kb-lesson-main','.kb-lesson-screen']},
    review:{primary:['.adaptive-review-form','.adaptive-empty']},
    project_result:{primary:[]},
    project_workspace:{primary:['.pw-role-onboarding','.pw-next-action','.pw-empty-hero','.pw-work-board','.pw-team-section','.pw-qa-layout','.pw-reflection-layout','.pw-overview-grid']},
    project_lobbies:{primary:['.team-current.formed','.team-current','.team-create-grid']},
    community:{primary:['.friend-requests','.classmate-grid']},
    profile:{primary:['.profile-editor','.social-about']},
  };
  const rule=rules[view];
  if(rule){primaryWork(rule.primary||[]);}

  if(view==='recovery'){
    const steps=[...document.querySelectorAll('.recovery-step')];
    const current=steps.find(x=>!x.classList.contains('done'))||steps[0]||null;
    if(current){current.id='v507-primary-work';current.classList.add('v507-current-work');}
  }

  // Team pages can provide a more precise primary action after their dynamic state is rendered.
  if(view==='project_lobbies'){
    const direct=document.querySelector('.team-current.formed a.btn.primary,.team-current form .btn.primary');
    if(direct&&primary&&primary.getAttribute('href')==='#v507-primary-work'){
      if(direct.tagName==='A')primary.setAttribute('href',direct.getAttribute('href')||'#v507-primary-work');
      primary.textContent=(direct.textContent||EduI18n.tr('Pokračovat s týmem')).trim()+' →';
    }
  }
  if(view==='project_workspace'){
    const direct=document.querySelector('#v507-primary-work a.btn.primary,#v507-primary-work button.btn.primary');
    if(direct&&primary&&direct.tagName==='A'){
      primary.setAttribute('href',direct.getAttribute('href')||'#v507-primary-work');
      primary.textContent=(direct.textContent||EduI18n.tr('Pokračovat na úkolu')).trim().replace(/→\s*$/,'')+' →';
    }
  }

  // Anchor CTA is intentionally a scroll action, not another navigation branch.
  if(primary&&primary.getAttribute('href')==='#v507-primary-work'){
    primary.addEventListener('click',event=>{
      const target=document.getElementById('v507-primary-work');
      if(!target)return;
      event.preventDefault();
      target.scrollIntoView({behavior:'smooth',block:'start'});
      const focusable=target.querySelector('button,a,input,textarea,select');if(focusable)window.setTimeout(()=>focusable.focus({preventScroll:true}),350);
    });
  }
})();



/* v50.7.7 Calm Navigation */
document.addEventListener('click',function(event){
  document.querySelectorAll('details[data-nav-dropdown][open]').forEach(function(node){
    if(!node.contains(event.target)) node.removeAttribute('open');
  });
});
document.addEventListener('keydown',function(event){
  if(event.key==='Escape') document.querySelectorAll('details[data-nav-dropdown][open]').forEach(function(node){node.removeAttribute('open');});
});
