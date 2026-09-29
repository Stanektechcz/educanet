<?php

declare(strict_types=1);

/**
 * EDUCANET v60 · behaviorální audit tools/storage_selftest.php (produkční kontrola ukládání).
 *   php tools/v60_storage_selftest_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte/nezapisuje ostrou storage/.
 * Ověřuje: selftest projde nad zdravým úložištěm, po běhu nezůstane žádný _selftest_* soubor/zámek/proud,
 * data se nezmění (SHA-256 celého stromu před/po), poškozený soubor = FAIL bez úniku obsahu,
 * zastaralá záloha = WARN, neplatné TLS/--base = FAIL, --strict, deploy skript a dokumentace.
 * Konec: V60_STORAGE_SELFTEST_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v60-selftest')), '/');
require $root . '/bootstrap.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp);

/** Spustí selftest jako samostatný proces nad dočasným úložištěm. @return array{code:int,out:string} */
function v60st_run(string $root, string $storage, array $env = [], array $args = []): array
{
    $cmd = array_merge([PHP_BINARY, $root . '/tools/storage_selftest.php'], $args);
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, array_merge(getenv() ?: [], ['EDUCANET_STORAGE_DIR' => $storage], $env));
    if (!is_resource($proc)) return ['code' => -1, 'out' => ''];
    $out = (string)stream_get_contents($pipes[1]) . (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['code' => proc_close($proc), 'out' => $out];
}

/** SHA-256 všech souborů stromu (včetně .lock) podle relativní cesty. */
function v60st_tree(string $dir): array
{
    $out = [];
    if (!is_dir($dir)) return $out;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) if ($f->isFile()) $out[str_replace('\\', '/', substr($f->getPathname(), strlen($dir)))] = hash_file('sha256', $f->getPathname());
    ksort($out);
    return $out;
}

// --- Zdravé úložiště s daty ----------------------------------------------------------------------
storage_update($tmp . '/points_v53.json.php', static fn(array $d): array => $d + ['class_3a|zak' => ['earned' => 5, 'spent' => 0]]);
storage_update($tmp . '/learning_profiles.json.php', static fn(array $d): array => $d + ['class_3a:s:abc' => ['xp' => 120]]);
storage_append('marketplace_v60_log', ['type' => 'buy', 'at' => date(DATE_ATOM)]);
$backupDir = $tmp . '-backups';
mkdir($backupDir . '/storage-fresh', 0700, true);
register_shutdown_function(static function () use ($backupDir): void {
    foreach (glob($backupDir . '/*') ?: [] as $entry) { if (is_dir($entry)) @rmdir($entry); else @unlink($entry); }
    @rmdir($backupDir);
    foreach (glob($backupDir . '-env.txt') ?: [] as $file) @unlink($file);
});
$uploads = $root . '/uploads';
$cacheRt = $root . '/cache/runtime';
$dataBefore = v60st_tree($tmp);
$auxBefore = [v60st_tree($uploads), v60st_tree($cacheRt)];

$run = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir, 'EDUCANET_APP_URL' => 'http://127.0.0.1']);
$check('selftest nad zdravým úložištěm končí STORAGE_SELFTEST_OK (exit 0)', $run['code'] === 0 && str_contains($run['out'], 'STORAGE_SELFTEST_OK checks=') && str_contains($run['out'], 'failed=0'));
$check('selftest: zápisová zkouška (storage_update, zámek, storage_append) a úklid hlásí PASS', str_contains($run['out'], 'PASS storage_update: zápis a čtení zpět') && str_contains($run['out'], 'PASS dvě aktualizace po sobě') && str_contains($run['out'], 'PASS storage_append') && str_contains($run['out'], 'PASS po zkoušce nezůstal žádný dočasný soubor'));
$check('selftest: čerstvá záloha v EDUCANET_BACKUP_DIR je PASS', str_contains($run['out'], 'PASS nejnovější záloha je stará 0 h'));
$check('selftest po běhu: nezůstal žádný _selftest_* soubor, zámek ani proud selftest_*', glob($tmp . '/_selftest_*') === [] && glob($tmp . '/selftest_*') === []);
$check('selftest nezměnil data: SHA-256 celého úložiště před/po je shodné', v60st_tree($tmp) === $dataBefore && $dataBefore !== []);
$check('selftest nezměnil uploads/ ani cache/runtime (zkušební soubory se smazaly)', [v60st_tree($uploads), v60st_tree($cacheRt)] === $auxBefore);
$check('selftest nevypisuje obsah dat ani jména žáků', !str_contains($run['out'], 'class_3a|zak') && !str_contains($run['out'], 'earned'));

