<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – Git v terminálu: checkout, switch, restore, branch, tag, reset, stash, clone,
 * rev-parse, remote. Klonuje se jen z místní složky ve VFS (např. /srv/git/web.git) – žádná síť.
 */

function lab58_git_valid_ref(string $name): bool
{
    return preg_match('~^(?!-)(?!.*\.\.)(?!.*//)(?!.*@\{)[A-Za-z0-9._/-]+$~', $name) === 1 && !str_ends_with($name, '.lock') && !str_ends_with($name, '/') && !str_ends_with($name, '.') && $name !== 'HEAD';
}

/** Ověří jméno nové větve a startovní bod. @return array{0:string,1:?string}|null [id, sledovaná vzdálená větev] */
function lab58_git_branch_check(Lab57Proc $p, array $r, string $name, string $start, bool $force = false): ?array
{
    $w = $p->w;
    if (!lab58_git_valid_ref($name)) { lab58_git_fatal($p, "'" . $name . "' is not a valid branch name"); return null; }
    if (!$force && lab58_git_ref($w, $r, 'refs/heads/' . $name) !== null) { lab58_git_fatal($p, "a branch named '" . $name . "' already exists"); return null; }
    $id = lab58_git_resolve($w, $r, $start);
    if ($id === null) { lab58_git_fatal($p, "not a valid object name: '" . $start . "'"); return null; }
    return [$id, str_contains($start, '/') && lab58_git_ref($w, $r, 'refs/remotes/' . $start) !== null ? $start : null];
}

function lab58_git_branch_create(Lab57Proc $p, array $r, string $name, string $id, ?string $track): void
{
    lab58_git_ref_set($p->w, $r, 'refs/heads/' . $name, $id);
    if ($track === null) return;
    [$remote, $rb] = explode('/', $track, 2);
    lab58_git_cfg_set($p->w, $r['gd'] . '/config', 'branch.' . $name . '.remote', $remote);
    lab58_git_cfg_set($p->w, $r['gd'] . '/config', 'branch.' . $name . '.merge', 'refs/heads/' . $rb);
    $p->line("branch '" . $name . "' set up to track '" . $track . "'.");
}

/**
 * Přepne na commit – s větví ($branch), nebo odpojeně. $new = [sledovaná větev|null] → větev se po úspěchu založí.
 * Místní změny souborů, které se mezi HEAD a cílem liší, přepnutí zastaví; ostatní se přenesou (jako v gitu).
 */
function lab58_git_switch_to(Lab57Proc $p, array $r, string $target, ?string $branch, string $shown, ?array $new = null): int
{
    $w = $p->w;
    $st = lab58_git_status_data($w, $r);
    $to = lab58_git_tree($w, $r, $target);
    $paths = array_values(array_filter(array_unique(array_map('strval', array_merge(array_keys($st['tree']), array_keys($to)))), static fn(string $x): bool => ($st['tree'][$x] ?? null) !== ($to[$x] ?? null)));
    $dirty = array_values(array_filter($paths, static fn(string $x): bool => isset($st['staged'][$x]) || isset($st['unstaged'][$x])));
    $clash = array_values(array_filter($paths, static fn(string $x): bool => !isset($st['index'][$x]) && isset($st['ids'][$x]) && $st['ids'][$x] !== ($to[$x] ?? null)));
    if ($dirty !== [] || $clash !== []) {
        $p->err($dirty !== [] ? "error: Your local changes to the following files would be overwritten by checkout:\n\t" . implode("\n\t", $dirty) . "\nPlease commit your changes or stash them before you switch branches.\nAborting\n"
            : "error: The following untracked working tree files would be overwritten by checkout:\n\t" . implode("\n\t", $clash) . "\nPlease move or remove them before you switch branches.\nAborting\n");
        $w->tip(tr('Neuložené změny by přepnutí přepsalo. Ulož je commitem (git commit -am "…"), odlož (git stash), nebo zahoď (git restore <soubor>) – a přepni znovu.'));
        return 1;
    }
    $index = $st['index'];
    foreach ($paths as $path) {
        if (isset($to[$path])) { lab58_git_wt_write($w, $r, $path, (string)lab58_git_blob($w, $r, $to[$path])); $index[$path] = $to[$path]; }
        else { lab58_git_wt_remove($w, $r, $path); unset($index[$path]); }
    }
    lab58_git_index_save($w, $r, $index);
    $local = $st['staged'] + $st['unstaged'];
    ksort($local, SORT_STRING);
    foreach ($local as $path => $kind) $p->line($kind . "\t" . $path);
    $old = $st['head'];
    if ($old['branch'] === null && $old['id'] !== null && $old['id'] !== $target) $p->err('Previous HEAD position was ' . substr($old['id'], 0, 7) . ' ' . lab58_git_subject(lab58_git_obj($w, $r, $old['id'])) . "\n");
    if ($new !== null && $branch !== null) lab58_git_branch_create($p, $r, $branch, $target, $new[0]);
    lab58_git_ref_set($w, $r, 'HEAD', $branch !== null ? 'ref: refs/heads/' . $branch : $target);
    if ($branch === null) {
        if ($shown !== '') $p->err("Note: switching to '" . $shown . "'.\n\nYou are in 'detached HEAD' state. You can look around, make experimental\nchanges and commit them, and you can discard any commits you make in this\nstate without impacting any branches by switching back to a branch.\n\nIf you want to create a new branch to retain commits you create, you may\ndo so (now or later) by using -c with the switch command. Example:\n\n  git switch -c <new-branch-name>\n\nOr undo this operation with:\n\n  git switch -\n\nTurn off this advice by setting config variable advice.detachedHead to false\n\n");
        $p->err('HEAD is now at ' . substr($target, 0, 7) . ' ' . lab58_git_subject(lab58_git_obj($w, $r, $target)) . "\n");
        return 0;
    }
    $p->err(($new !== null ? "Switched to a new branch '" : ($old['branch'] === $branch ? "Already on '" : "Switched to branch '")) . $branch . "'\n");
    $p->out(lab58_git_tracking($w, $r, $branch));
    return 0;
}

