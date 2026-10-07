<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v70 · audit přehledného cockpitu (Dnešní hodina, Přehled správy, souhrny rozcestníků, „Související“).
 *   1) soubory vrstvy: strict_types, guard, ≤ 800 řádků; záložky v mapě a registru, admin jen pro Přehled správy,
 *   2) žádná nová POST akce ani GET politika (snímek politik teacher59 = v69), moduly v70 nemají post/get,
 *   3) čisté funkce: plán dne (dnes / další hodina / ?lesson=), fáze lekce, KPI, hash žáka, doporučení správy, „Související“,
 *   4) model hodiny nad fiktivními daty v dočasném úložišti (postup, úkol po termínu, kód hodiny),
 *   5) HTTP v režimu sdíleného klíče: stránky bez chyb, otevření hodiny s návratem (CSRF), Režim hodiny bez ?lesson= = lekce podle kalendáře,
 *   6) HTTP v režimu účtů: učitel jen své třídy (cizí třída 403, žádné odkazy na cizí třídy), Přehled správy jen admin, asistent bez formuláře,
 *      v HTML nejsou hesla ani jednorázová hesla,
 *   7) CSS jen tokeny, fokus a cíle 44 px; oprava fokusu v rámci v68.
 *   php tools/v70_cockpit_audit.php        Dočasné úložiště, fiktivní data. Konec: V70_COCKPIT_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v70-cockpit')), '/');
const V70C_KEY = 'v70-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V70C_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_operations_control_v46_2.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'ui_v67.php',
    'tutorial_v52.php', 'learning_v56.php', 'runtime_content.php', 'session_v53.php', 'accounts_v53.php', 'intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
foreach ((array)teacher58_modules()['hodina']['files'] as $f) require_once $ROOT . '/' . $f;
foreach ((array)teacher58_modules()['sprava_prehled']['files'] as $f) require_once $ROOT . '/' . $f;
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

/** Snímek politik teacher59 z v68/v69 (tools/v68_cockpit_audit.php) – v70 nesmí přidat akci ani GET politiku. */
const V70C_POST_POLICY_SHA = '656e6ab3383dd1f14b02e3ee6799157ff5c9ac50f021808617245eea74afe55c';
const V70C_GET_POLICY_SHA = 'c27cfc36d68d8d73eaee7bed2a84f6ab478bc6f62ab6f17591982d6763699c2a';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && STORAGE_DIR === $tmp && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));
$check('bez souboru účtů je režim legacy', teacher59_mode() === 'legacy');

// ---------------------------------------------------------------- 1) soubory a registr
$files = ['cockpit_v70.php', 'lesson_today_v70.php', 'lesson_today_v70_views.php', 'admin_overview_v70.php', 'admin_overview_v70_views.php'];
$bad = array_values(array_filter($files, static function (string $f) use ($read): bool {
    $s = $read($f);
    return !str_contains($s, 'declare(strict_types=1);') || !str_contains($s, "basename(__FILE__)) { http_response_code(403); exit; }") || substr_count($s, "\n") > 800;
}));
$check('soubory v70: strict_types, guard proti přímému volání, ≤ 800 řádků' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === [], false);
$map = teacher68_tab_map();
$check('mapa: Dnešní hodina je v sekci Dnes hned za „Co řešit dnes“, Přehled správy je první ve Správě a jen pro admina',
    ($map['hodina']['section'] ?? '') === 'dnes' && array_slice(array_keys($map), 0, 2) === ['attention', 'hodina'] && ($map['sprava_prehled']['admin'] ?? false) === true
    && ($map['sprava_prehled']['section'] ?? '') === 'sprava' && array_values(array_filter(array_keys($map), static fn(string $t): bool => $map[$t]['section'] === 'sprava'))[0] === 'sprava_prehled');
$mods = teacher58_modules();
$check('registr: moduly hodina a sprava_prehled existují, nemají post ani get (jen čtení), Přehled správy je admin a v TEACHER59_ADMIN_TABS',
    isset($mods['hodina'], $mods['sprava_prehled']) && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get']) && !isset($mods['sprava_prehled']['post']) && !isset($mods['sprava_prehled']['get'])
    && !empty($mods['sprava_prehled']['admin']) && empty($mods['hodina']['admin']) && in_array('sprava_prehled', TEACHER59_ADMIN_TABS, true) && in_array('hodina', teacher58_tabs(), true));
