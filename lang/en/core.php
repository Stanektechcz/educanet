<?php

declare(strict_types=1);

/** v58 · OPS-02 – catalogue "core" (English). */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'lang.label' => 'Language',
    'lang.apply' => 'Apply',
    'lang.invalid' => 'This language is not supported yet.',
    'lang.content_note' => 'Learning content is available in Czech only for now.',
];
