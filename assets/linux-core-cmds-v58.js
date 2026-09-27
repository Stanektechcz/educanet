/* EDUCANET v58 · Linux Lab offline – sada příkazů pro pískoviště (assets/linux-core-v58.js musí
 * být načtený dřív). Chybové hlášky jsou anglicky jako ve skutečném Linuxu / online labu, české
 * tipy k nim doplňuje Core.addTip()/Core.errorTip(). Nic se nespouští doopravdy, žádná síť.
 * Zjednodušení oproti online labu: jediný uživatel (kontroluje se jen vlastnická trojice práv),
 * bez $(...) / historie „!!“, ls bez sloupcování a barev (adresáře mají místo barvy koncové „/“). */
(function (root) {
  'use strict';
  var Core = root.LinuxCoreV58;
  var U = Core.fsutil;
  var reg = Core.registerCommand;

  function pad2(n) { return (n < 10 ? '0' : '') + n; }
  function byteLength(s) { return (root.TextEncoder ? new root.TextEncoder().encode(s).length : s.length); }

  // Sdílené pomocníky (getopt, čtení vstupů, práce s řádky) --------------
  function parseOpts(args, spec) {
    var opts = {}, operands = [], n = args.length;
    for (var i = 0; i < n; i++) {
      var a = args[i];
      if (a === '--') { for (var j = i + 1; j < n; j++) operands.push(args[j]); break; }
      if (a !== '-' && a.charAt(0) === '-' && !/^-\d+$/.test(a)) {
        for (var k = 1; k < a.length; k++) {
          var ch = a.charAt(k), pos = spec.indexOf(ch);
          if (pos === -1) return { error: "invalid option -- '" + ch + "'" };
          if (spec.charAt(pos + 1) === ':') {
            var val = k + 1 < a.length ? a.slice(k + 1) : args[++i];
            if (val === undefined) return { error: "option requires an argument -- '" + ch + "'" };
            opts[ch] = val;
            break;
          }
          opts[ch] = true;
        }
        continue;
      }
      operands.push(a);
    }
    return { opts: opts, operands: operands };
  }
  function optError(proc, message) {
    proc.err(proc.name + ': ' + message + "\nTry '" + proc.name + " --help' for more information.\n");
    Core.addTip(proc.session, 'Neznámá volba. Nápovědu ukáže ' + proc.name + ' --help.');
    return 2;
  }
  function missingOperand(proc, what) {
    proc.err(proc.name + ': missing ' + (what || 'operand') + "\nTry '" + proc.name + " --help' for more information.\n");
    return 1;
  }
  function readInputs(proc, files) {
    var list = files.length ? files : ['-'];
    var out = [], status = 0;
    list.forEach(function (file) {
      if (file === '-') { out.push([file, proc.stdin]); return; }
      var err = {};
      var content = U.readFile(proc.session, file, err);
      if (content === null) { proc.err(proc.name + ': ' + file + ': ' + err.msg + '\n'); Core.errorTip(proc.session, err.msg, file); status = 1; return; }
      out.push([file, content]);
    });
    return { inputs: out, status: status };
  }
  function needsInput(proc, files) {
    if (files.length || proc.hasStdin) return false;
    Core.addTip(proc.session, proc.name + ' bez souboru čeká na text z klávesnice. Pošli mu ho rourou, např. cat soubor | ' + proc.name + '.');
    return true;
  }
  function splitLines(content) {
    if (content === '') return { lines: [], endsNl: false };
    var lines = content.split('\n');
    var endsNl = lines[lines.length - 1] === '';
    if (endsNl) lines.pop();
    return { lines: lines, endsNl: endsNl };
  }
  function joinLines(lines) { return lines.length ? lines.join('\n') + '\n' : ''; }

  // pwd, cd ----------------------------------------------------------------
  reg('pwd', function (proc) { proc.line(proc.session.cwd); return 0; }, { builtin: true });
  reg('cd', function (proc, argv) {
    var session = proc.session;
    var args = argv.slice(1).filter(function (a) { return a !== '-L' && a !== '-P'; });
    if (args.length > 1) { proc.err('bash: cd: too many arguments\n'); return 1; }
    var target = args[0] !== undefined ? args[0] : Core.envAll(session).HOME;
    if (target === '-') { target = session.oldCwd; proc.line(target); }
    var abs = U.normalize(target, session.cwd);
    var node = U.getNode(session.fs, abs);
    if (!node) { proc.err('bash: cd: ' + target + ': No such file or directory\n'); Core.errorTip(session, 'No such file or directory', target); return 1; }
    if (node.type !== 'd') { proc.err('bash: cd: ' + target + ': Not a directory\n'); return 1; }
    if (!U.canTraverse(session.fs, abs) || !U.canExecute(node)) { proc.err('bash: cd: ' + target + ': Permission denied\n'); Core.errorTip(session, 'Permission denied', target); return 1; }
    session.oldCwd = session.cwd;
    session.cwd = abs;
    return 0;
  }, { builtin: true });

  // ls ----------------------------------------------------------------------
  function lsDate(ts) {
    var d = new Date(ts * 1000);
    var m = ['led', 'úno', 'bře', 'dub', 'kvě', 'čvn', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'];
    return m[d.getMonth()] + ' ' + ('  ' + d.getDate()).slice(-2) + ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
  }
  function renderLs(entries, opt) {
    entries = entries.slice().sort(function (a, b) { return U.nameCompare(a[0], b[0]); });
    if (!opt.long) return joinLines(entries.map(function (e) { return e[0] + (e[1].type === 'd' ? '/' : ''); }));
    return joinLines(entries.map(function (e) {
      var size = U.fileSize(e[1]);
      return U.modeString(e[1]) + ' 1 student student ' + ('     ' + (opt.human ? U.humanSize(size) : size)).slice(-6) + ' ' + lsDate(e[1].mtime) + ' ' + e[0];
    }));
  }
  reg('ls', function (proc, argv) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), 'lahAR1');
    if (r.error) return optError(proc, r.error);
    var opt = { all: !!r.opts.a, almost: !!r.opts.A, long: !!r.opts.l, human: !!r.opts.h };
    var paths = r.operands.length ? r.operands : ['.'];
    var status = 0, files = [], dirs = [];
    paths.forEach(function (p) {
      var abs = U.normalize(p, session.cwd);
      var node = U.getNode(session.fs, abs);
      if (!node || !U.canTraverse(session.fs, abs)) {
        proc.err("ls: cannot access '" + p + "': " + (node ? 'Permission denied' : 'No such file or directory') + '\n');
        if (!node) Core.errorTip(session, 'No such file or directory', p);
        status = 2; return;
      }
      if (node.type === 'd') dirs.push([p, abs]); else files.push([p, node]);
    });
    var printed = false;
    if (files.length) { proc.out(renderLs(files, opt)); printed = true; }
    var header = paths.length > 1;
    dirs.forEach(function (pair) {
      if (printed) proc.out('\n');
      printed = true;
      if (header) proc.out(pair[0] + ':\n');
      var abs = pair[1], entries = [];
      if (opt.all) entries.push(['.', session.fs[abs]], ['..', session.fs[U.dirnameOf(abs)] || session.fs[abs]]);
      U.childrenOf(session.fs, abs).forEach(function (name) {
        if (name.charAt(0) === '.' && !opt.all && !opt.almost) return;
        entries.push([name, session.fs[(abs === '/' ? '' : abs) + '/' + name]]);
      });
      proc.out(renderLs(entries, opt));
    });
    return status;
  });

  // cat, echo, printf --------------------------------------------------------
  reg('cat', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'n');
    if (r.error) return optError(proc, r.error);
    var files = r.operands;
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var n = 0;
    ri.inputs.forEach(function (pair) {
      if (!r.opts.n) { proc.out(pair[1]); return; }
      var sp = splitLines(pair[1]);
      var out = sp.lines.map(function (line) { n++; return ('     ' + n).slice(-6) + '\t' + line; });
      proc.out(out.join('\n') + (sp.endsNl ? '\n' : ''));
    });
    return ri.status;
  });
  function unescapeBasic(text) {
    return text.replace(/\\(n|t|r|\\|a|b|e|0)/g, function (m, c) {
      return { n: '\n', t: '\t', r: '\r', '\\': '\\', a: '\x07', b: '\b', e: '\x1b', '0': '\0' }[c];
    });
  }
  reg('echo', function (proc, argv) {
    var args = argv.slice(1), newline = true, escapes = false;
    while (args.length && /^-[neE]+$/.test(args[0])) {
      var flag = args.shift();
      if (flag.indexOf('n') !== -1) newline = false;
      if (flag.indexOf('e') !== -1) escapes = true;
      if (flag.indexOf('E') !== -1) escapes = false;
    }
    var text = args.join(' ');
    proc.out((escapes ? unescapeBasic(text) : text) + (newline ? '\n' : ''));
    return 0;
  }, { builtin: true });
  reg('printf', function (proc, argv) {
    if (argv.length < 2) { proc.err('printf: usage: printf format [arguments]\n'); return 2; }
    var args = argv.slice(2);
    var out = argv[1].replace(/%([-+0]*\d*)(?:\.(\d+))?([sd%])/g, function (m, flags, prec, conv) {
      if (conv === '%') return '%';
      var arg = args.length ? String(args.shift()) : '';
      if (conv === 's') return prec ? arg.slice(0, parseInt(prec, 10)) : arg;
      return String(parseInt(arg, 10) || 0);
    });
    proc.out(unescapeBasic(out));
    return 0;
  }, { builtin: true });

  // mkdir, rmdir, touch, rm ---------------------------------------------------
  reg('mkdir', function (proc, argv) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), 'pv');
    if (r.error) return optError(proc, r.error);
    if (!r.operands.length) return missingOperand(proc);
    var status = 0;
    r.operands.forEach(function (dirPath) {
      var abs = U.normalize(dirPath, session.cwd);
      if (session.fs[abs]) {
        if (r.opts.p && U.isDir(session.fs, abs)) return;
        proc.err("mkdir: cannot create directory '" + dirPath + "': File exists\n"); status = 1; return;
      }
      var chain = [], cur = abs;
      while (!session.fs[cur]) { chain.push(cur); cur = U.dirnameOf(cur); }
      if (chain.length > 1 && !r.opts.p) {
        proc.err("mkdir: cannot create directory '" + dirPath + "': No such file or directory\n");
        Core.addTip(session, 'Nadřazená složka neexistuje. Celou cestu vytvoří mkdir -p ' + dirPath + '.');
        status = 1; return;
      }
      if (!U.isDir(session.fs, cur)) { proc.err("mkdir: cannot create directory '" + dirPath + "': Not a directory\n"); status = 1; return; }
      var ok = true;
      chain.slice().reverse().forEach(function (p) {
        if (!ok) return;
        var parent = U.dirnameOf(p);
        if (!U.canTraverse(session.fs, p) || !U.canModifyDir(session.fs, parent)) {
          proc.err("mkdir: cannot create directory '" + dirPath + "': Permission denied\n");
          Core.errorTip(session, 'Permission denied', parent);
          status = 1; ok = false; return;
        }
        session.fs[p] = { type: 'd', mode: 0o755, mtime: U.nowSec() };
        if (r.opts.v) proc.line("mkdir: created directory '" + (p === abs ? dirPath : p) + "'");
      });
    });
    return status;
  });
  reg('rmdir', function (proc, argv) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), 'p');
    if (r.error) return optError(proc, r.error);
    if (!r.operands.length) return missingOperand(proc);
    var status = 0;
    r.operands.forEach(function (dirPath) {
      var abs = U.normalize(dirPath, session.cwd);
      var node = U.canTraverse(session.fs, abs) ? U.getNode(session.fs, abs) : null;
      if (!node) { proc.err("rmdir: failed to remove '" + dirPath + "': No such file or directory\n"); status = 1; return; }
      if (node.type !== 'd') { proc.err("rmdir: failed to remove '" + dirPath + "': Not a directory\n"); status = 1; return; }
      if (U.childrenOf(session.fs, abs).length) {
        proc.err("rmdir: failed to remove '" + dirPath + "': Directory not empty\n");
        Core.addTip(session, 'rmdir maže jen prázdné složky. Se vším uvnitř smaže rm -r ' + dirPath + ' (opatrně!).');
        status = 1; return;
      }
      delete session.fs[abs];
    });
    return status;
  });
  reg('touch', function (proc, argv) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), 'ac');
    if (r.error) return optError(proc, r.error);
    if (!r.operands.length) return missingOperand(proc, 'file operand');
    var status = 0;
    r.operands.forEach(function (file) {
      var abs = U.normalize(file, session.cwd);
      var node = U.canTraverse(session.fs, abs) ? U.getNode(session.fs, abs) : null;
      if (node) { node.mtime = U.nowSec(); return; }
      if (r.opts.c) return;
      var err = {};
      if (!U.writeFile(session, file, '', false, err)) { proc.err("touch: cannot touch '" + file + "': " + err.msg + '\n'); Core.errorTip(session, err.msg, file); status = 1; }
    });
    return status;
  });
  reg('rm', function (proc, argv) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), 'rRfv');
    if (r.error) return optError(proc, r.error);
    var recursive = !!(r.opts.r || r.opts.R), force = !!r.opts.f;
    if (!r.operands.length) return force ? 0 : missingOperand(proc);
    var status = 0;
    r.operands.forEach(function (target) {
      var abs = U.normalize(target, session.cwd);
      if (abs === '/') {
        if (recursive) { proc.err("rm: it is dangerous to operate recursively on '/'\nrm: use --no-preserve-root to override this failsafe\n"); Core.addTip(session, 'Smazání celého pískoviště nedovolí. Vrátí ho tlačítko „Začít znovu“.'); }
        else proc.err("rm: cannot remove '/': Is a directory\n");
        status = 1; return;
      }
      var node = U.canTraverse(session.fs, abs) ? U.getNode(session.fs, abs) : null;
      if (!node) { if (!force) { proc.err("rm: cannot remove '" + target + "': No such file or directory\n"); status = 1; } return; }
      if (node.type === 'd' && !recursive) {
        proc.err("rm: cannot remove '" + target + "': Is a directory\n");
        Core.addTip(session, 'Složku smažeš rm -r ' + target + ', prázdnou také rmdir ' + target + '.');
        status = 1; return;
      }
      if (node.type === 'd') {
        if (r.opts.v) U.treePaths(session.fs, abs).slice().reverse().forEach(function (p) { proc.line((U.isDir(session.fs, p) ? "removed directory '" : "removed '") + p + "'"); });
        U.deleteTree(session.fs, abs); return;
      }
      delete session.fs[abs];
      if (r.opts.v) proc.line("removed '" + target + "'");
    });
    return status;
  });

  // cp, mv --------------------------------------------------------------------
  function copyMove(proc, argv, move) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), move ? 'vf' : 'rRvaf');
    if (r.error) return optError(proc, r.error);
    var recursive = move || !!(r.opts.r || r.opts.R || r.opts.a);
    var ops = r.operands;
    if (!ops.length) return missingOperand(proc, 'file operand');
    if (ops.length === 1) { proc.err(proc.name + ": missing destination file operand after '" + ops[0] + "'\n"); return 1; }
    var dest = ops.pop();
    var destAbs = U.normalize(dest, session.cwd);
    var destIsDir = U.isDir(session.fs, destAbs);
    if (ops.length > 1 && !destIsDir) { proc.err(proc.name + ": target '" + dest + "' is not a directory\n"); return 1; }
    var status = 0;
    ops.forEach(function (src) {
      var srcAbs = U.normalize(src, session.cwd);
      var node = U.canTraverse(session.fs, srcAbs) ? U.getNode(session.fs, srcAbs) : null;
      if (!node) { proc.err(proc.name + ": cannot stat '" + src + "': No such file or directory\n"); Core.errorTip(session, 'No such file or directory', src); status = 1; return; }
      var isDirSrc = node.type === 'd';
      if (isDirSrc && !recursive) { proc.err("cp: -r not specified; omitting directory '" + src + "'\n"); Core.addTip(session, 'Složku zkopíruješ s -r: cp -r ' + src + ' ' + dest); status = 1; return; }
      var target = destIsDir ? (destAbs === '/' ? '' : destAbs) + '/' + U.basenameOf(srcAbs) : destAbs;
      if (target === srcAbs) { proc.err(proc.name + ": '" + src + "' and '" + dest + "' are the same file\n"); status = 1; return; }
      if (isDirSrc && (target + '/').indexOf(srcAbs + '/') === 0) {
        proc.err((move ? "mv: cannot move '" : "cp: cannot copy a directory, '") + src + (move ? "' to a subdirectory of itself, '" : "', into itself, '") + dest + "'\n");
        status = 1; return;
      }
      if (!move && !U.canRead(node)) { proc.err("cp: cannot open '" + src + "' for reading: Permission denied\n"); status = 1; return; }
      var parentAbs = U.dirnameOf(target);
      if (!U.isDir(session.fs, parentAbs)) { proc.err(proc.name + ': cannot ' + (move ? 'move' : 'create regular file') + " '" + dest + "': No such file or directory\n"); status = 1; return; }
      if (!U.canModifyDir(session.fs, parentAbs)) { proc.err(proc.name + ": cannot create regular file '" + dest + "': Permission denied\n"); Core.errorTip(session, 'Permission denied', dest); status = 1; return; }
      if (session.fs[target] && U.isDir(session.fs, target) && !isDirSrc) { proc.err(proc.name + ": cannot overwrite directory '" + dest + "' with non-directory\n"); status = 1; return; }
      if (session.fs[target] && isDirSrc) U.deleteTree(session.fs, target);
      U.treePaths(session.fs, srcAbs).forEach(function (p) {
        var n = session.fs[p], t = target + p.slice(srcAbs.length);
        session.fs[t] = move ? n : { type: n.type, mode: n.mode, mtime: U.nowSec(), content: n.content };
      });
      if (move) U.deleteTree(session.fs, srcAbs);
      if (r.opts.v) proc.line((move ? 'renamed ' : '') + "'" + src + "' -> '" + (destIsDir ? dest.replace(/\/+$/, '') + '/' + U.basenameOf(srcAbs) : dest) + "'");
    });
    return status;
  }
  reg('cp', function (proc, argv) { return copyMove(proc, argv, false); });
  reg('mv', function (proc, argv) { return copyMove(proc, argv, true); });

  // head, tail ------------------------------------------------------------------
  function headTail(proc, argv, head) {
    var args = argv.slice(1).map(function (a) { var m = /^-(\d+)$/.exec(a); return m ? '-n' + m[1] : a; });
    var r = parseOpts(args, 'n:qv');
    if (r.error) return optError(proc, r.error);
    var raw = r.opts.n !== undefined ? r.opts.n : '10';
    if (!/^[+-]?\d+$/.test(raw)) { proc.err(proc.name + ": invalid number of lines: '" + raw + "'\n"); return 1; }
    var fromStart = !head && raw.charAt(0) === '+';
    var count = parseInt(raw, 10);
    if (!head && !fromStart) count = Math.abs(count);
    var files = r.operands;
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var multi = ri.inputs.length > 1 && !r.opts.q;
    ri.inputs.forEach(function (pair, k) {
      var name = pair[0], content = pair[1];
      if (multi) proc.out((k > 0 ? '\n' : '') + '==> ' + (name === '-' ? 'standard input' : name) + " <==\n");
      var lines = splitLines(content).lines, total = lines.length, sel;
      if (head) sel = count >= 0 ? lines.slice(0, count) : lines.slice(0, Math.max(0, total + count));
      else sel = fromStart ? lines.slice(Math.max(0, count - 1)) : (count === 0 ? [] : lines.slice(-count));
      if (sel.length) proc.out(sel.join('\n') + '\n');
    });
    return ri.status;
  }
  reg('head', function (proc, argv) { return headTail(proc, argv, true); });
  reg('tail', function (proc, argv) { return headTail(proc, argv, false); });

  // wc, sort, uniq, cut, tr ---------------------------------------------------------
  reg('wc', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'lwcm');
    if (r.error) return optError(proc, r.error);
    var want = [];
    if (r.opts.l) want.push('l');
    if (r.opts.w) want.push('w');
    if (r.opts.m || r.opts.c) want.push('c');
    if (!want.length) want = ['l', 'w', 'c'];
    var files = r.operands;
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var rows = [], total = { l: 0, w: 0, c: 0 };
    ri.inputs.forEach(function (pair) {
      var content = pair[1];
      var counts = { l: (content.match(/\n/g) || []).length, w: (content.trim().match(/\S+/g) || []).length, c: byteLength(content) };
      Object.keys(counts).forEach(function (k) { total[k] += counts[k]; });
      rows.push([pair[0], counts]);
    });
    if (rows.length > 1) rows.push(['total', total]);
    var single = want.length === 1 && rows.length === 1;
    rows.forEach(function (row) {
      var cols = want.map(function (k) { return single ? String(row[1][k]) : ('      ' + row[1][k]).slice(-7); });
      proc.line(cols.join(' ') + (row[0] === '-' ? '' : ' ' + row[0]));
    });
    return ri.status;
  });
  reg('sort', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'nruf');
    if (r.error) return optError(proc, r.error);
    var files = r.operands;
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var lines = [];
    ri.inputs.forEach(function (pair) { splitLines(pair[1]).lines.forEach(function (l) { lines.push(l); }); });
    var numeric = !!r.opts.n, fold = !!r.opts.f;
    lines.sort(function (a, b) {
      if (numeric) { var na = parseFloat(a) || 0, nb = parseFloat(b) || 0; if (na !== nb) return na - nb; }
      else { var ka = fold ? a.toLowerCase() : a, kb = fold ? b.toLowerCase() : b; if (ka !== kb) return ka < kb ? -1 : 1; }
      return a === b ? 0 : (a < b ? -1 : 1);
    });
    if (r.opts.r) lines.reverse();
    if (r.opts.u) lines = lines.filter(function (l, i) { return i === 0 || lines[i - 1] !== l; });
    proc.out(joinLines(lines));
    return ri.status;
  });
  reg('uniq', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'cudi');
    if (r.error) return optError(proc, r.error);
    var files = r.operands.slice(0, 1);
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var lines = splitLines((ri.inputs[0] || ['-', ''])[1]).lines;
    var icase = !!r.opts.i, groups = [];
    lines.forEach(function (line) {
      var last = groups[groups.length - 1];
      var same = last && (icase ? last[0].toLowerCase() === line.toLowerCase() : last[0] === line);
      if (same) last[1]++; else groups.push([line, 1]);
    });
    var out = [];
    groups.forEach(function (g) {
      if (r.opts.u && g[1] > 1) return;
      if (r.opts.d && g[1] < 2) return;
      out.push(r.opts.c ? ('      ' + g[1]).slice(-7) + ' ' + g[0] : g[0]);
    });
    proc.out(joinLines(out));
    return ri.status;
  });
  function cutRanges(list) {
    var ranges = [];
    var ok = list.split(',').every(function (part) {
      var m = /^(\d*)-(\d*)$/.exec(part);
      if (m) { if (m[1] === '' && m[2] === '') return false; ranges.push([m[1] === '' ? 1 : +m[1], m[2] === '' ? Infinity : +m[2]]); return true; }
      if (/^\d+$/.test(part) && +part > 0) { ranges.push([+part, +part]); return true; }
      return false;
    });
    return ok ? ranges : null;
  }
  function inRanges(n, ranges) { return ranges.some(function (r) { return n >= r[0] && n <= r[1]; }); }
  reg('cut', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'd:f:c:s');
    if (r.error) return optError(proc, r.error);
    var fields = r.opts.f, chars = r.opts.c;
    if (fields === undefined && chars === undefined) { proc.err('cut: you must specify a list of bytes, characters, or fields\n'); return 1; }
    var ranges = cutRanges(fields !== undefined ? fields : chars);
    if (!ranges) { proc.err("cut: invalid field value '" + (fields !== undefined ? fields : chars) + "'\n"); return 1; }
    var delim = r.opts.d !== undefined ? r.opts.d : '\t';
    var files = r.operands;
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var out = [];
    ri.inputs.forEach(function (pair) {
      splitLines(pair[1]).lines.forEach(function (line) {
        if (chars !== undefined) {
          var chunk = '';
          for (var i = 1; i <= line.length; i++) if (inRanges(i, ranges)) chunk += line.charAt(i - 1);
          out.push(chunk); return;
        }
        if (line.indexOf(delim) === -1) { if (!r.opts.s) out.push(line); return; }
        var parts = line.split(delim);
        out.push(parts.filter(function (p, i) { return inRanges(i + 1, ranges); }).join(delim));
      });
    });
    proc.out(joinLines(out));
    return ri.status;
  });
  var TR_CLASSES = { '[:upper:]': 'A-Z', '[:lower:]': 'a-z', '[:digit:]': '0-9', '[:alpha:]': 'A-Za-z', '[:alnum:]': 'A-Za-z0-9', '[:space:]': ' \t\n\r' };
  function trSet(set) {
    Object.keys(TR_CLASSES).forEach(function (k) { set = set.split(k).join(TR_CLASSES[k]); });
    set = set.replace(/\\n/g, '\n').replace(/\\t/g, '\t');
    var chars = Array.from(set), out = [];
    for (var i = 0; i < chars.length; i++) {
      if (i + 2 < chars.length && chars[i + 1] === '-') {
        var from = chars[i].codePointAt(0), to = chars[i + 2].codePointAt(0);
        if (from <= to && to - from < 2000) { for (var c = from; c <= to; c++) out.push(String.fromCodePoint(c)); i += 2; continue; }
      }
      out.push(chars[i]);
    }
    return out;
  }
  reg('tr', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'ds');
    if (r.error) return optError(proc, r.error);
    var sets = r.operands, del = !!r.opts.d, squeeze = !!r.opts.s;
    if (!sets.length) { proc.err('tr: missing operand\n'); return 1; }
    if (!del && !squeeze && sets.length < 2) { proc.err("tr: missing operand after '" + sets[0] + "'\n"); return 1; }
    if (!proc.hasStdin) { Core.addTip(proc.session, 'tr čte jen ze vstupu (roury). Zkus: cat soubor | tr a-z A-Z'); return 0; }
    var set1 = trSet(sets[0]), set2 = sets[1] !== undefined ? trSet(sets[1]) : [];
    var in1 = {}; set1.forEach(function (c) { in1[c] = true; });
    var map = {};
    if (!del && set2.length) { var lastCh = set2[set2.length - 1]; set1.forEach(function (c, i) { map[c] = set2[i] !== undefined ? set2[i] : lastCh; }); }
    var squeezeSet = {}; (del || !set2.length ? set1 : set2).forEach(function (c) { squeezeSet[c] = true; });
    var out = '', prev = null;
    Array.from(proc.stdin).forEach(function (ch) {
      var member = !!in1[ch];
      if (del && member) return;
      if (!del && member && map[ch] !== undefined) ch = map[ch];
      if (squeeze && prev === ch && squeezeSet[ch]) return;
      out += ch; prev = ch;
    });
    proc.out(out);
    return 0;
  });

  // grep ------------------------------------------------------------------------
  function grepRegex(pattern, icase) {
    try { return new RegExp(pattern, icase ? 'iu' : 'u'); } catch (e1) {
      try { return new RegExp(pattern, icase ? 'i' : ''); } catch (e2) { return null; }
    }
  }
  function collectGrepInputs(proc, abs0, disp, inputs, recursive, statusRef) {
    var session = proc.session;
    var abs = U.normalize(abs0, session.cwd);
    var node = U.getNode(session.fs, abs);
    if (!node) { proc.err('grep: ' + disp + ': No such file or directory\n'); statusRef.v = 2; return; }
    if (node.type !== 'd') { inputs.push([disp, node.content || '']); return; }
    if (!recursive) { proc.err('grep: ' + disp + ': Is a directory\n'); return; }
    U.childrenOf(session.fs, abs).forEach(function (name) {
      collectGrepInputs(proc, abs + '/' + name, (disp === '.' ? './' : disp.replace(/\/+$/, '') + '/') + name, inputs, recursive, statusRef);
    });
  }
  reg('grep', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'ivncr');
    if (r.error) return optError(proc, r.error);
    var pattern = r.operands.shift();
    if (pattern === undefined) { proc.err('Usage: grep [OPTION]... PATTERNS [FILE]...\n'); return 2; }
    var re = grepRegex(pattern, !!r.opts.i);
    if (!re) { proc.err('grep: Invalid regular expression\n'); return 2; }
    var recursive = !!r.opts.r, files = r.operands;
    var inputs = [], statusRef = { v: 0 };
    if (!files.length && recursive) files = ['.'];
    if (!files.length) {
      if (needsInput(proc, files)) return 2;
      inputs.push(['(standard input)', proc.stdin]);
    } else {
      files.forEach(function (file) { collectGrepInputs(proc, file, file, inputs, recursive, statusRef); });
    }
    var showName = files.length > 1 || recursive;
    var any = false;
    inputs.forEach(function (pair) {
      var name = pair[0], sp = splitLines(pair[1]), hits = [];
      sp.lines.forEach(function (line, i) { if (re.test(line) !== !!r.opts.v) hits.push(i); });
      if (hits.length) any = true;
      if (r.opts.c) { proc.line((showName ? name + ':' : '') + hits.length); return; }
      hits.forEach(function (i) { proc.line((showName ? name + ':' : '') + (r.opts.n ? (i + 1) + ':' : '') + sp.lines[i]); });
    });
    return any ? 0 : (statusRef.v || 1);
  });

  // find, tree --------------------------------------------------------------------
  reg('find', function (proc, argv) {
    var session = proc.session;
    var args = argv.slice(1), paths = [];
    while (args.length && args[0].charAt(0) !== '-') paths.push(args.shift());
    if (!paths.length) paths = ['.'];
    var nameRe = null, typeFilter = null;
    for (var i = 0; i < args.length; i++) {
      if (args[i] === '-name' && args[i + 1] !== undefined) { nameRe = U.globNameRegex(args[++i]); continue; }
      if (args[i] === '-type' && args[i + 1] !== undefined) { typeFilter = args[++i]; continue; }
    }
    var status = 0;
    paths.forEach(function (start) {
      var abs = U.normalize(start, session.cwd);
      if (!session.fs[abs]) { proc.err("find: '" + start + "': No such file or directory\n"); status = 1; return; }
      U.treePaths(session.fs, abs).forEach(function (p) {
        var node = session.fs[p];
        var rel = p === abs ? start : start.replace(/\/+$/, '') + p.slice(abs.length);
        if (nameRe && !nameRe.test(U.basenameOf(p))) return;
        if (typeFilter && ((typeFilter === 'f' && node.type !== 'f') || (typeFilter === 'd' && node.type !== 'd'))) return;
        proc.line(rel);
      });
    });
    return status;
  });
  reg('tree', function (proc, argv) {
    var session = proc.session;
    var r = parseOpts(argv.slice(1), 'ad');
    if (r.error) return optError(proc, r.error);
    var rootPath = r.operands[0] || '.';
    var abs = U.normalize(rootPath, session.cwd);
    var node = U.getNode(session.fs, abs);
    if (!node || node.type !== 'd') { proc.line(rootPath + '  [error opening dir]'); proc.line(''); proc.line('0 directories, 0 files'); return 2; }
    var counts = { d: 0, f: 0 };
    proc.line(rootPath);
    (function walk(dirAbs, prefix) {
      var names = U.childrenOf(session.fs, dirAbs).filter(function (n) { return r.opts.a || n.charAt(0) !== '.'; }).filter(function (n) {
        return !r.opts.d || U.isDir(session.fs, (dirAbs === '/' ? '' : dirAbs) + '/' + n);
      });
      names.forEach(function (name, idx) {
        var childAbs = (dirAbs === '/' ? '' : dirAbs) + '/' + name, isLast = idx === names.length - 1;
        var isDirNode = U.isDir(session.fs, childAbs);
        isDirNode ? counts.d++ : counts.f++;
        proc.line(prefix + (isLast ? '└── ' : '├── ') + name);
        if (isDirNode) walk(childAbs, prefix + (isLast ? '    ' : '│   '));
      });
    })(abs, '');
    proc.line('');
    proc.line(counts.d + (counts.d === 1 ? ' directory' : ' directories') + (r.opts.d ? '' : ', ' + counts.f + (counts.f === 1 ? ' file' : ' files')));
    return 0;
  });

  // chmod, stat, file -------------------------------------------------------------
  reg('chmod', function (proc, argv) {
    var session = proc.session;
    var args = argv.slice(1), rest = [], verbose = false;
    args.forEach(function (a) { if (a === '-v') verbose = true; else rest.push(a); });
    if (!rest.length) return missingOperand(proc);
    if (rest.length === 1) { proc.err("chmod: missing operand after '" + rest[0] + "'\n"); return 1; }
    var spec = rest.shift(), status = 0;
    rest.forEach(function (file) {
      var abs = U.normalize(file, session.cwd);
      var node = U.canTraverse(session.fs, abs) ? U.getNode(session.fs, abs) : null;
      if (!node) { proc.err("chmod: cannot access '" + file + "': No such file or directory\n"); Core.errorTip(session, 'No such file or directory', file); status = 1; return; }
      var next = U.chmodApply(node.mode, spec, node.type === 'd');
      if (next === null) { proc.err("chmod: invalid mode: '" + spec + "'\n"); status = 1; return; }
      var old = node.mode;
      node.mode = next;
      if (verbose) proc.line("mode of '" + file + "' changed from " + U.octal(old) + ' to ' + U.octal(next));
    });
    return status;
  });
  reg('stat', function (proc, argv) {
    var session = proc.session, files = argv.slice(1);
    if (!files.length) return missingOperand(proc);
    var status = 0;
    files.forEach(function (file) {
      var abs = U.normalize(file, session.cwd);
      var node = U.canTraverse(session.fs, abs) ? U.getNode(session.fs, abs) : null;
      if (!node) { proc.err("stat: cannot statx '" + file + "': No such file or directory\n"); status = 1; return; }
      var size = U.fileSize(node);
      var type = node.type === 'd' ? 'directory' : (size === 0 ? 'regular empty file' : 'regular file');
      var when = new Date(node.mtime * 1000).toISOString().replace('T', ' ').replace('Z', '');
      proc.out('  File: ' + file + '\n');
      proc.out('  Size: ' + size + '\tBlocks: ' + Math.ceil(size / 512) + '\tIO Block: 4096   ' + type + '\n');
      proc.out('Access: (' + U.octal(node.mode) + '/' + U.modeString(node) + ')  Uid: ( 1000/ student)   Gid: ( 1000/ student)\n');
      proc.out('Modify: ' + when + '\n');
    });
    return status;
  });
  function detectFileType(name, node) {
    if (node.type === 'd') return 'directory';
    var c = node.content || '';
    if (c === '') return 'empty';
    if (c.slice(0, 4) === '\x89PNG') return 'PNG image data';
    if (c.slice(0, 2) === '#!') return (c.indexOf('bash') !== -1 ? 'Bourne-Again shell script' : 'shell script') + ', text executable';
    var trimmed = c.replace(/^\s+/, '');
    if (/^<!doctype html/i.test(trimmed) || /^<html/i.test(trimmed)) return 'HTML document, ASCII text';
    if (name.slice(-4) === '.csv') return 'CSV text';
    return /[^\x00-\x7F]/.test(c) ? 'Unicode text, UTF-8 text' : 'ASCII text';
  }
  reg('file', function (proc, argv) {
    var session = proc.session, files = argv.slice(1);
    if (!files.length) { proc.err('Usage: file [file...]\n'); return 1; }
    files.forEach(function (f) {
      var abs = U.normalize(f, session.cwd);
      var node = U.canTraverse(session.fs, abs) ? U.getNode(session.fs, abs) : null;
      if (!node) { proc.line(f + ": cannot open `" + f + "' (No such file or directory)"); return; }
      proc.line(f + ': ' + detectFileType(U.basenameOf(abs), node));
    });
    return 0;
  });

  // date, whoami, id, hostname, uname ----------------------------------------------
  reg('date', function (proc, argv) {
    var format = null;
    argv.slice(1).forEach(function (a) { if (a.charAt(0) === '+') format = a.slice(1); });
    var d = new Date();
    if (!format) { proc.line(d.toString()); return 0; }
    var map = { '%Y': d.getFullYear(), '%m': pad2(d.getMonth() + 1), '%d': pad2(d.getDate()), '%H': pad2(d.getHours()), '%M': pad2(d.getMinutes()), '%S': pad2(d.getSeconds()), '%F': d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()), '%T': pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds()) };
    proc.line(format.replace(/%[A-Za-z%]/g, function (m) { return m === '%%' ? '%' : (map[m] !== undefined ? String(map[m]) : m); }));
    return 0;
  });
  reg('whoami', function (proc) { proc.line(proc.session.user); return 0; });
  reg('id', function (proc) { proc.line('uid=1000(student) gid=1000(student) groups=1000(student)'); return 0; });
  reg('hostname', function (proc, argv) {
    if (argv[1] && argv[1].charAt(0) !== '-') { proc.err('hostname: you must be root to change the host name\n'); return 1; }
    proc.line(proc.session.hostname);
    return 0;
  });
  reg('uname', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'asnrvmo');
    if (r.error) return optError(proc, r.error);
    if (!Object.keys(r.opts).length) { proc.line('Linux'); return 0; }
    if (r.opts.a) { proc.line('Linux ' + proc.session.hostname + ' 6.1.0-25-amd64 #1 SMP PREEMPT_DYNAMIC Debian x86_64 GNU/Linux'); return 0; }
    var parts = { s: 'Linux', n: proc.session.hostname, r: '6.1.0-25-amd64', v: '#1 SMP PREEMPT_DYNAMIC Debian', m: 'x86_64', o: 'GNU/Linux' };
    proc.line(['s', 'n', 'r', 'v', 'm', 'o'].filter(function (k) { return r.opts[k]; }).map(function (k) { return parts[k]; }).join(' '));
    return 0;
  });

  // history, clear, env, export, unset, which, type, true, false --------------------
  reg('history', function (proc, argv) {
    var session = proc.session, arg = argv[1];
    if (arg === '-c') { session.history = []; return 0; }
    var start = 0;
    if (arg !== undefined) {
      if (!/^\d+$/.test(arg)) { proc.err('bash: history: ' + arg + ': numeric argument required\n'); return 1; }
      start = Math.max(0, session.history.length - parseInt(arg, 10));
    }
    for (var i = start; i < session.history.length; i++) proc.line(('     ' + (i + 1)).slice(-5) + '  ' + session.history[i]);
    return 0;
  }, { builtin: true });
  reg('clear', function (proc) { proc.session.pendingClear = true; return 0; }, { builtin: true });
  reg('env', function (proc) {
    var env = Core.envAll(proc.session);
    Object.keys(env).sort().forEach(function (k) { proc.line(k + '=' + env[k]); });
    return 0;
  }, { builtin: true });
  reg('export', function (proc, argv) {
    var session = proc.session, args = argv.slice(1);
    if (!args.length || (args.length === 1 && args[0] === '-p')) {
      var env = Core.envAll(session);
      Object.keys(env).sort().forEach(function (k) { proc.line('declare -x ' + k + '="' + env[k] + '"'); });
      return 0;
    }
    var status = 0;
    args.forEach(function (arg) {
      var m = /^([A-Za-z_][A-Za-z0-9_]*)(?:=([\s\S]*))?$/.exec(arg);
      if (!m) { proc.err("bash: export: `" + arg + "': not a valid identifier\n"); status = 1; return; }
      if (m[2] !== undefined) session.env[m[1]] = m[2];
      else if (Core.envAll(session)[m[1]] === undefined) session.env[m[1]] = '';
    });
    return status;
  }, { builtin: true });
  reg('unset', function (proc, argv) {
    var session = proc.session, protectedNames = Core.protectedEnvNames();
    argv.slice(1).forEach(function (name) {
      if (protectedNames.indexOf(name) !== -1) session.env[name] = ''; else delete session.env[name];
    });
    return 0;
  }, { builtin: true });
  reg('which', function (proc, argv) {
    var status = 0;
    argv.slice(1).forEach(function (name) {
      if (name.charAt(0) === '-') return;
      if (Core.knownCommandNames().indexOf(name) === -1 || Core.isBuiltin(name)) { status = 1; return; }
      proc.line('/usr/bin/' + name);
    });
    return status;
  });
  reg('type', function (proc, argv) {
    var session = proc.session, status = 0;
    argv.slice(1).forEach(function (name) {
      if (session.aliases[name]) { proc.line(name + " is aliased to `" + session.aliases[name] + "'"); return; }
      if (Core.isBuiltin(name)) { proc.line(name + ' is a shell builtin'); return; }
      if (Core.knownCommandNames().indexOf(name) !== -1) { proc.line(name + ' is /usr/bin/' + name); return; }
      proc.err('bash: type: ' + name + ': not found\n');
      status = 1;
    });
    return status;
  }, { builtin: true });
  reg('true', function () { return 0; }, { builtin: true });
  reg('false', function () { return 1; }, { builtin: true });

  // seq, basename, dirname, rev, nl, tee -------------------------------------------
  reg('seq', function (proc, argv) {
    var nums = argv.slice(1).map(Number);
    var start = 1, step = 1, end;
    if (nums.length === 1) end = nums[0];
    else if (nums.length === 2) { start = nums[0]; end = nums[1]; }
    else if (nums.length >= 3) { start = nums[0]; step = nums[1]; end = nums[2]; }
    else { proc.err('seq: missing operand\n'); return 1; }
    if (!step) { proc.err("seq: invalid Zero increment value: '0'\n"); return 1; }
    var out = [];
    if (step > 0) for (var i = start; i <= end; i += step) out.push(i);
    else for (var j = start; j >= end; j += step) out.push(j);
    proc.out(joinLines(out.map(String)));
    return 0;
  });
  reg('basename', function (proc, argv) {
    var p = argv[1] || '';
    var base = U.basenameOf(p.replace(/\/+$/, '') || '/');
    var suffix = argv[2];
    if (suffix && base.length > suffix.length && base.slice(-suffix.length) === suffix) base = base.slice(0, -suffix.length);
    proc.line(base);
    return 0;
  }, { builtin: true });
  reg('dirname', function (proc, argv) {
    proc.line(U.dirnameOf((argv[1] || '').replace(/\/+$/, '') || '/'));
    return 0;
  }, { builtin: true });
  reg('rev', function (proc, argv) {
    var files = argv.slice(1);
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    ri.inputs.forEach(function (pair) {
      var sp = splitLines(pair[1]);
      proc.out(sp.lines.map(function (l) { return Array.from(l).reverse().join(''); }).join('\n') + (sp.lines.length ? '\n' : ''));
    });
    return ri.status;
  });
  reg('nl', function (proc, argv) {
    var files = argv.slice(1);
    if (needsInput(proc, files)) return 0;
    var ri = readInputs(proc, files);
    var n = 0;
    ri.inputs.forEach(function (pair) {
      splitLines(pair[1]).lines.forEach(function (line) {
        if (line === '') { proc.line(''); return; }
        n++;
        proc.line(('     ' + n).slice(-6) + '\t' + line);
      });
    });
    return ri.status;
  });
  reg('tee', function (proc, argv) {
    var r = parseOpts(argv.slice(1), 'a');
    if (r.error) return optError(proc, r.error);
    var status = 0;
    r.operands.forEach(function (file) {
      var err = {};
      if (!U.writeFile(proc.session, file, proc.stdin, !!r.opts.a, err)) { proc.err('tee: ' + file + ': ' + err.msg + '\n'); Core.errorTip(proc.session, err.msg, file); status = 1; }
    });
    proc.out(proc.stdin);
    return status;
  });

  // man, help (z assets/lab-manual-v58.json přes Core.setManual) ---------------------
  function wrapText(text, indent, width) {
    width = width || 76;
    var pad = new Array(indent + 1).join(' '), lines = [];
    (text || '').split('\n').forEach(function (para) {
      var words = para.trim().split(/\s+/).filter(Boolean), line = '';
      words.forEach(function (word) {
        if (line && (line + ' ' + word).length > width - indent) { lines.push(pad + line); line = word; return; }
        line = line ? line + ' ' + word : word;
      });
      if (line) lines.push(pad + line);
    });
    return lines.length ? lines.join('\n') + '\n' : '';
  }
  reg('man', function (proc, argv) {
    var manual = Core.getManual();
    var args = argv.slice(1);
    if (!args.length) { proc.err("What manual page do you want?\nFor example, try 'man man'.\n"); return 1; }
    var name = args[args.length - 1];
    if (!Object.keys(manual.commands).length) { proc.err('man: příručka se v tomto prohlížeči zatím nenačetla (musíš být aspoň jednou online).\n'); return 1; }
    if (name === 'man') { proc.out('MAN(1)     Příručka EDUCANET (offline)     MAN(1)\n\nNÁZEV\n       man - zobrazí příručku k příkazu\n\nPOUŽITÍ\n       man příkaz\n'); return 0; }
    var cmd = manual.commands[name];
    if (!cmd) {
      proc.err('No manual entry for ' + name + '\n');
      var s = Core.suggest(name, Object.keys(manual.commands));
      if (s) Core.addTip(proc.session, 'Nemyslel(a) jsi man ' + s + '?');
      return 16;
    }
    var title = name.toUpperCase() + '(1)';
    var out = title + '     Příručka EDUCANET (offline)     ' + title;
    out += '\n\nNÁZEV\n       ' + name + ' - ' + cmd.summary + '\n\nPOUŽITÍ\n       ' + cmd.synopsis + '\n\nPOPIS\n' + wrapText(cmd.about, 7);
    if (cmd.options && cmd.options.length) { out += '\nVOLBY\n'; cmd.options.forEach(function (o) { out += '       ' + o[0] + '\n' + wrapText(o[1], 14); }); }
    if (cmd.examples && cmd.examples.length) { out += '\nPŘÍKLADY\n'; cmd.examples.forEach(function (e) { out += '       $ ' + e[0] + '\n' + wrapText(e[1], 14); }); }
    if (cmd.tip) out += '\nTIP\n' + wrapText(cmd.tip, 7);
    if (Core.knownCommandNames().indexOf(name) === -1) out += '\nPOZNÁMKA\n       Tento příkaz offline pískoviště nepodporuje – stránka slouží jako přehled.\n';
    proc.out(out + '\n');
    return 0;
  }, { builtin: true });
  reg('help', function (proc) {
    var manual = Core.getManual();
    proc.line('EDUCANET Linux Lab – offline pískoviště. Nic se nespouští doopravdy, klidně zkoušej.');
    proc.line('');
    var byCat = {};
    Object.keys(manual.commands || {}).forEach(function (name) {
      if (Core.knownCommandNames().indexOf(name) === -1) return;
      var cat = manual.commands[name].cat || '?';
      (byCat[cat] = byCat[cat] || []).push(name);
    });
    Object.keys(manual.categories || {}).forEach(function (key) {
      var names = byCat[key];
      if (!names || !names.length) return;
      proc.line((manual.categories[key].label + '                          ').slice(0, 26) + names.sort().join(' '));
    });
    proc.line('');
    proc.line('Shell umí: roury |, přesměrování > >> < 2> 2>&1, && || ;, proměnné $HOME, ~, glob * ?');
    proc.line('Klávesy: ↑/↓ historie · Tab doplňování · Ctrl+L smaže obrazovku');
    proc.line('Nápověda k příkazu: man <příkaz>');
    return 0;
  }, { builtin: true });
})(typeof window !== 'undefined' ? window : globalThis);
