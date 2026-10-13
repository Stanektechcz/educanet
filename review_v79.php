<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79_views.php';

/**
 * EDUCANET v79 · Opakování před písemkou jako stepper: Témata (rozsah lekcí od–do) → Náhled balíčku → Zveřejnit.
 * Výběr je jen v URL (?od=, ?do=, ?krok=) a jako rozpracovaný stav v relaci (poslední rozsah třídy); zveřejnění i skrytí
 * odesílají beze změny op75_publish / op75_close. Cockpit česky, bez jmen žáků.
 */

const RV79_LABELS = ['Témata', 'Náhled balíčku', 'Zveřejnit'];

/** Chyby rozsahu z GET (prázdné = v pořádku nebo nezadáno). @return array<string,string> */
function rv79_range_errors(array $get): array
{
    $err = [];
    $od = is_string($get['od'] ?? null) ? trim((string)$get['od']) : '';
    $do = is_string($get['do'] ?? null) ? trim((string)$get['do']) : '';
    $ok = static fn(string $v): bool => preg_match('/^\d{1,2}$/', $v) === 1 && (int)$v >= 1 && (int)$v <= 28;
    if (!$ok($od)) $err['rv75-od'] = 'Zadejte „Od lekce“ jako číslo 1–28.';
    if (!$ok($do)) $err['rv75-do'] = 'Zadejte „Do lekce“ jako číslo 1–28.';
    elseif ($ok($od) && (int)$do < (int)$od) $err['rv75-do'] = '„Do lekce“ musí být stejná nebo vyšší než „Od lekce“ (' . (int)$od . ').';
    return $err;
}

function rv79_url(string $class, int $from, int $to, int $step): string
{
    return rv75_tab_url($class, ($from > 0 ? ['od' => (string)$from, 'do' => (string)$to] : []) + ['krok' => (string)$step]);
}

/** Celá záložka po krocích (volá rv75_render_teacher_tab po výběru třídy). */
function rv79_render(array $modules, string $class, string $csrf): void
{
    $current = rv75_pack($class);
    $hasRange = isset($_GET['od']) || isset($_GET['do']);
    $errors = $hasRange ? rv79_range_errors($_GET) : [];
    [$from, $to] = rv75_range($_GET);
    $draft = sp79_draft_for('opakovani', $class);
    if (!$hasRange && (int)($draft['data']['od'] ?? 0) > 0) [$from, $to] = [(int)$draft['data']['od'], (int)$draft['data']['do']];
    elseif (!$hasRange && $current !== null) [$from, $to] = [(int)$current['from'], (int)$current['to']];
    $valid = $errors === [] && $from >= 1 && $to >= $from;
    $flash = sp79_teacher_flash();
    $justDone = $hasRange && $valid && $flash !== null && $flash['type'] === 'ok' && (int)$draft['step'] === 3;
    $default = $justDone ? 3 : ($hasRange ? 2 : ($valid && (int)$draft['step'] > 1 ? (int)$draft['step'] : 1));
    $cur = $valid ? sp79_current_step('opakovani', 3, $default) : 1;
    if ($valid) sp79_draft_put('opakovani', ['scope' => $class, 'step' => $cur, 'max' => 3, 'data' => ['od' => $from, 'do' => $to]]);
    $w = sp79_words();
    echo sp79_bar_html(RV79_LABELS, $cur, $valid ? 3 : 1, static fn(int $k): string => rv79_url($class, $from, $to, $k), $w) . sp79_errors_html($errors, $w);
    $can = function_exists('teacher_permission') && teacher_permission('content.manage');
    $field = static fn(string $id, string $name, string $label, int $value) => '<div class="rv79-field"><label for="' . $id . '">' . $label . '</label><input id="' . $id . '" name="' . $name . '" type="number" inputmode="numeric" min="1" max="28" value="'
        . ($value > 0 ? $value : e(is_string($_GET[$name] ?? null) ? (string)$_GET[$name] : '')) . '" required data-sp79-msg="' . e('Zadejte číslo lekce 1–28.') . '"' . sp79_aria($errors, $id) . '>' . sp79_field_error($errors, $id) . '</div>';
    echo sp79_panel_open(1, $cur, RV79_LABELS[0]) . '<div class="c70-card c70-wide">'
        . ($current !== null ? '<p role="status"><strong>Zveřejněno:</strong> L' . (int)$current['from'] . '–L' . (int)$current['to'] . ' (' . e(date('j. n. Y', (int)strtotime((string)$current['created_at']))) . ', ' . e((string)$current['by']) . ').</p>' : '<p class="c70-muted">Třída teď nemá zveřejněné opakování.</p>')
        . '<p>Vyberte, které lekce písemka pokrývá. Balíček se složí z chyb třídy v exit ticketech a z úloh k tématům rozsahu.</p>'
        . '<form method="get" class="rv75-range" data-sp79><input type="hidden" name="tab" value="opakovani75"><input type="hidden" name="class" value="' . e($class) . '"><input type="hidden" name="krok" value="2">'
        . $field('rv75-od', 'od', 'Od lekce', $errors === [] ? $from : 0) . $field('rv75-do', 'do', 'Do lekce', $errors === [] && $to > 0 ? max($from, $to) : 0)
        . '<div class="ds78 sp79-w"><div class="sp79-nav"><button class="ds-btn ds-btn--primary" type="submit" data-sp79-validate>Další: ' . RV79_LABELS[1] . '</button></div></div></form></div></section>';
    if (!$valid) return;
    $pack = rv75_build($class, is_array($modules[$class] ?? null) ? $modules[$class] : [], $from, $to);
    echo sp79_panel_open(2, $cur, RV79_LABELS[1] . ' L' . $pack['from'] . '–L' . $pack['to']) . '<div class="c70-card c70-wide">'
        . '<p class="c70-muted">Podklad: ' . $pack['answers'] . ' odpovědí z exit ticketů v rozsahu.</p>' . rv75_exits_html($pack['exits'], $pack['answers'], true) . '<h3>Úlohy k procvičení</h3>';
    if ($pack['tasks'] === []) echo '<p class="c70-muted">' . (tb75_tasks_for_class($class) === [] ? 'Tahle třída zatím nemá banku úloh (začínáme sítěmi a Linuxem pro 3.A a 4.A).' : 'K tématům rozsahu není slabé místo v bance úloh (třída má ≥ ' . RV75_TASK_WEAK_PCT . ' % správně), nebo se témata úloh v rozsahu neprobírají.') . '</p>';
    else {
        echo '<ul class="kp75-paths">';
        foreach ($pack['tasks'] as $t) echo '<li>' . e(TB75_TASKS[$t['task']]['title']) . ' – ' . e($t['untried'] ? 'třída zatím nezkusila' : $t['rate'] . ' % správně z ' . $t['attempts'] . ' pokusů') . '</li>';
        echo '</ul>';
    }
    echo '</div>' . sp79_nav_links_html(rv79_url($class, $from, $to, 3), RV79_LABELS[2], rv79_url($class, $from, $to, 1), $w) . '</section>';
    rv79_publish_panel($class, $pack, $current, $cur, $can, $csrf, $justDone);
}

