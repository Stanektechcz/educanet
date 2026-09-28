<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · vykreslení přehlednějšího profilu žáka (záložky + sidebar + panely + grafy).
 * Volá ho render_student_profile_view() ve student_social_views.php (beze změny chování dat,
 * jen nové rozvržení). Cizí profil (přes ?student=) vidí jen prehled/pokrok/odznaky.
 */

function profile60_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/profile-v60.css?v=60.0')) . '">' . "\n";
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/arena-v60.css?v=60.0')) . '">' . "\n";
    echo '<script src="' . e(asset_url('assets/profile-v60.js?v=60.0')) . '" defer></script>' . "\n";
}

function profile60_tab_url(string $tab, string $target, bool $isMe): string
{
    $params = ['tab' => $tab];
    if (!$isMe) { $params['student'] = $target; }
    return module_url('profile', $params);
}

/** Hlavní vstupní bod: sestaví identitu, aktivní záložku a vykreslí layout se sidebarem/lištou. */
function render_profile60_view(string $classId, array $module, string $flash = ''): void
{
    $students = project_students_for_class($classId);
    $me = social_current_student_key($classId);
    $target = is_string($_GET['student'] ?? null) ? (string)$_GET['student'] : $me;
    if ($target === '' || !isset($students[$target])) { $target = $me; }
    if ($target === '' || !isset($students[$target])) { $_SESSION['flash'] = tr('Profil nebyl nalezen.'); redirect_to('?view=dashboard'); }
    $isMe = hash_equals($me, $target);
    $student = $students[$target];
    $tab = profile60_current_tab($isMe);
    $skillKey = skill_student_key_for_label($classId, (string)$student['label']);

    render_header($isMe ? tr('Můj profil') : tr('Profil studenta'), $module);
    profile60_assets();
    if ($flash !== '') { echo '<div class="notice">' . e($flash) . '</div>'; }

    $snapshot = learning_profile_snapshot_for_student($classId, (string)$student['label']);
    if ($isMe) {
        $own = learning_profile($classId);
        $snapshot = ['xp' => (int)($own['xp'] ?? 0), 'level' => learning_level((int)($own['xp'] ?? 0)), 'badge_ids' => array_keys((array)($own['badges'] ?? [])), 'achievement_count' => count((array)($own['achievements'] ?? []))];
    }
    $friends = student_friend_count($classId, $target);
    $teams = student_formed_team_count($classId, $target);

    echo '<div class="p60-layout">';
    profile60_render_sidebar($classId, $tab, $target, $isMe, $student, $snapshot);
    echo '<div class="p60-content">';
    profile60_render_tabbar($tab, $target, $isMe);
    profile60_render_hero($classId, $target, $student, $snapshot, $friends, $teams, $isMe);

    $data = profile60_data($classId, $target, $skillKey, $isMe, $tab);
    switch ($tab) {
        case 'pokrok': profile60_render_progress($data); break;
        case 'lab': if ($isMe) profile60_render_lab($data); break;
        case 'odznaky': profile60_render_badges($classId, $target, $data, $isMe); break;
        case 'arena':
            if ($isMe) { arena60_render_profile_tab($classId, $target); }
            else { arena60_render_challenge_button($classId, social_current_student_key($classId), $target); }
            break;
        case 'body': if ($isMe) profile60_render_points($data); break;
        case 'nastaveni': if ($isMe) social_render_profile_editor($classId, $target); break;
        default: profile60_render_overview($classId, $target, $data, $isMe); break;
    }
    echo '</div></div>';
    render_footer();
}

function profile60_render_sidebar(string $classId, string $tab, string $target, bool $isMe, array $student, array $snapshot): void
{
    echo '<nav class="p60-sidebar" aria-label="' . e(tr('Záložky profilu')) . '">';
    echo '<div class="p60-mini-card"><span class="p60-avatar">' . e(u_substr((string)$student['label'], 0, 1)) . '</span>'
        . '<div><strong>' . e(tr('Level {level}', ['level' => (int)($snapshot['level']['level'] ?? 1)])) . '</strong>'
        . '<small>' . e(tr('{count} XP', ['count' => (int)($snapshot['level']['xp'] ?? $snapshot['xp'] ?? 0)])) . '</small></div></div>';
    echo '<ul class="p60-nav-list">';
    foreach (profile60_tabs() as $t) {
        if (!$isMe && !in_array($t, ['prehled', 'pokrok', 'odznaky', 'arena'], true)) continue;
        $current = $t === $tab;
        $badge = '';
        if ($isMe && $t === 'arena' && function_exists('arena60_incoming_count')) {
            $n = arena60_incoming_count($classId, $target);
            if ($n > 0) $badge = ' <span class="p60-badge-count" aria-label="' . e(tr('{n} nových výzev', ['n' => $n])) . '">' . (int)$n . '</span>';
        }
        echo '<li><a href="' . e(profile60_tab_url($t, $target, $isMe)) . '"' . ($current ? ' aria-current="page"' : '') . ' class="p60-nav-link' . ($current ? ' is-current' : '') . '">' . e(profile60_tab_label($t)) . $badge . '</a></li>';
    }
    echo '</ul></nav>';
}

