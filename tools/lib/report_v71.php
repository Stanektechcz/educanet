<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v71 · společné pomocníky produkčních reportů (tools/v71_content_report.php, tools/v71_data_report.php).
 * Reporty jsou CLI nástroje, ne audity: jen čtení, explicitní --out mimo projekt (a mimo úložiště), výstup jen souhrnné
 * počty a prefixy hashů – nikdy jména. Nikdy nejsou součástí tools/run_audits.php (nemají v názvu „audit“).
 */

/** Hodnota přepínače --name=hodnota, jinak null. */
function rp71_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return null;
}

/** Absolutní normalizovaná cesta (i pro neexistující cíl: relativní vůči cwd, bez „.“ a „..“). */
function rp71_norm_path(string $path): string
{
    $real = realpath($path);
    if ($real !== false) return rtrim(str_replace('\\', '/', $real), '/');
    $path = str_replace('\\', '/', $path);
    if (!str_starts_with($path, '/') && preg_match('~^[A-Za-z]:/~', $path) !== 1) $path = str_replace('\\', '/', (string)getcwd()) . '/' . $path;
    $prefix = preg_match('~^([A-Za-z]:)/~', $path, $m) === 1 ? $m[1] : '';
    $parts = [];
    foreach (explode('/', substr($path, strlen($prefix))) as $seg) {
        if ($seg === '' || $seg === '.') continue;
        if ($seg === '..') { array_pop($parts); continue; }
        $parts[] = $seg;
    }
    $norm = $prefix . '/' . implode('/', $parts);
    // Existující předek kvůli symlinkům a zkratkám (např. 8.3 názvy na Windows).
    $parent = dirname($norm);
    while ($parent !== '' && $parent !== dirname($parent) && realpath($parent) === false) $parent = dirname($parent);
    $realParent = realpath($parent);
    return $realParent !== false ? rtrim(str_replace('\\', '/', $realParent), '/') . substr($norm, strlen($parent)) : $norm;
}

/** Je $path uvnitř $dir (nebo jím)? Porovnání bez ohledu na velikost písmen (Windows). */
function rp71_inside(string $path, string $dir): bool
{
    $p = strtolower(rp71_norm_path($path)) . '/';
    $d = strtolower(rp71_norm_path($dir)) . '/';
    return str_starts_with($p, $d);
}

function rp71_fail(string $message, int $code = 2): never
{
    fwrite(STDERR, 'CHYBA: ' . $message . PHP_EOL);
    exit($code);
}

/** Ověří a případně založí výstupní adresář (0700); nesmí ležet v projektu ani v $forbidden. */
function rp71_out_dir(?string $out, string $projectRoot, array $forbidden = []): string
{
    if ($out === null || trim($out) === '') rp71_fail('Chybí --out=<adresář mimo projekt>.');
    // Kontrola umístění PŘED založením adresáře – report nesmí nic vytvořit v projektu ani v úložišti.
    if (rp71_inside($out, $projectRoot)) rp71_fail('--out musí ležet mimo projekt (report se nesmí dostat do vydání ani na web).');
    foreach ($forbidden as $dir) if ($dir !== '' && rp71_inside($out, $dir)) rp71_fail('--out nesmí ležet v úložišti dat.');
    if (!is_dir($out) && !@mkdir($out, 0700, true) && !is_dir($out)) rp71_fail('Výstupní adresář nelze vytvořit.');
    if (!is_writable($out)) rp71_fail('Do výstupního adresáře nelze zapisovat.');
    return rp71_norm_path($out);
}

/** SHA-256 celého stromu (cesta + obsah každého souboru) – kontrola, že report nic nezměnil. */
function rp71_tree_hash(string $dir): string
{
    $rows = [];
    if (is_dir($dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            $rel = substr(str_replace('\\', '/', $file->getPathname()), strlen(rtrim(str_replace('\\', '/', $dir), '/')) + 1);
            $rows[] = $rel . '|' . (string)hash_file('sha256', $file->getPathname());
        }
    }
    sort($rows, SORT_STRING);
    return hash('sha256', implode("\n", $rows));
}

/** Dočasný adresář reportu (0700), smaže se při ukončení. */
function rp71_temp_dir(string $tag): string
{
    $dir = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet-report-' . preg_replace('/[^a-z0-9_-]/i', '', $tag) . '-' . bin2hex(random_bytes(6));
    if (!mkdir($dir, 0700, true) && !is_dir($dir)) rp71_fail('Nelze vytvořit dočasný adresář.');
    register_shutdown_function(static function () use ($dir): void { rp71_remove_dir($dir); });
    return $dir;
}

function rp71_remove_dir(string $dir): void
{
    if (!is_dir($dir) || !str_contains($dir, 'educanet-report-')) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $item) { $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname()); }
    @rmdir($dir);
}

/** Zapíše <prefix>-<datum>.json a .txt do výstupního adresáře; vrátí cesty. @return array{json:string,txt:string} */
function rp71_write(string $outDir, string $prefix, array $data, array $lines): array
{
    $stamp = date('Ymd-His');
    $json = $outDir . '/' . $prefix . '-' . $stamp . '.json';
    $txt = $outDir . '/' . $prefix . '-' . $stamp . '.txt';
    if (file_put_contents($json, (string)json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false) rp71_fail('Report nelze zapsat.');
    if (file_put_contents($txt, implode(PHP_EOL, $lines) . PHP_EOL) === false) rp71_fail('Report nelze zapsat.');
    @chmod($json, 0600);
    @chmod($txt, 0600);
    return ['json' => $json, 'txt' => $txt];
}

/** Prefix hashe (8 znaků) – jediná identifikace záznamu, kterou report smí vypsat. */
function rp71_hash_prefix(string $value): string
{
    return substr(hash('sha256', $value), 0, 8);
}

function rp71_pct(int $part, int $total): string
{
    return $total > 0 ? number_format($part / $total * 100, 1, ',', '') . ' %' : '0 %';
}
