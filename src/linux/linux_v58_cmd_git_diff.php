<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – Git v terminálu: vlastní řádkový diff přes tabulku nejdelší společné podposloupnosti
 * (LCS, po odříznutí společného začátku a konce; nad 400 × 400 řádků hrubý diff), výstup unified diff (3 řádky kontextu),
 * --stat, blame a příkazy git diff, log, show, blame, rev-parse, remote.
 */

const LAB58_GIT_DIFF_CELLS = 160000;

/** @return array{0:list<string>,1:bool} řádky a zda text končí novým řádkem */
function lab58_git_lines(string $text): array
{
    $lines = $text === '' ? [] : explode("\n", $text);
    return end($lines) === '' ? [array_slice($lines, 0, -1), true] : [$lines, $lines === []];
}

/** @return list<array{0:string,1:?int,2:?int}> [' '|'-'|'+', index ve starém, index v novém] */
function lab58_git_lcs(array $a, array $b): array
{
    [$n, $m] = [count($a), count($b)];
    for ($pre = 0; $pre < $n && $pre < $m && $a[$pre] === $b[$pre]; $pre++);
    for ($suf = 0; $suf < $n - $pre && $suf < $m - $pre && $a[$n - 1 - $suf] === $b[$m - 1 - $suf]; $suf++);
    [$x, $y] = [array_slice($a, $pre, $n - $pre - $suf), array_slice($b, $pre, $m - $pre - $suf)];
    [$p, $q] = [count($x), count($y)];
    $ops = [];
    for ($i = 0; $i < $pre; $i++) $ops[] = [' ', $i, $i];
    $len = $p * $q <= LAB58_GIT_DIFF_CELLS ? array_fill(0, $p + 1, array_fill(0, $q + 1, 0)) : null;
    if ($len !== null) for ($i = $p - 1; $i >= 0; $i--) for ($j = $q - 1; $j >= 0; $j--) $len[$i][$j] = $x[$i] === $y[$j] ? $len[$i + 1][$j + 1] + 1 : max($len[$i + 1][$j], $len[$i][$j + 1]);
    for ($i = $j = 0; $i < $p || $j < $q;) {
        if ($len !== null && $i < $p && $j < $q && $x[$i] === $y[$j]) $ops[] = [' ', $pre + $i++, $pre + $j++];
        elseif ($i < $p && ($j >= $q || $len === null || $len[$i + 1][$j] >= $len[$i][$j + 1])) $ops[] = ['-', $pre + $i++, null];
        else $ops[] = ['+', null, $pre + $j++];
    }
    for ($i = 0; $i < $suf; $i++) $ops[] = [' ', $n - $suf + $i, $m - $suf + $i];
    return $ops;
}

/** Hunky se 3 řádky kontextu; změny oddělené nejvýš 6 shodnými řádky patří do jednoho hunku. */
function lab58_git_hunks(array $ops, int $ctx = 3): array
{
    [$pos, $o, $n] = [[], 0, 0];
    foreach ($ops as $k => [$op]) { $pos[$k] = [$o, $n]; $o += $op !== '+' ? 1 : 0; $n += $op !== '-' ? 1 : 0; }
    $hunks = [];
    for ($k = 0, $count = count($ops); $k < $count; $k++) {
        if ($ops[$k][0] === ' ') continue;
        $end = $k;
        for ($j = $k + 1; $j < $count && ($ops[$j][0] !== ' ' || $j - $end <= 2 * $ctx); $j++) if ($ops[$j][0] !== ' ') $end = $j;
        [$from, $to] = [max(0, $k - $ctx), min($count - 1, $end + $ctx)];
        $slice = array_slice($ops, $from, $to - $from + 1);
        $oc = count(array_filter($slice, static fn(array $s): bool => $s[0] !== '+'));
        $nc = count(array_filter($slice, static fn(array $s): bool => $s[0] !== '-'));
        $hunks[] = ['os' => $pos[$from][0] + ($oc > 0 ? 1 : 0), 'oc' => $oc, 'ns' => $pos[$from][1] + ($nc > 0 ? 1 : 0), 'nc' => $nc, 'ops' => $slice, 'before' => $pos[$from][0]];
        $k = $to;
    }
    return $hunks;
}

