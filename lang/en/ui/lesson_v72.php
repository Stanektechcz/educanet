<?php

declare(strict_types=1);

/**
 * v72 · katalog msgid domény „lesson_v72“ (en): cíl hodiny, exit ticket a volitelná domácí příprava schválené lekce.
 * Klíč = přesně český text ze zdroje (lesson_exit_v72_views.php, app/actions/lesson_exit_v72.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Cíl dnešní hodiny' => 'Today\'s lesson goal',
    'Poznáš, že to umíš, když:' => 'You\'ll know you\'ve got it when:',
    'Domácí příprava (volitelné)' => 'Homework (optional)',
    'Dobrovolné – nezapočítává se do hodnocení.' => 'Voluntary – it does not count towards your grade.',
    'asi {n} min' => 'about {n} min',
    'Exit ticket' => 'Exit ticket',
    'Jedna rychlá otázka na konec hodiny. Nehodnotí se – pomůže učiteli poznat, co zopakovat.' => 'One quick question at the end of the lesson. It is not graded – it helps your teacher see what to review.',
    'Správně.' => 'Correct.',
    'Tentokrát ne.' => 'Not this time.',
    'Tvoje odpověď: {answer}' => 'Your answer: {answer}',
    'Správná odpověď: {answer}' => 'Correct answer: {answer}',
    'Odeslat odpověď' => 'Send answer',
    'Nepodařilo se určit studentský profil.' => 'We couldn\'t identify your student profile.',
    'Lekce {n} se otevře až v den, kdy ji budete mít.' => 'Lesson {n} will open on the day you have it.',
    'Odpověď je uložená. Díky!' => 'Your answer is saved. Thanks!',
    'Na exit ticket už máš odpověď.' => 'You have already answered the exit ticket.',
    'Vyber jednu odpověď.' => 'Pick one answer.',
    'Exit ticket pro tuto lekci teď není k dispozici.' => 'The exit ticket for this lesson is not available right now.',
    'Odpověď se nepodařilo uložit. Zkus to prosím znovu.' => 'Your answer could not be saved. Please try again.',
];
