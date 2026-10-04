<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/paths_v63_class.php';

/**
 * EDUCANET v63 · cockpit učitele: záložka „Výukové cesty“ (vždy česky).
 *
 * Přiřazení cest třídě (POST p63_assign / p63_unassign s CSRF; třída musí být v rozsahu učitele – vynucuje
 * teacher_scope_v59.php a handler to kontroluje znovu), trychtýř kroků (počty žáků, kteří krok zkusili a splnili,
 * bez jmen) a průměrná kalibrace sebehodnocení (až od 3 reflexí). Reflexní věty žáků učitel nevidí nikdy.
 * Žák smí dělat i nepřiřazené cesty své třídy; přiřazení je jen doporučení s prioritou na přehledu.
 */

/** @return list<string> třídy s cestami v rozsahu přihlášeného učitele */
function p63_teacher_classes(): array
{
    $all = function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : array_keys(P63_CLASS_FILES);
    return array_values(array_filter($all, 'p63_enabled_for_class'));
}

function p63_teacher_can_class(string $classId): bool
{
    return $classId !== '' && in_array($classId, p63_teacher_classes(), true);
}

/** POST p63_assign / p63_unassign: třída v rozsahu a cesta patřící třídě, jinak výjimka (handler teacher.php ji zobrazí). */
function p63_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    $pathId = is_string($_POST['path'] ?? null) ? (string)$_POST['path'] : '';
    if (!in_array($action, ['p63_assign', 'p63_unassign'], true)) throw new RuntimeException('Neznámá akce cest.');
    if (!p63_teacher_can_class($classId)) throw new RuntimeException('Třída není v rozsahu nebo nemá výukové cesty.');
    if (p63_path_for_class($classId, $pathId) === null) throw new RuntimeException('Cesta nepatří této třídě.');
    $ok = $action === 'p63_assign' ? p63_assign($classId, $pathId, p63_teacher_hash()) : p63_unassign($classId, $pathId);
    if (function_exists('teacher_flash')) {
        teacher_flash($ok ? ($action === 'p63_assign' ? 'Cesta je přiřazená třídě.' : 'Přiřazení cesty je zrušené.') : 'Změnu se nepodařilo uložit.', $ok ? 'ok' : 'error');
    }
    if (function_exists('teacher_redirect')) teacher_redirect(['tab' => 'cesty', 'class' => $classId]);
    redirect_to('teacher.php?tab=cesty&class=' . rawurlencode($classId));
}

function p63_teacher_assign_form(string $classId, string $pathId, bool $assigned, string $csrf): string
{
    $action = $assigned ? 'p63_unassign' : 'p63_assign';
    return '<form method="post" class="p63-t-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '"><input type="hidden" name="path" value="' . e($pathId) . '">'
        . '<button type="submit">' . ($assigned ? 'Zrušit přiřazení' : 'Přiřadit třídě') . '</button></form>';
}

/** Trychtýř kroků jedné cesty: kolik žáků krok zkusilo a kolik ho splnilo (bez jmen). */
function p63_teacher_funnel_table(array $path, array $row, int $students): string
{
    $html = '<div class="p63-t-scroll" tabindex="0" role="region" aria-label="Trychtýř kroků, posuňte vodorovně"><table class="p63-t-table"><caption>Trychtýř kroků: ' . e((string)$path['title']) . ' (žáků ve třídě: ' . $students . ')</caption>'
        . '<thead><tr><th scope="col">Krok</th><th scope="col">Zkusilo</th><th scope="col">Splnilo</th><th scope="col">Podíl splnivších</th></tr></thead><tbody>';
    foreach ((array)$path['steps'] as $step) {
        $counts = (array)($row['steps'][(string)$step['id']] ?? ['tried' => 0, 'done' => 0]);
        $pct = $students > 0 ? (int)round((int)$counts['done'] / $students * 100) : 0;
        $html .= '<tr><th scope="row">' . e((string)$step['title']) . '</th><td>' . (int)$counts['tried'] . '</td><td>' . (int)$counts['done'] . '</td><td>' . $pct . ' %</td></tr>';
    }
    return $html . '</tbody></table></div>';
}

