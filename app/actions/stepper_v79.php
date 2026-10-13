<?php

declare(strict_types=1);

/**
 * POST akce žáka v79: sp79_s_next / sp79_s_back / sp79_s_reset – krokové postupy (Zpět / Další / Zahodit). Ukládají jen koncept
 * do relace ($_SESSION['sp79']); do úložiště nezapisují. Router: app/routes.php ('actions_class'). CSRF a třídu ($classId) už
 * zajistil index.php; identita žáka jen ze session. Poslední krok toku odesílá původní akci (pf75_s_pin) se stejnými poli.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (in_array($action, SP79_STUDENT_ACTIONS, true)) {
    sp79_student_handle_post($action, (string)$classId);
}
