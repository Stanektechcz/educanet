<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · přepis sekce „AI prompty pro studenta“ ve 112 lesson kitech (materials/lesson_kits/<třída>/lesson_NN.md).
 *   php tools/v72_1_ai_prompts_build.php            náhled: kolik souborů by se změnilo (nic nezapisuje)
 *   php tools/v72_1_ai_prompts_build.php --apply    zapíše změněné kity (atomicky přes dočasný soubor) a sloupec prompts v LESSON_KITS_INDEX.csv
 * Čte jen zdroje obsahu (lm71) nad dočasným prázdným úložištěm – ostrou storage/ ani data žáků nečte. Konec: AI_PROMPTS_BUILD_OK.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v721-aip')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
require_once $ROOT . '/bootstrap.php';
foreach (['tutorial_v52.php', 'learning_v56.php', 'lesson_model_v71.php'] as $lib) require_once $ROOT . '/' . $lib;
require_once __DIR__ . '/lib/ai_prompts_v72_1_build.php';

$apply = in_array('--apply', $argv, true);
$changed = 0;
$total = 0;
foreach (LM71_CLASSES as $classId) {
    for ($n = 1; $n <= LM71_LESSONS; $n++) {
        $path = $ROOT . '/materials/lesson_kits/' . $classId . '/lesson_' . str_pad((string)$n, 2, '0', STR_PAD_LEFT) . '.md';
        if (!is_file($path)) { fwrite(STDERR, "FAIL chybí kit {$classId} L{$n}\n"); exit(1); }
        $total++;
        $old = (string)file_get_contents($path);
        $new = aip721b_replace($old, aip721b_section($classId, $n));
        if ($new === $old) continue;
        if ($new === '' || !str_contains($new, '## 7.')) { fwrite(STDERR, "FAIL kit {$classId} L{$n} nemá sekci 6/7\n"); exit(1); }
        $changed++;
        if ($apply) {
            $tmpFile = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
            if (file_put_contents($tmpFile, $new, LOCK_EX) === false || !rename($tmpFile, $path)) { @unlink($tmpFile); fwrite(STDERR, "FAIL zápis {$classId} L{$n}\n"); exit(1); }
        }
    }
}
$csvPath = $ROOT . '/materials/lesson_kits/LESSON_KITS_INDEX.csv';
$csv = (string)file_get_contents($csvPath);
$csvNew = (string)preg_replace('/^(class_[a-z0-9]+,\d+,".*",\d+,\d+),\d+,(\d+)$/mu', '${1},6,${2}', $csv);
if ($csvNew !== $csv) {
    $changed++;
    if ($apply) file_put_contents($csvPath, $csvNew, LOCK_EX);
}
echo ($apply ? 'Zapsáno' : 'Náhled') . ": kitů {$total}, změněno {$changed} (včetně indexu)\n";
echo "AI_PROMPTS_BUILD_OK total={$total} changed={$changed}" . ($apply ? '' : ' (dry-run)') . "\n";
