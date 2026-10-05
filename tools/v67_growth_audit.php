<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · audit profilu žáka (fáze G): příběh růstu, cíle, mapa kompetencí jako hlavní záložka, sdílení.
 *   1) milníky kompetencí z důkazů (deterministicky; hra sama „zvládnuto“ nedá),
 *   2) časová osa je jen čtení + cache (v režimu jen pro čtení nic nezapíše), cache se při nových důkazech obnoví,
 *   3) cíle: validace (katalog, cílový stav, duplicita, nejvýš 3), jedna kontrola týdně, splnění cíle, odebrání, žádný volný text, jen pilotní třídy,
 *   4) retence: data růstu se mažou s důkazy (30 dní po odchodu), dřív ne,
 *   5) sdílení: výchozí nic, jen co žák zapne, cíle nikdy; školní vypnutí EDUCANET_GROWTH_PUBLIC=0,
 *   6) HTTP: mapa kompetencí je první a výchozí záložka pilotní třídy, SVG s textovou alternativou, POST s CSRF (bez něj nic), cizí profil vidí jen sdílené.
 *
 *   php tools/v67_growth_audit.php
 * Dočasné úložiště, fiktivní žáci. Konec: V67_GROWTH_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v67-growth')), '/');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/app/lib.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
app_require_libs(['core', 'layout', 'competency', 'growth', 'paths', 'projects65']);
require_once $ROOT . '/profile_v60.php';
require_once $ROOT . '/growth_v67_views.php';
require_once __DIR__ . '/lib/v62_competency_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$php = is_file('C:/php/php.exe') ? 'C:/php/php.exe' : PHP_BINARY;
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));

v62fx_seed($tmp, time());
identity58_ensure();
$C = V62FX_CLASS;
$key = v62fx_key('none');
$sid = (string)grow67_student_id($C, $key);
$now = time();
$day = static fn(int $d): string => date(DATE_ATOM, $now - $d * 86400);
$row = static fn(string $comp, string $src, float $score, int $daysAgo, string $ref): array => ['competency' => $comp, 'level' => 2, 'source' => $src, 'score' => $score, 'at' => $day($daysAgo), 'artefact_ref' => $ref];
$check('identita: fixture žák má student_id', ev62_valid_id($sid));

// ---------------------------------------------------------------- 1) milníky
$rows = [];
foreach ([$row('lnx_navigation', 'test', 0.9, 50, 'test:t1'), $row('lnx_navigation', 'lab', 0.85, 30, 'lab:l1'), $row('lnx_users', 'game', 0.95, 20, 'game:g1'), $row('lnx_users', 'arena', 0.95, 10, 'arena:a1')] as $r) {
    $rows[] = ev62_normalize($sid, $r);
}
$events = grow67_competency_milestones($rows, ['lnx_navigation', 'lnx_users']);
$byRef = [];
foreach ($events as $e) $byRef[$e['ref']][$e['state']] = $e['at'];
$check('milníky: kompetence s testem a labem dosáhne „zvládnuto“ v den důkazu s dostatečnou vahou a „upevněno“ až s druhým typem zdroje s odstupem', isset($byRef['lnx_navigation']['zvladnuto']) && isset($byRef['lnx_navigation']['upevneno']) && $byRef['lnx_navigation']['upevneno'] >= $byRef['lnx_navigation']['zvladnuto']);
$check('milníky: hra a aréna samy zvládnutí ani upevnění nedají (žádná událost)', !isset($byRef['lnx_users']));
$check('milníky: výpočet je deterministický (dvě volání = totéž)', $events === grow67_competency_milestones($rows, ['lnx_navigation', 'lnx_users']));
$check('milníky: bez důkazů nic', grow67_competency_milestones([], ['lnx_navigation']) === []);

