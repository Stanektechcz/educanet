<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – drobné příkazy coreutils/util-linux pro soubory a text (CNT-02):
 * basename, dirname, realpath, readlink, seq, shuf, paste, column, fold, comm, join, split, yes.
 * Výstupy, chyby a exit kódy podle GNU coreutils 9.1 / util-linux 2.38 (Debian 12). Nic se nespouští.
 * Náhoda (shuf) jen z Lab57Rng se semínkem světa; nekonečné výstupy (yes, seq) mají strop.
 */

const LAB58_X_MAX_ITEMS = 100000;

function lab58_x_extra(Lab57Proc $p, string $operand): int
{
    $p->err($p->name . ': extra operand ‘' . $operand . "’\nTry '" . $p->name . " --help' for more information.\n");
    $p->w->tip(tr('Příkaz dostal víc argumentů, než umí. Správné použití ukáže man {name}.', ['name' => $p->name]));
    return 1;
}

// ---------------------------------------------------------------------------
// basename, dirname
// ---------------------------------------------------------------------------

function lab58_x_basename(string $name, ?string $suffix): string
{
    if ($name === '') return '';
    $t = rtrim($name, '/');
    if ($t === '') return '/';
    $pos = strrpos($t, '/');
    $base = $pos === false ? $t : substr($t, $pos + 1);
    if ($suffix !== null && $suffix !== '' && $base !== $suffix && str_ends_with($base, $suffix)) $base = substr($base, 0, -strlen($suffix));
    return $base;
}

function lab58_cmd_basename(Lab57Proc $p, array $argv): int
{
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'as:z', ['multiple' => false, 'suffix' => true, 'zero' => false]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if ($ops === []) return lab57_missing_operand($p);
    $suffix = $o['s'] ?? $o['--suffix'] ?? null;
    if (!isset($o['a']) && !isset($o['--multiple']) && $suffix === null) {
        if (count($ops) > 2) return lab58_x_extra($p, $ops[2]);
        $suffix = $ops[1] ?? null;
        $ops = [$ops[0]];
    }
    $end = isset($o['z']) || isset($o['--zero']) ? "\0" : "\n";
    foreach ($ops as $name) $p->out(lab58_x_basename($name, $suffix === null ? null : (string)$suffix) . $end);
    return 0;
}

function lab58_x_dirname(string $name): string
{
    $t = rtrim($name, '/');
    if ($t === '') return $name === '' ? '.' : '/';
    $pos = strrpos($t, '/');
    if ($pos === false) return '.';
    $dir = rtrim(substr($t, 0, $pos), '/');
    return $dir === '' ? '/' : $dir;
}

function lab58_cmd_dirname(Lab57Proc $p, array $argv): int
{
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'z', ['zero' => false]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if ($ops === []) return lab57_missing_operand($p);
    foreach ($ops as $name) $p->out(lab58_x_dirname($name) . (isset($o['z']) || isset($o['--zero']) ? "\0" : "\n"));
    return 0;
}

// ---------------------------------------------------------------------------
// realpath, readlink (VFS nemá symbolické odkazy – kanonická cesta = normalizovaná cesta)
// ---------------------------------------------------------------------------

/** @return array{0:?string,1:?string} [cesta, chyba]; $mode e = vše musí existovat, d = vše kromě poslední části, m = nic */
function lab58_x_canon(Lab57World $w, string $path, string $mode): array
{
    $abs = $w->abs($path);
    if ($mode === 'm') return [$abs, null];
    $parts = $abs === '/' ? [] : explode('/', substr($abs, 1));
    $cur = '';
    foreach ($parts as $i => $part) {
        $cur .= '/' . $part;
        if (!$w->canTraverse($cur)) return [null, 'Permission denied'];
        $node = $w->fs->get($cur);
        $last = $i === count($parts) - 1;
        if ($node === null) return $last && $mode === 'd' ? [$abs, null] : [null, 'No such file or directory'];
        if (!$last && ($node['t'] ?? '') !== 'd') return [null, 'Not a directory'];
    }
    return [$abs, null];
}

function lab58_x_relative(string $path, string $base): string
{
    $a = $path === '/' ? [] : explode('/', substr($path, 1));
    $b = $base === '/' ? [] : explode('/', substr($base, 1));
    $i = 0;
    while ($i < count($a) && $i < count($b) && $a[$i] === $b[$i]) $i++;
    $rel = array_merge(array_fill(0, count($b) - $i, '..'), array_slice($a, $i));
    return $rel === [] ? '.' : implode('/', $rel);
}

