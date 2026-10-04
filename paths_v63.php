<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/competencies_v62.php';
require_once __DIR__ . '/evidence_v62.php';

/**
 * EDUCANET v63 · výukové cesty (jádro): katalog cest, stav žáka, hodnocení kroků, důkazy a doporučení „Co dál“.
 *
 * Cesta = cíl + kroky explain → pre (predikce výstupu) → parsons → retrieval → verify → reflect; každý krok má
 * kompetenci a úroveň z katalogu v62. Obsah je statický (paths_v63_content_*.php), otázky ověřování a opakování
 * se berou z banky týmových her podle id. Stav žáka: storage/paths_v63/<student_id>.json.php
 *   {v:1, paths:{cesta:{krok:{status,attempts,best,last_variant,at,verify_day,verify_count}}},
 *    reflect:{cesta:{self,mastery_at_time,note,at}}, spaced:{kompetence:{due_at,interval_d}}, assigned:{cesta:čas}}.
 * Pravidla (rozhodnutí školy): cesty nedávají XP ani body; verify nejvýš 3× denně; opakování po 1/3/7/14/30 dnech;
 * reflexní věta je jen žákova (max 200 znaků, bez značek), nikdy do důkazů, exportu, logu ani URL.
 * Důkazy jdou přes ev62_append: verify = zdroj test, ostatní hodnocené kroky = zdroj lesson, bez volného textu.
 * Čtení pro dashboard (p63_next) nezapisuje a nevolá synchronizaci důkazů ani m62_class.
 * Učitelské funkce (přiřazení, trychtýř, kalibrace, retence) jsou v paths_v63_class.php.
 */

const P63_VERSION = 1;
const P63_PASS_SCORE = 0.75;
const P63_VERIFY_DAILY_LIMIT = 3;
const P63_NOTE_MAX = 200;
/** Retence: stav cest (i reflexní věta) se maže 30 dní po stavu left/archived, jako důkazy v62. */
const P63_RETENTION_GRACE_DAYS = 30;
const P63_SPACED_INTERVALS = [1, 3, 7, 14, 30];
const P63_PATH_RE = '/^[a-z][a-z0-9_]{2,39}$/';
const P63_STEP_RE = '/^[a-z][a-z0-9_]{1,19}$/';
const P63_CLASS_FILES = ['class_3a' => 'paths_v63_content_os.php', 'class_1a' => 'paths_v63_content_gfx.php'];
const P63_STEP_TYPES = ['explain', 'pre', 'parsons', 'retrieval', 'verify', 'reflect'];
const P63_QUESTION_TYPES = ['single', 'bool', 'numeric'];
/** Kategorie banky týmových her → kompetence, ke kterým se otázka smí vázat (kontroluje audit). */
const P63_BANK_COMPETENCY = [
    'files' => ['lnx_users'], 'linux' => ['lnx_navigation'], 'dns' => ['net_dns_dhcp'],
    'color' => ['gfx_color_contrast'], 'type' => ['gfx_typography'], 'formats' => ['gfx_formats'], 'license' => ['gfx_formats'],
    'html' => ['web_html_structure'], 'css' => ['web_css_layout'], 'a11y' => ['web_a11y'], 'ux' => ['web_ux', 'gfx_composition'],
];

// ---------------------------------------------------------------------------
// Katalog cest
// ---------------------------------------------------------------------------

function p63_enabled_for_class(string $classId): bool
{
    return isset(P63_CLASS_FILES[$classId]);
}

/** Zapisují cesty třídy důkazy do v62? (třída musí být v pilotu kompetencí; jinak se jen ukládá postup) */
function p63_writes_evidence(string $classId): bool
{
    return comp62_enabled_for_class($classId) && comp62_subject_for_class($classId) !== null;
}

/** @return array<string,array<string,mixed>> cesty třídy podle id (id a class doplněny) */
function p63_paths_for_class(string $classId): array
{
    static $cache = [];
    if (!p63_enabled_for_class($classId)) return [];
    if (isset($cache[$classId])) return $cache[$classId];
    $data = require __DIR__ . '/' . P63_CLASS_FILES[$classId];
    $out = [];
    foreach ((array)$data as $id => $path) {
        if (is_array($path) && preg_match(P63_PATH_RE, (string)$id) === 1) $out[(string)$id] = ['id' => (string)$id, 'class' => $classId] + $path;
    }
    return $cache[$classId] = $out;
}

