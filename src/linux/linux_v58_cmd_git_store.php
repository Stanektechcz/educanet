<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – Git v terminálu (LAB-06): úložiště repozitáře. Vlastní zjednodušený výukový model
 * (není to formát skutečného gitu) – všechno jsou záznamy ve VFS:
 *   .git/HEAD                  „ref: refs/heads/main“ nebo id commitu (odpojená HEAD)
 *   .git/objects/<id>.json     blob {type,size,data=base64} · tree {type,entries{cesta: id blobu}}
 *                              commit {type,tree,parents,author,committer,message} · tag {type,object,tag,tagger,message}
 *   .git/refs/heads|tags|remotes/…   id + "\n" (symbolický ref: „ref: refs/remotes/origin/main“)
 *   .git/index.json            {"entries": {cesta: id blobu}} – snímek připravený pro další commit
 *   .git/stash.json            odložené změny (nejnovější první) · .git/config a ~/.gitconfig ve tvaru INI
 * id objektu = sha1 kanonického JSON (seřazené klíče) – jen identifikátor, žádná komprese ani delta.
 * Strom je plochý (cesta → blob), mód souborů se neukládá. Obsah blobu je v Base64 (obdoba komprese),
 * takže grep ve .git/objects nic nenajde. Nic se nespouští, žádná síť.
 */

const LAB58_GIT_VERSION = '2.39.5';
const LAB58_GIT_ZERO = '0000000';
const LAB58_GIT_MAX_WALK = 400;

function lab58_git_join(string $dir, string $name): string { return ($dir === '/' ? '' : rtrim($dir, '/')) . '/' . $name; }

/** Najde repozitář od složky nahoru. @return array{gd:string,root:?string}|null (root null = holý repozitář) */
function lab58_git_find(Lab57World $w, ?string $start = null): ?array
{
    $dir = $start ?? $w->cwd;
    for ($i = 0; $i < 64; $i++) {
        $gd = lab58_git_join($dir, '.git');
        if ($w->fs->isDir($gd) && $w->fs->isFile($gd . '/HEAD') && $w->canTraverse($gd . '/HEAD')) return ['gd' => $gd, 'root' => $dir];
        if ($w->fs->isFile(lab58_git_join($dir, 'HEAD')) && $w->fs->isDir(lab58_git_join($dir, 'objects')) && $w->fs->isDir(lab58_git_join($dir, 'refs'))) return ['gd' => $dir, 'root' => null];
        if ($dir === '/') return null;
        $dir = Lab57Vfs::dirname($dir);
    }
    return null;
}

/** Smí efektivní uživatel do repozitáře zapisovat? */
function lab58_git_writable(Lab57World $w, array $r): bool { $n = $w->fs->get($r['gd']); return $n !== null && ($w->root || ($w->can($n, 'w') && $w->can($n, 'x'))); }

/** Vytvoří složku i s nadřazenými (patří efektivnímu uživateli). */
function lab58_git_mkdir(Lab57World $w, string $abs): void
{
    $owner = $w->effectiveUser();
    $cur = '';
    foreach (array_filter(explode('/', $abs), 'strlen') as $part) {
        $cur .= '/' . $part;
        if (!$w->fs->exists($cur)) $w->fs->set($cur, ['t' => 'd', 'm' => 0755, 'u' => $owner, 'g' => $w->primaryGroup($owner), 'mt' => $w->now]);
    }
}

/** Zapíše soubor (vnitřek .git i pracovní strom) a chybějící složky; nové položky patří efektivnímu uživateli. */
function lab58_git_write(Lab57World $w, string $abs, string $content, int $mode = 0644): void
{
    $owner = $w->effectiveUser();
    lab58_git_mkdir($w, Lab57Vfs::dirname($abs));
    $node = $w->fs->get($abs);
    $w->fs->set($abs, ['t' => 'f', 'm' => (int)($node['m'] ?? $mode), 'u' => (string)($node['u'] ?? $owner), 'g' => (string)($node['g'] ?? $w->primaryGroup($owner)), 'mt' => $w->now, 'c' => $content]);
}

