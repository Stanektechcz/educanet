<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/run_audits.php (OPS-05)
 *
 * Spustí všechny audity vrstvy v51+ (a novější) jako samostatné procesy, každý s
 * čerstvou dočasnou kopií storage/ (žádný audit se nesmí dotknout ostré storage/).
 *
 * Použití:
 *   php tools/run_audits.php [--since=v48] [--only=v57,v58] [--exclude=v46]
 *                             [--timeout=180] [--with-smoke] [--with-router] [--json] [--tmp=<adresář>]
 *
 * --since   Nejnižší vrstva, kterou zahrnout (výchozí v51 – tj. v5[1-9]* a v6*). Lze jít
 *           i níž, např. --since=v30, pro srovnání stavu před/po refaktoringu.
 * --only    Filtr: audit se zahrne, jen pokud jeho název obsahuje některý z uvedených
 *           podřetězců (čárkou oddělený seznam).
 * --exclude Audit se vynechá, pokud jeho název obsahuje některý z uvedených podřetězců.
 * --with-smoke  Spustí navíc tools/v58_smoke_audit.php (jinak se do výchozí sady nepočítá,
 *               protože potřebuje běžící HTTP server).
 * --with-router Spustí navíc tests/app_router_audit.php (statická kontrola routeru bez storage;
 *               jinak se do výchozí sady nepočítá, protože leží mimo tools/ a glob() ho nevidí).
 * --json    Výstup jako JSON pole místo tabulky.
 * --tmp     Základní adresář pro dočasné kopie storage (výchozí: systémový temp aktuálního
 *           uživatele, nebo env EDUCANET_AUDIT_TMP_DIR).
 *
 * POZOR – citlivá data: dočasné kopie obsahují stejná osobní data žáků jako ostrá
 * storage/ (jen na čtení pro audit). Vždy se vytvářejí pod vlastním podadresářem
 * v systémovém temp *aktuálního* uživatele (nikdy sdílený adresář jiného účtu), s právy
 * 0700 tam, kde to OS dovolí, a vždy se smažou po doběhnutí – i při timeoutu nebo pádu
 * (finally + register_shutdown_function). Nikdy je neponechávej ležet ani nekopíruj mimo
 * tento dočasný adresář.
 *
 * Konec: "RUN_AUDITS_OK total=N failed=0" nebo "RUN_AUDITS_FAIL total=N failed=M" (exit 1).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/backup_storage.php'; // bkp_rcopy_list, bkp_copy_locked

function ra_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) {
            return substr($a, strlen($name) + 3);
        }
    }
    return $default;
}

function ra_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

function ra_discover_audits(string $toolsDir, string $since, array $only, array $exclude): array
{
    $files = glob($toolsDir . '/*audit*.php') ?: [];
    $result = [];
    foreach ($files as $file) {
        $base = basename($file);
        if ($base === basename(__FILE__)) {
            continue;
        }
        if (!ra_matches_since($base, $since)) {
            continue;
        }
        if ($only && !ra_matches_any($base, $only)) {
            continue;
        }
        if ($exclude && ra_matches_any($base, $exclude)) {
            continue;
        }
        $result[] = $file;
    }
    sort($result);
    return $result;
}

function ra_matches_any(string $name, array $needles): bool
{
    foreach ($needles as $needle) {
        if ($needle !== '' && str_contains($name, $needle)) {
            return true;
        }
    }
    return false;
}

/** Zahrne soubory od verze $since (v51) výše: v5[since..9]* a v6*, v7*, ... */
function ra_matches_since(string $name, string $since): bool
{
    if (!preg_match('/v(\d+)/i', $since, $m)) {
        return true;
    }
    $sinceVer = (int)$m[1];
    if (!preg_match('/v(\d+)/i', $name, $m2)) {
        return false; // audity bez verze v názvu (staré) se v51+ režimu vynechávají
    }
    return (int)$m2[1] >= $sinceVer;
}

/** Vlastní podadresář v systémovém temp aktuálního uživatele (nikdy sdílený s jiným účtem). */
function ra_tmp_base(?string $override): string
{
    $base = rtrim((string)($override ?: (getenv('EDUCANET_AUDIT_TMP_DIR') ?: sys_get_temp_dir())), '/\\');
    $dir = $base . '/educanet-audit-tmp';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    @chmod($dir, 0700);
    return $dir;
}

/** @var array<int,string> globální registr rozpracovaných dočasných kopií pro shutdown úklid. */
$GLOBALS['ra_temp_dirs'] = [];
register_shutdown_function(static function (): void {
    foreach ($GLOBALS['ra_temp_dirs'] ?? [] as $dir) {
        ra_rrmdir($dir);
    }
});

function ra_make_temp_storage(string $realStorage, string $tmpBase): string
{
    $tmp = $tmpBase . '/storage_' . bin2hex(random_bytes(6));
    mkdir($tmp, 0700, true);
    @chmod($tmp, 0700);
    $GLOBALS['ra_temp_dirs'][] = $tmp;
    foreach (bkp_rcopy_list($realStorage) as $rel) {
        $from = $realStorage . '/' . $rel;
        $to = $tmp . '/' . $rel;
        $dir = dirname($to);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        copy($from, $to);
    }
    return $tmp;
}

