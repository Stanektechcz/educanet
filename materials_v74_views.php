<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · záložka „Knihovna materiálů“ (modul `materialy74`, sekce Výuka). Cockpit je vždy česky.
 * Přehled materiálů třídy (filtr lekce / stav / hledání), detail s ověřením, připojením k lekci, úpravou a vyřazením,
 * historie verzí s náhledem, rozdílem a obnovou. GET jen ?class= (obecná kontrola rozsahu), ?lesson=, ?stav=, ?q=, ?mat=, ?verze=
 * (žádný GET parametr registru). Formuláře jen s oprávněním content.manage. Každý výstup přes e(), odkazy přes safe_url().
 */

require_once __DIR__ . '/materials_v74.php';

function mat74_can_manage(): bool
{
    return function_exists('teacher_permission') && teacher_permission('content.manage');
}

function mat74_url(string $classId, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'materialy74', 'class' => $classId] + $extra);
}

function mat74_render_tab(array $modules, string $classId, string $csrf): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : MAT74_CLASSES;
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · materiály k lekcím</div><h1>Knihovna materiálů</h1>'
        . '<p>Odkazy k lekcím s ověřením a historií. Každá změna je nová verze – starší verzi můžete kdykoli prohlédnout, porovnat a obnovit. Dnešní hodina ukazuje nejdřív povinné a ověřené materiály, vyřazené nikdy.</p></div></section>';
    if ($class === '') {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Knihovna se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    $data = mat74_data();
    $items = mat74_items($class, $data);
    $lesson = is_string($_GET['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_GET['lesson']) === 1 ? min(MAT74_LESSONS, (int)$_GET['lesson']) : 0;
    $state = is_string($_GET['stav'] ?? null) && in_array((string)$_GET['stav'], ['aktivni', 'neovereno', 'neodpovida', 'vyrazeny', 'vse'], true) ? (string)$_GET['stav'] : 'vse';
    $q = mat74_text($_GET['q'] ?? '', MAT74_SHORT_MAX);
    $sel = is_string($_GET['mat'] ?? null) && isset($items[(string)$_GET['mat']]) ? (string)$_GET['mat'] : '';
    echo mat74_picker_html($classes, $class, $modules);
    $s = mat74_stats($class, null, $data);
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Aktivní materiály', (string)$s['active'], $s['required'] . ' povinných v lekcích', ''],
        ['Ověřeno za 180 dní', $s['active'] > 0 ? $s['pct'] . ' %' : '—', $s['verified'] . ' z ' . $s['active'] . ' (cíl ≥ 90 %)', $s['active'] > 0 && $s['pct'] >= 90 ? 'ok' : ($s['active'] > 0 ? 'warn' : '')],
        ['Čeká na ověření', (string)$s['unverified'], 'neověřené nebo staré ověření', $s['unverified'] > 0 ? 'warn' : 'ok'],
        ['Neodpovídá tématu', (string)$s['mismatch'], 'v Dnešní hodině skryté', ''],
        ['Vyřazeno', (string)$s['retired'], 'jde obnovit ze starší verze', ''],
    ], 'Souhrn knihovny');
    if ($sel !== '') echo mat74_detail_html($class, $items[$sel], $data, $csrf);
    echo mat74_list_html($class, $items, $lesson, $state, $q, $sel);
    echo mat74_add_html($class, $csrf, $items === []);
    echo '<p class="c70-muted">Nahrávání souborů zatím není součástí knihovny (rozhodnutí školy č. 7 je otevřené) – vložte odkaz https na veřejnou stránku. '
        . 'Videa z YouTube se nevkládají do stránky, jen se otevřou odkazem. Historie drží nejvýš ' . MAT74_HISTORY_MAX . ' verzí na materiál (starší než ' . MAT74_HISTORY_DAYS . ' dní se odřezávají).</p>';
}

function mat74_picker_html(array $classes, string $class, array $modules): string
{
    $html = '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) {
        $html .= '<li><a href="' . e(mat74_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    }
    return $html . '</ul></nav>';
}

/** Štítek stavu materiálu (ověřeno / neověřeno / neodpovídá / vyřazeno). */
function mat74_state_chip(array $item): string
{
    $state = (string)($item['state'] ?? 'aktivni');
    if ($state === 'vyrazeny') return '<span class="c70-chip c70-chip-bad">vyřazený</span>';
    if ($state === 'neodpovida') return '<span class="c70-chip c70-chip-warn">neodpovídá tématu</span>';
    if (mat74_verified($item)) return '<span class="c70-chip c70-chip-ok">ověřeno ' . e(date('j. n. Y', (int)strtotime((string)$item['checked_at']))) . '</span>';
    return '<span class="c70-chip c70-chip-warn">' . ((string)($item['checked_at'] ?? '') === '' ? 'neověřeno' : 'ověření starší než 180 dní') . '</span>';
}

