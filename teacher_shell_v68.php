<?php

declare(strict_types=1);

/**
 * EDUCANET v68 · vykreslení rámce cockpitu (boční panel / horní lišta s <details>, drobečky, přepínač vzhledu, rozcestníky).
 * Logika menu je v teacher_nav_v68.php. Rámec funguje bez JavaScriptu; assets/teacher-shell-v68.js jen přidává Esc, fokus a sbalení panelu.
 * Texty cockpitu jsou vždy česky (bez tr()).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/teacher_nav_v68.php';

const TEACHER68_ASSET_VERSION = '68.0';

// Sdílené šablony (edu_css_href v bootstrap.php) si v cockpitu vyžádají tokenizovanou kopii CSS.
$GLOBALS['edu_css_mapper'] = 'teacher68_css_href';

/** Atributy <html> a obsah <head> specifické pro v68 (tokeny, tmavé tokeny, rámec). */
function teacher68_theme_attr(string $tab): string
{
    return ' data-theme="' . e(teacher68_effective_theme($tab)) . '"';
}

function teacher68_head_html(string $tab): string
{
    $scheme = ui67_color_scheme($tab, null, UI68_TEACHER_DARK_TABS);
    $v = '?v=' . TEACHER68_ASSET_VERSION;
    $html = '<meta name="color-scheme" content="' . e($scheme) . '">'
        . '<link rel="stylesheet" href="assets/tokens-v61.css' . $v . '"><link rel="stylesheet" href="assets/tokens-palette-v68.css' . $v . '">';
    return $html;
}

/** Tmavé tokeny a úpravy – poslední v kaskádě (musí přebít color-scheme: light ze starších vrstev). Při „podle systému“ s media dotazem. */
function teacher68_dark_html(string $tab): string
{
    $theme = teacher68_effective_theme($tab);
    if ($theme === 'light') return '';
    $media = $theme === 'system' ? ' media="(prefers-color-scheme: dark)"' : '';
    $v = '?v=' . TEACHER68_ASSET_VERSION;
    return '<link rel="stylesheet" href="assets/tokens-dark-v67.css?v=67.0"' . $media . '><link rel="stylesheet" href="assets/tokens-palette-dark-v68.css' . $v . '"' . $media . '><link rel="stylesheet" href="assets/teacher-dark-v68.css' . $v . '"' . $media . '><link rel="stylesheet" href="assets/cockpit-dark-v68.css' . $v . '"' . $media . '>';
}

/** Odkaz na CSS cockpitu: sdílené soubory mají tokenizovanou kopii assets/cx-<název> (tools/v68_tokenize_css.php --derive), originál zůstává žákům. */
function teacher68_css_href(string $path): string
{
    $base = basename($path);
    $derived = 'assets/cx-' . $base;
    $use = is_file(__DIR__ . '/' . $derived) ? $derived : $path;
    $file = __DIR__ . '/' . $use;
    return $use . '?v=' . (is_file($file) ? (string)filemtime($file) : TEACHER68_ASSET_VERSION);
}

function teacher68_link_html(string $path): string
{
    return '<link rel="stylesheet" href="' . e(teacher68_css_href($path)) . '">';
}

/** Všechny šablony stylů cockpitu v pořadí kaskády (tokeny → starší vrstvy → záložka → rámec v68). */
function teacher68_stylesheets_html(string $tab): string
{
    $out = teacher68_head_html($tab) . teacher68_link_html('assets/app.css');
    if ($tab === 'authoring') $out .= teacher68_link_html('assets/mastery.css');
    $out .= teacher68_link_html('assets/teacher.css') . teacher68_link_html('assets/teacher-ops-v46.css');
    if (in_array($tab, ['analytics', 'teach'], true)) $out .= teacher68_link_html('assets/hands-on-v50.css');
    if ($tab === 'teach') {
        foreach (['cognitive-v43.css', 'learning-studio-v44.css', 'visual-simulation-v45.css', 'visual-practical-v48.css', 'visual-labs-3a-v48-1.css'] as $n) $out .= teacher68_link_html('assets/' . $n);
    }
    $out .= teacher68_link_html('assets/ui-v51.css');
    if (in_array($tab, ['teach', 'session'], true)) $out .= teacher68_link_html('assets/tutorial-v52.css');
    $out .= teacher68_link_html('assets/session-v53.css') . teacher68_link_html('assets/brand-v54.css');
    if ($tab === 'arena') $out .= teacher68_link_html('assets/linux-v57.css') . teacher68_link_html('assets/arena-v57.css');
    return $out;
}

