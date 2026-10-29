<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/levels_v75.php';

/**
 * EDUCANET v75 · záložka cockpitu `urovne75` (česky): návrhy úrovní Základ / Standard / Výzva pro L1–L16, schválení po lekcích
 * nebo hromadně (POST lv75_approve / lv75_return, content.manage) a rozložení voleb žáků (bez jmen).
 */

const LV75_STATUS_LABELS = ['navrh' => 'návrh', 'schvaleno' => 'schváleno', 'vraceno' => 'vráceno k úpravě', 'zmeneno' => 'změněno od schválení', 'chybi' => 'bez návrhu'];

function lv75_tab_url(string $classId, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'urovne75', 'class' => $classId] + $extra);
}

function lv75_render_teacher_tab(array $modules, string $classId, string $csrf): void
{
    $classes = array_values(array_intersect(array_keys(LV75_CLASS_FILES), function_exists('c70_classes') ? c70_classes() : array_keys(LV75_CLASS_FILES)));
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · diferenciace</div><h1>Úrovně v hodině</h1>'
        . '<p>Hlavní aktivita každé lekce má tři úrovně: <strong>Základ</strong> (s oporou), <strong>Standard</strong> a <strong>Výzva</strong> (navíc). Žák si úroveň vybírá sám; '
        . 'systém mu jednu doporučí podle jeho posledních exit ticketů a úloh s variantami a vždy napíše proč. Návrhy úrovní uvidí žáci až po vašem schválení.</p></div></section>';
    if ($class === '') { echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Úrovně se ukážou, jakmile vám administrátor přidělí třídu.', []) : ''; return; }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(lv75_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    $content = lv75_content($class);
    $statusData = storage_read(lv75_status_path(), false);
    $status = [];
    foreach ($content as $n => $_) $status[$n] = lv75_status($class, $n, $statusData);
    $dist = lv75_distribution($class);
    $count = static fn(string $s): int => count(array_filter($status, static fn(array $r): bool => $r['status'] === $s));
    $picks = array_sum(array_column($dist, 'total'));
    $followed = array_sum(array_column($dist, 'followed'));
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Lekce s návrhem', (string)count($content), 'L1–L' . LV75_LESSONS_MAX, ''],
        ['Schváleno', (string)$count('schvaleno'), 'žáci volbu vidí', $count('schvaleno') === count($content) ? 'ok' : ''],
        ['Čeká na schválení', (string)($count('navrh') + $count('zmeneno')), $count('zmeneno') > 0 ? $count('zmeneno') . ' změněno od schválení' : 'návrhy agenta', $count('navrh') + $count('zmeneno') > 0 ? 'warn' : 'ok'],
        ['Volby žáků', (string)$picks, $picks > 0 ? round($followed / $picks * 100) . ' % podle doporučení' : 'zatím žádná', ''],
    ], 'Souhrn úrovní');
    echo lv75_distribution_html($dist);
    $can = function_exists('teacher_permission') && teacher_permission('content.manage');
    $pending = array_keys(array_filter($status, static fn(array $r): bool => in_array($r['status'], ['navrh', 'zmeneno'], true)));
    $hidden = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="class_id" value="' . e($class) . '"><input type="hidden" name="return_tab" value="urovne75">';
    echo '<section class="c70-card c70-wide" aria-labelledby="lv75-t-list"><h2 id="lv75-t-list">Návrhy úrovní po lekcích</h2>';
    if ($can && $pending !== []) {
        echo '<form method="post" class="kp75-form">' . $hidden . '<input type="hidden" name="action" value="lv75_approve">';
        foreach ($pending as $n) echo '<input type="hidden" name="lessons[]" value="' . (int)$n . '">';
        echo '<label class="kp75-check"><input type="checkbox" name="confirm" value="1" required> Prošel/prošla jsem návrhy lekcí ' . e('L' . implode(', L', $pending)) . '.</label>'
            . '<button class="btn primary" type="submit">Schválit všechny čekající (' . count($pending) . ')</button></form>';
    } elseif (!$can) {
        echo '<p class="c70-muted">Schvalovat může učitel s oprávněním ke správě obsahu (asistent jen čte).</p>';
    }
    echo '<div class="lv75-lessons">';
    foreach ($content as $n => $row) {
        $st = $status[$n];
        echo '<details class="lv75-lesson"' . ($st['status'] !== 'schvaleno' && $n === ($pending[0] ?? 0) ? ' open' : '') . '><summary><strong>L' . $n . ' · ' . e((string)$row['activity']) . '</strong> '
            . '<span class="c70-chip' . ($st['status'] === 'schvaleno' ? ' c70-chip-ok' : ' c70-chip-warn') . '">' . e(LV75_STATUS_LABELS[$st['status']]) . '</span></summary><dl class="lv75-levels">';
        foreach (LV75_LEVELS as $key => $label) echo '<dt>' . e($label) . '</dt><dd>' . e((string)($row[$key]['task'] ?? '')) . ' <span class="c70-muted">Hotovo, když: ' . e((string)($row[$key]['check'] ?? '')) . '</span></dd>';
        echo '</dl>';
        if ($st['status'] === 'vraceno' && $st['note'] !== '') echo '<p class="c70-muted">Poznámka k úpravě: ' . e($st['note']) . '</p>';
        if ($can) {
            echo '<div class="c70-actions">';
            if ($st['status'] !== 'schvaleno') echo '<form method="post">' . $hidden . '<input type="hidden" name="action" value="lv75_approve"><input type="hidden" name="lessons[]" value="' . (int)$n . '"><input type="hidden" name="confirm" value="1"><button class="btn secondary" type="submit">Schválit L' . $n . '</button></form>';
            echo '<form method="post" class="lv75-return">' . $hidden . '<input type="hidden" name="action" value="lv75_return"><input type="hidden" name="lessons[]" value="' . (int)$n . '">'
                . '<label for="lv75-n' . $n . '">Co upravit</label><input id="lv75-n' . $n . '" name="note" maxlength="' . LV75_NOTE_MAX . '" required><button class="btn secondary" type="submit">Vrátit L' . $n . '</button></form></div>';
        }
        echo '</details>';
    }
    echo '</div></section>';
}