function profile60_render_tabbar(string $tab, string $target, bool $isMe): void
{
    echo '<nav class="p60-tabbar" aria-label="' . e(tr('Záložky profilu')) . '"><ul>';
    foreach (profile60_tabs() as $t) {
        if (!$isMe && !in_array($t, ['prehled', 'pokrok', 'odznaky', 'arena'], true)) continue;
        $current = $t === $tab;
        echo '<li><a href="' . e(profile60_tab_url($t, $target, $isMe)) . '"' . ($current ? ' aria-current="page"' : '') . ' class="p60-tab-link' . ($current ? ' is-current' : '') . '">' . e(profile60_tab_label($t)) . '</a></li>';
    }
    echo '</ul></nav>';
}

function profile60_render_hero(string $classId, string $target, array $student, array $snapshot, int $friends, int $teams, bool $isMe): void
{
    $profile = social_profile_get($classId, $target);
    $me = social_current_student_key($classId);
    $relation = $isMe ? null : friendship_between($classId, $me, $target);
    echo '<section class="p60-hero"><div class="p60-avatar xl">' . e(u_substr((string)$student['label'], 0, 1)) . '</div>'
        . '<div class="p60-hero-intro"><h1 id="p60-panel-title" tabindex="-1">' . e((string)$student['label']) . '</h1>'
        . '<p>' . e((string)($profile['headline'] !== '' ? $profile['headline'] : tr('Zatím bez profilového motta.'))) . '</p>'
        . '<div class="p60-chips"><span>' . e(tr('Level {level}', ['level' => (int)($snapshot['level']['level'] ?? 1)])) . '</span>'
        . '<span>' . e(tr('{count} badge', ['count' => count((array)$snapshot['badge_ids'])])) . '</span>'
        . '<span>' . e(tr('{count} přátel', ['count' => $friends])) . '</span>'
        . '<span>' . e(tr('{count} týmů', ['count' => $teams])) . '</span></div></div>';
    if (!$isMe) {
        echo '<div class="p60-hero-actions">';
        if (!$relation) {
            echo '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="friend_request"><input type="hidden" name="student_key" value="' . e($target) . '"><button class="btn primary" type="submit">' . e(tr('Přidat do přátel')) . '</button></form>';
        } elseif (($relation['status'] ?? '') === 'accepted') {
            echo '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="friend_remove"><input type="hidden" name="student_key" value="' . e($target) . '"><button class="btn secondary" type="submit">' . e(tr('Odebrat propojení')) . '</button></form>';
        } else {
            echo '<span class="social-pending">' . e(tr('Žádost čeká na potvrzení')) . '</span>';
        }
        echo '</div>';
    }
    echo '</section>';
}

