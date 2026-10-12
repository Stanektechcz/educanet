<?php

declare(strict_types=1);

/**
 * POST akce žáka v75: tb75_answer / tb75_next (úlohy s variantami), dif75_pick (úroveň aktivity v lekci), pf75_s_pin /
 * pf75_s_reflect / pf75_s_unpin (portfolio). Router: app/routes.php ('actions_class'). CSRF a třídu ($classId) už zajistil index.php;
 * identita žáka je výhradně ze session (adaptive_student_key), lekce se ověří proti kalendáři. Žádná data žáka do URL.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (in_array($action, ['tb75_answer', 'tb75_next'], true)) {
    tb75_handle_post($action, (string)$classId);
}

if ($action === 'dif75_pick') {
    $lv75No = is_string($_POST['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_POST['lesson']) === 1 ? max(1, min(28, (int)$_POST['lesson'])) : 0;
    if ($lv75No < 1 || $lv75No > v56_current_lesson_number((string)$classId, $schoolYear)) {
        $_SESSION['flash'] = tr('Úroveň si vybereš v den, kdy lekci budete mít.');
        redirect_to('?view=materialy');
    }
    $lv75Today = sess53_for_class_date((string)$classId, date('Y-m-d'));
    $lv75Back = is_array($lv75Today) && (int)($lv75Today['lesson_number'] ?? 0) === $lv75No && (string)($lv75Today['kind'] ?? '') === 'work' ? '?view=hodina' : v56_lesson_url($lv75No);
    lv75_handle_post((string)$classId, $lv75No, $lv75Back);
}

if (in_array($action, ['pf75_s_pin', 'pf75_s_reflect', 'pf75_s_unpin'], true)) {
    pf75_handle_student_post($action, (string)$classId);
}
