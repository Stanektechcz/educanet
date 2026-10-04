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
            . ($item['active'] ? ' aria-current="page"' : '') . '><i aria-hidden="true">' . e($item['mark']) . '</i><span>' . e(tr($label)) . '</span></a></li>';
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