// --- Objekty -----------------------------------------------------------------

function lab58_git_canon(mixed $v): mixed
{
    if (is_array($v) && !array_is_list($v)) ksort($v, SORT_STRING);
    return is_array($v) ? array_map('lab58_git_canon', $v) : $v;
}

function lab58_git_json(array $obj): string { return (string)json_encode(lab58_git_canon($obj), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE); }

function lab58_git_put(Lab57World $w, array $r, array $obj): string
{
    $json = lab58_git_json($obj);
    $path = $r['gd'] . '/objects/' . sha1($json) . '.json';
    if (!$w->fs->exists($path)) lab58_git_write($w, $path, $json . "\n", 0444);
    return sha1($json);
}

function lab58_git_obj(Lab57World $w, array $r, ?string $id): ?array
{
    $obj = $id !== null && preg_match('/^[0-9a-f]{40}$/', $id) === 1 ? json_decode((string)($w->fs->get($r['gd'] . '/objects/' . $id . '.json')['c'] ?? ''), true) : null;
    return is_array($obj) && isset($obj['type']) ? $obj : null;
}

function lab58_git_blob_obj(string $content): array { return ['type' => 'blob', 'size' => strlen($content), 'data' => base64_encode($content)]; }
function lab58_git_blob_id(string $content): string { return sha1(lab58_git_json(lab58_git_blob_obj($content))); }
function lab58_git_blob_put(Lab57World $w, array $r, string $content): string { return lab58_git_put($w, $r, lab58_git_blob_obj($content)); }

function lab58_git_blob(Lab57World $w, array $r, ?string $id): ?string
{
    $obj = lab58_git_obj($w, $r, $id);
    $data = $obj !== null && $obj['type'] === 'blob' ? base64_decode((string)($obj['data'] ?? ''), true) : false;
    return $data === false ? null : $data;
}

/** Snímek commitu, značky nebo stromu: cesta → id blobu. */
function lab58_git_tree(Lab57World $w, array $r, ?string $id): array
{
    $obj = lab58_git_obj($w, $r, $id);
    for ($i = 0; $i < 4 && $obj !== null && $obj['type'] !== 'tree'; $i++) $obj = lab58_git_obj($w, $r, (string)($obj['tree'] ?? ($obj['object'] ?? '')));
    $out = [];
    foreach ((array)($obj !== null && $obj['type'] === 'tree' ? ($obj['entries'] ?? []) : []) as $path => $blob) $out[(string)$path] = (string)$blob;
    ksort($out, SORT_STRING);
    return $out;
}

function lab58_git_tree_put(Lab57World $w, array $r, array $map): string { ksort($map, SORT_STRING); return lab58_git_put($w, $r, ['type' => 'tree', 'entries' => $map]); }

/** $author/$committer = {name, email, time} */
function lab58_git_commit_put(Lab57World $w, array $r, array $files, array $parents, array $author, array $committer, string $message): string
{
    return lab58_git_put($w, $r, ['type' => 'commit', 'tree' => lab58_git_tree_put($w, $r, $files), 'parents' => array_values($parents), 'author' => $author, 'committer' => $committer, 'message' => $message]);
}

function lab58_git_subject(?array $commit): string { return (string)strtok((string)($commit['message'] ?? '') . "\n", "\n"); }

/** Zpráva jako v gitu: bez koncových mezer a prázdných řádků na okrajích. */
function lab58_git_clean_msg(string $msg): string
{
    return trim(implode("\n", array_map('rtrim', explode("\n", str_replace("\r", '', $msg)))), "\n");
}

// --- Refs, HEAD, index ---------------------------------------------------------

function lab58_git_ref_raw(Lab57World $w, array $r, string $ref): ?string
{
    $node = preg_match('~^[A-Za-z0-9._/-]+$~', $ref) === 1 && !str_contains($ref, '..') ? $w->fs->get($r['gd'] . '/' . $ref) : null;
    return $node !== null && ($node['t'] ?? '') === 'f' ? trim((string)($node['c'] ?? '')) : null;
}

