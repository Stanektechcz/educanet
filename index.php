<?php

declare(strict_types=1);

/**
 * EDUCANET – žákovská aplikace: vstup, globální ochrany a router (v58 · F5).
 *
 * Tady zůstává jen: načtení jádra, přihlášení z dev-bypassu, CSRF a vynucená změna hesla u POST,
 * společný stav stránky, vynucená změna hesla a přihlašovací zeď u GET a router.
 * POST akce jsou v app/actions/*, pohledy v app/views/*, tabulka rout a knihoven v app/routes.php.
 * Segmenty se vkládají příkazem require v globálním rozsahu (vidí $modules, $classId, $module, $flash…).
 */

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/runtime_content.php';
require __DIR__ . '/app/lib.php';
app_require_libs(['core']);
try { acc53_provision_all($modules); } catch (Throwable $accError) { error_log('EDUCANET v53 účty: ' . $accError->getMessage()); }
try { if (function_exists('identity58_ensure')) identity58_ensure(); } catch (Throwable $idError) { error_log('EDUCANET identita: ' . $idError->getMessage()); }
acc58_auto_migrate(); // v58: sdílené heslo → jednorázová hesla (jen jednou, chyby jdou do logu)
try { intake_v51_sync_v1($modules); } catch (Throwable $intakeSyncError) { error_log('EDUCANET v51 V1 import: ' . $intakeSyncError->getMessage()); }

$view = isset($_GET['view']) && is_string($_GET['view']) ? $_GET['view'] : 'home';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
// v68: vyřazené pohledy (v48_state, continue, one_task) → 302 na přehled (app/redirects_v68.php).
require_once __DIR__ . '/app/redirects_v68.php';
routes68_redirect_if_retired($view, (string)$method, $_GET);
$runtimeClassId = current_class_id($modules);
$runtimeClasses = [];
$lightViews = ['home','link_account','verify_email','reset_password','privacy','activate','join','change_password'];
if ($runtimeClassId !== null && (!in_array($view,$lightViews,true) || $method === 'POST')) {
    $runtimeClasses = [$runtimeClassId];
    if (in_array($view,['knowledgebase','kb_lesson','kb_quiz','topics'],true)) {
        $runtimeClasses = allowed_subject_class_ids($runtimeClassId,$modules);
    }
}
$runtime = runtime_content_load_classes($runtimeClasses);
$knowledgeTours = $runtime['knowledgeTours'];
$nextLessons = $runtime['nextLessons'];
$extendedLessons = $runtime['extendedLessons'];
$simulations = $runtime['simulations'];
$learningResources = $runtime['learningResources'];
$schoolYear = require __DIR__ . '/school_year.php';

// Volitelná integrace: ?class=class_3a&student=Jméno
if ($method === 'GET' && educanet_dev_bypass_enabled() && isset($_GET['class']) && is_string($_GET['class']) && isset($modules[$_GET['class']])) {
    $_SESSION['next_class_id'] = $_GET['class'];
    if (isset($_GET['student']) && is_string($_GET['student'])) {
        $_SESSION['student_label'] = trim(u_substr($_GET['student'], 0, 120));
    }
    redirect_to('?view=dashboard');
}

// ---------------------------------------------------------------- POST akce
if ($method === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
    // v53: dokud si žák nenastaví vlastní heslo, projde jen změna hesla a odhlášení
    // (a po vstupu kódem hodiny i seznamovací dotazník – stejně jako u GET výjimek níže).
    if (acc53_must_change_password()) {
        // v59 OPS-02: i nový žák si musí umět přepnout jazyk rozhraní ještě před změnou hesla.
        $acc53AllowedPost = ['acc53_change_password', 'logout_class', 'edu_set_lang', 'ui67_theme_set'];
        if (!empty($_SESSION['sess53_joined'])) $acc53AllowedPost = array_merge($acc53AllowedPost, ['intake_submit', 'sess53_enter', 'sess53_register']);
        if (!in_array($action, $acc53AllowedPost, true)) {
            $_SESSION['flash'] = tr('Nejdřív si nastav vlastní heslo.');
            redirect_to('?view=change_password');
        }
    }
    // v67: po akci, která mění výsledek učení, se postup v dovednostech přepočítá po odeslání odpovědi (ne při GET přehledu).
    perf67_register_post_sync($modules, $action);
    // Akce, které třídu nepotřebují nebo si ji ověřují samy (app/routes.php › actions_pre).
    foreach (app_segments('actions_pre', $action) as $appSegment) require app_segment_file($appSegment);

    // Ostatní akce vyžadují třídu (app/routes.php › actions_class). Neznámá akce se třídou pokračuje na GET pohled.
    $classId = current_class_id($modules);
    if ($classId === null) {
        redirect_to('?view=home');
    }
    $module = $modules[$classId];
    foreach (app_segments('actions_class', $action) as $appSegment) require app_segment_file($appSegment);
}

// ---------------------------------------------------------------- společný stav stránky
$classId = current_class_id($modules);
if ($classId === null && auth_user() !== null && $view !== 'link_account' && try_restore_auth_assignment($modules)) { redirect_to('?view=dashboard'); }
$classId = current_class_id($modules);
$module = $classId !== null ? $modules[$classId] : null;
$sessionTestResult = $_SESSION['next_result'] ?? null;
$completedTestResult = ($classId !== null && is_array($sessionTestResult) && (($sessionTestResult['class_id'] ?? null) === $classId)) ? $sessionTestResult : null;
if ($classId !== null && $completedTestResult === null && auth_is_signed_in()) {
    $persistedTests = learning_student_rows(STORAGE_DIR . '/practice_results.json.php', (string)$classId, 1);
    if (is_array($persistedTests[0] ?? null) && isset($persistedTests[0]['score'], $persistedTests[0]['max_score'])) {
        $completedTestResult = $persistedTests[0];
        $_SESSION['next_result'] = $completedTestResult;
    }
}
$flash = isset($_SESSION['flash']) ? (string)$_SESSION['flash'] : '';
unset($_SESSION['flash']);

// ---------------------------------------------------------------- pohledy před vynucenou změnou hesla
foreach (app_segments('views_early', $view) as $appSegment) require app_segment_file($appSegment);

// Globální ochrana v53: účet se školním heslem musí nejdřív nastavit vlastní heslo.
// Po vstupu kódem má přednost seznamovací dotazník; heslo si žák nastaví hned po něm.
$acc53DeferViews = !empty($_SESSION['sess53_joined']) ? ['intake', 'my_intake', 'my_intake_file', 'join'] : [];
if ($view === 'change_password' || (acc53_must_change_password() && !in_array($view, array_merge(['privacy', 'logout'], $acc53DeferViews), true) && $method === 'GET')) {
    require app_segment_file(app_page('change_password'));
}

// ---------------------------------------------------------------- veřejné pohledy (odkazy z e-mailu, soukromí, propojení účtu)
foreach (app_segments('views_public', $view) as $appSegment) require app_segment_file($appSegment);

// v60: přihlášený žák (vybraná třída) na úvodní stránce nevidí přihlašovací formulář – přesměrování na přehled.
if ($view === 'home' && $module !== null && $method === 'GET') {
    redirect_to('?view=dashboard');
}

// Globální ochrana: bez třídy (nepřihlášený žák) vždy přihlašovací stránka.
if ($view === 'home' || $module === null) {
    require app_segment_file(app_page('home'));
}

// ---------------------------------------------------------------- žákovské pohledy (pořadí podle app/routes.php)
foreach (app_segments('views_student', $view) as $appSegment) require app_segment_file($appSegment);

redirect_to('?view=dashboard');
