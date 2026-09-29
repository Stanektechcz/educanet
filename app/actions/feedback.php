<?php

declare(strict_types=1);

/**
 * POST akce žáka pro nahlášení chyby / návrh vylepšení (v60). Router: app/routes.php ('actions_class').
 * CSRF a třída ($classId) už zajistil index.php; identita žáka se bere výhradně ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'fb60_submit') {
    $studentKey = adaptive_student_key((string)$classId);
    $input = [
        'type' => is_string($_POST['type'] ?? null) ? (string)$_POST['type'] : '',
        'title' => is_string($_POST['title'] ?? null) ? (string)$_POST['title'] : '',
        'description' => is_string($_POST['description'] ?? null) ? (string)$_POST['description'] : '',
        'page' => is_string($_POST['page'] ?? null) ? (string)$_POST['page'] : '',
    ];
    $result = fb60_submit((string)$classId, $studentKey, $input);
    if ($result['ok']) {
        unset($_SESSION['fb60_draft']);
        $_SESSION['flash'] = tr('Díky! Hlášení je odeslané, učitel ho posoudí.');
    } else {
        // Rozepsaný text se vrátí do formuláře (jen krátce, ze session; nic se neukládá do URL).
        $_SESSION['fb60_draft'] = ['type' => $input['type'], 'title' => mb_substr($input['title'], 0, FB60_TITLE_MAX + 1), 'description' => mb_substr($input['description'], 0, FB60_DESC_MAX + 1), 'page' => fb60_page_clean($input['page'])];
        $_SESSION['flash'] = feedback60_error_text((string)$result['error']);
    }
    redirect_to('?view=hlaseni');
}
