<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v66 · katalog msgid domény „grading_v66“ (angličtina) – Moje hodnocení (řetězec důkazů a převzatá známka).
 */
return [
    'Moje hodnocení' => 'My assessment',
    'Moje hodnocení: z čeho se skládá' => 'My assessment: what it is based on',
    'V tvé třídě se hodnocení z testů, kompetencí a projektů zatím nepočítá.' => 'Your class does not use an assessment based on tests, competencies and projects yet.',
    'Tady vidíš, z čeho se skládá podklad pro hodnocení. Hry, aréna a body do něj nepatří. Rozhoduje učitel.' => 'Here you can see what the assessment draft is based on. Games, the arena and points are not part of it. The teacher decides.',
    'Známka' => 'Grade',
    'Učitel zatím žádnou známku nepřevzal. Až se to stane, uvidíš ji tady.' => 'Your teacher has not accepted a grade yet. You will see it here once they do.',
    'převzal učitel {date}' => 'accepted by your teacher on {date}',
    'Z čeho se skládá' => 'What it is made of',
    'Z čeho se hodnocení skládá' => 'What the assessment is made of',
    'Zdroj' => 'Source',
    'Váha' => 'Weight',
    'Tvůj výsledek' => 'Your result',
    'zatím bez podkladů' => 'no evidence yet',
    'Sumativní testy' => 'Summative tests',
    'Zvládnutí kompetencí' => 'Competency mastery',
    'Projekty' => 'Projects',
    'Důkazy' => 'Evidence',
    'Test lekce {n}' => 'Lesson {n} test',
    'Ověření cesty' => 'Path check',
    'Hodnocení projektu' => 'Project assessment',
    'Výborně zvládnuto' => 'Excellent',
    'Chvalitebně zvládnuto' => 'Very good',
    'Základy zvládnuty' => 'Basics mastered',
    'Zvládnuto jen minimum' => 'Only the minimum mastered',
    'Zatím nezvládnuto' => 'Not mastered yet',
    'Zpět na přehled' => 'Back to overview',
    'Zatím není dost podkladů' => 'Not enough evidence yet',
    'Otevřít moje cesty' => 'Open my paths',
    'Až budeš mít výsledky sumativního testu, ověřené kompetence nebo hodnocený projekt, objeví se tady.' => 'The results of a summative test, verified competencies or a graded project will appear here.',
];
