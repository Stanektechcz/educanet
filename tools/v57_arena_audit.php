<?php

declare(strict_types=1);

/**
 * EDUCANET v57 · Aréna – CLI audit třídních závodů.
 * Běží nad dočasným úložištěm (nikdy nesahá na storage/). Spuštění: php tools/v57_arena_audit.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$tmp = rtrim(sys_get_temp_dir(), '/\\') . '/educanet_arena57_audit_' . bin2hex(random_bytes(5));
if (!mkdir($tmp, 0770, true) && !is_dir($tmp)) { fwrite(STDERR, "Nelze vytvořit dočasné úložiště.\n"); exit(1); }
$GLOBALS['lab57_storage_override'] = $tmp;
$GLOBALS['lab57_secret_override'] = 'arena57-audit-secret';

require_once $root . '/linux_v57_lab.php';
require_once $root . '/arena_v57.php';

$GLOBALS['audit_xp'] = [];
if (!function_exists('learning_award_once')) {
    function learning_award_once(string $classId, string $eventKey, int $xp): bool
    {
        $key = $classId . '|' . $eventKey;
        if (isset($GLOBALS['audit_xp'][$key])) return false;
        $GLOBALS['audit_xp'][$key] = $xp;
        return true;
    }
}

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

function ctx(string $class, string $student, string $label, string $raceId, string $levelId, int $now, array $mates = []): array
{
    return ['class' => $class, 'student' => $student, 'label' => $label, 'context' => 'race:' . $raceId, 'level' => $levelId, 'now' => $now, 'cli' => true, 'classmates' => $mates];
}

/** Vyřeší úroveň referenčním postupem úrovně (+ volitelně nápovědy předem). */
function solve(string $class, string $student, string $label, string $raceId, string $levelId, int $now, int $hints = 0): array
{
    $level = lab57_level($levelId);
    $c = 'race:' . $raceId;
    $world = lab57_build_world($level, lab57_seed($class, $student, $c, $level), ['CODE' => lab57_code($class, $student, $c, $levelId)], $now);
    for ($i = 0; $i < $hints; $i++) lab57_session(ctx($class, $student, $label, $raceId, $levelId, $now), 'run', ['line' => 'hint']);
    $last = ['ok' => false, 'error' => 'no commands'];
    foreach (($level['solution'])($world) as $cmd) {
        $last = lab57_session(ctx($class, $student, $label, $raceId, $levelId, $now), 'run', ['line' => $cmd]);
        if (!empty($last['solved']) || empty($last['ok'])) break;
    }
    return $last;
}

