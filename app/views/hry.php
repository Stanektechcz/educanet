<?php

declare(strict_types=1);

/**
 * v58 · Týmové hry (?view=hry, ?view=hry&hra=<id>) – Štafeta, bingo, Riskuj!, přetahovaná, správci, úniková místnost.
 * Spouští ho jen router v index.php (viz app/routes.php). Stav her a XP obsluhuje teamgames_v58_api.php.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'hry') {
    guarded_study_redirect();
    tg58_render_student((string)$classId, $module, isset($_GET['hra']) && is_string($_GET['hra']) ? $_GET['hra'] : '');
    exit;
}
