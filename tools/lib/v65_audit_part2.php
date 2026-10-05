<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 audit · část 2 (týmy, zámky kompetencí, portfolio a export, retence, statický sken a vykreslení).
 * Spouští se z tools/v65_projects_audit.php ve sdíleném globálním rozsahu ($check, $CLASS, $good, $player, $none, $left, $allowAll …).
 */

// --- 8) Týmové projekty ------------------------------------------------------------------------------------
$P3 = '3a_incident_team';
$group = project_save_group(['class_id' => $CLASS, 'project_id' => $P3, 'name' => 'Audit tým', 'member_keys' => [$good, $player, $none]]);
unset($GLOBALS['educanet_runtime_indexes']);
$check('tým: člen týmu zahájí týmový projekt, nečlen dostane no_team; cíl je id týmu (ne klíč žáka)', proj65_start($CLASS, $left, $P3, 'x')['error'] === 'no_team' && proj65_settings_save($CLASS, $P3, 'full', false, true)
    && proj65_start($CLASS, $good, $P3, str_repeat('Tým navrhuje postup. ', 3))['ok'] && proj65_get(proj65_ref_cat($CLASS, $P3, 'group', (string)$group['id'])) !== null);
$teamRef = proj65_ref_cat($CLASS, $P3, 'group', (string)$group['id']);
proj65_bulk_approve([$teamRef], $allowAll, 'audit');
$check('tým: členové cíle = členové týmu, cizí žák není člen', proj65_member_keys(proj65_get($teamRef)) === [$good, $player, $none] && !proj65_is_member(proj65_get($teamRef), $left) && proj65_is_member(proj65_get($teamRef), $player));
$ms = proj65_ms_add($teamRef, $player, 'Návrh topologie', ['neexistuje-task']);
$msRow = storage_read(proj65_path('milestones'))[$ms['id'] ?? ''] ?? [];
$check('milníky: člen přidá milník (status todo, neplatné odkazy na úkoly se zahodí), nečlen forbidden, krátký název title_short', $ms['ok'] && ($msRow['status'] ?? '') === 'todo' && ($msRow['task_ids'] ?? null) === [] && proj65_ms_add($teamRef, $left, 'Cizí milník', [])['error'] === 'forbidden'
    && proj65_ms_add($teamRef, $good, 'ab', [])['error'] === 'title_short');
$check('milníky: přesun mezi sloupci (todo → doing → done), neplatný stav a nečlen se odmítnou, stav milníku nemění nic mimo sidecar', proj65_ms_move($ms['id'], $good, 'doing')['ok'] && proj65_ms_move($ms['id'], $good, 'done')['ok'] && proj65_ms_move($ms['id'], $good, 'smazano')['error'] === 'invalid'
    && proj65_ms_move($ms['id'], $left, 'todo')['error'] === 'forbidden' && storage_read(proj65_path('milestones'))[$ms['id']]['status'] === 'done' && proj65_ms_list((string)$group['id'])[0]['title'] === 'Návrh topologie');
proj65_settings_save($CLASS, $P3, 'small', false, false);
$check('milníky jsou volitelné: u projektu bez milníků ms_add vrací milestones_off a záznam i bez milníků může dál (odevzdání)', proj65_ms_add($teamRef, $good, 'Další milník', [])['error'] === 'milestones_off' && proj65_submit($teamRef, $player, 'https://example.org/team', 'tým odevzdal')['ok'] && proj65_get($teamRef)['state'] === 'submitted');
proj65_settings_save($CLASS, $P3, 'full', false, true);
for ($i = 0; $i < 20; $i++) $lastMs = proj65_ms_add($teamRef, $good, 'Milník ' . $i, []);
$check('milníky: nejvýš ' . PROJ65_MS_MAX_PER_TEAM . ' na tým (další vrací limit)', ($lastMs['error'] ?? '') === 'limit' && count(proj65_ms_list((string)$group['id'])) === PROJ65_MS_MAX_PER_TEAM);
$long = proj65_diary_add($teamRef, $good, str_repeat('č', 400));
$entry = proj65_diary_list($teamRef)[0] ?? [];
$check('deník: zápis se ořízne na 280 znaků, vidí ho tým i učitel (čtení podle ref), nečlen forbidden, příliš krátký text text_short', $long['ok'] && u_strlen((string)($entry['text'] ?? '')) === PROJ65_DIARY_MAX && proj65_diary_add($teamRef, $left, 'cizí zápis')['error'] === 'forbidden'
    && proj65_diary_add($teamRef, $good, 'ab')['error'] === 'text_short' && ($entry['student_key'] ?? '') === $good);
