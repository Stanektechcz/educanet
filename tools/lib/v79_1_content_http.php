<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v79.1 · části 3–6 tools/v79_1_content_audit.php (používá $tmp, $check, $ROOT, $read, V791_KEY). Dočasné úložiště, fiktivní data, nic ostrého.
 */

$cache = $tmp . '-lm71';
@mkdir($cache, 0700, true);
register_shutdown_function(static function () use ($cache): void { edu_audit_remove_dir($cache); });
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V791_KEY, 'EDUCANET_TEACHER_ROLE' => 'admin', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cache];
$hasAction = static fn(string $html): bool => preg_match('~<a class="(?:ds-btn|btn)[^"]*" href="[^"]+">[^<]+~u', $html) === 1;
$explained = static fn(string $html): bool => str_contains($html, 'role="status"') && preg_match('~<(h2|h3|strong|p)[^>]*>[^<]{6,}~u', $html) === 1;

// ---------------------------------------------------------------- 3) Dnešní hodina (vykreslení nad modelem lt71, pevné datum výuky)
$now = (int)strtotime(EDU_AUDIT_TEACHING_DATE . ' 09:00:00');
$html = audit_capture(static function () use ($now): void { $_GET = ['class' => 'class_3a']; lt71_render_tab($GLOBALS['modules'], 'class_3a', $now); $_GET = []; });
$pos = static fn(string $needle): int => ($p = strpos($html, $needle)) === false ? -1 : $p;
$hero = (string)substr($html, max(0, $pos('class="ds78 lt791-hero"')), max(0, strpos($html, '</section>', max(0, $pos('class="ds78 lt791-hero"'))) - $pos('class="ds78 lt791-hero"')));
$check('Dnešní hodina – hero karta: nadpis s třídou, lekce, čas, cíl, stav schválení textem (odznak) a primární akce',
    $hero !== '' && str_contains($hero, '<h1 id="lt791-h1">Dnešní hodina · ') && str_contains($hero, '<dt>Čas</dt>') && str_contains($hero, '<dt>Cíl</dt>') && str_contains($hero, '<dt>Stav</dt>')
    && preg_match('~<span class="ds-badge is-(ok|warn|danger|info)">[^<]{6,}</span>~u', $hero) === 1 && preg_match('~class="ds-btn ds-btn--primary[^"]*"~', $hero) === 1 && str_contains($hero, 'Lekce '));
$order = ['plan', 'materialy', 'pomoc', 'exit', 'poznamky'];
$at = array_map(static fn(string $k): int => $pos('data-lt791-sec="' . $k . '"'), $order);
$sorted = $at;
sort($sorted);
$check('Dnešní hodina – pořadí sekcí: Plán po minutách → Materiály → Komu pomoct → Exit ticket → Poznámky (' . implode(' < ', $at) . '), pak téma, kód hodiny, úkoly, cesty, odkazy, žáci',
    !in_array(-1, $at, true) && $at === $sorted && $pos('data-lt791-sec="tema"') > $at[4] && $pos('data-lt791-sec="zaci"') > $pos('data-lt791-sec="odkazy"') && $pos('class="ds78 lt791-hero"') < $at[0]);
$ctx = (string)substr($html, max(0, $pos('<div class="lt791-ctx"')), max(0, $at[3] - $pos('<div class="lt791-ctx"')));
$check('Dnešní hodina – kontextový panel (role=complementary s popiskem) obsahuje Komu pomoct' . (str_contains($ctx, 'data-lt791-sec="t14"') ? ' a T-14' : '') . ' a stojí mezi Materiály a Exit ticketem (na xl vpravo přes CSS grid)',
    $ctx !== '' && str_contains($ctx, 'role="complementary" aria-label="Kontext hodiny: komu pomoct a T-14"') && str_contains($ctx, 'data-lt791-sec="pomoc"') && $pos('<div class="lt791-ctx"') > $at[1]);
