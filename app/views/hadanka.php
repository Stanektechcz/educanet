<?php

declare(strict_types=1);

/**
 * v58 · ARN-01 Týdenní hádanka (?view=hadanka). Golfová úloha týdne mimo třídní závod,
 * vlastní soubor pohledu kvůli línému načítání knihoven (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'hadanka') {
    guarded_study_redirect();
    if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled((string)$classId)) { $_SESSION['flash'] = tr('Linux Lab je pro vaši třídu zatím vypnutý.'); redirect_to('?view=hodina'); }
    arena58_weekly_render_student((string)$classId, $module);
    exit;
}
