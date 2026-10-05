<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Aréna – audit CTF týdne (ARN-02) a Incidentů (ARN-03).
 *
 * Jen CLI. Pracuje na izolovaném dočasném úložišti (nikdy na ostré storage/). Ověřuje: řešitelnost všech
 * úloh/scénářů na více semínkách, že cizí kód/vlajka neprojde, dynamické bodování CTF (vzorec + determinismus),
 * zmrazení žebříčku, pevný 15minutový limit incidentu, že body dá až postmortem, soukromí jmen, CSRF
 * v arena_v58_events_api.php a bezpečnostní token-sken (žádné spouštění/síť) nad vlastními soubory.
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__);
$T0 = microtime(true);

$AUDIT_TMP = sys_get_temp_dir() . '/v58_ctfinc_audit_' . bin2hex(random_bytes(6));
mkdir($AUDIT_TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $AUDIT_TMP;
$GLOBALS['lab57_secret_override'] = 'audit-secret-ctfinc-v58';
date_default_timezone_set('Europe/Prague');

register_shutdown_function(static function () use ($AUDIT_TMP): void {
    v58ci_rrmdir($AUDIT_TMP);
});

function v58ci_rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = @scandir($dir);
    if ($items === false) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) v58ci_rrmdir($path); else @unlink($path);
    }
    @rmdir($dir);
}

$GLOBALS['v58ci_warnings'] = [];
set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return true;
    $GLOBALS['v58ci_warnings'][] = sprintf('%s (%s:%d)', $errstr, basename($errfile), $errline);
    return true;
});

// Izolace od rozpracovaných souborů dalších agentů vlny 1b (linux_v58_cmd_*.php / jiné linux_v58_levels_*.php):
// vypneme automatické načítání rozšíření a natáhneme ručně jen naše dva balíčky úloh. Bez téhle izolace by
// syntaktická chyba v cizím WIP souboru (běžné během paralelní práce) shodila i tenhle audit.
$GLOBALS['lab58_skip_ext'] = true;
require_once $ROOT . '/linux_v57_lab.php';
require_once $ROOT . '/linux_v58_levels_ctf.php';
require_once $ROOT . '/linux_v58_levels_incident.php';
require_once $ROOT . '/arena_v58_ctf.php';
require_once $ROOT . '/arena_v58_incident.php';

// ---------------------------------------------------------------------------
// Harness (stejný vzor jako tools/v57_linux_lab_audit.php)
// ---------------------------------------------------------------------------

$GLOBALS['v58ci_checks'] = 0;
$GLOBALS['v58ci_failed'] = 0;

function v58ci_ok(string $name): void
{
    $GLOBALS['v58ci_checks']++;
    echo "PASS {$name}\n";
}

function v58ci_fail(string $name, string $detail): void
{
    $GLOBALS['v58ci_checks']++;
    $GLOBALS['v58ci_failed']++;
    echo "FAIL {$name} – {$detail}\n";
}

function v58ci_check(string $name, bool $cond, string $detail = ''): void
{
    if ($cond) v58ci_ok($name); else v58ci_fail($name, $detail !== '' ? $detail : 'podmínka nesplněna');
}

/** @return array ctx pro přímé volání lab57_session() v CTF/Incident kontextu. */
function v58ci_ctx(string $context, string $class, string $student, string $level, int $now, array $classmates): array
{
    return ['class' => $class, 'student' => $student, 'label' => $student, 'context' => $context, 'level' => $level, 'now' => $now, 'classmates' => static fn(): array => $classmates];
}

// ===========================================================================
// 1) Balíčky existují, jsou skryté z procvičování, mají minimální počty úloh
// ===========================================================================

v58ci_check('registry:no-errors', lab58_registry_errors() === [], implode(' | ', lab58_registry_errors()));

$ctfLevels = lab57_pack_levels('ctf');
$incLevels = lab57_pack_levels('incident');
v58ci_check('ctf:levels-count-min-15', count($ctfLevels) >= 15, 'nalezeno ' . count($ctfLevels));
v58ci_check('incident:scenarios-count-min-6', count($incLevels) >= 6, 'nalezeno ' . count($incLevels));
v58ci_check('ctf:hidden-from-practice', !lab58_pack_allows_class('ctf', 'class_1a') && !lab58_pack_allows_class('ctf', 'class_3a'));
v58ci_check('incident:hidden-from-practice', !lab58_pack_allows_class('incident', 'class_1a') && !lab58_pack_allows_class('incident', 'class_3a'));

