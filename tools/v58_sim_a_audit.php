<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Audit SIM-A – SSH klíče, archivy, cron, uživatelé a práva.
 * Pouze CLI, izolované dočasné úložiště (nikdy nesahá na storage/). Nic nespouští, žádná síť.
 * Načítá jen VLASTNÍ rozšíření (lab58_skip_ext), takže rozpracované soubory jiných agentů audit neovlivní.
 * Spuštění: php tools/v58_sim_a_audit.php  →  poslední řádek V58_SIM_A_AUDIT_OK checks=N failed=0
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Prague');

$ROOT = dirname(__DIR__);
$TMP = sys_get_temp_dir() . '/v58_sim_a_audit_' . bin2hex(random_bytes(6));
mkdir($TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $TMP;
$GLOBALS['lab57_secret_override'] = 'v58-sim-a-secret';
$GLOBALS['lab58_skip_ext'] = true;
ini_set('log_errors', '1');
ini_set('error_log', $TMP . '/php_errors.log');

register_shutdown_function(static function () use ($TMP): void {
    if (!is_dir($TMP)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($TMP, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($TMP);
});

require_once $ROOT . '/linux_v57_lab.php';
foreach (['archive', 'users', 'ssh', 'cron'] as $f) require_once $ROOT . '/linux_v58_cmd_' . $f . '.php';
foreach (['keys', 'archives', 'cron', 'users'] as $f) require_once $ROOT . '/linux_v58_levels_' . $f . '.php';

const SIMA_NOW = 1790000000; // pevný čas (Po 2026-09-21 …)

$GLOBALS['sima'] = ['checks' => 0, 'failed' => 0];
function sima_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['sima']['checks']++;
    if (!$ok) $GLOBALS['sima']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}

function sima_world(string $seed = 'sima'): Lab57World
{
    return lab57_build_world(lab57_sandbox_level(), 'sima|' . $seed, ['CODE' => 'EDU-TEST-0000'], SIMA_NOW);
}

function sima_out(array $run, ?int $fd = null): string
{
    $o = '';
    foreach ($run['chunks'] as [$f, $t]) if ($fd === null || $f === $fd) $o .= $t;
    return (string)preg_replace('/\e\[[0-9;]*m/', '', $o);
}

/** Spustí příkaz nad světem, vrátí [stdout, stderr, exit, tips]. */
function sima_run(Lab57World $w, string $line): array
{
    $run = lab57_run_line($w, $line);
    return [sima_out($run, 1), sima_out($run, 2), (int)$run['exit'], $w->tips];
}

function sima_session(string $level, string $line, string $student = 'zak-a', string $class = 'class_3a', int $now = SIMA_NOW): array
{
    return lab57_session(['class' => $class, 'student' => $student, 'label' => 'Test Žák', 'context' => 'practice', 'level' => $level, 'now' => $now, 'cli' => true, 'classmates' => ['zak-a', 'zak-b']], 'run', ['line' => $line]);
}

function sima_sess_out(array $resp, ?int $fd = null): string
{
    $o = '';
    foreach ((array)($resp['out'] ?? []) as [$f, $t]) if ($fd === null || $f === $fd) $o .= $t;
    return (string)preg_replace('/\e\[[0-9;]*m/', '', $o);
}

// ===========================================================================
// 0) Bezpečnostní token-sken vlastních souborů
// ===========================================================================

(static function () use ($ROOT): void {
    $forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'create_function', 'assert',
        'fsockopen', 'pfsockopen', 'stream_socket_client', 'socket_create', 'socket_connect', 'curl_init', 'curl_exec', 'curl_multi_exec',
        'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr', 'mail'];
    $fileFuncs = ['file_get_contents', 'fopen', 'file'];
    $files = ['linux_v58_cmd_ssh.php', 'linux_v58_cmd_archive.php', 'linux_v58_cmd_cron.php', 'linux_v58_cmd_users.php',
        'linux_v58_levels_keys.php', 'linux_v58_levels_archives.php', 'linux_v58_levels_cron.php', 'linux_v58_levels_users.php', 'tools/v58_sim_a_audit.php'];
    $violations = [];
    foreach ($files as $rel) {
        $src = (string)file_get_contents($ROOT . '/' . $rel);
        $tokens = token_get_all($src);
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $tok = $tokens[$i];
            if ($tok === '`') { $violations[] = $rel . ': zpětné apostrofy'; continue; }
            if (!is_array($tok) || $tok[0] !== T_STRING) continue;
            $name = strtolower($tok[1]);
            $j = $i + 1;
            while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (($tokens[$j] ?? null) !== '(') continue;
            $p = $i - 1;
            while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) $p--;
            $prev = $tokens[$p] ?? null;
            if (is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true)) continue;
            if (in_array($name, $forbidden, true)) $violations[] = $rel . ':' . $tok[2] . ' ' . $name . '()';
            if (in_array($name, $fileFuncs, true)) {
                $k = $j + 1;
                while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k++;
                if (($tokens[$k][0] ?? null) === T_CONSTANT_ENCAPSED_STRING && preg_match('~^.(https?|ftp|php)://~i', $tokens[$k][1]) === 1) $violations[] = $rel . ':' . $tokens[$k][2] . ' ' . $name . '(URL)';
            }
        }
    }
    sima_check('safety:no-exec-network', $violations === [], implode('; ', $violations));
})();

