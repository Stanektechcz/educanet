<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/assessment_v66_build.php';

/**
 * EDUCANET v66 · cockpit učitele: záložka „Testy a hodnocení“ (hodnoceni66, vždy česky).
 *
 * Při požadavku (GET) se čtou jen cache z cronu (položková analýza, ranní přehled, návrhy, štítky integrity) – nic se nepočítá.
 * Výjimkou je detail jednoho žáka (?zak=<hash>), který ukáže živý řetězec důkazů. POST akce (a66_*, g66_*) mají exact politiky v
 * teacher_scope_v59.php (třída povinná a v rozsahu, g66_settings jen admin). Zásah z ranního přehledu používá akce p63_assign
 * a p63_assign_student z paths_v63_teacher_views.php. Exporty (GET ?export=items|proposals) jsou lokální CSV (středník, UTF-8 BOM,
 * ochrana proti vložení vzorců); PDF se dělá tiskem z prohlížeče (assets/assessment-v66-print.css).
 */

const A66T_FLAG_LABELS = ['hard' => 'těžká (p < 0,3)', 'easy' => 'snadná (p > 0,9)', 'low_discrimination' => 'slabě rozlišuje (r < 0,2)', 'weak_distractor' => 'slabé distraktory'];
const A66T_INTEGRITY_LABELS = ['speed' => 'velmi rychlé odevzdání', 'match' => 'shoda špatných odpovědí'];

/** @return list<string> třídy v rozsahu přihlášeného učitele */
function a66t_classes(): array
{
    $all = array_map('strval', array_keys(is_array($GLOBALS['modules'] ?? null) ? $GLOBALS['modules'] : []));
    if (!function_exists('teacher59_allowed_class_ids')) return $all;
    return array_values(array_intersect($all, teacher59_allowed_class_ids()));
}

function a66t_can_class(string $classId): bool
{
    return $classId !== '' && in_array($classId, a66t_classes(), true);
}

function a66t_is_admin(): bool
{
    return !function_exists('teacher59_is_admin') || teacher59_is_admin();
}

function a66t_class_label(string $classId): string
{
    return function_exists('teacher_class_label') ? teacher_class_label($classId) : $classId;
}

/** Hash žáka → jméno pro učitele třídy (jména se nikdy neukládají do cache). @return array<string,string> */
function a66t_labels(string $classId): array
{
    $out = [];
    foreach (project_students_for_class($classId) as $key => $student) {
        $hash = g66_key_hash((string)$key);
        if ($hash !== '') $out[$hash] = (string)$student['label'];
    }
    return $out;
}

/** student_id hash (i66_hash) → jméno žáka třídy. @return array<string,string> */
function a66t_integrity_labels(string $classId): array
{
    $out = [];
    foreach (project_students_for_class($classId) as $student) {
        $sid = identity58_id_for_student($classId, (string)$student['label']);
        if ($sid !== null) $out[i66_hash($sid)] = (string)$student['label'];
    }
    return $out;
}

function a66t_form(string $csrf, string $action, string $classId, string $inner, string $class = 'a66-form'): string
{
    return '<form method="post" class="' . e($class) . '"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '">' . $inner . '</form>';
}

// ---------------------------------------------------------------------------
// POST
// ---------------------------------------------------------------------------

function a66t_back(string $classId, array $extra = []): never
{
    if (function_exists('teacher_redirect')) teacher_redirect(['tab' => 'hodnoceni66', 'class' => $classId] + $extra);
    redirect_to('teacher.php?tab=hodnoceni66&class=' . rawurlencode($classId));
}

function a66t_flash(string $message, bool $ok): void
{
    if (function_exists('teacher_flash')) teacher_flash($message, $ok ? 'ok' : 'error');
}

/** @return array{enabled:bool,weights:array<string,int>,thresholds:list<int>} vstup formuláře nastavení */
function a66t_settings_input(): array
{
    $int = static fn(string $name): mixed => is_string($_POST[$name] ?? null) && preg_match('/^\d{1,3}$/', (string)$_POST[$name]) === 1 ? (int)$_POST[$name] : null;
    return ['enabled' => (string)($_POST['enabled'] ?? '') === '1',
        'weights' => ['summative_test' => $int('w_summative_test'), 'mastery' => $int('w_mastery'), 'project_v65' => $int('w_project_v65')],
        'thresholds' => [$int('t1'), $int('t2'), $int('t3'), $int('t4')]];
}

