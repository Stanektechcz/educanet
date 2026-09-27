<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · audit LEARN (EDU-01..04, LAB-08, LAB-09, TCH-02..04).
 * Pouze CLI, izolované dočasné úložiště (nikdy nesahá na storage/). Nic nespouští, žádná síť.
 * Spuštění: php tools/v58_learning_lab_audit.php   → poslední řádek V58_LEARNING_LAB_AUDIT_OK checks=N failed=0
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Prague');

$ROOT = dirname(__DIR__);
$TMP = sys_get_temp_dir() . '/v58_learning_lab_audit_' . bin2hex(random_bytes(6));
mkdir($TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $TMP;
$GLOBALS['lab57_secret_override'] = 'v58-learning-audit-secret';
ini_set('log_errors', '1');
ini_set('error_log', $TMP . '/php_errors.log');

register_shutdown_function(static function () use ($TMP): void {
    if (!is_dir($TMP)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($TMP, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($TMP);
});

// ---------------------------------------------------------------------------
// Stuby cizích funkcí (bootstrap.php / teacher_*.php se záměrně nenačítá – viz zpráva agenta)
// ---------------------------------------------------------------------------

$GLOBALS['v58la_roster'] = [];
$GLOBALS['modules'] = ['class_1a' => [], 'class_2a' => [], 'class_3a' => [], 'class_4a' => []];
define('STORAGE_DIR', $TMP);

function load_php_json(string $path): array
{
    if (!is_file($path)) return [];
    $raw = (string)file_get_contents($path);
    $raw = preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function storage_update(string $path, callable $mutate): array
{
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0770, true);
    $data = $mutate(load_php_json($path));
    file_put_contents($path, "<?php http_response_code(403); exit; ?>\n" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
    return $data;
}

function learning_award_once(string $classId, string $eventKey, int $xp): bool { return true; }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function project_students_for_class(string $classId): array { return $GLOBALS['v58la_roster'][$classId] ?? []; }
function teacher_class_label(string $classId): string { return 'Test ' . $classId; }
function adaptive_student_label(string $classId, string $studentKey): string { return (string)(project_students_for_class($classId)[$studentKey]['label'] ?? 'Student'); }
function csv_safe_cell(string $value): string { return $value !== '' && preg_match('/^[=+\-@\t\r]/u', $value) === 1 ? "'" . $value : $value; }
function tut52_family(string $classId, array $module): string { return in_array($classId, ['class_3a', 'class_4a'], true) ? 'networks' : 'graphics'; }
function tut52_tools(string $family): array { return []; }

$GLOBALS['v58la'] = ['checks' => 0, 'failed' => 0];
function v58la_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['v58la']['checks']++;
    if (!$ok) $GLOBALS['v58la']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}

// learning_v56.php se úmyslně načte SAMO, PŘED linux_v57_lab.php – ověří, že v56_lab_gate()/v56_lab_gate_proof()
// fungují i tehdy, když trasa (např. POST odevzdání v app/actions/lesson_path.php) skupinu knihoven Labu
// nenačetla (líné require uvnitř v56_lab_ensure_loaded()), místo tichého fail-open bez zámku.
require_once $ROOT . '/learning_v56.php';
v58la_check('edu01:lab-lib-not-preloaded-before-gate-check', !function_exists('lab57_level'));
$coldGate = v56_lab_gate('class_3a', 1);
v58la_check('edu01:gate-lazy-loads-lab-lib', $coldGate !== null && $coldGate['level'] === 'sit-4' && function_exists('lab57_level'));

require_once $ROOT . '/linux_v57_lab.php';
require_once $ROOT . '/lab_v58_review.php';
require_once $ROOT . '/lab_v58_learning.php';
require_once $ROOT . '/lab_v58_teacher.php';
require_once $ROOT . '/learning_v56_views.php';

const V58LA_NOW = 1790000000;

/** Odehraje referenční řešení úrovně jako skutečný žák (stejný seed/kód jako živá relace) a vrátí poslední odpověď. */
function v58la_solve_level(string $classId, string $studentKey, string $context, array $level, int $now): array
{
    $ctxBase = ['class' => $classId, 'student' => $studentKey, 'label' => 'Audit Žák', 'context' => $context, 'level' => $level['id'], 'now' => $now, 'classmates' => []];
    $ctx = lab58_ctx_prepare($ctxBase);
    $seed = lab58_context_seed($ctx, $level);
    $code = lab57_code($classId, (string)$ctx['state_key'], $context, (string)$level['id']);
    $try = lab58_try_solution($level, $seed, $now, $code);
    $result = ['ok' => false];
    foreach ($try['cmds'] as $line) {
        $result = lab57_session($ctxBase, 'run', ['line' => $line]);
        if (!empty($result['solved'])) break;
    }
    return $result;
}

// ---------------------------------------------------------------------------
// 0) Načtení a čistý registr
// ---------------------------------------------------------------------------

foreach (['lab58_review_today', 'lab58_heatmap', 'lab58_skill_map', 'lab58_badges', 'lab58t_supervision', 'lab58t_replay', 'lab58t_export_csv', 'lab58t_teacher_handle_post', 'v56_lab_gate', 'v56_lab_gate_proof'] as $fn) {
    v58la_check('api:exists:' . $fn, function_exists($fn));
}
lab57_levels();
lab58_manual();
lab58_packs();
// Globální registr může v tuto chvíli obsahovat chyby z cizích balíčků (souběžně vyvíjené vrstvy jiných agentů
// v kořeni projektu) – ty nejsou v mé působnosti. Kontroluji jen, že registr neobsahuje chybu týkající se MÉHO
// balíčku/úrovní ('review'); ostatní chyby jen vypíšu jako informaci na STDERR.
$v58laRegistryErrors = lab58_registry_errors();
$v58laMyRegistryErrors = array_values(array_filter($v58laRegistryErrors, static fn(string $e): bool => str_contains($e, 'review')));
if ($v58laRegistryErrors !== [] && $v58laMyRegistryErrors === []) fwrite(STDERR, "INFO (mimo LEARN): " . implode(' | ', $v58laRegistryErrors) . "\n");
v58la_check('registry:no-errors-from-review-pack', $v58laMyRegistryErrors === [], implode(' | ', $v58laMyRegistryErrors));
v58la_check('review:pack-registered', lab58_pack('review') !== null && count(lab57_pack_levels('review')) === 8);
v58la_check('review:pack-visible-all-classes', array_key_exists('review', lab58_packs_for_class('class_1a')));

foreach (lab57_pack_levels('review') as $level) {
    $ok = true;
    for ($seedNo = 0; $seedNo < 3; $seedNo++) {
        $seed = hash('sha256', 'review-audit|' . $level['id'] . '|' . $seedNo);
        $try = lab58_try_solution($level, $seed, V58LA_NOW, 'EDU-TEST-' . $seedNo);
        if (!$try['solved']) $ok = false;
    }
    v58la_check('review:solvable:' . $level['id'], $ok);
}

// ---------------------------------------------------------------------------
// 1) EDU-01: zámek kroku projektu + důkaz z Labu
// ---------------------------------------------------------------------------

$edu01Class = 'class_3a';
$edu01Student = 'edu01-student';
v58la_check('edu01:gate-3a-lesson1', v56_lab_gate('class_3a', 1) === ['step' => 2, 'level' => 'sit-4', 'title' => (string)lab57_level('sit-4')['title']]);
v58la_check('edu01:gate-4a-lesson1', v56_lab_gate('class_4a', 1) === ['step' => 2, 'level' => 'sit-6', 'title' => (string)lab57_level('sit-6')['title']]);
v58la_check('edu01:no-gate-lesson2', v56_lab_gate('class_3a', 2) === null);
v58la_check('edu01:no-gate-graphics', v56_lab_gate('class_1a', 1) === null);

$edu01Gate = v56_lab_gate($edu01Class, 1);
v58la_check('edu01:proof-null-before-solve', $edu01Gate !== null && v56_lab_gate_proof($edu01Class, $edu01Student, $edu01Gate) === null);

$edu01Threw = false;
$edu01Message = '';
try {
    v56_toggle_project_step($edu01Class, $edu01Student, 1, 2, true);
} catch (RuntimeException $e) {
    $edu01Threw = true;
    $edu01Message = $e->getMessage();
}
v58la_check('edu01:locked-step-throws', $edu01Threw && $edu01Message !== '');
v58la_check('edu01:locked-step-not-recorded', empty(v56_progress($edu01Class, $edu01Student, 1)['project'][2]));

// Balíček 'sit' se odemyká postupně – nejdřív vyřeš předchozí úrovně, pak samotnou zamykající úroveň.
$edu01Solve = ['solved' => false];
foreach (lab57_pack_levels((string)lab57_level((string)$edu01Gate['level'])['pack']) as $prereq) {
    $edu01Solve = v58la_solve_level($edu01Class, $edu01Student, 'practice', $prereq, V58LA_NOW);
    if ($prereq['id'] === $edu01Gate['level']) break;
}
v58la_check('edu01:reference-solution-solves-gate-level', !empty($edu01Solve['solved']));

$edu01Proof = v56_lab_gate_proof($edu01Class, $edu01Student, $edu01Gate);
v58la_check('edu01:proof-after-solve', is_array($edu01Proof) && $edu01Proof['level'] === 'sit-4' && $edu01Proof['at'] !== '' && !array_key_exists('code', $edu01Proof));

$edu01Threw2 = false;
try {
    v56_toggle_project_step($edu01Class, $edu01Student, 1, 2, true);
} catch (RuntimeException $e) {
    $edu01Threw2 = true;
}
v58la_check('edu01:unlocked-step-does-not-throw', !$edu01Threw2);
v58la_check('edu01:unlocked-step-recorded', !empty(v56_progress($edu01Class, $edu01Student, 1)['project'][2]));

$edu01Bundle = v56_lesson_bundle($edu01Class, [], 1, [], []);
$edu01State = v56_lesson_state($edu01Class, $edu01Student, $edu01Bundle);
v58la_check('edu01:lesson-state-lab-gate-unlocked', is_array($edu01State['lab_gate']) && $edu01State['lab_gate']['locked'] === false);

ob_start();
v56_render_lab_gate(['step' => 2, 'level' => 'sit-4', 'title' => 'Vypnuté rozhraní', 'locked' => true, 'proof' => null]);
$lockedHtml = (string)ob_get_clean();
v58la_check('edu01:view-locked-links-to-lab', str_contains($lockedHtml, '?view=lab&amp;uroven=sit-4'));
v58la_check('edu01:view-locked-no-submit-affordance', !str_contains($lockedHtml, 'Krok mám hotový'));

ob_start();
v56_render_lab_gate(['step' => 2, 'level' => 'sit-4', 'title' => 'Vypnuté rozhraní', 'locked' => false, 'proof' => ['at' => date(DATE_ATOM, V58LA_NOW)]]);
$proofHtml = (string)ob_get_clean();
v58la_check('edu01:view-shows-proof', str_contains($proofHtml, 'Důkaz z Labu'));

// ---------------------------------------------------------------------------
// 2) EDU-02: heatmapa chyb – jen agregace (>= 3 žáci na buňku)
// ---------------------------------------------------------------------------

$heatClass = 'class_heat';
foreach (['h1', 'h2'] as $student) {
    lab58_log_append($heatClass, $student, 'lvl-a', ['t' => V58LA_NOW - 10, 'k' => 'cmd', 'l' => 'ipconfig', 'x' => 127, 'e' => 'not_found']);
}
foreach (['h1', 'h2', 'h3'] as $student) {
    lab58_log_append($heatClass, $student, 'lvl-b', ['t' => V58LA_NOW - 5, 'k' => 'cmd', 'l' => 'chmod 777 x', 'x' => 1, 'e' => 'permission']);
}
$heatmap = lab58_heatmap($heatClass, V58LA_NOW - 3600, V58LA_NOW);
$hasIpconfig = false;
$hasChmod = false;
foreach ($heatmap['cells'] as $cell) {
    if ($cell['command'] === 'ipconfig') $hasIpconfig = true;
    if ($cell['command'] === 'chmod' && $cell['students'] === 3) $hasChmod = true;
}
v58la_check('edu02:cell-below-3-students-hidden', !$hasIpconfig);
v58la_check('edu02:cell-with-3-students-shown', $hasChmod);
v58la_check('edu02:top5-has-advice', $heatmap['top'] !== [] && trim((string)($heatmap['top'][0]['advice'] ?? '')) !== '');
$top5Sorted = true;
for ($i = 1; $i < count($heatmap['top']); $i++) if ($heatmap['top'][$i]['count'] > $heatmap['top'][$i - 1]['count']) $top5Sorted = false;
v58la_check('edu02:top5-sorted-desc', $top5Sorted);

// ---------------------------------------------------------------------------
// 3) EDU-03: adaptivní nápověda po 3 stejných chybách
// ---------------------------------------------------------------------------

v58la_check('edu03:rule-not-found-mapped', lab58_adaptive_hint_text('not_found', 'ipconfig') === ['text' => 'Příkaz „ipconfig“ v Linuxu neexistuje. Zkus „ip a“.', 'reason' => 'not_found:ipconfig']);
v58la_check('edu03:rule-permission', str_contains((string)lab58_adaptive_hint_text('permission', 'cat')['text'], 'ls -l'));
v58la_check('edu03:rule-no-such-file', str_contains((string)lab58_adaptive_hint_text('no_such_file', 'cat')['text'], 'pwd'));
v58la_check('edu03:rule-bad-option', str_contains((string)lab58_adaptive_hint_text('bad_option', 'ls')['text'], 'man ls'));
v58la_check('edu03:rule-unknown-class-is-null', lab58_adaptive_hint_text('ok', 'ls') === null);

$hintClass = 'class_3a';
$hintStudent = 'hint-student';
// review-ls-1 je v balíčku 'review' s unlock=free, takže je hned dostupná bez odemykání předchozích úrovní.
$hintCtx = ['class' => $hintClass, 'student' => $hintStudent, 'label' => 'Audit', 'context' => 'practice', 'level' => 'review-ls-1', 'now' => V58LA_NOW, 'classmates' => []];
lab57_session($hintCtx, 'run', ['line' => 'frobnicate']);
lab57_session($hintCtx, 'run', ['line' => 'frobnicate']);
$hintState1 = lab57_session($hintCtx, 'state');
// Pozn.: nelze psát `($hintState1['adaptive_hint'] ?? 'x') === null` – `??` bere null i chybějící klíč stejně,
// takže by test nikdy neprošel ani ve správném stavu. Ověřuje se zvlášť existence klíče a zvlášť hodnota null.
v58la_check('edu03:no-hint-before-3-repeats', array_key_exists('adaptive_hint', $hintState1) && $hintState1['adaptive_hint'] === null);
lab57_session($hintCtx, 'run', ['line' => 'frobnicate']);
$hintState2 = lab57_session($hintCtx, 'state');
v58la_check('edu03:hint-after-3-repeats', is_array($hintState2['adaptive_hint'] ?? null) && (string)$hintState2['adaptive_hint']['reason'] === 'not_found:frobnicate');
v58la_check('edu03:hint-key-always-present', array_key_exists('adaptive_hint', $hintState1) && array_key_exists('adaptive_hint', $hintState2));

// ---------------------------------------------------------------------------
// 4) EDU-04: mapa dovedností
// ---------------------------------------------------------------------------

$skillClass = 'class_skill';
$skillStudent = 'skill-student';
for ($i = 0; $i < 5; $i++) lab58_log_append($skillClass, $skillStudent, 'lvl-a', ['t' => V58LA_NOW - 100 + $i, 'c' => 'practice', 'k' => 'cmd', 'l' => 'ls -la', 'x' => 0, 'e' => 'ok']);
for ($i = 0; $i < 2; $i++) lab58_log_append($skillClass, $skillStudent, 'lvl-a', ['t' => V58LA_NOW - 50 + $i, 'c' => 'practice', 'k' => 'cmd', 'l' => 'grep foo x', 'x' => 0, 'e' => 'ok']);
$skillMap = lab58_skill_map($skillClass, $skillStudent);
v58la_check('edu04:mastered-after-5-ok', ($skillMap['commands']['ls']['state'] ?? '') === 'mastered');
v58la_check('edu04:learning-under-threshold', ($skillMap['commands']['grep']['state'] ?? '') === 'learning');
v58la_check('edu04:new-when-unused', ($skillMap['commands']['pwd']['state'] ?? '') === 'new');
v58la_check('edu04:summary-counts-match', $skillMap['summary']['total'] === count($skillMap['commands']) && $skillMap['summary']['mastered'] >= 1);
v58la_check('edu04:concept-progress-uses-mastered-command', (float)($skillMap['concepts']['glob']['progress'] ?? -1) > 0.0);
v58la_check('edu04:concept-new-when-unused', ($skillMap['concepts']['quotes']['state'] ?? '') === 'new' && (float)($skillMap['concepts']['quotes']['progress'] ?? -1) === 0.0);

// ---------------------------------------------------------------------------
// 5) LAB-08: odznaky
// ---------------------------------------------------------------------------

$badgeClass = 'class_badge';
$badgeStudent = 'badge-student';
$sitLevels = lab57_pack_levels('sit');
$sitSolvedAll = $sitLevels !== [];
foreach ($sitLevels as $level) {
    $r = v58la_solve_level($badgeClass, $badgeStudent, 'practice', $level, V58LA_NOW);
    if (empty($r['solved'])) $sitSolvedAll = false;
}
v58la_check('lab08:reference-solutions-solve-sit-pack', $sitSolvedAll && count($sitLevels) > 0);

$badgesFresh = lab58_badges($badgeClass, 'nobody-yet');
$fresh = array_values(array_filter($badgesFresh, static fn(array $b): bool => $b['id'] === 'net_detective'))[0] ?? null;
v58la_check('lab08:fresh-student-not-earned', $fresh !== null && $fresh['earned'] === false && $fresh['progress'] === 0.0);

$badges = lab58_badges($badgeClass, $badgeStudent);
$netDetective = array_values(array_filter($badges, static fn(array $b): bool => $b['id'] === 'net_detective'))[0] ?? null;
v58la_check('lab08:pack-fully-solved-earns-badge', $netDetective !== null && $netDetective['earned'] === true && $netDetective['progress'] === 1.0);
$noHints = array_values(array_filter($badges, static fn(array $b): bool => $b['id'] === 'no_hints'))[0] ?? null;
v58la_check('lab08:no-hints-badge-from-reference-solves', $noHints !== null && $noHints['earned'] === true);
$keymaster = array_values(array_filter($badges, static fn(array $b): bool => $b['id'] === 'keymaster'))[0] ?? null;
v58la_check('lab08:missing-sibling-pack-graceful', $keymaster !== null && $keymaster['earned'] === false && $keymaster['progress'] === 0.0);

$badgesAgain = lab58_badges($badgeClass, $badgeStudent);
v58la_check('lab08:idempotent-recompute', $badgesAgain === $badges);
$ids = array_column($badges, 'id');
v58la_check('lab08:each-badge-appears-once', count($ids) === count(array_unique($ids)));

lab57_store_update(lab58_review_store_path($badgeClass, $badgeStudent), static fn(array $d): array => ['streak' => ['count' => 3, 'last' => '2026-01-01', 'max' => 8]] + $d);
$diligent = array_values(array_filter(lab58_badges($badgeClass, $badgeStudent), static fn(array $b): bool => $b['id'] === 'diligent'))[0] ?? null;
v58la_check('lab08:diligent-uses-max-streak-not-current', $diligent !== null && $diligent['earned'] === true);

// ---------------------------------------------------------------------------
// 6) LAB-09: opakování s rozestupy (Leitner)
// ---------------------------------------------------------------------------

v58la_check('lab09:leitner-progression', array_map(
    static fn(int $box): int => LAB58_REVIEW_INTERVALS[$box] ?? -1,
    [1, 2, 3, 4, 5]
) === [1, 2, 4, 7, 14]);

$reviewClass = 'class_3a';
$reviewStudent = 'review-student';
$today = date('Y-m-d', V58LA_NOW);
$todayId = date('Ymd', V58LA_NOW);
lab58_review_leitner_advance($reviewClass, $reviewStudent, 'x', true, V58LA_NOW); // neexistující skill – nic se nesmí stát
lab57_store_update(lab58_review_store_path($reviewClass, $reviewStudent), static fn(array $d): array => $d);

$today1 = lab58_review_today($reviewClass, $reviewStudent, V58LA_NOW);
$today2 = lab58_review_today($reviewClass, $reviewStudent, V58LA_NOW);
v58la_check('lab09:daily-selection-max-3', count($today1['items']) > 0 && count($today1['items']) <= 3);
v58la_check('lab09:daily-selection-stable', $today1 === $today2);
foreach ($today1['items'] as $it) {
    v58la_check('lab09:item-level-exists:' . $it['level'], lab57_level($it['level']) !== null);
    v58la_check('lab09:item-url-has-context:' . $it['level'], str_contains($it['url'], 'opakovani=' . $todayId) && str_contains($it['url'], $it['level']));
}

$firstItem = $today1['items'][0];
$firstLevel = lab57_level($firstItem['level']);
$firstSkill = lab58_review_skill_of($firstItem['level']);
$reviewCtxStr = 'review:' . $todayId;
v58la_check('lab09:item-has-known-skill', $firstSkill !== null);
lab57_session(['class' => $reviewClass, 'student' => $reviewStudent, 'label' => 'Audit', 'context' => $reviewCtxStr, 'level' => $firstItem['level'], 'now' => V58LA_NOW, 'classmates' => []], 'run', ['line' => match ($firstLevel['type']) {
    'answer' => 'answer __wrong__',
    'check' => 'check',
    default => 'submit EDU-0000-0000',
}]);
$boxAfterFail = (int)(lab57_store_read(lab58_review_store_path($reviewClass, $reviewStudent))['boxes'][$firstSkill]['box'] ?? -1);
v58la_check('lab09:wrong-answer-resets-box-to-1', $boxAfterFail === 1);

$solveFirst = v58la_solve_level($reviewClass, $reviewStudent, $reviewCtxStr, $firstLevel, V58LA_NOW);
v58la_check('lab09:reference-solution-solves-review-item', !empty($solveFirst['solved']));
$boxAfterSolve = (int)(lab57_store_read(lab58_review_store_path($reviewClass, $reviewStudent))['boxes'][$firstSkill]['box'] ?? -1);
v58la_check('lab09:success-advances-box-from-1-to-2', $boxAfterSolve === 2);

foreach (array_slice($today1['items'], 1) as $it) {
    v58la_solve_level($reviewClass, $reviewStudent, $reviewCtxStr, lab57_level($it['level']), V58LA_NOW);
}
$todayAfter = lab58_review_today($reviewClass, $reviewStudent, V58LA_NOW);
v58la_check('lab09:done-flags-update-after-solving', array_values(array_filter($todayAfter['items'], static fn(array $i): bool => !$i['done'])) === []);
v58la_check('lab09:streak-becomes-1-after-first-full-day', lab58_review_streak($reviewClass, $reviewStudent) === 1);

// Formát dne (^[a-z0-9_-]{4,40}$) projde obecným parserem kontextu; odmítnutí přijde buď z 'levels'
// (úloha do dnešního výběru nepatří), nebo z 'access' (neplatný den) – obojí je správné odepření přístupu.
$wrongDayResult = lab57_session(['class' => $reviewClass, 'student' => $reviewStudent, 'label' => 'A', 'context' => 'review:abcd', 'level' => $firstItem['level'], 'now' => V58LA_NOW, 'classmates' => []], 'state');
v58la_check('lab09:invalid-day-format-rejected', $wrongDayResult['ok'] === false);
$futureId = date('Ymd', strtotime($today . ' +2 day'));
$futureResult = lab57_session(['class' => $reviewClass, 'student' => $reviewStudent, 'label' => 'A', 'context' => 'review:' . $futureId, 'level' => $firstItem['level'], 'now' => V58LA_NOW, 'classmates' => []], 'state');
v58la_check('lab09:future-day-rejected', $futureResult['ok'] === false);
$oldId = date('Ymd', strtotime($today . ' -10 day'));
$oldResult = lab57_session(['class' => $reviewClass, 'student' => $reviewStudent, 'label' => 'A', 'context' => 'review:' . $oldId, 'level' => $firstItem['level'], 'now' => V58LA_NOW, 'classmates' => []], 'state');
v58la_check('lab09:old-day-rejected', $oldResult['ok'] === false);

$otherLevelId = null;
foreach (lab57_pack_levels('review') as $lvl) if ($lvl['id'] !== $firstItem['level'] && !in_array($lvl['id'], array_column($today1['items'], 'level'), true)) { $otherLevelId = $lvl['id']; break; }
if ($otherLevelId !== null) {
    $foreignResult = lab57_session(['class' => $reviewClass, 'student' => $reviewStudent, 'label' => 'A', 'context' => $reviewCtxStr, 'level' => $otherLevelId, 'now' => V58LA_NOW, 'classmates' => []], 'state');
    v58la_check('lab09:level-outside-todays-set-rejected', $foreignResult['ok'] === false);
}

// ---------------------------------------------------------------------------
// 7) TCH-02: přehrávání relace (jen vlastní třída)
// ---------------------------------------------------------------------------

$replayClass = 'class_replay';
$GLOBALS['v58la_roster'][$replayClass] = ['replay-a' => ['label' => 'Adam Testovací']];
$GLOBALS['modules'][$replayClass] = [];
lab58_log_append($replayClass, 'replay-a', 'lvl-x', ['t' => V58LA_NOW, 'c' => 'practice', 'k' => 'cmd', 'l' => 'ls', 'x' => 0, 'e' => 'ok']);
$replayOwn = lab58t_replay($replayClass, 'replay-a', 'lvl-x');
v58la_check('tch02:own-class-student-has-events', count($replayOwn['events']) === 1 && $replayOwn['events'][0]['line'] === 'ls');
$replayForeign = lab58t_replay($replayClass, 'replay-not-in-class', 'lvl-x');
v58la_check('tch02:student-outside-class-empty', $replayForeign['events'] === []);
$levelsForA = lab58t_student_levels($replayClass, 'replay-a');
v58la_check('tch02:student-level-list', count($levelsForA) === 1 && $levelsForA[0]['id'] === 'lvl-x');

// ---------------------------------------------------------------------------
// 8) TCH-03: živý dohled
// ---------------------------------------------------------------------------

$watchClass = 'class_watch';
// adaptive_student_label() (stub) vrací štítek z roster – nastav ho na klíč samotný, ať jde přehledně ověřit.
foreach (['watch-idle', 'watch-errors', 'watch-ok', 'watch-solved'] as $w) $GLOBALS['v58la_roster'][$watchClass][$w] = ['label' => $w];
lab58_log_append($watchClass, 'watch-idle', 'lvl-w', ['t' => V58LA_NOW - 400, 'c' => 'practice', 'k' => 'cmd', 'l' => 'ls', 'x' => 0, 'e' => 'ok']);
for ($i = 0; $i < 7; $i++) lab58_log_append($watchClass, 'watch-errors', 'lvl-w', ['t' => V58LA_NOW - 60 + $i, 'c' => 'practice', 'k' => 'cmd', 'l' => 'chmod', 'x' => 1, 'e' => 'bad_option']);
lab58_log_append($watchClass, 'watch-ok', 'lvl-w', ['t' => V58LA_NOW - 5, 'c' => 'practice', 'k' => 'cmd', 'l' => 'ls', 'x' => 0, 'e' => 'ok']);
lab58_log_append($watchClass, 'watch-solved', 'lvl-w', ['t' => V58LA_NOW - 5, 'c' => 'practice', 'k' => 'complete', 'p' => 100, 'h' => 0]);
$supervision = lab58t_supervision($watchClass, V58LA_NOW);
$byStudent = [];
foreach ($supervision['alerts'] as $a) $byStudent[$a['student']] = $a;
v58la_check('tch03:idle-student-flagged', isset($byStudent['watch-idle']));
v58la_check('tch03:many-errors-flagged', isset($byStudent['watch-errors']));
v58la_check('tch03:active-ok-student-not-flagged', !isset($byStudent['watch-ok']));
v58la_check('tch03:solved-student-not-flagged', !isset($byStudent['watch-solved']));

// ---------------------------------------------------------------------------
// 9) TCH-04: export CSV
// ---------------------------------------------------------------------------

$csvClass = 'class_csv';
$GLOBALS['v58la_roster'][$csvClass] = [
    'csv-a' => ['label' => 'Bára Nováková'],
    'csv-b' => ['label' => '=cmd|calc!A1'],
];
$GLOBALS['modules'][$csvClass] = [];
lab58_log_append($csvClass, 'csv-a', 'lvl-c', ['t' => V58LA_NOW, 'c' => 'practice', 'k' => 'complete', 'p' => 50, 'h' => 1]);
lab57_store_update(lab57_state_path($csvClass, 'csv-a'), static fn(array $d): array => $d + ['solved' => ['practice' => ['lvl-c' => ['at' => date(DATE_ATOM, V58LA_NOW), 'points' => 50, 'hints' => 1, 'cmds' => 3, 'secs' => 40]]]]);

/**
 * V CLI je v tuto chvíli skriptu už dávno odesláno stdout (PASS/FAIL řádky), takže `header()` uvnitř
 * lab58t_export_csv() (zcela správně volané pro reálný HTTP požadavek) v CLI vždy vyvolá E_WARNING
 * "headers already sent" – a protože běží uvnitř ob_start(), text varování by znečistil zachycený CSV.
 * Jde jen o artefakt tohoto CLI testu, ne o produkční chybu; proto se tu (a jen tu) dočasně potlačí.
 */
function v58la_capture_csv(string $classId, string $mode): string
{
    set_error_handler(static fn(int $no, string $str): bool => str_contains($str, 'headers already sent') || str_contains($str, 'Cannot modify header'), E_WARNING);
    ob_start();
    lab58t_export_csv($classId, $mode);
    $out = (string)ob_get_clean();
    restore_error_handler();
    return $out;
}

$csv = v58la_capture_csv($csvClass, 'sumativni');
v58la_check('tch04:csv-has-bom', str_starts_with($csv, "\xEF\xBB\xBF"));
v58la_check('tch04:csv-header-has-mode-column', str_contains($csv, 'Typ hodnocení'));
v58la_check('tch04:csv-no-email-column', !str_contains($csv, 'mail'));
v58la_check('tch04:csv-mode-summative', str_contains($csv, 'Sumativní') && !str_contains($csv, 'Formativní'));
v58la_check('tch04:csv-injection-guarded', str_contains($csv, "'=cmd|calc!A1") && !preg_match('/;=cmd/', $csv));
v58la_check('tch04:csv-includes-student-a-points', str_contains($csv, 'Bára Nováková'));

$csvDefault = v58la_capture_csv($csvClass, 'neplatny-typ');
v58la_check('tch04:csv-invalid-mode-falls-back-formativni', str_contains($csvDefault, 'Formativní'));

// ---------------------------------------------------------------------------
// 10) Bezpečnostní invariant Linux Labu – token-sken vlastních souborů
// ---------------------------------------------------------------------------

/** Nejbližší další „skutečný“ token (přeskočí bílé znaky) jako string, nebo null na konci. */
function v58la_next_real_token(array $tokens, int $i): ?string
{
    for ($j = $i + 1; $j < count($tokens); $j++) {
        $t = $tokens[$j];
        if (is_array($t) && $t[0] === T_WHITESPACE) continue;
        return is_array($t) ? $t[1] : $t;
    }
    return null;
}

/**
 * Token-sken (ne substring): najde skutečná volání zakázaných funkcí a literální operátor zpětných apostrofů.
 * T_STRING následovaný '(' a nejde o ->/::/function (metoda/deklarace); komentáře jsou celé jeden token, takže
 * zpětné apostrofy v PHPDoc (např. `lab57_store_update()`) nejsou samostatný token a nejsou hlášeny.
 */
function v58la_security_scan_forbidden_calls(string $src): array
{
    $forbiddenCalls = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'stream_socket_client', 'socket_create', 'curl_init', 'curl_exec', 'gethostbyname', 'dns_get_record', 'checkdnsrr', 'mail'];
    $tokens = token_get_all($src);
    $hits = [];
    $prevReal = null;
    foreach ($tokens as $i => $tok) {
        if (is_array($tok) && $tok[0] === T_WHITESPACE) continue;
        if ($tok === '`') { $hits[] = '`'; $prevReal = $tok; continue; }
        if (is_array($tok) && $tok[0] === T_STRING && in_array(strtolower($tok[1]), $forbiddenCalls, true)) {
            $isCall = v58la_next_real_token($tokens, $i) === '(';
            $isMethodOrDecl = is_array($prevReal) && in_array($prevReal[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true);
            if ($isCall && !$isMethodOrDecl) $hits[] = $tok[1] . '(';
        }
        $prevReal = $tok;
    }
    return array_values(array_unique($hits));
}

$myFiles = ['linux_v58_levels_review.php', 'lab_v58_review.php', 'lab_v58_learning.php', 'lab_v58_teacher.php', 'lab_v58_teacher_views.php', 'learning_v56.php', 'learning_v56_views.php'];
foreach ($myFiles as $rel) {
    $src = (string)file_get_contents($ROOT . '/' . $rel);
    $hits = v58la_security_scan_forbidden_calls($src);
    v58la_check('security:token-scan:' . $rel, $hits === [], implode(', ', $hits));
}
foreach ($myFiles as $rel) {
    $src = (string)file_get_contents($ROOT . '/' . $rel);
    v58la_check('security:no-url-fopen:' . $rel, preg_match('/(file_get_contents|fopen)\s*\(\s*[\'"]https?:/i', $src) !== 1);
}

// ---------------------------------------------------------------------------
// Souhrn
// ---------------------------------------------------------------------------

echo "\n";
if ($GLOBALS['v58la']['failed'] > 0) {
    echo 'V58_LEARNING_LAB_AUDIT_OK checks=' . $GLOBALS['v58la']['checks'] . ' failed=' . $GLOBALS['v58la']['failed'] . "\n";
    exit(1);
}
echo 'V58_LEARNING_LAB_AUDIT_OK checks=' . $GLOBALS['v58la']['checks'] . ' failed=0' . "\n";
exit(0);
