<?php

declare(strict_types=1);

/**
 * ?view=materialy, vysledky, lekce (v56).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'materialy') {
    guarded_study_redirect();
    $v56Index = v56_materials_index((string)$classId, $module, $nextLessons, $extendedLessons, $schoolYear);
    v56_render_materials((string)$classId, $module, $v56Index, (string)($_GET['sekce'] ?? 'lekce'), (array)($simulations[$classId] ?? []), $flash);
    exit;
}
if ($view === 'vysledky') {
    guarded_study_redirect();
    $v56Index = v56_materials_index((string)$classId, $module, $nextLessons, $extendedLessons, $schoolYear);
    v56_render_results((string)$classId, $module, $v56Index, $flash);
    exit;
}
if ($view === 'lekce') {
    guarded_study_redirect();
    $v56No = max(1, min(28, (int)($_GET['n'] ?? v56_current_lesson_number((string)$classId, $schoolYear))));
    $v56Max = v56_current_lesson_number((string)$classId, $schoolYear);
    if ($v56No > $v56Max) { $_SESSION['flash'] = tr('Lekce {n} se otevře až v den, kdy ji budete mít.', ['n' => $v56No]); redirect_to('?view=materialy'); }
    $v56Bundle = v56_lesson_bundle((string)$classId, $module, $v56No, $nextLessons, $extendedLessons);
    v56_render_lesson((string)$classId, $module, $v56Bundle, null, $schoolYear, $flash, (string)($_GET['faze'] ?? ''));
    exit;
}