/** Unified diff jednoho souboru. $old/$new = [id blobu, obsah] nebo null (soubor na té straně chybí). */
function lab58_git_patch(Lab57Proc $p, string $path, ?array $old, ?array $new): void
{
    $p->line('diff --git a/' . $path . ' b/' . $path);
    if ($old === null || $new === null) $p->line(($old === null ? 'new' : 'deleted') . ' file mode 100644');
    $short = static fn(?array $s): string => $s === null ? LAB58_GIT_ZERO : substr((string)$s[0], 0, 7);
    $p->line('index ' . $short($old) . '..' . $short($new) . ($old !== null && $new !== null ? ' 100644' : ''));
    [$a, $b] = [(string)($old[1] ?? ''), (string)($new[1] ?? '')];
    [$from, $to] = [$old === null ? '/dev/null' : 'a/' . $path, $new === null ? '/dev/null' : 'b/' . $path];
    if ($a === $b) return;
    if (!lab57_is_text($a) || !lab57_is_text($b)) { $p->line('Binary files ' . $from . ' and ' . $to . ' differ'); return; }
    $p->out('--- ' . $from . "\n+++ " . $to . "\n");
    [[$la, $nla], [$lb, $nlb]] = [lab58_git_lines($a), lab58_git_lines($b)];
    [$ka, $kb] = [$la, $lb];
    if (!$nla) $ka[count($ka) - 1] .= "\0";
    if (!$nlb) $kb[count($kb) - 1] .= "\0";
    $range = static fn(int $s, int $c): string => $c === 1 ? (string)$s : $s . ',' . $c;
    foreach (lab58_git_hunks(lab58_git_lcs($ka, $kb)) as $h) {
        $func = '';
        for ($i = $h['before'] - 1; $i >= 0 && $func === ''; $i--) if (preg_match('/^[A-Za-z_$]/', $la[$i]) === 1) $func = ' ' . rtrim($la[$i]);
        $p->line('@@ -' . $range($h['os'], $h['oc']) . ' +' . $range($h['ns'], $h['nc']) . ' @@' . $func);
        foreach ($h['ops'] as [$op, $i, $j]) {
            $p->line($op . ($op === '+' ? $lb[$j] : $la[$i]));
            if (($op !== '+' && !$nla && $i === count($la) - 1) || ($op !== '-' && !$nlb && $j === count($lb) - 1)) $p->line('\\ No newline at end of file');
        }
    }
}

/** @return array{0:int,1:int} přidané a odebrané řádky (binární soubor = 0, 0) */
function lab58_git_numstat(string $a, string $b): array
{
    if (!lab57_is_text($a) || !lab57_is_text($b)) return [0, 0];
    $ops = array_column(lab58_git_lcs(lab58_git_lines($a)[0], lab58_git_lines($b)[0]), 0);
    return [count(array_keys($ops, '+', true)), count(array_keys($ops, '-', true))];
}

function lab58_git_summary(int $files, int $ins, int $del): string
{
    return ' ' . $files . ' file' . ($files === 1 ? '' : 's') . ' changed' . ($ins > 0 || $del === 0 ? ', ' . $ins . ' insertion' . ($ins === 1 ? '' : 's') . '(+)' : '')
        . ($del > 0 || $ins === 0 ? ', ' . $del . ' deletion' . ($del === 1 ? '' : 's') . '(-)' : '');
}

/** Výpis --stat. $rows: [cesta, přidané, odebrané, binární, stará velikost, nová velikost] */
function lab58_git_stat(Lab57Proc $p, array $rows): void
{
    if ($rows === []) return;
    $nameW = max(array_map(static fn(array $x): int => mb_strwidth((string)$x[0]), $rows));
    $max = max(array_map(static fn(array $x): int => $x[1] + $x[2], $rows));
    $numW = max(strlen((string)$max), in_array(true, array_column($rows, 3), true) ? 3 : 1);
    $scale = $max > max(10, 72 - $nameW - $numW) ? max(10, 72 - $nameW - $numW) / $max : 1.0;
    $bar = static fn(int $n): int => $n === 0 ? 0 : max(1, (int)round($n * $scale));
    foreach ($rows as [$path, $i, $d, $bin, $os, $ns]) {
        $name = ' ' . $path . str_repeat(' ', $nameW - mb_strwidth((string)$path)) . ' | ';
        $p->line($name . ($bin ? 'Bin ' . $os . ' -> ' . $ns . ' bytes' : str_pad((string)($i + $d), $numW, ' ', STR_PAD_LEFT) . ($i + $d > 0 ? ' ' . str_repeat('+', $bar($i)) . str_repeat('-', $bar($d)) : '')));
    }
    $p->line(lab58_git_summary(count($rows), (int)array_sum(array_column($rows, 1)), (int)array_sum(array_column($rows, 2))));
}