function rm_tree(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

$classes = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
$T0 = strtotime('2026-09-25 10:00:00');
$A = 'class_3a:student:aaaa'; $B = 'class_3a:student:bbbb'; $C = 'class_3a:student:cccc'; $D = 'class_3a:student:dddd';
$F = 'class_3a:student:ffff'; $G = 'class_3a:student:gggg'; $Z = 'class_3a:student:zzzz';
$GLOBALS['arena57_roster_override'] = [
    'class_3a' => [$A => ['label' => 'Adam Kovář'], $B => ['label' => 'Bára Novotná'], $C => ['label' => 'Cyril Dvořák'], $F => ['label' => 'Filip Sedlák'], $G => ['label' => 'Gita Malá'], $Z => ['label' => 'Zora Tichá']],
    'class_2a' => ['class_2a:student:p1' => ['label' => 'Petr Jedna'], 'class_2a:student:p2' => ['label' => 'Pavla Dvě'], 'class_2a:student:p3' => ['label' => 'Pavel Tři'], 'class_2a:student:p4' => ['label' => 'Petra Čtyři']],
    'class_1a' => [],
];

try {
    // --- Nastavení třídy ------------------------------------------------------
    check(arena57_lab_enabled('class_3a') === true, 'lab enabled by default');
    arena57_set_lab_enabled('class_3a', false);
    check(arena57_lab_enabled('class_3a') === false, 'lab toggle off');
    arena57_set_lab_enabled('class_3a', true);
    check(arena57_lab_enabled('class_3a') === true, 'lab toggle on again');

    // --- Validace -------------------------------------------------------------
    $base = ['class_id' => 'class_3a', 'preset' => 'custom', 'levels' => ['start-1', 'start-2', 'start-3'], 'duration_min' => '10', 'mode' => 'solo', 'team_size' => '3', 'hints' => '1', 'hint_penalty' => '0.2', 'names' => 'initials', 'class_goal' => '5', 'title' => 'Audit závod'];
    check(throws(fn() => arena57_create_race(['class_id' => 'class_9z'] + $base, $classes, $T0), 'třída'), 'create rejects unknown class');
    check(throws(fn() => arena57_create_race(['levels' => ['start-1', 'neni-1']] + $base, $classes, $T0), 'neexistuje'), 'create rejects unknown level');
    check(throws(fn() => arena57_create_race(['duration_min' => '3'] + $base, $classes, $T0), 'Délka'), 'create rejects duration < 5');
    check(throws(fn() => arena57_create_race(['duration_min' => '91'] + $base, $classes, $T0), 'Délka'), 'create rejects duration > 90');
    check(throws(fn() => arena57_create_race(['hint_penalty' => '0.9'] + $base, $classes, $T0), 'nápovědu'), 'create rejects hint penalty 0.9');
    check(throws(fn() => arena57_create_race(['team_size' => '7'] + $base, $classes, $T0), 'týmu'), 'create rejects team size 7');
    check(throws(fn() => arena57_create_race(['names' => 'nick'] + $base, $classes, $T0), 'jmen'), 'create rejects names mode');
    check(throws(fn() => arena57_create_race(['levels' => []] + $base, $classes, $T0), 'aspoň jednu'), 'create rejects empty custom selection');
    $preset = arena57_parse_create(['preset' => 'rozcvicka', 'levels' => []] + $base, $classes);
    check($preset['levels'] === ['start-1', 'start-2', 'start-3', 'start-4'], 'preset Rozcvička = Start 1–4', json_encode($preset['levels']));
    $titled = arena57_parse_create(['title' => "  Páteční   závod  "] + $base, $classes);
    check($titled['title'] === 'Páteční závod', 'title trimmed, whitespace collapsed, Czech kept', $titled['title']);
    check(throws(fn() => arena57_parse_create(['title' => str_repeat('ž', 81)] + $base, $classes), '80'), 'title over 80 chars rejected');
    $allPresetLevelsExist = true;
    foreach (arena57_presets() as $pid => $p) { if ($pid !== 'custom' && count($p['levels']) < 3) $allPresetLevelsExist = false; }
    check($allPresetLevelsExist, 'every preset has ≥3 existing levels');

    // --- Vytvoření, přístup v konceptu ----------------------------------------
    at($T0 - 60);
    $r1 = arena57_create_race($base, $classes, $T0 - 60);
    $R1 = (string)$r1['id'];
    check(preg_match('/^[a-z0-9]{6,32}$/', $R1) === 1 && $r1['status'] === 'draft', 'create race → draft with API-compatible id');
    check(arena57_race_access($R1, 'class_3a', 'start-1', $T0 - 30) === 'Závod ještě nezačal.', 'draft race blocks access');
    $draftRun = lab57_session(ctx('class_3a', $A, 'Adam Kovář', $R1, 'start-1', $T0 - 30), 'run', ['line' => 'pwd']);
    check(empty($draftRun['ok']) && str_contains((string)($draftRun['error'] ?? ''), 'nezačal'), 'engine refuses draft race');

    // --- Start ------------------------------------------------------------------
    at($T0);
    $r1 = arena57_start_race($R1, $T0);
    check($r1['status'] === 'live' && arena57_ts($r1['ends_at']) === $T0 + 600, 'start → live, ends_at = start + 10 min');
    $r1b = arena57_create_race(['title' => 'Druhý'] + $base, $classes, $T0);
    check(throws(fn() => arena57_start_race((string)$r1b['id'], $T0), 'živý závod'), 'only one live race per class');
    check(throws(fn() => arena57_start_race($R1, $T0), 'připravený'), 'cannot start a live race again');
    $r2 = arena57_create_race(['class_id' => 'class_2a'] + $base, $classes, $T0);

    // --- Přístupová pravidla --------------------------------------------------
    at($T0 + 10);
    check(arena57_race_access($R1, 'class_3a', 'start-1', $T0 + 10) === null, 'own class + race level allowed');
    check(arena57_race_access($R1, 'class_2a', 'start-1', $T0 + 10) !== null, 'other class denied');
    check(arena57_race_access($R1, 'class_3a', 'quest-1', $T0 + 10) !== null, 'level outside race denied');
    check(arena57_race_access('neexistuje99', 'class_3a', 'start-1', $T0 + 10) !== null, 'unknown race denied');
    $foreignClassRun = lab57_session(ctx('class_2a', 'class_2a:student:p1', 'Petr Jedna', $R1, 'start-1', $T0 + 10), 'run', ['line' => 'pwd']);
    check(empty($foreignClassRun['ok']), 'engine refuses other class in race ctx');
    check((arena57_live_for_class('class_3a')['id'] ?? '') === $R1, 'live_for_class returns running race');
    check(arena57_live_for_class('class_4a') === null, 'live_for_class null for class without race');
    check(arena57_lock_active($R1) === false, 'lock inactive without lock_nav');

    // --- Řešení, první krev, penalizace za nápovědu --------------------------
    $s = solve('class_3a', $A, 'Adam Kovář', $R1, 'start-1', $T0 + 60);
    check(!empty($s['solved']) && ($s['points'] ?? 0) === 125 && !empty($s['first_blood']), 'first blood start-1 = 100 × 1.25 = 125', json_encode($s['points'] ?? null));
    $s = solve('class_3a', $B, 'Bára Novotná', $R1, 'start-1', $T0 + 90);
    check(!empty($s['solved']) && ($s['points'] ?? 0) === 100 && empty($s['first_blood']), 'second solve start-1 = 100, no bonus');
    $s = solve('class_3a', $B, 'Bára Novotná', $R1, 'start-2', $T0 + 120, 1);
    check(!empty($s['solved']) && ($s['points'] ?? 0) === 100, 'hint penalty 20 % then first blood: 80 × 1.25 = 100', json_encode($s['points'] ?? null));
    $s = solve('class_3a', $A, 'Adam Kovář', $R1, 'start-3', $T0 + 200);
    check(($s['points'] ?? 0) === 125, 'first blood start-3 = 125');
    $s = solve('class_3a', $C, 'Cyril Dvořák', $R1, 'start-2', $T0 + 250);
    check(($s['points'] ?? 0) === 100 && empty($s['first_blood']), 'C solves start-2 = 100');

    // Cizí kód (G zadá Adamův kód)
    $codeA = lab57_code('class_3a', $A, 'race:' . $R1, 'start-2');
    $fr = lab57_session(ctx('class_3a', $G, 'Gita Malá', $R1, 'start-2', $T0 + 260, [$A, $G]), 'run', ['line' => 'submit ' . $codeA]);
    check(!empty($fr['ok']) && empty($fr['solved']), 'foreign code is not a solve');

    // Zaseknutý žák: píše příkazy, ale 9 minut nic nevyřešil
    foreach (['ls', 'cat nic.txt', 'pwd', 'ls -l', 'cat .x'] as $i => $cmd) lab57_session(ctx('class_3a', $F, 'Filip Sedlák', $R1, 'start-2', $T0 + 540 + $i * 5), 'run', ['line' => $cmd]);

    // --- Pořadí a žebříček ----------------------------------------------------
    at($T0 + 300);
    $board = arena57_board($R1, $B, 'class_3a');
    $names = array_column($board['rows'], 'name');
    check(($board['rows'][0]['name'] ?? '') === 'Adam K.' && ($board['rows'][0]['points'] ?? 0) === 250, 'rank 1 = Adam K. with 250', json_encode($board['rows']));
    check(($board['me']['rank'] ?? null) === 2 && ($board['me']['points'] ?? 0) === 200, 'viewer B rank 2 with 200');
    check(!in_array('Adam Kovář', $names, true) && in_array('Bára N.', $names, true), 'initials mode hides full names');
    check(!in_array('Filip S.', $names, true) && !in_array('Gita M.', $names, true), 'students without points are not listed');
    check($board['goal'] === ['target' => 5, 'done' => 5, 'pct' => 100], 'class goal progress 5/5', json_encode($board['goal']));
    check(count($board['feed']) === 3 && ($board['feed'][0]['name'] ?? '') === 'Adam K.', 'first-blood feed (3, newest first)');
    check(arena57_board($R1, $B, 'class_2a')['status'] === 'missing', 'board refuses other class');
    check(($board['race']['status'] ?? '') === 'live' && $board['remaining'] === 300, 'board status live, remaining 300 s');

    $calc = arena57_compute(arena57_race($R1));
    $anonRace = ['settings' => ['names' => 'anon'] + $r1['settings']] + $r1;
    $anonRows = arena57_public_rows($anonRace, $calc['students'], arena57_roster('class_3a'), $B, 10);
    check(($anonRows[0]['name'] ?? '') === 'Hráč 1' && ($anonRows[1]['name'] ?? '') === 'Bára N.' && !empty($anonRows[1]['me']), 'anon: others „Hráč N“, viewer sees own name', json_encode($anonRows));
    $projRows = arena57_public_rows($anonRace, $calc['students'], arena57_roster('class_3a'), null, 10);
    check(!array_filter($projRows, static fn(array $r): bool => !str_starts_with($r['name'], 'Hráč ')), 'anon projector shows no names');
    $fullRace = ['settings' => ['names' => 'full'] + $r1['settings']] + $r1;
    check((arena57_public_rows($fullRace, $calc['students'], arena57_roster('class_3a'), $B, 10)[0]['name'] ?? '') === 'Adam Kovář', 'full-name mode uses roster label');
    $anonFeed = arena57_public_feed($anonRace, $calc, arena57_roster('class_3a'), $B);
    check(!array_filter($anonFeed, static fn(array $f): bool => str_contains($f['name'], 'Adam')), 'anon feed hides names');

    $tie = arena57_rank([
        ['key' => 'x', 'label' => 'X', 'points' => 200, 'solved' => 2, 'last' => 500],
        ['key' => 'y', 'label' => 'Y', 'points' => 200, 'solved' => 2, 'last' => 400],
        ['key' => 'z', 'label' => 'Z', 'points' => 0, 'solved' => 0, 'last' => 0],
    ]);
    check($tie[0]['key'] === 'y' && $tie[0]['rank'] === 1 && $tie[1]['rank'] === 2 && $tie[2]['rank'] === null, 'tie → earlier last solve wins; zero points unranked');

    // --- Učitel: data a poll --------------------------------------------------
    at($T0 + 570);
    $td = arena57_teacher_data($R1, $T0 + 570);
    $stuckNames = array_column($td['stuck'] ?? [], 'name');
    check(in_array('Filip Sedlák', $stuckNames, true) && count($stuckNames) === 1, 'stuck: typing ≥8 min without a solve', json_encode($td['stuck'] ?? null));
    check(count($td['alerts']) === 1 && $td['alerts'][0]['name'] === 'Gita Malá', 'foreign-code alert with full name');
    $lvl2 = array_values(array_filter($td['levels'], static fn(array $l): bool => $l['id'] === 'start-2'))[0] ?? [];
    check(($lvl2['solves'] ?? 0) === 2 && ($lvl2['hints'] ?? 0) === 1 && ($lvl2['first'] ?? '') === 'Bára Novotná', 'level stats: solves, hints, first', json_encode($lvl2));
    check(($td['board'][0]['name'] ?? '') === 'Adam Kovář' && count($td['board']) === 6, 'teacher board: full names, whole roster');
    $_GET['race'] = $R1;
    ob_start();
    arena57_teacher_poll();
    $json = json_decode((string)ob_get_clean(), true);
    $shapeOk = is_array($json) && ($json['ok'] ?? false) === true;
    foreach (['race', 'remaining', 'board', 'teams', 'levels', 'stuck', 'alerts', 'feed', 'goal', 'public'] as $k) $shapeOk = $shapeOk && array_key_exists($k, $json);
    check($shapeOk && isset($json['public']['rows'], $json['public']['feed'], $json['race']['ends_at']) && $json['remaining'] === 30, 'teacher poll JSON shape');
    check(($json['public']['rows'][0]['name'] ?? '') === 'Adam K.', 'poll public block respects initials');
    $_GET['race'] = '../etc';
    ob_start();
    arena57_teacher_poll();
    $bad = json_decode((string)ob_get_clean(), true);
    check(($bad['ok'] ?? true) === false, 'poll rejects invalid race id');

    // --- Po konci závodu --------------------------------------------------------
    at($T0 + 700);
    check(arena57_race_access($R1, 'class_3a', 'start-1', $T0 + 700) === null, 'finished race stays viewable');
    $late = solve('class_3a', $D, 'David Horák', $R1, 'start-1', $T0 + 700);
    check(!empty($late['solved']), 'late solve still recorded by engine');
    $after = arena57_board($R1, $D, 'class_3a');
    check($after['status'] === 'finished' && ($after['me']['points'] ?? -1) === 0 && count($after['rows']) === 3, 'events after ends_at ignored', json_encode($after['rows']));
    check(arena57_live_for_class('class_3a') === null && (arena57_race($R1)['status'] ?? '') === 'finished', 'expired race auto-marked finished');

    arena57_award_finished_xp('class_3a', $A);
    arena57_award_finished_xp('class_3a', $A);
    arena57_award_finished_xp('class_3a', $F);
    arena57_award_finished_xp('class_3a', $Z);
    $xp = $GLOBALS['audit_xp'];
    check(($xp['class_3a|v57:race:' . $R1] ?? null) === 50 && count($xp) === 1, 'XP: winner gets 20 + 30 once (idempotent)', json_encode($xp));
    $GLOBALS['audit_xp'] = [];
    arena57_award_finished_xp('class_3a', $F);
    check(($GLOBALS['audit_xp']['class_3a|v57:race:' . $R1] ?? null) === 20, 'XP: participation 20 without solves');
    $GLOBALS['audit_xp'] = [];
    arena57_award_finished_xp('class_3a', $Z);
    check($GLOBALS['audit_xp'] === [], 'XP: non-participant gets nothing');

    // --- Týmy -------------------------------------------------------------------
    $snake = arena57_snake_teams(['a' => 100, 'b' => 90, 'c' => 80, 'd' => 70, 'e' => 60, 'f' => 50], 3);
    $withA = array_values(array_filter($snake, static fn(array $t): bool => in_array('a', $t['members'], true)))[0]['members'] ?? [];
    sort($withA);
    check(count($snake) === 2 && $withA === ['a', 'd', 'e'], 'snake draft balances teams (a+d+e vs b+c+f)', json_encode($snake));
    $names = array_column($snake, 'name');
    check(count(array_unique($names)) === 2 && !array_diff($names, ARENA57_TEAM_NAMES), 'fun Czech team names');

    $T1 = $T0 + 2000;
    at($T1);
    $r4 = arena57_create_race(['class_id' => 'class_2a', 'mode' => 'teams', 'team_size' => '2', 'title' => 'Týmy'] + $base, $classes, $T1);
    $r4 = arena57_start_race((string)$r4['id'], $T1);
    $R4 = (string)$r4['id'];
    $sizes = array_map(static fn(array $t): int => count($t['members']), $r4['teams']);
    check(count($r4['teams']) === 2 && $sizes === [2, 2], 'teams auto-built at start (2 × 2)', json_encode($r4['teams']));
    $team = $r4['teams'][0];
    [$m1, $m2] = $team['members'];
    solve('class_2a', $m1, 'Člen Jedna', $R4, 'start-1', $T1 + 30, 2);
    solve('class_2a', $m2, 'Člen Dva', $R4, 'start-1', $T1 + 40);
    at($T1 + 60);
    $tb = arena57_board($R4, $m1, 'class_2a');
    $mine = array_values(array_filter($tb['teams'], static fn(array $t): bool => !empty($t['me'])))[0] ?? [];
    check(($mine['points'] ?? 0) === 100 && ($mine['solved'] ?? 0) === 1, 'team: level counted once with max points (75 vs 100 → 100)', json_encode($tb['teams']));
    check(($tb['my_team']['rank'] ?? null) === 1, 'team rank for viewer');
    check(throws(fn() => arena57_delete_race($R4, $T1 + 60), 'Běžící'), 'cannot delete live race');

    // --- Zámek navigace, vypnuté nápovědy, prodloužení, stop, smazání --------
    $T2 = $T0 + 5000;
    at($T2);
    $r5 = arena57_create_race(['class_id' => 'class_1a', 'lock_nav' => '1', 'hints' => '0', 'title' => 'Zámek'] + $base, $classes, $T2);
    $R5 = (string)$r5['id'];
    check(arena57_lock_active($R5) === false, 'lock inactive while draft');
    arena57_start_race($R5, $T2);
    check(arena57_lock_active($R5) === true, 'lock active when live + lock_nav');
    $h = lab57_session(ctx('class_1a', 'class_1a:student:h', 'Hana Hint', $R5, 'start-1', $T2 + 5), 'run', ['line' => 'hint']);
    $hintText = implode('', array_map(static fn(array $c): string => (string)$c[1], (array)($h['out'] ?? [])));
    check(str_contains($hintText, 'vypnuté') && ($h['hints_used'] ?? 1) === 0, 'hints disabled in race');
    $ext = arena57_extend_race($R5, $T2 + 10);
    check(arena57_ts($ext['ends_at']) === $T2 + 900 && (int)$ext['duration_min'] === 15, 'extend +5 min');
    $stopped = arena57_stop_race($R5, $T2 + 20);
    check($stopped['status'] === 'finished' && arena57_ts($stopped['ends_at']) === $T2 + 20, 'stop → finished, ends_at = now');
    check(arena57_lock_active($R5) === false, 'lock released after stop');
    check(throws(fn() => arena57_extend_race($R5, $T2 + 30), 'běžící'), 'cannot extend finished race');
    arena57_delete_race($R5, $T2 + 30);
    check(arena57_race($R5) === null, 'delete finished race');
    arena57_delete_race((string)$r2['id'], $T2 + 30);
    check(arena57_race((string)$r2['id']) === null, 'delete draft race');

    $raw = (string)file_get_contents(arena57_path());
    check(str_starts_with($raw, '<?php http_response_code(403); exit; ?>'), 'storage file is PHP-guarded');
} catch (Throwable $e) {
    check(false, 'unexpected exception', $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
} finally {
    rm_tree($tmp);
}

echo ($failed === 0 ? 'AUDIT_OK' : 'AUDIT_FAIL') . ' checks=' . $checks . ' failed=' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
