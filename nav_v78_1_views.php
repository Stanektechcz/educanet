<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v78.1 · vykreslení navigace cockpitu (texty česky, výstup přes e(), žádné jméno žáka).
 *   - strom menu v <aside id="t68-side">: lg+ boční panel, md/xs/sm drawer (Menu / Více; assets/nav-v78-1.js: fokus uvnitř, Esc, inert),
 *     bez JS se drawer otevře odkazem #t68-side (:target),
 *   - spodní lišta xs/sm (4 sekce + Více), horní lišta, drobečky se „Zpět“, rozcestník sekce,
 *   - jednotný odznak <b class="t68-badge" aria-label="…">N</b> ve všech navigacích (nav781_badges).
 * Logika je v nav_v78_1.php; rámec v68 (teacher_shell_v68.php) jen deleguje.
 */

require_once __DIR__ . '/nav_v78_1.php';

const NAV781_ASSET_VERSION = '78.1';

/** Inline SVG ikona (dekorace, aria-hidden); neznámý název = Dnes. */
function nav781_icon(string $name): string
{
    $paths = [
        'dnes' => 'M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM12 13h4v4h-4z',
        'vyuka' => 'M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5zM4 5.5v15M8 7h8M8 11h6',
        'zaci' => 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM2 21v-1a6 6 0 0 1 12 0v1M16 3.5a4 4 0 0 1 0 7.5M22 21v-1a6 6 0 0 0-4-5.6',
        'sprava' => 'M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1M15 4v4M9 10v4M17 16v4',
        'vice' => 'M4 6h16M4 12h16M4 18h16',
        'hledat' => 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM20 20l-4-4',
        'zpet' => 'M15 18l-6-6 6-6',
        'zavrit' => 'M6 6l12 12M18 6L6 18',
    ];
    return '<svg class="n781-ico" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="' . ($paths[$name] ?? $paths['dnes']) . '"/></svg>';
}

/** Jednotný odznak (stejné značky jako odznak T-14 z v77). */
function nav781_badge_html(?array $badge, string $tab = ''): string
{
    if ($badge === null || (int)$badge['n'] <= 0) return '';
    return '<b class="t68-badge" aria-label="' . e((string)$badge['label']) . '"' . ($tab === 'hodina' ? ' data-n781-b="hodina"' : '') . '>' . (int)$badge['n'] . '</b>';
}

/** Kontext požadavku pro odznaky: třída a zda ji stránka výslovně určuje (?class=). */
function nav781_request_badges(array $visibleTabs, string $classId): array
{
    $explicit = is_string($_GET['class'] ?? null) && teacher68_class_param((string)$_GET['class']) !== '';
    return nav781_badges($visibleTabs, $classId, $explicit);
}

/** Strom menu (obsah <aside id="t68-side">): hlavička draweru, vyhledávání, 4 sekce se skupinami, nástroje pro úzké displeje. */
function nav781_tree_html(string $tab, string $classId, array $visibleTabs, string $sectionParam): string
{
    $map = teacher68_tab_map();
    $sections = nav781_sections();
    $active = nav781_section_of($tab, $sectionParam);
    $badges = nav781_request_badges($visibleTabs, $classId);
    $html = '<div class="ds78 n781-tree"><div class="n781-head"><strong id="n781-title">Navigace</strong>'
        . '<a class="n781-iconbtn" href="#t68-main" data-n781-close aria-label="Zavřít navigaci">' . nav781_icon('zavrit') . '</a></div>';
    if (!function_exists('pal74_enabled') || pal74_enabled($tab)) {
        $html .= '<button type="button" class="n781-search" data-command-open data-n781-search>' . nav781_icon('hledat') . '<span>Hledat stránku, třídu nebo žáka</span></button>';
    }
    $html .= '<nav class="t68-nav n781-nav" aria-label="Hlavní navigace">';
    foreach (nav781_tree($visibleTabs) as $sectionId => $groups) {
        $sectionTabs = array_merge(...array_values($groups));
        $isActive = $sectionId === $active || ($active === '' && $sectionId === 'dnes');
        $html .= '<details class="n781-sec"' . ($isActive ? ' open' : '') . '><summary' . ($sectionId === $active ? ' class="is-active"' : '') . '>'
            . nav781_icon($sections[$sectionId]['icon']) . '<span class="n781-sec-label">' . e($sections[$sectionId]['label']) . '</span>'
            . nav781_badge_html(nav781_section_badge($sectionTabs, $badges)) . '</summary>'
            . '<a class="n781-hublink" href="' . e(nav781_section_url($sectionId, $classId)) . '"' . ($tab === 'sekce' && $sectionId === $active ? ' aria-current="page"' : '') . '>Přehled sekce ' . e($sections[$sectionId]['label']) . '</a>';
        foreach ($groups as $group => $tabs) {
            if (count($groups) > 1) $html .= '<p class="n781-group">' . e(nav781_group_label($group)) . '</p>';
            $html .= '<ul class="n781-list">';
            foreach ($tabs as $t) {
                $html .= '<li><a class="ds-navlink" href="' . e(teacher68_tab_url($t, $classId)) . '"' . ($t === $tab ? ' aria-current="page"' : '') . ' data-n781-tab="' . e($t) . '">'
                    . '<span>' . e($map[$t]['label']) . '</span>' . nav781_badge_html($badges[$t] ?? null, $t) . '</a></li>';
            }
            $html .= '</ul>';
        }
        $html .= '</details>';
    }
    return $html . '</nav><div class="n781-tools">' . nav781_account_html($tab, $classId) . '</div></div>';
}

