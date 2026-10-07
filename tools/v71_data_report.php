<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v71 · report kvality dat úložiště (CLI nástroj, NE audit – nikdy není v run_audits).
 *
 *   php tools/v71_data_report.php --storage=<cesta k úložišti> --out=<adresář mimo projekt i úložiště>
 *
 * Jen čtení: úložiště se nejdřív zkopíruje do soukromého dočasného adresáře (0700) a aplikace běží nad kopií;
 * originál se otevírá jen pro čtení a jeho SHA-256 (celý strom) se ověří před a po běhu. Kopie se na konci smaže.
 * Výstup: data-report-<čas>.json + .txt (jen souhrnné počty a 8znakové prefixy hashů klíčů, NIKDY jména).
 * Kontroly: osiřelé záznamy (postup, body, skóre úkolů, dovednosti, úkoly učitele), neplatná čísla lekcí,
 * chybějící student_id, výjimky kalendáře s neznámou třídou/datem, stav cache (runtime + model lekce).
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/report_v71.php';

$storage = rp71_arg($argv, 'storage');
if ($storage === null || trim($storage) === '') rp71_fail('Chybí --storage=<cesta k úložišti> (report nikdy nečte úložiště implicitně).');
if (!is_dir($storage) || (glob(rtrim($storage, '/\\') . '/*.json.php') ?: []) === []) rp71_fail('--storage neukazuje na úložiště EDUCANET (žádné *.json.php).');
$storage = rp71_norm_path($storage);
$outDir = rp71_out_dir(rp71_arg($argv, 'out'), $ROOT, [$storage]);
$before = rp71_tree_hash($storage);

// Kopie úložiště (jen čtení originálu) – aplikace pak běží výhradně nad kopií.
$tmp = rp71_temp_dir('data');
$copy = $tmp . '/storage';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
mkdir($copy, 0700);
foreach ($it as $item) {
    $rel = substr(str_replace('\\', '/', $item->getPathname()), strlen($storage) + 1);
    if ($item->isDir()) { @mkdir($copy . '/' . $rel, 0700, true); continue; }
    if (!@copy($item->getPathname(), $copy . '/' . $rel)) rp71_fail('Soubor úložiště nelze přečíst (oprávnění?).');
}
putenv('EDUCANET_STORAGE_DIR=' . $copy);
$_ENV['EDUCANET_STORAGE_DIR'] = $copy;
putenv('EDUCANET_LM71_CACHE_DIR=' . $tmp . '/lesson_model');

require_once $ROOT . '/bootstrap.php';
foreach (['identity_v58.php', 'teacher_tasks.php', 'lesson_model_v71.php'] as $lib) if (is_file($ROOT . '/' . $lib)) require_once $ROOT . '/' . $lib;

/** Hash žáka z klíče (`třída:student:H` i `třída:s:H`). */
function dr71_hash(string $key): string
{
    return preg_match('/:(?:student|s):([a-f0-9]{24})(?:$|\|)/', $key, $m) === 1 ? $m[1] : '';
}

$classes = LM71_CLASSES;
$roster = [];
$rosterCount = [];
foreach ($classes as $c) {
    $roster[$c] = [];
    foreach (project_students_for_class($c) as $key => $_) { $h = dr71_hash((string)$key); if ($h !== '') $roster[$c][$h] = true; }
    $rosterCount[$c] = count($roster[$c]);
}
$read = static fn(string $file): array => storage_read(STORAGE_DIR . '/' . $file, false);
$orphans = [];
$orphanSamples = [];
$addOrphan = static function (string $store, string $classId, string $key) use (&$orphans, &$orphanSamples, $roster): void {
    $h = dr71_hash($key);
    $known = isset($roster[$classId]) && $h !== '' && isset($roster[$classId][$h]);
    $orphans[$store] ??= ['zaznamu' => 0, 'osirelych' => 0];
    $orphans[$store]['zaznamu']++;
    if ($known) return;
    $orphans[$store]['osirelych']++;
    if (count($orphanSamples[$store] ?? []) < 5) $orphanSamples[$store][] = rp71_hash_prefix($key);
};

// postup v lekcích (třída|klíč → číslo lekce → řádek)
$invalidLessons = ['progress_v56' => 0, 'tutorial_v52_scores' => 0, 'lesson_sessions' => 0];
foreach ($read('progress_v56.json.php') as $key => $rows) {
    [$c, $sk] = array_pad(explode('|', (string)$key, 2), 2, '');
    $addOrphan('progress_v56', $c, $sk);
    foreach (array_keys(is_array($rows) ? $rows : []) as $n) if (!ctype_digit((string)$n) || (int)$n < 1 || (int)$n > LM71_LESSONS) $invalidLessons['progress_v56']++;
}
foreach ($read('tutorial_v52_scores.json.php') as $key => $rows) {
    [$c, $sk] = array_pad(explode('|', (string)$key, 2), 2, '');
    $addOrphan('tutorial_v52_scores', $c, $sk);
    foreach (array_keys(is_array($rows) ? $rows : []) as $ex) {
        if (preg_match('/^L(\d+):/', (string)$ex, $m) === 1 && ((int)$m[1] < 1 || (int)$m[1] > LM71_LESSONS)) $invalidLessons['tutorial_v52_scores']++;
    }
}
foreach ($read('points_v53.json.php') as $key => $_) {
    [$c, $sk] = array_pad(explode('|', (string)$key, 2), 2, '');
    $addOrphan('points_v53', $c, $sk);
}
foreach ($read('skill_progress.json.php') as $key => $row) {
    $c = is_array($row) ? (string)($row['class_id'] ?? '') : '';
    $addOrphan('skill_progress', $c !== '' ? $c : (string)strstr((string)$key, ':', true), (string)$key);
}
if (function_exists('teacher_tasks_path')) foreach (storage_read(teacher_tasks_path(), false) as $row) {
    if (is_array($row) && (string)($row['student_key'] ?? '') !== '') $addOrphan('teacher_tasks', (string)($row['class_id'] ?? ''), (string)$row['student_key']);
}
$sessions = $read('lesson_sessions.json.php');
foreach ($sessions as $row) {
    if (!is_array($row) || (string)($row['kind'] ?? 'work') !== 'work') continue;
    $n = (int)($row['lesson_number'] ?? 0);
    if ($n < 1 || $n > LM71_LESSONS || !in_array((string)($row['class_id'] ?? ''), $classes, true)) $invalidLessons['lesson_sessions']++;
}
$badExceptions = 0;
$exceptions = $read('adaptive_calendar_exceptions.json.php');
foreach ($exceptions as $row) {
    if (!is_array($row)) { $badExceptions++; continue; }
    $date = (string)($row['date'] ?? '');
    if (!in_array((string)($row['class_id'] ?? ''), $classes, true) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1 || strtotime($date) === false) $badExceptions++;
}

