<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Žákovské pohledy projektového cyklu (?view=projekt65): přehled, detail, recenze spolužáků, týmové prvky.
 * Šablony jsou psané přes tr(); obsah rubrik (texty kritérií) je výukový a zůstává česky (edu_content_lang_attr).
 * Recenzent je pro autora anonymní – detail zobrazuje jen schválené texty bez identity (proj65_peer_author_view).
 */

function p65v_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/projects-v65.css?v=65.0')) . '">' . "\n"
        . '<script src="' . e(asset_url('assets/projects-v65.js?v=65.0')) . '" defer></script>' . "\n";
}

function p65v_state_label(string $state): string
{
    return [
        'proposal' => tr('Návrh'), 'rejected' => tr('Vráceno k úpravě'), 'approved' => tr('Schváleno'), 'in_progress' => tr('Rozpracováno'),
        'submitted' => tr('Odevzdáno'), 'peer_review' => tr('Peer review'), 'graded' => tr('Ohodnoceno'), 'portfolio' => tr('V portfoliu'),
    ][$state] ?? $state;
}

function p65v_level_label(int $level): string
{
    return [1 => tr('Začátek'), 2 => tr('Základ'), 3 => tr('Dobré'), 4 => tr('Výborné')][$level] ?? (string)$level;
}

function p65v_error_text(string $code): string
{
    return [
        'need_pitch' => tr('Návrh musí mít aspoň 20 znaků.'), 'need_submission' => tr('Doplň odkaz nebo krátkou poznámku k odevzdání.'),
        'bad_url' => tr('Odkaz nevypadá správně.'), 'no_team' => tr('Nejdřív musíš být v týmu pro tento projekt.'), 'exists' => tr('Tenhle projekt už máš rozjetý.'),
        'text_short' => tr('Text je moc krátký.'), 'level_range' => tr('Vyber úroveň u každého kritéria.'), 'daily_limit' => tr('Dnes už jsi do deníku psal/a dost.'),
        'milestones_off' => tr('U tohoto projektu se milníky nepoužívají.'), 'invalid' => tr('Akci se nepodařilo provést.'), 'identity' => tr('Portfolio se teď nepodařilo uložit.'),
    ][$code] ?? tr('Akci se nepodařilo provést.');
}

function p65v_form(string $action, string $inner, string $class = 'p65-form', string $attrs = ''): string
{
    return '<form method="post" class="' . e($class) . '"' . $attrs . '><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">' . $inner . '</form>';
}

function p65v_textarea(string $name, string $label, int $max, string $value = '', bool $required = true): string
{
    static $counter = 0;
    $id = 'p65-t' . (++$counter);
    return '<label for="' . e($id) . '">' . e($label) . '</label><textarea id="' . e($id) . '" name="' . e($name) . '" maxlength="' . $max . '"' . ($required ? ' required' : '') . ' data-p65-max="' . $max . '" data-p65-count="' . e($id) . '-n">' . e($value) . '</textarea>'
        . '<span class="p65-count" id="' . e($id) . '-n" aria-live="polite"></span>';
}

function p65v_steps(array $record): string
{
    $steps = PROJ65_STEPS[(string)$record['mode']];
    $state = (string)$record['state'] === 'rejected' ? 'proposal' : (string)$record['state'];
    $at = array_search($state, $steps, true);
    $html = '<ol class="p65-steps" aria-label="' . e(tr('Kroky projektu')) . '">';
    foreach ($steps as $i => $step) {
        $cls = $at !== false && $i < $at ? ' class="done"' : '';
        $html .= '<li' . ($i === $at ? ' aria-current="step"' : $cls) . '>' . e(p65v_state_label($step)) . '</li>';
    }
    return $html . '</ol>';
}

function p65v_url(string $id = ''): string
{
    return '?view=projekt65' . ($id !== '' ? '&amp;id=' . rawurlencode($id) : '');
}