function profile60_render_overview(string $classId, string $target, array $data, bool $isMe): void
{
    $profile = $data['profile'];
    if ($isMe) {
        echo '<section class="dashboard-panel p60-panel"><div class="p60-stat-grid">'
            . '<div class="p60-stat"><strong>' . (int)$data['level']['level'] . '</strong><small>' . e(tr('level')) . '</small></div>'
            . '<div class="p60-stat"><strong>' . (int)$data['xp'] . '</strong><small>' . e(tr('XP celkem')) . '</small></div>'
            . '<div class="p60-stat"><strong>' . (int)$data['balance'] . '</strong><small>' . e(tr('bodů k utracení')) . '</small></div>'
            . '<div class="p60-stat"><strong>' . (int)$data['achievement_earned'] . '/' . (int)$data['achievement_count'] . '</strong><small>' . e(tr('achievementů')) . '</small></div>'
            . '</div>';
        $lvl = $data['level'];
        echo '<div class="p60-xpbar" style="--p60-percent: ' . (int)($lvl['percent'] ?? 0) . '"><span>' . e(tr('Level {n}', ['n' => (int)$lvl['level']])) . '</span><i><b></b></i><span>' . e(tr('{current} / {next} XP', ['current' => (int)($lvl['current'] ?? 0), 'next' => (int)($lvl['next'] ?? 0)])) . '</span></div>';
        $points = [];
        $maxDay = 0.0;
        foreach ((array)$data['timeline'] as $d) {
            $points[] = ['label' => (string)date('j.', strtotime((string)$d['date'])), 'value' => (float)$d['earned']];
            $maxDay = max($maxDay, (float)$d['earned']);
        }
        echo profile60_svg_bars($points, tr('XP za posledních 14 dní'), tr('XP'));
        if ($maxDay === 0.0) {
            echo '<p class="p60-chart-empty">' . e(tr('Zatím nemáš žádné XP. Získáš je hned, jak projdeš první téma dnešní hodiny.')) . '</p>';
        }
        echo '</section>';
    }
    $badgeDefs = learning_badge_definitions();
    $featured = array_values(array_filter((array)$profile['featured_badges'], static fn($id): bool => is_string($id)));
    echo '<section class="dashboard-panel p60-panel"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Showcase')) . '</div><h2>' . e(tr('Vybrané vzácné badge')) . '</h2></div></div><div class="social-badge-showcase">';
    if (!$featured) {
        echo '<div class="social-empty-mini">' . e(tr('Zatím není vybraná žádná trofej.')) . '</div>';
    } else {
        foreach ($featured as $bid) {
            if (!isset($badgeDefs[$bid])) continue;
            $b = $badgeDefs[$bid];
            echo '<article class="prestige-badge earned rarity-' . e((string)($b['rarity'] ?? 'epic')) . '"><i>' . e((string)$b['mark']) . '</i><div><small>' . e(strtoupper((string)($b['rarity'] ?? 'epic'))) . '</small><strong>' . e((string)$b['title']) . '</strong><span>' . e((string)$b['text']) . '</span></div></article>';
        }
    }
    echo '</div></section>';
    if (!$isMe) {
        echo '<p class="p60-muted-note">' . e(tr('Motto: {text}', ['text' => (string)($profile['headline'] !== '' ? $profile['headline'] : tr('neuvedeno'))])) . '</p>';
    }
}

function profile60_render_progress(array $data): void
{
    $branches = $data['branches'];
    $defs = $data['branch_defs'];
    $rows = [];
    foreach ($branches as $branch => $bp) {
        $bd = $defs[$branch] ?? [];
        $rows[] = ['label' => (string)($bd['name'] ?? $branch), 'value' => (float)($bp['mastery_percent'] ?? 0), 'max' => 100];
    }
    echo '<section class="dashboard-panel p60-panel"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Mastery profil')) . '</div><h2>' . e(tr('Postup po větvích')) . '</h2></div><a class="text-link" href="?view=skills">' . e(tr('Můj Skill Tree →')) . '</a></div>';
    echo profile60_svg_hbars($rows, tr('Mastery po větvích'), '%');
    if ($data['top_skills']) {
        echo '<div class="profile-top-skills"><strong>' . e(tr('Nejsilnější skills')) . '</strong>';
        foreach ($data['top_skills'] as $row) {
            echo '<span>' . e((string)$row['skill']['name']) . ' <b>' . edu_number((float)$row['progress']['mastery_percent'], 0) . '%</b></span>';
        }
        echo '</div>';
    }
    $spec = $data['specialization'];
    if ($spec) {
        echo '<p class="p60-muted-note">' . e(tr('Specializace: {name}', ['name' => (string)($spec['name'] ?? '')])) . '</p>';
    }
    echo '</section>';
}

/**
 * Záložka „body“ – jen vlastní profil (nikdy se nevolá pro cizí, viz profile60_current_tab()).
 * Zůstatek, získáno/utraceno, graf přírůstku po týdnech a posledních 20 pohybů.
 */
