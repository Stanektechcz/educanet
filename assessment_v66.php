<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/question_meta_v66.php';

/**
 * EDUCANET v66 · testy: druh testu (formativní / sumativní), normalizace pokusů a položková analýza.
 *
 * Druh testu určuje učitel třídy (storage/assessment_v66/test_kinds.json.php); výchozí je formativní. Startovní test
 * (id "start") je vždy formativní. Do návrhu hodnocení (grading_v66.php) jdou jen sumativní testy.
 * Pokusy (a66_attempts) se jen ČTOU z existujících úložišť (practice_results, progress_v56, stav cest v63) – žádný zdroj
 * se nemění. Položková analýza (a66_item_stats) je čistá funkce: obtížnost p, korigovaná bodově-biseriální korelace
 * (položka vs. součet ostatních), diskriminace D (horních a dolních 27 %), slabé distraktory (< 5 % výběrů nebo kladná
 * korelace s výsledkem). Pod A66_MIN_ATTEMPTS pokusů je výsledek jen „málo dat“. Soubor nikdy nepočítá odměny.
 */

const A66_VERSION = 1;
const A66_MIN_ATTEMPTS = 20;
const A66_P_HARD = 0.30;
const A66_P_EASY = 0.90;
const A66_RPB_MIN = 0.20;
const A66_DISTRACTOR_MIN_SHARE = 0.05;
const A66_GROUP_FRACTION = 0.27;
const A66_KINDS = ['formative', 'summative'];
const A66_TEST_RE = '/^(start|v56:l([1-9]|1\d|2[0-8])|p63:[a-z][a-z0-9_]{2,39}:verify)$/';

function a66_dir(): string
{
    return STORAGE_DIR . '/assessment_v66';
}

function a66_class_safe(string $classId): string
{
    return (string)preg_replace('/[^A-Za-z0-9_]/', '', $classId);
}

function a66_kinds_path(): string
{
    return a66_dir() . '/test_kinds.json.php';
}

function a66_test_id_valid(string $testId): bool
{
    return preg_match(A66_TEST_RE, $testId) === 1;
}

/** Popisek testu pro cockpit. */
function a66_test_label(string $testId): string
{
    if ($testId === 'start') return 'Startovní test';
    if (preg_match('/^v56:l(\d+)$/', $testId, $m) === 1) return 'Test lekce ' . (int)$m[1];
    if (preg_match('/^p63:([a-z0-9_]+):verify$/', $testId, $m) === 1) return 'Ověření cesty ' . $m[1];
    return $testId;
}

/** @return array<string,array{kind:string,at:int,by:string}> druhy testů třídy (jen nastavené) */
function a66_kinds(string $classId): array
{
    $rows = storage_read(a66_kinds_path(), false)[$classId] ?? [];
    $out = [];
    foreach (is_array($rows) ? $rows : [] as $testId => $row) {
        if (is_array($row) && a66_test_id_valid((string)$testId) && in_array($row['kind'] ?? '', A66_KINDS, true)) {
            $out[(string)$testId] = ['kind' => (string)$row['kind'], 'at' => (int)($row['at'] ?? 0), 'by' => (string)($row['by'] ?? '')];
        }
    }
    return $out;
}

/** Druh testu; neznámý nebo startovní test = formativní. */
function a66_test_kind(string $classId, string $testId): string
{
    if ($testId === 'start' || !a66_test_id_valid($testId)) return 'formative';
    return a66_kinds($classId)[$testId]['kind'] ?? 'formative';
}

function a66_is_summative(string $classId, string $testId): bool
{
    return a66_test_kind($classId, $testId) === 'summative';
}

