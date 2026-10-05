<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v64 audit · dávka 4: výzva týdne, série s omluvenými dny, odznaky za kompetence, adaptéry robotí ligy/týmových her/arény. */
foreach (['teacher_operations_v46.php', 'identity_v58.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php', 'ops_v58.php', 'app/lib.php',
    'paths_v63.php', 'paths_v63_flow.php', 'paths_v63_class.php', 'paths_v63_actions.php', 'paths_v63_views.php', 'motivation_v61.php', 'badges_v60.php', 'challenges_v64.php'] as $lib) require_once $root . '/' . $lib;
require_once __DIR__ . '/v63_pre.php';
require_once __DIR__ . '/v63_paths_fixtures.php';

$_SESSION = []; // izolace od předchozích sekcí (přihlášený žák z dávky 1)
$C3 = 'class_3a';
$NOW = (int)strtotime('2026-10-14 10:00:00'); // středa
v63fx_seed($tmp);
$keyA = v63fx_key($C3, 'a');
$sidA = v63fx_sid($C3, 'a');

// --- Čisté funkce ---------------------------------------------------------------------------------
$check('výzva: pondělí týdne a klíč týdne (středa 2026-10-14 → 2026-10-12, 2026-W42)', ch64_monday($NOW) === '2026-10-12' && ch64_week_key('2026-10-12') === '2026-W42');
$done = ['2026-10-12' => true, '2026-10-05' => true, '2026-09-28' => true];
$check('série: 3 splněné týdny po sobě = 3', ch64_streak($done, '2026-10-12') === 3);
$check('série: nesplněný aktuální týden sérii nepřeruší (zatím 2), nesplněný minulý ji přeruší', ch64_streak(['2026-10-05' => true, '2026-09-28' => true], '2026-10-12') === 2 && ch64_streak(['2026-09-28' => true], '2026-10-12') === 0);
// Prázdniny: týden 2026-10-26 až 2026-10-30 školní volno? Najdi skutečný celý volný týden v kalendáři a ověř omluvu.
$freeMonday = null;
for ($d = strtotime('2026-09-01'); $d < strtotime('2027-08-31'); $d += 7 * 86400) {
    $m = date('Y-m-d', $d);
    if ((int)date('N', $d) === 1 && ch64_week_is_free($m)) { $freeMonday = $m; break; }
}
$check('série: v kalendáři existuje celý volný týden (prázdniny) pro test omluvených dní', $freeMonday !== null);
if ($freeMonday !== null) {
    $after = date('Y-m-d', strtotime($freeMonday . ' +7 day 12:00:00'));
    $before = date('Y-m-d', strtotime($freeMonday . ' -7 day 12:00:00'));
    $check('série: celý volný týden sérii nepřeruší (týden před + týden po = 2)', ch64_streak([$before => true, $after => true], $after) === 2 && ch64_streak([$before => true], $after) === 1);
}
$rows = [['competency' => 'lnx_ssh', 'source' => 'arena', 'at' => '2026-10-14T09:00:00+02:00'], ['competency' => 'lnx_ssh', 'source' => 'test', 'at' => '2026-10-14T09:00:00+02:00'], ['competency' => 'lnx_text', 'source' => 'game', 'at' => '2026-10-14T09:00:00+02:00'], ['competency' => 'lnx_ssh', 'source' => 'game', 'at' => '2026-10-05T09:00:00+02:00']];
$check('splnění: jen důkaz game|arena pro tu kompetenci v týdnu (test, jiná kompetence a jiný týden se nepočítají)', ch64_week_done($rows, 'lnx_ssh', '2026-10-12') && !ch64_week_done([$rows[1]], 'lnx_ssh', '2026-10-12') && !ch64_week_done([$rows[2]], 'lnx_ssh', '2026-10-12') && !ch64_week_done([$rows[3]], 'lnx_ssh', '2026-10-12'));