/** @return array<string,array<string,mixed>> všechny cesty všech tříd */
function p63_paths(): array
{
    $all = [];
    foreach (array_keys(P63_CLASS_FILES) as $classId) $all += p63_paths_for_class($classId);
    return $all;
}

function p63_path(string $pathId): ?array
{
    return preg_match(P63_PATH_RE, $pathId) === 1 ? (p63_paths()[$pathId] ?? null) : null;
}

/** Cesta, ale jen když patří třídě (jinak null). */
function p63_path_for_class(string $classId, string $pathId): ?array
{
    return preg_match(P63_PATH_RE, $pathId) === 1 ? (p63_paths_for_class($classId)[$pathId] ?? null) : null;
}

/** Krok cesty podle id; pseudokrok „spaced“ (opakování) má vlastní definici v cestě. */
function p63_step(array $path, string $stepId): ?array
{
    if ($stepId === 'spaced') {
        $spaced = (array)($path['spaced'] ?? []);
        return $spaced === [] ? null : ['id' => 'spaced', 'type' => 'retrieval', 'title' => 'Opakování', 'competency' => (string)$path['competency'], 'level' => 2,
            'minutes' => (int)($spaced['minutes'] ?? 3), 'count' => (int)($spaced['count'] ?? 4), 'pool' => (array)($spaced['pool'] ?? [])];
    }
    foreach ((array)($path['steps'] ?? []) as $step) {
        if (is_array($step) && (string)($step['id'] ?? '') === $stepId) return $step;
    }
    return null;
}

function p63_step_ids(array $path): array
{
    return array_values(array_map(static fn(array $s): string => (string)$s['id'], array_filter((array)($path['steps'] ?? []), 'is_array')));
}

/** Otisk obsahu cest (mění se s jakoukoli úpravou kroků) – invaliduje cache trychtýře. */
function p63_catalog_version(): string
{
    static $v = null;
    return $v ??= substr(sha1((string)json_encode(array_map(static fn(array $p): array => [$p['id'], p63_step_ids($p), $p['competency']], p63_paths()))), 0, 12);
}

// ---------------------------------------------------------------------------
// Identita a stav žáka
// ---------------------------------------------------------------------------

function p63_student_id(string $classId, string $studentKey): ?string
{
    $student = project_students_for_class($classId)[$studentKey] ?? null;
    $id = is_array($student) ? identity58_id_for_student($classId, (string)$student['label']) : null;
    return $id !== null && ev62_valid_id($id) ? $id : null;
}

function p63_dir(): string
{
    return STORAGE_DIR . '/paths_v63';
}

function p63_state_path(string $studentId): string
{
    if (!ev62_valid_id($studentId)) throw new InvalidArgumentException('Neplatné student_id.');
    return p63_dir() . '/' . $studentId . '.json.php';
}

function p63_state_normalize(array $raw): array
{
    $state = ['v' => P63_VERSION, 'paths' => [], 'reflect' => [], 'spaced' => [], 'assigned' => []];
    foreach (['paths', 'reflect', 'spaced', 'assigned'] as $key) {
        if (is_array($raw[$key] ?? null)) $state[$key] = $raw[$key];
    }
    return $state;
}

function p63_state(string $studentId): array
{
    return ev62_valid_id($studentId) ? p63_state_normalize(storage_read(p63_state_path($studentId), false)) : p63_state_normalize([]);
}

/** Zahodí paměť požadavku (doporučení „Co dál“); volá se po každé změně stavu. */
function p63_memo_reset(): void
{
    $GLOBALS['p63_memo'] = [];
}

/** Záznam kroku s výchozími hodnotami. */
function p63_step_entry(array $state, string $pathId, string $stepId): array
{
    $e = is_array($state['paths'][$pathId][$stepId] ?? null) ? $state['paths'][$pathId][$stepId] : [];
    return ['status' => (string)($e['status'] ?? ''), 'attempts' => (int)($e['attempts'] ?? 0), 'best' => (float)($e['best'] ?? 0.0), 'last_variant' => (int)($e['last_variant'] ?? -1),
        'at' => (int)($e['at'] ?? 0), 'verify_day' => (string)($e['verify_day'] ?? ''), 'verify_count' => (int)($e['verify_count'] ?? 0)];
}

function p63_step_done(array $state, string $pathId, string $stepId): bool
{
    return p63_step_entry($state, $pathId, $stepId)['status'] === 'done';
}

