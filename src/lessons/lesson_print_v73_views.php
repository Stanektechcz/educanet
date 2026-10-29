<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · Tisk hodiny (`hodina_tisk`) a list pro zástup (`hodina_zastup`) – jen čtení, data z lesson_prep_v73.php.
 *   Tisk: A4 strana 1 = plán hodiny po minutách, cíl a kritéria, materiály, bezpečnost, výsledky ŠVP; A4 strana 2 = pracovní list žáka;
 *   volitelná strana 3 = kontrolní list třídy (jména podle volby učitele, při skrytí „Žák N“).
 *   Zástup: jedna A4 bez jmen – co žáci dělají, plán B offline, jak otevřít kód hodiny, bezpečnost, kontakt na koordinaci z konfigurace.
 * Každý výstup přes e(); žádný formulář, žádná nová POST akce ani GET parametr (jen obecné ?class= a ?lesson=). Texty česky.
 */

/** Společný rámec chyb / prázdného stavu; null = model je v pořádku. */
function lp73_guarded_model(array $modules, string $what): ?array
{
    [$requested, $lesson] = lp73_request();
    try {
        $m = lp73_model($modules, $requested, $lesson);
    } catch (Throwable $e) {
        error_log('EDUCANET v73 ' . $what . ': ' . $e->getMessage());
        echo c70_empty_html('Podklady hodiny se teď nepodařilo sestavit.', 'Zkuste stránku obnovit. Plán hodiny najdete i v Dnešní hodině.', [['hodina', 'Dnešní hodina']]);
        return null;
    }
    if ($m['class'] === '') {
        echo c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Podklady se ukážou, jakmile vám administrátor přidělí třídu (Správa → Učitelé).', []);
        return null;
    }
    return $m;
}

/** Hlavička A4 stránky: třída, lekce, datum a čas bloku. */
function lp73_sheet_head(array $m, array $modules, string $kind): string
{
    $l = $m['lesson'];
    $sch = $m['schedule'];
    $when = $m['slot']['date'] !== '' ? lt70_date((string)$m['slot']['date']) : '';
    $time = trim((string)$sch['start']) !== '' ? (string)$sch['start'] . '–' . (string)$sch['end'] . ((string)$sch['room'] !== '' ? ', učebna ' . (string)$sch['room'] : '') : '';
    return '<header class="lp73-sheet-head"><p class="lp73-kind">' . e($kind) . ' · ' . e(lt70_class_name($modules, $m['class'])) . '</p>'
        . '<h2>Lekce ' . (int)$l['number'] . ' · ' . e(lp73_title($l)) . '</h2>'
        . '<p class="lp73-meta">' . e(implode(' · ', array_filter([$when, $time]))) . '</p></header>';
}

/** Lišta jen na obrazovce: přepínání lekcí, tlačítko tisku (JS) a návod bez JS. */
function lp73_toolbar(array $m, string $tab, string $title, string $hint): string
{
    $n = (int)$m['lesson']['number'];
    $nav = [];
    if ($n > 1) $nav[] = lt70_tab_link($tab, $m['class'], '← Lekce ' . ($n - 1), ['lesson' => $n - 1]);
    if ($m['slot']['override']) $nav[] = lt70_tab_link($tab, $m['class'], 'Lekce podle kalendáře');
    if ($n < LT70_LESSONS_MAX) $nav[] = lt70_tab_link($tab, $m['class'], 'Lekce ' . ($n + 1) . ' →', ['lesson' => $n + 1]);
    $nav[] = lt70_tab_link('hodina', $m['class'], 'Zpět na Dnešní hodinu', $m['slot']['override'] ? ['lesson' => $n] : []);
    return '<section class="teacher-page-head lp73-toolbar lp73-noprint"><div><div class="eyebrow">Příprava hodiny</div><h1>' . e($title) . '</h1><p>' . e($hint) . '</p>'
        . '<p class="lp73-actions"><button type="button" class="btn lp73-print-btn" data-lp73-print hidden>Vytisknout</button>'
        . '<span class="c70-muted">Tisk: Ctrl+P (na Macu ⌘+P), formát A4, okraje výchozí.</span></p>'
        . '<nav class="lp73-nav" aria-label="Jiná lekce">' . implode(' ', array_filter($nav)) . '</nav></div></section>';
}

function lp73_list(array $items, string $class = 'lp73-list', string $tag = 'ul'): string
{
    return '<' . $tag . ' class="' . e($class) . '">' . implode('', array_map(static fn(string $t): string => '<li>' . e($t) . '</li>', $items)) . '</' . $tag . '>';
}

