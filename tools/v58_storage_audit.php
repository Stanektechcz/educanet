<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v58 · audit jádra úložiště (F2, DAT-01/03/07, Z2).
 *
 * Běží VŽDY v izolované dočasné storage (vlastní adresář v systémovém temp, na konci smazaný).
 *  1. token-sken: žádné zápisy starými obaly mimo jádro (povolený seznam jen zdůvodněné výjimky),
 *  2. souběh: 20 procesů × 50 zápisů přes storage_update i storage_append → 1000 záznamů beze ztráty,
 *  3. čtení během zápisu nikdy nevrátí [] ani poškozená data (mapa i proud),
 *  4. storage_update_many s opačným pořadím cest bez deadlocku,
 *  5. migrace: --dry-run nic nezmění, --apply zachová všechny záznamy a je idempotentní,
 *  6. Z2: souběžné udělení XP ze dvou „session“ se sečte; zastaralá kopie profilu nepřepíše cizí změny.
 * Konec: V58_STORAGE_AUDIT_OK checks=N failed=0 (jinak V58_STORAGE_AUDIT_FAIL, exit 1).
 */

const SA_WORKERS = 20;
const SA_PER_WORKER = 50;
const SA_TIMEOUT = 180;

$saArgs = [];
foreach (array_slice($argv, 1) as $a) if (preg_match('/^--([a-z]+)=(.*)$/', $a, $m) === 1) $saArgs[$m[1]] = $m[2];
$saChild = $saArgs['child'] ?? '';

if ($saChild === '') {
    $saTmp = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet-storage-audit-' . bin2hex(random_bytes(6));
    if (!mkdir($saTmp, 0700, true)) { fwrite(STDERR, "Nelze vytvořit dočasnou storage.\n"); exit(1); }
    putenv('EDUCANET_STORAGE_DIR=' . $saTmp);
}
require_once dirname(__DIR__) . '/bootstrap.php';
if ($saChild === '' && STORAGE_DIR !== $saTmp) { fwrite(STDERR, "Audit neběží v izolované storage.\n"); exit(1); }

// ------------------------------------------------------------------ dětské procesy
if ($saChild !== '') {
    $n = (int)($saArgs['n'] ?? SA_PER_WORKER);
    $id = (string)($saArgs['id'] ?? '0');
    try {
        switch ($saChild) {
            case 'update':
                for ($i = 0; $i < $n; $i++) {
                    storage_update(STORAGE_DIR . '/sa_counter.json.php', static function (array $d) use ($id, $i): array {
                        $d['counter'] = (int)($d['counter'] ?? 0) + 1;
                        $d['rows'][] = $id . ':' . $i;
                        return $d;
                    });
                }
                break;
            case 'append':
                for ($i = 0; $i < $n; $i++) storage_append('sa_stream', ['w' => $id, 'i' => $i, 'pad' => str_repeat('x', 200 + $i)]);
                break;
            case 'read':
                $stop = STORAGE_DIR . '/sa_stop';
                $reads = 0; $bad = 0; $last = 0; $lastStream = 0;
                while (!is_file($stop) && $reads < 100000) {
                    unset($GLOBALS['educanet_json_request_cache'], $GLOBALS['educanet_stream_cache']);
                    try {
                        $d = storage_read(STORAGE_DIR . '/sa_counter.json.php');
                        $c = (int)($d['counter'] ?? -1);
                        if ($d === [] || $c < $last || count((array)($d['rows'] ?? [])) !== $c) $bad++;
                        $last = max($last, $c);
                        $s = count(storage_stream_rows('sa_stream'));
                        if ($s < $lastStream) $bad++;
                        $lastStream = max($lastStream, $s);
                    } catch (Throwable $e) {
                        $bad++;
                    }
                    $reads++;
                }
                echo "READ reads=$reads bad=$bad\n";
                break;
            case 'many':
                $a = STORAGE_DIR . '/sa_many_a.json.php';
                $b = STORAGE_DIR . '/sa_many_b.json.php';
                $paths = ($saArgs['order'] ?? 'ab') === 'ab' ? [$a, $b] : [$b, $a];
                for ($i = 0; $i < $n; $i++) {
                    storage_update_many($paths, static function (array $d) use ($a, $b): array {
                        $d[$a]['n'] = (int)($d[$a]['n'] ?? 0) + 1;
                        $d[$b]['n'] = (int)($d[$b]['n'] ?? 0) + 1;
                        return $d;
                    });
                }
                break;
            case 'xp':
                // Dvě „session“ (dvě zařízení) téhož žáka udělují XP souběžně.
                $_SESSION['local_user'] = ['id' => 'sa-audit-local', 'email' => 'sa.audit@educanet.cz'];
                $_SESSION['student_label'] = 'Audit Testovací';
                for ($i = 0; $i < $n; $i++) {
                    if (!learning_award_once('class_3a', 'sa_xp:' . $id . ':' . $i, 10)) throw new RuntimeException('XP neuděleno.');
                    $p = learning_profile('class_3a'); // session kopie se obnoví z úložiště
                    $p['kb']['sa-topic-' . $id]['visual'] = true;
                    learning_save_profile('class_3a', $p);
                }
                break;
            default:
                throw new RuntimeException('Neznámý režim.');
        }
        echo "CHILD_OK\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, 'CHILD_FAIL ' . $e->getMessage() . "\n");
        exit(1);
    }
}