sima_check('registry:no-errors', lab58_registry_errors() === [], implode(' | ', lab58_registry_errors()));

// ===========================================================================
// 1) Archivy
// ===========================================================================

$w = sima_world('arc');
sima_run($w, 'printf "ahoj svete, tohle je test\n" > ~/a.txt');
[$o] = sima_run($w, 'gzip -k a.txt');
sima_check('arc:gzip-keeps-original', $w->fs->isFile($w->home() . '/a.txt') && $w->fs->isFile($w->home() . '/a.txt.gz'));
[$o] = sima_run($w, 'file a.txt.gz');
sima_check('arc:file-gzip', str_contains($o, 'gzip compressed data'), $o);
[$o] = sima_run($w, 'zcat a.txt.gz');
sima_check('arc:zcat-roundtrip', $o === "ahoj svete, tohle je test\n", var_export($o, true));
[$o, $e, $x] = sima_run($w, 'gunzip a.txt.gz');
sima_check('arc:gunzip-removes-gz', !$w->fs->isFile($w->home() . '/a.txt.gz') && $x === 0);

sima_run($w, 'mkdir -p proj && printf obsah1 > proj/f1.txt && printf obsah2 > proj/f2.txt');
[$o, $e, $x] = sima_run($w, 'tar -czf proj.tar.gz proj');
sima_check('arc:tar-create', $x === 0 && $w->fs->isFile($w->home() . '/proj.tar.gz'));
[$o] = sima_run($w, 'file proj.tar.gz');
sima_check('arc:tar-gz-is-gzip', str_contains($o, 'gzip compressed data'), $o);
[$o] = sima_run($w, 'tar -tzf proj.tar.gz');
sima_check('arc:tar-list', str_contains($o, 'proj/f1.txt') && str_contains($o, 'proj/f2.txt'), $o);
sima_run($w, 'rm -rf proj');
[$o, $e, $x] = sima_run($w, 'tar -xzf proj.tar.gz');
[$o] = sima_run($w, 'cat proj/f1.txt proj/f2.txt');
sima_check('arc:tar-extract', $o === 'obsah1obsah2', var_export($o, true));

[$o, $e, $x] = sima_run($w, 'zip -r proj.zip proj');
sima_check('arc:zip-create', $x === 0 && $w->fs->isFile($w->home() . '/proj.zip'));
[$o] = sima_run($w, 'file proj.zip');
sima_check('arc:file-zip', str_contains($o, 'Zip archive data'), $o);
[$o] = sima_run($w, 'unzip -l proj.zip');
sima_check('arc:unzip-list', str_contains($o, 'proj/f1.txt') && str_contains($o, 'Archive:'), $o);

