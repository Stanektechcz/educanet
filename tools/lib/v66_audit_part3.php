<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v66 · část 3 auditu (načítá ji tools/lib/v66_audit_part2.php, sdílí proměnné $check, $tmp, $CLASS, $NOW, $ACTOR, $good, $player, $none, $p …).
 * Ranní přehled, per-žákovské přiřazení cesty, cockpit (jen cache, velikost, detail), CSV a ochrana proti vzorcům.
 */

// --- 8) Ranní přehled a per-žákovské přiřazení cesty ------------------------------------------------------
$paths = p63_paths_for_class($CLASS);
$pathId = (string)array_key_first($paths);
$stepId = (string)(p63_step_ids($paths[$pathId])[3] ?? p63_step_ids($paths[$pathId])[1]);
storage_update(p63_state_path($goodId), static function (array $raw) use ($pathId, $stepId, $NOW): array {
    $st = p63_state_normalize($raw);
    $st['paths'][$pathId][$stepId] = ['status' => 'tried', 'attempts' => 4, 'best' => 0.25, 'last_variant' => -1, 'at' => $NOW, 'verify_day' => '', 'verify_count' => 0];
    return $st;
});
$morning = m66_build($CLASS, $NOW);
$mItems = $morning['items'];
$stuck = array_values(array_filter($mItems, static fn(array $i): bool => $i['type'] === 'stuck'));
$impacts = array_column($mItems, 'impact');
$sortedImpacts = $impacts;
rsort($sortedImpacts);
$check('ranní přehled: žák uvízlý ve 4 pokusech je položka „stuck“ s návrhem zásahu (přiřadit mu cestu), hash žáka místo jména', count($stuck) === 1 && $stuck[0]['students'] === [g66_key_hash($good)] && $stuck[0]['action'] === ['kind' => 'assign_student', 'path' => $pathId]
    && $stuck[0]['detail']['attempts'] === 4);
$check('ranní přehled: položky jsou seřazené podle dopadu sestupně, nejvýš ' . M66_STORE_MAX . ' a každá má id, typ a dopad', $impacts === $sortedImpacts && count($mItems) <= M66_STORE_MAX && !array_filter($mItems, static fn(array $i): bool => !isset($i['id'], $i['type'], $i['impact'])));
$inactive = array_values(array_filter($mItems, static fn(array $i): bool => $i['type'] === 'inactive'));
$inactiveStudents = $inactive === [] ? [] : array_merge(...array_column($inactive, 'students'));
$check('ranní přehled: žáci bez aktivity ≥ 7 dní jsou „inactive“, uvízlý žák má vyšší dopad než kterýkoli neaktivní a není zároveň neaktivní', count($inactive) >= 1 && $stuck[0]['impact'] > max(array_column($inactive, 'impact')) && !in_array(g66_key_hash($good), $inactiveStudents, true));
$mText = (string)file_get_contents(m66_path($CLASS));
$check('cache ranního přehledu: žádná jména žáků ani student_id (jen hashe)', !str_contains($mText, 'Audit') && !str_contains($mText, 'stu_') && !str_contains($mText, 'Dobry'));
$check('třída nezvládá kompetenci: položka „weak“ vzniká až od ' . M66_WEAK_MIN_STUDENTS . ' žáků a aspoň poloviny žáků s důkazy (v malé fixtuře žádná)', m66_weak_items($CLASS, $NOW) === []);
$many = [];
for ($i = 0; $i < 12; $i++) $many[] = ['id' => 'fx' . $i, 'type' => 'inactive', 'impact' => 3.0 - $i / 100, 'students' => [str_pad(dechex($i), 24, 'a')], 'detail' => ['days' => 8 + $i], 'action' => null];
storage_update(m66_path('class_2a'), static fn(array $d): array => ['v' => 1, 'class' => 'class_2a', 'built_at' => $NOW, 'items' => $many]);
$html12 = a66t_section_morning('class_2a', 'tok', []);
$beforeDetails = (string)strstr($html12, '<details', true);
$check('cockpit Ráno: nejvýš ' . M66_SHOW_MAX . ' položek viditelně, zbylých 7 pod <details>', substr_count($beforeDetails, 'class="a66-card') === M66_SHOW_MAX && substr_count($html12, 'class="a66-card') === 12 && str_contains($html12, 'Dalších 7 položek'));
$check('cockpit Ráno: bez cache ukáže „připravuje se“ a nic nepočítá; zastaralá cache (starší 48 h) je označená', str_contains(a66t_section_morning('class_4a', 'tok', []), 'připravuje') && m66_is_stale(['built_at' => $NOW - 49 * 3600], $NOW) && !m66_is_stale(['built_at' => $NOW - 3600], $NOW));

