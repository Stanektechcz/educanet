<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · týdenní kontrola provozu bez externích služeb.
 *
 * Spustí jako podprocesy tools/storage_selftest.php, tools/preflight.php a tools/v61_perf_report.php (měření jen
 * pro čtení), převede výsledky na PASS/WARN/FAIL + počty a zapíše je
 *   - do storage/ops_health_v61.json.php (posledních 12 běhů, přes storage_update) – čte je banner pro administrátora
 *     v učitelském cockpitu a záložka Provoz,
 *   - na STDOUT (log cronu) a do souboru health-v61.log v EDUCANET_BACKUP_DIR (jeden řádek na běh).
 * Nic z osobních údajů ani tajných hodnot se neukládá ani nevypisuje.
 *
 *   php tools/v61_weekly_health.php [--env-file=<educanet.env>] [--base=https://domena] [--skip=perf,preflight,selftest]
 *                                   [--perf-runs=3] [--log=<soubor>] [--no-log] [--no-record]
 *
 * Spouští se jako uživatel webu (www): cron případ `health` v docs/deploy/aapanel/educanet-cron.sh.example.
 * Konec: WEEKLY_HEALTH_OK|WARN|FAIL checks=N fail=F warn=W (exit 0 / 2 u WARN / 1 u FAIL).
 */

function wh_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    }
    return $default;
}

/** Načte jen EDUCANET_* proměnné ze souboru NAME=hodnota (před bootstrapem; skutečné prostředí má přednost). */
function wh_load_env_file(?string $path): void
{
    if ($path === null || !is_file($path)) return;
    foreach ((@file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (str_starts_with($line, 'export ')) $line = trim(substr($line, 7));
        $eq = strpos($line, '=');
        if ($eq === false) continue;
        $name = trim(substr($line, 0, $eq));
        if (preg_match('/^EDUCANET_[A-Z0-9_]+$/', $name) !== 1 || getenv($name) !== false) continue;
        putenv($name . '=' . trim(trim(substr($line, $eq + 1)), "\"'"));
    }
}

$argv = $argv ?? [];
wh_load_env_file(wh_arg($argv, 'env-file'));
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/ops_v61.php';
require_once __DIR__ . '/lib/v61_health_lib.php';
require_once dirname(__DIR__) . '/ops_v67.php'; // v67: rozšířená kontrola provozu

/** Argumenty podprocesů podle názvu kontroly. @return list<string> */
function wh_tool_args(string $name, array $argv): array
{
    $root = dirname(__DIR__);
    $envFile = wh_arg($argv, 'env-file');
    $base = wh_arg($argv, 'base') ?? (string)getenv('EDUCANET_APP_URL');
    switch ($name) {
        case 'selftest':
            $args = [$root . '/tools/storage_selftest.php'];
            if (str_starts_with($base, 'https://')) $args[] = '--base=' . $base;
            return $args;
        case 'preflight':
            return array_merge([$root . '/tools/preflight.php'], $envFile !== null ? ['--env-file=' . $envFile] : []);
        default:
            return [$root . '/tools/v61_perf_report.php', '--runs=' . max(3, min(10, (int)wh_arg($argv, 'perf-runs', '3')))];
    }
}

/** @return array<string,array<string,mixed>> */
function wh_collect(array $argv): array
{
    $skip = array_filter(array_map('trim', explode(',', (string)wh_arg($argv, 'skip', ''))));
    $prefix = ['selftest' => 'STORAGE_SELFTEST', 'preflight' => 'PREFLIGHT', 'perf' => 'PERF_REPORT'];
    $checks = [];
    foreach ($prefix as $name => $final) {
        if (in_array($name, $skip, true)) { $checks[$name] = ['status' => 'SKIP', 'summary' => 'přeskočeno', 'counts' => [], 'issues' => []]; continue; }
        $run = h61_run_tool(wh_tool_args($name, $argv));
        $checks[$name] = h61_parse_result($final, $run['output'], $run['exit'], $run['timed_out']);
    }
    $checks['ops'] = in_array('ops', $skip, true) ? ['status' => 'SKIP', 'summary' => 'přeskočeno', 'counts' => [], 'issues' => []] : ops67_health_check();
    return $checks;
}

function wh_main(array $argv): int
{
    $started = time();
    $checks = wh_collect($argv);
    $overall = h61_overall($checks);
    $counts = array_count_values(array_map(static fn(array $c): string => (string)$c['status'], $checks));
    $run = ['at' => date(DATE_ATOM, $started), 'duration_s' => time() - $started, 'status' => $overall, 'checks' => $checks];
    foreach ($checks as $name => $c) {
        echo $c['status'] . ' ' . $name . ($c['summary'] !== '' ? ' ' . $c['summary'] : '') . "\n";
        foreach ($c['issues'] as $issue) echo '  ' . $issue . "\n";
    }
    $recorded = false;
    if (!in_array('--no-record', $argv, true)) {
        try { ops61_health_record($run); $recorded = true; }
        catch (Throwable $e) { fwrite(STDERR, "FAIL zápis výsledku do úložiště: " . h61_scrub($e->getMessage()) . "\n"); $overall = 'FAIL'; }
    }
    $line = 'WEEKLY_HEALTH_' . ($overall === 'PASS' ? 'OK' : $overall) . ' checks=' . count($checks) . ' fail=' . ($counts['FAIL'] ?? 0) . ' warn=' . ($counts['WARN'] ?? 0) . ' recorded=' . (int)$recorded;
    wh_log($argv, date(DATE_ATOM, $started) . ' ' . $line);
    echo $line . "\n";
    return $overall === 'FAIL' ? 1 : ($overall === 'WARN' ? 2 : 0);
}

function wh_log(array $argv, string $line): void
{
    if (in_array('--no-log', $argv, true)) return;
    $dir = rtrim((string)getenv('EDUCANET_BACKUP_DIR'), '/\\');
    $path = wh_arg($argv, 'log') ?? ($dir !== '' && is_dir($dir) ? $dir . '/health-v61.log' : '');
    if ($path !== '' && !h61_append_log($path, $line)) fwrite(STDERR, "WARN log kontroly nelze zapsat (zkontroluj práva adresáře záloh)\n");
}

exit(wh_main($argv));
