/* EDUCANET v58 · Robotí liga – editor skriptu, volání API, přehrávač záznamu (SVG) a živé aktualizace.
 * DOM se plní jen přes textContent / createElement – data od uživatelů se nikdy neskládají do HTML řetězců. */
(function () {
  'use strict';

  // v59 OPS-02: shim pro stránky bez assets/i18n-v58.js (učitel/projektor) – proměnnou tr nikdy nepoužívat jako alias.
  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  var SVG = 'http://www.w3.org/2000/svg';
  var CELL = 20;
  var COLORS = ['#007a87', '#b35400', '#6b3fa0', '#2e7d32'];
  var ACT = { 0: EduI18n.tr('čeká'), 1: EduI18n.tr('jede'), 2: EduI18n.tr('zablokovaný'), 3: EduI18n.tr('zvedl(a) balíček'), 4: EduI18n.tr('doručil(a) náklad'), 5: EduI18n.tr('opravuje uzel'), 6: EduI18n.tr('nabíjí se'), 7: EduI18n.tr('chyba ve skriptu'), 8: EduI18n.tr('přes limit kroků / chladne'), 9: EduI18n.tr('málo energie'), 10: EduI18n.tr('akce se nepovedla'), 11: EduI18n.tr('dokončil(a) opravu uzlu') };
  var NOTABLE = { 3: 1, 4: 1, 5: 1, 6: 1, 7: 1, 8: 1, 9: 1, 10: 1, 11: 1 };
  var POLL_MS = 20000;
  var MAX_LOG = 3000;
  var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = String(text);
    return n;
  }
  function svgEl(tag, attrs, text) {
    var n = document.createElementNS(SVG, tag);
    Object.keys(attrs || {}).forEach(function (k) { n.setAttribute(k, String(attrs[k])); });
    if (text !== undefined) n.textContent = String(text);
    return n;
  }
  function clear(node) { while (node && node.firstChild) node.removeChild(node.firstChild); }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function timeText(iso) { var d = new Date(iso); return isNaN(d.getTime()) ? '–' : d.getDate() + '. ' + (d.getMonth() + 1) + '. ' + pad(d.getHours()) + ':' + pad(d.getMinutes()); }
  function readJson(root, attr) {
    var node = root.querySelector('[' + attr + ']');
    if (!node) return null;
    try { return JSON.parse(node.textContent || 'null'); } catch (e) { return null; }
  }

  // -------------------------------------------------------------------------
  // Přehrávač záznamu
  // -------------------------------------------------------------------------
  function Player(root) {
    this.root = root;
    this.svg = root.querySelector('[data-rb58-svg]');
    this.slider = root.querySelector('[data-rb58-slider]');
    this.turnOut = root.querySelector('[data-rb58-turn]');
    this.live = root.querySelector('[data-rb58-live]');
    this.playBtn = root.querySelector('[data-rb58-ctl="play"]');
    this.speed = root.querySelector('[data-rb58-speed]');
    this.scoreBody = root.querySelector('[data-rb58-score] tbody');
    this.timer = null;
    this.onJump = null;
    var self = this;
    root.querySelectorAll('[data-rb58-ctl]').forEach(function (b) {
      b.addEventListener('click', function () { self.control(b.getAttribute('data-rb58-ctl')); });
    });
    this.slider.addEventListener('input', function () { self.pause(); self.seek(parseInt(self.slider.value, 10) || 0, true); });
    root.querySelector('[data-rb58-stage]').addEventListener('keydown', function (ev) { self.key(ev); });
    var logBox = root.querySelector('[data-rb58-logbox]');
    logBox.addEventListener('toggle', function () { if (logBox.open) self.buildLog(); });
    root.querySelector('[data-rb58-logmine]').addEventListener('change', function () { self.buildLog(); });
  }

  Player.prototype.key = function (ev) {
    var map = { ' ': 'play', ArrowLeft: 'back', ArrowRight: 'fwd', Home: 'start', End: 'end' };
    var action = map[ev.key];
    if (!action) return;
    ev.preventDefault();
    this.control(action);
  };

  Player.prototype.control = function (action) {
    if (!this.r) return;
    if (action === 'play') { if (this.timer) this.pause(true); else this.play(); return; }
    this.pause();
    var t = { start: 0, back: this.turn - 1, fwd: this.turn + 1, end: this.r.turns }[action];
    this.seek(Math.max(0, Math.min(this.r.turns, t)), true);
  };

  Player.prototype.play = function () {
    var self = this;
    if (this.turn >= this.r.turns) this.seek(0, false);
    this.playBtn.textContent = EduI18n.tr('Pauza');
    this.playBtn.setAttribute('aria-pressed', 'true');
    this.timer = setInterval(function () {
      if (self.turn >= self.r.turns) { self.pause(true); return; }
      self.apply(self.state, self.r.d[self.turn]);
      self.turn++;
      self.render(false);
    }, Math.round(400 / (parseInt(this.speed.value, 10) || 1)));
  };

  Player.prototype.pause = function (announce) {
    if (!this.timer) return;
    clearInterval(this.timer);
    this.timer = null;
    this.playBtn.textContent = EduI18n.tr('Přehrát');
    this.playBtn.setAttribute('aria-pressed', 'false');
    if (announce) this.announce();
  };

  Player.prototype.load = function (data) {
    this.pause();
    this.r = data.replay;
    this.me = data.me || null;
    this.meIndex = -1;
    var self = this;
    (this.r.robots || []).forEach(function (rb, i) { if (rb.me) self.meIndex = i; });
    this.root.hidden = false;
    this.root.querySelector('[data-rb58-title]').textContent = (this.r.title || EduI18n.tr('Záznam')) + ' · ' + EduI18n.trn({one: '{n} tah', few: '{n} tahy', other: '{n} tahů'}, this.r.turns);
    this.slider.max = String(this.r.turns);
    this.build();
    this.buildScore();
    this.renderStats();
    this.root.querySelector('[data-rb58-logbox]').open = false;
    clear(this.root.querySelector('[data-rb58-log]'));
    this.seek(0, false);
  };

  Player.prototype.color = function (i) {
    var rb = this.r.robots[i] || {};
    var corner = rb.team !== null && rb.team !== undefined && this.r.teams[rb.team] ? this.r.teams[rb.team].corner : this.r.corners[i];
    return COLORS[corner % 4];
  };

  Player.prototype.build = function () {
    var r = this.r, svg = this.svg, self = this;
    clear(svg);
    svg.setAttribute('viewBox', '0 0 ' + r.w * CELL + ' ' + r.h * CELL);
    svg.appendChild(svgEl('rect', { x: 0, y: 0, width: r.w * CELL, height: r.h * CELL, 'class': 'rb58-floor' }));
    for (var i = 0; i < r.grid.length; i++) {
      var ch = r.grid.charAt(i), x = (i % r.w) * CELL, y = Math.floor(i / r.w) * CELL;
      if (ch === '#') svg.appendChild(svgEl('rect', { x: x + 1, y: y + 1, width: CELL - 2, height: CELL - 2, rx: 2, 'class': 'rb58-wall' }));
      if (ch === 'B') {
        var corner = ((i % r.w) >= r.w / 2 ? 1 : 0) + (Math.floor(i / r.w) >= r.h / 2 ? 2 : 0);
        svg.appendChild(svgEl('rect', { x: x, y: y, width: CELL, height: CELL, fill: COLORS[corner], 'class': 'rb58-base' }));
        svg.appendChild(svgEl('text', { x: x + CELL / 2, y: y + 14, 'class': 'rb58-base-t' }, 'Z'));
      }
      if (ch === '+') {
        svg.appendChild(svgEl('rect', { x: x + 2, y: y + 2, width: CELL - 4, height: CELL - 4, rx: 4, 'class': 'rb58-charger' }));
        svg.appendChild(svgEl('text', { x: x + CELL / 2, y: y + 15, 'class': 'rb58-charger-t' }, '+'));
      }
    }
    this.packetEls = r.packets.map(function (idx) {
      var p = svgEl('rect', { x: (idx % r.w) * CELL + 6, y: Math.floor(idx / r.w) * CELL + 6, width: 8, height: 8, rx: 1, 'class': 'rb58-packet' });
      svg.appendChild(p);
      return p;
    });
    this.nodeEls = r.nodes.map(function (idx) {
      var g = svgEl('g', { 'class': 'rb58-node', transform: 'translate(' + (idx % r.w) * CELL + ',' + Math.floor(idx / r.w) * CELL + ')' });
      g.appendChild(svgEl('polygon', { points: '10,1 19,18 1,18' }));
      var t = svgEl('text', { x: 10, y: 16 }, '3');
      g.appendChild(t);
      svg.appendChild(g);
      return { g: g, t: t };
    });
    this.robotEls = r.robots.map(function (rb, i) {
      var g = svgEl('g', { 'class': 'rb58-robot' + (rb.me ? ' is-me' : '') });
      if (rb.me) g.appendChild(svgEl('circle', { r: 9.5, 'class': 'rb58-me-ring' }));
      g.appendChild(svgEl('circle', { r: 7.5, fill: self.color(i) }));
      g.appendChild(svgEl('text', { y: 3.5, 'class': 'rb58-robot-t' }, String(i + 1)));
      if (rb.me) g.appendChild(svgEl('text', { y: -11, 'class': 'rb58-me-t' }, EduI18n.tr('TY')));
      var say = svgEl('text', { y: rb.me ? -21 : -11, 'class': 'rb58-say' }, '');
      g.appendChild(say);
      g.appendChild(svgEl('title', {}, EduI18n.tr('{name} (č. {n})', { name: rb.name || EduI18n.tr('Robot'), n: i + 1 })));
      svg.appendChild(g);
      return { g: g, say: say };
    });
  };

  Player.prototype.buildScore = function () {
    var self = this;
    clear(this.scoreBody);
    this.scoreRows = this.r.robots.map(function (rb, i) {
      var tr = el('tr', rb.me ? 'is-me' : '');
      tr.appendChild(el('td', '', i + 1));
      // v58 A11Y-A13: „tvůj“ robot nesmí být poznat jen podle barvy/podbarvení řádku – i textem.
      var name = el('th', '', (rb.name || EduI18n.tr('Robot')) + (rb.me ? EduI18n.tr(' (ty)') : ''));
      name.setAttribute('scope', 'row');
      var sw = el('i', 'rb58-swatch');
      sw.style.background = self.color(i);
      name.insertBefore(sw, name.firstChild);
      tr.appendChild(name);
      tr.appendChild(el('td', '', rb.team !== null && rb.team !== undefined && self.r.teams[rb.team] ? self.r.teams[rb.team].name : '–'));
      var cells = [el('td'), el('td'), el('td'), el('td')];
      cells.forEach(function (c) { tr.appendChild(c); });
      self.scoreBody.appendChild(tr);
      return cells;
    });
  };

  Player.prototype.stateAt = function (t) {
    var r = this.r, k = Math.floor(t / r.keyEvery) * r.keyEvery;
    while (k > 0 && !r.key[k]) k -= r.keyEvery;
    var kf = r.key[k];
    var s = { robots: kf.r.map(function (x) { return [x[0], x[1], x[2], x[3], 0]; }), packets: kf.p.split('').map(Number), nodes: kf.n.slice(), says: kf.m.slice(), events: [] };
    for (var turn = k + 1; turn <= t; turn++) this.apply(s, r.d[turn - 1]);
    return s;
  };

  /** Použije změny jednoho tahu; do s.events uloží zajímavé události tahu (pro výpis a čtečky). */
  Player.prototype.apply = function (s, delta) {
    s.events = [];
    s.robots.forEach(function (q) { q[4] = 0; });
    delta[0].forEach(function (row) {
      var q = s.robots[row[0]], gained = row[5] - q[3];
      q[0] = row[1]; q[4] = row[2]; q[1] = row[3]; q[2] = row[4]; q[3] = row[5];
      if (NOTABLE[row[2]]) s.events.push({ i: row[0], act: row[2], pts: gained });
    });
    delta[1].forEach(function (p) { s.packets[p[0]] = p[1]; });
    delta[2].forEach(function (n) {
      if (n[1] === 3 && s.nodes[n[0]] === 0) s.events.push({ i: -1, act: 'break', node: n[0] });
      s.nodes[n[0]] = n[1];
    });
    delta[3].forEach(function (m) { s.says[m[0]] = m[1]; });
  };

  Player.prototype.seek = function (t, announce) {
    this.turn = t;
    this.state = this.stateAt(t);
    this.render(announce);
  };

  Player.prototype.eventText = function (ev) {
    if (ev.act === 'break') return EduI18n.tr('Rozbil se uzel č. {n} – kdo ho opraví?', { n: ev.node + 1 });
    var name = (this.r.robots[ev.i] || {}).name || EduI18n.tr('Robot');
    var extra = ev.pts > 0 ? EduI18n.tr(' (+{n} b)', { n: ev.pts }) : '';
    return name + ' ' + ACT[ev.act] + extra + '.';
  };

  Player.prototype.render = function (announce) {
    var r = this.r, s = this.state, self = this, onTile = {};
    s.robots.forEach(function (q, i) {
      var n = onTile[q[0]] = (onTile[q[0]] || 0) + 1;
      var off = n > 1 ? ((n - 1) % 3 - 1) * 4 : 0;
      var x = (q[0] % r.w) * CELL + CELL / 2 + off, y = Math.floor(q[0] / r.w) * CELL + CELL / 2 + off;
      var item = self.robotEls[i];
      item.g.style.transform = 'translate(' + x + 'px,' + y + 'px)';
      item.say.textContent = s.says[i] || '';
      var cells = self.scoreRows[i];
      cells[0].textContent = q[3];
      cells[1].textContent = q[1];
      cells[2].textContent = q[2] + ' / 3';
      cells[3].textContent = ACT[q[4]] || '';
    });
    this.packetEls.forEach(function (p, i) { p.style.display = s.packets[i] ? '' : 'none'; });
    this.nodeEls.forEach(function (n, i) {
      n.g.style.display = s.nodes[i] > 0 ? '' : 'none';
      n.t.textContent = String(s.nodes[i]);
    });
    this.slider.value = String(this.turn);
    this.slider.setAttribute('aria-valuetext', EduI18n.tr('Tah {n} z {m}', { n: this.turn, m: r.turns }));
    this.turnOut.textContent = this.turn + ' / ' + r.turns;
    if (announce) this.announce();
  };

  Player.prototype.announce = function () {
    var s = this.state, self = this;
    var parts = [EduI18n.tr('Tah {n} z {m}.', { n: this.turn, m: this.r.turns })];
    if (this.meIndex >= 0) {
      var q = s.robots[this.meIndex];
      parts.push(EduI18n.tr('Tvůj robot: {points} bodů, energie {energy}, náklad {cargo}, {status}.', { points: q[3], energy: q[1], cargo: q[2], status: ACT[q[4]] }));
    }
    s.events.slice(0, 4).forEach(function (ev) { parts.push(self.eventText(ev)); });
    if (s.events.length > 4) parts.push(EduI18n.tr('A další události: {n}.', { n: s.events.length - 4 }));
    this.live.textContent = parts.join(' ');
  };

  Player.prototype.buildLog = function () {
    var list = this.root.querySelector('[data-rb58-log]'), mine = this.root.querySelector('[data-rb58-logmine]').checked;
    clear(list);
    if (!this.r) return;
    var s = this.stateAt(0), count = 0;
    for (var t = 1; t <= this.r.turns && count < MAX_LOG; t++) {
      this.apply(s, this.r.d[t - 1]);
      for (var e = 0; e < s.events.length && count < MAX_LOG; e++) {
        var ev = s.events[e];
        if (mine && ev.i !== this.meIndex) continue;
        list.appendChild(el('li', ev.i === this.meIndex ? 'is-me' : '', EduI18n.tr('Tah {n}: {text}', { n: t, text: this.eventText(ev) })));
        count++;
      }
    }
    if (count === 0) list.appendChild(el('li', '', EduI18n.tr('Žádné události.')));
  };

  Player.prototype.renderStats = function () {
    var box = this.root.querySelector('[data-rb58-stats]'), self = this;
    clear(box);
    var st = this.me && this.me.stats;
    if (!st) return;
    box.appendChild(el('h3', '', EduI18n.tr('Statistiky tvého robota')));
    var dl = el('dl', 'rb58-dl');
    [[EduI18n.tr('Body'), st.points], [EduI18n.tr('Doručené balíčky'), st.delivered], [EduI18n.tr('Opravy (dokončené uzly)'), st.repairs + ' (' + st.fixed + ')'], [EduI18n.tr('Nabíjení'), st.charges], [EduI18n.tr('Pohyby / zablokováno'), st.moves + ' / ' + st.blocked],
      [EduI18n.tr('Spotřebovaná energie'), st.energy_used], [EduI18n.tr('Efektivita (body na 100 energie)'), st.efficiency], [EduI18n.tr('Průměr kroků skriptu na tah'), st.avg_steps], [EduI18n.tr('Chyby / přetečení kroků'), st.errors + ' / ' + st.budget]].forEach(function (row) {
      dl.appendChild(el('dt', '', row[0]));
      dl.appendChild(el('dd', '', row[1]));
    });
    box.appendChild(dl);
    if (!st.issues || !st.issues.length) { box.appendChild(el('p', 'rb58-ok', EduI18n.tr('Žádné chyby ani varování. Pěkné!'))); return; }
    box.appendChild(el('h3', '', EduI18n.tr('Chyby a varování')));
    var ul = el('ul', 'rb58-issues');
    st.issues.forEach(function (is) {
      var extra = is.count > 1 ? EduI18n.tr(', celkem {n}×', { n: is.count }) : '';
      var linePrefix = is.line > 0 ? EduI18n.tr('Řádek {n}: ', { n: is.line }) : '';
      var tip = is.tip ? EduI18n.tr(' Tip: {tip}', { tip: is.tip }) : '';
      var li = el('li', '', linePrefix + is.message + EduI18n.tr(' (poprvé v tahu {n}{extra})', { n: is.turn, extra: extra }) + tip);
      if (is.line > 0 && self.onJump) {
        var b = el('button', 'rb58-link', EduI18n.tr('Ukaž řádek {n}', { n: is.line }));
        b.type = 'button';
        b.addEventListener('click', function () { self.onJump(is.line); });
        li.appendChild(document.createTextNode(' '));
        li.appendChild(b);
      }
      ul.appendChild(li);
    });
    box.appendChild(ul);
  };

  // -------------------------------------------------------------------------
  // Editor
  // -------------------------------------------------------------------------
  function Editor(area, gutter, bytes) {
    this.area = area; this.gutter = gutter; this.bytes = bytes; this.escaped = false;
    var self = this;
    area.addEventListener('input', function () { self.sync(); });
    area.addEventListener('scroll', function () { gutter.scrollTop = area.scrollTop; });
    area.addEventListener('blur', function () { self.escaped = false; });
    area.addEventListener('keydown', function (ev) { self.key(ev); });
    this.sync();
  }
  Editor.prototype.sync = function () {
    var lines = this.area.value.split('\n').length, out = [];
    for (var i = 1; i <= lines; i++) out.push(i);
    this.gutter.textContent = out.join('\n');
    this.gutter.scrollTop = this.area.scrollTop;
    var size = window.TextEncoder ? new TextEncoder().encode(this.area.value).length : this.area.value.length;
    this.bytes.textContent = size + ' / 4096 B' + (size > 4096 ? EduI18n.tr(' – příliš dlouhé!') : '');
    this.bytes.classList.toggle('is-bad', size > 4096);
  };
  Editor.prototype.replace = function (start, end, text, caretStart, caretEnd) {
    this.area.setRangeText(text, start, end, 'end');
    this.area.setSelectionRange(caretStart, caretEnd);
    this.sync();
  };
  Editor.prototype.key = function (ev) {
    var a = this.area, v = a.value, s = a.selectionStart, e = a.selectionEnd;
    if (ev.key === 'Escape') { this.escaped = true; return; }
    if (ev.key === 'Tab' && !this.escaped && !ev.ctrlKey && !ev.altKey && !ev.metaKey) {
      ev.preventDefault();
      var ls = v.lastIndexOf('\n', s - 1) + 1;
      if (!ev.shiftKey && s === e) { this.replace(s, e, '    ', s + 4, s + 4); return; }
      var block = v.slice(ls, e), lines = block.split('\n');
      var changed = lines.map(function (l) { return ev.shiftKey ? l.replace(/^ {1,4}/, '') : '    ' + l; }).join('\n');
      this.replace(ls, e, changed, ls, ls + changed.length);
      return;
    }
    this.escaped = false;
    if (ev.key === 'Enter' && !ev.shiftKey && !ev.ctrlKey) {
      var lineStart = v.lastIndexOf('\n', s - 1) + 1, line = v.slice(lineStart, s);
      var indent = (line.match(/^ */) || [''])[0] + (/:\s*$/.test(line.replace(/#.*$/, '')) ? '    ' : '');
      ev.preventDefault();
      this.replace(s, e, '\n' + indent, s + 1 + indent.length, s + 1 + indent.length);
    }
  };
  Editor.prototype.jump = function (line) {
    var lines = this.area.value.split('\n'), start = 0;
    for (var i = 0; i < line - 1 && i < lines.length; i++) start += lines[i].length + 1;
    this.area.focus();
    this.area.setSelectionRange(start, start + (lines[line - 1] || '').length);
    this.area.scrollTop = Math.max(0, (line - 4) * 20);
  };

  // -------------------------------------------------------------------------
  // Žákovská stránka
  // -------------------------------------------------------------------------
  function Student(root) {
    this.root = root;
    this.apiUrl = root.getAttribute('data-api');
    this.csrf = root.getAttribute('data-csrf');
    this.version = root.getAttribute('data-version') || '';
    this.out = root.querySelector('[data-rb58-out]');
    this.errors = root.querySelector('[data-rb58-errors]');
    this.examples = readJson(root, 'data-rb58-examples') || {};
    this.editor = new Editor(root.querySelector('[data-rb58-code]'), root.querySelector('[data-rb58-gutter]'), root.querySelector('[data-rb58-bytes]'));
    this.player = new Player(root.querySelector('[data-rb58-player]'));
    var self = this;
    this.player.onJump = function (line) { self.editor.jump(line); };
    root.querySelectorAll('[data-rb58-op]').forEach(function (b) { b.addEventListener('click', function () { self.op(b.getAttribute('data-rb58-op')); }); });
    root.querySelector('[data-rb58-load-example]').addEventListener('click', function () { self.loadExample(); });
    root.addEventListener('click', function (ev) {
      var btn = ev.target.closest ? ev.target.closest('[data-rb58-replay]') : null;
      if (btn) self.replay(btn.getAttribute('data-rb58-replay'));
    });
    this.schedulePoll();
    document.addEventListener('visibilitychange', function () { if (!document.hidden) self.poll(); });
  }

  Student.prototype.api = function (op, params) {
    var fd = new FormData(), self = this;
    fd.append('csrf', this.csrf);
    fd.append('op', op);
    Object.keys(params || {}).forEach(function (k) { fd.append(k, params[k]); });
    return fetch(this.apiUrl, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (res) {
      return res.json().catch(function () { return { ok: false, error: EduI18n.tr('Server odpověděl nečekaně ({n}).', { n: res.status }) }; });
    }).catch(function () { self.say(EduI18n.tr('Nepodařilo se spojit se serverem. Zkontroluj připojení.'), true); return null; });
  };

  Student.prototype.say = function (text, bad) {
    this.out.textContent = text;
    this.out.classList.toggle('is-bad', !!bad);
  };

  Student.prototype.showErrors = function (list) {
    var self = this;
    clear(this.errors);
    (list || []).forEach(function (err) {
      if (!err) return;
      var linePrefix = err.line > 0 ? EduI18n.tr('Řádek {n}: ', { n: err.line }) : '';
      var tip = err.tip ? EduI18n.tr(' Tip: {tip}', { tip: err.tip }) : '';
      var li = el('li', err.kind === 'warn' ? 'is-warn' : 'is-bad', linePrefix + err.message + tip);
      if (err.line > 0) {
        var b = el('button', 'rb58-link', EduI18n.tr('Na řádek {n}', { n: err.line }));
        b.type = 'button';
        b.addEventListener('click', function () { self.editor.jump(err.line); });
        li.appendChild(document.createTextNode(' '));
        li.appendChild(b);
      }
      self.errors.appendChild(li);
    });
  };

  Student.prototype.busy = function (on) {
    this.root.querySelectorAll('[data-rb58-op]').forEach(function (b) { b.disabled = on; });
    this.root.setAttribute('aria-busy', on ? 'true' : 'false');
  };

  Student.prototype.op = function (op) {
    var self = this, code = this.editor.area.value, params = { code: code };
    if (op === 'test') {
      params.turns = this.root.querySelector('[data-rb58-turns]').value;
      params.map = this.root.querySelector('[data-rb58-map]').value;
      params.sparring = this.root.querySelector('[data-rb58-sparring]').checked ? '1' : '0';
      this.say(EduI18n.tr('Simuluji…'));
    }
    if (op === 'submit') params.match = this.root.querySelector('[data-rb58-match]').value;
    this.busy(true);
    this.api(op, params).then(function (res) {
      self.busy(false);
      if (res) self.handle(op, res);
    });
  };

  Student.prototype.handle = function (op, res) {
    var warns = (res.warnings || []).map(function (w) { return { line: w.line, message: w.message, tip: w.tip, kind: 'warn' }; });
    if (!res.ok) { this.say(res.error || EduI18n.tr('Nepovedlo se.'), true); this.showErrors(res.parse ? [res.parse] : []); return; }
    if (op === 'validate' || res.valid === false) {
      this.say(res.valid === false ? EduI18n.tr('Skript má chybu – oprav ji a zkus to znovu.') : EduI18n.tr('Skript je v pořádku.'), res.valid === false);
      this.showErrors(res.valid === false ? [res.parse || res.error] : warns);
      return;
    }
    this.showErrors(warns);
    if (op === 'test') {
      var st = res.me.stats;
      this.root.querySelector('[data-rb58-player-empty]').hidden = true;
      this.player.load(res);
      var sparringPart = res.sparring !== null ? EduI18n.tr(' (cvičný soupeř {n})', { n: res.sparring }) : '';
      this.say(EduI18n.tr('Hotovo: {points} bodů za {turns} tahů', { points: st.points, turns: res.replay.turns }) + sparringPart + EduI18n.tr('. Chyby a tipy najdeš pod přehrávačem.'));
    } else if (op === 'save') {
      this.say(EduI18n.tr('Koncept uložen v {time}.', { time: timeText(res.at).split(' ').pop() }));
    } else if (op === 'submit') {
      this.say(EduI18n.tr('Odevzdáno! Pokus č. {n} – počítá se poslední platný skript.', { n: res.n }));
      this.poll(true);
    }
  };

  Student.prototype.loadExample = function () {
    var id = this.root.querySelector('[data-rb58-example]').value, text = this.examples[id];
    if (!text) return;
    if (this.editor.area.value.trim() !== '' && !window.confirm(EduI18n.tr('Nahradit tvůj skript ukázkou? Neuložené změny se ztratí.'))) return;
    this.editor.area.value = text;
    this.editor.sync();
    this.say(EduI18n.tr('Ukázka načtena. Zkus ji otestovat a pak upravit.'));
  };

  Student.prototype.replay = function (id) {
    var self = this;
    this.say(EduI18n.tr('Načítám záznam zápasu…'));
    this.api('replay', { match: id }).then(function (res) {
      if (!res) return;
      if (!res.ok) { self.say(res.error || EduI18n.tr('Záznam se nepodařilo načíst.'), true); return; }
      self.root.querySelector('[data-rb58-player-empty]').hidden = true;
      self.player.load(res);
      var xpPart = res.xp_claimed > 0 ? EduI18n.tr(' Připsali jsme ti XP za zápas.') : '';
      self.say(EduI18n.tr('Záznam zápasu je připravený v přehrávači.') + xpPart);
      self.player.root.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
    });
  };

  Student.prototype.schedulePoll = function () {
    var self = this;
    clearTimeout(this.pollTimer);
    this.pollTimer = setTimeout(function () { self.poll(); }, POLL_MS);
  };

  Student.prototype.poll = function (force) {
    var self = this;
    if (document.hidden && !force) { this.schedulePoll(); return; }
    this.api('state', { v: force ? '' : this.version }).then(function (res) {
      if (res && res.ok && res.changed && res.state) { self.version = res.version; self.renderState(res.state); }
      self.schedulePoll();
    });
  };

  Student.prototype.renderState = function (state) {
    var box = this.root.querySelector('[data-rb58-matches]'), open = [], select = this.root.querySelector('[data-rb58-match]');
    clear(box);
    if (!state.matches.length) box.appendChild(el('p', 'rb58-muted', EduI18n.tr('Zatím žádný zápas.')));
    var ul = el('ul', 'rb58-matches');
    state.matches.forEach(function (m) { ul.appendChild(renderMatch(m)); if (m.status === 'open') open.push(m); });
    if (state.matches.length) box.appendChild(ul);
    var keep = select.value;
    clear(select);
    open.forEach(function (m) { var o = el('option', '', EduI18n.tr('{title} (do {time})', { title: m.title, time: timeText(m.deadline) })); o.value = m.id; select.appendChild(o); });
    if (keep) select.value = keep;
    this.root.querySelector('[data-rb58-submit-box]').hidden = open.length === 0;
    this.root.querySelector('[data-rb58-no-open]').hidden = open.length !== 0;
    renderLeague(this.root.querySelector('[data-rb58-league]'), state.league);
  };

  var STATUS = { open: EduI18n.tr('Příprava – odevzdávej'), closed: EduI18n.tr('Uzávěrka – čeká na simulaci'), finished: EduI18n.tr('Odehráno') };

  function resultsTable(res, caption, teams) {
    var rows = teams ? res.teams : res.robots, wrap = el('div', 'rb58-table-wrap'), t = el('table', 'rb58-table');
    t.appendChild(el('caption', '', caption));
    var head = el('tr');
    var headers = [EduI18n.tr('Místo'), teams ? EduI18n.tr('Tým') : EduI18n.tr('Robot')];
    if (!teams) headers.push(EduI18n.tr('Tým'));
    headers.push(EduI18n.tr('Body'), teams ? EduI18n.tr('Robotů') : EduI18n.tr('Doručeno / opravy'));
    headers.forEach(function (h) { var th = el('th', '', h); th.setAttribute('scope', 'col'); head.appendChild(th); });
    var thead = el('thead'); thead.appendChild(head); t.appendChild(thead);
    var body = el('tbody');
    rows.forEach(function (r) {
      var tr = el('tr', r.me ? 'is-me' : '');
      tr.appendChild(el('td', '', r.rank + '.'));
      var th = el('th', '', r.name + (r.me ? EduI18n.tr(' (ty)') : '')); th.setAttribute('scope', 'row'); tr.appendChild(th);
      if (!teams) tr.appendChild(el('td', '', r.team || '–'));
      tr.appendChild(el('td', '', r.points));
      tr.appendChild(el('td', '', teams ? r.robots + ' / ' + r.size : r.delivered + ' / ' + r.repairs));
      body.appendChild(tr);
    });
    t.appendChild(body);
    wrap.appendChild(t);
    return wrap;
  }

  function renderMatch(m) {
    var li = el('li', 'rb58-match is-' + m.status), head = el('div', 'rb58-match-head');
    head.appendChild(el('h3', '', m.title));
    head.appendChild(el('span', 'rb58-badge is-' + m.status, STATUS[m.status] || m.status));
    li.appendChild(head);
    var modeText = m.mode === 'teams' ? EduI18n.tr('Týmy') : EduI18n.tr('Každý sám za sebe');
    var turnsText = EduI18n.trn({ one: '{n} tah', few: '{n} tahy', other: '{n} tahů' }, m.turns);
    li.appendChild(el('p', 'rb58-muted small', modeText + ' · ' + turnsText + ' · ' + EduI18n.tr('uzávěrka {time}', { time: timeText(m.deadline) }) + ' · ' + EduI18n.tr('odevzdáno {n}', { n: m.submitted })));
    if (m.team) {
      var matesOrSize = m.team.mates.length ? EduI18n.tr(' – {mates}', { mates: m.team.mates.join(', ') }) : EduI18n.tr(' ({n} hráčů)', { n: m.team.size });
      li.appendChild(el('p', '', EduI18n.tr('Tvůj tým: {name}', { name: m.team.name }) + matesOrSize));
    }
    var statusText;
    if (m.status === 'finished') statusText = m.played ? EduI18n.tr('Tvůj robot v zápase hrál.') : EduI18n.tr('Do tohohle zápasu jsi neodevzdal(a).');
    else if (m.my) statusText = EduI18n.tr('Tvoje odevzdání: {time} (pokus {n})', { time: timeText(m.my.at), n: m.my.n });
    else statusText = m.status === 'open' ? EduI18n.tr('Zatím jsi neodevzdal(a).') : EduI18n.tr('Do tohohle zápasu jsi neodevzdal(a).');
    li.appendChild(el('p', '', statusText));
    if (m.status === 'finished' && m.results) {
      if (m.place) li.appendChild(el('p', 'rb58-place', EduI18n.tr('Tvoje umístění: {n}. místo', { n: m.place })));
      li.appendChild(resultsTable(m.results, EduI18n.tr('Výsledky – {title}', { title: m.title }), m.mode === 'teams'));
      var b = el('button', 'rb58-btn', EduI18n.tr('Přehrát zápas'));
      b.type = 'button';
      b.setAttribute('data-rb58-replay', m.id);
      li.appendChild(b);
    }
    return li;
  }

  function renderLeague(section, league) {
    if (!section || !league) return;
    section.querySelector('h2').textContent = EduI18n.tr('Liga · {label}', { label: league.label });
    var old = section.querySelector('.rb58-table-wrap, [data-rb58-league-empty]');
    if (old) section.removeChild(old);
    if (!league.rows.length) { var p = el('p', 'rb58-muted', EduI18n.tr('V tomhle pololetí se ještě nehrálo.')); p.setAttribute('data-rb58-league-empty', ''); section.appendChild(p); return; }
    var wrap = el('div', 'rb58-table-wrap'), t = el('table', 'rb58-table'), head = el('tr'), body = el('tbody');
    t.appendChild(el('caption', 'rb58-sr', EduI18n.tr('Ligová tabulka')));
    [EduI18n.tr('Pořadí'), EduI18n.tr('Hráč'), EduI18n.tr('Zápasy'), EduI18n.tr('Výhry'), EduI18n.tr('Ligové body'), EduI18n.tr('Body robotů')].forEach(function (h) { var th = el('th', '', h); th.setAttribute('scope', 'col'); head.appendChild(th); });
    var thead = el('thead'); thead.appendChild(head); t.appendChild(thead);
    league.rows.forEach(function (r) {
      var tr = el('tr', r.me ? 'is-me' : '');
      tr.appendChild(el('td', '', r.rank + '.'));
      var th = el('th', '', r.name + (r.me ? EduI18n.tr(' (ty)') : '')); th.setAttribute('scope', 'row'); tr.appendChild(th);
      [r.matches, r.wins, r.league, r.points].forEach(function (v) { tr.appendChild(el('td', '', v)); });
      body.appendChild(tr);
    });
    t.appendChild(body);
    wrap.appendChild(t);
    section.appendChild(wrap);
  }

  // -------------------------------------------------------------------------
  // Učitel a projektor: vložený záznam + polling stavu (verze → {changed:false})
  // -------------------------------------------------------------------------
  function initEmbedded(root) {
    var data = readJson(root, 'data-rb58-replay-json'), host = root.querySelector('[data-rb58-player]');
    if (!data || !host) return;
    new Player(host).load(data);
  }

  function initPoll(node) {
    var url = node.getAttribute('data-rb58-poll'), version = node.getAttribute('data-version') || '', status = node.getAttribute('data-status'), delay = 10000;
    function tick() {
      if (document.hidden) { setTimeout(tick, delay); return; }
      fetch(url + '&v=' + encodeURIComponent(version), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
        delay = 10000;
        if (!res || !res.ok || !res.changed) return;
        version = res.version;
        var count = node.querySelector('[data-rb58-submitted]');
        if (count) count.textContent = res.submitted;
        if (res.status !== status) {
          if (node.getAttribute('data-reload') === '1') { window.location.reload(); return; }
          var live = node.querySelector('[data-rb58-teacher-live]'), badge = node.querySelector('[data-rb58-status]');
          if (badge) badge.textContent = STATUS[res.status] || res.status;
          if (live) live.textContent = 'Stav zápasu se změnil. Obnov stránku pro aktuální tlačítka.';
          status = res.status;
        }
      }).catch(function () { delay = Math.min(60000, delay * 2); }).then(function () {
        if (status !== 'finished') setTimeout(tick, delay);
      });
    }
    if (status !== 'finished') setTimeout(tick, delay);
  }

  function init() {
    document.querySelectorAll('[data-rb58-student]').forEach(function (root) { new Student(root); });
    document.querySelectorAll('[data-rb58-teacher], .rb58-projector').forEach(initEmbedded);
    document.querySelectorAll('[data-rb58-poll]').forEach(initPoll);
    document.querySelectorAll('form[data-rb58-confirm]').forEach(function (f) {
      f.addEventListener('submit', function (ev) { if (!window.confirm(f.getAttribute('data-rb58-confirm'))) ev.preventDefault(); });
    });
    document.querySelectorAll('.rb58 time[datetime]').forEach(function (t) { t.textContent = timeText(t.getAttribute('datetime')); });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
}());