/** Přepnutí na jméno: místní větev, jinak nová sledovací větev z jediné vzdálené větve <remote>/<jméno>. null = nejde o větev. */
function lab58_git_switch_name(Lab57Proc $p, array $r, string $name): ?int
{
    $id = lab58_git_ref($p->w, $r, 'refs/heads/' . $name);
    if ($id !== null) return lab58_git_switch_to($p, $r, $id, $name, $name);
    $remotes = array_values(array_filter(array_keys(lab58_git_refs($p->w, $r, 'refs/remotes')), static fn($ref): bool => str_ends_with((string)$ref, '/' . $name) && substr_count((string)$ref, '/') === 1));
    if (count($remotes) !== 1 || !lab58_git_valid_ref($name)) return null;
    return lab58_git_switch_to($p, $r, (string)lab58_git_ref($p->w, $r, 'refs/remotes/' . $remotes[0]), $name, '', [(string)$remotes[0]]);
}

/** -b/-c: založí větev ze startovního bodu a přepne na ni. */
function lab58_git_switch_new(Lab57Proc $p, array $r, string $name, string $start, bool $force): int
{
    $chk = lab58_git_branch_check($p, $r, $name, $start, $force);
    return $chk === null ? 128 : lab58_git_switch_to($p, $r, $chk[0], $name, '', [$chk[1]]);
}

function lab58_git_cmd_checkout(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['b' => 1, 'B' => 1, 'detach' => 0, 'f|force' => 0, 'q|quiet' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'checkout', $err);
    if (isset($o['b']) || isset($o['B'])) return lab58_git_switch_new($p, $r, (string)($o['b'] ?? $o['B']), (string)($ops[0] ?? 'HEAD'), isset($o['B']));
    $name = (string)($ops[0] ?? '');
    if ($after === null && count($ops) === 1) {
        $done = empty($o['detach']) ? lab58_git_switch_name($p, $r, $name) : null;
        if ($done !== null) return $done;
        $id = lab58_git_resolve($w, $r, $name);
        if ($id !== null) return lab58_git_switch_to($p, $r, $id, null, $name);
    }
    $source = $after !== null ? ($ops[0] ?? null) : (count($ops) > 1 && lab58_git_resolve($w, $r, $name) !== null ? $name : null);
    $files = $after ?? array_slice($ops, $source !== null ? 1 : 0);
    if ($files === []) return lab58_git_bad_opt($p, 'checkout', 'you must specify a branch to check out');
    $id = $source === null ? null : lab58_git_resolve($w, $r, (string)$source);
    if ($source !== null && $id === null) return lab58_git_ambiguous($p, (string)$source);
    $n = lab58_git_restore_paths($p, $r, $files, $id, $id !== null, true);
    if ($n >= 0) $p->err('Updated ' . $n . ' path' . ($n === 1 ? '' : 's') . ' from ' . ($id === null ? 'the index' : substr((string)lab58_git_obj($w, $r, $id)['tree'], 0, 7)) . "\n");
    return $n < 0 ? 1 : 0;
}

