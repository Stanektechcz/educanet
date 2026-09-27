
var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

// Google Workspace sign-in --------------------------------------------------
window.handleGoogleCredential = function(response) {
  const credential = response && response.credential ? response.credential : '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  if (!credential || !csrf) return;
  const form = document.createElement('form');
  form.method = 'post';
  form.action = location.pathname + location.search;
  [['csrf', csrf], ['action', 'google_login'], ['credential', credential]].forEach(([name,value]) => {
    const input = document.createElement('input'); input.type='hidden'; input.name=name; input.value=value; form.appendChild(input);
  });
  document.body.appendChild(form); form.submit();
};

(() => {
  const form = document.querySelector('[data-account-link-form]');
  if (!form) return;
  const classSelect = form.querySelector('[data-link-class]');
  const studentSelect = form.querySelector('[data-link-student]');
  const studentWrap = form.querySelector('[data-link-student-wrap]');
  const refresh = () => {
    const cid = classSelect?.value || '';
    if (!studentSelect) return;
    let visible = 0;
    Array.from(studentSelect.options).forEach((opt, idx) => {
      if (idx === 0) return;
      const show = opt.dataset.class === cid;
      opt.hidden = !show; opt.disabled = !show;
      if (show) visible++;
    });
    studentSelect.value = '';
    if (studentWrap) studentWrap.classList.toggle('optional-student', cid === 'class_1a');
    studentSelect.required = cid !== 'class_1a';
    studentSelect.options[0].textContent = cid === 'class_1a' ? EduI18n.tr('1.A používá jméno z Google účtu') : (visible ? EduI18n.tr('Vyber svoje jméno…') : EduI18n.tr('Pro tuto třídu zatím nejsou studenti v diagnostice'));
  };
  classSelect?.addEventListener('change', refresh); refresh();
})();

