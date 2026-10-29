<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79_views.php';

/**
 * EDUCANET v79 · záložka „Příprava hodiny“ (`priprava79`, sekce Dnes): stepper Třída → Plán → Materiály → Tisk/Projektor.
 * JEN ČTENÍ – žádná nová zapisující akce. Výběr (třída a lekce) je v URL (?class=, ?lesson=, ?krok=) a jako rozpracovaný stav v relaci
 * (po návratu do záložky se pokračuje tam, kde učitel skončil). Data z lp73_model (jako Tisk/Projektor) a knihovny materiálů v74.
 * Poslední krok = souhrn a odkazy na existující záložky Tisk hodiny, Projektor, Zástup a Dnešní hodina se stejnou třídou a lekcí.
 * Registr modulu nese i POST prefix sp79_ (koncepty krokových postupů všech toků v79 – sp79_teacher_handle_post).
 */

const PP79_LABELS = ['Třída', 'Plán', 'Materiály', 'Tisk/Projektor'];

/** Parametry z GET; chyby výběru (třída mimo rozsah, lekce mimo 1–28). */
function pp79_request(array $classes): array
{
    $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $lessonRaw = is_string($_GET['lesson'] ?? null) ? trim((string)$_GET['lesson']) : '';
    $errors = [];
    $picked = sp79_get_step('priprava') > 1;
    if ($picked && !in_array($class, $classes, true)) $errors['pp79-class-0'] = 'Vyberte třídu ze seznamu.';
    if ($picked && $lessonRaw !== '' && (preg_match('/^\d{1,2}$/', $lessonRaw) !== 1 || (int)$lessonRaw < 1 || (int)$lessonRaw > LT70_LESSONS_MAX)) $errors['pp79-lesson'] = 'Vyberte lekci 1–' . LT70_LESSONS_MAX . '.';
    $lesson = preg_match('/^\d{1,2}$/', $lessonRaw) === 1 && (int)$lessonRaw >= 1 && (int)$lessonRaw <= LT70_LESSONS_MAX ? (int)$lessonRaw : 0;
    return [$class, $lesson, $errors];
}

function pp79_url(string $class, int $lesson, int $step): string
{
    return '?' . http_build_query(['tab' => 'priprava79'] + ($class !== '' ? ['class' => $class] : []) + ($lesson > 0 ? ['lesson' => (string)$lesson] : []) + ['krok' => (string)$step]);
}

/** Celá záložka. */
function pp79_render_tab(array $modules, string $classId): void
{
    $year = lt70_school_year();
    $classes = lt70_classes($modules, $year);
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Dnes · před hodinou</div><h1>Příprava hodiny</h1>'
        . '<p>Čtyři kroky: vyberte třídu a lekci, projděte plán po minutách a materiály a pak hodinu vytiskněte nebo pusťte na projektor. Nic se tu neukládá.</p></div></section>';
    if ($classes === []) {
        echo c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Příprava se ukáže, jakmile vám administrátor přidělí třídu (Správa → Učitelé).', []);
        return;
    }
    [$reqClass, $reqLesson, $errors] = pp79_request($classes);
    $draft = sp79_draft('priprava');
    $resume = sp79_get_step('priprava') === 0 && $reqClass === '' && in_array((string)($draft['data']['class'] ?? ''), $classes, true);
    if ($resume) [$reqClass, $reqLesson] = [(string)$draft['data']['class'], (int)($draft['data']['lesson'] ?? 0)];
    try {
        $m = lp73_model($modules, $errors === [] ? $reqClass : '', $errors === [] ? $reqLesson : 0);
    } catch (Throwable $e) {
        error_log('EDUCANET v79 příprava: ' . $e->getMessage());
        echo c70_empty_html('Podklady hodiny se teď nepodařilo sestavit.', 'Zkuste stránku obnovit. Plán hodiny najdete i v Dnešní hodině.', [['hodina', 'Dnešní hodina']]);
        return;
    }
    $class = (string)$m['class'];
    $n = (int)$m['lesson']['number'];
    $chosen = $errors === [] && ($reqClass !== '' || $resume);
    $cur = $errors !== [] ? 1 : sp79_current_step('priprava', $chosen ? 4 : 1, $resume ? max(1, (int)$draft['step']) : ($chosen ? 2 : 1));
    if ($chosen) sp79_draft_put('priprava', ['scope' => $class, 'step' => $cur, 'max' => 4, 'data' => ['class' => $class, 'lesson' => (string)$n]]);
    $w = sp79_words();
    echo sp79_bar_html(PP79_LABELS, $cur, $chosen ? 4 : 1, static fn(int $k): string => pp79_url($class, $n, $k), $w);
    if ($resume && sp79_get_step('priprava') === 0) echo sp79_resume_html('Pokračujete v rozpracované přípravě: ' . lt70_class_name($modules, $class) . ', lekce ' . $n . '.', pp79_url($class, $n, 1), 'Vybrat jinou třídu nebo lekci');
    echo sp79_errors_html($errors, $w);
    echo pp79_pick_panel($modules, $classes, $m, $cur, $errors);
    if (!$chosen) return;
    echo pp79_plan_panel($m, $cur) . pp79_materials_panel($m, $cur) . pp79_finish_panel($modules, $m, $cur);
}

