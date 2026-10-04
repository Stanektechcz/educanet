<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Provoz (OPS-03) – zdraví úložiště, poslední záloha, retenční politika.
 *
 * Čistě datová vrstva (žádný HTML výstup – ten je v ops_v58_views.php).
 * Vše čte jen z existující storage/ a z backup adresáře (manifesty); nic nespouští,
 * nikam se nepřipojuje.
 */

require_once __DIR__ . '/tools/backup_storage.php';

/** Aktuální školní rok jako "2025/2026" (školní rok začíná v září). */
function ops58_school_year(?int $timestamp = null): string
{
    $timestamp = $timestamp ?? time();
    $year = (int)date('Y', $timestamp);
    $month = (int)date('n', $timestamp);
    return $month >= 9 ? ($year . '/' . ($year + 1)) : (($year - 1) . '/' . $year);
}

function ops58_backup_dir(): string
{
    $env = trim((string)getenv('EDUCANET_BACKUP_DIR'));
    return $env !== '' ? $env : dirname(__DIR__) . '/educanet-backups';
}

/**
 * Zdraví úložiště: velikost, počet souborů, top 15 podle velikosti, počty labových/
 * arénových událostí, poslední záloha (z manifestů), volné místo na disku, PHP verze
 * a dostupnost rozšíření.
 */
function ops58_health(): array
{
    $storageDir = STORAGE_DIR;
    $files = ops58_rlist($storageDir);
    $totalSize = 0;
    $sized = [];
    foreach ($files as $rel) {
        $full = $storageDir . '/' . $rel;
        $size = is_file($full) ? (int)filesize($full) : 0;
        $totalSize += $size;
        $sized[] = ['path' => $rel, 'size' => $size];
    }
    usort($sized, static fn(array $a, array $b): int => $b['size'] <=> $a['size']);
    $top15 = array_slice($sized, 0, 15);

    $labEventsFile = $storageDir . '/lab_v57_events.json.php';
    $labEvents = is_file($labEventsFile) ? load_php_json($labEventsFile) : [];
    $labEventCount = is_array($labEvents) ? count($labEvents) : 0;

    $raceCount = 0;
    $raceDir = $storageDir . '/linux_v57';
    if (is_dir($raceDir)) {
        foreach (glob($raceDir . '/race_*') ?: [] as $raceFile) {
            $raceCount++;
        }
    }

    $lastBackup = ops58_last_backup();
    $freeBytes = @disk_free_space($storageDir);

    return [
        'storage_dir' => $storageDir,
        'total_size_bytes' => $totalSize,
        'file_count' => count($files),
        'top_files' => $top15,
        'lab_event_count' => $labEventCount,
        'race_file_count' => $raceCount,
        'last_backup' => $lastBackup,
        'free_disk_bytes' => $freeBytes === false ? null : (int)$freeBytes,
        'php_version' => PHP_VERSION,
        'extensions' => [
            'sodium' => extension_loaded('sodium'),
            'openssl' => extension_loaded('openssl'),
            'zip' => extension_loaded('zip'),
            'intl' => extension_loaded('intl'),
        ],
        'school_year' => ops58_school_year(),
        'generated_at' => date(DATE_ATOM),
    ];
}

function ops58_rlist(string $dir, string $base = ''): array
{
    $out = [];
    if (!is_dir($dir)) {
        return $out;
    }
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $full = $dir . '/' . $item;
        $rel = $base === '' ? $item : $base . '/' . $item;
        if (is_dir($full)) {
            $out = array_merge($out, ops58_rlist($full, $rel));
        } else {
            $out[] = $rel;
        }
    }
    return $out;
}

