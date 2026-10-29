<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/teacher/classmap_v80_1.php';
require_once dirname(__DIR__, 2) . '/src/student/support_v80_1.php';

/**
 * EDUCANET v80.1 · záložka cockpitu `mapa801` – Mapa třídy (česky, desktop první, responzivní od 320 px).
 * Mapa žáci × kompetence / úlohy s hodnotou textem a ikonou v každé buňce (barva je jen doplněk), filtry a řazení přes GET (whitelist),
 * lepicí hlavička a první sloupce (vzor v79.1), klik na buňku = detail žáka. Pod mapou jeden formulář: výběr žáků zaškrtnutím
 * a dvě akce – rychlá zpětná vazba (fráze, upravitelný text) a přidělení procvičování. Jména podle volby učitele (tp721): „Žák N“.
 */

/** „3 sloupce“, „10 žáků“ (česká množná čísla v popisku tabulky). */
function cm801_count(int $n, string $one, string $few, string $many): string
{
    return $n . ' ' . ($n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many));
}

/** Záhlaví sloupce bez úvodního „Umím“ (kratší sloupce; plné znění kompetence je v nabídce Řadit podle a v detailu žáka). */
function cm801_short_label(string $label): string
{
    $short = (string)preg_replace('/^Umím\s+/u', '', $label);
    return $short === $label ? $label : mb_strtoupper(mb_substr($short, 0, 1)) . mb_substr($short, 1);
}

function cm801_url(string $classId, array $state, array $change = []): string
{
    $q = ['tab' => 'mapa801', 'class' => $classId];
    foreach (array_merge($state, $change) as $k => $v) {
        if ($v === '' || $v === 0 || ($k === 'razeni' && $v === 'jmeno') || ($k === 'smer' && $v === 'asc') || ($k === 'zobrazeni' && $v === 'mapa')) continue;
        $q[$k] = $v;
    }
    return '?' . http_build_query($q);
}

/** Záhlaví sloupce s odkazem na řazení (aria-sort na th). */
function cm801_th(string $classId, array $state, string $key, string $label, string $small = ''): string
{
    $active = $state['razeni'] === $key;
    $dir = $active && $state['smer'] === 'asc' ? 'desc' : 'asc';
    $sort = $active ? ' aria-sort="' . ($state['smer'] === 'asc' ? 'ascending' : 'descending') . '"' : '';
    return '<th scope="col"' . $sort . '><a class="cm801-sort" href="' . e(cm801_url($classId, $state, ['razeni' => $key, 'smer' => $dir])) . '">' . e($label)
        . ($small !== '' ? '<small>' . e($small) . '</small>' : '') . '<span class="c70-sr">' . e($active ? ' (řazeno ' . ($state['smer'] === 'asc' ? 'vzestupně' : 'sestupně') . ', klikněte pro opačný směr)' : ' (seřadit)') . '</span></a></th>';
}

/** Buňka mapy: ikona + hodnota textem; v zobrazení „tabulka“ celé slovo bez barvy. Klik = detail žáka. */
function cm801_cell_html(array $cell, string $href, bool $plain, string $colLabel): string
{
    $lvl = CM801_LEVELS[$cell['level']] ?? CM801_LEVELS['none'];
    $value = $cell['value'] === null ? '—' : $cell['value'] . ' %';
    $inner = $plain ? e($cell['text']) : '<span aria-hidden="true">' . e($lvl['icon']) . '</span> ' . e($value) . '<span class="c70-sr"> ' . e($lvl['label']) . '</span>';
    return '<td class="cm801-c is-' . e((string)$cell['level']) . '" data-label="' . e($colLabel) . '"><a href="' . e($href) . '">' . $inner . '</a></td>';
}

function cm801_student_url(string $classId, string $key): string
{
    return '?' . http_build_query(['tab' => 'student360', 'class' => $classId, 'student' => $key]);
}

