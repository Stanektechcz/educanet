<?php

declare(strict_types=1);

/**
 * ?view=activate – aktivační kód (v51).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'activate') {
    if (isset($_GET['reset'])) { unset($_SESSION['intake_activation_code']); redirect_to('?view=activate'); }
    intake_v51_render_activate_view($modules, $flash);
    exit;
}
