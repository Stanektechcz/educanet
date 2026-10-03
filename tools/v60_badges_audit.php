<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v60 · tools/v60_badges_audit.php – audit unikátních odznaků (badges_v60.php) a jejich použití
 * v profilu (záložky Přehled/Odznaky/Nastavení).
 *
 * Kontroluje: determinismus (stejné id + stav = stejný SVG řetězec), unikátnost kombinace
 * tvar/kategorie/paleta/vzor/akcent/rarita pro všechny definované odznaky (learning_badge_definitions,
 * lab odznaky, milníky) – kolize se hlásí a FAILují, přístupné názvy (<title>, aria-label, případně
 * aria-hidden u dekorativních), rozdíl získaný × zamčený (šedý obrys, zámek, „Jak získat“), rámec podle rarity,
 * tvary z povolené sady rarity, escapování textů, absenci externích URL/skriptů, bezpečný Linux-lab invariant
 * (žádné exec/eval…), a skutečné vykreslení záložek profilu nad dočasným úložištěm.
 *
 * Dočasné úložiště (edu_audit_temp_storage) – ostrou storage/ nikdy nečte ani nezapisuje.
 * Spuštění: C:/php/php.exe tools/v60_badges_audit.php
 * Konec: V60_BADGES_AUDIT_OK checks=N failed=0 (jinak _FAIL, nenulový exit kód).
 */

$ROOT = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once $ROOT . '/tools/lib/audit_storage.php';
edu_audit_temp_storage('v60-badges');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/tools/lib/audit.php';

foreach ([
    'app/lib.php',
    'app/views/_layout.php', 'student_v55.php', 'student_v55_views.php', 'zero_friction_v50_6.php',
    'unified_page_shell_v50_7.php', 'one_task_v50_5.php', 'goal_navigator_v50_4.php', 'hands_on_learning_v50.php',
    'independent_growth_v50.php', 'learning_v56.php', 'session_v53.php', 'tutorial_v52.php', 'linux_v57_lab.php', 'arena_v57.php',
    'teacher_operations_v46.php', 'student_learning_coach_v47.php', 'student_learning_coach_views_v47.php',
    'student_learning_accelerator_v47_1.php', 'student_learning_accelerator_views_v47_1.php',
    'student_corrective_cycle_v47_2.php', 'student_corrective_cycle_views_v47_2.php', 'student_social_views.php',
    'skill_views.php', 'project_workspace_views.php', 'points_v53.php', 'learning_v56_views.php',
    'profile_v60.php', 'profile_v60_views.php', 'lab_v58_learning.php', 'badges_v60.php',
    'arena_v58_weekly.php', 'arena_v60_challenge.php', 'arena_v60_challenge_views.php',
] as $rel) {
    require_once $ROOT . '/' . $rel;
}
$GLOBALS['nextLessons'] = [];
$GLOBALS['extendedLessons'] = [];
$GLOBALS['modules'] = [];
$GLOBALS['view'] = 'profile';

$state = audit_counter();
$check = audit_checker($state);

// 1) Soubor: lint, strict_types, guard, bezpečnostní invariant, žádné externí URL.
$src = (string)file_get_contents($ROOT . '/badges_v60.php');
exec('C:/php/php.exe -l ' . escapeshellarg($ROOT . '/badges_v60.php') . ' 2>&1', $lintOut, $lintCode);
$check('php -l badges_v60.php', $lintCode === 0);
$check('declare(strict_types=1) a guard proti přímému volání', str_contains($src, 'declare(strict_types=1);') && str_contains($src, 'http_response_code(403)'), false);
$forbidden = '/\b(exec|shell_exec|system|passthru|proc_open|popen|pcntl_exec|eval|assert|create_function|fsockopen|stream_socket_client|mail)\s*\(|`|\bcurl_|\bsocket_/';
$check('badges_v60.php nepoužívá exec/eval/sockety/curl/backticky', !preg_match($forbidden, $src), false);
$check('badges_v60.php nemá externí URL ani innerHTML', !preg_match('~https?://~', $src) && !str_contains($src, 'innerHTML'), false);
$check('badges_v60.php < 800 řádků', substr_count($src, "\n") < 800, false);

