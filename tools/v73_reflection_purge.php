<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v73 · retence reflexí hodin (storage/lesson_reflections_v73.json.php).
 *   php tools/v73_reflection_purge.php --dry-run            jen spočítá, co by se smazalo (nic nezapíše)
 *   php tools/v73_reflection_purge.php --apply [--days=365] smaže reflexe starší než retence (výchozí LR73_RETENTION_DAYS) a nad strop na třídu
 * Úložiště = STORAGE_DIR (EDUCANET_STORAGE_DIR); před --apply zálohujte storage/. Výstup jen počty – žádné texty ani autory.
 * Konec: V73_REFLECTION_PURGE_OK mode=… removed=N kept=M (exit 0), jinak chyba (exit 1).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/src/lessons/lesson_reflect_v73.php';

$args = array_slice($argv, 1);
$dry = in_array('--dry-run', $args, true);
$apply = in_array('--apply', $args, true);
$days = LR73_RETENTION_DAYS;
foreach ($args as $a) if (preg_match('/^--days=(\d{1,4})$/', $a, $m) === 1) $days = (int)$m[1];
if ($dry === $apply || $days < 30) {
    fwrite(STDERR, "Použití: --dry-run | --apply [--days=N, N ≥ 30]\n");
    exit(1);
}
$path = lr73_path();
$count = static function (array $d): int { $n = 0; foreach ((array)($d['classes'] ?? []) as $rows) $n += is_array($rows) ? count($rows) : 0; return $n; };
if (!is_file($path)) {
    echo 'V73_REFLECTION_PURGE_OK mode=' . ($dry ? 'dry-run' : 'apply') . " removed=0 kept=0 (soubor reflexí zatím neexistuje)\n";
    exit(0);
}
$now = time();
if ($dry) {
    [$after, $removed] = lr73_purge(storage_read($path, false), $now, $days);
    echo 'V73_REFLECTION_PURGE_OK mode=dry-run removed=' . $removed . ' kept=' . $count($after) . ' days=' . $days . "\n";
    exit(0);
}
$removed = 0;
$kept = 0;
storage_update($path, static function (array $d) use ($now, $days, $count, &$removed, &$kept): array {
    [$d, $removed] = lr73_purge($d, $now, $days);
    $kept = $count($d);
    return $d;
});
echo 'V73_REFLECTION_PURGE_OK mode=apply removed=' . $removed . ' kept=' . $kept . ' days=' . $days . "\n";
exit(0);
