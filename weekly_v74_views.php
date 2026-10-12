<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · záložka „Týdenní souhrn“ (modul `tyden74`, sekce Dnes). Jen čtení, bez jmen žáků, s tiskem (A4, @media print
 * v assets/cockpit-v74.css, tlačítko přes assets/lesson-v73.js). GET jen ?tyden= (datum) – žádný GET parametr registru kromě
 * ?paleta (JSON pro rychlé hledání, palette_v74.php, politika tyden74|paleta). Každý výstup přes e().
 */

require_once __DIR__ . '/weekly_v74.php';

function wk74_date(string $date): string
{
    return $date === '' ? '—' : date('j. n.', (int)strtotime($date));
}

function wk74_render_tab(array $modules, string $classId): void
{
    $today = date('Y-m-d');
    $week = wk74_week_start(is_string($_GET['tyden'] ?? null) ? (string)$_GET['tyden'] : '', $today);
    $end = date('Y-m-d', (int)strtotime($week . ' +6 days'));
    echo '<section class="teacher-page-head c70-head wk74-head"><div><div class="eyebrow">Dnes · týden</div><h1>Týdenní souhrn ' . e(date('j. n.', (int)strtotime($week)) . ' – ' . date('j. n. Y', (int)strtotime($end))) . '</h1>'
        . '<p>Co se odučilo podle plánu, co čeká na schválení (T-14), kolik žáků potřebuje pomoc a jaké termíny se blíží. Souhrn je bez jmen žáků, takže ho můžete vytisknout.</p></div></section>';
    $prev = date('Y-m-d', (int)strtotime($week . ' -7 days'));
    $next = date('Y-m-d', (int)strtotime($week . ' +7 days'));
    echo '<nav class="wk74-nav wk74-noprint" aria-label="Týden"><a class="btn secondary" href="' . e('?tab=tyden74&tyden=' . $prev) . '">← Předchozí týden</a>'
        . '<a class="btn secondary" href="?tab=tyden74"' . ($week === wk74_week_start('', $today) ? ' aria-current="page"' : '') . '>Tento týden</a>'
        . '<a class="btn secondary" href="' . e('?tab=tyden74&tyden=' . $next) . '">Další týden →</a>'
        . '<button type="button" class="btn primary" data-lp73-print hidden>Vytisknout souhrn</button></nav>';
    try {
        $s = wk74_summary($modules, $week);
    } catch (Throwable $e) {
        error_log('EDUCANET v74 týdenní souhrn: ' . get_class($e));
        echo '<section class="c70-empty" role="status"><h2>Souhrn se teď nepodařilo sestavit.</h2><p>Zkuste stránku obnovit.</p></section>';
        return;
    }
    if ($s['classes'] === []) {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Týdenní souhrn se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    $t = $s['totals'];
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Odučeno / plán', $t['taught'] . ' / ' . $t['past'], $t['planned'] . ' výukových dnů v týdnu · ' . $t['unreflected'] . ' bez reflexe', $t['unreflected'] > 0 ? 'warn' : ''],
        ['Čeká na schválení', (string)$t['pending'], 'výuka do 14 dní (T-14)', $t['late'] > 0 ? 'warn' : ($t['pending'] === 0 ? 'ok' : '')],
        ['Po termínu schválení', (string)$t['late'], 'termín = výuka − 14 dní', $t['late'] > 0 ? 'warn' : 'ok'],
        ['Potřebují pomoc', (string)$t['behind'], $t['idle'] . ' zatím bez aktivity (bez dat)', $t['behind'] > 0 ? 'warn' : ''],
        ['Termíny', (string)($t['overdue'] + $t['soon']), $t['overdue'] . ' úkolů po termínu', $t['overdue'] > 0 ? 'warn' : ''],
    ], 'Souhrn týdne');
    foreach ($s['classes'] as $c) echo wk74_class_html($c, $s['today'], $week);
    echo '<p class="c70-muted">„Odučeno“ = reflexe hodiny se stavem odučeno celé nebo část (záložka Reflexe hodiny; vidíte jen reflexe, které smíte číst). '
        . 'Docházku ani známky souhrn neobsahuje. Žáky jmenovitě najdete v Dnešní hodině v kartě „Komu dnes pomoct“.</p>';
}

function wk74_link(string $tab, string $class, string $label, array $extra = []): string
{
    if (function_exists('c70_tab_visible') && !c70_tab_visible($tab)) return '';
    return '<a class="c70-link wk74-noprint" href="' . e('?' . http_build_query(['tab' => $tab, 'class' => $class] + $extra)) . '">' . e($label) . '</a>';
}

