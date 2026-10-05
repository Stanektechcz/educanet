<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v64 audit · dávka 6: HTTP přes skutečný dev server nad dočasným úložištěm (dashboard výzva týdne, profil s ligou,
 * týmová hra s retrospektivou, CSRF, cockpit Ekonomika). Proměnné: $check, $tmp, $root.
 */
require_once __DIR__ . '/http_harness.php';
foreach (['teamgames_v58_registry.php', 'teamgames_v58_game_escape.php', 'paths_v63.php'] as $lib) require_once $root . '/' . $lib;
if (!function_exists('v63fx_seed')) require_once __DIR__ . '/v63_paths_fixtures.php';

$C3 = 'class_3a';
$keyH = v63fx_key($C3, 'b');
$sidH = v63fx_sid($C3, 'b');
$labelH = V63FX_LABELS[$C3]['b'];
$gameId = 'aud64game1';
$now = time();
$fixtureSession = ['id' => $gameId, 'class_id' => 'class_3a', 'type' => 'escape', 'line' => 'graphics', 'title' => 'Audit hra 64', 'names' => 'initials', 'status' => 'finished',
    'created_at' => date(DATE_ATOM, $now - 3600), 'started_at' => date(DATE_ATOM, $now - 3000), 'finished_at' => date(DATE_ATOM, $now - 600), 'teams' => [['id' => 't1', 'name' => 'Tým A', 'members' => [$keyH, 'class_3a:student:cizi']]],
    'signals' => [], 'contrib' => []];
$fixtureSession = tg58_escape_on_start($fixtureSession, ['teams' => $fixtureSession['teams']]);
tg58_update(tg58_session_path($gameId), static fn(array $d): array => $fixtureSession);
tg58_update(tg58_index_path(), static fn(array $d): array => ['entries' => [['id' => $gameId, 'class_id' => 'class_3a', 'type' => 'escape', 'created_at' => date(DATE_ATOM, $now - 3600)]]]);