// ------------------------------------------------------------------ hlavní proces
$checks = 0; $failed = 0;
$check = static function (string $name, bool $ok, string $detail = '') use (&$checks, &$failed): void {
    $checks++;
    if (!$ok) $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($detail !== '' ? ' (' . $detail . ')' : '') . "\n";
};
$spawn = static function (array $args) use ($saTmp) {
    $env = getenv();
    $env['EDUCANET_STORAGE_DIR'] = $saTmp;
    $proc = proc_open(array_merge([PHP_BINARY, __FILE__], $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__), $env);
    if (!is_resource($proc)) throw new RuntimeException('Nelze spustit proces.');
    return [$proc, $pipes];
};
$collect = static function (array $procs): array {
    $out = [];
    $deadline = time() + SA_TIMEOUT;
    foreach ($procs as $i => [$proc, $pipes]) {
        $stdout = stream_get_contents($pipes[1]); $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        while (($st = proc_get_status($proc)) && $st['running'] && time() < $deadline) usleep(20000);
        $code = proc_close($proc);
        $out[$i] = ['code' => $code === -1 ? (int)($st['exitcode'] ?? 1) : $code, 'out' => (string)$stdout . (string)$stderr];
    }
    return $out;
};
$treeHash = static function (string $dir): string {
    $items = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) if ($f->isFile()) $items[] = substr(str_replace('\\', '/', $f->getPathname()), strlen($dir)) . ':' . hash_file('sha256', $f->getPathname());
    sort($items);
    return hash('sha256', implode("\n", $items));
};
$rrm = static function (string $dir) use (&$rrm): void {
    foreach (scandir($dir) ?: [] as $n) {
        if ($n === '.' || $n === '..') continue;
        is_dir("$dir/$n") ? $rrm("$dir/$n") : @unlink("$dir/$n");
    }
    @rmdir($dir);
};

