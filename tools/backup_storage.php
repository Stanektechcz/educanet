<?php

declare(strict_types=1);
// Přímé volání z webu je zakázané; jako knihovnu ho smí načíst ops_v58.php (záložka Provoz) – hlavní blok níže běží jen z CLI.
if (PHP_SAPI !== 'cli' && basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET · tools/backup_storage.php (OPS-04)
 *
 * Zálohuje storage/ (a volitelně uploads/) mimo web root.
 *
 * Použití:
 *   php tools/backup_storage.php [--src=<adresář>] [--dest=<adresář>] [--keep=N] [--with-uploads] [--quiet]
 *
 * --src   Zdroj (výchozí: STORAGE_DIR z bootstrap.php, tj. EDUCANET_STORAGE_DIR nebo storage/).
 * --dest  Cíl mimo web root (výchozí: EDUCANET_BACKUP_DIR nebo "<rodič kořene projektu>/educanet-backups").
 * --keep  Kolik posledních záloh v cíli ponechat, starší se smažou (výchozí 14).
 *
 * Záloha = adresář storage-YYYYmmdd-HHMMSS/ se zkopírovanými soubory + manifest.json
 * (relativní cesta, velikost, sha256, čas). Každý zdrojový soubor se čte pod flock(LOCK_SH),
 * aby se nezálohoval rozepsaný zápis. Po zkopírování se hash ověří proti zdroji.
 *
 * Exit 0 = úspěch, exit 1 = chyba (nic nenechá napůl zapsané: při chybě smaže rozpracovaný
 * cílový adresář zálohy).
 */

require_once dirname(__DIR__) . '/bootstrap.php';

function bkp_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) {
            return substr($a, strlen($name) + 3);
        }
    }
    return $default;
}

function bkp_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

/**
 * v58 · SEC58-10: soubory, které se NEZÁLOHUJÍ (relativně ke storage/). Šifrovací klíč
 * jednorázových hesel nesmí ležet ve stejné záloze jako šifrované kopie hesel – kdo by
 * získal zálohu, přečetl by i kartičky. Klíč patří mimo web root (secret otp_card_key, viz INSTALL.md);
 * bez něj se po obnově jen vytisknou nové kartičky (vydání nových jednorázových hesel).
 */
function bkp_excluded_paths(): array
{
    return ['accounts_v58_key.json.php'];
}

function bkp_is_excluded(string $rel): bool
{
    return in_array(str_replace('\\', '/', $rel), bkp_excluded_paths(), true);
}

function bkp_rcopy_list(string $dir, string $base = ''): array
{
    $out = [];
    if (!is_dir($dir)) {
        return $out;
    }
    $items = scandir($dir);
    if ($items === false) {
        return $out;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        // v58: pomocné soubory jádra úložiště (zámky, rozepsané zápisy) se nezálohují.
        if (str_ends_with($item, '.lock') || str_ends_with($item, '.tmp')) {
            continue;
        }
        $full = $dir . '/' . $item;
        $rel = $base === '' ? $item : $base . '/' . $item;
        if (is_dir($full)) {
            $out = array_merge($out, bkp_rcopy_list($full, $rel));
        } else {
            $out[] = $rel;
        }
    }
    return $out;
}

function bkp_copy_locked(string $src, string $dst): string
{
    $dir = dirname($dst);
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
        throw new RuntimeException("Nelze vytvořit adresář $dir.");
    }
    $fp = fopen($src, 'rb');
    if ($fp === false) {
        throw new RuntimeException("Nelze otevřít $src ke čtení.");
    }
    $hash = hash_init('sha256');
    try {
        if (!flock($fp, LOCK_SH)) {
            throw new RuntimeException("Nelze uzamknout $src ke čtení.");
        }
        $out = fopen($dst, 'wb');
        if ($out === false) {
            throw new RuntimeException("Nelze zapsat $dst.");
        }
        try {
            while (!feof($fp)) {
                $chunk = fread($fp, 1048576);
                if ($chunk === false) {
                    throw new RuntimeException("Chyba čtení $src.");
                }
                hash_update($hash, $chunk);
                if (fwrite($out, $chunk) === false) {
                    throw new RuntimeException("Chyba zápisu $dst.");
                }
            }
        } finally {
            fclose($out);
        }
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }
    return hash_final($hash);
}

function bkp_sha256(string $path): string
{
    $h = hash_file('sha256', $path);
    if ($h === false) {
        throw new RuntimeException("Nelze spočítat hash $path.");
    }
    return $h;
}

