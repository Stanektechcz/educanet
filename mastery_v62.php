<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · výpočet zvládnutí kompetencí z důkazů.
 *
 * Čistá funkce m62_compute($rows, $now): posledních M62_LAST_N důkazů kompetence, váha zdroje × časový útlum
 * (poločas M62_HALF_LIFE_DAYS), vážený průměr skóre. Stavy:
 *   neovereno    žádný důkaz
 *   rozpracovano má důkazy, ale nesplňuje podmínky zvládnutí
 *   zvladnuto    skóre ≥ 0,70, součet vah (bez útlumu) ≥ 1 a aspoň jeden důkaz, který není hra
 *   upevneno     zvládnuto + dva různé typy zdrojů se skóre ≥ 0,6 s odstupem ≥ 7 dní (aspoň jeden z nich není hra)
 * Hra ani aréna samy zvládnutí ani upevnění nikdy nedají. Hodnoty rozhodla škola (konstanty níže).
 * Cache m62_student/m62_class se zapisuje do storage/mastery_v62/ (odvoditelná, kdykoli smazatelná).
 */

const M62_THRESHOLD = 0.70;
const M62_HALF_LIFE_DAYS = 60;
const M62_LAST_N = 8;
const M62_MIN_WEIGHT = 1.0;
const M62_CONSOLIDATE_SCORE = 0.6;
const M62_CONSOLIDATE_GAP_DAYS = 7;
const M62_WEIGHTS = ['test' => 1.0, 'project' => 1.0, 'lab' => 0.7, 'game' => 0.4, 'arena' => 0.4, 'lesson' => 0.3];
const M62_GAME_SOURCES = ['game', 'arena'];
const M62_STATES = ['neovereno', 'rozpracovano', 'zvladnuto', 'upevneno'];

function m62_weight(string $source): float
{
    return M62_WEIGHTS[$source] ?? 0.0;
}

function m62_is_game(string $source): bool
{
    return in_array($source, M62_GAME_SOURCES, true);
}

/** Stav → text pro cockpit (žákovský text řeší view přes tr()). */
function m62_state_label(string $state): string
{
    return ['neovereno' => 'neověřeno', 'rozpracovano' => 'rozpracováno', 'zvladnuto' => 'zvládnuto', 'upevneno' => 'upevněno'][$state] ?? 'neověřeno';
}

/**
 * v65: projekt publikovaný ve více verzích (artefact_ref proj:v65_<hash12>_v<N>) se pro stejný základ a kompetenci počítá jen nejvyšší verzí.
 * Ostatní řádky (jiné zdroje, starší typy referencí) zůstávají beze změny.
 * @return list<array<string,mixed>>
 */
function m62_latest_versions(array $rows): array
{
    $best = [];
    foreach ($rows as $i => $r) {
        if (!is_array($r) || preg_match('/^(proj:v65_[0-9a-f]{12})_v(\d+)$/', (string)($r['artefact_ref'] ?? ''), $m) !== 1) continue;
        $base = $m[1] . '|' . (string)($r['competency'] ?? '');
        if (!isset($best[$base]) || (int)$m[2] > $best[$base][1]) $best[$base] = [$i, (int)$m[2]];
    }
    $keep = array_column($best, 0);
    return array_values(array_filter($rows, static fn($r, $i): bool => !is_array($r) || preg_match('/^proj:v65_[0-9a-f]{12}_v\d+$/', (string)($r['artefact_ref'] ?? '')) !== 1 || in_array($i, $keep, true), ARRAY_FILTER_USE_BOTH));
}

/** Posledních M62_LAST_N důkazů (nejnovější první, shoda času rozhodne klíč → deterministické). @return list<array<string,mixed>> */
function m62_recent(array $rows): array
{
    $rows = m62_latest_versions($rows);
    $items = [];
    foreach ($rows as $r) {
        $ts = strtotime((string)($r['at'] ?? ''));
        if ($ts === false || m62_weight((string)($r['source'] ?? '')) <= 0.0) continue;
        $items[] = ['ts' => $ts, 'k' => (string)($r['k'] ?? ''), 'source' => (string)$r['source'], 'score' => (float)($r['score'] ?? 0)];
    }
    usort($items, static fn(array $a, array $b): int => $b['ts'] <=> $a['ts'] ?: strcmp($a['k'], $b['k']));
    return array_slice($items, 0, M62_LAST_N);
}

/** Existuje dvojice důkazů ze dvou různých typů zdrojů, skóre ≥ 0,6, odstup ≥ 7 dní a aspoň jeden není hra? */
function m62_consolidated(array $items): bool
{
    $good = array_values(array_filter($items, static fn(array $i): bool => $i['score'] >= M62_CONSOLIDATE_SCORE));
    $gap = M62_CONSOLIDATE_GAP_DAYS * 86400;
    foreach ($good as $i => $a) {
        foreach (array_slice($good, $i + 1) as $b) {
            if ($a['source'] !== $b['source'] && abs($a['ts'] - $b['ts']) >= $gap && !(m62_is_game($a['source']) && m62_is_game($b['source']))) return true;
        }
    }
    return false;
}

