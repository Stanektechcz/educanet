<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v67 · profil žáka: příběh růstu, cíle a sdílení (fáze G).
 *
 * Časová osa je odvozená, jen čtená a cachovaná: milníky kompetencí (v62: první chvíle, kdy stav dosáhl „zvládnuto“
 * a „upevněno“, přehráním důkazů), dokončené výukové cesty (v63), ohodnocené projekty (v65) a odznaky za kompetence (v64).
 * Cache storage/growth_v67/tl_<student_id>.json.php se přepíše jen při změně otisku zdrojů (v režimu jen pro čtení nikdy).
 *
 * Cíle: nejvýš GROW67_MAX_GOALS na žáka, vždy navázané na kompetenci z katalogu a s cílovým stavem; žádný volný text
 * (soukromí nezletilých). Týdenní kontrola zapíše aktuální stav kompetence (jedna na týden). Úložiště
 * storage/growth_v67/<student_id>.json.php (jen přes storage_update); smazání se stejnou retencí jako důkazy v62 (ev62_delete_student_files v evidence_v62.php maže i tyto soubory).
 *
 * Sdílení: spolužáci vidí jen to, co žák sám zapnul (výchozí je nic) – „Umím“ z mapy kompetencí a časovou osu bez
 * projektů. Cíle se nesdílejí nikdy. Škola může sdílení vypnout proměnnou EDUCANET_GROWTH_PUBLIC=0.
 */

const GROW67_VERSION = 1;
const GROW67_MAX_GOALS = 3;
const GROW67_TARGETS = ['zvladnuto', 'upevneno'];
const GROW67_TIMELINE_MAX = 40;
const GROW67_CHECKS_KEEP = 26;
const GROW67_GOAL_ID_RE = '/^g_[0-9a-f]{8}$/';
const GROW67_RANK = ['neovereno' => 0, 'rozpracovano' => 1, 'zvladnuto' => 2, 'upevneno' => 3];

function grow67_dir(): string
{
    return STORAGE_DIR . '/growth_v67';
}

function grow67_path(string $studentId): string
{
    if (!ev62_valid_id($studentId)) throw new InvalidArgumentException('Neplatné student_id.');
    return grow67_dir() . '/' . $studentId . '.json.php';
}

function grow67_cache_path(string $studentId): string
{
    if (!ev62_valid_id($studentId)) throw new InvalidArgumentException('Neplatné student_id.');
    return grow67_dir() . '/tl_' . $studentId . '.json.php';
}

/** Vrstva běží jen v pilotních třídách kompetencí (bez nich nemá cíle na co navázat). */
function grow67_enabled(string $classId): bool
{
    return function_exists('comp62_enabled_for_class') && comp62_enabled_for_class($classId) && comp62_subject_for_class($classId) !== null;
}

/** Sdílení se spolužáky může škola vypnout (EDUCANET_GROWTH_PUBLIC=0). */
function grow67_public_allowed(): bool
{
    return getenv('EDUCANET_GROWTH_PUBLIC') !== '0';
}

function grow67_student_id(string $classId, string $studentKey): ?string
{
    $ctx = function_exists('ev62_student_context') ? ev62_student_context($classId, $studentKey) : null;
    return $ctx === null ? null : (string)$ctx['id'];
}

