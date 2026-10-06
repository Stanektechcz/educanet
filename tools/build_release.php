<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v59 · tools/build_release.php
 *
 * Sestaví vydání aplikace do adresáře MIMO projekt (nikdy nezapisuje dovnitř
 * projektu). Nečte obsah úložiště žáků – jen jeho existenci a .htaccess.
 * Vylučuje vývojové, testovací a interní soubory podle BUILD_EXCLUDES.
 *
 * Použití:
 *   php tools/build_release.php --out=<adresář mimo projekt> [--zip] [--lint] [--quiet]
 *
 * Výstup: <out>/RELEASE_MANIFEST.json (verze, čas, sha256 každého souboru) a
 * volitelně <out>.zip. Konec: BUILD_RELEASE_OK files=N (exit 0), jinak
 * BUILD_RELEASE_FAIL (exit 1).
 */

$root = dirname(__DIR__);

function br_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    }
    return null;
}

function br_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

function br_norm(string $path): string
{
    return str_replace('\\', '/', $path);
}

function br_rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = @scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . '/' . $item;
        if (is_dir($full) && !is_link($full)) {
            br_rrmdir($full);
        } else {
            @unlink($full);
        }
    }
    @rmdir($dir);
}

/**
 * Vzory pro vyloučení, relativní k projektu (posuny lomítek normalizované na /).
 * Adresářové vzory testujeme jako prefix "name/", souborové/glob vzory přes fnmatch.
 */
const BUILD_EXCLUDE_DIRS = [
    'storage', 'uploads', // s výjimkou .htaccess (viz br_should_include)
    '.claude', 'tests', 'V1', 'lab-runtime', 'database',
    'retired', // v68: kopie vyřazených souborů (tools/v68_retire.php) patří do zálohy, ne do vydání
    'docs/archive', '.git',
];
// SEC59-01/d: V1/ (legacy aplikace, web-nastavitelné první heslo) se do vydání
// nedostane vůbec – ani chráněné .htaccess. Pokud škola ještě potřebuje import
// starších dat dotazníků (intake_v51_sync_v1()), zkopíruj JEN V1/data MIMO web
// root a nastav EDUCANET_V1_DATA_DIR (viz docs/NASAZENI_PRODUKCE.md §3 a
// intake_v51.php – proměnná je už v aplikaci podporovaná).
const BUILD_EXCLUDE_TOOLS_SUBDIR = 'tools/legacy';
const BUILD_EXCLUDE_FILES = [
    'private/educanet.secrets.php',
];
const BUILD_EXCLUDE_GLOBS = [
    '*.lock', '*.bak', '*.tmp', '*.log',
    '*.pem', '*.key', // SEC59-20: soukromé klíče/certifikáty nikdy do vydání.
    'CLAUDE.md', 'PROMPT_*.md', 'AUDIT_*.md',
    'RELEASE_*.md', 'RELEASE_GATE_*.md', 'UPGRADE_*.md', 'PATCH_MANIFEST_*.md',
    'BUILD_MANIFEST_*.md', 'CHANGELOG_*.md', 'ROADMAP_*.md',
    '*_retired*',
    '.DS_Store', 'Thumbs.db', 'desktop.ini',
];
// SEC59-20: jediný dotfile, který smí do vydání, je .htaccess (chrání interní
// adresáře). Jakýkoli jiný dotfile (např. zapomenutý .env, nebo dočasný testovací
// soubor typu ".v59_preflight_audit_secret_inside.tmp.php" – ten navíc nekončí na
// .tmp, takže by ho *.tmp glob výše nezachytil) shodí celý build, místo aby ho
// tiše vynechal nebo tiše zabalil do vydání.
const BUILD_ALLOWED_DOTFILES = ['.htaccess'];

/** SEC59-20: soubor vypadající jako secrets (obsahuje "secrets" v názvu, .php) mimo private/. */
function br_is_secret_like_outside_private(string $rel, string $base): bool
{
    if (str_starts_with($rel, 'private/')) return false;
    return str_ends_with(strtolower($base), '.php') && stripos($base, 'secrets') !== false;
}

/** @return array{0:bool,1:string} [zahrnout?, důvod odmítnutí pro neočekávaný dotfile (jinak '')] */
function br_should_include2(string $relPath): array
{
    $rel = br_norm($relPath);
    $base = basename($rel);

    // storage/ a uploads/ – jen .htaccess prochází, nic z obsahu.
    if (str_starts_with($rel, 'storage/') || $rel === 'storage') {
        return [$rel === 'storage/.htaccess', ''];
    }
    if (str_starts_with($rel, 'uploads/') || $rel === 'uploads') {
        return [$rel === 'uploads/.htaccess', ''];
    }

    foreach (BUILD_EXCLUDE_DIRS as $dir) {
        if ($rel === $dir || str_starts_with($rel, $dir . '/')) return [false, ''];
    }
    if ($rel === BUILD_EXCLUDE_TOOLS_SUBDIR || str_starts_with($rel, BUILD_EXCLUDE_TOOLS_SUBDIR . '/')) return [false, ''];
    foreach (BUILD_EXCLUDE_FILES as $f) {
        if ($rel === $f) return [false, ''];
    }
    foreach (BUILD_EXCLUDE_GLOBS as $pattern) {
        if (fnmatch($pattern, $base)) return [false, ''];
    }
    if (br_is_secret_like_outside_private($rel, $base)) return [false, ''];

    if (str_starts_with($base, '.') && !in_array($base, BUILD_ALLOWED_DOTFILES, true)) {
        return [false, $rel];
    }

    return [true, ''];
}