$categories = [];
foreach ($ctfLevels as $l) $categories[(string)($l['category'] ?? '')] = true;
v58ci_check('ctf:covers-5-categories', count(array_intersect(array_keys($categories), ['forenzni', 'kodovani', 'web', 'linux', 'site'])) === 5, 'kategorie nalezeny: ' . implode(',', array_keys($categories)));

// ===========================================================================
// 2) Řešitelnost všech úloh/scénářů na ≥ 5 semínkách (lab58_try_solution, bez úložiště)
// ===========================================================================

foreach (array_merge($ctfLevels, $incLevels) as $level) {
    $id = (string)$level['id'];
    $ok = true;
    $detail = '';
    for ($s = 1; $s <= 5; $s++) {
        $seed = 'audit-seed-' . $id . '-' . $s;
        try {
            $res = lab58_try_solution($level, $seed, 2_000_000_000);
        } catch (Throwable $e) {
            $ok = false; $detail = 'semínko ' . $s . ' vyhodilo ' . $e->getMessage(); break;
        }
        if (!$res['solved']) { $ok = false; $detail = 'semínko ' . $s . ' nevede k vyřešení (' . count($res['cmds']) . ' příkazů)'; break; }
    }
    v58ci_check('solvable:' . $id, $ok, $detail);
}

// ===========================================================================
// 3) Cizí vlajka neprojde (CTF) a je zalogovaná jako foreign_code
// ===========================================================================

$classF = 'class_audit_foreign';
$GLOBALS['arena57_roster_override'][$classF] = ['stud-a' => ['label' => 'Anna Testova'], 'stud-b' => ['label' => 'Boris Testovic']];
$now = 2_000_000_000;
$GLOBALS['arena58_ctf_now_override'] = $now;
$eventF = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit foreign', 'class_ids' => [$classF]], [$classF], $now)['id'], $now);
$levelIdF = (string)$ctfLevels[0]['id'];
$codeA = lab57_code($classF, 'stud-a', 'ctf:' . $eventF['id'], $levelIdF);
$codeB = lab57_code($classF, 'stud-b', 'ctf:' . $eventF['id'], $levelIdF);
v58ci_check('ctf:codes-differ-per-student', $codeA !== $codeB);
$respForeign = lab57_session(v58ci_ctx('ctf:' . $eventF['id'], $classF, 'stud-b', $levelIdF, $now, ['stud-a', 'stud-b']), 'run', ['line' => 'submit ' . $codeA]);
v58ci_check('ctf:foreign-code-rejected', ($respForeign['ok'] ?? false) === true && empty($respForeign['solved']), json_encode($respForeign));
$hasForeignEvent = false;
foreach (lab57_events('ctf:' . $eventF['id']) as $ev) if (($ev['kind'] ?? '') === 'foreign_code' && ($ev['student_key'] ?? '') === 'stud-b') $hasForeignEvent = true;
v58ci_check('ctf:foreign-code-logged', $hasForeignEvent);
$respOwn = lab57_session(v58ci_ctx('ctf:' . $eventF['id'], $classF, 'stud-b', $levelIdF, $now, ['stud-a', 'stud-b']), 'run', ['line' => 'submit ' . $codeB]);
v58ci_check('ctf:own-code-accepted', ($respOwn['ok'] ?? false) === true && !empty($respOwn['solved']));

// ===========================================================================
// 4) Dynamické bodování: vzorec podle pořadí ve třídě + first blood, determinismus
// ===========================================================================

function v58ci_expected_ctf_points(int $base, int $rank, float $penalty, int $hints, bool $first): int
{
    $tier = $rank <= 3 ? 1.0 : ($rank <= 8 ? 0.8 : ($rank <= 15 ? 0.6 : 0.4));
    $frac = max(0.4, $tier - $penalty * $hints);
    $pts = (int)round($base * $frac);
    if ($first) $pts = (int)round($pts * 1.25);
    return max((int)round($base * 0.4), $pts);
}

