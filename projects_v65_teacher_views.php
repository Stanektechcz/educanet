<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/projects_v65_teacher_actions.php';

/**
 * v65 · Učitelský cockpit cyklu projektů (modul 'projekty65', vždy česky). Fronta práce učitele je záměrně krátká:
 * návrhy ke schválení (hromadně max. 50), odevzdané práce k hodnocení rubrikou, moderace peer textů, přínos týmů.
 * Rozsah tříd vynucuje teacher59_guard_post(); tady se zobrazuje jen vybraná třída, která už je v rozsahu.
 */

function p65t_form(string $csrf, string $action, string $classId, string $inner, string $class = 't-form'): string
{
    return '<form method="post" class="' . e($class) . '"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '">' . $inner . '</form>';
}

function p65t_label(string $classId, array $record): string
{
    if ((string)$record['target_type'] === 'group') return 'Tým ' . (string)(project_group_find((string)$record['target_id'])['name'] ?? '');
    return adaptive_student_label($classId, (string)$record['target_id']);
}

function proj65_render_teacher_tab(string $classId, string $csrf): void
{
    echo '<section class="teacher-panel p65"><h2>Projekty: cyklus, rubriky a peer review</h2>'
        . '<p class="teacher-note">Rubrika jen navrhuje známku, rozhoduješ ty. Peer review nikdy neovlivní skóre, známku ani důkaz kompetence. Recenzenta vidíš jen ty, autor ne.</p>';
    if (project_catalog()[$classId] ?? null) {
        p65t_render_settings($classId, $csrf);
        $records = proj65_for_class($classId);
        p65t_render_proposals($classId, $csrf, $records);
        p65t_render_grading($classId, $csrf, $records);
        p65t_render_peer($classId, $csrf);
        p65t_render_teams($classId, $csrf, $records);
    } else {
        echo '<p class="teacher-empty">Tahle třída nemá katalogové projekty.</p>';
    }
    echo '</section>';
}

function p65t_render_settings(string $classId, string $csrf): void
{
    echo '<h3>Nastavení projektů</h3><p class="teacher-note">Výchozí je malý projekt (zkrácený cyklus bez návrhu, milníků a peer review). Plný cyklus zapni jen tam, kde se vyplatí.</p>';
    foreach ((array)project_catalog()[$classId] as $project) {
        $pid = (string)$project['id'];
        $s = proj65_settings($classId, $pid);
        $inner = '<input type="hidden" name="project_id" value="' . e($pid) . '"><label>Režim <select name="cycle_mode"><option value="small"' . ($s['cycle_mode'] === 'small' ? ' selected' : '') . '>Malý (zkrácený)</option>'
            . '<option value="full"' . ($s['cycle_mode'] === 'full' ? ' selected' : '') . '>Plný</option></select></label>'
            . '<label class="t-check"><input type="checkbox" name="peer" value="1"' . ($s['peer'] ? ' checked' : '') . '> Peer review (jen plný)</label>'
            . '<label class="t-check"><input type="checkbox" name="milestones" value="1"' . ($s['milestones'] ? ' checked' : '') . '> Milníky týmu (jen plný)</label>'
            . '<button class="btn secondary" type="submit">Uložit</button>';
        echo '<details class="p65-card"><summary><strong>' . e((string)$project['title']) . '</strong> · ' . ($s['cycle_mode'] === 'full' ? 'plný' : 'malý') . '</summary>'
            . p65t_form($csrf, 'proj65_t_settings', $classId, $inner) . p65t_rubric_form($classId, $pid, $csrf);
        if ($s['peer']) echo p65t_form($csrf, 'proj65_t_peer_open', $classId, '<input type="hidden" name="project_id" value="' . e($pid) . '"><button class="btn secondary" type="submit">Otevřít peer review (min. ' . PROJ65_PEER_MIN_SUBMISSIONS . ' odevzdané práce)</button>');
        echo '</details>';
    }
}

