/* EDUCANET v57 · Linux Lab – terminál v prohlížeči.
 * Vše je simulace: JS jen posílá řádky na lab_v57_api.php a kreslí odpověď.
 * Žádné innerHTML se serverovým obsahem – vše jde přes textContent/DOM uzly (XSS). */
(function () {
  'use strict';

  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  var REDUCED_MOTION = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ------------------------------------------------------------------
  // v58 · OFFLINE kontrakt: window.Lab57.setTransport(fn) nahradí fetch na lab_v57_api.php
  // vlastní funkcí fn(op, payload) → Promise<object> se stejným tvarem JSON (viz docs/LAB_V58_API.md, §5).
  // ------------------------------------------------------------------

  var lab57Transport = null;
  window.Lab57 = window.Lab57 || {};
  window.Lab57.setTransport = function (fn) {
    lab57Transport = typeof fn === 'function' ? fn : null;
  };

  // ------------------------------------------------------------------
  // ANSI SGR → DOM
  // ------------------------------------------------------------------

  function ansiClasses(codes) {
    var cls = [];
    var fg = null, bg = null, bold = false;
    for (var i = 0; i < codes.length; i++) {
      var n = codes[i];
      if (n === 0 || n === '' ) { fg = null; bg = null; bold = false; }
      else if (n === 1) bold = true;
      else if (n === 22) bold = false;
      else if ((n >= 30 && n <= 37) || (n >= 90 && n <= 97)) fg = n;
      else if (n === 39) fg = null;
      else if ((n >= 40 && n <= 47)) bg = n;
      else if (n === 49) bg = null;
    }
    if (bold) cls.push('a-bold');
    if (fg !== null) cls.push('a-fg-' + fg);
    if (bg !== null) cls.push('a-bg-' + bg);
    return cls;
  }

  /** Rozparsuje text s \e[..m escape sekvencemi do DocumentFragmentu se span barvami. */
  function ansiFragment(text) {
    var frag = document.createDocumentFragment();
    var re = /\x1b\[([0-9;]*)m/g;
    var last = 0, m, active = [];
    function push(str) {
      if (str === '') return;
      if (active.length === 0) { frag.appendChild(document.createTextNode(str)); return; }
      var span = document.createElement('span');
      span.className = active.join(' ');
      span.appendChild(document.createTextNode(str));
      frag.appendChild(span);
    }
    while ((m = re.exec(text)) !== null) {
      push(text.slice(last, m.index));
      last = re.lastIndex;
      var codes = m[1] === '' ? [0] : m[1].split(';').map(function (s) { return parseInt(s, 10); });
      active = ansiClasses(codes);
    }
    push(text.slice(last));
    return frag;
  }

  function stripAnsi(text) {
    return String(text).replace(/\x1b\[[0-9;]*m/g, '');
  }

  // ------------------------------------------------------------------
  // Jedna instance Labu
  // ------------------------------------------------------------------

  function initLab57(root) {
    var api = root.dataset.api;
    var level = root.dataset.level;
    var ctx = root.dataset.ctx;
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var out = root.querySelector('[data-lab57-out]');
    var form = root.querySelector('[data-lab57-form]');
    var input = root.querySelector('[data-lab57-input]');
    var promptEl = root.querySelector('[data-lab57-prompt]');
    var hintBtn = root.querySelector('[data-lab57-hint]');
    var hintCount = root.querySelector('[data-lab57-hint-count]');
    var miseBtn = root.querySelector('[data-lab57-mise]');
    var resetBtn = root.querySelector('[data-lab57-reset]');
    var solvedBox = root.querySelector('[data-lab57-solved]');
    var learnEl = root.querySelector('[data-lab57-learn]');
    var pointsEl = root.querySelector('[data-lab57-points]');
    var xpEl = root.querySelector('[data-lab57-xp]');
    var firstBloodEl = root.querySelector('[data-lab57-first-blood]');
    var nextLink = root.querySelector('[data-lab57-next]');
    var checklistEl = root.querySelector('[data-lab57-checklist]');
    var missionToggle = root.querySelector('[data-lab57-mission-toggle]');
    var touchbar = root.querySelector('[data-lab57-touchbar]');
    var netWrap = root.querySelector('[data-lab57-net]');
    var netSvg = root.querySelector('[data-lab57-net-svg]');

    var history = [];
    var historyPos = 0;
    var busy = false;
    var hintsTotal = 0;
    var levelType = '';
    var lastLine = '';
    var currentTopology = null;

    function post(op, extra) {
      var payload = { csrf: csrf, op: op, level: level, ctx: ctx };
      if (extra) Object.keys(extra).forEach(function (k) { payload[k] = extra[k]; });
      if (lab57Transport) {
        return Promise.resolve().then(function () { return lab57Transport(op, payload); }).then(function (json) {
          json = json && typeof json === 'object' ? json : {};
          if (typeof json.__status !== 'number') json.__status = json.ok === false ? 400 : 200;
          return json;
        });
      }
      var body = new URLSearchParams();
      Object.keys(payload).forEach(function (k) { body.set(k, String(payload[k])); });
      return fetch(api, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (res) {
        return res.json().then(function (json) {
          json.__status = res.status;
          return json;
        }, function () {
          throw new Error('bad-json');
        });
      });
    }

    // ---------------- výstup do terminálu ----------------

    function scrollBottom() {
      out.scrollTop = out.scrollHeight;
    }

    function appendCmdLine(promptText, cmdText) {
      var div = document.createElement('div');
      div.className = 'lab57-cmdline';
      var p = document.createElement('span');
      p.className = 'lab57-prompt-echo';
      p.textContent = promptText;
      div.appendChild(p);
      div.appendChild(document.createTextNode(cmdText));
      out.appendChild(div);
    }

    function appendChunk(fd, text) {
      if (text === '') return;
      var div = document.createElement('div');
      div.className = 'lab57-out-chunk' + (fd === 2 ? ' is-err' : '');
      div.appendChild(ansiFragment(text));
      out.appendChild(div);
    }

    function appendTip(text) {
      var div = document.createElement('div');
      div.className = 'lab57-tipline';
      div.textContent = '💡 ' + text;
      out.appendChild(div);
    }

    function appendSys(text) {
      var div = document.createElement('div');
      div.className = 'lab57-sysline';
      div.textContent = text;
      out.appendChild(div);
    }

    function appendError(text) {
      var div = document.createElement('div');
      div.className = 'lab57-out-chunk is-err';
      div.textContent = text;
      out.appendChild(div);
    }

    function appendColumns(items) {
      var wrap = document.createElement('div');
      wrap.className = 'lab57-cols';
      items.forEach(function (it) {
        var span = document.createElement('span');
        span.textContent = it;
        wrap.appendChild(span);
      });
      out.appendChild(wrap);
    }

    // ---------------- stav mise ----------------

    function setPrompt(p) {
      promptEl.textContent = p || '$';
    }

    function setBusy(v) {
      busy = v;
      root.classList.toggle('lab57-busy', v);
    }

    function updateHintCount(used) {
      if (hintCount) hintCount.textContent = '(' + used + '/' + hintsTotal + ')';
    }

    function renderChecklist(checks) {
      if (!checklistEl) return;
      checklistEl.textContent = '';
      (checks || []).forEach(function (c) {
        var li = document.createElement('li');
        li.className = c.ok ? 'ok' : 'fail';
        li.textContent = (c.ok ? '✔ ' : '○ ') + c.label;
        checklistEl.appendChild(li);
      });
    }

    function showSolved(resp) {
      if (!solvedBox) return;
      solvedBox.hidden = false;
      learnEl.textContent = resp.learn || '';
      pointsEl.textContent = typeof resp.points === 'number' ? '+' + resp.points + ' b.' : '';
      if (typeof resp.xp === 'number' && resp.xp > 0) { xpEl.hidden = false; xpEl.textContent = '+' + resp.xp + ' XP'; } else if (xpEl) xpEl.hidden = true;
      if (firstBloodEl) firstBloodEl.hidden = !resp.first_blood;
      solvedBox.setAttribute('role', 'status');
    }

    // ---------------- topologie sítě ----------------

    var NET_SHAPES = { pc: 'rect', switch: 'rect', router: 'rect', server: 'rect', dns: 'circle', printer: 'rect' };
    var NET_ICON = { pc: '💻', switch: '⇄', router: '📡', server: '🖥', dns: '🌐', printer: '🖨' };

    function drawTopology(topo) {
      if (!netSvg || !topo) return;
      currentTopology = topo;
      while (netSvg.firstChild) netSvg.removeChild(netSvg.firstChild);
      var linksG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
      var nodesG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
      var pos = {};
      (topo.nodes || []).forEach(function (n) { pos[n.id] = n; });
      (topo.links || []).forEach(function (link) {
        var a = pos[link[0]], b = pos[link[1]];
        if (!a || !b) return;
        var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', a.x); line.setAttribute('y1', a.y);
        line.setAttribute('x2', b.x); line.setAttribute('y2', b.y);
        line.setAttribute('class', 'lab57-net-link');
        line.dataset.a = a.id; line.dataset.b = b.id;
        linksG.appendChild(line);
      });
      (topo.nodes || []).forEach(function (n) {
        var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'lab57-net-node');
        g.dataset.id = n.id;
        var shape = NET_SHAPES[n.kind] || 'circle';
        var shapeEl;
        if (shape === 'rect') {
          shapeEl = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
          shapeEl.setAttribute('x', n.x - 22); shapeEl.setAttribute('y', n.y - 16);
          shapeEl.setAttribute('width', 44); shapeEl.setAttribute('height', 32); shapeEl.setAttribute('rx', 8);
        } else {
          shapeEl = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
          shapeEl.setAttribute('cx', n.x); shapeEl.setAttribute('cy', n.y); shapeEl.setAttribute('r', 20);
        }
        var title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        title.textContent = n.label + (n.ip ? ' (' + n.ip + ')' : '');
        g.appendChild(title);
        g.appendChild(shapeEl);
        var icon = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        icon.setAttribute('x', n.x); icon.setAttribute('y', n.y + 4);
        icon.setAttribute('text-anchor', 'middle'); icon.setAttribute('font-size', '13');
        icon.textContent = NET_ICON[n.kind] || '•';
        g.appendChild(icon);
        var label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', n.x); label.setAttribute('y', n.y + 30);
        label.setAttribute('text-anchor', 'middle');
        label.textContent = n.label;
        g.appendChild(label);
        nodesG.appendChild(g);
      });
      netSvg.appendChild(linksG);
      netSvg.appendChild(nodesG);
      // v58 · A11Y-02: textová alternativa (tabulka cesty) poslouchá tuhle událost, aby znala jména/adresy uzlů.
      document.dispatchEvent(new CustomEvent('lab57:topology', { detail: { root: root, topology: topo } }));
    }

    function netNodeEl(id) { return netSvg ? netSvg.querySelector('.lab57-net-node[data-id="' + CSS.escape(String(id)) + '"]') : null; }
    function netLinkEl(a, b) {
      if (!netSvg) return null;
      return netSvg.querySelector('.lab57-net-link[data-a="' + CSS.escape(String(a)) + '"][data-b="' + CSS.escape(String(b)) + '"]') ||
        netSvg.querySelector('.lab57-net-link[data-a="' + CSS.escape(String(b)) + '"][data-b="' + CSS.escape(String(a)) + '"]');
    }

    function clearNetActive() {
      if (!netSvg) return;
      netSvg.querySelectorAll('.is-active, .is-ok, .is-fail').forEach(function (el) {
        el.classList.remove('is-active', 'is-ok', 'is-fail');
      });
    }

    /** v58 · A11Y-02: prefers-reduced-motion i režim „jen text“ vykreslí rovnou konečný (statický) stav cesty bez časovačů. */
    function netMotionOff() {
      return REDUCED_MOTION || root.classList.contains('lab57-plain');
    }

    function drawNetStatic(events) {
      events.forEach(function (ev) {
        var hops = ev.hops || [];
        hops.forEach(function (nodeId, i) {
          var nodeEl = netNodeEl(nodeId);
          if (nodeEl) nodeEl.classList.add('is-active');
          if (i > 0) {
            var linkEl = netLinkEl(hops[i - 1], nodeId);
            if (linkEl) linkEl.classList.add('is-active');
          }
          if (i === hops.length - 1 && nodeEl) {
            nodeEl.classList.toggle('is-ok', !!ev.ok);
            nodeEl.classList.toggle('is-fail', !ev.ok);
          }
        });
        if (ev.fail) {
          var failEl = netNodeEl(ev.fail);
          if (failEl) failEl.classList.add('is-fail');
        }
      });
    }

    function animateNet(events) {
      if (!netSvg || !events || events.length === 0) return;
      clearNetActive();
      if (netMotionOff()) { drawNetStatic(events); return; }
      var delay = 260;
      var t = 0;
      events.forEach(function (ev) {
        var hops = ev.hops || [];
        hops.forEach(function (nodeId, i) {
          setTimeout(function () {
            var nodeEl = netNodeEl(nodeId);
            if (nodeEl) nodeEl.classList.add('is-active');
            if (i > 0) {
              var linkEl = netLinkEl(hops[i - 1], nodeId);
              if (linkEl) linkEl.classList.add('is-active');
            }
            var isLast = i === hops.length - 1;
            if (isLast && nodeEl) {
              nodeEl.classList.toggle('is-ok', !!ev.ok);
              nodeEl.classList.toggle('is-fail', !ev.ok);
            }
          }, t);
          t += delay;
        });
        if (ev.fail) {
          setTimeout(function () {
            var failEl = netNodeEl(ev.fail);
            if (failEl) failEl.classList.add('is-fail');
          }, t);
        }
        t += delay;
      });
    }

    // ---------------- editor (nano) ----------------

    var editor = buildEditor();

    function buildEditor() {
      var box = document.createElement('div');
      box.className = 'lab57-editor';
      box.hidden = true;
      var head = document.createElement('div');
      head.className = 'lab57-editor-head';
      var b = document.createElement('b'); b.textContent = 'GNU nano 7.2';
      var nameSpan = document.createElement('span');
      head.appendChild(b);
      head.appendChild(document.createTextNode('  '));
      head.appendChild(nameSpan);
      var warn = document.createElement('div');
      warn.className = 'lab57-editor-warn';
      warn.hidden = true;
      var textarea = document.createElement('textarea');
      textarea.className = 'lab57-editor-text';
      textarea.spellcheck = false;
      var msg = document.createElement('div');
      msg.className = 'lab57-editor-msg';
      var foot = document.createElement('div');
      foot.className = 'lab57-editor-foot';
      var saveBtn = document.createElement('button');
      saveBtn.type = 'button'; saveBtn.textContent = EduI18n.tr('^O Uložit');
      var closeBtn = document.createElement('button');
      closeBtn.type = 'button'; closeBtn.textContent = EduI18n.tr('^X Zavřít');
      foot.appendChild(saveBtn); foot.appendChild(closeBtn);
      box.appendChild(head); box.appendChild(warn); box.appendChild(textarea); box.appendChild(msg); box.appendChild(foot);
      document.body.appendChild(box);

      var state = { path: '', token: '', writable: true };

      function close() {
        box.hidden = true;
        input.focus();
      }
      function save() {
        if (!state.writable) { msg.textContent = EduI18n.tr('Soubor je jen ke čtení.'); return; }
        post('save', { path: state.path, token: state.token, content: textarea.value }).then(function (resp) {
          if (resp.__status === 419) { appendError(EduI18n.tr('Relace vypršela – obnov stránku (F5).')); return; }
          msg.textContent = resp.message || (resp.ok ? EduI18n.tr('Uloženo.') : (resp.error || EduI18n.tr('Chyba.')));
          if (resp.checks) renderChecklist(resp.checks);
          if (resp.solved) {
            showSolved(resp);
            appendSys(EduI18n.tr('✔ Úroveň vyřešena.'));
            // Aréna (žebříček závodu) poslouchá a hned se obnoví.
            document.dispatchEvent(new CustomEvent('lab57:solved', { detail: resp }));
          }
        }).catch(function () { msg.textContent = EduI18n.tr('Uložení se nezdařilo – zkus to znovu.'); });
      }
      saveBtn.addEventListener('click', save);
      closeBtn.addEventListener('click', close);
      textarea.addEventListener('keydown', function (e) {
        if (e.ctrlKey && (e.key === 'o' || e.key === 'O')) { e.preventDefault(); save(); }
        else if (e.ctrlKey && (e.key === 'x' || e.key === 'X')) { e.preventDefault(); close(); }
      });

      return {
        open: function (info) {
          state.path = info.path; state.token = info.token; state.writable = info.writable;
          nameSpan.textContent = info.name || info.path;
          textarea.value = info.content || '';
          msg.textContent = '';
          warn.hidden = !!info.writable;
          warn.textContent = EduI18n.tr('Soubor patří správci – uprav ho přes sudo nano.');
          box.hidden = false;
          textarea.focus();
        }
      };
    }

    // ---------------- zpracování odpovědi na op=run ----------------

    function applyRunResponse(resp, typedLine) {
      if (resp.clear) out.textContent = '';
      var shown = typeof resp.echo === 'string' ? resp.echo : typedLine;
      appendCmdLine((resp.prompt_before || promptEl.textContent) + ' ', shown);
      (resp.out || []).forEach(function (chunk) { appendChunk(chunk[0], chunk[1]); });
      (resp.tips || []).forEach(appendTip);
      if (resp.checks) renderChecklist(resp.checks);
      if (resp.net && resp.net.length) {
        animateNet(resp.net);
        document.dispatchEvent(new CustomEvent('lab57:net', { detail: { root: root, events: resp.net } }));
      }
      if (resp.reset) { out.textContent = ''; if (solvedBox) solvedBox.hidden = true; }
      if (resp.editor) editor.open(resp.editor);
      if (typeof resp.hints_used === 'number') updateHintCount(resp.hints_used);
      if (resp.solved) showSolved(resp);
      if (typeof resp.prompt === 'string') setPrompt(resp.prompt);
      scrollBottom();
    }

    function runLine(line) {
      if (busy) return Promise.resolve();
      setBusy(true);
      var promptBefore = promptEl.textContent;
      return post('run', { line: line }).then(function (resp) {
        setBusy(false);
        if (resp.__status === 419) { appendError(EduI18n.tr('Relace vypršela – obnov stránku (F5).')); scrollBottom(); return resp; }
        if (!resp.ok) { appendError(resp.error || EduI18n.tr('Příkaz selhal.')); scrollBottom(); return resp; }
        resp.prompt_before = promptBefore;
        applyRunResponse(resp, line);
        if (line.trim() !== '') { history.push(line); historyPos = history.length; }
        return resp;
      }).catch(function () {
        setBusy(false);
        appendError(EduI18n.tr('Spojení se serverem selhalo. Zkus to znovu.'));
        scrollBottom();
      });
    }

    // ---------------- odeslání z formuláře ----------------

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (busy) return;
      var line = input.value;
      input.value = '';
      runLine(line);
    });

    // v58 · A11Y-A2: Tab/Shift+Tab už není klávesnicová past. escapeArmed sleduje „Esc a pak Tab“ –
    // dokumentovaná úniková cesta z terminálu, viditelná i v nápovědě u vstupu (linux_v57_views.php).
    var escapeArmed = false;

    input.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape' && e.key !== 'Tab') escapeArmed = false;
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (history.length === 0) return;
        historyPos = Math.max(0, historyPos - 1);
        input.value = history[historyPos] || '';
        moveCaretEnd();
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (history.length === 0) return;
        historyPos = Math.min(history.length, historyPos + 1);
        input.value = history[historyPos] || '';
        moveCaretEnd();
      } else if (e.key === 'Escape') {
        escapeArmed = true;
      } else if (e.key === 'Tab') {
        // Shift+Tab vždy necháme projít (fokus zpět). Po Esc necháme projít i obyčejný Tab (fokus dál) –
        // to je ta zdokumentovaná úniková cesta. Jinak Tab zachytíme, jen když je opravdu co doplňovat.
        if (e.shiftKey || escapeArmed) { escapeArmed = false; return; }
        if (input.value.trim() === '') return;
        e.preventDefault();
        doComplete();
      } else if (e.ctrlKey && (e.key === 'l' || e.key === 'L')) {
        e.preventDefault();
        out.textContent = '';
      } else if (e.ctrlKey && (e.key === 'c' || e.key === 'C')) {
        e.preventDefault();
        appendCmdLine(promptEl.textContent + ' ', input.value + '^C');
        input.value = '';
        scrollBottom();
      } else if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
        e.preventDefault();
        input.value = '';
      }
    });

    function moveCaretEnd() {
      var v = input.value;
      input.value = '';
      input.value = v;
    }

    function doComplete() {
      if (busy) return;
      post('complete', { line: input.value }).then(function (resp) {
        if (resp.__status === 419 || !resp.ok) return;
        var c = resp.complete || {};
        var candidates = c.candidates || [];
        if (candidates.length === 1) {
          replaceLastWord(candidates[0]);
        } else if (candidates.length > 1) {
          appendCmdLine(promptEl.textContent + ' ', input.value);
          appendColumns(candidates);
          scrollBottom();
          if (c.common) replaceLastWord(c.common);
        }
      }).catch(function () {});
    }

    function replaceLastWord(word) {
      var v = input.value;
      var idx = v.length - (c_word(v)).length;
      input.value = v.slice(0, idx) + word;
    }
    function c_word(v) {
      var parts = v.split(/\s+/);
      return parts[parts.length - 1] || '';
    }

    // ---------------- mise tlačítka ----------------

    if (hintBtn) hintBtn.addEventListener('click', function () { runLine('hint'); });
    if (miseBtn) miseBtn.addEventListener('click', function () { runLine('mise'); });
    if (resetBtn) resetBtn.addEventListener('click', function () {
      if (!window.confirm(EduI18n.tr('Opravdu vrátit úroveň do původního stavu? Postup se ztratí.'))) return;
      post('reset').then(function (resp) {
        out.textContent = '';
        if (solvedBox) solvedBox.hidden = true;
        if (resp.prompt) setPrompt(resp.prompt);
        history = []; historyPos = 0;
        appendSys(EduI18n.tr('Úroveň se vrátila do původního stavu.'));
      }).catch(function () { appendError(EduI18n.tr('Reset se nezdařil.')); });
    });

    var codeInput = root.querySelector('[data-lab57-code-input]');
    var submitCodeBtn = root.querySelector('[data-lab57-submit-code]');
    if (submitCodeBtn) submitCodeBtn.addEventListener('click', function () {
      var v = (codeInput.value || '').trim();
      if (v === '') { codeInput.focus(); return; }
      runLine('submit ' + v);
    });

    var answerInput = root.querySelector('[data-lab57-answer-input]');
    var submitAnswerBtn = root.querySelector('[data-lab57-submit-answer]');
    if (submitAnswerBtn) submitAnswerBtn.addEventListener('click', function () {
      var v = (answerInput.value || '').trim();
      if (v === '') { answerInput.focus(); return; }
      runLine('answer ' + v);
    });

    var checkBtn = root.querySelector('[data-lab57-check]');
    if (checkBtn) checkBtn.addEventListener('click', function () { runLine('check'); });

    var submitGolfBtn = root.querySelector('[data-lab57-submit-golf]');
    if (submitGolfBtn) submitGolfBtn.addEventListener('click', function () { runLine('submit'); });

    if (missionToggle) missionToggle.addEventListener('click', function () {
      var expanded = missionToggle.getAttribute('aria-expanded') === 'true';
      missionToggle.setAttribute('aria-expanded', String(!expanded));
      root.classList.toggle('is-collapsed', expanded);
    });

    // dotykové tlačítko lišty
    if (touchbar) touchbar.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      input.focus();
      if (btn.dataset.lab57Key === 'Tab') { doComplete(); }
      else if (btn.dataset.lab57Key === 'Up') { input.value = history[Math.max(0, --historyPos)] || ''; }
      else if (btn.dataset.lab57Key === 'Down') { historyPos = Math.min(history.length, historyPos + 1); input.value = history[historyPos] || ''; }
      else if (btn.dataset.lab57Key === 'Ctrl+C') { appendCmdLine(promptEl.textContent + ' ', input.value + '^C'); input.value = ''; scrollBottom(); }
      else if (btn.dataset.lab57Insert) {
        var pos = input.selectionStart || input.value.length;
        input.value = input.value.slice(0, pos) + btn.dataset.lab57Insert + input.value.slice(pos);
        input.setSelectionRange(pos + 1, pos + 1);
      }
    });

    out.addEventListener('click', function () { input.focus(); });

    // ---------------- prvotní stav ----------------

    post('state').then(function (state) {
      if (state.__status === 419) { appendError(EduI18n.tr('Relace vypršela – obnov stránku (F5).')); return; }
      if (!state.ok) { appendError(state.error || EduI18n.tr('Úroveň se nepodařilo načíst.')); return; }
      var lvl = state.level || {};
      levelType = lvl.type || '';
      hintsTotal = lvl.hints_total || 0;
      updateHintCount(state.hints_used || 0);
      if (state.checks) renderChecklist(state.checks);
      if (state.topology) drawTopology(state.topology);
      setPrompt(state.prompt);
      history = (state.history || []).slice();
      historyPos = history.length;
      (state.tx || []).forEach(function (t) {
        appendCmdLine(t.p + ' ', t.c);
        if (t.o) appendChunk(1, t.o);
      });
      if (state.motd) appendSys(stripAnsi(state.motd));
      if (state.solved) showSolved({ learn: lvl.learn, points: 0, xp: 0, first_blood: false });
      // v58 · integrace jádra: kontextová lišta (role v týmovém kontextu) a adaptivní nápověda (LEARN, volitelné pole).
      if (state.context) document.dispatchEvent(new CustomEvent('lab57:context', { detail: { root: root, context: state.context } }));
      if (state.adaptive_hint) document.dispatchEvent(new CustomEvent('lab57:adaptive-hint', { detail: { root: root, hint: state.adaptive_hint } }));
      scrollBottom();

      var prefill = root.dataset.cmd || getQueryParam('cmd');
      if (prefill) { input.value = prefill; }
      input.focus();
    }).catch(function () {
      appendError(EduI18n.tr('Nepodařilo se spojit s Labem. Zkus obnovit stránku.'));
    });
  }

  function getQueryParam(name) {
    try {
      return new URLSearchParams(window.location.search).get(name) || '';
    } catch (e) { return ''; }
  }

  function ready() {
    document.querySelectorAll('[data-lab57]').forEach(initLab57);
    initManualSearch();
  }

  // ------------------------------------------------------------------
  // Vyhledávání v příručce (?view=prikazy)
  // ------------------------------------------------------------------

  function initManualSearch() {
    var input = document.querySelector('[data-lab57-manual-search]');
    if (!input) return;
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-lab57-manual-item]'));
    var cats = Array.prototype.slice.call(document.querySelectorAll('[data-lab57-manual-cat]'));
    var empty = document.querySelector('[data-lab57-manual-empty]');
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      var anyVisible = false;
      cats.forEach(function (cat) {
        var catItems = Array.prototype.slice.call(cat.querySelectorAll('[data-lab57-manual-item]'));
        var visibleInCat = 0;
        catItems.forEach(function (item) {
          var name = (item.dataset.name || '').toLowerCase();
          var summary = (item.dataset.summary || '').toLowerCase();
          var show = q === '' || name.indexOf(q) !== -1 || summary.indexOf(q) !== -1;
          item.style.display = show ? '' : 'none';
          if (show) visibleInCat++;
        });
        cat.style.display = visibleInCat > 0 ? '' : 'none';
        if (visibleInCat > 0) anyVisible = true;
      });
      if (empty) empty.hidden = anyVisible || q === '';
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();
})();
