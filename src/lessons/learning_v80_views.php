<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/learning_v80.php';

/**
 * EDUCANET v80 · žákovské UI podpory učení: odstupňované nápovědy a vysvětlení chyb v úloze, upoutávka „Na dnes k opakování“,
 * stránka Opakování chyb (?view=opakovat). Texty rozhraní přes tr() (katalog lang/<en|uk>/ui/learning_v80.php); nápovědy, vysvětlení
 * a zadání jsou výukový obsah (česky, edu_content_lang_attr()). Formuláře bez JS, CSRF, identita jen ze session, nic v URL kromě úlohy.
 */

function lr80_assets_html(bool $js = false): string
{
    $url = static fn(string $p): string => function_exists('asset_url') ? asset_url($p) : $p;
    return lr80_css_once()
        . ($js ? '<script src="' . e($url('assets/practice-v80.js?v=80.0')) . '" defer></script>' : '');
}

/** Styl v80 jednou za stránku (nápovědy a upoutávka uvnitř starších stránek, např. Úlohy). */
function lr80_css_once(): string
{
    static $done = false;
    if ($done) return '';
    $done = true;
    $url = static fn(string $p): string => function_exists('asset_url') ? asset_url($p) : $p;   // komponenty v78 (stejná adresa jako stepper v79 = z mezipaměti)
    return '<link rel="stylesheet" href="' . e($url('assets/components-v78.css?v=79.0')) . '"><link rel="stylesheet" href="' . e($url('assets/learning-v80.css?v=80.0')) . '">';
}

function lr80_csrf_html(string $action): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">';
}

/** Názvy úrovní nápovědy. */
function lr80_hint_names(): array
{
    return [1 => tr('Postrčení'), 2 => tr('Postup řešení'), 3 => tr('Vyřešený krok')];
}

/**
 * Blok nápověd k variantě: odkryté nápovědy (seznam) + tlačítko na další (POST lr80_hint). $mode = task | rep.
 */
function lr80_hints_html(string $classId, string $studentKey, string $mode, string $task, int $round, array $variant): string
{
    $hints = hn80_hints($variant);
    if ($hints === []) return '';
    $key = ($mode === 'rep' ? 'rep:' : '') . $task;
    $level = lr80_hint_level(lr80_state($classId, lr80_ref($classId, $studentKey)), $key, $round);
    $names = lr80_hint_names();
    $html = lr80_css_once() . '<section class="ds78 lr80-hints" id="lr80-hints" aria-labelledby="lr80-hints-h"><h3 id="lr80-hints-h">' . e(tr('Nápověda')) . '</h3>';
    if ($level > 0) {
        $html .= '<ol class="lr80-hint-list">';
        for ($i = 1; $i <= $level; $i++) $html .= '<li><strong>' . e(tr('Nápověda {n} ze {m}: {name}', ['n' => $i, 'm' => HN80_LEVELS, 'name' => $names[$i]])) . '</strong> <span' . edu_content_lang_attr() . '>' . e((string)preg_replace('/^Vyřešený krok[^:]*:\s*/u', '', $hints[$i - 1])) . '</span></li>';   // název úrovně už je v nadpisu
        $html .= '</ol>';
    } else {
        $html .= '<p class="lr80-muted">' . e(tr('Zasekl ses? Nápovědy jdou postupně: nejdřív postrčení, pak postup, nakonec jeden vyřešený krok.')) . '</p>';
    }
    if ($level < HN80_LEVELS) {
        $html .= '<form method="post" class="lr80-hint-form">' . lr80_csrf_html('lr80_hint') . '<input type="hidden" name="key" value="' . e($key) . '"><input type="hidden" name="round" value="' . $round . '">'
            . '<button class="ds-btn ds-btn--secondary" type="submit">' . e(tr('Ukázat nápovědu {n} ze {m}', ['n' => $level + 1, 'm' => HN80_LEVELS])) . '</button></form>';
    } else {
        $html .= '<p class="lr80-muted" role="status">' . e(tr('Všechny nápovědy jsou odkryté.')) . '</p>';
    }
    return $html . '<p class="lr80-note">' . e(tr('Nápověda nesnižuje body ani známku. Učitel vidí jen počet použitých nápověd.')) . '</p></section>';
}

/** Vysvětlení konkrétních chyb (pole → text). */
function lr80_why_html(array $variant, array $why): string
{
    if ($why === []) return '';
    $labels = [];
    foreach ((array)$variant['fields'] as $id => $f) $labels[is_array($f) ? (string)$f['id'] : (string)$id] = is_array($f) ? (string)$f['label'] : (string)$id;
    $html = lr80_css_once() . '<div class="ds78 lr80-why" role="status"><h3>' . e(tr('Kde je chyba')) . '</h3><ul>';
    foreach ($why as $id => $text) $html .= '<li><strong' . edu_content_lang_attr() . '>' . e($labels[(string)$id] ?? (string)$id) . ':</strong> <span' . edu_content_lang_attr() . '>' . e((string)$text) . '</span></li>';
    return $html . '</ul></div>';
}

