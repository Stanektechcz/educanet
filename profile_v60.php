<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · přehlednější profil žáka (záložky, sidebar, přehledy, grafy).
 *
 * Čistě čtecí agregace dat pro profile_v60_views.php. Žádná funkce zde nezapisuje do storage
 * (žádné storage_update/save_php_json/file_put_contents) – jen skládá to, co už počítají existující
 * moduly (social, learning, points, skills, lab). Počítá se jen to, co potřebuje aktivní záložka
 * (profile60_data() vypočte jen odpovídající panel); opakovaná volání v rámci jednoho vykreslení
 * (profil, kosmetika, počet výzev) jdou přes profile60_memo(), takže se nic nečte dvakrát.
 */

/** Whitelist záložek profilu; pořadí = pořadí v sidebaru/liště. */
function profile60_tabs(): array
{
    return ['prehled', 'pokrok', 'lab', 'odznaky', 'arena', 'body', 'nastaveni'];
}

/**
 * Záložky vlastního profilu pro třídu: pilotní třídy (kompetence v62) mají jako hlavní záložky „kompetence“ (mapa) a hned za ní
 * „rust“ (Můj růst: cíle, příběh růstu, sdílení – v67). Do profile60_public_tabs() obě záměrně nepatří; cizí profil je dostane
 * jen přes profile60_public_tabs_for(), a jen když je žák sám sdílí.
 */
function profile60_tabs_for(?string $classId): array
{
    $tabs = profile60_tabs();
    if ($classId === null || !function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId)) return $tabs;
    array_unshift($tabs, 'kompetence', 'rust');
    return $tabs;
}

/** Záložky, které smí vidět cizí profil (spolužák). */
function profile60_public_tabs(): array
{
    return ['prehled', 'pokrok', 'odznaky', 'arena'];
}

/**
 * v67: záložky cizího profilu = základní čtyři + „kompetence" a „rust" podle toho, co žák sdílí (výchozí nic).
 */
function profile60_public_tabs_for(?string $classId, ?string $target): array
{
    $tabs = profile60_public_tabs();
    if ($classId === null || $target === null || $target === '' || !function_exists('grow67_public_flags')) return $tabs;
    $flags = grow67_public_flags($classId, $target);
    return array_merge($flags['competencies'] ? ['kompetence'] : [], $flags['timeline'] ? ['rust'] : [], $tabs);
}

/** Popisky záložek (i18n). */
function profile60_tab_label(string $tab): string
{
    return [
        'prehled' => tr('Přehled'),
        'pokrok' => tr('Pokrok'),
        'kompetence' => tr('Kompetence'),
        'rust' => tr('Můj růst'),
        'lab' => tr('Linux Lab'),
        'odznaky' => tr('Odznaky'),
        'body' => tr('Body'),
        'nastaveni' => tr('Nastavení'),
        'arena' => tr('Aréna'),
    ][$tab] ?? $tab;
}

/**
 * Rozhodne aktivní záložku z ?tab= s ohledem na to, jestli jde o vlastní, nebo cizí profil.
 * Cizí profil smí jen prehled/pokrok/odznaky/arena; neplatná nebo nepovolená hodnota → prehled.
 */
function profile60_current_tab(bool $isMe, ?string $classId = null, ?string $target = null): string
{
    $raw = is_string($_GET['tab'] ?? null) ? (string)$_GET['tab'] : '';
    $allowed = $isMe ? profile60_tabs_for($classId) : profile60_public_tabs_for($classId, $target);
    if ($raw === '' && $isMe && in_array('kompetence', $allowed, true)) return 'kompetence';   // v67: mapa kompetencí je hlavní záložka pilotních tříd
    return in_array($raw, $allowed, true) ? $raw : 'prehled';
}

/**
 * Paměť jednoho vykreslení: $compute se zavolá jen poprvé pro daný klíč. profile60_memo_reset() ji
 * vyprázdní (volá se na začátku každého vykreslení profilu, aby nic nezůstalo z dřívějšího požadavku).
 */
function profile60_memo(string $key, callable $compute)
{
    $store = &$GLOBALS['profile60_memo'];
    if (!is_array($store)) $store = [];
    if (!array_key_exists($key, $store)) $store[$key] = $compute();
    return $store[$key];
}

function profile60_memo_reset(): void
{
    $GLOBALS['profile60_memo'] = [];
}

/** Sociální profil (motto, bio, …) – načte se jednou na vykreslení. */
function profile60_social(string $classId, string $target): array
{
    return profile60_memo('social|' . $target, static fn(): array => social_profile_get($classId, $target));
}

/** Počet příchozích výzev v Aréně (sidebar i úkol záložky). */
function profile60_incoming(string $classId, string $target): int
{
    return profile60_memo('incoming|' . $target, static fn(): int => function_exists('arena60_incoming_count') ? (int)arena60_incoming_count($classId, $target) : 0);
}

