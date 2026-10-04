<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/restore_storage.php
 *
 * Obnoví storage/ ze zálohy vytvořené backup_storage.php.
 *
 * Použití:
 *   php tools/restore_storage.php --from=<adresář zálohy> [--dest=<adresář>] [--apply] [--prune] [--keep=N]
 *   php tools/restore_storage.php --from=<storage-….edubak> --decrypt [--tmp=<adresář>] [--dest=…] [--apply] …
 *
 * Výchozí je dry-run: vypíše přidané/změněné/chybějící soubory a nic nemění.
 * --apply: nejdřív vytvoří bezpečnostní zálohu aktuálního stavu (přes backup_storage_run,
 *          se stejnou rotací --keep), ověří manifest zálohy (sha256) a pak obnoví soubory
 *          atomicky (zápis do dočasného souboru + rename). Soubory, které v záloze nejsou,
 *          se nemažou, pokud není zadáno --prune.
 *
 * --decrypt (v61): --from je šifrovaný archiv .edubak. Rozšifruje se (klíč = tajemství backup_key) do dočasného
 *          adresáře 0700 (výchozí systémový temp, --tmp), ověří se otisk .sha256 a všechny soubory proti manifestu,
 *          pak běží stejná obnova jako výše (včetně bezpečnostní zálohy). Dočasný adresář se vždy smaže.
 *          Špatný klíč / poškozený nebo zkrácený archiv = chyba, do cíle se nezapíše nic.
 *
 * Exit 0 = úspěch/žádné rozdíly, exit 1 = rozdíly nalezeny (dry-run) nebo chyba (--apply).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/backup_storage.php';

function restore_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) {
            return substr($a, strlen($name) + 3);
        }
    }
    return $default;
}

function restore_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

function restore_load_manifest(string $backupDir): array
{
    $path = $backupDir . '/manifest.json';
    if (!is_file($path)) {
        throw new RuntimeException("Manifest nenalezen: $path");
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Nelze číst manifest $path.");
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['files']) || !is_array($data['files'])) {
        throw new RuntimeException('Manifest má neplatný formát.');
    }
    return $data;
}

/**
 * v58 · OPS58-11: cesta z manifestu je nedůvěryhodná (záloha mohla být podvržena).
 * Povolené jsou jen relativní cesty ze znaků [A-Za-z0-9_./-], bez segmentů „..“/„.“ a bez
 * prázdných segmentů. Vrací cestu beze změny, jinak výjimka.
 */
function restore_validate_rel(string $rel): string
{
    if ($rel === '' || strlen($rel) > 400 || !preg_match('#^[A-Za-z0-9_./-]+$#', $rel)) {
        throw new RuntimeException('Neplatná cesta v manifestu zálohy.');
    }
    foreach (explode('/', $rel) as $segment) {
        if ($segment === '' || $segment === '.' || $segment === '..') {
            throw new RuntimeException('Neplatná cesta v manifestu zálohy (segment).');
        }
    }
    return $rel;
}

/** Ověří, že existující adresář $dir leží uvnitř kořene $root (realpath – platí i přes symlinky). */
function restore_assert_inside(string $root, string $dir): void
{
    $realRoot = realpath($root);
    $realDir = realpath($dir);
    if ($realRoot === false || $realDir === false) {
        throw new RuntimeException('Cílovou cestu obnovy nelze ověřit.');
    }
    $realRoot = rtrim(str_replace('\\', '/', $realRoot), '/');
    $realDir = rtrim(str_replace('\\', '/', $realDir), '/');
    if ($realDir !== $realRoot && !str_starts_with($realDir . '/', $realRoot . '/')) {
        throw new RuntimeException('Cesta z manifestu míří mimo cílovou storage.');
    }
}