function p65t_rubric_form(string $classId, string $projectId, string $csrf): string
{
    $rubric = proj65_rubric_for(['class_id' => $classId, 'project_id' => $projectId, 'kind' => 'cat']);
    if ($rubric === null) return '';
    $subject = comp62_subject_for_class($classId);
    $comps = $subject !== null ? comp62_competencies($subject) : [];
    $inner = '<input type="hidden" name="project_id" value="' . e($projectId) . '">';
    foreach ($rubric['criteria'] as $c) {
        $id = (string)$c['id'];
        $inner .= '<fieldset><legend>' . e((string)$c['title']) . ' (max ' . (int)$c['max'] . ' b.)</legend>'
            . '<label>Název <input type="text" name="criteria[' . e($id) . '][title]" maxlength="80" value="' . e((string)$c['title']) . '"></label>'
            . '<label>Popis <input type="text" name="criteria[' . e($id) . '][description]" maxlength="300" value="' . e((string)$c['description']) . '"></label>';
        if ($comps !== []) {
            $inner .= '<label>Kompetence <select name="criteria[' . e($id) . '][competency]"><option value="">– žádná –</option>';
            foreach ($comps as $cid => $comp) $inner .= '<option value="' . e((string)$cid) . '"' . ((string)$c['competency'] === (string)$cid ? ' selected' : '') . '>' . e((string)$comp['label']) . '</option>';
            $inner .= '</select></label>';
        }
        foreach ([1, 2, 3, 4] as $lvl) $inner .= '<label>Úroveň ' . $lvl . ' <input type="text" name="criteria[' . e($id) . '][level' . $lvl . ']" maxlength="200" value="' . e((string)$c['levels'][$lvl]) . '"></label>';
        $inner .= '</fieldset>';
    }
    return '<details><summary>Rubrika (' . ($rubric['source'] === 'clone' ? 'upravená' : 'šablona') . '): klonovat a upravit</summary>'
        . p65t_form($csrf, 'proj65_t_rubric_clone', $classId, $inner . '<button class="btn secondary" type="submit">Uložit rubriku</button>') . '</details>';
}

function p65t_render_proposals(string $classId, string $csrf, array $records): void
{
    $rows = array_values(array_filter($records, static fn(array $r): bool => (string)$r['state'] === 'proposal'));
    echo '<h3>Návrhy ke schválení (' . count($rows) . ')</h3>';
    if ($rows === []) { echo '<p class="teacher-empty">Žádné návrhy.</p>'; return; }
    $inner = '<table class="teacher-table"><thead><tr><th>Vybrat</th><th>Žák / tým</th><th>Projekt</th><th>Návrh</th></tr></thead><tbody>';
    foreach (array_slice($rows, 0, 200) as $r) {
        $inner .= '<tr><td><input type="checkbox" name="refs[]" value="' . e((string)$r['ref']) . '" aria-label="Vybrat návrh"></td><td>' . e(p65t_label($classId, $r)) . '</td><td>' . e((string)$r['title']) . '</td><td>' . e((string)$r['pitch']) . '</td></tr>';
    }
    echo p65t_form($csrf, 'proj65_t_bulk_approve', $classId, $inner . '</tbody></table><button class="btn primary" type="submit">Schválit vybrané (max. ' . PROJ65_BULK_MAX . ' najednou)</button>');
    foreach (array_slice($rows, 0, 20) as $r) {
        echo p65t_form($csrf, 'proj65_t_reject', $classId, '<input type="hidden" name="ref" value="' . e((string)$r['ref']) . '"><button class="btn secondary" type="submit">Vrátit k úpravě: ' . e(p65t_label($classId, $r)) . '</button>', 't-inline-form');
    }
}

function p65t_render_grading(string $classId, string $csrf, array $records): void
{
    $rows = array_values(array_filter($records, static fn(array $r): bool => in_array((string)$r['state'], ['submitted', 'peer_review', 'graded', 'portfolio'], true)));
    echo '<h3>Odevzdané práce (' . count($rows) . ')</h3>';
    if ($rows === []) { echo '<p class="teacher-empty">Zatím nic odevzdaného.</p>'; return; }
    foreach ($rows as $r) {
        $rubric = proj65_rubric_for($r);
        if ($rubric === null) continue;
        $versions = array_values(array_filter((array)$r['versions'], 'is_array'));
        $last = $versions !== [] ? $versions[count($versions) - 1] : ['levels' => (array)(($r['draft']['levels'] ?? [])), 'comments' => (array)(($r['draft']['comments'] ?? []))];
        $url = safe_url((string)($r['submission']['url'] ?? ''));
        echo '<details class="p65-card"><summary><strong>' . e(p65t_label($classId, $r)) . '</strong> · ' . e((string)$r['title']) . ' · ' . e(proj65_state_label((string)$r['state'])) . ($versions !== [] ? ' · verze ' . count($versions) : '') . '</summary>'
            . ($url !== '' ? '<p><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">Odevzdaná práce</a></p>' : '') . '<p>' . e((string)($r['submission']['note'] ?? '')) . '</p>'
            . p65t_grade_form($classId, $csrf, $r, $rubric, $last) . '</details>';
    }
}

