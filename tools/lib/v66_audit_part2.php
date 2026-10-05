<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v66 · část 2 auditu (načítá ji tools/v66_assessment_audit.php, sdílí její proměnné $check, $tmp, $CLASS, $NOW, $ACTOR, $questions).
 * Návrh hodnocení, formativní/herní zdroje, převzetí, ranní přehled, per-žákovské přiřazení, CSV, politiky a rozsah, soukromí, token-sken.
 */

$good = v62fx_key('good');
$player = v62fx_key('player');
$none = v62fx_key('none');
$left = v62fx_key('left');
$goodId = (string)ev62_student_context($CLASS, $good)['id'];
$playerId = (string)ev62_student_context($CLASS, $player)['id'];
$noneId = (string)ev62_student_context($CLASS, $none)['id'];
$leftId = (string)ev62_student_context($CLASS, $left)['id'];
$jsonOf = static fn(string $file): string => is_file(STORAGE_DIR . '/' . $file) ? hash_file('sha256', STORAGE_DIR . '/' . $file) : 'missing';

// --- 5) Výchozí vypnutí, práva, vzorec --------------------------------------------------------------------
$check('návrh hodnocení je výchozí VYPNUTÝ v každé třídě a g66_proposal vrací null', !g66_enabled($CLASS) && !g66_enabled('class_1a') && g66_proposal($CLASS, $good) === null && g66_settings($CLASS)['weights'] === G66_DEFAULT_WEIGHTS && g66_settings($CLASS)['thresholds'] === G66_DEFAULT_THRESHOLDS);
$check('výchozí rozhodnutí školy: váhy 40 / 40 / 20, hranice 90 / 75 / 50 / 30, zdroje přesně summative_test, mastery, project_v65',
    G66_DEFAULT_WEIGHTS === ['summative_test' => 40, 'mastery' => 40, 'project_v65' => 20] && G66_DEFAULT_THRESHOLDS === [90, 75, 50, 30] && g66_sources() === ['summative_test', 'mastery', 'project_v65']);
$onInput = ['enabled' => true, 'weights' => G66_DEFAULT_WEIGHTS, 'thresholds' => G66_DEFAULT_THRESHOLDS];
$denied = g66_settings_save($CLASS, $onInput, $ACTOR, false);
$check('zapnutí návrhu smí jen administrátor: ne-admin je odmítnut (admin_only) a nastavení se nezmění', !$denied['ok'] && $denied['error'] === 'admin_only' && !g66_enabled($CLASS) && !is_file(g66_settings_path()));
$bad1 = g66_settings_save($CLASS, ['weights' => ['summative_test' => 50, 'mastery' => 40, 'project_v65' => 20]] + $onInput, $ACTOR, true);
$bad2 = g66_settings_save($CLASS, ['weights' => ['summative_test' => 40, 'mastery' => 40, 'game' => 20]] + $onInput, $ACTOR, true);
$bad3 = g66_settings_save($CLASS, ['weights' => ['summative_test' => 40, 'mastery' => 40, 'project_v65' => 10, 'arena' => 10]] + $onInput, $ACTOR, true);
$bad4 = g66_settings_save($CLASS, ['thresholds' => [90, 90, 50, 30]] + $onInput, $ACTOR, true);
$bad5 = g66_settings_save($CLASS, ['thresholds' => [90, 75, 50]] + $onInput, $ACTOR, true);
$bad6 = g66_settings_save('class_neexistuje', $onInput, $ACTOR, true);
$bad7 = g66_settings_save($CLASS, $onInput, 'neplatny', true);
$check('váhy a hranice: součet ≠ 100, cizí zdroj (hra, aréna), neklesající hranice, chybějící hranice, neznámá třída i neplatný hash učitele se odmítnou celé',
    $bad1['error'] === 'weights' && $bad2['error'] === 'weights' && $bad3['error'] === 'weights' && $bad4['error'] === 'thresholds' && $bad5['error'] === 'thresholds' && $bad6['error'] === 'class' && $bad7['error'] === 'class' && !is_file(g66_settings_path()));
$check('převod procent na známku: 90 → 1, 89,99 → 2, 75 → 2, 74,9 → 3, 50 → 3, 49,9 → 4, 30 → 4, 29,9 → 5, 0 → 5', array_map(static fn(float $p): int => g66_grade_for_percent($p, G66_DEFAULT_THRESHOLDS), [100, 90, 89.99, 75, 74.9, 50, 49.9, 30, 29.9, 0]) === [1, 1, 2, 2, 3, 3, 4, 4, 5, 5]);
$check('vážený průměr: všechny části 40/40/20; chybějící část přepočte váhy; bez dat null; nulová váha část vypne',
    g66_combine(['summative_test' => 100, 'mastery' => 50, 'project_v65' => 0], G66_DEFAULT_WEIGHTS) === 60.0 && abs((float)g66_combine(['summative_test' => 100, 'mastery' => null, 'project_v65' => 50], G66_DEFAULT_WEIGHTS) - 83.33) < 0.01
    && g66_combine(['summative_test' => null, 'mastery' => null, 'project_v65' => null], G66_DEFAULT_WEIGHTS) === null && g66_combine(['summative_test' => 80, 'mastery' => 20, 'project_v65' => null], ['summative_test' => 100, 'mastery' => 0, 'project_v65' => 0]) === 80.0);