/** Záznamy manifestu k obnově (bez uploads/); každou cestu nejdřív ověří. @return list<array{path:string,sha256:string}> */
function restore_manifest_entries(array $manifest): array
{
    $out = [];
    foreach ($manifest['files'] as $entry) {
        $rel = is_array($entry) ? (string)($entry['path'] ?? '') : '';
        if ($rel !== '' && str_starts_with($rel, 'uploads/')) {
            continue;
        }
        $out[] = ['path' => restore_validate_rel($rel), 'sha256' => (string)($entry['sha256'] ?? '')];
    }
    return $out;
}

/**
 * Porovná manifest zálohy s aktuálním stavem $dest. Vrací pole se seznamy
 * added/changed/missing (cesty relativní k "storage/" v záloze, tj. bez prefixu).
 */
function restore_diff(array $manifest, string $dest): array
{
    $result = ['added' => [], 'changed' => [], 'missing' => []];
    $seen = [];
    foreach (restore_manifest_entries($manifest) as $entry) {
        $rel = $entry['path'];
        $seen[$rel] = true;
        $live = $dest . '/' . $rel;
        if (!is_file($live)) {
            $result['added'][] = $rel;
            continue;
        }
        $hash = @hash_file('sha256', $live);
        if ($hash !== (string)($entry['sha256'] ?? '')) {
            $result['changed'][] = $rel;
        }
    }
    foreach (bkp_rcopy_list($dest) as $rel) {
        if (!isset($seen[$rel]) && !bkp_is_excluded($rel)) {
            $result['missing'][] = $rel; // v aktuálním stavu je navíc oproti záloze
        }
    }
    return $result;
}

function restore_apply(array $manifest, string $backupDir, string $dest, bool $prune): array
{
    $applied = ['restored' => [], 'pruned' => []];
    // Nejdřív ověř všechny cesty, ať podvržený manifest nezačne obnovu napůl.
    $entries = restore_manifest_entries($manifest);
    if (!is_dir($dest)) {
        throw new RuntimeException("Cílová storage $dest neexistuje.");
    }
    foreach ($entries as $entry) {
        $rel = $entry['path'];
        $src = $backupDir . '/storage/' . $rel;
        if (!is_file($src)) {
            throw new RuntimeException("Soubor zálohy chybí: $rel");
        }
        $hash = hash_file('sha256', $src);
        if ($hash !== (string)($entry['sha256'] ?? '')) {
            throw new RuntimeException("Integrita zálohy porušena u $rel (hash nesouhlasí).");
        }
        $live = $dest . '/' . $rel;
        $dir = dirname($live);
        if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new RuntimeException("Nelze vytvořit $dir.");
        }
        restore_assert_inside($dest, $dir);
        restore_assert_inside($backupDir . '/storage', dirname($src));
        $tmp = $live . '.restore-tmp-' . bin2hex(random_bytes(4));
        if (!copy($src, $tmp)) {
            throw new RuntimeException("Nelze zkopírovat $rel do dočasného souboru.");
        }
        if (!rename($tmp, $live)) {
            @unlink($tmp);
            throw new RuntimeException("Nelze přejmenovat obnovený soubor $rel.");
        }
        if (function_exists('php_json_cache_forget')) {
            php_json_cache_forget($live);
        }
        $applied['restored'][] = $rel;
    }
    if ($prune) {
        $seen = array_column($entries, 'path');
        foreach (bkp_rcopy_list($dest) as $rel) {
            // Soubory vynechané ze zálohy (klíč OTP) se prořezáním nesmí smazat.
            if (!in_array($rel, $seen, true) && !bkp_is_excluded($rel)) {
                @unlink($dest . '/' . $rel);
                $applied['pruned'][] = $rel;
            }
        }
    }
    return $applied;
}

/**
 * v61 · Rozšifruje archiv .edubak do nového dočasného adresáře (0700) a ověří ho proti manifestu.
 * Vrací cestu k adresáři ve tvaru běžné zálohy (manifest.json + storage/ + uploads/); volající ho po použití smaže.
 */
