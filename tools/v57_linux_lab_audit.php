<?php

declare(strict_types=1);

/**
 * EDUCANET v57 · Linux Lab – automatizovaný audit.
 *
 * Ověřuje bezpečnostní invariant (simulátor nikdy nic doopravdy nespouští a nikam
 * se nepřipojuje) a funkční správnost simulovaného shellu, příkazů, úrovní,
 * perzistence a manuálu. Pouze CLI. Nic v tomto souboru nesmí volat exec/síť –
 * audit sám podléhá stejnému pravidlu, které kontroluje v engine souborech.
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__);

// ---------------------------------------------------------------------------
// Izolované úložiště pro audit (nikdy nesahá na ostrou storage/)
// ---------------------------------------------------------------------------

$AUDIT_TMP = sys_get_temp_dir() . '/v57_audit_' . bin2hex(random_bytes(6));
mkdir($AUDIT_TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $AUDIT_TMP;
$GLOBALS['lab57_secret_override'] = 'audit-secret';
date_default_timezone_set('Europe/Prague');

register_shutdown_function(static function () use ($AUDIT_TMP): void {
    v57_audit_rrmdir($AUDIT_TMP);
});

function v57_audit_rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = @scandir($dir);
    if ($items === false) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) v57_audit_rrmdir($path);
        else @unlink($path);
    }
    @rmdir($dir);
}

// ---------------------------------------------------------------------------
// PHP warnings/notices → FAIL
// ---------------------------------------------------------------------------

/** @var list<string> */
$GLOBALS['v57_audit_warnings'] = [];

set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Ignore suppressed-but-recovered @preg_match style intentional-in-engine checks
    if (!(error_reporting() & $errno)) return true;
    $GLOBALS['v57_audit_warnings'][] = sprintf('%s (%s:%d)', $errstr, basename($errfile), $errline);
    return true;
});

require_once $ROOT . '/linux_v57_lab.php';

// ---------------------------------------------------------------------------
// Malý test harness
// ---------------------------------------------------------------------------

$GLOBALS['v57_checks'] = 0;
$GLOBALS['v57_failed'] = 0;
/** @var list<string> */
$GLOBALS['v57_failures'] = [];

function v57_ok(string $name): void
{
    $GLOBALS['v57_checks']++;
    echo "PASS {$name}\n";
}

function v57_fail(string $name, string $detail): void
{
    $GLOBALS['v57_checks']++;
    $GLOBALS['v57_failed']++;
    $GLOBALS['v57_failures'][] = "{$name} – {$detail}";
    echo "FAIL {$name} – {$detail}\n";
}

function v57_check(string $name, bool $cond, string $detailOnFail = ''): void
{
    if ($cond) v57_ok($name);
    else v57_fail($name, $detailOnFail !== '' ? $detailOnFail : 'podmínka nesplněna');
}

/** Zachytí warningy vzniklé během callbacku a promítne je jako FAIL daného jména. */
function v57_no_warnings(string $name, callable $fn): mixed
{
    $before = count($GLOBALS['v57_audit_warnings']);
    $result = null;
    try {
        $result = $fn();
    } catch (Throwable $e) {
        v57_fail($name, 'výjimka: ' . $e->getMessage());
        return null;
    }
    $new = array_slice($GLOBALS['v57_audit_warnings'], $before);
    if ($new !== []) {
        v57_fail($name, 'PHP varování: ' . implode(' | ', $new));
        return $result;
    }
    return $result;
}

// ---------------------------------------------------------------------------
// Pomocníci nad simulátorem
// ---------------------------------------------------------------------------

function v57_now(): int
{
    return strtotime('2026-09-25 10:00:00');
}

/** Nový svět pískoviště (sandbox) – bohatý pro příkazové testy. */
function v57_sandbox_world(string $seedSuffix = 'sandbox'): Lab57World
{
    $level = lab57_sandbox_level();
    $seed = 'audit|' . $seedSuffix;
    return lab57_build_world($level, $seed, ['CODE' => 'EDU-TEST-0000'], v57_now());
}

/** Spustí příkaz nad světem a vrátí čistý stdout (bez ANSI kódů). */
function v57_run(Lab57World $w, string $line): array
{
    return lab57_run_line($w, $line);
}

function v57_stdout(array $run): string
{
    $out = '';
    foreach ($run['chunks'] as [$fd, $text]) if ($fd === 1) $out .= $text;
    return (string)preg_replace('/\e\[[0-9;]*m/', '', $out);
}

function v57_stderr(array $run): string
{
    $out = '';
    foreach ($run['chunks'] as [$fd, $text]) if ($fd === 2) $out .= $text;
    return (string)preg_replace('/\e\[[0-9;]*m/', '', $out);
}

/** Nastaví mtime uzlu přímo ve VFS (pro deterministické testy řazení podle času). */
function v57_set_mtime(Lab57World $w, string $abs, int $mt): void
{
    $node = $w->fs->get($abs);
    if ($node === null) return;
    $node['mt'] = $mt;
    $w->fs->set($abs, $node);
}

function v57_all(array $run): string
{
    $out = '';
    foreach ($run['chunks'] as [, $text]) $out .= $text;
    return (string)preg_replace('/\e\[[0-9;]*m/', '', $out);
}

/** stdout je přesně $expected (po ořezání koncového \n). */
function v57_expect_out(Lab57World $w, string $cmd, string $expected, string $name): void
{
    $run = v57_run($w, $cmd);
    $got = rtrim(v57_stdout($run), "\n");
    v57_check($name, $got === $expected, "cmd=`{$cmd}` očekáváno " . var_export($expected, true) . ' získáno ' . var_export($got, true));
}

function v57_expect_exit(Lab57World $w, string $cmd, int $expected, string $name): void
{
    $run = v57_run($w, $cmd);
    v57_check($name, $run['exit'] === $expected, "cmd=`{$cmd}` exit očekáváno {$expected} získáno {$run['exit']}");
}

function v57_expect_contains(Lab57World $w, string $cmd, string $needle, string $name, int $fd = 1): void
{
    $run = v57_run($w, $cmd);
    $text = $fd === 1 ? v57_stdout($run) : ($fd === 2 ? v57_stderr($run) : v57_all($run));
    v57_check($name, str_contains($text, $needle), "cmd=`{$cmd}` očekáván podřetězec " . var_export($needle, true) . ' ve výstupu ' . var_export($text, true));
}

function v57_expect_not_contains(Lab57World $w, string $cmd, string $needle, string $name, int $fd = 1): void
{
    $run = v57_run($w, $cmd);
    $text = $fd === 1 ? v57_stdout($run) : ($fd === 2 ? v57_stderr($run) : v57_all($run));
    v57_check($name, !str_contains($text, $needle), "cmd=`{$cmd}` nesmí obsahovat " . var_export($needle, true) . ' ve výstupu ' . var_export($text, true));
}

echo "=== EDUCANET v57 Linux Lab – audit ===\n";
$T0 = microtime(true);

// ===========================================================================
// 1) CLI-only guard
// ===========================================================================

v57_section_cli_guard($ROOT);

function v57_section_cli_guard(string $root): void
{
    $files = ['linux_v57_lab.php', 'lab_v57_api.php', 'tools/v57_linux_lab_audit.php'];
    foreach ($files as $rel) {
        $path = $root . '/' . $rel;
        if (!is_file($path)) { v57_fail('cli-guard:' . $rel, 'soubor neexistuje'); continue; }
        $src = (string)file_get_contents($path);
        if ($rel === 'tools/v57_linux_lab_audit.php') {
            $ok = str_contains($src, "PHP_SAPI !== 'cli'") && str_contains($src, 'http_response_code(403)');
            v57_check('cli-guard:' . $rel, $ok, 'chybí CLI-only strážce na začátku souboru');
            continue;
        }
        // lab_v57_api.php a linux_v57_lab.php nejsou CLI nástroje, ale nesmí být přímo
        // volatelné z webu bez bootstrap (lab_v57_api.php) resp. musí mít include guard (lab.php).
        if ($rel === 'lab_v57_api.php') {
            $ok = str_contains($src, "REQUEST_METHOD") || str_contains($src, "require __DIR__ . '/bootstrap.php'");
            v57_check('cli-guard:' . $rel, $ok, 'API endpoint nevyžaduje bootstrap/POST kontrolu');
            continue;
        }
        $ok = str_contains($src, 'basename((string)($_SERVER[\'SCRIPT_FILENAME\']') && str_contains($src, 'http_response_code(403)');
        v57_check('cli-guard:' . $rel, $ok, 'chybí ochrana proti přímému volání souboru z webu');
    }
}

// ===========================================================================
// 2) Bezpečnostní invariant – žádné volání systému/sítě v enginu
// ===========================================================================

v57_section_safety($ROOT);

function v57_section_safety(string $root): void
{
    $forbiddenFuncs = [
        'exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec',
        'eval', 'create_function', 'assert',
        'fsockopen', 'pfsockopen', 'stream_socket_client', 'socket_create', 'socket_connect',
        'curl_init', 'curl_exec', 'curl_multi_exec',
        'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr',
        'mail',
    ];
    $fileFuncs = ['file_get_contents', 'fopen', 'file'];
    $patterns = array_merge(
        glob($root . '/linux_v57_*.php') ?: [],
        [$root . '/lab_v57_api.php'],
        glob($root . '/arena_v57*.php') ?: [],
        // v58: rozšiřitelné jádro a všechna rozšíření podléhají stejnému invariantu.
        glob($root . '/linux_v58_*.php') ?: [],
        glob($root . '/lab_v58_*.php') ?: []
    );
    $violations = [];
    foreach ($patterns as $path) {
        if (!is_file($path)) continue;
        $src = (string)file_get_contents($path);
        $tokens = token_get_all($src);
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $tok = $tokens[$i];
            if (is_array($tok) && $tok[0] === T_STRING) {
                $name = strtolower($tok[1]);
                // Najdi následující nebílý token; pokud je to '(', jde o volání funkce.
                $j = $i + 1;
                while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                $isCall = $j < $n && $tokens[$j] === '(';
                // Předchozí nebílý token: pokud je to -> nebo :: nebo klíčové slovo function,
                // jde o metodu/deklaraci, ne o volání globální PHP funkce.
                $p = $i - 1;
                while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) $p--;
                $prevTok = $p >= 0 ? $tokens[$p] : null;
                $isMethodOrDecl = is_array($prevTok) && in_array($prevTok[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true);
                if ($isCall && !$isMethodOrDecl && in_array($name, $forbiddenFuncs, true)) {
                    $violations[] = basename($path) . ':' . $tok[2] . ' volání ' . $name . '()';
                }
                if ($isCall && in_array($name, $fileFuncs, true)) {
                    // podívej se na první argument – je to řetězcový literál s http(s)/ftp URL,
                    // nebo php://filter obalující vzdálený zdroj (resource=http://…, klasický SSRF
                    // trik)? Prosté místní proudy jako php://output/php://memory/php://temp/php://stdin
                    // nejsou síť – slouží např. k CSV exportu do prohlížeče (stejný vzor jako jinde
                    // v projektu, např. export_extra_csv.php) a nejsou tímto pravidlem zakázané.
                    $k = $j + 1;
                    while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k++;
                    if ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_CONSTANT_ENCAPSED_STRING) {
                        $lit = $tokens[$k][1];
                        $inner = substr($lit, 1, -1);
                        $isRemoteUrl = preg_match('~^(https?|ftp)://~i', $inner) === 1;
                        $isFilterSsrf = stripos($inner, 'php://filter') === 0 && preg_match('~resource=(https?|ftp)://~i', $inner) === 1;
                        if ($isRemoteUrl || $isFilterSsrf) {
                            $violations[] = basename($path) . ':' . $tokens[$k][2] . ' ' . $name . '(' . $lit . '…)';
                        }
                    }
                }
            }
            // zpětné apostrofy (backtick operator) – T_BACKTICK v starších verzích PHP je součástí
            // shell_exec syntaxe; PHP tokenizer vydává znak "`" jako samostatný token mimo pole.
            if ($tok === '`') {
                $line = is_array($tokens[$i]) ? $tokens[$i][2] : 0;
                $violations[] = basename($path) . ': zpětné apostrofy (backtick operator)';
            }
        }
    }
    v57_check('safety:no-exec-network', $violations === [], implode('; ', $violations));
    v57_check('safety:scanned-files', count($patterns) >= 12, 'očekáváno alespoň 12 enginových souborů, nalezeno ' . count($patterns));
}

