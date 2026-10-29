<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – Git v terminálu (LAB-06): příkaz git, volby, konfigurace, init, config, status, add, commit.
 * Úložiště: linux_v58_cmd_git_store.php · diff/log/show/blame/rev-parse: linux_v58_cmd_git_diff.php ·
 * větve/značky/reset/restore/stash/clone: linux_v58_cmd_git_more.php. Jen nad VFS – nic se nespouští, žádná síť.
 */

require_once __DIR__ . '/linux_v58_cmd_git_store.php';
require_once __DIR__ . '/linux_v58_cmd_git_diff.php';
require_once __DIR__ . '/linux_v58_cmd_git_more.php';

/** Podpříkaz → [funkce, potřeba repozitáře (0 ne, 1 ano, 2 s pracovním stromem), usage] */
function lab58_git_commands(): array
{
    return [
        'init' => ['lab58_git_cmd_init', 0, 'git init [-q | --quiet] [--bare] [-b <branch-name>] [<directory>]'], 'clone' => ['lab58_git_cmd_clone', 0, 'git clone [<options>] [--] <repo> [<dir>]'],
        'config' => ['lab58_git_cmd_config', 0, 'git config [--global | --local] [-l | --list] [--get | --unset] <name> [<value>]'], 'status' => ['lab58_git_cmd_status', 2, 'git status [<options>] [--] [<pathspec>...]'],
        'add' => ['lab58_git_cmd_add', 2, 'git add [<options>] [--] <pathspec>...'], 'commit' => ['lab58_git_cmd_commit', 2, 'git commit [-a | --amend] [-m <msg>] [--] [<pathspec>...]'],
        'log' => ['lab58_git_cmd_log', 1, 'git log [<options>] [<revision-range>] [[--] <path>...]'], 'show' => ['lab58_git_cmd_show', 1, 'git show [<options>] <object>...'],
        'diff' => ['lab58_git_cmd_diff', 2, 'git diff [<options>] [<commit>] [--] [<path>...]'], 'blame' => ['lab58_git_cmd_blame', 1, 'git blame [<options>] [<rev>] [--] <file>'],
        'checkout' => ['lab58_git_cmd_checkout', 2, 'git checkout [-b <new-branch>] <branch> | git checkout [<tree-ish>] -- <file>...'], 'switch' => ['lab58_git_cmd_switch', 2, 'git switch [-c <new-branch>] [--detach] <branch>'],
        'restore' => ['lab58_git_cmd_restore', 2, 'git restore [--staged] [--worktree] [--source=<tree>] <pathspec>...'], 'branch' => ['lab58_git_cmd_branch', 1, 'git branch [-a | -r] [-v] [-d | -D] [<branchname>] [<start-point>]'],
        'tag' => ['lab58_git_cmd_tag', 1, 'git tag [-a] [-m <msg>] <tagname> [<commit>] | git tag -l [-n] [<pattern>] | git tag -d <tagname>'],
        'reset' => ['lab58_git_cmd_reset', 2, 'git reset [--mixed | --soft | --hard] [-q] [<commit>] | git reset [<commit>] [--] <pathspec>...'],
        'stash' => ['lab58_git_cmd_stash', 2, 'git stash [push [-m <message>] | list | show [-p] | pop | apply | drop | clear] [<stash>]'],
        'rev-parse' => ['lab58_git_cmd_rev_parse', 1, 'git rev-parse [--short | --abbrev-ref | --verify] <args>...'], 'remote' => ['lab58_git_cmd_remote', 1, 'git remote [-v | --verbose]'],
        'help' => ['lab58_git_cmd_help', 0, 'git help [<command>]'],
    ];
}