function bkp_rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $full = $dir . '/' . $item;
        is_dir($full) ? bkp_rrmdir($full) : @unlink($full);
    }
    @rmdir($dir);
}

/**
 * Provede plnou zálohu a vrátí cestu k vytvořenému adresáři zálohy. Používá i restore_storage.php
 * pro bezpečnostní zálohu před obnovou.
 */
function backup_storage_run(string $src, string $destRoot, int $keep, bool $withUploads, ?string $uploadsSrc = null, bool $quiet = false): string
{
    if (!is_dir($src)) {
        throw new RuntimeException("Zdroj $src neexistuje.");
    }
    if (!is_dir($destRoot) && !mkdir($destRoot, 0770, true) && !is_dir($destRoot)) {
        throw new RuntimeException("Nelze vytvořit cílový adresář $destRoot.");
    }
    $stamp = date('Ymd-His');
    $target = rtrim($destRoot, '/\\') . '/storage-' . $stamp;
    if (is_dir($target)) {
        $target .= '-' . substr((string)microtime(true), -4);
    }
    if (!mkdir($target, 0770, true)) {
        throw new RuntimeException("Nelze vytvořit $target.");
    }

    $manifest = ['created_at' => date(DATE_ATOM), 'source' => $src, 'files' => []];
    try {
        $files = array_values(array_filter(bkp_rcopy_list($src), static fn(string $rel): bool => !bkp_is_excluded($rel)));
        foreach ($files as $rel) {
            $from = $src . '/' . $rel;
            $to = $target . '/storage/' . $rel;
            $hash = bkp_copy_locked($from, $to);
            $verify = bkp_sha256($to);
            if (!hash_equals($hash, $verify)) {
                throw new RuntimeException("Ověření hashe selhalo pro $rel.");
            }
            $manifest['files'][] = [
                'path' => $rel,
                'size' => filesize($to),
                'sha256' => $verify,
                'copied_at' => date(DATE_ATOM),
            ];
            if (!$quiet) {
                echo "PASS zkopírováno storage/$rel\n";
            }
        }

        if ($withUploads && $uploadsSrc !== null && is_dir($uploadsSrc)) {
            foreach (bkp_rcopy_list($uploadsSrc) as $rel) {
                $from = $uploadsSrc . '/' . $rel;
                $to = $target . '/uploads/' . $rel;
                $hash = bkp_copy_locked($from, $to);
                $verify = bkp_sha256($to);
                if (!hash_equals($hash, $verify)) {
                    throw new RuntimeException("Ověření hashe selhalo pro uploads/$rel.");
                }
                $manifest['files'][] = [
                    'path' => 'uploads/' . $rel,
                    'size' => filesize($to),
                    'sha256' => $verify,
                    'copied_at' => date(DATE_ATOM),
                ];
                if (!$quiet) {
                    echo "PASS zkopírováno uploads/$rel\n";
                }
            }
        }

        $json = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        file_put_contents($target . '/manifest.json', $json);
    } catch (Throwable $e) {
        bkp_rrmdir($target);
        throw $e;
    }

    backup_storage_rotate($destRoot, $keep);
    return $target;
}

function backup_storage_rotate(string $destRoot, int $keep): void
{
    if ($keep <= 0) {
        return;
    }
    $dirs = glob(rtrim($destRoot, '/\\') . '/storage-*', GLOB_ONLYDIR) ?: [];
    sort($dirs);
    $excess = count($dirs) - $keep;
    for ($i = 0; $i < $excess; $i++) {
        bkp_rrmdir($dirs[$i]);
    }
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $src = bkp_arg($argv, 'src', STORAGE_DIR);
    $destDefault = rtrim((string)getenv('EDUCANET_BACKUP_DIR'), '/\\');
    if ($destDefault === '') {
        $destDefault = dirname(dirname(__DIR__)) . '/educanet-backups';
    }
    $dest = bkp_arg($argv, 'dest', $destDefault);
    $keep = (int)bkp_arg($argv, 'keep', '14');
    $withUploads = bkp_flag($argv, 'with-uploads');
    $uploadsSrc = dirname((string)$src) . '/uploads';
    if (!is_dir($uploadsSrc)) {
        $uploadsSrc = dirname(__DIR__) . '/uploads';
    }

    try {
        $target = backup_storage_run((string)$src, (string)$dest, $keep, $withUploads, $uploadsSrc);
        echo "BACKUP_STORAGE_OK dest=" . $target . "\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'BACKUP_STORAGE_FAIL ' . $e->getMessage() . "\n");
        exit(1);
    }
}
