<?php

declare(strict_types=1);

/**
 * ?view=course, calendar, tutorial, topics, tools (+ knowledgebase bez tématu) – Tutorial Mode (v52).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (in_array($view, ['course', 'calendar', 'tutorial', 'topics', 'tools'], true) || ($view === 'knowledgebase' && !isset($_GET['topic']) && !isset($_GET['kb_class']))) {
    guarded_study_redirect();
    $t52Lessons = tut52_lessons((string)$classId, $module, $nextLessons, $extendedLessons, $schoolYear, is_array($completedTestResult) ? $completedTestResult : null);
    $t52Scores = tut52_student_scores((string)$classId, adaptive_student_key((string)$classId));
    $t52SimMap = is_array($simulations[$classId] ?? null) ? $simulations[$classId] : [];
    match ($view) {
        'course' => tut52_render_course((string)$classId, $module, $modules, $t52Lessons, $t52Scores, $flash),
        'tutorial' => tut52_render_tutorial((string)$classId, $module, $modules, $t52Lessons, max(1, (int)($_GET['lesson'] ?? 1)), $schoolYear, $t52Scores, is_array($completedTestResult) ? $completedTestResult : null, $t52SimMap, $flash),
        'calendar' => tut52_render_calendar((string)$classId, $module, $modules, $t52Lessons, $schoolYear, $flash),
        'tools' => tut52_render_tools((string)$classId, $module, $t52Lessons, $flash),
        default => tut52_render_topics((string)$classId, $module, $modules, $t52Lessons, $simulations, $flash),
    };
    exit;
}
