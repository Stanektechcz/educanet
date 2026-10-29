<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · záložka „Plnění ŠVP“ (modul `svp76`, sekce Výuka): přehled po třídách a pololetích, tisk (GET ?tisk=1) a CSV (GET ?csv=1)
 * – politiky svp76|tisk a svp76|csv (třída povinná a v rozsahu; handler ji ověří znovu). Bez jmen žáků; česky; každý výstup přes e().
 * GET ?pololeti=1|2 jen přepíná pohled (není parametr registru). Logika v svp_export_v76.php.
 */

require_once dirname(__DIR__, 2) . '/src/assessment/svp_export_v76.php';

function sx76_tab_url(string $class, int $half, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'svp76', 'class' => $class, 'pololeti' => (string)$half] + $extra);
}

function sx76_half_param(): int
{
    $p = is_string($_GET['pololeti'] ?? null) ? (string)$_GET['pololeti'] : '';
    return in_array($p, ['1', '2'], true) ? (int)$p : sx76_current_half(date('Y-m-d'));
}

function sx76_class_label(string $classId): string
{
    return strtoupper(substr($classId, 6, 1)) . '.' . strtoupper(substr($classId, 7));
}

function sx76_render_tab(array $modules, string $classId): void
{
    $allowed = function_exists('c70_classes') ? c70_classes() : SX76_CLASSES;
    $classes = array_values(array_filter(SX76_CLASSES, static fn(string $c): bool => in_array($c, $allowed, true) && isset($modules[$c])));
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    $half = sx76_half_param();
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · ŠVP</div><h1>Plnění ŠVP</h1>'
        . '<p>Které výsledky vzdělávání ŠVP „Inteligentní digitální systémy 2026“ třída v pololetí opravdu probrala: výsledek je naplněný, když se odučila lekce '
        . 'se schválenou vazbou na ŠVP. Přehled je bez jmen žáků – můžete ho vytisknout nebo stáhnout jako CSV pro třídního učitele a vedení.</p></div></section>';
    if ($class === '') {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Plnění ŠVP se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(sx76_tab_url($cid, $half)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav><nav class="pv76-actions" aria-label="Pololetí">';
    foreach ([1 => '1. pololetí (září–leden)', 2 => '2. pololetí (únor–červen)'] as $h => $label) {
        echo '<a class="btn ' . ($h === $half ? 'primary' : 'secondary') . '" href="' . e(sx76_tab_url($class, $h)) . '"' . ($h === $half ? ' aria-current="page"' : '') . '>' . e($label) . '</a> ';
    }
    echo '</nav>';
    try {
        $a = sx76_class($class, $half);
    } catch (Throwable $e) {
        error_log('EDUCANET v76 plnění ŠVP: ' . get_class($e));
        echo '<section class="c70-empty" role="status"><h2>Přehled se teď nepodařilo sestavit.</h2><p>Zkuste stránku obnovit.</p></section>';
        return;
    }
    $c = $a['count'];
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Naplněno', $c['naplněno'] . ' z ' . count($a['rows']), 'výsledků ŠVP v pololetí (' . $a['expected'] . ' z ročníku třídy)', $c['naplněno'] > 0 ? 'ok' : ''],
        ['Mezery', (string)$c['mezera'], 'výsledky ročníku bez lekce v pololetí', $c['mezera'] > 0 ? 'warn' : 'ok'],
        ['Čeká na schválení vazby', (string)$c['čeká na vazbu'], 'odučeno, ale vazba na ŠVP je jen návrh', $c['čeká na vazbu'] > 0 ? 'warn' : ''],
        ['Lekce pololetí', $a['lessons_taught'] . ' / ' . $a['lessons_total'], 'odučeno podle reflexí · ' . $a['approved'] . ' se schválenou vazbou', ''],
    ], 'Plnění ŠVP');
    if ($a['lessons_total'] === 0) {
        echo '<p class="pv76-note">V tomto pololetí nemá třída podle kalendáře žádnou lekci.</p>';
    } elseif ($a['lessons_taught'] === 0) {
        echo '<p class="pv76-note">Zatím žádná odučená lekce s reflexí – výsledky se začnou plnit, jakmile zapíšete reflexi hodiny (stav „odučeno“) a schválíte vazbu lekce na ŠVP.</p>';
    }
    echo '<p class="pv76-actions"><a class="btn secondary" href="' . e(sx76_tab_url($class, $half, ['tisk' => '1'])) . '" target="_blank" rel="noopener">Verze k tisku (A4)</a> '
        . '<a class="btn secondary" href="' . e(sx76_tab_url($class, $half, ['csv' => '1'])) . '">Stáhnout CSV</a> '
        . (function_exists('c70_tab_visible') && c70_tab_visible('schvalovani') ? '<a class="btn secondary" href="' . e('?' . http_build_query(['tab' => 'schvalovani', 'class' => $class])) . '#svp72">Schválit vazby na ŠVP</a>' : '') . '</p>';
    echo sx76_table_html($a);
    echo '<p class="c70-muted">Zdroj: ' . e(SVP72_SOURCE['title'] . ', ' . SVP72_SOURCE['ref'] . ', ověřeno ' . date('j. n. Y', (int)strtotime(SVP72_SOURCE['verified_at']))) . '. '
        . 'Výsledky jiného ročníku (předstih nebo opakování) jsou označené. Odučení se bere z reflexí hodin, které smíte číst; docházku ani známky přehled neobsahuje.</p>';
}

