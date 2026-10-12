<?php

declare(strict_types=1);

/**
 * ?view=opakovani – opakování před písemkou (v75): balíček zveřejněný učitelem třídy. Identita žáka jen ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'opakovani') {
    guarded_study_redirect();
    render_header(tr('Opakování před písemkou'), $module);
    if ((string)$flash !== '') echo '<div class="notice" role="status">' . e((string)$flash) . '</div>';
    rv75_render_student_page((string)$classId, adaptive_student_key((string)$classId));
    unset($_SESSION['tb75_last']);
    render_footer();
    exit;
}
