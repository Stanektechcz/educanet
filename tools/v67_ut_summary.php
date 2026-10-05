<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · tools/v67_ut_summary.php – souhrn uživatelského testu ze záznamu CSV (test provádí škola; nástroj jen počítá).
 *
 *   php tools/v67_ut_summary.php [--file=docs/ut_v67_zaznam.csv]
 *
 * Vstup: středníkové CSV `ucastnik;role;uloha;uspech;cas_s;poznamka` (řádky s # se přeskočí). Výstup po úlohách: počet pokusů,
 * úspěšnost, medián času; úlohy s úspěšností pod 80 % označí ke zpracování do roadmapy. Poznámky se nevypisují (mohly by obsahovat
 * osobní údaje); nástroj nic nezapisuje. Konec: V67_UT_SUMMARY_OK tasks=N rows=M | V67_UT_SUMMARY_EMPTY | V67_UT_SUMMARY_FAIL.
 */

function ut67_median(array $values): float
{
    sort($values);
    $n = count($values);
    if ($n === 0) return 0.0;
    return $n % 2 === 1 ? (float)$values[intdiv($n, 2)] : ($values[$n / 2 - 1] + $values[$n / 2]) / 2;
}

/** @return list<array{role:string,task:string,ok:bool,secs:int}> */
function ut67_rows(string $csv): array
{
    $rows = [];
    foreach (preg_split('/\R/', $csv) ?: [] as $i => $line) {
        $line = trim($line);
        if ($i === 0 || $line === '' || $line[0] === '#') continue;
        $c = str_getcsv($line, ';', '"', '');
        if (count($c) < 5 || !in_array($c[3], ['0', '1'], true) || !ctype_digit($c[4])) continue;
        $rows[] = ['role' => (string)$c[1], 'task' => (string)$c[2], 'ok' => $c[3] === '1', 'secs' => (int)$c[4]];
    }
    return $rows;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $file = dirname(__DIR__) . '/docs/ut_v67_zaznam.csv';
    foreach ($argv as $a) if (str_starts_with((string)$a, '--file=')) $file = substr((string)$a, 7);
    if (!is_file($file)) { fwrite(STDERR, "V67_UT_SUMMARY_FAIL soubor neexistuje\n"); exit(1); }
    $rows = ut67_rows((string)file_get_contents($file));
    if ($rows === []) { echo "V67_UT_SUMMARY_EMPTY zatím žádná data\n"; exit(0); }
    $by = [];
    foreach ($rows as $r) $by[$r['role'] . ' ' . $r['task']][] = $r;
    ksort($by);
    printf("%-14s %7s %10s %10s\n", 'úloha', 'pokusů', 'úspěšnost', 'medián s');
    foreach ($by as $name => $list) {
        $rate = count(array_filter($list, static fn(array $r): bool => $r['ok'])) / count($list);
        printf("%-14s %7d %9d%% %10.0f%s\n", $name, count($list), (int)round($rate * 100), ut67_median(array_column($list, 'secs')), $rate < 0.8 ? '  <- do roadmapy' : '');
    }
    echo 'V67_UT_SUMMARY_OK tasks=' . count($by) . ' rows=' . count($rows) . "\n";
}
