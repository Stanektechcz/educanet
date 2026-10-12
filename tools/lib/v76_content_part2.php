<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · druhá část auditu obsahu vlny 2 (načítá tools/v76_content_audit.php, sdílí $check, $rawOv, $classes, $read).
 * Úrovně L1–L28, schválení a viditelnost pro žáka, AI prompty vlny 2 a úplnost 112 lekcí. Vše v dočasném úložišti auditu.
 */

// ---------------------------------------------------------------- 6) úrovně Základ / Standard / Výzva
$lvBad = [];
foreach (array_keys(LV75_CLASS_FILES) as $c) {
    $content = lv75_content($c);
    if (array_keys($content) !== range(1, 28)) $lvBad[] = $c . ':lekce';
    foreach ($content as $n => $row) {
        if ($n < 17) continue;
        $texts = [];
        if (trim((string)($row['activity'] ?? '')) === '') $lvBad[] = $c . ':L' . $n;
        foreach (array_keys(LV75_LEVELS) as $k) {
            if (trim((string)($row[$k]['task'] ?? '')) === '' || trim((string)($row[$k]['check'] ?? '')) === '') $lvBad[] = $c . ':L' . $n . ':' . $k;
            $texts[] = (string)($row[$k]['task'] ?? '');
        }
        if (count(array_unique($texts)) !== 3) $lvBad[] = $c . ':L' . $n . ':shodné';
    }
}
$check('úrovně: L1–L28 všech 4 tříd (v76 přidává L17–L28 = 48 lekcí × Základ / Standard / Výzva se zadáním a „hotovo, když“), úrovně se liší'
    . ($lvBad ? ' [' . implode(', ', array_slice($lvBad, 0, 5)) . ']' : ''), $lvBad === [] && LV75_LESSONS_MAX === 28);
$check('úrovně L20 3.A: bez schválení je žák nevidí; po schválení (otisk obsahu) ano', lv75_student_levels('class_3a', 20) === null
    && lv75_decide('lv75_approve', 'class_3a', ['20'], '', 'Audit') === 1 && (lv75_student_levels('class_3a', 20)['activity'] ?? '') === 'Služby po restartu');

// ---------------------------------------------------------------- schválení a viditelnost pro žáka
$hash20 = lc72_overlay_hash(lc72_overlay('class_3a', 20));
$before = lc72_student_overlay('class_3a', 20) === null && lx72_ticket('class_3a', 20) === null && lc72_status('class_3a', 20)['status'] === 'navrh';
lc72_decide('lc72_approve', 'class_3a', 20, $hash20, '', 'Audit Učitel');
$ticket = lx72_ticket('class_3a', 20);
$check('schvalování vlny 2: návrh L20 3.A žák nevidí (ani exit ticket); po lc72_approve vidí schválený obsah a exit ticket se 3 variantami; přehled třídy má 24 lekcí',
    $before && lc72_status('class_3a', 20)['status'] === 'schvaleno' && is_array(lc72_student_overlay('class_3a', 20)) && is_array($ticket) && count((array)($ticket['variants'] ?? [])) === 3
    && count(lc72_overview('class_3a', '2026-10-12')) === 24 && lc72_overlay_lessons('class_1a') === range(5, 28));

// ---------------------------------------------------------------- 7) AI prompty vlny 2
$promptBad = [];
foreach ($rawOv as $c => $rows) foreach ($rows as $n => $ov) {
    $p = aip76_prompts($c, $ov);
    $titleOnly = trim((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', (string)$ov['title']));
    $joined = implode("\n", array_map(static fn(array $x): string => $x['title'] . "\n" . $x['text'], $p));
    if (count($p) !== 6 || count(array_unique(array_column($p, 'text'))) !== 6 || !str_contains($joined, $titleOnly)) $promptBad[] = $c . ' L' . $n . ' tvar';
    if (preg_match('~https?://|@|<|EDUCANET~i', $joined) === 1) $promptBad[] = $c . ' L' . $n . ' odkaz/identita';
    foreach ((array)($ov['exit_ticket']['variants'] ?? []) as $v) if (str_contains($joined, (string)$v['explanation'])) $promptBad[] = $c . ' L' . $n . ' prozrazuje odpověď';
    foreach ($p as $x) if (mb_strlen($x['text']) > AIP721_MAX_LEN) $promptBad[] = $c . ' L' . $n . ' délka';
}
$check('AI prompty vlny 2 (styl v72.1): 6 různých promptů na lekci z názvu, cíle, kritérií, úkolů a glosáře; bez odkazů, e-mailů, identity školy a bez vysvětlení exit ticketu'
    . ($promptBad ? ' [' . implode(', ', array_slice($promptBad, 0, 5)) . ']' : ''), $promptBad === []);
$kit21 = aip721_parse((string)file_get_contents(aip721_kit_path('class_3a', 21)));
$student20 = aip721_prompts('class_3a', 20);
$block = audit_capture(static fn() => aip721_render_student_block('class_3a', 20));
$check('AI prompty žáka: schválená L20 3.A = prompty ze schváleného obsahu (blok s 5 body upozornění na soukromí, 6 promptů, bez odkazů a skriptů); neschválená L21 = prompty z lesson kitu',
    $student20 === aip76_prompts('class_3a', lc72_student_overlay('class_3a', 20)) && str_contains($student20[0]['title'], 'Služby a jejich závislosti')
    && aip721_prompts('class_3a', 21) === $kit21 && $kit21 !== [] && substr_count($block, '<li>') === 5 && substr_count($block, '<pre class="lx72-prompt"') === 6
    && preg_match('/<script|<a\s|<form|on[a-z]+=/i', $block) !== 1);
$prev = aip721_teacher_preview_html('class_3a', 21);
$check('AI prompty učitele: náhled neschválené L21 3.A ve Schvalování ukazuje 6 promptů z návrhu s vysvětlením, že je žák uvidí až po schválení',
    substr_count($prev, '<li><strong>') === 6 && str_contains($prev, 'uvidí až po schválení') && str_contains($prev, e(aip76_prompts('class_3a', lc72_overlay('class_3a', 21))[0]['title'])));

// ---------------------------------------------------------------- 8) úplnost 112 lekcí
$complete = 0;
$templateLeft = 0;
foreach (LM71_CLASSES as $c) foreach (lm71_lessons($c) as $l) {
    $complete += $l['completeness']['complete'] ? 1 : 0;
    if ($l['number'] >= 19 && (lm71_is_english_title($l['title']) || array_filter($l['tasks'], static fn(array $t): bool => $t['template']) !== [])) $templateLeft++;
}
$check('úplnost: ' . $complete . ' ze 112 lekcí 12/12 (v75: 48), šablonové L19–L28 (anglické titulky, generické úkoly) přepsané všechny', $complete >= 96 && $templateLeft === 0);
$check('dokumentace: CHANGELOG_V76.md, BUILD_MANIFEST_V76.md, docs/NAVOD_V76.md a sekce v76 v INSTALL.md', is_file($ROOT . '/CHANGELOG_V76.md') && is_file($ROOT . '/BUILD_MANIFEST_V76.md')
    && is_file($ROOT . '/docs/NAVOD_V76.md') && str_contains($read('INSTALL.md'), 'v76'));
