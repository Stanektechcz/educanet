<?php

declare(strict_types=1);

/**
 * ?view=intake, my_intake, my_intake_file – seznamovací dotazník (v51).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'intake') { intake_v51_render_intake_view((string)$classId, $modules, $flash); exit; }
if ($view === 'my_intake') { intake_v51_render_my_intake_view((string)$classId, $modules, $flash); exit; }
if ($view === 'my_intake_file') {
    $ownIntake = intake_v51_response_for_student((string)$classId, intake_v51_current_label());
    if (!$ownIntake) { http_response_code(404); exit(tr('Soubor není dostupný.')); }
    intake_v51_stream_artifact($ownIntake);
}