function lv75_distribution_html(array $dist): string
{
    $html = '<section class="c70-card c70-wide" aria-labelledby="lv75-t-dist"><h2 id="lv75-t-dist">Jak si žáci vybírají</h2>';
    if ($dist === []) return $html . '<p class="c70-muted" role="status">Zatím si nikdo úroveň nevybral. Volba se žákům ukáže v lekci, jakmile úrovně schválíte; do hodnocení nevstupuje.</p></section>';
    $html .= '<p class="c70-muted">Počty voleb po lekcích (bez jmen). „Podle doporučení“ = žák zvolil úroveň, kterou mu systém navrhl.</p>'
        . '<div class="kp75-scroll"><table class="kp75-table"><caption class="c70-sr">Rozložení úrovní</caption><thead><tr><th scope="col">Lekce</th><th scope="col">Základ</th><th scope="col">Standard</th><th scope="col">Výzva</th><th scope="col">Rozložení</th><th scope="col">Podle doporučení</th></tr></thead><tbody>';
    foreach ($dist as $n => $c) {
        $bar = '';
        foreach (['zaklad', 'standard', 'vyzva'] as $k) if ($c['total'] > 0 && $c[$k] > 0) $bar .= '<span class="lv75-bar lv75-' . $k . '" style="width:' . round($c[$k] / $c['total'] * 100) . '%"></span>';
        $html .= '<tr><th scope="row">L' . (int)$n . '</th><td>' . $c['zaklad'] . '</td><td>' . $c['standard'] . '</td><td>' . $c['vyzva'] . '</td><td><span class="lv75-bars" aria-hidden="true">' . $bar . '</span></td><td>' . $c['followed'] . ' z ' . $c['total'] . '</td></tr>';
    }
    return $html . '</tbody></table></div></section>';
}
