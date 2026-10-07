<?php

declare(strict_types=1);

/**
 * EDUCANET v68 · navigace cockpitu učitele a administrátora (logika bez výstupu, čisté funkce).
 *
 * Šest sekcí: Dnes / Třída a žáci / Výuka / Hodnocení / Hry a motivace / Správa. Každá záložka je v mapě právě jednou
 * (teacher68_tab_map). Menu ukazuje jen záložky, které role smí (teacher68_visible_tabs); prázdná sekce se nevykreslí.
 * Staré záložky (control, class_overview, growth, skills, mastery, filters, automations) z menu zmizely a vrací 302
 * na novou sekci (teacher68_redirect_url). Oprávnění POST akcí a politiky teacher59 se tím nemění – menu je jen pohled.
 * Vykreslení je v teacher_shell_v68.php. Přepínač vzhledu: POST teacher68_theme_set → ui67_theme_set (jen cookie edu_theme).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/ui_v67.php';

/** Záložky cockpitu s tmavým režimem (po převodu CSS na tokeny a kontrole kontrastu; ostatní zůstanou světlé). */
const UI68_TEACHER_DARK_TABS = [
    'attention', 'hodina', 'prehled', 'hlaseni', 'student360', 'class_results', 'analytics', 'interventions', 'communications', 'intake', 'pristupy', 'groups',
    'cesty', 'curriculum', 'teach', 'session', 'calendar', 'authoring', 'editor', 'labdata',
    'hodnoceni66', 'kompetence', 'projekty65', 'grade', 'projekty', 'workspace', 'history',
    'arena', 'roboti', 'hry', 'ctf', 'incidenty', 'souboje', 'ekonomika', 'obchod',
    'sprava_prehled', 'ucitele', 'identita', 'provoz', 'quality', 'ops_audit', 'team_admin', 'demo_accounts', 'overview', 'reports', 'ucet', 'sekce',
];

/** @return array<string, array{label:string, hint:string}> pořadí = pořadí v menu */
function teacher68_sections(): array
{
    return [
        'dnes' => ['label' => 'Dnes', 'hint' => 'Dnešní hodina, co vyžaduje pozornost a co čeká na schválení'],
        'trida' => ['label' => 'Třída a žáci', 'hint' => 'Výsledky, detail žáka, podpora a přístupy'],
        'vyuka' => ['label' => 'Výuka', 'hint' => 'Cesty, plán, hodina a obsah'],
        'hodnoceni' => ['label' => 'Hodnocení', 'hint' => 'Kompetence, projekty a známky'],
        'hry' => ['label' => 'Hry a motivace', 'hint' => 'Aréna, týmové hry, Lab a obchod'],
        'sprava' => ['label' => 'Správa', 'hint' => 'Účty, provoz, přehledy školy a audit'],
    ];
}

/**
 * Každá záložka cockpitu: sekce, popisek, nápověda, zda nese ?class=, oprávnění (teacher_permission), admin = jen administrátor.
 * @return array<string, array{section:string, label:string, hint:string, class:bool, perm:string, admin:bool}>
 */