sima_run($w, 'bzip2 -k a.txt');
[$o] = sima_run($w, 'file a.txt.bz2');
sima_check('arc:file-bzip2', str_contains($o, 'bzip2 compressed data'), $o);
[$o] = sima_run($w, 'bzcat a.txt.bz2');
sima_check('arc:bzip2-roundtrip', $o === "ahoj svete, tohle je test\n", var_export($o, true));

[$o, $e, $x] = sima_run($w, 'gunzip neexistuje.gz');
sima_check('arc:gunzip-missing', $x !== 0 && str_contains($e, 'neexistuje.gz'), "exit=$x err=$e");

// tar detekuje ustar
[$o] = sima_run($w, 'file /nic 2>/dev/null; printf zkouska > raw.txt; tar -cf raw.tar raw.txt; file raw.tar');
sima_check('arc:file-tar', str_contains($o, 'POSIX tar archive'), $o);

// determinismus: stejné semínko = stejné bajty archivu
$w1 = sima_world('det');
$w2 = sima_world('det');
sima_run($w1, 'printf data > x.txt; gzip x.txt');
sima_run($w2, 'printf data > x.txt; gzip x.txt');
sima_check('arc:deterministic', (string)($w1->fs->get($w1->home() . '/x.txt.gz')['c'] ?? 'a') === (string)($w2->fs->get($w2->home() . '/x.txt.gz')['c'] ?? 'b'));

// ===========================================================================
// 2) SSH klíče
// ===========================================================================

$w = sima_world('ssh');
[$o, $e, $x] = sima_run($w, "ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N '' -C student@lab");
sima_check('ssh:keygen-exit', $x === 0 && str_contains($o, 'The key fingerprint is:'), "exit=$x");
$priv = $w->fs->get($w->home() . '/.ssh/id_ed25519');
$pub = $w->fs->get($w->home() . '/.ssh/id_ed25519.pub');
sima_check('ssh:keygen-perms', $priv !== null && ((int)$priv['m'] & 07777) === 0600 && $pub !== null && ((int)$pub['m'] & 07777) === 0644);
sima_check('ssh:keygen-fp-format', preg_match('/SHA256:[A-Za-z0-9+\/]{43}/', $o) === 1, $o);
sima_check('ssh:keygen-randomart', str_contains($o, '[ED25519 256]') && str_contains($o, '[SHA256]'));
[$o] = sima_run($w, 'ssh-keygen -l -f ~/.ssh/id_ed25519.pub');
sima_check('ssh:keygen-list-fp', preg_match('/^256 SHA256:\S+ student@lab \(ED25519\)$/', trim($o)) === 1, $o);

// determinismus otisku
$w2 = sima_world('ssh');
[$o2] = sima_run($w2, "ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N '' -C student@lab");
preg_match('/SHA256:\S+/', $o, $m1);
preg_match('/SHA256:\S+/', $o2, $m2);
sima_check('ssh:keygen-deterministic', ($m1[0] ?? 'a') === ($m2[0] ?? 'b'), ($m1[0] ?? '') . ' vs ' . ($m2[0] ?? ''));

// ssh na neexistujícího hosta v sandboxu = odmítnutí bez pádu, žádné „unknown option"
[$o, $e, $x] = sima_run($w, "ssh student@intranet.skola.test 'ls'");
sima_check('ssh:no-account-denied', $x === 255 && str_contains($e, 'Permission denied'), "exit=$x err=$e");

// úroveň klice-2: špatná práva klíče → varování, po opravě přihlášení
$lvl = lab57_level('klice-2');
$ws = lab57_build_world($lvl, 'sima|k2', ['CODE' => 'EDU-K2K2-K2K2'], SIMA_NOW);
[$o, $e, $x] = sima_run($ws, "ssh student@intranet.skola.test 'cat kod.txt'");
sima_check('ssh:bad-perms-warns', str_contains($e, 'UNPROTECTED PRIVATE KEY FILE') && str_contains($e, 'are too open'), $e);
$tips = $ws->tips;
sima_check('ssh:bad-perms-tip', (bool)array_filter($tips, static fn(string $t): bool => str_contains($t, 'chmod 600')), implode(' | ', $tips));
sima_run($ws, 'chmod 700 ~/.ssh');
sima_run($ws, 'chmod 600 ~/.ssh/id_ed25519');
[$o, $e, $x] = sima_run($ws, "ssh student@intranet.skola.test 'cat kod.txt'");
sima_check('ssh:key-auth-after-fix', $x === 0 && trim($o) === 'EDU-K2K2-K2K2', "exit=$x out=" . var_export($o, true));