// --- Skutečný tok: zafixování týdne, splnění důkazem ----------------------------------------------
$c = ch64_weekly_for($C3, $keyA, $NOW);
$check('výzva týdne: žák v pilotní třídě dostane výzvu s kompetencí z „Co dál“ (a týden se zafixuje)', is_array($c) && $c['competency'] !== '' && $c['week'] === '2026-W42' && !$c['done'] && isset(storage_read(ch64_path(), false)[$sidA]['weeks']['2026-W42']));
$frozen = $c['competency'] ?? '';
$c2 = ch64_weekly_for($C3, $keyA, $NOW + 86400);
$check('výzva týdne: během týdne zůstává stejná kompetence (zafixováno)', is_array($c2) && $c2['competency'] === $frozen);
ev62_append($sidA, [['student_id' => $sidA, 'competency' => $frozen, 'level' => 2, 'source' => 'game', 'score' => 0.6, 'at' => date(DATE_ATOM, $NOW + 3600), 'artefact_ref' => 'tg:fx64']], $NOW + 3600);
$c3 = ch64_weekly_for($C3, $keyA, $NOW + 7200);
$check('výzva týdne: důkaz hry v týdnu = splněno, série 1', is_array($c3) && $c3['done'] === true && $c3['streak'] === 1);
$check('výzva týdne: třída mimo pilot (2.A) nedostane nic a nic nezapíše', ch64_weekly_for('class_2a', 'class_2a:student:x', $NOW) === null);
ob_start();
ch64_render_card($C3, $keyA);
$cardHtml = (string)ob_get_clean();
$check('výzva týdne: karta se vykreslí bez nového CSS (třídy ui-card/p63-card), s h2, bez XP/bodů v textu', str_contains($cardHtml, 'class="p63-card ui-card"') && str_contains($cardHtml, '<h2') && !preg_match('/\b\d+ (XP|bod)/u', $cardHtml));

// --- Odznaky za kompetence ------------------------------------------------------------------------
$award1 = ch64_award_competency_badges($sidA, ['lnx_ssh' => ['state' => 'zvladnuto'], 'lnx_text' => ['state' => 'rozpracovano'], 'lnx_users' => ['state' => 'neovereno']], $NOW);
$award2 = ch64_award_competency_badges($sidA, ['lnx_ssh' => ['state' => 'zvladnuto']], $NOW + 60);
$check('odznaky: jen za zvládnuto/upevněno (ne rozpracováno), jednou (podruhé nic)', $award1 === ['comp64_lnx_ssh_zvladnuto'] && $award2 === []);
$award3 = ch64_award_competency_badges($sidA, ['lnx_ssh' => ['state' => 'upevneno']], $NOW + 120);
$check('odznaky: upevněno je nový odznak, původní zůstává', $award3 === ['comp64_lnx_ssh_upevneno'] && count(ch64_badges_of($sidA)) === 2);
$html = ch64_render_badges($C3, $keyA);
$check('odznaky: vykreslení přes badge60_card (získané, rarita)', str_contains($html, 'is-earned') && str_contains($html, 'b60-card'));
$gameOnly = m62_compute([['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'game', 'score' => 1.0, 'at' => date(DATE_ATOM, $NOW - 30 * 86400)], ['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'arena', 'score' => 1.0, 'at' => date(DATE_ATOM, $NOW)]], $NOW, ['lnx_ssh']);
$check('hra/aréna samy nikdy nedají „upevněno“ ani „zvládnuto“ (m62) – tedy ani odznak', !in_array($gameOnly['lnx_ssh']['state'], ['zvladnuto', 'upevneno'], true) && ch64_award_competency_badges($sidA, $gameOnly, $NOW + 200) === []);

