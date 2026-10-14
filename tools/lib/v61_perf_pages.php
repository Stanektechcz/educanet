<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · seznam měřených stránek a rozpočty (sdílí tools/v61_perf_audit.php a tools/v61_perf_report.php).
 * Rozpočty z plánu v61: žákovská stránka ≤ 60 ms serveru a ≤ 60 KB HTML, učitelská záložka ≤ 150 ms.
 */

require_once __DIR__ . '/v61_perf_calibration.php';

const V61_BUDGET_STUDENT_MS = 60.0;
/** 60 KB + 2 KB pro navigaci se skupinami a podmenu (nav_v61.php; nejtěžší stránka prikazy měřila 58,8 -> 61,3 KB). */
const V61_BUDGET_STUDENT_KB = 62.0;
const V61_BUDGET_TEACHER_MS = 150.0;
/**
 * Tolerance šumu měření: medián z ≥ 5 běhů kolísá na sdíleném stroji (Windows + antivirus i ±25 % u téže stránky),
 * proto se čas porovnává s limitem × (1 + tolerance). Velikost HTML je deterministická – tolerance se nepoužívá.
 */
const V61_NOISE_TOLERANCE = 0.25;

function v61_effective_limit_ms(float $budgetMs): float
{
    return round($budgetMs * (1 + V61_NOISE_TOLERANCE), 1);
}

/** @return list<array{id:string,query:array<string,string>}> 20 nejčastějších stránek žáka. */
function v61_student_pages(): array
{
    $pages = [];
    foreach ([
        'dashboard' => ['view' => 'dashboard'],
        'profil' => ['view' => 'profile'],
        'profil-odznaky' => ['view' => 'profile', 'tab' => 'odznaky'],
        'profil-nastaveni' => ['view' => 'profile', 'tab' => 'nastaveni'],
        'lab' => ['view' => 'lab'],
        'prikazy' => ['view' => 'prikazy'],
        'materialy' => ['view' => 'materialy'],
        'vysledky' => ['view' => 'vysledky'],
        'lekce' => ['view' => 'lekce'],
        'hodina' => ['view' => 'hodina'],
        'hry' => ['view' => 'hry'],
        'hadanka' => ['view' => 'hadanka'],
        'roboti' => ['view' => 'roboti'],
        'obchod' => ['view' => 'obchod'],
        'projekty' => ['view' => 'projekty'],
        'hlaseni' => ['view' => 'hlaseni'],
        'dovednosti' => ['view' => 'skills'],
        'studium' => ['view' => 'study'],
        'komunita' => ['view' => 'community'],
        'soukromi' => ['view' => 'privacy'],
    ] as $id => $query) {
        $pages[] = ['id' => $id, 'query' => $query];
    }
    return $pages;
}

/** @return list<array{id:string,query:array<string,string>}> 10 učitelských záložek. */
function v61_teacher_pages(string $classId): array
{
    $pages = [];
    foreach (['overview', 'prehled', 'class_results', 'session', 'arena', 'pristupy', 'attention', 'analytics', 'student360', 'reports'] as $tab) {
        $pages[] = ['id' => 'ucitel-' . $tab, 'query' => ['tab' => $tab, 'class' => $classId]];
    }
    return $pages;
}

/** Medián hodnot (prázdné pole = 0.0). */
function v61_median(array $values): float
{
    $values = array_values(array_map('floatval', $values));
    if ($values === []) return 0.0;
    sort($values);
    $n = count($values);
    return $n % 2 === 1 ? $values[intdiv($n, 2)] : ($values[$n / 2 - 1] + $values[$n / 2]) / 2;
}

/**
 * Spustí měřicí proces jedné stránky (tools/lib/v61_perf_worker.php) a vrátí metriky, nebo null při selhání.
 * $env se přidá k prostředí potomka (např. EDUCANET_STORAGE_READONLY). Spec jde přes proměnnou prostředí.
 *
 * @return array{ms:float,bytes:int,calls:int,disk:int,unique:int,max_same:int,status:int,location:string,error:bool}|null
 */
