<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Linux Lab – audit rozšiřitelného jádra (registry, kontexty, události, log, svět).
 * Pouze CLI, izolované dočasné úložiště (nikdy nesahá na storage/). Nic nespouští, žádná síť.
 * Spuštění: php tools/v58_lab_ext_audit.php   → poslední řádek V58_LAB_EXT_AUDIT_OK checks=N failed=0
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Prague');

$ROOT = dirname(__DIR__);
$TMP = sys_get_temp_dir() . '/v58_lab_ext_audit_' . bin2hex(random_bytes(6));
mkdir($TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $TMP;
$GLOBALS['lab57_secret_override'] = 'v58-audit-secret';
ini_set('log_errors', '1');
ini_set('error_log', $TMP . '/php_errors.log'); // záměrné chyby registrace a posluchačů nešpiní výstup

register_shutdown_function(static function () use ($TMP): void {
    if (!is_dir($TMP)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($TMP, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($TMP);
});

$GLOBALS['v58a_warnings'] = [];
set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) return true;
    $GLOBALS['v58a_warnings'][] = $str . ' (' . basename($file) . ':' . $line . ')';
    return true;
});

require_once $ROOT . '/linux_v57_lab.php';

$GLOBALS['v58a'] = ['checks' => 0, 'failed' => 0];
function v58a_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['v58a']['checks']++;
    if (!$ok) $GLOBALS['v58a']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}

function v58a_out(array $run, ?int $fd = null): string
{
    $out = '';
    foreach ($run['chunks'] ?? $run['out'] ?? [] as [$f, $text]) if ($fd === null || $f === $fd) $out .= $text;
    return $out;
}

const V58A_NOW = 1790000000;

function v58a_ctx(string $class, string $student, string $level, string $context = 'practice', int $now = V58A_NOW): array
{
    return ['class' => $class, 'student' => $student, 'label' => 'Test Žák', 'context' => $context, 'level' => $level, 'now' => $now, 'cli' => true, 'classmates' => []];
}

function v58a_run(array $ctx, string $line): array
{
    return lab57_session($ctx, 'run', ['line' => $line]);
}

// ---------------------------------------------------------------------------
// 0) Načtení a čistý registr (hlídá i chyby rozšíření ostatních agentů)
// ---------------------------------------------------------------------------

foreach (['lab58_register_command', 'lab58_register_manual', 'lab58_register_pack', 'lab58_register_level_source', 'lab58_register_generator', 'lab58_register_check', 'lab58_register_context', 'lab58_on', 'lab58_emit', 'lab58_add_filter', 'lab58_filter', 'lab58_register_world_extender', 'lab58_now', 'lab58_packs_for_class', 'lab58_classify_error', 'lab58_log_read', 'lab58_class_log_summary', 'lab58_level_stats', 'lab58_class_activity'] as $fn) {
    v58a_check('api:exists:' . $fn, function_exists($fn));
}
lab57_levels();
lab58_manual();
lab58_packs();
v58a_check('registry:no-errors-after-load', lab58_registry_errors() === [], implode(' | ', lab58_registry_errors()));
echo 'INFO rozšíření: ' . (lab58_loaded_extensions() === [] ? '(žádná)' : implode(', ', lab58_loaded_extensions())) . "\n";
v58a_check('context:core-registered', lab58_context_spec('practice') !== null && lab58_context_spec('race') !== null);

// ---------------------------------------------------------------------------
// 1) Registr příkazů + apt + příručka
// ---------------------------------------------------------------------------

function v58audit_cmd_hello(Lab57Proc $p, array $argv): int { $p->line('hello ' . implode(' ', array_slice($argv, 1))); return 0; }
function v58audit_cmd_tool(Lab57Proc $p, array $argv): int { $p->line('tool ok'); return 0; }
function v58audit_cmd_builtin(Lab57Proc $p, array $argv): int { $p->line('builtin ok'); return 0; }
function v58audit_cmd_warp(Lab57Proc $p, array $argv): int { lab58_clock_advance($p->w, (int)($argv[1] ?? 0)); return 0; }
function v58audit_cmd_adduser(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $name = (string)($argv[1] ?? '');
    if (preg_match('/^[a-z]{2,16}$/', $name) !== 1) return $p->fail('neplatné jméno');
    $w->groups[$name] = 1100 + count($w->groups);
    $w->users[$name] = ['uid' => 1100 + count($w->users), 'gid' => $w->groups[$name], 'home' => '/home/' . $name, 'shell' => '/bin/bash', 'groups' => [$name], 'gecos' => $name];
    lab57_world_write_accounts($w);
    return 0;
}
function v58audit_cmd_ext(Lab57Proc $p, array $argv): int { $p->w->ext['pocitadlo'] = (int)($p->w->ext['pocitadlo'] ?? 0) + 1; $p->line('ext=' . $p->w->ext['pocitadlo']); return 0; }
function v58audit_cmd_locktest(Lab57Proc $p, array $argv): int
{
    $fp = fopen((string)$GLOBALS['v58a_lock_path'], 'c+');
    $free = $fp !== false && flock($fp, LOCK_EX | LOCK_NB);
    if ($free) flock($fp, LOCK_UN);
    if ($fp !== false) fclose($fp);
    $p->line($free ? 'free' : 'locked');
    return 0;
}
function v58audit_cmd_whoami_role(Lab57Proc $p, array $argv): int { $p->line('role=' . (lab58_role($p->w) ?? '-')); return 0; }

