/* EDUCANET v52 · Tutorial Mode
   GSAP 3 scény pro lekce a témata, interaktivní úkoly, krokový průvodce lekcí, kalendář a programy. */
var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};
(() => {
  'use strict';
  const gsap = window.gsap;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (gsap) {
    ['MotionPathPlugin', 'Flip', 'Draggable', 'TextPlugin'].forEach((p) => { if (window[p]) gsap.registerPlugin(window[p]); });
  }
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  // Odhalovací animace nikdy nesmí nechat obsah skrytý (pozastavený rAF, úsporný režim, tisk).
  const reveal = (targets, vars) => {
    if (!gsap || reduce || document.visibilityState === 'hidden') return;
    const list = gsap.utils.toArray(targets); if (!list.length) return;
    gsap.from(list, Object.assign({ clearProps: 'opacity,transform' }, vars));
    setTimeout(() => gsap.set(list, { clearProps: 'opacity,transform' }), 1500);
  };
  const shuffle = (arr) => { const a = arr.slice(); for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; };

  // ---------------------------------------------------------------------
  // SVG pomocníci
  // ---------------------------------------------------------------------
  const W = 480, H = 220;
  const box = (id, x, y, w, h, label, sub = '', cls = '') =>
    `<g id="${id}" class="n ${cls}"><rect x="${x}" y="${y}" width="${w}" height="${h}" rx="10"/><text x="${x + w / 2}" y="${y + h / 2 + (sub ? -3 : 5)}">${esc(label)}</text>${sub ? `<text class="sub" x="${x + w / 2}" y="${y + h / 2 + 13}">${esc(sub)}</text>` : ''}</g>`;
  const line = (x1, y1, x2, y2, cls = 'ln') => `<line class="${cls}" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"/>`;
  const txt = (id, x, y, s, cls = 'lbl') => `<text id="${id}" class="${cls}" x="${x}" y="${y}">${esc(s)}</text>`;
  const pk = (id = 'pk', cls = 'pk') => `<circle id="${id}" class="${cls}" r="7" cx="0" cy="0" opacity="0"/>`;
  const svg = (stage, inner) => { stage.innerHTML = `<svg viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg" class="t52-svg">${inner}</svg>`; return stage.querySelector('svg'); };
  const $ = (root, sel) => root.querySelector(sel);
  const move = (tl, el, pts, dur = 0.55) => { tl.set(el, { x: pts[0][0], y: pts[0][1], opacity: 1 }); pts.slice(1).forEach((p) => tl.to(el, { x: p[0], y: p[1], duration: dur, ease: 'power1.inOut' })); return tl; };

  // Každá scéna vrací timeline se štítky s0…s3 (pro režim „po krocích“).
  const SCENES = {
    dns(stage) {
      const s = svg(stage, [line(100, 110, 180, 110), line(260, 100, 350, 40), line(260, 110, 350, 110), line(260, 120, 350, 180),
        box('c', 20, 88, 80, 44, 'Klient', 'www.skola.cz?'), box('r', 180, 88, 80, 44, 'Resolver'), box('root', 350, 20, 110, 40, 'Kořen .'), box('tld', 350, 90, 110, 40, 'TLD .cz'), box('auth', 350, 160, 110, 40, 'skola.cz', 'autoritativní'),
        txt('ans', 300, 214, 'A → 93.184.216.34', 'lbl strong'), pk()].join(''));
      const tl = gsap.timeline({ paused: true }); const p = $(s, '#pk');
      tl.set($(s, '#ans'), { opacity: 0 }).addLabel('s0'); move(tl, p, [[100, 110], [180, 110]]);
      tl.addLabel('s1'); move(tl, p, [[260, 105], [350, 40], [260, 110], [350, 110], [260, 110]], 0.4);
      tl.addLabel('s2'); move(tl, p, [[260, 115], [350, 180]], 0.45); tl.to($(s, '#auth rect'), { fill: 'var(--t52-ok-soft)', duration: 0.3 }).to($(s, '#ans'), { opacity: 1, y: -4, duration: 0.4 });
      tl.addLabel('s3'); move(tl, p, [[350, 180], [260, 110], [100, 110]], 0.45); tl.to($(s, '#c rect'), { fill: 'var(--t52-ok-soft)', duration: 0.3 });
      return tl;
    },
    dhcp(stage) {
      const msgs = [['DISCOVER', 1], ['OFFER', -1], ['REQUEST', 1], ['ACK', -1]];
      const s = svg(stage, [box('cl', 20, 70, 100, 70, 'Klient', '0.0.0.0'), box('sv', 360, 70, 100, 70, 'DHCP server', 'UDP 67'),
        ...msgs.map((m, i) => `<g id="m${i}" opacity="0"><rect class="msg" x="-45" y="-12" width="90" height="24" rx="12"/><text class="lbl w" x="0" y="4">${m[0]}</text></g>`)].join(''));
      const tl = gsap.timeline({ paused: true });
      msgs.forEach((m, i) => {
        const g = $(s, '#m' + i); const from = m[1] > 0 ? 130 : 350; const to = m[1] > 0 ? 350 : 130; const y = 40 + i * 48;
        tl.addLabel('s' + i).set(g, { x: from, y, opacity: 1 }).to(g, { x: to, duration: 0.9, ease: 'power2.inOut' });
      });
      tl.to($(s, '#cl .sub'), { text: '192.168.1.25', duration: 0.4 }).to($(s, '#cl rect'), { fill: 'var(--t52-ok-soft)', duration: 0.3 }, '<');
      return tl;
    },
    ip(stage) {
      let cells = ''; for (let i = 0; i < 32; i++) cells += `<rect class="bit" data-i="${i}" x="${18 + i * 14 + Math.floor(i / 8) * 6}" y="70" width="12" height="34" rx="3"/>`;
      const s = svg(stage, [txt('addr', 240, 44, '192.168.1.25 / 24', 'lbl big'), cells, txt('net', 180, 132, 'síť (24 bitů)', 'lbl net'), txt('host', 420, 132, 'zařízení', 'lbl host'),
        box('a', 60, 160, 150, 44, '192.168.1.25', 'stejná síť', 'soft'), box('b', 270, 160, 150, 44, '192.168.1.40', 'stejná síť', 'soft')].join(''));
      const bits = s.querySelectorAll('.bit'); const tl = gsap.timeline({ paused: true });
      tl.set([$(s, '#net'), $(s, '#host'), $(s, '#a'), $(s, '#b')], { opacity: 0 }).addLabel('s0').from(bits, { scaleY: 0, transformOrigin: '50% 100%', stagger: 0.02, duration: 0.3 });
      tl.addLabel('s1').to([...bits].slice(0, 24), { fill: 'var(--t52-accent)', stagger: 0.015, duration: 0.2 }).to($(s, '#net'), { opacity: 1 });
      tl.addLabel('s2').to([...bits].slice(24), { fill: 'var(--t52-warn)', stagger: 0.03, duration: 0.2 }).to($(s, '#host'), { opacity: 1 });
      tl.addLabel('s3').to([$(s, '#a'), $(s, '#b')], { opacity: 1, y: -4, stagger: 0.2 });
      return tl;
    },
    ports(stage) {
      const ports = ['22', '53', '80', '443'];
      const s = svg(stage, [box('cl', 16, 90, 80, 44, 'Klient'), `<rect class="srv" x="300" y="20" width="160" height="180" rx="14"/>`, txt('sl', 380, 44, 'Server 10.0.0.5', 'lbl'),
        ...ports.map((p, i) => box('p' + p, 320, 58 + i * 34, 120, 26, 'port ' + p, '', 'port')), box('p23', 320, 194, 0, 0, ''),
        txt('cmd', 16, 206, '', 'mono'), pk('a'), pk('b', 'pk bad')].join(''));
      const tl = gsap.timeline({ paused: true });
      tl.addLabel('s0'); move(tl, $(s, '#a'), [[96, 112], [300, 112]], 0.8);
      tl.addLabel('s1'); move(tl, $(s, '#a'), [[300, 112], [320, 173]], 0.5); tl.to($(s, '#p443 rect'), { fill: 'var(--t52-ok-soft)', duration: 0.3 });
      tl.addLabel('s2'); move(tl, $(s, '#b'), [[96, 112], [296, 112]], 0.7); tl.to($(s, '#b'), { x: 180, duration: 0.5, ease: 'bounce.out' }).to($(s, '#b'), { opacity: 0, duration: 0.3 });
      tl.addLabel('s3').to($(s, '#cmd'), { text: '$ ss -tulpn   → 22, 53, 80, 443 LISTEN', duration: 1.4, ease: 'none' });
      return tl;
    },
    routing(stage) {
      const s = svg(stage, [line(90, 90, 150, 90), line(240, 90, 300, 90), line(390, 90, 420, 90),
        box('pc', 10, 68, 80, 44, 'PC', '192.168.50.88'), box('gw', 150, 68, 90, 44, 'Brána', '.50.1'), box('isp', 300, 68, 90, 44, 'Router ISP'), box('dst', 420, 68, 54, 44, '1.1.1.1'),
        txt('ttl', 240, 30, 'TTL 64', 'lbl strong'), txt('tr', 20, 150, '', 'mono'), txt('tr2', 20, 172, '', 'mono'), txt('tr3', 20, 194, '', 'mono'), pk()].join(''));
      const tl = gsap.timeline({ paused: true }); const p = $(s, '#pk');
      tl.addLabel('s0'); move(tl, p, [[90, 90], [150, 90]], 0.6);
      tl.addLabel('s1'); move(tl, p, [[240, 90], [300, 90]], 0.6); tl.to($(s, '#ttl'), { text: 'TTL 63', duration: 0.2 });
      move(tl, p, [[390, 90], [420, 90]], 0.6); tl.to($(s, '#ttl'), { text: 'TTL 62', duration: 0.2 });
      tl.addLabel('s2').to($(s, '#tr'), { text: '1  192.168.50.1   1 ms', duration: 0.6, ease: 'none' }).to($(s, '#tr2'), { text: '2  10.10.0.1       5 ms', duration: 0.6, ease: 'none' }).to($(s, '#tr3'), { text: '3  1.1.1.1        11 ms', duration: 0.6, ease: 'none' });
      tl.addLabel('s3'); move(tl, p, [[420, 90], [90, 90]], 1.2);
      return tl;
    },
    ssh(stage) {
      const s = svg(stage, [box('cl', 20, 60, 120, 60, 'Klient', '🔑 privátní klíč'), box('sv', 340, 60, 120, 60, 'Server', 'authorized_keys'),
        `<rect id="tun" class="tunnel" x="140" y="150" width="200" height="30" rx="15"/>`, txt('tt', 240, 170, 'šifrovaný tunel', 'lbl w'),
        `<g id="env" opacity="0"><rect class="msg" x="-34" y="-12" width="68" height="24" rx="6"/><text class="lbl w" x="0" y="4">výzva</text></g>`].join(''));
      const tl = gsap.timeline({ paused: true }); const env = $(s, '#env');
      tl.set([$(s, '#tun'), $(s, '#tt')], { opacity: 0 }).addLabel('s0').from($(s, '#cl'), { scale: 0.9, transformOrigin: '50% 50%', duration: 0.4 });
      tl.addLabel('s1').from($(s, '#sv'), { scale: 0.9, transformOrigin: '50% 50%', duration: 0.4 }).to($(s, '#sv rect'), { fill: 'var(--t52-accent-soft)', duration: 0.3 });
      tl.addLabel('s2').set(env, { x: 340, y: 90, opacity: 1 }).to(env, { x: 140, duration: 0.8 }).to($(s, '#env text'), { text: 'podpis ✓', duration: 0.2 }).to(env, { x: 340, duration: 0.8 }).to(env, { opacity: 0 });
      tl.addLabel('s3').to($(s, '#tun'), { opacity: 1, duration: 0.2 }).from($(s, '#tun'), { attr: { width: 0 }, duration: 0.7 }, '<').to($(s, '#tt'), { opacity: 1 });
      return tl;
    },
    https(stage) {
      const s = svg(stage, [box('br', 20, 70, 110, 60, 'Prohlížeč'), box('sv', 350, 70, 110, 60, 'Server'),
        `<g id="cert" opacity="0"><rect class="card" x="-40" y="-24" width="80" height="48" rx="6"/><text class="lbl" x="0" y="-2">certifikát</text><text class="sub" x="0" y="14">skola.cz</text></g>`,
        txt('hello', 240, 60, 'ClientHello →', 'lbl'), txt('lock', 240, 110, '🔒', 'lbl big'), txt('data', 240, 180, 'GET /rozvrh', 'mono')].join(''));
      const tl = gsap.timeline({ paused: true });
      tl.set([$(s, '#hello'), $(s, '#lock')], { opacity: 0 }).addLabel('s0').fromTo($(s, '#hello'), { opacity: 0, x: -80 }, { opacity: 1, x: 0, duration: 0.7 });
      tl.addLabel('s1').set($(s, '#cert'), { x: 350, y: 100, opacity: 1 }).to($(s, '#cert'), { x: 160, duration: 0.9, ease: 'power2.out' });
      tl.addLabel('s2').to($(s, '#cert'), { opacity: 0, duration: 0.3 }).fromTo($(s, '#lock'), { opacity: 0, scale: 0.4, transformOrigin: '50% 50%' }, { opacity: 1, scale: 1, duration: 0.5, ease: 'back.out(2)' });
      tl.addLabel('s3').to($(s, '#data'), { text: 'x9#Qa!7z@Lp2', duration: 0.8, ease: 'none' });
      return tl;
    },
    firewall(stage) {
      const rules = ['1  ALLOW 443/tcp', '2  ALLOW 22/tcp z 10.0.0.0/24', '3  DENY  vše ostatní'];
      const s = svg(stage, [`<rect class="wall" x="200" y="20" width="160" height="180" rx="10"/>`, ...rules.map((r, i) => `<g id="r${i}"><rect class="rule" x="210" y="${40 + i * 50}" width="140" height="34" rx="6"/><text class="mono" x="218" y="${62 + i * 50}">${esc(r)}</text></g>`),
        box('sv', 390, 90, 80, 44, 'Server'), txt('x', 180, 190, '✕', 'lbl bad big'), pk('a'), pk('b', 'pk bad')].join(''));
      const tl = gsap.timeline({ paused: true });
      tl.set($(s, '#x'), { opacity: 0 }).addLabel('s0'); move(tl, $(s, '#a'), [[20, 57], [200, 57]], 0.8);
      tl.addLabel('s1').to($(s, '#r0 .rule'), { fill: 'var(--t52-ok-soft)', duration: 0.3 });
      tl.addLabel('s2'); move(tl, $(s, '#a'), [[360, 57], [390, 112]], 0.6);
      tl.addLabel('s3'); move(tl, $(s, '#b'), [[20, 160], [200, 160]], 0.8); tl.to($(s, '#r2 .rule'), { fill: 'var(--t52-bad-soft)', duration: 0.3 }).to($(s, '#b'), { opacity: 0 }).to($(s, '#x'), { opacity: 1, scale: 1.3, transformOrigin: '50% 50%', duration: 0.3 });
      return tl;
    },
    logs(stage) {
      const lines = [['14:02', 'cron', 'info', 'job started'], ['14:10', 'sshd', 'info', 'Accepted key'], ['14:31', 'nginx', 'info', 'started'], ['14:40', 'kernel', 'warn', 'eth0 link up'],
        ['14:52', 'nginx', 'error', 'bind() :80 failed'], ['15:05', 'nginx', 'info', 'reload'], ['15:20', 'cron', 'info', 'job done']];
      const s = svg(stage, [`<rect class="panel" x="10" y="10" width="460" height="200" rx="10"/>`, ...lines.map((l, i) => `<g class="lg" data-svc="${l[1]}" data-t="${l[0]}" data-lvl="${l[2]}"><rect class="hl" x="18" y="${20 + i * 26}" width="444" height="22" rx="5"/><text class="mono" x="28" y="${35 + i * 26}">${l[0]}  ${l[1].padEnd(7, ' ')} ${l[2].padEnd(6, ' ')} ${esc(l[3])}</text></g>`)].join(''));
      const g = [...s.querySelectorAll('.lg')]; const tl = gsap.timeline({ paused: true });
      tl.addLabel('s0').from(g, { opacity: 0, x: -10, stagger: 0.08, duration: 0.3 });
      tl.addLabel('s1').to(g.filter((x) => x.dataset.svc !== 'nginx'), { opacity: 0.18, duration: 0.4 });
      tl.addLabel('s2').to(g.filter((x) => x.dataset.svc === 'nginx' && x.dataset.t < '14:45'), { opacity: 0.18, duration: 0.4 });
      tl.addLabel('s3').to(g.filter((x) => x.dataset.lvl === 'error').map((x) => x.querySelector('.hl')), { fill: 'var(--t52-bad-soft)', duration: 0.3 }).to(g.filter((x) => x.dataset.lvl === 'error'), { x: 6, yoyo: true, repeat: 3, duration: 0.08 });
      return tl;
    },
    systemd(stage) {
      const st = [['inactive', 'dead'], ['activating', 'start'], ['active', 'running'], ['failed', 'exit-code']];
      const s = svg(stage, [...st.map((x, i) => box('st' + i, 12 + i * 118, 70, 104, 50, x[0], x[1], 'pill')), `<circle id="dot" class="pk" r="9" cx="0" cy="0"/>`, txt('cmd', 20, 180, '', 'mono'), txt('loop', 240, 40, '', 'lbl strong')].join(''));
      const tl = gsap.timeline({ paused: true }); const dot = $(s, '#dot');
      tl.set(dot, { x: 64, y: 140 }).addLabel('s0').to($(s, '#st0 rect'), { fill: 'var(--t52-soft)', duration: 0.2 }).to($(s, '#cmd'), { text: '$ sudo systemctl start nginx', duration: 0.8, ease: 'none' });
      tl.addLabel('s1').to(dot, { x: 182, duration: 0.5 }).to($(s, '#st1 rect'), { fill: 'var(--t52-warn-soft)', duration: 0.2 }).to(dot, { x: 300, duration: 0.5 }).to($(s, '#st2 rect'), { fill: 'var(--t52-ok-soft)', duration: 0.2 });
      tl.addLabel('s2').to(dot, { x: 418, duration: 0.6 }).to($(s, '#st3 rect'), { fill: 'var(--t52-bad-soft)', duration: 0.2 }).to($(s, '#cmd'), { text: '$ systemctl status nginx → failed', duration: 0.6, ease: 'none' });
      tl.addLabel('s3').to($(s, '#loop'), { text: '↺ restart + journalctl -u nginx', duration: 0.6 }).to(dot, { x: 300, duration: 0.6, ease: 'back.inOut' }).to($(s, '#st3 rect'), { fill: 'var(--t52-surface)', duration: 0.2 });
      return tl;
    },
    permissions(stage) {
      const groups = ['vlastník', 'skupina', 'ostatní']; const letters = ['r', 'w', 'x']; const on = [1, 1, 0, 1, 0, 0, 0, 0, 0];
      let cells = ''; groups.forEach((g, gi) => { cells += txt('g' + gi, 90 + gi * 150, 40, g, 'lbl'); letters.forEach((l, li) => { const i = gi * 3 + li; cells += `<g class="cell" data-on="${on[i]}"><rect class="bitbox" x="${40 + gi * 150 + li * 34}" y="60" width="30" height="40" rx="6"/><text class="lbl" x="${55 + gi * 150 + li * 34}" y="86">${l}</text><text class="sub val" x="${55 + gi * 150 + li * 34}" y="118">${[4, 2, 1][li]}</text></g>`; }); });
      const s = svg(stage, [cells, txt('oct', 240, 170, 'chmod 640 app.conf', 'lbl big'), txt('d6', 90, 145, '6', 'lbl strong'), txt('d4', 240, 145, '4', 'lbl strong'), txt('d0', 390, 145, '0', 'lbl strong')].join(''));
      const cellsEl = [...s.querySelectorAll('.cell')]; const tl = gsap.timeline({ paused: true });
      tl.set([...s.querySelectorAll('.val'), $(s, '#oct'), $(s, '#d6'), $(s, '#d4'), $(s, '#d0')], { opacity: 0 }).addLabel('s0').from(cellsEl, { y: 12, opacity: 0, stagger: 0.05, duration: 0.25 });
      tl.addLabel('s1').to(s.querySelectorAll('.val'), { opacity: 1, stagger: 0.04 });
      tl.addLabel('s2').to(cellsEl.filter((c) => c.dataset.on === '1').map((c) => c.querySelector('rect')), { fill: 'var(--t52-accent)', stagger: 0.15, duration: 0.25 }).to([$(s, '#d6'), $(s, '#d4'), $(s, '#d0')], { opacity: 1, stagger: 0.15 });
      tl.addLabel('s3').fromTo($(s, '#oct'), { opacity: 0, scale: 0.6, transformOrigin: '50% 50%' }, { opacity: 1, scale: 1, duration: 0.5, ease: 'back.out(2)' });
      return tl;
    },
    filesystem(stage) {
      const nodes = [['root', 220, 20, '/'], ['etc', 40, 90, '/etc'], ['var', 160, 90, '/var'], ['home', 280, 90, '/home'], ['usr', 390, 90, '/usr'], ['log', 160, 160, '/var/log'], ['nginx', 40, 160, 'nginx/']];
      const s = svg(stage, [line(240, 50, 70, 90, 'ln tr'), line(240, 50, 190, 90, 'ln tr'), line(240, 50, 310, 90, 'ln tr'), line(240, 50, 420, 90, 'ln tr'), line(190, 120, 190, 160, 'ln tr'), line(70, 120, 70, 160, 'ln tr'),
        ...nodes.map((n) => box(n[0], n[1], n[2], n[0] === 'log' ? 70 : 60, 30, n[3], '', 'folder')), txt('cmd', 250, 200, '', 'mono')].join(''));
      const tl = gsap.timeline({ paused: true }); const lines = s.querySelectorAll('.tr');
      tl.addLabel('s0').from($(s, '#root'), { scale: 0.5, transformOrigin: '50% 50%', opacity: 0, duration: 0.4 }).from(lines, { opacity: 0, stagger: 0.05 }).from(['#etc', '#var', '#home', '#usr'].map((q) => $(s, q)), { y: -10, opacity: 0, stagger: 0.08 });
      tl.addLabel('s1').to($(s, '#etc rect'), { fill: 'var(--t52-accent-soft)' }).from([$(s, '#nginx')], { opacity: 0, y: -8 });
      tl.addLabel('s2').to($(s, '#var rect'), { fill: 'var(--t52-accent-soft)' }).from([$(s, '#log')], { opacity: 0, y: -8 }).to($(s, '#log rect'), { fill: 'var(--t52-ok-soft)' });
      tl.addLabel('s3').to($(s, '#cmd'), { text: '$ cd /var/log && ls -la', duration: 1, ease: 'none' });
      return tl;
    },
    bash(stage) {
      const code = ['#!/usr/bin/env bash', 'set -euo pipefail', 'tar -czf /backup/web.tgz /var/www', 'cp /backup/web.tgz /mnt/nas/', 'echo "hotovo"'];
      const s = svg(stage, [`<rect class="panel" x="10" y="10" width="330" height="160" rx="10"/>`, ...code.map((c, i) => `<g class="cl"><text class="mono" x="40" y="${38 + i * 28}">${esc(c)}</text><text class="exit" x="320" y="${38 + i * 28}"></text></g>`),
        `<path id="arrow" class="arrow" d="M18 32 l12 -0 M24 26 l6 6 l-6 6"/>`, box('cron', 360, 20, 110, 60, '⏰ cron', '0 2 * * *'), txt('stop', 175, 200, '', 'lbl bad')].join(''));
      const rows = [...s.querySelectorAll('.cl')]; const arrow = $(s, '#arrow'); const tl = gsap.timeline({ paused: true });
      tl.addLabel('s0').from(rows, { opacity: 0, x: -8, stagger: 0.08 });
      tl.addLabel('s1'); [0, 1, 2].forEach((i) => { tl.to(arrow, { y: i * 28, duration: 0.3 }).to(rows[i].querySelector('.exit'), { text: '0', duration: 0.1 }); });
      tl.addLabel('s2').to(arrow, { y: 84, duration: 0.3 }).to(rows[3].querySelector('.exit'), { text: '1', duration: 0.1 }).to(rows[3], { fill: 'var(--t52-bad)', x: 4, yoyo: true, repeat: 3, duration: 0.07 }).to($(s, '#stop'), { text: 'set -e → skript se bezpečně zastavil', duration: 0.5 });
      tl.addLabel('s3').to($(s, '#cron rect'), { fill: 'var(--t52-accent-soft)', duration: 0.3 }).from($(s, '#cron'), { rotation: -6, transformOrigin: '50% 50%', repeat: 3, yoyo: true, duration: 0.1 });
      return tl;
    },
    packet(stage) {
      const layers = [['eth', 30, 40, 420, 150, 'Ethernet · MAC'], ['ip', 70, 60, 340, 110, 'IP · 10.0.0.5 → 1.1.1.1'], ['tcp', 110, 80, 260, 70, 'TCP · port 443'], ['data', 160, 100, 160, 34, 'HTTP data']];
      const s = svg(stage, layers.map((l) => `<g id="${l[0]}"><rect class="layer ${l[0]}" x="${l[1]}" y="${l[2]}" width="${l[3]}" height="${l[4]}" rx="10"/><text class="lbl" x="${l[1] + 12}" y="${l[2] + 16}" text-anchor="start">${esc(l[5])}</text></g>`).join(''));
      const tl = gsap.timeline({ paused: true }); const order = ['data', 'tcp', 'ip', 'eth'];
      tl.set(order.slice(1).map((id) => $(s, '#' + id)), { opacity: 0, scale: 0.9, transformOrigin: '50% 50%' });
      order.forEach((id, i) => { tl.addLabel('s' + i); if (i === 0) tl.from($(s, '#data'), { scale: 0.5, opacity: 0, transformOrigin: '50% 50%', duration: 0.5 }); else tl.to($(s, '#' + id), { opacity: 1, scale: 1, duration: 0.6, ease: 'back.out(1.6)' }); });
      return tl;
    },

    // ---------------- grafika a web ----------------
    hierarchy(stage) {
      const s = svg(stage, [`<rect class="poster" x="150" y="10" width="180" height="200" rx="8"/>`,
        `<rect id="h" class="tb" x="170" y="40" width="140" height="16" rx="3"/>`, `<rect id="d" class="tb" x="170" y="80" width="140" height="16" rx="3"/>`, `<rect id="t" class="tb" x="170" y="120" width="140" height="16" rx="3"/>`, `<rect id="cta" class="tb" x="170" y="160" width="140" height="16" rx="3"/>`,
        txt('n1', 350, 58, '1', 'badge'), txt('n2', 350, 96, '2', 'badge'), txt('n3', 350, 180, '3', 'badge')].join(''));
      const tl = gsap.timeline({ paused: true }); const badges = ['#n1', '#n2', '#n3'].map((q) => $(s, q));
      tl.set(badges, { opacity: 0 }).addLabel('s0').from(['#h', '#d', '#t', '#cta'].map((q) => $(s, q)), { scaleX: 0, transformOrigin: '0 50%', stagger: 0.1, duration: 0.3 });
      tl.addLabel('s1').to($(s, '#h'), { attr: { height: 40, y: 26 }, fill: 'var(--t52-text)', duration: 0.6, ease: 'power2.out' }).to(badges[0], { opacity: 1 });
      tl.addLabel('s2').to($(s, '#d'), { attr: { height: 20, width: 100, y: 84 }, fill: 'var(--t52-muted)', duration: 0.5 }).to($(s, '#t'), { attr: { height: 8, y: 124 }, opacity: 0.5, duration: 0.4 }, '<').to(badges[1], { opacity: 1 });
      tl.addLabel('s3').to($(s, '#cta'), { attr: { height: 28, width: 110, y: 156, rx: 14 }, fill: 'var(--t52-accent)', duration: 0.5, ease: 'back.out(1.7)' }).to(badges[2], { opacity: 1 });
      return tl;
    },
    contrast(stage) {
      stage.innerHTML = '<div class="t52-html-scene t52-contrast-scene"><div class="t52-cs-card" data-card><strong>Přijď na workshop</strong><span>Středa 14:30 · učebna ITuč</span></div><div class="t52-cs-meter"><div class="t52-cs-bar"><i data-bar></i><b class="aa" style="left:22%">AA 4,5</b></div><strong data-ratio>1,4 : 1</strong></div></div>';
      const card = stage.querySelector('[data-card]'); const bar = stage.querySelector('[data-bar]'); const ratio = stage.querySelector('[data-ratio]');
      const st = { r: 1.4 }; const upd = () => { ratio.textContent = st.r.toFixed(1).replace('.', ',') + ' : 1'; bar.style.width = Math.min(100, st.r / 21 * 100 * 1.3) + '%'; };
      const tl = gsap.timeline({ paused: true, onUpdate: upd });
      tl.set(card, { color: '#d9dde3', backgroundColor: '#ffffff' }).set(st, { r: 1.4 }).addLabel('s0').from(card, { y: 10, opacity: 0, duration: 0.4 });
      tl.addLabel('s1').to(card, { color: '#8a94a3', duration: 0.8 }).to(st, { r: 3.0, duration: 0.8 }, '<');
      tl.addLabel('s2').to(card, { color: '#4b5563', duration: 0.8 }).to(st, { r: 7.6, duration: 0.8 }, '<');
      tl.addLabel('s3').to(card, { color: '#111827', duration: 0.6 }).to(st, { r: 17.7, duration: 0.6 }, '<').call(upd);
      upd();
      return tl;
    },
    typography(stage) {
      stage.innerHTML = '<div class="t52-html-scene t52-type-scene"><div data-box><h4 data-h>Nadpis</h4><h5 data-s>Podnadpis sekce</h5><p data-p>Typografický systém dává textu pořadí, rytmus a čitelnost. Délka řádku kolem šedesáti znaků se čte nejpohodlněji.</p><i class="measure" data-m>45–75 znaků</i></div></div>';
      const q = (a) => stage.querySelector(a); const tl = gsap.timeline({ paused: true });
      tl.set([q('[data-h]'), q('[data-s]'), q('[data-p]')], { fontSize: '3cqw', lineHeight: 1.1 }).set(q('[data-box]'), { width: '100%' }).set(q('[data-m]'), { opacity: 0 });
      tl.addLabel('s0').from(q('[data-box]'), { opacity: 0, y: 8, duration: 0.4 });
      tl.addLabel('s1').to(q('[data-h]'), { fontSize: '6.4cqw', duration: 0.6 }).to(q('[data-s]'), { fontSize: '4.2cqw', duration: 0.6 }, '<0.1');
      tl.addLabel('s2').to(q('[data-p]'), { lineHeight: 1.55, duration: 0.6 });
      tl.addLabel('s3').to(q('[data-box]'), { width: '62%', duration: 0.7, ease: 'power2.inOut' }).to(q('[data-m]'), { opacity: 1 });
      return tl;
    },
    grid(stage) {
      let cols = ''; for (let i = 0; i < 12; i++) cols += `<rect class="col" x="${40 + i * 34}" y="10" width="26" height="200"/>`;
      const blocks = [[60, 30, 180, 50], [300, 60, 120, 40], [110, 130, 90, 60], [240, 140, 170, 50]];
      const target = [[40, 20, 196, 60], [244, 20, 196, 60], [40, 100, 128, 100], [176, 100, 264, 100]];
      const s = svg(stage, [`<g id="cols" opacity="0">${cols}</g>`, ...blocks.map((b, i) => `<rect class="blk b${i}" id="b${i}" x="${b[0]}" y="${b[1]}" width="${b[2]}" height="${b[3]}" rx="6"/>`), txt('gap', 240, 94, '8 px', 'lbl strong')].join(''));
      const tl = gsap.timeline({ paused: true });
      tl.set($(s, '#gap'), { opacity: 0 }).addLabel('s0').from(s.querySelectorAll('.blk'), { opacity: 0, scale: 0.8, transformOrigin: '50% 50%', stagger: 0.08, duration: 0.3 });
      tl.addLabel('s1').to($(s, '#cols'), { opacity: 1, duration: 0.5 });
      tl.addLabel('s2'); target.forEach((t, i) => tl.to($(s, '#b' + i), { attr: { x: t[0], y: t[1], width: t[2], height: t[3] }, duration: 0.6, ease: 'power3.inOut' }, i ? '<0.05' : '>'));
      tl.addLabel('s3').to($(s, '#gap'), { opacity: 1, duration: 0.3 }).to($(s, '#cols'), { opacity: 0.35, duration: 0.3 }, '<');
      return tl;
    },
    rastervector(stage) {
      let px = ''; for (let y = 0; y < 10; y++) for (let x = 0; x < 10; x++) { const d = Math.hypot(x - 4.5, y - 4.5); if (d < 4.8) px += `<rect x="${60 + x * 12}" y="${50 + y * 12}" width="12" height="12" class="${d > 3.8 ? 'px edge' : 'px'}"/>`; }
      const s = svg(stage, [`<g id="rg">${px}</g>`, `<circle id="vc" class="vec" cx="360" cy="110" r="58"/>`, txt('l1', 120, 200, 'rastr (pixely)', 'lbl'), txt('l2', 360, 200, 'vektor (křivka)', 'lbl'), txt('z', 240, 30, '100 %', 'lbl strong')].join(''));
      const tl = gsap.timeline({ paused: true });
      tl.set($(s, '#rg'), { transformOrigin: '50% 50%' }).addLabel('s0').from([$(s, '#rg'), $(s, '#vc')], { opacity: 0, duration: 0.4, stagger: 0.1 });
      tl.addLabel('s1').to($(s, '#z'), { text: '800 %', duration: 0.3 }).to([$(s, '#rg'), $(s, '#vc')], { scale: 1.3, transformOrigin: '50% 50%', duration: 0.8, ease: 'power2.inOut' });
      tl.addLabel('s2').to(s.querySelectorAll('.px'), { stroke: 'var(--t52-surface)', strokeWidth: 1, duration: 0.3 }).to(s.querySelectorAll('.edge'), { fill: 'var(--t52-warn)', duration: 0.3 });
      tl.addLabel('s3').to($(s, '#vc'), { stroke: 'var(--t52-ok)', strokeWidth: 3, duration: 0.3 });
      return tl;
    },
    export(stage) {
      const files = [['JPG', 'fotka'], ['SVG', 'logo'], ['PNG', 'průhlednost'], ['PDF', 'tisk']];
      const s = svg(stage, [...files.map((f, i) => `<rect class="slot" x="${22 + i * 116}" y="116" width="96" height="88" rx="10"/><text class="lbl" x="${70 + i * 116}" y="216">${esc(f[1])}</text>`),
        ...files.map((f, i) => `<g id="f${i}"><rect class="file" x="-34" y="-40" width="68" height="80" rx="8"/><text class="lbl strong" x="0" y="6">${f[0]}</text></g>`)].join(''));
      const tl = gsap.timeline({ paused: true });
      files.forEach((f, i) => { const g = $(s, '#f' + i); tl.set(g, { x: 240, y: 60, opacity: 0 }); });
      files.forEach((f, i) => { const g = $(s, '#f' + i); tl.addLabel('s' + i).to(g, { opacity: 1, duration: 0.2 }).to(g, { x: 70 + i * 116, y: 160, duration: 0.7, ease: 'power3.out' }); });
      return tl;
    },
    crop(stage) {
      const s = svg(stage, [`<defs><linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#bcd3ff"/><stop offset="1" stop-color="#eef3ff"/></linearGradient></defs>`,
        `<rect x="40" y="20" width="400" height="180" fill="url(#sky)" rx="6"/><circle cx="330" cy="70" r="24" fill="#ffc94d"/><polygon points="40,200 170,90 260,170 340,110 440,200" fill="#7c93b8"/><circle cx="170" cy="150" r="10" fill="#1f2a44"/><rect x="165" y="158" width="10" height="30" fill="#1f2a44"/>`,
        `<g id="thirds" opacity="0">${line(173, 20, 173, 200, 'third')}${line(306, 20, 306, 200, 'third')}${line(40, 80, 440, 80, 'third')}${line(40, 140, 440, 140, 'third')}</g>`,
        `<rect id="frame" class="frame" x="40" y="20" width="400" height="180"/>`, txt('ratio', 240, 214, 'originál', 'lbl strong')].join(''));
      const tl = gsap.timeline({ paused: true }); const fr = $(s, '#frame');
      tl.addLabel('s0').from(fr, { opacity: 0, duration: 0.3 });
      tl.addLabel('s1').to($(s, '#thirds'), { opacity: 1, duration: 0.5 });
      tl.addLabel('s2').to(fr, { attr: { x: 110, y: 20, width: 144, height: 180 }, duration: 0.8, ease: 'power2.inOut' }).to($(s, '#ratio'), { text: '4 : 5', duration: 0.2 });
      tl.addLabel('s3').to(fr, { attr: { x: 60, y: 45, width: 320, height: 180 * 0.75 }, duration: 0.8, ease: 'power2.inOut' }).to($(s, '#ratio'), { text: '16 : 9', duration: 0.2 });
      return tl;
    },
    responsive(stage) {
      stage.innerHTML = '<div class="t52-html-scene t52-resp-scene"><div class="t52-device" data-dev><div class="t52-resp-grid" data-grid>' + [1, 2, 3, 4, 5, 6].map((n) => `<span>Karta ${n}</span>`).join('') + '</div></div><strong data-w>1280 px</strong></div>';
      const dev = stage.querySelector('[data-dev]'); const grid = stage.querySelector('[data-grid]'); const w = stage.querySelector('[data-w]');
      const setCols = (n, label) => () => {
        if (!window.Flip) { grid.style.gridTemplateColumns = `repeat(${n},1fr)`; w.textContent = label; return; }
        const state = Flip.getState(grid.children); grid.style.gridTemplateColumns = `repeat(${n},1fr)`; w.textContent = label; Flip.from(state, { duration: 0.6, ease: 'power2.inOut' });
      };
      const tl = gsap.timeline({ paused: true });
      tl.call(setCols(3, '1280 px')).set(dev, { width: '92%' }).addLabel('s0').from(grid.children, { opacity: 0, y: 8, stagger: 0.05 });
      tl.addLabel('s1').to(dev, { width: '62%', duration: 0.6 }).call(setCols(2, '768 px'));
      tl.addLabel('s2').to(dev, { width: '34%', duration: 0.6 }).call(setCols(1, '375 px'));
      tl.addLabel('s3').to(grid.children, { backgroundColor: 'var(--t52-accent-soft)', stagger: 0.06, duration: 0.2 }).to(grid.children, { backgroundColor: 'var(--t52-soft)', stagger: 0.06, duration: 0.2 });
      return tl;
    },
    components(stage) {
      stage.innerHTML = '<div class="t52-html-scene t52-comp-scene"><button type="button" tabindex="-1" class="t52-demo-btn" data-b>Přihlásit se</button><em data-state>default</em><span class="t52-cursor" data-c>➤</span></div>';
      const b = stage.querySelector('[data-b]'); const st = stage.querySelector('[data-state]'); const c = stage.querySelector('[data-c]');
      const tl = gsap.timeline({ paused: true });
      tl.set(b, { clearProps: 'all' }).set(c, { x: 120, y: 60, opacity: 0 }).addLabel('s0').from(b, { scale: 0.8, opacity: 0, duration: 0.4 }).set(st, { text: 'default' });
      tl.addLabel('s1').to(c, { opacity: 1, x: 10, y: 6, duration: 0.6 }).to(b, { backgroundColor: '#2446b5', y: -2, boxShadow: '0 6px 14px rgba(48,86,211,.3)', duration: 0.3 }).set(st, { text: 'hover' });
      tl.addLabel('s2').to(c, { opacity: 0, duration: 0.2 }).to(b, { y: 0, boxShadow: '0 0 0 4px rgba(48,86,211,.35)', duration: 0.3 }).set(st, { text: 'focus (Tab)' });
      tl.addLabel('s3').to(b, { backgroundColor: '#cfd5dd', color: '#6b7280', boxShadow: 'none', duration: 0.4 }).set(st, { text: 'disabled' });
      return tl;
    },
    forms(stage) {
      stage.innerHTML = '<div class="t52-html-scene t52-form-scene"><label data-l>E-mail</label><div class="t52-input" data-i><span data-v></span></div><small data-e>Chybí zavináč.</small><small data-h>Zadej e-mail ve tvaru jmeno@skola.cz</small><b data-ok>✓ Přihláška odeslána</b></div>';
      const q = (a) => stage.querySelector(a); const tl = gsap.timeline({ paused: true });
      tl.set([q('[data-l]'), q('[data-e]'), q('[data-h]'), q('[data-ok]')], { opacity: 0 }).set(q('[data-v]'), { text: '' }).set(q('[data-i]'), { borderColor: '#cfd5dd' });
      tl.addLabel('s0').to(q('[data-l]'), { opacity: 1, y: -2, duration: 0.3 }).to(q('[data-v]'), { text: 'jan.novak.skola.cz', duration: 0.9, ease: 'none' });
      tl.addLabel('s1').to(q('[data-i]'), { borderColor: '#b42318', x: 4, yoyo: true, repeat: 3, duration: 0.07 }).to(q('[data-e]'), { opacity: 1 });
      tl.addLabel('s2').to(q('[data-h]'), { opacity: 1 }).to(q('[data-v]'), { text: 'jan.novak@skola.cz', duration: 0.6, ease: 'none' });
      tl.addLabel('s3').to([q('[data-e]'), q('[data-h]')], { opacity: 0 }).to(q('[data-i]'), { borderColor: '#0f7b4f', duration: 0.3 }).fromTo(q('[data-ok]'), { opacity: 0, y: 6 }, { opacity: 1, y: 0 });
      return tl;
    },
    cta(stage) {
      stage.innerHTML = '<div class="t52-html-scene t52-cta-scene"><div class="t52-cta-card"><strong>Rozvrh workshopů</strong><span>Všechny termíny na jednom místě.</span><div class="t52-cta-row"><button type="button" tabindex="-1" data-main>Klikni zde</button><button type="button" tabindex="-1" data-sec>Více</button><button type="button" tabindex="-1" data-sec2>Sdílet</button></div></div></div>';
      const m = stage.querySelector('[data-main]'); const secs = [stage.querySelector('[data-sec]'), stage.querySelector('[data-sec2]')];
      const tl = gsap.timeline({ paused: true });
      tl.set(m, { clearProps: 'all', text: 'Klikni zde' }).set(secs, { clearProps: 'all' }).addLabel('s0').from(stage.querySelector('.t52-cta-card'), { y: 10, opacity: 0, duration: 0.4 });
      tl.addLabel('s1').to(m, { text: 'Stáhnout rozvrh', duration: 0.6 });
      tl.addLabel('s2').to(m, { backgroundColor: '#3056d3', color: '#fff', scale: 1.08, duration: 0.4, ease: 'back.out(2)' });
      tl.addLabel('s3').to(secs, { backgroundColor: 'transparent', color: '#5f6b7a', borderColor: 'transparent', duration: 0.4 });
      return tl;
    },
    palette(stage) {
      const s = svg(stage, [`<rect id="pri" class="sw" x="30" y="30" width="130" height="90" rx="12" fill="#3056d3"/>`, txt('pl', 95, 140, 'primární', 'lbl'),
        ...['#111827', '#5f6b7a', '#cfd5dd', '#f6f7f9'].map((c, i) => `<rect class="neu" x="${190 + i * 44}" y="30" width="36" height="90" rx="8" fill="${c}"/>`), txt('nl', 256, 140, 'neutrální', 'lbl'),
        `<circle id="acc" cx="400" cy="75" r="28" fill="#ff8a3d"/>`, txt('al', 400, 140, 'akcent', 'lbl'),
        ...[0, 1, 2, 3, 4].map((i) => `<g class="ico"><rect x="${70 + i * 72}" y="160" width="40" height="40" rx="10" class="icobox"/><circle cx="${90 + i * 72}" cy="180" r="${8}" class="icoc"/></g>`)].join(''));
      const tl = gsap.timeline({ paused: true });
      tl.addLabel('s0').from($(s, '#pri'), { scale: 0, transformOrigin: '50% 50%', duration: 0.5, ease: 'back.out(1.7)' }).from($(s, '#pl'), { opacity: 0 });
      tl.addLabel('s1').from(s.querySelectorAll('.neu'), { scaleY: 0, transformOrigin: '50% 100%', stagger: 0.08, duration: 0.3 }).from($(s, '#nl'), { opacity: 0 });
      tl.addLabel('s2').from($(s, '#acc'), { scale: 0, transformOrigin: '50% 50%', duration: 0.4, ease: 'back.out(3)' }).from($(s, '#al'), { opacity: 0 });
      tl.addLabel('s3').from(s.querySelectorAll('.ico'), { y: 14, opacity: 0, stagger: 0.08, duration: 0.3 });
      return tl;
    },
  };

  // ---------------------------------------------------------------------
  // Scény: inicializace, ovládání a titulky
  // ---------------------------------------------------------------------
  const scenes = new WeakMap();
  const initScene = (root) => {
    if (scenes.has(root)) return scenes.get(root);
    const stage = root.querySelector('[data-t52-stage]');
    const build = SCENES[root.dataset.t52Scene] || SCENES.hierarchy;
    if (!gsap || !stage) { root.classList.add('t52-noanim'); return null; }
    let tl;
    try { tl = build(stage); } catch (err) { console.warn('t52 scene', root.dataset.t52Scene, err); stage.textContent = ''; return null; }
    const caps = [...root.querySelectorAll('[data-t52-captions] li')];
    const labels = Object.keys(tl.labels).sort((a, b) => tl.labels[a] - tl.labels[b]);
    const setCap = () => {
      const t = tl.time(); let idx = 0;
      labels.forEach((l, i) => { if (t >= tl.labels[l] - 0.001) idx = i; });
      caps.forEach((c, i) => { c.classList.toggle('active', i === idx); c.classList.toggle('seen', i < idx); });
    };
    tl.eventCallback('onUpdate', setCap);
    const api = {
      tl, labels,
      play() { if (reduce) { tl.progress(1); setCap(); return; } if (tl.progress() >= 1) tl.restart(); else tl.play(); },
      step() {
        const t = tl.time(); const next = labels.find((l) => tl.labels[l] > t + 0.01);
        tl.pause();
        if (reduce) { tl.seek(next ?? tl.duration()); setCap(); return; }
        if (next) tl.tweenTo(next); else tl.tweenTo(tl.duration());
      },
      replay() { tl.restart(); if (reduce) tl.progress(1); },
    };
    tl.progress(0).pause(); setCap();
    root.querySelector('[data-t52-play]')?.addEventListener('click', () => api.play());
    root.querySelector('[data-t52-step]')?.addEventListener('click', () => api.step());
    root.querySelector('[data-t52-replay]')?.addEventListener('click', () => api.replay());
    scenes.set(root, api);
    return api;
  };

  const lazy = new IntersectionObserver((entries) => {
    entries.forEach((en) => {
      if (!en.isIntersecting) return;
      const root = en.target; lazy.unobserve(root);
      const api = initScene(root);
      if (!api) return;
      if (root.matches('[data-t52-autoplay]')) { api.tl.repeat(-1).repeatDelay(1.2); api.play(); }
      else if (root.matches('[data-t52-hover]')) {
        api.tl.progress(reduce ? 1 : 0.999);
        const card = root.closest('a') || root;
        card.addEventListener('mouseenter', () => api.replay());
        card.addEventListener('focus', () => api.replay());
      } else if (!root.closest('[data-t52-slide][hidden]')) api.play();
    });
  }, { rootMargin: '120px' });
  document.querySelectorAll('[data-t52-scene]').forEach((el) => lazy.observe(el));
  window.EDUCANET_T52 = { initScene, scenes: Object.keys(SCENES) };

  // ---------------------------------------------------------------------
  // Interaktivní úkoly
  // ---------------------------------------------------------------------
  const pointsFor = (attempts, hints = 0) => Math.max(1, 3 - (attempts - 1) - hints);
  const saveScore = async (id, points) => {
    try {
      const body = new URLSearchParams({ csrf: csrf(), action: 'tut52_score', exercise: id, points: String(points) });
      const res = await fetch('?view=dashboard', { method: 'POST', body, headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' } });
      const data = await res.json();
      if (data?.ok) document.dispatchEvent(new CustomEvent('t52:score', { detail: data }));
      return data;
    } catch (_) { return null; }
  };

  const ENGINES = {
    order(ex, body) {
      const correct = ex.items.slice();
      let items = shuffle(correct); if (items.join('|') === correct.join('|')) items = items.reverse();
      body.innerHTML = '<ol class="t52-order"></ol>';
      const list = body.querySelector('ol');
      const tUp = EduI18n.tr('Posunout výš');
      const tDown = EduI18n.tr('Posunout níž');
      const render = () => {
        const state = window.Flip ? Flip.getState(list.children) : null;
        list.innerHTML = items.map((it, i) => `<li draggable="true" data-i="${i}"><span class="t52-grip" aria-hidden="true">⋮⋮</span><span>${esc(it)}</span><span class="t52-order-btns"><button type="button" data-up aria-label="${esc(tUp)}" ${i === 0 ? 'disabled' : ''}>↑</button><button type="button" data-down aria-label="${esc(tDown)}" ${i === items.length - 1 ? 'disabled' : ''}>↓</button></span></li>`).join('');
        if (state) Flip.from(state, { duration: 0.35, ease: 'power2.out', targets: list.children });
      };
      const swap = (a, b) => { if (b < 0 || b >= items.length) return; [items[a], items[b]] = [items[b], items[a]]; render(); list.children[b]?.querySelector(a < b ? '[data-down]' : '[data-up]')?.focus(); };
      list.addEventListener('click', (e) => { const li = e.target.closest('li'); if (!li) return; const i = +li.dataset.i; if (e.target.closest('[data-up]')) swap(i, i - 1); if (e.target.closest('[data-down]')) swap(i, i + 1); });
      let dragFrom = null;
      list.addEventListener('dragstart', (e) => { dragFrom = +e.target.closest('li').dataset.i; e.dataTransfer.effectAllowed = 'move'; });
      list.addEventListener('dragover', (e) => e.preventDefault());
      list.addEventListener('drop', (e) => { e.preventDefault(); const li = e.target.closest('li'); if (!li || dragFrom === null) return; const to = +li.dataset.i; const [m] = items.splice(dragFrom, 1); items.splice(to, 0, m); dragFrom = null; render(); });
      render();
      return {
        check() {
          const lis = [...list.children]; let ok = true;
          lis.forEach((li, i) => { const good = items[i] === correct[i]; li.classList.toggle('good', good); li.classList.toggle('bad', !good); if (!good) ok = false; });
          const goodCount = lis.filter((l) => l.classList.contains('good')).length;
          return { ok, message: ok ? EduI18n.tr('Pořadí je správně.') : EduI18n.tr('Správně je {good} z {total}. Oprav červené řádky.', { good: goodCount, total: lis.length }) };
        },
      };
    },
    match(ex, body) {
      const pairs = ex.pairs; const right = shuffle(pairs.map((p) => p[1]));
      const chosen = new Array(pairs.length).fill(null); let active = 0;
      const tHint = esc(EduI18n.tr('Vyber pojem vlevo a potom odpověď vpravo.'));
      body.innerHTML = `<div class="t52-match"><ul class="t52-match-left">${pairs.map((p, i) => `<li><button type="button" data-l="${i}"><span>${esc(p[0])}</span><em data-ans></em></button></li>`).join('')}</ul><ul class="t52-match-right">${right.map((r, i) => `<li><button type="button" data-r="${i}">${esc(r)}</button></li>`).join('')}</ul></div><p class="t52-muted small">${tHint}</p>`;
      const L = [...body.querySelectorAll('[data-l]')]; const R = [...body.querySelectorAll('[data-r]')];
      const paint = () => {
        L.forEach((b, i) => { b.classList.toggle('active', i === active); b.querySelector('[data-ans]').textContent = chosen[i] === null ? '' : right[chosen[i]]; b.classList.toggle('filled', chosen[i] !== null); b.classList.remove('good', 'bad'); });
        R.forEach((b, j) => b.classList.toggle('used', chosen.includes(j)));
      };
      L.forEach((b, i) => b.addEventListener('click', () => { active = i; paint(); }));
      R.forEach((b, j) => b.addEventListener('click', () => {
        const prev = chosen.indexOf(j); if (prev >= 0) chosen[prev] = null;
        chosen[active] = j;
        if (gsap && !reduce) gsap.fromTo(L[active], { scale: 0.97 }, { scale: 1, duration: 0.25 });
        const nextEmpty = chosen.findIndex((c) => c === null); active = nextEmpty >= 0 ? nextEmpty : active; paint();
      }));
      paint();
      return {
        check() {
          if (chosen.includes(null)) return { ok: false, incomplete: true, message: EduI18n.tr('Přiřaď všechny dvojice.') };
          let good = 0;
          L.forEach((b, i) => { const ok = right[chosen[i]] === pairs[i][1]; b.classList.add(ok ? 'good' : 'bad'); if (ok) good++; });
          return { ok: good === pairs.length, message: good === pairs.length ? EduI18n.tr('Všechny dvojice sedí.') : EduI18n.tr('Správně {good} z {total}. Oprav červené.', { good, total: pairs.length }) };
        },
      };
    },
    terminal(ex, body, ctx) {
      let idx = 0; let errors = 0; let hints = 0;
      const tPrompt = esc(EduI18n.tr('Příkaz'));
      const tHintBtn = esc(EduI18n.tr('Nápověda'));
      body.innerHTML = `<div class="t52-term"><div class="t52-term-goal" data-goal></div><pre class="t52-term-out" data-out aria-live="polite"></pre><form class="t52-term-line" data-form><span>student@srv:~$</span><input data-cmd autocomplete="off" autocapitalize="off" spellcheck="false" aria-label="${tPrompt}"></form><div class="t52-term-tools"><button type="button" class="t52-link" data-hint>${tHintBtn}</button><span data-prog></span></div></div>`;
      const goal = body.querySelector('[data-goal]'); const out = body.querySelector('[data-out]'); const input = body.querySelector('[data-cmd]');
      const show = () => {
        goal.textContent = '';
        const goalStrong = document.createElement('b');
        if (idx < ex.tasks.length) {
          goalStrong.textContent = EduI18n.tr('Úkol {n}/{total}:', { n: idx + 1, total: ex.tasks.length });
          goal.appendChild(goalStrong);
          goal.appendChild(document.createTextNode(' ' + ex.tasks[idx].goal));
        } else {
          goalStrong.textContent = '✓ ' + EduI18n.tr('Všechny příkazy jsou správně.');
          goal.appendChild(goalStrong);
        }
        body.querySelector('[data-prog]').textContent = EduI18n.tr('chyby: {errors} · nápovědy: {hints}', { errors, hints });
      };
      const print = (s, cls = '') => { const span = document.createElement('span'); span.className = 't52-line' + (cls ? ' ' + cls : ''); span.textContent = s; out.appendChild(span); out.scrollTop = out.scrollHeight; };
      body.querySelector('[data-form]').addEventListener('submit', (e) => {
        e.preventDefault(); const cmd = input.value.trim(); if (!cmd || idx >= ex.tasks.length) return;
        print('$ ' + cmd, 'cmd'); input.value = '';
        const task = ex.tasks[idx];
        if (new RegExp(task.accept, 'i').test(cmd)) {
          if (task.output) print(task.output); idx++; show();
          if (idx >= ex.tasks.length) { input.disabled = true; ctx.finish(pointsFor(1 + Math.floor(errors / 2), hints), EduI18n.tr('Hotovo – příkazy jsou správně.')); }
        } else { errors++; print(/^[a-z]/i.test(cmd) ? EduI18n.tr('Tento příkaz úkol nesplní. Zkus to jinak nebo použij nápovědu.') : 'command not found', 'err'); show(); }
      });
      body.querySelector('[data-hint]').addEventListener('click', () => { if (idx >= ex.tasks.length) return; hints = Math.min(2, hints + 1); input.value = ex.tasks[idx].hint; input.focus(); show(); });
      show();
      return { auto: true, check() { return { ok: idx >= ex.tasks.length, message: idx >= ex.tasks.length ? EduI18n.tr('Hotovo.') : EduI18n.tr('Napiš příkaz do terminálu a potvrď Enterem.') }; } };
    },
    bits(ex, body) {
      const groups = ['vlastník', 'skupina', 'ostatní']; const letters = ['r', 'w', 'x'];
      body.innerHTML = `<div class="t52-bits">${groups.map((g, gi) => `<div><span>${g}</span>${letters.map((l, li) => `<button type="button" aria-pressed="false" data-b="${gi * 3 + li}">${l}</button>`).join('')}<b data-o="${gi}">0</b></div>`).join('')}</div><p class="t52-octal">chmod <strong data-oct>000</strong> soubor.conf · <code data-sym>---------</code></p>`;
      const btns = [...body.querySelectorAll('[data-b]')];
      const upd = () => {
        const v = btns.map((b) => b.getAttribute('aria-pressed') === 'true');
        const oct = [0, 1, 2].map((g) => (v[g * 3] ? 4 : 0) + (v[g * 3 + 1] ? 2 : 0) + (v[g * 3 + 2] ? 1 : 0));
        oct.forEach((o, g) => { body.querySelector(`[data-o="${g}"]`).textContent = o; });
        body.querySelector('[data-oct]').textContent = oct.join('');
        body.querySelector('[data-sym]').textContent = v.map((on, i) => on ? letters[i % 3] : '-').join('');
        return oct.join('');
      };
      btns.forEach((b) => b.addEventListener('click', () => { b.setAttribute('aria-pressed', b.getAttribute('aria-pressed') === 'true' ? 'false' : 'true'); upd(); }));
      upd();
      return { check() { const o = upd(); return { ok: o === ex.target, message: o === ex.target ? EduI18n.tr('Správně – {value}.', { value: o }) : EduI18n.tr('Máš {value}, cíl je {target}.', { value: o, target: ex.target }) }; } };
    },
    sizes(ex, body) {
      body.innerHTML = `<div class="t52-sizes"><div class="t52-poster-prev"><strong data-h>JARNÍ JAM</strong><span data-d>Středa 22. 4. · aula</span><em data-c>Registrovat</em></div><div class="t52-sliders">${[['h', 'Nadpis', 18], ['d', 'Datum', 18], ['c', 'CTA', 18]].map(([k, l, v]) => `<label><span>${l}</span><input type="range" min="10" max="48" value="${v}" data-s="${k}"><output>${v} px</output></label>`).join('')}</div></div>`;
      const q = (s) => body.querySelector(s); const val = (k) => +q(`[data-s="${k}"]`).value;
      const upd = () => { ['h', 'd', 'c'].forEach((k) => { q(`[data-${k}]`).style.fontSize = val(k) + 'px'; q(`[data-s="${k}"]`).nextElementSibling.textContent = val(k) + ' px'; }); };
      body.querySelectorAll('input').forEach((i) => i.addEventListener('input', upd)); upd();
      return { check() { const h = val('h'), d = val('d'), c = val('c'); const ok = h > d && d >= c && h >= 2 * c; return { ok, message: ok ? EduI18n.tr('Pořadí čtení je jasné.') : (h <= d ? EduI18n.tr('Nadpis musí být největší.') : (d < c ? EduI18n.tr('Datum má být alespoň tak velké jako text CTA.') : EduI18n.tr('Nadpis má být aspoň 2× větší než CTA text.'))) }; } };
    },
    contrast(ex, body) {
      body.innerHTML = `<div class="t52-contrast"><div class="t52-cs-card" data-prev><strong>Přijď na workshop</strong><span>Středa 14:30</span></div><div class="t52-color-row"><label>Text <input type="color" value="#9aa3ad" data-fg></label><label>Pozadí <input type="color" value="#ffffff" data-bg></label><strong data-r></strong></div></div>`;
      const fg = body.querySelector('[data-fg]'), bg = body.querySelector('[data-bg]'), prev = body.querySelector('[data-prev]'), out = body.querySelector('[data-r]');
      const lum = (hex) => { const c = hex.match(/\w\w/g).map((h) => parseInt(h, 16) / 255).map((v) => v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4); return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]; };
      const ratio = () => { const a = lum(fg.value), b = lum(bg.value); return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05); };
      const upd = () => { prev.style.color = fg.value; prev.style.background = bg.value; const r = ratio(); out.textContent = r.toFixed(2).replace('.', ',') + ' : 1'; out.className = r >= 4.5 ? 'ok' : 'bad'; };
      [fg, bg].forEach((i) => i.addEventListener('input', upd)); upd();
      return { check() { const r = ratio(); return { ok: r >= 4.5, message: r >= 4.5 ? EduI18n.tr('Splňuje WCAG AA.') : EduI18n.tr('Kontrast je pod 4,5 : 1.') }; } };
    },
  };

  document.querySelectorAll('[data-t52-exercise]').forEach((root) => {
    let ex; try { ex = JSON.parse(root.dataset.t52Exercise); } catch (_) { return; }
    const body = root.querySelector('[data-t52-ex-body]'); const status = root.querySelector('[data-t52-ex-status]');
    const checkBtn = root.querySelector('[data-t52-check]'); const resetBtn = root.querySelector('[data-t52-reset]');
    let attempts = 0; let solved = false; let engine;
    const noSave = root.hasAttribute('data-no-save');
    const ctx = {
      finish(points, msg) {
        solved = true; root.classList.add('solved');
        status.textContent = noSave ? msg : `${msg} +${points} b.`;
        if (gsap && !reduce) gsap.fromTo(root, { boxShadow: '0 0 0 0 rgba(15,123,79,.5)' }, { boxShadow: '0 0 0 10px rgba(15,123,79,0)', duration: 0.8 });
        if (noSave) return;
        root.dataset.best = String(Math.max(Number(root.dataset.best || 0), points));
        saveScore(root.dataset.id, points).then((d) => { if (d?.best) status.textContent = `${msg} +${points} b. · nejlepší ${d.best}/3`; });
      },
    };
    const start = () => { attempts = 0; solved = false; root.classList.remove('solved'); engine = ENGINES[ex.type]?.(ex, body, ctx); checkBtn.hidden = !!engine?.auto; };
    checkBtn.addEventListener('click', () => {
      if (!engine || solved) return;
      const r = engine.check();
      if (r.incomplete) { status.textContent = r.message; return; }
      attempts++;
      if (r.ok) ctx.finish(pointsFor(attempts), r.message);
      else { status.textContent = EduI18n.tr('{message} (pokus {n})', { message: r.message, n: attempts }); if (gsap && !reduce) gsap.fromTo(body, { x: -6 }, { x: 0, duration: 0.4, ease: 'elastic.out(1,0.3)' }); }
    });
    resetBtn.addEventListener('click', start);
    start();
  });

  // Nápovědy za body: první zdarma, další dvě za 2 body (text přichází ze serveru).
  document.querySelectorAll('[data-t52-hints]').forEach((box) => {
    const out = box.querySelector('[data-t52-hint-out]');
    const balance = box.querySelector('[data-t52-balance] b');
    const exercise = box.closest('[data-t52-exercise]')?.dataset.id || '';
    box.querySelectorAll('[data-t52-hint]').forEach((btn) => btn.addEventListener('click', async () => {
      const level = btn.dataset.t52Hint;
      btn.disabled = true;
      const label = btn.textContent;
      btn.textContent = EduI18n.tr('Odemykám…');
      try {
        const body = new URLSearchParams({ csrf: csrf(), action: 'pts53_hint', scene: box.dataset.scene, exercise, level });
        const res = await fetch('?view=dashboard', { method: 'POST', body, headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' } });
        const data = await res.json();
        if (!data.ok) {
          btn.disabled = false; btn.textContent = label;
          const warn = document.createElement('p');
          const warnTitle = document.createElement('b'); warnTitle.textContent = EduI18n.tr('Nemáš dost bodů');
          warn.appendChild(warnTitle);
          warn.append(data.error || EduI18n.tr('Zkus nejdřív vyřešit jiný úkol.'));
          out.appendChild(warn);
          if (balance && typeof data.balance === 'number') balance.textContent = String(data.balance);
          return;
        }
        const p = document.createElement('p');
        const pTitle = document.createElement('b');
        pTitle.textContent = data.label + (data.cost ? ' · −' + data.cost + ' b.' : ' · ' + EduI18n.tr('zdarma'));
        p.appendChild(pTitle);
        p.append(data.text);
        out.appendChild(p);
        btn.textContent = label.replace(/ · .*$/, '') + ' ✓';
        if (balance && typeof data.balance === 'number') balance.textContent = String(data.balance);
        reveal(p, { opacity: 0, y: 8, duration: 0.35 });
      } catch (_) { btn.disabled = false; btn.textContent = label; }
    }));
  });

  // Projekce v Režimu hodiny: přepínání ukázek lekce na jedné velké ploše.
  document.querySelectorAll('[data-t52-deck]').forEach((deck) => {
    const items = [...deck.querySelectorAll('[data-t52-deck-item]')];
    const tabs = [...deck.querySelectorAll('[data-t52-deck-go]')];
    const show = (i, replay = true) => {
      items.forEach((it, n) => { it.hidden = n !== i; });
      tabs.forEach((t, n) => t.setAttribute('aria-selected', n === i ? 'true' : 'false'));
      const fig = items[i]?.querySelector('[data-t52-scene]');
      if (!fig) return;
      const api = initScene(fig);
      if (api && replay) api.replay();
    };
    tabs.forEach((t, i) => t.addEventListener('click', () => show(i)));
    deck.querySelector('[data-t52-deck-full]')?.addEventListener('click', () => {
      if (!document.fullscreenElement) deck.requestFullscreen?.(); else document.exitFullscreen?.();
    });
    deck.addEventListener('keydown', (e) => {
      const at = tabs.findIndex((t) => t.getAttribute('aria-selected') === 'true');
      if (e.key === 'ArrowRight' && at < tabs.length - 1) { show(at + 1); tabs[at + 1].focus(); }
      if (e.key === 'ArrowLeft' && at > 0) { show(at - 1); tabs[at - 1].focus(); }
    });
    show(0, false);
  });

  // ---------------------------------------------------------------------
  // Krokový průvodce lekcí
  // ---------------------------------------------------------------------
  const tut = document.querySelector('[data-t52-tutorial]');
  if (tut) {
    const slides = [...tut.querySelectorAll('[data-t52-slide]')];
    const dots = tut.querySelector('[data-t52-dots]');
    const bar = tut.querySelector('[data-t52-bar]');
    const counter = tut.querySelector('[data-t52-counter]');
    const prev = tut.querySelector('[data-t52-prev]'); const next = tut.querySelector('[data-t52-next]');
    let cur = -1;
    dots.innerHTML = slides.map((s, i) => `<li><button type="button" data-go="${i}" title="${esc(s.dataset.title || '')}"><b>${s.dataset.done === '1' ? '✓' : i}</b><span>${esc(s.dataset.title || '')}</span></button></li>`).join('');
    const go = (i, anim = true) => {
      i = Math.max(0, Math.min(slides.length - 1, i)); if (i === cur) return;
      const dir = i > cur ? 1 : -1; const old = slides[cur];
      slides.forEach((s, n) => { s.hidden = n !== i; });
      cur = i;
      dots.querySelectorAll('li').forEach((li, n) => { li.className = (n === i ? 'current ' : '') + (slides[n].dataset.done === '1' ? 'done' : ''); });
      dots.querySelector('li.current')?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: anim ? 'smooth' : 'auto' });
      if (bar) bar.style.width = Math.round((i / Math.max(1, slides.length - 1)) * 100) + '%';
      counter.textContent = `${slides[i].dataset.title || ''} · ${i + 1}/${slides.length}`;
      prev.style.visibility = i === 0 ? 'hidden' : 'visible';
      next.hidden = i === slides.length - 1;
      history.replaceState(null, '', '#krok-' + i);
      if (anim && old) {
        reveal(slides[i], { opacity: 0, x: 24 * dir, duration: 0.4, ease: 'power2.out' });
        reveal(slides[i].querySelectorAll('.t52-card, .t52-scene, .t52-exercise'), { y: 12, opacity: 0, stagger: 0.06, duration: 0.35, delay: 0.1 });
      }
      slides[i].querySelectorAll('[data-t52-scene]').forEach((el) => { const api = initScene(el); if (api && api.tl.progress() === 0) api.play(); });
      const timeline = slides[i].querySelector('[data-t52-timeline]');
      if (timeline && gsap && !reduce && !timeline.dataset.done) { timeline.dataset.done = '1'; reveal(timeline.children, { scaleX: 0, transformOrigin: '0 50%', opacity: 0, stagger: 0.08, duration: 0.45, ease: 'power2.out' }); }
      if (anim) tut.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    dots.addEventListener('click', (e) => { const b = e.target.closest('[data-go]'); if (b) go(+b.dataset.go); });
    prev.addEventListener('click', () => go(cur - 1));
    next.addEventListener('click', () => go(cur + 1));
    document.addEventListener('keydown', (e) => {
      if (e.target.closest('input, textarea, select, [contenteditable]') || e.altKey || e.ctrlKey || e.metaKey) return;
      if (e.key === 'ArrowRight') go(cur + 1); if (e.key === 'ArrowLeft') go(cur - 1);
    });

    // Dokončení kroku → progress.php (stejná pravidla jako klasická lekce)
    tut.querySelectorAll('[data-t52-work]').forEach((work) => {
      const slide = work.closest('[data-t52-slide]'); const btn = work.querySelector('[data-t52-complete]'); if (!btn) return;
      const fb = work.querySelector('[data-t52-feedback]'); const tasks = [...work.querySelectorAll('[data-t52-task]')];
      const isQuiz = slide.dataset.kind === 'quiz'; let answer = null;
      const ready = () => { btn.disabled = !(tasks.every((t) => t.checked) && (!isQuiz || answer !== null)); };
      tasks.forEach((t) => t.addEventListener('change', () => { ready(); if (gsap && t.checked && !reduce) gsap.fromTo(t.closest('label'), { backgroundColor: 'rgba(15,123,79,.12)' }, { backgroundColor: 'rgba(15,123,79,0)', duration: 0.8 }); }));
      work.querySelectorAll('[data-t52-answer]').forEach((b) => b.addEventListener('click', () => { work.querySelectorAll('[data-t52-answer]').forEach((x) => x.classList.remove('selected')); b.classList.add('selected'); answer = +b.dataset.t52Answer; ready(); }));
      ready();
      btn.addEventListener('click', async () => {
        btn.disabled = true; btn.textContent = EduI18n.tr('Ukládám…');
        const body = new URLSearchParams({ csrf: csrf(), action: tut.dataset.stepAction, step: slide.dataset.step, lesson: tut.dataset.lesson, tasks_done: String(tasks.filter((t) => t.checked).length) });
        if (answer !== null) body.set('answer', String(answer));
        const say = (msg, ok) => { fb.hidden = false; fb.textContent = msg; fb.className = 't52-feedback ' + (ok ? 'ok' : 'bad'); };
        const tDoneStep = EduI18n.tr('Dokončit krok');
        try {
          const res = await fetch('progress.php', { method: 'POST', body, headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' } });
          const data = await res.json();
          if (!res.ok || !data.ok) throw new Error(data.error || EduI18n.tr('Krok se nepodařilo uložit.'));
          if (data.correct === false) {
            say(data.explanation || EduI18n.tr('To ještě není ono. Zkus jinou odpověď.'), false);
            answer = null; work.querySelectorAll('[data-t52-answer]').forEach((x) => x.classList.remove('selected'));
            btn.textContent = tDoneStep; ready(); return;
          }
          say(data.awarded_xp ? EduI18n.tr('Hotovo · +{xp} XP. Pokračujeme dalším krokem.', { xp: data.awarded_xp }) : EduI18n.tr('Hotovo. Pokračujeme dalším krokem.'), true);
          slide.dataset.done = '1'; btn.replaceWith(Object.assign(document.createElement('span'), { className: 't52-done-badge', textContent: '✓ ' + EduI18n.tr('Krok je hotový') }));
          tasks.forEach((t) => { t.disabled = true; });
          const dot = dots.querySelectorAll('li')[cur]; if (dot) { dot.classList.add('done'); dot.querySelector('b').textContent = '✓'; if (gsap && !reduce) gsap.fromTo(dot, { scale: 1.3 }, { scale: 1, duration: 0.5, ease: 'back.out(3)' }); }
          setTimeout(() => go(cur + 1), 900);
        } catch (err) {
          say(err.message, false); btn.textContent = tDoneStep; ready();
        }
      });
    });

    document.addEventListener('t52:score', () => {
      const el = tut.querySelector('[data-t52-lesson-points]'); if (!el) return;
      const sum = [...tut.querySelectorAll('[data-t52-exercise]')].reduce((acc, x) => acc + Number(x.dataset.best || 0), 0);
      el.textContent = String(sum);
      if (gsap && !reduce) gsap.fromTo(el, { scale: 1.4 }, { scale: 1, duration: 0.5 });
    });

    const hash = /#krok-(\d+)/.exec(location.hash);
    let start = hash ? +hash[1] : 0;
    if (!hash) { const firstOpen = slides.findIndex((s, i) => i > 0 && s.dataset.step && s.dataset.done !== '1'); const anyDone = slides.some((s) => s.dataset.done === '1'); if (anyDone && firstOpen > 0) start = firstOpen; }
    go(start, false);
    window.addEventListener('hashchange', () => { const m = /#krok-(\d+)/.exec(location.hash); if (m) go(+m[1]); });
  }

  // ---------------------------------------------------------------------
  // Kalendář: měsíce
  // ---------------------------------------------------------------------
  document.querySelectorAll('[data-t52-tabs]').forEach((tabs) => {
    const btns = [...tabs.querySelectorAll('[data-t52-tab]')];
    const show = (id, anim) => {
      btns.forEach((b) => b.setAttribute('aria-selected', b.dataset.t52Tab === id ? 'true' : 'false'));
      document.querySelectorAll('[data-t52-panel]').forEach((p) => {
        p.hidden = p.dataset.t52Panel !== id;
        if (!p.hidden && anim) reveal(p.children, { opacity: 0, y: 10, stagger: 0.04, duration: 0.3 });
      });
    };
    btns.forEach((b) => b.addEventListener('click', () => show(b.dataset.t52Tab, true)));
    tabs.querySelector('[aria-selected="true"]')?.scrollIntoView({ block: 'nearest', inline: 'center' });
  });

  // Kalendář: klik na den zvýrazní detail v agendě
  document.querySelectorAll('a.cal54-cell').forEach((cell) => {
    cell.addEventListener('click', (ev) => {
      const id = (cell.getAttribute('href') || '').slice(1);
      const row = id ? document.getElementById(id) : null;
      if (!row) return;
      ev.preventDefault();
      document.querySelectorAll('.t52-day.cal54-focus').forEach((r) => r.classList.remove('cal54-focus'));
      row.classList.add('cal54-focus');
      row.scrollIntoView({ behavior: 'smooth', block: 'center' });
      setTimeout(() => row.classList.remove('cal54-focus'), 2600);
    });
  });

  // Filtr témat a programů
  document.querySelectorAll('[data-t52-filter]').forEach((input) => {
    const page = input.closest('.t52');
    input.addEventListener('input', () => {
      const uiLocale = (window.EduI18n && window.EduI18n.locale) || 'cs';
      const localeTag = uiLocale === 'cs' ? 'cs-CZ' : (uiLocale === 'uk' ? 'uk-UA' : 'en-GB');
      const q = input.value.toLocaleLowerCase(localeTag).trim(); let any = false;
      page.querySelectorAll('[data-t52-item]').forEach((el) => { const hit = !q || el.textContent.toLocaleLowerCase(localeTag).includes(q); el.hidden = !hit; if (hit) any = true; });
      page.querySelectorAll('.t52-topic-group').forEach((g) => { g.hidden = !g.querySelector('[data-t52-item]:not([hidden])'); });
      const empty = page.querySelector('[data-t52-empty]'); if (empty) empty.hidden = any;
    });
  });

  // Kopírování příkazů
  document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-t52-copy]'); if (!b) return;
    try { await navigator.clipboard.writeText(b.dataset.t52Copy); const t = b.textContent; b.textContent = EduI18n.tr('Zkopírováno'); setTimeout(() => { b.textContent = t; }, 1200); } catch (_) {}
  });

  // Trénink klávesových zkratek (výběr ze 4 možností)
  document.querySelectorAll('[data-t52-trainer]').forEach((root) => {
    let pool; try { pool = JSON.parse(root.dataset.t52Trainer); } catch (_) { return; }
    const body = root.querySelector('[data-t52-trainer-body]');
    const run = () => {
      const qs = shuffle(pool).slice(0, 5); let i = 0; let score = 0;
      const ask = () => {
        if (i >= qs.length) {
          const tAgain = esc(EduI18n.tr('Znovu'));
          const tCorrectWord = esc(EduI18n.tr('správně'));
          body.innerHTML = `<p class="t52-trainer-result"><strong>${score} / ${qs.length}</strong> ${tCorrectWord}</p><button type="button" class="btn primary" data-again>${tAgain}</button>`;
          body.querySelector('[data-again]').addEventListener('click', run); return;
        }
        const q = qs[i];
        const opts = shuffle([q.keys, ...shuffle(pool.filter((p) => p.keys !== q.keys)).map((p) => p.keys).filter((k, n, a) => a.indexOf(k) === n).slice(0, 3)]);
        body.innerHTML = `<p class="t52-trainer-q"><span>${esc(q.tool)} · ${i + 1}/${qs.length}</span><strong>${esc(q.desc)}</strong></p><div class="t52-trainer-opts">${opts.map((o) => `<button type="button" data-k="${esc(o)}">${o.split(' / ').map((c) => c.split('+').map((p) => `<kbd>${esc(p)}</kbd>`).join('+')).join(' / ')}</button>`).join('')}</div>`;
        reveal(body.children, { opacity: 0, y: 8, stagger: 0.06, duration: 0.3 });
        body.querySelectorAll('[data-k]').forEach((b) => b.addEventListener('click', () => {
          const ok = b.dataset.k === q.keys; if (ok) score++;
          b.classList.add(ok ? 'good' : 'bad');
          body.querySelector(`[data-k="${CSS.escape(q.keys)}"]`)?.classList.add('good');
          body.querySelectorAll('[data-k]').forEach((x) => { x.disabled = true; });
          setTimeout(() => { i++; ask(); }, 900);
        }));
      };
      ask();
    };
    root.querySelector('[data-t52-trainer-start]')?.addEventListener('click', run);
  });

  // Jemné odhalení obsahu a počítadla
  if (gsap && !reduce) {
    const io = new IntersectionObserver((entries) => {
      const vis = entries.filter((en) => en.isIntersecting).map((en) => en.target);
      vis.forEach((el) => io.unobserve(el));
      if (vis.length) reveal(vis, { opacity: 0, y: 14, stagger: 0.04, duration: 0.4, ease: 'power2.out' });
    }, { rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('[data-t52-reveal]').forEach((el) => io.observe(el));
    document.querySelectorAll('[data-t52-count]').forEach((el) => {
      const target = +el.dataset.t52Count; const o = { v: 0 }; const strong = el.querySelector('strong');
      gsap.to(o, { v: target, duration: 1.1, ease: 'power2.out', onUpdate: () => { el.style.setProperty('--p', o.v); if (strong) strong.textContent = Math.round(o.v) + ' %'; } });
    });
  }
})();