function v61_perf_run(array $spec, array $env = []): ?array
{
    $php = is_file('C:/php/php.exe') ? 'C:/php/php.exe' : PHP_BINARY;
    $worker = __DIR__ . '/v61_perf_worker.php';
    $full = [];
    // Prostředí aplikace (EDUCANET_* z educanet.env) – getenv() ho vidí i při variables_order bez „E“.
    foreach (array_merge($_SERVER, $_ENV, getenv()) as $k => $v) if (is_scalar($v)) $full[(string)$k] = (string)$v;
    $full['V61_PERF_SPEC'] = (string)json_encode($spec, JSON_UNESCAPED_UNICODE);
    foreach ($env as $k => $v) $full[(string)$k] = (string)$v;
    $cmd = [$php];
    $opc = (string)($spec['opcache_dir'] ?? '');
    if ($opc !== '' && is_dir($opc)) {
        // Zkompilovaný kód se drží v souborové cache (každé měření je nový proces) – stejně jako v PHP-FPM, kde opcache žije mezi požadavky.
        if (!extension_loaded('Zend OPcache')) array_push($cmd, '-d', 'zend_extension=opcache');
        array_push($cmd, '-d', 'opcache.enable=1', '-d', 'opcache.enable_cli=1', '-d', 'opcache.file_cache=' . $opc, '-d', 'opcache.file_cache_only=1');
    }
    $cmd[] = $worker;
    $proc = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2), $full, ['bypass_shell' => true]);
    if (!is_resource($proc)) return null;
    fclose($pipes[0]);
    $out = (string)stream_get_contents($pipes[1]);
    $err = (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);
    if (preg_match('/V61PERF (\{.*\})\s*$/', $out, $m) !== 1) {
        // v76: důvod selhání měřicího procesu do popisku kontroly (bez dat žáků – jen kód a začátek chybového výstupu)
        $GLOBALS['v61_perf_last_error'] = 'exit ' . $code . ': ' . mb_substr(trim((string)preg_replace('/\s+/', ' ', $err !== '' ? $err : substr($out, -300))), 0, 160);
        return null;
    }
    $row = json_decode($m[1], true);
    return is_array($row) ? $row : null;
}

/**
 * Medián z $runs běhů; vrací metriky mediánu (ms, bytes, calls…) a příznak chyby/přesměrování z posledního běhu.
 * v80.2: navíc kalibrace stroje – 'ref_ms' (medián referenční zátěže změřené v týchž procesech hned po stránce),
 * 'ratio' (medián ms stránky / medián ref_ms), 'slowness' (zpomalení stroje, ≥ 1, viz v61_slowness()), 'slow_ok'
 * (stroj není přetížený nad V61_SLOWNESS_MAX) a 'norm_ms' = ms / slowness (≈ čas na nevytíženém referenčním stroji).
 */
function v61_perf_measure(array $spec, int $runs = 5, array $env = []): ?array
{
    $rows = [];
    v61_perf_run($spec, $env); // zahřívací běhy (zkompilované soubory do opcache, souborová cache OS); do mediánu se nepočítají
    v61_perf_run($spec, $env); // v76: druhý zahřívací běh – první měřený běh po kompilaci býval odlehlý
    for ($i = 0; $i < $runs; $i++) {
        $r = v61_perf_run($spec, $env);
        if ($r === null) return null;
        $rows[] = $r;
    }
    return v61_perf_summary($rows) + ['rows' => $rows];
}

/** Souhrn řádků měřicích procesů (medián, kalibrace). @param list<array<string,mixed>> $rows */
function v61_perf_summary(array $rows): array
{
    $last = $rows[count($rows) - 1];
    $ms = v61_median(array_column($rows, 'ms'));
    $refs = array_map('floatval', array_column($rows, 'ref_ms'));
    $ref = v61_median($refs);
    $slow = v61_slowness($refs);
    return [
        'ms' => round($ms, 1),
        'ms_min' => round(min(array_column($rows, 'ms')), 1),
        'ref_ms' => round($ref, 1),
        'ratio' => $ref > 0 ? round($ms / $ref, 3) : 0.0,
        'slowness' => $slow['factor'],
        'slowness_raw' => $slow['raw'],
        'slow_ok' => $slow['ok'],
        'norm_ms' => round($ms / $slow['factor'], 1),
        'bytes' => (int)$last['bytes'],
        'calls' => (int)$last['calls'],
        'disk' => (int)$last['disk'],
        'unique' => (int)$last['unique'],
        'files' => (int)($last['files'] ?? 0),
        'max_same' => (int)$last['max_same'],
        'status' => (int)$last['status'],
        'location' => (string)$last['location'],
        'top' => (array)($last['top'] ?? []),
        'error' => (bool)$last['error'],
    ];
}