/** Přehled: moje projekty, nabídka k zahájení, recenze spolužáků. */
function p65v_render_home(string $classId, string $studentKey, string $flash): void
{
    p65v_assets();
    echo '<div class="p65"><section class="p65-card"><div class="eyebrow">' . e(tr('Projekty')) . '</div><h1>' . e(tr('Moje projekty')) . '</h1>'
        . '<p>' . e(tr('Od návrhu po portfolio: každý krok vidíš na jednom místě.')) . '</p>'
        . '<p><a class="btn secondary" href="?view=portfolio">' . e(tr('Moje portfolio')) . '</a></p></section>';
    if ($flash !== '') echo '<div class="notice" role="status">' . e($flash) . '</div>';
    $mine = proj65_for_student($classId, $studentKey);
    echo '<section aria-labelledby="p65-mine"><h2 id="p65-mine">' . e(tr('Rozjeté projekty')) . '</h2>';
    if ($mine === []) echo '<p>' . e(tr('Zatím nemáš žádný projekt. Vyber si z nabídky níže.')) . '</p>';
    echo '<div class="p65-grid">';
    foreach ($mine as $row) {
        echo '<article class="p65-card"><h3>' . e((string)$row['title']) . '</h3><p><span class="p65-badge">' . e(p65v_state_label((string)$row['state'])) . '</span></p>'
            . '<a class="btn primary" href="' . p65v_url((string)$row['id']) . '">' . e(tr('Otevřít projekt')) . '</a></article>';
    }
    echo '</div></section>';
    p65v_render_offer($classId, $studentKey, $mine);
    p65v_render_review_tasks($classId, $studentKey);
    echo '</div>';
}

function p65v_render_offer(string $classId, string $studentKey, array $mine): void
{
    $started = array_flip(array_map(static fn(array $r): string => (string)$r['project_id'], $mine));
    $html = '';
    foreach ((array)(project_catalog()[$classId] ?? []) as $project) {
        $pid = (string)$project['id'];
        if (isset($started[$pid])) continue;
        $full = proj65_settings($classId, $pid)['cycle_mode'] === 'full';
        $inner = '<input type="hidden" name="project_id" value="' . e($pid) . '">';
        if ($full) $inner .= p65v_textarea('pitch', tr('Tvůj návrh: co a jak chceš udělat'), PROJ65_PITCH_MAX);
        $inner .= '<button class="btn primary" type="submit">' . e($full ? tr('Poslat návrh') : tr('Začít projekt')) . '</button>';
        $noTeam = (string)$project['type'] === 'group' && project_group_for_student($classId, $pid, $studentKey) === null;
        $html .= '<article class="p65-card"><h3>' . e((string)$project['title']) . '</h3><p>' . e((string)$project['summary']) . '</p>'
            . '<p><span class="p65-badge">' . e((string)$project['type'] === 'group' ? tr('Týmový') : tr('Osobní')) . '</span></p>'
            . ($noTeam ? '<p class="p65-hint">' . e(tr('Nejdřív musíš být v týmu pro tento projekt.')) . '</p>' : p65v_form('proj65_s_start', $inner)) . '</article>';
    }
    foreach (proj60_my_applications($classId, $studentKey) as $app) {
        if ((string)$app['status'] !== 'approved' || proj65_get(proj65_ref_p60((string)$app['id'])) !== null) continue;
        $html .= '<article class="p65-card"><h3>' . e((string)$app['title']) . '</h3><p><span class="p65-badge">' . e(tr('Projekt klienta')) . '</span></p>'
            . p65v_form('proj65_s_start_p60', '<input type="hidden" name="application_id" value="' . e((string)$app['id']) . '"><button class="btn primary" type="submit">' . e(tr('Začít projekt')) . '</button>') . '</article>';
    }
    if ($html !== '') echo '<section aria-labelledby="p65-offer"><h2 id="p65-offer">' . e(tr('Nabídka projektů')) . '</h2><div class="p65-grid">' . $html . '</div></section>';
}

