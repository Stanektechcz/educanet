<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · záložka „Středa: T-14“ (modul `streda77`, sekce Dnes). Cockpit česky, jen čtení, bez dat žáků.
 * Po třídách: lekce s výukou do 4 týdnů, termín schválení (výuka − 14 dní), stav obsahu a vazby na ŠVP, úplnost přípravy,
 * odkaz na náhled ve Schvalování lekcí a jedním klikem do Hromadného schválení (rozsah T-14 a třída předvybrané).
 * GET jen ?class= (obecná kontrola rozsahu teacher59). Každý výstup přes e().
 */

require_once dirname(__DIR__, 2) . '/src/teacher/t14_v77.php';

const T1477_WEEKDAYS = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'];

function t1477_date(string $date): string
{
    $ts = (int)strtotime($date);
    return T1477_WEEKDAYS[(int)date('w', $ts)] . ' ' . date('j. n.', $ts);
}

function t1477_tone(string $bucket): string
{
    return ['po_terminu' => 'c70-chip-bad', 'tento_tyden' => 'c70-chip-warn', 'pristi_tyden' => '', 'schvaleno' => 'c70-chip-ok'][$bucket] ?? '';
}

function t1477_deadline_text(array $r, string $today): string
{
    $days = t1477_days_between($today, (string)$r['deadline']);
    $when = t1477_date((string)$r['deadline']);
    if ($r['bucket'] === 'schvaleno') return $when;
    if ($days < 0) return $when . ' (před ' . abs($days) . ' d)';
    return $when . ($days === 0 ? ' (dnes)' : ' (za ' . $days . ' d)');
}

