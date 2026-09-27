<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Robotí liga – CLI audit (jazyk, simulace, férovost, izolace, výkon, soukromí, úložiště, XP, CSRF).
 * Běží nad dočasným úložištěm – nikdy nesahá na storage/. Spuštění: php tools/v58_robots_audit.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$tmp = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet_robots58_audit_' . bin2hex(random_bytes(5));
if (!mkdir($tmp, 0770, true) && !is_dir($tmp)) { fwrite(STDERR, "Nelze vytvořit dočasné úložiště.\n"); exit(1); }
$GLOBALS['robots58_storage_override'] = $tmp;

/** Testovací dvojník storage_update z bootstrap.php (stejná sémantika: jeden LOCK_EX přes čtení i zápis). */
function storage_update(string $path, callable $mutate): array
{
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0770, true);
    $fp = fopen($path, 'c+');
    if ($fp === false) throw new RuntimeException('open');
    try {
        flock($fp, LOCK_EX);
        $raw = (string)stream_get_contents($fp);
        $data = json_decode(trim(preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? ''), true);
        $data = $mutate(is_array($data) ? $data : []);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, "<?php http_response_code(403); exit; ?>\n" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }
    return $data;
}

$GLOBALS['audit_xp'] = [];
function learning_award_once(string $classId, string $eventKey, int $xp): bool
{
    $key = $classId . '|' . $eventKey;
    if (isset($GLOBALS['audit_xp'][$key])) return false;
    $GLOBALS['audit_xp'][$key] = $xp;
    return true;
}

require_once $root . '/robots_v58.php';
require_once $root . '/robots_v58_views.php';

$checks = 0;
$failed = 0;
function check(bool $ok, string $label, string $detail = ''): void
{
    global $checks, $failed;
    $checks++;
    if (!$ok) $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . ($ok || $detail === '' ? '' : ' – ' . $detail) . PHP_EOL;
}

function parse_err(string $src): ?array
{
    return robots58_parse($src, false)['error'];
}

/** Jeden tah skriptu mimo svět (senzory vrací none). */
function run_once(string $src, array $mem = []): array
{
    $p = robots58_parse($src);
    if (!$p['ok']) return ['status' => 'syntax', 'error' => $p['error']];
    $c = new Robots58Ctx();
    $c->g = $mem;
    $c->kept = array_fill_keys(array_keys($mem), true);
    $res = robots58_run_turn($p['program'], $c);
    $res['g'] = $c->g;
    $res['say'] = $c->say;
    $res['steps'] = $c->steps;
    $res['mem'] = array_intersect_key($c->g, $c->kept);
    return $res;
}

