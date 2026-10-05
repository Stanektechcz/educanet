<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 migrace 0003: vytvoří prázdné sidecary projektů (cyklus, nastavení, rubriky, peer review, milníky).
 * Žádný starý soubor (project_grades, project_workspace_*, projects_v60*) se nemění ani nepřepisuje –
 * migrace pouze vytvoří chybějící soubory, takže je idempotentní. Existující sidecar (i neprázdný) zůstane.
 * Rollback: smazat pět sidecarů z 'files' (nic jiného na nich nezávisí) – viz INSTALL.md, sekce v65;
 * tools/migrate.php 'down' neumí, proto je 'down' jen pomocná funkce (používá ji audit v65).
 */

/** @return array<string,array<string,mixed>> název souboru → výchozí obsah */
$proj65Seeds = static fn(): array => [
    'projects_v65_cycle.json.php' => [],
    'projects_v65_meta.json.php' => ['default_mode' => 'small', 'projects' => []],
    'projects_v65_rubrics.json.php' => [],
    'projects_v65_peer.json.php' => [],
    'projects_v65_milestones.json.php' => [],
];

return [
    'id' => '0003_projects_v65',
    'description' => 'Vytvořit sidecary projektů v65 (cyklus, rubriky, peer review, milníky); staré soubory beze změny.',
    'files' => array_keys($proj65Seeds()),
    'up' => static function (bool $dryRun) use ($proj65Seeds): array {
        $details = [];
        $created = 0;
        foreach ($proj65Seeds() as $name => $seed) {
            $path = STORAGE_DIR . '/' . $name;
            if (is_file($path)) { $details[] = $name . ': existuje, beze změny'; continue; }
            $details[] = $name . ': ' . ($dryRun ? 'bude vytvořen' : 'vytvořen');
            $created++;
            if ($dryRun) continue;
            storage_update($path, static fn(array $d): array => $d === [] ? $seed : $d);
        }
        return ['changed' => $created, 'details' => $details];
    },
    'down' => static function () use ($proj65Seeds): array {
        $removed = 0;
        foreach (array_keys($proj65Seeds()) as $name) {
            $path = STORAGE_DIR . '/' . $name;
            if (is_file($path) && @unlink($path)) $removed++;
            if (is_file($path . STORAGE_LOCK_SUFFIX)) @unlink($path . STORAGE_LOCK_SUFFIX);
        }
        return ['removed' => $removed];
    },
];