function restore_decrypt_archive(string $archive, string $tmpRoot, string $key): string
{
    require_once __DIR__ . '/lib/backup_crypto_v61.php';
    if (!is_file($archive)) {
        throw new RuntimeException('Archiv nenalezen: ' . basename($archive));
    }
    $sidecar = $archive . '.sha256';
    if (is_file($sidecar)) {
        $expected = strtok((string)file_get_contents($sidecar), " \t\r\n");
        $actual = hash_file('sha256', $archive);
        if (!is_string($expected) || $actual === false || !hash_equals($expected, $actual)) {
            throw new RuntimeException('Otisk SHA-256 šifrovaného archivu nesouhlasí s .sha256 – archiv je poškozený.');
        }
    }
    $dir = rtrim(str_replace(chr(92), '/', $tmpRoot), '/') . '/educanet-restore-' . bin2hex(random_bytes(6));
    if (!mkdir($dir, 0700, true)) {
        throw new RuntimeException('Nelze vytvořit dočasný adresář pro rozšifrování.');
    }
    try {
        bkc61_decrypt_to_dir($archive, $dir, $key);
        bkc61_verify_manifest($dir);
    } catch (Throwable $e) {
        bkp_rrmdir($dir);
        throw $e;
    }
    return $dir;
}
if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $from = restore_arg($argv, 'from');
    $dest = restore_arg($argv, 'dest', STORAGE_DIR);
    $apply = restore_flag($argv, 'apply');
    $prune = restore_flag($argv, 'prune');
    $keep = (int)restore_arg($argv, 'keep', '14');

    $decrypt = restore_flag($argv, 'decrypt');
    if (!is_string($from) || $from === '' || !($decrypt ? is_file($from) : is_dir($from))) {
        fwrite(STDERR, "RESTORE_STORAGE_FAIL --from je povinné a musí existovat" . ($decrypt ? " (soubor .edubak).\n" : ".\n"));
        exit(1);
    }

    try {
        if ($decrypt) {
            require_once __DIR__ . '/lib/backup_crypto_v61.php';
            $tmpDir = restore_decrypt_archive($from, (string)restore_arg($argv, 'tmp', sys_get_temp_dir()), bkc61_key_from_secrets());
            register_shutdown_function(static function () use ($tmpDir): void { bkp_rrmdir($tmpDir); }); // i při exit()
            $from = $tmpDir;
            echo "DECRYPT_OK\n";
        }
        $manifest = restore_load_manifest($from);
        $diff = restore_diff($manifest, (string)$dest);
        foreach ($diff['added'] as $rel) {
            echo "ADD $rel\n";
        }
        foreach ($diff['changed'] as $rel) {
            echo "CHANGE $rel\n";
        }
        foreach ($diff['missing'] as $rel) {
            echo ($prune ? 'PRUNE ' : 'EXTRA ') . "$rel\n";
        }
        $hasDiff = $diff['added'] || $diff['changed'] || ($prune && $diff['missing']);

        if (!$apply) {
            echo 'RESTORE_STORAGE_DRYRUN added=' . count($diff['added']) . ' changed=' . count($diff['changed']) . ' extra=' . count($diff['missing']) . "\n";
            exit($hasDiff ? 1 : 0);
        }

        $safetyDest = (string)(getenv('EDUCANET_BACKUP_DIR') ?: dirname(dirname(__DIR__)) . '/educanet-backups');
        $safety = backup_storage_run((string)$dest, $safetyDest, $keep, false, null, true);
        echo "SAFETY_BACKUP $safety\n";

        $applied = restore_apply($manifest, $from, (string)$dest, $prune);
        echo 'RESTORE_STORAGE_OK restored=' . count($applied['restored']) . ' pruned=' . count($applied['pruned']) . "\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'RESTORE_STORAGE_FAIL ' . $e->getMessage() . "\n");
        exit(1);
    }
}
