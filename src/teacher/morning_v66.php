<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/teacher_overview_v61.php';
require_once __DIR__ . '/assessment_v66.php';
require_once __DIR__ . '/integrity_v66.php';
require_once __DIR__ . '/grading_v66.php';
require_once __DIR__ . '/paths_v63.php';

/**
 * EDUCANET v66 · cockpit „5 minut ráno“: co má učitel udělat dnes, seřazené podle dopadu.
 *
 * Tři druhy položek:
 *   stuck     žák uvízl v kroku cesty v63 (≥ M66_STUCK_ATTEMPTS pokusů bez splnění), dopad 3 + 0,1 × pokusy,
 *   inactive  žák je M66_INACTIVE_DAYS dní bez aktivity (nebo žádná), dopad 1,5 + dny/14 (nejvýš +1,5),
 *   weak      třída nezvládá kompetenci (≥ polovina žáků s důkazy a aspoň M66_WEAK_MIN_STUDENTS žáků), dopad 1 + počet žáků.
 * Počítá se mimo požadavek (tools/v66_morning_build.php z cronu) do storage/assessment_v66/morning_<třída>.json.php; cockpit
 * jen čte tuto cache (jinak „připravuje se“). Zobrazí se nejvýš M66_SHOW_MAX položek, zbytek je pod <details>.
 * Cache a log obsahují jen zkrácené hashe žáků (posledních 24 hex z klíče žáka), žádná jména. Zásah = přiřazení cesty jednomu
 * žákovi nebo celé třídě (p63_assign_student / p63_assign); do morning_log se zapíše jen čas, druh, hash učitele a hash žáka.
 */

const M66_STUCK_ATTEMPTS = 3;
const M66_INACTIVE_DAYS = 7;
const M66_WEAK_MIN_STUDENTS = 3;
const M66_SHOW_MAX = 5;
const M66_STORE_MAX = 20;
const M66_STALE_HOURS = 48;
const M66_LOG_MAX = 500;

function m66_path(string $classId): string
{
    return a66_dir() . '/morning_' . a66_class_safe($classId) . '.json.php';
}

function m66_log_path(): string
{
    return a66_dir() . '/morning_log.json.php';
}

/** Poslední aktivita žáků třídy (hash => čas, 0 = žádná); bez kontroly rozsahu učitele (běží z cronu). @return array<string,int> */
function m66_activity_map(string $classId, array $students): array
{
    $map = [];
    foreach (array_keys($students) as $key) {
        $hash = g66_key_hash((string)$key);
        if ($hash !== '') $map[$hash] = 0;
    }
    $bump = static function (string $hash, int $ts) use (&$map): void {
        if ($ts > 0 && isset($map[$hash]) && $ts > $map[$hash]) $map[$hash] = $ts;
    };
    foreach (storage_read(learning_profiles_path(), false) as $key => $profile) {
        if (is_array($profile) && str_starts_with((string)$key, $classId . ':s:')) $bump(g66_key_hash((string)$key), ov61_profile_activity($profile));
    }
    foreach (storage_read(STORAGE_DIR . '/lab_v57_events.json.php', false) as $event) {
        if (is_array($event) && (string)($event['class_id'] ?? '') === $classId) $bump(g66_key_hash((string)($event['student_key'] ?? '')), ov61_ts($event['at'] ?? ''));
    }
    if (function_exists('teacher_class_activity_index')) {
        foreach (teacher_class_activity_index($classId) as $key => $row) $bump(g66_key_hash((string)$key), (int)($row['last_activity'] ?? 0));
    }
    return $map;
}

/** Krok cesty, ve kterém žák uvízl (nejvíc pokusů), nebo null. @return array{path:string,step:string,attempts:int}|null */
function m66_stuck_step(array $state, array $paths): ?array
{
    $best = null;
    foreach ($paths as $pathId => $path) {
        foreach (p63_step_ids($path) as $stepId) {
            $entry = p63_step_entry($state, (string)$pathId, $stepId);
            if ($entry['status'] !== 'done' && $entry['attempts'] >= M66_STUCK_ATTEMPTS && ($best === null || $entry['attempts'] > $best['attempts'])) $best = ['path' => (string)$pathId, 'step' => $stepId, 'attempts' => $entry['attempts']];
        }
    }
    return $best;
}

/** Cesta, kterou žák zatím nedokončil (první v katalogu), nebo null. */
function m66_open_path(array $state, array $paths): ?string
{
    foreach ($paths as $pathId => $path) {
        if (!p63_path_progress($path, $state)['finished']) return (string)$pathId;
    }
    return null;
}