/** Odpovídá cesta některému pathspecu? ('' = vše, složka = prefix, jinak přesná shoda nebo glob) */
function lab58_git_matches(string $path, array $specs): bool
{
    foreach ($specs as $s) if ($s === '' || $path === $s || str_starts_with($path, rtrim($s, '/') . '/') || (lab57_has_glob($s) && lab57_glob_match($s, $path))) return true;
    return $specs === [];
}

/**
 * Porovná dva snímky (cesta → id blobu); obsah z $oldWork/$newWork (pracovní strom) nebo z úložiště.
 * $mode: patch | stat | name-only | name-status | quiet. Vrací počet změněných souborů.
 */
function lab58_git_diff_trees(Lab57Proc $p, array $r, array $old, array $new, array $specs, string $mode, array $newWork = [], array $oldWork = []): int
{
    $paths = array_unique(array_map('strval', array_merge(array_keys($old), array_keys($new))));
    sort($paths, SORT_STRING);
    $rows = [];
    foreach ($paths as $path) {
        [$ia, $ib] = [$old[$path] ?? null, $new[$path] ?? null];
        if ($ia === $ib || !lab58_git_matches($path, $specs)) continue;
        $rows[] = $path;
        if ($mode === 'quiet') continue;
        if ($mode === 'name-only' || $mode === 'name-status') { $p->line(($mode === 'name-status' ? ($ia === null ? 'A' : ($ib === null ? 'D' : 'M')) . "\t" : '') . $path); continue; }
        $a = $ia === null ? null : [$ia, $oldWork[$path] ?? (string)lab58_git_blob($p->w, $r, $ia)];
        $b = $ib === null ? null : [$ib, $newWork[$path] ?? (string)lab58_git_blob($p->w, $r, $ib)];
        if ($mode === 'patch') { lab58_git_patch($p, $path, $a, $b); continue; }
        [$ca, $cb] = [(string)($a[1] ?? ''), (string)($b[1] ?? '')];
        $rows[count($rows) - 1] = array_merge([$path], lab58_git_numstat($ca, $cb), [!lab57_is_text($ca) || !lab57_is_text($cb), strlen($ca), strlen($cb)]);
    }
    if ($mode === 'stat') lab58_git_stat($p, $rows);
    return count($rows);
}

/**
 * Kdo naposledy změnil který řádek (jen po první rodičovské linii, bez sledování přejmenování).
 * @return list<array{0:string,1:int,2:string}>|null [commit, číslo řádku v něm, text]
 */
function lab58_git_blame_lines(Lab57World $w, array $r, string $start, string $path): ?array
{
    $content = lab58_git_blob($w, $r, lab58_git_tree($w, $r, $start)[$path] ?? null);
    if ($content === null) return null;
    $lines = lab58_git_lines($content)[0];
    [$pending, $cur, $commit, $who] = [array_keys($lines), $lines, $start, []];
    for ($guard = 0; $pending !== [] && $guard < LAB58_GIT_MAX_WALK; $guard++) {
        $parent = lab58_git_obj($w, $r, $commit)['parents'][0] ?? null;
        $prevText = $parent === null ? null : lab58_git_blob($w, $r, lab58_git_tree($w, $r, (string)$parent)[$path] ?? null);
        $prev = $prevText === null ? [] : lab58_git_lines($prevText)[0];
        [$map, $next] = [[], []];
        if ($prevText !== null) foreach (lab58_git_lcs($prev, $cur) as [$op, $i, $j]) if ($op === ' ') $map[$j] = $i;
        foreach ($pending as $final => $idx) { if (isset($map[$idx]) && $guard < LAB58_GIT_MAX_WALK - 1) $next[$final] = $map[$idx]; else $who[$final] = [$commit, $idx + 1]; }
        [$pending, $cur, $commit] = [$next, $prev, (string)$parent];
    }
    ksort($who);
    return array_map(static fn(array $x, string $text): array => [$x[0], $x[1], $text], $who, $lines);
}