/** Poslední záloha podle manifestů v EDUCANET_BACKUP_DIR (nebo null, pokud žádná není). */
function ops58_last_backup(): ?array
{
    $dir = ops58_backup_dir();
    $dirs = glob(rtrim($dir, '/\\') . '/storage-*', GLOB_ONLYDIR) ?: [];
    // v61: šifrované archivy storage-….edubak (stejné časové razítko v názvu jako u adresářů).
    $archives = glob(rtrim($dir, '/\\') . '/storage-*.edubak') ?: [];
    $all = array_merge($dirs, $archives);
    if (!$all) {
        return null;
    }
    usort($all, static fn(string $a, string $b): int => strcmp(basename($a), basename($b)));
    $latest = end($all);
    if (str_ends_with($latest, '.edubak')) {
        return ['path' => $latest, 'created_at' => date(DATE_ATOM, (int)filemtime($latest)), 'file_count' => null,
            'manifest_ok' => is_file($latest . '.sha256'), 'encrypted' => true];
    }
    $manifestPath = $latest . '/manifest.json';
    if (!is_file($manifestPath)) {
        return ['path' => $latest, 'created_at' => null, 'file_count' => null, 'manifest_ok' => false];
    }
    $raw = file_get_contents($manifestPath);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        return ['path' => $latest, 'created_at' => null, 'file_count' => null, 'manifest_ok' => false];
    }
    return [
        'path' => $latest,
        'created_at' => (string)($data['created_at'] ?? ''),
        'file_count' => count((array)($data['files'] ?? [])),
        'manifest_ok' => true,
    ];
}

/**
 * Deklarativní retenční politika: vzor relativní cesty ve storage/ → pravidlo.
 * 'max_age_days' – po kolika dnech od poslední úpravy souboru se má obsah archivovat/
 * pročistit; 'action' – 'archive_school_year' (přesune celý soubor do
 * storage/archive/<školní rok>/) nebo 'trim_entries' (u JSON mapy smaže staré záznamy
 * podle 'age_field', ale soubor samotný nechá na místě přes storage_update()).
 */
