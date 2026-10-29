<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – vestavěné příkazy shellu a informace o systému.
 */

function lab57_cmds_shell(): array
{
    return [
        'pwd' => 'lab57_cmd_pwd', 'cd' => 'lab57_cmd_cd', 'echo' => 'lab57_cmd_echo', 'printf' => 'lab57_cmd_printf',
        'clear' => 'lab57_cmd_clear', 'history' => 'lab57_cmd_history', 'man' => 'lab57_cmd_man', 'help' => 'lab57_cmd_help',
        'env' => 'lab57_cmd_env', 'printenv' => 'lab57_cmd_env', 'export' => 'lab57_cmd_export', 'unset' => 'lab57_cmd_unset',
        'alias' => 'lab57_cmd_alias', 'unalias' => 'lab57_cmd_unalias', 'which' => 'lab57_cmd_which', 'type' => 'lab57_cmd_type',
        'sudo' => 'lab57_cmd_sudo', 'exit' => 'lab57_cmd_exit', 'logout' => 'lab57_cmd_exit', 'true' => 'lab57_cmd_true',
        'false' => 'lab57_cmd_false', 'sleep' => 'lab57_cmd_sleep', 'source' => 'lab57_cmd_source', '.' => 'lab57_cmd_source',
        'bash' => 'lab57_cmd_bash', 'sh' => 'lab57_cmd_bash', 'test' => 'lab57_cmd_test', '[' => 'lab57_cmd_test',
        'whoami' => 'lab57_cmd_whoami', 'id' => 'lab57_cmd_id', 'groups' => 'lab57_cmd_groups', 'hostname' => 'lab57_cmd_hostname',
        'uname' => 'lab57_cmd_uname', 'date' => 'lab57_cmd_date', 'uptime' => 'lab57_cmd_uptime', 'cowsay' => 'lab57_cmd_cowsay',
        'xargs' => 'lab57_cmd_xargs',
    ];
}

function lab57_cmd_pwd(Lab57Proc $p, array $argv): int
{
    $p->line($p->w->cwd);
    return 0;
}

function lab57_cmd_cd(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => $a !== '-L' && $a !== '-P'));
    if (count($args) > 1) { $p->err("bash: cd: too many arguments\n"); return 1; }
    $target = $args[0] ?? (string)($w->envAll()['HOME'] ?? $w->home());
    if ($target === '-') {
        $target = $w->oldCwd;
        $p->line($target);
    }
    $abs = $w->abs($target);
    $node = $w->fs->get($abs);
    if ($node === null) {
        $p->err("bash: cd: $target: No such file or directory\n");
        lab57_error_tip($w, 'No such file or directory', $target);
        return 1;
    }
    if (($node['t'] ?? '') !== 'd') { $p->err("bash: cd: $target: Not a directory\n"); return 1; }
    if (!$w->canTraverse($abs) || !$w->can($node, 'x')) {
        $p->err("bash: cd: $target: Permission denied\n");
        lab57_error_tip($w, 'Permission denied', $target);
        return 1;
    }
    $w->oldCwd = $w->cwd;
    $w->cwd = $abs;
    return 0;
}

/** Rozbalí escape sekvence jako echo -e / printf. */
function lab57_unescape(string $text, bool &$stop = false): string
{
    $out = '';
    $n = strlen($text);
    for ($i = 0; $i < $n; $i++) {
        $c = $text[$i];
        if ($c !== '\\' || $i + 1 >= $n) { $out .= $c; continue; }
        $next = $text[++$i];
        switch ($next) {
            case 'n': $out .= "\n"; break;
            case 't': $out .= "\t"; break;
            case 'r': $out .= "\r"; break;
            case '\\': $out .= '\\'; break;
            case 'a': $out .= "\x07"; break;
            case 'b': $out .= "\x08"; break;
            case 'e': $out .= "\e"; break;
            case 'c': $stop = true; return $out;
            case 'x':
                if (preg_match('/[0-9A-Fa-f]{1,2}/A', $text, $m, 0, $i + 1) === 1) { $out .= chr((int)hexdec($m[0])); $i += strlen($m[0]); }
                else $out .= '\\x';
                break;
            case '0':
                preg_match('/[0-7]{0,3}/A', $text, $m, 0, $i + 1);
                $out .= chr((int)octdec($m[0] === '' ? '0' : $m[0]) & 0xFF);
                $i += strlen($m[0]);
                break;
            default: $out .= '\\' . $next;
        }
    }
    return $out;
}

