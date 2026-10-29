<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/portfolio_v75.php';

/**
 * EDUCANET v75 · záložka cockpitu `portfolia75` (česky): připnuté práce žáků třídy v rozsahu, reflexe a komentář učitele
 * (POST pf75_comment, projects.manage – asistent jen čte). ?zak=N = pořadí žáka v seznamu (žádný klíč ani jméno v URL).
 * GET ?tisk=1 = samostatná stránka k tisku / PDF (politika portfolia75|tisk: třída povinná a v rozsahu), bez cizích knihoven.
 */

function pf75_tab_url(string $classId, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'portfolia75', 'class' => $classId] + $extra);
}

/** @return list<array{key:string,label:string}> */
function pf75_students(string $classId): array
{
    $out = [];
    foreach (function_exists('project_students_for_class') ? project_students_for_class($classId) : [] as $k => $s) $out[] = ['key' => (string)$k, 'label' => (string)($s['label'] ?? $k)];
    return $out;
}

function pf75_render_teacher_tab(array $modules, string $classId, string $csrf): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : [];
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Třída · portfolia</div><h1>Portfolia žáků</h1>'
        . '<p>Práce, které si žáci sami připnuli (odkazy https na grafiku nebo web, vyřešené úlohy, projekty), s jejich reflexí. Ke každé práci můžete napsat komentář – žák ho uvidí ve svém portfoliu. '
        . 'Portfolio vidí jen žák a učitelé jeho třídy; nesdílí se odkazem a nevstupuje do hodnocení.</p></div></section>';
    if ($class === '') { echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Portfolia se ukážou, jakmile vám administrátor přidělí třídu.', []) : ''; return; }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(pf75_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    $students = pf75_students($class);
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $sel = is_string($_GET['zak'] ?? null) && preg_match('/^\d{1,3}$/', (string)$_GET['zak']) === 1 ? (int)$_GET['zak'] : 0;
    $counts = [];
    foreach ($students as $i => $s) $counts[$i] = pf75_pins($class, $s['key']);
    $withPins = count(array_filter($counts));
    $comments = 0;
    foreach ($counts as $pins) foreach ($pins as $p) $comments += (int)is_array($p['comment'] ?? null);
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Žáci s portfoliem', $withPins . ' z ' . count($students), 'aspoň jedna připnutá práce', ''],
        ['Připnuté práce', (string)array_sum(array_map('count', $counts)), 'nejvýš ' . PF75_MAX . ' na žáka', ''],
        ['S vaším komentářem', (string)$comments, 'komentáře učitelů', ''],
    ], 'Souhrn portfolií');
    echo '<section class="c70-card c70-wide" aria-labelledby="pf75-t-list"><h2 id="pf75-t-list">Žáci</h2>';
    if ($students === []) echo '<p class="c70-muted">Třída zatím nemá žáky v seznamu.</p>';
    elseif ($withPins === 0) echo '<p class="c70-muted" role="status">Zatím nikdo nic nepřipnul. Žáci najdou portfolio v profilu (Moje portfolio) – připnout jde odkaz na práci, vyřešená úloha nebo projekt.</p>';
    echo '<ul class="pf75-students">';
    foreach ($students as $i => $s) {
        $label = function_exists('tp721_student_label') ? tp721_student_label($s['label'], $i + 1, $hidden) : $s['label'];
        echo '<li><a href="' . e(pf75_tab_url($class, ['zak' => (string)($i + 1)])) . '"' . ($sel === $i + 1 ? ' aria-current="true"' : '') . '>' . e($label) . '</a> <span class="c70-muted">' . count($counts[$i]) . ' prací</span></li>';
    }
    echo '</ul></section>';
    if ($sel >= 1 && isset($students[$sel - 1])) echo pf75_detail_html($class, $students[$sel - 1], $sel, $counts[$sel - 1], $csrf, $hidden);
}

