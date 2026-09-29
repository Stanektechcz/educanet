<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v60 · tools/storage_selftest.php – kontrola, že se na serveru správně ukládá.
 *
 * Spouští se na produkci jako uživatel webu (www) s prostředím aplikace, viz docs/NASAZENI_AAPANEL.md
 * „Kontrola ukládání“:
 *   sudo -u www env -i PATH=/usr/bin:/bin HOME=/tmp bash -c \
 *     'set -a; . /www/server/educanet/educanet.env; set +a; cd /www/wwwroot/is.stanektech.cz; \
 *      /www/server/php/83/bin/php tools/storage_selftest.php --base=https://is.stanektech.cz'
 *
 * Přepínače:
 *   --base=URL           navíc ověří TLS certifikát (stream_socket_client s verify_peer; nepotřebuje allow_url_fopen)
 *   --open-basedir=LIST  hodnota open_basedir z PHP-FPM poolu (CLI ji nevidí); výchozí ini_get('open_basedir')
 *   --env-file=SOUBOR    načte NAME=hodnota (jen EDUCANET_*) před startem aplikace
 *   --strict             WARN se počítá jako chyba (exit 2)
 *
 * Nic nemění v datech žáků: jediné zápisy jsou dočasné soubory _selftest_<náhoda>.json.php, proud
 * selftest_<náhoda>/ a .selftest_*.tmp v uploads/ a cache/runtime, které se vždy smažou (včetně .lock).
 * Nikdy nevypisuje obsah souborů ani osobní údaje – jen počty a relativní názvy souborů.
 * Výstup: PASS/WARN/FAIL řádky a STORAGE_SELFTEST_OK|FAIL checks=N warn=W failed=F (exit 0 / 1 / 2).
 */

function st_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    }
    return null;
}

/** Načte jen EDUCANET_* proměnné ze souboru NAME=hodnota (před bootstrapem, který z nich čte STORAGE_DIR). */
function st_load_env_file(?string $path): void
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
st_load_env_file(st_arg($argv, 'env-file'));
require_once dirname(__DIR__) . '/bootstrap.php';

/** Počítadlo a výpis výsledků. */
function st_report(string $level, string $message): void
{
    $c = &$GLOBALS['st_counts'];
    if (!is_array($c)) $c = ['checks' => 0, 'warn' => 0, 'fail' => 0];
    $c['checks']++;
    if ($level === 'WARN') $c['warn']++;
    if ($level === 'FAIL') $c['fail']++;
    echo $level . ' ' . $message . "\n";
}

function st_check(bool $ok, string $message, string $failLevel = 'FAIL'): bool
{
    st_report($ok ? 'PASS' : $failLevel, $message);
    return $ok;
}

function st_rel(string $path): string
{
    $root = rtrim(str_replace('\\', '/', STORAGE_DIR), '/') . '/';
    $path = str_replace('\\', '/', $path);
    return str_starts_with($path, $root) ? substr($path, strlen($root)) : basename($path);
}

function st_euid(): ?int
{
    return function_exists('posix_geteuid') ? posix_geteuid() : null;
}

function st_check_dirs(): void
{
    $dir = STORAGE_DIR;
    if (!st_check(is_dir($dir), 'STORAGE_DIR existuje')) return;
    st_check(is_writable($dir), 'STORAGE_DIR je zapisovatelný pro aktuálního uživatele');
    $euid = st_euid();
    if ($euid === null) {
        st_report('WARN', 'posix není dostupné – vlastník STORAGE_DIR se neověřuje');
        return;
    }
    st_check((int)@fileowner($dir) === $euid, 'STORAGE_DIR vlastní uživatel procesu (uid ' . $euid . ')');
}