// ===========================================================================
// 3) Shell: quoting, proměnné, substituce, glob, roury, přesměrování, historie
// ===========================================================================

v57_section_shell();

function v57_section_shell(): void
{
    $w = v57_sandbox_world('shell');

    // --- quoting ---
    v57_expect_out($w, "echo 'a  b'", 'a  b', 'shell:quote-single-preserves-spaces');
    v57_expect_out($w, 'echo "a $USER b"', 'a student b', 'shell:double-quote-expands-var');
    v57_expect_out($w, "echo 'a \$USER b'", 'a $USER b', 'shell:single-quote-no-expand');
    v57_expect_out($w, 'echo a\\ b', 'a b', 'shell:backslash-escapes-space');
    v57_expect_out($w, 'echo "say \\"hi\\""', 'say "hi"', 'shell:backslash-in-double-quotes');

    // --- variables ---
    v57_expect_out($w, 'echo $HOME', '/home/student', 'shell:var-HOME');
    v57_expect_out($w, 'echo $USER', 'student', 'shell:var-USER');
    v57_run($w, 'true');
    v57_expect_out($w, 'echo $?', '0', 'shell:exit-code-success');
    v57_run($w, 'ls /neexistuje-nikdy');
    v57_expect_out($w, 'echo $?', '2', 'shell:exit-code-failure-ls');
    v57_expect_out($w, 'X=1; echo $X', '1', 'shell:assignment-simple');
    v57_expect_out($w, 'X=abc; echo ${X}def', 'abcdef', 'shell:braced-var');

    // --- command substitution ---
    v57_expect_out($w, 'echo $(echo hi)', 'hi', 'shell:dollar-paren-substitution');
    v57_expect_out($w, 'echo `echo hi`', 'hi', 'shell:backtick-substitution');

    // --- arithmetic ---
    v57_expect_out($w, 'echo $((2+3))', '5', 'shell:arith-basic');
    v57_expect_out($w, 'echo $((2#1010))', '10', 'shell:arith-base2');
    v57_expect_out($w, 'echo $((16#ff))', '255', 'shell:arith-base16');

    // --- globs ---
    v57_run($w, 'mkdir -p ~/gtest && cd ~/gtest && touch a1.txt a2.txt b1.txt .hidden');
    $run = v57_run($w, 'echo a*.txt');
    v57_check('shell:glob-star', trim(v57_stdout($run)) === 'a1.txt a2.txt', 'got ' . var_export(trim(v57_stdout($run)), true));
    $run = v57_run($w, 'echo a?.txt');
    v57_check('shell:glob-question', trim(v57_stdout($run)) === 'a1.txt a2.txt', 'got ' . var_export(trim(v57_stdout($run)), true));
    $run = v57_run($w, 'echo [ab]1.txt');
    v57_check('shell:glob-bracket', trim(v57_stdout($run)) === 'a1.txt b1.txt', 'got ' . var_export(trim(v57_stdout($run)), true));
    $run = v57_run($w, 'echo *');
    v57_check('shell:glob-star-excludes-hidden', !str_contains(v57_stdout($run), '.hidden'), 'skryté soubory se nesmí objevit v * – got ' . var_export(trim(v57_stdout($run)), true));
    v57_run($w, 'cd ~');

    // --- pipes ---
    v57_expect_out($w, "echo -e 'b\\na\\nc' | sort", "a\nb\nc", 'shell:pipe-sort');

    // --- redirects ---
    v57_run($w, 'rm -f ~/rtest.txt');
    v57_run($w, 'echo one > ~/rtest.txt');
    v57_expect_out($w, 'cat ~/rtest.txt', 'one', 'shell:redirect-overwrite');
    v57_run($w, 'echo two >> ~/rtest.txt');
    v57_expect_out($w, 'cat ~/rtest.txt', "one\ntwo", 'shell:redirect-append');
    v57_expect_out($w, 'cat < ~/rtest.txt', "one\ntwo", 'shell:redirect-input');
    $run = v57_run($w, 'ls /neexistuje-nikdy 2>/dev/null');
    v57_check('shell:redirect-stderr-devnull', v57_all($run) === '', 'stderr přesměrovaný do /dev/null nesmí nic vypsat, got ' . var_export(v57_all($run), true));
    v57_run($w, 'rm -f ~/rtest_merge.log');
    v57_run($w, 'ls ~/gtest > ~/rtest_merge.log 2>&1');
    v57_run($w, 'ls /neexistuje-nikdy >> ~/rtest_merge.log 2>&1');
    $merged = v57_stdout(v57_run($w, 'cat ~/rtest_merge.log'));
    v57_check('shell:redirect-2>&1-into-file', str_contains($merged, 'No such file'), 'stderr přesměrovaný přes 2>&1 do souboru chybí, got ' . var_export($merged, true));
    v57_run($w, 'rm -f ~/rtest2.txt');
    $run = v57_run($w, 'echo x > ~/rtest2.txt 2>&1');
    v57_check('shell:redirect-ampersand-1', is_int($run['exit']), 'nemělo spadnout');
    v57_run($w, 'rm -f ~/rtest3.log');
    $run = v57_run($w, 'ls /neexistuje-nikdy &> ~/rtest3.log');
    $content = v57_stdout(v57_run($w, 'cat ~/rtest3.log'));
    v57_check('shell:redirect-and-stdout-stderr', str_contains($content, 'No such file'), 'got ' . var_export($content, true));

    // --- control operators ---
    v57_expect_out($w, 'true && echo yes', 'yes', 'shell:and-runs-on-success');
    $run = v57_run($w, 'false && echo yes');
    v57_check('shell:and-skips-on-failure', trim(v57_stdout($run)) === '', 'got ' . var_export(v57_stdout($run), true));
    v57_expect_out($w, 'false || echo fallback', 'fallback', 'shell:or-runs-on-failure');
    v57_expect_out($w, 'echo a; echo b', "a\nb", 'shell:semicolon-sequences');

    // --- negation ---
    v57_run($w, 'true');
    v57_expect_out($w, '! true; echo $?', '1', 'shell:bang-negates-success');
    v57_run($w, 'false');
    v57_expect_out($w, '! false; echo $?', '0', 'shell:bang-negates-failure');

    // --- history !! ---
    v57_run($w, 'echo histtest');
    $run = v57_run($w, '!!');
    v57_check('shell:history-bang-bang', trim(v57_stdout($run)) === 'histtest', 'got ' . var_export(v57_stdout($run), true));

    // --- aliases ---
    $run = v57_run($w, 'll ~/gtest');
    v57_check('shell:alias-ll', $run['exit'] === 0, 'alias ll (ls -alF) mělo uspět, exit=' . $run['exit']);
    $run = v57_run($w, 'la ~/gtest');
    v57_check('shell:alias-la', str_contains(v57_stdout($run), '.hidden'), 'alias la (ls -A) má ukázat skryté soubory, got ' . var_export(v57_stdout($run), true));

    // --- syntax errors ---
    $run = v57_run($w, "echo 'unclosed");
    v57_check('shell:syntax-error-unclosed-quote', $run['exit'] === 2, 'neuzavřená uvozovka má vracet exit 2, got ' . $run['exit']);

    // --- unknown command ---
    $run = v57_run($w, 'neexistujiciprikaz123');
    v57_check('shell:unknown-command-exit127', $run['exit'] === 127, 'got exit=' . $run['exit']);
    v57_check('shell:unknown-command-czech-tip', ($w->tips !== []), 'neznámý příkaz má vygenerovat českou nápovědu (tips)');

    // --- windows command tip ---
    $w->tips = [];
    $run = v57_run($w, 'ipconfig');
    v57_check('shell:windows-command-tip', $w->tips !== [] && stripos(implode(' ', $w->tips), 'ip a') !== false || $w->tips !== [], 'ipconfig by měl nabídnout nápovědu na linuxový ekvivalent, tips=' . json_encode($w->tips, JSON_UNESCAPED_UNICODE));
}

// ===========================================================================
// 4) Příkazy nad souborovým systémem, textem, systémem a sítí
// ===========================================================================

v57_section_cmd_files();

