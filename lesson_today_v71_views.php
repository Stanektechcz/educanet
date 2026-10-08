<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v71 · „Dnešní hodina“ nad jednotným modelem lekce lm71 (jen čtení).
 * Navíc proti v70: karta dne bez lekce (calendar_days_v71.php), plán po minutách, kritéria úspěchu, poznámky pro učitele,
 * pracovní list, odkaz na lesson kit, odznak úplnosti a oddělení „zatím bez aktivity“ od „potřebuje pomoc“.
 * Nové POST akce ani GET parametry nejsou (jediný formulář zůstává sess53_t_open z v70). Každý výstup přes e(). Texty česky.
 */

require_once __DIR__ . '/lesson_today_v70_views.php';
require_once __DIR__ . '/calendar_days_v71.php';

/** Model stránky: v70 + den bez lekce (jen bez ručního ?lesson=) + lesson kit. */
function lt71_model(array $modules, string $requestedClass, int $lessonParam, ?int $now = null): array
{
    $m = lt70_model($modules, $requestedClass, $lessonParam, $now);
    if ($m['class'] === '') return $m;
    $row = $lessonParam === 0 ? cd71_next_day_row(adaptive_school_year_rows(lt70_school_year(), $m['class']), $m['today']) : null;
    $m['day'] = $row !== null ? lm71_day($m['class'], $row) + ['is_today' => (bool)$row['is_today']] : null;
    $n = (int)$m['lesson']['number'];
    // v72: stav návrhu podle schválení učitelem (schváleno / vráceno / změněno po schválení), jen čtení.
    if (function_exists('lc72_status') && (string)($m['lesson']['meta']['status'] ?? '') !== 'puvodni') $m['lesson']['meta']['status'] = lc72_status($m['class'], $n)['status'];
    $m['kit'] = ['exists' => is_file(__DIR__ . '/materials/lesson_kits/' . $m['class'] . '/lesson_' . str_pad((string)$n, 2, '0', STR_PAD_LEFT) . '.md')];
    return $m;
}

