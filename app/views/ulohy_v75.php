<?php

declare(strict_types=1);

/**
 * ?view=ulohy – úlohy s variantami (v75). ?uloha=<id> otevře jednu úlohu. Identita žáka jen ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'ulohy') {
    guarded_study_redirect();
    render_header(tr('Úlohy s variantami'), $module);
    if ((string)$flash !== '') echo '<div class="notice" role="status">' . e((string)$flash) . '</div>';
    tb75_render_page((string)$classId, adaptive_student_key((string)$classId), is_string($_GET['uloha'] ?? null) ? (string)$_GET['uloha'] : '');
    unset($_SESSION['tb75_last']);
    render_footer();
    exit;
}