/** @return array{done:int,total:int,next:?string,finished:bool,started:bool} */
function p63_path_progress(array $path, array $state): array
{
    $ids = p63_step_ids($path);
    $done = 0;
    $next = null;
    $started = false;
    foreach ($ids as $id) {
        $entry = p63_step_entry($state, (string)$path['id'], $id);
        if ($entry['attempts'] > 0 || $entry['status'] !== '') $started = true;
        if ($entry['status'] === 'done') $done++;
        elseif ($next === null) $next = $id;
    }
    return ['done' => $done, 'total' => count($ids), 'next' => $next, 'finished' => $next === null && $ids !== [], 'started' => $started];
}

/** Krok je přístupný, když jsou hotové všechny předchozí; opakování (spaced) až po ověření. */
function p63_step_open(array $path, array $state, string $stepId): bool
{
    if ($stepId === 'spaced') return p63_step_done($state, (string)$path['id'], 'verify');
    foreach (p63_step_ids($path) as $id) {
        if ($id === $stepId) return true;
        if (!p63_step_done($state, (string)$path['id'], $id)) return false;
    }
    return false;
}

// ---------------------------------------------------------------------------
// Varianty a výběr otázek (deterministické, bez náhody)
// ---------------------------------------------------------------------------

/** Varianta pokusu: hexdec(substr(sha1(sid|cesta|pokus),0,8)) % n; při shodě s předchozí +1. */
function p63_pick_variant(string $studentId, string $pathId, int $attempt, int $n, int $last = -1): int
{
    if ($n <= 1) return 0;
    $variant = (int)(hexdec(substr(sha1($studentId . '|' . $pathId . '|' . $attempt), 0, 8)) % $n);
    return $variant === $last ? ($variant + 1) % $n : $variant;
}

function p63_seeded_shuffle(array $items, string $seed): array
{
    $keyed = [];
    foreach (array_values($items) as $i => $item) $keyed[] = [sha1($seed . '|' . $i), $item];
    usort($keyed, static fn(array $a, array $b): int => strcmp($a[0], $b[0]));
    return array_column($keyed, 1);
}

/** Id otázek pro pokus: verify = jedna z variant, retrieval/spaced = count otázek z poolu. @return list<string> */
function p63_step_question_ids(array $path, array $step, string $studentId, array $state, int $attempt): array
{
    $pathId = (string)$path['id'];
    if ((string)$step['type'] === 'verify') {
        $variants = array_values((array)$step['variants']);
        $last = p63_step_entry($state, $pathId, (string)$step['id'])['last_variant'];
        return array_values(array_map('strval', (array)($variants[p63_pick_variant($studentId, $pathId . '|' . $step['id'], $attempt, count($variants), $last)] ?? [])));
    }
    $pool = p63_seeded_shuffle(array_values((array)$step['pool']), $studentId . '|' . $pathId . '|' . $step['id'] . '|' . $attempt);
    return array_values(array_map('strval', array_slice($pool, 0, max(1, (int)($step['count'] ?? 4)))));
}

/** Aktuální varianta kroku (0-based) pro zobrazení: verify = varianta, pre = případ, jinak 0. */
function p63_current_variant(array $path, array $step, string $studentId, array $state): int
{
    $entry = p63_step_entry($state, (string)$path['id'], (string)$step['id']);
    $n = (string)$step['type'] === 'verify' ? count((array)$step['variants']) : count((array)($step['cases'] ?? []));
    return p63_pick_variant($studentId, (string)$path['id'] . '|' . $step['id'], $entry['attempts'] + 1, max(1, $n), $entry['last_variant']);
}

// ---------------------------------------------------------------------------
// Hodnocení (čisté funkce)
// ---------------------------------------------------------------------------

/**
 * Banka otázek pro ověřování a opakování: položky typu single/bool/numeric ze souborů banky týmových her
 * (teamgames_v58_bank_net.php, teamgames_v58_bank_gfx.php) a opravy učitele ze storage (stejné id je přepíše,
 * draft ho vyřadí). Čte se přímo, bez načítání týmových her a simulátoru Linuxu (lehké).
 * @return array<string,array<string,mixed>>
 */