function lab58_git_cmd_switch(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, , $err] = lab58_git_opts($args, ['c|create' => 1, 'C|force-create' => 1, 'd|detach' => 0, 'q|quiet' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'switch', $err);
    if (isset($o['c']) || isset($o['C'])) return lab58_git_switch_new($p, $r, (string)($o['c'] ?? $o['C']), (string)($ops[0] ?? 'HEAD'), isset($o['C']));
    $name = (string)($ops[0] ?? '');
    if ($name === '') return lab58_git_fatal($p, 'missing branch or commit argument');
    $id = lab58_git_resolve($w, $r, $name);
    if (!empty($o['d'])) return $id === null ? lab58_git_fatal($p, 'invalid reference: ' . $name) : lab58_git_switch_to($p, $r, $id, null, '');
    $done = lab58_git_switch_name($p, $r, $name);
    if ($done !== null) return $done;
    if ($id === null) { $w->tip(tr('Větev „{name}“ neexistuje. Seznam větví: git branch -a; novou založíš git switch -c {name}.', ['name' => $name])); return lab58_git_fatal($p, 'invalid reference: ' . $name); }
    $kind = lab58_git_ref($w, $r, 'refs/remotes/' . $name) !== null ? 'remote branch' : (lab58_git_ref($w, $r, 'refs/tags/' . $name) !== null ? 'tag' : 'commit');
    $p->err('fatal: a branch is expected, got ' . $kind . " '" . $name . "'\nhint: If you want to detach HEAD at the commit, try again with the --detach option.\n");
    return 128;
}

/** Obnoví cesty ze zdroje (commit, nebo index když $source null) do indexu ($staged) a/nebo pracovního stromu. Vrací počet cest, -1 = chyba. */
function lab58_git_restore_paths(Lab57Proc $p, array $r, array $args, ?string $source, bool $staged, bool $worktree): int
{
    $w = $p->w;
    $st = lab58_git_status_data($w, $r);
    $from = $source !== null ? lab58_git_tree($w, $r, $source) : $st['index'];
    [$index, $count] = [$st['index'], 0];
    foreach ($args as $arg) {
        $spec = lab58_git_relpath($w, $r, (string)$arg);
        $hit = 0;
        foreach (array_unique(array_map('strval', array_merge(array_keys($from), array_keys($st['index'])))) as $path) {
            if ($spec === null || !lab58_git_matches($path, [$spec])) continue;
            $hit++;
            $id = $from[$path] ?? null;
            if ($staged) { if ($id === null) unset($index[$path]); else $index[$path] = $id; }
            if ($worktree && $id === null) lab58_git_wt_remove($w, $r, $path);
            elseif ($worktree && ($st['ids'][$path] ?? null) !== $id) lab58_git_wt_write($w, $r, $path, (string)lab58_git_blob($w, $r, $id));
        }
        if ($hit === 0) { $p->err("error: pathspec '" . $arg . "' did not match any file(s) known to git\n"); lab57_error_tip($w, 'No such file or directory', (string)$arg); return -1; }
        $count += $hit;
    }
    if ($staged) lab58_git_index_save($w, $r, $index);
    return $count;
}

function lab58_git_cmd_restore(Lab57Proc $p, array $args, array $r): int
{
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['S|staged' => 0, 'W|worktree' => 0, 's|source' => 1, 'q|quiet' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'restore', $err);
    $files = array_merge($ops, (array)$after);
    if ($files === []) return lab58_git_fatal($p, 'you must specify path(s) to restore');
    $source = isset($o['s']) ? lab58_git_resolve($p->w, $r, (string)$o['s']) : (!empty($o['S']) ? lab58_git_resolve($p->w, $r, 'HEAD') : null);
    if (isset($o['s']) && $source === null) return lab58_git_fatal($p, 'could not resolve ' . $o['s']);
    return lab58_git_restore_paths($p, $r, $files, $source, !empty($o['S']), empty($o['S']) || !empty($o['W'])) < 0 ? 1 : 0;
}