/**
 * Agreguje data pro jeden panel profilu. Nikdy nezapisuje.
 * @return array<string,mixed>
 */
function profile60_data(string $classId, string $target, string $skillKey, bool $isMe, string $tab): array
{
    switch ($tab) {
        case 'pokrok': return profile60_data_progress($classId, $skillKey, $isMe);
        case 'lab': return $isMe ? profile60_data_lab($classId, $target) : [];
        case 'odznaky': return profile60_data_badges($classId, $target, $isMe);
        case 'body': return $isMe ? profile60_data_points($classId, $target) : [];
        case 'arena':
        case 'kompetence':
        case 'rust':
        case 'nastaveni': return [];
        default: return profile60_data_overview($classId, $target, $skillKey, $isMe);
    }
}

/** Počet položek z uložené mapy, které jsou ve výčtu definic (zastaralé klíče se nepočítají). */
function profile60_count_known($stored, array $defs): int
{
    return count(array_intersect_key(is_array($stored) ? $stored : [], $defs));
}

function profile60_data_overview(string $classId, string $target, string $skillKey, bool $isMe): array
{
    $profile = profile60_social($classId, $target);
    if (!$isMe) {
        return ['profile' => $profile, 'featured_skills' => array_values(array_filter((array)($profile['featured_skills'] ?? []), 'is_string'))];
    }
    $own = learning_profile($classId);
    $lvl = function_exists('v55_level_state') ? v55_level_state($classId) : ['level' => 1, 'percent' => 0, 'current' => 0, 'next' => 1, 'remaining' => 0];
    $lab = profile60_data_lab_summary($classId, $target);
    return [
        'profile' => $profile,
        'level' => $lvl,
        'xp' => (int)($own['xp'] ?? 0),
        'balance' => function_exists('pts53_balance') ? pts53_balance($classId, $target) : 0,
        'timeline' => function_exists('learning_xp_timeline') ? learning_xp_timeline($own, 14) : [],
        'achievement_count' => count(learning_achievement_definitions()),
        'achievement_earned' => profile60_count_known($own['achievements'] ?? [], learning_achievement_definitions()),
        'badge_count' => count(learning_badge_definitions()),
        'badge_earned' => profile60_count_known($own['badges'] ?? [], learning_badge_definitions()),
        'lab' => $lab,
        'mastery' => function_exists('skill_overall_mastery') ? (float)skill_overall_mastery($classId, $skillKey) : 0.0,
        'specialization' => function_exists('skill_specialization_for') ? skill_specialization_for($classId, $skillKey) : null,
        'incoming' => profile60_incoming($classId, $target),
        'next_skill' => function_exists('skill_next_recommendation') ? skill_next_recommendation($classId, $skillKey) : null,
        'profile_filled' => trim((string)$profile['headline']) !== '' && trim((string)$profile['bio']) !== '',
    ];
}

function profile60_data_progress(string $classId, string $skillKey, bool $isMe): array
{
    $branches = skill_branch_progress_map($classId, $skillKey);
    $progress = skill_progress_map($classId, $skillKey);
    $top = [];
    foreach (skill_relevant_skills($classId) as $skill) {
        $p = $progress[(string)$skill['slug']] ?? [];
        if ((float)($p['mastery_percent'] ?? 0) > 0) $top[] = ['skill' => $skill, 'progress' => $p];
    }
    usort($top, static fn($a, $b) => (float)$b['progress']['mastery_percent'] <=> (float)$a['progress']['mastery_percent']);
    return [
        'branches' => $branches,
        'branch_defs' => skill_branches(),
        'top_skills' => array_slice($top, 0, 5),
        'specialization' => skill_specialization_for($classId, $skillKey),
        'next_skill' => ($isMe && function_exists('skill_next_recommendation')) ? skill_next_recommendation($classId, $skillKey) : null,
    ];
}

/** Data pro záložku „body“ – jen vlastní profil (volající to musí zajistit, funkce sama nekontroluje isMe). */
function profile60_data_points(string $classId, string $target): array
{
    $summary = function_exists('pts60_summary') ? pts60_summary($classId, $target, 20) : ['balance' => 0, 'earned' => 0, 'spent' => 0, 'history' => []];
    $weekly = function_exists('pts60_weekly_series') ? pts60_weekly_series($classId, $target, 8) : [];
    return ['summary' => $summary, 'weekly' => $weekly];
}

/**
 * Postup po balíčcích Linux Labu (jedno čtení stavu žáka) + nejbližší nedokončená úroveň.
 * @return array{packs:list<array<string,mixed>>,solved_count:int,level_count:int,next:?array{pack:string,title:string}}
 */