function mat74_link_html(string $url, string $title): string
{
    $safe = safe_url($url);
    if ($safe === '' || $safe === '#') return e($title);
    return '<a href="' . e($safe) . '" target="_blank" rel="noopener noreferrer">' . e($title) . '<span class="c70-sr"> (otevře se v novém okně)</span></a>';
}

function mat74_list_html(string $class, array $items, int $lesson, string $state, string $q, string $sel): string
{
    $rows = array_filter($items, static function (array $i) use ($lesson, $state, $q): bool {
        $st = (string)($i['state'] ?? '');
        if ($state === 'aktivni' && $st !== 'aktivni') return false;
        if ($state === 'neovereno' && ($st !== 'aktivni' || mat74_verified($i))) return false;
        if (in_array($state, ['neodpovida', 'vyrazeny'], true) && $st !== $state) return false;
        if ($lesson > 0 && !isset(mat74_lessons_norm($i['lessons'] ?? [])[(string)$lesson])) return false;
        if ($q !== '' && !str_contains(mb_strtolower((string)$i['title'] . ' ' . implode(' ', (array)($i['topics'] ?? []))), mb_strtolower($q))) return false;
        return true;
    });
    uasort($rows, static fn(array $a, array $b): int => strnatcasecmp((string)$a['title'], (string)$b['title']));
    $html = '<section class="c70-card c70-wide" aria-labelledby="mat74-list"><h2 id="mat74-list">Materiály třídy</h2>' . mat74_filter_html($class, $lesson, $state, $q);
    if ($items === []) {
        return $html . '<div class="c70-empty" role="status"><h3>Knihovna třídy je zatím prázdná.</h3><p>Přidejte první materiál formulářem níže. Správce může jednorázově převzít videa učitelů a doporučené zdroje lekcí migrací '
            . '<code>php tools/migrate.php --apply --only=0004_materials_v74</code> (náhled bez změn: <code>--dry-run</code>). Do té doby Dnešní hodina ukazuje doporučené zdroje jako dosud.</p></div></section>';
    }
    if ($rows === []) return $html . '<p class="c70-muted" role="status">Filtru neodpovídá žádný materiál. <a class="c70-link" href="' . e(mat74_url($class)) . '">Zrušit filtr</a></p></section>';
    $html .= '<div class="c72-scroll"><table class="c70-table mat74-table"><caption class="c70-sr">Materiály třídy, ' . count($rows) . ' položek</caption><thead><tr><th scope="col">Materiál</th><th scope="col">Typ</th>'
        . '<th scope="col">Lekce</th><th scope="col">Stav</th><th scope="col">Verze</th></tr></thead><tbody>';
    foreach ($rows as $id => $i) {
        $lessons = array_keys(mat74_lessons_norm($i['lessons'] ?? []));
        $html .= '<tr><th scope="row"><a href="' . e(mat74_url($class, ['mat' => (string)$id]) . '#mat74-detail') . '"' . ((string)$id === $sel ? ' aria-current="true"' : '') . '>' . e((string)$i['title']) . '</a>'
            . ((array)($i['topics'] ?? []) !== [] ? '<small class="mat74-sub">' . e(implode(', ', array_slice((array)$i['topics'], 0, 4))) . '</small>' : '') . '</th>'
            . '<td data-label="Typ">' . e(MAT74_TYPES[(string)$i['type']] ?? (string)$i['type']) . '</td>'
            . '<td data-label="Lekce">' . e($lessons === [] ? 'k tématům' : 'L' . implode(', L', $lessons)) . '</td>'
            . '<td data-label="Stav">' . mat74_state_chip($i) . '</td><td data-label="Verze">' . (int)$i['version'] . '</td></tr>';
    }
    return $html . '</tbody></table></div></section>';
}

