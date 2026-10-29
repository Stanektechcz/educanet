<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/levels/levels_v75.php';

/**
 * EDUCANET v75 · diferenciace v hodině – žákovské UI v lekci v56 (volba Základ / Standard / Výzva, akce dif75_pick).
 * Texty přes tr(); zadání úrovní je výukový obsah (česky). Bez JS, CSRF, identita jen ze session. Učitelský přehled: levels_v75_teacher_views.php.
 */

/** Název úrovně pro žáka. */
function lv75_t_level(string $level): string
{
    return match ($level) { 'zaklad' => tr('Základ'), 'vyzva' => tr('Výzva'), default => tr('Standard') };
}

/** Vysvětlený důvod doporučení (bez čísel jiných žáků; jen vlastní výsledky). */
function lv75_t_reason(array $s): string
{
    if (!$s['data']) return tr('Zatím nemáme dost tvých výsledků, tak začni na Standardu. Úroveň si můžeš kdykoli změnit.');
    $bits = [];
    if ($s['exit_n'] > 0) $bits[] = tr('v posledních exit ticketech máš {ok} z {n} správně', ['ok' => $s['exit_ok'], 'n' => $s['exit_n']]);
    if ($s['rate'] !== null) $bits[] = tr('v úlohách s variantami za 14 dní {rate} % správně', ['rate' => $s['rate']]);
    $basis = implode(tr(' a '), $bits);
    return match ($s['level']) {
        'zaklad' => tr('Doporučujeme Základ: {basis}. Začni s oporou a pak klidně přidej.', ['basis' => $basis]),
        'vyzva' => tr('Doporučujeme Výzvu: {basis}. Zkus něco navíc.', ['basis' => $basis]),
        default => tr('Doporučujeme Standard: {basis}.', ['basis' => $basis]),
    };
}

function lv75_render_student_block(string $classId, int $lessonNo): void
{
    try {
        $levels = lv75_student_levels($classId, $lessonNo);
    } catch (Throwable $e) {
        error_log('EDUCANET v75 úrovně: ' . get_class($e));
        return;
    }
    if ($levels === null) return;
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    if ($studentKey === '') return;
    $suggest = lv75_suggest($classId, $studentKey, $lessonNo);
    $pick = lv75_pick_of($classId, $studentKey, $lessonNo);
    $chosen = is_array($pick) ? (string)$pick['l'] : '';
    echo '<link rel="stylesheet" href="' . e(function_exists('asset_url') ? asset_url('assets/learn-v75.css?v=75.0') : 'assets/learn-v75.css?v=75.0') . '">'
        . '<section class="l75-levels ds78" id="urovne" aria-labelledby="lv75-h"><h2 id="lv75-h">' . e(tr('Vyber si úroveň')) . '</h2>'
        . '<p class="l75-muted">' . tr_html('Aktivita: {activity}', ['activity' => edu_cs((string)$levels['activity'])]) . '</p>'
        . '<p class="l75-suggest" role="note"><span class="l75-chip is-accent">' . e(tr('Doporučení')) . '</span> ' . e(lv75_t_reason($suggest)) . '</p>'
        . '<p class="l75-muted">' . e(tr('Úroveň volíš ty – nic se tím nehodnotí a můžeš ji změnit. Učitel vidí jen, kolik žáků zvolilo kterou úroveň.')) . '</p><div class="l75-level-grid">';
    foreach (LV75_LEVELS as $key => $_) {
        $row = (array)($levels[$key] ?? []);
        $isChosen = $chosen === $key;
        echo '<article class="l75-level' . ($isChosen ? ' is-chosen' : '') . ($suggest['level'] === $key ? ' is-suggested' : '') . '" aria-labelledby="lv75-' . e($key) . '">'
            . '<h3 id="lv75-' . e($key) . '">' . e(lv75_t_level($key)) . ($suggest['level'] === $key ? ' <span class="l75-chip">' . e(tr('doporučeno')) . '</span>' : '') . ($isChosen ? ' <span class="l75-chip is-ok">' . e(tr('tvoje volba')) . '</span>' : '') . '</h3>'
            . '<p' . edu_content_lang_attr() . '>' . e((string)($row['task'] ?? '')) . '</p>'
            . '<p class="l75-muted"><strong>' . e(tr('Hotovo, když:')) . '</strong> <span' . edu_content_lang_attr() . '>' . e((string)($row['check'] ?? '')) . '</span></p>';
        if (!$isChosen) {
            echo '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="dif75_pick">'
                . '<input type="hidden" name="lesson" value="' . (int)$lessonNo . '"><input type="hidden" name="level" value="' . e($key) . '">'
                . '<button class="btn ' . ($suggest['level'] === $key && $chosen === '' ? 'primary' : 'secondary') . '" type="submit">' . e(tr('Vybrat: {level}', ['level' => lv75_t_level($key)])) . '</button></form>';
        }
        echo '</article>';
    }
    echo '</div>' . edu_content_note_html() . '</section>';
}

/** POST dif75_pick (CSRF ověřil index.php; třída a žák ze session; lekci a návrat ověřila akce app/actions/v75.php). */
function lv75_handle_post(string $classId, int $lesson, string $back): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    $level = is_string($_POST['level'] ?? null) ? (string)$_POST['level'] : '';
    $ok = $lesson >= 1 && lv75_pick($classId, $studentKey, $lesson, $level);
    $_SESSION['flash'] = $ok ? tr('Úroveň uložena: {level}.', ['level' => lv75_t_level($level)]) : tr('Úroveň se nepodařilo uložit.');
    redirect_to($back . '#urovne');
}