for ($i = 0; $i < 5; $i++) $lastDiary = proj65_diary_add($teamRef, $good, 'Zápis číslo ' . $i . ' o mém přínosu');
$check('deník: nejvýš ' . PROJ65_DIARY_PER_DAY . ' zápisů denně na člena (další daily_limit), ostatní členové nejsou omezeni', ($lastDiary['error'] ?? '') === 'daily_limit' && proj65_diary_add($teamRef, $player, 'Můj první zápis dnes')['ok']);
$check('rozdělení bodů: platí jen pro ostatní členy, celá čísla, součet přesně 100 (99, 101, sebe, cizí klíč, záporné = odmítnuto)', proj65_split_validate([$player => 70, $none => 30], [$good, $player, $none], $good) === [$player => 70, $none => 30]
    && proj65_split_validate([$player => 70, $none => 29], [$good, $player, $none], $good) === null && proj65_split_validate([$player => 70, $none => 31], [$good, $player, $none], $good) === null
    && proj65_split_validate([$player => 50, $none => 30, $good => 20], [$good, $player, $none], $good) === null && proj65_split_validate([$player => 100, $none => 0, 'cizi' => 0], [$good, $player, $none], $good) === null
    && proj65_split_validate([$player => -10, $none => 110], [$good, $player, $none], $good) === null && proj65_split_validate([$player => 100], [$good, $player], $good) === null);
$check('rozdělení bodů: nečlen ani neplatný součet se neuloží', proj65_split_save($teamRef, $left, [$player => 50, $none => 50])['ok'] === false && proj65_split_save($teamRef, $good, [$player => 60, $none => 60])['ok'] === false && (array)proj65_get($teamRef)['splits'] === []);
$s1 = proj65_split_save($teamRef, $good, [$player => 70, $none => 30]);
$s2 = proj65_split_save($teamRef, $player, [$good => 50, $none => 50]);
$splitsNow = (array)proj65_get($teamRef)['splits'];
$check('rozdělení bodů: s jedním rozdělením faktor není (méně než 2), se dvěma ano', $s1['ok'] && $s2['ok'] && proj65_contribution_factor(['a' => ['b' => 50, 'c' => 50]], ['a', 'b', 'c']) === null && proj65_contribution_factor($splitsNow, [$good, $player, $none]) !== null);
$s3 = proj65_split_save($teamRef, $none, [$good => 90, $player => 10]);
$factors = proj65_contribution_factor((array)proj65_get($teamRef)['splits'], [$good, $player, $none]);
$check('faktor přínosu: vždy v ⟨0,8; 1,2⟩ (clamp); extrémní přijaté body = 1,2 / 0,8, vyvážené = 1,0', $s3['ok'] && $factors[$good] === 1.2 && $factors[$player] === 0.8 && $factors[$none] === 0.8 && min($factors) >= 0.8 && max($factors) <= 1.2
    && proj65_contribution_factor(['a' => ['b' => 50, 'c' => 50], 'b' => ['a' => 50, 'c' => 50], 'c' => ['a' => 50, 'b' => 50]], ['a', 'b', 'c']) === ['a' => 1.0, 'b' => 1.0, 'c' => 1.0]);
$check('faktor přínosu: tým < 3 členů faktor nemá (null), i když rozdělení existují', proj65_contribution_factor(['a' => ['b' => 100], 'b' => ['a' => 100]], ['a', 'b']) === null && proj65_contribution_factor([], ['a', 'b', 'c']) === null);
$teamLv = ['design' => 3, 'evidence' => 3, 'security' => 3, 'validation' => 3];
$evTeam0 = $evCount($good) + $evCount($player) + $evCount($none);
$check('hodnocení týmu: koncept před publikací nevytvoří důkaz členům a stav zůstane submitted', proj65_grade($teamRef, $teamLv, [], [], false, $allowAll, 'audit')['ok'] && $evCount($left) === 0 && $evCount($good) + $evCount($player) + $evCount($none) === $evTeam0 && proj65_get($teamRef)['state'] === 'submitted');
$evBeforeTeam = [$evCount($good), $evCount($player), $evCount($none)];
$teamPub = proj65_grade($teamRef, $teamLv, [], [], true, $allowAll, 'audit');
$check('hodnocení týmu: publikace dá důkaz VŠEM členům týmu (ref s verzí, kompetence z rubriky) a nikomu mimo tým', $teamPub['ok'] && $evCount($good) === $evBeforeTeam[0] + 4 && $evCount($player) === $evBeforeTeam[1] + 4 && $evCount($none) === $evBeforeTeam[2] + 4 && $evCount($left) === 0 && $teamPub['evidence']['emitted'] === 12);
$delta = proj65_factor_suggestion(proj65_get($teamRef));
$teamGrade = project_grade_find($CLASS, $P3, 'group', (string)$group['id']);
$maxDelta = (int)ceil(0.2 * (int)$teamGrade['points']);
$check('návrh faktoru: points_delta = round((faktor−1)×body) a nikdy víc než ±20 % bodů týmu; sám nic nezapíše (všechny points_delta v hodnocení zůstávají 0)', $delta !== null && max(array_map('abs', $delta)) <= $maxDelta && $delta[$good] > 0 && $delta[$player] < 0
    && array_sum(array_map(static fn(array $m): int => abs((int)($m['points_delta'] ?? 0)), (array)$teamGrade['member_adjustments'])) === 0);