$check('tmavý režim: obě nové záložky jsou v UI68_TEACHER_DARK_TABS', in_array('hodina', UI68_TEACHER_DARK_TABS, true) && in_array('sprava_prehled', UI68_TEACHER_DARK_TABS, true));
$check('role: učitel i asistent vidí Dnešní hodinu, Přehled správy jen administrátor',
    in_array('hodina', teacher68_visible_tabs('assistant', false, teacher58_tabs()), true) && !in_array('sprava_prehled', teacher68_visible_tabs('teacher', false, teacher58_tabs()), true)
    && in_array('sprava_prehled', teacher68_visible_tabs('admin', true, teacher58_tabs()), true));

// ---------------------------------------------------------------- 2) politiky beze změny
$post = teacher59_action_policies();
unset($post['teacher68_theme_set']);
ksort($post);
$get = teacher59_get_policies();
foreach (['kompetence', 'cesty', 'projekty65', 'hodnoceni66', 'labdata'] as $t) unset($get[$t . '|student']);
ksort($get);
$check('politiky teacher59: POST i GET tabulka beze změny proti v69 (v70 nepřidává akci ani GET parametr registru)',
    hash('sha256', (string)json_encode($post)) === V70C_POST_POLICY_SHA && hash('sha256', (string)json_encode($get)) === V70C_GET_POLICY_SHA);
$check('deny-by-default: neznámé akce lt70_*/ad70_*/c70_* jsou zakázané', teacher59_action_policy('lt70_open')['deny'] === true && teacher59_action_policy('ad70_x')['deny'] === true && teacher59_action_policy('c70_x')['deny'] === true);

// ---------------------------------------------------------------- 3) čisté funkce
$year = lt70_school_year();
$teachingDate = '';
$offDate = '';
foreach (adaptive_school_year_rows($year, 'class_3a') as $row) {
    if ($teachingDate === '' && (string)($row['status'] ?? '') === 'teaching' && (int)($row['lesson_number'] ?? 0) >= 3) $teachingDate = (string)$row['date'];
}
$dayAfter = date('Y-m-d', (int)strtotime($teachingDate . ' +1 day'));
$slotToday = lt70_slot($year, 'class_3a', $teachingDate, 0);
$slotNext = lt70_slot($year, 'class_3a', $dayAfter, 0);
$slotOver = lt70_slot($year, 'class_3a', $teachingDate, 9);
$slotBad = lt70_slot($year, 'class_3a', $teachingDate, 99);
$check('plán dne: výukový den = dnešní lekce; den poté = nejbližší další hodina (o týden později, vyšší lekce); ?lesson=9 jen náhled; 99 se ignoruje',
    $teachingDate !== '' && $slotToday['status'] === 'today' && $slotToday['is_today'] && $slotToday['date'] === $teachingDate && !$slotToday['override']
    && $slotNext['status'] === 'next' && $slotNext['date'] > $dayAfter && $slotNext['lesson'] > $slotToday['lesson']
    && $slotOver['override'] && $slotOver['lesson'] === 9 && $slotOver['calendar_lesson'] === $slotToday['lesson'] && !$slotBad['override'] && $slotBad['lesson'] === $slotToday['lesson']);
$check('plán dne: po konci školního roku žádná další hodina (status none) a lekce v rozsahu 1–28', ($end = lt70_slot($year, 'class_3a', '2099-01-01', 0))['status'] === 'none' && $end['lesson'] >= 1 && $end['lesson'] <= 28);
$check('fáze lekce: výklad + test + projekt + odevzdání = 4; nic = 0; neúplný výklad se nepočítá',
    lt70_phases_done(['theory' => ['a' => 1, 'b' => 1], 'test' => ['passed' => true], 'project' => ['0' => true, '1' => true], 'submit' => ['done' => true]], ['a', 'b'], 2) === 4
    && lt70_phases_done([], ['a'], 2) === 0 && lt70_phases_done(['theory' => ['a' => 1]], ['a', 'b'], 0) === 0);