/** Upoutávka „Na dnes k opakování: N“ (prázdná, když nic není splatné). */
function lr80_due_banner_html(string $classId, string $studentKey, ?int $now = null): string
{
    if (preg_match(LR80_CLASS_RE, $classId) !== 1 || $studentKey === '') return '';
    $n = count(lr80_rep_due(lr80_state($classId, lr80_ref($classId, $studentKey)), $now ?? time()));
    if ($n === 0) return '';
    return lr80_css_once() . '<div class="ds78 lr80-due" role="status"><p><strong>' . e(tr('Na dnes k opakování: {n}', ['n' => $n])) . '</strong> '
        . e(tr('Úlohy, ve kterých se objevila chyba, se vracejí po 1, 3 a 7 dnech.')) . '</p><a class="ds-btn ds-btn--primary" href="?view=opakovat">' . e(tr('Zopakovat teď')) . '</a></div>';
}

/** Text dne „za N dní“ (plurál). */
function lr80_days_text(int $d): string
{
    return match ($d) { 1 => tr('zítra'), 3 => tr('za 3 dny'), 7 => tr('za týden'), default => tr('za {n} dní', ['n' => $d]) };
}

function lr80_date_html(int $ts): string
{
    return function_exists('edu_date') ? edu_date($ts) : date('j. n.', $ts);
}

/** Stránka Opakování chyb (?view=opakovat, ?uloha= otevře jednu splatnou úlohu). */
function lr80_render_review_page(string $classId, string $studentKey, string $task, ?int $now = null): void
{
    $now ??= time();
    $ref = lr80_ref($classId, $studentKey);
    $state = lr80_state($classId, $ref);
    $due = lr80_rep_due($state, $now);
    $last = is_array($_SESSION['lr80_rep_last'] ?? null) ? $_SESSION['lr80_rep_last'] : null;
    echo lr80_assets_html() . '<div class="ds78 lr80-page"><h1>' . e(tr('Opakování chyb')) . '</h1>'
        . '<p class="lr80-lead">' . e(tr('Úloha, ve které se objevila chyba, se vrátí zítra, pak za 3 dny a nakonec za týden – pokaždé s jinými čísly. Když se splete, začne znovu od zítřka.')) . '</p>';
    if ($last !== null) echo lr80_rep_result_html($last);
    if ($task !== '' && in_array($task, $due, true)) {
        lr80_render_rep_task($classId, $studentKey, $task, (array)$state['rep'][$task], $ref);
        echo '</div>';
        return;
    }
    echo '<section class="ds-card lr80-sec" aria-labelledby="lr80-due-h"><h2 id="lr80-due-h">' . e(tr('Na dnes k opakování: {n}', ['n' => count($due)])) . '</h2>';
    if ($due === []) {
        echo '<div class="ds-empty" role="status"><h3>' . e(tr('Na dnešek je hotovo')) . '</h3><p>' . e(tr('Nic dalšího dnes opakovat nemusíš. Nové položky přibudou, až se v úloze nebo cvičení objeví chyba.')) . '</p>'
            . '<a class="ds-btn ds-btn--primary" href="?view=pokrok">' . e(tr('Otevřít Můj pokrok')) . '</a></div>';
    } else {
        echo '<ul class="lr80-list">';
        foreach ($due as $t) {
            $item = (array)$state['rep'][$t];
            echo '<li><span class="lr80-list__title">' . e(tb75_t_title($t)) . '</span> <span class="ds-badge is-warn">' . e(tr('opakování {n} ze 3', ['n' => (int)$item['s'] + 1])) . '</span>'
                . ' <a class="ds-btn ds-btn--primary" href="?' . e(http_build_query(['view' => 'opakovat', 'uloha' => $t])) . '#lr80-task">' . e(tr('Procvičit')) . '</a></li>';
        }
        echo '</ul>';
    }
    echo '</section>';
    $planned = lr80_rep_planned($state, $now);
    if ($planned !== []) {
        echo '<section class="ds-card lr80-sec" aria-labelledby="lr80-plan-h"><h2 id="lr80-plan-h">' . e(tr('Naplánováno')) . '</h2><ul class="lr80-list">';
        foreach ($planned as $t => $at) echo '<li><span class="lr80-list__title">' . e(tb75_t_title($t)) . '</span> <span class="lr80-muted">' . e(tr('vrátí se {datum}', ['datum' => lr80_date_html($at)])) . '</span></li>';
        echo '</ul></section>';
    }
    echo '</div>';
}

