<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · vykreslení profilu žáka (záložky + sidebar + panely + grafy).
 * Volá ho render_student_profile_view() ve student_social_views.php. Cizí profil (přes ?student=)
 * vidí jen záložky z profile60_public_tabs(). Každá záložka začíná „hlavním úkolem“ (profile60_task),
 * odznaky jsou v jednom SVG spritu (badges_v60.php), data se počítají jen pro aktivní záložku.
 */

require_once __DIR__ . '/badges_v60.php';
require_once __DIR__ . '/profile_v60_ui.php';
require_once __DIR__ . '/motivation_v61_views.php';

function profile60_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/profile-v60.css?v=60.2')) . '">' . "\n";
    echo '<script src="' . e(asset_url('assets/profile-v60.js?v=60.2')) . '" defer></script>' . "\n";
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
    profile60_memo_reset();
    $students = project_students_for_class($classId);
    $me = social_current_student_key($classId);
    $target = is_string($_GET['student'] ?? null) ? (string)$_GET['student'] : $me;
    if ($target === '' || !isset($students[$target])) { $target = $me; }
    if ($target === '' || !isset($students[$target])) { $_SESSION['flash'] = tr('Profil nebyl nalezen.'); redirect_to('?view=dashboard'); }
    $isMe = hash_equals($me, $target);
    $student = $students[$target];
    $tab = profile60_current_tab($isMe);

    render_header($isMe ? tr('Můj profil') : tr('Profil studenta'), $module);
    profile60_assets();
    badge60_sprite_mode(true);
    if ($flash !== '') { echo '<div class="notice p60-flash ui-flash" role="status">' . e($flash) . '</div>'; }
    $snapshot = profile60_snapshot($classId, $student, $isMe);

    echo '<div class="p60-layout">';
    profile60_render_sidebar($classId, $tab, $target, $isMe, $student, $snapshot);
    echo '<div class="p60-content">';
    profile60_render_tabbar($classId, $tab, $target, $isMe);
    profile60_render_hero($classId, $target, $student, $snapshot, $isMe);
    echo '<div class="p60-panels" id="p60-panel" tabindex="-1">';
    profile60_render_tab($classId, $target, skill_student_key_for_label($classId, (string)$student['label']), $isMe, $tab);
    echo '</div></div></div>';
    echo badge60_sprite_flush();
    badge60_sprite_mode(false);
    render_footer();
}

/** Souhrn XP/levelu/odznaků pro hlavičku (vlastní profil z vlastních dat, cizí ze snapshotu). */
function profile60_snapshot(string $classId, array $student, bool $isMe): array
{
    if (!$isMe) return learning_profile_snapshot_for_student($classId, (string)$student['label']);
    $own = learning_profile($classId);
    $xp = (int)($own['xp'] ?? 0);
    return ['xp' => $xp, 'level' => learning_level($xp), 'badge_ids' => array_keys((array)($own['badges'] ?? [])), 'achievement_count' => count((array)($own['achievements'] ?? []))];
}