function rm_tree(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

$examples = robots58_examples();

// ---------------------------------------------------------------------------
// 1. Bezpečnostní invariant: token-sken vlastních souborů
// ---------------------------------------------------------------------------
$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'assert', 'create_function', 'fsockopen', 'pfsockopen', 'stream_socket_client', 'stream_socket_server', 'gethostbyname', 'gethostbynamel', 'gethostbyaddr', 'dns_get_record', 'checkdnsrr', 'getmxrr', 'mail', 'curl_init', 'curl_exec', 'socket_create', 'unserialize'];
$phpFiles = ['robots_v58.php', 'robots_v58_lang.php', 'robots_v58_game.php', 'robots_v58_views.php', 'robots_v58_api.php', 'tools/v58_robots_audit.php'];
foreach ($phpFiles as $file) {
    $src = (string)file_get_contents($root . '/' . $file);
    $tokens = token_get_all($src);
    $bad = [];
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $t = $tokens[$i];
        if ($t === '`') { $bad[] = 'backtick'; continue; }
        if (!is_array($t)) continue;
        if ($t[0] === T_EVAL) $bad[] = 'eval';
        if ($t[0] === T_CONSTANT_ENCAPSED_STRING && preg_match('#^[\'"](https?|ftp|php|data|phar)://#i', $t[1])) $bad[] = 'URL ' . $t[1];
        if ($t[0] !== T_STRING) continue;
        $name = strtolower($t[1]);
        $j = $i + 1;
        while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        if (($tokens[$j] ?? null) !== '(') continue;
        $k = $i - 1;
        while ($k >= 0 && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k--;
        $prev = $tokens[$k] ?? null;
        if (is_array($prev) && in_array($prev[0], [T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NEW], true)) continue;
        if (in_array($name, $forbidden, true) || str_starts_with($name, 'curl_') || str_starts_with($name, 'socket_')) $bad[] = $name;
    }
    check($bad === [], 'invariant: ' . $file . ' nevolá exec/eval/sockety/síť/zpětné apostrofy', implode(', ', array_unique($bad)));
    $lines = substr_count($src, "\n") + 1;
    check($lines <= 800 || $file === 'robots_v58_lang.php' || $file === 'tools/v58_robots_audit.php', 'velikost: ' . $file . ' má ' . $lines . ' řádků (limit 800, výjimka jen interpret a audit)');
    check(str_contains($src, 'declare(strict_types=1);'), 'konvence: ' . $file . ' má strict_types');
}
foreach (['robots_v58.php', 'robots_v58_lang.php', 'robots_v58_game.php', 'robots_v58_views.php'] as $file) {
    check(str_contains((string)file_get_contents($root . '/' . $file), "basename((string)(\$_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)"), 'konvence: ' . $file . ' má guard proti přímému spuštění');
}
$js = (string)file_get_contents($root . '/assets/robots-v58.js');
check(preg_match('/innerHTML|outerHTML|insertAdjacentHTML|document\.write|\beval\s*\(|new Function|`/', $js) !== 1, 'JS: žádné innerHTML/eval/new Function/template literály');
check(!preg_match('#https?://(?!www\.w3\.org/2000/svg)#', $js . (string)file_get_contents($root . '/assets/robots-v58.css')), 'assety: žádné externí URL (CDN, analytika)');
check(str_contains((string)file_get_contents($root . '/assets/robots-v58.css'), 'prefers-reduced-motion'), 'CSS: respektuje prefers-reduced-motion');

// ---------------------------------------------------------------------------
// 2. Gramatika a chybové hlášky (řádek + tip, česky)
// ---------------------------------------------------------------------------
foreach ($examples as $id => $ex) {
    $p = robots58_parse($ex['text']);
    check($p['ok'] && $p['warnings'] === [], 'gramatika: ukázka „' . $id . '“ je platná', $p['ok'] ? '' : robots58_error_text($p['error']));
}
$cases = [
    ["if cargo > 0\n    drop", 1, 'dvojtečka'],
    ["x = 1\nif x = 3:\n    pick", 2, 'Pro porovnání piš =='],
    ["wait\n  move up", 2, 'odsazený'],
    ["if here == \"packet\":\npick", 2, 'chybí odsazený blok'],
    ["move up\nmove sever", 2, 'Neznám jméno'],
    ["x = nearst(\"packet\")\nwait", 1, 'Nemyslel(a) jsi „nearest“'],
    ["say \"ahoj\nwait", 1, 'Neukončený text'],
    ["x = 2.5\nwait", 1, 'Desetinná'],
    ["wait\nwait\nelse:\n    wait", 3, 'nemá své if'],
    ["If cargo > 0:\n    drop", 1, 'malými písmeny'],
    ["break", 1, 'jen dovnitř smyčky'],
    ["return 1", 1, 'jen dovnitř funkce'],
    ["energy = 5\nwait", 1, 'senzor'],
    ["cíl = 1", 1, 'bez diakritiky'],
    ["move up; move down", 1, 'středník'],
    ["def f(a):\n    return a\nx = f(1, 2)\nwait", 3, 'parametr'],
    ["if x:\n        wait\n    wait", 3, 'Odsazení nesedí'],
    ["x = (1 + 2\nwait", 1, 'závorka'],
    ["print(\"a\")", 1, 'say'],
    ["if cargo > 0:\n    drop\nelse if cargo == 0:\n    wait", 3, 'elif'],
];
foreach ($cases as [$src, $line, $needle]) {
    $err = parse_err($src);
    $text = $err !== null ? robots58_error_text($err) : '';
    check($err !== null && (int)$err['line'] === $line && str_contains($text, $needle) && str_contains($text, 'Řádek ' . $line . ':'), 'hláška: „' . str_replace("\n", '⏎', mb_substr($src, 0, 28)) . '“ → řádek ' . $line, $text);
}
$tipless = 0;
foreach ($cases as [$src]) { $err = parse_err($src); if ($err !== null && trim((string)$err['tip']) === '' && !str_contains((string)$err['message'], 'Nemyslel')) $tipless++; }
check($tipless <= 2, 'hlášky: skoro každá má tip, jak chybu opravit', $tipless . ' bez tipu');
check(robots58_parse("wait\n")['warnings'] === [] && robots58_parse("x = 1\n")['warnings'] !== [], 'varování: skript bez akce dostane upozornění');