$teacherKey = 'audit-v64-teacher-key-' . bin2hex(random_bytes(4));
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey]);
try {
    $login = audit_login_student($h, $C3, $labelH);
    $csrf = (string)$login['csrf'];
    $dash = $h->request('GET', '/?view=dashboard');
    $check('HTTP: přehled má kartu „Výzva týdne“ (h2, odkaz do hry, bez nového CSS souboru arena-v64) pod „Co dál“', $dash['status'] === 200 && str_contains($dash['body'], 'id="ch64-title"') && str_contains($dash['body'], 'Výzva týdne')
        && (int)strpos($dash['body'], 'ch64-title') > (int)strpos($dash['body'], 'p63-next-title') && !str_contains($dash['body'], 'arena-v64.css') && audit_response_clean($dash));
    $prof = $h->request('GET', '/?view=profile&tab=arena');
    $check('HTTP: profil → Aréna ukazuje „Moje liga“ (název ligy bez čísla ELO), vyrovnané soupeře a zlepšení; bez PHP chyb', $prof['status'] === 200 && str_contains($prof['body'], 'Moje liga') && str_contains($prof['body'], 'liga') && str_contains($prof['body'], 'Kdo se za 14 dní')
        && !preg_match('/\bELO\b/', strip_tags($prof['body'])) && audit_response_clean($prof));
    $page = $h->request('GET', '/?view=hry&hra=' . $gameId);
    $check('HTTP: dohraná týmová hra má formulář retrospektivy se 3 otázkami (label, textarea maxlength 280, CSRF); bez PHP chyb', $page['status'] === 200 && str_contains($page['body'], 'name="action" value="tg64_retro"') && substr_count($page['body'], '<textarea') === 3
        && str_contains($page['body'], 'maxlength="280"') && substr_count($page['body'], '<label for="tg64-q') === 3 && audit_response_clean($page));
    $noCsrf = $h->request('POST', '/', ['action' => 'tg64_retro', 'game' => $gameId, 'a' => ['Nic', '', '']], ['follow_redirects' => false]);
    $badCsrf = $h->request('POST', '/', ['action' => 'tg64_retro', 'game' => $gameId, 'a' => ['Nic', '', ''], 'csrf' => 'podvrzeny'], ['follow_redirects' => false]);
    $check('HTTP: POST tg64_retro bez CSRF a s cizím CSRF se odmítne (419) a nic se neuloží', $noCsrf['status'] === 419 && $badCsrf['status'] === 419 && !tg64_retro_done($C3, $gameId, 't1', $keyH));
    $ok = $h->request('POST', '/', ['action' => 'tg64_retro', 'game' => $gameId, 'a' => ['Komunikace', 'Čas na kvízu', 'Víc kontroly'], 'csrf' => $csrf], ['follow_redirects' => true]);
    $check('HTTP: POST tg64_retro s CSRF uloží retrospektivu (žák v týmu, hra dohraná), stránka potvrdí', tg64_retro_done($C3, $gameId, 't1', $keyH) && str_contains($ok['body'], 'retrospektiva je'));
    $foreign = $h->request('POST', '/', ['action' => 'tg64_retro', 'game' => 'neexistujici1', 'a' => ['x', '', ''], 'csrf' => $csrf], ['follow_redirects' => true]);
    $check('HTTP: retrospektiva k neexistující hře se neuloží (soubor retrospektiv zná jen vlastní hru)', array_keys(storage_read(tg64_retro_path())) === [$gameId] && $foreign['status'] === 200);

    $tl = audit_login_teacher($h, $teacherKey);
    $tab = $h->request('GET', '/teacher.php?tab=ekonomika&class=class_3a');
    $section = preg_match('/<section class="teacher-panel eco64-t-wrap">.*?<\/section>/s', $tab['body'], $secM) === 1 ? $secM[0] : '';
    $check('HTTP cockpit: ?tab=ekonomika je 200, má report inflace, přepínače žebříčku a retrospektivy BEZ jmen žáků (jen Tým t1)', $tl['response']['status'] === 200 && $tab['status'] === 200 && str_contains($tab['body'], 'Měsíční report inflace XP') && str_contains($tab['body'], 'name="action" value="v64_abs_board"')
        && str_contains($section, 'Komunikace') && !str_contains($section, 'Audit Cesta') && !str_contains($section, 'Audit Cesta') && !str_contains($section, $keyH) && audit_response_clean($tab));
    $tCsrf = (string)$h->csrfToken($tab['body']);
    $h->request('POST', '/teacher.php', ['action' => 'v64_abs_board', 'class_id' => $C3, 'kind' => 'robots', 'id' => '', 'on' => '1'], ['follow_redirects' => false]);
    $offBefore = fair64_absolute_board_enabled('robots', $C3);
    $h->request('POST', '/teacher.php', ['action' => 'v64_abs_board', 'class_id' => $C3, 'kind' => 'robots', 'id' => '', 'on' => '1', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $onAfter = fair64_absolute_board_enabled('robots', $C3);
    $check('HTTP cockpit: v64_abs_board bez CSRF nic nezmění; s CSRF zapne absolutní žebříček robotí ligy (výchozí vypnutý)', $offBefore === false && $onAfter === true);
    $h->request('POST', '/teacher.php', ['action' => 'v64_abs_board', 'class_id' => $C3, 'kind' => 'robots', 'id' => '', 'on' => '0', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $h->request('POST', '/teacher.php', ['action' => 'v64_neznama', 'class_id' => $C3, 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $h->request('POST', '/teacher.php', ['action' => 'v64_abs_board', 'class_id' => $C3, 'kind' => 'hack', 'id' => 'x', 'on' => '1', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $check('HTTP cockpit: vypnutí funguje; neznámá akce v64_* a neznámý druh akce nic nezapíšou', !fair64_absolute_board_enabled('robots', $C3) && !fair64_absolute_board_enabled('hack', $C3, 'x'));
    $badMonth = $h->request('GET', '/teacher.php?tab=ekonomika&class=class_3a&month=2026-13');
    $check('HTTP cockpit: neplatný měsíc se ošetří (200, report za aktuální měsíc), PHP chyba se nezobrazí', $badMonth['status'] === 200 && audit_response_clean($badMonth));
} finally {
    $h->stop();
}
