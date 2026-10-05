<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/competencies_v62.php';
require_once __DIR__ . '/evidence_v62.php';
require_once __DIR__ . '/mastery_v62.php';
require_once __DIR__ . '/projects_v65.php';
require_once __DIR__ . '/assessment_v66.php';

/**
 * EDUCANET v66 · návrh hodnocení (známka 1–5 + slovní hodnocení) s řetězcem důkazů. Rozhoduje učitel.
 *
 * Rozhodnutí školy: tři zdroje s vahami sumativní testy 40 % / zvládnutí kompetencí 40 % / projekty v65 20 %, hranice
 * 90 / 75 / 50 / 30 % (váhy a hranice jsou nastavitelné po třídách, mění je jen administrátor). Návrh je ve výchozím stavu
 * VYPNUTÝ a zapíná se po třídách. Žák vidí řetězec důkazů vždy (je-li hodnocení ve třídě zapnuté), známku až po převzetí
 * učitelem (g66_accept). Do návrhu jdou jen zdroje z G66_SOURCES (whitelist): sumativní testy, zvládnutí kompetencí
 * (jen důkazy typu test / project / lab) a hodnocení projektů v65. Hry, aréna, ekonomika ani odměny se nepočítají.
 * Návrh i převzetí se ukládají do storage/assessment_v66/ (nastavení, převzetí s hashem vzorce a učitele, cache třídy).
 */

const G66_SOURCES = ['summative_test', 'mastery', 'project_v65'];
const G66_DEFAULT_WEIGHTS = ['summative_test' => 40, 'mastery' => 40, 'project_v65' => 20];
const G66_DEFAULT_THRESHOLDS = [90, 75, 50, 30];
const G66_MASTERY_SOURCES = ['test', 'project', 'lab'];
const G66_MIN_MASTERY_COMPETENCIES = 3;
const G66_LOG_CAP = 20;
const G66_VERSION = 1;

function g66_sources(): array
{
    return G66_SOURCES;
}

function g66_settings_path(): string
{
    return a66_dir() . '/grading_settings.json.php';
}

function g66_accepted_path(): string
{
    return a66_dir() . '/grading_accepted.json.php';
}

function g66_proposals_path(string $classId): string
{
    return a66_dir() . '/proposals_' . a66_class_safe($classId) . '.json.php';
}

/** Váhy: přesně klíče z G66_SOURCES, celá čísla 0–100, součet 100; jinak null. @return array<string,int>|null */
function g66_valid_weights(array $weights): ?array
{
    if (array_diff(array_keys($weights), G66_SOURCES) !== [] || count($weights) !== count(G66_SOURCES)) return null;
    $out = [];
    foreach (G66_SOURCES as $source) {
        $w = $weights[$source] ?? null;
        if (!is_int($w) || $w < 0 || $w > 100) return null;
        $out[$source] = $w;
    }
    return array_sum($out) === 100 ? $out : null;
}

/** Hranice známek 1–4 (procenta, striktně klesající, 1–100); jinak null. @return list<int>|null */
function g66_valid_thresholds(array $thresholds): ?array
{
    $t = array_values($thresholds);
    if (count($t) !== 4) return null;
    foreach ($t as $i => $v) {
        if (!is_int($v) || $v < 1 || $v > 100 || ($i > 0 && $v >= $t[$i - 1])) return null;
    }
    return $t;
}

/** Nastavení třídy s výchozími hodnotami (výchozí: vypnuto). @return array{enabled:bool,weights:array<string,int>,thresholds:list<int>,at:int,by:string} */
function g66_settings(string $classId): array
{
    $row = storage_read(g66_settings_path(), false)[$classId] ?? null;
    $row = is_array($row) ? $row : [];
    return ['enabled' => ($row['enabled'] ?? false) === true,
        'weights' => g66_valid_weights(is_array($row['weights'] ?? null) ? $row['weights'] : []) ?? G66_DEFAULT_WEIGHTS,
        'thresholds' => g66_valid_thresholds(is_array($row['thresholds'] ?? null) ? $row['thresholds'] : []) ?? G66_DEFAULT_THRESHOLDS,
        'at' => (int)($row['at'] ?? 0), 'by' => (string)($row['by'] ?? '')];
}

