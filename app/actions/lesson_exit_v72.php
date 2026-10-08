<?php

declare(strict_types=1);

/**
 * POST lx72_answer – odpověď žáka na exit ticket schválené lekce (v72, formativní).
 * Router: app/routes.php ('actions_class'). CSRF a třídu ($classId) už ověřil index.php; identita žáka je výhradně
 * ze session (adaptive_student_key), varianta otázky se určí na serveru. Lekce musí být odemčená podle kalendáře.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'lx72_answer') {
    $lx72No = is_string($_POST['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_POST['lesson']) === 1 ? max(1, min(28, (int)$_POST['lesson'])) : 1;
    $lx72Today = sess53_for_class_date((string)$classId, date('Y-m-d'));
    $lx72Back = is_array($lx72Today) && (int)($lx72Today['lesson_number'] ?? 0) === $lx72No && (string)($lx72Today['kind'] ?? '') === 'work'
        ? '?view=hodina#exit-ticket' : v56_lesson_url($lx72No) . '#exit-ticket';
    $lx72Key = adaptive_student_key((string)$classId);
    $lx72Choice = is_string($_POST['choice'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_POST['choice']) === 1 ? (int)$_POST['choice'] : -1;
    if ($lx72Key === '') { $_SESSION['flash'] = tr('Nepodařilo se určit studentský profil.'); redirect_to('?view=dashboard'); }
    if ($lx72No > v56_current_lesson_number((string)$classId, $schoolYear)) {
        $_SESSION['flash'] = tr('Lekce {n} se otevře až v den, kdy ji budete mít.', ['n' => $lx72No]);
        redirect_to('?view=materialy');
    }
    try {
        $lx72Res = lx72_submit((string)$classId, $lx72Key, $lx72No, $lx72Choice);
        $_SESSION['flash'] = match ($lx72Res['error']) {
            '' => tr('Odpověď je uložená. Díky!'),
            'duplicate' => tr('Na exit ticket už máš odpověď.'),
            'choice' => tr('Vyber jednu odpověď.'),
            default => tr('Exit ticket pro tuto lekci teď není k dispozici.'),
        };
    } catch (Throwable $e) {
        error_log('EDUCANET v72 exit ticket: ' . get_class($e));
        $_SESSION['flash'] = tr('Odpověď se nepodařilo uložit. Zkus to prosím znovu.');
    }
    redirect_to($lx72Back);
}
