<?php

declare(strict_types=1);

/**
 * ?view=obchod – marketplace bodů pro žáka (v60). Router: app/routes.php.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'obchod') {
    guarded_study_redirect();
    $studentKey = adaptive_student_key((string)$classId);
    render_header(tr('Obchod'), $module);
    marketplace60_render_shop((string)$classId, $studentKey);
    render_footer();
    exit;
}
