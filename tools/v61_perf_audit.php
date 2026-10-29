<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · audit rychlosti celé aplikace (část B).
 *   1) fiktivní třída 30 žáků (deterministicky, dočasné úložiště) s historií: XP, body, testy, úrovně labu,
 *   2) 20 stránek žáka + 10 učitelských záložek: medián ms serveru, KB HTML, počet čtení úložiště (samostatný
 *      proces na stránku, opcache jako v PHP-FPM); rozpočty ≤ 60 ms / ≤ 62 KB (žák; +2 KB navigace), ≤ 150 ms (učitel),
 *      čas s tolerancí šumu (V61_NOISE_TOLERANCE v tools/lib/v61_perf_pages.php); v76: 2 zahřívací běhy a při překročení limitu nejvýš
 *      2 opakovaná kola měření (platí nejnižší medián kola), samotest s uměle zpomalenou stránkou dokazuje, že regrese se nezamaskuje,
 *   3) žádné N+1 čtení (stejný soubor ≤ 12×, celkem ≤ 120 čtení na stránku) a GET v ustáleném stavu nic nezapisuje,
 *   4) paměti v rámci požadavku se po zápisu zneplatní (čerstvá data), storage_update beze změny nepřepisuje,
 *   5) režim jen pro čtení (EDUCANET_STORAGE_READONLY) nezapisuje, bez něj by zapisoval (kladná kontrola),
 *   6) HTTP cache assetů: pravidlo pro dlouhou cache sedí na verze z asset_url(), ručně psané ?v= zůstává krátce,
 *   7) service worker (Node, atrapy API): přednačtení verzovaných assetů, nic z HTML žáka, offline stránka labu,
 *   8) tools/v61_perf_report.php běží nad úložištěm jen čtením a končí PERF_REPORT_OK/WARN.
 *   v80.2: kalibrace stroje – každý měřicí proces po stránce změří pevnou referenční stránku (tools/lib/v61_perf_calibration.php);
 *      čas stránky se normalizuje zpomalením stroje (limity beze změny) a poměr stránka/reference se porovná se základnou
 *      (tools/lib/v61_perf_baseline.php, regrese > 20 % = FAIL); samotesty: +120 ms (spánek i práce CPU) a +30 % regrese.
 *
 *   php tools/v61_perf_audit.php [--update-baseline[=3]]   (--update-baseline: N měření všech stránek → nová základna poměrů, nic jiného)
 * Dočasné úložiště (edu_audit_temp_storage), fiktivní žáci; ostrou storage/ nikdy nečte ani nezapisuje.
 * Konec: V61_PERF_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$GLOBALS['educanet_request_memo'] = true;   // paměti požadavku zapnout před prvním použitím (jako ve webovém požadavku)
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v61-perf')), '/');
$opcacheDir = rtrim(str_replace(chr(92), '/', sys_get_temp_dir()), '/') . '/educanet-audit-opc-' . bin2hex(random_bytes(6));
@mkdir($opcacheDir, 0700, true);
register_shutdown_function(static function () use ($opcacheDir): void { edu_audit_remove_dir($opcacheDir); edu_audit_remove_dir($opcacheDir . '-ref'); });
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/app/lib.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v61_perf_pages.php';
require_once __DIR__ . '/lib/v61_perf_fixture.php';

$state = audit_counter();
$check = audit_checker($state);
$TEACHER_KEY = 'audit-v61-teacher-key-0123456789';
const V61A_RUNS = 11;   // v67: medián z 11 běhů (dřív 5) – stabilnější na hlučném stroji; limity beze změny (schválená změna metodiky, viz BUILD_MANIFEST_V67.md)
const V61A_MAX_SAME = 12;
const V61A_MAX_READS = 120;

/** Otisk obsahu všech souborů úložiště (mimo .lock) – pro důkaz, že se nic nezapsalo. */
function v61a_snapshot(string $dir): array
{
    $out = [];
    if (!is_dir($dir)) return $out;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (str_ends_with($f->getFilename(), '.lock')) continue;
        $out[substr(str_replace(chr(92), '/', $f->getPathname()), strlen($dir))] = md5_file($f->getPathname());
    }
    ksort($out);
    return $out;
}

function v61a_changed(array $before, array $after): array
{
    $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
    return array_values(array_filter($keys, static fn(string $k): bool => ($before[$k] ?? null) !== ($after[$k] ?? null)));
}

function v61a_spec(string $kind, array $page, array $session, string $dir, string $opcache): array
{
    return ['kind' => $kind, 'query' => $page['query'], 'session' => $session, 'storage' => $dir, 'opcache_dir' => $opcache, 'ref_dir' => $opcache . '-ref'];
}

