<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/paths_v63.php';

/**
 * EDUCANET v63 · výukové cesty na úrovni třídy: přiřazení učitelem, trychtýř kroků, kalibrace a retence.
 *
 * Přiřazení: storage/paths_v63/assign.json.php {třída:{cesta:{assigned_at,assigned_by_hash,open}}}; učitel přiřazuje
 * jen v rozsahu svých tříd (vynucuje teacher_scope_v59.php), žák smí dělat i nepřiřazené cesty své třídy. Záznam se
 * navíc zrcadlí do stavů žáků třídy, aby dashboard nemusel číst další soubor.
 * Trychtýř a kalibrace: storage/paths_v63/_funnel_<třída>.json.php – jen počty a průměry BEZ jmen a bez reflexních
 * vět; kalibrace se ukládá i ukazuje až od P63_CALIBRATION_MIN reflexí (jednotlivec se nedá dohledat).
 * Retence: stavy žáků (včetně reflexních vět) se mažou 30 dní po stavu left/archived, stejně jako důkazy v62.
 */

const P63_CALIBRATION_MIN = 3;

function p63_assign_file(): string
{
    return p63_dir() . '/assign.json.php';
}

/** class_3a → „3.A“ (popisky v cockpitu). */
function p63_class_label(string $classId): string
{
    return preg_match('/^class_(\d)([a-z])$/', $classId, $m) === 1 ? $m[1] . '.' . strtoupper($m[2]) : $classId;
}

/** Přiřazené cesty třídy (jen existující v katalogu třídy): cesta => ['assigned_at','assigned_by_hash','open']. */
function p63_assignments(string $classId): array
{
    $rows = storage_read(p63_assign_file(), false)[$classId] ?? [];
    $out = [];
    foreach (is_array($rows) ? $rows : [] as $pathId => $row) {
        if (is_array($row) && p63_path_for_class($classId, (string)$pathId) !== null) $out[(string)$pathId] = $row;
    }
    return $out;
}

function p63_teacher_hash(): string
{
    $key = function_exists('teacher59_owner_key') ? (string)(teacher59_owner_key() ?? 'legacy') : 'legacy';
    return substr(hash('sha256', 'p63|' . $key), 0, 16);
}

/** @return list<string> student_id žáků třídy (podle soupisu a identity), seřazená */
function p63_roster_ids(string $classId): array
{
    $ids = [];
    foreach (project_students_for_class($classId) as $student) {
        $id = identity58_id_for_student($classId, (string)$student['label']);
        if ($id !== null && ev62_valid_id($id)) $ids[$id] = true;
    }
    $list = array_keys($ids);
    sort($list);
    return $list;
}

/** Zrcadlí (at) nebo odebere (null) přiřazení ve stavech žáků třídy; vrací počet dotčených souborů. */
function p63_mirror_assignment(string $classId, string $pathId, ?int $at): int
{
    $touched = 0;
    foreach (p63_roster_ids($classId) as $sid) {
        if ($at === null && !is_file(p63_state_path($sid))) continue;
        storage_update(p63_state_path($sid), static function (array $raw) use ($pathId, $at): array {
            $state = p63_state_normalize($raw);
            if ($at === null) unset($state['assigned'][$pathId]);
            else $state['assigned'][$pathId] = $at;
            return $state;
        });
        $touched++;
    }
    return $touched;
}

/** Přiřadí cestu třídě (cesta musí patřit třídě). Oprávnění a rozsah tříd ověřuje volající (cockpit). */
function p63_assign(string $classId, string $pathId, string $teacherHash, ?int $now = null): bool
{
    $now ??= time();
    if (p63_path_for_class($classId, $pathId) === null || preg_match('/^[a-f0-9]{16}$/', $teacherHash) !== 1) return false;
    storage_update(p63_assign_file(), static function (array $data) use ($classId, $pathId, $teacherHash, $now): array {
        $data[$classId][$pathId] = ['assigned_at' => $now, 'assigned_by_hash' => $teacherHash, 'open' => true];
        return $data;
    });
    p63_mirror_assignment($classId, $pathId, $now);
    p63_memo_reset();
    return true;
}

function p63_unassign(string $classId, string $pathId): bool
{
    if (p63_path_for_class($classId, $pathId) === null) return false;
    storage_update(p63_assign_file(), static function (array $data) use ($classId, $pathId): array {
        unset($data[$classId][$pathId]);
        if (isset($data[$classId]) && $data[$classId] === []) unset($data[$classId]);
        return $data;
    });
    p63_mirror_assignment($classId, $pathId, null);
    p63_memo_reset();
    return true;
}

// ---------------------------------------------------------------------------
// Trychtýř a kalibrace (bez jmen)
// ---------------------------------------------------------------------------

function p63_funnel_file(string $classId): string
{
    return p63_dir() . '/_funnel_' . preg_replace('/[^A-Za-z0-9_]/', '', $classId) . '.json.php';
}

