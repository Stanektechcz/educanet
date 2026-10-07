<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v70 · vykreslení záložky „Dnešní hodina“ (data z lesson_today_v70.php).
 * Každý výstup přes e(), externí odkazy přes safe_url(). Jediný formulář je otevření hodiny (existující akce sess53_t_open
 * s CSRF, politikou teacher59 a oprávněním students.manage – návrat zpět sem přes return_tab=hodina). Texty česky.
 */

require_once __DIR__ . '/cockpit_v70.php';

/** Odkaz na záložku cockpitu jen tehdy, když ji role vidí ('' = nezobrazit). */
function lt70_tab_link(string $tab, string $classId, string $label, array $extra = [], string $class = 'c70-link'): string
{
    if (!c70_tab_visible($tab)) return '';
    $url = function_exists('teacher68_tab_url') ? teacher68_tab_url($tab, $classId) : '?tab=' . rawurlencode($tab);
    foreach ($extra as $k => $v) $url .= '&' . rawurlencode((string)$k) . '=' . rawurlencode((string)$v);
    return '<a class="' . e($class) . '" href="' . e($url) . '">' . e($label) . '</a>';
}

function lt70_date(string $date): string
{
    if ($date === '') return '';
    return function_exists('tut52_cz_date') ? tut52_cz_date($date) : date('j. n. Y', (int)strtotime($date));
}

function lt70_class_name(array $modules, string $classId): string
{
    $module = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    return trim((string)($module['name'] ?? $classId) . ' · ' . (string)($module['subject'] ?? ''), ' ·');
}

