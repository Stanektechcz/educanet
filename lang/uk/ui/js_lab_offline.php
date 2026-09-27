<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_lab_offline“ (українська). Офлайн-сторінка Labu.
 * Власник: i18n builder B5.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Nalezeno příkazů: {n}' => 'Знайдено команд: {n}',
    'Offline pískoviště Linux Labu. Napiš help pro nápovědu, cat vitej.txt pro úvod.' => 'Офлайн-пісочниця Linux Lab. Введи help для довідки, cat vitej.txt для вступу.',
    'Opravdu chceš smazat pískoviště a začít znovu? Vlastní soubory v offline tréninku se ztratí.' => 'Дійсно очистити пісочницю й почати спочатку? Власні файли в офлайн-тренуванні втратяться.',
    'Popis' => 'Опис',
    'Použití' => 'Використання',
    'Příklady:' => 'Приклади:',
    'Příručku se teď nepodařilo načíst. Až se jednou připojíš k internetu a stránku načteš, příště bude fungovat i offline.' => 'Зараз не вдалося завантажити довідник. Коли підключишся до інтернету й завантажиш сторінку, наступного разу це працюватиме й офлайн.',
    'Tento příkaz offline pískoviště nepodporuje – slouží jen jako přehled.' => 'Офлайн-пісочниця не підтримує цю команду – вона показана лише для огляду.',
    'Tip: {tip}' => 'Порада: {tip}',

    // v59 · A11Y-04: статичний текст сторінки lab-offline.html (див. assets/lab-offline-v58.js applyStaticI18n)
    'Přeskočit na příkazovou řádku' => 'Перейти до командного рядка',
    'Offline trénink terminálu' => 'Офлайн-тренування термінала',
    'Offline trénink' => 'Офлайн-тренування',
    'Offline trénink – Linux Lab · EDUCANET' => 'Офлайн-тренування – Linux Lab · EDUCANET',
    'Offline pískoviště Linux Labu: terminál a příručka příkazů fungující bez připojení k internetu.' => 'Офлайн-пісочниця Linux Lab: термінал і довідник команд, які працюють без підключення до інтернету.',
    '– postup se do školy neukládá; kódy úloh ověřuje jen online Linux Lab. Pískoviště běží jen v tomto prohlížeči a nic neposílá na server.' => '– прогрес не зберігається до школи; коди завдань перевіряє лише онлайн Linux Lab. Пісочниця працює лише в цьому браузері й нічого не надсилає на сервер.',
    'Návrat do aplikace' => 'Повернення до застосунку',
    'Zpět do online Linux Labu' => 'Назад до онлайн Linux Lab',
    'Terminál (pískoviště)' => 'Термінал (пісочниця)',
    'Šipky ↑ ↓: historie příkazů. Tab: doplnění názvu. Ctrl+L: vymaže obrazovku.' => 'Стрілки ↑ ↓: історія команд. Tab: доповнення назви. Ctrl+L: очищає екран.',
    'Výstup terminálu' => 'Вивід термінала',
    'Příkaz pro terminál' => 'Команда для термінала',
    'Spustit' => 'Виконати',
    'Vymazat obrazovku' => 'Очистити екран',
    'Začít znovu' => 'Почати знову',
    'Příručka příkazů (offline)' => 'Довідник команд (офлайн)',
    'Hledat příkaz' => 'Пошук команди',
    'Příručka se načítá…' => 'Довідник завантажується…',
    'Postup se ukládá jen na tomto zařízení (localStorage tohoto prohlížeče) – žádná data se neposílají na server ani do školy. Verze pískoviště 58.0.' => 'Прогрес зберігається лише на цьому пристрої (localStorage цього браузера) – жодні дані не надсилаються на сервер чи до школи. Версія пісочниці 58.0.',
];