function g66_enabled(string $classId): bool
{
    return g66_settings($classId)['enabled'];
}

/**
 * Uloží nastavení třídy. Smí jen administrátor ($isAdmin určuje volající z přihlášení); neplatné váhy/hranice se odmítnou celé.
 * @return array{ok:bool,error:string}
 */
function g66_settings_save(string $classId, array $input, string $actorHash, bool $isAdmin, ?int $now = null): array
{
    if (!$isAdmin) return ['ok' => false, 'error' => 'admin_only'];
    if (!isset($GLOBALS['modules'][$classId]) || preg_match('/^[a-f0-9]{16}$/', $actorHash) !== 1) return ['ok' => false, 'error' => 'class'];
    $weights = g66_valid_weights(is_array($input['weights'] ?? null) ? $input['weights'] : []);
    $thresholds = g66_valid_thresholds(is_array($input['thresholds'] ?? null) ? $input['thresholds'] : []);
    if ($weights === null) return ['ok' => false, 'error' => 'weights'];
    if ($thresholds === null) return ['ok' => false, 'error' => 'thresholds'];
    $row = ['enabled' => ($input['enabled'] ?? false) === true, 'weights' => $weights, 'thresholds' => $thresholds, 'at' => $now ?? time(), 'by' => $actorHash];
    storage_update(g66_settings_path(), static function (array $data) use ($classId, $row): array {
        $data[$classId] = $row;
        return $data;
    });
    return ['ok' => true, 'error' => ''];
}

/** @return array{v:int,weights:array<string,int>,thresholds:list<int>} */
function g66_formula(string $classId): array
{
    $s = g66_settings($classId);
    return ['v' => G66_VERSION, 'weights' => $s['weights'], 'thresholds' => $s['thresholds']];
}

function g66_formula_hash(array $formula): string
{
    return substr(hash('sha256', (string)json_encode([$formula['v'] ?? 0, $formula['weights'] ?? [], $formula['thresholds'] ?? []])), 0, 16);
}

/** Známka 1–5 z procent podle hranic. */
function g66_grade_for_percent(float $percent, array $thresholds): int
{
    foreach (array_values($thresholds) as $i => $limit) {
        if ($percent >= $limit) return $i + 1;
    }
    return 5;
}

/** Slovní hodnocení známky česky (cockpit, exporty); žákovský text řeší view přes tr(). */
function g66_verbal_cs(int $grade): string
{
    return [1 => 'Výborně zvládnuto', 2 => 'Chvalitebně zvládnuto', 3 => 'Základy zvládnuty', 4 => 'Zvládnuto jen minimum', 5 => 'Zatím nezvládnuto'][$grade] ?? '';
}

/** Vážený průměr dostupných částí (procenta); váhy chybějících částí se přepočtou, bez dat null. @param array<string,?float> $parts @param array<string,int> $weights */
function g66_combine(array $parts, array $weights): ?float
{
    $num = 0.0;
    $den = 0.0;
    foreach (G66_SOURCES as $source) {
        $pct = $parts[$source] ?? null;
        $w = (int)($weights[$source] ?? 0);
        if ($pct === null || $w <= 0) continue;
        $num += $w * $pct;
        $den += $w;
    }
    return $den > 0 ? round($num / $den, 2) : null;
}

/** Hash z klíče žáka (posledních 24 hex znaků). */
function g66_key_hash(string $studentKey): string
{
    return preg_match('/:(?:student|s):([a-f0-9]{24})$/D', $studentKey, $m) === 1 ? $m[1] : '';
}

/** Klíč žáka třídy podle hashe, nebo null (jen žáci dané třídy). */
function g66_key_for_hash(string $classId, string $hash): ?string
{
    if (preg_match('/^[a-f0-9]{24}$/', $hash) !== 1) return null;
    foreach (array_keys(project_students_for_class($classId)) as $key) {
        if (g66_key_hash((string)$key) === $hash) return (string)$key;
    }
    return null;
}

function g66_student_id(string $classId, string $studentKey): ?string
{
    $student = project_students_for_class($classId)[$studentKey] ?? null;
    $id = is_array($student) ? identity58_id_for_student($classId, (string)$student['label']) : null;
    return $id !== null && ev62_valid_id($id) ? $id : null;
}