$errBefore = count(lab58_registry_errors());
v58a_check('cmd:register', lab58_register_command('v58hello', 'v58audit_cmd_hello', ['package' => 'v58audit-tools']));
v58a_check('cmd:register-bin', lab58_register_command('v58tool', 'v58audit_cmd_tool', ['bin' => '/usr/local/bin/v58tool']));
v58a_check('cmd:register-builtin', lab58_register_command('v58bi', 'v58audit_cmd_builtin', ['builtin' => true]));
foreach (['v58warp' => 'v58audit_cmd_warp', 'v58adduser' => 'v58audit_cmd_adduser', 'v58ext' => 'v58audit_cmd_ext', 'v58locktest' => 'v58audit_cmd_locktest', 'v58role' => 'v58audit_cmd_whoami_role'] as $n => $h) lab58_register_command($n, $h);
v58a_check('cmd:reject-duplicate', !lab58_register_command('v58hello', 'v58audit_cmd_hello'));
v58a_check('cmd:reject-core-override', !lab58_register_command('ls', 'v58audit_cmd_hello'));
v58a_check('cmd:reject-missing-fn', !lab58_register_command('v58nofn', 'v58audit_neexistuje'));
v58a_check('cmd:reject-bad-name', !lab58_register_command('Bad Name', 'v58audit_cmd_hello'));
v58a_check('cmd:errors-recorded', count(lab58_registry_errors()) === $errBefore + 4, implode(' | ', array_slice(lab58_registry_errors(), $errBefore)));
lab58_register_apt_package('v58audit-tools', ['version' => '2.0-1', 'description' => 'auditni nastroje v58', 'bins' => ['v58hello']]);
lab58_register_manual(['categories' => ['v58audit' => ['label' => 'Audit v58']], 'commands' => [
    'v58tool' => ['cat' => 'v58audit', 'summary' => 'Testovací nástroj auditu.', 'synopsis' => 'v58tool', 'about' => 'Jen pro audit.', 'examples' => [['v58tool', 'vypíše tool ok']], 'tldr' => [['v58tool', 'spustí nástroj']], 'see_also' => ['v58hello'], 'in_lab' => true],
    'ls' => ['extend' => true, 'tldr' => [['ls -lah', 'vše i se skrytými, čitelné velikosti']], 'see_also' => ['tree']],
]]);
v58a_check('cmd:in-registry', isset(lab57_command_registry()['v58hello']) && isset(lab57_command_registry()['ls']));
v58a_check('cmd:package-meta', lab57_command_package('v58hello') === 'v58audit-tools' && lab57_command_package('cowsay') === 'cowsay');
v58a_check('cmd:builtin-list', in_array('v58bi', lab57_builtins(), true));

$w = lab57_build_world(lab57_sandbox_level(), 'v58a-cmd', ['CODE' => 'EDU-TEST-0000'], V58A_NOW);
$r = lab57_run_line($w, 'v58hello svete');
v58a_check('cmd:gated-not-installed', $r['exit'] === 127 && str_contains(implode(' ', $w->tips), 'apt install v58audit-tools'), 'exit=' . $r['exit'] . ' tips=' . implode(' | ', $w->tips));
$r = lab57_run_line($w, 'apt search v58audit');
v58a_check('cmd:apt-search', str_contains(v58a_out($r), 'v58audit-tools/stable 2.0-1'), v58a_out($r));
$r = lab57_run_line($w, 'sudo apt install -y v58audit-tools');
v58a_check('cmd:apt-install', $r['exit'] === 0, v58a_out($r));
$r = lab57_run_line($w, 'v58hello svete');
v58a_check('cmd:runs-after-install', v58a_out($r, 1) === "hello svete\n", var_export(v58a_out($r), true));
$r = lab57_run_line($w, 'which v58hello v58tool');
v58a_check('cmd:which', v58a_out($r, 1) === "/usr/bin/v58hello\n/usr/local/bin/v58tool\n", var_export(v58a_out($r), true));
$r = lab57_run_line($w, 'type v58bi');
v58a_check('cmd:type-builtin', v58a_out($r, 1) === "v58bi is a shell builtin\n", var_export(v58a_out($r), true));
v58a_check('cmd:builtin-no-bin', !$w->fs->exists('/usr/bin/v58bi'));
$r = lab57_run_line($w, 'v58tool --help');
v58a_check('cmd:help-from-manual', str_contains(v58a_out($r), 'Usage: v58tool'), v58a_out($r));
$r = lab57_run_line($w, 'man v58tool');
v58a_check('cmd:man-tldr-see-also', str_contains(v58a_out($r), 'RYCHLÉ PŘÍKLADY') && str_contains(v58a_out($r), 'v58hello'), v58a_out($r));
$r = lab57_run_line($w, 'man ls');
v58a_check('manual:extend-existing', str_contains(v58a_out($r), 'ls -lah') && str_contains(v58a_out($r), 'tree'));
$r = lab57_run_line($w, 'v58tol');
v58a_check('cmd:not-found-suggestion', $r['exit'] === 127 && str_contains(implode(' ', $w->tips), 'v58tool'), implode(' | ', $w->tips));
$r = lab57_run_line($w, 'help');
v58a_check('cmd:help-lists', str_contains(v58a_out($r), 'Audit v58') && str_contains(v58a_out($r), 'v58tool'));
$r = lab57_run_line($w, 'sudo apt remove v58audit-tools; v58hello');
v58a_check('cmd:apt-remove', $r['exit'] === 127 && !$w->fs->exists('/usr/bin/v58hello'), 'exit=' . $r['exit']);
v58a_check('manual:v57-compatible', v57_manual() === lab58_manual() && count(v57_manual_base()['commands']) < count(lab58_manual()['commands']));