$check('Dnešní hodina – sbalitelné sekce: kořen data-lt791, ≥ 10 sekcí s klíčem, každá s nadpisem h2; JS jen přes modul (žádný inline skript)',
    str_contains($html, '<div class="lt791" data-lt791>') && preg_match_all('~data-lt791-sec="[a-z0-9]+"~', $html) >= 10 && !str_contains($html, '<script'));
$check('Dnešní hodina – značky starších auditů zůstávají: „Průběh hodiny“, „Plán po minutách“, „Souhrn hodiny“, class="c70-table", nejvýš jeden formulář sess53_t_open',
    str_contains($html, 'Průběh hodiny') && str_contains($html, 'Plán po minutách') && str_contains($html, 'Souhrn hodiny') && str_contains($html, 'class="c70-table"') && substr_count($html, 'value="sess53_t_open"') <= 1);

// ---------------------------------------------------------------- 4) prázdné stavy (seznam stránek) + formuláře + ukazatele
$m = lt71_model($GLOBALS['modules'], 'class_3a', 0, $now);
$mPlan = $m;
$mPlan['lesson']['timeline'] = [];
$mExit = $m;
$mExit['class'] = '';
$mExit['lesson']['exit_ticket'] = [];
$mTasks = $m;
$mTasks['tasks'] = ['active' => 0, 'overdue' => 0, 'soon' => 0, 'top' => []];
$empties = [
    'Dnešní hodina – bez třídy' => audit_capture(static function () use ($now): void { $_GET = []; lt791_render_tab([], '', $now); }),
    'Dnešní hodina – bez plánu po minutách' => lt791_plan_html($mPlan),
    'Dnešní hodina – bez exit ticketu' => lt791_exit_html($mExit),
    'Dnešní hodina – bez úkolů' => lt791_tasks_html($mTasks),
    'Dnešní hodina – T-14 vše schválené' => lt791_t14_html($m, (int)strtotime('2099-01-05 09:00:00')),
    'Přehled třídy – bez žáků' => audit_capture(static function (): void { ov61_render_lagging(['students' => [], 'lagging' => []], 'class_3a'); }),
    'Přehled třídy – bez hlášení' => audit_capture(static function (): void { ov61_render_reports(['reports' => []], 'class_3a', 'x', true, []); }),
    'Přehled třídy – přihlášky a nákupy' => audit_capture(static function (): void { ov61_render_applications_and_purchases(['applications' => [], 'purchases' => []], 'class_3a', []); }),
    'Schvalování – bez návrhu' => lc72_table_html('class_3a', [], 0),
    'Obecný prázdný stav c70 (bez navržené záložky)' => c70_empty_html('Nic tu není.', 'Proč je prázdno.', []),
    'Tabulka – filtr bez výsledku' => dt791_table_html('e1', 'T', [['key' => 'n', 'label' => 'N', 'sort' => true]], [['sort' => ['n' => 1], 'text' => 'a', 'cells' => ['1']]], ['tab' => 'prehled'], dt791_state(['n'], 'n', 'asc', ['filtr' => 'zz'])),
];
$bad = [];
foreach ($empties as $label => $out) if (!$explained($out) || !$hasAction($out)) $bad[] = $label;
$check('prázdné stavy (' . count($empties) . ' vykreslených): role=status, co tu bude + proč je prázdno, tlačítko co dělat' . ($bad ? ' [' . implode('; ', $bad) . ']' : ''), $bad === [] && count($empties) === 11);
$src = [
    'Komu dnes pomoct – nikdo' => ['src/teacher/insights_v74_views.php', "'Otevřít Přehled třídy'"],
    'Knihovna materiálů – prázdná' => ['src/ops/materials_v74_views.php', 'href="#mat74-pridat">Přidat první materiál</a>'],
    'Hlášení – žádná / filtr' => ['src/student/feedback_v60_teacher_views.php', "'Zrušit filtr' : 'Otevřít Co řešit dnes'"],
    'Středa T-14 – třída bez lekcí' => ['src/teacher/t14_v77_views.php', "'Otevřít Schvalování lekcí', [], 'btn secondary'"],
    'Výsledky třídy – bez žáků' => ['src/teacher/teacher_class_dashboard.php', "ux791_empty_html('Třída zatím nemá žáky'"],
    'Žák – Úlohy bez sady' => ['src/assessment/taskbank_v75_views.php', '<a class="btn primary" href="?view=dashboard">\' . e(tr(\'Zpět na přehled\'))'],
    'Žák – Portfolio bez připnutých prací' => ['src/student/portfolio_v75_views.php', '<a class="btn primary" href="#sp79-top">\' . e(tr(\'Připnout první práci\'))'],
    'Žák – Náhled portfolia prázdný' => ['src/student/portfolio_v65_views.php', "e(tr('Připnout práci'))"],
];
$miss = [];
foreach ($src as $label => [$file, $needle]) if (!str_contains($read($file), $needle)) $miss[] = $label;
$check('prázdné stavy ve zdrojích (' . count($src) . ' dalších stránek) mají tlačítko akce' . ($miss ? ' [' . implode('; ', $miss) . ']' : ''), $miss === []);
$en75 = require $ROOT . '/lang/en/ui/v75.php';
$uk75 = require $ROOT . '/lang/uk/ui/v75.php';
$en65 = require $ROOT . '/lang/en/ui/projects_v65.php';
$uk65 = require $ROOT . '/lang/uk/ui/projects_v65.php';
$check('i18n žáka: nové texty prázdných stavů mají en i uk překlad', isset($en75['Zpět na přehled'], $uk75['Zpět na přehled'], $en75['Připnout první práci'], $uk75['Připnout první práci'], $en65['Připnout práci'], $uk65['Připnout práci'],
    $en65['Vyber ohodnocený projekt výše, nebo připni odkaz na svou práci níže.'], $uk65['Vyber ohodnocený projekt výše, nebo připni odkaz na svou práci níže.']));