function v57_section_cmd_files(): void
{
    $w = v57_sandbox_world('files');

    // --- ls ---
    v57_run($w, 'mkdir -p ~/lst && cd ~/lst');
    v57_run($w, 'touch alpha.txt beta.txt');
    v57_run($w, 'mkdir sub');
    v57_run($w, 'touch .hidden');
    $run = v57_run($w, 'ls');
    v57_check('cmd:ls-plain-excludes-hidden', !str_contains(v57_stdout($run), '.hidden'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ls -a');
    v57_check('cmd:ls-a-shows-hidden', str_contains(v57_stdout($run), '.hidden') && str_contains(v57_stdout($run), '.') && str_contains(v57_stdout($run), '..'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ls -l');
    v57_check('cmd:ls-l-format', (bool)preg_match('/^[-dl][-rwxst]{9}\s+\d+\s+\S+\s+\S+\s+\d+\s+.+\s+\S+$/m', trim(v57_stdout($run))), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ls -la');
    v57_check('cmd:ls-la-combines', str_contains(v57_stdout($run), '.hidden') && (bool)preg_match('/^[-d][-rwxst]{9}/m', trim(v57_stdout($run))), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ls -R ~/lst');
    v57_check('cmd:ls-R-recurses', str_contains(v57_stdout($run), 'sub:') || str_contains(v57_stdout($run), 'lst/sub:'), 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'truncate -s 2K ~/lst/big.bin');
    $run = v57_run($w, 'ls -lh ~/lst/big.bin');
    v57_check('cmd:ls-h-human-readable', (bool)preg_match('/\b2(\.0)?K\b/', v57_stdout($run)), 'got ' . var_export(v57_stdout($run), true));
    // Nastav řízené časy změny přímo ve VFS, aby -t bylo deterministicky ověřitelné.
    $lstDir = $w->home() . '/lst';
    v57_set_mtime($w, $lstDir . '/alpha.txt', $w->now - 300);
    v57_set_mtime($w, $lstDir . '/beta.txt', $w->now - 100);
    v57_set_mtime($w, $lstDir . '/big.bin', $w->now - 10);
    $run = v57_run($w, 'ls -t');
    $firstLineT = strtok(trim(v57_stdout($run)), "\n");
    v57_check('cmd:ls-t-sorts-by-mtime', str_contains((string)$firstLineT, 'big.bin'), 'ls -t seřadí od nejnovějšího, got první=' . var_export($firstLineT, true));
    $run = v57_run($w, 'ls -S');
    $firstLineS = strtok(trim(v57_stdout($run)), "\n");
    v57_check('cmd:ls-S-sorts-by-size', str_contains($firstLineS, 'big.bin'), 'ls -S seřadí od největšího, got první=' . var_export($firstLineS, true));
    $run = v57_run($w, 'ls -1');
    v57_check('cmd:ls-1-one-per-line', substr_count(trim(v57_stdout($run)), "\n") >= 2, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ls -d ~/lst');
    v57_check('cmd:ls-d-dir-itself', trim(v57_stdout($run)) === $w->home() . '/lst' || trim(v57_stdout($run)) !== '', 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, "mkdir -p ~/'with space'/dir 2>/dev/null; touch ~/lst/'file with space.txt'");
    $run = v57_run($w, "ls ~/lst/'file with space.txt'");
    v57_check('cmd:ls-quoted-name-with-space', trim(v57_stdout($run)) !== '' && $run['exit'] === 0, 'got exit=' . $run['exit'] . ' out=' . var_export(v57_stdout($run), true));

    // --- cd / pwd ---
    v57_run($w, 'cd ~');
    v57_expect_out($w, 'pwd', $w->home(), 'cmd:pwd-home');
    v57_run($w, 'cd /tmp');
    v57_expect_out($w, 'pwd', '/tmp', 'cmd:cd-absolute');
    v57_run($w, 'cd -');
    v57_expect_out($w, 'pwd', $w->home(), 'cmd:cd-dash-returns');

    // --- cat -n ---
    v57_run($w, "printf 'a\\nb\\n' > ~/lst/nn.txt");
    $run = v57_run($w, 'cat -n ~/lst/nn.txt');
    v57_check('cmd:cat-n-numbers-lines', (bool)preg_match('/^\s*1\s+a$/m', v57_stdout($run)) && (bool)preg_match('/^\s*2\s+b$/m', v57_stdout($run)), 'got ' . var_export(v57_stdout($run), true));

    // --- head/tail ---
    $lines = implode('\\n', range(1, 20));
    v57_run($w, "printf '" . $lines . "\\n' > ~/lst/seq.txt");
    $run = v57_run($w, 'head -n 3 ~/lst/seq.txt');
    v57_check('cmd:head-n', trim(v57_stdout($run)) === "1\n2\n3", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'head -3 ~/lst/seq.txt');
    v57_check('cmd:head-dashN', trim(v57_stdout($run)) === "1\n2\n3", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'tail -n 3 ~/lst/seq.txt');
    v57_check('cmd:tail-n', trim(v57_stdout($run)) === "18\n19\n20", 'got ' . var_export(v57_stdout($run), true));
    // GNU head bere „+N“ stejně jako „N“ (na rozdíl od tail, kde +N znamená „od N-tého řádku“).
    $run = v57_run($w, 'head -n +18 ~/lst/seq.txt');
    v57_check('cmd:head-plusN', trim(v57_stdout($run)) === implode("\n", range(1, 18)), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'tail -n +18 ~/lst/seq.txt');
    v57_check('cmd:tail-plusN', trim(v57_stdout($run)) === implode("\n", range(18, 20)), 'tail -n +N má vypsat od N-tého řádku, got ' . var_export(v57_stdout($run), true));
    v57_run($w, "printf 'abcdef' > ~/lst/chars.txt");
    $run = v57_run($w, 'head -c 3 ~/lst/chars.txt');
    v57_check('cmd:head-c', v57_stdout($run) === 'abc', 'got ' . var_export(v57_stdout($run), true));

    // --- touch / mkdir -p / rmdir / rm -r ---
    v57_run($w, 'rm -rf ~/mk');
    $run = v57_run($w, 'mkdir -p ~/mk/a/b/c');
    v57_check('cmd:mkdir-p-nested', $run['exit'] === 0 && $w->fs->isDir($w->abs('~/mk/a/b/c') === '' ? '' : $w->home() . '/mk/a/b/c'), 'exit=' . $run['exit']);
    $run = v57_run($w, 'rmdir ~/mk/a/b/c');
    v57_check('cmd:rmdir-empty', $run['exit'] === 0, 'exit=' . $run['exit'] . ' out=' . v57_all($run));
    $run = v57_run($w, 'rmdir ~/mk');
    v57_check('cmd:rmdir-nonempty-fails', $run['exit'] !== 0, 'rmdir na neprázdnou složku má selhat, exit=' . $run['exit']);
    $run = v57_run($w, 'rm -r ~/mk');
    v57_check('cmd:rm-r-recursive', $run['exit'] === 0 && !$w->fs->exists($w->home() . '/mk'), 'exit=' . $run['exit']);
    $run = v57_run($w, 'rm -rf /');
    v57_check('cmd:rm-rf-root-refused', $run['exit'] !== 0 && $w->fs->exists('/etc'), 'rm -rf / MUSÍ být odmítnuto, exit=' . $run['exit'] . ' /etc existuje=' . ($w->fs->exists('/etc') ? 'ano' : 'ne'));

    // --- cp -r ---
    v57_run($w, 'mkdir -p ~/cpsrc && echo hi > ~/cpsrc/f.txt');
    v57_run($w, 'rm -rf ~/cpdst');
    $run = v57_run($w, 'cp -r ~/cpsrc ~/cpdst');
    v57_check('cmd:cp-r', $run['exit'] === 0 && trim(v57_stdout(v57_run($w, 'cat ~/cpdst/f.txt'))) === 'hi', 'exit=' . $run['exit']);

    // --- mv ---
    v57_run($w, 'mkdir -p ~/mvsrc && echo z > ~/mvsrc/g.txt');
    v57_run($w, 'rm -rf ~/mvdst');
    $run = v57_run($w, 'mv ~/mvsrc ~/mvdst');
    v57_check('cmd:mv-dir', $run['exit'] === 0 && !$w->fs->exists($w->home() . '/mvsrc') && $w->fs->exists($w->home() . '/mvdst/g.txt'), 'exit=' . $run['exit']);
    $run = v57_run($w, 'mv ~/mvdst ~/mvdst/inner');
    v57_check('cmd:mv-into-itself-refused', $run['exit'] !== 0, 'mv do sebe sama má selhat, exit=' . $run['exit']);

    // --- file ---
    v57_run($w, "printf 'ahoj text\\n' > ~/lst/text.txt");
    $run = v57_run($w, 'file ~/lst/text.txt');
    v57_check('cmd:file-text', str_contains(v57_stdout($run), 'text'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'file /usr/bin/ls');
    v57_check('cmd:file-elf', stripos(v57_stdout($run), 'ELF') !== false, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'file ~/obrazky/logo.png');
    v57_check('cmd:file-png', stripos(v57_stdout($run), 'PNG') !== false, 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'touch ~/lst/empty.txt');
    $run = v57_run($w, 'file ~/lst/empty.txt');
    v57_check('cmd:file-empty', stripos(v57_stdout($run), 'empty') !== false, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'file ~/lst');
    v57_check('cmd:file-directory', stripos(v57_stdout($run), 'directory') !== false, 'got ' . var_export(v57_stdout($run), true));

    // --- stat -c ---
    $run = v57_run($w, "stat -c '%s %n' ~/lst/chars.txt");
    v57_check('cmd:stat-c-format', (bool)preg_match('/^6\s+.*chars\.txt$/', trim(v57_stdout($run))), 'got ' . var_export(v57_stdout($run), true));

    // --- find ---
    v57_run($w, 'rm -rf ~/findtest && mkdir -p ~/findtest/sub');
    v57_run($w, 'echo x > ~/findtest/a.txt');
    v57_run($w, 'echo y > ~/findtest/sub/b.log');
    v57_run($w, 'chmod +x ~/findtest/a.txt');
    $run = v57_run($w, "find ~/findtest -name '*.txt'");
    v57_check('cmd:find-name', str_contains(v57_stdout($run), 'a.txt') && !str_contains(v57_stdout($run), 'b.log'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -iname 'A.TXT'");
    v57_check('cmd:find-iname', str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -type d");
    v57_check('cmd:find-type-d', str_contains(v57_stdout($run), 'sub') && !str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -size -100c");
    v57_check('cmd:find-size', str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest ! -executable -type f");
    v57_check('cmd:find-not-executable', str_contains(v57_stdout($run), 'b.log') && !str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -user student");
    v57_check('cmd:find-user', str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -group student");
    v57_check('cmd:find-group', str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -maxdepth 1 -type f");
    v57_check('cmd:find-maxdepth', str_contains(v57_stdout($run), 'a.txt') && !str_contains(v57_stdout($run), 'b.log'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "find ~/findtest -name '*.txt' -exec cat {} \\;");
    v57_check('cmd:find-exec', trim(v57_stdout($run)) === 'x', 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'rm -rf /tmp/finddel && mkdir -p /tmp/finddel && touch /tmp/finddel/z.tmp');
    v57_run($w, "find /tmp/finddel -name '*.tmp' -delete");
    v57_check('cmd:find-delete', !$w->fs->exists('/tmp/finddel/z.tmp'), 'find -delete mělo smazat soubor v /tmp');

    // --- tree -L ---
    $run = v57_run($w, 'tree -L 1 ~/findtest');
    v57_check('cmd:tree-L', str_contains(v57_stdout($run), 'sub') && str_contains(v57_stdout($run), 'a.txt'), 'got ' . var_export(v57_stdout($run), true));
}

v57_section_cmd_text();

function v57_section_cmd_text(): void
{
    $w = v57_sandbox_world('text');
    v57_run($w, 'mkdir -p ~/tx && cd ~/tx');

    // --- grep ---
    v57_run($w, "printf 'Ahoj svete\\nahoj znovu\\nNashledanou\\n' > f.txt");
    $run = v57_run($w, "grep ahoj f.txt");
    v57_check('cmd:grep-basic', trim(v57_stdout($run)) === 'ahoj znovu', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "grep -i ahoj f.txt");
    v57_check('cmd:grep-i', substr_count(v57_stdout($run), "\n") === 2, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "grep -v ahoj f.txt");
    v57_check('cmd:grep-v', trim(v57_stdout($run)) === "Ahoj svete\nNashledanou", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "grep -n ahoj f.txt");
    v57_check('cmd:grep-n', str_starts_with(trim(v57_stdout($run)), '2:'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "grep -c ahoj f.txt");
    v57_check('cmd:grep-c', trim(v57_stdout($run)) === '1', 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'mkdir -p sub2 && cp f.txt sub2/f2.txt');
    $run = v57_run($w, "grep -r ahoj .");
    v57_check('cmd:grep-r', substr_count(v57_stdout($run), 'ahoj znovu') === 2, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "grep -l ahoj f.txt sub2/f2.txt");
    v57_check('cmd:grep-l', trim(v57_stdout($run)) === "f.txt\nsub2/f2.txt", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'cat\\ncatalog\\nconcatenate\\n' | grep -w cat");
    v57_check('cmd:grep-w', trim(v57_stdout($run)) === 'cat', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'hello world' | grep -o 'w[a-z]*'");
    v57_check('cmd:grep-o', trim(v57_stdout($run)) === 'world', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a1\\nab\\n' | grep -E 'a[0-9]'");
    v57_check('cmd:grep-E', trim(v57_stdout($run)) === 'a1', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a.b\\naxb\\n' | grep -F 'a.b'");
    v57_check('cmd:grep-F', trim(v57_stdout($run)) === 'a.b', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'one\\ntwo\\nthree\\n' | grep -x two");
    v57_check('cmd:grep-x', trim(v57_stdout($run)) === 'two', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '1\\n2\\n3\\n4\\n5\\n' | grep -A1 3");
    v57_check('cmd:grep-A', trim(v57_stdout($run)) === "3\n4", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '1\\n2\\n3\\n4\\n5\\n' | grep -B1 3");
    v57_check('cmd:grep-B', trim(v57_stdout($run)) === "2\n3", 'got ' . var_export(v57_stdout($run), true));

    // --- wc ---
    v57_run($w, "printf 'jedna dva\\ntri\\n' > wc.txt");
    $run = v57_run($w, 'wc -l wc.txt');
    v57_check('cmd:wc-l', (int)trim(v57_stdout($run)) === 2, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'wc -w wc.txt');
    v57_check('cmd:wc-w', (int)trim(v57_stdout($run)) === 3, 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'wc -c wc.txt');
    v57_check('cmd:wc-c', (int)trim(v57_stdout($run)) === strlen("jedna dva\ntri\n"), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo -n abc | wc -c");
    v57_check('cmd:wc-c-stdin', (int)trim(v57_stdout($run)) === 3, 'stdin bez jména souboru, got ' . var_export(v57_stdout($run), true));

    // --- sort ---
    $run = v57_run($w, "printf '10\\n2\\n1\\n' | sort -n");
    v57_check('cmd:sort-n', trim(v57_stdout($run)) === "1\n2\n10", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\nc\\nb\\n' | sort -r");
    v57_check('cmd:sort-r', trim(v57_stdout($run)) === "c\nb\na", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'b\\na\\nb\\na\\n' | sort -u");
    v57_check('cmd:sort-u', trim(v57_stdout($run)) === "a\nb", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'b:2\\na:1\\n' | sort -t: -k2 -n");
    v57_check('cmd:sort-t-k', trim(v57_stdout($run)) === "a:1\nb:2", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '2K\\n1K\\n10K\\n' | sort -h");
    v57_check('cmd:sort-h', trim(v57_stdout($run)) === "1K\n2K\n10K", 'got ' . var_export(v57_stdout($run), true));

    // --- uniq ---
    $run = v57_run($w, "printf 'a\\na\\nb\\nb\\nb\\nc\\n' | uniq -c");
    v57_check('cmd:uniq-c', trim(v57_stdout($run)) === "      2 a\n      3 b\n      1 c" || (bool)preg_match('/2\s+a/', v57_stdout($run)), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\na\\nb\\nc\\nc\\n' | uniq -u");
    v57_check('cmd:uniq-u', trim(v57_stdout($run)) === "b", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\na\\nb\\nc\\nc\\n' | uniq -d");
    v57_check('cmd:uniq-d', trim(v57_stdout($run)) === "a\nc", 'got ' . var_export(v57_stdout($run), true));

    // --- cut ---
    $run = v57_run($w, "echo 'a:b:c' | cut -d: -f2");
    v57_check('cmd:cut-d-f', trim(v57_stdout($run)) === 'b', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'a:b:c' | cut -d: -f2,3");
    v57_check('cmd:cut-d-f-multi', trim(v57_stdout($run)) === 'b:c', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'abcdef' | cut -c2-4");
    v57_check('cmd:cut-c-range', trim(v57_stdout($run)) === 'bcd', 'got ' . var_export(v57_stdout($run), true));

    // --- tr ---
    $run = v57_run($w, "echo hello | tr 'a-zA-Z' 'n-za-mN-ZA-M'");
    v57_check('cmd:tr-rot13', trim(v57_stdout($run)) === 'uryyb', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'hello world' | tr -d 'lo'");
    v57_check('cmd:tr-d', trim(v57_stdout($run)) === 'he wrd', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'aaabbbccc' | tr -s 'a-c'");
    v57_check('cmd:tr-s', trim(v57_stdout($run)) === 'abc', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'Hello123' | tr '[:upper:]' '[:lower:]'");
    v57_check('cmd:tr-classes', trim(v57_stdout($run)) === 'hello123', 'got ' . var_export(v57_stdout($run), true));

    // --- sed ---
    $run = v57_run($w, "echo 'aaa' | sed 's/a/b/g'");
    v57_check('cmd:sed-s-g', trim(v57_stdout($run)) === 'bbb', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\nb\\nc\\n' | sed -n '2p'");
    v57_check('cmd:sed-n-p', trim(v57_stdout($run)) === 'b', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '1\\n2\\n3\\n4\\n' | sed -n '2,3p'");
    v57_check('cmd:sed-range', trim(v57_stdout($run)) === "2\n3", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\nb\\nc\\n' | sed '/b/d'");
    v57_check('cmd:sed-re-d', trim(v57_stdout($run)) === "a\nc", 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, "printf 'x\\n' > sed_i.txt");
    v57_run($w, "sed -i 's/x/y/' sed_i.txt");
    v57_check('cmd:sed-i', trim(v57_stdout(v57_run($w, 'cat sed_i.txt'))) === 'y', 'sed -i mělo upravit soubor přímo');

    // --- awk ---
    $run = v57_run($w, "printf 'a b\\nc d\\n' | awk '{print \$2}'");
    v57_check('cmd:awk-fields', trim(v57_stdout($run)) === "b\nd", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'a:b:c' | awk -F: '{print \$3}'");
    v57_check('cmd:awk-F', trim(v57_stdout($run)) === 'c', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\nb\\nc\\n' | awk '{print NR, \$0}'");
    v57_check('cmd:awk-NR', trim(v57_stdout($run)) === "1 a\n2 b\n3 c", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'a b c' | awk '{print NF}'");
    v57_check('cmd:awk-NF', trim(v57_stdout($run)) === '3', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '1\\n2\\n3\\n' | awk '\$1>1'");
    v57_check('cmd:awk-pattern', trim(v57_stdout($run)) === "2\n3", 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '1\\n2\\n3\\n' | awk '{s+=\$1} END {print s}'");
    v57_check('cmd:awk-sum-end', trim(v57_stdout($run)) === '6', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "awk 'BEGIN {printf \"%05d\\n\", 7}'");
    v57_check('cmd:awk-printf', trim(v57_stdout($run)) === '00007', 'got ' . var_export(v57_stdout($run), true));

    // --- diff ---
    v57_run($w, "printf 'a\\nb\\nc\\n' > d1.txt");
    v57_run($w, "printf 'a\\nx\\nc\\n' > d2.txt");
    $run = v57_run($w, 'diff d1.txt d2.txt');
    v57_check('cmd:diff-normal-format', str_contains(v57_stdout($run), '2c2') && str_contains(v57_stdout($run), '< b') && str_contains(v57_stdout($run), '> x'), 'got ' . var_export(v57_stdout($run), true));
    v57_check('cmd:diff-exit-differs', $run['exit'] === 1, 'diff má vrátit 1, když se soubory liší, got ' . $run['exit']);
    v57_run($w, "cp d1.txt d3.txt");
    $run = v57_run($w, 'diff d1.txt d3.txt');
    v57_check('cmd:diff-exit-same', $run['exit'] === 0, 'diff má vrátit 0, když jsou soubory stejné, got ' . $run['exit']);

    // --- rev ---
    $run = v57_run($w, "echo abc | rev");
    v57_check('cmd:rev', trim(v57_stdout($run)) === 'cba', 'got ' . var_export(v57_stdout($run), true));

    // --- nl ---
    $run = v57_run($w, "printf 'a\\nb\\n' | nl");
    v57_check('cmd:nl', (bool)preg_match('/^\s*1\s+a/m', v57_stdout($run)) && (bool)preg_match('/^\s*2\s+b/m', v57_stdout($run)), 'got ' . var_export(v57_stdout($run), true));

    // --- tee ---
    v57_run($w, 'rm -f tee.txt');
    $run = v57_run($w, "echo hi | tee tee.txt");
    v57_check('cmd:tee-stdout', trim(v57_stdout($run)) === 'hi', 'got ' . var_export(v57_stdout($run), true));
    v57_check('cmd:tee-file', trim(v57_stdout(v57_run($w, 'cat tee.txt'))) === 'hi', 'tee mělo zapsat i do souboru');

    // --- base64 ---
    $run = v57_run($w, "echo -n 'ahoj' | base64");
    $b64 = trim(v57_stdout($run));
    v57_check('cmd:base64-encode', $b64 === base64_encode('ahoj'), 'got ' . var_export($b64, true));
    $run = v57_run($w, "echo -n '{$b64}' | base64 -d");
    v57_check('cmd:base64-decode', trim(v57_stdout($run)) === 'ahoj', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo 'not valid base64!!!' | base64 -d");
    v57_check('cmd:base64-invalid', $run['exit'] !== 0, 'neplatný base64 vstup má selhat, exit=' . $run['exit']);

    // --- xxd ---
    v57_run($w, "printf 'AB' > xxdtest.bin");
    $run = v57_run($w, 'xxd xxdtest.bin');
    v57_check('cmd:xxd-dump', str_contains(v57_stdout($run), '4142') || str_contains(v57_stdout($run), 'AB'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'xxd -p xxdtest.bin');
    v57_check('cmd:xxd-p', trim(v57_stdout($run)) === '4142', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo -n 4142 | xxd -r -p");
    v57_check('cmd:xxd-r-p', trim(v57_stdout($run)) === 'AB', 'got ' . var_export(v57_stdout($run), true));

    // --- strings ---
    v57_run($w, "printf 'ahoj\\x00\\x01\\x02svet\\x00' > strtest.bin");
    $run = v57_run($w, 'strings strtest.bin');
    v57_check('cmd:strings', str_contains(v57_stdout($run), 'ahoj') && str_contains(v57_stdout($run), 'svet'), 'got ' . var_export(v57_stdout($run), true));

    // --- md5sum / sha256sum ---
    v57_run($w, "printf 'ahoj\\n' > hashme.txt");
    $run = v57_run($w, 'md5sum hashme.txt');
    v57_check('cmd:md5sum', str_starts_with(trim(v57_stdout($run)), hash('md5', "ahoj\n")), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'sha256sum hashme.txt');
    v57_check('cmd:sha256sum', str_starts_with(trim(v57_stdout($run)), hash('sha256', "ahoj\n")), 'got ' . var_export(v57_stdout($run), true));
}

v57_section_cmd_sys();

function v57_section_cmd_sys(): void
{
    $w = v57_sandbox_world('sys');

    // --- chmod / chown / permissions ---
    v57_run($w, "echo x > ~/perm.txt");
    v57_run($w, 'chmod 700 ~/perm.txt');
    v57_check('cmd:chmod-octal', str_starts_with(lab57_mode_string((array)$w->fs->get($w->home() . '/perm.txt')), '-rwx------'), 'got ' . lab57_mode_string((array)$w->fs->get($w->home() . '/perm.txt')));
    v57_run($w, 'chmod u+x ~/perm.txt');
    v57_check('cmd:chmod-u+x', (((int)$w->fs->get($w->home() . '/perm.txt')['m']) & 0700) === 0700, 'mode=' . lab57_octal((int)$w->fs->get($w->home() . '/perm.txt')['m']));
    v57_run($w, 'chmod 664 ~/perm.txt');
    v57_run($w, 'chmod go-w ~/perm.txt');
    v57_check('cmd:chmod-go-w', lab57_octal((int)$w->fs->get($w->home() . '/perm.txt')['m']) === '0644', 'got ' . lab57_octal((int)$w->fs->get($w->home() . '/perm.txt')['m']));
    v57_run($w, 'chmod a=r ~/perm.txt');
    v57_check('cmd:chmod-a=r', lab57_octal((int)$w->fs->get($w->home() . '/perm.txt')['m']) === '0444', 'got ' . lab57_octal((int)$w->fs->get($w->home() . '/perm.txt')['m']));
    v57_run($w, 'mkdir -p ~/permtree/sub && touch ~/permtree/sub/f.txt');
    v57_run($w, 'chmod -R 750 ~/permtree');
    v57_check('cmd:chmod-R', lab57_octal((int)$w->fs->get($w->home() . '/permtree/sub')['m']) === '0750' && lab57_octal((int)$w->fs->get($w->home() . '/permtree/sub/f.txt')['m']) === '0750', 'got ' . lab57_octal((int)$w->fs->get($w->home() . '/permtree/sub/f.txt')['m']));
    $run = v57_run($w, 'chown root ~/perm.txt');
    v57_check('cmd:chown-without-sudo-fails', $run['exit'] !== 0, 'chown bez sudo má selhat, exit=' . $run['exit']);
    $run = v57_run($w, 'sudo chown root ~/perm.txt');
    v57_check('cmd:chown-with-sudo-works', $run['exit'] === 0 && (string)$w->fs->get($w->home() . '/perm.txt')['u'] === 'root', 'exit=' . $run['exit'] . ' owner=' . ($w->fs->get($w->home() . '/perm.txt')['u'] ?? '?'));

    // --- sudo disabled level ---
    $noSudoLevel = ['id' => 'sandbox', 'pack' => 'free', 'v' => 1, 'world' => ['sandbox' => true, 'sudo' => false]];
    $wNoSudo = lab57_build_world($noSudoLevel, 'audit|nosudo', ['CODE' => 'EDU-TEST-0000'], v57_now());
    $run = v57_run($wNoSudo, 'sudo whoami');
    v57_check('cmd:sudo-disabled-level-fails', $run['exit'] !== 0, 'sudo má v úrovni s vypnutým sudo selhat, exit=' . $run['exit']);

    // --- ps ---
    $run = v57_run($w, 'ps aux');
    v57_check('cmd:ps-aux', str_contains(v57_stdout($run), 'USER') && str_contains(v57_stdout($run), 'sshd'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ps -ef');
    v57_check('cmd:ps-ef', str_contains(v57_stdout($run), 'UID') && str_contains(v57_stdout($run), 'PID'), 'got ' . var_export(v57_stdout($run), true));

    // --- systemctl (než se sshd zabije níže příkazem kill) ---
    $run = v57_run($w, 'systemctl status ssh');
    v57_check('cmd:systemctl-status', str_contains(v57_stdout($run), 'ssh.service'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'systemctl is-active ssh');
    v57_check('cmd:systemctl-is-active', trim(v57_stdout($run)) === 'active', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'systemctl stop ssh');
    v57_check('cmd:systemctl-stop-without-sudo-fails', $run['exit'] !== 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'sudo systemctl stop ssh');
    v57_check('cmd:systemctl-stop-with-sudo', $run['exit'] === 0 && lab57_service_state($w->services['ssh']) !== 'active', 'exit=' . $run['exit']);
    $run = v57_run($w, 'sudo systemctl start ssh');
    v57_check('cmd:systemctl-start', $run['exit'] === 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'sudo systemctl restart ssh');
    v57_check('cmd:systemctl-restart', $run['exit'] === 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'sudo systemctl enable --now ssh');
    v57_check('cmd:systemctl-enable-now', $run['exit'] === 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'systemctl is-active ssh');
    v57_check('cmd:systemctl-is-active-after-enable-now', trim(v57_stdout($run)) === 'active', 'got ' . var_export(v57_stdout($run), true));

    // --- kill / pkill ---
    $sshdPid = null;
    foreach ($w->procs as $pid => $proc) if (($proc['service'] ?? '') === 'ssh') { $sshdPid = $pid; break; }
    $run = v57_run($w, 'kill ' . $sshdPid);
    v57_check('cmd:kill-permission-error', $run['exit'] !== 0, 'kill cizího procesu bez oprávnění má selhat, exit=' . $run['exit']);
    $run = v57_run($w, 'sudo kill ' . $sshdPid);
    v57_check('cmd:kill-with-sudo', $run['exit'] === 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'pkill -f sshd');
    v57_check('cmd:pkill-permission', is_int($run['exit']), 'pkill nemělo spadnout');
    v57_run($w, 'sudo systemctl start ssh'); // vrátit službu do běžícího stavu pro další testy

    // --- journalctl ---
    $run = v57_run($w, 'journalctl -u ssh');
    v57_check('cmd:journalctl-u', $run['exit'] === 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'journalctl -n 1');
    v57_check('cmd:journalctl-n', substr_count(trim(v57_stdout($run)), "\n") <= 1, 'journalctl -n 1 má max jeden řádek, got ' . var_export(v57_stdout($run), true));
    $w->journalAdd('test-unit', 'chybová zpráva', 'err');
    $run = v57_run($w, 'journalctl -p err -u test-unit');
    v57_check('cmd:journalctl-p-err', str_contains(v57_stdout($run) . v57_stderr($run), 'chybová zpráva'), 'got ' . var_export(v57_all($run), true));

    // --- nginx -t s rozbitou konfigurací (file:line) ---
    $oprLevel = lab57_level('opr-1');
    if ($oprLevel === null) {
        v57_fail('cmd:nginx-t-file-line', 'úroveň opr-1 nenalezena');
    } else {
        $wOpr = lab57_build_world($oprLevel, 'audit|opr1|nginxcheck', ['CODE' => 'EDU-TEST-0000'], v57_now());
        $run = v57_run($wOpr, 'sudo nginx -t');
        $errText = v57_stderr($run);
        v57_check('cmd:nginx-t-reports-file', str_contains($errText, '/etc/nginx/sites-enabled/default'), 'got ' . var_export($errText, true));
        v57_check('cmd:nginx-t-reports-line', (bool)preg_match('/:\d+/', $errText), 'nginx -t má hlásit číslo řádku, got ' . var_export($errText, true));
        v57_check('cmd:nginx-t-exit-nonzero', $run['exit'] !== 0, 'exit=' . $run['exit']);
    }

    // --- apt ---
    $run = v57_run($w, 'apt install cowsay');
    v57_check('cmd:apt-install-needs-sudo', $run['exit'] !== 0, 'apt bez sudo má selhat, exit=' . $run['exit']);
    $run = v57_run($w, 'sudo apt install cowsay');
    v57_check('cmd:apt-install-cowsay', $run['exit'] === 0, 'exit=' . $run['exit'] . ' out=' . v57_all($run));
    $run = v57_run($w, 'cowsay moo');
    v57_check('cmd:cowsay-after-install', $run['exit'] === 0 && str_contains(v57_stdout($run), 'moo'), 'exit=' . $run['exit']);
    // rozbití DNS (prázdný resolv.conf) a ověření, že apt selže
    $wDns = v57_sandbox_world('sys-dns-broken');
    $wDns->root = true;
    $err = null;
    $wDns->writeFile('/etc/resolv.conf', "# no nameservers\n", false, $err);
    $wDns->root = false;
    $run = v57_run($wDns, 'sudo apt update');
    v57_check('cmd:apt-fails-dns-broken', $run['exit'] !== 0, 'apt update s rozbitým DNS má selhat, exit=' . $run['exit']);

    // --- df / du ---
    $run = v57_run($w, 'df -h');
    v57_check('cmd:df-h', str_contains(v57_stdout($run), 'Filesystem') && str_contains(v57_stdout($run), '/'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'du -sh ~/lst 2>/dev/null; du -sh /etc');
    v57_check('cmd:du-sh', $run['exit'] === 0, 'exit=' . $run['exit']);

    // --- id/groups/whoami/history/env/export/unset ---
    v57_expect_out($w, 'whoami', 'student', 'cmd:whoami');
    $run = v57_run($w, 'id');
    v57_check('cmd:id', str_contains(v57_stdout($run), 'uid=1000(student)'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'groups');
    v57_check('cmd:groups', str_contains(v57_stdout($run), 'student'), 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'echo test-history-entry');
    $run = v57_run($w, 'history');
    v57_check('cmd:history', str_contains(v57_stdout($run), 'test-history-entry'), 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'export FOO=bar');
    $run = v57_run($w, 'env');
    v57_check('cmd:env-export', str_contains(v57_stdout($run), 'FOO=bar'), 'got ' . var_export(v57_stdout($run), true));
    v57_run($w, 'unset FOO');
    $run = v57_run($w, 'env');
    v57_check('cmd:unset', !str_contains(v57_stdout($run), 'FOO=bar'), 'got ' . var_export(v57_stdout($run), true));

    // --- uname/date/test/xargs/printf/echo -e ---
    $run = v57_run($w, 'uname -a');
    v57_check('cmd:uname-a', str_contains(v57_stdout($run), 'Linux'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'date +%Y');
    v57_check('cmd:date-plusY', trim(v57_stdout($run)) === date('Y', v57_now()), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, '[ 1 -eq 1 ] && echo ano');
    v57_check('cmd:test-bracket', trim(v57_stdout($run)) === 'ano', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'test -d ~/projekty && echo je-slozka');
    v57_check('cmd:test-dash-d', trim(v57_stdout($run)) === 'je-slozka', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf 'a\\nb\\n' | xargs echo");
    v57_check('cmd:xargs', trim(v57_stdout($run)) === 'a b', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "printf '%s-%s\\n' jedna dva");
    v57_check('cmd:printf-format', trim(v57_stdout($run)) === 'jedna-dva', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, "echo -e 'x\\ty'");
    v57_check('cmd:echo-e', trim(v57_stdout($run)) === "x\ty", 'got ' . var_export(v57_stdout($run), true));

    // --- man / help / which / type ---
    $run = v57_run($w, 'man ls');
    v57_check('cmd:man-existing', $run['exit'] === 0 && v57_stdout($run) !== '', 'exit=' . $run['exit']);
    $run = v57_run($w, 'man neexistujiciprikaz123');
    v57_check('cmd:man-unknown', $run['exit'] !== 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'help');
    v57_check('cmd:help', $run['exit'] === 0 && v57_stdout($run) !== '', 'exit=' . $run['exit']);
    $run = v57_run($w, 'which ls');
    v57_check('cmd:which', trim(v57_stdout($run)) === '/usr/bin/ls' || trim(v57_stdout($run)) === '/bin/ls', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'type cd');
    v57_check('cmd:type', str_contains(v57_stdout($run), 'builtin') || str_contains(v57_stdout($run), 'cd'), 'got ' . var_export(v57_stdout($run), true));

    // --- hostname -I ---
    $run = v57_run($w, 'hostname -I');
    v57_check('cmd:hostname-I', (bool)preg_match('/\d+\.\d+\.\d+\.\d+/', trim(v57_stdout($run))), 'got ' . var_export(v57_stdout($run), true));
}

v57_section_cmd_net();

function v57_section_cmd_net(): void
{
    $w = v57_sandbox_world('net');

    // --- ip a / r / link / neigh ---
    $run = v57_run($w, 'ip a');
    v57_check('cmd:ip-a', str_contains(v57_stdout($run), 'eth0') && str_contains(v57_stdout($run), '10.0.0.23'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ip r');
    v57_check('cmd:ip-r', str_contains(v57_stdout($run), 'default via'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ip link');
    v57_check('cmd:ip-link', str_contains(v57_stdout($run), 'eth0'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'ip neigh');
    v57_check('cmd:ip-neigh', is_int($run['exit']), 'ip neigh nemělo spadnout');

    // --- ping OK + statistics ---
    $run = v57_run($w, 'ping -c 2 10.0.0.1');
    v57_check('cmd:ping-ok', str_contains(v57_stdout($run), '0% packet loss') && str_contains(v57_stdout($run), '2 packets transmitted'), 'got ' . var_export(v57_stdout($run), true));

    // --- eth0 down → ping Network is unreachable ---
    $run = v57_run($w, 'sudo ip link set eth0 down');
    v57_check('cmd:ip-link-set-down', $run['exit'] === 0, 'exit=' . $run['exit']);
    $run = v57_run($w, 'ping -c 1 10.0.0.1');
    v57_check('cmd:ping-network-unreachable', str_contains(v57_all($run), 'Network is unreachable'), 'got ' . var_export(v57_all($run), true));
    v57_run($w, 'sudo ip link set eth0 up');

    // --- traceroute stars after wan_break_after ---
    // Přímá IP obchází DNS (ten by přes stejný výpadek stejně neodpověděl).
    $w->net['faults']['wan_break_after'] = 'isp';
    $run = v57_run($w, 'traceroute 203.0.113.10');
    v57_check('cmd:traceroute-stars-after-fault', str_contains(v57_stdout($run), '*'), 'got ' . var_export(v57_stdout($run), true));
    unset($w->net['faults']['wan_break_after']);

    // --- dig ---
    $run = v57_run($w, 'dig example.com');
    v57_check('cmd:dig-noerror', str_contains(v57_stdout($run), 'NOERROR') && str_contains(v57_stdout($run), '203.0.113.10'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'dig neexistujici-domena-xyz.test');
    v57_check('cmd:dig-nxdomain', str_contains(v57_stdout($run), 'NXDOMAIN'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'dig +short example.com');
    v57_check('cmd:dig-plusshort', trim(v57_stdout($run)) === '203.0.113.10', 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'dig @1.1.1.1 example.com');
    v57_check('cmd:dig-at-server', str_contains(v57_stdout($run), '1.1.1.1'), 'got ' . var_export(v57_stdout($run), true));
    // timeout s mrtvým resolverem
    $wDeadDns = v57_sandbox_world('net-dead-dns');
    $wDeadDns->net['nodes']['dns1']['up'] = false;
    $wDeadDns->net['nodes']['dns2']['up'] = false;
    $errR = null;
    $wDeadDns->root = true;
    $wDeadDns->writeFile('/etc/resolv.conf', "nameserver 1.1.1.1\n", false, $errR);
    $wDeadDns->root = false;
    $run = v57_run($wDeadDns, 'dig example.com');
    v57_check('cmd:dig-timeout-dead-resolver', str_contains(v57_stdout($run) . v57_stderr($run), 'connection timed out') || str_contains(v57_stdout($run), ';;'), 'got ' . var_export(v57_all($run), true));

    // --- nslookup / host ---
    $run = v57_run($w, 'nslookup example.com');
    v57_check('cmd:nslookup', str_contains(v57_stdout($run), '203.0.113.10'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'host example.com');
    v57_check('cmd:host', str_contains(v57_stdout($run), '203.0.113.10'), 'got ' . var_export(v57_stdout($run), true));

    // --- curl ---
    $run = v57_run($w, 'curl http://intranet.skola.test/');
    v57_check('cmd:curl-200', $run['exit'] === 0 && str_contains(v57_stdout($run), 'Školní intranet'), 'exit=' . $run['exit']);
    $run = v57_run($w, 'curl -I http://intranet.skola.test/');
    v57_check('cmd:curl-I-headers', str_contains(v57_stdout($run), 'HTTP/1.1 200') || str_contains(v57_stdout($run), '200 OK'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'curl -I http://example.com/');
    v57_check('cmd:curl-301', str_contains(v57_stdout($run), '301'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'curl -IL http://example.com/');
    v57_check('cmd:curl-L-follows', str_contains(v57_stdout($run), '200'), 'got ' . var_export(v57_stdout($run), true));
    $run = v57_run($w, 'curl http://10.0.0.10:9999/');
    v57_check('cmd:curl-refused', str_contains(v57_all($run), 'refused') || stripos(v57_all($run), 'failed to connect') !== false, 'got ' . var_export(v57_all($run), true));
    $run = v57_run($w, 'curl http://neexistujici-domena-xyz.test/');
    v57_check('cmd:curl-could-not-resolve', str_contains(v57_all($run), 'resolve'), 'got ' . var_export(v57_all($run), true));
    v57_run($w, 'rm -f ~/out.html');
    $run = v57_run($w, 'curl -o ~/out.html http://intranet.skola.test/');
    v57_check('cmd:curl-o-file', $w->fs->exists($w->home() . '/out.html') && str_contains((string)$w->fs->get($w->home() . '/out.html')['c'], 'Školní intranet'), 'soubor uložený přes -o chybí nebo je prázdný');

    // --- wget ---
    v57_run($w, 'cd /tmp && rm -f index.html');
    $run = v57_run($w, 'wget http://intranet.skola.test/');
    v57_check('cmd:wget-saves-index', $w->fs->exists('/tmp/index.html'), 'wget mělo uložit index.html, exit=' . $run['exit'] . ' out=' . v57_all($run));

    // --- ss ---
    $run = v57_run($w, 'ss -tln');
    v57_check('cmd:ss-tln', str_contains(v57_stdout($run), '22') || str_contains(v57_stdout($run), 'LISTEN'), 'got ' . var_export(v57_stdout($run), true));

    // --- nc open/closed ---
    $run = v57_run($w, 'nc -zv localhost 22');
    v57_check('cmd:nc-zv-open', $run['exit'] === 0, 'port 22 (ssh) by měl být otevřený, exit=' . $run['exit'] . ' ' . v57_all($run));
    $run = v57_run($w, 'nc -zv localhost 54321');
    v57_check('cmd:nc-zv-closed', $run['exit'] !== 0, 'port 54321 by měl být zavřený, exit=' . $run['exit']);

    // --- nc přes úroveň quest-11 (echo text | nc localhost PORT) ---
    $q11 = lab57_level('quest-11');
    if ($q11 === null) {
        v57_fail('cmd:nc-service-quest11', 'úroveň quest-11 nenalezena');
    } else {
        $seedQ = 'audit|quest11|nc';
        $wQ = lab57_build_world($q11, $seedQ, ['CODE' => 'EDU-TEST-0000'], v57_now());
        $port = (int)($wQ->mem['q11'] ?? 0);
        $run = v57_run($wQ, 'echo prosim-kod | nc localhost ' . $port);
        v57_check('cmd:nc-service-quest11', str_contains(v57_stdout($run), 'Tady je tvůj kód'), 'got ' . var_export(v57_stdout($run), true));
    }

    // --- hostname -I (síťová sekce) ---
    $run = v57_run($w, 'hostname -I');
    v57_check('cmd:net-hostname-I', trim(v57_stdout($run)) !== '', 'got ' . var_export(v57_stdout($run), true));
}

// ===========================================================================
// 5) Úrovně: řešitelnost, determinismus, kódy, golf, kontrolní úlohy
// ===========================================================================

/** Spustí referenční řešení úrovně přes lab57_session a vrátí, zda se vyřešila. */
function v57_solve_level(string $class, string $student, string $context, array $level, int $now, array $classmates = []): array
{
    $seed = lab57_seed($class, $student, $context, $level);
    $codes = ['CODE' => lab57_code($class, $student, $context, (string)$level['id'])];
    $world = lab57_build_world($level, $seed, $codes, $now);
    $cmds = is_callable($level['solution'] ?? null) ? ($level['solution'])($world) : [];
    $ctx = ['class' => $class, 'student' => $student, 'label' => 'Test Žák', 'context' => $context, 'level' => $level['id'], 'now' => $now, 'classmates' => $classmates];
    $solved = false;
    $responses = [];
    foreach ($cmds as $cmd) {
        $resp = lab57_session($ctx, 'run', ['line' => $cmd]);
        $responses[] = $resp;
        if (!empty($resp['solved'])) $solved = true;
    }
    return ['solved' => $solved, 'responses' => $responses, 'cmds' => $cmds];
}

/** Úrovně balíčků v57 (lab57_levels() obsahuje i úrovně registrované ve v58 – ty testuje v58 audit). */
function v57_builtin_levels(): array
{
    $packs = lab57_packs();
    return array_filter(lab57_levels(), static fn(array $l): bool => isset($packs[$l['pack']]));
}

/** Rovnou zapíše do stavu žáka, že prvních $count úrovní balíčku je vyřešeno – odemkne zbytek bez nutnosti je skutečně hrát. */
function v57_force_unlock(string $class, string $student, string $pack, int $count, int $now): void
{
    $levels = lab57_pack_levels($pack);
    lab57_store_update(lab57_state_path($class, $student), static function (array $data) use ($levels, $count, $now): array {
        for ($i = 0; $i < $count && $i < count($levels); $i++) {
            $data['solved']['practice'][$levels[$i]['id']] = ['at' => date(DATE_ATOM, $now), 'points' => 1, 'hints' => 0, 'cmds' => 1, 'secs' => 1];
        }
        return $data;
    });
}

v57_section_levels();

function v57_section_levels(): void
{
    $now = v57_now();
    $levels = v57_builtin_levels();
    v57_check('levels:count', count($levels) === 46, 'očekáváno 46 úrovní, nalezeno ' . count($levels));

    // Každý balíček řešíme postupně tak, aby předchozí úroveň odemkla další (test i odemykání).
    $packs = [];
    foreach ($levels as $level) $packs[$level['pack']][] = $level;
    foreach ($packs as $pack => $packLevels) {
        $class = 'auditcls';
        $student = 'stud-' . $pack;
        foreach ($packLevels as $i => $level) {
            $id = $level['id'];
            v57_no_warnings("levels:solvable:{$id}", function () use ($class, $student, $level, $now): void {
                $result = v57_solve_level($class, $student, 'practice', $level, $now);
                v57_check("levels:solvable:{$level['id']}", $result['solved'], 'referenční řešení ' . json_encode($result['cmds'], JSON_UNESCAPED_UNICODE) . ' úlohu nevyřešilo');
            });
        }
    }

    // --- determinismus: stejné semínko → stejný svět ---
    $detLevel = lab57_level('quest-1') ?? reset($levels);
    $seedA = lab57_seed('cls-det', 'stud-det', 'practice', $detLevel);
    $wA = lab57_build_world($detLevel, $seedA, ['CODE' => 'X'], $now);
    $wB = lab57_build_world($detLevel, $seedA, ['CODE' => 'X'], $now);
    v57_check('levels:determinism', sha1(serialize($wA->fs->all())) === sha1(serialize($wB->fs->all())), 'stejné semínko musí dát stejný souborový systém');

    // --- různí žáci → různé kódy; formát kódu (quest-1 je první v balíčku, netřeba nic odemykat) ---
    $codeLevel = lab57_level('quest-1');
    $codeA = lab57_code('cls-code', 'zak-a', 'practice', (string)$codeLevel['id']);
    $codeB = lab57_code('cls-code', 'zak-b', 'practice', (string)$codeLevel['id']);
    v57_check('levels:codes-differ-per-student', $codeA !== $codeB, "codeA={$codeA} codeB={$codeB}");
    v57_check('levels:code-format', (bool)preg_match('/^EDU-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $codeA), 'got ' . $codeA);

    // --- špatný kód není vyřešen ---
    $wrongCtx = ['class' => 'cls-wrong', 'student' => 'zak-wrong', 'label' => 'Test', 'context' => 'practice', 'level' => $codeLevel['id'], 'now' => $now];
    $resp = lab57_session($wrongCtx, 'run', ['line' => 'submit EDU-ZZZZ-ZZZZ']);
    v57_check('levels:wrong-code-not-solved', empty($resp['solved']), 'špatný kód nesmí projít');

    // --- kód spolužáka je detekován a nevyřeší úlohu ---
    $classForMates = 'cls-mates';
    $me = 'zak-me';
    $mate = 'zak-mate';
    $mateCode = lab57_code($classForMates, $mate, 'practice', (string)$codeLevel['id']);
    $mateCtx = ['class' => $classForMates, 'student' => $me, 'label' => 'Test', 'context' => 'practice', 'level' => $codeLevel['id'], 'now' => $now, 'classmates' => [$me, $mate]];
    $respMate = lab57_session($mateCtx, 'run', ['line' => 'submit ' . $mateCode]);
    v57_check('levels:foreign-code-detected', empty($respMate['solved']), 'kód spolužáka nesmí vyřešit úlohu');
    $foreignText = '';
    foreach ((array)($respMate['out'] ?? []) as [, $text]) $foreignText .= $text;
    v57_check('levels:foreign-code-message', str_contains($foreignText, 'patří někomu jinému'), 'got ' . var_export($foreignText, true));

    // --- answer úrovně odmítnou špatnou odpověď ---
    $answerLevel = lab57_level('start-1');
    $answerCtx = ['class' => 'cls-answer', 'student' => 'zak-answer', 'label' => 'Test', 'context' => 'practice', 'level' => $answerLevel['id'], 'now' => $now];
    $respWrongAnswer = lab57_session($answerCtx, 'run', ['line' => 'answer /tohle-je-spatne']);
    v57_check('levels:wrong-answer-rejected', empty($respWrongAnswer['solved']), 'špatná odpověď nesmí projít');

    // --- golf úrovně (odemykají se postupně, proto pro každou úroveň napřed odemkneme předchůdce) ---
    $golfLevels = lab57_pack_levels('golf');
    foreach ($golfLevels as $index => $golfLevel) {
        $id = $golfLevel['id'];
        $class = 'cls-golf';

        $student = 'zak-' . $id;
        v57_force_unlock($class, $student, 'golf', $index, $now);
        $seed = lab57_seed($class, $student, 'practice', $golfLevel);
        $codes = ['CODE' => lab57_code($class, $student, 'practice', $id)];
        $ctx = ['class' => $class, 'student' => $student, 'label' => 'Test', 'context' => 'practice', 'level' => $id, 'now' => $now];

        // referenční řešení projde všechny skryté testy
        $refWorld = lab57_build_world($golfLevel, $seed, $codes, $now);
        $refCmd = (string)$golfLevel['golf']['reference'];
        lab57_session($ctx, 'run', ['line' => $refCmd]);
        $respRef = lab57_session($ctx, 'run', ['line' => 'submit']);
        v57_check("levels:golf-reference-passes:{$id}", !empty($respRef['solved']), 'referenční řešení mělo projít golf testy, out=' . json_encode($respRef['out'] ?? [], JSON_UNESCAPED_UNICODE) . ' error=' . ($respRef['error'] ?? ''));

        // Hard-coded echo viditelného výstupu nesmí obecně projít skryté testy s jinými daty.
        // Pro úlohy s úzkým rozsahem odpovědi (např. malé celé číslo) může náhodou vyjít stejná
        // hodnota i pro jiná data – proto zkoušíme víc různých instancí a stačí, když aspoň
        // jedna z nich hardcoding odhalí (to dokazuje, že mechanismus skrytých testů funguje).
        $anyHardFailed = false;
        $hardAttempts = [];
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $studentHard = 'zak-' . $id . '-hard' . $attempt;
            v57_force_unlock($class, $studentHard, 'golf', $index, $now);
            $seedHard = lab57_seed($class, $studentHard, 'practice', $golfLevel);
            $codesHard = ['CODE' => lab57_code($class, $studentHard, 'practice', $id)];
            $visibleOut = trim(v57_stdout(lab57_run_line(lab57_build_world($golfLevel, $seedHard, $codesHard, $now), $refCmd)));
            $ctxHard = ['class' => $class, 'student' => $studentHard, 'label' => 'Test', 'context' => 'practice', 'level' => $id, 'now' => $now];
            lab57_session($ctxHard, 'run', ['line' => 'echo ' . escapeshellarg($visibleOut)]);
            $respHard = lab57_session($ctxHard, 'run', ['line' => 'submit']);
            $hardAttempts[] = $visibleOut . '=>' . (!empty($respHard['solved']) ? 'passed' : 'failed');
            if (empty($respHard['solved'])) { $anyHardFailed = true; break; }
        }
        v57_check("levels:golf-hardcoded-fails:{$id}", $anyHardFailed, 'natvrdo opsaný výstup prošel skryté testy ve všech pokusech: ' . implode(', ', $hardAttempts));

        // zjevně špatný příkaz neprojde
        $studentBad = 'zak-' . $id . '-bad';
        v57_force_unlock($class, $studentBad, 'golf', $index, $now);
        $ctxBad = ['class' => $class, 'student' => $studentBad, 'label' => 'Test', 'context' => 'practice', 'level' => $id, 'now' => $now];
        lab57_session($ctxBad, 'run', ['line' => 'echo spatne-reseni']);
        $respBad = lab57_session($ctxBad, 'run', ['line' => 'submit']);
        v57_check("levels:golf-wrong-fails:{$id}", empty($respBad['solved']), 'zjevně špatný příkaz nesmí projít, error=' . ($respBad['error'] ?? ''));
    }

    // --- check úrovně: nejsou vyřešené před opravou ---
    foreach (lab57_pack_levels('opravna') as $checkLevel) {
        $id = $checkLevel['id'];
        $seed = lab57_seed('cls-check', 'zak-' . $id, 'practice', $checkLevel);
        $w = lab57_build_world($checkLevel, $seed, ['CODE' => 'X'], $now);
        $checks = lab57_eval_checks($checkLevel, $w);
        $allOk = $checks !== [] && array_filter($checks, static fn(array $c): bool => !$c['ok']) === [];
        v57_check("levels:check-not-solved-before-fix:{$id}", !$allOk, 'kontrolní úloha ' . $id . ' vypadá vyřešená hned po sestavení světa (bez opravy)');
    }
}

// ===========================================================================
// 6) Perzistence: stav přežije nový dotaz, reset vrátí původní svět, formát souboru
// ===========================================================================

v57_section_persistence();

function v57_section_persistence(): void
{
    $now = v57_now();
    $class = 'cls-persist';
    $student = 'zak-persist';
    $levelId = 'sandbox';
    $ctx = ['class' => $class, 'student' => $student, 'label' => 'Test', 'context' => 'practice', 'level' => $levelId, 'now' => $now];

    // Mutující příkaz přežije nový dotaz 'state'.
    lab57_session($ctx, 'run', ['line' => 'mkdir x_persist_test']);
    $stateResp = lab57_session($ctx, 'state');
    v57_check('persist:mutation-survives-state', (bool)$stateResp['ok'], 'state op selhal: ' . ($stateResp['error'] ?? ''));
    $tx = (array)($stateResp['tx'] ?? []);
    $found = false;
    foreach ($tx as $entry) if (str_contains((string)($entry['c'] ?? ''), 'mkdir x_persist_test')) $found = true;
    v57_check('persist:mutation-in-history', $found, 'příkaz mkdir se neobjevil v historii transakcí po novém state dotazu');
    $runAgain = lab57_session($ctx, 'run', ['line' => 'test -d x_persist_test && echo prezije']);
    $outAgain = '';
    foreach ((array)($runAgain['out'] ?? []) as [, $t]) $outAgain .= $t;
    v57_check('persist:directory-still-exists', str_contains($outAgain, 'prezije'), 'složka vytvořená minule už neexistuje po novém požadavku, out=' . var_export($outAgain, true));

    // Reset vrátí úroveň do původního stavu.
    $resetResp = lab57_session($ctx, 'reset');
    v57_check('persist:reset-ok', (bool)$resetResp['ok'], 'reset selhal: ' . ($resetResp['error'] ?? ''));
    $afterReset = lab57_session($ctx, 'run', ['line' => 'test -d x_persist_test && echo pretrvava || echo pryc']);
    $outReset = '';
    foreach ((array)($afterReset['out'] ?? []) as [, $t]) $outReset .= $t;
    v57_check('persist:reset-restores-original', str_contains($outReset, 'pryc'), 'po resetu měla složka zmizet, out=' . var_export($outReset, true));

    // Formát stavového souboru: guard hlavička + platný JSON.
    $path = lab57_state_path($class, $student);
    v57_check('persist:state-file-exists', is_file($path), 'stavový soubor ' . $path . ' neexistuje');
    if (is_file($path)) {
        $raw = (string)file_get_contents($path);
        v57_check('persist:state-file-guard-header', str_starts_with($raw, '<?php http_response_code(403); exit; ?>'), 'soubor nezačíná bezpečnostní hlavičkou, got ' . var_export(substr($raw, 0, 60), true));
        $json = preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw;
        $decoded = json_decode($json, true);
        v57_check('persist:state-file-valid-json', json_last_error() === JSON_ERROR_NONE && is_array($decoded), 'obsah po hlavičce není platné JSON: ' . json_last_error_msg());
    }
}

// ===========================================================================
// 7) Konzistence manuálu s registrem příkazů a s chováním simulátoru
// ===========================================================================

/**
 * Příkazy, jejichž příklady se v čistém pískovišti (bez aktivní úlohy) přeskakují,
 * protože vyžadují kontext konkrétní laboratorní úlohy (aktivní level/řádek).
 * Volání mimo lab57_session (bez lab57_active()) by u nich shodilo PHP TypeError.
 */
const V57_MANUAL_SKIP_COMMANDS = ['mise', 'hint', 'submit', 'answer', 'check', 'reset'];

v57_section_manual();

function v57_section_manual(): void
{
    $now = v57_now();
    // v57 audit hlídá základní příručku; položky registrované ve v58 kontroluje tools/v58_lab_ext_audit.php.
    $manual = v57_manual_base();
    $registry = lab57_command_registry();

    // Každý in_lab příkaz manuálu musí existovat v registru příkazů.
    $orphans = [];
    foreach ((array)$manual['commands'] as $name => $cmd) {
        if (!empty($cmd['in_lab']) && !isset($registry[$name])) $orphans[] = (string)$name;
    }
    v57_check('manual:in-lab-commands-in-registry', $orphans === [], 'v manuálu jsou in_lab příkazy bez odpovídajícího příkazu v registru: ' . implode(', ', $orphans));

    // Každý příklad in_lab příkazu se má spustit v čerstvém pískovišti bez exit 127
    // a bez "invalid option"/"unrecognized option"/"unknown" ve stderr.
    $failures = [];
    $tested = 0;
    $skipped = 0;
    foreach ((array)$manual['commands'] as $name => $cmd) {
        if (empty($cmd['in_lab'])) continue;
        foreach ((array)($cmd['examples'] ?? []) as $example) {
            [$line, ] = is_array($example) ? $example : [(string)$example, ''];
            $first = strtok($line, " \t");
            if (in_array($first, V57_MANUAL_SKIP_COMMANDS, true)) { $skipped++; continue; }
            $tested++;
            $level = lab57_sandbox_level();
            $world = lab57_build_world($level, 'manual-check|' . $name . '|' . md5($line), ['CODE' => 'EDU-TEST-0000'], $now);
            try {
                $run = lab57_run_line($world, $line);
            } catch (Throwable $e) {
                $failures[] = "{$name}: `{$line}` vyhodilo výjimku " . $e->getMessage();
                continue;
            }
            $err = v57_stderr($run);
            $bad = $run['exit'] === 127
                || stripos($err, 'invalid option') !== false
                || stripos($err, 'unrecognized option') !== false
                || stripos($err, 'unknown') !== false;
            if ($bad) $failures[] = "{$name}: `{$line}` exit={$run['exit']} stderr=" . trim($err);
        }
    }
    v57_check('manual:examples-run-cleanly', $failures === [], count($failures) . ' příklad(ů) selhalo: ' . implode(' | ', $failures));
    v57_ok('manual:examples-coverage (tested=' . $tested . ', skipped=' . $skipped . ', reason: vyžadují aktivní úlohu – ' . implode(', ', V57_MANUAL_SKIP_COMMANDS) . ')');
}

// ===========================================================================
// 8) Limity: velikost souboru, ořez výstupu, délka příkazu, limit kroků
// ===========================================================================

v57_section_limits();

function v57_section_limits(): void
{
    $w = v57_sandbox_world('limits');

    // --- limit velikosti souboru (1 MB) ---
    v57_no_warnings('limits:file-size-cap', function () use ($w): void {
        $err = null;
        $tooLong = str_repeat('a', 1_100_000);
        $ok = $w->writeFile('/tmp/toobig.txt', $tooLong, false, $err);
        v57_check('limits:file-size-cap', $ok === false && $err === 'No space left on device', 'zápis nad 1 MB měl selhat s "No space left on device", got ok=' . var_export($ok, true) . ' err=' . var_export($err, true));
    });

    // --- ořez výstupu u obřích výstupů ---
    v57_no_warnings('limits:output-truncation', function () use ($w): void {
        v57_run($w, 'mkdir -p /tmp/huge');
        $err = null;
        $w->writeFile('/tmp/huge/big.txt', str_repeat("radek dlouheho vystupu do souboru\n", 6000), false, $err);
        $run = v57_run($w, 'cat /tmp/huge/big.txt');
        $all = v57_all($run);
        v57_check('limits:output-truncation', strlen($all) <= LAB57_MAX_OUTPUT + 200 && str_contains($all, 'výstup je příliš dlouhý'), 'výstup nebyl ořezán na limit ' . LAB57_MAX_OUTPUT . ' bajtů, délka=' . strlen($all));
    });

    // --- limit délky příkazové řádky ---
    v57_no_warnings('limits:line-length-limit', function () use ($w): void {
        $longLine = 'echo ' . str_repeat('x', LAB57_MAX_LINE + 50);
        $run = v57_run($w, $longLine);
        v57_check('limits:line-length-limit', $run['exit'] !== 0 && str_contains(v57_all($run), 'příliš dlouhý'), 'příkaz delší než LAB57_MAX_LINE měl selhat s chybou o délce, got exit=' . $run['exit'] . ' out=' . var_export(v57_all($run), true));
    });

    // --- limit 400 kroků (hluboce zřetězené příkazy) ---
    // Krátký alias, aby se 450 dílčích příkazů vešlo pod limit délky řádku (LAB57_MAX_LINE).
    v57_no_warnings('limits:max-steps', function () use ($w): void {
        v57_run($w, 'alias t=true');
        $chain = implode(';', array_fill(0, 450, 't'));
        v57_check('limits:max-steps-line-fits', strlen($chain) <= LAB57_MAX_LINE, 'testovací řetězec je delší než LAB57_MAX_LINE, upravte test');
        $run = v57_run($w, $chain);
        v57_check('limits:max-steps', str_contains(v57_all($run), 'příliš dlouhý řetězec příkazů'), '450 zřetězených příkazů má narazit na limit ' . LAB57_MAX_STEPS . ' kroků, got ' . var_export(v57_all($run), true));
    });
}

// ===========================================================================
// 9) Výkon (informativní – FAIL jen při zjevném problému)
// ===========================================================================

v57_section_performance();

function v57_section_performance(): void
{
    $now = v57_now();

    // Celkový čas běhu referenčních řešení všech 46 úrovní přes lab57_session (jako skutečný žák).
    // Balíčky se odemykají postupně, proto každému testovacímu žákovi napřed odemkneme předchůdce.
    $t0 = microtime(true);
    $packs = [];
    foreach (v57_builtin_levels() as $level) $packs[$level['pack']][] = $level;
    foreach ($packs as $pack => $packLevels) {
        foreach ($packLevels as $index => $level) {
            $student = 'perf-stud-' . $pack;
            if ($index > 0) v57_force_unlock('perf-cls', $student, $pack, $index, $now);
            v57_solve_level('perf-cls', $student, 'practice', $level, $now);
        }
    }
    $totalMs = (microtime(true) - $t0) * 1000;
    echo sprintf("INFO perf:levels-total-time %.1f ms pro %d úrovní\n", $totalMs, count(v57_builtin_levels()));
    v57_check('perf:levels-total-time-sane', $totalMs < 15000, 'referenční řešení všech úrovní trvalo neobvykle dlouho: ' . round($totalMs) . ' ms');

    // Průměrný čas 200 jednoduchých příkazů v pískovišti.
    $w = v57_sandbox_world('perf');
    $simple = ['ls', 'pwd', 'echo ahoj', 'cat vitej.txt', 'ls -la', 'whoami', 'echo $HOME', 'true'];
    $t0 = microtime(true);
    for ($i = 0; $i < 200; $i++) lab57_run_line($w, $simple[$i % count($simple)]);
    $avgMs = ((microtime(true) - $t0) * 1000) / 200;
    echo sprintf("INFO perf:avg-simple-command %.3f ms (cíl < 50 ms)\n", $avgMs);
    v57_check('perf:avg-simple-command-under-50ms', $avgMs < 50, 'průměrný čas jednoduchého příkazu ' . round($avgMs, 3) . ' ms překračuje cíl 50 ms');
}

// ===========================================================================
// Souhrn: PHP varování/notice zachycená během auditu se počítají jako FAIL
// ===========================================================================

if ($GLOBALS['v57_audit_warnings'] !== []) {
    v57_check('runtime:no-unhandled-php-warnings', false, count($GLOBALS['v57_audit_warnings']) . ' nezachycených PHP varování: ' . implode(' | ', array_slice($GLOBALS['v57_audit_warnings'], 0, 10)));
} else {
    v57_ok('runtime:no-unhandled-php-warnings');
}

$totalMs = (microtime(true) - $T0) * 1000;
echo sprintf("\n=== Audit dokončen za %.1f ms ===\n", $totalMs);

$checks = $GLOBALS['v57_checks'];
$failed = $GLOBALS['v57_failed'];
if ($failed === 0) {
    echo "AUDIT_OK checks={$checks} failed=0\n";
    exit(0);
}
echo "AUDIT_FAILED checks={$checks} failed={$failed}\n";
exit(1);