// Všechny položky příručky registrované ve v58 (i od jiných agentů): in_lab ⇒ příkaz existuje, příklady běží čistě.
$base = v57_manual_base()['commands'];
$registry = lab57_command_registry();
$bad = [];
foreach (lab58_manual()['commands'] as $name => $cmd) {
    if (isset($base[$name]) || empty($cmd['in_lab'])) continue;
    if (!isset($registry[$name])) { $bad[] = "$name: in_lab bez příkazu"; continue; }
    foreach (array_merge((array)$cmd['examples'], (array)($cmd['tldr'] ?? [])) as $ex) {
        $line = (string)($ex[0] ?? '');
        $first = (string)strtok($line, " \t");
        if ($line === '' || in_array($first, ['submit', 'answer', 'check', 'hint', 'mise', 'reset'], true)) continue;
        $mw = lab57_build_world(lab57_sandbox_level(), 'v58a-man|' . $name . '|' . md5($line), ['CODE' => 'EDU-TEST-0000'], V58A_NOW);
        $package = lab57_command_package($first);
        if ($package !== null) $mw->packages[$package] = '1';
        if ($package !== null) $mw->fs->set(lab57_command_bin($first), ['t' => 'f', 'm' => 0755, 'u' => 'root', 'g' => 'root', 'mt' => V58A_NOW, 'c' => 'x', 'x' => 'bin:' . $first]);
        $run = lab57_run_line($mw, $line);
        $err = v58a_out($run, 2);
        if ($run['exit'] === 127 || preg_match('/invalid option|unrecognized option|unknown/i', $err) === 1) $bad[] = "$name: `$line` exit={$run['exit']} " . trim($err);
    }
}
v58a_check('manual:v58-entries-valid', $bad === [], implode(' | ', $bad));

// ---------------------------------------------------------------------------
// 2) Balíčky, deklarativní úrovně, zdroje
// ---------------------------------------------------------------------------

$packOk = lab58_register_pack(['id' => 'v58audit-graf', 'title' => 'Audit: grafici', 'description' => 'Jen 1.A/2.A', 'order' => 5, 'classes' => ['class_1a', 'class_2a'], 'unlock' => 'sequential', 'badge' => 'audit'], static fn(): array => [
    ['id' => 'v58a-code', 'type' => 'code', 'title' => 'Najdi kód', 'story' => 'Kód je někde v domovské složce.', 'task' => 'Najdi kód a odevzdej ho.', 'points' => 60, 'hints' => ['Zkus find.'],
        'generate' => [['decoys', ['dir' => '~/archiv', 'count' => 4]], ['code_file', ['dirs' => ['~/archiv', '~/projekty', '~/.skryte'], 'names' => ['kod.txt', 'poznamka-{TOKEN}.txt']]]],
        'solution' => ['cat {f:code_path}', 'submit {CODE}']],
    ['id' => 'v58a-check', 'type' => 'check', 'title' => 'Oprav stav', 'story' => 'Stav se ztratil.', 'task' => 'Zapiš hotovo do ~/stav.txt.',
        'generate' => [['file', ['path' => '~/stav.txt', 'content' => "rozbito\n"]]],
        'checks' => [['file_contains', ['path' => '~/stav.txt', 'equals' => 'hotovo', 'label' => 'stav.txt obsahuje hotovo']], ['service_running', ['service' => 'ssh', 'label' => 'SSH běží']]],
        'solution' => ['echo hotovo > ~/stav.txt']],
]);
v58a_check('pack:register', $packOk);
lab58_register_pack(['id' => 'v58audit-free', 'title' => 'Audit: volný', 'order' => 900, 'classes' => null, 'unlock' => 'free'], static fn(): array => [
    ['id' => 'v58a-answer', 'type' => 'answer', 'title' => 'Kde je tajemství', 'story' => 'Soubor se schoval.', 'task' => 'Odpověz cestou k souboru.', 'answer_format' => 'cesta',
        'generate' => [['file', ['path' => '/srv/tajne-{TOKEN}.txt', 'content' => "psst\n", 'fact' => 'secret']]], 'answer' => '{f:secret}', 'solution' => ['answer {f:secret}']],
    ['id' => 'v58a-free2', 'type' => 'code', 'title' => 'Druhá volná', 'story' => 'Druhá.', 'task' => 'Odevzdej kód.', 'generate' => [['file', ['path' => '~/kod.txt', 'content' => "{CODE}\n"]]], 'solution' => ['submit {CODE}']],
]);
v58a_check('pack:reject-duplicate', !lab58_register_pack(['id' => 'quest', 'title' => 'x'], static fn(): array => []));
v58a_check('pack:reject-bad-classes', !lab58_register_pack(['id' => 'v58audit-bad', 'title' => 'x', 'classes' => []], static fn(): array => []));
lab58_register_level_source(static fn(): array => ['packs' => [['id' => 'v58audit-store', 'title' => 'Audit: úrovně učitele', 'order' => 950, 'unlock' => 'free']],
    'levels' => [['id' => 'v58a-store', 'pack' => 'v58audit-store', 'type' => 'code', 'title' => 'Z úložiště', 'story' => 'Úroveň od učitele.', 'task' => 'Odevzdej kód.', 'generate' => [['file', ['path' => '~/kod.txt', 'content' => "{CODE}\n"]]], 'solution' => ['cat ~/kod.txt', 'submit {CODE}']]]]);

$levels = lab57_levels();
$v57count = count(array_filter($levels, static fn(array $l): bool => isset(lab57_packs()[$l['pack']])));
v58a_check('levels:v57-intact', $v57count === 46, 'v57 úrovní: ' . $v57count);
v58a_check('levels:registered', isset($levels['v58a-code'], $levels['v58a-check'], $levels['v58a-answer'], $levels['v58a-store']) && $levels['v58a-check']['no'] === 2);
$for1a = array_keys(lab58_packs_for_class('class_1a'));
$for3a = array_keys(lab58_packs_for_class('class_3a'));
v58a_check('packs:for-class-1a', in_array('v58audit-graf', $for1a, true) && in_array('start', $for1a, true) && in_array('v58audit-store', $for1a, true));
v58a_check('packs:for-class-3a-excludes', !in_array('v58audit-graf', $for3a, true) && in_array('v58audit-free', $for3a, true) && in_array('sit', $for3a, true));
v58a_check('packs:order', array_search('v58audit-graf', $for1a, true) < array_search('start', $for1a, true));
v58a_check('packs:shape', lab58_pack('v58audit-graf')['classes'] === ['class_1a', 'class_2a'] && lab58_pack('start')['classes'] === null && lab58_pack('v58audit-free')['unlock'] === 'free');
v58a_check('levels:validate-unknown-generator', lab58_validate_level(['id' => 'x-bad', 'pack' => 'x', 'type' => 'code', 'title' => 't', 'story' => 's', 'task' => 't', 'generate' => [['neni', []]]]) !== []);
v58a_check('levels:validate-ok', lab58_validate_level(['id' => 'x-ok', 'pack' => 'x', 'type' => 'code', 'title' => 't', 'story' => 's', 'task' => 't', 'generate' => [['file', ['path' => '~/a']]]]) === []);