/** Soubory ve storage, které nepatří uživateli procesu. */
function st_check_foreign_files(): void
{
    $euid = st_euid();
    if ($euid === null || !is_dir(STORAGE_DIR)) return;
    $foreign = [];
    $unwritable = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(STORAGE_DIR, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile() || (int)$file->getOwner() === $euid) continue;
        $foreign[] = st_rel($file->getPathname());
        if (!$file->isWritable()) $unwritable++;
    }
    if ($foreign === []) { st_report('PASS', 'žádný soubor ve storage nepatří jinému uživateli'); return; }
    $msg = count($foreign) . ' souborů ve storage nepatří uživateli procesu (např. ' . implode(', ', array_slice($foreign, 0, 3)) . ') – spusť chown -R www:www storage';
    st_report($unwritable > 0 ? 'FAIL' : 'WARN', $msg . ($unwritable > 0 ? '; ' . $unwritable . ' z nich nejde zapisovat' : ''));
}

/** Zápisová zkouška přes storage_update + storage_append; vždy uklidí. */
function st_probe_storage(): void
{
    if (!is_dir(STORAGE_DIR) || !is_writable(STORAGE_DIR)) { st_report('FAIL', 'zápisová zkouška přeskočena (storage není zapisovatelná)'); return; }
    $token = bin2hex(random_bytes(6));
    $path = STORAGE_DIR . '/_selftest_' . $token . '.json.php';
    $stream = 'selftest_' . $token;
    try {
        st_probe_map($path);
        st_probe_stream($stream);
    } catch (Throwable $e) {
        st_report('FAIL', 'zápisová zkouška selhala: ' . get_class($e));
    } finally {
        st_probe_cleanup($path, $stream);
    }
    $left = glob(STORAGE_DIR . '/_selftest_*') ?: [];
    $leftStream = is_dir(STORAGE_DIR . '/' . $stream);
    st_check($left === [] && !$leftStream, 'po zkoušce nezůstal žádný dočasný soubor ani zámek');
}

function st_probe_map(string $path): void
{
    storage_update($path, static function (array $d): array { $d['n'] = 1; return $d; });
    $first = storage_read($path);
    st_check(($first['n'] ?? null) === 1, 'storage_update: zápis a čtení zpět');
    $raw = (string)@file_get_contents($path);
    st_check(str_starts_with($raw, STORAGE_GUARD_LINE), 'nový soubor má ochranný první řádek');
    storage_update($path, static function (array $d): array { $d['n'] = (int)($d['n'] ?? 0) + 1; return $d; });
    storage_update($path, static function (array $d): array { $d['n'] = (int)($d['n'] ?? 0) + 1; return $d; });
    st_check((int)(storage_read($path)['n'] ?? 0) === 3, 'dvě aktualizace po sobě se nepřepíšou (zámek)');
    st_check(is_file(storage_lock_path($path)), 'zámek <soubor>.lock vzniká (LOCK_EX)');
}

function st_probe_stream(string $stream): void
{
    if (!function_exists('storage_append')) { st_report('WARN', 'storage_append není k dispozici'); return; }
    storage_append($stream, ['n' => 1]);
    storage_append($stream, ['n' => 2]);
    $rows = function_exists('storage_stream_rows') ? storage_stream_rows($stream) : [];
    st_check(count($rows) === 2, 'storage_append: dva záznamy proudu se zapsaly a jdou přečíst');
}

function st_probe_cleanup(string $path, string $stream): void
{
    foreach ([$path, storage_lock_path($path)] as $file) if (is_file($file)) @unlink($file);
    $dir = STORAGE_DIR . '/' . $stream;
    if (!is_dir($dir) || !str_starts_with($stream, 'selftest_')) return;
    foreach (glob($dir . '/*') ?: [] as $file) if (is_file($file)) @unlink($file);
    foreach (glob($dir . '/.*') ?: [] as $file) if (is_file($file)) @unlink($file);
    @rmdir($dir);
}

/** Zapisovatelnost pomocného adresáře zkušebním souborem (hned se maže). */
function st_dir_writable(string $dir): bool
{
    if (!is_dir($dir) || !is_writable($dir)) return false;
    $probe = $dir . '/.selftest_' . bin2hex(random_bytes(4)) . '.tmp';
    $ok = @file_put_contents($probe, 'x') === 1;
    if (is_file($probe)) @unlink($probe);
    return $ok;
}