function lab57_cmd_echo(Lab57Proc $p, array $argv): int
{
    $args = array_slice($argv, 1);
    $newline = true;
    $escapes = false;
    while ($args !== [] && preg_match('/^-[neE]+$/', $args[0]) === 1) {
        $flag = (string)array_shift($args);
        if (str_contains($flag, 'n')) $newline = false;
        if (str_contains($flag, 'e')) $escapes = true;
        if (str_contains($flag, 'E')) $escapes = false;
    }
    $text = implode(' ', $args);
    $stop = false;
    if ($escapes) $text = lab57_unescape($text, $stop);
    $p->out($text . ($newline && !$stop ? "\n" : ''));
    return 0;
}

function lab57_cmd_printf(Lab57Proc $p, array $argv): int
{
    if (count($argv) < 2) { $p->err("printf: usage: printf [-v var] format [arguments]\n"); return 2; }
    $format = (string)$argv[1];
    $args = array_slice($argv, 2);
    $out = '';
    $guard = 0;
    do {
        $used = 0;
        $stop = false;
        $chunk = preg_replace_callback('/%([-+ 0#]*)(\d*)(?:\.(\d+))?([sdifxXoc%b])/', static function (array $m) use (&$args, &$used): string {
            if ($m[4] === '%') return '%';
            $arg = $args === [] ? '' : (string)array_shift($args);
            $used++;
            $spec = '%' . $m[1] . $m[2] . ($m[3] !== '' ? '.' . $m[3] : '');
            return match ($m[4]) {
                's' => sprintf($spec . 's', $arg),
                'b' => lab57_unescape($arg),
                'c' => $arg === '' ? '' : mb_substr($arg, 0, 1),
                'd', 'i' => sprintf($spec . 'd', (int)$arg),
                'f' => sprintf($spec . 'f', (float)$arg),
                'x' => sprintf($spec . 'x', (int)$arg),
                'X' => sprintf($spec . 'X', (int)$arg),
                'o' => sprintf($spec . 'o', (int)$arg),
                default => '',
            };
        }, $format) ?? $format;
        $out .= lab57_unescape($chunk, $stop);
        $guard++;
    } while ($args !== [] && $used > 0 && !$stop && $guard < 200);
    $p->out($out);
    return 0;
}

function lab57_cmd_clear(Lab57Proc $p, array $argv): int
{
    $p->w->effects['clear'] = true;
    return 0;
}

function lab57_cmd_history(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $arg = $argv[1] ?? null;
    if ($arg === '-c') { $w->history = []; return 0; }
    $entries = $w->history;
    $start = 0;
    if ($arg !== null) {
        if (!ctype_digit($arg)) { $p->err("bash: history: $arg: numeric argument required\n"); return 1; }
        $start = max(0, count($entries) - (int)$arg);
    }
    for ($i = $start, $n = count($entries); $i < $n; $i++) $p->line(str_pad((string)($i + 1), 5, ' ', STR_PAD_LEFT) . '  ' . $entries[$i]);
    return 0;
}

/** Krátká nápověda z příručky pro „příkaz --help“. */
function lab57_manual_help(Lab57Proc $p, string $name): bool
{
    if (!function_exists('v57_manual')) return false;
    $cmd = v57_manual()['commands'][$name] ?? null;
    if (!is_array($cmd)) return false;
    $p->line('Usage: ' . (string)$cmd['synopsis']);
    $p->line((string)$cmd['summary']);
    if ($cmd['options'] !== []) {
        $p->line('');
        foreach ((array)$cmd['options'] as [$flag, $text]) $p->line('  ' . str_pad((string)$flag, 14) . ' ' . $text);
    }
    $p->line('');
    $p->line('Celá nápověda: man ' . $name);
    return true;
}