function lab58_cmd_realpath(Lab57Proc $p, array $argv): int
{
    $long = ['canonicalize-existing' => false, 'canonicalize-missing' => false, 'logical' => false, 'physical' => false, 'quiet' => false, 'strip' => false, 'no-symlinks' => false, 'zero' => false, 'relative-to' => true, 'relative-base' => true];
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'emLPqsz', $long);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if ($ops === []) return lab57_missing_operand($p);
    $mode = isset($o['e']) || isset($o['--canonicalize-existing']) ? 'e' : (isset($o['m']) || isset($o['--canonicalize-missing']) ? 'm' : 'd');
    $quiet = isset($o['q']) || isset($o['--quiet']);
    $relTo = isset($o['--relative-to']) ? lab58_x_canon($p->w, (string)$o['--relative-to'], 'm')[0] : null;
    $relBase = isset($o['--relative-base']) ? lab58_x_canon($p->w, (string)$o['--relative-base'], 'm')[0] : null;
    $status = 0;
    foreach ($ops as $path) {
        [$abs, $err] = lab58_x_canon($p->w, $path, $mode);
        if ($abs === null) {
            if (!$quiet) { $p->err("realpath: $path: $err\n"); lab57_error_tip($p->w, (string)$err, $path); }
            $status = 1;
            continue;
        }
        if ($relTo !== null) $abs = lab58_x_relative($abs, $relTo);
        elseif ($relBase !== null && ($abs === $relBase || str_starts_with($abs, rtrim($relBase, '/') . '/'))) $abs = lab58_x_relative($abs, $relBase);
        $p->out($abs . (isset($o['z']) || isset($o['--zero']) ? "\0" : "\n"));
    }
    return $status;
}

function lab58_cmd_readlink(Lab57Proc $p, array $argv): int
{
    $long = ['canonicalize' => false, 'canonicalize-existing' => false, 'canonicalize-missing' => false, 'no-newline' => false, 'quiet' => false, 'silent' => false, 'verbose' => false, 'zero' => false];
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'femnqsvz', $long);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if ($ops === []) return lab57_missing_operand($p);
    $mode = isset($o['f']) || isset($o['--canonicalize']) ? 'd' : (isset($o['e']) || isset($o['--canonicalize-existing']) ? 'e' : (isset($o['m']) || isset($o['--canonicalize-missing']) ? 'm' : null));
    $verbose = isset($o['v']) || isset($o['--verbose']);
    $noNl = (isset($o['n']) || isset($o['--no-newline'])) && count($ops) === 1;
    $status = 0;
    foreach ($ops as $path) {
        if ($mode === null) {
            if ($verbose) $p->err("readlink: $path: " . ($p->w->fs->exists($p->w->abs($path)) ? 'Invalid argument' : 'No such file or directory') . "\n");
            $p->w->tip(tr('„{path}“ není symbolický odkaz (v laboratoři odkazy nejsou). Celou cestu vypíše readlink -f {path} nebo realpath {path}.', ['path' => $path]));
            $status = 1;
            continue;
        }
        [$abs, $err] = lab58_x_canon($p->w, $path, $mode);
        if ($abs === null) {
            if ($verbose) $p->err("readlink: $path: $err\n");
            $status = 1;
            continue;
        }
        $p->out($abs . ($noNl ? '' : (isset($o['z']) || isset($o['--zero']) ? "\0" : "\n")));
    }
    return $status;
}

// ---------------------------------------------------------------------------
// seq
// ---------------------------------------------------------------------------

/** @return array{0:int,1:int}|null [celé číslo, počet desetinných míst] */
function lab58_x_seqnum(string $s): ?array
{
    if (preg_match('/^([+-]?)(\d*)(?:\.(\d*))?$/', $s, $m) !== 1 || ($m[2] === '' && ($m[3] ?? '') === '') || strlen($m[2] . ($m[3] ?? '')) > 15) return null;
    $frac = (string)($m[3] ?? '');
    return [($m[1] === '-' ? -1 : 1) * (int)(($m[2] === '' ? '0' : $m[2]) . $frac), strlen($frac)];
}

