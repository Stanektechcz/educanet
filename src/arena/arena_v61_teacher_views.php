<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Učitelský přehled soubojů (modul 'souboje' v teacher58_modules()). Vždy česky, jen čtení.
 * Seznam i statistiky se filtrují na třídy učitele (teacher59_allowed_class_ids), admin vidí vše;
 * třída mimo rozsah v GET filtru se tiše ignoruje (nic nevyzradí). Nic nezapisuje – ani asistent nemá co měnit.
 */

require_once __DIR__ . '/arena_v61_duels.php';

const ARENA61_T_STATUS = ['pending' => 'Čeká na odpověď', 'accepted' => 'Probíhá', 'done' => 'Dokončeno', 'declined' => 'Odmítnuto', 'cancelled' => 'Zrušeno', 'expired' => 'Vypršelo'];

/** @return list<string> třídy, které smí přihlášený učitel vidět */
function arena61_teacher_classes(): array
{
    return function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : [];
}

/** Filtr z GET: neplatná hodnota nebo třída mimo rozsah = bez filtru (nikdy rozšíření rozsahu). */
function arena61_teacher_filters(array $allowed): array
{
    $class = is_string($_GET['duel_class'] ?? null) ? (string)$_GET['duel_class'] : '';
    $status = is_string($_GET['duel_status'] ?? null) ? (string)$_GET['duel_status'] : '';
    return ['class' => in_array($class, $allowed, true) ? $class : '', 'status' => isset(ARENA61_T_STATUS[$status]) ? $status : ''];
}

function arena61_teacher_filter_form(array $allowed, array $filters): string
{
    $html = '<form method="get" class="teacher-filter-row"><input type="hidden" name="tab" value="souboje"><label>Třída<select name="duel_class"><option value="">všechny moje</option>';
    foreach ($allowed as $c) { $html .= '<option value="' . e($c) . '"' . ($c === $filters['class'] ? ' selected' : '') . '>' . e($c) . '</option>'; }
    $html .= '</select></label><label>Stav<select name="duel_status"><option value="">vše</option>';
    foreach (ARENA61_T_STATUS as $value => $text) { $html .= '<option value="' . e($value) . '"' . ($value === $filters['status'] ? ' selected' : '') . '>' . e($text) . '</option>'; }
    return $html . '</select></label><button class="btn secondary" type="submit">Filtrovat</button></form>';
}

function arena61_teacher_stats_html(array $classIds, int $now): string
{
    $html = '';
    foreach ($classIds as $c) {
        $s = arena61_class_stats($c, $now);
        $top = [];
        foreach ($s['top_levels'] as $levelId => $n) { $top[] = (string)(lab57_level((string)$levelId)['title'] ?? $levelId) . ' (' . $n . '×)'; }
        $html .= '<article class="ui-card mot61-card"><h3>' . e($c) . '</h3><p>Souboje celkem: <strong>' . (int)$s['total'] . '</strong> · dokončeno ' . (int)$s['counts']['done']
            . ' · čeká ' . (int)$s['counts']['pending'] . ' · probíhá ' . (int)$s['counts']['accepted'] . ' · odmítnuto ' . (int)$s['counts']['declined'] . ' · vypršelo ' . (int)$s['counts']['expired'] . '.</p>'
            . '<p>Výzvy přijímá <strong>' . (int)$s['optin'] . '</strong> z ' . (int)$s['roster'] . ' žáků' . ($s['accept_percent'] === null ? '' : ', přijato ' . (int)$s['accept_percent'] . ' % zodpovězených výzev') . '.'
            . ($top ? ' Nejčastější úlohy: ' . e(implode(', ', $top)) . '.' : '') . '</p></article>';
    }
    return $html;
}

function arena61_teacher_table_html(array $rows, int $now): string
{
    $rosters = [];
    $out = '<div class="mot61-table-wrap"><table class="ui-table"><caption>Přehled soubojů (nejnovější první, nejvýš ' . ARENA61_TEACHER_ROW_LIMIT . ' řádků)</caption><thead><tr><th scope="col">Třída</th><th scope="col">Vyzyvatel</th><th scope="col">Vyzvaný</th><th scope="col">Úloha</th><th scope="col">Stav</th><th scope="col">Vítěz</th><th scope="col">Vytvořeno</th></tr></thead><tbody>';
    foreach (array_slice($rows, 0, ARENA61_TEACHER_ROW_LIMIT) as $row) {
        $c = (string)$row['class_id'];
        $rosters[$c] ??= arena57_roster($c);
        $name = static fn(string $key): string => arena57_full_label($key, '', $rosters[$c]);
        $winner = (string)$row['effective_winner'];
        $out .= '<tr><td>' . e($c) . '</td><td>' . e($name((string)$row['from_key'])) . '</td><td>' . e($name((string)$row['to_key'])) . '</td><td>'
            . e((string)(lab57_level((string)$row['level_id'])['title'] ?? $row['level_id'])) . '</td><td>' . e(ARENA61_T_STATUS[(string)$row['effective']] ?? (string)$row['effective']) . '</td><td>'
            . e($winner !== '' ? $name($winner) : '–') . '</td><td>' . e(date('j. n. Y H:i', (int)(strtotime((string)$row['created_at']) ?: $now))) . '</td></tr>';
    }
    return $out . '</tbody></table></div>';
}

function arena61_render_teacher_tab(string $classId, string $csrf): void
{
    $now = arena57_now();
    $allowed = arena61_teacher_classes();
    $filters = arena61_teacher_filters($allowed);
    $scope = $filters['class'] !== '' ? [$filters['class']] : $allowed;
    echo '<section class="teacher-panel"><h2>Souboje v Aréně</h2><p class="teacher-note">Přehled jen ke čtení. Souboje jsou dobrovolné: žák musí výzvy sám zapnout, body za ně nejsou a žákům se jména zobrazují podle jejich nastavení soukromí.</p>';
    echo arena61_teacher_filter_form($allowed, $filters);
    if ($scope === []) { echo '<p class="teacher-empty">Nemáte přiřazenou žádnou třídu.</p></section>'; return; }
    echo '<div class="teacher-grid">' . arena61_teacher_stats_html($scope, $now) . '</div>';
    $rows = arena61_rows($scope, $now, $filters['status']);
    echo $rows === [] ? '<p class="teacher-empty">Žádné souboje neodpovídají filtru.</p>' : arena61_teacher_table_html($rows, $now);
    echo '</section>';
}