// ---------------------------------------------------------------------------
// 3. Sémantika interpretu
// ---------------------------------------------------------------------------
$r = run_once("a = 7 / 2\nb = -7 / 2\nc = -7 % 3\nd = \"x\" + 5\ne = 2 + 3 * 4\nf = (2 + 3) * 4\nwait");
check($r['status'] === 'action' && $r['g'] === ['a' => 3, 'b' => -4, 'c' => 2, 'd' => 'x5', 'e' => 14, 'f' => 20], 'sémantika: aritmetika (celočíselné dělení, modulo, priorita, spojení textu)', json_encode($r['g']));
$r = run_once("def dvakrat(n):\n    return n * 2\ni = 0\nsoucet = 0\nwhile true:\n    i += 1\n    if i > 10:\n        break\n    if i % 2 == 0:\n        continue\n    soucet += dvakrat(i)\nrepeat 3:\n    soucet -= 1\nmove left");
check($r['status'] === 'action' && $r['action'][0] === 'move' && $r['action'][1] === 'left' && $r['g']['soucet'] === 47, 'sémantika: funkce, return, while/break/continue, repeat', json_encode($r['g']));
$r = run_once("def f(n):\n    return f(n + 1)\nx = f(0)\nwait");
check($r['status'] === 'error' && str_contains($r['error']['message'], 'hluboko') && $r['error']['line'] === 2, 'limit: hloubka volání (rekurze) se zastaví s hláškou');
$r = run_once("x = \"a\"\nrepeat 200:\n    x = x + \"a\"\nwait");
check($r['status'] === 'error' && str_contains($r['error']['message'], '100 znaků'), 'limit: délka řetězce 100 znaků');
$r = run_once("x = 1000000000 * 10\nwait");
check($r['status'] === 'error' && str_contains($r['error']['message'], 'moc velké'), 'limit: přetečení čísla');
$r = run_once("keep n = 5\nn += 1\nwait");
$r2 = run_once("keep n = 5\nn += 1\nwait", $r['mem']);
check($r['mem'] === ['n' => 6] && $r2['mem'] === ['n' => 7], 'paměť: keep přežije mezi tahy a inicializuje se jen jednou', json_encode([$r['mem'], $r2['mem']]));
$r = run_once("say \"Ahoj světe, tohle je moc dlouhá zpráva na jednu bublinu\"\nwait");
check($r['say'] !== null && mb_strlen($r['say']) === 40, 'say: zpráva se zkrátí na 40 znaků');
check(robots58_say_clean('ty kurvo')[1] && robots58_say_clean('K.O.K.O.T')[1] && robots58_say_clean('f u c k')[1] && !robots58_say_clean('Jedu nabíjet, pak sbírám')[1] && !robots58_say_clean('epický tropický picasso')[1], 'say: filtr skryje nevhodná slova a nechá běžný text');

