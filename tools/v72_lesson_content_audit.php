<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v72 · audit obsahu vlny 1 (L5–L16 pro 1.A, 2.A, 3.A, 4.A = 48 lekcí) a glosáře.
 *   1) soubory obsahu a glosáře (strict_types, guard, ≤ 800 řádků, PHP 8.1), overlay lm71 načítá všech 8 souborů,
 *   2) povinná pole modelu u 48 lekcí, úplnost 12/12, plán souvislý 0–90 min, rubrika 3–5 × 1–4, stav „navrh“,
 *   3) exit ticket: ≥ 3 varianty, jiná otázka než kvíz lekce i třídy, `correct` není u všech variant na stejné pozici,
 *      rozložení správných pozic vyrovnané, bez duplicit (otázky, cíle, úkoly, titulky),
 *   4) glosář: každé id existuje, termín je v textu lekce, každý termín je použitý,
 *   5) domácí příprava jen volitelná a v limitu školy, kompetence z katalogu v62 (pilot 1.A/3.A), ŠVP „chybí vazba“,
 *      žádné odkazy na AI prompty / materials/ ani externí URL, e-maily jen ukázkové,
 *   6) technická fakta: každý příkaz `sim` se spustí v simulátoru Linux Labu (nic skutečného) a výstup obsahuje `expect`.
 *   php tools/v72_lesson_content_audit.php     Dočasné úložiště i cache modelu. Konec: V72_LESSON_CONTENT_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v72-content')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
