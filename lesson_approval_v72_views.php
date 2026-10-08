<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · záložka „Schvalování lekcí“ (modul `schvalovani`, sekce Výuka). Cockpit je vždy česky.
 * Přehled lekcí s návrhem (stav, úplnost, datum výuky, upozornění T-14) a náhled celého návrhu s formuláři
 * lc72_approve / lc72_return (CSRF, třída v rozsahu, oprávnění content.manage – asistent formuláře nevidí).
 * GET jen ?class= (obecná kontrola rozsahu teacher59) a ?lesson= (číslo 1–28) – žádný nový GET parametr registru.
 * Každý výstup přes e(); data žáků se tu nezobrazují.
 */

require_once __DIR__ . '/lesson_approval_v72.php';
require_once __DIR__ . '/lesson_glossary_v72.php';
if (is_file(__DIR__ . '/cockpit_v70.php')) require_once __DIR__ . '/cockpit_v70.php';

function lc72_render_tab(array $modules, string $classId, string $csrf): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : [];
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    $lesson = is_string($_GET['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_GET['lesson']) === 1 ? (int)$_GET['lesson'] : 0;
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · obsah lekcí</div><h1>Schvalování lekcí</h1>'
        . '<p>Návrh obsahu vlny 1 (lekce ' . LC72_WAVE1[0] . '–' . LC72_WAVE1[1] . '). Žáci uvidí cíl, exit ticket a domácí přípravu až po schválení – do té doby mají původní lekci.</p></div></section>';
    if ($class === '') {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Schvalování se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    try {
        $rows = lc72_overview($class);
    } catch (Throwable $e) {
        error_log('EDUCANET v72 schvalování: ' . get_class($e));
        echo '<section class="c70-empty" role="status"><h2>Přehled se teď nepodařilo sestavit.</h2><p>Zkuste stránku obnovit.</p></section>';
        return;
    }
    echo lc72_picker_html($classes, $class, $modules);
    $count = static fn(string $st): int => count(array_filter($rows, static fn(array $r): bool => $r['status']['status'] === $st));
    $t14 = count(array_filter($rows, static fn(array $r): bool => $r['t14']));
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Lekcí s návrhem', (string)count($rows), 'vlna 1 obsahové stopy', ''],
        ['Schváleno', (string)$count('schvaleno'), 'žáci vidí nový obsah', $count('schvaleno') === count($rows) && $rows !== [] ? 'ok' : ''],
        ['Čeká na rozhodnutí', (string)($count('navrh') + $count('zmeneno')), 'návrh nebo změna po schválení', ''],
        ['Učí se do 14 dní', (string)$t14, 'a nejsou schválené (T-14)', $t14 > 0 ? 'warn' : 'ok'],
    ], 'Souhrn schvalování');
    echo lc72_table_html($class, $rows, $lesson);
    if ($lesson > 0) echo lc72_preview_html($class, $lesson, $csrf);
    echo '<p class="c70-muted">Čeká na rozhodnutí školy (výchozí nastavení): vazba na ŠVP „' . e(LC72_SVP_DEFAULT) . '“; domácí příprava jen volitelná, nejvýš '
        . LC72_HOMEWORK_MAX_MIN . ' min týdně na lekci; exit ticket je čistě formativní a nevstupuje do hodnocení.</p>';
}

/** Přepínač třídy (jen třídy v rozsahu). */
function lc72_picker_html(array $classes, string $class, array $modules): string
{
    $html = '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) {
        $current = $cid === $class ? ' aria-current="page"' : '';
        $html .= '<li><a href="' . e('?tab=schvalovani&class=' . rawurlencode($cid)) . '"' . $current . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    }
    return $html . '</ul></nav>';
}

function lc72_status_chip(array $status): string
{
    $tone = ['schvaleno' => ' c70-chip-ok', 'vraceno' => ' c70-chip-bad', 'zmeneno' => ' c70-chip-warn', 'navrh' => ' c70-chip-warn'][$status['status']] ?? '';
    return '<span class="c70-chip' . $tone . '">' . e((string)$status['label']) . '</span>';
}