$h1 = g66_formula_hash(g66_formula($CLASS));
$ok = g66_settings_save($CLASS, $onInput, $ACTOR, true, $NOW);
$check('administrátor návrh po třídě zapne: třída 3.A je zapnutá, 1.A zůstává vypnutá, hash vzorce má 16 hex znaků a změna vah ho změní', $ok['ok'] && g66_enabled($CLASS) && !g66_enabled('class_1a') && preg_match('/^[a-f0-9]{16}$/', $h1) === 1
    && g66_formula_hash(['v' => G66_VERSION, 'weights' => ['summative_test' => 30, 'mastery' => 50, 'project_v65' => 20], 'thresholds' => G66_DEFAULT_THRESHOLDS]) !== $h1);

// --- 6) Návrh hodnocení s řetězcem důkazů -----------------------------------------------------------------
foreach ([$good, $player, $none, $left] as $key) ev62_sync_student($CLASS, $key, true, false, $NOW);
$none66 = g66_proposal($CLASS, $none, $NOW);
$check('žák bez důkazů: stav insufficient, bez známky a bez řetězce (návrh se nevymýšlí)', $none66 !== null && $none66['status'] === 'insufficient' && $none66['grade'] === null && $none66['chain'] === []);
a66_set_kind($CLASS, 'v56:l1', 'summative', $ACTOR, $NOW);
$p = g66_proposal($CLASS, $good, $NOW);
$check('žák Dobrý: návrh má známku 1–5, slovní hodnocení, procenta, hash vzorce a hash návrhu', $p !== null && $p['status'] === 'ok' && $p['grade'] >= 1 && $p['grade'] <= 5 && $p['verbal'] !== '' && $p['percent'] !== null && preg_match('/^[a-f0-9]{16}$/', $p['proposal_hash']) === 1 && $p['formula_hash'] === g66_formula_hash(g66_formula($CLASS)));
$check('návrh Dobrý: všechny tři zdroje mají podklady (sumativní test lekce 1 = 67 %, projekt 80 %, kompetence ≥ 3)', $p['parts']['summative_test']['pct'] === 67.0 && $p['parts']['project_v65']['pct'] === 80.0 && $p['parts']['mastery']['pct'] !== null && $p['parts']['mastery']['n'] >= G66_MIN_MASTERY_COMPETENCIES);
$check('návrh = vážený průměr částí podle vah třídy a známka odpovídá hranicím', abs($p['percent'] - (float)g66_combine(array_map(static fn(array $x): ?float => $x['pct'], $p['parts']), $p['weights'])) < 0.01 && $p['grade'] === g66_grade_for_percent((float)$p['percent'], G66_DEFAULT_THRESHOLDS));
$evidenceRefs = array_column(ev62_read($goodId), 'artefact_ref');
$chainOk = $p['chain'] !== [];
$evidenceSeen = 0;
foreach ($p['chain'] as $c) {
    $chainOk = $chainOk && isset($c['source'], $c['ref'], $c['label'], $c['score']) && in_array($c['source'], G66_SOURCES, true);
    foreach ($c['evidence'] as $e) { $evidenceSeen++; $chainOk = $chainOk && in_array($e['ref'], $evidenceRefs, true) && in_array($e['source'], G66_MASTERY_SOURCES, true); }
}
$progress = storage_read(STORAGE_DIR . '/progress_v56.json.php')[$CLASS . '|' . $good] ?? [];
$projectIds = array_column(project_grade_records_for_class($CLASS), 'id');
$chainRefsExist = true;
foreach ($p['chain'] as $c) {
    if ($c['source'] === 'summative_test') $chainRefsExist = $chainRefsExist && isset($progress['1']['test']) && $c['ref'] === 'v56:l1';
    if ($c['source'] === 'project_v65') $chainRefsExist = $chainRefsExist && in_array(substr($c['ref'], 5), $projectIds, true);
    if ($c['source'] === 'mastery') $chainRefsExist = $chainRefsExist && isset(comp62_competencies((string)comp62_subject_for_class($CLASS))[substr($c['ref'], 5)]);
}
$check('každý návrh má neprázdný řetězec důkazů a každý jeho důkaz existuje (artefact_ref v důkazech žáka, test v postupu lekce, hodnocení projektu, kompetence v katalogu); důkazů ' . $evidenceSeen, $chainOk && $chainRefsExist && $evidenceSeen > 0);
$gameRows = [];
foreach (array_slice(array_keys(comp62_competencies((string)comp62_subject_for_class($CLASS))), 0, 6) as $i => $competency) {
    foreach (['game', 'arena', 'lesson'] as $j => $source) $gameRows[] = ['competency' => $competency, 'level' => 2, 'source' => $source, 'score' => 1.0, 'at' => date(DATE_ATOM, $NOW - 3600), 'artefact_ref' => 'fx66:' . $source . ':' . $i . $j];
}
$beforeMastery = g66_part_mastery($CLASS, $goodId, $NOW);
$added = ev62_append($goodId, $gameRows)['added'];
$afterMastery = g66_part_mastery($CLASS, $goodId, $NOW);
$check('hra, aréna ani lekce do návrhu neprojdou: 18 nových důkazů typu game/arena/lesson se skóre 1,0 nezmění zvládnutí ani řetězec (' . $added . ' přidáno)', $added === 18 && $beforeMastery === $afterMastery
    && g66_proposal($CLASS, $good, $NOW)['proposal_hash'] === $p['proposal_hash']);