$lvl = $levels['v58a-code'];
$wA = lab57_build_world($lvl, 'seed-a', ['CODE' => 'EDU-AAAA-AAAA'], V58A_NOW);
$wA2 = lab57_build_world($lvl, 'seed-a', ['CODE' => 'EDU-AAAA-AAAA'], V58A_NOW);
v58a_check('decl:deterministic', sha1(serialize($wA->fs->all())) === sha1(serialize($wA2->fs->all())) && $wA->facts === $wA2->facts);
$paths = [];
foreach (range(1, 8) as $i) $paths[] = lab57_build_world($lvl, 'seed-' . $i, ['CODE' => 'X'], V58A_NOW)->facts['code_path'] ?? '';
v58a_check('decl:random-path', count(array_unique($paths)) > 1, implode(', ', $paths));
v58a_check('decl:code-file', trim((string)($wA->fs->get($wA->facts['code_path'])['c'] ?? '')) === 'EDU-AAAA-AAAA');
v58a_check('decl:decoys', count(explode("\n", $wA->facts['decoys'] ?? '')) === 4 && !str_contains((string)$wA->fs->get(explode("\n", $wA->facts['decoys'])[0])['c'], 'EDU-AAAA-AAAA'));
$wc = lab57_build_world($levels['v58a-check'], 'seed-c', ['CODE' => 'X'], V58A_NOW);
v58a_check('decl:checks-fail-before', array_column(lab57_eval_checks($levels['v58a-check'], $wc), 'ok') === [false, true]);

// Řešitelnost všech úrovní v58 (i od jiných agentů) na 3 semínkách, bez úložiště.
$unsolved = [];
foreach ($levels as $id => $level) {
    if (isset(lab57_packs()[$level['pack']])) continue;
    foreach ([1, 2, 3] as $s) {
        $res = lab58_try_solution($level, 'v58a-solve|' . $id . '|' . $s, V58A_NOW, 'EDU-SOLV-' . $s . '2' . $s . '2');
        if (!$res['solved']) { $unsolved[] = $id . '#' . $s . ' ' . json_encode($res['cmds'], JSON_UNESCAPED_UNICODE); break; }
    }
}
v58a_check('levels:v58-solvable', $unsolved === [], implode(' | ', $unsolved));

// ---------------------------------------------------------------------------
// 3) Celý tok přes lab57_session: open → run → log → submit → complete → události → body
// ---------------------------------------------------------------------------

$GLOBALS['v58a_events'] = [];
lab58_on('*', static function (array $ev): void { $GLOBALS['v58a_events'][] = $ev; });
lab58_on('command', static function (array $ev): void { throw new RuntimeException('záměrná chyba posluchače'); });
lab58_add_filter('state_payload', static fn(array $p, array $a): array => $p + ['v58audit' => $a['level']['id']]);
lab58_add_filter('state_payload', static function (array $p): array { throw new RuntimeException('záměrná chyba filtru'); });
lab58_add_filter('level_points', static fn(int $pts, array $a): int => $a['level']['pack'] === 'v58audit-graf' ? $pts + 7 : $pts);

$A = 'class_1a:student:v58a';
$c = v58a_ctx('class_1a', $A, 'v58a-code');
$state = lab57_session($c, 'state');
v58a_check('flow:state-filter', ($state['v58audit'] ?? '') === 'v58a-code' && ($state['context']['prefix'] ?? '') === 'practice', json_encode($state['context'] ?? null));
v58a_check('flow:access-other-class', !empty(lab57_session(v58a_ctx('class_3a', 'class_3a:student:x', 'v58a-code'), 'state')['error']));
v58a_check('flow:sequential-lock', str_contains((string)(lab57_session(v58a_ctx('class_1a', $A, 'v58a-check'), 'state')['error'] ?? ''), 'odemkne'));
v58a_check('flow:free-unlock', !empty(lab57_session(v58a_ctx('class_3a', 'class_3a:student:y', 'v58a-free2'), 'state')['ok']));
$classes = [];
foreach (['ls' => 'ok', 'cat nic.txt' => 'no_such_file', 'nexistuje' => 'not_found', 'ls --bogus' => 'bad_option', "echo 'x" => 'syntax', 'submit EDU-AAAA-BBBB' => 'wrong_answer', 'cat /root/.bashrc' => 'permission', 'hint' => 'ok'] as $line => $expect) {
    $resp = v58a_run($c, $line);
    $cmdEvents = array_values(array_filter($GLOBALS['v58a_events'], static fn(array $e): bool => $e['event'] === 'command'));
    $classes[] = ($resp['ok'] ?? false) && end($cmdEvents)['error_class'] === $expect ? 'ok' : "$line→" . (end($cmdEvents)['error_class'] ?? '?');
}
v58a_check('flow:error-classes', array_unique($classes) === ['ok'], implode(', ', $classes));
v58a_check('flow:listener-exception-contained', !empty(v58a_run($c, 'pwd')['ok']));
$solveWorld = lab57_build_world($lvl, lab57_seed('class_1a', $A, 'practice', $lvl), ['CODE' => lab57_code('class_1a', $A, 'practice', 'v58a-code')], V58A_NOW);
v58a_run($c, 'cat ' . $solveWorld->facts['code_path']);
$solved = v58a_run(['now' => V58A_NOW + 30] + $c, 'submit ' . lab57_code('class_1a', $A, 'practice', 'v58a-code'));
v58a_check('flow:solved', !empty($solved['solved']));
v58a_check('flow:points-filter', ($solved['points'] ?? 0) === (int)round(60 * 0.9) + 7, 'points=' . ($solved['points'] ?? 'null'));
$names = array_column($GLOBALS['v58a_events'], 'event');
foreach (['open', 'command', 'hint', 'submit_fail', 'submit_ok', 'complete'] as $ev) v58a_check('events:' . $ev, in_array($ev, $names, true));
$complete = array_values(array_filter($GLOBALS['v58a_events'], static fn(array $e): bool => $e['event'] === 'complete'))[0] ?? [];
v58a_check('events:complete-shape', ($complete['points'] ?? 0) === 61 && ($complete['hints'] ?? -1) === 1 && ($complete['secs'] ?? 0) >= 1 && ($complete['student'] ?? '') === $A && ($complete['level'] ?? '') === 'v58a-code' && ($complete['ctx'] ?? '') === 'practice');
$cmdEv = array_values(array_filter($GLOBALS['v58a_events'], static fn(array $e): bool => $e['event'] === 'command'))[0] ?? [];
v58a_check('events:command-shape', count(array_diff(['ctx', 'class', 'student', 'level', 'line', 'exit', 'error_class', 'ts', 'out', 'seq'], array_keys($cmdEv))) === 0, implode(',', array_keys($cmdEv)));
v58a_check('flow:next-unlocked', !empty(lab57_session(v58a_ctx('class_1a', $A, 'v58a-check'), 'state')['ok']));
lab57_session(v58a_ctx('class_1a', $A, 'v58a-check'), 'reset');
v58a_check('events:reset', in_array('reset', array_column($GLOBALS['v58a_events'], 'event'), true));

