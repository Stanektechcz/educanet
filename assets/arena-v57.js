/* EDUCANET v57 · Aréna – odpočet, žebříček žáka, živý panel učitele a projektor.
 * DOM se plní jen přes textContent. Polling se zastaví po konci závodu, při chybě sítě zpomalí. */
(function () {
  'use strict';

  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  var ANNOUNCE_AT = [60, 30, 10];
  var MAX_BACKOFF = 30000;

  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function clockText(secs) { secs = Math.max(0, secs | 0); return Math.floor(secs / 60) + ':' + pad(secs % 60); }
  function timeText(iso) { var d = new Date(iso); return isNaN(d.getTime()) ? '–' : pad(d.getHours()) + ':' + pad(d.getMinutes()); }
  function timeEl(iso) { var t = el('time', '', timeText(iso)); if (iso) t.setAttribute('datetime', iso); return t; }
  // Časy vykreslené serverem (časové pásmo serveru) převedeme na místní čas prohlížeče.
  function localizeTimes(root) {
    Array.prototype.forEach.call((root || document).querySelectorAll('.arena57 time[datetime], .arena57-projector time[datetime]'), function (t) { t.textContent = timeText(t.getAttribute('datetime')); });
  }
  function minutesLeftText(n) { return EduI18n.trn({one: '{n} minuta', few: '{n} minuty', other: '{n} minut'}, n); }
  function secsText(s) { if (s === null || s === undefined) return '–'; return s >= 60 ? EduI18n.tr('{min} min {s} s', {min: Math.floor(s / 60), s: s % 60}) : EduI18n.tr('{s} s', {s: s}); }
  function clear(node) { while (node && node.firstChild) node.removeChild(node.firstChild); }
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = String(text);
    return n;
  }
  function statusLabel(s) { return s === 'live' ? EduI18n.tr('Běží') : (s === 'finished' ? EduI18n.tr('Skončil') : EduI18n.tr('Připravený')); }

  // -------------------------------------------------------------------------
  // Odpočet: vizuálně každou sekundu, čtečkám jen jednou za minutu a v 60/30/10 s
  // -------------------------------------------------------------------------
  function Countdown(node) {
    if (!node) return null;
    var valueEl = node.querySelector('[data-arena57-clock-value]');
    var announceEl = node.querySelector('[data-arena57-clock-announce]');
    var ends = NaN, skew = 0, status = node.getAttribute('data-status') || 'draft';
    var lastMinute = null, announced = {}, timer = null;

    function remaining() { return Math.max(0, Math.round((ends - (Date.now() + skew)) / 1000)); }
    function announce(text) { if (announceEl) announceEl.textContent = text; }
    function markPassed() {
      var r = remaining();
      announced = {};
      ANNOUNCE_AT.forEach(function (s) { if (r <= s) announced[s] = true; });
      lastMinute = Math.ceil(r / 60);
    }
    function tick() {
      if (status !== 'live' || isNaN(ends)) return;
      var r = remaining();
      if (valueEl) valueEl.textContent = clockText(r);
      node.classList.toggle('is-urgent', r <= 60);
      var minute = Math.ceil(r / 60);
      if (r > 60 && minute !== lastMinute) { announce(EduI18n.tr('Zbývá {mins}.', {mins: minutesLeftText(minute)})); lastMinute = minute; }
      ANNOUNCE_AT.forEach(function (s) {
        if (r <= s && !announced[s]) { announced[s] = true; announce(EduI18n.tr('Zbývá {n} sekund.', {n: s})); }
      });
      if (r === 0) { announce(EduI18n.tr('Čas vypršel.')); stopTimer(); node.dispatchEvent(new CustomEvent('arena57:timeup', { bubbles: true })); }
    }
    function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }
    function set(endsIso, nowSecs, st) {
      var parsed = Date.parse(endsIso || '');
      var changed = parsed !== ends;
      ends = parsed;
      if (typeof nowSecs === 'number' && !isNaN(nowSecs)) skew = nowSecs * 1000 - Date.now();
      status = st || status;
      node.setAttribute('data-status', status);
      node.classList.toggle('is-stopped', status !== 'live');
      if (changed) markPassed();
      if (status === 'live' && !isNaN(ends)) {
        if (!timer) timer = setInterval(tick, 1000);
        tick();
      } else {
        stopTimer();
        if (status === 'finished' && valueEl) valueEl.textContent = '0:00';
      }
    }
    set(node.getAttribute('data-ends'), parseInt(node.getAttribute('data-now'), 10), status);
    return { set: set, stop: stopTimer };
  }

  // -------------------------------------------------------------------------
  // Polling s ústupem při chybách a pauzou ve skryté kartě
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

  // -------------------------------------------------------------------------
  // Společné vykreslení tabulek (zrcadlí PHP)
  // -------------------------------------------------------------------------
  function renderRows(list, rows, empty) {
    if (!list) return;
    clear(list);
    if (!rows || !rows.length) { list.appendChild(el('li', 'arena57-row is-empty', empty)); return; }
    rows.forEach(function (r) {
      var li = el('li', 'arena57-row' + (r.me ? ' is-me' : ''));
      li.appendChild(el('b', 'arena57-rank', r.rank !== null && r.rank !== undefined ? r.rank + '.' : '–'));
      var name = el('span', 'arena57-name', r.name);
      if (r.me) { name.appendChild(document.createTextNode(' ')); name.appendChild(el('small', '', '(' + EduI18n.tr('ty') + ')')); }
      if (r.members && r.members.length) name.appendChild(el('small', 'arena57-members', r.members.join(', ')));
      li.appendChild(name);
      li.appendChild(el('span', 'arena57-pts', EduI18n.tr('{n} b', {n: r.points})));
      li.appendChild(el('small', 'arena57-solved', EduI18n.tr('{n} úl.', {n: r.solved})));
      list.appendChild(li);
    });
  }

  function renderFeed(list, feed, empty) {
    if (!list) return;
    clear(list);
    if (!feed || !feed.length) { list.appendChild(el('li', 'is-empty', empty)); return; }
    feed.forEach(function (f) {
      var li = el('li', f.me ? 'is-me' : '');
      li.appendChild(timeEl(f.at));
      li.appendChild(document.createTextNode(' '));
      li.appendChild(el('strong', '', f.name));
      li.appendChild(document.createTextNode(' ' + EduI18n.tr('první vyřešil(a)') + ' '));
      li.appendChild(el('em', '', f.level));
      list.appendChild(li);
    });
  }

  function renderGoal(root, goal) {
    var box = root.querySelector('[data-arena57-goal]');
    if (!box || !goal || !goal.target) return;
    var text = box.querySelector('[data-arena57-goal-text]');
    var bar = box.querySelector('[data-arena57-goal-bar]');
    if (text) text.textContent = EduI18n.tr('{done} / {target} vyřešených úloh', {done: goal.done, target: goal.target});
    if (bar) {
      bar.setAttribute('aria-valuenow', String(goal.pct));
      var fill = bar.querySelector('i');
      if (fill) fill.style.width = Math.max(0, Math.min(100, goal.pct)) + '%';
    }
    box.classList.toggle('is-done', goal.done >= goal.target);
  }

  // -------------------------------------------------------------------------
  // Žák: stránka závodu
  // -------------------------------------------------------------------------
  function initStudent(root) {
    var raceId = root.getAttribute('data-race');
    var status = root.getAttribute('data-status');
    var api = root.getAttribute('data-api') || 'lab_v57_api.php';
    var meta = document.querySelector('meta[name="csrf-token"]');
    var csrf = meta ? meta.getAttribute('content') : '';
    var net = root.querySelector('[data-arena57-net]');
    var clock = Countdown(root.querySelector('[data-arena57-countdown]'));
    var poller = null;
    var levelOpen = /[?&]uroven=/.test(window.location.search);

    function finishNotice() {
      if (!levelOpen) { window.location.reload(); return; }
      if (!net) return;
      clear(net);
      net.appendChild(document.createTextNode(EduI18n.tr('Závod skončil.') + ' '));
      var a = el('a', '', EduI18n.tr('Zobrazit výsledky →'));
      a.href = '?view=lab&zavod=' + encodeURIComponent(raceId);
      net.appendChild(a);
    }

    function render(board) {
      if (board.error) return Promise.reject({ own: true, fatal: true, message: board.error });
      if (clock) clock.set(board.ends_at, board.now, board.status);
      if (board.status !== status) {
        var prev = status;
        status = board.status;
        root.setAttribute('data-status', status);
        if (prev === 'draft' && status === 'live') { window.location.reload(); return; }
        if (status === 'finished') { poller && poller.stop(); finishNotice(); }
      }
      var me = board.me || {};
      var rankEl = root.querySelector('[data-arena57-my-rank]');
      var ptsEl = root.querySelector('[data-arena57-my-points]');
      if (rankEl) rankEl.textContent = me.rank ? me.rank + '.' : '–';
      if (ptsEl) ptsEl.textContent = String(me.points || 0);
      renderRows(root.querySelector('[data-arena57-rows]'), board.rows, EduI18n.tr('Zatím nikdo nemá body. Buď první!'));
      renderRows(root.querySelector('[data-arena57-teams]'), board.teams, EduI18n.tr('Týmy se ukážou po startu.'));
      renderFeed(root.querySelector('[data-arena57-feed]'), board.feed, EduI18n.tr('Zatím nic – první vyřešení úlohy dává bonus ×1,25.'));
      renderGoal(root, board.goal);
      (me.levels || []).forEach(function (id) {
        var link = root.querySelector('.arena57-level[data-level="' + (window.CSS && CSS.escape ? CSS.escape(id) : id) + '"]');
        if (!link || link.classList.contains('is-solved')) return;
        link.classList.add('is-solved');
        var mark = link.querySelector('[data-arena57-level-mark]');
        if (mark) { mark.textContent = '✓'; mark.setAttribute('aria-label', EduI18n.tr('vyřešeno')); }
      });
    }

    function fetchBoard() {
      var body = new URLSearchParams();
      body.set('csrf', csrf); body.set('op', 'board'); body.set('ctx', 'race:' + raceId); body.set('level', 'sandbox');
      return fetch(api, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }, body: body })
        .then(parseJson).then(function (j) { return render(j.board || {}); });
    }

    if (status === 'finished') return;
    poller = Poller(fetchBoard, 5000, net, false);
    root.addEventListener('arena57:timeup', function () { setTimeout(function () { poller.now(); }, 1500); });
    // Po vyřešení úlohy (terminál ukáže box „vyřešeno“) obnovíme žebříček hned.
    document.addEventListener('lab57:solved', function () { poller.now(); });
    var solvedBox = root.querySelector('[data-lab57-solved]');
    if (solvedBox && window.MutationObserver) {
      new MutationObserver(function () { if (!solvedBox.hidden) poller.now(); }).observe(solvedBox, { attributes: true, attributeFilter: ['hidden'] });
    }
  }

  // -------------------------------------------------------------------------
  // Učitel: živý panel závodu
  // -------------------------------------------------------------------------
  function cell(tr, text) { tr.appendChild(el('td', '', text)); }

  function renderTeacherBoard(body, board) {
    if (!body) return;
    clear(body);
    if (!board.length) { var tr0 = el('tr'); var td = el('td', 'arena57-muted', EduI18n.tr('Na soupisce třídy zatím nikdo není.')); td.colSpan = 6; tr0.appendChild(td); body.appendChild(tr0); return; }
    board.forEach(function (r) {
      var tr = el('tr', r.active ? '' : 'is-idle');
      cell(tr, r.rank ? r.rank + '.' : '–'); cell(tr, r.name); cell(tr, r.points); cell(tr, r.solved); cell(tr, r.cmds);
      cell(tr, r.level || (r.active ? '' : EduI18n.tr('nezapojen(a)')));
      body.appendChild(tr);
    });
  }

  function renderList(list, items, empty, build) {
    if (!list) return;
    clear(list);
    if (!items || !items.length) { list.appendChild(el('li', 'is-empty', empty)); return; }
    items.forEach(function (it) { list.appendChild(build(it)); });
  }

  function renderTeacher(root, d, clock) {
    var status = d.race.status;
    if (clock) clock.set(d.race.ends_at, d.race.now, status);
    var badge = root.querySelector('[data-arena57-status-badge]');
    if (badge) { badge.textContent = statusLabel(status); badge.className = 'arena57-badge is-' + status; }
    root.setAttribute('data-status', status);
    if (status !== 'live') Array.prototype.forEach.call(root.querySelectorAll('[data-arena57-live-only]'), function (f) { f.hidden = true; });
    renderGoal(root, d.goal);
    renderTeacherBoard(root.querySelector('[data-arena57-t-board]'), d.board || []);
    renderList(root.querySelector('[data-arena57-t-stuck]'), d.stuck, EduI18n.tr('Nikdo – zatím všichni postupují.'), function (s) {
      var li = el('li');
      li.appendChild(el('strong', '', s.name));
      li.appendChild(document.createTextNode(' · ' + s.level + ' · ' + EduI18n.tr('{min} min, {cmds} příkazů ({failed} s chybou)', {min: s.minutes, cmds: s.cmds, failed: s.failed})));
      li.appendChild(el('br'));
      li.appendChild(el('code', '', (s.recent || []).join(' ⏎ ')));
      return li;
    });
    renderList(root.querySelector('[data-arena57-t-alerts]'), d.alerts, EduI18n.tr('Žádné – nikdo nezkoušel cizí kód.'), function (a) {
      var li = el('li');
      li.appendChild(timeEl(a.at));
      li.appendChild(document.createTextNode(' '));
      li.appendChild(el('strong', '', a.name));
      li.appendChild(document.createTextNode(' ' + EduI18n.tr('zadal(a) kód spolužáka v úloze') + ' '));
      li.appendChild(el('em', '', a.level));
      return li;
    });
    var kinds = { solve: EduI18n.tr('vyřešil(a)'), hint: EduI18n.tr('si vzal(a) nápovědu v'), foreign_code: EduI18n.tr('zkusil(a) cizí kód v') };
    renderList(root.querySelector('[data-arena57-t-feed]'), d.feed, EduI18n.tr('Zatím se nic nestalo.'), function (f) {
      var li = el('li', 'is-' + f.kind);
      li.appendChild(timeEl(f.at));
      li.appendChild(document.createTextNode(' '));
      li.appendChild(el('strong', '', f.name));
      li.appendChild(document.createTextNode(' ' + (kinds[f.kind] || f.kind) + ' '));
      li.appendChild(el('em', '', f.level));
      if (f.kind === 'solve') li.appendChild(document.createTextNode(' ' + EduI18n.tr('(+{n} b{first})', {n: f.points, first: f.first ? ', ' + EduI18n.tr('první!') : ''})));
      return li;
    });
    renderList(root.querySelector('[data-arena57-t-teams]'), d.teams, EduI18n.tr('Bez týmů.'), function (t) {
      var li = el('li');
      li.appendChild(el('strong', '', (t.rank ? t.rank + '. ' : '') + t.name));
      li.appendChild(document.createTextNode(' – ' + EduI18n.tr('{n} b, {m} úl.', {n: t.points, m: t.solved})));
      li.appendChild(el('br'));
      li.appendChild(el('small', '', (t.members || []).join(', ')));
      return li;
    });
    var levels = root.querySelector('[data-arena57-t-levels]');
    if (levels) {
      clear(levels);
      (d.levels || []).forEach(function (l) {
        var tr = el('tr');
        cell(tr, l.title); cell(tr, l.solves); cell(tr, secsText(l.median_secs)); cell(tr, l.hints); cell(tr, l.first || '–');
        levels.appendChild(tr);
      });
    }
    return status;
  }

  function initTeacher(root) {
    var url = root.getAttribute('data-poll');
    var status = root.getAttribute('data-status');
    var clock = Countdown(root.querySelector('[data-arena57-countdown]'));
    if (!url || status !== 'live') return;
    var poller = Poller(function () {
      return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(parseJson).then(function (d) {
        if (renderTeacher(root, d, clock) !== 'live') poller.stop();
      });
    }, 4000, root.querySelector('[data-arena57-net]'), false);
    root.addEventListener('arena57:timeup', function () { setTimeout(function () { poller.now(); }, 1500); });
  }

  // -------------------------------------------------------------------------
  // Projektor
  // -------------------------------------------------------------------------
  function initProjector(root) {
    var url = root.getAttribute('data-poll');
    var clock = Countdown(root.querySelector('[data-arena57-countdown]'));
    if (!url || root.getAttribute('data-status') === 'finished') return;
    var poller = Poller(function () {
      return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(parseJson).then(function (d) {
        var p = d.public || {};
        if (clock) clock.set(d.race.ends_at, d.race.now, d.race.status);
        var badge = root.querySelector('[data-arena57-status-badge]');
        if (badge) badge.textContent = statusLabel(d.race.status);
        root.setAttribute('data-status', d.race.status);
        renderRows(root.querySelector('[data-arena57-rows]'), p.rows, EduI18n.tr('Kdo vyřeší první úlohu?'));
        renderRows(root.querySelector('[data-arena57-teams]'), p.teams, EduI18n.tr('Týmy se ukážou po startu.'));
        renderFeed(root.querySelector('[data-arena57-feed]'), p.feed, EduI18n.tr('Zatím nic – bonus ×1,25 čeká.'));
        renderGoal(root, p.goal);
        if (d.race.status === 'finished') poller.stop();
      });
    }, 3000, root.querySelector('[data-arena57-net]'), true);
    root.addEventListener('arena57:timeup', function () { setTimeout(function () { poller.now(); }, 1500); });
  }

  // -------------------------------------------------------------------------
  // Formulář nového závodu a potvrzení akcí
  // -------------------------------------------------------------------------
  function initCreate(form) {
    var duration = form.querySelector('[data-arena57-duration]');
    var title = form.querySelector('[data-arena57-title]');
    var custom = form.querySelector('[data-arena57-custom]');
    function apply(radio) {
      if (!radio) return;
      if (duration && radio.getAttribute('data-minutes')) duration.value = radio.getAttribute('data-minutes');
      if (title) title.placeholder = radio.getAttribute('data-title') || '';
      if (custom && radio.value === 'custom') custom.open = true;
    }
    form.addEventListener('change', function (e) { if (e.target && e.target.name === 'preset') apply(e.target); });
    apply(form.querySelector('input[name="preset"]:checked'));
  }

  document.addEventListener('submit', function (e) {
    var form = e.target && e.target.closest ? e.target.closest('[data-arena57-confirm]') : null;
    if (form && !window.confirm(form.getAttribute('data-arena57-confirm'))) e.preventDefault();
  });

  function boot() {
    localizeTimes(document);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena57-student]'), initStudent);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena57-teacher]'), initTeacher);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena57-projector]'), initProjector);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena57-create]'), initCreate);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
