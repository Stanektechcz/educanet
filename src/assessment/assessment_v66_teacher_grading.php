<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/assessment_v66_teacher_views.php';

/**
 * EDUCANET v66 · cockpit: návrhy hodnocení, integrita, exporty CSV a vykreslení záložky „Testy a hodnocení“ (vždy česky).
 * Navazuje na assessment_v66_teacher_views.php (POST, ráno, položková analýza, druhy testů). Cockpit čte jen cache;
 * detail žáka (?zak=<hash>) ukáže živý řetězec důkazů, který vidí i žák.
 */

function a66t_pct(mixed $v): string
{
    return $v === null ? '–' : number_format((float)$v, 0, ',', '') . ' %';
}

function a66t_settings_form(string $classId, string $csrf): string
{
    $s = g66_settings($classId);
    $num = static fn(string $id, string $label, int $value): string => '<label for="' . $id . '">' . e($label) . '</label><input id="' . $id . '" name="' . $id . '" type="number" min="0" max="100" inputmode="numeric" value="' . $value . '" required>';
    $inner = '<label class="a66-check"><input type="checkbox" name="enabled" value="1"' . ($s['enabled'] ? ' checked' : '') . '> Zapnout návrh hodnocení v této třídě</label>'
        . '<fieldset><legend>Váhy zdrojů (součet musí být 100 %)</legend>' . $num('w_summative_test', 'Sumativní testy (%)', $s['weights']['summative_test']) . $num('w_mastery', 'Zvládnutí kompetencí (%)', $s['weights']['mastery']) . $num('w_project_v65', 'Projekty (%)', $s['weights']['project_v65']) . '</fieldset>'
        . '<fieldset><legend>Hranice známek (minimum v %)</legend>' . $num('t1', 'Známka 1 od', $s['thresholds'][0]) . $num('t2', 'Známka 2 od', $s['thresholds'][1]) . $num('t3', 'Známka 3 od', $s['thresholds'][2]) . $num('t4', 'Známka 4 od', $s['thresholds'][3]) . '</fieldset>'
        . '<button type="submit">Uložit nastavení</button>';
    return '<details class="a66-more"><summary>Nastavení návrhu hodnocení (jen administrátor)</summary>' . a66t_form($csrf, 'g66_settings', $classId, $inner) . '</details>';
}

/** Převzatá známka žáků třídy podle hashe. @return array<string,int> */
function a66t_accepted_map(string $classId): array
{
    $out = [];
    foreach (array_keys(project_students_for_class($classId)) as $key) {
        $sid = g66_student_id($classId, (string)$key);
        $acc = $sid !== null ? g66_accepted($classId, $sid) : null;
        if ($acc !== null) $out[g66_key_hash((string)$key)] = $acc['grade'];
    }
    return $out;
}

function a66t_proposal_row(string $hash, array $row, array $labels, array $accepted, string $classId, string $csrf): string
{
    $name = $labels[$hash] ?? 'Žák';
    $parts = (array)$row['parts'];
    $grade = $row['grade'] === null ? 'málo podkladů' : (string)(int)$row['grade'];
    $accept = '';
    if (($row['status'] ?? '') === 'ok' && ($accepted[$hash] ?? null) !== (int)$row['grade']) {
        $accept = a66t_form($csrf, 'g66_accept', $classId, '<input type="hidden" name="student_hash" value="' . e($hash) . '"><input type="hidden" name="proposal_hash" value="' . e((string)$row['ph']) . '"><button type="submit">Převzít návrh</button>');
    }
    return '<tr><th scope="row">' . e($name) . '</th><td>' . e($grade) . '</td><td>' . e(a66t_pct($row['percent'])) . '</td><td>' . e(a66t_pct($parts['summative_test'] ?? null)) . '</td><td>' . e(a66t_pct($parts['mastery'] ?? null))
        . '</td><td>' . e(a66t_pct($parts['project_v65'] ?? null)) . '</td><td>' . (isset($accepted[$hash]) ? 'převzato: ' . (int)$accepted[$hash] : 'nepřevzato') . '</td><td><a href="?tab=hodnoceni66&amp;class=' . e(rawurlencode($classId)) . '&amp;zak=' . e($hash) . '">Řetězec důkazů</a>' . $accept . '</td></tr>';
}

