<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v66 · tools/v66_morning_build.php – ranní přehled „5 minut ráno“ pro cockpit.
 *
 *   php tools/v66_morning_build.php [--class=class_3a] [--json]
 *
 * Pro každou třídu sestaví seznam zásahů seřazený podle dopadu (uvízl v kroku cesty, 7 dní bez aktivity, třída nezvládá
 * kompetenci) a zapíše storage/assessment_v66/morning_<třída>.json.php. Cockpit při požadavku jen čte tuto cache
 * (bez ní ukáže „připravuje se“). Cache obsahuje jen zkrácené hashe žáků, žádná jména.
 * Spouštět každý školní den ráno před výukou (docs/deploy/educanet.cron.example).
 * Konec: V66_MORNING_BUILD_OK classes=N items=N (exit 0).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/identity_v58.php';
require_once dirname(__DIR__) . '/teacher_class_dashboard.php';
require_once dirname(__DIR__) . '/evidence_v62_adapters.php';
require_once dirname(__DIR__) . '/assessment_v66_build.php';

$classFilter = '';
foreach ($argv as $arg) {
    if (str_starts_with((string)$arg, '--class=')) $classFilter = substr((string)$arg, 8);
}
$asJson = in_array('--json', $argv, true);
$report = [];
try {
    foreach (array_keys($modules) as $classId) {
        if ($classFilter !== '' && $classFilter !== (string)$classId) continue;
        $report[(string)$classId] = count((array)m66_build((string)$classId)['items']);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'V66_MORNING_BUILD_FAIL ' . get_class($e) . "\n");
    exit(1);
}
if ($asJson) {
    echo json_encode($report, JSON_PRETTY_PRINT) . "\n";
} else {
    foreach ($report as $classId => $count) echo $classId . ' items=' . $count . "\n";
}
echo 'V66_MORNING_BUILD_OK classes=' . count($report) . ' items=' . array_sum($report) . "\n";
exit(0);