function teacher68_tab_map(): array
{
    $t = static fn(string $section, string $label, string $hint, bool $class = true, string $perm = 'view', bool $admin = false): array
        => ['section' => $section, 'label' => $label, 'hint' => $hint, 'class' => $class, 'perm' => $perm, 'admin' => $admin];
    return [
        'attention' => $t('dnes', 'Co řešit dnes', 'Upozornění, úkoly a nedávné zásahy'),
        'hodina' => $t('dnes', 'Dnešní hodina', 'Téma, materiály, úkoly a postup žáků'),   // v70
        'prehled' => $t('dnes', 'Přehled třídy', 'Kdo zaostává a co čeká na schválení'),
        'hlaseni' => $t('dnes', 'Hlášení', 'Chyby a návrhy od žáků'),
        'student360' => $t('trida', 'Detail žáka', 'Výsledky, dovednosti, úkoly a časová osa'),
        'class_results' => $t('trida', 'Výsledky třídy', 'Známky, testy, laboratoře a mastery'),
        'analytics' => $t('trida', 'Analytika třídy', 'Trendy, heatmapy a analýza lekcí'),
        'interventions' => $t('trida', 'Intervence', 'Plány podpory a jejich výsledek'),
        'communications' => $t('trida', 'Komunikace', 'Šablony a bezpečné koncepty'),
        'intake' => $t('trida', 'Dotazník', 'Seznamovací dotazník a aktivační kódy', false, 'students.manage'),
        'pristupy' => $t('trida', 'Přístupy', 'Jednorázová hesla a kartičky žáků', true, 'students.manage'),
        'groups' => $t('trida', 'Týmy', 'Členství, lobby a role v týmech'),
        'cesty' => $t('vyuka', 'Výukové cesty', 'Přiřazení cest, trychtýř kroků a kalibrace'),
        'curriculum' => $t('vyuka', 'Plán a kurikulum', 'Lekce, checklist a příprava'),
        'teach' => $t('vyuka', 'Režim hodiny', 'Projekce, simulace a živá výuka'),
        'session' => $t('vyuka', 'Kód hodiny', 'Kód pro žáky, příchody a odevzdání', false),
        'calendar' => $t('vyuka', 'Kalendář', 'Školní rok, bloky a výjimky', false),
        'authoring' => $t('vyuka', 'Obsah a otázky', 'Autorské nástroje a mikrotipy', true, 'content.manage'),
        'editor' => $t('vyuka', 'Editor úrovní', 'Vlastní úlohy do Linux Labu', true, 'content.manage'),
        'labdata' => $t('vyuka', 'Analytika Labu', 'Chyby třídy, dohled a export'),
        'hodnoceni66' => $t('hodnoceni', 'Testy a hodnocení', 'Ráno, položková analýza a návrhy hodnocení'),
        'kompetence' => $t('hodnoceni', 'Kompetence', 'Mapa „umím / učím se / zatím ne“'),
        'projekty65' => $t('hodnoceni', 'Cyklus projektů', 'Návrhy, rubriky, peer review a týmy'),
        'grade' => $t('hodnoceni', 'Hodnotit projekty', 'Rubriky, komentáře a publikace'),
        'projekty' => $t('hodnoceni', 'Nabídky projektů', 'Projekty podle levelu a přihlášky'),
        'workspace' => $t('hodnoceni', 'Workspace týmů', 'Průběh, role a retrospektiva'),
        'history' => $t('hodnoceni', 'Historie změn', 'Záznam změn hodnocení'),
        'arena' => $t('hry', 'Aréna', 'Závody třídy, týdenní hádanka a záznam', false),
        'roboti' => $t('hry', 'Robotí liga', 'Zápasy programovatelných robotů'),
        'hry' => $t('hry', 'Týmové hry', 'Štafeta, bingo, Riskuj! a další'),
        'ctf' => $t('hry', 'CTF týden', 'Hledání vlajek ve cvičném systému'),
        'incidenty' => $t('hry', 'Incidenty', 'Opravy serveru na čas a postmortem'),
        'souboje' => $t('hry', 'Souboje', 'Výzvy a souboje žáků'),
        'ekonomika' => $t('hry', 'Ekonomika', 'Strop XP, žebříčky a retrospektivy', true, 'students.manage'),
        'obchod' => $t('hry', 'Obchod', 'Katalog obchodu bodů a nákupy'),
        'sprava_prehled' => $t('sprava', 'Přehled správy', 'Účty, role, provoz a doporučení', false, 'view', true),   // v70
        'ucitele' => $t('sprava', 'Učitelé', 'Účty, třídy a předměty', false, 'view', true),
        'identita' => $t('sprava', 'Identita a nový rok', 'Stabilní identita žáků a přechod roku', false, 'view', true),
        'provoz' => $t('sprava', 'Provoz', 'Zdraví úložiště a zálohy', false, 'view', true),
        'quality' => $t('sprava', 'Kvalita dat', 'Integrita a osiřelé záznamy', false, 'view', true),
        'ops_audit' => $t('sprava', 'Audit operací', 'Zásahy, hromadné akce a změny', false, 'audit.view'),
        'team_admin' => $t('sprava', 'Tým a role', 'Role a oprávnění učitelů', false, 'roles.manage'),
        'demo_accounts' => $t('sprava', 'Demo účty', 'Testovací žáci a přihlášení', false, 'students.manage'),
        'overview' => $t('sprava', 'Přehled školy', 'Všechny třídy, rizika a stav výuky', false),
        'reports' => $t('sprava', 'Reporty', 'Srovnání tříd a exporty', false),
    ];
}

