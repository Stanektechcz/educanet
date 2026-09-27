<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – práce s textem a daty (grep, sed, awk, sort, base64 …).
 */

function lab57_cmds_text(): array
{
    return [
        'grep' => 'lab57_cmd_grep', 'egrep' => 'lab57_cmd_grep', 'fgrep' => 'lab57_cmd_grep', 'wc' => 'lab57_cmd_wc',
        'sort' => 'lab57_cmd_sort', 'uniq' => 'lab57_cmd_uniq', 'cut' => 'lab57_cmd_cut', 'tr' => 'lab57_cmd_tr',
        'sed' => 'lab57_cmd_sed', 'awk' => 'lab57_cmd_awk', 'diff' => 'lab57_cmd_diff', 'rev' => 'lab57_cmd_rev',
        'nl' => 'lab57_cmd_nl', 'tee' => 'lab57_cmd_tee', 'base64' => 'lab57_cmd_base64', 'xxd' => 'lab57_cmd_xxd',
        'strings' => 'lab57_cmd_strings', 'md5sum' => 'lab57_cmd_hashsum', 'sha256sum' => 'lab57_cmd_hashsum',
        'sha1sum' => 'lab57_cmd_hashsum',
    ];
}

/** @return array{0:list<string>,1:bool} řádky a zda text končil \n */
function lab57_split_lines(string $content): array
{
    if ($content === '') return [[], false];
    $lines = explode("\n", $content);
    $endsNl = end($lines) === '';
    if ($endsNl) array_pop($lines);
    return [$lines, $endsNl];
}

// ---------------------------------------------------------------------------
// grep
// ---------------------------------------------------------------------------

function lab57_cmd_grep(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $patterns = [];
    $rest = [];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '-e') { $patterns[] = (string)($args[++$i] ?? ''); continue; }
        if (str_starts_with($a, '-e') && strlen($a) > 2 && !str_starts_with($a, '--')) { $patterns[] = substr($a, 2); continue; }
        if (str_starts_with($a, '--regexp=')) { $patterns[] = substr($a, 9); continue; }
        $rest[] = $a;
    }
    [$o, $ops, $error] = lab57_getopt($rest, 'ivncrRlLwoEFHhsqxA:B:C:m:', ['color' => false, 'colour' => false, 'ignore-case' => false, 'invert-match' => false, 'count' => false, 'recursive' => false, 'line-number' => false, 'include' => true, 'exclude' => true]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($patterns === []) {
        if ($ops === []) { $p->err("Usage: grep [OPTION]... PATTERNS [FILE]...\nTry 'grep --help' for more information.\n"); return 2; }
        $patterns = [(string)array_shift($ops)];
    }
    $flavor = isset($o['F']) || $argv[0] === 'fgrep' ? 'fixed' : ((isset($o['E']) || $argv[0] === 'egrep') ? 'ere' : 'bre');
    $icase = isset($o['i']) || isset($o['--ignore-case']);
    $regexes = [];
    foreach ($patterns as $pattern) {
        foreach (explode("\n", $pattern) as $single) {
            $regex = lab57_regex($single, $flavor, $icase, isset($o['w']), isset($o['x']));
            if ($regex === null) { $p->err("grep: Invalid regular expression\n"); return 2; }
            $regexes[] = $regex;
        }
    }
    $opt = [
        'invert' => isset($o['v']) || isset($o['--invert-match']),
        'number' => isset($o['n']) || isset($o['--line-number']),
        'count' => isset($o['c']) || isset($o['--count']),
        'list' => isset($o['l']),
        'nolist' => isset($o['L']),
        'only' => isset($o['o']),
        'quiet' => isset($o['q']),
        'silent' => isset($o['s']),
        'max' => isset($o['m']) ? max(0, (int)$o['m']) : PHP_INT_MAX,
        'after' => (int)($o['A'] ?? $o['C'] ?? 0),
        'before' => (int)($o['B'] ?? $o['C'] ?? 0),
        'color' => $p->tty,
        'regexes' => $regexes,
    ];
    $recursive = isset($o['r']) || isset($o['R']) || isset($o['--recursive']);
    if ($ops === [] && $recursive) $ops = ['.'];
    if ($ops === [] && lab57_needs_input($p, $ops)) return 2;
    $inputs = [];
    $status = 0;
    if ($ops === []) {
        $inputs[] = ['(standard input)', $p->stdin];
    } else {
        foreach ($ops as $file) {
            $abs = $w->abs($file);
            $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
            if ($node === null) {
                if (!$opt['silent']) $p->err("grep: $file: No such file or directory\n");
                if (!$opt['silent']) lab57_error_tip($w, 'No such file or directory', $file);
                $status = 2;
                continue;
            }
            if (($node['t'] ?? '') === 'd') {
                if (!$recursive) { if (!$opt['silent']) $p->err("grep: $file: Is a directory\n"); continue; }
                lab57_grep_collect($p, $abs, $file, $inputs, $opt, isset($o['--include']) ? (string)$o['--include'] : null, $status);
                continue;
            }
            if (!$w->can($node, 'r')) { if (!$opt['silent']) $p->err("grep: $file: Permission denied\n"); $status = 2; continue; }
            $inputs[] = [$file, (string)($node['c'] ?? '')];
        }
    }
    $showName = isset($o['H']) || ((count($ops) > 1 || $recursive) && !isset($o['h']));
    $any = false;
    foreach ($inputs as [$name, $content]) {
        if (lab57_grep_one($p, $name, $content, $opt, $showName)) $any = true;
        if ($any && $opt['quiet']) return 0;
    }
    if ($status === 2 && !$any) return 2;
    return $any ? 0 : ($status === 2 ? 2 : 1);
}

function lab57_grep_collect(Lab57Proc $p, string $abs, string $disp, array &$inputs, array $opt, ?string $include, int &$status): void
{
    $w = $p->w;
    $node = $w->fs->get($abs);
    if ($node === null) return;
    if (($node['t'] ?? '') !== 'd') {
        if ($include !== null && !lab57_glob_match($include, Lab57Vfs::basename($abs))) return;
        if (!$w->can($node, 'r')) { if (!$opt['silent']) $p->err("grep: $disp: Permission denied\n"); $status = 2; return; }
        $inputs[] = [$disp, (string)($node['c'] ?? '')];
        return;
    }
    if (!$w->can($node, 'r') || !$w->can($node, 'x')) { if (!$opt['silent']) $p->err("grep: $disp: Permission denied\n"); $status = 2; return; }
    foreach ($w->fs->children($abs) as $name) {
        $childDisp = ($disp === '/' ? '' : rtrim($disp, '/')) . '/' . $name;
        if ($disp === '.') $childDisp = './' . $name;
        lab57_grep_collect($p, ($abs === '/' ? '' : $abs) . '/' . $name, $childDisp, $inputs, $opt, $include, $status);
    }
}

function lab57_grep_matches(string $line, array $regexes): bool
{
    foreach ($regexes as $regex) if (@preg_match($regex, $line) === 1) return true;
    return false;
}

