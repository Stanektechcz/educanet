<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v70 · společné prvky přehledného cockpitu (jen čtení, žádné POST akce ani nové GET parametry).
 *   - c70_kpis_html / c70_empty_html: jednotný pruh ukazatelů a prázdný stav s dalším krokem,
 *   - c70_related_links: „Související“ – křížové odkazy mezi sekcemi u každé záložky (jen záložky, které role vidí),
 *   - c70_hub_html: souhrn nad kartami rozcestníku sekce (dnešní výuka tříd, počty žáků, aktuální lekce, stav správy).
 * Rozsah tříd: jen třídy z teacher59_allowed_class_ids() (v režimu bez účtů všechny). Texty česky.
 */

/** Záložky, které aktuální role vidí (cache na požadavek). */
function c70_visible_tabs(): array
{
    static $tabs = null;
    if ($tabs === null) $tabs = function_exists('teacher68_visible_tabs_current') ? teacher68_visible_tabs_current() : [];
    return $tabs;
}

function c70_tab_visible(string $tab): bool
{
    return in_array($tab, c70_visible_tabs(), true);
}

/** @param list<array{0:string,1:string,2:string,3:string}> $items [popisek, hodnota, nápověda, tón ok|warn|''] */
function c70_kpis_html(array $items, string $label): string
{
    $html = '<section class="c70-kpis" aria-label="' . e($label) . '">';
    foreach ($items as [$name, $value, $hint, $tone]) {
        $html .= '<article class="c70-kpi' . ($tone !== '' ? ' is-' . e($tone) : '') . '"><span>' . e($name) . '</span><strong>' . e($value) . '</strong><small>' . e($hint) . '</small></article>';
    }
    return $html . '</section>';
}

/** Prázdný stav: co chybí, proč a kam dál. @param list<array{0:string,1:string}> $next [záložka, popisek] */
function c70_empty_html(string $title, string $text, array $next, string $classId = ''): string
{
    $links = [];
    foreach ($next as [$tab, $label]) {
        if (!c70_tab_visible($tab)) continue;
        $url = function_exists('teacher68_tab_url') ? teacher68_tab_url($tab, $classId) : '?tab=' . rawurlencode($tab);
        $links[] = '<a class="btn secondary" href="' . e($url) . '">' . e($label) . '</a>';
    }
    return '<section class="c70-empty" role="status"><h2>' . e($title) . '</h2><p>' . e($text) . '</p>' . ($links !== [] ? '<p class="c70-actions">' . implode('', $links) . '</p>' : '') . '</section>';
}

/** Třídy v rozsahu učitele. @return list<string> */
function c70_classes(): array
{
    $all = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
    if (!function_exists('teacher59_allowed_class_ids')) return $all;
    return array_values(array_intersect($all, teacher59_allowed_class_ids()));
}

