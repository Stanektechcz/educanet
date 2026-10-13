<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/taskbank_v75.php';

/**
 * EDUCANET v75 · banka úloh s variantami – žákovské UI (?view=ulohy, akce tb75_answer / tb75_next). Texty přes tr(),
 * zadání úloh je výukový obsah (česky, edu_content_lang_attr()). Formuláře bez JS, CSRF, identita jen ze session.
 */

/** Název úlohy pro žáka (msgid shodné s TB75_TASKS, hlídá audit v75). */
function tb75_t_title(string $task): string
{
    $ids = [
        'net_subnet' => trm('Podsíť: síť, broadcast a počet adres'), 'net_mask' => trm('Maska a prefix'), 'net_ports' => trm('Porty a služby'),
        'net_route' => trm('Směrovací tabulka'), 'lnx_path' => trm('Cesty v Linuxu'), 'lnx_chmod' => trm('Oprávnění a chmod'), 'lnx_grep' => trm('grep a roury'),
        'gfx_color' => trm('Barvy: HEX a RGB'), 'web_units' => trm('Jednotky CSS: px, em, rem a %'), 'css_spec' => trm('Selektory a specificita'), 'img_dpi' => trm('Rozlišení obrázku a DPI'),   // v76
    ];
    $msgid = $ids[$task] ?? null;
    return $msgid !== null ? tr($msgid) : $task;
}

function tb75_css_link(): string
{
    return '<link rel="stylesheet" href="' . e(function_exists('asset_url') ? asset_url('assets/learn-v75.css?v=75.0') : 'assets/learn-v75.css?v=75.0') . '">';
}

/** Stav úlohy pro seznam: [text, třída štítku]. */
function tb75_status_label(array $e): array
{
    if ($e['solved'] > 0 && !($e['tries'] > 0 && !$e['last_ok'])) return [tr('Vyřešeno {n}×', ['n' => $e['solved']]), 'is-ok'];
    if ($e['tries'] > 0) return [tr('Rozpracováno'), 'is-warn'];
    return [tr('Nezačato'), ''];
}

function tb75_render_page(string $classId, string $studentKey, string $task): void
{
    echo tb75_css_link() . '<div class="l75-wrap">';
    $tasks = tb75_tasks_for_class($classId);
    if ($tasks === []) {
        echo '<h1>' . e(tr('Úlohy s variantami')) . '</h1><div class="l75-empty" role="status"><strong>' . e(tr('Pro tvou třídu zatím úlohy s variantami nejsou.')) . '</strong> '
            . e(tr('Začínáme sítěmi a Linuxem (3.A a 4.A). Až učitel přidá sadu i pro tvůj předmět, objeví se tady.'))
            . '<p><a class="btn primary" href="?view=dashboard">' . e(tr('Zpět na přehled')) . '</a></p></div></div>';   // v79.1: prázdný stav s akcí
        return;
    }
    if ($task !== '' && in_array($task, $tasks, true)) {
        tb75_render_task($classId, $studentKey, $task);
        echo '</div>';
        return;
    }
    $ref = tb75_student_ref($classId, $studentKey);
    $state = tb75_student_state($classId, $ref);
    echo '<h1>' . e(tr('Úlohy s variantami')) . '</h1>'
        . '<p class="l75-lead">' . e(tr('Každý dostane stejný typ úlohy, ale s jinými čísly – opsat výsledek od souseda nejde. Odpověď se zkontroluje hned. Úlohy se nepočítají do známky ani bodů, slouží k procvičení.')) . '</p>'
        . '<ul class="l75-cards">';
    foreach ($tasks as $t) {
        $e = tb75_entry($state, $t);
        [$label, $tone] = tb75_status_label($e);
        echo '<li class="l75-card"><h2><a href="?' . e(http_build_query(['view' => 'ulohy', 'uloha' => $t])) . '">' . e(tb75_t_title($t)) . '</a></h2>'
            . '<p><span class="l75-chip ' . e($tone) . '">' . e($label) . '</span> <span class="l75-muted">' . e(tr('asi {n} min', ['n' => (int)TB75_TASKS[$t]['minutes']])) . '</span></p></li>';
    }
    echo '</ul></div>';
}