/** Krok 1: výběr třídy a lekce (GET formulář, bez JS). */
function pp79_pick_panel(array $modules, array $classes, array $m, int $cur, array $errors): string
{
    $html = sp79_panel_open(1, $cur, PP79_LABELS[0]) . '<form method="get" action="teacher.php" class="c70-card c70-wide pp79-pick" data-sp79><input type="hidden" name="tab" value="priprava79"><input type="hidden" name="krok" value="2">'
        . '<fieldset class="pp79-classes"' . (isset($errors['pp79-class-0']) ? ' aria-describedby="pp79-class-0-err"' : '') . '><legend>Třída</legend>';
    foreach (array_values($classes) as $i => $c) {
        $html .= '<label class="pp79-choice" for="pp79-class-' . $i . '"><input type="radio" id="pp79-class-' . $i . '" name="class" value="' . e($c) . '" required' . ($c === $m['class'] ? ' checked' : '')
            . ($i === 0 ? ' data-sp79-msg="Vyberte třídu ze seznamu."' : '') . '> ' . e(lt70_class_name($modules, $c)) . '</label>';
    }
    $html .= '</fieldset>' . sp79_field_error($errors, 'pp79-class-0') . '<label class="pp79-lesson" for="pp79-lesson">Lekce <span class="c70-muted">(výchozí podle kalendáře)</span></label>'
        . '<select id="pp79-lesson" name="lesson"' . sp79_aria($errors, 'pp79-lesson', 'pp79-lesson-h') . '><option value="">Podle kalendáře (lekce ' . (int)$m['lesson']['number'] . ')</option>';
    for ($i = 1; $i <= LT70_LESSONS_MAX; $i++) $html .= '<option value="' . $i . '"' . (!empty($m['slot']['override']) && (int)$m['lesson']['number'] === $i ? ' selected' : '') . '>Lekce ' . $i . '</option>';
    return $html . '</select><p class="c70-muted" id="pp79-lesson-h">Jiná lekce = náhled jiné hodiny; kalendář se nemění.</p>' . sp79_field_error($errors, 'pp79-lesson')
        . '<div class="ds78 sp79-w"><div class="sp79-nav"><button class="ds-btn ds-btn--primary" type="submit" data-sp79-validate>Další: ' . PP79_LABELS[1] . '</button></div></div></form></section>';
}

/** Krok 2: plán po minutách, cíl a stav schválení. */
function pp79_plan_panel(array $m, int $cur): string
{
    $l = $m['lesson'];
    $status = (string)($l['meta']['status'] ?? '');
    $html = sp79_panel_open(2, $cur, PP79_LABELS[1]) . '<div class="c70-card c70-wide"><h3>Lekce ' . (int)$l['number'] . ' · ' . e(lp73_title($l)) . '</h3>'
        . ($status !== '' ? '<p><span class="c70-chip">' . e(function_exists('lc72_status_label') ? lc72_status_label($status) : $status) . '</span></p>' : '')
        . '<p><strong>Cíl:</strong> ' . e((string)$l['goal']['student'] !== '' ? (string)$l['goal']['student'] : 'Cíl lekce zatím není zapsaný.') . '</p>'
        . lp73_plan_table($l['timeline']);
    $tasks = lp73_task_texts($l, LP73_PRINT_TASKS_MAX);
    if ($tasks !== []) $html .= '<h3>Úkoly pro žáky</h3>' . lp73_list($tasks, 'lp73-list', 'ol');
    return $html . '</div>' . sp79_nav_links_html(pp79_url($m['class'], (int)$l['number'], 3), PP79_LABELS[2], pp79_url($m['class'], (int)$l['number'], 1), sp79_words()) . '</section>';
}

/** Materiály lekce: z modelu lekce a z knihovny v74 (povinné, ověřené). @return array{0:list<string>,1:list<array>} */
function pp79_materials(array $m): array
{
    $lines = lp73_material_lines($m['lesson']);
    $library = [];
    if (function_exists('mat74_for_lesson')) {
        try {
            $library = mat74_for_lesson($m['class'], (int)$m['lesson']['number'], array_column((array)$m['lesson']['topics'], 'key'));
        } catch (Throwable $e) {
            error_log('EDUCANET v79 příprava – materiály: ' . get_class($e));
        }
    }
    return [$lines, $library];
}

