<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

// v51 Light Clarity audit: dotazník z V1, aktivační účty, body v testech, krokové lekce a světlé tokeny.
// v58 F3: kontroly jsou přednostně behaviorální (funkční volání + HTTP přes dev server), ne
// "obsahuje řetězec"; audit běží v dočasném úložišti (nikdy v ostrých datech žáků).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once __DIR__ . '/lib/audit_storage.php';
$storageDir = edu_audit_temp_storage('v51');

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/teacher_operations_v46.php'; // teacher_permission() – vyžaduje intake_v51_render_teacher_tab()
require_once $root . '/intake_v51.php';
require_once $root . '/intake_v51_teacher.php';
require_once $root . '/app/views/_diagrams.php'; // u51_practice_points()

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($root . '/' . $rel);

$index = edu_app_source();
$css = $read('assets/ui-v51.css');
$js = $read('assets/ui-v51.js');
$sw = $read('sw.js');

// --- Soubory (knihovny bez vlastního vstupu – zapojení do stránek ověřuje HTTP blok níže) ---
foreach (['intake_v51.php', 'intake_v51_views.php', 'intake_v51_teacher.php'] as $file) {
    $check('exists ' . $file, is_file($root . '/' . $file), false);
}

// --- HTTP: přihlašovací stránka, dotazník, aktivace, export, CSRF (dev server přes http_harness) ---
audit_prewarm_accounts($modules); // založí účty ještě v tomto procesu (viz audit_prewarm_accounts – jinak start serveru vyprší)
$teacherKey = 'audit-v51-' . bin2hex(random_bytes(6));
$harness = Harness::start([
    'EDUCANET_STORAGE_DIR' => $storageDir,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey,
]);
try {
    $home = $harness->request('GET', '/?view=home');
    $check('přihlašovací stránka nabízí vstup aktivačním kódem', audit_response_clean($home) && str_contains((string)$home['body'], 'href="?view=activate"'));
    $activate = $harness->request('GET', '/?view=activate');
    $check('stránka aktivace se vykreslí bez přihlášení', audit_response_clean($activate) && str_contains((string)$activate['body'], 'Zadej aktivační kód'));

    $login = audit_login_student($harness, 'class_1a', 'Audit Tester 51');
    $check('žák se dev-bypassem přihlásí a přehled se vykreslí bez chyby', audit_response_clean($login['response']));
    $dashboardBody = (string)$login['response']['body'];
    $check('přehled nabízí dotazník jako první krok', str_contains($dashboardBody, 'href="?view=intake"'));
    $check('pokračovací tlačítko na přehledu nikdy nevede zpátky na přehled', !preg_match('/student-primary-cta"[^>]*href="\?view=dashboard"/', $dashboardBody));

    $intake = $harness->request('GET', '/?view=intake');
    $check('dotazník se vykreslí přihlášenému žákovi', audit_response_clean($intake) && str_contains((string)$intake['body'], 'Seznamovací dotazník'));

    foreach (['assets/ui-v51.css', 'assets/ui-v51.js'] as $asset) {
        $resp = $harness->request('GET', '/' . $asset, ['v' => '51.0']);
        $check('asset ' . $asset . ' je dostupný (HTTP 200)', $resp['status'] === 200);
    }

    $topic = (string)array_key_first($modules['class_1a']['knowledgebase'] ?? []);
    $kbRedirect = $harness->request('GET', '/?view=one_task', ['task' => 'kb', 'topic' => $topic], ['follow_redirects' => false]);
    $check('vysvětlení z One Task přesměruje na plnou interaktivní lekci (kb_lesson)', in_array($kbRedirect['status'], [301, 302, 303, 307, 308], true) && str_contains((string)($kbRedirect['headers']['Location'] ?? ''), 'view=kb_lesson'));

    $csrf = $harness->csrfToken((string)$intake['body']);
    $badCode = $harness->request('POST', '/', ['action' => 'intake_activate', 'code' => 'ZZZZ-0000', 'step' => 'check', 'csrf' => (string)$csrf]);
    $check('POST intake_activate s CSRF opravdu ověří kód (neznámý kód se odmítne)', str_contains((string)$badCode['body'], 'Tento aktivační kód neznáme'));
    $noCsrf = $harness->request('POST', '/', ['action' => 'intake_activate', 'code' => 'ZZZZ-0000', 'step' => 'check']);
    $check('POST intake_activate bez CSRF je odmítnut (419)', $noCsrf['status'] === 419);

    $teacherLogin = audit_login_teacher($harness, $teacherKey);
    $check('učitel se přihlásí učitelským klíčem', audit_response_clean($teacherLogin['response']));
    $csv = $harness->request('GET', '/teacher.php', ['tab' => 'intake', 'class' => 'class_1a', 'export' => 'csv']);
    $check('export dotazníku (CSV) má BOM a hlavičku sloupců', str_starts_with((string)$csv['body'], "\xEF\xBB\xBF") && str_contains((string)$csv['body'], 'Jméno;'));
} finally {
    $harness->stop();
}