/** CSS rámce se načítá jako poslední (po starších vrstvách), JS jen v těle stránky. */
function teacher68_shell_css_html(string $tab): string
{
    return teacher68_link_html('assets/teacher-shell-v68.css') . teacher68_link_html('assets/teacher-contrast-v69.css') . teacher68_dark_html($tab);   // v69: kontrast malých popisků (AA)
}

/** Tři tlačítka vzhledu (POST s CSRF, funguje bez JS, aria-pressed = aktuální volba). */
function teacher68_theme_switch_html(string $tab, string $classId): string
{
    $current = ui67_theme_pref();
    $return = '?tab=' . rawurlencode($tab) . (teacher68_class_param($classId) !== '' ? '&class=' . rawurlencode($classId) : '');
    $labels = ['system' => 'Systém', 'light' => 'Světlý', 'dark' => 'Tmavý'];
    $html = '<form class="t68-theme" method="post" action="teacher.php" role="group" aria-label="Vzhled">'
        . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="teacher68_theme_set">'
        . '<input type="hidden" name="return" value="' . e($return) . '">';
    foreach ($labels as $value => $label) {
        $html .= '<button type="submit" name="theme" value="' . e($value) . '" aria-pressed="' . ($value === $current ? 'true' : 'false') . '">' . e($label) . '</button>';
    }
    return $html . '</form>';
}

/** Seznam sekcí s odkazy (jednou pro boční panel, podruhé pro menu na mobilu). */
function teacher68_nav_html(string $tab, string $classId, array $visibleTabs, string $sectionParam, string $label): string
{
    $map = teacher68_tab_map();
    $sections = teacher68_sections();
    $activeSection = teacher68_section_of($tab, $sectionParam);
    static $unread = null;
    $unread ??= function_exists('teacher_ops_notifications_for_current_teacher') ? count(teacher_ops_notifications_for_current_teacher(true)) : 0;
    $html = '<nav class="t68-nav" aria-label="' . e($label) . '">';
    foreach (teacher68_visible_sections($visibleTabs) as $sectionId => $tabs) {
        $open = $sectionId === $activeSection ? ' open' : '';
        $html .= '<details class="t68-sec"' . $open . '><summary>' . e($sections[$sectionId]['label']) . '</summary><ul>';
        foreach ($tabs as $t) {
            $current = $t === $tab ? ' aria-current="page"' : '';
            $badge = $t === 'attention' && $unread > 0 ? '<b class="t68-badge" aria-label="' . $unread . ' nepřečtených">' . $unread . '</b>' : '';
            $html .= '<li><a href="' . e(teacher68_tab_url($t, $classId)) . '"' . $current . '><span>' . e($map[$t]['label']) . '</span>' . $badge . '</a></li>';
        }
        $html .= '</ul></details>';
    }
    return $html . '</nav>';
}

function teacher68_breadcrumb_html(string $tab, string $classId, string $sectionParam): string
{
    $crumbs = teacher68_breadcrumb($tab, $classId, $sectionParam);
    $html = '<nav class="t68-crumbs" aria-label="Drobečková navigace"><ol>';
    foreach ($crumbs as $i => [$label, $url]) {
        $last = $i === count($crumbs) - 1;
        $html .= '<li>' . ($url !== null && !$last ? '<a href="' . e($url) . '">' . e($label) . '</a>' : '<span aria-current="page">' . e($label) . '</span>') . '</li>';
    }
    return $html . '</ol></nav>';
}

/** Horní lišta: logo, menu (mobil), nástroje (vzhled, účet, odhlášení). */
function teacher68_topbar_html(string $tab, string $classId, array $visibleTabs, string $sectionParam): string
{
    $account = '<span class="t68-user">' . e(teacher_display_name()) . '</span><span class="ops-role-badge">' . e(teacher_role_label()) . '</span>';
    if (teacher59_mode() !== 'legacy') $account .= '<a class="t68-link" href="?tab=ucet">Můj účet</a>';
    $logout = '<form method="post" action="teacher.php"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="teacher_logout"><button type="submit" class="t68-btn">Odhlásit</button></form>';
    return '<header class="t68-top"><a class="t68-logo" href="teacher.php"><i aria-hidden="true">ED</i><span><strong>EDUCANET</strong><small>Cockpit</small></span></a>'
        . '<details class="t68-menu"><summary>Menu</summary>' . teacher68_nav_html($tab, $classId, $visibleTabs, $sectionParam, 'Hlavní navigace (mobil)') . '</details>'
        . '<div class="t68-tools">' . teacher68_theme_switch_html($tab, $classId) . '<button type="button" class="t68-btn t68-collapse" data-t68-collapse aria-pressed="false" aria-controls="t68-side" hidden>Sbalit panel</button>'
        . '<button type="button" class="t68-btn teacher-command-button" data-command-open title="Příkazy Ctrl/⌘+K">⌘K</button>'
        . '<div class="t68-account">' . $account . '</div>' . $logout . '</div></header>';
}

