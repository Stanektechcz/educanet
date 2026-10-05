<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Týmové projekty: kanban milníků (sidecar nad project_tasks, milníky jsou volitelné), deník přínosu
 * a vzájemné rozdělení 100 bodů → faktor přínosu jako NÁVRH do member_adjustments.points_delta (potvrzuje učitel).
 *
 * - Milníky: storage/projects_v65_milestones.json.php = {id => milník}; úkoly zůstávají v project_tasks (project_workspace.php), milník na ně jen odkazuje.
 * - Deník: proud storage/projects_v65_diary/ (storage_append), záznam ≤ 280 znaků; vidí tým a učitel. Retence viz projects_v65_evidence.php.
 * - Rozdělení bodů: každý člen rozdělí 100 bodů mezi ostatní členy; faktor = clamp(průměr přijatých / rovný podíl, 0,8–1,2).
 *   Tým s méně než 3 členy faktor nemá. Faktor nikdy nemění známku sám – učitel ho potvrdí akcí proj65_t_apply_factor.
 */

const PROJ65_MS_STATUSES = ['todo', 'doing', 'done'];
const PROJ65_MS_MAX_PER_TEAM = 20;
const PROJ65_DIARY_MAX = 280;
const PROJ65_DIARY_PER_DAY = 5;
const PROJ65_FACTOR_MIN = 0.8;
const PROJ65_FACTOR_MAX = 1.2;
const PROJ65_TEAM_MIN_FOR_FACTOR = 3;

function proj65_ms_status_label(string $s): string
{
    return ['todo' => 'K udělání', 'doing' => 'Pracujeme', 'done' => 'Hotovo'][$s] ?? $s;
}

/** Milníky týmu (pořadí vzniku). @return list<array<string,mixed>> */
function proj65_ms_list(string $groupId): array
{
    $rows = array_values(array_filter(storage_read(proj65_path('milestones'), false), static fn($m): bool => is_array($m) && (string)($m['group_id'] ?? '') === $groupId));
    usort($rows, static fn(array $a, array $b): int => strcmp((string)$a['created_at'], (string)$b['created_at']) ?: strcmp((string)$a['id'], (string)$b['id']));
    return $rows;
}

/** Milník přidá člen týmu; povolen jen když projekt milníky zapíná a cyklus není uzavřen. */
function proj65_ms_add(string $ref, string $studentKey, string $title, array $taskIds = []): array
{
    $row = proj65_get($ref);
    if ($row === null || (string)$row['target_type'] !== 'group' || !proj65_is_member($row, $studentKey)) return ['ok' => false, 'error' => 'forbidden'];
    if (!proj65_settings((string)$row['class_id'], (string)$row['project_id'])['milestones']) return ['ok' => false, 'error' => 'milestones_off'];
    $title = proj65_text($title, 80);
    if (u_strlen($title) < 3) return ['ok' => false, 'error' => 'title_short'];
    $valid = array_flip(array_map(static fn(array $t): string => (string)$t['id'], project_tasks_for_group((string)$row['target_id'])));
    $tasks = array_values(array_unique(array_filter(array_map('strval', $taskIds), static fn(string $id): bool => isset($valid[$id]))));
    $out = ['ok' => false, 'error' => 'limit'];
    $groupId = (string)$row['target_id'];
    storage_update(proj65_path('milestones'), static function (array $all) use ($row, $groupId, $studentKey, $title, $tasks, &$out): array {
        $count = count(array_filter($all, static fn($m): bool => is_array($m) && (string)($m['group_id'] ?? '') === $groupId));
        if ($count >= PROJ65_MS_MAX_PER_TEAM) return $all;
        $id = 'ms65_' . bin2hex(random_bytes(6));
        $all[$id] = ['id' => $id, 'group_id' => $groupId, 'class_id' => (string)$row['class_id'], 'project_id' => (string)$row['project_id'], 'title' => $title,
            'status' => 'todo', 'task_ids' => array_slice($tasks, 0, 10), 'created_by' => $studentKey, 'created_at' => date(DATE_ATOM), 'updated_at' => date(DATE_ATOM)];
        $out = ['ok' => true, 'error' => null, 'id' => $id];
        return $all;
    });
    return $out;
}

