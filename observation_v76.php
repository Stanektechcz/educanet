<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · ruční důkaz kompetence učitelem jedním klikem (zdroj `observation`, záložka Kompetence).
 *
 * Učitel vybere žáka a kompetenci a jedním tlačítkem zapíše, co v hodině viděl: „umí“ (1,0) / „učí se“ (0,5) / „zatím ne“ (0,0).
 * Zápis = řádek důkazu v62 (append-only, ev62_append): kompetence, cílová úroveň z katalogu, skóre, čas a odkaz `ob76:<čas>:<hash učitele>`
 * – žádný volný text ani jméno (soukromí nezletilých). Váha zdroje v mastery_v62.php; do návrhů známek v66 nevstupuje.
 * POST ob76_add: CSRF v teacher.php, politika teacher59 (třída povinná a v rozsahu), oprávnění grading.manage (asistent nezapisuje);
 * handler ověří třídu v pilotu kompetencí, žáka v seznamu třídy (pořadí, ne jméno v URL) a kompetenci v katalogu. Jiné ob76_* zakázané.
 */

const OB76_ACTIONS = ['ob76_add'];
const OB76_RESULTS = ['umi' => ['score' => 1.0, 'label' => 'umí'], 'uci' => ['score' => 0.5, 'label' => 'učí se'], 'zatim' => ['score' => 0.0, 'label' => 'zatím ne']];

/** Žáci třídy seřazení podle jména (stejné pořadí jako mapa kompetencí): list<['label','id']>. */
function ob76_students(string $classId): array
{
    $rows = [];
    foreach (project_students_for_class($classId) as $s) {
        $id = identity58_id_for_student($classId, (string)$s['label']);
        $rows[] = ['label' => (string)$s['label'], 'id' => is_string($id) ? $id : ''];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($a['label'], $b['label']));
    return $rows;
}

/**
 * Zapíše pozorování (čistá validace + ev62_append). $index = pořadí žáka (1…), $result ∈ OB76_RESULTS.
 * @return array{competency:string,result:string,added:int}
 */
function ob76_record(string $classId, int $index, string $competency, string $result, string $teacherKey, ?int $now = null): array
{
    if (!comp62_enabled_for_class($classId)) throw new RuntimeException('Třída není zapojená do kompetencí.');
    $catalog = comp62_competencies((string)comp62_subject_for_class($classId));
    if (!isset($catalog[$competency])) throw new RuntimeException('Neznámá kompetence.');
    if (!isset(OB76_RESULTS[$result])) throw new RuntimeException('Vyberte, co jste viděli: umí, učí se, nebo zatím ne.');
    $students = ob76_students($classId);
    $student = $students[$index - 1] ?? null;
    if ($student === null || $student['id'] === '' || !ev62_valid_id($student['id'])) throw new RuntimeException('Žák nebyl nalezen v seznamu třídy (nebo nemá stabilní identitu).');
    $now ??= time();
    $ref = 'ob76:' . date('YmdHis', $now) . ':' . substr(hash('sha256', 'ob76|' . $teacherKey), 0, 8);
    $row = ['competency' => $competency, 'level' => max(1, min(4, (int)($catalog[$competency]['level'] ?? 2))), 'source' => 'observation',
        'score' => OB76_RESULTS[$result]['score'], 'at' => date(DATE_ATOM, $now), 'artefact_ref' => $ref];
    $stat = ev62_append($student['id'], [$row]);
    if (function_exists('ev62_memo_reset')) ev62_memo_reset();
    return ['competency' => $competency, 'result' => $result, 'added' => (int)$stat['added']];
}

