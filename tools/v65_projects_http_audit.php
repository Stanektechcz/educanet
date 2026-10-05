<?php

declare(strict_types=1);

/**
 * EDUCANET v65 · HTTP smoke audit projektů (vestavěný server nad dočasným úložištěm, fiktivní žáci „Audit …“).
 *   php tools/v65_projects_http_audit.php
 * Cesta žáka přes HTTP: návrh → (učitel schválí) → odevzdání → 2 recenze → (učitel moderuje a hodnotí) → portfolio → export HTML;
 * týmový projekt: milník, deník, rozdělení 100 bodů → návrh faktoru ±20 %; POST bez CSRF a cizí id záznamu jsou odmítnuty.
 * Kroky učitele volají knihovny (cockpit je pokrytý auditem v65 a v59 scope), žákovské kroky jdou skutečnými HTTP požadavky.
 * Konec: V65_PROJECTS_HTTP_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v65-http')), '/');
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php',
    'projects_v65.php', 'projects_v65_rubrics.php', 'projects_v65_peer.php', 'projects_v65_team.php', 'projects_v65_evidence.php', 'portfolio_v65.php', 'ops_v58.php', 'app/lib.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v62_competency_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));
v62fx_seed($tmp, time());
$CLASS = V62FX_CLASS;
$allow = static fn(string $c): bool => $c === $CLASS;
$fresh = static function (): void { unset($GLOBALS['educanet_runtime_indexes']); };
$P2 = '3a_monitoring';
$P3 = '3a_incident_team';
proj65_settings_save($CLASS, $P2, 'full', true, false);
proj65_settings_save($CLASS, $P3, 'full', false, true);
$keys = ['good' => v62fx_key('good'), 'player' => v62fx_key('player'), 'none' => v62fx_key('none')];
$group = project_save_group(['class_id' => $CLASS, 'project_id' => $P3, 'name' => 'Audit tým HTTP', 'member_keys' => array_values($keys)]);
$fresh();

$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $enter = static function (string $who) use ($h): void { $h->request('GET', '/?class=' . V62FX_CLASS . '&student=' . rawurlencode(V62FX_LABELS[$who])); };
    $get = static fn(string $path): array => $h->request('GET', $path);
    $post = static function (string $action, array $fields, string $page = '/?view=projekt65') use ($h): array {
        $token = (string)$h->csrfToken($h->request('GET', $page)['body']);
        return $h->request('POST', '/?view=projekt65', ['csrf' => $token, 'action' => $action] + $fields);
    };
    $idFor = static function (string $ref): string { return proj65_cycle_id($ref); };

    // 1) Návrh žáků přes HTTP
    $enter('good');
    $home = $get('/?view=projekt65');
    $check('HTTP: přehled projektů se vykreslí (200), má h1 a formulář návrhu s CSRF tokenem', $home['status'] === 200 && str_contains($home['body'], '<h1>Moje projekty</h1>') && str_contains($home['body'], 'proj65_s_start') && $h->csrfToken($home['body']) !== null);
    $noCsrf = $h->request('POST', '/?view=projekt65', ['action' => 'proj65_s_start', 'project_id' => $P2, 'pitch' => str_repeat('Návrh bez tokenu. ', 3)]);
    $badCsrf = $h->request('POST', '/?view=projekt65', ['csrf' => 'podvrzeny', 'action' => 'proj65_s_start', 'project_id' => $P2, 'pitch' => str_repeat('Návrh s falešným tokenem. ', 3)]);
    $fresh();
    $check('HTTP: POST bez CSRF a s falešným CSRF se odmítne (419) a nic se nezapíše', $noCsrf['status'] === 419 && $badCsrf['status'] === 419 && proj65_all() === []);
    foreach (['good', 'player', 'none'] as $who) {
        $enter($who);
        $r = $post('proj65_s_start', ['project_id' => $P2, 'pitch' => str_repeat('Navrhuji monitoring služby. ', 2)]);
    }
    $fresh();
    $refs = [];
    foreach ($keys as $k) $refs[$k] = proj65_ref_cat($CLASS, $P2, 'individual', $k);
    $check('HTTP: tři žáci poslali návrh, záznamy jsou ve stavu proposal a odpověď přesměruje na detail (200)', $r['status'] === 200 && count(proj65_all()) === 3 && array_unique(array_column(proj65_all(), 'state')) === ['proposal'] && str_contains($r['body'], 'čeká na schválení'));
    $enter('good');
    $foreign = $get('/?view=projekt65&id=' . $idFor($refs[$keys['player']]));
    $check('HTTP: detail cizího záznamu (id jiného žáka) se neotevře – žák dostane jen svůj přehled, ne cizí kroky', $foreign['status'] === 200 && !str_contains($foreign['body'], 'p65-steps') && str_contains($foreign['body'], 'Moje projekty'));
    $bad = $h->request('POST', '/?view=projekt65', ['csrf' => (string)$h->csrfToken($get('/?view=projekt65')['body']), 'action' => 'proj65_s_submit', 'id' => $idFor($refs[$keys['player']]), 'url' => 'https://example.org/x', 'note' => 'cizí']);
    $fresh();
    $check('HTTP: odevzdání za cizí záznam se nepovede (stav i odevzdání cizího žáka beze změny)', proj65_get($refs[$keys['player']])['state'] === 'proposal' && proj65_get($refs[$keys['player']])['submission'] === null);

    // 2) Schválení učitelem, odevzdání přes HTTP
    $bulk = proj65_bulk_approve(array_values($refs), $allow, 'audit-ucitel');
    $fresh();
    $check('učitel: hromadné schválení 3 návrhů (knihovna)', $bulk['approved'] === 3);
    foreach (['good', 'player', 'none'] as $who) {
        $enter($who);
        $detail = $get('/?view=projekt65&id=' . $idFor($refs[$keys[$who]]));
        $sub = $post('proj65_s_submit', ['id' => $idFor($refs[$keys[$who]]), 'url' => 'https://example.org/' . $who, 'note' => 'Práce ' . $who], '/?view=projekt65&id=' . $idFor($refs[$keys[$who]]));
    }
    $fresh();
    $check('HTTP: detail schváleného projektu nabízí odevzdání, po odevzdání jsou všechny tři záznamy submitted', str_contains($detail['body'], 'proj65_s_submit') && array_unique(array_column(proj65_all(), 'state')) === ['submitted'] && str_contains($sub['body'], 'Odevzdáno'));

    // 3) Peer review: učitel otevře, žáci píší 2 recenze
    $open = proj65_peer_open($CLASS, $P2, 'audit-ucitel');
    $fresh();
    $check('učitel: peer review otevřeno (3 odevzdané práce) a každý žák má 2 recenze k napsání', $open['ok'] && count(storage_read(proj65_path('peer'))) === 6);
    $enter('good');
    $page = $get('/?view=projekt65');
    preg_match_all('/name="review_id" value="(r65_[0-9a-f]{16})"/', $page['body'], $rid);
    $check('HTTP: žák vidí 2 recenzní úkoly „Práce č. N“ s nápovědou vět a bez jména autora', count($rid[1]) === 2 && str_contains($page['body'], 'Práce č. 1') && str_contains($page['body'], 'data-p65-starter') && !str_contains($page['body'], V62FX_LABELS['player']) && !str_contains($page['body'], V62FX_LABELS['none']));
    $short = $post('proj65_s_review', ['review_id' => $rid[1][0], 'levels[design]' => '3', 'strength' => 'krátké', 'suggestion' => 'taky krátké']);
    $fresh();
    $check('HTTP: příliš krátká recenze se nepřijme (zůstane assigned)', storage_read(proj65_path('peer'))[$rid[1][0]]['state'] === 'assigned' && str_contains($short['body'], 'moc krátký'));
    $levelsRubric = proj65_rubric_for((array)proj65_get($refs[$keys['good']]));
    foreach (['good', 'player', 'none'] as $who) {
        $enter($who);
        $mine = [];
        preg_match_all('/name="review_id" value="(r65_[0-9a-f]{16})"/', $get('/?view=projekt65')['body'], $mine);
        foreach ($mine[1] as $i => $reviewId) {
            $fields = ['review_id' => $reviewId, 'strength' => 'Dobře vybrané metriky a přehledný dashboard č. ' . $i, 'suggestion' => 'Zkus přidat prahové hodnoty a komu se pošle upozornění č. ' . $i];
            foreach ($levelsRubric['criteria'] as $c) $fields['levels[' . $c['id'] . ']'] = '3';
            $rr = $post('proj65_s_review', $fields);
        }
    }
    $fresh();
    $peerStates = array_count_values(array_column(storage_read(proj65_path('peer')), 'state'));
    $check('HTTP: všech 6 recenzí odesláno (submitted, čekají na schválení), autor je ještě nevidí', ($peerStates['submitted'] ?? 0) === 6 && str_contains($rr['body'], 'po schválení') && proj65_peer_author_view($refs[$keys['good']]) === []);
    foreach (storage_read(proj65_path('peer')) as $rev) proj65_review_moderate((string)$rev['id'], 'approve', $allow, 'audit-ucitel');
    $fresh();
    $enter('good');
    $authorPage = $get('/?view=projekt65&id=' . $idFor($refs[$keys['good']]));
    $check('HTTP: po schválení autor vidí anonymní texty (bez jmen recenzentů a id recenze) a stav peer_review', str_contains($authorPage['body'], 'Zpětná vazba od spolužáků') && str_contains($authorPage['body'], 'Dobře vybrané metriky') && !str_contains($authorPage['body'], V62FX_LABELS['player']) && !str_contains($authorPage['body'], V62FX_LABELS['none']) && !str_contains($authorPage['body'], 'r65_'));

    // 4) Hodnocení, portfolio, export
    $lv = ['design' => 4, 'evidence' => 3, 'security' => 3, 'validation' => 4];
    $g = proj65_grade($refs[$keys['good']], $lv, [], ['teacher_comment' => 'Dobrá práce'], true, $allow, 'audit-ucitel');
    $fresh();
    $check('učitel: hodnocení rubrikou zveřejněno, důkaz kompetence zapsán', $g['ok'] && ($g['evidence']['emitted'] ?? 0) === count(array_unique(array_values(proj65_criteria_competencies(proj65_rubric_for((array)proj65_get($refs[$keys['good']])))))));
    $enter('good');
    $res = $get('/?view=projekt65&id=' . $idFor($refs[$keys['good']]));
    $check('HTTP: žák vidí hodnocení (úrovně kritérií, verze 1) a odkaz do portfolia', str_contains($res['body'], '<h2>Hodnocení</h2>') && str_contains($res['body'], '4 · Výborné') && str_contains($res['body'], 'view=portfolio'));
    $pf = $get('/?view=portfolio');
    $check('HTTP: portfolio (200) nabízí ohodnocený projekt, stažení HTML a tisk', $pf['status'] === 200 && str_contains($pf['body'], 'proj65_s_portfolio_save') && str_contains($pf['body'], 'view=portfolio_export') && str_contains($pf['body'], 'data-p65-print'));
    $itemKey = 'c:' . $idFor($refs[$keys['good']]);
    $psave = $h->request('POST', '/?view=portfolio', ['csrf' => (string)$h->csrfToken($pf['body']), 'action' => 'proj65_s_portfolio_save', 'key' => $itemKey, 'selected' => '1', 'reflection' => 'Naučil jsem se nastavit prahy. <script>alert(1)</script>']);
    $fresh();
    $export = $get('/?view=portfolio_export');
    $hdr = array_change_key_case($export['headers'], CASE_LOWER);
    $check('HTTP: export portfolia je HTML attachment (Content-Disposition, CSP default-src none, nosniff), reflexe escapovaná, bez <script a externích zdrojů',
        $export['status'] === 200 && str_contains((string)($hdr['content-disposition'] ?? ''), 'attachment') && str_contains((string)($hdr['content-security-policy'] ?? ''), "default-src 'none'") && ($hdr['x-content-type-options'] ?? '') === 'nosniff'
        && str_contains($export['body'], '&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains(strtolower($export['body']), '<script') && !str_contains($export['body'], 'http://') && !str_contains($export['body'], 'https://') && !str_contains($export['body'], 'Zpětná vazba'));
    $check('HTTP: zařazená práce přešla do stavu portfolio a export neobsahuje texty spolužáků (peer)', proj65_get($refs[$keys['good']])['state'] === 'portfolio' && !str_contains($export['body'], 'Dobře vybrané metriky') && !str_contains($export['body'], 'prahové hodnoty'));
    $enter('player');
    $other = $get('/?view=portfolio_export');
    $check('HTTP: export jiného žáka je jeho vlastní (nesdílí se odkazem, nejde zadat cizí identitu parametrem)', !str_contains($other['body'], 'Naučil jsem se') && $get('/?view=portfolio_export&student=' . rawurlencode(V62FX_LABELS['good']))['body'] !== $export['body']);

    // 5) Týmový projekt přes HTTP
    $teamRef = proj65_ref_cat($CLASS, $P3, 'group', (string)$group['id']);
    foreach (['good'] as $who) { $enter($who); $post('proj65_s_start', ['project_id' => $P3, 'pitch' => str_repeat('Tým navrhuje postup řešení. ', 2)]); }
    $fresh();
    proj65_bulk_approve([$teamRef], $allow, 'audit-ucitel');
    $fresh();
    $teamId = $idFor($teamRef);
    $enter('player');
    $td = $get('/?view=projekt65&id=' . $teamId);
    $check('HTTP: spoluhráč týmu vidí týmový projekt (milníky, deník, rozdělení bodů)', str_contains($td['body'], 'p65-kanban') && str_contains($td['body'], 'proj65_s_diary') && str_contains($td['body'], 'proj65_s_split'));
    $post('proj65_s_ms_add', ['id' => $teamId, 'title' => 'Návrh řešení incidentu'], '/?view=projekt65&id=' . $teamId);
    $fresh();
    $ms = array_values(storage_read(proj65_path('milestones')));
    $post('proj65_s_ms_move', ['id' => $teamId, 'ms_id' => $ms[0]['id'], 'status' => 'doing'], '/?view=projekt65&id=' . $teamId);
    $post('proj65_s_diary', ['id' => $teamId, 'text' => str_repeat('d', 300)], '/?view=projekt65&id=' . $teamId);
    $fresh();
    $diary = proj65_diary_list($teamRef);
    $check('HTTP: milník přidán a přesunut, zápis deníku oříznut na 280 znaků (vidí tým i učitel)', count($ms) === 1 && storage_read(proj65_path('milestones'))[$ms[0]['id']]['status'] === 'doing' && count($diary) === 1 && u_strlen((string)$diary[0]['text']) === 280);
    $splitPlan = ['good' => [$keys['player'] => 70, $keys['none'] => 30], 'player' => [$keys['good'] => 50, $keys['none'] => 50], 'none' => [$keys['good'] => 90, $keys['player'] => 10]];
    foreach ($splitPlan as $who => $pts) {
        $enter($who);
        $fields = ['id' => $teamId];
        foreach ($pts as $k => $v) $fields['points[' . $k . ']'] = (string)$v;
        $post('proj65_s_split', $fields, '/?view=projekt65&id=' . $teamId);
    }
    $enter('none');
    $badSplit = $post('proj65_s_split', ['id' => $teamId, 'points[' . $keys['good'] . ']' => '60', 'points[' . $keys['player'] . ']' => '60'], '/?view=projekt65&id=' . $teamId);
    $fresh();
    $check('HTTP: rozdělení bodů s neplatným součtem se neuloží, platná rozdělení všech tří členů ano', count((array)proj65_get($teamRef)['splits']) === 3 && proj65_get($teamRef)['splits'][$keys['none']] === [$keys['good'] => 90, $keys['player'] => 10]);
    $enter('good');
    $subTeam = $post('proj65_s_submit', ['id' => $teamId, 'url' => 'https://example.org/team', 'note' => 'tým'], '/?view=projekt65&id=' . $teamId);
    $fresh();
    $tg = proj65_grade($teamRef, ['design' => 3, 'evidence' => 3, 'security' => 3, 'validation' => 3], [], [], true, $allow, 'audit-ucitel');
    $fresh();
    $grade = project_grade_find($CLASS, $P3, 'group', (string)$group['id']);
    $delta = proj65_factor_suggestion((array)proj65_get($teamRef));
    $check('učitel: návrh faktoru přínosu je v rozsahu ±20 % bodů týmu a sám nic nezapíše', $tg['ok'] && $delta !== null && max(array_map('abs', $delta)) <= (int)ceil(0.2 * (int)$grade['points']) && array_sum(array_map(static fn(array $m): int => abs((int)($m['points_delta'] ?? 0)), (array)$grade['member_adjustments'])) === 0);
    $applied = proj65_factor_apply($teamRef, $allow);
    $fresh();
    $after = project_grade_find($CLASS, $P3, 'group', (string)$group['id']);
    $check('učitel: po potvrzení je points_delta zapsán a hodnocení zůstává zveřejněné', $applied['ok'] && (int)$after['member_adjustments'][$keys['good']]['points_delta'] === $delta[$keys['good']] && $after['status'] === 'published');
    $enter('good');
    $teamAfter = $get('/?view=projekt65&id=' . $teamId);
    $check('HTTP: po hodnocení týmu žák vidí výsledek a stránky nehlásí PHP chyby', str_contains($teamAfter['body'], '<h2>Hodnocení</h2>') && audit_response_clean($teamAfter) && audit_response_clean($authorPage) && audit_response_clean($export) && audit_response_clean($pf));
} finally {
    $h->stop();
}

exit(audit_summary($state, 'V65_PROJECTS_HTTP'));