$check('hash žáka: oba tvary klíče (student i s), jinak prázdný', lt70_hash('class_3a:student:' . str_repeat('a', 24)) === str_repeat('a', 24) && lt70_hash('class_3a:s:' . str_repeat('b', 24)) === str_repeat('b', 24) && lt70_hash('x') === '');
$k = lt70_kpis([['phases' => 4, 'behind' => false, 'percent' => 100], ['phases' => 1, 'behind' => true, 'percent' => 25]], ['active' => 3, 'overdue' => 1], ['joined' => 2, 'submitted' => 1]);
$check('KPI hodiny: hotovo 1, začalo 2, pomoc 1, průměr 63 %, úkoly 3 / po termínu 1', $k['done'] === 1 && $k['started'] === 2 && $k['behind'] === 1 && $k['avg'] === 63 && $k['tasks'] === 3 && $k['overdue'] === 1 && $k['joined'] === 2);
$o = ['accounts' => ['mode' => 'legacy', 'otp_expired' => 0, 'locked' => 0, 'coverage' => []], 'ops' => ['backup_days' => null, 'health' => 'none', 'health_days' => null, 'missing_ext' => []], 'data' => ['quality' => null, 'identity' => null]];
$rec = ad70_recommendations($o);
$check('doporučení správy: chybějící záloha je vážná a první, sdílený klíč a týdenní kontrola jsou upozornění s příkazem',
    ($rec[0]['level'] ?? '') === 'bad' && str_contains($rec[0]['text'], 'záloha') && count(array_filter($rec, static fn(array $r): bool => str_contains($r['cmd'], 'v59_teacher_accounts'))) === 1
    && count(array_filter($rec, static fn(array $r): bool => str_contains($r['cmd'], 'v61_weekly_health'))) === 1);
$o2 = ['accounts' => ['mode' => 'accounts', 'otp_expired' => 1, 'locked' => 0, 'coverage' => ['class_1a' => ['teacher' => 0, 'assistant' => 0], 'class_2a' => ['teacher' => 1, 'assistant' => 0]]],
    'ops' => ['backup_days' => 1, 'health' => 'PASS', 'health_days' => 2, 'missing_ext' => []], 'data' => ['quality' => ['high' => 0, 'medium' => 0, 'low' => 3], 'identity' => ['total' => 3, 'active' => 3, 'duplicates' => 0]]];
$rec2 = ad70_recommendations($o2);
$check('doporučení správy: třída bez učitele a vypršelé heslo se hlásí, čerstvá záloha a PASS ne', count($rec2) === 2 && str_contains(implode(' ', array_column($rec2, 'text')), '1.A') && array_column($rec2, 'tab') === ['ucitele', 'ucitele']);
$all = teacher68_visible_tabs('admin', true, teacher58_tabs());
$rel = c70_related_links('session', $all, 'class_3a');
$check('Související: Kód hodiny → Dnešní hodina (s ?class=) a Režim hodiny; skrytá záložka se nevypíše; neopakuje „Další krok“ v68',
    array_column($rel, 'tab') === ['hodina', 'teach'] && str_contains($rel[0]['url'], 'class=class_3a') && array_column(c70_related_links('session', ['session', 'teach'], 'class_3a'), 'tab') === ['teach']
    && !in_array('prehled', array_column(c70_related_links('attention', $all), 'tab'), true));
$unknownRules = [];
foreach (c70_related_rules() as $tab => $rules) foreach ($rules as [$target]) if (!isset($map[$target]) || !isset($map[$tab])) $unknownRules[] = $tab . '→' . $target;
$check('Související: každé pravidlo vede ze záložky mapy na záložku mapy' . ($unknownRules ? ' [' . implode(', ', $unknownRules) . ']' : ''), $unknownRules === []);