// změněný otisk hosta
$lvl = lab57_level('klice-6');
$ws = lab57_build_world($lvl, 'sima|k6', ['CODE' => 'EDU-K6K6-K6K6'], SIMA_NOW);
[$o, $e, $x] = sima_run($ws, "ssh student@intranet.skola.test 'cat kod.txt'");
sima_check('ssh:host-key-changed', $x === 255 && str_contains($e, 'REMOTE HOST IDENTIFICATION HAS CHANGED'), $e);
[$o, $e, $x] = sima_run($ws, 'ssh-keygen -R intranet.skola.test');
sima_check('ssh:known-hosts-remove', $x === 0);
[$o, $e, $x] = sima_run($ws, "ssh student@intranet.skola.test 'cat kod.txt'");
sima_check('ssh:reconnect-after-R', $x === 0 && trim($o) === 'EDU-K6K6-K6K6', "exit=$x out=" . var_export($o, true));

// ssh-copy-id + scp přes úroveň klice-3 / klice-4
$lvl = lab57_level('klice-3');
$ws = lab57_build_world($lvl, 'sima|k3', ['CODE' => 'EDU-K3K3-K3K3'], SIMA_NOW);
sima_run($ws, "ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ''");
[$o, $e, $x] = sima_run($ws, 'ssh-copy-id student@intranet.skola.test');
sima_check('ssh:copy-id', $x === 0 && str_contains($o, 'Number of key(s) added: 1'), "exit=$x out=$o");
[$o, $e, $x] = sima_run($ws, "ssh student@intranet.skola.test 'cat kod.txt'");
sima_check('ssh:login-after-copy-id', $x === 0 && trim($o) === 'EDU-K3K3-K3K3', "exit=$x out=" . var_export($o, true));

$lvl = lab57_level('klice-4');
$ws = lab57_build_world($lvl, 'sima|k4', ['CODE' => 'EDU-K4K4-K4K4'], SIMA_NOW);
[$o, $e, $x] = sima_run($ws, 'scp student@intranet.skola.test:tajne/kod.txt .');
sima_check('ssh:scp-download', $x === 0 && $ws->fs->isFile($ws->home() . '/kod.txt') && trim((string)$ws->fs->get($ws->home() . '/kod.txt')['c']) === 'EDU-K4K4-K4K4', "exit=$x");

// ===========================================================================
// 3) Cron
// ===========================================================================

$w = sima_world('cron');
[$o, $e, $x] = sima_run($w, 'crontab -l');
sima_check('cron:list-empty', $x === 1 && str_contains($e, 'no crontab for student'), "exit=$x err=$e");
sima_run($w, "printf '*/10 * * * * echo tik >> ~/tik.log\n' > plan.txt");
[$o, $e, $x] = sima_run($w, 'crontab plan.txt');
sima_check('cron:install', $x === 0);
[$o] = sima_run($w, 'crontab -l');
sima_check('cron:list', str_contains($o, '*/10 * * * * echo tik'), $o);
sima_run($w, "printf 'chybny radek\n' > bad.txt");
[$o, $e, $x] = sima_run($w, 'crontab bad.txt');
sima_check('cron:validate-error', $x === 1 && str_contains($e, "can't install"), "exit=$x err=$e");

