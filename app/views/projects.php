<?php

declare(strict_types=1);

/**
 * ?view=projekty – nabídka projektů podle levelu pro žáka (v60). Router: app/routes.php.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'projekty') {
    guarded_study_redirect();
    $studentKey = adaptive_student_key((string)$classId);
    $level = (int)learning_level((int)(learning_profile((string)$classId)['xp'] ?? 0))['level'];
    render_header(tr('Projekty'), $module);
    if ($flash !== '') { echo '<div class="notice" role="status">' . e($flash) . '</div>'; }
    projects60_render((string)$classId, $studentKey, $level);
    render_footer();
    exit;
}