// 2) Sada všech definovaných odznaků (badge + lab + milníky).
$all = [];
foreach (learning_badge_definitions() as $id => $def) { $all[(string)$id] = (array)$def; }
$labCount = 0;
foreach (lab58_badges('class_3a', 'audit-student') as $lb) {
    $labCount++;
    $all['lab_' . (string)$lb['id']] = ['title' => (string)$lb['title'], 'text' => (string)$lb['hint'], 'condition' => (string)$lb['hint'], 'rarity' => 'epic', 'category' => 'lab'];
}
$achCount = 0;
foreach (learning_achievement_definitions() as $id => $def) {
    $achCount++;
    $target = (int)($def['target'] ?? 1);
    $all['ach_' . (string)$id] = ['title' => (string)$def['title'], 'text' => (string)$def['text'], 'mark' => (string)($def['mark'] ?? ''),
        'rarity' => ($target >= 15 || ((string)($def['kind'] ?? '') === 'xp' && $target >= 2500)) ? 'rare' : 'common', 'category' => profile60_achievement_category((string)($def['kind'] ?? ''))];
}
$check('sada odznaků: alespoň 60 definic (badge + lab + milníky)', count($all) >= 60);
$check('sada obsahuje lab odznaky i milníky', $labCount >= 10 && $achCount >= 20);

// 3) Determinismus.
$deterministic = true;
$stateDiffers = true;
foreach ($all as $id => $meta) {
    $a = badge60_svg($id, $meta, true, 64);
    $b = badge60_svg($id, $meta, true, 64);
    $deterministic = $deterministic && hash('sha256', $a) === hash('sha256', $b);
    $stateDiffers = $stateDiffers && $a !== badge60_svg($id, $meta, false, 64);
}
$check('stejné id + meta + stav => stejný SVG (SHA-256) pro všech ' . count($all) . ' odznaků', $deterministic);
$check('získaný a zamčený stav se u každého odznaku liší', $stateDiffers);
$check('varianta se nemění mezi voláními (stabilní podpis)', badge60_signature(badge60_variant('level_50', $all['level_50'])) === badge60_signature(badge60_variant('level_50', $all['level_50'])));

// 4) Unikátnost kombinací (kolize se vypisují).
$bySignature = [];
$bySvg = [];
foreach ($all as $id => $meta) {
    $bySignature[badge60_signature(badge60_variant($id, $meta))][] = $id;
    $bySvg[preg_replace('~<title>.*?</title>|aria-label="[^"]*"~', '', badge60_svg($id, $meta, true, 64))][] = $id;
}
$collisions = array_filter($bySignature, static fn(array $ids): bool => count($ids) > 1);
foreach ($collisions as $sig => $ids) { echo 'INFO  kolize podpisu ' . $sig . ': ' . implode(', ', $ids) . "\n"; }
$check('žádné dva odznaky nesdílejí kombinaci tvar/kategorie/paleta/vzor/akcent/rarita (' . count($all) . ' odznaků, ' . count($collisions) . ' kolizí)', $collisions === []);
$svgCollisions = array_filter($bySvg, static fn(array $ids): bool => count($ids) > 1);
foreach ($svgCollisions as $ids) { echo 'INFO  identický obraz: ' . implode(', ', $ids) . "\n"; }
$check('žádné dva odznaky nemají identický vykreslený obraz (bez názvu)', $svgCollisions === []);
$shapes = [];
$palettes = [];
$categories = [];
foreach ($all as $id => $meta) {
    $v = badge60_variant($id, $meta);
    $shapes[$v['shape']] = true; $palettes[$v['palette']] = true; $categories[$v['category']] = true;
}
$check('sada využívá ≥ 6 tvarů, ≥ 6 palet a ≥ 8 kategorií/ikon (tvarů ' . count($shapes) . ', palet ' . count($palettes) . ', kategorií ' . count($categories) . ')', count($shapes) >= 6 && count($palettes) >= 6 && count($categories) >= 8);
echo 'INFO  systém: tvarů=' . count(array_unique(array_merge(...array_map('badge60_shape_pool', BADGE60_RARITIES)))) . ', ikon=' . count(badge60_icons()) . ' (+ číslo pro level), rarit=' . count(BADGE60_RARITIES) . ', palet=' . count(badge60_palettes()) . "\n";