// --- git diff, git log, git show, git blame ------------------------------------

function lab58_git_cmd_diff(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['cached|staged' => 0, 'stat' => 0, 'name-only' => 0, 'name-status' => 0, 'quiet' => 0, 'exit-code' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'diff', $err);
    if (($sp = lab58_git_revs_paths($p, $r, $ops, $after)) === null) return 128;
    [$revs, $specs] = $sp;
    if (count($revs) === 1 && str_contains($revs[0], '..')) $revs = array_map(static fn(string $x): string => $x === '' ? 'HEAD' : $x, explode('..', str_replace('...', '..', $revs[0]), 2));
    [$st, $work] = [lab58_git_status_data($w, $r), []];
    if (count($revs) >= 2) [$old, $new] = [lab58_git_tree($w, $r, lab58_git_resolve($w, $r, $revs[0])), lab58_git_tree($w, $r, lab58_git_resolve($w, $r, $revs[1]))];
    elseif (!empty($o['cached'])) [$old, $new] = [$revs === [] ? $st['tree'] : lab58_git_tree($w, $r, lab58_git_resolve($w, $r, $revs[0])), $st['index']];
    else [$old, $new, $work] = [$revs === [] ? $st['index'] : lab58_git_tree($w, $r, lab58_git_resolve($w, $r, $revs[0])), array_intersect_key($st['ids'], $revs === [] ? $st['index'] : $st['index'] + $st['tree']), $st['work']];
    $mode = !empty($o['quiet']) ? 'quiet' : (!empty($o['stat']) ? 'stat' : (!empty($o['name-only']) ? 'name-only' : (!empty($o['name-status']) ? 'name-status' : 'patch')));
    $changed = lab58_git_diff_trees($p, $r, $old, $new, $specs, $mode, $work);
    return (!empty($o['exit-code']) || !empty($o['quiet'])) && $changed > 0 ? 1 : 0;
}

/** Ozdoby commitů jako v git log: HEAD -> větev, pak ostatní refs v obráceném abecedním pořadí (tag: …). */
function lab58_git_decorations(Lab57World $w, array $r): array
{
    [$head, $refs] = [lab58_git_head($w, $r), []];
    foreach (['refs/heads' => '', 'refs/remotes' => '', 'refs/tags' => 'tag: '] as $prefix => $label) {
        foreach (lab58_git_refs($w, $r, $prefix) as $name => $id) if (!($prefix === 'refs/heads' && $name === $head['branch'])) $refs[$prefix . '/' . $name] = [lab58_git_peel($w, $r, $id), $label . $name];
    }
    krsort($refs, SORT_STRING);
    $out = $head['id'] !== null ? [$head['id'] => [$head['branch'] !== null ? 'HEAD -> ' . $head['branch'] : 'HEAD']] : [];
    foreach ($refs as [$id, $label]) if ($id !== null) $out[$id][] = $label;
    return $out;
}

/** Nastavení výpisu commitů pro log/show. */
function lab58_git_fmt(Lab57Proc $p, array $r, array $o, bool $show, array $specs): array
{
    $pretty = (string)($o['pretty'] ?? 'medium');
    $format = preg_match('/^(t?format:)(.*)$/s', $pretty, $m) === 1 ? $m[2] : (str_contains($pretty, '%') ? $pretty : null);
    $diff = !empty($o['stat']) ? 'stat' : (!empty($o['name-only']) ? 'name-only' : (!empty($o['name-status']) ? 'name-status' : ((($show && empty($o['s'])) || !empty($o['p'])) ? 'patch' : '')));
    return ['oneline' => !empty($o['oneline']) || $pretty === 'oneline', 'short' => !empty($o['oneline']) || !empty($o['abbrev-commit']), 'pretty' => $pretty, 'format' => $format, 'date' => (string)($o['date'] ?? 'default'),
        'dec' => !empty($o['decorate']) || (empty($o['no-decorate']) && $p->tty) ? lab58_git_decorations($p->w, $r) : [], 'diff' => $diff, 'specs' => $specs];
}

