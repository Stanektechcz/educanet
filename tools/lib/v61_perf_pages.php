<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · seznam měřených stránek a rozpočty (sdílí tools/v61_perf_audit.php a tools/v61_perf_report.php).
 * Rozpočty z plánu v61: žákovská stránka ≤ 60 ms serveru a ≤ 60 KB HTML, učitelská záložka ≤ 150 ms.
 */

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
    foreach (['overview', 'class_overview', 'class_results', 'session', 'arena', 'pristupy', 'attention', 'analytics', 'student360', 'reports'] as $tab) {
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
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
    if (preg_match('/V61PERF (\{.*\})\s*$/', $out, $m) !== 1) return null;
    $row = json_decode($m[1], true);
    return is_array($row) ? $row : null;
}

/** Medián z $runs běhů; vrací metriky mediánu (ms, bytes, calls…) a příznak chyby/přesměrování z posledního běhu. */
function v61_perf_measure(array $spec, int $runs = 5, array $env = []): ?array
{
    $rows = [];
    v61_perf_run($spec, $env); // zahřívací běh (zkompilované soubory do opcache); do mediánu se nepočítá
    for ($i = 0; $i < $runs; $i++) {
        $r = v61_perf_run($spec, $env);
        if ($r === null) return null;
        $rows[] = $r;
    }
    $last = $rows[count($rows) - 1];
    return [
        'ms' => round(v61_median(array_column($rows, 'ms')), 1),
        'ms_min' => round(min(array_column($rows, 'ms')), 1),
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