$check('ukazatele: Výsledky třídy – rozložení známek přes ind791_bars_html (text + tabulka pro čtečky), Dnešní hodina – pruh postupu žáka má text „N/4 fáze“',
    str_contains($read('src/teacher/teacher_class_dashboard.php'), "ind791_bars_html('Průměrná projektová známka studentů – počet žáků podle známky'") && str_contains($html, '/4 fáze'));

// ---------------------------------------------------------------- 5) HTTP
audit_prewarm_accounts($GLOBALS['modules']);
$students3 = array_values(project_students_for_class('class_3a'));
$label3 = (string)($students3[0]['label'] ?? '');
$check('fixture: třída 3.A má žáky v seznamu (fiktivní soupiska aplikace)', $label3 !== '' && count($students3) >= 3);
$h = Harness::start($env);
$formPages = [];
try {
    $formPages['přihlášení učitele'] = (string)$h->request('GET', '/teacher.php')['body'];
    audit_login_teacher($h, V791_KEY, 'Audit Obsah');
    $pages = [];
    foreach (['hodina' => '/teacher.php?tab=hodina&class=class_3a', 'prehled' => '/teacher.php?tab=prehled&class=class_3a', 'schvalovani' => '/teacher.php?tab=schvalovani&class=class_3a',
        'class_results' => '/teacher.php?tab=class_results&class=class_3a', 'projektor' => '/teacher.php?tab=hodina_projektor&class=class_3a', 'tisk' => '/teacher.php?tab=hodina_tisk&class=class_3a'] as $k => $u) $pages[$k] = $h->request('GET', $u);
    $dirty = array_keys(array_filter($pages, static fn(array $r): bool => !audit_response_clean($r)));
    $check('HTTP: Dnešní hodina, Přehled třídy, Schvalování, Výsledky, Projektor a Tisk – 200 bez PHP chyb' . ($dirty ? ' [' . implode(', ', $dirty) . ']' : ''), $dirty === []);
    $hb = (string)$pages['hodina']['body'];
    $check('HTTP: Dnešní hodina načítá CSS v79.1 (rámec) a JS sbalitelných sekcí, hero + kontextový panel; Projektor a Tisk beze změny funkce (úseky, A4 stránky), bez JS v79.1',
        str_contains($hb, 'assets/content-v79-1.css') && str_contains($hb, 'assets/today-v79-1.js') && str_contains($hb, 'lt791-hero') && str_contains($hb, 'lt791-ctx')
        && str_contains((string)$pages['projektor']['body'], 'data-pj73') && substr_count((string)$pages['tisk']['body'], '<article class="lp73-page') >= 2 && !str_contains((string)$pages['tisk']['body'], 'today-v79-1.js'));
    $names = static function (string $body, string $tableId): array {
        $start = strpos($body, 'id="' . $tableId . '"');
        if ($start === false) return [];
        preg_match_all('~<th scope="row">(?:<a[^>]*>)?([^<]+)~u', substr($body, $start), $mm);
        return array_map('html_entity_decode', $mm[1]);
    };
    $pAsc = $names((string)$h->request('GET', '/teacher.php?tab=prehled&class=class_3a&razeni=zak&smer=asc')['body'], 'ov61-zaci');
    $pDesc = $names((string)$h->request('GET', '/teacher.php?tab=prehled&class=class_3a&razeni=zak&smer=desc')['body'], 'ov61-zaci');
    $first = (string)($pAsc[0] ?? '');
    $pFilt = $h->request('GET', '/teacher.php?tab=prehled&class=class_3a&filtr=' . rawurlencode(mb_substr($first, 0, 5)));
    $check('HTTP Přehled třídy: řazení podle žáka A–Z a Z–A (obrácené pořadí), filtr zúží tabulku a zachová záložku i třídu ve formuláři',
        count($pAsc) >= 3 && $pAsc === array_reverse($pDesc) && audit_response_clean($pFilt) && count($names((string)$pFilt['body'], 'ov61-zaci')) >= 1 && count($names((string)$pFilt['body'], 'ov61-zaci')) < count($pAsc)
        && str_contains((string)$pFilt['body'], 'name="filtr" inputmode="search" autocomplete="off" maxlength="80" value="' . e(mb_substr($first, 0, 5)) . '"'));
    $junk = $h->request('GET', '/teacher.php?tab=prehled&class=class_3a&razeni=%3Cscript%3E&smer=x&strana=abc&filtr=%3Cb%3E');
    $check('HTTP: neplatné ?razeni/?smer/?strana se ignorují (výchozí pořadí), filtr se jen escapuje (žádné neescapované <b>/<script> z URL)',
        audit_response_clean($junk) && !str_contains((string)$junk['body'], '<script>') && !str_contains((string)$junk['body'], 'value="<b>"') && str_contains((string)$junk['body'], 'value="&lt;b&gt;"'));
    $sAsc = $h->request('GET', '/teacher.php?tab=schvalovani&class=class_3a&razeni=lekce&smer=desc');
    preg_match_all('~lesson=(\d+)#lc72-preview~', substr((string)$sAsc['body'], (int)strpos((string)$sAsc['body'], 'id="lc72-tab"')), $ln);
    $nums = array_map('intval', $ln[1]);
    $check('HTTP Schvalování: řazení podle lekce sestupně, aria-sort na aktivním sloupci, filtr a počet „Zobrazeno …“',
        audit_response_clean($sAsc) && count($nums) >= 2 && $nums[0] > end($nums) && str_contains((string)$sAsc['body'], 'aria-sort="descending"') && str_contains((string)$sAsc['body'], 'Zobrazeno 1–'));
    $cr = (string)$h->request('GET', '/teacher.php?tab=class_results&class=class_3a&razeni=student&smer=desc')['body'];
    preg_match_all('~<tr data-result-row data-name="([^"]*)"~u', $cr, $crn);
    $check('HTTP Výsledky třídy: řazení na serveru (Student Z–A), záhlaví s aria-sort, buňky s data-label (karty na mobilu), posuvná oblast s popiskem, rozložení známek s tabulkou pro čtečky',
        count($crn[1]) >= 3 && strcmp($crn[1][0], end($crn[1])) >= 0 && str_contains($cr, 'aria-sort="descending"') && str_contains($cr, 'data-label="Průměr"') && str_contains($cr, 'role="region" tabindex="0" aria-label="Tabulka výsledků (posuvná)"')
        && str_contains($cr, '<table class="ds-sr"><caption>Průměrná projektová známka studentů'));
    $formUrls = ['hodina' => 'hodina&class=class_3a', 'Knihovna materiálů' => 'materialy74&class=class_3a', 'Přehled třídy' => 'prehled&class=class_3a', 'Schvalování' => 'schvalovani&class=class_3a',
        'Výsledky třídy' => 'class_results&class=class_3a', 'Reflexe' => 'reflexe73&class=class_3a', 'Nastavení výuky' => 'nastaveni_vyuky&class=class_3a', 'Hlášení' => 'hlaseni',
        'Detail žáka' => 'student360&class=class_3a', 'Plán a kurikulum' => 'curriculum&class=class_3a', 'Dotazník' => 'intake&class=class_3a', 'Nabídky projektů' => 'projekty&class=class_3a',
        'Obchod' => 'obchod&class=class_3a', 'Týmové hry' => 'hry&class=class_3a', 'CTF' => 'ctf&class=class_3a', 'Incidenty' => 'incidenty&class=class_3a', 'Intervence' => 'interventions&class=class_3a',
        'Demo účty' => 'demo_accounts&class=class_3a', 'Kód hodiny' => 'session&class=class_3a', 'Přístupy' => 'pristupy&class=class_3a', 'Hromadné schválení' => 'hromadne74&class=class_3a'];
    foreach ($formUrls as $label => $q) $formPages[$label] = (string)$h->request('GET', '/teacher.php?tab=' . $q)['body'];
} finally {
    $h->stop();
}
$hs = Harness::start($env);
try {
    $formPages['žák – přihlášení'] = (string)$hs->request('GET', '/?view=home')['body'];
    $login = audit_login_student($hs, 'class_3a', $label3);
    $formPages['žák – Portfolio'] = (string)$hs->request('GET', '/?view=portfolio')['body'];
    $formPages['žák – Úloha'] = (string)$hs->request('GET', '/?view=ulohy&uloha=net_ports')['body'];
    $sys = (string)$hs->request('GET', '/?view=ulohy')['body'];
    $set = $hs->request('POST', '/', ['action' => 'ui67_theme_set', 'theme' => 'dark', 'return' => '?view=ulohy', 'csrf' => (string)$login['csrf']], ['follow_redirects' => false]);
    $dU = $hs->request('GET', '/?view=ulohy&uloha=net_ports');
    $dP = $hs->request('GET', '/?view=portfolio');
    $hs->request('POST', '/', ['action' => 'ui67_theme_set', 'theme' => 'light', 'return' => '?view=ulohy', 'csrf' => (string)$hs->csrfToken((string)$dU['body'])], ['follow_redirects' => false]);
    $lU = (string)$hs->request('GET', '/?view=ulohy')['body'];
    $check('žák – tmavý režim Úloh a Portfolia: bez volby „podle systému“ (data-theme=system, tmavá vrstva jen skriptem při tmavém zařízení), volba Tmavý = data-theme=dark + tokeny + vrstva pohledu v69, Světlý = nic navíc; přepínač vzhledu je na stránce',
        str_contains($sys, 'data-theme="system"') && str_contains($sys, 'student-dark-ulohy-v69.css') && str_contains($sys, 'matchMedia') && in_array((int)$set['status'], [302, 303], true)
        && audit_response_clean($dU) && str_contains((string)$dU['body'], 'data-theme="dark"') && preg_match('~<link[^>]*assets/dark/student-dark-ulohy-v69\.css~', (string)$dU['body']) === 1 && str_contains((string)$dU['body'], 'tokens-dark-v67.css')
        && audit_response_clean($dP) && preg_match('~<link[^>]*assets/dark/student-dark-portfolio-v69\.css~', (string)$dP['body']) === 1 && str_contains((string)$dP['body'], 'ui67-theme')
        && str_contains($lU, 'data-theme="light"') && !str_contains($lU, 'student-dark-'));
} finally {
    $hs->stop();
}
$check('žák – tmavé vrstvy Úloh a Portfolia vygenerované (tools/v69_dark_overlay.php), v ui67_dark_views() a v DO69_VIEWS',
    in_array('ulohy', ui67_dark_views(), true) && in_array('portfolio', ui67_dark_views(), true) && is_file($ROOT . '/assets/dark/student-dark-ulohy-v69.css') && is_file($ROOT . '/assets/dark/student-dark-portfolio-v69.css')
    && str_contains($read('tools/v69_dark_overlay.php'), "'out' => 'assets/dark/student-dark-portfolio-v69.css'"));