/** @return array{v:int,goals:list<array<string,mixed>>,public:array{timeline:bool,competencies:bool}} */
function grow67_normalize(array $raw): array
{
    $goals = [];
    foreach ((array)($raw['goals'] ?? []) as $g) {
        if (!is_array($g) || preg_match(GROW67_GOAL_ID_RE, (string)($g['id'] ?? '')) !== 1 || preg_match(EV62_COMP_RE, (string)($g['competency'] ?? '')) !== 1) continue;
        if (!in_array((string)($g['target'] ?? ''), GROW67_TARGETS, true)) continue;
        $goals[] = ['id' => (string)$g['id'], 'competency' => (string)$g['competency'], 'target' => (string)$g['target'],
            'created_at' => (string)($g['created_at'] ?? ''), 'reached_at' => is_string($g['reached_at'] ?? null) ? $g['reached_at'] : null,
            'checks' => array_slice(array_values(array_filter((array)($g['checks'] ?? []), 'is_array')), -GROW67_CHECKS_KEEP)];
    }
    $pub = is_array($raw['public'] ?? null) ? $raw['public'] : [];
    return ['v' => GROW67_VERSION, 'goals' => array_slice($goals, 0, GROW67_MAX_GOALS), 'public' => ['timeline' => !empty($pub['timeline']), 'competencies' => !empty($pub['competencies'])]];
}

function grow67_state(?string $studentId): array
{
    return grow67_normalize($studentId !== null ? storage_read(grow67_path($studentId), false) : []);
}

/** Týden ve tvaru 2026-W41 (ISO). */
function grow67_week_key(int $ts): string
{
    return date('o-\WW', $ts);
}

function grow67_rank(string $state): int
{
    return GROW67_RANK[$state] ?? 0;
}

/** Aktuální stav kompetence žáka (m62), nebo 'neovereno'. */
function grow67_current_state(string $classId, string $studentKey, string $competency): string
{
    return (string)(m62_student($classId, $studentKey)['map'][$competency]['state'] ?? 'neovereno');
}

/** @return array{ok:bool,error:?string} chyby: identity, unknown_competency, bad_target, max, duplicate */
function grow67_goal_add(string $classId, string $studentKey, string $competency, string $target, ?int $now = null): array
{
    $subject = comp62_subject_for_class($classId);
    $studentId = grow67_student_id($classId, $studentKey);
    if (!grow67_enabled($classId) || $studentId === null || $subject === null) return ['ok' => false, 'error' => 'identity'];
    if (!isset(comp62_competencies($subject)[$competency])) return ['ok' => false, 'error' => 'unknown_competency'];
    if (!in_array($target, GROW67_TARGETS, true)) return ['ok' => false, 'error' => 'bad_target'];
    $out = ['ok' => true, 'error' => null];
    $at = date(DATE_ATOM, $now ?? time());
    storage_update(grow67_path($studentId), static function (array $d) use ($competency, $target, $at, &$out): array {
        $state = grow67_normalize($d);
        foreach ($state['goals'] as $g) {
            if ($g['competency'] === $competency) { $out = ['ok' => false, 'error' => 'duplicate']; return $d; }
        }
        if (count($state['goals']) >= GROW67_MAX_GOALS) { $out = ['ok' => false, 'error' => 'max']; return $d; }
        $state['goals'][] = ['id' => 'g_' . bin2hex(random_bytes(4)), 'competency' => $competency, 'target' => $target, 'created_at' => $at, 'reached_at' => null, 'checks' => []];
        return $state;
    });
    return $out;
}

/** Týdenní kontrola: zapíše aktuální stav kompetence (jedna za týden); při dosažení cíle zapíše čas. @return array{ok:bool,error:?string,state:?string} */
function grow67_goal_check(string $classId, string $studentKey, string $goalId, ?int $now = null): array
{
    $studentId = grow67_student_id($classId, $studentKey);
    if (!grow67_enabled($classId) || $studentId === null || preg_match(GROW67_GOAL_ID_RE, $goalId) !== 1) return ['ok' => false, 'error' => 'identity', 'state' => null];
    $now ??= time();
    $out = ['ok' => false, 'error' => 'not_found', 'state' => null];
    $goals = grow67_state($studentId)['goals'];
    $competency = null;
    foreach ($goals as $g) if ($g['id'] === $goalId) $competency = $g['competency'];
    if ($competency === null) return $out;
    $current = grow67_current_state($classId, $studentKey, $competency);
    $week = grow67_week_key($now);
    storage_update(grow67_path($studentId), static function (array $d) use ($goalId, $current, $week, $now, &$out): array {
        $state = grow67_normalize($d);
        foreach ($state['goals'] as $i => $g) {
            if ($g['id'] !== $goalId) continue;
            $out = ['ok' => true, 'error' => null, 'state' => $current];
            foreach ($g['checks'] as $c) if ((string)($c['week'] ?? '') === $week) return $d;
            $g['checks'][] = ['week' => $week, 'state' => $current, 'at' => date(DATE_ATOM, $now)];
            $g['checks'] = array_slice($g['checks'], -GROW67_CHECKS_KEEP);
            if ($g['reached_at'] === null && grow67_rank($current) >= grow67_rank($g['target'])) $g['reached_at'] = date(DATE_ATOM, $now);
            $state['goals'][$i] = $g;
            return $state;
        }
        return $d;
    });
    return $out;
}