// 5) Přístupnost a bezpečnost SVG.
$a11yOk = true;
$safeOk = true;
foreach ($all as $id => $meta) {
    foreach ([true, false] as $earned) {
        $svg = badge60_svg($id, $meta, $earned, 64);
        $a11yOk = $a11yOk && preg_match('~^<svg[^>]*role="img"[^>]*aria-label="[^"]+"~', $svg) === 1 && preg_match('~<title>[^<]+</title>~', $svg) === 1;
        $safeOk = $safeOk && !preg_match('~https?://|<image|<foreignObject|<script|href=|onload|onerror|style=~i', $svg);
    }
}
$check('každé SVG má role="img", aria-label a neprázdný <title>', $a11yOk);
$check('SVG bez externích URL, <image>, <script>, href a událostí', $safeOk);
$decor = badge60_svg('level_50', $all['level_50'], true, 64, true);
$check('dekorativní varianta má aria-hidden a žádnou role, ale zachová <title>', str_contains($decor, 'aria-hidden="true"') && !str_contains($decor, 'role="img"') && str_contains($decor, '<title>'));
$locked = badge60_svg('exam_perfect', $all['exam_perfect'], false, 64);
$earnedSvg = badge60_svg('exam_perfect', $all['exam_perfect'], true, 64);
$check('zamčený odznak: šedý obrys (přerušovaný), zámek a text „Jak získat“ v názvu', str_contains($locked, 'stroke-dasharray="5 4"') && str_contains($locked, '#c9d2d8') && str_contains($locked, 'M7 11V8a5 5 0 0 1 10 0v3') && str_contains($locked, 'Jak získat'));
$check('získaný odznak: barvy z palety, bez zámku a bez „Jak získat“', !str_contains($earnedSvg, 'M7 11V8a5 5 0 0 1 10 0v3') && !str_contains($earnedSvg, 'Jak získat') && !str_contains($earnedSvg, '#c9d2d8'));

// 6) Rarita: rám i sada tvarů.
$frames = [];
$poolOk = true;
foreach (BADGE60_RARITIES as $rarity) {
    $svg = badge60_svg('probe_id', ['title' => 'T', 'rarity' => $rarity], true, 64);
    $frames[] = preg_replace('~<title>.*?</title>|aria-label="[^"]*"~', '', $svg);
    $poolOk = $poolOk && in_array(badge60_variant('probe_id', ['rarity' => $rarity])['shape'], badge60_shape_pool($rarity), true);
}
$check('každá z 5 rarit má jiný vzhled (rám)', count(array_unique($frames)) === 5);
$check('tvar vždy pochází ze sady dané rarity', $poolOk);
$check('neznámá rarita padá na „common“', badge60_variant('x', ['rarity' => '<b>'])['rarity'] === 'common');

// 7) Kategorie a ikony.
$expected = ['lab_explorer' => 'lab', 'level_30' => 'level', 'skill_network_master' => 'network', 'skill_security_master' => 'security',
    'skill_design_master' => 'graphics', 'skill_renaissance' => 'skill', 'project_masterpiece' => 'project', 'prestige_exam_double' => 'exam',
    'course_mastery' => 'learn', 'active_5' => 'streak', 'friend_first' => 'team', 'xp_1000' => 'xp', 'arena_champion' => 'arena', 'feedback_first' => 'feedback', 'shop_first' => 'shop'];
$catOk = true;
foreach ($expected as $id => $cat) { $catOk = $catOk && badge60_category($id, []) === $cat; }
$check('kategorie podle id (lab, síť, grafika, aréna, tým, streak, chyby, projekty, obchod…)', $catOk);
$glyphs = [];
foreach (array_keys(badge60_icons()) as $cat) { $glyphs[] = badge60_glyph(['category' => $cat, 'mark' => ''], true, '#fff'); }
$check('každá kategorie má jinou ikonu (' . count($glyphs) . ' ikon)', count($glyphs) >= 13 && count(array_unique($glyphs)) === count($glyphs));
$levelSvg = badge60_svg('level_10', ['title' => 'L', 'rarity' => 'epic', 'mark' => '10'], true, 64);
$check('level odznak kreslí číslo (text) místo ikony', str_contains($levelSvg, '<text') && str_contains($levelSvg, '>10</text>'));

// 8) Escapování a meze.
$evil = badge60_svg('evil', ['title' => '<script>alert("x")</script>', 'condition' => '"><img src=x onerror=1>', 'rarity' => 'epic'], false, 64);
$check('názvy a podmínky jsou escapované (žádný surový <script>/<img>)', !str_contains($evil, '<script') && !str_contains($evil, '<img') && str_contains($evil, '&lt;script&gt;'));
$evilLevel = badge60_svg('level_666', ['title' => 'x', 'rarity' => 'epic', 'mark' => '<b>1</b>'], true, 64);
$check('mark u level odznaku je escapovaný a zkrácený na 3 znaky', !str_contains($evilLevel, '<b>') && str_contains($evilLevel, '&lt;b&gt;'));
$check('velikost je omezena na 24–256 px', str_contains(badge60_svg('a', [], true, 5), 'width="24"') && str_contains(badge60_svg('a', [], true, 9999), 'width="256"'));
$card = badge60_card('exam_perfect', ['title' => '<i>Titul</i>', 'text' => 'Text', 'condition' => 'Podmínka', 'rarity' => 'legendary'], false, 40);
$check('karta zamčeného odznaku: stav, podmínka, postup 40 % a escapovaný název', str_contains($card, 'data-b60-state="locked"') && str_contains($card, 'Podmínka') && str_contains($card, 'v55-badge-bar') && str_contains($card, 'width:40%') && str_contains($card, '&lt;i&gt;Titul'));
$cardEarned = badge60_card('exam_perfect', ['title' => 'Titul', 'text' => 'Text', 'rarity' => 'legendary', 'earned_at' => '2026-09-01'], true);
$check('karta získaného odznaku: stav earned, text a datum', str_contains($cardEarned, 'data-b60-state="earned"') && str_contains($cardEarned, '1. 9. 2026') && str_contains($cardEarned, '>Text<'));
[$fa, $fb] = badge60_frame_colors('mkt_frame_gold');
$check('barvy rámečku avataru jsou deterministické a ve tvaru #rrggbb', badge60_frame_colors('mkt_frame_x') === badge60_frame_colors('mkt_frame_x') && preg_match('/^#[0-9a-f]{6}$/', $fa . '') === 1 && preg_match('/^#[0-9a-f]{6}$/', $fb . '') === 1);