function lab57_wrap(string $text, int $indent, int $width = 76): string
{
    $pad = str_repeat(' ', $indent);
    $lines = [];
    foreach (explode("\n", $text) as $paragraph) {
        $words = preg_split('/\s+/u', trim($paragraph)) ?: [];
        $line = '';
        foreach ($words as $word) {
            if ($line !== '' && mb_strlen($line . ' ' . $word) > $width - $indent) { $lines[] = $pad . $line; $line = $word; continue; }
            $line = $line === '' ? $word : $line . ' ' . $word;
        }
        if ($line !== '') $lines[] = $pad . $line;
    }
    return lab57_join($lines);
}

function lab57_cmd_man(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    if ($args === []) { $p->err("What manual page do you want?\nFor example, try 'man man'.\n"); return 1; }
    $manual = function_exists('v57_manual') ? v57_manual() : ['commands' => [], 'concepts' => []];
    if ($args[0] === '-k' || $args[0] === 'apropos') {
        $term = mb_strtolower((string)($args[1] ?? ''));
        $found = 0;
        foreach ((array)$manual['commands'] as $name => $cmd) {
            if ($term !== '' && !str_contains(mb_strtolower($name . ' ' . $cmd['summary']), $term)) continue;
            $p->line(str_pad($name . ' (1)', 16) . '- ' . $cmd['summary']);
            $found++;
        }
        if ($found === 0) { $p->line($term . ': nothing appropriate.'); return 16; }
        return 0;
    }
    $name = (string)end($args);
    if ($name === 'man') {
        $p->out("MAN(1)                    Příručka EDUCANET                    MAN(1)\n\nNÁZEV\n       man - zobrazí příručku k příkazu\n\nPOUŽITÍ\n       man příkaz\n       man -k slovo     hledá příkazy podle popisu\n");
        return 0;
    }
    $cmd = $manual['commands'][$name] ?? null;
    if (!is_array($cmd)) {
        $p->err("No manual entry for $name\n");
        $suggest = lab57_suggest($name, array_keys((array)$manual['commands']));
        if ($suggest !== null) $w->tip(tr('Nemyslel(a) jsi man {navrh}?', ['navrh' => $suggest]));
        return 16;
    }
    $title = strtoupper($name) . '(1)';
    $head = $title . str_repeat(' ', max(2, 26 - strlen($title))) . 'Příručka EDUCANET' . str_repeat(' ', max(2, 26 - strlen($title))) . $title;
    $out = $head . "\n\nNÁZEV\n       " . $name . ' - ' . $cmd['summary'] . "\n\nPOUŽITÍ\n       " . $cmd['synopsis'] . "\n\nPOPIS\n" . lab57_wrap((string)$cmd['about'], 7);
    if ($cmd['options'] !== []) {
        $out .= "\nVOLBY\n";
        foreach ((array)$cmd['options'] as [$flag, $text]) $out .= '       ' . $flag . "\n" . lab57_wrap((string)$text, 14);
    }
    if ($cmd['examples'] !== []) {
        $out .= "\nPŘÍKLADY\n";
        foreach ((array)$cmd['examples'] as $example) $out .= '       $ ' . $example[0] . "\n" . lab57_wrap((string)$example[1], 14);
    }
    if (!empty($cmd['tldr'])) {
        $out .= "\nRYCHLÉ PŘÍKLADY\n";
        foreach ((array)$cmd['tldr'] as $example) $out .= '       $ ' . $example[0] . "\n" . lab57_wrap((string)($example[1] ?? ''), 14);
    }
    if (!empty($cmd['tip'])) $out .= "\nTIP\n" . lab57_wrap((string)$cmd['tip'], 7);
    if (!empty($cmd['warn'])) $out .= "\nPOZOR\n" . lab57_wrap((string)$cmd['warn'], 7);
    $related = array_values(array_unique(array_merge((array)$cmd['related'], (array)($cmd['see_also'] ?? []))));
    if ($related !== []) $out .= "\nSOUVISEJÍCÍ\n       " . implode(', ', $related) . "\n";
    // v58: o dostupnosti rozhoduje registr příkazů (v57 položky manuálu mohou mít zastaralé in_lab).
    if (empty($cmd['in_lab']) && !isset(lab57_command_registry()[$name])) $out .= "\nPOZNÁMKA\n       Tento příkaz simulace laboratoře nespouští – stránka slouží jako přehled.\n";
    $p->out($out);
    $w->effects['manual'] = $name;
    return 0;
}

