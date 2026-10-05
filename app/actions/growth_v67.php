<?php

declare(strict_types=1);

/**
 * v67 · profil žáka: cíle a sdílení (POST grow67_goal_add, grow67_goal_check, grow67_goal_remove, grow67_share_set).
 * CSRF už ověřil index.php (verify_csrf). Identita žáka je vždy ze session (adaptive_student_key); z formuláře se čte jen
 * kompetence/cíl/id cíle, které growth_v67.php znovu validuje (katalog, regulární výraz, limit tří cílů).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (str_starts_with($action, 'grow67_')) {
    $grow67Key = adaptive_student_key((string)$classId);
    $grow67Post = static fn(string $name): string => is_string($_POST[$name] ?? null) ? trim((string)$_POST[$name]) : '';
    $grow67Result = ['ok' => false, 'error' => 'identity'];
    $grow67Ok = tr('Uloženo.');
    if (!grow67_enabled((string)$classId) || $grow67Key === '') {
        $_SESSION['flash'] = tr('Tahle část profilu zatím pro tvoji třídu není.');
        redirect_to('?view=profile');
    }
    switch ($action) {
        case 'grow67_goal_add':
            $grow67Result = grow67_goal_add((string)$classId, $grow67Key, $grow67Post('competency'), $grow67Post('target'));
            $grow67Ok = tr('Cíl je přidaný.');
            break;
        case 'grow67_goal_check':
            $grow67Result = grow67_goal_check((string)$classId, $grow67Key, $grow67Post('goal'));
            $grow67Ok = tr('Kontrola zapsaná.');
            break;
        case 'grow67_goal_remove':
            $grow67Result = grow67_goal_remove((string)$classId, $grow67Key, $grow67Post('goal'));
            $grow67Ok = tr('Cíl je odebraný.');
            break;
        case 'grow67_share_set':
            $grow67Result = grow67_share_set((string)$classId, $grow67Key, $grow67Post('share_timeline') === '1', $grow67Post('share_competencies') === '1');
            $grow67Ok = tr('Sdílení je uložené.');
            break;
    }
    $grow67Errors = ['max' => tr('Můžeš mít nejvýš tři cíle.'), 'duplicate' => tr('Na tuhle kompetenci už cíl máš.'), 'unknown_competency' => tr('Tuhle kompetenci neznám.'), 'bad_target' => tr('Neplatný cíl.')];
    $_SESSION['flash'] = !empty($grow67Result['ok']) ? $grow67Ok : ($grow67Errors[(string)($grow67Result['error'] ?? '')] ?? tr('Nepodařilo se uložit.'));
    redirect_to('?view=profile&tab=rust');
}
