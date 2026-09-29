<?php

declare(strict_types=1);

/**
 * EDUCANET v60 · behaviorální audit hlášení chyb / návrhů vylepšení (feedback_v60*.php).
 *   php tools/v60_feedback_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte/nezapisuje ostrou storage/.
 * Pokrývá: validaci a limity, whitelist stránky, anti-spam, izolaci žáků, jednorázovou odměnu (body + XP),
 * zamítnutí bez odměny, rozsah učitele a roli asistenta, CSRF a identitu ze session přes HTTP, XSS, zdroj bez CDN/innerHTML.
 * Konec: V60_FEEDBACK_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v60-feedback')), '/');
require $root . '/bootstrap.php';
require_once $root . '/app/lib.php';
foreach (['points_v53.php', 'points_v60.php', 'feedback_v60.php', 'feedback_v60_views.php', 'feedback_v60_teacher_views.php', 'teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_accounts_v59.php', 'teacher_v58.php'] as $lib) {
    require_once $root . '/' . $lib;
}

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp);

$GLOBALS['modules'] = ['class_1a' => ['course_type' => 'graphics'], 'class_3a' => ['course_type' => 'networks'], 'class_4a' => ['course_type' => 'networks']];
teacher59_all_modules($GLOBALS['modules']);
$A = 'class_3a';
$B = 'class_4a';
$stu = static fn(string $c, string $name): string => project_student_key($c, $name);
$zak = $stu($A, 'Audit Zak');
$jiny = $stu($A, 'Audit Jiny');
$good = static fn(string $title, string $type = 'bug', string $page = 'lab') => ['type' => $type, 'title' => $title, 'description' => 'Tlačítko na stránce nereaguje, když na něj kliknu.', 'page' => $page];

// --- 1) Validace, limity délek a whitelist stránky -------------------------------------------------
$check('validace: neplatný typ se odmítne', fb60_submit($A, $zak, ['type' => 'zbran'] + $good('Titulek ok'))['error'] === 'type');
$check('validace: krátký titulek se odmítne', fb60_submit($A, $zak, ['title' => 'abc'] + $good('x'))['error'] === 'title_short');
$check('validace: titulek 121 znaků se odmítne, 120 projde', fb60_submit($A, $zak, ['title' => str_repeat('a', 121)] + $good('x'))['error'] === 'title_long' && fb60_submit($A, $zak, $good(str_repeat('b', 120)))['ok']);
$check('validace: krátký popis se odmítne', fb60_submit($A, $zak, ['description' => 'krátké'] + $good('Titulek popis'))['error'] === 'desc_short');
$check('validace: popis 2001 znaků se odmítne', fb60_submit($A, $zak, ['description' => str_repeat('x', 2001)] + $good('Titulek dlouhý'))['error'] === 'desc_long');
$check('validace: popis přesně 2000 znaků projde', fb60_submit($A, $zak, ['description' => str_repeat('x', 2000)] + $good('Titulek 2000'))['ok']);
$check('validace: prázdná identita (bez třídy/žáka) se odmítne', fb60_submit('', $zak, $good('Titulek identita'))['error'] === 'identity' && fb60_submit($A, '', $good('Titulek identita'))['error'] === 'identity');
$r = fb60_submit($A, $zak, $good('Chyba v labu', 'bug', '<script>alert(1)</script>'));
$rec = fb60_item((string)$r['id']);
$check('whitelist: neznámý název stránky se uloží jako prázdný', $r['ok'] && $rec !== null && $rec['page'] === '');
$r2 = fb60_submit($A, $zak, $good('Chyba v obchodě', 'bug', 'obchod'));
$check('whitelist: platný název pohledu se uloží', ($rec2 = fb60_item((string)$r2['id'])) !== null && $rec2['page'] === 'obchod');
$check('nový záznam: stav new, odměna 0, třída a klíč žáka ze zadání, texty ořezané a bez ID cizích polí',
    $rec['status'] === 'new' && $rec['reward_points'] === 0 && $rec['reward_xp'] === 0 && $rec['class_id'] === $A && $rec['student_key'] === $zak && $rec['decided_by'] === '' && $rec['teacher_note'] === '');
$check('úložiště: soubor začíná ochranným řádkem', str_starts_with((string)file_get_contents(fb60_path()), STORAGE_GUARD_LINE));
$check('whitelist stránek je v souladu s routerem (každý žákovský pohled lze nahlásit)', (static function () use ($root): bool {
    $routes = require $root . '/app/routes.php';
    $missing = [];
    foreach ((array)$routes['views_student'] as $entry) {
        foreach ((array)$entry['match'] as $name) if (!in_array($name, fb60_pages(), true) && !in_array($name, ['v48_state', 'my_intake_file'], true)) $missing[] = $name;
    }
    if ($missing !== []) fwrite(STDERR, 'CHYBÍ ve whitelistu: ' . implode(',', $missing) . "\n");
    return $missing === [];
})());

// --- 2) Anti-spam: duplicita a denní limit -----------------------------------------------------------
$check('duplicita: stejný titulek (jiná velikost písmen, mezery) se odmítne', fb60_submit($A, $zak, $good('  chyba   V LABU '))['error'] === 'duplicate');
$check('duplicita: stejný titulek od jiného žáka projde', fb60_submit($A, $jiny, $good('Chyba v labu'))['ok']);
$spamKey = $stu($A, 'Audit Spam');
$okCount = 0;
for ($i = 1; $i <= 5; $i++) if (fb60_submit($A, $spamKey, $good('Spam číslo ' . $i))['ok']) $okCount++;
$sixth = fb60_submit($A, $spamKey, $good('Spam číslo šest'));
$check('limit: 5 hlášení za den projde, šesté se odmítne', $okCount === 5 && $sixth['error'] === 'limit' && fb60_remaining_today($A, $spamKey) === 0);
$check('limit: jiný žák není limitem ovlivněn a zbývající počet sedí', fb60_remaining_today($A, $stu($A, 'Audit Cerstvy')) === FB60_DAILY_LIMIT);

// --- 3) Izolace: žák vidí jen svá hlášení -------------------------------------------------------------
$mine = fb60_for_student($A, $zak);
$check('izolace: žák vidí jen vlastní hlášení', $mine !== [] && count(array_filter($mine, static fn(array $x): bool => $x['student_key'] !== $zak)) === 0);
$check('izolace: jiný žák nevidí hlášení prvního, jiná třída ani cizí klíč nic', count(array_filter(fb60_for_student($A, $jiny), static fn(array $x): bool => $x['student_key'] === $zak)) === 0 && fb60_for_student($B, $zak) === [] && fb60_for_student($A, '') === []);

// --- 4) Rozhodování: potvrzení odmění právě jednou -----------------------------------------------------
$rid = (string)fb60_submit($A, $zak, $good('Potvrzená chyba tlačítko'))['id'];
$balBefore = pts53_balance($A, $zak);
$xpOf = static fn(string $c, string $s): int => (int)(storage_read(learning_profiles_path())[fb60_learning_key($c, $s)]['xp'] ?? 0);
$xpBefore = $xpOf($A, $zak);
$d1 = fb60_decide($rid, 'confirm', 5, 50, 'Díky, opravíme.', 'Audit Učitel', false);
$check('potvrzení: uspěje, stav confirmed a XP se připsalo', $d1['ok'] && $d1['status'] === 'confirmed' && $d1['xp_applied'] === true);
$check('potvrzení: body +5 a XP +50 přesně jednou', pts53_balance($A, $zak) === $balBefore + 5 && $xpOf($A, $zak) === $xpBefore + 50);
$d2 = fb60_decide($rid, 'confirm', 5, 50, '', 'Audit Učitel', false);
$d3 = fb60_decide($rid, 'confirm', 10, 100, 'Zkouším víc', 'Audit Admin', true);
$check('opakované potvrzení (i s vyšší odměnou) nepřidá nic navíc', $d2['ok'] && $d3['ok'] && pts53_balance($A, $zak) === $balBefore + 5 && $xpOf($A, $zak) === $xpBefore + 50);
$recC = (array)fb60_item($rid);
$check('opakované potvrzení nemění uloženou odměnu (5 b / 50 XP)', $recC['reward_points'] === 5 && $recC['reward_xp'] === 50);
$events = (array)(storage_read(learning_profiles_path())[fb60_learning_key($A, $zak)]['events'] ?? []);
$check('XP událost fb60:<id> je v profilu právě jednou a v peněžence právě jeden award', isset($events['fb60:' . $rid]) && count(array_filter(array_keys($events), static fn($k): bool => $k === 'fb60:' . $rid)) === 1 && isset(pts53_wallet($A, $zak)['awards']['fb60:' . $rid]) && (int)pts53_wallet($A, $zak)['awards']['fb60:' . $rid]['points'] === 5);
$check('zamítnutí už odměněného hlášení se odmítne a nic nemění', fb60_decide($rid, 'reject', 1, 0, '', 'x', false)['error'] === 'rewarded' && (fb60_item($rid)['status'] ?? '') === 'confirmed' && pts53_balance($A, $zak) === $balBefore + 5);
$check('hotovo po potvrzení: stav done, odměna se nezdvojí', fb60_decide($rid, 'done', 1, 0, '', 'x', false)['ok'] && pts53_balance($A, $zak) === $balBefore + 5 && $xpOf($A, $zak) === $xpBefore + 50);

$noReward = static function (string $decision) use ($A, $stu, $good, $xpOf): bool {
    $who = $stu($A, 'Audit Bez Odmeny ' . $decision);
    $id = (string)fb60_submit($A, $who, $good('Bez odměny ' . $decision))['id'];
    $b = pts53_balance($A, $who);
    $x = $xpOf($A, $who);
    $r = fb60_decide($id, $decision, 10, 100, 'poznámka', 'Audit', true);
    return $id !== '' && $r['ok'] && pts53_balance($A, $who) === $b && $xpOf($A, $who) === $x && (int)fb60_item($id)['reward_points'] === 0 && !isset(pts53_wallet($A, $who)['awards']['fb60:' . $id]);
};
$check('zamítnutí nic neudělí', $noReward('reject'));
$check('duplicita nic neudělí', $noReward('duplicate'));
$check('hotovo bez potvrzení nic neudělí', $noReward('done'));
$check('neplatné rozhodnutí a neexistující ID se odmítnou', fb60_decide($rid, 'smazat', 1, 1, '', 'x', false)['error'] === 'decision' && fb60_decide('fb60_neexistuje', 'confirm', 1, 1, '', 'x', false)['error'] === 'not_found');
$check('poznámka nad 500 znaků se odmítne', fb60_decide((string)fb60_submit($A, $stu($A, 'Audit Poznamka Dlouha'), $good('Poznámka dlouhá'))['id'], 'reject', 1, 0, str_repeat('n', 501), 'x', false)['error'] === 'note_long');
$clampT = fb60_clamp_reward(99, 77, false);
$clampA = fb60_clamp_reward(0, 77, true);
$check('odměna: učitel jen předvolby XP a body 1–10, admin libovolné XP', $clampT === ['points' => 10, 'xp' => 50] && $clampA === ['points' => 1, 'xp' => 77] && fb60_clamp_reward(3, 999, true)['xp'] === FB60_MAX_XP);
$check('XP nejde připsat žáku bez odvoditelného profilu (a nevznikne prázdný profil)', fb60_award_xp($A, $A . ':student:Nema Hash', 'fb60:x', 30) === false && !isset(storage_read(learning_profiles_path())[$A . ':s:Nema Hash']));
$noteKey = $stu($A, 'Audit Poznamka');
$noteRid = (string)fb60_submit($A, $noteKey, $good('Poznámka viditelná'))['id'];
fb60_decide($noteRid, 'reject', 1, 0, 'Tohle už existuje <b>tučně</b>', 'Tajný Učitel', false);
$viewHtml = audit_capture(static function () use ($A, $noteKey): void { feedback60_render_page($A, $noteKey, 'lab', []); });
$check('žákovský pohled ukáže poznámku učitele escapovanou a neukáže identitu učitele', str_contains($viewHtml, 'Tohle už existuje &lt;b&gt;tučně&lt;/b&gt;') && !str_contains($viewHtml, 'Tajný Učitel') && !str_contains($viewHtml, '<b>tučně'));
$summary = fb60_summary($A, $zak);
$check('souhrn do profilu: nahlášeno a potvrzeno (jen vlastní), odměna 5 b / 50 XP', $summary['confirmed'] === 1 && $summary['points'] === 5 && $summary['xp'] === 50 && $summary['total'] === count(fb60_for_student($A, $zak)));

// --- 5) Učitel: rozsah tříd, role asistenta, seznam ----------------------------------------------------
$ownId = (string)fb60_submit($A, $stu($A, 'Audit Vlastni'), $good('Hlášení vlastní třídy MK3A'))['id'];
$foreignId = (string)fb60_submit($B, $stu($B, 'Audit Cizi'), $good('Hlášení cizí třídy MK4A'))['id'];
$teacherId = 't_' . bin2hex(random_bytes(8));
storage_update(teacher59_accounts_path(), static function (array $store) use ($teacherId, $A): array {
    $store['mode'] = 'accounts';
    $store['version'] = TEACHER59_STORE_VERSION;
    $store['accounts'][$teacherId] = ['id' => $teacherId, 'login' => 'audit.teacher', 'display_name' => 'Audit Učitel', 'role' => 'teacher', 'assignments' => [['class_id' => $A, 'subject_id' => '*']],
        'password_hash' => password_hash('audit-heslo-123456', PASSWORD_DEFAULT), 'must_change_password' => false, 'status' => 'active', 'session_version' => 1,
        'created_at' => date(DATE_ATOM), 'created_by' => 'audit', 'updated_at' => date(DATE_ATOM)];
    return $store;
});
$_SESSION['teacher59'] = ['id' => $teacherId, 'sv' => 1, 'seen_at' => time(), 'login_at' => time()];
teacher59_reset_cache();
$check('fixture: učitel s rozsahem jen 3.A', (string)(teacher59_current()['role'] ?? '') === 'teacher' && teacher59_allowed_class_ids() === [$A]);
$check('guard: fb60_decide pro hlášení VLASTNÍ třídy povoleno, CIZÍ třídy zamítnuto, neznámé ID zamítnuto, chybějící ID zamítnuto',
    teacher59_guard_post_check('fb60_decide', ['id' => $ownId]) === null && teacher59_guard_post_check('fb60_decide', ['id' => $foreignId]) !== null
    && teacher59_guard_post_check('fb60_decide', ['id' => 'fb60_neexistuje']) !== null && teacher59_guard_post_check('fb60_decide', []) !== null);
$check('guard: neznámá fb60_ akce je zamítnuta (deny-by-default)', teacher59_guard_post_check('fb60_neco_neznameho', ['id' => $ownId]) !== null);
$check('oprávnění: fb60_decide vyžaduje content.manage a učitel ho má', teacher_action_permission('fb60_decide') === 'content.manage' && teacher_permission('content.manage'));
$html = audit_capture(static function () use ($A): void { fb60_render_teacher_tab($A, 'csrf-test'); });
$check('seznam učitele: vidí své třídy, cizí třídu ne, formulář má CSRF a akci fb60_decide', str_contains($html, 'MK3A') && !str_contains($html, 'MK4A') && str_contains($html, 'name="csrf" value="csrf-test"') && str_contains($html, 'value="fb60_decide"'));
$_GET['fb_status'] = 'confirmed';
$filtered = audit_capture(static function () use ($A): void { fb60_render_teacher_tab($A, 'csrf-test'); });
unset($_GET['fb_status']);
$check('seznam učitele: filtr stavu skryje nová hlášení', !str_contains($filtered, 'MK3A'));
storage_map_update(teacher59_accounts_path(), 'accounts', static function (?array $accounts) use ($teacherId): array { $accounts = is_array($accounts) ? $accounts : []; $accounts[$teacherId]['role'] = 'assistant'; return $accounts; });
teacher59_reset_cache();
$htmlAssistant = audit_capture(static function () use ($A): void { fb60_render_teacher_tab($A, 'csrf-test'); });
$check('asistent: nesmí content.manage (nemůže rozhodovat), seznam vidí bez formuláře rozhodnutí', !teacher_permission('content.manage') && teacher_permission('view') && str_contains($htmlAssistant, 'MK3A') && !str_contains($htmlAssistant, 'value="fb60_decide"'));
storage_map_update(teacher59_accounts_path(), 'accounts', static function (?array $accounts) use ($teacherId): array { $accounts = is_array($accounts) ? $accounts : []; $accounts[$teacherId]['role'] = 'admin'; $accounts[$teacherId]['assignments'] = []; return $accounts; });
teacher59_reset_cache();
$htmlAdmin = audit_capture(static function () use ($A): void { fb60_render_teacher_tab($A, 'csrf-test'); });
$check('admin: vidí hlášení všech tříd', teacher59_is_admin() && str_contains($htmlAdmin, 'MK3A') && str_contains($htmlAdmin, 'MK4A'));
$check('modul hlaseni je registrovaný, prefix fb60_ mapovaný a rozsah tříd má politiku', isset(teacher58_modules()['hlaseni']['post']['fb60_']) && isset(teacher59_action_policies()['fb60_decide']['entity']) && teacher59_entity_classes('fb60_report', $foreignId) === [$B]);

// --- 6) HTTP: CSRF, identita ze session, XSS, menu, izolace, profil -------------------------------------
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0'];
$h = Harness::start($env);
$h2 = Harness::start($env);
try {
    $login = audit_login_student($h, $A, 'Http Zak');
    $csrf = (string)$login['csrf'];
    $httpKey = $stu($A, 'Http Zak');
    $check('menu a patička: odkaz na hlášení s předvyplněním stránky', str_contains((string)$login['response']['body'], 'href="?view=hlaseni"') && str_contains((string)$login['response']['body'], 'fb60-footer-link'));
    $page = $h->request('GET', '/?view=hlaseni&page=lab');
    $check('HTTP GET ?view=hlaseni: 200, formulář s CSRF a předvyplněnou stránkou', $page['status'] === 200 && str_contains($page['body'], 'name="page" value="lab"') && str_contains($page['body'], 'value="fb60_submit"') && audit_response_clean($page));
    $evil = $h->request('GET', '/?view=hlaseni&page=' . rawurlencode('"><script>x</script>'));
    $check('HTTP: nepovolená ?page= se do formuláře nedostane', !str_contains($evil['body'], '<script>x</script>') && str_contains($evil['body'], 'name="page" value=""'));
    $before = count(storage_read(fb60_path()));
    $noCsrf = $h->request('POST', '/', ['action' => 'fb60_submit'] + $good('Bez CSRF tokenu'));
    $check('CSRF: POST bez tokenu je odmítnut (419) a nic se neuloží', $noCsrf['status'] === 419 && count(storage_read(fb60_path())) === $before);
    $forged = $h->request('POST', '/', ['action' => 'fb60_submit', 'csrf' => $csrf, 'student_key' => $zak, 'class_id' => $B, 'reward_points' => '10', 'status' => 'confirmed'] + $good('Titulek <script>alert(1)</script> http'));
    $mineHttp = fb60_for_student($A, $httpKey);
    $check('identita ze session: podvržený student_key/class_id/status/reward se ignoruje', $forged['status'] === 200 && count($mineHttp) === 1 && $mineHttp[0]['class_id'] === $A && $mineHttp[0]['status'] === 'new' && $mineHttp[0]['reward_points'] === 0 && fb60_for_student($B, $httpKey) === []);
    $check('XSS: titulek se ve výpisu žáka escapuje', str_contains($forged['body'], '&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($forged['body'], '<script>alert(1)</script>'));
    $tooShort = $h->request('POST', '/', ['action' => 'fb60_submit', 'csrf' => $csrf] + ['type' => 'bug', 'title' => 'abc', 'description' => 'krátké']);
    $check('HTTP: neplatný vstup vrátí formulář s hláškou a rozepsaným textem', $tooShort['status'] === 200 && str_contains($tooShort['body'], 'Titulek je moc krátký') && str_contains($tooShort['body'], 'value="abc"'));
    $login2 = audit_login_student($h2, $A, 'Http Jiny');
    $other = $h2->request('GET', '/?view=hlaseni');
    $check('izolace přes HTTP: jiný žák nevidí titulek cizího hlášení', $other['status'] === 200 && !str_contains($other['body'], 'alert(1)') && !str_contains($other['body'], 'Potvrzená chyba'));
} finally {
    $h->stop();
    $h2->stop();
}

// --- 6b) Profil: souhrn jen ve vlastním profilu ------------------------------------------------------------
require_once $root . '/profile_v60.php';
require_once $root . '/profile_v60_views.php';
$own = audit_capture(static function () use ($A, $zak): void { profile60_render_badges($A, $zak, ['defs' => [], 'featured' => []], true); });
$foreign = audit_capture(static function () use ($A, $zak): void { profile60_render_badges($A, $zak, ['defs' => [], 'featured' => []], false); });
$check('profil: vlastní záložka Odznaky ukáže souhrn nahlášených chyb, cizí profil ne', str_contains($own, 'fb60-profile') && str_contains($own, 'Nahlášeno: ') && !str_contains($foreign, 'fb60-profile'));

// --- 7) Zdroj: bez CDN, innerHTML, dvojic load+save a zakázaných funkcí ------------------------------
$src = '';
foreach (['feedback_v60.php', 'feedback_v60_views.php', 'feedback_v60_teacher_views.php', 'assets/feedback-v60.css', 'app/views/feedback.php', 'app/actions/feedback.php'] as $f) $src .= (string)file_get_contents($root . '/' . $f);
$check('zdroj: žádné innerHTML, CDN ani externí URL', !preg_match('/innerHTML|outerHTML|document\.write|https?:\/\/(?!127\.0\.0\.1)/i', $src), false);
$check('zdroj: RMW jen přes storage_update (žádné save_php_json/file_put_contents/load_php_json)', !preg_match('/save_php_json|load_php_json|file_put_contents|fopen\(/', $src), false);
$check('zdroj: žádné exec/eval/curl/mail', !preg_match('/\b(exec|shell_exec|system|passthru|proc_open|popen|eval|curl_\w+|mail|fsockopen)\s*\(/', $src), false);
$check('stránky: router obsahuje pohled hlaseni i akci fb60_submit', (static function () use ($root): bool {
    $routes = require $root . '/app/routes.php';
    $views = array_merge(...array_map(static fn(array $e): array => (array)$e['match'], (array)$routes['views_student']));
    $actions = array_merge(...array_map(static fn(array $e): array => (array)$e['match'], (array)$routes['actions_class']));
    return in_array('hlaseni', $views, true) && in_array('fb60_submit', $actions, true);
})());

exit(audit_summary($state, 'V60_FEEDBACK'));
