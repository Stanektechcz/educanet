<?php

declare(strict_types=1);

/**
 * POST akce žáka pro projekty podle levelu (v60). Router: app/routes.php ('actions_class').
 * CSRF a třída/identita žáka ($classId, $module) už zajistil index.php.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'proj60_apply') {
    $studentKey = adaptive_student_key((string)$classId);
    $projectId = is_string($_POST['project_id'] ?? null) ? (string)$_POST['project_id'] : '';
    $motivation = is_string($_POST['motivation'] ?? null) ? (string)$_POST['motivation'] : '';
    // Level se počítá vždy ze session žáka – nikdy z formuláře.
    $level = (int)learning_level((int)(learning_profile((string)$classId)['xp'] ?? 0))['level'];
    $result = proj60_apply((string)$classId, $studentKey, $projectId, $motivation, $level);
    $_SESSION['flash'] = $result['ok'] ? tr('Přihláška byla odeslána.') : tr('Přihlášku se nepodařilo odeslat.');
    redirect_to('?view=projekty');
}

if ($action === 'proj60_withdraw') {
    $studentKey = adaptive_student_key((string)$classId);
    $applicationId = is_string($_POST['application_id'] ?? null) ? (string)$_POST['application_id'] : '';
    $ok = proj60_withdraw((string)$classId, $studentKey, $applicationId);
    $_SESSION['flash'] = $ok ? tr('Přihláška byla stažena.') : tr('Přihlášku se nepodařilo stáhnout.');
    redirect_to('?view=projekty');
}
