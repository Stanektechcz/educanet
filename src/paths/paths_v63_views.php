<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/paths_v63_flow.php';

/**
 * EDUCANET v63 · žákovské šablony výukových cest: seznam cest, krok, reflexe a karta „Co dál“ na přehledu.
 *
 * Texty UI přes tr() (doména paths); obsah cest a otázky z banky jsou výukový obsah a zůstávají česky
 * (obal edu_content_lang_attr()). Parsonsova úloha funguje bez JS: každý řádek má tlačítka nahoru/dolů
 * (POST p63_parsons_move), pořadí je podepsané HMAC a drží se v session. Stav je vždy i textem, ne jen barvou.
 * Žák vidí jen sebe; cesty nedávají XP ani body. Reflexní věta se zobrazí jen žákovi.
 */

/** Jen styly karty „Co dál“ (malý soubor, načítá se na přehledu). */
function p63_card_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/paths-card-v63.css?v=63.0')) . '">' . "\n";
}

/** Styly a skript stránek cest (seznam, krok); skript je jen drobné vylepšení a načítá se s defer. */
function p63_assets(): void
{
    p63_card_assets();
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/paths-v63.css?v=63.0')) . '">' . "\n";
    echo '<script src="' . e(asset_url('assets/paths-v63.js?v=63.0')) . '" defer></script>' . "\n";
}

/** Obsah (česky) vložený do přeloženého UI. */
function p63_cs(string $text): string
{
    return '<span' . edu_content_lang_attr() . '>' . e($text) . '</span>';
}

function p63_t_type(string $type): string
{
    return ['explain' => tr('Vysvětlení'), 'pre' => tr('Předpověz výstup'), 'parsons' => tr('Poskládej pořadí'), 'retrieval' => tr('Vybavování'),
        'verify' => tr('Ověření'), 'reflect' => tr('Reflexe')][$type] ?? tr('Opakování');
}

function p63_t_minutes(int $n): string
{
    return trn(['one' => '{n} minuta', 'few' => '{n} minuty', 'other' => '{n} minut'], $n);
}

/** Věta s důvodem doporučení (HTML, název cesty je obsah). */
function p63_reason_html(array $rec): string
{
    $title = ['title' => p63_cs((string)$rec['title'])];
    return [
        'spaced' => tr_html('Je čas zopakovat cestu „{title}“, aby ti učivo zůstalo v hlavě.', $title),
        'continue' => tr_html('Rozpracoval jsi cestu „{title}“. Dokonči další krok.', $title),
        'assigned' => tr_html('Učitel ti přiřadil cestu „{title}“.', $title),
        'weak' => tr_html('Tahle kompetence je zatím rozpracovaná. Cesta „{title}“ ji posílí.', $title),
        'unverified' => tr_html('Cesta „{title}“ ověří, co už z tohoto tématu umíš.', $title),
    ][(string)$rec['reason']] ?? tr_html('Další cesta ve tvé třídě: „{title}“.', $title);
}

/** Karta „Co dál“ na přehledu: jedno doporučení s důvodem a odhadem času. Nic nezapisuje. */
function p63_render_next_card(string $classId, string $studentKey): void
{
    $rec = p63_next($classId, $studentKey);
    if ($rec === null) return;
    p63_card_assets();
    $minutes = (int)$rec['minutes'];
    echo '<section class="p63-card ui-card" aria-labelledby="p63-next-title"><p class="ui-eyebrow">' . e(tr('Co dál')) . '</p>'
        . '<h2 id="p63-next-title">' . p63_cs((string)$rec['title']) . '</h2>'
        . '<p class="p63-reason">' . p63_reason_html($rec) . '</p>'
        . '<p class="p63-meta">' . p63_cs((string)$rec['step_title']) . ' · ' . e(p63_t_minutes($minutes)) . '</p>'
        . '<p><a class="ui-btn ui-btn--primary p63-go" href="' . e((string)$rec['href']) . '">' . e(tr('Pokračovat')) . '</a> '
        . '<a class="ui-link p63-all" href="?view=cesty">' . e(tr('Všechny cesty')) . '</a></p></section>';
}

function p63_step_status_text(string $status): string
{
    return ['done' => tr('Hotovo'), 'next' => tr('Další krok'), 'locked' => tr('Zamčeno'), 'tried' => tr('Zkoušeno')][$status] ?? '';
}