function a66t_section_grading(string $classId, string $csrf, array $labels): string
{
    $s = g66_settings($classId);
    $html = '<section class="a66-section" aria-labelledby="a66-grading"><h3 id="a66-grading">Návrh hodnocení</h3>'
        . '<p class="a66-note">Návrh (známka 1–5 se slovním hodnocením) je jen podklad, rozhoduje učitel. Počítá se ze sumativních testů, zvládnutí kompetencí a projektů; hry, aréna ani odměny se nepočítají. Žák vidí řetězec důkazů, známku až po převzetí.</p>';
    if (!$s['enabled']) {
        $html .= '<p role="status">Návrh hodnocení je v této třídě vypnutý. Zapnout ho může jen administrátor.</p>';
        return $html . (a66t_is_admin() ? a66t_settings_form($classId, $csrf) : '') . '</section>';
    }
    $cache = g66_class_cache($classId);
    $html .= '<p>Váhy: sumativní testy ' . (int)$s['weights']['summative_test'] . ' %, kompetence ' . (int)$s['weights']['mastery'] . ' %, projekty ' . (int)$s['weights']['project_v65'] . ' %. Hranice: ' . implode(' / ', $s['thresholds']) . ' %. Vzorec ' . e(g66_formula_hash(g66_formula($classId))) . '.</p>';
    if ($cache === [] || !($cache['enabled'] ?? false)) $html .= '<p class="a66-note" role="status">Návrhy se připravují (tools/v66_item_analysis.php z cronu, nebo tlačítko Přepočítat v analýze).</p>';
    else {
        $accepted = a66t_accepted_map($classId);
        $html .= '<div class="a66-scroll" tabindex="0" role="region" aria-label="Návrhy hodnocení, posuňte vodorovně"><table class="a66-table"><caption class="a66-sr">Návrhy hodnocení žáků třídy</caption><thead><tr><th scope="col">Žák</th><th scope="col">Návrh</th><th scope="col">Celkem</th><th scope="col">Testy</th><th scope="col">Kompetence</th><th scope="col">Projekty</th><th scope="col">Stav</th><th scope="col">Akce</th></tr></thead><tbody>';
        foreach ((array)$cache['rows'] as $hash => $row) $html .= a66t_proposal_row((string)$hash, (array)$row, $labels, $accepted, $classId, $csrf);
        $html .= '</tbody></table></div><p class="a66-note">Spočteno ' . e(date('j. n. Y H:i', (int)$cache['built_at'])) . '.</p>';
    }
    return $html . (a66t_is_admin() ? a66t_settings_form($classId, $csrf) : '') . '</section>';
}

// ---------------------------------------------------------------------------
// Integrita
// ---------------------------------------------------------------------------

function a66t_section_integrity(string $classId): string
{
    $flags = i66_flags($classId);
    $html = '<section class="a66-section" aria-labelledby="a66-integrity"><h3 id="a66-integrity">K ověření</h3>'
        . '<p class="a66-note">Upozornění na velmi rychlé nebo podezřele shodné odevzdání. Je to jen důvod si práci ověřit rozhovorem; skóre se nemění, žák upozornění nevidí a nikdy z něj neplyne trest. Záznamy se mažou po skončení školního roku.</p>';
    if ($flags === []) return $html . '<p>Nic k ověření.</p></section>';
    $labels = a66t_integrity_labels($classId);
    $html .= '<ul class="a66-flags">';
    foreach (array_slice($flags, 0, 30) as $f) {
        $html .= '<li>' . e($labels[(string)$f['sid_hash']] ?? 'Neznámý žák') . ' · ' . e(a66_test_label((string)$f['test'])) . ' · ' . e(A66T_INTEGRITY_LABELS[(string)$f['kind']] ?? (string)$f['kind']) . ((int)$f['at'] > 0 ? ' · ' . e(date('j. n. Y', (int)$f['at'])) : '') . '</li>';
    }
    return $html . '</ul></section>';
}

// ---------------------------------------------------------------------------
// Detail žáka (živý řetězec důkazů)
// ---------------------------------------------------------------------------

