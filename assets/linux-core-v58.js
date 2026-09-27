/* EDUCANET v58 · Linux Lab offline – JS jádro pískoviště (podmnožina PHP simulátoru linux_v57_*).
 * Běží čistě v prohlížeči (nebo v Node.js pro testy), nic se nespouští doopravdy a nic se nikam
 * neposílá – VFS je obyčejný objekt v paměti, volitelně uložený do localStorage na tomto zařízení.
 * Zjednodušení oproti online labu: jediný uživatel „student“ (žádný root/sudo), bez seedovaného
 * generování úrovní, bez $(...) / $((...)) / historie „!!“ / řídicích struktur skriptu – jen shell
 * základy popsané v zadání (roury, přesměrování, &&/||/;, uvozovky, $VAR, ~, glob * ?).
 * DOM se odsud nikdy nedotýkáme (o to se stará assets/lab-offline-v58.js) – žádné innerHTML, žádný eval. */
(function (root) {
  'use strict';

  var STORAGE_KEY = 'educanet_lab_offline_v58';
  var SCHEMA_VERSION = 1;
  var HOME = '/home/student';
  var MAX_LINE = 1000;
  var MAX_OUTPUT = 200000;
  var MAX_HISTORY = 500;

  function nowSec() { return Math.floor(Date.now() / 1000); }

  // Cesty
  function normalize(path, cwd) {
    if (!path) return cwd;
    var abs = path.charAt(0) === '/' ? path : (cwd === '/' ? '' : cwd) + '/' + path;
    var parts = [];
    abs.split('/').forEach(function (part) {
      if (part === '' || part === '.') return;
      if (part === '..') { parts.pop(); return; }
      parts.push(part);
    });
    return '/' + parts.join('/');
  }
  function dirnameOf(abs) {
    if (abs === '/' || abs.indexOf('/') === -1) return '/';
    var dir = abs.slice(0, abs.lastIndexOf('/'));
    return dir === '' ? '/' : dir;
  }
  function basenameOf(abs) {
    if (abs === '/') return '/';
    var pos = abs.lastIndexOf('/');
    return pos === -1 ? abs : abs.slice(pos + 1);
  }
  function nameCompare(a, b) {
    var ka = a.replace(/^\.+/, '').toLowerCase();
    var kb = b.replace(/^\.+/, '').toLowerCase();
    if (ka < kb) return -1;
    if (ka > kb) return 1;
    return a < b ? -1 : (a > b ? 1 : 0);
  }
  function escapeRegExp(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  // VFS: fs = { "/abs/path": {type:'f'|'d', mode:number, mtime:seconds, content?:string} }
  function getNode(fs, abs) { return fs[abs] || null; }
  function isDir(fs, abs) { var n = fs[abs]; return !!n && n.type === 'd'; }
  function isFile(fs, abs) { var n = fs[abs]; return !!n && n.type === 'f'; }
  function childrenOf(fs, dir) {
    var prefix = dir === '/' ? '/' : dir + '/';
    var names = [];
    Object.keys(fs).forEach(function (p) {
      if (p === '/' || p.indexOf(prefix) !== 0) return;
      var rest = p.slice(prefix.length);
      if (rest.indexOf('/') !== -1) return;
      names.push(rest);
    });
    names.sort(nameCompare);
    return names;
  }
  function treePaths(fs, abs) {
    var out = [abs];
    if (isDir(fs, abs)) {
      var prefix = abs === '/' ? '/' : abs + '/';
      Object.keys(fs).forEach(function (p) { if (p !== abs && p.indexOf(prefix) === 0) out.push(p); });
    }
    out.sort();
    return out;
  }
  function deleteTree(fs, abs) { treePaths(fs, abs).slice().reverse().forEach(function (p) { delete fs[p]; }); }
  function fileSize(node) { return node.type === 'd' ? 4096 : (node.content || '').length; }

  // Práva: pískoviště má jen jednoho uživatele (student), takže se kontrolují vždy jen bity
  // vlastníka (horní trojice v mode) – zbylé bity slouží čistě k výuce čtení „ls -l“/„stat“.
  function canRead(node) { return (node.mode & 0o400) !== 0; }
  function canWrite(node) { return (node.mode & 0o200) !== 0; }
  function canExecute(node) { return (node.mode & 0o100) !== 0; }
  function canTraverse(fs, abs) {
    var dir = dirnameOf(abs);
    if (dir === '/') return true;
    var parts = dir.split('/').filter(Boolean);
    var path = '';
    for (var i = 0; i < parts.length; i++) {
      path += '/' + parts[i];
      var node = fs[path];
      if (!node) return true;
      if (!canExecute(node)) return false;
    }
    return true;
  }
  function canModifyDir(fs, dirAbs) {
    var d = fs[dirAbs];
    return !!d && d.type === 'd' && canWrite(d) && canExecute(d);
  }

  function mkdirp(session, path, mode) {
    var abs = normalize(path, session.cwd);
    if (abs === '/') return abs;
    var parts = abs.split('/').filter(Boolean);
    var cur = '';
    parts.forEach(function (part) {
      cur += '/' + part;
      if (!session.fs[cur]) session.fs[cur] = { type: 'd', mode: cur === abs ? mode : 0o755, mtime: nowSec() };
    });
    return abs;
  }

  function readFile(session, path, errOut) {
    var abs = normalize(path, session.cwd);
    if (!canTraverse(session.fs, abs)) { errOut.msg = 'Permission denied'; return null; }
    var node = session.fs[abs];
    if (!node) { errOut.msg = 'No such file or directory'; return null; }
    if (node.type === 'd') { errOut.msg = 'Is a directory'; return null; }
    if (!canRead(node)) { errOut.msg = 'Permission denied'; return null; }
    return node.content || '';
  }
  function writeFileAbs(session, abs, content, append, errOut) {
    var node = session.fs[abs];
    var existingLen = append && node ? (node.content || '').length : 0;
    if (existingLen + content.length > 1048576) { errOut.msg = 'No space left on device'; addTip(session, 'Soubor by byl větší než 1 MB – na cvičném disku pro něj není místo.'); return false; }
    if (!canTraverse(session.fs, abs)) { errOut.msg = 'Permission denied'; return false; }
    if (node) {
      if (node.type === 'd') { errOut.msg = 'Is a directory'; return false; }
      if (!canWrite(node)) { errOut.msg = 'Permission denied'; return false; }
      node.content = append ? (node.content || '') + content : content;
      node.mtime = nowSec();
      return true;
    }
    var parent = session.fs[dirnameOf(abs)];
    if (!parent) { errOut.msg = 'No such file or directory'; return false; }
    if (parent.type !== 'd') { errOut.msg = 'Not a directory'; return false; }
    if (!canWrite(parent) || !canExecute(parent)) { errOut.msg = 'Permission denied'; return false; }
    session.fs[abs] = { type: 'f', mode: 0o644, mtime: nowSec(), content: content };
    return true;
  }
  function writeFile(session, path, content, append, errOut) {
    return writeFileAbs(session, normalize(path, session.cwd), content, append, errOut);
  }

  function modeString(node) {
    var m = node.mode, s = node.type === 'd' ? 'd' : '-';
    [6, 3, 0].forEach(function (shift) {
      var v = (m >> shift) & 7;
      s += (v & 4 ? 'r' : '-') + (v & 2 ? 'w' : '-') + (v & 1 ? 'x' : '-');
    });
    return s;
  }
  function octal(mode) { var s = (mode & 0o7777).toString(8); return '0000'.slice(s.length) + s; }
  function humanSize(bytes) {
    if (bytes < 1024) return String(bytes);
    var units = ['K', 'M', 'G', 'T'], value = bytes;
    for (var i = 0; i < units.length; i++) {
      value /= 1024;
      if (value < 1024 || units[i] === 'T') return (value < 10 ? Math.ceil(value * 10) / 10 : Math.ceil(value)) + units[i];
    }
    return String(bytes);
  }
  function chmodApply(mode, spec, isDirNode) {
    if (/^[0-7]{1,4}$/.test(spec)) return parseInt(spec, 8);
    var clauses = spec.split(','), masks = { u: 0o4700, g: 0o2070, o: 0o1007, a: 0o7777 };
    for (var i = 0; i < clauses.length; i++) {
      var m = /^([ugoa]*)([-+=])([rwxXst]*)$/.exec(clauses[i]);
      if (!m) return null;
      var who = m[1] || 'a', mask = 0;
      for (var w = 0; w < who.length; w++) mask |= masks[who.charAt(w)] || 0;
      var val = 0;
      if (m[3].indexOf('r') !== -1) val |= 0o444;
      if (m[3].indexOf('w') !== -1) val |= 0o222;
      if (m[3].indexOf('x') !== -1 || (m[3].indexOf('X') !== -1 && (isDirNode || (mode & 0o111)))) val |= 0o111;
      val &= mask;
      mode = m[2] === '+' ? (mode | val) : (m[2] === '-' ? (mode & ~val) : ((mode & ~(mask & 0o777)) | val));
    }
    return mode & 0o7777;
  }

  // Výchozí pískoviště (podobné lab57_world_sandbox_home, ale nezávislé, bez semínka)
  function buildDefaultFs() {
    var now = nowSec(), old = now - 86400 * 2, fs = {};
    function dir(path, mtime) { fs[path] = { type: 'd', mode: 0o755, mtime: mtime || old }; }
    function file(path, content, mode, mtime) { fs[path] = { type: 'f', mode: mode || 0o644, mtime: mtime || old, content: content }; }
    ['/', '/home', HOME, HOME + '/projekty', HOME + '/projekty/web', HOME + '/projekty/skripty', HOME + '/data', HOME + '/obrazky', '/tmp', '/usr', '/usr/bin'].forEach(function (d) { dir(d); });
    file(HOME + '/vitej.txt', 'Ahoj! Tohle je offline trénink Linux Labu.\nVšechno je simulace v tvém prohlížeči – nic se neposílá na server.\n\nZkus třeba:\n  ls -la        vypíše obsah složky i se skrytými soubory\n  cd data       přejde do složky data\n  cat vitej.txt vypíše tento soubor\n  man ls        otevře nápovědu k příkazu ls\n\nPostup se ukládá jen v tomto prohlížeči (localStorage). Tlačítko „Začít znovu“ vše smaže.\n');
    file(HOME + '/poznamky.txt', 'Linux je operační systém.\nV terminálu píšu příkazy.\nTODO: naučit se příkaz grep\nSíť spojuje počítače.\nTODO: zjistit svoji IP adresu\nSložky oddělujeme lomítkem /.\nTODO: vyzkoušet rouru |\nKonec poznámek.\n');
    file(HOME + '/.skryty_tip.txt', 'Skryté soubory začínají tečkou. Uvidíš je příkazem ls -a.\n');
    file(HOME + '/projekty/web/index.html', '<!doctype html>\n<html lang="cs">\n<head>\n  <meta charset="utf-8">\n  <title>Můj web</title>\n  <link rel="stylesheet" href="styl.css">\n</head>\n<body>\n  <h1>Můj první web</h1>\n</body>\n</html>\n');
    file(HOME + '/projekty/web/styl.css', 'body {\n  font-family: sans-serif;\n  color: #1d2733;\n}\nh1 {\n  color: #007a87;\n}\n');
    file(HOME + '/projekty/skripty/zaloha.sh', '#!/bin/bash\n# Zkopíruje web do zálohy (v pískovišti se skripty jen čtou, nespouští se)\nmkdir -p /tmp/zaloha\ncp -r ~/projekty/web /tmp/zaloha/\necho "Záloha hotová: /tmp/zaloha/web"\n', 0o644);
    file(HOME + '/projekty/skripty/pozdrav.sh', '#!/bin/bash\necho "Ahoj, $(whoami)!"\n', 0o755);
    file(HOME + '/data/zaci.csv', 'id,jmeno,trida,body\n1,Adam,3.A,58\n2,Bára,3.A,71\n3,Cyril,4.A,64\n4,Dana,4.A,82\n5,Ema,3.A,45\n');
    file(HOME + '/data/cisla.txt', '42\n7\n108\n15\n7\n23\n99\n3\n');
    file(HOME + '/data/slova.txt', 'server\nsíť\npaket\nrouter\nserver\nkabel\nswitch\nlinux\nsíť\nterminál\n');
    file(HOME + '/data/access.log', '10.0.0.31 - - [01/Sep/2026:08:12:01 +0200] "GET /index.html HTTP/1.1" 200 5120\n10.0.0.44 - - [01/Sep/2026:08:12:05 +0200] "GET /styl.css HTTP/1.1" 200 1874\n10.0.0.31 - - [01/Sep/2026:08:13:40 +0200] "GET /stara-stranka.html HTTP/1.1" 404 162\n');
    file(HOME + '/data/zprava.b64', 'R3JhdHVsdWp1LCBkZWvDs2R1amXFoSBqc2kgenByw6F2dSB2IGJhc2U2NCE=\n');
    file(HOME + '/obrazky/logo.png', '\x89PNG\r\n\x1a\n[binární data ukázkového obrázku – jen pro cvičení příkazu file]');
    return fs;
  }

  // Relace (session)
  function defaultAliases() { return { ll: 'ls -alF', la: 'ls -A', l: 'ls -CF' }; }
  function protectedEnvNames() { return ['HOME', 'USER', 'PWD', 'OLDPWD', 'SHELL', 'PATH', 'HOSTNAME', 'LOGNAME', 'TERM', 'LANG', 'EDITOR']; }

  function createSessionBase() {
    return {
      fs: {}, cwd: HOME, oldCwd: HOME, home: HOME, user: 'student', hostname: 'lab-pc',
      env: {}, aliases: defaultAliases(), history: [], lastExit: 0, tips: [], pendingClear: false
    };
  }
  function createSession() {
    var s = createSessionBase();
    s.fs = buildDefaultFs();
    return s;
  }
  function envAll(session) {
    return Object.assign({
      HOME: session.home, USER: session.user, LOGNAME: session.user, SHELL: '/bin/bash',
      PATH: '/usr/local/bin:/usr/bin:/bin', PWD: session.cwd, OLDPWD: session.oldCwd,
      HOSTNAME: session.hostname, TERM: 'xterm-256color', LANG: 'C.UTF-8', EDITOR: 'nano'
    }, session.env);
  }
  function resolveVar(session, name) {
    if (/^[0-9]+$/.test(name)) return name === '0' ? 'bash' : '';
    switch (name) {
      case '?': return String(session.lastExit);
      case '$': return '1487';
      case '#': return '0';
      case '@': case '*': return '';
      case 'RANDOM': return String(Math.floor(Math.random() * 32768));
      default: return envAll(session)[name] || '';
    }
  }
  function addTip(session, text) {
    if (session.tips.indexOf(text) === -1 && session.tips.length < 3) session.tips.push(text);
  }

  // Perzistence (jen na tomto zařízení; nikdy se nikam neposílá)
  function serialize(session) {
    return {
      v: SCHEMA_VERSION, cwd: session.cwd, oldCwd: session.oldCwd, env: session.env,
      aliases: session.aliases, history: session.history.slice(-MAX_HISTORY),
      hostname: session.hostname, lastExit: session.lastExit, fs: session.fs
    };
  }
  function restore(data) {
    if (!data || data.v !== SCHEMA_VERSION || !data.fs || typeof data.fs !== 'object') return null;
    var session = createSessionBase();
    if (typeof data.cwd === 'string') session.cwd = data.cwd;
    if (typeof data.oldCwd === 'string') session.oldCwd = data.oldCwd;
    if (data.env && typeof data.env === 'object') session.env = data.env;
    if (data.aliases && typeof data.aliases === 'object') session.aliases = data.aliases;
    if (Array.isArray(data.history)) session.history = data.history.filter(function (h) { return typeof h === 'string'; });
    if (typeof data.hostname === 'string') session.hostname = data.hostname;
    if (typeof data.lastExit === 'number') session.lastExit = data.lastExit;
    session.fs = data.fs;
    if (!session.fs[session.cwd]) session.cwd = session.home;
    if (!session.fs['/']) return null;
    return session;
  }
  function saveSession(session) {
    try { root.localStorage.setItem(STORAGE_KEY, JSON.stringify(serialize(session))); return true; } catch (e) { return false; }
  }
  function loadSession() {
    try {
      var raw = root.localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      return restore(JSON.parse(raw));
    } catch (e) { return null; }
  }
  function clearSaved() { try { root.localStorage.removeItem(STORAGE_KEY); } catch (e) { /* offline pískoviště bez localStorage prostě nepřežije reload */ } }
  function resetSession(session) {
    clearSaved();
    var fresh = createSession();
    Object.keys(session).forEach(function (k) { delete session[k]; });
    Object.keys(fresh).forEach(function (k) { session[k] = fresh[k]; });
    return session;
  }

  // Lexer
  function SyntaxErr(message) { this.message = message; }
  SyntaxErr.prototype = Object.create(Error.prototype);

  function lexDollar(src, i) {
    var n = src.length, next = src.charAt(i + 1);
    if (next === '(') throw new SyntaxErr('$(…) a $((…)) offline pískoviště nepodporuje – použij jen $PROMĚNNOU.');
    if (next === '{') {
      var end = src.indexOf('}', i + 2);
      if (end === -1) throw new SyntaxErr("unexpected EOF while looking for matching `}'");
      var name = src.slice(i + 2, end);
      if (!/^([A-Za-z_][A-Za-z0-9_]*|[0-9]+|[?$#@*])$/.test(name)) throw new SyntaxErr('${' + name + '}: bad substitution');
      return { k: 'v', v: name, len: end - i + 1 };
    }
    if (/[A-Za-z_]/.test(next)) {
      var m = /^[A-Za-z_][A-Za-z0-9_]*/.exec(src.slice(i + 1));
      return { k: 'v', v: m[0], len: m[0].length + 1 };
    }
    if ('?$#@*0123456789'.indexOf(next) !== -1 && next !== '') return { k: 'v', v: next, len: 2 };
    return null;
  }

  function lex(src) {
    var tokens = [], parts = [], inWord = false, i = 0, n = src.length;
    function flush() { if (inWord) tokens.push({ t: 'W', parts: parts }); parts = []; inWord = false; }
    function lit(text, quoted) {
      inWord = true;
      var last = parts[parts.length - 1];
      if (last && last.k === 'l' && last.q === quoted) { last.v += text; return; }
      parts.push({ k: 'l', v: text, q: quoted });
    }
    while (i < n) {
      var c = src.charAt(i);
      if (c === ' ' || c === '\t') { flush(); i++; continue; }
      if (c === '#' && !inWord) break;
      if (!inWord && c === '2' && src.charAt(i + 1) === '>') {
        if (src.charAt(i + 2) === '&' && src.charAt(i + 3) === '1') { tokens.push({ t: 'R', v: '2>&1' }); i += 4; }
        else if (src.charAt(i + 2) === '>') { tokens.push({ t: 'R', v: '2>>' }); i += 3; }
        else { tokens.push({ t: 'R', v: '2>' }); i += 2; }
        continue;
      }
      if ('|&;<>'.indexOf(c) !== -1) {
        flush();
        var two = src.substr(i, 2);
        if (two === '&&') { tokens.push({ t: 'OP', v: '&&' }); i += 2; }
        else if (two === '||') { tokens.push({ t: 'OP', v: '||' }); i += 2; }
        else if (two === '>>') { tokens.push({ t: 'R', v: '>>' }); i += 2; }
        else if (c === '>') { tokens.push({ t: 'R', v: '>' }); i++; }
        else if (c === '<') { tokens.push({ t: 'R', v: '<' }); i++; }
        else if (c === '|') { tokens.push({ t: 'OP', v: '|' }); i++; }
        else if (c === '&') { tokens.push({ t: 'OP', v: '&' }); i++; }
        else { tokens.push({ t: 'OP', v: ';' }); i++; }
        continue;
      }
      if (c === "'") {
        var end1 = src.indexOf("'", i + 1);
        if (end1 === -1) throw new SyntaxErr("unexpected EOF while looking for matching `''");
        lit(src.slice(i + 1, end1), true);
        i = end1 + 1;
        continue;
      }
      if (c === '"') {
        i++; inWord = true;
        var buf = '', closed = false;
        while (i < n) {
          var d = src.charAt(i);
          if (d === '"') { closed = true; i++; break; }
          if (d === '\\' && i + 1 < n && '"\\$`'.indexOf(src.charAt(i + 1)) !== -1) { buf += src.charAt(i + 1); i += 2; continue; }
          if (d === '`') throw new SyntaxErr('náhradu příkazů (`…`) offline pískoviště nepodporuje.');
          if (d === '$') {
            var got = lexDollar(src, i);
            if (got) { if (buf !== '') { lit(buf, true); buf = ''; } parts.push({ k: got.k, v: got.v, q: true }); i += got.len; continue; }
          }
          buf += d; i++;
        }
        if (!closed) throw new SyntaxErr('unexpected EOF while looking for matching `"\'');
        lit(buf, true);
        continue;
      }
      if (c === '\\') { if (i + 1 < n) { lit(src.charAt(i + 1), true); i += 2; } else i++; continue; }
      if (c === '`') throw new SyntaxErr('náhradu příkazů (`…`) offline pískoviště nepodporuje.');
      if (c === '$') {
        var got2 = lexDollar(src, i);
        if (got2) { inWord = true; parts.push({ k: got2.k, v: got2.v, q: false }); i += got2.len; continue; }
      }
      if (c === '~' && !inWord) {
        var j = i + 1;
        while (j < n && /[A-Za-z0-9_.-]/.test(src.charAt(j))) j++;
        var nextCh = j < n ? src.charAt(j) : '';
        if (j === i + 1 && (nextCh === '' || nextCh === '/' || ' \t;|&<>'.indexOf(nextCh) !== -1)) {
          inWord = true; parts.push({ k: 't', v: '', q: true }); i = j; continue;
        }
      }
      lit(c, false); i++;
    }
    flush();
    return tokens;
  }

  // Parser
  function parseCommand(tokens, cur) {
    var cmd = { words: [], redirs: [] }, n = tokens.length;
    while (cur.i < n) {
      var tok = tokens[cur.i];
      if (tok.t === 'OP') break;
      if (tok.t === 'R') {
        cur.i++;
        if (tok.v === '2>&1') { cmd.redirs.push([tok.v, null]); continue; }
        var target = tokens[cur.i];
        if (!target || target.t !== 'W') throw new SyntaxErr("syntax error near unexpected token `" + (target ? target.v : 'newline') + "'");
        cmd.redirs.push([tok.v, target.parts]);
        cur.i++;
        continue;
      }
      cmd.words.push(tok.parts);
      cur.i++;
    }
    if (!cmd.words.length && !cmd.redirs.length) {
      var near = cur.i < n ? tokens[cur.i].v : 'newline';
      throw new SyntaxErr("syntax error near unexpected token `" + near + "'");
    }
    return cmd;
  }
  function parsePipeline(tokens, cur) {
    var cmds = [];
    for (;;) {
      cmds.push(parseCommand(tokens, cur));
      var t = tokens[cur.i];
      if (t && t.t === 'OP' && t.v === '|') { cur.i++; if (cur.i >= tokens.length) throw new SyntaxErr('syntax error: unexpected end of file'); continue; }
      break;
    }
    return { cmds: cmds };
  }
  function parseLine(tokens) {
    var cur = { i: 0 }, n = tokens.length, items = [];
    while (cur.i < n) {
      var tok = tokens[cur.i];
      if (tok.t === 'OP' && tok.v === ';') { cur.i++; continue; }
      if (tok.t === 'OP') throw new SyntaxErr("syntax error near unexpected token `" + tok.v + "'");
      var andor = [], op = null;
      for (;;) {
        andor.push({ op: op, pipe: parsePipeline(tokens, cur) });
        var nextOp = tokens[cur.i];
        if (nextOp && nextOp.t === 'OP' && (nextOp.v === '&&' || nextOp.v === '||')) {
          op = nextOp.v; cur.i++;
          if (cur.i >= n) throw new SyntaxErr('syntax error: unexpected end of file');
          continue;
        }
        break;
      }
      var bg = false, t2 = tokens[cur.i];
      if (t2 && t2.t === 'OP') {
        if (t2.v === '&') { bg = true; cur.i++; }
        else if (t2.v === ';') { cur.i++; }
        else throw new SyntaxErr("syntax error near unexpected token `" + t2.v + "'");
      }
      items.push({ andor: andor, bg: bg });
    }
    return items;
  }

  // Expanze slov a globy
  function escapeGlobChars(s) { return s.replace(/([*?[])/g, '\\$1'); }
  function hasGlobChars(s) { return /[*?[]/.test(s); }
  function componentRegex(comp) {
    var out = '';
    for (var i = 0; i < comp.length; i++) {
      var ch = comp.charAt(i);
      if (ch === '\\' && i + 1 < comp.length) { out += escapeRegExp(comp.charAt(++i)); continue; }
      if (ch === '*') { out += '.*'; continue; }
      if (ch === '?') { out += '.'; continue; }
      if (ch === '[') {
        var end = comp.indexOf(']', i + 1);
        if (end !== -1) { var set = comp.slice(i + 1, end); if (set.charAt(0) === '!' || set.charAt(0) === '^') set = '^' + set.slice(1); out += '[' + set + ']'; i = end; continue; }
      }
      out += escapeRegExp(ch);
    }
    return new RegExp('^' + out + '$');
  }
  function globPaths(session, pattern) {
    var absolute = pattern.charAt(0) === '/';
    var comps = pattern.split('/').filter(function (c) { return c !== ''; });
    if (!comps.length) return [];
    var results = [[absolute ? '/' : session.cwd, absolute ? '/' : '']];
    comps.forEach(function (comp, idx) {
      var last = idx === comps.length - 1;
      var isGlob = hasGlobChars(comp);
      var next = [];
      results.forEach(function (pair) {
        var dirAbs = pair[0], disp = pair[1];
        var sep = (disp === '' || /\/$/.test(disp)) ? '' : '/';
        if (!isGlob) {
          var literal = comp.replace(/\\(.)/g, '$1');
          var abs = normalize(literal, dirAbs);
          if (!session.fs[abs]) return;
          if (!last && !isDir(session.fs, abs)) return;
          next.push([abs, disp + sep + literal]);
          return;
        }
        if (!isDir(session.fs, dirAbs)) return;
        var re = componentRegex(comp);
        childrenOf(session.fs, dirAbs).forEach(function (name) {
          if (name.charAt(0) === '.' && comp.charAt(0) !== '.') return;
          if (!re.test(name)) return;
          var abs2 = (dirAbs === '/' ? '' : dirAbs) + '/' + name;
          if (!last && !isDir(session.fs, abs2)) return;
          next.push([abs2, disp + sep + name]);
        });
      });
      results = next;
    });
    var out = results.map(function (r) { return r[1]; });
    out.sort(nameCompare);
    return out;
  }
  function expandWord(session, parts, doGlob) {
    var fields = [[]], keep = [false], cur = 0;
    parts.forEach(function (part) {
      if (part.k === 'l') { fields[cur].push([part.v, part.q]); if (part.q) keep[cur] = true; return; }
      if (part.k === 't') { fields[cur].push([session.home, true]); keep[cur] = true; return; }
      var text = resolveVar(session, part.v);
      if (part.q) { fields[cur].push([text, true]); keep[cur] = true; return; }
      var pieces = text.split(/[ \t]+/).filter(function (s) { return s !== ''; });
      if (!pieces.length) return;
      var startsWs = /^[ \t]/.test(text);
      pieces.forEach(function (piece, k) {
        if (k > 0 || (startsWs && fields[cur].length)) { fields.push([]); keep.push(false); cur++; }
        fields[cur].push([piece, false]);
      });
      if (/[ \t]$/.test(text)) { fields.push([]); keep.push(false); cur++; }
    });
    var out = [];
    fields.forEach(function (segs, idx) {
      if (!segs.length && !keep[idx]) return;
      var plain = '', pattern = '', hasGlob2 = false;
      segs.forEach(function (seg) {
        plain += seg[0];
        if (seg[1]) pattern += escapeGlobChars(seg[0]);
        else { pattern += seg[0]; if (hasGlobChars(seg[0])) hasGlob2 = true; }
      });
      if (doGlob && hasGlob2) {
        var matches = globPaths(session, pattern);
        if (matches.length) { matches.forEach(function (m) { out.push(m); }); return; }
      }
      out.push(plain);
    });
    return out;
  }

  // Příkazy: registr, nápověda, rady po chybě
  var commands = {};
  var manualData = null;

  function registerCommand(name, fn, meta) {
    commands[name] = { fn: fn, help: !(meta && meta.help === false), builtin: !!(meta && meta.builtin) };
  }
  function isBuiltin(name) { return !!(commands[name] && commands[name].builtin); }
  function knownCommandNames() { return Object.keys(commands); }
  function setManual(data) { manualData = data; }
  function getManual() { return manualData || { categories: {}, concepts: {}, commands: {} }; }

  function levenshtein(a, b) {
    var m = a.length, n = b.length, d = [];
    for (var i = 0; i <= m; i++) d.push([i]);
    for (var j = 0; j <= n; j++) d[0][j] = j;
    for (i = 1; i <= m; i++) for (j = 1; j <= n; j++) d[i][j] = Math.min(d[i - 1][j] + 1, d[i][j - 1] + 1, d[i - 1][j - 1] + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1));
    return d[m][n];
  }
  function suggest(word, known) {
    var best = null, bestScore = 3;
    known.forEach(function (cand) {
      if (cand === word || Math.abs(cand.length - word.length) > 2) return;
      var score = levenshtein(word, cand);
      if (score < bestScore) { best = cand; bestScore = score; }
    });
    return best;
  }
  function notFoundTip(session, name) {
    var windowsCmds = { dir: 'ls', cls: 'clear', copy: 'cp', del: 'rm', move: 'mv', ren: 'mv', md: 'mkdir', findstr: 'grep' };
    if (windowsCmds[name.toLowerCase()]) { addTip(session, '„' + name + '“ je příkaz z Windows. V Linuxu použij: ' + windowsCmds[name.toLowerCase()]); return; }
    var s = suggest(name, knownCommandNames());
    addTip(session, s ? 'Neznámý příkaz. Nemyslel(a) jsi „' + s + '“?' : 'Tenhle příkaz pískoviště nezná. Seznam vypíše help.');
  }
  function errorTip(session, message, path) {
    var tip = {
      'No such file or directory': 'Cesta „' + path + '“ neexistuje. Zkontroluj překlep a podívej se příkazem ls.',
      'Permission denied': 'Na „' + path + '“ nemáš oprávnění – zkontroluj práva pomocí ls -l.',
      'Is a directory': '„' + path + '“ je složka, ne soubor. Obsah vypíšeš ls, vstoupíš cd.',
      'Not a directory': 'Část cesty „' + path + '“ je soubor, ne složka.'
    }[message];
    if (tip) addTip(session, tip);
  }
  function manualHelp(proc, name) {
    var cmd = getManual().commands[name];
    if (!cmd) return false;
    proc.line('Usage: ' + cmd.synopsis);
    proc.line(cmd.summary);
    if (cmd.options && cmd.options.length) {
      proc.line('');
      cmd.options.forEach(function (o) { proc.line('  ' + (o[0] + '              ').slice(0, 14) + ' ' + o[1]); });
    }
    proc.line('');
    proc.line('Celá nápověda: man ' + name);
    return true;
  }

  // Provádění
  function makeProc(session, argv, stdin, hasStdin, tty) {
    return {
      session: session, argv: argv, stdin: stdin || '', hasStdin: !!hasStdin, tty: !!tty, name: argv[0] || '', chunks: [],
      out: function (text) { if (text) this.chunks.push([1, text]); },
      line: function (text) { this.chunks.push([1, (text || '') + '\n']); },
      err: function (text) { if (text) this.chunks.push([2, text]); },
      fail: function (message, code) { this.chunks.push([2, this.name + ': ' + message + '\n']); return code || 1; }
    };
  }
  function dispatch(session, argv, stdin, hasStdin, tty) {
    var name = argv[0] || '';
    if (!name) return { status: 0, chunks: [] };
    var meta = commands[name];
    var proc = makeProc(session, argv, stdin, hasStdin, tty);
    if (!meta) {
      proc.err('bash: ' + name + ': command not found\n');
      notFoundTip(session, name);
      return { status: 127, chunks: proc.chunks };
    }
    if (meta.help && argv.indexOf('--help') !== -1 && manualHelp(proc, name)) return { status: 0, chunks: proc.chunks };
    var status;
    try { status = meta.fn(proc, argv) | 0; }
    catch (e) { proc.err('bash: ' + name + ': interní chyba pískoviště (' + (e && e.message ? e.message : e) + ')\n'); status = 1; }
    return { status: status, chunks: proc.chunks };
  }

  /** Neplatný přesměrovací cíl je jediný neúspěšný výsledek – odlišný od "žádný vstup" (null). */
  var REDIR_FAIL = { fail: true };
  function runRedirs(session, cmd, fd1, fd2, stdinIn, term) {
    var stdin = stdinIn;
    for (var i = 0; i < cmd.redirs.length; i++) {
      var op = cmd.redirs[i][0], wordParts = cmd.redirs[i][1];
      if (op === '2>&1') { fd2.v = fd1.v; continue; }
      var fields = expandWord(session, wordParts, true);
      if (fields.length !== 1) { term.push([2, 'bash: ambiguous redirect\n']); return REDIR_FAIL; }
      var target = fields[0];
      if (op === '<') {
        var errIn = {};
        var content = readFile(session, target, errIn);
        if (content === null) { term.push([2, 'bash: ' + target + ': ' + errIn.msg + '\n']); errorTip(session, errIn.msg, target); return REDIR_FAIL; }
        stdin = content;
        continue;
      }
      var abs = normalize(target, session.cwd);
      var append = op === '>>';
      if (abs !== '/dev/null') {
        var errOut = {};
        if (!writeFileAbs(session, abs, '', append, errOut)) { term.push([2, 'bash: ' + target + ': ' + errOut.msg + '\n']); errorTip(session, errOut.msg, target); return REDIR_FAIL; }
      }
      var dest = abs === '/dev/null' ? 'null' : ('file:' + abs);
      if (op === '>' || op === '>>') fd1.v = dest; else fd2.v = dest;
    }
    return stdin === undefined ? null : stdin;
  }
  function execSimple(session, cmd, input, isLast, term) {
    var argv = [];
    cmd.words.forEach(function (w) { expandWord(session, w, true).forEach(function (f) { argv.push(f); }); });
    if (argv.length && session.aliases[argv[0]]) {
      var expanded = session.aliases[argv[0]].trim().split(/\s+/).filter(Boolean);
      argv = expanded.concat(argv.slice(1));
    }
    var fd1 = { v: isLast ? 'term' : 'pipe' }, fd2 = { v: 'term' };
    var stdin = runRedirs(session, cmd, fd1, fd2, input, term);
    if (stdin === REDIR_FAIL) return { status: 1, out: '' };
    if (!argv.length) return { status: 0, out: '' };
    var d = dispatch(session, argv, stdin, stdin !== null, fd1.v === 'term');
    var captured = '', files = {};
    d.chunks.forEach(function (ch) {
      var dest = ch[0] === 1 ? fd1.v : fd2.v;
      if (dest === 'term') term.push(ch);
      else if (dest === 'pipe') captured += ch[1];
      else if (dest.indexOf('file:') === 0) { var p = dest.slice(5); files[p] = (files[p] || '') + ch[1]; }
    });
    Object.keys(files).forEach(function (p) {
      var errW = {};
      if (!writeFileAbs(session, p, files[p], true, errW)) term.push([2, 'bash: ' + p + ': ' + errW.msg + '\n']);
    });
    return { status: d.status, out: captured };
  }
  function execPipeline(session, pipe, term) {
    var input = null, status = 0;
    for (var i = 0; i < pipe.cmds.length; i++) {
      var r = execSimple(session, pipe.cmds[i], input, i === pipe.cmds.length - 1, term);
      status = r.status; input = r.out;
    }
    session.lastExit = status;
    return status;
  }
  function execAndOr(session, andor, term) {
    var status = 0;
    for (var i = 0; i < andor.length; i++) {
      if (i > 0 && andor[i].op === '&&' && status !== 0) continue;
      if (i > 0 && andor[i].op === '||' && status === 0) continue;
      status = execPipeline(session, andor[i].pipe, term);
    }
    return status;
  }
  function execItems(session, items, term) {
    var status = session.lastExit;
    items.forEach(function (item) {
      if (item.bg) { term.push([2, '[1] ' + (2000 + session.history.length) + '\n']); addTip(session, 'Úlohy na pozadí (&) pískoviště spouští hned v popředí.'); }
      status = execAndOr(session, item.andor, term);
    });
    return status;
  }

  function runLine(session, line) {
    line = String(line).replace(/\r/g, '');
    if (line.length > MAX_LINE) return { chunks: [[2, 'bash: příkaz je příliš dlouhý (nejvýš ' + MAX_LINE + ' znaků)\n']], exit: 1, tips: [], clear: false };
    session.tips = [];
    session.pendingClear = false;
    if (!line.trim()) return { chunks: [], exit: session.lastExit, tips: [], clear: false };
    if (!/^\s/.test(line)) { session.history.push(line); if (session.history.length > MAX_HISTORY) session.history = session.history.slice(-MAX_HISTORY); }
    var term = [], status;
    try {
      status = execItems(session, parseLine(lex(line)), term);
    } catch (e) {
      term.push([2, 'bash: ' + (e && e.message ? e.message : String(e)) + '\n']);
      status = 2;
    }
    session.lastExit = status;
    var size = 0, limited = [];
    for (var i = 0; i < term.length; i++) {
      size += term[i][1].length;
      if (size > MAX_OUTPUT) { limited.push([2, '\n… výstup je příliš dlouhý, pískoviště ho zkrátilo.\n']); break; }
      limited.push(term[i]);
    }
    return { chunks: limited, exit: status, tips: session.tips.slice(), clear: session.pendingClear };
  }

  // Tab doplňování
  function commonPrefix(list) {
    var p = list[0];
    for (var i = 1; i < list.length; i++) {
      var b = list[i], j = 0;
      while (j < p.length && j < b.length && p.charAt(j) === b.charAt(j)) j++;
      p = p.slice(0, j);
    }
    return p;
  }
  function complete(session, text, caret) {
    var before = text.slice(0, caret);
    var m = /([^\s]*)$/.exec(before);
    var prefix = m ? m[1] : '';
    var wordStart = caret - prefix.length;
    var priorTrim = before.slice(0, wordStart).replace(/(&&|\|\|)/g, ' ');
    var firstWord = !/[^\s;&|]/.test(priorTrim) || priorTrim.trim() === '';
    var options = [];
    if (firstWord) {
      options = knownCommandNames().concat(Object.keys(session.aliases)).filter(function (n) { return n.indexOf(prefix) === 0; });
    } else {
      var slashIdx = prefix.lastIndexOf('/');
      var dirPart = slashIdx === -1 ? '' : prefix.slice(0, slashIdx + 1);
      var namePart = slashIdx === -1 ? prefix : prefix.slice(slashIdx + 1);
      var dirAbs = normalize(dirPart || '.', session.cwd);
      if (isDir(session.fs, dirAbs)) {
        childrenOf(session.fs, dirAbs).forEach(function (name) {
          if (name.indexOf(namePart) !== 0) return;
          if (namePart.charAt(0) !== '.' && name.charAt(0) === '.') return;
          var abs = (dirAbs === '/' ? '' : dirAbs) + '/' + name;
          options.push(dirPart + name + (isDir(session.fs, abs) ? '/' : ''));
        });
      }
    }
    options = Array.from(new Set(options)).sort();
    if (!options.length) return { value: text, caret: caret, list: [] };
    var completion = options.length === 1 ? options[0] : commonPrefix(options);
    var newText = text.slice(0, wordStart) + completion + text.slice(caret);
    return { value: newText, caret: wordStart + completion.length, list: options.length > 1 ? options : [] };
  }

  function prompt(session) {
    var home = session.home, cwd = session.cwd;
    var shown = cwd === home ? '~' : (cwd.indexOf(home + '/') === 0 ? '~' + cwd.slice(home.length) : cwd);
    return session.user + '@' + session.hostname + ':' + shown + '$ ';
  }

  // Veřejné API
  root.LinuxCoreV58 = {
    VERSION: 58, HOME: HOME, STORAGE_KEY: STORAGE_KEY,
    createSession: createSession, serialize: serialize, restore: restore,
    save: saveSession, load: loadSession, reset: resetSession, clearSaved: clearSaved,
    runLine: runLine, complete: complete, prompt: prompt,
    registerCommand: registerCommand, isBuiltin: isBuiltin, knownCommandNames: knownCommandNames,
    setManual: setManual, getManual: getManual, addTip: addTip, errorTip: errorTip, suggest: suggest,
    envAll: envAll, protectedEnvNames: protectedEnvNames,
    fsutil: {
      normalize: normalize, dirnameOf: dirnameOf, basenameOf: basenameOf, nameCompare: nameCompare,
      getNode: getNode, isDir: isDir, isFile: isFile, childrenOf: childrenOf, treePaths: treePaths,
      deleteTree: deleteTree, fileSize: fileSize, canRead: canRead, canWrite: canWrite, canExecute: canExecute,
      canTraverse: canTraverse, canModifyDir: canModifyDir, mkdirp: mkdirp, readFile: readFile,
      writeFile: writeFile, writeFileAbs: writeFileAbs, modeString: modeString, octal: octal,
      humanSize: humanSize, chmodApply: chmodApply, globPaths: globPaths, escapeRegExp: escapeRegExp,
      globNameRegex: componentRegex, nowSec: nowSec
    }
  };
})(typeof window !== 'undefined' ? window : globalThis);
