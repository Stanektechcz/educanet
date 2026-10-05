<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/migrate.php (DAT-07)
 *
 * Spouští migrace úložiště z migrations/NNNN_nazev.php (každá vrací ['id','description','files','up'=>callable(bool $dryRun): array]).
 *
 * Použití:
 *   php tools/migrate.php [--dry-run]                 výchozí: jen vypíše, co by se změnilo (nic nezapíše)
 *   php tools/migrate.php --apply [--backup-now]      provede čekající migrace; vyžaduje zálohu storage/ mladší než 1 h
 *                                                     (--backup-now ji nejdřív vytvoří přes tools/backup_storage.php)
 *   --only=0001_streams_jsonl                         jen vybraná migrace
 *   --backup-dir=<adresář>                            kde hledat/vytvářet zálohy (výchozí EDUCANET_BACKUP_DIR nebo ../educanet-backups)
 *   --status                                          vypíše provedené migrace a verze schémat
 *   --rollback=<id> [--apply]                         v67: vrácení jedné provedené migrace. Má-li migrace funkci down(bool $dryRun), spustí ji
 *                                                     (výchozí náhled; --apply vyžaduje zálohu mladší než 1 h a smaže záznam z logu migrací).
 *                                                     Bez down() vypíše přesný příkaz obnovy ze zálohy pořízené před migrací (nic nemění).
 *
 * Provedené migrace: storage/migrations_v58.json.php; verze schémat: storage/_schema_v58.json.php.
 * Konec: MIGRATE_OK pending=N applied=M dry_run=0|1, při chybě MIGRATE_FAIL (exit 1).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/backup_storage.php';

const MIGRATE_BACKUP_MAX_AGE = 3600;

function migrate_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    return $default;
}

function migrate_log_path(): string
{
    return STORAGE_DIR . '/migrations_v58.json.php';
}

/** @return array<string,array> id → definice, seřazené podle id */
function migrate_load_all(string $dir): array
{
    $out = [];
    foreach (glob($dir . '/[0-9][0-9][0-9][0-9]_*.php') ?: [] as $file) {
        $def = require $file;
        if (!is_array($def) || !isset($def['id'], $def['up']) || !is_callable($def['up'])) throw new RuntimeException('Neplatná migrace ' . basename($file));
        if ($def['id'] . '.php' !== basename($file)) throw new RuntimeException('Id migrace neodpovídá souboru ' . basename($file));
        $out[(string)$def['id']] = $def;
    }
    ksort($out, SORT_STRING);
    return $out;
}

function migrate_backup_dir(array $argv): string
{
    $env = rtrim((string)getenv('EDUCANET_BACKUP_DIR'), '/\\');
    return (string)migrate_arg($argv, 'backup-dir', $env !== '' ? $env : dirname(dirname(__DIR__)) . '/educanet-backups');
}

/** Nejnovější záloha této storage mladší než limit (podle manifest.json), nebo null. */
function migrate_fresh_backup(string $backupDir, string $storageDir, int $maxAge = MIGRATE_BACKUP_MAX_AGE): ?string
{
    $want = realpath($storageDir) ?: $storageDir;
    $dirs = glob(rtrim($backupDir, '/\\') . '/storage-*', GLOB_ONLYDIR) ?: [];
    rsort($dirs, SORT_STRING);
    foreach ($dirs as $dir) {
        $manifest = json_decode((string)@file_get_contents($dir . '/manifest.json'), true);
        if (!is_array($manifest)) continue;
        $created = strtotime((string)($manifest['created_at'] ?? '')) ?: 0;
        if (time() - $created > $maxAge) continue;
        $src = (string)($manifest['source'] ?? '');
        if ((realpath($src) ?: $src) !== $want) continue;
        return $dir;
    }
    return null;
}

/**
 * v67: plán vrácení migrace. Vrací ['mode'=>'down'|'restore'|'unknown'|'not_applied', 'command'=>?string].
 * 'restore' = migrace nemá down(); command je příkaz obnovy z nejnovější zálohy, která vznikla PŘED provedením migrace.
 */
function migrate_rollback_plan(array $migrations, string $id, string $backupDir, string $storageDir): array
{
    if (!isset($migrations[$id])) return ['mode' => 'unknown', 'command' => null];
    $done = storage_read(migrate_log_path());
    if (!isset($done[$id])) return ['mode' => 'not_applied', 'command' => null];
    if (isset($migrations[$id]['down']) && is_callable($migrations[$id]['down'])) return ['mode' => 'down', 'command' => null];
    $appliedAt = strtotime((string)($done[$id]['applied_at'] ?? '')) ?: 0;
    $want = realpath($storageDir) ?: $storageDir;
    $dirs = glob(rtrim($backupDir, '/\\') . '/storage-*', GLOB_ONLYDIR) ?: [];
    rsort($dirs, SORT_STRING);
    foreach ($dirs as $dir) {
        $manifest = json_decode((string)@file_get_contents($dir . '/manifest.json'), true);
        if (!is_array($manifest)) continue;
        $created = strtotime((string)($manifest['created_at'] ?? '')) ?: 0;
        $src = (string)($manifest['source'] ?? '');
        if ($created > 0 && $created <= $appliedAt && (realpath($src) ?: $src) === $want) return ['mode' => 'restore', 'command' => 'php tools/restore_storage.php --from=' . $dir . ' --apply'];
    }
    return ['mode' => 'restore', 'command' => null];
}