/** Jeden řádek mini-seznamu kroků v kartě cesty. */
function p63_render_step_item(array $path, array $state, array $step, ?string $next): string
{
    $id = (string)$step['id'];
    $entry = p63_step_entry($state, (string)$path['id'], $id);
    $status = $entry['status'] === 'done' ? 'done' : ($id === $next ? 'next' : ($entry['status'] === 'tried' ? 'tried' : 'locked'));
    $icon = ['done' => '✓', 'next' => '→', 'tried' => '◐', 'locked' => '○'][$status];
    $open = p63_step_open($path, $state, $id);
    $title = p63_cs((string)$step['title']);
    $text = $open ? '<a href="?view=cesta&amp;path=' . e(rawurlencode((string)$path['id'])) . '&amp;step=' . e(rawurlencode($id)) . '">' . $title . '</a>' : $title;
    return '<li class="p63-step p63-st-' . e($status) . '"><span class="p63-icon" aria-hidden="true">' . e($icon) . '</span><span class="p63-label">' . $text
        . '<small class="p63-type">' . e(p63_t_type((string)$step['type'])) . '</small></span><span class="p63-state">' . e(p63_step_status_text($status)) . '</span></li>';
}

function p63_render_path_card(array $path, array $state, bool $assigned): string
{
    $progress = p63_path_progress($path, $state);
    $items = '';
    foreach ((array)$path['steps'] as $step) $items .= p63_render_step_item($path, $state, (array)$step, $progress['next']);
    $badge = $assigned ? '<span class="p63-badge">' . e(tr('Přiřadil učitel')) . '</span>' : '';
    $pid = 'p63-p-' . e((string)$path['id']);
    return '<li class="p63-path ui-card"><h2 id="' . $pid . '">' . p63_cs((string)$path['title']) . ' ' . $badge . '</h2>'
        . '<p class="p63-goal">' . p63_cs((string)$path['goal']) . '</p>'
        . '<p class="p63-meta">' . e(p63_t_minutes((int)$path['minutes'])) . ' · ' . e(tr('Hotovo {done} z {total} kroků', ['done' => $progress['done'], 'total' => $progress['total']])) . '</p>'
        . '<ol class="p63-steps" aria-labelledby="' . $pid . '">' . $items . '</ol></li>';
}

/** Seznam cest třídy (?view=cesty). */
function p63_render_list(string $classId, string $studentKey): void
{
    p63_assets();
    $sid = p63_student_id($classId, $studentKey);
    $state = $sid !== null ? p63_state($sid) : p63_state_normalize([]);
    $assigned = $sid !== null ? array_keys($state['assigned']) : [];
    echo '<div class="p63-wrap"><h1>' . e(tr('Moje cesty')) . '</h1>'
        . '<p class="p63-lead">' . e(tr('Cesta je krátká posloupnost kroků k jedné kompetenci: vysvětlení, cvičení, ověření a reflexe. Cesty nedávají XP ani body, jen ukazují, co už umíš. Můžeš dělat i cesty, které ti učitel nepřiřadil.')) . '</p>';
    if ($sid === null) echo '<p class="ui-flash ui-flash--warn" role="status">' . e(tr('Cesty se ukládají k tvému účtu ve třídě. Zatím tě v soupisu třídy nevidíme, postup se proto neuloží. Požádej učitele o zařazení.')) . '</p>';
    $due = $sid !== null ? p63_spaced_due($state['spaced'], time()) : [];
    if ($due !== []) echo '<p class="ui-flash" role="status">' . e(trn(['one' => 'Čeká na tebe {n} opakování.', 'few' => 'Čekají na tebe {n} opakování.', 'other' => 'Čeká na tebe {n} opakování.'], count($due))) . '</p>';
    echo '<ul class="p63-paths">';
    foreach (p63_paths_for_class($classId) as $path) echo p63_render_path_card($path, $state, in_array((string)$path['id'], $assigned, true));
    echo '</ul></div>';
}

// ---------------------------------------------------------------------------
// Krok cesty
// ---------------------------------------------------------------------------

/** Skrytá pole každého formuláře kroku. */
function p63_form_head(string $action, array $path, array $step): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="path" value="' . e((string)$path['id']) . '"><input type="hidden" name="step" value="' . e((string)$step['id']) . '">';
}

function p63_render_explain(array $path, array $step, bool $done): string
{
    $html = '';
    foreach ((array)$step['paragraphs'] as $p) $html .= '<p>' . p63_cs((string)$p) . '</p>';
    if (!empty($step['example'])) $html .= '<pre class="p63-pre"' . edu_content_lang_attr() . '><code>' . e((string)$step['example']) . '</code></pre>';
    return $html . '<form method="post">' . p63_form_head('p63_submit', $path, $step)
        . '<button class="ui-btn ui-btn--primary p63-go" type="submit">' . e($done ? tr('Přečteno, pokračuj') : tr('Rozumím, pokračovat')) . '</button></form>';
}

