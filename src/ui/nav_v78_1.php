<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v78.1 · informační architektura cockpitu: 4 hlavní sekce (Dnes · Výuka · Žáci · Správa), jednotné odznaky,
 * drobečky se „Zpět“ a model počtu klepnutí (logika bez výstupu, čisté funkce; vykreslení je v nav_v78_1_views.php).
 *
 * Šest skupin z v68 (teacher68_sections: Dnes, Třída a žáci, Výuka, Hodnocení, Hry a motivace, Správa) zůstává jako SKUPINY
 * uvnitř sekcí – mapa záložek, oprávnění, politiky teacher59 i URL záložek se nemění, menu je jen pohled.
 * Staré odkazy na rozcestník (?tab=sekce&sekce=trida|hodnoceni|hry) dál fungují – vedou na sekci, do které skupina patří.
 * Žádná nová POST akce ani GET parametr; odznaky jsou jen čísla (bez jmen žáků), cache v relaci nejvýš 5 minut.
 */

const NAV781_BADGE_TTL = 300;          // s – stejně jako odznak T-14 (v77)
const NAV781_UNAPPROVED_DAYS = 28;     // neschválené lekce s výukou do 4 týdnů (stejný horizont jako Středa T-14)
/** Pořadí odznaků u sekce (sekce ukáže první nenulový): T-14 > neschválené > komu pomoct > nepřečtené. */
const NAV781_BADGE_PRIORITY = ['streda77', 'schvalovani', 'hodina', 'attention'];
/** Záložky bez rámce navigace pro dotyk (projektor, tisk, zástup) – bez spodní lišty a bez vyhledávání. */
const NAV781_BARE_TABS = ['hodina_projektor', 'hodina_tisk', 'hodina_zastup'];

/** @return array<string, array{label:string, hint:string, icon:string, groups:list<string>}> pořadí = pořadí v menu */
function nav781_sections(): array
{
    return [
        'dnes' => ['label' => 'Dnes', 'hint' => 'Dnešní hodina, co řešit, Středa T-14 a týdenní souhrn', 'icon' => 'dnes', 'groups' => ['dnes']],
        'vyuka' => ['label' => 'Výuka', 'hint' => 'Plán, obsah, schvalování a hodnocení', 'icon' => 'vyuka', 'groups' => ['vyuka', 'hodnoceni']],
        'zaci' => ['label' => 'Žáci', 'hint' => 'Výsledky, detail žáka, podpora, přístupy, hry a motivace', 'icon' => 'zaci', 'groups' => ['trida', 'hry']],
        'sprava' => ['label' => 'Správa', 'hint' => 'Účty, provoz, přehledy školy a audit', 'icon' => 'sprava', 'groups' => ['sprava']],
    ];
}

/** Popisek skupiny uvnitř sekce (u sekce s jedinou skupinou se nadpis skupiny nevykresluje). */
function nav781_group_label(string $group): string
{
    return ['dnes' => 'Dnes', 'vyuka' => 'Plán, obsah a hodina', 'hodnoceni' => 'Hodnocení', 'trida' => 'Třída a žáci', 'hry' => 'Hry a motivace', 'sprava' => 'Správa'][$group] ?? $group;
}

/** Sekce v78.1 pro skupinu v68 ('' = neznámá). */
function nav781_section_of_group(string $group): string
{
    foreach (nav781_sections() as $id => $s) if (in_array($group, $s['groups'], true)) return $id;
    return '';
}

/** Hodnota ?sekce= → sekce v78.1: nové id, nebo staré id skupiny v68 (trida → zaci, hodnoceni → vyuka, hry → zaci). */
function nav781_section_param(string $raw): string
{
    if (isset(nav781_sections()[$raw])) return $raw;
    return isset(teacher68_sections()[$raw]) ? nav781_section_of_group($raw) : '';
}