/** Záložka „Dnešní hodina“. $now jen pro audit (pevné datum); v provozu aktuální čas. */
function lt71_render_tab(array $modules, string $classId, ?int $now = null): void
{
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $lesson = is_string($_GET['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_GET['lesson']) === 1 ? (int)$_GET['lesson'] : 0;
    try {
        $m = lt71_model($modules, $requested, $lesson, $now);
    } catch (Throwable $e) {
        error_log('EDUCANET v71 dnešní hodina: ' . $e->getMessage());
        echo c70_empty_html('Dnešní hodinu se teď nepodařilo sestavit.', 'Zkuste stránku obnovit. Rozvrh a kód hodiny najdete i v záložce Kód hodiny.', [['session', 'Kód hodiny']]);
        return;
    }
    if ($m['class'] === '') {
        echo c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Dnešní hodina se ukáže, jakmile vám administrátor přidělí třídu (Správa → Učitelé).', []);
        return;
    }
    echo lt71_head_html($m, $modules);
    if (is_array($m['day'])) echo lt71_day_html($m);
    echo lt70_picker_html($m, $modules);
    echo lt71_kpis_html($m);
    echo '<div class="c70-grid"><div class="c70-col">' . lt71_topic_html($m) . lt71_plan_html($m) . lt71_notes_html($m) . lt71_materials_html($m) . lt70_paths_html($m) . '</div>'
        . '<div class="c70-col c70-side">' . lt70_session_html($m) . (function_exists('lx72_teacher_card_html') ? lx72_teacher_card_html($m) : '') . lt70_actions_html($m) . lt70_tasks_html($m) . '</div></div>';
    echo lt71_students_html($m);
}

function lt71_head_html(array $m, array $modules): string
{
    $slot = $m['slot'];
    $day = $m['day'];
    $sch = $m['schedule'];
    if (is_array($day)) {
        $when = ($day['is_today'] ? 'Dnes · ' : 'Další výukový den · ') . lt70_date((string)$day['date']);
        $what = 'Den bez lekce · ' . (string)$day['title'];
    } else {
        $when = $slot['status'] === 'today' ? 'Dnes · ' . lt70_date($m['today']) : ($slot['status'] === 'next' ? 'Další hodina · ' . lt70_date($slot['date']) : 'Školní rok skončil');
        $what = 'Lekce ' . (int)$m['lesson']['number'] . ' z ' . LT70_LESSONS_MAX . ' · ' . (string)$m['lesson']['title'];
    }
    $time = trim((string)$sch['start']) !== '' ? (string)$sch['start'] . '–' . (string)$sch['end'] . ((string)$sch['room'] !== '' ? ' · učebna ' . (string)$sch['room'] : '') : '';
    $html = '<section class="teacher-page-head c70-head"><div><div class="eyebrow">' . e($when) . '</div><h1>Dnešní hodina · ' . e(lt70_class_name($modules, $m['class'])) . '</h1>'
        . '<p>' . e($what) . ($time !== '' ? ' · ' . e($time) : '') . '</p></div></section>';
    if ($slot['note'] !== '' && !(is_array($day) && $day['is_today'])) $html .= '<p class="c70-note" role="status">' . e($slot['note']) . ' Níže je nejbližší výuková hodina.</p>';
    if ($slot['override']) $html .= '<p class="c70-note" role="status">Prohlížíte lekci ' . (int)$slot['lesson'] . ', podle kalendáře je na řadě lekce ' . (int)$slot['calendar_lesson'] . '. '
        . lt70_tab_link('hodina', $m['class'], 'Zpět na lekci podle kalendáře') . '</p>';
    return $html;
}

/** Plán úseků od–do jako seznam (stejné pro lekci i den bez lekce). */
function lt71_timeline_list(array $segments, string $label): string
{
    $html = '<ol class="c71-timeline" aria-label="' . e($label) . '">';
    foreach ($segments as $s) {
        $who = array_filter([(string)($s['teacher'] ?? ($s['text'] ?? '')), (string)($s['student'] ?? '') !== '' ? 'Žáci: ' . (string)$s['student'] : '']);
        $html .= '<li><span class="c71-time">' . (int)$s['from'] . '–' . (int)$s['to'] . ' min</span><div><strong>' . e((string)$s['phase']) . '</strong>'
            . ($who !== [] ? '<span>' . e(implode(' · ', $who)) . '</span>' : '') . '</div></li>';
    }
    return $html . '</ol>';
}

function lt71_list(array $items, string $class = 'c71-list'): string
{
    return '<ul class="' . e($class) . '">' . implode('', array_map(static fn(string $t): string => '<li>' . e($t) . '</li>', $items)) . '</ul>';
}

function lt71_day_html(array $m): string
{
    $d = $m['day'];
    $html = '<section class="c70-card c71-day" aria-labelledby="c71-day"><h2 id="c71-day">' . e(($d['is_today'] ? 'Dnes: ' : 'Další výukový den: ') . (string)$d['title']) . '</h2>'
        . '<p><span class="c70-chip c70-chip-accent">den bez lekce</span> <span class="c70-chip">' . e($d['meta']['status'] === 'zakladni' ? 'základní karta' : ($d['meta']['status'] === 'schvaleno' ? 'schváleno' : 'návrh')) . '</span></p>'
        . '<p class="c70-lead">' . e((string)$d['goal']) . '</p>';
    if ((string)$d['description'] !== '') $html .= '<p class="c70-muted">' . e((string)$d['description']) . '</p>';
    if ((string)$d['focus'] !== '') $html .= '<p><strong>Zaměření závěru roku:</strong> ' . e((string)$d['focus']) . '</p>';
    $html .= '<div class="c71-cols"><div><h3>Průběh (90 min)</h3>' . lt71_timeline_list($d['flow'], 'Průběh dne') . '</div><div><h3>Co mají žáci mít</h3>' . lt71_list($d['bring'])
        . '<h3>Co kontroluji</h3>' . lt71_list($d['check']) . '</div></div>';
    $link = $d['projects'] ? lt70_tab_link('projekty65', $m['class'], 'Projekty třídy (týmy, odevzdání, rubriky) →') : '';
    return $html . ($link !== '' ? '<p>' . $link . '</p>' : '') . '<p class="c70-muted">Níže je lekce, která je podle kalendáře na řadě, a postup žáků.</p></section>';
}

function lt71_kpis_html(array $m): string
{
    $k = $m['kpis'];
    $session = $m['session'];
    $sessionText = $session['exists'] ? ($session['open'] ? 'Otevřená' : 'Uzavřená') : ($m['slot']['status'] === 'today' ? 'Neotevřená' : 'Až v den výuky');
    return c70_kpis_html([
        ['Žáci', (string)$k['students'], $k['students'] > 0 ? 'v seznamu třídy' : 'seznam je prázdný', ''],
        ['Lekce hotová', $k['done'] . ' / ' . $k['students'], 'průměr ' . $k['avg'] . ' % fází', ''],
        ['Potřebují pomoc', (string)$k['behind'], 'signály a minulá lekce', $k['behind'] > 0 ? 'warn' : 'ok'],
        ['Zatím bez aktivity', (string)$k['idle'], 'bez dat – nejde o zaostávání', ''],
        ['Úkoly', (string)$k['tasks'], $k['overdue'] . ' po termínu', $k['overdue'] > 0 ? 'warn' : ''],
        ['Hodina s kódem', $sessionText, $session['exists'] ? $k['joined'] . ' pracuje · ' . $k['submitted'] . ' odevzdalo' : 'kód pro žáky', $session['open'] ? 'ok' : ''],
    ], 'Souhrn hodiny');
}

/** Odznak úplnosti přípravy lekce (12 polí modelu lm71). */
function lt71_badge_html(array $lesson): string
{
    $c = $lesson['completeness'];
    $status = ['puvodni' => 'původní obsah', 'navrh' => 'návrh (neschváleno)', 'schvaleno' => 'schváleno', 'vraceno' => 'vráceno k úpravě', 'zmeneno' => 'změněno po schválení'][(string)$lesson['meta']['status']] ?? 'původní obsah';   // v72: + vráceno, změněno
    $html = '<div class="c71-badge' . ($c['complete'] ? ' is-ok' : '') . '"><strong>Úplnost přípravy: ' . (int)$c['score'] . '/' . (int)$c['total'] . ' polí</strong>'
        . '<span class="c70-chip">' . e($status) . '</span>' . (!empty($lesson['meta']['template']) ? '<span class="c70-chip c70-chip-warn">šablonový obsah</span>' : '') . '</div>';
    if ($c['missing'] !== []) $html .= '<details class="c71-missing"><summary>Chybí: ' . e(implode(', ', array_slice($c['missing'], 0, 3))) . (count($c['missing']) > 3 ? ' a další' : '') . '</summary>' . lt71_list($c['missing']) . '</details>';
    return $html;
}

function lt71_topic_html(array $m): string
{
    $l = $m['lesson'];
    $html = '<section class="c70-card" aria-labelledby="c70-topic"><h2 id="c70-topic">Téma a cíl</h2>' . lt71_badge_html($l) . '<p class="c70-lead"><strong>' . e($l['title']) . '</strong></p>';
    $html .= $l['goal'] !== '' ? '<p>' . e($l['goal']) . '</p>' : '<p class="c70-muted">Lekce nemá zapsaný cíl – doplňte ho v přípravě (Plán a kurikulum).</p>';
    $html .= '<h3>Kritéria úspěchu</h3>';
    if ($l['success_criteria'] !== []) {
        $html .= lt71_list($l['success_criteria']);
    } elseif ($l['worksheet'] !== []) {
        $html .= '<p class="c70-muted">Kritéria zatím nejsou zapsaná. Jako vodítko poslouží výstupy pracovního listu (níže).</p>';
    } else {
        $html .= '<p class="c70-muted">Kritéria zatím nejsou zapsaná. Doplní je obsahová stopa (fáze B).</p>';
    }
    if ($l['topics'] !== []) {
        $html .= '<h3>Témata lekce</h3><ul class="c70-topics">';
        foreach ($l['topics'] as $t) $html .= '<li><strong>' . e($t['title']) . '</strong>' . ($t['summary'] !== '' ? '<span>' . e($t['summary']) . '</span>' : '') . '</li>';
        $html .= '</ul>';
    }
    $meta = [];
    if ($l['tools'] !== []) $meta[] = 'Nástroje: ' . implode(', ', $l['tools']);
    $meta[] = 'Test lekce: ' . $l['questions'] . ' otázek';
    return $html . '<p class="c70-muted">' . e(implode(' · ', $meta)) . '</p></section>';
}

function lt71_plan_html(array $m): string
{
    $l = $m['lesson'];
    $html = '<section class="c70-card" aria-labelledby="c70-plan"><h2 id="c70-plan">Průběh hodiny</h2>';
    $total = $l['timeline'] !== [] ? (int)end($l['timeline'])['to'] : 0;
    if ($l['timeline'] === []) {
        $html .= '<p class="c70-muted">Lekce zatím nemá plán po minutách. Připravte ho v záložce Plán a kurikulum.</p>';
    } else {
        $html .= '<h3>Plán po minutách</h3>' . lt71_timeline_list($l['timeline'], 'Plán hodiny po minutách');
        if (!empty($l['timeline'][0]['derived'])) $html .= '<p class="c70-muted">Plán je dopočtený z kroků lekce (celkem ' . $total . ' min z ' . LM71_LESSON_MINUTES . ').</p>';
    }
    if ($l['exit_ticket'] !== []) $html .= '<h3>Exit ticket</h3>' . lt71_list($l['exit_ticket']) . (count($l['exit_ticket']) < LM71_EXIT_VARIANTS_MIN ? '<p class="c70-muted">Zatím jedna varianta – stejná otázka jako v kvízu lekce.</p>' : '');
    if ($l['steps'] !== []) {
        $html .= '<details class="c71-steps"><summary>Kroky, které vidí žáci (' . count($l['steps']) . ')</summary><ol class="c70-steps">';
        foreach ($l['steps'] as $s) {
            $html .= '<li><div><strong>' . e($s['title']) . '</strong>' . ($s['time'] !== '' ? ' <span class="c70-chip">' . e($s['time']) . '</span>' : '') . '</div>';
            if ($s['tasks'] !== []) $html .= '<ul>' . implode('', array_map(static fn(string $t): string => '<li>' . e($t) . '</li>', array_slice($s['tasks'], 0, 4))) . '</ul>';
            $html .= '</li>';
        }
        $html .= '</ol></details>';
    }
    return $html . '</section>';
}

function lt71_notes_html(array $m): string
{
    $l = $m['lesson'];
    $html = '<section class="c70-card" aria-labelledby="c71-notes"><h2 id="c71-notes">Poznámky pro učitele a pracovní list</h2><h3>Poznámky pro učitele</h3>';
    $html .= $l['teacher_notes'] !== [] ? lt71_list($l['teacher_notes']) : '<p class="c70-muted">Lekce zatím nemá poznámky pro učitele (časté omyly, otázky do třídy). Doplní je obsahová stopa.</p>';
    $html .= '<h3>Pracovní list · výstupy žáka</h3>';
    $html .= $l['worksheet'] !== [] ? lt71_list($l['worksheet']) : '<p class="c70-muted">Lekce nemá pracovní list. Výstupem je odevzdání v lekci (fáze 4).</p>';
    if (!empty($l['differentiation']['support'])) $html .= '<h3>Podpora pro pomalejší</h3><p>' . e((string)$l['differentiation']['support']) . '</p>';   // v72: diferenciace z obsahové stopy
    if (!empty($l['differentiation']['challenge'])) $html .= '<h3>Výzva pro rychlejší</h3><p>' . e((string)$l['differentiation']['challenge']) . '</p>';
    return $html . '</section>';
}

/** Materiály v70 + odkaz na lesson kit v Režimu hodiny (soubory materials/ jsou z webu zakázané, kit se zobrazí v aplikaci). */
function lt71_materials_html(array $m): string
{
    $html = lt70_materials_html($m);
    if (!c70_tab_visible('teach')) return $html;
    $n = (int)$m['lesson']['number'];
    $url = (function_exists('teacher68_tab_url') ? teacher68_tab_url('teach', $m['class']) : '?tab=teach&class=' . rawurlencode($m['class'])) . '&lesson=' . $n . '#lesson-kit';
    $kit = '<p class="c71-kit"><a class="c70-link" href="' . e($url) . '">Lesson kit lekce ' . $n . ' (video, prezentace, podklady) →</a>'
        . ($m['kit']['exists'] ? '' : ' <span class="c70-muted">Podklad v materiálech pro tuto lekci chybí.</span>') . '</p>';
    $pos = strrpos($html, '</section>');
    return $pos === false ? $html . $kit : substr($html, 0, $pos) . $kit . '</section>';
}

function lt71_students_html(array $m): string
{
    $rows = $m['students'];
    if ($rows === []) return lt70_students_html($m);
    $n = (int)$m['lesson']['number'];
    $html = '<section class="c70-card c70-wide" aria-labelledby="c70-students"><h2 id="c70-students">Žáci a postup v lekci ' . $n . '</h2>'
        . '<p class="c70-muted">Postup = splněné fáze lekce (výklad, test, projekt, odevzdání). Nahoře žáci, kteří potřebují pomoc. „Zatím bez aktivity“ znamená, že o žákovi nejsou data – nejde o zaostávání.</p>'
        . '<table class="c70-table"><caption class="c70-sr">Postup žáků v lekci ' . $n . '</caption><thead><tr><th scope="col">Žák</th><th scope="col">Lekce ' . $n . '</th><th scope="col">Minulá lekce</th>'
        . '<th scope="col">Kompetence</th><th scope="col">Úkoly</th><th scope="col">V hodině</th><th scope="col">Signály</th></tr></thead><tbody>';
    $detail = c70_tab_visible('student360');
    foreach ($rows as $r) {
        $name = $detail ? '<a href="' . e('?tab=student360&class=' . rawurlencode($m['class']) . '&student=' . rawurlencode($r['key'])) . '">' . e($r['label']) . '</a>' : e($r['label']);
        $mastery = $r['mastered'] === null ? '—' : (int)$r['mastered'] . ' / ' . (int)$r['mastery_total'];
        $signal = !empty($r['idle']) ? '<span class="c70-chip">zatím bez aktivity</span>' : ($r['reasons'] === [] ? '<span class="c70-ok">v pořádku</span>' : e(implode(' · ', $r['reasons'])));
        $html .= '<tr' . ($r['behind'] ? ' class="is-behind"' : '') . '><th scope="row" data-label="Žák">' . $name . '</th>'
            . '<td data-label="Lekce ' . $n . '"><span class="c70-bar" aria-hidden="true"><i style="width:' . (int)$r['percent'] . '%"></i></span> ' . (int)$r['phases'] . '/4 fáze</td>'
            . '<td data-label="Minulá lekce">' . ($r['prev'] === null || !empty($r['idle']) ? '—' : (int)$r['prev'] . ' %') . '</td>'
            . '<td data-label="Kompetence">' . e($mastery) . '</td>'
            . '<td data-label="Úkoly">' . (int)$r['tasks'] . ($r['overdue'] > 0 ? ' <span class="c70-chip c70-chip-warn">' . (int)$r['overdue'] . ' po termínu</span>' : '') . '</td>'
            . '<td data-label="V hodině">' . e($r['session'] !== '' ? $r['session'] : '—') . '</td>'
            . '<td data-label="Signály">' . $signal . '</td></tr>';
    }
    return $html . '</tbody></table></section>';
}