/** @return list<int> přesné body v pořadí zápisu událostí pro danou úlohu */
function v58ci_run_tier_drill(string $classId, string $eventId, string $levelId, int $studentCount, int $now): array
{
    $roster = [];
    for ($i = 1; $i <= $studentCount; $i++) $roster['t' . $i] = ['label' => 'Tier Student ' . $i];
    $GLOBALS['arena57_roster_override'][$classId] = $roster;
    $keys = array_keys($roster);
    foreach ($keys as $key) {
        $code = lab57_code($classId, $key, 'ctf:' . $eventId, $levelId);
        lab57_session(v58ci_ctx('ctf:' . $eventId, $classId, $key, $levelId, $now, $keys), 'run', ['line' => 'submit ' . $code]);
    }
    $points = [];
    foreach (lab57_events('ctf:' . $eventId) as $ev) if (($ev['kind'] ?? '') === 'solve' && (string)($ev['level'] ?? '') === $levelId) $points[] = (int)$ev['points'];
    return $points;
}

$classT = 'class_audit_tier';
$eventT = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit tier', 'class_ids' => [$classT]], [$classT], $now)['id'], $now);
$levelIdT = 'ctf-kod-2';
$levelT = lab57_level($levelIdT);
$baseT = (int)$levelT['points'];
$penaltyT = (float)$eventT['settings']['hint_penalty'];
$gotPoints = v58ci_run_tier_drill($classT, (string)$eventT['id'], $levelIdT, 20, $now);
$expectedPoints = [];
for ($rank = 1; $rank <= 20; $rank++) $expectedPoints[] = v58ci_expected_ctf_points($baseT, $rank, $penaltyT, 0, $rank === 1);
v58ci_check('ctf:tier-points-match-formula', $gotPoints === $expectedPoints, 'ocekavano ' . implode(',', $expectedPoints) . ' ziskano ' . implode(',', $gotPoints));
v58ci_check('ctf:first-blood-bonus-applied', ($gotPoints[0] ?? 0) > (int)round($baseT * 1.0 * 0.9), 'prvni reseni nema bonus 1.25x: ' . ($gotPoints[0] ?? 0));

// determinismus: stejná posloupnost řešení ve druhé (nové) třídě/akci dá stejná čísla
$classT2 = 'class_audit_tier_replay';
$eventT2 = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit tier replay', 'class_ids' => [$classT2]], [$classT2], $now)['id'], $now);
$gotPoints2 = v58ci_run_tier_drill($classT2, (string)$eventT2['id'], $levelIdT, 20, $now);
v58ci_check('ctf:tier-points-deterministic-replay', $gotPoints === $gotPoints2, 'prvni beh ' . implode(',', $gotPoints) . ' vs druhy beh ' . implode(',', $gotPoints2));

// ===========================================================================
// 5) Zmrazení žebříčku (žák vidí starší stav, učitel vidí aktuální)
// ===========================================================================

$classFr = 'class_audit_freeze';
$GLOBALS['arena57_roster_override'][$classFr] = ['sf1' => ['label' => 'Freeze Testerova']];
$GLOBALS['arena58_ctf_now_override'] = $now;
$eventFrDraft = arena58_ctf_create_event(['title' => 'Audit freeze', 'class_ids' => [$classFr]], [$classFr], $now);
$eventFr = null;
arena58_ctf_update(static function (array $d) use ($eventFrDraft, $now, &$eventFr): array {
    foreach ($d['events'] as $i => $row) {
        if ($row['id'] !== $eventFrDraft['id']) continue;
        $row['status'] = 'live';
        $row['starts_at'] = date(DATE_ATOM, $now - 100);
        $row['ends_at'] = date(DATE_ATOM, $now + 120);
        $row['settings']['freeze_before_end_min'] = 2; // zmrazení začíná přesně v $now
        $d['events'][$i] = $eventFr = $row;
    }
    return $d;
});
$levelIdFr = 'ctf-kod-3';
$codeFr = lab57_code($classFr, 'sf1', 'ctf:' . $eventFr['id'], $levelIdFr);
$GLOBALS['arena58_ctf_now_override'] = $now + 50; // řešení PO začátku zmrazení
lab57_session(v58ci_ctx('ctf:' . $eventFr['id'], $classFr, 'sf1', $levelIdFr, $now + 50, ['sf1']), 'run', ['line' => 'submit ' . $codeFr]);
$GLOBALS['arena58_ctf_now_override'] = $now + 60; // dotaz o 10 s později, pořád živé, ale zmrazené
$studentBoardFr = arena58_ctf_board((string)$eventFr['id'], $classFr, 'sf1');
v58ci_check('ctf:freeze-flag-set', !empty($studentBoardFr['event']['frozen']));
v58ci_check('ctf:freeze-hides-late-solve-from-student', (int)$studentBoardFr['me']['points'] === 0, json_encode($studentBoardFr['me']));
$teacherDataFr = arena58_ctf_teacher_data((string)$eventFr['id'], $classFr, $now + 60);
$teacherSeesPoints = false;
foreach ($teacherDataFr['board'] as $row) if ((int)$row['points'] > 0) $teacherSeesPoints = true;
v58ci_check('ctf:freeze-still-visible-to-teacher', $teacherSeesPoints, json_encode($teacherDataFr['board']));
unset($GLOBALS['arena58_ctf_now_override']);

