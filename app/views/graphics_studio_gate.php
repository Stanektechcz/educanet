<?php

declare(strict_types=1);

/**
 * Vstupní podmínky ?view=graphics_studio (1.A/2.A); stránku kreslí graphics_studio.php.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'graphics_studio' && in_array($classId, ['class_1a', 'class_2a'], true)) {
    $graphicsSimulationMap = is_array($simulations[$classId] ?? null) ? $simulations[$classId] : [];
    if (!is_array($completedTestResult)) {
        $_SESSION['flash'] = tr('Nejdřív dokonči startovní test. Knowledgebase můžeš studovat předem, ale Studio patří až za test.');
        redirect_to('?view=dashboard');
    }
    if (!learning_graphics_core_complete((string)$classId, $graphicsSimulationMap)) {
        $_SESSION['flash'] = tr('Studio se odemkne po třech základních lekcích: hierarchie, kompozice/grid a kontrast.');
        redirect_to(module_url('kb_lesson', ['topic' => 'hierarchy']));
    }
}