function lab57_cmd_help(Lab57Proc $p, array $argv): int
{
    $manual = function_exists('v57_manual') ? v57_manual() : ['categories' => [], 'commands' => []];
    $registry = lab57_command_registry();
    $p->line('EDUCANET Linux Lab – simulovaný bash. Nic se nespouští doopravdy, klidně zkoušej.');
    $p->line('');
    foreach ((array)$manual['categories'] as $key => $cat) {
        $names = [];
        foreach ((array)$manual['commands'] as $name => $cmd) {
            if (($cmd['cat'] ?? '') === $key && isset($registry[$name])) $names[] = $name;
        }
        if ($names === []) continue;
        $p->line(str_pad((string)$cat['label'], 26) . implode(' ', $names));
    }
    $p->line('');
    $p->line('Shell umí: roury |, přesměrování > >> < 2> 2>&1, && || ;, proměnné $HOME, $(…), $((…)), ~, * ? [abc]');
    $p->line('Klávesy: ↑/↓ historie · Tab doplňování · Ctrl+L smaže obrazovku · Ctrl+C zruší řádek');
    $p->line('Nápověda k příkazu: man <příkaz>   ·   hledání: man -k <slovo>');
    return 0;
}

function lab57_cmd_env(Lab57Proc $p, array $argv): int
{
    $env = $p->w->envAll();
    ksort($env);
    if (isset($argv[1]) && $argv[0] === 'printenv') {
        if (!isset($env[$argv[1]])) return 1;
        $p->line($env[$argv[1]]);
        return 0;
    }
    foreach ($env as $key => $value) $p->line($key . '=' . $value);
    return 0;
}

function lab57_cmd_export(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    if ($args === [] || $args === ['-p']) {
        $env = $w->envAll();
        ksort($env);
        foreach ($env as $key => $value) $p->line('declare -x ' . $key . '="' . addcslashes($value, '"\\$`') . '"');
        return 0;
    }
    $status = 0;
    foreach ($args as $arg) {
        if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)(?:=(.*))?$/s', $arg, $m) !== 1) {
            $p->err("bash: export: `$arg': not a valid identifier\n");
            $status = 1;
            continue;
        }
        if (isset($m[2])) $w->env[$m[1]] = $m[2];
        elseif (!isset($w->envAll()[$m[1]])) $w->env[$m[1]] = '';
    }
    return $status;
}

function lab57_cmd_unset(Lab57Proc $p, array $argv): int
{
    foreach (array_slice($argv, 1) as $name) {
        if (in_array($name, ['HOME', 'USER', 'PWD', 'OLDPWD', 'SHELL', 'PATH', 'HOSTNAME', 'LOGNAME', 'TERM', 'LANG', 'EDITOR'], true)) {
            $p->w->env[$name] = '';
            continue;
        }
        unset($p->w->env[$name]);
    }
    return 0;
}

function lab57_cmd_alias(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    if ($args === []) {
        ksort($w->aliases);
        foreach ($w->aliases as $name => $value) $p->line("alias $name='$value'");
        return 0;
    }
    $status = 0;
    foreach ($args as $arg) {
        if (str_contains($arg, '=')) {
            [$name, $value] = explode('=', $arg, 2);
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_.-]*$/', $name) !== 1) { $p->err("bash: alias: `$name': invalid alias name\n"); $status = 1; continue; }
            $w->aliases[$name] = $value;
            continue;
        }
        if (!isset($w->aliases[$arg])) { $p->err("bash: alias: $arg: not found\n"); $status = 1; continue; }
        $p->line("alias $arg='" . $w->aliases[$arg] . "'");
    }
    return $status;
}

