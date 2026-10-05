<?php

declare(strict_types=1);

/**
 * ?view=portfolio (výběr, reflexe, náhled pro tisk) a ?view=portfolio_export (lokální stažení HTML). v65.
 * Žádné sdílení odkazem – oba pohledy čtou jen portfolio přihlášeného žáka ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'portfolio_export') {
    guarded_study_redirect();
    port65v_send_export((string)$classId, adaptive_student_key((string)$classId));
}

if ($view === 'portfolio') {
    guarded_study_redirect();
    render_header(tr('Moje portfolio'), $module);
    port65v_render_page((string)$classId, adaptive_student_key((string)$classId), (string)$flash);
    render_footer();
    exit;
}