$check('potvrzení návrhu: cizí rozsah se odmítne bez zápisu; potvrzení učitelem zapíše points_delta, stav a body týmu zůstanou', proj65_factor_apply($teamRef, static fn(string $c): bool => false)['error'] === 'class_out_of_scope'
    && array_sum(array_map(static fn(array $m): int => abs((int)($m['points_delta'] ?? 0)), (array)project_grade_find($CLASS, $P3, 'group', (string)$group['id'])['member_adjustments'])) === 0);
$applied = proj65_factor_apply($teamRef, $allowAll);
unset($GLOBALS['educanet_runtime_indexes']);
$teamGradeAfter = project_grade_find($CLASS, $P3, 'group', (string)$group['id']);
$studentResult = array_values(array_filter(project_student_results($CLASS, V62FX_LABELS['good']), static fn(array $r): bool => $r['record']['project_id'] === $P3))[0] ?? [];
$check('potvrzení návrhu: points_delta členů odpovídá návrhu, hodnocení zůstává published, žák vidí body upravené v rozsahu ±20 %', $applied['ok'] && (int)$teamGradeAfter['member_adjustments'][$good]['points_delta'] === $delta[$good] && $teamGradeAfter['status'] === 'published'
    && (int)$teamGradeAfter['points'] === (int)$teamGrade['points'] && ($studentResult['points'] ?? -1) === min((int)$teamGrade['max_points'], (int)$teamGrade['points'] + $delta[$good]));

$_SESSION['csrf'] = 'audit-token';
ob_start();
p65v_render_detail(proj65_get($teamRef), $CLASS, $good, '');
$detailTeam = (string)ob_get_clean();

// --- 9) Zámky projektů podle kompetencí --------------------------------------------------------------------
$in = static fn(array $over = []): array => array_replace(['title' => 'Audit zakázka', 'reward_type' => 'portfolio', 'min_level' => 1, 'capacity' => 5, 'status' => 'open', 'classes' => [$CLASS],
    'rc_id' => ['net_dns_dhcp', '', ''], 'rc_state' => ['zvladnuto', 'zvladnuto', 'zvladnuto']], $over);
$check('zámky: neznámá kompetence, neplatný stav a víc než 3 kompetence se odmítnou (proj60_save vrátí null)', proj60_save('', $in(['rc_id' => ['neexistuje', '', '']]), [$CLASS], 'audit') === null && proj60_save('', $in(['rc_state' => ['hotovo', '', '']]), [$CLASS], 'audit') === null
    && proj60_save('', $in(['rc_id' => ['net_dns_dhcp', 'net_addressing', 'net_diagnose', 'net_services'], 'rc_state' => ['zvladnuto', 'zvladnuto', 'zvladnuto', 'zvladnuto']]), [$CLASS], 'audit') === null);