function br_should_include(string $relPath, string $root): bool
{
    return br_should_include2($relPath)[0];
}

function br_collect(string $root): array
{
    $out = [];
    $unexpectedDotfiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        $rel = br_norm(substr($item->getPathname(), strlen($root) + 1));
        if ($item->isDir()) continue;
        [$include, $rejectedDotfile] = br_should_include2($rel);
        if ($rejectedDotfile !== '') $unexpectedDotfiles[] = $rejectedDotfile;
        if (!$include) continue;
        $out[] = $rel;
    }
    if ($unexpectedDotfiles !== []) {
        fwrite(STDERR, "BUILD_RELEASE_FAIL Neočekávané skryté soubory (dotfiles) – smaž je, nebo pokud patří do vydání přidej je do BUILD_ALLOWED_DOTFILES vědomě:\n");
        foreach ($unexpectedDotfiles as $f) fwrite(STDERR, "  $f\n");
        exit(1);
    }
    sort($out);
    return $out;
}

function br_app_version(string $root): string
{
    $claude = @file_get_contents($root . '/CLAUDE.md') ?: '';
    if (preg_match('/Aktuální vrstva: \*\*v(\d+)\*\*/u', $claude, $m)) {
        return 'v' . $m[1];
    }
    return 'unknown';
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $out = br_arg($argv, 'out');
    $quiet = br_flag($argv, 'quiet');
    $doZip = br_flag($argv, 'zip');
    $doLint = br_flag($argv, 'lint');

    if ($out === null || trim($out) === '') {
        fwrite(STDERR, "BUILD_RELEASE_FAIL Chybí --out=<adresář mimo projekt>\n");
        exit(1);
    }
    $out = rtrim(br_norm($out), '/');
    $realRoot = br_norm(realpath($root) ?: $root);
    $outParent = dirname($out);
    @mkdir($outParent, 0770, true);
    $realOutParent = br_norm(realpath($outParent) ?: $outParent);
    $prospectiveOut = $realOutParent . '/' . basename($out);

    if ($prospectiveOut === $realRoot || str_starts_with($prospectiveOut . '/', $realRoot . '/')) {
        fwrite(STDERR, "BUILD_RELEASE_FAIL Cílový adresář nesmí ležet uvnitř projektu ($realRoot)\n");
        exit(1);
    }

    if (is_dir($out)) {
        if (!$quiet) echo "Mažu existující cílový adresář: $out\n";
        br_rrmdir($out);
    }
    if (!@mkdir($out, 0770, true) && !is_dir($out)) {
        fwrite(STDERR, "BUILD_RELEASE_FAIL Nelze vytvořit $out\n");
        exit(1);
    }

    $files = br_collect($root);
    $manifestFiles = [];
    $lintErrors = [];
    foreach ($files as $rel) {
        $src = $root . '/' . $rel;
        $dst = $out . '/' . $rel;
        @mkdir(dirname($dst), 0770, true);
        if (!@copy($src, $dst)) {
            fwrite(STDERR, "BUILD_RELEASE_FAIL Nelze zkopírovat $rel\n");
            exit(1);
        }
        $manifestFiles[$rel] = hash_file('sha256', $src) ?: '';

        if ($doLint && str_ends_with($rel, '.php')) {
            $phpBinary = PHP_BINARY ?: 'php';
            $cmd = escapeshellarg($phpBinary) . ' -l ' . escapeshellarg($src) . ' 2>&1';
            $lintOut = shell_exec($cmd) ?: '';
            if (!str_contains($lintOut, 'No syntax errors detected')) {
                $lintErrors[$rel] = trim($lintOut);
            }
        }
    }

    if ($lintErrors) {
        foreach ($lintErrors as $rel => $msg) {
            fwrite(STDERR, "LINT_FAIL $rel: $msg\n");
        }
        fwrite(STDERR, 'BUILD_RELEASE_FAIL php -l selhal na ' . count($lintErrors) . " souborech\n");
        exit(1);
    }

    $manifest = [
        'version' => br_app_version($root),
        'built_at' => date(DATE_ATOM),
        'file_count' => count($manifestFiles),
        'files' => $manifestFiles,
    ];
    file_put_contents($out . '/RELEASE_MANIFEST.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

    if ($doZip) {
        if (!class_exists('ZipArchive')) {
            fwrite(STDERR, "BUILD_RELEASE_FAIL --zip vyžaduje rozšíření zip, které není k dispozici\n");
            exit(1);
        }
        $zipPath = $out . '.zip';
        if (is_file($zipPath)) @unlink($zipPath);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            fwrite(STDERR, "BUILD_RELEASE_FAIL Nelze vytvořit $zipPath\n");
            exit(1);
        }
        $zipIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($out, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($zipIterator as $item) {
            /** @var SplFileInfo $item */
            if ($item->isDir()) continue;
            $localName = br_norm(substr($item->getPathname(), strlen($out) + 1));
            $zip->addFile($item->getPathname(), $localName);
        }
        $zip->close();
        if (!$quiet) echo "ZIP: $zipPath\n";
    }

    if (!$quiet) {
        echo 'Vydání: ' . $out . "\n";
        echo 'Verze: ' . $manifest['version'] . "\n";
    }
    echo 'BUILD_RELEASE_OK files=' . count($manifestFiles) . "\n";
    exit(0);
}