function lab58_git_format(Lab57World $w, string $id, array $c, array $f): string
{
    [$a, $m, $dec, $parents] = [(array)$c['author'], (array)$c['committer'], $f['dec'][$id] ?? [], array_map('strval', (array)($c['parents'] ?? []))];
    $v = ['H' => $id, 'h' => substr($id, 0, 7), 'T' => (string)$c['tree'], 't' => substr((string)$c['tree'], 0, 7), 'P' => implode(' ', $parents), 'p' => implode(' ', array_map(static fn(string $x): string => substr($x, 0, 7), $parents)),
        's' => lab58_git_subject($c), 'b' => ltrim((string)(explode("\n", (string)$c['message'], 2)[1] ?? ''), "\n"), 'B' => (string)$c['message'], 'd' => $dec === [] ? '' : ' (' . implode(', ', $dec) . ')', 'D' => implode(', ', $dec), 'n' => "\n", '%' => '%'];
    foreach (['a' => $a, 'c' => $m] as $k => $who) {
        $t = (int)$who['time'];
        $v += [$k . 'n' => (string)$who['name'], $k . 'e' => (string)$who['email'], $k . 'd' => lab58_git_date($t, $f['date'], $w->now), $k . 'r' => lab58_git_reldate($t, $w->now), $k . 't' => (string)$t, $k . 'i' => lab58_git_date($t, 'iso'), $k . 's' => lab58_git_date($t, 'short')];
    }
    return (string)preg_replace_callback('/%(C\([^)]*\)|C(?:red|green|blue|reset)|[ac][nedrtis]|[HhTtPpsbBdDn%])/', static fn(array $x): string => $x[1][0] === 'C' && strlen($x[1]) > 2 ? '' : (string)($v[$x[1]] ?? $x[0]), (string)$f['format']);
}

/** --graph: pořadí větev po větvi (jako --topo-order) a sloupce souběžných větví. @return array<string,array{0:string,1:string,2:string}> id → [sloupce s *, pokračování, spojení] */
function lab58_git_graph(Lab57World $w, array $r, array $list): array
{
    [$in, $lanes, $out] = [array_fill_keys($list, 0), [], []];
    foreach ($list as $id) foreach ((array)(lab58_git_obj($w, $r, $id)['parents'] ?? []) as $par) if (isset($in[$par])) $in[$par]++;
    for ($stack = array_reverse(array_keys($in, 0, true)); $stack !== [];) {
        $id = (string)array_pop($stack);
        if (($col = array_search($id, $lanes, true)) === false) $lanes[$col = count($lanes)] = $id;
        $star = implode(' ', array_map(static fn(int $i): string => $i === $col ? '*' : '|', array_keys($lanes)));
        $par = (string)(lab58_git_obj($w, $r, $id)['parents'][0] ?? '');
        $join = $par !== '' && in_array($par, $lanes, true) ? str_repeat('| ', max(0, $col - 1)) . '|/' : '';
        if ($par === '' || $join !== '') array_splice($lanes, $col, 1); else $lanes[$col] = $par;
        if (isset($in[$par]) && --$in[$par] === 0) $stack[] = $par;
        $out[$id] = [$star . ' ', $lanes === [] ? '  ' : str_repeat('| ', count($lanes)), $join];
    }
    return $out;
}