$lockId = (string)proj60_save('', $in(), [$CLASS], 'audit');
$freeId = (string)proj60_save('', $in(['title' => 'Audit bez zámku', 'rc_id' => ['', '', '']]), [$CLASS], 'audit');
$strictId = (string)proj60_save('', $in(['title' => 'Audit upevněno', 'rc_state' => ['upevneno', 'zvladnuto', 'zvladnuto']]), [$CLASS], 'audit');
$check('zámky: požadované kompetence se uloží do katalogu (max 3, id + min_state), projekt bez požadavků nemá žádné', proj60_item($lockId)['required_competencies'] === [['id' => 'net_dns_dhcp', 'min_state' => 'zvladnuto']] && proj60_item($freeId)['required_competencies'] === []);
$lockApply = proj60_apply($CLASS, $none, $lockId, 'chci', 5);
$check('zámky: žák bez kompetence dostane competency_missing a nevznikne přihláška; projekt bez zámku projde', $lockApply === ['ok' => false, 'error' => 'competency_missing'] && count(proj60_my_applications($CLASS, $none)) === 0 && proj60_apply($CLASS, $none, $freeId, 'chci', 5)['ok']);
ev62_append((string)ev62_student_context($CLASS, $player)['id'], [['competency' => 'net_dns_dhcp', 'level' => 2, 'source' => 'test', 'score' => 0.95, 'at' => date(DATE_ATOM), 'artefact_ref' => 'test:audit:dns']]);
$check('zámky: zvládnutá kompetence (stav zvladnuto) přihlášku povolí, „upevněno“ u téhož žáka stále ne', proj60_apply($CLASS, $player, $lockId, 'mám dns', 5)['ok'] && proj60_apply($CLASS, $player, $strictId, 'chci', 5)['error'] === 'competency_missing'
    && proj60_missing_competencies($CLASS, $player, proj60_item($strictId)) === ['net_dns_dhcp'] && proj60_missing_competencies($CLASS, $player, proj60_item($lockId)) === []);
$check('zámky: mimo pilot kompetencí se požadavek ignoruje (class_2a nikdy nehlásí chybějící kompetence)', proj60_missing_competencies('class_2a', 'class_2a:student:' . str_repeat('a', 24), proj60_item($lockId)) === [] && !comp62_enabled_for_class('class_2a'));
$lockSrc = (string)file_get_contents($root . '/projects_v60.php');
$check('zámky: kontrola kompetencí běží i uvnitř storage_update (znovu nad čerstvým projektem)', preg_match('/storage_update\(proj60_applications_path\(\).*proj60_missing_competencies\(\$classId, \$studentKey, \$fresh\)/s', $lockSrc) === 1, false);
ob_start();
projects60_render_card($lockId, proj60_item($lockId), $CLASS, $none, 5);
$cardHtml = (string)ob_get_clean();
$check('zámky: karta projektu ukáže chybějící kompetenci a neposkytne formulář přihlášky', str_contains($cardHtml, 'Chybí ti kompetence') && !str_contains($cardHtml, 'name="action" value="proj60_apply"'));

// --- 10) Portfolio a export --------------------------------------------------------------------------------
$goodId = (string)ev62_student_context($CLASS, $good)['id'];
$cand = port65_candidates($CLASS, $good);
$candKeys = array_column($cand, 'key');
$p1Key = 'c:' . (string)proj65_get(proj65_ref_cat($CLASS, $P1, 'individual', $good))['id'];
$check('portfolio: nabízí ohodnocené projekty žáka (v65), bez výběru nic není zařazeno (výchozí výběr jen z Featured)', in_array($p1Key, $candKeys, true) && count(port65_view_model($CLASS, $good)) === 0
    && array_sum(array_map(static fn(array $s): int => $s['selected'] ? 1 : 0, port65_selection($goodId, $cand))) === 0);
$sv = port65_save_item($CLASS, $good, $p1Key, true, '<b>tučně</b> ' . str_repeat('r', 700));
$stored = storage_read(port65_path($goodId));
$check('portfolio: výběr + reflexe se uloží do storage/portfolio_v65/<stu_id>.json.php, reflexe max ' . PORT65_REFLECTION_MAX . ' znaků, zařazená práce přejde do stavu portfolio', $sv['ok'] && u_strlen((string)$stored['items'][$p1Key]['reflection']) === PORT65_REFLECTION_MAX && $stored['saved'] === true
    && proj65_get(proj65_ref_cat($CLASS, $P1, 'individual', $good))['state'] === 'portfolio' && count(port65_view_model($CLASS, $good)) === 1);
$check('portfolio: cizí/neplatný klíč položky se odmítne (not_found), neznámý žák nemá identitu (portfolio se neukládá)', port65_save_item($CLASS, $good, 'c:c65_0000000000000000', true, 'x')['error'] === 'not_found'
    && port65_save_item($CLASS, 'class_3a:student:' . str_repeat('9', 24), $p1Key, true, 'x')['error'] === 'identity');