function grow67_goal_remove(string $classId, string $studentKey, string $goalId): array
{
    $studentId = grow67_student_id($classId, $studentKey);
    if (!grow67_enabled($classId) || $studentId === null || preg_match(GROW67_GOAL_ID_RE, $goalId) !== 1) return ['ok' => false, 'error' => 'identity'];
    $found = false;
    storage_update(grow67_path($studentId), static function (array $d) use ($goalId, &$found): array {
        $state = grow67_normalize($d);
        $kept = array_values(array_filter($state['goals'], static fn(array $g): bool => $g['id'] !== $goalId));
        $found = count($kept) !== count($state['goals']);
        if (!$found) return $d;
        $state['goals'] = $kept;
        return $state;
    });
    return ['ok' => $found, 'error' => $found ? null : 'not_found'];
}

/** Zapne/vypne sdílení se spolužáky (výchozí vypnuto). */
function grow67_share_set(string $classId, string $studentKey, bool $timeline, bool $competencies): array
{
    $studentId = grow67_student_id($classId, $studentKey);
    if (!grow67_enabled($classId) || $studentId === null || !grow67_public_allowed()) return ['ok' => false, 'error' => 'identity'];
    storage_update(grow67_path($studentId), static function (array $d) use ($timeline, $competencies): array {
        $state = grow67_normalize($d);
        $state['public'] = ['timeline' => $timeline, 'competencies' => $competencies];
        return $state;
    });
    return ['ok' => true, 'error' => null];
}

/** Co o sobě žák ukazuje spolužákům (cizí profil). Bez souhlasu/identity/povolení vždy nic. @return array{timeline:bool,competencies:bool} */
function grow67_public_flags(string $classId, string $studentKey): array
{
    $none = ['timeline' => false, 'competencies' => false];
    if (!grow67_enabled($classId) || !grow67_public_allowed()) return $none;
    $studentId = grow67_student_id($classId, $studentKey);
    return $studentId === null ? $none : grow67_state($studentId)['public'];
}

/**
 * Milníky kompetencí z důkazů: kdy poprvé stav dosáhl „zvládnuto“ a „upevněno“ (přehrání důkazů v čase). Čistá funkce.
 * @param list<array<string,mixed>> $rows důkazy žáka
 * @param list<string> $competencyIds
 * @return list<array{at:int,type:string,ref:string,state:string}>
 */
function grow67_competency_milestones(array $rows, array $competencyIds): array
{
    $by = [];
    foreach ($rows as $r) {
        if (is_array($r) && strtotime((string)($r['at'] ?? '')) !== false) $by[(string)($r['competency'] ?? '')][] = $r;
    }
    $events = [];
    foreach ($competencyIds as $id) {
        $list = $by[$id] ?? [];
        usort($list, static fn(array $a, array $b): int => strtotime((string)$a['at']) <=> strtotime((string)$b['at']) ?: strcmp((string)($a['k'] ?? ''), (string)($b['k'] ?? '')));
        $reached = [];
        foreach ($list as $i => $row) {
            $ts = (int)strtotime((string)$row['at']);
            $rank = grow67_rank((string)m62_state_for(array_slice($list, 0, $i + 1), $ts)['state']);
            foreach (['zvladnuto' => 2, 'upevneno' => 3] as $state => $need) {
                if ($rank >= $need && !isset($reached[$state])) { $reached[$state] = true; $events[] = ['at' => $ts, 'type' => 'competency', 'ref' => (string)$id, 'state' => $state]; }
            }
        }
    }
    return $events;
}