/** POST a66_recompute / a66_set_kind / g66_settings / g66_accept (CSRF ověřil teacher.php, politiku scope guard). */
function a66t_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!a66t_can_class($classId)) throw new RuntimeException('Třída není v rozsahu.');
    if ($action === 'a66_recompute') {
        $r = a66_recompute_class($classId);
        m66_build($classId);
        a66t_flash('Přepočítáno: ' . $r['attempts'] . ' pokusů, ' . $r['flags'] . ' štítků integrity, ' . $r['proposals'] . ' návrhů.', true);
    } elseif ($action === 'a66_set_kind') {
        $ok = a66_set_kind($classId, (string)($_POST['test'] ?? ''), (string)($_POST['kind'] ?? ''), a66_actor_hash());
        a66t_flash($ok ? 'Druh testu je uložený.' : 'Druh testu se nepodařilo uložit (startovní test je vždy formativní).', $ok);
    } elseif ($action === 'g66_settings') {
        $res = g66_settings_save($classId, a66t_settings_input(), a66_actor_hash(), a66t_is_admin());
        a66t_flash($res['ok'] ? 'Nastavení hodnocení je uložené.' : 'Nastavení se neuložilo (váhy musí dávat 100 % a hranice klesat).', $res['ok']);
    } elseif ($action === 'g66_accept') {
        $key = g66_key_for_hash($classId, (string)($_POST['student_hash'] ?? ''));
        $res = $key === null ? ['ok' => false, 'error' => 'student'] : g66_accept($classId, $key, (string)($_POST['proposal_hash'] ?? ''), a66_actor_hash());
        a66t_flash($res['ok'] ? 'Návrh je převzatý, žák uvidí známku.' : 'Návrh se nepodařilo převzít (změnily se podklady nebo je hodnocení vypnuté). Přepočítejte a zkuste to znovu.', $res['ok']);
    } else {
        throw new RuntimeException('Neznámá akce hodnocení.');
    }
    a66t_back($classId);
}

// ---------------------------------------------------------------------------
// Ráno
// ---------------------------------------------------------------------------

/** Text jedné ranní položky. @param array<string,string> $labels */
function a66t_morning_text(array $item, string $classId, array $labels): string
{
    $name = $labels[(string)($item['students'][0] ?? '')] ?? 'Žák';
    $d = (array)$item['detail'];
    if ($item['type'] === 'stuck') {
        $path = function_exists('p63_path_for_class') ? p63_path_for_class($classId, (string)$d['path']) : null;
        $step = $path !== null ? p63_step($path, (string)$d['step']) : null;
        return $name . ': uvízl(a) v kroku „' . (string)($step['title'] ?? $d['step']) . '“ cesty „' . (string)($path['title'] ?? $d['path']) . '“ (' . (int)$d['attempts'] . ' pokusů).';
    }
    if ($item['type'] === 'inactive') return $name . ': ' . ($d['days'] === null ? 'zatím žádná aktivita.' : (int)$d['days'] . ' dní bez aktivity.');
    $subject = comp62_subject_for_class($classId);
    $comp = $subject !== null ? (comp62_competencies($subject)[(string)$d['competency']]['label'] ?? (string)$d['competency']) : (string)$d['competency'];
    return 'Třída nezvládá: ' . $comp . ' (' . (int)$d['n'] . ' z ' . (int)$d['of'] . ' žáků s důkazy je ještě „rozpracováno“).';
}

