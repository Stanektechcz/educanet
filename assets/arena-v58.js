/* EDUCANET v58 · Aréna – Týdenní hádanka (ARN-01), Záznam závodu (ARN-05), doplněk k arena-v57.js.
 * Nezávislá IIFE (arena-v57.js má svoje pomocníky soukromé) – DOM jen přes textContent, žádné innerHTML. */
(function () {
  'use strict';

  var MAX_BACKOFF = 30000;

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = String(text);
    return n;
  }
  function clear(node) { while (node && node.firstChild) node.removeChild(node.firstChild); }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function secsText(s) { if (s === null || s === undefined) return '–'; return s >= 60 ? Math.floor(s / 60) + ' min ' + (s % 60) + ' s' : s + ' s'; }
  function plural(n, one, few, many) { return n === 1 ? one : (n >= 2 && n <= 4 ? few : many); }
  function countdownText(secs) {
    secs = Math.max(0, secs | 0);
    var days = Math.floor(secs / 86400), hours = Math.floor((secs % 86400) / 3600), mins = Math.floor((secs % 3600) / 60);
    if (days > 0) return days + ' ' + plural(days, 'den', 'dny', 'dní') + ' ' + hours + ' h';
    if (hours > 0) return hours + ' h ' + mins + ' min';
    return mins + ' min';
  }
  function reduceMotion() { return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches); }

  function parseJson(res) {
    if (res.status === 419 || res.status === 401) return Promise.reject({ own: true, fatal: true, message: 'Relace vypršela – obnov stránku.' });
    var type = res.headers.get('Content-Type') || '';
    if (type.indexOf('application/json') === -1) return Promise.reject(res.ok ? { own: true, fatal: true, message: 'Nejsi přihlášen(a) – obnov stránku.' } : { status: res.status });
    return res.json().then(function (j) {
      if (!res.ok || !j || j.ok === false) return Promise.reject({ own: true, fatal: res.status === 403 || res.status === 404, message: (j && j.error) || 'Data se nepodařilo načíst.' });
      return j;
    });
  }

  function Poller(task, interval, netEl) {
    var delay = interval, timer = null, stopped = false, busy = false;
    function schedule(ms) { if (!stopped) timer = setTimeout(run, ms); }
    function note(text) { if (netEl) netEl.textContent = text || ''; }
    function run() {
      timer = null;
      if (stopped || busy) return;
      if (document.hidden) { schedule(interval); return; }
      busy = true;
      task().then(function () { busy = false; delay = interval; note(''); schedule(interval); }, function (err) {
        busy = false;
        if (err && err.fatal) { stopped = true; note((err && err.message) || 'Data se nepodařilo načíst.'); return; }
        delay = Math.min(MAX_BACKOFF, delay * 2);
        note('Spojení se přerušilo, zkouším to znovu…');
        schedule(delay);
      });
    }
    schedule(interval);
    return { now: function () { if (!stopped && !busy) { if (timer) clearTimeout(timer); run(); } }, stop: function () { stopped = true; if (timer) clearTimeout(timer); } };
  }

  // -------------------------------------------------------------------------
  // Odpočet do uzávěrky hádanky (dny/hodiny, ne sekundy – týden je dlouhý)
  // -------------------------------------------------------------------------
  function WeeklyCountdown(node) {
    if (!node) return null;
    var valueEl = node.querySelector('[data-arena58-clock-value]');
    var announceEl = node.querySelector('[data-arena58-clock-announce]');
    var ends = NaN, skew = 0, timer = null, announcedClose = false;
    function remaining() { return Math.max(0, Math.round((ends - (Date.now() + skew)) / 1000)); }
    function tick() {
      var r = remaining();
      if (valueEl) valueEl.textContent = r <= 0 ? '0 min' : countdownText(r);
      if (r <= 3600 && !announcedClose && announceEl) { announcedClose = true; announceEl.textContent = 'Hádance zbývá poslední hodina.'; }
      if (r <= 0) { clearInterval(timer); timer = null; node.dispatchEvent(new CustomEvent('arena58:weeklyclose', { bubbles: true })); }
    }
    function set(endsIso, nowSecs) {
      ends = Date.parse(endsIso || '');
      if (typeof nowSecs === 'number' && !isNaN(nowSecs)) skew = nowSecs * 1000 - Date.now();
      if (!timer && !isNaN(ends)) timer = setInterval(tick, 60000);
      tick();
    }
    set(node.getAttribute('data-ends'), parseInt(node.getAttribute('data-now'), 10));
    return { set: set };
  }

  // -------------------------------------------------------------------------
  // Vykreslení žebříčku (tabulka) – zrcadlí arena58_weekly_render_board() v PHP
  // -------------------------------------------------------------------------
  function renderBoardTable(table, scope) {
    if (!table) return;
    var body = table.querySelector('tbody');
    if (!body) return;
    clear(body);
    var rows = (scope && scope.rows) || [];
    if (!rows.length) {
      var tr0 = el('tr'); var td0 = el('td', 'arena57-muted', 'Zatím nikdo nevyřešil. Buď první!'); td0.colSpan = 4; tr0.appendChild(td0); body.appendChild(tr0);
      return;
    }
    rows.forEach(function (r) {
      var tr = el('tr', r.me ? 'is-me' : '');
      tr.appendChild(el('td', '', r.rank + '.'));
      var nameTd = el('td', '', r.name);
      if (r.me) { nameTd.appendChild(document.createTextNode(' ')); nameTd.appendChild(el('small', '', '(ty)')); }
      tr.appendChild(nameTd);
      tr.appendChild(el('td', '', String(r.len)));
      tr.appendChild(el('td', '', secsText(r.secs)));
      body.appendChild(tr);
      if (r.cmd) {
        var solTr = el('tr', 'arena58w-solution');
        solTr.appendChild(el('td'));
        var solTd = el('td'); solTd.colSpan = 3;
        solTd.appendChild(el('code', '', r.cmd));
        solTr.appendChild(solTd);
        body.appendChild(solTr);
      }
    });
  }

  function initWeeklyStudent(root) {
    var api = root.getAttribute('data-api') || 'lab_v57_api.php';
    var ctx = root.getAttribute('data-ctx') || '';
    var meta = document.querySelector('meta[name="csrf-token"]');
    var csrf = meta ? meta.getAttribute('content') : '';
    var net = root.querySelector('[data-arena57-net]') || root.querySelector('.arena57-net');
    var clock = WeeklyCountdown(root.querySelector('[data-arena58-countdown]'));
    var interval = parseInt(root.getAttribute('data-poll-interval'), 10) || 15000;

    function render(board) {
      if (board.error) return Promise.reject({ own: true, fatal: true, message: board.error });
      if (clock) clock.set(board.ends_at, board.now);
      var me = board.me || {};
      var lenEl = root.querySelector('[data-arena58-me-len]');
      if (lenEl && me.solved) lenEl.textContent = me.len + ' znaků';
      var rc = root.querySelector('[data-arena58-me-rank-class]');
      if (rc) rc.textContent = me.rank_class ? me.rank_class + '.' : '–';
      var rs = root.querySelector('[data-arena58-me-rank-school]');
      if (rs) rs.textContent = me.rank_school ? me.rank_school + '.' : '–';
      renderBoardTable(root.querySelector('[data-arena58-board="class"]'), board.class);
      renderBoardTable(root.querySelector('[data-arena58-board="school"]'), board.school);
    }

    function fetchBoard() {
      var body = new URLSearchParams();
      body.set('csrf', csrf); body.set('op', 'board'); body.set('ctx', ctx); body.set('level', 'sandbox');
      return fetch(api, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }, body: body })
        .then(parseJson).then(function (j) { return render(j.board || {}); });
    }

    var poller = Poller(fetchBoard, interval, net);
    document.addEventListener('lab57:solved', function () { poller.now(); });
    root.addEventListener('arena58:weeklyclose', function () { poller.now(); });
  }

  // -------------------------------------------------------------------------
  // Učitel: panel Týdenní hádanky (jednoduché periodické obnovení TOP tabulky)
  // -------------------------------------------------------------------------
  function initWeeklyTeacher(root) {
    var url = root.getAttribute('data-poll');
    if (!url) return;
    var net = root.querySelector('[data-arena57-net]');
    var poller = Poller(function () {
      return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(parseJson).then(function (d) {
        var badge = root.querySelector('.arena57-badge');
        if (badge) { var closed = Date.now() / 1000 >= d.end; badge.textContent = closed ? 'Uzavřeno' : 'Běží'; badge.className = 'arena57-badge ' + (closed ? 'is-finished' : 'is-live'); }
        var top = root.querySelector('[data-arena58-t-top] tbody');
        if (top) {
          clear(top);
          (d.top || []).forEach(function (r) {
            var tr = el('tr');
            [r.rank + '.', r.label, r.class_id, r.len, secsText(r.secs)].forEach(function (v) { tr.appendChild(el('td', '', v)); });
            top.appendChild(tr);
          });
        }
      });
    }, 20000, net);
    // Odhlásíme dotazy, když učitel opustí panel (jiná záložka DOM, ne jen skrytá karta prohlížeče).
    window.addEventListener('beforeunload', function () { poller.stop(); });
  }

  // -------------------------------------------------------------------------
  // Přepínač kategorií ve formuláři nového závodu (ARN-06): pole se ukáže jen
  // pro sólo + hodnocení „kategorie“. Bez JS je pole vždy vidět – nic se neztratí.
  // -------------------------------------------------------------------------
  function initRatingToggle(form) {
    var rating = form.querySelector('[data-arena57-rating]');
    var mode = form.querySelector('select[name="mode"]');
    var wrap = form.querySelector('[data-arena57-category-count]');
    if (!rating || !wrap) return;
    function apply() {
      var show = rating.value === 'kategorie' && (!mode || mode.value === 'solo');
      wrap.hidden = !show;
    }
    rating.addEventListener('change', apply);
    if (mode) mode.addEventListener('change', apply);
    apply();
  }

  // -------------------------------------------------------------------------
  // Záznam závodu (ARN-05): přehrávání nad vždy-viditelným seznamem kroků.
  // prefers-reduced-motion: žádné automatické přehrávání, jen ruční krok.
  // -------------------------------------------------------------------------
  function initReplay(root) {
    var frames = [];
    try { frames = JSON.parse(root.getAttribute('data-frames') || '[]'); } catch (e) { frames = []; }
    var list = root.querySelector('[data-replay-list]');
    var items = list ? Array.prototype.slice.call(list.querySelectorAll('[data-replay-frame]')) : [];
    var clockEl = root.querySelector('[data-replay-clock]');
    var announce = root.querySelector('[data-replay-announce]');
    var playBtn = root.querySelector('[data-replay-play]');
    var pauseBtn = root.querySelector('[data-replay-pause]');
    var speedSel = root.querySelector('[data-replay-speed]');
    var reduced = reduceMotion();
    var i = -1, timer = null;

    function clockText(t) { t = Math.max(0, t | 0); return Math.floor(t / 60) + ':' + pad(t % 60); }

    function show(idx) {
      i = Math.max(-1, Math.min(items.length - 1, idx));
      items.forEach(function (it, n) {
        it.classList.remove('is-current', 'is-done', 'is-upcoming');
        it.classList.add(n < i ? 'is-done' : (n === i ? 'is-current' : 'is-upcoming'));
      });
      if (i >= 0) {
        var cur = items[i];
        if (clockEl) clockEl.textContent = clockText(parseInt(cur.getAttribute('data-t'), 10) || 0);
        if (announce) announce.textContent = cur.textContent.replace(/^\s*\d+:\d+\s*/, '');
        if (!reduced && cur.scrollIntoView) cur.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      } else if (clockEl) {
        clockEl.textContent = '0:00';
      }
    }

    function stop() {
      if (timer) { clearTimeout(timer); timer = null; }
      if (playBtn) playBtn.hidden = false;
      if (pauseBtn) pauseBtn.hidden = true;
    }

    function scheduleNext() {
      if (i >= items.length - 1) { stop(); return; }
      var speed = parseFloat((speedSel && speedSel.value) || '1') || 1;
      var cur = parseInt(items[i] ? items[i].getAttribute('data-t') : '0', 10) || 0;
      var next = parseInt(items[i + 1].getAttribute('data-t'), 10) || cur;
      var wait = Math.max(250, Math.min(4000, ((next - cur) * 350) / speed));
      timer = setTimeout(function () { show(i + 1); scheduleNext(); }, wait);
    }

    function play() {
      if (reduced) { show(i + 1); return; } // omezený pohyb: jen krok po kroku na vyžádání
      if (i >= items.length - 1) show(-1);
      if (playBtn) playBtn.hidden = true;
      if (pauseBtn) pauseBtn.hidden = false;
      scheduleNext();
    }

    if (playBtn) playBtn.addEventListener('click', play);
    if (pauseBtn) pauseBtn.addEventListener('click', stop);
    var backBtn = root.querySelector('[data-replay-back]');
    var fwdBtn = root.querySelector('[data-replay-fwd]');
    if (backBtn) backBtn.addEventListener('click', function () { stop(); show(i - 1); });
    if (fwdBtn) fwdBtn.addEventListener('click', function () { stop(); show(i + 1); });
    document.addEventListener('keydown', function (e) {
      if (!root.contains(document.activeElement) && document.activeElement !== document.body) return;
      if (e.key === ' ' && (playBtn || pauseBtn)) { e.preventDefault(); (pauseBtn && !pauseBtn.hidden) ? stop() : play(); }
      else if (e.key === 'ArrowRight') { stop(); show(i + 1); }
      else if (e.key === 'ArrowLeft') { stop(); show(i - 1); }
    });
    if (reduced && playBtn) playBtn.textContent = '▶ Další krok';
    show(-1);
  }

  function boot() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena58-weekly]'), initWeeklyStudent);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena58-weekly-teacher]'), initWeeklyTeacher);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena57-create]'), initRatingToggle);
    Array.prototype.forEach.call(document.querySelectorAll('[data-arena58-replay]'), initReplay);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