function lab58_git_ref(Lab57World $w, array $r, string $ref, int $depth = 0): ?string
{
    $raw = lab58_git_ref_raw($w, $r, $ref);
    if ($raw !== null && str_starts_with($raw, 'ref: ')) return $depth < 4 ? lab58_git_ref($w, $r, substr($raw, 5), $depth + 1) : null;
    return $raw !== null && preg_match('/^[0-9a-f]{40}$/', $raw) === 1 ? $raw : null;
}

function lab58_git_ref_set(Lab57World $w, array $r, string $ref, string $value): void { lab58_git_write($w, $r['gd'] . '/' . $ref, $value . "\n"); }

/** @return array<string,string> jméno bez prefixu → id (seřazené) */
function lab58_git_refs(Lab57World $w, array $r, string $prefix): array
{
    $base = $r['gd'] . '/' . $prefix;
    $out = [];
    foreach ($w->fs->tree($base) as $path) {
        $id = $path !== $base && $w->fs->isFile($path) ? lab58_git_ref($w, $r, $prefix . '/' . substr($path, strlen($base) + 1)) : null;
        if ($id !== null) $out[substr($path, strlen($base) + 1)] = $id;
    }
    ksort($out, SORT_STRING);
    return $out;
}

/** @return array{branch:?string,id:?string} */
function lab58_git_head(Lab57World $w, array $r): array
{
    $raw = (string)lab58_git_ref_raw($w, $r, 'HEAD');
    if (str_starts_with($raw, 'ref: refs/heads/')) return ['branch' => substr($raw, 16), 'id' => lab58_git_ref($w, $r, substr($raw, 5))];
    return ['branch' => null, 'id' => preg_match('/^[0-9a-f]{40}$/', $raw) === 1 ? $raw : null];
}

/** Posune aktuální větev (nebo odpojenou HEAD) na commit. */
function lab58_git_advance(Lab57World $w, array $r, string $id): void
{
    $branch = lab58_git_head($w, $r)['branch'];
    lab58_git_ref_set($w, $r, $branch !== null ? 'refs/heads/' . $branch : 'HEAD', $id);
}

function lab58_git_index(Lab57World $w, array $r): array
{
    $out = [];
    foreach ((array)(json_decode((string)($w->fs->get($r['gd'] . '/index.json')['c'] ?? ''), true)['entries'] ?? []) as $path => $id) $out[(string)$path] = (string)$id;
    ksort($out, SORT_STRING);
    return $out;
}

