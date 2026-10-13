<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · rychlé hledání v cockpitu (paleta ⌘K / Ctrl+K) bez jmen žáků v HTML stránky.
 *
 * Dřív (v46) se do KAŽDÉ stránky cockpitu – i tisku a projektoru, v režimu bez účtů dokonce na přihlašovací stránku – vypsal
 * seznam všech žáků (jméno a e-mail v atributu data-search). Od v74:
 *   - stránka obsahuje jen prázdný dialog palety (žádné jméno ani e-mail, žádné položky – stránky se nezpomalí),
 *   - záložky, třídy i jména se načtou až při otevření palety z JSON teacher.php?tab=tyden74&paleta=1[&class=…] (jen přihlášený učitel;
 *     politika GET tyden74|paleta, data omezená na třídy v rozsahu teacher59; bez e-mailů; odpověď no-store),
 *   - při skrytých jménech (tp721_names_hidden, Nastavení výuky) koncový bod žádná jména nevrací,
 *   - na projektoru, tisku a listu pro zástup se paleta ani tlačítko ⌘K nevykreslí a nic se nenačítá.
 */

const PAL74_EXCLUDED_TABS = ['hodina_projektor', 'hodina_tisk', 'hodina_zastup'];
const PAL74_MAX_STUDENTS = 2000;
const PAL74_SOURCE = 'teacher.php?tab=tyden74&paleta=1';

function pal74_enabled(string $tab): bool
{
    return !in_array($tab, PAL74_EXCLUDED_TABS, true);
}

/** Hledací klíč bez diakritiky, malými písmeny (stejně jako normalizace v JS). */
function pal74_search_key(string $text): string
{
    static $tr = false;
    if ($tr === false) $tr = class_exists('Transliterator') ? Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Lower()') : null;
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    $out = $tr instanceof Transliterator ? $tr->transliterate($text) : false;
    return is_string($out) ? $out : mb_strtolower($text);
}

/**
 * Statické položky palety: viditelné záložky cockpitu a Dnešní hodina / Přehled třídy pro třídy v rozsahu. Žádná data žáků.
 * @return list<array{label:string,hint:string,url:string}>
 */
function pal74_static_entries(string $classId): array
{
    if (!function_exists('teacher68_tab_map') || !function_exists('teacher68_visible_tabs_current')) return [];
    $map = teacher68_tab_map();
    $sections = function_exists('nav781_sections') ? nav781_sections() : [];   // v78.1: nápověda = sekce menu (Dnes · Výuka · Žáci · Správa)
    $visible = function_exists('c70_visible_tabs') ? c70_visible_tabs() : teacher68_visible_tabs_current();   // c70 drží výsledek na požadavek
    $out = [];
    foreach ($visible as $tab) {
        $def = $map[$tab] ?? null;
        if ($def === null) continue;
        $out[] = ['label' => $def['label'], 'hint' => ($sections[function_exists('nav781_section_of_group') ? nav781_section_of_group($def['section']) : '']['label'] ?? '') . ' · ' . $def['hint'], 'url' => 'teacher.php' . teacher68_tab_url($tab, $classId)];
    }
    $classes = function_exists('c70_classes') ? c70_classes() : [];
    $names = $GLOBALS['modules'] ?? [];
    foreach ($classes as $c) {
        $name = (string)($names[$c]['name'] ?? $c);
        foreach (['hodina' => 'Dnešní hodina', 'prehled' => 'Přehled třídy'] as $tab => $label) {
            if (in_array($tab, $visible, true)) $out[] = ['label' => $label . ' · ' . $name, 'hint' => 'Třída', 'url' => 'teacher.php' . teacher68_tab_url($tab, $c)];
        }
    }
    return $out;
}

/** Dialog palety bez jmen žáků; '' na projektoru, tisku a zástupu. */
function pal74_render_html(string $tab, string $classId): string
{
    if (!pal74_enabled($tab)) return '';
    $src = PAL74_SOURCE . (function_exists('teacher68_class_param') && teacher68_class_param($classId) !== '' ? '&class=' . rawurlencode($classId) : '');
    $html = '<div class="ops-command-backdrop" data-command-backdrop data-palette-src="' . e($src) . '" hidden><div class="ops-command" role="dialog" aria-modal="true" aria-label="Rychlé hledání">'
        . '<header><span aria-hidden="true">⌘K</span><input type="search" placeholder="Stránka, třída nebo žák…" aria-label="Hledat stránku, třídu nebo žáka" data-command-input autocomplete="off">'
        . '<button type="button" data-command-close>Esc</button></header><p class="pal74-status" data-palette-status role="status" aria-live="polite"></p><div class="ops-command-results" data-command-results>';
    return $html . '</div><footer><span>↑↓ pohyb</span><span>Enter otevřít</span><span>Esc zavřít</span></footer></div></div>';
}

/**
 * Žáci pro paletu (jen přihlášený učitel; třídy v rozsahu). Při skrytých jménech nebo bez přístupu k detailu žáka prázdný seznam.
 * @return array{ok:bool,names_hidden:bool,items:list<array{label:string,hint:string,url:string,search:string}>}
 */
function pal74_students(): array
{
    if (!function_exists('tp721_names_hidden') && is_file(__DIR__ . '/teacher_prefs_v72_1.php')) require_once __DIR__ . '/teacher_prefs_v72_1.php';
    if (function_exists('tp721_names_hidden') && tp721_names_hidden()) return ['ok' => true, 'names_hidden' => true, 'items' => []];
    if (function_exists('c70_tab_visible') && !c70_tab_visible('student360')) return ['ok' => true, 'names_hidden' => false, 'items' => []];
    $names = $GLOBALS['modules'] ?? [];
    $items = [];
    foreach (function_exists('c70_classes') ? c70_classes() : [] as $c) {
        if (function_exists('teacher59_can_class') && !teacher59_can_class($c)) continue;
        $className = (string)($names[$c]['name'] ?? $c);
        foreach (project_students_for_class($c) as $key => $student) {
            $label = trim((string)($student['label'] ?? ''));
            if ($label === '') continue;
            $items[] = ['label' => $label, 'hint' => $className . ' · Detail žáka', 'url' => 'teacher.php?' . http_build_query(['tab' => 'student360', 'class' => $c, 'student' => (string)$key]),
                'search' => pal74_search_key($label . ' ' . $className)];
            if (count($items) >= PAL74_MAX_STUDENTS) break 2;
        }
    }
    return ['ok' => true, 'names_hidden' => false, 'items' => $items];
}

/** GET tyden74&paleta=1 – JSON (no-store). Volá teacher58_handle_get po ověření přihlášení a politiky GET. */
function pal74_json(): void
{
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
    }
    try {
        $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
        $class = function_exists('c70_classes') && in_array($class, c70_classes(), true) ? $class : '';
        $pages = array_map(static fn(array $p): array => $p + ['search' => pal74_search_key($p['label'] . ' ' . $p['hint'])], pal74_static_entries($class));
        $data = ['pages' => $pages] + pal74_students();
    } catch (Throwable $e) {
        error_log('EDUCANET v74 paleta: ' . get_class($e));
        $data = ['ok' => false, 'names_hidden' => false, 'pages' => [], 'items' => []];
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
}
