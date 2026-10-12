<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v76 · audit obsahu vlny 2 (L17–L28 pro 1.A, 2.A, 3.A, 4.A = 48 lekcí), úrovní L17–L28 a AI promptů vlny 2.
 *   1) soubory obsahu a úrovní (strict_types, guard, ≤ 800 řádků, PHP 8.1), overlay lm71 načítá 8 souborů vlny 2,
 *   2) povinná pole modelu, úplnost 12/12, plán souvislý 0–90 min, rubrika 3–5 × 1–4, stav „navrh“, české titulky,
 *   3) exit ticket: 3 varianty, jiná otázka než kvíz třídy, `correct` na různých pozicích, rozložení 20–50 %, bez duplicit,
 *   4) glosář (id existuje a termín je v textu), dobrovolná domácí příprava v limitu, kompetence z katalogu (1.A/3.A v62, 2.A/4.A návrh v75),
 *      vazba na ŠVP (návrh z svp_v72_map.php), žádné externí URL / materials/ / e-maily mimo example.com,
 *   5) technická fakta: každý `sim` příkaz 3.A/4.A se v simulátoru Linux Labu chová podle zadání (nic skutečného),
 *   6) úrovně Základ / Standard / Výzva L1–L28 všech tříd, schválení a viditelnost pro žáka (dočasné úložiště),
 *   7) AI prompty vlny 2: žák jen ze schváleného obsahu (jinak prompty z kitu), učitel z návrhu, bez odpovědí a odkazů,
 *   8) úplnost všech 112 lekcí: vlna 1 + vlna 2 = 96 úplných, šablonové L19–L28 přepsané (report v71 skóre ≥ minule).
 *   php tools/v76_content_audit.php     Dočasné úložiště i cache modelu. Konec: V76_CONTENT_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v76-content')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
foreach (['tutorial_v52.php', 'learning_v56.php', 'lesson_model_v71.php', 'lesson_glossary_v72.php', 'competencies_v62.php', 'competencies_v75.php', 'lesson_approval_v72.php',
    'teacher_prefs_v72_1.php', 'lesson_exit_v72.php', 'ai_prompts_v72_1.php', 'ai_prompts_v76.php', 'svp_v72.php', 'taskbank_v75_gen.php', 'taskbank_v75.php', 'levels_v75.php',
    'linux_v57_core.php', 'linux_v57_world.php', 'linux_v57_shell.php', 'linux_v57_cmd_files.php', 'linux_v57_cmd_shell.php', 'linux_v57_cmd_text.php',
    'linux_v57_cmd_sys.php', 'linux_v57_cmd_net.php', 'linux_v57_manual.php', 'linux_v57_levels.php', 'linux_v57_levels_ops.php', 'linux_v57_lab.php'] as $lib) require_once $ROOT . '/' . $lib;

