<?php

declare(strict_types=1);

/**
 * ?view=cesty (seznam výukových cest třídy) a ?view=cesta&path=<id>&step=<id> (jeden krok) – v63.
 * Router: app/routes.php. Cesta a krok se validují proti katalogu třídy, neznámé hodnoty končí 404.
 * Jen pro třídy s cestami (3.A, 1.A); jinde 404. Identita žáka je ze session.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'cesty' || $view === 'cesta') {
    guarded_study_redirect();
    $p63Class = (string)$classId;
    $p63Path = null;
    $p63Step = null;
    if ($view === 'cesta') {
        $p63Path = p63_path_for_class($p63Class, is_string($_GET['path'] ?? null) ? (string)$_GET['path'] : '');
        $p63Step = $p63Path === null ? null : p63_step($p63Path, is_string($_GET['step'] ?? null) ? (string)$_GET['step'] : '');
    }
    if (!p63_enabled_for_class($p63Class) || ($view === 'cesta' && $p63Step === null)) {
        http_response_code(404);
        render_header(tr('Stránka nenalezena'), $module);
        echo '<div class="p63-wrap"><h1>' . e(tr('Tahle cesta neexistuje')) . '</h1><p><a class="ui-link" href="?view=dashboard">' . e(tr('Zpět na přehled')) . '</a></p></div>';
        render_footer();
        exit;
    }
    $p63Student = adaptive_student_key($p63Class);
    render_header($view === 'cesty' ? tr('Moje cesty') : (string)$p63Path['title'], $module, $view === 'cesta');
    if ($flash !== '') echo '<div class="notice" role="status">' . e($flash) . '</div>';
    if ($view === 'cesty') p63_render_list($p63Class, $p63Student);
    else p63_render_step($p63Class, $p63Student, $p63Path, $p63Step);
    render_footer();
    exit;
}
