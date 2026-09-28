<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · přehlednější profil žáka (záložky, sidebar, přehledy, grafy).
 *
 * Čistě čtecí agregace dat pro app/views (profile_v60_views.php). Žádná funkce zde nezapisuje
 * do storage (žádné storage_update/save_php_json/file_put_contents) – jen skládá to, co už
 * počítají existující moduly (social, learning, points, skills, lab). Počítá se jen to, co
 * potřebuje aktivní záložka (profile60_data() bere $tab a vypočte jen odpovídající panel).
 */

/** Whitelist záložek profilu; pořadí = pořadí v sidebaru/liště. */
function profile60_tabs(): array
{
    return ['prehled', 'pokrok', 'lab', 'odznaky', 'arena', 'body', 'nastaveni'];
}

/** Popisky záložek (i18n). */
function profile60_tab_label(string $tab): string
{
    return [
        'prehled' => tr('Přehled'),
        'pokrog' => tr('Pokrok'),
        'pokrok' => tr('Pokrok'),
        'lab' => tr('Linux Lab'),
        'odznaky' => tr('Odznaky'),
        'body' => tr('Body'),
        'nastaveni' => tr('Nastavení'),
        'arena' => tr('Aréna'),
    ][$tab] ?? $tab;
}

/**
 * Rozhodne aktivní záložku z ?tab= s ohledem na to, jestli jde o vlastní, nebo cizí profil.
 * Cizí profil smí jen prehled/pokrok/odznaky; neplatná nebo nepovolená hodnota → prehled.
 */
function profile60_current_tab(bool $isMe): string
{
    $raw = is_string($_GET['tab'] ?? null) ? (string)$_GET['tab'] : '';
    $allowed = profile60_tabs();
    if (!$isMe) {
        $allowed = array_values(array_intersect($allowed, ['prehled', 'pokrok', 'odznaky', 'arena']));
    }
    return in_array($raw, $allowed, true) ? $raw : 'prehled';
}

/**
 * Agreguje data pro jeden panel profilu. Nikdy nezapisuje. $student = řádek z project_students_for_class().
 * @return array<string,mixed>
 */
function profile60_data(string $classId, string $target, string $skillKey, bool $isMe, string $tab): array
{
    switch ($tab) {
        case 'pokrok': return profile60_data_progress($classId, $skillKey);
        case 'lab': return $isMe ? profile60_data_lab($classId, $target) : [];
        case 'odznaky': return profile60_data_badges($classId, $target, $isMe);
        case 'arena': return [];
        case 'body': return $isMe ? profile60_data_points($classId, $target) : [];
        case 'nastaveni': return [];
        default: return profile60_data_overview($classId, $target, $isMe);
    }
}

function profile60_data_overview(string $classId, string $target, bool $isMe): array
{
    $profile = social_profile_get($classId, $target);
    if (!$isMe) {
        return ['profile' => $profile];
    }
    $own = learning_profile($classId);
    $lvl = function_exists('v55_level_state') ? v55_level_state($classId) : ['level' => 1, 'percent' => 0, 'current' => 0, 'next' => 1];
    $balance = function_exists('pts53_balance') ? pts53_balance($classId, $target) : 0;
    $timeline = function_exists('learning_xp_timeline') ? learning_xp_timeline($own, 14) : [];
    $achievements = function_exists('learning_achievement_progress') ? learning_achievement_progress($classId, [], (array)($GLOBALS['simulations'][$classId] ?? [])) : [];
    $earnedAchievements = array_filter($achievements, static fn(array $a): bool => !empty($a['earned']));
    return [
        'profile' => $profile,
        'level' => $lvl,
        'xp' => (int)($own['xp'] ?? 0),
        'balance' => $balance,
        'timeline' => $timeline,
        'achievement_count' => count($achievements),
        'achievement_earned' => count($earnedAchievements),
    ];
}