$onlyGames = [];
foreach ($gameRows as $r) $onlyGames[] = $r + ['student_id' => $playerId];
$playerPart = g66_part_mastery($CLASS, $playerId, $NOW);
$check('žák Hráč (jen hry a aréna) nemá z kompetencí žádný podklad (část null) a jeho návrh z her nevznikne', $playerPart['pct'] === null && g66_proposal($CLASS, $player, $NOW)['parts']['mastery']['pct'] === null);
$projectsOnlyHash = $p['proposal_hash'];
storage_append('practice_results', ['id' => 'fx66_form', 'class_id' => $CLASS, 'student_label' => V62FX_LABELS['good'], 'student_email' => '', 'auth_key' => '', 'google_sub' => '', 'started_at' => date(DATE_ATOM, $NOW - 900), 'finished_at' => date(DATE_ATOM, $NOW - 600),
    'score' => 0, 'max_score' => 2, 'answers' => [['question_id' => $questions[0]['id'], 'selected' => 'a', 'correct' => false], ['question_id' => $questions[1]['id'], 'selected' => 'a', 'correct' => false]]]);
storage_update(STORAGE_DIR . '/progress_v56.json.php', static function (array $all) use ($good, $CLASS, $NOW): array {
    $all[$CLASS . '|' . $good]['2'] = ['theory' => [], 'test' => ['at' => date(DATE_ATOM, $NOW - 100), 'percent' => 0, 'score' => 0, 'max' => 5, 'detail' => [['id' => 'dns_role', 'given' => 'a', 'correct' => 'b', 'ok' => false]]]];
    return $all;
}, );
$check('formativní test návrh nemění: nový slabý pokus startovního testu i test lekce 2 (nesumativní) nezmění hash návrhu ani sumativní část', g66_proposal($CLASS, $good, $NOW)['proposal_hash'] === $projectsOnlyHash && g66_part_summative($CLASS, $good, $goodId)['n'] === 1);
a66_set_kind($CLASS, 'v56:l2', 'summative', $ACTOR, $NOW);
$p2 = g66_proposal($CLASS, $good, $NOW);
$check('označení testu lekce 2 jako sumativního návrh změní (zahrne se, teď 2 testy: průměr 67 % a 0 %)', $p2['proposal_hash'] !== $projectsOnlyHash && $p2['parts']['summative_test']['n'] === 2 && abs((float)$p2['parts']['summative_test']['pct'] - 33.5) < 0.01);
a66_set_kind($CLASS, 'v56:l2', 'formative', $ACTOR, $NOW);
$check('vrácení testu na formativní obnoví původní návrh', g66_proposal($CLASS, $good, $NOW)['proposal_hash'] === $projectsOnlyHash);
$off = g66_proposal('class_1a', v62fx_key('good'), $NOW);
$check('třída se zapnutým návrhem nemění jiné třídy: 1.A zůstává bez návrhu', $off === null);

