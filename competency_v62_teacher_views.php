<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · cockpit učitele: mapa kompetencí třídy (záložka 'kompetence', vždy česky).
 *
 * Mapa žák × kompetence se stavem textem i ikonou, souhrn za třídu a tlačítko ručního přepočtu (comp62_sync,
 * POST s CSRF). Zobrazí se jen pilotní třídy v rozsahu učitele (deny-by-default: politika comp62_ v teacher_scope_v59.php
 * vyžaduje class_id v rozsahu). Retence a pilot jsou rozhodnutí školy (viz docs/KOMPETENCE_V62.md).
 */

require_once __DIR__ . '/competencies_v62.php';
require_once __DIR__ . '/evidence_v62.php';
require_once __DIR__ . '/evidence_v62_adapters.php';
require_once __DIR__ . '/mastery_v62.php';

/** @return list<string> pilotní třídy v rozsahu přihlášeného učitele */
function comp62_teacher_classes(): array
{
    $all = function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : COMP62_PILOT_CLASSES;
    return array_values(array_filter($all, 'comp62_enabled_for_class'));
}

/** class_3a → „3.A“ (jen pro popisky v cockpitu). */
function comp62_class_label(string $classId): string
{
    return preg_match('/^class_(\d)([a-z])$/', $classId, $m) === 1 ? $m[1] . '.' . strtoupper($m[2]) : $classId;
}

function comp62_teacher_can_class(string $classId): bool
{
    return $classId !== '' && in_array($classId, comp62_teacher_classes(), true);
}

/** POST comp62_sync: ruční přepočet důkazů třídy (každý učitel s rozsahem na třídu). */
function comp62_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if ($action === 'comp62_sync') {
        if (!comp62_teacher_can_class($classId)) throw new RuntimeException('Třída není v rozsahu nebo není v pilotu.');
        $sum = ['added' => 0, 'students' => 0, 'skipped' => 0, 'unmapped' => 0];
        foreach (array_keys(project_students_for_class($classId)) as $key) {
            $stat = ev62_sync_student($classId, (string)$key, true);
            if (isset($stat['skipped'])) { $sum['skipped']++; continue; }
            $sum['students']++;
            $sum['added'] += (int)$stat['added'];
            $sum['unmapped'] += (int)$stat['unmapped'];
        }
        ev62_memo_reset();
        $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => 'Přepočet kompetencí hotov: ' . $sum['students'] . ' žáků, nových důkazů ' . $sum['added']
            . ($sum['skipped'] > 0 ? ', přeskočeno ' . $sum['skipped'] . ' (bez identity)' : '') . ($sum['unmapped'] > 0 ? ', nenamapovaných aktivit ' . $sum['unmapped'] : '') . '.'];
    }
    if (function_exists('teacher_redirect')) teacher_redirect(['tab' => 'kompetence', 'class' => $classId]);
    redirect_to('teacher.php?tab=kompetence&class=' . rawurlencode($classId));
}

function comp62_teacher_cell(array $info): string
{
    $view = comp62_state_view_cs((string)$info['state']);
    $pct = $info['score'] === null ? '' : ' (' . (int)round((float)$info['score'] * 100) . " %)";
    return '<td><span class="c62-s-' . e((string)$info['state']) . '"><span aria-hidden="true">' . e($view['icon']) . '</span> ' . e($view['text']) . '</span>' . e($pct) . '</td>';
}

/** Ikona a český text stavu (cockpit nepoužívá tr()). @return array{icon:string,text:string} */
function comp62_state_view_cs(string $state): array
{
    $icons = ['upevneno' => '✓✓', 'zvladnuto' => '✓', 'rozpracovano' => '◐', 'neovereno' => '○'];
    $text = $state === 'neovereno' ? 'neověřeno' : m62_state_label($state);
    return ['icon' => $icons[$state] ?? '○', 'text' => $text];
}

/** @param array<string,array<string,mixed>> $competencies */
function comp62_teacher_table(string $classId, array $competencies, array $mastery): string
{
    $head = '<tr><th scope="col">Žák</th>';
    foreach ($competencies as $c) $head .= '<th scope="col">' . e((string)$c['label']) . '<br><small>' . e((string)$c['area_label'] . ' · ' . comp62_level_label((int)$c['level'])) . '</small></th>';
    $body = '';
    $rows = [];
    foreach (project_students_for_class($classId) as $s) {
        $id = identity58_id_for_student($classId, (string)$s['label']);
        $rows[] = [(string)$s['label'], is_string($id) ? ($mastery['students'][$id] ?? null) : null];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($a[0], $b[0]));
    foreach ($rows as [$label, $map]) {
        $body .= '<tr><th scope="row">' . e($label) . '</th>';
        foreach ($competencies as $id => $c) $body .= comp62_teacher_cell(is_array($map) ? ($map[$id] ?? ['state' => 'neovereno', 'score' => null]) : ['state' => 'neovereno', 'score' => null]);
        $body .= '</tr>';
    }
    $foot = '<tr><th scope="row">Třída: umí / učí se / zatím ne</th>';
    foreach ($competencies as $id => $c) {
        $s = $mastery['summary'][$id] ?? array_fill_keys(M62_STATES, 0);
        $foot .= '<td>' . ((int)$s['zvladnuto'] + (int)$s['upevneno']) . ' / ' . (int)$s['rozpracovano'] . ' / ' . (int)$s['neovereno'] . '</td>';
    }
    return '<div class="c62-scroll" tabindex="0" role="region" aria-label="Mapa kompetencí třídy, posuňte vodorovně"><table class="c62-map"><caption>Mapa kompetencí třídy ' . e(comp62_class_label($classId)) . '</caption>'
        . '<thead>' . $head . '</tr></thead><tbody>' . $body . '</tbody><tfoot>' . $foot . '</tr></tfoot></table></div>';
}

function comp62_render_teacher_tab(string $requestedClass, string $csrf): void
{
    $classes = comp62_teacher_classes();
    echo '<section class="teacher-panel c62-wrap"><h2>Kompetence a důkazy</h2>';
    if ($classes === []) {
        echo '<p class="teacher-empty">Kompetence zatím běží jen v pilotních třídách 3.A a 1.A a vy k nim nemáte přístup.</p></section>';
        return;
    }
    $classId = in_array($requestedClass, $classes, true) ? $requestedClass : $classes[0];
    $subject = (string)comp62_subject_for_class($classId);
    $competencies = comp62_competencies($subject);
    echo '<p class="c62-note">Stav kompetence vychází z posledních 8 důkazů (test, projekt, lab, lekce, hra, aréna) s útlumem po 60 dnech. '
        . 'Hra ani aréna samy zvládnutí nedají. Žák vidí jen svou mapu.</p>';
    echo '<form method="post" class="c62-sync"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="comp62_sync">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '"><button type="submit" aria-describedby="c62-sync-note">Přepočítat důkazy třídy</button>'
        . '<span class="c62-note" id="c62-sync-note">Pilot: ' . e(implode(', ', array_map('comp62_class_label', COMP62_PILOT_CLASSES))) . ' · důkazy se mažou ' . (int)COMP62_RETENTION_GRACE_DAYS . ' dní po odchodu žáka.</span></form>';
    echo comp62_teacher_table($classId, $competencies, m62_class($classId));
    echo '</section>';
}
