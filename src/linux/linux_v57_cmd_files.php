<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – příkazy pro soubory a složky.
 * Vše pracuje jen s virtuálním souborovým systémem (Lab57Vfs) v paměti.
 */

function lab57_cmds_files(): array
{
    return [
        'ls' => 'lab57_cmd_ls', 'cat' => 'lab57_cmd_cat', 'less' => 'lab57_cmd_less', 'more' => 'lab57_cmd_less',
        'head' => 'lab57_cmd_head', 'tail' => 'lab57_cmd_tail', 'touch' => 'lab57_cmd_touch', 'mkdir' => 'lab57_cmd_mkdir',
        'rmdir' => 'lab57_cmd_rmdir', 'rm' => 'lab57_cmd_rm', 'cp' => 'lab57_cmd_cp', 'mv' => 'lab57_cmd_mv',
        'file' => 'lab57_cmd_file', 'stat' => 'lab57_cmd_stat', 'find' => 'lab57_cmd_find', 'tree' => 'lab57_cmd_tree',
        'truncate' => 'lab57_cmd_truncate', 'nano' => 'lab57_cmd_nano', 'chmod' => 'lab57_cmd_chmod', 'chown' => 'lab57_cmd_chown',
    ];
}

// ---------------------------------------------------------------------------
// Sdílené pomocníky pro všechny příkazy
// ---------------------------------------------------------------------------

/**
 * Jednoduchý getopt. $spec: krátké volby, dvojtečka = volba s hodnotou ("n:c:").
 * $long: jméno => bere hodnotu? Uložení: $opts['x'] = true|hodnota, $opts['--jmeno'] = true|hodnota.
 * @return array{0:array<string,mixed>,1:list<string>,2:?string}
 */
function lab57_getopt(array $args, string $spec, array $long = []): array
{
    $opts = [];
    $operands = [];
    $n = count($args);
    for ($i = 0; $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '--') {
            foreach (array_slice($args, $i + 1) as $rest) $operands[] = (string)$rest;
            break;
        }
        if (str_starts_with($a, '--') && strlen($a) > 2) {
            $name = substr($a, 2);
            $value = null;
            if (str_contains($name, '=')) [$name, $value] = explode('=', $name, 2);
            if (!array_key_exists($name, $long)) return [$opts, $operands, "unrecognized option '--$name'"];
            if ($long[$name] && $value === null) {
                if ($i + 1 >= $n) return [$opts, $operands, "option '--$name' requires an argument"];
                $value = (string)$args[++$i];
            }
            $opts['--' . $name] = $value ?? true;
            continue;
        }
        if ($a !== '-' && str_starts_with($a, '-')) {
            $len = strlen($a);
            for ($j = 1; $j < $len; $j++) {
                $ch = $a[$j];
                $pos = $ch === ':' ? false : strpos($spec, $ch);
                if ($pos === false) return [$opts, $operands, "invalid option -- '$ch'"];
                if (($spec[$pos + 1] ?? '') === ':') {
                    $value = $j + 1 < $len ? substr($a, $j + 1) : ($args[++$i] ?? null);
                    if ($value === null) return [$opts, $operands, "option requires an argument -- '$ch'"];
                    $opts[$ch] = (string)$value;
                    break;
                }
                $opts[$ch] = true;
            }
            continue;
        }
        $operands[] = $a;
    }
    return [$opts, $operands, null];
}

function lab57_opt_error(Lab57Proc $p, string $error, int $code = 2): int
{
    $p->err($p->name . ': ' . $error . "\nTry '" . $p->name . " --help' for more information.\n");
    $p->w->tip(tr('Neznámá volba. Seznam voleb ukáže man {prikaz} nebo {prikaz} --help.', ['prikaz' => $p->name]));
    return $code;
}

function lab57_missing_operand(Lab57Proc $p, string $what = 'operand'): int
{
    $p->err($p->name . ': missing ' . $what . "\nTry '" . $p->name . " --help' for more information.\n");
    return 1;
}

/**
 * Načte vstupy příkazu: soubory, nebo stdin (pro '-' a když žádný soubor není).
 * @return array{0:list<array{0:string,1:string}>,1:int}
 */
function lab57_read_inputs(Lab57Proc $p, array $files): array
{
    if ($files === []) $files = ['-'];
    $out = [];
    $status = 0;
    foreach ($files as $file) {
        $file = (string)$file;
        if ($file === '-') { $out[] = ['-', $p->stdin]; continue; }
        $err = null;
        $content = $p->w->readFile($file, $err);
        if ($content === null) {
            $p->err($p->name . ': ' . $file . ': ' . $err . "\n");
            lab57_error_tip($p->w, (string)$err, $file);
            $status = 1;
            continue;
        }
        $out[] = [$file, $content];
    }
    return [$out, $status];
}

/** Bez souboru a bez roury by skutečný příkaz čekal na klávesnici – v simulaci to vysvětlíme. */
function lab57_needs_input(Lab57Proc $p, array $files): bool
{
    if ($files !== [] || $p->hasStdin) return false;
    $p->w->tip(tr('{prikaz} bez jména souboru čeká na text z klávesnice. V simulaci mu ho pošli rourou (např. cat soubor | {prikaz}) nebo napiš jméno souboru.', ['prikaz' => $p->name]));
    return true;
}

function lab57_blocks(array $node): int
{
    if (($node['t'] ?? '') === 'd') return 4;
    $size = Lab57Vfs::size($node);
    return $size === 0 ? 0 : (int)ceil($size / 4096) * 4;
}

function lab57_vis_len(string $text): int
{
    return mb_strlen((string)preg_replace('/\e\[[0-9;]*m/', '', $text), 'UTF-8');
}

/** Rozloží položky do sloupců jako ls na terminálu (řazení po sloupcích). */
function lab57_columns(array $items, int $width = 80): string
{
    $n = count($items);
    if ($n === 0) return '';
    $lens = array_map('lab57_vis_len', $items);
    $rows = 1;
    $colWidths = [];
    for ($rows = 1; $rows <= $n; $rows++) {
        $cols = (int)ceil($n / $rows);
        $colWidths = [];
        for ($c = 0; $c < $cols; $c++) {
            $max = 0;
            for ($r = 0; $r < $rows; $r++) {
                $i = $c * $rows + $r;
                if ($i < $n) $max = max($max, $lens[$i]);
            }
            $colWidths[$c] = $max;
        }
        if (array_sum($colWidths) + 2 * (count($colWidths) - 1) <= $width) break;
    }
    $rows = min($rows, $n);
    $lines = [];
    for ($r = 0; $r < $rows; $r++) {
        $line = '';
        foreach ($colWidths as $c => $cw) {
            $i = $c * $rows + $r;
            if ($i >= $n) continue;
            $hasNext = ($c + 1) * $rows + $r < $n;
            $line .= $items[$i] . ($hasNext ? str_repeat(' ', $cw - $lens[$i] + 2) : '');
        }
        $lines[] = $line;
    }
    return lab57_join($lines);
}

function lab57_ls_quote(string $name): string
{
    if ($name !== '' && preg_match('/^[\p{L}\p{N}._\-+,\/:@%^=]+$/u', $name) === 1) return $name;
    if (!str_contains($name, "'")) return "'" . $name . "'";
    return '"' . str_replace(['\\', '"', '$', '`'], ['\\\\', '\"', '\$', '\`'], $name) . '"';
}

function lab57_ls_color(string $name, array $node): string
{
    $mode = (int)($node['m'] ?? 0);
    if (($node['t'] ?? '') === 'd') {
        if (($mode & 01000) && ($mode & 0002)) return "\e[30;42m";
        if ($mode & 0002) return "\e[34;42m";
        return "\e[01;34m";
    }
    if (($mode & 0111) !== 0) return "\e[01;32m";
    if (preg_match('/\.(png|jpe?g|gif|svg|webp)$/i', $name) === 1) return "\e[01;35m";
    if (preg_match('/\.(tar|gz|tgz|zip|xz|bz2|7z|deb)$/i', $name) === 1) return "\e[01;31m";
    return '';
}

