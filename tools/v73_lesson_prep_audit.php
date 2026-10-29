<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v73 · audit přípravného balíčku hodiny: Tisk (`hodina_tisk`), Projektor (`hodina_projektor`), Zástup (`hodina_zastup`).
 *   1) soubory vrstvy (strict_types, guard, ≤ 800 řádků, PHP 8.1), registr (jen čtení: bez post/get, žádná vlastní GET politika), mapa,
 *      tmavý režim, „Související“, Režim hodiny zůstává (vyřadí se až ve v76), rozpočet CSS+JS ≤ 20 kB, projektor písmo ≥ 28 px, tokeny,
 *   2) čisté funkce: aktuální úsek podle hodin, plán B / pokyny, kontakt z konfigurace (ne osobní telefon z dat), otázky exit ticketu bez
 *      možností a odpovědí, zadání bez očekávaných výstupů, popisky žáků podle volby jmen,
 *   3) HTTP: tři záložky vracejí 200 (čistá odpověď), tisk má A4 strany a @media print, projektor bez odpovědí a bez jmen, zástup bez jmen
 *      s kontaktem z konfigurace; po skrytí jmen tisk ukazuje „Žák N“; v režimu účtů cizí třída 403, asistent smí číst.
 *   php tools/v73_lesson_prep_audit.php     Dočasné úložiště i cache modelu. Konec: V73_LESSON_PREP_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v73-prep')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V73P_KEY = 'v73-audit-teacher-key';
const V73P_CONTACT = 'Kancelář zástupce ředitele pro zástupy, vnitřní linka 100';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V73P_KEY);
putenv('EDUCANET_SUBSTITUTION_CONTACT');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php',
    'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php', 'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