/** Krok 3: materiály. */
function pp79_materials_panel(array $m, int $cur): string
{
    [$lines, $library] = pp79_materials($m);
    $n = (int)$m['lesson']['number'];
    $html = sp79_panel_open(3, $cur, PP79_LABELS[2]) . '<div class="c70-card c70-wide"><h3>Z plánu lekce</h3>'
        . ($lines !== [] ? lp73_list($lines) : '<p class="c70-muted">Bez zvláštních materiálů – stačí aplikace a pracovní list.</p>') . '<h3>Z knihovny materiálů</h3>';
    if ($library === []) {
        $html .= '<p class="c70-muted">K lekci zatím není připojený žádný materiál z knihovny.</p>';
    } else {
        $html .= '<ul class="pp79-mats">';
        foreach (array_slice($library, 0, 12) as $it) {
            $html .= '<li>' . (function_exists('mat74_link_html') ? mat74_link_html((string)($it['url'] ?? ''), (string)$it['title']) : e((string)$it['title']))
                . ($it['required'] ? ' <span class="c70-chip c70-chip-warn">povinné</span>' : '') . ' <span class="c70-chip' . ($it['verified'] ? ' c70-chip-ok">ověřeno' : '">neověřeno') . '</span></li>';
        }
        $html .= '</ul>';
    }
    $lib = lt70_tab_link('materialy74', $m['class'], 'Knihovna materiálů k lekci ' . $n . ' →', ['lesson' => $n]);
    return $html . ($lib !== '' ? '<p class="c70-links">' . $lib . '</p>' : '') . '</div>'
        . sp79_nav_links_html(pp79_url($m['class'], $n, 4), PP79_LABELS[3], pp79_url($m['class'], $n, 2), sp79_words()) . '</section>';
}

/** Krok 4: souhrn a akce (existující záložky Tisk, Projektor, Zástup, Dnešní hodina). */
function pp79_finish_panel(array $modules, array $m, int $cur): string
{
    $l = $m['lesson'];
    $n = (int)$l['number'];
    [$lines, $library] = pp79_materials($m);
    $minutes = $l['timeline'] !== [] ? (int)end($l['timeline'])['to'] : 0;
    $html = sp79_panel_open(4, $cur, PP79_LABELS[3]) . '<div class="c70-card c70-wide">'
        . sp79_summary_html([
            ['Třída', lt70_class_name($modules, $m['class']), 1, pp79_url($m['class'], $n, 1)],
            ['Lekce', 'Lekce ' . $n . ' · ' . lp73_title($l) . ($m['slot']['date'] !== '' ? ' (' . lt70_date((string)$m['slot']['date']) . ')' : ''), 1, pp79_url($m['class'], $n, 1)],
            ['Plán', pp79_count(count($l['timeline']), 'úsek', 'úseky', 'úseků') . ($minutes > 0 ? ', ' . $minutes . ' min' : '') . ', ' . pp79_count(count(lp73_task_texts($l, 99)), 'úkol', 'úkoly', 'úkolů'), 2, pp79_url($m['class'], $n, 2)],
            ['Materiály', count($lines) . ' z plánu lekce, ' . count($library) . ' z knihovny', 3, pp79_url($m['class'], $n, 3)],
        ], sp79_words());
    $btn = static fn(string $tab, string $label, string $kind): string => c70_tab_visible($tab) ? '<a class="ds-btn ds-btn--' . $kind . '" href="' . e(teacher68_tab_url($tab, $m['class']) . '&lesson=' . $n) . '">' . e($label) . '</a>' : '';
    $actions = array_filter([$btn('hodina_projektor', 'Pustit na projektor', 'primary'), $btn('hodina_tisk', 'Vytisknout plán a pracovní list', 'secondary'),
        $btn('hodina_zastup', 'List pro zástup', 'secondary'), $btn('hodina', 'Otevřít Dnešní hodinu', 'secondary')]);
    return $html . '<div class="ds78 sp79-w"><div class="sp79-nav sp79-nav--wrap">' . implode('', $actions) . '<a class="ds-btn ds-btn--secondary" href="' . e(pp79_url($m['class'], $n, 3)) . '">Zpět</a></div></div></div></section>';
}

/** Počet s českým tvarem (1 úkol, 2–4 úkoly, 5+ úkolů). */
function pp79_count(int $n, string $one, string $few, string $many): string
{
    return $n . ' ' . ($n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many));
}