function proj65_ms_move(string $msId, string $studentKey, string $status): array
{
    if (!in_array($status, PROJ65_MS_STATUSES, true)) return ['ok' => false, 'error' => 'invalid'];
    $out = ['ok' => false, 'error' => 'not_found'];
    storage_update(proj65_path('milestones'), static function (array $all) use ($msId, $studentKey, $status, &$out): array {
        $m = is_array($all[$msId] ?? null) ? $all[$msId] : null;
        if ($m === null) return $all;
        $group = project_group_find((string)$m['group_id']);
        if ($group === null || !in_array($studentKey, array_map('strval', (array)$group['member_keys']), true)) { $out = ['ok' => false, 'error' => 'forbidden']; return $all; }
        $all[$msId] = array_replace($m, ['status' => $status, 'updated_at' => date(DATE_ATOM)]);
        $out = ['ok' => true, 'error' => null];
        return $all;
    });
    return $out;
}

/** Podíl hotových úkolů navázaných na milník (informativní, nemění stav milníku). @return array{done:int,total:int} */
function proj65_ms_task_progress(array $milestone): array
{
    $byId = [];
    foreach (project_tasks_for_group((string)$milestone['group_id']) as $t) $byId[(string)$t['id']] = (string)($t['status'] ?? '');
    $ids = array_values(array_filter((array)($milestone['task_ids'] ?? []), static fn($id): bool => isset($byId[(string)$id])));
    return ['done' => count(array_filter($ids, static fn($id): bool => $byId[(string)$id] === 'done')), 'total' => count($ids)];
}

/** Zápis do deníku přínosu (≤ 280 znaků; nejvýš PROJ65_DIARY_PER_DAY denně na člena). */
function proj65_diary_add(string $ref, string $studentKey, string $text): array
{
    $row = proj65_get($ref);
    if ($row === null || (string)$row['target_type'] !== 'group' || !proj65_is_member($row, $studentKey)) return ['ok' => false, 'error' => 'forbidden'];
    $text = proj65_text($text, PROJ65_DIARY_MAX);
    if (u_strlen($text) < 3) return ['ok' => false, 'error' => 'text_short'];
    $today = date('Y-m-d');
    $count = count(storage_stream_rows('projects_v65_diary', static fn(array $r): bool => (string)($r['ref'] ?? '') === $ref && (string)($r['student_key'] ?? '') === $studentKey && str_starts_with((string)($r['at'] ?? ''), $today)));
    if ($count >= PROJ65_DIARY_PER_DAY) return ['ok' => false, 'error' => 'daily_limit'];
    storage_append('projects_v65_diary', ['id' => 'd65_' . bin2hex(random_bytes(6)), 'ref' => $ref, 'student_key' => $studentKey, 'text' => $text, 'at' => date(DATE_ATOM)]);
    return ['ok' => true, 'error' => null];
}

/** @return list<array<string,mixed>> záznamy deníku týmu, nejnovější první (max 100) */
function proj65_diary_list(string $ref): array
{
    $rows = storage_stream_rows('projects_v65_diary', static fn(array $r): bool => (string)($r['ref'] ?? '') === $ref);
    usort($rows, static fn(array $a, array $b): int => strcmp((string)$b['at'], (string)$a['at']));
    return array_slice($rows, 0, 100);
}

/**
 * Ověří rozdělení 100 bodů: členové týmu kromě sebe, celá nezáporná čísla, součet přesně 100.
 * @param array<string,mixed> $points @param list<string> $memberKeys
 * @return array<string,int>|null
 */
function proj65_split_validate(array $points, array $memberKeys, string $giver): ?array
{
    if (count($memberKeys) < PROJ65_TEAM_MIN_FOR_FACTOR || !in_array($giver, $memberKeys, true)) return null;
    $others = array_values(array_diff($memberKeys, [$giver]));
    $out = [];
    foreach ($others as $k) {
        $v = $points[$k] ?? null;
        if (is_string($v) && preg_match('/^\d{1,3}$/', $v) === 1) $v = (int)$v;
        if (!is_int($v) || $v < 0 || $v > 100) return null;
        $out[$k] = $v;
    }
    foreach (array_keys($points) as $k) if (!in_array((string)$k, $others, true)) return null;
    return array_sum($out) === 100 ? $out : null;
}

