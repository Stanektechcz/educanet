<?php

declare(strict_types=1);

/**
 * POST submit_extra a submit_graphics (vyžaduje třídu).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'submit_extra') {
    $extra = $module['practice']['extra'] ?? null;
    $practiceResult = $_SESSION['next_practice_result'] ?? null;
    if (!is_array($extra) || !is_array($practiceResult) || ($practiceResult['class_id'] ?? null) !== $classId) {
        $_SESSION['flash'] = tr('Extra challenge se odemkne až po dokončení hlavní praktické části.');
        redirect_to('?view=dashboard');
    }
    guarded_study_redirect();
    $responses = [];
    foreach (($extra['fields'] ?? []) as $field) {
        $name = (string)($field['name'] ?? '');
        $label = (string)($field['label'] ?? $name);
        $min = max(1, (int)($field['min'] ?? 20));
        if ($name === '') {
            continue;
        }
        $value = trim((string)($_POST[$name] ?? ''));
        if (u_strlen($value) < $min) {
            $_SESSION['flash'] = tr('Doplň pole „{label}“ alespoň na {min} znaků.', ['label' => $label, 'min' => (string)$min]);
            redirect_to('?view=extra_challenge#submit');
        }
        $responses[$name] = u_substr($value, 0, 6000);
    }
    if (!$responses) {
        $_SESSION['flash'] = tr('Odevzdání neobsahuje žádné odpovědi.');
        redirect_to('?view=extra_challenge#submit');
    }
    $submission = [
        'id' => 'extra_' . bin2hex(random_bytes(8)),
        'class_id' => $classId,
        'student_label' => (string)($_SESSION['student_label'] ?? 'Anonymní student'),
            'student_email' => (string)(auth_user()['email'] ?? ''),
            'auth_key' => (string)(auth_user()['auth_key'] ?? ''),
            'google_sub' => ((auth_user()['provider'] ?? '') === 'google') ? (string)(auth_user()['sub'] ?? '') : '',
        'submitted_at' => date(DATE_ATOM),
        'challenge_title' => (string)($extra['title'] ?? 'Extra challenge'),
        'requested_grading' => true,
        'max_points' => (int)($extra['max_points'] ?? 0),
        'points' => null,
        'grade' => null,
        'graded_at' => null,
        'status' => 'awaiting_teacher_grading',
        'responses' => $responses,
    ];
    storage_append('extra_submissions', $submission);
    $_SESSION['next_extra_submission'] = $submission;
    $_SESSION['flash'] = tr('Extra challenge byl odevzdán k hodnocení.');
    redirect_to('?view=extra_challenge#submit');
}

if ($action === 'submit_graphics') {
    if (!in_array($classId, ['class_1a', 'class_2a'], true)) {
        redirect_to('?view=dashboard');
    }
    guarded_study_redirect();
    if (!learning_studio_complete($classId)) {
        $_SESSION['flash'] = tr('Nejdřív dokonči všechny povinné kroky Design Studia a Canva handoffu.');
        redirect_to('?view=graphics_studio');
    }
    $reflection = trim((string)($_POST['reflection'] ?? ''));
    $title = trim((string)($_POST['project_title'] ?? ''));
    $checklist = $_POST['checklist'] ?? [];
    if (!is_array($checklist)) {
        $checklist = [];
    }
    if ($title === '' || u_strlen($reflection) < 20) {
        $_SESSION['flash'] = tr('Doplň název práce a krátkou reflexi alespoň 20 znaků.');
        redirect_to('?view=graphics_guide#submit');
    }
    $requiredChecklist = count($module['guide']['checklist'] ?? []);
    if ($requiredChecklist > 0 && count(array_unique(array_map('strval', $checklist))) < $requiredChecklist) {
        $_SESSION['flash'] = tr('Před odevzdáním musíš projít a potvrdit celý kontrolní checklist.');
        redirect_to('?view=graphics_guide#submit');
    }
    if (!isset($_FILES['artifact']) || !is_array($_FILES['artifact']) || ($_FILES['artifact']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $_SESSION['flash'] = tr('Vyber finální soubor k odevzdání.');
        redirect_to('?view=graphics_guide#submit');
    }
    $file = $_FILES['artifact'];
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        $_SESSION['flash'] = tr('Soubor je větší než 12 MB.');
        redirect_to('?view=graphics_guide#submit');
    }
    $tmp = (string)$file['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmp);
    $allowed = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];
    if (!isset($allowed[$mime])) {
        $_SESSION['flash'] = tr('Povolené formáty jsou PNG, JPG, WEBP a PDF.');
        redirect_to('?view=graphics_guide#submit');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0770, true);
    }
    $storageName = 'graphics_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime] . '.upload';
    $target = UPLOAD_DIR . '/' . $storageName;
    if (!move_uploaded_file($tmp, $target)) {
        $_SESSION['flash'] = tr('Soubor se nepodařilo uložit.');
        redirect_to('?view=graphics_guide#submit');
    }
    $submission = [
        'id' => 'graphics_' . bin2hex(random_bytes(8)),
        'class_id' => $classId,
        'student_label' => (string)($_SESSION['student_label'] ?? 'Anonymní student'),
            'student_email' => (string)(auth_user()['email'] ?? ''),
            'auth_key' => (string)(auth_user()['auth_key'] ?? ''),
            'google_sub' => ((auth_user()['provider'] ?? '') === 'google') ? (string)(auth_user()['sub'] ?? '') : '',
        'submitted_at' => date(DATE_ATOM),
        'project_title' => u_substr($title, 0, 160),
        'reflection' => u_substr($reflection, 0, 2000),
        'checklist' => array_values(array_map('strval', array_slice($checklist, 0, 20))),
        'artifact' => [
            'storage_name' => $storageName,
            'original_name' => u_substr((string)$file['name'], 0, 220),
            'mime' => $mime,
            'size' => (int)$file['size'],
        ],
    ];
    storage_append('graphics_submissions', $submission);
    learning_award_once($classId, 'graphics:submit', 75);
    $_SESSION['flash'] = tr('Hotovo – práce byla odevzdána. Získáváš XP za dokončení projektu.');
    redirect_to('?view=graphics_guide#submit');
}
