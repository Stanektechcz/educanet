<?php

declare(strict_types=1);

/**
 * POST akce žáka v80 (podpora učení): lr80_hint, lr80_rep_answer, lr80_w_check, lr80_goal_add, lr80_goal_remove.
 * Router: app/routes.php ('actions_class'). CSRF a třídu ($classId) už zajistil index.php; identita žáka jen ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (in_array($action, LR80_ACTIONS, true)) {
    lr80_handle_post($action, (string)$classId);
}
