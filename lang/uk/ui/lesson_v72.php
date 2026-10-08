<?php

declare(strict_types=1);

/**
 * v72 · katalog msgid domény „lesson_v72“ (uk): cíl hodiny, exit ticket a volitelná domácí příprava schválené lekce.
 * Klíč = přesně český text ze zdroje (lesson_exit_v72_views.php, app/actions/lesson_exit_v72.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Cíl dnešní hodiny' => 'Мета сьогоднішнього уроку',
    'Poznáš, že to umíš, když:' => 'Ти зрозумієш, що вмієш це, коли:',
    'Domácí příprava (volitelné)' => 'Домашня підготовка (за бажанням)',
    'Dobrovolné – nezapočítává se do hodnocení.' => 'Добровільно – не впливає на оцінку.',
    'asi {n} min' => 'близько {n} хв',
    'Exit ticket' => 'Вихідний квиток',
    'Jedna rychlá otázka na konec hodiny. Nehodnotí se – pomůže učiteli poznat, co zopakovat.' => 'Одне швидке запитання наприкінці уроку. Воно не оцінюється – допоможе вчителю зрозуміти, що повторити.',
    'Správně.' => 'Правильно.',
    'Tentokrát ne.' => 'Цього разу ні.',
    'Tvoje odpověď: {answer}' => 'Твоя відповідь: {answer}',
    'Správná odpověď: {answer}' => 'Правильна відповідь: {answer}',
    'Odeslat odpověď' => 'Надіслати відповідь',
    'Nepodařilo se určit studentský profil.' => 'Не вдалося визначити профіль учня.',
    'Lekce {n} se otevře až v den, kdy ji budete mít.' => 'Урок {n} відкриється в день, коли він у тебе за розкладом.',
    'Odpověď je uložená. Díky!' => 'Відповідь збережено. Дякуємо!',
    'Na exit ticket už máš odpověď.' => 'Ти вже відповів(-ла) на вихідний квиток.',
    'Vyber jednu odpověď.' => 'Обери одну відповідь.',
    'Exit ticket pro tuto lekci teď není k dispozici.' => 'Вихідний квиток для цього уроку зараз недоступний.',
    'Odpověď se nepodařilo uložit. Zkus to prosím znovu.' => 'Не вдалося зберегти відповідь. Спробуй, будь ласка, ще раз.',
];
