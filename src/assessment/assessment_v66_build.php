<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/assessment_v66.php';
require_once __DIR__ . '/integrity_v66.php';
require_once __DIR__ . '/grading_v66.php';
require_once __DIR__ . '/morning_v66.php';

/**
 * EDUCANET v66 · sestavení cache třídy (položková analýza, štítky integrity, návrhy hodnocení, ranní přehled) a retence.
 * Volá je cron (tools/v66_item_analysis.php, tools/v66_morning_build.php) a tlačítko „Přepočítat“ v cockpitu (POST a66_recompute).
 * Cockpit při GET nikdy nepočítá, jen čte tyto cache.
 */

/** Přepočítá analýzu, integritu a návrhy třídy (pokusy se čtou jednou). @return array{attempts:int,tests:int,flags:int,proposals:int} */
function a66_recompute_class(string $classId, ?int $now = null): array
{
    $now ??= time();
    $attempts = a66_attempts($classId);
    $items = a66_items_build($classId, $now, $attempts);
    $flags = i66_store($classId, i66_scan($attempts));
    $proposals = g66_class_build($classId, $now);
    return ['attempts' => count($attempts), 'tests' => count((array)$items['tests']), 'flags' => $flags, 'proposals' => count((array)$proposals['rows'])];
}

/** Retence v66: štítky integrity a ranní log z minulého školního roku, převzaté známky odešlých žáků. @return array<string,mixed> */
function a66_retention_purge(bool $dryRun, ?int $now = null): array
{
    return ['integrity' => i66_retention_purge($dryRun, $now)['purge'], 'morning_log' => m66_log_purge($dryRun, $now)['purge'], 'accepted' => g66_retention_purge($dryRun, $now)['purge'], 'dry_run' => $dryRun];
}