function lc72_table_html(string $class, array $rows, int $selected): string
{
    if ($rows === []) return '<section class="c70-empty" role="status"><h2>Třída zatím nemá žádný návrh obsahu.</h2><p>Lekce používají původní obsah.</p></section>';
    $html = '<section class="c70-card c70-wide" aria-labelledby="lc72-list"><h2 id="lc72-list">Lekce s návrhem obsahu</h2>'
        . '<table class="c70-table"><caption class="c70-sr">Lekce, jejich stav a datum výuky</caption><thead><tr><th scope="col">Lekce</th><th scope="col">Výuka</th>'
        . '<th scope="col">Stav</th><th scope="col">Úplnost</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $n = (int)$r['number'];
        $url = '?tab=schvalovani&class=' . rawurlencode($class) . '&lesson=' . $n . '#lc72-preview';
        $when = $r['date'] === '' ? '—' : date('j. n. Y', (int)strtotime($r['date']));
        if ($r['days'] !== null && $r['days'] >= 0) $when .= ' (za ' . (int)$r['days'] . ' d)';
        $html .= '<tr' . ($r['t14'] ? ' class="is-behind"' : '') . '><th scope="row"><a href="' . e($url) . '"' . ($n === $selected ? ' aria-current="true"' : '') . '>' . e((string)$r['title']) . '</a></th>'
            . '<td data-label="Výuka">' . e($when) . ($r['t14'] ? ' <span class="c70-chip c70-chip-warn">schvalte do výuky</span>' : '') . '</td>'
            . '<td data-label="Stav">' . lc72_status_chip($r['status']) . '</td>'
            . '<td data-label="Úplnost">' . (int)$r['completeness']['score'] . '/' . (int)$r['completeness']['total'] . '</td></tr>';
    }
    return $html . '</tbody></table></section>';
}

/** Náhled celého návrhu lekce + formuláře rozhodnutí. */
function lc72_preview_html(string $class, int $n, string $csrf): string
{
    $ov = lc72_overlay($class, $n);
    if ($ov === []) return '<section class="c70-empty" id="lc72-preview" role="status"><h2>Lekce ' . $n . ' nemá návrh obsahu.</h2><p>Používá původní obsah.</p></section>';
    $model = lm71_lesson($class, $n);
    $status = lc72_status($class, $n);
    $html = '<section class="c70-card c70-wide c72-preview" id="lc72-preview" aria-labelledby="lc72-prev-h"><h2 id="lc72-prev-h">Náhled návrhu · ' . e((string)$model['title']) . '</h2>'
        . '<p>' . lc72_status_chip($status) . ' <span class="c70-chip">úplnost ' . (int)$model['completeness']['score'] . '/' . (int)$model['completeness']['total'] . '</span>'
        . ' <span class="c70-chip">otisk ' . e($status['hash']) . '</span></p>';
    if ($status['at'] !== '') $html .= '<p class="c70-muted">Poslední rozhodnutí: ' . e(lc72_status_label((string)(lc72_records()[$class][(string)$n]['status'] ?? ''))) . ' · ' . e(date('j. n. Y H:i', (int)strtotime($status['at']))) . ' · ' . e($status['by']) . '</p>';
    if ($status['note'] !== '') $html .= '<p class="c70-note">Důvod vrácení: ' . e($status['note']) . '</p>';
    $html .= lc72_decision_html($class, $n, $status, $csrf);
    $html .= '<h3>Cíl pro žáka</h3><p>' . e((string)$model['goal']['student']) . '</p>' . lc72_ul($model['goal']['success_criteria']);
    $html .= '<h3>Plán hodiny (90 min)</h3><ol class="c71-timeline" aria-label="Plán hodiny">';
    foreach ($model['timeline'] as $s) {
        $who = array_filter([(string)$s['teacher'], (string)$s['student'] !== '' ? 'Žáci: ' . (string)$s['student'] : '', (string)$s['form'] !== '' ? 'Forma: ' . (string)$s['form'] : '']);
        $html .= '<li><span class="c71-time">' . (int)$s['from'] . '–' . (int)$s['to'] . ' min</span><div><strong>' . e((string)$s['phase']) . '</strong><span>' . e(implode(' · ', $who)) . '</span></div></li>';
    }
    $html .= '</ol><h3>Úkoly</h3><ol class="c71-list">';
    foreach ((array)($ov['tasks'] ?? []) as $t) {
        if (!is_array($t)) continue;
        $html .= '<li>' . e((string)($t['text'] ?? '')) . ' <span class="c70-muted">(' . e((string)($t['time'] ?? '')) . ')</span><br><span class="c70-muted">Výstup: ' . e((string)($t['output'] ?? '')) . '</span>';
        foreach ((array)($t['sim'] ?? []) as $sim) if (is_array($sim)) $html .= '<br><span class="c70-muted">Simulátor: <code>' . e((string)($sim['cmd'] ?? '')) . '</code> → obsahuje „' . e((string)($sim['expect'] ?? '')) . '“</span>';
        $html .= '</li>';
    }
    $html .= '</ol>' . lc72_section_html('Diferenciace', [
        'Podpora: ' . (string)($model['differentiation']['support'] ?? ''), 'Standard: ' . (string)($model['differentiation']['standard'] ?? ''), 'Výzva: ' . (string)($model['differentiation']['challenge'] ?? '')]);
    $html .= lc72_section_html('Formativní kontrola', (array)($model['assessment']['formative'] ?? []));
    $html .= lc72_rubric_html((array)($model['assessment']['rubric'] ?? []));
    $html .= lc72_exit_html($class, $ov);
    $html .= lc72_section_html('Domácí příprava (volitelná, nejvýš ' . LC72_HOMEWORK_MAX_MIN . ' min)', array_map(static fn($h): string => is_array($h) ? (string)($h['text'] ?? '') . ' (' . (int)($h['minutes'] ?? 0) . ' min)' : (string)$h, (array)($ov['homework'] ?? [])));
    $html .= lc72_section_html('Bezpečnost a licence', $model['safety']) . lc72_section_html('Poznámky pro učitele', $model['teacher_notes']) . lc72_section_html('Zástup a plán B', $model['substitution']);
    $terms = lg72_for_lesson((array)($ov['glossary'] ?? []));
    if ($terms !== []) $html .= lc72_section_html('Glosář', array_map(static fn(array $t): string => $t['term'] . ' = ' . $t['cs'] . ' – ' . $t['explain'], $terms));
    return $html . '<p class="c70-muted">Vazba na ŠVP: ' . e(LC72_SVP_DEFAULT) . ' (čeká na dokument školy).</p></section>';
}