(() => {
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) {
    fileInput.addEventListener('change', () => {
      const file = fileInput.files && fileInput.files[0];
      if (file && file.size > 12 * 1024 * 1024) {
        alert(EduI18n.tr('Soubor je větší než 12 MB. Vyber menší export.'));
        fileInput.value = '';
      }
    });
  }

  document.querySelectorAll('.terminal-run').forEach((button) => {
    button.addEventListener('click', () => {
      const card = button.closest('.terminal-card');
      if (!card) return;
      const output = card.querySelector('.terminal-output');
      const meaning = card.querySelector('.terminal-meaning');
      const expanded = button.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        if (output) output.hidden = true;
        if (meaning) meaning.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        button.textContent = EduI18n.tr('Spustit příkaz');
        card.classList.remove('ran');
      } else {
        if (output) output.hidden = false;
        if (meaning) meaning.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        button.textContent = EduI18n.tr('Skrýt výstup');
        card.classList.add('ran');
      }
    });
  });

  const kbTour = document.querySelector('[data-kb-tour]');
  const kbOverall = document.querySelector('[data-kb-overall]');

  const storageKeyFor = (topic) => {
    const classId = kbOverall ? (kbOverall.getAttribute('data-class') || 'class') : 'class';
    return `educanet_kb_${classId}_${topic}`;
  };

  const updateKbProgress = () => {
    if (!kbOverall) return;
    const cards = Array.from(document.querySelectorAll('[data-kb-progress-card]'));
    const done = cards.filter((card) => card.classList.contains('completed')).length;
    const total = cards.length || Number(kbOverall.getAttribute('data-total')) || 1;
    const doneEl = kbOverall.querySelector('[data-kb-done]');
    const bar = kbOverall.querySelector('[data-kb-progress-bar]');
    if (doneEl) doneEl.textContent = String(done);
    if (bar) bar.style.width = `${Math.round((done / total) * 100)}%`;
  };

  const activateKbTab = (tour, name) => {
    tour.querySelectorAll('[data-kb-tab]').forEach((btn) => {
      btn.classList.toggle('active', btn.getAttribute('data-kb-tab') === name);
    });
    tour.querySelectorAll('[data-kb-panel]').forEach((panel) => {
      const active = panel.getAttribute('data-kb-panel') === name;
      panel.hidden = !active;
      panel.classList.toggle('active', active);
    });
  };

  document.querySelectorAll('[data-kb-tour]').forEach((tour) => {
    tour.querySelectorAll('[data-kb-tab]').forEach((button) => {
      button.addEventListener('click', () => activateKbTab(tour, button.getAttribute('data-kb-tab') || 'visual'));
    });

    const start = tour.querySelector('.kb-tour-start');
    if (start) {
      start.addEventListener('click', () => {
        activateKbTab(tour, 'visual');
        const model = tour.querySelector('.kb-mental-model');
        if (model) model.scrollIntoView({behavior: 'smooth', block: 'center'});
        const stage = tour.querySelector('[data-kb-demo-stage]');
        if (stage) {
          stage.classList.remove('demo-ready');
          const parts = Array.from(stage.querySelectorAll('[data-demo-step]'));
          parts.forEach((part) => part.classList.remove('revealed'));
          parts.forEach((part, i) => setTimeout(() => part.classList.add('revealed'), 220 + i * 330));
        }
      });
    }

    tour.querySelectorAll('[data-kb-stepper]').forEach((stepper) => {
      const slides = Array.from(stepper.querySelectorAll('[data-kb-step]'));
      const dots = Array.from(stepper.querySelectorAll('[data-kb-step-dot]'));
      const prev = stepper.querySelector('.kb-step-prev');
      const next = stepper.querySelector('.kb-step-next');
      const current = stepper.querySelector('[data-kb-step-current]');
      let index = 0;
      const render = () => {
        slides.forEach((slide, i) => {
          slide.hidden = i !== index;
          slide.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
          dot.classList.toggle('active', i === index);
          dot.classList.toggle('done', i < index);
        });
        if (current) current.textContent = String(index + 1);
        if (prev) prev.disabled = index === 0;
        if (next) {
          next.textContent = index >= slides.length - 1 ? EduI18n.tr('Hotovo ✓') : EduI18n.tr('Další krok →');
          next.disabled = slides.length === 0;
        }
      };
      dots.forEach((dot, i) => dot.addEventListener('click', () => { index = i; render(); }));
      if (prev) prev.addEventListener('click', () => { if (index > 0) { index -= 1; render(); } });
      if (next) next.addEventListener('click', () => {
        if (index < slides.length - 1) { index += 1; render(); }
        else {
          tour.dispatchEvent(new CustomEvent('kb:steps-complete', {bubbles: true}));
        }
      });
      render();
    });

    tour.querySelectorAll('[data-kb-check]').forEach((check) => {
      const correct = Number(check.getAttribute('data-correct'));
      const feedback = check.querySelector('[data-kb-check-feedback]');
      check.querySelectorAll('[data-check-option]').forEach((button) => {
        button.addEventListener('click', () => {
          const value = Number(button.getAttribute('data-check-option'));
          check.querySelectorAll('[data-check-option]').forEach((b) => b.classList.remove('correct', 'wrong'));
          const ok = value === correct;
          button.classList.add(ok ? 'correct' : 'wrong');
          if (feedback) {
            feedback.hidden = false;
            feedback.classList.toggle('ok', ok);
            feedback.classList.toggle('bad', !ok);
            const label = feedback.querySelector('strong');
            if (label) label.textContent = ok ? EduI18n.tr('Správně — tohle drží.') : EduI18n.tr('Ještě ne. Podívej se na vysvětlení a zkus to znovu.');
          }
          if (ok) tour.dispatchEvent(new CustomEvent('kb:check-correct', {bubbles: true, detail: {answer: value}}));
        });
      });
    });

    const complete = tour.querySelector('.kb-mark-complete');
    if (complete) {
      const topic = tour.getAttribute('data-topic') || '';
      const card = complete.closest('.kb-complete-card');
      const renderState = () => {
        const done = localStorage.getItem(storageKeyFor(topic)) === '1';
        if (card) card.classList.toggle('completed', done);
        complete.textContent = done ? EduI18n.tr('✓ Projito') : EduI18n.tr('Označit jako projité');
      };
      complete.addEventListener('click', () => {
        const done = localStorage.getItem(storageKeyFor(topic)) === '1';
        localStorage.setItem(storageKeyFor(topic), done ? '0' : '1');
        renderState();
      
  // Graphics Design Studio ---------------------------------------------------
  const studio = document.querySelector('[data-design-studio]');
  if (studio) {
    const q = (sel) => studio.querySelector(sel);
    const qa = (sel) => Array.from(studio.querySelectorAll(sel));
    const inputs = qa('[data-ds]');
    const canvasEl = q('[data-ds-canvas]');
    const gridEl = q('[data-ds-grid]');
    const thumb = q('[data-ds-thumb]');
    const feedback = q('[data-ds-feedback]');
    const score = q('[data-ds-score]');
    const contrastEl = q('[data-ds-contrast]');
    const contrastNote = q('[data-ds-contrast-note]');
    const exportAdvice = q('[data-ds-export-advice]');
    const storageKey = `educanet_graphics_studio_${studio.getAttribute('data-ds-class') || 'graphics'}_v3`;

    const readState = () => {
      const state = {};
      inputs.forEach((input) => {
        const key = input.getAttribute('data-ds');
        if (!key) return;
        state[key] = input.type === 'checkbox' ? input.checked : input.value;
      });
      return state;
    };
    const setState = (state) => {
      inputs.forEach((input) => {
        const key=input.getAttribute('data-ds'); if(!key || state[key] === undefined) return;
        if(input.type==='checkbox') input.checked=Boolean(state[key]); else input.value=String(state[key]);
      });
    };
    try { const saved=JSON.parse(localStorage.getItem(storageKey)||'null'); if(saved) setState(saved); } catch(_) {}

    const posterDimensions = (format) => ({portrait:[1080,1350],square:[1080,1080],story:[1080,1920],a4:[1240,1754]})[format] || [1080,1350];
    const colorContrast = (a,b) => contrastRatio(a,b);
    const downloadBlob = (blob,name) => { const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download=name; document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(url),500); };

    const renderStudio = () => {
      const s = readState();
      localStorage.setItem(storageKey, JSON.stringify(s));
      qa('[data-ds-out]').forEach((el)=>{ const key=el.getAttribute('data-ds-out'); if(key && s[key]!==undefined) el.textContent=String(s[key]); });
      const title=q('[data-ds-title]'), info=q('[data-ds-info]'), cta=q('[data-ds-cta]');
      if(title) title.textContent=s.title || 'HEADLINE'; if(info) info.textContent=s.info || 'Datum · místo'; if(cta) cta.textContent=(s.cta || 'CTA')+' →';
      const [w,h]=posterDimensions(s.format); const aspect=`${w} / ${h}`;
      if(canvasEl){
        canvasEl.style.setProperty('--ds-bg',s.bg); canvasEl.style.setProperty('--ds-text',s.text); canvasEl.style.setProperty('--ds-accent',s.accent);
        canvasEl.style.setProperty('--ds-title',`${s.titleSize}px`); canvasEl.style.setProperty('--ds-info',`${s.infoSize}px`); canvasEl.style.setProperty('--ds-space',`${s.spacing}px`);
        canvasEl.style.aspectRatio=aspect; canvasEl.style.filter=`${s.gray?'grayscale(1) ':''}${s.blur?'blur(4px)':''}`.trim() || 'none';
      }
      if(gridEl){gridEl.hidden=!s.showGrid; gridEl.style.setProperty('--ds-cols',s.grid||4);}
      if(thumb){thumb.style.background=s.bg;thumb.style.color=s.text; const b=thumb.querySelector('b'),i=thumb.querySelector('i'),e=thumb.querySelector('em'); if(b)b.textContent=s.title||'';if(i)i.textContent=s.info||'';if(e){e.textContent=s.cta||'';e.style.color=s.accent;}}
      const cr=colorContrast(s.bg,s.text), ar=colorContrast(s.bg,s.accent); const hierarchy=Number(s.titleSize)>=Number(s.infoSize)*2; const spacing=Number(s.spacing)>=16; const mainPass=cr>=4.5; const accentPass=ar>=3;
      if(contrastEl) contrastEl.textContent=`${cr.toFixed(2)} : 1`;
      if(contrastNote){contrastNote.textContent=mainPass?'dobrý základ pro čitelnost':'zvyš rozdíl text / pozadí';contrastNote.className=mainPass?'pass':'fail';}
      const checks=[mainPass,hierarchy,spacing,accentPass]; if(score) score.textContent=`${checks.filter(Boolean).length} / 4 kontroly`;
      const tips=[];
      tips.push(mainPass?'✓ Hlavní text má použitelný orientační kontrast.':'• Hlavní text splývá s pozadím — změň text nebo background.');
      tips.push(hierarchy?'✓ Headline je jasně dominantní.':'• Zvětši rozdíl mezi headline a informačním textem.');
      tips.push(spacing?'✓ Spacing dává prvkům vzduch.':'• Přidej spacing; podobné prvky mohou být blízko, ale celý layout potřebuje rytmus.');
      tips.push(accentPass?'✓ Akcent je proti pozadí dostatečně rozpoznatelný.':'• CTA akcent splývá s pozadím — zkus světlejší/tmavší akcent.');
      if(feedback) feedback.innerHTML=tips.map((x)=>`<li class="${x.startsWith('✓')?'ok':'warn'}">${escapeHtml(x)}</li>`).join('');
      if(exportAdvice){
        const target=s.target, ft=s.filetype; let text='';
        if(target==='social') text=`Doporučení: RGB, přesný pixelový rozměr, ${ft==='pdf'?'pro sociální síť raději PNG/JPG/WebP než PDF':ft.toUpperCase()}, kontrola na mobilním náhledu.`;
        else if(target==='web') text=`Doporučení: RGB, optimalizovat datovou velikost; ${['webp','jpg'].includes(ft)?'zvolený formát dává pro web smysl':'zvaž WebP/JPG podle typu obrazu'}.`;
        else text=`Doporučení: zjisti specifikaci tiskárny, spadávku a požadovaný PDF profil. Design Studio negeneruje produkční tiskové PDF — preset je checklist pro Canvu.`;
        exportAdvice.innerHTML=`<span>${escapeHtml(target.toUpperCase())}</span><p>${escapeHtml(text)}</p><small>Kvalita: ${escapeHtml(s.quality)} % · zvolený soubor: ${escapeHtml(String(ft).toUpperCase())}</small>`;
      }
    };

    inputs.forEach((input)=>input.addEventListener('input',renderStudio));
    q('[data-ds-download-config]')?.addEventListener('click',()=>{
      const s=readState(); const [w,h]=posterDimensions(s.format);
      const payload={version:2,created_at:new Date().toISOString(),canvas:{preset:s.format,width:w,height:h,grid:Number(s.grid)},content:{headline:s.title,info:s.info,cta:s.cta},visual:{background:s.bg,text:s.text,accent:s.accent,title_size:Number(s.titleSize),info_size:Number(s.infoSize),spacing:Number(s.spacing),contrast:Number(colorContrast(s.bg,s.text).toFixed(2))},export:{target:s.target,filetype:s.filetype,quality:Number(s.quality)},canva_checklist:['Vytvoř dokument podle rozměru','Nastav vodítka/grid','Přenes poměr headline/info/CTA','Ověř kontrast a thumbnail','Doplň vlastní vizuální styl','Export otevři mimo editor']};
      downloadBlob(new Blob([JSON.stringify(payload,null,2)],{type:'application/json;charset=utf-8'}),'educanet-design-config.json');
    });
    q('[data-ds-copy]')?.addEventListener('click',async()=>{
      const s=readState(); const [w,h]=posterDimensions(s.format);
      const text=`CANVA PŘENOS\n• Dokument: ${w} × ${h}\n• Grid: ${s.grid} sloupců\n• Headline: ${s.title} · relativní velikost ${s.titleSize}\n• Info: ${s.info} · ${s.infoSize}\n• CTA: ${s.cta}\n• Barvy: BG ${s.bg} · text ${s.text} · akcent ${s.accent}\n• Spacing: ${s.spacing}\n• Export: ${String(s.target).toUpperCase()} / ${String(s.filetype).toUpperCase()} / kvalita ${s.quality}%\n• Kontrola: thumbnail, grayscale, kontrast, export mimo editor`;
      try { await navigator.clipboard.writeText(text); const b=q('[data-ds-copy]'); if(b){const old=b.textContent;b.textContent=EduI18n.tr('Zkopírováno ✓');setTimeout(()=>b.textContent=old,1300);} } catch(_) { window.prompt(EduI18n.tr('Zkopíruj checklist:'),text); }
    });
    q('[data-ds-download-png]')?.addEventListener('click',()=>{
      const s=readState(); const [w,h]=posterDimensions(s.format); const scale=Math.min(1,1600/Math.max(w,h)); const cw=Math.round(w*scale), ch=Math.round(h*scale);
      const c=document.createElement('canvas'); c.width=cw;c.height=ch; const ctx=c.getContext('2d'); if(!ctx)return;
      ctx.fillStyle=s.bg;ctx.fillRect(0,0,cw,ch); const pad=Math.round(cw*.08); ctx.fillStyle=s.text;ctx.font=`700 ${Math.max(34,Math.round(cw*.09))}px Arial, sans-serif`;ctx.textBaseline='top';
      const words=String(s.title||'DESIGN NIGHT').toUpperCase().split(' '); const half=Math.ceil(words.length/2); ctx.fillText(words.slice(0,half).join(' '),pad,Math.round(ch*.18));ctx.fillText(words.slice(half).join(' '),pad,Math.round(ch*.18)+Math.round(cw*.105));
      ctx.font=`500 ${Math.max(18,Math.round(cw*.035))}px Arial, sans-serif`;ctx.fillText(String(s.info||''),pad,Math.round(ch*.48));
      ctx.fillStyle=s.accent;ctx.fillRect(pad,Math.round(ch*.63),Math.round(cw*.42),Math.round(ch*.075));ctx.fillStyle='#000';ctx.font=`700 ${Math.max(16,Math.round(cw*.028))}px Arial, sans-serif`;ctx.fillText(String(s.cta||'CTA').toUpperCase(),pad+Math.round(cw*.025),Math.round(ch*.648));
      ctx.fillStyle=s.text;ctx.globalAlpha=.55;ctx.font=`500 ${Math.max(12,Math.round(cw*.018))}px Arial, sans-serif`;ctx.fillText('STUDY PREVIEW · převeď do Canvy',pad,Math.round(ch*.88));ctx.globalAlpha=1;
      c.toBlob((blob)=>{if(blob)downloadBlob(blob,'educanet-poster-preview.png');},'image/png');
    });
    renderStudio();
  }

  updateKbProgress();
      });
      renderState();
    }
  });

  // Interactive Knowledge Tour simulations ---------------------------------
  const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

  const setSimPhase = (sim, name) => {
    const order = ['predict', 'experiment', 'evidence', 'conclude'];
    const activeIndex = Math.max(0, order.indexOf(name));
    sim.querySelectorAll('[data-sim-phase]').forEach((el) => {
      const idx = order.indexOf(el.getAttribute('data-sim-phase'));
      el.classList.toggle('active', idx === activeIndex);
      el.classList.toggle('done', idx >= 0 && idx < activeIndex);
    });
  };

  const simSelect = (name, label, options, value) => `
    <label class="sim-control"><span>${escapeHtml(label)}</span>
      <select data-sim-input="${escapeHtml(name)}">
        ${options.map(([v,l]) => `<option value="${escapeHtml(v)}"${String(v)===String(value)?' selected':''}>${escapeHtml(l)}</option>`).join('')}
      </select>
    </label>`;

  const simRange = (name, label, min, max, step, value, suffix='') => `
    <label class="sim-control range"><span>${escapeHtml(label)} <b data-sim-value="${escapeHtml(name)}">${escapeHtml(value)}${escapeHtml(suffix)}</b></span>
      <input type="range" min="${min}" max="${max}" step="${step}" value="${value}" data-sim-input="${escapeHtml(name)}" data-suffix="${escapeHtml(suffix)}">
    </label>`;

  const simToggle = (name, label, checked) => `
    <label class="sim-toggle"><input type="checkbox" data-sim-input="${escapeHtml(name)}"${checked?' checked':''}><span></span><b>${escapeHtml(label)}</b></label>`;

  const hexToRgb = (hex) => {
    const clean = String(hex || '').replace('#','').trim();
    if (!/^[0-9a-fA-F]{6}$/.test(clean)) return [0,0,0];
    return [0,2,4].map((i)=>parseInt(clean.slice(i,i+2),16));
  };
  const relativeLuminance = (hex) => {
    const [r,g,b] = hexToRgb(hex).map((v)=>v/255).map((v)=>v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4));
    return 0.2126*r + 0.7152*g + 0.0722*b;
  };
  const contrastRatio = (a,b) => {
    const l1=relativeLuminance(a), l2=relativeLuminance(b);
    return (Math.max(l1,l2)+0.05)/(Math.min(l1,l2)+0.05);
  };

  const simEvidence = (sim, known, unknown) => {
    const k = sim.querySelector('[data-sim-known]');
    const u = sim.querySelector('[data-sim-unknown]');
    if (k) {
      if (known.length) { k.innerHTML = known.map((x) => `<p><span>✓</span>${escapeHtml(x)}</p>`).join(''); }
      else { k.innerHTML = '<p class="empty"></p>'; const p = k.querySelector('p'); if (p) p.textContent = EduI18n.tr('Zatím nemáš dost důkazů.'); }
    }
    if (u) {
      if (unknown.length) { u.innerHTML = unknown.map((x) => `<p><span>?</span>${escapeHtml(x)}</p>`).join(''); }
      else { u.innerHTML = '<p class="all-known"><span>✓</span></p>'; const p = u.querySelector('p'); if (p) p.append(EduI18n.tr('Máš dost evidence pro závěr.')); }
    }
  };

  const initSimulation = (sim) => {
    const configNode = sim.querySelector('[data-sim-config]');
    if (!configNode) return;
    let cfg;
    try { cfg = JSON.parse(configNode.textContent || '{}'); } catch (_) { return; }
    const type = cfg.type || '';
    const controls = sim.querySelector('[data-sim-controls]');
    const stage = sim.querySelector('[data-sim-stage]');
    if (stage && window.EduI18n && window.EduI18n.locale && window.EduI18n.locale !== 'cs') stage.setAttribute('lang', 'cs');
    const terminal = sim.querySelector('[data-sim-terminal]');
    const status = sim.querySelector('[data-sim-status]');
    const scoreEl = sim.querySelector('[data-sim-score] strong');
    const maxPoints = Number(cfg.points || 5);
    const savedKey = `educanet_sim_score_${cfg.id || type}`;
    let bestScore = Number(localStorage.getItem(savedKey) || 0);
    let state = { values: {}, commands: new Set(), validation: new Set(), predictionCorrect: null, conclusionCorrect: null, hints: 0, verified: false };

    const defaults = () => {
      const map = {
        hierarchy: {title:34, info:28, cta:16, spacing:8},
        grid: {columns:'2', margin:8, gutter:4, align:false},
        export: {format:'png', width:'4000', quality:100},
        contrast: {bg:'#2a1a55', text:'#7b5ab9', size:18, gray:false},
        typography: {fonts:'4', headline:30, body:18, line:1.0},
        rastervector: {source:'png', zoom:100},
        colormode: {saturation:100, mode:'screen'},
        subnetlab: {prefix:'25'},
        dnscache: {ttl:300, elapsed:60},
        httpsstack: {dns:true, tcp:true, tls:true, http:true},
        routinglab: {gateway:'192.168.20.1'},
        cidrplan: {prefix:'27'},
        lpm: {route:'16'},
        dnsmigrate: {prettl:'3600'},
        sshkeylab: {perm:'0644', user:'wrong', key:'wrong'},
        permissionslab: {mode:'777'},
        dhcp: {server:'off', vlan:'wrong', pool:'full'},
        reachability: {icmp:true, service:true, firewall443:false},
        binding: {bind:'127.0.0.1', firewall:'deny'},
        tcp: {listening:true, firewall:'drop'},
        palette: {bg:'#111827', text:'#f9fafb', accent:'#22c55e', accents:'1'},
        iconset: {style:'outline', stroke:'2', size:'24'},
        cropfocus: {focus:'right', textpos:'left', contrast:'high'},
        critique: {hierarchy:false, contrast:false, spacing:false},
        spacingtokens: {base:'8', tokens:'5'},
        components: {radius:'12', padding:'16', states:true},
        motioncurve: {duration:'240', easing:'out'},
        portfolioflow: {order:'correct'},
        ipv6subnet: {prefix:'64', same:true},
        dnsrecords: {alias:'cname', a:true, aaaa:true},
        monitoring: {check:'ping', app:'503'},
        dhcpreserve: {mac:'wrong', ip:'55', pool:'collision'},
        httpprobe: {status:'503', retry:true, latency:'820'},
        loadbalance: {web2:'unhealthy', remove:false, policy:'rr'},
        canary: {traffic:'10', err:'4', threshold:'2'},
        backuprestore: {verified:false, target:'prod', point:'old'},
        responsivepreview: {target:'post', safe:'6', hierarchy:false},
        accessibilityaudit: {bg:'#f8fafc', text:'#94a3b8', size:'14', focus:false},
        packetflow: {fault:'arp', filter:'all'},
        slobudget: {slo:'99.9', actual:'99.5', action:'release'},
      };
      return Object.assign({}, map[type] || {});
    };

    const updateScore = () => {
      if (scoreEl) scoreEl.textContent = `${bestScore} / ${maxPoints}`;
    };

    const controlsMarkup = () => {
      const v = state.values;
      if (type === 'hierarchy') return [
        simRange('title','Titulek',20,84,2,v.title,' px'),
        simRange('info','Datum + místo',12,40,2,v.info,' px'),
        simRange('cta','CTA',12,44,2,v.cta,' px'),
        simRange('spacing','Vertikální spacing',4,48,2,v.spacing,' px'),
      ].join('');
      if (type === 'grid') return [
        simSelect('columns','Počet sloupců', [['2','2 sloupce'],['4','4 sloupce'],['6','6 sloupců'],['12','12 sloupců']], v.columns),
        simRange('margin','Vnější okraj',8,48,4,v.margin,' px'),
        simRange('gutter','Mezera mezi sloupci',4,32,4,v.gutter,' px'),
        simToggle('align','Přichytit prvky ke gridu',v.align),
      ].join('');
      if (type === 'export') return [
        simSelect('format','Formát',[['png','PNG'],['jpg','JPG'],['webp','WebP']],v.format),
        simSelect('width','Šířka',[['1080','1080 px'],['1920','1920 px'],['2560','2560 px'],['4000','4000 px']],v.width),
        simRange('quality','Kvalita',40,100,5,v.quality,' %'),
      ].join('') + '<div class="sim-fixed-context"><span>Cílové médium</span><strong>Web hero · desktop</strong></div>';
      if (type === 'contrast') return [
        `<label class="sim-control color"><span>Pozadí</span><input type="color" value="${escapeHtml(v.bg)}" data-sim-input="bg"></label>`,
        `<label class="sim-control color"><span>Text</span><input type="color" value="${escapeHtml(v.text)}" data-sim-input="text"></label>`,
        simRange('size','Velikost textu',12,54,2,v.size,' px'),
        simToggle('gray','Grayscale test',v.gray),
      ].join('');
      if (type === 'typography') return [
        simSelect('fonts','Počet rodin písem',[['1','1 rodina'],['2','2 rodiny'],['3','3 rodiny'],['4','4 rodiny']],v.fonts),
        simRange('headline','Headline',24,80,2,v.headline,' px'),
        simRange('body','Body text',12,28,1,v.body,' px'),
        simRange('line','Line-height ×10',10,18,1,Math.round(Number(v.line)*10),''),
      ].join('');
      if (type === 'rastervector') return [
        simSelect('source','Zdroj',[['png','PNG · raster'],['svg','SVG · vektor']],v.source),
        simRange('zoom','Zvětšení',100,1200,100,v.zoom,' %'),
      ].join('');
      if (type === 'colormode') return [
        simRange('saturation','Sytost akcentu',20,100,5,v.saturation,' %'),
        simSelect('mode','Náhled',[['screen','Obrazovka · RGB'],['print','Simulovaný tisk']],v.mode),
      ].join('');
      if (type === 'subnetlab') return [simSelect('prefix','Prefix zdroje',[['25','/25'],['24','/24']],v.prefix)].join('');
      if (type === 'dnscache') return [simRange('ttl','TTL',30,600,30,v.ttl,' s'),simRange('elapsed','Čas od změny',0,600,30,v.elapsed,' s')].join('');
      if (type === 'httpsstack') return [simToggle('dns','DNS funguje',v.dns),simToggle('tcp','TCP/443 funguje',v.tcp),simToggle('tls','TLS handshake funguje',v.tls),simToggle('http','HTTP aplikace odpovídá',v.http)].join('');
      if (type === 'routinglab') return [simSelect('gateway','Default gateway',[['192.168.20.1','192.168.20.1 · jiný subnet'],['192.168.10.1','192.168.10.1 · lokální']],v.gateway)].join('');
      if (type === 'cidrplan') return [simSelect('prefix','Prefix',[['27','/27 · 30 hostů'],['26','/26 · 62 hostů'],['25','/25 · 126 hostů'],['24','/24 · 254 hostů']],v.prefix)].join('');
      if (type === 'lpm') return [simSelect('route','Vybraná route',[['8','10.0.0.0/8'],['16','10.20.0.0/16'],['24','10.20.30.0/24'],['0','default']],v.route)].join('');
      if (type === 'dnsmigrate') return [simSelect('prettl','TTL před migrací',[['3600','3600 s'],['300','300 s'],['60','60 s']],v.prettl)].join('');
      if (type === 'sshkeylab') return [simSelect('perm','Private key permissions',[['0644','0644'],['0600','0600']],v.perm),simSelect('user','Username',[['wrong','admin'],['correct','deploy']],v.user),simSelect('key','Public key na serveru',[['wrong','jiný klíč'],['match','odpovídá']],v.key)].join('');
      if (type === 'permissionslab') return [simSelect('mode','Souborový mód',[['777','777'],['644','644'],['640','640'],['600','600']],v.mode)].join('');
      if (type === 'dhcp') return [
        simSelect('server','DHCP server',[['off','OFF'],['on','ON']],v.server),
        simSelect('vlan','VLAN klienta',[['wrong','špatná VLAN'],['correct','správná VLAN']],v.vlan),
        simSelect('pool','Adresní pool',[['full','vyčerpaný'],['free','volné adresy']],v.pool),
      ].join('');
      if (type === 'reachability') return [
        simToggle('icmp','ICMP / ping povolen',v.icmp),
        simToggle('service','HTTPS služba běží',v.service),
        simToggle('firewall443','TCP/443 povolen ve firewallu',v.firewall443),
      ].join('');
      if (type === 'binding') return [
        simSelect('bind','Bind adresa',[['127.0.0.1','127.0.0.1 · loopback'],['0.0.0.0','0.0.0.0 · všechny interface'],['10.20.0.30','10.20.0.30 · LAN interface']],v.bind),
        simSelect('firewall','Firewall TCP/8000',[['deny','blokovat z LAN'],['allow','povolit z LAN']],v.firewall),
      ].join('');
      if (type === 'tcp') return [
        simToggle('listening','Služba naslouchá na portu',v.listening),
        simSelect('firewall','Firewall',[['drop','DROP · zahodit'],['reject','REJECT · aktivně odmítnout'],['allow','ALLOW · propustit']],v.firewall),
      ].join('');
      if (type === 'palette') return [
        `<label class="sim-control color"><span>Pozadí</span><input type="color" value="${escapeHtml(v.bg)}" data-sim-input="bg"></label>`,
        `<label class="sim-control color"><span>Text</span><input type="color" value="${escapeHtml(v.text)}" data-sim-input="text"></label>`,
        `<label class="sim-control color"><span>Akcent</span><input type="color" value="${escapeHtml(v.accent)}" data-sim-input="accent"></label>`,
        simSelect('accents','Počet akcentních barev',[['1','1 akcent'],['2','2 akcenty'],['4','4 akcenty']],v.accents),
      ].join('');
      if (type === 'iconset') return [simSelect('style','Styl',[['outline','Outline'],['fill','Fill'],['mixed','Mixed']],v.style),simRange('stroke','Tloušťka',1,5,1,v.stroke,' px'),simRange('size','Optická velikost',16,40,2,v.size,' px')].join('');
      if (type === 'cropfocus') return [simSelect('focus','Focal point',[['left','vlevo'],['center','uprostřed'],['right','vpravo']],v.focus),simSelect('textpos','Textový blok',[['left','vlevo'],['center','uprostřed'],['right','vpravo']],v.textpos),simSelect('contrast','Overlay / kontrast',[['low','nízký'],['high','vysoký']],v.contrast)].join('');
      if (type === 'critique') return [simToggle('hierarchy','Opravit hierarchii',v.hierarchy),simToggle('contrast','Opravit kontrast',v.contrast),simToggle('spacing','Opravit spacing',v.spacing)].join('');
      if (type === 'spacingtokens') return [simSelect('base','Základní krok',[['4','4 px'],['8','8 px'],['10','10 px']],v.base),simSelect('tokens','Počet tokenů',[['3','3 hodnoty'],['5','5 hodnot'],['9','9 hodnot']],v.tokens)].join('');
      if (type === 'components') return [simRange('radius','Radius',0,24,2,v.radius,' px'),simRange('padding','Horiz. padding',8,28,2,v.padding,' px'),simToggle('states','Rozlišit hover / disabled',v.states)].join('');
      if (type === 'motioncurve') return [simRange('duration','Délka',100,1200,20,v.duration,' ms'),simSelect('easing','Easing',[['linear','linear'],['out','ease-out'],['bounce','přehnaný bounce']],v.easing)].join('');
      if (type === 'portfolioflow') return [simSelect('order','Pořadí case study',[['mockup','Mockup → problém → proces'],['correct','Problém → proces → rozhodnutí → výsledek → reflexe'],['screens','Screenshoty → screenshoty → export']],v.order)].join('');
      if (type === 'ipv6subnet') return [simSelect('prefix','Prefix',[['48','/48'],['64','/64'],['96','/96']],v.prefix),simToggle('same','Druhá adresa sdílí stejný prefix',v.same)].join('');
      if (type === 'dnsrecords') return [simSelect('alias','www.school.cz',[['a','A → IPv4'],['cname','CNAME → web.school.cz'],['txt','TXT']],v.alias),simToggle('a','web.school.cz má A',v.a),simToggle('aaaa','web.school.cz má AAAA',v.aaaa)].join('');
      if (type === 'monitoring') return [simSelect('check','Monitoring check',[['ping','ICMP ping'],['tcp','TCP/443'],['http','HTTPS GET /health']],v.check),simSelect('app','Stav aplikace',[['200','HTTP 200'],['503','HTTP 503']],v.app)].join('');
      if (type === 'dhcpreserve') return [simSelect('mac','MAC zařízení',[['wrong','AA:BB:CC:00:00:99'],['correct','AA:BB:CC:DD:EE:01']],v.mac),simSelect('ip','Reservation IP',[['50','192.168.10.50'],['55','192.168.10.55']],v.ip),simSelect('pool','Kolize s poolem',[['collision','IP už je v aktivní lease'],['safe','bez kolize']],v.pool)].join('');
      if (type === 'httpprobe') return [simSelect('status','HTTP status',[['200','200 OK'],['302','302 Found'],['404','404 Not Found'],['503','503 Service Unavailable']],v.status),simToggle('retry','Server posílá Retry-After',v.retry),simRange('latency','Latency',40,1500,20,v.latency,' ms')].join('');
      if (type === 'loadbalance') return [simSelect('policy','Policy',[['rr','round-robin'],['weighted','weighted 80/20']],v.policy),simSelect('web2','web02 health',[['healthy','healthy'],['unhealthy','unhealthy']],v.web2),simToggle('remove','Vyřadit unhealthy backend',v.remove)].join('');
      if (type === 'canary') return [simRange('traffic','Traffic na v2',0,100,5,v.traffic,' %'),simRange('err','Error rate v2',0,10,0.5,v.err,' %'),simRange('threshold','Stop threshold',0.5,6,0.5,v.threshold,' %')].join('');
      if (type === 'backuprestore') return [simSelect('point','Recovery point',[['old','včerejší neověřený'],['latest','poslední ověřený']],v.point),simToggle('verified','Ověřit integritu backupu',v.verified),simSelect('target','Restore cíl',[['prod','produkce'],['test','izolovaný test target']],v.target)].join('');
      if (type === 'responsivepreview') return [simSelect('target','Cílový formát',[['post','Post 1080×1350'],['square','Square 1080×1080'],['story','Story 1080×1920']],v.target),simRange('safe','Safe zone',4,14,1,v.safe,' %'),simToggle('hierarchy','Zachovat headline → info → CTA',v.hierarchy)].join('');
      if (type === 'accessibilityaudit') return [`<label class="sim-control color"><span>Pozadí</span><input type="color" value="${escapeHtml(v.bg)}" data-sim-input="bg"></label>`,`<label class="sim-control color"><span>Text</span><input type="color" value="${escapeHtml(v.text)}" data-sim-input="text"></label>`,simRange('size','Velikost textu',12,28,1,v.size,' px'),simToggle('focus','Viditelný focus ring',v.focus)].join('');
      if (type === 'packetflow') return [simSelect('fault','Simulovaná závada',[['arp','ARP nezná gateway'],['dns','DNS query bez response'],['tcp','TCP port odmítá'],['none','bez závady']],v.fault),simSelect('filter','Capture filtr',[['all','bez filtru'],['arp','arp'],['dns','dns'],['tcp','tcp.port == 443']],v.filter)].join('');
      if (type === 'slobudget') return [simSelect('slo','SLO cíl',[['99','99,0 %'],['99.9','99,9 %'],['99.99','99,99 %']],v.slo),simSelect('actual','Skutečná dostupnost',[['99.99','99,99 %'],['99.9','99,9 %'],['99.5','99,5 %']],v.actual),simSelect('action','Další krok',[['release','pokračovat v rollout'],['stabilize','stabilizovat + postmortem'],['ignore','ignorovat budget']],v.action)].join('');
      if (type === 'troubleshoot3') return `
        <div class="sim-command-picker">
          ${[['ipconfig','ipconfig /all'],['pinggw','ping 192.168.10.1'],['pingip','ping 10.20.0.15'],['nslookup','nslookup intranet.school.local'],['port','Test-NetConnection 10.20.0.15 -Port 443']].map(([k,l])=>`<button type="button" data-sim-command="${k}"><code>${escapeHtml(l)}</code><span>spustit</span></button>`).join('')}
        </div>`;
      if (type === 'production') return `
        <div class="sim-command-picker">
          ${[['curl','curl -I https://app.school.cz'],['dig','dig app.school.cz A'],['ss','ss -lntp'],['nginx','grep proxy_pass /etc/nginx/sites-enabled/app'],['route','ip route'],['logs','journalctl -u app --since -5m']].map(([k,l])=>`<button type="button" data-sim-command="${k}"><code>${escapeHtml(l)}</code><span>spustit</span></button>`).join('')}
        </div>
        <div class="sim-validation"><strong>Po opravě ověřím:</strong>
          ${[['curl-ok','externí URL vrací 200'],['logs-ok','log neobsahuje upstream chybu'],['monitor-ok','monitoring je zelený'],['rollback','mám připravený rollback']].map(([k,l])=>`<label><input type="checkbox" data-sim-validation="${k}"> ${escapeHtml(l)}</label>`).join('')}
        </div>`;
      return '';
    };

    const commandOutputs3 = {
      ipconfig: `Ethernet adapter:\n  IPv4 Address . . . . . : 192.168.10.42\n  Default Gateway . . . .: 192.168.10.1\n  DNS Servers . . . . . . : 192.168.99.53`,
      pinggw: `Reply from 192.168.10.1: bytes=32 time=1ms TTL=64\nPackets: Sent = 4, Received = 4, Lost = 0`,
      pingip: `Reply from 10.20.0.15: bytes=32 time=3ms TTL=62\nPackets: Sent = 4, Received = 4, Lost = 0`,
      nslookup: `Server:  192.168.99.53\n*** Request to 192.168.99.53 timed-out`,
      port: `ComputerName     : 10.20.0.15\nRemotePort       : 443\nTcpTestSucceeded : True`,
    };
    const commandOutputsProd = {
      curl: `HTTP/2 502\nserver: nginx\ncontent-type: text/html`,
      dig: `app.school.cz. 300 IN A 203.0.113.50`,
      ss: `LISTEN 0 4096 127.0.0.1:8000 0.0.0.0:* users:((\"app\",pid=2148,fd=9))`,
      nginx: `proxy_pass http://127.0.0.1:9000;`,
      route: `default via 10.20.0.1 dev eth0\n10.20.0.0/24 dev eth0 proto kernel src 10.20.0.30`,
      logs: `app[2148]: server started\napp[2148]: listening on http://127.0.0.1:8000`,
    };

    const showTerminal = (command, output) => {
      if (!terminal) return;
      terminal.hidden = false;
      terminal.innerHTML = `<div class="sim-terminal-head"><span>student@lab</span><b>evidence</b></div><pre><code>$ ${escapeHtml(command)}\n${escapeHtml(output)}</code></pre>`;
    };

    const taskOk = () => {
      const v = state.values;
      if (type === 'hierarchy') return Number(v.title) >= 54 && Number(v.info) <= 28 && Number(v.cta) >= 20 && Number(v.spacing) >= 16;
      if (type === 'grid') return Number(v.columns) >= 4 && Number(v.margin) >= 20 && Number(v.gutter) >= 12 && Boolean(v.align);
      if (type === 'export') return ['webp','jpg'].includes(v.format) && Number(v.width) >= 1600 && Number(v.width) <= 2560 && Number(v.quality) >= 75 && Number(v.quality) <= 90;
      if (type === 'contrast') return contrastRatio(v.bg,v.text) >= 4.5 && Number(v.size) >= 18;
      if (type === 'typography') return Number(v.fonts) <= 2 && Number(v.headline) >= Number(v.body)*2 && Number(v.line) >= 1.3;
      if (type === 'rastervector') return v.source === 'svg' && Number(v.zoom) >= 800;
      if (type === 'colormode') return v.mode === 'print' && Number(v.saturation) <= 70;
      if (type === 'subnetlab') return String(v.prefix) === '24';
      if (type === 'dnscache') return Number(v.elapsed) >= Number(v.ttl);
      if (type === 'httpsstack') return Boolean(v.dns) && Boolean(v.tcp) && !Boolean(v.tls);
      if (type === 'routinglab') return v.gateway === '192.168.10.1';
      if (type === 'cidrplan') return String(v.prefix) === '26';
      if (type === 'lpm') return String(v.route) === '24';
      if (type === 'dnsmigrate') return String(v.prettl) === '60';
      if (type === 'sshkeylab') return v.perm === '0600' && v.user === 'correct' && v.key === 'match';
      if (type === 'permissionslab') return v.mode === '640';
      if (type === 'dhcp') return v.server === 'on' && v.vlan === 'correct' && v.pool === 'free';
      if (type === 'reachability') return !v.icmp && v.service && v.firewall443;
      if (type === 'binding') return v.bind === '10.20.0.30' && v.firewall === 'allow';
      if (type === 'tcp') return !v.listening && v.firewall === 'allow';
      if (type === 'palette') return contrastRatio(v.bg,v.text) >= 4.5 && String(v.accents) === '1';
      if (type === 'iconset') return v.style === 'outline' && Number(v.stroke) === 2 && Number(v.size) >= 22 && Number(v.size) <= 28;
      if (type === 'cropfocus') return v.focus === 'right' && v.textpos === 'left' && v.contrast === 'high';
      if (type === 'critique') return Boolean(v.hierarchy) && Boolean(v.contrast) && Boolean(v.spacing);
      if (type === 'spacingtokens') return String(v.base) === '8' && Number(v.tokens) >= 4 && Number(v.tokens) <= 6;
      if (type === 'components') return Number(v.radius) >= 8 && Number(v.radius) <= 16 && Number(v.padding) >= 14 && Number(v.padding) <= 20 && Boolean(v.states);
      if (type === 'motioncurve') return Number(v.duration) >= 180 && Number(v.duration) <= 320 && v.easing === 'out';
      if (type === 'portfolioflow') return v.order === 'correct';
      if (type === 'ipv6subnet') return String(v.prefix) === '64' && Boolean(v.same);
      if (type === 'dnsrecords') return v.alias === 'cname' && Boolean(v.a) && Boolean(v.aaaa);
      if (type === 'monitoring') return v.check === 'http' && v.app === '503';
      if (type === 'dhcpreserve') return v.mac === 'correct' && v.ip === '50' && v.pool === 'safe';
      if (type === 'httpprobe') return v.status === '503' && Boolean(v.retry) && Number(v.latency) >= 500;
      if (type === 'loadbalance') return v.web2 === 'unhealthy' && Boolean(v.remove);
      if (type === 'canary') return Number(v.traffic) <= 10 && Number(v.err) > Number(v.threshold);
      if (type === 'backuprestore') return v.point === 'latest' && Boolean(v.verified) && v.target === 'test';
      if (type === 'responsivepreview') return v.target === 'story' && Number(v.safe) >= 8 && Boolean(v.hierarchy);
      if (type === 'accessibilityaudit') return contrastRatio(v.bg,v.text) >= 4.5 && Number(v.size) >= 16 && Boolean(v.focus);
      if (type === 'packetflow') return v.fault === 'dns' && v.filter === 'dns';
      if (type === 'slobudget') return v.slo === '99.9' && v.actual === '99.5' && v.action === 'stabilize';
      if (type === 'troubleshoot3') return ['ipconfig','pinggw','nslookup'].every((x)=>state.commands.has(x));
      if (type === 'production') return ['curl','dig','ss','nginx'].every((x)=>state.commands.has(x)) && state.validation.size >= 2;
      return false;
    };

    const render = () => {
      if (!stage) return;
      const v = state.values;
      let known = [], unknown = [];
      if (type === 'hierarchy') {
        const ok = taskOk();
        stage.innerHTML = `<div class="sim-poster" style="--sim-space:${Number(v.spacing)}px">
          <span class="sim-poster-kicker">EDUCANET · GAMING CLUB</span>
          <strong style="font-size:${Number(v.title)}px">NIGHT<br>ARENA</strong>
          <div class="sim-poster-info" style="font-size:${Number(v.info)}px">18. 9. · 18:00 · Brno</div>
          <button style="font-size:${Number(v.cta)}px">REGISTRUJ SE →</button><small>Vstup zdarma · kapacita omezená</small>
        </div><div class="sim-thumbnail"><span>Pohled z dálky</span><div class="mini-poster"><b style="font-size:${Math.max(7,Number(v.title)/7)}px">NIGHT ARENA</b><i style="font-size:${Math.max(5,Number(v.info)/7)}px">18. 9. · Brno</i><em style="font-size:${Math.max(5,Number(v.cta)/7)}px">REGISTRUJ SE</em></div></div>`;
        known = [`Titulek je ${v.title}px, informace ${v.info}px a CTA ${v.cta}px.`, Number(v.spacing)>=16?'Prvky mají dostatek prostoru.':'Spacing je velmi těsný.'];
        unknown = ok ? [] : ['Je cesta oka dost jasná i v malém náhledu?','Je sekundární text opravdu sekundární?'];
      } else if (type === 'grid') {
        const cols = Number(v.columns), margin=Number(v.margin), gutter=Number(v.gutter);
        stage.innerHTML = `<div class="sim-grid-canvas" style="--cols:${cols};--margin:${margin}px;--gutter:${gutter}px">
          <div class="sim-grid-lines">${Array.from({length:cols},()=>'<i></i>').join('')}</div>
          <div class="sim-layout ${v.align?'aligned':'loose'}"><b class="l-title">DESIGN WEEK</b><div class="l-image">IMAGE</div><p class="l-copy">Workshopy · přednášky<br>12.–14. října</p><span class="l-cta">PROGRAM →</span></div>
        </div>`;
        known = [`Grid: ${cols} sloupců, margin ${margin}px, gutter ${gutter}px.`, v.align?'Prvky sdílejí společné hrany.':'Prvky nejsou přichycené ke společným hranám.'];
        unknown = taskOk()?[]:['Jsou okraje dostatečné?','Je mezi sloupci konzistentní rytmus?'];
      } else if (type === 'export') {
        const width=Number(v.width), quality=Number(v.quality); const factor=v.format==='png'?0.9:(v.format==='jpg'?0.21:0.14); const kb=Math.round(width*width*0.5625*factor*(quality/100)/1000);
        const artifacts=quality<60?'výrazné':(quality<75?'viditelné':'nízké');
        stage.innerHTML = `<div class="sim-export-preview"><div class="sim-photo ${quality<60?'degraded':''}"><span>WEB HERO</span><strong>BUILD<br>BETTER.</strong></div><div class="sim-export-metrics"><div><span>Formát</span><b>${v.format.toUpperCase()}</b></div><div><span>Šířka</span><b>${width}px</b></div><div><span>Odhad velikosti*</span><b>~${kb} kB</b></div><div><span>Artefakty</span><b>${artifacts}</b></div></div><small>* Didaktický orientační odhad, ne skutečný encoder.</small></div>`;
        known = [`Zvolen ${v.format.toUpperCase()}, ${width}px, kvalita ${quality} %.`,`Orientační datová velikost: ~${kb} kB.`];
        unknown = taskOk()?[]:['Je rozlišení přiměřené cílovému použití?','Neplatíš příliš daty za kvalitu, kterou uživatel neuvidí?'];
      } else if (type === 'contrast') {
        const ratio=contrastRatio(v.bg,v.text); const pass=ratio>=4.5; const filter=v.gray?'grayscale(1)':'none';
        stage.innerHTML = `<div class="sim-contrast-stage" style="background:${escapeHtml(v.bg)};color:${escapeHtml(v.text)};filter:${filter}"><span>STUDENT DESIGN LAB</span><strong style="font-size:${Number(v.size)*2}px">NIGHT<br>SESSION</strong><p style="font-size:${Number(v.size)}px">18. 9. · 18:00 · Brno</p><button style="color:${escapeHtml(v.text)}">REGISTRUJ SE →</button></div><div class="sim-contrast-meter ${pass?'ok':'warn'}"><span>Orientační poměr</span><strong>${ratio.toFixed(2)} : 1</strong><small>${pass?'čitelný základ ✓':'zkus větší rozdíl světlosti'}</small></div>`;
        known=[`Orientační kontrastní poměr je ${ratio.toFixed(2)} : 1.`,v.gray?'Grayscale test je zapnutý.':'Náhled je barevný.',`Text má ${v.size}px.`];
        unknown=taskOk()?[]:['Zůstává informace čitelná i po odebrání barevného dojmu?','Je text dostatečně velký pro zamýšlené použití?'];
      } else if (type === 'typography') {
        const ratio=Number(v.headline)/Math.max(1,Number(v.body)); const good=taskOk();
        stage.innerHTML = `<div class="sim-type-stage ${Number(v.fonts)>2?'noisy':''}"><div class="type-kicker">EDUCANET DESIGN CLUB</div><strong style="font-size:${Number(v.headline)}px">MAKE IT<br>CLEAR.</strong><p style="font-size:${Number(v.body)}px;line-height:${Number(v.line)}">Workshop vizuální komunikace<br>12. října · Brno</p><em>Počet rodin: ${escapeHtml(v.fonts)}</em></div><div class="sim-type-score"><span>Poměr headline/body</span><strong>${ratio.toFixed(1)}×</strong><small>${good?'role jsou jasně oddělené':'systém je zatím příliš plochý nebo roztříštěný'}</small></div>`;
        known=[`Používáš ${v.fonts} rodin(y) písem.`,`Headline/body poměr je ${ratio.toFixed(1)}×.`,`Line-height je ${Number(v.line).toFixed(1)}.`];
        unknown=taskOk()?[]:['Je headline jasně první?', 'Není počet stylů větší než jejich skutečné role?'];
      } else if (type === 'rastervector') {
        const zoom=Number(v.zoom), raster=v.source==='png';
        stage.innerHTML = `<div class="sim-scale-stage"><div class="scale-logo ${raster&&zoom>=500?'pixelated':''}" style="--zoom:${Math.min(2.2,zoom/400)}"><span>▲</span><b>NOVA</b></div><div class="scale-meta"><span>${v.source.toUpperCase()}</span><strong>${zoom}%</strong><small>${raster?(zoom>=500?'pixelace je záměrně zvýrazněna':'raster zatím vypadá použitelně'):'vektor zůstává geometricky ostrý'}</small></div></div>`;
        known=[`${v.source.toUpperCase()} při ${zoom}% zvětšení.`,raster?'Zdroj má konečný počet pixelů.':'Zdroj je vektorový.'];
        unknown=taskOk()?[]:['Jak se bude zdroj chovat na mnohem větším výstupu?'];
      } else if (type === 'colormode') {
        const sat=Number(v.saturation); const print=v.mode==='print'; const shown=print?Math.round(sat*.72):sat;
        stage.innerHTML = `<div class="sim-color-mode"><div class="color-swatch" style="--sat:${shown}%"><span>${print?'SIMULOVANÝ TISK':'OBRAZOVKA'}</span><strong>NEON<br>EVENT</strong></div><div class="color-mode-meta"><span>Požadovaná sytost</span><b>${sat}%</b><span>Vizuální simulace</span><b>${shown}%</b><small>Didaktická simulace, ne ICC soft-proof.</small></div></div>`;
        known=[`Režim: ${print?'simulovaný tisk':'obrazovka'}.`,`Akcent má nastavenou sytost ${sat}%.`];
        unknown=taskOk()?[]:['Bude extrémně sytý RGB akcent v běžném tiskovém procesu působit stejně?'];
      } else if (type === 'subnetlab') {
        const same=String(v.prefix)==='24';
        stage.innerHTML=`<div class="sim-subnet-lab"><div class="subnet-host"><b>CLIENT</b><code>192.168.10.42/${escapeHtml(v.prefix)}</code></div><div class="subnet-path ${same?'direct':'gateway'}"><span>${same?'lokální ARP + Ethernet':'cíl je mimo lokální subnet'}</span><strong>${same?'────────────→':'────→ GATEWAY ────→'}</strong></div><div class="subnet-host"><b>TARGET</b><code>192.168.10.200</code></div></div><div class="sim-result-pill ${same?'ok':'warn'}">${same?'STEJNÝ SUBNET':'RŮZNÉ /25 SUBNETY'}</div>`;
        known=[`Zdroj je 192.168.10.42/${v.prefix}.`,same?'Cíl .200 patří při /24 do stejné sítě.':'Při /25 je .42 v bloku 0–127 a .200 v bloku 128–255.']; unknown=taskOk()?[]:['Rozhoduješ podle celé IP adresy spolu s prefixem?'];
      } else if (type === 'dnscache') {
        const expired=Number(v.elapsed)>=Number(v.ttl); const remaining=Math.max(0,Number(v.ttl)-Number(v.elapsed));
        stage.innerHTML=`<div class="sim-dns-cache"><div><span>AUTHORITATIVE</span><b>app.school.cz</b><code>10.20.0.30 · NEW</code></div><div class="cache-arrow">→</div><div class="${expired?'expired':''}"><span>RESOLVER CACHE</span><b>${expired?'10.20.0.30':'10.20.0.15'}</b><code>${expired?'cache refreshed':`TTL remaining ${remaining}s`}</code></div></div>`;
        known=[`TTL je ${v.ttl}s, od změny uběhlo ${v.elapsed}s.`,expired?'Původní cache už vypršela.':'Resolver smí stále používat starou cached odpověď.']; unknown=expired?[]:['Kolik času zbývá do expirace cache?'];
      } else if (type === 'httpsstack') {
        const layers=[['DNS',v.dns],['TCP :443',v.tcp],['TLS',v.tls],['HTTP',v.http]]; let blocked=false;
        stage.innerHTML=`<div class="sim-https-layers">${layers.map(([n,on])=>{const usable=!blocked&&on;if(!on)blocked=true;return `<div class="${usable?'ok':'fail'}"><span>${usable?'✓':'×'}</span><strong>${n}</strong></div>`}).join('')}</div><div class="sim-browser-result"><span>Browser</span><strong>${!v.dns?'DNS error':(!v.tcp?'Connection failed':(!v.tls?'Certificate / TLS error':(!v.http?'HTTP/app error':'200 OK')))}</strong></div>`;
        known=[`DNS: ${v.dns?'OK':'FAIL'}.`,`TCP/443: ${v.tcp?'OK':'FAIL'}.`,`TLS: ${v.tls?'OK':'FAIL'}.`]; unknown=taskOk()?[]:['Dokážeš izolovat TLS problém, aniž bys rozbil DNS/TCP?'];
      } else if (type === 'routinglab') {
        const ok=v.gateway==='192.168.10.1';
        stage.innerHTML=`<div class="sim-route-map"><div><b>CLIENT</b><code>192.168.10.42/24</code></div><span>→</span><div class="${ok?'ok':'fail'}"><b>GATEWAY</b><code>${escapeHtml(v.gateway)}</code></div><span>→</span><div><b>INTERNET</b><code>8.8.8.8</code></div></div>`;
        known=[`Klient je 192.168.10.42/24.`,`Gateway je ${v.gateway}.`,ok?'Gateway je lokálně dosažitelná.':'Gateway leží mimo klientův lokální /24.']; unknown=ok?[]:['Jak klient doručí rámec gateway, která není v jeho lokálním subnetu?'];
      } else if (type === 'cidrplan') {
        const prefix=Number(v.prefix); const total=Math.pow(2,32-prefix); const usable=Math.max(0,total-2); const enough=usable>=50; const minimal=prefix===26;
        stage.innerHTML=`<div class="sim-cidr-plan"><div class="cidr-bar" style="--fill:${Math.min(100,50/usable*100)}%"><span></span></div><div class="cidr-stats"><div><span>Subnet</span><b>/${prefix}</b></div><div><span>Adres celkem</span><b>${total}</b></div><div><span>Typicky použitelných</span><b>${usable}</b></div><div><span>Požadavek</span><b>50 hostů</b></div></div></div>`;
        known=[`/${prefix} dává ${usable} typicky použitelných host adres.`,enough?'Kapacitně vyhoví.':'Kapacitně nestačí.']; unknown=minimal?[]:['Je to nejmenší subnet z nabízených variant, který vyhoví?'];
      } else if (type === 'lpm') {
        const routes=[['8','10.0.0.0/8'],['16','10.20.0.0/16'],['24','10.20.30.0/24'],['0','default']];
        stage.innerHTML=`<div class="sim-lpm"><div class="lpm-dest"><span>DESTINATION</span><strong>10.20.30.55</strong></div>${routes.map(([k,l])=>`<div class="lpm-route ${v.route===k?'selected':''} ${k==='24'?'best':''}"><code>${l}</code><span>${k==='24'?'nejkonkrétnější':'shoda'}</span></div>`).join('')}</div>`;
        known=[`Vybral/a jsi ${routes.find(x=>x[0]===String(v.route))?.[1]||'route'}.`,'Pro destination odpovídají /8, /16 i /24.']; unknown=taskOk()?[]:['Který odpovídající prefix je nejdelší?'];
      } else if (type === 'dnsmigrate') {
        const ttl=Number(v.prettl); const good=ttl<=60;
        stage.innerHTML=`<div class="sim-migrate"><div><span>T−24h</span><strong>TTL ${ttl}s</strong><small>${good?'krátká cache připravena':'část resolverů může držet hodnotu dlouho'}</small></div><div class="migrate-arrow">→</div><div><span>T0</span><strong>A → NEW IP</strong><small>203.0.113.50</small></div><div class="migrate-arrow">→</div><div><span>T+</span><strong>${good?'rychlejší konvergence':'pomalejší konvergence'}</strong></div></div>`;
        known=[`Před migrací je TTL ${ttl}s.`,good?'Staré cache mají krátké maximální okno.':'Staré cache mohou přežívat výrazně déle.']; unknown=good?[]:['Kdy je potřeba snížit TTL, aby to mělo efekt na staré cache?'];
      } else if (type === 'sshkeylab') {
        let result='SUCCESS'; if(v.perm!=='0600')result='PRIVATE KEY TOO OPEN'; else if(v.user!=='correct')result='UNKNOWN / WRONG USER'; else if(v.key!=='match')result='PERMISSION DENIED (PUBLICKEY)';
        stage.innerHTML=`<div class="sim-ssh-auth"><div><span>PRIVATE KEY</span><strong>${escapeHtml(v.perm)}</strong></div><span>→</span><div><span>USER</span><strong>${v.user==='correct'?'deploy':'admin'}</strong></div><span>→</span><div><span>AUTHORIZED_KEYS</span><strong>${v.key==='match'?'MATCH':'NO MATCH'}</strong></div></div><div class="sim-terminal-inline ${result==='SUCCESS'?'ok':'fail'}"><code>${result}</code></div>`;
        known=[`Private key permissions: ${v.perm}.`,`Username: ${v.user==='correct'?'deploy':'admin'}.`,`Public key: ${v.key==='match'?'odpovídá':'neodpovídá'}.`]; unknown=taskOk()?[]:['Která fáze autentizace ještě selhává?'];
      } else if (type === 'permissionslab') {
        const map={'777':['rwx','rwx','rwx'],'644':['rw-','r--','r--'],'640':['rw-','r--','---'],'600':['rw-','---','---']}; const row=map[v.mode]||map['777'];
        stage.innerHTML=`<div class="sim-perm-table"><div><span>OWNER · deploy</span><b>${row[0]}</b></div><div><span>GROUP · web</span><b>${row[1]}</b></div><div><span>OTHER</span><b>${row[2]}</b></div><strong>chmod ${escapeHtml(v.mode)} config.env</strong></div>`;
        known=[`Owner: ${row[0]}, group: ${row[1]}, other: ${row[2]}.`]; unknown=taskOk()?[]:['Má group pouze read a other žádná práva?'];
      } else if (type === 'dhcp') {
        const offer=v.server==='on'&&v.vlan==='correct'&&v.pool==='free';
        const serverReach=v.server==='on'&&v.vlan==='correct';
        stage.innerHTML = `<div class="sim-dora"><div class="ok">DISCOVER <span>klient → broadcast</span></div><div class="${serverReach?'ok':'fail'}">OFFER <span>${serverReach?(v.pool==='free'?'server nabízí lease':'pool nemá volnou lease'):'bez odpovědi'}</span></div><div class="${offer?'ok':'muted'}">REQUEST <span>${offer?'klient žádá nabídnutou IP':'neproběhne'}</span></div><div class="${offer?'ok':'muted'}">ACK <span>${offer?'IP + GW + DNS':'neproběhne'}</span></div></div><div class="sim-ipconfig"><code>IPv4: ${offer?'192.168.10.42':'169.254.41.18'}<br>Gateway: ${offer?'192.168.10.1':'—'}<br>DNS: ${offer?'192.168.10.53':'—'}</code></div>`;
        known = [serverReach?'Klient se dostane k DHCP serveru.':'Klient nedostává použitelnou OFFER.', offer?'DORA doběhl až do ACK.':'Klient nemá kompletní DHCP lease.'];
        unknown = offer?[]:['Je problém server, VLAN, nebo pool?','Dokud není platná IP konfigurace, nemá smysl začínat TLS diagnostikou.'];
      } else if (type === 'reachability') {
        const ping=Boolean(v.icmp); const tcp=Boolean(v.service)&&Boolean(v.firewall443); const curl=tcp;
        stage.innerHTML = `<div class="sim-reach"><div class="sim-host client">CLIENT</div><div class="sim-wire ${ping?'ok':'blocked'}"><span>ICMP</span></div><div class="sim-host server">WEB01<small>HTTPS ${v.service?'RUNNING':'STOPPED'}</small></div></div><div class="sim-test-grid"><div class="${ping?'ok':'fail'}"><code>ping web01</code><b>${ping?'REPLY':'TIMEOUT'}</b></div><div class="${tcp?'ok':'fail'}"><code>TCP :443</code><b>${tcp?'OPEN':'FAILED'}</b></div><div class="${curl?'ok':'fail'}"><code>curl https://web01</code><b>${curl?'HTTP 200':'NO RESPONSE'}</b></div></div>`;
        known = [`ICMP je ${ping?'povolené':'blokované'}.`,`HTTPS služba ${v.service?'běží':'neběží'}; firewall 443 ${v.firewall443?'povoluje':'blokuje'}.`];
        unknown = taskOk()?[]:['Dokážeš vytvořit stav „ping ne, HTTPS ano“?'];
      } else if (type === 'binding') {
        const local=true; const remote=(v.bind==='0.0.0.0'||v.bind==='10.20.0.30')&&v.firewall==='allow';
        stage.innerHTML = `<div class="sim-binding-map"><div class="sim-remote ${remote?'ok':'fail'}">LAN CLIENT<small>${remote?'CONNECTED':'NO ACCESS'}</small></div><div class="sim-bind-arrow ${remote?'ok':'fail'}">TCP/8000 →</div><div class="sim-server-interfaces"><strong>SERVER</strong><div class="${v.bind==='127.0.0.1'||v.bind==='0.0.0.0'?'active':''}">lo <code>127.0.0.1</code></div><div class="${v.bind==='10.20.0.30'||v.bind==='0.0.0.0'?'active':''}">eth0 <code>10.20.0.30</code></div><small>bind: ${escapeHtml(v.bind)}:8000</small></div></div><div class="sim-test-grid"><div class="ok"><code>curl localhost:8000</code><b>${local?'200 OK':'FAIL'}</b></div><div class="${remote?'ok':'fail'}"><code>curl 10.20.0.30:8000 z LAN</code><b>${remote?'200 OK':'FAIL'}</b></div></div>`;
        known = [`Proces poslouchá na ${v.bind}:8000.`,`Firewall z LAN: ${v.firewall==='allow'?'ALLOW':'DENY'}.`];
        unknown = taskOk()?[]:['Je služba dostupná z požadovaného interface?','Nevystavuješ ji na více interface, než úkol potřebuje?'];
      } else if (type === 'tcp') {
        let result, flow;
        if (v.firewall==='drop') { result='TIMEOUT'; flow=['SYN →','… žádná odpověď']; }
        else if (v.firewall==='reject') { result='REJECTED'; flow=['SYN →','← ICMP/RST reject']; }
        else if (v.listening) { result='ESTABLISHED'; flow=['SYN →','← SYN-ACK','ACK →']; }
        else { result='CONNECTION REFUSED'; flow=['SYN →','← RST']; }
        stage.innerHTML = `<div class="sim-handshake"><div><b>CLIENT</b></div><div class="sim-packets">${flow.map((x,i)=>`<span style="--delay:${i}">${escapeHtml(x)}</span>`).join('')}</div><div><b>SERVER</b><small>${v.listening?'LISTENING':'NO LISTENER'}</small></div></div><div class="sim-result-pill ${result==='ESTABLISHED'?'ok':'warn'}">${result}</div>`;
        known = [`Firewall: ${String(v.firewall).toUpperCase()}.`,`Listener: ${v.listening?'ano':'ne'}.`,`Výsledek klienta: ${result}.`];
        unknown = taskOk()?[]:['Jaký rozdíl je mezi zahazeným paketem a aktivním RST?'];
      } else if (type === 'palette') {
        const ratio=contrastRatio(v.bg,v.text); const accents=Number(v.accents); const extra=accents>1?['#38bdf8','#f472b6','#f59e0b'].slice(0,accents-1):[];
        stage.innerHTML=`<div class="sim-palette-stage" style="background:${escapeHtml(v.bg)};color:${escapeHtml(v.text)}"><span>EDUCANET / EVENT</span><strong>MAKE IT<br>CLEAR.</strong><button style="background:${escapeHtml(v.accent)}">REGISTRUJ SE</button><div class="sim-swatches"><i style="background:${escapeHtml(v.bg)}"></i><i style="background:${escapeHtml(v.text)}"></i><i style="background:${escapeHtml(v.accent)}"></i>${extra.map(c=>`<i style="background:${c}"></i>`).join('')}</div></div><div class="sim-result-pill ${ratio>=4.5&&accents===1?'ok':'warn'}">text/background ${ratio.toFixed(1)}:1 · ${accents} accent</div>`;
        known=[`Kontrast text/background je ${ratio.toFixed(1)}:1.`,`Používáš ${accents} akcentní barvu/barvy.`]; unknown=taskOk()?[]:['Je accent opravdu jen jeden hlavní signál?','Je text čitelný na backgroundu?'];
      } else if (type === 'iconset') {
        const mixed=v.style==='mixed'; const icons=['◷','⌖','↗'];
        stage.innerHTML=`<div class="sim-icon-row ${mixed?'mixed':''}">${icons.map((x,i)=>`<div style="--stroke:${v.stroke}px;--sz:${v.size}px"><i>${i===1&&mixed?'📍':x}</i><span>${['čas','místo','registrace'][i]}</span></div>`).join('')}</div><div class="sim-result-pill ${taskOk()?'ok':'warn'}">${v.style} · ${v.stroke}px · ${v.size}px</div>`;
        known=[`Styl: ${v.style}.`,`Tloušťka: ${v.stroke}px, velikost ${v.size}px.`]; unknown=taskOk()?[]:['Působí ikony jako jedna rodina?'];
      } else if (type === 'cropfocus') {
        const collide=v.focus===v.textpos; const pos={left:'18%',center:'50%',right:'82%'};
        stage.innerHTML=`<div class="sim-crop-stage ${v.contrast}"><div class="crop-subject" style="left:${pos[v.focus]}"><span>●</span><b>FOCAL</b></div><div class="crop-copy ${collide?'collision':''}" style="left:${pos[v.textpos]}"><strong>NIGHT<br>SESSION</strong><small>18. 9. · Brno</small></div></div><div class="sim-result-pill ${!collide&&v.contrast==='high'?'ok':'warn'}">${collide?'kolize textu a focal pointu':'oddělené zóny'} · ${v.contrast} contrast</div>`;
        known=[`Focal point je ${v.focus}.`,`Text je ${v.textpos}.`,`Kontrast: ${v.contrast}.`]; unknown=taskOk()?[]:['Má text vlastní klidovou zónu?'];
      } else if (type === 'critique') {
        const fixed=[v.hierarchy,v.contrast,v.spacing].filter(Boolean).length;
        stage.innerHTML=`<div class="sim-critique-poster f${fixed}"><strong style="font-size:${v.hierarchy?'54':'28'}px">OPEN DAY</strong><p style="opacity:${v.contrast?'1':'.35'}">12. 10. · BRNO · 17:00</p><button style="margin-top:${v.spacing?'28':'4'}px">PŘIJĎ</button></div><div class="sim-fix-list"><span class="${v.hierarchy?'ok':''}">hierarchie</span><span class="${v.contrast?'ok':''}">kontrast</span><span class="${v.spacing?'ok':''}">spacing</span></div>`;
        known=[`${fixed}/3 zásadních problémů je opraveno.`]; unknown=taskOk()?[]:['Který funkční problém ještě zůstal?'];
      } else if (type === 'spacingtokens') {
        const base=Number(v.base), count=Number(v.tokens); const vals=Array.from({length:count},(_,i)=>base*(i+1));
        stage.innerHTML=`<div class="sim-token-list">${vals.map((x,i)=>`<div><span>${['xs','sm','md','lg','xl','2xl','3xl'][i]||'t'+i}</span><b>${x}px</b><i style="width:${Math.min(100,x*2)}px"></i></div>`).join('')}</div><div class="sim-ui-cards"><div>Card A</div><div>Card B</div><div>Card C</div></div>`;
        known=[`Base ${base}px, ${count} tokenů.`,`Hodnoty: ${vals.join(', ')} px.`]; unknown=taskOk()?[]:['Je sada dost malá, aby byla zapamatovatelná, ale dost bohatá pro různé role?'];
      } else if (type === 'components') {
        stage.innerHTML=`<div class="sim-component-stage" style="--r:${v.radius}px;--p:${v.padding}px"><button class="primary">Primary</button><button>Secondary</button><button class="disabled ${v.states?'state-ok':''}">Disabled</button><div class="component-card"><strong>Component card</strong><span>stejný radius + spacing</span></div></div>`;
        known=[`Radius ${v.radius}px, horizontal padding ${v.padding}px.`,v.states?'Stavy jsou rozlišitelné.':'Disabled state je nerozlišitelný.']; unknown=taskOk()?[]:['Jsou role konzistentní bez ztráty významu?'];
      } else if (type === 'motioncurve') {
        const d=Number(v.duration); const ease=v.easing==='out'?'cubic-bezier(.2,.8,.2,1)':(v.easing==='bounce'?'cubic-bezier(.2,1.8,.5,1)':'linear');
        stage.innerHTML=`<div class="sim-motion-track"><span>CLICK</span><i class="sim-motion-dot"></i><b>✓ SAVED</b></div><div class="sim-result-pill ${taskOk()?'ok':'warn'}">${d} ms · ${v.easing}</div>`;
        requestAnimationFrame(()=>{const dot=stage.querySelector('.sim-motion-dot'); if(dot&&dot.animate) dot.animate([{transform:'translateX(0)',opacity:.4},{transform:'translateX(220px)',opacity:1}],{duration:d,easing:ease,iterations:2,direction:'alternate'});});
        known=[`Duration ${d} ms.`,`Easing ${v.easing}.`]; unknown=taskOk()?[]:['Je feedback rychlý a klidný?'];
      } else if (type === 'portfolioflow') {
        const seq=v.order==='correct'?['PROBLÉM','PROCES','ROZHODNUTÍ','VÝSLEDEK','REFLEXE']:(v.order==='mockup'?['MOCKUP','PROBLÉM','PROCES','VÝSLEDEK']:['SCREEN','SCREEN','SCREEN','EXPORT']);
        stage.innerHTML=`<div class="sim-portfolio-flow">${seq.map((x,i)=>`<div><span>0${i+1}</span><strong>${x}</strong></div>`).join('')}</div>`;
        known=[`Příběh: ${seq.join(' → ')}.`]; unknown=taskOk()?[]:['Rozumí divák problému dřív, než uvidí řešení?'];
      } else if (type === 'ipv6subnet') {
        const prefix=Number(v.prefix); const same=Boolean(v.same); const addr2=same?'2001:db8:10:20::99':'2001:db8:10:21::99';
        stage.innerHTML=`<div class="sim-ipv6"><code>2001:db8:10:20::42/${prefix}</code><span>${same?'↔':'⇢'}</span><code>${addr2}/${prefix}</code><strong>${prefix===64&&same?'SAME /64':'CHECK PREFIX'}</strong></div>`;
        known=[`Prefix /${prefix}.`,`Druhá adresa ${same?'sdílí':'nesdílí'} 2001:db8:10:20.`]; unknown=taskOk()?[]:['Kde přesně končí síťová část?'];
      } else if (type === 'dnsrecords') {
        stage.innerHTML=`<div class="sim-dns-record-chain"><div><b>www.school.cz</b><span>${v.alias.toUpperCase()}</span></div><i>→</i><div><b>${v.alias==='cname'?'web.school.cz':(v.alias==='a'?'203.0.113.50':'text')}</b><span>${v.a?'A ✓':'A ×'} · ${v.aaaa?'AAAA ✓':'AAAA ×'}</span></div></div>`;
        known=[`www používá ${v.alias.toUpperCase()}.`,`Cílový host má A ${v.a?'ano':'ne'} a AAAA ${v.aaaa?'ano':'ne'}.`]; unknown=taskOk()?[]:['Je alias řešen přes jméno a má cílový host oba address records?'];
      } else if (type === 'monitoring') {
        const detects=v.check==='http' || (v.check==='tcp'&&v.app==='200'); const appBad=v.app==='503';
        const result=v.check==='ping'?'HOST UP':(v.check==='tcp'?'TCP OPEN':`HTTP ${v.app}`);
        stage.innerHTML=`<div class="sim-monitor-stack"><div>SERVER <small>host up</small></div><span>→</span><div>WEB <small>${appBad?'503 unhealthy':'200 healthy'}</small></div><span>→</span><div class="${v.check==='http'&&appBad?'fail':'ok'}">CHECK <strong>${result}</strong></div></div>`;
        known=[`Check: ${v.check}.`,`Aplikace vrací ${v.app}.`]; unknown=taskOk()?[]:['Detekuje zvolený check stav aplikace, nebo jen host/port?'];
      } else if (type === 'dhcpreserve') {
        const goodmac=v.mac==='correct', safe=v.pool==='safe';
        stage.innerHTML=`<div class="sim-reservation"><div><span>PRINTER</span><strong>${goodmac?'AA:BB:CC:DD:EE:01':'AA:BB:CC:00:00:99'}</strong></div><span>→</span><div><span>DHCP</span><strong>${v.ip==='50'?'192.168.10.50':'192.168.10.55'}</strong></div><span>→</span><div class="${safe?'ok':'fail'}"><span>LEASE</span><strong>${safe?'RESERVED':'COLLISION'}</strong></div></div>`;
        known=[`MAC ${goodmac?'odpovídá':'neodpovídá'} tiskárně.`,`Vybraná IP je ${safe?'bez kolize':'v kolizi'}.`]; unknown=taskOk()?[]:['Je reservation v souladu s adresním plánem?'];
      } else if (type === 'httpprobe') {
        const st=String(v.status), latency=Number(v.latency); const cls=st.startsWith('2')?'ok':(st.startsWith('5')?'fail':'warn');
        stage.innerHTML=`<div class="sim-http-probe ${cls}"><pre><code>HTTP/2 ${st}
server: nginx
${v.retry?'retry-after: 30\n':''}x-request-id: req-7f31
server-timing: app;dur=${latency}</code></pre><div><span>LATENCY</span><strong>${latency} ms</strong></div></div>`;
        known=[`Status ${st}.`,`Latency ${latency} ms.`,v.retry?'Retry-After je přítomný.':'Retry-After chybí.']; unknown=taskOk()?[]:['Co přesně tato HTTP odpověď dokazuje o cestě requestu?'];
      } else if (type === 'loadbalance') {
        const unhealthy=v.web2==='unhealthy'; const routed2=unhealthy&&!v.remove?10:(v.policy==='weighted'?4:10); const routed1=20-routed2;
        stage.innerHTML=`<div class="sim-lb"><div class="lb-head">20 requests</div><div class="lb-router">LB<br><small>${v.policy}</small></div><div class="lb-backends"><div class="ok">web01 <b>${routed1}</b></div><div class="${unhealthy?'fail':'ok'}">web02 <b>${routed2}</b><small>${v.web2}</small></div></div></div>`;
        known=[`web02 je ${v.web2}.`,`Vyřazení unhealthy: ${v.remove?'ano':'ne'}.`,`web02 dostal ${routed2}/20 requestů.`]; unknown=taskOk()?[]:['Proč unhealthy backend ještě dostává traffic?'];
      } else if (type === 'canary') {
        const traffic=Number(v.traffic), err=Number(v.err), th=Number(v.threshold); const rollback=err>th;
        stage.innerHTML=`<div class="sim-canary"><div><span>v1</span><i style="width:${100-traffic}%"></i><b>${100-traffic}%</b></div><div><span>v2</span><i class="${rollback?'fail':''}" style="width:${traffic}%"></i><b>${traffic}%</b></div><section><span>v2 error</span><strong>${err}%</strong><em>stop &gt; ${th}%</em></section><div class="sim-result-pill ${rollback?'warn':'ok'}">${rollback?'ROLLBACK':'CONTINUE / OBSERVE'}</div></div>`;
        known=[`v2 traffic ${traffic}%.`,`v2 error rate ${err}%, threshold ${th}%.`]; unknown=taskOk()?[]:['Je blast radius omezený a stop podmínka překročená?'];
      } else if (type === 'backuprestore') {
        const point=v.point==='latest'?'2026-09-11 17:45 verified':'2026-09-10 02:00 unverified';
        stage.innerHTML=`<div class="sim-restore"><div><span>BACKUP</span><strong>${point}</strong></div><span>→</span><div class="${v.verified?'ok':'warn'}"><span>VERIFY</span><strong>${v.verified?'PASS':'NOT RUN'}</strong></div><span>→</span><div class="${v.target==='test'?'ok':'fail'}"><span>RESTORE</span><strong>${v.target.toUpperCase()}</strong></div></div>`;
        known=[`Recovery point: ${point}.`,`Integrity verify: ${v.verified?'ano':'ne'}.`,`Restore target: ${v.target}.`]; unknown=taskOk()?[]:['Je recovery point důvěryhodný a restore izolovaný od produkce?'];
      } else if (type === 'responsivepreview') {
        const dims=v.target==='story'?'1080×1920':(v.target==='square'?'1080×1080':'1080×1350');
        const ar=v.target==='story'?'9/16':(v.target==='square'?'1/1':'4/5');
        stage.innerHTML=`<div class="sim-responsive-wrap"><div class="sim-device-preview ${v.target}" style="--safe:${Number(v.safe)}%;aspect-ratio:${ar}"><i></i><div class="sim-responsive-content ${v.hierarchy?'ordered':'flat'}"><span>EDUCANET DESIGN</span><strong>CREATE<br>BETTER.</strong><p>Workshop · 18:00 · Brno</p><b>REGISTRUJ SE →</b></div></div><div class="sim-responsive-meta"><span>${dims}</span><strong>${v.hierarchy?'Hierarchie zachována':'Hierarchie rozpadlá'}</strong><small>safe zone ${v.safe}%</small></div></div>`;
        known=[`Cílový formát ${dims}.`,`Safe zone ${v.safe} %.`,v.hierarchy?'Pořadí headline → info → CTA je zachované.':'Role prvků nejsou jasně zachované.']; unknown=taskOk()?[]:['Je layout přizpůsobený story, ne jen oříznutý?'];
      } else if (type === 'accessibilityaudit') {
        const ratio=contrastRatio(v.bg,v.text); const pass=ratio>=4.5;
        stage.innerHTML=`<div class="sim-a11y-card" style="background:${escapeHtml(v.bg)};color:${escapeHtml(v.text)}"><span>STUDENT PORTAL</span><strong style="font-size:${Number(v.size)*1.65}px">Přihlášení</strong><p style="font-size:${Number(v.size)}px">Zkontroluj kontrast, velikost a ovládací stav.</p><button class="${v.focus?'focus-on':''}">Pokračovat</button></div><div class="sim-a11y-score ${pass?'ok':'warn'}"><span>CONTRAST</span><strong>${ratio.toFixed(2)} : 1</strong><small>${pass?'AA základ splněn':'kontrast je slabý'}</small></div>`;
        known=[`Kontrast ${ratio.toFixed(2)} : 1.`,`Text ${v.size}px.`,v.focus?'Focus ring je viditelný.':'Focus ring chybí.']; unknown=taskOk()?[]:['Zůstane ovládání čitelné i bez myši a při horším kontrastu displeje?'];
      } else if (type === 'packetflow') {
        const fault=v.fault, filter=v.filter;
        const rows=[['ARP','Who has 192.168.10.1?','192.168.10.1 is-at 52:54:00:aa:01:01'],['DNS','A intranet.school.local','A 10.20.0.15'],['TCP','SYN 10.20.0.15:443','SYN,ACK']];
        const visible=filter==='all'?rows:rows.filter(r=>r[0].toLowerCase()===filter);
        stage.innerHTML=`<div class="sim-packet-head"><span>CAPTURE FILTER</span><strong>${escapeHtml(filter==='all'?'none':filter)}</strong><em>fault: ${escapeHtml(fault)}</em></div><div class="sim-packet-list">${visible.map(r=>{let response=r[2];let cls='ok';if(fault===r[0].toLowerCase()){response=fault==='dns'?'— timeout —':(fault==='tcp'?'RST':'— no reply —');cls='fail';}return `<div><span>${r[0]}</span><code>${r[1]}</code><i>→</i><code class="${cls}">${response}</code></div>`}).join('')}</div>`;
        known=[`Simulovaná závada: ${fault}.`,`Použitý filtr: ${filter}.`]; if(filter==='dns'&&fault==='dns')known.push('DNS query je vidět, ale response chybí.'); else unknown.push('Je filtr dost úzký na otázku, kterou řešíš?'); if(!taskOk())unknown.push('Dokážeš z capture odlišit ARP, DNS a TCP symptom?');
      } else if (type === 'slobudget') {
        const slo=Number(v.slo), actual=Number(v.actual), month=30*24*60; const budget=Math.max(0,month*(100-slo)/100); const used=Math.max(0,month*(100-actual)/100); const pct=budget>0?Math.min(180,Math.round(used/budget*100)):180; const breached=actual<slo;
        stage.innerHTML=`<div class="sim-slo"><div class="sim-slo-head"><span>30denní okno</span><strong>SLO ${slo}%</strong><b>actual ${actual}%</b></div><div class="sim-budget-bar"><i style="width:${Math.min(100,pct)}%" class="${breached?'fail':'ok'}"></i></div><div class="sim-slo-stats"><div><span>Budget</span><strong>${budget.toFixed(1)} min</strong></div><div><span>Odhad chyb</span><strong>${used.toFixed(1)} min</strong></div><div><span>Stav</span><strong>${breached?'BREACH':'OK'}</strong></div></div><div class="sim-result-pill ${v.action==='stabilize'&&breached?'ok':'warn'}">${v.action==='stabilize'?'STABILIZE + POSTMORTEM':v.action.toUpperCase()}</div></div>`;
        known=[`SLO ${slo} %.`,`Skutečnost ${actual} %.`,`Odhad budgetu ${budget.toFixed(1)} min vs. chyb ${used.toFixed(1)} min.`]; unknown=taskOk()?[]:['Odpovídá rozhodnutí stavu error budgetu?'];
      } else if (type === 'troubleshoot3') {
        const c=state.commands;
        stage.innerHTML = `<div class="sim-incident-card"><span>INC-3A-014 · 09:05</span><strong>„Intranet nejde, ale přes IP se na web dostanu.“</strong><p>Tvým cílem není spustit všechny příkazy. Tvým cílem je získat minimum evidence, které odliší síť, DNS a službu.</p><div class="sim-command-count">Použité testy: <b>${c.size}</b></div></div>`;
        if(c.has('ipconfig')) known.push('Klient má 192.168.10.42/24, gateway 192.168.10.1 a DNS 192.168.99.53.'); else unknown.push('Jakou IP, gateway a DNS klient skutečně používá?');
        if(c.has('pinggw')) known.push('Lokální gateway je dosažitelná.'); else unknown.push('Funguje alespoň lokální cesta ke gateway?');
        if(c.has('pingip')) known.push('Server 10.20.0.15 je dosažitelný přes IP.'); else unknown.push('Je server dosažitelný přes IP?');
        if(c.has('nslookup')) known.push('Dotaz na nakonfigurovaný DNS 192.168.99.53 timeoutuje.'); else unknown.push('Překládá klient intranetové jméno?');
        if(c.has('port')) known.push('TCP/443 na serveru je z klienta dostupný.'); else unknown.push('Je konkrétní HTTPS služba dostupná?');
      } else if (type === 'production') {
        const c=state.commands;
        stage.innerHTML = `<div class="sim-prod-map"><div>USER</div><span>HTTPS</span><div>NGINX<small>502</small></div><span>UPSTREAM</span><div>APP<small>?</small></div></div><div class="sim-incident-card production"><span>PROD-502 · 14:05 · P1</span><strong>Deploy proběhl. Monitoring hlásí HTTP 502.</strong><p>Neprováděj změnu, dokud neumíš ukázat konkrétní nesoulad v evidence.</p><div class="sim-command-count">Použité testy: <b>${c.size}</b> · validace: <b>${state.validation.size}</b></div></div>`;
        if(c.has('curl')) known.push('Nginx z veřejné URL odpovídá HTTP 502.'); else unknown.push('Odpovídá vůbec veřejný endpoint a jakým HTTP stavem?');
        if(c.has('dig')) known.push('DNS vrací očekávanou IP 203.0.113.50.'); else unknown.push('Míří DNS na správný host?');
        if(c.has('ss')) known.push('Aplikace poslouchá na 127.0.0.1:8000.'); else unknown.push('Na jakém portu aplikace skutečně poslouchá?');
        if(c.has('nginx')) known.push('Nginx proxy_pass míří na 127.0.0.1:9000.'); else unknown.push('Na jaký upstream míří reverse proxy?');
        if(c.has('route')) known.push('Default route je přítomná přes 10.20.0.1.');
        if(c.has('logs')) known.push('Aplikace po deployi startovala a hlásí listener na :8000.');
        if(state.validation.size<2) unknown.push('Po opravě musíš ověřit původní symptom i provozní stav.');
      }
      simEvidence(sim, known, unknown);
      if (status) status.textContent = taskOk() ? 'Cíl splněn ✓' : 'Experimentuj';
      sim.classList.toggle('task-ok', taskOk());
      setSimPhase(sim, known.length ? 'evidence' : 'experiment');
    };

    const bindControls = () => {
      if (!controls) return;
      controls.innerHTML = controlsMarkup();
      controls.querySelectorAll('[data-sim-input]').forEach((input) => {
        input.addEventListener('input', () => {
          const name=input.getAttribute('data-sim-input'); if(!name) return;
          state.values[name]=input.type==='checkbox'?input.checked:(name==='line'?Number(input.value)/10:input.value);
          const out=controls.querySelector(`[data-sim-value="${name}"]`); if(out) out.textContent=`${input.value}${input.getAttribute('data-suffix')||''}`;
          state.verified=false; render();
        });
      });
      controls.querySelectorAll('[data-sim-command]').forEach((button) => {
        button.addEventListener('click', () => {
          const cmd=button.getAttribute('data-sim-command'); if(!cmd) return;
          state.commands.add(cmd); button.classList.add('ran');
          const labels = type==='production' ? {curl:'curl -I https://app.school.cz',dig:'dig app.school.cz A',ss:'ss -lntp',nginx:'grep proxy_pass /etc/nginx/sites-enabled/app',route:'ip route',logs:'journalctl -u app --since -5m'} : {ipconfig:'ipconfig /all',pinggw:'ping 192.168.10.1',pingip:'ping 10.20.0.15',nslookup:'nslookup intranet.school.local',port:'Test-NetConnection 10.20.0.15 -Port 443'};
          showTerminal(labels[cmd]||cmd, (type==='production'?commandOutputsProd:commandOutputs3)[cmd]||'');
          render();
        });
      });
      controls.querySelectorAll('[data-sim-validation]').forEach((input) => {
        input.addEventListener('change',()=>{ const key=input.getAttribute('data-sim-validation'); if(!key)return; input.checked?state.validation.add(key):state.validation.delete(key); render(); });
      });
    };

    const reset = () => {
      state = { values: defaults(), commands:new Set(), validation:new Set(), predictionCorrect:null, conclusionCorrect:null, hints:0, verified:false };
      if (terminal) terminal.hidden=true;
      sim.querySelectorAll('[data-sim-predict],[data-sim-conclude]').forEach((b)=>b.classList.remove('correct','wrong','selected'));
      const pf=sim.querySelector('[data-sim-predict-feedback]'); if(pf) pf.hidden=true;
      const ff=sim.querySelector('[data-sim-final-feedback]'); if(ff) ff.hidden=true;
      sim.querySelectorAll('[data-sim-hint-item]').forEach((x)=>x.hidden=true);
      const hb=sim.querySelector('[data-sim-hint-box]'); if(hb) hb.hidden=true;
      const hintBtn=sim.querySelector('[data-sim-hint]'); if(hintBtn){ hintBtn.textContent='Zobrazit nápovědu 1'; hintBtn.disabled=false; }
      bindControls(); render(); setSimPhase(sim,'predict'); updateScore();
    };

    sim.querySelectorAll('[data-sim-predict]').forEach((button) => {
      button.addEventListener('click',()=>{
        const correct=Number(sim.querySelector('[data-sim-prediction]')?.getAttribute('data-correct')||0);
        const chosen=Number(button.getAttribute('data-sim-predict')); const ok=chosen===correct;
        if(state.predictionCorrect===null) state.predictionCorrect=ok;
        sim.querySelectorAll('[data-sim-predict]').forEach((b)=>b.classList.remove('correct','wrong','selected'));
        button.classList.add(ok?'correct':'wrong','selected');
        const fb=sim.querySelector('[data-sim-predict-feedback]'); if(fb){fb.hidden=false; fb.textContent=ok?'Dobrá predikce. Teď ji ověř experimentem.':'Tohle je hypotéza — experiment ti ukáže, proč neplatí.'; fb.className=`sim-inline-feedback ${ok?'ok':'bad'}`;}
        setSimPhase(sim,'experiment');
      });
    });

    const hintBtn=sim.querySelector('[data-sim-hint]');
    if(hintBtn) hintBtn.addEventListener('click',()=>{
      const items=Array.from(sim.querySelectorAll('[data-sim-hint-item]')); if(state.hints>=items.length)return;
      const box=sim.querySelector('[data-sim-hint-box]'); if(box)box.hidden=false;
      items[state.hints].hidden=false; state.hints+=1;
      hintBtn.textContent=state.hints>=items.length?'Všechny nápovědy zobrazeny':`Zobrazit nápovědu ${state.hints+1}`;
      hintBtn.disabled=state.hints>=items.length;
    });

    const checkState=sim.querySelector('[data-sim-check-state]');
    if(checkState) checkState.addEventListener('click',()=>{
      state.verified=true; const ok=taskOk(); render();
      if(status) status.textContent=ok?'Správné nastavení / dost evidence ✓':'Ještě chybí důležitá část';
      if(ok) setSimPhase(sim,'conclude');
    });

    sim.querySelectorAll('[data-sim-conclude]').forEach((button)=>{
      button.addEventListener('click',()=>{
        const correct=Number(sim.querySelector('[data-sim-conclusion]')?.getAttribute('data-correct')||0);
        const chosen=Number(button.getAttribute('data-sim-conclude')); const ok=chosen===correct;
        state.conclusionCorrect=ok;
        sim.querySelectorAll('[data-sim-conclude]').forEach((b)=>b.classList.remove('correct','wrong'));
        button.classList.add(ok?'correct':'wrong');
        const final=sim.querySelector('[data-sim-final-feedback]'); if(!final)return;
        final.hidden=false;
        if(!ok){ final.className='sim-final-feedback bad'; final.innerHTML='<strong>Ještě ne.</strong><p>Vrať se k evidence panelu: které tvrzení opravdu podporují naměřené výsledky?</p>'; return; }
        if(!taskOk()){ final.className='sim-final-feedback warn'; final.innerHTML='<strong>Závěr je správný, ale chybí důkaz.</strong><p>Dokonči experiment nebo potřebné diagnostické testy. V praxi nestačí mít pravdu náhodou.</p>'; return; }
        const raw=(state.predictionCorrect?1:0)+3+1; const cap=Math.max(1,maxPoints-Math.max(0,state.hints-1)); const score=Math.min(raw,cap);
        if(score>bestScore){bestScore=score;localStorage.setItem(savedKey,String(bestScore));}
        updateScore(); setSimPhase(sim,'conclude'); sim.classList.add('completed');
        final.className='sim-final-feedback ok'; final.innerHTML=`<strong>Hotovo · ${score}/${maxPoints}</strong><p>Dokázal jsi propojit nastavení, evidence a závěr. ${state.hints>1?'Další průchod zkus s méně nápovědami.':'Výborně — zkus stejný princip přenést do praktického labu.'}</p>`;
        sim.dispatchEvent(new CustomEvent('kb:simulation-complete', {bubbles:true, detail:{score, maxPoints, hints:state.hints}}));
      });
    });

    const resetBtn=sim.querySelector('[data-sim-reset]'); if(resetBtn) resetBtn.addEventListener('click',reset);
    reset();
  };

  document.querySelectorAll('[data-kb-sim]').forEach(initSimulation);


  // Graphics Design Studio ---------------------------------------------------
  const studio = document.querySelector('[data-design-studio]');
  if (studio) {
    const q = (sel) => studio.querySelector(sel);
    const qa = (sel) => Array.from(studio.querySelectorAll(sel));
    const inputs = qa('[data-ds]');
    const canvasEl = q('[data-ds-canvas]');
    const gridEl = q('[data-ds-grid]');
    const thumb = q('[data-ds-thumb]');
    const feedback = q('[data-ds-feedback]');
    const score = q('[data-ds-score]');
    const contrastEl = q('[data-ds-contrast]');
    const contrastNote = q('[data-ds-contrast-note]');
    const exportAdvice = q('[data-ds-export-advice]');
    const storageKey = `educanet_graphics_studio_${studio.getAttribute('data-ds-class') || 'graphics'}_v3`;

    const readState = () => {
      const state = {};
      inputs.forEach((input) => {
        const key = input.getAttribute('data-ds');
        if (!key) return;
        state[key] = input.type === 'checkbox' ? input.checked : input.value;
      });
      return state;
    };
    const setState = (state) => {
      inputs.forEach((input) => {
        const key=input.getAttribute('data-ds'); if(!key || state[key] === undefined) return;
        if(input.type==='checkbox') input.checked=Boolean(state[key]); else input.value=String(state[key]);
      });
    };
    try { const saved=JSON.parse(localStorage.getItem(storageKey)||'null'); if(saved) setState(saved); } catch(_) {}

    const posterDimensions = (format) => ({portrait:[1080,1350],square:[1080,1080],story:[1080,1920],a4:[1240,1754]})[format] || [1080,1350];
    const colorContrast = (a,b) => contrastRatio(a,b);
    const downloadBlob = (blob,name) => { const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download=name; document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(url),500); };

    const renderStudio = () => {
      const s = readState();
      localStorage.setItem(storageKey, JSON.stringify(s));
      qa('[data-ds-out]').forEach((el)=>{ const key=el.getAttribute('data-ds-out'); if(key && s[key]!==undefined) el.textContent=String(s[key]); });
      const title=q('[data-ds-title]'), info=q('[data-ds-info]'), cta=q('[data-ds-cta]');
      if(title) title.textContent=s.title || 'HEADLINE'; if(info) info.textContent=s.info || 'Datum · místo'; if(cta) cta.textContent=(s.cta || 'CTA')+' →';
      const [w,h]=posterDimensions(s.format); const aspect=`${w} / ${h}`;
      if(canvasEl){
        canvasEl.style.setProperty('--ds-bg',s.bg); canvasEl.style.setProperty('--ds-text',s.text); canvasEl.style.setProperty('--ds-accent',s.accent);
        canvasEl.style.setProperty('--ds-title',`${s.titleSize}px`); canvasEl.style.setProperty('--ds-info',`${s.infoSize}px`); canvasEl.style.setProperty('--ds-space',`${s.spacing}px`);
        canvasEl.style.aspectRatio=aspect; canvasEl.style.filter=`${s.gray?'grayscale(1) ':''}${s.blur?'blur(4px)':''}`.trim() || 'none';
      }
      if(gridEl){gridEl.hidden=!s.showGrid; gridEl.style.setProperty('--ds-cols',s.grid||4);}
      if(thumb){thumb.style.background=s.bg;thumb.style.color=s.text; const b=thumb.querySelector('b'),i=thumb.querySelector('i'),e=thumb.querySelector('em'); if(b)b.textContent=s.title||'';if(i)i.textContent=s.info||'';if(e){e.textContent=s.cta||'';e.style.color=s.accent;}}
      const cr=colorContrast(s.bg,s.text), ar=colorContrast(s.bg,s.accent); const hierarchy=Number(s.titleSize)>=Number(s.infoSize)*2; const spacing=Number(s.spacing)>=16; const mainPass=cr>=4.5; const accentPass=ar>=3;
      if(contrastEl) contrastEl.textContent=`${cr.toFixed(2)} : 1`;
      if(contrastNote){contrastNote.textContent=mainPass?'dobrý základ pro čitelnost':'zvyš rozdíl text / pozadí';contrastNote.className=mainPass?'pass':'fail';}
      const checks=[mainPass,hierarchy,spacing,accentPass]; if(score) score.textContent=`${checks.filter(Boolean).length} / 4 kontroly`;
      const tips=[];
      tips.push(mainPass?'✓ Hlavní text má použitelný orientační kontrast.':'• Hlavní text splývá s pozadím — změň text nebo background.');
      tips.push(hierarchy?'✓ Headline je jasně dominantní.':'• Zvětši rozdíl mezi headline a informačním textem.');
      tips.push(spacing?'✓ Spacing dává prvkům vzduch.':'• Přidej spacing; podobné prvky mohou být blízko, ale celý layout potřebuje rytmus.');
      tips.push(accentPass?'✓ Akcent je proti pozadí dostatečně rozpoznatelný.':'• CTA akcent splývá s pozadím — zkus světlejší/tmavší akcent.');
      if(feedback) feedback.innerHTML=tips.map((x)=>`<li class="${x.startsWith('✓')?'ok':'warn'}">${escapeHtml(x)}</li>`).join('');
      if(exportAdvice){
        const target=s.target, ft=s.filetype; let text='';
        if(target==='social') text=`Doporučení: RGB, přesný pixelový rozměr, ${ft==='pdf'?'pro sociální síť raději PNG/JPG/WebP než PDF':ft.toUpperCase()}, kontrola na mobilním náhledu.`;
        else if(target==='web') text=`Doporučení: RGB, optimalizovat datovou velikost; ${['webp','jpg'].includes(ft)?'zvolený formát dává pro web smysl':'zvaž WebP/JPG podle typu obrazu'}.`;
        else text=`Doporučení: zjisti specifikaci tiskárny, spadávku a požadovaný PDF profil. Design Studio negeneruje produkční tiskové PDF — preset je checklist pro Canvu.`;
        exportAdvice.innerHTML=`<span>${escapeHtml(target.toUpperCase())}</span><p>${escapeHtml(text)}</p><small>Kvalita: ${escapeHtml(s.quality)} % · zvolený soubor: ${escapeHtml(String(ft).toUpperCase())}</small>`;
      }
    };

    inputs.forEach((input)=>input.addEventListener('input',renderStudio));
    q('[data-ds-download-config]')?.addEventListener('click',()=>{
      const s=readState(); const [w,h]=posterDimensions(s.format);
      const payload={version:2,created_at:new Date().toISOString(),canvas:{preset:s.format,width:w,height:h,grid:Number(s.grid)},content:{headline:s.title,info:s.info,cta:s.cta},visual:{background:s.bg,text:s.text,accent:s.accent,title_size:Number(s.titleSize),info_size:Number(s.infoSize),spacing:Number(s.spacing),contrast:Number(colorContrast(s.bg,s.text).toFixed(2))},export:{target:s.target,filetype:s.filetype,quality:Number(s.quality)},canva_checklist:['Vytvoř dokument podle rozměru','Nastav vodítka/grid','Přenes poměr headline/info/CTA','Ověř kontrast a thumbnail','Doplň vlastní vizuální styl','Export otevři mimo editor']};
      downloadBlob(new Blob([JSON.stringify(payload,null,2)],{type:'application/json;charset=utf-8'}),'educanet-design-config.json');
    });
    q('[data-ds-copy]')?.addEventListener('click',async()=>{
      const s=readState(); const [w,h]=posterDimensions(s.format);
      const text=`CANVA PŘENOS\n• Dokument: ${w} × ${h}\n• Grid: ${s.grid} sloupců\n• Headline: ${s.title} · relativní velikost ${s.titleSize}\n• Info: ${s.info} · ${s.infoSize}\n• CTA: ${s.cta}\n• Barvy: BG ${s.bg} · text ${s.text} · akcent ${s.accent}\n• Spacing: ${s.spacing}\n• Export: ${String(s.target).toUpperCase()} / ${String(s.filetype).toUpperCase()} / kvalita ${s.quality}%\n• Kontrola: thumbnail, grayscale, kontrast, export mimo editor`;
      try { await navigator.clipboard.writeText(text); const b=q('[data-ds-copy]'); if(b){const old=b.textContent;b.textContent='Zkopírováno ✓';setTimeout(()=>b.textContent=old,1300);} } catch(_) { window.prompt('Zkopíruj checklist:',text); }
    });
    q('[data-ds-download-png]')?.addEventListener('click',()=>{
      const s=readState(); const [w,h]=posterDimensions(s.format); const scale=Math.min(1,1600/Math.max(w,h)); const cw=Math.round(w*scale), ch=Math.round(h*scale);
      const c=document.createElement('canvas'); c.width=cw;c.height=ch; const ctx=c.getContext('2d'); if(!ctx)return;
      ctx.fillStyle=s.bg;ctx.fillRect(0,0,cw,ch); const pad=Math.round(cw*.08); ctx.fillStyle=s.text;ctx.font=`700 ${Math.max(34,Math.round(cw*.09))}px Arial, sans-serif`;ctx.textBaseline='top';
      const words=String(s.title||'DESIGN NIGHT').toUpperCase().split(' '); const half=Math.ceil(words.length/2); ctx.fillText(words.slice(0,half).join(' '),pad,Math.round(ch*.18));ctx.fillText(words.slice(half).join(' '),pad,Math.round(ch*.18)+Math.round(cw*.105));
      ctx.font=`500 ${Math.max(18,Math.round(cw*.035))}px Arial, sans-serif`;ctx.fillText(String(s.info||''),pad,Math.round(ch*.48));
      ctx.fillStyle=s.accent;ctx.fillRect(pad,Math.round(ch*.63),Math.round(cw*.42),Math.round(ch*.075));ctx.fillStyle='#000';ctx.font=`700 ${Math.max(16,Math.round(cw*.028))}px Arial, sans-serif`;ctx.fillText(String(s.cta||'CTA').toUpperCase(),pad+Math.round(cw*.025),Math.round(ch*.648));
      ctx.fillStyle=s.text;ctx.globalAlpha=.55;ctx.font=`500 ${Math.max(12,Math.round(cw*.018))}px Arial, sans-serif`;ctx.fillText('STUDY PREVIEW · převeď do Canvy',pad,Math.round(ch*.88));ctx.globalAlpha=1;
      c.toBlob((blob)=>{if(blob)downloadBlob(blob,'educanet-poster-preview.png');},'image/png');
    });
    renderStudio();
  }

  updateKbProgress();

  const hash = window.location.hash;
  if (hash) {
    const el = document.querySelector(hash);
    if (el) setTimeout(() => el.scrollIntoView({behavior: 'smooth', block: 'start'}), 50);
  }
})();