function lab58_cmd_git(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_map('strval', array_slice($argv, 1)));
    $cwd = $w->cwd;
    try {
        while ($args !== [] && str_starts_with($args[0], '-')) {
            $a = (string)array_shift($args);
            if ($a === '--version' || $a === '-v') { $p->line('git version ' . LAB58_GIT_VERSION); return 0; }
            if ($a === '-h' || $a === '--help') return lab58_git_cmd_help($p, [], null);
            if ($a === '-C' && $args !== []) { if (!$w->fs->isDir($w->abs($args[0]))) return lab58_git_fatal($p, "cannot change to '" . $args[0] . "': No such file or directory"); $w->cwd = $w->abs((string)array_shift($args)); continue; }
            if (in_array($a, ['--no-pager', '-P', '-p', '--paginate'], true)) continue;
            $p->err('unknown option: ' . $a . "\nusage: git [-v | --version] [-h | --help] [-C <path>] <command> [<args>]\n");
            return 129;
        }
        [$sub, $cmds] = [(string)array_shift($args), lab58_git_commands()];
        if ($sub === '') { lab58_git_cmd_help($p, [], null); return 1; }
        if (!isset($cmds[$sub])) return lab58_git_unknown($p, $sub);
        [$fn, $need, $usage] = $cmds[$sub];
        if (in_array('-h', $args, true)) { $p->line('usage: ' . $usage); return 129; }
        $r = $need > 0 ? lab58_git_find($w) : null;
        if ($need > 0 && $r === null) { $w->tip(tr('Tady není žádný repozitář gitu. Přejdi do složky projektu (např. cd ~/web – podívej se ls), nebo založ nový: git init.')); return lab58_git_fatal($p, 'not a git repository (or any of the parent directories): .git'); }
        if ($need === 2 && $r['root'] === null) return lab58_git_fatal($p, 'this operation must be run in a work tree');
        $ops = array_filter($args, static fn(string $a): bool => $a === '' || $a[0] !== '-');
        $writes = in_array($sub, ['add', 'commit', 'checkout', 'switch', 'restore', 'reset', 'stash'], true) || (in_array($sub, ['branch', 'tag'], true) && ($ops !== [] || array_intersect($args, ['-d', '-D', '--delete']) !== []) && array_intersect($args, ['-l', '--list', '-n']) === []);
        if ($writes && !lab58_git_writable($w, (array)$r)) return lab58_git_fatal($p, "Unable to create '" . $r['gd'] . "/index.lock': Permission denied");
        // -q/--quiet: úspěšný podpříkaz nic nevypíše; při chybě se výstup ukáže (diff --quiet a rev-parse -q si výstup řídí samy)
        $q = array_intersect($args, ['-q', '--quiet']) !== [] && !in_array($sub, ['diff', 'rev-parse'], true) ? new Lab57Proc($w) : $p;
        $code = (int)$fn($q, $args, $r);
        if ($q !== $p && $code !== 0) array_push($p->chunks, ...$q->chunks);
        return $code;
    } finally {
        $w->cwd = $cwd;
    }
}

function lab58_git_unknown(Lab57Proc $p, string $sub): int
{
    if (in_array($sub, ['push', 'pull', 'fetch', 'merge', 'rebase', 'cherry-pick', 'revert', 'bisect', 'rm', 'mv', 'reflog', 'grep', 'shortlog', 'describe', 'clean', 'cat-file', 'ls-files'], true)) {
        $p->err('git ' . $sub . ": tento podpříkaz simulace zatím neumí\n");
        $p->w->tip(in_array($sub, ['push', 'pull', 'fetch'], true) ? tr('V laboratoři není síť ani vzdálený server. Historii zkoumej lokálně: git log, git show, git diff.') : tr('Simulace gitu umí: {seznam}.', ['seznam' => implode(', ', array_keys(lab58_git_commands()))]));
        return 1;
    }
    $similar = lab57_suggest($sub, array_keys(lab58_git_commands()));
    $p->err("git: '" . $sub . "' is not a git command. See 'git --help'.\n" . ($similar !== null ? "\nThe most similar command is\n\t" . $similar . "\n" : ''));
    $p->w->tip($similar !== null ? tr('Překlep? Zkus git {similar}.', ['similar' => $similar]) : tr('Seznam podpříkazů vypíše git help, podrobnosti man git.'));
    return 1;
}