foreach (['tutorial_v52.php', 'learning_v56.php', 'lesson_model_v71.php', 'lesson_glossary_v72.php', 'competencies_v62.php', 'lesson_approval_v72.php',
    'linux_v57_core.php', 'linux_v57_world.php', 'linux_v57_shell.php', 'linux_v57_cmd_files.php', 'linux_v57_cmd_shell.php', 'linux_v57_cmd_text.php',
    'linux_v57_cmd_sys.php', 'linux_v57_cmd_net.php', 'linux_v57_manual.php', 'linux_v57_levels.php', 'linux_v57_levels_ops.php', 'linux_v57_lab.php'] as $lib) require_once $ROOT . '/' . $lib;

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu i cache modelu jsou dočasné', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory
$classes = ['class_1a' => '1a', 'class_2a' => '2a', 'class_3a' => '3a', 'class_4a' => '4a'];
$contentFiles = [];
foreach ($classes as $c => $short) foreach (['a', 'b'] as $part) $contentFiles[] = 'lesson_content_v72_' . $short . '_' . $part . '.php';
$bad = [];
foreach (array_merge($contentFiles, ['lesson_glossary_v72.php']) as $f) {
    $s = $read($f);
    if ($s === '' || !str_contains($s, 'declare(strict_types=1);') || !str_contains($s, "basename(__FILE__)) { http_response_code(403); exit; }") || substr_count($s, "\n") > 800
        || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory obsahu (8) a glosáře: strict_types, guard, ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === [], false);
$check('overlay lm71 načítá právě 8 souborů vlny 1 (lesson_content_v72_*)', array_values(array_filter(lm71_overlay_files(), static fn(string $f): bool => str_starts_with($f, 'lesson_content_v72_'))) === $contentFiles);

// ---------------------------------------------------------------- 2) povinná pole a úplnost
$lessons = [];
$rawOv = [];
$fieldBad = [];
foreach ($classes as $c => $short) {
    $ovs = (array)(lm71_raw($c)['overlay']['lessons'] ?? []);
    if (array_keys($ovs) !== range(LC72_WAVE1[0], LC72_WAVE1[1])) $fieldBad[] = $c . ': overlay není přesně L5–L16';
    foreach ($ovs as $n => $ov) {
        $rawOv[$c][$n] = $ov;
        $l = lm71_lesson($c, (int)$n);
        $lessons[$c][$n] = $l;
        $id = $c . ' L' . $n;
        if (($ov['status'] ?? '') !== 'navrh') $fieldBad[] = $id . ' stav';
        if (count((array)($ov['goal']['success_criteria'] ?? [])) < 2 || count((array)($ov['goal']['success_criteria'] ?? [])) > 4) $fieldBad[] = $id . ' kritéria';
        foreach ((array)($ov['timeline'] ?? []) as $seg) if (trim((string)($seg['teacher'] ?? '')) === '' || trim((string)($seg['student'] ?? '')) === '' || trim((string)($seg['form'] ?? '')) === '') $fieldBad[] = $id . ' úsek plánu';
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
$check('48 lekcí (4 třídy × L5–L16) má povinná pole: kritéria 2–4, úseky plánu (učitel, žák, forma), úkoly s výstupem a časem, rubrika 3–5 × 1–4, formativní kontrola, diferenciace, bezpečnost, poznámky, zástup, stav „navrh“'
    . ($fieldBad ? ' [' . implode(', ', array_slice($fieldBad, 0, 6)) . ']' : ''), count($all) === 48 && $fieldBad === []);
$incomplete = array_values(array_map(static fn(array $l): string => $l['id'], array_filter($all, static fn(array $l): bool => !$l['completeness']['complete'])));
$check('úplnost modelu lm71 12/12 u všech 48 lekcí (vlna 1 = 100 %)' . ($incomplete ? ' [' . implode(', ', $incomplete) . ']' : ''), $incomplete === []);
$check('plán po minutách: souvislý od 0 do 90 min, ≥ 3 úseky, nedopočtený z kroků (u všech 48 lekcí)', array_reduce($all, static fn(bool $ok, array $l): bool => $ok && lm71_timeline_ok($l['timeline'])
    && array_sum(array_column($l['timeline'], 'minutes')) === LM71_LESSON_MINUTES, true));
$check('titulky česky (heuristika lm71), do 70 znaků a jedinečné ve třídě', array_reduce($all, static fn(bool $ok, array $l): bool => $ok && !lm71_is_english_title($l['title']) && mb_strlen($l['title']) <= LM71_TITLE_MAX, true)
    && array_reduce(array_keys($lessons), static fn(bool $ok, string $c): bool => $ok && count(array_unique(array_column($lessons[$c], 'title'))) === count($lessons[$c]), true));

// ---------------------------------------------------------------- 3) exit ticket
$exitBad = [];
$positions = [];
$questions = [];
foreach ($classes as $c => $short) {
    $classQuiz = [];
    foreach (lm71_lessons($c) as $l) foreach ($l['assessment']['checks'] as $q) $classQuiz[lm71_norm_text((string)$q['question'])] = true;
    foreach ($rawOv[$c] as $n => $ov) {
        $variants = (array)($ov['exit_ticket']['variants'] ?? []);
        $own = array_map(static fn(array $q): string => lm71_norm_text((string)$q['question']), $lessons[$c][$n]['assessment']['checks']);
        if (count($variants) < LM71_EXIT_VARIANTS_MIN) $exitBad[] = $c . ' L' . $n . ' < 3 varianty';
        $corr = [];
        foreach ($variants as $v) {
            $q = lm71_norm_text((string)($v['question'] ?? ''));
            $opts = (array)($v['options'] ?? []);
            $corr[] = (int)($v['correct'] ?? -1);
            $positions[(int)($v['correct'] ?? -1)] = ($positions[(int)($v['correct'] ?? -1)] ?? 0) + 1;
            $questions[] = $q;
            if (in_array($q, $own, true) || isset($classQuiz[$q])) $exitBad[] = $c . ' L' . $n . ' = kvíz';
            if (count($opts) < 3 || count($opts) > 4 || !isset($opts[(int)($v['correct'] ?? -1)]) || count(array_unique(array_map('lm71_norm_text', $opts))) !== count($opts)) $exitBad[] = $c . ' L' . $n . ' možnosti';
            if (trim((string)($v['explanation'] ?? '')) === '') $exitBad[] = $c . ' L' . $n . ' vysvětlení';
        }
        if (count(array_unique($corr)) < 2) $exitBad[] = $c . ' L' . $n . ' correct na stejné pozici';
    }
}
ksort($positions);
$total = array_sum($positions);
$check('exit ticket: ≥ 3 varianty, 3–4 různé možnosti, platné `correct`, vysvětlení, otázka se neshoduje s kvízem lekce ani třídy' . ($exitBad ? ' [' . implode(', ', array_slice(array_unique($exitBad), 0, 6)) . ']' : ''), $exitBad === []);
$check('`correct` není na stejné pozici: v každé lekci aspoň 2 různé pozice, každá z pozic 0–2 má 20–50 % správných odpovědí (' . json_encode($positions) . ')',
    $total === 144 && array_keys($positions) === [0, 1, 2] && min($positions) >= 0.2 * $total && max($positions) <= 0.5 * $total);
$goals = array_map(static fn(array $l): string => lm71_norm_text((string)$l['goal']['student']), $all);
$taskDup = [];
foreach ($lessons as $c => $rows) {
    $texts = [];
    foreach ($rows as $l) foreach ($l['tasks'] as $t) $texts[] = lm71_norm_text($t['text']);
    $taskDup = array_merge($taskDup, array_keys(array_filter(array_count_values($texts), static fn(int $n): bool => $n > 1)));
}
$check('bez duplicit: 144 otázek exit ticketu, 48 cílů, úkoly ve třídě a šablonové úkoly (≥ 3 lekce) = 0', count(array_unique($questions)) === 144 && count(array_unique($goals)) === 48 && $taskDup === []
    && array_reduce($all, static fn(bool $ok, array $l): bool => $ok && array_filter($l['tasks'], static fn(array $t): bool => $t['template']) === [], true));

// ---------------------------------------------------------------- 4) glosář
$terms = lg72_terms();
$used = [];
$glossBad = [];
foreach ($rawOv as $c => $rows) foreach ($rows as $n => $ov) {
    $text = (string)json_encode($ov, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    foreach ((array)($ov['glossary'] ?? []) as $id) {
        $used[$id] = true;
        if (!isset($terms[$id])) $glossBad[] = $c . ' L' . $n . ' neznámé id ' . $id;
        elseif (!lg72_mentioned($id, $text)) $glossBad[] = $c . ' L' . $n . ' nezmiňuje ' . $id;
    }
}
$unused = array_diff(array_keys($terms), array_keys($used));
$check('glosář: ' . count($terms) . ' termínů s českým ekvivalentem a vysvětlením; každé id lekce existuje a termín je v textu lekce; žádný nepoužitý termín'
    . ($glossBad || $unused ? ' [' . implode(', ', array_slice(array_merge($glossBad, array_map(static fn(string $u): string => 'nepoužito ' . $u, $unused)), 0, 6)) . ']' : ''),
    count($terms) >= 80 && $glossBad === [] && $unused === [] && array_reduce($terms, static fn(bool $ok, array $t): bool => $ok && $t['cs'] !== '' && mb_strlen($t['explain']) >= 15
        && in_array($t['area'], ['grafika', 'site', 'provoz'], true), true));

// ---------------------------------------------------------------- 5) pravidla školy, kompetence, bezpečnost obsahu
$hwBad = [];
$compBad = [];
$textBad = [];
foreach ($rawOv as $c => $rows) {
    $subject = comp62_subject_for_class($c);
    $catalog = $subject !== null ? comp62_competencies($subject) : [];
    foreach ($rows as $n => $ov) {
        $minutes = 0;
        foreach ((array)($ov['homework'] ?? []) as $h) {
            $minutes += (int)($h['minutes'] ?? 0);
            if (($h['optional'] ?? false) !== true || (int)($h['minutes'] ?? 0) < 1 || stripos((string)($h['text'] ?? ''), 'Volitelné') !== 0) $hwBad[] = $c . ' L' . $n;
        }
        if ($minutes > LC72_HOMEWORK_MAX_MIN || count((array)($ov['homework'] ?? [])) !== 1) $hwBad[] = $c . ' L' . $n . ' limit';
        $exitComp = (string)($ov['exit_ticket']['competence'] ?? '');
        if (trim((string)($ov['exit_ticket']['competence_label'] ?? '')) === '') $compBad[] = $c . ' L' . $n . ' popis';
        if (comp62_enabled_for_class($c)) {
            if (!isset($catalog[$exitComp])) $compBad[] = $c . ' L' . $n . ' exit ' . $exitComp;
            foreach ((array)($ov['competencies'] ?? []) as $cp) if (!isset($catalog[(string)($cp['id'] ?? '')]) || (int)($cp['level'] ?? 0) < 1 || (int)($cp['level'] ?? 0) > 4) $compBad[] = $c . ' L' . $n . ' kompetence';
            if ((array)($ov['competencies'] ?? []) === []) $compBad[] = $c . ' L' . $n . ' bez kompetencí';
        } elseif ($exitComp !== '' || (array)($ov['competencies'] ?? []) !== []) {
            $compBad[] = $c . ' L' . $n . ' třída bez katalogu (v75)';
        }
        $text = (string)json_encode($ov, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (isset($ov['curriculum'])) $textBad[] = $c . ' L' . $n . ' ŠVP';
        if (preg_match('~https?://(?!(?:localhost|intranet\.skola\.test|10\.0\.0\.\d{1,3})\b)|materials/|AI prompt|chatgpt|<script|javascript:~i', $text) === 1) $textBad[] = $c . ' L' . $n . ' odkaz';
        preg_match_all('/[\p{L}0-9._-]+@([A-Za-z0-9.-]+)/u', $text, $mm);
        foreach ($mm[1] as $dom) if (!in_array(rtrim($dom, '.'), ['example.com', '10.0.0.10'], true)) $textBad[] = $c . ' L' . $n . ' e-mail ' . $dom;
    }
}
$check('domácí příprava: jedna položka „Volitelné: …“, optional, 1–' . LC72_HOMEWORK_MAX_MIN . ' min (rozhodnutí školy čeká – výchozí limit)' . ($hwBad ? ' [' . implode(', ', array_slice($hwBad, 0, 5)) . ']' : ''), $hwBad === [] && LC72_HOMEWORK_OPTIONAL === true);
$check('kompetence: pilot 1.A/3.A jen id z katalogu v62 (úroveň 1–4, exit ticket s kompetencí), 2.A/4.A bez katalogu (v75) jen s popisem' . ($compBad ? ' [' . implode(', ', array_slice($compBad, 0, 5)) . ']' : ''), $compBad === []);
$check('obsah: ŠVP zůstává „' . LC72_SVP_DEFAULT . '“ (žádné vymyšlené kódy), žádné externí URL (jen hosté labu: localhost, intranet.skola.test, 10.0.0.x), odkazy do materials/ ani na AI prompty, e-maily jen example.com' . ($textBad ? ' [' . implode(', ', array_slice($textBad, 0, 5)) . ']' : ''),
    $textBad === [] && array_reduce($all, static fn(bool $ok, array $l): bool => $ok && empty($l['curriculum']['svp']), true) && LC72_LINK_AI_PROMPTS === false);

// ---------------------------------------------------------------- 6) technická fakta v simulátoru
$simTotal = 0;
$simBad = [];
foreach ($rawOv as $c => $rows) foreach ($rows as $n => $ov) foreach ((array)($ov['tasks'] ?? []) as $t) foreach ((array)($t['sim'] ?? []) as $sim) {
    $simTotal++;
    $w = lab57_world_new('v72-audit', 1791100000, ['nginx' => true]);
    $out = '';
    foreach (lab57_run_line($w, (string)$sim['cmd'])['chunks'] as [$fd, $chunk]) $out .= (string)$chunk;
    $out = (string)preg_replace('/\e\[[0-9;]*m/', '', $out);
    if (!str_contains($out, (string)$sim['expect'])) $simBad[] = $c . ' L' . $n . ': ' . $sim['cmd'];
}
$simClasses = array_keys(array_filter($rawOv, static fn(array $rows): bool => array_filter($rows, static fn(array $ov): bool => array_filter((array)($ov['tasks'] ?? []), static fn($t): bool => !empty($t['sim'])) !== []) !== []));
$check('technická fakta: ' . $simTotal . ' příkazů z obsahu 3.A/4.A se v simulátoru Linux Labu chová podle zadání (očekávaný výstup)' . ($simBad ? ' [' . implode(' | ', array_slice($simBad, 0, 4)) . ']' : ''),
    $simTotal >= 40 && $simBad === [] && $simClasses === ['class_3a', 'class_4a']);
$check('3.A: každá lekce L5–L16 kromě L8 (výpočty na papíře) má aspoň jeden ověřený příkaz simulátoru', array_reduce(array_keys($rawOv['class_3a']), static fn(bool $ok, int $n): bool => $ok
    && ($n === 8 || array_filter((array)$rawOv['class_3a'][$n]['tasks'], static fn($t): bool => !empty($t['sim'])) !== []), true));

exit(audit_summary($state, 'V72_LESSON_CONTENT'));
