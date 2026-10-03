<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · stavební kameny profilu (jednotný vizuální jazyk všech záložek).
 *
 * Karta s nadpisem, „hlavní úkol“ záložky, dlaždice přehledu, čísla, ukazatele postupu a prázdný stav.
 * Čisté funkce vracející HTML řetězce (vše přes e()); styl v assets/profile-v60.css (.p60-*).
 * Používají je profile_v60_views.php, arena_v60_challenge_views.php, student_social_views.php a feedback_v60_views.php.
 */

const P60_TONES = ['', 'teal', 'yellow', 'orange'];

function profile60_tone(string $tone): string
{
    return in_array($tone, P60_TONES, true) && $tone !== '' ? ' is-' . $tone : '';
}

/** Začátek karty: volitelný „eyebrow“, nadpis h2 a odkaz vpravo. Zavírá se profile60_panel_close(). */
function profile60_panel_open(string $title, string $eyebrow = '', string $actionText = '', string $actionHref = '', string $id = '', string $class = ''): string
{
    $head = '<header class="p60-card-head"><div>' . ($eyebrow !== '' ? '<p class="p60-eyebrow">' . e($eyebrow) . '</p>' : '') . '<h2>' . e($title) . '</h2></div>'
        . ($actionHref !== '' ? '<a class="p60-link" href="' . e($actionHref) . '">' . e($actionText) . '</a>' : '') . '</header>';
    return '<section class="p60-card' . ($class !== '' ? ' ' . e($class) : '') . '"' . ($id !== '' ? ' id="' . e($id) . '"' : '') . '>' . $head;
}

function profile60_panel_close(): string
{
    return '</section>';
}

/**
 * „Hlavní úkol“ záložky – první věc, kterou žák vidí. $cta* prázdné = jen sdělení.
 * $live = true přidá role="status" (obsah se mění po akci).
 */
function profile60_task(string $eyebrow, string $title, string $text, string $ctaText = '', string $ctaHref = '', string $tone = 'teal'): string
{
    return '<section class="p60-task' . profile60_tone($tone) . '"><div class="p60-task-text"><p class="p60-eyebrow">' . e($eyebrow) . '</p><h2>' . e($title) . '</h2>'
        . ($text !== '' ? '<p>' . e($text) . '</p>' : '') . '</div>'
        . ($ctaHref !== '' ? '<a class="btn primary p60-task-cta" href="' . e($ctaHref) . '">' . e($ctaText) . '</a>' : '') . '</section>';
}

/** Jedno číslo se jmenovkou. */
function profile60_stat(string $value, string $label, string $tone = ''): string
{
    return '<div class="p60-stat' . profile60_tone($tone) . '"><strong>' . e($value) . '</strong><span>' . e($label) . '</span></div>';
}

/** @param list<array{0:string,1:string,2?:string}> $items [hodnota, popisek, tón] */
function profile60_stats(array $items): string
{
    $html = '';
    foreach ($items as $i) { $html .= profile60_stat((string)$i[0], (string)$i[1], (string)($i[2] ?? '')); }
    return '<div class="p60-stats">' . $html . '</div>';
}

/** Ukazatel postupu (progressbar). $valueText = text vpravo („3 / 8“, „62 %“). */
function profile60_meter(string $label, float $value, float $max, string $valueText, string $tone = ''): string
{
    $percent = $max > 0 ? max(0, min(100, (int)round($value / $max * 100))) : 0;
    return '<div class="p60-meter' . profile60_tone($tone) . '"><div class="p60-meter-row"><span>' . e($label) . '</span><b>' . e($valueText) . '</b></div>'
        . '<span class="p60-meter-bar" role="progressbar" aria-label="' . e($label) . '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . $percent . '"><i style="width:' . $percent . '%"></i></span></div>';
}

/** @param list<array{label:string,value:float,max:float,text:string}> $rows */
function profile60_meter_list(array $rows): string
{
    $html = '';
    foreach ($rows as $r) { $html .= '<li>' . profile60_meter($r['label'], $r['value'], $r['max'], $r['text']) . '</li>'; }
    return '<ul class="p60-meters">' . $html . '</ul>';
}

/** Prázdný stav: přátelská věta a (volitelně) další krok jako odkaz. */
function profile60_empty(string $text, string $linkText = '', string $href = ''): string
{
    return '<div class="p60-empty-state"><p>' . e($text) . '</p>' . ($href !== '' ? '<a class="p60-link" href="' . e($href) . '">' . e($linkText) . '</a>' : '') . '</div>';
}

/** Dlaždice přehledu – celá je odkaz na záložku. */
function profile60_tile(string $href, string $label, string $value, string $hint, string $tone = ''): string
{
    return '<a class="p60-tile' . profile60_tone($tone) . '" href="' . e($href) . '"><span class="p60-tile-label">' . e($label) . '</span>'
        . '<strong>' . e($value) . '</strong><span class="p60-tile-hint">' . e($hint) . '</span></a>';
}

/** Štítky (dovednosti, zájmy). */
function profile60_tags(array $items): string
{
    $html = '';
    foreach ($items as $item) { $html .= '<li>' . e((string)$item) . '</li>'; }
    return $html === '' ? '' : '<ul class="p60-tags">' . $html . '</ul>';
}