function profile60_render_points(array $data): void
{
    $summary = $data['summary'];
    echo '<section class="dashboard-panel p60-panel"><div class="p60-stat-grid">'
        . '<div class="p60-stat"><strong>' . (int)$summary['balance'] . '</strong><small>' . e(tr('k utracení')) . '</small></div>'
        . '<div class="p60-stat"><strong>' . (int)$summary['earned'] . '</strong><small>' . e(tr('získáno celkem')) . '</small></div>'
        . '<div class="p60-stat"><strong>' . (int)$summary['spent'] . '</strong><small>' . e(tr('utraceno celkem')) . '</small></div>'
        . '</div>';
    echo profile60_svg_bars($data['weekly'], tr('Body získané po týdnech'), tr('bodů'));
    echo '<h3 class="p60-badge-group">' . e(tr('Poslední pohyby')) . '</h3>';
    if (!$summary['history']) {
        echo '<p class="p60-chart-empty">' . e(tr('Zatím žádný pohyb bodů.')) . '</p>';
    } else {
        echo '<ul class="p60-points-list">';
        foreach ($summary['history'] as $row) {
            $delta = (int)$row['delta'];
            echo '<li><span>' . e((string)$row['text']) . '</span><b class="' . ($delta >= 0 ? 'p60-plus' : 'p60-minus') . '">' . ($delta >= 0 ? '+' : '') . $delta . '</b></li>';
        }
        echo '</ul>';
    }
    echo '<p class="p60-muted-note"><a class="btn secondary" href="?view=obchod">' . e(tr('Otevřít obchod →')) . '</a></p>';
    echo '</section>';
}

function profile60_render_lab(array $data): void
{
    $rows = [];
    foreach ($data['packs'] as $p) { $rows[] = ['label' => (string)$p['title'], 'value' => (float)$p['done'], 'max' => max(1, (float)$p['total'])]; }
    echo '<section class="dashboard-panel p60-panel"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Linux Lab')) . '</div><h2>' . e(tr('Postup po balíčcích')) . '</h2><p>' . e(tr('{done} z {total} úrovní vyřešeno.', ['done' => (int)$data['solved_count'], 'total' => (int)$data['level_count']])) . '</p></div></div>';
    echo profile60_svg_hbars($rows, tr('Vyřešené úrovně v balíčcích'), '');
    echo '</section>';
    if ($data['badges']) {
        echo '<section class="dashboard-panel p60-panel"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Lab odznaky')) . '</div><h2>' . e(tr('Odznaky za dovednosti v terminálu')) . '</h2></div></div><div class="social-badge-showcase">';
        foreach ($data['badges'] as $b) {
            if (empty($b['earned'])) continue;
            echo '<article class="prestige-badge earned"><i>' . e((string)$b['icon']) . '</i><div><strong>' . e((string)$b['title']) . '</strong></div></article>';
        }
        echo '</div></section>';
    }
}

function profile60_render_badges(string $classId, string $target, array $data, bool $isMe): void
{
    $defs = $data['defs'];
    echo '<section class="dashboard-panel p60-panel"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Showcase')) . '</div><h2>' . e(tr('Vystavené odznaky')) . '</h2></div></div><div class="social-badge-showcase">';
    if (!$data['featured']) {
        echo '<div class="social-empty-mini">' . e(tr('Zatím není vybraná žádná trofej.')) . '</div>';
    } else {
        foreach ($data['featured'] as $bid) {
            if (!isset($defs[$bid])) continue;
            $b = $defs[$bid];
            echo '<article class="prestige-badge earned rarity-' . e((string)($b['rarity'] ?? 'epic')) . '"><i>' . e((string)$b['mark']) . '</i><div><strong>' . e((string)$b['title']) . '</strong></div></article>';
        }
    }
    echo '</div></section>';
    if ($isMe && isset($data['board'])) {
        v55_render_badge_board_from_data($data['board']);
    }
    if (function_exists('render_profile_role_experience')) {
        render_profile_role_experience($classId, $target, $isMe);
    }
}

/** Menší variantou v55_render_badge_board() nad už spočítanou tabulí (bez druhého výpočtu). */
function v55_render_badge_board_from_data(array $board): void
{
    echo '<section class="dashboard-panel p60-panel" id="odznaky"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Sbírka')) . '</div><h2>' . e(tr('Získávání odznaků')) . '</h2></div>'
        . '<div class="v55-badge-score"><strong>' . count($board['earned']) . '</strong><small>' . e(tr('z {total} odznaků', ['total' => (int)$board['total']])) . '</small></div></div>';
    if ($board['earned']) {
        echo '<h3 class="v55-badge-group">' . e(tr('Získané')) . '</h3><div class="v55-badge-grid">';
        foreach ($board['earned'] as $b) v55_render_badge_card($b);
        echo '</div>';
    }
    if ($board['progress']) {
        echo '<h3 class="v55-badge-group">' . e(tr('Nejblíž máš k těmto')) . '</h3><div class="v55-badge-grid">';
        foreach (array_slice($board['progress'], 0, 6) as $b) v55_render_badge_card($b);
        echo '</div>';
    }
    echo '</section>';
}