// ---------------------------------------------------------------------------
// 4) Log a analytika
// ---------------------------------------------------------------------------

$log = lab58_log_read('class_1a', $A, 'v58a-code', V58A_NOW + 60);
$kinds = array_count_values(array_column($log, 'kind'));
v58a_check('log:entries', ($kinds['cmd'] ?? 0) === 11 && ($kinds['open'] ?? 0) === 1 && ($kinds['complete'] ?? 0) === 1, json_encode($kinds));
v58a_check('log:entry-shape', count(array_diff(['ts', 'ctx', 'level', 'kind', 'line', 'exit', 'error_class', 'out'], array_keys($log[1] ?? []))) === 0 && $log[1]['line'] === 'ls' && $log[1]['out'] !== '');
$stats = lab58_level_stats('class_1a', $A, 'v58a-code', V58A_NOW + 60);
v58a_check('log:level-stats', $stats['attempts'] === 11 && $stats['failed_submits'] === 1 && $stats['hints'] === 1 && ($stats['errors']['no_such_file'] ?? 0) === 1 && $stats['completed_at'] === V58A_NOW + 30 && $stats['secs_open'] === 30, json_encode($stats));
$B = 'class_1a:student:v58b';
v58a_run(v58a_ctx('class_1a', $B, 'v58a-code', 'practice', V58A_NOW + 40), 'cat nic.txt');
$sum = lab58_class_log_summary('class_1a', V58A_NOW - 10, V58A_NOW + 60);
v58a_check('log:summary', $sum['students'] === 2 && ($sum['commands']['cat']['errors']['no_such_file']['students'] ?? 0) === 2 && ($sum['errors']['not_found']['students'] ?? 0) === 1, json_encode($sum['commands']['cat'] ?? null));
v58a_check('log:summary-no-names', !str_contains((string)json_encode($sum), 'v58a') && !str_contains((string)json_encode($sum), 'Test'));
$act = lab58_class_activity('class_1a', V58A_NOW - 10, 5, V58A_NOW + 60);
v58a_check('log:activity', ($act[$B]['level'] ?? '') === 'v58a-code' && ($act[$B]['errors_recent'] ?? 0) === 1 && isset($act[$A]), json_encode($act[$B] ?? null));
v58a_check('log:class-isolation', lab58_class_activity('class_2a', 0, 5, V58A_NOW + 60) === []);
foreach ([['bash: foo: command not found', 127, 'foo', 'not_found'], ['cat: x: No such file or directory', 1, 'cat x', 'no_such_file'], ["ls: cannot access 'x': No such file or directory", 2, 'ls x', 'no_such_file'], ['E: Unable to acquire the dpkg frontend lock, are you root?', 100, 'apt install x', 'permission'], ["ls: unrecognized option '--x'", 2, 'ls --x', 'bad_option'], ["bash: syntax error near unexpected token `|'", 2, '| x', 'syntax'], ['mkdir: missing operand', 1, 'mkdir', 'usage'], ['', 1, 'answer x', 'wrong_answer'], ['', 1, 'grep x y', 'other'], ['', 0, 'ls', 'ok']] as [$err, $exit, $cmd, $want]) {
    $got = lab58_classify_error($err, $exit, $cmd);
    v58a_check('classify:' . $want . ':' . $cmd, $got === $want, 'got ' . $got);
}
$R = 'class_2a:student:ring';
for ($i = 0; $i < 400; $i++) lab58_log_append('class_2a', $R, 'ring-test', ['t' => V58A_NOW - 45 * 86400 + $i * 60, 'c' => 'practice', 'k' => 'cmd', 'l' => str_repeat('x', 120), 'x' => 0, 'e' => 'ok', 'o' => str_repeat('o', 250)], $i % 50 === 0);
v58a_check('log:ring-buffer', count(lab58_log_read('class_2a', $R, 'ring-test', V58A_NOW - 44 * 86400)) === 150);
clearstatcache();
v58a_check('log:size-bounded', filesize(lab58_log_file('class_2a', $R, 'ring-test')) <= LAB58_LOG_COMPACT_BYTES + 2048, (string)filesize(lab58_log_file('class_2a', $R, 'ring-test')));
lab58_log_append('class_2a', $R, 'ring-test', ['t' => V58A_NOW, 'c' => 'practice', 'k' => 'cmd', 'l' => 'ls', 'x' => 0, 'e' => 'ok'], true);
$raw = (string)file_get_contents(lab58_log_file('class_2a', $R, 'ring-test'));
v58a_check('log:retention-on-write', substr_count($raw, "\n{") === 1 && substr_count($raw, "\n") === 2 && str_starts_with($raw, LAB58_LOG_GUARD), 'řádků ' . substr_count($raw, "\n"));
v58a_check('log:read-retention', lab58_log_read('class_2a', $R, 'ring-test', V58A_NOW + 31 * 86400) === []);
$longResp = v58a_run(v58a_ctx('class_1a', $B, 'v58a-code', 'practice', V58A_NOW + 50), 'echo ' . str_repeat('dlouhy ', 60));
$last = lab58_log_read('class_1a', $B, 'v58a-code', V58A_NOW + 60);
$last = end($last);
v58a_check('log:truncation', mb_strlen($last['line']) === 200 && mb_strlen($last['out']) === 300 && !empty($longResp['ok']));
$GLOBALS['lab58_log_defer'] = true;
v58a_run(v58a_ctx('class_1a', $B, 'v58a-code', 'practice', V58A_NOW + 55), 'pwd');
$beforeFlush = count(lab58_log_read('class_1a', $B, 'v58a-code', V58A_NOW + 60));
$flushed = lab58_log_flush();
$GLOBALS['lab58_log_defer'] = false;
v58a_check('log:deferred-flush', $flushed === 1 && count(lab58_log_read('class_1a', $B, 'v58a-code', V58A_NOW + 60)) === $beforeFlush + 1);
touch(lab58_log_file('class_2a', $R, 'ring-test'), V58A_NOW - 40 * 86400);
v58a_check('log:purge', lab58_log_purge(V58A_NOW) >= 1 && !is_file(lab58_log_file('class_2a', $R, 'ring-test')));

