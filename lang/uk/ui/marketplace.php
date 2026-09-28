<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „marketplace“ (українська) – магазин балів учня.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Obchod' => 'Магазин',
    'Nakup si za body' => 'Купуй за бали',
    'Body získáváš za úkoly a známky. Obchod nikdy neovlivní tvoji známku, XP ani žebříček.' => 'Бали ти отримуєш за завдання й оцінки. Магазин ніколи не впливає на оцінку, XP чи рейтинг.',
    'bodů k utracení' => 'балів на витрату',
    '{n} bodů' => ['one' => '{n} бал', 'few' => '{n} бали', 'many' => '{n} балів', 'other' => '{n} балів'],
    'Obchod je zatím prázdný. Zeptej se učitele, kdy přidá první položky.' => 'Магазин поки що порожній. Запитай учителя, коли з’являться перші товари.',
    'Nabídka obchodu' => 'Пропозиції магазину',
    'Kosmetika profilu' => 'Косметика профілю',
    'Extra obsah' => 'Додатковий контент',
    'Nápověda do labu' => 'Підказка для лабораторії',
    'Ostatní' => 'Інше',
    'Skladem: {n}' => 'На складі: {n}',
    'Vyprodáno' => 'Розпродано',
    'Koupit' => 'Купити',
    'Nemáš dost bodů.' => 'У тебе недостатньо балів.',
    'Moje nákupy' => 'Мої покупки',
    'Historie nákupů v obchodě' => 'Історія покупок у магазині',
    'Zatím jsi nic nekoupil/a.' => 'Ти ще нічого не купив/ла.',
    'Vráceno učitelem' => 'Повернено вчителем',
    'Použít na profilu' => 'Застосувати в профілі',
    'Vypnout' => 'Вимкнути',
    'Otevřít obsah' => 'Відкрити контент',
    'Nákup proběhl. Zůstatek: {n} bodů.' => 'Покупку виконано. Залишок: {n} балів.',
    'Nákup se nepodařil.' => 'Покупка не вдалася.',
    'Kosmetiku se nepodařilo nastavit.' => 'Не вдалося встановити косметичний елемент.',
    'Zlatý rámeček' => 'Золота рамка',
    'Ozdobný rámeček kolem avataru.' => 'Декоративна рамка навколо аватара.',
    'Neonový rámeček' => 'Неонова рамка',
    'Zářivý rámeček kolem avataru.' => 'Яскрава рамка навколо аватара.',
    'Titulek: Legenda třídy' => 'Титул: Легенда класу',
    'Titulek vedle jména v profilu.' => 'Титул поруч з іменем у профілі.',
    'Extra lekce: rychlé zkratky' => 'Додатковий урок: швидкі клавіші',
    'Doplňková lekce nad rámec výuky.' => 'Додатковий урок понад програму.',
];
