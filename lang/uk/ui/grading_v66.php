<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v66 · katalog msgid domény „grading_v66“ (ukrajinština) – Moje hodnocení (řetězec důkazů a převzatá známka).
 */
return [
    'Moje hodnocení' => 'Моє оцінювання',
    'Moje hodnocení: z čeho se skládá' => 'Моє оцінювання: на чому воно ґрунтується',
    'V tvé třídě se hodnocení z testů, kompetencí a projektů zatím nepočítá.' => 'У твоєму класі оцінювання за тестами, компетенціями та проєктами поки що не застосовується.',
    'Tady vidíš, z čeho se skládá podklad pro hodnocení. Hry, aréna a body do něj nepatří. Rozhoduje učitel.' => 'Тут ти бачиш, з чого складається підстава для оцінювання. Ігри, арена та бали до неї не входять. Рішення ухвалює вчитель.',
    'Známka' => 'Оцінка',
    'Učitel zatím žádnou známku nepřevzal. Až se to stane, uvidíš ji tady.' => 'Вчитель ще не прийняв жодної оцінки. Коли це станеться, ти побачиш її тут.',
    'převzal učitel {date}' => 'прийняв вчитель {date}',
    'Z čeho se skládá' => 'З чого складається',
    'Z čeho se hodnocení skládá' => 'З чого складається оцінювання',
    'Zdroj' => 'Джерело',
    'Váha' => 'Вага',
    'Tvůj výsledek' => 'Твій результат',
    'zatím bez podkladů' => 'ще немає підстав',
    'Sumativní testy' => 'Підсумкові тести',
    'Zvládnutí kompetencí' => 'Опанування компетенцій',
    'Projekty' => 'Проєкти',
    'Důkazy' => 'Докази',
    'Test lekce {n}' => 'Тест уроку {n}',
    'Ověření cesty' => 'Перевірка шляху',
    'Hodnocení projektu' => 'Оцінка проєкту',
    'Výborně zvládnuto' => 'Відмінно опановано',
    'Chvalitebně zvládnuto' => 'Добре опановано',
    'Základy zvládnuty' => 'Основи опановано',
    'Zvládnuto jen minimum' => 'Опановано лише мінімум',
    'Zatím nezvládnuto' => 'Ще не опановано',
    'Zpět na přehled' => 'Назад до огляду',
    'Zatím není dost podkladů' => 'Ще недостатньо підстав',
    'Otevřít moje cesty' => 'Відкрити мої шляхи',
    'Až budeš mít výsledky sumativního testu, ověřené kompetence nebo hodnocený projekt, objeví se tady.' => 'Тут з’являться результати підсумкового тесту, підтверджені компетентності або оцінений проєкт.',
];