// Canva handoff workflow ---------------------------------------------------
(() => {
  const root = document.querySelector('[data-canva-handoff]');
  const studio = document.querySelector('[data-design-studio]');
  if (!root || !studio) return;

  const q = (sel, base = root) => base.querySelector(sel);
  const qa = (sel, base = root) => Array.from(base.querySelectorAll(sel));
  const cls = studio.getAttribute('data-ds-class') || 'graphics';
  const progressKey = `educanet_canva_handoff_${cls}_v1`;
  const qaKey = `educanet_canva_qa_${cls}_v1`;
  const reflectionKey = `educanet_canva_reflection_${cls}_v1`;
  const posterDimensions = (format) => ({portrait:[1080,1350],square:[1080,1080],story:[1080,1920],a4:[1240,1754]})[format] || [1080,1350];
  const gcd = (a,b) => b ? gcd(b,a%b) : a;
  const ratioLabel = (w,h) => { const g=gcd(w,h); return `${Math.round(w/g)} : ${Math.round(h/g)}`; };
  const studioState = () => {
    const s = {};
    qa('[data-ds]', studio).forEach((el) => {
      const key=el.getAttribute('data-ds');
      if(!key) return;
      s[key] = el.type === 'checkbox' ? el.checked : el.value;
    });
    return s;
  };
  const rgb = (hex) => {
    const v=String(hex||'').replace('#','');
    if(!/^[0-9a-f]{6}$/i.test(v)) return [0,0,0];
    return [parseInt(v.slice(0,2),16),parseInt(v.slice(2,4),16),parseInt(v.slice(4,6),16)];
  };
  const lum = (hex) => {
    const vals=rgb(hex).map(v=>{const c=v/255;return c<=.03928?c/12.92:Math.pow((c+.055)/1.055,2.4);});
    return .2126*vals[0]+.7152*vals[1]+.0722*vals[2];
  };
  const contrast = (a,b) => { const A=lum(a),B=lum(b);return (Math.max(A,B)+.05)/(Math.min(A,B)+.05); };
  const setText = (sel, value) => { const el=q(sel); if(el) el.textContent=value; };
  const copyText = async (text, button) => {
    try { await navigator.clipboard.writeText(text); }
    catch (_) { window.prompt('Zkopíruj:', text); }
    if(button){const old=button.textContent;button.textContent='Zkopírováno ✓';setTimeout(()=>button.textContent=old,1100);}
  };

  // Tabs
  qa('[data-handoff-tab]').forEach(btn => btn.addEventListener('click', () => {
    const id=btn.getAttribute('data-handoff-tab');
    qa('[data-handoff-tab]').forEach(x=>x.classList.toggle('active',x===btn));
    qa('[data-handoff-panel]').forEach(x=>x.classList.toggle('active',x.getAttribute('data-handoff-panel')===id));
  }));

  // Step completion + persistence
  let completed={};
  try { completed=JSON.parse(localStorage.getItem(progressKey)||'{}')||{}; } catch(_) {}
  const renderProgress=()=>{
    const done=Object.values(completed).filter(Boolean).length;
    setText('[data-handoff-progress]',`${done} / 7`);
    const bar=q('[data-handoff-progressbar]'); if(bar) bar.style.width=`${done/7*100}%`;
    qa('[data-handoff-done]').forEach(ch=>{
      const id=ch.getAttribute('data-handoff-done'); ch.checked=Boolean(completed[id]);
      q(`[data-handoff-tab="${id}"]`)?.classList.toggle('done',Boolean(completed[id]));
    });
  };
  qa('[data-handoff-done]').forEach(ch=>ch.addEventListener('change',()=>{
    completed[ch.getAttribute('data-handoff-done')]=ch.checked;
    localStorage.setItem(progressKey,JSON.stringify(completed));renderProgress();
  }));

  // QA checklist
  let qaState={};
  try { qaState=JSON.parse(localStorage.getItem(qaKey)||'{}')||{}; } catch(_) {}
  qa('[data-ho-check]').forEach(ch=>{
    const id=ch.getAttribute('data-ho-check'); ch.checked=Boolean(qaState[id]);
    ch.addEventListener('change',()=>{qaState[id]=ch.checked;localStorage.setItem(qaKey,JSON.stringify(qaState));renderQA();});
  });
  const renderQA=()=>{
    const total=qa('[data-ho-check]').length, done=qa('[data-ho-check]').filter(x=>x.checked).length;
    setText('[data-ho-qa-count]',`${done} / ${total}`);
    setText('[data-ho-qa-message]',done===total?'Visual QA je kompletní. Teď porovnej finální export s plánem.':done>=7?'Dobré. Dokonči poslední kontroly před exportem.':'Začni čitelností a hierarchií, potom řeš detaily.');
    setText('[data-ho-check-score]',`${done}/${total}`);
    setText('[data-ho-check-note]',done===total?'Kontrolní checklist je kompletní.':'Dokonči checklist; není to automatické hodnocení estetiky.');
  };

  // Reflection persistence
  let reflection={};
  try { reflection=JSON.parse(localStorage.getItem(reflectionKey)||'{}')||{}; } catch(_) {}
  const rg=q('[data-ho-reflect-good]'), rf=q('[data-ho-reflect-fix]');
  if(rg) rg.value=reflection.good||''; if(rf) rf.value=reflection.fix||'';
  [rg,rf].filter(Boolean).forEach(el=>el.addEventListener('input',()=>{
    reflection={good:rg?.value||'',fix:rf?.value||''};localStorage.setItem(reflectionKey,JSON.stringify(reflection));
  }));

  let uploadedMeta=null;
  const renderHandoff=()=>{
    const s=studioState(); const [w,h]=posterDimensions(s.format); const safe=Math.round(w*.08); const cr=contrast(s.bg,s.text);
    setText('[data-ho-dimensions]',`${w} × ${h} px`); setText('[data-ho-size-label]',`${w} × ${h}`); setText('[data-ho-ratio]',ratioLabel(w,h)); setText('[data-ho-safezone]',`cca ${safe} px`);
    setText('[data-ho-dimension-note]',s.format==='a4'?'Didaktický A4 náhled; pro skutečný tisk ověř rozměr, spadávku a požadavky tiskárny.':s.format==='story'?'Vertikální formát pro story/reels cover.':s.format==='square'?'Čtvercový digitální formát.':'Digitální post na výšku.');
    const sheet=q('[data-ho-size-sheet]'); if(sheet) sheet.style.aspectRatio=`${w}/${h}`;
    const cols=Number(s.grid||4); setText('[data-ho-grid]',`${cols} sloupce`); setText('[data-ho-margin]','8 % šířky'); setText('[data-ho-gutter]',cols>=8?'cca 1,5–2 %':'cca 2–3 %');
    const gs=q('[data-ho-grid-sheet]'); if(gs) gs.style.setProperty('--cols',String(cols));
    setText('[data-ho-title-preview]',s.title||'HEADLINE'); setText('[data-ho-info-preview]',s.info||'Datum · místo'); setText('[data-ho-cta-preview]',s.cta||'CTA');
    setText('[data-ho-title-size]',String(s.titleSize)); setText('[data-ho-info-size]',String(s.infoSize));
    const tb=q('[data-ho-title-bar]'), ib=q('[data-ho-info-bar]'); if(tb) tb.style.width='100%'; if(ib) ib.style.width=`${Math.min(100,Number(s.infoSize)/Math.max(1,Number(s.titleSize))*100)}%`;
    const tp=q('[data-ho-type-preview]'); if(tp){tp.style.background=s.bg;tp.style.color=s.text;const em=tp.querySelector('em');if(em){em.style.background=s.accent;em.style.color=contrast(s.accent,'#111111')>=contrast(s.accent,'#ffffff')?'#111':'#fff';}}
    [['bg',s.bg],['text',s.text],['accent',s.accent]].forEach(([key,val])=>{const sw=q(`[data-ho-${key}]`);if(sw)sw.style.background=val;setText(`[data-ho-${key}-code]`,String(val).toUpperCase());});
    setText('[data-ho-contrast]',`${cr.toFixed(2)} : 1`); setText('[data-ho-contrast-state]',cr>=4.5?'dobrý základ pro běžný text':cr>=3?'spíš jen pro velký text':'nedostatečný — uprav paletu');
    const cs=q('[data-ho-contrast-sample]');if(cs){cs.style.background=s.bg;cs.style.color=s.text;}
    setText('[data-ho-export-target]',String(s.target||'social').toUpperCase()); setText('[data-ho-export-type]',String(s.filetype||'png').toUpperCase()); setText('[data-ho-export-quality]',`${s.quality||85} %`);
    let extra='RGB · kontrola na mobilním náhledu', warning='Digitální export: zkontroluj miniaturu, ořez a čitelnost na telefonu.';
    if(s.target==='web'){extra='RGB · optimalizace datové velikosti';warning='Web: WebP/JPG bývá efektivnější pro fotografie; PNG používej, když potřebuješ ostrost grafiky nebo transparenci.';}
    if(s.target==='print'){extra='tisk · ověř spadávku a specifikaci tiskárny';warning='Tisk: Design Studio je výukový preset. Před produkčním tiskem vždy ověř CMYK workflow, spadávku, PDF profil a požadavky konkrétní tiskárny.';}
    setText('[data-ho-export-extra]',extra);setText('[data-ho-export-warning]',warning);
    const planned=q('[data-ho-planned]'); if(planned){planned.style.aspectRatio=`${w}/${h}`;planned.style.background=s.bg;planned.style.color=s.text;const em=planned.querySelector('em');if(em){em.style.background=s.accent;em.style.color=contrast(s.accent,'#111')>=contrast(s.accent,'#fff')?'#111':'#fff';}}
    setText('[data-ho-compare-title]',s.title||'HEADLINE');setText('[data-ho-compare-info]',s.info||'Datum · místo');setText('[data-ho-compare-cta]',s.cta||'CTA');
    if(uploadedMeta) updateCompareScores(uploadedMeta);
  };

  qa('[data-ds]', studio).forEach(el=>el.addEventListener('input',renderHandoff));
  qa('[data-copy-color]').forEach(btn=>btn.addEventListener('click',()=>{const key=btn.getAttribute('data-copy-color');const s=studioState();copyText(String(s[key]||''),btn);}));
  q('[data-copy-step="dimensions"]')?.addEventListener('click',()=>{const s=studioState();const [w,h]=posterDimensions(s.format);copyText(`Canva dokument: ${w} × ${h} px · bezpečná zóna cca ${Math.round(w*.08)} px · grid ${s.grid} sloupců`,q('[data-copy-step="dimensions"]'));});

  const dist=(a,b)=>Math.sqrt((a[0]-b[0])**2+(a[1]-b[1])**2+(a[2]-b[2])**2);
  const samplePalette=(img, targets)=>{
    const c=document.createElement('canvas');c.width=64;c.height=64;const ctx=c.getContext('2d',{willReadFrequently:true});if(!ctx)return [];
    ctx.drawImage(img,0,0,64,64);const d=ctx.getImageData(0,0,64,64).data;const targetRgb=targets.map(rgb);const hits=targetRgb.map(()=>0);let n=0;
    for(let i=0;i<d.length;i+=16){const px=[d[i],d[i+1],d[i+2]];n++;targetRgb.forEach((t,idx)=>{if(dist(px,t)<=58)hits[idx]++;});}
    return hits.map(h=>h/Math.max(1,n));
  };
  const updateCompareScores=(meta)=>{
    const s=studioState();const [w,h]=posterDimensions(s.format);const expected=w/h, actual=meta.width/meta.height;const delta=Math.abs(expected-actual)/expected;const aspectPass=delta<.018;
    setText('[data-ho-aspect-score]',aspectPass?'✓ shoda':'△ odlišné');setText('[data-ho-aspect-note]',`${meta.width} × ${meta.height} px · očekáváno ${w} × ${h} · ${aspectPass?'poměr stran odpovídá':'zkontroluj, zda změna formátu byla záměrná'}`);
    const ratios=meta.paletteRatios||[];const found=ratios.map(x=>x>.002);const count=found.filter(Boolean).length;
    setText('[data-ho-palette-score]',`${count}/3 barvy`);setText('[data-ho-palette-note]',count>=2?'Orientační sampling našel většinu plánované palety.':'Paleta se proti plánu výrazně změnila nebo jsou barvy jen na malé ploše — zkontroluj změnu ručně.');
    renderQA();
  };
  q('[data-ho-canva-file]')?.addEventListener('change',(ev)=>{
    const file=ev.target.files?.[0];if(!file)return;
    if(file.size>12*1024*1024){alert('Soubor je větší než 12 MB. Pro srovnání použij menší export.');ev.target.value='';return;}
    const url=URL.createObjectURL(file);const img=new Image();
    img.onload=()=>{
      const preview=q('[data-ho-canva-preview]');if(preview)preview.src=url;
      q('[data-ho-compare-stage]')?.removeAttribute('hidden');q('[data-ho-compare-results]')?.removeAttribute('hidden');
      const s=studioState();const ratios=samplePalette(img,[s.bg,s.text,s.accent]);uploadedMeta={width:img.naturalWidth,height:img.naturalHeight,paletteRatios:ratios};
      const pct=ratios.map(x=>`${Math.round(x*1000)/10}%`);
      const meta=q('[data-ho-file-meta]');if(meta)meta.innerHTML=`<div><strong>${file.name.replace(/[<>]/g,'')}</strong><br><span>${img.naturalWidth} × ${img.naturalHeight} px · ${(file.size/1024/1024).toFixed(2)} MB</span><br><small>Orientační výskyt plánovaných barev: pozadí ${pct[0]}, text ${pct[1]}, akcent ${pct[2]}.</small></div>`;
      updateCompareScores(uploadedMeta);
    };
    img.onerror=()=>{URL.revokeObjectURL(url);alert('Náhled se nepodařilo načíst. Použij PNG, JPG nebo WebP.');};
    img.src=url;
  });

  renderProgress();renderQA();renderHandoff();
})();

