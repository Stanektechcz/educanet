<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_events“ (angličtina).
 * assets/arena-events-v58.js – žákovské UI CTF týdne a Incidentů (viz PLAN_I18N.md B6c).
 * Klíč = přesně český text ze zdroje. Vlastník: i18n_events (B6c).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Čas vypršel.' => 'Time is up.',
    'Zbývá {n} minuta.' => '{n} minute left.',
    'Zbývá {n} minuty.' => '{n} minutes left.',
    'Zbývá {n} minut.' => ['one' => 'Remaining: {n} minute.', 'few' => 'Remaining: {n} minutes.', 'other' => 'Remaining: {n} minutes.'],
    'Nepodařilo se spustit scénář.' => 'Failed to start the scenario.',
    'Nepodařilo se to.' => "That didn't work.",
    'Pauza' => 'Pause',
    'Pokračovat' => 'Continue',
    'Postmortem se nepodařilo odeslat.' => 'Failed to submit the post-mortem.',
];