function profile60_render_tab(string $classId, string $target, string $skillKey, bool $isMe, string $tab): void
{
    $data = profile60_data($classId, $target, $skillKey, $isMe, $tab);
    switch ($tab) {
        case 'pokrok': profile60_render_progress($data, $isMe); break;
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
}

/** Odkazy na záložky; $variant = nav-list (sidebar) / tab-link (lišta na mobilu). */
function profile60_nav_items(string $classId, string $tab, string $target, bool $isMe, string $class): string
{
    $html = '';
    foreach ($isMe ? profile60_tabs() : profile60_public_tabs() as $t) {
        $current = $t === $tab;
        $badge = '';
        if ($isMe && $t === 'arena' && ($n = profile60_incoming($classId, $target)) > 0) {
            $badge = ' <span class="p60-badge-count" aria-label="' . e(tr('{n} nových výzev', ['n' => $n])) . '">' . $n . '</span>';
        }
        $html .= '<li><a href="' . e(profile60_tab_url($t, $target, $isMe)) . '"' . ($current ? ' aria-current="page"' : '') . ' class="' . $class . ($current ? ' is-current' : '') . '">' . e(profile60_tab_label($t)) . $badge . '</a></li>';
    }
    return $html;
}

function profile60_render_sidebar(string $classId, string $tab, string $target, bool $isMe, array $student, array $snapshot): void
{
    echo '<nav class="p60-sidebar" aria-label="' . e(tr('Záložky profilu')) . '">'
        . '<div class="p60-mini-card"><span class="p60-avatar" aria-hidden="true">' . e(u_substr((string)$student['label'], 0, 1)) . '</span>'
        . '<div><strong>' . e(tr('Level {level}', ['level' => (int)($snapshot['level']['level'] ?? 1)])) . '</strong>'
        . '<small>' . e(tr('{count} XP', ['count' => (int)($snapshot['xp'] ?? 0)])) . '</small></div></div>'
        . '<ul class="p60-nav-list">' . profile60_nav_items($classId, $tab, $target, $isMe, 'p60-nav-link') . '</ul></nav>';
}

function profile60_render_tabbar(string $classId, string $tab, string $target, bool $isMe): void
{
    echo '<nav class="p60-tabbar" aria-label="' . e(tr('Záložky profilu')) . '"><ul>' . profile60_nav_items($classId, $tab, $target, $isMe, 'p60-tab-link') . '</ul></nav>';
}

/** Aktivní kosmetika (rámeček/titulek) žáka pro zobrazení; bez obchodu vrací prázdné hodnoty. */
function profile60_cosmetics(string $classId, string $target): array
{
    return profile60_memo('cosmetics|' . $target, static function () use ($classId, $target): array {
        $empty = ['frame_id' => '', 'frame_colors' => [], 'title' => ''];
        if (!function_exists('mkt60_cosmetics') && is_file(__DIR__ . '/marketplace_v60.php')) { require_once __DIR__ . '/marketplace_v60.php'; }
        if (!function_exists('mkt60_cosmetics') || !function_exists('mkt60_item')) return $empty;
        $active = mkt60_cosmetics($classId, $target);
        $frame = is_string($active['frame'] ?? null) ? (string)$active['frame'] : '';
        $titleItem = is_string($active['title'] ?? null) ? mkt60_item((string)$active['title']) : null;
        return [
            'frame_id' => $frame,
            'frame_colors' => $frame !== '' ? badge60_frame_colors($frame) : [],
            'title' => $titleItem !== null ? (string)($titleItem['title'] ?? '') : '',
        ];
    });
}

/** Kruhový progress level (SVG; r = 44 → obvod ≈ 276,5). Přístupný popisek, bez animace. */
function profile60_level_ring(int $level, int $percent): string
{
    $percent = max(0, min(100, $percent));
    $circ = 2 * M_PI * 44;
    $fill = number_format($circ * $percent / 100, 1, '.', '');
    $rest = number_format($circ - (float)$fill, 1, '.', '');
    $label = e(tr('Level {level}, {percent} % do dalšího levelu', ['level' => $level, 'percent' => $percent]));
    return '<svg class="p60-ring" viewBox="0 0 100 100" role="img" aria-label="' . $label . '"><title>' . $label . '</title>'
        . '<circle class="p60-ring-track" cx="50" cy="50" r="44" fill="none" stroke-width="7"/>'
        . '<circle class="p60-ring-fill" cx="50" cy="50" r="44" fill="none" stroke-width="7" stroke-linecap="round" stroke-dasharray="' . $fill . ' ' . $rest . '" transform="rotate(-90 50 50)"/>'
        . '</svg>';
}

function profile60_friend_form(string $action, string $target, string $label, string $class): string
{
    return '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="student_key" value="' . e($target) . '"><button class="btn ' . $class . '" type="submit">' . e($label) . '</button></form>';
}

function profile60_render_hero(string $classId, string $target, array $student, array $snapshot, bool $isMe): void
{
    $profile = profile60_social($classId, $target);
    $cos = profile60_cosmetics($classId, $target);
    $level = (int)($snapshot['level']['level'] ?? 1);
    $frameStyle = $cos['frame_colors'] ? ' style="--p60-fa: ' . e((string)$cos['frame_colors'][0]) . '; --p60-fb: ' . e((string)$cos['frame_colors'][1]) . '"' : '';
    echo '<section class="p60-hero" aria-label="' . e(tr('Profil')) . '"><div class="p60-hero-avatar">' . profile60_level_ring($level, (int)($snapshot['level']['percent'] ?? 0))
        . '<span class="p60-avatar-frame' . ($cos['frame_colors'] ? ' has-frame' : '') . '"' . $frameStyle . '><span class="p60-avatar xl" aria-hidden="true">' . e(u_substr((string)$student['label'], 0, 1)) . '</span></span>'
        . '<span class="p60-lvl">' . e(tr('Level {level}', ['level' => $level])) . '</span></div>'
        . '<div class="p60-hero-intro"><h1 id="p60-panel-title" tabindex="-1">' . e((string)$student['label']) . '</h1>';
    if ($cos['title'] !== '') { echo '<p class="p60-title-pill">' . e($cos['title']) . '</p>'; }
    echo '<p class="p60-headline">' . e((string)($profile['headline'] !== '' ? $profile['headline'] : tr('Zatím bez profilového motta.'))) . '</p></div>'
        . '<ul class="p60-hero-stats"><li><strong>' . (int)($snapshot['xp'] ?? 0) . '</strong><span>' . e(tr('XP')) . '</span></li>'
        . '<li><strong>' . count((array)$snapshot['badge_ids']) . '</strong><span>' . e(tr('odznaků')) . '</span></li>'
        . '<li><strong>' . student_friend_count($classId, $target) . '</strong><span>' . e(tr('přátel')) . '</span></li>'
        . '<li><strong>' . student_formed_team_count($classId, $target) . '</strong><span>' . e(tr('týmů')) . '</span></li></ul>';
    if (!$isMe) profile60_render_relation($classId, $target);
    echo '</section>';
}

/** Tlačítko vztahu (přidat / odebrat / čeká) na cizím profilu. */
function profile60_render_relation(string $classId, string $target): void
{
    $relation = friendship_between($classId, social_current_student_key($classId), $target);
    echo '<div class="p60-hero-actions">';
    if (!$relation) {
        echo profile60_friend_form('friend_request', $target, tr('Přidat do přátel'), 'primary');
    } elseif (($relation['status'] ?? '') === 'accepted') {
        echo profile60_friend_form('friend_remove', $target, tr('Odebrat propojení'), 'secondary');
    } else {
        echo '<span class="social-pending">' . e(tr('Žádost čeká na potvrzení')) . '</span>';
    }
    echo '</div>';
}

/** Hlavní úkol Přehledu: první použitelný krok (výzva → Lab → skill → doplnit profil). */
function profile60_overview_task(array $d, string $target): string
{
    if ((int)$d['incoming'] > 0) {
        return profile60_task(tr('Čeká na tebe'), trn(['one' => '{n} nová výzva', 'few' => '{n} nové výzvy', 'other' => '{n} nových výzev'], (int)$d['incoming']), tr('Spolužák tě vyzval na souboj v Linux Labu.'), tr('Otevřít Arénu'), profile60_tab_url('arena', $target, true), 'yellow');
    }
    if (is_array($d['lab']['next'])) {
        $next = $d['lab']['next'];
        return profile60_task(tr('Další krok'), tr('Pokračuj v Linux Labu'), $next['pack'] . ($next['title'] !== '' ? ' · ' . $next['title'] : ''), tr('Otevřít Lab'), '?view=lab');
    }
    if (is_array($d['next_skill'])) {
        $skill = $d['next_skill']['skill'];
        return profile60_task(tr('Další krok'), (string)$skill['name'], (string)$d['next_skill']['reason'], tr('Pokračovat'), module_url('skill_detail', ['skill' => (string)$skill['slug']]));
    }
    if (!$d['profile_filled']) {
        return profile60_task(tr('Další krok'), tr('Dokonči svůj profil'), tr('Motto a krátké představení pomůžou spolužákům při hledání týmu.'), tr('Otevřít nastavení'), profile60_tab_url('nastaveni', $target, true));
    }
    return profile60_task(tr('Další krok'), tr('Máš hotovo, co šlo'), tr('Podívej se, co je na programu v dnešní hodině.'), tr('Dnešní hodina'), '?view=dashboard');
}

/** Dlaždice souhrnu všech sekcí vlastního profilu. */
function profile60_overview_tiles(array $d, string $target): string
{
    $lab = $d['lab'];
    $spec = is_array($d['specialization']) ? (string)$d['specialization']['name'] : tr('Skill Tree');
    $tiles = [
        profile60_tile(profile60_tab_url('pokrok', $target, true), tr('Pokrok'), edu_number((float)$d['mastery'], 0) . ' %', $spec, 'teal'),
        profile60_tile(profile60_tab_url('lab', $target, true), tr('Linux Lab'), (int)$lab['solved_count'] . ' / ' . (int)$lab['level_count'], tr('úrovní vyřešeno'), 'teal'),
        profile60_tile(profile60_tab_url('odznaky', $target, true), tr('Odznaky'), (int)$d['badge_earned'] . ' / ' . (int)$d['badge_count'], tr('milníků: {a} / {b}', ['a' => (int)$d['achievement_earned'], 'b' => (int)$d['achievement_count']]), 'yellow'),
        profile60_tile(profile60_tab_url('arena', $target, true), tr('Aréna'), (string)(int)$d['incoming'], tr('příchozích výzev'), $d['incoming'] > 0 ? 'orange' : ''),
        profile60_tile(profile60_tab_url('body', $target, true), tr('Body'), (string)(int)$d['balance'], tr('k utracení'), 'yellow'),
        profile60_tile(profile60_tab_url('nastaveni', $target, true), tr('Nastavení'), $d['profile_filled'] ? tr('Hotovo') : tr('Doplnit'), tr('motto a představení')),
    ];
    return '<div class="p60-tiles ui-tiles">' . implode('', $tiles) . '</div>';
}

/** Graf XP za 14 dní + ukazatel levelu. */
function profile60_overview_xp(array $d): string
{
    $lvl = $d['level'];
    $points = [];
    $total = 0;
    foreach ((array)$d['timeline'] as $row) {
        $points[] = ['label' => (string)date('j.', (int)strtotime((string)$row['date'])), 'value' => (float)$row['earned']];
        $total += (int)$row['earned'];
    }
    $html = profile60_panel_open(tr('Úroveň a XP'), tr('Posledních 14 dní'))
        . profile60_meter(tr('Level {n}', ['n' => (int)$lvl['level']]), (float)$lvl['current'], (float)max(1, (int)$lvl['next']), tr('{current} / {next} XP', ['current' => (int)$lvl['current'], 'next' => (int)$lvl['next']]))
        . profile60_svg_bars($points, tr('XP za posledních 14 dní'), tr('XP'));
    if ($total === 0) $html .= profile60_empty(tr('Zatím nemáš žádné XP. Získáš je hned, jak projdeš první téma dnešní hodiny.'), tr('Dnešní hodina'), '?view=dashboard');
    return $html . profile60_panel_close();
}

function profile60_render_showcase(array $featured, array $defs, string $emptyHint = ''): void
{
    echo profile60_panel_open(tr('Vystavené odznaky'), tr('Showcase')) . '<div class="b60-showcase">';
    $shown = 0;
    foreach ($featured as $bid) {
        if (!isset($defs[$bid])) continue;
        echo badge60_card((string)$bid, (array)$defs[$bid], true);
        $shown++;
    }
    echo '</div>';
    if ($shown === 0) echo profile60_empty(tr('Zatím není vybraná žádná trofej.'), $emptyHint !== '' ? tr('Vybrat v nastavení') : '', $emptyHint);
    echo profile60_panel_close();
}

/** Cizí profil: O mně (jen to, co žák sám vyplnil a co vidí spolužáci). */
function profile60_render_about(array $profile): void
{
    $tags = static fn(array $items): array => array_values(array_filter(array_map('strval', $items), static fn(string $s): bool => $s !== ''));
    echo profile60_panel_open(tr('O mně'));
    echo ($profile['bio'] !== '' ? '<p class="p60-bio">' . e((string)$profile['bio']) . '</p>' : profile60_empty(tr('Zatím bez představení.')));
    echo profile60_tags($tags((array)$profile['skills'])) . profile60_tags($tags((array)$profile['interests']));
    echo '<p class="p60-muted-note">' . e(tr('Role v týmu: {role} · {status}', ['role' => social_role_label((string)$profile['preferred_role']), 'status' => social_team_status_label((string)$profile['team_status'])])) . '</p>';
    echo profile60_panel_close();
}

function profile60_render_overview(string $classId, string $target, array $data, bool $isMe): void
{
    $defs = learning_badge_definitions();
    $featured = array_values(array_filter((array)$data['profile']['featured_badges'], static fn($id): bool => is_string($id)));
    if ($isMe) {
        echo profile60_overview_task($data, $target) . profile60_overview_tiles($data, $target) . profile60_overview_xp($data);
        mot61_render_goals_card($classId); // v61: dnešní cíle a série
        profile60_render_showcase($featured, $defs, profile60_tab_url('nastaveni', $target, true));
        return;
    }
    profile60_render_about($data['profile']);
    profile60_render_showcase($featured, $defs);
}

/** Záložka Pokrok: nejbližší cíl, mastery po větvích, nejsilnější skills a specializace. */
function profile60_render_progress(array $data, bool $isMe): void
{
    if ($isMe) {
        $next = $data['next_skill'];
        echo is_array($next)
            ? profile60_task(tr('Nejbližší cíl'), (string)$next['skill']['name'], (string)$next['reason'] . ' · ' . edu_number((float)($next['progress']['mastery_percent'] ?? 0), 0) . ' %', tr('Pokračovat'), module_url('skill_detail', ['skill' => (string)$next['skill']['slug']]))
            : profile60_task(tr('Nejbližší cíl'), tr('Vyber si první dovednost'), tr('Ve Skill Tree najdeš dostupné dovednosti i to, co je potřeba pro odemčení dalších.'), tr('Otevřít Skill Tree'), '?view=skills');
    }
    $rows = [];
    $any = false;
    foreach ($data['branches'] as $branch => $bp) {
        $percent = (float)($bp['mastery_percent'] ?? 0);
        $any = $any || $percent > 0;
        $rows[] = ['label' => (string)($data['branch_defs'][$branch]['name'] ?? $branch), 'value' => $percent, 'max' => 100.0, 'text' => edu_number($percent, 0) . ' %'];
    }
    echo profile60_panel_open(tr('Postup po větvích'), tr('Mastery profil'), $isMe ? tr('Můj Skill Tree →') : '', $isMe ? '?view=skills' : '');
    echo $any || $rows ? profile60_meter_list($rows) : profile60_empty(tr('Zatím žádný postup.'), tr('Otevřít Skill Tree'), '?view=skills');
    echo profile60_panel_close();
    profile60_render_top_skills($data);
}

function profile60_render_top_skills(array $data): void
{
    if (!$data['top_skills'] && !$data['specialization']) return;
    echo profile60_panel_open(tr('Nejsilnější dovednosti'));
    if ($data['top_skills']) {
        $rows = [];
        foreach ($data['top_skills'] as $row) {
            $p = (float)$row['progress']['mastery_percent'];
            $rows[] = ['label' => (string)$row['skill']['name'], 'value' => $p, 'max' => 100.0, 'text' => edu_number($p, 0) . ' %'];
        }
        echo profile60_meter_list($rows);
    }
    $spec = $data['specialization'];
    if (is_array($spec)) {
        echo '<div class="p60-spec">' . profile60_meter(tr('Specializace: {name}', ['name' => (string)$spec['name']]), (float)$spec['progress'], 100.0, (int)$spec['progress'] . ' %' . (!empty($spec['unlocked']) ? ' · ' . tr('odemčeno') : ''), 'yellow') . '</div>';
    }
    echo profile60_panel_close();
}

/**
 * Záložka „body“ – jen vlastní profil (nikdy se nevolá pro cizí, viz profile60_current_tab()).
 * Zůstatek, získáno/utraceno, graf přírůstku po týdnech a posledních 20 pohybů.
 */
function profile60_render_points(array $data): void
{
    $summary = $data['summary'];
    echo profile60_task(tr('Zůstatek'), trn(['one' => '{n} bod', 'few' => '{n} body', 'other' => '{n} bodů'], (int)$summary['balance']), tr('Body utratíš za kosmetiku a další odměny v obchodě.'), tr('Otevřít obchod'), '?view=obchod', 'yellow');
    echo profile60_panel_open(tr('Přehled bodů'));
    echo profile60_stats([[(string)(int)$summary['earned'], tr('získáno celkem'), 'teal'], [(string)(int)$summary['spent'], tr('utraceno celkem'), 'orange']]);
    echo profile60_svg_bars($data['weekly'], tr('Body získané po týdnech'), tr('bodů'));
    echo profile60_panel_close();
    echo profile60_panel_open(tr('Poslední pohyby'));
    if (!$summary['history']) {
        echo profile60_empty(tr('Zatím žádný pohyb bodů.'), tr('Jak body získat? Splň úlohy v dnešní hodině.'), '?view=dashboard');
    } else {
        echo '<ul class="p60-points-list">';
        foreach ($summary['history'] as $row) {
            $delta = (int)$row['delta'];
            echo '<li><span>' . e((string)$row['text']) . '</span><b class="' . ($delta >= 0 ? 'p60-plus' : 'p60-minus') . '">' . ($delta >= 0 ? '+' : '') . $delta . '</b></li>';
        }
        echo '</ul>';
    }
    echo profile60_panel_close();
}

function profile60_render_lab(array $data): void
{
    $next = $data['next'];
    echo is_array($next)
        ? profile60_task(tr('Další úroveň'), $next['pack'], $next['title'] !== '' ? $next['title'] : tr('Pokračuj, kde jsi skončil/a.'), tr('Otevřít Lab'), '?view=lab')
        : profile60_task(tr('Linux Lab'), tr('Všechny úrovně hotové'), tr('Zkus výzvu spolužákovi v Aréně.'), tr('Otevřít Arénu'), profile60_tab_url('arena', '', true), 'yellow');
    $rows = [];
    foreach ($data['packs'] as $p) { $rows[] = ['label' => $p['title'], 'value' => (float)$p['done'], 'max' => max(1.0, (float)$p['total']), 'text' => $p['done'] . ' / ' . $p['total']]; }
    echo profile60_panel_open(tr('Postup po balíčcích'), tr('Linux Lab'), '', '');
    echo '<p class="p60-muted-note">' . e(tr('{done} z {total} úrovní vyřešeno.', ['done' => (int)$data['solved_count'], 'total' => (int)$data['level_count']])) . '</p>';
    echo $rows ? profile60_meter_list($rows) : profile60_empty(tr('Lab zatím nemá žádné balíčky.'));
    echo profile60_panel_close();
    if (!$data['badges']) return;
    echo profile60_panel_open(tr('Odznaky za dovednosti v terminálu'), tr('Lab odznaky')) . '<div class="b60-grid">';
    foreach ($data['badges'] as $b) {
        $meta = ['title' => (string)$b['title'], 'text' => (string)$b['hint'], 'condition' => (string)$b['hint'], 'rarity' => 'epic', 'category' => 'lab'];
        echo badge60_card('lab_' . (string)$b['id'], $meta, !empty($b['earned']), (int)round(((float)($b['progress'] ?? 0)) * 100));
    }
    echo '</div>' . profile60_panel_close();
}

/** Záložka Odznaky: nejbližší cíl, vystavené, sbírka (zamčené sbalené), milníky, role a hlášení. */
function profile60_render_badges(string $classId, string $target, array $data, bool $isMe): void
{
    if ($isMe && isset($data['board'])) {
        echo profile60_badges_task($data['board'], (array)($data['achievements'] ?? []), (array)($data['achievement_defs'] ?? []));
    }
    profile60_render_showcase($data['featured'], $data['defs'], $isMe ? profile60_tab_url('nastaveni', $target, true) : '');
    if ($isMe) mot61_render_seasons($classId); // v61: sezónní sbírka (pololetí)
    if ($isMe && isset($data['board'])) {
        $lockedUrl = profile60_locked_url();
        v55_render_badge_board_from_data($data['board'], profile60_show_locked(), $lockedUrl);
        profile60_render_achievements((array)($data['achievements'] ?? []), (array)($data['achievement_defs'] ?? []), profile60_show_locked(), $lockedUrl);
    }
    if (function_exists('render_profile_role_experience')) {
        render_profile_role_experience($classId, $target, $isMe);
    }
    // v60 · souhrn hlášení chyb/návrhů – jen vlastní profil.
    if ($isMe && function_exists('feedback60_render_profile_summary')) feedback60_render_profile_summary($classId, $target);
}

const P60_BADGE_PAGE = 8;
const P60_NEAR_PAGE = 2;
const P60_MILESTONE_PAGE = 4;

/** Zamčené odznaky a milníky se vykreslí jen na samostatné URL ?zamcene=1 (bez JS i s JS stejně lehká stránka). */
function profile60_show_locked(): bool
{
    return ($_GET['zamcene'] ?? '') === '1';
}

/** Adresa záložky Odznaky se zamčenými (kotva #zamcene) – jen vlastní profil. */
function profile60_locked_url(): string
{
    return profile60_collection_url(['zamcene' => '1']) . '#zamcene';
}

/** Adresa záložky Odznaky s doplňujícími parametry (zachová už zapnuté ?vsechny / ?zamcene). */
function profile60_collection_url(array $extra): string
{
    $keep = [];
    foreach (['vsechny', 'zamcene'] as $name) { if (($_GET[$name] ?? '') === '1') $keep[$name] = '1'; }
    return module_url('profile', ['tab' => 'odznaky'] + $extra + $keep);
}

/** ?vsechny=1 – i získané odznaky nad rámec první stránky (ať záložka zůstane lehká i u velké sbírky). */
function profile60_show_all(): bool
{
    return ($_GET['vsechny'] ?? '') === '1';
}

/** Karty k vykreslení: nejnovější získané (max. P60_BADGE_PAGE) + nejbližší rozpracované; $hidden = kolik získaných se nevešlo. */
function profile60_page_cards(array $earned, array $near, bool $all, int &$hidden): array
{
    usort($earned, static fn(array $a, array $b): int => strcmp((string)($b['earned_at'] ?? ''), (string)($a['earned_at'] ?? '')));
    $hidden = $all ? 0 : max(0, count($earned) - P60_BADGE_PAGE);
    usort($near, static fn(array $a, array $b): int => (int)($b['percent'] ?? 0) <=> (int)($a['percent'] ?? 0));
    return array_merge($all ? $earned : array_slice($earned, 0, P60_BADGE_PAGE), $all ? $near : array_slice($near, 0, P60_NEAR_PAGE));
}

/** Odkaz „Zobrazit zamčené (N)“ místo sbaleného seznamu; po otevření je seznam vidět rovnou. */
function profile60_locked_link(int $count, string $url): string
{
    return profile60_more_link(tr('Zobrazit zamčené ({n})', ['n' => $count]), $url);
}

function profile60_more_link(string $label, string $url): string
{
    return '<p class="p60-more-link"><a class="btn" href="' . e($url) . '">' . e($label) . '</a></p>';
}

/** Hlavní úkol záložky Odznaky: nejbližší nezískaný odznak (nebo milník) s procenty. */
function profile60_badges_task(array $board, array $progress, array $defs): string
{
    $best = null;
    foreach ((array)$board['progress'] as $b) {
        if ($best === null || (int)$b['percent'] > (int)$best['percent']) $best = $b;
    }
    if ($best !== null) {
        return profile60_task(tr('Nejblíž ti je'), (string)$best['title'] . ' · ' . (int)$best['percent'] . ' %', (string)($best['condition'] !== '' ? $best['condition'] : $best['text']), '', '', 'yellow');
    }
    foreach ($defs as $id => $def) {
        $p = (array)($progress[$id] ?? []);
        if (empty($p['earned']) && (int)($p['percent'] ?? 0) > 0) {
            return profile60_task(tr('Nejblíž ti je'), (string)$def['title'] . ' · ' . (int)$p['percent'] . ' %', (string)($def['text'] ?? ''), '', '', 'yellow');
        }
    }
    return profile60_task(tr('Sbírka odznaků'), tr('První odznak je za rohem'), tr('Odznaky získáš za zkoušky, projekty, Linux Lab i pravidelné učení.'), tr('Dnešní hodina'), '?view=dashboard', 'yellow');
}

/**
 * Sbírka odznaků: počitadlo, filtr (JS; bez JS jsou vidět všechny) a mřížka unikátních odznaků.
 * Získané a rozpracované jsou karty; zbylé zamčené jsou sbalené v <details>, ať stránka zůstane lehká.
 */
function v55_render_badge_board_from_data(array $board, bool $showLocked = false, string $lockedUrl = ''): void
{
    $earned = (array)$board['earned'];
    $near = (array)$board['progress'];
    $locked = (array)$board['locked'];
    $total = count($earned) + count($near) + count($locked);
    echo profile60_panel_open(tr('Získávání odznaků'), tr('Sbírka'), '', '', 'odznaky') . '<p class="p60-score"><strong>' . count($earned) . '</strong> ' . e(tr('z {total} odznaků', ['total' => $total])) . '</p>';
    profile60_render_filter($total, count($earned), $showLocked ? '' : $lockedUrl);
    echo '<div class="b60-grid" data-b60-grid>';
    $hiddenEarned = 0;
    foreach (profile60_page_cards($earned, $near, profile60_show_all(), $hiddenEarned) as $b) {
        echo badge60_card((string)$b['id'], $b, !empty($b['earned']), (int)$b['percent']);
    }
    echo '</div>';
    if ($hiddenEarned > 0) echo profile60_more_link(tr('Zobrazit všechny získané ({n})', ['n' => count($earned)]), profile60_collection_url(['vsechny' => '1']));
    if ($locked && !$showLocked) { echo profile60_locked_link(count($locked), $lockedUrl); }
    if ($locked && $showLocked) {
        echo '<h3 id="zamcene" class="p60-subhead" tabindex="-1">' . e(tr('Zamčené odznaky')) . ' (' . count($locked) . ')</h3><ul class="b60-rows">';
        foreach ($locked as $b) { echo badge60_row((string)$b['id'], $b); }
        echo '</ul>';
    }
    echo profile60_panel_close();
}

/** Filtr Všechny / Získané / Zamčené – zobrazí ho až JS (bez JS by tlačítka nic nedělala). */
function profile60_render_filter(int $total, int $earned, string $lockedUrl = ''): void
{
    echo '<div class="b60-filter" data-b60-filter hidden><div class="b60-filter-buttons" role="group" aria-label="' . e(tr('Filtr odznaků')) . '">'
        . '<button type="button" class="b60-chip" data-b60-show="all" aria-pressed="true">' . e(tr('Všechny')) . ' <b>' . $total . '</b></button>'
        . '<button type="button" class="b60-chip" data-b60-show="earned" aria-pressed="false">' . e(tr('Získané')) . ' <b>' . $earned . '</b></button>'
        . ($lockedUrl !== ''
            ? '<a class="b60-chip" href="' . e($lockedUrl) . '">' . e(tr('Zamčené')) . ' <b>' . max(0, $total - $earned) . '</b></a></div>'
            : '<button type="button" class="b60-chip" data-b60-show="locked" aria-pressed="false">' . e(tr('Zamčené')) . ' <b>' . max(0, $total - $earned) . '</b></button></div>')
        . '<p class="b60-filter-status" role="status" aria-live="polite" data-b60-status data-tpl="' . e(tr('Zobrazeno {shown} z {total}')) . '"></p></div>';
}

/** Kategorie odznaku podle druhu achievementu. */
function profile60_achievement_category(string $kind): string
{
    $map = ['xp' => 'xp', 'kb' => 'learn', 'sim' => 'lab', 'days' => 'streak', 'event' => 'exam', 'projects' => 'project',
        'excellent_projects' => 'project', 'lessons' => 'learn', 'friends' => 'team', 'teams' => 'team', 'teams_founded' => 'team',
        'special_exams' => 'exam', 'skill_dynamic' => 'skill', 'project_dynamic' => 'project'];
    return $map[$kind] ?? 'skill';
}

/** Milníky (achievementy): stejné unikátní SVG, rarita běžná / vzácná podle náročnosti; nezačaté jsou sbalené. */
function profile60_render_achievements(array $progress, array $defs, bool $showLocked = false, string $lockedUrl = ''): void
{
    if ($defs === []) return;
    $earned = 0;
    $open = '';
    $rest = '';
    $restCount = 0;
    $shownCards = 0;
    $hiddenCards = 0;
    $showAll = profile60_show_all();
    foreach ($defs as $id => $def) {
        $p = (array)($progress[$id] ?? []);
        $isEarned = !empty($p['earned']);
        $percent = (int)($p['percent'] ?? 0);
        $earned += $isEarned ? 1 : 0;
        $target = (int)($def['target'] ?? 1);
        $kind = (string)($def['kind'] ?? '');
        $meta = ['title' => (string)($def['title'] ?? $id), 'text' => (string)($def['text'] ?? ''), 'condition' => (string)($def['text'] ?? ''), 'mark' => (string)($def['mark'] ?? ''),
            'rarity' => ($target >= 15 || ($kind === 'xp' && $target >= 2500)) ? 'rare' : 'common', 'category' => profile60_achievement_category($kind)];
        if ($isEarned || $percent > 0) {
            if ($shownCards < P60_MILESTONE_PAGE || $showAll) { $open .= badge60_card('ach_' . $id, $meta, $isEarned, $percent); $shownCards++; }
            else { $hiddenCards++; }
        }
        else { if ($showLocked) $rest .= badge60_row('ach_' . $id, $meta); $restCount++; }
    }
    echo profile60_panel_open(tr('Průběžné úspěchy'), tr('Milníky'), '', '', 'milniky') . '<p class="p60-score"><strong>' . $earned . '</strong> ' . e(tr('z {total} milníků', ['total' => count($defs)])) . '</p>'
        . '<div class="b60-grid" data-b60-grid>' . $open . '</div>';
    if ($hiddenCards > 0) echo profile60_more_link(tr('Zobrazit všechny milníky ({n})', ['n' => $shownCards + $hiddenCards]), profile60_collection_url(['vsechny' => '1']));
    if ($restCount > 0 && !$showLocked) echo profile60_locked_link($restCount, $lockedUrl);
    if ($restCount > 0 && $showLocked) echo '<ul class="b60-rows">' . $rest . '</ul>';
    echo profile60_panel_close();
}