// --- branch, tag ---------------------------------------------------------------

function lab58_git_cmd_branch(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, , $err] = lab58_git_opts($args, ['a|all' => 0, 'r|remotes' => 0, 'v|verbose' => 0, 'd|delete' => 0, 'D' => 0, 'f|force' => 0, 'show-current' => 0, 'l|list' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'branch', $err);
    $h = lab58_git_head($w, $r);
    if (!empty($o['show-current'])) { if ($h['branch'] !== null) $p->line($h['branch']); return 0; }
    if (!empty($o['d']) || !empty($o['D'])) {
        if ($ops === []) return lab58_git_fatal($p, 'branch name required');
        foreach ($ops as $name) {
            [$id, $up] = [lab58_git_ref($w, $r, 'refs/heads/' . $name), lab58_git_upstream($w, $r, $name)[0]];
            $merged = $id !== null && isset(lab58_git_reach($w, $r, $up !== null ? lab58_git_ref($w, $r, 'refs/remotes/' . $up) : $h['id'])[$id]);
            $error = $id === null ? "error: branch '" . $name . "' not found." : ($name === $h['branch'] ? "error: Cannot delete branch '" . $name . "' checked out at '" . ($r['root'] ?? $r['gd']) . "'"
                : (!$merged && empty($o['D']) ? "error: The branch '" . $name . "' is not fully merged.\nIf you are sure you want to delete it, run 'git branch -D " . $name . "'." : null));
            if ($error !== null) { $p->err($error . "\n"); return 1; }
            $w->fs->delete($r['gd'] . '/refs/heads/' . $name);
            $p->line('Deleted branch ' . $name . ' (was ' . substr((string)$id, 0, 7) . ').');
        }
        return 0;
    }
    if ($ops !== [] && empty($o['l'])) {
        $chk = lab58_git_branch_check($p, $r, $ops[0], (string)($ops[1] ?? 'HEAD'), !empty($o['f']));
        if ($chk !== null) lab58_git_branch_create($p, $r, $ops[0], $chk[0], $chk[1]);
        return $chk === null ? 128 : 0;
    }
    $rows = $h['branch'] === null && $h['id'] !== null && empty($o['r']) ? [['*', '(HEAD detached at ' . substr($h['id'], 0, 7) . ')', $h['id'], null]] : [];
    if (empty($o['r'])) foreach (lab58_git_refs($w, $r, 'refs/heads') as $name => $id) $rows[] = [$name === $h['branch'] ? '*' : ' ', (string)$name, $id, (string)$name];
    if (!empty($o['a']) || !empty($o['r'])) foreach (lab58_git_refs($w, $r, 'refs/remotes') as $name => $id) $rows[] = [' ', (empty($o['r']) ? 'remotes/' : '') . $name
        . (str_starts_with($raw = (string)lab58_git_ref_raw($w, $r, 'refs/remotes/' . $name), 'ref: refs/remotes/') ? ' -> ' . substr($raw, 18) : ''), $id, null];
    $rows = array_values(array_filter($rows, static fn(array $row): bool => $ops === [] || lab57_glob_match((string)$ops[0], (string)$row[1])));
    $width = max(array_merge([0], array_map(static fn(array $row): int => str_contains($row[1], ' -> ') ? 0 : mb_strwidth($row[1]), $rows)));
    foreach ($rows as [$mark, $name, $id, $local]) {
        if (empty($o['v']) || str_contains($name, ' -> ')) { $p->line($mark . ' ' . $name); continue; }
        [, $a, $b] = $local !== null ? lab58_git_upstream($w, $r, $local) : [null, 0, 0];
        $track = $a > 0 || $b > 0 ? '[' . implode(', ', array_filter([$a > 0 ? 'ahead ' . $a : '', $b > 0 ? 'behind ' . $b : ''])) . '] ' : '';
        $p->line($mark . ' ' . $name . str_repeat(' ', $width - mb_strwidth($name)) . ' ' . substr($id, 0, 7) . ' ' . $track . lab58_git_subject(lab58_git_obj($w, $r, $id)));
    }
    return 0;
}

