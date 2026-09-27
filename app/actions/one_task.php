<?php

declare(strict_types=1);

/**
 * POST v505_* – One Task.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (str_starts_with($action, 'v505_')) {
    $actionClassId = current_class_id($modules);
    if ($actionClassId === null || !isset($modules[$actionClassId])) {
        if (in_array($action, ['v505_draft_save','v505_event'], true)) {
            http_response_code(401); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>false,'error'=>'auth'], JSON_UNESCAPED_UNICODE); exit;
        }
        $_SESSION['flash'] = tr('Nejdřív se přihlas.'); redirect_to('?view=home');
    }
    if (v505_handle_post($actionClassId, $modules[$actionClassId], $action)) exit;
}