// ===========================================================================
// 6) Incidenty: pevný 15minutový limit od VLASTNÍHO startu
// ===========================================================================

$classI = 'class_audit_inc_timer';
$GLOBALS['arena57_roster_override'][$classI] = ['si1' => ['label' => 'Time Testovic']];
$GLOBALS['arena58_inc_now_override'] = $now;
$sessionI = arena58_inc_start_session(arena58_inc_create_session(['title' => 'Audit timer', 'class_id' => $classI], [$classI], $now)['id'], $now);
$scenarioI = (string)$incLevels[0]['id'];
arena58_inc_start_attempt((string)$sessionI['id'], $classI, 'si1', $scenarioI, $now);
$levelI = lab57_level($scenarioI);
$ctxOk = lab58_ctx_prepare(['class' => $classI, 'student' => 'si1', 'label' => 'Time Testovic', 'context' => 'incident:' . $sessionI['id'], 'level' => $scenarioI, 'now' => $now + 60, 'classmates' => static fn(): array => ['si1']]);
v58ci_check('incident:access-ok-within-15min', arena58_inc_access($levelI, $ctxOk) === null, (string)arena58_inc_access($levelI, $ctxOk));
$ctxExpired = lab58_ctx_prepare(['class' => $classI, 'student' => 'si1', 'label' => 'Time Testovic', 'context' => 'incident:' . $sessionI['id'], 'level' => $scenarioI, 'now' => $now + 901, 'classmates' => static fn(): array => ['si1']]);
$errExpired = arena58_inc_access($levelI, $ctxExpired);
v58ci_check('incident:access-blocked-after-15min', is_string($errExpired) && str_contains($errExpired, 'vypršel'), (string)$errExpired);

// pauza zastaví čas (přístupnost)
$classI2 = 'class_audit_inc_pause';
$GLOBALS['arena57_roster_override'][$classI2] = ['sp' => ['label' => 'Pauza Testovic']];
$sessionI2 = arena58_inc_start_session(arena58_inc_create_session(['title' => 'Audit pause', 'class_id' => $classI2], [$classI2], $now)['id'], $now);
$scenarioI2 = (string)$incLevels[1]['id'];
arena58_inc_start_attempt((string)$sessionI2['id'], $classI2, 'sp', $scenarioI2, $now);
arena58_inc_toggle_pause((string)$sessionI2['id'], 'sp', $scenarioI2, true, $now + 30);
$attemptPaused = arena58_inc_attempt(arena58_inc_session((string)$sessionI2['id']), 'sp', $scenarioI2);
$remainingWhilePaused = arena58_inc_remaining($attemptPaused, $now + 500); // "teď" je jedno, dokud je pauza aktivní
v58ci_check('incident:pause-freezes-remaining', $remainingWhilePaused === (ARENA58_INC_SECONDS - 30), 'zbyva ' . $remainingWhilePaused . ' ocekavano ' . (ARENA58_INC_SECONDS - 30));
// FAIR58-09: během pauzy Lab příkazy nepřijímá (přístup odmítnut), po obnovení ano
$pausedRun = lab57_session(v58ci_ctx('incident:' . $sessionI2['id'], $classI2, 'sp', $scenarioI2, $now + 40, ['sp']), 'run', ['line' => 'ls']);
v58ci_check('incident:paused-lab-rejects-commands', empty($pausedRun['ok']) && str_contains((string)($pausedRun['error'] ?? ''), 'pozastaven'), json_encode($pausedRun, JSON_UNESCAPED_UNICODE) ?: '');
arena58_inc_toggle_pause((string)$sessionI2['id'], 'sp', $scenarioI2, false, $now + 90); // 60 s pauzy
$resumedRun = lab57_session(v58ci_ctx('incident:' . $sessionI2['id'], $classI2, 'sp', $scenarioI2, $now + 95, ['sp']), 'run', ['line' => 'ls']);
v58ci_check('incident:resumed-lab-accepts-commands', !empty($resumedRun['ok']), (string)($resumedRun['error'] ?? ''));
$attemptResumed = arena58_inc_attempt(arena58_inc_session((string)$sessionI2['id']), 'sp', $scenarioI2);
v58ci_check('incident:resume-adds-back-paused-time', (int)$attemptResumed['pause_accum'] === 60, 'pause_accum=' . (int)$attemptResumed['pause_accum']);