function st_check_aux_dirs(): void
{
    st_check(st_dir_writable(UPLOAD_DIR), 'uploads/ existuje a je zapisovatelné');
    st_check(st_dir_writable(dirname(__DIR__) . '/cache/runtime'), 'cache/runtime je zapisovatelné', 'WARN');
}

/** Cesta session.save_path bez prefixu „N;“ a případného módu; prázdná = systémový temp. */
function st_session_path(): string
{
    $raw = trim((string)ini_get('session.save_path'));
    if (($pos = strrpos($raw, ';')) !== false) $raw = substr($raw, $pos + 1);
    return $raw !== '' ? $raw : sys_get_temp_dir();
}

function st_in_open_basedir(string $path, string $list): bool
{
    if (trim($list) === '') return true;
    $real = str_replace('\\', '/', (string)(realpath($path) ?: $path));
    foreach (explode(PATH_SEPARATOR, $list) as $base) {
        $baseReal = str_replace('\\', '/', (string)(realpath($base) ?: $base));
        if ($baseReal !== '' && str_starts_with(rtrim($real, '/') . '/', rtrim($baseReal, '/') . '/')) return true;
    }
    return false;
}

function st_check_session(array $argv): void
{
    $path = st_session_path();
    st_check(is_dir($path) && is_writable($path), 'session.save_path je zapisovatelná');
    $basedir = st_arg($argv, 'open-basedir') ?? (string)ini_get('open_basedir');
    if (trim($basedir) === '') { st_report('WARN', 'open_basedir není zadáno (CLI ho nevidí) – přidej --open-basedir=<hodnota z poolu>'); return; }
    st_check(st_in_open_basedir($path, $basedir), 'session.save_path leží v open_basedir');
    st_check(st_in_open_basedir(STORAGE_DIR, $basedir), 'STORAGE_DIR leží v open_basedir');
}

function st_check_disk(): void
{
    $free = @disk_free_space(STORAGE_DIR);
    if ($free === false) { st_report('WARN', 'volné místo na disku nejde zjistit'); return; }
    $mb = (int)floor($free / 1048576);
    if ($mb < 50) st_report('FAIL', 'volné místo na disku: ' . $mb . ' MB');
    elseif ($mb < 500) st_report('WARN', 'volné místo na disku: ' . $mb . ' MB (pod 500 MB)');
    else st_report('PASS', 'volné místo na disku: ' . $mb . ' MB');
}

/** Nejnovější položka v EDUCANET_BACKUP_DIR (mtime). */
function st_newest_backup(string $dir): ?int
{
    $newest = null;
    foreach (scandir($dir) ?: [] as $name) {
        if ($name === '.' || $name === '..') continue;
        $mtime = @filemtime($dir . '/' . $name);
        if ($mtime !== false && ($newest === null || $mtime > $newest)) $newest = $mtime;
    }
    return $newest;
}

function st_check_backup(): void
{
    $dir = rtrim((string)getenv('EDUCANET_BACKUP_DIR'), '/\\');
    if ($dir === '') { st_report('WARN', 'EDUCANET_BACKUP_DIR není nastaveno – zálohy nejde ověřit'); return; }
    if (!st_check(is_dir($dir), 'adresář záloh existuje', 'WARN')) return;
    $newest = st_newest_backup($dir);
    if ($newest === null) { st_report('WARN', 'v adresáři záloh zatím nic není (spusť tools/backup_storage.php)'); return; }
    $hours = (int)floor((time() - $newest) / 3600);
    st_check($hours <= 36, 'nejnovější záloha je stará ' . $hours . ' h (limit 36 h)', 'WARN');
}

/** Přečte všechny *.json.php / *.jsonl.php; vrací názvy nečitelných a souborů bez guardu. */
function st_scan_files(): array
{
    $bad = [];
    $noGuard = [];
    $count = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(STORAGE_DIR, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        $name = $file->getFilename();
        if (!$file->isFile() || !preg_match('/\.jsonl?\.php$/', $name) || str_contains($name, '.tmp.') || str_starts_with($name, '_selftest_')) continue;
        $count++;
        $raw = @file_get_contents($file->getPathname());
        $rel = st_rel($file->getPathname());
        if ($raw === false || !st_file_parses($raw, str_ends_with($name, '.jsonl.php'))) $bad[] = $rel;
        elseif (!str_starts_with($raw, STORAGE_GUARD_LINE) && $raw !== '') $noGuard[] = $rel;
    }
    return ['count' => $count, 'bad' => $bad, 'no_guard' => $noGuard];
}