// ---------------------------------------------------------------- 1) fixture
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));
$seedStart = microtime(true);
$fx = v61_perf_seed($modules, $tmp, 30);
$accounts = storage_read(local_accounts_path());
$mustChange = array_filter($accounts, static fn($a): bool => is_array($a) && !empty($a['must_change_password']));
$check('fixture: 30 fiktivních žáků ve třídě a pro každého účet (' . count($accounts) . ' účtů, ' . round(microtime(true) - $seedStart, 1) . ' s)', count($fx['labels']) === 30 && count(array_unique($fx['labels'])) === 30 && count($accounts) >= 30);
$check('fixture: účty žáků nemají vynucenou změnu hesla (stránky se nepřesměrují)', $mustChange === [] && $fx['email'] !== '');
$check('fixture: historie – XP, body, výsledky testů a úrovně labu jsou zapsané pro všechny žáky', (function () use ($fx): bool {
    $profiles = storage_read(learning_profiles_path());
    $withXp = 0;
    foreach ($fx['labels'] as $label) { if ((int)(($profiles[social_student_key(V61_PERF_CLASS, $label)]['xp'] ?? 0)) > 0) $withXp++; }
    return $withXp === 30 && count(storage_rows(STORAGE_DIR . '/practice_results.json.php')) >= 120;
})());

$studentSession = ['next_class_id' => $fx['class'], 'student_label' => $fx['label'], 'local_user' => ['id' => $fx['id'], 'email' => $fx['email'], 'name' => $fx['label']]];
$teacherSession = ['teacher_export_authenticated' => true, 'teacher_display_name' => 'Audit'];
$perfEnv = ['EDUCANET_TEACHER_EXPORT_KEY' => $TEACHER_KEY];

