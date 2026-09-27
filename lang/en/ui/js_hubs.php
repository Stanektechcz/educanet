<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_hubs“ (angličtina).
 * JS: assets/one-task-v50-5.js, assets/hands-on-v50.js (viz lang/domains_v59.php).
 * Vlastník: i18n builder B4.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Server nevrátil platnou odpověď.' => 'The server did not return a valid response.',
    'Krok se nepodařilo uložit.' => 'The step could not be saved.',
    'Hotovo. Krok je uložený.' => 'Done. The step is saved.',
    'Další krok →' => 'Next step →',
    'Opravit a ověřit znovu →' => 'Fix and check again →',
    'Nejdřív dokonči všechny části tohoto jediného kroku.' => 'Finish all parts of this single step first.',
    'Vyber jednu odpověď a potom ji ověř.' => 'Pick one answer and then check it.',
    'Ověřuji…' => 'Checking…',
    'Tento závěr ještě nesedí. Vrať se k důkazům a zkus to znovu.' => "This conclusion doesn't hold yet. Go back to the evidence and try again.",
    '✓ Správně. Další krok je připravený.' => '✓ Correct. The next step is ready.',
    'Krok se nepodařilo ověřit.' => 'The step could not be checked.',
    'Ještě to nesedí. Zkus si princip vysvětlit jinak.' => "It doesn't fit yet. Try explaining the principle a different way.",
    '✓ Krok vysvětlení je hotový.' => '✓ The explanation step is done.',
    'Ukládám…' => 'Saving…',
    'Uloženo' => 'Saved',
    'Uložení se nezdařilo' => 'Saving failed',
    'Uzel' => 'Node',
    'Bez dalších metadat.' => 'No further metadata.',
    'Znovu od začátku ↻' => 'Restart from the beginning ↻',
];
