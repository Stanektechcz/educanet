<?php

declare(strict_types=1);

/**
 * Přesměrování course_lesson/next_lesson na tutoriál (v52), pokud chybí ?classic.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

// v52 · Tutorial Mode: kurz, tutoriál lekce, kalendář, témata, programy a zkratky.
if (in_array($view, ['course_lesson', 'next_lesson'], true) && !isset($_GET['classic'])) {
    guarded_study_redirect();
    $t52No = 2;
    if ($view === 'course_lesson') {
        $t52No = 0;
        foreach ((array)($extendedLessons[$classId] ?? []) as $t52Candidate) if (is_array($t52Candidate) && (string)($t52Candidate['id'] ?? '') === (string)($_GET['lesson'] ?? '')) { $t52No = (int)($t52Candidate['number'] ?? 0); break; }
    }
    if ($t52No > 0) redirect_to('?view=tutorial&lesson=' . $t52No);
}