function a66t_chain_li(array $c): string
{
    $score = number_format((float)$c['score'] * 100, 0, ',', '') . ' %';
    $ev = '';
    foreach ((array)($c['evidence'] ?? []) as $e) $ev .= '<li>' . e((string)$e['ref']) . ' · ' . e((string)$e['source']) . ' · ' . e(number_format((float)$e['score'] * 100, 0, ',', '')) . ' %</li>';
    return '<li><strong>' . e((string)$c['label']) . '</strong> <span class="a66-tag">' . e(['summative_test' => 'sumativní test', 'mastery' => 'kompetence', 'project_v65' => 'projekt'][(string)$c['source']] ?? '') . '</span> ' . e($score)
        . (isset($c['state']) ? ' · ' . e(m62_state_label((string)$c['state'])) : '') . ($ev !== '' ? '<ul>' . $ev . '</ul>' : '') . '</li>';
}

function a66t_render_detail(string $classId, string $hash, string $csrf): void
{
    $key = g66_key_for_hash($classId, $hash);
    $labels = a66t_labels($classId);
    $back = '<p><a href="?tab=hodnoceni66&amp;class=' . e(rawurlencode($classId)) . '">Zpět na přehled</a></p>';
    $p = $key === null ? null : g66_proposal($classId, $key);
    if ($key === null || $p === null) {
        echo $back . '<p class="teacher-empty">Žák nebyl nalezen nebo je hodnocení ve třídě vypnuté.</p>';
        return;
    }
    echo $back . '<section class="a66-section"><h3>Řetězec důkazů: ' . e($labels[$hash] ?? 'Žák') . '</h3>';
    if ($p['status'] !== 'ok') {
        echo '<p>Zatím není dost podkladů pro návrh.</p></section>';
        return;
    }
    echo '<p><strong>Návrh: ' . (int)$p['grade'] . ' (' . e($p['verbal']) . ')</strong> · ' . e(number_format((float)$p['percent'], 1, ',', '')) . ' % · vzorec ' . e($p['formula_hash']) . '</p><ul class="a66-chain">'
        . implode('', array_map('a66t_chain_li', $p['chain'])) . '</ul>'
        . a66t_form($csrf, 'g66_accept', $classId, '<input type="hidden" name="student_hash" value="' . e($hash) . '"><input type="hidden" name="proposal_hash" value="' . e($p['proposal_hash']) . '"><button type="submit">Převzít návrh</button>')
        . '<p class="a66-note">Žák vidí tytéž důkazy; známku uvidí až po převzetí.</p></section>';
}

// ---------------------------------------------------------------------------
// Exporty
// ---------------------------------------------------------------------------