/** Staré záložky, které z menu zmizely: původní → cílová záložka, nebo `sekce:<id>` (rozcestník sekce). */
function teacher68_tab_redirects(): array
{
    return [
        'control' => 'prehled', 'class_overview' => 'prehled', 'growth' => 'cesty', 'skills' => 'kompetence', 'mastery' => 'kompetence',
        'filters' => 'sekce:sprava', 'automations' => 'sekce:sprava',
    ];
}

/**
 * Záložky, které role smí vidět (v pořadí mapy). $v58Available = teacher58_tabs() (moduly v58 jen když existují soubory a role je smí).
 * @param list<string> $v58Available
 * @return list<string>
 */
function teacher68_visible_tabs(string $role, bool $isAdmin, array $v58Available): array
{
    $v58 = array_keys(teacher58_modules());
    $out = [];
    foreach (teacher68_tab_map() as $tab => $def) {
        if (in_array($tab, $v58, true) && !in_array($tab, $v58Available, true)) continue;
        if ($def['admin'] && !$isAdmin) continue;
        if (!teacher_permission_for_role($role, $def['perm'])) continue;
        $out[] = $tab;
    }
    return $out;
}

/** Aktuální role a modulů cockpitu (volá teacher.php). @return list<string> */
function teacher68_visible_tabs_current(): array
{
    $admin = function_exists('teacher59_is_admin') ? teacher59_is_admin() : true;
    return teacher68_visible_tabs(teacher_role(), $admin, teacher58_tabs());
}

/** @return array<string, list<string>> sekce → viditelné záložky (prázdné sekce se vynechají) */
function teacher68_visible_sections(array $visibleTabs): array
{
    $map = teacher68_tab_map();
    $out = [];
    foreach (array_keys(teacher68_sections()) as $section) {
        $tabs = array_values(array_filter($visibleTabs, static fn(string $t): bool => ($map[$t]['section'] ?? '') === $section));
        if ($tabs !== []) $out[$section] = $tabs;
    }
    return $out;
}

/** Sekce záložky (včetně přesměrovaných a rozcestníku); prázdný řetězec pro neznámou. */
function teacher68_section_of(string $tab, string $sectionParam = ''): string
{
    if ($tab === 'sekce') return isset(teacher68_sections()[$sectionParam]) ? $sectionParam : '';
    $redirect = teacher68_tab_redirects()[$tab] ?? null;
    if ($redirect !== null && !str_starts_with($redirect, 'sekce:')) $tab = $redirect;
    return teacher68_tab_map()[$tab]['section'] ?? '';
}

function teacher68_class_param(string $raw): string
{
    return preg_match('/^class_[a-z0-9]{1,8}$/', $raw) === 1 ? $raw : '';
}

/** Relativní URL záložky; ?class= jen u záložek, které třídu používají. */
function teacher68_tab_url(string $tab, string $classId = ''): string
{
    $def = teacher68_tab_map()[$tab] ?? null;
    $url = '?tab=' . rawurlencode($tab);
    if ($def !== null && $def['class'] && teacher68_class_param($classId) !== '') $url .= '&class=' . rawurlencode($classId);
    return $url;
}

/** Cíl 302 pro starou záložku nebo null. Cíl, který role nevidí, vede na „Dnes“; třída se přenáší jen v platném tvaru. */
function teacher68_redirect_url(string $rawTab, array $get, array $visibleTabs): ?string
{
    $target = teacher68_tab_redirects()[$rawTab] ?? null;
    if ($target === null) return null;
    $class = teacher68_class_param(is_string($get['class'] ?? null) ? (string)$get['class'] : '');
    if (str_starts_with($target, 'sekce:')) return 'teacher.php?tab=sekce&sekce=' . rawurlencode(substr($target, 6));
    if (!in_array($target, $visibleTabs, true)) return 'teacher.php?tab=attention';
    return 'teacher.php' . teacher68_tab_url($target, $class);
}

/** Drobečková navigace: [popisek, url|null][] – Cockpit › sekce › záložka (poslední bez odkazu). */
function teacher68_breadcrumb(string $tab, string $classId = '', string $sectionParam = ''): array
{
    $crumbs = [['Cockpit', 'teacher.php']];
    $sections = teacher68_sections();
    $section = teacher68_section_of($tab, $sectionParam);
    if ($tab === 'ucet') return array_merge($crumbs, [['Můj účet', null]]);
    if ($section === '') return $crumbs;
    if ($tab === 'sekce') return array_merge($crumbs, [[$sections[$section]['label'], null]]);
    $def = teacher68_tab_map()[$tab] ?? null;
    $crumbs[] = [$sections[$section]['label'], 'teacher.php?tab=sekce&sekce=' . rawurlencode($section)];
    if ($def !== null) $crumbs[] = [$def['label'], null];
    return $crumbs;
}