function profile60_data_progress(string $classId, string $skillKey): array
{
    $branches = skill_branch_progress_map($classId, $skillKey);
    $defs = skill_branches();
    $progress = skill_progress_map($classId, $skillKey);
    $top = [];
    foreach (skill_relevant_skills($classId) as $skill) {
        $slug = (string)$skill['slug'];
        $p = $progress[$slug] ?? [];
        if ((float)($p['mastery_percent'] ?? 0) > 0) {
            $top[] = ['skill' => $skill, 'progress' => $p];
        }
    }
    usort($top, static fn($a, $b) => (float)$b['progress']['mastery_percent'] <=> (float)$a['progress']['mastery_percent']);
    return [
        'branches' => $branches,
        'branch_defs' => $defs,
        'top_skills' => array_slice($top, 0, 5),
        'specialization' => skill_specialization_for($classId, $skillKey),
    ];
}

/** Data pro záložku „body“ – jen vlastní profil (volající to musí zajistit, funkce sama nekontroluje isMe). */
function profile60_data_points(string $classId, string $target): array
{
    $summary = function_exists('pts60_summary') ? pts60_summary($classId, $target, 20) : ['balance' => 0, 'earned' => 0, 'spent' => 0, 'history' => []];
    $weekly = function_exists('pts60_weekly_series') ? pts60_weekly_series($classId, $target, 8) : [];
    return ['summary' => $summary, 'weekly' => $weekly];
}

function profile60_data_lab(string $classId, string $target): array
{
    $solved = function_exists('lab57_solved') ? lab57_solved($classId, $target, 'practice') : [];
    $packs = function_exists('lab57_packs') ? lab57_packs() : [];
    $rows = [];
    $doneTotal = 0;
    $levelTotal = 0;
    foreach ($packs as $packId => $pack) {
        $prog = function_exists('lab57_pack_progress') ? lab57_pack_progress((string)$packId, $solved) : ['done' => 0, 'total' => 0];
        $doneTotal += (int)$prog['done'];
        $levelTotal += (int)$prog['total'];
        $rows[] = ['id' => $packId, 'title' => (string)($pack['title'] ?? $packId), 'done' => (int)$prog['done'], 'total' => (int)$prog['total']];
    }
    $badges = function_exists('lab58_badges') ? lab58_badges($classId, $target) : [];
    return ['packs' => $rows, 'solved_count' => $doneTotal, 'level_count' => $levelTotal, 'badges' => $badges];
}

function profile60_data_badges(string $classId, string $target, bool $isMe): array
{
    $profile = social_profile_get($classId, $target);
    $badgeDefs = learning_badge_definitions();
    $featured = array_values(array_filter((array)$profile['featured_badges'], static fn($id): bool => is_string($id)));
    $out = ['featured' => $featured, 'defs' => $badgeDefs];
    if ($isMe) {
        $out['board'] = function_exists('v55_badge_board') ? v55_badge_board($classId, [], (array)($GLOBALS['simulations'][$classId] ?? [])) : ['earned' => [], 'progress' => [], 'locked' => [], 'total' => 0];
        $out['achievements'] = function_exists('learning_achievement_progress') ? learning_achievement_progress($classId, [], (array)($GLOBALS['simulations'][$classId] ?? [])) : [];
        $out['achievement_defs'] = function_exists('learning_achievement_definitions') ? learning_achievement_definitions() : [];
    }
    return $out;
}

/**
 * Inline SVG sloupcový graf (např. XP za 14 dní). $points = list<array{label:string,value:float}>.
 * Vrací <figure> s <svg role="img"> + <title>/<desc> a skrytou/rozbalitelnou tabulkou hodnot.
 */