/** Jedna otázka z banky jako fieldset (radio nebo číslo); $i je pozice v odpovědích. */
function p63_render_question(array $q, int $i, string $seed): string
{
    $legend = '<legend>' . e((string)($i + 1) . '. ') . p63_cs((string)$q['prompt']) . '</legend>';
    if ($q['type'] === 'numeric') {
        $id = 'p63-n-' . $i;
        return '<fieldset class="p63-q">' . $legend . '<label class="p63-num" for="' . $id . '">' . e(tr('Tvoje odpověď (číslo)')) . '</label>'
            . '<input class="ui-input" id="' . $id . '" type="text" inputmode="decimal" autocomplete="off" name="a[' . $i . ']"></fieldset>';
    }
    $options = $q['type'] === 'bool' ? [['1', tr('Ano')], ['0', tr('Ne')]]
        : array_map(static fn($o): array => [(string)$o, (string)$o], p63_seeded_shuffle((array)$q['options'], $seed . '|' . $q['id']));
    $html = '<fieldset class="p63-q">' . $legend;
    foreach ($options as $k => [$value, $label]) {
        $id = 'p63-o-' . $i . '-' . $k;
        $html .= '<label class="p63-opt" for="' . $id . '"><input type="radio" id="' . $id . '" name="a[' . $i . ']" value="' . e($value) . '"> <span>'
            . ($q['type'] === 'bool' ? e($label) : p63_cs($label)) . '</span></label>';
    }
    return $html . '</fieldset>';
}

function p63_render_quiz(array $path, array $step, string $sid, array $state): string
{
    $entry = p63_step_entry($state, (string)$path['id'], (string)$step['id']);
    $attempt = $entry['attempts'] + 1;
    $isVerify = (string)$step['type'] === 'verify';
    $left = p63_verify_left($state, (string)$path['id']);
    if ($isVerify && $left <= 0) return '<p class="ui-flash ui-flash--warn" role="status">' . e(tr('Dnešní pokusy o ověření jsou vyčerpané. Zkus to zítra, mezitím si zopakuj vysvětlení.')) . '</p>';
    $seed = $sid . '|' . $path['id'] . '|' . $step['id'] . '|' . $attempt;
    $html = $isVerify ? '<p class="p63-meta">' . e(trn(['one' => 'Dnes zbývá {n} pokus.', 'few' => 'Dnes zbývají {n} pokusy.', 'other' => 'Dnes zbývá {n} pokusů.'], $left)) . '</p>' : '';
    $html .= '<form method="post" class="p63-form">' . p63_form_head('p63_submit', $path, $step);
    foreach (p63_step_question_ids($path, $step, $sid, $state, $attempt) as $i => $id) {
        $q = p63_question($id);
        if ($q !== null) $html .= p63_render_question($q, (int)$i, $seed);
    }
    return $html . '<button class="ui-btn ui-btn--primary p63-go" type="submit">' . e($isVerify ? tr('Odevzdat ověření') : tr('Zkontrolovat odpovědi')) . '</button></form>';
}

function p63_render_pre(array $path, array $step, string $sid, array $state): string
{
    $cases = array_values((array)$step['cases']);
    $case = (array)($cases[p63_current_variant($path, $step, $sid, $state)] ?? []);
    if ($case === []) return '';
    $html = '<div class="p63-case"' . edu_content_lang_attr() . '>';
    if (!empty($case['static'])) {
        $html .= '<p>' . e((string)$case['show']) . '</p>';
    } else {
        foreach ((array)$case['setup'] as $line) $html .= '<pre class="p63-pre p63-done"><code>$ ' . e((string)$line) . '</code></pre>';
        $html .= '<pre class="p63-pre"><code>$ ' . e((string)$case['cmd']) . '</code></pre>';
    }
    $html .= '</div><form method="post" class="p63-form">' . p63_form_head('p63_submit', $path, $step) . '<fieldset class="p63-q"><legend' . edu_content_lang_attr() . '>' . e((string)$case['question']) . '</legend>';
    foreach ((array)$case['options'] as $k => $option) {
        $id = 'p63-c-' . $k;
        $html .= '<label class="p63-opt" for="' . $id . '"><input type="radio" id="' . $id . '" name="choice" value="' . (int)$k . '" required> <span' . edu_content_lang_attr() . '>' . e((string)$option) . '</span></label>';
    }
    return $html . '</fieldset><button class="ui-btn ui-btn--primary p63-go" type="submit">' . e(tr('Ověřit předpověď')) . '</button></form>';
}