function mat74_filter_html(string $class, int $lesson, string $state, string $q): string
{
    $opt = static fn(string $v, string $label, string $cur): string => '<option value="' . e($v) . '"' . ($v === $cur ? ' selected' : '') . '>' . e($label) . '</option>';
    $lessons = $opt('0', 'Všechny lekce', (string)$lesson);
    for ($n = 1; $n <= MAT74_LESSONS; $n++) $lessons .= $opt((string)$n, 'Lekce ' . $n, (string)$lesson);
    $states = $opt('vse', 'Všechny stavy', $state) . $opt('aktivni', 'Aktivní', $state) . $opt('neovereno', 'Čeká na ověření', $state) . $opt('neodpovida', 'Neodpovídá tématu', $state) . $opt('vyrazeny', 'Vyřazené', $state);
    return '<form method="get" action="teacher.php" class="mat74-filter" role="search" aria-label="Filtr materiálů"><input type="hidden" name="tab" value="materialy74"><input type="hidden" name="class" value="' . e($class) . '">'
        . '<label>Lekce<select name="lesson">' . $lessons . '</select></label><label>Stav<select name="stav">' . $states . '</select></label>'
        . '<label>Hledat v názvu a tématech<input type="search" name="q" maxlength="' . MAT74_SHORT_MAX . '" value="' . e($q) . '"></label><button class="btn secondary" type="submit">Filtrovat</button></form>';
}

/** Skryté pole formuláře + společný začátek POST formuláře knihovny. */
function mat74_form_open(string $class, string $action, string $csrf, string $id = '', string $extraClass = ''): string
{
    return '<form method="post" action="teacher.php" class="c70-form mat74-form' . ($extraClass !== '' ? ' ' . e($extraClass) : '') . '"><input type="hidden" name="csrf" value="' . e($csrf) . '">'
        . '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="class_id" value="' . e($class) . '"><input type="hidden" name="return_tab" value="materialy74">'
        . ($id !== '' ? '<input type="hidden" name="material_id" value="' . e($id) . '">' : '');
}

/** Pole formuláře přidání / úpravy (předvyplněné z $f). */
function mat74_fields_html(array $f, string $prefix): string
{
    $sel = static function (string $name, string $label, array $opts, string $cur) use ($prefix): string {
        $h = '<label for="' . e($prefix . $name) . '">' . e($label) . '</label><select id="' . e($prefix . $name) . '" name="' . e($name) . '">';
        foreach ($opts as $v => $l) $h .= '<option value="' . e((string)$v) . '"' . ((string)$v === $cur ? ' selected' : '') . '>' . e((string)$l) . '</option>';
        return '<div class="mat74-field">' . $h . '</select></div>';
    };
    $inp = static function (string $name, string $label, string $value, int $max, string $hint = '', string $type = 'text', bool $req = false) use ($prefix): string {
        return '<div class="mat74-field"><label for="' . e($prefix . $name) . '">' . e($label) . ($req ? ' <span aria-hidden="true">*</span>' : '') . '</label>'
            . '<input id="' . e($prefix . $name) . '" type="' . e($type) . '" name="' . e($name) . '" maxlength="' . $max . '" value="' . e($value) . '"' . ($req ? ' required' : '')
            . ($hint !== '' ? ' aria-describedby="' . e($prefix . $name) . '-h"' : '') . '>' . ($hint !== '' ? '<small id="' . e($prefix . $name) . '-h">' . e($hint) . '</small>' : '') . '</div>';
    };
    return '<div class="mat74-grid">' . $inp('title', 'Název', (string)$f['title'], MAT74_TITLE_MAX, 'Česky a výstižně, např. „DNS záznamy – vizuální vysvětlení“.', 'text', true)
        . $inp('url', 'Odkaz', (string)$f['url'], MAT74_URL_MAX, 'Jen https://…; http:, javascript: ani data: knihovna nepřijme.', 'url', true)
        . $sel('type', 'Typ', MAT74_TYPES, (string)$f['type']) . $sel('lang', 'Jazyk', MAT74_LANGS, (string)$f['lang']) . $sel('audience', 'Publikum', MAT74_AUDIENCES, (string)$f['audience'])
        . $inp('duration', 'Délka', (string)$f['duration'], MAT74_SHORT_MAX, 'Např. 18 min.') . $inp('level', 'Úroveň', (string)$f['level'], MAT74_SHORT_MAX, 'Např. začátečník.')
        . $inp('license', 'Licence', (string)$f['license'], MAT74_LICENSE_MAX, 'Např. CC BY 4.0, „volně dostupné“, „jen odkaz“.')
        . $inp('topics', 'Témata (klíče)', implode(', ', (array)$f['topics']), 400, 'Klíče témat lekce oddělené čárkou, např. dns, dhcp. Podle nich se materiál nabídne v Dnešní hodině.')
        . $inp('competencies', 'Kompetence (id)', implode(', ', (array)$f['competencies']), 300, 'Nepovinné – id z katalogu kompetencí.')
        . '<div class="mat74-field mat74-wide"><label for="' . e($prefix) . 'note">Poznámka</label><textarea id="' . e($prefix) . 'note" name="note" rows="2" maxlength="' . MAT74_NOTE_MAX . '">' . e((string)$f['note']) . '</textarea></div></div>';
}