// ---------------------------------------------------------------------------
// Části návrhu: každá vrací ['pct' => ?float, 'n' => počet podkladů, 'chain' => [...]]
// ---------------------------------------------------------------------------

/** Sumativní testy žáka: poslední uložený pokus každého testu označeného jako sumativní. */
function g66_part_summative(string $classId, string $studentKey, ?string $sid): array
{
    $kinds = array_keys(array_filter(a66_kinds($classId), static fn(array $k): bool => $k['kind'] === 'summative'));
    $scores = [];
    $chain = [];
    $progress = storage_read(STORAGE_DIR . '/progress_v56.json.php', false)[$classId . '|' . $studentKey] ?? [];
    $state = $sid !== null && function_exists('p63_state') ? p63_state($sid) : null;
    foreach ($kinds as $testId) {
        $pct = null;
        $at = '';
        if (preg_match('/^v56:l(\d+)$/', $testId, $m) === 1 && is_array($progress[$m[1]]['test'] ?? null) && (string)($progress[$m[1]]['test']['at'] ?? '') !== '') {
            $pct = (float)($progress[$m[1]]['test']['percent'] ?? 0);
            $at = (string)$progress[$m[1]]['test']['at'];
        } elseif (preg_match('/^p63:([a-z0-9_]+):verify$/', $testId, $m) === 1 && $state !== null) {
            $entry = p63_step_entry($state, $m[1], 'verify');
            if ($entry['attempts'] > 0) { $pct = $entry['best'] * 100; $at = $entry['at'] > 0 ? date(DATE_ATOM, $entry['at']) : ''; }
        }
        if ($pct === null) continue;
        $pct = max(0.0, min(100.0, $pct));
        $scores[] = $pct;
        $chain[] = ['source' => 'summative_test', 'ref' => $testId, 'label' => a66_test_label($testId), 'score' => round($pct / 100, 3), 'at' => $at, 'evidence' => []];
    }
    return ['pct' => $scores === [] ? null : round(array_sum($scores) / count($scores), 2), 'n' => count($scores), 'chain' => $chain];
}

/** Zvládnutí kompetencí: jen důkazy typu test / project / lab; potřebuje aspoň G66_MIN_MASTERY_COMPETENCIES ověřených kompetencí. */
function g66_part_mastery(string $classId, ?string $sid, int $now): array
{
    $subject = comp62_subject_for_class($classId);
    if ($subject === null || $sid === null) return ['pct' => null, 'n' => 0, 'chain' => []];
    $competencies = comp62_competencies($subject);
    $rows = array_values(array_filter(ev62_read($sid), static fn(array $r): bool => in_array((string)($r['source'] ?? ''), G66_MASTERY_SOURCES, true)));
    $map = m62_compute($rows, $now, array_keys($competencies));
    $scores = [];
    $chain = [];
    foreach ($map as $id => $info) {
        if ($info['state'] === 'neovereno' || $info['score'] === null) continue;
        $mine = array_values(array_filter($rows, static fn(array $r): bool => (string)$r['competency'] === (string)$id));
        usort($mine, static fn(array $a, array $b): int => strcmp((string)$b['at'], (string)$a['at']));
        $evidence = array_map(static fn(array $r): array => ['ref' => (string)$r['artefact_ref'], 'source' => (string)$r['source'], 'score' => (float)$r['score'], 'at' => (string)$r['at']], array_slice($mine, 0, 3));
        $scores[] = (float)$info['score'];
        $chain[] = ['source' => 'mastery', 'ref' => 'comp:' . $id, 'label' => (string)$competencies[$id]['label'], 'state' => (string)$info['state'], 'score' => (float)$info['score'], 'at' => (string)($info['last_at'] ?? ''), 'evidence' => $evidence];
    }
    if (count($scores) < G66_MIN_MASTERY_COMPETENCIES) return ['pct' => null, 'n' => count($scores), 'chain' => $chain];
    return ['pct' => round(array_sum($scores) / count($scores) * 100, 2), 'n' => count($scores), 'chain' => $chain];
}