// Sequential learning journey + XP/badges ----------------------------------
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const toastRoot = (() => {
    let el = document.querySelector('[data-learning-toasts]');
    if (!el) {
      el = document.createElement('div');
      el.className = 'learning-toasts';
      el.setAttribute('data-learning-toasts', '');
      document.body.appendChild(el);
    }
    return el;
  })();

  const clean = (value) => String(value ?? '').replace(/[<>]/g, '');
  const toast = (title, text = '', kind = 'xp', meta = '') => {
    const card = document.createElement('div');
    card.className = `learning-toast ${kind}`;
    card.setAttribute('role', 'status');
    card.setAttribute('aria-live', 'polite');
    const icon = kind === 'badge' ? '◆' : (kind === 'achievement' ? '★' : (kind === 'error' ? '!' : 'XP'));
    card.innerHTML = `<i>${icon}</i><div class="toast-copy"><small>${kind === 'badge' ? 'VZÁCNÝ BADGE' : (kind === 'achievement' ? 'ACHIEVEMENT' : (kind === 'error' ? 'POZOR' : 'XP PŘIPSÁNO'))}</small><strong>${clean(title)}</strong>${text ? `<span>${clean(text)}</span>` : ''}${meta ? `<em>${clean(meta)}</em>` : ''}</div><b class="toast-timer" aria-hidden="true"></b>`;
    toastRoot.appendChild(card);
    requestAnimationFrame(() => card.classList.add('show'));
    setTimeout(() => { card.classList.remove('show'); setTimeout(() => card.remove(), 320); }, kind === 'badge' ? 5200 : (kind === 'achievement' ? 4000 : 3400));
  };
  window.toast = toast;

  const renderHud = (state, newBadges = [], awarded = 0, awardedEvents = [], newAchievements = []) => {
    if (!state) return;
    const hud = document.querySelector('[data-learning-hud]');
    if (hud) {
      const xp = hud.querySelector('[data-hud-xp]');
      const lvl = hud.querySelector('[data-hud-level]');
      const next = hud.querySelector('[data-hud-next]');
      const bar = hud.querySelector('[data-hud-bar]');
      const count = hud.querySelector('[data-hud-badge-count]');
      if (xp) xp.textContent = String(state.xp || 0);
      if (lvl) lvl.textContent = String(state.level?.level || 1);
      if (next) next.textContent = `${state.level?.current || 0} / ${state.level?.next || 250}`;
      if (bar) bar.style.width = `${state.level?.percent || 0}%`;
      if (count) count.textContent = String((state.badges || []).length);
      const list = hud.querySelector('[data-badge-list]');
      if (list) {
        const defs = state.badge_definitions || {};
        const badges = state.badges || [];
        list.innerHTML = badges.length ? badges.map(id => {
          const b = defs[id] || {mark:'◆', title:id, text:''};
          return `<div class="mini-badge"><i>${String(b.mark).replace(/[<>]/g,'')}</i><div><strong>${String(b.title).replace(/[<>]/g,'')}</strong><span>${String(b.text).replace(/[<>]/g,'')}</span></div></div>`;
        }).join('') : '<p class="badge-empty">Badge jsou vzácné trofeje. První můžeš získat za level 10 nebo mimořádný výkon.</p>';
      }
    }
    const kbOverall = document.querySelector('[data-kb-overall]');
    if (kbOverall && state.kb) {
      const cards = Array.from(document.querySelectorAll('[data-kb-progress-card]'));
      let done = 0;
      cards.forEach(card => {
        const topic = card.getAttribute('data-kb-topic-card') || '';
        const complete = Boolean(state.kb?.[topic]?.complete);
        if (complete) done += 1;
        card.classList.toggle('completed', complete);
        const status = card.querySelector('[data-kb-topic-status]');
        if (status && complete) status.textContent = '✓';
      });
      const total = cards.length || Number(kbOverall.getAttribute('data-total')) || 1;
      const doneEl = kbOverall.querySelector('[data-kb-done]');
      const bar = kbOverall.querySelector('[data-kb-progress-bar]');
      if (doneEl) doneEl.textContent = String(done);
      if (bar) bar.style.width = `${Math.round((done / total) * 100)}%`;
    }
    const events = Array.isArray(awardedEvents) ? awardedEvents : [];
    if (events.length) {
      events.forEach((event, index) => setTimeout(() => toast(`+${Number(event.xp || 0)} XP`, event.label || 'Krok dokončen', 'xp', event.detail || ''), index * 520));
    } else if (awarded > 0) {
      toast(`+${awarded} XP`, 'Postup byl automaticky uložen.', 'xp');
    }
    const defs = state.badge_definitions || {};
    const badgeDelay = Math.max(0, events.length * 520);
    (newBadges || []).forEach((id, index) => {
      const b = defs[id] || {title:'Nový badge', text:''};
      setTimeout(() => toast(b.title, b.text || 'Nový milník odemčen.', 'badge'), badgeDelay + index * 650);
    });
    const achievementDefs = state.achievement_definitions || {};
    const achievementDelay = badgeDelay + (newBadges || []).length * 650;
    (newAchievements || []).forEach((id, index) => {
      const a = achievementDefs[id] || {title:'Nový achievement', text:''};
      setTimeout(() => toast(a.title, a.text || 'Nový průběžný milník.', 'achievement'), achievementDelay + index * 520);
    });
  };

  const api = async (payload) => {
    const body = new URLSearchParams();
    body.set('csrf', csrf);
    Object.entries(payload).forEach(([k,v]) => body.set(k, String(v)));
    const res = await fetch('progress.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body});
    const data = await res.json().catch(() => ({ok:false,error:'Neplatná odpověď serveru.'}));
    if (!res.ok || !data.ok) throw new Error(data.error || 'Postup se nepodařilo uložit.');
    renderHud(data.state, data.new_badges || [], Number(data.awarded_xp || 0), data.awarded_events || [], data.new_achievements || []);
    return data;
  };

  const fetchState = async () => {
    try {
      const res = await fetch('progress.php', {headers:{'Accept':'application/json'}});
      const data = await res.json();
      if (data.ok) { renderHud(data.state); return data.state; }
    } catch (_) {}
    return null;
  };

  window.addEventListener('learning:state', (event) => {
    const data = event.detail || {};
    if (data.state) renderHud(data.state, data.new_badges || [], Number(data.awarded_xp || 0), data.awarded_events || [], data.new_achievements || []);
  });

  // Knowledge Tour: locked, sequential, auto-completing.
  document.querySelectorAll('[data-kb-tour]').forEach((tour) => {
    const topic = tour.getAttribute('data-topic') || '';
    const kbClass = tour.getAttribute('data-kb-class') || '';
    let progress = {};
    try { progress = JSON.parse(tour.getAttribute('data-kb-initial') || '{}') || {}; } catch (_) {}
    const hasSim = Boolean(tour.querySelector('[data-kb-panel="simulate"]'));
    const order = hasSim ? ['visual','simulate','steps','deep'] : ['visual','steps','deep'];
    const keyMap = {visual:'visual', simulate:'simulation', steps:'steps', deep:'deep'};

    const openPanel = (name) => {
      const btn = tour.querySelector(`[data-kb-tab="${name}"]`);
      if (!btn || btn.disabled) return;
      btn.click();
      btn.scrollIntoView({behavior:'smooth', block:'nearest', inline:'nearest'});
    };

    const sync = () => {
      let previousDone = true;
      order.forEach((name, i) => {
        const btn = tour.querySelector(`[data-kb-tab="${name}"]`);
        const done = Boolean(progress[keyMap[name]]);
        if (btn) {
          btn.disabled = i > 0 && !previousDone;
          btn.classList.toggle('done', done);
          btn.classList.toggle('locked', btn.disabled);
        }
        previousDone = previousDone && done;
      });
      const card = tour.querySelector('[data-kb-complete-card]');
      if (card) {
        const complete = Boolean(progress.complete);
        card.classList.toggle('completed', complete);
        const icon = card.querySelector(':scope > div > span');
        const title = card.querySelector('[data-kb-complete-title]');
        const copy = card.querySelector('[data-kb-complete-copy]');
        if (icon) icon.textContent = complete ? '✓' : '○';
        if (title) title.textContent = complete ? 'Lekce dokončena' : 'Dokonči všechny kroky';
        if (copy) copy.textContent = complete ? 'Všechny povinné kroky jsou splněné. Další lekce je odemčená.' : 'Další lekce se odemkne automaticky až po správném mini-checku.';
      }
      const quizLink = tour.querySelector('[data-kb-quiz-link]');
      const quizLaunch = tour.querySelector('[data-kb-quiz-launch]');
      if (quizLaunch) {
        quizLaunch.classList.toggle('ready', Boolean(progress.deep));
        quizLaunch.classList.toggle('locked', !progress.deep);
      }
      if (quizLink && progress.deep && quizLink.tagName === 'BUTTON') {
        const url = quizLink.getAttribute('data-quiz-url');
        quizLink.disabled = false;
        quizLink.textContent = 'Spustit knowledge check →';
        if (url) quizLink.addEventListener('click', () => { window.location.href = url; }, {once:true});
      }
      const next = document.querySelector('[data-kb-next-link]');
      if (next && progress.complete) {
        const url = next.getAttribute('data-next-url');
        if (next.tagName === 'BUTTON' && url) {
          next.disabled = false;
          next.textContent = 'Pokračovat na další lekci →';
          next.addEventListener('click', () => { window.location.href = url; }, {once:true});
        }
      }
    };

    const completeStep = async (step, extra = {}) => {
      try {
        const data = step === 'check'
          ? await api({action:'kb_check', topic, answer:extra.answer, kb_class:kbClass})
          : await api({action:'kb_step', topic, step, kb_class:kbClass, ...(extra || {})});
        if (data.topic_progress) progress = Object.assign(progress, data.topic_progress);
        sync();
        if (progress.complete) {
          setTimeout(() => document.querySelector('[data-kb-next-link]')?.scrollIntoView({behavior:'smooth', block:'center'}), 350);
          return data;
        }
        const currentName = order.find(name => !progress[keyMap[name]]);
        if (currentName) setTimeout(() => openPanel(currentName), 180);
        return data;
      } catch (err) {
        toast('Postup se neuložil', err.message || 'Zkus to znovu.', 'error');
        return null;
      }
    };

    const setAutoStatus = (step, mode = 'active') => {
      const node = tour.querySelector(`[data-auto-step-status="${step}"]`);
      if (!node) return;
      node.classList.toggle('done', mode === 'done');
      node.classList.toggle('counting', mode === 'counting');
      const icon = node.querySelector(':scope > i');
      const title = node.querySelector('strong');
      const copy = node.querySelector('span');
      if (mode === 'done') {
        if (icon) icon.textContent = '✓';
        if (title) title.textContent = step === 'deep' ? 'Vysvětlení uložené' : 'Vizuální krok uložen';
        if (copy) copy.textContent = '+5 XP už je započítáno.';
      } else if (mode === 'counting') {
        if (icon) icon.textContent = '◔';
        if (title) title.textContent = 'Čtu aktivní část…';
        if (copy) copy.textContent = 'Po krátkém soustředěném přečtení se krok uloží sám.';
      }
    };

    tour.addEventListener('kb:visual-complete', () => {
      if (!progress.visual) completeStep('visual').then((data) => { if (data && progress.visual) setAutoStatus('visual', 'done'); });
    });

    let deepTimer = null;
    const armDeepAutoComplete = () => {
      if (progress.deep || deepTimer) return;
      const panel = tour.querySelector('[data-kb-panel="deep"]');
      if (!panel || panel.hidden || document.hidden) return;
      setAutoStatus('deep', 'counting');
      deepTimer = window.setTimeout(() => {
        deepTimer = null;
        if (!progress.deep && !panel.hidden && !document.hidden) completeStep('deep').then((data) => { if (data && progress.deep) setAutoStatus('deep', 'done'); });
      }, 5200);
    };
    tour.querySelector('[data-kb-tab="deep"]')?.addEventListener('click', () => setTimeout(armDeepAutoComplete, 60));
    document.addEventListener('visibilitychange', () => {
      if (document.hidden && deepTimer) { clearTimeout(deepTimer); deepTimer = null; }
      else armDeepAutoComplete();
    });
    if (!progress.visual) setAutoStatus('visual', 'active'); else setAutoStatus('visual', 'done');
    if (progress.deep) setAutoStatus('deep', 'done');

    tour.addEventListener('kb:simulation-complete', (ev) => {
      if (!progress.simulation) completeStep('simulation', ev.detail || {});
    });
    tour.addEventListener('kb:steps-complete', () => {
      if (!progress.steps) completeStep('steps');
      else openPanel('deep');
    });
    // Stepper dots stay sequential even on client side.
    tour.querySelectorAll('[data-kb-stepper]').forEach(stepper => {
      const dots = Array.from(stepper.querySelectorAll('[data-kb-step-dot]'));
      const next = stepper.querySelector('.kb-step-next');
      const unlockCurrent = () => {
        const slides = Array.from(stepper.querySelectorAll('[data-kb-step]'));
        const active = Math.max(0, slides.findIndex(x => x.classList.contains('active')));
        dots.forEach((dot, i) => { dot.disabled = !progress.steps && i > active; });
      };
      next?.addEventListener('click', () => setTimeout(unlockCurrent, 0));
      unlockCurrent();
    });
    sync();
  });


  // Knowledgebase catalog: instant search + class/topic/priority/status filters.
  document.querySelectorAll('[data-kb-catalog]').forEach((catalog) => {
    const search = catalog.querySelector('[data-kb-search]');
    const filters = Array.from(catalog.querySelectorAll('[data-kb-filter]'));
    const cards = Array.from(catalog.querySelectorAll('[data-kb-topic-card]'));
    const count = catalog.querySelector('[data-kb-result-count]');
    const empty = catalog.querySelector('[data-kb-empty]');
    const normalize = (value) => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    const values = () => Object.fromEntries(filters.map(el => [el.getAttribute('data-kb-filter'), el.value]));
    const apply = () => {
      const q = normalize(search?.value || '');
      const f = values();
      let visible = 0;
      cards.forEach((card) => {
        const hay = normalize(card.getAttribute('data-kb-search-text') || card.textContent || '');
        const matches = (!q || hay.includes(q))
          && (!f.class || f.class === 'all' || card.getAttribute('data-kb-card-class') === f.class)
          && (!f.topic || f.topic === 'all' || card.getAttribute('data-kb-card-topic') === f.topic)
          && (!f.priority || f.priority === 'all' || card.getAttribute('data-kb-card-priority') === f.priority)
          && (!f.status || f.status === 'all' || card.getAttribute('data-kb-card-status') === f.status);
        card.hidden = !matches;
        if (matches) visible += 1;
      });
      if (count) count.textContent = String(visible);
      if (empty) empty.hidden = visible !== 0;
      catalog.classList.toggle('has-active-filters', Boolean(q || Object.values(f).some(v => v && v !== 'all')));
    };
    const clear = () => {
      if (search) search.value = '';
      filters.forEach((el) => { el.value = 'all'; });
      apply();
      search?.focus();
    };
    search?.addEventListener('input', apply);
    filters.forEach(el => el.addEventListener('change', apply));
    catalog.querySelectorAll('[data-kb-clear]').forEach(btn => btn.addEventListener('click', clear));
    document.addEventListener('keydown', (ev) => {
      if ((ev.metaKey || ev.ctrlKey) && ev.key.toLowerCase() === 'k' && search) {
        ev.preventDefault(); search.focus(); search.select();
      }
    });
    apply();
  });

  // Design Studio + Canva handoff: strict sequential gates.
  const studio = document.querySelector('[data-design-studio]');
  const handoff = document.querySelector('[data-canva-handoff]');
  if (studio && handoff) {
    let serverStudio = {};
    const classId = studio.getAttribute('data-ds-class') || '';
    const required = ['brief','visual','export','dimensions','grid','type','palette','handoff_export','check','compare'];
    const handoffIds = ['dimensions','grid','type','palette','export','check','compare'];
    const serverKey = id => id === 'export' ? 'handoff_export' : id;
    const stageEls = {
      brief: studio.querySelector('[data-studio-stage="brief"]'),
      visual: studio.querySelector('[data-studio-stage="visual"]'),
      export: studio.querySelector('[data-studio-stage="export"]'),
      handoff,
    };
    const setLocked = (el, locked) => {
      if (!el) return;
      el.classList.toggle('is-locked', locked);
      el.setAttribute('aria-disabled', locked ? 'true' : 'false');
      el.querySelectorAll('input,select,textarea,button').forEach(control => {
        if (control.matches('[data-studio-commit]')) return;
        if (locked) control.setAttribute('data-gate-disabled','1');
        if (locked) control.disabled = true;
        else if (control.getAttribute('data-gate-disabled') === '1') { control.disabled = false; control.removeAttribute('data-gate-disabled'); }
      });
    };
    const updateStudioGates = () => {
      setLocked(stageEls.brief, false);
      setLocked(stageEls.visual, !serverStudio.brief);
      setLocked(stageEls.export, !serverStudio.visual);
      setLocked(stageEls.handoff, !serverStudio.export);
      studio.querySelectorAll('[data-studio-commit]').forEach(btn => {
        const step = btn.getAttribute('data-studio-commit');
        const done = Boolean(serverStudio[step]);
        btn.disabled = done || (step === 'visual' && !serverStudio.brief) || (step === 'export' && !serverStudio.visual);
        if (done) btn.textContent = 'Splněno ✓';
      });
      handoffIds.forEach((id, idx) => {
        const key = serverKey(id);
        const tab = handoff.querySelector(`[data-handoff-tab="${id}"]`);
        const check = handoff.querySelector(`[data-handoff-done="${id}"]`);
        const prevKey = idx === 0 ? 'export' : serverKey(handoffIds[idx-1]);
        const unlocked = Boolean(serverStudio[prevKey]);
        const done = Boolean(serverStudio[key]);
        if (tab) { tab.disabled = !unlocked; tab.classList.toggle('done', done); tab.classList.toggle('locked', !unlocked); }
        if (check) { check.checked = done; check.disabled = done || !unlocked; }
      });
      const handoffDone = handoffIds.filter(id => Boolean(serverStudio[serverKey(id)])).length;
      const handoffProgress = handoff.querySelector('[data-handoff-progress]');
      const handoffBar = handoff.querySelector('[data-handoff-progressbar]');
      if (handoffProgress) handoffProgress.textContent = `${handoffDone} / 7`;
      if (handoffBar) handoffBar.style.width = `${Math.round(handoffDone / 7 * 100)}%`;
      const finish = document.querySelector('[data-studio-finish-link]');
      if (finish) {
        const all = required.every(k => serverStudio[k]);
        finish.classList.toggle('disabled', !all);
        finish.setAttribute('aria-disabled', all ? 'false' : 'true');
        if (!all) finish.setAttribute('data-locked-link','1'); else finish.removeAttribute('data-locked-link');
      }
    };

    const completeStudioStep = async (step) => {
      try {
        const data = await api({action:'studio_step', step});
        serverStudio = Object.assign({}, data.state?.studio || serverStudio);
        updateStudioGates();
        return true;
      } catch (err) {
        toast('Krok je zatím zamčený', err.message || 'Dokonči předchozí část.', 'error');
        return false;
      }
    };

    studio.querySelectorAll('[data-studio-commit]').forEach(btn => btn.addEventListener('click', async () => {
      const step = btn.getAttribute('data-studio-commit');
      if (!step) return;
      if (step === 'brief') {
        const fields = ['title','info','cta'].map(k => studio.querySelector(`[data-ds="${k}"]`)?.value.trim() || '');
        if (fields.some(x => x.length < 3)) { toast('Brief není hotový', 'Doplň headline, klíčovou informaci a CTA.', 'error'); return; }
      }
      if (step === 'visual') {
        const score = studio.querySelector('[data-ds-score]')?.textContent || '';
        const gray = studio.querySelector('[data-ds="gray"]');
        const blur = studio.querySelector('[data-ds="blur"]');
        if (!score.includes('4 / 4')) { toast('Ještě uprav vizuál', 'Design coach musí hlásit 4 / 4 kontroly.', 'error'); return; }
        if (!gray?.checked || !blur?.checked) { toast('Proveď oba vizuální testy', 'Zapni grayscale i 3sekundový blur test alespoň pro finální kontrolu.', 'error'); return; }
      }
      const ok = await completeStudioStep(step);
      if (ok) {
        if (step === 'brief') stageEls.visual?.scrollIntoView({behavior:'smooth',block:'center'});
        if (step === 'visual') {
          ['gray','blur'].forEach(key => { const el=studio.querySelector(`[data-ds="${key}"]`); if(el && el.checked){el.checked=false;el.dispatchEvent(new Event('input',{bubbles:true}));} });
          stageEls.export?.scrollIntoView({behavior:'smooth',block:'center'});
        }
        if (step === 'export') handoff.scrollIntoView({behavior:'smooth',block:'start'});
      }
    }));

    // Handoff tabs: a future tab never opens until previous server step is done.
    handoff.querySelectorAll('[data-handoff-tab]').forEach(tab => {
      tab.addEventListener('click', (ev) => {
        if (tab.disabled || tab.classList.contains('locked')) { ev.preventDefault(); ev.stopImmediatePropagation(); toast('Nejdřív dokonči předchozí krok', 'Postup je záměrně sekvenční.', 'error'); }
      }, true);
    });

    handoff.querySelectorAll('[data-handoff-done]').forEach(check => {
      check.addEventListener('change', async (ev) => {
        const id = check.getAttribute('data-handoff-done') || '';
        if (!check.checked) { check.checked = true; return; }
        if (id === 'check') {
          const qa = Array.from(handoff.querySelectorAll('[data-ho-check]'));
          if (!qa.length || qa.some(x => !x.checked)) {
            check.checked = false;
            toast('Checklist není kompletní', 'Projdi všech 10 kontrolních bodů.', 'error');
            return;
          }
        }
        if (id === 'compare') {
          const imageReady = !handoff.querySelector('[data-ho-compare-stage]')?.hasAttribute('hidden');
          const good = handoff.querySelector('[data-ho-reflect-good]')?.value.trim() || '';
          const fix = handoff.querySelector('[data-ho-reflect-fix]')?.value.trim() || '';
          if (!imageReady || good.length < 12 || fix.length < 12) {
            check.checked = false;
            toast('Porovnání ještě není hotové', 'Nahraj Canva export a doplň obě krátké reflexe.', 'error');
            return;
          }
        }
        const ok = await completeStudioStep(serverKey(id));
        if (!ok) { check.checked = false; return; }
        const idx = handoffIds.indexOf(id);
        const nextId = handoffIds[idx+1];
        if (nextId) {
          const nextTab = handoff.querySelector(`[data-handoff-tab="${nextId}"]`);
          nextTab?.click();
          nextTab?.scrollIntoView({behavior:'smooth',block:'nearest'});
        } else {
          toast('Design Studio dokončeno', 'Můžeš pokračovat na finální zadání v Canvě.', 'badge');
          document.querySelector('[data-studio-finish-link]')?.scrollIntoView({behavior:'smooth',block:'center'});
        }
      }, true);
    });

    document.querySelector('[data-studio-finish-link]')?.addEventListener('click', (ev) => {
      if (ev.currentTarget.getAttribute('data-locked-link') === '1') {
        ev.preventDefault();
        toast('Ještě nejsi na konci', 'Dokonči všechny kroky Design Studia a Canva handoffu.', 'error');
      }
    });

    fetchState().then(state => {
      serverStudio = Object.assign({}, state?.studio || {});
      updateStudioGates();
      const firstIncomplete = required.find(k => !serverStudio[k]);
      if (firstIncomplete && handoffIds.includes(firstIncomplete === 'handoff_export' ? 'export' : firstIncomplete)) {
        const id = firstIncomplete === 'handoff_export' ? 'export' : firstIncomplete;
        handoff.querySelector(`[data-handoff-tab="${id}"]`)?.click();
      }
    });
  } else {
    fetchState();
  }
})();