/** Plán po minutách jako tabulka (obrazovka: posun uvnitř bloku; tisk: celá šířka). */
function lp73_plan_table(array $timeline): string
{
    if ($timeline === []) return '<p class="c70-muted">Lekce zatím nemá plán po minutách – doplňte ho v přípravě (Plán a kurikulum).</p>';
    $html = '<div class="lp73-scroll" role="region" aria-label="Plán hodiny po minutách" tabindex="0"><table class="lp73-table"><caption class="c70-sr">Plán hodiny po minutách</caption>'
        . '<thead><tr><th scope="col">Min</th><th scope="col">Fáze</th><th scope="col">Učitel</th><th scope="col">Žáci</th><th scope="col">Forma</th></tr></thead><tbody>';
    foreach ($timeline as $s) {
        $html .= '<tr><td class="lp73-min">' . (int)$s['from'] . '–' . (int)$s['to'] . '</td><th scope="row">' . e((string)$s['phase']) . '</th><td>' . e((string)$s['teacher']) . '</td>'
            . '<td>' . e((string)$s['student']) . '</td><td>' . e((string)$s['form']) . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}

/** Výchozí bezpečnostní pravidla, když lekce žádná nemá. @return list<string> */
function lp73_default_safety(): array
{
    return ['Sítě, servery a útoky zkoušíme jen v simulátoru Linux Labu – nikdy na školní ani cizí síti.', 'Žádné osobní údaje (své ani cizí) do zadání, souborů ani AI nástrojů.',
        'Obrázky a podklady jen s licencí, která dovoluje použití; zdroj uvést.'];
}

/** Strana 1: plán hodiny pro učitele. */
function lp73_page_plan(array $m, array $modules): string
{
    $l = $m['lesson'];
    $safety = $l['safety'] !== [] ? $l['safety'] : lp73_default_safety();
    $html = '<article class="lp73-page" aria-labelledby="lp73-p1">' . lp73_sheet_head($m, $modules, 'Plán hodiny') . '<h3 id="lp73-p1" class="c70-sr">Strana 1 – plán hodiny</h3>';
    $html .= '<section class="lp73-block"><h3>Cíl</h3><p class="lp73-goal">' . e((string)$l['goal']['student'] !== '' ? (string)$l['goal']['student'] : 'Cíl lekce zatím není zapsaný.') . '</p>';
    if ($l['goal']['success_criteria'] !== []) $html .= '<h4>Kritéria úspěchu</h4>' . lp73_list($l['goal']['success_criteria']);
    $html .= '</section><section class="lp73-block"><h3>Plán po minutách</h3>' . lp73_plan_table($l['timeline']) . '</section><div class="lp73-cols">';
    $materials = lp73_material_lines($l);
    $html .= '<section class="lp73-block"><h3>Materiály a nástroje</h3>' . ($materials !== [] ? lp73_list($materials) : '<p class="c70-muted">Bez zvláštních materiálů – stačí aplikace a pracovní list.</p>') . '</section>';
    $html .= '<section class="lp73-block"><h3>Bezpečnost</h3>' . lp73_list($safety) . '</section></div>';
    $svp = $m['svp'];
    $html .= '<section class="lp73-block"><h3>Výsledky vzdělávání (ŠVP)</h3>';
    if ($svp['rows'] === []) {
        $html .= '<p class="c70-muted">Lekce zatím nemá vazbu na ŠVP.</p>';
    } else {
        $html .= lp73_list(array_map(static fn(array $r): string => $r['text'] . ($r['subject'] !== '' ? ' (' . $r['subject'] . ($r['year'] > 0 ? ', ' . $r['year'] . '. r.' : '') . ')' : ''), $svp['rows']))
            . '<p class="lp73-small">' . e($svp['status'] . ($svp['source'] !== '' ? ' · zdroj: ' . $svp['source'] : '')) . '</p>';
    }
    $html .= '</section>';
    $questions = lp73_exit_questions($l);
    $notes = array_slice((array)$l['teacher_notes'], 0, 3);
    if ($questions !== [] || $notes !== []) {
        $html .= '<div class="lp73-cols">';
        if ($questions !== []) $html .= '<section class="lp73-block"><h3>Exit ticket (otázky)</h3>' . lp73_list($questions, 'lp73-list', 'ol') . '</section>';
        if ($notes !== []) $html .= '<section class="lp73-block"><h3>Poznámky pro učitele</h3>' . lp73_list(array_map('strval', $notes)) . '</section>';
        $html .= '</div>';
    }
    return $html . '</article>';
}

/** Strana 2: pracovní list žáka (bez jmen – žák si vyplní sám). */
function lp73_page_worksheet(array $m, array $modules): string
{
    $l = $m['lesson'];
    $tasks = lp73_task_texts($l, LP73_PRINT_TASKS_MAX);
    $html = '<article class="lp73-page lp73-worksheet" aria-labelledby="lp73-p2">' . lp73_sheet_head($m, $modules, 'Pracovní list') . '<h3 id="lp73-p2" class="c70-sr">Strana 2 – pracovní list žáka</h3>'
        . '<p class="lp73-fill"><span>Jméno: <i></i></span><span>Datum: <i></i></span></p>';
    if ((string)$l['goal']['student'] !== '') $html .= '<section class="lp73-block"><h3>Dnešní cíl</h3><p class="lp73-goal">' . e((string)$l['goal']['student']) . '</p></section>';
    $html .= '<section class="lp73-block"><h3>Úkoly</h3>';
    if ($tasks === []) {
        $html .= '<p class="c70-muted">Úkoly najdeš v lekci v aplikaci (kroky lekce).</p>';
    } else {
        $html .= '<ol class="lp73-tasks">';
        foreach ($tasks as $t) $html .= '<li><p>' . e($t) . '</p><span class="lp73-lines" aria-hidden="true"></span></li>';
        $html .= '</ol>';
    }
    $html .= '</section>';
    if ($l['worksheet'] !== []) $html .= '<section class="lp73-block"><h3>Co mám mít hotové</h3>' . lp73_list(array_map('strval', $l['worksheet']), 'lp73-checks') . '</section>';
    if ($l['goal']['success_criteria'] !== []) {
        $html .= '<section class="lp73-block"><h3>Jak jsem na tom</h3><ul class="lp73-self">';
        foreach ($l['goal']['success_criteria'] as $c) $html .= '<li><span>' . e((string)$c) . '</span><span class="lp73-boxes" aria-hidden="true">☐ umím · ☐ skoro · ☐ zatím ne</span></li>';
        $html .= '</ul></section>';
    }
    return $html . '<p class="lp73-small">Exit ticket vyplníš na konci hodiny v aplikaci EDUCANET.</p></article>';
}

/** Strana 3 (volitelná): kontrolní list třídy – jména podle volby učitele. */
function lp73_page_checklist(array $m, array $modules): string
{
    $labels = lp73_roster_labels($m['class']);
    if ($labels === []) return '';
    $criteria = array_slice(array_map('strval', (array)$m['lesson']['goal']['success_criteria']), 0, 4);
    $cols = $criteria !== [] ? $criteria : ['Úkoly hotové', 'Odevzdáno'];
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $html = '<article class="lp73-page lp73-optional" aria-labelledby="lp73-p3">' . lp73_sheet_head($m, $modules, 'Kontrolní list třídy (volitelná strana)')
        . '<h3 id="lp73-p3" class="c70-sr">Strana 3 – kontrolní list třídy</h3>'
        . '<p class="lp73-small lp73-noprint">' . e($hidden ? 'Jména jsou skrytá (Nastavení výuky) – místo nich „Žák 1, 2, …“.' : 'Jména jsou zobrazená; skrýt je můžete v Nastavení výuky.')
        . ' Stranu netiskněte, pokud ji nepotřebujete (v dialogu tisku zvolte stránky 1–2).</p>'
        . '<div class="lp73-scroll" role="region" aria-label="Kontrolní list třídy" tabindex="0"><table class="lp73-table lp73-check"><caption class="c70-sr">Kontrolní list třídy</caption><thead><tr><th scope="col">Žák</th>';
    foreach ($cols as $c) $html .= '<th scope="col">' . e(mb_strimwidth($c, 0, 48, '…')) . '</th>';
    $html .= '<th scope="col">Poznámka</th></tr></thead><tbody>';
    foreach ($labels as $label) $html .= '<tr><th scope="row">' . e($label) . '</th>' . str_repeat('<td></td>', count($cols)) . '<td class="lp73-note"></td></tr>';
    return $html . '</tbody></table></div></article>';
}

/** Záložka „Tisk hodiny“. */
function lp73_render_print(array $modules): void
{
    $m = lp73_guarded_model($modules, 'tisk');
    if ($m === null) return;
    echo '<div class="lp73" data-lp73>' . lp73_toolbar($m, 'hodina_tisk', 'Tisk hodiny', 'Strana 1: plán hodiny pro vás. Strana 2: pracovní list pro žáky. Strana 3: volitelný kontrolní list třídy.')
        . lp73_page_plan($m, $modules) . lp73_page_worksheet($m, $modules) . lp73_page_checklist($m, $modules) . '</div>';
}

/** Rozdělí pokyny pro zástup na „plán B offline“ a ostatní pokyny. @return array{0:list<string>,1:list<string>} */
function lp73_split_substitution(array $lines): array
{
    $planB = [];
    $other = [];
    foreach ($lines as $line) {
        $line = (string)$line;
        if (preg_match('/^\s*Pl[aá]n\s*B\b\s*(?:offline)?\s*[:–-]?\s*/iu', $line, $mm) === 1) $planB[] = lp73_sentence(mb_substr($line, mb_strlen($mm[0])));
        else $other[] = lp73_sentence((string)preg_replace('/^\s*Zástup bez odborníka\s*:\s*/u', '', $line));
    }
    return [array_values(array_filter($planB)), array_values(array_filter($other))];
}

/** Záložka „Zástup“: jedna A4 bez jmen. */
function lp73_render_substitution(array $modules): void
{
    $m = lp73_guarded_model($modules, 'zástup');
    if ($m === null) return;
    $l = $m['lesson'];
    [$planB, $other] = lp73_split_substitution((array)$l['substitution']);
    if ($planB === []) $planB = ['Žáci pracují na vytištěném pracovním listu (strana 2 v Tisku hodiny) – úkoly vyřeší na papír.', 'Kdo je hotový, zapíše tři věci, které se dnes naučil, a jednu otázku pro vyučujícího.'];
    $safety = array_values(array_unique(array_merge($l['safety'] !== [] ? $l['safety'] : lp73_default_safety(),
        ['Na školních počítačích nic neinstalujte a neměňte nastavení sítě ani účtů.', 'Při úrazu nebo mimořádné události postupujte podle školního řádu a ihned volejte kontakt níže.'])));
    $flow = [];
    foreach ($l['timeline'] as $s) {
        $text = lp73_segment_student_text($s);
        $flow[] = (int)$s['from'] . '–' . (int)$s['to'] . ' min · ' . (string)$s['phase'] . ($text !== '' ? ': ' . $text : '');
    }
    echo '<div class="lp73" data-lp73>' . lp73_toolbar($m, 'hodina_zastup', 'List pro zástup', 'Jedna strana A4 pro zastupujícího – bez jmen žáků. Přístup zastupujícího do systému zatím není (čeká na rozhodnutí školy).')
        . '<article class="lp73-page lp73-sub" aria-labelledby="lp73-sub">' . lp73_sheet_head($m, $modules, 'Podklady pro zastupujícího') . '<h3 id="lp73-sub" class="c70-sr">List pro zástup</h3>'
        . '<section class="lp73-block"><h3>Cíl hodiny</h3><p class="lp73-goal">' . e((string)$l['goal']['student'] !== '' ? (string)$l['goal']['student'] : (string)$l['title']) . '</p></section>'
        . '<section class="lp73-block"><h3>Co žáci dělají</h3>' . ($other !== [] ? lp73_list($other) : '') . ($flow !== [] ? lp73_list($flow, 'lp73-list lp73-flow', 'ol') : '<p>Žáci pokračují v lekci ' . (int)$l['number'] . ' v aplikaci podle kroků lekce.</p>') . '</section>'
        . '<div class="lp73-cols"><section class="lp73-block"><h3>Plán B offline</h3>' . lp73_list($planB) . '</section>'
        . '<section class="lp73-block"><h3>Jak otevřít kód hodiny</h3>' . lp73_list(lp73_code_steps($m['session']), 'lp73-list', 'ol') . '</section></div>'
        . '<section class="lp73-block"><h3>Bezpečnost</h3>' . lp73_list($safety) . '</section>'
        . '<section class="lp73-block lp73-contact"><h3>Kontakt na koordinaci zástupů</h3><p>' . e(lp73_substitution_contact()) . '</p></section>'
        . '<p class="lp73-small">Výsledky a odevzdání zkontroluje vyučující po návratu. Zastupující nic nehodnotí a nezapisuje.</p></article></div>';
}
