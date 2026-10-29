<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Rubriky 3–5 kritérií × úrovně 1–4 navázané na kompetence (comp62) a zápis hodnocení.
 *
 * Šablona vychází z rubriky katalogového projektu (project_assessments.php): stejná id a maxima, takže se body
 * převedou na rubric_scores pro project_save_grade() beze změny stávajícího hodnocení. Učitel šablonu klonuje do
 * storage/projects_v65_rubrics.json.php a upravuje texty a kompetence (počet kritérií a jejich id zůstávají).
 * Rubrika jen NAVRHUJE známku (project_suggested_grade); rozhoduje učitel (volitelný ruční override).
 * Úroveň 1 nelze udělit bez komentáře (PROJ65_L1_COMMENT_MIN znaků).
 */

const PROJ65_L1_COMMENT_MIN = 20;
const PROJ65_CRITERIA_MIN = 3;
const PROJ65_CRITERIA_MAX = 5;

/** Výchozí kompetence kritéria podle předmětu třídy (jen pilotní třídy mají předmět v comp62). */
function proj65_default_competencies(): array
{
    return [
        'os_site' => ['design' => 'net_addressing', 'evidence' => 'net_diagnose', 'security' => 'net_security', 'validation' => 'net_services'],
        'grafika_web' => ['brief' => 'gfx_formats', 'hierarchy' => 'gfx_composition', 'craft' => 'gfx_typography', 'ux' => 'web_ux',
            'visual' => 'gfx_color_contrast', 'responsive' => 'web_a11y', 'prototype' => 'web_html_structure'],
    ];
}

function proj65_level_labels(): array
{
    return [1 => 'Začátek', 2 => 'Základ', 3 => 'Dobré', 4 => 'Výborné'];
}

/** @return array{criteria:list<array<string,mixed>>,source:string}|null */
function proj65_rubric_template(string $classId, string $projectId): ?array
{
    $project = project_find($classId, $projectId);
    if ($project === null) return null;
    $subject = function_exists('comp62_subject_for_class') ? comp62_subject_for_class($classId) : null;
    $defaults = $subject !== null ? (proj65_default_competencies()[$subject] ?? []) : [];
    $criteria = [];
    foreach ((array)$project['rubric'] as $c) {
        if (!is_array($c)) continue;
        $id = (string)$c['id'];
        $criteria[] = ['id' => $id, 'title' => (string)$c['title'], 'description' => (string)$c['description'], 'max' => (int)$c['max'],
            'competency' => (string)($defaults[$id] ?? ''), 'levels' => [1 => 'Chybí podstatné části.', 2 => 'Splněna část zadání.', 3 => 'Splněno s drobnými nedostatky.', 4 => 'Splněno a obhájeno.']];
    }
    return ['criteria' => $criteria, 'source' => 'template'];
}

/** Rubrika projektu: klon učitele, jinak šablona. Projekt klienta (p60) má obecnou rubriku bez kompetencí, dokud ji učitel neklonuje. */
function proj65_rubric_for(array $record): ?array
{
    $key = (string)$record['class_id'] . ':' . (string)$record['project_id'];
    $clone = storage_read(proj65_path('rubrics'), false)[$key] ?? null;
    if (is_array($clone) && is_array($clone['criteria'] ?? null)) return ['criteria' => array_values($clone['criteria']), 'source' => 'clone'];
    if ((string)$record['kind'] === 'p60') return proj65_rubric_generic();
    return proj65_rubric_template((string)$record['class_id'], (string)$record['project_id']);
}

function proj65_rubric_generic(): array
{
    $levels = [1 => 'Chybí podstatné části.', 2 => 'Splněna část zadání.', 3 => 'Splněno s drobnými nedostatky.', 4 => 'Splněno a obhájeno.'];
    $mk = static fn(string $id, string $title, string $desc): array => ['id' => $id, 'title' => $title, 'description' => $desc, 'max' => 5, 'competency' => '', 'levels' => $levels];
    return ['source' => 'template', 'criteria' => [
        $mk('brief', 'Splnění zadání', 'Výstup odpovídá tomu, co klient potřeboval.'),
        $mk('quality', 'Kvalita provedení', 'Řemeslné zpracování je čisté a promyšlené.'),
        $mk('communication', 'Komunikace a termíny', 'Práce je předaná včas a srozumitelně.'),
    ]];
}