function lab57_ls_name(string $name, array $node, bool $color, bool $classify, bool $quote): string
{
    $shown = $quote ? lab57_ls_quote($name) : $name;
    $code = $color ? lab57_ls_color($name, $node) : '';
    $out = $code !== '' ? $code . $shown . "\e[0m" : $shown;
    if ($classify) {
        if (($node['t'] ?? '') === 'd') $out .= '/';
        elseif (((int)($node['m'] ?? 0) & 0111) !== 0) $out .= '*';
    }
    return $out;
}

// ---------------------------------------------------------------------------
// ls
// ---------------------------------------------------------------------------

function lab57_cmd_ls(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $paths, $error] = lab57_getopt(array_slice($argv, 1), 'lahARtSr1dFC', ['all' => false, 'almost-all' => false, 'human-readable' => false, 'color' => false, 'recursive' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $opt = [
        'all' => isset($o['a']) || isset($o['--all']),
        'almost' => isset($o['A']) || isset($o['--almost-all']),
        'long' => isset($o['l']),
        'human' => isset($o['h']) || isset($o['--human-readable']),
        'recursive' => isset($o['R']) || isset($o['--recursive']),
        'one' => isset($o['1']),
        'dirself' => isset($o['d']),
        'classify' => isset($o['F']),
        'sort' => isset($o['t']) ? 't' : (isset($o['S']) ? 'S' : 'n'),
        'reverse' => isset($o['r']),
        'color' => $p->tty,
        'quote' => $p->tty,
    ];
    if ($paths === []) $paths = ['.'];
    $status = 0;
    $files = [];
    $dirs = [];
    foreach ($paths as $path) {
        $abs = $w->abs($path);
        $node = $w->fs->get($abs);
        if ($node === null || !$w->canTraverse($abs)) {
            if ($node !== null) {
                $p->err("ls: cannot access '$path': Permission denied\n");
            } else {
                $p->err("ls: cannot access '$path': No such file or directory\n");
                lab57_error_tip($w, 'No such file or directory', $path);
            }
            $status = 2;
            continue;
        }
        if (($node['t'] ?? '') === 'd' && !$opt['dirself']) $dirs[] = [$path, $abs];
        else $files[] = [$path, $abs, $node];
    }
    $printed = false;
    if ($files !== []) {
        $p->out(lab57_ls_render($w, lab57_ls_sort($files, $opt), $opt, false));
        $printed = true;
    }
    $header = count($paths) > 1 || $opt['recursive'];
    foreach ($dirs as [$label, $abs]) {
        $status = max($status, lab57_ls_dir($p, $label, $abs, $opt, $header, $printed));
    }
    return $status;
}

function lab57_ls_sort(array $entries, array $opt): array
{
    usort($entries, static function (array $a, array $b) use ($opt): int {
        if ($opt['sort'] === 't') {
            $d = ((int)($b[2]['mt'] ?? 0)) <=> ((int)($a[2]['mt'] ?? 0));
            if ($d !== 0) return $d;
        } elseif ($opt['sort'] === 'S') {
            $d = Lab57Vfs::size($b[2]) <=> Lab57Vfs::size($a[2]);
            if ($d !== 0) return $d;
        }
        return lab57_name_cmp((string)$a[0], (string)$b[0]);
    });
    return $opt['reverse'] ? array_reverse($entries) : $entries;
}

function lab57_ls_dir(Lab57Proc $p, string $label, string $abs, array $opt, bool $header, bool &$printed): int
{
    $w = $p->w;
    $node = $w->fs->get($abs);
    if ($printed) $p->out("\n");
    $printed = true;
    if ($header) $p->out($label . ":\n");
    if ($node === null || !$w->can($node, 'r')) {
        $p->err("ls: cannot open directory '$label': Permission denied\n");
        lab57_error_tip($w, 'Permission denied', $label);
        return 2;
    }
    $entries = [];
    if ($opt['all']) {
        $entries[] = ['.', $abs, $node];
        $parent = Lab57Vfs::dirname($abs);
        $entries[] = ['..', $parent, $w->fs->get($parent) ?? $node];
    }
    foreach ($w->fs->children($abs) as $name) {
        if ($name[0] === '.' && !$opt['all'] && !$opt['almost']) continue;
        $childAbs = ($abs === '/' ? '' : $abs) . '/' . $name;
        $child = $w->fs->get($childAbs);
        if ($child !== null) $entries[] = [$name, $childAbs, $child];
    }
    $entries = lab57_ls_sort($entries, $opt);
    $p->out(lab57_ls_render($w, $entries, $opt, true));
    $status = 0;
    if ($opt['recursive']) {
        foreach ($entries as [$name, $childAbs, $child]) {
            if ($name === '.' || $name === '..' || ($child['t'] ?? '') !== 'd') continue;
            $childLabel = ($label === '/' ? '' : rtrim($label, '/')) . '/' . $name;
            $status = max($status, lab57_ls_dir($p, $childLabel, $childAbs, $opt, true, $printed));
        }
    }
    return $status;
}

function lab57_ls_render(Lab57World $w, array $entries, array $opt, bool $isDir): string
{
    if (!$opt['long']) {
        if ($entries === []) return '';
        $names = array_map(static fn(array $e): string => lab57_ls_name((string)$e[0], $e[2], $opt['color'], $opt['classify'], $opt['quote']), $entries);
        if (!$opt['color'] || $opt['one']) return lab57_join($names);
        return lab57_columns($names, 80);
    }
    $rows = [];
    $total = 0;
    $wl = $wu = $wg = $ws = 0;
    foreach ($entries as [$name, $abs, $node]) {
        $total += lab57_blocks($node);
        $links = 1;
        if (($node['t'] ?? '') === 'd') {
            $links = 2;
            foreach ($w->fs->children($abs) as $child) if ($w->fs->isDir(($abs === '/' ? '' : $abs) . '/' . $child)) $links++;
        }
        $size = Lab57Vfs::size($node);
        $row = [
            lab57_perm_string($node),
            (string)$links,
            (string)($node['u'] ?? 'root'),
            (string)($node['g'] ?? 'root'),
            $opt['human'] ? lab57_human_size($size) : (string)$size,
            lab57_ls_date((int)($node['mt'] ?? $w->now), $w->now),
            lab57_ls_name((string)$name, $node, $opt['color'], $opt['classify'], $opt['quote']),
        ];
        $wl = max($wl, strlen($row[1]));
        $wu = max($wu, strlen($row[2]));
        $wg = max($wg, strlen($row[3]));
        $ws = max($ws, strlen($row[4]));
        $rows[] = $row;
    }
    $lines = [];
    if ($isDir) $lines[] = 'total ' . ($opt['human'] ? lab57_human_size($total * 1024) : (string)$total);
    foreach ($rows as $r) {
        $lines[] = $r[0] . ' ' . str_pad($r[1], $wl, ' ', STR_PAD_LEFT) . ' ' . str_pad($r[2], $wu) . ' ' . str_pad($r[3], $wg) . ' ' . str_pad($r[4], $ws, ' ', STR_PAD_LEFT) . ' ' . $r[5] . ' ' . $r[6];
    }
    return lab57_join($lines);
}

// ---------------------------------------------------------------------------
// Čtení obsahu
// ---------------------------------------------------------------------------

function lab57_cmd_cat(Lab57Proc $p, array $argv): int
{
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'nbAEsT', ['number' => false, 'show-all' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $number = isset($o['n']) || isset($o['--number']);
    $nonblank = isset($o['b']);
    $showAll = isset($o['A']) || isset($o['--show-all']);
    $n = 0;
    foreach ($inputs as [, $content]) {
        if (!$number && !$nonblank && !$showAll && !isset($o['E']) && !isset($o['T'])) { $p->out($content); continue; }
        $lines = explode("\n", $content);
        $endsNl = str_ends_with($content, "\n");
        if ($endsNl) array_pop($lines);
        $out = [];
        foreach ($lines as $line) {
            if ($showAll || isset($o['T'])) $line = str_replace("\t", '^I', $line);
            if ($showAll || isset($o['E'])) $line .= '$';
            if ($nonblank && $line === '') { $out[] = ''; continue; }
            if ($number || $nonblank) { $n++; $line = str_pad((string)$n, 6, ' ', STR_PAD_LEFT) . "\t" . $line; }
            $out[] = $line;
        }
        $p->out(implode("\n", $out) . ($endsNl ? "\n" : ''));
    }
    return $status;
}

function lab57_cmd_less(Lab57Proc $p, array $argv): int
{
    [, $files, $error] = lab57_getopt(array_slice($argv, 1), 'NSR', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($files === [] && !$p->hasStdin) { $p->err("Missing filename (\"less --help\" for help)\n"); return 1; }
    [$inputs, $status] = lab57_read_inputs($p, $files);
    foreach ($inputs as [, $content]) $p->out($content);
    if ($p->tty) $p->w->tip(tr('{prikaz} v simulaci ukáže celý soubor najednou. Ve skutečném terminálu listuješ mezerníkem a končíš klávesou q.', ['prikaz' => $p->name]));
    return $status;
}

function lab57_headtail(Lab57Proc $p, array $argv, bool $head): int
{
    $args = array_slice($argv, 1);
    foreach ($args as $i => $a) {
        if (preg_match('/^-(\d+)$/', (string)$a, $m) === 1) $args[$i] = '-n' . $m[1];
    }
    [$o, $files, $error] = lab57_getopt($args, 'n:c:qvf', ['lines' => true, 'bytes' => true, 'follow' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $raw = (string)($o['n'] ?? $o['--lines'] ?? '10');
    $bytes = $o['c'] ?? $o['--bytes'] ?? null;
    $fromStart = !$head && str_starts_with($raw, '+');
    if (preg_match('/^[+-]?\d+$/', $raw) !== 1) { $p->err($p->name . ": invalid number of lines: ‘{$raw}’\n"); return 1; }
    $count = (int)$raw;
    if (!$head && !$fromStart) $count = abs($count);
    if (isset($o['f']) || isset($o['--follow'])) $p->w->tip(tr('tail -f by čekal na nové řádky. Simulace vypíše konec souboru a hned skončí.'));
    if (lab57_needs_input($p, $files)) return 0;
    [$inputs, $status] = lab57_read_inputs($p, $files);
    $multi = count($inputs) > 1 && !isset($o['q']);
    foreach ($inputs as $k => [$name, $content]) {
        if ($multi || isset($o['v'])) $p->out(($k > 0 ? "\n" : '') . '==> ' . ($name === '-' ? 'standard input' : $name) . " <==\n");
        if ($bytes !== null) {
            $b = (int)$bytes;
            $p->out($head ? substr($content, 0, max(0, $b)) : ($b > 0 ? substr($content, -$b) : ''));
            continue;
        }
        $lines = explode("\n", $content);
        $endsNl = str_ends_with($content, "\n");
        if ($endsNl) array_pop($lines);
        $total = count($lines);
        if ($head) {
            $sel = $count >= 0 ? array_slice($lines, 0, $count) : array_slice($lines, 0, max(0, $total + $count));
            $lastIncluded = count($sel) === $total;
        } else {
            $sel = $fromStart ? array_slice($lines, max(0, $count - 1)) : ($count === 0 ? [] : array_slice($lines, -$count));
            $lastIncluded = true;
        }
        if ($sel === []) continue;
        $p->out(implode("\n", $sel) . (($endsNl || !$lastIncluded) ? "\n" : ''));
    }
    return $status;
}

function lab57_cmd_head(Lab57Proc $p, array $argv): int { return lab57_headtail($p, $argv, true); }
function lab57_cmd_tail(Lab57Proc $p, array $argv): int { return lab57_headtail($p, $argv, false); }

// ---------------------------------------------------------------------------
// Vytváření, mazání, kopírování
// ---------------------------------------------------------------------------

function lab57_cmd_touch(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'acm', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($files === []) return lab57_missing_operand($p, 'file operand');
    $status = 0;
    foreach ($files as $file) {
        $abs = $w->abs($file);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node !== null) {
            if (!$w->root && (string)($node['u'] ?? '') !== $w->user && !$w->can($node, 'w')) {
                $p->err("touch: cannot touch '$file': Permission denied\n");
                $status = 1;
                continue;
            }
            $node['mt'] = $w->now;
            $w->fs->set($abs, $node);
            continue;
        }
        if (isset($o['c'])) continue;
        $err = null;
        if (!$w->writeFile($file, '', false, $err)) {
            $p->err("touch: cannot touch '$file': $err\n");
            lab57_error_tip($w, (string)$err, $file);
            $status = 1;
            continue;
        }
        // Nový soubor dostane práva podle umask (0666 & ~umask), stejně jako u skutečného touch.
        $created = $w->fs->get($abs);
        if ($created !== null) { $created['m'] = 0666 & ~lab57_umask($w); $w->fs->set($abs, lab57_inherit_setgid($w, $abs, $created)); }
    }
    return $status;
}

/**
 * Aktuální umask (výchozí práva pro nové soubory a složky). v58: builtin umask ho ukládá do $w->ext['umask'];
 * bez nastavení odpovídá výchozímu chování Debianu (uživatel 0002, správce 0022).
 */
function lab57_umask(Lab57World $w): int
{
    $m = $w->ext['umask'] ?? null;
    if (is_int($m)) return $m & 0777;
    return $w->root ? 022 : 002;
}

function lab57_new_dir_node(Lab57World $w, ?int $mode = null): array
{
    $owner = $w->effectiveUser();
    return ['t' => 'd', 'm' => $mode ?? (0777 & ~lab57_umask($w)), 'u' => $owner, 'g' => $w->primaryGroup($owner), 'mt' => $w->now];
}

/**
 * Dědění skupiny a setgid bitu z nadřazené složky (v58). Když má rodič nastavený setgid (02000),
 * nová položka získá jeho skupinu a nová složka i setgid bit – jako na skutečném Linuxu.
 * Redirekce > vytváří soubory přes writeFile (mimo tento soubor) a dědění zatím nepodporuje.
 */
/** Práva jako ls -l včetně setuid/setgid (s/S); idempotentní i po případném rozšíření lab57_mode_string v jádře. */
function lab57_perm_string(array $node): string
{
    $s = lab57_mode_string($node);
    $mode = (int)($node['m'] ?? 0);
    if (($mode & 04000) && in_array($s[3], ['x', '-'], true)) $s[3] = $s[3] === 'x' ? 's' : 'S';
    if (($mode & 02000) && in_array($s[6], ['x', '-'], true)) $s[6] = $s[6] === 'x' ? 's' : 'S';
    return $s;
}

function lab57_inherit_setgid(Lab57World $w, string $abs, array $node): array
{
    $parent = $w->fs->get(Lab57Vfs::dirname($abs));
    if ($parent === null || ((int)($parent['m'] ?? 0) & 02000) === 0) return $node;
    $node['g'] = (string)($parent['g'] ?? ($node['g'] ?? 'root'));
    if (($node['t'] ?? '') === 'd') $node['m'] = (int)($node['m'] ?? 0) | 02000;
    return $node;
}

function lab57_cmd_mkdir(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $dirs, $error] = lab57_getopt(array_slice($argv, 1), 'pvm:', ['parents' => false, 'verbose' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($dirs === []) return lab57_missing_operand($p);
    $parents = isset($o['p']) || isset($o['--parents']);
    $verbose = isset($o['v']) || isset($o['--verbose']);
    $mode = isset($o['m']) && preg_match('/^[0-7]{3,4}$/', (string)$o['m']) === 1 ? (int)octdec((string)$o['m']) : null;
    $status = 0;
    foreach ($dirs as $dir) {
        $abs = $w->abs($dir);
        if ($w->fs->exists($abs)) {
            if ($parents && $w->fs->isDir($abs)) continue;
            $p->err("mkdir: cannot create directory ‘{$dir}’: File exists\n");
            $status = 1;
            continue;
        }
        $chain = [];
        $cur = $abs;
        while (!$w->fs->exists($cur)) { $chain[] = $cur; $cur = Lab57Vfs::dirname($cur); }
        if (count($chain) > 1 && !$parents) {
            $p->err("mkdir: cannot create directory ‘{$dir}’: No such file or directory\n");
            $w->tip(tr('Nadřazená složka neexistuje. Celou cestu najednou vytvoří mkdir -p {cesta}', ['cesta' => $dir]));
            $status = 1;
            continue;
        }
        if (!$w->fs->isDir($cur)) { $p->err("mkdir: cannot create directory ‘{$dir}’: Not a directory\n"); $status = 1; continue; }
        foreach (array_reverse($chain) as $path) {
            $parent = Lab57Vfs::dirname($path);
            if (!$w->canTraverse($path) || !$w->canModifyDir($parent)) {
                $p->err("mkdir: cannot create directory ‘{$dir}’: Permission denied\n");
                lab57_error_tip($w, 'Permission denied', $parent);
                $status = 1;
                continue 2;
            }
            $w->fs->set($path, lab57_inherit_setgid($w, $path, lab57_new_dir_node($w, $path === $abs ? $mode : null)));
            if ($verbose) $p->line("mkdir: created directory '" . ($path === $abs ? $dir : $path) . "'");
        }
    }
    return $status;
}

function lab57_cmd_rmdir(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [, $dirs, $error] = lab57_getopt(array_slice($argv, 1), 'pv', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($dirs === []) return lab57_missing_operand($p);
    $status = 0;
    foreach ($dirs as $dir) {
        $abs = $w->abs($dir);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) { $p->err("rmdir: failed to remove '$dir': No such file or directory\n"); $status = 1; continue; }
        if (($node['t'] ?? '') !== 'd') { $p->err("rmdir: failed to remove '$dir': Not a directory\n"); $status = 1; continue; }
        if ($w->fs->children($abs) !== []) {
            $p->err("rmdir: failed to remove '$dir': Directory not empty\n");
            $w->tip(tr('rmdir maže jen prázdné složky. Složku i s obsahem smaže rm -r {cesta} (opatrně!).', ['cesta' => $dir]));
            $status = 1;
            continue;
        }
        if (!lab57_can_unlink($w, $abs, $node)) { $p->err("rmdir: failed to remove '$dir': Permission denied\n"); $status = 1; continue; }
        $w->fs->delete($abs);
    }
    return $status;
}

/** Smí efektivní uživatel smazat položku? (práva složky + sticky bit jako v /tmp) */
function lab57_can_unlink(Lab57World $w, string $abs, array $node): bool
{
    if ($w->root) return true;
    $parent = $w->fs->get(Lab57Vfs::dirname($abs));
    if ($parent === null || !$w->can($parent, 'w') || !$w->can($parent, 'x')) return false;
    if (((int)($parent['m'] ?? 0) & 01000) !== 0) {
        return (string)($node['u'] ?? '') === $w->user || (string)($parent['u'] ?? '') === $w->user;
    }
    return true;
}

function lab57_cmd_rm(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $targets, $error] = lab57_getopt(array_slice($argv, 1), 'rRfvid', ['recursive' => false, 'force' => false, 'verbose' => false, 'no-preserve-root' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $recursive = isset($o['r']) || isset($o['R']) || isset($o['--recursive']);
    $force = isset($o['f']) || isset($o['--force']);
    $verbose = isset($o['v']) || isset($o['--verbose']);
    if ($targets === []) return $force ? 0 : lab57_missing_operand($p);
    $status = 0;
    foreach ($targets as $target) {
        $abs = $w->abs($target);
        if ($abs === '/') {
            if ($recursive) {
                $p->err("rm: it is dangerous to operate recursively on '/'\nrm: use --no-preserve-root to override this failsafe\n");
                $w->tip(tr('Smazání celého systému laboratoř nedovolí ani v simulaci. Úroveň vrátíš do původního stavu příkazem reset.'));
            } else {
                $p->err("rm: cannot remove '/': Is a directory\n");
            }
            $status = 1;
            continue;
        }
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) {
            if (!$force) {
                $p->err("rm: cannot remove '$target': " . ($w->fs->exists($abs) ? 'Permission denied' : 'No such file or directory') . "\n");
                $status = 1;
            }
            continue;
        }
        if (($node['t'] ?? '') === 'd' && !$recursive) {
            $p->err("rm: cannot remove '$target': Is a directory\n");
            $w->tip(tr('Složku smažeš rm -r {cil}, prázdnou také rmdir {cil}.', ['cil' => $target]));
            $status = 1;
            continue;
        }
        if (!lab57_can_unlink($w, $abs, $node)) {
            $p->err("rm: cannot remove '$target': Permission denied\n");
            lab57_error_tip($w, 'Permission denied', $target);
            $status = 1;
            continue;
        }
        if (($node['t'] ?? '') === 'd') {
            foreach ($w->fs->tree($abs) as $path) {
                $n = $w->fs->get($path);
                if (($n['t'] ?? '') === 'd' && !$w->root && !($w->can($n, 'w') && $w->can($n, 'x') && $w->can($n, 'r'))) {
                    $p->err("rm: cannot remove '$path': Permission denied\n");
                    $status = 1;
                    continue 2;
                }
            }
            if ($verbose) foreach (array_reverse($w->fs->tree($abs)) as $path) $p->line(($w->fs->isDir($path) ? "removed directory '" : "removed '") . $path . "'");
            $w->fs->deleteTree($abs);
            continue;
        }
        $w->fs->delete($abs);
        if ($verbose) $p->line("removed '$target'");
    }
    return $status;
}

/** Zkopíruje/přesune strom. $move ponechá vlastníka a čas, kopie patří efektivnímu uživateli. */
function lab57_transfer_tree(Lab57World $w, string $src, string $dest, bool $move): void
{
    $owner = $w->effectiveUser();
    $paths = $w->fs->tree($src);
    foreach ($paths as $path) {
        $node = (array)$w->fs->get($path);
        $target = $dest . substr($path, strlen($src));
        if (!$move) {
            $node['u'] = $owner;
            $node['g'] = $w->primaryGroup($owner);
            $node['mt'] = $w->now;
            unset($node['x']);
            if (isset($node['s']) && !isset($node['c'])) $node['c'] = '';
        }
        $w->fs->set($target, $node);
    }
    if ($move) $w->fs->deleteTree($src);
}

function lab57_copy_move(Lab57Proc $p, array $argv, bool $move): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), $move ? 'vfin' : 'rRvapfin', ['recursive' => false, 'verbose' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $verbose = isset($o['v']) || isset($o['--verbose']);
    $recursive = $move || isset($o['r']) || isset($o['R']) || isset($o['a']) || isset($o['--recursive']);
    if (count($ops) === 0) return lab57_missing_operand($p, 'file operand');
    if (count($ops) === 1) {
        $p->err($p->name . ": missing destination file operand after '" . $ops[0] . "'\nTry '" . $p->name . " --help' for more information.\n");
        return 1;
    }
    $dest = (string)array_pop($ops);
    $destAbs = $w->abs($dest);
    $destIsDir = $w->fs->isDir($destAbs);
    if (count($ops) > 1 && !$destIsDir) { $p->err($p->name . ": target '$dest' is not a directory\n"); return 1; }
    $status = 0;
    foreach ($ops as $src) {
        $srcAbs = $w->abs($src);
        $node = $w->canTraverse($srcAbs) ? $w->fs->get($srcAbs) : null;
        if ($node === null) {
            $p->err($p->name . ": cannot stat '$src': No such file or directory\n");
            lab57_error_tip($w, 'No such file or directory', $src);
            $status = 1;
            continue;
        }
        $isDir = ($node['t'] ?? '') === 'd';
        if ($isDir && !$recursive) {
            $p->err("cp: -r not specified; omitting directory '$src'\n");
            $w->tip(tr('Složku zkopíruješ s volbou -r: cp -r {zdroj} {cil}', ['zdroj' => $src, 'cil' => $dest]));
            $status = 1;
            continue;
        }
        $target = $destIsDir ? ($destAbs === '/' ? '' : $destAbs) . '/' . Lab57Vfs::basename($srcAbs) : $destAbs;
        if ($target === $srcAbs) { $p->err($p->name . ": '$src' and '$dest' are the same file\n"); $status = 1; continue; }
        if ($isDir && str_starts_with($target . '/', $srcAbs . '/')) {
            $p->err($move ? "mv: cannot move '$src' to a subdirectory of itself, '$dest'\n" : "cp: cannot copy a directory, '$src', into itself, '$dest'\n");
            $status = 1;
            continue;
        }
        if (!$move && !$w->can($node, 'r')) { $p->err("cp: cannot open '$src' for reading: Permission denied\n"); $status = 1; continue; }
        if ($move && !lab57_can_unlink($w, $srcAbs, $node)) { $p->err("mv: cannot move '$src' to '$dest': Permission denied\n"); $status = 1; continue; }
        $targetNode = $w->fs->get($target);
        if ($targetNode !== null && ($targetNode['t'] ?? '') === 'd' && !$isDir) { $p->err($p->name . ": cannot overwrite directory '$dest' with non-directory\n"); $status = 1; continue; }
        if ($targetNode !== null && !$isDir && !$w->can($targetNode, 'w') && !$move) { $p->err("cp: cannot create regular file '$dest': Permission denied\n"); $status = 1; continue; }
        $parentAbs = Lab57Vfs::dirname($target);
        if (!$w->fs->isDir($parentAbs)) {
            $p->err($p->name . ": cannot " . ($move ? 'move' : 'create regular file') . " '$dest': No such file or directory\n");
            $status = 1;
            continue;
        }
        if (!$w->canModifyDir($parentAbs) || !$w->canTraverse($target)) {
            $p->err($p->name . ($move ? ": cannot move '$src' to '$dest': Permission denied\n" : ": cannot create regular file '$dest': Permission denied\n"));
            lab57_error_tip($w, 'Permission denied', $dest);
            $status = 1;
            continue;
        }
        if ($targetNode !== null && $isDir) $w->fs->deleteTree($target);
        lab57_transfer_tree($w, $srcAbs, $target, $move);
        if ($verbose) $p->line(($move ? 'renamed ' : '') . "'$src' -> '" . ($destIsDir ? rtrim($dest, '/') . '/' . Lab57Vfs::basename($srcAbs) : $dest) . "'");
    }
    return $status;
}

function lab57_cmd_cp(Lab57Proc $p, array $argv): int { return lab57_copy_move($p, $argv, false); }
function lab57_cmd_mv(Lab57Proc $p, array $argv): int { return lab57_copy_move($p, $argv, true); }

// ---------------------------------------------------------------------------
// Informace o souborech
// ---------------------------------------------------------------------------

function lab57_file_type(string $name, array $node): string
{
    if (($node['t'] ?? '') === 'd') return (((int)($node['m'] ?? 0) & 01000) ? 'sticky, ' : '') . 'directory';
    $c = (string)($node['c'] ?? '');
    if ($c === '' && !isset($node['s'])) return 'empty';
    if (str_starts_with($c, "\x7fELF")) return 'ELF 64-bit LSB pie executable, x86-64, version 1 (SYSV), dynamically linked, interpreter /lib64/ld-linux-x86-64.so.2, for GNU/Linux 3.2.0, stripped';
    if (str_starts_with($c, "\x89PNG")) return 'PNG image data, 64 x 64, 8-bit/color RGBA, non-interlaced';
    if (str_starts_with($c, "\x1f\x8b")) return 'gzip compressed data, from Unix';
    if (str_starts_with($c, '%PDF')) return 'PDF document, version 1.7';
    if (str_starts_with($c, "\xff\xd8\xff")) return 'JPEG image data, JFIF standard 1.01';
    if (!lab57_is_text($c)) return 'data';
    $ascii = preg_match('/[^\x00-\x7F]/', $c) !== 1;
    $charset = $ascii ? 'ASCII text' : 'Unicode text, UTF-8 text';
    $long = false;
    foreach (explode("\n", $c) as $line) if (strlen($line) > 300) { $long = true; break; }
    $suffix = $long ? ', with very long lines (' . max(array_map('strlen', explode("\n", $c))) . ')' : '';
    if (str_starts_with($c, '#!')) {
        $interp = str_contains(strtok($c, "\n") ?: '', 'bash') ? 'Bourne-Again shell script' : (str_contains(strtok($c, "\n") ?: '', 'python') ? 'Python script' : 'POSIX shell script');
        return $interp . ', ' . $charset . ' executable' . $suffix;
    }
    $trim = ltrim($c);
    if (stripos($trim, '<!doctype html') === 0 || stripos($trim, '<html') === 0) return 'HTML document, ' . $charset . $suffix;
    if (($trim[0] ?? '') === '{' && json_decode($c) !== null) return 'JSON text data';
    if (str_ends_with($name, '.csv')) return 'CSV text';
    return $charset . $suffix;
}

function lab57_cmd_file(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [, $files, $error] = lab57_getopt(array_slice($argv, 1), 'bL', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($files === []) { $p->err("Usage: file [-bchikLlNnprsSvzZ0] [--apple] [--extension] [--mime-encoding]\n            [--mime-type] [-e <testname>] [-F <separator>]  [-f <namefile>]\n            [-m <magicfiles>] [-P <parameter=value>] [--exclude-quiet]\n            <file> ...\n"); return 1; }
    foreach ($files as $file) {
        $abs = $w->abs($file);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) { $p->line($file . ": cannot open `" . $file . "' (No such file or directory)"); continue; }
        if (($node['t'] ?? '') !== 'd' && !$w->can($node, 'r')) { $p->line($file . ": regular file, no read permission"); continue; }
        // v58: rozšiřitelný filtr file_type (archivy, obrázky…). Neprázdný popis přebije výchozí detekci.
        $desc = '';
        if (($node['t'] ?? '') !== 'd' && function_exists('lab58_filter')) {
            $desc = (string)lab58_filter('file_type', '', ['path' => $abs, 'content' => (string)($node['c'] ?? ''), 'world' => $w]);
        }
        $p->line($file . ': ' . ($desc !== '' ? $desc : lab57_file_type(Lab57Vfs::basename($abs), $node)));
    }
    return 0;
}

function lab57_cmd_stat(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'c:L', ['format' => true]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($files === []) return lab57_missing_operand($p);
    $format = $o['c'] ?? $o['--format'] ?? null;
    $status = 0;
    foreach ($files as $file) {
        $abs = $w->abs($file);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) { $p->err("stat: cannot statx '$file': No such file or directory\n"); $status = 1; continue; }
        $size = Lab57Vfs::size($node);
        $type = ($node['t'] ?? '') === 'd' ? 'directory' : ($size === 0 ? 'regular empty file' : 'regular file');
        $mode = (int)($node['m'] ?? 0);
        $user = (string)($node['u'] ?? 'root');
        $group = (string)($node['g'] ?? 'root');
        $mt = (int)($node['mt'] ?? $w->now);
        if (is_string($format)) {
            $p->line(strtr($format, ['%a' => decoct($mode & 07777), '%A' => lab57_perm_string($node), '%U' => $user, '%G' => $group, '%u' => (string)$w->uid($user), '%g' => (string)$w->gid($group), '%s' => (string)$size, '%n' => $file, '%F' => $type, '%y' => date('Y-m-d H:i:s.000000000 O', $mt), '%Y' => (string)$mt, '%i' => (string)(100000 + crc32($abs) % 2000000), '%%' => '%']));
            continue;
        }
        $inode = 100000 + crc32($abs) % 2000000;
        $links = ($node['t'] ?? '') === 'd' ? 2 : 1;
        $time = date('Y-m-d H:i:s.000000000 O', $mt);
        $p->out("  File: $file\n");
        $p->out('  Size: ' . str_pad((string)$size, 10) . "\tBlocks: " . str_pad((string)(lab57_blocks($node) * 2), 10) . ' IO Block: 4096   ' . $type . "\n");
        $p->out('Device: 8,1' . "\tInode: " . str_pad((string)$inode, 11) . ' Links: ' . $links . "\n");
        $p->out('Access: (' . lab57_octal($mode) . '/' . lab57_perm_string($node) . ')  Uid: (' . str_pad((string)$w->uid($user), 5, ' ', STR_PAD_LEFT) . '/' . str_pad($user, 8, ' ', STR_PAD_LEFT) . ')   Gid: (' . str_pad((string)$w->gid($group), 5, ' ', STR_PAD_LEFT) . '/' . str_pad($group, 8, ' ', STR_PAD_LEFT) . ")\n");
        $p->out("Access: $time\nModify: $time\nChange: $time\n Birth: -\n");
    }
    return $status;
}

// ---------------------------------------------------------------------------
// find
// ---------------------------------------------------------------------------

function lab57_cmd_find(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_slice($argv, 1));
    $paths = [];
    while ($args !== [] && !str_starts_with($args[0], '-') && $args[0] !== '!' && $args[0] !== '(') $paths[] = (string)array_shift($args);
    if ($paths === []) $paths = ['.'];
    $global = ['maxdepth' => PHP_INT_MAX, 'mindepth' => 0];
    $tokens = [];
    $count = count($args);
    for ($i = 0; $i < $count; $i++) {
        $a = (string)$args[$i];
        if ($a === '-maxdepth' || $a === '-mindepth') {
            $v = $args[$i + 1] ?? null;
            if ($v === null || !ctype_digit((string)$v)) return $p->fail("missing argument to `$a'");
            $global[substr($a, 1)] = (int)$v;
            $i++;
            continue;
        }
        $tokens[] = $a;
    }
    $pos = 0;
    try {
        $expr = $tokens === [] ? null : lab57_find_or($tokens, $pos);
        if ($pos < count($tokens)) throw new RuntimeException("paths must precede expression: `" . $tokens[$pos] . "'");
    } catch (RuntimeException $e) {
        return $p->fail($e->getMessage());
    }
    $hasAction = $expr !== null && lab57_find_has_action($expr);
    $status = 0;
    $ctx = ['batch' => [], 'delete' => []];
    foreach ($paths as $start) {
        $abs = $w->abs($start);
        if (!$w->canTraverse($abs) || !$w->fs->exists($abs)) { $p->err("find: ‘{$start}’: No such file or directory\n"); $status = 1; continue; }
        lab57_find_walk($p, $abs, $start, 0, $expr, $global, $hasAction, $status, $ctx);
    }
    foreach ($ctx['batch'] as $template) {
        $argvExec = [];
        foreach ($template['argv'] as $part) {
            if ($part === '{}') { foreach ($template['files'] as $f) $argvExec[] = $f; continue; }
            $argvExec[] = $part;
        }
        if ($template['files'] !== []) lab57_subrun($p, $argvExec);
    }
    foreach (array_reverse($ctx['delete']) as [$abs, $disp]) {
        $node = $w->fs->get($abs);
        if ($node === null) continue;
        if (($node['t'] ?? '') === 'd' && $w->fs->children($abs) !== []) { $p->err("find: cannot delete ‘{$disp}’: Directory not empty\n"); $status = 1; continue; }
        if (!lab57_can_unlink($w, $abs, $node)) { $p->err("find: cannot delete ‘{$disp}’: Permission denied\n"); $status = 1; continue; }
        $w->fs->delete($abs);
    }
    return $status;
}

function lab57_find_or(array $t, int &$pos): array
{
    $left = lab57_find_and($t, $pos);
    while (($t[$pos] ?? null) === '-o' || ($t[$pos] ?? null) === '-or') {
        $pos++;
        $left = ['or', $left, lab57_find_and($t, $pos)];
    }
    return $left;
}

function lab57_find_and(array $t, int &$pos): array
{
    $left = lab57_find_not($t, $pos);
    while ($pos < count($t) && !in_array($t[$pos], ['-o', '-or', ')'], true)) {
        if ($t[$pos] === '-a' || $t[$pos] === '-and') $pos++;
        $left = ['and', $left, lab57_find_not($t, $pos)];
    }
    return $left;
}

function lab57_find_not(array $t, int &$pos): array
{
    if (($t[$pos] ?? null) === '!' || ($t[$pos] ?? null) === '-not') {
        $pos++;
        return ['not', lab57_find_not($t, $pos)];
    }
    return lab57_find_primary($t, $pos);
}

function lab57_find_primary(array $t, int &$pos): array
{
    $tok = $t[$pos] ?? null;
    if ($tok === null) throw new RuntimeException('expected an expression');
    $pos++;
    if ($tok === '(') {
        $inner = lab57_find_or($t, $pos);
        if (($t[$pos] ?? null) !== ')') throw new RuntimeException("invalid expression; I was expecting to find a ')' somewhere but did not see one.");
        $pos++;
        return $inner;
    }
    $withArg = ['-name', '-iname', '-type', '-size', '-user', '-group', '-perm', '-mtime', '-path', '-wholename', '-newer'];
    if (in_array($tok, $withArg, true)) {
        $arg = $t[$pos] ?? null;
        if ($arg === null) throw new RuntimeException("missing argument to `$tok'");
        $pos++;
        if ($tok === '-type' && !in_array($arg, ['f', 'd', 'l'], true)) throw new RuntimeException("Unknown argument to -type: $arg");
        if ($tok === '-size' && preg_match('/^[+-]?\d+[bcwkMG]?$/', (string)$arg) !== 1) throw new RuntimeException("invalid -size type `$arg'");
        return ['pred', $tok, (string)$arg];
    }
    if (in_array($tok, ['-empty', '-executable', '-readable', '-writable', '-true', '-false', '-print', '-delete'], true)) return ['pred', $tok, ''];
    if ($tok === '-exec' || $tok === '-ok') {
        $parts = [];
        while (($t[$pos] ?? null) !== null && $t[$pos] !== ';' && $t[$pos] !== '+') $parts[] = (string)$t[$pos++];
        $end = $t[$pos] ?? null;
        if ($end === null || $parts === []) throw new RuntimeException("missing argument to `-exec'");
        $pos++;
        return ['exec', $parts, $end === '+'];
    }
    throw new RuntimeException("unknown predicate `$tok'");
}

function lab57_find_has_action(array $e): bool
{
    return match ($e[0]) {
        'exec' => true,
        'pred' => in_array($e[1], ['-print', '-delete'], true),
        'not' => lab57_find_has_action($e[1]),
        default => lab57_find_has_action($e[1]) || lab57_find_has_action($e[2]),
    };
}

function lab57_find_eval(Lab57Proc $p, array $e, string $abs, string $disp, array $node, array &$ctx): bool
{
    $w = $p->w;
    switch ($e[0]) {
        case 'and': return lab57_find_eval($p, $e[1], $abs, $disp, $node, $ctx) && lab57_find_eval($p, $e[2], $abs, $disp, $node, $ctx);
        case 'or': return lab57_find_eval($p, $e[1], $abs, $disp, $node, $ctx) || lab57_find_eval($p, $e[2], $abs, $disp, $node, $ctx);
        case 'not': return !lab57_find_eval($p, $e[1], $abs, $disp, $node, $ctx);
        case 'exec':
            if ($e[2]) {
                $key = implode("\0", $e[1]);
                $ctx['batch'][$key] ??= ['argv' => $e[1], 'files' => []];
                $ctx['batch'][$key]['files'][] = $disp;
                return true;
            }
            $argvExec = array_map(static fn(string $part): string => str_replace('{}', $disp, $part), $e[1]);
            return lab57_subrun($p, $argvExec) === 0;
    }
    [, $pred, $arg] = $e;
    $name = Lab57Vfs::basename($abs === '/' ? '/' : $abs);
    $size = Lab57Vfs::size($node);
    switch ($pred) {
        case '-name': return lab57_glob_match($arg, $name);
        case '-iname': return lab57_glob_match(mb_strtolower($arg), mb_strtolower($name));
        case '-path':
        case '-wholename': return lab57_glob_match($arg, $disp);
        case '-type': return ($arg === 'd') === (($node['t'] ?? '') === 'd') && $arg !== 'l';
        case '-user': return (string)($node['u'] ?? '') === $arg;
        case '-group': return (string)($node['g'] ?? '') === $arg;
        case '-empty': return ($node['t'] ?? '') === 'd' ? $w->fs->children($abs) === [] : $size === 0;
        case '-executable': return $w->can($node, 'x');
        case '-readable': return $w->can($node, 'r');
        case '-writable': return $w->can($node, 'w');
        case '-true': return true;
        case '-false': return false;
        case '-print': $p->line($disp); return true;
        case '-delete': $ctx['delete'][] = [$abs, $disp]; return true;
        case '-newer':
            $ref = $w->fs->get($w->abs($arg));
            return $ref !== null && (int)($node['mt'] ?? 0) > (int)($ref['mt'] ?? 0);
        case '-mtime':
            preg_match('/^([+-]?)(\d+)$/', $arg, $m);
            $days = (int)floor(($w->now - (int)($node['mt'] ?? $w->now)) / 86400);
            $n = (int)($m[2] ?? 0);
            return match ($m[1] ?? '') { '+' => $days > $n, '-' => $days < $n, default => $days === $n };
        case '-perm':
            $mode = (int)($node['m'] ?? 0) & 07777;
            if (preg_match('/^([-\/]?)([0-7]{3,4})$/', $arg, $m) !== 1) return false;
            $want = (int)octdec($m[2]);
            return match ($m[1]) { '-' => ($mode & $want) === $want, '/' => ($mode & $want) !== 0, default => $mode === $want };
        case '-size':
            preg_match('/^([+-]?)(\d+)([bcwkMG]?)$/', $arg, $m);
            $unit = match ($m[3] ?? '') { 'c' => 1, 'w' => 2, 'k' => 1024, 'M' => 1048576, 'G' => 1073741824, default => 512 };
            $units = (int)ceil($size / $unit);
            $n = (int)($m[2] ?? 0);
            return match ($m[1] ?? '') { '+' => $units > $n, '-' => $units < $n, default => $units === $n };
    }
    return false;
}

function lab57_find_walk(Lab57Proc $p, string $abs, string $disp, int $depth, ?array $expr, array $g, bool $hasAction, int &$status, array &$ctx): void
{
    $w = $p->w;
    $node = $w->fs->get($abs);
    if ($node === null) return;
    if ($depth >= $g['mindepth'] && $depth <= $g['maxdepth']) {
        $match = $expr === null || lab57_find_eval($p, $expr, $abs, $disp, $node, $ctx);
        if ($match && !$hasAction) $p->line($disp);
    }
    if (($node['t'] ?? '') !== 'd' || $depth >= $g['maxdepth']) return;
    if (!$w->can($node, 'r') || !$w->can($node, 'x')) {
        $p->err("find: ‘{$disp}’: Permission denied\n");
        $status = 1;
        return;
    }
    foreach ($w->fs->children($abs) as $name) {
        $childAbs = ($abs === '/' ? '' : $abs) . '/' . $name;
        $childDisp = ($disp === '/' ? '' : rtrim($disp, '/')) . '/' . $name;
        lab57_find_walk($p, $childAbs, $childDisp, $depth + 1, $expr, $g, $hasAction, $status, $ctx);
    }
}

// ---------------------------------------------------------------------------
// tree
// ---------------------------------------------------------------------------

function lab57_cmd_tree(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $paths, $error] = lab57_getopt(array_slice($argv, 1), 'adL:fC', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    $root = $paths[0] ?? '.';
    $abs = $w->abs($root);
    $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
    if ($node === null || ($node['t'] ?? '') !== 'd') {
        $p->line($root . '  [error opening dir]');
        $p->line("\n0 directories, 0 files");
        return 2;
    }
    $opt = ['all' => isset($o['a']), 'dirs' => isset($o['d']), 'max' => isset($o['L']) ? max(1, (int)$o['L']) : PHP_INT_MAX, 'color' => $p->tty];
    $counts = ['d' => 0, 'f' => 0];
    $p->line($opt['color'] ? "\e[01;34m" . $root . "\e[0m" : $root);
    lab57_tree_walk($p, $abs, '', 1, $opt, $counts);
    $p->line('');
    $p->line($counts['d'] . ($counts['d'] === 1 ? ' directory' : ' directories') . ($opt['dirs'] ? '' : ', ' . $counts['f'] . ($counts['f'] === 1 ? ' file' : ' files')));
    return 0;
}

function lab57_tree_walk(Lab57Proc $p, string $abs, string $prefix, int $depth, array $opt, array &$counts): void
{
    $w = $p->w;
    $node = $w->fs->get($abs);
    if ($node === null || !$w->can($node, 'r')) return;
    $names = [];
    foreach ($w->fs->children($abs) as $name) {
        if ($name[0] === '.' && !$opt['all']) continue;
        $childAbs = ($abs === '/' ? '' : $abs) . '/' . $name;
        if ($opt['dirs'] && !$w->fs->isDir($childAbs)) continue;
        $names[] = $name;
    }
    usort($names, 'lab57_name_cmp');
    $last = count($names) - 1;
    foreach ($names as $i => $name) {
        $childAbs = ($abs === '/' ? '' : $abs) . '/' . $name;
        $child = (array)$w->fs->get($childAbs);
        $isDir = ($child['t'] ?? '') === 'd';
        $isDir ? $counts['d']++ : $counts['f']++;
        $p->line($prefix . ($i === $last ? '└── ' : '├── ') . lab57_ls_name($name, $child, $opt['color'], false, false));
        if ($isDir && $depth < $opt['max']) lab57_tree_walk($p, $childAbs, $prefix . ($i === $last ? '    ' : '│   '), $depth + 1, $opt, $counts);
    }
}

// ---------------------------------------------------------------------------
// truncate, nano, chmod, chown
// ---------------------------------------------------------------------------

function lab57_parse_size(string $raw): ?int
{
    if (preg_match('/^(\d+)([KMG]?)(i?B)?$/i', $raw, $m) !== 1) return null;
    $mult = match (strtoupper($m[2])) { 'K' => 1024, 'M' => 1048576, 'G' => 1073741824, default => 1 };
    return (int)$m[1] * $mult;
}

function lab57_cmd_truncate(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 's:c', ['size' => true]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $raw = $o['s'] ?? $o['--size'] ?? null;
    if ($raw === null) { $p->err("truncate: you must specify either '--size' or '--reference'\nTry 'truncate --help' for more information.\n"); return 1; }
    $size = lab57_parse_size((string)$raw);
    if ($size === null) { $p->err("truncate: Invalid number: ‘{$raw}’\n"); return 1; }
    if ($files === []) return lab57_missing_operand($p, 'file operand');
    $status = 0;
    foreach ($files as $file) {
        $abs = $w->abs($file);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) {
            if (isset($o['c'])) continue;
            $err = null;
            if (!$w->writeFile($file, '', false, $err)) { $p->err("truncate: cannot open '$file' for writing: $err\n"); $status = 1; continue; }
            $node = (array)$w->fs->get($abs);
        }
        if (($node['t'] ?? '') === 'd') { $p->err("truncate: cannot open '$file' for writing: Is a directory\n"); $status = 1; continue; }
        if (!$w->can($node, 'w')) { $p->err("truncate: cannot open '$file' for writing: Permission denied\n"); lab57_error_tip($w, 'Permission denied', $file); $status = 1; continue; }
        $content = (string)($node['c'] ?? '');
        if ($size === 0) { $node['c'] = ''; unset($node['s']); }
        elseif ($size <= strlen($content)) { $node['c'] = substr($content, 0, $size); unset($node['s']); }
        else { $node['s'] = $size; }
        $node['mt'] = $w->now;
        $w->fs->set($abs, $node);
    }
    return $status;
}

function lab57_cmd_nano(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $files = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '-') && !str_starts_with($a, '+')));
    if ($files === []) {
        $p->err("nano: v simulaci otevři editor se jménem souboru, např. nano poznamky.txt\n");
        return 1;
    }
    $file = $files[0];
    $abs = $w->abs($file);
    $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
    if ($node !== null && ($node['t'] ?? '') === 'd') { $p->err("nano: \"$file\" is a directory\n"); return 1; }
    if ($node !== null && !$w->can($node, 'r')) {
        $p->err("nano: Error reading $file: Permission denied\n");
        lab57_error_tip($w, 'Permission denied', $file);
        return 1;
    }
    $content = $node !== null ? (string)($node['c'] ?? '') : '';
    if ($node !== null && !lab57_is_text($content)) { $p->err("nano: $file: soubor není text – editor ho v simulaci neotevře\n"); return 1; }
    $writable = $node !== null ? $w->can($node, 'w') : $w->canModifyDir(Lab57Vfs::dirname($abs));
    $w->effects['editor'] = ['path' => $abs, 'name' => $file, 'content' => $content, 'root' => $w->root, 'new' => $node === null, 'writable' => $writable];
    $p->line('[ Otevírám ' . $file . ' v editoru nano… ]');
    return 0;
}