$p63Other = (string)array_key_first(p63_paths_for_class('class_1a'));
p63_memo_reset();
$check('před přiřazením žák Nikdo nemá přiřazenou cestu a „Co dál“ ji nenabízí jako přiřazenou', (p63_next($CLASS, $none, $NOW)['kind'] ?? '') !== 'assigned' && p63_state($noneId)['assigned'] === []);
$check('p63_assign_student: neplatný hash učitele, neznámý žák, cesta jiné třídy i cesta mimo katalog se odmítnou a nic se nezapíše', !p63_assign_student($CLASS, $none, $pathId, 'nehash', $NOW) && !p63_assign_student($CLASS, $CLASS . ':student:' . str_repeat('0', 24), $pathId, $ACTOR, $NOW)
    && !p63_assign_student($CLASS, $none, $p63Other, $ACTOR, $NOW) && !p63_assign_student($CLASS, $none, 'cesta_neexistuje', $ACTOR, $NOW) && p63_state($noneId)['assigned'] === []);
$assignedOk = p63_assign_student($CLASS, $none, $pathId, $ACTOR, $NOW);
p63_memo_reset();
$next = p63_next($CLASS, $none, $NOW);
$check('přiřazení cesty jednomu žákovi: žák Nikdo ji má ve stavu a „Co dál“ ji nabídne jako přiřazenou, ostatní žáci (Hráč) ji nemají', $assignedOk && isset(p63_state($noneId)['assigned'][$pathId]) && ($next['kind'] ?? '') === 'assigned' && ($next['path'] ?? '') === $pathId
    && !isset(p63_state($playerId)['assigned'][$pathId]));
$check('per-žákovské přiřazení se v třídním přehledu nezobrazí jako přiřazení třídy (p63_assignments beze změny)', p63_assignments($CLASS) === []);
$classOk = p63_assign($CLASS, $pathId, $ACTOR, $NOW);
$check('třídní přiřazení v63 dál funguje: cesta se zrcadlí do stavu všech žáků a vrátí ji p63_assignments', $classOk && isset(p63_assignments($CLASS)[$pathId]) && isset(p63_state($playerId)['assigned'][$pathId]) && isset(p63_state($goodId)['assigned'][$pathId]));
p63_unassign($CLASS, $pathId);
$check('zrušení třídního přiřazení odebere cestu ze stavů žáků a třídní přiřazení zmizí', p63_assignments($CLASS) === [] && !isset(p63_state($playerId)['assigned'][$pathId]));
$check('zápis zásahu do ranního logu: jen čas, druh, třída, hash učitele a hash žáka; neplatný druh a neplatný hash žáka se odmítnou',
    m66_log_action($CLASS, 'assign_student', $ACTOR, g66_key_hash($none), $pathId, $NOW) && !m66_log_action($CLASS, 'smaz_vse', $ACTOR, '', $pathId) && !m66_log_action($CLASS, 'assign_student', $ACTOR, 'Audit Nikdo', $pathId)
    && !m66_log_action($CLASS, 'assign_class', 'nehash', '', $pathId) && count(m66_log_rows()) === 1 && array_keys(m66_log_rows()[0]) === ['at', 'class', 'kind', 'actor_hash', 'student_hash', 'path']);
$logText = (string)file_get_contents(m66_log_path());
$check('ranní log neobsahuje jména žáků ani student_id', !str_contains($logText, 'Audit') && !str_contains($logText, 'Nikdo') && !str_contains($logText, 'stu_'));
$check('retence ranního logu: v běžném školním roce se nic nemaže, po jeho skončení by se smazal 1 záznam (dry-run nic nemění)', m66_log_purge(true, $NOW)['purge'] === 0 && m66_log_purge(true, i66_school_year_start($NOW) + 400 * 86400)['purge'] === 1 && count(m66_log_rows()) === 1);
$check('retence v66 souhrnně: a66_retention_purge zahrnuje štítky integrity, ranní log i převzaté známky', array_keys(a66_retention_purge(true, $NOW)) === ['integrity', 'morning_log', 'accepted', 'dry_run']);

// --- 9) Cockpit: čtení jen z cache, velikost, detail, soukromí --------------------------------------------
a66_recompute_class($CLASS, $NOW);
$GLOBALS['educanet_storage_stats'] = null;
$_GET = [];
$tab = audit_capture(static function () use ($CLASS): void { a66t_render_tab($CLASS, 'tok'); });
$read = array_keys((array)($GLOBALS['educanet_storage_stats']['by'] ?? []));
$heavy = array_filter($read, static fn(string $n): bool => preg_match('/^(stu_|progress_v56|learning_profiles|lab_v57_events|practice_results|evidence)/', $n) === 1);
$check('záložka cockpitu čte jen cache: žádné čtení souborů žáků, postupu lekcí, profilů, událostí labu ani výsledků testů (přečteno: ' . count($read) . ' souborů)', $tab !== '' && $heavy === []);
$check('záložka cockpitu: HTML < 40 KB (' . strlen($tab) . ' B), všechny sekce, tisková šablona jen s media="print", jména žáků vidí učitel třídy', strlen($tab) < 40960 && str_contains($tab, 'Ráno: co udělat dnes') && str_contains($tab, 'Položková analýza') && str_contains($tab, 'Druhy testů') && str_contains($tab, 'Návrh hodnocení')
    && str_contains($tab, 'K ověření') && str_contains($tab, 'Exporty') && str_contains($tab, 'assessment-v66-print.css') && str_contains($tab, 'media="print"') && str_contains($tab, 'Audit Dobry'));