/** Filtry (GET) nad mapou. */
function cm801_filters_html(string $classId, array $m): string
{
    $s = $m['state'];
    $opt = static fn(string $v, string $label, string $cur): string => '<option value="' . e($v) . '"' . ($v === $cur ? ' selected' : '') . '>' . e($label) . '</option>';
    $html = '<form method="get" class="cm801-filters" aria-label="Filtry mapy"><input type="hidden" name="tab" value="mapa801"><input type="hidden" name="class" value="' . e($classId) . '">';
    if (count($m['sources']) > 1) {
        $html .= '<div class="ds-field"><label class="ds-label" for="cm801-zdroj">Co zobrazit</label><select class="ds-input" id="cm801-zdroj" name="zdroj">';
        foreach ($m['sources'] as $src) $html .= $opt($src, CM801_SOURCES[$src], $s['zdroj']);
        $html .= '</select></div>';
    }
    $html .= '<div class="ds-field"><label class="ds-label" for="cm801-oblast">Oblast</label><select class="ds-input" id="cm801-oblast" name="oblast">' . $opt('', 'Všechny oblasti', $s['oblast']);
    foreach ($m['groups'] as $g => $label) $html .= $opt((string)$g, $label, $s['oblast']);
    $html .= '</select></div><fieldset class="cm801-range"><legend class="ds-label">Lekce (rozsah)</legend><label for="cm801-od" class="c70-sr">Od lekce</label>'
        . '<input class="ds-input" id="cm801-od" name="od" type="number" inputmode="numeric" min="1" max="' . CM801_LESSONS . '" value="' . ($s['od'] > 0 ? $s['od'] : '') . '" placeholder="od" aria-describedby="cm801-range-help">'
        . '<span aria-hidden="true">–</span><label for="cm801-do" class="c70-sr">Do lekce</label><input class="ds-input" id="cm801-do" name="do" type="number" inputmode="numeric" min="1" max="' . CM801_LESSONS . '" value="' . ($s['do'] > 0 ? $s['do'] : '') . '" placeholder="do" aria-describedby="cm801-range-help">'
        . '<p class="ds-help" id="cm801-range-help">Jen kompetence, jejichž témata se v těchto lekcích probírají (1–' . CM801_LESSONS . ').</p></fieldset>';
    $html .= '<div class="ds-field"><label class="ds-label" for="cm801-razeni">Řadit podle</label><select class="ds-input" id="cm801-razeni" name="razeni">' . $opt('jmeno', 'Jména', $s['razeni']) . $opt('prumer', 'Průměru', $s['razeni']);
    foreach ($m['columns'] as $id => $c) $html .= $opt((string)$id, $c['label'], $s['razeni']);
    $html .= '</select></div><div class="ds-field"><label class="ds-label" for="cm801-smer">Směr</label><select class="ds-input" id="cm801-smer" name="smer">' . $opt('asc', 'Vzestupně (nejslabší první)', $s['smer']) . $opt('desc', 'Sestupně', $s['smer']) . '</select></div>'
        . '<div class="ds-field"><label class="ds-label" for="cm801-zobr">Zobrazení</label><select class="ds-input" id="cm801-zobr" name="zobrazeni">' . $opt('mapa', 'Mapa (ikony a barvy)', $s['zobrazeni']) . $opt('tabulka', 'Tabulka (jen text)', $s['zobrazeni']) . '</select></div>'
        . '<div class="cm801-filter-actions"><button type="submit" class="ds-btn ds-btn--secondary">Použít</button>'
        . ($s['oblast'] !== '' || $s['od'] > 0 || $s['razeni'] !== 'jmeno' || $s['zobrazeni'] !== 'mapa' ? '<a class="ds-btn ds-btn--ghost" href="' . e(cm801_url($classId, ['zdroj' => $s['zdroj']])) . '">Zrušit filtr</a>' : '') . '</div></form>';
    return $html;
}

function cm801_legend_html(string $source): string
{
    $html = '<ul class="cm801-legend" aria-label="Legenda mapy">';
    foreach (CM801_LEVELS as $lvl => $l) $html .= '<li class="cm801-c is-' . e($lvl) . '"><span aria-hidden="true">' . e($l['icon']) . '</span> ' . e($l['label']) . '</li>';
    return $html . '</ul><p class="ds-help">' . e($source === 'komp'
        ? 'Hodnota = skóre kompetence z posledních 8 důkazů (umí od 70 %, učí se 40–69 %, potřebuje pomoc pod 40 %; umí jistě = potvrzeno dvěma druhy důkazů). Klik na buňku otevře detail žáka.'
        : 'Hodnota = úspěšnost pokusů žáka v úloze (umí od 75 %, učí se 40–74 %, potřebuje pomoc pod 40 %). Klik na buňku otevře detail žáka.') . '</p>';
}

