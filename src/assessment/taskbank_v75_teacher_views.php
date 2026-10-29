<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/taskbank_v75.php';

/**
 * EDUCANET v75 · záložka cockpitu `ulohy75` (česky, jen čtení): úspěšnost třídy po úlohách, přehled žáků a klíč k variantě
 * konkrétního žáka (?zak=N = pořadí v tabulce, žádný klíč ani jméno v URL; ?kolo= jiné kolo). Jména podle volby učitele (tp721).
 */

function tb75_tab_url(string $classId, array $extra = []): string
{
    return '?' . http_build_query(['tab' => 'ulohy75', 'class' => $classId] + $extra);
}

function tb75_render_teacher_tab(array $modules, string $classId): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : array_keys(TB75_CLASS_SETS);
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · procvičování</div><h1>Úlohy s variantami</h1>'
        . '<p>Každý žák dostane stejný typ úlohy s vlastními čísly (varianta je spočítaná z jeho pseudonymu a čísla kola), takže opisování nemá smysl. '
        . 'Odpovědi se kontrolují automaticky; po 3 chybách se žákovi ukáže řešení a další pokus je jen v nové variantě. Nic se nespouští ani nikam nepřipojuje – vše je výpočet. Úlohy nedávají body ani známku.</p></div></section>';
    if ($class === '') { echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Úlohy se ukážou, jakmile vám administrátor přidělí třídu.', []) : ''; return; }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(tb75_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    $tasks = tb75_tasks_for_class($class);
    if ($tasks === []) {
        echo function_exists('c70_empty_html') ? c70_empty_html('Tahle třída zatím sadu úloh nemá.', 'Banka začíná sítěmi (podsítě, masky, porty, směrování) a Linuxem (cesty, chmod, grep) pro 3.A a 4.A. Grafické úlohy pro 1.A a 2.A jsou v plánu.', [['cesty', 'Výukové cesty']], $class) : '';
        return;
    }
    $data = tb75_data();
    $stats = tb75_class_stats($class, null, $data);
    $students = function_exists('project_students_for_class') ? array_values(array_map(static fn($k, $s): array => ['key' => (string)$k, 'label' => (string)($s['label'] ?? $k)], array_keys(project_students_for_class($class)), project_students_for_class($class))) : [];
    $hidden = function_exists('tp721_names_hidden') && tp721_names_hidden();
    $active = count(array_filter($stats, static fn(array $s): bool => $s['students'] > 0));
    $attempts = array_sum(array_column($stats, 'attempts'));
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Úlohy v sadě', (string)count($tasks), in_array($class, ['class_1a', 'class_2a'], true) ? 'grafika a web' : 'síť a Linux', ''],
        ['Úlohy s pokusem', (string)$active, 'aspoň jeden žák zkusil', ''],
        ['Pokusy celkem', (string)$attempts, 'za celé období', ''],
        ['Žáci ve třídě', (string)count($students), $hidden ? 'jména skrytá (Nastavení výuky)' : 'se jménem', ''],
    ], 'Souhrn úloh');
    echo '<section class="c70-card c70-wide" aria-labelledby="tb75-t-stats"><h2 id="tb75-t-stats">Úspěšnost po úlohách</h2>';
    if ($attempts === 0) echo '<p class="c70-muted" role="status">Zatím nikdo neodevzdal žádnou úlohu. Pošlete žákům odkaz <strong>Učení → Úlohy s variantami</strong> nebo promítněte ukázkovou variantu níže.</p>';
    echo '<div class="kp75-scroll"><table class="kp75-table"><caption class="c70-sr">Úspěšnost po úlohách</caption><thead><tr><th scope="col">Úloha</th><th scope="col">Žáků</th><th scope="col">Pokusů</th><th scope="col">Správně</th><th scope="col">Nejčastější chyba</th></tr></thead><tbody>';
    foreach ($tasks as $t) {
        $s = $stats[$t];
        $worst = '';
        $max = 0;
        $labels = array_column(tb75_variant($t, 'ukazka', 1)['fields'], 'label', 'id');
        foreach ($s['fields'] as $fid => $f) if ($f['wrong'] > $max) { $max = $f['wrong']; $worst = (string)($labels[$fid] ?? $fid) . ' (' . $f['wrong'] . '× chybně)'; }
        echo '<tr><th scope="row">' . e(TB75_TASKS[$t]['title']) . '</th><td>' . $s['students'] . '</td><td>' . $s['attempts'] . '</td><td>' . ($s['attempts'] > 0 ? $s['rate'] . ' %' : '—') . '</td><td>' . e($worst !== '' ? $worst : '—') . '</td></tr>';
    }
    echo '</tbody></table></div></section>';
    $sel = is_string($_GET['zak'] ?? null) && preg_match('/^\d{1,3}$/', (string)$_GET['zak']) === 1 ? (int)$_GET['zak'] : 0;
    echo tb75_students_html($class, $students, $tasks, $data, $hidden, $sel);
    if ($sel >= 1 && isset($students[$sel - 1])) echo tb75_key_html($class, $students[$sel - 1], $sel, $tasks, $data, $hidden);
    else echo tb75_sample_html($tasks);
}