/** Dokončené cesty (v63) a ohodnocené projekty (v65) jako události. @return list<array{at:int,type:string,ref:string,state:string}> */
function grow67_path_project_events(string $classId, string $studentKey, ?string $studentId): array
{
    $events = [];
    if ($studentId !== null && function_exists('p63_paths_for_class')) {
        $state = p63_state($studentId);
        foreach (p63_paths_for_class($classId) as $pathId => $path) {
            if (!p63_path_progress($path, $state)['finished']) continue;
            $at = 0;
            foreach (p63_step_ids($path) as $stepId) $at = max($at, p63_step_entry($state, (string)$pathId, $stepId)['at']);
            if ($at > 0) $events[] = ['at' => $at, 'type' => 'path', 'ref' => (string)$pathId, 'state' => ''];
        }
    }
    if (function_exists('proj65_for_student')) {
        foreach (proj65_for_student($classId, $studentKey) as $row) {
            $versions = array_values(array_filter((array)($row['versions'] ?? []), 'is_array'));
            if ($versions === [] || !in_array((string)($row['state'] ?? ''), ['graded', 'portfolio'], true)) continue;
            $at = (int)strtotime((string)($versions[count($versions) - 1]['published_at'] ?? ''));
            if ($at > 0) $events[] = ['at' => $at, 'type' => 'project', 'ref' => (string)($row['id'] ?? ''), 'state' => ''];
        }
    }
    return $events;
}

/** Otisk zdrojů časové osy (jen stat souborů). */
function grow67_timeline_signature(string $studentId): string
{
    $parts = [storage_signature(ev62_path($studentId)), storage_signature(p63_state_path($studentId)), storage_signature(proj65_path('cycle')),
        storage_signature(ch64_path()), storage_signature(STORAGE_DIR . '/project_groups.json.php'), comp62_catalog_version(), p63_catalog_version(), 'g' . GROW67_VERSION];
    return sha1(implode('|', array_map('strval', $parts)));
}

/**
 * Časová osa žáka (nejnovější první, nejvýš GROW67_TIMELINE_MAX). Jen čtení + cache; odznak = žák má odznak za milník (v64).
 * @return list<array{at:int,type:string,ref:string,state:string,badge:bool}>
 */
function grow67_timeline(string $classId, string $studentKey): array
{
    $subject = comp62_subject_for_class($classId);
    $studentId = grow67_student_id($classId, $studentKey);
    if ($subject === null || $studentId === null) return [];
    $sig = grow67_timeline_signature($studentId);
    $cache = storage_read(grow67_cache_path($studentId), false);
    if (($cache['sig'] ?? '') === $sig && is_array($cache['events'] ?? null)) return $cache['events'];
    $events = array_merge(grow67_competency_milestones(ev62_read($studentId), array_keys(comp62_competencies($subject))), grow67_path_project_events($classId, $studentKey, $studentId));
    $badges = function_exists('ch64_badges_of') ? ch64_badges_of($studentId) : [];
    foreach ($events as $i => $e) {
        $events[$i]['badge'] = $e['type'] === 'competency' && isset($badges[ch64_badge_id($e['ref'], $e['state'])]);
    }
    usort($events, static fn(array $a, array $b): int => $b['at'] <=> $a['at'] ?: strcmp($a['ref'], $b['ref']));
    $events = array_slice($events, 0, GROW67_TIMELINE_MAX);
    if (!storage_readonly()) storage_update(grow67_cache_path($studentId), static fn(array $d): array => ['sig' => $sig, 'events' => $events]);
    return $events;
}
