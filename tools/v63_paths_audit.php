<?php

declare(strict_types=1);

/**
 * EDUCANET v63 · behaviorální audit vrstvy „výukové cesty a moderní metody“.
 *   php tools/v63_paths_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte ani nezapisuje ostrou storage/. Fiktivní žáci „Audit …“.
 * Kontroly: struktura cest a obsahu (≥ 3 varianty, otázky v bance, kompetence v katalogu), determinismus variant,
 * hodnocení (Parsons/retrieval/predikce/kontrast WCAG), předpočítané výstupy = simulátor, skutečný tok žáka
 * (zámky kroků, důkazy test/lesson bez volného textu, limit 3 ověření za den, jiná varianta při opakování), třída mimo
 * pilot nic nezapíše, reflexe a soukromí (věta mimo důkazy, trychtýř a cockpit), opakování 1/3/7/14/30, doporučení
 * „Co dál“ (jen čtení, bez synchronizace, ≤ 2 čtení), přiřazení a trychtýř, politiky učitele (deny-by-default),
 * Parsons bez JS, retence 30 dní, router, i18n, token-sken invariantu labu a velikost assetů.
 * Konec: V63_PATHS_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v63-paths')), '/');
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'mastery_v62.php', 'ops_v58.php', 'app/lib.php',
    'paths_v63.php', 'paths_v63_flow.php', 'paths_v63_class.php', 'paths_v63_actions.php', 'paths_v63_views.php', 'paths_v63_teacher_views.php', 'teacher_v58.php', 'nav_v61.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v63_pre.php';
require_once __DIR__ . '/lib/v63_paths_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));

$NOW = 1_790_000_000;
$day = 86400;
$C3 = 'class_3a';
$C1 = 'class_1a';
v63fx_seed($tmp);
$snapshot = static function () use ($tmp): array {
    $out = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, FilesystemIterator::SKIP_DOTS)) as $f) {
        if (str_ends_with($f->getFilename(), '.lock')) continue;
        $out[substr(str_replace('\\', '/', $f->getPathname()), strlen($tmp) + 1)] = hash_file('sha256', $f->getPathname());
    }
    return $out;
};

// --- 1) Struktura cest a obsahu ---------------------------------------------------------------------------
$paths = p63_paths();
$check('katalog: cesty jsou 4 (2× 3.A, 2× 1.A) a patří třídám s obsahem', array_keys($paths) === ['lnx_chmod', 'net_dns', 'web_html', 'gfx_contrast']
    && array_keys(p63_paths_for_class($C3)) === ['lnx_chmod', 'net_dns'] && array_keys(p63_paths_for_class($C1)) === ['web_html', 'gfx_contrast'] && p63_paths_for_class('class_2a') === []
    && p63_enabled_for_class($C3) && p63_enabled_for_class($C1) && !p63_enabled_for_class('class_2a') && !p63_enabled_for_class('class_4a'));
$problems = [];
$typesSeen = [];
foreach ($paths as $pid => $p) {
    $subject = (string)comp62_subject_for_class((string)$p['class']);
    $comps = comp62_competencies($subject);
    $ids = p63_step_ids($p);
    if ($ids[0] !== 'explain' || end($ids) !== 'reflect' || !in_array('verify', $ids, true) || count($ids) !== count(array_unique($ids))) $problems[] = $pid . ': pořadí kroků';
    if (!isset($comps[$p['competency']]) || (int)$p['minutes'] < 10 || trim((string)$p['goal']) === '') $problems[] = $pid . ': kompetence/cíl/čas';
    foreach ((array)$p['steps'] as $s) {
        $typesSeen[(string)$s['type']] = true;
        if (!in_array($s['type'], P63_STEP_TYPES, true) || preg_match(P63_STEP_RE, (string)$s['id']) !== 1 || !isset($comps[$s['competency']]) || (int)$s['level'] < 1 || (int)$s['level'] > 4 || (int)$s['minutes'] < 1) $problems[] = $pid . '/' . $s['id'] . ': typ/kompetence/úroveň';
    }
}
$check('katalog: každá cesta začíná vysvětlením, končí reflexí, má ověření; každý krok má typ, kompetenci z katalogu třídy, úroveň 1–4 a čas' . ($problems ? ' (' . implode('; ', $problems) . ')' : ''), $problems === [] && array_diff(P63_STEP_TYPES, array_keys($typesSeen)) === []);
$qProblems = [];
$variantCounts = [];
foreach ($paths as $pid => $p) {
    $verify = (array)p63_step($p, 'verify');
    $variants = array_values((array)$verify['variants']);
    $variantCounts[] = count($variants);
    $flat = array_merge(...array_map('array_values', $variants));
    if (count($variants) < 3 || count($flat) !== count(array_unique($flat))) $qProblems[] = $pid . ': varianty ověření';
    foreach ($variants as $v) if (count($v) < 4) $qProblems[] = $pid . ': méně než 4 otázky ve variantě';
    $pools = [(array)p63_step($p, 'recall')['pool'], (array)$p['spaced']['pool']];
    foreach (array_merge($flat, $pools[0], $pools[1]) as $qid) {
        $q = p63_question((string)$qid);
        if ($q === null || !in_array($p['competency'], P63_BANK_COMPETENCY[$q['category']] ?? [], true)) $qProblems[] = $pid . ': otázka ' . $qid . ' chybí v bance, má nepodporovaný typ nebo jinou kompetenci';
    }
    if (count($pools[0]) < 8 || count($pools[1]) < 8 || count($pools[0]) < (int)p63_step($p, 'recall')['count']) $qProblems[] = $pid . ': malý pool';
}
$check('obsah: ověření má ≥ 3 navzájem různé varianty po ≥ 4 otázkách z banky, otázky (i z poolů) existují, jsou single/bool/numeric a patří kategorii kompetence' . ($qProblems ? ' (' . implode('; ', array_slice($qProblems, 0, 4)) . ')' : ''), $qProblems === [] && min($variantCounts) >= 3);
$parsonsProblems = [];
foreach ($paths as $pid => $p) {
    $step = (array)p63_step($p, 'order');
    $n = count((array)$step['lines']);
    if ($n < 4 || count(array_unique($step['lines'])) !== $n) $parsonsProblems[] = $pid . ': řádky';
    foreach ((array)$step['accept'] as $alt) if (!p63_valid_order((array)$alt, $n) || $alt === range(0, $n - 1)) $parsonsProblems[] = $pid . ': alternativa';
    foreach ((array)(p63_step($p, 'predict')['cases'] ?? []) as $case) {
        if (count((array)$case['options']) < 2 || !isset($case['options'][$case['correct']]) || trim((string)$case['explain']) === '') $parsonsProblems[] = $pid . ': případ ' . $case['id'];
    }
}
$check('obsah: Parsonsovy úlohy mají ≥ 4 různé řádky a platné alternativy, případy predikce platnou správnou volbu a vysvětlení' . ($parsonsProblems ? ' (' . implode('; ', $parsonsProblems) . ')' : ''), $parsonsProblems === []);
$check('metody: každá třída má cestu s retrieval, parsons, predict–run–explain i opakováním (spaced); 4 metody naplno, každá s ukázkovou cestou pro 1.A i 3.A',
    array_reduce([$C3, $C1], static function (bool $ok, string $c): bool {
        $types = [];
        foreach (p63_paths_for_class($c) as $p) foreach ((array)$p['steps'] as $s) $types[(string)$s['type']] = true;
        $spaced = array_filter(p63_paths_for_class($c), static fn(array $p): bool => p63_step($p, 'spaced') !== null);
        return $ok && isset($types['retrieval'], $types['parsons'], $types['pre'], $types['verify']) && count($spaced) === count(p63_paths_for_class($c));
    }, true));
$wcagOk = true;
foreach (p63_step(p63_path('gfx_contrast'), 'predict')['cases'] as $case) {
    $ratio = audit_contrast_ratio((string)$case['fg'], (string)$case['bg']);
    $claimed = (float)str_replace(',', '.', (string)$case['ratio']);
    if ($ratio === null || abs(round($ratio, 2) - $claimed) > 0.011 || ($ratio >= 4.5) !== (bool)$case['passes'] || (int)$case['correct'] !== ($case['passes'] ? 0 : 1)) $wcagOk = false;
}
$check('obsah gfx: poměry kontrastu a výsledek splní/nesplní v krocích predikce odpovídají výpočtu WCAG (audit_contrast_ratio)', $wcagOk);

// --- 2) Varianty: determinismus a „jiné zadání při opakování“ ------------------------------------------------
$sameAll = true;
$differs = true;
$inRange = true;
for ($i = 0; $i < 1000; $i++) {
    $sid = 'stu_' . substr(sha1('v63-' . $i), 0, 16);
    $pid = ['lnx_chmod', 'net_dns', 'web_html', 'gfx_contrast'][$i % 4] . '|verify';
    $n = 3 + $i % 3;
    $attempt = 1 + $i % 7;
    $v1 = p63_pick_variant($sid, $pid, $attempt, $n);
    $v2 = p63_pick_variant($sid, $pid, $attempt + 1, $n, $v1);
    if ($v1 !== p63_pick_variant($sid, $pid, $attempt, $n) || $v1 < 0 || $v1 >= $n || $v2 < 0 || $v2 >= $n) { $sameAll = false; $inRange = false; }
    if ($v1 === $v2) $differs = false;
}
$check('varianta: stejný vstup = stejná varianta a v rozsahu (1000 kombinací; hexdec(substr(sha1(sid|cesta|pokus),0,8)) % n)', $sameAll && $inRange && p63_pick_variant('stu_0123456789abcdef', 'p', 1, 1) === 0);
$check('varianta: pokus n+1 nikdy nedostane variantu pokusu n (1000 kombinací, při shodě +1)', $differs);
$seen = [];
$lastVariant = -1;
for ($a = 1; $a <= 8; $a++) { $lastVariant = p63_pick_variant('stu_0123456789abcdef', 'lnx_chmod|verify', $a, 3, $lastVariant); $seen[$lastVariant] = true; }
$check('varianta: v řadě pokusů se objeví všechny 3 varianty ověření (ne jen jedna)', count($seen) === 3);
$sh1 = p63_seeded_shuffle(range(0, 9), 'seed-a');
$check('zamíchání: deterministické podle semínka, různá semínka = jiné pořadí, nic se neztratí', $sh1 === p63_seeded_shuffle(range(0, 9), 'seed-a') && $sh1 !== p63_seeded_shuffle(range(0, 9), 'seed-b') && count(array_unique($sh1)) === 10);

// --- 3) Hodnocení (čisté funkce) --------------------------------------------------------------------------------
$check('Parsons: přesné pořadí 1.0, jakákoli jiná permutace < 1.0 (LCS/délka), prohozená dvojice = 0.8 u 5 řádků, obrácené pořadí nízké', p63_grade_parsons([0, 1, 2, 3, 4], [], [0, 1, 2, 3, 4]) === 1.0
    && p63_grade_parsons([0, 1, 2, 3, 4], [], [0, 2, 1, 3, 4]) === 0.8 && p63_grade_parsons([0, 1, 2, 3, 4], [], [4, 3, 2, 1, 0]) <= 0.2 && p63_grade_parsons([0, 1, 2], [], [1, 0, 2]) < 1.0);
$check('Parsons: akceptovaná alternativa dá 1.0 (title/meta v HTML kostře), nesprávná permutace ne; neplatné pořadí se odmítne', p63_grade_parsons(range(0, 11), [[0, 1, 2, 4, 3, 5, 6, 7, 8, 9, 10, 11]], [0, 1, 2, 4, 3, 5, 6, 7, 8, 9, 10, 11]) === 1.0
    && p63_grade_parsons(range(0, 11), [[0, 1, 2, 4, 3, 5, 6, 7, 8, 9, 10, 11]], [1, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]) < 1.0 && !p63_valid_order([0, 1, 1], 3) && !p63_valid_order([0, 1], 3) && p63_valid_order([2, 0, 1], 3) && !p63_valid_order([0, 1, 3], 3));
$qids = (array)p63_step_question_ids(p63_path('lnx_chmod'), (array)p63_step(p63_path('lnx_chmod'), 'verify'), 'stu_0123456789abcdef', p63_state_normalize([]), 1);
$allRight = p63_grade_questions($qids, v63fx_answers($qids, true));
$half = p63_grade_questions($qids, v63fx_answers($qids, true, 2));
$none = p63_grade_questions($qids, v63fx_answers($qids, false));
$check('retrieval/verify: samé správné odpovědi 1.0, polovina 0.5, samé špatné 0.0, prázdné/neplatné 0.0 (číslo, ano/ne a text volby)', $allRight['score'] === 1.0 && $half['score'] === 0.5 && $none['score'] === 0.0
    && p63_grade_questions($qids, [])['score'] === 0.0 && p63_grade_questions($qids, array_fill(0, 4, ['x']))['score'] === 0.0 && p63_grade_retrieval($qids, v63fx_answers($qids, true))['score'] === 1.0);
$q011 = (array)p63_question('net.files.011');
$check('číselná otázka: čárka i tečka jako desetinný oddělovač, tolerance, jiné číslo ne', p63_check_answer($q011, p63_normalize_answer($q011, '6')) && p63_check_answer($q011, p63_normalize_answer($q011, '6,0')) && !p63_check_answer($q011, p63_normalize_answer($q011, '7')) && p63_normalize_answer($q011, 'abc') === null);
$case = (array)p63_step(p63_path('lnx_chmod'), 'predict')['cases'][0];
$check('predikce: správná volba 1.0, špatná 0.0, mimo nabídku / text / záporné číslo = null (volba jen z nabídky)', p63_grade_pre($case, 0) === 1.0 && p63_grade_pre($case, '0') === 1.0 && p63_grade_pre($case, 1) === 0.0
    && p63_grade_pre($case, 9) === null && p63_grade_pre($case, -1) === null && p63_grade_pre($case, 'a') === null && p63_grade_pre($case, null) === null);
$check('aktuální úroveň z ověření: 0.25 → 1, 0.5 → 2, 0.75 → 3, 1.0 → 4', p63_actual_level(0.25) === 1 && p63_actual_level(0.5) === 2 && p63_actual_level(0.75) === 3 && p63_actual_level(1.0) === 4);

// --- 4) Předpočítané výstupy = simulátor ----------------------------------------------------------------------
$fresh = v63_pre_all();
$data = (array)require $root . '/paths_v63_pre_data.php';
$check('predict–run–explain: paths_v63_pre_data.php přesně odpovídá výstupu simulátoru lab57_run_line (' . count($fresh) . ' případů, pevné semínko i čas)', $data['outputs'] === $fresh && $fresh !== [] && v63_pre_render($fresh) === (string)file_get_contents($root . '/paths_v63_pre_data.php'));
$preOk = true;
foreach (p63_paths() as $pid => $p) {
    foreach ((array)$p['steps'] as $s) {
        if ($s['type'] !== 'pre') continue;
        foreach ((array)$s['cases'] as $c) {
            if (!empty($c['static'])) continue;
            $out = (string)p63_pre_output((string)$pid, (string)$s['id'], (string)$c['id']);
            $opt = (string)$c['options'][$c['correct']];
            if ($out === '' || !str_contains($out, (string)$c['expect']) || !(str_contains($opt, (string)$c['expect']) || str_contains((string)$c['expect'], $opt))) $preOk = false;
        }
    }
}
$check('predict–run–explain: výstup každého případu obsahuje očekávaný řetězec a správná volba s ním souhlasí; výstup je deterministický (dvakrát stejný)', $preOk && v63_pre_compute($case) === v63_pre_compute($case));

// --- 5) Skutečný tok žáka 3.A: zámky, důkazy, varianty ------------------------------------------------------------
$before = $snapshot();
$keyA = v63fx_key($C3, 'a');
$sidA = v63fx_sid($C3, 'a');
$check('fixture: fiktivní žáci mají student_id v identitě', ev62_valid_id($sidA) && ev62_valid_id(v63fx_sid($C3, 'b')) && ev62_valid_id(v63fx_sid($C1, 'a')) && $sidA !== v63fx_sid($C3, 'b'));
$locked = p63_submit_step($C3, $keyA, 'lnx_chmod', 'verify', ['a' => ['x']], $NOW);
$check('tok: krok ověření je zamčený, dokud nejsou hotové předchozí kroky (error=locked, nic se nezapíše)', !$locked['ok'] && $locked['error'] === 'locked' && !is_file(p63_state_path($sidA)));
$check('tok: neznámá cesta/krok a cesta cizí třídy se odmítnou (unknown_step)', p63_submit_step($C3, $keyA, 'neexistuje', 'explain', [], $NOW)['error'] === 'unknown_step' && p63_submit_step($C3, $keyA, 'web_html', 'explain', [], $NOW)['error'] === 'unknown_step'
    && p63_submit_step($C3, $keyA, 'lnx_chmod', '../../x', [], $NOW)['error'] === 'unknown_step' && p63_submit_step($C3, 'class_3a:student:nikdo', 'lnx_chmod', 'explain', [], $NOW)['error'] === 'identity');
$res = v63fx_complete_steps($C3, 'a', 'lnx_chmod', '', $NOW);
$allOk = $res !== [] && array_reduce($res, static fn(bool $ok, array $r): bool => $ok && $r['ok'] && $r['passed'], true);
$st = p63_state($sidA);
$check('tok: správné splnění všech kroků (explain, predikce, Parsons, vybavování, ověření) uloží stav done a postup 5 z 6', $allOk && count($res) === 5 && p63_path_progress(p63_path('lnx_chmod'), $st)['done'] === 5 && p63_path_progress(p63_path('lnx_chmod'), $st)['next'] === 'reflect');
$rows = ev62_read($sidA);
$verifyRow = array_values(array_filter($rows, static fn(array $r): bool => $r['source'] === 'test'));
$lessonRows = array_values(array_filter($rows, static fn(array $r): bool => $r['source'] === 'lesson'));
$check('důkazy: verify = zdroj test (ref p63:<cesta>:verify:v<n>, skóre 1.0, kompetence lnx_users úroveň 2); predikce, Parsons i vybavování = zdroj lesson s ref p63:<cesta>:<krok>:a<pokus>; explain nic',
    count($verifyRow) === 1 && preg_match('/^p63:lnx_chmod:verify:v[1-3]$/', $verifyRow[0]['artefact_ref']) === 1 && $verifyRow[0]['competency'] === 'lnx_users' && $verifyRow[0]['level'] === 2 && abs((float)$verifyRow[0]['score'] - 1.0) < 1e-9
    && count($lessonRows) === 3 && array_reduce($lessonRows, static fn(bool $ok, array $r): bool => $ok && preg_match('/^p63:lnx_chmod:(predict|order|recall):a1$/', $r['artefact_ref']) === 1, true) && count($rows) === 4);
$dupProbe = ev62_append($sidA, [array_intersect_key($verifyRow[0], array_flip(['competency', 'level', 'source', 'score', 'at', 'artefact_ref']))], $NOW);
$bad = $dupProbe['invalid'] + $dupProbe['added'] + ($dupProbe['duplicates'] === 1 ? 0 : 1);
$check('důkazy: všechny řádky prošly validací v62 (ev62_normalize), žádný volný text ani jména v souboru důkazů', $bad === 0 && !str_contains((string)file_get_contents(ev62_path($sidA)), 'Audit') && !str_contains((string)file_get_contents(ev62_path($sidA)), '@'));
$spacedAfter = $st['spaced']['lnx_users'] ?? [];
$check('po splněném ověření vznikne plán opakování: interval 1 den, splatné za 1 den od ověření', ($spacedAfter['interval_d'] ?? 0) === 1 && (int)($spacedAfter['due_at'] ?? 0) === $NOW + $day);
$r2 = p63_submit_step($C3, v63fx_key($C3, 'b'), 'lnx_chmod', 'explain', [], $NOW);
$check('tok: explain je „Přečteno“ – done bez důkazu; opakované odevzdání téhož kroku je povoleno (procvičování)', $r2['ok'] && $r2['passed'] && $r2['evidence'] === 0 && ev62_read(v63fx_sid($C3, 'b')) === [] && p63_submit_step($C3, v63fx_key($C3, 'b'), 'lnx_chmod', 'explain', [], $NOW + 5)['ok']);

// --- 6) Limit ověření 3× denně a jiná varianta při opakování -------------------------------------------------------
$keyB = v63fx_key($C3, 'b');
$sidB = v63fx_sid($C3, 'b');
$pathChmod = (array)p63_path('lnx_chmod');
v63fx_complete_steps($C3, 'b', 'lnx_chmod', 'verify', $NOW);
$verifyStep = (array)p63_step($pathChmod, 'verify');
$variantsUsed = [];
$tries = [];
for ($i = 0; $i < 4; $i++) {
    $in = v63fx_input($C3, 'b', $pathChmod, $verifyStep, false);
    $tries[] = p63_submit_step($C3, $keyB, 'lnx_chmod', 'verify', $in, $NOW + $i * 60);
    if ($tries[$i]['ok']) $variantsUsed[] = $tries[$i]['variant'];
}
$check('limit: 3 pokusy o ověření za den projdou (špatné odpovědi = neprojde, skóre 0), 4. pokus v týž den = error limit a nic se nezapíše', count(array_filter($tries, static fn(array $t): bool => $t['ok'])) === 3 && $tries[3]['error'] === 'limit'
    && array_reduce(array_slice($tries, 0, 3), static fn(bool $ok, array $t): bool => $ok && !$t['passed'] && $t['score'] === 0.0, true) && p63_step_entry(p63_state($sidB), 'lnx_chmod', 'verify')['attempts'] === 3);
$check('limit: opakované pokusy dostanou různé zadání (po sobě jdoucí varianty se liší, žádná dvakrát za sebou)', count($variantsUsed) === 3 && $variantsUsed[0] !== $variantsUsed[1] && $variantsUsed[1] !== $variantsUsed[2]);
$check('limit: zbývající pokusy se počítají (0 dnes, 3 další den), další den ověření znovu projde a splní', p63_verify_left(p63_state($sidB), 'lnx_chmod', $NOW + 300) === 0 && p63_verify_left(p63_state($sidB), 'lnx_chmod', $NOW + $day) === 3
    && p63_submit_step($C3, $keyB, 'lnx_chmod', 'verify', v63fx_input($C3, 'b', $pathChmod, $verifyStep, true), $NOW + $day)['passed']);
$evB = ev62_read($sidB);
$check('limit: neúspěšné pokusy zapíšou poctivý důkaz se skórem 0 (každý s vlastním refem varianty), úspěšný se skórem 1', count(array_filter($evB, static fn(array $r): bool => $r['source'] === 'test')) === 4
    && count(array_filter($evB, static fn(array $r): bool => $r['source'] === 'test' && abs((float)$r['score']) < 1e-9)) === 3);
$ev62UnchangedFiles = array_filter(array_keys($snapshot()), static fn(string $f): bool => !str_starts_with($f, 'paths_v63/') && !str_starts_with($f, 'evidence_v62/') && !str_starts_with($f, 'mastery_v62/') && !str_starts_with($f, 'identity') && !str_starts_with($f, 'intake/') && !str_starts_with($f, 'linux_v57/') && $f !== 'schema_v58.json.php');
$changedOutside = array_filter($ev62UnchangedFiles, static fn(string $f): bool => ($before[$f] ?? '') !== ($snapshot()[$f] ?? ''));
$check('XP a body: celý tok cest nezměnil žádný soubor mimo paths_v63/, evidence_v62/ a mastery_v62/ (žádné XP, body ani jiné učení)' . ($changedOutside ? ' (' . implode(',', array_slice($changedOutside, 0, 4)) . ')' : ''), $changedOutside === []);

// --- 7) Třída mimo pilot / bez cest -------------------------------------------------------------------------------
$beforeEv = glob($tmp . '/evidence_v62/*') ?: [];
$resOut = p63_submit_step('class_2a', 'class_2a:student:x', 'lnx_chmod', 'explain', [], $NOW);
$stepSome = (array)p63_step($pathChmod, 'verify');
$check('třída mimo katalog cest (2.A, 4.A): odevzdání se odmítne a p63_next vrací null', !$resOut['ok'] && p63_submit_step('class_4a', 'class_4a:student:x', 'net_dns', 'explain', [], $NOW)['error'] === 'unknown_step' && p63_next('class_2a', 'x') === null);
$check('třída mimo pilot kompetencí nezapisuje důkazy: p63_writes_evidence(2.A/4.A) = false, p63_record_evidence vrátí 0 a nevytvoří soubor; 3.A i 1.A zapisují',
    !p63_writes_evidence('class_2a') && !p63_writes_evidence('class_4a') && p63_writes_evidence($C3) && p63_writes_evidence($C1)
    && p63_record_evidence('class_2a', 'stu_aaaaaaaaaaaaaaaa', $pathChmod, $stepSome, ['score' => 1.0, 'variant' => 0], 1, $NOW) === 0 && !is_file(ev62_path('stu_aaaaaaaaaaaaaaaa')) && (glob($tmp . '/evidence_v62/*') ?: []) === $beforeEv);

// --- 8) Cesty 1.A zapisují důkazy (rozhodnutí školy) --------------------------------------------------------------
$res1 = v63fx_complete_steps($C1, 'a', 'gfx_contrast', '', $NOW);
$sid1 = v63fx_sid($C1, 'a');
$ev1 = ev62_read($sid1);
$check('1.A: cesta gfx_contrast se dá projít a zapíše důkazy do kompetence gfx_color_contrast (test i lesson), hodnoty v katalogu grafika_web', $res1 !== [] && array_reduce($res1, static fn(bool $ok, array $r): bool => $ok && $r['ok'], true)
    && count($ev1) === 4 && array_unique(array_column($ev1, 'competency')) === ['gfx_color_contrast'] && in_array('test', array_column($ev1, 'source'), true) && isset(comp62_competencies('grafika_web')['gfx_color_contrast']));
$mapA = m62_student($C1, v63fx_key($C1, 'a'), $NOW)['map'];
$check('1.A: mapa zvládnutí (m62_student) vidí důkaz z cesty – kompetence gfx_color_contrast je rozpracovaná/zvládnutá, ostatní neověřené', in_array($mapA['gfx_color_contrast']['state'], ['rozpracovano', 'zvladnuto', 'upevneno'], true) && $mapA['web_html_structure']['state'] === 'neovereno');

// --- 9) Reflexe, kalibrace, soukromí věty -----------------------------------------------------------------------------
$note = 'Chmod mi šel dobře. <script>alert(1)</script> Zopakuji si <b>umask</b>. ' . str_repeat('x', 300);
$cleanNote = p63_clean_note($note);
$check('reflexe: věta bez značek, max 200 znaků, bílé znaky sloučeny', mb_strlen($cleanNote) === 200 && !str_contains($cleanNote, '<') && !str_contains($cleanNote, '>') && p63_clean_note("a \n\t  b") === 'a b' && p63_clean_note('') === '');
$check('reflexe: sebehodnocení mimo 1–4 se odmítne; bez splněného ověření (verify_first) také; neznámá cesta také', p63_save_reflection($C3, $keyA, 'lnx_chmod', 0, 'x', $NOW)['error'] === 'bad_self' && p63_save_reflection($C3, $keyA, 'lnx_chmod', 5, 'x', $NOW)['error'] === 'bad_self'
    && p63_save_reflection($C3, v63fx_key($C3, 'c'), 'lnx_chmod', 3, 'x', $NOW)['error'] === 'verify_first' && p63_save_reflection($C3, $keyA, 'nic', 3, 'x', $NOW)['error'] === 'unknown_step');
$ref = p63_save_reflection($C3, $keyA, 'lnx_chmod', 4, $note, $NOW);
$stA = p63_state($sidA);
$check('reflexe: uloží self 1–4, mastery_at_time z ověření (1.0 → 4), delta 0, větu ≤ 200 znaků a označí krok reflect jako done', $ref['ok'] && $ref['self'] === 4 && $ref['actual'] === 4 && $ref['delta'] === 0 && ($stA['reflect']['lnx_chmod']['self'] ?? 0) === 4
    && ($stA['reflect']['lnx_chmod']['mastery_at_time'] ?? 0) === 4 && mb_strlen((string)$stA['reflect']['lnx_chmod']['note']) === 200 && p63_step_done($stA, 'lnx_chmod', 'reflect') && p63_path_progress($pathChmod, $stA)['finished']);
$rawEvidence = (string)file_get_contents(ev62_path($sidA));
$check('soukromí věty: reflexní věta není v souboru důkazů, v cache zvládnutí ani v trychtýři; důkaz po reflexi přibyl 0 řádků', !str_contains($rawEvidence, 'Chmod mi') && !str_contains($rawEvidence, 'umask') && count(ev62_read($sidA)) === 4);
$low = p63_save_reflection($C3, $keyB, 'lnx_chmod', 4, 'ok', $NOW + $day);
$check('kalibrace: přecenění (odhad 4, výsledek ověření 4 → delta 0; u slabého žáka se spočítá kladná delta)', $low['ok'] && $low['delta'] === $low['self'] - $low['actual'] && p63_save_reflection($C3, $keyB, 'lnx_chmod', 1, 'ok', $NOW + $day + 5)['delta'] === 1 - $low['actual']);

// --- 10) Opakování 1/3/7/14/30 (čisté funkce) a doporučení „Co dál“ ---------------------------------------------------------
$sp = ['a' => ['due_at' => $NOW - 100, 'interval_d' => 3], 'b' => ['due_at' => $NOW + 100, 'interval_d' => 1], 'c' => ['due_at' => $NOW - 5000, 'interval_d' => 7], 'd' => ['due_at' => $NOW, 'interval_d' => 1], 'e' => 'rozbito'];
$check('spaced_due: splatné (due_at ≤ now) nejstarší první, budoucí a poškozené ne', p63_spaced_due($sp, $NOW) === ['c', 'a', 'd'] && p63_spaced_due([], $NOW) === [] && p63_spaced_due($sp, $NOW - 10000) === []);
$ladder = [];
$entry = null;
for ($i = 0; $i < 7; $i++) { $entry = p63_spaced_schedule($entry, true, $NOW); $ladder[] = $entry['interval_d']; }
$check('spaced: úspěšná opakování postupují 1 → 3 → 7 → 14 → 30 → 30 a due_at = now + interval dní; neúspěch vrací na 1 den', $ladder === [1, 3, 7, 14, 30, 30, 30] && p63_spaced_schedule(['interval_d' => 14], false, $NOW) === ['due_at' => $NOW + $day, 'interval_d' => 1]
    && p63_spaced_schedule(['interval_d' => 7], true, $NOW) === ['due_at' => $NOW + 14 * $day, 'interval_d' => 14] && P63_SPACED_INTERVALS === [1, 3, 7, 14, 30]);
p63_memo_reset();
$nextDue = p63_next($C3, $keyA, $NOW + 2 * $day);
$check('Co dál: splatné opakování má přednost (kind spaced, krok spaced, důvod a minuty > 0, odkaz ?view=cesta s cestou a krokem z katalogu)', is_array($nextDue) && $nextDue['kind'] === 'spaced' && $nextDue['step'] === 'spaced' && $nextDue['path'] === 'lnx_chmod'
    && $nextDue['reason'] === 'spaced' && $nextDue['minutes'] > 0 && $nextDue['href'] === '?view=cesta&path=lnx_chmod&step=spaced');
$spacedRes = p63_submit_step($C3, $keyA, 'lnx_chmod', 'spaced', ['a' => v63fx_answers((array)p63_step_question_ids($pathChmod, (array)p63_step($pathChmod, 'spaced'), $sidA, p63_state($sidA), 1), true)], $NOW + 2 * $day);
$stSp = p63_state($sidA)['spaced']['lnx_users'] ?? [];
$check('spaced: opakování po splatnosti projde, zapíše důkaz (lesson, ref p63:lnx_chmod:spaced:a1) a posune interval na 3 dny', $spacedRes['ok'] && $spacedRes['passed'] && ($stSp['interval_d'] ?? 0) === 3 && (int)$stSp['due_at'] === $NOW + 5 * $day
    && count(array_filter(ev62_read($sidA), static fn(array $r): bool => $r['artefact_ref'] === 'p63:lnx_chmod:spaced:a1' && $r['source'] === 'lesson')) === 1);
p63_memo_reset();
$check('Co dál: opakování po opakování už není splatné (3 dny), doporučí se jiná nehotová cesta nebo nic', ($n2 = p63_next($C3, $keyA, $NOW + 2 * $day)) === null || $n2['kind'] !== 'spaced');

$keyC = v63fx_key($C3, 'c');
$sidC = v63fx_sid($C3, 'c');
p63_memo_reset();
$fresh1 = p63_next($C3, $keyC, $NOW);
$check('Co dál: žák bez historie dostane doporučení s důvodem a odhadem minut (první cesta třídy, neověřená kompetence)', is_array($fresh1) && in_array($fresh1['kind'], ['weak', 'start'], true) && $fresh1['reason'] !== '' && $fresh1['minutes'] > 0 && $fresh1['step'] === 'explain' && in_array($fresh1['path'], ['lnx_chmod', 'net_dns'], true));
ev62_append($sidC, [['competency' => 'net_dns_dhcp', 'level' => 2, 'source' => 'lab', 'score' => 0.4, 'at' => date(DATE_ATOM, $NOW - $day), 'artefact_ref' => 'lab:practice:fx-weak']], $NOW);
m62_student($C3, $keyC, $NOW);
p63_memo_reset();
$weak = p63_next($C3, $keyC, $NOW);
$check('Co dál: rozpracovaná (slábnoucí) kompetence podle cache v62 vede na její cestu (kind weak, reason weak, net_dns)', is_array($weak) && $weak['kind'] === 'weak' && $weak['reason'] === 'weak' && $weak['path'] === 'net_dns' && $weak['minutes'] > 0);
p63_submit_step($C3, $keyC, 'lnx_chmod', 'explain', [], $NOW + 10);
p63_memo_reset();
$cont = p63_next($C3, $keyC, $NOW + 20);
$check('Co dál: rozpracovaná cesta má přednost před slabou kompetencí (kind continue, další krok predict)', is_array($cont) && $cont['kind'] === 'continue' && $cont['path'] === 'lnx_chmod' && $cont['step'] === 'predict');
$keyD = v63fx_key($C3, 'd');
$sidD = v63fx_sid($C3, 'd');
$teacherHash = p63_teacher_hash();
$check('přiřazení: p63_assign zapíše {třída:{cesta:{assigned_at,assigned_by_hash,open}}}, cesta cizí třídy / špatný hash se odmítnou', p63_assign($C3, 'net_dns', $teacherHash, $NOW) && !p63_assign($C3, 'web_html', $teacherHash, $NOW) && !p63_assign($C3, 'net_dns', 'x', $NOW)
    && !p63_assign('class_2a', 'net_dns', $teacherHash, $NOW) && (array)(storage_read(p63_assign_file(), false)[$C3]['net_dns'] ?? []) === ['assigned_at' => $NOW, 'assigned_by_hash' => $teacherHash, 'open' => true]
    && array_keys(p63_assignments($C3)) === ['net_dns'] && preg_match('/^[a-f0-9]{16}$/', $teacherHash) === 1);
p63_memo_reset();
$assigned = p63_next($C3, $keyD, $NOW);
$check('přiřazení: žák bez rozpracované cesty dostane přiřazenou (kind assigned, net_dns); přiřazení se zrcadlí do jeho stavu; žák smí i nepřiřazenou cestu', is_array($assigned) && $assigned['kind'] === 'assigned' && $assigned['path'] === 'net_dns' && isset(p63_state($sidD)['assigned']['net_dns'])
    && p63_submit_step($C3, $keyD, 'lnx_chmod', 'explain', [], $NOW)['ok']);
p63_unassign($C3, 'net_dns');
$check('přiřazení: p63_unassign odebere záznam i zrcadlo ve stavech žáků a nic jiného nesmaže', p63_assignments($C3) === [] && !isset(p63_state($sidD)['assigned']['net_dns']) && !p63_unassign($C3, 'web_html') && is_file(p63_state_path($sidD)));

// --- 11) p63_next jen čte ---------------------------------------------------------------------------------------------------
$snapA = $snapshot();
p63_student_id($C3, $keyA);
$callsBefore = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0);
p63_memo_reset();
for ($i = 0; $i < 50; $i++) p63_next($C3, $keyA, $NOW + 30 * $day);
$reads = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0) - $callsBefore;
$check('p63_next: nezapisuje (žádný soubor úložiště se nezměnil) a opakovaná volání v jednom požadavku jsou z paměti (50× volání, ' . $reads . ' čtení)', $snapshot() === $snapA && $reads <= 6);
$fnSrc = '';
foreach (['p63_next', 'p63_next_compute', 'p63_active_path', 'p63_weak_path', 'p63_mastery_cache', 'p63_recommend', 'p63_path_for_competency'] as $fn) {
    $rf = new ReflectionFunction($fn);
    $fnSrc .= implode('', array_slice(file((string)$rf->getFileName()), $rf->getStartLine() - 1, $rf->getEndLine() - $rf->getStartLine() + 1));
}
$check('p63_next: nevolá ev62_sync_student, m62_class ani m62_student (jen čte stav žáka a cache v62), nic nezapisuje (storage_update/append)', !str_contains($fnSrc, 'ev62_sync_student') && !str_contains($fnSrc, 'm62_class(') && !str_contains($fnSrc, 'm62_student(')
    && !str_contains($fnSrc, 'storage_update') && !str_contains($fnSrc, 'storage_append') && !str_contains($fnSrc, 'storage_write'), false);
p63_memo_reset();
$callsBefore = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0);
p63_next($C3, $keyC, $NOW + 20);
$perCall = (int)($GLOBALS['educanet_storage_stats']['calls'] ?? 0) - $callsBefore;
$check('p63_next: jedno volání nad zahřátou identitou čte nejvýš 2 soubory úložiště (stav žáka + cache v62; bylo ' . $perCall . ')', $perCall <= 2);
$cardHtml = audit_capture(static function () use ($C3, $keyC): void { p63_render_next_card($C3, $keyC); });
$check('karta Co dál: má nadpis „Co dál“, název cesty, důvod, odhad minut, tlačítko Pokračovat s odkazem na krok a aria-labelledby; bez PHP chyb; pro žáka bez identity ani třídu bez cest nic',
    str_contains($cardHtml, 'Co dál') && str_contains($cardHtml, 'aria-labelledby="p63-next-title"') && str_contains($cardHtml, 'href="?view=cesta&amp;path=lnx_chmod&amp;step=predict"') && str_contains($cardHtml, 'minut') && str_contains($cardHtml, 'Pokračovat')
    && audit_response_clean(['status' => 200, 'body' => $cardHtml]) && audit_capture(static function (): void { p63_render_next_card('class_3a', 'class_3a:student:nikdo'); }) === '' && audit_capture(static function (): void { p63_render_next_card('class_2a', 'x'); }) === '');

// --- 12+) Další sekce (třída, politiky, Parsons bez JS, retence, router, i18n, token-sken) ---------------------
require __DIR__ . '/lib/v63_paths_audit_part2.php';
require __DIR__ . '/lib/v63_paths_audit_http.php';

exit(audit_summary($state, 'V63_PATHS'));
