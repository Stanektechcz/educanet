<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/v58_ops_audit.php
 *
 * Ověřuje OPS-03/04/05 v izolované dočasné storage (nikdy se nedotkne ostré storage/):
 * záloha → změna → obnova (obsah shodný), rotace záloh, dry-run restore nic nezmění,
 * retention dry-run nic nezmění a --apply archivuje, ops58_health() vrací očekávané
 * klíče, CLI guardy nástrojů, run_audits.php --only=v57 na malé sadě.
 *
 * PASS …/FAIL … po řádcích, konec V58_OPS_AUDIT_OK checks=N failed=0 (jinak FAILED, exit 1).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/ops_v58.php';
require_once __DIR__ . '/backup_storage.php';
require_once __DIR__ . '/restore_storage.php';

$checks = 0;
$failed = 0;

function opsaudit_check(bool $cond, string $label): void
{
    global $checks, $failed;
    $checks++;
    if ($cond) {
        echo "PASS $label\n";
    } else {
        $failed++;
        echo "FAIL $label\n";
    }
}

function opsaudit_rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $full = $dir . '/' . $item;
        is_dir($full) ? opsaudit_rrmdir($full) : @unlink($full);
    }
    @rmdir($dir);
}

function opsaudit_write(string $path, string $json): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    file_put_contents($path, "<?php http_response_code(403); exit; ?>\n" . $json);
}

$base = sys_get_temp_dir() . '/educanet_ops_audit_' . bin2hex(random_bytes(6));
mkdir($base, 0700, true);
$isolatedStorage = $base . '/storage';
$backupDir = $base . '/backups';
mkdir($isolatedStorage, 0700, true);
mkdir($backupDir, 0700, true);

$cleanup = static function () use ($base): void {
    opsaudit_rrmdir($base);
};
register_shutdown_function($cleanup);