function p63_bank(): array
{
    static $bank = null;
    if ($bank !== null) return $bank;
    $rows = [];
    foreach (['teamgames_v58_bank_net.php', 'teamgames_v58_bank_gfx.php'] as $file) {
        $data = is_file(__DIR__ . '/' . $file) ? include __DIR__ . '/' . $file : [];
        foreach (is_array($data) ? $data : [] as $row) if (is_array($row)) $rows[] = $row;
    }
    $teacher = storage_read(STORAGE_DIR . '/teamgames_v58_bank.json.php', false)['items'] ?? [];
    foreach (is_array($teacher) ? $teacher : [] as $row) if (is_array($row)) $rows[] = $row;
    $bank = [];
    foreach ($rows as $row) {
        $id = (string)($row['id'] ?? '');
        if ($id === '') continue;
        $usable = in_array($row['type'] ?? '', P63_QUESTION_TYPES, true) && (string)($row['status'] ?? 'published') !== 'draft' && trim((string)($row['prompt'] ?? '')) !== '';
        if (!$usable) { unset($bank[$id]); continue; }
        $bank[$id] = ['id' => $id, 'category' => (string)($row['category'] ?? ''), 'type' => (string)$row['type'], 'prompt' => (string)$row['prompt'],
            'options' => array_values(array_filter((array)($row['options'] ?? []), 'is_string')), 'answer' => $row['answer'] ?? null,
            'tolerance' => is_numeric($row['tolerance'] ?? null) ? (float)$row['tolerance'] : 0.0, 'explain' => (string)($row['explain'] ?? '')];
    }
    return $bank;
}

/** Otázka z banky, jen podporovaného typu (single/bool/numeric), jinak null. */
function p63_question(string $id): ?array
{
    return p63_bank()[$id] ?? null;
}

function p63_norm_text(string $text): string
{
    return (string)preg_replace('/\s+/u', ' ', mb_strtolower(trim($text)));
}

/** Správnost odpovědi: single = text volby, bool = true/false, numeric = číslo s tolerancí. */
function p63_check_answer(array $question, mixed $given): bool
{
    return match ($question['type']) {
        'bool' => is_bool($given) && $given === (bool)$question['answer'],
        'numeric' => is_numeric($given) && is_numeric($question['answer']) && abs((float)$given - (float)$question['answer']) <= (float)$question['tolerance'] + 1e-9,
        default => is_string($given) && p63_norm_text($given) === p63_norm_text((string)$question['answer']),
    };
}

function p63_normalize_answer(array $question, mixed $given): mixed
{
    if (!is_string($given) && !is_int($given) && !is_float($given)) return null;
    $text = trim((string)$given);
    if ($text === '') return null;
    if ($question['type'] === 'bool') return in_array($text, ['1', 'ano'], true) ? true : (in_array($text, ['0', 'ne'], true) ? false : null);
    if ($question['type'] === 'numeric') return is_numeric(str_replace(',', '.', $text)) ? (float)str_replace(',', '.', $text) : null;
    return $text;
}

/**
 * Ohodnotí odpovědi na seznam otázek (odpověď i se shoduje s otázkou i); chybějící/neplatná = špatně.
 * @param list<string> $ids @param array<int,mixed> $answers
 * @return array{score:float,correct:int,total:int,detail:list<bool>}
 */
function p63_grade_questions(array $ids, array $answers): array
{
    $detail = [];
    foreach (array_values($ids) as $i => $id) {
        $q = p63_question((string)$id);
        $given = $q === null ? null : p63_normalize_answer($q, $answers[$i] ?? null);
        $detail[] = $q !== null && $given !== null && p63_check_answer($q, $given);
    }
    $correct = count(array_filter($detail));
    return ['score' => $detail === [] ? 0.0 : round($correct / count($detail), 3), 'correct' => $correct, 'total' => count($detail), 'detail' => $detail];
}

function p63_grade_retrieval(array $ids, array $answers): array
{
    return p63_grade_questions($ids, $answers);
}

function p63_lcs_len(array $a, array $b): int
{
    $prev = array_fill(0, count($b) + 1, 0);
    foreach ($a as $x) {
        $cur = [0];
        foreach ($b as $j => $y) $cur[$j + 1] = $x === $y ? $prev[$j] + 1 : max($prev[$j + 1], $cur[$j]);
        $prev = $cur;
    }
    return $prev[count($b)];
}

/**
 * Parsonsova úloha: skóre = LCS/délka vůči nejbližšímu přijatelnému pořadí; 1.0 jen při přesné shodě.
 * @param list<int> $correct správné pořadí (indexy řádků) @param list<list<int>> $accept přijatelné alternativy @param list<int> $given
 */
function p63_grade_parsons(array $correct, array $accept, array $given): float
{
    $given = array_values(array_map('intval', $given));
    $best = 0.0;
    foreach (array_merge([$correct], $accept) as $candidate) {
        $candidate = array_values(array_map('intval', (array)$candidate));
        if ($candidate === $given) return 1.0;
        if ($candidate !== []) $best = max($best, p63_lcs_len($candidate, $given) / count($candidate));
    }
    return round(min($best, 0.99), 3);
}

