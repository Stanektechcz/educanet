<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · záložka „Reflexe hodiny“ (`reflexe73`) a karta „Minule“ v Dnešní hodině. Každý výstup přes e(); jediný formulář
 * je lr73_save s CSRF (asistent bez content.manage formulář nedostane). GET jen obecné ?class= a ?lesson=. Texty česky.
 */

function lr73_state_label(string $state): string
{
    return LR73_STATES[$state] ?? 'neuvedeno';
}

/** Volby (checkboxy) rychlých odpovědí. */
function lr73_picks_html(string $name, array $options, string $legend): string
{
    $html = '<fieldset class="c70-fieldset lr73-picks"><legend>' . e($legend) . '</legend>';
    foreach ($options as $key => $label) {
        $id = 'lr73-' . $name . '-' . $key;
        $html .= '<label class="lr73-pick" for="' . e($id) . '"><input type="checkbox" id="' . e($id) . '" name="' . e($name) . '[]" value="' . e((string)$key) . '"> ' . e((string)$label) . '</label>';
    }
    return $html . '</fieldset>';
}

function lr73_textarea_html(string $name, string $label, string $hint): string
{
    return '<label class="lr73-field" for="lr73-' . e($name) . '"><span>' . e($label) . '</span><textarea id="lr73-' . e($name) . '" name="' . e($name) . '" maxlength="' . LR73_TEXT_MAX
        . '" rows="2" aria-describedby="lr73-privacy">' . '</textarea><small>' . e($hint) . ' Nejvýš ' . LR73_TEXT_MAX . ' znaků.</small></label>';
}

/** Formulář reflexe jedné lekce. */
function lr73_form_html(string $classId, array $lesson, string $date, string $csrf): string
{
    $html = '<form method="post" action="teacher.php" class="c70-form lr73-form"><input type="hidden" name="csrf" value="' . e($csrf) . '">'
        . '<input type="hidden" name="action" value="lr73_save"><input type="hidden" name="return_tab" value="reflexe73">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '"><input type="hidden" name="lesson" value="' . (int)$lesson['number'] . '">'
        . '<fieldset class="c70-fieldset lr73-state"><legend>Jak hodina proběhla? <span class="c70-muted">(povinné, stačí jedno klepnutí)</span></legend>';
    foreach (LR73_STATES as $key => $label) {
        $html .= '<label class="lr73-choice" for="lr73-state-' . $key . '"><input type="radio" id="lr73-state-' . $key . '" name="state" value="' . $key . '" required> ' . e($label) . '</label>';
    }
    $html .= '</fieldset><div class="lr73-row"><label class="lr73-field" for="lr73-stop-seg"><span>Kde jsme skončili – úsek plánu</span><select id="lr73-stop-seg" name="stop_seg"><option value="">— nevyplněno —</option>';
    foreach (array_values($lesson['timeline']) as $i => $s) $html .= '<option value="' . $i . '">' . (int)$s['from'] . '–' . (int)$s['to'] . ' min · ' . e(mb_strimwidth((string)$s['phase'], 0, 60, '…')) . '</option>';
    $html .= '</select></label><label class="lr73-field" for="lr73-stop-task"><span>… nebo úkol</span><select id="lr73-stop-task" name="stop_task"><option value="">— nevyplněno —</option>';
    foreach (array_values($lesson['tasks']) as $i => $t) $html .= '<option value="' . $i . '">' . ($i + 1) . '. ' . e(mb_strimwidth((string)$t['text'], 0, 70, '…')) . '</option>';
    $html .= '</select></label></div>'
        . '<p class="c70-note" id="lr73-privacy">Reflexe je o hodině, ne o žácích: <strong>nepište jména žáků ani známky.</strong> Text s celým jménem žáka ze seznamu třídy se neuloží.</p>'
        . lr73_picks_html('picks_worked', LR73_PICKS_WORKED, 'Co fungovalo') . lr73_textarea_html('worked', 'Co fungovalo – vlastními slovy (nepovinné)', 'Např. „ukázka dig +short pomohla“.')
        . lr73_picks_html('picks_adjust', LR73_PICKS_ADJUST, 'Co upravit') . lr73_textarea_html('adjust', 'Co upravit – vlastními slovy (nepovinné)', 'Např. „úkol 3 zkrátit o 5 min“.')
        . lr73_textarea_html('next', 'Poznámka na příště (nepovinné)', 'Ukáže se v Dnešní hodině jako „Minule“.')
        . '<p class="c70-muted">Datum hodiny: ' . e(lt70_date($date)) . ' (podle kalendáře). Stejnou lekci můžete uložit znovu – přepíše se vaše poslední reflexe.</p>'
        . '<button type="submit" class="btn lr73-submit">Uložit reflexi</button></form>';
    return $html;
}