/** Použije symbolický nebo osmičkový zápis práv. Vrací null při neplatném zápisu. */
function lab57_chmod_apply(int $mode, string $spec, bool $isDir): ?int
{
    if (preg_match('/^[0-7]{1,4}$/', $spec) === 1) return (int)octdec($spec);
    foreach (explode(',', $spec) as $clause) {
        if (preg_match('/^([ugoa]*)([-+=])([rwxXst]*)$/', $clause, $m) !== 1) return null;
        $who = $m[1] === '' ? 'a' : $m[1];
        $mask = 0;
        foreach (str_split($who) as $c) $mask |= match ($c) { 'u' => 04700, 'g' => 02070, 'o' => 01007, default => 07777 };
        $val = 0;
        if (str_contains($m[3], 'r')) $val |= 0444;
        if (str_contains($m[3], 'w')) $val |= 0222;
        if (str_contains($m[3], 'x') || (str_contains($m[3], 'X') && ($isDir || ($mode & 0111)))) $val |= 0111;
        if (str_contains($m[3], 's')) $val |= 06000;
        if (str_contains($m[3], 't')) $val |= 01000;
        $val &= $mask;
        $mode = match ($m[2]) {
            '+' => $mode | $val,
            '-' => $mode & ~$val,
            default => ($mode & ~($mask & 0777)) | $val,
        };
    }
    return $mode & 07777;
}