// timewarp spustí úlohu a zapíše do syslogu
$wt = lab57_build_world(lab57_level('cron-2'), 'sima|c2', ['CODE' => 'EDU-C2C2-C2C2'], SIMA_NOW);
[$o, $e, $x] = sima_run($wt, 'timewarp +10m');
sima_check('cron:timewarp-runs', $x === 0 && str_contains($o, 'Proběhlo naplánovaných úloh'), $o);
[$o] = sima_run($wt, 'cat /tmp/kopirka.log');
sima_check('cron:job-effect', trim($o) === 'EDU-C2C2-C2C2', var_export($o, true));
[$o, $e, $x] = sima_run($wt, 'sudo grep CRON /var/log/syslog');
sima_check('cron:syslog-line', str_contains($o, 'CRON') && str_contains($o, 'kopirka.sh'), $o);
[$o] = sima_run($wt, 'journalctl -u cron -n 20');
sima_check('cron:journalctl', str_contains($o, 'kopirka.sh'), $o);
[$o] = sima_run($wt, 'date +%H:%M');
sima_check('cron:clock-advanced', trim($o) === date('H:i', SIMA_NOW + 600), trim($o) . ' vs ' . date('H:i', SIMA_NOW + 600));

// run-parts nespadne na prázdné složce
$wr = lab57_build_world(lab57_level('cron-1'), 'sima|c1', ['CODE' => 'EDU-C1C1-C1C1'], SIMA_NOW);
[$o, $e, $x] = sima_run($wr, 'run-parts --report /etc/cron.hourly');
sima_check('cron:run-parts-empty', $x === 0 && trim($o . $e) === '', "exit=$x out=$o err=$e");

// ===========================================================================
// 4) Uživatelé a práva
// ===========================================================================

$w = sima_world('usr');
[$o, $e, $x] = sima_run($w, 'useradd -m novak');
sima_check('usr:useradd-needs-root', $x !== 0 && str_contains($e, 'Permission denied'), "exit=$x err=$e");
[$o, $e, $x] = sima_run($w, 'sudo useradd -m -s /bin/bash -c "Jan Novak" novak');
sima_check('usr:useradd', $x === 0 && isset($w->users['novak']) && $w->fs->isDir('/home/novak'));
[$o] = sima_run($w, 'getent passwd novak');
sima_check('usr:getent-passwd', preg_match('#^novak:x:\d+:\d+:Jan Novak:/home/novak:/bin/bash$#', trim($o)) === 1, $o);
sima_run($w, 'sudo groupadd projekt');
sima_run($w, 'sudo usermod -aG projekt novak');
[$o] = sima_run($w, 'getent group projekt');
sima_check('usr:usermod-aG', str_contains($o, 'novak'), $o);
[$o] = sima_run($w, 'id novak');
sima_check('usr:id-shows-groups', str_contains($o, '(projekt)') && str_contains($o, '(novak)'), $o);
[$o, $e, $x] = sima_run($w, 'echo "novak:Heslo12345" | sudo chpasswd');
sima_check('usr:chpasswd', $x === 0 && ($w->users['novak']['pw'] ?? null) === true);

// /etc/shadow jen pro root, fiktivní hash
[$o, $e, $x] = sima_run($w, 'cat /etc/shadow');
sima_check('usr:shadow-root-only', $x !== 0 && str_contains($e, 'Permission denied'), "exit=$x");
[$o] = sima_run($w, 'sudo cat /etc/shadow');
sima_check('usr:shadow-fictional-hash', str_contains($o, 'novak:$6$lab$'), $o);

// umask ovlivní touch
$w2 = sima_world('umask');
sima_run($w2, 'umask 077');
sima_run($w2, 'touch soukromy.txt');
sima_check('usr:umask-touch', ((int)($w2->fs->get($w2->home() . '/soukromy.txt')['m'] ?? 0) & 07777) === 0600, lab57_octal((int)($w2->fs->get($w2->home() . '/soukromy.txt')['m'] ?? 0)));
[$o] = sima_run($w2, 'umask');
sima_check('usr:umask-print', trim($o) === '0077', $o);