// ---------------------------------------------------------------- 4) model nad fiktivními daty
$students = project_students_for_class('class_3a');
$firstKey = (string)array_key_first($students);
$now = (int)strtotime($teachingDate . ' 09:00:00');
$lessonNo = $slotToday['lesson'];
$lesson = lt70_lesson('class_3a', $GLOBALS['modules']['class_3a'], $lessonNo);
storage_update(v56_progress_path(), static function (array $d) use ($firstKey, $lesson): array {
    $d['class_3a|' . $firstKey][(string)$lesson['number']] = ['theory' => array_fill_keys(array_column($lesson['topics'], 'key'), true), 'test' => ['passed' => true, 'best' => 90],
        'project' => array_fill_keys(array_map('strval', array_keys($lesson['steps'])), true), 'submit' => ['done' => true]];
    return $d;
});
storage_update(teacher_tasks_path(), static function (array $d) use ($firstKey, $teachingDate): array {
    $d[] = ['id' => 'tt_v70audit', 'class_id' => 'class_3a', 'student_key' => $firstKey, 'title' => 'V70 audit úkol', 'priority' => 'normal', 'status' => 'assigned', 'due_at' => date('Y-m-d', (int)strtotime($teachingDate . ' -2 days'))];
    $d[] = ['id' => 'tt_v70audit_2a', 'class_id' => 'class_2a', 'student_key' => 'class_2a:student:' . str_repeat('c', 24), 'title' => 'Cizí úkol', 'status' => 'assigned'];
    return $d;
});
$model = lt70_model($GLOBALS['modules'], 'class_3a', 0, $now);
$me = array_values(array_filter($model['students'], static fn(array $r): bool => $r['key'] === $firstKey))[0] ?? [];
$check('model: třída 3.A, lekce podle kalendáře, téma, kroky a materiály z obsahu lekce', $model['class'] === 'class_3a' && $model['lesson']['number'] === $lessonNo && $model['lesson']['title'] !== ''
    && $model['lesson']['steps'] !== [] && $model['materials'] !== [] && array_reduce($model['materials'], static fn(bool $c, array $r): bool => $c && preg_match('~^https?://~', $r['url']) === 1, true));
$check('model: fiktivní postup žáka = 4/4 fáze, úkol po termínu se počítá jen ve své třídě', count($students) > 0 && ($me['phases'] ?? 0) === 4 && ($me['tasks'] ?? 0) === 1 && ($me['overdue'] ?? 0) === 1
    && $model['kpis']['done'] === 1 && $model['kpis']['tasks'] === 1 && $model['tasks']['top'][0]['title'] === 'V70 audit úkol');
$check('model: výukové cesty 3.A v pilotu (s příznakem „k tématu lekce“), 2.A pilot nemá', $model['paths']['enabled'] && $model['paths']['rows'] !== [] && !lt70_paths('class_2a', [])['enabled']);
$check('model: cizí nebo neplatná třída → první třída v rozsahu, prázdný seznam tříd → prázdný model',
    lt70_model($GLOBALS['modules'], 'class_9z', 0, $now)['class'] !== 'class_9z' && lt70_model([], 'class_3a', 0, $now)['class'] === '');
$auditSession = edu_audit_open_sessions(['class_3a' => $GLOBALS['modules']['class_3a']], $year, $teachingDate, []);
$check('model: otevřená hodina ukáže kód a počty (v53)', ($s = lt70_session('class_3a', $teachingDate))['exists'] && $s['open'] && $s['code'] === (string)$auditSession['class_3a']['code']);
$html = audit_capture(static function () use ($now): void { $_GET = ['class' => 'class_3a']; lt70_render_tab($GLOBALS['modules'], 'class_3a'); $_GET = []; });
$check('vykreslení: nadpis, souhrn, téma, průběh, materiály (rel=noopener), žáci; jména žáků jen escapovaná', str_contains($html, 'Dnešní hodina') && str_contains($html, 'Souhrn hodiny')
    && str_contains($html, 'Průběh hodiny') && str_contains($html, 'Materiály k lekci') && str_contains($html, 'rel="noopener noreferrer"') && str_contains($html, 'class="c70-table"') && !str_contains($html, '<script'));