/** Vzhled, účet a odhlášení (v horní liště od 1024 px, v draweru pod menu na užších displejích). */
function nav781_account_html(string $tab, string $classId): string
{
    $account = '<span class="t68-user">' . e(teacher_display_name()) . '</span><span class="ops-role-badge">' . e(teacher_role_label()) . '</span>';
    if (teacher59_mode() !== 'legacy') $account .= '<a class="t68-link" href="teacher.php?tab=ucet">Můj účet</a>';
    $logout = '<form method="post" action="teacher.php"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="teacher_logout"><button type="submit" class="t68-btn">Odhlásit</button></form>';
    return teacher68_theme_switch_html($tab, $classId) . '<div class="t68-account">' . $account . '</div>' . $logout;
}

/** Horní lišta: logo, Menu (md), ⌘K ($palette = pal74_enabled – rozhoduje rámec v68), sbalení panelu a nástroje (lg+). */
function nav781_topbar_html(string $tab, string $classId, bool $palette = true): string
{
    return '<header class="t68-top n781-top"><a class="t68-logo" href="teacher.php"><i aria-hidden="true">ED</i><span><strong>EDUCANET</strong><small>Cockpit</small></span></a>'
        . '<a class="t68-btn n781-menu" href="#t68-side" data-n781-open aria-controls="t68-side" aria-expanded="false">' . nav781_icon('vice') . '<span>Menu</span></a>'
        . '<div class="t68-tools"><button type="button" class="t68-btn t68-collapse" data-t68-collapse aria-pressed="false" aria-controls="t68-side" hidden>Sbalit panel</button>'
        . ($palette ? '<button type="button" class="t68-btn teacher-command-button" data-command-open title="Rychlé hledání Ctrl/⌘+K" aria-label="Rychlé hledání (Ctrl+K)">⌘K</button>' : '')   // v74: na projektoru, tisku a zástupu bez palety
        . '<div class="n781-toptools">' . nav781_account_html($tab, $classId) . '</div></div></header>';
}

/** Spodní lišta pro xs/sm: 4 sekce (rozcestníky s třídou) + Více (drawer s celým menu a vyhledáváním). Na projektoru/tisku/zástupu nic. */
function nav781_bottom_html(string $tab, string $classId, array $visibleTabs, string $sectionParam): string
{
    $scrim = '<div class="ds78 n781-scrim" data-n781-scrim></div>';   // pozadí otevřeného draweru (klik = zavřít)
    if (in_array($tab, NAV781_BARE_TABS, true)) return $scrim;
    $sections = nav781_sections();
    $active = nav781_section_of($tab, $sectionParam);
    $badges = nav781_request_badges($visibleTabs, $classId);
    $html = $scrim . '<div class="ds78 n781-bar"><nav class="ds-bottomnav ds-bottomnav--fixed n781-bottom" aria-label="Sekce cockpitu"><ul>';
    foreach (nav781_tree($visibleTabs) as $sectionId => $groups) {
        $current = $sectionId === $active ? ($tab === 'sekce' ? ' aria-current="page"' : ' aria-current="true"') : '';
        $html .= '<li><a href="' . e(nav781_section_url($sectionId, $classId)) . '"' . $current . '>' . nav781_icon($sections[$sectionId]['icon'])
            . '<span>' . e($sections[$sectionId]['label']) . '</span>' . nav781_badge_html(nav781_section_badge(array_merge(...array_values($groups)), $badges)) . '</a></li>';
    }
    return $html . '<li><a href="#t68-side" data-n781-open aria-controls="t68-side" aria-expanded="false">' . nav781_icon('vice') . '<span>Více</span></a></li></ul></nav></div>';
}

