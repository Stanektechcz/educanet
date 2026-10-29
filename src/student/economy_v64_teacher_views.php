<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/economy_v64.php';
require_once __DIR__ . '/arena_v64_fair.php';
require_once __DIR__ . '/teamgames_v64_roles.php';

/**
 * EDUCANET v64 · cockpit učitele: záložka „Ekonomika“ (vždy česky).
 *
 * Obsah: měsíční report inflace XP (medián, p90, podíl omezených událostí, nákupy v obchodě bodů), přepínač absolutního
 * žebříčku u konkrétní akce (CTF, robotí liga, týmová hra; výchozí vypnuto) a retrospektivy týmových her (bez jmen;
 * vidí je jen učitel s rozsahem třídy, ne asistent). ELO a liga se nikdy nepoužívají pro známky ani exporty známek.
 * POST v64_abs_board (CSRF, třída v rozsahu – politika v teacher_scope_v59.php, handler ji ověřuje znovu).
 */

/** @return list<string> */
function eco64_teacher_classes(): array
{
    return function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : [];
}

function eco64_teacher_handle_post(string $action): void
{
    if ($action !== 'v64_abs_board') throw new RuntimeException('Neznámá akce ekonomiky.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    $kind = is_string($_POST['kind'] ?? null) ? (string)$_POST['kind'] : '';
    $id = is_string($_POST['id'] ?? null) ? (string)$_POST['id'] : '';
    $on = (string)($_POST['on'] ?? '') === '1';
    if (!in_array($classId, eco64_teacher_classes(), true)) throw new RuntimeException('Třída není v rozsahu.');
    $ok = fair64_absolute_board_set($classId, $kind, $id, $on);
    if (function_exists('teacher_flash')) teacher_flash($ok ? ($on ? 'Absolutní žebříček je zapnutý.' : 'Absolutní žebříček je vypnutý.') : 'Změnu se nepodařilo uložit.', $ok ? 'ok' : 'error');
    if (function_exists('teacher_redirect')) teacher_redirect(['tab' => 'ekonomika', 'class' => $classId]);
    redirect_to('teacher.php?tab=ekonomika&class=' . rawurlencode($classId));
}

function eco64_pct(float $share): string
{
    return number_format($share * 100, 0, ',', '') . ' %';
}

function eco64_report_html(array $r): string
{
    $c = $r['current'];
    $p = $r['previous'];
    $change = $r['median_change'] === null ? 'bez srovnání (minulý měsíc bez dat)' : (($r['median_change'] >= 0 ? '+' : '') . eco64_pct((float)$r['median_change']));
    $html = '<table class="eco64-t-table"><caption>Měsíc ' . e((string)$r['month']) . ' · denní strop her ' . (int)$r['cap'] . ' XP na žáka</caption>'
        . '<thead><tr><th scope="col">Ukazatel</th><th scope="col">Tento měsíc</th><th scope="col">Minulý měsíc</th></tr></thead><tbody>'
        . '<tr><th scope="row">Žáci s XP z her</th><td>' . (int)$c['students'] . '</td><td>' . (int)$p['students'] . '</td></tr>'
        . '<tr><th scope="row">Medián XP z her na žáka</th><td>' . e((string)$c['median']) . '</td><td>' . e((string)$p['median']) . '</td></tr>'
        . '<tr><th scope="row">90. percentil</th><td>' . e((string)$c['p90']) . '</td><td>' . e((string)$p['p90']) . '</td></tr>'
        . '<tr><th scope="row">Podíl událostí omezených stropem</th><td>' . eco64_pct((float)$c['capped_share']) . '</td><td>' . eco64_pct((float)$p['capped_share']) . '</td></tr>'
        . '<tr><th scope="row">Nákupy v obchodě bodů</th><td>' . (int)$r['purchases'] . '</td><td>–</td></tr>'
        . '</tbody></table><p class="eco64-t-note">Změna mediánu proti minulému měsíci: ' . e($change) . '. Hry dávají jen XP; body (obchod) a Kč (projekty) se z her nezískávají.</p>';
    return $html;
}

/** Přepínače absolutního žebříčku pro akce třídy. */
function eco64_board_rows(string $classId): array
{
    $rows = [['kind' => 'robots', 'id' => '', 'label' => 'Robotí liga (celá třída)']];
    if (function_exists('arena58_ctf_events_for_class')) {
        foreach (arena58_ctf_events_for_class($classId) as $e) $rows[] = ['kind' => 'ctf', 'id' => (string)$e['id'], 'label' => 'CTF: ' . (string)$e['title']];
    }
    if (function_exists('tg58_sessions_for_class')) {
        foreach (array_slice(tg58_sessions_for_class($classId), 0, 10) as $s) $rows[] = ['kind' => 'tg', 'id' => (string)$s['id'], 'label' => 'Týmová hra: ' . (string)$s['title']];
    }
    return $rows;
}

function eco64_board_form(string $classId, array $row, string $csrf): string
{
    $on = fair64_absolute_board_enabled((string)$row['kind'], $classId, (string)$row['id']);
    return '<li><span>' . e((string)$row['label']) . ' – žebříček: <strong>' . ($on ? 'zapnutý' : 'vypnutý') . '</strong></span> '
        . '<form method="post" class="eco64-t-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="v64_abs_board">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '"><input type="hidden" name="kind" value="' . e((string)$row['kind']) . '"><input type="hidden" name="id" value="' . e((string)$row['id']) . '">'
        . '<input type="hidden" name="on" value="' . ($on ? '0' : '1') . '"><button type="submit">' . ($on ? 'Vypnout' : 'Zapnout') . '</button></form></li>';
}

/** Retrospektivy her třídy (bez jmen); jen s oprávněním (rozsah třídy, ne asistent). */
function eco64_retro_html(string $classId): string
{
    if (!tg64_retro_teacher_can($classId)) return '<p class="eco64-t-note">Retrospektivy týmových her vidí jen učitel s přístupem ke třídě (ne asistent).</p>';
    $html = '';
    foreach (storage_read(tg64_retro_path()) as $sessionId => $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
        $teams = tg64_retro_for_teacher((string)$sessionId);
        $title = function_exists('tg58_get') ? (string)(tg58_get((string)$sessionId)['title'] ?? $sessionId) : (string)$sessionId;
        $html .= '<h4>' . e($title) . '</h4><ul>';
        foreach ($teams as $teamId => $entries) {
            foreach ($entries as $a) $html .= '<li>Tým ' . e((string)$teamId) . ': ' . e(implode(' / ', array_filter(array_map('strval', $a), 'strlen'))) . '</li>';
        }
        $html .= '</ul>';
    }
    return $html === '' ? '<p class="eco64-t-note">Zatím žádné retrospektivy.</p>' : $html;
}

function eco64_render_teacher_tab(string $requestedClass, string $csrf): void
{
    $classes = eco64_teacher_classes();
    echo '<section class="teacher-panel eco64-t-wrap"><h2>Ekonomika her</h2>';
    if ($classes === []) { echo '<p class="teacher-empty">Nemáte přístup k žádné třídě.</p></section>'; return; }
    $classId = in_array($requestedClass, $classes, true) ? $requestedClass : $classes[0];
    $month = is_string($_GET['month'] ?? null) && preg_match(STORAGE_MONTH_RE, (string)$_GET['month']) === 1 ? (string)$_GET['month'] : date('Y-m');
    $links = '';
    foreach ($classes as $c) $links .= '<a class="' . ($c === $classId ? 'active' : '') . '" href="?tab=ekonomika&class=' . e(rawurlencode($c)) . '&month=' . e($month) . '"' . ($c === $classId ? ' aria-current="page"' : '') . '>' . e($c) . '</a> ';
    echo '<nav aria-label="Třída">' . $links . '</nav>';
    echo '<p class="eco64-t-note">Všechny hry a arény dávají jen XP a společně nejvýš ' . ECO64_XP_DAY_CAP . ' XP na žáka a den. ELO a liga jsou jen motivační a nikdy nejdou do známek ani exportů známek. Žáci vidí vlastní pozici a ligu; absolutní žebříček zapnete u konkrétní akce.</p>';
    echo '<h3>Měsíční report inflace XP</h3>' . eco64_report_html(eco64_inflation_report($classId, $month));
    echo '<h3>Absolutní žebříček u akcí</h3><ul class="eco64-t-list">';
    foreach (eco64_board_rows($classId) as $row) echo eco64_board_form($classId, $row, $csrf);
    echo '</ul><p class="eco64-t-note">U závodu v Aréně se žebříček volí při jeho vytvoření (hodnocení „žebříček“); výchozí je osobní rekord.</p>';
    echo '<h3>Retrospektivy týmových her</h3>' . eco64_retro_html($classId) . '</section>';
}