function lab58_cmd_seq(Lab57Proc $p, array $argv): int
{
    $args = array_values(array_slice($argv, 1));
    $sep = "\n";
    $fmt = null;
    $wide = false;
    $ops = [];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($ops !== [] || $a === '-' || !str_starts_with($a, '-') || preg_match('/^-\.?\d/', $a) === 1) { $ops[] = $a; continue; }
        if ($a === '-w' || $a === '--equal-width') { $wide = true; continue; }
        if (preg_match('/^(-s|--separator=?)(.*)$/s', $a, $m) === 1 || preg_match('/^(-f|--format=?)(.*)$/s', $a, $m) === 1) {
            $value = $m[2] !== '' || str_ends_with($m[1], '=') ? $m[2] : ($args[++$i] ?? null);
            if ($value === null) return lab57_opt_error($p, "option requires an argument -- '" . substr($m[1], 1, 1) . "'", 1);
            if ($m[1][1] === 's' || str_starts_with($m[1], '--s')) $sep = (string)$value;
            else $fmt = (string)$value;
            continue;
        }
        return lab57_opt_error($p, str_starts_with($a, '--') ? "unrecognized option '$a'" : "invalid option -- '" . $a[1] . "'", 1);
    }
    if ($ops === []) return lab57_missing_operand($p);
    if (count($ops) > 3) return lab58_x_extra($p, $ops[3]);
    $nums = [];
    foreach ($ops as $op) {
        $num = lab58_x_seqnum($op);
        if ($num === null) {
            $p->err("seq: invalid floating point argument: ‘{$op}’\nTry 'seq --help' for more information.\n");
            $p->w->tip(tr('seq čeká čísla: seq 5, seq 2 10, seq 0 5 100 (od, krok, do). → man seq'));
            return 1;
        }
        $nums[] = $num;
    }
    [$first, $step, $last] = count($nums) === 1 ? [[1, 0], [1, 0], $nums[0]] : (count($nums) === 2 ? [$nums[0], [1, 0], $nums[1]] : $nums);
    if ($step[0] === 0) { $p->err("seq: invalid Zero increment value: ‘{$ops[1]}’\nTry 'seq --help' for more information.\n"); return 1; }
    if ($fmt !== null && $wide) { $p->err("seq: format string may not be specified when printing equal width strings\nTry 'seq --help' for more information.\n"); return 1; }
    if ($fmt !== null && preg_match('/^(?:[^%]|%%)*%[-+ #0\']*\d*(?:\.\d+)?[eEfFgG](?:[^%]|%%)*$/', $fmt) !== 1) { $p->err("seq: format ‘{$fmt}’ has unknown %" . (preg_match('/%[-+ #0\']*\d*(?:\.\d+)?(.)/', $fmt, $fm) === 1 ? $fm[1] : '') . " directive\n"); return 1; }
    $scale = max($first[1], $step[1], $last[1]);
    $prec = max($first[1], $step[1]);
    [$f, $s, $l] = [$first[0] * 10 ** ($scale - $first[1]), $step[0] * 10 ** ($scale - $step[1]), $last[0] * 10 ** ($scale - $last[1])];
    $format = static function (int $v) use ($scale, $prec, $fmt): string {
        if ($fmt !== null) return sprintf($fmt, $v / 10 ** $scale);
        $abs = str_pad((string)abs($v), $scale + 1, '0', STR_PAD_LEFT);
        $int = substr($abs, 0, strlen($abs) - $scale);
        return ($v < 0 ? '-' : '') . $int . ($prec > 0 ? '.' . substr($abs, strlen($abs) - $scale, $prec) : '');
    };
    $width = $wide ? max(strlen($format($f)), strlen($format($l))) : 0;
    $out = [];
    for ($v = $f; $s > 0 ? $v <= $l : $v >= $l; $v += $s) {
        if (count($out) >= LAB58_X_MAX_ITEMS) { $p->w->tip(tr('Simulace vypíše nejvýš {max} čísel.', ['max' => LAB58_X_MAX_ITEMS])); break; }
        $t = $format($v);
        $out[] = $width > 0 ? ($v < 0 ? '-' . str_pad(substr($t, 1), $width - 1, '0', STR_PAD_LEFT) : str_pad($t, $width, '0', STR_PAD_LEFT)) : $t;
    }
    if ($out !== []) $p->out(implode($sep, $out) . "\n");
    return 0;
}

// ---------------------------------------------------------------------------
// shuf (deterministicky ze semínka světa)
// ---------------------------------------------------------------------------

function lab58_cmd_shuf(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $long = ['echo' => false, 'input-range' => true, 'head-count' => true, 'output' => true, 'repeat' => false, 'zero-terminated' => false, 'random-source' => true];
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'ei:n:o:rz', $long);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    $range = $o['i'] ?? $o['--input-range'] ?? null;
    if (isset($o['e']) || isset($o['--echo'])) {
        $items = $ops;
    } elseif ($range !== null) {
        if ($ops !== []) return lab58_x_extra($p, $ops[0]);
        if (preg_match('/^(\d{1,9})-(\d{1,9})$/', (string)$range, $m) !== 1 || (int)$m[2] < (int)$m[1] - 1) { $p->err("shuf: invalid input range: ‘{$range}’\n"); return 1; }
        if ((int)$m[2] - (int)$m[1] >= LAB58_X_MAX_ITEMS) { $p->err("shuf: invalid input range: ‘{$range}’\n"); $w->tip(tr('Simulace zamíchá nejvýš {max} čísel.', ['max' => LAB58_X_MAX_ITEMS])); return 1; }
        $items = array_map('strval', range((int)$m[1], (int)$m[2]));
        if ((int)$m[2] < (int)$m[1]) $items = [];
    } else {
        if (count($ops) > 1) return lab58_x_extra($p, $ops[1]);
        if (lab57_needs_input($p, $ops)) return 0;
        [$inputs, $status] = lab57_read_inputs($p, $ops);
        if ($status !== 0) return 1;
        $items = lab57_split_lines((string)($inputs[0][1] ?? ''))[0];
    }
    $count = $o['n'] ?? $o['--head-count'] ?? null;
    if ($count !== null && preg_match('/^\d{1,9}$/', (string)$count) !== 1) { $p->err("shuf: invalid line count: ‘{$count}’\n"); return 1; }
    $w->effects['shuf'] = (int)($w->effects['shuf'] ?? 0) + 1;
    $source = $o['--random-source'] ?? null;
    $rng = new Lab57Rng($source !== null ? 'shuf-src|' . md5((string)$w->readFile((string)$source)) : $w->seed . '|shuf|' . count($w->history) . '|' . $w->effects['shuf']);
    if (isset($o['r']) || isset($o['--repeat'])) {
        if ($items === []) { $p->err("shuf: no lines to repeat\n"); return 1; }
        if ($count === null) $w->tip(tr('shuf -r by bez -n psal donekonečna – simulace skončí po 1000 řádcích.'));
        $out = [];
        for ($k = 0, $max = min(LAB58_X_MAX_ITEMS, (int)($count ?? 1000)); $k < $max; $k++) $out[] = (string)$rng->pick($items);
    } else {
        $out = $rng->shuffle($items);
        if ($count !== null) $out = array_slice($out, 0, (int)$count);
    }
    $text = $out === [] ? '' : implode(isset($o['z']) ? "\0" : "\n", $out) . (isset($o['z']) ? "\0" : "\n");
    $file = $o['o'] ?? $o['--output'] ?? null;
    if ($file === null) { $p->out($text); return 0; }
    $err = null;
    if (!$w->writeFile((string)$file, $text, false, $err)) { $p->err("shuf: {$file}: $err\n"); return 1; }
    return 0;
}