/** Průměry kalibrace z dvojic [self, actual]; pod P63_CALIBRATION_MIN reflexí jen počet. @param list<array{0:int,1:int}> $pairs */
function p63_calibration_summary(array $pairs): array
{
    $n = count($pairs);
    if ($n < P63_CALIBRATION_MIN) return ['n' => $n];
    $self = array_sum(array_column($pairs, 0)) / $n;
    $actual = array_sum(array_column($pairs, 1)) / $n;
    $over = count(array_filter($pairs, static fn(array $p): bool => $p[0] - $p[1] >= 1));
    $under = count(array_filter($pairs, static fn(array $p): bool => $p[0] - $p[1] <= -1));
    return ['n' => $n, 'avg_self' => round($self, 2), 'avg_actual' => round($actual, 2), 'avg_delta' => round($self - $actual, 2), 'over' => $over, 'under' => $under, 'match' => $n - $over - $under];
}

/** Spočítá trychtýř a kalibraci třídy ze stavů žáků. @param list<string> $ids */
function p63_compute_stats(array $paths, array $ids, string $sig): array
{
    $stats = [];
    $pairsAll = [];
    foreach ($paths as $pathId => $path) {
        $steps = array_fill_keys(p63_step_ids($path), ['tried' => 0, 'done' => 0]);
        $row = ['started' => 0, 'finished' => 0, 'steps' => $steps];
        $pairs = [];
        foreach ($ids as $sid) {
            $state = p63_state($sid);
            $progress = p63_path_progress($path, $state);
            if ($progress['started']) $row['started']++;
            if ($progress['finished']) $row['finished']++;
            foreach ($steps as $stepId => $_) {
                $entry = p63_step_entry($state, (string)$pathId, (string)$stepId);
                if ($entry['attempts'] > 0) $row['steps'][$stepId]['tried']++;
                if ($entry['status'] === 'done') $row['steps'][$stepId]['done']++;
            }
            $reflect = $state['reflect'][$pathId] ?? null;
            if (is_array($reflect) && isset($reflect['self'], $reflect['mastery_at_time'])) $pairs[] = [(int)$reflect['self'], (int)$reflect['mastery_at_time']];
        }
        $row['calibration'] = p63_calibration_summary($pairs);
        $pairsAll = array_merge($pairsAll, $pairs);
        $stats[(string)$pathId] = $row;
    }
    return ['sig' => $sig, 'students' => count($ids), 'paths' => $stats, 'calibration' => p63_calibration_summary($pairsAll)];
}

/**
 * Trychtýř kroků a kalibrace třídy s cache podle podpisu souborů žáků. Zápis cache jen z cockpitu ($persist).
 * @return array{sig:string,students:int,paths:array<string,array<string,mixed>>,calibration:array<string,mixed>}
 */
function p63_class_stats(string $classId, bool $persist = true): array
{
    $paths = p63_paths_for_class($classId);
    $ids = p63_roster_ids($classId);
    $parts = array_map(static fn(string $id): string => $id . ':' . (string)storage_signature(p63_state_path($id)), $ids);
    $sig = sha1(implode('|', $parts) . '|' . p63_catalog_version() . '|' . $classId);
    $cache = storage_read(p63_funnel_file($classId), false);
    if (($cache['sig'] ?? '') === $sig && is_array($cache['paths'] ?? null)) return $cache;
    $result = p63_compute_stats($paths, $ids, $sig);
    if ($persist && !storage_readonly()) storage_update(p63_funnel_file($classId), static fn(array $d): array => $result);
    return $result;
}

function p63_funnel(string $classId, bool $persist = true): array
{
    return p63_class_stats($classId, $persist);
}

/** Agregovaná kalibrace (bez jmen). @return array{overall:array<string,mixed>,paths:array<string,array<string,mixed>>} */
function p63_calibration(string $classId, bool $persist = true): array
{
    $stats = p63_class_stats($classId, $persist);
    return ['overall' => (array)$stats['calibration'], 'paths' => array_map(static fn(array $p): array => (array)$p['calibration'], (array)$stats['paths'])];
}

// ---------------------------------------------------------------------------
// Retence
// ---------------------------------------------------------------------------

/**
 * Smaže stavy cest (včetně reflexních vět) žáků se stavem left/archived déle než P63_RETENTION_GRACE_DAYS.
 * Bez data archivace se nemaže nic. Dry-run nic nemění. Přiřazení a trychtýř (bez jmen) zůstávají.
 * @return array{files:int,purge:int,kept_unknown:int,ids:list<string>,dry_run:bool}
 */
function p63_retention_purge(bool $dryRun, ?int $now = null): array
{
    $now ??= time();
    $out = ['files' => 0, 'purge' => 0, 'kept_unknown' => 0, 'ids' => [], 'dry_run' => $dryRun];
    if (!function_exists('identity58_student')) return $out;
    foreach (glob(p63_dir() . '/stu_*.json.php') ?: [] as $file) {
        $id = basename($file, '.json.php');
        if (!ev62_valid_id($id)) continue;
        $out['files']++;
        $stu = identity58_student($id);
        if (!is_array($stu) || !in_array((string)($stu['status'] ?? 'active'), ['left', 'archived'], true)) continue;
        $since = strtotime((string)($stu['archived_at'] ?? '')) ?: 0;
        if ($since <= 0) { $out['kept_unknown']++; continue; }
        if ($now - $since < P63_RETENTION_GRACE_DAYS * 86400) continue;
        $out['purge']++;
        $out['ids'][] = $id;
        if (!$dryRun) ev62_delete_locked($file);
    }
    return $out;
}