// --- Poškozený soubor: FAIL, jen název, nikdy obsah ------------------------------------------------------
file_put_contents($tmp . '/broken_v60.json.php', STORAGE_GUARD_LINE . "TAJNY_OBSAH_ZAKA_123 {nejde-parsovat\n");
$before = v60st_tree($tmp);
$bad = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir]);
$check('poškozený datový soubor: FAIL + STORAGE_SELFTEST_FAIL, exit 1, vypíše název souboru', $bad['code'] === 1 && str_contains($bad['out'], 'STORAGE_SELFTEST_FAIL') && str_contains($bad['out'], 'broken_v60.json.php'));
$check('poškozený datový soubor: obsah se nikdy nevypíše a soubor se nezmění', !str_contains($bad['out'], 'TAJNY_OBSAH_ZAKA_123') && v60st_tree($tmp) === $before);
unlink($tmp . '/broken_v60.json.php');
file_put_contents($tmp . '/noguard_v60.json.php', json_encode(['a' => 1]));
$noGuard = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir]);
$check('soubor bez ochranného řádku: WARN (ne FAIL), název je uveden', $noGuard['code'] === 0 && str_contains($noGuard['out'], 'WARN soubory bez ochranného prvního řádku: 1') && str_contains($noGuard['out'], 'noguard_v60.json.php'));
unlink($tmp . '/noguard_v60.json.php');

// --- Záloha, TLS, --strict, env-file ----------------------------------------------------------------------
touch($backupDir . '/storage-fresh', time() - 5 * 86400);
$stale = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir]);
$check('zastaralá záloha (5 dní): WARN, ne FAIL', $stale['code'] === 0 && str_contains($stale['out'], 'WARN nejnovější záloha je stará'));
$strict = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir], ['--strict']);
$check('--strict: WARN vrací exit 2, ale výstup zůstává STORAGE_SELFTEST_OK', $strict['code'] === 2 && str_contains($strict['out'], 'STORAGE_SELFTEST_OK'));
$noBackup = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => '']);
$check('bez EDUCANET_BACKUP_DIR: WARN, ne FAIL', $noBackup['code'] === 0 && str_contains($noBackup['out'], 'WARN EDUCANET_BACKUP_DIR není nastaveno'));
$tls = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir], ['--base=https://127.0.0.1:1']);
$check('--base s nedostupným/neplatným TLS: FAIL a exit 1', $tls['code'] === 1 && str_contains($tls['out'], 'FAIL TLS spojení s ověřením certifikátu selhalo'));
$http = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir], ['--base=http://example.invalid']);
$check('--base bez https: FAIL (cookie_secure=1 by přihlášení nepustil)', $http['code'] === 1 && str_contains($http['out'], '--base musí být adresa https'));
$https = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir, 'EDUCANET_APP_URL' => 'https://is.example.invalid']);
$check('EDUCANET_APP_URL https: připomenutí cookie_secure/certifikát', str_contains($https['out'], 'cookie_secure=1'));
$obd = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => $backupDir], ['--open-basedir=' . $root . '/nikde-jinde']);
$check('open_basedir bez session.save_path/STORAGE_DIR: FAIL', $obd['code'] === 1 && str_contains($obd['out'], 'FAIL session.save_path leží v open_basedir'));
$envFile = $tmp . '-env.txt';
file_put_contents($envFile, "# komentář\nEDUCANET_BACKUP_DIR=" . $backupDir . "\nJINA_PROMENNA=nesmi-projit\n");
$viaEnv = v60st_run($root, $tmp, ['EDUCANET_BACKUP_DIR' => ''], ['--env-file=' . $envFile]);
$check('--env-file načte EDUCANET_BACKUP_DIR', str_contains($viaEnv['out'], 'PASS adresář záloh existuje'));
unlink($envFile);

// --- Zdroj a nasazení -----------------------------------------------------------------------------------------
$tool = (string)file_get_contents($root . '/tools/storage_selftest.php');
$check('nástroj: jen CLI (PHP_SAPI guard), žádné exec/eval/curl/mail', str_contains(substr($tool, 0, 200), "PHP_SAPI !== 'cli'") && !preg_match('/\b(exec|shell_exec|system|passthru|proc_open|popen|eval|curl_\w+|mail)\s*\(/', $tool), false);
$deploy = (string)file_get_contents($root . '/docs/deploy/aapanel/deploy_aapanel.sh.example');
$check('deploy skript: po nasazení spouští storage_selftest jako www a chrání storage/ před rsync --delete', str_contains($deploy, 'storage_selftest') && str_contains($deploy, 'P /storage/***') && str_contains($deploy, 'as_web tools/storage_selftest.php') && !preg_match('#rm\s+-rf?\s+[^\n]*storage#', $deploy) && !str_contains($deploy, '--delete-excluded'), false);
$check('deploy skript: chown www:www storage uploads cache', str_contains($deploy, 'chown -R "$WEB_USER:$WEB_USER" "$SITE/storage" "$SITE/uploads"') && str_contains($deploy, '"$SITE/cache"'), false);
$doc = (string)file_get_contents($root . '/docs/NASAZENI_AAPANEL.md');
$check('dokumentace: sekce „Kontrola ukládání“ s příkazem storage_selftest', str_contains($doc, 'Kontrola ukládání') && str_contains($doc, 'tools/storage_selftest.php'), false);
$bash = trim((string)@shell_exec('bash --version 2>&1'));
if ($bash !== '' && str_contains($bash, 'GNU bash')) {
    $rc = 1;
    exec('bash -n ' . escapeshellarg($root . '/docs/deploy/aapanel/deploy_aapanel.sh.example') . ' 2>&1', $o, $rc);
    $check('deploy skript: bash -n (syntaxe) projde', $rc === 0);
}

exit(audit_summary($state, 'V60_STORAGE_SELFTEST'));