function lab57_grep_one(Lab57Proc $p, string $name, string $content, array $opt, bool $showName): bool
{
    [$lines] = lab57_split_lines($content);
    $binary = !lab57_is_text($content);
    $hits = [];
    foreach ($lines as $i => $line) {
        $match = lab57_grep_matches($line, $opt['regexes']);
        if ($match !== $opt['invert']) {
            $hits[] = $i;
            if (count($hits) >= $opt['max']) break;
        }
    }
    $found = $hits !== [];
    if ($opt['quiet']) return $found;
    $nameColor = static fn(string $n): string => $opt['color'] ? "\e[35m" . $n . "\e[0m" : $n;
    if ($opt['list']) { if ($found) $p->line($nameColor($name)); return $found; }
    if ($opt['nolist']) { if (!$found) $p->line($nameColor($name)); return $found; }
    if ($opt['count']) { $p->line(($showName ? $nameColor($name) . ':' : '') . count($hits)); return $found; }
    if (!$found) return false;
    if ($binary) { $p->line('grep: ' . $name . ': binary file matches'); return true; }
    $prefix = static function (int $i, string $sep) use ($opt, $showName, $name, $nameColor): string {
        $out = $showName ? $nameColor($name) . $sep : '';
        if ($opt['number']) $out .= ($opt['color'] ? "\e[32m" . ($i + 1) . "\e[0m" : (string)($i + 1)) . $sep;
        return $out;
    };
    $highlight = static function (string $line) use ($opt): string {
        if (!$opt['color'] || $opt['invert']) return $line;
        foreach ($opt['regexes'] as $regex) {
            $line = (string)(@preg_replace_callback($regex, static fn(array $m): string => $m[0] === '' ? '' : "\e[01;31m" . $m[0] . "\e[0m", $line) ?? $line);
        }
        return $line;
    };
    if ($opt['only']) {
        foreach ($hits as $i) {
            foreach ($opt['regexes'] as $regex) {
                if (@preg_match_all($regex, $lines[$i], $m) > 0) {
                    foreach ($m[0] as $piece) if ($piece !== '') $p->line($prefix($i, ':') . ($opt['color'] ? "\e[01;31m" . $piece . "\e[0m" : $piece));
                }
            }
        }
        return true;
    }
    $show = [];
    foreach ($hits as $i) {
        for ($k = max(0, $i - $opt['before']); $k <= min(count($lines) - 1, $i + $opt['after']); $k++) $show[$k] = $show[$k] ?? ($k === $i);
        $show[$i] = true;
    }
    ksort($show);
    $last = null;
    foreach ($show as $i => $isHit) {
        if ($last !== null && $i > $last + 1 && ($opt['after'] > 0 || $opt['before'] > 0)) $p->line($opt['color'] ? "\e[36m--\e[0m" : '--');
        $p->line($prefix($i, $isHit ? ':' : '-') . ($isHit ? $highlight($lines[$i]) : $lines[$i]));
        $last = $i;
    }
    return true;
}

// ---------------------------------------------------------------------------
// wc, sort, uniq, cut, tr
// ---------------------------------------------------------------------------