/** Další kroky (kontextový panel s odkazy, ne tlačítka akcí). */
function teacher68_next_steps_html(string $tab, array $visibleTabs, string $classId): string
{
    $steps = teacher68_next_steps($tab, $visibleTabs, $classId);
    if ($steps === []) return '';
    $html = '<aside class="t68-next" aria-label="Další kroky"><strong>Další krok</strong><ul>';
    foreach ($steps as $s) $html .= '<li><a href="' . e($s['url']) . '">' . e($s['why']) . ' <span aria-hidden="true">→</span></a></li>';
    return $html . '</ul></aside>';
}

/** Rozcestník žáka (detail žáka): odkazy na kompetence, cesty, projekty, návrhy hodnocení, intervence a Lab. */
function teacher68_student_hub_html(string $classId, string $studentKey, array $visibleTabs): string
{
    $links = teacher68_student_hub_links($classId, $studentKey, $visibleTabs);
    if ($links === []) return '';
    $html = '<section class="t68-hub teacher-panel" aria-label="Rozcestník žáka"><h2>Další pohledy na žáka</h2><ul>';
    foreach ($links as $l) $html .= '<li><a class="t68-link" href="' . e($l['url']) . '">' . e($l['label']) . '</a></li>';
    return $html . '</ul></section>';
}

/** Rozcestník sekce (?tab=sekce&sekce=…): karty záložek, které role vidí. */
function teacher68_render_section_hub(string $sectionId, array $visibleTabs, string $classId): void
{
    $sections = teacher68_sections();
    $map = teacher68_tab_map();
    $visible = teacher68_visible_sections($visibleTabs);
    if (!isset($sections[$sectionId]) || !isset($visible[$sectionId])) {
        echo '<section class="teacher-empty wide">Tato sekce není pro vaši roli dostupná. <a href="teacher.php?tab=attention">Přejít na Dnes</a></section>';
        return;
    }
    echo '<section class="teacher-page-head"><div><div class="eyebrow">Sekce</div><h1>' . e($sections[$sectionId]['label']) . '</h1><p>' . e($sections[$sectionId]['hint']) . '</p></div></section><ul class="t68-cards">';
    foreach ($visible[$sectionId] as $t) {
        echo '<li><a href="' . e(teacher68_tab_url($t, $classId)) . '"><strong>' . e($map[$t]['label']) . '</strong><small>' . e($map[$t]['hint']) . '</small></a></li>';
    }
    echo '</ul>';
}

/** Výběr třídy pod záhlavím (jen u záložek, které třídu používají a nemají vlastní přepínač). */
function teacher68_class_bar_html(string $tab, string $classId, array $catalog, array $visibleTabs): string
{
    $def = teacher68_tab_map()[$tab] ?? null;
    if ($def === null || in_array($tab, teacher58_own_class_tabs(), true) || in_array($tab, ['pristupy', 'arena', 'session', 'intake'], true)) return '';
    if (!$def['class']) {   // globální záložka: ukáže rozsah účtu (třídy a počet lekcí), aby bylo jasné, na co se přehled vztahuje
        return '<section class="t68-classbar t68-scope" aria-label="Rozsah"><span class="t68-classbar-label">Rozsah</span><span>' . e(teacher_overview_scope_label(array_keys($catalog))) . '</span><span>' . count($catalog) * 28 . ' lekcí</span></section>';
    }
    $all = $tab === 'attention';
    $html = '<section class="t68-classbar" aria-label="Třída"><span class="t68-classbar-label">Třída</span><div class="t68-classtabs">';
    if ($all) $html .= '<a href="?tab=attention"' . (isset($_GET['class']) ? '' : ' aria-current="page"') . '>Všechny třídy</a>';
    foreach (array_keys($catalog) as $cid) {
        $url = $all ? '?tab=attention&class=' . rawurlencode($cid) : teacher_class_tab_url($tab, $cid);
        $current = $cid === $classId && (!$all || isset($_GET['class'])) ? ' aria-current="page"' : '';
        $html .= '<a href="' . e($url) . '"' . $current . '>' . e(teacher_class_label($cid)) . '</a>';
    }
    return $html . '</div></section>';
}
