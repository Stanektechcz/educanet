<?php

declare(strict_types=1);

/**
 * EDUCANET v61 · audit části F (produkce a provoz).
 *   php tools/v61_ops_audit.php
 *
 * F1 create-admin s EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1 bez souboru účtů (CLI projde, web do té doby 503, sdílený klíč nežije),
 * F2 týdenní kontrola (parsování výsledků, rotace 12 běhů, banner jen pro administrátora, nástroj v procesu, cron),
 * F3 šifrované zálohy (bez klíče nečitelné, špatný klíč selže, správný klíč obnoví přesně, poškození/zkrácení, rotace),
 * F4 update.sh (bash -n, jediný závěrečný řádek NASAZENO_OK/NASAZENI_FAIL, žádný P filtr, nic nemaže storage/).
 * Vše běží v dočasných adresářích (edu_audit_temp_storage) – ostrá storage/ se nečte ani nezapisuje.
 * Šifrovací kontroly potřebují rozšíření sodium; chybí-li, audit se jednou znovu spustí s `-d extension=sodium`.
 * Konec: V61_OPS_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = str_replace(chr(92), '/', dirname(__DIR__));

if (!extension_loaded('sodium') && getenv('V61_OPS_AUDIT_CHILD') !== '1') {
    $proc = proc_open([PHP_BINARY, '-d', 'extension=sodium', __FILE__], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, array_merge(getenv() ?: [], ['V61_OPS_AUDIT_CHILD' => '1']));
    if (is_resource($proc)) {
        echo stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        exit(proc_close($proc));
    }
}

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v61-ops')), '/');
require $root . '/bootstrap.php';
require_once $root . '/ops_v61.php';
require_once $root . '/ops_v58.php';
require_once __DIR__ . '/lib/v61_health_lib.php';
require_once __DIR__ . '/lib/backup_crypto_v61.php';
require_once __DIR__ . '/backup_storage.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp);

/** Dočasný adresář pod prefixem educanet-audit- (smaže se po skončení). */
function v61o_dir(string $tag): string
{
    $dir = rtrim(str_replace(chr(92), '/', sys_get_temp_dir()), '/') . '/educanet-audit-v61o-' . $tag . '-' . bin2hex(random_bytes(5));
    mkdir($dir, 0700, true);
    register_shutdown_function(static function () use ($dir): void { edu_audit_remove_dir($dir); });
    return $dir;
}

/** PHP pro podprocesy: při znovuspuštění přes -d extension=sodium ho předáme i dětem. @return list<string> */
function v61o_php(): array
{
    return getenv('V61_OPS_AUDIT_CHILD') === '1' && extension_loaded('sodium') ? [PHP_BINARY, '-d', 'extension=sodium'] : [PHP_BINARY];
}

/** Spustí příkaz bez shellu. @return array{code:int,out:string} (stdout + stderr dohromady) */
function v61o_run(array $cmd, array $env, string $cwd): array
{
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd, array_merge(getenv() ?: [], $env));
    if (!is_resource($proc)) return ['code' => -1, 'out' => ''];
    $out = (string)stream_get_contents($pipes[1]) . (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['code' => proc_close($proc), 'out' => $out];
}

/** SHA-256 všech souborů stromu (bez .lock) podle relativní cesty. @return array<string,string> */
function v61o_tree(string $dir): array
{
    $out = [];
    if (!is_dir($dir)) return $out;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->isFile() && !str_ends_with($f->getFilename(), '.lock')) $out[ltrim(str_replace(chr(92), '/', substr($f->getPathname(), strlen($dir))), '/')] = (string)hash_file('sha256', $f->getPathname());
    }
    ksort($out);
    return $out;
}

function v61o_secrets(string $dir, string $name, ?string $keyB64): string
{
    $path = $dir . '/' . $name . '.php';
    file_put_contents($path, '<?php return ' . var_export($keyB64 === null ? [] : ['backup_key' => $keyB64], true) . ';');
    return $path;
}