// ---------------------------------------------------------------------------
// 4. Rozpočet kroků a limity skriptu
// ---------------------------------------------------------------------------
$t0 = hrtime(true);
$r = run_once("while true:\n    x = 1");
$ms = (hrtime(true) - $t0) / 1e6;
check($r['status'] === 'budget' && $r['steps'] === ROBOTS58_STEP_BUDGET + 1 && $ms < 200, 'rozpočet: nekonečná smyčka se zastaví po ' . ROBOTS58_STEP_BUDGET . ' krocích (' . round($ms, 1) . ' ms)');
$r = run_once("def f():\n    while true:\n        pass\nf()\nwait");
check($r['status'] === 'budget', 'rozpočet: nekonečná smyčka uvnitř funkce se také zastaví');
check(str_contains((string)parse_err(str_repeat("wait\n", 900))['message'], '4 KB'), 'limit: skript nad 4 KB se odmítne');
check(str_contains((string)parse_err(str_repeat("#\n", 201) . "wait")['message'], '200 řádků'), 'limit: nejvýš 200 řádků');
$deep = '';
for ($i = 0; $i < 9; $i++) $deep .= str_repeat('    ', $i) . "if true:\n";
$deep .= str_repeat('    ', 9) . "wait\n";
check(str_contains((string)parse_err($deep)['message'], 'vnoření'), 'limit: vnoření bloků max ' . ROBOTS58_MAX_DEPTH);
$vars = '';
for ($i = 0; $i < 33; $i++) $vars .= 'v' . $i . " = 1\n";
check(str_contains((string)parse_err($vars . 'wait')['message'], 'proměnných'), 'limit: nejvýš ' . ROBOTS58_MAX_VARS . ' proměnných');
$keeps = '';
for ($i = 0; $i < 17; $i++) $keeps .= 'keep k' . $i . " = 1\n";
check(str_contains((string)parse_err($keeps . 'wait')['message'], 'keep'), 'limit: paměť robota nejvýš ' . ROBOTS58_MAX_KEEP . ' proměnných');
check(str_contains((string)parse_err('x = ' . str_repeat('(', 30) . '1' . str_repeat(')', 30) . "\nwait")['message'], 'složitý'), 'limit: hloubka výrazu');

// ---------------------------------------------------------------------------
// 5. Mapa, determinismus a férovost
// ---------------------------------------------------------------------------
$symOk = true;
for ($seed = 1; $seed <= 25; $seed++) {
    $map = robots58_map($seed * 7919);
    for ($y = 0; $y < 16 && $symOk; $y++) for ($x = 0; $x < 24 && $symOk; $x++) {
        $c = $map['grid'][$y * 24 + $x];
        if ($c !== $map['grid'][$y * 24 + 23 - $x] || $c !== $map['grid'][(15 - $y) * 24 + $x]) $symOk = false;
    }
    foreach (array_chunk($map['packets'], 4) as $g) {
        [$a, $b, $cc, $d] = array_map(static fn(int $i): array => [$i % 24, intdiv($i, 24)], $g);
        if ($b !== [23 - $a[0], $a[1]] || $cc !== [$a[0], 15 - $a[1]] || $d !== [23 - $a[0], 15 - $a[1]]) $symOk = false;
    }
    if (substr_count($map['grid'], '+') !== 4 || substr_count($map['grid'], 'B') !== 16) $symOk = false;
}
check($symOk, 'férovost: 25 map je zrcadlových podle obou os (regály, nabíječky, základny, balíčky)');
$entries = [];
for ($i = 0; $i < 8; $i++) $entries[] = ['code' => $examples[['start', 'collector', 'repair', 'memory'][$i % 4]]['text'], 'corner' => $i % 4];
$a = robots58_simulate(4242, 200, $entries);
$b = robots58_simulate(4242, 200, $entries);
$c = robots58_simulate(4243, 200, $entries);
check($a['hash'] === $b['hash'] && $a['scores'] === $b['scores'], 'determinismus: stejné semínko → stejný hash záznamu (' . substr($a['hash'], 0, 12) . ')');
check($a['hash'] !== $c['hash'], 'determinismus: jiné semínko → jiný zápas');
$single = [];
foreach ([0, 1, 2, 3] as $corner) $single[] = robots58_simulate(99, 300, [['code' => $examples['memory']['text'], 'corner' => $corner]])['scores'][0];
check(count(array_unique($single)) === 1 && $single[0] > 0, 'férovost: stejný skript sám v kterémkoli rohu získá stejné body (' . implode('/', $single) . ')');
$four = robots58_simulate(1234, 300, array_map(static fn(int $c): array => ['code' => $GLOBALS['examples']['collector']['text'], 'corner' => $c], [0, 1, 2, 3]));
$fc = $four['first_count'];
check(min($fc) >= 45 && max($fc) <= 110 && array_sum($fc) === 300, 'férovost: pořadí robotů se losuje – první na tahu ' . implode('/', $fc) . ' z 300');
$avg = array_sum($four['scores']) / 4;
check($avg > 0 && min($four['scores']) >= $avg * 0.5, 'férovost: 4 stejné skripty v rozích skončí vyrovnaně (' . implode('/', $four['scores']) . ')');

