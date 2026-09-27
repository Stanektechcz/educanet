<?php

declare(strict_types=1);

/**
 * ?view=hodina – dnešní hodina (v55/v56).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'hodina') {
    guarded_study_redirect();
    $todaySession = sess53_for_class_date((string)$classId, date('Y-m-d'));
    if (!$todaySession) { $_SESSION['flash'] = tr('Pro dnešek není otevřená žádná hodina.'); redirect_to('?view=course'); }
    if ((string)$todaySession['kind'] === 'intake') redirect_to('?view=intake');
    $todayNo = max(1, min(28, (int)($todaySession['lesson_number'] ?? 1)));
    $todayBundle = v56_lesson_bundle((string)$classId, $module, $todayNo, $nextLessons, $extendedLessons);
    v56_render_lesson((string)$classId, $module, $todayBundle, $todaySession, $schoolYear, $flash, (string)($_GET['faze'] ?? ''));
    exit;
}
