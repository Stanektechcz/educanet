<?php

declare(strict_types=1);

/**
 * ?view=join – vstup kódem hodiny (v53).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'join') { sess53_render_join($modules, $flash); exit; }