try {
    // -----------------------------------------------------------------
    // Fixtures v izolované storage
    // -----------------------------------------------------------------
    opsaudit_write($isolatedStorage . '/sample_a.json.php', json_encode(['v' => 1], JSON_THROW_ON_ERROR));
    opsaudit_write($isolatedStorage . '/nested/sample_b.json.php', json_encode(['v' => 2], JSON_THROW_ON_ERROR));

    // -----------------------------------------------------------------
    // Backup -> change -> restore, obsah musí sedět
    // -----------------------------------------------------------------
    $backup1 = backup_storage_run($isolatedStorage, $backupDir, 14, false);
    opsaudit_check(is_dir($backup1) && is_file($backup1 . '/manifest.json'), 'backup_storage_run vytvoří adresář s manifestem');

    opsaudit_write($isolatedStorage . '/sample_a.json.php', json_encode(['v' => 99], JSON_THROW_ON_ERROR));

    $diff = restore_diff(restore_load_manifest($backup1), $isolatedStorage);
    opsaudit_check(in_array('sample_a.json.php', $diff['changed'], true), 'restore_diff detekuje změněný soubor');

    // Dry-run restore nesmí nic změnit.
    $beforeDryRun = file_get_contents($isolatedStorage . '/sample_a.json.php');
    restore_diff(restore_load_manifest($backup1), $isolatedStorage); // jen čte
    $afterDryRun = file_get_contents($isolatedStorage . '/sample_a.json.php');
    opsaudit_check($beforeDryRun === $afterDryRun, 'dry-run restore (jen diff) nic nemění');

    $applied = restore_apply(restore_load_manifest($backup1), $backup1, $isolatedStorage, false);
    opsaudit_check(in_array('sample_a.json.php', $applied['restored'], true), 'restore_apply obnoví změněný soubor');
    $restoredContent = (string)file_get_contents($isolatedStorage . '/sample_a.json.php');
    opsaudit_check(str_contains($restoredContent, '"v":1') || str_contains($restoredContent, '"v": 1'), 'obsah po obnově odpovídá záloze (v=1)');

    // -----------------------------------------------------------------
    // Rotace záloh (--keep)
    // -----------------------------------------------------------------
    for ($i = 0; $i < 4; $i++) {
        usleep(1100000); // adresáře se jmenují podle sekundy, ať se nepřepíšou
        backup_storage_run($isolatedStorage, $backupDir, 2, false);
    }
    $remaining = glob($backupDir . '/storage-*', GLOB_ONLYDIR) ?: [];
    opsaudit_check(count($remaining) === 2, 'rotace zálohy ponechá jen --keep=2 nejnovějších, má ' . count($remaining));

    // -----------------------------------------------------------------
    // SEC58-10: klíč jednorázových hesel se nezálohuje a --prune ho nesmaže
    // -----------------------------------------------------------------
    opsaudit_write($isolatedStorage . '/accounts_v58_key.json.php', json_encode(['key' => base64_encode(random_bytes(32))], JSON_THROW_ON_ERROR));
    usleep(1100000);
    $backupKey = backup_storage_run($isolatedStorage, $backupDir, 5, false, null, true);
    $keyManifest = restore_load_manifest($backupKey);
    opsaudit_check(!is_file($backupKey . '/storage/accounts_v58_key.json.php') && !in_array('accounts_v58_key.json.php', array_column($keyManifest['files'], 'path'), true), 'SEC58-10: záloha neobsahuje accounts_v58_key.json.php');
    opsaudit_check(!in_array('accounts_v58_key.json.php', restore_diff($keyManifest, $isolatedStorage)['missing'], true), 'SEC58-10: restore diff klíč nehlásí jako navíc');
    restore_apply($keyManifest, $backupKey, $isolatedStorage, true);
    opsaudit_check(is_file($isolatedStorage . '/accounts_v58_key.json.php'), 'SEC58-10: restore --prune klíč v ostré storage nesmaže');

    // -----------------------------------------------------------------
    // OPS58-11: cesty z manifestu se validují (žádné ../, absolutní cesty ani cizí znaky)
    // -----------------------------------------------------------------
    $outsideFile = $base . '/outside-pwned.json.php';
    $evilCases = ['../outside-pwned.json.php', 'nested/../../outside-pwned.json.php', '/etc/passwd', 'C:/Windows/evil.php', 'a\\..\\b.php', 'x//y.php', './x.php', "bad\0name.php", ''];
    foreach ($evilCases as $evilRel) {
        $evilManifest = ['files' => [['path' => $evilRel, 'sha256' => '0']]];
        $rejectedApply = false;
        try { restore_apply($evilManifest, $backupKey, $isolatedStorage, false); } catch (Throwable $e) { $rejectedApply = true; }
        $rejectedDiff = false;
        try { restore_diff($evilManifest, $isolatedStorage); } catch (Throwable $e) { $rejectedDiff = true; }
        opsaudit_check($rejectedApply && $rejectedDiff && !is_file($outsideFile), 'OPS58-11: manifest s cestou ' . json_encode($evilRel) . ' je odmítnut (diff i apply)');
    }
    opsaudit_check(restore_validate_rel('linux_v58/log/class_3a__ab/sandbox.jsonl.php') === 'linux_v58/log/class_3a__ab/sandbox.jsonl.php', 'OPS58-11: běžná vnořená cesta projde');

    // OPS58-14: interní adresáře mají deny .htaccess. lab-runtime/ je kandidát na vyřazení z vydání
    // (PLAN_F4_PROD.md A.1/A.3) – pokud adresář v tomto stromu vůbec existuje, pak musí zakazovat
    // přístup stejně jako ostatní; pokud byl už odstraněn, na tuto podmínku se nic neváže.
    foreach (['docs', '.claude'] as $internalDir) {
        $ht = (string)@file_get_contents(dirname(__DIR__) . '/' . $internalDir . '/.htaccess');
        opsaudit_check(str_contains($ht, 'Require all denied') && str_contains($ht, 'Deny from all'), "OPS58-14: $internalDir/.htaccess zakazuje přístup z webu");
    }
    $labRuntimeDir = dirname(__DIR__) . '/lab-runtime';
    if (is_dir($labRuntimeDir)) {
        $ht = (string)@file_get_contents($labRuntimeDir . '/.htaccess');
        opsaudit_check(str_contains($ht, 'Require all denied') && str_contains($ht, 'Deny from all'), 'OPS58-14: lab-runtime/.htaccess zakazuje přístup z webu (adresář existuje)');
    } else {
        opsaudit_check(true, 'OPS58-14: lab-runtime/ neexistuje ve stromu, deny .htaccess se nevyžaduje');
    }
    $installMd = (string)@file_get_contents(dirname(__DIR__) . '/INSTALL.md');
    opsaudit_check(str_contains($installMd, 'location ~* \.md$ { deny all; }') && str_contains($installMd, '<FilesMatch "\.md$">'), 'OPS58-14: INSTALL.md popisuje zákaz *.md pro Apache i nginx');

    // -----------------------------------------------------------------
    // Retenční politika: dry-run nic nemění, --apply archivuje
    // -----------------------------------------------------------------
    $GLOBALS['lab57_storage_override'] = null; // jistota, ať se nepoužije jiná storage
    putenv('EDUCANET_STORAGE_DIR=' . $isolatedStorage);
    $_ENV['EDUCANET_STORAGE_DIR'] = $isolatedStorage;
    if (!defined('STORAGE_DIR')) {
        // STORAGE_DIR je definováno v bootstrap.php už dřív s jinou hodnotou (real storage);
        // ops58_* funkce ho čtou přímo jako konstantu, proto testujeme přes dočasné přepnutí
        // v samostatném subprocesu, ne uvnitř tohoto běhu (viz níže).
    }
    $retentionScript = __DIR__ . '/v58_retention.php';
    $phpBinary = getenv('PHP_BINARY') ?: 'C:/php/php.exe';
    if (!is_file($phpBinary)) {
        $phpBinary = PHP_BINARY;
    }
    // Simuluj starý soubor pro archivaci.
    $raceRel = 'linux_v57/race_ops_audit.events.json.php';
    opsaudit_write($isolatedStorage . '/' . $raceRel, json_encode([['student' => 'x']], JSON_THROW_ON_ERROR));
    @touch($isolatedStorage . '/' . $raceRel, time() - (400 * 86400));

    $env = ['EDUCANET_STORAGE_DIR' => $isolatedStorage];
    $dryOut = opsaudit_run_subprocess($phpBinary, $retentionScript, [], $env);
    opsaudit_check(!is_file($isolatedStorage . '/archive/' . ops58_school_year_for_archive() . '/' . $raceRel), 'retention dry-run nearchivuje soubor');
    opsaudit_check(str_contains($dryOut, 'DRYRUN') && str_contains($dryOut, 'actions=1'), 'retention dry-run hlásí 1 akci: ' . trim($dryOut));

    $applyOut = opsaudit_run_subprocess($phpBinary, $retentionScript, ['--apply'], $env);
    $archivedPath = $isolatedStorage . '/archive/' . ops58_school_year_for_archive() . '/' . $raceRel;
    opsaudit_check(is_file($archivedPath), 'retention --apply archivuje starý soubor: ' . $archivedPath);
    opsaudit_check(!is_file($isolatedStorage . '/' . $raceRel), 'retention --apply přesune originál (nezůstane duplicitně)');
    opsaudit_check(str_contains($applyOut, 'APPLY'), 'retention --apply hlásí APPLY: ' . trim($applyOut));

    // DAT58-03: logy příkazů labu (linux_v58/log/<třída>__<sha1>/…) starší 30 dní se MAŽOU, mladší zůstanou.
    $oldLogDir = 'linux_v58/log/class_3a__' . sha1('audit-stary');
    $newLogDir = 'linux_v58/log/class_3a__' . sha1('audit-novy');
    opsaudit_write($isolatedStorage . '/' . $oldLogDir . '/sandbox.jsonl.php', '{"t":1}');
    opsaudit_write($isolatedStorage . '/' . $oldLogDir . '/meta.json.php', '{"student":"x"}');
    opsaudit_write($isolatedStorage . '/' . $newLogDir . '/sandbox.jsonl.php', '{"t":2}');
    @touch($isolatedStorage . '/' . $oldLogDir . '/sandbox.jsonl.php', time() - (40 * 86400));
    $logDry = opsaudit_run_subprocess($phpBinary, $retentionScript, [], $env);
    opsaudit_check(is_file($isolatedStorage . '/' . $oldLogDir . '/sandbox.jsonl.php') && str_contains($logDry, 'DELETE ' . $oldLogDir . '/sandbox.jsonl.php'), 'DAT58-03: dry-run ohlásí smazání starého logu labu a nic nesmaže: ' . trim($logDry));
    opsaudit_check(!str_contains($logDry, $newLogDir), 'DAT58-03: mladší log labu se v náhledu neobjeví');
    $logApply = opsaudit_run_subprocess($phpBinary, $retentionScript, ['--apply'], $env);
    opsaudit_check(!is_file($isolatedStorage . '/' . $oldLogDir . '/sandbox.jsonl.php') && !is_dir($isolatedStorage . '/' . $oldLogDir), 'DAT58-03: --apply smaže log starší 30 dní i prázdnou složku žáka (lab58_log_purge)');
    opsaudit_check(!is_file($isolatedStorage . '/archive/' . ops58_school_year_for_archive() . '/' . $oldLogDir . '/sandbox.jsonl.php'), 'DAT58-03: log labu se nearchivuje (data nezletilých se mažou)');
    opsaudit_check(is_file($isolatedStorage . '/' . $newLogDir . '/sandbox.jsonl.php'), 'DAT58-03: mladší log labu zůstane');
    opsaudit_check(str_contains($logApply, 'lab_log_purged='), 'DAT58-03: --apply volá lab58_log_purge(): ' . trim($logApply));
    $policyPatterns = array_column(ops58_retention_policy(), 'action', 'pattern');
    opsaudit_check(($policyPatterns['linux_v58/log/*.jsonl.php'] ?? '') === 'delete_after_days' && ops58_matches_pattern('linux_v58/log/class_3a__' . sha1('x') . '/sandbox.jsonl.php', 'linux_v58/log/*.jsonl.php'), 'DAT58-03: vzor retence odpovídá skutečné cestě logu');

    // -----------------------------------------------------------------
    // ops58_health() vrací očekávané klíče (ve stejném procesu, na ostré STORAGE_DIR
    // jen ke čtení – health je čistě read-only a nic nemění).
    // -----------------------------------------------------------------
    $health = ops58_health();
    foreach (['storage_dir', 'total_size_bytes', 'file_count', 'top_files', 'lab_event_count', 'race_file_count', 'last_backup', 'free_disk_bytes', 'php_version', 'extensions', 'school_year'] as $key) {
        opsaudit_check(array_key_exists($key, $health), "ops58_health() obsahuje klíč '$key'");
    }

    // -----------------------------------------------------------------
    // CLI guardy
    // -----------------------------------------------------------------
    foreach (['backup_storage.php', 'restore_storage.php', 'run_audits.php', 'v58_smoke_audit.php', 'v58_retention.php', 'v58_ops_audit.php'] as $tool) {
        $path = __DIR__ . '/' . $tool;
        $contents = (string)file_get_contents($path);
        opsaudit_check(str_contains($contents, "PHP_SAPI !== 'cli'"), "$tool má CLI guard");
    }
    $httpGuardContents = (string)file_get_contents(__DIR__ . '/lib/http_harness.php');
    opsaudit_check(str_contains($httpGuardContents, 'basename((string)($_SERVER'), 'lib/http_harness.php má guard proti přímému volání');

    // -----------------------------------------------------------------
    // run_audits.php --only=v57 na malé sadě (na ostré storage/, ale run_audits.php si
    // sám dělá dočasné kopie a nic nezapisuje zpět).
    // -----------------------------------------------------------------
    $runAudits = __DIR__ . '/run_audits.php';
    $before = md5(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(STORAGE_DIR, FilesystemIterator::SKIP_DOTS)))));
    $out = opsaudit_run_subprocess($phpBinary, $runAudits, ['--only=v57', '--timeout=60'], []);
    $after = md5(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(STORAGE_DIR, FilesystemIterator::SKIP_DOTS)))));
    opsaudit_check(str_contains($out, 'RUN_AUDITS_OK'), 'run_audits.php --only=v57 hlásí RUN_AUDITS_OK: ' . trim($out));
    opsaudit_check($before === $after, 'run_audits.php --only=v57 nezměnil ostrou storage/ (souborový listing beze změny)');
} catch (Throwable $e) {
    opsaudit_check(false, 'Výjimka během ops auditu: ' . $e->getMessage());
} finally {
    $cleanup();
}

function opsaudit_run_subprocess(string $phpBinary, string $script, array $args, array $env): string
{
    $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $fullEnv = [];
    foreach ($_SERVER as $k => $v) {
        if (is_scalar($v)) {
            $fullEnv[$k] = (string)$v;
        }
    }
    foreach ($env as $k => $v) {
        $fullEnv[$k] = (string)$v;
    }
    $cmd = escapeshellarg($phpBinary) . ' ' . escapeshellarg($script);
    foreach ($args as $a) {
        $cmd .= ' ' . escapeshellarg($a);
    }
    $process = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__), $fullEnv, ['bypass_shell' => true]);
    if (!is_resource($process)) {
        return '';
    }
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
    return ($out ?: '') . ($err ?: '');
}

function ops58_school_year_for_archive(): string
{
    return str_replace('/', '-', ops58_school_year());
}

if ($failed === 0) {
    echo "V58_OPS_AUDIT_OK checks={$checks} failed=0\n";
    exit(0);
}
echo "V58_OPS_AUDIT_FAILED checks={$checks} failed={$failed}\n";
exit(1);