/** Jeden commit ve výpisu log/show (medium, oneline, short, full, format:) a volitelně jeho změny. */
function lab58_git_print_commit(Lab57Proc $p, array $r, string $id, array $f, bool $first): void
{
    $c = (array)lab58_git_obj($p->w, $r, $id);
    $who = static fn(array $x): string => $x['name'] . ' <' . $x['email'] . '>';
    [$star, $pre, $join] = $f['gp'][$id] ?? ['', '', ''];
    $dec = isset($f['dec'][$id]) ? ' (' . implode(', ', $f['dec'][$id]) . ')' : '';
    if ($f['format'] !== null) $p->line($star . lab58_git_format($p->w, $id, $c, $f));
    elseif ($f['oneline']) $p->line($star . ($f['short'] ? substr($id, 0, 7) : $id) . $dec . ' ' . lab58_git_subject($c));
    else {
        if (!$first) $p->line(rtrim(str_replace('*', '|', $star)));
        $p->line($star . 'commit ' . ($f['short'] ? substr($id, 0, 7) : $id) . $dec);
        $p->line($pre . 'Author: ' . $who((array)$c['author']));
        if ($f['pretty'] === 'medium') $p->line($pre . 'Date:   ' . lab58_git_date((int)$c['author']['time'], $f['date'], $p->w->now));
        $p->line(rtrim($pre));
        foreach ($f['pretty'] === 'short' ? [lab58_git_subject($c)] : explode("\n", (string)$c['message']) as $l) $p->line($l === '' ? rtrim($pre) : $pre . '    ' . $l);
    }
    [$old, $new] = [lab58_git_tree($p->w, $r, $c['parents'][0] ?? null), lab58_git_tree($p->w, $r, $id)];
    if ($f['diff'] !== '' && lab58_git_diff_trees($p, $r, $old, $new, $f['specs'], 'quiet') > 0) {
        if (!$f['oneline'] && $f['format'] === null) $p->line('');
        lab58_git_diff_trees($p, $r, $old, $new, $f['specs'], $f['diff']);
    }
    if ($join !== '') $p->line($join);
}

function lab58_git_cmd_log(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    $args = array_map(static fn($a): string => preg_match('/^-(\d+)$/', (string)$a, $m) === 1 ? '--max-count=' . $m[1] : (string)$a, $args);
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['n|max-count' => 1, 'oneline' => 0, 'p|u|patch' => 0, 'stat' => 0, 'name-only' => 0, 'name-status' => 0, 'all' => 0, 'author' => 1, 'grep' => 1, 'S' => 1, 'i|regexp-ignore-case' => 0,
        'decorate' => 0, 'no-decorate' => 0, 'graph' => 0, 'reverse' => 0, 'date' => 1, 'pretty|format' => 1, 'abbrev-commit' => 0, 'no-merges' => 0, 'follow' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'log', $err);
    if (($sp = lab58_git_revs_paths($p, $r, $ops, $after)) === null) return 128;
    [$revs, $specs] = $sp;
    $head = lab58_git_head($w, $r);
    if ($revs === [] && $head['id'] === null) return lab58_git_fatal($p, "your current branch '" . $head['branch'] . "' does not have any commits yet");
    [$include, $skip] = [[], []];
    foreach ($revs as $rev) {
        $range = explode('..', $rev, 2);
        if (count($range) === 2) $skip += lab58_git_reach($w, $r, lab58_git_resolve($w, $r, $range[0] === '' ? 'HEAD' : $range[0]));
        $include[] = lab58_git_resolve($w, $r, (string)(end($range) ?: 'HEAD'));
    }
    if (!empty($o['all'])) foreach (['refs/heads', 'refs/remotes', 'refs/tags'] as $prefix) foreach (lab58_git_refs($w, $r, $prefix) as $id) $include[] = lab58_git_peel($w, $r, $id);
    if ($revs === []) $include[] = $head['id'];
    [$re, $list] = [static fn(string $pat): ?string => lab57_regex($pat, 'bre', !empty($o['i'])), []];
    foreach (lab58_git_walk($w, $r, array_filter($include)) as $id) {
        $c = (array)lab58_git_obj($w, $r, $id);
        [$old, $new] = [lab58_git_tree($w, $r, $c['parents'][0] ?? null), lab58_git_tree($w, $r, $id)];
        if (isset($skip[$id]) || ($specs !== [] && lab58_git_diff_trees($p, $r, $old, $new, $specs, 'quiet') === 0)) continue;
        if (isset($o['author']) && @preg_match((string)$re((string)$o['author']), $c['author']['name'] . ' <' . $c['author']['email'] . '>') !== 1) continue;
        if (isset($o['grep']) && @preg_match((string)$re((string)$o['grep']), (string)$c['message']) !== 1) continue;
        if (isset($o['S']) && !lab58_git_pickaxe($w, $r, $old, $new, (string)$o['S'])) continue;
        $list[] = $id;
        if (count($list) >= (int)($o['n'] ?? PHP_INT_MAX)) break;
    }
    $f = lab58_git_fmt($p, $r, $o, false, $specs) + ['gp' => !empty($o['graph']) ? lab58_git_graph($w, $r, $list) : []];
    $list = !empty($o['reverse']) ? array_reverse($list) : ($f['gp'] !== [] ? array_map('strval', array_keys($f['gp'])) : $list);
    foreach ($list as $k => $id) lab58_git_print_commit($p, $r, $id, $f, $k === 0);
    return 0;
}