const V76A_WAVE2 = [17, 28];
$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu i cache modelu jsou dočasné', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory
$classes = ['class_1a' => '1a', 'class_2a' => '2a', 'class_3a' => '3a', 'class_4a' => '4a'];
$contentFiles = [];
foreach ($classes as $short) foreach (['a', 'b'] as $part) $contentFiles[] = 'lesson_content_v76_' . $short . '_' . $part . '.php';
$levelFiles = array_values(LV76_CLASS_FILES);
$bad = [];
foreach (array_merge($contentFiles, $levelFiles, ['ai_prompts_v76.php']) as $f) {
    $s = $read($f);
    if ($s === '' || !str_contains($s, 'declare(strict_types=1);') || !str_contains($s, "basename(__FILE__)) { http_response_code(403); exit; }") || substr_count($s, "\n") > 800
        || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory vlny 2 (8 obsahu, 4 úrovní, AI prompty): strict_types, guard, ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$check('overlay lm71 načítá právě 8 souborů vlny 2 (lesson_content_v76_*) po souborech vlny 1', array_values(array_filter(lm71_overlay_files(), static fn(string $f): bool => str_starts_with($f, 'lesson_content_v76_'))) === $contentFiles
    && count(lm71_overlay_files()) === 16);

// ---------------------------------------------------------------- 2) povinná pole a úplnost
$rawOv = [];
$lessons = [];
$fieldBad = [];
foreach ($classes as $c => $short) {
    $all = (array)(lm71_raw($c)['overlay']['lessons'] ?? []);
    $ovs = array_filter($all, static fn(int $n): bool => $n >= V76A_WAVE2[0] && $n <= V76A_WAVE2[1], ARRAY_FILTER_USE_KEY);
    if (array_keys($ovs) !== range(V76A_WAVE2[0], V76A_WAVE2[1])) $fieldBad[] = $c . ': overlay není přesně L17–L28';
    foreach ($ovs as $n => $ov) {
        if (!str_starts_with((string)($ov['_file'] ?? ''), 'lesson_content_v76_' . $short . '_')) $fieldBad[] = $c . ' L' . $n . ' soubor';
        $rawOv[$c][$n] = $ov;
        $lessons[$c][$n] = lm71_lesson($c, (int)$n);
        $id = $c . ' L' . $n;
        if (($ov['status'] ?? '') !== 'navrh' || (int)($ov['version'] ?? 0) !== 1) $fieldBad[] = $id . ' stav';
        if (count((array)($ov['goal']['success_criteria'] ?? [])) < 2 || count((array)($ov['goal']['success_criteria'] ?? [])) > 4) $fieldBad[] = $id . ' kritéria';
        if (!str_starts_with((string)($ov['goal']['student'] ?? ''), 'Na konci hodiny umím')) $fieldBad[] = $id . ' cíl';
        foreach ((array)($ov['timeline'] ?? []) as $seg) if (trim((string)($seg['teacher'] ?? '')) === '' || trim((string)($seg['student'] ?? '')) === '' || trim((string)($seg['form'] ?? '')) === '') $fieldBad[] = $id . ' úsek plánu';
        if (count((array)($ov['tasks'] ?? [])) < 3) $fieldBad[] = $id . ' úkoly';
        foreach ((array)($ov['tasks'] ?? []) as $t) if (trim((string)($t['text'] ?? '')) === '' || trim((string)($t['output'] ?? '')) === '' || preg_match('/^\d+ min$/', (string)($t['time'] ?? '')) !== 1) $fieldBad[] = $id . ' úkol';
        $rubric = (array)($ov['assessment']['rubric'] ?? []);
        if (count($rubric) < 3 || count($rubric) > 5) $fieldBad[] = $id . ' rubrika';
        foreach ($rubric as $r) if (trim((string)($r['criterion'] ?? '')) === '' || count((array)($r['levels'] ?? [])) !== 4) $fieldBad[] = $id . ' rubrika 1–4';
        if ((array)($ov['assessment']['formative'] ?? []) === []) $fieldBad[] = $id . ' formativní kontrola';
        if (count((array)($ov['teacher_notes'] ?? [])) < 2 || count((array)($ov['substitution'] ?? [])) < 2 || (array)($ov['safety'] ?? []) === []) $fieldBad[] = $id . ' poznámky/zástup/bezpečnost';
        if (count(array_filter((array)($ov['differentiation'] ?? []), static fn($v): bool => trim((string)$v) !== '')) !== 3) $fieldBad[] = $id . ' diferenciace';
    }
}
$all = array_merge(...array_values(array_map('array_values', $lessons)));
$check('48 lekcí (4 třídy × L17–L28) má povinná pole: cíl „Na konci hodiny umím…“, kritéria 2–4, úseky plánu (učitel, žák, forma), ≥ 3 úkoly s výstupem a časem, rubrika 3–5 × 1–4, formativní kontrola, diferenciace, bezpečnost, poznámky, zástup, stav „navrh“'
    . ($fieldBad ? ' [' . implode(', ', array_slice($fieldBad, 0, 6)) . ']' : ''), count($all) === 48 && $fieldBad === []);
$incomplete = array_values(array_map(static fn(array $l): string => $l['id'], array_filter($all, static fn(array $l): bool => !$l['completeness']['complete'])));
$check('úplnost modelu lm71 12/12 u všech 48 lekcí vlny 2' . ($incomplete ? ' [' . implode(', ', $incomplete) . ']' : ''), $incomplete === []);
$check('plán po minutách: souvislý od 0 do 90 min, ≥ 3 úseky, nedopočtený z kroků', array_reduce($all, static fn(bool $ok, array $l): bool => $ok && lm71_timeline_ok($l['timeline'])
    && array_sum(array_column($l['timeline'], 'minutes')) === LM71_LESSON_MINUTES, true));
$titlesOk = true;
foreach ($classes as $c => $short) {
    $t = array_map(static fn(int $n): string => lm71_lesson($c, $n)['title'], range(1, LM71_LESSONS));
    $titlesOk = $titlesOk && count(array_unique($t)) === LM71_LESSONS;
}
$check('titulky vlny 2 česky (heuristika lm71), „Lekce N · …“, do 70 znaků; všech 28 titulků každé třídy je jedinečných', $titlesOk && array_reduce($all, static fn(bool $ok, array $l): bool => $ok
    && !lm71_is_english_title($l['title']) && mb_strlen($l['title']) <= LM71_TITLE_MAX && str_starts_with($l['title'], 'Lekce ' . $l['number'] . ' · '), true));

// ---------------------------------------------------------------- 3) exit ticket
$exitBad = [];
$positions = [];
$questions = [];
foreach ($classes as $c => $short) {
    $classQuiz = [];
    foreach (lm71_lessons($c) as $l) foreach ($l['assessment']['checks'] as $q) $classQuiz[lm71_norm_text((string)$q['question'])] = true;
    $wave1 = [];
    foreach ((array)(lm71_raw($c)['overlay']['lessons'] ?? []) as $n => $ov) if ($n < V76A_WAVE2[0]) foreach ((array)($ov['exit_ticket']['variants'] ?? []) as $v) $wave1[lm71_norm_text((string)($v['question'] ?? ''))] = true;
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
            if (isset($classQuiz[$q]) || isset($wave1[$q])) $exitBad[] = $c . ' L' . $n . ' = kvíz / vlna 1';
            if (count($opts) < 3 || count($opts) > 4 || !isset($opts[$k]) || count(array_unique(array_map('lm71_norm_text', $opts))) !== count($opts)) $exitBad[] = $c . ' L' . $n . ' možnosti';
            if (trim((string)($v['explanation'] ?? '')) === '') $exitBad[] = $c . ' L' . $n . ' vysvětlení';
        }
        if (count(array_unique($corr)) < 2) $exitBad[] = $c . ' L' . $n . ' correct na stejné pozici';
    }
}
ksort($positions);
$total = array_sum($positions);
$check('exit ticket: ≥ 3 varianty, 3–4 různé možnosti, platné `correct`, vysvětlení, otázka se neshoduje s kvízem třídy ani s vlnou 1' . ($exitBad ? ' [' . implode(', ', array_slice(array_unique($exitBad), 0, 6)) . ']' : ''), $exitBad === []);
$check('`correct` na různých pozicích: v každé lekci aspoň 2, každá z pozic 0–2 má 20–50 % správných odpovědí (' . json_encode($positions) . ')',
    $total === 144 && array_keys($positions) === [0, 1, 2] && min($positions) >= 0.2 * $total && max($positions) <= 0.5 * $total);