function ops58_retention_policy(): array
{
    return [
        [
            'pattern' => 'linux_v57/race_*.events.json.php',
            'label' => 'Události závodů Arény po skončení školního roku',
            'action' => 'archive_school_year',
            'max_age_days' => null, // řídí se koncem školního roku (červen), ne stářím
        ],
        // v58 · DAT58-03: logy příkazů labu (storage/linux_v58/log/<třída>__<sha1>/<úroveň>.jsonl.php)
        // jsou data nezletilých bez hodnoty archivu → po 30 dnech bez změny se MAŽOU (ne archivují).
        // tools/v58_retention.php --apply navíc volá lab58_log_purge() (uklidí i meta.json.php a prázdné složky).
        [
            'pattern' => 'linux_v58/log/*.jsonl.php',
            'label' => 'Logy příkazů Linux Labu v58 (mažou se po 30 dnech)',
            'action' => 'delete_after_days',
            'max_age_days' => 30,
        ],
        [
            'pattern' => 'rate_limits.json.php',
            'label' => 'Rate-limit záznamy (přihlašovací pokusy apod.)',
            'action' => 'trim_entries',
            'max_age_days' => 1,
        ],
        // v58 (F2, DAT-03): měsíční proudy JSONL storage/<proud>/<YYYY-MM>.jsonl.php – vše jen archivací
        // (přesun celého uzavřeného měsíce do storage/archive/<školní rok>/), nic se nemaže.
        // Výsledky a odevzdané práce jsou podklad hodnocení → drží se celý školní rok.
        ['pattern' => 'practice_results/*.jsonl.php', 'label' => 'Výsledky testů (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'lab_results/*.jsonl.php', 'label' => 'Výsledky laboratoří (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'extra_submissions/*.jsonl.php', 'label' => 'Odevzdané extra úkoly (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'graphics_submissions/*.jsonl.php', 'label' => 'Odevzdané grafické práce (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'special_exam_results/*.jsonl.php', 'label' => 'Prestižní zkoušky (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'skill_attempts/*.jsonl.php', 'label' => 'Pokusy mastery challenge (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'skill_audit/*.jsonl.php', 'label' => 'Audit stromu dovedností (měsíční proud) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        ['pattern' => 'adaptive_v50_hands_on_events/*.jsonl.php', 'label' => 'Události praktických labů v50 (obnovují stav labu) po skončení školního roku', 'action' => 'archive_school_year', 'max_age_days' => null],
        // Učební události a logy pokusů slouží jen k průběžné diagnostice → kratší lhůty.
        ['pattern' => 'adaptive_ml_events/*.jsonl.php', 'label' => 'Učební události mastery v41 (log pokusů)', 'action' => 'archive_after_days', 'max_age_days' => 365],
        ['pattern' => 'adaptive_retrieval_attempts/*.jsonl.php', 'label' => 'Pokusy opakování (log pokusů)', 'action' => 'archive_after_days', 'max_age_days' => 365],
        ['pattern' => 'adaptive_help_events/*.jsonl.php', 'label' => 'Události nápovědy (log)', 'action' => 'archive_after_days', 'max_age_days' => 180],
        ['pattern' => 'adaptive_v505_events/*.jsonl.php', 'label' => 'Události „jeden úkol“ v50.5 (log)', 'action' => 'archive_after_days', 'max_age_days' => 180],
        // v62 · důkazy kompetencí (storage/evidence_v62/<student_id>.json.php): drží se do konce studia, 30 dní po stavu
        // left/archived se MAŽOU. Rozhodnutí podle stavu žáka (ne podle stáří souboru) dělá ev62_retention_purge(), kterou
        // volá tools/v58_retention.php – ops58_apply_retention() tuto akci přeskakuje (jen ji ukazuje v náhledu politiky).
        ['pattern' => 'evidence_v62/*.json.php', 'label' => 'Důkazy kompetencí v62 (mažou se 30 dní po odchodu žáka)', 'action' => 'student_left', 'max_age_days' => 30],
        // Záznamy provedených migrací a manifest schémat se nearchivují (potřebné pro tools/migrate.php).
    ];
}

function ops58_matches_pattern(string $relPath, string $pattern): bool
{
    $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
    return preg_match($regex, $relPath) === 1;
}

/**
 * Vyhodnotí a (pokud $dryRun===false) provede retenční politiku. Archivované soubory se
 * přesunou do storage/archive/<školní rok>/, "trim_entries" akce upravují obsah přes
 * storage_update() (odstranění starých položek z JSON mapy, ne smazání souboru).
 * Výjimka: "delete_after_days" (jen logy příkazů labu – data nezletilých) soubor po lhůtě smaže.
 *
 * Vrací ['dry_run'=>bool,'actions'=>array<int,array{pattern,label,path,action,detail}>].
 */
function ops58_apply_retention(bool $dryRun): array
{
    $storageDir = STORAGE_DIR;
    $policies = ops58_retention_policy();
    $allFiles = ops58_rlist($storageDir);
    $actions = [];
    $schoolYear = str_replace('/', '-', ops58_school_year());
    $now = time();

    foreach ($policies as $policy) {
        foreach ($allFiles as $rel) {
            if (str_starts_with($rel, 'archive/')) {
                continue;
            }
            if (!ops58_matches_pattern($rel, (string)$policy['pattern'])) {
                continue;
            }
            $full = $storageDir . '/' . $rel;
            $mtime = @filemtime($full) ?: 0;
            $ageDays = $mtime > 0 ? (int)floor(($now - $mtime) / 86400) : 0;

            if ($policy['action'] === 'archive_school_year') {
                // Archivuje jen soubory starší než ~10 měsíců (tj. z předchozích školních roků),
                // aby se aktivní sezóna neschovávala do archivu.
                if ($ageDays < 300) {
                    continue;
                }
                $target = $storageDir . '/archive/' . $schoolYear . '/' . $rel;
                $actions[] = ['pattern' => $policy['pattern'], 'label' => $policy['label'], 'path' => $rel, 'action' => 'archive', 'detail' => 'age_days=' . $ageDays . ' -> archive/' . $schoolYear . '/' . $rel];
                if (!$dryRun) {
                    ops58_move_to_archive($full, $target);
                }
            } elseif ($policy['action'] === 'archive_after_days') {
                $maxAge = (int)$policy['max_age_days'];
                if ($ageDays < $maxAge) {
                    continue;
                }
                $target = $storageDir . '/archive/' . $schoolYear . '/' . $rel;
                $actions[] = ['pattern' => $policy['pattern'], 'label' => $policy['label'], 'path' => $rel, 'action' => 'archive', 'detail' => 'age_days=' . $ageDays . ' -> archive/' . $schoolYear . '/' . $rel];
                if (!$dryRun) {
                    ops58_move_to_archive($full, $target);
                }
            } elseif ($policy['action'] === 'delete_after_days') {
                $maxAge = (int)$policy['max_age_days'];
                if ($ageDays < $maxAge) {
                    continue;
                }
                $actions[] = ['pattern' => $policy['pattern'], 'label' => $policy['label'], 'path' => $rel, 'action' => 'delete', 'detail' => 'age_days=' . $ageDays . ' -> smazáno (max_age_days=' . $maxAge . ')'];
                if (!$dryRun && is_file($full) && !@unlink($full)) {
                    throw new RuntimeException('Nelze smazat ' . $rel . '.');
                }
            } elseif ($policy['action'] === 'trim_entries') {
                $maxAge = (int)$policy['max_age_days'];
                $trimmed = ops58_trim_entries_preview($full, $maxAge);
                if ($trimmed > 0) {
                    $actions[] = ['pattern' => $policy['pattern'], 'label' => $policy['label'], 'path' => $rel, 'action' => 'trim', 'detail' => $trimmed . ' starých položek k odstranění (max_age_days=' . $maxAge . ')'];
                    if (!$dryRun) {
                        ops58_trim_entries_apply($full, $maxAge);
                    }
                }
            }
        }
    }

    return ['dry_run' => $dryRun, 'actions' => $actions, 'school_year' => ops58_school_year(), 'checked_at' => date(DATE_ATOM)];
}

function ops58_move_to_archive(string $from, string $to): void
{
    if (!is_file($from)) {
        return;
    }
    $dir = dirname($to);
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
        throw new RuntimeException("Nelze vytvořit archivní adresář $dir.");
    }
    if (!rename($from, $to)) {
        // rename může selhat přes disky/FS hranice – zkopíruj a smaž originál až po ověření.
        if (!copy($from, $to)) {
            throw new RuntimeException("Nelze archivovat $from.");
        }
        if (hash_file('sha256', $from) !== hash_file('sha256', $to)) {
            @unlink($to);
            throw new RuntimeException("Archivace $from selhala na ověření obsahu.");
        }
        @unlink($from);
    }
    if (function_exists('php_json_cache_forget')) {
        php_json_cache_forget($from);
    }
}

/**
 * Kolik položek by "trim_entries" odstranil. Podporuje dva tvary map, jak se v projektu
 * skutečně vyskytují:
 *  - rate_limits.json.php: bucket => seznam číselných unix timestampů pokusů,
 *  - obecná evidence: id => pole s polem 'ts'/'time'/'created_at'/'at'.
 */
function ops58_trim_entries_preview(string $path, int $maxAgeDays): int
{
    if (!is_file($path)) {
        return 0;
    }
    $data = load_php_json($path);
    if (!is_array($data)) {
        return 0;
    }
    $cutoff = time() - ($maxAgeDays * 86400);
    $count = 0;
    foreach ($data as $entry) {
        if (ops58_is_timestamp_list($entry)) {
            foreach ($entry as $ts) {
                if ((int)$ts < $cutoff) {
                    $count++;
                }
            }
            continue;
        }
        if (ops58_entry_is_old($entry, $cutoff)) {
            $count++;
        }
    }
    return $count;
}

function ops58_trim_entries_apply(string $path, int $maxAgeDays): void
{
    if (!function_exists('storage_update')) {
        return;
    }
    $cutoff = time() - ($maxAgeDays * 86400);
    storage_update($path, static function (array $data) use ($cutoff): array {
        foreach ($data as $key => $entry) {
            if (ops58_is_timestamp_list($entry)) {
                $kept = array_values(array_filter($entry, static fn($ts): bool => (int)$ts >= $cutoff));
                if ($kept === []) {
                    unset($data[$key]);
                } else {
                    $data[$key] = $kept;
                }
                continue;
            }
            if (ops58_entry_is_old($entry, $cutoff)) {
                unset($data[$key]);
            }
        }
        return $data;
    });
}

/** Rozezná bucket rate limitu (pole čistě číselných unix timestampů) od jiných struktur. */
function ops58_is_timestamp_list($entry): bool
{
    if (!is_array($entry) || $entry === []) {
        return false;
    }
    foreach ($entry as $v) {
        if (!is_numeric($v)) {
            return false;
        }
    }
    return true;
}

function ops58_entry_is_old($entry, int $cutoff): bool
{
    if (!is_array($entry)) {
        return false;
    }
    foreach (['ts', 'time', 'created_at', 'at'] as $field) {
        if (isset($entry[$field])) {
            $value = $entry[$field];
            $ts = is_numeric($value) ? (int)$value : (int)strtotime((string)$value);
            return $ts > 0 && $ts < $cutoff;
        }
    }
    return false;
}
