<?php

declare(strict_types=1);

/**
 * v67 · přepnutí vzhledu (POST ui67_theme_set): světlý / tmavý / podle systému. CSRF už ověřil index.php (verify_csrf).
 * Ukládá jen hodnotu do cookie edu_theme (whitelist); návrat jen na relativní adresu ?view=… Funguje i bez třídy (přihlášení).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'ui67_theme_set') {
    $ui67Theme = is_string($_POST['theme'] ?? null) ? $_POST['theme'] : '';
    if (!ui67_theme_set($ui67Theme)) $_SESSION['flash'] = tr('Neznámá volba vzhledu.');
    redirect_to(ui67_return_url(is_string($_POST['return'] ?? null) ? $_POST['return'] : ''));
}