// ====================================================================================================
// F1 · create-admin s povinnými účty a bez souboru účtů
// ====================================================================================================
function v61o_section_create_admin(Closure $check, string $root): void
{
    $st = v61o_dir('f1');
    $shared = 'audit-shared-teacher-key-' . bin2hex(random_bytes(6));
    $env = ['EDUCANET_STORAGE_DIR' => $st, 'EDUCANET_TEACHER_ACCOUNTS_REQUIRED' => '1', 'EDUCANET_TEACHER_EXPORT_KEY' => $shared];
    $accounts = $st . '/teacher_accounts_v59.json.php';
    $check('F1 výchozí stav: soubor účtů neexistuje', !is_file($accounts));
    $h = Harness::start($env);
    try {
        $before = $h->request('GET', '/teacher.php');
        $check('F1 web bez souboru účtů a s REQUIRED=1 vrací 503', $before['status'] === 503);
        $check('F1 503 má jasnou hlášku s příkazem create-admin', str_contains($before['body'], 'create-admin') && str_contains($before['body'], 'ještě nejsou založené'));
        $check('F1 503 nenabízí přihlášení sdíleným klíčem', !str_contains($before['body'], 'name="teacher_key"'));
        $post = $h->request('POST', '/teacher.php', ['action' => 'teacher_login', 'teacher_key' => $shared, 'teacher_name' => 'Audit', 'csrf' => 'x']);
        $check('F1 sdílený klíč nežije: POST přihlášení neprojde', $post['status'] !== 200 || !str_contains($post['body'], 'teacher-topbar'));
        $tab = $h->request('GET', '/teacher.php?tab=overview&teacher_key=' . rawurlencode($shared));
        $check('F1 sdílený klíč v URL nic neodemkne (stále 503)', $tab['status'] === 503 && !str_contains($tab['body'], 'teacher-topbar'));
        $run = v61o_run([PHP_BINARY, $root . '/tools/v59_teacher_accounts.php', 'create-admin', '--login=audit.admin', '--name=Audit Admin'], $env, $root);
        $check('F1 create-admin (CLI) projde s REQUIRED=1 bez souboru účtů', $run['code'] === 0 && is_file($accounts));
        $check('F1 create-admin hlásí zapnutou pojistku a nevypíše sdílený klíč', str_contains($run['out'], 'zapnutá') && !str_contains($run['out'], $shared));
        $store = json_decode((string)substr((string)file_get_contents($accounts), strpos((string)file_get_contents($accounts), "\n") + 1), true);
        $check('F1 vznikl režim accounts s aktivním adminem', is_array($store) && ($store['mode'] ?? '') === 'accounts' && count($store['accounts'] ?? []) === 1);
        $after = $h->request('GET', '/teacher.php');
        $check('F1 po create-admin web neodpovídá 503 a nenabízí sdílený klíč', $after['status'] === 200 && !str_contains($after['body'], 'name="teacher_key"'));
    } finally {
        $h->stop();
    }
    $legacyDir = v61o_dir('f1l');
    $run = v61o_run([PHP_BINARY, $root . '/tools/v59_teacher_accounts.php', 'list'], ['EDUCANET_STORAGE_DIR' => $legacyDir, 'EDUCANET_TEACHER_ACCOUNTS_REQUIRED' => '0'], $root);
    $check('F1 bez REQUIRED zůstává legacy režim beze změny (list: legacy)', $run['code'] === 0 && str_contains($run['out'], 'legacy'));
}

