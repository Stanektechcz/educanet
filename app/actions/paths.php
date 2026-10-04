<?php

declare(strict_types=1);

/**
 * POST akce výukových cest (v63): p63_submit (odevzdání kroku), p63_parsons_move (posun řádku bez JS), p63_reflect.
 * Router: app/routes.php ('actions_class'). CSRF a třídu ($classId) už zajistil index.php; identita žáka je výhradně
 * ze session (adaptive_student_key), cesta a krok se vždy ověří proti katalogu třídy. Žádná data žáka do URL.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (in_array($action, ['p63_submit', 'p63_parsons_move', 'p63_reflect'], true)) {
    if (!p63_enabled_for_class((string)$classId)) redirect_to('?view=dashboard');
    $p63Path = p63_path_for_class((string)$classId, is_string($_POST['path'] ?? null) ? (string)$_POST['path'] : '');
    $p63Step = $p63Path === null ? null : p63_step($p63Path, is_string($_POST['step'] ?? null) ? (string)$_POST['step'] : '');
    if ($p63Path === null || $p63Step === null) {
        $_SESSION['flash'] = tr('Tenhle krok neexistuje.');
        redirect_to('?view=cesty');
    }
    $p63Key = adaptive_student_key((string)$classId);
    if ($action === 'p63_parsons_move') p63_action_move((string)$classId, $p63Key, $p63Path, $p63Step);
    if ($action === 'p63_reflect') p63_action_reflect((string)$classId, $p63Key, $p63Path, $p63Step);
    p63_action_submit((string)$classId, $p63Key, $p63Path, $p63Step);
}