/** Drobečky (Cockpit › sekce › záložka) se „Zpět“: bez JS na nadřazenou úroveň s třídou, s JS na předchozí stránku cockpitu. */
function nav781_crumbs_html(string $tab, string $classId, string $sectionParam): string
{
    $crumbs = nav781_breadcrumb($tab, $classId, $sectionParam);
    $html = '<nav class="t68-crumbs" aria-label="Drobečková navigace"><div class="ds78 n781-crumbs">';
    if (count($crumbs) > 1) $html .= '<a class="n781-back" href="' . e(nav781_back_url($tab, $classId, $sectionParam)) . '" data-n781-back>' . nav781_icon('zpet') . '<span>Zpět</span></a>';
    $html .= '<ol>';
    foreach ($crumbs as $i => [$label, $url]) {
        $last = $i === count($crumbs) - 1;
        $html .= '<li>' . ($url !== null && !$last ? '<a href="' . e($url) . '">' . e($label) . '</a>' : '<span aria-current="page">' . e($label) . '</span>') . '</li>';
    }
    return $html . '</ol></div></nav>';
}

/** Rozcestník sekce (?tab=sekce&sekce=…): hlavička v78 (písmo ≥ 14 px), souhrn v70, karty záložek po skupinách s odznaky. */
function nav781_render_section_hub(string $sectionParam, array $visibleTabs, string $classId): void
{
    $section = nav781_section_param($sectionParam);
    $tree = nav781_tree($visibleTabs);
    if ($section === '' || !isset($tree[$section])) {
        echo '<section class="teacher-empty wide">Tato sekce není pro vaši roli dostupná. <a href="teacher.php?tab=attention">Přejít na Dnes</a></section>';
        return;
    }
    $def = nav781_sections()[$section];
    $map = teacher68_tab_map();
    $badges = nav781_request_badges($visibleTabs, $classId);
    echo '<header class="ds78 n781-hubhead"><p class="ds-eyebrow">Sekce</p><h1>' . e($def['label']) . '</h1><p>' . e($def['hint']) . '</p></header>';
    $summary = ['dnes' => 'dnes', 'vyuka' => 'vyuka', 'zaci' => 'trida', 'sprava' => 'sprava'][$section];
    if (function_exists('c70_hub_html')) echo c70_hub_html($summary);   // v70: souhrn sekce (dnešní výuka, třídy, stav správy)
    foreach ($tree[$section] as $group => $tabs) {
        echo '<div class="ds78 n781-hub">' . (count($tree[$section]) > 1 ? '<h2 class="n781-hub-h">' . e(nav781_group_label($group)) . '</h2>' : '<h2 class="c70-sr">Záložky sekce</h2>');
        echo '<ul class="t68-cards">';
        foreach ($tabs as $t) {
            $b = $badges[$t] ?? null;
            echo '<li><a href="' . e(teacher68_tab_url($t, $classId)) . '"><strong>' . e($map[$t]['label']) . '</strong>' . nav781_badge_html($b, $t)
                . '<small>' . e($map[$t]['hint']) . '</small>' . ($b !== null ? '<small class="n781-badgetext">' . e($b['label']) . '</small>' : '') . '</a></li>';
        }
        echo '</ul></div>';
    }
}

/** CSS navigace (tokeny a komponenty v78 + rámec v78.1); na záložce design78 už tokeny a komponenty načítá modul. */
function nav781_css_html(string $tab): string
{
    $out = '';
    $files = $tab === 'design78' ? ['assets/nav-v78-1.css'] : ['assets/tokens-v78.css', 'assets/components-v78.css', 'assets/nav-v78-1.css'];
    foreach ($files as $f) {
        $path = __DIR__ . '/' . $f;
        $out .= '<link rel="stylesheet" href="' . e($f . '?v=' . (is_file($path) ? (string)filemtime($path) : NAV781_ASSET_VERSION)) . '">';
    }
    return $out;
}

function nav781_js_html(): string
{
    return '<script src="assets/nav-v78-1.js?v=' . NAV781_ASSET_VERSION . '" defer></script>';
}