function pf75_detail_html(string $class, array $student, int $index, array $pins, string $csrf, bool $hidden): string
{
    $label = function_exists('tp721_student_label') ? tp721_student_label($student['label'], $index, $hidden) : $student['label'];
    $can = function_exists('teacher_permission') && teacher_permission('projects.manage');
    $html = '<section class="c70-card c70-wide" aria-labelledby="pf75-t-d"><h2 id="pf75-t-d">Portfolio: ' . e($label) . '</h2>'
        . '<p><a class="btn secondary" href="' . e(pf75_tab_url($class, ['zak' => (string)$index, 'tisk' => '1'])) . '">Verze k tisku / PDF</a></p>';
    if ($pins === []) return $html . '<p class="c70-muted" role="status">Žák zatím nic nepřipnul.</p></section>';
    $html .= '<ol class="pf75-list">';
    foreach ($pins as $id => $p) {
        $url = (string)$p['kind'] === 'link' ? safe_url((string)$p['url']) : '';
        $html .= '<li class="pf75-pin"><h3><span class="c70-chip">' . e(PF75_KINDS[(string)$p['kind']] ?? '') . '</span> '
            . ($url !== '' ? '<a href="' . e($url) . '" rel="noopener noreferrer" target="_blank">' . e((string)$p['title']) . '</a> <span class="pf75-url">' . e($url) . '</span>' : e((string)$p['title'])) . '</h3>'
            . '<p>' . ((string)$p['reflection'] !== '' ? '<strong>Reflexe žáka:</strong> ' . e((string)$p['reflection']) : '<span class="c70-muted">Bez reflexe.</span>') . '</p>';
        $comment = is_array($p['comment'] ?? null) ? $p['comment'] : null;
        if ($comment !== null) $html .= '<p class="pf75-comment"><strong>Komentář:</strong> ' . e((string)$comment['text']) . ' <span class="c70-muted">(' . e((string)$comment['by']) . ')</span></p>';
        if ($can) {
            $html .= '<form method="post" class="kp75-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="pf75_comment">'
                . '<input type="hidden" name="class_id" value="' . e($class) . '"><input type="hidden" name="zak" value="' . $index . '"><input type="hidden" name="pin" value="' . e((string)$id) . '"><input type="hidden" name="return_tab" value="portfolia75">'
                . '<label for="pf75-c-' . e((string)$id) . '">' . ($comment !== null ? 'Upravit komentář (prázdné = smazat)' : 'Komentář pro žáka') . '</label>'
                . '<textarea id="pf75-c-' . e((string)$id) . '" name="comment" rows="2" maxlength="' . PF75_TEXT_MAX . '">' . e((string)($comment['text'] ?? '')) . '</textarea>'
                . '<button class="btn secondary" type="submit">Uložit komentář</button></form>';
        }
        $html .= '</li>';
    }
    return $html . '</ol></section>';
}

/** GET portfolia75|tisk – samostatná stránka k tisku (A4), bez navigace cockpitu. */
function pf75_print_page(): never
{
    $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $index = is_string($_GET['zak'] ?? null) && preg_match('/^\d{1,3}$/', (string)$_GET['zak']) === 1 ? (int)$_GET['zak'] : 0;
    $students = (function_exists('teacher59_can_class') && !teacher59_can_class($class)) ? [] : pf75_students($class);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    if ($index < 1 || !isset($students[$index - 1])) { http_response_code(404); echo '<!doctype html><html lang="cs"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Portfolio</title><p>Žák nebyl nalezen – vyberte ho v seznamu žáků v záložce Portfolia žáků.</p><p><a href="teacher.php?tab=portfolia75">Zpět do cockpitu</a></p>'; exit; }
    $s = $students[$index - 1];
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $label = function_exists('tp721_student_label') ? tp721_student_label($s['label'], $index, $hidden) : $s['label'];
    $href = static fn(string $p): string => function_exists('teacher68_css_href') ? teacher68_css_href($p) : $p;
    echo '<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Portfolio – ' . e($label) . '</title>'
        . '<link rel="stylesheet" href="' . e($href('assets/print-v75.css')) . '"><script src="' . e($href('assets/print-v75.js')) . '" defer></script></head><body><main class="pr75">'
        . '<p class="pr75-noprint"><button type="button" data-pr75-print>Vytisknout nebo uložit jako PDF</button> <a href="' . e(pf75_tab_url($class, ['zak' => (string)$index])) . '">Zpět do cockpitu</a></p>'
        . '<h1>Portfolio: ' . e($label) . '</h1><p class="pr75-meta">Vytištěno ' . e(date('j. n. Y')) . ' · jen pro žáka a jeho učitele</p>';
    $pins = pf75_pins($class, $s['key']);
    if ($pins === []) echo '<p>Žák zatím nic nepřipnul.</p>';
    foreach ($pins as $p) {
        echo '<section class="pr75-item"><h2>' . e((string)$p['title']) . '</h2><p class="pr75-meta">' . e(PF75_KINDS[(string)$p['kind']] ?? '') . ((string)$p['url'] !== '' ? ' · ' . e((string)$p['url']) : '') . '</p>'
            . ((string)$p['reflection'] !== '' ? '<p><strong>Reflexe:</strong> ' . e((string)$p['reflection']) . '</p>' : '')
            . (is_array($p['comment'] ?? null) ? '<p><strong>Komentář učitele:</strong> ' . e((string)$p['comment']['text']) . '</p>' : '') . '</section>';
    }
    echo '</main></body></html>';
    exit;
}