// Knowledgebase access guard: KB is available before/after the test, never during it.
(() => {
  if (!document.querySelector('[data-kb-catalog], [data-kb-tour], [data-kb-quiz]')) return;
  const check = async () => {
    try {
      const res = await fetch('progress.php', {headers:{'Accept':'application/json'}, cache:'no-store'});
      const data = await res.json();
      if (data?.ok && data?.test_active) location.replace('?view=test');
    } catch (_) {}
  };
  check();
  setInterval(check, 3000);
})();

// Case-study gate -----------------------------------------------------------
(() => {
  const btn = document.querySelector('[data-journey-complete="case_study"]');
  if (!btn) return;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  btn.addEventListener('click', async () => {
    const body = new URLSearchParams({csrf, action:'journey_step', step:'case_study'});
    try {
      const res = await fetch('progress.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body});
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error || 'Krok se nepodařilo uložit.');
      btn.disabled = true;
      btn.textContent = 'Briefing splněn ✓';
      const practice = document.querySelector('[data-case-practice-button]');
      if (practice) practice.disabled = false;
      window.dispatchEvent(new CustomEvent('learning:state', {detail:data}));
      location.reload();
    } catch (err) {
      alert(err.message || 'Krok se nepodařilo uložit.');
    }
  });
})();

// Navazující dvouhodinová lekce -------------------------------------------
(() => {
  const root = document.querySelector('[data-next-lesson]');
  if (!root) return;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const stepAction = root.getAttribute('data-step-action') || 'next_lesson_step';
  const lessonId = root.getAttribute('data-lesson-id') || '';

  const current = root.querySelector('[data-next-step].current');
  if (sessionStorage.getItem('educanet_auto_advance') === '1' && current) {
    sessionStorage.removeItem('educanet_auto_advance');
    setTimeout(() => current.scrollIntoView({behavior:'smooth', block:'center'}), 180);
  }

  const setAutoFooter = (step, state, title, copy = '') => {
    const footer = step.querySelector('[data-auto-submit-status]');
    if (!footer) return;
    footer.classList.toggle('working', state === 'working');
    footer.classList.toggle('done', state === 'done');
    footer.classList.toggle('error', state === 'error');
    const icon = footer.querySelector(':scope > span');
    const strong = footer.querySelector('strong');
    const small = footer.querySelector('small');
    if (icon) icon.textContent = state === 'working' ? '◌' : state === 'done' ? '✓' : state === 'error' ? '!' : '✦';
    if (strong && title) strong.textContent = title;
    if (small && copy) small.textContent = copy;
  };

  root.querySelectorAll('[data-next-step]').forEach(step => {
    if (step.classList.contains('locked') || step.classList.contains('done')) return;
    const kind = step.getAttribute('data-kind') || 'manual';
    const feedback = step.querySelector('[data-next-feedback]');
    let answer = null;
    let submitting = false;

    const checks = () => Array.from(step.querySelectorAll('[data-next-task]'));
    const ready = () => {
      const list = checks();
      const tasksReady = !list.length || list.every(x => x.checked);
      const answerReady = kind !== 'quiz' || answer !== null;
      return tasksReady && answerReady;
    };

    const submit = async () => {
      if (submitting || !ready()) return;
      submitting = true;
      const list = checks();
      setAutoFooter(step, 'working', 'Ukládám krok…', 'XP se připíšou automaticky po ověření.');
      const body = new URLSearchParams({csrf, action:stepAction, step:step.getAttribute('data-next-step') || ''});
      if (lessonId) body.set('lesson', lessonId);
      body.set('tasks_done', String(list.filter(x => x.checked).length));
      if (answer !== null) body.set('answer', String(answer));
      try {
        const res = await fetch('progress.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body});
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || 'Krok se nepodařilo uložit.');
        if (data.correct === false) {
          submitting = false;
          if (feedback) {
            feedback.hidden = false;
            feedback.classList.remove('ok');
            feedback.textContent = data.explanation || 'Tento závěr ještě není správný. Vrať se k důkazům a zkus to znovu.';
          }
          setAutoFooter(step, 'error', 'Zkus jinou odpověď', 'Nic se neodečetlo. XP získáš až za správně dokončený krok.');
          answer = null;
          step.querySelectorAll('[data-next-answer]').forEach(x => x.classList.remove('selected'));
          return;
        }
        const gained = Number(data.awarded_xp || 0);
        if (feedback) {
          feedback.hidden = false;
          feedback.classList.add('ok');
          feedback.textContent = 'Hotovo. Krok je uložený a další se právě odemyká.';
        }
        setAutoFooter(step, 'done', gained > 0 ? `Hotovo · +${gained} XP` : 'Hotovo · uloženo', 'Další krok se otevře automaticky.');
        step.classList.add('done');
        window.dispatchEvent(new CustomEvent('learning:state', {detail:data}));
        sessionStorage.setItem('educanet_auto_advance', '1');
        setTimeout(() => location.reload(), 1250);
      } catch (err) {
        submitting = false;
        setAutoFooter(step, 'error', 'Ještě nelze pokračovat', err.message || 'Zkontroluj předchozí části.');
        if (window.toast) window.toast('Krok zatím nelze dokončit', err.message || 'Zkontroluj předchozí části.', 'error');
      }
    };

    step.querySelectorAll('[data-next-answer]').forEach(btn => {
      btn.addEventListener('click', () => {
        if (submitting) return;
        step.querySelectorAll('[data-next-answer]').forEach(x => x.classList.remove('selected'));
        btn.classList.add('selected');
        answer = Number(btn.getAttribute('data-next-answer'));
        if (feedback) feedback.hidden = true;
        setAutoFooter(step, 'working', 'Vyhodnocuji odpověď…', 'Není potřeba nic potvrzovat.');
        setTimeout(submit, 180);
      });
    });

    checks().forEach(box => box.addEventListener('change', () => {
      if (submitting) return;
      const list = checks();
      const done = list.filter(x => x.checked).length;
      if (ready()) {
        setAutoFooter(step, 'working', 'Vše splněno · ukládám…', 'Krok se automaticky ověří a připíše XP.');
        setTimeout(submit, 160);
      } else {
        setAutoFooter(step, 'idle', `${done} / ${list.length} bodů`, 'Po posledním bodu se krok uloží automaticky.');
      }
    }));
  });
})();