/** Pořadí řádků Parsonovy úlohy: z session (platný podpis a pokus), jinak počáteční. @return list<int> */
function p63_parsons_order(array $path, array $step, string $sid, int $attempt): array
{
    $key = $path['id'] . '|' . $step['id'];
    $saved = is_array($_SESSION['p63_order'][$key] ?? null) ? $_SESSION['p63_order'][$key] : [];
    $order = array_values(array_map('intval', (array)($saved['order'] ?? [])));
    if ((int)($saved['attempt'] ?? 0) === $attempt && p63_valid_order($order, count((array)$step['lines']))
        && hash_equals(p63_parsons_sign($order, (string)$path['id'], (string)$step['id'], $attempt, p63_parsons_secret()), (string)($saved['sig'] ?? ''))) return $order;
    return p63_parsons_initial($sid, (string)$path['id'], (string)$step['id'], $attempt, $step);
}

function p63_move_button(array $path, array $step, array $order, int $pos, string $dir, int $attempt, string $line): string
{
    $sig = p63_parsons_sign($order, (string)$path['id'], (string)$step['id'], $attempt, p63_parsons_secret());
    $label = $dir === 'up' ? tr('Posunout nahoru: {line}', ['line' => $line]) : tr('Posunout dolů: {line}', ['line' => $line]);
    return '<form method="post" class="p63-move">' . p63_form_head('p63_parsons_move', $path, $step)
        . '<input type="hidden" name="order" value="' . e(implode(',', $order)) . '"><input type="hidden" name="sig" value="' . e($sig) . '">'
        . '<input type="hidden" name="attempt" value="' . $attempt . '"><input type="hidden" name="pos" value="' . $pos . '"><input type="hidden" name="dir" value="' . e($dir) . '">'
        . '<button type="submit" id="p63-' . e($dir) . '-' . $pos . '" aria-label="' . e($label) . '"' . edu_content_lang_attr() . '>' . ($dir === 'up' ? '↑' : '↓') . '</button></form>';
}

function p63_render_parsons(array $path, array $step, string $sid, array $state): string
{
    $attempt = p63_step_entry($state, (string)$path['id'], (string)$step['id'])['attempts'] + 1;
    $order = p63_parsons_order($path, $step, $sid, $attempt);
    $lines = array_values((array)$step['lines']);
    $items = '';
    foreach ($order as $pos => $idx) {
        $line = (string)$lines[$idx];
        $items .= '<li class="p63-line"><code' . edu_content_lang_attr() . '>' . e($line) . '</code><span class="p63-moves">'
            . ($pos > 0 ? p63_move_button($path, $step, $order, $pos, 'up', $attempt, $line) : '') . ($pos < count($order) - 1 ? p63_move_button($path, $step, $order, $pos, 'down', $attempt, $line) : '') . '</span></li>';
    }
    $sig = p63_parsons_sign($order, (string)$path['id'], (string)$step['id'], $attempt, p63_parsons_secret());
    $live = (string)($_SESSION['p63_live'] ?? '');
    unset($_SESSION['p63_live']);
    return '<p class="p63-meta">' . e(tr('Řádky přesouvej tlačítky nahoru a dolů. Když je pořadí hotové, zkontroluj ho.')) . '</p>'
        . '<p class="p63-live" role="status" aria-live="polite">' . e($live) . '</p><ol class="p63-order" id="p63-order">' . $items . '</ol>'
        . '<form method="post" class="p63-form">' . p63_form_head('p63_submit', $path, $step) . '<input type="hidden" name="order" value="' . e(implode(',', $order)) . '"><input type="hidden" name="sig" value="' . e($sig) . '">'
        . '<button class="ui-btn ui-btn--primary p63-go" type="submit">' . e(tr('Zkontrolovat pořadí')) . '</button></form>';
}