/** POST ob76_add (politiku, CSRF a oprávnění už ověřil teacher.php). */
function ob76_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!in_array($action, OB76_ACTIONS, true)) throw new RuntimeException('Neznámá akce.');
    if (!function_exists('comp62_teacher_can_class') || !comp62_teacher_can_class($classId)) throw new RuntimeException('Třída není v rozsahu nebo není zapojená do kompetencí.');
    $index = is_string($_POST['zak'] ?? null) && preg_match('/^\d{1,3}$/', (string)$_POST['zak']) === 1 ? (int)$_POST['zak'] : 0;
    $comp = is_string($_POST['competency'] ?? null) ? (string)$_POST['competency'] : '';
    $result = is_string($_POST['vysledek'] ?? null) ? (string)$_POST['vysledek'] : '';
    $teacherKey = function_exists('teacher59_owner_key') ? (string)(teacher59_owner_key() ?? 'legacy') : 'legacy';
    $res = ob76_record($classId, $index, $comp, $result, $teacherKey);
    $label = (string)(comp62_competencies((string)comp62_subject_for_class($classId))[$comp]['label'] ?? $comp);
    $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $res['added'] > 0
        ? 'Pozorování zapsáno: ' . OB76_RESULTS[$result]['label'] . ' – ' . $label . ' (žák č. ' . $index . ' v seznamu). Stav kompetence se přepočítá z posledních důkazů.'
        : 'Stejné pozorování už je zapsané – nic nového se nepřidalo.'];
    if (function_exists('teacher_redirect')) teacher_redirect(['tab' => 'kompetence', 'class' => $classId]);
    redirect_to('teacher.php?tab=kompetence&class=' . rawurlencode($classId));
}

/** Formulář „Zapsat pozorování“ pod mapou kompetencí (jen s oprávněním grading.manage). */
function ob76_form_html(string $classId, string $csrf, array $competencies): string
{
    if (!function_exists('teacher_permission') || !teacher_permission('grading.manage')) {
        return '<p class="c62-note">Ruční pozorování zapisují učitelé třídy (asistent mapu jen čte).</p>';
    }
    $students = ob76_students($classId);
    if ($students === []) return '<p class="c62-note">Ve třídě zatím nejsou žáci – pozorování půjde zapsat, až budou v seznamu.</p>';
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $opts = '';
    foreach ($students as $i => $s) {
        $label = function_exists('tp721_student_label') ? tp721_student_label($s['label'], $i + 1, $hidden) : $s['label'];
        $opts .= '<option value="' . ($i + 1) . '"' . ($s['id'] === '' ? ' disabled' : '') . '>' . e($label . ($s['id'] === '' ? ' (bez identity)' : '')) . '</option>';
    }
    $comps = '';
    foreach ($competencies as $id => $c) $comps .= '<option value="' . e((string)$id) . '">' . e((string)$c['label']) . '</option>';
    $buttons = '';
    foreach (OB76_RESULTS as $key => $r) $buttons .= '<button type="submit" name="vysledek" value="' . e($key) . '" class="btn ' . ($key === 'umi' ? 'primary' : 'secondary') . '">' . e('Zapsat: ' . $r['label']) . '</button>';
    return '<section class="c70-card ob76" aria-labelledby="ob76-h"><h3 id="ob76-h">Zapsat pozorování jedním klikem</h3>'
        . '<p class="c62-note">Viděli jste v hodině, že žák kompetenci zvládá (nebo ještě ne)? Vyberte žáka a kompetenci a klikněte na výsledek. '
        . 'Zápis je důkaz „pozorování učitele“ bez poznámky a jména v datech; stav kompetence bere posledních 8 důkazů, takže pozdější pozorování starší přepíše.</p>'
        . '<form method="post" class="ob76-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="ob76_add">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '"><div class="ob76-row">'
        . '<label for="ob76-zak">Žák<br><select id="ob76-zak" name="zak" required>' . $opts . '</select></label>'
        . '<label for="ob76-comp">Kompetence<br><select id="ob76-comp" name="competency" required>' . $comps . '</select></label></div>'
        . '<div class="ob76-row">' . $buttons . '</div></form></section>';
}