// Main navigation ----------------------------------------------------------
(() => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-main-menu]');
  if (!toggle || !menu) return;
  const close = () => { menu.classList.remove('open'); toggle.setAttribute('aria-expanded','false'); };
  toggle.addEventListener('click', () => {
    const open = !menu.classList.contains('open');
    menu.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();

// Small interactive visuals inside assessment screens ---------------------
(() => {
  document.querySelectorAll('[data-assessment-demo]').forEach(root => {
    root.querySelectorAll('[data-demo-action]').forEach(btn => {
      btn.addEventListener('click', () => {
        const action = btn.getAttribute('data-demo-action');
        if (action === 'evidence') {
          const evidence = root.querySelector('.demo-evidence');
          if (evidence) evidence.hidden = !evidence.hidden;
          btn.classList.toggle('active', evidence ? !evidence.hidden : false);
        } else if (action === 'grayscale') {
          root.classList.toggle('demo-grayscale');
          btn.classList.toggle('active', root.classList.contains('demo-grayscale'));
        } else if (action === 'blur') {
          root.classList.toggle('demo-blur');
          btn.classList.toggle('active', root.classList.contains('demo-blur'));
        } else if (action === 'grid') {
          root.classList.toggle('demo-grid-off');
          btn.classList.toggle('active', !root.classList.contains('demo-grid-off'));
        }
      });
    });
  });
})();

// Standalone Knowledge Check ----------------------------------------------
(() => {
  const root = document.querySelector('[data-kb-quiz]');
  if (!root) return;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const topic = root.getAttribute('data-topic') || '';
  const kbClass = root.getAttribute('data-kb-class') || '';
  const feedback = root.querySelector('[data-kb-quiz-feedback]');
  const hint = root.querySelector('[data-kb-quiz-hint]');
  const continueWrap = root.querySelector('[data-kb-quiz-continue]');
  let attempts = 0;
  root.querySelectorAll('[data-kb-quiz-answer]').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (root.classList.contains('complete')) return;
      root.querySelectorAll('[data-kb-quiz-answer]').forEach(x => x.classList.remove('selected','wrong'));
      btn.classList.add('selected');
      const answer = btn.getAttribute('data-kb-quiz-answer') || '-1';
      const confidence = root.getAttribute('data-confidence') || root.dataset.confidence || '0';
      const latency = Math.max(0, Date.now() - Number(root.dataset.startedAt || Date.now()));
      const body = new URLSearchParams({csrf, action:'kb_check', topic, answer, kb_class:kbClass, confidence, latency_ms:String(latency)});
      root.classList.add('checking');
      try {
        const res = await fetch('progress.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body});
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || 'Odpověď se nepodařilo ověřit.');
        attempts += 1;
        if (data.correct === false) {
          btn.classList.add('wrong');
          if (feedback) {
            feedback.hidden = false;
            feedback.className = 'kb-quiz-feedback bad';
            feedback.innerHTML = `<strong>Ještě ne.</strong><p>${attempts === 1 ? 'Vrať se k vizuálnímu důkazu a zkus oddělit fakt od domněnky.' : 'Zkus si otázku převést na konkrétní situaci: co bys skutečně viděl nebo změřil?'}</p>`;
          }
          if (hint && attempts >= 1) hint.hidden = false;
          return;
        }
        root.classList.add('complete');
        btn.classList.remove('wrong');
        btn.classList.add('correct');
        root.querySelectorAll('[data-kb-quiz-answer]').forEach(x => { if (x !== btn) x.disabled = true; });
        if (feedback) {
          feedback.hidden = false;
          feedback.className = 'kb-quiz-feedback ok';
          const why = root.getAttribute('data-why') || 'Správný závěr vychází z principu vysvětleného v této lekci.';
          feedback.innerHTML = `<strong>Správně.</strong><p>${why.replace(/[<>]/g,'')}</p>`;
        }
        if (continueWrap) continueWrap.hidden = false;
        window.dispatchEvent(new CustomEvent('learning:state', {detail:data}));
      } catch (err) {
        if (feedback) {
          feedback.hidden = false;
          feedback.className = 'kb-quiz-feedback bad';
          feedback.textContent = err.message || 'Odpověď se nepodařilo ověřit.';
        }
      } finally {
        root.classList.remove('checking');
      }
    });
  });
})();