/** Křížové odkazy: záložka → [cílová záložka, proč]. Doplňují „Další krok“ v68 (ten se tu neopakuje). */
function c70_related_rules(): array
{
    return [
        'attention' => [['hodina', 'Dnešní hodina: téma, materiály a žáci'], ['prehled', 'Kdo zaostává ve třídě']],
        'hodina' => [['prehled', 'Přehled třídy a čekající schválení'], ['hodnoceni66', 'Ráno: co řešit jako první'], ['curriculum', 'Příprava dalších lekcí']],
        'prehled' => [['hodina', 'Dnešní hodina třídy'], ['student360', 'Detail žáka'], ['interventions', 'Naplánovat podporu']],
        'hlaseni' => [['prehled', 'Přehled třídy']],
        'student360' => [['interventions', 'Plán podpory'], ['communications', 'Napsat rodičům nebo žákovi'], ['hodina', 'Dnešní hodina třídy']],
        'class_results' => [['analytics', 'Trendy a heatmapa'], ['hodnoceni66', 'Návrhy hodnocení'], ['student360', 'Detail žáka']],
        'analytics' => [['interventions', 'Naplánovat podporu'], ['labdata', 'Chyby v Linux Labu']],
        'interventions' => [['student360', 'Detail žáka'], ['communications', 'Komunikace']],
        'communications' => [['student360', 'Detail žáka']],
        'intake' => [['pristupy', 'Přístupy a kartičky'], ['hodina', 'Dnešní hodina']],
        'pristupy' => [['intake', 'Seznamovací dotazník'], ['session', 'Kód hodiny']],
        'groups' => [['workspace', 'Workspace týmů'], ['projekty65', 'Cyklus projektů']],
        'cesty' => [['kompetence', 'Mapa kompetencí'], ['hodina', 'Dnešní hodina']],
        'curriculum' => [['hodina', 'Dnešní hodina'], ['teach', 'Promítnout lekci'], ['calendar', 'Kalendář a výjimky']],
        'teach' => [['hodina', 'Dnešní hodina: žáci a postup'], ['session', 'Kód hodiny']],
        'session' => [['hodina', 'Dnešní hodina: téma a materiály'], ['teach', 'Promítnout lekci']],
        'calendar' => [['curriculum', 'Plán lekcí'], ['hodina', 'Dnešní hodina']],
        'authoring' => [['curriculum', 'Plán a kurikulum']],
        'editor' => [['labdata', 'Analytika Labu']],
        'labdata' => [['kompetence', 'Mapa kompetencí'], ['hodina', 'Dnešní hodina']],
        'hodnoceni66' => [['class_results', 'Výsledky třídy']],
        'kompetence' => [['cesty', 'Výukové cesty'], ['hodina', 'Dnešní hodina']],
        'projekty65' => [['grade', 'Hodnotit projekty'], ['workspace', 'Workspace týmů']],
        'grade' => [['history', 'Historie změn'], ['projekty65', 'Cyklus projektů']],
        'projekty' => [['projekty65', 'Cyklus projektů']],
        'workspace' => [['grade', 'Hodnotit projekty']],
        'history' => [['grade', 'Hodnotit projekty']],
        'arena' => [['ekonomika', 'Strop XP a žebříčky']], 'roboti' => [['ekonomika', 'Ekonomika her']], 'hry' => [['ekonomika', 'Ekonomika her']],
        'ctf' => [['labdata', 'Analytika Labu']], 'incidenty' => [['labdata', 'Analytika Labu']], 'souboje' => [['arena', 'Aréna']],
        'ekonomika' => [['obchod', 'Obchod bodů']], 'obchod' => [['ekonomika', 'Ekonomika her']],
        'sprava_prehled' => [['ucitele', 'Učitelské účty'], ['provoz', 'Provoz a zálohy'], ['quality', 'Kvalita dat']],
        'ucitele' => [['sprava_prehled', 'Přehled správy'], ['team_admin', 'Tým a role']],
        'identita' => [['sprava_prehled', 'Přehled správy'], ['pristupy', 'Přístupy žáků']],
        'provoz' => [['sprava_prehled', 'Přehled správy'], ['quality', 'Kvalita dat']],
        'quality' => [['sprava_prehled', 'Přehled správy'], ['provoz', 'Provoz']],
        'ops_audit' => [['sprava_prehled', 'Přehled správy']], 'team_admin' => [['ucitele', 'Učitelské účty']], 'demo_accounts' => [['pristupy', 'Přístupy žáků']],
        'overview' => [['hodina', 'Dnešní hodina'], ['reports', 'Reporty a export']], 'reports' => [['overview', 'Přehled školy']],
    ];
}

/** @return list<array{tab:string,label:string,section:string,why:string,url:string}> */
function c70_related_links(string $tab, array $visibleTabs, string $classId = ''): array
{
    if (!function_exists('teacher68_tab_map')) return [];
    $map = teacher68_tab_map();
    $sections = teacher68_sections();
    $skip = function_exists('teacher68_next_steps') ? array_column(teacher68_next_steps($tab, $visibleTabs, $classId), 'tab') : [];
    $out = [];
    foreach (c70_related_rules()[$tab] ?? [] as [$target, $why]) {
        if ($target === $tab || in_array($target, $skip, true) || !isset($map[$target]) || !in_array($target, $visibleTabs, true)) continue;
        $out[] = ['tab' => $target, 'label' => $map[$target]['label'], 'section' => $sections[$map[$target]['section']]['label'], 'why' => $why, 'url' => teacher68_tab_url($target, $classId)];
    }
    return $out;
}

function c70_related_html(string $tab, array $visibleTabs, string $classId = ''): string
{
    $links = c70_related_links($tab, $visibleTabs, $classId);
    if ($links === []) return '';
    $html = '<aside class="c70-related" aria-label="Související"><strong>Související</strong><ul>';
    foreach ($links as $l) $html .= '<li><a href="' . e($l['url']) . '"><span>' . e($l['why']) . '</span><small>' . e($l['section'] . ' › ' . $l['label']) . '</small></a></li>';
    return $html . '</ul></aside>';
}

