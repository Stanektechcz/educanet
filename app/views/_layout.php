<?php

declare(strict_types=1);

/**
 * Hlavička a patička žákovských stránek, studijní postup.
 * Přesunuto z index.php (v58 · F5); jediná změna: odkazy na CSS/JS jdou přes asset_url() (DAT-05).
 * Spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once dirname(__DIR__, 2) . '/nav_v61.php';   // v61: drobečky a spodní lišta
require_once dirname(__DIR__, 2) . '/ui_v67.php';    // v67: vzhled (data-theme) a prázdné stavy

function render_header(string $title, ?array $module = null, bool $titleIsContent = false): void
{
    global $modules, $view, $nextLessons, $extendedLessons;
    $accent = $module['accent'] ?? 'default';
    $cid = null;
    $compactAssessment = in_array((string)$view, ['test', 'kb_quiz'], true);
    if ($module !== null) {
        $cid = current_class_id($modules);
    }
    $ui61Cid = ($module !== null && is_string($cid)) ? $cid : null;
    $GLOBALS['ui61_primary_nav'] = null;   // nové vykreslení hlavičky = čerstvé položky (v jednom požadavku se počítají jednou)
    $ui61Nav = $module !== null ? nav61_primary_items($ui61Cid, (string)$view) : [];   // v61: položky hlavní navigace se počítají jednou pro menu, drobečky i spodní lištu
    $continueUrl=($module!==null&&is_string($cid)&&$cid!=='')?v506_continue_url((string)$cid,$module,$nextLessons,$extendedLessons):'?view=dashboard';
    $GLOBALS['v55_continue_url']=$continueUrl;
    ?>
<!doctype html>
<html lang="<?= e(edu_html_lang()) ?>"<?= ui67_html_theme_attr((string)$view) ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="<?= e(ui67_color_scheme((string)$view)) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title) ?> · EDUCANET</title>
    <link rel="manifest" href="manifest.webmanifest">
    <meta name="theme-color" content="#f6f7f9">
    <link rel="stylesheet" href="<?= e(asset_url('assets/app.css?v=46')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/mastery.css?v=46')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/cognitive-v43.css?v=46')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/learning-studio-v44.css?v=46')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/visual-simulation-v45.css?v=46')) ?>">
    <?php if(in_array((string)$view,['dashboard','study','mistakes','study_loop'],true)): ?><link rel="stylesheet" href="<?= e(asset_url('assets/student-coach-v47.css?v=47.2')) ?>"><?php endif; ?>
    <?php if((string)$view==='visual_lab'): ?><link rel="stylesheet" href="<?= e(asset_url('assets/visual-practical-v48.css?v=48')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/visual-labs-3a-v48-1.css?v=48.1')) ?>"><?php endif; ?>
    <?php if(in_array((string)$view,['hands_on','growth','growth_path','skill_passport','peer_lab'],true)): ?><link rel="stylesheet" href="<?= e(asset_url('assets/hands-on-v50.css?v=50')) ?>"><?php endif; ?>
    <?php if($module !== null): ?><link rel="stylesheet" href="<?= e(asset_url('assets/student-ui-v50-7-7.css?v=51.0')) ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset_url('assets/ui-v51.css?v=51.0')) ?>">
    <?php if ($module !== null): ?><link rel="stylesheet" href="<?= e(asset_url('assets/tutorial-v52.css?v=52.0')) ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset_url('assets/session-v53.css?v=53.0')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/tokens-v61.css?v=61.0')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/brand-v54.css?v=61.0')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/student-v55.css?v=55.1')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/learning-v56.css?v=56.1')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/components-v61.css?v=61.0')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/nav-v61.css?v=61.1')) ?>">
    <?= ui67_assets_html((string)$view) ?>
    <?= ui67_dark_link_html((string)$view) /* v67: tmavé tokeny jen pro ověřené pohledy a jen při volbě „tmavý“/„podle systému“ */ ?>
    <?php if ($module !== null): ?><script src="<?= e(asset_url('assets/nav-v61.js?v=61.1')) ?>" defer></script><?php endif; ?>
    <?php if (in_array((string)$view, ['lab', 'prikazy'], true)): ?><link rel="stylesheet" href="<?= e(asset_url('assets/linux-v57.css?v=59.1')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/arena-v57.css?v=57.0')) ?>"><?php endif; ?>
    <?php if ($module === null && google_auth_configured()): ?><script src="https://accounts.google.com/gsi/client" async defer></script><?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset_url('assets/i18n-v59.css?v=59.0')) ?>">
    <?php if (!empty($GLOBALS['login63'])): ?><link rel="stylesheet" href="<?= e(asset_url('assets/login-v63.css?v=63.0')) ?>"><?php endif; ?>
    <script src="<?= e(asset_url('assets/i18n-v58.js?v=58.0')) ?>" defer></script>
    <?= edu_tr_json_js() ?>
