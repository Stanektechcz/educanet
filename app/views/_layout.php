<?php

declare(strict_types=1);

/**
 * Hlavička a patička žákovských stránek, studijní postup.
 * Přesunuto z index.php (v58 · F5); jediná změna: odkazy na CSS/JS jdou přes asset_url() (DAT-05).
 * Spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function render_header(string $title, ?array $module = null, bool $titleIsContent = false): void
{
    global $modules, $view, $nextLessons, $extendedLessons;
    $accent = $module['accent'] ?? 'default';
    $cid = null;
    $compactAssessment = in_array((string)$view, ['test', 'kb_quiz'], true);
    if ($module !== null) {
        $cid = current_class_id($modules);
    }
    $continueUrl=($module!==null&&is_string($cid)&&$cid!=='')?v506_continue_url((string)$cid,$module,$nextLessons,$extendedLessons):'?view=dashboard';
    $GLOBALS['v55_continue_url']=$continueUrl;
    ?>
<!doctype html>
<html lang="<?= e(edu_html_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
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
    <link rel="stylesheet" href="<?= e(asset_url('assets/brand-v54.css?v=54.1')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/student-v55.css?v=55.1')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/learning-v56.css?v=56.1')) ?>">
    <?php if (in_array((string)$view, ['lab', 'prikazy'], true)): ?><link rel="stylesheet" href="<?= e(asset_url('assets/linux-v57.css?v=59.1')) ?>"><link rel="stylesheet" href="<?= e(asset_url('assets/arena-v57.css?v=57.0')) ?>"><?php endif; ?>
    <?php if ($module === null && google_auth_configured()): ?><script src="https://accounts.google.com/gsi/client" async defer></script><?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset_url('assets/i18n-v59.css?v=59.0')) ?>">
    <script src="<?= e(asset_url('assets/i18n-v58.js?v=58.0')) ?>" defer></script>
    <?= edu_tr_json_js() ?>
</head>
<?php $v507ShellActive=$module!==null&&in_array((string)$view,v507_shell_views(),true); ?>
<?php $v55Lock = v55_lock_active((string)$view); ?>
<body class="accent-<?= e($accent) ?><?= $compactAssessment ? ' assessment-mode' : '' ?> view-<?= e((string)$view) ?><?= $v507ShellActive?' v507-shell-active':'' ?><?= $v55Lock ? ' v55-locked' : '' ?>"<?= $v55Lock ? ' data-v55-lock="on"' : '' ?>>
<div class="shell">
    <header class="topbar<?= $compactAssessment ? ' assessment-topbar' : '' ?>">
        <a class="brand" href="?view=<?= $module ? 'dashboard' : 'home' ?>"><span class="brand-mark">E</span><span>EDUCANET</span></a>
        <?php if (!$module): ?>
            <div class="topbar-right topbar-lang-only"><?= edu_lang_switcher_html(csrf_token(), '?view=' . (string)$view) ?></div>
        <?php endif; ?>
        <?php if ($module): ?>
            <?php if (!$compactAssessment): ?>
                <?php /* v56: pět položek, nic víc. Vše ostatní je uvnitř Materiálů nebo v účtovém menu. */ ?>
                <nav class="main-menu student-main-menu v5077-calm-nav v55-nav" aria-label="<?= e(tr('Hlavní studentské menu')) ?>" data-main-menu>
                    <?php foreach (v55_primary_nav($cid, (string)$view) as $item): $navLabel = (string)$item['label']; ?>
                        <a class="<?= e((string)$item['class']) ?>" href="<?= e((string)$item['href']) ?>"<?= !empty($item['active']) ? ' aria-current="page"' : '' ?>><i aria-hidden="true"><?= e((string)$item['mark']) ?></i><?= e(tr($navLabel)) ?></a>
                    <?php endforeach; ?>
                </nav>
                <div class="topbar-right v55-right">
                    <?php if (is_string($cid) && $cid !== ''): v55_render_level_chip($cid); endif; ?>
                    <div class="v56-menu" data-v56-menu>
                        <button class="v56-menu-button" type="button" data-v56-menu-button aria-label="<?= e(tr('Menu účtu')) ?>" aria-expanded="false" aria-controls="v56-account-menu"><span><?= e(tr('Menu')) ?></span><i aria-hidden="true">⌄</i></button>
                        <div class="v56-menu-panel" id="v56-account-menu" data-v56-menu-panel>
                            <span class="v56-menu-label"><?= e(tr('Učení')) ?></span>
                            <?php foreach (v55_primary_nav($cid, (string)$view) as $item): $navLabel = (string)$item['label']; ?><a class="v55-mobile-only" href="<?= e((string)$item['href']) ?>"><?= e(tr($navLabel)) ?></a><?php endforeach; ?>
                            <a href="?view=materialy&amp;sekce=temata"><?= e(tr('Témata a vysvětlení')) ?></a>
                            <a href="?view=materialy&amp;sekce=programy"><?= e(tr('Programy a zkratky')) ?></a>
                            <a href="?view=prikazy"><?= e(tr('Linux příkazy')) ?></a>
                            <span class="v56-menu-label"><?= e(tr('Třída')) ?></span>
                            <a href="?view=community"><?= e(tr('Spolužáci')) ?></a>
                            <a href="?view=project_lobbies"><?= e(tr('Týmy a projekty')) ?></a>
                            <span class="v56-menu-label"><?= e(tr('Účet')) ?></span>
                            <a href="?view=profile"><?= e(tr('Můj profil')) ?></a>
                            <a href="?view=obchod"><?= e(tr('Obchod')) ?></a>
                            <a href="?view=projekty"><?= e(tr('Projekty')) ?></a>
                            <a href="?view=hlaseni"><?= e(tr('Nahlásit chybu')) ?></a>
                            <a href="?view=my_intake"><?= e(tr('Můj dotazník')) ?></a>
                            <a href="?view=study_loop"><?= e(tr('Potřebuju pomoct')) ?></a>
                            <div class="v56-menu-lang"><?= edu_lang_switcher_html(csrf_token(), '?view=' . (string)$view) ?></div>
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
    <?php if($module!==null&&!$compactAssessment): ?>
    <?php if($v507ShellActive): ?>
    <?php v507_render_page_shell((string)$cid,(string)$view,$title,$module,$nextLessons,$extendedLessons,$titleIsContent); ?>
    <?php else: ?>
    <?php v506_render_student_compass((string)$cid,(string)$view,$title,$module,$continueUrl,$titleIsContent); ?>
    <?php endif; ?>
    <?php endif; ?>
    <main>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <?php v55_footer_cta(); ?>
    <footer class="footer"><a href="?view=privacy"><?= e(tr('Soukromí')) ?></a><?php $fbView = (string)($GLOBALS['view'] ?? ''); if (!empty($_SESSION['next_class_id']) && $fbView !== 'home'): ?><a class="fb60-footer-link" style="display:inline-flex;align-items:center;min-height:44px;padding:0 10px;margin-left:8px" href="?view=hlaseni<?= preg_match('/^[a-z0-9_]{1,40}$/', $fbView) === 1 && $fbView !== 'hlaseni' ? '&amp;page=' . e($fbView) : '' ?>"><?= e(tr('Nahlásit chybu')) ?></a><?php endif; ?><?= edu_lang_switcher_html(csrf_token(), '?view=' . (string)($GLOBALS['view'] ?? 'dashboard')) ?></footer>
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