function lab58_git_cmd_help(Lab57Proc $p, array $args, ?array $r): int
{
    $cmds = lab58_git_commands();
    if (isset($cmds[$args[0] ?? ''])) { $p->out('usage: ' . $cmds[$args[0]][2] . "\n\nCelá nápověda: man git\n"); return 0; }
    $p->out("usage: git [-v | --version] [-h | --help] [-C <path>] <command> [<args>]\n\nThese are common Git commands used in various situations:\n\nstart a working area (see also: git help tutorial)\n   clone     Clone a repository into a new directory\n   init      Create an empty Git repository or reinitialize an existing one\n\n"
        . "work on the current change (see also: git help everyday)\n   add       Add file contents to the index\n   restore   Restore working tree files\n\nexamine the history and state (see also: git help revisions)\n   blame     Show what revision and author last modified each line of a file\n   diff      Show changes between commits, commit and working tree, etc\n   log       Show commit logs\n   show      Show various types of objects\n   status    Show the working tree status\n\n"
        . "grow, mark and tweak your common history\n   branch    List, create, or delete branches\n   commit    Record changes to the repository\n   reset     Reset current HEAD to the specified state\n   stash     Stash the changes in a dirty working directory away\n   switch    Switch branches\n   tag       Create, list, delete or verify a tag object signed with GPG\n\n'git help <command>' shows the usage of a command. See 'man git' for more.\n");
    return 0;
}

// --- Společné pomocníky -----------------------------------------------------------
function lab58_git_fatal(Lab57Proc $p, string $msg, int $code = 128): int { $p->err('fatal: ' . $msg . "\n"); return $code; }

function lab58_git_no_ident(Lab57Proc $p): int
{
    $p->err("Author identity unknown\n\n*** Please tell me who you are.\n\nRun\n\n  git config --global user.email \"you@example.com\"\n  git config --global user.name \"Your Name\"\n\nto set your account's default identity.\nOmit --global to set the identity only in this repository.\n\nfatal: unable to auto-detect email address (got '" . $p->w->user . '@' . $p->w->hostname . ".(none)')\n");
    $p->w->tip(tr('Git potřebuje vědět, kdo změnu dělá: git config --global user.name "Tvé Jméno" a git config --global user.email tvuj@email.cz'));
    return 128;
}

function lab58_git_bad_opt(Lab57Proc $p, string $sub, string $err): int
{
    $p->err('error: ' . $err . "\nusage: " . lab58_git_commands()[$sub][2] . "\n");
    $p->w->tip(tr('Tuhle volbu git {sub} nezná (nebo ji simulace nepodporuje). Přehled: git {sub} -h nebo man git.', ['sub' => $sub]));
    return 129;
}

function lab58_git_ambiguous(Lab57Proc $p, string $arg): int
{
    $p->w->tip(tr('Git nezná revizi ani soubor „{arg}“. Commity ukáže git log --oneline, větve git branch -a, značky git tag. Smazaný soubor zadej za --: git log -- {arg}', ['arg' => $arg]));
    return lab58_git_fatal($p, "ambiguous argument '" . $arg . "': unknown revision or path not in the working tree.\nUse '--' to separate paths from revisions, like this:\n'git <command> [<revision>...] -- [<file>...]'");
}

/** Git-style volby. $spec: 'm|message' => 2 (opakovaná hodnota), 1 (hodnota), 0 (přepínač). @return array{0:array,1:list<string>,2:?list<string>,3:?string} */
function lab58_git_opts(array $args, array $spec): array
{
    [$map, $opts, $ops, $after] = [[], [], [], null];
    foreach ($spec as $names => $kind) foreach (explode('|', (string)$names) as $n) $map[$n] = [explode('|', (string)$names)[0], $kind];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '--') { $after = array_values(array_map('strval', array_slice($args, $i + 1))); break; }
        $long = str_starts_with($a, '--');
        if (!$long && ($a === '-' || !str_starts_with($a, '-'))) { $ops[] = $a; continue; }
        $names = $long ? [explode('=', substr($a, 2), 2)[0]] : str_split(substr($a, 1));
        foreach ($names as $k => $name) {
            if (!isset($map[$name])) return [$opts, $ops, $after, ($long ? 'unknown option `' : 'unknown switch `') . $name . "'"];
            [$key, $kind] = $map[$name];
            if ($kind === 0) { $opts[$key] = true; continue; }
            $val = $long ? (str_contains($a, '=') ? explode('=', $a, 2)[1] : null) : ($k + 1 < count($names) ? substr($a, $k + 2) : null);
            $val ??= $i + 1 < $n ? (string)$args[++$i] : null;
            if ($val === null) return [$opts, $ops, $after, ($long ? 'option `' : 'switch `') . $name . "' requires a value"];
            if ($kind === 2) $opts[$key][] = $val; else $opts[$key] = $val;
            break;
        }
    }
    return [$opts, $ops, $after, null];
}

