<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · tools/v61_perf_report.php – měření rychlosti na nasazené instanci, jen čtením.
 *
 *   php tools/v61_perf_report.php [--runs=5] [--class=class_3a] [--no-teacher] [--no-opcache] [--json]
 *
 * Stejné měření jako tools/v61_perf_audit.php (20 stránek žáka + 10 učitelských záložek: medián ms, KB HTML,
 * počet čtení úložiště), ale nad SKUTEČNÝMI daty. Zárukou, že nic nezapisuje:
 *   - každá stránka běží v samostatném procesu s EDUCANET_STORAGE_READONLY=1 (storage_update/_write/_append
 *     jen počítají nad aktuálními daty a nezapisují, nevznikají ani zámky),
 *   - před měřením proběhne samotest téhož režimu nad DOČASNÝM prázdným adresářem (zápis musí selhat),
 *     při neúspěchu se nic neměří,
 *   - relace jsou jen v paměti procesu (žádný soubor relace, žádný cookie), nikdo se nepřihlašuje,
 *   - účty se nezakládají (acc53_provision_all má v GET bránu a stejně jde o dry-run), nic se neposílá ven.
 * Žák pro měření = první účet vybrané třídy bez vynucené změny hesla; jméno ani e-mail se nevypisují.
 * Učitelská relace se sestaví jen v paměti z prvního aktivního admin účtu (nebo ze sdíleného klíče v legacy režimu).
 *
 * Spuštění na serveru (jako uživatel webu, s prostředím aplikace – viz docs/deploy/aapanel/educanet-cron.sh.example):
 *   bash /www/server/educanet/educanet-cron.sh perf
 * Výstup: tabulka a poslední řádek PERF_REPORT_OK (vše v rozpočtu) nebo PERF_REPORT_WARN pages_over=N.
 * Exit kód: 0 = OK, 2 = WARN (nad rozpočtem / nelze změřit část stránek), 1 = chyba (samotest, úložiště).
 */

require_once __DIR__ . '/lib/v61_perf_pages.php';

function v61r_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return $default;
}

/** Obsah JSON úložiště bez bootstrapu, bez zámků a bez zápisu (prosté čtení; poškozený soubor = prázdné pole). */
function v61r_read_store(string $file): array
{
    if (!is_file($file)) return [];
    $raw = (string)@file_get_contents($file);
    $raw = preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Adresář úložiště stejně jako bootstrap.php; nastavená, ale neplatná EDUCANET_STORAGE_DIR = '' (nikdy tichý návrat k jiným datům). */
function v61r_storage_dir(): string
{
    $override = trim((string)getenv('EDUCANET_STORAGE_DIR'));
    if ($override !== '') return is_dir($override) ? rtrim(str_replace(chr(92), '/', $override), '/') : '';
    return str_replace(chr(92), '/', dirname(__DIR__)) . '/storage';
}

/** @return array{label:string,email:string,id:string,class:string,warn:string}|null */
function v61r_pick_student(string $dir, string $classId): ?array
{
    $fallback = null;
    foreach (v61r_read_store($dir . '/local_accounts.json.php') as $a) {
        if (!is_array($a) || !empty($a['demo_account']) || (string)($a['class_id'] ?? '') !== $classId) continue;
        $row = ['label' => (string)($a['student_label'] ?? ''), 'email' => (string)($a['email'] ?? ''), 'id' => (string)($a['id'] ?? ''), 'class' => $classId, 'warn' => ''];
        if ($row['label'] === '' || $row['email'] === '' || $row['id'] === '') continue;
        if (empty($a['must_change_password'])) return $row;
        $fallback ??= $row;
    }
    if ($fallback !== null) $fallback['warn'] = 'všem žákům třídy zbývá vynucená změna hesla – stránky se přesměrují, měření není reprezentativní';
    return $fallback;
}

/** Učitelská relace jen v paměti: účty → první aktivní admin; legacy → příznak sdíleného klíče. */
function v61r_teacher_session(string $dir): array
{
    $store = v61r_read_store($dir . '/teacher_accounts_v59.json.php');
    if ((string)($store['mode'] ?? '') === 'accounts' && is_array($store['accounts'] ?? null)) {
        foreach ($store['accounts'] as $acc) {
            if (is_array($acc) && (string)($acc['status'] ?? '') === 'active' && (string)($acc['role'] ?? '') === 'admin' && empty($acc['must_change_password'])) {
                $now = time();
                return ['teacher59' => ['id' => (string)$acc['id'], 'sv' => (int)($acc['session_version'] ?? 1), 'login_at' => $now, 'seen_at' => $now], 'teacher_display_name' => 'Měření'];
            }
        }
        return [];
    }
    return ['teacher_export_authenticated' => true, 'teacher_display_name' => 'Měření'];
}

/** Rychlý otisk úložiště (počet souborů, součet velikostí, nejnovější změna) – jen stat(), nic se nečte ani nezapisuje. */
function v61r_stat_snapshot(string $dir): string
{
    $count = 0; $size = 0; $newest = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (str_ends_with($f->getFilename(), '.lock')) continue;
        $count++; $size += (int)$f->getSize(); $newest = max($newest, (int)$f->getMTime());
    }
    return $count . ':' . $size . ':' . $newest;
}