// 9) Skutečné vykreslení profilu (dočasné úložiště).
$classId = 'class_3a';
$students = project_students_for_class($classId);
$labels = array_values(array_map(static fn(array $s): string => (string)$s['label'], $students));
$_SESSION['next_class_id'] = $classId;
$_SESSION['student_label'] = $labels[0] ?? 'Test Žák';
unset($_GET['student']);
$render = static function (string $tab) use ($classId): string {
    $_GET['tab'] = $tab;
    return audit_capture(static function () use ($classId): void { render_profile60_view($classId, []); });
};
$badgesHtml = $render('odznaky');
$check('záložka Odznaky: mřížka s unikátními SVG, počitadlem a filtrem', str_contains($badgesHtml, 'class="b60-grid"') && str_contains($badgesHtml, 'b60-card') && str_contains($badgesHtml, 'p60-score') && str_contains($badgesHtml, 'data-b60-filter'));
$check('záložka Odznaky: karty mají stav earned/locked a postup (v55-badge-bar)', str_contains($badgesHtml, 'data-b60-state="locked"') && str_contains($badgesHtml, 'v55-badge-bar'));
$check('záložka Odznaky: sekce Milníky s achievementy', str_contains($badgesHtml, 'id="milniky"'));
$check('záložka Odznaky: bez fatální chyby', !preg_match('/Fatal error|Uncaught|Warning:/', $badgesHtml));
$overview = $render('prehled');
$check('přehled: hero s kruhovým progress ringem úrovně a klíčovými čísly', str_contains($overview, 'class="p60-ring"') && str_contains($overview, 'p60-hero-stats') && str_contains($overview, 'stroke-dasharray'));
$settings = $render('nastaveni');
$check('nastavení: skupiny O mně / Dovednosti / Vzhled / Soukromí', str_contains($settings, 'O mně') && str_contains($settings, 'Dovednosti a zájmy') && str_contains($settings, 'Vzhled profilu') && str_contains($settings, 'Soukromí'));
$check('nastavení: zachovaná akce save_student_profile, CSRF a pole formuláře', str_contains($settings, 'name="action" value="save_student_profile"') && str_contains($settings, 'name="csrf"') && str_contains($settings, 'name="headline"') && str_contains($settings, 'maxlength="80"') && str_contains($settings, 'name="bio"') && str_contains($settings, 'maxlength="320"'));
$check('nastavení: počitadla znaků, popisky <label for>, lepivé tlačítko Uložit napojené na formulář', str_contains($settings, 'data-p60-count-for="p60-f-bio"') && str_contains($settings, '<label for="p60-f-headline">') && str_contains($settings, 'form="p60-profile-form"') && str_contains($settings, 'p60-savebar') && str_contains($settings, 'aria-live="polite"'));
unset($_GET['tab']);

// 10) Statické kontroly JS/CSS: velikost a bezpečnost.
$js = (string)file_get_contents($ROOT . '/assets/profile-v60.js');
$cssSize = (int)filesize($ROOT . '/assets/profile-v60.css');
$check('JS bez innerHTML a bez externích URL', !preg_match('/innerHTML|https?:\/\//', $js), false);
$check('CSS < 25 KB (' . $cssSize . ' B) a respektuje prefers-reduced-motion', $cssSize <= 24576 && str_contains((string)file_get_contents($ROOT . '/assets/profile-v60.css'), 'prefers-reduced-motion'), false);

require __DIR__ . '/lib/v60_badges_sprite_checks.php';
v60_badges_sprite_checks($check, $ROOT, $all);

exit(audit_summary($state, 'V60_BADGES'));
