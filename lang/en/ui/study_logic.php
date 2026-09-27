<?php

declare(strict_types=1);

/** v59 · OPS-02 – překlady UI (en), doména study_logic (GPS kroky v44, režimy v48.1). msgid = český text ze zdroje. */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Předpověz' => 'Predict',
    'Změň' => 'Change it',
    'Sestav model' => 'Build the model',
    'Vysvětli' => 'Explain',
    'Použij jinde' => 'Apply it elsewhere',
    'Nejdřív vytvoř hypotézu.' => 'Make a hypothesis first.',
    'Manipuluj modelem a sleduj důsledek.' => 'Change the model and watch what happens.',
    'Seřaď části podle skutečného vztahu.' => 'Put the parts in order by how they really relate.',
    'Popiš příčinu, důkaz a závěr.' => 'Describe the cause, the evidence and the conclusion.',
    'Ověř princip v novém kontextu.' => 'Test the principle in a new context.',
    'Guided · více opory' => 'Guided · more support',
    'Challenge · minimum opory' => 'Challenge · minimal support',
    'Practice · vyváženě' => 'Practice · balanced',
];