// ---------------------------------------------------------------- 2) měření stránek
$pages = [];
foreach (v61_student_pages() as $p) $pages[] = ['student', $p];
foreach (v61_teacher_pages($fx['class']) as $p) $pages[] = ['teacher', $p];
// v80.2: kalibrace – pevná referenční stránka (měří ji každý měřicí proces hned po stránce) a základna poměrů pro tento stroj
$check('kalibrace: referenční stránka připravena (' . V61_REF_PAGE_FILES . ' knihoven, ' . V61_REF_PAGE_DATA . ' souborů dat, deterministický obsah)', v61_ref_prepare($opcacheDir . '-ref'));
$baseline = v61_perf_baseline();
$baselineHere = $baseline['pages'] !== [] && $baseline['machine'] === v61_machine_id();
$updateRounds = 0;
foreach (array_slice($argv, 1) as $arg) if (preg_match('/^--update-baseline(?:=(\d+))?$/', (string)$arg, $um) === 1) $updateRounds = max(1, min(5, (int)($um[1] ?? 3) ?: 3));
if ($updateRounds > 0) {
    // Nová základna: N měření každé stránky (medián poměrů) – nic jiného se nekontroluje; změna základny = změna limitu (zapsat do manifestu).
    $ratios = [];
    for ($round = 1; $round <= $updateRounds; $round++) {
        foreach ($pages as [$kind, $page]) {
            $m = v61_perf_measure(v61a_spec($kind, $page, $kind === 'student' ? $studentSession : $teacherSession, $tmp, $opcacheDir), V61A_RUNS, $perfEnv);
            if ($m === null || !$m['slow_ok']) { echo 'FAIL základna: měření ' . $page['id'] . " selhalo nebo je stroj přetížený\n"; exit(1); }
            $ratios[$page['id']][] = $m['ratio'];
            printf("kolo %d %-24s %6.1f ms ref %5.1f ms poměr %.3f\n", $round, $page['id'], $m['ms'], $m['ref_ms'], $m['ratio']);
        }
    }
    $new = array_map('v61_median', $ratios);
    $ok = v61_perf_write_baseline($new, $updateRounds . '× medián z ' . V61A_RUNS . ' běhů');
    echo ($ok ? 'V61_PERF_BASELINE_OK pages=' . count($new) : 'V61_PERF_BASELINE_FAIL') . ' machine=' . v61_machine_id() . "\n";
    exit($ok ? 0 : 1);
}
$results = [];
foreach ($pages as [$kind, $page]) {
    // v76: stabilní měření – 2 zahřívací běhy, medián z 11, při nesplnění nejvýš 2 opakovaná kola (platí nejlepší kolo).
    // v80.2: porovnává se normalizovaný čas (ms / zpomalení stroje) s nezměněným limitem a poměr stránka/reference se základnou.
    $limitMs = v61_effective_limit_ms($kind === 'student' ? V61_BUDGET_STUDENT_MS : V61_BUDGET_TEACHER_MS);
    // opakované kolo se spustí už nad rozpočtem bez tolerance (žák 60 ms), aby i souhrnný medián 20 stránek nebyl rukojmím jednoho hlučného kola
    $retryMs = $kind === 'student' ? V61_BUDGET_STUDENT_MS : $limitMs;
    $base = $baselineHere ? (float)($baseline['pages'][$page['id']] ?? 0) : 0.0;
    $m = v61_perf_measure_stable(v61a_spec($kind, $page, $kind === 'student' ? $studentSession : $teacherSession, $tmp, $opcacheDir), V61A_RUNS, $perfEnv,
        static fn(array $r): bool => $r['slow_ok'] && $r['norm_ms'] <= $retryMs && !v61_is_regression($r['ratio_all'], $base));
    $results[$page['id']] = ['kind' => $kind, 'm' => $m];
    $label = ($kind === 'student' ? 'žák ' : 'učitel ') . $page['id'] . ($m !== null && ($m['rounds'] ?? 1) > 1 ? ' (kol měření: ' . $m['rounds'] . ')' : '');
    if ($m === null) { $check($label . ': měření proběhlo (' . (string)($GLOBALS['v61_perf_last_error'] ?? '?') . ')', false); continue; }
    $clean = $m['status'] === 200 && !$m['error'] && $m['location'] === '' && $m['bytes'] > 500;
    $kb = round($m['bytes'] / 1024, 1);
    $okKb = $kind === 'teacher' || $kb <= V61_BUDGET_STUDENT_KB;
    $check(sprintf('%s: HTTP 200 bez chyby/přesměrování, %.1f ms (min %.1f; stroj %.2f×%s → %.1f ms, limit %.1f), %.1f KB%s, čtení %d', $label, $m['ms'], $m['ms_min'], $m['slowness_raw'], $m['slow_ok'] ? '' : ' PŘETÍŽENÝ – zopakovat', $m['norm_ms'], $limitMs, $kb, $kind === 'student' ? ' (limit ' . V61_BUDGET_STUDENT_KB . ')' : '', $m['calls']), $clean && $m['slow_ok'] && $m['norm_ms'] <= $limitMs && $okKb);
    if ($baselineHere) {
        $check(sprintf('%s: poměr stránka/reference %.3f (z %d běhů) ≤ základna %.3f + %d %% (regrese kódu)', $label, $m['ratio_all'], $m['runs_all'], $base, (int)(V61_REGRESSION_TOLERANCE * 100)), $base > 0 && !v61_is_regression($m['ratio_all'], $base));
    }
}
if (!$baselineHere) echo 'INFO  základna poměrů je ' . ($baseline['pages'] === [] ? 'prázdná' : 'z jiného stroje') . ' – porovnání s ní se přeskakuje (absolutní limity platí); nová: php tools/v61_perf_audit.php --update-baseline' . "\n";
$check('kalibrace: zpomalení stroje během měření (medián ' . v61_median(array_map(static fn(array $r): float => (float)($r['m']['slowness_raw'] ?? 0), $results)) . '×, uznatelné ≤ ' . V61_SLOWNESS_MAX . '×) – jinak se měření neuznává',
    count(array_filter($results, static fn(array $r): bool => $r['m'] !== null && $r['m']['slow_ok'])) === count($results));
$over = array_filter($results, static fn(array $r): bool => $r['m'] !== null && ($r['m']['max_same'] > V61A_MAX_SAME || $r['m']['calls'] > V61A_MAX_READS));
$check('N+1: žádná stránka nečte stejný soubor víc než ' . V61A_MAX_SAME . '× ani víc než ' . V61A_MAX_READS . ' čtení' . ($over ? ' (' . implode(', ', array_keys($over)) . ')' : ''), $over === []);
$check('rozpočet: medián všech 20 stránek žáka ≤ ' . V61_BUDGET_STUDENT_MS . ' ms bez tolerance, normalizovaně (celkově rychlé, ne jen výjimečně pod limitem)', (function () use ($results): bool {
    $ms = [];
    foreach ($results as $r) if ($r['kind'] === 'student' && $r['m'] !== null) $ms[] = $r['m']['norm_ms'];
    return count($ms) === 20 && v61_median($ms) <= V61_BUDGET_STUDENT_MS;
})());