</head>
<?php $v507ShellActive=$module!==null&&in_array((string)$view,v507_shell_views(),true); ?>
<?php $v55Lock = v55_lock_active((string)$view); ?>
<?php $ui61Bottom = $module !== null && !nav61_bottom_hidden((string)$view); $GLOBALS['ui61_bottomnav'] = $ui61Bottom; ?>
<body class="accent-<?= e($accent) ?><?= $compactAssessment ? ' assessment-mode' : '' ?> view-<?= e((string)$view) ?><?= $v507ShellActive?' v507-shell-active':'' ?><?= $v55Lock ? ' v55-locked' : '' ?><?= $ui61Bottom ? ' ui61-has-bottomnav' : '' ?><?= !empty($GLOBALS['login63']) ? ' login63-page' : '' ?>"<?= $v55Lock ? ' data-v55-lock="on"' : '' ?>>
<?= nav61_skip_html() ?>
<div class="shell">
    <?php if (empty($GLOBALS['login63'])): ?>
    <header class="topbar<?= $compactAssessment ? ' assessment-topbar' : '' ?>">
        <a class="brand" href="?view=<?= $module ? 'dashboard' : 'home' ?>"><span class="brand-mark">E</span><span>EDUCANET</span></a>
        <?php if (!$module): ?>
            <div class="topbar-right topbar-lang-only"><?= edu_lang_switcher_html(csrf_token(), '?view=' . (string)$view) ?></div>
        <?php endif; ?>
        <?php if ($module): ?>
            <?php if (!$compactAssessment): ?>
                <?php /* v61 (nav): pět míst (Dnes, Učení, Hry a aréna, Projekty, Profil) s podmenu; vykresluje nav_v61.php. */ ?>
                <?= nav61_main_html($ui61Cid, (string)$view) ?>
                <div class="topbar-right v55-right">
                    <?php if (is_string($cid) && $cid !== ''): v55_render_level_chip($cid); endif; ?>
                    <div class="v56-menu" data-v56-menu>
                        <button class="v56-menu-button" type="button" data-v56-menu-button aria-label="<?= e(tr('Menu účtu')) ?>" aria-expanded="false" aria-controls="v56-account-menu"><?= nav61_icon('menu') ?><span><?= e(tr('Menu')) ?></span><i aria-hidden="true">⌄</i></button>
                        <div class="v56-menu-panel" id="v56-account-menu" data-v56-menu-panel>
                            <?= nav61_drawer_html($ui61Cid, (string)$view) /* v61: skupiny odkazů jen na úzkém displeji; na širším je nahrazuje horní lišta */ ?>
                            <div class="v56-menu-lang"><?= edu_lang_switcher_html(csrf_token(), '?view=' . (string)$view) ?></div>
                            <div class="v56-menu-lang"><?= ui67_theme_switch_html(csrf_token(), '?view=' . (string)$view, (string)$view) ?></div>
                            <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="logout_class"><button type="submit"><?= e(tr('Odhlásit se')) ?></button></form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($compactAssessment): ?>
            <div class="topbar-right"><div class="assessment-mode-label"><span><?= e(tr('Soustředění')) ?></span><strong><?= e($view === 'test' ? tr('Startovní test') : tr('Knowledge check')) ?></strong></div><span class="v55-lock-flag"><?= e(tr('Zamčeno do odevzdání')) ?></span></div>
            <?php endif; ?>
        <?php endif; ?>
    </header>
    <?php endif; ?>
    <main class="ui-page" id="main-content" tabindex="-1"><?php /* v62: h1 stránkového shellu leží uvnitř <main> */ ?>
    <?php if($module!==null&&!$compactAssessment): ?>
    <?php if($v507ShellActive): ?>
    <?= nav61_breadcrumb_html(is_string($cid) ? $cid : null, (string)$view, $title, $titleIsContent) ?>
    <?php v507_render_page_shell((string)$cid,(string)$view,$title,$module,$nextLessons,$extendedLessons,$titleIsContent); ?>
    <?php else: ?>
    <?php v506_render_student_compass((string)$cid,(string)$view,$title,$module,$continueUrl,$titleIsContent); ?>
    <?php endif; ?>
    <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <?php if (!empty($GLOBALS['login63'])): echo login63_footer_html(); else: ?>
    <?php v55_footer_cta(); ?>
    <footer class="footer"><a href="?view=privacy"><?= e(tr('Soukromí')) ?></a><?php $fbView = (string)($GLOBALS['view'] ?? ''); if (!empty($_SESSION['next_class_id']) && $fbView !== 'home'): ?><a class="fb60-footer-link" style="display:inline-flex;align-items:center;min-height:44px;padding:0 10px;margin-left:8px" href="?view=hlaseni<?= preg_match('/^[a-z0-9_]{1,40}$/', $fbView) === 1 && $fbView !== 'hlaseni' ? '&amp;page=' . e($fbView) : '' ?>"><?= e(tr('Nahlásit chybu')) ?></a><?php endif; ?><?= edu_lang_switcher_html(csrf_token(), '?view=' . (string)($GLOBALS['view'] ?? 'dashboard')) ?></footer>
    <?php endif; ?>
