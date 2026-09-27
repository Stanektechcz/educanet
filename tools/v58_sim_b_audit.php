<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Audit SIM-B2 – grafika (ImageMagick, exiftool, rename, file), drobné nástroje
 * (basename … yes, expr, factor, bc), příručka (tldr, see_also, tipy „→ man“) a balíček úrovní
 * „Terminál pro grafiky“ (grafika-*).
 * Pouze CLI, izolované dočasné úložiště (nikdy nesahá na storage/). Nic nespouští, žádná síť.
 * Načítá jen VLASTNÍ rozšíření (lab58_skip_ext), takže rozpracované soubory jiných agentů audit neovlivní.
 * Spuštění: php tools/v58_sim_b_audit.php  →  poslední řádek V58_SIM_B_AUDIT_OK checks=N failed=0
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Prague');

$ROOT = dirname(__DIR__);
$TMP = sys_get_temp_dir() . '/v58_sim_b_audit_' . bin2hex(random_bytes(6));
mkdir($TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $TMP;
$GLOBALS['lab57_secret_override'] = 'v58-sim-b-secret';
$GLOBALS['lab58_skip_ext'] = true;
ini_set('log_errors', '1');
ini_set('error_log', $TMP . '/php_errors.log');

register_shutdown_function(static function () use ($TMP): void {
    if (!is_dir($TMP)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($TMP, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($TMP);
});

$GLOBALS['simb_warnings'] = [];
set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) return true;
    $GLOBALS['simb_warnings'][] = $str . ' (' . basename($file) . ':' . $line . ')';
    return true;
});

const SIMB_FILES = [
    'linux_v58_cmd_extra.php', 'linux_v58_cmd_extra_bc.php', 'linux_v58_cmd_extra_calc.php', 'linux_v58_cmd_extra_sys.php',
    'linux_v58_cmd_media.php', 'linux_v58_cmd_media_im.php', 'linux_v58_cmd_media_tools.php',
    'linux_v58_cmd_tldr_a.php', 'linux_v58_cmd_tldr_b.php', 'linux_v58_cmd_tldr_c.php', 'linux_v58_levels_grafika.php',
];
const SIMB_NEW = ['identify', 'convert', 'magick', 'mogrify', 'exiftool', 'rename', 'basename', 'dirname', 'realpath', 'readlink', 'seq', 'shuf',
    'paste', 'column', 'fold', 'comm', 'join', 'split', 'yes', 'whereis', 'pgrep', 'lsblk', 'lscpu', 'mount', 'watch', 'time', 'expr', 'factor', 'bc'];
const SIMB_NOW = 1790000000;
const SIMB_SEEDS = ['s1', 's2', 's3', 's4', 's5', 's6'];

require_once $ROOT . '/linux_v57_lab.php';
foreach (SIMB_FILES as $f) require_once $ROOT . '/' . $f;

$GLOBALS['simb'] = ['checks' => 0, 'failed' => 0];
function simb_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['simb']['checks']++;
    if (!$ok) $GLOBALS['simb']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}

function simb_world(string $seed = 'out'): Lab57World
{
    return lab57_build_world(lab57_sandbox_level(), 'simb|' . $seed, ['CODE' => 'EDU-TEST-0000'], SIMB_NOW);
}

function simb_text(array $chunks, ?int $fd): string
{
    $o = '';
    foreach ($chunks as [$f, $t]) if ($fd === null || $f === $fd) $o .= $t;
    return (string)preg_replace('/\e\[[0-9;]*m/', '', $o);
}

/** @return array{0:string,1:string,2:int,3:string} stdout, stderr, exit, tipy */
function simb_exec(Lab57World $w, string $line): array
{
    $w->tips = [];
    $r = lab57_run_line($w, $line);
    return [simb_text($r['chunks'], 1), simb_text($r['chunks'], 2), (int)$r['exit'], implode(' | ', $w->tips)];
}

/** Spustí řádky nad světem úrovně jako lab58_try_solution; true = úroveň vyřešena. */
function simb_attempt(array $level, string $seed, array $lines, string $code = 'EDU-TEST-0000'): bool
{
    $codes = ['CODE' => $code];
    $w = lab57_build_world($level, $seed, $codes, SIMB_NOW);
    $row = ['started' => SIMB_NOW, 'hints' => 0, 'cmds' => 0];
    foreach ($lines as $line) {
        $active = &lab57_active();
        $active = ['level' => $level, 'row' => $row, 'result' => [], 'codes' => $codes, 'seed' => $seed, 'now' => SIMB_NOW, 'race' => null, 'foreign' => null];
        lab57_run_line($w, lab58_fill($w, (string)$line));
        $row = $active['row'];
        $result = $active['result'];
        $active = [];
        unset($active);
        if (!empty($result['solve'])) return true;
        if ($level['type'] === 'check' && array_filter(lab57_eval_checks($level, $w), static fn(array $c): bool => !$c['ok']) === []) return true;
    }
    return false;
}

/** Otisk složky ~/studio (výpis se velikostmi + md5 obsahu) – pro determinismus světa úrovně. */
function simb_snapshot(Lab57World $w): string
{
    return md5(simb_text(lab57_run_line($w, 'ls -lR ~/studio; find ~/studio -type f -exec md5sum {} \;')['chunks'], null));
}

// ---------------------------------------------------------------------------
// 1) Registr, balíčky, načtení bez varování
// ---------------------------------------------------------------------------