// --- Adaptéry ev62 ---------------------------------------------------------------------------------
$check('adaptér robotů: umístění → skóre 0,8 (1.) až 0,5 (poslední), max 0,8', ev62_robots_score(1, 10) === 0.8 && ev62_robots_score(10, 10) === 0.5 && ev62_robots_score(5, 10) > 0.5 && ev62_robots_score(1, 1) === 0.8);
$ctx = ev62_student_context($C3, $keyA);
file_put_contents($tmp . '/robots_v58.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode(['matches' => [
    ['id' => 'abcdef123456', 'class_id' => $C3, 'mode' => 'solo', 'ran_at' => date(DATE_ATOM, $NOW), 'results' => ['robots' => [['key' => $keyA, 'rank' => 2], ['key' => 'jiny', 'rank' => 1], ['key' => 'treti', 'rank' => 3]], 'teams' => []]],
    ['id' => 'abcdef999999', 'class_id' => 'class_1a', 'mode' => 'solo', 'ran_at' => date(DATE_ATOM, $NOW), 'results' => ['robots' => [['key' => $keyA, 'rank' => 1]], 'teams' => []]],
    ['id' => 'abcdef000000', 'class_id' => $C3, 'mode' => 'solo', 'ran_at' => '', 'results' => null],
]]));
$rob = ev62_collect_robots_v58($ctx);
$check('adaptér robotů: jen odehraný zápas vlastní třídy, tag robots:algo, zdroj game, skóre 0,65 pro 2. ze 3', count($rob) === 1 && $rob[0]['tags'] === ['robots:algo'] && $rob[0]['source'] === 'game' && $rob[0]['score'] === 0.65);
$check('adaptér robotů je v registru adaptérů a tag robots:algo je namapován na kompetenci v katalogu 3.A', isset(ev62_adapters()['robots_v58']) && comp62_match('os_site', ['robots:algo']) !== []);
$check('adaptér týmových her: tag tg:graphics se mapuje na kompetence grafiky (1.A)', comp62_match('grafika_web', ['tg:graphics']) !== []);
$sess = ['id' => 'abc12345', 'status' => 'finished', 'finished_at' => date(DATE_ATOM, $NOW), 'line' => 'networks', 'teams' => [['id' => 't1', 'members' => [$keyA, 'x', 'y']]], 'contrib' => []];
$lazy = ev62_teamgame_candidate($sess, $keyA);
for ($i = 0; $i < 6; $i++) $sess = tg64_actor_note($sess, $keyA, 'ok');
$worker = ev62_teamgame_candidate($sess, $keyA);
$sess2 = tg64_actor_note($sess, 'x', 'ok');
$check('týmová hra: skóre podle přínosu (bez přínosu v záznamu 0,6 jako dřív; nositel 0,8; max 0,8)', $lazy['score'] === 0.6 && $worker['score'] === 0.8 && ev62_teamgame_candidate($sess2, 'x')['score'] < 0.8);
$sessFree = $sess;
$sessFree['contrib']['y'] = ['ok' => 0, 'wrong' => 0, 'signal' => 0];
$check('týmová hra: člen bez práce dostane jen 0,3 (nelze jet na černo)', ev62_teamgame_candidate($sessFree, 'y')['score'] === 0.3);
$check('aréna 1v1: poražený podle podílu kroků (0,4–0,6; stejný počet kroků = 0,6, žádné = 0,4)', ev62_arena60_loser_score_from(5, 5) === 0.6 && ev62_arena60_loser_score_from(0, 5) === 0.4 && ev62_arena60_loser_score_from(2, 4) === 0.5 && ev62_arena60_loser_score_from(9, 3) === 0.6);
$check('hra nikdy „upevněno“: tg+robots+arena jen ze her → stav ≤ rozpracováno', (function () use ($NOW): bool {
    $rows = [];
    foreach (['game', 'arena'] as $i => $src) foreach ([1, 20, 40] as $d) $rows[] = ['competency' => 'lnx_ssh', 'level' => 2, 'source' => $src, 'score' => 0.8, 'at' => date(DATE_ATOM, $NOW - $d * 86400)];
    return m62_compute($rows, $NOW, ['lnx_ssh'])['lnx_ssh']['state'] === 'rozpracovano';
})());