function lab57_cmd_unalias(Lab57Proc $p, array $argv): int
{
    $status = 0;
    foreach (array_slice($argv, 1) as $name) {
        if ($name === '-a') { $p->w->aliases = []; continue; }
        if (!isset($p->w->aliases[$name])) { $p->err("bash: unalias: $name: not found\n"); $status = 1; continue; }
        unset($p->w->aliases[$name]);
    }
    return $status;
}

function lab57_cmd_which(Lab57Proc $p, array $argv): int
{
    $status = 0;
    foreach (array_slice($argv, 1) as $name) {
        if (str_starts_with($name, '-')) continue;
        $path = lab57_which($p->w, $name);
        if ($path === null) { $status = 1; continue; }
        $p->line($path);
    }
    return $status;
}

function lab57_cmd_type(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $status = 0;
    foreach (array_slice($argv, 1) as $name) {
        if (isset($w->aliases[$name])) { $p->line("$name is aliased to `" . $w->aliases[$name] . "'"); continue; }
        if (in_array($name, lab57_builtins(), true)) { $p->line("$name is a shell builtin"); continue; }
        $path = lab57_which($w, $name);
        if ($path !== null) { $p->line("$name is $path"); continue; }
        $p->err("bash: type: $name: not found\n");
        $status = 1;
    }
    return $status;
}

function lab57_cmd_sudo(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    while ($args !== [] && str_starts_with($args[0], '-')) {
        $flag = (string)array_shift($args);
        if ($flag === '-l') {
            // v58: pokud existují soubory sudoers, odvoď pravidla z nich; jinak výchozí chování v57.
            if (function_exists('lab58_sudo_list') && lab58_sudoers_files($w) !== []) {
                $list = lab58_sudo_list($w);
                foreach ($list['lines'] as $line) { if ((int)$list['exit'] === 0) $p->line($line); else $p->err($line . "\n"); }
                return (int)$list['exit'];
            }
            if (!$w->sudoAllowed) { $p->err("Sorry, user {$w->user} may not run sudo on {$w->hostname}.\n"); return 1; }
            $p->line("User {$w->user} may run the following commands on {$w->hostname}:\n    (ALL : ALL) ALL");
            return 0;
        }
        if (in_array($flag, ['-i', '-s', '-su'], true)) {
            $p->err("sudo: interaktivní administrátorský shell je v laboratoři vypnutý\n");
            $w->tip(tr('Napiš sudo před konkrétní příkaz, např. sudo systemctl restart nginx. Tak je vždy vidět, co děláš jako správce.'));
            return 1;
        }
        if ($flag === '--') break;
    }
    if ($args === []) { $p->err("usage: sudo -h | -K | -k | -V\nusage: sudo [-l] [command]\n"); return 1; }
    if (!$w->sudoAllowed) {
        $p->err("{$w->user} is not in the sudoers file.\n");
        $w->tip(tr('V této úrovni nejsi správce počítače, takže sudo nesmíš použít. Úkol jde vyřešit bez něj.'));
        return 1;
    }
    if (in_array($args[0], ['su', 'bash', 'sh'], true)) {
        $p->err("sudo: interaktivní administrátorský shell je v laboratoři vypnutý\n");
        $w->tip(tr('Napiš sudo před konkrétní příkaz, např. sudo nano /etc/hosts.'));
        return 1;
    }
    $auth = $w->fs->get('/var/log/auth.log');
    if ($auth !== null) {
        $auth['c'] = (string)($auth['c'] ?? '') . date('M j H:i:s', $w->now) . ' ' . $w->hostname . ' sudo: ' . str_pad($w->user, 8) . ' : TTY=pts/0 ; PWD=' . $w->cwd . ' ; USER=root ; COMMAND=' . (lab57_which($w, $args[0]) ?? $args[0]) . (count($args) > 1 ? ' ' . implode(' ', array_slice($args, 1)) : '') . "\n";
        $w->fs->set('/var/log/auth.log', $auth);
    }
    $was = $w->root;
    $w->root = true;
    try {
        $status = lab57_dispatch($p, $args);
    } finally {
        $w->root = $was;
    }
    $p->name = 'sudo';
    return $status;
}