function lab58_git_cmd_tag(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    $args = array_map(static fn($a): string => preg_match('/^-n\d+$/', (string)$a) === 1 ? '-n' : (string)$a, $args);
    [$o, $ops, , $err] = lab58_git_opts($args, ['a|annotate' => 0, 'm|message' => 2, 'l|list' => 0, 'n' => 0, 'd|delete' => 0, 'f|force' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'tag', $err);
    $tags = lab58_git_refs($w, $r, 'refs/tags');
    if (!empty($o['d'])) {
        foreach ($ops as $t) {
            if (!isset($tags[$t])) { $p->err("error: tag '" . $t . "' not found.\n"); return 1; }
            $w->fs->delete($r['gd'] . '/refs/tags/' . $t);
            $p->line("Deleted tag '" . $t . "' (was " . substr($tags[$t], 0, 7) . ')');
        }
        return 0;
    }
    if ($ops === [] || !empty($o['l']) || !empty($o['n'])) {
        foreach ($tags as $name => $id) {
            if (isset($ops[0]) && !lab57_glob_match((string)$ops[0], (string)$name)) continue;
            $obj = (array)lab58_git_obj($w, $r, $id);
            $p->line(empty($o['n']) ? (string)$name : str_pad((string)$name, 15) . ' ' . (($obj['type'] ?? '') === 'tag' ? (string)strtok((string)$obj['message'] . "\n", "\n") : lab58_git_subject($obj)));
        }
        return 0;
    }
    [$name, $rev] = [(string)$ops[0], (string)($ops[1] ?? 'HEAD')];
    if (!lab58_git_valid_ref($name)) return lab58_git_fatal($p, "'" . $name . "' is not a valid tag name.");
    if (isset($tags[$name]) && empty($o['f'])) return lab58_git_fatal($p, "tag '" . $name . "' already exists");
    $target = lab58_git_resolve($w, $r, $rev);
    if ($target === null) return lab58_git_fatal($p, "Failed to resolve '" . $rev . "' as a valid ref.");
    $id = $target;
    if (!empty($o['a']) || isset($o['m'])) {
        if (!isset($o['m'])) { $w->tip(tr('Skutečný git by otevřel editor pro popis značky. Zadej ho rovnou: git tag -a {name} -m "Popis vydání"', ['name' => $name])); return lab58_git_fatal($p, 'no tag message?'); }
        $me = lab58_git_ident($w, $r);
        if ($me === null) return lab58_git_no_ident($p);
        $id = lab58_git_put($w, $r, ['type' => 'tag', 'object' => $target, 'tag' => $name, 'tagger' => $me, 'message' => lab58_git_clean_msg(implode("\n\n", (array)$o['m']))]);
    }
    lab58_git_ref_set($w, $r, 'refs/tags/' . $name, $id);
    if (isset($tags[$name])) $p->line("Updated tag '" . $name . "' (was " . substr($tags[$name], 0, 7) . ')');
    return 0;
}

// --- reset, stash -----------------------------------------------------------------

function lab58_git_cmd_reset(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['soft' => 0, 'mixed' => 0, 'hard' => 0, 'q|quiet' => 0]);
    if ($err !== null) return lab58_git_bad_opt($p, 'reset', $err);
    $rev = $ops !== [] && lab58_git_resolve($w, $r, $ops[0]) !== null ? (string)array_shift($ops) : 'HEAD';
    $target = lab58_git_resolve($w, $r, $rev);
    $paths = array_merge($ops, (array)$after);
    foreach ($ops as $op) if (!$w->fs->exists($w->abs($op)) && !isset(lab58_git_index($w, $r)[(string)lab58_git_relpath($w, $r, $op)])) return lab58_git_ambiguous($p, $op);
    if ($paths !== [] && (!empty($o['hard']) || !empty($o['soft']))) return lab58_git_fatal($p, 'Cannot do ' . (!empty($o['hard']) ? 'hard' : 'soft') . ' reset with paths.');
    $st = lab58_git_status_data($w, $r);
    if ($paths !== [] && lab58_git_restore_paths($p, $r, $paths, $target, true, false) < 0) return 1;
    if ($paths === []) {
        if (!empty($o['hard'])) lab58_git_force_tree($w, $r, $st, lab58_git_tree($w, $r, $target));
        elseif (empty($o['soft'])) lab58_git_index_save($w, $r, lab58_git_tree($w, $r, $target));
        if ($st['head']['id'] !== null) lab58_git_ref_set($w, $r, 'ORIG_HEAD', $st['head']['id']);
        if ($target !== null) lab58_git_advance($w, $r, $target);
    }
    if (!empty($o['hard'])) { $p->line('HEAD is now at ' . substr((string)$target, 0, 7) . ' ' . lab58_git_subject(lab58_git_obj($w, $r, $target))); return 0; }
    $after = lab58_git_status_data($w, $r)['unstaged'];
    if (empty($o['soft']) && empty($o['q']) && $after !== []) { $p->line('Unstaged changes after reset:'); foreach ($after as $path => $kind) $p->line($kind . "\t" . $path); }
    return 0;
}

