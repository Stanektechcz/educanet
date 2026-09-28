<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_lab“ (angličtina). JS Linux Labu a Arény.
 * Vlastník: i18n builder B5.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // assets/linux-v57.js – editor souborů (nano), chybové hlášky terminálu
    '^O Uložit' => '^O Save',
    '^X Zavřít' => '^X Close',
    'Soubor je jen ke čtení.' => 'The file is read-only.',
    'Relace vypršela – obnov stránku (F5).' => 'Your session expired – refresh the page (F5).',
    'Uloženo.' => 'Saved.',
    'Chyba.' => 'Error.',
    '✔ Úroveň vyřešena.' => '✔ Level solved.',
    'Uložení se nezdařilo – zkus to znovu.' => "Saving failed – try again.",
    'Soubor patří správci – uprav ho přes sudo nano.' => 'This file belongs to the admin – edit it with sudo nano.',
    'Příkaz selhal.' => 'The command failed.',
    'Spojení se serverem selhalo. Zkus to znovu.' => 'The connection to the server failed. Try again.',
    'Opravdu vrátit úroveň do původního stavu? Postup se ztratí.' => 'Really reset this level? Your progress will be lost.',
    'Úroveň se vrátila do původního stavu.' => 'The level has been reset.',
    'Reset se nezdařil.' => 'The reset failed.',
    'Úroveň se nepodařilo načíst.' => 'The level could not be loaded.',
    'Nepodařilo se spojit s Labem. Zkus obnovit stránku.' => 'Could not connect to the Lab. Try refreshing the page.',
    // kontextová nabídka konzole
    'Nabídka konzole' => 'Console menu',
    'Kopírovat' => 'Copy',
    'Vložit' => 'Paste',
    'Odevzdat označené jako odpověď' => 'Submit selection as answer',
    'Tahle úroveň se neodevzdává odpovědí.' => 'This level is not turned in with an answer.',
    'Kopírování se nezdařilo – použij Ctrl+C.' => 'Copying failed – use Ctrl+C.',
    'Prohlížeč nepovolil vložení – použij Ctrl+V.' => 'The browser blocked pasting – use Ctrl+V.',

    // assets/arena-v57.js – odpočet, žebříčky žáka, panel učitele, projektor
    '(+{n} b{first})' => '(+{n} pts{first})',
    'Bez týmů.' => 'No teams.',
    'Běží' => 'Running',
    'Data se nepodařilo načíst – obnov stránku.' => 'Data could not be loaded – refresh the page.',
    'Data se nepodařilo načíst.' => 'Data could not be loaded.',
    'Kdo vyřeší první úlohu?' => 'Who will solve the first task?',
    'Na soupisce třídy zatím nikdo není.' => 'Nobody is on the class list yet.',
    'Nejsi přihlášen(a) – obnov stránku.' => "You're not signed in – refresh the page.",
    'Nikdo – zatím všichni postupují.' => "Nobody – everyone's making progress so far.",
    'Připravený' => 'Ready',
    'Relace vypršela – obnov stránku.' => 'Your session expired – refresh the page.',
    'Skončil' => 'Finished',
    'Spojení se přerušilo, zkouším to znovu…' => 'Connection interrupted, retrying…',
    'Zatím nic – bonus ×1,25 čeká.' => 'Nothing yet – the ×1.25 bonus is waiting.',
    'Zatím se nic nestalo.' => 'Nothing has happened yet.',
    'Zbývá {mins}.' => 'Remaining: {mins}.',
    'Zbývá {n} sekund.' => ['one' => 'Remaining: {n} second.', 'few' => 'Remaining: {n} seconds.', 'other' => 'Remaining: {n} seconds.'],
    'Zobrazit výsledky →' => 'Show results →',
    'Závod skončil.' => 'The race is over.',
    'nezapojen(a)' => 'not taking part',
    'první vyřešil(a)' => 'first solved',
    'první!' => 'first!',
    'si vzal(a) nápovědu v' => 'took a hint in',
    'vyřešil(a)' => 'solved',
    'zadal(a) kód spolužáka v úloze' => "entered a classmate's code in task",
    'zkusil(a) cizí kód v' => "tried someone else's code in",
    '{min} min, {cmds} příkazů ({failed} s chybou)' => '{min} min, {cmds} commands ({failed} with an error)',
    '{n} b, {m} úl.' => '{n} pts, {m} tsk.',
    'Čas vypršel.' => 'Time is up.',
    'Žádné – nikdo nezkoušel cizí kód.' => "None – nobody has tried someone else's code.",
    '{n} minut' => ['one' => '{n} minute', 'other' => '{n} minutes'],
    'Týmy se ukážou po startu.' => 'Teams will appear once it starts.',
    'ty' => 'you',
    'vyřešeno' => 'solved',
    '{done} / {target} vyřešených úloh' => '{done} / {target} tasks solved',
    '{min} min {s} s' => '{min} min {s} s',
    '{n} b' => '{n} pts',
    '{n} úl.' => '{n} tsk.',
    '{s} s' => '{s} s',
    'Zatím nikdo nemá body. Buď první!' => 'Nobody has points yet. Be the first!',
    'Zatím nic – první vyřešení úlohy dává bonus ×1,25.' => 'Nothing yet – solving a task first gives a ×1.25 bonus.',

    // assets/lab-a11y-v58.js – přístupnost terminálu
    'Bez odezvy' => 'No response',
    'Bez textového obsahu.' => 'No text content.',
    'Cestu se nepodařilo zjistit.' => 'The route could not be determined.',
    'Chyba:' => 'Error:',
    'Switch (místní síť)' => 'Switch (local network)',
    'Tento počítač' => 'This computer',
    'Terminál je zatím prázdný.' => 'The terminal is empty so far.',
    'Týmový režim' => 'Team mode',
    'V pořádku' => 'OK',
    'role: {role}' => 'role: {role}',

    // assets/lab-explain-v58.js – panel "Vysvětli výstup" (UI, vysvětlení samotná zůstávají obsah)
    'Zpráva simulátoru' => 'Simulator message',
    'Tohle je nápověda nebo systémová zpráva simulátoru, ne přímý výstup příkazu.' => "This is a hint or a system message from the simulator, not the command's direct output.",
    'Pro příkaz „{prikaz}“ zatím nemáme rychlé vysvětlení přímo tady – zkus příručku příkazů.' => "We don't have a quick explanation for the command \"{prikaz}\" here yet – try the command manual.",
    'K tomuhle řádku se nepodařilo najít příkaz, ke kterému patří.' => 'No command could be found for this line.',
    'Příkaz {prikaz}' => 'Command {prikaz}',
    'Výstup' => 'Output',
    'Vysvětlit řádek:' => 'Explain line:',
    '(prázdné)' => '(empty)',
    'Otevřít „{prikaz}“ v příručce →' => 'Open "{prikaz}" in the manual →',
];