function wk74_class_html(array $c, string $today, string $week): string
{
    $states = ['cele' => 'odučeno celé', 'cast' => 'odučena část', 'neoduceno' => 'neodučeno'];
    $html = '<section class="c70-card c70-wide wk74-class" aria-labelledby="wk74-' . e($c['class']) . '"><h2 id="wk74-' . e($c['class']) . '">' . e($c['name']) . '</h2><div class="wk74-cols">';
    $html .= '<div><h3>Odučeno vs. plán</h3>';
    if ($c['days'] === []) {
        $html .= '<p class="c70-muted">V tomto týdnu třída podle kalendáře nemá výuku.</p>';
    } else {
        $html .= '<ul class="wk74-list">';
        foreach ($c['days'] as $d) {
            $status = !$d['past'] ? '<span class="c70-chip">naplánováno</span>'
                : ($d['state'] !== '' ? '<span class="c70-chip ' . ($d['state'] === 'neoduceno' ? 'c70-chip-bad' : 'c70-chip-ok') . '">' . e($states[$d['state']] ?? $d['state']) . '</span>' : '<span class="c70-chip c70-chip-warn">bez reflexe</span>');
            $html .= '<li><strong>' . e(wk74_date($d['date'])) . '</strong> ' . e($d['title']) . ' ' . $status
                . ($d['past'] && $d['state'] === '' && $d['kind'] === 'lesson' ? ' ' . wk74_link('reflexe73', $c['class'], 'Zapsat reflexi', ['lesson' => (string)$d['lesson']]) : '') . '</li>';
        }
        $html .= '</ul>';
    }
    $html .= '</div><div><h3>Čeká na schválení (T-14)</h3>';
    if ($c['pending'] === []) {
        $html .= '<p class="c70-muted">Nic – lekce na příštích 14 dní jsou schválené nebo mají původní obsah.</p>';
    } else {
        $html .= '<ul class="wk74-list">';
        foreach ($c['pending'] as $p) {
            $html .= '<li' . ($p['late'] ? ' class="is-late"' : '') . '><strong>L' . (int)$p['lesson'] . '</strong> ' . e($p['kind'] === 'lesson' ? 'obsah' : 'ŠVP') . ' · výuka ' . e(wk74_date($p['date']))
                . ' · <span class="c70-chip ' . ($p['late'] ? 'c70-chip-bad' : 'c70-chip-warn') . '">' . e(($p['late'] ? 'termín schválení uplynul ' : 'schválit do ') . wk74_date($p['deadline'])) . '</span></li>';
        }
        $html .= '</ul><p>' . wk74_link('hromadne74', $c['class'], 'Schválit hromadně →', ['rozsah' => 'trida', 'f' => '1', 'co_lekce' => '1', 'co_svp' => '1']) . '</p>';
    }
    $html .= '</div><div><h3>Žáci</h3><p>' . e($c['students'] . ' v seznamu · ' . $c['behind'] . ' potřebuje pomoc · ' . $c['idle'] . ' zatím bez aktivity') . '</p>'
        . ($c['help'] > 0 ? '<p>' . e('„Komu dnes pomoct“ vybírá ' . $c['help'] . ' žáků.') . ' ' . wk74_link('hodina', $c['class'], 'Otevřít Dnešní hodinu →') . '</p>' : '<p class="c70-muted">Výrazný signál nemá nikdo.</p>');
    $tasks = $c['deadlines']['tasks'];
    $html .= '<h3>Termíny</h3><p>' . e($tasks['active'] . ' aktivních úkolů · ' . $tasks['overdue'] . ' po termínu · ' . $tasks['soon'] . ' do 3 dní') . '</p>';
    if ($tasks['top'] !== []) {
        $html .= '<ul class="wk74-list">';
        foreach (array_slice($tasks['top'], 0, 3) as $tk) $html .= '<li>' . e($tk['title'] . ' (' . $tk['count'] . '×' . ($tk['due'] !== '' ? ', termín ' . wk74_date($tk['due']) : '') . ')') . '</li>';
        $html .= '</ul>';
    }
    $fu = $c['deadlines']['followups'];
    if ($fu !== []) {
        $late = count(array_filter($fu, static fn(array $f): bool => $f['late']));
        $html .= '<p>' . e('Follow-upy s termínem do konce týdne: ' . count($fu) . ($late > 0 ? ' (' . $late . ' po termínu)' : '')) . ' ' . wk74_link('attention', $c['class'], 'Co řešit dnes →') . '</p>';
    }
    return $html . '</div></div></section>';
}
