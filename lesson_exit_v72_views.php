<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · vykreslení exit ticketu a domácí přípravy.
 *   - žák (lekce v56): lx72_render_student_block – cíl hodiny, exit ticket (formulář bez JS, akce lx72_answer s CSRF)
 *     a volitelná domácí příprava; jen u schválené lekce. UI přes tr() (katalog lang/<en|uk>/ui/lesson_v72.php),
 *     výukový obsah zůstává česky v kontejneru s edu_content_lang_attr().
 *   - učitel (Dnešní hodina v71): lx72_teacher_card_html – souhrn bez jmen (cockpit je vždy česky).
 * Každý výstup přes e(); žádné innerHTML ani data žáka v URL.
 */

require_once __DIR__ . '/lesson_exit_v72.php';

/** Blok pod fází lekce (žák). Nic nevypíše, pokud lekce nemá schválený obsah. */
function lx72_render_student_block(string $classId, int $lessonNo): void
{
    try {
        $ov = lc72_student_overlay($classId, $lessonNo);
    } catch (Throwable $e) {
        error_log('EDUCANET v72 lekce: ' . get_class($e));
        return;
    }
    if ($ov === null) return;
    $studentKey = adaptive_student_key($classId);
    $goal = is_array($ov['goal'] ?? null) ? $ov['goal'] : [];
    $criteria = array_values(array_filter(array_map(static fn($c): string => trim((string)$c), (array)($goal['success_criteria'] ?? [])), static fn(string $c): bool => $c !== ''));
    $ticket = lx72_ticket($classId, $lessonNo);
    $homework = lx72_homework($classId, $lessonNo);
    echo '<link rel="stylesheet" href="' . e(function_exists('asset_url') ? asset_url('assets/lesson-v72.css?v=72') : 'assets/lesson-v72.css?v=72') . '">';
    echo '<section class="lx72" aria-labelledby="lx72-goal-h">';
    echo '<h2 id="lx72-goal-h">' . e(tr('Cíl dnešní hodiny')) . '</h2>';
    if (trim((string)($goal['student'] ?? '')) !== '') echo '<p class="lx72-goal"' . edu_content_lang_attr() . '>' . e(trim((string)$goal['student'])) . '</p>';
    if ($criteria !== []) {
        echo '<p class="lx72-lead">' . e(tr('Poznáš, že to umíš, když:')) . '</p><ul class="lx72-list"' . edu_content_lang_attr() . '>';
        foreach ($criteria as $c) echo '<li>' . e($c) . '</li>';
        echo '</ul>';
    }
    echo edu_content_note_html();
    if ($ticket !== null && $studentKey !== '') lx72_render_ticket($classId, $lessonNo, $studentKey, $ticket);
    if ($homework !== []) {
        echo '<div class="lx72-card lx72-homework"><h3>' . e(tr('Domácí příprava (volitelné)')) . '</h3>'
            . '<p class="lx72-muted">' . e(tr('Dobrovolné – nezapočítává se do hodnocení.')) . '</p><ul class="lx72-list">';
        foreach ($homework as $h) {
            echo '<li><span' . edu_content_lang_attr() . '>' . e($h['text']) . '</span>'
                . ($h['minutes'] > 0 ? ' <span class="lx72-chip">' . e(tr('asi {n} min', ['n' => $h['minutes']])) . '</span>' : '') . '</li>';
        }
        echo '</ul></div>';
    }
    echo '</section>';
}

