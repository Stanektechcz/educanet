<?php

declare(strict_types=1);

/**
 * v58 · OPS-02 – přepnutí jazyka rozhraní (POST edu_set_lang). CSRF už ověřil index.php (verify_csrf).
 * Ukládá jen kód jazyka do cookie edu_lang; návrat jen na relativní adresu ?view=…
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'edu_set_lang') {
    $lang = is_string($_POST['lang'] ?? null) ? $_POST['lang'] : '';
    if (!edu_set_locale($lang)) $_SESSION['flash'] = t('core.lang.invalid');
    redirect_to(edu_i18n_return_url(is_string($_POST['return'] ?? null) ? $_POST['return'] : ''));
}