// ---------------------------------------------------------------------------
// paste, column, fold
// ---------------------------------------------------------------------------

/** Seznam oddělovačů paste -d (\n \t \\ \0 = prázdný). */
function lab58_x_delims(string $spec): array
{
    $out = [];
    $chars = mb_str_split($spec);
    for ($i = 0, $n = count($chars); $i < $n; $i++) {
        if ($chars[$i] === '\\' && $i + 1 < $n) {
            $d = $chars[++$i];
            $out[] = match ($d) { 'n' => "\n", 't' => "\t", '0' => '', default => $d };
            continue;
        }
        $out[] = $chars[$i];
    }
    return $out === [] ? [''] : $out;
}

function lab58_cmd_paste(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'd:sz', ['delimiters' => true, 'serial' => false, 'zero-terminated' => false]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if (lab57_needs_input($p, $files)) return 0;
    if ($files === []) $files = ['-'];
    $delims = lab58_x_delims((string)($o['d'] ?? $o['--delimiters'] ?? "\t"));
    $serial = isset($o['s']) || isset($o['--serial']);
    $stdin = lab57_split_lines($p->stdin)[0];
    $dashes = count(array_keys($files, '-', true));
    $cols = [];
    $k = 0;
    foreach ($files as $file) {
        if ($file === '-') {
            $mine = [];
            foreach ($stdin as $idx => $line) if ($serial ? $k === 0 : $idx % $dashes === $k) $mine[] = $line;
            $cols[] = $mine;
            $k++;
            continue;
        }
        $err = null;
        $content = $p->w->readFile($file, $err);
        if ($content === null) { $p->err("paste: $file: $err\n"); lab57_error_tip($p->w, (string)$err, $file); return 1; }
        $cols[] = lab57_split_lines($content)[0];
    }
    $out = [];
    $d = static fn(int $i): string => $delims[$i % count($delims)];
    if ($serial) {
        foreach ($cols as $lines) {
            $line = '';
            foreach ($lines as $i => $l) $line .= ($i > 0 ? $d($i - 1) : '') . $l;
            $out[] = $line;
        }
    } else {
        for ($r = 0, $rows = max(array_map('count', $cols)); $r < $rows; $r++) {
            $line = '';
            foreach ($cols as $i => $lines) $line .= ($i > 0 ? $d($i - 1) : '') . ($lines[$r] ?? '');
            $out[] = $line;
        }
    }
    $p->out(lab57_join($out));
    return 0;
}