// --- Učitelská záložka (přímé volání vykreslovací funkce, žádná HTTP režie navíc) ---
$teacherTabHtml = audit_capture(static function () use ($modules): void { intake_v51_render_teacher_tab($modules); });
$check('učitelská záložka vykreslí dotazník s importem z V1', $teacherTabHtml !== '' && str_contains($teacherTabHtml, 'intake_t_sync'));

// --- Třídy a typy dotazníku ---
$classes = intake_v51_classes($modules);
$check('questionnaire exists for 1.A–4.A', count(array_intersect(['class_1a', 'class_2a', 'class_3a', 'class_4a'], array_keys($classes))) === 4);
$check('1.A and 2.A use graphics poster task', ($classes['class_1a']['course_type'] ?? '') === 'graphics' && ($classes['class_2a']['course_type'] ?? '') === 'graphics');
$check('3.A basic and 4.A advanced SOSaPS quiz', ($classes['class_3a']['course_type'] ?? '') === 'networks' && ($classes['class_4a']['course_type'] ?? '') === 'networks_advanced');
$check('every class has classroom seats', !array_filter($classes, static fn(array $c): bool => count($c['seats']) < 2));

// --- Body ---
$q3 = intake_v51_quiz_questions('networks');
$q4 = intake_v51_quiz_questions('networks_advanced');
$check('quiz banks have 10 questions each', count($q3) === 10 && count($q4) === 10);
$allCorrect = [];
foreach ($q3 as $q) $allCorrect[$q['id']] = $q['correct'];
$score = intake_v51_score_quiz('networks', $allCorrect);
$check('quiz scoring: all correct = 10/10 b.', $score['score'] === 10 && $score['max_score'] === 10 && $score['percentage'] === 100 && !$score['missing']);
$partial = $allCorrect; $partial[array_key_first($partial)] = 'z';
$score = intake_v51_score_quiz('networks', $partial);
$check('quiz scoring: invalid answer is missing, not a point', $score['score'] === 9 && count($score['missing']) === 1);
$fresh = u51_practice_points([['attempts' => 1, 'hints_used' => 0]]);
$struggled = u51_practice_points([['attempts' => 3, 'hints_used' => 1]]);
$check('practice checkpoints award points (first try ' . $fresh . '/' . U51_PRACTICE_MAX_POINTS . ', with hints ' . $struggled . ')', $fresh === U51_PRACTICE_MAX_POINTS && $struggled === 1 && $struggled < $fresh);

// --- Aktivační kódy ---
$check('activation code normalisation', intake_v51_normalize_code('ab cd-12 34') === 'ABCD-1234' && intake_v51_normalize_code('short') === '');
$codes = [];
for ($i = 0; $i < 200; $i++) $codes[intake_v51_generate_code($codes)] = true;
$check('activation codes are unique and unambiguous', count($codes) === 200 && !array_filter(array_keys($codes), static fn(string $c): bool => (bool)preg_match('/[01IO]/', $c)));