/** Sekce aktuální stránky ('' = účet nebo neznámá záložka). */
function nav781_section_of(string $tab, string $sectionParam = ''): string
{
    if ($tab === 'sekce') return nav781_section_param($sectionParam);
    $group = teacher68_section_of($tab, '');
    return $group === '' ? '' : nav781_section_of_group($group);
}

/**
 * Strom menu pro roli: sekce → skupina → viditelné záložky (prázdné skupiny a sekce se vynechají, pořadí = mapa v68).
 * @param list<string> $visibleTabs
 * @return array<string, array<string, list<string>>>
 */
function nav781_tree(array $visibleTabs): array
{
    $groups = teacher68_visible_sections($visibleTabs);
    $out = [];
    foreach (nav781_sections() as $id => $s) {
        foreach ($s['groups'] as $g) if (isset($groups[$g])) $out[$id][$g] = $groups[$g];
    }
    return $out;
}

/** Rozcestník sekce (relativně k teacher.php); třída se přenáší jen v platném tvaru. */
function nav781_section_url(string $section, string $classId = ''): string
{
    $url = 'teacher.php?tab=sekce&sekce=' . rawurlencode($section);
    return teacher68_class_param($classId) !== '' ? $url . '&class=' . rawurlencode($classId) : $url;
}

/**
 * Drobečky: Cockpit › sekce › záložka (poslední bez odkazu). Odkaz na sekci nese třídu, aby „o úroveň výš“ zachovalo kontext.
 * @return list<array{0:string,1:?string}>
 */
function nav781_breadcrumb(string $tab, string $classId = '', string $sectionParam = ''): array
{
    $crumbs = [['Cockpit', 'teacher.php']];
    if ($tab === 'ucet') return array_merge($crumbs, [['Můj účet', null]]);
    $section = nav781_section_of($tab, $sectionParam);
    if ($section === '') return $crumbs;
    $label = nav781_sections()[$section]['label'];
    if ($tab === 'sekce') return array_merge($crumbs, [[$label, null]]);
    $crumbs[] = [$label, nav781_section_url($section, $classId)];
    $def = teacher68_tab_map()[$tab] ?? null;
    if ($def !== null) $crumbs[] = [$def['label'], null];
    return $crumbs;
}

/** Záložní cíl „Zpět“ bez JS (nadřazená úroveň s třídou). JS ho nahradí předchozí stránkou cockpitu (filtr, týden, posun). */
function nav781_back_url(string $tab, string $classId = '', string $sectionParam = ''): string
{
    $crumbs = nav781_breadcrumb($tab, $classId, $sectionParam);
    for ($i = count($crumbs) - 2; $i >= 0; $i--) if ($crumbs[$i][1] !== null) return (string)$crumbs[$i][1];
    return 'teacher.php';
}

// ---------------------------------------------------------------- odznaky

/** „1 lekce / 2 lekce / 5 lekcí“. */
function nav781_plural(int $n, string $one, string $few, string $many): string
{
    return $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
}

/** Uloží počet žáků z karty „Komu dnes pomoct“ (jen číslo a třída, žádná jména). Volá in74_card_html. */
function nav781_help_store(string $classId, int $count): void
{
    if (session_status() !== PHP_SESSION_ACTIVE || teacher68_class_param($classId) === '') return;
    $_SESSION['nav781_help'] = ['class' => $classId, 'n' => max(0, $count), 't' => time()];
}

/** Počet z poslední otevřené Dnešní hodiny (platí 5 minut a jen pro stejnou třídu, nebo když stránka třídu neurčuje). @return array{n:int,class:string} */
function nav781_help_badge(string $classId, bool $classExplicit): array
{
    $c = $_SESSION['nav781_help'] ?? null;
    if (!is_array($c) || !is_int($c['n'] ?? null) || !is_int($c['t'] ?? null) || time() - $c['t'] > NAV781_BADGE_TTL) return ['n' => 0, 'class' => ''];
    $cls = (string)($c['class'] ?? '');
    if ($classExplicit && $cls !== $classId) return ['n' => 0, 'class' => ''];
    return ['n' => $c['n'], 'class' => $cls];
}

