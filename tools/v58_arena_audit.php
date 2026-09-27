<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Aréna – CLI audit ARN-01 (Týdenní hádanka), ARN-05 (Záznam závodu), ARN-06 (férové režimy).
 * Běží nad dočasným úložištěm (nikdy nesahá na storage/). Spuštění: php tools/v58_arena_audit.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$tmp = rtrim(sys_get_temp_dir(), '/\\') . '/educanet_arena58_audit_' . bin2hex(random_bytes(5));
if (!mkdir($tmp, 0770, true) && !is_dir($tmp)) { fwrite(STDERR, "Nelze vytvořit dočasné úložiště.\n"); exit(1); }
$GLOBALS['lab57_storage_override'] = $tmp;
$GLOBALS['lab57_secret_override'] = 'arena58-audit-secret';

require_once $root . '/linux_v57_lab.php';
require_once $root . '/arena_v57.php';
require_once $root . '/arena_v58_weekly.php';
require_once $root . '/arena_v58_replay.php';
require_once $root . '/arena_v58_views.php';

if (!function_exists('learning_award_once')) {
    $GLOBALS['audit_xp'] = [];
    function learning_award_once(string $classId, string $eventKey, int $xp): bool
    {
        $key = $classId . '|' . $eventKey;
        if (isset($GLOBALS['audit_xp'][$key])) return false;
        $GLOBALS['audit_xp'][$key] = $xp;
        return true;
    }
}
if (!function_exists('render_header')) { function render_header(string $title, ?array $module = null): void { echo "<!--header:$title-->"; } }
if (!function_exists('render_footer')) { function render_footer(): void { echo '<!--footer-->'; } }
if (!function_exists('teacher_flash')) { function teacher_flash(string $m, string $t = 'ok'): void { $GLOBALS['audit_flash'] = $m; } }
if (!function_exists('teacher_redirect')) { function teacher_redirect(array $p = []): never { $GLOBALS['audit_redirect'] = $p; exit(0); } }

$checks = 0;
$failed = 0;
function check(bool $ok, string $label, string $detail = ''): void
{
    global $checks, $failed;
    $checks++;
    if (!$ok) $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . ($ok || $detail === '' ? '' : ' – ' . $detail) . PHP_EOL;
}

function throws(callable $fn, string $needle = ''): bool
{
    try { $fn(); } catch (RuntimeException $e) { return $needle === '' || str_contains($e->getMessage(), $needle); }
    return false;
}

function at(int $ts): void { $GLOBALS['arena57_now_override'] = $ts; }