/** Souhrn „od učitele“ po žácích: ref => [open, done, fb_at]. */
function cm801_support_summary(string $classId): array
{
    $data = ts801_data($classId);
    if ($data['fb'] === [] && $data['as'] === []) return [];
    $lr = lr80_data($classId);
    $tb = (array)((tb75_data()['classes'] ?? [])[$classId] ?? []);
    $out = [];
    foreach ($data['as'] as $a) {
        $ref = (string)$a['ref'];
        $done = ts801_assignment_status($a, lr80_state($classId, $ref, $lr), (array)($tb[$ref] ?? []))['done'];
        $out[$ref][$done ? 'done' : 'open'] = ($out[$ref][$done ? 'done' : 'open'] ?? 0) + 1;
    }
    foreach ($data['fb'] as $f) $out[(string)$f['ref']]['fb_at'] = max((int)($out[(string)$f['ref']]['fb_at'] ?? 0), (int)$f['at']);
    return $out;
}

function cm801_support_text(array $s): string
{
    $parts = [];
    if (($s['open'] ?? 0) > 0) $parts[] = (int)$s['open'] . ' čeká';
    if (($s['done'] ?? 0) > 0) $parts[] = (int)$s['done'] . ' hotovo';
    if (($s['fb_at'] ?? 0) > 0) $parts[] = 'zpráva ' . date('j. n.', (int)$s['fb_at']);
    return $parts === [] ? '—' : implode(' · ', $parts);
}

/** Tabulka mapy (uvnitř formuláře výběru). */
function cm801_table_html(string $classId, array $m, array $selected, bool $hidden, array $support, bool $pick = true): string
{
    $s = $m['state'];
    $plain = $s['zobrazeni'] === 'tabulka';
    $cap = 'Mapa třídy – ' . mb_strtolower(CM801_SOURCES[$s['zdroj']]) . ': ' . cm801_count(count($m['students']), 'žák', 'žáci', 'žáků') . ' × ' . cm801_count(count($m['columns']), 'sloupec', 'sloupce', 'sloupců')
        . ($s['oblast'] !== '' ? ', oblast ' . ($m['groups'][$s['oblast']] ?? '') : '') . ($s['od'] > 0 ? ', lekce ' . $s['od'] . '–' . $s['do'] : '');
    $html = '<div class="cm801-wrap" role="region" tabindex="0" aria-labelledby="cm801-cap"><table class="cm801-table' . ($plain ? ' is-plain' : '') . ($pick ? '' : ' no-pick') . '"><caption id="cm801-cap">' . e($cap) . '</caption><thead><tr>'
        . ($pick ? '<th scope="col" class="cm801-sel"><span class="c70-sr">Vybrat žáka</span></th>' : '') . cm801_th($classId, $s, 'jmeno', 'Žák') . cm801_th($classId, $s, 'prumer', 'Průměr');
    foreach ($m['columns'] as $id => $c) $html .= cm801_th($classId, $s, (string)$id, cm801_short_label($c['label']), $c['group_label']);
    $html .= '<th scope="col">Od učitele</th></tr></thead><tbody>';
    foreach ($m['students'] as $st) {
        $name = function_exists('tp721_student_label') ? tp721_student_label($st['label'], (int)$st['index'], $hidden) : ($hidden ? 'Žák ' . $st['index'] : $st['label']);
        $cells = $m['cells'][$st['key']] ?? [];
        $avg = cm801_avg($cells);
        $href = cm801_student_url($classId, $st['key']);
        $id = 'cm801-s' . (int)$st['index'];
        $html .= '<tr>' . ($pick ? '<td class="cm801-sel"><label class="cm801-check" for="' . $id . '"><input type="checkbox" id="' . $id . '" name="students[]" value="' . e($st['key']) . '"' . (in_array($st['key'], $selected, true) ? ' checked' : '')
            . ' data-cm801-pick><span class="c70-sr">Vybrat: ' . e($name) . '</span></label></td>' : '') . '<th scope="row"><a href="' . e($href) . '">' . e($name) . '</a></th>'
            . '<td class="cm801-avg" data-label="Průměr">' . ($avg === null ? '—' : $avg . ' %') . '</td>';
        foreach ($m['columns'] as $col => $c) $html .= cm801_cell_html($cells[$col] ?? ['level' => 'none', 'value' => null, 'text' => 'bez dat'], $href, $plain, $c['label']);
        $html .= '<td data-label="Od učitele">' . e(cm801_support_text($support[$st['ref']] ?? [])) . '</td></tr>';
    }
    $html .= '</tbody><tfoot><tr>' . ($pick ? '<td class="cm801-sel"></td>' : '') . '<th scope="row">Třída</th><td></td>';
    foreach (array_keys($m['columns']) as $col) {
        $sum = $m['summary'][$col] ?? [];
        $html .= '<td class="cm801-sum">umí ' . ((int)($sum['strong'] ?? 0) + (int)($sum['ok'] ?? 0)) . ' · učí se ' . (int)($sum['mid'] ?? 0) . ' · pomoc ' . (int)($sum['low'] ?? 0) . ' · bez dat ' . (int)($sum['none'] ?? 0) . '</td>';
    }
    return $html . '<td></td></tr></tfoot></table></div>';
}