/** Zvládnutí jedné kompetence z jejích důkazů. */
function m62_state_for(array $rows, int $now): array
{
    $items = m62_recent($rows);
    if ($items === []) return ['state' => 'neovereno', 'score' => null, 'weight' => 0.0, 'n' => 0, 'sources' => [], 'last_at' => null];
    $num = 0.0;
    $den = 0.0;
    $raw = 0.0;
    $nonGame = false;
    $sources = [];
    foreach ($items as $i) {
        $w = m62_weight($i['source']);
        $decay = 0.5 ** (max(0, $now - $i['ts']) / 86400 / M62_HALF_LIFE_DAYS);
        $num += $w * $decay * $i['score'];
        $den += $w * $decay;
        $raw += $w;
        $nonGame = $nonGame || !m62_is_game($i['source']);
        $sources[$i['source']] = true;
    }
    $score = $den > 0 ? $num / $den : 0.0;
    $state = 'rozpracovano';
    if ($score >= M62_THRESHOLD && $raw >= M62_MIN_WEIGHT && $nonGame) $state = m62_consolidated($items) ? 'upevneno' : 'zvladnuto';
    $names = array_keys($sources);
    sort($names);
    return ['state' => $state, 'score' => round($score, 3), 'weight' => round($raw, 2), 'n' => count($items), 'sources' => $names, 'last_at' => date(DATE_ATOM, (int)$items[0]['ts'])];
}

/**
 * Zvládnutí všech zadaných kompetencí (i bez důkazů). Čistá funkce – žádné čtení ani zápis.
 * @param list<array<string,mixed>> $rows důkazy žáka
 * @param list<string> $competencyIds
 * @return array<string,array<string,mixed>>
 */
function m62_compute(array $rows, int $now, array $competencyIds): array
{
    $by = [];
    foreach ($rows as $r) if (is_array($r)) $by[(string)($r['competency'] ?? '')][] = $r;
    $out = [];
    foreach ($competencyIds as $id) $out[(string)$id] = m62_state_for($by[(string)$id] ?? [], $now);
    return $out;
}

function m62_cache_path(string $name): string
{
    return STORAGE_DIR . '/mastery_v62/' . preg_replace('/[^A-Za-z0-9_]/', '', $name) . '.json.php';
}

function m62_signature(string $evidenceSig, int $now): string
{
    return sha1($evidenceSig . '|' . comp62_catalog_version() . '|' . gmdate('Y-m-d', $now));
}

/** Zvládnutí žáka s cache (platnost = stejné důkazy, katalog a den, protože útlum závisí na čase). */
function m62_student(string $classId, string $studentKey, ?int $now = null): array
{
    $now ??= time();
    $subject = comp62_subject_for_class($classId);
    $ids = $subject !== null ? array_keys(comp62_competencies($subject)) : [];
    $student = project_students_for_class($classId)[$studentKey] ?? null;
    $studentId = is_array($student) ? identity58_id_for_student($classId, (string)$student['label']) : null;
    if ($studentId === null || !ev62_valid_id($studentId)) return ['id' => null, 'map' => m62_compute([], $now, $ids)];
    $sig = m62_signature((string)storage_signature(ev62_path($studentId)), $now);
    $cache = storage_read(m62_cache_path($studentId), false);
    if (($cache['sig'] ?? '') === $sig && is_array($cache['map'] ?? null)) return ['id' => $studentId, 'map' => $cache['map']];
    $map = m62_compute(ev62_read($studentId), $now, $ids);
    if (!storage_readonly()) storage_update(m62_cache_path($studentId), static fn(array $d): array => ['sig' => $sig, 'map' => $map]);
    return ['id' => $studentId, 'map' => $map];
}

/**
 * Zvládnutí celé třídy: ['students' => [id => map], 'summary' => [kompetence => [stav => počet]], 'sig' => …].
 * Cache _class_<třída> se platí podle podpisu souborů důkazů; bez jmen (jen student_id).
 */
function m62_class(string $classId, ?int $now = null): array
{
    $now ??= time();
    $subject = comp62_subject_for_class($classId);
    $ids = $subject !== null ? array_keys(comp62_competencies($subject)) : [];
    $studentIds = [];
    foreach (project_students_for_class($classId) as $s) {
        $id = identity58_id_for_student($classId, (string)$s['label']);
        if ($id !== null && ev62_valid_id($id)) $studentIds[$id] = true;
    }
    ksort($studentIds);
    $parts = [];
    foreach (array_keys($studentIds) as $id) $parts[] = $id . ':' . (string)storage_signature(ev62_path($id));
    $sig = m62_signature(sha1(implode('|', $parts)), $now);
    $cache = storage_read(m62_cache_path('_class_' . $classId), false);
    if (($cache['sig'] ?? '') === $sig && is_array($cache['students'] ?? null)) return $cache;
    $students = [];
    foreach (array_keys($studentIds) as $id) $students[$id] = m62_compute(ev62_read($id), $now, $ids);
    $result = ['sig' => $sig, 'students' => $students, 'summary' => m62_summary($students, $ids)];
    if (!storage_readonly()) storage_update(m62_cache_path('_class_' . $classId), static fn(array $d): array => $result);
    return $result;
}

/** @param array<string,array<string,array<string,mixed>>> $students @param list<string> $ids */
function m62_summary(array $students, array $ids): array
{
    $summary = [];
    foreach ($ids as $id) {
        $summary[$id] = array_fill_keys(M62_STATES, 0);
        foreach ($students as $map) $summary[$id][(string)($map[$id]['state'] ?? 'neovereno')]++;
    }
    return $summary;
}