function lab57_cmd_chmod(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $recursive = false;
    $verbose = false;
    $rest = [];
    foreach ($args as $a) {
        if ($a === '-R' || $a === '--recursive') { $recursive = true; continue; }
        if ($a === '-v' || $a === '--verbose') { $verbose = true; continue; }
        $rest[] = $a;
    }
    if ($rest === []) return lab57_missing_operand($p);
    if (count($rest) === 1) { $p->err("chmod: missing operand after ‘{$rest[0]}’\nTry 'chmod --help' for more information.\n"); return 1; }
    $spec = (string)array_shift($rest);
    $status = 0;
    foreach ($rest as $file) {
        $abs = $w->abs($file);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) { $p->err("chmod: cannot access '$file': No such file or directory\n"); lab57_error_tip($w, 'No such file or directory', $file); $status = 1; continue; }
        foreach ($recursive ? $w->fs->tree($abs) : [$abs] as $path) {
            $n = (array)$w->fs->get($path);
            if (!$w->root && (string)($n['u'] ?? '') !== $w->user) {
                $p->err("chmod: changing permissions of '" . ($path === $abs ? $file : $path) . "': Operation not permitted\n");
                $w->tip(tr('Práva může měnit jen vlastník souboru nebo správce (sudo chmod …).'));
                $status = 1;
                continue;
            }
            $old = (int)($n['m'] ?? 0);
            $new = lab57_chmod_apply($old, $spec, ($n['t'] ?? '') === 'd');
            if ($new === null) { $p->err("chmod: invalid mode: ‘{$spec}’\nTry 'chmod --help' for more information.\n"); return 1; }
            $n['m'] = $new;
            $w->fs->set($path, $n);
            if ($verbose) $p->line("mode of '" . ($path === $abs ? $file : $path) . "' changed from " . lab57_octal($old) . ' (' . substr(lab57_mode_string(['m' => $old, 't' => $n['t'] ?? 'f']), 1) . ') to ' . lab57_octal($new) . ' (' . substr(lab57_mode_string($n), 1) . ')');
        }
    }
    return $status;
}