// ---------------------------------------------------------------------------
// 5) Kontext se sdíleným stavem týmu a rolemi
// ---------------------------------------------------------------------------

$T1 = 'class_3a:student:t1';
$T2 = 'class_3a:student:t2';
$GLOBALS['v58a_complete_cb'] = [];
v58a_check('context:register', lab58_register_context('tym', [
    'label' => 'Týmová mise',
    'levels' => static fn(array $ctx): array => ['v58a-team'],
    'access' => static fn(array $level, array $ctx): ?string => in_array($ctx['student'], [$GLOBALS['v58a_T1'], $GLOBALS['v58a_T2']], true) ? null : 'Nejsi v tomhle týmu.',
    'state_key' => static fn(array $ctx): string => 'team:' . $ctx['id'],
    'role' => static fn(array $ctx): ?string => [$GLOBALS['v58a_T1'] => 'sitar', $GLOBALS['v58a_T2'] => 'spravce'][$ctx['student']] ?? null,
    'points' => static fn(array $level, int $hints, array $ctx): int => 50,
    'on_complete' => static function (array $ctx, array $level, array $event): void { $GLOBALS['v58a_complete_cb'][] = $event; },
    'reset' => false,
]));
$GLOBALS['v58a_T1'] = $T1;
$GLOBALS['v58a_T2'] = $T2;
v58a_check('context:reject-duplicate', !lab58_register_context('tym', []));
lab58_register_pack(['id' => 'v58audit-tym', 'title' => 'Audit: tým', 'unlock' => 'free', 'order' => 990], static fn(): array => [[
    'id' => 'v58a-team', 'type' => 'check', 'title' => 'Týmová oprava', 'story' => 'Společný server.', 'task' => 'Společně vytvořte a.txt a b.txt.',
    'roles' => ['sitar' => ['task' => 'Síťař: zapiš A do ~/a.txt.'], 'spravce' => ['task' => 'Správce: zapiš B do ~/b.txt.']],
    'checks' => [['file_contains', ['path' => '~/a.txt', 'equals' => 'A', 'label' => 'a.txt']], ['file_contains', ['path' => '~/b.txt', 'equals' => 'B', 'label' => 'b.txt']]],
    'solution' => ['echo A > ~/a.txt', 'echo B > ~/b.txt'],
]]);
lab58_register_world_extender(static function (Lab57World $w, array $level, Lab57Rng $rng): void { $w->mkfile('/etc/v58audit.conf', 'uroven=' . ($level['id'] ?? '') . "\n"); });
v58a_check('context:valid', lab58_context_valid('tym:mise1') && lab58_context_valid('practice') && lab58_context_valid('race:abc123') && !lab58_context_valid('tym:ab') && !lab58_context_valid('xyz:abcd') && !lab58_context_valid('practice:abcd') && !lab58_context_valid('TYM:mise1'));
$c1 = v58a_ctx('class_3a', $T1, 'v58a-team', 'tym:mise1');
$c2 = v58a_ctx('class_3a', $T2, 'v58a-team', 'tym:mise1');
$s1 = lab57_session($c1, 'state');
$s2 = lab57_session($c2, 'state');
v58a_check('team:roles-see-different', str_starts_with((string)($s1['level']['task'] ?? ''), 'Síťař') && str_starts_with((string)($s2['level']['task'] ?? ''), 'Správce') && ($s1['context']['shared'] ?? false) === true && ($s2['level']['role'] ?? '') === 'spravce');
v58a_check('team:role-in-world', v58a_out(v58a_run($c1, 'v58role')) === "role=sitar\n");
v58a_check('team:outsider-denied', str_contains((string)(lab57_session(v58a_ctx('class_3a', 'class_3a:student:cizi', 'v58a-team', 'tym:mise1'), 'state')['error'] ?? ''), 'týmu'));
v58a_check('team:level-not-in-context', str_contains((string)(lab57_session(v58a_ctx('class_3a', $T1, 'v58a-free2', 'tym:mise1'), 'state')['error'] ?? ''), 'nepatří'));
v58a_run($c1, 'cd /tmp');
$p2 = v58a_run($c2, 'pwd');
v58a_check('team:per-player-cwd', v58a_out($p2, 1) === "/home/student\n" && str_contains((string)$p2['prompt'], ':~$'), v58a_out($p2));
v58a_run($c1, 'echo A > ~/a.txt');
v58a_check('team:shared-world', v58a_out(v58a_run($c2, 'cat ~/a.txt'), 1) === "A\n");
$h2 = v58a_out(v58a_run($c2, 'history'), 1);
v58a_check('team:per-player-history', !str_contains($h2, 'echo A') && str_contains($h2, 'cat ~/a.txt'), $h2);
v58a_check('team:extender', v58a_out(v58a_run($c1, 'cat /etc/v58audit.conf'), 1) === "uroven=v58a-team\n");
$GLOBALS['v58a_lock_path'] = lab57_state_path('class_3a', 'team:mise1');
v58a_check('team:commands-serialized-by-lock', v58a_out(v58a_run($c2, 'v58locktest'), 1) === "locked\n");
$GLOBALS['v58a_lock_path'] = lab57_state_path('class_3a', $T2);
v58a_check('team:player-file-not-used', v58a_out(v58a_run($c2, 'v58locktest'), 1) === "free\n");
v58a_check('team:reset-refused', !empty(lab57_session($c1, 'reset')['error']) && v58a_run($c1, 'reset')['exit'] === 1);
$done = v58a_run(['now' => V58A_NOW + 5] + $c2, 'echo B > ~/b.txt');
v58a_check('team:solved-shared', !empty($done['solved']) && ($done['points'] ?? 0) === 50, json_encode(['solved' => $done['solved'] ?? null, 'points' => $done['points'] ?? null]));
v58a_check('team:already-for-mate', !empty(v58a_run($c1, 'ls')['already']));
v58a_check('team:on-complete', count($GLOBALS['v58a_complete_cb']) === 1 && ($GLOBALS['v58a_complete_cb'][0]['points'] ?? 0) === 50);
$teamComplete = array_values(array_filter($GLOBALS['v58a_events'], static fn(array $e): bool => $e['event'] === 'complete' && $e['ctx'] === 'tym:mise1'))[0] ?? [];
v58a_check('team:complete-event', ($teamComplete['state_key'] ?? '') === 'team:mise1' && ($teamComplete['role'] ?? '') === 'spravce' && ($teamComplete['student'] ?? '') === $T2);
v58a_check('team:events-path', is_file(lab57_storage_dir() . '/linux_v58/ctx_tym_mise1.events.json.php') && lab57_events('tym:mise1')[0]['kind'] === 'solve');
$teamRow = lab57_store_read(lab57_state_path('class_3a', 'team:mise1'))['inst']['tym:mise1:v58a-team'] ?? [];
v58a_check('team:per-player-state-and-rate', count($teamRow['players'] ?? []) === 2 && count(array_filter(array_keys($teamRow['rl'] ?? []), static fn($k): bool => str_starts_with((string)$k, 'cmd:'))) === 2);
v58a_check('team:logs-per-player', count(lab58_log_read('class_3a', $T1, 'v58a-team')) > 0 && count(lab58_log_read('class_3a', $T2, 'v58a-team')) > 0);
v58a_check('race:context-without-arena', function_exists('arena57_race_access') || lab57_session(v58a_ctx('class_3a', $T1, 'start-1', 'race:abc123'), 'state')['error'] === 'Závody nejsou dostupné.');
v58a_check('race:events-path', lab57_events_path('race:abc123') === lab57_storage_dir() . '/linux_v57/race_abc123.events.json.php' && lab57_events_path('practice') === lab57_storage_dir() . '/lab_v57_events.json.php');

