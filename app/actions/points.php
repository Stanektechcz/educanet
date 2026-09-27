<?php

declare(strict_types=1);

/**
 * POST pts53_hint a tut52_score (JSON).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'pts53_hint') {
    $hintClass = current_class_id($modules);
    if ($hintClass === null) { http_response_code(401); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok' => false]); exit; }
    pts53_handle_hint_post($hintClass);
}
if ($action === 'tut52_score') {
    $scoreClassId = current_class_id($modules);
    if ($scoreClassId === null) { http_response_code(401); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>false]); exit; }
    tut52_handle_score_post($scoreClassId);
}