// ===========================================================================
// 7) Body jen s postmortemem – technická oprava sama o sobě 0 bodů
// ===========================================================================

$classP = 'class_audit_postmortem';
$GLOBALS['arena57_roster_override'][$classP] = ['sp1' => ['label' => 'Post Mortemova']];
$GLOBALS['arena58_inc_now_override'] = $now;
$sessionP = arena58_inc_start_session(arena58_inc_create_session(['title' => 'Audit PM', 'class_id' => $classP], [$classP], $now)['id'], $now);
$scenarioP = 'inc-cpu';
arena58_inc_start_attempt((string)$sessionP['id'], $classP, 'sp1', $scenarioP, $now);
$levelP = lab57_level($scenarioP);
$seedP = lab57_seed($classP, 'sp1', 'incident:' . $sessionP['id'], $levelP);
$codeP = lab57_code($classP, 'sp1', 'incident:' . $sessionP['id'], $scenarioP);
$worldP = lab57_build_world($levelP, $seedP, ['CODE' => $codeP], $now + 30);
$cmdsP = ($levelP['solution'])($worldP);
// Víceřádkové řešení může splnit kontroly už uprostřed (např. druhý kill), ne nutně na posledním příkazu –
// sleduj VŠECHNY odpovědi v pořadí, ne jen tu poslední.
$anySolved = false;
$solvePoints = null;
foreach ($cmdsP as $line) {
    $resp = lab57_session(v58ci_ctx('incident:' . $sessionP['id'], $classP, 'sp1', $scenarioP, $now + 30, ['sp1']), 'run', ['line' => $line]);
    if (!empty($resp['solved'])) { $anySolved = true; $solvePoints = $resp['points'] ?? null; }
}
v58ci_check('incident:technical-fix-succeeds', $anySolved);
v58ci_check('incident:solve-alone-gives-zero-points', (int)($solvePoints ?? -1) === 0, 'points=' . json_encode($solvePoints));
$attemptAfterFix = arena58_inc_attempt(arena58_inc_session((string)$sessionP['id']), 'sp1', $scenarioP);
v58ci_check('incident:on-complete-marks-solved', !empty($attemptAfterFix['solved_at']) && $attemptAfterFix['postmortem'] === null);

$goodPm = ['cause' => 'Bezpečnostní skript se zacyklil kvůli chybějící podmínce ukončení a vytěžoval procesor.', 'fix' => 'Našel jsem proces příkazem ps aux --sort=-%cpu a ukončil ho příkazem kill.', 'prevention' => 'Přidat do skriptu časový limit a monitoring vytížení procesoru, aby to příště zachytil dřív.'];
$pmResult = arena58_inc_submit_postmortem((string)$sessionP['id'], $classP, 'sp1', $scenarioP, $goodPm, $now + 60);
v58ci_check('incident:postmortem-awards-points', (int)($pmResult['postmortem']['points'] ?? 0) > 0, 'points=' . (int)($pmResult['postmortem']['points'] ?? 0));