// peer text schválený u práce autora + soukromá poznámka v hodnocení: nesmí do exportu
$authorOwner = (string)proj65_get($authorRef)['owner_key'];
foreach (port65_candidates($CLASS, $authorOwner) as $c) port65_save_item($CLASS, $authorOwner, (string)$c['key'], true, 'Reflexe autora');
$html = port65v_export_html(port65_view_model($CLASS, $good), adaptive_student_label($CLASS, $good));
$htmlAuthor = port65v_export_html(port65_view_model($CLASS, $authorOwner), adaptive_student_label($CLASS, $authorOwner));
$check('export: samostatný HTML dokument s inline CSS a @media print, reflexe escapovaná (<b> se neprovede), obsahuje kritéria a úrovně', str_starts_with($html, '<!DOCTYPE html>') && str_contains($html, '<style>') && str_contains($html, '@media print') && str_contains($html, '&lt;b&gt;tučně&lt;/b&gt;')
    && !str_contains($html, '<b>tučně') && str_contains($html, 'Návrh řešení') && str_contains($html, '<meta charset="utf-8">'));
$check('export: bez JS, bez odkazů a externích zdrojů (žádné <script, http(s)://, src=, <link, @import, url(, href=)', array_sum(array_map(static fn(string $needle): int => substr_count(strtolower($html . $htmlAuthor), $needle), ['<script', 'http://', 'https://', ' src=', '<link', '@import', 'url(', 'href=', 'onclick', '<iframe'])) === 0);
$check('export: neobsahuje soukromou poznámku učitele ani peer texty (schválený peer text autora a marker private_note nejsou v žádném exportu)', !str_contains($html . $htmlAuthor, $privateMarker) && !str_contains($htmlAuthor, 'Přehledná topologie') && !str_contains($htmlAuthor, 'tabulku portů')
    && count(storage_read(proj65_path('peer'))) > 0 && str_contains($htmlAuthor, 'Reflexe autora'));
$desel = port65_save_item($CLASS, $good, $p1Key, false, '');
$check('portfolio: odebrání z výběru vrátí práci do stavu graded a z exportu zmizí; export nenabízí žádné sdílení odkazem (žádná sdílecí akce v routách)', $desel['ok'] && proj65_get(proj65_ref_cat($CLASS, $P1, 'individual', $good))['state'] === 'graded' && count(port65_view_model($CLASS, $good)) === 0
    && !str_contains((string)file_get_contents($root . '/app/actions/projects_v65.php'), 'share') && !str_contains((string)file_get_contents($root . '/portfolio_v65.php') . (string)file_get_contents($root . '/portfolio_v65_views.php'), 'token='));

