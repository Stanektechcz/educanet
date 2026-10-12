<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v74 migrace 0004: naplní knihovnu materiálů (storage/materials_v74.json.php) z dosavadních zdrojů.
 *   - videa učitelů k lekcím v42 (storage/adaptive_lesson_resources.json.php) → materiál připojený k lekci (role hlavní),
 *   - zdroje learning_resources.php (po třídách a tématech) → materiál s tématy, stav „aktivní“, neověřený.
 * Jen https (http: se přeskočí a vypíše v náhledu). Id je deterministické → opakované spuštění nic nezdvojí (idempotentní);
 * existující materiál (i upravený učitelem) se nikdy nepřepíše. Původní soubory zůstávají beze změny.
 * Náhled: php tools/migrate.php --dry-run --only=0004_materials_v74 (jen počty). Provedení: --apply --backup-now.
 * Rollback: php tools/migrate.php --rollback=0004_materials_v74 [--apply] – odebere jen importované materiály, které od importu
 * nikdo nezměnil (verze 1); materiály přidané nebo upravené učiteli zůstanou. Prázdná knihovna se smaže.
 */

if (!function_exists('mat74_import_candidates')) require_once dirname(__DIR__) . '/materials_v74.php';

$mat74Sources = static function (): array {
    $lr = is_file(dirname(__DIR__) . '/learning_resources.php') ? require dirname(__DIR__) . '/learning_resources.php' : [];
    $v42Path = function_exists('adaptive_store_path') ? adaptive_store_path('lesson_resources') : STORAGE_DIR . '/adaptive_lesson_resources.json.php';
    $v42 = is_file($v42Path) ? storage_read($v42Path, false) : [];
    return mat74_import_candidates(is_array($lr) ? $lr : [], is_array($v42) ? $v42 : []);
};

return [
    'id' => '0004_materials_v74',
    'description' => 'Knihovna materiálů v74: import videí učitelů v42 a zdrojů learning_resources.php (jen https, idempotentní).',
    'files' => ['materials_v74.json.php', 'materials_v74_history.json.php'],
    'up' => static function (bool $dryRun) use ($mat74Sources): array {
        $cand = $mat74Sources();
        $existing = mat74_items(null);
        $byOrigin = ['import_v42' => 0, 'import_lr' => 0];
        $new = [];
        foreach ($cand['rows'] as $r) {
            if (isset($existing[$r['id']])) continue;
            $new[] = $r;
            $byOrigin[$r['origin']] = ($byOrigin[$r['origin']] ?? 0) + 1;
        }
        $details = ['videa učitelů v42: ' . $byOrigin['import_v42'] . ' ' . ($dryRun ? 'k importu' : 'importováno'),
            'zdroje learning_resources: ' . $byOrigin['import_lr'] . ' ' . ($dryRun ? 'k importu' : 'importováno'),
            'už v knihovně (beze změny): ' . (count($cand['rows']) - count($new)), 'přeskočeno (není https / neplatné): ' . count($cand['skipped'])];
        foreach (array_slice($cand['skipped'], 0, 10) as $s) $details[] = '  ' . $s;
        if (!$dryRun && $new !== []) mat74_import_apply($new, 'migrace 0004');
        return ['changed' => count($new), 'details' => $details];
    },
    'down' => static function (bool $dryRun = true): array {
        $removed = 0;
        $kept = 0;
        $data = mat74_data();
        foreach (mat74_items(null, $data) as $id => $item) {
            $imported = str_starts_with((string)($item['origin'] ?? ''), 'import_');
            if ($imported && (int)($item['version'] ?? 0) === 1) $removed++; else $kept++;
        }
        if (!$dryRun && $removed > 0) {
            // v75: historie je ve vlastním souboru (materials_v74_history.json.php) – odebírá se z obou.
            storage_update_many([mat74_path(), mat74_history_path()], static function (array $all): array {
                $d = $all[mat74_path()];
                $h = $all[mat74_history_path()];
                foreach ((array)($d['items'] ?? []) as $id => $item) {
                    if (is_array($item) && str_starts_with((string)($item['origin'] ?? ''), 'import_') && (int)($item['version'] ?? 0) === 1) unset($d['items'][$id], $d['history'][$id], $h['history'][$id]);
                }
                return [mat74_path() => $d, mat74_history_path() => $h];
            });
            if (mat74_items(null) === []) {
                foreach ([mat74_path(), mat74_history_path()] as $p) {
                    if (is_file($p)) @unlink($p);
                    if (defined('STORAGE_LOCK_SUFFIX') && is_file($p . STORAGE_LOCK_SUFFIX)) @unlink($p . STORAGE_LOCK_SUFFIX);
                }
            }
        }
        return ['removed' => $removed, 'details' => ['importované beze změny: ' . $removed . ($dryRun ? ' k odebrání' : ' odebráno'), 'ponechané (učitel přidal nebo upravil): ' . $kept]];
    },
];
