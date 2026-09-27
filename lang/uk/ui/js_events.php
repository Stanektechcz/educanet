<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_events“ (ukrajinština).
 * assets/arena-events-v58.js – žákovské UI CTF týdne a Incidentů (viz PLAN_I18N.md B6c).
 * Klíč = přesně český text ze zdroje. Vlastník: i18n_events (B6c).
 * Neformální tykání („ти“); revize rodilým mluvčím před zveřejněním (ROADMAP_V59.md).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Čas vypršel.' => 'Час вийшов.',
    'Zbývá {n} minuta.' => 'Залишилась {n} хвилина.',
    'Zbývá {n} minuty.' => 'Залишилось {n} хвилини.',
    'Zbývá {n} minut.' => ['one' => 'Залишилась {n} хвилина.', 'few' => 'Залишилось {n} хвилини.', 'many' => 'Залишилось {n} хвилин.', 'other' => 'Залишилось {n} хвилин.'],
    'Nepodařilo se spustit scénář.' => 'Не вдалося запустити сценарій.',
    'Nepodařilo se to.' => 'Не вдалося.',
    'Pauza' => 'Пауза',
    'Pokračovat' => 'Продовжити',
    'Postmortem se nepodařilo odeslat.' => 'Не вдалося надіслати розбір.',
];