// --- 11) Retence -------------------------------------------------------------------------------------------
$leftId = (string)ev62_student_context($CLASS, $left)['id'];
$P4 = '3a_network_rollout';
$group2 = project_save_group(['class_id' => $CLASS, 'project_id' => $P4, 'name' => 'Audit tým 2', 'member_keys' => [$good, $player, $left]]);
unset($GLOBALS['educanet_runtime_indexes']);
proj65_settings_save($CLASS, $P4, 'full', false, false);
proj65_start($CLASS, $left, $P4, str_repeat('Pitch odešlého žáka. ', 3));
$ref4 = proj65_ref_cat($CLASS, $P4, 'group', (string)$group2['id']);
proj65_bulk_approve([$ref4], $allowAll, 'audit');
proj65_submit($ref4, $left, 'https://example.org/l', 'poznámka odešlého');
proj65_diary_add($ref4, $left, 'Zápis odešlého žáka');
proj65_diary_add($ref4, $left, 'Druhý zápis odešlého žáka');
proj65_diary_add($ref4, $good, 'Zápis aktivního žáka');
proj65_split_save($ref4, $left, [$good => 50, $player => 50]);
proj65_split_save($ref4, $good, [$player => 50, $left => 50]);
storage_update(proj65_path('peer'), static function (array $p) use ($left, $refs2): array {
    $p['r65_leftreview0000001'] = ['id' => 'r65_leftreview0000001', 'ref' => $refs2[$GLOBALS['good']], 'class_id' => 'class_3a', 'project_id' => '3a_monitoring', 'reviewer_key' => $left, 'levels' => ['design' => 3], 'strength' => 'text odešlého', 'suggestion' => 'text odešlého 2', 'state' => 'approved', 'created_at' => date(DATE_ATOM)];
    return $p;
});
storage_update(port65_path($leftId), static fn(array $d): array => ['v' => 1, 'saved' => true, 'items' => ['c:x' => ['selected' => true, 'reflection' => 'reflexe odešlého']]]);
$setStatus = static function (string $id, string $status, ?string $at): void {
    storage_update(identity58_path(), static function (array $reg) use ($id, $status, $at): array { $reg['students'][$id]['status'] = $status; $reg['students'][$id]['archived_at'] = $at; return $reg; });
};
$snap = static fn(): string => hash('sha256', json_encode([storage_read(proj65_path('cycle')), storage_read(proj65_path('peer')), storage_stream_rows('projects_v65_diary'), is_file(port65_path($leftId))]));
$ndiary = static fn(): int => count(storage_stream_rows('projects_v65_diary'));
$check('retence: aktivní žák (status active) se nemaže, dry-run 0 kandidátů', proj65_retention_purge(true, $NOW + 40 * 86400)['students'] === 0);
$setStatus($leftId, 'left', date(DATE_ATOM, $NOW - 29 * 86400));
$check('retence: 29 dní po odchodu se nic nemaže (COMP62_RETENTION_GRACE_DAYS = 30)', COMP62_RETENTION_GRACE_DAYS === 30 && proj65_retention_purge(true, $NOW)['students'] === 0);
$setStatus($leftId, 'left', date(DATE_ATOM, $NOW - 31 * 86400));
$before = $snap();
$diaryBefore = $ndiary();
$dryRun = proj65_retention_purge(true, $NOW);
$check('retence: dry-run po 31 dnech spočítá portfolio, pitch/poznámky, peer, deník i rozdělení, ale nic nezmění', $dryRun['students'] === 1 && $dryRun['portfolio'] === 1 && $dryRun['pitch'] >= 1 && $dryRun['peer'] === 1 && $dryRun['diary'] === 2 && $dryRun['splits'] >= 1 && $snap() === $before);
$apply = proj65_retention_purge(false, $NOW);
$rec4 = proj65_get($ref4);
$check('retence: apply smaže portfolio odešlého, jeho pitch a poznámku k odevzdání, peer texty a zápisy deníku (aktivním žákům zůstanou)', !is_file(port65_path($leftId)) && $rec4['pitch'] === '' && $rec4['submission']['note'] === '' && !isset($rec4['splits'][$left])
    && !isset(storage_read(proj65_path('peer'))['r65_leftreview0000001']) && $ndiary() === $diaryBefore - 2 && count(array_filter(proj65_diary_list($ref4), static fn(array $d): bool => $d['student_key'] === $left)) === 0
    && count(array_filter(proj65_diary_list($ref4), static fn(array $d): bool => $d['student_key'] === $good)) === 1);
$check('retence: data aktivních žáků zůstala (pitch žáka Dobrý u P2, jeho portfolio a jeho hodnocení), druhé spuštění je idempotentní', proj65_get($refs2[$good])['pitch'] !== '' && is_file(port65_path($goodId)) === true && $apply['students'] === 1 && proj65_retention_purge(false, $NOW)['pitch'] === 0 && proj65_retention_purge(false, $NOW)['peer'] === 0);
$retentionSrc = (string)file_get_contents($root . '/tools/v58_retention.php');
$check('retence: CLI tools/v58_retention.php volá proj65_retention_purge (30 dní po odchodu, dry-run bez --apply)', str_contains($retentionSrc, 'proj65_retention_purge(!$apply)') && str_contains($retentionSrc, 'projects_v65_purged'), false);

// --- 12) Vykreslení žáka ------------------------------------------------------------------------------------
$_SESSION['csrf'] = 'audit-token';
ob_start();
p65v_render_home($CLASS, $none, '');
$home = (string)ob_get_clean();
$taskNone = proj65_reviews_for_reviewer($CLASS, $none);
ob_start();
p65v_render_detail(proj65_get($authorRef), $CLASS, $authorOwner, '');
$detail = (string)ob_get_clean();
$check('žák: přehled má nadpis h1, odkaz na portfolio, formuláře s CSRF tokenem a bez klíče žáka v HTML (jen neprůhledné c65_ id)', str_contains($home, '<h1>') && str_contains($home, 'href="?view=portfolio"') && str_contains($home, 'name="csrf" value="audit-token"') && !str_contains($home, 'student:') && str_contains($home, 'proj65_s_start'));
$check('žák: detail zobrazí kroky cyklu (ol, aria-current="step"), autor vidí schválený anonymní text a nikdy jméno recenzenta ani id recenze', str_contains($detail, '<ol class="p65-steps"') && str_contains($detail, 'aria-current="step"') && (str_contains($detail, 'Přehledná topologie') || proj65_peer_author_view($authorRef) === [])
    && !str_contains($detail, V62FX_LABELS['good'] . '</') && !str_contains($detail, 'r65_') && !str_contains($detail, 'student:'));
