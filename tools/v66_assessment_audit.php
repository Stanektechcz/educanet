<?php

declare(strict_types=1);

/**
 * EDUCANET v66 · behaviorální audit vrstvy „testy, hodnocení a učitelská analytika“.
 *   php tools/v66_assessment_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte ani nezapisuje ostrou storage/. Fiktivní žáci „Audit …“.
 * Část 1 (tento soubor): metadata otázek, druhy testů, položková analýza na fixtuře 40 žáků se známými vlastnostmi (p, r, D, distraktory,
 *   „málo dat“ pod 20 pokusy), integrita (rychlost, shoda, jen štítek, soukromí).
 * Část 2 (tools/lib/v66_audit_part2.php): výchozí vypnutí a práva, vzorec a návrh hodnocení s řetězcem důkazů, formativní/herní zdroje, převzetí,
 *   ranní přehled, per-žákovské přiřazení cesty, CSV a ochrana vzorců, politiky a rozsah učitelských účtů, soukromí, token-sken.
 * Konec: V66_ASSESSMENT_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SESSION = [];
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v66-assessment')), '/');
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_accounts_v59_admin.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php',
    'mastery_v62.php', 'projects_v65.php', 'paths_v63_class.php', 'paths_v63_flow.php', 'runtime_content.php', 'tutorial_v52.php', 'session_v53.php', 'learning_v56.php', 'assessment_v66_build.php', 'grading_v66_views.php', 'competency_v62_views.php', 'teacher_v58.php',
    'assessment_v66_teacher_grading.php', 'ops_v58.php', 'app/lib.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v62_competency_fixtures.php';
require_once __DIR__ . '/lib/v66_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));
$NOW = time();
$CLASS = V66FX_CLASS;
$ACTOR = str_repeat('a', 16);
$questions = array_values((array)$GLOBALS['modules'][$CLASS]['questions']);
v62fx_seed($tmp, $NOW); // soupis 3.A (Audit Dobry, Hrac, Nikdo, Odesel), stav labu, lekce, testy a projekt; musí běžet před čtením soupisu

// --- 1) Metadata otázek -----------------------------------------------------------------------------------
$check('klíč položky: platný tvar zdroj:id (mod/tg/p63/sa), neplatný se odmítne', q66_ref_valid('mod:dns_role') && q66_ref_valid('tg:net-001') && q66_ref_valid('p63:x1') && q66_ref_valid('sa:exam_1')
    && !q66_ref_valid('xx:dns_role') && !q66_ref_valid('mod:') && !q66_ref_valid('mod:a b') && !q66_ref_valid("mod:a\nb"));
$norm = q66_normalize(['competency' => 'lnx_users', 'difficulty' => 5, 'type' => 'essay', 'variant_group' => 'g 1', 'summative' => 'yes']);
$check('normalizace metadat: neplatná pole (obtížnost 5, typ essay, skupina s mezerou, summative ne-bool) se zahodí', $norm['competency'] === 'lnx_users' && $norm['difficulty'] === null && $norm['type'] === null && $norm['variant_group'] === null && $norm['summative'] === true);
$meta = q66_meta($CLASS, 'mod:' . $questions[0]['id']);
$check('výchozí metadata otázky modulu: typ mcq, obtížnost 2 a kompetence z katalogu v62 (nebo null)', $meta['type'] === 'mcq' && $meta['difficulty'] === 2
    && ($meta['competency'] === null || isset(comp62_competencies((string)comp62_subject_for_class($CLASS))[$meta['competency']])));
$bankId = (string)array_key_first(q66_bank_rows());
$check('banka týmových her: metadata tg: z kategorie (kompetence z P63_BANK_COMPETENCY) a typ otázky', $bankId !== '' && q66_meta($CLASS, 'tg:' . $bankId)['type'] !== null);
$check('ruční přepsání metadat: platná pole se uloží, neplatná klíč nezmění, prázdná změna se odmítne', q66_set_override('mod:' . $questions[0]['id'], ['difficulty' => 3, 'variant_group' => 'skupina_a'])
    && q66_meta($CLASS, 'mod:' . $questions[0]['id'])['difficulty'] === 3 && q66_meta($CLASS, 'mod:' . $questions[0]['id'])['variant_group'] === 'skupina_a'
    && !q66_set_override('bad ref', ['difficulty' => 1]) && !q66_set_override('mod:x1', ['neznamy' => 1]));
$ids = array_map(static fn(array $q): string => (string)$q['id'], $questions);
$o1 = array_map(static fn(array $q): string => (string)$q['id'], q66_seeded_order($questions, 'seed-1'));
$o1b = array_map(static fn(array $q): string => (string)$q['id'], q66_seeded_order($questions, 'seed-1'));
$o2 = array_map(static fn(array $q): string => (string)$q['id'], q66_seeded_order($questions, 'seed-2'));
$sorted = $o1; sort($sorted); $idsSorted = $ids; sort($idsSorted);
$check('náhodné pořadí se seedem: stejný seed = stejné pořadí, jiný seed = jiné, vždy permutace všech otázek', $o1 === $o1b && $o1 !== $o2 && $sorted === $idsSorted && $o1 !== $ids);

// --- 2) Druh testu ----------------------------------------------------------------------------------------
$check('výchozí druh každého testu je formativní', a66_test_kind($CLASS, 'v56:l1') === 'formative' && a66_test_kind($CLASS, 'start') === 'formative' && a66_test_kind($CLASS, 'neznamy') === 'formative' && a66_kinds($CLASS) === []);
$check('startovní test nejde označit sumativně (zůstane formativní)', !a66_set_kind($CLASS, 'start', 'summative', $ACTOR) && a66_test_kind($CLASS, 'start') === 'formative');
$check('druh testu: neplatné id, neplatný druh i neplatný hash učitele se odmítnou a nic se nezapíše', !a66_set_kind($CLASS, 'v56:l99', 'summative', $ACTOR) && !a66_set_kind($CLASS, 'v56:l1', 'jiny', $ACTOR) && !a66_set_kind($CLASS, 'v56:l1', 'summative', 'xyz') && a66_kinds($CLASS) === []);
$check('označení sumativního testu lekce v56 se uloží a platí jen v dané třídě', a66_set_kind($CLASS, 'v56:l1', 'summative', $ACTOR) && a66_is_summative($CLASS, 'v56:l1') && !a66_is_summative('class_1a', 'v56:l1') && !a66_is_summative($CLASS, 'v56:l2'));
$markable = array_column(a66_markable_tests($CLASS), 'kind', 'id');
$check('seznam označitelných testů: lekce v56 a ověření cest v63, startovní test chybí, označený je sumativní', ($markable['v56:l1'] ?? '') === 'summative' && isset($markable['v56:l28']) && !isset($markable['start'])
    && array_filter(array_keys($markable), static fn(string $k): bool => str_starts_with($k, 'p63:')) !== []);
a66_set_kind($CLASS, 'v56:l1', 'formative', $ACTOR);
$check('zpět na formativní: označení se odstraní', !a66_is_summative($CLASS, 'v56:l1') && a66_kinds($CLASS) === []);

// --- 3) Položková analýza na fixtuře 40 žáků --------------------------------------------------------------
foreach (v66fx_rows($questions, $NOW) as $row) storage_append('practice_results', $row);
storage_append('practice_results', ['id' => 'fx66_again'] + v66fx_rows($questions, $NOW)[5]); // druhý pokus žáka 06 (opakování se v analýze nepočítá)
$allAttempts = a66_attempts($CLASS);
$attempts = array_values(array_filter($allAttempts, static fn(array $a): bool => str_starts_with((string)$a['sid'], 'anon_'))); // fixtura 40 žáků mimo soupis; žáci soupisu (Audit Dobry …) mají své vlastní pokusy z fixtury v62
$startAttempts = array_values(array_filter($attempts, static fn(array $a): bool => $a['test'] === 'start'));
$check('normalizace pokusů: 41 pokusů startovního testu (40 žáků + 1 opakování), každý s 11 položkami, dobou a klíčem mod:<id>', count($startAttempts) === 41
    && count($startAttempts[0]['items']) === 11 && str_starts_with((string)$startAttempts[0]['items'][0]['item'], 'mod:') && is_int($startAttempts[0]['dur']));
$check('první pokusy: opakování téhož žáka se do analýzy nepočítá (40 pokusů)', count(a66_first_attempts($startAttempts)) === 40);
$stats = a66_item_stats_by_test($attempts, a66_item_options($CLASS));
$items = $stats['start']['items'];
$ref = static fn(string $letter): string => 'mod:' . $questions[V66FX_ITEMS[$letter]]['id'];
$expect = v66fx_expected(array_slice(v66fx_rows($questions, $NOW), 0, 40));
$check('analýza startovního testu: n = 40 a všech 11 položek má stav ok', $stats['start']['n'] === 40 && count($items) === 11 && count(array_filter($items, static fn(array $s): bool => $s['status'] === 'ok')) === 11);
$A = $items[$ref('A')];
$check('položka A: obtížnost p = 0,9 (±0,02) a není označená jako snadná (p > 0,9 je podmínka)', abs($A['p'] - 0.9) <= 0.02 && !in_array('easy', $A['flags'], true));
$B = $items[$ref('B')];
$check('položka B: korigovaná r_pb > 0,5 (silně rozlišuje), D (horních/dolních 27 %) ≥ 0,9, bez příznaku slabé citlivosti', $B['r_pb'] > 0.5 && $B['d'] >= 0.9 && !in_array('low_discrimination', $B['flags'], true));
$C = $items[$ref('C')];
$check('položka C: |r_pb| < 0,1 a příznak „slabě rozlišuje“', abs((float)$C['r_pb']) < 0.1 && in_array('low_discrimination', $C['flags'], true));
$optionsOf = static fn(string $letter): array => v66fx_options($questions[V66FX_ITEMS[$letter]]);
$D = $items[$ref('D')];
$dDist = array_column($D['distractors'], null, 'key');
$neverKey = $optionsOf('D')['wrong'][2];
$check('položka D: distraktor, který nikdo nezvolil (0×), je označený jako slabý; používané distraktory ne', $dDist[$neverKey]['share'] === 0.0 && $dDist[$neverKey]['weak'] === true
    && !$dDist[$optionsOf('D')['wrong'][0]]['weak'] && !$dDist[$optionsOf('D')['wrong'][1]]['weak'] && in_array('weak_distractor', $D['flags'], true));
$E = $items[$ref('E')];
$eDist = array_column($E['distractors'], null, 'key');
$posKey = $optionsOf('E')['wrong'][1];
$check('položka E: špatná možnost s kladnou korelací s výsledkem (volí ji nejlepší žáci) je slabý distraktor, běžný distraktor ne', $eDist[$posKey]['r'] > 0 && $eDist[$posKey]['weak'] === true && $eDist[$optionsOf('E')['wrong'][0]]['r'] < 0 && !$eDist[$optionsOf('E')['wrong'][0]]['weak']);
$maxDiff = 0.0;
foreach ($items as $id => $s) {
    $e = $expect[substr((string)$id, 4)] ?? null;
    if ($e === null) { $maxDiff = 9.0; continue; }
    $maxDiff = max($maxDiff, abs($s['p'] - $e['p']), $e['r'] === null || $s['r_pb'] === null ? 0.0 : abs($s['r_pb'] - $e['r']));
}
$check('všech 11 položek: p a r_pb odpovídají nezávislému výpočtu (kovariance) v toleranci ±0,02 (největší odchylka ' . round($maxDiff, 4) . ')', $maxDiff <= 0.02);
$hard = a66_item_stats(array_map(static fn(array $a): array => ['items' => [['item' => 'x', 'ok' => false, 'sel' => 'a']] + $a['items']], array_slice($startAttempts, 0, 25)), []);
$check('těžká položka: p < 0,3 dostane příznak „hard“ (všech 25 žáků špatně → p = 0)', $hard['x']['p'] === 0.0 && in_array('hard', $hard['x']['flags'], true));
$few = a66_item_stats(array_slice(a66_first_attempts($startAttempts), 0, 19), a66_item_options($CLASS));
$check('pod 20 pokusů je každá položka jen „málo dat“ (bez p, r a D)', count($few) === 11 && count(array_filter($few, static fn(array $s): bool => $s['status'] === 'low_data' && !isset($s['p']) && !isset($s['r_pb']) && $s['n'] === 19)) === 11);
$exact20 = a66_item_stats(array_slice(a66_first_attempts($startAttempts), 0, 20), a66_item_options($CLASS));
$check('přesně 20 pokusů už se hodnotí', count(array_filter($exact20, static fn(array $s): bool => $s['status'] === 'ok')) === 11);
$check('čistá funkce: Pearson při nulovém rozptylu vrací null, perfektní korelace 1,0', a66_pearson([1, 1, 1], [1, 2, 3]) === null && abs((float)a66_pearson([0, 1, 0, 1], [1, 5, 2, 6]) - 0.9701) < 0.001 && a66_pearson([1], [1]) === null);
$built = a66_items_build($CLASS, $NOW, $attempts);
$cache = a66_items_cache($CLASS);
$cacheText = (string)file_get_contents(a66_items_path($CLASS));
$check('cache analýzy: zapsaná i přečtená, test „start“ je formativní, n = 40, položky nesou kompetenci a obtížnost, žádná jména ani student_id',
    ($cache['tests']['start']['kind'] ?? '') === 'formative' && ($cache['tests']['start']['n'] ?? 0) === 40 && array_key_exists('competency', $cache['tests']['start']['items'][$ref('A')]) && !str_contains($cacheText, 'Audit') && !str_contains($cacheText, 'stu_') && $built['built_at'] === $NOW);

// --- 4) Integrita -----------------------------------------------------------------------------------------
$sets = ['x|a', 'y|b', 'z|c', 'w|d', 'v|a'];
$mk = static fn(array $wrong, ?int $dur = null, string $sid = 's'): array => ['test' => 't', 'src' => 'practice', 'sid' => $sid, 'at' => 100, 'dur' => $dur, 'score' => 0.1,
    'items' => array_map(static fn(string $w): array => ['item' => explode('|', $w)[0], 'ok' => false, 'sel' => explode('|', $w)[1]], $wrong)];
$check('rychlost: 10 otázek za 20 s je „rychlé“ (< 5 s na otázku), za 60 s ne, neznámá doba ne, bez položek ne', i66_is_fast($mk($sets + [5 => 'q|a', 6 => 'r|a', 7 => 's|a', 8 => 't|a', 9 => 'u|a'], 20))
    && !i66_is_fast($mk($sets + [5 => 'q|a', 6 => 'r|a', 7 => 's|a', 8 => 't|a', 9 => 'u|a'], 60)) && !i66_is_fast($mk($sets, null)) && !i66_is_fast($mk([], 1)));
$check('Jaccard: shoda množin 4 z 5 = 0,8, disjunktní = 0, prázdné = 0', abs(i66_jaccard(['a', 'b', 'c', 'd'], ['a', 'b', 'c', 'd', 'e']) - 0.8) < 1e-9 && i66_jaccard(['a'], ['b']) === 0.0 && i66_jaccard([], []) === 0.0);
$same1 = $mk($sets, null, 's1'); $same2 = $mk($sets, null, 's2'); $diff = $mk(['k|a', 'l|b', 'm|c', 'n|d', 'o|a'], null, 's3'); $tiny1 = $mk(['x|a', 'y|b'], null, 's4'); $tiny2 = $mk(['x|a', 'y|b'], null, 's5');
$flags = i66_scan([$same1, $same2, $diff, $tiny1, $tiny2]);
$flagged = array_column($flags, 'sid_hash');
$check('shoda špatných odpovědí: dvojice s Jaccardem ≥ 0,8 a ≥ 4 špatnými je označena (obě), odlišný žák a dvojice s méně než 4 špatnými ne',
    in_array(i66_hash('s1'), $flagged, true) && in_array(i66_hash('s2'), $flagged, true) && !in_array(i66_hash('s3'), $flagged, true) && !in_array(i66_hash('s4'), $flagged, true) && count($flags) === 2);
$before = json_encode([$same1, $same2, $diff]);
i66_scan([$same1, $same2, $diff]);
$check('integrita je jen štítek: sken nemění pokusy ani skóre a štítek nenese skóre', json_encode([$same1, $same2, $diff]) === $before && array_diff(array_keys($flags[0]), ['kind', 'test', 'sid_hash', 'at']) === []);
$scan = i66_scan($attempts);
$speed = array_filter($scan, static fn(array $f): bool => $f['kind'] === 'speed');
$check('fixtura 40 žáků: přesně 3 štítky rychlosti (žáci odevzdaní za 30 s při 11 otázkách), žádný pro 330 s', count($speed) === 3);
$wrongSets = [];
foreach (a66_first_attempts($startAttempts) as $a) $wrongSets[(string)$a['sid']] = i66_wrong_set($a);
$expectMatch = [];
$sidList = array_keys($wrongSets);
foreach ($sidList as $i => $s1) foreach (array_slice($sidList, $i + 1) as $s2) {
    if (count($wrongSets[$s1]) >= I66_MIN_WRONG && count($wrongSets[$s2]) >= I66_MIN_WRONG && i66_jaccard($wrongSets[$s1], $wrongSets[$s2]) >= I66_JACCARD_MIN) { $expectMatch[i66_hash($s1)] = true; $expectMatch[i66_hash($s2)] = true; }
}
$gotMatch = array_flip(array_column(array_filter($scan, static fn(array $f): bool => $f['kind'] === 'match'), 'sid_hash'));
ksort($expectMatch); ksort($gotMatch);
$check('fixtura 40 žáků: štítky shody odpovídají nezávislému výpočtu párů (' . count($expectMatch) . ' žáků), nejlepší a nejhorší žák spolu shodní nejsou', array_keys($expectMatch) === array_keys($gotMatch) && $expectMatch !== [] && !isset($gotMatch[i66_hash((string)$sidList[39])]));
$stored = i66_store($CLASS, $scan);
$flagText = (string)file_get_contents(i66_path());
$check('uložené štítky: jen zkrácený hash, typ, test a čas (žádná jména, student_id ani skóre), čtení vrátí totéž, opakované uložení je idempotentní', $stored === count($scan) && !str_contains($flagText, 'Audit') && !str_contains($flagText, 'stu_') && !str_contains($flagText, 'anon_')
    && count(i66_flags($CLASS)) === $stored && i66_store($CLASS, $scan) === $stored && count(i66_flags($CLASS)) === $stored);
$rowsAfter = a66_items_build($CLASS, $NOW, $attempts)['tests']['start']['items'][$ref('A')];
$check('štítky integrity nemění analýzu ani skóre: p položky A je po uložení štítků stejné', abs($rowsAfter['p'] - $A['p']) < 1e-9);
$ret = i66_retention_purge(true, $NOW);
$future = i66_retention_purge(true, i66_school_year_start($NOW) + 400 * 86400);
$check('retence štítků: v běžném školním roce se nic nemaže, po jeho skončení by se smazaly všechny (dry-run nic nemění)', $ret['purge'] === 0 && $future['purge'] === $stored && count(i66_flags($CLASS)) === $stored);
$done = i66_retention_purge(false, i66_school_year_start($NOW) + 400 * 86400);
$check('retence štítků: ostrý běh po skončení školního roku štítky smaže', $done['purge'] === $stored && i66_flags($CLASS) === []);
i66_store($CLASS, $scan);

require __DIR__ . '/lib/v66_audit_part2.php';
exit(audit_summary($state, 'V66_ASSESSMENT'));