/** Jeden uložený záznam (bez jmen; autor jen „vy“ / „jiný učitel“). */
function lr73_entry_html(array $row, string $authorHash): string
{
    $picks = array_merge(array_map(static fn(string $k): string => '+ ' . (LR73_PICKS_WORKED[$k] ?? $k), (array)$row['picks_worked']),
        array_map(static fn(string $k): string => '↻ ' . (LR73_PICKS_ADJUST[$k] ?? $k), (array)$row['picks_adjust']));
    $html = '<li class="lr73-entry"><p><strong>Lekce ' . (int)$row['lesson'] . ' · ' . e(lt70_date((string)$row['date'])) . '</strong> <span class="c70-chip' . ($row['state'] === 'neoduceno' ? ' c70-chip-warn' : '') . '">'
        . e(lr73_state_label((string)$row['state'])) . '</span>' . ((string)$row['author'] !== $authorHash ? ' <span class="c70-chip">jiný učitel</span>' : '') . '</p>';
    if ((string)$row['stop_label'] !== '') $html .= '<p>Skončili jsme u: ' . e((string)$row['stop_label']) . '</p>';
    if ($picks !== []) $html .= '<p class="c70-muted">' . e(implode(' · ', $picks)) . '</p>';
    foreach (['worked' => 'Fungovalo', 'adjust' => 'Upravit', 'next' => 'Na příště'] as $k => $label) if ((string)($row[$k] ?? '') !== '') $html .= '<p><strong>' . $label . ':</strong> ' . e((string)$row[$k]) . '</p>';
    return $html . '</li>';
}

/** Záložka „Reflexe hodiny“. */
function lr73_render_tab(array $modules, string $csrf): void
{
    [$requested, $lessonParam] = lp73_request();
    $year = lt70_school_year();
    $today = date('Y-m-d');
    $classes = lt70_classes($modules, $year);
    $classId = lt70_pick_class($classes, $requested, $year, $today, date('H:i'));
    if ($classId === '') {
        echo c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Reflexe se ukáže, jakmile vám administrátor přidělí třídu (Správa → Učitelé).', []);
        return;
    }
    $n = $lessonParam > 0 ? $lessonParam : lr73_default_lesson($classId, $today, lt70_slot($year, $classId, $today, 0)['lesson']);
    $module = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    $lesson = lm71_lesson($classId, $n, $module);
    $author = lr73_author_hash();
    $visible = lr73_visible($classId, $author);
    $cov = lr73_coverage($classId, $today, $visible);
    $readers = lr73_readers_mode() === 'class_teachers' ? 'vy, ostatní učitelé této třídy a administrátor' : 'jen vy a administrátor';
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Po hodině · ' . e(lt70_class_name($modules, $classId)) . '</div><h1>Reflexe hodiny</h1>'
        . '<p>Lekce ' . $n . ' · ' . e((string)$lesson['title']) . '. Zabere to do minuty; čtou ji ' . e($readers) . '.</p></div></section>';
    $nav = [];
    if ($n > 1) $nav[] = lt70_tab_link('reflexe73', $classId, '← Lekce ' . ($n - 1), ['lesson' => $n - 1]);
    if ($n < LT70_LESSONS_MAX) $nav[] = lt70_tab_link('reflexe73', $classId, 'Lekce ' . ($n + 1) . ' →', ['lesson' => $n + 1]);
    $nav[] = lt70_tab_link('hodina', $classId, 'Dnešní hodina');
    echo '<nav class="lp73-nav lr73-nav" aria-label="Jiná lekce">' . implode(' ', array_filter($nav)) . '</nav>';
    echo c70_kpis_html([
        ['Odučené hodiny', (string)$cov['taught'], 'podle kalendáře do dneška', ''],
        ['S reflexí', $cov['reflected'] . ' / ' . $cov['taught'], $cov['pct'] . ' % (cíl ≥ 70 %)', $cov['taught'] > 0 && $cov['pct'] < 70 ? 'warn' : 'ok'],
    ], 'Pokrytí reflexemi');
    echo '<div class="c70-grid"><div class="c70-col"><section class="c70-card" aria-labelledby="lr73-form-h"><h2 id="lr73-form-h">Reflexe lekce ' . $n . '</h2>';
    if (function_exists('teacher_permission') && teacher_permission('content.manage')) {
        echo lr73_form_html($classId, $lesson, lr73_lesson_date($classId, $n, $today), $csrf);
    } else {
        echo '<p class="c70-note" role="status">Máte jen čtení – reflexi zapisuje učitel třídy.</p>';
    }
    echo '</section></div><div class="c70-col c70-side"><section class="c70-card" aria-labelledby="lr73-list-h"><h2 id="lr73-list-h">Uložené reflexe</h2>';
    echo $visible === [] ? '<p class="c70-muted">Zatím žádná reflexe této třídy.</p>' : '<ul class="lr73-list">' . implode('', array_map(static fn(array $r): string => lr73_entry_html($r, $author), array_slice($visible, 0, 12))) . '</ul>';
    echo '<p class="c70-muted">Reflexe se mažou po ' . LR73_RETENTION_DAYS . ' dnech.</p></section></div></div>';
}