/** Validace kritérií (3–5, texty, kompetence z katalogu předmětu, id a maxima shodná se šablonou). Vrací kód chyby, nebo null. */
function proj65_rubric_validate(array $criteria, ?array $template, string $classId): ?string
{
    $n = count($criteria);
    if ($n < PROJ65_CRITERIA_MIN || $n > PROJ65_CRITERIA_MAX) return 'criteria_count';
    $subject = function_exists('comp62_subject_for_class') ? comp62_subject_for_class($classId) : null;
    $known = $subject !== null ? array_keys(comp62_competencies($subject)) : [];
    $tplById = [];
    foreach ((array)($template['criteria'] ?? []) as $t) $tplById[(string)$t['id']] = $t;
    foreach ($criteria as $c) {
        if (!is_array($c) || preg_match('/^[a-z0-9_]{2,30}$/', (string)($c['id'] ?? '')) !== 1) return 'criterion_id';
        $title = trim((string)($c['title'] ?? ''));
        if ($title === '' || u_strlen($title) > 80 || u_strlen((string)($c['description'] ?? '')) > 300) return 'criterion_text';
        foreach ([1, 2, 3, 4] as $lvl) {
            $txt = (string)(($c['levels'] ?? [])[$lvl] ?? '');
            if (trim($txt) === '' || u_strlen($txt) > 200) return 'level_text';
        }
        $comp = (string)($c['competency'] ?? '');
        if ($comp !== '' && !in_array($comp, $known, true)) return 'competency_unknown';
        if ($tplById !== [] && (!isset($tplById[(string)$c['id']]) || (int)$tplById[(string)$c['id']]['max'] !== (int)($c['max'] ?? 0))) return 'criterion_mismatch';
    }
    if ($tplById !== [] && count(array_unique(array_map(static fn($c): string => (string)$c['id'], $criteria))) !== count($tplById)) return 'criterion_mismatch';
    return null;
}

/** Učitel naklonuje (a upraví) rubriku projektu. Vstup: criteria[id] = [title, description, competency, level1..level4]. */
function proj65_rubric_clone(string $classId, string $projectId, array $input, string $by): array
{
    $template = proj65_rubric_template($classId, $projectId);
    if ($template === null) return ['ok' => false, 'error' => 'not_found'];
    $criteria = [];
    foreach ($template['criteria'] as $t) {
        $raw = is_array($input[$t['id']] ?? null) ? $input[$t['id']] : [];
        $levels = [];
        foreach ([1, 2, 3, 4] as $lvl) $levels[$lvl] = proj65_text($raw['level' . $lvl] ?? $t['levels'][$lvl], 200);
        $criteria[] = ['id' => $t['id'], 'max' => $t['max'], 'title' => proj65_text($raw['title'] ?? $t['title'], 80), 'description' => proj65_text($raw['description'] ?? $t['description'], 300),
            'competency' => proj65_text($raw['competency'] ?? $t['competency'], 40), 'levels' => $levels];
    }
    $error = proj65_rubric_validate($criteria, $template, $classId);
    if ($error !== null) return ['ok' => false, 'error' => $error];
    storage_update(proj65_path('rubrics'), static function (array $all) use ($classId, $projectId, $criteria, $by): array {
        $all[$classId . ':' . $projectId] = ['criteria' => $criteria, 'cloned_by' => $by, 'updated_at' => date(DATE_ATOM)];
        return $all;
    });
    return ['ok' => true, 'error' => null];
}

/**
 * Úrovně → body. Úroveň L z maxima M dává round(M × L / 4); rubric_scores má klíče id kritérií (jako project_save_grade).
 * @param array<string,int> $levels
 * @return array{points:int,max:int,rubric_scores:array<string,int>}
 */
function proj65_levels_to_points(array $rubric, array $levels): array
{
    $scores = [];
    $points = 0;
    $max = 0;
    foreach ($rubric['criteria'] as $c) {
        $id = (string)$c['id'];
        $lvl = max(1, min(4, (int)($levels[$id] ?? 1)));
        $scores[$id] = (int)round(((int)$c['max']) * $lvl / 4);
        $points += $scores[$id];
        $max += (int)$c['max'];
    }
    return ['points' => $points, 'max' => max(1, $max), 'rubric_scores' => $scores];
}

/** Úrovně 1–4 u všech kritérií; úroveň 1 vyžaduje komentář. Vrací kód chyby, nebo null. */
function proj65_levels_validate(array $rubric, array $levels, array $comments): ?string
{
    foreach ($rubric['criteria'] as $c) {
        $id = (string)$c['id'];
        $lvl = $levels[$id] ?? null;
        if (!is_int($lvl) || $lvl < 1 || $lvl > 4) return 'level_range';
        if ($lvl === 1 && u_strlen(trim((string)($comments[$id] ?? ''))) < PROJ65_L1_COMMENT_MIN) return 'level1_comment';
    }
    return null;
}

/** Úrovně z formuláře (jen id kritérií rubriky, celá čísla). @return array<string,int|null> */
function proj65_levels_from_input(array $rubric, mixed $raw): array
{
    $raw = is_array($raw) ? $raw : [];
    $out = [];
    foreach ($rubric['criteria'] as $c) {
        $v = $raw[(string)$c['id']] ?? null;
        $out[(string)$c['id']] = is_string($v) && preg_match('/^[1-4]$/', $v) === 1 ? (int)$v : null;
    }
    return $out;
}

