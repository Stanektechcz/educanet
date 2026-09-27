/* EDUCANET v58 · Analytika Linux Labu pro učitele (teacher.php ?tab=labdata).
   Vanilla JS, jen DOM API (textContent), žádné innerHTML s daty. Bez CDN. */
(function () {
  'use strict';

  function renderAlerts(list, alerts) {
    list.textContent = '';
    if (!alerts || !alerts.length) {
      var empty = document.createElement('li');
      empty.className = 'lab58t-empty';
      empty.setAttribute('data-lab58t-empty', '');
      empty.textContent = 'Nikdo teď nepotřebuje pomoc.';
      list.appendChild(empty);
      return;
    }
    alerts.forEach(function (a) {
      var li = document.createElement('li');
      var student = document.createElement('strong');
      student.textContent = a.student || '';
      var level = document.createElement('span');
      level.className = 'lab58t-level';
      level.textContent = a.level || '';
      var reason = document.createElement('span');
      reason.className = 'lab58t-reason';
      reason.textContent = a.reason || '';
      li.appendChild(student);
      li.appendChild(level);
      li.appendChild(reason);
      list.appendChild(li);
    });
  }

  function initSupervision(panel) {
    var list = panel.querySelector('[data-lab58t-alerts]');
    var url = panel.getAttribute('data-poll-url');
    if (!list || !url) return;
    function poll() {
      fetch(url, { credentials: 'same-origin', cache: 'no-store' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) { if (data && data.ok) renderAlerts(list, data.alerts); })
        .catch(function () { /* tichá chyba: příští poll to zkusí znovu */ });
    }
    poll();
    setInterval(poll, 10000);
  }

  function lineClass(ev) {
    if (ev.kind === 'cmd') return 'lab58t-line-cmd';
    if (ev.error_class && ev.error_class !== 'ok') return 'lab58t-line-err';
    return 'lab58t-line-out';
  }

  function pad2(n) { return (n < 10 ? '0' : '') + n; }

  function formatTime(ts) {
    var d = new Date((ts || 0) * 1000);
    return pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
  }

  function readEvents(root) {
    var dataEl = root.parentElement ? root.parentElement.querySelector('[data-lab58t-data]') : null;
    if (!dataEl) return [];
    try {
      var parsed = JSON.parse(dataEl.textContent || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (err) {
      return [];
    }
  }

  function buildPlayer(root, events) {
    var screen = root.querySelector('[data-lab58t-screen]');
    var playBtn = root.querySelector('[data-lab58t-play]');
    var seek = root.querySelector('[data-lab58t-seek]');
    var speedSel = root.querySelector('[data-lab58t-speed]');
    var pos = root.querySelector('[data-lab58t-pos]');
    var live = root.querySelector('[data-lab58t-live]');
    if (!screen || !playBtn || !seek || !pos) return null;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var state = { i: -1, timer: null };

    // v58 A11Y-A9: `screen` je jen vizuální přepis (bez aria-live). Čtečce jde jen `live`
    // s textem poslední řádky – jinak by se při každém kroku znovu přečetl celý přepis.
    function renderUpTo(index) {
      screen.textContent = '';
      var lastText = '';
      for (var k = 0; k <= index && k < events.length; k++) {
        var ev = events[k] || {};
        var line = document.createElement('div');
        line.className = lineClass(ev);
        var prefix = ev.kind === 'cmd' ? '$ ' : '';
        var text = formatTime(ev.ts) + '  ' + prefix + (ev.line || '');
        if (ev.out) text += '\n' + ev.out;
        line.textContent = text;
        screen.appendChild(line);
        lastText = text;
      }
      pos.textContent = (index + 1) + ' / ' + events.length;
      seek.value = String(Math.max(0, index));
      if (live) live.textContent = lastText;
      if (!reduceMotion) screen.scrollTop = screen.scrollHeight;
    }

    function stop() {
      if (state.timer) { clearInterval(state.timer); state.timer = null; }
      playBtn.textContent = 'Přehrát';
    }

    function step() {
      if (state.i >= events.length - 1) { stop(); return; }
      state.i++;
      renderUpTo(state.i);
    }

    function play() {
      if (!events.length) return;
      if (state.timer) { stop(); return; }
      if (state.i >= events.length - 1) state.i = -1;
      playBtn.textContent = 'Pozastavit';
      state.timer = setInterval(step, parseInt(speedSel.value, 10) || 1000);
      step();
    }

    playBtn.addEventListener('click', play);
    seek.addEventListener('input', function () {
      stop();
      state.i = parseInt(seek.value, 10) || 0;
      renderUpTo(state.i);
    });
    speedSel.addEventListener('change', function () {
      if (state.timer) { clearInterval(state.timer); state.timer = setInterval(step, parseInt(speedSel.value, 10) || 1000); }
    });
    root.addEventListener('keydown', function (ev) {
      if (ev.target !== root) return;
      if (ev.key === ' ') { ev.preventDefault(); play(); }
      else if (ev.key === 'ArrowRight') { ev.preventDefault(); stop(); state.i = Math.min(events.length - 1, state.i + 1); renderUpTo(state.i); }
      else if (ev.key === 'ArrowLeft') { ev.preventDefault(); stop(); state.i = Math.max(0, state.i - 1); renderUpTo(state.i); }
    });

    pos.textContent = '0 / ' + events.length;
    return state;
  }

  function initPlayer(root) {
    buildPlayer(root, readEvents(root));
  }

  document.addEventListener('DOMContentLoaded', function () {
    var watchers = document.querySelectorAll('[data-lab58t-watch]');
    for (var i = 0; i < watchers.length; i++) initSupervision(watchers[i]);
    var players = document.querySelectorAll('[data-lab58t-player]');
    for (var j = 0; j < players.length; j++) initPlayer(players[j]);
  });
})();