function lab57_cmd_exit(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $code = isset($argv[1]) && preg_match('/^\d+$/', $argv[1]) === 1 ? (int)$argv[1] % 256 : $w->lastExit;
    if ($w->depth > 0) {
        $w->effects['exit'] = $code;
        return $code;
    }
    $p->line('logout');
    $w->tip(tr('V laboratoři terminál zůstává otevřený – můžeš psát dál.'));
    return $code;
}

function lab57_cmd_true(Lab57Proc $p, array $argv): int { return 0; }
function lab57_cmd_false(Lab57Proc $p, array $argv): int { return 1; }

function lab57_cmd_sleep(Lab57Proc $p, array $argv): int
{
    $arg = $argv[1] ?? null;
    if ($arg === null) { $p->err("sleep: missing operand\nTry 'sleep --help' for more information.\n"); return 1; }
    if (preg_match('/^(\d+(?:\.\d+)?)([smhd]?)$/', $arg, $m) !== 1) { $p->err("sleep: invalid time interval ‘{$arg}’\n"); return 1; }
    // v58: čekání je okamžité, ale posune simulované hodiny (date, cron, journalctl).
    $seconds = (int)round((float)$m[1] * ['' => 1, 's' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400][$m[2]]);
    if ($seconds > 0 && function_exists('lab58_clock_advance')) lab58_clock_advance($p->w, $seconds);
    return 0;
}

function lab57_cmd_source(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $file = $argv[1] ?? null;
    if ($file === null) { $p->err("bash: {$argv[0]}: filename argument required\n"); return 2; }
    $err = null;
    $content = $w->readFile($file, $err);
    if ($content === null) { $p->err("bash: $file: $err\n"); return 1; }
    return lab57_run_script($p, $content, array_slice($argv, 2), $file);
}

function lab57_cmd_bash(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    if (($args[0] ?? '') === '-c') {
        $src = (string)($args[1] ?? '');
        if ($w->depth >= 4) { $p->err("bash: příliš hluboké vnoření\n"); return 1; }
        $w->depth++;
        $term = [];
        try {
            $status = lab57_exec_source($w, $src, $term);
        } catch (Lab57SyntaxError $e) {
            $term[] = [2, 'bash: -c: ' . $e->getMessage() . "\n"];
            $status = 2;
        } finally {
            $w->depth--;
        }
        foreach ($term as [$fd, $text]) { if ($fd === 1) $p->out($text); else $p->err($text); }
        return $status;
    }
    $file = null;
    foreach ($args as $i => $arg) {
        if (!str_starts_with($arg, '-')) { $file = $arg; $args = array_slice($args, $i + 1); break; }
    }
    if ($file === null) {
        $w->tip(tr('Nový interaktivní shell simulace nespouští – už v jednom jsi. Skript spustíš: bash soubor.sh'));
        return 0;
    }
    $err = null;
    $content = $w->readFile($file, $err);
    if ($content === null) { $p->err("bash: $file: $err\n"); return 127; }
    return lab57_run_script($p, $content, $args, $file);
}

function lab57_cmd_test(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    if ($argv[0] === '[') {
        if (end($args) !== ']') { $p->err("bash: [: missing `]'\n"); return 2; }
        array_pop($args);
    }
    $negate = false;
    if (($args[0] ?? '') === '!') { $negate = true; array_shift($args); }
    $result = false;
    $n = count($args);
    if ($n === 0) $result = false;
    elseif ($n === 1) $result = $args[0] !== '';
    elseif ($n === 2) {
        [$op, $val] = $args;
        $abs = $w->abs($val);
        $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
        $result = match ($op) {
            '-e' => $node !== null,
            '-f' => $node !== null && ($node['t'] ?? '') === 'f',
            '-d' => $node !== null && ($node['t'] ?? '') === 'd',
            '-s' => $node !== null && Lab57Vfs::size($node) > 0,
            '-r' => $node !== null && $w->can($node, 'r'),
            '-w' => $node !== null && $w->can($node, 'w'),
            '-x' => $node !== null && $w->can($node, 'x'),
            '-z' => $val === '',
            '-n' => $val !== '',
            default => null,
        };
        if ($result === null) { $p->err("bash: test: $op: unary operator expected\n"); return 2; }
    } elseif ($n === 3) {
        [$a, $op, $b] = $args;
        $numeric = in_array($op, ['-eq', '-ne', '-lt', '-le', '-gt', '-ge'], true);
        if ($numeric && (preg_match('/^-?\d+$/', $a) !== 1 || preg_match('/^-?\d+$/', $b) !== 1)) {
            $p->err("bash: test: " . (preg_match('/^-?\d+$/', $a) !== 1 ? $a : $b) . ": integer expression expected\n");
            return 2;
        }
        $result = match ($op) {
            '=', '==' => $a === $b,
            '!=' => $a !== $b,
            '-eq' => (int)$a === (int)$b,
            '-ne' => (int)$a !== (int)$b,
            '-lt' => (int)$a < (int)$b,
            '-le' => (int)$a <= (int)$b,
            '-gt' => (int)$a > (int)$b,
            '-ge' => (int)$a >= (int)$b,
            default => null,
        };
        if ($result === null) { $p->err("bash: test: $op: binary operator expected\n"); return 2; }
    } else {
        $p->err("bash: test: too many arguments\n");
        return 2;
    }
    return ($result xor $negate) ? 0 : 1;
}