function t1477_render_tab(array $modules, string $classId): void
{
    $today = date('Y-m-d');
    $all = t1477_classes();
    $reqClass = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $pick = in_array($reqClass, $all, true) ? [$reqClass] : $all;
    echo '<section class="teacher-page-head c70-head v81-legacy-head"><div><div class="eyebrow">Dnes · plán schvalování</div><h1>Středa: co schválit do 14 dní</h1>'
        . '<p>Pravidlo T-14: obsah lekce má být schválený nejpozději 14 dní před výukou. Tady vidíte lekce, které se učí v příštích 4 týdnech, '
        . 'jejich termín schválení a stav. Žáci do schválení vidí původní lekci. Každou středu stačí projít tuto stránku a kliknout na „Schválit hromadně“.</p></div>'
        . ((function_exists('c70_tab_visible') && c70_tab_visible('hromadne74')) ? '<div class="teacher-hero-actions"><a class="btn primary" href="?tab=hromadne74">Otevřít Hromadné schválení</a></div>' : '') . '</section>'   // v81: jedna primární akce v hlavičce
        . (function_exists('v81_density_html') ? v81_density_html() : '');   // v81: Komfortní / Kompaktní
    if ($all === []) {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Přehled T-14 se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    try {
        $plan = t1477_plan($pick, $today);
    } catch (Throwable $e) {
        error_log('EDUCANET v77 středa T-14: ' . get_class($e));
        echo '<section class="c70-empty" role="status"><h2>Přehled se teď nepodařilo sestavit.</h2><p>Zkuste stránku obnovit.</p></section>';
        return;
    }
    $t = $plan['totals'];
    echo function_exists('c70_kpis_html') ? c70_kpis_html([
        ['Po termínu', (string)$t['po_terminu'], 'výuka do 14 dní, neschváleno', $t['po_terminu'] > 0 ? 'warn' : 'ok'],
        ['Schválit do 7 dní', (string)$t['tento_tyden'], 'termín tento týden', $t['tento_tyden'] > 0 ? 'warn' : ''],
        ['Schválit za 8–14 dní', (string)$t['pristi_tyden'], 'termín příští týden', ''],
        ['Schváleno', (string)$t['schvaleno'], 'lekce do 4 týdnů', 'ok'],
    ], 'Souhrn T-14') : '';
    if (count($all) > 1) {
        echo '<nav class="t1477-filter" aria-label="Třída"><a class="btn secondary" href="?tab=streda77"' . (count($pick) > 1 ? ' aria-current="page"' : '') . '>Všechny třídy</a>';
        foreach ($all as $c) echo '<a class="btn secondary" href="' . e('?tab=streda77&class=' . rawurlencode($c)) . '"' . ($pick === [$c] ? ' aria-current="page"' : '') . '>' . e((string)($modules[$c]['name'] ?? $c)) . '</a>';
        echo '</nav>';
    }
    foreach ($plan['classes'] as $cp) echo t1477_class_html($modules, $cp, $today);
}

function t1477_class_html(array $modules, array $cp, string $today): string
{
    $c = (string)$cp['class'];
    $name = (string)($modules[$c]['name'] ?? $c);
    $id = 't1477-' . preg_replace('/[^a-z0-9_]/', '', $c);
    $late = (int)$cp['counts']['po_terminu'];
    $html = '<section class="c70-card c70-wide t1477-class' . ($late > 0 ? ' is-warn' : '') . '" aria-labelledby="' . e($id) . '"><div class="t1477-class-head"><h2 id="' . e($id) . '">' . e($name) . '</h2>';
    $html .= $late > 0 ? '<span class="c70-chip c70-chip-bad">' . e($late . ' po termínu') . '</span>' : '<span class="c70-chip c70-chip-ok">bez lekcí po termínu</span>';
    if ($cp['pending'] > 0 && (!function_exists('c70_tab_visible') || c70_tab_visible('hromadne74'))) {
        $html .= '<a class="btn primary" href="' . e(t1477_bulk_url($c)) . '">Schválit hromadně (' . (int)$cp['pending'] . ')<span class="c70-sr"> – ' . e($name) . '</span></a>';
    }
    $html .= '</div>';
    if ($cp['rows'] === []) {
        $html .= '<div class="c70-empty" role="status"><h3>Nic ke schválení</h3><p>V příštích 4 týdnech se v této třídě neučí žádná lekce s návrhem obsahu.</p>'   // v79.1: s akcí
            . (function_exists('lt70_tab_link') ? '<p class="c70-actions">' . lt70_tab_link('schvalovani', (string)$cp['class'], 'Otevřít Schvalování lekcí', [], 'btn secondary') . '</p>' : '') . '</div>';
    } else {
        $html .= '<div class="c72-scroll"><table class="c70-table t1477-table"><caption class="c70-sr">Lekce ' . e($name) . ' s výukou do 4 týdnů a termínem schválení</caption><thead><tr>'
            . '<th scope="col">Lekce</th><th scope="col">Výuka</th><th scope="col">Schválit do</th><th scope="col">Obsah lekce</th><th scope="col">Vazba na ŠVP</th><th scope="col">Příprava</th></tr></thead><tbody>';
        foreach ($cp['rows'] as $r) {
            $label = (preg_match('/^Lekce\s+\d+/u', (string)$r['title']) === 1 ? (string)$r['title'] : 'L' . (int)$r['lesson'] . ' · ' . (string)$r['title']);
            $preview = '?tab=schvalovani&class=' . rawurlencode($c) . '&lesson=' . (int)$r['lesson'] . '#lc72-preview';
            $svpTone = $r['svp']['status'] === 'schvaleno' ? 'c70-chip-ok' : ($r['svp']['status'] === 'chybi' ? '' : 'c70-chip-warn');
            $html .= '<tr' . ($r['bucket'] === 'po_terminu' ? ' class="is-behind"' : '') . '><th scope="row"><a class="c70-link" href="' . e($preview) . '">' . e($label) . '</a></th>'
                . '<td data-label="Výuka">' . e(t1477_date((string)$r['date']) . ' (za ' . (int)$r['days'] . ' d)') . '</td>'
                . '<td data-label="Schválit do"><span class="c70-chip ' . e(t1477_tone((string)$r['bucket'])) . '">' . e(T1477_BUCKETS[$r['bucket']]) . '</span> ' . e(t1477_deadline_text($r, $today)) . '</td>'
                . '<td data-label="Obsah lekce">' . e((string)$r['status']['label']) . '</td>'
                . '<td data-label="Vazba na ŠVP"><span class="c70-chip ' . e($svpTone) . '">' . e((string)$r['svp']['label']) . '</span></td>'
                . '<td data-label="Příprava">' . e($r['score'] . '/' . $r['total'] . ' polí') . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
    }
    if ($cp['days'] !== []) {
        $items = array_map(static fn(array $d): string => '<li>' . e(t1477_date($d['date']) . ' – ' . $d['title']) . '</li>', $cp['days']);
        $html .= '<p class="c70-muted">Dny bez lekce v tomto období (plán dne najdete v Dnešní hodině; jsou jen pro učitele, schválení nepotřebují):</p><ul class="c71-list">' . implode('', $items) . '</ul>';
    }
    return $html . '</section>';
}