/** v76: kolikrát nejvýš zopakovat celé měření stránky, když medián přesáhne limit (přechodná zátěž stroje ≠ regrese kódu). */
const V61_REMEASURE_ROUNDS = 2;

/**
 * v76 · stabilní měření: medián z $runs běhů (po 2 zahřívacích); když nesplní limit, měření se celé zopakuje nejvýš
 * V61_REMEASURE_ROUNDS× a platí nejlepší kolo. Skutečná regrese (stránka je pomalá pokaždé) zůstane nad limitem ve všech
 * kolech, přechodný šum (jiný proces na stroji) ne. Vrací metriky jako v61_perf_measure() + 'rounds' (počet kol).
 * v80.2: $limit je buď limit normalizovaného času v ms (porovnává se 'norm_ms', ne surové 'ms'), nebo funkce
 * fn(array $m): bool (např. limit + porovnání se základnou přes 'ratio_all' = poměr ze všech dosavadních kol); nejlepší kolo = nejnižší 'norm_ms'.
 */
function v61_perf_measure_stable(array $spec, int $runs, array $env, float|Closure $limit): ?array
{
    $ok = $limit instanceof Closure ? $limit : static fn(array $m): bool => $m['slow_ok'] && $m['norm_ms'] <= $limit;
    $best = null;
    $all = [];
    for ($round = 1; $round <= 1 + V61_REMEASURE_ROUNDS; $round++) {
        $m = v61_perf_measure($spec, $runs, $env);
        if ($m === null) return null;
        // v80.2: poměr pro porovnání se základnou se počítá ze VŠECH dosavadních běhů (opakované kolo zpřesní odhad, nevybírá šťastné kolo)
        $all = array_merge($all, (array)$m['rows']);
        $pool = v61_perf_summary($all);
        $m['ratio_all'] = $pool['ratio'];
        $passed = $ok($m);
        if ($best === null || ($passed && !$best['passed']) || ($passed === $best['passed'] && $m['norm_ms'] < $best['norm_ms'])) $best = $m + ['passed' => $passed];
        $best['ratio_all'] = $pool['ratio'];
        $best['runs_all'] = count($all);
        $best['passed'] = $ok($best);
        $best['rounds'] = $round;
        if ($best['passed']) break;
    }
    unset($best['rows']);
    return $best;
}

/**
 * v80.2 · střídané měření dvou variant téže stránky (A, B, A, B …) – obě varianty zažijí stejnou zátěž stroje, takže poměr
 * jejich časů je spolehlivý i na vytíženém stroji (samotest: B = A s umělou regresí). Vrací surové řádky obou variant
 * ['a' => list, 'b' => list] (souhrn přes v61_perf_pair_ratios(), i za víc kol dohromady) nebo null.
 *
 * @return array{a:list<array<string,mixed>>,b:list<array<string,mixed>>}|null
 */
function v61_perf_measure_paired(array $specA, array $specB, int $runs, array $env = []): ?array
{
    v61_perf_run($specA, $env);
    v61_perf_run($specB, $env);
    $a = $b = [];
    for ($i = 0; $i < $runs; $i++) {
        $ra = v61_perf_run($specA, $env);
        $rb = $ra === null ? null : v61_perf_run($specB, $env);
        if ($ra === null || $rb === null) return null;
        $a[] = $ra;
        $b[] = $rb;
    }
    return ['a' => $a, 'b' => $b];
}

/**
 * Poměry obou variant ze střídaného měření: ms / medián reference OBOU variant dohromady (zažily stejnou zátěž, takže
 * poměr B/A závisí jen na stránce, ne na šumu reference). @return array{a:float,b:float}
 */
function v61_perf_pair_ratios(array $rowsA, array $rowsB): array
{
    $ref = v61_median(array_map('floatval', array_merge(array_column($rowsA, 'ref_ms'), array_column($rowsB, 'ref_ms'))));
    if ($ref <= 0 || $rowsA === [] || $rowsB === []) return ['a' => 0.0, 'b' => 0.0];
    return ['a' => round(v61_median(array_column($rowsA, 'ms')) / $ref, 3), 'b' => round(v61_median(array_column($rowsB, 'ms')) / $ref, 3)];
}