function lab57_cmd_whoami(Lab57Proc $p, array $argv): int
{
    $p->line($p->w->effectiveUser());
    return 0;
}

function lab57_cmd_id(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $user = $argv[1] ?? $w->effectiveUser();
    if (!isset($w->users[$user])) { $p->err("id: ‘{$user}’: no such user\n"); return 1; }
    $u = $w->users[$user];
    $groups = [];
    foreach ($w->userGroups($user) as $g) $groups[] = $w->gid($g) . '(' . $g . ')';
    $p->line('uid=' . $u['uid'] . '(' . $user . ') gid=' . $u['gid'] . '(' . $w->primaryGroup($user) . ') groups=' . implode(',', $groups));
    return 0;
}

function lab57_cmd_groups(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $user = $argv[1] ?? $w->effectiveUser();
    if (!isset($w->users[$user])) { $p->err("groups: ‘{$user}’: no such user\n"); return 1; }
    $p->line((isset($argv[1]) ? $user . ' : ' : '') . implode(' ', $w->userGroups($user)));
    return 0;
}

function lab57_cmd_hostname(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $arg = $argv[1] ?? null;
    if ($arg === '-I' || $arg === '-i') {
        $ips = [];
        foreach ((array)($w->net['ifaces'] ?? []) as $name => $iface) {
            if ($name === 'lo' || empty($iface['up']) || (string)($iface['ip'] ?? '') === '') continue;
            $ips[] = (string)$iface['ip'];
        }
        $p->line($arg === '-i' ? '127.0.1.1' : implode(' ', $ips) . ' ');
        return 0;
    }
    if ($arg === '-f') { $p->line($w->hostname . '.skola.test'); return 0; }
    if ($arg !== null && !str_starts_with($arg, '-')) {
        if (!$w->root) { $p->err("hostname: you must be root to change the host name\n"); return 1; }
        $w->hostname = $arg;
        return 0;
    }
    $p->line($w->hostname);
    return 0;
}