function mat74_add_html(string $class, string $csrf, bool $open): string
{
    if (!mat74_can_manage()) return '<p class="c70-note" role="status">Máte jen čtení – materiály přidává a upravuje učitel třídy nebo administrátor.</p>';
    return '<details class="c70-card c70-wide mat74-add"' . ($open ? ' open' : '') . '><summary><strong>Přidat materiál</strong> <span class="c70-muted">(vznikne verze 1)</span></summary>'
        . mat74_form_open($class, 'mat74_add', $csrf) . mat74_fields_html(mat74_blank(), 'mat74-add-') . '<button class="btn primary" type="submit">Přidat do knihovny</button></form></details>';
}

function mat74_detail_html(string $class, array $item, array $data, string $csrf): string
{
    $id = (string)$item['id'];
    $html = '<section class="c70-card c70-wide mat74-detail" id="mat74-detail" aria-labelledby="mat74-detail-h"><h2 id="mat74-detail-h">' . e((string)$item['title']) . '</h2>'
        . '<p>' . mat74_state_chip($item) . ' <span class="c70-chip">' . e(MAT74_TYPES[(string)$item['type']] ?? '') . '</span> <span class="c70-chip">verze ' . (int)$item['version'] . '</span></p>'
        . '<p class="c70-muted">Naposledy: ' . e((string)$item['change']) . ' · ' . e(date('j. n. Y H:i', (int)strtotime((string)$item['at']))) . ' · ' . e((string)$item['by']) . '</p><dl class="mat74-dl">';
    $html .= '<dt>Odkaz</dt><dd>' . mat74_link_html((string)$item['url'], (string)$item['url']) . '</dd>';
    foreach (['lang', 'duration', 'level', 'audience', 'license', 'topics', 'competencies', 'lessons', 'checked_at', 'note'] as $f) {
        $html .= '<dt>' . e(MAT74_FIELD_LABELS[$f]) . '</dt><dd>' . e(mat74_value_text($f, $item[$f] ?? '') . ($f === 'checked_at' && (string)($item['checked_by'] ?? '') !== '' ? ' (' . $item['checked_by'] . ')' : '')) . '</dd>';
    }
    if ((string)$item['state'] === 'vyrazeny') $html .= '<dt>Důvod vyřazení</dt><dd>' . e((string)$item['retired_reason']) . '</dd>';
    $html .= '</dl>' . (mat74_can_manage() ? mat74_actions_html($class, $item, $csrf) : '<p class="c70-note" role="status">Máte jen čtení.</p>');
    $html .= mat74_history_html($class, $item, $data, $csrf);
    return $html . '<p><a class="c70-link" href="' . e(mat74_url($class)) . '">Zavřít detail</a></p></section>';
}