$formBad = [];
$fields = 0;
foreach ($formPages as $label => $body) {
    $r = v791_form_issues($body);
    $fields += $r['fields'];
    foreach ($r['list'] as $issue) $formBad[] = $label . ': ' . $issue;
}
$check('formuláře (' . count($formPages) . ' stránek, ' . $fields . ' polí): popisek u každého pole, e-mail/heslo s autocomplete, čísla s inputmode, adresy type=url, jméno učitele autocomplete=username'
    . ($formBad ? ' [' . implode('; ', array_slice($formBad, 0, 6)) . ']' : ''), $formBad === [] && $fields > 150);
$check('formuláře: na mobilu 1 sloupec (CSS v79.1 pro starší mřížky formulářů), v hero kartě fieldset + legenda + nápověda pod polem (aria-describedby)',
    str_contains($css, '.teacher-two-col, .teacher-feedback-grid') && str_contains($read('src/lessons/lesson_today_v79_1_views.php'), '<fieldset class="lt791-fs"><legend>Hodina s kódem pro žáky</legend>') && str_contains($read('src/lessons/lesson_today_v79_1_views.php'), 'aria-describedby="lt791-kind-h"')
    && str_contains($read('src/lessons/lesson_today_v79_1_views.php'), '<p class="ds-help" id="lt791-kind-h">'));

