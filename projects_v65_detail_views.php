<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Detail projektu pro žáka: kroky, formuláře podle stavu, týmové milníky, deník přínosu, rozdělení bodů, výsledek.
 * Volá ho app/views/projects_v65.php. Identita žáka je vždy ze session, id záznamu je neprůhledné (c65_…).
 */

function p65v_hidden(array $record): string
{
    return '<input type="hidden" name="id" value="' . e((string)$record['id']) . '">';
}

function p65v_render_detail(array $record, string $classId, string $studentKey, string $flash): void
{
    p65v_assets();
    $settings = proj65_settings($classId, (string)$record['project_id']);
    echo '<div class="p65"><section class="p65-card"><p><a href="?view=projekt65">' . e(tr('Zpět na projekty')) . '</a></p><h1>' . e((string)$record['title']) . '</h1>'
        . p65v_steps($record) . '<p><span class="p65-badge">' . e(p65v_state_label((string)$record['state'])) . '</span></p></section>';
    if ($flash !== '') echo '<div class="notice" role="status">' . e($flash) . '</div>';
    p65v_render_state_area($record, $settings);
    if ((string)$record['target_type'] === 'group') p65v_render_team($record, $classId, $studentKey, $settings);
    p65v_render_peer_received($record);
    p65v_render_result($record);
    echo '</div>';
}

function p65v_render_state_area(array $record, array $settings): void
{
    $state = (string)$record['state'];
    $pitch = (string)($record['pitch'] ?? '');
    if ($pitch !== '') echo '<section class="p65-card"><h2>' . e(tr('Návrh')) . '</h2><p>' . e($pitch) . '</p></section>';
    if ($state === 'proposal') echo '<section class="p65-card"><p>' . e(tr('Návrh čeká na schválení učitelem.')) . '</p></section>';
    if ($state === 'rejected') {
        $form = p65v_hidden($record) . p65v_textarea('pitch', tr('Uprav návrh a pošli ho znovu'), PROJ65_PITCH_MAX, $pitch) . '<button class="btn primary" type="submit">' . e(tr('Poslat znovu')) . '</button>';
        echo '<section class="p65-card"><h2>' . e(tr('Návrh vrácen k úpravě')) . '</h2>' . p65v_form('proj65_s_pitch', $form) . '</section>';
    }
    if ($state === 'approved' && $settings['milestones']) {
        echo '<section class="p65-card"><p>' . e(tr('Milníky jsou volitelné: můžeš je používat, nebo rovnou odevzdat.')) . '</p>'
            . p65v_form('proj65_s_begin', p65v_hidden($record) . '<button class="btn secondary" type="submit">' . e(tr('Začít s milníky')) . '</button>') . '</section>';
    }
    if (in_array($state, ['approved', 'in_progress'], true)) p65v_render_submit_form($record, tr('Odevzdat práci'));
    if (in_array($state, ['submitted', 'peer_review', 'graded', 'portfolio'], true) && is_array($record['submission'] ?? null)) p65v_render_submission($record);
    if (in_array($state, ['submitted', 'peer_review'], true)) echo '<section class="p65-card"><p>' . e(tr('Práce je odevzdaná, čeká na hodnocení učitele.')) . '</p></section>';
}

function p65v_render_submit_form(array $record, string $title): void
{
    $sub = is_array($record['submission'] ?? null) ? $record['submission'] : [];
    $form = p65v_hidden($record) . '<label for="p65-url">' . e(tr('Odkaz na práci (nepovinné)')) . '</label><input id="p65-url" type="url" name="url" maxlength="300" value="' . e((string)($sub['url'] ?? '')) . '">'
        . p65v_textarea('note', tr('Krátká poznámka k odevzdání'), PROJ65_NOTE_MAX, (string)($sub['note'] ?? ''), false)
        . '<button class="btn primary" type="submit">' . e(tr('Odevzdat')) . '</button>';
    echo '<section class="p65-card"><h2>' . e($title) . '</h2>' . p65v_form('proj65_s_submit', $form) . '</section>';
}