function lab57_cmd_wc(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'lwcmL', ['lines' => false, 'words' => false, 'bytes' => false, 'chars' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    $want = [];
    if (isset($o['l']) || isset($o['--lines'])) $want[] = 'l';
    if (isset($o['w']) || isset($o['--words'])) $want[] = 'w';
    if (isset($o['m']) || isset($o['--chars'])) $want[] = 'm';
    if (isset($o['c']) || isset($o['--bytes'])) $want[] = 'c';
    if (isset($o['L'])) $want[] = 'L';
    if ($want === []) $want = ['l', 'w', 'c'];
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $rows = [];
    $total = ['l' => 0, 'w' => 0, 'm' => 0, 'c' => 0, 'L' => 0];
    foreach ($inputs as [$name, $content]) {
        $counts = [
            'l' => substr_count($content, "\n"),
            'w' => count(preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: []),
            'm' => mb_strlen($content, 'UTF-8'),
            'c' => strlen($content),
            'L' => $content === '' ? 0 : max(array_map(static fn(string $l): int => mb_strlen($l), explode("\n", $content))),
        ];
        foreach ($counts as $k => $v) $total[$k] = $k === 'L' ? max($total[$k], $v) : $total[$k] + $v;
        $rows[] = [$name, $counts];
    }
    if (count($rows) > 1) $rows[] = ['total', $total];
    $single = count($want) === 1 && count($rows) === 1;
    $width = 1;
    if (!$single) {
        $max = 0;
        foreach ($rows as [, $counts]) foreach ($want as $k) $max = max($max, $counts[$k]);
        $width = ($files === [] || $files === ['-']) ? 7 : max(1, strlen((string)$max));
    }
    foreach ($rows as [$name, $counts]) {
        $cols = [];
        foreach ($want as $k) $cols[] = $single ? (string)$counts[$k] : str_pad((string)$counts[$k], $width, ' ', STR_PAD_LEFT);
        $p->line(implode(' ', $cols) . ($name === '-' ? '' : ' ' . $name));
    }
    return $status;
}

function lab57_sort_key(string $line, array $o): string
{
    if (!isset($o['k'])) return $line;
    preg_match('/^(\d+)(?:\.\d+)?[a-zA-Z]*(?:,(\d+))?/', (string)$o['k'], $m);
    $from = max(1, (int)($m[1] ?? 1));
    $to = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : null;
    if (isset($o['t'])) {
        $fields = explode((string)$o['t'], $line);
    } else {
        $fields = preg_split('/\s+/', trim($line)) ?: [];
    }
    $slice = array_slice($fields, $from - 1, $to === null ? null : max(1, $to - $from + 1));
    return implode(isset($o['t']) ? (string)$o['t'] : ' ', $slice);
}

function lab57_human_value(string $text): float
{
    if (preg_match('/^\s*(-?[\d.]+)\s*([KMGT]?)/i', $text, $m) !== 1) return 0.0;
    $mult = match (strtoupper($m[2])) { 'K' => 1024, 'M' => 1048576, 'G' => 1073741824, 'T' => 1099511627776, default => 1 };
    return (float)$m[1] * $mult;
}

function lab57_cmd_sort(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'nrufhk:t:bV', ['numeric-sort' => false, 'reverse' => false, 'unique' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $lines = [];
    foreach ($inputs as [, $content]) foreach (lab57_split_lines($content)[0] as $line) $lines[] = $line;
    $numeric = isset($o['n']) || isset($o['--numeric-sort']);
    $human = isset($o['h']);
    $fold = isset($o['f']);
    $cmp = static function (string $a, string $b) use ($o, $numeric, $human, $fold): int {
        $ka = lab57_sort_key($a, $o);
        $kb = lab57_sort_key($b, $o);
        if ($numeric || $human) {
            $na = $human ? lab57_human_value($ka) : (preg_match('/^\s*(-?\d+(?:\.\d+)?)/', $ka, $m) === 1 ? (float)$m[1] : 0.0);
            $nb = $human ? lab57_human_value($kb) : (preg_match('/^\s*(-?\d+(?:\.\d+)?)/', $kb, $m2) === 1 ? (float)$m2[1] : 0.0);
            if ($na !== $nb) return $na <=> $nb;
        } else {
            $d = $fold ? strcmp(mb_strtolower($ka), mb_strtolower($kb)) : strcmp($ka, $kb);
            if ($d !== 0) return $d;
        }
        return strcmp($a, $b);
    };
    usort($lines, $cmp);
    if (isset($o['r']) || isset($o['--reverse'])) $lines = array_reverse($lines);
    if (isset($o['u']) || isset($o['--unique'])) {
        $unique = [];
        $prev = null;
        foreach ($lines as $line) {
            $key = lab57_sort_key($line, $o);
            if ($prev !== null && ($numeric ? (float)$key === (float)$prev : ($fold ? mb_strtolower($key) === mb_strtolower($prev) : $key === $prev))) continue;
            $unique[] = $line;
            $prev = $key;
        }
        $lines = $unique;
    }
    $p->out(lab57_join($lines));
    return $status;
}

function lab57_cmd_uniq(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'cudi', ['count' => false, 'unique' => false, 'repeated' => false, 'ignore-case' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, array_slice($files, 0, 1));
    [$lines] = lab57_split_lines((string)($inputs[0][1] ?? ''));
    $icase = isset($o['i']) || isset($o['--ignore-case']);
    $groups = [];
    foreach ($lines as $line) {
        $last = count($groups) - 1;
        $same = $last >= 0 && ($icase ? mb_strtolower($groups[$last][0]) === mb_strtolower($line) : $groups[$last][0] === $line);
        if ($same) $groups[$last][1]++;
        else $groups[] = [$line, 1];
    }
    $out = [];
    foreach ($groups as [$line, $count]) {
        if ((isset($o['u']) || isset($o['--unique'])) && $count > 1) continue;
        if ((isset($o['d']) || isset($o['--repeated'])) && $count < 2) continue;
        $out[] = (isset($o['c']) || isset($o['--count'])) ? str_pad((string)$count, 7, ' ', STR_PAD_LEFT) . ' ' . $line : $line;
    }
    $p->out(lab57_join($out));
    return $status;
}

/** Seznam polí "1,3-5,-2,7-" → funkce, která řekne, zda číslo patří do výběru. */
function lab57_cut_list(string $list): ?array
{
    $ranges = [];
    foreach (explode(',', $list) as $part) {
        if (preg_match('/^(\d*)-(\d*)$/', $part, $m) === 1) {
            if ($m[1] === '' && $m[2] === '') return null;
            $ranges[] = [$m[1] === '' ? 1 : (int)$m[1], $m[2] === '' ? PHP_INT_MAX : (int)$m[2]];
        } elseif (ctype_digit($part) && (int)$part > 0) {
            $ranges[] = [(int)$part, (int)$part];
        } else {
            return null;
        }
    }
    return $ranges;
}

function lab57_in_ranges(int $n, array $ranges): bool
{
    foreach ($ranges as [$a, $b]) if ($n >= $a && $n <= $b) return true;
    return false;
}

function lab57_cmd_cut(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'd:f:c:b:s', ['delimiter' => true, 'fields' => true, 'characters' => true, 'output-delimiter' => true]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $fields = $o['f'] ?? $o['--fields'] ?? null;
    $chars = $o['c'] ?? $o['b'] ?? $o['--characters'] ?? null;
    if ($fields === null && $chars === null) { $p->err("cut: you must specify a list of bytes, characters, or fields\nTry 'cut --help' for more information.\n"); return 1; }
    $ranges = lab57_cut_list((string)($fields ?? $chars));
    if ($ranges === null) { $p->err("cut: invalid field value ‘" . ($fields ?? $chars) . "’\n"); return 1; }
    $delim = (string)($o['d'] ?? $o['--delimiter'] ?? "\t");
    if ($fields !== null && mb_strlen($delim) !== 1) { $p->err("cut: the delimiter must be a single character\nTry 'cut --help' for more information.\n"); return 1; }
    $outDelim = (string)($o['--output-delimiter'] ?? $delim);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $out = [];
    foreach ($inputs as [, $content]) {
        foreach (lab57_split_lines($content)[0] as $line) {
            if ($chars !== null) {
                $chunk = '';
                $len = mb_strlen($line);
                for ($i = 1; $i <= $len; $i++) if (lab57_in_ranges($i, $ranges)) $chunk .= mb_substr($line, $i - 1, 1);
                $out[] = $chunk;
                continue;
            }
            if (!str_contains($line, $delim)) { if (!isset($o['s'])) $out[] = $line; continue; }
            $parts = explode($delim, $line);
            $sel = [];
            foreach ($parts as $i => $part) if (lab57_in_ranges($i + 1, $ranges)) $sel[] = $part;
            $out[] = implode($outDelim, $sel);
        }
    }
    $p->out(lab57_join($out));
    return $status;
}

/** Rozbalí množinu znaků pro tr: rozsahy a-z, třídy [:upper:], escape \n \t. */
function lab57_tr_set(string $set): array
{
    $classes = [
        '[:upper:]' => 'A-Z', '[:lower:]' => 'a-z', '[:digit:]' => '0-9', '[:alpha:]' => 'A-Za-z',
        '[:alnum:]' => 'A-Za-z0-9', '[:space:]' => " \t\n\r\x0B\x0C", '[:blank:]' => " \t", '[:punct:]' => '!-/:-@[-`{-~',
    ];
    $set = strtr($set, $classes);
    $set = strtr($set, ['\\n' => "\n", '\\t' => "\t", '\\\\' => '\\', '\\r' => "\r"]);
    $chars = preg_split('//u', $set, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $out = [];
    $n = count($chars);
    for ($i = 0; $i < $n; $i++) {
        if ($i + 2 < $n && $chars[$i + 1] === '-') {
            $from = mb_ord($chars[$i]);
            $to = mb_ord($chars[$i + 2]);
            if ($from !== false && $to !== false && $from <= $to && $to - $from < 2000) {
                for ($c = $from; $c <= $to; $c++) $out[] = (string)mb_chr($c);
                $i += 2;
                continue;
            }
        }
        $out[] = $chars[$i];
    }
    return $out;
}

function lab57_cmd_tr(Lab57Proc $p, array $argv): int
{
    [$o, $sets, $error] = lab57_getopt(array_slice($argv, 1), 'dscC', ['delete' => false, 'squeeze-repeats' => false, 'complement' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $delete = isset($o['d']) || isset($o['--delete']);
    $squeeze = isset($o['s']) || isset($o['--squeeze-repeats']);
    $complement = isset($o['c']) || isset($o['C']) || isset($o['--complement']);
    if ($sets === []) { $p->err("tr: missing operand\nTry 'tr --help' for more information.\n"); return 1; }
    if (!$delete && !$squeeze && count($sets) < 2) { $p->err("tr: missing operand after ‘{$sets[0]}’\nTwo strings must be given when translating.\nTry 'tr --help' for more information.\n"); return 1; }
    if (!$p->hasStdin) { $p->w->tip(tr('tr čte jen ze vstupu (roury). Použij třeba: cat soubor | tr a-z A-Z')); return 0; }
    $set1 = lab57_tr_set((string)$sets[0]);
    $set2 = isset($sets[1]) ? lab57_tr_set((string)$sets[1]) : [];
    $in1 = array_fill_keys($set1, true);
    $chars = preg_split('//u', $p->stdin, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($p->stdin);
    $map = [];
    if (!$delete && $set2 !== []) {
        $lastChar = end($set2);
        foreach ($set1 as $i => $ch) $map[$ch] = $set2[$i] ?? $lastChar;
    }
    $squeezeSet = array_fill_keys($delete || $set2 === [] ? $set1 : $set2, true);
    $out = '';
    $prev = null;
    foreach ($chars as $ch) {
        $member = isset($in1[$ch]) !== $complement;
        if ($delete && $member) continue;
        if (!$delete && $member && isset($map[$ch])) $ch = $map[$ch];
        elseif (!$delete && $member && $complement && $set2 !== []) $ch = (string)end($set2);
        if ($squeeze && $prev === $ch && (isset($squeezeSet[$ch]) !== ($complement && ($delete || $set2 === [])))) continue;
        $out .= $ch;
        $prev = $ch;
    }
    $p->out($out);
    return 0;
}

// ---------------------------------------------------------------------------
// sed (s///, p, d, q, y, adresy N, N,M, $, /re/)
// ---------------------------------------------------------------------------

function lab57_sed_parse(string $script, bool $ere): array
{
    $cmds = [];
    $i = 0;
    $n = strlen($script);
    $readAddr = static function () use (&$i, $script, $n, $ere): ?array {
        if ($i >= $n) return null;
        if (ctype_digit($script[$i])) {
            $j = $i;
            while ($j < $n && ctype_digit($script[$j])) $j++;
            $addr = ['line', (int)substr($script, $i, $j - $i)];
            $i = $j;
            return $addr;
        }
        if ($script[$i] === '$') { $i++; return ['last']; }
        if ($script[$i] === '/') {
            $end = $i + 1;
            while ($end < $n && ($script[$end] !== '/' || $script[$end - 1] === '\\')) $end++;
            if ($end >= $n) throw new RuntimeException('unterminated address regex');
            $regex = lab57_regex(substr($script, $i + 1, $end - $i - 1), $ere ? 'ere' : 'bre');
            if ($regex === null) throw new RuntimeException('invalid regular expression');
            $i = $end + 1;
            return ['re', $regex];
        }
        return null;
    };
    while ($i < $n) {
        while ($i < $n && (ctype_space($script[$i]) || $script[$i] === ';')) $i++;
        if ($i >= $n) break;
        $a1 = $readAddr();
        $a2 = null;
        if ($a1 !== null && $i < $n && $script[$i] === ',') { $i++; $a2 = $readAddr(); if ($a2 === null) throw new RuntimeException('unexpected `,\''); }
        while ($i < $n && $script[$i] === ' ') $i++;
        $negate = false;
        if ($i < $n && $script[$i] === '!') { $negate = true; $i++; }
        $cmd = $script[$i] ?? '';
        $i++;
        if ($cmd === 's' || $cmd === 'y') {
            $delim = $script[$i] ?? '';
            if ($delim === '' || $delim === '\\' || ctype_space($delim)) throw new RuntimeException('unterminated `' . $cmd . "' command");
            $parts = [];
            $buf = '';
            $i++;
            while ($i < $n && count($parts) < 2) {
                $ch = $script[$i];
                if ($ch === '\\' && $i + 1 < $n) {
                    $buf .= $script[$i + 1] === $delim ? $delim : $ch . $script[$i + 1];
                    $i += 2;
                    continue;
                }
                if ($ch === $delim) { $parts[] = $buf; $buf = ''; $i++; continue; }
                $buf .= $ch;
                $i++;
            }
            if (count($parts) < 2) throw new RuntimeException('unterminated `' . $cmd . "' command");
            $flags = '';
            while ($i < $n && preg_match('/[gpiI0-9]/', $script[$i]) === 1) $flags .= $script[$i++];
            if ($cmd === 'y') {
                $from = preg_split('//u', $parts[0], -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $to = preg_split('//u', $parts[1], -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (count($from) !== count($to)) throw new RuntimeException('strings for `y\' command are different lengths');
                $cmds[] = ['a1' => $a1, 'a2' => $a2, 'neg' => $negate, 'cmd' => 'y', 'map' => array_combine($from, $to)];
                continue;
            }
            $regex = lab57_regex($parts[0], $ere ? 'ere' : 'bre', stripos($flags, 'i') !== false);
            if ($regex === null) throw new RuntimeException('invalid regular expression');
            $replacement = preg_replace_callback('/\\\\(.)|&/', static function (array $m): string {
                if ($m[0] === '&') return '${0}';
                if (ctype_digit($m[1])) return '${' . $m[1] . '}';
                if ($m[1] === 'n') return "\n";
                if ($m[1] === 't') return "\t";
                return $m[1] === '$' ? '\$' : $m[1];
            }, str_replace('$', '\$', $parts[1])) ?? $parts[1];
            preg_match('/\d+/', $flags, $nth);
            $cmds[] = ['a1' => $a1, 'a2' => $a2, 'neg' => $negate, 'cmd' => 's', 'regex' => $regex, 'rep' => $replacement, 'g' => str_contains($flags, 'g'), 'p' => str_contains($flags, 'p'), 'nth' => isset($nth[0]) ? (int)$nth[0] : 1];
            continue;
        }
        if (in_array($cmd, ['p', 'd', 'q', '='], true)) {
            $cmds[] = ['a1' => $a1, 'a2' => $a2, 'neg' => $negate, 'cmd' => $cmd];
            continue;
        }
        throw new RuntimeException($cmd === '' ? 'missing command' : "unknown command: `$cmd'");
    }
    return $cmds;
}

function lab57_sed_addr_match(?array $addr, string $line, int $no, bool $last): bool
{
    if ($addr === null) return true;
    return match ($addr[0]) {
        'line' => $no === $addr[1],
        'last' => $last,
        default => @preg_match($addr[1], $line) === 1,
    };
}

function lab57_cmd_sed(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $scripts = [];
    $files = [];
    $quiet = false;
    $inPlace = false;
    $ere = false;
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '-n' || $a === '--quiet') { $quiet = true; continue; }
        if ($a === '-E' || $a === '-r') { $ere = true; continue; }
        if ($a === '-i' || str_starts_with($a, '-i') && !str_starts_with($a, '--')) { $inPlace = true; continue; }
        if ($a === '-e') { $scripts[] = (string)($args[++$i] ?? ''); continue; }
        if (preg_match('/^-[nEr]+$/', $a) === 1) { if (str_contains($a, 'n')) $quiet = true; if (str_contains($a, 'E') || str_contains($a, 'r')) $ere = true; continue; }
        if (str_starts_with($a, '-') && $a !== '-') return lab57_opt_error($p, "invalid option -- '" . substr($a, 1, 1) . "'", 1);
        if ($scripts === []) { $scripts[] = $a; continue; }
        $files[] = $a;
    }
    if ($scripts === []) { $p->err("Usage: sed [OPTION]... {script-only-if-no-other-script} [input-file]...\n"); return 1; }
    try {
        $cmds = lab57_sed_parse(implode("\n", $scripts), $ere);
    } catch (RuntimeException $e) {
        $p->err('sed: -e expression #1, char 1: ' . $e->getMessage() . "\n");
        return 1;
    }
    if ($inPlace && $files === []) { $p->err("sed: no input files\n"); return 1; }
    if (!$inPlace && lab57_needs_input($p, $files)) return 0;
    $status = 0;
    $groups = $inPlace ? array_map(static fn(string $f): array => [$f], $files) : [$files];
    foreach ($groups as $group) {
        [$inputs, $st] = lab57_read_inputs($p, $group);
        $status = max($status, $st);
        if ($inputs === []) continue;
        $lines = [];
        $endsNl = true;
        foreach ($inputs as [, $content]) {
            [$ls, $endsNl] = lab57_split_lines($content);
            foreach ($ls as $line) $lines[] = $line;
        }
        $result = lab57_sed_run($cmds, $lines, $quiet);
        $text = implode("\n", $result) . ($result !== [] && $endsNl ? "\n" : '');
        if ($inPlace) {
            $file = $group[0];
            $node = $w->fs->get($w->abs($file));
            if ($node !== null && !$w->can($node, 'w')) {
                $p->err("sed: couldn't open temporary file " . Lab57Vfs::dirname($w->abs($file)) . "/sed" . substr(md5($file), 0, 6) . ": Permission denied\n");
                lab57_error_tip($w, 'Permission denied', $file);
                $status = 4;
                continue;
            }
            $err = null;
            if (!$w->writeFile($file, $text, false, $err)) { $p->err("sed: cannot rename $file: $err\n"); $status = 4; }
            continue;
        }
        $p->out($text);
    }
    return $status;
}

/** @return list<string> */
function lab57_sed_run(array $cmds, array $lines, bool $quiet): array
{
    $out = [];
    $active = [];
    $total = count($lines);
    foreach ($lines as $idx => $line) {
        $no = $idx + 1;
        $last = $no === $total;
        $deleted = false;
        $quit = false;
        foreach ($cmds as $ci => $c) {
            $match = false;
            if ($c['a2'] === null) {
                $match = lab57_sed_addr_match($c['a1'], $line, $no, $last);
            } elseif (!empty($active[$ci])) {
                $match = true;
                $end = $c['a2'];
                if (($end[0] === 'line' && $no >= $end[1]) || ($end[0] === 'last' && $last) || ($end[0] === 're' && @preg_match($end[1], $line) === 1)) $active[$ci] = false;
            } elseif (lab57_sed_addr_match($c['a1'], $line, $no, $last)) {
                $match = true;
                $end = $c['a2'];
                $active[$ci] = !(($end[0] === 'line' && $no >= $end[1]) || ($end[0] === 'last' && $last));
            }
            if ($c['neg']) $match = !$match;
            if (!$match) continue;
            switch ($c['cmd']) {
                case 'p': $out[] = $line; break;
                case 'd': $deleted = true; break 2;
                case '=': $out[] = (string)$no; break;
                case 'q': $quit = true; break 2;
                case 'y': $line = strtr($line, $c['map']); break;
                case 's':
                    $count = 0;
                    if ($c['g']) {
                        $new = @preg_replace($c['regex'], $c['rep'], $line, -1, $count);
                    } else {
                        $nth = $c['nth'];
                        $seen = 0;
                        $new = @preg_replace_callback($c['regex'], static function (array $m) use (&$seen, $nth, $c): string {
                            $seen++;
                            if ($seen !== $nth) return $m[0];
                            $rep = $c['rep'];
                            return (string)preg_replace_callback('/\$\{(\d+)\}|\\\\\$/', static fn(array $r): string => $r[0] === '\$' ? '$' : (string)($m[(int)$r[1]] ?? ''), $rep);
                        }, $line, -1, $count);
                        if ($seen < $nth) $count = 0;
                    }
                    if (is_string($new) && $count > 0) {
                        $line = $new;
                        if ($c['p']) $out[] = $line;
                    }
                    break;
            }
        }
        if (!$deleted && !$quiet) $out[] = $line;
        if ($quit) break;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// awk (podmnožina: pole, NR/NF, vzory, print/printf, proměnné, BEGIN/END)
// ---------------------------------------------------------------------------

function lab57_cmd_awk(Lab57Proc $p, array $argv): int
{
    $args = array_slice($argv, 1);
    $fs = null;
    $vars = [];
    $program = null;
    $files = [];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($program === null && $a === '-F') { $fs = (string)($args[++$i] ?? ' '); continue; }
        if ($program === null && str_starts_with($a, '-F')) { $fs = substr($a, 2); continue; }
        if ($program === null && $a === '-v') {
            $assign = (string)($args[++$i] ?? '');
            if (str_contains($assign, '=')) { [$k, $v] = explode('=', $assign, 2); $vars[$k] = $v; }
            continue;
        }
        if ($program === null) { $program = $a; continue; }
        $files[] = $a;
    }
    if ($program === null) { $p->err("usage: awk [-F fs][-v var=value][prog | -f progfile][file ...]\n"); return 2; }
    try {
        $awk = new Lab57Awk($program);
    } catch (RuntimeException $e) {
        $p->err("awk: " . $e->getMessage() . "\n awk: syntax error in: " . $program . "\n");
        $p->w->tip(tr('Simulace awk zná pole ($1, $NF), NR, NF, vzory /text/ a porovnání, print/printf, proměnné a bloky BEGIN/END. Pole a cykly zatím ne.'));
        return 2;
    }
    if (!$awk->onlyBegin() && lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = $awk->onlyBegin() ? [[], 0] : lab57_read_inputs($p, $files);
    if ($fs !== null) $vars['FS'] = $fs === 't' ? "\t" : $fs;
    $out = $awk->run($inputs, $vars);
    $p->out($out);
    if ($awk->error !== '') { $p->err('awk: ' . $awk->error . "\n"); return 2; }
    return $status;
}

final class Lab57Awk
{
    public string $error = '';
    /** @var list<array{0:string,1:string}> */
    private array $toks = [];
    private int $pos = 0;
    /** @var list<array> */
    private array $rules = [];
    private array $vars = [];
    private array $fields = [];
    private string $record = '';
    private string $output = '';

    public function __construct(string $program)
    {
        $this->tokenize($program);
        while ($this->pos < count($this->toks)) {
            if ($this->peek() === ';' || $this->peek() === "\n") { $this->pos++; continue; }
            $this->rules[] = $this->parseRule();
        }
    }

    public function onlyBegin(): bool
    {
        foreach ($this->rules as $rule) if ($rule['kind'] !== 'BEGIN') return false;
        return $this->rules !== [];
    }

    private function tokenize(string $src): void
    {
        $i = 0;
        $n = strlen($src);
        $prevValue = false;
        while ($i < $n) {
            $c = $src[$i];
            if ($c === ' ' || $c === "\t") { $i++; continue; }
            if ($c === '#') { while ($i < $n && $src[$i] !== "\n") $i++; continue; }
            if ($c === "\n") { $this->toks[] = ['op', "\n"]; $i++; $prevValue = false; continue; }
            if ($c === '"') {
                $j = $i + 1;
                $buf = '';
                while ($j < $n && $src[$j] !== '"') {
                    if ($src[$j] === '\\' && $j + 1 < $n) { $buf .= match ($src[$j + 1]) { 'n' => "\n", 't' => "\t", '"' => '"', '\\' => '\\', default => '\\' . $src[$j + 1] }; $j += 2; continue; }
                    $buf .= $src[$j++];
                }
                if ($j >= $n) throw new RuntimeException('non-terminated string');
                $this->toks[] = ['str', $buf];
                $i = $j + 1;
                $prevValue = true;
                continue;
            }
            if ($c === '/' && !$prevValue) {
                $j = $i + 1;
                while ($j < $n && ($src[$j] !== '/' || $src[$j - 1] === '\\')) $j++;
                if ($j >= $n) throw new RuntimeException('non-terminated regular expression');
                $regex = lab57_regex(substr($src, $i + 1, $j - $i - 1), 'ere');
                if ($regex === null) throw new RuntimeException('invalid regular expression');
                $this->toks[] = ['re', $regex];
                $i = $j + 1;
                $prevValue = true;
                continue;
            }
            if (ctype_digit($c) || ($c === '.' && ctype_digit($src[$i + 1] ?? ''))) {
                preg_match('/\d*\.?\d+(?:[eE][-+]?\d+)?/A', $src, $m, 0, $i);
                $this->toks[] = ['num', $m[0]];
                $i += strlen($m[0]);
                $prevValue = true;
                continue;
            }
            if (ctype_alpha($c) || $c === '_') {
                preg_match('/[A-Za-z_][A-Za-z0-9_]*/A', $src, $m, 0, $i);
                $this->toks[] = ['id', $m[0]];
                $i += strlen($m[0]);
                $prevValue = !in_array($m[0], ['print', 'printf', 'BEGIN', 'END'], true);
                continue;
            }
            $three = substr($src, $i, 2);
            if (in_array($three, ['==', '!=', '<=', '>=', '&&', '||', '+=', '-=', '*=', '/=', '++', '--', '!~'], true)) {
                $this->toks[] = ['op', $three];
                $i += 2;
                $prevValue = in_array($three, ['++', '--'], true);
                continue;
            }
            if (str_contains('{}()$,;<>+-*/%!~=', $c)) {
                $this->toks[] = ['op', $c];
                $i++;
                $prevValue = $c === ')';
                continue;
            }
            if ($c === '[') throw new RuntimeException('pole (arrays) simulace awk nepodporuje');
            throw new RuntimeException("unexpected character '$c'");
        }
    }

    private function peek(int $ahead = 0): ?string
    {
        $t = $this->toks[$this->pos + $ahead] ?? null;
        if ($t === null) return null;
        return $t[0] === 'op' || $t[0] === 'id' ? $t[1] : $t[0];
    }

    private function expect(string $op): void
    {
        if ($this->peek() !== $op) throw new RuntimeException("expected '$op'");
        $this->pos++;
    }

    private function parseRule(): array
    {
        if ($this->peek() === 'BEGIN' || $this->peek() === 'END') {
            $kind = (string)$this->peek();
            $this->pos++;
            return ['kind' => $kind, 'pattern' => null, 'body' => $this->parseBlock()];
        }
        $pattern = null;
        if ($this->peek() !== '{') $pattern = $this->parseExpr();
        $body = $this->peek() === '{' ? $this->parseBlock() : null;
        return ['kind' => 'main', 'pattern' => $pattern, 'body' => $body];
    }

    private function parseBlock(): array
    {
        $this->expect('{');
        $stmts = [];
        while ($this->peek() !== '}') {
            if ($this->peek() === null) throw new RuntimeException("missing '}'");
            if ($this->peek() === ';' || $this->peek() === "\n") { $this->pos++; continue; }
            if (in_array($this->peek(), ['for', 'while', 'if', 'do'], true)) throw new RuntimeException('řídicí příkazy (if/for/while) simulace awk nepodporuje');
            $stmts[] = $this->parseStmt();
        }
        $this->pos++;
        return $stmts;
    }

    private function parseStmt(): array
    {
        if ($this->peek() === 'print' || $this->peek() === 'printf') {
            $kind = (string)$this->peek();
            $this->pos++;
            $args = [];
            if (!in_array($this->peek(), [';', '}', "\n", null], true)) {
                $args[] = $this->parseExpr(true);
                while ($this->peek() === ',') { $this->pos++; $args[] = $this->parseExpr(true); }
            }
            return [$kind, $args];
        }
        return ['expr', $this->parseExpr()];
    }

    private function parseExpr(bool $noGt = false): array
    {
        if (($this->toks[$this->pos][0] ?? '') === 'id' && in_array($this->peek(1), ['=', '+=', '-=', '*=', '/='], true)) {
            $name = (string)$this->toks[$this->pos][1];
            $op = (string)$this->peek(1);
            $this->pos += 2;
            return ['assign', $name, $op, $this->parseExpr($noGt)];
        }
        return $this->parseOr($noGt);
    }

    private function parseOr(bool $noGt): array
    {
        $l = $this->parseAnd($noGt);
        while ($this->peek() === '||') { $this->pos++; $l = ['bin', '||', $l, $this->parseAnd($noGt)]; }
        return $l;
    }

    private function parseAnd(bool $noGt): array
    {
        $l = $this->parseMatch($noGt);
        while ($this->peek() === '&&') { $this->pos++; $l = ['bin', '&&', $l, $this->parseMatch($noGt)]; }
        return $l;
    }

    private function parseMatch(bool $noGt): array
    {
        $l = $this->parseCmp($noGt);
        while ($this->peek() === '~' || $this->peek() === '!~') {
            $neg = $this->peek() === '!~';
            $this->pos++;
            $t = $this->toks[$this->pos] ?? null;
            if ($t === null || $t[0] !== 're') throw new RuntimeException('expected /regex/ after ~');
            $this->pos++;
            $l = ['match', $l, $t[1], $neg];
        }
        return $l;
    }

    private function parseCmp(bool $noGt): array
    {
        $l = $this->parseConcat();
        $ops = $noGt ? ['==', '!=', '<', '<=', '>='] : ['==', '!=', '<', '>', '<=', '>='];
        while (in_array($this->peek(), $ops, true)) {
            $op = (string)$this->peek();
            $this->pos++;
            $l = ['bin', $op, $l, $this->parseConcat()];
        }
        return $l;
    }

    private function parseConcat(): array
    {
        $parts = [$this->parseAdd()];
        while ($this->startsValue()) $parts[] = $this->parseAdd();
        return count($parts) === 1 ? $parts[0] : ['concat', $parts];
    }

    private function startsValue(): bool
    {
        $t = $this->toks[$this->pos] ?? null;
        if ($t === null) return false;
        if (in_array($t[0], ['num', 'str'], true)) return true;
        if ($t[0] === 'id') return !in_array($t[1], ['print', 'printf', 'BEGIN', 'END'], true);
        return $t[0] === 'op' && in_array($t[1], ['$', '(', '!'], true);
    }

    private function parseAdd(): array
    {
        $l = $this->parseMul();
        while ($this->peek() === '+' || $this->peek() === '-') {
            $op = (string)$this->peek();
            $this->pos++;
            $l = ['bin', $op, $l, $this->parseMul()];
        }
        return $l;
    }

    private function parseMul(): array
    {
        $l = $this->parseUnary();
        while (in_array($this->peek(), ['*', '/', '%'], true)) {
            $op = (string)$this->peek();
            $this->pos++;
            $l = ['bin', $op, $l, $this->parseUnary()];
        }
        return $l;
    }

    private function parseUnary(): array
    {
        if ($this->peek() === '!') { $this->pos++; return ['not', $this->parseUnary()]; }
        if ($this->peek() === '-') { $this->pos++; return ['neg', $this->parseUnary()]; }
        if ($this->peek() === '+') { $this->pos++; return $this->parseUnary(); }
        return $this->parsePrimary();
    }

    private function parsePrimary(): array
    {
        $t = $this->toks[$this->pos] ?? null;
        if ($t === null) throw new RuntimeException('unexpected end of program');
        $this->pos++;
        if ($t[0] === 'num') return ['num', (float)$t[1]];
        if ($t[0] === 'str') return ['str', $t[1]];
        if ($t[0] === 're') return ['match', ['field', ['num', 0.0]], $t[1], false];
        if ($t[0] === 'op' && $t[1] === '$') return ['field', $this->parsePrimary()];
        if ($t[0] === 'op' && $t[1] === '(') {
            $e = $this->parseExpr();
            $this->expect(')');
            return $e;
        }
        if ($t[0] === 'id') {
            if ($this->peek() === '++' || $this->peek() === '--') {
                $delta = $this->peek() === '++' ? 1 : -1;
                $this->pos++;
                return ['incr', $t[1], $delta];
            }
            if ($this->peek() === '(') {
                $this->pos++;
                $args = [];
                if ($this->peek() !== ')') {
                    $args[] = $this->parseExpr();
                    while ($this->peek() === ',') { $this->pos++; $args[] = $this->parseExpr(); }
                }
                $this->expect(')');
                return ['call', $t[1], $args];
            }
            return ['var', $t[1]];
        }
        throw new RuntimeException("syntax error at '" . $t[1] . "'");
    }

    /** @param list<array{0:string,1:string}> $inputs */
    public function run(array $inputs, array $vars): string
    {
        $this->vars = array_merge(['FS' => ' ', 'OFS' => ' ', 'ORS' => "\n", 'NR' => 0, 'NF' => 0, 'FILENAME' => ''], $vars);
        $steps = 0;
        try {
            foreach ($this->rules as $r) if ($r['kind'] === 'BEGIN') $this->runBlock($r['body']);
            foreach ($inputs as [$name, $content]) {
                $this->vars['FILENAME'] = $name === '-' ? '' : $name;
                foreach (lab57_split_lines($content)[0] as $line) {
                    if (++$steps > 20000) throw new RuntimeException('příliš mnoho řádků');
                    $this->vars['NR'] = (int)$this->vars['NR'] + 1;
                    $this->setRecord($line);
                    foreach ($this->rules as $r) {
                        if ($r['kind'] !== 'main') continue;
                        if ($r['pattern'] !== null && !$this->truthy($this->eval($r['pattern']))) continue;
                        if ($r['body'] === null) $this->output .= $this->record . "\n";
                        else $this->runBlock($r['body']);
                    }
                }
            }
            foreach ($this->rules as $r) if ($r['kind'] === 'END') $this->runBlock($r['body']);
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
        }
        return $this->output;
    }

    private function setRecord(string $line): void
    {
        $this->record = $line;
        $fs = (string)$this->vars['FS'];
        if ($fs === ' ') $fields = preg_split('/[ \t]+/', trim($line), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        elseif (mb_strlen($fs) === 1) $fields = $line === '' ? [] : explode($fs, $line);
        else $fields = @preg_split('~' . str_replace('~', '\~', $fs) . '~', $line) ?: [$line];
        $this->fields = array_values($fields);
        $this->vars['NF'] = count($this->fields);
    }

    private function runBlock(array $stmts): void
    {
        foreach ($stmts as $s) {
            if ($s[0] === 'print') {
                $parts = $s[1] === [] ? [$this->record] : array_map(fn(array $e): string => $this->str($this->eval($e)), $s[1]);
                $this->output .= implode((string)$this->vars['OFS'], $parts) . (string)$this->vars['ORS'];
            } elseif ($s[0] === 'printf') {
                if ($s[1] === []) continue;
                $format = $this->str($this->eval($s[1][0]));
                $args = array_map(fn(array $e) => $this->eval($e), array_slice($s[1], 1));
                $this->output .= (string)preg_replace_callback('/%([-+ 0#]*\d*(?:\.\d+)?)([sdifxoc%])/', static function (array $m) use (&$args): string {
                    if ($m[2] === '%') return '%';
                    $v = $args === [] ? '' : array_shift($args);
                    return match ($m[2]) { 's' => sprintf('%' . $m[1] . 's', is_float($v) && floor($v) === $v ? (string)(int)$v : (string)$v), 'd', 'i' => sprintf('%' . $m[1] . 'd', (int)$v), 'f' => sprintf('%' . $m[1] . 'f', (float)$v), 'x' => sprintf('%' . $m[1] . 'x', (int)$v), 'o' => sprintf('%' . $m[1] . 'o', (int)$v), default => mb_substr((string)$v, 0, 1) };
                }, $format);
            } else {
                $this->eval($s[1]);
            }
        }
    }

    private function eval(array $e): mixed
    {
        switch ($e[0]) {
            case 'num': return $e[1];
            case 'str': return $e[1];
            case 'var': return $this->vars[$e[1]] ?? '';
            case 'field':
                $i = (int)$this->num($this->eval($e[1]));
                if ($i === 0) return $this->record;
                return $this->fields[$i - 1] ?? '';
            case 'concat': return implode('', array_map(fn(array $x): string => $this->str($this->eval($x)), $e[1]));
            case 'not': return $this->truthy($this->eval($e[1])) ? 0.0 : 1.0;
            case 'neg': return -$this->num($this->eval($e[1]));
            case 'match': $r = @preg_match($e[2], $this->str($this->eval($e[1]))) === 1; return ($r xor $e[3]) ? 1.0 : 0.0;
            case 'incr': $old = $this->num($this->vars[$e[1]] ?? 0); $this->vars[$e[1]] = $old + $e[2]; return $old;
            case 'assign':
                $v = $this->eval($e[3]);
                $cur = $this->num($this->vars[$e[1]] ?? 0);
                $this->vars[$e[1]] = match ($e[2]) { '+=' => $cur + $this->num($v), '-=' => $cur - $this->num($v), '*=' => $cur * $this->num($v), '/=' => $this->num($v) == 0 ? throw new RuntimeException('division by zero') : $cur / $this->num($v), default => $v };
                return $this->vars[$e[1]];
            case 'call': return $this->call($e[1], $e[2]);
            case 'bin':
                [$l, $r] = [$this->eval($e[2]), $this->eval($e[3])];
                switch ($e[1]) {
                    case '&&': return ($this->truthy($l) && $this->truthy($r)) ? 1.0 : 0.0;
                    case '||': return ($this->truthy($l) || $this->truthy($r)) ? 1.0 : 0.0;
                    case '+': return $this->num($l) + $this->num($r);
                    case '-': return $this->num($l) - $this->num($r);
                    case '*': return $this->num($l) * $this->num($r);
                    case '/': if ($this->num($r) == 0) throw new RuntimeException('division by zero'); return $this->num($l) / $this->num($r);
                    case '%': if ((int)$this->num($r) === 0) throw new RuntimeException('division by zero in %'); return (float)fmod($this->num($l), $this->num($r));
                }
                $numeric = $this->looksNumeric($l) && $this->looksNumeric($r);
                $cmp = $numeric ? ($this->num($l) <=> $this->num($r)) : strcmp($this->str($l), $this->str($r));
                return (match ($e[1]) { '==' => $cmp === 0, '!=' => $cmp !== 0, '<' => $cmp < 0, '>' => $cmp > 0, '<=' => $cmp <= 0, default => $cmp >= 0 }) ? 1.0 : 0.0;
        }
        return '';
    }

    private function call(string $name, array $args): mixed
    {
        $v = array_map(fn(array $a) => $this->eval($a), $args);
        return match ($name) {
            'length' => (float)mb_strlen($args === [] ? $this->record : $this->str($v[0])),
            'toupper' => mb_strtoupper($this->str($v[0] ?? '')),
            'tolower' => mb_strtolower($this->str($v[0] ?? '')),
            'substr' => mb_substr($this->str($v[0] ?? ''), max(0, (int)$this->num($v[1] ?? 1) - 1), isset($v[2]) ? (int)$this->num($v[2]) : null),
            'int' => (float)(int)$this->num($v[0] ?? 0),
            default => throw new RuntimeException("calling undefined function $name"),
        };
    }

    private function looksNumeric(mixed $v): bool
    {
        return is_float($v) || is_int($v) || (is_string($v) && is_numeric(trim($v)));
    }

    private function num(mixed $v): float
    {
        if (is_float($v) || is_int($v)) return (float)$v;
        return preg_match('/^\s*[-+]?(\d+\.?\d*|\.\d+)([eE][-+]?\d+)?/', (string)$v, $m) === 1 ? (float)$m[0] : 0.0;
    }

    private function str(mixed $v): string
    {
        if (is_float($v)) return floor($v) === $v && abs($v) < 1e15 ? (string)(int)$v : rtrim(rtrim(sprintf('%.6f', $v), '0'), '.');
        return (string)$v;
    }

    private function truthy(mixed $v): bool
    {
        if (is_float($v) || is_int($v)) return $v != 0;
        return $v !== '' && $v !== '0';
    }
}

// ---------------------------------------------------------------------------
// diff, rev, nl, tee
// ---------------------------------------------------------------------------

function lab57_cmd_diff(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'qsuiwB', ['brief' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (count($files) !== 2) {
        $p->err(count($files) < 2 ? "diff: missing operand after '" . ($files[0] ?? 'diff') . "'\ndiff: Try 'diff --help' for more information.\n" : "diff: extra operand '" . $files[2] . "'\n");
        return 2;
    }
    [$inputs, $status] = lab57_read_inputs($p, $files);
    if ($status !== 0 || count($inputs) !== 2) return 2;
    [$a] = lab57_split_lines($inputs[0][1]);
    [$b] = lab57_split_lines($inputs[1][1]);
    if ($a === $b) return 0;
    if (isset($o['q']) || isset($o['--brief'])) { $p->line('Files ' . $files[0] . ' and ' . $files[1] . ' differ'); return 1; }
    $n = count($a);
    $m = count($b);
    if ($n * $m > 4000000) { $p->line('Files ' . $files[0] . ' and ' . $files[1] . ' differ'); return 1; }
    $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
    for ($i = $n - 1; $i >= 0; $i--) {
        for ($j = $m - 1; $j >= 0; $j--) {
            $lcs[$i][$j] = $a[$i] === $b[$j] ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
        }
    }
    $i = 0;
    $j = 0;
    $hunks = [];
    while ($i < $n || $j < $m) {
        if ($i < $n && $j < $m && $a[$i] === $b[$j]) { $i++; $j++; continue; }
        $si = $i;
        $sj = $j;
        while (($i < $n || $j < $m) && !($i < $n && $j < $m && $a[$i] === $b[$j])) {
            if ($j < $m && ($i >= $n || $lcs[$i][$j + 1] >= $lcs[$i + 1][$j])) $j++;
            else $i++;
        }
        $hunks[] = [$si, $i, $sj, $j];
    }
    $range = static fn(int $s, int $e): string => $e - $s <= 1 ? (string)max($s + 1, $e) : ($s + 1) . ',' . $e;
    foreach ($hunks as [$si, $ei, $sj, $ej]) {
        if ($si === $ei) $p->line($si . 'a' . $range($sj, $ej));
        elseif ($sj === $ej) $p->line($range($si, $ei) . 'd' . $sj);
        else $p->line($range($si, $ei) . 'c' . $range($sj, $ej));
        for ($k = $si; $k < $ei; $k++) $p->line('< ' . $a[$k]);
        if ($si !== $ei && $sj !== $ej) $p->line('---');
        for ($k = $sj; $k < $ej; $k++) $p->line('> ' . $b[$k]);
    }
    return 1;
}

function lab57_cmd_rev(Lab57Proc $p, array $argv): int
{
    $files = array_slice($argv, 1);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    foreach ($inputs as [, $content]) {
        [$lines, $endsNl] = lab57_split_lines($content);
        $rev = array_map(static fn(string $l): string => implode('', array_reverse(preg_split('//u', $l, -1, PREG_SPLIT_NO_EMPTY) ?: [])), $lines);
        $p->out(implode("\n", $rev) . ($endsNl ? "\n" : ''));
    }
    return $status;
}

function lab57_cmd_nl(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'b:w:s:', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $all = ($o['b'] ?? 't') === 'a';
    $width = max(1, (int)($o['w'] ?? 6));
    $sep = (string)($o['s'] ?? "\t");
    $n = 0;
    foreach ($inputs as [, $content]) {
        foreach (lab57_split_lines($content)[0] as $line) {
            if ($line === '' && !$all) { $p->line(''); continue; }
            $n++;
            $p->line(str_pad((string)$n, $width, ' ', STR_PAD_LEFT) . $sep . $line);
        }
    }
    return $status;
}

function lab57_cmd_tee(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'a', ['append' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $append = isset($o['a']) || isset($o['--append']);
    $status = 0;
    foreach ($files as $file) {
        $err = null;
        if (!$w->writeFile($file, $p->stdin, $append, $err)) {
            $p->err("tee: $file: $err\n");
            lab57_error_tip($w, (string)$err, $file);
            $status = 1;
        }
    }
    $p->out($p->stdin);
    return $status;
}

// ---------------------------------------------------------------------------
// base64, xxd, strings, *sum
// ---------------------------------------------------------------------------

function lab57_cmd_base64(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'diw:', ['decode' => false, 'wrap' => true]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, array_slice($files, 0, 1));
    $data = (string)($inputs[0][1] ?? '');
    if ($status !== 0) return $status;
    if (isset($o['d']) || isset($o['--decode'])) {
        $clean = (string)preg_replace('/\s+/', '', $data);
        $decoded = base64_decode($clean, true);
        if ($decoded === false) {
            $partial = base64_decode((string)preg_replace('/[^A-Za-z0-9+\/=]/', '', $clean));
            $p->out((string)$partial);
            $p->err("base64: invalid input\n");
            return 1;
        }
        $p->out($decoded);
        return 0;
    }
    $wrap = (int)($o['w'] ?? $o['--wrap'] ?? 76);
    $encoded = base64_encode($data);
    $p->out(($wrap > 0 ? implode("\n", str_split($encoded, $wrap)) : $encoded) . ($encoded === '' ? '' : "\n"));
    return 0;
}

function lab57_cmd_xxd(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'prc:l:u', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, array_slice($files, 0, 1));
    if ($status !== 0) return $status;
    $data = (string)($inputs[0][1] ?? '');
    if (isset($o['r'])) {
        if (isset($o['p'])) {
            $hex = (string)preg_replace('/[^0-9A-Fa-f]/', '', $data);
            if (strlen($hex) % 2 === 1) $hex = substr($hex, 0, -1);
            $p->out((string)hex2bin($hex));
            return 0;
        }
        $bin = '';
        foreach (lab57_split_lines($data)[0] as $line) {
            if (preg_match('/^[0-9a-fA-F]+:\s+((?:[0-9a-fA-F]{2,4}\s?)+)/', $line, $m) !== 1) continue;
            $hex = (string)preg_replace('/\s+/', '', $m[1]);
            $bin .= (string)hex2bin(substr($hex, 0, strlen($hex) - strlen($hex) % 2));
        }
        $p->out($bin);
        return 0;
    }
    if (isset($o['l'])) $data = substr($data, 0, max(0, (int)$o['l']));
    if (isset($o['p'])) {
        $hex = bin2hex($data);
        $p->out($hex === '' ? '' : implode("\n", str_split($hex, 60)) . "\n");
        return 0;
    }
    $cols = max(1, min(64, (int)($o['c'] ?? 16)));
    $out = [];
    foreach (str_split($data, $cols) as $k => $chunk) {
        if ($chunk === '') continue;
        $hex = bin2hex($chunk);
        $groups = implode(' ', str_split($hex, 4));
        $ascii = (string)preg_replace('/[^\x20-\x7E]/', '.', $chunk);
        $width = (int)($cols * 2 + ceil($cols / 2) - 1);
        $out[] = sprintf('%08x', $k * $cols) . ': ' . str_pad($groups, $width) . '  ' . $ascii;
    }
    $p->out(lab57_join($out));
    return 0;
}

function lab57_cmd_strings(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'n:a', ['bytes' => true]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    $min = max(1, (int)($o['n'] ?? $o['--bytes'] ?? 4));
    [$inputs, $status] = lab57_read_inputs($p, $files);
    foreach ($inputs as [, $content]) {
        preg_match_all('/[\x20-\x7E\t]{' . $min . ',}/', $content, $m);
        foreach ($m[0] as $s) $p->line($s);
    }
    return $status;
}

function lab57_cmd_hashsum(Lab57Proc $p, array $argv): int
{
    $algo = match ($argv[0]) { 'md5sum' => 'md5', 'sha1sum' => 'sha1', default => 'sha256' };
    [, $files, $error] = lab57_getopt(array_slice($argv, 1), 'bt', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    foreach ($inputs as [$name, $content]) $p->line(hash($algo, $content) . '  ' . $name);
    return $status;
}
