<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · navigace žákovské aplikace: drobečková navigace a spodní lišta na mobilu.
 *
 * Obě jsou obyčejné odkazy (žádné JS), s aria-current="page" na aktuální položce. Styl: assets/components-v61.css
 * (.ui-crumbs, .ui-bottomnav, tokeny z tokens-v61.css). Položky vycházejí z v55_primary_nav(), takže se menu na
 * počítači a lišta na mobilu nikdy nerozejdou. Spodní lišta se nevykresluje v testu/zamčené stránce (záměrně
 * se nedá odejít) a v otevřeném terminálu labu (překážela by řádku příkazu a klávesnici).
 */

/** Položky hlavní navigace (v55_primary_nav) jednou za požadavek – sdílí je horní menu, drobečky a spodní lišta. */
function nav61_primary_items(?string $classId, string $view): array
{
    $memo = &$GLOBALS['ui61_primary_nav'];
    if (!is_array($memo) || ($memo['view'] ?? null) !== $view || ($memo['cid'] ?? null) !== $classId) {
        $memo = ['view' => $view, 'cid' => $classId, 'items' => v55_primary_nav($classId, $view)];
    }
    return $memo['items'];
}

/** Spodní lišta se pro stránku nevykresluje (test, zamčená stránka, otevřený terminál labu). */
function nav61_bottom_hidden(string $view): bool
{
    if (in_array($view, ['test', 'kb_quiz'], true)) return true;
    if (function_exists('v55_lock_active') && v55_lock_active($view)) return true;
    if ($view !== 'lab') return false;
    foreach (['uroven', 'zavod', 'opakovani'] as $key) {
        if (isset($_GET[$key]) && is_string($_GET[$key]) && $_GET[$key] !== '') return true;
    }
    return false;
}

/**
 * Položky spodní lišty: Přehled, Materiály, Dnešní hodina (jen když běží) nebo Linux Lab, Výsledky, Profil.
 *
 * @return list<array{href:string,label:string,mark:string,active:bool,class:string}>
 */
function nav61_bottom_items(?string $classId, string $view): array
{
    $byKey = [];
    foreach (nav61_primary_items($classId, $view) as $item) {
        $views = (array)($item['views'] ?? []);
        $key = in_array('hodina', $views, true) ? 'hodina' : (in_array('lab', $views, true) ? 'lab' : basename((string)$item['href']));
        $byKey[$key] = $item;
    }
    $home = ['href' => '?view=dashboard', 'label' => trm('Přehled'), 'mark' => '⌂', 'active' => $view === 'dashboard', 'class' => ''];
    $out = [$home];
    $lessonOpen = isset($byKey['hodina']) && ((string)($byKey['hodina']['class'] ?? '') === 'is-today' || !empty($byKey['hodina']['active']));
    // Třetí místo: běží-li hodina, je tam „Dnešní hodina“; v labu samotném a bez hodiny „Linux Lab“.
    $slot3 = ($lessonOpen && empty($byKey['lab']['active'])) || !isset($byKey['lab']) ? 'hodina' : 'lab';
    if ($slot3 === 'hodina' && !$lessonOpen) $slot3 = 'none';
    foreach (['?view=materialy', $slot3, '?view=vysledky', '?view=profile'] as $want) {
        $key = str_starts_with($want, '?view=') ? basename($want) : $want;
        $item = $byKey[$key] ?? null;
        if (!is_array($item)) continue;
        $out[] = [
            'href' => (string)$item['href'], 'label' => (string)$item['label'], 'mark' => (string)$item['mark'],
            'active' => !empty($item['active']), 'class' => (string)($item['class'] === 'is-today' ? 'is-today' : ''),
        ];
    }
    return $out;
}