// ---------------------------------------------------------------- 6) prohlížeč
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$mode = (string)getenv('EDUCANET_V791_BROWSER');
if ($mode === 'skip') { echo 'SKIP  prohlížečová část (EDUCANET_V791_BROWSER=skip)' . PHP_EOL; return; }
$bh = Harness::start($env);
try {
    $shots = (string)getenv('EDUCANET_V791_SHOTS');
    $cmd = escapeshellarg($node) . ' ' . escapeshellarg(__DIR__ . '/v79_1_content_browser.cjs') . ' ' . escapeshellarg($bh->baseUrl()) . ' ' . escapeshellarg(V791_KEY) . ' ' . escapeshellarg($label3) . ($shots !== '' ? ' ' . escapeshellarg($shots) : '');
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    $err = is_resource($proc) ? (string)stream_get_contents($pipes[2]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn ' . substr(trim($err), 0, 200);
    if ($mode === 'require') $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_V791_BROWSER=require ji vynutí)' . PHP_EOL;
    return;
}
$rows = (array)($d['rows'] ?? []);
$check('prohlížeč: skript doběhl bez chyby (6 stránek × 11 šířek × světlý/tmavý = 132 měření)', !isset($d['error']) && count($rows) === 132);
$fmt = static fn(array $r): string => $r['page'] . '@' . $r['width'] . $r['scheme'][0];
$hs2 = array_filter($rows, static fn(array $r): bool => $r['hscroll']);
$check('prohlížeč: 0× vodorovný posun na 11 šířkách (320–1920, 1366 × 600) v obou režimech' . ($hs2 ? ' [' . implode(', ', array_map($fmt, array_slice($hs2, 0, 6))) . ']' : ''), $hs2 === []);
$small = array_filter($rows, static fn(array $r): bool => $r['smallCount'] > 0);
$check('prohlížeč: 0 cílů < 44 px v obsahu v79.1 (hero, sbalovací tlačítka, tabulky – řazení, filtr, stránky, odkazy v řádcích, prázdné stavy, T-14)'
    . ($small ? ' [' . implode('; ', array_map(static fn(array $r): string => $fmt($r) . ' ' . implode(',', (array)$r['small']), array_slice($small, 0, 4))) . ']' : ''), $small === []);