function p65v_render_submission(array $record): void
{
    $sub = (array)$record['submission'];
    $url = safe_url((string)($sub['url'] ?? ''));
    echo '<section class="p65-card"><h2>' . e(tr('Odevzdáno')) . '</h2>'
        . ($url !== '' ? '<p><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e(tr('Otevřít odevzdanou práci')) . '</a></p>' : '')
        . ((string)($sub['note'] ?? '') !== '' ? '<p>' . e((string)$sub['note']) . '</p>' : '') . '</section>';
}

function p65v_render_team(array $record, string $classId, string $studentKey, array $settings): void
{
    $state = (string)$record['state'];
    if (!in_array($state, ['approved', 'in_progress', 'submitted', 'peer_review', 'graded'], true)) return;
    if ($settings['milestones']) p65v_render_kanban($record);
    p65v_render_diary($record, $classId);
    $members = proj65_member_keys($record);
    if (count($members) >= PROJ65_TEAM_MIN_FOR_FACTOR) p65v_render_split($record, $classId, $studentKey, $members);
}

function p65v_render_kanban(array $record): void
{
    $milestones = proj65_ms_list((string)$record['target_id']);
    $tasks = array_slice(project_tasks_for_group((string)$record['target_id']), 0, 20);
    $add = p65v_hidden($record) . '<label for="p65-ms">' . e(tr('Nový milník')) . '</label><input id="p65-ms" type="text" name="title" maxlength="80" required>';
    if ($tasks !== []) {
        $add .= '<fieldset class="p65-levels"><legend>' . e(tr('Navázané úkoly (nepovinné)')) . '</legend>';
        foreach ($tasks as $t) $add .= '<label><input type="checkbox" name="task_ids[]" value="' . e((string)$t['id']) . '"> ' . e((string)($t['title'] ?? '')) . '</label>';
        $add .= '</fieldset>';
    }
    echo '<section class="p65-card" aria-labelledby="p65-kb"><h2 id="p65-kb">' . e(tr('Milníky týmu')) . '</h2><div class="p65-kanban">';
    foreach (PROJ65_MS_STATUSES as $status) {
        echo '<div class="p65-col"><h3>' . e(p65v_ms_label($status)) . '</h3><ul>';
        foreach ($milestones as $m) {
            if ((string)$m['status'] !== $status) continue;
            $prog = proj65_ms_task_progress($m);
            echo '<li><strong>' . e((string)$m['title']) . '</strong>' . ($prog['total'] > 0 ? '<span class="p65-hint">' . e(tr('Úkoly: {done}/{total}', ['done' => $prog['done'], 'total' => $prog['total']])) . '</span>' : '') . '<div class="p65-inline">';
            foreach (PROJ65_MS_STATUSES as $to) {
                if ($to === $status) continue;
                echo p65v_form('proj65_s_ms_move', p65v_hidden($record) . '<input type="hidden" name="ms_id" value="' . e((string)$m['id']) . '"><input type="hidden" name="status" value="' . e($to) . '"><button class="btn secondary" type="submit">' . e(tr('Přesunout: {stav}', ['stav' => p65v_ms_label($to)])) . '</button>', 'p65-inline');
            }
            echo '</div></li>';
        }
        echo '</ul></div>';
    }
    echo '</div>' . p65v_form('proj65_s_ms_add', $add . '<button class="btn primary" type="submit">' . e(tr('Přidat milník')) . '</button>') . '</section>';
}

function p65v_ms_label(string $status): string
{
    return ['todo' => tr('K udělání'), 'doing' => tr('Pracujeme'), 'done' => tr('Hotovo')][$status] ?? $status;
}

function p65v_render_diary(array $record, string $classId): void
{
    $rows = proj65_diary_list((string)$record['ref']);
    echo '<section class="p65-card" aria-labelledby="p65-di"><h2 id="p65-di">' . e(tr('Deník přínosu')) . '</h2><p class="p65-hint">' . e(tr('Co jsi udělal/a pro tým. Vidí ho tým i učitel.')) . '</p><ul class="p65-diary">';
    foreach (array_slice($rows, 0, 15) as $r) {
        echo '<li><strong>' . e(adaptive_student_label($classId, (string)$r['student_key'])) . '</strong> · ' . e(date('j. n. Y', strtotime((string)$r['at']) ?: time())) . '<br>' . e((string)$r['text']) . '</li>';
    }
    if ($rows === []) echo '<li>' . e(tr('Zatím tu nic není.')) . '</li>';
    echo '</ul>' . p65v_form('proj65_s_diary', p65v_hidden($record) . p65v_textarea('text', tr('Nový zápis'), PROJ65_DIARY_MAX) . '<button class="btn primary" type="submit">' . e(tr('Zapsat')) . '</button>') . '</section>';
}

function p65v_render_split(array $record, string $classId, string $studentKey, array $members): void
{
    $mine = (array)(((array)($record['splits'] ?? []))[$studentKey] ?? []);
    $inner = p65v_hidden($record);
    foreach ($members as $m) {
        if ($m === $studentKey) continue;
        $inner .= '<label>' . e(adaptive_student_label($classId, $m)) . '<input type="number" name="points[' . e($m) . ']" min="0" max="100" step="1" value="' . (int)($mine[$m] ?? 0) . '" required></label>';
    }
    $inner .= '<p aria-live="polite">' . e(tr('Součet')) . ': <strong data-p65-sum>0 / 100</strong></p><button class="btn primary" type="submit">' . e(tr('Uložit rozdělení')) . '</button>';
    echo '<section class="p65-card"><h2>' . e(tr('Rozdělení 100 bodů')) . '</h2><p class="p65-hint">' . e(tr('Rozděl 100 bodů mezi ostatní členy podle jejich přínosu. Výsledek je jen návrh, potvrzuje ho učitel.')) . '</p>'
        . p65v_form('proj65_s_split', $inner, 'p65-form p65-split', ' data-p65-split') . '</section>';
}

function p65v_render_peer_received(array $record): void
{
    if (!in_array((string)$record['state'], ['peer_review', 'graded', 'portfolio'], true)) return;
    $rows = proj65_peer_author_view((string)$record['ref']);
    if ($rows === []) return;
    echo '<section class="p65-card"><h2>' . e(tr('Zpětná vazba od spolužáků')) . '</h2><p class="p65-hint">' . e(tr('Anonymní a schválená učitelem. Na známku nemá vliv.')) . '</p>';
    foreach ($rows as $r) {
        echo '<div><p><strong>' . e(tr('Silná stránka')) . ':</strong> ' . e((string)$r['strength']) . '</p><p><strong>' . e(tr('Návrh')) . ':</strong> ' . e((string)$r['suggestion']) . '</p></div>';
    }
    echo '</section>';
}

function p65v_render_result(array $record): void
{
    $versions = array_values(array_filter((array)($record['versions'] ?? []), 'is_array'));
    if ($versions === [] || !in_array((string)$record['state'], ['graded', 'portfolio'], true)) return;
    $last = $versions[count($versions) - 1];
    $rubric = proj65_rubric_for($record);
    echo '<section class="p65-card"><h2>' . e(tr('Hodnocení')) . '</h2><p class="p65-hint">' . e(tr('Verze {n}', ['n' => count($versions)])) . '</p>';
    foreach ((array)($rubric['criteria'] ?? []) as $c) {
        $lvl = (int)(($last['levels'] ?? [])[$c['id']] ?? 0);
        echo '<div class="p65-levelrow"><span' . edu_content_lang_attr() . '>' . e((string)$c['title']) . '</span><strong>' . $lvl . ' · ' . e(p65v_level_label($lvl)) . '</strong></div>';
        $cm = (string)(($last['comments'] ?? [])[$c['id']] ?? '');
        if ($cm !== '') echo '<p class="p65-hint"' . edu_content_lang_attr() . '>' . e($cm) . '</p>';
    }
    echo '<p><a class="btn secondary" href="?view=portfolio">' . e(tr('Přidat do portfolia')) . '</a></p></section>';
}