/**
 * Neschválené lekce (návrh obsahu existuje, schválení chybí nebo neodpovídá obsahu) s výukou od dneška do 28 dní ve třídách učitele.
 * Stejná lehká cesta jako odznak T-14 (overlay jen ze souborů třídy, jedno čtení záznamů); cache v relaci do změny schválení / 5 min.
 */
function nav781_unapproved_count(?string $today = null, ?array $classes = null): int
{
    $today ??= date('Y-m-d');
    if ($classes === null) $classes = function_exists('c70_classes') ? c70_classes() : [];
    $approvals = defined('STORAGE_DIR') ? STORAGE_DIR . '/lesson_approvals_v72.json.php' : '';
    $sig = $today . '|' . implode(',', $classes) . '|' . ($approvals !== '' && is_file($approvals) ? filemtime($approvals) . ':' . filesize($approvals) : '-') . '|' . intdiv(time(), NAV781_BADGE_TTL);
    $cached = $_SESSION['nav781_unapproved'] ?? null;
    if (session_status() === PHP_SESSION_ACTIVE && is_array($cached) && ($cached['sig'] ?? '') === $sig && is_int($cached['n'] ?? null)) return $cached['n'];
    if (!function_exists('lc72_overlay_light')) {
        if (!is_file(dirname(__DIR__, 2) . '/src/lessons/lesson_approval_v72.php')) return 0;
        require_once dirname(__DIR__, 2) . '/src/lessons/lesson_approval_v72.php';
    }
    $records = lc72_records();
    $end = date('Y-m-d', (int)strtotime($today . ' +' . NAV781_UNAPPROVED_DAYS . ' days'));
    $count = 0;
    foreach ($classes as $c) {
        foreach (lc72_lesson_dates($c) as $n => $date) {
            if ($date === '' || $date < $today || $date > $end) continue;
            $ov = lc72_overlay_light($c, (int)$n);
            if ($ov === []) continue;
            $rec = $records[$c][(string)$n] ?? null;
            $approved = is_array($rec) && (string)($rec['status'] ?? '') === 'schvaleno' && hash_equals((string)($rec['hash'] ?? ''), lc72_overlay_hash($ov));
            if (!$approved) $count++;
        }
    }
    if (session_status() === PHP_SESSION_ACTIVE) $_SESSION['nav781_unapproved'] = ['sig' => $sig, 'n' => $count];
    return $count;
}

/**
 * Jednotné odznaky všech navigací (boční panel, drawer, spodní lišta, rozcestník): záložka → [počet, popisek pro čtečku].
 * Chyba výpočtu odznaku menu nerozbije (odznak se jen nevykreslí). Výsledek drží požadavek.
 * @param list<string> $visibleTabs
 * @return array<string, array{n:int, label:string}>
 */
