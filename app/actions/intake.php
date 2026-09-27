<?php

declare(strict_types=1);

/**
 * POST intake_* – seznamovací dotazník (v51).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (str_starts_with($action, 'intake_')) intake_v51_handle_post($action, $modules);
