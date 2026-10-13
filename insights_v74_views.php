<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · karta „Komu dnes pomoct“ v Dnešní hodině (jen čtení, česky). Jména podle osobní volby učitele
 * (tp721_names_hidden → „Žák N“ se stejným číslem jako v tabulce žáků níže). Každý žák má viditelné důvody, „Proč?“ se zdrojem
 * dat a navržený krok s odkazem na existující záložku. Žák kartu nikdy nevidí; nic se neukládá ani neexportuje.
 */

require_once __DIR__ . '/insights_v74.php';

/** Karta pro model Dnešní hodiny; $now jen pro audit. */
function in74_card_html(array $m, ?int $now = null): string
{
    try {
        $c = in74_candidates($m, $now);
    } catch (Throwable $e) {
        error_log('EDUCANET v74 komu pomoct: ' . get_class($e));
        return '';
    }
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $helpN = count($c['rows']);   // v78.1: odznak „Komu pomoct“ v navigaci – jen počet a třída (bez jmen), cache v relaci
    if (function_exists('nav781_help_store')) nav781_help_store((string)($m['class'] ?? ''), $helpN);
    $helpLabel = function_exists('nav781_plural') ? 'Komu dnes pomoct: ' . $helpN . ' ' . nav781_plural($helpN, 'žák', 'žáci', 'žáků') : '';
    $html = '<link rel="stylesheet" href="' . e(function_exists('teacher68_css_href') ? teacher68_css_href('assets/cockpit-v74.css') : 'assets/cockpit-v74.css') . '">'
        . '<section class="c70-card c70-wide in74-card" aria-labelledby="in74-h" data-n781-help="' . $helpN . '" data-n781-help-label="' . e($helpLabel) . '"><div class="in74-head"><h2 id="in74-h">Komu dnes pomoct</h2>'
        . '<details class="in74-why-all"><summary>Jak vybíráme?</summary><p>Nejvýš ' . IN74_MAX . ' žáků s nejsilnějšími signály: 2× chybný exit ticket, klesající výsledky ve třech lekcích, '
        . 'chybění v minulé hodině s kódem, 7 a více dní bez aktivity, nízké zvládnutí, úkol po termínu, nedokončená minulá lekce (pod ' . IN74_UNFINISHED_PCT . ' %), '
        . 'chybějící exit ticket schválené lekce a váš otevřený follow-up. Žáci bez jakýchkoli dat se nevybírají – „bez dat“ neznamená „zaostává“.</p>'
        . '<p>Je to jen nápověda pro vás: žák štítek nevidí, nic se neukládá ani nevstupuje do hodnocení.' . ($hidden ? ' Jména jsou skrytá podle vaší volby v Nastavení výuky – číslo žáka odpovídá tabulce žáků níže.' : '') . '</p></details></div>';
    if ($c['rows'] === []) {
        $why = $c['students'] === 0 ? 'Třída zatím nemá žáky v seznamu.'
            : ($c['idle'] === $c['students'] ? 'Žáci zatím nemají v aplikaci žádná data (' . $c['idle'] . ' bez aktivity) – signály se objeví po první práci v lekci.'
                : 'Žádný žák teď nemá výrazný signál.' . ($c['idle'] > 0 ? ' ' . $c['idle'] . ' žáků je zatím bez aktivity (bez dat se nehodnotí).' : ''));
        return $html . '<p class="in74-empty" role="status"><strong>Dnes nikoho nevybíráme.</strong> ' . e($why) . '</p></section>';
    }
    $html .= '<p class="c70-muted">' . e('Vybráno ' . count($c['rows']) . ' z ' . $c['with_reasons'] . ' žáků se signálem' . ($c['idle'] > 0 ? ' · ' . $c['idle'] . ' bez aktivity se nehodnotí' : '') . '.') . '</p><ol class="in74-list">';
    foreach ($c['rows'] as $r) {
        $label = function_exists('tp721_student_label') ? tp721_student_label($r['label'], (int)$r['index'], $hidden) : $r['label'];
        $top = $r['reasons'][0];
        [$url, $linkLabel] = in74_action_link($top['code'], (string)$m['class'], $r['key']);
        $html .= '<li class="in74-item"><div class="in74-who"><strong>' . e($label) . '</strong><span class="in74-chips">';
        foreach ($r['reasons'] as $reason) $html .= '<span class="c70-chip' . ($reason['weight'] >= 3 ? ' c70-chip-warn' : '') . '">' . e($reason['text']) . '</span>';
        $html .= '</span></div><p class="in74-step"><span class="in74-step-label">Navržený krok:</span> ' . e($top['action'])
            . ($url !== '' ? ' <a class="btn secondary small in74-btn" href="' . e($url) . '">' . e($linkLabel) . '<span class="c70-sr"> – ' . e($label) . '</span></a>' : '') . '</p>'
            . '<details class="in74-why"><summary>Proč?<span class="c70-sr"> – ' . e($label) . '</span></summary><ul>';
        foreach ($r['reasons'] as $reason) $html .= '<li><strong>' . e($reason['text']) . '</strong> – ' . e($reason['source']) . ' <em>' . e($reason['action']) . '</em></li>';
        $html .= '</ul></details></li>';
    }
    return $html . '</ol></section>';
}