// v76: samotest stabilizace – stránka uměle zpomalená o 2× rozpočet zůstane nad limitem i po opakovaných kolech (regrese se nezamaskuje).
$slowPage = v61_student_pages()[0];
$slowLimit = v61_effective_limit_ms(V61_BUDGET_STUDENT_MS);
$slow = v61_perf_measure_stable(v61a_spec('student', $slowPage, $studentSession, $tmp, $opcacheDir) + ['selftest_delay_ms' => (int)(2 * V61_BUDGET_STUDENT_MS)], 3, $perfEnv, $slowLimit);
$check('samotest stabilizace: uměle zpomalená stránka (+' . (int)(2 * V61_BUDGET_STUDENT_MS) . ' ms spánek) zůstane po normalizaci nad limitem ve všech ' . (1 + V61_REMEASURE_ROUNDS) . ' kolech (' . ($slow['ms'] ?? 0) . ' ms → ' . ($slow['norm_ms'] ?? 0) . ' ms) – skutečná regrese se odhalí',
    $slow !== null && $slow['norm_ms'] > $slowLimit && ($slow['rounds'] ?? 0) === 1 + V61_REMEASURE_ROUNDS);
// v80.2: totéž s prací CPU (+120 ms referenčního stroje) – zpomalí se se strojem stejně jako skutečný kód, normalizace ji neschová
$slowCpu = v61_perf_measure_stable(v61a_spec('student', $slowPage, $studentSession, $tmp, $opcacheDir) + ['selftest_work_ms' => 2 * V61_BUDGET_STUDENT_MS], 3, $perfEnv, $slowLimit);
$check('samotest kalibrace: stránka zpomalená prací CPU (+' . (int)(2 * V61_BUDGET_STUDENT_MS) . ' ms referenčního stroje) zůstane nad limitem ve všech ' . (1 + V61_REMEASURE_ROUNDS) . ' kolech (' . ($slowCpu['ms'] ?? 0) . ' ms, stroj ' . ($slowCpu['slowness_raw'] ?? 0) . '× → ' . ($slowCpu['norm_ms'] ?? 0) . ' ms)',
    $slowCpu !== null && $slowCpu['norm_ms'] > $slowLimit && ($slowCpu['rounds'] ?? 0) === 1 + V61_REMEASURE_ROUNDS);
// v80.2: samotest regrese +30 % – střídaně čistá stránka (A) a táž stránka s prací CPU navíc, dokud netrvá o 30 % déle (B, týž proces);
// kola se sčítají stejně jako u kontroly základny (poměr ze všech běhů), B musí být regrese proti A, A proti A (kontrola falešného poplachu) ne.
$regSpec = v61a_spec('student', ['id' => 'hodina', 'query' => ['view' => 'hodina']], $studentSession, $tmp, $opcacheDir);
$rows = ['a' => [], 'b' => [], 'a1' => [], 'a2' => []];
$regInfo = [];
for ($round = 1; $round <= 1 + V61_REMEASURE_ROUNDS; $round++) {
    $pair = v61_perf_measure_paired($regSpec, $regSpec + ['selftest_regress_pct' => 30], 9, $perfEnv);
    $same = v61_perf_measure_paired($regSpec, $regSpec, 7, $perfEnv);
    if ($pair === null || $same === null) break;
    [$rows['a'], $rows['b'], $rows['a1'], $rows['a2']] = [array_merge($rows['a'], $pair['a']), array_merge($rows['b'], $pair['b']), array_merge($rows['a1'], $same['a']), array_merge($rows['a2'], $same['b'])];
    $r = v61_perf_pair_ratios($pair['a'], $pair['b']);
    $regInfo[] = sprintf('kolo %d: %+d %%', $round, (int)round(100 * ($r['b'] / max(0.001, $r['a']) - 1)));
}
$regAB = v61_perf_pair_ratios($rows['a'], $rows['b']);
$regAA = v61_perf_pair_ratios($rows['a1'], $rows['a2']);
$check(sprintf('samotest regrese: stránka zpomalená o 30 %% (práce CPU) je regrese – poměr %.3f → %.3f (%+d %%; %s; tolerance %d %%)', $regAB['a'], $regAB['b'], (int)round(100 * ($regAB['b'] / max(0.001, $regAB['a']) - 1)), implode(', ', $regInfo), (int)(V61_REGRESSION_TOLERANCE * 100)),
    count($regInfo) === 1 + V61_REMEASURE_ROUNDS && v61_is_regression($regAB['b'], $regAB['a']));
$check(sprintf('samotest regrese (kontrola falešného poplachu): čistá stránka proti sobě regresí není – poměr %.3f → %.3f (%+d %%)', $regAA['a'], $regAA['b'], (int)round(100 * ($regAA['b'] / max(0.001, $regAA['a']) - 1))),
    count($regInfo) === 1 + V61_REMEASURE_ROUNDS && $regAA['a'] > 0 && !v61_is_regression($regAA['b'], $regAA['a']));

