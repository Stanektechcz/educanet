<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v77 · audit obsahu a úklidu vrstvy v77:
 *   1) soubory vrstvy (strict_types, guard, ≤ 800 řádků, PHP 8.1), overlay lm71 načítá 8 souborů v77 (4 × L1–L4, 4 × dny),
 *   2) L1–L4 všech tříd (16 lekcí): povinná pole, úplnost 12/12, plán 0–90 min, české titulky, stav „navrh“,
 *   3) exit ticket (3 varianty, jiné než kvíz třídy a ostatní lekce, `correct` na různých pozicích 20–50 %), bez duplicit,
 *   4) glosář, dobrovolná domácí příprava, kompetence z katalogu, vazba na ŠVP, úrovně L1–L4, obsah bez URL / e-mailů / veřejných IP,
 *   5) technická fakta: příkazy 3.A/4.A v lekcích i dnech bez lekce se v simulátoru Linux Labu chovají podle zadání,
 *   6) 112/112 lekcí úplných; 44 karet dnů bez lekce s plným obsahem (průběh 90 min, úkoly, diferenciace, zástup) a v Dnešní hodině,
 *   7) AI prompty: L1–L4 ze (schváleného) obsahu, ostatní za běhu shodně s vyřazenými kity (otisky tools/lib/v77_kit_prompts_fixture.php),
 *   8) vyřazené soubory neexistují a žádný kód na ně neodkazuje; T-14 (Středa), grafické úlohy v Opakování, kontrast LVL, dokumentace.
 *   php tools/v77_content_audit.php     Dočasné úložiště i cache modelu. Konec: V77_CONTENT_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v77-content')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
foreach (['src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/teacher/cockpit_v70.php', 'src/lessons/lesson_model_v71.php', 'src/ops/calendar_days_v71.php', 'src/lessons/lesson_glossary_v72.php', 'src/assessment/competencies_v62.php', 'src/assessment/competencies_v75.php',
    'src/lessons/lesson_approval_v72.php', 'src/teacher/teacher_prefs_v72_1.php', 'src/lessons/lesson_exit_v72.php', 'src/teacher/ai_prompts_v72_1.php', 'src/teacher/ai_prompts_v76.php', 'src/teacher/ai_prompts_v77.php', 'src/assessment/svp_v72.php', 'src/assessment/svp_v72_map.php', 'src/levels/levels_v75.php',
    'src/assessment/taskbank_v75_gen.php', 'src/assessment/taskbank_v75.php', 'src/assessment/taskbank_v76.php', 'src/student/review_v75.php', 'src/teacher/bulk_approval_v74.php', 'src/teacher/t14_v77.php', 'src/teacher/t14_v77_views.php', 'src/lessons/lesson_today_v70.php', 'src/lessons/lesson_today_v70_views.php', 'src/lessons/lesson_today_v71_views.php',
    'src/linux/linux_v57_core.php', 'src/linux/linux_v57_world.php', 'src/linux/linux_v57_shell.php', 'src/linux/linux_v57_cmd_files.php', 'src/linux/linux_v57_cmd_shell.php', 'src/linux/linux_v57_cmd_text.php',
    'src/linux/linux_v57_cmd_sys.php', 'src/linux/linux_v57_cmd_net.php', 'src/linux/linux_v57_manual.php', 'src/linux/linux_v57_levels.php', 'src/linux/linux_v57_levels_ops.php', 'src/linux/linux_v57_lab.php'] as $lib) require_once $ROOT . '/' . $lib;