function rm_tree(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

/** Reálně vyřeší weekly úlohu přes engine (op=run po jednotlivých příkazech ze solution()). */
function weekly_solve(string $classId, string $studentKey, string $label, string $weekKey, string $levelId, int $now): array
{
    $level = lab57_level($levelId);
    $ctxStr = 'weekly:' . $weekKey;
    $world = lab57_build_world($level, lab57_seed($classId, $studentKey, $ctxStr, $level), [], $now);
    $ctx = ['class' => $classId, 'student' => $studentKey, 'label' => $label, 'context' => $ctxStr, 'level' => $levelId, 'now' => $now, 'cli' => true];
    $last = ['ok' => false];
    foreach (($level['solution'])($world) as $cmd) {
        $last = lab57_session($ctx, 'run', ['line' => $cmd]);
        if (!empty($last['solved']) || empty($last['ok'])) break;
    }
    return $last;
}

/** Vloží syntetickou událost solve přímo do souboru týdne (pro rychlé testy žebříčku bez skládání golfu). */
function inject_solve(string $weekKey, string $classId, string $studentKey, string $label, string $levelId, int $len, int $secs, int $at, string $cmd = ''): void
{
    lab57_store_update(lab57_storage_dir() . '/linux_v58/ctx_weekly_' . preg_replace('/[^a-z0-9_-]/', '', $weekKey) . '.events.json.php', static function (array $rows) use ($weekKey, $classId, $studentKey, $label, $levelId, $len, $secs, $at, $cmd): array {
        $rows[] = ['id' => bin2hex(random_bytes(6)), 'kind' => 'solve', 'class_id' => $classId, 'student_key' => $studentKey, 'label' => $label, 'ctx' => 'weekly:' . $weekKey, 'level' => $levelId, 'pack' => 'tyden', 'at' => date(DATE_ATOM, $at), 'points' => 100, 'hints' => 0, 'cmds' => 3, 'secs' => $secs, 'golf_len' => $len, 'golf_cmd' => $cmd];
        return $rows;
    });
}

/** Skutečná volání zakázaných funkcí a shell-exec zpětnými apostrofy – přes tokenizer, ne hrubé podřetězce
 *  (jinak by hlásilo i legitimní `lab57_golf_eval()`, slovo „system“ v datech puzzlu nebo `` `kód` `` v komentáři). */
function security_scan_file(string $path): array
{
    $forbiddenCalls = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'stream_socket_client', 'socket_create', 'curl_init', 'dns_get_record', 'gethostbyname', 'checkdnsrr', 'mail'];
    $tokens = token_get_all((string)file_get_contents($path));
    $violations = [];
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if ($t === '`') { $violations[] = 'backtick shell-exec'; continue; }
        if (!is_array($t) || $t[0] !== T_STRING) continue;
        $name = strtolower($t[1]);
        if (in_array($name, $forbiddenCalls, true)) {
            for ($j = $i + 1; $j < $n; $j++) {
                $nt = $tokens[$j];
                if (is_array($nt) && in_array($nt[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
                if ($nt === '(') $violations[] = $name . '()';
                break;
            }
        }
        if ($name === 'file_get_contents' || $name === 'fopen') {
            for ($j = $i + 1; $j < min($n, $i + 6); $j++) {
                $nt = $tokens[$j];
                if (is_array($nt) && $nt[0] === T_CONSTANT_ENCAPSED_STRING) {
                    if (preg_match('~^[\'"](https?|ftp)://~i', $nt[1]) === 1) $violations[] = $name . '(URL)';
                    break;
                }
            }
        }
    }
    return $violations;
}

try {
    // --- 403 na cizí řešení před uzávěrkou (musí běžet jako úplně první věc v procesu –
    // jakmile jednou něco vypíšeme na stdout, CLI SAPI si "hlavičky" už myslí, že jsou odeslané). ---
    at(1000);
    $earlyWeek = arena58_weekly_week(arena58_weekly_current_key(1000));
    $_GET['reseni'] = '1';
    ob_start();
    arena58_weekly_render_student('class_3a', []);
    $out403 = (string)ob_get_clean();
    check(http_response_code() === 403, 'requesting foreign solutions before the deadline returns HTTP 403', (string)http_response_code());
    check(str_contains($out403, 'odemknou'), 'HTTP 403 response explains when solutions unlock');
    // Odemčeno (učitel odhalí dřív – to je jediný způsob, jak se „tenhle týden“ ještě před uzávěrkou
    // odemkne přes stejnou routu) → stejná routa už neblokuje (obsahově, response kód v CLI harnessu
    // po prvním vypsání na stdout už jednou nastavit nejde, viz výše).
    arena58_weekly_set_revealed($earlyWeek['key'], true, 1000);
    ob_start();
    arena58_weekly_render_student('class_3a', []);
    $outAfter = (string)ob_get_clean();
    check(!str_contains($outAfter, 'odemknou'), 'same route no longer blocks once the teacher reveals solutions early', $outAfter);
    unset($_GET['reseni']);
    at(1500);

    // --- Bezpečnostní invariant: token-sken vlastních souborů --------------------
    $ownFiles = ['arena_v57.php', 'arena_v57_views.php', 'arena_v58_weekly.php', 'arena_v58_replay.php', 'arena_v58_views.php', 'linux_v58_levels_weekly.php'];
    $scanOk = true;
    $scanDetail = '';
    foreach ($ownFiles as $f) {
        foreach (security_scan_file($root . '/' . $f) as $v) { $scanOk = false; $scanDetail .= "$f:$v "; }
    }
    check($scanOk, 'security: no forbidden exec/network calls in own files (token-level, not substring)', $scanDetail);

    // --- Rotace: klíč týdne, hranice, deterministika ----------------------------
    $mon = (new DateTimeImmutable('2026-09-21 00:00:00', new DateTimeZone('Europe/Prague')))->getTimestamp();
    check(arena58_weekly_key($mon) === '2026t39', 'week key for known Monday', arena58_weekly_key($mon));
    check(arena58_weekly_key($mon + 6 * 86400 + 3600) === '2026t39', 'still same week 1h before next rollover');
    check(arena58_weekly_key($mon + 7 * 86400) === '2026t40', 'next Monday 00:00 rolls over to next week key');
    $b = arena58_weekly_bounds('2026t39');
    check($b !== null && $b['start'] === $mon && $b['end'] === $mon + 7 * 86400, 'bounds: Mon 00:00 .. next Mon 00:00 (Prague, no DST here)', json_encode($b));
    // DST hranice (jarní čas CZ 2026-03-29): rozdíl start/end musí být 7 dní kalendářně, ne 604800 s.
    $dstBounds = arena58_weekly_bounds('2026t13');
    check($dstBounds !== null && ($dstBounds['end'] - $dstBounds['start']) === 6 * 86400 + 23 * 3600, 'DST spring week is 1h shorter in seconds but still calendar +7 days', json_encode($dstBounds));
    check(arena58_weekly_bounds('9999t99') === null, 'invalid week key rejected');
    check(arena58_weekly_bounds('abcdxyz') === null, 'malformed week key rejected');
    $k1 = arena58_weekly_default_level_id('2026t39');
    $k2 = arena58_weekly_default_level_id('2026t39');
    check($k1 !== '' && $k1 === $k2, 'default level assignment is deterministic for the same week key');
    $variants = array_unique(array_map('arena58_weekly_default_level_id', ['2026t01', '2026t10', '2026t20', '2026t30', '2026t40', '2026t50']));
    check(count($variants) >= 3, 'default level varies across different weeks (not a constant)', json_encode($variants));

    // --- Přístup a hranice ochoty hrát ------------------------------------------
    $WK = '2026t39';
    $week = arena58_weekly_week($WK);
    check($week !== null && isset(lab57_level($week['level'])['id']), 'current week resolves to a real bank level', json_encode($week));
    $lvl = lab57_level($week['level']);
    at($week['start'] - 10);
    check(arena58_weekly_access($lvl, ['id' => $WK, 'now' => $week['start'] - 10]) !== null, 'access denied before the week starts');
    at($week['start'] + 10);
    check(arena58_weekly_access($lvl, ['id' => $WK, 'now' => $week['start'] + 10]) === null, 'access allowed once the week starts');
    check(arena58_weekly_access(['id' => 'jina-uloha'], ['id' => $WK, 'now' => $week['start'] + 10]) !== null, 'wrong puzzle for this week is denied');
    check(arena58_weekly_levels_for(['id' => $WK]) === [$week['level']], 'levels() restricts to exactly this week\'s puzzle');

    // --- Učitel: přeskočení / vlastní výběr / odhalení --------------------------
    $bank = array_keys(arena58_weekly_bank_by_id());
    check(count($bank) >= 12, 'bank has at least 12 golf puzzles', (string)count($bank));
    $other = $bank[0] === $week['level'] ? $bank[1] : $bank[0];
    arena58_weekly_set_override($WK, $other, $week['start'] + 10);
    $afterOverride = arena58_weekly_week($WK);
    check($afterOverride['level'] === $other && $afterOverride['override'] === true, 'teacher override picks the chosen level');
    $rerolled = arena58_weekly_reroll($WK, $week['start'] + 10);
    check($rerolled !== $other && in_array($rerolled, $bank, true), 'reroll picks a different valid level from the bank');
    arena58_weekly_set_override($WK, null, $week['start'] + 10);
    check(arena58_weekly_week($WK)['level'] === $week['level'], 'clearing override restores the deterministic default');
    check(throws(fn() => arena58_weekly_set_override($WK, 'neexistuje-999', $week['start']), 'neexistuje'), 'override rejects unknown level id');
    check(arena58_weekly_reveal_allowed(arena58_weekly_week($WK), $week['start'] + 10) === false, 'solutions locked mid-week by default');
    arena58_weekly_set_revealed($WK, true, $week['start'] + 10);
    check(arena58_weekly_reveal_allowed(arena58_weekly_week($WK), $week['start'] + 10) === true, 'teacher can reveal solutions early');
    arena58_weekly_set_revealed($WK, false, $week['start'] + 10);
    check(arena58_weekly_reveal_allowed(arena58_weekly_week($WK), $week['start'] + 10) === false, 'teacher can lock solutions back');

    // --- Reálné vyřešení přes engine (end-to-end golf → událost) ----------------
    at($week['start'] + 100);
    $A = 'class_3a:student:aaaa';
    $res = weekly_solve('class_3a', $A, 'Adam Kovář', $WK, $week['level'], $week['start'] + 100);
    check(!empty($res['solved']) && isset($res['points']), 'engine solves the weekly golf level end-to-end', json_encode($res));
    $solvesReal = arena58_weekly_solves($WK, $week['level'], $week['start'] + 200);
    check(count($solvesReal) === 1 && $solvesReal[0]['student_key'] === $A && $solvesReal[0]['len'] > 0, 'solve event recorded with a real golf_len', json_encode($solvesReal));
    // Reset a druhé (jiné) řešení – osobní rekord bere kratší/lepší pokus, ne jen první.
    lab57_session(['class' => 'class_3a', 'student' => $A, 'label' => 'Adam Kovář', 'context' => 'weekly:' . $WK, 'level' => $week['level'], 'now' => $week['start'] + 150], 'reset');
    weekly_solve('class_3a', $A, 'Adam Kovář', $WK, $week['level'], $week['start'] + 160);
    check(count(arena58_weekly_solves($WK, $week['level'], $week['start'] + 200)) === 1, 'multiple solves by the same student still count once (best kept)');

    // --- Žebříček: syntetická data pro rychlé a přesné testy pořadí/soukromí ---
    $WK2 = '2020t01'; // platný ISO klíč mimo aktuální rotaci – jen pro izolovaný test žebříčku/soukromí
    $lvlId = $week['level'];
    arena58_weekly_set_override($WK2, $lvlId, 1); // ať board() vidí stejnou úlohu, jakou mají injektované události
    inject_solve($WK2, 'class_3a', 'class_3a:student:bbbb', 'Bára Novotná', $lvlId, 40, 30, 1000, 'cut -d, -f1 x | sort -u');
    inject_solve($WK2, 'class_3a', 'class_3a:student:cccc', 'Cyril Dvořák', $lvlId, 25, 50, 1010, 'wc -w < x');
    inject_solve($WK2, 'class_3a', 'class_3a:student:dddd', 'Dana Malá', $lvlId, 25, 20, 1020, 'wc -c < x'); // shoda délky, kratší čas vyhrává
    inject_solve($WK2, 'class_2a', 'class_2a:student:p1', 'Petr Jedna', $lvlId, 10, 90, 1030, 'echo x');
    inject_solve($WK2, 'class_3a', 'class_3a:student:late', 'Pozdní Žák', $lvlId, 5, 5, 999999999, 'x'); // po uzávěrce – nesmí se počítat
    $solves = arena58_weekly_solves($WK2, $lvlId, 5000);
    check(count($solves) === 4, 'solves after the cutoff are excluded from ranking', (string)count($solves));
    $ranked = arena58_weekly_rank($solves);
    check($ranked[0]['student_key'] === 'class_2a:student:p1', 'shortest solution (len 10) ranks first overall', json_encode(array_column($ranked, 'student_key')));
    check($ranked[1]['student_key'] === 'class_3a:student:dddd' && $ranked[2]['student_key'] === 'class_3a:student:cccc', 'ties on length break by time (Dana 25/20s before Cyril 25/50s)', json_encode(array_column($ranked, 'student_key')));
    check($ranked[3]['student_key'] === 'class_3a:student:bbbb' && $ranked[3]['rank'] === 4, 'longer solution ranks last');
    $classScope = arena58_weekly_scope_rows($solves, 'class_3a');
    check(count($classScope) === 3 && !in_array('class_2a:student:p1', array_column($classScope, 'student_key'), true), 'class scope excludes other classes');
    $schoolScope = arena58_weekly_scope_rows($solves, null);
    check(count($schoolScope) === 4, 'school scope includes every class');

    // --- Soukromí: cizí řešení (cmd) skryté před uzávěrkou, vlastní vidět vždy -
    $pubBefore = arena58_weekly_public_rows($schoolScope, 'class_3a:student:cccc', false);
    $mineRow = current(array_filter($pubBefore, static fn(array $r): bool => $r['me']));
    $otherRow = current(array_filter($pubBefore, static fn(array $r): bool => !$r['me']));
    check($mineRow !== false && $mineRow['cmd'] !== null, 'own solution text visible to self before the deadline');
    check($otherRow !== false && $otherRow['cmd'] === null, 'foreign solution text hidden before the deadline (data-level, not just UI)');
    $pubAfter = arena58_weekly_public_rows($schoolScope, 'class_3a:student:cccc', true);
    check(!array_filter($pubAfter, static fn(array $r): bool => $r['cmd'] === null), 'all solutions visible once unlocked');
    check(current($pubBefore)['name'] !== '' && !str_contains(json_encode($pubBefore), 'Bára Novotná'), 'weekly board never shows full names to students (initials only)');

    // --- Board() end-to-end (kontext callback) ----------------------------------
    at(1500);
    $boardMe = arena58_weekly_board(['id' => $WK2, 'class' => 'class_3a', 'student' => 'class_3a:student:cccc', 'now' => 1500]);
    check($boardMe['me']['solved'] === true && $boardMe['me']['rank_class'] === 2 && $boardMe['me']['rank_school'] === 3, 'board() computes own class+school rank correctly', json_encode($boardMe['me']));
    check($boardMe['solutions_unlocked'] === false, 'board() reports puzzle still locked mid-week');
    check(count($boardMe['class']['rows']) === 3 && count($boardMe['school']['rows']) === 4, 'board() rows scoped correctly (class vs school)');

    at(1500);

    // --- ARN-06: hodnocení závodu (žebříček/osobní rekord/kategorie), skrytí pořadí ---
    $classes = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
    $base = ['class_id' => 'class_3a', 'preset' => 'custom', 'levels' => ['start-1', 'start-2'], 'duration_min' => '10', 'mode' => 'solo', 'hint_penalty' => '0.2', 'names' => 'initials', 'class_goal' => '0', 'title' => 'ARN-06'];
    check(throws(fn() => arena57_parse_create(['rating' => 'kategorie', 'mode' => 'teams'] + $base, $classes), 'jednotlivců'), 'kategorie rejected for team races');
    check(throws(fn() => arena57_parse_create(['rating' => 'neco'] + $base, $classes), 'hodnocení'), 'unknown rating rejected');

    $roster10 = [];
    foreach (range(1, 10) as $i) $roster10['class_3a:student:s' . $i] = ['label' => 'Žák Číslo' . $i];
    $GLOBALS['arena57_roster_override']['class_3a'] = $roster10;
    $T = 5_000_000;
    at($T);
    $rZeb = arena57_start_race(arena57_create_race(['rating' => 'zebricek'] + $base, $classes, $T)['id'], $T);
    foreach (array_keys($roster10) as $i => $key) {
        solve_start($key, 'Žák Číslo' . ($i + 1), (string)$rZeb['id'], $T + 10 + $i);
    }
    $boardHide = arena57_board((string)$rZeb['id'], array_key_first($roster10), 'class_3a');
    check($boardHide['rank_hidden'] === false && $boardHide['rows'] !== [], 'zebricek: rank visible by default');
    check(arena57_toggle_hide_rank((string)$rZeb['id'], 'class_3a', array_key_first($roster10)) === true, 'student can opt out of the ranking');
    $meHidden = arena57_board((string)$rZeb['id'], array_key_first($roster10), 'class_3a');
    check($meHidden['rank_hidden'] === true && $meHidden['rows'] === [] && $meHidden['me']['rank'] === null, 'opted-out student sees no ranking data at all (rows empty, own rank hidden)');
    $otherView = arena57_board((string)$rZeb['id'], array_keys($roster10)[1], 'class_3a');
    check($otherView['rank_hidden'] === false && count($otherView['rows']) === 9, 'opted-out student is excluded from OTHER students\' view of the ranking (10 solved, 1 hidden → 9 rows)', (string)count($otherView['rows']));
    $rZebFresh = arena57_race((string)$rZeb['id']); // hide_rank žije v uloženém závodu, ne ve staré proměnné z create/start
    $projectorRows = arena57_public_rows($rZebFresh, arena57_compute($rZebFresh)['students'], arena57_roster('class_3a'), null, ARENA57_BOARD_TOP);
    check(count($projectorRows) === 9, 'opted-out student is excluded from the projector too');

    at($T + 3600);
    $rSolo = arena57_start_race(arena57_create_race(['rating' => 'osobni_rekord'] + $base, $classes, $T + 3600)['id'], $T + 3600);
    $keys10 = array_keys($roster10);
    foreach ($keys10 as $i => $key) solve_start($key, 'Žák Číslo' . ($i + 1), (string)$rSolo['id'], $T + 3610 + $i);
    $pr = arena57_board((string)$rSolo['id'], $keys10[3], 'class_3a');
    check($pr['rating'] === 'osobni_rekord' && $pr['rows'] === [] && $pr['teams'] === [] && $pr['feed'] === [], 'osobni_rekord: no foreign ranking anywhere in the API response');
    check($pr['me']['points'] > 0 && $pr['me']['rank'] === null, 'osobni_rekord: own points visible, own rank still hidden');
    check(is_array($pr['previous_best']), 'osobni_rekord: previous_best block present for comparison against past races');

    at($T + 7200);
    $rCat = arena57_start_race(arena57_create_race(['rating' => 'kategorie', 'category_count' => '3'] + $base, $classes, $T + 7200)['id'], $T + 7200);
    check(count($rCat['categories']) >= 2 && count($rCat['categories']) <= 3, '3 teams requested: 2-3 non-empty categories formed', json_encode(array_map(static fn($c) => count($c['members']), $rCat['categories'])));
    $seen = [];
    foreach ($rCat['categories'] as $c) foreach ($c['members'] as $m) $seen[] = $m;
    check(count($seen) === count(array_unique($seen)) && count($seen) === 10, 'every student assigned to exactly one category');
    foreach ($keys10 as $i => $key) solve_start($key, 'Žák Číslo' . ($i + 1), (string)$rCat['id'], $T + 7210 + $i);
    $catBoard = arena57_board((string)$rCat['id'], $keys10[0], 'class_3a');
    check($catBoard['categories'] !== [], 'kategorie: board exposes per-category leaderboards');
    $myCat = current(array_filter($catBoard['categories'], static fn(array $c): bool => $c['mine']));
    check($myCat !== false && count($myCat['rows']) === count(current(array_filter($rCat['categories'], static fn($c) => in_array($keys10[0], $c['members'], true)))['members']), 'own category board only ranks members of that category');

    // --- Cizí kód pořád nedává body (regrese, i po změnách ARN-06) --------------
    $codeOwner = lab57_code('class_3a', $keys10[0], 'race:' . $rZeb['id'], 'start-2');
    $foreignAttempt = lab57_session(['class' => 'class_3a', 'student' => $keys10[1], 'label' => 'X', 'context' => 'race:' . $rZeb['id'], 'level' => 'start-2', 'now' => $T + 20, 'classmates' => $keys10], 'run', ['line' => 'submit ' . $codeOwner]);
    check(!empty($foreignAttempt['ok']) && empty($foreignAttempt['solved']), 'submitting a classmate\'s code is never a solve, even in ARN-06 races');

    // --- Souběžné zápisy: >= 10 procesů řeší stejnou hádanku bez ztráty dat ----
    $workerFile = $tmp . '/weekly_worker.php';
    file_put_contents($workerFile, '<?php
declare(strict_types=1);
$root = ' . var_export($root, true) . ';
$GLOBALS["lab57_storage_override"] = ' . var_export($tmp, true) . ';
$GLOBALS["lab57_secret_override"] = ' . var_export('arena58-audit-secret', true) . ';
require_once $root . "/linux_v57_lab.php";
require_once $root . "/arena_v58_weekly.php";
$student = $argv[1]; $now = (int)$argv[2]; $week = $argv[3]; $levelId = $argv[4];
$level = lab57_level($levelId);
$world = lab57_build_world($level, lab57_seed("class_3a", $student, "weekly:" . $week, $level), [], $now);
$ctx = ["class" => "class_3a", "student" => $student, "label" => $student, "context" => "weekly:" . $week, "level" => $levelId, "now" => $now, "cli" => true];
foreach (($level["solution"])($world) as $cmd) { $r = lab57_session($ctx, "run", ["line" => $cmd]); if (!empty($r["solved"]) || empty($r["ok"])) break; }
');
    $WK3 = arena58_weekly_current_key($T + 100000);
    $week3 = arena58_weekly_week($WK3);
    $procs = [];
    $N = 12;
    for ($i = 0; $i < $N; $i++) {
        $cmd = 'C:/php/php.exe ' . escapeshellarg($workerFile) . ' ' . escapeshellarg('class_3a:student:conc' . $i) . ' ' . escapeshellarg((string)($week3['start'] + 5)) . ' ' . escapeshellarg($WK3) . ' ' . escapeshellarg($week3['level']);
        $p = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (is_resource($p)) $procs[] = [$p, $pipes];
    }
    foreach ($procs as [$p, $pipes]) { fclose($pipes[1]); fclose($pipes[2]); proc_close($p); }
    $concurrentSolves = arena58_weekly_solves($WK3, $week3['level'], $week3['start'] + 60);
    check(count($procs) === $N, 'all ' . $N . ' worker processes launched');
    check(count($concurrentSolves) === $N, $N . ' concurrent processes solving the same weekly puzzle → no lost writes', (string)count($concurrentSolves));

    // --- ARN-05: Záznam závodu ---------------------------------------------------
    at($T + 200000);
    $rReplay = arena57_start_race(arena57_create_race(['class_id' => 'class_3a', 'preset' => 'custom', 'levels' => ['start-1', 'start-2', 'start-3'], 'duration_min' => '10', 'mode' => 'solo', 'hint_penalty' => '0.2', 'names' => 'initials', 'class_goal' => '2', 'title' => 'Replay'], $classes, $T + 200000)['id'], $T + 200000);
    solve_start($keys10[0], 'Žák Číslo1', (string)$rReplay['id'], $T + 200010);
    solve_start($keys10[1], 'Žák Číslo2', (string)$rReplay['id'], $T + 200030);
    $rReplayStopped = arena57_stop_race((string)$rReplay['id'], $T + 200100);
    $timeline = arena58_replay_timeline($rReplayStopped);
    check(($timeline[0]['kind'] ?? '') === 'start' && end($timeline)['kind'] === 'end', 'replay timeline starts with start and ends with end frame');
    check(count(array_filter($timeline, static fn(array $f): bool => $f['kind'] === 'goal')) >= 1, 'replay timeline includes a class-goal milestone frame');
    $namesInTimeline = implode(' ', array_column($timeline, 'text'));
    check(str_contains($namesInTimeline, 'Žák'), 'replay timeline uses privacy-respecting public names (initials), not raw internal keys', $namesInTimeline);
    check(!str_contains($namesInTimeline, 'student:s') && !str_contains($namesInTimeline, 'conc'), 'replay timeline never leaks internal student keys');
    // PRIV58-06: hide_rank, osobní rekord a anonymní čísla nezávislá na pořadí
    $tlHide = arena58_replay_timeline(arena57_race((string)$rZeb['id']));
    $hiddenFrames = array_values(array_filter($tlHide, static fn(array $f): bool => str_starts_with($f['text'], 'Spolužák')));
    check(count($hiddenFrames) === 1 && !str_contains($hiddenFrames[0]['text'], ' b)') && $hiddenFrames[0]['kind'] === 'solve', 'PRIV58-06: hide_rank student is only „Spolužák“ in the replay, without points or first blood', (string)count($hiddenFrames));
    $tlSolo = arena58_replay_timeline(arena57_race((string)$rSolo['id']));
    $soloText = implode(' ', array_column($tlSolo, 'text'));
    check(!str_contains($soloText, 'Žák') && !str_contains($soloText, ' b)') && !str_contains($soloText, 'první krev') && !str_contains($soloText, 'pořadí je') && str_contains($soloText, 'Třída vyřešila'), 'PRIV58-06: osobni_rekord replay shows only the class summary (no names, points or ranking)', $soloText);
    at($T + 300000);
    $rAnon = arena57_start_race(arena57_create_race(['names' => 'anon', 'title' => 'Anon replay'] + $base, $classes, $T + 300000)['id'], $T + 300000);
    foreach ($keys10 as $i => $key) solve_start($key, 'Žák Číslo' . ($i + 1), (string)$rAnon['id'], $T + 300010 + $i);
    $rAnonDone = arena57_stop_race((string)$rAnon['id'], $T + 300500);
    $anonCalc = arena57_compute($rAnonDone);
    $anonNo = arena58_replay_anon_numbers((string)$rAnonDone['id'], $anonCalc['solves'], []);
    $sameAsRank = 0;
    foreach ($anonCalc['students'] as $row) if (($anonNo[$row['key']] ?? -1) === $row['rank']) $sameAsRank++;
    check(count($anonNo) === 10 && $sameAsRank < 10, 'PRIV58-06: anonymous „Hráč N“ numbers are not the final ranking', 'shodných=' . $sameAsRank);
    check(arena58_replay_anon_numbers((string)$rAnonDone['id'], array_reverse($anonCalc['solves']), []) === $anonNo, 'PRIV58-06: anonymous numbers do not depend on solve order either');
    $anonText = implode(' ', array_column(arena58_replay_timeline($rAnonDone), 'text'));
    check(str_contains($anonText, 'Hráč ') && !str_contains($anonText, 'Žák'), 'PRIV58-06: anon replay uses only „Hráč N“');
    ob_start();
    arena58_replay_render((string)$rReplay['id']);
    $replayHtml = (string)ob_get_clean();
    check(str_contains($replayHtml, 'data-arena58-replay') && str_contains($replayHtml, 'role="timer"') && str_contains($replayHtml, '<ol'), 'replay page renders accessible timer + always-visible step list (works without JS)');
    at($T + 200000);
    ob_start();
    arena58_replay_render((string)arena57_create_race(['class_id' => 'class_3a', 'preset' => 'custom', 'levels' => ['start-1'], 'duration_min' => '5', 'mode' => 'solo', 'hint_penalty' => '0.2', 'names' => 'initials', 'class_goal' => '0', 'title' => 'Draft'], $classes, $T + 200000)['id']);
    $draftReplay = (string)ob_get_clean();
    check(str_contains($draftReplay, 'ještě není hotový'), 'replay page for a not-yet-finished race explains it is not ready, does not fake data');

    $rawWeekly = (string)file_get_contents(arena58_weekly_path());
    check(str_starts_with($rawWeekly, '<?php http_response_code(403); exit; ?>'), 'weekly storage file is PHP-guarded');
} catch (Throwable $e) {
    check(false, 'unexpected exception', $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
} finally {
    rm_tree($tmp);
}

echo ($failed === 0 ? 'V58_ARENA_AUDIT_OK' : 'V58_ARENA_AUDIT_FAIL') . ' checks=' . $checks . ' failed=' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);

/** Vyřeší start-1/start-2 v race:<id> referenčním postupem (pomocník pro ARN-06 testy). */
function solve_start(string $student, string $label, string $raceId, int $now): void
{
    foreach (['start-1', 'start-2'] as $levelId) {
        $level = lab57_level($levelId);
        if ($level === null || isset(lab57_solved('class_3a', $student, 'race:' . $raceId)[$levelId])) continue;
        $world = lab57_build_world($level, lab57_seed('class_3a', $student, 'race:' . $raceId, $level), ['CODE' => lab57_code('class_3a', $student, 'race:' . $raceId, $levelId)], $now);
        $ctx = ['class' => 'class_3a', 'student' => $student, 'label' => $label, 'context' => 'race:' . $raceId, 'level' => $levelId, 'now' => $now, 'cli' => true];
        foreach (($level['solution'])($world) as $cmd) {
            $r = lab57_session($ctx, 'run', ['line' => $cmd]);
            if (!empty($r['solved']) || empty($r['ok'])) break;
        }
        return;
    }
}