try {
    // 1. token-sken ----------------------------------------------------------------
    $root = str_replace('\\', '/', dirname(__DIR__));
    $legacy = ['save_php' . '_json_map', 'append_php' . '_json', 'adaptive_store' . '_save', 'ml_store' . '_save', 'coach_store' . '_save', 'v50_save' . '_rows', 'v505_save' . '_rows', 'project_workspace' . '_save', 'teacher_ops_save' . '_rows', 'team_lobby_save' . '_rows'];
    // Zdůvodněné výjimky: testovací fixtures cizích auditů v izolované storage (vlastníci F3/SEC/OPS; mohou přejít na storage_write).
    $allow = ['tests/skill_tree_audit.php', 'tools/v56_learning_path_audit.php', 'tools/v58_accounts_audit.php'];
    $re = '/(?<![\w>$:])(' . implode('|', array_map('preg_quote', $legacy)) . ')\s*\(/';
    $hits = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $rel = substr(str_replace('\\', '/', $f->getPathname()), strlen($root) + 1);
        if (!str_ends_with($rel, '.php') || preg_match('#^(V1|lab-runtime|vendor|node_modules|storage|cache)/#', $rel) === 1 || in_array($rel, $allow, true)) continue;
        foreach (preg_split('/\R/', (string)file_get_contents($f->getPathname())) ?: [] as $ln => $line) {
            if (preg_match($re, $line) !== 1 || preg_match('/function\s+(' . implode('|', $legacy) . ')\s*\(/', $line) === 1) continue;
            $hits[] = $rel . ':' . ($ln + 1);
        }
    }
    $check('token-sken: žádné zápisy starými obaly mimo jádro', $hits === [], implode(', ', array_slice($hits, 0, 8)));
    $kernel = (string)file_get_contents($root . '/storage_v58.php');
    foreach (['storage_read', 'storage_update', 'storage_update_many', 'storage_write', 'storage_append', 'storage_scan'] as $fn) {
        $check("jádro definuje $fn", preg_match('/function ' . $fn . '\(/', $kernel) === 1);
    }
    $boot = (string)file_get_contents($root . '/bootstrap.php');
    $check('bootstrap načítá storage_v58.php a nedefinuje storage_update znovu', str_contains($boot, "require_once __DIR__ . '/storage_v58.php'") && preg_match('/function storage_update\(/', $boot) !== 1);
    $check('storage_v58.php: strict_types, guard, < 800 řádků', str_contains($kernel, 'declare(strict_types=1);') && str_contains($kernel, '=== basename(__FILE__)) { http_response_code(403); exit; }') && substr_count($kernel, "\n") < 800);

    // 2 + 3. souběh a čtení během zápisu ----------------------------------------------
    storage_write(STORAGE_DIR . '/sa_counter.json.php', ['counter' => 0, 'rows' => []]);
    $readers = [$spawn(['--child=read']), $spawn(['--child=read'])];
    $procs = [];
    for ($w = 0; $w < SA_WORKERS; $w++) {
        $procs[] = $spawn(['--child=update', '--id=' . $w]);
        $procs[] = $spawn(['--child=append', '--id=' . $w]);
    }
    $res = $collect($procs);
    touch(STORAGE_DIR . '/sa_stop');
    $readRes = $collect($readers);
    $bad = array_filter($res, static fn(array $r): bool => $r['code'] !== 0 || !str_contains($r['out'], 'CHILD_OK'));
    $check('souběh: všech 40 zapisujících procesů doběhlo bez chyby', $bad === [], $bad ? trim((string)reset($bad)['out']) : '');
    unset($GLOBALS['educanet_json_request_cache'], $GLOBALS['educanet_stream_cache']);
    $d = storage_read(STORAGE_DIR . '/sa_counter.json.php');
    $expected = SA_WORKERS * SA_PER_WORKER;
    $check("storage_update: 20 × 50 = $expected zápisů beze ztráty", (int)($d['counter'] ?? 0) === $expected && count(array_unique((array)($d['rows'] ?? []))) === $expected, 'counter=' . (int)($d['counter'] ?? 0));
    $streamRows = storage_stream_rows('sa_stream');
    $uniq = array_unique(array_map(static fn(array $r): string => $r['w'] . ':' . $r['i'], $streamRows));
    $check("storage_append: 20 × 50 = $expected řádků beze ztráty a bez poškození", count($streamRows) === $expected && count($uniq) === $expected, 'rows=' . count($streamRows));
    $file = storage_stream_file('sa_stream', date('Y-m'));
    $check('proud má ochranný 1. řádek právě jednou', str_starts_with((string)file_get_contents($file), STORAGE_GUARD_LINE) && substr_count((string)file_get_contents($file), '<?php') === 1);
    foreach ($readRes as $i => $r) {
        $ok = preg_match('/READ reads=(\d+) bad=(\d+)/', $r['out'], $m) === 1 && (int)$m[2] === 0 && (int)$m[1] > 0;
        $check('čtení během zápisu #' . ($i + 1) . ' nikdy nevrátilo [] ani poškozená data', $ok, $ok ? $m[0] : trim($r['out']));
    }

    // 4. storage_update_many bez deadlocku --------------------------------------------
    $procs = [];
    for ($w = 0; $w < 10; $w++) $procs[] = $spawn(['--child=many', '--n=30', '--order=' . ($w % 2 ? 'ba' : 'ab')]);
    $t0 = microtime(true);
    $res = $collect($procs);
    unset($GLOBALS['educanet_json_request_cache']);
    $a = (int)(storage_read(STORAGE_DIR . '/sa_many_a.json.php')['n'] ?? 0);
    $b = (int)(storage_read(STORAGE_DIR . '/sa_many_b.json.php')['n'] ?? 0);
    $check('storage_update_many: opačné pořadí cest bez deadlocku, 10 × 30 = 300', $a === 300 && $b === 300 && !array_filter($res, static fn($r) => $r['code'] !== 0), "a=$a b=$b t=" . round(microtime(true) - $t0, 1) . 's');
    $nested = false;
    try { storage_update(STORAGE_DIR . '/sa_many_a.json.php', static function (array $d): array { storage_update(STORAGE_DIR . '/sa_many_a.json.php', static fn(array $x): array => $x); return $d; }); } catch (RuntimeException $e) { $nested = true; }
    $check('vnořený zápis do stejného souboru = výjimka (ne zamrznutí)', $nested);
    $corrupt = STORAGE_DIR . '/sa_corrupt.json.php';
    file_put_contents($corrupt, STORAGE_GUARD_LINE . '{"poškozeno":');
    $threw = false;
    try { storage_update($corrupt, static fn(array $x): array => ['x' => 1]); } catch (RuntimeException $e) { $threw = true; }
    $check('poškozený JSON: výjimka a soubor zůstane nepřepsaný', $threw && str_contains((string)file_get_contents($corrupt), 'poškozeno'));
    $check('storage_read(strict=false) vrátí u poškozeného souboru []', storage_read($corrupt, false) === []);

    // 4b. DAT58-04: atomický zápis (tmp + fsync + rename), simulace selhání zápisu -------------
    $atomic = STORAGE_DIR . '/sa_atomic.json.php';
    storage_write($atomic, ['keep' => 'původní', 'n' => 1]);
    $orig = (string)file_get_contents($atomic);
    $tmpLeft = static fn(): array => glob(STORAGE_DIR . '/*.tmp') ?: [];
    foreach (['write', 'rename'] as $stage) {
        $GLOBALS['educanet_storage_fault'] = $stage;
        $threw = false;
        try { storage_update($atomic, static fn(array $d): array => ['keep' => 'nové', 'n' => 2, 'pad' => str_repeat('x', 5000)]); } catch (RuntimeException $e) { $threw = true; }
        unset($GLOBALS['educanet_storage_fault']);
        $check("DAT58-04: selhání fáze '$stage' → výjimka, původní soubor beze změny, žádný .tmp", $threw && (string)file_get_contents($atomic) === $orig && $tmpLeft() === []);
    }
    unset($GLOBALS['educanet_json_request_cache']);
    $check('DAT58-04: po selhání jde další zápis normálně', storage_update($atomic, static fn(array $d): array => $d + ['ok' => true])['ok'] === true && (storage_read($atomic)['keep'] ?? '') === 'původní');
    $check('DAT58-04: zámek je vedlejší soubor <soubor>.lock, data jsou celý guard+JSON', is_file($atomic . '.lock') && str_starts_with((string)file_get_contents($atomic), STORAGE_GUARD_LINE));
    $streamFile = storage_stream_file('sa_stream', date('Y-m'));
    $before = (string)file_get_contents($streamFile);
    $GLOBALS['educanet_storage_fault'] = 'write';
    $threw = false;
    try { storage_append('sa_stream', ['w' => 'fault', 'i' => 0]); } catch (RuntimeException $e) { $threw = true; }
    unset($GLOBALS['educanet_storage_fault'], $GLOBALS['educanet_stream_cache']);
    $check('DAT58-04: selhání připojení do proudu → výjimka, proud beze změny', $threw && (string)file_get_contents($streamFile) === $before);
    $check('DAT58-04: po souběhu nezůstal žádný dočasný soubor', $tmpLeft() === [] && (glob(STORAGE_DIR . '/*/*.tmp') ?: []) === []);
    $nestedRead = storage_update($atomic, static function (array $d) use ($atomic): array {
        $d['seen'] = storage_read($atomic)['keep'] ?? null; // čtení zamčeného souboru uvnitř $mutate nezamrzne
        return $d;
    });
    $check('DAT58-04: čtení vlastního zamčeného souboru uvnitř $mutate vrátí potvrzený stav', ($nestedRead['seen'] ?? null) === 'původní');
    foreach (['teamgames_v58_core.php' => 'function tg58_read', 'robots_v58.php' => 'function robots58_read', 'linux_v57_lab.php' => 'function lab57_store_read'] as $mod => $fn) {
        $src = (string)file_get_contents($root . '/' . $mod);
        $body = substr($src, (int)strpos($src, $fn), 400);
        $check("DAT58-04: $mod čte přes storage_read (sdílený zámek)", str_contains($body, 'storage_read('));
    }

    // 5. migrace --------------------------------------------------------------------
    require_once __DIR__ . '/migrate.php';
    $legacyRows = [];
    for ($i = 0; $i < 30; $i++) $legacyRows[] = ['class_id' => 'class_3a', 'student_label' => 'Žák ' . ($i % 4), 'score' => $i, 'finished_at' => date(DATE_ATOM, strtotime('2026-0' . (6 + $i % 3) . '-1' . ($i % 9) . ' 10:00'))];
    $legacyRows[] = $legacyRows[3]; // duplicitní záznam se musí zachovat
    $legacyRows[] = ['class_id' => 'class_3a', 'note' => 'bez času'];
    storage_write(STORAGE_DIR . '/practice_results.json.php', $legacyRows);
    storage_write(STORAGE_DIR . '/adaptive_v505_events.json.php', ['ot_a' => ['id' => 'ot_a', 'created_at' => '2026-07-01T10:00:00+02:00'], 'ot_b' => ['id' => 'ot_b', 'created_at' => '2026-08-01T10:00:00+02:00']]);
    storage_write(STORAGE_DIR . '/learning_profiles.json.php', ['k1' => ['xp' => 5], 'k2' => ['xp' => 7, 'version' => 3]]);
    storage_append('practice_results', ['class_id' => 'class_3a', 'student_label' => 'Nový', 'score' => 1, 'finished_at' => date(DATE_ATOM)]);
    $before = count(storage_stream_rows('practice_results'));
    $migrations = migrate_load_all($root . '/migrations');
    $check('migrace: nalezeny 0001 a 0002 s id, popisem, soubory a up', isset($migrations['0001_streams_jsonl'], $migrations['0002_profile_versions'], $migrations['0003_projects_v65']) && is_array($migrations['0001_streams_jsonl']['files'] ?? null));
    $h0 = $treeHash(STORAGE_DIR);
    $dry = migrate_run($migrations, true);
    $check('migrate --dry-run nic nezmění', $treeHash(STORAGE_DIR) === $h0 && count($dry['pending']) === 3 && $dry['applied'] === []); // v65: přibyla migrace 0003 (sidecary projektů)
    $applied = migrate_run($migrations, false);
    unset($GLOBALS['educanet_json_request_cache'], $GLOBALS['educanet_stream_cache']);
    $after = count(storage_stream_rows('practice_results'));
    $check('migrate --apply: všechny záznamy zachovány (počty před/po)', $before === 33 && $after === $before, "před=$before po=$after");
    $check('migrate --apply: legacy soubor přesunut do _migrated_v58 (nesmazán)', !is_file(STORAGE_DIR . '/practice_results.json.php') && is_file(STORAGE_DIR . '/_migrated_v58/practice_results.json.php'));
    $check('migrate --apply: události v50.5 (mapa) převedeny', count(storage_stream_rows('adaptive_v505_events')) === 2);
    $profiles = storage_read(STORAGE_DIR . '/learning_profiles.json.php');
    $check('migrace 0002: profily mají verzi, XP beze změny', ($profiles['k1']['version'] ?? null) === 0 && ($profiles['k2']['version'] ?? null) === 3 && ($profiles['k1']['xp'] ?? null) === 5);
    $check('manifest schémat _schema_v58.json.php zapsán', storage_schema_version('practice_results') === 2 && storage_schema_version('learning_profiles.json.php') === 2);
    $check('záznam provedených migrací', count(storage_read(migrate_log_path())) === 3 && count($applied['applied']) === 3 && is_file(STORAGE_DIR . '/projects_v65_cycle.json.php'));
    $h1 = $treeHash(STORAGE_DIR);
    $again = migrate_run($migrations, false);
    $direct = ($migrations['0001_streams_jsonl']['up'])(false);
    ($migrations['0002_profile_versions']['up'])(false);
    $check('migrate --apply je idempotentní (2. běh i přímé up() nic nezmění)', $again['pending'] === [] && $treeHash(STORAGE_DIR) === $h1 && ($direct['details'] ?? []) === []);
    $scan = iterator_to_array(storage_scan('practice_results', '2026-07', '2026-07', static fn(array $r): bool => (int)($r['score'] ?? -1) % 2 === 1, 3), false);
    $check('storage_scan: rozsah měsíců, filtr a limit', count($scan) === 3 && !array_filter($scan, static fn($r) => !str_starts_with((string)$r['finished_at'], '2026-07')));

    // 6. Z2 -----------------------------------------------------------------------
    $res = $collect([$spawn(['--child=xp', '--id=A', '--n=25']), $spawn(['--child=xp', '--id=B', '--n=25'])]);
    $_SESSION['local_user'] = ['id' => 'sa-audit-local', 'email' => 'sa.audit@educanet.cz'];
    $_SESSION['student_label'] = 'Audit Testovací';
    unset($GLOBALS['educanet_json_request_cache']);
    $key = learning_profile_key('class_3a');
    $p = storage_read(STORAGE_DIR . '/learning_profiles.json.php')[$key] ?? [];
    $check('Z2: souběžné XP ze dvou session se sečte (2 × 25 × 10 = 500)', (int)($p['xp'] ?? 0) === 500 && count((array)($p['events'] ?? [])) === 50 && !array_filter($res, static fn($r) => $r['code'] !== 0), 'xp=' . (int)($p['xp'] ?? 0));
    $check('Z2: změny postupu z obou session zachovány', !empty($p['kb']['sa-topic-A']['visual']) && !empty($p['kb']['sa-topic-B']['visual']));
    $stale = learning_profile('class_3a'); // zastaralá kopie (např. otevřená stránka na jiném zařízení)
    storage_map_update(STORAGE_DIR . '/learning_profiles.json.php', $key, static function (?array $cur): array {
        $cur['xp'] = (int)$cur['xp'] + 40;                 // učitel přidal body
        $cur['badges']['teacher_award'] = ['earned_at' => date(DATE_ATOM)];
        $cur['version'] = (int)$cur['version'] + 1;
        return $cur;
    });
    $stale['kb']['sa-stale']['check'] = true;
    $stale['xp'] = (int)$stale['xp'] + 5;
    learning_save_profile('class_3a', $stale);
    $p = storage_read(STORAGE_DIR . '/learning_profiles.json.php')[$key] ?? [];
    $check('Z2: zastaralá kopie nepřepíše změny od učitele (sloučení, XP se sčítá)', (int)$p['xp'] === 545 && isset($p['badges']['teacher_award']) && !empty($p['kb']['sa-stale']['check']), 'xp=' . (int)$p['xp']);
    $check('Z2: session kopie se po zápisu obnoví z úložiště', (int)($_SESSION['learning_profiles'][$key]['xp'] ?? 0) === 545 && (int)$_SESSION['learning_profiles'][$key]['version'] === (int)$p['version']);
    $check('Z2: learning_award_once je idempotentní', learning_award_once('class_3a', 'sa_xp:A:0', 10) === false && (int)(learning_profile('class_3a')['xp'] ?? 0) === 545);
} catch (Throwable $e) {
    $check('audit doběhl bez výjimky', false, $e->getMessage() . ' @' . basename($e->getFile()) . ':' . $e->getLine());
} finally {
    $rrm($saTmp);
}

echo ($failed === 0 ? 'V58_STORAGE_AUDIT_OK' : 'V58_STORAGE_AUDIT_FAIL') . " checks=$checks failed=$failed\n";
exit($failed === 0 ? 0 : 1);
