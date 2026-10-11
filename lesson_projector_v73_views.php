<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · Projektor hodiny (`hodina_projektor`) – nástupce Režimu hodiny (ten zůstává do v76). Jen čtení.
 *   Aktuální úsek plánu, cíl, kód hodiny velkým písmem, zadání a exit ticket BEZ odpovědí (jen text otázek, žádné možnosti,
 *   správné odpovědi ani vysvětlení; zadání bez očekávaných výstupů). Písmo ≥ 28 px, kontrast AA (tokeny v61/v68).
 *   Bez JS: všechny úseky jsou kotvy (#pj73-sN, :target), u běžícího úseku čas konce a „Obnovit“. S JS (assets/lesson-v73.js):
 *   ← → úseky, mezerník časovač, F celá obrazovka. Na veřejné obrazovce jen počty žáků, nikdy jména.
 */

function pj73_clock(int $ts): string
{
    return date('H:i', $ts);
}

/** Karta jednoho úseku (časovač: s JS odpočet, bez JS délka / čas konce). */
function pj73_segment_html(array $seg, int $i, int $count, array $current, string $refresh): string
{
    $isCurrent = $i === $current['index'];
    $running = $isCurrent && $current['running'];
    $minutes = max(1, (int)$seg['to'] - (int)$seg['from']);
    $text = lp73_segment_student_text($seg);
    $static = $running ? 'Úsek končí v ' . pj73_clock($current['ends_at']) . ' (zbývá ' . (int)ceil($current['remaining'] / 60) . ' min)' : 'Délka úseku: ' . $minutes . ' min';
    $html = '<article id="pj73-s' . $i . '" class="pj73-seg' . ($isCurrent ? ' is-current' : '') . '" tabindex="-1" aria-labelledby="pj73-h' . $i . '">'
        . '<p class="pj73-when">' . (int)$seg['from'] . '–' . (int)$seg['to'] . ' min · úsek ' . ($i + 1) . ' z ' . $count . ($running ? ' · právě teď' : '') . '</p>'
        . '<h2 id="pj73-h' . $i . '">' . e((string)$seg['phase'] !== '' ? (string)$seg['phase'] : 'Úsek ' . ($i + 1)) . '</h2>'
        . ($text !== '' ? '<p class="pj73-do">' . e($text) . '</p>' : '')
        . '<p class="pj73-timer" data-pj73-timer data-minutes="' . $minutes . '"' . ($running ? ' data-ends="' . (int)$current['ends_at'] . '"' : '') . '>'
        . '<span class="pj73-left" data-pj73-left>' . e($static) . '</span>'
        . ($running ? ' <a class="pj73-refresh" href="' . e($refresh) . '">Obnovit</a>' : '') . '</p></article>';
    return $html;
}

/** Kód hodiny velkým písmem, nebo výzva k otevření (pro učitele). */
function pj73_code_html(array $m): string
{
    $s = $m['session'];
    $html = '<section class="pj73-code" aria-labelledby="pj73-code-h"><h2 id="pj73-code-h">Kód hodiny</h2>';
    if ($s['exists'] && $s['open'] && (string)$s['code'] !== '') {
        $html .= '<p class="pj73-codebig">' . e((string)$s['code']) . '</p><p>Přihlas se → <strong>Mám kód hodiny</strong> → zadej kód.</p>'
            . '<p class="pj73-count">' . (int)$s['joined'] . ' pracuje · ' . (int)$s['submitted'] . ' odevzdalo</p>';
    } else {
        $html .= '<p class="pj73-nocode">Kód hodiny teď není otevřený.</p><p class="pj73-hint">' . lt70_tab_link('hodina', $m['class'], 'Otevřít hodinu v Dnešní hodině', [], 'pj73-link') . '</p>';
    }
    return $html . '</section>';
}

/** Záložka „Projektor“. */
function pj73_render_projector(array $modules): void
{
    [$requested, $lessonParam] = lp73_request();
    try {
        $m = lp73_model($modules, $requested, $lessonParam);
    } catch (Throwable $e) {
        error_log('EDUCANET v73 projektor: ' . $e->getMessage());
        echo c70_empty_html('Projektor se teď nepodařilo sestavit.', 'Zkuste stránku obnovit, nebo použijte Režim hodiny.', [['hodina', 'Dnešní hodina'], ['teach', 'Režim hodiny']]);
        return;
    }
    if ($m['class'] === '') {
        echo c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Projektor se ukáže, jakmile vám administrátor přidělí třídu (Správa → Učitelé).', []);
        return;
    }
    $l = $m['lesson'];
    $n = (int)$l['number'];
    $extra = $m['slot']['override'] ? ['lesson' => $n] : [];
    $refresh = (function_exists('teacher68_tab_url') ? teacher68_tab_url('hodina_projektor', $m['class']) : '?tab=hodina_projektor&class=' . rawurlencode($m['class'])) . ($extra ? '&lesson=' . $n : '');
    $back = lt70_tab_link('hodina', $m['class'], '← Dnešní hodina', $extra, 'pj73-link');
    $timeline = array_values($l['timeline']);
    $count = count($timeline);
    $html = '<div class="pj73" data-pj73 data-now="' . (int)$m['now'] . '" data-current="' . (int)$m['current']['index'] . '">'
        . '<header class="pj73-bar"><p class="pj73-title"><span>' . e(lt70_class_name($modules, $m['class'])) . '</span> <strong>Lekce ' . $n . ' · ' . e(lp73_title($l)) . '</strong></p>'
        . '<nav class="pj73-nav" aria-label="Projektor">' . $back . ' <button type="button" class="pj73-btn" data-pj73-toggle hidden>Časovač ▶︎ / ❚❚</button>'
        . ' <button type="button" class="pj73-btn" data-pj73-full hidden>Celá obrazovka</button></nav></header>';
    $html .= '<h1 class="c70-sr">Projektor · lekce ' . $n . '</h1><div class="pj73-top">' . pj73_code_html($m)
        . '<section class="pj73-goal" aria-labelledby="pj73-goal-h"><h2 id="pj73-goal-h">Cíl hodiny</h2><p class="pj73-goaltext">' . e((string)$l['goal']['student'] !== '' ? (string)$l['goal']['student'] : (string)$l['title']) . '</p>'
        . ($l['goal']['success_criteria'] !== [] ? '<ul class="pj73-list">' . implode('', array_map(static fn($c): string => '<li>' . e((string)$c) . '</li>', array_slice($l['goal']['success_criteria'], 0, 4))) . '</ul>' : '')
        . '</section></div>';
    if ($count > 0) {
        $html .= '<section class="pj73-stage" aria-label="Průběh hodiny">';
        foreach ($timeline as $i => $seg) $html .= pj73_segment_html($seg, $i, $count, $m['current'], $refresh . '#pj73-s' . $i);
        $html .= '<nav class="pj73-steps" aria-label="Úseky hodiny"><ol>';
        foreach ($timeline as $i => $seg) {
            $html .= '<li><a href="#pj73-s' . $i . '"' . ($i === $m['current']['index'] ? ' aria-current="step"' : '') . '>' . (int)$seg['from'] . '–' . (int)$seg['to'] . ' · ' . e(mb_strimwidth((string)$seg['phase'], 0, 28, '…')) . '</a></li>';
        }
        $html .= '</ol></nav></section>';
    } else {
        $html .= '<p class="pj73-hint">Lekce zatím nemá plán po minutách.</p>';
    }
    $tasks = lp73_task_texts($l, LP73_PROJECTOR_TASKS_MAX);
    $questions = lp73_exit_questions($l);
    $html .= '<div class="pj73-cols">';
    if ($tasks !== []) $html .= '<section class="pj73-tasks" aria-labelledby="pj73-tasks-h"><h2 id="pj73-tasks-h">Zadání</h2><ol class="pj73-list">' . implode('', array_map(static fn(string $t): string => '<li>' . e($t) . '</li>', $tasks)) . '</ol></section>';
    if ($questions !== []) {
        $html .= '<section class="pj73-exit" aria-labelledby="pj73-exit-h"><h2 id="pj73-exit-h">Exit ticket</h2><p>Na konci hodiny odpovíš v aplikaci – dostaneš jednu z těchto otázek:</p>'
            . '<ol class="pj73-list">' . implode('', array_map(static fn(string $q): string => '<li>' . e($q) . '</li>', $questions)) . '</ol></section>';
    }
    $html .= '</div><p class="pj73-help">Klávesy: ← → další / předchozí úsek · mezerník spustí nebo zastaví časovač · F celá obrazovka. Bez JavaScriptu použijte odkazy úseků a „Obnovit“.</p>'
        . '<p class="c70-sr" aria-live="polite" data-pj73-live></p></div>';
    echo $html;
}