// ---------------------------------------------------------------- 3) GET v ustáleném stavu nezapisuje
$writers = [];
foreach ($pages as [$kind, $page]) {
    $spec = v61a_spec($kind, $page, $kind === 'student' ? $studentSession : $teacherSession, $tmp, $opcacheDir);
    $before = v61a_snapshot($tmp);
    v61_perf_run($spec, $perfEnv);
    $changed = v61a_changed($before, v61a_snapshot($tmp));
    if ($changed !== []) $writers[] = $page['id'] . ' → ' . implode(',', array_slice($changed, 0, 3));
}
$check('GET v ustáleném stavu (po prvním načtení) nezapisuje do úložiště' . ($writers ? ': ' . implode('; ', $writers) : ''), $writers === []);

// ---------------------------------------------------------------- 4) paměti požadavku a bezpečné zápisy
$memoPath = $tmp . '/v61_memo_probe.json.php';
storage_write($memoPath, ['a' => 1]);
$callsBefore = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0);
for ($i = 0; $i < 50; $i++) load_php_json($memoPath);
$callsAfter = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0);
$check('paměť požadavku: 50× load_php_json téhož souboru = nejvýš 1 čtení úložiště (bylo ' . ($callsAfter - $callsBefore) . ')', $callsAfter - $callsBefore <= 1);
storage_update($memoPath, static fn(array $d): array => ['a' => 2]);
$check('paměť požadavku: po zápisu (storage_update) se čtou čerstvá data', (load_php_json($memoPath)['a'] ?? null) === 2);
$check('storage_read zůstává vždy čerstvý (paměť požadavku je jen ve vrstvě storage_read_request/load_php_json)', (function () use ($memoPath): bool {
    load_php_json($memoPath);
    file_put_contents($memoPath, "<?php http_response_code(403); exit; ?>\n" . json_encode(['a' => 3]));
    return (storage_read($memoPath)['a'] ?? null) === 3;
})());
$typePath = $tmp . '/v61_type_probe.json.php';
storage_write($typePath, ['mastery' => 0, 'x' => ['n' => 1]]);
touch($typePath, time() - 3600);
clearstatcache();
$mtimeBefore = filemtime($typePath);
storage_update($typePath, static fn(array $d): array => ['mastery' => 0.0, 'x' => ['n' => 1]]);
clearstatcache();
$check('storage_update: stejný obsah jen s jiným typem (0 vs 0.0) soubor nepřepíše', filemtime($typePath) === $mtimeBefore);
storage_update($typePath, static fn(array $d): array => ['mastery' => 5, 'x' => ['n' => 1]]);
clearstatcache();
$check('storage_update: skutečná změna se zapíše', filemtime($typePath) !== $mtimeBefore && (storage_read($typePath)['mastery'] ?? null) === 5);
$check('storage_changes_empty: 0 a 0.0 jsou beze změny, 0 a 1 ne', storage_changes_empty(['p' => 0], ['p' => 0.0]) && !storage_changes_empty(['p' => 0], ['p' => 1]) && storage_changes_empty(['p' => 1, 'updated_at' => 'a'], ['p' => 1, 'updated_at' => 'b'], ['updated_at']));
$profile = learning_profile(V61_PERF_CLASS);
$_SESSION['student_label'] = $fx['label'];
$_SESSION['local_user'] = ['id' => $fx['id'], 'email' => $fx['email'], 'name' => $fx['label']];
$_SESSION['next_class_id'] = V61_PERF_CLASS;
$before = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0);
for ($i = 0; $i < 100; $i++) learning_profile(V61_PERF_CLASS);
$check('learning_profile(): 100 volání bez zápisu = nejvýš 2 čtení (bylo ' . ((int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0) - $before) . ')', ((int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0) - $before) <= 2);
$xpBefore = (int)(learning_profile(V61_PERF_CLASS)['xp'] ?? 0);
learning_award_once(V61_PERF_CLASS, 'v61_audit_event', 7);
$check('learning_profile(): po udělení XP vrací nový stav (paměť se zneplatní)', (int)(learning_profile(V61_PERF_CLASS)['xp'] ?? 0) === $xpBefore + 7);
$check('normalized_person_name: stejný výsledek při opakování a pro různé zápisy téhož jména', normalized_person_name('  Žofie  Čermáková ') === 'zofiecermakova' && normalized_person_name('Žofie Čermáková') === 'zofiecermakova' && normalized_person_name('Žofie Čermáková') === 'zofiecermakova');
$t0 = hrtime(true);
for ($i = 0; $i < 3000; $i++) normalized_person_name('Matyáš Ukázkový');
$check('normalized_person_name: 3000 volání téhož jména < 15 ms (' . round((hrtime(true) - $t0) / 1e6, 1) . ' ms)', (hrtime(true) - $t0) / 1e6 < 15);
$memoOn = storage_request_memo_enabled();
$check('paměti požadavku jsou ve webovém požadavku zapnuté (PHP_SAPI ≠ cli) a v čistém CLI vypnuté', $memoOn === true && (function (): bool {
    $php = is_file('C:/php/php.exe') ? 'C:/php/php.exe' : PHP_BINARY;
    $code = 'declare(strict_types=1); $_SERVER["SCRIPT_FILENAME"]="x"; require "' . dirname(__DIR__) . '/src/ops/storage_v58.php"; echo storage_request_memo_enabled() ? "on" : "off";';
    $p = proc_open([$php, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
    if (!is_resource($p)) return false;
    $out = (string)stream_get_contents($pipes[1]);
    foreach ($pipes as $pp) fclose($pp);
    proc_close($p);
    return $out === 'off';
})());

// ---------------------------------------------------------------- 5) režim jen pro čtení
$roDir = $tmp . '-ro';
$rwDir = $tmp . '-rw';
foreach ([$roDir, $rwDir] as $d) { @mkdir($d, 0700, true); v61_perf_write_roster($d, v61_perf_roster(5)); }
$probeRo = v61_perf_run(['kind' => 'readonly_probe', 'storage' => $roDir], ['EDUCANET_STORAGE_READONLY' => '1']);
$probeRw = v61_perf_run(['kind' => 'readonly_probe', 'storage' => $rwDir]);
$check('samotest: v režimu jen pro čtení zápis (storage_update + storage_append) nevznikne', $probeRo !== null && $probeRo['wrote'] === false && !is_file($roDir . '/v61_probe.json.php') && !is_dir($roDir . '/practice_results'));
$check('samotest (kladná kontrola): bez režimu jen pro čtení se zápis provede, takže test zápis opravdu rozpozná', $probeRw !== null && $probeRw['wrote'] === true);
$roSnap = v61a_snapshot($roDir);
$rwSnap = v61a_snapshot($rwDir);
$pageRo = v61_perf_run(['kind' => 'student', 'query' => ['view' => 'dashboard'], 'session' => ['next_class_id' => V61_PERF_CLASS, 'student_label' => v61_perf_roster(5)[0]], 'storage' => $roDir], ['EDUCANET_STORAGE_READONLY' => '1']);
$pageRw = v61_perf_run(['kind' => 'student', 'query' => ['view' => 'dashboard'], 'session' => ['next_class_id' => V61_PERF_CLASS, 'student_label' => v61_perf_roster(5)[0]], 'storage' => $rwDir]);
$check('režim jen pro čtení: celá stránka žáka (první návštěva nového žáka) nezapíše do úložiště ani bajt', $pageRo !== null && $pageRo['status'] === 200 && v61a_changed($roSnap, v61a_snapshot($roDir)) === []);
$check('kladná kontrola: tatáž první návštěva bez režimu jen pro čtení zapisuje (zakládá účty, kostru dovedností)', $pageRw !== null && v61a_changed($rwSnap, v61a_snapshot($rwDir)) !== []);
foreach ([$roDir, $rwDir] as $d) edu_audit_remove_dir($d . '/');
$sessFiles = glob(rtrim(str_replace(chr(92), '/', session_save_path() ?: sys_get_temp_dir()), '/') . '/sess_v61*') ?: [];
$check('měřicí procesy nezakládají soubory relací (relace jen v paměti)', $sessFiles === []);

// ---------------------------------------------------------------- 6) HTTP cache assetů
// Regulární výraz se bere přímo z nginx šablony (ne opsaný), takže test ověřuje to, co se opravdu nasadí.
$nginxText = (string)file_get_contents($ROOT . '/docs/deploy/aapanel/educanet-rules.nginx.conf.example');
$longRule = preg_match('#\$arg_v ~ "([^"]+)"#', $nginxText, $ruleMatch) === 1 ? '~' . $ruleMatch[1] . '~' : '~(?!)~';
$vOf = static function (string $url): string { parse_str((string)parse_url($url, PHP_URL_QUERY), $q); return (string)($q['v'] ?? ''); };
$check('asset_url(): verze je čas změny souboru (9+ číslic) → pravidlo „dlouhá cache“ ji pozná', preg_match($longRule, $vOf(asset_url('assets/app.css?v=46'))) === 1 && preg_match($longRule, $vOf(asset_url('assets/student-v55.js'))) === 1);
$check('ručně psané ?v=57.0 / ?v=46 a URL bez verze dlouhou cache nedostanou', preg_match($longRule, '57.0') === 0 && preg_match($longRule, '46') === 0 && preg_match($longRule, $vOf('assets/app.css')) === 0);
$check('asset_url(): verze je přesně čas změny souboru (po úpravě souboru vznikne nová URL, proto je dlouhá cache bezpečná)', $vOf(asset_url('assets/app.css')) === (string)filemtime($ROOT . '/assets/app.css'));
$nginx = (string)file_get_contents($ROOT . '/docs/deploy/aapanel/educanet-rules.nginx.conf.example');
$nginxGen = (string)file_get_contents($ROOT . '/docs/deploy/nginx-educanet.conf.example');
$apache = (string)file_get_contents($ROOT . '/tools/apache_performance.htaccess.example');
$rule = 'if ($arg_v ~ "^[0-9]{9,}$") { expires 365d; }';
$assetsBlock = static function (string $conf): string { $a = strpos($conf, 'location ^~ /assets/ {'); $b = $a === false ? false : strpos($conf, "\n    }\n", $a); $c = $a === false ? false : strpos($conf, "\n}\n", $a); $end = min(array_filter([$b, $c], static fn($v): bool => $v !== false) ?: [0]); return $a === false ? '' : (string)preg_replace('/^\s*#.*$/m', '', substr($conf, $a, $end - $a)); };
$check('nginx (aaPanel): rok jen pro ?v= s 9+ číslicemi, jinak hodina, bez add_header (nezruší bezpečnostní hlavičky)', str_contains($assetsBlock($nginx), 'expires 1h;') && str_contains($assetsBlock($nginx), $rule) && !str_contains($assetsBlock($nginx), 'add_header'), false);
$check('nginx (obecná šablona) má stejné pravidlo a sw.js bez cache', str_contains($assetsBlock($nginxGen), 'expires 1h;') && str_contains($assetsBlock($nginxGen), $rule) && str_contains($nginxGen, 'location = /sw.js { try_files $uri =404; expires off; }'), false);
$check('Apache příklad: immutable jen pro EDU_ASSET_LONG (?v= 9+ číslic), sw.js no-cache', str_contains($apache, 'env=EDU_ASSET_LONG') && str_contains($apache, 'immutable') && str_contains($apache, '[0-9]{9,}') && str_contains($apache, '"sw.js"'), false);
$check('kořenový .htaccess zůstává bez zmínky o assets (kontrola v59 preflight) a bez expires', !preg_match('~^[^#\n]*(assets|ExpiresActive)~mi', (string)file_get_contents($ROOT . '/.htaccess')), false);

// ---------------------------------------------------------------- HTTP: seznam pro přednačtení
$harness = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_TEACHER_EXPORT_KEY' => $TEACHER_KEY]);
try {
    $hdr = static function (array $resp, string $name): string { foreach ((array)$resp['headers'] as $k => $v) if (strcasecmp((string)$k, $name) === 0) return (string)$v; return ''; };
    $r = $harness->request('GET', '/index.php?view=precache');
    $json = json_decode((string)$r['body'], true);
    $urls = is_array($json['urls'] ?? null) ? $json['urls'] : [];
    $check('?view=precache: JSON se seznamem ≥ 20 verzovaných assetů (200, application/json, necachovat)', $r['status'] === 200 && str_contains(strtolower($hdr($r, 'Content-Type')), 'json') && count($urls) >= 20 && str_contains(strtolower($hdr($r, 'Cache-Control')), 'no-store'));
    $allOk = $urls !== [];
    foreach ($urls as $u) { if (!is_string($u) || preg_match('~^assets/[A-Za-z0-9_./-]+\.(css|js)\?v=[0-9]{9,}$~', $u) !== 1 || !is_file($ROOT . '/' . strtok($u, '?'))) $allOk = false; }
    $check('?view=precache: každá položka je existující asset s verzí = čas změny (přesně URL, které stránky žádají)', $allOk);
    $check('?view=precache: neobsahuje osobní údaje (žádný e-mail, jméno žáka ani identifikátor)', !str_contains((string)$r['body'], '@') && !str_contains((string)$r['body'], 'Zkušební') && !str_contains((string)$r['body'], $fx['id']));
    $layout = $harness->request('GET', '/?class=class_3a&student=' . rawurlencode($fx['label']));
    $page = (string)$layout['body'];
    $linked = [];
    if (preg_match_all('~(?:href|src)="(assets/[^"]+\.(?:css|js)\?v=[0-9]+)"~', $page, $mm)) $linked = $mm[1];
    $inList = array_intersect(array_map('html_entity_decode', $linked), $urls);
    $check('přednačtení pokrývá společné assety stránek: každý CSS/JS z hlavičky přehledu, který je v seznamu, má shodnou URL (' . count($inList) . ' shod)', count($inList) >= 10);
} finally {
    $harness->stop();
}

// ---------------------------------------------------------------- 7) service worker (Node)
$node = (function (): ?string {
    $p = @proc_open(['node', '--version'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
    if (!is_resource($p)) return null;
    $out = trim((string)stream_get_contents($pipes[1]));
    foreach ($pipes as $pp) fclose($pp);
    proc_close($p);
    return preg_match('/^v\d+/', $out) === 1 ? 'node' : null;
})();
if ($node === null) {
    echo "SKIP service worker: Node.js není k dispozici (kontrola chování sw.js vyžaduje node)\n";
} else {
    $p = proc_open([$node, __DIR__ . '/lib/v61_sw_check.js', $ROOT . '/sw.js'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
    $out = is_resource($p) ? (string)stream_get_contents($pipes[1]) : '';
    if (is_resource($p)) { foreach ($pipes as $pp) fclose($pp); proc_close($p); }
    $swResult = json_decode(trim($out), true);
    $swChecks = is_array($swResult['checks'] ?? null) ? $swResult['checks'] : [];
    $check('service worker: kontrolní skript proběhl a vrátil ≥ 12 kontrol', count($swChecks) >= 12 && !isset($swResult['error']));
    foreach ($swChecks as $c) $check((string)$c['name'], (bool)$c['ok']);
}

// ---------------------------------------------------------------- 8) tools/v61_perf_report.php
$reportEnv = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => $TEACHER_KEY];
$php = is_file('C:/php/php.exe') ? 'C:/php/php.exe' : PHP_BINARY;
$full = [];
foreach (array_merge($_SERVER, $_ENV) as $k => $v) if (is_scalar($v)) $full[(string)$k] = (string)$v;
$reportBefore = v61a_snapshot($tmp);
$p = proc_open([$php, $ROOT . '/tools/v61_perf_report.php', '--runs=3'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $ROOT, array_merge($full, $reportEnv), ['bypass_shell' => true]);
$reportOut = is_resource($p) ? (string)stream_get_contents($pipes[1]) : '';
$reportErr = is_resource($p) ? (string)stream_get_contents($pipes[2]) : '';
if (is_resource($p)) { foreach ($pipes as $pp) fclose($pp); $reportExit = proc_close($p); } else { $reportExit = -1; }
$check('v61_perf_report: vypíše tabulku 30 stránek a poslední řádek PERF_REPORT_OK|WARN (exit ' . $reportExit . ')', preg_match('/^PERF_REPORT_(OK|WARN)\b/m', $reportOut) === 1 && substr_count($reportOut, "\n") >= 33 && in_array($reportExit, [0, 2], true) && $reportErr === '');
$check('v61_perf_report: nad úložištěm běží jen čtením (po běhu je každý soubor beze změny)', v61a_changed($reportBefore, v61a_snapshot($tmp)) === []);
$check('v61_perf_report: nevypisuje jméno ani e-mail žáka', !str_contains($reportOut, $fx['label']) && !str_contains($reportOut, $fx['email']));
$pReport = proc_open([$php, $ROOT . '/tools/v61_perf_report.php', '--runs=3'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $ROOT, array_merge($full, ['EDUCANET_STORAGE_DIR' => $tmp . '/neexistuje']), ['bypass_shell' => true]);
$badOut = is_resource($pReport) ? (string)stream_get_contents($pipes[1]) . (string)stream_get_contents($pipes[2]) : '';
if (is_resource($pReport)) { foreach ($pipes as $pp) fclose($pp); $badExit = proc_close($pReport); } else { $badExit = -1; }
$check('v61_perf_report: s neplatným adresářem úložiště skončí chybou a nic neměří', $badExit === 1 || str_contains($badOut, 'FAIL') || $badExit === 1);

echo "\nPoznámka: čas = medián z " . V61A_RUNS . " běhů, limit s tolerancí šumu " . (int)(V61_NOISE_TOLERANCE * 100) . " %; opcache = souborová cache (jako sdílená paměť v PHP-FPM).\n";
echo 'Kalibrace v80.2: čas normalizovaný zpomalením stroje (referenční stránka ' . V61_REF_NOMINAL_MS . ' ms = 1×, max ' . V61_SLOWNESS_MAX . '×), regrese poměru > ' . (int)(V61_REGRESSION_TOLERANCE * 100) . " % proti základně.\n";
exit(audit_summary($state, 'V61_PERF'));
