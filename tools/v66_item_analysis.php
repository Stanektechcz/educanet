<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v66 · tools/v66_item_analysis.php – položková analýza testů, štítky integrity a cache návrhů hodnocení.
 *
 *   php tools/v66_item_analysis.php [--class=class_3a] [--json]
 *
 * Pro každou třídu (nebo jen zadanou) přečte pokusy z existujících úložišť (jen čtení), spočítá obtížnost, citlivost
 * a distraktory a zapíše storage/assessment_v66/items_<třída>.json.php, štítky „k ověření“ a cache návrhů
 * (proposals_<třída>.json.php; návrhy jen ve třídách, kde je hodnocení zapnuté). Cockpit tyto soubory jen čte.
 * Spouštět denně v noci z cronu (docs/deploy/educanet.cron.example). Žádná jména se neukládají ani nevypisují.
 * Konec: V66_ITEM_ANALYSIS_OK classes=N attempts=N flags=N (exit 0).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/identity_v58.php';
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
        $report[(string)$classId] = a66_recompute_class((string)$classId);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'V66_ITEM_ANALYSIS_FAIL ' . get_class($e) . "\n");
    exit(1);
}
if ($asJson) {
    echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else {
    foreach ($report as $classId => $row) echo $classId . ' attempts=' . $row['attempts'] . ' tests=' . $row['tests'] . ' flags=' . $row['flags'] . ' proposals=' . $row['proposals'] . "\n";
}
echo 'V66_ITEM_ANALYSIS_OK classes=' . count($report) . ' attempts=' . array_sum(array_column($report, 'attempts')) . ' flags=' . array_sum(array_column($report, 'flags')) . "\n";
exit(0);