// identita: student_id u žáků v seznamu a u účtů
$missingId = ['zaku' => 0, 'bez_student_id' => 0, 'ucty' => 0, 'ucty_bez_student_id' => 0];
foreach ($classes as $c) foreach (project_students_for_class($c) as $student) {
    $missingId['zaku']++;
    $id = function_exists('identity58_id_for_student') ? identity58_id_for_student($c, (string)($student['label'] ?? '')) : null;
    if ($id === null || $id === '') $missingId['bez_student_id']++;
}
foreach ($read('student_accounts.json.php') as $row) {
    if (!is_array($row)) continue;
    $missingId['ucty']++;
    if (trim((string)($row['student_id'] ?? '')) === '') $missingId['ucty_bez_student_id']++;
}

$cache = lm71_cache_status($ROOT . '/cache/lesson_model');
$staleCache = count(array_filter($cache['runtime'], static fn(string $s): bool => $s !== 'aktuální')) + count(array_filter($cache['model'], static fn(string $s): bool => $s === 'zastaralá'));
$after = rp71_tree_hash($storage);
$unchanged = $after === $before;
$findings = array_sum(array_column($orphans, 'osirelych')) + array_sum($invalidLessons) + $badExceptions + $missingId['bez_student_id'] + $missingId['ucty_bez_student_id'] + $staleCache;

$report = [
    'report' => 'v71_data_report', 'vytvoreno' => date(DATE_ATOM), 'uloziste_otisk' => substr($before, 0, 12), 'uloziste_beze_zmeny' => $unchanged,
    'zaci_v_seznamu' => $rosterCount, 'osirele_zaznamy' => $orphans, 'osirele_prefixy' => $orphanSamples, 'neplatne_lekce' => $invalidLessons,
    'vyjimky_kalendare' => ['celkem' => count($exceptions), 'neplatnych' => $badExceptions], 'identita' => $missingId, 'cache' => $cache, 'nalezu_celkem' => $findings,
];
$lines = [
    'EDUCANET v71 · report dat (' . date('j. n. Y H:i') . ') · úložiště ' . substr($before, 0, 12) . ' · beze změny: ' . ($unchanged ? 'ano' : 'NE'),
    'Žáci v seznamu: ' . implode(', ', array_map(static fn(string $c, int $n): string => $c . ' ' . $n, array_keys($rosterCount), $rosterCount)),
    'Osiřelé záznamy: ' . ($orphans === [] ? 'žádné záznamy' : implode(', ', array_map(static fn(string $s, array $r): string => $s . ' ' . $r['osirelych'] . '/' . $r['zaznamu'], array_keys($orphans), $orphans))),
    'Neplatná čísla lekcí: ' . implode(', ', array_map(static fn(string $s, int $n): string => $s . ' ' . $n, array_keys($invalidLessons), $invalidLessons)),
    'Výjimky kalendáře: ' . count($exceptions) . ', neplatných ' . $badExceptions,
    sprintf('Identita: žáků %d, bez student_id %d · účtů %d, bez student_id %d', $missingId['zaku'], $missingId['bez_student_id'], $missingId['ucty'], $missingId['ucty_bez_student_id']),
    'Cache runtime: ' . implode(', ', array_map(static fn(string $c, string $s): string => $c . '=' . $s, array_keys($cache['runtime']), $cache['runtime']))
        . ' · model lekce: ' . implode(', ', array_map(static fn(string $c, string $s): string => $c . '=' . $s, array_keys($cache['model']), $cache['model'])),
    'Nálezů celkem: ' . $findings,
];
if (!$unchanged) {
    $lines[] = 'POZOR: otisk úložiště se během reportu změnil – nejspíš souběžný zápis aplikace (report sám zapisuje jen do kopie). Spusť znovu mimo provoz.';
}
$paths = rp71_write($outDir, 'data-report', $report, $lines);
echo implode(PHP_EOL, $lines) . PHP_EOL . 'Zapsáno: ' . basename($paths['json']) . ', ' . basename($paths['txt']) . PHP_EOL . ($unchanged ? 'V71_DATA_REPORT_OK' : 'V71_DATA_REPORT_CHANGED') . PHP_EOL;
exit($unchanged ? 0 : 3);