function ra_rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $full = $dir . '/' . $item;
        is_dir($full) ? ra_rrmdir($full) : @unlink($full);
    }
    @rmdir($dir);
    $key = array_search($dir, $GLOBALS['ra_temp_dirs'] ?? [], true);
    if ($key !== false) {
        unset($GLOBALS['ra_temp_dirs'][$key]);
    }
}

function ra_last_nonempty_line(string $text): string
{
    $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (trim($lines[$i]) !== '') {
            return trim($lines[$i]);
        }
    }
    return '';
}

/**
 * Spustí jeden audit jako samostatný proces s vlastní dočasnou storage.
 * Vrací ['name'=>string,'status'=>'PASS'|'FAIL'|'TIMEOUT','last_line'=>string,'seconds'=>float,'exit_code'=>int].
 */
function ra_run_one(string $file, string $phpBinary, int $timeoutSeconds, string $realStorageDir, string $tmpBase): array
{
    $name = basename($file);
    $tmpStorage = ra_make_temp_storage($realStorageDir, $tmpBase);
    try {
        $start = microtime(true);
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = [];
        foreach ($_SERVER as $k => $v) {
            if (is_scalar($v)) {
                $env[$k] = (string)$v;
            }
        }
        $env['EDUCANET_STORAGE_DIR'] = $tmpStorage;
        $env['EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY'] = '0';
        $cmd = escapeshellarg($phpBinary) . ' ' . escapeshellarg($file);
        $process = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__), $env, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            return ['name' => $name, 'status' => 'FAIL', 'last_line' => 'Nelze spustit proces.', 'seconds' => 0.0, 'exit_code' => -1];
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $out = '';
        $err = '';
        $deadline = microtime(true) + $timeoutSeconds;
        $timedOut = false;
        do {
            $out .= (string)stream_get_contents($pipes[1]);
            $err .= (string)stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            if (microtime(true) > $deadline) {
                $timedOut = true;
                @proc_terminate($process);
                break;
            }
            usleep(50000);
        } while (true);
        $out .= (string)stream_get_contents($pipes[1]);
        $err .= (string)stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = $timedOut ? -1 : (int)proc_close($process);
        $seconds = microtime(true) - $start;

        $lastLine = ra_last_nonempty_line($out !== '' ? $out : $err);
        $status = $timedOut ? 'TIMEOUT' : ($exitCode === 0 ? 'PASS' : 'FAIL');
        if ($lastLine === '') {
            $lastLine = $timedOut ? 'Vypršel časový limit.' : ('exit=' . $exitCode);
        }
        return ['name' => $name, 'status' => $status, 'last_line' => $lastLine, 'seconds' => $seconds, 'exit_code' => $exitCode];
    } finally {
        // Vždy uklidit dočasnou kopii citlivé storage – i při timeoutu/výjimce výše.
        ra_rrmdir($tmpStorage);
    }
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $since = (string)ra_arg($argv, 'since', 'v51');
    $only = array_filter(explode(',', (string)ra_arg($argv, 'only', '')));
    $exclude = array_filter(explode(',', (string)ra_arg($argv, 'exclude', '')));
    $timeout = (int)ra_arg($argv, 'timeout', '180');
    $withSmoke = ra_flag($argv, 'with-smoke');
    $withRouter = ra_flag($argv, 'with-router');
    $asJson = ra_flag($argv, 'json');
    $phpBinary = getenv('PHP_BINARY') ?: 'C:/php/php.exe';
    if (!is_file($phpBinary)) {
        $phpBinary = PHP_BINARY;
    }

    $toolsDir = __DIR__;
    $tmpBase = ra_tmp_base(ra_arg($argv, 'tmp'));
    $audits = ra_discover_audits($toolsDir, $since, $only, $exclude);
    $smokeFile = $toolsDir . '/v58_smoke_audit.php';
    if ($withSmoke && is_file($smokeFile) && !in_array($smokeFile, $audits, true)) {
        $audits[] = $smokeFile;
    }
    $routerFile = dirname($toolsDir) . '/tests/app_router_audit.php';
    if ($withRouter && is_file($routerFile) && !in_array($routerFile, $audits, true)) {
        $audits[] = $routerFile;
    }

    $results = [];
    foreach ($audits as $file) {
        $results[] = ra_run_one($file, $phpBinary, $timeout, STORAGE_DIR, $tmpBase);
    }

    $failed = 0;
    foreach ($results as $r) {
        if ($r['status'] !== 'PASS') {
            $failed++;
        }
    }

    if ($asJson) {
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    } else {
        printf("%-45s %-8s %6s  %s\n", 'audit', 'stav', 'cas(s)', 'posledni radek');
        foreach ($results as $r) {
            printf("%-45s %-8s %6.2f  %s\n", $r['name'], $r['status'], $r['seconds'], $r['last_line']);
        }
    }

    $total = count($results);
    if ($failed === 0) {
        echo "RUN_AUDITS_OK total={$total} failed=0\n";
        exit(0);
    }
    echo "RUN_AUDITS_FAIL total={$total} failed={$failed}\n";
    exit(1);
}