function v61r_probe_readonly(): bool
{
    $tmp = rtrim(str_replace(chr(92), '/', sys_get_temp_dir()), '/') . '/educanet-v61-probe-' . bin2hex(random_bytes(6));
    if (!@mkdir($tmp, 0700, true)) return false;
    $res = v61_perf_run(['kind' => 'readonly_probe', 'storage' => $tmp], ['EDUCANET_STORAGE_READONLY' => '1']);
    $left = glob($tmp . '/*') ?: [];
    foreach ($left as $f) { is_dir($f) ? @rmdir($f) : @unlink($f); }
    @rmdir($tmp);
    return $res !== null && ($res['wrote'] ?? true) === false && $left === [];
}

function v61r_make_cache_dir(bool $enabled): string
{
    if (!$enabled) return '';
    $dir = rtrim(str_replace(chr(92), '/', sys_get_temp_dir()), '/') . '/educanet-v61-opc-' . bin2hex(random_bytes(6));
    return @mkdir($dir, 0700, true) ? $dir : '';
}

function v61r_remove_dir(string $dir): void
{
    if ($dir === '' || !is_dir($dir) || !str_contains($dir, 'educanet-v61-opc-')) return;
    foreach (glob($dir . '/*') ?: [] as $f) { is_dir($f) ? v61r_remove_dir($f) : @unlink($f); }
    @rmdir($dir);
}

/** @return list<array<string,mixed>> */
function v61r_measure_all(array $pages, array $session, string $dir, string $cacheDir, int $runs): array
{
    $rows = [];
    foreach ($pages as [$kind, $page]) {
        $m = v61_perf_measure(['kind' => $kind, 'query' => $page['query'], 'session' => $session[$kind], 'storage' => $dir, 'opcache_dir' => $cacheDir], $runs, ['EDUCANET_STORAGE_READONLY' => '1']);
        $limitMs = v61_effective_limit_ms($kind === 'student' ? V61_BUDGET_STUDENT_MS : V61_BUDGET_TEACHER_MS);
        $row = ['id' => $page['id'], 'kind' => $kind, 'limit_ms' => $limitMs, 'limit_kb' => $kind === 'student' ? V61_BUDGET_STUDENT_KB : null, 'measured' => $m !== null];
        if ($m !== null) {
            $row += ['ms' => $m['ms'], 'ms_min' => $m['ms_min'], 'kb' => round($m['bytes'] / 1024, 1), 'reads' => $m['calls'], 'disk' => $m['disk'], 'status' => $m['status'], 'redirect' => $m['location'] !== '', 'error' => $m['error']];
        }
        $rows[] = $row;
    }
    return $rows;
}

function v61r_verdict(array $row): string
{
    if (!$row['measured']) return 'NEZMĚŘENO';
    if ($row['error'] || $row['status'] >= 400 || $row['redirect']) return $row['redirect'] ? 'PŘESMĚROVÁNÍ' : 'CHYBA';
    if ($row['ms'] > $row['limit_ms']) return 'POMALÉ';
    if ($row['limit_kb'] !== null && $row['kb'] > $row['limit_kb']) return 'VELKÉ';
    return 'OK';
}

