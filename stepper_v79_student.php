<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79_views.php';

/**
 * EDUCANET v79 · krokové postupy žáka (texty přes tr(), katalog lang/<en|uk>/ui/stepper_v79.php; zadání úloh zůstává česky):
 *   - úloha s variantou (?view=ulohy&uloha=…): Zadání → Řešení → Kontrola → Co dál; odpověď = beze změny tb75_answer, další varianta tb75_next,
 *     krok jen v URL (?krok=) – rozpracovaný stav drží úložiště v75 (pokusy) a relace (poslední kontrola tb75_last);
 *   - připnutí práce do portfolia (?view=portfolio): Odkaz → Reflexe → Uložit; Zpět/Další = sp79_s_next / sp79_s_back (koncept jen v relaci),
 *     Uložit = beze změny pf75_s_pin se stejnými poli. Identita žáka jen ze session, žádná data žáka v URL.
 */

/** Slova komponenty pro žáka. */
function sp79s_words(): array
{
    return sp79_words([
        'count' => tr('Krok {n} ze {m}'), 'done' => tr('hotovo'), 'current' => tr('aktuální krok'), 'wait' => tr('čeká'), 'nav' => tr('Postup po krocích'),
        'back' => tr('Zpět'), 'next' => tr('Další'), 'errors' => tr('Než budeš pokračovat, oprav:'), 'edit' => tr('Upravit'), 'summary' => tr('Souhrn před uložením'),
    ]);
}

function tb79_labels(): array
{
    return [tr('Zadání'), tr('Řešení'), tr('Kontrola'), tr('Co dál')];
}

function tb79_url(string $task, int $step): string
{
    return '?' . http_build_query(['view' => 'ulohy', 'uloha' => $task, 'krok' => (string)$step]) . '#sp79-top';
}