/** Nastaví index i sledované soubory pracovního stromu přesně na $tree (reset --hard, stash push); nesledované nechá. */
function lab58_git_force_tree(Lab57World $w, array $r, array $st, array $tree): void
{
    foreach (array_unique(array_map('strval', array_merge(array_keys($st['index']), array_keys($st['tree']), array_keys($tree)))) as $path) {
        if (!isset($tree[$path])) lab58_git_wt_remove($w, $r, $path);
        elseif (($st['ids'][$path] ?? null) !== $tree[$path]) lab58_git_wt_write($w, $r, $path, (string)lab58_git_blob($w, $r, $tree[$path]));
    }
    lab58_git_index_save($w, $r, $tree);
}

function lab58_git_cmd_stash(Lab57Proc $p, array $args, array $r): int
{
    $w = $p->w;
    $sub = isset($args[0]) && !str_starts_with((string)$args[0], '-') ? (string)array_shift($args) : 'push';
    [$o, $ops, , $err] = lab58_git_opts($args, ['m|message' => 1, 'p|patch' => 0, 'q|quiet' => 0, 'stat' => 0]);
    if ($err !== null || !in_array($sub, ['push', 'save', 'list', 'show', 'pop', 'apply', 'drop', 'clear'], true)) return lab58_git_bad_opt($p, 'stash', $err ?? 'unknown subcommand: `' . $sub . "'");
    $list = lab58_git_stash_list($w, $r);
    if ($sub === 'list') { foreach ($list as $i => $e) $p->line('stash@{' . $i . '}: ' . $e['message']); return 0; }
    if ($sub === 'clear') { lab58_git_stash_save($w, $r, []); return 0; }
    $st = lab58_git_status_data($w, $r);
    if ($sub === 'push' || $sub === 'save') {
        $h = $st['head'];
        if ($h['id'] === null) { $p->err("You do not have the initial commit yet\n"); return 1; }
        if ($st['staged'] === [] && $st['unstaged'] === []) { $p->line('No local changes to save'); return 0; }
        $work = $st['index'];
        foreach ($st['unstaged'] as $path => $kind) { if ($kind === 'D') unset($work[$path]); else $work[$path] = lab58_git_blob_put($w, $r, $st['work'][$path]); }
        $msg = isset($o['m']) || ($sub === 'save' && $ops !== []) ? (string)($o['m'] ?? implode(' ', $ops)) : null;
        $where = $h['branch'] ?? '(no branch)';
        $e = ['base' => $h['id'], 'branch' => $where, 'index' => lab58_git_tree_put($w, $r, $st['index']), 'work' => lab58_git_tree_put($w, $r, $work), 'time' => $w->now,
            'message' => $msg !== null ? 'On ' . $where . ': ' . $msg : 'WIP on ' . $where . ': ' . substr($h['id'], 0, 7) . ' ' . lab58_git_subject(lab58_git_obj($w, $r, $h['id']))];
        $e['id'] = sha1(lab58_git_json($e));
        lab58_git_stash_save($w, $r, array_merge([$e], $list));
        lab58_git_force_tree($w, $r, $st, $st['tree']);
        if (empty($o['q'])) $p->line('Saved working directory and index state ' . $e['message']);
        return 0;
    }
    if ($list === []) { $p->err($sub === 'show' ? "error: No stash entries found.\n" : "No stash entries found.\n"); return 1; }
    $ref = (string)($ops[0] ?? 'stash@{0}');
    $n = preg_match('/^(?:stash@\{)?(\d+)\}?$/', $ref, $m) === 1 ? (int)$m[1] : -1;
    if (!isset($list[$n])) return lab58_git_fatal($p, $n < 0 ? "'" . $ref . "' is not a stash-like commit" : "log for 'stash' only has " . count($list) . ' entries');
    $e = $list[$n];
    [$base, $work, $idx] = [lab58_git_tree($w, $r, (string)$e['base']), lab58_git_tree($w, $r, (string)$e['work']), lab58_git_tree($w, $r, (string)$e['index'])];
    if ($sub === 'show') { lab58_git_diff_trees($p, $r, $base, $work, [], !empty($o['p']) ? 'patch' : 'stat'); return 0; }
    if ($sub !== 'drop') {
        $changed = array_values(array_filter(array_unique(array_map('strval', array_merge(array_keys($base), array_keys($work)))), static fn(string $x): bool => ($base[$x] ?? null) !== ($work[$x] ?? null)));
        $dirty = array_values(array_filter($changed, static fn(string $x): bool => isset($st['staged'][$x]) || isset($st['unstaged'][$x]) || (!isset($st['index'][$x]) && isset($st['ids'][$x]))));
        $both = array_values(array_filter($changed, static fn(string $x): bool => ($st['tree'][$x] ?? null) !== ($base[$x] ?? null) && ($st['tree'][$x] ?? null) !== ($work[$x] ?? null)));
        if ($dirty !== []) { $p->err("error: Your local changes to the following files would be overwritten by merge:\n\t" . implode("\n\t", $dirty) . "\nPlease commit your changes or stash them before you merge.\nAborting\n"); $w->tip(tr('Nejdřív ulož nebo zahoď rozdělané změny (git status), pak zkus git stash {sub} znovu.', ['sub' => $sub])); return 1; }
        if ($both !== []) { $p->err('CONFLICT (content): Merge conflict in ' . implode("\nCONFLICT (content): Merge conflict in ", $both) . "\nThe stash entry is kept in case you need it again.\n"); $w->tip(tr('Soubor se změnil v commitu i ve stashi. Simulace konflikt neslučuje – obsah stashe ukáže git stash show -p.')); return 1; }
        $index = $st['index'];
        foreach ($changed as $path) {
            if (!isset($work[$path])) { lab58_git_wt_remove($w, $r, $path); unset($index[$path]); continue; }
            lab58_git_wt_write($w, $r, $path, (string)lab58_git_blob($w, $r, $work[$path]));
            if (!isset($base[$path]) && isset($idx[$path])) $index[$path] = $idx[$path];
        }
        lab58_git_index_save($w, $r, $index);
        lab58_git_status_long($p, $r, lab58_git_status_data($w, $r));
    }
    if ($sub === 'pop' || $sub === 'drop') {
        array_splice($list, $n, 1);
        lab58_git_stash_save($w, $r, $list);
        $p->line('Dropped ' . ($ops[0] ?? 'refs/stash@{0}') . ' (' . $e['id'] . ')');
    }
    return 0;
}