// ====================================================================================================
// F2 · týdenní kontrola
// ====================================================================================================
function v61o_section_health_parse(Closure $check): void
{
    $pre = h61_parse_result('PREFLIGHT', "PASS ok\nFAIL Chybí věc\nWARN Něco\nPREFLIGHT_FAIL checks=50 failed=3 warned=9\n", 1);
    $check('F2 preflight s chybou = FAIL, počty a seznam chyb', $pre['status'] === 'FAIL' && ($pre['counts']['failed'] ?? 0) === 3 && ($pre['issues'][0] ?? '') === 'FAIL Chybí věc');
    $ok = h61_parse_result('STORAGE_SELFTEST', "PASS a\nSTORAGE_SELFTEST_OK checks=17 warn=0 failed=0\n", 0);
    $warn = h61_parse_result('STORAGE_SELFTEST', "WARN x\nSTORAGE_SELFTEST_OK checks=17 warn=3 failed=0\n", 0);
    $check('F2 samotest: bez varování PASS, s varováním WARN', $ok['status'] === 'PASS' && $warn['status'] === 'WARN');
    $perfOk = h61_parse_result('PERF_REPORT', "PERF_REPORT_OK pages=30\n", 0);
    $perfWarn = h61_parse_result('PERF_REPORT', "PERF_REPORT_WARN pages_over=2\n", 2);
    $check('F2 perf: OK = PASS, pomalé stránky = WARN', $perfOk['status'] === 'PASS' && $perfWarn['status'] === 'WARN');
    $none = h61_parse_result('PERF_REPORT', "FAIL ve třídě není žádný účet žáka\n", 1);
    $check('F2 bez závěrečného řádku = FAIL se zachovanou příčinou', $none['status'] === 'FAIL' && str_contains(implode(' ', $none['issues']), 'není žádný účet'));
    $check('F2 překročení času = FAIL', h61_parse_result('PREFLIGHT', 'PREFLIGHT_OK checks=1 failed=0 warned=0', 0, true)['status'] === 'FAIL');
    $secret = 'AbCdEfGhIjKlMnOpQrStUvWxYz0123456789abcdef';
    $line = h61_scrub('FAIL klíč=hodnota-xyz a token ' . $secret . ' ' . str_repeat('x ', 200));
    $check('F2 očištění: dlouhé tokeny a hodnoty klíčů se nezapíší, délka omezená', !str_contains($line, $secret) && !str_contains($line, 'hodnota-xyz') && mb_strlen($line) <= H61_ISSUE_LEN);
    $check('F2 celkový stav: FAIL > WARN > PASS, SKIP se nepočítá', h61_overall([['status' => 'PASS'], ['status' => 'SKIP']]) === 'PASS'
        && h61_overall([['status' => 'PASS'], ['status' => 'WARN']]) === 'WARN' && h61_overall([['status' => 'WARN'], ['status' => 'FAIL']]) === 'FAIL');
}

function v61o_section_health_store(Closure $check, string $tmp): void
{
    for ($i = 0; $i < 15; $i++) {
        ops61_health_record(['at' => date(DATE_ATOM, 1700000000 + $i * 604800), 'seq' => $i, 'checks' => ['selftest' => ['status' => 'PASS']]]);
    }
    $runs = ops61_health_runs();
    $check('F2 rotace: po 15 zápisech zůstane posledních 12 běhů v pořadí', count($runs) === OPS61_HEALTH_KEEP && $runs[0]['seq'] === 3 && $runs[11]['seq'] === 14);
    $raw = (string)file_get_contents($tmp . '/ops_health_v61.json.php');
    $check('F2 soubor výsledků má ochranný první řádek', str_starts_with($raw, '<?php http_response_code(403); exit; ?>'));
    $now = 1700000000 + 3600;
    $fail = ['at' => date(DATE_ATOM, $now - 60), 'checks' => ['selftest' => ['status' => 'PASS'], 'preflight' => ['status' => 'FAIL']]];
    $warn = ['at' => date(DATE_ATOM, $now - 60), 'checks' => ['selftest' => ['status' => 'WARN']]];
    $pass = ['at' => date(DATE_ATOM, $now - 60), 'checks' => ['selftest' => ['status' => 'PASS'], 'perf' => ['status' => 'SKIP']]];
    $html = ops61_health_banner_html($fail, true, $now);
    $check('F2 banner při FAIL: admin vidí chybu a odkaz do záložky Provoz', str_contains($html, 'teacher-flash error') && str_contains($html, '?tab=provoz#tydenni-kontrola') && str_contains($html, 'role="status"'));
    $check('F2 banner při WARN: admin vidí upozornění', str_contains(ops61_health_banner_html($warn, true, $now), 'upozornění'));
    $check('F2 banner: ostatním učitelům nic (FAIL i WARN)', ops61_health_banner_html($fail, false, $now) === '' && ops61_health_banner_html($warn, false, $now) === '');
    $check('F2 banner: čerstvé PASS a žádný běh = nic', ops61_health_banner_html($pass, true, $now) === '' && ops61_health_banner_html(null, true, $now) === '');
    $stale = ['at' => date(DATE_ATOM, $now - 20 * 86400), 'checks' => ['selftest' => ['status' => 'PASS']]];
    $check('F2 banner: kontrola starší než 10 dní se připomene', str_contains(ops61_health_banner_html($stale, true, $now), 'před 20 dny'));
    ops61_health_record(['at' => date(DATE_ATOM), 'checks' => ['preflight' => ['status' => 'FAIL', 'issues' => ['FAIL <script>alert(1)</script>']]]]);
    $admin = audit_capture(static fn() => ops61_render_health_banner(true));
    $other = audit_capture(static fn() => ops61_render_health_banner(false));
    $check('F2 vykreslení banneru nad uloženými daty: admin ano, ostatní ne', str_contains($admin, 'data-ops61-health="FAIL"') && $other === '');
    $panel = audit_capture(static fn() => ops61_render_health_panel());
    $check('F2 panel v záložce Provoz escapuje obsah (žádné holé <script>)', str_contains($panel, 'id="tydenni-kontrola"') && !str_contains($panel, '<script>') && str_contains($panel, '&lt;script&gt;'));
}