/** Dnešní výuka tříd v rozsahu (pro rozcestník Dnes a Výuka): čas, lekce, stav hodiny. @return list<array<string,mixed>> */
function c70_today_rows(): array
{
    if (!function_exists('lt70_school_year')) require_once __DIR__ . '/lesson_today_v70.php';
    $year = lt70_school_year();
    $today = date('Y-m-d');
    $catalog = $GLOBALS['modules'] ?? [];
    $rows = [];
    foreach (c70_classes() as $classId) {
        $slot = lt70_slot($year, $classId, $today, 0);
        $sch = adaptive_class_schedule($year, $classId);
        $module = is_array($catalog[$classId] ?? null) ? $catalog[$classId] : [];
        $title = '';
        if ($module !== [] && function_exists('v56_lesson_bundle')) {
            try { $title = (string)lt70_lesson($classId, $module, $slot['lesson'])['title']; } catch (Throwable $e) { $title = ''; }
        }
        $session = function_exists('sess53_for_class_date') && $slot['is_today'] ? sess53_for_class_date($classId, $today) : null;
        $rows[] = ['class' => $classId, 'name' => (string)($module['name'] ?? $classId), 'time' => trim((string)$sch['start']) !== '' ? $sch['start'] . '–' . $sch['end'] : '',
            'slot' => $slot, 'title' => $title, 'session' => is_array($session) ? (!empty($session['open']) ? 'kód otevřený' : 'hodina uzavřená') : ($slot['is_today'] ? 'kód neotevřený' : ''),
            'students' => count(project_students_for_class($classId))];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp((string)$a['time'], (string)$b['time']));
    return $rows;
}

/** Souhrn nad kartami rozcestníku sekce (prázdný řetězec = sekce bez souhrnu). */
function c70_hub_html(string $sectionId): string
{
    try {
        if (in_array($sectionId, ['dnes', 'vyuka', 'trida'], true)) return c70_hub_classes_html($sectionId);
        if ($sectionId === 'sprava' && c70_tab_visible('sprava_prehled')) {
            if (!function_exists('ad70_overview')) require_once __DIR__ . '/admin_overview_v70.php';
            $o = ad70_overview(false);
            return c70_kpis_html(ad70_kpi_items($o), 'Souhrn správy') . '<p><a class="c70-link" href="?tab=sprava_prehled">Celý přehled správy a doporučení →</a></p>';
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v70 rozcestník: ' . $e->getMessage());
    }
    return '';
}

function c70_hub_classes_html(string $sectionId): string
{
    $rows = c70_today_rows();
    if ($rows === []) return c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Souhrn se ukáže, jakmile vám administrátor přidělí třídu.', []);
    $heading = ['dnes' => 'Dnešní výuka', 'vyuka' => 'Aktuální lekce tříd', 'trida' => 'Třídy v rozsahu'][$sectionId];
    $html = '<section class="c70-card c70-wide" aria-labelledby="c70-hub-h"><h2 id="c70-hub-h">' . e($heading) . '</h2><ul class="c70-hubrows">';
    foreach ($rows as $r) {
        $slot = $r['slot'];
        $when = $slot['status'] === 'today' ? 'dnes ' . $r['time'] : ($slot['status'] === 'next' ? 'další ' . date('j. n.', (int)strtotime((string)$slot['date'])) : 'bez další hodiny');
        $detail = $sectionId === 'trida' ? $r['students'] . ' žáků v seznamu' : 'Lekce ' . (int)$slot['lesson'] . ($r['title'] !== '' ? ' · ' . $r['title'] : '');
        $tab = $sectionId === 'trida' ? 'prehled' : 'hodina';
        $url = c70_tab_visible($tab) ? teacher68_tab_url($tab, $r['class']) : '';
        $inner = '<strong>' . e($r['name']) . '</strong><span>' . e($when) . '</span><span>' . e($detail) . '</span>' . ($r['session'] !== '' && $sectionId !== 'trida' ? '<span class="c70-chip">' . e($r['session']) . '</span>' : '');
        $html .= '<li>' . ($url !== '' ? '<a href="' . e($url) . '">' . $inner . '</a>' : '<div>' . $inner . '</div>') . '</li>';
    }
    return $html . '</ul></section>';
}
