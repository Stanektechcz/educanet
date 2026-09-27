<?php

declare(strict_types=1);

/**
 * ?view=visual_lab (v48).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'visual_lab') {
    guarded_study_redirect();$lessonNo=max(1,min(28,(int)($_GET['lesson']??1)));$lesson=v42_find_lesson((string)$classId,$lessonNo);
    if(!$lesson){$_SESSION['flash']=tr('Visual Lab nebyl nalezen.');redirect_to('?view=course');}
    v48_render_student_lab_page((string)$classId,$lesson,$module);exit;
}