$check('záložka cockpitu: upozornění integrity říká, že skóre se nemění; formuláře mají CSRF a třídu', str_contains($tab, 'skóre se nemění') && str_contains($tab, 'name="csrf"') && str_contains($tab, 'name="class_id" value="class_3a"'));
$_GET = ['zak' => g66_key_hash($good)];
$detail = audit_capture(static function () use ($CLASS): void { a66t_render_tab($CLASS, 'tok'); });
$check('detail žáka v cockpitu ukáže řetězec důkazů (test lekce, projekt, důkazy kompetencí) a tlačítko Převzít návrh', str_contains($detail, 'Řetězec důkazů') && str_contains($detail, 'Test lekce 1') && str_contains($detail, 'lab:practice:') && str_contains($detail, 'Převzít návrh'));
$_GET = ['zak' => 'zzzz'];
$detailBad = audit_capture(static function () use ($CLASS): void { a66t_render_tab($CLASS, 'tok'); });
$_GET = ['zak' => g66_key_hash(project_student_key('class_1a', 'Neznamy Zak'))];
$detailOther = audit_capture(static function () use ($CLASS): void { a66t_render_tab($CLASS, 'tok'); });
$_GET = [];
$check('detail žáka: neplatný hash a hash žáka mimo třídu vedou na „žák nebyl nalezen“ (žádný únik cizí třídy)', str_contains($detailBad, 'nebyl nalezen') && str_contains($detailOther, 'nebyl nalezen'));
$itemsCsv = a66t_csv_body(a66t_items_rows($CLASS));
$propCsv = a66t_csv_body(a66t_proposal_rows($CLASS));
$check('export položek: UTF-8 BOM, středník, záhlaví a řádky položek, žádná jména žáků', str_starts_with($itemsCsv, "\xEF\xBB\xBF") && str_contains($itemsCsv, 'Třída;Test;Druh;Položka') && substr_count($itemsCsv, "\n") >= 12 && !str_contains($itemsCsv, 'Audit'));
$propRows = array_map(static fn(string $l): array => str_getcsv(ltrim($l, "\xEF\xBB\xBF"), ';', '"', ''), array_filter(explode("\n", $propCsv)));
$check('export návrhů: BOM, středník, 9 sloupců na každém řádku, řádek žáka Dobrý má návrh známky a vzorec odpovídá třídě', str_starts_with($propCsv, "\xEF\xBB\xBF") && count(array_unique(array_map('count', $propRows))) === 1 && count($propRows[0]) === 9
    && str_contains($propCsv, '"Audit Dobry";' . $p['grade'] . ';') && str_contains($propCsv, g66_formula_hash(g66_formula($CLASS))));
$check('export návrhů ve vypnuté třídě má jen záhlaví (žádný žák)', count(a66t_proposal_rows('class_1a')) === 1);
$check('ochrana proti vložení vzorců: = + - @ tabulátor na začátku textu se neutralizují apostrofem, čísla zůstanou čísly, běžný text beze změny',
    a66t_csv_cell('=HYPERLINK("x")') === "'=HYPERLINK(\"x\")" && a66t_csv_cell('+1') === "'+1" && a66t_csv_cell('-2') === "'-2" && a66t_csv_cell('@SUM(A1)') === "'@SUM(A1)" && a66t_csv_cell("\tx") === "'\tx"
    && a66t_csv_cell(-0.069) === '-0,069' && a66t_csv_cell(3) === '3' && a66t_csv_cell('Běžný text') === 'Běžný text' && a66t_csv_cell("a\nb") === 'a b');
$evil = a66t_csv_body([['=1+1', 'a;b', 'řádek "s" uvozovkami', '-5']]);
$evilRow = str_getcsv(ltrim($evil, "\xEF\xBB\xBF"), ';', '"', '');
$check('CSV: buňka s vloženým středníkem a uvozovkami zůstane jedním polem, vzorec je neutralizovaný', count($evilRow) === 4 && $evilRow[0] === "'=1+1" && $evilRow[1] === 'a;b' && $evilRow[2] === 'řádek "s" uvozovkami' && $evilRow[3] === "'-5");
$check('export mimo rozsah: a66t_can_class odmítne neznámou třídu', !a66t_can_class('class_xx') && a66t_can_class($CLASS));