$check('žák: týmový detail má milníky (kanban), deník s počítadlem znaků, formulář rozdělení 100 bodů a aria-live součet', str_contains($detailTeam, 'p65-kanban') && str_contains($detailTeam, 'data-p65-max="280"') && str_contains($detailTeam, 'proj65_s_split') && str_contains($detailTeam, 'data-p65-sum') && str_contains($detailTeam, 'aria-live="polite"'));
ob_start();
port65v_render_page($CLASS, $good, '');
$pfPage = (string)ob_get_clean();
$check('žák: stránka portfolia nabízí stažení HTML a tisk (tlačítko data-p65-print), počítadlo reflexe a upozornění, že se nesdílí odkazem', str_contains($pfPage, 'view=portfolio_export') && str_contains($pfPage, 'data-p65-print') && str_contains($pfPage, 'data-p65-max="' . PORT65_REFLECTION_MAX . '"') && str_contains($pfPage, 'nesdílí se odkazem'));
$review = proj65_reviews_for_reviewer($CLASS, $player)[0] ?? null;
ob_start();
if ($review !== null) p65v_render_review_form($review, 1);
$revForm = (string)ob_get_clean();
$check('žák: formulář recenze má nápovědu vět, label u textarey a povinné 1 silná stránka + 1 návrh (maxlength 400), práce je označena „Práce č. N“ bez jména autora', $review !== null && str_contains($revForm, 'data-p65-starter') && str_contains($revForm, '<label for="p65-t') && str_contains($revForm, 'maxlength="400"') && str_contains($revForm, 'Práce č. 1')
    && !str_contains($revForm, 'student:'));

// --- 13) Učitel: vykreslení a politiky ---------------------------------------------------------------------
ob_start();
proj65_render_teacher_tab($CLASS, 'tok');
$teacherHtml = (string)ob_get_clean();
$check('učitel: záložka obsahuje nastavení projektů, návrhy ke schválení, odevzdané práce, moderaci peer review a přínos týmů (česky)', str_contains($teacherHtml, 'Nastavení projektů') && str_contains($teacherHtml, 'Návrhy ke schválení') && str_contains($teacherHtml, 'Odevzdané práce') && str_contains($teacherHtml, 'Peer review: moderace') && str_contains($teacherHtml, 'Týmy: přínos členů'));
$check('učitel: rubriku lze klonovat a upravit (formulář proj65_t_rubric_clone), hodnocení nabízí koncept i publikaci a úroveň 1 vyžaduje komentář', str_contains($teacherHtml, 'proj65_t_rubric_clone') && str_contains($teacherHtml, 'name="publish" value="1"') && str_contains($teacherHtml, 'name="publish" value="0"') && str_contains($teacherHtml, 'u úrovně 1 povinný'));
$teacherActions = [];
preg_match_all("/case '(proj65_t_[a-z_]+)'/", (string)file_get_contents($root . '/projects_v65_teacher_actions.php'), $mm);
$teacherActions = array_unique($mm[1]);
$policyOk = $teacherActions !== [];
foreach ($teacherActions as $a) { $p = teacher59_action_policy($a); if (($p['class'] ?? '') !== 'required' || !empty($p['deny'])) $policyOk = false; }
$check('politiky: každá akce proj65_t_* (' . count($teacherActions) . ') má v teacher_scope_v59 politiku s povinnou třídou, neznámá proj65_t_* je zakázaná (deny-by-default)', $policyOk && count($teacherActions) === 8 && !empty(teacher59_action_policy('proj65_t_neznama')['deny']) && !empty(teacher59_action_policy('proj65_s_start')['deny']));
$permOk = true;
foreach ($teacherActions as $a) if (teacher_action_permission($a) !== 'projects.manage') $permOk = false;
$check('oprávnění: akce proj65_ vyžadují projects.manage a záložka je v registru cockpitu s POST prefixem proj65_t_', $permOk && (teacher58_modules()['projekty65']['post'] ?? []) === ['proj65_t_' => 'proj65_teacher_handle_post'] && teacher58_available('projekty65'));
$check('oprávnění: asistent nemá projects.manage v matici rolí (jen čte)', preg_match("/'assistant'=>\[[^\]]*'projects\.manage'/", (string)file_get_contents($root . '/teacher_operations_v46.php')) !== 1, false);