function mat74_actions_html(string $class, array $item, string $csrf): string
{
    $id = (string)$item['id'];
    $retired = (string)$item['state'] === 'vyrazeny';
    $html = '<div class="mat74-actions">';
    if (!$retired) {
        $html .= mat74_form_open($class, 'mat74_verify', $csrf, $id, 'mat74-inline') . '<p class="mat74-label">Ověření odkazu (otevřete ho a zkontrolujte obsah):</p>'
            . '<button class="btn primary" type="submit" name="result" value="ok">Funguje a sedí k tématu</button> <button class="btn secondary" type="submit" name="result" value="mismatch">Neodpovídá tématu</button></form>';
        $lessonOpts = '';
        for ($n = 1; $n <= MAT74_LESSONS; $n++) $lessonOpts .= '<option value="' . $n . '">Lekce ' . $n . '</option>';
        $roleOpts = '';
        foreach (MAT74_ROLES as $k => $l) $roleOpts .= '<option value="' . e($k) . '">' . e($l) . '</option>';
        $html .= mat74_form_open($class, 'mat74_attach', $csrf, $id, 'mat74-inline') . '<p class="mat74-label">Připojit k lekci:</p><div class="mat74-row">'
            . '<label>Lekce<select name="lesson">' . $lessonOpts . '</select></label><label>Role<select name="role">' . $roleOpts . '</select></label>'
            . '<label class="mat74-check"><input type="checkbox" name="required" value="1"> povinný</label><button class="btn secondary" type="submit">Připojit</button></div></form>';
        foreach (mat74_lessons_norm($item['lessons'] ?? []) as $n => $row) {
            $html .= mat74_form_open($class, 'mat74_attach', $csrf, $id, 'mat74-inline') . '<input type="hidden" name="lesson" value="' . (int)$n . '"><input type="hidden" name="detach" value="1">'
                . '<span>Lekce ' . (int)$n . ' · ' . e(MAT74_ROLES[$row['role']] ?? '') . ($row['required'] ? ' · povinný' : '') . '</span> <button class="btn secondary small" type="submit">Odpojit od lekce ' . (int)$n . '</button></form>';
        }
        $html .= '<details class="mat74-sub-form"><summary>Upravit popis a odkaz</summary>' . mat74_form_open($class, 'mat74_update', $csrf, $id)
            . '<input type="hidden" name="version" value="' . (int)$item['version'] . '">' . mat74_fields_html($item, 'mat74-ed-') . '<button class="btn primary" type="submit">Uložit jako verzi ' . ((int)$item['version'] + 1) . '</button></form></details>';
        $html .= '<details class="mat74-sub-form"><summary>Vyřadit materiál</summary>' . mat74_form_open($class, 'mat74_retire', $csrf, $id)
            . '<label for="mat74-reason">Proč (nejvýš ' . MAT74_REASON_MAX . ' znaků)</label><textarea id="mat74-reason" name="reason" rows="2" maxlength="' . MAT74_REASON_MAX . '" required></textarea>'
            . '<p class="c70-muted">Vyřazený materiál zmizí z Dnešní hodiny. Vrátit ho jde obnovením starší verze v historii.</p><button class="btn secondary" type="submit">Vyřadit</button></form></details>';
    } else {
        $html .= '<p class="c70-note" role="status">Materiál je vyřazený. Pokud ho chcete vrátit, obnovte v historii verzi před vyřazením.</p>';
    }
    return $html . '</div>';
}

function mat74_history_html(string $class, array $item, array $data, string $csrf): string
{
    $id = (string)$item['id'];
    $hist = mat74_history($id, $data);
    $pick = is_string($_GET['verze'] ?? null) && preg_match('/^\d{1,4}$/', (string)$_GET['verze']) === 1 ? (int)$_GET['verze'] : 0;
    $html = '<h3>Historie verzí (' . count($hist) . ')</h3><div class="c72-scroll"><table class="c70-table mat74-table"><caption class="c70-sr">Verze materiálu od nejnovější</caption>'
        . '<thead><tr><th scope="col">Verze</th><th scope="col">Kdy</th><th scope="col">Kdo</th><th scope="col">Změna</th><th scope="col">Náhled</th></tr></thead><tbody>';
    foreach (array_reverse($hist) as $v) {
        $n = (int)$v['version'];
        $html .= '<tr' . ($n === $pick ? ' class="is-selected"' : '') . '><th scope="row">' . $n . ($n === (int)$item['version'] ? ' <span class="c70-chip">aktuální</span>' : '') . '</th>'
            . '<td data-label="Kdy">' . e(date('j. n. Y H:i', (int)strtotime((string)$v['at']))) . '</td><td data-label="Kdo">' . e((string)$v['by']) . '</td><td data-label="Změna">' . e((string)$v['change']) . '</td>'
            . '<td data-label="Náhled"><a class="c70-link" href="' . e(mat74_url($class, ['mat' => $id, 'verze' => (string)$n]) . '#mat74-ver') . '">Náhled a rozdíl<span class="c70-sr"> verze ' . $n . '</span></a></td></tr>';
    }
    $html .= '</tbody></table></div>';
    $sel = null;
    $prev = null;
    foreach ($hist as $i => $v) if ((int)$v['version'] === $pick) { $sel = $v; $prev = $hist[$i - 1] ?? null; }
    if ($pick > 0 && $sel === null) return $html . '<p class="c70-note" role="status">Verze ' . $pick . ' už v historii není.</p>';
    if ($sel === null) return $html . '<p class="c70-muted">Vyberte verzi a uvidíte její obsah, rozdíl proti předchozí a proti aktuální verzi.</p>';
    $html .= '<section class="mat74-version" id="mat74-ver" aria-labelledby="mat74-ver-h"><h3 id="mat74-ver-h">Náhled verze ' . $pick . '</h3><dl class="mat74-dl">';
    foreach (MAT74_FIELDS as $f) $html .= '<dt>' . e(MAT74_FIELD_LABELS[$f]) . '</dt><dd>' . e(mat74_value_text($f, $sel[$f] ?? '')) . '</dd>';
    $html .= '</dl>' . mat74_diff_html('Co se změnilo proti verzi ' . ($prev !== null ? (int)$prev['version'] : '—'), $prev !== null ? mat74_diff($prev, $sel) : null);
    if ($pick !== (int)$item['version']) {
        $html .= mat74_diff_html('Rozdíl proti aktuální verzi ' . (int)$item['version'] . ' (co obnovení vrátí)', mat74_diff($item, $sel));
        if (mat74_can_manage()) $html .= mat74_form_open($class, 'mat74_restore', $csrf, $id) . '<input type="hidden" name="restore_version" value="' . $pick . '">'
            . '<button class="btn primary" type="submit">Obnovit verzi ' . $pick . ' (uloží se jako verze ' . ((int)$item['version'] + 1) . ')</button></form>';
    }
    return $html . '</section>';
}

