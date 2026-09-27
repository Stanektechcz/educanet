/* EDUCANET v58 · Aréna – CTF týden a Incidenty. Jen DOM API / textContent, žádné innerHTML s daty ze serveru. */
(function () {
  'use strict';

  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function el(tag, attrs, text) {
    var node = document.createElement(tag);
    if (attrs) {
      Object.keys(attrs).forEach(function (key) {
        if (attrs[key] !== null && attrs[key] !== undefined) node.setAttribute(key, attrs[key]);
      });
    }
    if (text !== undefined && text !== null) node.textContent = text;
    return node;
  }

  function clear(node) { while (node.firstChild) node.removeChild(node.firstChild); }

  function durationText(secs) {
    secs = Math.max(0, secs | 0);
    if (secs >= 86400) return Math.floor(secs / 86400) + ' d ' + Math.floor((secs % 86400) / 3600) + ' h';
    if (secs >= 3600) return Math.floor(secs / 3600) + ' h ' + Math.floor((secs % 3600) / 60) + ' min';
    var m = Math.floor(secs / 60), s = secs % 60;
    return m + ':' + (s < 10 ? '0' : '') + s;
  }

  // ---------------------------------------------------------------------
  // Odpočty: klientský tik po sekundě z hodnoty data-remaining, hlášení
  // jen jednou za minutu (aria-live="polite"), aby čtečka nespamovala.
  // ---------------------------------------------------------------------
  function initCountdowns() {
    qsa('[data-arn58e-countdown]').forEach(function (box) {
      var remaining = parseInt(box.getAttribute('data-remaining') || '0', 10);
      var valueEl = qs('[data-arn58e-clock-value]', box);
      var announceEl = qs('[data-arn58e-clock-announce]', box);
      var lastAnnouncedMinute = null;
      var startedAt = Date.now();
      var baseRemaining = remaining;
      function tick() {
        var elapsed = Math.floor((Date.now() - startedAt) / 1000);
        var left = Math.max(0, baseRemaining - elapsed);
        if (valueEl) valueEl.textContent = durationText(left);
        box.classList.toggle('is-low', left > 0 && left <= 60);
        var minute = Math.floor(left / 60);
        if (announceEl && minute !== lastAnnouncedMinute && left % 60 === 0) {
          lastAnnouncedMinute = minute;
          var minuteWord = minute === 1 ? EduI18n.tr('Zbývá {n} minuta.', {n: minute}) : (minute < 5 ? EduI18n.tr('Zbývá {n} minuty.', {n: minute}) : EduI18n.tr('Zbývá {n} minut.', {n: minute}));
          announceEl.textContent = left <= 0 ? EduI18n.tr('Čas vypršel.') : minuteWord;
        }
        if (left <= 0) { window.clearInterval(timer); }
      }
      var timer = window.setInterval(tick, 1000);
      tick();
    });
  }

  // ---------------------------------------------------------------------
  // Potvrzovací dialogy u nebezpečných akcí (smazat / ukončit).
  // ---------------------------------------------------------------------
  function initConfirms() {
    document.addEventListener('submit', function (ev) {
      var form = ev.target;
      if (form instanceof HTMLFormElement && form.hasAttribute('data-arn58e-confirm')) {
        if (!window.confirm(form.getAttribute('data-arn58e-confirm') || 'Opravdu?')) ev.preventDefault();
      }
    });
  }

  // ---------------------------------------------------------------------
  // Malá pomocná fetch obálka pro naše dvě API (arena_v58_events_api.php).
  // ---------------------------------------------------------------------
  function postForm(url, fields) {
    var body = new URLSearchParams();
    Object.keys(fields).forEach(function (k) { body.append(k, fields[k]); });
    return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
      .then(function (res) { return res.json(); });
  }

  // ---------------------------------------------------------------------
  // Incidenty: start / pauza / postmortem
  // ---------------------------------------------------------------------
  function initIncident() {
    var box = qs('[data-arn58e-incident]');
    if (!box) return;
    var api = box.getAttribute('data-api');
    var session = box.getAttribute('data-session');
    var scenario = box.getAttribute('data-scenario');
    var csrf = box.getAttribute('data-csrf');

    var startForm = qs('[data-arn58e-inc-start]', box);
    if (startForm) {
      startForm.addEventListener('submit', function (ev) {
        ev.preventDefault();
        postForm(api, { csrf: csrf, op: 'start', session: session, scenario: scenario }).then(function (res) {
          if (res && res.ok) { window.location.reload(); } else { window.alert((res && res.error) || EduI18n.tr('Nepodařilo se spustit scénář.')); }
        });
      });
    }

    var pauseBtn = qs('[data-arn58e-inc-pause]', box);
    if (pauseBtn) {
      pauseBtn.addEventListener('click', function () {
        var paused = pauseBtn.getAttribute('data-paused') === '1';
        postForm(api, { csrf: csrf, op: paused ? 'resume' : 'pause', session: session, scenario: scenario }).then(function (res) {
          if (!res || !res.ok) { window.alert((res && res.error) || EduI18n.tr('Nepodařilo se to.')); return; }
          pauseBtn.setAttribute('data-paused', paused ? '0' : '1');
          pauseBtn.textContent = paused ? '⏸ ' + EduI18n.tr('Pauza') : '▶ ' + EduI18n.tr('Pokračovat');
          var clock = qs('[data-arn58e-countdown]', box);
          if (clock && res.attempt && typeof res.attempt.remaining === 'number') clock.setAttribute('data-remaining', String(res.attempt.remaining));
        });
      });
    }

    var pmForm = qs('[data-arn58e-inc-postmortem]', box);
    if (pmForm) {
      pmForm.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var fields = { csrf: csrf, op: 'postmortem', session: session, scenario: scenario };
        qsa('textarea', pmForm).forEach(function (t) { fields[t.name] = t.value; });
        postForm(api, fields).then(function (res) {
          if (res && res.ok) { window.location.reload(); } else { window.alert((res && res.error) || EduI18n.tr('Postmortem se nepodařilo odeslat.')); }
        });
      });
    }
  }

  // ---------------------------------------------------------------------
  // Učitelský panel CTF – polling, přestavuje jen tabulku/seznamy (textContent).
  // ---------------------------------------------------------------------
  function renderCtfBoard(tbody, rows) {
    clear(tbody);
    if (!rows.length) { tbody.appendChild(el('tr', null)).appendChild(el('td', { colspan: '4' }, 'Zatím nikdo nemá body.')); return; }
    rows.forEach(function (row) {
      var tr = el('tr');
      tr.appendChild(el('td', null, row.rank !== null ? row.rank + '.' : '–'));
      tr.appendChild(el('td', null, row.name));
      tr.appendChild(el('td', null, String(row.points)));
      tr.appendChild(el('td', null, String(row.solved)));
      tbody.appendChild(tr);
    });
  }

  function renderList(ul, items, textFn) {
    clear(ul);
    if (!items.length) { ul.appendChild(el('li', { class: 'is-empty' }, 'Žádné.')); return; }
    items.forEach(function (item) { ul.appendChild(el('li', null, textFn(item))); });
  }

  function pollCtfPanel(panel) {
    var url = panel.getAttribute('data-poll');
    fetch(url, { credentials: 'same-origin' }).then(function (res) { return res.json(); }).then(function (data) {
      if (!data || !data.ok) return;
      var board = qs('[data-arn58e-t-board]', panel);
      if (board) renderCtfBoard(board, data.board || []);
      var levels = qs('[data-arn58e-t-levels]', panel);
      if (levels) renderList(levels, data.levels || [], function (l) {
        return l.title + ' · ' + l.solves + '× vyřešeno · teď ' + l.current_tier + ' % bodů' + (l.first ? (' · první: ' + l.first) : '');
      });
      var alerts = qs('[data-arn58e-t-alerts]', panel);
      if (alerts) renderList(alerts, data.alerts || [], function (a) { return a.name + ' zkusil(a) cizí kód v ' + a.level; });
      var feed = qs('[data-arn58e-t-feed]', panel);
      if (feed) renderList(feed, data.feed || [], function (f) {
        return f.name + (f.kind === 'solve' ? (' vyřešil(a) ' + f.level + ' (+' + f.points + ' b' + (f.first ? ', první krev!' : '') + ')') : (' zkusil(a) cizí kód v ' + f.level));
      });
    }).catch(function () {});
  }

  function pollIncPanel(panel) {
    var url = panel.getAttribute('data-poll');
    fetch(url, { credentials: 'same-origin' }).then(function (res) { return res.json(); }).then(function (data) {
      if (!data || !data.ok) return;
      var rows = qs('[data-arn58e-inc-rows]', panel);
      if (!rows) return;
      clear(rows);
      (data.attempts || []).forEach(function (row) {
        var tr = el('tr');
        tr.appendChild(el('td', null, row.name));
        tr.appendChild(el('td', null, row.scenario));
        tr.appendChild(el('td', null, row.solved ? 'vyřešeno' : (row.expired ? 'čas vypršel' : 'běží')));
        tr.appendChild(el('td', null, row.postmortem ? 'odevzdán' : '–'));
        tr.appendChild(el('td', null, row.postmortem ? String(row.postmortem.points) : '–'));
        rows.appendChild(tr);
      });
    }).catch(function () {});
  }

  function initPolling() {
    var ctf = qs('[data-arn58e-ctf-panel]');
    if (ctf) { pollCtfPanel(ctf); window.setInterval(function () { pollCtfPanel(ctf); }, 5000); }
    var inc = qs('[data-arn58e-inc-panel]');
    if (inc) { pollIncPanel(inc); window.setInterval(function () { pollIncPanel(inc); }, 5000); }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCountdowns();
    initConfirms();
    initIncident();
    initPolling();
  });
})();