/** Exit ticket: formulář (bez JS) nebo uložená odpověď se zpětnou vazbou. */
function lx72_render_ticket(string $classId, int $lessonNo, string $studentKey, array $ticket): void
{
    $vi = lx72_variant_index($studentKey, $lessonNo, count($ticket['variants']));
    $variant = $ticket['variants'][$vi];
    $answer = lx72_answer_of($classId, $studentKey, $lessonNo);
    echo '<div class="lx72-card lx72-exit" id="exit-ticket"><h3>' . e(tr('Exit ticket')) . '</h3>'
        . '<p class="lx72-muted">' . e(tr('Jedna rychlá otázka na konec hodiny. Nehodnotí se – pomůže učiteli poznat, co zopakovat.')) . '</p>';
    if (is_array($answer) && (int)($answer['v'] ?? -1) === $vi) {
        $chosen = (string)($variant['options'][(int)($answer['c'] ?? -1)] ?? '');
        $ok = (int)($answer['ok'] ?? 0) === 1;
        echo '<p class="lx72-question"' . edu_content_lang_attr() . '>' . e($variant['question']) . '</p>'
            . '<p class="lx72-result ' . ($ok ? 'is-ok' : 'is-miss') . '" role="status"><strong>' . e($ok ? tr('Správně.') : tr('Tentokrát ne.')) . '</strong> '
            . tr_html('Tvoje odpověď: {answer}', ['answer' => edu_cs($chosen)]) . '</p>';
        if (!$ok) echo '<p>' . tr_html('Správná odpověď: {answer}', ['answer' => edu_cs((string)$variant['options'][$variant['correct']])]) . '</p>';
        if ($variant['explanation'] !== '') echo '<p class="lx72-muted"' . edu_content_lang_attr() . '>' . e($variant['explanation']) . '</p>';
        echo '</div>';
        return;
    }
    echo '<form method="post" data-v55-autosave="exit72">'
        . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="lx72_answer">'
        . '<input type="hidden" name="lesson" value="' . (int)$lessonNo . '">'
        . '<fieldset class="lx72-fieldset"><legend class="lx72-question"' . edu_content_lang_attr() . '>' . e($variant['question']) . '</legend>';
    foreach ($variant['options'] as $i => $option) {
        $id = 'lx72-o' . (int)$lessonNo . '-' . (int)$i;
        echo '<label class="lx72-option" for="' . e($id) . '"><input type="radio" id="' . e($id) . '" name="choice" value="' . (int)$i . '" required>'
            . '<span' . edu_content_lang_attr() . '>' . e($option) . '</span></label>';
    }
    echo '</fieldset><button class="btn primary" type="submit">' . e(tr('Odeslat odpověď')) . '</button></form></div>';
}

/** Karta „Exit ticket a příprava“ v Dnešní hodině (učitel, česky, bez jmen). */
function lx72_teacher_card_html(array $m): string
{
    $classId = (string)($m['class'] ?? '');
    $n = (int)($m['lesson']['number'] ?? 0);
    if ($classId === '' || $n < 1) return '';
    try {
        $sum = lx72_summary($classId, $n, array_column((array)($m['students'] ?? []), 'key'));
        $status = lc72_status($classId, $n);
    } catch (Throwable $e) {
        error_log('EDUCANET v72 souhrn exit ticketu: ' . get_class($e));
        return '';
    }
    if (!$sum['has_ticket']) return '';
    $link = function_exists('lt70_tab_link') ? lt70_tab_link('schvalovani', $classId, 'Otevřít schvalování lekce ' . $n . ' →', ['lesson' => (string)$n]) : '';
    $html = '<section class="c70-card" aria-labelledby="lx72-sum"><h2 id="lx72-sum">Exit ticket lekce ' . $n . '</h2>'
        . '<p><span class="c70-chip">' . e($status['label']) . '</span> <span class="c70-chip">formativní – nevstupuje do hodnocení</span></p>';
    if (!$sum['approved']) {
        return $html . '<p class="c70-muted">Žáci exit ticket ani domácí přípravu zatím nevidí – lekce čeká na schválení. Do té doby vidí původní obsah lekce.</p>'
            . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
    }
    $html .= '<ul class="c71-list"><li><strong>Odpovědělo:</strong> ' . (int)$sum['responded'] . ' z ' . (int)$sum['students'] . ' žáků</li>'
        . '<li><strong>Správně:</strong> ' . ($sum['percent'] === null ? '—' : (int)$sum['percent'] . ' %') . '</li>'
        . '<li><strong>Kompetence:</strong> ' . e($sum['competence'] !== '' ? $sum['competence'] : '—') . '</li></ul>';
    $html .= $sum['wrong_top'] !== null
        ? '<p><strong>Nejčastější chybná odpověď:</strong> „' . e($sum['wrong_top']['text']) . '“ (' . (int)$sum['wrong_top']['count'] . '×, varianta ' . (int)$sum['wrong_top']['variant'] . ')</p>'
        : '<p class="c70-muted">Zatím žádná chybná odpověď.</p>';
    $html .= '<details class="c71-steps"><summary>Varianty otázky (' . count($sum['variants']) . ')</summary><ol class="c70-steps">';
    foreach ($sum['variants'] as $v) $html .= '<li><strong>' . e($v['question']) . '</strong> <span class="c70-muted">' . (int)$v['correct'] . ' / ' . (int)$v['responded'] . ' správně</span></li>';
    return $html . '</ol></details>' . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
}
