<?php

declare(strict_types=1);

/**
 * v58 · Robotí liga (?view=roboti). Každý herní modul v58 má vlastní soubor pohledu kvůli línému
 * načítání knihoven (viz app/routes.php). Vypíná ho stejný přepínač jako Linux Lab.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'roboti') {
    guarded_study_redirect();
    if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled((string)$classId)) { $_SESSION['flash'] = tr('Linux Lab a hry jsou pro vaši třídu zatím vypnuté.'); redirect_to('?view=hodina'); }
    robots58_render_student((string)$classId, $module, $flash);
    exit;
}