// ---------------------------------------------------------------------------
// 6. Izolace robotů
// ---------------------------------------------------------------------------
$spy = "keep tajne = 0\nsay \"t=\" + tajne\nwait";
$owner = "keep tajne = 42\ntajne += 1\nsay \"mam \" + tajne\nwait";
$iso = robots58_simulate(5, 5, [['code' => $owner, 'corner' => 0], ['code' => $spy, 'corner' => 3]]);
$said = [];
foreach ($iso['replay']['d'] as $delta) foreach ($delta[3] as [$i, $text]) $said[$i][] = $text;
check(in_array('t=0', $said[1] ?? [], true) && !in_array('t=43', $said[1] ?? [], true) && in_array('mam 43', $said[0] ?? [], true), 'izolace: robot nevidí paměť jiného robota (stejně pojmenovaná keep je jeho vlastní)', json_encode($said, JSON_UNESCAPED_UNICODE));
check(parse_err("x = tajne\nwait") !== null, 'izolace: cizí proměnnou nejde ani přečíst (neznámé jméno)');
$alone = robots58_simulate(77, 200, [['code' => $examples['collector']['text'], 'corner' => 0]])['stats'][0]['points'];
$withChaos = robots58_simulate(77, 200, [['code' => $examples['collector']['text'], 'corner' => 0], ['code' => "while true:\n    x = 1", 'corner' => 3], ['code' => "y = 1 / 0\nwait", 'corner' => 1]]);
check($withChaos['stats'][0]['points'] === $alone && $withChaos['stats'][1]['budget'] > 0 && $withChaos['stats'][2]['errors'] > 0, 'izolace: chybující a zacyklené skripty neovlivní cizího robota (' . $alone . ' b)');
$cool = $withChaos['stats'][1];
check($cool['budget'] < 120 && str_contains(json_encode($cool['issues'], JSON_UNESCAPED_UNICODE), 'přehřál'), 'rozpočet: 3× po sobě přes limit → robot chladne (přetečení ' . $cool['budget'] . '× za 200 tahů)');
$lazy = robots58_simulate(3, 60, [['code' => "wait", 'corner' => 0], ['code' => "step_to point(0, 0)", 'corner' => 3]]);
check($lazy['stats'][1]['blocked'] > 0 && $lazy['scores'] === [0, 0], 'pravidla: do cizí základny se nevjede, nic se neukradne');

// ---------------------------------------------------------------------------
// 7. Výkon
// ---------------------------------------------------------------------------
$class30 = [];
for ($i = 0; $i < 30; $i++) $class30[] = ['code' => $examples[['start', 'collector', 'repair', 'memory'][$i % 4]]['text'], 'corner' => $i % 4];
$perf = robots58_simulate(2026, 300, $class30);
check($perf['ms'] < 1000, 'výkon: 30 skriptů × 300 tahů = ' . $perf['ms'] . ' ms (PHP ' . PHP_VERSION . ', limit 1000 ms)');
$worst = robots58_simulate(2026, 300, array_map(static fn(int $i): array => ['code' => "while true:\n    x = 1", 'corner' => $i % 4], range(0, 29)));
check($worst['ms'] < 1000, 'výkon: nejhorší případ 30 nekonečných smyček × 300 tahů = ' . $worst['ms'] . ' ms (díky přehřátí)');
check(strlen((string)json_encode($perf['replay'])) < 400000, 'záznam: kompaktní (klíčové snímky + změny) – ' . round(strlen((string)json_encode($perf['replay'])) / 1024) . ' KB pro 30 robotů × 300 tahů');

// ---------------------------------------------------------------------------
// 8. Zápas: úložiště, odevzdání, týmy, simulace, liga, XP, soukromí
// ---------------------------------------------------------------------------
$class = 'class_3a';
$roster = [];
$names = ['Adam Novák', 'Bára Svobodová', 'Cyril Dvořák', 'Dana Černá', 'Emil Procházka', 'Filip Kučera', 'Gábina Veselá', 'Hugo Horák'];
foreach ($names as $n) $roster['k_' . substr(sha1($n), 0, 10)] = ['label' => $n];
$GLOBALS['robots58_roster_override'][$class] = $roster;
$keys = array_keys($roster);
$T0 = strtotime('2026-10-05 09:00:00');
$GLOBALS['robots58_now_override'] = $T0;
$_SESSION = ['csrf' => 'tok-audit-123'];

