<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab offline – export příručky pro pískoviště bez připojení.
 *
 * Načte jádro labu (linux_v57_lab.php → na konci linux_v58_ext.php → všechna rozšíření
 * linux_v58_cmd_*.php / linux_v58_levels_*.php), zavolá lab58_manual() a uloží celou
 * příručku (kategorie, koncepty, příkazy) jako statický, deterministicky seřazený JSON
 * do assets/lab-manual-v58.json. Žádná osobní data – jen popis příkazů.
 *
 * Nástroj jen čte registr v paměti (nic nezapisuje do storage/); protistorage se přesto
 * dočasně přesměruje, kdyby ho nějaké rozšíření omylem použilo při načítání.
 *
 * Spuštění:  C:/php/php.exe tools/v58_export_manual.php
 * Integrátor tento nástroj spustí znovu po dokončení agentů SIM-A/SIM-B/LEVELS, aby
 * příručka obsahovala i jejich nové příkazy (tools/v58_offline_audit.php to ověří).
 */

$ROOT = dirname(__DIR__);
$GLOBALS['lab57_storage_override'] = sys_get_temp_dir() . '/v58_export_manual_' . bin2hex(random_bytes(6));

require_once $ROOT . '/linux_v57_lab.php';

$manual = function_exists('lab58_manual') ? lab58_manual() : v57_manual_base();
if (!is_array($manual)) {
    fwrite(STDERR, "FAIL lab58_manual() nevrátil pole\n");
    exit(1);
}

$errors = function_exists('lab58_registry_errors') ? lab58_registry_errors() : [];
foreach ($errors as $error) {
    fwrite(STDERR, 'WARN registry: ' . $error . "\n");
}

$sorted = v58em_sort_manual($manual);

$outPath = $ROOT . '/assets/lab-manual-v58.json';
$json = json_encode($sorted, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
if (file_put_contents($outPath, $json . "\n") === false) {
    fwrite(STDERR, "FAIL nelze zapsat $outPath\n");
    exit(1);
}

$counts = [
    'commands' => count($sorted['commands']),
    'categories' => count($sorted['categories']),
    'concepts' => count($sorted['concepts']),
];
echo 'V58_EXPORT_MANUAL_OK commands=' . $counts['commands'] . ' categories=' . $counts['categories']
    . ' concepts=' . $counts['concepts'] . ' warnings=' . count($errors) . "\n";

/**
 * Seřadí manuál deterministicky (klíče podle abecedy) a odstraní interní/needeterministické
 * příznaky (extend), aby export nezávisel na pořadí registrace rozšíření.
 */
function v58em_sort_manual(array $manual): array
{
    return [
        'version' => 58,
        'source' => 'lab58_manual',
        'categories' => v58em_ksort_map((array)($manual['categories'] ?? [])),
        'concepts' => v58em_ksort_map((array)($manual['concepts'] ?? [])),
        'commands' => array_map('v58em_clean_command', v58em_ksort_map((array)($manual['commands'] ?? []))),
    ];
}

/** @param array<string,mixed> $map */
function v58em_ksort_map(array $map): array
{
    ksort($map, SORT_STRING);
    return $map;
}

/** @param array<string,mixed> $entry */
function v58em_clean_command(array $entry): array
{
    unset($entry['extend']);
    ksort($entry, SORT_STRING);
    return $entry;
}
