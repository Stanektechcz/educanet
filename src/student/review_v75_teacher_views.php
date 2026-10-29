<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/student/review_v75.php';

/**
 * EDUCANET v75 · záložka cockpitu `opakovani75` (česky): výběr rozsahu lekcí (?od=, ?do=; v79 po krocích ?krok=, review_v79.php), náhled balíčku z chyb třídy,
 * zveřejnění / skrytí (POST op75_publish / op75_close, content.manage) a verze k tisku s klíčem (GET opakovani75|tisk).
 */

function rv75_tab_url(string $classId, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'opakovani75', 'class' => $classId] + $extra);
}

function rv75_render_teacher_tab(array $modules, string $classId, string $csrf): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : [];
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · před písemkou</div><h1>Opakování před písemkou</h1>'
        . '<p>Tři kroky: témata (rozsah lekcí) → náhled balíčku → zveřejnění. Balíček se složí sám: otázky z exit ticketů, ve kterých třída chybovala (aspoň ' . RV75_MIN_ERROR_PCT . ' % špatně), a úlohy s variantami k tématům rozsahu, '
        . 'které třída zatím nezkusila nebo v nich má pod ' . RV75_TASK_WEAK_PCT . ' % správně. Žák dostane vlastní varianty úloh; jména se nikde neukazují.</p></div></section>';
    if ($class === '') { echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Opakování se ukáže, jakmile vám administrátor přidělí třídu.', []) : ''; return; }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(rv75_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    // v79: Témata → Náhled balíčku → Zveřejnit (review_v79.php); akce op75_publish / op75_close beze změny.
    require_once dirname(__DIR__, 2) . '/src/student/review_v79.php';
    rv79_render($modules, $class, $csrf);
}

function rv75_exits_html(array $exits, int $answers, bool $withStats): string
{
    $html = '<h3>Otázky z exit ticketů, ve kterých třída chybovala</h3>';
    if ($exits === []) return $html . '<p class="c70-muted">' . ($answers === 0 ? 'V rozsahu zatím nejsou žádné odpovědi z exit ticketů (exit tickety mají jen schválené lekce).' : 'Žádná otázka nemá aspoň ' . RV75_MIN_ERROR_PCT . ' % chybných odpovědí – třída je v rozsahu v pořádku.') . '</p>';
    $html .= '<ol class="rv75-list">';
    foreach ($exits as $x) {
        $html .= '<li class="rv75-q"><p><span class="c70-chip">L' . (int)$x['lesson'] . '</span> ' . e((string)$x['question']) . ($withStats ? ' <span class="c70-muted">(' . (int)$x['wrong'] . ' z ' . (int)$x['total'] . ' špatně)</span>' : '') . '</p><ol type="A">';
        foreach ((array)$x['options'] as $i => $o) $html .= '<li' . ((int)$i === (int)$x['correct'] ? ' class="rv75-correct"' : '') . '>' . e((string)$o) . ((int)$i === (int)$x['correct'] ? ' <strong>(správně)</strong>' : '') . '</li>';
        $html .= '</ol>' . ((string)$x['explanation'] !== '' ? '<p class="c70-muted">' . e((string)$x['explanation']) . '</p>' : '') . '</li>';
    }
    return $html . '</ol>';
}

/** GET opakovani75|tisk – samostatná stránka k tisku s klíčem (bez jmen). */
function rv75_print_page(): never
{
    $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    [$from, $to] = rv75_range($_GET);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    if (!lm71_class_ok($class) || $from < 1 || (function_exists('teacher59_can_class') && !teacher59_can_class($class))) { http_response_code(404); echo '<!doctype html><html lang="cs"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Opakování</title><p>Rozsah nebo třída nebyly nalezeny – v záložce Opakování před písemkou zadejte rozsah lekcí od–do a teprve pak otevřete verzi k tisku.</p><p><a href="teacher.php?tab=opakovani75">Zpět do cockpitu</a></p>'; exit; }
    $module = is_array($GLOBALS['modules'][$class] ?? null) ? $GLOBALS['modules'][$class] : [];
    $pack = rv75_build($class, $module, $from, max($from, $to));
    $href = static fn(string $p): string => function_exists('teacher68_css_href') ? teacher68_css_href($p) : $p;
    echo '<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Opakování L' . $pack['from'] . '–L' . $pack['to'] . '</title>'
        . '<link rel="stylesheet" href="' . e($href('assets/print-v75.css')) . '"><script src="' . e($href('assets/print-v75.js')) . '" defer></script></head><body><main class="pr75">'
        . '<p class="pr75-noprint"><button type="button" data-pr75-print>Vytisknout nebo uložit jako PDF</button> <a href="' . e(rv75_tab_url($class, ['od' => (string)$pack['from'], 'do' => (string)$pack['to']])) . '">Zpět do cockpitu</a></p>'
        . '<h1>Opakování před písemkou · ' . e((string)($module['name'] ?? $class)) . ' · L' . $pack['from'] . '–L' . $pack['to'] . '</h1><p class="pr75-meta">Verze pro učitele s klíčem · ' . e(date('j. n. Y')) . '</p>'
        . rv75_exits_html($pack['exits'], $pack['answers'], true) . '<h2>Úlohy k procvičení (ukázková varianta s klíčem)</h2>';
    if ($pack['tasks'] === []) echo '<p>Bez úloh s variantami.</p>';
    foreach ($pack['tasks'] as $t) {
        $v = tb75_variant($t['task'], 'ukazka', 1);
        echo '<section class="pr75-item"><h3>' . e(TB75_TASKS[$t['task']]['title']) . '</h3><p>' . e((string)$v['prompt']) . '</p>' . ($v['data'] !== [] ? '<pre>' . e(implode("\n", $v['data'])) . '</pre>' : '') . '<ul>';
        foreach ($v['fields'] as $f) echo '<li>' . e((string)$f['label']) . ': <strong>' . e((string)$f['answer']) . '</strong></li>';
        echo '</ul></section>';
    }
    echo '<p class="pr75-meta">Žáci dostanou každý vlastní variantu úloh (jiná čísla); klíč ke konkrétnímu žákovi je v záložce Úlohy s variantami.</p></main></body></html>';
    exit;
}