// --- clone (jen z místní složky ve VFS) ----------------------------------------------

/** Zkopíruje repozitář $src do $dest: objekty, větve jako origin/*, značky, konfigurace, pracovní strom. */
function lab58_git_clone_into(Lab57World $w, array $src, string $url, string $dest, ?string $branch = null): array
{
    $r = ['gd' => lab58_git_join($dest, '.git'), 'root' => $dest];
    foreach ($w->fs->children($src['gd'] . '/objects') as $file) lab58_git_write($w, $r['gd'] . '/objects/' . $file, (string)($w->fs->get($src['gd'] . '/objects/' . $file)['c'] ?? ''), 0444);
    lab58_git_mkdir($w, $r['gd'] . '/refs/tags');
    foreach (lab58_git_refs($w, $src, 'refs/heads') as $name => $id) lab58_git_ref_set($w, $r, 'refs/remotes/origin/' . $name, $id);
    foreach (lab58_git_refs($w, $src, 'refs/tags') as $name => $id) lab58_git_ref_set($w, $r, 'refs/tags/' . $name, $id);
    $head = $branch ?? lab58_git_head($w, $src)['branch'] ?? 'main';
    $id = lab58_git_ref($w, $src, 'refs/heads/' . $head);
    lab58_git_cfg_write($w, $r['gd'] . '/config', array_merge([['core.repositoryformatversion', '0'], ['core.filemode', 'true'], ['core.bare', 'false'], ['core.logallrefupdates', 'true'], ['remote.origin.url', $url], ['remote.origin.fetch', '+refs/heads/*:refs/remotes/origin/*']],
        $id === null ? [] : [['branch.' . $head . '.remote', 'origin'], ['branch.' . $head . '.merge', 'refs/heads/' . $head]]));
    lab58_git_write($w, $r['gd'] . '/HEAD', 'ref: refs/heads/' . $head . "\n");
    if ($id === null) return $r;
    lab58_git_ref_set($w, $r, 'refs/remotes/origin/HEAD', 'ref: refs/remotes/origin/' . $head);
    lab58_git_ref_set($w, $r, 'refs/heads/' . $head, $id);
    $tree = lab58_git_tree($w, $r, $id);
    foreach ($tree as $path => $blob) lab58_git_wt_write($w, $r, (string)$path, (string)lab58_git_blob($w, $r, $blob));
    lab58_git_index_save($w, $r, $tree);
    return $r;
}