<?php if (!empty($GLOBALS['ui61_bottomnav'])): $ui61Cid = current_class_id($GLOBALS['modules'] ?? []); echo nav61_bottom_html(is_string($ui61Cid) ? $ui61Cid : null, (string)($GLOBALS['view'] ?? '')); endif; ?>
</div>
<script src="<?= e(asset_url('assets/app.js?v=46')) ?>"></script>
<?php if(($GLOBALS['module']??null)!==null): ?><script src="<?= e(asset_url('assets/student-ui-v50-7-7.js?v=51.0')) ?>"></script><?php endif; ?>
<script src="<?= e(asset_url('assets/ui-v51.js?v=51.0')) ?>" defer></script>
<script src="<?= e(asset_url('assets/student-v55.js?v=55.1')) ?>" defer></script>
<script src="<?= e(asset_url('assets/learning-v56.js?v=56.1')) ?>" defer></script>
<?php if (in_array((string)($GLOBALS['view'] ?? ''), ['lab', 'prikazy'], true)): ?><script src="<?= e(asset_url('assets/linux-v57.js?v=59.1')) ?>" defer></script><script src="<?= e(asset_url('assets/arena-v57.js?v=57.0')) ?>" defer></script><?php endif; ?>
<?php if(($GLOBALS['module']??null)!==null && (string)($GLOBALS['view']??'')==='goal_nav'): ?><script src="<?= e(asset_url('assets/goal-navigator-v50-4.js?v=50.4')) ?>"></script><?php endif; ?>
<?php if(in_array((string)($GLOBALS['view']??''),['dashboard','study','mistakes','study_loop'],true)): ?><script src="<?= e(asset_url('assets/student-coach-v47.js?v=47.2')) ?>"></script><?php endif; ?>
<?php if((string)($GLOBALS['view']??'')==='visual_lab'): ?><script src="<?= e(asset_url('assets/visual-practical-v48.js?v=48')) ?>"></script><script src="<?= e(asset_url('assets/visual-labs-3a-v48-1.js?v=48.1')) ?>"></script><?php endif; ?>
<?php if(in_array((string)($GLOBALS['view']??''),['hands_on','growth','growth_path','skill_passport','peer_lab'],true)): ?><script src="<?= e(asset_url('assets/hands-on-v50.js?v=50')) ?>"></script><?php endif; ?>
<script src="<?= e(asset_url('assets/cognitive-v43.js?v=46')) ?>"></script><script src="<?= e(asset_url('assets/learning-studio-v44.js?v=46')) ?>"></script><script src="<?= e(asset_url('assets/visual-simulation-v45.js?v=46')) ?>"></script>
</body>
</html>
    <?php
}

/** v63 · Patička přihlašovací stránky (render_footer() ji použije místo běžné patičky). Vrací HTML. */
function login63_footer_html(): string
{
    $year = (string)($GLOBALS['schoolYear']['meta']['school_year'] ?? '');
    $heart = '<span class="login63-heart" aria-hidden="true">❤️</span><span class="login63-sr">' . e(tr('láskou')) . '</span>';
    $credit = tr_html('S {heart} vytvořil {a}stanektech.cz{/a}', ['heart' => $heart, 'a' => '<a href="' . e(safe_url('https://stanektech.cz')) . '" target="_blank" rel="noopener noreferrer">', '/a' => '</a>']);
    return '<footer class="login63-footer"><p class="login63-credit">' . $credit . '</p>' . ui67_theme_switch_html(csrf_token(), "?view=home", "home") . '<p class="login63-meta">'
        . ($year !== '' ? e(tr('Školní rok')) . ' ' . e($year) . ' · ' : '') . 'EDUCANET v63 · <a href="?view=privacy">' . e(tr('Soukromí')) . '</a></p></footer>';
}