/** Tabulka rozdílu (null = první verze). */
function mat74_diff_html(string $title, ?array $diff): string
{
    $html = '<h4>' . e($title) . '</h4>';
    if ($diff === null) return $html . '<p class="c70-muted">První verze – není s čím porovnat.</p>';
    if ($diff === []) return $html . '<p class="c70-muted">Beze změny obsahu.</p>';
    $html .= '<table class="c70-table mat74-diff"><caption class="c70-sr">' . e($title) . '</caption><thead><tr><th scope="col">Pole</th><th scope="col">Před</th><th scope="col">Po</th></tr></thead><tbody>';
    foreach ($diff as [, $label, $from, $to]) $html .= '<tr><th scope="row">' . e($label) . '</th><td data-label="Před"><del>' . e($from) . '</del></td><td data-label="Po"><ins>' . e($to) . '</ins></td></tr>';
    return $html . '</tbody></table>';
}

/**
 * Karta „Materiály k lekci“ v Dnešní hodině, když třída má knihovnu (jinak zůstává karta v70 se zdroji learning_resources).
 * Nejdřív povinné a ověřené, nikdy vyřazené ani „neodpovídá tématu“.
 */
function mat74_today_html(array $m): string
{
    $class = (string)$m['class'];
    $n = (int)$m['lesson']['number'];
    $rows = mat74_for_lesson($class, $n, array_column((array)$m['lesson']['topics'], 'key'));
    $html = '<section class="c70-card" aria-labelledby="c70-mat"><h2 id="c70-mat">Materiály k lekci</h2>';
    if (function_exists('lt70_tab_link')) {
        $links = array_filter([lt70_tab_link('teach', $class, 'Promítnout lekci (Režim hodiny)', ['lesson' => $n], 'btn secondary'), lt70_tab_link('curriculum', $class, 'Příprava v Plánu a kurikulu', [], 'btn secondary')]);
        if ($links !== []) $html .= '<div class="c70-actions">' . implode('', $links) . '</div>';
    }
    $libUrl = mat74_url($class, ['lesson' => (string)$n]);
    if ($rows === []) {
        return $html . '<p class="c70-muted">K lekci ' . $n . ' ani jejím tématům zatím není v knihovně žádný aktivní materiál.</p>'
            . '<p><a class="c70-link" href="' . e($libUrl) . '">Přidat nebo připojit materiál v knihovně →</a></p></section>';
    }
    $html .= '<ul class="c70-materials">';
    foreach (array_slice($rows, 0, 8) as $r) {
        $meta = array_filter([$r['required'] ? 'povinný' : '', MAT74_ROLES[$r['role']] ?? '', $r['verified'] ? 'ověřeno ' . date('j. n. Y', (int)strtotime((string)$r['checked_at'])) : 'neověřeno',
            (string)$r['lang'], (string)$r['duration']]);
        $html .= '<li><span class="c70-chip' . ($r['required'] ? ' c70-chip-accent' : '') . '">' . e(MAT74_TYPES[(string)$r['type']] ?? 'Odkaz') . '</span>' . mat74_link_html((string)$r['url'], (string)$r['title'])
            . '<small>' . e(implode(' · ', $meta)) . '</small></li>';
    }
    return $html . '</ul><p><a class="c70-link" href="' . e($libUrl) . '">Knihovna materiálů lekce ' . $n . ' (ověření, verze) →</a></p></section>';
}