/** Kontextové „další kroky“ (jen záložky, které role vidí a které nejsou aktuální). @return list<array{tab:string,label:string,why:string,url:string}> */
function teacher68_next_steps(string $tab, array $visibleTabs, string $classId = ''): array
{
    $rules = [
        'attention' => [['hodnoceni66', 'Ráno: co řešit jako první'], ['prehled', 'Kdo zaostává ve třídě']],
        'hodnoceni66' => [['cesty', 'Přiřadit výukovou cestu'], ['kompetence', 'Zkontrolovat kompetence']],
        'kompetence' => [['projekty65', 'Navázat projektem']],
        'projekty65' => [['hodnoceni66', 'Přejít na návrhy hodnocení']],
        'prehled' => [['hlaseni', 'Vyřídit hlášení od žáků']],
    ];
    $map = teacher68_tab_map();
    $out = [];
    foreach ($rules[$tab] ?? [] as [$next, $why]) {
        if ($next === $tab || !in_array($next, $visibleTabs, true)) continue;
        $out[] = ['tab' => $next, 'label' => $map[$next]['label'], 'why' => $why, 'url' => teacher68_tab_url($next, $classId)];
    }
    return $out;
}

/** Odkazy rozcestníku detailu žáka (jen čtení; třída musí být v rozsahu – ověří GET politika). @return list<array{label:string,url:string}> */
function teacher68_student_hub_links(string $classId, string $studentKey, array $visibleTabs): array
{
    $class = teacher68_class_param($classId);
    if ($class === '' || $studentKey === '' || str_contains($studentKey, "\n")) return [];
    $map = teacher68_tab_map();
    $out = [];
    foreach (['kompetence' => 'Kompetence', 'cesty' => 'Výukové cesty', 'projekty65' => 'Projekty', 'hodnoceni66' => 'Návrhy hodnocení', 'interventions' => 'Intervence', 'labdata' => 'Linux Lab'] as $tab => $label) {
        if (!isset($map[$tab]) || !in_array($tab, $visibleTabs, true)) continue;
        $out[] = ['label' => $label, 'url' => '?tab=' . rawurlencode($tab) . '&class=' . rawurlencode($class) . '&student=' . rawurlencode($studentKey)];
    }
    return $out;
}

/**
 * Přesměrování staré záložky (GET) PO ověření přístupu: cizí třída zůstává 403, povolený požadavek dostane 302 na novou sekci.
 * Volá teacher.php po teacher59_guard_get().
 */
function teacher68_redirect_legacy_tab(string $rawTab, array $get): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;
    $target = teacher68_redirect_url($rawTab, $get, teacher68_visible_tabs_current());
    if ($target === null) return;
    header('Location: ' . $target, true, 302);
    exit;
}

/** Bezpečná návratová adresa po přepnutí vzhledu: jen ?tab=[a-z0-9_]+ (+ &class=class_x); jinak „Dnes“. */
function teacher68_return_url(string $raw): string
{
    return preg_match('/^\?tab=[a-z0-9_]{1,40}(&class=class_[a-z0-9]{1,8})?$/', $raw) === 1 ? 'teacher.php' . $raw : 'teacher.php?tab=attention';
}

/** Hodnota data-theme pro záložku: tmavý režim jen u záložek ze seznamu, jinak světlý. */
function teacher68_effective_theme(string $tab, ?string $pref = null): string
{
    return ui67_effective_theme($tab, $pref, UI68_TEACHER_DARK_TABS);
}

/** POST teacher68_theme_set (CSRF už ověřil teacher.php, politika a oprávnění teacher59/teacher_action_permission). */
function teacher68_handle_post(string $action): void
{
    if ($action !== 'teacher68_theme_set') return;
    $theme = is_string($_POST['theme'] ?? null) ? (string)$_POST['theme'] : '';
    if (!ui67_theme_set($theme)) throw new RuntimeException('Neplatná volba vzhledu.');
    $return = teacher68_return_url(is_string($_POST['return'] ?? null) ? (string)$_POST['return'] : '');
    header('Location: ' . $return, true, 303);
    exit;
}
