<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v79.1 · rozvržení „Dnešní hodiny“ (jen prezentace nad modelem lt71 – stejná data, stejný jediný formulář sess53_t_open).
 *   1. hero karta: třída, čas a učebna, lekce, cíl, stav schválení, primární akce (otevřít hodinu s kódem / promítnout kód / projektor),
 *   2. pořadí sekcí: Plán po minutách → Materiály → Komu pomoct (+ T-14) → Exit ticket → Poznámky, pak téma, kód hodiny, úkoly, cesty, odkazy, žáci,
 *   3. xl (≥ 1366 px): Komu pomoct a T-14 v kontextovém panelu vpravo (CSS grid, žádná duplicita v HTML),
 *   4. mobil (< 768 px): sekce sbalitelné (assets/today-v79-1.js, paměť v localStorage v try/catch); bez JS je vše rozbalené.
 * lt71_render_tab() sem deleguje (registr modulu i audity v70/v71 beze změny). Každý výstup přes e(). Texty česky (cockpit).
 */

require_once dirname(__DIR__, 2) . '/src/ui/ux_v79_1.php';

const LT791_T14_DAYS = 14;

function lt791_render_tab(array $modules, string $classId, ?int $now = null): void
{
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $lesson = is_string($_GET['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_GET['lesson']) === 1 ? (int)$_GET['lesson'] : 0;
    try {
        $m = lt71_model($modules, $requested, $lesson, $now);
    } catch (Throwable $e) {
        error_log('EDUCANET v79.1 dnešní hodina: ' . $e->getMessage());
        echo ux791_empty_html('Dnešní hodinu se teď nepodařilo sestavit.', 'Zkuste stránku obnovit. Rozvrh a kód hodiny najdete i v záložce Kód hodiny.',
            [['?tab=hodina', 'Obnovit stránku'], ...(c70_tab_visible('session') ? [[ux791_tab_url('session'), 'Otevřít Kód hodiny']] : [])]);
        return;
    }
    if ($m['class'] === '') {
        echo ux791_empty_html('Nemáte přiřazenou žádnou třídu.', 'Dnešní hodina se ukáže, jakmile vám administrátor přidělí třídu (Správa → Učitelé).',
            [[ux791_tab_url('attention'), 'Otevřít Co řešit dnes']]);
        return;
    }
    $sec = static fn(string $key, string $html): string => $html === '' ? '' : '<div class="lt791-sec" data-lt791-sec="' . e($key) . '">' . $html . '</div>';
    echo '<div class="lt791" data-lt791>';
    echo lt791_hero_html($m, $modules);
    if (function_exists('v81_hint_html')) echo v81_hint_html('hodina', 'Jak to funguje', ['Nahoře je třída, lekce a jedna hlavní akce – otevřít hodinu a vygenerovat kód.', 'Karty s čísly ukazují stav třídy; „Pozor“ znamená, že je co řešit.', 'Níže je plán po minutách, materiály a komu dnes pomoct.']);   // v81: nápověda při první návštěvě
    if (function_exists('lr73_today_note_html')) echo lr73_today_note_html($m);   // v73: „Minule“ + Projektor / Tisk / Zástup / Reflexe
    if (is_array($m['day'])) echo lt71_day_html($m);
    echo lt70_picker_html($m, $modules) . lt71_kpis_html($m);
    $help = function_exists('in74_card_html') ? in74_card_html($m, $now) : '';
    echo '<div class="lt791-flow">'
        . $sec('plan', lt791_plan_html($m))
        . $sec('materialy', lt71_materials_html($m))
        . '<div class="lt791-ctx" role="complementary" aria-label="Kontext hodiny: komu pomoct a T-14">' . $sec('pomoc', $help) . $sec('t14', lt791_t14_html($m, $now)) . '</div>'
        . $sec('exit', lt791_exit_html($m))
        . $sec('poznamky', lt71_notes_html($m))
        . $sec('tema', lt71_topic_html($m))
        . $sec('kod', lt791_session_html($m))
        . $sec('ukoly', lt791_tasks_html($m))
        . $sec('cesty', lt70_paths_html($m))
        . $sec('odkazy', lt70_actions_html($m))
        . '</div>';
    echo $sec('zaci', lt71_students_html($m)) . '</div>';
}

/** Stav schválení lekce jako text + tón (nikdy jen barva). @return array{0:string,1:string} */
function lt791_status(array $m): array
{
    if (is_array($m['day'])) return ['den bez lekce · ' . ($m['day']['meta']['status'] === 'schvaleno' ? 'schváleno' : 'plán dne pro učitele'), 'info'];
    $st = (string)($m['lesson']['meta']['status'] ?? 'puvodni');
    return match ($st) {
        'schvaleno' => ['Schváleno – žáci vidí nový obsah', 'ok'],
        'navrh' => ['Návrh čeká na schválení – žáci vidí původní lekci', 'warn'],
        'vraceno' => ['Vráceno k úpravě – žáci vidí původní lekci', 'danger'],
        'zmeneno' => ['Změněno po schválení – schvalte znovu', 'warn'],
        default => ['Původní obsah lekce', 'info'],
    };
}

/** Hero karta: kdy, třída, čas, lekce, cíl, stav schválení a primární akce. */
function lt791_hero_html(array $m, array $modules): string
{
    $slot = $m['slot'];
    $day = $m['day'];
    $sch = $m['schedule'];
    if (is_array($day)) {
        $when = ($day['is_today'] ? 'Dnes · ' : 'Další výukový den · ') . lt70_date((string)$day['date']);
        $what = 'Den bez lekce · ' . (string)$day['title'];
        $goal = (string)$day['goal'];
    } else {
        $when = $slot['status'] === 'today' ? 'Dnes · ' . lt70_date($m['today']) : ($slot['status'] === 'next' ? 'Další hodina · ' . lt70_date($slot['date']) : 'Školní rok skončil');
        $what = 'Lekce ' . (int)$m['lesson']['number'] . ' z ' . LT70_LESSONS_MAX . ' · ' . (string)$m['lesson']['title'];
        $goal = (string)$m['lesson']['goal'];
    }
    $time = trim((string)$sch['start']) !== '' ? (string)$sch['start'] . '–' . (string)$sch['end'] . ((string)$sch['room'] !== '' ? ' · učebna ' . (string)$sch['room'] : '') : 'čas není v rozvrhu';
    [$statusText, $tone] = lt791_status($m);
    $html = '<section class="ds78 lt791-hero" aria-labelledby="lt791-h1"><div class="lt791-hero__main"><p class="ds-eyebrow">' . e($when) . '</p>'
        . '<h1 id="lt791-h1">Dnešní hodina · ' . e(lt70_class_name($modules, $m['class'])) . '</h1>'
        . '<p class="lt791-hero__what">' . e($what) . '</p><dl class="lt791-facts">'
        . '<div><dt>Čas</dt><dd>' . e($time) . '</dd></div>'
        . '<div><dt>Cíl</dt><dd>' . ($goal !== '' ? e($goal) : '<span class="ds-muted">Lekce nemá zapsaný cíl – doplňte ho v přípravě.</span>') . '</dd></div>'
        . '<div><dt>Stav</dt><dd><span class="ds-badge is-' . e($tone) . '">' . e($statusText) . '</span></dd></div></dl></div>'
        . '<div class="lt791-hero__actions">' . lt791_primary_html($m) . '</div></section>';
    if ($slot['note'] !== '' && !(is_array($day) && $day['is_today'])) $html .= '<p class="c70-note" role="status">' . e($slot['note']) . ' Níže je nejbližší výuková hodina.</p>';
    if ($slot['override']) $html .= '<p class="c70-note" role="status">Prohlížíte lekci ' . (int)$slot['lesson'] . ', podle kalendáře je na řadě lekce ' . (int)$slot['calendar_lesson'] . '. '
        . lt70_tab_link('hodina', $m['class'], 'Zpět na lekci podle kalendáře') . '</p>';
    return $html;
}

/**
 * Primární akce podle stavu: dnes neotevřená hodina (a oprávnění) → formulář sess53_t_open (jediný na stránce, přesunutý z karty kódu);
 * otevřená hodina → promítnout kód; jinak projektor. Vedlejší: projektor / tisk.
 */
function lt791_primary_html(array $m): string
{
    $s = $m['session'];
    $extra = !empty($m['slot']['override']) ? ['lesson' => (int)$m['lesson']['number']] : [];
    $second = array_filter([lt70_tab_link('hodina_projektor', $m['class'], 'Spustit projektor', $extra, 'ds-btn ds-btn--secondary'), lt70_tab_link('hodina_tisk', $m['class'], 'Vytisknout hodinu', $extra, 'ds-btn ds-btn--secondary')]);
    if (lt791_can_open($m)) {
        return '<form method="post" action="teacher.php" class="lt791-open"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
            . '<input type="hidden" name="action" value="sess53_t_open"><input type="hidden" name="return_tab" value="hodina">'
            . '<input type="hidden" name="class_id" value="' . e($m['class']) . '"><input type="hidden" name="date" value="' . e($m['today']) . '">'
            . '<fieldset class="lt791-fs"><legend>Hodina s kódem pro žáky</legend><div class="ds-field"><label class="ds-label" for="c70-kind">Typ hodiny</label>'
            . '<select class="ds-input" id="c70-kind" name="kind" aria-describedby="lt791-kind-h"><option value="work">Samostatná práce k lekci ' . (int)$m['slot']['calendar_lesson'] . '</option><option value="intake">Seznamovací dotazník</option></select>'
            . '<p class="ds-help" id="lt791-kind-h">Po otevření dostanete kód, který žáci zadají po přihlášení.</p></div></fieldset>'
            . '<button class="ds-btn ds-btn--primary ds-btn--block" type="submit">Otevřít hodinu a vygenerovat kód</button></form>'
            . ($second !== [] ? '<p class="lt791-more">' . implode(' ', $second) . '</p>' : '');
    }
    if ($s['exists'] && $s['open']) {
        $primary = lt70_tab_link('session', $m['class'], 'Promítnout kód a hodnotit', [], 'ds-btn ds-btn--primary ds-btn--block');
        $code = '<p class="lt791-code">Kód hodiny: <strong>' . e($s['code']) . '</strong></p>';
        return $code . ($primary !== '' ? $primary : '') . ($second !== [] ? '<p class="lt791-more">' . implode(' ', $second) . '</p>' : '');
    }
    $primary = lt70_tab_link('hodina_projektor', $m['class'], 'Spustit projektor', $extra, 'ds-btn ds-btn--primary ds-btn--block');
    $rest = array_filter([lt70_tab_link('hodina_tisk', $m['class'], 'Vytisknout hodinu', $extra, 'ds-btn ds-btn--secondary'), lt70_tab_link('priprava79', $m['class'], 'Otevřít přípravu hodiny', [], 'ds-btn ds-btn--secondary')]);
    return $primary . ($rest !== [] ? '<p class="lt791-more">' . implode(' ', $rest) . '</p>' : '');
}

/** Formulář otevření hodiny smí dostat jen učitel se students.manage v den výuky bez existující hodiny (stejná podmínka jako v70). */
function lt791_can_open(array $m): bool
{
    return !$m['session']['exists'] && $m['slot']['status'] === 'today' && function_exists('teacher_permission') && teacher_permission('students.manage');
}

/** Plán po minutách (+ kroky, které vidí žáci). Exit ticket má vlastní sekci. */
function lt791_plan_html(array $m): string
{
    $l = $m['lesson'];
    $html = '<section class="c70-card" aria-labelledby="c70-plan"><p class="ds-eyebrow lt791-eyebrow">Průběh hodiny</p><h2 id="c70-plan">Plán po minutách</h2>';
    if ($l['timeline'] === []) {
        $html .= ux791_empty_html('Lekce zatím nemá plán po minutách', 'Plán se doplní z obsahu lekce. Než se tak stane, poslouží kroky lekce níže nebo Režim hodiny.',
            array_values(array_filter([c70_tab_visible('curriculum') ? [ux791_tab_url('curriculum', $m['class']), 'Připravit plán'] : null, c70_tab_visible('teach') ? [ux791_tab_url('teach', $m['class']), 'Otevřít Režim hodiny'] : null])), 'p');
    } else {
        $total = (int)end($l['timeline'])['to'];
        $html .= lt71_timeline_list($l['timeline'], 'Plán hodiny po minutách');
        if (!empty($l['timeline'][0]['derived'])) $html .= '<p class="c70-muted">Plán je dopočtený z kroků lekce (celkem ' . $total . ' min z ' . LM71_LESSON_MINUTES . ').</p>';
    }
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

/** Exit ticket: souhrn odpovědí v72 (když lekce ticket má) + otázky z modelu lekce; jinak vysvětlený prázdný stav. */
function lt791_exit_html(array $m): string
{
    $l = $m['lesson'];
    $questions = $l['exit_ticket'] !== [] ? '<h3>Otázky exit ticketu</h3>' . lt71_list($l['exit_ticket'])
        . (count($l['exit_ticket']) < LM71_EXIT_VARIANTS_MIN ? '<p class="c70-muted">Zatím jedna varianta – stejná otázka jako v kvízu lekce.</p>' : '') : '';
    $card = function_exists('lx72_teacher_card_html') ? lx72_teacher_card_html($m) : '';
    if ($card !== '') {
        $pos = strrpos($card, '</section>');
        return $pos === false ? $card . $questions : substr($card, 0, $pos) . $questions . '</section>';
    }
    $html = '<section class="c70-card" aria-labelledby="lt791-exit"><h2 id="lt791-exit">Exit ticket</h2>';
    if ($questions !== '') return $html . $questions . '</section>';
    return $html . ux791_empty_html('Lekce nemá exit ticket', 'Exit ticket přibude se schváleným návrhem obsahu lekce. Do té doby ověříte porozumění kvízem lekce.',
        array_values(array_filter([c70_tab_visible('schvalovani') ? [ux791_tab_url('schvalovani', $m['class'], ['lesson' => (string)(int)$l['number']]), 'Otevřít schvalování lekce'] : null])), 'p') . '</section>';
}

/** Hodina s kódem: stav a kód; formulář otevření je v hero kartě (jediný na stránce). */
function lt791_session_html(array $m): string
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
    if (!lt791_can_open($m)) return $html . '<p class="c70-muted">Hodina zatím není otevřená. Otevřít ji může učitel třídy.</p></section>';
    return $html . '<p class="c70-muted">Hodina zatím není otevřená. Otevřete ji tlačítkem „Otevřít hodinu a vygenerovat kód“ v kartě hodiny nahoře.</p></section>';
}

/** Zadané úkoly v70 s vysvětleným prázdným stavem. */
function lt791_tasks_html(array $m): string
{
    if ((int)$m['tasks']['active'] > 0) return lt70_tasks_html($m);
    $actions = array_values(array_filter([c70_tab_visible('class_results') ? [ux791_tab_url('class_results', $m['class']), 'Zadat úkol ve Výsledcích třídy'] : null,
        c70_tab_visible('student360') ? [ux791_tab_url('student360', $m['class']), 'Otevřít detail žáka'] : null]));
    return '<section class="c70-card" aria-labelledby="c70-tasks"><h2 id="c70-tasks">Zadané úkoly</h2>'
        . ux791_empty_html('Třída nemá žádné aktivní úkoly', 'Úkoly zadáváte hromadně ve Výsledcích třídy nebo jednotlivě v Detailu žáka. Žák je uvidí na svém přehledu.', $actions, 'p') . '</section>';
}

/**
 * T-14 pro třídu hodiny: lekce s návrhem obsahu, které se učí do 14 dní a nejsou schválené (lehká cesta jako odznak v77: overlay jen ze
 * souborů třídy, jedno čtení záznamů schválení). Termín schválení = den výuky − 14 dní; „po termínu“ = termín už uplynul.
 */
function lt791_t14_html(array $m, ?int $now = null): string
{
    if (!c70_tab_visible('streda77') || !function_exists('lc72_overlay_light') || !function_exists('lc72_lesson_dates')) return '';
    $today = date('Y-m-d', $now ?? time());
    $end = date('Y-m-d', (int)strtotime($today . ' +' . LT791_T14_DAYS . ' days'));
    try {
        $records = lc72_records();
        $rows = [];
        foreach (lc72_lesson_dates((string)$m['class']) as $n => $date) {
            if ($date === '' || $date < $today || $date > $end) continue;
            $ov = lc72_overlay_light((string)$m['class'], (int)$n);
            if ($ov === []) continue;
            $rec = $records[$m['class']][(string)$n] ?? null;
            if (is_array($rec) && (string)($rec['status'] ?? '') === 'schvaleno' && hash_equals((string)($rec['hash'] ?? ''), lc72_overlay_hash($ov))) continue;
            $deadline = date('Y-m-d', (int)strtotime($date . ' -' . LT791_T14_DAYS . ' days'));
            $rows[] = ['n' => (int)$n, 'date' => $date, 'late' => $deadline < $today];
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v79.1 T-14 v Dnešní hodině: ' . get_class($e));
        return '';
    }
    $html = '<section class="c70-card lt791-t14" aria-labelledby="lt791-t14-h"><h2 id="lt791-t14-h">T-14: schválit do výuky</h2>';
    $open = lt70_tab_link('streda77', $m['class'], 'Otevřít Středu: T-14', [], 'btn secondary');
    if ($rows === []) {
        return $html . ux791_empty_html('Na 14 dní dopředu je vše schválené', 'Žádná lekce této třídy, která se učí do 14 dní, nečeká na schválení.',
            [[ux791_tab_url('streda77', $m['class']), 'Otevřít Středu: T-14']], 'p') . '</section>';
    }
    $late = count(array_filter($rows, static fn(array $r): bool => $r['late']));
    $html .= '<p>' . e(count($rows) . ' ' . (count($rows) === 1 ? 'lekce čeká' : (count($rows) < 5 ? 'lekce čekají' : 'lekcí čeká')) . ' na schválení' . ($late > 0 ? ', z toho ' . $late . ' po termínu' : '') . '.') . '</p><ul class="lt791-t14-list">';
    foreach ($rows as $r) {
        $html .= '<li><strong>Lekce ' . $r['n'] . '</strong> · výuka ' . e(lt70_date($r['date'])) . ' <span class="c70-chip ' . ($r['late'] ? 'c70-chip-bad">po termínu T-14' : 'c70-chip-warn">schválit do výuky') . '</span></li>';
    }
    $bulk = c70_tab_visible('hromadne74') ? '<a class="btn primary" href="' . e('?' . http_build_query(['tab' => 'hromadne74', 'f' => '1', 'rozsah' => 't14', 'class' => $m['class'], 'co_lekce' => '1', 'co_svp' => '1'])) . '">Hromadně schválit</a> ' : '';
    return $html . '</ul><p class="c70-actions">' . $bulk . $open . '</p></section>';
}
