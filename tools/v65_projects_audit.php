<?php

declare(strict_types=1);

/**
 * EDUCANET v65 · behaviorální audit vrstvy „projekty osobní i týmové + portfolio“.
 *   php tools/v65_projects_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte ani nezapisuje ostrou storage/. Fiktivní žáci „Audit …“ (fixture v62).
 * Část 1: migrace (idempotence, SHA-256 starých souborů, rollback), přechody plný/malý cyklus, hromadné schválení a rozsah,
 *         rubriky (validace, klon, úroveň 1 bez komentáře, převod na body), důkaz při publikaci, verze, mastery, peer review.
 * Část 2 (tools/lib/v65_audit_part2.php): týmy (milníky, deník, faktor), zámky kompetencí, portfolio a export, retence, statický sken.
 * Konec: V65_PROJECTS_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v65-projects')), '/');
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php',
    'mastery_v62.php', 'projects_v60.php', 'projects_v60_views.php', 'projects_v65.php', 'projects_v65_rubrics.php', 'projects_v65_peer.php', 'projects_v65_team.php', 'projects_v65_evidence.php',
    'portfolio_v65.php', 'projects_v65_views.php', 'projects_v65_detail_views.php', 'portfolio_v65_views.php', 'projects_v65_teacher_views.php', 'teacher_v58.php', 'ops_v58.php', 'app/lib.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v62_competency_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));

$NOW = time();
v62fx_seed($tmp, $NOW);
$CLASS = V62FX_CLASS;
$good = v62fx_key('good');
$player = v62fx_key('player');
$none = v62fx_key('none');
$left = v62fx_key('left');
$allowAll = static fn(string $c): bool => $c === $CLASS;
$evCount = static fn(string $key): int => count(array_filter(ev62_read((string)ev62_student_context($CLASS, $key)['id']), static fn(array $r): bool => $r['source'] === 'project'));
$jsonOf = static fn(string $file): string => is_file(STORAGE_DIR . '/' . $file) ? hash_file('sha256', STORAGE_DIR . '/' . $file) : 'missing';

// --- 1) Migrace 0003 --------------------------------------------------------------------------------------
$oldFiles = ['project_grades.json.php', 'project_groups.json.php', 'projects_v60.json.php', 'projects_v60_applications.json.php', 'project_workspace_tasks.json.php'];
foreach ($oldFiles as $f) storage_update(STORAGE_DIR . '/' . $f, static fn(array $d): array => $d === [] ? ['seed' => ['id' => 'fx65', 'x' => $f]] : $d);
$oldHashes = array_map($jsonOf, $oldFiles);
$migration = require $root . '/migrations/0003_projects_v65.php';
$dry = ($migration['up'])(true);
$check('migrace 0003: dry-run nic nezapíše (žádný sidecar neexistuje) a hlásí 5 souborů', $dry['changed'] === 5 && !is_file(STORAGE_DIR . '/projects_v65_cycle.json.php'));
$run1 = ($migration['up'])(false);
$check('migrace 0003: první běh vytvoří 5 sidecarů', $run1['changed'] === 5 && count(array_filter($migration['files'], static fn(string $f): bool => is_file(STORAGE_DIR . '/' . $f))) === 5);
$sidecarHashes = array_map($jsonOf, $migration['files']);
$run2 = ($migration['up'])(false);
$check('migrace 0003: druhý běh je idempotentní (changed=0, sidecary beze změny)', $run2['changed'] === 0 && array_map($jsonOf, $migration['files']) === $sidecarHashes);
$check('migrace 0003: staré soubory projektů mají stejný SHA-256 před i po migraci', array_map($jsonOf, $oldFiles) === $oldHashes);
$check('migrace 0003: výchozí režim cyklu je malý (small)', proj65_settings($CLASS, '3a_office_network')['cycle_mode'] === 'small' && storage_read(proj65_path('meta'))['default_mode'] === 'small');
$down = ($migration['down'])();
$check('migrace 0003: rollback smaže jen sidecary v65 a staré soubory zůstanou', $down['removed'] === 5 && array_map($jsonOf, $oldFiles) === $oldHashes && !is_file(STORAGE_DIR . '/projects_v65_cycle.json.php'));
($migration['up'])(false);

// --- 2) Přechody plný / malý ------------------------------------------------------------------------------
$T = static fn(string $mode, string $from, string $to, array $ctx = []): array => proj65_transition($mode, $from, $to, $ctx);
$check('cyklus: plný má 7 kroků a malý 4 (o 3 kratší: bez návrhu, milníků a peer review)', count(PROJ65_STEPS['full']) === 7 && count(PROJ65_STEPS['small']) === 4 && array_values(array_diff(PROJ65_STEPS['full'], PROJ65_STEPS['small'])) === ['proposal', 'in_progress', 'peer_review']);
$check('plný: návrh schvaluje jen učitel (žák → actor), návrh bez pitche (<20 znaků) se nevrátí do proposal', $T('full', 'proposal', 'approved', ['actor' => 'teacher'])['ok'] && $T('full', 'proposal', 'approved', ['actor' => 'student'])['error'] === 'actor'
    && $T('full', 'rejected', 'proposal', ['actor' => 'student', 'pitch_len' => 5])['error'] === 'need_pitch' && $T('full', 'rejected', 'proposal', ['actor' => 'student', 'pitch_len' => 30])['ok']);
$check('plný: odevzdání vyžaduje práci, milníky jsou volitelné (approved → submitted jde i bez nich, in_progress jen když jsou zapnuté)', $T('full', 'approved', 'submitted', ['actor' => 'student'])['error'] === 'need_submission'
    && $T('full', 'approved', 'submitted', ['actor' => 'student', 'has_submission' => true])['ok'] && $T('full', 'approved', 'in_progress', ['actor' => 'student'])['error'] === 'milestones_off' && $T('full', 'approved', 'in_progress', ['actor' => 'student', 'milestones_on' => true])['ok']);
$check('plný: peer review jen když je zapnuté a jsou ≥ 3 odevzdané práce (jinak peer_off / peer_few)', $T('full', 'submitted', 'peer_review', ['actor' => 'teacher', 'submitted_count' => 5])['error'] === 'peer_off'
    && $T('full', 'submitted', 'peer_review', ['actor' => 'teacher', 'peer_on' => true, 'submitted_count' => 2])['error'] === 'peer_few' && $T('full', 'submitted', 'peer_review', ['actor' => 'teacher', 'peer_on' => true, 'submitted_count' => 3])['ok']);
$check('plný: hodnocení vyžaduje publikovanou známku, portfolio jen po hodnocení žákem', $T('full', 'submitted', 'graded', ['actor' => 'teacher'])['error'] === 'need_grade' && $T('full', 'peer_review', 'graded', ['actor' => 'teacher', 'has_grade' => true])['ok']
    && $T('full', 'graded', 'portfolio', ['actor' => 'student'])['ok'] && $T('full', 'graded', 'portfolio', ['actor' => 'teacher'])['error'] === 'actor' && $T('full', 'submitted', 'portfolio', ['actor' => 'student'])['error'] === 'invalid_transition');
$check('malý: nemá proposal, milníky ani peer review; approved → submitted → graded → portfolio', $T('small', 'proposal', 'approved', ['actor' => 'teacher'])['error'] === 'invalid_transition' && $T('small', 'approved', 'in_progress', ['actor' => 'student', 'milestones_on' => true])['error'] === 'invalid_transition'
    && $T('small', 'submitted', 'peer_review', ['actor' => 'teacher', 'peer_on' => true, 'submitted_count' => 9])['error'] === 'invalid_transition' && $T('small', 'approved', 'submitted', ['actor' => 'student', 'has_submission' => true])['ok']
    && $T('small', 'submitted', 'graded', ['actor' => 'teacher', 'has_grade' => true])['ok'] && $T('small', 'graded', 'portfolio', ['actor' => 'student'])['ok']);
$check('přechody: neznámý režim a vrácení k přepracování (graded → approved) učitelem', $T('x', 'approved', 'submitted')['error'] === 'invalid_mode' && $T('small', 'graded', 'approved', ['actor' => 'teacher'])['ok'] && $T('small', 'graded', 'approved', ['actor' => 'student'])['error'] === 'actor');

// --- 3) Zahájení, malý projekt, pitch ----------------------------------------------------------------------
$P1 = '3a_office_network';
$r1 = proj65_start($CLASS, $good, $P1, '');
$rec1 = proj65_get(proj65_ref_cat($CLASS, $P1, 'individual', $good));
$check('start: malý projekt začíná rovnou schválený (bez návrhu), pitch není povinný, záznam má neprůhledné id c65_…', $r1['ok'] && $rec1 !== null && $rec1['state'] === 'approved' && $rec1['mode'] === 'small' && preg_match('/^c65_[0-9a-f]{16}$/', (string)$rec1['id']) === 1 && !str_contains((string)$rec1['id'], 'student'));
$check('start: stejný projekt podruhé = exists a nevznikne duplicita', proj65_start($CLASS, $good, $P1, '')['error'] === 'exists' && count(array_filter(proj65_all(), static fn(array $r): bool => $r['project_id'] === $P1)) === 1);
$check('start: cizí/neznámý projekt a neexistující žák se odmítnou', proj65_start($CLASS, $good, 'neexistuje', '')['error'] === 'invalid' && proj65_start($CLASS, 'class_3a:student:' . str_repeat('0', 24), $P1, '')['error'] === 'invalid');
$sub1 = proj65_submit((string)$rec1['ref'], $good, 'javascript:alert(1)', 'x');
$check('odevzdání: nebezpečný odkaz se odmítne (safe_url), bez odkazu i poznámky need_submission, cizí žák nic nezmění', $sub1['error'] === 'bad_url' && proj65_submit((string)$rec1['ref'], $good, '', '')['error'] === 'need_submission'
    && proj65_submit((string)$rec1['ref'], $player, 'https://example.org/p', 'cizí')['ok'] === false && proj65_get((string)$rec1['ref'])['state'] === 'approved');
$sub2 = proj65_submit((string)$rec1['ref'], $good, 'example.org/prace', str_repeat('ž', 450));
$rec1 = proj65_get((string)$rec1['ref']);
$check('odevzdání: stav submitted, odkaz normalizován na https://, poznámka oříznuta na 400 znaků', $sub2['ok'] && $rec1['state'] === 'submitted' && str_starts_with((string)$rec1['submission']['url'], 'https://') && u_strlen((string)$rec1['submission']['note']) === PROJ65_NOTE_MAX);
$check('pitch/odevzdání nevytvoří důkaz ani známku (nic v project_grades, 0 důkazů ze zdroje project pro v65)', $evCount($good) === 0 && project_grade_find($CLASS, $P1, 'individual', $good) === null);

// --- 4) Hromadné schválení a rozsah ------------------------------------------------------------------------
$mkProposal = static function (string $ref, string $class) use ($NOW): void {
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $class, $NOW): array {
        $all[$ref] = ['ref' => $ref, 'id' => proj65_cycle_id($ref), 'kind' => 'cat', 'class_id' => $class, 'project_id' => '3a_monitoring', 'target_type' => 'individual', 'target_id' => 'x', 'title' => 'T', 'mode' => 'full',
            'owner_key' => 'x', 'pitch' => str_repeat('p', 30), 'state' => 'proposal', 'submission' => null, 'versions' => [], 'draft' => null, 'splits' => [], 'history' => [], 'created_at' => date(DATE_ATOM, $NOW), 'updated_at' => date(DATE_ATOM, $NOW)];
        return $all;
    });
};
$bulkRefs = [];
for ($i = 0; $i < 55; $i++) { $ref = 'cat:class_3a:3a_monitoring:individual:class_3a:student:' . sprintf('%024x', $i + 1); $mkProposal($ref, $CLASS); $bulkRefs[] = $ref; }
$foreign = 'cat:class_2a:3a_monitoring:individual:class_2a:student:' . sprintf('%024x', 999);
$mkProposal($foreign, 'class_2a');
$bulk = proj65_bulk_approve(array_merge($bulkRefs, [$foreign]), $allowAll, 'audit');
$approvedNow = count(array_filter(proj65_all(), static fn(array $r): bool => $r['project_id'] === '3a_monitoring' && $r['state'] === 'approved'));
$check('hromadné schválení: nejvýš ' . PROJ65_BULK_MAX . ' položek najednou (truncated), zbytek zůstane návrhem', $bulk['approved'] === PROJ65_BULK_MAX && $bulk['truncated'] === true && $approvedNow === PROJ65_BULK_MAX);
$bulk2 = proj65_bulk_approve([$foreign, $bulkRefs[54], 'neplatny-ref', 'cat:class_3a:3a_monitoring:individual:class_3a:student:' . sprintf('%024x', 5000)], $allowAll, 'audit');
$check('hromadné schválení: rozsah se ověřuje u každé položky (cizí třída = class_out_of_scope, neexistující = not_found, neplatný ref se zahodí)', ($bulk2['skipped'][$foreign] ?? '') === 'class_out_of_scope'
    && proj65_get($foreign)['state'] === 'proposal' && $bulk2['approved'] === 1 && count($bulk2['skipped']) === 2);
$check('hromadné schválení: schválená položka znovu neprojde (už není návrhem) a odmítnutí respektuje rozsah', (proj65_bulk_approve([$bulkRefs[0]], $allowAll, 'audit')['skipped'][$bulkRefs[0]] ?? '') === 'invalid_transition'
    && proj65_reject($foreign, $allowAll, 'audit')['error'] === 'class_out_of_scope');
foreach (proj65_all() as $ref => $r) if ($r['project_id'] === '3a_monitoring' && $ref !== $foreign) storage_update(proj65_path('cycle'), static function (array $all) use ($ref): array { unset($all[$ref]); return $all; });
storage_update(proj65_path('cycle'), static function (array $all) use ($foreign): array { unset($all[$foreign]); return $all; });

// --- 5) Rubriky --------------------------------------------------------------------------------------------
$tpl = proj65_rubric_template($CLASS, $P1);
$check('rubrika: šablona z katalogu má 4 kritéria × úrovně 1–4, navázaná na kompetence comp62 (net_addressing, net_diagnose, net_security, net_services)', $tpl !== null && count($tpl['criteria']) === 4 && array_column($tpl['criteria'], 'competency') === ['net_addressing', 'net_diagnose', 'net_security', 'net_services']
    && array_reduce($tpl['criteria'], static fn(bool $ok, array $c): bool => $ok && array_keys($c['levels']) === [1, 2, 3, 4], true));
$mk = static fn(int $n): array => array_map(static fn(int $i): array => ['id' => 'k' . $i, 'title' => 'Kritérium ' . $i, 'description' => '', 'max' => 5, 'competency' => '', 'levels' => [1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd']], range(1, $n));
$check('rubrika: validace 3–5 kritérií (2 a 6 selže), neznámá kompetence a prázdný popis úrovně selže, platná projde', proj65_rubric_validate($mk(2), null, $CLASS) === 'criteria_count' && proj65_rubric_validate($mk(6), null, $CLASS) === 'criteria_count'
    && proj65_rubric_validate($mk(3), null, $CLASS) === null && proj65_rubric_validate($mk(5), null, $CLASS) === null
    && proj65_rubric_validate(array_replace_recursive($mk(3), [0 => ['competency' => 'neexistuje']]), null, $CLASS) === 'competency_unknown' && proj65_rubric_validate(array_replace_recursive($mk(3), [1 => ['levels' => [2 => ' ']]]), null, $CLASS) === 'level_text');
$clone = proj65_rubric_clone($CLASS, $P1, ['design' => ['title' => 'Návrh sítě (upraveno)', 'competency' => 'net_services']], 'audit-ucitel');
$cl = proj65_rubric_for((array)proj65_get((string)$rec1['ref']));
$check('rubrika: klon učitele se uloží a použije místo šablony (upravený název a kompetence), počet a id kritérií zůstávají', $clone['ok'] && $cl['source'] === 'clone' && $cl['criteria'][0]['title'] === 'Návrh sítě (upraveno)' && $cl['criteria'][0]['competency'] === 'net_services'
    && array_column($cl['criteria'], 'id') === array_column($tpl['criteria'], 'id'));
$bad = proj65_rubric_clone($CLASS, $P1, ['design' => ['competency' => 'neexistuje']], 'audit');
$check('rubrika: klon s neznámou kompetencí se odmítne a stávající klon zůstane', !$bad['ok'] && $bad['error'] === 'competency_unknown' && proj65_rubric_for((array)proj65_get((string)$rec1['ref']))['criteria'][0]['competency'] === 'net_services');
proj65_rubric_clone($CLASS, $P1, ['design' => ['title' => $tpl['criteria'][0]['title'], 'competency' => 'net_addressing']], 'audit');
$rub = proj65_rubric_for((array)proj65_get((string)$rec1['ref']));
$pAll4 = proj65_levels_to_points($rub, ['design' => 4, 'evidence' => 4, 'security' => 4, 'validation' => 4]);
$pAll1 = proj65_levels_to_points($rub, ['design' => 1, 'evidence' => 1, 'security' => 1, 'validation' => 1]);
$check('převod úrovní na body: samé čtyřky = plný počet (20/20), samé jedničky = 4/20, rubric_scores mají klíče kritérií', $pAll4['points'] === 20 && $pAll4['max'] === 20 && $pAll1['points'] === 4 && array_keys($pAll4['rubric_scores']) === ['design', 'evidence', 'security', 'validation']
    && proj65_levels_to_points($rub, ['design' => 3, 'evidence' => 2, 'security' => 3, 'validation' => 4])['points'] === 4 + 3 + 4 + 5);
$check('návrh známky z rubriky jde přes stávající project_suggested_grade (20/20 = 1, 4/20 = 5)', project_suggested_grade($pAll4['points'], $pAll4['max']) === 1 && project_suggested_grade($pAll1['points'], $pAll1['max']) === 5);
$lv = ['design' => 4, 'evidence' => 3, 'security' => 2, 'validation' => 4];
$check('úroveň 1 bez komentáře (nebo s 19 znaky) se odmítne, s 20 znaky projde, mimo 1–4 selže', proj65_levels_validate($rub, array_replace($lv, ['design' => 1]), []) === 'level1_comment'
    && proj65_levels_validate($rub, array_replace($lv, ['design' => 1]), ['design' => str_repeat('a', 19)]) === 'level1_comment' && proj65_levels_validate($rub, array_replace($lv, ['design' => 1]), ['design' => str_repeat('a', 20)]) === null
    && proj65_levels_validate($rub, array_replace($lv, ['design' => 5]), []) === 'level_range' && proj65_levels_validate($rub, $lv, []) === null && proj65_levels_from_input($rub, ['design' => '9', 'evidence' => '3'])['design'] === null);
$g0 = proj65_grade((string)$rec1['ref'], array_replace($lv, ['security' => 1]), [], [], true, $allowAll, 'audit');
$check('hodnocení: úroveň 1 bez komentáře se neuloží (žádný záznam v project_grades, stav zůstane submitted)', !$g0['ok'] && $g0['error'] === 'level1_comment' && project_grade_find($CLASS, $P1, 'individual', $good) === null && proj65_get((string)$rec1['ref'])['state'] === 'submitted');
$check('hodnocení: cizí třída (rozsah) a stav approved se odmítnou', proj65_grade((string)$rec1['ref'], $lv, [], [], true, static fn(string $c): bool => false, 'audit')['error'] === 'class_out_of_scope');

// --- 6) Důkaz při publikaci, verze, mastery ----------------------------------------------------------------
$gDraft = proj65_grade((string)$rec1['ref'], $lv, [], ['strengths' => 'Silné: adresace'], false, $allowAll, 'audit');
$gr = project_grade_find($CLASS, $P1, 'individual', $good);
$check('koncept hodnocení: uloží draft (rubric_scores, návrh známky), nevytvoří důkaz, žák hodnocení nevidí a stav zůstane submitted', $gDraft['ok'] && ($gr['status'] ?? '') === 'draft' && array_sum((array)$gr['rubric_scores']) === 17 && $evCount($good) === 0
    && proj65_get((string)$rec1['ref'])['state'] === 'submitted' && proj65_get((string)$rec1['ref'])['versions'] === []);
$privateMarker = 'SOUKROME-POZNAMKA-' . bin2hex(random_bytes(3));
storage_update(STORAGE_DIR . '/project_grades.json.php', static function (array $all) use ($privateMarker): array { foreach ($all as $i => $r) if (is_array($r) && ($r['project_id'] ?? '') === '3a_office_network') $all[$i]['private_note'] = $privateMarker; return $all; });
unset($GLOBALS['educanet_runtime_indexes']);
$gPub1 = proj65_grade((string)$rec1['ref'], $lv, [], ['teacher_comment' => 'Dobrá práce'], true, $allowAll, 'audit');
$gr1 = project_grade_find($CLASS, $P1, 'individual', $good);
$rows1 = array_values(array_filter(ev62_read((string)ev62_student_context($CLASS, $good)['id']), static fn(array $r): bool => $r['source'] === 'project'));
$ref12 = proj65_hash12((string)$rec1['ref']);
$check('publikace: stav graded, katalogové hodnocení published, soukromá poznámka zachována, nová verze v sidecaru', $gPub1['ok'] && ($gr1['status'] ?? '') === 'published' && ($gr1['private_note'] ?? '') === $privateMarker && proj65_get((string)$rec1['ref'])['state'] === 'graded'
    && count(proj65_get((string)$rec1['ref'])['versions']) === 1 && $gPub1['version'] === (int)$gr1['version']);
$byComp1 = [];
foreach ($rows1 as $r) $byComp1[$r['competency']] = $r;
$check('důkaz: zdroj project, úroveň 4, artefact_ref proj:v65_<hash12>_v<verze>, skóre (úroveň−1)/3 po kompetencích (4→1,0; 3→0,667; 2→0,333)', count($rows1) === 4 && array_unique(array_column($rows1, 'level')) === [4] && array_unique(array_column($rows1, 'artefact_ref')) === ['proj:v65_' . $ref12 . '_v' . $gPub1['version']]
    && abs($byComp1['net_addressing']['score'] - 1.0) < 0.001 && abs($byComp1['net_diagnose']['score'] - 0.667) < 0.001 && abs($byComp1['net_security']['score'] - 0.333) < 0.001 && abs($byComp1['net_services']['score'] - 1.0) < 0.001);
$ctxGood = ev62_student_context($CLASS, $good);
$cands = ev62_collect_projects($ctxGood);
$v65Cands = array_values(array_filter($cands, static fn(array $c): bool => str_starts_with((string)$c['ref'], 'proj:v65_')));
$legacyForManaged = array_values(array_filter($cands, static fn(array $c): bool => $c['ref'] === 'proj:' . ev62_safe_ref_part((string)($gr1['id'] ?? ''))));
$mappedRows = ev62_candidates_to_rows((string)$ctxGood['id'], 'os_site', $v65Cands);
$check('WARN projektů zmizí: projekt řízený v65 mapuje kompetence přímo (0 nenamapovaných), starý adaptér pro něj nevrací kandidáta bez tagů', count($v65Cands) === 1 && $mappedRows['unmapped'] === 0 && count($mappedRows['rows']) === 4 && $legacyForManaged === []);
$statBefore = $evCount($good);
ev62_sync_student($CLASS, $good, true, false, $NOW);
$check('pull i push dávají tytéž řádky: synchronizace po publikaci nepřidá duplicitní důkazy z v65', $evCount($good) === $statBefore);
$peerAbsent = $evCount($good);
// druhá publikace = verze 2
$lv2 = ['design' => 2, 'evidence' => 2, 'security' => 2, 'validation' => 2];
usleep(1_100_000);
$gPub2 = proj65_grade((string)$rec1['ref'], $lv2, [], [], true, $allowAll, 'audit');
$rows2 = array_values(array_filter(ev62_read((string)$ctxGood['id']), static fn(array $r): bool => $r['source'] === 'project'));
$check('verzování: druhá publikace má vyšší verzi a přidá nové řádky s ref …_v' . ($gPub2['version'] ?? '?') . ' (starší verze zůstává v záznamu, nic se nepřepíše)', $gPub2['ok'] && $gPub2['version'] > $gPub1['version'] && count($rows2) === 8
    && count(array_unique(array_column($rows2, 'artefact_ref'))) === 2);
$allRows = ev62_read((string)$ctxGood['id']);
$recent = m62_latest_versions(array_values(array_filter($allRows, static fn(array $r): bool => $r['competency'] === 'net_addressing' && $r['source'] === 'project')));
$st = m62_compute(array_values(array_filter($allRows, static fn(array $r): bool => $r['source'] === 'project')), $NOW + 86400, ['net_addressing', 'net_diagnose'])['net_addressing'];
$check('mastery: pro stejný základ projektu se bere jen nejvyšší verze (1 řádek v2 místo dvou), skóre odpovídá úrovni 2 (0,333), ne průměru verzí', count($recent) === 1 && str_ends_with((string)$recent[0]['artefact_ref'], '_v' . $gPub2['version']) && $st['n'] === 1 && abs($st['score'] - 0.333) < 0.01);
$check('mastery: řádky jiných zdrojů a jiné reference se verzí nefiltrují', count(m62_latest_versions([['competency' => 'c', 'artefact_ref' => 'lab:x', 'source' => 'lab'], ['competency' => 'c', 'artefact_ref' => 'lab:x', 'source' => 'lab', 'k' => 'b'], ['competency' => 'c', 'artefact_ref' => 'proj:v65_aaaaaaaaaaaa_v1'], ['competency' => 'c', 'artefact_ref' => 'proj:v65_aaaaaaaaaaaa_v3'], ['competency' => 'c', 'artefact_ref' => 'proj:v65_bbbbbbbbbbbb_v1']])) === 4);
$gDraft2 = proj65_grade((string)$rec1['ref'], $lv, [], [], false, $allowAll, 'audit');
$check('koncept po publikaci zveřejněné hodnocení neskryje (stav published zůstane, koncept jen v sidecaru) a nevytvoří další důkaz', $gDraft2['ok'] && project_grade_find($CLASS, $P1, 'individual', $good)['status'] === 'published' && $evCount($good) === 8 && proj65_get((string)$rec1['ref'])['draft'] !== null);
$unknownRec = ['ref' => 'cat:class_3a:3a_office_network:individual:class_3a:student:' . str_repeat('c', 24), 'class_id' => $CLASS, 'project_id' => $P1, 'target_type' => 'individual', 'target_id' => 'class_3a:student:' . str_repeat('c', 24)];
$vers = ['version' => 1, 'published_at' => date(DATE_ATOM), 'levels' => ['design' => 4], 'competencies' => ['design' => 'net_addressing']];
$filesBefore = count(glob(STORAGE_DIR . '/evidence_v62/*.json.php') ?: []);
$unknown = proj65_emit_evidence($unknownRec, $vers);
$notPilot = proj65_emit_evidence(array_replace($unknownRec, ['class_id' => 'class_2a', 'ref' => 'cat:class_2a:x:individual:' . $good, 'target_id' => $good]), $vers);
$check('nejistá identita: důkaz se nezapíše (žádný nový soubor), případ se započítá (skipped=1, reason identity); mimo pilot reason not_pilot a 0 řádků', $unknown['emitted'] === 0 && $unknown['skipped'] === 1 && $unknown['reason'] === 'identity'
    && count(glob(STORAGE_DIR . '/evidence_v62/*.json.php') ?: []) === $filesBefore && $notPilot['reason'] === 'not_pilot' && $notPilot['emitted'] === 0);
$check('hodnocení p60 projektu a obecná rubrika: bez kompetencí se důkaz nezapisuje (reason no_competency)', proj65_emit_evidence($unknownRec, ['version' => 1, 'published_at' => date(DATE_ATOM), 'levels' => ['brief' => 4], 'competencies' => []])['reason'] === 'no_competency' && count(proj65_rubric_generic()['criteria']) === 3);

// --- 7) Peer review ----------------------------------------------------------------------------------------
$P2 = '3a_monitoring';
$check('peer: nastavení projektu (plný + peer) se uloží; mimo plný režim peer neexistuje', proj65_settings_save($CLASS, $P2, 'full', true, true) && proj65_settings($CLASS, $P2) === ['cycle_mode' => 'full', 'peer' => true, 'milestones' => true]
    && proj65_settings_save($CLASS, $P2, 'small', true, true) && proj65_settings($CLASS, $P2)['peer'] === false && proj65_settings_save($CLASS, $P2, 'full', true, false));
$check('peer: plný projekt bez pitche (<20 znaků) se nezahájí, s pitchem začne jako návrh', proj65_start($CLASS, $good, $P2, 'krátké')['error'] === 'need_pitch' && proj65_start($CLASS, $good, $P2, str_repeat('Navrhuji monitoring. ', 3))['ok']
    && proj65_start($CLASS, $player, $P2, str_repeat('Navrhuji firewall. ', 3))['ok'] && proj65_start($CLASS, $none, $P2, str_repeat('Navrhuji alerty. ', 3))['ok']);
$refs2 = [];
foreach ([$good, $player, $none] as $k) $refs2[$k] = proj65_ref_cat($CLASS, $P2, 'individual', $k);
$check('plný cyklus: návrh čeká (state proposal), žák ho neschválí, odevzdat nejde dřív než po schválení', proj65_get($refs2[$good])['state'] === 'proposal' && proj65_submit($refs2[$good], $good, '', 'poznámka')['ok'] === false);
$appr = proj65_bulk_approve(array_values($refs2), $allowAll, 'audit');
$check('plný cyklus: hromadné schválení 3 návrhů, pak nelze peer_open (nic odevzdáno)', $appr['approved'] === 3 && proj65_peer_open($CLASS, $P2, 'audit')['error'] === 'peer_few');
proj65_submit($refs2[$good], $good, 'https://example.org/g', 'práce good');
proj65_submit($refs2[$player], $player, 'https://example.org/p', 'práce player');
$few = proj65_peer_open($CLASS, $P2, 'audit');
proj65_submit($refs2[$none], $none, 'https://example.org/n', 'práce none');
$check('peer: při < 3 odevzdáních se peer review přeskakuje (peer_few) a nikdo nic nedostane', $few['error'] === 'peer_few' && storage_read(proj65_path('peer')) === [] && proj65_get($refs2[$good])['state'] === 'submitted');
$open = proj65_peer_open($CLASS, $P2, 'audit');
$peerRows = storage_read(proj65_path('peer'));
$perAuthor = [];
$perReviewer = [];
foreach ($peerRows as $r) { $perAuthor[$r['ref']][] = $r['reviewer_key']; $perReviewer[$r['reviewer_key']][] = $r['ref']; }
$selfReview = false;
foreach ($peerRows as $r) if (in_array($r['reviewer_key'], proj65_member_keys(proj65_get($r['ref'])), true)) $selfReview = true;
$check('peer: při 3 odevzdáních se otevře (peer_review), každá práce má 2 recenzenty, nikdo nerecenzuje sebe, zátěž je vyvážená (2 recenze na žáka)', $open['ok'] && $open['moved'] === 3 && count($peerRows) === 6 && !$selfReview
    && array_values(array_unique(array_map('count', $perAuthor))) === [2] && array_values(array_unique(array_map('count', $perReviewer))) === [2] && proj65_get($refs2[$good])['state'] === 'peer_review');
$cycleNow = proj65_all();
$authorsMap = array_map(static fn(string $ref): array => proj65_member_keys($cycleNow[$ref]), $refs2);
$check('peer: přidělení je deterministické (stejný vstup = stejný výsledek) a nikdy nepřidělí spoluhráče týmu', proj65_peer_assign([], $authorsMap, [$good, $player, $none], $CLASS, $P2) === proj65_peer_assign([], $authorsMap, [$good, $player, $none], $CLASS, $P2)
    && proj65_peer_forbidden('a', ['a', 'b']) && !proj65_peer_forbidden('c', ['a', 'b']) && proj65_peer_forbidden('', ['a'])
    && array_reduce(proj65_peer_assign([], ['g' => ['a', 'b'], 'h' => ['c']], ['a', 'b', 'c', 'd'], $CLASS, $P2), static fn(bool $ok, array $r): bool => $ok && !($r['ref'] === 'g' && in_array($r['reviewer_key'], ['a', 'b'], true)) && !($r['ref'] === 'h' && $r['reviewer_key'] === 'c'), true));
$task = proj65_reviews_for_reviewer($CLASS, $good)[0];
$taskJson = json_encode($task, JSON_UNESCAPED_UNICODE);
$check('recenzent vidí práci (název, odkaz, poznámku) a nikdy identitu autora (v datech práce ani jméno ani klíč žáka)', $task['work']['title'] !== '' && $task['work']['url'] !== '' && !str_contains(json_encode($task['work']), 'student:') && !str_contains(json_encode($task['work']), 'Audit'));
$valid = ['design' => '3', 'evidence' => '3', 'security' => '3', 'validation' => '3'];
$check('recenze: kratší než 20 znaků (silná stránka i návrh) = text_short, chybějící úroveň = level_range, cizí recenzent nic nezmění', proj65_review_save($task['id'], $good, $valid, 'krátké', str_repeat('n', 25))['error'] === 'text_short'
    && proj65_review_save($task['id'], $good, ['design' => '3'], str_repeat('s', 25), str_repeat('n', 25))['error'] === 'level_range' && proj65_review_save($task['id'], $player === $task['reviewer_key'] ? $none : $player, $valid, str_repeat('s', 25), str_repeat('n', 25))['ok'] === false);
$gradesBeforePeer = $jsonOf('project_grades.json.php');
$evBeforePeer = $evCount($good) . '|' . $evCount($player) . '|' . $evCount($none);
$saved = proj65_review_save($task['id'], $good, $valid, 'Přehledná topologie a jasné popisky zařízení.', 'Zkus doplnit tabulku portů a služeb pro každý server.');
$flaggedTask = array_values(array_filter(proj65_reviews_for_reviewer($CLASS, $player), static fn(array $r): bool => $r['id'] !== $task['id']))[0];
$flaggedSave = proj65_review_save($flaggedTask['id'], $player, $valid, 'Tohle je idiot řešení a nic jiného.', 'Zkus to prostě udělat znovu a líp.');
$stored = storage_read(proj65_path('peer'))[$task['id']];
$check('recenze: uloží se jako submitted (čeká na schválení), nevhodný text (project_feedback_flagged) skončí ve stavu flagged', $saved['ok'] && $stored['state'] === 'submitted' && $flaggedSave['ok'] && storage_read(proj65_path('peer'))[$flaggedTask['id']]['state'] === 'flagged');
$authorRef = (string)$task['ref'];
$beforeApprove = proj65_peer_author_view($authorRef);
$mod = proj65_review_moderate($task['id'], 'approve', $allowAll, 'audit-ucitel');
$afterApprove = proj65_peer_author_view($authorRef);
$viewJson = json_encode($afterApprove, JSON_UNESCAPED_UNICODE);
$check('anonymita: autor text před schválením nevidí; po schválení vidí text, ale view model nemá identitu recenzenta, id recenze ani čas', $beforeApprove === [] && $mod['ok'] && count($afterApprove) === 1 && !str_contains((string)$viewJson, 'student:') && !str_contains((string)$viewJson, 'r65_')
    && !str_contains((string)$viewJson, 'reviewer') && array_keys($afterApprove[0]) === ['strength', 'suggestion', 'levels']);
$teacherRows = proj65_peer_teacher_rows($CLASS);
$check('učitel recenzenta vidí (moderace): řádek obsahuje reviewer_key, kalibraci a flagged je řazen první', in_array($good, array_column($teacherRows, 'reviewer_key'), true) && isset($teacherRows[0]['calibration']['weight']) && $teacherRows[0]['state'] === 'flagged');
$reject = proj65_review_moderate($flaggedTask['id'], 'reject', $allowAll, 'audit-ucitel');
$check('moderace: zamítnutý text autor nikdy neuvidí; cizí třída (rozsah) a neplatné rozhodnutí se odmítnou', $reject['ok'] && proj65_peer_author_view((string)$flaggedTask['ref']) === [] && proj65_review_moderate($task['id'], 'approve', static fn(string $c): bool => false, 'x')['error'] === 'class_out_of_scope' && proj65_review_moderate($task['id'], 'smazat', $allowAll, 'x')['error'] === 'invalid');
$check('peer review nikdy neovlivní skóre/známku/důkaz: záznamy známek (SHA-256) a počty důkazů jsou po recenzích a moderaci beze změny', $jsonOf('project_grades.json.php') === $gradesBeforePeer && $evCount($good) . '|' . $evCount($player) . '|' . $evCount($none) === $evBeforePeer);
$xs = [1, 2, 3, 4, 5, 6];
$ys = [2, 4, 5, 4, 5, 7];
$check('Pearson: fixture r = 0,878 ±0,01; dokonalá shoda = 1; < 6 párů = null; nulový rozptyl = null', abs((float)proj65_pearson($xs, $ys) - 0.8783) < 0.01 && abs((float)proj65_pearson($xs, $xs) - 1.0) < 0.0001 && proj65_pearson([1, 2, 3, 4, 5], [2, 4, 5, 4, 5]) === null && proj65_pearson([3, 3, 3, 3, 3, 3], $ys) === null);
$calGood = proj65_calibration(array_map(null, [1, 2, 3, 4, 3, 2], [1, 2, 3, 4, 4, 2]));
$calDev = proj65_calibration([[1, 3], [2, 2], [3, 3], [4, 4], [2, 2], [3, 3]]);
$calMae = proj65_calibration([[1, 2], [2, 3], [3, 4], [4, 3], [2, 3], [3, 4]]);
$check('kalibrace: shoda → zkalibrovaný (váha 1,0); odchylka ≥ 2 úrovně a MAE > 1 se označí (váha 0,5); bez dat bez odchylky', $calGood['calibrated'] && $calGood['weight'] === 1.0 && !$calGood['deviation'] && $calDev['deviation'] && !$calDev['calibrated'] && $calDev['weight'] === 0.5
    && $calMae['mae'] > 0.99 && proj65_calibration([])['n'] === 0 && !proj65_calibration([])['deviation']);
$grade2 = proj65_grade($refs2[$none], ['design' => 3, 'evidence' => 3, 'security' => 4, 'validation' => 3], [], [], true, $allowAll, 'audit');
$grade2b = proj65_grade($refs2[$player], ['design' => 4, 'evidence' => 2, 'security' => 3, 'validation' => 3], [], [], true, $allowAll, 'audit');
$grade2c = proj65_grade($refs2[$good], ['design' => 4, 'evidence' => 3, 'security' => 3, 'validation' => 2], [], [], true, $allowAll, 'audit');
$pairs = proj65_reviewer_pairs($good);
$check('kalibrace z reálných dat: páry (recenzent × učitel) po kritériích a odchylka od učitele (6 párů dá Pearsona, jinak null)', $grade2['ok'] && $grade2b['ok'] && $grade2c['ok'] && count($pairs) === 4 && proj65_calibration($pairs)['pearson'] === null && count(proj65_reviewer_pairs($good)) === 4);

require __DIR__ . '/lib/v65_audit_part2.php';