function tb75_students_html(string $class, array $students, array $tasks, array $data, bool $hidden, int $sel): string
{
    $html = '<section class="c70-card c70-wide" aria-labelledby="tb75-t-st"><h2 id="tb75-t-st">Žáci</h2>';
    if ($students === []) return $html . '<p class="c70-muted">Třída zatím nemá žáky v seznamu.</p></section>';
    $html .= '<p class="c70-muted">Číslo = počet vyřešených variant, „…“ = rozpracováno. Klíč k variantě žáka otevřete odkazem v prvním sloupci.</p>'
        . '<div class="kp75-scroll"><table class="kp75-table"><caption class="c70-sr">Úlohy podle žáků</caption><thead><tr><th scope="col">Žák</th>';
    foreach ($tasks as $t) $html .= '<th scope="col">' . e(TB75_TASKS[$t]['title']) . '</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($students as $i => $s) {
        $label = function_exists('tp721_student_label') ? tp721_student_label($s['label'], $i + 1, $hidden) : $s['label'];
        $state = tb75_student_state($class, tb75_student_ref($class, $s['key']), $data);
        $html .= '<tr' . ($sel === $i + 1 ? ' class="is-selected"' : '') . '><th scope="row"><a href="' . e(tb75_tab_url($class, ['zak' => (string)($i + 1)])) . '">' . e($label) . '</a></th>';
        foreach ($tasks as $t) {
            $e = tb75_entry($state, $t);
            $html .= '<td>' . ($e['solved'] > 0 ? (string)$e['solved'] : '') . ($e['tries'] > 0 && !$e['last_ok'] ? '…' : '') . ($e['solved'] === 0 && $e['tries'] === 0 ? '<span class="c70-muted">–</span>' : '') . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table></div></section>';
}

function tb75_variant_html(array $v, string $headingId, string $title): string
{
    $html = '<article class="tb75-key" aria-labelledby="' . e($headingId) . '"><h3 id="' . e($headingId) . '">' . e($title) . '</h3><p>' . e((string)$v['prompt']) . '</p>';
    if ($v['data'] !== []) $html .= '<pre class="tb75-pre">' . e(implode("\n", $v['data'])) . '</pre>';
    $html .= '<dl class="tb75-dl">';
    foreach ($v['fields'] as $f) $html .= '<dt>' . e((string)$f['label']) . '</dt><dd><code>' . e((string)$f['answer']) . '</code></dd>';
    return $html . '</dl><p class="c70-muted">' . e((string)$v['explain']) . '</p></article>';
}

function tb75_key_html(string $class, array $student, int $index, array $tasks, array $data, bool $hidden): string
{
    $label = function_exists('tp721_student_label') ? tp721_student_label($student['label'], $index, $hidden) : $student['label'];
    $ref = tb75_student_ref($class, $student['key']);
    $round = is_string($_GET['kolo'] ?? null) && preg_match('/^\d{1,3}$/', (string)$_GET['kolo']) === 1 ? max(1, (int)$_GET['kolo']) : 0;
    $html = '<section class="c70-card c70-wide" aria-labelledby="tb75-t-key"><h2 id="tb75-t-key">Klíč k variantám: ' . e($label) . '</h2>'
        . '<p class="c70-muted">' . ($round > 0 ? 'Kolo ' . $round . ' u všech úloh. ' : 'Aktuální kolo každé úlohy (to, co žák právě vidí). ') . 'Varianta je pevně daná pseudonymem žáka a kolem – stejný klíč dostanete i příště.</p>'
        . '<form method="get" class="tb75-round"><input type="hidden" name="tab" value="ulohy75"><input type="hidden" name="class" value="' . e($class) . '"><input type="hidden" name="zak" value="' . $index . '">'
        . '<label for="tb75-kolo">Jiné kolo</label><input id="tb75-kolo" name="kolo" type="number" inputmode="numeric" min="1" max="999" value="' . ($round > 0 ? $round : '') . '"><button class="btn secondary" type="submit">Ukázat</button></form><div class="tb75-keys">';
    foreach ($tasks as $t) {
        $e = tb75_entry(tb75_student_state($class, $ref, $data), $t);
        $r = $round > 0 ? $round : $e['round'];
        $status = $e['round'] === $r ? ($e['last_ok'] && $e['tries'] > 0 ? 'vyřešeno' : ($e['shown'] ? 'ukázáno řešení' : ($e['tries'] > 0 ? $e['tries'] . ' z ' . TB75_TRIES . ' pokusů' : 'nezačato'))) : 'jiné kolo';
        $html .= tb75_variant_html(tb75_variant($t, $ref, $r), 'tb75-k-' . $t, TB75_TASKS[$t]['title'] . ' · kolo ' . $r . ' · ' . $status);
    }
    return $html . '</div><p><a class="c70-link" href="' . e(tb75_tab_url($class)) . '">← Zpět na ukázkovou variantu</a></p></section>';
}

function tb75_sample_html(array $tasks): string
{
    $html = '<section class="c70-card c70-wide" aria-labelledby="tb75-t-sample"><h2 id="tb75-t-sample">Ukázková varianta k promítnutí</h2>'
        . '<p class="c70-muted">Tuto variantu nedostane žádný žák – hodí se k vysvětlení postupu před třídou. Klíč ke konkrétnímu žákovi otevřete v tabulce žáků.</p><div class="tb75-keys">';
    foreach ($tasks as $t) $html .= tb75_variant_html(tb75_variant($t, 'ukazka', 1), 'tb75-s-' . $t, TB75_TASKS[$t]['title']);
    return $html . '</div></section>';
}
