<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · tools/v67_nightly_recompute.php – noční přepočet odvozených dat pilotních tříd (cron 01:30).
 *
 *   EDUCANET_NIGHTLY_ALLOW=1 php tools/v67_nightly_recompute.php [--backup-dir=<adresář>]
 *
 * Pro každého žáka pilotních tříd (kompetence v62): doplní důkazy z nových zdrojů (ev62_sync_student), obnoví cache
 * zvládnutí (m62_student), udělí odznaky za milníky (v64) a zahřeje cache časové osy profilu (v67). Vše jen přes
 * storage_update / ev62_append (idempotentní: druhý běh nic nezmění). Výstup nikdy neobsahuje jména ani e-maily, jen počty.
 *
 * Bezpečnostní brány (každá končí odmítnutím, exit 2, a nic se nezapíše):
 *   1) proměnná EDUCANET_NIGHTLY_ALLOW=1 (vědomý zápis; nástroj NEROZLIŠUJE ostrá data a kopii – zápis jde do aktuálního STORAGE_DIR),
 *   2) existuje záloha storage/ mladší než 1 hodina (adresář storage-* s manifestem, nebo šifrovaný archiv .edubak se .sha256),
 *   3) výlučný zámek storage/.nightly.lock (druhý souběžný běh se odmítne).
 * Konec: V67_NIGHTLY_OK students=N evidence_added=A mastery=M badges=B | V67_NIGHTLY_REFUSED reason=… | V67_NIGHTLY_FAIL.
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/intake_v51.php';
require_once dirname(__DIR__) . '/identity_v58.php';
foreach (['competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php', 'challenges_v64.php', 'paths_v63.php', 'projects_v60.php', 'projects_v65.php', 'growth_v67.php'] as $nightlyLib) {
    require_once dirname(__DIR__) . '/' . $nightlyLib;
}
require_once __DIR__ . '/migrate.php'; // migrate_fresh_backup(), migrate_backup_dir()

const NIGHTLY67_BACKUP_MAX_AGE = 3600;
const NIGHTLY67_STATE = 'v67_nightly.json.php';

/** Šifrovaný archiv .edubak (s .sha256) mladší než $maxAge s v adresáři záloh, nebo null. */
function nightly67_fresh_encrypted(string $dir, int $maxAge): ?string
{
    foreach (glob(rtrim($dir, '/\\') . '/storage-*.edubak') ?: [] as $file) {
        if (is_file($file . '.sha256') && time() - (int)filemtime($file) <= $maxAge) return $file;
    }
    return null;
}

/** Čerstvá záloha (adresářová přes migrate_fresh_backup, nebo šifrovaná). */
function nightly67_fresh_backup(string $backupDir, int $maxAge = NIGHTLY67_BACKUP_MAX_AGE): ?string
{
    return migrate_fresh_backup($backupDir, STORAGE_DIR, $maxAge) ?? nightly67_fresh_encrypted($backupDir, $maxAge);
}

function nightly67_refuse(string $reason): int
{
    fwrite(STDERR, "V67_NIGHTLY_REFUSED reason=$reason\n");
    return 2;
}

/** Přepočet jedné třídy. @return array{students:int,evidence_added:int,mastery:int,badges:int} */
function nightly67_class(string $classId): array
{
    $stat = ['students' => 0, 'evidence_added' => 0, 'mastery' => 0, 'badges' => 0];
    foreach (array_keys(project_students_for_class($classId)) as $key) {
        $key = (string)$key;
        if (ev62_student_context($classId, $key) === null) continue;
        $stat['students']++;
        $sync = ev62_sync_student($classId, $key);
        $stat['evidence_added'] += (int)($sync['added'] ?? 0);
        $mastery = m62_student($classId, $key);
        $stat['mastery']++;
        if ($mastery['id'] !== null) $stat['badges'] += count(ch64_award_competency_badges((string)$mastery['id'], (array)$mastery['map'], time()));
        grow67_timeline($classId, $key);
    }
    return $stat;
}

function nightly67_main(array $argv): int
{
    if (getenv('EDUCANET_NIGHTLY_ALLOW') !== '1') return nightly67_refuse('allow_env');
    $backupDir = migrate_backup_dir($argv);
    if (nightly67_fresh_backup($backupDir) === null) return nightly67_refuse('no_fresh_backup');
    $lockPath = STORAGE_DIR . '/.nightly.lock';
    $lock = @fopen($lockPath, 'c');
    if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) return nightly67_refuse('locked');
    try {
        $total = ['students' => 0, 'evidence_added' => 0, 'mastery' => 0, 'badges' => 0];
        foreach (COMP62_PILOT_CLASSES as $classId) {
            foreach (nightly67_class($classId) as $k => $v) $total[$k] += $v;
        }
        $total['at'] = date(DATE_ATOM);
        storage_update(STORAGE_DIR . '/' . NIGHTLY67_STATE, static fn(array $d): array => ['v' => 1, 'last' => $total]);
        echo 'V67_NIGHTLY_OK students=' . $total['students'] . ' evidence_added=' . $total['evidence_added'] . ' mastery=' . $total['mastery'] . ' badges=' . $total['badges'] . "\n";
        return 0;
    } catch (Throwable $e) {
        fwrite(STDERR, 'V67_NIGHTLY_FAIL ' . get_class($e) . "\n");
        return 1;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(nightly67_main($argv));