function st_file_parses(string $raw, bool $lines): bool
{
    $body = trim((string)preg_replace('/^<\?php.*?\?>\s*/s', '', $raw));
    if ($body === '') return true;
    if (!$lines) return is_array(json_decode($body, true));
    foreach (explode("\n", $body) as $line) {
        if (trim($line) !== '' && !is_array(json_decode($line, true))) return false;
    }
    return true;
}

function st_check_parse(): void
{
    if (!is_dir(STORAGE_DIR)) return;
    $scan = st_scan_files();
    $bad = $scan['bad'];
    st_check($bad === [], $scan['count'] . ' datových souborů zkontrolováno, nečitelných: ' . count($bad) . ($bad ? ' (' . implode(', ', array_slice($bad, 0, 10)) . ')' : ''));
    $noGuard = $scan['no_guard'];
    st_check($noGuard === [], 'soubory bez ochranného prvního řádku: ' . count($noGuard) . ($noGuard ? ' (' . implode(', ', array_slice($noGuard, 0, 5)) . ')' : ''), 'WARN');
}

/** Ověření TLS certifikátu adresy --base (verify_peer); bez allow_url_fopen. */
function st_check_tls(?string $base): void
{
    $appUrl = trim((string)getenv('EDUCANET_APP_URL'));
    if (str_starts_with($appUrl, 'https://')) {
        st_report('PASS', 'EDUCANET_APP_URL je https – bez platného certifikátu cookie_secure=1 znemožní přihlášení (ověř přes --base=URL)');
    }
    if ($base === null || $base === '') return;
    $parts = parse_url($base);
    if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) { st_report('FAIL', '--base musí být adresa https://…'); return; }
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => (string)$parts['host'], 'capture_peer_cert' => true]]);
    $errno = 0;
    $errstr = '';
    $sock = @stream_socket_client('ssl://' . $parts['host'] . ':' . (int)($parts['port'] ?? 443), $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $ctx);
    if ($sock === false) { st_report('FAIL', 'TLS spojení s ověřením certifikátu selhalo: ' . preg_replace('/\s+/', ' ', substr($errstr, 0, 160))); return; }
    $cert = stream_context_get_params($sock)['options']['ssl']['peer_certificate'] ?? null;
    fclose($sock);
    st_report('PASS', 'TLS certifikát je platný a odpovídá názvu hostitele');
    $info = ($cert && function_exists('openssl_x509_parse')) ? openssl_x509_parse($cert) : false;
    if (is_array($info) && isset($info['validTo_time_t'])) {
        $days = (int)floor(((int)$info['validTo_time_t'] - time()) / 86400);
        st_check($days >= 14, 'certifikát vyprší za ' . $days . ' dní (limit 14)', 'WARN');
    }
}

function st_main(array $argv): int
{
    echo "EDUCANET storage selftest\n";
    st_check_dirs();
    st_check_foreign_files();
    st_probe_storage();
    st_check_aux_dirs();
    st_check_session($argv);
    st_check_disk();
    st_check_backup();
    st_check_parse();
    st_check_tls(st_arg($argv, 'base'));
    $c = $GLOBALS['st_counts'];
    $line = 'checks=' . $c['checks'] . ' warn=' . $c['warn'] . ' failed=' . $c['fail'];
    if ($c['fail'] > 0) { echo 'STORAGE_SELFTEST_FAIL ' . $line . "\n"; return 1; }
    echo 'STORAGE_SELFTEST_OK ' . $line . "\n";
    return ($c['warn'] > 0 && in_array('--strict', $argv, true)) ? 2 : 0;
}

exit(st_main($argv));