/** Člen uloží své rozdělení (přepíše předchozí). Jen týmy ≥ 3 členů a jen po odevzdání/před uzavřením. */
function proj65_split_save(string $ref, string $studentKey, array $points): array
{
    $out = ['ok' => false, 'error' => 'invalid'];
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $studentKey, $points, &$out): array {
        $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
        if ($row === null || (string)$row['target_type'] !== 'group' || !proj65_is_member($row, $studentKey)) return $all;
        if (!in_array((string)$row['state'], ['in_progress', 'approved', 'submitted', 'peer_review', 'graded'], true)) { $out = ['ok' => false, 'error' => 'locked']; return $all; }
        $clean = proj65_split_validate($points, proj65_member_keys($row), $studentKey);
        if ($clean === null) return $all;
        $row['splits'] = array_replace((array)($row['splits'] ?? []), [$studentKey => $clean]);
        $all[$ref] = $row;
        $out = ['ok' => true, 'error' => null];
        return $all;
    });
    return $out;
}

/**
 * Faktor přínosu členů. Null = tým < 3 členů nebo méně než 2 odevzdaná rozdělení. Faktor ∈ ⟨0,8; 1,2⟩, člen bez přijatých bodů 1,0.
 * @param array<string,array<string,int>> $splits dárce → [příjemce → body] @param list<string> $memberKeys
 * @return array<string,float>|null
 */
function proj65_contribution_factor(array $splits, array $memberKeys): ?array
{
    $n = count($memberKeys);
    $givers = array_values(array_filter(array_keys($splits), static fn($g): bool => in_array((string)$g, $memberKeys, true)));
    if ($n < PROJ65_TEAM_MIN_FOR_FACTOR || count($givers) < 2) return null;
    $share = 100 / ($n - 1);
    $out = [];
    foreach ($memberKeys as $m) {
        $got = [];
        foreach ($givers as $g) {
            if ((string)$g !== $m && isset($splits[$g][$m])) $got[] = (int)$splits[$g][$m];
        }
        $out[$m] = $got === [] ? 1.0 : round(max(PROJ65_FACTOR_MIN, min(PROJ65_FACTOR_MAX, (array_sum($got) / count($got)) / $share)), 2);
    }
    return $out;
}

/** Návrh úprav bodů členů (points_delta = round((faktor − 1) × body týmu), tedy nejvýš ±20 %). @return array<string,int>|null */
function proj65_factor_suggestion(array $record): ?array
{
    $factors = proj65_contribution_factor((array)($record['splits'] ?? []), proj65_member_keys($record));
    $grade = (string)$record['kind'] === 'cat' ? project_grade_find((string)$record['class_id'], (string)$record['project_id'], 'group', (string)$record['target_id']) : null;
    if ($factors === null || $grade === null) return null;
    $points = (int)$grade['points'];
    return array_map(static fn(float $f): int => (int)round(($f - 1.0) * $points), $factors);
}

/** Učitel potvrdí návrh: zapíše points_delta do hodnocení týmu (nová verze záznamu, stav hodnocení se nemění). */
function proj65_factor_apply(string $ref, callable $canClass): array
{
    $record = proj65_get($ref);
    if ($record === null || (string)$record['target_type'] !== 'group') return ['ok' => false, 'error' => 'not_found'];
    if (!$canClass((string)$record['class_id'])) return ['ok' => false, 'error' => 'class_out_of_scope'];
    $delta = proj65_factor_suggestion($record);
    $grade = project_grade_find((string)$record['class_id'], (string)$record['project_id'], 'group', (string)$record['target_id']);
    if ($delta === null || $grade === null) return ['ok' => false, 'error' => 'no_suggestion'];
    $adjust = (array)($grade['member_adjustments'] ?? []);
    foreach ($delta as $key => $d) {
        $adjust[$key] = array_replace(['grade_override' => null, 'comment' => ''], is_array($adjust[$key] ?? null) ? $adjust[$key] : [], ['points_delta' => $d]);
    }
    project_save_grade(['class_id' => $record['class_id'], 'project_id' => $record['project_id'], 'target_type' => 'group', 'target_id' => $record['target_id'],
        'rubric_scores' => (array)$grade['rubric_scores'], 'status' => (string)$grade['status'], 'grade' => !empty($grade['grade_overridden']) ? $grade['grade'] : null,
        'teacher_comment' => (string)$grade['teacher_comment'], 'strengths' => (string)$grade['strengths'], 'next_step' => (string)$grade['next_step'],
        'private_note' => (string)$grade['private_note'], 'member_adjustments' => $adjust]);
    unset($GLOBALS['educanet_runtime_indexes']['project_grades:index']);
    return ['ok' => true, 'error' => null];
}