function lab58_git_cmd_clone(Lab57Proc $p, array $args, ?array $r): int
{
    $w = $p->w;
    [$o, $ops, $after, $err] = lab58_git_opts($args, ['q|quiet' => 0, 'b|branch' => 1]);
    if ($err !== null) return lab58_git_bad_opt($p, 'clone', $err);
    $ops = array_merge($ops, (array)$after);
    if ($ops === []) { $p->err("fatal: You must specify a repository to clone.\n\nusage: " . lab58_git_commands()['clone'][2] . "\n"); return 129; }
    $url = (string)$ops[0];
    $name = (string)($ops[1] ?? preg_replace('~\.git$~', '', basename(rtrim((string)preg_replace('~^[a-z]+://[^/]*|^[\w.-]+@[\w.-]+:~', '', $url), '/'))));
    if (!str_starts_with($url, 'file://') && preg_match('~^(?:[a-z]+://|[\w.-]+@)([\w.-]+)~', $url, $m) === 1) {
        $p->err("Cloning into '" . $name . "'...\n" . (str_contains($url, '://') ? "fatal: unable to access '" . $url . "': Could not resolve host: " . $m[1] . "\n"
            : 'ssh: Could not resolve hostname ' . $m[1] . ": Temporary failure in name resolution\nfatal: Could not read from remote repository.\n\nPlease make sure you have the correct access rights\nand the repository exists.\n"));
        $w->tip(tr('V laboratoři není internet. Klonuj z místního repozitáře ve složce, např.: git clone /srv/git/web.git'));
        return 128;
    }
    $abs = $w->abs(str_starts_with($url, 'file://') ? substr($url, 7) : $url);
    $src = null;
    foreach ([$abs, $abs . '.git'] as $cand) if ($src === null && ($found = lab58_git_find($w, $cand)) !== null && ($found['root'] ?? $found['gd']) === $cand) [$src, $abs] = [$found, $cand];
    if ($src === null) { $w->tip(tr('Na cestě „{url}“ repozitář není. Zkontroluj ji (ls /srv/git).', ['url' => $url])); return lab58_git_fatal($p, "repository '" . $url . "' does not exist"); }
    $dest = $w->abs($name);
    if ($w->fs->exists($dest) && (!$w->fs->isDir($dest) || $w->fs->children($dest) !== [])) return lab58_git_fatal($p, "destination path '" . $name . "' already exists and is not an empty directory.");
    for ($parent = Lab57Vfs::dirname($dest); !$w->fs->exists($parent); $parent = Lab57Vfs::dirname($parent));
    if (!$w->root && !$w->canModifyDir($parent)) return lab58_git_fatal($p, "could not create work tree dir '" . $name . "': Permission denied");
    if (isset($o['b']) && lab58_git_ref($w, $src, 'refs/heads/' . $o['b']) === null) return lab58_git_fatal($p, 'Remote branch ' . $o['b'] . ' not found in upstream origin');
    if (empty($o['q'])) $p->err("Cloning into '" . $name . "'...\n");
    lab58_git_clone_into($w, $src, $abs, $dest, isset($o['b']) ? (string)$o['b'] : null);
    if (empty($o['q'])) $p->err("done.\n");
    return 0;
}