// --- 7) Převzetí učitelem ---------------------------------------------------------------------------------
$stale = g66_accept($CLASS, $good, str_repeat('0', 16), $ACTOR, $NOW);
$noEvidence = g66_accept($CLASS, $none, (string)$none66['proposal_hash'], $ACTOR, $NOW);
$badActor = g66_accept($CLASS, $good, $projectsOnlyHash, 'nehash', $NOW);
$check('převzetí: zastaralý hash, žák bez podkladů a neplatný hash učitele se odmítnou a nic se nezapíše', $stale['error'] === 'stale' && $noEvidence['error'] === 'stale' && $badActor['error'] === 'actor' && g66_accepted($CLASS, $goodId) === null && !is_file(g66_accepted_path()));
$studentHtml = audit_capture(static function () use ($CLASS, $good): void { $GLOBALS['edu_locale_override'] = 'cs'; g66v_render($CLASS, $good); });
$gradeShown = static fn(string $html): bool => str_contains($html, 'class="a66-grade"');
$check('pohled žáka před převzetím: obsahuje řetězec důkazů a rozpis zdrojů, neobsahuje známku ani návrh známky', str_contains($studentHtml, 'a66-chain') && str_contains($studentHtml, 'Sumativní testy') && !$gradeShown($studentHtml) && str_contains($studentHtml, 'Učitel zatím žádnou známku nepřevzal'));
$acc = g66_accept($CLASS, $good, $projectsOnlyHash, $ACTOR, $NOW + 5);
$accepted = g66_accepted($CLASS, $goodId);
$check('převzetí platného návrhu: uloží známku, procenta a hash vzorce beze změny', $acc['ok'] && $accepted !== null && $accepted['grade'] === $p['grade'] && abs($accepted['percent'] - $p['percent']) < 0.001 && $accepted['formula_hash'] === $p['formula_hash']);
$log = g66_accept_log($CLASS, $goodId);
$acceptedText = (string)file_get_contents(g66_accepted_path());
$check('auditní stopa převzetí: čas, známka, hash vzorce a hash učitele; v souboru žádné jméno žáka ani učitele', count($log) === 1 && $log[0]['formula_hash'] === $p['formula_hash'] && $log[0]['actor_hash'] === $ACTOR && !str_contains($acceptedText, 'Audit') && !str_contains($acceptedText, 'Dobry'));
$studentAfter = audit_capture(static function () use ($CLASS, $good): void { g66v_render($CLASS, $good); });
$check('pohled žáka po převzetí: ukáže známku a slovní hodnocení; jiný žák (Hráč) známku nevidí', $gradeShown($studentAfter) && str_contains($studentAfter, '>' . $p['grade'] . '<') && !$gradeShown(audit_capture(static function () use ($CLASS, $player): void { g66v_render($CLASS, $player); })));
$check('pohled žáka neobsahuje jména spolužáků ani štítky integrity', !str_contains($studentAfter, 'Audit Hrac') && !str_contains($studentAfter, 'Audit Nikdo') && !str_contains($studentAfter, 'Audit Odesel') && !str_contains($studentAfter, 'k ověření') && !str_contains($studentAfter, 'rychl'));
g66_accept($CLASS, $good, $projectsOnlyHash, $ACTOR, $NOW + 9);
$check('opakované převzetí aktualizuje známku a log narůstá (max. ' . G66_LOG_CAP . ')', count(g66_accept_log($CLASS, $goodId)) === 2);
$disabledView = audit_capture(static function (): void { g66v_render('class_1a', v62fx_key('good')); });
$check('třída s vypnutým hodnocením: pohled žáka jen informuje (žádný řetězec, žádná známka)', str_contains($disabledView, 'zatím nepočítá') && !str_contains($disabledView, 'a66-chain') && !$gradeShown($disabledView));
storage_update(g66_accepted_path(), static function (array $d) use ($CLASS, $leftId): array { $d[$CLASS][$leftId] = ['current' => ['grade' => 3, 'at' => 1], 'log' => []]; return $d; });
$stu = identity58_student($leftId);
$check('retence převzatých známek: bez data odchodu se nic nemaže', is_array($stu) && g66_retention_purge(true, $NOW)['purge'] === 0);
storage_update(identity58_path(), static function (array $d) use ($leftId, $NOW): array { $d['students'][$leftId]['status'] = 'left'; $d['students'][$leftId]['archived_at'] = date(DATE_ATOM, $NOW - 40 * 86400); return $d; });
$dry = g66_retention_purge(true, $NOW);
$check('retence převzatých známek: žák odešlý před 40 dny se mazat bude (dry-run nic nemění), aktivní žák zůstává', $dry['purge'] === 1 && g66_accepted($CLASS, $leftId) !== null);
$real = g66_retention_purge(false, $NOW);
$check('retence převzatých známek: ostrý běh smaže jen odešlého žáka, známka Dobrého zůstává', $real['purge'] === 1 && g66_accepted($CLASS, $leftId) === null && g66_accepted($CLASS, $goodId) !== null);

require __DIR__ . '/v66_audit_part3.php';
require __DIR__ . '/v66_audit_part4.php';