const V77A_LESSONS = [1, 4];
$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu i cache modelu jsou dočasné', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory
$classes = ['class_1a' => '1a', 'class_2a' => '2a', 'class_3a' => '3a', 'class_4a' => '4a'];
$contentFiles = [];
foreach ($classes as $short) $contentFiles[] = 'src/content/lesson_content_v77_' . $short . '.php';
foreach ($classes as $short) $contentFiles[] = 'src/content/lesson_content_v77_days_' . $short . '.php';
$libs = array_merge($contentFiles, ['src/teacher/ai_prompts_v77.php', 'src/teacher/t14_v77.php', 'src/teacher/t14_v77_views.php', 'tools/lib/v77_kit_prompts_fixture.php', 'tools/lib/v77_content_part2.php', 'tools/lib/v77_http.php']);
$bad = [];
foreach (array_merge($libs, ['tools/v77_content_audit.php', 'tools/v77_http_audit.php']) as $f) {
    $s = $read($f);
    $guard = str_starts_with($f, 'tools/v77_') ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if ($s === '' || !str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800
        || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory v77 (obsah L1–L4, dny, AI prompty, Středa, audity): strict_types, guard, ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$v77files = array_values(array_filter(lm71_overlay_files(), static fn(string $f): bool => str_starts_with($f, 'src/content/lesson_content_v77_')));
sort($contentFiles);
$check('overlay lm71 načítá 8 souborů v77 (L1–L4 a dny bez lekce po třídách) spolu s vlnou 1 a 2 (24 souborů)', $v77files === $contentFiles && count(lm71_overlay_files()) === 24);

// ---------------------------------------------------------------- 2) L1–L4: povinná pole a úplnost
$rawOv = [];
$lessons = [];
$fieldBad = [];
foreach ($classes as $c => $short) {
    $all = (array)(lm71_raw($c)['overlay']['lessons'] ?? []);
    $ovs = array_filter($all, static fn(int $n): bool => $n >= V77A_LESSONS[0] && $n <= V77A_LESSONS[1], ARRAY_FILTER_USE_KEY);
    if (array_keys($ovs) !== range(V77A_LESSONS[0], V77A_LESSONS[1])) $fieldBad[] = $c . ': overlay není přesně L1–L4';
    foreach ($ovs as $n => $ov) {
        $id = $c . ' L' . $n;
        if ((string)($ov['_file'] ?? '') !== 'src/content/lesson_content_v77_' . $short . '.php') $fieldBad[] = $id . ' soubor';
        $rawOv[$c][$n] = $ov;
        $lessons[$c][$n] = lm71_lesson($c, (int)$n);
        if (($ov['status'] ?? '') !== 'navrh' || (int)($ov['version'] ?? 0) !== 1) $fieldBad[] = $id . ' stav';
        $crit = count((array)($ov['goal']['success_criteria'] ?? []));
        if ($crit < 2 || $crit > 4 || !str_starts_with((string)($ov['goal']['student'] ?? ''), 'Na konci hodiny umím')) $fieldBad[] = $id . ' cíl/kritéria';
        foreach ((array)($ov['timeline'] ?? []) as $seg) if (trim((string)($seg['teacher'] ?? '')) === '' || trim((string)($seg['student'] ?? '')) === '' || trim((string)($seg['form'] ?? '')) === '') $fieldBad[] = $id . ' úsek plánu';
        if (count((array)($ov['tasks'] ?? [])) < 3) $fieldBad[] = $id . ' úkoly';
        foreach ((array)($ov['tasks'] ?? []) as $t) if (trim((string)($t['text'] ?? '')) === '' || trim((string)($t['output'] ?? '')) === '' || preg_match('/^\d+ min$/', (string)($t['time'] ?? '')) !== 1) $fieldBad[] = $id . ' úkol';
        $rubric = (array)($ov['assessment']['rubric'] ?? []);
        if (count($rubric) < 3 || count($rubric) > 5 || (array)($ov['assessment']['formative'] ?? []) === []) $fieldBad[] = $id . ' rubrika/formativní';
        foreach ($rubric as $r) if (trim((string)($r['criterion'] ?? '')) === '' || count((array)($r['levels'] ?? [])) !== 4) $fieldBad[] = $id . ' rubrika 1–4';
        if (count((array)($ov['teacher_notes'] ?? [])) < 2 || count((array)($ov['substitution'] ?? [])) < 2 || (array)($ov['safety'] ?? []) === []) $fieldBad[] = $id . ' poznámky/zástup/bezpečnost';
        if (count(array_filter((array)($ov['differentiation'] ?? []), static fn($v): bool => trim((string)$v) !== '')) !== 3) $fieldBad[] = $id . ' diferenciace';
    }
}
$all = array_merge(...array_values(array_map('array_values', $lessons)));
$check('16 lekcí (4 třídy × L1–L4): cíl „Na konci hodiny umím…“, kritéria 2–4, úseky plánu (učitel, žák, forma), ≥ 3 úkoly s výstupem a časem, rubrika 3–5 × 1–4, formativní kontrola, diferenciace, bezpečnost, poznámky, zástup, stav „navrh“'
    . ($fieldBad ? ' [' . implode(', ', array_slice($fieldBad, 0, 6)) . ']' : ''), count($all) === 16 && $fieldBad === []);
$incomplete = array_values(array_map(static fn(array $l): string => $l['id'], array_filter($all, static fn(array $l): bool => !$l['completeness']['complete'])));
$check('úplnost modelu lm71 12/12 u všech 16 lekcí L1–L4' . ($incomplete ? ' [' . implode(', ', $incomplete) . ']' : ''), $incomplete === []);
$check('plán po minutách L1–L4: souvislý 0–90 min, ≥ 3 úseky, nedopočtený z kroků', array_reduce($all, static fn(bool $ok, array $l): bool => $ok && lm71_timeline_ok($l['timeline'])
    && array_sum(array_column($l['timeline'], 'minutes')) === LM71_LESSON_MINUTES, true));
$titlesOk = true;
foreach ($classes as $c => $short) $titlesOk = $titlesOk && count(array_unique(array_map(static fn(int $n): string => lm71_lesson($c, $n)['title'], range(1, LM71_LESSONS)))) === LM71_LESSONS;
$check('titulky L1–L4 česky (i 4.A L3 – dřív anglická „Observability“), „Lekce N · …“, do 70 znaků; 28 titulků každé třídy jedinečných', $titlesOk && array_reduce($all, static fn(bool $ok, array $l): bool => $ok
    && !lm71_is_english_title($l['title']) && mb_strlen($l['title']) <= LM71_TITLE_MAX && str_starts_with($l['title'], 'Lekce ' . $l['number'] . ' · '), true));

// ---------------------------------------------------------------- 3) exit ticket a duplicity
$exitBad = [];
$positions = [];
$questions = [];
foreach ($classes as $c => $short) {
    $classQuiz = [];
    foreach (lm71_lessons($c) as $l) foreach ($l['assessment']['checks'] as $q) $classQuiz[lm71_norm_text((string)$q['question'])] = true;
    $others = [];
    foreach ((array)(lm71_raw($c)['overlay']['lessons'] ?? []) as $n => $ov) if ($n > V77A_LESSONS[1]) foreach ((array)($ov['exit_ticket']['variants'] ?? []) as $v) $others[lm71_norm_text((string)($v['question'] ?? ''))] = true;
    foreach ($rawOv[$c] as $n => $ov) {
        $variants = (array)($ov['exit_ticket']['variants'] ?? []);
        if (count($variants) < LM71_EXIT_VARIANTS_MIN) $exitBad[] = $c . ' L' . $n . ' < 3 varianty';
        $corr = [];
        foreach ($variants as $v) {
            $q = lm71_norm_text((string)($v['question'] ?? ''));
            $opts = (array)($v['options'] ?? []);
            $k = (int)($v['correct'] ?? -1);
            $corr[] = $k;
            $positions[$k] = ($positions[$k] ?? 0) + 1;
            $questions[] = $q;
            if (isset($classQuiz[$q]) || isset($others[$q])) $exitBad[] = $c . ' L' . $n . ' = kvíz / jiná lekce';
            if (count($opts) < 3 || count($opts) > 4 || !isset($opts[$k]) || count(array_unique(array_map('lm71_norm_text', $opts))) !== count($opts)) $exitBad[] = $c . ' L' . $n . ' možnosti';
            if (trim((string)($v['explanation'] ?? '')) === '') $exitBad[] = $c . ' L' . $n . ' vysvětlení';
        }
        if (count(array_unique($corr)) < 2) $exitBad[] = $c . ' L' . $n . ' correct na stejné pozici';
    }
}
ksort($positions);
$total = array_sum($positions);
$check('exit ticket L1–L4: ≥ 3 varianty, 3–4 různé možnosti, platné `correct`, vysvětlení, otázka ≠ kvíz třídy ani jiná lekce' . ($exitBad ? ' [' . implode(', ', array_slice(array_unique($exitBad), 0, 6)) . ']' : ''), $exitBad === []);
$check('`correct` na různých pozicích: v každé lekci aspoň 2, každá pozice 0–2 má 20–50 % (' . json_encode($positions) . ')',
    $total === 48 && array_keys($positions) === [0, 1, 2] && min($positions) >= 0.2 * $total && max($positions) <= 0.5 * $total);
$taskDup = [];
foreach ($classes as $c => $short) {
    $texts = [];
    foreach ((array)(lm71_raw($c)['overlay']['lessons'] ?? []) as $ov) foreach ((array)($ov['tasks'] ?? []) as $t) $texts[] = lm71_norm_text((string)($t['text'] ?? ''));
    $taskDup = array_merge($taskDup, array_keys(array_filter(array_count_values($texts), static fn(int $n): bool => $n > 1)));
}
$goals = array_map(static fn(array $l): string => lm71_norm_text((string)$l['goal']['student']), $all);
$check('bez duplicit: 48 otázek exit ticketu, 16 cílů, úkoly ve třídě (L1–L28) a šablonové úkoly = 0', count(array_unique($questions)) === 48 && count(array_unique($goals)) === 16 && $taskDup === []
    && array_reduce($all, static fn(bool $ok, array $l): bool => $ok && array_filter($l['tasks'], static fn(array $t): bool => $t['template']) === [], true));

require __DIR__ . '/lib/v77_content_part2.php';

exit(audit_summary($state, 'V77_CONTENT'));