/** Akce pod mapou: zpětná vazba a procvičování (jen s oprávněním students.manage). */
function cm801_actions_html(string $classId, int $selectedCount): string
{
    $phrases = ts801_phrases(ts801_teacher_hash());
    $html = '<div class="cm801-actions" id="cm801-akce"><p class="cm801-count" role="status" aria-live="polite" data-cm801-count data-t-one="Vybrán 1 žák" data-t-few="Vybráni {n} žáci" data-t-many="Vybráno {n} žáků" data-t-zero="Zatím nikdo není vybraný – zaškrtněte žáky v mapě.">'
        . e($selectedCount > 0 ? 'Vybráno z minula: ' . $selectedCount . ' (zaškrtnutí v mapě)' : 'Zaškrtněte žáky v mapě, pak zvolte akci.') . '</p>'
        . '<p class="cm801-pickall" hidden data-cm801-pickall><button type="button" class="ds-btn ds-btn--ghost" data-cm801-all="1">Vybrat všechny</button><button type="button" class="ds-btn ds-btn--ghost" data-cm801-all="0">Zrušit výběr</button>'
        . '<button type="button" class="ds-btn ds-btn--ghost" data-cm801-weak>Vybrat, kdo potřebuje pomoc</button></p>';
    $html .= '<fieldset class="ds-card cm801-box"><legend>Rychlá zpětná vazba</legend><p class="ds-help" id="cm801-fb-help">Vyberte frázi, nebo napište vlastní text (můžete frázi upravit). Žák zprávu uvidí v Můj pokrok tak, jak je napsaná. Nepište do ní známky ani citlivé údaje.</p>';
    foreach (TS801_KINDS as $k => $label) {
        $html .= '<div class="cm801-phr" role="group" aria-label="' . e($label) . '"><p class="cm801-phr__h">' . e($label) . '</p>';
        foreach ($phrases[$k] ?? [] as $p) {
            $pid = 'cm801-p-' . preg_replace('/[^a-z0-9_]/', '', (string)$p['id']);
            $html .= '<label class="cm801-radio" for="' . e($pid) . '"><input type="radio" id="' . e($pid) . '" name="fraze" value="' . e((string)$p['id']) . '" data-kind="' . e($k) . '" data-text="' . e((string)$p['t']) . '"> <span>' . e((string)$p['t']) . ($p['own'] ? ' <small>(moje)</small>' : '') . '</span></label>';
        }
        $html .= '</div>';
    }
    $html .= '<div class="ds-field"><label class="ds-label" for="cm801-druh">Druh zprávy</label><select class="ds-input" id="cm801-druh" name="druh">';
    foreach (TS801_KINDS as $k => $label) $html .= '<option value="' . e($k) . '"' . ($k === 'next' ? ' selected' : '') . '>' . e($label) . '</option>';
    $html .= '</select></div><div class="ds-field"><label class="ds-label" for="cm801-text">Text zprávy (nepovinné – jinak se pošle vybraná fráze)</label><textarea class="ds-input" id="cm801-text" name="text" rows="3" maxlength="' . TS801_TEXT_MAX . '" aria-describedby="cm801-fb-help"></textarea></div>'
        . '<button type="submit" name="action" value="ts801_feedback_send" class="ds-btn ds-btn--primary">Poslat zpětnou vazbu</button></fieldset>';
    $html .= '<fieldset class="ds-card cm801-box"><legend>Přidělit procvičování</legend><p class="ds-help" id="cm801-as-help">Žák ho uvidí v Můj pokrok jako Další krok „Od učitele“. Splnění se pozná samo z jeho výsledků.</p>'
        . '<div class="ds-field"><label class="ds-label" for="cm801-polozka">Co procvičit</label><select class="ds-input" id="cm801-polozka" name="polozka" aria-describedby="cm801-as-help"><option value="">Vyberte…</option>';
    foreach (ts801_assign_options($classId) as $group => $items) {
        $html .= '<optgroup label="' . e($group) . '">';
        foreach ($items as $v => $label) $html .= '<option value="' . e($v) . '">' . e($label) . '</option>';
        $html .= '</optgroup>';
    }
    return $html . '</select></div><button type="submit" name="action" value="ts801_assign" class="ds-btn ds-btn--primary">Přidělit procvičování</button></fieldset></div>';
}