/** HTML spodní lišty (prázdný řetězec, když se nemá zobrazit). */
function nav61_bottom_html(?string $classId, string $view): string
{
    if (nav61_bottom_hidden($view)) return '';
    $html = '';
    foreach (nav61_bottom_items($classId, $view) as $item) {
        $label = $item['label'];
        $html .= '<li><a' . ($item['class'] !== '' ? ' class="' . e($item['class']) . '"' : '') . ' href="' . e($item['href']) . '"'
            . ($item['active'] ? ' aria-current="page"' : '') . '><i aria-hidden="true">' . nav61_bottom_icon($item['href']) . '</i><span>' . e(tr($label)) . '</span>' . ($item['class'] === 'is-today' ? '<span class="nav61-sr"> – ' . e(tr('právě běží')) . '</span>' : '') . '</a></li>';
    }
    return '<nav class="ui-bottomnav" aria-label="' . e(tr('Hlavní cíle')) . '" data-ui61-bottomnav><ul>' . $html . '</ul></nav>';
}

/**
 * Drobečky aktuální stránky: Domů › sekce (když je odkazovatelná) › stránka.
 *
 * @return list<array{label:string,href:string}> poslední položka má href '' (aktuální stránka)
 */
function nav61_crumbs(?string $classId, string $view, string $title): array
{
    $crumbs = [['label' => trm('Domů'), 'href' => '?view=dashboard']];
    foreach (nav61_primary_items($classId, $view) as $item) {
        if (!in_array($view, (array)($item['views'] ?? []), true)) continue;
        $own = '?view=' . $view;
        if ((string)$item['href'] !== $own) $crumbs[] = ['label' => (string)$item['label'], 'href' => (string)$item['href']];
        break;
    }
    $crumbs[] = ['label' => $title, 'href' => ''];
    return $crumbs;
}

/** HTML drobečkové navigace; $titleIsContent = název je výukový obsah (zůstává česky, atribut lang). */
function nav61_breadcrumb_html(?string $classId, string $view, string $title, bool $titleIsContent = false): string
{
    if ($view === 'dashboard') return '';
    $items = '';
    foreach (nav61_crumbs($classId, $view, $title) as $crumb) {
        $crumbLabel = $crumb['label'];
        if ($crumb['href'] !== '') {
            $items .= '<li><a href="' . e($crumb['href']) . '">' . e(tr($crumbLabel)) . '</a></li>';
            continue;
        }
        $items .= '<li><span aria-current="page"' . ($titleIsContent ? edu_content_lang_attr() : '') . '>' . e($crumb['label']) . '</span></li>';
    }
    return '<nav class="ui-crumbs" aria-label="' . e(tr('Drobečková navigace')) . '"><ol>' . $items . '</ol></nav>';
}

/** Ikona navigace: <span> s maskou z assets/nav-v61.css (SVG je v CSS, ne v každé stránce); neznámý název = domů. */
function nav61_icon(string $name): string
{
    $known = ['today', 'learn', 'play', 'projects', 'profile', 'home', 'terminal', 'chart', 'clock', 'menu'];
    return '<span class="nav61-ico nav61-ico--' . (in_array($name, $known, true) ? $name : 'home') . '" aria-hidden="true"></span>';
}

/** Ikona položky spodní lišty podle cíle (dashboard/materialy/lab/hodina/vysledky/profile). */
function nav61_bottom_icon(string $href): string
{
    $map = ['dashboard' => 'home', 'materialy' => 'learn', 'lab' => 'terminal', 'hodina' => 'clock', 'vysledky' => 'chart', 'profile' => 'profile'];
    parse_str((string)substr($href, (int)strpos($href, '?') + 1), $q);
    return nav61_icon($map[(string)($q['view'] ?? '')] ?? 'home');
}

/** Pomocná položka podmenu; $views = pohledy, při nichž je položka „aktuální“ (prázdné = nikdy, např. odkaz na sekci). */
function nav61_entry(string $href, string $label, array $views, string $view, string $class = ''): array
{
    return ['href' => $href, 'label' => $label, 'class' => $class, 'active' => in_array($view, $views, true)];
}

/** Skupina Dnes: Přehled, Dnešní hodina (když ji třída má), Kalendář. */
function nav61_group_today(?array $lesson, string $view): array
{
    $items = [nav61_entry('?view=dashboard', trm('Přehled'), ['dashboard'], $view)];
    if ($lesson !== null) $items[] = nav61_entry('?view=hodina', trm('Dnešní hodina'), ['hodina', 'intake'], $view, (string)$lesson['class']);
    $items[] = nav61_entry('?view=calendar', trm('Kalendář'), ['calendar'], $view);
    return ['key' => 'today', 'label' => trm('Dnes'), 'icon' => 'today', 'flag' => (string)($lesson['class'] ?? '') === 'is-today' ? 'today' : '', 'items' => $items];
}

