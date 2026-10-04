<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v63 · tools/v63_paths_build_pre.php – předpočítá výstupy kroků predict–run–explain simulátorem Linuxu.
 *
 *   php tools/v63_paths_build_pre.php            # náhled: vypíše počet případů a zda je datový soubor aktuální
 *   php tools/v63_paths_build_pre.php --apply    # přepíše paths_v63_pre_data.php (atomicky)
 *   php tools/v63_paths_build_pre.php --check    # exit 1, pokud datový soubor neodpovídá simulátoru
 *
 * Nepotřebuje storage/ (používá dočasné úložiště), nic nespouští ani se nepřipojuje (simulátor je čisté PHP).
 * Konec: V63_PRE_BUILD_OK cases=N (nebo V63_PRE_BUILD_STALE / V63_PRE_BUILD_FAIL, nenulový exit kód).
 */

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
edu_audit_temp_storage('v63-pre');
$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
require_once $root . '/intake_v51.php';
require_once $root . '/identity_v58.php';
require_once $root . '/linux_v57_lab.php';
require_once $root . '/paths_v63.php';
require_once __DIR__ . '/lib/v63_pre.php';

$apply = in_array('--apply', $argv, true);
$check = in_array('--check', $argv, true);
$file = $root . '/paths_v63_pre_data.php';

try {
    $outputs = v63_pre_all();
    $rendered = v63_pre_render($outputs);
    $current = is_file($file) ? (string)file_get_contents($file) : '';
    $fresh = $current === $rendered;
    if ($apply && !$fresh) {
        $tmp = $file . '.tmp';
        if (file_put_contents($tmp, $rendered, LOCK_EX) === false || !rename($tmp, $file)) throw new RuntimeException('Soubor se nepodařilo zapsat.');
        $fresh = true;
        echo "WRITE paths_v63_pre_data.php\n";
    }
    if (!$fresh && ($check || !$apply)) {
        echo 'V63_PRE_BUILD_STALE cases=' . count($outputs) . " (spusť s --apply)\n";
        exit($check ? 1 : 0);
    }
    echo 'V63_PRE_BUILD_OK cases=' . count($outputs) . "\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'V63_PRE_BUILD_FAIL ' . get_class($e) . ': ' . $e->getMessage() . "\n");
    exit(1);
}