/** Buňka CSV: čísla jako čísla (desetinná čárka), texty s ochranou proti vložení vzorců (= + - @ tabulátor / CR na začátku). */
function a66t_csv_cell(mixed $value): string
{
    if (is_int($value) || is_float($value)) return str_replace('.', ',', (string)round((float)$value, 3));
    $text = str_replace(["\r\n", "\r", "\n"], ' ', (string)$value);
    $first = $text !== '' ? $text[0] : '';
    return in_array($first, ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $text : $text;
}

/** @param list<list<mixed>> $rows Tělo CSV: UTF-8 BOM, oddělovač „;“. */
function a66t_csv_body(array $rows): string
{
    $fp = fopen('php://temp', 'r+');
    fwrite($fp, "\xEF\xBB\xBF");
    foreach ($rows as $row) fputcsv($fp, array_map('a66t_csv_cell', $row), ';', '"', '');
    rewind($fp);
    $body = (string)stream_get_contents($fp);
    fclose($fp);
    return $body;
}

/** @return list<list<mixed>> řádky exportu položkové analýzy (jen z cache, bez jmen) */
function a66t_items_rows(string $classId): array
{
    $rows = [['Třída', 'Test', 'Druh', 'Položka', 'Kompetence', 'Obtížnost 1-3', 'Pokusů', 'p', 'r', 'D', 'Příznaky', 'Slabé možnosti']];
    foreach ((array)(a66_items_cache($classId)['tests'] ?? []) as $test => $row) {
        foreach ((array)$row['items'] as $ref => $s) {
            $weak = array_map(static fn(array $d): string => (string)$d['key'], array_filter((array)($s['distractors'] ?? []), static fn(array $d): bool => !empty($d['weak'])));
            $rows[] = [$classId, (string)$test, (string)$row['kind'], (string)$ref, (string)($s['competency'] ?? ''), $s['difficulty'] ?? '', (int)$s['n'], $s['p'] ?? 'málo dat', $s['r_pb'] ?? '', $s['d'] ?? '',
                implode(' ', (array)($s['flags'] ?? [])), implode(' ', $weak)];
        }
    }
    return $rows;
}

/** @return list<list<mixed>> řádky exportu návrhů (jména žáků jen pro učitele třídy v rozsahu) */
function a66t_proposal_rows(string $classId): array
{
    $rows = [['Třída', 'Žák', 'Návrh známky', 'Celkem %', 'Sumativní testy %', 'Zvládnutí kompetencí %', 'Projekty %', 'Převzatá známka', 'Vzorec']];
    $cache = g66_class_cache($classId);
    if (!($cache['enabled'] ?? false)) return $rows;
    $labels = a66t_labels($classId);
    $accepted = a66t_accepted_map($classId);
    foreach ((array)$cache['rows'] as $hash => $r) {
        $parts = (array)$r['parts'];
        $rows[] = [$classId, $labels[(string)$hash] ?? '', $r['grade'] ?? 'málo podkladů', $r['percent'] ?? '', $parts['summative_test'] ?? '', $parts['mastery'] ?? '', $parts['project_v65'] ?? '', $accepted[(string)$hash] ?? '', (string)$cache['formula_hash']];
    }
    return $rows;
}

/** GET ?tab=hodnoceni66&export=items|proposals&class=… (politika hodnoceni66|export: třída povinná a v rozsahu). */
function a66t_export_csv(string $classId, string $kind): never
{
    if (!a66t_can_class($classId) || !in_array($kind, ['items', 'proposals'], true)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Tuto třídu nebo export nelze zobrazit.');
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . ($kind === 'items' ? 'polozky' : 'navrhy') . '-' . a66_class_safe(substr($classId, 6)) . '-' . date('Ymd') . '.csv"');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo a66t_csv_body($kind === 'items' ? a66t_items_rows($classId) : a66t_proposal_rows($classId));
    exit;
}

function a66t_section_exports(string $classId): string
{
    $base = '?tab=hodnoceni66&amp;class=' . e(rawurlencode($classId));
    return '<section class="a66-section a66-noprint" aria-labelledby="a66-export"><h3 id="a66-export">Exporty</h3><p class="a66-note">Soubory se stahují jen do vašeho počítače (CSV se středníkem, UTF-8). PDF vytvoříte tiskem stránky z prohlížeče.</p>'
        . '<p><a class="a66-btn" href="' . $base . '&amp;export=items">Položková analýza (CSV)</a> <a class="a66-btn" href="' . $base . '&amp;export=proposals">Návrhy hodnocení (CSV)</a> <button type="button" class="a66-btn" data-a66-print>Tisk / PDF</button></p></section>';
}

// ---------------------------------------------------------------------------
// Záložka
// ---------------------------------------------------------------------------

function a66t_render_tab(string $requestedClass, string $csrf): void
{
    $classes = a66t_classes();
    $print = 'assets/assessment-v66-print.css';
    echo '<link rel="stylesheet" href="' . e($print . '?v=' . (is_file(__DIR__ . '/' . $print) ? (string)filemtime(__DIR__ . '/' . $print) : '66')) . '" media="print">';
    echo '<section class="teacher-panel a66-wrap"><h2>Testy a hodnocení</h2>';
    if ($classes === []) {
        echo '<p class="teacher-empty">Nemáte přiřazenou žádnou třídu.</p></section>';
        return;
    }
    $classId = in_array($requestedClass, $classes, true) ? $requestedClass : $classes[0];
    $links = '';
    foreach ($classes as $c) $links .= '<a class="' . ($c === $classId ? 'active' : '') . '" href="?tab=hodnoceni66&amp;class=' . e(rawurlencode($c)) . '"' . ($c === $classId ? ' aria-current="page"' : '') . '>' . e(a66t_class_label($c)) . '</a> ';
    echo '<nav class="a66-classes a66-noprint" aria-label="Třída">' . $links . '</nav>';
    $zak = is_string($_GET['zak'] ?? null) ? (string)$_GET['zak'] : '';
    if ($zak !== '') {
        a66t_render_detail($classId, $zak, $csrf);
        echo '</section>';
        return;
    }
    $labels = a66t_labels($classId);
    echo a66t_section_morning($classId, $csrf, $labels) . a66t_section_items($classId, $csrf) . a66t_section_kinds($classId, $csrf)
        . a66t_section_grading($classId, $csrf, $labels) . a66t_section_integrity($classId) . a66t_section_exports($classId) . '</section>';
}