simb_check('reg:no-errors', lab58_registry_errors() === [], implode(' | ', lab58_registry_errors()));
simb_check('reg:no-warnings-on-load', $GLOBALS['simb_warnings'] === [], implode(' | ', $GLOBALS['simb_warnings']));
$reg = lab57_command_registry();
$missing = array_values(array_filter(SIMB_NEW, static fn(string $c): bool => !isset($reg[$c])));
simb_check('reg:new-commands-registered', $missing === [], implode(' ', $missing));
foreach (['imagemagick', 'libimage-exiftool-perl', 'rename', 'bc'] as $pkg) simb_check('reg:package-preinstalled:' . $pkg, isset(lab58_default_packages()[$pkg]));
simb_check('reg:no-sleep-override', lab58_command_meta('sleep') === null, 'sleep patří jádru v57');

// ---------------------------------------------------------------------------
// 2) Přesné výstupy, stderr, exit kódy a české tipy (každý případ v čerstvém pískovišti)
// ---------------------------------------------------------------------------

// [id, řádek, stdout, stderr (null = nekontrolovat přesně), exit, podřetězec tipu ('' = bez kontroly)]
$cases = [
    ['basename:suffix', 'basename /home/student/data/zaci.csv .csv', "zaci\n", '', 0],
    ['basename:multiple', 'basename -a a/b c/d/', "b\nd\n", '', 0],
    ['basename:missing-operand', 'basename', '', "basename: missing operand\nTry 'basename --help' for more information.\n", 1],
    ['dirname:basic', 'dirname /usr/bin/ zaci.csv', "/usr\n.\n", '', 0],
    ['realpath:canon', 'realpath data/../data/zaci.csv', "/home/student/data/zaci.csv\n", '', 0],
    ['realpath:missing', 'realpath -e chybi', '', "realpath: chybi: No such file or directory\n", 1, '→ man ls'],
    ['readlink:f', 'readlink -f /home/student/./data', "/home/student/data\n", '', 0],
    ['readlink:not-link', 'readlink vitej.txt', '', '', 1, 'readlink -f'],
    ['seq:basic', 'seq 3', "1\n2\n3\n", '', 0],
    ['seq:separator', 'seq -s, 2 2 10', "2,4,6,8,10\n", '', 0],
    ['seq:equal-width', 'seq -w 8 10', "08\n09\n10\n", '', 0],
    ['seq:float-step', 'seq 1 0.5 2', "1.0\n1.5\n2.0\n", '', 0],
    ['seq:invalid-number', 'seq x', '', "seq: invalid floating point argument: ‘x’\nTry 'seq --help' for more information.\n", 1, 'man seq'],
    ['seq:unknown-option', 'seq --bogus', '', "seq: unrecognized option '--bogus'\nTry 'seq --help' for more information.\n", 1, 'man seq'],
    ['paste:columns', 'seq 6 | paste - - -', "1\t2\t3\n4\t5\t6\n", '', 0],
    ['paste:serial', 'seq 3 | paste -sd+', "1+2+3\n", '', 0],
    ['column:table', 'printf \'a,bb\nccc,d\n\' | column -t -s,', "a    bb\nccc  d\n", '', 0],
    ['fold:width', 'echo abcdefghij | fold -w 4', "abcd\nefgh\nij\n", '', 0],
    ['comm:common', 'seq 5 > a.txt && seq 3 7 > b.txt && comm -12 a.txt b.txt', "3\n4\n5\n", '', 0],
    ['join:key', 'printf \'1 Adam\n2 Bara\n\' > j1.txt && printf \'1 3.A\n2 4.A\n\' > j2.txt && join j1.txt j2.txt', "1 Adam 3.A\n2 Bara 4.A\n", '', 0],
    ['split:lines', 'seq 25 > s.txt && split -l 10 s.txt kus_ && wc -l kus_ac', "5 kus_ac\n", '', 0],
    ['yes:default', 'yes | head -3', "y\ny\ny\n", '', 0],
    ['yes:text', 'yes ahoj | head -2', "ahoj\nahoj\n", '', 0],
    ['expr:add', 'expr 7 + 5', "12\n", '', 0],
    ['expr:integer-division', 'expr 17 / 5', "3\n", '', 0],
    ['expr:escaped-star', 'expr 5 \* 3', "15\n", '', 0],
    ['expr:division-by-zero', 'expr 1 / 0', '', "expr: division by zero\n", 2, 'man expr'],
    ['expr:false-is-exit-1', 'expr 3 = 4', "0\n", '', 1],
    ['expr:length', 'expr length ahoj', "4\n", '', 0],
    ['expr:missing-operand', 'expr', '', "expr: missing operand\nTry 'expr --help' for more information.\n", 2],
    ['factor:composite', 'factor 84', "84: 2 2 3 7\n", '', 0],
    ['factor:prime-and-one', 'factor 97 1', "97: 97\n1:\n", '', 0],
    ['factor:invalid', 'factor abc', '', "factor: ‘abc’ is not a valid positive integer\n", 1],
    ['whereis:ls', 'whereis ls', "ls: /usr/bin/ls /usr/share/man/man1/ls.1.gz\n", '', 0],
    ['lscpu:first-line', 'lscpu | head -1', "Architecture:            x86_64\n", '', 0],
    ['lsblk:header', 'lsblk | head -1', "NAME   MAJ:MIN RM  SIZE RO TYPE MOUNTPOINTS\n", '', 0],
    ['mount:list', 'mount | head -2', "sysfs on /sys type sysfs (rw,nosuid,nodev,noexec,relatime)\nproc on /proc type proc (rw,nosuid,nodev,noexec,relatime)\n", '', 0],
    ['time:stderr-report', 'time true', '', "\nreal\t0m0.002s\nuser\t0m0.000s\nsys\t0m0.001s\n", 0],
    ['watch:single-run', 'watch -n 5 df -h | head -1', "Every 5.0s: df -h                               lab-pc: Mon Sep 21 16:13:20 2026\n", '', 0, 'jen jednou'],
    ['pgrep:list', 'pgrep -l sshd', "180 sshd\n", '', 0],
    ['bc:scale', 'echo \'scale=2; 10/3\' | bc', "3.33\n", '', 0],
    ['bc:bignum', 'echo \'2^64\' | bc', "18446744073709551616\n", '', 0],
    ['bc:obase', 'echo \'obase=2; 200\' | bc', "11001000\n", '', 0],
    ['bc:ibase', 'echo \'ibase=16; FF\' | bc', "255\n", '', 0],
    ['bc:mathlib-sine', 'echo \'s(1)\' | bc -l', ".84147098480789650665\n", '', 0],
    ['bc:mathlib-pi', 'echo \'a(1)*4\' | bc -l', "3.14159265358979323844\n", '', 0],
    ['bc:sqrt', 'echo \'sqrt(2)\' | bc -l', "1.41421356237309504880\n", '', 0],
    ['bc:line-wrap-70', 'echo \'2^300\' | bc', "203703597633448608626844568840937816105146839366593625063614044935438\\\n1299763336706183397376\n", '', 0],
    ['bc:for-loop', 'echo \'for (i=1; i<=3; i++) i*i\' | bc', "1\n4\n9\n", '', 0],
    ['bc:post-increment', 'echo \'x=5; x++; x\' | bc', "5\n6\n", '', 0],
    ['bc:modulo-sign', 'echo \'-7 % 3\' | bc', "-1\n", '', 0],
    ['bc:divide-by-zero', 'echo \'1/0\' | bc', '', "Runtime error (func=(main), adr=1): Divide by zero\n", 0],
    ['bc:syntax-error', 'echo \'3 +\' | bc', '', "(standard_in) 2: syntax error\n", 1, 'man bc'],
    ['bc:unknown-option', 'bc --bogus', '', null, 1, 'man bc'],
    ['bc:loop-limit', 'echo \'while (1) { }\' | bc', '', null, 0],
    ['identify:png', 'identify obrazky/logo.png', "obrazky/logo.png PNG 64x64 64x64+0+0 8-bit sRGB 356B 0.000u 0:00.000\n", '', 0],
    ['identify:missing', 'identify chybi.png', '', "identify-im6.q16: unable to open image `chybi.png': No such file or directory @ error/blob.c/OpenBlob/2924.\n", 1, '→ man ls'],
    ['identify:not-image', 'identify vitej.txt', '', "identify-im6.q16: no decode delegate for this image format `TXT' @ error/constitute.c/ReadImage/575.\n", 1, 'man identify'],
    ['convert:resize-percent', 'convert obrazky/logo.png -resize 50% maly.png && identify -format \'%m %wx%h\n\' maly.png', "PNG 32x32\n", '', 0],
    ['convert:webp-quality', 'convert obrazky/logo.png -quality 80 logo.webp && identify -format \'%m %wx%h %Q\n\' logo.webp', "WEBP 64x64 80\n", '', 0],
    ['convert:pdf-policy', 'convert obrazky/logo.png x.pdf', '', "convert-im6.q16: attempt to perform an operation not allowed by the security policy `PDF' @ error/constitute.c/IsCoderAuthorized/421.\n", 1, 'policy.xml'],
    ['convert:unknown-option', 'convert -bogus obrazky/logo.png b.png', '', "convert-im6.q16: unrecognized option `-bogus' @ error/convert.c/ConvertImageCommand/3244.\n", 1, 'man convert'],
    ['convert:rotate-thumbnail', 'convert obrazky/logo.png -rotate 90 -thumbnail 32x32 t.jpg && identify -format \'%m %wx%h\n\' t.jpg', "JPEG 32x32\n", '', 0],
    ['mogrify:path', 'mkdir nah && mogrify -path nah -resize 50% obrazky/logo.png && identify -format \'%wx%h\n\' nah/logo.png obrazky/logo.png', "32x32\n64x64\n", '', 0],
    ['mogrify:format', 'mogrify -format jpg obrazky/logo.png && ls obrazky', "logo.jpg  logo.png\n", '', 0],
    ['mogrify:missing', 'mogrify -resize 50% chybi.jpg', '', "mogrify-im6.q16: unable to open image `chybi.jpg': No such file or directory @ error/blob.c/OpenBlob/2924.\n", 1, '→ man ls'],
    ['magick:webp-and-file', 'magick obrazky/logo.png -resize 25% m.webp && file m.webp', "m.webp: RIFF (little-endian) data, Web/P image, VP8 encoding, 16x16, Scaling: [none]x[none], YUV color, decoders should clamp\n", '', 0],
    ['file:png-filter', 'file obrazky/logo.png', "obrazky/logo.png: PNG image data, 64 x 64, 8-bit/color RGBA, non-interlaced\n", '', 0],
    ['exiftool:tags', 'exiftool -ImageWidth -ImageHeight obrazky/logo.png', "Image Width                     : 64\nImage Height                    : 64\n", '', 0],
    ['exiftool:missing', 'exiftool chybi.jpg', '', "Error: File not found - chybi.jpg\n", 1, '→ man ls'],
    ['exiftool:write-keeps-backup', 'convert obrazky/logo.png t.jpg && exiftool -Artist="Ema" t.jpg && ls t.jpg*', "    1 image files updated\nt.jpg  t.jpg_original\n", '', 0, '_original'],
    ['exiftool:all-cleared', 'convert obrazky/logo.png t.jpg && exiftool -Artist=Ema -overwrite_original t.jpg && exiftool -all= -overwrite_original t.jpg && exiftool -Artist t.jpg', "    1 image files updated\n    1 image files updated\n", '', 0],
    ['exiftool:json', 'exiftool -json -FileType obrazky/logo.png', "[{\n  \"SourceFile\": \"obrazky/logo.png\",\n  \"FileType\": \"PNG\"\n}]\n", '', 0],
    ['rename:dry-run', 'convert obrazky/logo.png c.png && rename -n \'s/\.png$/.PNG/\' *.png && ls c.*', "rename(c.png, c.PNG)\nc.png\n", '', 0],
    ['rename:perl-syntax-error', 'rename \'s/(\' *.txt', '', "Substitution pattern not terminated at (user-supplied code).\n", 255, 'man rename'],
    ['rename:transliterate', 'convert obrazky/logo.png maly.png && rename -v \'y/a-z/A-Z/\' maly.png', "maly.png renamed as MALY.PNG\n", '', 0],
    ['rename:unknown-option', 'rename --bogus x', '', "Unknown option: bogus\nUsage: rename [-h|-m|-V] [-v] [-0] [-n] [-f] [-d] [-u [enc]] [-e|-E perlexpr]*|perlexpr [files]\n", 2, 'man rename'],
];
foreach ($cases as $case) {
    [$id, $line, $out, $err, $exit] = $case;
    $tip = (string)($case[5] ?? '');
    [$o, $e, $x, $t] = simb_exec(simb_world(), $line);
    $ok = $o === $out && ($err === null || $e === $err) && $x === $exit && ($tip === '' || str_contains($t, $tip));
    simb_check('out:' . $id, $ok, json_encode(['out' => $o, 'err' => $e, 'exit' => $x, 'tip' => $t], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
[, $e] = simb_exec(simb_world(), 'bc --bogus');
simb_check('out:bc:unknown-option-text', str_starts_with($e, "bc: unrecognized option '--bogus'\nusage: bc [options] [file ...]\n"), $e);
[, $e] = simb_exec(simb_world(), 'echo \'while (1) { }\' | bc');
simb_check('out:bc:loop-stops', str_contains($e, 'Runtime error') && str_contains($e, '20000'), $e);
[$o, , $x] = simb_exec(simb_world(), 'echo \'define f(n) { return n }\' | bc');
simb_check('out:bc:define-explained', $x === 1, 'define simulace nepodporuje, musí skončit chybou');
$help = [];
foreach (SIMB_NEW as $cmd) {
    if ($cmd === 'time') continue; // klíčové slovo bashe, --help by bylo jméno příkazu
    [$o, , $x] = simb_exec(simb_world(), $cmd . ' --help');
    if ($x !== 0 || !str_contains($o, $cmd)) $help[] = $cmd;
}
simb_check('out:help-for-new-commands', $help === [], implode(' ', $help));

// Tipy jádra v57 odkazují na Příručku („→ man příkaz“)
$w = simb_world();
simb_check('tips:suggestion-man', str_contains(simb_exec($w, 'lss')[3], 'Nemyslel(a) jsi „less“? → man less'), simb_exec($w, 'lss')[3]);
simb_check('tips:error-man', str_contains(simb_exec($w, 'cat nic')[3], '→ man ls'));
simb_check('tips:windows-man', str_contains(simb_exec($w, 'ipconfig')[3], 'ip a → man ip'));
simb_check('tips:editor-man', str_contains(simb_exec($w, 'vim x')[3], '→ man nano'));

// ---------------------------------------------------------------------------
// 3) Determinismus
// ---------------------------------------------------------------------------

$script = ['shuf -i 1-100 -n 5', 'convert obrazky/logo.png -resize 200% -quality 70 velky.jpg', 'identify -verbose velky.jpg', 'exiftool velky.jpg', 'ls -l', 'echo \'scale=30; 4*a(1)\' | bc -l'];
$runA = [];
$runB = [];
$wa = simb_world('det');
$wb = simb_world('det');
foreach ($script as $line) { $runA[] = simb_exec($wa, $line); $runB[] = simb_exec($wb, $line); }
simb_check('det:same-seed-same-output', $runA === $runB);
$shuf = array_map(static fn(string $s): string => simb_exec(simb_world($s), 'shuf -i 1-100 -n 5')[0], ['d1', 'd2', 'd3']);
simb_check('det:seed-changes-shuf', count(array_unique($shuf)) > 1, implode(' / ', $shuf));
$nums = array_map('intval', explode("\n", trim($runA[0][0])));
simb_check('det:shuf-range', count($nums) === 5 && count(array_unique($nums)) === 5 && min($nums) >= 1 && max($nums) <= 100);

// ---------------------------------------------------------------------------
// 4) Obrázky přežijí mezi požadavky (overlay VFS, binární obsah)
// ---------------------------------------------------------------------------

$ctxFor = static fn(string $class, string $student, string $level): array => ['class' => $class, 'student' => $student, 'label' => 'Test Žák', 'context' => 'practice', 'level' => $level, 'now' => SIMB_NOW, 'cli' => true, 'classmates' => []];
$srun = static fn(array $ctx, string $line): array => lab57_session($ctx, 'run', ['line' => $line]);
$sout = static fn(array $r): string => simb_text((array)($r['chunks'] ?? $r['out'] ?? []), 1);
$cp = $ctxFor('class_1a', 'class_1a:student:simb-persist', 'sandbox');
$srun($cp, 'convert obrazky/logo.png -resize 50% -quality 70 maly.webp');
$srun($cp, 'exiftool -Artist=Ema -overwrite_original maly.webp');
simb_check('persist:image-format', $sout($srun($cp, 'identify -format \'%m %wx%h %Q\n\' maly.webp')) === "WEBP 32x32 70\n");
simb_check('persist:exif-tag', $sout($srun($cp, 'exiftool -Artist maly.webp')) === "Artist                          : Ema\n");
simb_check('persist:magic-bytes', str_contains($sout($srun($cp, 'file maly.webp')), 'Web/P image'));
$srun($cp, 'mogrify -format png maly.webp');
simb_check('persist:mogrify-result', $sout($srun($cp, 'identify -format \'%m %wx%h\n\' maly.png')) === "PNG 32x32\n");

// ---------------------------------------------------------------------------
// 5) Balíček „Terminál pro grafiky“
// ---------------------------------------------------------------------------

$pack = lab58_pack('grafika');
simb_check('pack:registered', is_array($pack) && ($pack['title'] ?? '') === 'Terminál pro grafiky');
simb_check('pack:classes-1a-2a', ($pack['classes'] ?? null) === ['class_1a', 'class_2a']);
simb_check('pack:not-for-3a', !lab58_pack_allows_class('grafika', 'class_3a') && lab58_pack_allows_class('grafika', 'class_2a'));
$raw = lab58_levels_grafika();
$levels = array_filter(lab58_extra_levels([]), static fn(array $l): bool => str_starts_with((string)$l['id'], 'grafika-'));
simb_check('levels:count', count($levels) >= 6 && count($levels) === count($raw), (string)count($levels));
$lab = ['submit', 'answer', 'check', 'hint', 'mise', 'reset'];
foreach ($raw as $rl) {
    $id = (string)$rl['id'];
    $errors = lab58_validate_level($rl + ['pack' => 'grafika']);
    simb_check('levels:valid:' . $id, $errors === [], implode(' | ', $errors));
    $hints = count((array)($rl['hints'] ?? []));
    $unknown = array_filter((array)$rl['commands'], static fn(string $c): bool => !isset($reg[$c]) && !in_array($c, $lab, true));
    $text = mb_strtolower($rl['story'] . ' ' . $rl['task']);
    $offTopic = preg_match('/\b(tar|zip|gzip|ping|router|ip adres|dns|traceroute)\b/u', $text) === 1 || array_intersect((array)$rl['commands'], ['tar', 'zip', 'unzip', 'gzip', 'ping', 'ip', 'dig']) !== [];
    simb_check('levels:content:' . $id, $hints >= 1 && $hints <= 3 && $unknown === [] && trim((string)$rl['learn']) !== '' && !$offTopic && is_array($rl['solution'] ?? null), "hints=$hints unknown=" . implode(',', $unknown));
}
foreach ($levels as $id => $lv) {
    $solved = [];
    $fresh = [];
    foreach (SIMB_SEEDS as $seed) {
        $res = lab58_try_solution($lv, $seed, SIMB_NOW);
        if (!$res['solved']) $solved[] = $seed;
        $w = lab57_build_world($lv, $seed, ['CODE' => 'EDU-TEST-0000'], SIMB_NOW);
        if ($lv['type'] === 'check' && array_filter(lab57_eval_checks($lv, $w), static fn(array $c): bool => !$c['ok']) === []) $fresh[] = $seed;
    }
    simb_check('levels:solvable-6-seeds:' . $id, $solved === [], 'nevyřešeno: ' . implode(',', $solved));
    simb_check('levels:not-solved-at-start:' . $id, $fresh === [], implode(',', $fresh));
    $snapA = simb_snapshot(lab57_build_world($lv, 's1', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW));
    $snapB = simb_snapshot(lab57_build_world($lv, 's1', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW));
    $snapC = simb_snapshot(lab57_build_world($lv, 's2', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW));
    simb_check('levels:deterministic-world:' . $id, $snapA === $snapB && $snapA !== $snapC);
    $t1 = lab58_try_solution($lv, 's3', SIMB_NOW);
    $t2 = lab58_try_solution($lv, 's3', SIMB_NOW);
    simb_check('levels:deterministic-transcript:' . $id, $t1['transcript'] === $t2['transcript']);
}

/** Najde semínko, jehož fakt se liší od s1 (pro test „cizí/stará odpověď neprojde“). */
$otherFact = static function (array $lv, string $fact): array {
    $base = lab57_build_world($lv, 's1', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW)->facts[$fact] ?? '';
    foreach (['s2', 's3', 's4', 's5', 's6', 's7', 's8', 's9'] as $seed) {
        $val = lab57_build_world($lv, $seed, ['CODE' => 'EDU-TEST-0000'], SIMB_NOW)->facts[$fact] ?? '';
        if ($val !== $base) return [$base, $val];
    }
    return [$base, $base];
};
$L = static fn(string $id): array => $levels[$id];

// grafika-1: přesun jen podle přípon nechá logo bez přípony v podkladech
simb_check('neg:grafika-1:extension-only', !simb_attempt($L('grafika-1'), 's1', ['cd ~/studio', 'mkdir obrazky texty', 'mv podklady/*.jpg podklady/*.png podklady/*.svg obrazky/', 'mv podklady/*.txt podklady/*.md texty/']));
simb_check('neg:grafika-1:texts-with-images', !simb_attempt($L('grafika-1'), 's1', ['cd ~/studio', 'mkdir obrazky texty', 'cp podklady/* obrazky/', 'mv podklady/* texty/']));
// grafika-2: cizí (jiné semínko) i špatná odpověď neprojdou
[$mine, $foreign] = $otherFact($L('grafika-2'), 'graf_banner');
simb_check('neg:grafika-2:foreign-answer', $mine !== $foreign && !simb_attempt($L('grafika-2'), 's1', ['answer ' . $foreign]), "$mine / $foreign");
simb_check('pos:grafika-2:own-answer', simb_attempt($L('grafika-2'), 's1', ['answer ' . strtoupper($mine)]));
// grafika-3: předpona z jiného semínka a přípona .JPG neprojdou
[$mine, $foreign] = $otherFact($L('grafika-3'), 'graf_prefix');
simb_check('neg:grafika-3:foreign-prefix', $mine !== $foreign && !simb_attempt($L('grafika-3'), 's1', ['cd ~/studio/akce', 'rename \'s/^IMG_(\d+)\.JPG$/' . $foreign . '-$1.jpg/\' *.JPG']), "$mine / $foreign");
simb_check('neg:grafika-3:upper-extension', !simb_attempt($L('grafika-3'), 's1', ['cd ~/studio/akce', 'rename \'s/IMG_/{f:graf_prefix}-/\' *.JPG']));
simb_check('pos:grafika-3:two-step-rename', simb_attempt($L('grafika-3'), 's1', ['cd ~/studio/akce', 'rename \'s/IMG_/{f:graf_prefix}-/\' *.JPG', 'rename \'s/\.JPG$/.jpg/\' *.JPG']));
// grafika-4: přesunout všechno je špatně
simb_check('neg:grafika-4:move-all', !simb_attempt($L('grafika-4'), 's1', ['mkdir -p ~/studio/k-uprave', 'mv ~/studio/web/img/* ~/studio/k-uprave/']));
// grafika-5: mogrify bez -path přepíše originály
simb_check('neg:grafika-5:overwrite-originals', !simb_attempt($L('grafika-5'), 's1', ['cd ~/studio/galerie', 'mkdir nahledy', 'mogrify -resize 50% *.jpg', 'cp *.jpg nahledy/']));
$w5 = lab57_build_world($L('grafika-5'), 's2', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW);
$perFile = array_map(static fn(string $n): string => "convert $n -resize 50% nahledy/$n", explode("\n", (string)($w5->facts['graf_gal'] ?? '')));
simb_check('pos:grafika-5:convert-per-file', count($perFile) >= 4 && simb_attempt($L('grafika-5'), 's2', array_merge(['cd ~/studio/galerie', 'mkdir nahledy'], $perFile)));
// grafika-6: bez -quality převezme WebP kvalitu z JPEG (> 80)
simb_check('neg:grafika-6:no-quality', !simb_attempt($L('grafika-6'), 's1', ['cd ~/studio/web/obrazky', 'mogrify -format webp *.jpg *.png']));
simb_check('neg:grafika-6:recompressed-originals', !simb_attempt($L('grafika-6'), 's1', ['cd ~/studio/web/obrazky', 'mogrify -quality 80 *.jpg', 'mogrify -format webp -quality 80 *.jpg *.png']));
// grafika-7: cizí kód a starý kód z poznámky neprojdou
simb_check('neg:grafika-7:foreign-code', !simb_attempt($L('grafika-7'), 's1', ['submit EDU-OTHR-9999'], 'EDU-TEST-0000'));
$w7 = lab57_build_world($L('grafika-7'), 's1', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW);
preg_match_all('/EDU-[A-Z0-9]{4}-[A-Z0-9]{4}/', simb_exec($w7, 'exiftool -UserComment ~/studio/archiv/*.jpg')[0], $decoys);
simb_check('neg:grafika-7:decoy-code', ($decoys[0] ?? []) !== [] && !in_array('EDU-TEST-0000', $decoys[0], true) && !simb_attempt($L('grafika-7'), 's1', ['submit ' . $decoys[0][0]]));
simb_check('neg:grafika-7:old-student-code', !simb_attempt($L('grafika-7'), 's1', ['cd ~/studio/archiv', 'submit EDU-TEST-0000'], 'EDU-NEWC-1234'));
// grafika-8: -all= smaže autora, bez -overwrite_original zůstanou zálohy
simb_check('neg:grafika-8:all-cleared', !simb_attempt($L('grafika-8'), 's1', ['cd ~/studio/zverejnit', 'exiftool -all= -overwrite_original *.jpg']));
simb_check('neg:grafika-8:backups-left', !simb_attempt($L('grafika-8'), 's1', ['cd ~/studio/zverejnit', 'exiftool -gps:all= *.jpg']));
simb_check('pos:grafika-8:backups-removed', simb_attempt($L('grafika-8'), 's1', ['cd ~/studio/zverejnit', 'exiftool -gps:all= *.jpg', 'rm *_original']));
$w8 = lab57_build_world($L('grafika-8'), 's1', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW);
simb_check('data:grafika-8:fictional-gps', str_contains(simb_exec($w8, 'exiftool -gps:all ~/studio/zverejnit/*.jpg')[0], 'GPS Latitude'));

// Celý tok přes lab57_session: 1.A vyřeší grafika-1 a odemkne grafika-2; 3.A balíček nevidí
$cg = $ctxFor('class_1a', 'class_1a:student:simb-flow', 'grafika-1');
$last = [];
foreach ((array)($L('grafika-1')['solution'])(lab57_build_world($L('grafika-1'), 'x', ['CODE' => 'EDU-TEST-0000'], SIMB_NOW)) as $line) $last = $srun($cg, $line);
$state = lab57_session($cg, 'state');
simb_check('flow:grafika-1-solved', !empty($state['solved']), json_encode(array_slice($state, 0, 3), JSON_UNESCAPED_UNICODE));
simb_check('flow:grafika-2-unlocked', !empty(lab57_session($ctxFor('class_1a', 'class_1a:student:simb-flow', 'grafika-2'), 'state')['ok']));
simb_check('flow:class-3a-denied', !empty(lab57_session($ctxFor('class_3a', 'class_3a:student:simb-flow', 'grafika-1'), 'state')['error']));

// Generátory snesou nesmyslné parametry z editoru učitele: žádné varování, strop počtu souborů, složky se nepřepíšou
$warnBefore = count($GLOBALS['simb_warnings']);
$wBad = simb_world('bad');
$genErrors = [];
foreach ([
    ['media_photos', ['dir' => '~/zlo', 'count' => [1, 100000], 'name' => ['x'], 'secret' => ['text' => ['x'], 'tag' => ['y']], 'decoy' => ['count' => 999], 'ctype' => ['rgb'], 'gps' => true, 'w' => [1, 99999], 'fact' => ['f']]],
    ['media_photos', ['dir' => '~/zlo2', 'names' => ['..', '.', '', ['a'], 'ok.png'], 'count' => 5, 'gps_place' => ['a', 'b'], 'gps' => true]],
    ['media_variants', ['dir' => '~/zlo3', 'names' => [['a'], '..', 'b.png', 'c.png'], 'decoys' => [[1, 2], 'x'], 'target' => 'bad', 'fact' => 'v']],
    ['media_text_files', ['dir' => '~', 'files' => ['a.txt' => ['x'], '..' => 'y'], 'fact' => ['f']]],
    ['media_pick', ['fact' => 'p', 'values' => [['a'], 'b']]],
    ['media_image', ['path' => '~', 'label' => ['y'], 'fmt' => ['PNG']]],
] as $gen) {
    try { lab58_run_generators($wBad, [$gen]); } catch (Throwable $e) { $genErrors[] = $gen[0] . ': ' . $e->getMessage(); }
}
$badFiles = count(array_filter($wBad->fs->children('/home/student/zlo'), static fn(string $n): bool => $wBad->fs->isFile('/home/student/zlo/' . $n)));
simb_check('robust:generators-bad-params', $genErrors === [] && count($GLOBALS['simb_warnings']) === $warnBefore, implode(' | ', array_merge($genErrors, array_slice($GLOBALS['simb_warnings'], $warnBefore, 3))));
simb_check('robust:file-cap', $badFiles >= 1 && $badFiles <= LAB58_GRAF_MAX_FILES, (string)$badFiles);
simb_check('robust:dirs-intact', $wBad->fs->isDir('/home/student') && $wBad->fs->isDir('/home/student/zlo2') && $wBad->fs->isFile('/home/student/zlo2/ok.png') && $wBad->fs->isDir('/home'));

// ---------------------------------------------------------------------------
// 6) Příručka: položka pro každý příkaz, tldr + see_also, funkční příklady, počet
// ---------------------------------------------------------------------------

$man = v57_manual();
$cmds = $man['commands'];
$aliases = ['.' => 'source', '[' => 'test'];
$noEntry = array_values(array_filter(array_keys($reg), static fn(string $c): bool => !isset($cmds[$aliases[$c] ?? $c])));
simb_check('manual:entry-for-every-command', $noEntry === [], implode(' ', $noEntry));
simb_check('manual:count>=130', count($cmds) >= 130, (string)count($cmds));
echo 'INFO manual commands=' . count($cmds) . ' registry=' . count($reg) . "\n";
$incomplete = [];
foreach (SIMB_NEW as $c) {
    $e = $cmds[$c] ?? [];
    if (empty($e['in_lab']) && !in_array($c, ['lsblk', 'mount'], true)) $incomplete[] = $c . ':in_lab';
    foreach (['summary', 'synopsis', 'about', 'cat'] as $k) if (trim((string)($e[$k] ?? '')) === '') $incomplete[] = $c . ':' . $k;
    if (count((array)($e['examples'] ?? [])) < 1 || count((array)($e['tldr'] ?? [])) < 1) $incomplete[] = $c . ':examples/tldr';
}
simb_check('manual:new-commands-complete', $incomplete === [], implode(' ', $incomplete));
$gaps = [];
$bad = [];
foreach ($cmds as $n => $c) {
    if (!empty($c['in_lab']) && (count((array)($c['tldr'] ?? [])) === 0 || count(array_merge((array)$c['related'], (array)($c['see_also'] ?? []))) === 0)) $gaps[] = $n;
    foreach (array_merge((array)$c['related'], (array)($c['see_also'] ?? [])) as $s) if (!isset($cmds[$s])) $bad[] = "$n→$s";
}
simb_check('manual:tldr-and-see-also-everywhere', $gaps === [], implode(' ', $gaps));
simb_check('manual:see-also-resolves', $bad === [], implode(' ', $bad));
simb_check('manual:categories', isset($man['categories']['grafika'], $man['categories']['vypocty']));
$broken = [];
$tested = 0;
foreach ($cmds as $n => $c) {
    if (empty($c['in_lab'])) continue;
    foreach (array_merge((array)$c['examples'], (array)($c['tldr'] ?? [])) as $ex) {
        $line = (string)($ex[0] ?? '');
        $first = (string)strtok($line, " \t");
        if ($line === '' || in_array($first, $lab, true)) continue;
        $w = lab57_build_world(lab57_sandbox_level(), 'simb|man|' . md5($line), ['CODE' => 'EDU-TEST-0000'], SIMB_NOW);
        foreach (array_unique([$first, (string)$n]) as $bin) {
            $pkg = isset($reg[$bin]) ? lab57_command_package($bin) : null;
            if ($pkg !== null) { $w->packages[$pkg] = '1'; $w->fs->set(lab57_command_bin($bin), ['t' => 'f', 'm' => 0755, 'u' => 'root', 'g' => 'root', 'mt' => 1, 'c' => 'x', 'x' => 'bin:' . $bin]); }
        }
        [, $e, $x] = simb_exec($w, $line);
        $tested++;
        if ($x === 127 || preg_match('/invalid option|unrecognized option|Unknown option|command not found|syntax error|missing operand/i', $e) === 1) $broken[] = "$n: $line (exit $x) " . trim($e);
    }
}
simb_check('manual:examples-run', $broken === [] && $tested > 300, "tested=$tested " . implode(' || ', array_slice($broken, 0, 5)));

// ---------------------------------------------------------------------------
// 7) Bezpečnost: token-sken vlastních souborů, veřejné IP jen RFC 5737, velikost souborů
// ---------------------------------------------------------------------------

$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'unserialize',
    'fsockopen', 'pfsockopen', 'stream_socket_client', 'stream_socket_server', 'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr', 'mail',
    'file_get_contents', 'file_put_contents', 'fopen', 'file', 'readfile', 'unlink', 'rename', 'copy', 'mkdir', 'rmdir', 'touch', 'chmod', 'symlink', 'tempnam', 'tmpfile',
    'call_user_func', 'call_user_func_array', 'forward_static_call', 'forward_static_call_array', 'extract', 'parse_str', 'header', 'setcookie'];
$violations = [];
$own = array_merge(SIMB_FILES, ['linux_v57_shell.php']);
foreach ($own as $f) {
    $src = (string)file_get_contents($ROOT . '/' . $f);
    $lines = substr_count($src, "\n");
    if ($f !== 'linux_v57_shell.php' && $lines > 800) $violations[] = "$f: $lines řádků";
    $tokens = token_get_all($src);
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        $tok = $tokens[$i];
        if ($tok === '`') $violations[] = "$f: zpětné apostrofy";
        if (!is_array($tok)) continue;
        if (in_array($tok[0], [T_EVAL, T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE], true)) $violations[] = "$f:{$tok[2]} " . $tok[1];
        if ($tok[0] !== T_STRING) continue;
        $name = strtolower($tok[1]);
        $j = $i + 1;
        while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        $p = $i - 1;
        while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) $p--;
        $method = $p >= 0 && is_array($tokens[$p]) && in_array($tokens[$p][0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true);
        if ($j < $n && $tokens[$j] === '(' && !$method && (in_array($name, $forbidden, true) || str_starts_with($name, 'socket_') || str_starts_with($name, 'curl_'))) $violations[] = "$f:{$tok[2]} $name()";
    }
    if (preg_match_all('/preg_replace(_callback)?\s*\(\s*\'(.)(?:(?!\2).)*\2([a-zA-Z]*)\'/', $src, $mm, PREG_SET_ORDER) > 0) {
        foreach ($mm as $m) if (str_contains($m[3], 'e')) $violations[] = "$f: preg_replace /e";
    }
    if ($f === 'linux_v57_shell.php') continue;
    // IPv4 literály (ne čísla verzí typu 8:6.9.11.60+dfsg)
    preg_match_all('/(?<![\w.:])(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})(?![\w.+-])/', $src, $ips, PREG_SET_ORDER);
    foreach ($ips as $ip) {
        $a = (int)$ip[1];
        $b = (int)$ip[2];
        $c = (int)$ip[3];
        $private = $a === 10 || $a === 127 || $a === 0 || ($a === 172 && $b >= 16 && $b <= 31) || ($a === 192 && $b === 168) || ($a === 169 && $b === 254) || $a >= 224;
        $doc = ($a === 192 && $b === 0 && $c === 2) || ($a === 198 && $b === 51 && $c === 100) || ($a === 203 && $b === 0 && $c === 113);
        if (!$private && !$doc && !in_array($ip[0], ['8.8.8.8', '1.1.1.1', '9.9.9.9', '1.0.0.1', '8.8.4.4'], true) && $a <= 223 && $ip[0] !== '1.07.1') $violations[] = "$f: veřejná IP " . $ip[0];
    }
}
simb_check('safety:own-files-token-scan', $violations === [], implode('; ', array_slice($violations, 0, 8)));
simb_check('safety:no-warnings-during-audit', $GLOBALS['simb_warnings'] === [], implode(' | ', array_slice($GLOBALS['simb_warnings'], 0, 5)));

$ok = $GLOBALS['simb']['failed'] === 0;
echo ($ok ? 'V58_SIM_B_AUDIT_OK' : 'V58_SIM_B_AUDIT_FAIL') . ' checks=' . $GLOBALS['simb']['checks'] . ' failed=' . $GLOBALS['simb']['failed'] . "\n";
exit($ok ? 0 : 1);
