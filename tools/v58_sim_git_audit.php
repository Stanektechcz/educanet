<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – audit simulace gitu (LAB-06): přesné výstupy a exit kódy podpříkazů, chyby a české tipy,
 * determinismus, perzistence repozitáře mezi požadavky (lab57_session), řešitelnost úrovní balíčku „git“ na 5 semínkách,
 * cizí/stará odpověď neprojde, příručka s funkčními příklady, token-sken souborů simulace.
 * Izolovaně: jen jádro + soubory gitu (lab58_skip_ext), dočasné úložiště. Nic se nespouští, žádná síť.
 * Konec: V58_SIM_GIT_AUDIT_OK checks=N failed=0 (jinak V58_SIM_GIT_AUDIT_FAIL, exit 1).
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Prague');
$ROOT = dirname(__DIR__);
$TMP = sys_get_temp_dir() . '/v58_sim_git_audit_' . bin2hex(random_bytes(6));
mkdir($TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $TMP;
$GLOBALS['lab57_secret_override'] = 'v58-sim-git-audit';
$GLOBALS['lab58_skip_ext'] = true;
register_shutdown_function(static function () use ($TMP): void {
    if (!is_dir($TMP)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($TMP, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($TMP);
});
$GLOBALS['ga_warn'] = [];
set_error_handler(static function (int $no, string $str, string $file, int $line): bool { $GLOBALS['ga_warn'][] = $str . ' (' . basename($file) . ':' . $line . ')'; return true; });

require_once $ROOT . '/linux_v57_lab.php';
const GA_FILES = ['linux_v58_cmd_git.php', 'linux_v58_cmd_git_store.php', 'linux_v58_cmd_git_diff.php', 'linux_v58_cmd_git_more.php', 'linux_v58_levels_git.php'];
foreach (GA_FILES as $file) require_once $ROOT . '/' . $file;
const GA_NOW = 1790000000;

$GLOBALS['ga'] = ['checks' => 0, 'failed' => 0];
function ga_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['ga']['checks']++;
    if (!$ok) $GLOBALS['ga']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}

/** Spustí řádek v terminálu světa (čas +60 s). @return array{out:string,err:string,exit:int,tips:string} */
function ga_run(Lab57World $w, string $line): array
{
    $w->now += 60;
    $run = lab57_run_line($w, $line);
    [$out, $err] = ['', ''];
    foreach ($run['chunks'] as [$fd, $text]) { if ($fd === 1) $out .= $text; else $err .= $text; }
    return ['out' => (string)preg_replace('/\e\[[0-9;]*m/', '', $out), 'err' => $err, 'exit' => (int)$run['exit'], 'tips' => implode(' | ', $w->tips)];
}

/** Očekávání: řetězec = přesná shoda, „~…~“ = regulární výraz, null = nekontroluje se. */
function ga_match(?string $want, string $got): bool
{
    return $want === null || (str_starts_with($want, '~') ? preg_match($want, $got) === 1 : $got === $want);
}

function ga_cmd(Lab57World $w, string $name, string $line, ?string $out, int $exit = 0, ?string $err = '', ?string $tip = null): array
{
    $r = ga_run($w, $line);
    $ok = $r['exit'] === $exit && ga_match($out, $r['out']) && ga_match($err, $r['err']) && ($tip === null || str_contains($r['tips'], $tip));
    ga_check($name, $ok, '`' . $line . '` exit=' . $r['exit'] . ' out=' . var_export($r['out'], true) . ' err=' . var_export($r['err'], true) . ' tips=' . $r['tips']);
    return $r;
}

function ga_world(string $levelId, string $seed, int $now = GA_NOW, string $code = 'EDU-AUDT-0001'): Lab57World
{
    $level = $levelId === 'sandbox' ? lab57_sandbox_level() : (array)lab57_level($levelId);
    $w = lab57_build_world($level, $seed, ['CODE' => $code], $now);
    $a = &lab57_active();
    $a = ['level' => $level, 'row' => ['started' => $now, 'hints' => 0, 'cmds' => 0], 'result' => [], 'codes' => ['CODE' => $code], 'seed' => $seed, 'now' => $now, 'race' => null, 'foreign' => null];
    return $w;
}

function ga_rev(Lab57World $w, string $rev, int $len = 40): string { return substr(trim(ga_run($w, 'git rev-parse ' . $rev)['out']), 0, $len); }
function ga_date(int $ts, string $fmt = 'D M j H:i:s Y O'): string { return date($fmt, $ts); }

// ---------------------------------------------------------------------------
// 0) Registrace, balíček apt, příručka, bezpečnost souborů
// ---------------------------------------------------------------------------

lab57_levels();
lab58_manual();
ga_check('registry:no-errors', lab58_registry_errors() === [], implode(' | ', lab58_registry_errors()));
ga_check('registry:git-command', isset(lab57_command_registry()['git']) && lab57_command_package('git') === 'git' && (lab58_default_packages()['git'] ?? '') !== '');
$levels = lab57_pack_levels('git');
$pack = lab58_pack('git');
ga_check('pack:shape', $pack !== null && $pack['classes'] === null && $pack['unlock'] === 'sequential' && count($levels) >= 6 && array_filter($levels, static fn(array $l): bool => !str_starts_with($l['id'], 'git-')) === [], json_encode(array_column($levels, 'id')));
$invalid = array_merge(...array_map(static fn(array $l): array => lab58_validate_level(['pack' => 'git'] + $l), lab58_gitlv_levels()));
ga_check('pack:validate', $invalid === [], implode(' | ', $invalid));
foreach ($levels as $l) ga_check('level:' . $l['id'] . ':hints-1-3', count($l['hints']) >= 1 && count($l['hints']) <= 3 && is_callable($l['solution'] ?? null) && $l['story'] !== '' && $l['learn'] !== '');

$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'pfsockopen', 'stream_socket_client', 'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr', 'mail', 'unserialize', 'file_get_contents', 'fopen', 'file', 'include', 'curl_init'];
foreach (array_merge(GA_FILES, ['tools/v58_sim_git_audit.php']) as $file) {
    $src = (string)file_get_contents($ROOT . '/' . $file);
    $bad = [];
    try {
        $tokens = token_get_all($src, TOKEN_PARSE);
    } catch (ParseError $e) {
        $tokens = [];
        $bad[] = 'syntax: ' . $e->getMessage();
    }
    foreach ($tokens as $i => $tok) {
        if ($tok === '`') $bad[] = 'backtick';
        if (!is_array($tok) || !in_array($tok[0], [T_STRING, T_INCLUDE, T_INCLUDE_ONCE, T_EVAL], true)) continue;
        $name = strtolower($tok[1]);
        $j = $i + 1;
        while (is_array($tokens[$j] ?? null) && $tokens[$j][0] === T_WHITESPACE) $j++;
        $k = $i - 1;
        while (is_array($tokens[$k] ?? null) && $tokens[$k][0] === T_WHITESPACE) $k--;
        $method = is_array($tokens[$k] ?? null) && in_array($tokens[$k][0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true);
        $isSim = $file !== 'tools/v58_sim_git_audit.php';
        if (!$method && ($tok[0] !== T_STRING || ($tokens[$j] ?? null) === '(') && (in_array($name, $forbidden, true) || str_starts_with($name, 'socket_') || str_starts_with($name, 'curl_')) && ($isSim || !in_array($name, ['file_get_contents', 'fopen'], true))) $bad[] = $name . '@' . $tok[2];
    }
    ga_check('safety:' . $file, $bad === [], implode(', ', $bad));
    ga_check('size:' . $file . ':<400', substr_count($src, "\n") < 400, (string)substr_count($src, "\n"));
}

function ga_expect(string $name, array $r, string $out, int $exit = 0, ?string $err = ''): void
{
    ga_check($name, $r['exit'] === $exit && ga_match($out, $r['out']) && ga_match($err, $r['err']), 'exit=' . $r['exit'] . ' out=' . var_export($r['out'], true) . ' err=' . var_export($r['err'], true));
}

// ---------------------------------------------------------------------------
// 1) Základní tok v pískovišti: přesné výstupy, exit kódy, stderr a české tipy
// ---------------------------------------------------------------------------

$w = ga_world('sandbox', 'ga-basic');
ga_cmd($w, 'err:not-a-repo', 'git status', '', 128, "fatal: not a git repository (or any of the parent directories): .git\n", 'git init');
ga_cmd($w, 'version', 'git --version', "git version 2.39.5\n");
ga_cmd($w, 'err:typo-suggestion', 'git comit', '', 1, "git: 'comit' is not a git command. See 'git --help'.\n\nThe most similar command is\n\tcommit\n", 'git commit');
ga_cmd($w, 'err:unsupported-push', 'git push', '', 1, "git push: tento podpříkaz simulace zatím neumí\n", 'síť');
ga_cmd($w, 'init', 'git init web', "Initialized empty Git repository in /home/student/web/.git/\n");
ga_run($w, 'cd web');
ga_cmd($w, 'status:empty', 'git status', "On branch main\n\nNo commits yet\n\nnothing to commit (create/copy files and use \"git add\" to track)\n");
foreach (["printf 'a\\nb\\nc\\n' > f.txt", 'mkdir img', 'echo x > img/logo.txt'] as $line) ga_run($w, $line);
ga_cmd($w, 'status:short-untracked', 'git status -s', "?? f.txt\n?? img/\n");
ga_cmd($w, 'add', 'git add f.txt', '');
ga_cmd($w, 'err:pathspec', 'git add neni.txt', '', 128, "fatal: pathspec 'neni.txt' did not match any files\n");
ga_cmd($w, 'err:identity', 'git commit -m Prvni', '', 128, "~^Author identity unknown\n.*fatal: unable to auto-detect email address \\(got 'student@lab-pc\\.\\(none\\)'\\)\n$~s", 'user.name');
ga_cmd($w, 'config:set-name', 'git config --global user.name "Test Žák"', '');
ga_cmd($w, 'config:set-email', 'git config --global user.email test@skola.test', '');
ga_cmd($w, 'config:get', 'git config user.name', "Test Žák\n");
ga_cmd($w, 'config:missing-key', 'git config user.phone', '', 1);
ga_cmd($w, 'config:bad-key', 'git config jmeno', '', 1, "error: key does not contain a section: jmeno\n", 'user.name');
ga_cmd($w, 'config:list', 'git config --list', "user.name=Test Žák\nuser.email=test@skola.test\ncore.repositoryformatversion=0\ncore.filemode=true\ncore.bare=false\ncore.logallrefupdates=true\n");
ga_cmd($w, 'config:file', 'cat ~/.gitconfig', "[user]\n\tname = Test Žák\n\temail = test@skola.test\n");
$r = ga_run($w, 'git commit -m Prvni');
$h1 = ga_rev($w, 'HEAD');
ga_expect('commit:root', $r, '[main (root-commit) ' . substr($h1, 0, 7) . "] Prvni\n 1 file changed, 3 insertions(+)\n create mode 100644 f.txt\n");
ga_check('store:object-json', is_array(json_decode((string)($w->fs->get('/home/student/web/.git/objects/' . $h1 . '.json')['c'] ?? ''), true)) && sha1(rtrim((string)$w->fs->get('/home/student/web/.git/objects/' . $h1 . '.json')['c'], "\n")) === $h1);
ga_run($w, "printf 'a\\nB\\nc\\nd\\n' > f.txt");
[$b1, $b2] = [substr(lab58_git_blob_id("a\nb\nc\n"), 0, 7), substr(lab58_git_blob_id("a\nB\nc\nd\n"), 0, 7)];
ga_cmd($w, 'diff:worktree', 'git diff', "diff --git a/f.txt b/f.txt\nindex $b1..$b2 100644\n--- a/f.txt\n+++ b/f.txt\n@@ -1,3 +1,4 @@\n a\n-b\n+B\n c\n+d\n");
ga_cmd($w, 'diff:stat', 'git diff --stat', " f.txt | 3 ++-\n 1 file changed, 2 insertions(+), 1 deletion(-)\n");
ga_cmd($w, 'status:long', 'git status', "On branch main\nChanges not staged for commit:\n  (use \"git add <file>...\" to update what will be committed)\n  (use \"git restore <file>...\" to discard changes in working directory)\n\tmodified:   f.txt\n\nUntracked files:\n  (use \"git add <file>...\" to include in what will be committed)\n\timg/\n\nno changes added to commit (use \"git add\" and/or \"git commit -a\")\n");
ga_run($w, 'git add f.txt');
ga_cmd($w, 'diff:staged', 'git diff --staged --name-status', "M\tf.txt\n");
ga_cmd($w, 'status:short-staged', 'git status -s', "M  f.txt\n?? img/\n");
$r = ga_run($w, 'git commit -m Druhy');
$h2 = ga_rev($w, 'HEAD');
ga_expect('commit:second', $r, '[main ' . substr($h2, 0, 7) . "] Druhy\n 1 file changed, 2 insertions(+), 1 deletion(-)\n");
[$s1, $s2] = [substr($h1, 0, 7), substr($h2, 0, 7)];
ga_cmd($w, 'log:oneline-tty', 'git log --oneline', "$s2 (HEAD -> main) Druhy\n$s1 Prvni\n");
ga_cmd($w, 'log:oneline-pipe', 'git log --oneline | cat', "$s2 Druhy\n$s1 Prvni\n");
$repo = (array)lab58_git_find($w);
[$t1, $t2] = [(int)lab58_git_obj($w, $repo, $h1)['author']['time'], (int)lab58_git_obj($w, $repo, $h2)['author']['time']];
ga_cmd($w, 'log:medium-n1', 'git log -n 1', "commit $h2 (HEAD -> main)\nAuthor: Test Žák <test@skola.test>\nDate:   " . ga_date($t2) . "\n\n    Druhy\n");
ga_cmd($w, 'log:patch', 'git log -p -1 --no-decorate', "~^commit $h2\n.*    Druhy\n\ndiff --git a/f.txt b/f.txt\nindex $b1\\.\\.$b2 100644\n~s");
ga_cmd($w, 'log:format', "git log --format='%h|%an|%s' --date=short", "$s2|Test Žák|Druhy\n$s1|Test Žák|Prvni\n");
ga_cmd($w, 'log:author-filter', 'git log --oneline --author=Nikdo', '');
ga_cmd($w, 'show:rev-path', 'git show HEAD~1:f.txt', "a\nb\nc\n");
ga_cmd($w, 'show:path-missing', 'git show HEAD~1:img/logo.txt', '', 128, "fatal: path 'img/logo.txt' exists on disk, but not in 'HEAD~1'\n");
[$d1, $d2] = [ga_date($t1, 'Y-m-d H:i:s O'), ga_date($t2, 'Y-m-d H:i:s O')];
ga_cmd($w, 'blame', 'git blame f.txt', "^$s1 (Test Žák $d1 1) a\n" . substr($h2, 0, 8) . " (Test Žák $d2 2) B\n^$s1 (Test Žák $d1 3) c\n" . substr($h2, 0, 8) . " (Test Žák $d2 4) d\n");
ga_cmd($w, 'rev-parse:short', 'git rev-parse --short HEAD~1', "$s1\n");
ga_cmd($w, 'rev-parse:abbrev', 'git rev-parse --abbrev-ref HEAD', "main\n");
ga_cmd($w, 'rev-parse:toplevel', 'git rev-parse --show-toplevel', "/home/student/web\n");
$amb = "fatal: ambiguous argument 'nic': unknown revision or path not in the working tree.\nUse '--' to separate paths from revisions, like this:\n'git <command> [<revision>...] -- [<file>...]'\n";
ga_cmd($w, 'err:unknown-revision', 'git show nic', '', 128, $amb, 'git log --oneline');
ga_cmd($w, 'err:rev-parse-unknown', 'git rev-parse nic', "nic\n", 128, $amb);
ga_cmd($w, 'err:bad-option', 'git log --neznama', '', 129, "error: unknown option `neznama'\nusage: git log [<options>] [<revision-range>] [[--] <path>...]\n", 'git log -h');
ga_run($w, 'echo e >> f.txt');
ga_cmd($w, 'err:empty-message', 'git commit -a', '', 1, "Aborting commit due to empty commit message.\n", 'git commit -m');
ga_cmd($w, 'restore:after-abort', 'git restore f.txt && git status -s', "?? img/\n");
ga_cmd($w, 'commit:nothing', 'git commit -m Nic', "On branch main\nUntracked files:\n  (use \"git add <file>...\" to include in what will be committed)\n\timg/\n\nnothing added to commit but untracked files present (use \"git add\" to track)\n", 1);

// Větve, konflikt při přepnutí, stash, značky, reset, restore, klon, apt
ga_cmd($w, 'branch:create', 'git branch dalsi', '');
ga_cmd($w, 'branch:list', 'git branch', "  dalsi\n* main\n");
ga_cmd($w, 'branch:verbose', 'git branch -v', "  dalsi $s2 Druhy\n* main  $s2 Druhy\n");
ga_cmd($w, 'err:branch-exists', 'git branch dalsi', '', 128, "fatal: a branch named 'dalsi' already exists\n");
ga_cmd($w, 'switch:branch', 'git switch dalsi', '', 0, "Switched to branch 'dalsi'\n");
ga_run($w, 'echo e >> f.txt');
ga_run($w, 'git commit -qam Treti');
$s3 = ga_rev($w, 'HEAD', 7);
ga_cmd($w, 'switch:back', 'git switch main', '', 0, "Switched to branch 'main'\n");
ga_cmd($w, 'log:graph-all', 'git log --oneline --graph --all', "* $s3 (dalsi) Treti\n* $s2 (HEAD -> main) Druhy\n* $s1 Prvni\n");
ga_cmd($w, 'err:switch-unknown', 'git switch neni', '', 128, "fatal: invalid reference: neni\n", 'git branch -a');
ga_run($w, 'echo zmena > f.txt');
ga_cmd($w, 'err:switch-conflict', 'git switch dalsi', '', 1, "error: Your local changes to the following files would be overwritten by checkout:\n\tf.txt\nPlease commit your changes or stash them before you switch branches.\nAborting\n", 'git stash');
ga_cmd($w, 'err:checkout-conflict', 'git checkout dalsi', '', 1, '~^error: Your local changes to the following files would be overwritten by checkout:\n\tf\.txt\n~');
ga_cmd($w, 'stash:push', 'git stash', "Saved working directory and index state WIP on main: $s2 Druhy\n");
ga_cmd($w, 'stash:list', 'git stash list', "stash@{0}: WIP on main: $s2 Druhy\n");
ga_cmd($w, 'stash:show', 'git stash show', " f.txt | 5 +----\n 1 file changed, 1 insertion(+), 4 deletions(-)\n");
ga_cmd($w, 'switch:after-stash', 'git switch dalsi', '', 0, "Switched to branch 'dalsi'\n");
ga_run($w, 'git switch main');
ga_cmd($w, 'stash:pop', 'git stash pop', "~^On branch main\nChanges not staged for commit:\n.*\tmodified:   f\\.txt\n.*Dropped refs/stash@\\{0\\} \\([0-9a-f]{40}\\)\n$~s");
ga_cmd($w, 'err:stash-empty', 'git stash pop', '', 1, "No stash entries found.\n");
ga_cmd($w, 'restore:worktree', 'git restore f.txt && cat f.txt', "a\nB\nc\nd\n");
ga_cmd($w, 'tag:annotated', 'git tag -a v1.0 -m "Vydání 1.0"', '');
ga_cmd($w, 'tag:list', 'git tag -l', "v1.0\n");
ga_cmd($w, 'tag:n', 'git tag -n', "v1.0            Vydání 1.0\n");
ga_cmd($w, 'tag:show', 'git show v1.0 -s --no-decorate', "~^tag v1\\.0\nTagger: Test Žák <test@skola.test>\nDate:   .+\n\nVydání 1\\.0\n\ncommit $h2\n~");
ga_cmd($w, 'err:tag-exists', 'git tag v1.0', '', 128, "fatal: tag 'v1.0' already exists\n");
ga_cmd($w, 'checkout:file-from-rev', 'git checkout HEAD~1 -- f.txt', '', 0, "~^Updated 1 path from [0-9a-f]{7}\n$~");
ga_cmd($w, 'status:after-checkout', 'git status -s', "M  f.txt\n?? img/\n");
ga_cmd($w, 'restore:staged', 'git restore --staged f.txt && git status -s', " M f.txt\n?? img/\n");
ga_cmd($w, 'reset:hard', 'git reset --hard HEAD~1', "HEAD is now at $s1 Prvni\n");
ga_cmd($w, 'reset:hard-content', 'cat f.txt', "a\nb\nc\n");
ga_cmd($w, 'reset:soft', "git reset --soft $h2 && git status -s", "M  f.txt\n?? img/\n");
ga_cmd($w, 'reset:mixed', 'git reset', "Unstaged changes after reset:\nM\tf.txt\n");
ga_cmd($w, 'err:branch-unmerged', 'git branch -d dalsi', '', 1, "error: The branch 'dalsi' is not fully merged.\nIf you are sure you want to delete it, run 'git branch -D dalsi'.\n");
ga_cmd($w, 'branch:force-delete', 'git branch -D dalsi', "Deleted branch dalsi (was $s3).\n");
ga_run($w, 'git restore f.txt');
ga_run($w, 'cd ~');
ga_cmd($w, 'clone:local', 'git clone web kopie', '', 0, "Cloning into 'kopie'...\ndone.\n");
ga_run($w, 'cd kopie');
ga_cmd($w, 'clone:branch-a', 'git branch -a', "* main\n  remotes/origin/HEAD -> origin/main\n  remotes/origin/main\n");
ga_cmd($w, 'clone:status', 'git status', "On branch main\nYour branch is up to date with 'origin/main'.\n\nnothing to commit, working tree clean\n");
ga_cmd($w, 'clone:remote', 'git remote -v', "origin\t/home/student/web (fetch)\norigin\t/home/student/web (push)\n");
ga_cmd($w, 'clone:log', 'git log --oneline', "$s2 (HEAD -> main, tag: v1.0, origin/main, origin/HEAD) Druhy\n$s1 Prvni\n");
ga_cmd($w, 'err:clone-network', 'git clone https://example.com/x.git', '', 128, "Cloning into 'x'...\nfatal: unable to access 'https://example.com/x.git': Could not resolve host: example.com\n", 'internet');
ga_cmd($w, 'err:clone-missing', 'git clone /srv/git/neni.git', '', 128, "fatal: repository '/srv/git/neni.git' does not exist\n");
ga_cmd($w, 'err:clone-exists', 'git -C ~ clone web kopie', '', 128, "fatal: destination path 'kopie' already exists and is not an empty directory.\n");
ga_cmd($w, 'apt:remove-git', 'sudo apt remove -y git > /dev/null; git --version', '', 127, null, 'apt install git');

// ---------------------------------------------------------------------------
// 2) Determinismus: stejné semínko = stejný repozitář; id commitů nezávisí na čase požadavku
// ---------------------------------------------------------------------------

$wa = ga_world('git-7', 'ga-det');
$wb = ga_world('git-7', 'ga-det', GA_NOW + 86400 * 40);
$la = ga_run($wa, 'git -C ~/web log --oneline --all --graph')['out'];
ga_check('det:same-seed-same-history', $la !== '' && $la === ga_run($wb, 'git -C ~/web log --oneline --all --graph')['out'] && $wa->facts === $wb->facts, $la);
ga_check('det:bare-equals-clone', ga_run($wa, 'git -C /srv/git/web.git rev-parse main')['out'] === ga_run($wa, 'git -C ~/web rev-parse origin/main')['out']);
$facts = array_map(static fn(int $s): array => ga_world('git-3', 'ga-seed|' . $s)->facts, range(1, 5));
ga_check('det:seeds-differ', count(array_unique(array_column($facts, 'broken'))) === 5 && count(array_unique(array_column($facts, 'h1_author'))) > 1 && count(array_unique(array_column($facts, 'typo'))) > 1);
ga_cmd($wa, 'log:same-second-parent-after-child', 'cd ~/web && git commit -q --allow-empty -m A && git commit -q --allow-empty -m B && git log --format=%s -3', "~^B\nA\n(Oprava překlepů v README|Aktualizace kontaktů)\n$~");
ga_cmd($wa, 'reset:orig-head', 'git reset -q --hard HEAD~2 && git reset --hard ORIG_HEAD', "~^HEAD is now at [0-9a-f]{7} B\n$~");
ga_cmd($wa, 'bare:log','git -C /srv/git/web.git log --oneline -1 v0.9', '~^[0-9a-f]{7} \(tag: v0\.9\) Poznámky k vydání 0\.9\n$~');
ga_cmd($wa, 'err:bare-worktree', 'git -C /srv/git/web.git status', '', 128, "fatal: this operation must be run in a work tree\n");
ga_cmd($wa, 'err:bare-readonly', 'git -C /srv/git/web.git tag test', '', 128, "fatal: Unable to create '/srv/git/web.git/index.lock': Permission denied\n");
ga_cmd($wa, 'bare:read-allowed', 'git -C /srv/git/web.git tag -n && git -C /srv/git/web.git branch', "~^v0\.9 .+\nv1\.0 .+\n(  [a-z0-9-]+\n)*\* main\n(  [a-z0-9-]+\n)+$~");

// ---------------------------------------------------------------------------
// 3) Perzistence mezi požadavky (lab57_session, úložiště) a celý balíček přes relaci ve správném pořadí
// ---------------------------------------------------------------------------

function ga_sess(string $level, string $line, int $now, string $student = 'Audit Žák', array $mates = []): array
{
    $r = lab57_session(['class' => 'class_3a', 'student' => $student, 'label' => $student, 'context' => 'practice', 'level' => $level, 'now' => $now, 'cli' => true, 'classmates' => $mates], 'run', ['line' => $line]);
    return ['out' => implode('', array_column((array)($r['out'] ?? []), 1)), 'exit' => $r['exit'] ?? null, 'solved' => !empty($r['solved']), 'ok' => !empty($r['ok'])];
}

$t = GA_NOW;
foreach (['git init repo', 'cd repo', 'echo ahoj > a.txt', 'git add a.txt', 'git config --global user.name "Audit Žák"', 'git config --global user.email audit@skola.test', 'git commit -qm "Uloženo v jednom požadavku"'] as $line) ga_sess('sandbox', $line, $t += 30);
$r = ga_sess('sandbox', 'git log --oneline', $t += 3600);
ga_check('persist:sandbox-log', $r['exit'] === 0 && preg_match('~^[0-9a-f]{7} \(HEAD -> main\) Uloženo v jednom požadavku\n$~', $r['out']) === 1, $r['out']);
$r = ga_sess('sandbox', 'git status -s && cat .git/HEAD', $t += 60);
ga_check('persist:sandbox-state', $r['out'] === "ref: refs/heads/main\n", $r['out']);
$solvedAll = true;
foreach ($levels as $level) {
    $seed = lab57_seed('class_3a', 'Audit Žák', 'practice', $level);
    $lw = lab57_build_world($level, $seed, ['CODE' => lab57_code('class_3a', 'Audit Žák', 'practice', $level['id'])], $t);
    $solved = false;
    foreach (($level['solution'])($lw) as $line) { $r = ga_sess($level['id'], $line, $t += 1800); $solved = $solved || $r['solved']; }
    ga_check('persist:session-solves:' . $level['id'], $solved, json_encode($r, JSON_UNESCAPED_UNICODE));
    $solvedAll = $solvedAll && $solved;
}
$r = ga_sess('git-1', 'git -C ~/web log --oneline -1 && git -C ~/web status -sb', $t += 86400);
ga_check('persist:clone-survives-days', $r['exit'] === 0 && preg_match('~^[0-9a-f]{7} \(HEAD -> main, origin/main, origin/HEAD\) .+\n## main\.\.\.origin/main\n$~', $r['out']) === 1, $r['out']);
foreach (['echo novinka > ~/web/novinka.txt', 'git -C ~/web add novinka.txt', 'git -C ~/web commit -qm "Novinka"'] as $line) ga_sess('git-1', $line, $t += 60);
$r = ga_sess('git-1', 'git -C ~/web status -sb && git -C ~/web log --oneline -2 --no-decorate | cut -c9-', $t += 7200);
ga_check('persist:own-commit-on-level-repo', preg_match('~^## main\.\.\.origin/main \[ahead 1\]\nNovinka\n.+\n$~', $r['out']) === 1, $r['out']);
ga_check('persist:locked-without-previous', !empty(lab57_session(['class' => 'class_3a', 'student' => 'Jiný Žák', 'label' => 'J', 'context' => 'practice', 'level' => 'git-5', 'now' => $t, 'cli' => true], 'state')['error']));

// ---------------------------------------------------------------------------
// 4) Úrovně: správná odpověď projde; stará, cizí a návnada ne
// ---------------------------------------------------------------------------

$w2 = ga_world('git-2', 'ga-ans|2');
$decoyAuthor = trim(ga_run($w2, "git -C ~/web log --format=%an --grep='Nový nadpis'")['out']);
ga_check('answer:git-2:decoy-differs', $decoyAuthor !== '' && $decoyAuthor !== $w2->facts['h1_author'], $decoyAuthor);
ga_cmd($w2, 'answer:git-2:blame-shows-author', 'git -C ~/web blame index.html | grep h1', '~\(' . preg_quote($w2->facts['h1_author'], '~') . ' +2026-03-~');
ga_cmd($w2, 'answer:git-2:old-author-rejected', 'answer ' . $decoyAuthor, "To není ono. Formát odpovědi: celé jméno autora, např. Jana Nováková.\n", 1);
ga_cmd($w2, 'answer:git-2:ascii-accepted', 'answer ' . lab58_gitlv_ascii($w2->facts['h1_author']), '', 0);
$w3 = ga_world('git-3', 'ga-ans|3');
$added = trim(ga_run($w3, "git -C ~/web log --format=%H -S 'href=\"kontakt.html\"' --reverse")['out']);
ga_cmd($w3, 'answer:git-3:pickaxe', "git -C ~/web log --format=%H -S 'href=\"kontakt.html\"'", $w3->facts['broken'] . "\n" . substr($added, 0, 40) . "\n");
ga_cmd($w3, 'answer:git-3:link-adding-commit-rejected', 'answer ' . substr($added, 0, 7), null, 1);
ga_cmd($w3, 'answer:git-3:foreign-seed-rejected', 'answer ' . substr($facts[0]['broken'], 0, 7), null, 1);
ga_cmd($w3, 'answer:git-3:6-chars-rejected', 'answer ' . substr($w3->facts['broken'], 0, 6), null, 1);
ga_cmd($w3, 'answer:git-3:8-chars-accepted', 'answer ' . strtoupper(substr($w3->facts['broken'], 0, 8)), '', 0);
$w4 = ga_world('git-4', 'ga-ans|4');
$l4 = (array)lab57_level('git-4');
ga_cmd($w4, 'level:git-4:plain-restore-fails', 'cd ~/web && git restore rozvrh.html', '', 1, "error: pathspec 'rozvrh.html' did not match any file(s) known to git\n");
ga_cmd($w4, 'level:git-4:log-needs-dashdash', 'git log rozvrh.html', '', 128, "~^fatal: ambiguous argument 'rozvrh\.html'~", 'git log -- rozvrh.html');
ga_run($w4, 'git checkout ' . $w4->facts['del_short'] . '~6 -- rozvrh.html');
ga_check('level:git-4:old-version-fails', array_column(lab57_eval_checks($l4, $w4), 'ok') === [false, false]);
ga_run($w4, 'git checkout ' . $w4->facts['del_short'] . '~1 -- rozvrh.html');
ga_check('level:git-4:restored-not-committed', array_column(lab57_eval_checks($l4, $w4), 'ok') === [true, false]);
ga_run($w4, 'git commit -qm "Obnoven rozvrh"');
ga_check('level:git-4:committed', array_column(lab57_eval_checks($l4, $w4), 'ok') === [true, true]);
$w5 = ga_world('git-5', 'ga-ans|5', GA_NOW, 'EDU-PRAV-DA55');
lab57_active()['foreign'] = static fn(string $c): bool => $c === 'EDU-SPOL-ZAK5';
preg_match('~Poznámky k vydání: (\S+)~u', ga_run($w5, 'git -C ~/web show v0.9 -s')['out'], $m09);
preg_match('~(EDU-[A-Z0-9]{4}-[A-Z0-9]{4})~', ga_run($w5, 'git -C ~/web show v0.9:' . ($m09[1] ?? 'x'))['out'], $d09);
ga_cmd($w5, 'answer:git-5:v0.9-decoy-rejected', 'submit ' . ($d09[1] ?? 'EDU-NENI-0000'), "Kód nesouhlasí. Zkontroluj, že jsi ho zkopíroval(a) celý (EDU-XXXX-XXXX).\n", 1);
ga_cmd($w5, 'answer:git-5:foreign-rejected', 'submit EDU-SPOL-ZAK5', "Tenhle kód patří někomu jinému – každý má v Labu svůj vlastní. Najdi ten svůj. 🙂\n", 1);
ga_cmd($w5, 'answer:git-5:code-in-v1.0-notes', 'git -C ~/web show v1.0:' . $w5->facts['notes10'], '~Kontrolní kód vydání: EDU-PRAV-DA55\n~');
ga_cmd($w5, 'answer:git-5:not-in-worktree', 'grep -r EDU- ~/web', '', 1, '');
ga_cmd($w5, 'answer:git-5:objects-not-greppable', 'grep -rl DA55 ~/web/.git /srv/git', '', 1, '');
$w6 = ga_world('git-6', 'ga-ans|6', GA_NOW, 'EDU-STAS-H666');
ga_cmd($w6, 'level:git-6:stash-list', 'git -C ~/web stash list', "~^stash@\\{0\\}: (On main: rozdělaná stránka o kroužcích|WIP on main: [0-9a-f]{7} .+)\nstash@\\{1\\}: (On main: rozdělaná stránka o kroužcích|WIP on main: [0-9a-f]{7} .+)\n$~");
$other = $w6->facts['stash_ref'] === 'stash@{0}' ? 'stash@{1}' : 'stash@{0}';
preg_match('~(EDU-[A-Z0-9]{4}-[A-Z0-9]{4})~', ga_run($w6, 'git -C ~/web stash show -p ' . $other)['out'], $d6);
ga_cmd($w6, 'answer:git-6:other-stash-decoy-rejected', 'submit ' . ($d6[1] ?? 'EDU-NENI-0000'), null, 1);
ga_cmd($w6, 'answer:git-6:stash-show-p', 'git -C ~/web stash show -p ' . $w6->facts['stash_ref'], '~\+  <!-- kontrolní kód stránky: EDU-STAS-H666 -->\n~');
$w7 = ga_world('git-7', 'ga-ans|7', GA_NOW, 'EDU-DESI-GN77');
ga_cmd($w7, 'level:git-7:switch-conflict', 'cd ~/web && git switch ' . $w7->facts['design_branch'], '', 1, "error: Your local changes to the following files would be overwritten by checkout:\n\tcss/styl.css\nPlease commit your changes or stash them before you switch branches.\nAborting\n", 'git stash');
ga_run($w7, 'git stash');
ga_cmd($w7, 'level:git-7:switch-tracking', 'git switch ' . $w7->facts['design_branch'], "branch '" . $w7->facts['design_branch'] . "' set up to track 'origin/" . $w7->facts['design_branch'] . "'.\nYour branch is up to date with 'origin/" . $w7->facts['design_branch'] . "'.\n", 0, "Switched to a new branch '" . $w7->facts['design_branch'] . "'\n");
ga_cmd($w7, 'level:git-7:note', 'cat ' . $w7->facts['design_note'], '~Kód pro tým: EDU-DESI-GN77\n~');
ga_cmd($w7, 'level:git-7:stash-back-on-main', 'git switch -q main && git stash pop -q; git status -s', " M css/styl.css\n");

// ---------------------------------------------------------------------------
// 5) Řešitelnost: referenční řešení všech úrovní na 5 semínkách (bez úložiště); cizí kód neprojde
// ---------------------------------------------------------------------------

foreach ($levels as $level) {
    $bad = [];
    foreach (range(1, 5) as $s) {
        $res = lab58_try_solution($level, 'ga-solve|' . $level['id'] . '|' . $s, GA_NOW + $s * 86400, 'EDU-SOLV-' . $s . 'A' . $s . 'B');
        if (!$res['solved']) $bad[] = '#' . $s . ' ' . json_encode(end($res['transcript']), JSON_UNESCAPED_UNICODE);
        if ($level['type'] === 'code') {
            $foreign = lab58_try_solution(['solution' => static fn(Lab57World $w): array => array_map(static fn(string $l): string => str_replace('EDU-SOLV-' . $s . 'A' . $s . 'B', 'EDU-CIZI-000' . $s, $l), ($level['solution'])($w))] + $level, 'ga-solve|' . $level['id'] . '|' . $s, GA_NOW, 'EDU-SOLV-' . $s . 'A' . $s . 'B');
            if ($foreign['solved']) $bad[] = '#' . $s . ' cizí kód prošel';
        }
    }
    ga_check('solve:' . $level['id'] . ':5-seeds', $bad === [], implode(' | ', $bad));
}

// ---------------------------------------------------------------------------
// 6) Příručka: man git, --help, příklady běží v pískovišti i v repozitáři úrovně
// ---------------------------------------------------------------------------

$man = (array)(lab58_manual()['commands']['git'] ?? []);
ga_check('manual:entry', ($man['cat'] ?? '') === 'verze' && count((array)($man['tldr'] ?? [])) >= 2 && count((array)$man['tldr']) <= 4 && ($man['see_also'] ?? []) !== [] && !empty($man['in_lab']) && isset(lab58_manual()['categories']['verze']));
$wm = ga_world('sandbox', 'ga-man');
ga_cmd($wm, 'manual:man-git', 'man git', '~RYCHLÉ PŘÍKLADY.*git blame index\.html~s');
ga_cmd($wm, 'manual:help-flag', 'git --help', "~^Usage: git <podpříkaz> \[volby\] \[argumenty\]\n~");
ga_cmd($wm, 'manual:git-help', 'git help commit', "usage: git commit [-a | --amend] [-m <msg>] [--] [<pathspec>...]\n\nCelá nápověda: man git\n");
$examples = array_merge((array)$man['examples'], (array)$man['tldr']);
foreach ([['sandbox', ''], ['git-5', 'cd ~/web && ']] as [$lvl, $prefix]) {
    $bad = [];
    foreach ($examples as [$line]) {
        $ew = ga_world($lvl, 'ga-man|' . $lvl . '|' . md5((string)$line));
        $r = ga_run($ew, $prefix . $line);
        $okExit = $lvl === 'sandbox' ? $r['exit'] !== 127 : ($r['exit'] === 0 || str_contains((string)$line, 'commit'));
        if (!$okExit || preg_match('/invalid option|unrecognized option|unknown/i', $r['err']) === 1) $bad[] = $line . ' → ' . $r['exit'] . ' ' . trim($r['err']);
    }
    ga_check('manual:examples-run:' . $lvl, $bad === [], implode(' | ', $bad));
}

ga_check('php:no-warnings', $GLOBALS['ga_warn'] === [], implode(' | ', array_slice($GLOBALS['ga_warn'], 0, 5)));
$ga = $GLOBALS['ga'];
echo ($ga['failed'] === 0 ? 'V58_SIM_GIT_AUDIT_OK' : 'V58_SIM_GIT_AUDIT_FAIL') . ' checks=' . $ga['checks'] . ' failed=' . $ga['failed'] . "\n";
exit($ga['failed'] === 0 ? 0 : 1);