function a66t_morning_action(array $item, string $classId, string $csrf): string
{
    $action = is_array($item['action'] ?? null) ? $item['action'] : null;
    if ($action === null) return '<span class="a66-note">Bez návrhu zásahu (třída nemá odpovídající cestu).</span>';
    $path = function_exists('p63_path_for_class') ? p63_path_for_class($classId, (string)$action['path']) : null;
    $title = (string)($path['title'] ?? $action['path']);
    $student = $action['kind'] === 'assign_student';
    $inner = '<input type="hidden" name="path" value="' . e((string)$action['path']) . '"><input type="hidden" name="from" value="morning66">'
        . ($student ? '<input type="hidden" name="student_hash" value="' . e((string)($item['students'][0] ?? '')) . '">' : '')
        . '<button type="submit">' . ($student ? 'Přiřadit žákovi cestu „' : 'Přiřadit třídě cestu „') . e($title) . '“</button>';
    return a66t_form($csrf, $student ? 'p63_assign_student' : 'p63_assign', $classId, $inner);
}

function a66t_morning_li(array $item, string $classId, string $csrf, array $labels): string
{
    $kind = ['stuck' => 'Uvízl', 'inactive' => 'Neaktivní', 'weak' => 'Třída'][(string)$item['type']] ?? '';
    return '<li class="a66-card a66-' . e((string)$item['type']) . '"><span class="a66-tag">' . e($kind) . '</span><p>' . e(a66t_morning_text($item, $classId, $labels)) . '</p>' . a66t_morning_action($item, $classId, $csrf) . '</li>';
}

function a66t_section_morning(string $classId, string $csrf, array $labels): string
{
    $cache = m66_read($classId);
    $html = '<section class="a66-section" aria-labelledby="a66-morning"><h3 id="a66-morning">Ráno: co udělat dnes</h3>';
    if ($cache === []) return $html . '<p class="a66-note" role="status">Ranní přehled se připravuje – pro tuto třídu zatím nebyl sestaven. Sestavíte ho hned tlačítkem „Přepočítat analýzu, integritu a návrhy“ níže; jinak ho každé ráno připraví cron (tools/v66_morning_build.php).</p></section>';
    if (m66_is_stale($cache)) $html .= '<p class="a66-note" role="status">Přehled je starší než ' . M66_STALE_HOURS . ' hodin, zkontrolujte cron.</p>';
    $items = array_values(array_filter((array)$cache['items'], 'is_array'));
    if ($items === []) return $html . '<p>Dnes není co řešit.</p></section>';
    $top = array_slice($items, 0, M66_SHOW_MAX);
    $html .= '<ol class="a66-list">' . implode('', array_map(static fn(array $i): string => a66t_morning_li($i, $classId, $csrf, $labels), $top)) . '</ol>';
    $rest = array_slice($items, M66_SHOW_MAX);
    if ($rest !== []) $html .= '<details class="a66-more"><summary>Dalších ' . count($rest) . ' položek</summary><ol class="a66-list" start="' . (M66_SHOW_MAX + 1) . '">'
        . implode('', array_map(static fn(array $i): string => a66t_morning_li($i, $classId, $csrf, $labels), $rest)) . '</ol></details>';
    return $html . '<p class="a66-note">Sestaveno ' . e(date('j. n. Y H:i', (int)$cache['built_at'])) . '. Řazeno podle dopadu.</p></section>';
}

// ---------------------------------------------------------------------------
// Položková analýza, druhy testů
// ---------------------------------------------------------------------------

function a66t_num(mixed $v): string
{
    return $v === null ? '–' : number_format((float)$v, 2, ',', '');
}

function a66t_item_row(string $ref, array $s): string
{
    $flags = implode(', ', array_map(static fn(string $f): string => A66T_FLAG_LABELS[$f] ?? $f, (array)($s['flags'] ?? [])));
    $weak = array_map(static fn(array $d): string => (string)$d['key'], array_filter((array)($s['distractors'] ?? []), static fn(array $d): bool => !empty($d['weak'])));
    return '<tr><th scope="row">' . e($ref) . '</th><td>' . (int)$s['n'] . '</td><td>' . a66t_num($s['p'] ?? null) . '</td><td>' . a66t_num($s['r_pb'] ?? null) . '</td><td>' . a66t_num($s['d'] ?? null)
        . '</td><td>' . e($flags . ($weak !== [] ? ' (možnosti: ' . implode(', ', $weak) . ')' : '')) . '</td></tr>';
}

