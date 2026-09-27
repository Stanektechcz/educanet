<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog JS msgid domény „js_games“ (angličtina).
 * assets/robots-v58.js (viz lang/domains_v59.php, PLAN_I18N.md B6e). Vkládá edu_tr_json_js().
 * Jen žákovské texty (editor skriptu, přehrávač, stav zápasů); učitelský/projektorový polling
 * (initPoll, "Stav zápasu se změnil…") zůstává česky. Vlastník: builder i18n_teamgames_play.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // Stavy robota (ACT)
    'čeká' => 'pending',
    'jede' => 'moving',
    'zablokovaný' => 'blocked',
    'zvedl(a) balíček' => 'picked up a package',
    'doručil(a) náklad' => 'delivered cargo',
    'opravuje uzel' => 'fixing a node',
    'nabíjí se' => 'charging',
    'chyba ve skriptu' => 'script error',
    'přes limit kroků / chladne' => 'over step limit / cooling down',
    'málo energie' => 'low energy',
    'akce se nepovedla' => 'action failed',
    'dokončil(a) opravu uzlu' => 'finished fixing a node',

    // Přehrávač záznamu
    'Pauza' => 'Pause',
    'Přehrát' => 'Play',
    'Záznam' => 'Recording',
    '{n} tahů' => ['one' => '{n} turn', 'other' => '{n} turns'],
    'TY' => 'YOU',
    '{name} (č. {n})' => '{name} (No. {n})',
    ' (ty)' => ' (you)',
    'Robot' => 'Robot',
    'Rozbil se uzel č. {n} – kdo ho opraví?' => 'Node No. {n} broke – who will fix it?',
    ' (+{n} b)' => ' (+{n} pts)',
    'Tah {n} z {m}' => 'Turn {n} of {m}',
    'Tah {n} z {m}.' => 'Turn {n} of {m}.',
    'Tvůj robot: {points} bodů, energie {energy}, náklad {cargo}, {status}.' => 'Your robot: {points} points, energy {energy}, cargo {cargo}, {status}.',
    'A další události: {n}.' => 'And {n} more events.',
    'Tah {n}: {text}' => 'Turn {n}: {text}',
    'Žádné události.' => 'No events.',
    'Statistiky tvého robota' => 'Your robot statistics',
    'Body' => 'Points',
    'Doručené balíčky' => 'Delivered packages',
    'Opravy (dokončené uzly)' => 'Repairs (nodes fixed)',
    'Nabíjení' => 'Charges',
    'Pohyby / zablokováno' => 'Moves / blocked',
    'Spotřebovaná energie' => 'Energy used',
    'Efektivita (body na 100 energie)' => 'Efficiency (points per 100 energy)',
    'Průměr kroků skriptu na tah' => 'Average script steps per turn',
    'Chyby / přetečení kroků' => 'Errors / step overflow',
    'Žádné chyby ani varování. Pěkné!' => 'No errors or warnings. Nice!',
    'Chyby a varování' => 'Errors and warnings',
    ', celkem {n}×' => ', {n}× in total',
    'Řádek {n}: ' => 'Line {n}: ',
    ' Tip: {tip}' => ' Tip: {tip}',
    ' (poprvé v tahu {n}{extra})' => ' (first in turn {n}{extra})',
    'Ukaž řádek {n}' => 'Show line {n}',
    'Na řádek {n}' => 'To line {n}',
    ' – příliš dlouhé!' => ' – too long!',

    // Žákovská stránka (editor, odevzdání)
    'Server odpověděl nečekaně ({n}).' => 'The server responded unexpectedly ({n}).',
    'Nepodařilo se spojit se serverem. Zkontroluj připojení.' => "Couldn't connect to the server. Check your connection.",
    'Simuluji…' => 'Simulating…',
    'Nepovedlo se.' => "Didn't work.",
    'Skript má chybu – oprav ji a zkus to znovu.' => 'The script has an error – fix it and try again.',
    'Skript je v pořádku.' => 'The script is fine.',
    ' (cvičný soupeř {n})' => ' (practice opponent {n})',
    'Hotovo: {points} bodů za {turns} tahů' => 'Done: {points} points for {turns} turns',
    '. Chyby a tipy najdeš pod přehrávačem.' => '. You’ll find errors and tips below the player.',
    'Koncept uložen v {time}.' => 'Draft saved at {time}.',
    'Odevzdáno! Pokus č. {n} – počítá se poslední platný skript.' => 'Submitted! Attempt No. {n} – the last valid script counts.',
    'Nahradit tvůj skript ukázkou? Neuložené změny se ztratí.' => 'Replace your script with the example? Unsaved changes will be lost.',
    'Ukázka načtena. Zkus ji otestovat a pak upravit.' => 'Example loaded. Try testing it and then edit it.',
    'Načítám záznam zápasu…' => 'Loading the match recording…',
    'Záznam se nepodařilo načíst.' => "Couldn't load the recording.",
    ' Připsali jsme ti XP za zápas.' => ' We credited you XP for the match.',
    'Záznam zápasu je připravený v přehrávači.' => 'The match recording is ready in the player.',
    'Zatím žádný zápas.' => 'No match yet.',
    '{title} (do {time})' => '{title} (by {time})',

    // Stavy zápasu
    'Příprava – odevzdávej' => 'In progress – submit your entry',
    'Uzávěrka – čeká na simulaci' => 'Deadline passed – waiting for the simulation',
    'Odehráno' => 'Played',

    // Tabulka výsledků a zápasy
    'Místo' => 'Place',
    'Tým' => 'Team',
    'Robotů' => 'Robots',
    'Doručeno / opravy' => 'Delivered / repairs',
    'Týmy' => 'Teams',
    'Každý sám za sebe' => 'Everyone for themselves',
    'uzávěrka {time}' => 'deadline {time}',
    'odevzdáno {n}' => 'submitted {n}',
    'Tvůj tým: {name}' => 'Your team: {name}',
    ' – {mates}' => ' – {mates}',
    ' ({n} hráčů)' => ' ({n} players)',
    'Tvůj robot v zápase hrál.' => 'Your robot played in the match.',
    'Do tohohle zápasu jsi neodevzdal(a).' => 'You didn\'t submit an entry for this match.',
    'Tvoje odevzdání: {time} (pokus {n})' => 'Your submission: {time} (attempt {n})',
    'Zatím jsi neodevzdal(a).' => "You haven't submitted yet.",
    'Tvoje umístění: {n}. místo' => 'Your placing: {n} place',
    'Výsledky – {title}' => 'Results – {title}',
    'Přehrát zápas' => 'Replay match',
    'V tomhle pololetí se ještě nehrálo.' => 'No matches have been played this half-year yet.',

    // Ligová tabulka
    'Liga · {label}' => 'League · {label}',
    'Ligová tabulka' => 'League table',
    'Pořadí' => 'Ranking',
    'Hráč' => 'Player',
    'Zápasy' => 'Matches',
    'Výhry' => 'Wins',
    'Ligové body' => 'League points',
    'Body robotů' => 'Robot points',
];