check(robots58_csrf_ok('tok-audit-123') && !robots58_csrf_ok('') && !robots58_csrf_ok(null) && !robots58_csrf_ok('tok-audit-12') && !robots58_csrf_ok(['tok-audit-123']), 'CSRF: API i učitel přijmou jen přesný token ze session (hash_equals)');
$api = (string)file_get_contents($root . '/robots_v58_api.php');
check(strpos($api, 'robots58_csrf_ok(') !== false && strpos($api, 'robots58_csrf_ok(') < strpos($api, 'current_class_id(') && strpos($api, 'robots58_csrf_ok(') < strpos($api, 'switch ($op)') && str_contains($api, "!== 'POST'"), 'CSRF: API ověřuje POST a token dřív, než zjistí identitu a provede operaci');
check(str_contains($api, 'adaptive_student_key($classId)') && !preg_match('/\$_(POST|GET|REQUEST)\[\'(student|student_key|class|class_id)\'\]/', $api), 'API: identita žáka jen ze session, nikdy z parametru');
$_POST = ['csrf' => 'spatny', 'action' => 'robots58_create', 'class_id' => $class];
$thrown = '';
try { robots58_teacher_handle_post('robots58_create', [$class => []]); } catch (RuntimeException $e) { $thrown = $e->getMessage(); }
check(str_contains($thrown, 'vypršel') && robots58_matches() === [], 'CSRF: učitelská akce bez platného tokenu se odmítne a nic nezaloží');

$solo = robots58_create_match(['class_id' => $class, 'title' => 'Sólo test', 'mode' => 'solo', 'turns' => '150', 'names' => 'initials', 'deadline' => date('Y-m-d\TH:i', $T0 + 3600)], [$class], $T0);
$head = (string)file_get_contents(robots58_path());
check(str_starts_with($head, '<?php http_response_code(403); exit; ?>') && robots58_status($solo, $T0) === 'open' && robots58_valid_id($solo['id']), 'úložiště: robots_v58.json.php má ochranný řádek, zápas je v přípravě');
$empty = '';
try { robots58_run_match($solo['id'], $T0 + 1); } catch (RuntimeException $e) { $empty = $e->getMessage(); }
check(str_contains($empty, 'nikdo neodevzdal') && robots58_status(robots58_match($solo['id']), $T0 + 2) === 'open', 'simulace: bez odevzdání se nespustí a odevzdávání zůstane otevřené');
check(!robots58_submit($class, $keys[0], $names[0], $solo['id'], "move nahoru", $T0)['ok'], 'odevzdání: skript s chybou se odmítne');
robots58_submit($class, $keys[0], $names[0], $solo['id'], $examples['start']['text'], $T0 + 10);
robots58_submit($class, $keys[0], $names[0], $solo['id'], "if cargo = 1:\n    wait", $T0 + 20);
$sub = robots58_submit($class, $keys[0], $names[0], $solo['id'], $examples['memory']['text'], $T0 + 30);
check($sub['ok'] && $sub['n'] === 2 && robots58_submissions($solo['id'])[$keys[0]]['code'] === $examples['memory']['text'], 'odevzdání: platí poslední platný skript (pokus č. 2)');
foreach ([1, 2, 3, 4] as $i) robots58_submit($class, $keys[$i], $names[$i], $solo['id'], $examples[['start', 'collector', 'repair', 'memory'][$i % 4]]['text'], $T0 + 40);
$v1 = robots58_poll_status(robots58_match($solo['id']), $T0 + 50)['version'];
robots58_submit($class, $keys[5], $names[5], $solo['id'], $examples['collector']['text'], $T0 + 60);
$v2 = robots58_poll_status(robots58_match($solo['id']), $T0 + 70)['version'];
check($v1 !== $v2 && $v2 === robots58_poll_status(robots58_match($solo['id']), $T0 + 80)['version'], 'polling: verze se mění jen se změnou stavu (jinak {changed:false})');
$late = robots58_submit($class, $keys[6], $names[6], $solo['id'], $examples['start']['text'], $T0 + 3601);
check(!$late['ok'] && robots58_status(robots58_match($solo['id']), $T0 + 3601) === 'closed', 'uzávěrka: po termínu se odevzdat nedá');
$ran = robots58_run_match($solo['id'], $T0 + 3700);
check(robots58_status($ran, $T0 + 3700) === 'finished' && (int)$ran['results']['count'] === 6 && str_starts_with((string)file_get_contents(robots58_path('replay', $solo['id'])), '<?php http_response_code(403)'), 'simulace: zápas odehrán, výsledky a záznam uloženy (' . $ran['results']['ms'] . ' ms)');
$again = '';
try { robots58_run_match($solo['id'], $T0 + 3800); } catch (RuntimeException $e) { $again = $e->getMessage(); }
check($again !== '', 'simulace: odehraný zápas nejde spustit znovu');
$rerun = robots58_simulate((int)$ran['seed'], (int)$ran['turns'], array_map(static fn(array $r): array => ['code' => $r['code'], 'corner' => $r['corner']], robots58_match_robots($ran, robots58_submissions($solo['id']))));
check($rerun['hash'] === $ran['results']['hash'], 'determinismus: uložený zápas jde kdykoli přepočítat na stejný hash');