/** Moje fráze (přidat / smazat) a odeslané zprávy a přidělení (stáhnout / zrušit). */
function cm801_sent_html(string $classId, string $csrf, array $students, bool $hidden, bool $canSend): string
{
    $label = [];
    foreach ($students as $st) $label[$st['ref']] = function_exists('tp721_student_label') ? tp721_student_label($st['label'], (int)$st['index'], $hidden) : $st['label'];
    $form = static fn(string $action, string $id, string $button, string $who): string => '<form method="post" class="cm801-inline"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="return_tab" value="mapa801"><input type="hidden" name="class_id" value="' . e($classId) . '"><input type="hidden" name="id" value="' . e($id) . '">'
        . '<button type="submit" class="ds-btn ds-btn--ghost">' . e($button) . '<span class="c70-sr"> – ' . e($who) . '</span></button></form>';
    $data = ts801_data($classId);
    $lr = lr80_data($classId);
    $tb = (array)((tb75_data()['classes'] ?? [])[$classId] ?? []);
    $opts = [];
    foreach (ts801_assign_options($classId) as $items) $opts += $items;
    $html = '<section class="ds-card cm801-sent" aria-labelledby="cm801-sent-h"><h2 id="cm801-sent-h">Odesláno a přiděleno</h2>';
    if ($data['fb'] === [] && $data['as'] === []) return $html . '<p class="ds-help" role="status">Zatím jste této třídě nic neposlali. Zaškrtněte žáky v mapě a zvolte akci.</p></section>';
    $html .= '<div class="cm801-sent-cols"><div><h3>Přidělené procvičování</h3><ul class="cm801-list">';
    $as = array_reverse($data['as']);
    foreach (array_slice($as, 0, 30) as $a) {
        $st = ts801_assignment_status($a, lr80_state($classId, (string)$a['ref'], $lr), (array)($tb[(string)$a['ref']] ?? []));
        $who = (string)($label[(string)$a['ref']] ?? 'Žák mimo seznam');
        $what = (string)($opts[$a['k'] . ':' . $a['i']] ?? $a['k']);
        $html .= '<li><strong>' . e($who) . '</strong>: ' . e($what) . ' <span class="ds-badge ' . ($st['done'] ? 'is-ok">hotovo' : 'is-warn">čeká') . '</span>'
            . ($st['total'] > 1 ? ' <small>' . (int)$st['have'] . ' z ' . (int)$st['total'] . '</small>' : '') . ' <small>' . e(date('j. n. Y', (int)$a['at'])) . '</small>'
            . ($canSend && !$st['done'] ? $form('ts801_assign_cancel', (string)$a['id'], 'Zrušit přidělení', $who) : '') . '</li>';
    }
    $html .= '</ul></div><div><h3>Zpětná vazba</h3><ul class="cm801-list">';
    foreach (array_slice(array_reverse($data['fb']), 0, 30) as $f) {
        $who = (string)($label[(string)$f['ref']] ?? 'Žák mimo seznam');
        $html .= '<li><strong>' . e($who) . '</strong> <span class="ds-badge is-info">' . e(TS801_KINDS[(string)$f['k']] ?? '') . '</span> <small>' . e(date('j. n. Y', (int)$f['at'])) . '</small><br>' . nl2br(e((string)$f['t']))
            . ($canSend ? $form('ts801_feedback_withdraw', (string)$f['id'], 'Stáhnout zprávu', $who) : '') . '</li>';
    }
    return $html . '</ul></div></div></section>';
}

