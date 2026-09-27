<?php

declare(strict_types=1);

/**
 * ?view=lab a ?view=prikazy – Linux Lab a třídní závod (v57).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

// v57 · Linux Lab: domov Labu, mise, pískoviště, příručka příkazů a třídní závod.
if (in_array($view, ['lab', 'prikazy'], true)) {
    guarded_study_redirect();
    if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled((string)$classId)) { $_SESSION['flash'] = tr('Linux Lab je pro vaši třídu zatím vypnutý.'); redirect_to('?view=hodina'); }
    if (function_exists('arena57_award_finished_xp')) arena57_award_finished_xp((string)$classId, adaptive_student_key((string)$classId));
    if ($view === 'prikazy' && function_exists('lab57_render_manual')) { lab57_render_manual((string)$classId, $module, isset($_GET['c']) && is_string($_GET['c']) ? $_GET['c'] : null); exit; }
    $v57Race = isset($_GET['zavod']) && is_string($_GET['zavod']) ? $_GET['zavod'] : '';
    if ($v57Race !== '' && function_exists('arena57_render_student')) { arena57_render_student((string)$classId, $module, $v57Race, (string)($_GET['uroven'] ?? ''), $flash); exit; }
    $v57Level = isset($_GET['uroven']) && is_string($_GET['uroven']) ? $_GET['uroven'] : '';
    // v58 · denní úloha opakování (LAB-09): ?view=lab&uroven=<id>&opakovani=<RRRRMMDD> běží v kontextu review:.
    $v58Review = isset($_GET['opakovani']) && is_string($_GET['opakovani']) ? $_GET['opakovani'] : '';
    if ($v58Review !== '' && $v57Level !== '' && function_exists('lab58_review_render_student')) { lab58_review_render_student((string)$classId, $module, $v58Review, $v57Level, $flash); exit; }
    if ($v57Level !== '' && function_exists('lab57_render_level')) { lab57_render_level((string)$classId, $module, $v57Level, $flash); exit; }
    // v58 · sekce labu: mapa dovedností (EDU-04) a dnešní opakování (LAB-09).
    $v58Section = isset($_GET['sekce']) && is_string($_GET['sekce']) ? $_GET['sekce'] : '';
    if ($v58Section !== '' && function_exists('lab58_render_section')) { lab58_render_section((string)$classId, $module, $v58Section); exit; }
    if (function_exists('lab57_render_home')) { lab57_render_home((string)$classId, $module, $flash); exit; }
}