// setgid dědění (přes úroveň prava-3)
$wg = lab57_build_world(lab57_level('prava-3'), 'sima|p3', ['CODE' => 'X'], SIMA_NOW);
sima_run($wg, 'umask 002');
sima_run($wg, 'touch /srv/tym/plan.txt');
$node = $wg->fs->get('/srv/tym/plan.txt');
sima_check('usr:setgid-inherit-group', $node !== null && (string)($node['g'] ?? '') === 'vyvoj', var_export($node['g'] ?? null, true));
sima_check('usr:setgid-group-writable', $node !== null && ((int)$node['m'] & 020) !== 0);
[$o] = sima_run($wg, 'ls -ld /srv/tym');
sima_check('usr:ls-shows-setgid', str_starts_with(trim($o), 'drwxrwsr-x'), $o);
[$o] = sima_run(sima_world('man'), 'man ssh');
sima_check('manual:ssh-marked-in-lab', !str_contains($o, 'nespouští') && str_contains($o, 'RYCHLÉ PŘÍKLADY'), $o);
[$o] = sima_run(sima_world('help'), 'help');
sima_check('manual:help-lists-new', str_contains($o, 'ssh-keygen') && str_contains($o, 'useradd') && str_contains($o, 'timewarp'), $o);

// sudo -l ze sudoers (přes úroveň prava-5)
$wp = lab57_build_world(lab57_level('prava-5'), 'sima|p5', ['CODE' => 'X'], SIMA_NOW);
[$o, $e, $x] = sima_run($wp, 'sudo -l');
sima_check('usr:sudo-l-from-sudoers', str_contains($o, 'NOPASSWD') && str_contains($o, 'systemctl restart'), $o);
[$o, $e, $x] = sima_run($wp, 'sudo visudo -c');
sima_check('usr:visudo-ok', $x === 0 && str_contains($o, '/etc/sudoers: parsed OK'), "exit=$x out=$o");

// visudo najde chybu
$wbad = lab57_build_world(lab57_level('prava-5'), 'sima|p5bad', ['CODE' => 'X'], SIMA_NOW);
sima_run($wbad, 'echo "spatny radek bez rovnitka" | sudo tee /etc/sudoers.d/chyba');
[$o, $e, $x] = sima_run($wbad, 'sudo visudo -c');
sima_check('usr:visudo-detects-error', $x === 1 && str_contains($e, 'syntax error'), "exit=$x err=$e");

// su vysvětlí neinteraktivitu
[$o, $e, $x] = sima_run($w, 'su root');
sima_check('usr:su-explains', $x === 1 && str_contains($e, 'Authentication failure'), "exit=$x");

// chage -l
[$o, $e, $x] = sima_run($w, 'sudo chage -l novak');
sima_check('usr:chage-l', $x === 0 && str_contains($o, 'Password expires'), $o);

// zamčení účtu
sima_run($w, 'sudo usermod -L novak');
sima_check('usr:usermod-lock', ($w->users['novak']['pw'] ?? null) === 'locked');
[$o] = sima_run($w, 'sudo cat /etc/shadow');
sima_check('usr:shadow-locked', (bool)preg_match('/^novak:!/m', $o), $o);

// ===========================================================================
// 5) Persistence mezi požadavky (ext/hodiny, uživatelé)
// ===========================================================================

// hodiny (ext clock_offset) přežijí do dalšího požadavku
$resp = sima_session('cron-1', 'timewarp +2h', 'zak-clock');
sima_check('persist:timewarp-ok', str_contains(sima_sess_out($resp), 'posunut'), sima_sess_out($resp));
$resp = sima_session('cron-1', 'date +%s', 'zak-clock');
sima_check('persist:clock-offset', trim(sima_sess_out($resp, 1)) === (string)(SIMA_NOW + 7200), trim(sima_sess_out($resp, 1)) . ' vs ' . (SIMA_NOW + 7200));

// uživatelé přežijí do dalšího požadavku
$resp = sima_session('prava-1', 'sudo useradd -m dana', 'zak-users');
$resp = sima_session('prava-1', 'getent passwd dana', 'zak-users');
sima_check('persist:users', str_contains(sima_sess_out($resp, 1), 'dana:x:'), sima_sess_out($resp, 1));

