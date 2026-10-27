<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

// CSRF validation is performed in index.php before dispatch.
if (in_array($action, ['vd82_answer', 'vd82_hint'], true)) {
    guarded_study_redirect();
    $vd82Class = current_class_id($modules);
    if ($vd82Class === null || !isset($modules[$vd82Class])) redirect_to('?view=home');
    $vd82Student = adaptive_student_key($vd82Class);
    $vd82Track = is_string($_POST['track'] ?? null) ? $_POST['track'] : '';
    $vd82Step = is_string($_POST['step'] ?? null) ? $_POST['step'] : '';
    $vd82Choice = is_string($_POST['choice'] ?? null) ? $_POST['choice'] : '';
    try {
        if (!isset(vd82_catalog()[$vd82Track]['steps'][$vd82Step])) throw new RuntimeException('Neznámý krok dílny.');
        if ($action === 'vd82_hint') {
            vd82_save_hint($vd82Class, $vd82Student, $vd82Track, $vd82Step);
            $_SESSION['flash'] = 'Nápověda je otevřená. Bez penalizace.';
        } else {
            if (strlen($vd82Choice) > 32) throw new RuntimeException('Neplatná možnost.');
            [, $vd82Message] = vd82_save_choice($vd82Class, $vd82Student, $vd82Track, $vd82Step, $vd82Choice);
            $_SESSION['flash'] = $vd82Message;
        }
    } catch (Throwable $e) {
        error_log('EDUCANET Virtuální dílna: ' . $e->getMessage());
        $_SESSION['flash'] = $e instanceof RuntimeException ? $e->getMessage() : 'Zápis se nezdařil. Zkus to znovu.';
    }
    redirect_to('?view=virtualni_dilna&mod=' . ($vd82Track === 'sit' ? 'sit' : 'pc'));
}