function lab57_cmd_chown(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $recursive = false;
    $rest = [];
    foreach ($args as $a) {
        if ($a === '-R' || $a === '--recursive') { $recursive = true; continue; }
        if ($a === '-v') continue;
        $rest[] = $a;
    }
    if (count($rest) < 2) return lab57_missing_operand($p);
    $spec = (string)array_shift($rest);
    [$user, $group] = array_pad(explode(':', $spec, 2), 2, null);
    if ($user !== '' && $user !== null && !isset($w->users[$user])) { $p->err("chown: invalid user: ‘{$spec}’\n"); return 1; }
    if ($group !== null && $group !== '' && !isset($w->groups[$group])) { $p->err("chown: invalid group: ‘{$spec}’\n"); return 1; }
    $status = 0;
    foreach ($rest as $file) {
        $abs = $w->abs($file);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        if ($node === null) { $p->err("chown: cannot access '$file': No such file or directory\n"); $status = 1; continue; }
        if (!$w->root) {
            $p->err("chown: changing ownership of '$file': Operation not permitted\n");
            $w->tip(tr('Vlastníka souboru mění jen správce systému: sudo chown {spec} {soubor}', ['spec' => $spec, 'soubor' => $file]));
            $status = 1;
            continue;
        }
        foreach ($recursive ? $w->fs->tree($abs) : [$abs] as $path) {
            $n = (array)$w->fs->get($path);
            if ($user !== null && $user !== '') $n['u'] = $user;
            if ($group !== null && $group !== '') $n['g'] = $group;
            $w->fs->set($path, $n);
        }
    }
    return $status;
}