// Keyboard shortcuts for focused assessments: 1–4 select, Enter submits. --
(() => {
  const page = document.querySelector('.assessment-page');
  if (!page) return;
  const choices = Array.from(page.querySelectorAll('.assessment-answer'));
  document.addEventListener('keydown', (e) => {
    if (e.target && /INPUT|TEXTAREA|SELECT/.test(e.target.tagName)) return;
    const n = Number(e.key);
    if (Number.isInteger(n) && n >= 1 && n <= choices.length) {
      e.preventDefault();
      const choice = choices[n - 1];
      const radio = choice.querySelector('input[type="radio"]');
      if (radio) { radio.checked = true; choice.click(); }
      else choice.click();
    }
    if (e.key === 'Enter') {
      const form = page.querySelector('.assessment-form');
      if (form && form.querySelector('input[type="radio"]:checked')) {
        e.preventDefault();
        form.requestSubmit();
      }
    }
  });
})();

// Assessment SVG 2.0: replayable, stateful, evidence-first demos ------------
(() => {
  document.querySelectorAll('.assessment-svg-visual[data-assessment-demo]').forEach(root => {
    const stage = root.querySelector('[data-av-stage]');
    const evidence = root.querySelector('[data-av-evidence]');
    const replay = () => {
      if (!stage) return;
      const svg = stage.querySelector('svg.av-svg');
      if (!svg) return;
      const clone = svg.cloneNode(true);
      svg.replaceWith(clone);
      root.classList.add('is-playing');
      window.setTimeout(() => root.classList.remove('is-playing'), 3200);
    };
    root.querySelectorAll('[data-av-action]').forEach(btn => {
      btn.addEventListener('click', () => {
        const action = btn.getAttribute('data-av-action');
        if (action === 'play') {
          replay();
          btn.classList.add('active');
          window.setTimeout(() => btn.classList.remove('active'), 900);
        } else if (action === 'state') {
          if (!btn.dataset.defaultLabel) btn.dataset.defaultLabel = btn.textContent.trim();
          root.classList.toggle('state-alt');
          const active = root.classList.contains('state-alt');
          btn.classList.toggle('active', active);
          btn.textContent = active ? '↶ Vrátit výchozí stav' : btn.dataset.defaultLabel;
        } else if (action === 'evidence' && evidence) {
          evidence.hidden = !evidence.hidden;
          btn.classList.toggle('active', !evidence.hidden);
        }
      });
    });
  });
})();

// KB visual labs 3.0 --------------------------------------------------------
(() => {
  const $$ = (root, selector) => Array.from(root.querySelectorAll(selector));
  const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
  const hexRgb = (hex) => {
    const value = String(hex || '').replace('#', '');
    if (!/^[0-9a-f]{6}$/i.test(value)) return [0,0,0];
    return [0,2,4].map((i) => parseInt(value.slice(i,i+2), 16));
  };
  const lum = (hex) => {
    const c = hexRgb(hex).map((v) => v / 255).map((v) => v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4));
    return .2126*c[0] + .7152*c[1] + .0722*c[2];
  };
  const ratio = (a,b) => {
    const A=lum(a), B=lum(b);
    return (Math.max(A,B)+.05)/(Math.min(A,B)+.05);
  };
  const setStageState = (stage, text) => {
    const node = stage.querySelector('[data-kb-demo-state]');
    if (node) node.textContent = text;
  };
  const markVisualComplete = (stage) => {
    if (!stage || stage.dataset.visualCompleted === '1') return;
    stage.dataset.visualCompleted = '1';
    stage.classList.add('learning-complete');
    stage.dispatchEvent(new CustomEvent('kb:visual-complete', {bubbles:true}));
  };

  // Generic visual models are now deliberate step-through explainers.
  document.querySelectorAll('[data-kb-demo-stage]').forEach((stage) => {
    const parts = $$(stage, '[data-demo-step]');
    const next = stage.querySelector('[data-kb-demo-next]');
    const reset = stage.querySelector('[data-kb-demo-reset]');
    let index = 0;
    const render = () => {
      if (!parts.length) return;
      parts.forEach((part, i) => {
        part.classList.toggle('revealed', i <= index);
        part.classList.toggle('is-focus', i === index);
      });
      if (next) next.textContent = index >= parts.length - 1 ? 'Od začátku ↻' : 'Další krok →';
      setStageState(stage, `${index + 1} / ${parts.length}`);
    };
    if (parts.length) {
      index = 0;
      render();
      next?.addEventListener('click', () => {
        if (index >= parts.length - 1) {
          markVisualComplete(stage);
          index = 0;
        } else {
          index += 1;
          if (index >= parts.length - 1) markVisualComplete(stage);
        }
        render();
      });
      reset?.addEventListener('click', () => { index = 0; render(); });
    } else {
      reset?.addEventListener('click', () => {
        stage.dispatchEvent(new CustomEvent('kbvisualreset', {bubbles:true}));
      });
    }
  });

  // Visual hierarchy: a genuine live design experiment instead of before/after art.
  document.querySelectorAll('[data-kb-hierarchy-lab]').forEach((lab) => {
    const stage = lab.closest('[data-kb-demo-stage]');
    const poster = lab.querySelector('[data-hierarchy-poster]');
    const insight = lab.querySelector('[data-hierarchy-insight]');
    const thumb = lab.querySelector('[data-hierarchy-thumbnail]');
    const path = lab.querySelector('.hierarchy-eye-path');
    const defaults = {title:36, meta:22, cta:15, space:8};
    const presets = {
      flat:{title:36,meta:22,cta:15,space:8},
      balanced:{title:54,meta:18,cta:17,space:16},
      strong:{title:66,meta:16,cta:20,space:22}
    };
    const read = () => Object.fromEntries(['title','meta','cta','space'].map((key) => {
      const el=lab.querySelector(`[data-hierarchy-input="${key}"]`);
      return [key, Number(el?.value || defaults[key])];
    }));
    const apply = (values) => {
      Object.entries(values).forEach(([key,value]) => {
        const input=lab.querySelector(`[data-hierarchy-input="${key}"]`);
        if(input) input.value=String(value);
      });
      render();
    };
    const render = () => {
      const v=read();
      if (poster) {
        poster.style.setProperty('--h-title', `${v.title}px`);
        poster.style.setProperty('--h-meta', `${v.meta}px`);
        poster.style.setProperty('--h-cta', `${v.cta}px`);
        poster.style.setProperty('--h-space', `${v.space}px`);
        poster.classList.toggle('thumbnail', Boolean(thumb?.checked));
      }
      ['title','meta','cta','space'].forEach((key) => {
        const out=lab.querySelector(`[data-hierarchy-value="${key}"]`);
        if(out) out.textContent=`${v[key]} px`;
      });
      const titleRatio=v.title/Math.max(1,v.meta);
      const ctaRatio=v.cta/Math.max(1,v.meta);
      let level='Plochá hierarchie', copy='Headline, datum a CTA si konkurují. Oko nemá jednoznačný první bod.', cls='flat';
      if(titleRatio>=2.8 && v.space>=18 && v.cta>=18){level='Silná hierarchie';copy='První je headline, potom datum a nakonec CTA. Pořadí zůstává čitelné i jako náhled.';cls='strong';}
      else if(titleRatio>=2.1 && v.space>=14 && ctaRatio>=.85){level='Použitelná hierarchie';copy='Role už se oddělují. Ještě ověř CTA a náhled z dálky.';cls='balanced';}
      if(insight){insight.className=`hierarchy-insight ${cls}`; insight.querySelector('strong').textContent=level; insight.querySelector('p').textContent=copy;}
      path?.classList.toggle('is-strong', cls==='strong');
      lab.querySelectorAll('[data-hierarchy-preset]').forEach((b)=>b.classList.toggle('active', JSON.stringify(presets[b.dataset.hierarchyPreset])===JSON.stringify(v)));
      setStageState(stage, level.toLowerCase());
    };
    lab.addEventListener('input', (e) => { if(e.target.matches('[data-hierarchy-input], [data-hierarchy-thumbnail]')) render(); });
    lab.addEventListener('change', (e) => { if(e.target.matches('[data-hierarchy-input], [data-hierarchy-thumbnail]')) markVisualComplete(stage); });
    lab.querySelectorAll('[data-hierarchy-preset]').forEach((b)=>b.addEventListener('click',()=>{apply(presets[b.dataset.hierarchyPreset] || defaults);markVisualComplete(stage);}));
    stage?.querySelector('[data-kb-demo-reset]')?.addEventListener('click',()=>{ if(thumb) thumb.checked=false; apply(defaults); });
    render();
  });

  document.querySelectorAll('[data-kb-grid-lab]').forEach((lab) => {
    const stage=lab.closest('[data-kb-demo-stage]'), canvas=lab.querySelector('[data-grid-canvas]'), lines=lab.querySelector('[data-grid-lines]'), content=lab.querySelector('[data-grid-content]'), align=lab.querySelector('[data-grid-align]');
    const render=()=>{
      const get=(k,def)=>Number(lab.querySelector(`[data-grid-input="${k}"]`)?.value||def);
      const cols=get('cols',4), margin=get('margin',24), gap=get('gap',16);
      canvas?.style.setProperty('--grid-cols',cols);canvas?.style.setProperty('--grid-margin',`${margin}px`);canvas?.style.setProperty('--grid-gap',`${gap}px`);
      if(lines) lines.innerHTML=Array.from({length:cols},()=>'<i></i>').join('');
      content?.classList.toggle('aligned',Boolean(align?.checked));
      [['cols',cols],['margin',`${margin} px`],['gap',`${gap} px`]].forEach(([k,v])=>{const n=lab.querySelector(`[data-grid-value="${k}"]`);if(n)n.textContent=String(v)});
      const status=lab.querySelector('[data-grid-status]');if(status)status.textContent=`${cols} sloupců · ${align?.checked?'společné hrany':'volné hrany'}`;
      setStageState(stage, align?.checked?'zarovnáno':'experiment');
    };
    lab.addEventListener('input',render);lab.addEventListener('change',(e)=>{render();if(e.target.matches('[data-grid-input], [data-grid-align]'))markVisualComplete(stage);});stage?.querySelector('[data-kb-demo-reset]')?.addEventListener('click',()=>{lab.querySelector('[data-grid-input="cols"]').value='4';lab.querySelector('[data-grid-input="margin"]').value='24';lab.querySelector('[data-grid-input="gap"]').value='16';if(align)align.checked=false;render();});render();
  });

  document.querySelectorAll('[data-kb-contrast-lab]').forEach((lab) => {
    const stage=lab.closest('[data-kb-demo-stage]'), card=lab.querySelector('[data-contrast-card]'), out=lab.querySelector('[data-contrast-readout]');
    const presets={low:['#7b6fa8','#a99fd0'],medium:['#4c1d95','#ddd6fe'],high:['#111827','#ffffff']};
    const render=()=>{
      const bg=lab.querySelector('[data-contrast-input="bg"]').value, text=lab.querySelector('[data-contrast-input="text"]').value, r=ratio(bg,text);
      card?.style.setProperty('--contrast-bg',bg);card?.style.setProperty('--contrast-text',text);
      if(out){out.querySelector('strong').textContent=`${r.toFixed(2)} : 1`;out.querySelector('p').textContent=r>=7?'Výborný kontrast pro běžný text.':r>=4.5?'Dobře čitelné pro běžný text.':r>=3?'Použitelné jen pro větší / výraznější text.':'Nedostatečné pro běžný text.';out.classList.toggle('ok',r>=4.5);out.classList.toggle('warn',r<4.5);}
      setStageState(stage,r>=4.5?'kontrast OK':'uprav kontrast');
    };
    lab.addEventListener('input',render);lab.addEventListener('change',(e)=>{if(e.target.matches('[data-contrast-input]'))markVisualComplete(stage);});lab.querySelectorAll('[data-contrast-preset]').forEach((b)=>b.addEventListener('click',()=>{const p=presets[b.dataset.contrastPreset]||presets.low;lab.querySelector('[data-contrast-input="bg"]').value=p[0];lab.querySelector('[data-contrast-input="text"]').value=p[1];lab.querySelectorAll('[data-contrast-preset]').forEach(x=>x.classList.toggle('active',x===b));render();markVisualComplete(stage);}));stage?.querySelector('[data-kb-demo-reset]')?.addEventListener('click',()=>{lab.querySelector('[data-contrast-input="bg"]').value=presets.low[0];lab.querySelector('[data-contrast-input="text"]').value=presets.low[1];render();});render();
  });

  document.querySelectorAll('[data-kb-scale-lab]').forEach((lab) => {
    const stage=lab.closest('[data-kb-demo-stage]'), input=lab.querySelector('[data-scale-input]'), raster=lab.querySelector('[data-scale-raster]'), vector=lab.querySelector('[data-scale-vector]');
    const render=()=>{const z=Number(input?.value||100), scale=clamp(z/400,.55,2.2);raster?.style.setProperty('--scale',scale);vector?.style.setProperty('--scale',scale);raster?.classList.toggle('pixelated',z>=500);const label=lab.querySelector('[data-scale-value]'),readout=lab.querySelector('[data-scale-readout]');if(label)label.textContent=`${z} %`;if(readout)readout.textContent=`${z} %`;setStageState(stage,z>=500?'raster degraduje':'porovnávej');};input?.addEventListener('input',render);input?.addEventListener('change',()=>markVisualComplete(stage));stage?.querySelector('[data-kb-demo-reset]')?.addEventListener('click',()=>{if(input)input.value='100';render();});render();
  });

  document.querySelectorAll('[data-kb-export-lab]').forEach((lab) => {
    const stage=lab.closest('[data-kb-demo-stage]');
    const render=()=>{const format=lab.querySelector('[data-export-input="format"]').value,width=Number(lab.querySelector('[data-export-input="width"]').value),quality=Number(lab.querySelector('[data-export-input="quality"]').value);const factor=format==='png'?.82:format==='jpg'?.19:.13;const size=Math.max(28,Math.round(width*width*.5625*factor*(quality/100)/1000));lab.querySelector('[data-export-format]').textContent=format.toUpperCase();lab.querySelector('[data-export-width]').textContent=`${width} px`;lab.querySelector('[data-export-size]').textContent=`~${size} kB`;lab.querySelector('[data-export-value]').textContent=`${quality} %`;lab.querySelector('[data-export-preview]')?.classList.toggle('artifacts',quality<62);setStageState(stage,quality<62?'viditelné artefakty':'náhled OK');};lab.addEventListener('input',render);lab.addEventListener('change',(e)=>{render();if(e.target.matches('[data-export-input]'))markVisualComplete(stage);});stage?.querySelector('[data-kb-demo-reset]')?.addEventListener('click',()=>{lab.querySelector('[data-export-input="format"]').value='webp';lab.querySelector('[data-export-input="width"]').value='1920';lab.querySelector('[data-export-input="quality"]').value='82';render();});render();
  });
})();