function lab58_cmd_column(Lab57Proc $p, array $argv): int
{
    $long = ['table' => false, 'output-width' => true, 'separator' => true, 'output-separator' => true, 'fillrows' => false, 'table-columns' => true, 'table-right' => true, 'keep-empty-lines' => false];
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'tc:s:o:xN:R:L', $long);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $lines = [];
    foreach ($inputs as [, $content]) foreach (lab57_split_lines($content)[0] as $l) if (trim($l) !== '' || isset($o['L'])) $lines[] = $l;
    $width = max(1, (int)($o['c'] ?? $o['--output-width'] ?? ($p->w->envAll()['COLUMNS'] ?? 80)));
    if (isset($o['t']) || isset($o['--table'])) {
        $sep = $o['s'] ?? $o['--separator'] ?? null;
        $osep = (string)($o['o'] ?? $o['--output-separator'] ?? '  ');
        $names = $o['N'] ?? $o['--table-columns'] ?? null;
        $rows = $names !== null ? [explode(',', (string)$names)] : [];
        foreach ($lines as $l) $rows[] = $sep === null ? (preg_split('/\s+/u', trim($l), -1, PREG_SPLIT_NO_EMPTY) ?: []) : array_values(array_filter(preg_split('/[' . preg_quote((string)$sep, '/') . ']/u', $l) ?: [], static fn(string $c): bool => $c !== ''));
        $right = [];
        foreach (explode(',', (string)($o['R'] ?? $o['--table-right'] ?? '')) as $r) if ($r !== '') $right[] = ctype_digit($r) ? (int)$r - 1 : (int)array_search($r, $rows[0] ?? [], true);
        $wd = [];
        foreach ($rows as $cells) foreach ($cells as $i => $c) $wd[$i] = max($wd[$i] ?? 0, mb_strlen($c));
        $out = [];
        foreach ($rows as $cells) {
            $parts = [];
            foreach ($cells as $i => $c) {
                $pad = str_repeat(' ', max(0, $wd[$i] - mb_strlen($c)));
                $parts[] = in_array($i, $right, true) ? $pad . $c : ($i === count($cells) - 1 ? $c : $c . $pad);
            }
            $out[] = implode($osep, $parts);
        }
        $p->out(lab57_join($out));
        return $status;
    }
    if ($lines === []) return $status;
    $max = max(array_map('mb_strlen', $lines));
    $colw = ($max + 8) & ~7;
    $numcols = max(1, intdiv($width, $colw));
    if ($colw >= $width) { $p->out(lab57_join($lines)); return $status; }
    $numrows = (int)ceil(count($lines) / $numcols);
    $out = '';
    for ($row = 0; $row < $numrows; $row++) {
        $chcnt = 0;
        $endcol = $colw;
        for ($col = 0; $col < $numcols; $col++) {
            $idx = isset($o['x']) || isset($o['--fillrows']) ? $row * $numcols + $col : $col * $numrows + $row;
            if (!isset($lines[$idx])) break;
            $out .= $lines[$idx];
            $chcnt += mb_strlen($lines[$idx]);
            $nextIdx = isset($o['x']) || isset($o['--fillrows']) ? $idx + 1 : $idx + $numrows;
            if ($col + 1 >= $numcols || !isset($lines[$nextIdx])) break;
            while (($cnt = ($chcnt + 8) & ~7) <= $endcol) { $out .= "\t"; $chcnt = $cnt; }
            $endcol += $colw;
        }
        $out .= "\n";
    }
    $p->out($out);
    return $status;
}

/** Zalomí text jako GNU fold (tabulátor do násobku 8; -s láme za poslední mezerou). Počítá znaky, ne bajty. */
function lab58_x_fold(string $text, int $width, bool $spaces, bool $bytes): string
{
    $out = '';
    $lines = explode("\n", $text);
    $last = count($lines) - 1;
    foreach ($lines as $li => $line) {
        $buf = [];
        $col = 0;
        $adv = static fn(int $c, string $ch): int => $ch === "\t" ? $c + 8 - $c % 8 : ($ch === "\x08" ? max(0, $c - 1) : ($ch === "\r" ? 0 : $c + 1));
        foreach ($bytes ? str_split($line) : mb_str_split($line) as $ch) {
            for ($guard = 0; $guard < 4; $guard++) {
                $ncol = $adv($col, $ch);
                if ($ncol <= $width || $buf === []) break;
                $cut = $spaces ? max((int)array_search(' ', array_reverse($buf, true), true), (int)array_search("\t", array_reverse($buf, true), true)) : 0;
                $hasBlank = $spaces && (in_array(' ', $buf, true) || in_array("\t", $buf, true));
                $take = $hasBlank ? $cut + 1 : count($buf);
                $out .= implode('', array_slice($buf, 0, $take)) . "\n";
                $buf = array_slice($buf, $take);
                $col = 0;
                foreach ($buf as $b) $col = $adv($col, $b);
            }
            $buf[] = $ch;
            $col = $adv($col, $ch);
        }
        $out .= implode('', $buf) . ($li < $last ? "\n" : '');
    }
    return $out;
}