// --- Import z V1 ---
$v1 = intake_v51_v1_read('responses.json');
if ($v1) {
    intake_v51_sync_v1($modules);
    $imported = array_column(intake_v51_responses(), 'id');
    $state51 = intake_v51_read('import_state');
    $deleted = array_map('strval', (array)($state51['deleted'] ?? []));
    $missing = array_filter($v1, static fn($r): bool => is_array($r) && isset($modules[(string)($r['class_id'] ?? '')]) && !in_array((string)$r['id'], $imported, true) && !in_array((string)$r['id'], $deleted, true));
    $check('all V1 responses are imported (' . count($v1) . ')', !$missing);
    $acts = intake_v51_activations();
    $withoutAccount = array_filter(intake_v51_responses(), static fn(array $r): bool => ($r['source'] ?? '') === 'V1' && !isset($acts[intake_v51_response_key($r)]));
    $check('every V1 student has a prepared account', !$withoutAccount);
    $before = count(intake_v51_responses());
    intake_v51_sync_v1($modules, true);
    $check('import is idempotent', count(intake_v51_responses()) === $before);
    $posters = array_filter(intake_v51_responses(), static fn(array $r): bool => !empty($r['assessment']['artifact']['storage_name']));
    $check('imported posters are available in protected storage', !array_filter($posters, static fn(array $r): bool => intake_v51_upload_path((string)$r['assessment']['artifact']['storage_name']) === null));
} else {
    echo "SKIP  V1 data folder not present" . PHP_EOL;
}

// --- Krokové lekce a interaktivní obsah (JS/CSS – bez JS runtime nelze ověřit chováním) ---
$shellCss = $read('assets/student-ui-v50-7-7.css');
$check('lesson content is not hidden behind Detail', !str_contains($shellCss, 'view-kb_lesson:not(.v507-details-expanded) .kb-lesson-screen') && !str_contains($shellCss, 'view-course_lesson:not(.v507-details-expanded) .next-lesson-shell'), false);
$check('long lesson pages use step mode', str_contains($js, "['view-case_study', 'view-lesson_kit']"), false);
$check('wizard validates seat and required answers', str_contains($js, 'Vyber prosím své místo v učebně.') && str_contains($js, 'Odpověz prosím na všechny otázky.'), false);

// --- Světlé tokeny (vlastní CSS vlastnosti rozřešené na skutečnou hodnotu, ne přesný zápis) ---
$vars = audit_css_vars($css);
$colorTokens = ['panel', 'border', 'soft', 'panel-soft', 'card', 'danger'];
$badColor = [];
foreach ($colorTokens as $name) {
    if (audit_hex_to_rgb(audit_css_resolve($vars, $vars[$name] ?? '')) === null) $badColor[] = $name;
}
$check('světlé tokeny (--panel, --border, --soft…) se rozřeší na platnou barvu', $vars !== [] && !$badColor);
$shadow = audit_css_resolve($vars, $vars['shadow-sm'] ?? '');
$check('token --shadow-sm je stín, ne barva plochy', str_contains($shadow, 'rgba') && str_contains($shadow, 'px'));
$ink = audit_css_resolve($vars, $vars['text'] ?? '');
$bg = audit_css_resolve($vars, $vars['bg'] ?? '');
$panel = audit_css_resolve($vars, $vars['panel'] ?? '');
$contrastBg = audit_contrast_ratio($ink, $bg);
$contrastPanel = audit_contrast_ratio($ink, $panel);
$check('text na pozadí má kontrast alespoň 4.5:1 (' . ($contrastBg !== null ? round($contrastBg, 2) : '?') . ':1)', $contrastBg !== null && $contrastBg >= 4.5);
$check('text na panelu má kontrast alespoň 4.5:1 (' . ($contrastPanel !== null ? round($contrastPanel, 2) : '?') . ':1)', $contrastPanel !== null && $contrastPanel >= 4.5);