/** Úloha s variantou po krocích (nahrazuje tb75_render_task na stránce ?view=ulohy). */
function tb79_render_task(string $classId, string $studentKey, string $task): void
{
    $ref = tb75_student_ref($classId, $studentKey);
    $cur = tb75_current($classId, $ref, $task);
    $e = $cur['entry'];
    $v = $cur['variant'];
    $last = is_array($_SESSION['tb75_last'] ?? null) && (string)($_SESSION['tb75_last']['task'] ?? '') === $task && (int)($_SESSION['tb75_last']['round'] ?? 0) === $e['round'] ? $_SESSION['tb75_last'] : null;
    $solved = $e['last_ok'] && $e['tries'] > 0;
    $closed = $solved || $e['shown'];
    $reach = $closed ? 4 : ($last !== null || $e['tries'] > 0 ? 3 : 2);
    $default = $closed || $last !== null ? 3 : ($e['tries'] > 0 ? 2 : 1);
    $step = sp79_current_step('uloha', $reach, $default);
    sp79_draft_put('uloha', ['scope' => $task, 'step' => $step, 'max' => $reach, 'data' => ['task' => $task]]);
    $labels = tb79_labels();
    $w = sp79s_words();
    echo sp79_student_assets_html() . '<p><a class="ui-link" href="?view=ulohy">← ' . e(tr('Úlohy s variantami')) . '</a></p>'
        . '<section class="l75-task sp79-flow" aria-labelledby="tb75-h-' . e($task) . '"><h2 id="tb75-h-' . e($task) . '">' . e(tb75_t_title($task)) . '</h2>'
        . sp79_bar_html($labels, $step, $reach, static fn(int $k): string => tb79_url($task, $k), $w) . sp79_errors_html([], $w);
    $prompt = '<p class="l75-muted">' . e(tr('Varianta {n} – jen tvoje. Pokusů v této variantě: {used} z {max}.', ['n' => $e['round'], 'used' => $e['tries'], 'max' => TB75_TRIES])) . '</p>'
        . '<p class="l75-prompt"' . edu_content_lang_attr() . '>' . e((string)$v['prompt']) . '</p>' . ($v['data'] !== [] ? '<pre class="l75-data"' . edu_content_lang_attr() . '>' . e(implode("\n", $v['data'])) . '</pre>' : '');
    echo sp79_panel_open(1, $step, $labels[0]) . $prompt . sp79_nav_links_html(tb79_url($task, 2), $labels[1], '', $w) . '</section>';
    echo sp79_panel_open(2, $step, $labels[1]) . '<details class="tb79-zadani"><summary>' . e(tr('Zadání (pro kontrolu)')) . '</summary>' . $prompt . '</details>';
    if ($closed) {
        echo '<p class="l75-muted" role="status">' . e(tr('Tahle varianta je uzavřená – vezmi si další.')) . '</p>' . sp79_nav_links_html(tb79_url($task, 4), $labels[3], tb79_url($task, 3), $w) . '</section>';
    } else {
        echo tb79_form_html($task, $v, $last, max(0, TB75_TRIES - $e['tries']), $w, $labels) . '</section>';
    }
    echo sp79_panel_open(3, $step, $labels[2]) . tb79_check_html($task, $v, $last, $solved, $closed, max(0, TB75_TRIES - $e['tries']), $w, $labels) . '</section>';
    echo sp79_panel_open(4, $step, $labels[3]);
    if ($closed) {
        echo '<p>' . e(tr('Vezmi si další variantu se stejným typem úlohy, nebo si vyřešenou úlohu připni do portfolia.')) . '</p>'
            . '<div class="ds78 sp79-w"><div class="sp79-nav sp79-nav--wrap"><form method="post" class="l75-actions">' . tb75_hidden($task, 'tb75_next', 'ulohy') . '<button class="ds-btn ds-btn--primary" type="submit">' . e(tr('Další varianta')) . '</button></form>'
            . ($solved ? '<a class="ds-btn ds-btn--secondary" href="?view=portfolio#sp79-top">' . e(tr('Připnout do portfolia')) . '</a>' : '')
            . '<a class="ds-btn ds-btn--secondary" href="?view=ulohy">' . e(tr('Všechny úlohy')) . '</a></div></div>';
    } else {
        echo '<p>' . e(tr('Nejdřív úlohu vyřeš nebo si nech ukázat řešení – pak dostaneš další variantu.')) . '</p>' . sp79_nav_links_html(tb79_url($task, 2), $labels[1], '', $w);
    }
    echo '</section></section>';
}

