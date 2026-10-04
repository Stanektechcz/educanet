<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v61 · šablony učitelského přehledu třídy (vždy česky; všechna data přes e()). Logika: teacher_overview_v61.php. */

const OV61_OUTCOME_LABEL = ['confirmed' => 'Potvrzeno', 'already' => 'Beze změny', 'skipped' => 'Beze změny', 'forbidden' => 'Odmítnuto',
    'wrong_class' => 'Odmítnuto', 'missing' => 'Nenalezeno', 'error' => 'Chyba'];

function ov61_class_label(string $classId): string
{
    return function_exists('teacher_class_label') ? teacher_class_label($classId) : $classId;
}

function ov61_date(int $ts): string
{
    return $ts > 0 ? date('j. n. Y', $ts) : '—';
}

function ov61_render_class_nav(string $classId): void
{
    echo '<nav class="ov61-classes" aria-label="Třída přehledu">';
    foreach (ov61_allowed_classes() as $id) {
        echo '<a class="ov61-class" href="?tab=prehled&amp;class=' . e(rawurlencode($id)) . '"'
            . ($id === $classId ? ' aria-current="page"' : '') . '>' . e(ov61_class_label($id)) . '</a>';
    }
    echo '</nav>';
}

function ov61_render_kpis(array $data): void
{
    $items = [['Zaostává', count($data['lagging']), 'žáků ze ' . count($data['students'])], ['Nová hlášení', count($data['reports']), 'čeká na potvrzení'],
        ['Projekty', count($data['applications']), 'přihlášek k rozhodnutí'], ['Obchod', count($data['purchases']), 'nákupů za ' . OV61_PURCHASE_DAYS . ' dní']];
    echo '<ul class="ov61-kpis">';
    foreach ($items as [$label, $value, $hint]) {
        echo '<li><span>' . e($label) . '</span><strong>' . (int)$value . '</strong><small>' . e($hint) . '</small></li>';
    }
    echo '</ul>';
}

function ov61_render_lagging(array $data): void
{
    echo '<section class="teacher-panel ov61-panel" aria-labelledby="ov61-lag-h"><div class="teacher-panel-head"><div><span>Zaostávání</span><h2 id="ov61-lag-h">Kdo potřebuje pomoc</h2></div>'
        . '<small>Žádná aktivita ' . OV61_INACTIVE_DAYS . ' dní a víc, nebo mastery pod ' . (int)OV61_LOW_MASTERY . ' % u žáka, který už začal.</small></div>';
    if ($data['lagging'] === []) {
        echo '<p class="teacher-empty">Nikdo ze třídy podle těchto pravidel nezaostává.</p></section>';
        return;
    }
    echo '<div class="ov61-table-wrap"><table class="ov61-table"><caption class="ov61-sr">Žáci, kteří zaostávají</caption><thead><tr><th scope="col">Žák</th><th scope="col">Poslední aktivita</th><th scope="col">Mastery</th><th scope="col">Důvod</th></tr></thead><tbody>';
    foreach ($data['lagging'] as $s) {
        echo '<tr><th scope="row">' . e($s['label']) . '</th><td>' . e(ov61_date((int)$s['last'])) . '</td><td>'
            . ($s['mastery'] === null ? '—' : e(number_format((float)$s['mastery'], 0, ',', ' ')) . ' %') . '</td><td>' . e(implode('; ', $s['reasons'])) . '</td></tr>';
    }
    echo '</tbody></table></div></section>';
}

function ov61_render_reports(array $data, string $classId, string $csrf, bool $canManage, array $labels): void
{
    echo '<section class="teacher-panel ov61-panel" aria-labelledby="ov61-rep-h"><div class="teacher-panel-head"><div><span>Hlášení žáků</span><h2 id="ov61-rep-h">Čeká na potvrzení</h2></div>'
        . '<small>Potvrzení odměňuje výchozími body a XP podle typu, každé hlášení jen jednou.</small></div>';
    if ($data['reports'] === []) {
        echo '<p class="teacher-empty">Žádné nové hlášení.</p></section>';
        return;
    }
    // Formulář (CSRF + akce) jen pro toho, kdo smí rozhodovat; asistent dostane čistě čtecí seznam.
    echo $canManage
        ? '<form method="post" class="ov61-bulk" data-ov61-bulk><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="ov61_bulk_confirm"><input type="hidden" name="class_id" value="' . e($classId) . '">'
        : '<div class="ov61-bulk">';
    if ($canManage) echo '<label class="ov61-check ov61-all"><input type="checkbox" data-ov61-all> Vybrat vše</label>';
    echo '<ul class="ov61-list">';
    foreach (array_slice($data['reports'], 0, OV61_LIST_MAX) as $row) {
        $id = (string)$row['id'];
        $who = $labels[ov61_hash((string)$row['student_key'])] ?? 'Žák';
        echo '<li>' . ($canManage ? '<label class="ov61-check"><input type="checkbox" name="ids[]" value="' . e($id) . '"><span class="ov61-sr">Vybrat hlášení: </span>' : '<div class="ov61-check">')
            . '<span><strong>' . e((string)$row['title']) . '</strong><small>' . e($who) . ' · ' . e(ov61_date(ov61_ts($row['created_at'] ?? ''))) . ' · ' . e($row['type'] === 'bug' ? 'chyba' : 'návrh') . '</small></span>'
            . ($canManage ? '</label>' : '</div>') . '</li>';
    }
    echo '</ul>';
    if (count($data['reports']) > OV61_LIST_MAX) echo '<p class="ov61-note">Zobrazeno prvních ' . OV61_LIST_MAX . ' z ' . count($data['reports']) . ' – zbytek najdeš v záložce Hlášení.</p>';
    echo $canManage ? '<button class="btn primary" type="submit">Potvrdit vybraná hlášení</button>'
        : '<p class="ov61-note">Asistent hlášení potvrzovat nemůže – rozhodnutí patří učiteli.</p>';
    echo ' <a class="btn secondary" href="?tab=hlaseni&amp;fb_class=' . e(rawurlencode($classId)) . '">Otevřít záložku Hlášení</a>' . ($canManage ? '</form>' : '</div>') . '</section>';
}