/** Cesta relativně ke kořeni repozitáře ('' = kořen), null = mimo repozitář. */
function lab58_git_relpath(Lab57World $w, array $r, string $arg): ?string
{
    if ($r['root'] === null) return trim($arg, '/');
    [$abs, $root] = [$w->abs($arg), lab58_git_join((string)$r['root'], '')];
    return $abs === $r['root'] ? '' : (str_starts_with($abs, $root) ? substr($abs, strlen($root)) : null);
}

/** Cesta pro výpis relativně k aktuální složce (jako v git status). */
function lab58_git_disp(Lab57World $w, array $r, string $rel): string
{
    $from = (string)lab58_git_relpath($w, $r, '.');
    [$a, $b] = [$from === '' ? [] : explode('/', $from), explode('/', rtrim($rel, '/'))];
    while ($a !== [] && count($b) > 1 && $a[0] === $b[0]) { array_shift($a); array_shift($b); }
    return str_repeat('../', count($a)) . implode('/', $b) . (str_ends_with($rel, '/') ? '/' : '');
}

/** Operandy → [revize, pathspecy]. Bez „--“ musí být operand revize, existující cesta, nebo glob. */
function lab58_git_revs_paths(Lab57Proc $p, array $r, array $ops, ?array $after): ?array
{
    $w = $p->w;
    [$revs, $specs] = [[], []];
    foreach (array_merge($ops, ['--'], (array)$after) as $k => $op) {
        if ($k === count($ops)) continue;
        $isPath = $k > count($ops);
        if (!$isPath && $specs === [] && trim($op, '.') !== '' && array_filter(explode('..', str_replace('...', '..', $op)), static fn(string $x): bool => $x !== '' && lab58_git_resolve($w, $r, $x, false) === null) === []) { $revs[] = $op; continue; }
        $rel = lab58_git_relpath($w, $r, $op);
        if ($rel === null) { lab58_git_fatal($p, $op . ": '" . $op . "' is outside repository at '" . $r['root'] . "'"); return null; }
        if (!$isPath && ($r['root'] === null || (!$w->fs->exists($w->abs($op)) && !lab57_has_glob($op)))) { lab58_git_ambiguous($p, $op); return null; }
        $specs[] = $rel;
    }
    return [$revs, $specs];
}

// --- Konfigurace (.git/config, ~/.gitconfig ve tvaru INI) a identita ------------------
/** Kanonický klíč: sekce a jméno malými písmeny, podsekce beze změny (branch.Main.remote). */
function lab58_git_cfg_key(string $key): ?string
{
    return preg_match('/^([A-Za-z0-9-]+)(?:\.(.+))?\.([A-Za-z][A-Za-z0-9-]*)$/', $key, $m) === 1 ? strtolower($m[1]) . ($m[2] !== '' ? '.' . $m[2] : '') . '.' . strtolower($m[3]) : null;
}

/** @return list<array{0:string,1:string}> [klíč, hodnota] v pořadí souboru */
function lab58_git_cfg_read(Lab57World $w, string $file): array
{
    [$out, $section] = [[], ''];
    foreach (explode("\n", (string)($w->fs->get($file)['c'] ?? '')) as $line) {
        if (preg_match('/^\s*\[\s*([A-Za-z0-9.-]+)(?:\s+"([^"]*)")?\s*\]/', $line, $m) === 1) { $section = $m[1] . (isset($m[2]) ? '.' . $m[2] : ''); continue; }
        if ($section === '' || preg_match('/^\s*([A-Za-z][A-Za-z0-9-]*)\s*(?:=\s*(.*?))?\s*$/', $line, $m) !== 1) continue;
        $val = $m[2] ?? 'true';
        $out[] = [$section . '.' . $m[1], strlen($val) >= 2 && $val[0] === '"' && str_ends_with($val, '"') ? stripslashes(substr($val, 1, -1)) : $val];
    }
    return $out;
}