// ---------------------------------------------------------------- 5) HTTP – sdílený klíč (admin)
audit_prewarm_accounts($GLOBALS['modules']);
$today = date('Y-m-d');
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V70C_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1']);
try {
    $login = audit_login_teacher($h, V70C_KEY, 'Audit Učitel');
    $page = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_2a']);
    $body = (string)$page['body'];
    $slot2a = lt70_slot($year, 'class_2a', $today, 0);
    $title2a = lt70_lesson('class_2a', $GLOBALS['modules']['class_2a'], $slot2a['lesson'])['title'];
    $check('HTTP: Dnešní hodina 2.A je čistá, má menu položku, název lekce podle kalendáře, materiály, žáky a Související', audit_response_clean($page) && str_contains($body, 'href="?tab=hodina')
        && str_contains($body, e($title2a)) && str_contains($body, 'Materiály k lekci') && str_contains($body, 'c70-related') && str_contains($body, 'assets/cockpit-v70.css'));
    $over = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_2a', 'lesson' => $slot2a['lesson'] === 9 ? '10' : '9'])['body'];
    $junk = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_2a', 'lesson' => '<x>']);
    $check('HTTP: ?lesson= přepne náhled s odkazem zpět; nečíselná hodnota se ignoruje bez chyby', str_contains($over, 'Prohlížíte lekci') && audit_response_clean($junk) && !str_contains((string)$junk['body'], 'Prohlížíte lekci'));
    $csrf = (string)$h->csrfToken($body);
    if ($slot2a['is_today']) {
        $open = $h->request('POST', '/teacher.php', ['action' => 'sess53_t_open', 'return_tab' => 'hodina', 'class_id' => 'class_2a', 'date' => $today, 'kind' => 'work', 'csrf' => $csrf], ['follow_redirects' => false]);
        $check('HTTP: otevření hodiny z Dnešní hodiny → 303 zpět na ?tab=hodina&class=class_2a a kód je vidět', (int)$open['status'] === 303 && str_contains((string)($open['headers']['Location'] ?? ''), 'tab=hodina&class=class_2a')
            && str_contains((string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_2a'])['body'], 'class="c70-code"'));
    } else {
        $check('HTTP: v nevýukový den Dnešní hodina neukazuje formulář otevření, ale datum další hodiny', !str_contains($body, 'value="sess53_t_open"') && str_contains($body, 'Kód pro žáky otevřete v den výuky'));
    }
    $noCsrf = $h->request('POST', '/teacher.php', ['action' => 'sess53_t_open', 'return_tab' => 'hodina', 'class_id' => 'class_4a', 'kind' => 'work'], ['follow_redirects' => false]);
    $evil = $h->request('POST', '/teacher.php', ['action' => 'sess53_t_open', 'return_tab' => '//evil.example', 'class_id' => 'class_4a', 'date' => $today, 'kind' => 'work', 'csrf' => $csrf], ['follow_redirects' => false]);
    $check('HTTP: bez CSRF 419; jiná návratová hodnota než „hodina“ vede na Kód hodiny (žádný open redirect)', (int)$noCsrf['status'] === 419 && (int)$evil['status'] === 303 && str_starts_with((string)($evil['headers']['Location'] ?? ''), 'teacher.php?tab=session'));
    $admin = $h->request('GET', '/teacher.php', ['tab' => 'sprava_prehled']);
    $check('HTTP: Přehled správy (sdílený klíč) – souhrn, „Co udělat“ s doporučením účtů, žádný formulář', audit_response_clean($admin) && str_contains((string)$admin['body'], 'Co udělat')
        && str_contains((string)$admin['body'], 'v59_teacher_accounts') && !preg_match('~<form[^>]*>(?:(?!</form>).)*name="action" value="(?!teacher68_theme_set|teacher_logout)~s', (string)$admin['body']));
    $hubDnes = (string)$h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'dnes'])['body'];
    $hubSprava = (string)$h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'sprava'])['body'];
    $check('HTTP: rozcestník Dnes má souhrn dnešní výuky všech tříd, Správa souhrn KPI a karty zůstaly', str_contains($hubDnes, 'Dnešní výuka') && substr_count($hubDnes, '?tab=hodina&amp;class=') >= 4
        && str_contains($hubDnes, 't68-cards') && str_contains($hubSprava, 'Souhrn správy') && str_contains($hubSprava, 't68-cards'));
    $att = (string)$h->request('GET', '/teacher.php', ['tab' => 'attention'])['body'];
    $check('HTTP: „Co řešit dnes“ už neodkazuje na vyřazenou Automatizaci, ale na Dnešní hodinu', !str_contains($att, 'tab=automations') && str_contains($att, '>Dnešní hodina</a>'));
    $teach = (string)$h->request('GET', '/teacher.php', ['tab' => 'teach', 'class' => 'class_3a'])['body'];
    $cal = v56_current_lesson_number('class_3a', $year);
    $check('HTTP: Režim hodiny bez ?lesson= otevře lekci podle kalendáře (' . $cal . '), ne vždy lekci 1', str_contains($teach, 'Lekce ' . str_pad((string)$cal, 2, '0', STR_PAD_LEFT)));
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- 6) HTTP – režim účtů
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V70C_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
$jars = [];
try {
    $as = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h2, $jarSwap, &$jars, $acc): array {
        if (!isset($jars[$who])) {
            $jarSwap([]);
            audit_login_teacher_account($h2, $acc['login'][$who], $acc['pw'][$who]);
            $jars[$who] = $jarSwap(null);
        }
        $jarSwap($jars[$who]);
        $r = $h2->request($method, $path, $fields, $headers);
        $jars[$who] = $jarSwap(null);
        return $r;
    };
    $secretLeak = static function (string $html) use ($acc): bool {
        foreach (array_merge(array_values($acc['pw']), array_values(array_filter($acc['otp']))) as $secret) if ($secret !== '' && str_contains($html, (string)$secret)) return true;
        return false;
    };
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'hodina']);
    $aBody = (string)$aPage['body'];
    $foreign = preg_match('/[?&;]class=class_[34]a\b|value="class_[34]a"/', $aBody) === 1;
    $check('účty: učitel 1.A/2.A vidí Dnešní hodinu jen se svými třídami (žádný odkaz ani hodnota 3.A/4.A)', audit_response_clean($aPage) && str_contains($aBody, 'Dnešní hodina') && !$foreign && str_contains($aBody, '?tab=hodina&amp;class=class_2a'));
    $aForeign = $as('a', 'GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $aAdmin = $as('a', 'GET', '/teacher.php', ['tab' => 'sprava_prehled'], ['follow_redirects' => false]);
    $aHub = (string)$as('a', 'GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'dnes'])['body'];
    $check('účty: cizí třída v Dnešní hodině → 403, Přehled správy pro učitele → 403, rozcestník Dnes bez cizích tříd', (int)$aForeign['status'] === 403 && (int)$aAdmin['status'] === 403
        && preg_match('/[?&;]class=class_[34]a\b/', $aHub) !== 1 && str_contains($aHub, 'Dnešní výuka'));
    $sPage = (string)$as('s', 'GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a'])['body'];
    $check('účty: asistent vidí Dnešní hodinu 3.A, ale bez formuláře otevření hodiny (nemá students.manage)', str_contains($sPage, 'Dnešní hodina') && !str_contains($sPage, 'value="sess53_t_open"'));
    $adm = $as('admin', 'GET', '/teacher.php', ['tab' => 'sprava_prehled']);
    $admBody = (string)$adm['body'];
    $check('účty: administrátor – Přehled správy s rolemi, pokrytím tříd a událostmi; v HTML nejsou hesla ani jednorázová hesla', audit_response_clean($adm)
        && str_contains($admBody, 'Pokrytí tříd') && str_contains($admBody, 'Poslední události účtů') && str_contains($admBody, 'Učitelé') && !$secretLeak($admBody) && !$secretLeak($aBody));
    $admHub = (string)$as('admin', 'GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'sprava'])['body'];
    $check('účty: rozcestník Správa pro admina ukazuje KPI účtů a odkaz na celý přehled', str_contains($admHub, 'Čeká na 1. přihlášení') && str_contains($admHub, 'Přihlašování') && str_contains($admHub, '?tab=sprava_prehled'));
} finally {
    $h2->stop();
}

// ---------------------------------------------------------------- 7) CSS
$css = preg_replace('~/\*.*?\*/~s', '', $read('assets/cockpit-v70.css'));
$check('CSS v70: jen tokeny (žádné pevné barvy), :focus-visible s barvou akcentu, cíle ≥ 44 px, reduced-motion, mobilní tabulka',
    preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', (string)$css) !== 1 && str_contains((string)$css, 'outline: var(--ui-focus, 3px) solid var(--ui-accent)') && substr_count((string)$css, 'min-height: 44px') >= 6
    && str_contains((string)$css, 'prefers-reduced-motion') && str_contains((string)$css, 'content: attr(data-label)'), false);
$check('rámec v68: fokus už nepoužívá --ui-focus (šířka 3px) jako barvu – obrys je viditelný', !str_contains($read('assets/teacher-shell-v68.css'), 'solid var(--ui-focus, var(--ui-accent))')
    && str_contains($read('assets/teacher-shell-v68.css'), 'outline: var(--ui-focus, 3px) solid var(--ui-accent)'), false);

exit(audit_summary($state, 'V70_COCKPIT'));