/** Nastaví druh testu (třída a oprávnění ověřuje volající). Startovní test nelze označit sumativně. */
function a66_set_kind(string $classId, string $testId, string $kind, string $actorHash, ?int $now = null): bool
{
    if ($classId === '' || $testId === 'start' || !a66_test_id_valid($testId) || !in_array($kind, A66_KINDS, true) || preg_match('/^[a-f0-9]{16}$/', $actorHash) !== 1) return false;
    $now ??= time();
    storage_update(a66_kinds_path(), static function (array $data) use ($classId, $testId, $kind, $actorHash, $now): array {
        if ($kind === 'formative') unset($data[$classId][$testId]);
        else $data[$classId][$testId] = ['kind' => $kind, 'at' => $now, 'by' => $actorHash];
        if (isset($data[$classId]) && $data[$classId] === []) unset($data[$classId]);
        return $data;
    });
    return true;
}

/** Hash učitele pro auditní stopy (stejný tvar jako v63). */
function a66_actor_hash(): string
{
    $key = function_exists('teacher59_owner_key') ? (string)(teacher59_owner_key() ?? 'legacy') : 'legacy';
    return substr(hash('sha256', 'a66|' . $key), 0, 16);
}

/** Testy třídy, které lze označit: testy lekcí v56 a ověření cest v63 (startovní test je vždy formativní). @return list<array{id:string,label:string,kind:string}> */
function a66_markable_tests(string $classId): array
{
    $ids = [];
    $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : null;
    if ($module !== null) {
        for ($n = 1; $n <= 28; $n++) $ids[] = 'v56:l' . $n;
    }
    if (function_exists('p63_paths_for_class')) {
        foreach (array_keys(p63_paths_for_class($classId)) as $pathId) $ids[] = 'p63:' . $pathId . ':verify';
    }
    $out = [];
    foreach ($ids as $id) {
        if (a66_test_id_valid($id)) $out[] = ['id' => $id, 'label' => a66_test_label($id), 'kind' => a66_test_kind($classId, $id)];
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Pokusy (jen čtení)
// ---------------------------------------------------------------------------

/** Mapy žáků třídy pro přiřazení historických řádků: jméno/e-mail/klíč → student_id. @return array{label:array<string,string>,email:array<string,string>,key:array<string,string>} */
function a66_roster_map(string $classId): array
{
    $map = ['label' => [], 'email' => [], 'key' => []];
    if (!function_exists('project_students_for_class') || !function_exists('identity58_id_for_student')) return $map;
    foreach (project_students_for_class($classId) as $key => $student) {
        $id = identity58_id_for_student($classId, (string)$student['label']);
        if ($id === null) continue;
        $map['label'][normalized_person_name((string)$student['label'])] = $id;
        $mail = strtolower(trim((string)($student['email'] ?? '')));
        if ($mail !== '') $map['email'][$mail] = $id;
        $map['key'][(string)$key] = $id;
    }
    return $map;
}

/** student_id řádku startovního testu: uložené pole, jinak podle jména nebo e-mailu; bez shody neprůhledný hash (jen pro seskupení). */
function a66_row_sid(array $row, array $roster): string
{
    $stored = (string)($row['student_id'] ?? '');
    if (preg_match('/^stu_[0-9a-f]{16}$/', $stored) === 1) return $stored;
    $label = normalized_person_name((string)($row['student_label'] ?? ''));
    if ($label !== '' && isset($roster['label'][$label])) return $roster['label'][$label];
    $mail = strtolower(trim((string)($row['student_email'] ?? '')));
    if ($mail !== '' && isset($roster['email'][$mail])) return $roster['email'][$mail];
    return 'anon_' . substr(sha1($label . '|' . $mail), 0, 12);
}

/** @return list<array<string,mixed>> pokusy startovního testu */
function a66_attempts_practice(string $classId, array $roster): array
{
    $out = [];
    foreach (storage_stream_rows('practice_results', static fn(array $r): bool => (string)($r['class_id'] ?? '') === $classId) as $row) {
        $items = [];
        foreach ((array)($row['answers'] ?? []) as $a) {
            if (!is_array($a) || !is_string($a['question_id'] ?? null)) continue;
            $items[] = ['item' => 'mod:' . $a['question_id'], 'ok' => !empty($a['correct']), 'sel' => is_string($a['selected'] ?? null) ? $a['selected'] : null];
        }
        $start = strtotime((string)($row['started_at'] ?? '')) ?: 0;
        $end = strtotime((string)($row['finished_at'] ?? '')) ?: 0;
        $dur = is_int($row['duration_s'] ?? null) ? (int)$row['duration_s'] : ($start > 0 && $end >= $start ? $end - $start : null);
        $max = max(1, (int)($row['max_score'] ?? count($items)));
        $out[] = ['test' => 'start', 'src' => 'practice', 'sid' => a66_row_sid($row, $roster), 'at' => $end, 'dur' => $dur, 'score' => min(1.0, (int)($row['score'] ?? 0) / $max), 'items' => $items];
    }
    return $out;
}

/** @return list<array<string,mixed>> poslední pokusy testů lekcí v56 (zdroj ukládá jen poslední pokus) */
function a66_attempts_v56(string $classId, array $roster): array
{
    $out = [];
    foreach (storage_read(STORAGE_DIR . '/progress_v56.json.php', false) as $key => $lessons) {
        if (!is_array($lessons) || !str_starts_with((string)$key, $classId . '|')) continue;
        $sid = $roster['key'][substr((string)$key, strlen($classId) + 1)] ?? '';
        if ($sid === '') continue;
        foreach ($lessons as $no => $row) {
            $test = is_array($row['test'] ?? null) ? $row['test'] : [];
            if ((string)($test['at'] ?? '') === '' || !is_array($test['detail'] ?? null)) continue;
            $items = [];
            foreach ($test['detail'] as $d) {
                if (is_array($d) && is_string($d['id'] ?? null)) $items[] = ['item' => 'mod:' . $d['id'], 'ok' => !empty($d['ok']), 'sel' => is_string($d['given'] ?? null) && $d['given'] !== '' ? $d['given'] : null];
            }
            $out[] = ['test' => 'v56:l' . (int)$no, 'src' => 'v56', 'sid' => $sid, 'at' => strtotime((string)$test['at']) ?: 0, 'dur' => null,
                'score' => max(0.0, min(1.0, (float)($test['percent'] ?? 0) / 100)), 'items' => $items];
        }
    }
    return $out;
}

/** @return list<array<string,mixed>> ověření cest v63 (stav drží jen nejlepší skóre kroku, položky nejsou k dispozici) */
function a66_attempts_p63(string $classId, array $roster): array
{
    $out = [];
    if (!function_exists('p63_state') || !function_exists('p63_paths_for_class')) return $out;
    foreach (array_unique(array_values($roster['key'])) as $sid) {
        $state = p63_state((string)$sid);
        foreach (array_keys(p63_paths_for_class($classId)) as $pathId) {
            $entry = p63_step_entry($state, (string)$pathId, 'verify');
            if ($entry['attempts'] < 1) continue;
            $out[] = ['test' => 'p63:' . $pathId . ':verify', 'src' => 'p63', 'sid' => (string)$sid, 'at' => $entry['at'], 'dur' => null, 'score' => max(0.0, min(1.0, $entry['best'])), 'items' => []];
        }
    }
    return $out;
}

/** Všechny pokusy třídy normalizované do jednoho tvaru (jen čtení). @return list<array<string,mixed>> */
function a66_attempts(string $classId): array
{
    $roster = a66_roster_map($classId);
    return array_merge(a66_attempts_practice($classId, $roster), a66_attempts_v56($classId, $roster), a66_attempts_p63($classId, $roster));
}

/** První pokus každého žáka v každém testu (položková analýza nesmí počítat opakování). @return list<array<string,mixed>> */
function a66_first_attempts(array $attempts): array
{
    $first = [];
    foreach ($attempts as $a) {
        $k = (string)$a['test'] . '|' . (string)$a['sid'];
        if (!isset($first[$k]) || ((int)$a['at'] > 0 && (int)$a['at'] < (int)$first[$k]['at'])) $first[$k] = $a;
    }
    return array_values($first);
}

// ---------------------------------------------------------------------------
// Položková analýza (čistá)
// ---------------------------------------------------------------------------

/** Pearsonova korelace; null při nulovém rozptylu nebo méně než 2 hodnotách. @param list<float|int> $x @param list<float|int> $y */
function a66_pearson(array $x, array $y): ?float
{
    $n = count($x);
    if ($n < 2 || $n !== count($y)) return null;
    $mx = array_sum($x) / $n;
    $my = array_sum($y) / $n;
    $sxy = 0.0;
    $sxx = 0.0;
    $syy = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $dx = $x[$i] - $mx;
        $dy = $y[$i] - $my;
        $sxy += $dx * $dy;
        $sxx += $dx * $dx;
        $syy += $dy * $dy;
    }
    return $sxx <= 1e-12 || $syy <= 1e-12 ? null : $sxy / sqrt($sxx * $syy);
}

/** @param list<array{ok:bool,total:int}> $rows @return float|null rozdíl úspěšnosti horních a dolních 27 % */
function a66_discrimination(array $rows): ?float
{
    $n = count($rows);
    if ($n < 2) return null;
    usort($rows, static fn(array $a, array $b): int => $b['total'] <=> $a['total']);
    $k = max(1, (int)round($n * A66_GROUP_FRACTION));
    $rate = static fn(array $part): float => array_sum(array_map(static fn(array $r): int => $r['ok'] ? 1 : 0, $part)) / count($part);
    return $rate(array_slice($rows, 0, $k)) - $rate(array_slice($rows, -$k));
}

/** Slabé distraktory jedné položky. @param list<array{sel:?string,rest:int}> $rows @return list<array<string,mixed>> */
function a66_distractors(array $rows, array $spec): array
{
    $out = [];
    $n = count($rows);
    foreach ((array)($spec['keys'] ?? []) as $key) {
        $key = (string)$key;
        if ($key === (string)($spec['correct'] ?? '')) continue;
        $chosen = array_map(static fn(array $r): int => $r['sel'] === $key ? 1 : 0, $rows);
        $share = $n > 0 ? array_sum($chosen) / $n : 0.0;
        $r = a66_pearson($chosen, array_column($rows, 'rest'));
        $out[] = ['key' => $key, 'share' => round($share, 3), 'r' => $r === null ? null : round($r, 3), 'weak' => $share < A66_DISTRACTOR_MIN_SHARE || ($r !== null && $r > 0.0)];
    }
    return $out;
}

/** Statistika jedné položky z řádků {ok, sel, total, rest}. */
function a66_one_item(string $item, array $rows, array $spec): array
{
    $n = count($rows);
    if ($n < A66_MIN_ATTEMPTS) return ['item' => $item, 'n' => $n, 'status' => 'low_data'];
    $p = array_sum(array_map(static fn(array $r): int => $r['ok'] ? 1 : 0, $rows)) / $n;
    $r = a66_pearson(array_map(static fn(array $x): int => $x['ok'] ? 1 : 0, $rows), array_column($rows, 'rest'));
    $d = a66_discrimination($rows);
    $dist = $spec === [] ? [] : a66_distractors($rows, $spec);
    $flags = [];
    if ($p < A66_P_HARD) $flags[] = 'hard';
    if ($p > A66_P_EASY) $flags[] = 'easy';
    if ($r === null || $r < A66_RPB_MIN) $flags[] = 'low_discrimination';
    if (array_filter($dist, static fn(array $x): bool => $x['weak']) !== []) $flags[] = 'weak_distractor';
    return ['item' => $item, 'n' => $n, 'status' => 'ok', 'p' => round($p, 3), 'r_pb' => $r === null ? null : round($r, 3), 'd' => $d === null ? null : round($d, 3), 'distractors' => $dist, 'flags' => $flags];
}

/**
 * Položková analýza jednoho testu. $attempts = [['items' => [['item','ok','sel'], …]], …] (jeden pokus na žáka),
 * $options = [položka => ['correct' => klíč, 'keys' => [klíče]]] (nepovinné; bez něj se distraktory nehodnotí).
 * @return array<string,array<string,mixed>> položka => statistika
 */
function a66_item_stats(array $attempts, array $options = []): array
{
    $byItem = [];
    foreach ($attempts as $attempt) {
        $items = array_values(array_filter((array)($attempt['items'] ?? []), 'is_array'));
        $total = count(array_filter($items, static fn(array $i): bool => !empty($i['ok'])));
        foreach ($items as $i) {
            $ok = !empty($i['ok']);
            $byItem[(string)$i['item']][] = ['ok' => $ok, 'sel' => is_string($i['sel'] ?? null) ? $i['sel'] : null, 'total' => $total, 'rest' => $total - ($ok ? 1 : 0)];
        }
    }
    $out = [];
    foreach ($byItem as $item => $rows) $out[(string)$item] = a66_one_item((string)$item, $rows, (array)($options[$item] ?? []));
    ksort($out);
    return $out;
}

/** Položková analýza po testech. @return array<string,array<string,mixed>> test => ['n' => počet pokusů, 'items' => …] */
function a66_item_stats_by_test(array $attempts, array $options = []): array
{
    $groups = [];
    foreach (a66_first_attempts($attempts) as $a) $groups[(string)$a['test']][] = $a;
    ksort($groups);
    $out = [];
    foreach ($groups as $test => $rows) $out[$test] = ['n' => count($rows), 'items' => a66_item_stats($rows, $options)];
    return $out;
}

/** Možnosti otázek modulu třídy pro analýzu distraktorů: mod:<id> => ['correct','keys']. @return array<string,array{correct:string,keys:list<string>}> */
function a66_item_options(string $classId): array
{
    $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
    $out = [];
    foreach ((array)($module['questions'] ?? []) as $q) {
        if (is_array($q) && is_string($q['id'] ?? null) && is_array($q['options'] ?? null)) {
            $out['mod:' . $q['id']] = ['correct' => (string)($q['correct'] ?? ''), 'keys' => array_map('strval', array_keys($q['options']))];
        }
    }
    return $out;
}

function a66_items_path(string $classId): string
{
    return a66_dir() . '/items_' . a66_class_safe($classId) . '.json.php';
}

/** Spočítá analýzu třídy a zapíše cache (bez jmen). @return array<string,mixed> */
function a66_items_build(string $classId, ?int $now = null, ?array $attempts = null): array
{
    $now ??= time();
    $stats = a66_item_stats_by_test($attempts ?? a66_attempts($classId), a66_item_options($classId));
    $tests = [];
    foreach ($stats as $test => $row) {
        $items = [];
        foreach ($row['items'] as $ref => $s) {
            $meta = q66_meta($classId, (string)$ref);
            $items[$ref] = $s + ['competency' => $meta['competency'], 'difficulty' => $meta['difficulty']];
        }
        $tests[$test] = ['n' => (int)$row['n'], 'kind' => a66_test_kind($classId, (string)$test), 'items' => $items];
    }
    $result = ['v' => A66_VERSION, 'class' => $classId, 'built_at' => $now, 'tests' => $tests];
    storage_update(a66_items_path($classId), static fn(array $d): array => $result);
    return $result;
}

/** Cache analýzy (jen čtení); prázdné pole = ještě nepřipraveno. @return array<string,mixed> */
function a66_items_cache(string $classId): array
{
    $data = storage_read(a66_items_path($classId), false);
    return is_array($data['tests'] ?? null) ? $data : [];
}
