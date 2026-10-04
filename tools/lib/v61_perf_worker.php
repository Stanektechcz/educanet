<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · měřicí proces jedné stránky (čerstvý PHP proces = čerstvé paměti v rámci požadavku).
 *
 *   php tools/lib/v61_perf_worker.php '<json>'
 *   json: {"kind":"student|teacher","query":{…},"session":{…},"storage":"<adresář>"}
 * Vykreslí index.php / teacher.php přes ob_start (výstup zahodí) a na stdout vypíše jeden JSON řádek:
 *   {"ms":…,"bytes":…,"calls":…,"disk":…,"unique":…,"max_same":…,"status":…,"location":…,"error":…}
 * Relace je v paměti (vlastní save handler) – žádný soubor relace, žádný cookie. Čas = od příprav požadavku
 * po konec skriptu (bootstrap + knihovny + router + šablona), bez spuštění PHP interpretu.
 * Zápisy do úložiště řídí EDUCANET_STORAGE_READONLY (zde nenastavujeme; nastavuje volající).
 */

final class V61PerfSession implements SessionHandlerInterface
{
    public function __construct(private string $payload) {}
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $id): string|false { return $this->payload; }
    public function write(string $id, string $data): bool { return true; }
    public function destroy(string $id): bool { return true; }
    public function gc(int $max_lifetime): int|false { return 0; }
}

$spec = json_decode((string)(getenv('V61_PERF_SPEC') ?: ($argv[1] ?? '')), true);
if (!is_array($spec) || !in_array($spec['kind'] ?? '', ['student', 'teacher', 'readonly_probe'], true)) { fwrite(STDERR, "bad spec\n"); exit(2); }
$root = str_replace(chr(92), '/', dirname(__DIR__, 2));
if (is_string($spec['storage'] ?? null) && $spec['storage'] !== '') { putenv('EDUCANET_STORAGE_DIR=' . $spec['storage']); $_ENV['EDUCANET_STORAGE_DIR'] = $spec['storage']; }
chdir($root);
if ($spec['kind'] === 'readonly_probe') {
    // Samotest režimu jen pro čtení: nad DOČASNÝM úložištěm zkusí zápis a ohlásí, jestli soubor vznikl (ostrá data se nikdy nepoužijí).
    $_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
    session_set_save_handler(new V61PerfSession(''), true);
    session_id('v61probe' . bin2hex(random_bytes(8)));
    require $root . '/bootstrap.php';
    $probe = STORAGE_DIR . '/v61_probe.json.php';
    storage_update($probe, static fn(array $d): array => ['x' => 1]);
    storage_append('practice_results', ['probe' => 1]);
    $wrote = is_file($probe) || is_dir(STORAGE_DIR . '/practice_results');
    echo "\nV61PERF " . json_encode(['ms' => 0, 'bytes' => 0, 'calls' => 0, 'disk' => 0, 'unique' => 0, 'files' => 0, 'max_same' => 0, 'status' => 200, 'location' => '', 'error' => false, 'wrote' => $wrote]) . "\n";
    exit(0);
}
$entry =$spec['kind'] === 'teacher' ? 'teacher.php' : 'index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/' . $entry;
$_SERVER['SCRIPT_NAME'] = '/' . $entry;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_GET = array_map('strval', (array)($spec['query'] ?? []));
$_SERVER['REQUEST_URI'] = '/' . $entry . '?' . http_build_query($_GET);
$_SERVER['QUERY_STRING'] = http_build_query($_GET);

$payload = '';
foreach ((array)($spec['session'] ?? []) as $k => $v) $payload .= $k . '|' . serialize($v);
session_set_save_handler(new V61PerfSession($payload), true);
session_id('v61perf' . bin2hex(random_bytes(8)));

$GLOBALS['educanet_request_memo'] = true; // jako ve webovém požadavku (PHP-FPM): paměť čtení v rámci jednoho vykreslení
$t0 = hrtime(true);
ob_start();
register_shutdown_function(static function () use ($t0, $spec): void {
    $html = (string)ob_get_clean();
    if (is_string($spec['dump'] ?? null) && $spec['dump'] !== '') file_put_contents($spec['dump'], $html); // ladění: uložení vykresleného HTML
    $ms = (hrtime(true) - $t0) / 1e6;
    $stats = $GLOBALS['educanet_storage_stats'] ?? ['calls' => 0, 'disk' => 0, 'by' => []];
    $by = (array)($stats['by'] ?? []);
    $location = '';
    foreach (headers_list() as $h) if (stripos($h, 'Location:') === 0) $location = trim(substr($h, 9));
    $err = error_get_last();
    $fatal = is_array($err) && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
    echo "\nV61PERF " . json_encode([
        'ms' => round($ms, 2),
        'bytes' => strlen($html),
        'calls' => (int)($stats['calls'] ?? 0),
        'disk' => (int)($stats['disk'] ?? 0),
        'unique' => count($by),
        'files' => count(get_included_files()),
        'max_same' => $by === [] ? 0 : max($by),
        'top' => (static function (array $b): array { arsort($b); return array_slice($b, 0, 4, true); })($by),
        'status' => (int)(http_response_code() ?: 200),
        'location' => $location,
        'error' => $fatal || preg_match('/Fatal error|Uncaught|Warning:|Notice:|Deprecated:/', $html) === 1,
    ], JSON_UNESCAPED_UNICODE) . "\n";
});
error_reporting(E_ALL);
ini_set('display_errors', '0');
require $root . '/' . $entry;