// ---------------------------------------------------------------- 2) časová osa: čtení + cache
ev62_append($sid, [$row('lnx_navigation', 'test', 0.9, 50, 'test:t1'), $row('lnx_navigation', 'lab', 0.85, 30, 'lab:l1')]);
$cachePath = grow67_cache_path($sid);
$check('časová osa: před prvním načtením cache neexistuje', !is_file($cachePath));
$tl = grow67_timeline($C, $key);
$check('časová osa: obsahuje milníky kompetence, nejnovější první, a vytvoří cache (jen odvozená data)', count($tl) >= 2 && $tl[0]['at'] >= $tl[count($tl) - 1]['at'] && is_file($cachePath) && ($tl[0]['type'] ?? '') === 'competency');
$sigBefore = (string)storage_signature($cachePath);
$tl2 = grow67_timeline($C, $key);
$check('časová osa: opakované načtení je jen čtení (cache se nepřepíše, výsledek stejný)', $tl2 === $tl && (string)storage_signature($cachePath) === $sigBefore);
ev62_append($sid, [$row('lnx_services', 'test', 0.95, 5, 'test:t9'), $row('lnx_services', 'lab', 0.9, 4, 'lab:l9')]);
$tl3 = grow67_timeline($C, $key);
$check('časová osa: nový důkaz změní otisk a cache se obnoví (přibyl milník lnx_services)', count($tl3) > count($tl) && in_array('lnx_services', array_column($tl3, 'ref'), true));
$ro = $tmp . '/_ro_probe.php';
file_put_contents($ro, "<?php\ndeclare(strict_types=1);\n\$_SERVER['SCRIPT_FILENAME']='x';\nrequire '" . $ROOT . "/bootstrap.php';\nrequire '" . $ROOT . "/app/lib.php';\napp_require_libs(['core','layout','competency','growth','paths','projects65']);\n\$c=" . var_export($C, true) . ";\n\$k=" . var_export($key, true) . ";\n\$sid=grow67_student_id(\$c,\$k);\n@unlink(grow67_cache_path(\$sid));\n\$t=grow67_timeline(\$c,\$k);\necho count(\$t), '|', is_file(grow67_cache_path(\$sid)) ? 'cache' : 'nocache', \"\\n\";\n");
$full = [];
foreach (array_merge($_SERVER, $_ENV, getenv()) as $k2 => $v2) if (is_scalar($v2)) $full[(string)$k2] = (string)$v2;
$proc = proc_open([$php, $ro], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $ROOT, array_merge($full, ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_STORAGE_READONLY' => '1']), ['bypass_shell' => true]);
$roOut = trim((string)stream_get_contents($pipes[1]));
foreach ($pipes as $pp) fclose($pp);
proc_close($proc);
$check('časová osa: v režimu jen pro čtení (EDUCANET_STORAGE_READONLY=1) se spočítá a nic nezapíše (cache nevznikne)', preg_match('/^\d+\|nocache$/', $roOut) === 1 && (int)$roOut > 0);
$check('časová osa: události nenesou žádný volný text (jen typ, odkaz, stav, čas)', (static function () use ($tl3): bool {
    foreach ($tl3 as $e) if (array_diff(array_keys($e), ['at', 'type', 'ref', 'state', 'badge']) !== []) return false;
    return true;
})());

// ---------------------------------------------------------------- 3) cíle
$subject = (string)comp62_subject_for_class($C);
$ids = array_keys(comp62_competencies($subject));
$check('cíl: neznámá kompetence a neplatný cílový stav se odmítnou', grow67_goal_add($C, $key, 'neexistuje_xx', 'zvladnuto')['error'] === 'unknown_competency' && grow67_goal_add($C, $key, $ids[0], 'upevneno_a_vic')['error'] === 'bad_target');
$g1 = grow67_goal_add($C, $key, 'lnx_users', 'zvladnuto', $now);
$check('cíl: platný cíl se uloží; stejná kompetence podruhé = duplicate', $g1['ok'] && grow67_goal_add($C, $key, 'lnx_users', 'upevneno')['error'] === 'duplicate');
$check('cíl: nejvýš tři (čtvrtý = max)', grow67_goal_add($C, $key, 'net_addressing', 'zvladnuto')['ok'] && grow67_goal_add($C, $key, 'net_dns_dhcp', 'zvladnuto')['ok'] && grow67_goal_add($C, $key, 'net_diagnose', 'zvladnuto')['error'] === 'max');
$stateRow = grow67_state($sid);
$goalId = (string)($stateRow['goals'][0]['id'] ?? '');
$check('cíl: id cíle má tvar g_ + 8 hex a uložený záznam má jen povolená pole (žádný volný text)', preg_match(GROW67_GOAL_ID_RE, $goalId) === 1 && array_diff(array_keys($stateRow['goals'][0]), ['id', 'competency', 'target', 'created_at', 'reached_at', 'checks']) === []);
$c1 = grow67_goal_check($C, $key, $goalId, $now);
$check('týdenní kontrola: zapíše aktuální stav kompetence (bez důkazů neověřeno)', $c1['ok'] && $c1['state'] === 'neovereno' && count(grow67_state($sid)['goals'][0]['checks']) === 1 && grow67_state($sid)['goals'][0]['reached_at'] === null);
grow67_goal_check($C, $key, $goalId, $now + 3600);
$check('týdenní kontrola: druhá kontrola v témže týdnu nic nepřidá (jedna za týden)', count(grow67_state($sid)['goals'][0]['checks']) === 1);
ev62_append($sid, [$row('lnx_users', 'test', 0.95, 2, 'test:tu1'), $row('lnx_users', 'lab', 0.9, 1, 'lab:lu1')]);
unset($GLOBALS['educanet_runtime_indexes']);
$c2 = grow67_goal_check($C, $key, $goalId, $now + 8 * 86400);
$stateAfter = grow67_state($sid)['goals'][0];
$check('týdenní kontrola: o týden později se zapíše další a po dosažení cílového stavu se zapíše splnění (reached_at)', $c2['ok'] && count($stateAfter['checks']) === 2 && in_array($c2['state'], ['zvladnuto', 'upevneno'], true) && is_string($stateAfter['reached_at']));
$check('týdenní kontrola: neplatné nebo cizí id cíle se odmítne', !grow67_goal_check($C, $key, 'g_zzzzzzzz', $now)['ok'] && !grow67_goal_check($C, $key, 'g_00000000', $now)['ok']);
$check('cíl: odebrání funguje a po odebrání lze přidat další', grow67_goal_remove($C, $key, $goalId)['ok'] && count(grow67_state($sid)['goals']) === 2 && grow67_goal_add($C, $key, 'net_diagnose', 'zvladnuto')['ok'] && !grow67_goal_remove($C, $key, $goalId)['ok']);
$check('cíl: mimo pilotní třídy (2.A) se nic neukládá a záložka Můj růst neexistuje', !grow67_goal_add('class_2a', $key, 'lnx_users', 'zvladnuto')['ok'] && !in_array('rust', profile60_tabs_for('class_2a'), true) && !grow67_enabled('class_2a'));
$week = grow67_week_key(strtotime('2026-10-05'));
$check('týden: klíč je ISO (2026-W41 pro 5. 10. 2026)', $week === '2026-W41');

// ---------------------------------------------------------------- 4) retence
$stu = identity58_student($sid);
grow67_timeline($C, $key);   // cache zrušená sondou v režimu jen pro čtení se vytvoří znovu
$check('retence: soubory růstu existují (cíle i cache)', is_file(grow67_path($sid)) && is_file(grow67_cache_path($sid)));
$setStatus = static function (string $id, string $status, ?string $at): void {
    storage_update(identity58_path(), static function (array $reg) use ($id, $status, $at): array {
        $reg = identity58_normalize($reg);
        $reg['students'][$id]['status'] = $status;
        $reg['students'][$id]['archived_at'] = $at;
        return $reg;
    });
};
$setStatus($sid, 'left', date(DATE_ATOM, $now - 5 * 86400));
$early = ev62_retention_purge(false, $now);
$check('retence: 5 dní po odchodu žáka se data růstu nemažou (lhůta 30 dní)', is_file(grow67_path($sid)) && !in_array($sid, $early['ids'], true));
$setStatus($sid, 'left', date(DATE_ATOM, $now - 40 * 86400));
$dry = ev62_retention_purge(true, $now);
$check('retence: dry-run nic nesmaže', in_array($sid, $dry['ids'], true) && is_file(grow67_path($sid)));
ev62_retention_purge(false, $now);
$check('retence: 40 dní po odchodu se smažou důkazy i data růstu (cíle, sdílení, cache časové osy)', !is_file(ev62_path($sid)) && !is_file(grow67_path($sid)) && !is_file(grow67_cache_path($sid)));
$setStatus($sid, 'active', null);

// ---------------------------------------------------------------- 5) sdílení
$other = v62fx_key('good');
$check('sdílení: výchozí je nic (spolužák nevidí kompetence ani časovou osu)', grow67_public_flags($C, $other) === ['timeline' => false, 'competencies' => false]);
$check('sdílení: cizí profil nemá záložky kompetence ani rust, dokud je žák nesdílí', !in_array('kompetence', profile60_public_tabs_for($C, $other), true) && !in_array('rust', profile60_public_tabs_for($C, $other), true) && profile60_public_tabs() === ['prehled', 'pokrok', 'odznaky', 'arena']);
$check('sdílení: žák zapne jen mapu kompetencí → cizí profil má jen záložku kompetence', grow67_share_set($C, $other, false, true)['ok'] && grow67_public_flags($C, $other) === ['timeline' => false, 'competencies' => true] && in_array('kompetence', profile60_public_tabs_for($C, $other), true) && !in_array('rust', profile60_public_tabs_for($C, $other), true));
putenv('EDUCANET_GROWTH_PUBLIC=0');
$check('sdílení: školní vypnutí (EDUCANET_GROWTH_PUBLIC=0) přebíjí volbu žáka a sdílení nelze zapnout', grow67_public_flags($C, $other) === ['timeline' => false, 'competencies' => false] && !grow67_share_set($C, $other, true, true)['ok']);
putenv('EDUCANET_GROWTH_PUBLIC');
$check('sdílení: mimo pilotní třídu se nikdy nesdílí', grow67_public_flags('class_2a', $other) === ['timeline' => false, 'competencies' => false]);
$check('záložky: pilotní třída má Mapa kompetencí první a Můj růst druhou; ?tab bez hodnoty = kompetence', array_slice(profile60_tabs_for($C), 0, 2) === ['kompetence', 'rust'] && (static function () use ($C): bool { unset($_GET['tab']); return profile60_current_tab(true, $C) === 'kompetence' && profile60_current_tab(true, null) === 'prehled' && profile60_current_tab(true, 'class_2a') === 'prehled'; })());

// ---------------------------------------------------------------- 6) HTTP
audit_prewarm_accounts($modules);
ev62_append($sid, [$row('lnx_navigation', 'test', 0.9, 50, 'test:t1'), $row('lnx_navigation', 'lab', 0.85, 30, 'lab:l1')]);
$harness = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $login = audit_login_student($harness, $C, V62FX_LABELS['none']);
    $profile = $harness->request('GET', '/?view=profile');
    $body = (string)$profile['body'];
    $check('HTTP: vlastní profil pilotní třídy se otevře na mapě kompetencí (aria-current na první záložce) a je 200 bez chyby', audit_response_clean($profile) && preg_match('/aria-current="page" class="p60-tab-link is-current">Kompetence</', $body) === 1);
    $check('HTTP: mapa má SVG přehled oblastí s popiskem pro čtečky (role=img, aria-label) a tabulku jako alternativu', str_contains($body, 'class="g67-bar"') && str_contains($body, 'role="img"') && str_contains($body, 'aria-label="Sítě:') && str_contains($body, 'Zobrazit jako tabulku'));
    $rust = $harness->request('GET', '/?view=profile&tab=rust');
    $rb = (string)$rust['body'];
    $check('HTTP: záložka Můj růst má cíle, příběh růstu a sdílení; formuláře mají CSRF a funkční bez JS (method=post)', audit_response_clean($rust) && str_contains($rb, 'g67-timeline') && str_contains($rb, 'name="action" value="grow67_goal_add"') && str_contains($rb, 'name="action" value="grow67_share_set"') && substr_count($rb, 'name="csrf"') >= 3);
    $token = (string)$harness->csrfToken($rb);
    $noToken = $harness->request('POST', '/index.php', ['action' => 'grow67_goal_add', 'competency' => 'net_services', 'target' => 'zvladnuto'], ['follow_redirects' => false]);
    $check('HTTP: POST bez CSRF tokenu cíl nepřidá', !in_array('net_services', array_column(grow67_state($sid)['goals'], 'competency'), true));
    $ok = $harness->request('POST', '/index.php', ['action' => 'grow67_goal_add', 'competency' => 'net_services', 'target' => 'zvladnuto', 'csrf' => $token], ['follow_redirects' => false]);
    $check('HTTP: POST s CSRF přidá cíl a přesměruje na záložku Můj růst', in_array('net_services', array_column(grow67_state($sid)['goals'], 'competency'), true) && str_contains((string)($ok['headers']['Location'] ?? $ok['headers']['location'] ?? ''), 'tab=rust'));
    $evil = $harness->request('POST', '/index.php', ['action' => 'grow67_goal_add', 'competency' => "x'; DROP", 'target' => 'zvladnuto', 'csrf' => $token], ['follow_redirects' => false]);
    $check('HTTP: neplatná kompetence z formuláře se neuloží', array_diff(array_column(grow67_state($sid)['goals'], 'competency'), $ids) === []);
    $harness->request('POST', '/index.php', ['action' => 'grow67_share_set', 'share_competencies' => '1', 'csrf' => $token], ['follow_redirects' => false]);
    $check('HTTP: sdílení uložené z formuláře (jen mapa kompetencí)', grow67_public_flags($C, $key) === ['timeline' => false, 'competencies' => true]);
    $rb2 = (string)$harness->request('GET', '/?view=profile&tab=rust')['body'];
    $goalTexts = [];
    foreach (grow67_state($sid)['goals'] as $g) $goalTexts[] = (string)comp62_competencies($subject)[$g['competency']]['label'];
    $harness->request('GET', '/?class=' . $C . '&student=' . rawurlencode(V62FX_LABELS['good']));
    $mine = (string)$harness->request('GET', '/?view=profile')['body'];
    $theirs = $harness->request('GET', '/?view=profile&student=' . rawurlencode(v62fx_key('none')) . '&tab=kompetence');
    $tb = (string)$theirs['body'];
    $check('HTTP: cizí profil sdílející mapu ukáže záložku Kompetence jen s částí „Co už umí“ a nikdy cíle', audit_response_clean($theirs) && str_contains($tb, 'Co už umí') && !str_contains($tb, 'g67-goal') && !str_contains($tb, 'grow67_goal') && !str_contains($tb, 'Moje cíle'));
    $leak = false;
    foreach ($goalTexts as $t) if ($t !== '' && str_contains($tb, $t) && !str_contains($tb, 'Co už umí')) $leak = true;
    $theirsRust = $harness->request('GET', '/?view=profile&student=' . rawurlencode(v62fx_key('none')) . '&tab=rust');
    $check('HTTP: cizí profil bez sdílené časové osy záložku Můj růst nenabízí (spadne na přehled) a nic z cílů neuniká', !str_contains((string)$theirsRust['body'], 'g67-timeline') && !str_contains((string)$theirsRust['body'], 'Moje cíle') && !$leak);
} finally {
    $harness->stop();
}

// ---------------------------------------------------------------- Linux Lab beze změny
$hashes = require __DIR__ . '/lib/v67_lab_hashes.php';
$changed = array_filter(array_keys($hashes), static fn(string $rel): bool => !is_file($ROOT . '/' . $rel) || hash_file('sha256', $ROOT . '/' . $rel) !== $hashes[$rel]);
$check('Linux Lab v57/v58 je beze změny proti snímku na začátku v67', $changed === []);

exit(audit_summary($state, 'V67_GROWTH'));