/**
 * Spustí migrace. $dryRun = true nic nezapisuje. Vrací ['pending'=>[], 'applied'=>[], 'reports'=>[id=>report]].
 * Použitelné i z auditu (bez výstupu).
 */
function migrate_run(array $migrations, bool $dryRun, ?string $only = null): array
{
    $done = storage_read(migrate_log_path());
    $result = ['pending' => [], 'applied' => [], 'reports' => []];
    foreach ($migrations as $id => $def) {
        if ($only !== null && $id !== $only) continue;
        if (isset($done[$id])) continue;
        $result['pending'][] = $id;
        $report = ($def['up'])($dryRun);
        $result['reports'][$id] = is_array($report) ? $report : [];
        if ($dryRun) continue;
        storage_map_update(migrate_log_path(), $id, static fn(?array $current): array => [
            'id' => $id,
            'description' => (string)($def['description'] ?? ''),
            'applied_at' => date(DATE_ATOM),
            'report' => array_slice((array)($report['details'] ?? []), 0, 50),
        ]);
        $result['applied'][] = $id;
    }
    return $result;
}

/** CLI --rollback=<id>; vrací exit kód. */
function migrate_rollback_cli(array $migrations, string $id, bool $apply, array $argv): int
{
    $backupDir = migrate_backup_dir($argv);
    $plan = migrate_rollback_plan($migrations, $id, $backupDir, STORAGE_DIR);
    if ($plan['mode'] === 'unknown' || $plan['mode'] === 'not_applied') {
        fwrite(STDERR, 'MIGRATE_ROLLBACK_FAIL ' . ($plan['mode'] === 'unknown' ? 'neznámá migrace' : 'migrace není provedená') . "
");
        return 1;
    }
    if ($plan['mode'] === 'restore') {
        echo "Migrace nemá down(). Vrácení = obnova storage/ ze zálohy před migrací:
";
        echo '  ' . ($plan['command'] ?? 'záloha před migrací nenalezena (hledej v ' . $backupDir . ' nebo v úložišti záloh mimo server)') . "
";
        echo "MIGRATE_ROLLBACK_MANUAL id=$id
";
        return 0;
    }
    if (!$apply) {
        $report = ($migrations[$id]['down'])(true);
        foreach ((array)(is_array($report) ? ($report['details'] ?? []) : []) as $line) echo '  - ' . $line . "
";
        echo "MIGRATE_ROLLBACK_DRYRUN id=$id (přidej --apply)
";
        return 0;
    }
    if (migrate_fresh_backup($backupDir, STORAGE_DIR) === null) {
        fwrite(STDERR, "MIGRATE_ROLLBACK_FAIL Chybí záloha storage/ mladší než 1 h. Spusť: php tools/backup_storage.php
");
        return 1;
    }
    ($migrations[$id]['down'])(false);
    storage_update(migrate_log_path(), static function (array $d) use ($id): array { unset($d[$id]); return $d; });
    echo "MIGRATE_ROLLBACK_OK id=$id
";
    return 0;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    try {
        $apply = in_array('--apply', $argv, true);
        $only = migrate_arg($argv, 'only');
        $migrations = migrate_load_all(dirname(__DIR__) . '/migrations');
        if (in_array('--status', $argv, true)) {
            $done = storage_read(migrate_log_path());
            foreach ($migrations as $id => $def) echo (isset($done[$id]) ? '[x] ' : '[ ] ') . $id . ' – ' . $def['description'] . (isset($done[$id]) ? ' (' . $done[$id]['applied_at'] . ')' : '') . "\n";
            foreach (storage_schema_versions() as $name => $v) echo "schema $name = $v\n";
            exit(0);
        }
        $rollbackId = migrate_arg($argv, 'rollback');
        if ($rollbackId !== null) exit(migrate_rollback_cli($migrations, $rollbackId, $apply, $argv));
        if ($apply) {
            $backupDir = migrate_backup_dir($argv);
            if (in_array('--backup-now', $argv, true)) {
                $made = backup_storage_run(STORAGE_DIR, $backupDir, 14, false, null, true);
                echo "Záloha vytvořena: " . basename($made) . "\n";
            }
            $backup = migrate_fresh_backup($backupDir, STORAGE_DIR);
            if ($backup === null) {
                fwrite(STDERR, "MIGRATE_FAIL Chybí záloha storage/ mladší než 1 h. Spusť: php tools/backup_storage.php (nebo přidej --backup-now).\n");
                exit(1);
            }
            echo "Záloha pro migraci: " . basename($backup) . "\n";
        }
        $result = migrate_run($migrations, !$apply, $only);
        foreach ($result['reports'] as $id => $report) {
            echo ($apply ? 'APPLIED ' : 'PENDING ') . $id . "\n";
            foreach ((array)($report['details'] ?? []) as $line) echo '  - ' . $line . "\n";
        }
        if (!$result['pending']) echo "Žádné čekající migrace.\n";
        echo 'MIGRATE_OK pending=' . count($result['pending']) . ' applied=' . count($result['applied']) . ' dry_run=' . ($apply ? '0' : '1') . "\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'MIGRATE_FAIL ' . $e->getMessage() . "\n");
        exit(1);
    }
}
