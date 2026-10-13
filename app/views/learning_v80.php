<?php

declare(strict_types=1);

/**
 * v80 · podpora učení žáka: ?view=cviceni (interaktivní cvičení, ?w=), ?view=opakovat (opakování chyb, ?uloha=).
 * Identita žáka jen ze session; do URL jde nejvýš id cvičení nebo úlohy. Výsledky posledních kontrol jsou v relaci a po vykreslení se smažou.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'cviceni') {
    guarded_study_redirect();
    $lr80W = is_string($_GET['w'] ?? null) ? (string)$_GET['w'] : '';
    render_header(isset(WG80_WIDGETS[$lr80W]) ? wg80_title($lr80W) : tr('Interaktivní cvičení'), $module);
    if ((string)$flash !== '') echo '<div class="notice" role="status">' . e((string)$flash) . '</div>';
    wg80_render_page((string)$classId, adaptive_student_key((string)$classId), $lr80W);
    unset($_SESSION['lr80_w_last']);
    render_footer();
    exit;
}

if ($view === 'opakovat') {
    guarded_study_redirect();
    render_header(tr('Opakování chyb'), $module);
    if ((string)$flash !== '') echo '<div class="notice" role="status">' . e((string)$flash) . '</div>';
    lr80_render_review_page((string)$classId, adaptive_student_key((string)$classId), is_string($_GET['uloha'] ?? null) ? (string)$_GET['uloha'] : '');
    unset($_SESSION['lr80_rep_last']);
    render_footer();
    exit;
}