function sx76_table_html(array $a, bool $print = false): string
{
    if ($a['rows'] === []) return '<p class="c70-muted">Třída zatím nemá žádnou vazbu lekcí na ŠVP.</p>';
    $html = '<div class="' . ($print ? '' : 'pv76-scroll') . '"><table class="c70-table pv76-table"><caption class="' . ($print ? '' : 'c70-sr') . '">Výsledky ŠVP v pololetí a jejich stav</caption>'
        . '<thead><tr><th scope="col">Výsledek vzdělávání</th><th scope="col">Předmět</th><th scope="col">Stav</th><th scope="col">Lekce</th></tr></thead><tbody>';
    foreach ($a['rows'] as $r) {
        $tone = SX76_STATUSES[$r['status']] ?? '';
        $lessons = $r['lessons'] === [] ? '—' : implode(', ', array_map(static fn(int $n): string => 'L' . $n, $r['lessons']));
        $html .= '<tr><th scope="row">' . e($r['text']) . ' <span class="c70-muted">' . e('(' . $r['id'] . ', s. ' . $r['page'] . ')') . '</span></th>'
            . '<td>' . e($r['subject_label'] . ' · ' . $r['year'] . '. r.') . ($r['other_year'] ? ' <span class="c70-chip">jiný ročník</span>' : '') . '</td>'
            . '<td><span class="c70-chip' . ($tone !== '' ? ' c70-chip-' . $tone : '') . '">' . e($r['status']) . '</span></td>'
            . '<td>' . e($lessons) . ($r['taught'] !== [] ? ' <span class="c70-muted">' . e('odučeno: ' . implode(', ', array_map(static fn(int $n): string => 'L' . $n, $r['taught']))) . '</span>' : '') . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}

/** Ověření třídy pro tisk a CSV (politika už prošla; handler ji ověří znovu). */
function sx76_request_class(): string
{
    $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    return in_array($class, SX76_CLASSES, true) && (!function_exists('teacher59_can_class') || teacher59_can_class($class)) ? $class : '';
}

/** GET ?tisk=1 – samostatná stránka k tisku (A4, bez jmen žáků). */
function sx76_print_page(): never
{
    $class = sx76_request_class();
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    $head = '<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    if ($class === '') {
        http_response_code(404);
        echo $head . '<title>Plnění ŠVP</title></head><body><main class="pr75"><p>Třídu se nepodařilo najít – vyberte ji v záložce Plnění ŠVP.</p><p><a href="teacher.php?tab=svp76">Zpět do cockpitu</a></p></main></body></html>';
        exit;
    }
    $a = sx76_class($class, sx76_half_param());
    $href = static fn(string $p): string => function_exists('teacher68_css_href') ? teacher68_css_href($p) : $p;
    $c = $a['count'];
    echo $head . '<title>Plnění ŠVP – ' . e(sx76_class_label($class)) . '</title><link rel="stylesheet" href="' . e($href('assets/print-v75.css')) . '"><link rel="stylesheet" href="' . e($href('assets/print-v76.css')) . '">'
        . '<script src="' . e($href('assets/print-v75.js')) . '" defer></script></head><body><main class="pr75">'
        . '<p class="pr75-noprint"><button type="button" data-pr75-print>Vytisknout nebo uložit jako PDF</button> <a href="' . e('teacher.php' . sx76_tab_url($class, $a['half'])) . '">Zpět do cockpitu</a></p>'
        . '<h1>Plnění ŠVP · ' . e(sx76_class_label($class)) . ' · ' . (int)$a['half'] . '. pololetí</h1>'
        . '<p class="pr75-meta">' . e(SVP72_SOURCE['title'] . ' (' . SVP72_SOURCE['ref'] . ') · období ' . date('j. n. Y', (int)strtotime($a['range'][0])) . ' – ' . date('j. n. Y', (int)strtotime($a['range'][1]))
            . ' · stav k ' . date('j. n. Y', (int)strtotime($a['today'])) . ' · bez jmen žáků') . '</p>'
        . '<p>' . e('Naplněno ' . $c['naplněno'] . ' z ' . count($a['rows']) . ' výsledků · mezery ' . $c['mezera'] . ' · čeká na schválení vazby ' . $c['čeká na vazbu'] . ' · nepotvrzeno ' . $c['nepotvrzeno']
            . ' · naplánováno ' . $c['naplánováno'] . ' · odučeno ' . $a['lessons_taught'] . ' z ' . $a['lessons_total'] . ' lekcí pololetí') . '</p>'
        . sx76_table_html($a, true) . '<p class="pr75-meta">Naplněno = odučená lekce (reflexe hodiny) se schválenou vazbou na výsledek ŠVP.</p></main></body></html>';
    exit;
}

/** GET ?csv=1 – stažení CSV (UTF-8, středník, bez jmen žáků). */
function sx76_csv_download(): never
{
    $class = sx76_request_class();
    if ($class === '') {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Tato třída není ve vašem rozsahu.');
    }
    $a = sx76_class($class, sx76_half_param());
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="plneni-svp-' . preg_replace('/[^a-z0-9]/', '', substr($class, 6)) . '-pololeti' . (int)$a['half'] . '-' . date('Ymd') . '.csv"');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo sx76_csv_body($a);
    exit;
}
