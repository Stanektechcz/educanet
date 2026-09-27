<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_lab_offline“ (angličtina). Offline stránka Labu
 * (lab-offline.html + lab-offline-v58.js), exportuje se i jako assets/lab-offline-i18n-v59.json
 * (tools/v59_export_offline_i18n.php). Vlastník: i18n builder B5.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Nalezeno příkazů: {n}' => 'Commands found: {n}',
    'Offline pískoviště Linux Labu. Napiš help pro nápovědu, cat vitej.txt pro úvod.' => "Offline Linux Lab sandbox. Type help for guidance, cat vitej.txt for an introduction.",
    'Opravdu chceš smazat pískoviště a začít znovu? Vlastní soubory v offline tréninku se ztratí.' => 'Really wipe the sandbox and start over? Your own files in the offline training will be lost.',
    'Popis' => 'Description',
    'Použití' => 'Usage',
    'Příklady:' => 'Examples:',
    'Příručku se teď nepodařilo načíst. Až se jednou připojíš k internetu a stránku načteš, příště bude fungovat i offline.' => "The manual couldn't be loaded right now. Once you connect to the internet and load the page, it will work offline next time too.",
    'Tento příkaz offline pískoviště nepodporuje – slouží jen jako přehled.' => "The offline sandbox doesn't support this command – it's shown for reference only.",
    'Tip: {tip}' => 'Tip: {tip}',

    // v59 · A11Y-04: statický text stránky lab-offline.html (viz assets/lab-offline-v58.js applyStaticI18n)
    'Přeskočit na příkazovou řádku' => 'Skip to the command line',
    'Offline trénink terminálu' => 'Offline terminal training',
    'Offline trénink' => 'Offline training',
    'Offline trénink – Linux Lab · EDUCANET' => 'Offline training – Linux Lab · EDUCANET',
    'Offline pískoviště Linux Labu: terminál a příručka příkazů fungující bez připojení k internetu.' => 'Offline Linux Lab sandbox: a terminal and command manual that work without an internet connection.',
    '– postup se do školy neukládá; kódy úloh ověřuje jen online Linux Lab. Pískoviště běží jen v tomto prohlížeči a nic neposílá na server.' => "– your progress isn't saved to school; task codes are checked only by the online Linux Lab. The sandbox runs only in this browser and sends nothing to a server.",
    'Návrat do aplikace' => 'Return to the app',
    'Zpět do online Linux Labu' => 'Back to the online Linux Lab',
    'Terminál (pískoviště)' => 'Terminal (sandbox)',
    'Šipky ↑ ↓: historie příkazů. Tab: doplnění názvu. Ctrl+L: vymaže obrazovku.' => 'Arrows ↑ ↓: command history. Tab: name completion. Ctrl+L: clears the screen.',
    'Výstup terminálu' => 'Terminal output',
    'Příkaz pro terminál' => 'Command for the terminal',
    'Spustit' => 'Run',
    'Vymazat obrazovku' => 'Clear screen',
    'Začít znovu' => 'Start over',
    'Příručka příkazů (offline)' => 'Command manual (offline)',
    'Hledat příkaz' => 'Search for a command',
    'Příručka se načítá…' => 'Loading the manual…',
    'Postup se ukládá jen na tomto zařízení (localStorage tohoto prohlížeče) – žádná data se neposílají na server ani do školy. Verze pískoviště 58.0.' => "Progress is saved only on this device (this browser's localStorage) – no data is sent to a server or to school. Sandbox version 58.0.",
];