function p65v_render_review_tasks(string $classId, string $studentKey): void
{
    $tasks = array_values(array_filter(proj65_reviews_for_reviewer($classId, $studentKey), static fn(array $r): bool => in_array((string)$r['state'], ['assigned', 'submitted', 'flagged'], true)));
    if ($tasks === []) return;
    echo '<section aria-labelledby="p65-rev"><h2 id="p65-rev">' . e(tr('Recenze pro spolužáky')) . '</h2><p>' . e(tr('Dobrovolné. Píšeš anonymně, text před zveřejněním schvaluje učitel a na známku nemá vliv.')) . '</p><div class="p65-grid">';
    foreach ($tasks as $i => $task) p65v_render_review_form($task, $i + 1);
    echo '</div></section>';
}

function p65v_render_review_form(array $task, int $n): void
{
    $work = proj65_get((string)$task['ref']);
    $rubric = $work !== null ? proj65_rubric_for($work) : null;
    if ($rubric === null) return;
    $inner = '<input type="hidden" name="review_id" value="' . e((string)$task['id']) . '">';
    foreach ($rubric['criteria'] as $c) {
        $inner .= '<fieldset class="p65-levels"><legend' . edu_content_lang_attr() . '>' . e((string)$c['title']) . '</legend>';
        foreach ([1, 2, 3, 4] as $lvl) {
            $checked = (int)(($task['levels'] ?? [])[$c['id']] ?? 0) === $lvl ? ' checked' : '';
            $inner .= '<label><input type="radio" name="levels[' . e((string)$c['id']) . ']" value="' . $lvl . '"' . $checked . ' required> ' . e(p65v_level_label($lvl)) . '</label>';
        }
        $inner .= '</fieldset>';
    }
    $starters = p65v_starter_texts();
    $s = p65v_textarea('strength', tr('Jedna silná stránka'), PROJ65_REVIEW_TEXT_MAX, (string)$task['strength']);
    $g = p65v_textarea('suggestion', tr('Jeden konkrétní návrh'), PROJ65_REVIEW_TEXT_MAX, (string)$task['suggestion']);
    $inner .= $s . p65v_starters($s, $starters['strength']) . $g . p65v_starters($g, $starters['suggestion'])
        . '<p class="p65-hint">' . e(tr('Piš k věci a laskavě: popiš, co vidíš, a navrhni, co zkusit.')) . '</p><button class="btn primary" type="submit">' . e(tr('Odeslat recenzi')) . '</button>';
    $url = safe_url((string)$task['work']['url']);
    echo '<article class="p65-card"><h3>' . e(tr('Práce č. {n}', ['n' => $n])) . ': ' . e((string)$task['work']['title']) . '</h3>'
        . ($url !== '' ? '<p><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e(tr('Otevřít odevzdanou práci')) . '</a></p>' : '')
        . ((string)$task['work']['note'] !== '' ? '<p>' . e((string)$task['work']['note']) . '</p>' : '')
        . ((string)$task['state'] !== 'assigned' ? '<p><span class="p65-badge">' . e(tr('Odesláno, čeká na schválení')) . '</span></p>' : '')
        . p65v_form('proj65_s_review', $inner) . '</article>';
}

/** Nápověda vět pro peer review (už přeložená). @return array{strength:list<string>,suggestion:list<string>} */
function p65v_starter_texts(): array
{
    return [
        'strength' => [tr('Nejvíc se mi líbilo, že …'), tr('Dobře funguje, že …'), tr('Silná stránka je …')],
        'suggestion' => [tr('Zkus příště …'), tr('Pomohlo by, kdyby …'), tr('Navrhuju změnit …, protože …')],
    ];
}

/** Tlačítka s nápovědou vět pro textarea (id se vyčte z HTML textarey). */
function p65v_starters(string $textareaHtml, array $starters): string
{
    if (preg_match('/<textarea id="([^"]+)"/', $textareaHtml, $m) !== 1) return '';
    $html = '<div class="p65-starters" role="group" aria-label="' . e(tr('Nápověda vět')) . '">';
    foreach ($starters as $text) $html .= '<button type="button" data-p65-starter="' . e($text) . '" data-p65-target="' . e($m[1]) . '">' . e($text) . '</button>';
    return $html . '</div>';
}