/** Zveřejněná hodnocení projektů v65. */
function g66_part_projects(string $classId, string $studentKey): array
{
    $rows = proj65_published_ratios($classId, $studentKey);
    $chain = array_map(static fn(array $r): array => ['source' => 'project_v65', 'ref' => 'proj:' . $r['id'], 'label' => 'Projekt', 'score' => $r['ratio'], 'at' => $r['at'], 'evidence' => []], $rows);
    return ['pct' => $rows === [] ? null : round(array_sum(array_column($rows, 'ratio')) / count($rows) * 100, 2), 'n' => count($rows), 'chain' => $chain];
}

/**
 * Návrh hodnocení žáka; null, když je hodnocení ve třídě vypnuté. Nic nezapisuje.
 * @return array{status:string,grade:?int,verbal:string,percent:?float,parts:array<string,array<string,mixed>>,chain:list<array<string,mixed>>,formula_hash:string,proposal_hash:string,weights:array<string,int>}|null
 */
function g66_proposal(string $classId, string $studentKey, ?int $now = null): ?array
{
    if (!g66_enabled($classId)) return null;
    $now ??= time();
    $formula = g66_formula($classId);
    $sid = g66_student_id($classId, $studentKey);
    $raw = ['summative_test' => g66_part_summative($classId, $studentKey, $sid), 'mastery' => g66_part_mastery($classId, $sid, $now), 'project_v65' => g66_part_projects($classId, $studentKey)];
    $percent = g66_combine(array_map(static fn(array $p): ?float => $p['pct'], $raw), $formula['weights']);
    $parts = [];
    $chain = [];
    foreach ($raw as $source => $part) {
        $used = $part['pct'] !== null && $formula['weights'][$source] > 0;
        $parts[$source] = ['pct' => $part['pct'], 'n' => $part['n'], 'weight' => $formula['weights'][$source], 'used' => $used];
        if ($used || $source === 'mastery') $chain = array_merge($chain, $part['chain']);
    }
    $grade = $percent === null ? null : g66_grade_for_percent($percent, $formula['thresholds']);
    $fh = g66_formula_hash($formula);
    $refs = array_map(static fn(array $c): string => $c['ref'] . '=' . $c['score'], $chain);
    return ['status' => $percent === null ? 'insufficient' : 'ok', 'grade' => $grade, 'verbal' => $grade === null ? '' : g66_verbal_cs($grade), 'percent' => $percent, 'parts' => $parts,
        'chain' => $percent === null ? [] : $chain, 'formula_hash' => $fh, 'weights' => $formula['weights'],
        'proposal_hash' => substr(hash('sha256', json_encode([$grade, $percent, $fh, $refs]) ?: ''), 0, 16)];
}

// ---------------------------------------------------------------------------
// Převzetí učitelem
// ---------------------------------------------------------------------------

/** @return array{grade:int,verbal:string,percent:float,formula_hash:string,at:int}|null převzatá známka žáka (jen aktuální) */
function g66_accepted(string $classId, string $studentId): ?array
{
    $row = storage_read(g66_accepted_path(), false)[$classId][$studentId]['current'] ?? null;
    return is_array($row) && isset($row['grade']) ? ['grade' => (int)$row['grade'], 'verbal' => (string)($row['verbal'] ?? ''), 'percent' => (float)($row['percent'] ?? 0), 'formula_hash' => (string)($row['formula_hash'] ?? ''), 'at' => (int)($row['at'] ?? 0)] : null;
}

/**
 * Učitel převezme návrh (známka se uloží beze změny). Očekávaný $proposalHash brání převzetí zastaralého návrhu.
 * Auditní stopa: čas, známka, hash vzorce a hash učitele (žádná jména).
 * @return array{ok:bool,error:string}
 */