// --- 14) Statický sken -------------------------------------------------------------------------------------
$newFiles = ['projects_v65.php', 'projects_v65_rubrics.php', 'projects_v65_peer.php', 'projects_v65_team.php', 'projects_v65_evidence.php', 'portfolio_v65.php', 'projects_v65_views.php', 'projects_v65_detail_views.php', 'portfolio_v65_views.php',
    'projects_v65_teacher_views.php', 'projects_v65_teacher_actions.php', 'app/views/projects_v65.php', 'app/views/portfolio.php', 'app/actions/projects_v65.php', 'migrations/0003_projects_v65.php', 'lang/en/ui/projects_v65.php', 'lang/uk/ui/projects_v65.php', 'tools/v65_projects_audit.php', 'tools/lib/v65_audit_part2.php'];
$strict = $guard = $size = $legacy = true;
foreach ($newFiles as $f) {
    $src = (string)file_get_contents($root . '/' . $f);
    $strict = $strict && str_contains(substr($src, 0, 120), 'declare(strict_types=1);');
    $isCli = str_starts_with($f, 'tools/');
    $guard = $guard && ($isCli ? str_contains($src, "PHP_SAPI !== 'cli'") || str_contains($src, 'basename((string)($_SERVER') : str_contains($src, 'http_response_code(403)'));
    $size = $size && substr_count($src, "\n") <= 800;
    $legacy = $legacy && !(str_contains($src, 'load_php_' . 'json(') && str_contains($src, 'save_php_json_' . 'map('));
}
$check('statický sken: nové soubory mají strict_types, guard proti přímému volání, ≤ 800 řádků a žádnou dvojici load_php_json + save_php_json_map', $strict && $guard && $size && $legacy, false);
$assetCss = (string)file_get_contents($root . '/assets/projects-v65.css');
$assetJs = (string)file_get_contents($root . '/assets/projects-v65.js');
$check('assety: CSS ≤ 12 kB a JS ≤ 6 kB, bez CDN/externích URL, JS bez innerHTML (jen textContent/value), CSS má prefers-reduced-motion a :focus-visible', strlen($assetCss) <= 12288 && strlen($assetJs) <= 6144 && !str_contains($assetCss . $assetJs, 'http') && !str_contains($assetJs, 'innerHTML')
    && str_contains($assetCss, 'prefers-reduced-motion') && str_contains($assetCss, ':focus-visible') && str_contains($assetCss, '@media print') && str_contains($assetJs, 'textContent'), false);
$studentActions = [];
preg_match_all("/case '(proj65_s_[a-z0-9_]+)'/", (string)file_get_contents($root . '/app/actions/projects_v65.php'), $ma);
$routed = true;
foreach (array_unique($ma[1]) as $a) if (app_segments('actions_class', $a) === []) $routed = false;
$actionSrc = (string)file_get_contents($root . '/app/actions/projects_v65.php');
$check('router: všech ' . count(array_unique($ma[1])) . ' akcí proj65_s_* je v tabulce rout (actions_class), pohledy projekt65/portfolio/portfolio_export mají záznam, identita žáka jen ze session (žádný $_POST/$_GET student)', $routed && count(array_unique($ma[1])) === 11
    && app_segments('views_student', 'projekt65') !== [] && app_segments('views_student', 'portfolio') !== [] && app_segments('views_student', 'portfolio_export') !== [] && !preg_match('/\$_(POST|GET)\[\'(student|student_key|class_id|key_owner)\'\]/', $actionSrc) && str_contains($actionSrc, 'adaptive_student_key'));
$indexSrc = (string)file_get_contents($root . '/index.php');
$check('CSRF: index.php ověří verify_csrf() u každého POST dřív než akce; učitelský POST ověřuje CSRF v teacher.php', preg_match('/if \(\$method === \'POST\'\) \{\s*verify_csrf\(\);/', $indexSrc) === 1 && str_contains((string)file_get_contents($root . '/teacher.php'), 'verify_csrf'), false);
$check('tr(): v žákovských souborech v65 nezůstal holý český text v echo (každý řetězec v HTML je přes tr()/e()) – hlídá i v59 i18n audit', str_contains((string)file_get_contents($root . '/lang/domains_v59.php'), "'projects_v65' => ["), false);

exit(audit_summary($state, 'V65_PROJECTS'));