/** Průměrná kalibrace za cestu: bez jmen, pod 3 reflexemi jen informace o malém počtu. */
function p63_teacher_calibration_html(array $cal): string
{
    $n = (int)($cal['n'] ?? 0);
    if ($n < P63_CALIBRATION_MIN) return '<p class="p63-t-note">Kalibrace: zatím málo reflexí (' . $n . '), souhrn se ukáže od ' . P63_CALIBRATION_MIN . '.</p>';
    $delta = (float)$cal['avg_delta'];
    $verdict = $delta >= 0.5 ? 'třída si spíš věří víc, než ukazuje ověření' : ($delta <= -0.5 ? 'třída se spíš podceňuje' : 'odhady žáků zhruba sedí s ověřením');
    return '<p class="p63-t-note">Kalibrace (' . $n . ' reflexí): průměrný odhad žáků ' . e(number_format((float)$cal['avg_self'], 1, ',', '')) . ', průměr z ověření ' . e(number_format((float)$cal['avg_actual'], 1, ',', ''))
        . ' (stupnice 1–4); přeceňuje se ' . (int)$cal['over'] . ', podceňuje ' . (int)$cal['under'] . ', sedí ' . (int)$cal['match'] . '. Závěr: ' . e($verdict) . '.</p>';
}

function p63_teacher_path_section(string $classId, array $path, array $assigned, array $stats, string $csrf): string
{
    $pathId = (string)$path['id'];
    $isAssigned = isset($assigned[$pathId]);
    $row = (array)($stats['paths'][$pathId] ?? ['started' => 0, 'finished' => 0, 'steps' => [], 'calibration' => ['n' => 0]]);
    $when = $isAssigned ? ' · přiřazeno ' . e(date('j. n. Y', (int)($assigned[$pathId]['assigned_at'] ?? 0))) : '';
    return '<section class="p63-t-path"><h3>' . e((string)$path['title']) . ($isAssigned ? ' <span class="p63-t-badge">přiřazeno</span>' : '') . '</h3>'
        . '<p class="p63-t-note">' . e((string)$path['goal']) . ' · ' . (int)$path['minutes'] . ' min' . $when . ' · začalo ' . (int)$row['started'] . ', dokončilo ' . (int)$row['finished'] . ' z ' . (int)$stats['students'] . ' žáků.</p>'
        . p63_teacher_assign_form($classId, $pathId, $isAssigned, $csrf) . p63_teacher_funnel_table($path, $row, (int)$stats['students']) . p63_teacher_calibration_html((array)$row['calibration']) . '</section>';
}

function p63_render_teacher_tab(string $requestedClass, string $csrf): void
{
    $classes = p63_teacher_classes();
    echo '<section class="teacher-panel p63-t-wrap"><h2>Výukové cesty</h2>';
    if ($classes === []) {
        echo '<p class="teacher-empty">Výukové cesty zatím existují pro 1.A a 3.A a vy k nim nemáte přístup.</p></section>';
        return;
    }
    $classId = in_array($requestedClass, $classes, true) ? $requestedClass : $classes[0];
    $links = '';
    foreach ($classes as $c) $links .= '<a class="' . ($c === $classId ? 'active' : '') . '" href="?tab=cesty&class=' . e(rawurlencode($c)) . '"' . ($c === $classId ? ' aria-current="page"' : '') . '>' . e(p63_class_label($c)) . '</a> ';
    echo '<nav class="p63-t-classes" aria-label="Třída">' . $links . '</nav>';
    echo '<p class="p63-t-note">Cesta vede žáka krokem vysvětlení, cvičení, ověření a reflexe a zapisuje důkazy do mapy kompetencí. Žák smí dělat i nepřiřazené cesty; přiřazená se mu nabídne přednostně na přehledu. Cesty nedávají XP ani body. '
        . 'Reflexní větu žáka vidí jen žák, vy dostáváte pouze souhrn.</p>';
    $stats = p63_class_stats($classId);
    $assigned = p63_assignments($classId);
    foreach (p63_paths_for_class($classId) as $path) echo p63_teacher_path_section($classId, $path, $assigned, $stats, $csrf);
    echo '</section>';
}
