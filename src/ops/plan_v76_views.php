<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · záložka „Plán vs. skutečnost“ (modul `plan76`, sekce Výuka) a stránka k tisku (GET ?tisk=1, politika plan76|tisk:
 * třída povinná a v rozsahu). Jen čtení, bez jmen žáků; česky; každý výstup přes e(). Logika v plan_v76.php.
 */

require_once dirname(__DIR__, 2) . '/src/ops/plan_v76.php';

const PV76_CLASSES = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];

function pv76_tab_url(string $class, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'plan76', 'class' => $class] + $extra);
}

function pv76_date(string $date): string
{
    return $date === '' ? '—' : date('j. n.', (int)strtotime($date));
}

/** Třídy v rozsahu učitele (c70_classes) v pořadí kalendáře. */
function pv76_classes(array $modules): array
{
    $allowed = function_exists('c70_classes') ? c70_classes() : PV76_CLASSES;
    return array_values(array_filter(PV76_CLASSES, static fn(string $c): bool => in_array($c, $allowed, true) && isset($modules[$c])));
}

function pv76_render_tab(array $modules, string $classId): void
{
    $classes = pv76_classes($modules);
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · plán</div><h1>Plán vs. skutečnost</h1>'
        . '<p>Porovnání tematického plánu (28 lekcí v kalendáři školy) s tím, co se opravdu odučilo podle reflexí hodin. Ukáže zpoždění, prognózu do konce roku '
        . 'a návrhy, jak dohnat. Nic se samo nezapisuje: posun potvrdíte výjimkou v Kalendáři, stav hodiny reflexí.</p></div></section>';
    if ($class === '') {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Plán vs. skutečnost se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(pv76_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    try {
        $a = pv76_class($class);
    } catch (Throwable $e) {
        error_log('EDUCANET v76 plán vs. skutečnost: ' . get_class($e));
        echo '<section class="c70-empty" role="status"><h2>Přehled se teď nepodařilo sestavit.</h2><p>Zkuste stránku obnovit.</p></section>';
        return;
    }
    echo pv76_summary_html($a);
    echo '<p class="pv76-actions"><a class="btn secondary" href="' . e(pv76_tab_url($class, ['tisk' => '1'])) . '" target="_blank" rel="noopener">Verze k tisku (A4)</a> '
        . (function_exists('c70_tab_visible') && c70_tab_visible('reflexe73') ? '<a class="btn secondary" href="' . e('?' . http_build_query(['tab' => 'reflexe73', 'class' => $class])) . '">Zapsat reflexi hodiny</a> ' : '')
        . (function_exists('c70_tab_visible') && c70_tab_visible('calendar') ? '<a class="btn secondary" href="?tab=calendar">Kalendář a výjimky</a>' : '') . '</p>';
    echo pv76_suggestions_html($a);
    echo pv76_table_html($a);
    echo '<p class="c70-muted">Plán = termíny lekcí v kalendáři školy (school_year.php). Kalendář = termín po výjimkách třídy (odpadlá hodina posune další lekce). '
        . 'Stav bere poslední reflexi hodiny, kterou smíte číst. „Nepotvrzeno“ znamená, že termín minul a reflexe chybí – do zpoždění se počítá jako odučeno.</p>';
}

/** Ukazatele a varování (sdílí záložka i tisk). */
function pv76_summary_html(array $a): string
{
    $f = $a['forecast'];
    $c = $a['count'];
    $html = function_exists('c70_kpis_html') ? c70_kpis_html([
        ['Zpoždění vůči plánu', $a['delay'] > 0 ? pv76_num($a['delay']) . ' lekce' : 'v plánu', 'plán do dneška ' . $a['planned_by_today'] . ' · odučeno ' . pv76_num($a['done']), $a['delay'] >= 2 ? 'warn' : ($a['delay'] > 0 ? '' : 'ok')],
        ['Prognóza', 'stihnete ' . $f['fits'] . ' z ' . PV76_LESSONS, $f['missing'] > 0 ? 'chybí ' . $f['missing'] . ' h i po rezervě' : '+ rezerva ' . $f['reserve_left'] . ' h', $f['missing'] > 0 ? 'warn' : 'ok'],
        ['Odučeno / část', $c['odučeno'] . ' / ' . $c['část'], $c['neodučeno'] . ' neodučeno · ' . $c['nepotvrzeno'] . ' bez reflexe', $c['neodučeno'] > 0 ? 'warn' : ''],
        ['Posunuto kalendářem', (string)($c['posunuto'] + $c['mimo kalendář']), count($a['cancelled']) . ' odpadlých hodin · ' . $c['mimo kalendář'] . ' mimo kalendář', $c['mimo kalendář'] > 0 ? 'warn' : ''],
    ], 'Plán vs. skutečnost') : '';
    if ($a['warnings'] !== []) {
        $html .= '<div class="pv76-warn" role="status"><h2 class="c70-sr">Upozornění</h2><ul>';
        foreach ($a['warnings'] as $w) $html .= '<li>' . e($w) . '</li>';
        $html .= '</ul></div>';
    } elseif ($a['done'] <= 0.0) {
        $html .= '<p class="pv76-note">Zatím se žádná lekce neodučila (nebo k ní není reflexe). Jakmile zapíšete reflexi hodiny, přehled ukáže skutečný stav.</p>';
    }
    return $html;
}

function pv76_suggestions_html(array $a): string
{
    $html = '<section class="c70-card c70-wide pv76-suggest" aria-labelledby="pv76-s"><h2 id="pv76-s">Jak dohnat</h2>';
    if ($a['suggestions'] === []) {
        return $html . '<p class="c70-muted">Není co dohánět – plán i prognóza sedí. Návrhy (sloučení, zkrácení, dobrovolná příprava) se ukážou, až bude třída za plánem nebo se lekce nevejdou do kalendáře.</p></section>';
    }
    $html .= '<p class="c70-muted">Jen návrhy k rozhodnutí – nic se nezmění, dokud je neprovedete (výjimka v Kalendáři, úprava hodiny).</p><ul class="pv76-list">';
    foreach ($a['suggestions'] as $s) {
        $html .= '<li><span class="c70-chip">' . e($s['kind']) . '</span> <strong>' . e($s['label']) . '</strong><br>' . e($s['detail'])
            . ($s['saves'] > 0 ? ' <span class="c70-muted">' . e('ušetří ≈ ' . $s['saves'] . ' min') . '</span>' : '') . '</li>';
    }
    return $html . '</ul></section>';
}

function pv76_table_html(array $a, bool $print = false): string
{
    $html = '<div class="' . ($print ? '' : 'pv76-scroll') . '"><table class="c70-table pv76-table"><caption class="' . ($print ? '' : 'c70-sr') . '">Lekce 1–28: plán, kalendář a stav</caption>'
        . '<thead><tr><th scope="col">Lekce</th><th scope="col">Plán</th><th scope="col">Kalendář</th><th scope="col">Stav</th></tr></thead><tbody>';
    foreach ($a['rows'] as $r) {
        $tone = PV76_STATUSES[$r['status']] ?? '';
        $shift = $r['shift_days'] !== null && $r['shift_days'] > 0 ? ' (+' . $r['shift_days'] . ' dní)' : '';
        $html .= '<tr><th scope="row">' . e($r['title']) . '</th><td>' . e(pv76_date($r['plan'])) . '</td><td>' . e(pv76_date($r['calendar']) . $shift) . '</td>'
            . '<td><span class="c70-chip' . ($tone !== '' ? ' c70-chip-' . $tone : '') . '">' . e($r['status']) . '</span>'
            . ($r['reflected_at'] !== '' ? ' <span class="c70-muted">' . e('reflexe ' . pv76_date($r['reflected_at'])) . '</span>' : '') . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}

/** GET ?tisk=1 – samostatná stránka k tisku (bez jmen žáků). */
function pv76_print_page(): never
{
    $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    $allowed = in_array($class, PV76_CLASSES, true) && (!function_exists('teacher59_can_class') || teacher59_can_class($class));
    $head = '<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    if (!$allowed) {
        http_response_code(404);
        echo $head . '<title>Plán vs. skutečnost</title></head><body><main class="pr75"><p>Třídu se nepodařilo najít – vyberte ji v záložce Plán vs. skutečnost.</p><p><a href="teacher.php?tab=plan76">Zpět do cockpitu</a></p></main></body></html>';
        exit;
    }
    $a = pv76_class($class);
    $href = static fn(string $p): string => function_exists('teacher68_css_href') ? teacher68_css_href($p) : $p;
    echo $head . '<title>Plán vs. skutečnost – ' . e(strtoupper(substr($class, 6, 1)) . '.' . strtoupper(substr($class, 7))) . '</title><link rel="stylesheet" href="' . e($href('assets/print-v75.css')) . '"><link rel="stylesheet" href="' . e($href('assets/print-v76.css')) . '">'
        . '<script src="' . e($href('assets/print-v75.js')) . '" defer></script></head><body><main class="pr75">'
        . '<p class="pr75-noprint"><button type="button" data-pr75-print>Vytisknout nebo uložit jako PDF</button> <a href="' . e('teacher.php' . pv76_tab_url($class)) . '">Zpět do cockpitu</a></p>'
        . '<h1>Plán vs. skutečnost · ' . e(strtoupper(substr($class, 6, 1)) . '.' . strtoupper(substr($class, 7))) . '</h1><p class="pr75-meta">Stav k ' . e(date('j. n. Y', (int)strtotime($a['today']))) . ' · bez jmen žáků</p>';
    $f = $a['forecast'];
    echo '<p><strong>Zpoždění vůči plánu:</strong> ' . e($a['delay'] > 0 ? pv76_num($a['delay']) . ' lekce' : 'žádné') . ' · <strong>Prognóza:</strong> '
        . e('stihnete ' . $f['fits'] . ' z ' . PV76_LESSONS . ($f['missing'] > 0 ? ', chybí ' . $f['missing'] . ' h' : ' + rezerva ' . $f['reserve_left'] . ' h')) . '</p>';
    if ($a['warnings'] !== []) {
        echo '<ul>';
        foreach ($a['warnings'] as $w) echo '<li>' . e($w) . '</li>';
        echo '</ul>';
    }
    if ($a['suggestions'] !== []) {
        echo '<h2>Návrhy dohnání</h2><ul>';
        foreach ($a['suggestions'] as $s) echo '<li><strong>' . e($s['label']) . '</strong> – ' . e($s['detail']) . '</li>';
        echo '</ul>';
    }
    echo '<h2>Lekce</h2>' . pv76_table_html($a, true) . '</main></body></html>';
    exit;
}