function tb75_render_task(string $classId, string $studentKey, string $task, string $returnView = 'ulohy'): void
{
    if ($returnView === 'ulohy') {
        require_once __DIR__ . '/stepper_v79_student.php';   // v79: Zadání → Řešení → Kontrola → Co dál (tb75_answer / tb75_next beze změny)
        tb79_render_task($classId, $studentKey, $task);
        return;
    }
    $ref = tb75_student_ref($classId, $studentKey);
    $cur = tb75_current($classId, $ref, $task);
    $e = $cur['entry'];
    $v = $cur['variant'];
    $last = is_array($_SESSION['tb75_last'] ?? null) && (string)($_SESSION['tb75_last']['task'] ?? '') === $task && (int)($_SESSION['tb75_last']['round'] ?? 0) === $e['round'] ? $_SESSION['tb75_last'] : null;
    if ($returnView === 'ulohy') echo '<p><a class="ui-link" href="?view=ulohy">← ' . e(tr('Úlohy s variantami')) . '</a></p>';
    echo '<section class="l75-task" aria-labelledby="tb75-h-' . e($task) . '"><h2 id="tb75-h-' . e($task) . '">' . e(tb75_t_title($task)) . '</h2>'
        . '<p class="l75-muted">' . e(tr('Varianta {n} – jen tvoje. Pokusů v této variantě: {used} z {max}.', ['n' => $e['round'], 'used' => $e['tries'], 'max' => TB75_TRIES])) . '</p>'
        . '<p class="l75-prompt"' . edu_content_lang_attr() . '>' . e((string)$v['prompt']) . '</p>';
    if ($v['data'] !== []) echo '<pre class="l75-data"' . edu_content_lang_attr() . '>' . e(implode("\n", $v['data'])) . '</pre>';
    $solved = $e['last_ok'] && $e['tries'] > 0;
    if ($solved || $e['shown']) {
        echo '<div class="l75-result ' . ($solved ? 'is-ok' : 'is-miss') . '" role="status"><strong>' . e($solved ? tr('Správně, všechna pole sedí.') : tr('Tentokrát to nevyšlo – tady je řešení.')) . '</strong></div>'
            . '<dl class="l75-key">';
        foreach ($v['fields'] as $f) echo '<dt' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</dt><dd><code>' . e((string)$f['answer']) . '</code></dd>';
        echo '</dl><p class="l75-muted"' . edu_content_lang_attr() . '>' . e((string)$v['explain']) . '</p>'
            . '<form method="post" class="l75-actions">' . tb75_hidden($task, 'tb75_next', $returnView) . '<button class="btn primary" type="submit">' . e(tr('Další varianta')) . '</button></form></section>';
        return;
    }
    if ($last !== null) {
        $good = (int)($last['correct'] ?? 0);
        echo '<div class="l75-result is-miss" role="status"><strong>' . e(tr('Správně {good} z {all} polí.', ['good' => $good, 'all' => count($v['fields'])])) . '</strong> '
            . e(tr('Oprav pole označená „zkus znovu“. Zbývá pokusů: {n}.', ['n' => max(0, TB75_TRIES - $e['tries'])])) . '</div>';
    }
    echo '<form method="post" class="l75-form">' . tb75_hidden($task, 'tb75_answer', $returnView);
    foreach ($v['fields'] as $f) {
        $id = 'tb75-' . $task . '-' . $f['id'];
        $state = $last !== null ? (!empty($last['fields'][$f['id']]) ? 'ok' : 'miss') : '';
        // Správně vyplněné pole se předvyplní správnou hodnotou varianty (text žáka se neukládá) a je jen ke čtení – žák opravuje jen chybná pole.
        $keep = $state === 'ok' ? (string)$f['answer'] : '';
        $describedBy = trim(((string)$f['hint'] !== '' ? $id . '-h ' : '') . ($state !== '' ? $id . '-m' : ''));
        echo '<div class="l75-field' . ($state !== '' ? ' is-' . $state : '') . '"><label for="' . e($id) . '"' . edu_content_lang_attr() . '>' . e((string)$f['label']) . '</label>';
        if ($f['kind'] === 'select') {
            echo '<select id="' . e($id) . '" name="a[' . e((string)$f['id']) . ']" required' . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '') . '><option value="">' . e(tr('Vyber…')) . '</option>';
            foreach ((array)$f['options'] as $o) echo '<option value="' . e((string)$o) . '"' . ($keep !== '' && (string)$o === $keep ? ' selected' : '') . '>' . e((string)$o) . '</option>';
            echo '</select>';
        } else {
            echo '<input id="' . e($id) . '" name="a[' . e((string)$f['id']) . ']" type="text" inputmode="' . ($f['kind'] === 'number' ? 'numeric' : 'text') . '" autocomplete="off" spellcheck="false" maxlength="80" required'
                . ($keep !== '' ? ' value="' . e($keep) . '" readonly' : '') . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '') . '>';
            if ((string)$f['hint'] !== '') echo '<small id="' . e($id) . '-h" class="l75-muted"' . edu_content_lang_attr() . '>' . e((string)$f['hint']) . '</small>';
        }
        if ($state !== '') echo '<span class="l75-mark" id="' . e($id) . '-m">' . e($state === 'ok' ? tr('správně') : tr('zkus znovu')) . '</span>';
        echo '</div>';
    }
    echo '<button class="btn primary" type="submit">' . e(tr('Zkontrolovat')) . '</button></form></section>';
}