$tiny = array_filter($rows, static fn(array $r): bool => $r['tinyCount'] > 0);
$check('prohlížeč: 0 textů < 14 px v obsahu v79.1' . ($tiny ? ' [' . implode('; ', array_map(static fn(array $r): string => $fmt($r) . ' ' . implode(',', (array)$r['tiny']), array_slice($tiny, 0, 4))) . ']' : ''), $tiny === []);
$hod = array_filter($rows, static fn(array $r): bool => $r['page'] === 'hodina');
$ctxBad = array_filter($hod, static fn(array $r): bool => $r['ctxRight'] !== ($r['width'] >= 1366));
$check('prohlížeč: kontextový panel (Komu pomoct, T-14) vpravo vedle plánu právě od 1366 px (xl), pod tím v toku stránky' . ($ctxBad ? ' [' . implode(', ', array_map($fmt, $ctxBad)) . ']' : ''), $hod !== [] && $ctxBad === []);
$tgBad = array_filter($hod, static fn(array $r): bool => ($r['toggles'] > 0) !== ($r['width'] < 768));
$check('prohlížeč: sbalitelné sekce (tlačítko v nadpisu, aria-expanded) jen pod 768 px' . ($tgBad ? ' [' . implode(', ', array_map($fmt, $tgBad)) . ']' : ''), $tgBad === []);
$tb = array_filter($rows, static fn(array $r): bool => $r['table'] !== null);
$tbBad = array_filter($tb, static fn(array $r): bool => $r['table']['cards'] !== ($r['width'] < 768) || ($r['width'] >= 768 && (!$r['table']['stickyHead'] || !$r['table']['stickyFirst'])));
$check('prohlížeč: tabulky (žáci, schvalování, výsledky, žáci v hodině) jako karty pod 768 px, od 768 px tabulka s lepicí hlavičkou a prvním sloupcem'
    . ($tbBad ? ' [' . implode(', ', array_map(static fn(array $r): string => $fmt($r) . json_encode($r['table']), array_slice($tbBad, 0, 4))) . ']' : ''), count($tb) >= 88 && $tbBad === []);