try {
    arena58_inc_submit_postmortem((string)$sessionP['id'], $classP, 'sp1', $scenarioP, $goodPm, $now + 70);
    v58ci_fail('incident:postmortem-not-resubmittable', 'druhé odevzdání prošlo bez chyby');
} catch (RuntimeException) {
    v58ci_ok('incident:postmortem-not-resubmittable');
}

$scenarioShort = 'inc-web';
arena58_inc_start_attempt((string)$sessionP['id'], $classP, 'sp1', $scenarioShort, $now);
try {
    arena58_inc_submit_postmortem((string)$sessionP['id'], $classP, 'sp1', $scenarioShort, ['cause' => 'x', 'fix' => 'x', 'prevention' => 'x'], $now + 10);
    v58ci_fail('incident:postmortem-min-length-enforced', 'příliš krátký text prošel bez chyby');
} catch (RuntimeException) {
    v58ci_ok('incident:postmortem-min-length-enforced');
}

$scenarioNoSolve = 'inc-dns';
arena58_inc_start_attempt((string)$sessionP['id'], $classP, 'sp1', $scenarioNoSolve, $now);
$pmNoSolve = arena58_inc_submit_postmortem((string)$sessionP['id'], $classP, 'sp1', $scenarioNoSolve, $goodPm, $now + 1000);
v58ci_check('incident:no-solve-no-points-but-postmortem-saved', (int)($pmNoSolve['postmortem']['points'] ?? -1) === 0 && is_array($pmNoSolve['postmortem']));

// ===========================================================================
// 8) Soukromí jmen (anonymně skryje ostatní, iniciály skryjí příjmení)
// ===========================================================================

$classPr = 'class_audit_privacy';
$GLOBALS['arena57_roster_override'][$classPr] = ['pa' => ['label' => 'Petra Anonymova'], 'pb' => ['label' => 'Pavel Bezejmenny']];
$GLOBALS['arena58_ctf_now_override'] = $now;
$eventAnon = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit anon', 'class_ids' => [$classPr], 'names' => 'anon'], [$classPr], $now)['id'], $now);
$levelIdPr = 'ctf-kod-4';
foreach (['pa', 'pb'] as $key) {
    $code = lab57_code($classPr, $key, 'ctf:' . $eventAnon['id'], $levelIdPr);
    lab57_session(v58ci_ctx('ctf:' . $eventAnon['id'], $classPr, $key, $levelIdPr, $now, ['pa', 'pb']), 'run', ['line' => 'submit ' . $code]);
}
$GLOBALS['fair64_prefs_override'][$classPr]['ctf:' . $eventAnon['id']] = true; // v64: absolutní žebříček je výchozí vypnutý – učitel ho zapíná u akce
$boardAnon = arena58_ctf_board((string)$eventAnon['id'], $classPr, 'pa');
$otherRow = null;
foreach ($boardAnon['rows'] as $row) if (empty($row['me'])) $otherRow = $row;
v58ci_check('privacy:anon-hides-other-name', $otherRow !== null && str_starts_with((string)$otherRow['name'], 'Hráč'), json_encode($otherRow));

$classPr2 = 'class_audit_privacy_ini';
$GLOBALS['arena57_roster_override'][$classPr2] = ['pa' => ['label' => 'Petra Anonymova'], 'pb' => ['label' => 'Pavel Bezejmenny']];
$eventInit = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit initials', 'class_ids' => [$classPr2], 'names' => 'initials'], [$classPr2], $now)['id'], $now);
foreach (['pa', 'pb'] as $key) {
    $code = lab57_code($classPr2, $key, 'ctf:' . $eventInit['id'], $levelIdPr);
    lab57_session(v58ci_ctx('ctf:' . $eventInit['id'], $classPr2, $key, $levelIdPr, $now, ['pa', 'pb']), 'run', ['line' => 'submit ' . $code]);
}
$boardInit = arena58_ctf_board((string)$eventInit['id'], $classPr2, 'pa');
$leaks = false;
foreach ($boardInit['rows'] as $row) if (empty($row['me']) && str_contains((string)$row['name'], 'Bezejmenny')) $leaks = true;
v58ci_check('privacy:initials-hides-surname', !$leaks, json_encode($boardInit['rows']));
unset($GLOBALS['arena58_ctf_now_override'], $GLOBALS['arena58_inc_now_override']);