function v61o_section_health_tool(Closure $check, string $root): void
{
    $st = v61o_dir('f2t');
    $env = ['EDUCANET_STORAGE_DIR' => $st];
    $run = v61o_run([PHP_BINARY, $root . '/tools/v61_weekly_health.php', '--skip=preflight,perf', '--no-log'], $env, $root);
    $final = trim((string)(preg_split('~\R~', trim($run['out'])) ?: [''])[count(preg_split('~\R~', trim($run['out'])) ?: []) - 1]);
    $store = $st . '/ops_health_v61.json.php';
    $data = is_file($store) ? json_decode((string)substr((string)file_get_contents($store), strpos((string)file_get_contents($store), "\n") + 1), true) : null;
    $check('F2 nástroj proběhne a končí řádkem WEEKLY_HEALTH_*', preg_match('~^WEEKLY_HEALTH_(OK|WARN|FAIL) checks=3 ~', $final) === 1 && in_array($run['code'], [0, 1, 2], true));
    $check('F2 výsledek zapsán do storage/ops_health_v61.json.php (1 běh, 3 kontroly, SKIP u přeskočených)',
        is_array($data) && count($data['runs'] ?? []) === 1 && ($data['runs'][0]['checks']['preflight']['status'] ?? '') === 'SKIP' && isset($data['runs'][0]['checks']['selftest']['counts']['checks']));
    $json = (string)json_encode($data);
    $check('F2 uložené jsou jen stavy a počty (žádné cesty ke storage ani e-maily)', !str_contains($json, $st) && !str_contains($json, '@'));
    $run2 = v61o_run([PHP_BINARY, $root . '/tools/v61_weekly_health.php', '--skip=preflight,perf', '--no-log', '--no-record'], $env, $root);
    $data2 = json_decode((string)substr((string)file_get_contents($store), strpos((string)file_get_contents($store), "\n") + 1), true);
    $check('F2 --no-record nic nezapíše', count($data2['runs'] ?? []) === 1 && str_contains($run2['out'], 'recorded=0'));
    $logFile = $st . '/health.log';
    v61o_run([PHP_BINARY, $root . '/tools/v61_weekly_health.php', '--skip=preflight,perf,selftest', '--no-record', '--log=' . $logFile], $env, $root);
    $check('F2 jednořádkový záznam v logu (--log)', is_file($logFile) && preg_match('~^\S+ WEEKLY_HEALTH_(OK|WARN|FAIL) checks=3 ~', (string)file_get_contents($logFile)) === 1);
    $perf = v61o_run([PHP_BINARY, $root . '/tools/v61_weekly_health.php', '--skip=preflight,selftest', '--no-log', '--no-record'], $env, $root);
    $check('F2 perf na prázdném úložišti se vyhodnotí (FAIL s příčinou nebo WARN), nic nespadne', preg_match('~^(FAIL|WARN|PASS) perf~m', $perf['out']) === 1);
}

// ====================================================================================================
// F3 · šifrované zálohy
// ====================================================================================================
/** Zdrojové úložiště s vymyšlenými daty. @return array{dir:string,marker:string} */
function v61o_make_source(): array
{
    $dir = v61o_dir('src');
    $marker = 'MARKER-' . bin2hex(random_bytes(8));
    mkdir($dir . '/sub', 0700, true);
    file_put_contents($dir . '/points_v53.json.php', "<?php http_response_code(403); exit; ?>\n{\"$marker\":1}");
    file_put_contents($dir . '/sub/nested_data.json.php', "<?php http_response_code(403); exit; ?>\n{\"x\":\"" . str_repeat('ěščř', 40) . '"}');
    file_put_contents($dir . '/big_blob.json.php', "<?php http_response_code(403); exit; ?>\n" . random_bytes(300000));
    file_put_contents($dir . '/empty_file.json.php', '');
    file_put_contents($dir . '/accounts_v58_key.json.php', 'EXCLUDED-KEY-FILE');
    return ['dir' => $dir, 'marker' => $marker];
}

