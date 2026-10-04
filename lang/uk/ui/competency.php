<?php

declare(strict_types=1);

/**
 * v62 · katalog msgid domény „competency“ (ukrajinština) – záložka Kompetence v profilu žáka.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Umím určit síť, masku a adresu zařízení' => 'Я вмію визначити мережу, маску й адресу пристрою',
    'Umím vysvětlit DNS a DHCP a ověřit je v praxi' => 'Я вмію пояснити DNS і DHCP та перевірити їх на практиці',
    'Umím krok za krokem diagnostikovat síťový problém' => 'Я вмію крок за кроком діагностувати проблему в мережі',
    'Umím rozlišit porty a služby a ověřit, co poslouchá' => 'Я вмію розрізняти порти та служби й перевірити, що слухає',
    'Umím popsat základy zabezpečení sítě a firewallu' => 'Я вмію описати основи безпеки мережі та брандмауера',
    'Umím se pohybovat v shellu a pracovat se soubory' => 'Я вмію орієнтуватися в оболонці та працювати з файлами',
    'Umím filtrovat a zpracovat text v příkazové řádce' => 'Я вмію фільтрувати й обробляти текст у командному рядку',
    'Umím spravovat uživatele a oprávnění' => 'Я вмію керувати користувачами й правами доступу',
    'Umím spravovat služby a číst systémové logy' => 'Я вмію керувати службами та читати системні журнали',
    'Umím se bezpečně připojit přes SSH a klíče' => 'Я вмію безпечно підключатися через SSH і ключі',
    'Umím automatizovat úlohy skriptem a cronem' => 'Я вмію автоматизувати завдання скриптом і cron',
    'test' => 'тест',
    'projekt' => 'проєкт',
    'lab' => 'лабораторія',
    'hra' => 'гра',
    'aréna' => 'арена',
    'lekce' => 'урок',
    'pamatuje' => 'пам’ятає',
    'použije' => 'застосовує',
    'analyzuje' => 'аналізує',
    'tvoří' => 'створює',
    'Upevněno' => 'Закріплено',
    'Zvládnuto' => 'Засвоєно',
    'Rozpracováno' => 'В процесі',
    'Zatím neověřeno' => 'Ще не перевірено',
    'Úroveň: {level}' => 'Рівень: {level}',
    'Důkazů: {n} ({sources})' => 'Доказів: {n} ({sources})',
    'Zatím tu nic není.' => 'Тут поки що нічого немає.',
    'Mapa ukazuje, co už umíš podle testů, lekcí a úloh v labu. Hra sama zvládnutí nedá, je to jen trénink. Mapu vidíš jen ty.' => 'Карта показує, що ти вже вмієш, за тестами, уроками й завданнями в лабораторії. Сама гра не дає засвоєння, це лише тренування. Цю карту бачиш лише ти.',
    'Umím {n} z {total} kompetencí' => 'Я вмію {n} з {total} компетентностей',
    'Umím' => 'Я вмію',
    'Učím se' => 'Я вчуся',
    'Zatím ne' => 'Ще ні',
];