function lt70_render_tab(array $modules, string $classId): void
{
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $lesson = is_string($_GET['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_GET['lesson']) === 1 ? (int)$_GET['lesson'] : 0;
    try {
        $m = lt70_model($modules, $requested, $lesson);
    } catch (Throwable $e) {
        error_log('EDUCANET v70 dnešní hodina: ' . $e->getMessage());
        echo c70_empty_html('Dnešní hodinu se teď nepodařilo sestavit.', 'Zkuste stránku obnovit. Rozvrh a kód hodiny najdete i v záložce Kód hodiny.', [['session', 'Kód hodiny']]);
        return;
    }
    if ($m['class'] === '') {
        echo c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Dnešní hodina se ukáže, jakmile vám administrátor přidělí třídu (Správa → Učitelé).', []);
        return;
    }
    echo lt70_head_html($m, $modules);
    echo lt70_picker_html($m, $modules);
    echo lt70_kpis_html($m);
    echo '<div class="c70-grid"><div class="c70-col">' . lt70_topic_html($m) . lt70_plan_html($m) . lt70_materials_html($m) . lt70_paths_html($m) . '</div>'
        . '<div class="c70-col c70-side">' . lt70_session_html($m) . lt70_actions_html($m) . lt70_tasks_html($m) . '</div></div>';
    echo lt70_students_html($m);
}

function lt70_head_html(array $m, array $modules): string
{
    $slot = $m['slot'];
    $sch = $m['schedule'];
    $when = $slot['status'] === 'today' ? 'Dnes · ' . lt70_date($m['today']) : ($slot['status'] === 'next' ? 'Další hodina · ' . lt70_date($slot['date']) : 'Školní rok skončil');
    $time = trim((string)$sch['start']) !== '' ? (string)$sch['start'] . '–' . (string)$sch['end'] . ((string)$sch['room'] !== '' ? ' · učebna ' . (string)$sch['room'] : '') : '';
    $html = '<section class="teacher-page-head c70-head"><div><div class="eyebrow">' . e($when) . '</div><h1>Dnešní hodina · ' . e(lt70_class_name($modules, $m['class'])) . '</h1>'
        . '<p>Lekce ' . (int)$m['lesson']['number'] . ' z ' . LT70_LESSONS_MAX . ' · ' . e($m['lesson']['title']) . ($time !== '' ? ' · ' . e($time) : '') . '</p></div></section>';
    if ($slot['note'] !== '') $html .= '<p class="c70-note" role="status">' . e($slot['note']) . ' Níže je nejbližší výuková hodina.</p>';
    if ($slot['override']) $html .= '<p class="c70-note" role="status">Prohlížíte lekci ' . (int)$slot['lesson'] . ', podle kalendáře je na řadě lekce ' . (int)$slot['calendar_lesson'] . '. '
        . lt70_tab_link('hodina', $m['class'], 'Zpět na lekci podle kalendáře') . '</p>';
    return $html;
}

/** Výběr třídy (časy bloků) a listování lekcemi. */
function lt70_picker_html(array $m, array $modules): string
{
    $html = '<nav class="c70-picker" aria-label="Třída a lekce"><ul class="c70-classes">';
    foreach ($m['classes'] as $cid) {
        $sch = $m['schedules'][$cid];
        $current = $cid === $m['class'] ? ' aria-current="page"' : '';
        $time = trim((string)$sch['start']) !== '' ? (string)$sch['start'] . '–' . (string)$sch['end'] : '';
        $html .= '<li><a href="' . e('?tab=hodina&class=' . rawurlencode($cid)) . '"' . $current . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong><small>' . e($time) . '</small></a></li>';
    }
    $n = (int)$m['lesson']['number'];
    $base = '?tab=hodina&class=' . rawurlencode($m['class']);
    $html .= '</ul><ul class="c70-lessons">';
    if ($n > 1) $html .= '<li><a href="' . e($base . '&lesson=' . ($n - 1)) . '">← Lekce ' . ($n - 1) . '</a></li>';
    if ($m['slot']['override']) $html .= '<li><a href="' . e($base) . '">Lekce podle kalendáře (' . (int)$m['slot']['calendar_lesson'] . ')</a></li>';
    if ($n < LT70_LESSONS_MAX) $html .= '<li><a href="' . e($base . '&lesson=' . ($n + 1)) . '">Lekce ' . ($n + 1) . ' →</a></li>';
    return $html . '</ul></nav>';
}

function lt70_kpis_html(array $m): string
{
    $k = $m['kpis'];
    $session = $m['session'];
    $sessionText = $session['exists'] ? ($session['open'] ? 'Otevřená' : 'Uzavřená') : ($m['slot']['status'] === 'today' ? 'Neotevřená' : 'Až v den výuky');
    return c70_kpis_html([
        ['Žáci', (string)$k['students'], $k['students'] > 0 ? 'v seznamu třídy' : 'seznam je prázdný', ''],
        ['Lekce hotová', $k['done'] . ' / ' . $k['students'], 'průměr ' . $k['avg'] . ' % fází', ''],
        ['Potřebují pomoc', (string)$k['behind'], 'signály a minulá lekce', $k['behind'] > 0 ? 'warn' : 'ok'],
        ['Úkoly', (string)$k['tasks'], $k['overdue'] . ' po termínu', $k['overdue'] > 0 ? 'warn' : ''],
        ['Hodina s kódem', $sessionText, $session['exists'] ? $k['joined'] . ' pracuje · ' . $k['submitted'] . ' odevzdalo' : 'kód pro žáky', $session['open'] ? 'ok' : ''],
    ], 'Souhrn hodiny');
}

function lt70_topic_html(array $m): string
{
    $l = $m['lesson'];
    $html = '<section class="c70-card" aria-labelledby="c70-topic"><h2 id="c70-topic">Téma a cíl</h2><p class="c70-lead"><strong>' . e($l['title']) . '</strong></p>';
    $html .= $l['goal'] !== '' ? '<p>' . e($l['goal']) . '</p>' : '<p class="c70-muted">Lekce nemá zapsaný cíl – doplňte ho v přípravě (Plán a kurikulum).</p>';
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

function lt70_plan_html(array $m): string
{
    $steps = $m['lesson']['steps'];
    $html = '<section class="c70-card" aria-labelledby="c70-plan"><h2 id="c70-plan">Průběh hodiny</h2>';
    if ($steps === []) return $html . '<p class="c70-muted">Lekce zatím nemá kroky. Připravte je v záložce Plán a kurikulum.</p></section>';
    $html .= '<ol class="c70-steps">';
    foreach ($steps as $s) {
        $html .= '<li><div><strong>' . e($s['title']) . '</strong>' . ($s['time'] !== '' ? ' <span class="c70-chip">' . e($s['time']) . '</span>' : '') . '</div>';
        if ($s['tasks'] !== []) $html .= '<ul>' . implode('', array_map(static fn(string $t): string => '<li>' . e($t) . '</li>', array_slice($s['tasks'], 0, 4))) . '</ul>';
        $html .= '</li>';
    }
    return $html . '</ol></section>';
}

function lt70_materials_html(array $m): string
{
    $html = '<section class="c70-card" aria-labelledby="c70-mat"><h2 id="c70-mat">Materiály k lekci</h2>';
    $links = array_filter([
        lt70_tab_link('teach', $m['class'], 'Promítnout lekci (Režim hodiny)', ['lesson' => (int)$m['lesson']['number']], 'btn secondary'),
        lt70_tab_link('curriculum', $m['class'], 'Příprava v Plánu a kurikulu', [], 'btn secondary'),
        lt70_tab_link('authoring', $m['class'], 'Otázky a obsah', [], 'btn secondary'),
    ]);
    if ($links !== []) $html .= '<div class="c70-actions">' . implode('', $links) . '</div>';
    if ($m['materials'] === []) {
        return $html . '<p class="c70-muted">K tématům této lekce zatím nejsou žádné odkazy. Doporučené video k lekci přidáte v Režimu hodiny (sekce Video k lekci).</p></section>';
    }
    $types = ['video' => 'Video', 'course' => 'Kurz', 'article' => 'Článek', 'tool' => 'Nástroj'];
    $html .= '<ul class="c70-materials">';
    foreach ($m['materials'] as $r) {
        $url = safe_url($r['url']);
        if ($url === '' || $url === '#') continue;
        $html .= '<li><span class="c70-chip">' . e($types[$r['type']] ?? 'Odkaz') . '</span><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($r['title'])
            . '<span class="c70-sr"> (otevře se v novém okně)</span></a><small>' . e(implode(' · ', array_filter([$r['topic'], $r['meta']]))) . '</small></li>';
    }
    return $html . '</ul><p class="c70-muted">Žák vidí stejné materiály v lekci (Materiály → lekce ' . (int)$m['lesson']['number'] . ').</p></section>';
}

function lt70_paths_html(array $m): string
{
    $html = '<section class="c70-card" aria-labelledby="c70-paths"><h2 id="c70-paths">Výukové cesty</h2>';
    if (!$m['paths']['enabled']) {
        return $html . '<p class="c70-muted">Výukové cesty jsou zatím v pilotu pro 1.A a 3.A. Pro tuto třídu použijte kroky lekce a Režim hodiny.</p></section>';
    }
    if ($m['paths']['rows'] === []) return $html . '<p class="c70-muted">Třída zatím nemá žádné cesty v katalogu.</p></section>';
    $html .= '<ul class="c70-paths">';
    foreach ($m['paths']['rows'] as $p) {
        $chips = ($p['related'] ? '<span class="c70-chip c70-chip-accent">k tématu lekce</span>' : '') . ($p['assigned'] ? '<span class="c70-chip c70-chip-ok">přiřazeno třídě</span>' : '<span class="c70-chip">nepřiřazeno</span>');
        $html .= '<li><div><strong>' . e($p['title']) . '</strong> ' . $chips . '</div><small>' . e($p['goal']) . '</small><small>' . e(($p['competency'] !== '' ? $p['competency'] . ' · ' : '') . $p['minutes'] . ' min · začalo ' . $p['started'] . ', dokončilo ' . $p['finished']) . '</small></li>';
    }
    $link = lt70_tab_link('cesty', $m['class'], 'Přiřadit nebo upravit cesty →');
    return $html . '</ul>' . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
}

function lt70_session_html(array $m): string
{
    $s = $m['session'];
    $html = '<section class="c70-card" aria-labelledby="c70-sess"><h2 id="c70-sess">Hodina s kódem</h2>';
    if ($s['exists']) {
        $html .= '<p class="c70-code" aria-label="Kód hodiny">' . e($s['code']) . '</p><p>' . e($s['title']) . '</p>'
            . '<p class="c70-muted">' . ($s['open'] ? 'Otevřená' : 'Uzavřená – kód nefunguje') . ' · ' . (int)$s['joined'] . ' pracuje · ' . (int)$s['submitted'] . ' odevzdalo · ' . (int)$s['graded'] . ' ohodnoceno</p>';
        $link = lt70_tab_link('session', $m['class'], 'Promítnout kód a hodnotit odevzdání →');
        return $html . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
    }
    if ($m['slot']['status'] !== 'today') {
        $when = $m['slot']['date'] !== '' ? 'Hodina je naplánovaná na ' . lt70_date($m['slot']['date']) . '. ' : '';
        return $html . '<p class="c70-muted">' . e($when . 'Kód pro žáky otevřete v den výuky.') . '</p></section>';
    }
    if (!function_exists('teacher_permission') || !teacher_permission('students.manage')) {
        return $html . '<p class="c70-muted">Hodina zatím není otevřená. Otevřít ji může učitel třídy.</p></section>';
    }
    return $html . '<p class="c70-muted">Hodina zatím není otevřená. Po otevření dostanete kód, který žáci zadají po přihlášení.</p>'
        . '<form method="post" action="teacher.php" class="c70-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
        . '<input type="hidden" name="action" value="sess53_t_open"><input type="hidden" name="return_tab" value="hodina">'
        . '<input type="hidden" name="class_id" value="' . e($m['class']) . '"><input type="hidden" name="date" value="' . e($m['today']) . '">'
        . '<label for="c70-kind">Typ hodiny</label><select id="c70-kind" name="kind"><option value="work">Samostatná práce k lekci ' . (int)$m['slot']['calendar_lesson'] . '</option><option value="intake">Seznamovací dotazník</option></select>'
        . '<button class="btn primary" type="submit">Otevřít hodinu a vygenerovat kód</button></form></section>';
}

function lt70_actions_html(array $m): string
{
    $c = $m['class'];
    $links = array_filter([
        lt70_tab_link('prehled', $c, 'Přehled třídy: kdo zaostává'),
        lt70_tab_link('class_results', $c, 'Výsledky třídy a známky'),
        lt70_tab_link('hodnoceni66', $c, 'Ráno: co řešit jako první'),
        lt70_tab_link('kompetence', $c, 'Mapa kompetencí'),
        $m['lesson']['family'] !== 'graphics' ? lt70_tab_link('labdata', $c, 'Analytika Linux Labu') : '',
        lt70_tab_link('pristupy', $c, 'Přístupy a kartičky žáků'),
    ]);
    if ($links === []) return '';
    return '<section class="c70-card" aria-labelledby="c70-quick"><h2 id="c70-quick">Rychlé odkazy</h2><ul class="c70-linklist"><li>' . implode('</li><li>', $links) . '</li></ul></section>';
}

function lt70_tasks_html(array $m): string
{
    $t = $m['tasks'];
    $html = '<section class="c70-card" aria-labelledby="c70-tasks"><h2 id="c70-tasks">Zadané úkoly</h2>';
    if ($t['active'] === 0) {
        $link = lt70_tab_link('student360', $m['class'], 'Otevřít detail žáka');
        return $html . '<p class="c70-muted">Třída nemá žádné aktivní učitelské úkoly. Úkol zadáte v Detailu žáka nebo hromadně ve Výsledcích třídy.</p>' . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
    }
    $html .= '<p>' . (int)$t['active'] . ' aktivních · ' . (int)$t['overdue'] . ' po termínu · ' . (int)$t['soon'] . ' do ' . LT70_TASKS_SOON_DAYS . ' dnů</p><ul class="c70-linklist">';
    foreach ($t['top'] as $row) {
        $html .= '<li><strong>' . e($row['title']) . '</strong> <span class="c70-muted">' . (int)$row['count'] . '× ' . e($row['due'] !== '' ? '· termín ' . date('j. n.', (int)strtotime($row['due'])) : '· bez termínu') . '</span></li>';
    }
    return $html . '</ul></section>';
}

function lt70_students_html(array $m): string
{
    $rows = $m['students'];
    $n = (int)$m['lesson']['number'];
    $html = '<section class="c70-card c70-wide" aria-labelledby="c70-students"><h2 id="c70-students">Žáci a postup v lekci ' . $n . '</h2>';
    if ($rows === []) {
        $links = array_filter([lt70_tab_link('intake', '', 'Seznamovací dotazník'), lt70_tab_link('pristupy', $m['class'], 'Přístupy žáků')]);
        return $html . '<p class="c70-muted">Třída zatím nemá žáky v seznamu. Žáci se do seznamu dostanou přes seznamovací dotazník nebo účty v záložce Přístupy.</p>'
            . ($links !== [] ? '<p class="c70-actions">' . implode(' ', $links) . '</p>' : '') . '</section>';
    }
    $html .= '<p class="c70-muted">Postup = splněné fáze lekce (výklad, test, projekt, odevzdání). Nahoře žáci, kteří potřebují pomoc.</p>'
        . '<table class="c70-table"><caption class="c70-sr">Postup žáků v lekci ' . $n . '</caption><thead><tr><th scope="col">Žák</th><th scope="col">Lekce ' . $n . '</th><th scope="col">Minulá lekce</th>'
        . '<th scope="col">Kompetence</th><th scope="col">Úkoly</th><th scope="col">V hodině</th><th scope="col">Signály</th></tr></thead><tbody>';
    $detail = c70_tab_visible('student360');
    foreach ($rows as $r) {
        $name = $detail ? '<a href="' . e('?tab=student360&class=' . rawurlencode($m['class']) . '&student=' . rawurlencode($r['key'])) . '">' . e($r['label']) . '</a>' : e($r['label']);
        $mastery = $r['mastered'] === null ? '—' : (int)$r['mastered'] . ' / ' . (int)$r['mastery_total'];
        $html .= '<tr' . ($r['behind'] ? ' class="is-behind"' : '') . '><th scope="row" data-label="Žák">' . $name . '</th>'
            . '<td data-label="Lekce ' . $n . '"><span class="c70-bar" aria-hidden="true"><i style="width:' . (int)$r['percent'] . '%"></i></span> ' . (int)$r['phases'] . '/4 fáze</td>'
            . '<td data-label="Minulá lekce">' . ($r['prev'] === null ? '—' : (int)$r['prev'] . ' %') . '</td>'
            . '<td data-label="Kompetence">' . e($mastery) . '</td>'
            . '<td data-label="Úkoly">' . (int)$r['tasks'] . ($r['overdue'] > 0 ? ' <span class="c70-chip c70-chip-warn">' . (int)$r['overdue'] . ' po termínu</span>' : '') . '</td>'
            . '<td data-label="V hodině">' . e($r['session'] !== '' ? $r['session'] : '—') . '</td>'
            . '<td data-label="Signály">' . ($r['reasons'] === [] ? '<span class="c70-ok">v pořádku</span>' : e(implode(' · ', $r['reasons']))) . '</td></tr>';
    }
    return $html . '</tbody></table></section>';
}