function lab58_cmd_fold(Lab57Proc $p, array $argv): int
{
    $args = array_map(static fn(string $a): string => preg_match('/^-\d+$/', $a) === 1 ? '-w' . substr($a, 1) : $a, array_slice($argv, 1));
    [$o, $files, $error] = lab57_getopt($args, 'bsw:', ['bytes' => false, 'spaces' => false, 'width' => true]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    $width = (string)($o['w'] ?? $o['--width'] ?? '80');
    if (preg_match('/^\d{1,6}$/', $width) !== 1 || (int)$width < 1) { $p->err("fold: invalid number of columns: ‘{$width}’\n"); return 1; }
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    foreach ($inputs as [, $content]) $p->out(lab58_x_fold($content, (int)$width, isset($o['s']) || isset($o['--spaces']), isset($o['b']) || isset($o['--bytes'])));
    return $status;
}

// ---------------------------------------------------------------------------
// comm, join (seřazené vstupy; kontrola pořadí jako GNU – hlásí se jen při nespárovaných řádcích)
// ---------------------------------------------------------------------------

function lab58_x_two_inputs(Lab57Proc $p, array $ops): ?array
{
    if (count($ops) < 2) {
        $p->err($p->name . ': missing operand' . ($ops === [] ? '' : ' after ‘' . $ops[0] . '’') . "\nTry '" . $p->name . " --help' for more information.\n");
        $p->w->tip(tr('{name} porovnává dva seřazené soubory: {name} a.txt b.txt (seřadíš je příkazem sort). → man {name}', ['name' => $p->name]));
        return null;
    }
    if (count($ops) > 2) { lab58_x_extra($p, $ops[2]); return null; }
    [$inputs, $status] = lab57_read_inputs($p, $ops);
    return $status !== 0 || count($inputs) < 2 ? null : [lab57_split_lines($inputs[0][1])[0], lab57_split_lines($inputs[1][1])[0]];
}

function lab58_cmd_comm(Lab57Proc $p, array $argv): int
{
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), '123z', ['check-order' => false, 'nocheck-order' => false, 'output-delimiter' => true, 'total' => false, 'zero-terminated' => false]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    $files = lab58_x_two_inputs($p, $ops);
    if ($files === null) return 1;
    $show = [1 => !isset($o['1']), 2 => !isset($o['2']), 3 => !isset($o['3'])];
    $delim = (string)($o['--output-delimiter'] ?? "\t");
    $prefix = [1 => '', 2 => $show[1] ? $delim : '', 3 => ($show[1] ? $delim : '') . ($show[2] ? $delim : '')];
    $mode = isset($o['--nocheck-order']) ? 'off' : (isset($o['--check-order']) ? 'on' : 'default');
    $pos = [0, 0];
    $unpaired = false;
    $warned = [false, false];
    $total = [1 => 0, 2 => 0, 3 => 0];
    $out = '';
    $advance = static function (int $f) use (&$pos, &$warned, &$unpaired, $files, $mode, $p, &$out): void {
        $prev = $files[$f][$pos[$f]];
        $pos[$f]++;
        $next = $files[$f][$pos[$f]] ?? null;
        if ($next === null || $warned[$f] || $mode === 'off' || ($mode === 'default' && !$unpaired) || strcmp($prev, $next) <= 0) return;
        $p->out($out);
        $out = '';
        $p->err('comm: file ' . ($f + 1) . " is not in sorted order\n");
        $warned[$f] = true;
    };
    while ($pos[0] < count($files[0]) || $pos[1] < count($files[1])) {
        $a = $files[0][$pos[0]] ?? null;
        $b = $files[1][$pos[1]] ?? null;
        $c = $a === null ? 1 : ($b === null ? -1 : strcmp($a, $b));
        $col = $c < 0 ? 1 : ($c > 0 ? 2 : 3);
        if ($col !== 3) $unpaired = true;
        $total[$col]++;
        if ($show[$col]) $out .= $prefix[$col] . ($col === 2 ? $b : $a) . "\n";
        if ($col !== 2) $advance(0);
        if ($col !== 1) $advance(1);
    }
    if (isset($o['--total'])) $out .= $total[1] . $delim . $total[2] . $delim . $total[3] . $delim . "total\n";
    $p->out($out);
    if ($warned[0] || $warned[1]) {
        $p->err("comm: input is not in sorted order\n");
        $p->w->tip(tr('comm potřebuje seřazené vstupy. Seřaď je: sort a.txt > a2.txt (nebo comm <(sort a) <(sort b) v bashi). → man comm'));
        return 1;
    }
    return 0;
}