function lab58_git_cfg_write(Lab57World $w, string $file, array $entries): void
{
    $groups = [];
    foreach ($entries as [$key, $val]) $groups[substr((string)$key, 0, (int)strrpos((string)$key, '.'))][] = [substr((string)$key, (int)strrpos((string)$key, '.') + 1), (string)$val];
    $text = '';
    foreach ($groups as $section => $items) {
        $dot = strpos((string)$section, '.');
        $text .= $dot === false ? '[' . $section . "]\n" : '[' . substr((string)$section, 0, $dot) . ' "' . substr((string)$section, $dot + 1) . "\"]\n";
        foreach ($items as [$name, $val]) $text .= "\t" . $name . ' = ' . (preg_match('/^\s|\s$|[#;"]/', $val) === 1 ? '"' . addcslashes($val, '"\\') . '"' : $val) . "\n";
    }
    lab58_git_write($w, $file, $text);
}

/** Nastaví (s $value null smaže) klíč. Vrací false, když mazaný klíč neexistoval. */
function lab58_git_cfg_set(Lab57World $w, string $file, string $key, ?string $value): bool
{
    [$entries, $found] = [lab58_git_cfg_read($w, $file), false];
    foreach ($entries as $i => [$k]) if (lab58_git_cfg_key((string)$k) === lab58_git_cfg_key($key)) {
        if ($value === null || $found) unset($entries[$i]); else $entries[$i] = [$key, $value];
        $found = true;
    }
    if (!$found && $value !== null) $entries[] = [$key, $value];
    if ($found || $value !== null) lab58_git_cfg_write($w, $file, array_values($entries));
    return $found || $value !== null;
}

function lab58_git_cfg_global(Lab57World $w): string { return lab58_git_join($w->home(), '.gitconfig'); }

/** Hodnota klíče (poslední vyhrává): ~/.gitconfig, pak .git/config repozitáře; $files = jen vybrané soubory. */
function lab58_git_cfg_get(Lab57World $w, ?array $r, string $key, ?array $files = null): ?string
{
    $value = null;
    foreach ($files ?? array_filter([lab58_git_cfg_global($w), $r !== null ? $r['gd'] . '/config' : null]) as $file) {
        foreach (lab58_git_cfg_read($w, (string)$file) as [$k, $v]) if (lab58_git_cfg_key((string)$k) === lab58_git_cfg_key($key)) $value = (string)$v;
    }
    return $value;
}

/** @return array{name:string,email:string,time:int}|null */
function lab58_git_ident(Lab57World $w, ?array $r): ?array
{
    [$name, $email] = [trim((string)lab58_git_cfg_get($w, $r, 'user.name')), trim((string)lab58_git_cfg_get($w, $r, 'user.email'))];
    return $name === '' || $email === '' ? null : ['name' => $name, 'email' => $email, 'time' => $w->now];
}

// --- init, config ---------------------------------------------------------------
function lab58_git_cmd_init(Lab57Proc $p, array $args, ?array $r): int
{
    $w = $p->w;
    [$o, $ops, , $err] = lab58_git_opts($args, ['q|quiet' => 0, 'bare' => 0, 'b|initial-branch' => 1]);
    if ($err !== null) return lab58_git_bad_opt($p, 'init', $err);
    $gd = !empty($o['bare']) ? $w->abs((string)($ops[0] ?? '.')) : lab58_git_join($w->abs((string)($ops[0] ?? '.')), '.git');
    for ($parent = $w->abs((string)($ops[0] ?? '.')); !$w->fs->exists($parent); $parent = Lab57Vfs::dirname($parent));
    if (!$w->fs->isDir($parent)) return lab58_git_fatal($p, 'cannot mkdir ' . ($ops[0] ?? '.') . ': Not a directory');
    if (!($existed = $w->fs->isFile($gd . '/HEAD')) && !$w->root && !$w->canModifyDir($parent)) return lab58_git_fatal($p, 'cannot mkdir ' . $gd . ': Permission denied');
    if (!$existed) {
        lab58_git_write($w, $gd . '/HEAD', 'ref: refs/heads/' . ($o['b'] ?? lab58_git_cfg_get($w, null, 'init.defaultBranch') ?? 'main') . "\n");
        lab58_git_cfg_write($w, $gd . '/config', array_merge([['core.repositoryformatversion', '0'], ['core.filemode', 'true'], ['core.bare', empty($o['bare']) ? 'false' : 'true']], empty($o['bare']) ? [['core.logallrefupdates', 'true']] : []));
        lab58_git_write($w, $gd . '/description', "Unnamed repository; edit this file 'description' to name the repository.\n");
        foreach (['objects', 'refs/heads', 'refs/tags'] as $sub) lab58_git_mkdir($w, $gd . '/' . $sub);
    }
    if (empty($o['q'])) $p->line(($existed ? 'Reinitialized existing' : 'Initialized empty') . ' Git repository in ' . $gd . '/');
    return 0;
}