function g66_accept(string $classId, string $studentKey, string $proposalHash, string $actorHash, ?int $now = null): array
{
    $now ??= time();
    if (preg_match('/^[a-f0-9]{16}$/', $actorHash) !== 1) return ['ok' => false, 'error' => 'actor'];
    $proposal = g66_proposal($classId, $studentKey, $now);
    $sid = g66_student_id($classId, $studentKey);
    if ($proposal === null) return ['ok' => false, 'error' => 'disabled'];
    if ($sid === null) return ['ok' => false, 'error' => 'student'];
    if ($proposal['status'] !== 'ok' || !hash_equals($proposal['proposal_hash'], $proposalHash)) return ['ok' => false, 'error' => 'stale'];
    $entry = ['grade' => (int)$proposal['grade'], 'verbal' => (string)$proposal['verbal'], 'percent' => (float)$proposal['percent'], 'formula_hash' => $proposal['formula_hash'], 'proposal_hash' => $proposal['proposal_hash'], 'at' => $now, 'actor_hash' => $actorHash];
    storage_update(g66_accepted_path(), static function (array $data) use ($classId, $sid, $entry): array {
        $log = array_values(array_filter((array)($data[$classId][$sid]['log'] ?? []), 'is_array'));
        $log[] = ['at' => $entry['at'], 'grade' => $entry['grade'], 'formula_hash' => $entry['formula_hash'], 'actor_hash' => $entry['actor_hash']];
        $data[$classId][$sid] = ['current' => $entry, 'log' => array_slice($log, -G66_LOG_CAP)];
        return $data;
    });
    return ['ok' => true, 'error' => ''];
}

/** Auditní stopa převzetí žáka (jen hashe). @return list<array<string,mixed>> */
function g66_accept_log(string $classId, string $studentId): array
{
    return array_values(array_filter((array)(storage_read(g66_accepted_path(), false)[$classId][$studentId]['log'] ?? []), 'is_array'));
}

/** Retence: převzaté známky žáků se stavem left/archived déle než COMP62_RETENTION_GRACE_DAYS se mažou (bez data odchodu nic). @return array{purge:int,dry_run:bool} */
function g66_retention_purge(bool $dryRun, ?int $now = null): array
{
    $now ??= time();
    $out = ['purge' => 0, 'dry_run' => $dryRun];
    if (!function_exists('identity58_student')) return $out;
    $expired = static function (string $sid) use ($now): bool {
        $stu = identity58_student($sid);
        if (!is_array($stu) || !in_array((string)($stu['status'] ?? 'active'), ['left', 'archived'], true)) return false;
        $since = strtotime((string)($stu['archived_at'] ?? '')) ?: 0;
        return $since > 0 && $now - $since >= COMP62_RETENTION_GRACE_DAYS * 86400;
    };
    $apply = static function (array $data) use ($expired, &$out): array {
        $out['purge'] = 0;
        foreach ($data as $classId => $students) {
            foreach (array_keys((array)$students) as $sid) {
                if (!$expired((string)$sid)) continue;
                unset($data[$classId][$sid]);
                $out['purge']++;
            }
            if (isset($data[$classId]) && $data[$classId] === []) unset($data[$classId]);
        }
        return $data;
    };
    if ($dryRun) $apply(storage_read(g66_accepted_path(), false));
    else storage_update(g66_accepted_path(), $apply);
    return $out;
}

// ---------------------------------------------------------------------------
// Cache návrhů třídy (čte cockpit; bez jmen)
// ---------------------------------------------------------------------------

/** Spočítá návrhy celé třídy a uloží cache: řádky podle hashe žáka (bez jmen). @return array<string,mixed> */
function g66_class_build(string $classId, ?int $now = null): array
{
    $now ??= time();
    $rows = [];
    if (g66_enabled($classId)) {
        foreach (array_keys(project_students_for_class($classId)) as $key) {
            $p = g66_proposal($classId, (string)$key, $now);
            $hash = g66_key_hash((string)$key);
            if ($p === null || $hash === '') continue;
            $rows[$hash] = ['status' => $p['status'], 'grade' => $p['grade'], 'percent' => $p['percent'], 'ph' => $p['proposal_hash'],
                'parts' => array_map(static fn(array $x): ?float => $x['used'] ? $x['pct'] : null, $p['parts'])];
        }
    }
    $result = ['v' => G66_VERSION, 'class' => $classId, 'built_at' => $now, 'formula_hash' => g66_formula_hash(g66_formula($classId)), 'enabled' => g66_enabled($classId), 'rows' => $rows];
    storage_update(g66_proposals_path($classId), static fn(array $d): array => $result);
    return $result;
}

/** Cache návrhů (jen čtení); prázdné pole = ještě nepřipraveno. @return array<string,mixed> */
function g66_class_cache(string $classId): array
{
    $data = storage_read(g66_proposals_path($classId), false);
    return is_array($data['rows'] ?? null) ? $data : [];
}
