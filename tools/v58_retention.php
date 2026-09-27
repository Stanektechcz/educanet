<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/v58_retention.php (OPS-03)
 *
 * CLI pro retenční politiku storage/ (ops58_retention_policy / ops58_apply_retention
 * v ops_v58.php). Výchozí je dry-run – jen vypíše, co by se stalo.
 *
 * Použití:
 *   php tools/v58_retention.php            # náhled
 *   php tools/v58_retention.php --apply    # skutečně archivuje/pročistí
 *   php tools/v58_retention.php --json
 *
 * "archive" akce přesouvají soubor do storage/archive/<školní rok>/, "trim" akce mění jen
 * obsah JSON mapy přes storage_update() (staré položky), soubor samotný zůstává na místě.
 * Výjimka (DAT58-03): logy příkazů Linux Labu starší 30 dní se MAŽOU ("delete") – s --apply
 * se navíc volá lab58_log_purge(), který uklidí i meta.json.php a prázdné složky žáků.
 * Spouštět denně z cronu (viz INSTALL.md, sekce v58).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/ops_v58.php';
require_once dirname(__DIR__) . '/linux_v57_lab.php';
require_once dirname(__DIR__) . '/lab_v58_log.php';

function v58r_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $apply = v58r_flag($argv, 'apply');
    $asJson = v58r_flag($argv, 'json');

    try {
        $result = ops58_apply_retention(!$apply);
        $result['lab_log_purged'] = $apply ? lab58_log_purge() : 0;
    } catch (Throwable $e) {
        fwrite(STDERR, 'V58_RETENTION_FAIL ' . $e->getMessage() . "\n");
        exit(1);
    }

    if ($asJson) {
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    } else {
        foreach ($result['actions'] as $a) {
            printf("%s %s — %s\n", strtoupper((string)$a['action']), (string)$a['path'], (string)$a['detail']);
        }
        echo ($apply ? 'APPLY' : 'DRYRUN') . ' school_year=' . $result['school_year'] . ' actions=' . count($result['actions']) . ' lab_log_purged=' . (int)$result['lab_log_purged'] . "\n";
    }
    exit(0);
}
