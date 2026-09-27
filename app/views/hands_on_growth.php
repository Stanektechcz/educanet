<?php

declare(strict_types=1);

/**
 * ?view=hands_on, goal_nav, growth, growth_path, skill_passport, peer_lab (v50).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'hands_on') {
    guarded_study_redirect();
    if(!in_array((string)$classId,['class_3a','class_4a'],true)){$_SESSION['flash']=tr('Hands-on Runtime je součást SOSaPS.');redirect_to('?view=dashboard');}
    $lessonNo=max(1,min(28,(int)($_GET['lesson']??1)));$lesson=v42_find_lesson((string)$classId,$lessonNo);
    if(!$lesson){$_SESSION['flash']=tr('Hands-on Lab nebyl nalezen.');redirect_to('?view=course');}
    v50_render_student_hands_on_page((string)$classId,$lesson,$module,$flash);exit;
}
if ($view === 'goal_nav') {
    guarded_study_redirect();v504_render_goal_navigator((string)$classId,$module,$flash);exit;
}
if ($view === 'growth') {
    guarded_study_redirect();v50_render_growth_overview((string)$classId,$module,$flash);exit;
}
if ($view === 'growth_path') {
    guarded_study_redirect();v50_render_growth_path((string)$classId,$module,(string)($_GET['path']??''),$flash);exit;
}
if ($view === 'skill_passport') {
    guarded_study_redirect();v50_render_skill_passport((string)$classId,$module,$flash);exit;
}
if ($view === 'peer_lab') {
    guarded_study_redirect();if(!in_array((string)$classId,['class_3a','class_4a'],true)){$_SESSION['flash']=tr('Peer debugging je součást SOSaPS.');redirect_to('?view=dashboard');}v50_render_peer_lab((string)$classId,$module,$flash);exit;
}
