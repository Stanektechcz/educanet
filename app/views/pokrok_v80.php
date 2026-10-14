<?php

declare(strict_types=1);

/**
 * v80 · Můj pokrok (?view=pokrok): kompetence, cesty, úlohy, exit tickety, opakování, cíle a odznaky; jeden Další krok. Identita žáka jen ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'pokrok') {
    guarded_study_redirect();
    render_header(tr('Můj pokrok'), $module);
    if ((string)$flash !== '') echo '<div class="notice" role="status" data-v81-toast="info" data-v81-close="' . e(tr('Zavřít oznámení')) . '">' . e((string)$flash) . '</div>';   // v81: s JS i jako toast
    pg80_render_page((string)$classId, adaptive_student_key((string)$classId));
    render_footer();
    exit;
}