function v61o_section_crypto_lib(Closure $check): void
{
    $key = random_bytes(32);
    $threw = static function (callable $fn): bool { try { $fn(); return false; } catch (Throwable $e) { return true; } };
    $check('F3 klíč: špatná délka / neplatné base64 se odmítne', $threw(static fn() => bkc61_decode_key('abc')) && $threw(static fn() => bkc61_decode_key(base64_encode(random_bytes(16)))) && strlen(bkc61_decode_key(base64_encode($key))) === 32);
    $dir = v61o_dir('evil');
    $archive = $dir . '/evil.edubak';
    $w = new Bkc61Writer($archive, $key);
    $w->write(pack('n', 12) . 'storage/../x' . pack('J', 3) . 'abc' . pack('n', 0));
    $w->finish();
    $out = $dir . '/out';
    mkdir($out);
    $check('F3 archiv s cestou „..“ se nerozbalí a nic nevznikne mimo cíl', $threw(static fn() => bkc61_decrypt_to_dir($archive, $out, $key)) && !is_file($dir . '/x') && v61o_tree($out) === []);
}

/** Jeden běh zálohy. */
function v61o_backup(string $root, string $src, string $dest, array $env, array $args): array
{
    return v61o_run(array_merge(v61o_php(), [$root . '/tools/backup_storage.php', '--src=' . $src, '--dest=' . $dest], $args), $env, $root);
}