$goals = array_map(static fn(array $l): string => lm71_norm_text((string)$l['goal']['student']), $all);
$taskDup = [];
foreach ($classes as $c => $short) {
    $texts = [];
    foreach ((array)(lm71_raw($c)['overlay']['lessons'] ?? []) as $ov) foreach ((array)($ov['tasks'] ?? []) as $t) $texts[] = lm71_norm_text((string)($t['text'] ?? ''));
    $taskDup = array_merge($taskDup, array_keys(array_filter(array_count_values($texts), static fn(int $n): bool => $n > 1)));
}
$check('bez duplicit: 144 otázek exit ticketu, 48 cílů, úkoly ve třídě (vlna 1 + 2) a šablonové úkoly = 0', count(array_unique($questions)) === 144 && count(array_unique($goals)) === 48 && $taskDup === []
    && array_reduce($all, static fn(bool $ok, array $l): bool => $ok && array_filter($l['tasks'], static fn(array $t): bool => $t['template']) === [], true));

// ---------------------------------------------------------------- 4) glosář, domácí příprava, kompetence, ŠVP, bezpečnost obsahu
$glossBad = [];
$hwBad = [];
$compBad = [];
$textBad = [];
foreach ($rawOv as $c => $rows) {
    $subject = comp62_subject_for_class($c);
    $catalog = $subject !== null ? comp62_competencies($subject) : comp75_flat((string)comp75_subject_for_class($c));
    foreach ($rows as $n => $ov) {
        $id = $c . ' L' . $n;
        $text = (string)json_encode($ov, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ((array)($ov['glossary'] ?? []) === []) $glossBad[] = $id . ' bez glosáře';
        foreach ((array)($ov['glossary'] ?? []) as $g) if (lg72_term((string)$g) === null || !lg72_mentioned((string)$g, $text)) $glossBad[] = $id . ' ' . $g;
        $hw = (array)($ov['homework'] ?? []);
        if (count($hw) !== 1 || ($hw[0]['optional'] ?? false) !== true || (int)($hw[0]['minutes'] ?? 0) < 1 || (int)($hw[0]['minutes'] ?? 0) > LC72_HOMEWORK_MAX_MIN || stripos((string)($hw[0]['text'] ?? ''), 'Volitelné') !== 0) $hwBad[] = $id;
        if (!isset($catalog[(string)($ov['exit_ticket']['competence'] ?? '')]) || trim((string)($ov['exit_ticket']['competence_label'] ?? '')) === '') $compBad[] = $id . ' exit';
        if ((array)($ov['competencies'] ?? []) === []) $compBad[] = $id . ' bez kompetencí';
        foreach ((array)($ov['competencies'] ?? []) as $cp) if (!isset($catalog[(string)($cp['id'] ?? '')]) || (int)($cp['level'] ?? 0) < 1 || (int)($cp['level'] ?? 0) > 4) $compBad[] = $id . ' ' . (string)($cp['id'] ?? '');
        if (isset($ov['curriculum'])) $textBad[] = $id . ' ŠVP v overlayi';
        if (preg_match('~https?://(?!(?:localhost|intranet\.skola\.test|10\.0\.0\.\d{1,3})\b)|materials/|chatgpt|<script|javascript:~i', $text) === 1) $textBad[] = $id . ' odkaz';
        preg_match_all('/[\p{L}0-9._-]+@([A-Za-z0-9.-]+)/u', $text, $mm);
        foreach ($mm[1] as $dom) if (rtrim($dom, '.') !== 'example.com') $textBad[] = $id . ' e-mail ' . $dom;
        preg_match_all('/\b(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\b/', $text, $ips, PREG_SET_ORDER);
        foreach ($ips as $ip) {
            $a = (int)$ip[1]; $b = (int)$ip[2];
            $private = $a === 10 || $a === 127 || ($a === 192 && $b === 168) || ($a === 172 && $b >= 16 && $b <= 31) || ($a === 192 && $b === 0 && (int)$ip[3] === 2) || ($a === 198 && $b === 51) || ($a === 203 && $b === 0) || $ip[0] === '0.0.0.0';
            if (!$private) $textBad[] = $id . ' veřejná IP ' . $ip[0];
        }
    }
}
$check('glosář: každá lekce má termíny, id existuje a termín je v textu lekce' . ($glossBad ? ' [' . implode(', ', array_slice($glossBad, 0, 6)) . ']' : ''), $glossBad === []);
$check('domácí příprava: jedna položka „Volitelné: …“, optional, 1–' . LC72_HOMEWORK_MAX_MIN . ' min (rozhodnutí školy: dobrovolná)' . ($hwBad ? ' [' . implode(', ', array_slice($hwBad, 0, 5)) . ']' : ''), $hwBad === []);
$check('kompetence: 1.A/3.A z katalogu v62, 2.A/4.A z návrhu katalogu v75, úroveň 1–4, exit ticket s kompetencí a popisem' . ($compBad ? ' [' . implode(', ', array_slice($compBad, 0, 5)) . ']' : ''), $compBad === []);
$svpOk = array_reduce($all, static fn(bool $ok, array $l): bool => $ok && is_array($l['curriculum']['svp'] ?? null) && ($l['curriculum']['svp']['outcomes'] ?? []) !== [], true);
$check('ŠVP v72: každá lekce vlny 2 má vazbu na výsledky ŠVP (návrh z svp_v72_map.php, mimo overlay i otisk); obsah bez externích URL, materials/, cizích e-mailů a veřejných IP (jen RFC 1918 / 5737)'
    . ($textBad ? ' [' . implode(', ', array_slice($textBad, 0, 5)) . ']' : ''), $svpOk && $textBad === []);

// ---------------------------------------------------------------- 5) technická fakta v simulátoru
$simTotal = 0;
$simBad = [];
$simLessons = ['class_3a' => 0, 'class_4a' => 0];
foreach ($rawOv as $c => $rows) foreach ($rows as $n => $ov) {
    $has = false;
    foreach ((array)($ov['tasks'] ?? []) as $t) foreach ((array)($t['sim'] ?? []) as $sim) {
        $simTotal++;
        $has = true;
        $w = lab57_world_new('v72-audit', 1791100000, ['nginx' => true]);
        $out = '';
        foreach (lab57_run_line($w, (string)$sim['cmd'])['chunks'] as [$fd, $chunk]) $out .= (string)$chunk;
        $out = (string)preg_replace('/\e\[[0-9;]*m/', '', $out);
        if (!str_contains($out, (string)$sim['expect'])) $simBad[] = $c . ' L' . $n . ': ' . $sim['cmd'];
    }
    if ($has && isset($simLessons[$c])) $simLessons[$c]++;
    if ($has && !isset($simLessons[$c])) $simBad[] = $c . ' L' . $n . ' sim mimo 3.A/4.A';
}
$check('technická fakta: ' . $simTotal . ' příkazů z obsahu 3.A/4.A se v simulátoru Linux Labu chová podle zadání; každá lekce 3.A i 4.A má ověřený příkaz'
    . ($simBad ? ' [' . implode(' | ', array_slice($simBad, 0, 4)) . ']' : ''), $simTotal >= 80 && $simBad === [] && $simLessons === ['class_3a' => 12, 'class_4a' => 12]);

require __DIR__ . '/lib/v76_content_part2.php';

exit(audit_summary($state, 'V76_CONTENT'));
