<?php

declare(strict_types=1);

/**
 * v58 · CTF týden (?view=ctf) a Incidenty (?view=incident) – soutěže ve vlastním cvičném systému Linux Labu.
 * Spouští ho jen router v index.php (viz app/routes.php). Vypíná ho stejný přepínač jako Linux Lab.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (in_array($view, ['ctf', 'incident'], true)) {
    guarded_study_redirect();
    if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled((string)$classId)) { $_SESSION['flash'] = tr('Linux Lab a soutěže jsou pro vaši třídu zatím vypnuté.'); redirect_to('?view=hodina'); }
    if ($view === 'ctf') { arena58_ctf_render_student((string)$classId, $module); exit; }
    arena58_inc_render_student((string)$classId, $module);
    exit;
}
