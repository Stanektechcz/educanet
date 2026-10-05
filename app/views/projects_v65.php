<?php

declare(strict_types=1);

/**
 * ?view=projekt65 – cyklus projektu žáka (přehled a detail, v65). Router: app/routes.php.
 * Detail se otevírá neprůhledným id (c65_…); cizí nebo neexistující id vede na přehled.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'projekt65') {
    guarded_study_redirect();
    $studentKey = adaptive_student_key((string)$classId);
    $record = isset($_GET['id']) && is_string($_GET['id']) ? proj65_find_by_id($_GET['id']) : null;
    render_header(tr('Moje projekty'), $module);
    if ($record !== null && (string)$record['class_id'] === (string)$classId && proj65_is_member($record, $studentKey)) {
        p65v_render_detail($record, (string)$classId, $studentKey, (string)$flash);
    } else {
        p65v_render_home((string)$classId, $studentKey, (string)$flash);
    }
    render_footer();
    exit;
}