function lab58_cmd_join(Lab57Proc $p, array $argv): int
{
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), '1:2:j:t:a:v:ie:o:', ['ignore-case' => false, 'check-order' => false, 'nocheck-order' => false, 'zero-terminated' => false]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    foreach (['1', '2', 'j', 'a', 'v'] as $key) {
        if (isset($o[$key]) && (preg_match('/^\d{1,3}$/', (string)$o[$key]) !== 1 || (int)$o[$key] < 1 || (in_array($key, ['a', 'v'], true) && (int)$o[$key] > 2))) {
            $p->err('join: invalid ' . (in_array($key, ['a', 'v'], true) ? 'file' : 'field') . ' number: ‘' . $o[$key] . "’\n");
            return 1;
        }
    }
    $t = isset($o['t']) ? (string)$o['t'] : null;
    if ($t !== null && mb_strlen($t) > 1) { $p->err("join: multi-character tab ‘{$t}’\n"); return 1; }
    $files = lab58_x_two_inputs($p, $ops);
    if ($files === null) return 1;
    $field = [(int)($o['1'] ?? $o['j'] ?? 1) - 1, (int)($o['2'] ?? $o['j'] ?? 1) - 1];
    $icase = isset($o['i']) || isset($o['--ignore-case']);
    $only = isset($o['v']) ? (int)$o['v'] : null;
    $also = isset($o['a']) ? (int)$o['a'] : null;
    $rows = [];
    foreach ($files as $f => $lines) {
        foreach ($lines as $no => $line) {
            $cells = $t === null ? (preg_split('/[ \t]+/', trim($line, " \t"), -1, PREG_SPLIT_NO_EMPTY) ?: []) : explode($t, $line);
            $rows[$f][] = ['key' => (string)($cells[$field[$f]] ?? ''), 'cells' => $cells, 'line' => $line, 'no' => $no + 1];
        }
        $rows[$f] ??= [];
    }
    $sep = $t ?? ' ';
    $cmp = static fn(string $a, string $b): int => $icase ? strcasecmp($a, $b) : strcmp($a, $b);
    $emit = static function (?array $r1, ?array $r2) use ($field, $sep, $o): string {
        $key = $r1['key'] ?? $r2['key'];
        if (isset($o['o']) && $o['o'] !== 'auto') {
            $parts = [];
            foreach (preg_split('/[, ]+/', (string)$o['o']) ?: [] as $spec) {
                if ($spec === '0') { $parts[] = $key; continue; }
                [$fn, $col] = array_map('intval', explode('.', $spec . '.0'));
                $row = $fn === 1 ? $r1 : $r2;
                $parts[] = (string)($row['cells'][$col - 1] ?? ($o['e'] ?? ''));
            }
            return implode($sep, $parts);
        }
        $parts = [$key];
        foreach ([[$r1, 0], [$r2, 1]] as [$row, $f]) foreach ((array)($row['cells'] ?? []) as $i => $c) if ($i !== $field[$f]) $parts[] = $c;
        return implode($sep, $parts);
    };
    $out = '';
    $i = $j = 0;
    $unpaired = false;
    $warned = false;
    $check = static function (int $f, int $idx) use (&$rows, &$warned, &$unpaired, $o, $cmp, $p, &$out, $ops): void {
        if ($warned || isset($o['--nocheck-order']) || (!isset($o['--check-order']) && !$unpaired) || $idx < 1 || !isset($rows[$f][$idx])) return;
        if ($cmp($rows[$f][$idx - 1]['key'], $rows[$f][$idx]['key']) <= 0) return;
        $p->out($out);
        $out = '';
        $p->err('join: ' . $ops[$f] . ':' . $rows[$f][$idx]['no'] . ': is not sorted: ' . $rows[$f][$idx]['line'] . "\n");
        $warned = true;
    };
    while ($i < count($rows[0]) || $j < count($rows[1])) {
        $c = $i >= count($rows[0]) ? 1 : ($j >= count($rows[1]) ? -1 : $cmp($rows[0][$i]['key'], $rows[1][$j]['key']));
        if ($c !== 0) {
            $unpaired = true;
            $f = $c < 0 ? 0 : 1;
            $row = $rows[$f][$f === 0 ? $i : $j];
            if ($only === $f + 1 || $also === $f + 1) $out .= $emit($f === 0 ? $row : null, $f === 1 ? $row : null) . "\n";
            if ($f === 0) $check(0, ++$i); else $check(1, ++$j);
            continue;
        }
        $i2 = $i;
        $j2 = $j;
        while ($i2 < count($rows[0]) && $cmp($rows[0][$i2]['key'], $rows[0][$i]['key']) === 0) $i2++;
        while ($j2 < count($rows[1]) && $cmp($rows[1][$j2]['key'], $rows[1][$j]['key']) === 0) $j2++;
        if ($only === null) for ($a = $i; $a < $i2; $a++) for ($b = $j; $b < $j2; $b++) $out .= $emit($rows[0][$a], $rows[1][$b]) . "\n";
        [$i, $j] = [$i2, $j2];
        $check(0, $i);
        $check(1, $j);
    }
    $p->out($out);
    if ($warned) {
        $p->err("join: input is not in sorted order\n");
        $p->w->tip(tr('join potřebuje oba soubory seřazené podle spojovacího pole (sort -k1). → man join'));
        return 1;
    }
    return 0;
}

// ---------------------------------------------------------------------------
// split, yes
// ---------------------------------------------------------------------------

function lab58_x_size(string $s): ?int
{
    if (preg_match('/^(\d{1,12})([kKmMgGtT](?:iB|B)?|b)?$/', $s, $m) !== 1) return null;
    $suf = (string)($m[2] ?? '');
    if ($suf === '' || $suf === 'b') return (int)$m[1] * ($suf === 'b' ? 512 : 1);
    $base = str_ends_with($suf, 'B') && !str_ends_with($suf, 'iB') ? 1000 : 1024;
    return (int)$m[1] * $base ** ((int)strpos('kmgt', strtolower($suf[0])) + 1);
}

