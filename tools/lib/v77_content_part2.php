<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · druhá část tools/v77_content_audit.php (používá $ROOT, $tmp, $check, $read, $classes, $rawOv, $all, $contentFiles).
 * Sekce 4–8: glosář, domácí příprava, kompetence, ŠVP, úrovně, bezpečnost textu; simulátor; 112/112 a dny bez lekce; AI prompty;
 * vyřazené soubory, Středa T-14, grafické úlohy v Opakování, kontrast LVL a dokumentace.
 */

// ---------------------------------------------------------------- 4) glosář, domácí příprava, kompetence, ŠVP, úrovně, text
$glossBad = $hwBad = $compBad = $textBad = [];
$textScan = static function (string $id, string $text) use (&$textBad): void {
    if (preg_match('~https?://(?!(?:localhost|intranet\.skola\.test|127\.0\.0\.1|10\.0\.0\.\d{1,3})\b)|materials/|chatgpt|<script|javascript:~i', $text) === 1) $textBad[] = $id . ' odkaz';
    preg_match_all('/[\p{L}0-9._-]+@([A-Za-z0-9.-]+)/u', $text, $mm);
    foreach ($mm[1] as $dom) if (rtrim($dom, '.') !== 'example.com') $textBad[] = $id . ' e-mail ' . $dom;
    preg_match_all('/\b(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\b/', $text, $ips, PREG_SET_ORDER);
    foreach ($ips as $ip) {
        $a = (int)$ip[1]; $b = (int)$ip[2];
        $private = $a === 10 || $a === 127 || ($a === 192 && $b === 168) || ($a === 172 && $b >= 16 && $b <= 31) || ($a === 192 && $b === 0 && (int)$ip[3] === 2) || ($a === 198 && $b === 51) || ($a === 203 && $b === 0) || $ip[0] === '0.0.0.0';
        if (!$private) $textBad[] = $id . ' veřejná IP ' . $ip[0];
    }
};
$levelsBad = [];
foreach ($rawOv as $c => $rows) {
    $subject = comp62_subject_for_class($c);
    $catalog = $subject !== null ? comp62_competencies($subject) : comp75_flat((string)comp75_subject_for_class($c));
    $levels = lv75_content($c);
    foreach ($rows as $n => $ov) {
        $id = $c . ' L' . $n;
        $text = (string)json_encode($ov, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ((array)($ov['glossary'] ?? []) === []) $glossBad[] = $id . ' bez glosáře';
        foreach ((array)($ov['glossary'] ?? []) as $g) if (lg72_term((string)$g) === null || !lg72_mentioned((string)$g, $text)) $glossBad[] = $id . ' ' . $g;
        $hw = (array)($ov['homework'] ?? []);
        if (count($hw) !== 1 || ($hw[0]['optional'] ?? false) !== true || (int)($hw[0]['minutes'] ?? 0) < 1 || (int)($hw[0]['minutes'] ?? 0) > LC72_HOMEWORK_MAX_MIN || stripos((string)($hw[0]['text'] ?? ''), 'Volitelné') !== 0) $hwBad[] = $id;
        if (!isset($catalog[(string)($ov['exit_ticket']['competence'] ?? '')]) || trim((string)($ov['exit_ticket']['competence_label'] ?? '')) === '' || (array)($ov['competencies'] ?? []) === []) $compBad[] = $id;
        foreach ((array)($ov['competencies'] ?? []) as $cp) if (!isset($catalog[(string)($cp['id'] ?? '')]) || (int)($cp['level'] ?? 0) < 1 || (int)($cp['level'] ?? 0) > 4) $compBad[] = $id . ' ' . (string)($cp['id'] ?? '');
        if (isset($ov['curriculum'])) $textBad[] = $id . ' ŠVP v overlayi';
        $textScan($id, $text);
        $lv = $levels[(int)$n] ?? null;
        if (!is_array($lv) || trim((string)($lv['activity'] ?? '')) === '' || array_diff(array_keys(LV75_LEVELS), array_keys($lv)) !== []) $levelsBad[] = $id;
    }
    foreach ((array)(lm71_raw($c)['overlay']['days'] ?? []) as $slot => $ov) $textScan($c . ' den ' . $slot, (string)json_encode($ov, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
$check('glosář L1–L4: každá lekce má termíny, id existuje a termín je v textu' . ($glossBad ? ' [' . implode(', ', array_slice($glossBad, 0, 6)) . ']' : ''), $glossBad === []);
$check('domácí příprava: jedna položka „Volitelné: …“, optional, 1–' . LC72_HOMEWORK_MAX_MIN . ' min; kompetence z katalogu (1.A/3.A v62, 2.A/4.A návrh v75) s úrovní 1–4'
    . ($hwBad || $compBad ? ' [' . implode(', ', array_slice(array_merge($hwBad, $compBad), 0, 5)) . ']' : ''), $hwBad === [] && $compBad === []);
$svpOk = array_reduce($all, static fn(bool $ok, array $l): bool => $ok && is_array($l['curriculum']['svp'] ?? null) && ($l['curriculum']['svp']['outcomes'] ?? []) !== [], true);
$check('ŠVP v72: L1–L4 mají vazbu na výsledky ŠVP (návrh z svp_v72_map.php, mimo overlay); lekce i dny bez URL, materials/, cizích e-mailů a veřejných IP'
    . ($textBad ? ' [' . implode(', ', array_slice($textBad, 0, 5)) . ']' : ''), $svpOk && $textBad === []);
$check('úrovně Základ / Standard / Výzva existují pro L1–L4 všech tříd (levels_v75_content_*, návrh ke schválení v záložce Úrovně v hodině)' . ($levelsBad ? ' [' . implode(', ', $levelsBad) . ']' : ''), $levelsBad === []);

// ---------------------------------------------------------------- 5) technická fakta v simulátoru
$simTotal = 0;
$simBad = [];
$simLessons = ['class_3a' => 0, 'class_4a' => 0];
$runSim = static function (string $id, array $tasks) use (&$simTotal, &$simBad): bool {
    $has = false;
    foreach ($tasks as $t) foreach ((array)($t['sim'] ?? []) as $sim) {
        $simTotal++;
        $has = true;
        $w = lab57_world_new('v72-audit', 1791100000, ['nginx' => true]);
        $out = '';
        foreach (lab57_run_line($w, (string)$sim['cmd'])['chunks'] as [$fd, $chunk]) $out .= (string)$chunk;
        if (!str_contains((string)preg_replace('/\e\[[0-9;]*m/', '', $out), (string)$sim['expect'])) $simBad[] = $id . ': ' . $sim['cmd'];
    }
    return $has;
};
foreach ($rawOv as $c => $rows) foreach ($rows as $n => $ov) {
    $has = $runSim($c . ' L' . $n, (array)($ov['tasks'] ?? []));
    if ($has && isset($simLessons[$c])) $simLessons[$c]++;
    if ($has && !isset($simLessons[$c])) $simBad[] = $c . ' L' . $n . ' sim mimo 3.A/4.A';
}
$simDays = 0;
foreach (['class_3a', 'class_4a'] as $c) foreach ((array)(lm71_raw($c)['overlay']['days'] ?? []) as $slot => $ov) $simDays += $runSim($c . ' den ' . $slot, (array)($ov['tasks'] ?? [])) ? 1 : 0;
$check('technická fakta: ' . $simTotal . ' příkazů (L1–L4 a dny bez lekce 3.A/4.A) se v simulátoru Linux Labu chová podle zadání; každá L1–L4 3.A i 4.A má ověřený příkaz'
    . ($simBad ? ' [' . implode(' | ', array_slice($simBad, 0, 4)) . ']' : ''), $simTotal >= 60 && $simBad === [] && $simLessons === ['class_3a' => 4, 'class_4a' => 4] && $simDays >= 14);

// ---------------------------------------------------------------- 6) 112/112 a dny bez lekce
$complete = 0;
foreach (LM71_CLASSES as $c) foreach (lm71_lessons($c) as $l) $complete += $l['completeness']['complete'] ? 1 : 0;
$check('úplnost: ' . $complete . ' ze 112 lekcí má 12/12 polí (v76: 96)', $complete === 112);
$year = require $ROOT . '/school_year.php';
$calDays = cd71_calendar_days($year);
$dayBad = [];
$cards = [];
foreach (LM71_CLASSES as $c) {
    $titles = [];
    foreach ($calDays as $row) {
        $d = lm71_day($c, $row);
        $cards[] = $d;
        $id = $c . ' den ' . $d['slot'];
        $titles[] = $d['title'];
        if (array_sum(array_column($d['flow'], 'minutes')) !== 90 || count($d['flow']) < 3) $dayBad[] = $id . ' průběh';
        foreach ($d['flow'] as $f) if ($f['text'] === '' || $f['student'] === '' || $f['form'] === '') $dayBad[] = $id . ' úsek';
        if ($d['meta']['status'] !== 'navrh' || !str_starts_with((string)$d['meta']['overlay'], 'lesson_content_v77_days_')) $dayBad[] = $id . ' overlay';
        if (count($d['tasks']) < 3 || count($d['differentiation']) !== 3 || count($d['teacher_notes']) < 2 || count($d['substitution']) < 2 || $d['safety'] === [] || $d['bring'] === [] || count($d['check']) < 2) $dayBad[] = $id . ' pole';
        if (!str_starts_with($d['title'], trim((string)($row['calendar_label'] ?? ($row['title'] ?? ''))))) $dayBad[] = $id . ' titulek';
    }
    if (count(array_unique($titles)) !== count($calDays)) $dayBad[] = $c . ' duplicitní titulky dnů';
}
$check('dny bez lekce: 11 dnů × 4 třídy = 44 karet s plným obsahem (průběh 90 min s učitelem, žáky a formou, ≥ 3 úkoly, diferenciace, poznámky, zástup, bezpečnost); titulek začíná názvem z kalendáře'
    . ($dayBad ? ' [' . implode(', ', array_slice($dayBad, 0, 5)) . ']' : ''), count($calDays) === 11 && count($cards) === 44 && $dayBad === []);
$d29 = lm71_day('class_3a', array_values(array_filter($calDays, static fn(array $r): bool => (int)($r['slot'] ?? 0) === 29))[0]);
$extra = lt77_day_extra_html($d29);
$check('Dnešní hodina: karta dne bez lekce ukazuje úkoly s výstupem, diferenciaci, poznámky, bezpečnost a „Pro zástup a plán B offline“ (každý text přes e())',
    str_contains($extra, 'Úkoly a výstupy dne') && str_contains($extra, 'Diferenciace') && str_contains($extra, 'Poznámky pro učitele') && str_contains($extra, 'Pro zástup a plán B offline')
    && str_contains($extra, e($d29['tasks'][0]['text'])) && str_contains($read('lesson_today_v71_views.php'), 'lt77_day_extra_html($d)'));

// ---------------------------------------------------------------- 7) AI prompty
$fixture = require __DIR__ . '/v77_kit_prompts_fixture.php';
$fxBad = [];
foreach (LM71_CLASSES as $c) for ($n = 1; $n <= LM71_LESSONS; $n++) {
    if (substr(hash('sha256', (string)json_encode(aip77_original_prompts($c, $n), JSON_UNESCAPED_UNICODE)), 0, 16) !== (string)($fixture[$c][$n] ?? '')) $fxBad[] = $c . ' L' . $n;
}
$check('AI prompty původních lekcí za běhu = otisky vyřazených lesson kitů u všech 112 lekcí (žák bez schválení vidí stejné prompty)' . ($fxBad ? ' [' . implode(', ', array_slice($fxBad, 0, 5)) . ']' : ''), $fxBad === []);
$promptBad = [];
foreach ($rawOv as $c => $rows) foreach ($rows as $n => $ov) {
    $p = aip721_prompts($c, (int)$n, true);
    $joined = implode("\n", array_map(static fn(array $x): string => $x['title'] . "\n" . $x['text'], $p));
    if ($p !== aip76_prompts($c, $ov) || count($p) !== 6 || !str_contains($joined, trim((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', (string)$ov['title'])))) $promptBad[] = $c . ' L' . $n . ' tvar';
    if (preg_match('~https?://|@|<|EDUCANET~i', $joined) === 1) $promptBad[] = $c . ' L' . $n . ' odkaz';
    foreach ((array)($ov['exit_ticket']['variants'] ?? []) as $v) if (str_contains($joined, (string)$v['explanation'])) $promptBad[] = $c . ' L' . $n . ' prozrazuje odpověď';
    if (aip76_prompts_for($c, (int)$n) !== null) $promptBad[] = $c . ' L' . $n . ' žák před schválením';   // aip721_prompts má cache za požadavek – volá se až po schválení níže
}
$scope = array_map('aip76_lesson_in_scope', range(1, 28));
$check('AI prompty L1–L4 (styl v72.1): učitel v náhledu 6 promptů z návrhu, žák do schválení prompty původní lekce (1.A L1 = otisk kitu); z obsahu jen L1–L4 a L17–L28'
    . ($promptBad ? ' [' . implode(', ', array_slice($promptBad, 0, 5)) . ']' : ''), $promptBad === [] && array_keys(array_filter($scope)) === array_merge(range(0, 3), range(16, 27))
    && aip721_prompts('class_1a', 1) === aip77_original_prompts('class_1a', 1));
lc72_decide('lc72_approve', 'class_2a', 3, lc72_overlay_hash(lc72_overlay('class_2a', 3)), '', 'Audit Učitel');
$student = aip721_prompts('class_2a', 3);
$block = audit_capture(static fn() => aip721_render_student_block('class_2a', 3));
$check('AI prompty po schválení L3 2.A: žák dostane prompty ze schváleného obsahu (5 bodů upozornění na soukromí, 6 promptů, bez odkazů a skriptů)',
    $student === aip76_prompts('class_2a', (array)lc72_student_overlay('class_2a', 3)) && str_contains($student[0]['title'], 'Úvodní sekce webu') && substr_count($block, '<li>') === 5
    && substr_count($block, '<pre class="lx72-prompt"') === 6 && preg_match('/<script|<a\s|<form|on[a-z]+=/i', $block) !== 1);

// ---------------------------------------------------------------- 8) úklid, Středa T-14, Opakování, kontrast, dokumentace
$retired = [];
foreach (LM71_CLASSES as $c) for ($n = 1; $n <= 28; $n++) $retired[] = 'materials/lesson_kits/' . $c . '/lesson_' . str_pad((string)$n, 2, '0', STR_PAD_LEFT) . '.md';
$retired = array_merge($retired, ['materials/lesson_kits/LESSON_KITS_INDEX.csv', 'materials/lesson_kits/README.md', 'tools/generate_v42_lesson_kits.php', 'tools/v72_1_ai_prompts_build.php', 'tools/lib/ai_prompts_v72_1_build.php']);
$left = array_values(array_filter($retired, static fn(string $f): bool => file_exists($ROOT . '/' . $f)));
$refs = [];
$codeFiles = array_merge(glob($ROOT . '/*.php') ?: [], glob($ROOT . '/app/*.php') ?: [], glob($ROOT . '/app/*/*.php') ?: [], glob($ROOT . '/tools/*.php') ?: [], glob($ROOT . '/tools/lib/*.php') ?: []);
foreach ($codeFiles as $f) {
    foreach (token_get_all((string)file_get_contents($f)) as $tok) {
        if (!is_array($tok) || !in_array($tok[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) continue;
        if (preg_match('~lesson_kits/|generate_v42_lesson_kits|v72_1_ai_prompts_build|ai_prompts_v72_1_build|LESSON_KITS_INDEX~', $tok[1]) === 1 && !str_ends_with($f, 'v77_content_part2.php')) $refs[] = basename($f);
    }
}
$check('úklid: 117 vyřazených souborů (112 šablonových lesson kitů, index, README a 3 nástroje) neexistuje a žádný řetězec v kódu (mimo komentáře) na ně neodkazuje'
    . ($left || $refs ? ' [' . implode(', ', array_slice(array_merge($left, array_unique($refs)), 0, 5)) . ']' : ''), count($retired) === 117 && $left === [] && $refs === [] && !is_dir($ROOT . '/materials/lesson_kits'));
$plan = t1477_plan(['class_3a'], '2026-10-13');
$rows = array_column($plan['classes'][0]['rows'], 'bucket', 'lesson');
$check('Středa T-14 (3.A, 13. 10. 2026): L5 a L6 po termínu, L7 za 8–14 dní, okno 28 dní; po schválení L5 počet po termínu 2 → 1; odkaz do Hromadného schválení s rozsahem t14',
    $rows === [5 => 'po_terminu', 6 => 'po_terminu', 7 => 'pristi_tyden'] && t1477_overdue_count('2026-10-13', ['class_3a']) === 2
    && (lc72_decide('lc72_approve', 'class_3a', 5, lc72_overlay_hash(lc72_overlay('class_3a', 5)), '', 'Audit') !== [] && t1477_overdue_count('2026-10-13', ['class_3a', 'class_4a']) === 3)
    && str_contains(t1477_bulk_url('class_3a'), 'rozsah=t14') && isset(BA74_SCOPES['t14']));
$rv1 = array_column(rv75_build('class_1a', $GLOBALS['modules']['class_1a'], 1, 4)['tasks'], 'task');
$rv2 = array_column(rv75_build('class_2a', $GLOBALS['modules']['class_2a'], 10, 16)['tasks'], 'task');
$check('Opakování před písemkou: grafické úlohy 1.A/2.A mají témata v RV75_TASK_TOPICS a balíček je nabízí (1.A L1–L4: ' . implode(', ', $rv1) . '; 2.A L10–L16: ' . implode(', ', $rv2) . ')',
    array_diff(array_keys(TB76_TASKS), array_keys(RV75_TASK_TOPICS)) === [] && in_array('gfx_color', $rv1, true) && in_array('img_dpi', $rv1, true) && in_array('css_spec', $rv2, true));
$lum = static function (string $hex): float {
    $c = array_map(static fn(string $h): float => hexdec($h) / 255, str_split(ltrim($hex, '#'), 2));
    $c = array_map(static fn(float $v): float => $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $c);
    return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
};
$ratio = static fn(string $a, string $b): float => (max($lum($a), $lum($b)) + 0.05) / (min($lum($a), $lum($b)) + 0.05);
$css = $read('assets/student-v55.css');
$light = $read('assets/tokens-v61.css');
$dark = $read('assets/tokens-dark-v67.css');
$tok = static fn(string $src, string $name): string => preg_match('/--' . preg_quote($name, '/') . ':\s*(#[0-9a-fA-F]{6})/', $src, $m) === 1 ? $m[1] : '#000000';
$rLight = $ratio($tok($light, 'ui-muted'), $tok($light, 'ui-surface') === '#000000' ? '#ffffff' : $tok($light, 'ui-surface'));
$rDark = $ratio($tok($dark, 'ui-muted'), $tok($dark, 'ui-surface'));
// v80.2: písmo popisku LVL a nápovědy XP 8 / 10,9 px → 14 px (tools/v80_2_font_floor.php; změna limitu, BUILD_MANIFEST_V80_2.md)
$check(sprintf('kontrast hlavičky žáka: popisek LVL a nápověda XP z tokenu --ui-muted na --ui-surface – světlý %.2f : 1, tmavý %.2f : 1 (≥ 4,5; dřív ≈ 3,07)', $rLight, $rDark),
    $rLight >= 4.5 && $rDark >= 4.5 && str_contains($css, '.v55-lvl-ring b { font-size: .875rem; letter-spacing: .08em; color: var(--ui-muted') && str_contains($css, '.v55-lvl-meta small { font-size: .875rem; color: var(--ui-muted')
    && str_contains($css, 'background: var(--ui-surface, #fff); text-decoration: none;') && !preg_match('/\.v55-lvl[^{]*\{[^}]*--u-faint/', $css));
$check('dokumentace: CHANGELOG_V77.md, BUILD_MANIFEST_V77.md, docs/NAVOD_V77.md a sekce v77 v INSTALL.md', is_file($ROOT . '/CHANGELOG_V77.md') && is_file($ROOT . '/BUILD_MANIFEST_V77.md')
    && is_file($ROOT . '/docs/NAVOD_V77.md') && str_contains($read('INSTALL.md'), '## v77'));