/** Skupina Učení: materiály, témata, programy, příkazy, výsledky, pomoc. */
function nav61_group_learn(string $view): array
{
    return ['key' => 'learn', 'label' => trm('Učení'), 'icon' => 'learn', 'flag' => '', 'items' => [
        nav61_entry('?view=materialy', trm('Materiály'), ['materialy', 'lekce', 'course', 'topics', 'tools', 'knowledgebase', 'kb_lesson', 'tutorial'], $view),
        nav61_entry('?view=materialy&sekce=temata', trm('Témata a vysvětlení'), [], $view),
        nav61_entry('?view=materialy&sekce=programy', trm('Programy a zkratky'), [], $view),
        nav61_entry('?view=prikazy', trm('Linux příkazy'), ['prikazy'], $view),
        nav61_entry('?view=vysledky', trm('Výsledky'), ['vysledky', 'skills'], $view),
        nav61_entry('?view=study_loop', trm('Potřebuju pomoct'), ['study_loop', 'study', 'mistakes'], $view),
    ]];
}

/** Skupina Hry a aréna (jen když je lab pro třídu zapnutý nebo běží živá aktivita). */
function nav61_group_play(array $lab, string $view): array
{
    return ['key' => 'play', 'label' => trm('Hry a aréna'), 'icon' => 'play', 'flag' => !empty($lab['live']) ? 'live' : '', 'items' => [
        nav61_entry((string)$lab['href'], (string)$lab['label'], ['lab'], $view, (string)$lab['class'] === 'is-live' ? 'is-live' : ''),
        nav61_entry('?view=roboti', trm('Roboti'), ['roboti'], $view),
        nav61_entry('?view=hry', trm('Týmové hry'), ['hry'], $view),
        nav61_entry('?view=hadanka', trm('Hádanka týdne'), ['hadanka'], $view),
        nav61_entry('?view=ctf', trm('CTF týden'), ['ctf'], $view),
        nav61_entry('?view=incident', trm('Incidenty'), ['incident'], $view),
    ]];
}

/**
 * Hlavní navigace seskupená do míst: Dnes, Učení, Hry a aréna, Projekty, Profil. Stav „dnes běží hodina“ a
 * „živá aktivita“ labu přebírá z v55_primary_nav(), takže se shoduje se spodní lištou a drobečky.
 *
 * @return list<array{key:string,label:string,icon:string,active:bool,flag:string,items:list<array{href:string,label:string,class:string,active:bool}>}>
 */
function nav61_groups(?string $classId, string $view): array
{
    $byKey = [];
    foreach (nav61_primary_items($classId, $view) as $item) {
        $views = (array)($item['views'] ?? []);
        $byKey[in_array('hodina', $views, true) ? 'hodina' : (in_array('lab', $views, true) ? 'lab' : basename((string)$item['href']))] = $item;
    }
    $groups = [nav61_group_today($byKey['hodina'] ?? null, $view), nav61_group_learn($view)];
    if (isset($byKey['lab'])) $groups[] = nav61_group_play($byKey['lab'], $view);
    $groups[] = ['key' => 'projects', 'label' => trm('Projekty'), 'icon' => 'projects', 'flag' => '', 'items' => [
        nav61_entry('?view=projekty', trm('Moje projekty'), ['projekty'], $view),
        nav61_entry('?view=project_lobbies', trm('Týmy a projekty'), ['project_lobbies', 'project_workspace'], $view),
        nav61_entry('?view=project_results', trm('Výsledky projektů'), ['project_results', 'project_result'], $view),
    ]];
    $groups[] = ['key' => 'profile', 'label' => trm('Profil'), 'icon' => 'profile', 'flag' => '', 'items' => [
        nav61_entry('?view=profile', trm('Můj profil'), ['profile'], $view),
        nav61_entry('?view=community', trm('Spolužáci'), ['community'], $view),
        nav61_entry('?view=obchod', trm('Obchod'), ['obchod'], $view),
        nav61_entry('?view=my_intake', trm('Můj dotazník'), ['my_intake'], $view),
        nav61_entry('?view=hlaseni', trm('Nahlásit chybu'), ['hlaseni'], $view),
    ]];
    foreach ($groups as $i => $group) {
        $groups[$i]['active'] = array_filter($group['items'], static fn(array $it): bool => $it['active']) !== [];
    }
    return $groups;
}

