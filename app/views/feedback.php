<?php

declare(strict_types=1);

/**
 * ?view=hlaseni – nahlášení chyby / návrh vylepšení pro žáka (v60). Router: app/routes.php.
 * ?page=<view> jen předvyplní název stránky (whitelist fb60_pages(), jinak se ignoruje).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'hlaseni') {
    guarded_study_redirect();
    $studentKey = adaptive_student_key((string)$classId);
    $page = fb60_page_clean(is_string($_GET['page'] ?? null) ? (string)$_GET['page'] : '');
    $draft = is_array($_SESSION['fb60_draft'] ?? null) ? $_SESSION['fb60_draft'] : [];
    unset($_SESSION['fb60_draft']);
    render_header(tr('Nahlásit chybu'), $module);
    if ($flash !== '') { echo '<div class="notice" role="status">' . e($flash) . '</div>'; }
    feedback60_render_page((string)$classId, $studentKey, $page !== '' ? $page : fb60_page_clean((string)($draft['page'] ?? '')), $draft);
    render_footer();
    exit;
}