// ===========================================================================
// 9) Řetězení úloh (odemčení)
// ===========================================================================

$classCh = 'class_audit_chain';
$GLOBALS['arena57_roster_override'][$classCh] = ['ch1' => ['label' => 'Chain Testovic']];
$GLOBALS['arena58_ctf_now_override'] = $now;
$eventCh = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit chain', 'class_ids' => [$classCh]], [$classCh], $now)['id'], $now);
$chainLevel = lab57_level('ctf-chain-1');
$ctxChain = lab58_ctx_prepare(['class' => $classCh, 'student' => 'ch1', 'label' => 'x', 'context' => 'ctf:' . $eventCh['id'], 'level' => 'ctf-chain-1', 'now' => $now, 'classmates' => static fn(): array => ['ch1']]);
v58ci_check('ctf:chain-locked-before-prereqs', is_string(arena58_ctf_access($chainLevel, $ctxChain)));
foreach (['ctf-for-1', 'ctf-kod-1'] as $prereq) {
    $code = lab57_code($classCh, 'ch1', 'ctf:' . $eventCh['id'], $prereq);
    lab57_session(v58ci_ctx('ctf:' . $eventCh['id'], $classCh, 'ch1', $prereq, $now, ['ch1']), 'run', ['line' => 'submit ' . $code]);
}
$ctxChain2 = lab58_ctx_prepare(['class' => $classCh, 'student' => 'ch1', 'label' => 'x', 'context' => 'ctf:' . $eventCh['id'], 'level' => 'ctf-chain-1', 'now' => $now, 'classmates' => static fn(): array => ['ch1']]);
v58ci_check('ctf:chain-unlocked-after-prereqs', arena58_ctf_access($chainLevel, $ctxChain2) === null, (string)arena58_ctf_access($chainLevel, $ctxChain2));
unset($GLOBALS['arena58_ctf_now_override']);

// ===========================================================================
// 10) Týmy (základní kontrola – vyrovnaný draft + sdílený klíč stavu)
// ===========================================================================

$classTm = 'class_audit_teams';
$GLOBALS['arena57_roster_override'][$classTm] = ['m1' => ['label' => 'Muž Jedna'], 'm2' => ['label' => 'Muž Dva'], 'm3' => ['label' => 'Muž Tři'], 'm4' => ['label' => 'Muž Čtyři']];
$GLOBALS['arena58_ctf_now_override'] = $now;
$eventTm = arena58_ctf_start_event(arena58_ctf_create_event(['title' => 'Audit teams', 'class_ids' => [$classTm], 'mode' => 'teams', 'team_size' => 2], [$classTm], $now)['id'], $now);
$teamsTm = (array)($eventTm['teams'][$classTm] ?? []);
v58ci_check('ctf:teams-drafted', count($teamsTm) >= 2, 'pocet tymu=' . count($teamsTm));
$member1 = (string)($teamsTm[0]['members'][0] ?? '');
$sameTeamKey = arena58_ctf_state_key(['id' => (string)$eventTm['id'], 'student' => $member1, 'class' => $classTm]);
v58ci_check('ctf:teams-state-key-shared', str_starts_with($sameTeamKey, 'ctfteam:'), $sameTeamKey);
unset($GLOBALS['arena58_ctf_now_override']);

// ===========================================================================
// 11) CSRF a metoda POST v arena_v58_events_api.php (statická kontrola zdroje)
// ===========================================================================

$apiSrc = (string)file_get_contents($ROOT . '/arena_v58_events_api.php');
v58ci_check('security:events-api-checks-csrf', str_contains($apiSrc, 'hash_equals') && str_contains($apiSrc, "_SESSION['csrf']"));
v58ci_check('security:events-api-requires-post', str_contains($apiSrc, "REQUEST_METHOD") && str_contains($apiSrc, "!== 'POST'"));
v58ci_check('security:events-api-identity-from-session', str_contains($apiSrc, 'current_class_id') && str_contains($apiSrc, 'adaptive_student_key'));
// SEC58-08: stejné brány jako lab_v57_api.php (povinná změna hesla, Lab vypnutý pro třídu) – před zpracováním operace
$gateA = strpos($apiSrc, 'acc53_must_change_password()');
$gateB = strpos($apiSrc, '!arena57_lab_enabled($classId)');
$opPos = strpos($apiSrc, '$op = (string)($_POST');
v58ci_check('security:events-api-password-and-lab-gates', $gateA !== false && $gateB !== false && $opPos !== false && $gateA < $opPos && $gateB < $opPos);