function v61o_section_crypto(Closure $check, string $root, string $tmp): void
{
    if (!extension_loaded('sodium')) { $check('F3 sodium není k dispozici ani přes -d extension=sodium – šifrované kontroly přeskočeny (WARN)', true, false); return; }
    $keyA = base64_encode(random_bytes(32));
    $keyB = base64_encode(random_bytes(32));
    $cfg = v61o_dir('cfg');
    $envA = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_SECRETS_FILE' => v61o_secrets($cfg, 'a', $keyA)];
    $envB = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_SECRETS_FILE' => v61o_secrets($cfg, 'b', $keyB)];
    $envNone = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_SECRETS_FILE' => v61o_secrets($cfg, 'none', null)];
    $src = v61o_make_source();
    $dest = v61o_dir('dest');
    $outputs = [];

    $none = v61o_backup($root, $src['dir'], $dest, $envNone, ['--encrypt']);
    $outputs[] = $none['out'];
    $check('F3 --encrypt bez backup_key: chyba, žádný nešifrovaný fallback ani zbytky v cíli', $none['code'] === 1 && str_contains($none['out'], 'backup_key') && v61o_dir_entries($dest) === []);
    $bad = v61o_backup($root, $src['dir'], $dest, ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_SECRETS_FILE' => v61o_secrets($cfg, 'bad', 'neni-base64-32-bajtu')], ['--encrypt']);
    $check('F3 --encrypt s neplatným klíčem: chyba bez zálohy', $bad['code'] === 1 && v61o_dir_entries($dest) === []);
    $soft = v61o_backup($root, $src['dir'], $dest, $envNone, ['--encrypt-if-key']);
    $check('F3 --encrypt-if-key bez klíče: varování a nešifrovaná záloha (cron)', $soft['code'] === 0 && str_contains($soft['out'], 'WARN backup_key') && count(glob($dest . '/storage-*', GLOB_ONLYDIR) ?: []) === 1);
    foreach (glob($dest . '/storage-*', GLOB_ONLYDIR) ?: [] as $d) bkp_rrmdir($d);

    $ok = v61o_backup($root, $src['dir'], $dest, $envA, ['--encrypt']);
    $outputs[] = $ok['out'];
    $archives = glob($dest . '/storage-*.edubak') ?: [];
    $check('F3 --encrypt s klíčem vytvoří archiv .edubak + .sha256 a v cíli nezůstane otevřená kopie', $ok['code'] === 0 && count($archives) === 1 && is_file($archives[0] . '.sha256')
        && array_map('basename', v61o_dir_entries($dest)) === [basename($archives[0]), basename($archives[0]) . '.sha256']);
    $archive = $archives[0] ?? '';
    $raw = (string)@file_get_contents($archive);
    $check('F3 archiv bez klíče nejde přečíst (žádný obsah ani názvy souborů v otevřené podobě)', str_starts_with($raw, 'EDUBAK61') && !str_contains($raw, $src['marker']) && !str_contains($raw, 'points_v53') && !str_contains($raw, 'manifest') && !str_contains($raw, 'ěščř'));
    $check('F3 hash .sha256 odpovídá archivu', trim((string)strtok((string)file_get_contents($archive . '.sha256'), ' ')) === hash_file('sha256', $archive));

    $live = v61o_dir('live');
    foreach (v61o_tree($src['dir']) as $rel => $_) { @mkdir(dirname($live . '/' . $rel), 0700, true); copy($src['dir'] . '/' . $rel, $live . '/' . $rel); }
    unlink($live . '/sub/nested_data.json.php');
    file_put_contents($live . '/points_v53.json.php', 'ZMENENO');
    file_put_contents($live . '/extra_file.json.php', 'navic');
    $safety = v61o_dir('safety');
    $scratch = v61o_dir('scratch');
    $restoreEnv = static fn(array $e): array => $e + ['EDUCANET_BACKUP_DIR' => $safety];
    $args = static function (string $file, string ...$more) use ($root, $live, $scratch): array { return array_merge(v61o_php(), [$root . '/tools/restore_storage.php', '--from=' . $file, '--decrypt', '--dest=' . $live, '--tmp=' . $scratch], $more); };

    $before = v61o_tree($live);
    $wrong = v61o_run($args($archive, '--apply'), $restoreEnv($envB), $root);
    $outputs[] = $wrong['out'];
    $check('F3 špatný klíč: obnova selže a ostrá (zde zkušební) data se nezmění', $wrong['code'] === 1 && str_contains($wrong['out'], 'Dešifrování selhalo') && v61o_tree($live) === $before);
    $nokey = v61o_run($args($archive, '--apply'), $restoreEnv($envNone), $root);
    $check('F3 obnova bez klíče: jasná chyba, data beze změny', $nokey['code'] === 1 && str_contains($nokey['out'], 'backup_key') && v61o_tree($live) === $before);
    $check('F3 po neúspěšném rozšifrování nezůstal v tmp otevřený obsah', v61o_dir_entries($scratch) === []);

    $tampered = $dest . '/tampered.edubak';
    $bytes = $raw; $bytes[200] = chr(ord($bytes[200]) ^ 0x55);
    file_put_contents($tampered, $bytes);
    $tamperedRun = v61o_run($args($tampered, '--apply'), $restoreEnv($envA), $root);
    file_put_contents($tampered . '.sha256', (string)file_get_contents($archive . '.sha256'));
    $tamperedSidecar = v61o_run($args($tampered, '--apply'), $restoreEnv($envA), $root);
    $check('F3 poškozený archiv selže (dešifrování i kontrola otisku .sha256)', $tamperedRun['code'] === 1 && str_contains($tamperedRun['out'], 'Dešifrování selhalo') && $tamperedSidecar['code'] === 1 && str_contains($tamperedSidecar['out'], 'SHA-256') && v61o_tree($live) === $before);
    $short = $dest . '/short.edubak';
    file_put_contents($short, substr($raw, 0, -50));
    $shortRun = v61o_run($args($short, '--apply'), $restoreEnv($envA), $root);
    $check('F3 zkrácený archiv selže a nic se neobnoví', $shortRun['code'] === 1 && str_contains($shortRun['out'], 'zkrácen') && v61o_tree($live) === $before);
    unlink($tampered); unlink($tampered . '.sha256'); unlink($short);

    $dry = v61o_run($args($archive), $restoreEnv($envA), $root);
    $check('F3 bez --apply jen náhled (rozdíly, nic se nezapíše)', str_contains($dry['out'], 'RESTORE_STORAGE_DRYRUN') && v61o_tree($live) === $before);
    $good = v61o_run($args($archive, '--apply', '--prune'), $restoreEnv($envA), $root);
    $outputs[] = $good['out'];
    $expected = v61o_tree($src['dir']);
    unset($expected['accounts_v58_key.json.php']);
    $restored = v61o_tree($live);
    unset($restored['accounts_v58_key.json.php']);
    $check('F3 správný klíč: obnova s bezpečnostní zálohou a SHA-256 shoda všech souborů se zdrojem', $good['code'] === 0 && str_contains($good['out'], 'DECRYPT_OK') && str_contains($good['out'], 'SAFETY_BACKUP') && $restored === $expected && $expected !== []);
    $check('F3 obnova nenechá otevřený obsah v tmp', v61o_dir_entries($scratch) === []);
    $leak = false;
    foreach ($outputs as $o) { if (str_contains($o, $keyA) || str_contains($o, $keyB)) $leak = true; }
    $check('F3 klíč (base64) se v žádném výstupu nevyskytuje', !$leak);

    v61o_section_rotation($check, $root, $src['dir'], $envA);
    putenv('EDUCANET_BACKUP_DIR=' . $dest);
    $last = ops58_last_backup();
    $check('F3 záložka Provoz pozná šifrovaný archiv jako poslední zálohu', is_array($last) && !empty($last['encrypted']) && !empty($last['manifest_ok']));
    putenv('EDUCANET_BACKUP_DIR');
}

function v61o_section_rotation(Closure $check, string $root, string $src, array $env): void
{
    $dest = v61o_dir('rot');
    mkdir($dest . '/storage-19990101-000000', 0700);
    $names = [];
    for ($i = 0; $i < 5; $i++) {
        $r = v61o_backup($root, $src, $dest, $env, ['--encrypt', '--keep=3']);
        if ($r['code'] !== 0) break;
        foreach (glob($dest . '/storage-*.edubak') ?: [] as $a) $names[basename($a)] = true;
    }
    $left = array_map('basename', glob($dest . '/storage-*.edubak') ?: []);
    $all = array_keys($names);
    sort($all);
    $check('F3 rotace --keep=3: zůstanou 3 nejnovější archivy i s .sha256, starší zmizí', count($left) === 3 && $left === array_slice($all, -3) && count(glob($dest . '/storage-*.edubak.sha256') ?: []) === 3);
    $check('F3 rotace nesahá na nešifrované zálohy (adresáře storage-*)', is_dir($dest . '/storage-19990101-000000'));
}

/** Položky adresáře bez . a .. (včetně skrytých). @return list<string> */
function v61o_dir_entries(string $dir): array
{
    $out = [];
    foreach (scandir($dir) ?: [] as $e) { if ($e !== '.' && $e !== '..') $out[] = $dir . '/' . $e; }
    sort($out);
    return $out;
}

// ====================================================================================================
// F4 · update.sh, deploy, cron, dokumentace
// ====================================================================================================
function v61o_bash(): ?string
{
    foreach (['bash', 'C:/Program Files/Git/bin/bash.exe', 'C:/Program Files/Git/usr/bin/bash.exe'] as $b) {
        $r = v61o_run([$b, '-c', 'echo ok'], [], sys_get_temp_dir());
        if (trim($r['out']) === 'ok') return $b;
    }
    return null;
}

function v61o_section_scripts(Closure $check, string $root): void
{
    $dir = $root . '/docs/deploy/aapanel';
    $update = (string)file_get_contents($dir . '/update_aapanel.sh.example');
    $deploy = (string)file_get_contents($dir . '/deploy_aapanel.sh.example');
    $cron = (string)file_get_contents($dir . '/educanet-cron.sh.example');
    $bash = v61o_bash();
    if ($bash === null) {
        $check('F4 bash není k dispozici – kontroly bash -n a běhu update.sh přeskočeny', true, false);
    } else {
        $syntax = true;
        foreach (['update_aapanel.sh.example', 'deploy_aapanel.sh.example', 'educanet-cron.sh.example'] as $f) $syntax = $syntax && v61o_run([$bash, '-n', $dir . '/' . $f], [], $root)['code'] === 0;
        $check('F4 bash -n: update, deploy i cron skript projdou', $syntax);
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $check('F4 běh update.sh jako neroot přeskočen (audit běží jako root)', true, false);
        } else {
            $run = v61o_run([$bash, $dir . '/update_aapanel.sh.example'], [], $root);
            $lines = array_values(array_filter(preg_split('~\R~', trim($run['out'])) ?: [], static fn(string $l): bool => $l !== ''));
            $status = array_values(array_filter($lines, static fn(string $l): bool => preg_match('~^(NASAZENO_OK|NASAZENI_FAIL)(\s|$)~', $l) === 1));
            $check('F4 update.sh bez práv roota: právě jeden závěrečný řádek NASAZENI_FAIL <důvod>, exit 1, nic nenasazeno',
                $run['code'] === 1 && count($status) === 1 && end($lines) === $status[0] && preg_match('~^NASAZENI_FAIL \S+~', $status[0]) === 1);
        }
    }
    $check('F4 update.sh stahuje main.zip z GitHubu s cache-busterem', str_contains($update, 'https://github.com/Stanektechcz/educanet/archive/refs/heads/main.zip') && str_contains($update, '?cb=$(date +%s)'), false);
    $posNginxT = strpos($update, '"$bin" -t'); $posReload = strpos($update, '-s reload');
    $check('F4 update.sh reload nginx až po úspěšném nginx -t', $posNginxT !== false && $posReload !== false && $posNginxT < $posReload && str_contains($update, 'reload přeskočen'), false);
    $check('F4 update.sh ověřuje built_at, selftest --base a http_smoke s allow_url_fopen', str_contains($update, 'built_at') && str_contains($update, 'storage_selftest.php --base=') && str_contains($update, 'allow_url_fopen=1 tools/http_smoke.php --base='), false);
    $check('F4 update.sh instaluje deploy skript z ZIPu až po bash -n', preg_match('~bash -n "\$new".*install -m 0750~s', $update) === 1, false);
    $rollbackLines = array_filter(explode("\n", $update), static fn(string $l): bool => stripos($l, 'rollback') !== false && !str_starts_with(trim($l), '#') && !str_contains($l, 'echo ') && !str_contains($l, 'fail "'));
    $check('F4 update.sh nikdy sám nespouští rollback (jen ho vypíše)', $rollbackLines === [], false);
    $code = implode("\n", array_filter(explode("\n", $update . "\n" . $deploy), static fn(string $l): bool => !str_starts_with(ltrim($l), '#')));
    $check('F4 update.sh ani deploy (mimo komentáře) neobsahují rsync filtr „P /storage“ ani nemažou storage/',
        !preg_match('~["\']P\s*/storage|--filter=["\']?P\b|-f\s+["\']?P\b~', $code) && !preg_match('~rm\s+-rf?\s[^\n]*storage~', $code) && !str_contains($code, '--delete-excluded'), false);
    $check('F4 deploy má příkaz from-dir a šifrovanou zálohu při dostupném klíči', str_contains($deploy, "\n  from-dir)") && str_contains($deploy, '--encrypt-if-key'), false);
    $check('F4 cron: případ health a šifrovaná záloha (--encrypt-if-key)', str_contains($cron, "\n  health)") && str_contains($cron, 'v61_weekly_health.php') && str_contains($cron, '--encrypt-if-key'), false);
    $env = (string)file_get_contents($dir . '/educanet.env.example');
    $check('F4 env příklad: REQUIRED=1 odkomentované a návod už nevyžaduje dočasné zakomentování', preg_match('~^EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1$~m', $env) === 1 && !str_contains($env, 'dočasně zakomentuj'), false);
    $docs = (string)@file_get_contents($root . '/docs/ZALOHY_V61.md');
    $check('F4 docs/ZALOHY_V61.md: klíč, obnova krok za krokem, test obnovy', str_contains($docs, 'backup_key') && str_contains($docs, '--decrypt') && str_contains($docs, 'random_bytes(32)') && str_contains($docs, 'Test obnovy'), false);
    $nas = (string)@file_get_contents($root . '/docs/NASAZENI_AAPANEL.md');
    $check('F4 NASAZENI_AAPANEL.md popisuje update.sh, health cron, šifrované zálohy a první nasazení', str_contains($nas, 'update.sh') && str_contains($nas, 'educanet-cron.sh health') && str_contains($nas, 'ZALOHY_V61') && str_contains($nas, 'NASAZENO_OK'), false);
}

v61o_section_create_admin($check, $root);
v61o_section_health_parse($check);
v61o_section_health_store($check, $tmp);
v61o_section_health_tool($check, $root);
v61o_section_crypto_lib($check);
v61o_section_crypto($check, $root, $tmp);
v61o_section_scripts($check, $root);

exit(audit_summary($state, 'V61_OPS'));