function lc72_ul(array $items): string
{
    $items = array_values(array_filter(array_map('strval', $items), static fn(string $t): bool => trim($t) !== ''));
    return $items === [] ? '<p class="c70-muted">—</p>' : '<ul class="c71-list">' . implode('', array_map(static fn(string $t): string => '<li>' . e($t) . '</li>', $items)) . '</ul>';
}

function lc72_section_html(string $title, array $items): string
{
    return '<h3>' . e($title) . '</h3>' . lc72_ul($items);
}

function lc72_rubric_html(array $rubric): string
{
    if ($rubric === []) return '';
    $html = '<h3>Rubrika (1–4)</h3><div class="c72-scroll"><table class="c70-table c72-rubric"><caption class="c70-sr">Rubrika lekce, úrovně 1 až 4</caption><thead><tr><th scope="col">Kritérium</th>'
        . '<th scope="col">1</th><th scope="col">2</th><th scope="col">3</th><th scope="col">4</th></tr></thead><tbody>';
    foreach ($rubric as $row) {
        if (!is_array($row)) continue;
        $html .= '<tr><th scope="row">' . e((string)($row['criterion'] ?? '')) . '</th>';
        foreach (array_slice(array_values((array)($row['levels'] ?? [])), 0, 4) as $i => $level) $html .= '<td data-label="Úroveň ' . ($i + 1) . '">' . e((string)$level) . '</td>';
        $html .= '</tr>';
    }
    return $html . '</tbody></table></div>';
}

/** Varianty exit ticketu se správnou odpovědí (jen pro učitele). */
function lc72_exit_html(string $class, array $ov): string
{
    $ticket = lx72_normalize($ov);
    if ($ticket === null) return '';
    $html = '<h3>Exit ticket (' . count($ticket['variants']) . ' varianty, kompetence: ' . e(lx72_competence_label($class, $ticket)) . ')</h3><ol class="c71-list">';
    foreach ($ticket['variants'] as $v) {
        $html .= '<li><strong>' . e($v['question']) . '</strong><ul>';
        foreach ($v['options'] as $i => $o) $html .= '<li>' . ($i === $v['correct'] ? '<strong>✓ ' . e($o) . '</strong> <span class="c70-chip c70-chip-ok">správně</span>' : e($o)) . '</li>';
        $html .= '</ul>' . ($v['explanation'] !== '' ? '<span class="c70-muted">' . e($v['explanation']) . '</span>' : '') . '</li>';
    }
    return $html . '</ol>';
}

/** Formuláře schválit / vrátit (jen s oprávněním content.manage). */
function lc72_decision_html(string $class, int $n, array $status, string $csrf): string
{
    $can = function_exists('teacher_permission') ? teacher_permission('content.manage') : false;
    if (!$can) return '<p class="c70-note" role="status">Máte jen čtení – schválit nebo vrátit lekci může učitel třídy nebo administrátor.</p>';
    $hidden = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="class_id" value="' . e($class) . '">'
        . '<input type="hidden" name="lesson" value="' . $n . '"><input type="hidden" name="hash" value="' . e($status['hash']) . '"><input type="hidden" name="return_tab" value="schvalovani">';
    $html = '<div class="c72-decide">';
    if ($status['status'] !== 'schvaleno') {
        $html .= '<form method="post" action="teacher.php" class="c70-form">' . $hidden . '<input type="hidden" name="action" value="lc72_approve">'
            . '<button class="btn primary" type="submit">Schválit lekci ' . $n . ' pro žáky</button></form>';
    }
    $html .= '<form method="post" action="teacher.php" class="c70-form c72-return">' . $hidden . '<input type="hidden" name="action" value="lc72_return">'
        . '<label for="lc72-note-' . $n . '">Vrátit k úpravě – co upravit (nejvýš ' . LC72_NOTE_MAX . ' znaků)</label>'
        . '<textarea id="lc72-note-' . $n . '" name="note" rows="2" maxlength="' . LC72_NOTE_MAX . '" required></textarea>'
        . '<button class="btn secondary" type="submit">Vrátit k úpravě</button></form></div>';
    return $html;
}
