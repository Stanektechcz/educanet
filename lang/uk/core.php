<?php

declare(strict_types=1);

/** v58 · OPS-02 – каталог «core» (українська). */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'lang.label' => 'Мова',
    'lang.apply' => 'Застосувати',
    'lang.invalid' => 'Ця мова поки що не підтримується.',
    'lang.content_note' => 'Навчальні матеріали поки що доступні лише чеською.',
];
