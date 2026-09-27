<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog JS msgid domény „js_teamgames“ (angličtina).
 * assets/teamgames-v58.js (viz lang/domains_v59.php). Vkládá edu_tr_json_js() na žákovské stránce
 * (teamgames_v58_views.php). Jen žákovský panel hry (initStudent, renderer jednotlivých her,
 * odpočet, polling); učitelský a projektorový panel (initTeacher, initProjector, COL_LABELS,
 * tabulka výsledků) zůstává česky – skript na ně běží i bez i18n-v58.js (shim). Vlastník: builder i18n_teamgames.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // Sdílené se statusLabel/core.php (teamgames.php) – překlad musí být stejný.
    'Běží' => 'Running',
    'Pauza' => 'Pause',
    'Skončila' => 'Finished',
    'Archiv' => 'Archive',
    'Připravuje se' => 'Getting ready',

    // Odpočet
    // POZN. integrátorovi: tools/v59_i18n_audit.php v59a_extract_js() – regex EduI18n.trn(\s*\{([^}]*)\})
    // ořízne tělo objektu na první '}' uvnitř textu formy (zde z placeholderu {n}), takže u těchto
    // dvou plurálů hlásí FAIL extract:no-template-literal-msgid / "chybí tvar other" i přes správný
    // zápis podle docs/I18N_V59.md. Stejný jev má assets/robots-v58.js (dvě volání EduI18n.trn s {n}
    // ve formách) – sdílená chyba nástroje, ne chyba katalogu/kódu domény.
    'Zbývá {n} minut.' => ['one' => 'Remaining: {n} minute.', 'few' => 'Remaining: {n} minutes.', 'other' => 'Remaining: {n} minutes.'],
    'Zbývá {n} sekund.' => ['one' => 'Remaining: {n} second.', 'few' => 'Remaining: {n} seconds.', 'other' => 'Remaining: {n} seconds.'],
    'Čas vypršel.' => 'Time is up.',

    // Chyby při pollingu / API
    'Data se nepodařilo načíst – obnov stránku.' => 'Data could not be loaded – refresh the page.',
    'Spojení se přerušilo, zkouším to znovu…' => 'Connection interrupted, retrying…',
    'Relace vypršela – obnov stránku.' => 'Your session expired – refresh the page.',
    'Nejsi přihlášen(a) – obnov stránku.' => 'You\'re not signed in – refresh the page.',
    'Data se nepodařilo načíst.' => 'Data could not be loaded.',
    'Signál se nepodařilo odeslat.' => "Couldn't send the signal.",

    // Drobné jednotky/hodnoty sdílené s výsledkovou tabulkou
    'ano' => 'yes',
    '{min} min {s} s' => '{min} min {s} s',
    '{s} s' => '{s} s',
    '{body} b.' => '{body} pts',

    // Kvízová otázka
    'Čekej na další otázku…' => 'Wait for the next question…',
    'Odeslat' => 'Send',
    'Zapiš pořadí čísly oddělenými čárkou (1 = první z nabídky výše).' => 'Write the order as numbers separated by commas (1 = first option above).',
    'Pořadí, např. 2,1,3' => 'Order, e.g. 2,1,3',
    'Odpověď' => 'Answer',
    'Správně!' => 'Correct!',
    'Zatím ne, zkus to znovu.' => 'Not yet, try again.',

    // Štafeta
    'Úsek {usek} z {celkem} – hotovo!' => 'Leg {usek} of {celkem} – done!',
    'Úsek {usek} z {celkem}' => 'Leg {usek} of {celkem}',
    'Váš tým doběhl do cíle. Skvělá práce!' => 'Your team reached the finish. Great job!',
    'Řeš úkol v terminálu výše{nazev}. Po vyřešení úsek sám postoupí.' => 'Solve the task in the terminal above{nazev}. The leg moves on automatically once solved.',
    'Poprosit o pomoc spoluhráče (+30 s)' => 'Ask a teammate for help (+30 s)',

    // Bingo
    'Body: {body} · celé řady: {rady} (s bonusem: {bonus})' => 'Points: {body} · full rows: {rady} (with bonus: {bonus})',
    'Bonus za řadu se počítá, až políčko vyřeší každý člen týmu aspoň jednou.' => "A row's bonus counts once every team member has solved a square at least once.",
    'Zdarma' => 'Free',

    // Riskuj!
    'Na tahu: {tym} (vy!)' => "{tym}'s turn (you!)",
    'Na tahu: {tym}' => "{tym}'s turn",
    'Hodnota: {body} b.' => 'Value: {body} pts',
    'Odpověď je odeslaná, čekej na uzavření otázky.' => 'Your answer is sent – wait for the question to close.',
    '(výsledek se ukáže po uzavření otázky)' => '(the result appears once the question closes)',
    '{kategorie}, {body} bodů' => '{kategorie}, {body} points',
    'Čekej, až váš tým bude na tahu.' => "Wait until it's your team's turn.",
    'Poslední otázka ({kategorie}): {odpoved} – {vysvetleni}' => 'Last question ({kategorie}): {odpoved} – {vysvetleni}',
    'Poslední otázka ({kategorie}): {odpoved}' => 'Last question ({kategorie}): {odpoved}',

    // Přetahovaná
    'Kolo {kolo} z {celkem} · zbývá {cas}' => 'Round {kolo} of {celkem} · {cas} left',
    'Kolo {kolo} z {celkem}' => 'Round {kolo} of {celkem}',
    'Vyrovnaný stav.' => "It's an even match.",
    'Váš tým vede o {procenta} %.' => 'Your team leads by {procenta}%.',
    'Soupeř vede o {procenta} %.' => 'The other team leads by {procenta}%.',
    'Přetahovaná – {stav}' => 'Tug of war – {stav}',
    'Připravuje se další otázka…' => 'Getting the next question ready…',

    // Správci sítě/webu
    'Vaše body: {body} · vlna {vlna}/{celkem}' => 'Your points: {body} · wave {vlna}/{celkem}',
    'Uzel {cislo} ({vlastnik})' => 'Node {cislo} ({vlastnik})',
    'Uzel {cislo}' => 'Node {cislo}',

    // Úniková místnost
    'Váš tým unikl! 🎉' => 'Your team escaped! 🎉',
    'Tvoje role: {role}' => 'Your role: {role}',
    'Najdi svou stopu v terminálu výše a řekni ji nahlas týmu.' => 'Find your clue in the terminal above and say it out loud to your team.',
    'Tvoje část kódu: ' => 'Your part of the code: ',
    'Řekni ji nahlas týmu – appka chat nemá.' => "Say it out loud to your team – the app doesn't have chat.",
    'Použít týmovou nápovědu (+120 s, zbývá {n})' => 'Use a team hint (+120 s, {n} left)',
    'Zámek' => 'Lock',
    'Poskládejte 4 části kódu v pořadí: {poradi}.' => 'Put the 4 code parts in order: {poradi}.',
    'Otevřít zámek' => 'Open the lock',
    'Zámek se otevřel!' => 'The lock opened!',
    'Kód nesedí – zkontrolujte to s týmem.' => "The code doesn't match – check it with your team.",

    // Panel mého týmu a signály
    'Tým: {jmeno}' => 'Team: {jmeno}',
    'spoluhráči: {jmena}' => 'teammates: {jmena}',
    'Zatím nejsi v žádném týmu.' => "You're not on a team yet.",
    'Zatím žádné.' => 'None yet.',
];