/** @return list<array<string,mixed>> položky „uvízl“ a „neaktivní“ (žáci s p63 stavem a aktivitou) */
function m66_student_items(string $classId, int $now): array
{
    $students = project_students_for_class($classId);
    $activity = m66_activity_map($classId, $students);
    $paths = function_exists('p63_paths_for_class') ? p63_paths_for_class($classId) : [];
    $items = [];
    foreach (array_keys($students) as $key) {
        $hash = g66_key_hash((string)$key);
        $sid = $hash !== '' ? g66_student_id($classId, (string)$key) : null;
        $state = $sid !== null && $paths !== [] ? p63_state($sid) : null;
        $stuck = $state !== null ? m66_stuck_step($state, $paths) : null;
        if ($stuck !== null) {
            $items[] = ['type' => 'stuck', 'impact' => round(3 + 0.1 * $stuck['attempts'], 2), 'students' => [$hash], 'detail' => $stuck, 'action' => ['kind' => 'assign_student', 'path' => $stuck['path']]];
            continue;
        }
        $last = (int)($activity[$hash] ?? 0);
        $days = $last > 0 ? (int)floor(($now - $last) / 86400) : null;
        if ($hash === '' || ($days !== null && $days < M66_INACTIVE_DAYS)) continue;
        $open = $state !== null ? m66_open_path($state, $paths) : null;
        $items[] = ['type' => 'inactive', 'impact' => round(1.5 + min(1.5, ($days ?? 28) / 14), 2), 'students' => [$hash], 'detail' => ['days' => $days],
            'action' => $open === null ? null : ['kind' => 'assign_student', 'path' => $open]];
    }
    return $items;
}

/** @return list<array<string,mixed>> položky „třída nezvládá kompetenci“ */
function m66_weak_items(string $classId, int $now): array
{
    $subject = comp62_subject_for_class($classId);
    if ($subject === null || !comp62_enabled_for_class($classId)) return [];
    $summary = (array)(m62_class($classId, $now)['summary'] ?? []);
    $paths = function_exists('p63_paths_for_class') ? p63_paths_for_class($classId) : [];
    $items = [];
    foreach ($summary as $competency => $counts) {
        $tested = (int)$counts['rozpracovano'] + (int)$counts['zvladnuto'] + (int)$counts['upevneno'];
        $weak = (int)$counts['rozpracovano'];
        if ($weak < M66_WEAK_MIN_STUDENTS || $weak * 2 < $tested) continue;
        $path = p63_path_for_competency($paths, (string)$competency);
        $items[] = ['type' => 'weak', 'impact' => round(1 + $weak, 2), 'students' => [], 'detail' => ['competency' => (string)$competency, 'n' => $weak, 'of' => $tested],
            'action' => $path === null ? null : ['kind' => 'assign_class', 'path' => (string)$path['id']]];
    }
    return $items;
}

/** Seřazené položky (dopad sestupně, shoda rozhoduje typ a hash), nejvýš M66_STORE_MAX. @param list<array<string,mixed>> $items @return list<array<string,mixed>> */
function m66_rank(array $items): array
{
    foreach ($items as $i => $item) $items[$i]['id'] = substr(sha1($item['type'] . '|' . implode(',', $item['students']) . '|' . json_encode($item['detail'])), 0, 12);
    usort($items, static fn(array $a, array $b): int => $b['impact'] <=> $a['impact'] ?: strcmp($a['id'], $b['id']));
    return array_slice($items, 0, M66_STORE_MAX);
}

/** Sestaví ranní přehled třídy a zapíše cache. @return array<string,mixed> */
function m66_build(string $classId, ?int $now = null): array
{
    $now ??= time();
    $items = m66_rank(array_merge(m66_student_items($classId, $now), m66_weak_items($classId, $now)));
    $result = ['v' => 1, 'class' => $classId, 'built_at' => $now, 'items' => $items];
    storage_update(m66_path($classId), static fn(array $d): array => $result);
    return $result;
}

/** Cache ranního přehledu (jen čtení); prázdné pole = „připravuje se“. @return array<string,mixed> */
function m66_read(string $classId): array
{
    $data = storage_read(m66_path($classId), false);
    return is_array($data['items'] ?? null) ? $data : [];
}

function m66_is_stale(array $cache, ?int $now = null): bool
{
    return ((int)($cache['built_at'] ?? 0)) < ($now ?? time()) - M66_STALE_HOURS * 3600;
}

/** Zápis zásahu do logu: jen čas, druh, třída, hash učitele a hash žáka (žádná jména). */
function m66_log_action(string $classId, string $kind, string $actorHash, string $studentHash, string $pathId, ?int $now = null): bool
{
    if (!in_array($kind, ['assign_student', 'assign_class'], true) || preg_match('/^[a-f0-9]{16}$/', $actorHash) !== 1 || ($studentHash !== '' && preg_match('/^[a-f0-9]{24}$/', $studentHash) !== 1)) return false;
    $row = ['at' => $now ?? time(), 'class' => $classId, 'kind' => $kind, 'actor_hash' => $actorHash, 'student_hash' => $studentHash, 'path' => $pathId];
    storage_update(m66_log_path(), static function (array $data) use ($row): array {
        $data[] = $row;
        return array_slice(array_values($data), -M66_LOG_MAX);
    });
    return true;
}

/** @return list<array<string,mixed>> */
function m66_log_rows(): array
{
    return array_values(array_filter(storage_read(m66_log_path(), false), 'is_array'));
}

/** Retence logu: záznamy z minulého školního roku se po jeho skončení mažou. @return array{purge:int,dry_run:bool} */
function m66_log_purge(bool $dryRun, ?int $now = null): array
{
    $limit = i66_school_year_start($now);
    $rows = m66_log_rows();
    $keep = array_values(array_filter($rows, static fn(array $r): bool => (int)($r['at'] ?? 0) >= $limit));
    if (!$dryRun && count($keep) !== count($rows)) storage_update(m66_log_path(), static fn(array $d): array => array_values(array_filter($d, static fn($r): bool => is_array($r) && (int)($r['at'] ?? 0) >= $limit)));
    return ['purge' => count($rows) - count($keep), 'dry_run' => $dryRun];
}