function lab58_git_index_save(Lab57World $w, array $r, array $map): void
{
    ksort($map, SORT_STRING);
    lab58_git_write($w, $r['gd'] . '/index.json', (string)json_encode(['entries' => (object)$map], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
}

// --- Pracovní strom a stav -----------------------------------------------------

/** @return array<string,string> cesta od kořene → obsah (bez .git a vnořených repozitářů) */
function lab58_git_worktree(Lab57World $w, array $r): array
{
    $root = (string)$r['root'];
    $cut = strlen(lab58_git_join($root, ''));
    $out = $skip = [];
    foreach ($w->fs->tree($root) as $path) {
        $rel = substr($path, $cut);
        if ($path === $root || array_filter($skip, static fn(string $s): bool => str_starts_with($rel, $s)) !== []) continue;
        $node = (array)$w->fs->get($path);
        if (($node['t'] ?? '') !== 'd') $out[$rel] = (string)($node['c'] ?? '');
        elseif (Lab57Vfs::basename($path) === '.git' || $w->fs->exists($path . '/.git')) $skip[] = $rel . '/';
    }
    return $out;
}

function lab58_git_wt_write(Lab57World $w, array $r, string $rel, string $content): void { lab58_git_write($w, lab58_git_join((string)$r['root'], $rel), $content); }

/** Smaže soubor z pracovního stromu i prázdné nadřazené složky (git prázdné složky nezná). */
function lab58_git_wt_remove(Lab57World $w, array $r, string $rel): void
{
    $root = (string)$r['root'];
    $abs = lab58_git_join($root, $rel);
    if ($w->fs->isFile($abs)) $w->fs->delete($abs);
    for ($dir = Lab57Vfs::dirname($abs); $dir !== $root && $dir !== '/' && $w->fs->isDir($dir) && $w->fs->children($dir) === []; $dir = Lab57Vfs::dirname($dir)) $w->fs->delete($dir);
}

/** .gitignore v kořeni: vzory * ? [], koncové / = jen složky, / na začátku či uvnitř = od kořene, ! = výjimka. */
function lab58_git_ignored(string $rules, string $path): bool
{
    $ignored = false;
    $parts = explode('/', $path);
    foreach (explode("\n", $rules) as $line) {
        $line = rtrim($line);
        if ($line === '' || $line[0] === '#') continue;
        $pat = $line[0] === '!' ? substr($line, 1) : $line;
        [$anchored, $dirOnly, $pat] = [str_contains(rtrim($pat, '/'), '/'), str_ends_with($pat, '/'), trim($pat, '/')];
        for ($i = 0, $n = count($parts); $i < $n; $i++) {
            $cand = $anchored ? implode('/', array_slice($parts, 0, $i + 1)) : $parts[$i];
            if (!($dirOnly && $i === $n - 1) && $pat !== '' && lab57_glob_match($pat, $cand)) { $ignored = $line[0] !== '!'; break; }
        }
    }
    return $ignored;
}

/** Celý stav: head, tree (HEAD), index, work, ids, staged/unstaged (cesta → A|M|D) a untracked. */
function lab58_git_status_data(Lab57World $w, array $r): array
{
    $head = lab58_git_head($w, $r);
    $tree = lab58_git_tree($w, $r, $head['id']);
    $index = lab58_git_index($w, $r);
    $work = $r['root'] === null ? [] : lab58_git_worktree($w, $r);
    $ids = $staged = $unstaged = $untracked = [];
    foreach ($work as $path => $content) $ids[(string)$path] = lab58_git_blob_id($content);
    foreach (array_unique(array_map('strval', array_merge(array_keys($tree), array_keys($index)))) as $path) {
        [$a, $b] = [$tree[$path] ?? null, $index[$path] ?? null];
        if ($a !== $b) $staged[$path] = $a === null ? 'A' : ($b === null ? 'D' : 'M');
    }
    ksort($staged, SORT_STRING);
    foreach ($index as $path => $id) if (($ids[$path] ?? null) !== $id) $unstaged[$path] = isset($ids[$path]) ? 'M' : 'D';
    foreach (array_keys($ids) as $path) {
        if (!isset($index[(string)$path]) && !lab58_git_ignored((string)($work['.gitignore'] ?? ''), (string)$path)) $untracked[lab58_git_untracked_dir((string)$path, $index)] = true;
    }
    $untracked = array_map('strval', array_keys($untracked));
    sort($untracked, SORT_STRING);
    return ['head' => $head, 'tree' => $tree, 'index' => $index, 'work' => $work, 'ids' => $ids, 'staged' => $staged, 'unstaged' => $unstaged, 'untracked' => $untracked];
}

/** Nesledovaná složka se v přehledu ukáže jako celek (img/), soubor ve sledované složce zvlášť. */
function lab58_git_untracked_dir(string $path, array $index): string
{
    $parts = explode('/', $path);
    $prefix = '';
    for ($i = 0; $i < count($parts) - 1; $i++) {
        $prefix .= $parts[$i] . '/';
        if (array_filter(array_keys($index), static fn($p): bool => str_starts_with((string)$p, $prefix)) === []) return $prefix;
    }
    return $path;
}

// --- Revize: HEAD, @, větev, značka, origin/x, (zkrácené) id, přípony ~N ^ ^N --------

function lab58_git_peel(Lab57World $w, array $r, ?string $id): ?string
{
    for ($i = 0, $obj = lab58_git_obj($w, $r, $id); $i < 4 && ($obj['type'] ?? '') === 'tag'; $i++) $obj = lab58_git_obj($w, $r, $id = (string)$obj['object']);
    return ($obj['type'] ?? '') === 'commit' ? $id : null;
}

function lab58_git_resolve_base(Lab57World $w, array $r, string $name): ?string
{
    if ($name === 'HEAD' || $name === '@') return lab58_git_head($w, $r)['id'];
    if (preg_match('/^[0-9a-f]{40}$/', $name) === 1) return lab58_git_obj($w, $r, $name) !== null ? $name : null;
    foreach (['refs/tags/' . $name, 'refs/heads/' . $name, 'refs/remotes/' . $name, str_starts_with($name, 'refs/') || preg_match('/^[A-Z]+_HEAD$/', $name) === 1 ? $name : '', 'refs/remotes/' . $name . '/HEAD'] as $ref) {
        $id = $ref === '' ? null : lab58_git_ref($w, $r, $ref);
        if ($id !== null) return $id;
    }
    if (preg_match('/^[0-9a-f]{4,39}$/', $name) !== 1) return null;
    $found = array_values(array_filter($w->fs->children($r['gd'] . '/objects'), static fn(string $f): bool => str_starts_with($f, $name) && str_ends_with($f, '.json')));
    return count($found) === 1 ? substr($found[0], 0, -5) : null;
}

/** Revize → id commitu (značky se rozbalí); $peel=false nechá id značky/blobu/stromu beze změny. */
function lab58_git_resolve(Lab57World $w, array $r, string $rev, bool $peel = true): ?string
{
    if (preg_match('/^(.+?)((?:[~^][0-9]*)*)$/', $rev, $m) !== 1) return null;
    $id = lab58_git_resolve_base($w, $r, $m[1]);
    if ($id === null || ($m[2] === '' && !$peel)) return $id;
    $id = lab58_git_peel($w, $r, $id);
    preg_match_all('/([~^])([0-9]*)/', $m[2], $ops, PREG_SET_ORDER);
    foreach ($ops as [, $op, $num]) {
        $n = $num === '' ? 1 : (int)$num;
        if ($op === '^') $id = $n === 0 ? $id : ((string)(lab58_git_obj($w, $r, $id)['parents'][$n - 1] ?? '') ?: null);
        else for ($i = 0; $i < $n && $id !== null; $i++) $id = (string)(lab58_git_obj($w, $r, $id)['parents'][0] ?? '') ?: null;
        if ($id === null) return null;
    }
    return $id;
}

/** @return array<string,true> commity dosažitelné z $id (včetně) */
function lab58_git_reach(Lab57World $w, array $r, ?string $id): array
{
    $seen = [];
    $queue = $id === null ? [] : [$id];
    while ($queue !== [] && count($seen) < LAB58_GIT_MAX_WALK) {
        $c = (string)array_shift($queue);
        if (isset($seen[$c])) continue;
        $seen[$c] = true;
        foreach ((array)(lab58_git_obj($w, $r, $c)['parents'] ?? []) as $parent) $queue[] = (string)$parent;
    }
    return $seen;
}

/** @return list<string> commity od nejnovějšího jako git log: fronta podle času commitu, rodič až po svém potomkovi (i z více začátků – --all) */
function lab58_git_walk(Lab57World $w, array $r, array $starts): array
{
    [$out, $seen, $queue] = [[], [], []];
    $push = static function (string $id) use ($w, $r, &$seen, &$queue): void { if (!isset($seen[$id])) $queue[] = [(int)(lab58_git_obj($w, $r, $id)['committer']['time'] ?? 0), -count($seen), $seen[$id] = $id]; };
    foreach ($starts as $s) if ($s !== null) $push((string)$s);
    while ($queue !== [] && count($out) < LAB58_GIT_MAX_WALK) {
        rsort($queue);
        foreach ((array)(lab58_git_obj($w, $r, $out[] = (string)array_shift($queue)[2])['parents'] ?? []) as $parent) $push((string)$parent);
    }
    return $out;
}

/** @return array{0:int,1:int} o kolik commitů je $a napřed a pozadu vůči $b */
function lab58_git_ahead_behind(Lab57World $w, array $r, ?string $a, ?string $b): array
{
    return [count(array_diff_key(lab58_git_reach($w, $r, $a), lab58_git_reach($w, $r, $b))), count(array_diff_key(lab58_git_reach($w, $r, $b), lab58_git_reach($w, $r, $a)))];
}

// --- Čas (formáty jako git log --date=…) -------------------------------------

function lab58_git_date(int $ts, string $mode = 'default', int $now = 0): string
{
    if ($mode === 'relative') return lab58_git_reldate($ts, $now);
    return date(['short' => 'Y-m-d', 'iso' => 'Y-m-d H:i:s O', 'iso8601' => 'Y-m-d H:i:s O', 'iso-strict' => 'c', 'rfc' => 'D, j M Y H:i:s O', 'unix' => 'U'][$mode] ?? 'D M j H:i:s Y O', $ts);
}

function lab58_git_reldate(int $ts, int $now): string
{
    [$d, $min] = [$now - $ts, intdiv($now - $ts + 30, 60)];
    [$hours, $days] = [intdiv($min + 30, 60), intdiv(intdiv($min + 30, 60) + 12, 24)];
    [$n, $unit] = match (true) {
        $d < 90 => [$d, 'second'], $min < 90 => [$min, 'minute'], $hours < 36 => [$hours, 'hour'], $days < 14 => [$days, 'day'],
        $days < 70 => [intdiv($days + 3, 7), 'week'], $days < 365 => [intdiv($days + 15, 30), 'month'], default => [intdiv($days, 365), 'year'],
    };
    return $d < 0 ? 'in the future' : $n . ' ' . $unit . ($n === 1 ? '' : 's') . ' ago';
}

// --- Stash (.git/stash.json, nejnovější první) -----------------------------------

function lab58_git_stash_list(Lab57World $w, array $r): array
{
    $data = json_decode((string)($w->fs->get($r['gd'] . '/stash.json')['c'] ?? ''), true);
    return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
}

function lab58_git_stash_save(Lab57World $w, array $r, array $list): void
{
    if ($list === []) { $w->fs->delete($r['gd'] . '/stash.json'); return; }
    lab58_git_write($w, $r['gd'] . '/stash.json', (string)json_encode(array_values($list), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
}

// --- Upstream větve (branch.<jméno>.remote/merge) ----------------------------------

/** @return array{0:?string,1:int,2:int,3:bool} [upstream (origin/main), napřed, pozadu, upstream zmizel] */
function lab58_git_upstream(Lab57World $w, array $r, string $branch): array
{
    [$remote, $merge] = [lab58_git_cfg_get($w, $r, 'branch.' . $branch . '.remote'), (string)lab58_git_cfg_get($w, $r, 'branch.' . $branch . '.merge')];
    if ($remote === null || !str_starts_with($merge, 'refs/heads/')) return [null, 0, 0, false];
    $up = $remote . '/' . substr($merge, 11);
    $upId = lab58_git_ref($w, $r, 'refs/remotes/' . $up);
    return array_merge([$up], $upId === null ? [0, 0] : lab58_git_ahead_behind($w, $r, lab58_git_ref($w, $r, 'refs/heads/' . $branch), $upId), [$upId === null]);
}

/** Řádky „Your branch is …“ jako v git status / git checkout (prázdné, když větev nemá upstream). */
function lab58_git_tracking(Lab57World $w, array $r, string $branch): string
{
    [$up, $a, $b, $gone] = lab58_git_upstream($w, $r, $branch);
    $n = static fn(int $k): string => $k . ' commit' . ($k === 1 ? '' : 's');
    return match (true) {
        $up === null => '',
        $gone => "Your branch is based on '$up', but the upstream is gone.\n  (use \"git branch --unset-upstream\" to fixup)\n",
        $a === 0 && $b === 0 => "Your branch is up to date with '$up'.\n",
        $b === 0 => "Your branch is ahead of '$up' by " . $n($a) . ".\n  (use \"git push\" to publish your local commits)\n",
        $a === 0 => "Your branch is behind '$up' by " . $n($b) . ", and can be fast-forwarded.\n  (use \"git pull\" to update your local branch)\n",
        default => "Your branch and '$up' have diverged,\nand have $a and $b different commits each, respectively.\n  (use \"git pull\" to merge the remote branch into yours)\n",
    };
}