/** Odkazy přípravného balíčku v Dnešní hodině (jen záložky, které role vidí). */
function lr73_prep_links_html(string $classId, array $extra): string
{
    $links = array_filter([lt70_tab_link('hodina_projektor', $classId, 'Projektor', $extra), lt70_tab_link('hodina_tisk', $classId, 'Tisk (plán + pracovní list)', $extra),
        lt70_tab_link('hodina_zastup', $classId, 'List pro zástup', $extra), lt70_tab_link('reflexe73', $classId, 'Reflexe po hodině', $extra)]);
    return $links === [] ? '' : '<p class="c70-links">' . implode(' · ', $links) . '</p>';
}

/** Karta „Minule“ v Dnešní hodině: kde skončili, poznámka na příště, návrh posunu u neodučené / části hodiny (jen návrh). */
function lr73_today_note_html(array $m): string
{
    if (($m['class'] ?? '') === '' || !isset($m['lesson']['number'])) return '';
    $current = (int)$m['lesson']['number'];
    $extra = !empty($m['slot']['override']) ? ['lesson' => $current] : [];
    $links = lr73_prep_links_html($m['class'], $extra);
    try {
        $last = lr73_latest_before($m['class'], (string)$m['today'], $current);
    } catch (Throwable $e) {
        error_log('EDUCANET v73 reflexe v Dnešní hodině: ' . $e->getMessage());
        $last = null;
    }
    if ($last === null) return $links === '' ? '' : '<section class="c70-card c70-wide lr73-today" aria-labelledby="lr73-today-h"><h2 id="lr73-today-h">Příprava a reflexe</h2>' . $links . '</section>';
    $n = (int)$last['lesson'];
    $parts = [];
    if ((string)$last['stop_label'] !== '') $parts[] = 'skončili jste u ' . (string)$last['stop_label'];
    if ((string)$last['next'] !== '') $parts[] = 'poznámka: ' . (string)$last['next'];
    $text = 'Minule (lekce ' . $n . ', ' . lt70_date((string)$last['date']) . ', ' . lr73_state_label((string)$last['state']) . ')' . ($parts !== [] ? ': ' . rtrim(implode(', ', $parts), '.!?… ') : '') . '.';
    $html = '<section class="c70-card c70-wide lr73-today" aria-labelledby="lr73-today-h"><h2 id="lr73-today-h">Minule</h2><p>' . e($text) . '</p>';
    if ((string)$last['state'] === 'neoduceno' && $n < $current) {
        $html .= '<p class="c70-note" role="status">Návrh posunu: lekce ' . $n . ' se neodučila – zvažte dnes začít lekcí ' . $n . ' místo ' . $current . '. Jen návrh, kalendář se nemění. '
            . lt70_tab_link('hodina', $m['class'], 'Náhled lekce ' . $n, ['lesson' => $n]) . '</p>';
    } elseif ((string)$last['state'] === 'cast' && $n < $current) {
        $html .= '<p class="c70-note" role="status">Návrh: na začátku hodiny dokončete zbytek lekce ' . $n . ((string)$last['stop_label'] !== '' ? ' (od: ' . e((string)$last['stop_label']) . ')' : '') . '. Jen návrh. '
            . lt70_tab_link('hodina', $m['class'], 'Náhled lekce ' . $n, ['lesson' => $n]) . '</p>';
    }
    return $html . $links . '</section>';
}