// ===========================================================================
// 6) Cizí / špatný kód odmítnut; determinismus kódu
// ===========================================================================

// archivy-1 = první (odemčená) úroveň balíčku, typ code
$codeA = lab57_code('class_3a', 'zak-a', 'practice', 'archivy-1');
$codeB = lab57_code('class_3a', 'zak-b', 'practice', 'archivy-1');
sima_check('code:deterministic', $codeA === lab57_code('class_3a', 'zak-a', 'practice', 'archivy-1') && $codeA !== $codeB);
$resp = sima_session('archivy-1', 'submit ' . $codeB, 'zak-a');
sima_check('code:foreign-rejected', empty($resp['solved']) && str_contains(sima_sess_out($resp, 1), 'někomu jinému'), json_encode($resp, JSON_UNESCAPED_UNICODE));
$resp = sima_session('archivy-1', 'submit EDU-XXXX-XXXX', 'zak-a');
sima_check('code:wrong-rejected', empty($resp['solved']) && str_contains(sima_sess_out($resp, 1), 'nesouhlasí'), sima_sess_out($resp, 1));
$resp = sima_session('archivy-1', 'submit ' . $codeA, 'zak-a');
sima_check('code:own-accepted', !empty($resp['solved']), json_encode($resp, JSON_UNESCAPED_UNICODE));
// pack s omezením tříd odmítne jinou třídu
$resp = sima_session('klice-1', 'ls', 'zak-a', 'class_1a');
sima_check('pack:class-restricted', empty($resp['ok']) && str_contains((string)($resp['error'] ?? ''), 'není pro tvou třídu'), json_encode($resp, JSON_UNESCAPED_UNICODE));

// ===========================================================================
// 7) Řešitelnost všech vlastních úrovní na 5 semínkách
// ===========================================================================

$myPacks = ['klice', 'archivy', 'cron', 'prava'];
$unsolved = [];
$hidden = [];
$levelCount = 0;
foreach (lab57_levels() as $id => $level) {
    if (!in_array($level['pack'], $myPacks, true)) continue;
    $levelCount++;
    for ($s = 1; $s <= 5; $s++) {
        $code = 'EDU-' . $s . 'ABC-' . $s . 'DEF';
        $res = lab58_try_solution($level, 'sima-solve|' . $id . '|' . $s, SIMA_NOW, $code);
        if (!$res['solved']) { $unsolved[] = $id . '#' . $s; break; }
        // Kód musí být opravdu vidět ve výstupu kroků řešení (ne jen „odevzdán naslepo").
        if ($level['type'] === 'code') {
            $seen = false;
            foreach ($res['transcript'] as $t) if (!str_starts_with($t['line'], 'submit') && str_contains($t['out'], $code)) { $seen = true; break; }
            if (!$seen) { $hidden[] = $id . '#' . $s; break; }
        }
    }
}
sima_check('levels:count', $levelCount === 24, 'nalezeno ' . $levelCount . ' úrovní (čekáno 24)');
sima_check('levels:solvable-5-seeds', $unsolved === [], implode(', ', $unsolved));
sima_check('levels:code-discoverable', $hidden === [], implode(', ', $hidden));
$packs = lab58_packs();
sima_check('packs:registered', isset($packs['klice'], $packs['archivy'], $packs['cron'], $packs['prava']) && $packs['archivy']['classes'] === null && $packs['klice']['classes'] === ['class_3a', 'class_4a']);

// ===========================================================================
// Souhrn
// ===========================================================================

$warnings = trim((string)@file_get_contents($TMP . '/php_errors.log'));
$ok = $GLOBALS['sima']['failed'] === 0;
echo "\n" . ($ok ? 'V58_SIM_A_AUDIT_OK' : 'V58_SIM_A_AUDIT_FAILED') . ' checks=' . $GLOBALS['sima']['checks'] . ' failed=' . $GLOBALS['sima']['failed'] . "\n";
exit($ok ? 0 : 1);
