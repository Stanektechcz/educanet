<?php

declare(strict_types=1);

/**
 * ?view=next_lesson&classic=1 a ?view=course_lesson&classic=1.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'next_lesson') {
    guarded_study_redirect();
    $lesson = is_array($nextLessons[$classId] ?? null) ? $nextLessons[$classId] : null;
    if (!is_array($lesson) || !learning_primary_block_complete((string)$classId)) { $_SESSION['flash']=tr('Lekce 2 se odemkne po dokončení prvního bloku.'); redirect_to('?view=course'); }
    $progress = learning_next_lesson_progress((string)$classId,(string)$lesson['id'],$lesson['steps']??[]);
    render_structured_lesson_page($lesson,(string)$classId,$module,$progress,'next_lesson_step',(string)$lesson['id']); exit;
}

if ($view === 'course_lesson') {
    guarded_study_redirect();
    $lessonId=is_string($_GET['lesson']??null)?$_GET['lesson']:''; $lesson=null;
    foreach(($extendedLessons[$classId]??[]) as $candidate) if(is_array($candidate)&&(string)($candidate['id']??'')===$lessonId){$lesson=$candidate;break;}
    if(!is_array($lesson)){ $_SESSION['flash']=tr('Lekce nebyla nalezena.'); redirect_to('?view=course'); }
    $num=(int)($lesson['number']??0);
    if(!learning_course_lesson_unlocked((string)$classId,$num,$nextLessons,$extendedLessons)){ $_SESSION['flash']=tr('Nejdřív dokonči předchozí lekci.'); redirect_to('?view=course'); }
    $progress=learning_course_lesson_progress((string)$classId,$lesson);
    render_structured_lesson_page($lesson,(string)$classId,$module,$progress,'course_lesson_step',(string)$lesson['id']); exit;
}
