<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/student/progress_v80.php';

/**
 * EDUCANET v80 · záložka cockpitu `pokrok80` (česky, jen čtení): podpora učení žáků třídy – cíle na týden a jejich plnění, odznaky za zlepšení,
 * opakování chyb (dnes / ve frontě), počty nápověd a vyřešená cvičení; nápovědy podle úloh pro celou třídu. Žádné odpovědi žáků, žádné pořadí.
 * Třída jen z rozsahu učitele (?class= hlídá teacher59_guard_get_check, seznam tříd z c70_classes); jména podle volby učitele (tp721_names_hidden).
 */

function lr80_tab_url(string $classId): string
{
    return '?' . http_build_query(['tab' => 'pokrok80', 'class' => $classId]);
}

function lr80_teacher_goal_text(array $g, int $have): string
{
    $label = ['tasks' => 'vyřešené varianty úloh', 'review' => 'zopakované položky', 'widgets' => 'vyřešená cvičení', 'nohint' => 'úlohy bez nápovědy'][(string)$g['k']] ?? 'cíl';
    return $label . ' ' . min($have, (int)$g['n']) . ' z ' . (int)$g['n'] . ($have >= (int)$g['n'] ? ' (splněno)' : '');
}

function lr80_render_teacher_tab(array $modules, string $classId, ?int $now = null): void
{
    $now ??= time();
    $classes = function_exists('c70_classes') ? c70_classes() : array_keys(TB75_CLASS_SETS);
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Třída a žáci · podpora učení</div><h1>Pokrok a podpora žáků</h1>'
        . '<p>Co žáci procvičují sami: osobní cíle na týden, odznaky za zlepšení (bez pořadí), opakování chyb po 1, 3 a 7 dnech, počet použitých nápověd a vyřešená interaktivní cvičení. '
        . 'Odpovědi žáků se neukládají, nápovědy jen jako počet. Stránku Můj pokrok vidí žák a učitelé jeho třídy.</p></div></section>';
    if ($class === '') { echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Přehled se ukáže, jakmile vám administrátor přidělí třídu.', []) : ''; return; }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(lr80_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    $data = lr80_data($class);
    $tbData = tb75_data();
    $students = function_exists('project_students_for_class') ? project_students_for_class($class) : [];
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $rows = [];
    $i = 0;
    foreach ($students as $key => $s) {
        $i++;
        $ref = tb75_student_ref($class, (string)$key);
        $rows[] = ['name' => $hidden ? 'Žák ' . $i : (string)($s['label'] ?? ('Žák ' . $i)), 'row' => pg80_teacher_row($class, $ref, $data, $tbData, $now)];
    }
    $sum = static fn(string $k): int => array_sum(array_map(static fn(array $r): int => (int)$r['row'][$k], $rows));
    $withGoals = count(array_filter($rows, static fn(array $r): bool => $r['row']['goals'] !== []));
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Žáci s cílem na týden', $withGoals . ' z ' . count($rows), 'cíle si žáci volí sami', ''],
        ['K opakování dnes', (string)$sum('due'), 'položek u celé třídy', $sum('due') > 0 ? 'warn' : ''],
        ['Nápovědy celkem', (string)$sum('hints'), 'jen počet, bez obsahu', ''],
        ['Vyřešená cvičení', (string)$sum('widgets'), 'kontrolováno serverem', ''],
    ], 'Souhrn podpory učení');
    echo '<section class="c70-card c70-wide" aria-labelledby="lr80-t-st"><h2 id="lr80-t-st">Žáci</h2>';
    if ($rows === []) { echo '<p class="c70-muted" role="status">Třída zatím nemá žáky.</p></section>'; return; }
    echo '<div class="kp75-scroll" role="region" tabindex="0" aria-labelledby="lr80-t-cap"><table class="kp75-table"><caption id="lr80-t-cap" class="c70-sr">Podpora učení po žácích</caption><thead><tr><th scope="col">Žák</th><th scope="col">Cíle tento týden</th>'
        . '<th scope="col">Odznaky</th><th scope="col">K opakování dnes</th><th scope="col">Ve frontě opakování</th><th scope="col">Nápovědy</th><th scope="col">Cvičení</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $x = $r['row'];
        $goals = $x['goals'] === [] ? '—' : implode('; ', array_map(static fn(array $g, int $have): string => lr80_teacher_goal_text($g, $have), $x['goals'], $x['progress']));
        echo '<tr><th scope="row">' . e($r['name']) . '</th><td data-label="Cíle">' . e($goals) . '</td><td data-label="Odznaky">' . (int)$x['badges'] . ' z ' . count(PG80_BADGES) . '</td><td data-label="K opakování dnes">' . (int)$x['due']
            . '</td><td data-label="Ve frontě">' . (int)$x['queued'] . '</td><td data-label="Nápovědy">' . (int)$x['hints'] . '</td><td data-label="Cvičení">' . (int)$x['widgets'] . '</td></tr>';
    }
    echo '</tbody></table></div></section>';
    $byTask = [];
    foreach ((array)($data['students'] ?? []) as $st) {
        foreach ((array)(lr80_normalize((array)$st)['hint']) as $key => $h) {
            $task = str_starts_with((string)$key, 'rep:') ? substr((string)$key, 4) : (string)$key;
            if (is_array($h) && isset(TB75_TASKS[$task])) $byTask[$task] = ($byTask[$task] ?? 0) + (int)($h['c'] ?? 0);
        }
    }
    arsort($byTask);
    echo '<section class="c70-card c70-wide" aria-labelledby="lr80-t-h"><h2 id="lr80-t-h">Kde žáci potřebují nápovědu</h2>';
    if ($byTask === []) {
        echo '<p class="c70-muted" role="status">Zatím nikdo nápovědu nepoužil. Žáci ji najdou v kroku Řešení každé úlohy s variantami.</p>';
    } else {
        echo '<ul class="c70-list">';
        foreach ($byTask as $task => $n) echo '<li><strong>' . e((string)TB75_TASKS[$task]['title']) . '</strong>: ' . (int)$n . '× nápověda (součet za třídu)</li>';
        echo '</ul>';
    }
    echo '</section>';
}
