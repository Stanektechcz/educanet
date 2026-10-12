<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v75 migrace 0005: historie verzí knihovny materiálů do vlastního souboru (výkon Dnešní hodiny).
 *   storage/materials_v74.json.php {"items","history"} → materials_v74.json.php {"items"} + materials_v74_history.json.php {"history"}.
 * Obsah verzí se nemění (jen přesun); opakované spuštění nic nezmění (idempotentní). Bez knihovny = nic k přesunu.
 * Náhled: php tools/migrate.php --dry-run --only=0005_materials_history_v75. Provedení: --apply --backup-now.
 * Rollback: php tools/migrate.php --rollback=0005_materials_history_v75 [--apply] – vrátí historii do hlavního souboru (stav v74).
 */

if (!function_exists('mat74_history_path')) require_once dirname(__DIR__) . '/materials_v74.php';

return [
    'id' => '0005_materials_history_v75',
    'description' => 'Knihovna materiálů v75: historie verzí do vlastního souboru materials_v74_history.json.php (jen přesun, idempotentní).',
    'files' => ['materials_v74.json.php', 'materials_v74_history.json.php'],
    'up' => static function (bool $dryRun): array {
        $legacy = array_filter((array)(mat74_data()['history'] ?? []), 'is_array');
        $details = ['materiály s historií v hlavním souboru: ' . count($legacy) . ($dryRun ? ' k přesunu' : ' přesunuto')];
        if ($legacy === [] || $dryRun) return ['changed' => $dryRun ? 0 : count($legacy), 'details' => $details];
        storage_update_many([mat74_path(), mat74_history_path()], static function (array $all): array {
            $main = $all[mat74_path()];
            $history = mat74_history_all($main, $all[mat74_history_path()]);
            unset($main['history']);
            return [mat74_path() => $main, mat74_history_path() => ['v' => 1, 'history' => $history]];
        });
        return ['changed' => count($legacy), 'details' => $details];
    },
    'down' => static function (bool $dryRun = true): array {
        $separate = array_filter((array)(storage_read(mat74_history_path(), false)['history'] ?? []), 'is_array');
        if (!$dryRun && $separate !== []) {
            storage_update_many([mat74_path(), mat74_history_path()], static function (array $all): array {
                $main = $all[mat74_path()];
                $main['history'] = mat74_history_all($main, $all[mat74_history_path()]);
                return [mat74_path() => $main, mat74_history_path() => ['v' => 1, 'history' => []]];
            });
        }
        return ['removed' => 0, 'details' => ['historie vrácená do hlavního souboru: ' . count($separate) . ($dryRun ? ' (náhled)' : '')]];
    },
];