function a66t_test_table(string $test, array $row): string
{
    $flagged = array_filter((array)$row['items'], static fn(array $s): bool => ($s['status'] ?? '') === 'ok' && (array)$s['flags'] !== []);
    $low = count(array_filter((array)$row['items'], static fn(array $s): bool => ($s['status'] ?? '') === 'low_data'));
    $html = '<h4>' . e(a66_test_label($test)) . ' <small>(' . e($row['kind'] === 'summative' ? 'sumativní' : 'formativní') . ', ' . (int)$row['n'] . ' pokusů)</small></h4>';
    if ($low > 0) $html .= '<p class="a66-note">' . $low . ' položek má málo dat (méně než ' . A66_MIN_ATTEMPTS . ' pokusů), zatím se nehodnotí.</p>';
    if ($flagged === []) return $html . '<p class="a66-note">Žádná položka k revizi.</p>';
    $html .= '<div class="a66-scroll" tabindex="0" role="region" aria-label="Položky k revizi, posuňte vodorovně"><table class="a66-table"><caption class="a66-sr">Položky k revizi: ' . e(a66_test_label($test)) . '</caption>'
        . '<thead><tr><th scope="col">Položka</th><th scope="col">Pokusů</th><th scope="col">p</th><th scope="col">r</th><th scope="col">D</th><th scope="col">K revizi</th></tr></thead><tbody>';
    foreach (array_slice($flagged, 0, 25, true) as $ref => $s) $html .= a66t_item_row((string)$ref, (array)$s);
    return $html . '</tbody></table></div>';
}

function a66t_section_items(string $classId, string $csrf): string
{
    $cache = a66_items_cache($classId);
    $html = '<section class="a66-section" aria-labelledby="a66-items"><h3 id="a66-items">Položková analýza</h3>'
        . '<p class="a66-note">p = podíl správných odpovědí, r = korelace položky s výsledkem ostatních otázek, D = rozdíl horních a dolních 27 % žáků. Slouží k revizi otázek, ne k hodnocení žáků.</p>';
    if ($cache === []) $html .= '<p class="a66-note" role="status">Analýza se připravuje (tools/v66_item_analysis.php z cronu). Můžete ji spustit ručně tlačítkem Přepočítat.</p>';
    else {
        foreach ((array)$cache['tests'] as $test => $row) $html .= a66t_test_table((string)$test, (array)$row);
        $html .= '<p class="a66-note">Spočteno ' . e(date('j. n. Y H:i', (int)$cache['built_at'])) . '.</p>';
    }
    return $html . a66t_form($csrf, 'a66_recompute', $classId, '<button type="submit">Přepočítat analýzu, integritu a návrhy</button>') . '</section>';
}

function a66t_section_kinds(string $classId, string $csrf): string
{
    $options = '';
    foreach (a66_markable_tests($classId) as $t) $options .= '<option value="' . e($t['id']) . '">' . e($t['label']) . ($t['kind'] === 'summative' ? ' (sumativní)' : '') . '</option>';
    $marked = array_keys(array_filter(a66_kinds($classId), static fn(array $k): bool => $k['kind'] === 'summative'));
    $html = '<section class="a66-section" aria-labelledby="a66-kinds"><h3 id="a66-kinds">Druhy testů</h3>'
        . '<p class="a66-note">Formativní test je trénink a do návrhu hodnocení nejde. Sumativní test do návrhu jde, žák v něm vidí správné odpovědi až po odevzdání, test nedává žádné odměny a otázky mají u každého žáka jiné pořadí. Startovní test je vždy formativní.</p>'
        . '<p>Sumativní testy třídy: ' . ($marked === [] ? 'zatím žádný.' : e(implode(', ', array_map('a66_test_label', $marked))) . '.') . '</p>';
    $inner = '<label for="a66-test">Test</label><select id="a66-test" name="test">' . $options . '</select>'
        . '<label for="a66-kind">Druh</label><select id="a66-kind" name="kind"><option value="summative">sumativní</option><option value="formative">formativní</option></select><button type="submit">Uložit druh</button>';
    return $html . a66t_form($csrf, 'a66_set_kind', $classId, $inner) . '</section>';
}