function profile60_svg_bars(array $points, string $title, string $unit): string
{
    static $seq = 0; $seq++; $titleId = 'p60-chart-title-' . $seq;
    $max = 0.0;
    foreach ($points as $p) { $max = max($max, (float)$p['value']); }
    $w = 20; $gap = 6; $h = 96;
    $bars = '';
    $table = '';
    foreach ($points as $i => $p) {
        $val = (float)$p['value'];
        $barH = $max > 0 ? max(2, (int)round($val / $max * ($h - 14))) : 2;
        $x = $i * ($w + $gap);
        $y = $h - $barH;
        $bars .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $barH . '" rx="3"></rect>'
            . '<text x="' . ($x + $w / 2) . '" y="' . $h . '" text-anchor="middle" class="p60-svg-val">' . e((string)(int)$val) . '</text>';
        $table .= '<tr><th scope="row">' . e((string)$p['label']) . '</th><td>' . e((string)(int)$val) . ' ' . e($unit) . '</td></tr>';
    }
    $svgW = max(1, count($points)) * ($w + $gap);
    $svg = '<figure class="p60-chart"><svg role="img" aria-labelledby="' . $titleId . '" viewBox="0 0 ' . $svgW . ' ' . ($h + 4) . '" preserveAspectRatio="xMinYMid meet">'
        . '<title id="' . $titleId . '">' . e($title) . '</title><desc>' . e(tr('Sloupcový graf, hodnoty jsou i v tabulce pod grafem.')) . '</desc>'
        . $bars . '</svg>'
        . '<details><summary>' . e(tr('Zobrazit jako tabulku')) . '</summary><table class="p60-data-table"><caption>' . e($title) . '</caption><tbody>' . $table . '</tbody></table></details>'
        . '</figure>';
    return $svg;
}

/**
 * Inline SVG vodorovné pruhy (mastery po větvích, balíčky labu…). $rows = list<array{label:string,value:float,max:float}>.
 */
function profile60_svg_hbars(array $rows, string $title, string $unit): string
{
    static $seq = 0; $seq++; $titleId = 'p60-hchart-title-' . $seq;
    $barsHtml = '';
    $table = '';
    $rowH = 26; $w = 220;
    foreach ($rows as $i => $r) {
        $max = (float)($r['max'] ?? 100);
        $val = (float)$r['value'];
        $pct = $max > 0 ? min(1, $val / $max) : 0;
        $y = $i * $rowH;
        $barsHtml .= '<text x="0" y="' . ($y + 12) . '" class="p60-svg-label">' . e((string)$r['label']) . '</text>'
            . '<rect x="0" y="' . ($y + 16) . '" width="' . $w . '" height="8" rx="4" class="p60-track"></rect>'
            . '<rect x="0" y="' . ($y + 16) . '" width="' . (int)round($w * $pct) . '" height="8" rx="4" class="p60-fill"></rect>'
            . '<text x="' . ($w + 8) . '" y="' . ($y + 24) . '" class="p60-svg-val">' . e((string)(int)round($val)) . ($unit !== '' ? ' ' . e($unit) : '') . '</text>';
        $table .= '<tr><th scope="row">' . e((string)$r['label']) . '</th><td>' . e((string)(int)round($val)) . ' ' . e($unit) . '</td></tr>';
    }
    $h = max(1, count($rows)) * $rowH + 4;
    $svg = '<figure class="p60-chart p60-chart-h"><svg role="img" aria-labelledby="' . $titleId . '" viewBox="0 0 ' . ($w + 60) . ' ' . $h . '" preserveAspectRatio="xMinYMid meet">'
        . '<title id="' . $titleId . '">' . e($title) . '</title><desc>' . e(tr('Vodorovné pruhy, hodnoty jsou i v tabulce pod grafem.')) . '</desc>'
        . $barsHtml . '</svg>'
        . '<details><summary>' . e(tr('Zobrazit jako tabulku')) . '</summary><table class="p60-data-table"><caption>' . e($title) . '</caption><tbody>' . $table . '</tbody></table></details>'
        . '</figure>';
    return $svg;
}