/**
 * Hodnocení učitelem. Katalogový projekt: body jdou přes project_save_grade (stávající záznam, historie, verze).
 * Publikace přidá verzi do sidecaru, přepne stav na graded a vyvolá zápis důkazu (proj65_emit_evidence).
 * @param array<string,int|null> $levels
 * @return array{ok:bool,error:?string,version?:int}
 */
function proj65_grade(string $ref, array $levels, array $comments, array $extra, bool $publish, callable $canClass, string $by): array
{
    $record = proj65_get($ref);
    if ($record === null) return ['ok' => false, 'error' => 'not_found'];
    if (!$canClass((string)$record['class_id'])) return ['ok' => false, 'error' => 'class_out_of_scope'];
    if (!in_array((string)$record['state'], ['submitted', 'peer_review', 'graded'], true)) return ['ok' => false, 'error' => 'invalid_transition'];
    $rubric = proj65_rubric_for($record);
    if ($rubric === null) return ['ok' => false, 'error' => 'no_rubric'];
    $comments = array_map(static fn($c): string => proj65_text($c, 400), $comments);
    $error = proj65_levels_validate($rubric, $levels, $comments);
    if ($error !== null) return ['ok' => false, 'error' => $error];
    $calc = proj65_levels_to_points($rubric, $levels);
    $gradeVersion = 0;
    $suggested = project_suggested_grade($calc['points'], $calc['max']);
    if ((string)$record['kind'] === 'cat') {
        $gradeVersion = proj65_save_catalog_grade($record, $calc, $extra, $publish);
    }
    $lastVersion = (int)(((array)end($record['versions']))['version'] ?? 0);
    $version = ['version' => max($gradeVersion, $lastVersion + 1), 'published_at' => date(DATE_ATOM), 'levels' => $levels, 'comments' => $comments,
        'competencies' => proj65_criteria_competencies($rubric), 'points' => $calc['points'], 'max' => $calc['max'], 'suggested_grade' => $suggested];
    $out = ['ok' => false, 'error' => 'not_found'];
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $levels, $comments, $publish, $version, $by, &$out): array {
        $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
        if ($row === null) return $all;
        $row['draft'] = $publish ? null : ['levels' => $levels, 'comments' => $comments, 'at' => date(DATE_ATOM)];
        if ($publish) {
            $row['versions'] = array_slice(array_merge(array_values((array)$row['versions']), [$version]), -10);
            if ((string)$row['state'] !== 'graded') $row = proj65_push_history($row, 'graded', $by);
        }
        $row['updated_at'] = date(DATE_ATOM);
        $all[$ref] = $row;
        $out = ['ok' => true, 'error' => null, 'version' => (int)$version['version']];
        return $all;
    });
    if ($out['ok'] && $publish && function_exists('proj65_emit_evidence')) {
        $fresh = proj65_get($ref);
        if ($fresh !== null) $out['evidence'] = proj65_emit_evidence($fresh, $version);
    }
    return $out;
}

/** @return array<string,string> kritérium → kompetence (jen vyplněné) */
function proj65_criteria_competencies(array $rubric): array
{
    $out = [];
    foreach ($rubric['criteria'] as $c) {
        if ((string)($c['competency'] ?? '') !== '') $out[(string)$c['id']] = (string)$c['competency'];
    }
    return $out;
}

/** Zapíše bodové hodnocení přes project_save_grade a zachová stávající komentáře, soukromou poznámku a úpravy členů týmu. */
function proj65_save_catalog_grade(array $record, array $calc, array $extra, bool $publish): int
{
    $existing = project_grade_find((string)$record['class_id'], (string)$record['project_id'], (string)$record['target_type'], (string)$record['target_id']) ?? [];
    $manual = isset($extra['grade']) && is_numeric($extra['grade']) ? max(1, min(5, (int)$extra['grade'])) : null;
    $input = ['class_id' => $record['class_id'], 'project_id' => $record['project_id'], 'target_type' => $record['target_type'], 'target_id' => $record['target_id'],
        'rubric_scores' => $calc['rubric_scores'], 'status' => $publish ? 'published' : 'draft', 'grade' => $manual,
        'teacher_comment' => proj65_text($extra['teacher_comment'] ?? ($existing['teacher_comment'] ?? ''), 4000),
        'strengths' => proj65_text($extra['strengths'] ?? ($existing['strengths'] ?? ''), 2000),
        'next_step' => proj65_text($extra['next_step'] ?? ($existing['next_step'] ?? ''), 2000),
        'private_note' => (string)($existing['private_note'] ?? ''), 'member_adjustments' => (array)($existing['member_adjustments'] ?? [])];
    // Rozpracované hodnocení už zveřejněné známky nesmí skrýt: koncept se pak drží jen v sidecaru.
    if (!$publish && in_array((string)($existing['status'] ?? ''), ['published', 'returned'], true)) return 0;
    $saved = project_save_grade($input);
    unset($GLOBALS['educanet_runtime_indexes']['project_grades:index']);
    return (int)($saved['version'] ?? 1);
}