/** Odkaz položky podmenu; aktuální = aria-current="page", živá/dnešní položka má textový štítek (ne jen barvu). */
function nav61_link_html(array $item): string
{
    $text = (string)$item['label'];
    $badge = $item['class'] === 'is-today' ? tr('právě běží') : ($item['class'] === 'is-live' ? tr('živě') : '');
    $cls = in_array($item['class'], ['is-muted', 'is-today', 'is-live'], true) ? ' class="' . e($item['class']) . '"' : '';
    return '<a' . $cls . ' href="' . e($item['href']) . '"' . ($item['active'] ? ' aria-current="page"' : '') . '>' . e(tr($text)) . ($badge !== '' ? '<small class="nav61-badge">' . e($badge) . '</small>' : '') . '</a>';
}

/** Horní lišta (desktop/tablet): skupiny s podmenu. Bez JS se podmenu otevírá přes :hover/:focus-within, s JS přes tlačítko. */
function nav61_main_html(?string $classId, string $view): string
{
    $html = '';
    foreach (nav61_groups($classId, $view) as $group) {
        $id = 'nav61-panel-' . $group['key'];
        $groupLabel = (string)$group['label'];
        $links = '';
        foreach ($group['items'] as $item) $links .= '<li>' . nav61_link_html($item) . '</li>';
        $flag = $group['flag'] !== '' ? '<span class="nav61-dot" aria-hidden="true"></span><span class="nav61-sr">' . e($group['flag'] === 'today' ? tr('právě běží') : tr('živě')) . '</span>' : '';
        $html .= '<li class="nav61-group' . ($group['active'] ? ' is-active' : '') . '">'
            . '<button type="button" class="nav61-trigger" aria-expanded="false" aria-controls="' . e($id) . '"' . ($group['active'] ? ' aria-current="true"' : '') . '>'
            . nav61_icon($group['icon']) . '<span class="nav61-label">' . e(tr($groupLabel)) . '</span>' . $flag . '<i class="nav61-chev" aria-hidden="true"></i></button>'
            . '<ul class="nav61-panel" id="' . e($id) . '">' . $links . '</ul></li>';
    }
    return '<nav class="nav61-main" aria-label="' . e(tr('Hlavní navigace')) . '"><ul class="nav61-list">' . $html . '</ul></nav>';
}

/**
 * Seznam skupin pro mobilní menu účtu: jen na úzkém displeji, kde je spodní lišta; cíle, které už ve spodní liště jsou,
 * se neopakují (menší HTML). Bez spodní lišty (test, terminál labu) se nevykresluje – tam zůstává horní lišta s ikonami.
 */
function nav61_drawer_html(?string $classId, string $view): string
{
    if (nav61_bottom_hidden($view)) return '';
    $inBar = array_column(nav61_bottom_items($classId, $view), 'href');
    $html = '';
    foreach (nav61_groups($classId, $view) as $group) {
        $groupLabel = (string)$group['label'];
        $links = '';
        foreach ($group['items'] as $item) {
            if (!in_array($item['href'], $inBar, true)) $links .= nav61_link_html($item);
        }
        if ($links !== '') $html .= '<span class="v56-menu-label">' . e(tr($groupLabel)) . '</span>' . $links;
    }
    return '<div class="nav61-drawer">' . $html . '</div>';
}

/** Odkaz „Přeskočit na obsah“ (první zaměřitelný prvek stránky; cílem je <main id="main-content">). */
function nav61_skip_html(): string
{
    return '<a class="nav61-skip" href="#main-content">' . e(tr('Přeskočit na obsah')) . '</a>';
}