/** Jedna splatná úloha opakování (jeden pokus, pak nový termín). */
function lr80_render_rep_task(string $classId, string $studentKey, string $task, array $item, string $ref): void
{
    $v = lr80_rep_variant($task, $ref, $item);
    $round = (int)($item['c'] ?? 0) + 1;
    echo '<section class="ds-card lr80-sec" id="lr80-task" aria-labelledby="lr80-task-h"><h2 id="lr80-task-h">' . e(tb75_t_title($task)) . '</h2>'
        . '<p class="lr80-muted">' . e(tr('Opakování {n} ze 3 – jeden pokus. Když to bude správně, další přijde {kdy}.', ['n' => (int)$item['s'] + 1, 'kdy' => lr80_days_text(LR80_REP_DAYS[min(2, (int)$item['s'] + 1)])])) . '</p>'
        . '<p' . edu_content_lang_attr() . '>' . e((string)$v['prompt']) . '</p>' . ($v['data'] !== [] ? '<pre class="lr80-data"' . edu_content_lang_attr() . '>' . e(implode("\n", $v['data'])) . '</pre>' : '');
    echo lr80_hints_html($classId, $studentKey, 'rep', $task, $round, $v);
    echo '<form method="post" class="lr80-form">' . lr80_csrf_html('lr80_rep_answer') . '<input type="hidden" name="task" value="' . e($task) . '">';
    foreach ($v['fields'] as $f) {
        $id = 'lr80-' . $task . '-' . $f['id'];
        echo '<div class="ds-field"><label class="ds-label" for="' . e($id) . '"' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</label>';
        if ($f['kind'] === 'select') {
            echo '<select class="ds-input" id="' . e($id) . '" name="a[' . e((string)$f['id']) . ']" required><option value="">' . e(tr('Vyber…')) . '</option>';
            foreach ((array)$f['options'] as $o) echo '<option value="' . e((string)$o) . '">' . e((string)$o) . '</option>';
            echo '</select>';
        } else {
            echo '<input class="ds-input" id="' . e($id) . '" name="a[' . e((string)$f['id']) . ']" type="text" inputmode="' . ($f['kind'] === 'number' ? 'decimal' : 'text') . '" autocomplete="off" spellcheck="false" maxlength="120" required'
                . ((string)$f['hint'] !== '' ? ' aria-describedby="' . e($id) . '-h"' : '') . '>';
            if ((string)$f['hint'] !== '') echo '<p class="ds-help" id="' . e($id) . '-h"' . edu_content_lang_attr() . '>' . e((string)$f['hint']) . '</p>';
        }
        echo '</div>';
    }
    echo '<div class="lr80-actions"><button class="ds-btn ds-btn--primary" type="submit">' . e(tr('Zkontrolovat')) . '</button><a class="ds-btn ds-btn--secondary" href="?view=opakovat">' . e(tr('Zpět na seznam')) . '</a></div></form></section>';
}

/** Výsledek posledního opakování (z relace: správnost, nový termín, řešení a vysvětlení chyb). */
function lr80_rep_result_html(array $last): string
{
    $ok = !empty($last['ok']);
    $msg = $ok ? (!empty($last['done']) ? tr('Správně – tahle úloha má hotovou celou řadu opakování.') : tr('Správně. Další opakování {kdy}.', ['kdy' => lr80_days_text((int)$last['days'])]))
        : tr('Tentokrát ne – úloha se vrátí zítra s jinými čísly.');
    $html = '<div class="lr80-result ' . ($ok ? 'is-ok' : 'is-miss') . '" role="status" aria-live="polite"><p><strong>' . e(tb75_t_title((string)$last['task'])) . ': ' . e($msg) . '</strong></p>';
    if (!$ok) {
        $html .= lr80_why_html(['fields' => (array)($last['fields'] ?? [])], (array)($last['why'] ?? [])) . '<dl class="lr80-key">';
        foreach ((array)($last['fields'] ?? []) as $f) $html .= '<dt' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</dt><dd><code>' . e((string)$f['answer']) . '</code></dd>';
        $html .= '</dl><p class="lr80-muted"' . edu_content_lang_attr() . '>' . e((string)($last['explain'] ?? '')) . '</p>';
    }
    return $html . '</div>';
}

/** Rozcestník podpory učení (Můj pokrok, cvičení, opakování) – obyčejné odkazy. */
function lr80_links_html(): string
{
    return lr80_css_once() . '<nav class="ds78 lr80-links" aria-label="' . e(tr('Podpora učení')) . '"><a class="ds-btn ds-btn--secondary" href="?view=pokrok">' . e(tr('Můj pokrok')) . '</a>'
        . '<a class="ds-btn ds-btn--secondary" href="?view=cviceni">' . e(tr('Interaktivní cvičení')) . '</a><a class="ds-btn ds-btn--secondary" href="?view=opakovat">' . e(tr('Opakování chyb')) . '</a></nav>';
}
