<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · záložka „Reflexe hodiny“ (`reflexe73`) a karta „Minule“ v Dnešní hodině. Každý výstup přes e(); jediný formulář
 * je lr73_save s CSRF (v79: po krocích, lesson_reflect_v79.php) (asistent bez content.manage formulář nedostane). GET jen obecné ?class= a ?lesson=. Texty česky.
 */

function lr73_state_label(string $state): string
{
    return LR73_STATES[$state] ?? 'neuvedeno';
}

// v79: formulář reflexe po krocích je v lesson_reflect_v79.php (lr79_form_html); dřívější lr73_form_html vyřazena.

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
        // v79: stepper Stav → Kde jsme skončili → Co upravit → Hotovo (stejná akce lr73_save; koncept v relaci). Načítá se jen tady, ne v Dnešní hodině.
        require_once dirname(__DIR__, 2) . '/src/lessons/lesson_reflect_v79.php';
        $st = lr79_state($classId, $n);
        $other = sp79_draft('reflexe');
        if ($lessonParam === 0 && $other['data'] !== [] && str_starts_with((string)$other['scope'], $classId . '|') && (string)$other['scope'] !== $classId . '|' . $n) {
            $ln = (int)substr((string)$other['scope'], strlen($classId) + 1);
            echo sp79_resume_html('Máte rozpracovanou reflexi lekce ' . $ln . ' (krok ' . (int)$other['step'] . ' ze 4).', lr79_url($classId, $ln, (int)$other['step']), 'Pokračovat v reflexi lekce ' . $ln);
        }
        echo lr79_bar_html($classId, $n, $st);
        if ($st['failed']) echo sp79_status_html('danger', 'Reflexi se nepodařilo uložit.', 'Údaje zůstaly zachované – opravte krok, kterého se chyba týká, a uložte znovu.');
        echo $st['done'] ? lr79_done_html($classId, $n) : lr79_form_html($classId, $lesson, lr73_lesson_date($classId, $n, $today), $csrf, $st);
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