/** Krok 3: souhrn před zveřejněním, tisk a původní formuláře op75_publish / op75_close. */
function rv79_publish_panel(string $class, array $pack, ?array $current, int $cur, bool $can, string $csrf, bool $justDone): void
{
    echo sp79_panel_open(3, $cur, RV79_LABELS[2]) . '<div class="c70-card c70-wide">';
    if ($justDone && $current !== null) echo sp79_status_html('ok', 'Hotovo – opakování L' . (int)$current['from'] . '–L' . (int)$current['to'] . ' je zveřejněné.', 'Žáci ho najdou v menu Učení → Opakování před písemkou.');
    $empty = $pack['exits'] === [] && $pack['tasks'] === [];
    echo sp79_summary_html([
        ['Rozsah lekcí', 'L' . $pack['from'] . '–L' . $pack['to'], 1, rv79_url($class, $pack['from'], $pack['to'], 1)],
        ['Otázky z exit ticketů', (string)count($pack['exits']), 2, rv79_url($class, $pack['from'], $pack['to'], 2)],
        ['Úlohy k procvičení', (string)count($pack['tasks']), 2, ''],
        ['Co se stane', $empty ? 'Balíček je prázdný – není co zveřejnit.' : ($current !== null ? 'Nahradí zveřejněné opakování L' . (int)$current['from'] . '–L' . (int)$current['to'] . '.' : 'Žáci uvidí opakování hned po zveřejnění.'), 0, ''],
    ], sp79_words());
    echo '<p><a class="btn secondary" href="' . e(rv75_tab_url($class, ['od' => (string)$pack['from'], 'do' => (string)$pack['to'], 'tisk' => '1'])) . '">Verze k tisku s klíčem</a></p>';
    if (!$can) { echo '<p class="c70-muted">Zveřejnit může učitel s oprávněním ke správě obsahu (asistent jen čte).</p></div></section>'; return; }
    $hidden = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="class_id" value="' . e($class) . '"><input type="hidden" name="return_tab" value="opakovani75">'
        . '<input type="hidden" name="od" value="' . $pack['from'] . '"><input type="hidden" name="do" value="' . $pack['to'] . '">';
    echo '<div class="ds78 sp79-w"><div class="sp79-nav">';
    if (!$empty) echo '<form method="post" class="kp75-form">' . $hidden . '<input type="hidden" name="action" value="op75_publish"><button class="ds-btn ds-btn--primary" type="submit">' . ($current !== null ? 'Nahradit zveřejněné opakování' : 'Zveřejnit žákům') . '</button></form>';
    if ($current !== null) echo '<form method="post" class="kp75-form">' . $hidden . '<input type="hidden" name="action" value="op75_close"><button class="ds-btn ds-btn--secondary" type="submit">Skrýt opakování</button></form>';
    echo '<a class="ds-btn ds-btn--secondary" href="' . e(rv79_url($class, $pack['from'], $pack['to'], 2)) . '">Zpět</a></div></div>';
    if ($empty) echo '<p class="c70-muted" role="status">Zkuste širší rozsah, nebo počkejte na odpovědi z exit ticketů.</p>';
    echo '</div></section>';
}