/** -S text: commit změnil počet výskytů textu v některém souboru. */
function lab58_git_pickaxe(Lab57World $w, array $r, array $old, array $new, string $needle): bool
{
    foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $path) if (substr_count((string)lab58_git_blob($w, $r, $old[$path] ?? null), $needle) !== substr_count((string)lab58_git_blob($w, $r, $new[$path] ?? null), $needle)) return true;
    return false;
}

function lab58_git_cmd_show(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['stat' => 0, 'name-only' => 0, 'name-status' => 0, 's|no-patch' => 0, 'p|patch' => 0, 'oneline' => 0, 'pretty|format' => 1, 'date' => 1, 'decorate' => 0, 'no-decorate' => 0, 'abbrev-commit' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'show', $err);
    $f = lab58_git_fmt($p, $r, $o, true, array_map(static fn(string $a): string => (string)lab58_git_relpath($w, $r, $a), (array)$after));
    foreach ($ops === [] ? ['HEAD'] : array_values($ops) as $k => $target) {
        if (str_contains($target, ':')) { if (!lab58_git_show_path($p, $r, $target)) return 128; continue; }
        $id = lab58_git_resolve($w, $r, $target, false);
        $obj = lab58_git_obj($w, $r, $id);
        if ($obj === null) return lab58_git_ambiguous($p, $target);
        if ($obj['type'] === 'tag') {
            $p->out('tag ' . $obj['tag'] . "\nTagger: " . $obj['tagger']['name'] . ' <' . $obj['tagger']['email'] . ">\nDate:   " . lab58_git_date((int)$obj['tagger']['time'], $f['date'], $w->now) . "\n\n" . $obj['message'] . "\n\n");
            $obj = lab58_git_obj($w, $r, $id = lab58_git_peel($w, $r, $id));
        }
        if (($obj['type'] ?? '') === 'blob') $p->out((string)lab58_git_blob($w, $r, $id));
        elseif (($obj['type'] ?? '') === 'commit') lab58_git_print_commit($p, $r, (string)$id, $f, $k === 0);
    }
    return 0;
}

/** git show rev:cesta (cesta od kořene, ./ od aktuální složky; prázdná revize = index). */
function lab58_git_show_path(Lab57Proc $p, array $r, string $target): bool
{
    $w = $p->w;
    [$rev, $path] = explode(':', $target, 2);
    $rel = preg_match('~^\.\.?(/|$)~', $path) === 1 ? (string)lab58_git_relpath($w, $r, $path) : trim($path, '/');
    $id = $rev === '' ? null : lab58_git_resolve($w, $r, $rev);
    if ($rev !== '' && $id === null) { $p->err("fatal: invalid object name '" . $rev . "'.\n"); return false; }
    $tree = $rev === '' ? lab58_git_index($w, $r) : lab58_git_tree($w, $r, $id);
    if (isset($tree[$rel])) { $p->out((string)lab58_git_blob($w, $r, $tree[$rel])); return true; }
    $onDisk = $r['root'] !== null && $w->fs->exists(lab58_git_join((string)$r['root'], $rel));
    $p->err("fatal: path '" . $rel . "' " . ($onDisk ? 'exists on disk, but not in' : 'does not exist in') . " '" . ($rev === '' ? 'the index' : $rev) . "'\n");
    return false;
}