function tb75_hidden(string $task, string $action, string $returnView): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="task" value="' . e($task) . '"><input type="hidden" name="back" value="' . e($returnView === 'opakovani' ? 'opakovani' : 'ulohy') . '">';
}

/** POST tb75_answer / tb75_next (CSRF ověřil index.php; třída a žák ze session). */
function tb75_handle_post(string $action, string $classId): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    $task = is_string($_POST['task'] ?? null) ? (string)$_POST['task'] : '';
    $back = (string)($_POST['back'] ?? '') === 'opakovani' ? '?view=opakovani' : '?' . http_build_query(['view' => 'ulohy', 'uloha' => $task]);
    if ($studentKey === '' || !in_array($task, tb75_tasks_for_class($classId), true)) { $_SESSION['flash'] = tr('Úlohu se nepodařilo najít.'); redirect_to('?view=ulohy'); }
    if ($action === 'tb75_next') {
        unset($_SESSION['tb75_last']);
        if (!tb75_next($classId, $studentKey, $task)) $_SESSION['flash'] = tr('Novou variantu dostaneš po vyřešení nebo po zobrazení řešení.');
        redirect_to($back);
    }
    $answers = is_array($_POST['a'] ?? null) ? array_filter($_POST['a'], 'is_string') : [];
    $res = tb75_submit($classId, $studentKey, $task, $answers);
    if (!$res['ok']) {
        $_SESSION['flash'] = match ((string)$res['error']) { 'empty' => tr('Vyplň aspoň jedno pole.'), 'solved', 'shown' => tr('Tahle varianta je uzavřená – vezmi si další.'), default => tr('Úlohu se nepodařilo najít.') };
        redirect_to($back);
    }
    $_SESSION['tb75_last'] = ['task' => $task, 'round' => (int)$res['entry']['round'], 'fields' => $res['result']['fields'], 'correct' => $res['result']['correct']];
    redirect_to($back . '#tb75-h-' . $task);
}