function profile60_data_lab_summary(string $classId, string $target): array
{
    return profile60_memo('lab|' . $target, static function () use ($classId, $target): array {
        $solved = function_exists('lab57_solved') ? lab57_solved($classId, $target, 'practice') : [];
        $rows = [];
        $done = 0;
        $total = 0;
        $next = null;
        foreach (function_exists('lab57_packs') ? lab57_packs() : [] as $packId => $pack) {
            $prog = lab57_pack_progress((string)$packId, $solved);
            $done += (int)$prog['done'];
            $total += (int)$prog['total'];
            $rows[] = ['id' => (string)$packId, 'title' => (string)($pack['title'] ?? $packId), 'done' => (int)$prog['done'], 'total' => (int)$prog['total']];
            if ($next === null && $prog['next'] !== null) {
                $level = function_exists('lab57_level') ? lab57_level((string)$prog['next']) : null;
                $next = ['pack' => (string)($pack['title'] ?? $packId), 'title' => (string)($level['title'] ?? '')];
            }
        }
        return ['packs' => $rows, 'solved_count' => $done, 'level_count' => $total, 'next' => $next];
    });
}

function profile60_data_lab(string $classId, string $target): array
{
    $summary = profile60_data_lab_summary($classId, $target);
    $summary['badges'] = function_exists('lab58_badges') ? lab58_badges($classId, $target) : [];
    return $summary;
}

/**
 * Odznaky: vystavené (všichni), u vlastního profilu navíc rozdělení sbírky a milníků na získané,
 * rozpracované a zamčené (zamčené se v UI sbalí) – ať se nevykresluje celý katalog najednou.
 */
function profile60_data_badges(string $classId, string $target, bool $isMe): array
{
    $profile = profile60_social($classId, $target);
    $out = ['featured' => array_values(array_filter((array)$profile['featured_badges'], static fn($id): bool => is_string($id))), 'defs' => learning_badge_definitions()];
    if (!$isMe) return $out;
    $sims = (array)($GLOBALS['simulations'][$classId] ?? []);
    $out['board'] = function_exists('v55_badge_board') ? v55_badge_board($classId, [], $sims) : ['earned' => [], 'progress' => [], 'locked' => [], 'total' => 0];
    $out['achievements'] = function_exists('learning_achievement_progress') ? learning_achievement_progress($classId, [], $sims) : [];
    $out['achievement_defs'] = function_exists('learning_achievement_definitions') ? learning_achievement_definitions() : [];
    return $out;
}

/**
 * Inline SVG sloupcový graf (např. XP za 14 dní). $points = list<array{label:string,value:float}>.
 * Vrací <figure> s <svg role="img"> + <title>/<desc> a rozbalitelnou tabulkou hodnot.
 */
function profile60_svg_bars(array $points, string $title, string $unit): string
{
    static $seq = 0; $seq++; $titleId = 'p60-chart-title-' . $seq;
    $max = 0.0;
    foreach ($points as $p) { $max = max($max, (float)$p['value']); }
    $w = 20; $gap = 6; $h = 96; $last = count($points) - 1;
    $bars = '';
    $table = '';
    foreach ($points as $i => $p) {
        $val = (float)$p['value'];
        $barH = $max > 0 ? max(2, (int)round($val / $max * ($h - 16))) : 2;
        $x = $i * ($w + $gap);
        $bars .= '<rect' . ($i === $last ? ' class="is-last"' : '') . ' x="' . $x . '" y="' . ($h - $barH - 12) . '" width="' . $w . '" height="' . $barH . '" rx="3"></rect>'
            . '<text x="' . ($x + $w / 2) . '" y="' . ($h - 1) . '" text-anchor="middle" class="p60-svg-val">' . e((string)$p['label']) . '</text>';
        $table .= '<tr><th scope="row">' . e((string)$p['label']) . '</th><td>' . e((string)(int)$val) . ' ' . e($unit) . '</td></tr>';
    }
    $svgW = max(1, count($points)) * ($w + $gap);
    return '<figure class="p60-chart"><svg role="img" aria-labelledby="' . $titleId . '" viewBox="0 0 ' . $svgW . ' ' . ($h + 2) . '" preserveAspectRatio="xMinYMid meet">'
        . '<title id="' . $titleId . '">' . e($title) . '</title><desc>' . e(tr('Sloupcový graf, hodnoty jsou i v tabulce pod grafem.')) . '</desc>'
        . $bars . '</svg>'
        . '<details><summary>' . e(tr('Zobrazit jako tabulku')) . '</summary><table class="p60-data-table ui-table"><caption>' . e($title) . '</caption><tbody>' . $table . '</tbody></table></details>'
        . '</figure>';
}