function lab58_cmd_split(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $long = ['lines' => true, 'bytes' => true, 'number' => true, 'numeric-suffixes' => false, 'suffix-length' => true, 'additional-suffix' => true, 'verbose' => false];
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'l:b:n:da:', $long);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if (count($ops) > 2) return lab58_x_extra($p, $ops[2]);
    $lines = $o['l'] ?? $o['--lines'] ?? null;
    $bytes = $o['b'] ?? $o['--bytes'] ?? null;
    $number = $o['n'] ?? $o['--number'] ?? null;
    if (count(array_filter([$lines, $bytes, $number], static fn($v): bool => $v !== null)) > 1) { $p->err("split: cannot split in more than one way\nTry 'split --help' for more information.\n"); return 1; }
    if ($lines !== null && (preg_match('/^\d{1,9}$/', (string)$lines) !== 1 || (int)$lines < 1)) { $p->err("split: invalid number of lines: ‘{$lines}’\n"); return 1; }
    $size = $bytes !== null ? lab58_x_size((string)$bytes) : null;
    if ($bytes !== null && ($size === null || $size < 1)) { $p->err("split: invalid number of bytes: ‘{$bytes}’\n"); return 1; }
    if ($number !== null && (preg_match('/^(l\/)?(\d{1,6})$/', (string)$number, $nm) !== 1 || (int)$nm[2] < 1)) { $p->err("split: invalid number of chunks: ‘{$number}’\n"); return 1; }
    $input = $ops[0] ?? '-';
    if ($input === '-' && !$p->hasStdin) { lab57_needs_input($p, []); return 0; }
    [$inputs, $status] = lab57_read_inputs($p, [$input]);
    if ($status !== 0) return 1;
    $data = (string)$inputs[0][1];
    $chunks = [];
    if ($size !== null) {
        $chunks = $data === '' ? [] : str_split($data, $size);
    } elseif ($number !== null) {
        $n = (int)$nm[2];
        $len = strlen($data);
        $start = 0;
        for ($k = 1; $k <= $n; $k++) {
            $end = $k === $n ? $len : intdiv($len * $k, $n);
            if ($nm[1] !== '' && $end > $start && $end < $len) { $nl = strpos($data, "\n", max($start, $end - 1)); $end = $nl === false ? $len : $nl + 1; }
            $chunks[] = substr($data, $start, max(0, $end - $start));
            $start = max($start, $end);
        }
    } else {
        foreach (array_chunk(lab57_split_lines($data)[0], (int)($lines ?? 1000)) as $group) $chunks[] = lab57_join($group);
        if ($data !== '' && !str_ends_with($data, "\n") && $chunks !== []) $chunks[count($chunks) - 1] = substr($chunks[count($chunks) - 1], 0, -1);
    }
    $numeric = isset($o['d']) || isset($o['--numeric-suffixes']);
    $slen = max(1, min(6, (int)($o['a'] ?? $o['--suffix-length'] ?? 2)));
    $max = min(676, ($numeric ? 10 : 26) ** $slen);
    $prefix = $ops[1] ?? 'x';
    foreach ($chunks as $k => $chunk) {
        if ($k >= $max) {
            $p->err("split: output file suffixes exhausted\n");
            $w->tip(tr('Výstupních souborů by bylo moc (simulace jich vytvoří nejvýš {max}). Zvětši kusy: split -l 500 nebo -b 1M. → man split', ['max' => $max]));
            return 1;
        }
        $suffix = '';
        for ($v = $k, $d = 0; $d < $slen; $d++, $v = intdiv($v, $numeric ? 10 : 26)) $suffix = ($numeric ? (string)($v % 10) : chr(97 + $v % 26)) . $suffix;
        $name = $prefix . $suffix . (string)($o['--additional-suffix'] ?? '');
        if (isset($o['--verbose'])) $p->line("creating file '$name'");
        $err = null;
        if (!$w->writeFile($name, $chunk, false, $err)) { $p->err("split: $name: $err\n"); lab57_error_tip($w, (string)$err, $name); return 1; }
    }
    return 0;
}

function lab58_cmd_yes(Lab57Proc $p, array $argv): int
{
    $args = array_slice($argv, 1);
    if (isset($args[0]) && preg_match('/^-[^-]|^--./', $args[0]) === 1 && $args[0] !== '--') return lab57_opt_error($p, str_starts_with($args[0], '--') ? "unrecognized option '{$args[0]}'" : "invalid option -- '" . $args[0][1] . "'", 1);
    if (($args[0] ?? '') === '--') array_shift($args);
    $text = $args === [] ? 'y' : implode(' ', $args);
    $lines = max(1, min($p->tty ? 2000 : 10000, intdiv(131072, strlen($text) + 1)));
    $p->out(str_repeat($text . "\n", $lines));
    if ($p->tty) $p->w->tip(tr('yes by psal donekonečna (do Ctrl+C) – simulace skončila po {n} řádcích. Typicky se posílá rourou: yes | příkaz.', ['n' => $lines]));
    return 0;
}

foreach (['basename', 'dirname', 'realpath', 'readlink', 'seq', 'shuf', 'paste', 'column', 'fold', 'comm', 'join', 'split', 'yes'] as $lab58XName) {
    lab58_register_command($lab58XName, 'lab58_cmd_' . $lab58XName);
}
unset($lab58XName);