function p65t_grade_form(string $classId, string $csrf, array $record, array $rubric, array $last): string
{
    $inner = '<input type="hidden" name="ref" value="' . e((string)$record['ref']) . '">';
    foreach ($rubric['criteria'] as $c) {
        $id = (string)$c['id'];
        $inner .= '<fieldset class="p65-levels"><legend>' . e((string)$c['title']) . '</legend>';
        foreach ([1, 2, 3, 4] as $lvl) {
            $inner .= '<label><input type="radio" name="levels[' . e($id) . ']" value="' . $lvl . '"' . ((int)($last['levels'][$id] ?? 0) === $lvl ? ' checked' : '') . ' required> ' . $lvl . ' · ' . e((string)$c['levels'][$lvl]) . '</label>';
        }
        $inner .= '<label>Komentář (u úrovně 1 povinný) <input type="text" name="comments[' . e($id) . ']" maxlength="400" value="' . e((string)($last['comments'][$id] ?? '')) . '"></label></fieldset>';
    }
    $inner .= '<label>Silné stránky <input type="text" name="strengths" maxlength="2000"></label><label>Další krok <input type="text" name="next_step" maxlength="2000"></label>'
        . '<label>Komentář učitele <input type="text" name="teacher_comment" maxlength="4000"></label>'
        . '<label>Známka (nechej prázdné = návrh z bodů) <select name="grade"><option value="">návrh z bodů</option>' . implode('', array_map(static fn(int $g): string => '<option value="' . $g . '">' . $g . '</option>', [1, 2, 3, 4, 5])) . '</select></label>'
        . '<p class="p65-inline"><button class="btn secondary" type="submit" name="publish" value="0">Uložit koncept</button><button class="btn primary" type="submit" name="publish" value="1">Publikovat hodnocení</button></p>';
    return p65t_form($csrf, 'proj65_t_grade', $classId, $inner);
}

function p65t_render_peer(string $classId, string $csrf): void
{
    $rows = proj65_peer_teacher_rows($classId);
    echo '<h3>Peer review: moderace (' . count($rows) . ')</h3>';
    if ($rows === []) { echo '<p class="teacher-empty">Žádné recenze.</p>'; return; }
    echo '<table class="teacher-table"><thead><tr><th>Recenzent</th><th>Práce</th><th>Stav</th><th>Text</th><th>Kalibrace</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $cal = $r['calibration'];
        $calText = $cal['n'] === 0 ? 'bez dat' : 'páry ' . $cal['n'] . ', r=' . ($cal['pearson'] === null ? 'n/a (min. ' . PROJ65_PEARSON_MIN_PAIRS . ' párů)' : number_format((float)$cal['pearson'], 2, ',', '')) . ', MAE=' . number_format((float)$cal['mae'], 2, ',', '') . ($cal['deviation'] ? ' – ODCHYLKA' : '') . ($cal['calibrated'] ? ' – zkalibrovaný' : '');
        echo '<tr><td>' . e(adaptive_student_label($classId, (string)$r['reviewer_key'])) . '</td><td>' . e((string)$r['work_title']) . '</td><td>' . e((string)$r['state']) . '</td>'
            . '<td>' . e((string)$r['strength']) . '<br>' . e((string)$r['suggestion']) . '</td><td>' . e($calText) . '</td><td>';
        if (in_array((string)$r['state'], ['submitted', 'flagged', 'approved', 'rejected'], true)) {
            foreach (['approve' => 'Schválit', 'reject' => 'Zamítnout'] as $d => $label) {
                echo p65t_form($csrf, 'proj65_t_moderate', $classId, '<input type="hidden" name="review_id" value="' . e((string)$r['id']) . '"><input type="hidden" name="decision" value="' . $d . '"><button class="btn secondary" type="submit">' . $label . '</button>', 't-inline-form');
            }
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
}

function p65t_render_teams(string $classId, string $csrf, array $records): void
{
    $teams = array_values(array_filter($records, static fn(array $r): bool => (string)$r['target_type'] === 'group'));
    if ($teams === []) return;
    echo '<h3>Týmy: přínos členů</h3><p class="teacher-note">Faktor 0,8–1,2 je jen návrh z rozdělení 100 bodů. Do hodnocení se zapíše až po tvém potvrzení. Tým do 2 členů faktor nemá.</p>';
    foreach ($teams as $r) {
        $members = proj65_member_keys($r);
        $factors = proj65_contribution_factor((array)($r['splits'] ?? []), $members);
        $delta = proj65_factor_suggestion($r);
        echo '<div class="p65-card"><strong>' . e(p65t_label($classId, $r)) . '</strong> · ' . e((string)$r['title']) . ' · členů ' . count($members) . ', deník: ' . count(proj65_diary_list((string)$r['ref'])) . ' zápisů';
        if ($factors === null) { echo '<p class="teacher-empty">Bez faktoru (tým &lt; 3 členů nebo málo rozdělení).</p></div>'; continue; }
        echo '<ul>';
        foreach ($factors as $k => $f) echo '<li>' . e(adaptive_student_label($classId, $k)) . ': faktor ' . number_format($f, 2, ',', '') . ($delta !== null ? ', návrh úpravy bodů ' . (($delta[$k] ?? 0) > 0 ? '+' : '') . (int)($delta[$k] ?? 0) : '') . '</li>';
        echo '</ul>';
        if ($delta !== null) echo p65t_form($csrf, 'proj65_t_apply_factor', $classId, '<input type="hidden" name="ref" value="' . e((string)$r['ref']) . '"><button class="btn primary" type="submit">Potvrdit návrh do hodnocení týmu</button>');
        echo '</div>';
    }
}