function v61r_main(array $argv): int
{
    $runs = max(3, min(20, (int)v61r_arg($argv, 'runs', '5')));
    $dir = v61r_storage_dir();
    if ($dir === '' || !is_dir($dir)) { fwrite(STDERR, "FAIL úložiště nenalezeno (zkontroluj EDUCANET_STORAGE_DIR)\n"); return 1; }
    if (!v61r_probe_readonly()) { fwrite(STDERR, "FAIL samotest režimu jen pro čtení selhal – nic se neměří\n"); return 1; }
    $classId = (string)v61r_arg($argv, 'class', 'class_3a');
    $student = v61r_pick_student($dir, $classId);
    if ($student === null) { fwrite(STDERR, "FAIL ve třídě $classId není žádný účet žáka\n"); return 1; }
    $studentSession = ['next_class_id' => $classId, 'student_label' => $student['label'], 'local_user' => ['id' => $student['id'], 'email' => $student['email'], 'name' => $student['label']]];
    $withTeacher = !in_array('--no-teacher', $argv, true);
    $teacherSession = $withTeacher ? v61r_teacher_session($dir) : [];
    $pages = [];
    foreach (v61_student_pages() as $p) $pages[] = ['student', $p];
    if ($teacherSession !== []) foreach (v61_teacher_pages($classId) as $p) $pages[] = ['teacher', $p];
    $cacheDir = v61r_make_cache_dir(!in_array('--no-opcache', $argv, true));
    $statBefore = v61r_stat_snapshot($dir);
    try {
        $rows = v61r_measure_all($pages, ['student' => $studentSession, 'teacher' => $teacherSession], $dir, $cacheDir, $runs);
    } finally {
        v61r_remove_dir($cacheDir);
    }
    if (v61r_stat_snapshot($dir) !== $statBefore) echo "INFO úložiště se během měření změnilo – měření samotné nezapisuje (režim jen pro čtení), jde o běžný provoz žáků.\n";
    return v61r_print($rows, $runs, $student['warn'], $withTeacher && $teacherSession === [], in_array('--json', $argv, true), $cacheDir !== '');
}

function v61r_print(array $rows, int $runs, string $studentWarn, bool $teacherMissing, bool $json, bool $opcache): int
{
    $over = 0;
    foreach ($rows as &$r) { $r['verdict'] = v61r_verdict($r); if ($r['verdict'] !== 'OK') $over++; }
    unset($r);
    if ($json) echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
    else {
        printf("%-24s %-8s %9s %9s %8s %8s  %s\n", 'stránka', 'typ', 'medián ms', 'min ms', 'KB', 'čtení', 'stav');
        foreach ($rows as $r) {
            $m = $r['measured'];
            printf("%-24s %-8s %9s %9s %8s %8s  %s\n", $r['id'], $r['kind'] === 'student' ? 'žák' : 'učitel', $m ? number_format($r['ms'], 1) : '-', $m ? number_format($r['ms_min'], 1) : '-', $m ? number_format($r['kb'], 1) : '-', $m ? (string)$r['reads'] : '-', $r['verdict']);
        }
        echo "\nmedián z $runs běhů, režim jen pro čtení, " . ($opcache ? 'opcache (souborová cache)' : 'bez opcache (pesimistické)') . ". Rozpočet: žák ≤ " . V61_BUDGET_STUDENT_MS . " ms a ≤ " . V61_BUDGET_STUDENT_KB . " KB, učitel ≤ " . V61_BUDGET_TEACHER_MS . " ms (čas s tolerancí šumu " . (int)(V61_NOISE_TOLERANCE * 100) . " %, tj. " . v61_effective_limit_ms(V61_BUDGET_STUDENT_MS) . " / " . v61_effective_limit_ms(V61_BUDGET_TEACHER_MS) . " ms).\n";
    }
    if ($studentWarn !== '') echo "WARN $studentWarn\n";
    if ($teacherMissing) echo "WARN učitelskou relaci nelze sestavit (žádný aktivní admin účet) – záložky učitele nezměřeny\n";
    if ($over === 0 && $studentWarn === '' && !$teacherMissing) { echo "PERF_REPORT_OK pages=" . count($rows) . "\n"; return 0; }
    echo "PERF_REPORT_WARN pages_over=$over\n";
    return 2;
}

exit(v61r_main($argv ?? []));