/** Panel s výsledkem posledního pokusu (z session, zobrazí se jednou). */
function p63_render_result(array $res, array $path, array $step): string
{
    $score = (int)round((float)$res['score'] * 100);
    $head = !empty($res['passed']) ? tr('Splněno, výsledek {score} %.', ['score' => $score]) : tr('Zatím ne, výsledek {score} %.', ['score' => $score]);
    $html = '<div class="p63-result ' . (!empty($res['passed']) ? 'p63-ok' : 'p63-bad') . '" role="status" aria-live="polite" tabindex="-1" id="p63-result"><p><strong>' . e($head) . '</strong></p>';
    $type = (string)$step['type'];
    if (in_array($type, ['retrieval', 'verify'], true)) $html .= p63_result_questions((array)$res['detail'], $type === 'verify');
    if ($type === 'pre') $html .= p63_result_pre($path, $step, (array)$res['detail']);
    if ($type === 'parsons' && empty($res['passed'])) $html .= '<p>' . e(tr('Pořadí ještě není správné. Zkus ho upravit; zamícháme ti ho znovu.')) . '</p>';
    return $html . '</div>';
}

function p63_result_questions(array $detail, bool $hideAnswers): string
{
    $html = '<ul class="p63-review">';
    foreach ((array)($detail['ids'] ?? []) as $i => $id) {
        $ok = !empty($detail['ok'][$i]);
        $q = p63_question((string)$id);
        $html .= '<li><span aria-hidden="true">' . ($ok ? '✓' : '✗') . '</span> ' . e($ok ? tr('Správně') : tr('Špatně')) . ' · ' . e(tr('Otázka {n}', ['n' => (int)$i + 1]));
        if ($q !== null && !$hideAnswers) $html .= '<br>' . p63_cs((string)($q['type'] === 'bool' ? ($q['answer'] ? tr('Ano') : tr('Ne')) : $q['answer'])) . ' – ' . p63_cs((string)$q['explain']);
        $html .= '</li>';
    }
    return $html . '</ul>' . ($hideAnswers ? '<p>' . e(tr('U ověření se správné odpovědi neukazují. Zopakuj si vysvětlení a zkus to znovu.')) . '</p>' : '');
}

function p63_result_pre(array $path, array $step, array $detail): string
{
    foreach ((array)$step['cases'] as $case) {
        if ((string)$case['id'] !== (string)($detail['case'] ?? '')) continue;
        $out = !empty($case['static']) ? (string)$case['reveal'] : (string)p63_pre_output((string)$path['id'], (string)$step['id'], (string)$case['id']);
        return '<p>' . e(tr('Skutečný výsledek:')) . '</p><pre class="p63-pre"' . edu_content_lang_attr() . '><code>' . e($out) . '</code></pre><p>' . p63_cs((string)$case['explain']) . '</p>';
    }
    return '';
}

/** Reflexe: sebehodnocení 1–4 a jedna věta (vidí ji jen žák). */
function p63_render_reflect(array $path, array $step, array $state): string
{
    $pathId = (string)$path['id'];
    if (!p63_step_done($state, $pathId, 'verify')) return '<p class="ui-flash ui-flash--warn" role="status">' . e(tr('Reflexe je dostupná po splněném ověření.')) . '</p>';
    $saved = is_array($state['reflect'][$pathId] ?? null) ? $state['reflect'][$pathId] : null;
    $levels = [1 => tr('Zatím tápu'), 2 => tr('Jde to s oporou'), 3 => tr('Zvládám to samostatně'), 4 => tr('Zvládám a umím to vysvětlit')];
    $html = '<form method="post" class="p63-form">' . p63_form_head('p63_reflect', $path, $step) . '<fieldset class="p63-q"><legend>' . e(tr('Jak moc to teď umíš?')) . '</legend>';
    foreach ($levels as $n => $label) {
        $id = 'p63-s-' . $n;
        $html .= '<label class="p63-opt" for="' . $id . '"><input type="radio" id="' . $id . '" name="self" value="' . $n . '" required' . ((int)($saved['self'] ?? 0) === $n ? ' checked' : '') . '> <span>' . e((string)$n . ' – ' . $label) . '</span></label>';
    }
    return $html . '</fieldset><label class="p63-num" for="p63-note">' . e(tr('Jednou větou: co ti šlo a co si ještě zopakuješ?')) . '</label>'
        . '<textarea class="ui-textarea" id="p63-note" name="note" maxlength="' . P63_NOTE_MAX . '" rows="3" aria-describedby="p63-note-hint" data-p63-count>' . e((string)($saved['note'] ?? '')) . '</textarea>'
        . '<p class="p63-meta" id="p63-note-hint">' . e(tr('Větu vidíš jen ty. Učitel vidí pouze souhrn za třídu, ne tvůj text. Nejvýš {max} znaků.', ['max' => P63_NOTE_MAX])) . '</p>'
        . '<p class="p63-count" aria-live="polite" data-p63-counter></p>'
        . '<button class="ui-btn ui-btn--primary p63-go" type="submit">' . e(tr('Uložit reflexi')) . '</button></form>';
}

