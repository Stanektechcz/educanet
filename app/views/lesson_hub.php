<?php

declare(strict_types=1);

/**
 * ?view=cognitive_lab, lesson_kit, lesson_slides (v42/v43).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'cognitive_lab') {
    guarded_study_redirect();
    $lessonNo=max(1,min(28,(int)($_GET['lesson']??1)));
    $lesson=v42_find_lesson((string)$classId,$lessonNo);
    if(!$lesson){$_SESSION['flash']=tr('Cognitive Lab nebyl nalezen.');redirect_to('?view=course');}
    cv43_render_lab_page((string)$classId,$lesson,$module); exit;
}

if ($view === 'lesson_kit') {
    guarded_study_redirect();
    $lessonNo=max(1,min(28,(int)($_GET['lesson']??1)));
    $lesson=v42_find_lesson((string)$classId,$lessonNo);
    if(!$lesson){$_SESSION['flash']=tr('Materiály nebyl nalezen.');redirect_to('?view=course');}
    render_v42_lesson_hub_page((string)$classId,$lesson,$module,$learningResources); exit;
}

if ($view === 'lesson_slides') {
    guarded_study_redirect();
    $lessonNo=max(1,min(28,(int)($_GET['lesson']??1)));
    $lesson=v42_find_lesson((string)$classId,$lessonNo);
    if(!$lesson){$_SESSION['flash']=tr('Prezentace lekce nebyla nalezena.');redirect_to('?view=course');}
    render_v42_slide_page((string)$classId,$lesson,$module,$learningResources); exit;
}