preg_match('/@media\s*\(prefers-color-scheme:\s*dark\)[^{]*\{\s*:root[^{]*\{([^}]*)\}/s', $css, $darkMatch);
$darkVars = array_merge($vars, audit_css_vars($darkMatch[1] ?? ''));
$darkBg = audit_css_resolve($darkVars, $darkVars['bg'] ?? '');
$check('systémový tmavý režim neztmaví pozadí aplikace (' . $darkBg . ')', $darkMatch !== [] && $bg !== '' && $darkBg === $bg);

$check('brand duplication fix', str_contains($css, '.brand > span:last-child::after { content: none !important; }'), false);
$check('mobile navigation stays visible', str_contains($css, '.student-main-menu.v5077-calm-nav { position: static !important; display: flex !important;'), false);
// ř. 169 (dřív): "educanet-v51-light-clarity" je dnes jen historický komentář v sw.js (ř. 13), ne
// živý CACHE název – ověřujeme aktivní `const CACHE=` (verzovaný educanet-v5x identifikátor) a
// skutečnou přítomnost ui-v51.css v SHELL (viz PLAN_F4_PROD.md A.2).
$v51ActiveCacheOk = (bool)preg_match('/const CACHE="educanet-v5\d/', $sw);
$check('PWA caches v51 assets', str_contains($sw, 'assets/ui-v51.css?v=51.0') && $v51ActiveCacheOk, false);

// F4-5 (PLAN_F4_PROD.md A.3): dřívější pravidlo skrylo detail žákovi natvrdo (viz v59 F4-5 – smazáno
// v student-ui-v50-7-7.{css,js}). Ověřujeme chování, ne konkrétní soubor: aktivní žákovská CSS
// (resolve z asset_url() volání v app/views/_layout.php) nesmí obsahovat pravidlo, které skrývá
// .v507-detail-only nebo .social-about přes display:none.
$layout = $read('app/views/_layout.php');
preg_match_all("/asset_url\\('assets\\/(student-ui-v[0-9-]+\\.css)[^']*'\\)/", $layout, $activeCssMatches);
$activeCssFiles = array_unique($activeCssMatches[1] ?? []);
$check('_layout.php odkazuje na aktivní student-ui-vN.css', $activeCssFiles !== []);
$hidesDetailOnly = false;
$hidesSocialAbout = false;
$hideRulePattern = '/([^{}]*\.(?:v507-detail-only|social-about)[^{}]*)\{([^}]*)\}/';
foreach ($activeCssFiles as $activeCssFile) {
    $activeCss = $read('assets/' . $activeCssFile);
    if (preg_match_all($hideRulePattern, $activeCss, $ruleMatches, PREG_SET_ORDER)) {
        foreach ($ruleMatches as $ruleMatch) {
            if (!preg_match('/display\s*:\s*none/', $ruleMatch[2])) continue;
            if (str_contains($ruleMatch[1], 'v507-detail-only')) $hidesDetailOnly = true;
            if (str_contains($ruleMatch[1], 'social-about')) $hidesSocialAbout = true;
        }
    }
}
$check('aktivní CSS neskrývá .v507-detail-only přes display:none', !$hidesDetailOnly);
$check('aktivní CSS neskrývá .social-about přes display:none', !$hidesSocialAbout);

// V59-A11Y-02: JS toggle přidávající .v507-details-expanded byl v v59 smazán, takže jakékoli
// zbylé ":not(.v507-details-expanded)"/".v507-details-expanded" pravidlo v aktivní CSS už natrvalo
// skrývá obsah (classmate-grid, social-showcase, skill-detail-layout, …). Chování, ne jeden soubor.
$hasDeadDetailToggleRule = false;
foreach ($activeCssFiles as $activeCssFile) {
    $activeCss = $read('assets/' . $activeCssFile);
    if (str_contains($activeCss, 'v507-details-expanded') || str_contains($activeCss, 'v507-detail-toggle')) {
        $hasDeadDetailToggleRule = true;
    }
}
$check('aktivní CSS neobsahuje mrtvé pravidlo ".v507-details-expanded"/".v507-detail-toggle" (Detail toggle smazán v v59)', !$hasDeadDetailToggleRule);

exit(audit_summary($state, 'V51_LIGHT_CLARITY'));