// ---------------------------------------------------------------------------
// 6) Svět: ext, uživatelé/skupiny, simulovaný čas
// ---------------------------------------------------------------------------

$P = 'class_3a:student:persist';
$cp = v58a_ctx('class_3a', $P, 'sandbox');
$t0 = (int)trim(v58a_out(v58a_run($cp, 'date +%s'), 1));
v58a_run($cp, 'v58warp 86400');
v58a_check('clock:now-default', $t0 === V58A_NOW);
v58a_check('clock:persisted', (int)trim(v58a_out(v58a_run($cp, 'date +%s'), 1)) === V58A_NOW + 86400);
v58a_check('clock:uptime', str_contains(v58a_out(v58a_run($cp, 'uptime -p'), 1), 'hours'));
v58a_run($cp, 'touch hodiny.txt');
$srow = lab57_store_read(lab57_state_path('class_3a', $P))['inst']['practice:sandbox'] ?? [];
v58a_check('clock:mtime', (int)($srow['overlay']['/home/student/hodiny.txt']['mt'] ?? 0) === V58A_NOW + 86400 && (int)($srow['ext']['clock_offset'] ?? 0) === 86400);
v58a_check('ext:persisted', v58a_out(v58a_run($cp, 'v58ext'), 1) === "ext=1\n" && v58a_out(v58a_run($cp, 'v58ext'), 1) === "ext=2\n");
v58a_check('users:not-stored-unchanged', !isset($srow['users']));
v58a_run($cp, 'v58adduser jana');
v58a_check('users:persisted', str_contains(v58a_out(v58a_run($cp, 'id jana'), 1), '(jana)') && str_contains(v58a_out(v58a_run($cp, 'grep ^jana: /etc/passwd | cat'), 1), 'jana:x:'));
v58a_check('users:stored', isset(lab57_store_read(lab57_state_path('class_3a', $P))['inst']['practice:sandbox']['users']['jana']));
lab57_session($cp, 'reset');
v58a_check('world:reset-clears', v58a_run($cp, 'id jana')['exit'] === 1 && (int)trim(v58a_out(v58a_run($cp, 'date +%s'), 1)) === V58A_NOW);

// ---------------------------------------------------------------------------
// 7) API, bezpečnost, konvence
// ---------------------------------------------------------------------------