/** Je $order permutací 0..n-1? */
function p63_valid_order(array $order, int $n): bool
{
    $copy = array_values(array_map('intval', $order));
    sort($copy);
    return count($order) === $n && $copy === range(0, $n - 1);
}

/** Počáteční (zamíchané) pořadí řádků – nikdy ne správné ani přijatelné. @return list<int> */
function p63_parsons_initial(string $studentId, string $pathId, string $stepId, int $attempt, array $step): array
{
    $n = count((array)$step['lines']);
    $good = [range(0, $n - 1)];
    foreach ((array)($step['accept'] ?? []) as $alt) $good[] = array_values(array_map('intval', (array)$alt));
    for ($salt = 0; $salt < 8; $salt++) {
        $order = array_values(array_map('intval', p63_seeded_shuffle(range(0, $n - 1), $studentId . '|' . $pathId . '|' . $stepId . '|' . $attempt . '|' . $salt)));
        if (!in_array($order, $good, true)) return $order;
    }
    return array_reverse(range(0, $n - 1));
}

/** Podpis pořadí řádků (HMAC se tajemstvím session) – brání podvržení položek mimo úlohu. */
function p63_parsons_sign(array $order, string $pathId, string $stepId, int $attempt, string $secret): string
{
    return hash_hmac('sha256', implode(',', array_map('intval', $order)) . '|' . $pathId . '|' . $stepId . '|' . $attempt, $secret);
}

function p63_parsons_secret(): string
{
    return hash('sha256', 'p63|' . (function_exists('csrf_token') ? csrf_token() : ''));
}

/** Předpočítaný výstup příkazu případu (paths_v63_pre_data.php, vytvořil tools/v63_paths_build_pre.php); null = chybí. */
function p63_pre_output(string $pathId, string $stepId, string $caseId): ?string
{
    static $data = null;
    $data ??= is_file(__DIR__ . '/paths_v63_pre_data.php') ? (array)require __DIR__ . '/paths_v63_pre_data.php' : [];
    $row = $data['outputs'][$pathId . '|' . $stepId . '|' . $caseId] ?? null;
    return is_array($row) ? (string)$row['out'] : null;
}

/** Predikce: 1.0 / 0.0, nebo null, když volba není z nabídky. */
function p63_grade_pre(array $case, mixed $choice): ?float
{
    $options = (array)($case['options'] ?? []);
    if (!is_int($choice) && !(is_string($choice) && preg_match('/^\d{1,2}$/', $choice) === 1)) return null;
    $index = (int)$choice;
    if ($index < 0 || $index >= count($options)) return null;
    return $index === (int)$case['correct'] ? 1.0 : 0.0;
}

/** Skutečná úroveň zvládnutí z nejlepšího skóre ověření (1–4) – pro porovnání se sebehodnocením. */
function p63_actual_level(float $score): int
{
    return $score < 0.4 ? 1 : ($score < 0.7 ? 2 : ($score < 0.9 ? 3 : 4));
}

function p63_clean_note(string $note): string
{
    $text = (string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags($note));
    return mb_substr(trim((string)preg_replace('/\s+/u', ' ', $text)), 0, P63_NOTE_MAX);
}

// ---------------------------------------------------------------------------
// Rozložené opakování (čisté funkce)
// ---------------------------------------------------------------------------

/** Kompetence, jejichž opakování je splatné (nejstarší první). @param array<string,array<string,mixed>> $spaced @return list<string> */
function p63_spaced_due(array $spaced, int $now): array
{
    $due = [];
    foreach ($spaced as $competency => $entry) {
        if (is_array($entry) && (int)($entry['due_at'] ?? PHP_INT_MAX) <= $now) $due[(string)$competency] = (int)$entry['due_at'];
    }
    asort($due);
    return array_keys($due);
}

/** Nový plán po opakování: úspěch = další interval (1→3→7→14→30), neúspěch = zpět na 1 den. @return array{due_at:int,interval_d:int} */
function p63_spaced_schedule(?array $entry, bool $passed, int $now): array
{
    $pos = array_search((int)($entry['interval_d'] ?? 0), P63_SPACED_INTERVALS, true);
    $interval = $passed && $pos !== false ? P63_SPACED_INTERVALS[min((int)$pos + 1, count(P63_SPACED_INTERVALS) - 1)] : P63_SPACED_INTERVALS[0];
    return ['due_at' => $now + $interval * 86400, 'interval_d' => $interval];
}