// Invite-code clipboard helper ---------------------------------------------
document.addEventListener('click', async (event) => {
  const button = event.target.closest('[data-copy-code]');
  if (!button) return;
  const code = button.getAttribute('data-copy-code') || '';
  if (!code) return;
  const original = button.textContent;
  try {
    await navigator.clipboard.writeText(code);
    button.textContent = 'Zkopírováno ✓';
  } catch (_) {
    const input = document.createElement('textarea');
    input.value = code; input.style.position = 'fixed'; input.style.opacity = '0';
    document.body.appendChild(input); input.select(); document.execCommand('copy'); input.remove();
    button.textContent = 'Zkopírováno ✓';
  }
  window.setTimeout(() => { button.textContent = original; }, 1600);
});

// v30 · adaptive explanation ladder + concept loop
(() => {
  document.querySelectorAll('[data-explain-ladder]').forEach((ladder) => {
    const toggle = ladder.querySelector('[data-explain-toggle]');
    const body = ladder.querySelector('[data-explain-body]');
    const activate = (name) => {
      ladder.querySelectorAll('[data-explain-mode]').forEach((b) => b.classList.toggle('active', b.getAttribute('data-explain-mode') === name));
      ladder.querySelectorAll('[data-explain-panel]').forEach((p) => {
        const on = p.getAttribute('data-explain-panel') === name;
        p.hidden = !on; p.classList.toggle('active', on);
      });
    };
    toggle?.addEventListener('click', () => {
      const next = Boolean(body?.hidden);
      if (body) body.hidden = !next;
      toggle.setAttribute('aria-expanded', next ? 'true' : 'false');
      if (next) body?.querySelector('[data-explain-mode]')?.focus();
    });
    ladder.querySelectorAll('[data-explain-mode]').forEach((b) => b.addEventListener('click', () => activate(b.getAttribute('data-explain-mode') || 'simple')));
  });
  document.querySelectorAll('[data-concept-loop]').forEach((loop) => {
    loop.querySelector('[data-loop-replay]')?.addEventListener('click', () => {
      loop.classList.remove('is-running');
      void loop.offsetWidth;
      loop.classList.add('is-running');
    });
  });
})();

// v31 · adaptive support telemetry + bounded EDU Tutor
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const post = async (params) => {
    const body = new URLSearchParams(params);
    const response = await fetch(window.location.pathname + window.location.search, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8','X-Requested-With':'XMLHttpRequest'},
      body,
      credentials: 'same-origin'
    });
    const data = await response.json().catch(() => ({ok:false,error:'Server vrátil neplatnou odpověď.'}));
    if (!response.ok || data.ok === false) throw new Error(data.error || 'Požadavek se nepodařilo dokončit.');
    return data;
  };

  document.querySelectorAll('[data-explain-ladder]').forEach((ladder) => {
    const topic = ladder.getAttribute('data-adaptive-topic') || '';
    ladder.querySelectorAll('[data-explain-mode]').forEach((button) => {
      button.addEventListener('click', () => {
        if (!csrf || !topic) return;
        post({csrf,action:'adaptive_help_event',topic,mode:button.getAttribute('data-explain-mode') || 'simple'}).catch(() => {});
      });
    });
    const tutor = ladder.querySelector('[data-edu-tutor]');
    const form = tutor?.querySelector('[data-edu-tutor-form]');
    const answer = tutor?.querySelector('[data-edu-tutor-answer]');
    form?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const input = form.querySelector('input[name="question"]');
      const question = (input?.value || '').trim();
      if (!question || !csrf || !topic || !answer) return;
      const button = form.querySelector('button[type="submit"]');
      if (button) { button.disabled = true; button.textContent = 'Přemýšlím…'; }
      answer.hidden = false; answer.classList.add('loading'); answer.textContent = 'Hledám nejkratší vysvětlení v této lekci…';
      try {
        const mode=tutor?.dataset.mode || 'guide';
        const data = await post({csrf,action:'adaptive_tutor',topic,question,mode});
        answer.classList.remove('loading'); answer.textContent = data.answer || 'Zkus otázku položit konkrétněji.';
      } catch (error) {
        answer.classList.remove('loading'); answer.textContent = error.message || 'Tutor teď není dostupný.';
      } finally {
        if (button) { button.disabled = false; button.textContent = 'Zeptat se'; }
      }
    });
  });
})();

// v35 · Reality Demos + modern lesson transitions -------------------------
(() => {
  const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  const supportsWAAPI = typeof Element !== 'undefined' && typeof Element.prototype.animate === 'function';
  const safeTransition = (update) => {
    if (!reduced && typeof document.startViewTransition === 'function') {
      try { return document.startViewTransition(update); } catch (_) {}
    }
    update();
    return null;
  };

  const animateIntro = (root) => {
    if (!root || reduced || root.classList.contains('motion-off')) return;
    root.classList.remove('demo-intro');
    // Force a new animation cycle only when the demo becomes relevant.
    void root.offsetWidth;
    root.classList.add('demo-intro');
    if (supportsWAAPI) {
      root.querySelectorAll('.topo-node,.stack-layer,.metric-card,.incident-line,.device,.component-preview,.search-intent').forEach((el, i) => {
        el.animate([
          {opacity:.45, transform:'translateY(5px)'},
          {opacity:1, transform:'translateY(0)'}
        ], {duration:260, delay:i*45, easing:'cubic-bezier(.2,.7,.3,1)', fill:'both'});
      });
    }
  };

  document.querySelectorAll('[data-reality-demo]').forEach(root => {
    const configNode = root.querySelector('[data-reality-config]');
    let config = {good:'Správně.',bad:'Zkus se vrátit k důkazům.',choices:[]};
    try { config = JSON.parse(configNode?.textContent || '{}'); } catch (_) {}
    const choices = Array.from(root.querySelectorAll('[data-reality-choice]'));
    const feedback = root.querySelector('[data-reality-feedback]');
    const explanation = root.querySelector('[data-reality-explanation]');
    const reset = root.querySelector('[data-reality-reset]');
    const status = root.querySelector('[data-stage-status]');
    const phases = Array.from(root.querySelectorAll('.reality-phases span'));
    const initialStatus = status?.textContent || '';
    const motionToggle = root.querySelector('[data-reality-motion-toggle]');
    const savedMotion = localStorage.getItem('educanet-reality-motion');
    const motionOff = reduced || savedMotion === 'off';
    root.classList.toggle('motion-off', motionOff);
    if (motionToggle) {
      motionToggle.setAttribute('aria-pressed', motionOff ? 'false' : 'true');
      motionToggle.textContent = motionOff ? 'Pohyb: omezený' : 'Pohyb: auto';
      motionToggle.addEventListener('click', () => {
        const off = !root.classList.contains('motion-off');
        document.querySelectorAll('[data-reality-demo]').forEach(d => {
          d.classList.toggle('motion-off', off);
          const b=d.querySelector('[data-reality-motion-toggle]'); if(b){b.setAttribute('aria-pressed',off?'false':'true');b.textContent=off?'Pohyb: omezený':'Pohyb: auto';}
        });
        localStorage.setItem('educanet-reality-motion', off ? 'off' : 'on');
        if (!off) animateIntro(root);
      });
    }
    let resolved = false;

    const setPhase = (n) => phases.forEach((el, i) => el.classList.toggle('active', i <= n));
    const resetDemo = () => {
      resolved = false;
      root.classList.remove('choice-good','choice-bad');
      choices.forEach(btn => { btn.disabled=false; btn.classList.remove('correct','wrong'); });
      if (feedback) { feedback.hidden=true; feedback.className='reality-feedback'; feedback.querySelector('strong').textContent=''; feedback.querySelector('p').textContent=''; }
      if (explanation) explanation.hidden=true;
      if (reset) reset.hidden=true;
      if (status) { status.textContent=initialStatus; status.classList.remove('good'); status.classList.add('bad'); }
      setPhase(0);
      animateIntro(root);
    };

    choices.forEach(btn => btn.addEventListener('click', () => {
      if (resolved) return;
      resolved = true;
      const idx = Number(btn.dataset.realityChoice || 0);
      const correct = btn.dataset.correct === '1';
      const item = Array.isArray(config.choices) ? config.choices[idx] : null;
      choices.forEach(b => b.disabled=true);
      btn.classList.add(correct ? 'correct' : 'wrong');
      root.classList.add(correct ? 'choice-good' : 'choice-bad');
      setPhase(3);
      if (feedback) {
        feedback.hidden=false;
        feedback.classList.add(correct ? 'good' : 'bad');
        feedback.querySelector('strong').textContent=correct ? 'Dobrá diagnostika' : 'Ještě ne dost přesné';
        feedback.querySelector('p').textContent=`${correct ? (config.good || '') : (config.bad || '')}${item?.result ? ' ' + item.result : ''}`.trim();
      }
      if (explanation) explanation.hidden=false;
      if (reset) reset.hidden=false;
      if (status) {
        status.textContent = item?.result || (correct ? 'výsledek ověřen' : 'potřebujeme další důkaz');
        status.classList.toggle('good', correct); status.classList.toggle('bad', !correct);
      }
      if (!reduced && supportsWAAPI) {
        const stage = root.querySelector('[data-reality-stage]');
        stage?.animate(
          correct
            ? [{boxShadow:'0 0 0 0 rgba(22,163,74,0)'},{boxShadow:'0 0 0 5px rgba(22,163,74,.10)'},{boxShadow:'0 0 0 0 rgba(22,163,74,0)'}]
            : [{transform:'translateX(0)'},{transform:'translateX(-3px)'},{transform:'translateX(3px)'},{transform:'translateX(0)'}],
          {duration:correct?520:260,easing:'ease-out'}
        );
      }
      root.dispatchEvent(new CustomEvent('reality:decision',{bubbles:true,detail:{correct,topic:root.dataset.topic||''}}));
      if (correct) root.dispatchEvent(new CustomEvent('kb:visual-complete',{bubbles:true,detail:{source:'reality-demo'}}));
    }));
    reset?.addEventListener('click', resetDemo);

    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting && entry.intersectionRatio >= .35) {
            animateIntro(root); io.unobserve(root);
          }
        });
      }, {threshold:[.35]});
      io.observe(root);
    } else animateIntro(root);
  });

  // Lesson walkthrough: manual-first, optional autoplay. Scene changes use
  // the standardized document View Transition API when available.
  document.querySelectorAll('[data-lesson-story-demo]').forEach(root => {
    const tabs = Array.from(root.querySelectorAll('[data-lesson-story-tab]'));
    const scenes = Array.from(root.querySelectorAll('[data-lesson-story-scene]'));
    const play = root.querySelector('[data-lesson-story-play]');
    const prev = root.querySelector('[data-lesson-story-prev]');
    const next = root.querySelector('[data-lesson-story-next]');
    if (!tabs.length || !scenes.length) return;
    let index = 0, running = false, timer = null, visible = true;

    const applyScene = (nextIndex) => {
      index = (nextIndex + scenes.length) % scenes.length;
      scenes.forEach((scene,j)=>{const on=j===index;scene.hidden=!on;scene.classList.toggle('active',on);});
      tabs.forEach((tab,j)=>{const on=j===index;tab.classList.toggle('active',on);tab.setAttribute('aria-selected',on?'true':'false');tab.tabIndex=on?0:-1;});
      const demo=scenes[index]?.querySelector('[data-reality-demo]');
      if (demo) animateIntro(demo);
    };
    const show = (i, user=false) => {
      safeTransition(() => applyScene(i));
      if (user) restart();
    };
    const updatePlay = () => {
      if (!play) return;
      play.setAttribute('aria-pressed',running?'true':'false');
      play.textContent=running?'Pozastavit':'Přehrát vše';
    };
    const restart = () => {
      if (timer) clearInterval(timer); timer=null;
      if (running && visible && scenes.length>1) timer=setInterval(()=>show(index+1,false),10500);
    };
    tabs.forEach((tab,i)=>{
      tab.addEventListener('click',()=>show(i,true));
      tab.addEventListener('keydown',(e)=>{
        if(e.key==='ArrowRight'||e.key==='ArrowLeft'){
          e.preventDefault(); const n=e.key==='ArrowRight'?i+1:i-1; const target=(n+tabs.length)%tabs.length; show(target,true); tabs[target].focus();
        }
      });
    });
    prev?.addEventListener('click',()=>show(index-1,true)); next?.addEventListener('click',()=>show(index+1,true));
    play?.addEventListener('click',()=>{running=!running;updatePlay();restart();if(running)animateIntro(scenes[index]?.querySelector('[data-reality-demo]'));});
    if ('IntersectionObserver' in window) {
      const io=new IntersectionObserver(entries=>{entries.forEach(entry=>{visible=entry.isIntersecting;if(!visible&&timer){clearInterval(timer);timer=null;}else if(visible)restart();});},{threshold:.08});
      io.observe(root);
    }
    updatePlay(); applyScene(0);
  });
})();

// v41 · Mastery Learning interactions --------------------------------------
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const postPage = async (params) => {
    const res = await fetch(window.location.pathname + window.location.search, {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8','X-Requested-With':'XMLHttpRequest'},body:new URLSearchParams(params),credentials:'same-origin'});
    const data = await res.json().catch(()=>({ok:false,error:'Neplatná odpověď serveru.'}));
    if(!res.ok || data.ok===false) throw new Error(data.error || 'Akci se nepodařilo dokončit.');
    return data;
  };

  document.querySelectorAll('[data-ml-worked]').forEach(root => {
    const steps = Array.from(root.querySelectorAll('[data-ml-worked-step]'));
    const apply = mode => {
      root.querySelectorAll('[data-ml-fade]').forEach(b=>b.classList.toggle('active',b.dataset.mlFade===mode));
      steps.forEach((step,i)=>{
        const input=step.querySelector('[data-ml-completion-input]');
        const p=step.querySelector('p');
        const hide = mode==='partial' ? (i===1 || i===3) : mode==='independent';
        if(p) p.hidden=hide;
        if(input){input.hidden=!hide;input.value='';input.placeholder=mode==='independent'?'Napiš svůj krok…':'Co sem patří podle tebe?';}
        step.classList.toggle('is-blank',hide);
      });
    };
    root.querySelectorAll('[data-ml-fade]').forEach(b=>b.addEventListener('click',()=>apply(b.dataset.mlFade||'full')));
  });

  document.querySelectorAll('[data-ml-transfer]').forEach(root => {
    let confidence=0, done=false;
    const feedback=root.querySelector('[data-ml-transfer-feedback]');
    root.querySelectorAll('[data-ml-confidence]').forEach(b=>b.addEventListener('click',()=>{confidence=Number(b.dataset.mlConfidence||0);root.querySelectorAll('[data-ml-confidence]').forEach(x=>x.classList.toggle('active',x===b));}));
    root.querySelectorAll('[data-ml-transfer-choice]').forEach(b=>b.addEventListener('click',async()=>{
      if(done) return; if(!confidence){feedback.hidden=false;feedback.className='ml-feedback warn';feedback.textContent='Nejdřív označ, jak moc si věříš. Neovlivní to hodnocení.';return;}
      try{const data=await postPage({csrf,action:'ml_transfer',topic:root.dataset.topic||'',answer:b.dataset.mlTransferChoice||'-1',confidence});done=true;root.querySelectorAll('[data-ml-transfer-choice]').forEach(x=>x.disabled=true);b.classList.add(data.correct?'correct':'wrong');feedback.hidden=false;feedback.className='ml-feedback '+(data.correct?'ok':'bad');feedback.innerHTML=`<strong>${data.correct?'Transfer zvládnutý':'Ještě uprav mentální model'}</strong><p>${String(data.why||'').replace(/[<>]/g,'')}</p>`;}catch(e){feedback.hidden=false;feedback.className='ml-feedback bad';feedback.textContent=e.message;}
    }));
  });

  document.querySelectorAll('[data-ml-browser-lab]').forEach(root => {
    if(root.dataset.labType==='terminal'){
      const form=root.querySelector('[data-ml-terminal-form]'), output=root.querySelector('[data-ml-terminal-output]'), hint=root.querySelector('[data-ml-terminal-hint]');
      form?.addEventListener('submit',async e=>{e.preventDefault();const input=form.querySelector('input[name="command"]');const command=(input?.value||'').trim();if(!command)return;output.textContent += `\n$ ${command}\n…`;try{const d=await postPage({csrf,action:'ml_terminal',topic:root.dataset.topic||'',command});output.textContent += `\n${d.out}\n`;hint.textContent=d.hint||'';input.value='';output.scrollTop=output.scrollHeight;}catch(err){output.textContent += `\n${err.message}\n`;}});
    } else {
      const preview=root.querySelector('[data-ml-design-preview]');
      root.querySelectorAll('[data-ml-design]').forEach(input=>input.addEventListener('input',()=>{const kind=input.dataset.mlDesign;const v=input.value;if(kind==='gap')preview.style.gap=v+'px';if(kind==='size')preview.querySelector('h4').style.fontSize=v+'px';if(kind==='width')preview.style.maxWidth=v+'px';}));
    }
  });

  // Extend standalone quiz with confidence + response latency.
  const quiz=document.querySelector('[data-kb-quiz]');
  if(quiz){
    quiz.dataset.startedAt=String(Date.now());
    quiz.querySelectorAll('[data-confidence]').forEach(b=>b.addEventListener('click',()=>{quiz.dataset.confidence=b.dataset.confidence||'0';quiz.querySelectorAll('[data-confidence]').forEach(x=>x.classList.toggle('active',x===b));}));
  }

  // Tutor modes are intentionally small: same context, different coaching strategy.
  document.querySelectorAll('[data-edu-tutor]').forEach(tutor=>{
    tutor.querySelectorAll('[data-tutor-mode]').forEach(btn=>btn.addEventListener('click',()=>{tutor.dataset.mode=btn.dataset.tutorMode||'guide';tutor.querySelectorAll('[data-tutor-mode]').forEach(x=>x.classList.toggle('active',x===btn));}));
  });

  // PWA registration: progressive enhancement only.
  if('serviceWorker' in navigator && location.protocol==='https:') window.addEventListener('load',()=>navigator.serviceWorker.register('sw.js').catch(()=>{}));
})();

// v41 · completion interactions: compare, templates, power-user shortcuts ----
(() => {
  document.querySelectorAll('.ml-visual-editor').forEach(root => {
    const preview=root.querySelector('[data-ml-design-preview]');
    const controls={}; root.querySelectorAll('[data-ml-design]').forEach(i=>controls[i.dataset.mlDesign]=i);
    if(!preview) return;
    const baseline={gap:Number(preview.dataset.baselineGap||12),size:Number(preview.dataset.baselineSize||38),width:Number(preview.dataset.baselineWidth||520)};
    let current={...baseline};
    const read=()=>({gap:Number(controls.gap?.value||baseline.gap),size:Number(controls.size?.value||baseline.size),width:Number(controls.width?.value||baseline.width)});
    const apply=v=>{preview.style.gap=v.gap+'px';const h=preview.querySelector('h4');if(h)h.style.fontSize=v.size+'px';preview.style.maxWidth=v.width+'px';};
    root.querySelector('[data-ml-before]')?.addEventListener('click',()=>{current=read();apply(baseline);preview.classList.add('is-before');});
    root.querySelector('[data-ml-after]')?.addEventListener('click',()=>{apply(current);preview.classList.remove('is-before');});
  });

  document.querySelectorAll('[data-ml-template]').forEach(btn=>btn.addEventListener('click',()=>{
    const form=document.querySelector('[data-ml-authoring-form]'); if(!form) return;
    try{
      const t=JSON.parse(btn.dataset.mlTemplate||'{}');
      const set=(name,value)=>{const el=form.querySelector(`[name="${name}"]`);if(el)el.value=value??'';};
      set('title',t.title);set('brief',t.brief);(t.choices||[]).forEach((v,i)=>set('choice_'+i,v));set('correct',t.correct??0);set('why',t.why);
      form.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});
    }catch(_){/* invalid template is ignored */}
  }));

  // Keyboard shortcuts never fire while a student is typing.
  document.addEventListener('keydown',e=>{
    const tag=(e.target?.tagName||'').toLowerCase(); if(['input','textarea','select'].includes(tag)||e.target?.isContentEditable) return;
    const k=e.key.toLowerCase();
    if(k==='r'){document.querySelector('[data-reality-reset]:not([hidden])')?.click();}
    if(k==='h'){document.querySelector('[data-adaptive-explain-toggle], [data-help-toggle]')?.click();}
    if(k==='n'){document.querySelector('[data-lesson-story-next]')?.click();}
    if(k==='p'){document.querySelector('[data-lesson-story-prev]')?.click();}
  });
})();

// v41 · teacher/student authored scenario runner ----------------------------
(() => {
  const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
  const send=async params=>{const res=await fetch(location.pathname+location.search,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8','X-Requested-With':'XMLHttpRequest'},body:new URLSearchParams(params),credentials:'same-origin'});const data=await res.json().catch(()=>({ok:false,error:'Neplatná odpověď serveru.'}));if(!res.ok||data.ok===false)throw new Error(data.error||'Akce selhala.');return data;};
  document.querySelectorAll('[data-ml-custom-scenario]').forEach(root=>{
    let confidence=0,done=false;const feedback=root.querySelector('[data-ml-scenario-feedback]');
    root.querySelectorAll('[data-ml-scenario-confidence]').forEach(b=>b.addEventListener('click',()=>{confidence=Number(b.dataset.mlScenarioConfidence||0);root.querySelectorAll('[data-ml-scenario-confidence]').forEach(x=>x.classList.toggle('active',x===b));}));
    root.querySelectorAll('[data-ml-scenario-choice]').forEach(b=>b.addEventListener('click',async()=>{if(done)return;if(!confidence){feedback.hidden=false;feedback.className='ml-feedback warn';feedback.textContent='Nejdřív označ jistotu. Neovlivní známku.';return;}try{const d=await send({csrf,action:'ml_scenario_answer',scenario_id:root.dataset.scenarioId||'',answer:b.dataset.mlScenarioChoice||'-1',confidence});done=true;root.querySelectorAll('[data-ml-scenario-choice]').forEach(x=>x.disabled=true);b.classList.add(d.correct?'correct':'wrong');feedback.hidden=false;feedback.className='ml-feedback '+(d.correct?'ok':'bad');feedback.textContent=(d.correct?'Správný mentální model. ':'Ještě zkus upravit hypotézu. ')+(d.why||'');}catch(e){feedback.hidden=false;feedback.className='ml-feedback bad';feedback.textContent=e.message;}}));
  });
})();

/* v42 Adaptive Lesson Kits ------------------------------------------------ */
(() => {
  document.querySelectorAll('[data-v42-copy-prompt]').forEach((button) => {
    button.addEventListener('click', async () => {
      const area = button.closest('.v42-prompt-card')?.querySelector('textarea');
      if (!area) return;
      try { await navigator.clipboard.writeText(area.value); button.textContent = 'Zkopírováno ✓'; }
      catch (_) { area.select(); document.execCommand('copy'); button.textContent = 'Zkopírováno ✓'; }
      setTimeout(() => button.textContent = 'Kopírovat prompt', 1600);
    });
  });
  const shell = document.querySelector('[data-v42-slides]');
  if (!shell) return;
  const slides = [...shell.querySelectorAll('[data-v42-slide]')]; let index = 0;
  const count = shell.querySelector('[data-v42-slide-count]');
  const show = (next) => { index = Math.max(0, Math.min(slides.length - 1, next)); slides.forEach((s,i)=>{s.hidden=i!==index;s.classList.toggle('active',i===index);}); if(count) count.textContent=`${index+1} / ${slides.length}`; };
  shell.querySelector('[data-v42-slide-prev]')?.addEventListener('click',()=>show(index-1));
  shell.querySelector('[data-v42-slide-next]')?.addEventListener('click',()=>show(index+1));
  document.addEventListener('keydown',(e)=>{if(e.key==='ArrowRight'||e.key==='PageDown')show(index+1);if(e.key==='ArrowLeft'||e.key==='PageUp')show(index-1);if(e.key.toLowerCase()==='f')shell.requestFullscreen?.();if(e.key==='Escape'&&!document.fullscreenElement)history.back();});
})();
