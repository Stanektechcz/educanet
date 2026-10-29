<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · tools/v67_rollover_plan.php – náhled dopadu přechodu roku na data vrstev v62–v67 (jen čte, nic nezapisuje).
 *   php tools/v67_rollover_plan.php [--year=2027]
 * Výstup: počty souborů důkazů/cest/portfolií/cílů a rozpracovaných projektů; žádná jména. Konec: V67_ROLLOVER_PLAN_OK.
 */
putenv('EDUCANET_STORAGE_READONLY=1');
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/src/student/intake_v51.php';
foreach (['src/student/identity_v58.php', 'src/student/identity_v58_rollover.php', 'src/assessment/competencies_v62.php', 'src/assessment/evidence_v62.php', 'src/paths/paths_v63.php', 'src/projects/projects_v60.php', 'src/projects/projects_v65.php', 'src/ops/rollover_v67.php'] as $lib) require_once dirname(__DIR__) . '/' . $lib;
$year = (int)date('Y');
foreach ($argv as $a) if (str_starts_with((string)$a, '--year=')) $year = (int)substr((string)$a, 7);
$plan = rollover67_plan($year);
foreach ($plan as $k => $v) echo $k . '=' . (is_array($v) ? json_encode($v) : $v) . "\n";
echo "V67_ROLLOVER_PLAN_OK\n";
