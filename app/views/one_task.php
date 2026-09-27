<?php

declare(strict_types=1);

/**
 * ?view=continue a ?view=one_task (v50.5/v50.6).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'continue') {
    guarded_study_redirect();
    redirect_to(v506_continue_url((string)$classId,$module,$nextLessons,$extendedLessons));
}

if ($view === 'one_task') {
    guarded_study_redirect();
    // v51: vysvětlení otevírá plnou krokovou lekci s interaktivním modelem, simulací a checkem (One Task ukazoval jen text).
    if ((string)($_GET['task'] ?? '') === 'kb' && isset($module['knowledgebase'][(string)($_GET['topic'] ?? '')])) {
        redirect_to(module_url('kb_lesson', ['topic' => (string)$_GET['topic']]));
    }
    $task = v505_task_from_request((string)$classId, $module, $nextLessons, $extendedLessons, $knowledgeTours, $simulations);
    v505_render_page($task, (string)$classId, $module);
    exit;
}