$teams = robots58_create_match(['class_id' => $class, 'title' => 'Týmy test', 'mode' => 'teams', 'teams' => '4', 'turns' => '100', 'names' => 'anon', 'deadline' => date('Y-m-d\TH:i', $T0 + 7200)], [$class], $T0 + 4000);
$sizes = array_map(static fn(array $t): int => count($t['members']), $teams['teams']);
$corners = array_column($teams['teams'], 'corner');
sort($corners);
check(count($teams['teams']) === 4 && max($sizes) - min($sizes) <= 1 && array_sum($sizes) === 8 && $corners === [0, 1, 2, 3], 'týmy: 4 vyvážené týmy (' . implode('/', $sizes) . '), každý ve svém rohu');
$GLOBALS['robots58_roster_override'][$class]['k_novacek'] = ['label' => 'Ivo Nováček'];
foreach (array_merge($keys, ['k_novacek']) as $i => $key) robots58_submit($class, $key, $names[$i] ?? 'Ivo Nováček', $teams['id'], $examples[['collector', 'repair'][$i % 2]]['text'], $T0 + 4100);
$teamsNow = robots58_match($teams['id']);
check(robots58_team_of($teamsNow, 'k_novacek') !== null, 'týmy: žák mimo původní soupisku se při odevzdání přidá do nejmenšího týmu');
$tRan = robots58_run_match($teams['id'], $T0 + 4200);
$teamSum = array_sum(array_column($tRan['results']['teams'], 'points'));
check($teamSum === array_sum(array_column($tRan['results']['robots'], 'points')) && $teamSum > 0, 'týmy: body týmu = součet jeho robotů');