/** Krok 2: formulář odpovědi (tb75_answer beze změny) se značkami z poslední kontroly. */
function tb79_form_html(string $task, array $v, ?array $last, int $left, array $w, array $labels): string
{
    $html = '<form method="post" class="l75-form" data-sp79>' . tb75_hidden($task, 'tb75_answer', 'ulohy');
    foreach ($v['fields'] as $f) {
        $id = 'tb75-' . $task . '-' . $f['id'];
        $state = $last !== null ? (!empty($last['fields'][$f['id']]) ? 'ok' : 'miss') : '';
        // Správně vyplněné pole se předvyplní správnou hodnotou varianty (text žáka se neukládá) a je jen ke čtení – žák opravuje jen chybná pole.
        $keep = $state === 'ok' ? (string)$f['answer'] : '';
        $describedBy = trim(((string)$f['hint'] !== '' ? $id . '-h ' : '') . ($state !== '' ? $id . '-m' : ''));
        $msg = ' data-sp79-msg="' . e(tr('Vyplň pole „{pole}“.', ['pole' => (string)$f['label']])) . '"';
        $html .= '<div class="l75-field' . ($state !== '' ? ' is-' . $state : '') . '"><label for="' . e($id) . '"' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</label>';
        if ($f['kind'] === 'select') {
            $html .= '<select id="' . e($id) . '" name="a[' . e((string)$f['id']) . ']" required' . $msg . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '') . '><option value="">' . e(tr('Vyber…')) . '</option>';
            foreach ((array)$f['options'] as $o) $html .= '<option value="' . e((string)$o) . '"' . ($keep !== '' && (string)$o === $keep ? ' selected' : '') . '>' . e((string)$o) . '</option>';
            $html .= '</select>';
        } else {
            $html .= '<input id="' . e($id) . '" name="a[' . e((string)$f['id']) . ']" type="text" inputmode="' . ($f['kind'] === 'number' ? 'numeric' : 'text') . '" autocomplete="off" spellcheck="false" maxlength="80" required' . $msg
                . ($keep !== '' ? ' value="' . e($keep) . '" readonly' : '') . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '') . '>';
            if ((string)$f['hint'] !== '') $html .= '<small id="' . e($id) . '-h" class="l75-muted"' . edu_content_lang_attr() . '>' . e((string)$f['hint']) . '</small>';
        }
        if ($state !== '') $html .= '<span class="l75-mark" id="' . e($id) . '-m">' . e($state === 'ok' ? tr('správně') : tr('zkus znovu')) . '</span>';
        $html .= '</div>';
    }
    return $html . '<p class="l75-muted">' . e(tr('Zbývá pokusů: {n}. Kontrola proběhne hned, výsledek uvidíš v dalším kroku.', ['n' => $left])) . '</p>'
        . '<div class="ds78 sp79-w"><div class="sp79-nav"><button class="ds-btn ds-btn--primary" type="submit" data-sp79-validate>' . e(tr('Zkontrolovat')) . '</button>'
        . '<a class="ds-btn ds-btn--secondary" href="' . e(tb79_url($task, 1)) . '">' . e((string)$w['back']) . '</a></div></div></form>';
}

/** Krok 3: výsledek kontroly (klíč a vysvětlení po vyřešení nebo zobrazení řešení). */
function tb79_check_html(string $task, array $v, ?array $last, bool $solved, bool $closed, int $left, array $w, array $labels): string
{
    if ($closed) {
        $html = '<div class="l75-result ' . ($solved ? 'is-ok' : 'is-miss') . '" role="status"><strong>' . e($solved ? tr('Správně, všechna pole sedí.') : tr('Tentokrát to nevyšlo – tady je řešení.')) . '</strong></div><dl class="l75-key">';
        foreach ($v['fields'] as $f) $html .= '<dt' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</dt><dd><code>' . e((string)$f['answer']) . '</code></dd>';
        return $html . '</dl><p class="l75-muted"' . edu_content_lang_attr() . '>' . e((string)$v['explain']) . '</p>' . sp79_nav_links_html(tb79_url($task, 4), $labels[3], '', $w);
    }
    if ($last === null) return '<p class="l75-muted" role="status">' . e(tr('Zatím nevyšlo. Zbývá pokusů: {n}.', ['n' => $left])) . '</p>' . tb79_fix_link($task);
    $good = (int)($last['correct'] ?? 0);
    $html = '<div class="l75-result is-miss" role="status"><strong>' . e(tr('Správně {good} z {all} polí.', ['good' => $good, 'all' => count($v['fields'])])) . '</strong> '
        . e(tr('Oprav pole označená „zkus znovu“. Zbývá pokusů: {n}.', ['n' => $left])) . '</div><ul class="sp79-list">';
    foreach ($v['fields'] as $f) $html .= '<li><span' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</span>: ' . e(!empty($last['fields'][$f['id']]) ? tr('správně') : tr('zkus znovu')) . '</li>';
    return $html . '</ul>' . tb79_fix_link($task);
}

/** Odkaz zpět na řešení (oprava chybných polí). */
function tb79_fix_link(string $task): string
{
    return '<div class="ds78 sp79-w"><div class="sp79-nav"><a class="ds-btn ds-btn--primary" href="' . e(tb79_url($task, 2)) . '">' . e(tr('Opravit odpovědi')) . '</a></div></div>';
}