$tabs = ['hodina_tisk', 'hodina_projektor', 'hodina_zastup'];
foreach ($tabs as $t) foreach ((array)$mods[$t]['files'] as $f) require_once $ROOT . '/' . $f;
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory, registr, rozpočet
$libs = ['src/lessons/lesson_prep_v73.php', 'src/lessons/lesson_print_v73_views.php', 'src/lessons/lesson_projector_v73_views.php'];
$cli = ['tools/v73_lesson_prep_audit.php'];
$bad = [];
foreach (array_merge($libs, $cli) as $f) {
    $s = $read($f);
    $guard = in_array($f, $cli, true) ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if (!str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800 || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory v73: strict_types, guard (knihovny SCRIPT_FILENAME, CLI PHP_SAPI), ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$map = teacher68_tab_map();
$regBad = [];
foreach ($tabs as $t) {
    $m = $mods[$t] ?? [];
    if (isset($m['post']) || isset($m['get']) || !in_array($t, teacher58_tabs(), true) || ($map[$t]['section'] ?? '') !== 'dnes' || !in_array($t, UI68_TEACHER_DARK_TABS, true)
        || !in_array('src/lessons/lesson_prep_v73.php', (array)$m['files'], true) || !isset(c70_related_rules()[$t])) $regBad[] = $t;
}
$getPolicies = array_filter(array_keys(teacher59_get_policies()), static fn(string $k): bool => preg_match('/^hodina_(tisk|projektor|zastup)\|/', $k) === 1);
$check('registr: tisk, projektor a zástup jsou jen pro čtení (bez post/get), v sekci Dnes, v UI68_TEACHER_DARK_TABS, se „Souvisejícím“; žádná vlastní GET politika' . ($regBad ? ' [' . implode(', ', $regBad) . ']' : ''),
    $regBad === [] && $getPolicies === [] && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get']));
$check('Režim hodiny (`teach`) zůstává v mapě i v navigaci (vyřadí se až ve v76); projektor je jeho nástupce', isset($map['teach']) && array_column(c70_related_rules()['hodina_projektor'], 0) === ['hodina', 'teach']);
$assets = ['assets/lesson-print-v73.css', 'assets/lesson-projector-v73.css', 'assets/lesson-v73.js', 'assets/cockpit-v73.css'];
$size = array_sum(array_map(static fn(string $f): int => strlen($read($f)), $assets));
$check('rozpočet fáze: CSS+JS v73 = ' . $size . ' B ≤ 20 kB, JS jen defer a bez innerHTML/fetch/odesílání dat', $size <= 20480 && !preg_match('/innerHTML|fetch\(|XMLHttpRequest|sendBeacon|localStorage/', $read('assets/lesson-v73.js'))
    && ($mods['hodina_projektor']['js'] ?? []) === ['assets/lesson-v73.js'] && str_contains($read('src/teacher/teacher_v58.php'), "'js' => ['assets/lesson-v73.js']"));
$literal = '~#[0-9a-fA-F]{3,8}\b|\brgba?\s*\(|\bhsla?\s*\(|(?<![-\w.#%])(?:white|black)(?![-\w(])~';
$printCss = $read('assets/lesson-print-v73.css');
$screenPart = (string)preg_replace('~@media print\s*\{.*\}\s*$~s', '', (string)preg_replace('~/\*.*?\*/~s', '', $printCss));
$check('CSS: projektor, reflexe a obrazovková část tisku jen tokeny (pevné barvy jen v @media print – papír), tisk má @page A4 a skrývá rámec cockpitu',
    preg_match($literal, (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/lesson-projector-v73.css'))) !== 1 && preg_match($literal, (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/cockpit-v73.css'))) !== 1
    && preg_match($literal, $screenPart) !== 1 && str_contains($printCss, '@page { size: A4;') && preg_match('~@media print\s*\{[^@]*\.t68-side~s', $printCss) === 1 && str_contains($printCss, 'break-after: page'));
$pj = $read('assets/lesson-projector-v73.css');
$check('projektor: základní písmo 1.75rem (28 px), nadpisy, časovač a odkazy ≥ 28 px, cíle dotyku ≥ 44 px, viditelný fokus, bez JS úseky přes :target',
    str_contains($pj, '.pj73 { font-size: 1.75rem;') && preg_match_all('/font-size:\s*(\d+(?:\.\d+)?)rem/', $pj, $fs) > 0 && min(array_map('floatval', $fs[1])) >= 1.75
    && str_contains($pj, 'min-height: 44px') && str_contains($pj, ':focus-visible') && str_contains($pj, '.pj73-seg:target { display: block; }'));

// ---------------------------------------------------------------- 2) čisté funkce
$tl = [['from' => 0, 'to' => 10, 'phase' => 'Úvod'], ['from' => 10, 'to' => 40, 'phase' => 'Práce'], ['from' => 40, 'to' => 90, 'phase' => 'Projekt']];
$sch = ['start' => '08:00', 'end' => '09:30', 'room' => '12'];
$at = static fn(string $hm): int => (int)strtotime('2026-10-12 ' . $hm);
$c1 = lp73_current_segment($tl, $sch, true, '2026-10-12', $at('08:12'));
$c2 = lp73_current_segment($tl, $sch, true, '2026-10-12', $at('07:50'));
$c3 = lp73_current_segment($tl, $sch, true, '2026-10-12', $at('09:31'));
$c4 = lp73_current_segment($tl, $sch, false, '2026-10-12', $at('08:12'));
$check('aktuální úsek: v 8:12 běží úsek 2 (končí 8:40, zbývá 28 min); před začátkem, po konci a mimo den výuky stojí na úseku 1',
    $c1['index'] === 1 && $c1['running'] && $c1['ends_at'] === $at('08:40') && $c1['remaining'] === 28 * 60 && !$c2['running'] && $c2['index'] === 0 && !$c3['running'] && !$c4['running']);
[$planB, $other] = lp73_split_substitution(['Zástup bez odborníka: žáci pracují v Labu podle úkolů 2–4.', 'Plán B offline: zkracování adres na papír.']);
$check('zástup: pokyny a plán B se rozdělí (bez úvodních štítků, velké počáteční písmeno); název lekce bez zdvojeného „Lekce N ·“', $planB === ['Zkracování adres na papír.'] && $other === ['Žáci pracují v Labu podle úkolů 2–4.'] && lp73_title(['title' => 'Lekce 5 · IPv6']) === 'IPv6');
$defaultContact = lp73_substitution_contact();
putenv('EDUCANET_SUBSTITUTION_CONTACT=' . V73P_CONTACT . "\x07");
$envContact = lp73_substitution_contact();
putenv('EDUCANET_SUBSTITUTION_CONTACT=' . str_repeat('x', 400));
$longContact = lp73_substitution_contact();
putenv('EDUCANET_SUBSTITUTION_CONTACT');
$check('kontakt na koordinaci: z konfigurace školy (bez řídicích znaků, max ' . LP73_CONTACT_MAX . ' znaků), bez nastavení obecná role – nikdy data žáků ani učitelů',
    $defaultContact === LP73_CONTACT_DEFAULT && $envContact === V73P_CONTACT && mb_strlen($longContact) === LP73_CONTACT_MAX + 1 && !str_contains($read('src/lessons/lesson_prep_v73.php'), 'phone'));
$ov5 = (array)(lm71_raw('class_3a')['overlay']['lessons'][5] ?? []);
$model5 = lm71_lesson('class_3a', 5);
$q = lp73_exit_questions($model5);
$variants = (array)($ov5['exit_ticket']['variants'] ?? []);
$secret = [];
foreach ($variants as $v) {
    $secret[] = (string)$v['explanation'];
    foreach ((array)$v['options'] as $o) $secret[] = (string)$o;
}
$outputs = array_values(array_filter(array_map(static fn($t): string => is_array($t) ? (string)($t['output'] ?? '') : '', (array)($ov5['tasks'] ?? [])), static fn(string $o): bool => mb_strlen($o) >= 12));
$taskText = implode("\n", lp73_task_texts($model5, 20));
$check('exit ticket a zadání: jen text otázek (' . count($q) . ' variant), žádné možnosti ani vysvětlení; zadání bez očekávaných výstupů (' . count($outputs) . ' výstupů hlídáno)',
    count($q) >= 3 && $q === array_map(static fn(array $v): string => (string)$v['question'], $variants) && array_filter($secret, static fn(string $s): bool => in_array($s, $q, true)) === []
    && array_filter($outputs, static fn(string $o): bool => str_contains($taskText, $o)) === []);
$roster = array_values(array_filter(array_map(static fn(array $s): string => (string)$s['label'], project_students_for_class('class_3a')), static fn(string $l): bool => mb_strlen($l) >= 5));
$shown = lp73_roster_labels('class_3a', false);
$hidden = lp73_roster_labels('class_3a', true);
$check('kontrolní list: jména podle volby učitele – zobrazená (výchozí), při skrytí „Žák 1 … Žák N“ [žáků 3.A: ' . count($shown) . ']',
    count($shown) === count($hidden) && ($hidden === [] || ($hidden[0] === 'Žák 1' && end($hidden) === 'Žák ' . count($hidden) && array_intersect($roster, $hidden) === [])) && !tp721_names_hidden());
$codeHtml = audit_capture(static function (): void { echo pj73_code_html(['class' => 'class_3a', 'session' => ['exists' => true, 'open' => true, 'code' => 'K7Q2M9', 'joined' => 3, 'submitted' => 1]]); });
$noCode = audit_capture(static function (): void { echo pj73_code_html(['class' => 'class_3a', 'session' => ['exists' => false, 'open' => false, 'code' => '', 'joined' => 0, 'submitted' => 0]]); });
$check('projektor: otevřený kód hodiny velkým písmem s počty (bez jmen); bez kódu výzva k otevření v Dnešní hodině',
    str_contains($codeHtml, '<p class="pj73-codebig">K7Q2M9</p>') && str_contains($codeHtml, '3 pracuje · 1 odevzdalo') && str_contains($noCode, 'Kód hodiny teď není otevřený') && !str_contains($noCode, 'pj73-codebig'));
$segHtml = pj73_segment_html(['from' => 10, 'to' => 40, 'phase' => 'Práce', 'student' => 'Řeší úkoly 1–3.', 'teacher' => ''], 1, 3, $c1, '?tab=hodina_projektor#pj73-s1');
$check('projektor bez JS: úsek je kotva s aria, běžící úsek má čas konce a „Obnovit“, s JS odpočet (data-ends)',
    str_contains($segHtml, 'id="pj73-s1" class="pj73-seg is-current"') && str_contains($segHtml, 'Úsek končí v 08:40') && str_contains($segHtml, 'data-ends="' . $at('08:40') . '"') && str_contains($segHtml, '>Obnovit</a>'));

require __DIR__ . '/lib/v73_prep_http.php';
exit(audit_summary($state, 'V73_LESSON_PREP'));