function lab57_cmd_uname(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, , $error] = lab57_getopt(array_slice($argv, 1), 'asnrvmpio', ['all' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $parts = ['s' => 'Linux', 'n' => $w->hostname, 'r' => '6.1.0-25-amd64', 'v' => '#1 SMP PREEMPT_DYNAMIC Debian 6.1.106-3 (2024-08-26)', 'm' => 'x86_64', 'o' => 'GNU/Linux'];
    if ($o === []) { $p->line('Linux'); return 0; }
    if (isset($o['a']) || isset($o['--all'])) { $p->line(implode(' ', $parts)); return 0; }
    $out = [];
    foreach (['s', 'n', 'r', 'v', 'm', 'p', 'i', 'o'] as $key) {
        if (!isset($o[$key])) continue;
        $out[] = in_array($key, ['p', 'i'], true) ? 'unknown' : $parts[$key];
    }
    $p->line(implode(' ', $out));
    return 0;
}

function lab57_cmd_date(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $format = null;
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '+')) { $format = substr($arg, 1); continue; }
        if ($arg === '-u' || $arg === '-R' || $arg === '-I') continue;
        if (!str_starts_with($arg, '-')) {
            if (!$w->root) { $p->err("date: cannot set date: Operation not permitted\n"); return 1; }
        }
    }
    $ts = $w->now;
    if ($format === null) { $p->line(date('D M j H:i:s T Y', $ts)); return 0; }
    $map = ['%Y' => 'Y', '%y' => 'y', '%m' => 'm', '%d' => 'd', '%e' => 'j', '%H' => 'H', '%M' => 'i', '%S' => 's', '%A' => 'l', '%a' => 'D', '%B' => 'F', '%b' => 'M', '%j' => 'z', '%s' => 'U', '%Z' => 'T', '%z' => 'O', '%u' => 'N', '%p' => 'A', '%F' => 'Y-m-d', '%T' => 'H:i:s', '%D' => 'm/d/y', '%R' => 'H:i'];
    $out = preg_replace_callback('/%[A-Za-z%n]/', static function (array $m) use ($map, $ts): string {
        if ($m[0] === '%%') return '%';
        if ($m[0] === '%n') return "\n";
        if ($m[0] === '%j') return str_pad((string)((int)date('z', $ts) + 1), 3, '0', STR_PAD_LEFT);
        return isset($map[$m[0]]) ? date($map[$m[0]], $ts) : $m[0];
    }, $format) ?? $format;
    $p->line($out);
    return 0;
}

function lab57_load_average(Lab57World $w): array
{
    $cpu = 0.0;
    foreach ($w->procs as $proc) $cpu += (float)($proc['cpu'] ?? 0);
    $base = min(4.0, $cpu / 100);
    return [round($base + 0.08, 2), round($base * 0.9 + 0.12, 2), round($base * 0.7 + 0.09, 2)];
}

function lab57_cmd_uptime(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $up = max(60, $w->now - $w->bootTime);
    $hours = intdiv($up, 3600);
    $minutes = intdiv($up % 3600, 60);
    if (($argv[1] ?? '') === '-p') { $p->line('up ' . ($hours > 0 ? $hours . ' hour' . ($hours === 1 ? '' : 's') . ', ' : '') . $minutes . ' minute' . ($minutes === 1 ? '' : 's')); return 0; }
    [$l1, $l5, $l15] = lab57_load_average($w);
    $p->line(' ' . date('H:i:s', $w->now) . ' up ' . sprintf('%2d:%02d', $hours, $minutes) . ',  1 user,  load average: ' . number_format($l1, 2, '.', '') . ', ' . number_format($l5, 2, '.', '') . ', ' . number_format($l15, 2, '.', ''));
    return 0;
}

function lab57_cmd_cowsay(Lab57Proc $p, array $argv): int
{
    $text = trim(implode(' ', array_slice($argv, 1)));
    if ($text === '') $text = trim($p->stdin) !== '' ? trim($p->stdin) : 'Bú! Linux je super.';
    $text = (string)preg_replace('/\s+/', ' ', $text);
    $len = mb_strlen($text);
    $p->out(' ' . str_repeat('_', $len + 2) . "\n< " . $text . " >\n " . str_repeat('-', $len + 2) . "\n        \\   ^__^\n         \\  (oo)\\_______\n            (__)\\       )\\/\\\n                ||----w |\n                ||     ||\n");
    return 0;
}

function lab57_cmd_xargs(Lab57Proc $p, array $argv): int
{
    $args = array_slice($argv, 1);
    $perCall = 0;
    if (($args[0] ?? '') === '-n') { $perCall = max(1, (int)($args[1] ?? 1)); $args = array_slice($args, 2); }
    $cmd = $args === [] ? ['echo'] : $args;
    $items = preg_split('/\s+/', trim($p->stdin), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($items === []) return 0;
    $status = 0;
    foreach ($perCall > 0 ? array_chunk($items, $perCall) : [$items] as $chunk) {
        $status = max($status, lab57_subrun($p, array_merge($cmd, $chunk)));
    }
    return $status === 0 ? 0 : 123;
}
