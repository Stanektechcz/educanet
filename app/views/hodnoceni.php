<?php

declare(strict_types=1);

/**
 * ?view=hodnoceni – „Moje hodnocení“ (v66): z čeho se skládá podklad pro hodnocení, řetězec důkazů a (až po převzetí učitelem) známka.
 * Router: app/routes.php. Identita žáka je ze session; ve třídě, kde je hodnocení vypnuté, pohled jen informuje.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'hodnoceni') {
    guarded_study_redirect();
    render_header(tr('Moje hodnocení'), $module);
    g66v_render((string)$classId, adaptive_student_key((string)$classId));
    render_footer();
    exit;
}