function lab58_git_cmd_blame(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['L' => 1, 'e|show-email' => 0, 's' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'blame', $err);
    $ops = array_merge($ops, (array)$after);
    if ($ops === []) { $p->err("usage: git blame [<options>] [<rev-opts>] [<rev>] [--] <file>\n"); return 129; }
    $rel = (string)lab58_git_relpath($w, $r, (string)array_pop($ops));
    $id = lab58_git_resolve($w, $r, (string)($ops[0] ?? 'HEAD'));
    if ($id === null) return lab58_git_fatal($p, "bad revision '" . ($ops[0] ?? 'HEAD') . "'");
    $lines = lab58_git_blame_lines($w, $r, $id, $rel);
    if ($lines === null) return lab58_git_fatal($p, "no such path '" . $rel . "' in " . ($ops[0] ?? 'HEAD'));
    [$from, $to] = isset($o['L']) && preg_match('/^(\d+)(?:,(\+?)(\d+))?$/', (string)$o['L'], $m) === 1 ? [(int)$m[1], isset($m[3]) ? ($m[2] === '+' ? (int)$m[1] + (int)$m[3] - 1 : (int)$m[3]) : count($lines)] : [1, count($lines)];
    if (isset($o['L']) && ($from < 1 || $from > count($lines) || $to < $from)) return lab58_git_fatal($p, 'file ' . $rel . ' has only ' . count($lines) . ' lines');
    $name = static fn(array $c): string => !empty($o['e']) ? '<' . $c['author']['email'] . '>' : (string)$c['author']['name'];
    $show = array_slice($lines, $from - 1, min($to, count($lines)) - $from + 1, true);
    $width = max(array_map(static fn(array $x): int => mb_strwidth($name((array)lab58_git_obj($w, $r, $x[0]))), $show));
    foreach ($show as $i => [$cid, , $text]) {
        $c = (array)lab58_git_obj($w, $r, $cid);
        $hash = empty($c['parents']) ? '^' . substr($cid, 0, 7) : substr($cid, 0, 8);
        $who = empty($o['s']) ? ' (' . $name($c) . str_repeat(' ', $width - mb_strwidth($name($c))) . ' ' . lab58_git_date((int)$c['author']['time'], 'iso') : '';
        $p->line($hash . $who . ' ' . str_pad((string)($i + 1), strlen((string)$to), ' ', STR_PAD_LEFT) . ') ' . $text);
    }
    return 0;
}

function lab58_git_cmd_rev_parse(Lab57Proc $p, array $args, array $r): int
{
    [$w, $mode] = [$p->w, ''];
    foreach ($args as $a) {
        if (in_array($a, ['--short', '--abbrev-ref', '--verify', '-q', '--quiet', '--'], true)) { $mode = in_array($a, ['-q', '--quiet', '--'], true) ? $mode : $a; continue; }
        if ($a === '--show-toplevel' && $r['root'] === null) return lab58_git_fatal($p, 'this operation must be run in a work tree');
        $info = ['--show-toplevel' => $r['root'], '--git-dir' => $w->cwd === $r['root'] ? '.git' : $r['gd'], '--is-bare-repository' => $r['root'] === null ? 'true' : 'false',
            '--is-inside-work-tree' => $r['root'] !== null && !str_starts_with($w->cwd . '/', $r['gd'] . '/') ? 'true' : 'false', '--show-prefix' => ltrim(lab58_git_relpath($w, $r, '.') . '/', '/')];
        if (array_key_exists($a, $info)) { $p->line((string)$info[$a]); continue; }
        $id = lab58_git_resolve($w, $r, $a, false);
        if ($id === null) { if ($mode !== '--verify') $p->line($a); return $mode === '--verify' ? lab58_git_fatal($p, 'Needed a single revision') : lab58_git_ambiguous($p, $a); }
        $p->line($mode === '--short' ? substr($id, 0, 7) : ($mode === '--abbrev-ref' ? (in_array($a, ['HEAD', '@'], true) ? (lab58_git_head($w, $r)['branch'] ?? 'HEAD') : $a) : $id));
    }
    return 0;
}

function lab58_git_cmd_remote(Lab57Proc $p, array $args, array $r): int
{
    if (array_diff($args, ['-v', '--verbose']) !== []) { $p->err("error: simulace umí jen git remote a git remote -v\n"); return 1; }
    foreach (lab58_git_cfg_read($p->w, $r['gd'] . '/config') as [$k, $url]) if (preg_match('/^remote\.(.+)\.url$/i', (string)$k, $m) === 1) $p->out($args !== [] ? $m[1] . "\t" . $url . " (fetch)\n" . $m[1] . "\t" . $url . " (push)\n" : $m[1] . "\n");
    return 0;
}
