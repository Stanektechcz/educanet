/* EDUCANET v58 · Týmové hry – společná infrastruktura (odpočet, polling, signály) a vykreslení
 * jednotlivých her (Štafeta, Bingo, Riskuj!, Přetahovaná, Správci sítě/webu, Úniková místnost).
 * DOM se plní jen přes textContent/createElement – nikdy innerHTML s daty ze serveru. Polling se
 * zpomalí ve skryté kartě (kromě projektoru) a při chybě sítě; zastaví se po konci hry. */
(function () {
  'use strict';

  // v59 · OPS-02 – shim: běží i na učitelských/projektorových stránkách bez assets/i18n-v58.js.
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  var ANNOUNCE_AT = [60, 30, 10];
  var MAX_BACKOFF = 30000;

  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function clockText(secs) { secs = Math.max(0, secs | 0); return Math.floor(secs / 60) + ':' + pad(secs % 60); }
  function clear(node) { while (node && node.firstChild) node.removeChild(node.firstChild); }
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = String(text);
    return n;
  }
  function btn(cls, text, handler) {
    var b = el('button', cls, text);
    b.type = 'button';
    b.addEventListener('click', handler);
    return b;
  }
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }
  function statusLabel(s) { return s === 'running' ? EduI18n.tr('Běží') : (s === 'paused' ? EduI18n.tr('Pauza') : (s === 'finished' ? EduI18n.tr('Skončila') : (s === 'archived' ? EduI18n.tr('Archiv') : EduI18n.tr('Připravuje se')))); }

  // -------------------------------------------------------------------------
  // Odpočet (role=timer): vizuálně každou sekundu, čtečkám jen jednou za minutu a v 60/30/10 s.
  // Server posílá jen "kolik zbývá teď" (remaining), ne cílový čas – při každém pollu se srovná znovu.
  // -------------------------------------------------------------------------
  function Countdown(node) {
    if (!node) return null;
    var valueEl = node.querySelector('[data-tg58-clock-value]');
    var announceEl = node.querySelector('[data-tg58-clock-announce]');
    var secs = null, lastMinute = null, announced = {}, timer = null;
    function announce(text) { if (announceEl) announceEl.textContent = text; }
    function markPassed() {
      announced = {};
      if (secs === null) return;
      ANNOUNCE_AT.forEach(function (s) { if (secs <= s) announced[s] = true; });
      lastMinute = Math.ceil(secs / 60);
    }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function tick() {
      if (secs === null) { stop(); return; }
      if (valueEl) valueEl.textContent = clockText(secs);
      node.classList.toggle('is-urgent', secs <= 60);
      var minute = Math.ceil(secs / 60);
      if (secs > 60 && minute !== lastMinute) { announce(EduI18n.trn({one: 'Zbývá {n} minuta.', few: 'Zbývá {n} minuty.', other: 'Zbývá {n} minut.'}, minute)); lastMinute = minute; }
      ANNOUNCE_AT.forEach(function (s) { if (secs <= s && !announced[s]) { announced[s] = true; announce(EduI18n.trn({one: 'Zbývá {n} sekunda.', few: 'Zbývá {n} sekundy.', other: 'Zbývá {n} sekund.'}, s)); } });
      if (secs <= 0) { announce(EduI18n.tr('Čas vypršel.')); stop(); node.dispatchEvent(new CustomEvent('tg58:timeup', { bubbles: true })); return; }
      secs--;
    }
    function set(remaining) {
      var changed = remaining !== secs;
      secs = (remaining === null || remaining === undefined) ? null : Math.max(0, remaining | 0);
      node.classList.toggle('is-open', secs === null);
      if (secs === null) { stop(); if (valueEl) valueEl.textContent = ''; return; }
      if (changed) markPassed();
      if (!timer) timer = setInterval(tick, 1000);
      tick();
    }
    var initial = node.getAttribute('data-remaining');
    set(initial === '' || initial === null ? null : parseInt(initial, 10));
    return { set: set, stop: stop };
  }

  // -------------------------------------------------------------------------
  // Polling s ústupem při chybách; ve skryté kartě se zpomalí (projektor běží dál).
  // -------------------------------------------------------------------------
  function Poller(task, interval, netEl, keepWhenHidden) {
    var delay = interval, timer = null, stopped = false, busy = false;
    function schedule(ms) { if (!stopped) timer = setTimeout(run, ms); }
    function note(text) { if (netEl) netEl.textContent = text || ''; }
    function run() {
      timer = null;
      if (stopped || busy) return;
      if (!keepWhenHidden && document.hidden) { schedule(interval); return; }
      busy = true;
      task().then(function () {
        busy = false; delay = interval; note('');
        schedule(interval);
      }, function (err) {
        busy = false;
        var own = err && err.own ? err.message : '';
        if (err && err.fatal) { stopped = true; note(own || EduI18n.tr('Data se nepodařilo načíst – obnov stránku.')); return; }
        delay = Math.min(MAX_BACKOFF, delay * 2);
        note(own || EduI18n.tr('Spojení se přerušilo, zkouším to znovu…'));
        schedule(delay);
      });
    }
    schedule(interval);
    return {
      now: function () { if (stopped || busy) return; if (timer) clearTimeout(timer); run(); },
      stop: function () { stopped = true; if (timer) clearTimeout(timer); timer = null; }
    };
  }

  function parseJson(res) {
    if (res.status === 419 || res.status === 401) return Promise.reject({ own: true, fatal: true, message: EduI18n.tr('Relace vypršela – obnov stránku.') });
    var type = res.headers.get('Content-Type') || '';
    if (type.indexOf('application/json') === -1) return Promise.reject(res.ok ? { own: true, fatal: true, message: EduI18n.tr('Nejsi přihlášen(a) – obnov stránku.') } : { status: res.status });
    return res.json().then(function (j) {
      if (!res.ok || !j || j.ok === false) return Promise.reject({ own: true, fatal: res.status === 403 || res.status === 404, message: (j && j.error) || EduI18n.tr('Data se nepodařilo načíst.') });
      return j;
    });
  }

  function apiPost(api, gameId, extra) {
    var csrf = csrfToken();
    var body = new URLSearchParams();
    body.set('csrf', csrf);
    body.set('game', gameId);
    Object.keys(extra || {}).forEach(function (k) { body.set(k, extra[k]); });
    return fetch(api, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }, body: body }).then(parseJson);
  }

  // -------------------------------------------------------------------------
  // Sdílené vykreslení (žebříčky / tabulky) – stejná struktura pro učitele i projektor.
  // -------------------------------------------------------------------------
  var COL_LABELS = {
    team: 'Tým', leg: 'Úsek', legs_total: 'Úseků celkem', finished: 'Dohráno', time_s: 'Čas',
    points: 'Body', solved: 'Vyřešeno', cells_total: 'Políček celkem', rows_complete: 'Řad', bonus_rows: 'Řad s bonusem',
    everyone_contributed: 'Přispěli všichni', escaped: 'Unikli'
  };
  function cellText(v) {
    if (typeof v === 'boolean') return v ? EduI18n.tr('ano') : '–';
    if (v === null || v === undefined) return '–';
    if (Array.isArray(v)) return v.length ? v.join(', ') : '–';
    return String(v);
  }
  function secsText(s) {
    if (s === null || s === undefined) return '–';
    return s >= 60 ? EduI18n.tr('{min} min {s} s', { min: Math.floor(s / 60), s: s % 60 }) : EduI18n.tr('{s} s', { s: s });
  }
  var HIDDEN_COLS = ['penalty_s', 'hints_used', 'roles'];

  function renderRowsTable(tbody, rows, hideExtra) {
    if (!tbody) return;
    clear(tbody);
    (rows || []).forEach(function (row) {
      var tr = el('tr');
      Object.keys(row).forEach(function (col) {
        if (hideExtra && HIDDEN_COLS.indexOf(col) !== -1) return;
        var v = row[col];
        tr.appendChild(el('td', '', /_s$/.test(col) && (v === null || typeof v === 'number') ? secsText(v) : cellText(v)));
      });
      tbody.appendChild(tr);
    });
  }

  function renderScoreList(list, scores) {
    if (!list) return;
    clear(list);
    (scores || []).forEach(function (s) {
      var li = el('li');
      li.appendChild(el('span', 'tg58-name', s.team));
      li.appendChild(el('span', 'tg58-pts', EduI18n.tr('{body} b.', { body: s.points })));
      list.appendChild(li);
    });
  }

  // -------------------------------------------------------------------------
  // Kvízová otázka (sdílená: Štafeta/Bingo/Riskuj!/Přetahovaná/Správci – grafická linie)
  // -------------------------------------------------------------------------
  function renderQuizQuestion(container, question, onAnswer, disabled) {
    clear(container);
    if (!question) { container.appendChild(el('p', 'tg58-muted', EduI18n.tr('Čekej na další otázku…'))); return; }
    container.appendChild(el('p', 'tg58-quiz-prompt', question.prompt));
    var opts = el('div', 'tg58-quiz-options');
    var send = function (value) { if (!disabled) onAnswer(value); };
    if (question.type === 'multi') {
      var form = el('form');
      var boxes = [];
      (question.options || []).forEach(function (opt) {
        var label = el('label');
        var cb = document.createElement('input');
        cb.type = 'checkbox'; cb.value = opt;
        boxes.push(cb);
        label.appendChild(cb);
        label.appendChild(document.createTextNode(' ' + opt));
        form.appendChild(label);
      });
      form.appendChild(btn('btn primary', EduI18n.tr('Odeslat'), function () {
        send(boxes.filter(function (b) { return b.checked; }).map(function (b) { return b.value; }));
      }));
      opts.appendChild(form);
    } else if (question.type === 'order') {
      container.appendChild(el('p', 'tg58-muted small', EduI18n.tr('Zapiš pořadí čísly oddělenými čárkou (1 = první z nabídky výše).')));
      (question.options || []).forEach(function (opt, i) { opts.appendChild(el('p', '', (i + 1) + '. ' + opt)); });
      var input0 = document.createElement('input');
      input0.type = 'text'; input0.setAttribute('aria-label', EduI18n.tr('Pořadí, např. 2,1,3'));
      opts.appendChild(input0);
      opts.appendChild(btn('btn primary', EduI18n.tr('Odeslat'), function () {
        var order = input0.value.split(',').map(function (s) { return parseInt(s.trim(), 10) - 1; }).map(function (i) { return question.options[i]; });
        send(order);
      }));
    } else if (question.options && question.options.length) {
      question.options.forEach(function (opt) { opts.appendChild(btn('', opt, function () { send(opt); })); });
    } else {
      var input = document.createElement('input');
      input.type = 'text'; input.setAttribute('aria-label', EduI18n.tr('Odpověď'));
      opts.appendChild(input);
      opts.appendChild(btn('btn primary', EduI18n.tr('Odeslat'), function () { send(input.value); }));
    }
    container.appendChild(opts);
    container.appendChild(el('p', 'tg58-quiz-feedback', ''));
  }
  function showFeedback(container, correct, extra) {
    var fb = container.querySelector('.tg58-quiz-feedback');
    if (!fb) return;
    fb.className = 'tg58-quiz-feedback ' + (correct ? 'is-correct' : 'is-wrong');
    fb.textContent = (correct ? EduI18n.tr('Správně!') : EduI18n.tr('Zatím ne, zkus to znovu.')) + (extra ? ' ' + extra : '');
  }

  // -------------------------------------------------------------------------
  // Jednotlivé hry – žákovský panel. Každá funkce (panel, game, ctx) vykreslí obsah data-tg58-game-panel.
  // ctx = { post(action, payload), refresh(), gameId }
  // -------------------------------------------------------------------------
  function renderRelay(panel, game, ctx) {
    clear(panel);
    panel.appendChild(el('p', '', game.finished ? EduI18n.tr('Úsek {usek} z {celkem} – hotovo!', { usek: game.leg, celkem: game.legs_total }) : EduI18n.tr('Úsek {usek} z {celkem}', { usek: game.leg, celkem: game.legs_total })));
    if (game.finished) { panel.appendChild(el('p', 'tg58-quiz-feedback is-correct', EduI18n.tr('Váš tým doběhl do cíle. Skvělá práce!'))); return; }
    if (game.mode === 'lab') {
      panel.appendChild(el('p', 'tg58-muted', EduI18n.tr('Řeš úkol v terminálu výše{nazev}. Po vyřešení úsek sám postoupí.', { nazev: game.level_title ? ' (' + game.level_title + ')' : '' })));
    } else {
      var box = el('div', 'tg58-quiz-panel');
      panel.appendChild(box);
      renderQuizQuestion(box, game.question, function (answer) {
        ctx.post('relay_answer', { answer: JSON.stringify(answer) }).then(function (r) { showFeedback(box, r.correct); ctx.refresh(); });
      });
    }
    if (game.can_help) panel.appendChild(btn('btn secondary', EduI18n.tr('Poprosit o pomoc spoluhráče (+30 s)'), function () { ctx.post('relay_help', {}).then(ctx.refresh); }));
  }

  function renderBingo(panel, game, ctx) {
    clear(panel);
    panel.appendChild(el('p', '', EduI18n.tr('Body: {body} · celé řady: {rady} (s bonusem: {bonus})', { body: game.points, rady: game.rows_complete, bonus: game.bonus_rows })));
    if (!game.everyone_contributed) panel.appendChild(el('p', 'tg58-muted small', EduI18n.tr('Bonus za řadu se počítá, až políčko vyřeší každý člen týmu aspoň jednou.')));
    var grid = el('div', 'tg58-bingo-grid');
    grid.style.setProperty('--tg-bingo-size', String(game.size));
    var detail = el('div', 'tg58-quiz-panel');
    (game.cells || []).forEach(function (cell) {
      var label = cell.kind === 'free' ? EduI18n.tr('Zdarma') : cell.label;
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'tg58-bingo-cell' + (cell.solved ? ' is-solved' : '') + (cell.kind === 'free' ? ' is-free' : '');
      b.textContent = label;
      b.disabled = cell.solved || cell.kind === 'free';
      b.addEventListener('click', function () {
        if (cell.kind === 'lab') { window.location.href = '?view=hry&hra=' + encodeURIComponent(ctx.gameId) + '&bunka=' + encodeURIComponent(cell.id); return; }
        clear(detail);
        if (cell.kind === 'quiz' && cell.question) {
          renderQuizQuestion(detail, cell.question, function (answer) {
            ctx.post('bingo_mark', { id: cell.id, answer: JSON.stringify(answer) }).then(function (r) { showFeedback(detail, r.correct); ctx.refresh(); });
          });
        }
      });
      grid.appendChild(b);
    });
    panel.appendChild(grid);
    panel.appendChild(detail);
  }

  function renderJeopardy(panel, game, ctx) {
    clear(panel);
    panel.appendChild(el('p', '', game.turn_team ? (game.my_turn ? EduI18n.tr('Na tahu: {tym} (vy!)', { tym: game.turn_team }) : EduI18n.tr('Na tahu: {tym}', { tym: game.turn_team })) : ''));
    if (game.question) {
      var box = el('div', 'tg58-quiz-panel');
      panel.appendChild(el('p', '', EduI18n.tr('Hodnota: {body} b.', { body: game.question.value })));
      if (game.question.answered) { box.appendChild(el('p', 'tg58-muted', EduI18n.tr('Odpověď je odeslaná, čekej na uzavření otázky.'))); }
      else renderQuizQuestion(box, game.question, function (answer) {
        ctx.post('jeopardy_answer', { answer: JSON.stringify(answer) }).then(function () { box.querySelector('.tg58-quiz-options') && (box.querySelector('.tg58-quiz-options').hidden = true); showFeedback(box, true, EduI18n.tr('(výsledek se ukáže po uzavření otázky)')); });
      });
      panel.appendChild(box);
    } else if (game.my_turn) {
      var grid = el('div', 'tg58-jeopardy-board');
      grid.style.setProperty('--tg-jeop-size', String(game.board.length));
      grid.appendChild(el('div'));
      game.board.forEach(function (cat) { grid.appendChild(el('div', 'tg58-jeopardy-cat', cat.category)); });
      var maxCells = Math.max.apply(null, game.board.map(function (c) { return c.cells.length; }));
      for (var r = 0; r < maxCells; r++) {
        grid.appendChild(el('div'));
        game.board.forEach(function (cat) {
          var cell = cat.cells[r];
          var b = document.createElement('button');
          b.type = 'button'; b.className = 'tg58-jeopardy-cell'; b.textContent = String(cell.value);
          b.setAttribute('aria-label', EduI18n.tr('{kategorie}, {body} bodů', { kategorie: cat.category, body: cell.value }));
          b.disabled = cell.used;
          b.addEventListener('click', function () { ctx.post('jeopardy_pick', { cat: cell.cat, cell: cell.cell }).then(ctx.refresh); });
          grid.appendChild(b);
        });
      }
      panel.appendChild(grid);
    } else {
      panel.appendChild(el('p', 'tg58-muted', EduI18n.tr('Čekej, až váš tým bude na tahu.')));
    }
    if (game.last) panel.appendChild(el('p', 'tg58-muted small', game.last.explain
      ? EduI18n.tr('Poslední otázka ({kategorie}): {odpoved} – {vysvetleni}', { kategorie: game.last.category, odpoved: cellText(game.last.answer), vysvetleni: game.last.explain })
      : EduI18n.tr('Poslední otázka ({kategorie}): {odpoved}', { kategorie: game.last.category, odpoved: cellText(game.last.answer) })));
    renderScoreList(panel.appendChild(el('ol', 'tg58-board')), game.scores);
  }

  function renderTug(panel, game, ctx) {
    clear(panel);
    var hasRemaining = game.round_remaining !== null && game.round_remaining !== undefined;
    panel.appendChild(el('p', '', hasRemaining
      ? EduI18n.tr('Kolo {kolo} z {celkem} · zbývá {cas}', { kolo: game.round, celkem: game.rounds_total, cas: secsText(game.round_remaining) })
      : EduI18n.tr('Kolo {kolo} z {celkem}', { kolo: game.round, celkem: game.rounds_total })));
    // v58 A11Y-A3: stav lana nesmí nést informaci jen pozicí prvku – vždy k němu jde i textový popis a aria-label.
    var share = Math.max(-1, Math.min(1, game.position || 0)) * (game.my_side || 1);
    var leadPct = Math.round(Math.abs(share) * 100);
    var stateText = leadPct === 0 ? EduI18n.tr('Vyrovnaný stav.') : (share > 0 ? EduI18n.tr('Váš tým vede o {procenta} %.', { procenta: leadPct }) : EduI18n.tr('Soupeř vede o {procenta} %.', { procenta: leadPct }));
    var rope = el('div', 'tg58-tug-rope');
    rope.setAttribute('role', 'img');
    rope.setAttribute('aria-label', EduI18n.tr('Přetahovaná – {stav}', { stav: stateText }));
    var i = el('i');
    i.style.left = (50 + share * 50) + '%';
    rope.appendChild(i);
    panel.appendChild(rope);
    panel.appendChild(el('p', 'tg58-tug-state', stateText));
    var box = el('div', 'tg58-quiz-panel');
    panel.appendChild(box);
    if (!game.question) {
      box.appendChild(el('p', 'tg58-muted', EduI18n.tr('Připravuje se další otázka…')));
      ctx.post('tug_next', {}).then(ctx.refresh);
    } else {
      renderQuizQuestion(box, game.question, function (answer) {
        if (!game.can_answer) return;
        ctx.post('tug_answer', { answer: JSON.stringify(answer) }).then(function (r) {
          showFeedback(box, r.correct, r.explain);
          setTimeout(function () { ctx.post('tug_next', {}).then(ctx.refresh); }, 900);
        });
      }, !game.can_answer);
    }
  }

  function renderNetadmin(panel, game, ctx) {
    clear(panel);
    panel.appendChild(el('p', '', EduI18n.tr('Vaše body: {body} · vlna {vlna}/{celkem}', { body: game.my_points, vlna: game.wave, celkem: game.waves_total })));
    var grid = el('div', 'tg58-netadmin-map');
    var detail = el('div', 'tg58-quiz-panel');
    (game.nodes || []).forEach(function (node, i) {
      if (!node.visible) { grid.appendChild(el('div', 'tg58-node is-hidden', '?')); return; }
      var cls = 'tg58-node' + (node.mine ? ' is-mine' : (node.owner ? ' is-other' : ' is-free'));
      if (node.level_id) {
        var a = document.createElement('a');
        a.className = cls; a.href = '?view=hry&hra=' + encodeURIComponent(ctx.gameId) + '&uzel=' + encodeURIComponent(node.level_id);
        a.textContent = node.owner ? EduI18n.tr('Uzel {cislo} ({vlastnik})', { cislo: i + 1, vlastnik: node.owner }) : EduI18n.tr('Uzel {cislo}', { cislo: i + 1 });
        grid.appendChild(a);
      } else {
        var b = document.createElement('button');
        b.type = 'button'; b.className = cls; b.textContent = node.owner ? EduI18n.tr('Uzel {cislo} ({vlastnik})', { cislo: i + 1, vlastnik: node.owner }) : EduI18n.tr('Uzel {cislo}', { cislo: i + 1 });
        b.addEventListener('click', function () {
          clear(detail);
          if (node.question) renderQuizQuestion(detail, node.question, function (answer) {
            ctx.post('netadmin_answer', { node_id: node.id, answer: JSON.stringify(answer) }).then(function (r) { showFeedback(detail, r.correct); ctx.refresh(); });
          });
        });
        grid.appendChild(b);
      }
    });
    panel.appendChild(grid);
    panel.appendChild(detail);
    renderScoreList(panel.appendChild(el('ol', 'tg58-board')), game.scores);
  }

  function renderEscape(panel, game, ctx) {
    clear(panel);
    if (game.escaped) { panel.appendChild(el('p', 'tg58-quiz-feedback is-correct', EduI18n.tr('Váš tým unikl! 🎉'))); return; }
    panel.appendChild(el('p', 'tg58-escape-role', EduI18n.tr('Tvoje role: {role}', { role: game.role_label || '–' })));
    if (game.mode === 'lab') {
      panel.appendChild(el('p', 'tg58-muted', EduI18n.tr('Najdi svou stopu v terminálu výše a řekni ji nahlas týmu.')));
    } else if (game.gate_done) {
      var fragP = el('p', '', EduI18n.tr('Tvoje část kódu: '));
      fragP.appendChild(el('strong', '', game.fragment));
      panel.appendChild(fragP);
      panel.appendChild(el('p', 'tg58-muted small', EduI18n.tr('Řekni ji nahlas týmu – appka chat nemá.')));
    } else {
      var box = el('div', 'tg58-quiz-panel');
      panel.appendChild(box);
      renderQuizQuestion(box, game.question, function (answer) {
        ctx.post('escape_answer', { answer: JSON.stringify(answer) }).then(function (r) { showFeedback(box, r.correct); ctx.refresh(); });
      });
    }
    if (game.can_hint) panel.appendChild(btn('btn secondary', EduI18n.tr('Použít týmovou nápovědu (+120 s, zbývá {n})', { n: game.hints_max - game.hints_used }), function () { ctx.post('escape_hint', {}).then(ctx.refresh); }));
    var lockWrap = el('div', 'tg58-escape-lock');
    lockWrap.appendChild(el('h3', '', EduI18n.tr('Zámek')));
    lockWrap.appendChild(el('p', 'tg58-muted small', EduI18n.tr('Poskládejte 4 části kódu v pořadí: {poradi}.', { poradi: game.roles_order.join(' → ') })));
    var parts = el('div', 'tg58-escape-lock-parts');
    var inputs = game.roles_order.map(function (label) {
      var wrap = el('label', '', label);
      var input = document.createElement('input');
      input.maxLength = 8; input.setAttribute('aria-label', label);
      wrap.appendChild(input);
      parts.appendChild(wrap);
      return input;
    });
    lockWrap.appendChild(parts);
    var lockMsg = el('p', 'tg58-quiz-feedback');
    lockWrap.appendChild(btn('btn primary', EduI18n.tr('Otevřít zámek'), function () {
      ctx.post('escape_unlock', { parts: JSON.stringify(inputs.map(function (i) { return i.value; })) }).then(function (r) {
        lockMsg.className = 'tg58-quiz-feedback ' + (r.correct ? 'is-correct' : 'is-wrong');
        lockMsg.textContent = r.correct ? EduI18n.tr('Zámek se otevřel!') : EduI18n.tr('Kód nesedí – zkontrolujte to s týmem.');
        if (r.correct) ctx.refresh();
      });
    }));
    lockWrap.appendChild(lockMsg);
    panel.appendChild(lockWrap);
  }

  var GAME_RENDERERS = { relay: renderRelay, bingo: renderBingo, jeopardy: renderJeopardy, tug: renderTug, netadmin: renderNetadmin, escape: renderEscape };

  // -------------------------------------------------------------------------
  // Žák: stránka hry
  // -------------------------------------------------------------------------
  function initStudent(root) {
    var gameId = root.getAttribute('data-game');
    var type = root.getAttribute('data-type');
    var api = root.getAttribute('data-api') || 'teamgames_v58_api.php';
    var net = root.querySelector('[data-tg58-net]');
    var clock = Countdown(root.querySelector('[data-tg58-countdown]'));
    var panel = root.querySelector('[data-tg58-game-panel]');
    var statusText = root.querySelector('[data-tg58-status-text]');
    var poller = null;
    var status = root.getAttribute('data-status');

    function post(action, payload) { return apiPost(api, gameId, Object.assign({ op: 'action', action: action }, payload)); }
    var ctx = { post: post, gameId: gameId, refresh: function () { poller && poller.now(); } };

    function render(state) {
      if (clock) clock.set(state.remaining);
      if (state.status !== status) { status = state.status; root.setAttribute('data-status', status); root.className = root.className.replace(/is-\S+/, '') + ' is-' + status; }
      if (statusText) statusText.textContent = statusLabel(status);
      var teamEl = root.querySelector('.tg58-team');
      if (teamEl) {
        clear(teamEl);
        if (state.team) {
          var p = el('p');
          p.appendChild(el('strong', '', EduI18n.tr('Tým: {jmeno}', { jmeno: state.team.name })));
          if (state.team.mates && state.team.mates.length) p.appendChild(document.createTextNode(' · ' + EduI18n.tr('spoluhráči: {jmena}', { jmena: state.team.mates.join(', ') })));
          teamEl.appendChild(p);
        } else {
          teamEl.appendChild(el('p', '', state.waiting || EduI18n.tr('Zatím nejsi v žádném týmu.')));
        }
      }
      var feed = root.querySelector('[data-tg58-signal-feed]');
      if (feed) {
        clear(feed);
        (state.signals || []).forEach(function (s) { feed.appendChild(el('li', '', s.label)); });
        if (!state.signals || !state.signals.length) feed.appendChild(el('li', 'is-empty', EduI18n.tr('Zatím žádné.')));
      }
      if (panel && state.team && state.game && GAME_RENDERERS[type]) {
        panel.removeAttribute('data-tg58-ssr');
        GAME_RENDERERS[type](panel, state.game, ctx);
      }
      if (status === 'finished') poller && poller.stop();
    }

    var lastVersion = '';
    function poll() {
      return apiPost(api, gameId, { op: 'state', v: lastVersion }).then(function (j) {
        if (j.changed === false) return;
        lastVersion = j.version;
        render(j.state);
      });
    }
    poller = Poller(poll, 3000, net, false);
    root.addEventListener('tg58:timeup', function () { setTimeout(function () { poller.now(); }, 1200); });

    root.querySelectorAll('[data-tg58-signal]').forEach(function (b) {
      b.addEventListener('click', function () {
        apiPost(api, gameId, { op: 'signal', kind: b.getAttribute('data-tg58-signal') }).then(function () { poller.now(); }, function (err) { if (net) net.textContent = err.message || EduI18n.tr('Signál se nepodařilo odeslat.'); });
      });
    });
  }

  // -------------------------------------------------------------------------
  // Učitel: živý panel
  // -------------------------------------------------------------------------
  function initTeacher(root) {
    var url = root.getAttribute('data-poll');
    var status = root.getAttribute('data-status');
    var clock = Countdown(root.querySelector('[data-tg58-countdown]')) || root.querySelector('[data-tg58-countdown]');
    if (!url || (status !== 'running' && status !== 'paused')) return;
    var lastVersion = '';
    var poller = Poller(function () {
      return fetch(url + '&v=' + encodeURIComponent(lastVersion), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(parseJson).then(function (d) {
        if (d.changed === false) return;
        lastVersion = d.version;
        var badge = root.querySelector('.tg58-badge');
        if (badge) { badge.textContent = statusLabel(d.status); badge.className = 'tg58-badge is-' + d.status; }
        if (d.status !== 'running' && d.status !== 'paused') { poller.stop(); return; }
        var game = d.game || {};
        if (game.rows) renderRowsTable(root.querySelector('[data-tg58-t-rows]'), game.rows, false);
        if (game.scores) renderScoreList(root.querySelector('[data-tg58-t-scores]'), game.scores);
        var sigList = root.querySelector('[data-tg58-t-signals]');
        if (sigList) {
          clear(sigList);
          (d.signals || []).forEach(function (s) { sigList.appendChild(el('li', '', s.team + ': ' + s.label)); });
          if (!d.signals || !d.signals.length) sigList.appendChild(el('li', 'is-empty', 'Zatím žádné.'));
        }
      });
    }, 4000, root.querySelector('[data-tg58-net]'), false);
  }

  // -------------------------------------------------------------------------
  // Projektor
  // -------------------------------------------------------------------------
  function initProjector(root) {
    var url = root.getAttribute('data-poll');
    var clock = Countdown(document.querySelector('[data-tg58-countdown]'));
    if (!url || root.getAttribute('data-status') === 'finished') return;
    var lastVersion = '';
    var poller = Poller(function () {
      return fetch(url + '&v=' + encodeURIComponent(lastVersion), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(parseJson).then(function (d) {
        if (d.changed === false) return;
        lastVersion = d.version;
        if (clock) clock.set(d.remaining);
        var statusText = document.querySelector('[data-tg58-status-text]');
        if (statusText) statusText.textContent = statusLabel(d.status);
        root.setAttribute('data-status', d.status);
        var game = d.game || {};
        var rows = root.querySelector('[data-tg58-proj-rows]');
        if (rows) renderRowsTable(rows, game.rows, true);
        var scores = root.querySelector('[data-tg58-proj-scores]');
        if (scores) renderScoreList(scores, game.scores);
        var rope = root.querySelector('[data-tg58-tug-rope]');
        if (rope && game.position !== undefined) rope.querySelector('i').style.left = (50 + Math.max(-1, Math.min(1, game.position)) * 50) + '%';
        if (d.status === 'finished') poller.stop();
      });
    }, 2000, root.querySelector('[data-tg58-net]'), true);
  }

  // -------------------------------------------------------------------------
  // Formulář nové hry (přepínání polí podle typu) a potvrzovací dialogy
  // -------------------------------------------------------------------------
  function initCreateForm(form) {
    var select = form.querySelector('[data-tg58-type-select]');
    if (!select) return;
    function apply() {
      form.querySelectorAll('[data-tg58-type-field]').forEach(function (f) { f.hidden = f.getAttribute('data-tg58-type-field') !== select.value; });
    }
    select.addEventListener('change', apply);
    apply();
  }

  document.addEventListener('submit', function (e) {
    var form = e.target && e.target.closest ? e.target.closest('[data-tg58-confirm]') : null;
    if (form && !window.confirm(form.getAttribute('data-tg58-confirm'))) e.preventDefault();
  });

  function boot() {
    document.querySelectorAll('[data-tg58-app]').forEach(initStudent);
    document.querySelectorAll('[data-tg58-teacher]').forEach(initTeacher);
    document.querySelectorAll('[data-tg58-projector]').forEach(initProjector);
    document.querySelectorAll('[data-tg58-create]').forEach(initCreateForm);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