function nav781_badges(array $visibleTabs, string $classId = '', bool $classExplicit = false): array
{
    static $memo = [];
    $key = implode(',', $visibleTabs) . '|' . $classId . '|' . ($classExplicit ? '1' : '0');
    if (isset($memo[$key])) return $memo[$key];
    $out = [];
    $add = static function (string $tab, callable $count, callable $label) use (&$out, $visibleTabs): void {
        if (!in_array($tab, $visibleTabs, true)) return;
        try {
            $n = (int)$count();
        } catch (Throwable $e) {
            error_log('EDUCANET v78.1 odznak ' . $tab . ': ' . get_class($e));
            return;
        }
        if ($n > 0) $out[$tab] = ['n' => $n, 'label' => $label($n)];
    };
    $add('streda77', static fn(): int => function_exists('teacher77_t14_badge_count') ? teacher77_t14_badge_count($visibleTabs) : 0,
        static fn(int $n): string => $n . ' ' . nav781_plural($n, 'lekce', 'lekce', 'lekcí') . ' po termínu T-14');
    $add('schvalovani', static fn(): int => nav781_unapproved_count(),
        static fn(int $n): string => $n . ' ' . nav781_plural($n, 'neschválená lekce', 'neschválené lekce', 'neschválených lekcí') . ' s výukou do 4 týdnů');
    $help = nav781_help_badge($classId, $classExplicit);
    $add('hodina', static fn(): int => $help['n'],
        static fn(int $n): string => 'Komu dnes pomoct: ' . $n . ' ' . nav781_plural($n, 'žák', 'žáci', 'žáků') . ($help['class'] !== '' && function_exists('teacher_class_label') ? ' (' . teacher_class_label($help['class']) . ')' : ''));
    $add('attention', static fn(): int => function_exists('teacher_ops_notifications_for_current_teacher') ? count(teacher_ops_notifications_for_current_teacher(true)) : 0,
        static fn(int $n): string => $n . ' ' . nav781_plural($n, 'nepřečtené upozornění', 'nepřečtená upozornění', 'nepřečtených upozornění'));
    return $memo[$key] = $out;
}

/** Odznak sekce = první nenulový odznak jejích záložek podle NAV781_BADGE_PRIORITY. @return array{n:int,label:string}|null */
function nav781_section_badge(array $sectionTabs, array $badges): ?array
{
    foreach (NAV781_BADGE_PRIORITY as $tab) if (in_array($tab, $sectionTabs, true) && isset($badges[$tab])) return $badges[$tab];
    return null;
}

// ---------------------------------------------------------------- počet klepnutí (doklad „≤ 3 klepnutí z úvodu“)

/** Hlavní úlohy učitele: [popis, záložka] (záložka musí být v mapě v68). */
function nav781_main_tasks(): array
{
    return [
        ['Otevřít dnešní hodinu (a „Komu dnes pomoct“)', 'hodina'], ['Pustit hodinu na projektor', 'hodina_projektor'], ['Vytisknout plán hodiny', 'hodina_tisk'],
        ['Zapsat reflexi po hodině', 'reflexe73'], ['Středa: co schválit do 14 dní', 'streda77'], ['Hromadně schválit lekce', 'hromadne74'],
        ['Schválit jednu lekci', 'schvalovani'], ['Najít materiál k lekci', 'materialy74'], ['Plán vs. skutečnost', 'plan76'],
        ['Opakování před písemkou', 'opakovani75'], ['Testy a návrhy hodnocení', 'hodnoceni66'], ['Hodnotit projekty', 'grade'],
        ['Výsledky třídy', 'class_results'], ['Detail žáka', 'student360'], ['Jednorázová hesla a kartičky', 'pristupy'],
        ['Vyřídit hlášení žáků', 'hlaseni'], ['Spustit závod v Aréně', 'arena'], ['Účty učitelů (admin)', 'ucitele'], ['Zdraví úložiště a zálohy (admin)', 'provoz'],
    ];
}

/**
 * Počet klepnutí z úvodu (teacher.php = „Co řešit dnes“, sekce Dnes je otevřená) podle rozvržení:
 *   lg/xl: boční panel – záložka v otevřené sekci 1, jinak rozbalit sekci + záložka 2,
 *   md: Menu (drawer) + případné rozbalení sekce + záložka,
 *   xs/sm: ikona sekce ve spodní liště → karta v rozcestníku = 2 (stejně přes „Více“: Více + sekce + záložka ≤ 3).
 * @return array{lg:int, md:int, xs:int}|null null = záložku role nevidí
 */
function nav781_clicks(string $tab, array $visibleTabs, string $homeSection = 'dnes'): ?array
{
    if (!in_array($tab, $visibleTabs, true)) return null;
    $section = nav781_section_of($tab);
    if ($section === '') return null;
    $open = $section === $homeSection ? 0 : 1;
    return ['lg' => $open + 1, 'md' => 1 + $open + 1, 'xs' => 2];
}
