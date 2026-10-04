<?php

declare(strict_types=1);

/**
 * POST akce motivace (v61): oblíbené položky obchodu. Router: app/routes.php ('actions_class').
 * CSRF a třída/identita žáka ($classId) už zajistil index.php; identita se bere ze session, nikdy z parametru.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'mot61_fav_toggle') {
    $studentKey = adaptive_student_key((string)$classId);
    $itemId = is_string($_POST['item_id'] ?? null) ? (string)$_POST['item_id'] : '';
    $result = $studentKey === '' ? 'unknown' : mot61_fav_toggle((string)$classId, $studentKey, $itemId);
    $_SESSION['flash'] = match ($result) {
        'added' => tr('Přidáno do oblíbených.'),
        'removed' => tr('Odebráno z oblíbených.'),
        'full' => tr('Oblíbených může být nejvýš {n}. Nejdřív některou odeber.', ['n' => MOT61_FAV_MAX]),
        default => tr('Tuhle položku nejde přidat do oblíbených.'),
    };
    redirect_to(($_POST['only_fav'] ?? '') === '1' ? '?view=obchod&oblibene=1' : '?view=obchod');
}