/** Moje fráze: přidat a smazat (jen vlastní záznam učitele). */
function cm801_phrases_html(string $classId, string $csrf): string
{
    $own = array_merge(...array_values(array_map(static fn(array $l): array => array_values(array_filter($l, static fn(array $p): bool => $p['own'])), ts801_phrases(ts801_teacher_hash())) ?: [[]]));
    $hid = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="return_tab" value="mapa801"><input type="hidden" name="class_id" value="' . e($classId) . '">';
    $html = '<details class="ds-card cm801-own"><summary>Moje fráze (' . count($own) . ' z ' . TS801_OWN_PHRASES_MAX . ')</summary>';
    if ($own !== []) {
        $html .= '<ul class="cm801-list">';
        foreach ($own as $p) $html .= '<li>' . e((string)$p['t']) . '<form method="post" class="cm801-inline">' . $hid . '<input type="hidden" name="action" value="ts801_phrase_delete"><input type="hidden" name="id" value="' . e((string)$p['id']) . '">'
            . '<button type="submit" class="ds-btn ds-btn--ghost">Smazat<span class="c70-sr"> frázi: ' . e((string)$p['t']) . '</span></button></form></li>';
        $html .= '</ul>';
    }
    $html .= '<form method="post" class="cm801-addphr">' . $hid . '<input type="hidden" name="action" value="ts801_phrase_add"><div class="ds-field"><label class="ds-label" for="cm801-pk">Druh</label><select class="ds-input" id="cm801-pk" name="druh">';
    foreach (TS801_KINDS as $k => $label) $html .= '<option value="' . e($k) . '">' . e($label) . '</option>';
    return $html . '</select></div><div class="ds-field"><label class="ds-label" for="cm801-pt">Nová fráze</label><input class="ds-input" id="cm801-pt" name="text" maxlength="' . TS801_PHRASE_MAX . '" required></div>'
        . '<button type="submit" class="ds-btn ds-btn--secondary">Uložit frázi</button></form></details>';
}

/** Celá záložka Mapa třídy. */
function cm801_render_tab(array $modules, string $classId, string $csrf): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Třída a žáci · mapa</div><h1>Mapa třídy</h1>'
        . '<p>Kdo co umí na jednom místě: žáci × kompetence nebo úlohy. Každá buňka má hodnotu textem, barva jen pomáhá. Zaškrtněte žáky a jedním klikem pošlete zpětnou vazbu nebo přidělte procvičování.</p></div></section>';
    if ($class === '') { echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Mapa se ukáže, jakmile vám administrátor přidělí třídu.', []) : ''; return; }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(cm801_url($cid, [])) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    $m = cm801_build($class, is_array($modules[$class] ?? null) ? $modules[$class] : [], $_GET);
    echo '<div class="ds78 cm801">';
    if ($m['state']['zdroj'] === '') {
        echo function_exists('ux791_empty_html') ? ux791_empty_html('Mapa pro tuto třídu zatím nemá data', 'Třída nemá zapojené kompetence ani úlohy s variantami.', [['?tab=kompetence75&class=' . $class, 'Otevřít Kompetence 2.A a 4.A']]) : '<p role="status">Mapa pro tuto třídu zatím nemá data.</p>';
        echo '</div>';
        return;
    }
    echo cm801_filters_html($class, $m) . cm801_legend_html($m['state']['zdroj']);
    if ($m['lesson_note'] !== '') echo '<p class="ds-help" role="status">' . e($m['lesson_note']) . '</p>';
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $canSend = !function_exists('teacher_permission') || teacher_permission('students.manage');
    if ($m['students'] === [] || $m['columns'] === []) {
        echo function_exists('ux791_empty_html') ? ux791_empty_html($m['students'] === [] ? 'Třída zatím nemá žáky' : 'Filtru nic neodpovídá', $m['students'] === [] ? 'Žáci se objeví po založení přístupů.' : 'Zkuste jinou oblast nebo širší rozsah lekcí.', [[cm801_url($class, ['zdroj' => $m['state']['zdroj']]), 'Zrušit filtr']]) : '';
        echo '</div>';
        return;
    }
    $selected = (array)(($_SESSION['ts801_sel'] ?? [])[$class] ?? []);
    $support = cm801_support_summary($class);
    if ($canSend) echo '<form method="post" class="cm801-form" aria-label="Výběr žáků a akce"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="return_tab" value="mapa801"><input type="hidden" name="class_id" value="' . e($class) . '">';
    echo cm801_table_html($class, $m, $selected, $hidden, $support, $canSend);
    if ($canSend) echo cm801_actions_html($class, count($selected)) . '</form>' . cm801_phrases_html($class, $csrf);
    else echo '<p class="ds-help" role="status">Zpětnou vazbu a procvičování posílají učitelé třídy – asistent mapu jen čte.</p>';
    echo cm801_sent_html($class, $csrf, $m['students'], $hidden, $canSend) . '</div>';
}