function ov61_render_applications_and_purchases(array $data, string $classId, array $labels): void
{
    echo '<section class="teacher-panel ov61-panel" aria-labelledby="ov61-app-h"><div class="teacher-panel-head"><div><span>Projekty a obchod</span><h2 id="ov61-app-h">Další čeká na rozhodnutí</h2></div></div><div class="ov61-two">';
    echo '<div><h3>Přihlášky na projekty</h3>';
    if ($data['applications'] === []) echo '<p class="teacher-empty">Žádná přihláška nečeká.</p>';
    else {
        echo '<ul class="ov61-list">';
        foreach (array_slice($data['applications'], 0, OV61_LIST_MAX) as $app) {
            $who = $labels[ov61_hash((string)($app['student_key'] ?? ''))] ?? 'Žák';
            echo '<li><span><strong>' . e((string)($app['project']['title'] ?? 'Projekt')) . '</strong><small>' . e($who) . ' · ' . e(ov61_date(ov61_ts($app['at'] ?? ''))) . '</small></span></li>';
        }
        echo '</ul>';
    }
    echo '<a class="btn secondary" href="?tab=projekty&amp;class=' . e(rawurlencode($classId)) . '">Rozhodnout v záložce Projekty</a></div>';
    echo '<div><h3>Nákupy k případnému vrácení</h3>';
    if ($data['purchases'] === []) echo '<p class="teacher-empty">Za ' . OV61_PURCHASE_DAYS . ' dní žádný nevrácený nákup.</p>';
    else {
        echo '<ul class="ov61-list">';
        foreach (array_slice($data['purchases'], 0, OV61_LIST_MAX) as $buy) {
            echo '<li><span><strong>' . e($buy['title']) . '</strong><small>' . e($labels[$buy['hash']] ?? 'Žák') . ' · ' . (int)$buy['cost'] . ' b · ' . e(ov61_date(ov61_ts($buy['at']))) . '</small></span></li>';
        }
        echo '</ul>';
    }
    echo '<a class="btn secondary" href="?tab=obchod&amp;class=' . e(rawurlencode($classId)) . '">Vrátit v záložce Obchod</a></div></div></section>';
}

/** Výsledek posledního hromadného potvrzení po položkách (zobrazí se jednou). */
function ov61_render_last_result(): void
{
    $results = $_SESSION['ov61_result'] ?? null;
    unset($_SESSION['ov61_result']);
    if (!is_array($results) || $results === []) return;
    echo '<section class="teacher-panel ov61-panel" aria-labelledby="ov61-res-h" role="status" aria-live="polite"><h2 id="ov61-res-h">Výsledek hromadného potvrzení</h2><ul class="ov61-list">';
    foreach ($results as $r) {
        if (!is_array($r)) continue;
        echo '<li class="ov61-res ov61-res-' . e((string)($r['outcome'] ?? '')) . '"><span><strong>' . e((string)($r['title'] ?? '') !== '' ? (string)$r['title'] : 'Hlášení')
            . '</strong><small>' . e(OV61_OUTCOME_LABEL[(string)($r['outcome'] ?? '')] ?? '') . ' – ' . e((string)($r['text'] ?? '')) . '</small></span></li>';
    }
    echo '</ul></section>';
}

/** Záložka „Přehled třídy“ (modul `prehled`). */
function ov61_render_teacher_tab(string $requestedClass, string $csrf): void
{
    $classId = ov61_pick_class(is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $requestedClass);
    $scope = function_exists('teacher_overview_scope_label') ? teacher_overview_scope_label(ov61_allowed_classes()) : '';
    echo '<section class="teacher-page-head ov61-head"><div><div class="eyebrow">Přehled třídy' . ($scope !== '' ? ' · rozsah ' . e($scope) : '') . '</div>'
        . '<h1>Kdo zaostává a co čeká na schválení</h1><p>Jedna obrazovka pro třídu: zaostávající žáci, hlášení, přihlášky na projekty a nákupy.</p></div>';
    if ($classId !== '') echo '<a class="btn secondary" href="?tab=prehled&amp;export=1&amp;class=' . e(rawurlencode($classId)) . '" download>Exportovat do CSV</a>';
    echo '</section>';
    if ($classId === '') {
        echo '<p class="teacher-empty">Nemáte přiřazenou žádnou třídu. Požádejte administrátora o přidělení.</p>';
        return;
    }
    ov61_render_class_nav($classId);
    ov61_render_last_result();
    $data = ov61_overview($classId);
    $labels = ov61_labels(project_students_for_class($classId));
    $canManage = !function_exists('teacher_permission') || teacher_permission('content.manage');
    ov61_render_kpis($data);
    ov61_render_lagging($data);
    ov61_render_reports($data, $classId, $csrf, $canManage, $labels);
    ov61_render_applications_and_purchases($data, $classId, $labels);
}