if (defined('TEACHER_PERMISSION_DENY') && function_exists('teacher_action_permission')) {
    $permCtf = teacher_action_permission('arena58_ctf_create');
    $permInc = teacher_action_permission('arena58_inc_create');
    v58ci_check('security:deny-by-default-until-integrated', $permCtf === TEACHER_PERMISSION_DENY && $permInc === TEACHER_PERMISSION_DENY, 'prefixy arena58_ctf_/arena58_inc_ zatím nejsou v teacher_action_permission_prefixes() (viz INTEGRATION.md) – do té doby je to bezpečně zamítnuto, ne otevřené');
}

// ===========================================================================
// 12) Token-sken – žádné spouštění/síť ve vlastních souborech Arény
// ===========================================================================

function v58ci_section_safety(string $root): void
{
    $forbiddenFuncs = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'create_function', 'assert', 'fsockopen', 'pfsockopen', 'stream_socket_client', 'socket_create', 'socket_connect', 'curl_init', 'curl_exec', 'curl_multi_exec', 'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr', 'mail'];
    $fileFuncs = ['file_get_contents', 'fopen', 'file'];
    $files = array_merge(
        glob($root . '/arena_v58_*.php') ?: [],
        [$root . '/linux_v58_levels_ctf.php', $root . '/linux_v58_levels_incident.php'],
    );
    $violations = [];
    foreach ($files as $path) {
        if (!is_file($path)) continue;
        $src = (string)file_get_contents($path);
        $tokens = token_get_all($src);
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $tok = $tokens[$i];
            if (is_array($tok) && $tok[0] === T_STRING) {
                $name = strtolower($tok[1]);
                $j = $i + 1;
                while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                $isCall = $j < $n && $tokens[$j] === '(';
                $p = $i - 1;
                while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) $p--;
                $prevTok = $p >= 0 ? $tokens[$p] : null;
                $isMethodOrDecl = is_array($prevTok) && in_array($prevTok[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true);
                if ($isCall && !$isMethodOrDecl && in_array($name, $forbiddenFuncs, true)) $violations[] = basename($path) . ':' . $tok[2] . ' volání ' . $name . '()';
                if ($isCall && in_array($name, $fileFuncs, true)) {
                    $k = $j + 1;
                    while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k++;
                    if ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_CONSTANT_ENCAPSED_STRING) {
                        $inner = substr($tokens[$k][1], 1, -1);
                        if (preg_match('~^(https?|ftp)://~i', $inner) === 1) $violations[] = basename($path) . ':' . $tokens[$k][2] . ' ' . $name . '(' . $tokens[$k][1] . '…)';
                    }
                }
            }
            if ($tok === '`') $violations[] = basename($path) . ': zpětné apostrofy (backtick operator)';
        }
    }
    v58ci_check('safety:no-exec-network', $violations === [], implode('; ', $violations));
    v58ci_check('safety:scanned-files-min-6', count($files) >= 6, 'nalezeno ' . count($files) . ' souborů');
}

v58ci_section_safety($ROOT);

// ===========================================================================
// Souhrn
// ===========================================================================

if ($GLOBALS['v58ci_warnings'] !== []) {
    v58ci_check('runtime:no-unhandled-php-warnings', false, count($GLOBALS['v58ci_warnings']) . ' varování: ' . implode(' | ', array_slice($GLOBALS['v58ci_warnings'], 0, 8)));
} else {
    v58ci_ok('runtime:no-unhandled-php-warnings');
}

$totalMs = (microtime(true) - $T0) * 1000;
echo sprintf("\n=== Audit dokončen za %.1f ms ===\n", $totalMs);

$checks = $GLOBALS['v58ci_checks'];
$failed = $GLOBALS['v58ci_failed'];
if ($failed === 0) {
    echo "V58_CTF_INCIDENT_AUDIT_OK checks={$checks} failed=0\n";
    exit(0);
}
echo "V58_CTF_INCIDENT_AUDIT_FAILED checks={$checks} failed={$failed}\n";
exit(1);
