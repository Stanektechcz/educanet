<?php

declare(strict_types=1);

/** v58 · OPS-02 – katalog „core“ (čeština, výchozí jazyk). Klíče bez prefixu domény: t('core.lang.label'). */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'lang.label' => 'Jazyk',
    'lang.apply' => 'Použít',
    'lang.invalid' => 'Tento jazyk zatím nepodporujeme.',
    'lang.content_note' => 'Učební obsah je zatím jen česky.',
];