$api = (string)file_get_contents($ROOT . '/lab_v57_api.php');
$closePos = strpos($api, 'session_write_close();');
v58a_check('api:session-write-close-after-csrf', $closePos !== false && $closePos > strpos($api, 'hash_equals') && $closePos > strpos($api, 'adaptive_student_key') && $closePos < strpos($api, 'lab57_session('));
v58a_check('api:no-session-write-after-close', preg_match('/\$_SESSION\[[^\]]+\]\s*=(?!=)/', substr($api, (int)$closePos)) !== 1);
v58a_check('api:context-validated-by-registry', str_contains($api, 'lab58_context_valid($context)'));
v58a_check('api:xp-reopens-session', str_contains((string)file_get_contents($ROOT . '/linux_v57_lab.php'), 'lab58_with_session('));

$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'create_function', 'assert', 'fsockopen', 'pfsockopen', 'stream_socket_client', 'socket_create', 'socket_connect', 'curl_init', 'curl_exec', 'curl_multi_exec', 'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr', 'mail'];
$files = array_merge(glob($ROOT . '/linux_v58_*.php') ?: [], glob($ROOT . '/lab_v58_*.php') ?: [], [__FILE__]);
$violations = [];
foreach ($files as $path) {
    $tokens = token_get_all((string)file_get_contents($path));
    $n = count($tokens);
    foreach ($tokens as $i => $tok) {
        if ($tok === '`') $violations[] = basename($path) . ': zpětné apostrofy';
        if (!is_array($tok) || $tok[0] !== T_STRING) continue;
        $j = $i + 1;
        while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        $p = $i - 1;
        while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) $p--;
        $isDecl = $p >= 0 && is_array($tokens[$p]) && in_array($tokens[$p][0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true);
        if (($tokens[$j] ?? null) === '(' && !$isDecl && in_array(strtolower($tok[1]), $forbidden, true)) $violations[] = basename($path) . ':' . $tok[2] . ' ' . $tok[1] . '()';
        if (($tokens[$j] ?? null) === '(' && in_array(strtolower($tok[1]), ['file_get_contents', 'fopen', 'file'], true)) {
            $k = $j + 1;
            while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k++;
            if (is_array($tokens[$k] ?? null) && $tokens[$k][0] === T_CONSTANT_ENCAPSED_STRING && preg_match('~^.(https?|ftp|php)://~i', $tokens[$k][1]) === 1) $violations[] = basename($path) . ':' . $tokens[$k][2] . ' URL';
        }
    }
}
v58a_check('safety:no-exec-network-v58', $violations === [], implode('; ', $violations));
v58a_check('safety:scanned', count($files) >= 3, (string)count($files));
foreach (array_merge(glob($ROOT . '/linux_v58_*.php') ?: [], glob($ROOT . '/lab_v58_*.php') ?: []) as $path) {
    $src = (string)file_get_contents($path);
    v58a_check('convention:' . basename($path), str_contains($src, 'declare(strict_types=1);') && str_contains($src, "basename((string)(\$_SERVER['SCRIPT_FILENAME']") && substr_count($src, "\n") < 800, 'strict_types, guard knihovny, < 800 řádků');
}
$extSrc = (string)file_get_contents($ROOT . '/linux_v58_ext.php');
v58a_check('safety:ext-loader-fixed-pattern', str_contains($extSrc, "glob(__DIR__ . '/linux_v58_' . \$kind . '_*.php')") && str_contains($extSrc, 'LAB58_EXT_FILE_RE'));

// ---------------------------------------------------------------------------
// 8) Výkon op=run: odložený log proti běhu bez logu (ve stejném procesu, střídavě)
// ---------------------------------------------------------------------------

$cmds = ['ls -la', 'cat vitej.txt', 'grep TODO poznamky.txt', 'cd data', 'ls', 'cd ~', 'echo $HOME', 'cat nic.txt'];
$ratios = [];
$times = ['nolog' => 0.0, 'defer' => 0.0, 'sync' => 0.0];
for ($round = 0; $round < 9; $round++) {
    $t = [];
    foreach (['nolog', 'defer', 'sync'] as $mode) {
        $GLOBALS['lab58_log_disabled'] = $mode === 'nolog';
        $GLOBALS['lab58_log_defer'] = $mode === 'defer';
        $pc = v58a_ctx('perfcls', 'perfcls:student:' . $mode . $round, 'sandbox', 'practice', V58A_NOW + $round * 1000);
        lab57_session($pc, 'state');
        $t0 = hrtime(true);
        for ($i = 0; $i < 40; $i++) { $pc['now']++; lab57_session($pc, 'run', ['line' => $cmds[$i % count($cmds)]]); }
        $t[$mode] = (hrtime(true) - $t0) / 1e6 / 40;
        $times[$mode] += $t[$mode] / 9;
        lab58_log_flush();
    }
    $ratios[] = $t['defer'] / $t['nolog'];
}
$GLOBALS['lab58_log_disabled'] = false;
$GLOBALS['lab58_log_defer'] = false;
sort($ratios);
printf("INFO perf:op-run nolog %.3f ms · odložený log %.3f ms · synchronní log %.3f ms (průměr/příkaz)\n", $times['nolog'], $times['defer'], $times['sync']);
v58a_check('perf:deferred-log-under-10pct', $ratios[4] < 1.10, sprintf('medián poměru %.3f', $ratios[4]));

// ---------------------------------------------------------------------------

v58a_check('runtime:no-php-warnings', $GLOBALS['v58a_warnings'] === [], implode(' | ', array_slice($GLOBALS['v58a_warnings'], 0, 8)));
$checks = $GLOBALS['v58a']['checks'];
$failed = $GLOBALS['v58a']['failed'];
if ($failed === 0) { echo "V58_LAB_EXT_AUDIT_OK checks={$checks} failed=0\n"; exit(0); }
echo "V58_LAB_EXT_AUDIT_FAILED checks={$checks} failed={$failed}\n";
exit(1);
