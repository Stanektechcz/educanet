<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Portfolio žáka: výběr prací + krátká reflexe + lokální export (HTML soubor, tisk do PDF). ŽÁDNÉ sdílení odkazem.
 *
 * Soubor storage/portfolio_v65/<stu_id>.json.php = {"v":1,"saved":bool,"items":{klíč => {selected, reflection ≤ 600, updated_at}}}.
 * Klíč položky: c:<id cyklu> (ohodnocený projekt z v65) nebo f:<id týmu> (Featured projekt z project_workspace, max 3).
 * Dokud žák výběr nikdy neuložil, výchozí výběr tvoří jeho Featured projekty. Retence: viz projects_v65_evidence.php (30 dní po odchodu).
 * Export nikdy neobsahuje soukromou poznámku učitele ani texty peer review.
 */

const PORT65_REFLECTION_MAX = 600;
const PORT65_MAX_SELECTED = 12;

function port65_path(string $studentId): string
{
    if (!ev62_valid_id($studentId)) throw new InvalidArgumentException('Neplatné student_id.');
    return STORAGE_DIR . '/portfolio_v65/' . $studentId . '.json.php';
}

/** student_id přes identity_v58, nebo null (neznámý žák – portfolio se pak neukládá). */
function port65_student_id(string $classId, string $studentKey): ?string
{
    $ctx = function_exists('ev62_student_context') ? ev62_student_context($classId, $studentKey) : null;
    return $ctx === null ? null : (string)$ctx['id'];
}

/** @return list<array<string,mixed>> položky, které žák může zařadit (ohodnocené v65 projekty + Featured týmové projekty) */
function port65_candidates(string $classId, string $studentKey): array
{
    $out = [];
    foreach (proj65_for_student($classId, $studentKey) as $row) {
        $versions = array_values(array_filter((array)($row['versions'] ?? []), 'is_array'));
        if ($versions === [] || !in_array((string)$row['state'], ['graded', 'portfolio'], true)) continue;
        $last = $versions[count($versions) - 1];
        $grade = (string)$row['kind'] === 'cat' ? project_grade_find($classId, (string)$row['project_id'], (string)$row['target_type'], (string)$row['target_id']) : null;
        $published = $grade !== null && in_array((string)$grade['status'], ['published', 'returned'], true);
        $out[] = ['key' => 'c:' . (string)$row['id'], 'type' => 'cycle', 'ref' => (string)$row['ref'], 'title' => (string)$row['title'], 'levels' => (array)$last['levels'],
            'rubric' => proj65_rubric_for($row), 'strengths' => $published ? (string)$grade['strengths'] : '', 'next_step' => $published ? (string)$grade['next_step'] : '', 'at' => (string)$last['published_at']];
    }
    foreach (project_portfolio_entries($classId, $studentKey, true) as $f) {
        $out[] = ['key' => 'f:' . (string)$f['group_id'], 'type' => 'featured', 'ref' => '', 'title' => (string)$f['project_title'], 'levels' => [], 'rubric' => null,
            'strengths' => '', 'next_step' => '', 'at' => (string)$f['published_at'], 'roles' => (array)$f['roles'], 'skills' => array_slice((array)$f['skills'], 0, 6), 'contributions' => (array)$f['contributions']];
    }
    return $out;
}

/** Uložený výběr, nebo výchozí (Featured), dokud žák nic neuložil. @return array<string,array{selected:bool,reflection:string}> */
function port65_selection(?string $studentId, array $candidates): array
{
    $data = $studentId !== null ? storage_read(port65_path($studentId), false) : [];
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
    $saved = !empty($data['saved']);
    $out = [];
    foreach ($candidates as $c) {
        $row = is_array($items[$c['key']] ?? null) ? $items[$c['key']] : null;
        $out[(string)$c['key']] = ['selected' => $saved ? !empty($row['selected']) : ((string)$c['type'] === 'featured'), 'reflection' => (string)($row['reflection'] ?? '')];
    }
    return $out;
}

/** @return array{ok:bool,error:?string} */
function port65_save_item(string $classId, string $studentKey, string $key, bool $selected, string $reflection): array
{
    $studentId = port65_student_id($classId, $studentKey);
    if ($studentId === null) return ['ok' => false, 'error' => 'identity'];
    $candidates = port65_candidates($classId, $studentKey);
    $match = array_values(array_filter($candidates, static fn(array $c): bool => $c['key'] === $key))[0] ?? null;
    if ($match === null) return ['ok' => false, 'error' => 'not_found'];
    $reflection = proj65_text($reflection, PORT65_REFLECTION_MAX);
    $current = port65_selection($studentId, $candidates);
    $out = ['ok' => true, 'error' => null];
    storage_update(port65_path($studentId), static function (array $d) use ($key, $selected, $reflection, $current, &$out): array {
        $items = [];
        foreach ($current as $k => $row) $items[$k] = ['selected' => $row['selected'], 'reflection' => $row['reflection']];
        $items[$key] = ['selected' => $selected, 'reflection' => $reflection, 'updated_at' => date(DATE_ATOM)];
        if (count(array_filter($items, static fn(array $i): bool => !empty($i['selected']))) > PORT65_MAX_SELECTED) { $out = ['ok' => false, 'error' => 'too_many']; return $d; }
        return ['v' => 1, 'saved' => true, 'items' => $items];
    });
    if ($out['ok'] && (string)$match['type'] === 'cycle') proj65_move((string)$match['ref'], $selected ? 'portfolio' : 'graded', 'student', $studentKey);
    return $out;
}

/** Vybrané položky s reflexí (pro stránku i export) – nikdy peer texty ani soukromá poznámka. @return list<array<string,mixed>> */
function port65_view_model(string $classId, string $studentKey): array
{
    $candidates = port65_candidates($classId, $studentKey);
    $sel = port65_selection(port65_student_id($classId, $studentKey), $candidates);
    $out = [];
    foreach ($candidates as $c) {
        if (empty($sel[$c['key']]['selected'])) continue;
        $out[] = $c + ['reflection' => $sel[$c['key']]['reflection']];
    }
    return $out;
}