/** Výsledek reflexe: porovnání odhadu s ověřením (kalibrace). */
function p63_reflect_feedback(array $res): string
{
    $delta = (int)$res['delta'];
    $text = $delta > 0 ? tr('Tvůj odhad ({self}) je vyšší než výsledek ověření ({actual}). Zkus si příště víc ověřovat, než si věříš.', ['self' => (int)$res['self'], 'actual' => (int)$res['actual']])
        : ($delta < 0 ? tr('Tvůj odhad ({self}) je nižší než výsledek ověření ({actual}). Umíš víc, než si myslíš.', ['self' => (int)$res['self'], 'actual' => (int)$res['actual']])
        : tr('Tvůj odhad ({self}) sedí s výsledkem ověření ({actual}).', ['self' => (int)$res['self'], 'actual' => (int)$res['actual']]));
    return '<div class="p63-result p63-ok" role="status" aria-live="polite" tabindex="-1" id="p63-result"><p><strong>' . e(tr('Reflexe je uložená.')) . '</strong></p><p>' . e($text) . '</p></div>';
}

/** Stránka kroku (?view=cesta&path=&step=): hlavička, případný výsledek a tělo podle typu. */
function p63_render_step(string $classId, string $studentKey, array $path, array $step): void
{
    p63_assets();
    $sid = p63_student_id($classId, $studentKey);
    $state = $sid !== null ? p63_state($sid) : p63_state_normalize([]);
    $stepId = (string)$step['id'];
    $ids = p63_step_ids($path);
    $pos = array_search($stepId, $ids, true);
    $res = is_array($_SESSION['p63_result'] ?? null) && ($_SESSION['p63_result']['path'] ?? '') === $path['id'] && ($_SESSION['p63_result']['step'] ?? '') === $stepId ? $_SESSION['p63_result'] : null;
    unset($_SESSION['p63_result']);
    echo '<div class="p63-wrap"><p><a class="ui-link" href="?view=cesty">← ' . e(tr('Moje cesty')) . '</a></p><h1>' . p63_cs((string)$path['title']) . '</h1>'
        . '<p class="p63-meta">' . e($pos === false ? p63_t_type('spaced') : tr('Krok {n} z {total}', ['n' => (int)$pos + 1, 'total' => count($ids)])) . ' · ' . e(p63_t_type((string)$step['type'])) . ' · ' . e(p63_t_minutes((int)$step['minutes'])) . '</p>'
        . '<h2>' . p63_cs((string)$step['title']) . '</h2>';
    if ($sid === null) echo '<p class="ui-flash ui-flash--warn" role="status">' . e(tr('Zatím tě v soupisu třídy nevidíme, postup se proto neuloží.')) . '</p>';
    elseif (!p63_step_open($path, $state, $stepId)) echo '<p class="ui-flash ui-flash--warn" role="status">' . e(tr('Tenhle krok se odemkne po dokončení předchozích kroků.')) . '</p>';
    if ($res !== null && isset($res['reflect'])) echo p63_reflect_feedback((array)$res['reflect']);
    elseif ($res !== null) echo p63_render_result($res, $path, $step);
    if ($sid !== null && p63_step_open($path, $state, $stepId)) echo p63_render_step_body($path, $step, $sid, $state);
    if ($sid !== null && $pos !== false && isset($ids[$pos + 1]) && p63_step_done($state, (string)$path['id'], $stepId)) {
        echo '<p class="p63-next"><a class="ui-btn ui-btn--secondary p63-go" href="?view=cesta&amp;path=' . e(rawurlencode((string)$path['id'])) . '&amp;step=' . e(rawurlencode((string)$ids[$pos + 1])) . '">' . e(tr('Další krok')) . ' →</a></p>';
    }
    echo '</div>';
}

function p63_render_step_body(array $path, array $step, string $sid, array $state): string
{
    $done = p63_step_done($state, (string)$path['id'], (string)$step['id']);
    return match ((string)$step['type']) {
        'explain' => p63_render_explain($path, $step, $done),
        'pre' => p63_render_pre($path, $step, $sid, $state),
        'parsons' => p63_render_parsons($path, $step, $sid, $state),
        'reflect' => p63_render_reflect($path, $step, $state),
        default => p63_render_quiz($path, $step, $sid, $state),
    };
}