// Soukromí
$pubAnon = robots58_replay_payload($teams['id'], null);
$pubInit = robots58_replay_payload($solo['id'], null);
$blobAnon = (string)json_encode($pubAnon, JSON_UNESCAPED_UNICODE);
$blobInit = (string)json_encode($pubInit, JSON_UNESCAPED_UNICODE);
$leak = static function (string $blob, bool $initialsOk) use ($names, $keys): bool {
    foreach ($keys as $k) if (str_contains($blob, $k)) return true;
    foreach ($names as $n) {
        [$first, $last] = explode(' ', $n);
        if (str_contains($blob, $n) || str_contains($blob, $last) || (!$initialsOk && str_contains($blob, $first))) return true;
    }
    return false;
};
check(!$leak($blobAnon, false) && str_contains($blobAnon, 'Robot 1'), 'soukromí: projektor v anonymním režimu – žádná jména ani klíče žáků');
check(!$leak($blobInit, true) && str_contains($blobInit, 'Adam N.'), 'soukromí: projektor v režimu iniciál – „Adam N.“, nikdy celé příjmení ani klíč');
ob_start();
robots58_render_projector($teams['id']);
$html = (string)ob_get_clean();
check(!$leak($html, false) && str_contains($html, 'data-rb58-replay-json') && !str_contains($html, 'Kód vidíš'), 'soukromí: HTML projektoru (anonymně) neobsahuje jména, klíče ani skripty');
check($pubAnon['all_stats'] === null && $pubAnon['me'] === null && !str_contains($blobAnon, 'keep ') && !str_contains($blobAnon, 'step_to'), 'izolace: veřejný záznam neobsahuje skripty ani statistiky jednotlivců');
$mine = robots58_replay_payload($solo['id'], $keys[0]);
$otherNames = array_values(array_filter(array_column($mine['replay']['robots'], 'name'), static fn(string $n): bool => $n !== 'Adam N.'));
check($mine['me'] !== null && is_array($mine['me']['stats']) && $mine['all_stats'] === null && $otherNames !== [], 'soukromí: žák dostane jen své vlastní statistiky a chyby');
$state = robots58_student_state($class, $keys[1], $T0 + 5000);
$stateBlob = (string)json_encode($state, JSON_UNESCAPED_UNICODE);
check(!str_contains($stateBlob, 'k_') && !str_contains($stateBlob, 'Novák') && !str_contains($stateBlob, 'if here'), 'soukromí: stav pro žáka neobsahuje klíče, příjmení spolužáků ani cizí skripty');
$teacher = robots58_replay_payload($solo['id'], null, true);
check(str_contains((string)json_encode($teacher, JSON_UNESCAPED_UNICODE), 'Adam Novák') && is_array($teacher['all_stats']), 'učitel: vidí celá jména a statistiky všech robotů');

// Liga a XP
$league = robots58_league($class, robots58_semester($T0));
$top = $league[0] ?? null;
check(count($league) >= 8 && $top !== null && $top['matches'] >= 1 && $top['league'] >= 10 && robots58_semester_label(robots58_semester($T0)) === '2026/27 · 1. pololetí', 'liga: tabulka pololetí sčítá ligové body z obou zápasů');
check(robots58_semester(strtotime('2027-01-20')) === '2026-1' && robots58_semester(strtotime('2027-03-01')) === '2026-2', 'liga: pololetí září–leden / únor–srpen');
$GLOBALS['audit_xp'] = [];
$first = robots58_claim_xp($class, $keys[0]);
$second = robots58_claim_xp($class, $keys[0]);
$place = robots58_place(robots58_match($solo['id']), $keys[0]);
check($first === 2 && $second === 0 && ($GLOBALS['audit_xp'][$class . '|robots58:' . $solo['id']] ?? 0) === robots58_xp_for($place), 'XP: vyzvednutí za 2 zápasy proběhne jen jednou (' . ($GLOBALS['audit_xp'][$class . '|robots58:' . $solo['id']] ?? 0) . ' XP za ' . $place . '. místo)');
check(robots58_claim_xp($class, 'k_nehral') === 0 && robots58_claim_xp($class, '') === 0, 'XP: kdo nehrál, nic nedostane');
$views = (string)file_get_contents($root . '/robots_v58_views.php') . (string)file_get_contents($root . '/robots_v58.php');
check(substr_count($views, 'robots58_claim_xp(') === 2 && !str_contains((string)preg_replace('/function robots58_claim_xp.*?\n}\n/s', '', (string)file_get_contents($root . '/robots_v58.php')), 'learning_award_once('), 'XP: učitelské akce XP nezapisují – jen vyzvednutí v požadavku žáka');

// Rate limit
$_SESSION['robots58_rl'] = [];
$okCount = 0;
for ($i = 0; $i < 15; $i++) $okCount += robots58_rate_ok('test', 12, 60, $T0) ? 1 : 0;
check($okCount === 12 && robots58_rate_ok('test', 12, 60, $T0 + 61), 'rate limit: 12 testů za minutu, pak zase volno');

// Úklid
robots58_delete_match($teams['id']);
check(robots58_match($teams['id']) === null && !is_file(robots58_path('replay', $teams['id'])), 'úklid: smazání zápasu odstraní i záznam a skripty');
rm_tree($tmp);
check(!is_dir($tmp), 'audit: dočasné úložiště smazáno (ostrá storage/ nedotčena)');

echo ($failed === 0 ? 'V58_ROBOTS_AUDIT_OK' : 'V58_ROBOTS_AUDIT_FAIL') . ' checks=' . $checks . ' failed=' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