$dark = array_filter($rows, static fn(array $r): bool => in_array($r['page'], ['ulohy', 'portfolio'], true) && $r['scheme'] === 'dark');
$darkBad = array_filter($dark, static fn(array $r): bool => !$r['darkBg'] || !$r['lightText']);
$check('prohlížeč: žák – Úlohy a Portfolio při tmavém zařízení tmavé (pozadí stránky i krokového postupu tmavé, text světlý) na všech 11 šířkách' . ($darkBad ? ' [' . implode(', ', array_map($fmt, array_slice($darkBad, 0, 4))) . ']' : ''), count($dark) === 22 && $darkBad === []);
$mem = (array)($d['memory'] ?? []);
$check('prohlížeč: sbalení sekce na mobilu (aria-expanded=false, obsah skrytý) se pamatuje po obnovení stránky; bez úložiště (localStorage hází výjimku) vše rozbalené a bez chyby; na 1280 px vše rozbalené',
    ($mem['collapsed'] ?? false) && ($mem['remembered'] ?? false) && ($mem['noStorageOpen'] ?? false) && ($mem['noStorageNoError'] ?? false) && ($mem['desktopOpen'] ?? false));
if (!empty($d['shots'])) echo 'INFO  snímky: ' . count((array)$d['shots']) . ' (max ' . (int)($d['maxShot'] ?? 0) . ' B)' . PHP_EOL;