function lab58_git_cmd_config(Lab57Proc $p, array $args, ?array $r): int
{
    $w = $p->w;
    [$o, $ops, , $err] = lab58_git_opts($args, ['global' => 0, 'local' => 0, 'l|list' => 0, 'get' => 0, 'unset' => 0, 'show-origin' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'config', $err);
    if (($r = lab58_git_find($w)) === null && !empty($o['local'])) return lab58_git_fatal($p, '--local can only be used inside a git repository');
    $local = $r !== null ? $r['gd'] . '/config' : null;
    $files = !empty($o['global']) ? [lab58_git_cfg_global($w)] : (!empty($o['local']) ? [$local] : array_values(array_filter([lab58_git_cfg_global($w), $local])));
    if (!empty($o['l'])) foreach ($files as $file) foreach (lab58_git_cfg_read($w, (string)$file) as [$k, $v]) $p->line((!empty($o['show-origin']) ? 'file:' . $file . "\t" : '') . lab58_git_cfg_key((string)$k) . '=' . $v);
    if (!empty($o['l'])) return 0;
    if ($ops === [] || count($ops) > 2) { $p->err(($ops === [] ? 'error: no action specified' : 'error: wrong number of arguments, should be from 1 to 2') . "\nusage: " . lab58_git_commands()['config'][2] . "\n"); return 129; }
    if (lab58_git_cfg_key($ops[0]) === null) { $w->tip(tr('Klíč má tvar sekce.jméno, např. user.name nebo user.email.')); $p->err('error: key does not contain a section: ' . $ops[0] . "\n"); return 1; }
    $target = !empty($o['global']) || $local === null ? lab58_git_cfg_global($w) : $local;
    if (!empty($o['unset'])) return lab58_git_cfg_set($w, $target, $ops[0], null) ? 0 : 5;
    if (count($ops) === 2 && empty($o['get'])) return lab58_git_cfg_set($w, $target, $ops[0], $ops[1]) ? 0 : 4;
    if (($value = lab58_git_cfg_get($w, $r, $ops[0], $files)) !== null) $p->line($value);
    return $value === null ? 1 : 0;
}

// --- status, add, commit ---------------------------------------------------------
function lab58_git_cmd_status(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['s|short' => 0, 'b|branch' => 0, 'porcelain' => 0, 'long' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'status', $err);
    $st = lab58_git_status_data($w, $r);
    $specs = array_map(static fn(string $a): string => (string)lab58_git_relpath($w, $r, $a), array_merge($ops, (array)$after));
    foreach (['staged', 'unstaged'] as $k) $st[$k] = array_filter($st[$k], static fn($path): bool => lab58_git_matches((string)$path, $specs), ARRAY_FILTER_USE_KEY);
    $st['untracked'] = array_values(array_filter($st['untracked'], static fn(string $path): bool => lab58_git_matches($path, $specs)));
    if (empty($o['s']) && empty($o['porcelain'])) { lab58_git_status_long($p, $r, $st); return 0; }
    $h = $st['head'];
    [$up, $a, $b] = $h['branch'] !== null ? lab58_git_upstream($w, $r, $h['branch']) : [null, 0, 0];
    $info = implode(', ', array_filter([$a > 0 ? 'ahead ' . $a : '', $b > 0 ? 'behind ' . $b : '']));
    if (!empty($o['b'])) $p->line('## ' . ($h['branch'] === null ? 'HEAD (no branch)' : ($h['id'] === null ? 'No commits yet on ' . $h['branch'] : $h['branch'] . ($up !== null ? '...' . $up : '') . ($info !== '' ? ' [' . $info . ']' : ''))));
    $paths = array_unique(array_map('strval', array_merge(array_keys($st['staged']), array_keys($st['unstaged']))));
    sort($paths, SORT_STRING);
    foreach ($paths as $path) $p->line(($st['staged'][$path] ?? ' ') . ($st['unstaged'][$path] ?? ' ') . ' ' . (!empty($o['porcelain']) ? $path : lab58_git_disp($w, $r, $path)));
    foreach ($st['untracked'] as $path) $p->line('?? ' . (!empty($o['porcelain']) ? $path : lab58_git_disp($w, $r, $path)));
    return 0;
}

/** Dlouhý výpis stavu (git status, git commit bez změn, git stash pop). */
function lab58_git_status_long(Lab57Proc $p, array $r, array $st): void
{
    [$w, $h] = [$p->w, $st['head']];
    $p->line($h['branch'] !== null ? 'On branch ' . $h['branch'] : 'HEAD detached at ' . substr((string)$h['id'], 0, 7));
    $track = $h['branch'] !== null && $h['id'] !== null ? lab58_git_tracking($w, $r, $h['branch']) : '';
    $p->out($track . ($track !== '' ? "\n" : '') . ($h['id'] === null ? "\nNo commits yet\n\n" : ''));
    $label = ['A' => 'new file:   ', 'M' => 'modified:   ', 'D' => 'deleted:    '];
    foreach ([['Changes to be committed:', [$h['id'] === null ? '(use "git rm --cached <file>..." to unstage)' : '(use "git restore --staged <file>..." to unstage)'], $st['staged']],
        ['Changes not staged for commit:', ['(use "git add' . (in_array('D', $st['unstaged'], true) ? '/rm' : '') . ' <file>..." to update what will be committed)', '(use "git restore <file>..." to discard changes in working directory)'], $st['unstaged']],
        ['Untracked files:', ['(use "git add <file>..." to include in what will be committed)'], array_fill_keys($st['untracked'], '')]] as [$title, $hints, $items]) {
        if ($items === []) continue;
        $p->line($title);
        foreach ($hints as $hint) $p->line('  ' . $hint);
        foreach ($items as $path => $kind) $p->line("\t" . ($label[$kind] ?? '') . lab58_git_disp($w, $r, (string)$path));
        $p->line('');
    }
    if ($st['staged'] === []) $p->line($st['unstaged'] !== [] ? 'no changes added to commit (use "git add" and/or "git commit -a")' : ($st['untracked'] !== [] ? 'nothing added to commit but untracked files present (use "git add" to track)'
        : ($h['id'] === null ? 'nothing to commit (create/copy files and use "git add" to track)' : 'nothing to commit, working tree clean')));
}

function lab58_git_cmd_add(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['A|all' => 0, 'u|update' => 0, 'v|verbose' => 0, 'n|dry-run' => 0, 'f|force' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'add', $err);
    $ops = array_merge($ops, (array)$after);
    if ($ops === [] && empty($o['A']) && empty($o['u'])) { $p->err("Nothing specified, nothing added.\nhint: Maybe you wanted to say 'git add .'?\nhint: Turn this message off by running\nhint: \"git config advice.addEmptyPathspec false\"\n"); return 0; }
    $st = lab58_git_status_data($w, $r);
    $index = $st['index'];
    foreach ($ops === [] ? [(string)$r['root']] : $ops as $op) {
        $spec = lab58_git_relpath($w, $r, $op);
        if ($spec === null) return lab58_git_fatal($p, $op . ": '" . $op . "' is outside repository at '" . $r['root'] . "'");
        [$hit, $ignored] = [false, false];
        foreach (array_unique(array_map('strval', array_merge(array_keys($st['ids']), array_keys($index)))) as $path) {
            if (!lab58_git_matches($path, [$spec])) continue;
            if (!isset($index[$path]) && isset($st['ids'][$path]) && (!empty($o['u']) || (empty($o['f']) && lab58_git_ignored((string)($st['work']['.gitignore'] ?? ''), $path)))) { $ignored = $ignored || empty($o['u']); continue; }
            $hit = true;
            if (($index[$path] ?? null) === ($st['ids'][$path] ?? null)) continue;
            if (!empty($o['v']) || !empty($o['n'])) $p->line((isset($st['ids'][$path]) ? 'add' : 'remove') . " '" . $path . "'");
            if (!isset($st['ids'][$path])) unset($index[$path]);
            else $index[$path] = empty($o['n']) ? lab58_git_blob_put($w, $r, $st['work'][$path]) : $st['ids'][$path];
        }
        if (!$hit && $ignored) { $p->err("The following paths are ignored by one of your .gitignore files:\n" . $op . "\nhint: Use -f if you really want to add them.\n"); return 1; }
        if (!$hit && $ops !== []) return lab58_git_fatal($p, "pathspec '" . $op . "' did not match any files");
    }
    if (empty($o['n'])) lab58_git_index_save($w, $r, $index);
    return 0;
}

function lab58_git_cmd_commit(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['m|message' => 2, 'a|all' => 0, 'amend' => 0, 'no-edit' => 0, 'q|quiet' => 0, 'allow-empty' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'commit', $err);
    $me = lab58_git_ident($w, $r);
    if ($me === null) return lab58_git_no_ident($p);
    [$h, $amend] = [lab58_git_head($w, $r), !empty($o['amend'])];
    $prev = lab58_git_obj($w, $r, $h['id']);
    if ($amend && $prev === null) return lab58_git_fatal($p, 'You have nothing to amend.');
    $st = lab58_git_status_data($w, $r);
    $specs = array_map(static fn(string $a): string => (string)lab58_git_relpath($w, $r, $a), array_merge($ops, (array)$after));
    $files = $st['index'];
    foreach ($st['unstaged'] as $path => $kind) {
        if (empty($o['a']) && ($specs === [] || !lab58_git_matches((string)$path, $specs))) continue;
        if ($kind === 'D') unset($files[$path]); else $files[$path] = lab58_git_blob_put($w, $r, $st['work'][$path]);
    }
    $parents = $amend ? array_map('strval', (array)$prev['parents']) : ($h['id'] !== null ? [$h['id']] : []);
    $base = lab58_git_tree($w, $r, $parents[0] ?? null);
    if ($files == $base && !$amend && empty($o['allow-empty'])) { lab58_git_status_long($p, $r, $st); return 1; }
    $msg = isset($o['m']) ? lab58_git_clean_msg(implode("\n\n", (array)$o['m'])) : ($amend ? (string)$prev['message'] : '');
    if ($msg === '') {
        $w->tip(tr('Skutečný git by teď otevřel editor pro zprávu commitu. V simulaci ji napiš rovnou: git commit -m "Co jsem změnil(a)"'));
        $p->err("Aborting commit due to empty commit message.\n");
        return 1;
    }
    $author = $amend ? (array)$prev['author'] : $me;
    $id = lab58_git_commit_put($w, $r, $files, $parents, $author, $me, $msg);
    lab58_git_index_save($w, $r, $files);
    lab58_git_advance($w, $r, $id);
    if (!empty($o['q'])) return 0;
    $p->line('[' . ($h['branch'] ?? 'detached HEAD') . ($parents === [] ? ' (root-commit)' : '') . ' ' . substr($id, 0, 7) . '] ' . lab58_git_subject(['message' => $msg]));
    if ($amend) $p->line(' Date: ' . lab58_git_date((int)$author['time']));
    [$ins, $del, $modes] = [0, 0, []];
    foreach (array_unique(array_map('strval', array_merge(array_keys($base), array_keys($files)))) as $path) {
        if (($base[$path] ?? null) === ($files[$path] ?? null)) continue;
        [$i, $d] = lab58_git_numstat((string)lab58_git_blob($w, $r, $base[$path] ?? null), (string)lab58_git_blob($w, $r, $files[$path] ?? null));
        [$ins, $del, $modes[$path]] = [$ins + $i, $del + $d, !isset($base[$path]) ? ' create mode 100644 ' . $path : (!isset($files[$path]) ? ' delete mode 100644 ' . $path : '')];
    }
    ksort($modes, SORT_STRING);
    if ($modes !== []) $p->line(lab58_git_summary(count($modes), $ins, $del));
    foreach (array_filter($modes) as $line) $p->line($line);
    return 0;
}

lab58_register_command('git', 'lab58_cmd_git', ['package' => 'git']);
lab58_register_apt_package('git', ['version' => '1:2.39.5-0+deb12u2', 'description' => 'fast, scalable, distributed revision control system', 'size' => '7,264 kB', 'installed_size' => '41.4 MB', 'bins' => ['git'], 'preinstalled' => true]);
