<?php

declare(strict_types=1);

/**
 * v58 · Registr učitelských modulů (integrátor).
 *
 * Každý modul v58 deklaruje záložku, soubory k línému načtení, render, POST prefixy a zvláštní GET
 * (projektor, polling, tisk, export). teacher.php volá jen funkce teacher58_* – nový modul = nový
 * záznam v teacher58_modules(). Modul je dostupný, jen když existují všechny jeho soubory.
 * Oprávnění POST akcí mapuje teacher_action_permission() (teacher_operations_v46.php, deny-by-default).
 */

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** @return array<string, array<string, mixed>> */
function teacher58_modules(): array
{
    static $mods = null;
    if ($mods !== null) return $mods;
    $csrf = static fn(): string => csrf_token();
    return $mods = [
        'roboti' => [
            'label' => 'Robotí liga', 'hint' => 'Programovatelná aréna – zápasy robotů', 'group' => 'hry',
            'files' => ['robots_v58.php', 'robots_v58_views.php'],
            'post' => ['robots58_' => 'robots58_teacher_handle_post'],
            'get' => [
                'robots_poll' => static function (array $m): void { robots58_teacher_poll(); },
                'projector' => static function (array $m): void { robots58_render_projector((string)($_GET['match'] ?? '')); },
            ],
            'render' => static function (array $m, string $c): void { robots58_render_teacher_tab($m, $c); },
        ],
        'hry' => [
            'label' => 'Týmové hry', 'hint' => 'Štafeta, bingo, Riskuj!, přetahovaná, správci, úniková místnost', 'group' => 'hry',
            'files' => ['teamgames_v58_core.php', 'teamgames_v58_teacher_views.php', 'teamgames_v58_projector_views.php'],
            'post' => ['tg58_' => 'tg58_teacher_handle_post'],
            'get' => [
                'tg_poll' => static function (array $m): void { if (function_exists('tg58_teacher_poll')) tg58_teacher_poll(); },
                'projektor' => static function (array $m): void { tg58_render_projector((string)($_GET['projektor'] ?? '')); },
            ],
            'render' => static function (array $m, string $c) use ($csrf): void { tg58_render_teacher_tab($c, $csrf()); },
        ],
        'ctf' => [
            'label' => 'CTF týden', 'hint' => 'Hledání vlajek ve vlastním cvičném systému', 'group' => 'hry',
            'files' => ['arena_v58_ctf.php', 'arena_v58_events_views.php'],
            'css' => ['assets/arena-events-v58.css'], 'js' => ['assets/arena-events-v58.js'],
            'post' => ['arena58_ctf_' => 'arena58_ctf_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { arena58_ctf_render_teacher_tab($c, $csrf()); },
        ],
        'incidenty' => [
            'label' => 'Incidenty', 'hint' => 'Opravy serveru na čas a postmortem', 'group' => 'hry',
            'files' => ['arena_v58_incident.php', 'arena_v58_events_views.php'],
            'css' => ['assets/arena-events-v58.css'], 'js' => ['assets/arena-events-v58.js'],
            'post' => ['arena58_inc_' => 'arena58_inc_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { arena58_inc_render_teacher_tab($c, $csrf()); },
        ],
        'labdata' => [
            'label' => 'Analytika labu', 'hint' => 'Chyby třídy, dohled, přehrávání, export', 'group' => 'hry',
            'files' => ['lab_v58_teacher.php', 'lab_v58_teacher_views.php'],
            'css' => ['assets/lab-teacher-v58.css'], 'js' => ['assets/lab-teacher-v58.js'],
            'post' => ['lab58t_' => 'lab58t_teacher_handle_post'],
            'get' => [
                'export' => static function (array $m): void { lab58t_export_csv((string)($_GET['class'] ?? ''), (string)($_GET['mode'] ?? 'formativni')); },
                'dohled_poll' => static function (array $m): void { if (function_exists('lab58t_teacher_poll')) lab58t_teacher_poll(); },
            ],
            'render' => static function (array $m, string $c) use ($csrf): void { lab58t_render_teacher_tab($c, $csrf()); },
        ],
        'editor' => [
            'label' => 'Editor úrovní', 'hint' => 'Vlastní úlohy do Linux Labu s kontrolou řešitelnosti', 'group' => 'hry',
            'files' => ['lab_v58_editor.php', 'lab_v58_editor_views.php'],
            'post' => ['lab58e_' => 'lab58e_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { lab58e_render_teacher_tab($c, $csrf()); },
        ],
        'identita' => [
            'label' => 'Identita a nový rok', 'hint' => 'student_id, kolize jmen, plán přechodu roku (náhled)', 'group' => 'podpora',
            'files' => ['identity_v58.php', 'identity_v58_rollover.php', 'identity_v58_views.php'], 'css' => ['assets/identity-v58.css'], 'admin' => true,
            'post' => ['identity58_' => 'identity58_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { identity58_render_teacher_tab($csrf()); },
        ],
        'provoz' => [
            'label' => 'Provoz', 'hint' => 'Zdraví úložiště, zálohy, údržba dat (náhled)', 'group' => 'podpora',
            'files' => ['ops_v58.php', 'ops_v58_views.php'], 'css' => ['assets/ops-v58.css'], 'admin' => true,
            'render' => static function (array $m, string $c) use ($csrf): void { ops58_render_health_tab($csrf()); },
        ],
        // v59 · AUTHZ58-07: správa učitelských účtů – jen admin a jen v režimu účtů (POST akce obsluhuje teacher59_handle_post v teacher.php).
        'ucitele' => [
            'label' => 'Učitelé', 'hint' => 'Účty učitelů, třídy, předměty a jednorázová hesla', 'group' => 'podpora',
            'admin' => true, 'accounts_only' => true,
            'files' => ['teacher_accounts_v59_admin.php', 'teacher_accounts_v59_views.php'],
            'css' => ['assets/teacher-accounts-v59.css'], 'js' => ['assets/teacher-accounts-v59.js'],
            'get' => [
                'karticka' => static function (array $m): void { teacher59_render_card_print(); },
            ],
            'render' => static function (array $m, string $c): void { teacher59_render_admin_tab(); },
        ],
        // v60 · obchod bodů: admin vidí a spravuje vše, učitel jen položky ve svých třídách (deny-by-default
        // v teacher59_action_policy()/teacher59_entity_resolve()); asistent má jen 'view' → post neprojde
        // (teacher_require_permission('content.manage') ho zastaví dřív, než sem dorazí).
        'obchod' => [
            'label' => 'Obchod', 'hint' => 'Katalog obchodu bodů, nákupy a vrácení', 'group' => 'podpora',
            'files' => ['marketplace_v60.php', 'marketplace_v60_teacher_views.php', 'points_v53.php', 'points_v60.php'],
            'css' => ['assets/marketplace-v60.css'],
            'post' => ['mkt60_' => 'mkt60_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { mkt60_render_teacher_tab($c, $csrf()); },
        ],
        // v61 · přehled soubojů v Aréně: JEN ČTENÍ (žádná POST akce ani GET parametr registru → nic k autorizaci
        // kromě rozsahu tříd, který si seznam vynucuje sám přes teacher59_allowed_class_ids(); asistent nemá co měnit).
        'souboje' => [
            'label' => 'Souboje', 'hint' => 'Přehled výzev a soubojů žáků, statistiky tříd', 'group' => 'hry',
            'files' => ['arena_v61_duels.php', 'arena_v61_teacher_views.php'],
            'css' => ['assets/motivation-v61.css'],
            'render' => static function (array $m, string $c) use ($csrf): void { arena61_render_teacher_tab($c, $csrf()); },
        ],
        // v60 · projekty podle levelu: nabídky skutečné práce od klientů mimo systém. Admin vidí a
        // spravuje vše, učitel jen projekty a přihlášky ve svých třídách (deny-by-default v
        // teacher59_action_policy()/teacher59_entity_resolve()); asistent má jen 'view' → post neprojde.
        'projekty' => [
            'label' => 'Projekty', 'hint' => 'Nabídky projektů podle levelu a přihlášky žáků', 'group' => 'podpora',
            'files' => ['projects_v60.php', 'projects_v60_teacher_views.php'],
            'css' => ['assets/projects-v60.css'],
            'post' => ['proj60_' => 'proj60_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { proj60_render_teacher_tab($c, $csrf()); },
        ],
        // v61 · přehled třídy: zaostávající žáci a vše, co čeká na schválení, na jedné obrazovce. POST ov61_bulk_confirm
        // (hromadné potvrzení hlášení; rozsah per položka) a GET export CSV mají politiky v teacher_scope_v59.php (třída povinná),
        // oprávnění ov61_ = content.manage (asistent jen čte).
        'prehled' => [
            'label' => 'Přehled třídy', 'hint' => 'Kdo zaostává a co čeká na schválení', 'group' => 'podpora',
            'files' => ['points_v53.php', 'points_v60.php', 'feedback_v60.php', 'projects_v60.php', 'marketplace_v60.php', 'teacher_class_dashboard.php', 'teacher_overview_v61.php', 'teacher_overview_v61_views.php'],
            'css' => ['assets/teacher-overview-v61.css'], 'js' => ['assets/teacher-overview-v61.js'],
            'post' => ['ov61_' => 'ov61_teacher_handle_post'],
            'get' => [
                'export' => static function (array $m): void { ov61_export_csv((string)($_GET['class'] ?? '')); },
            ],
            'render' => static function (array $m, string $c) use ($csrf): void { ov61_render_teacher_tab($c, $csrf()); },
        ],
        // v60 · hlášení chyb a návrhů žáků: potvrzení = body + XP (jen jednou). Admin vidí vše, učitel jen své třídy
        // (fb60_list + politika fb60_decide → entita fb60_report); asistent má jen 'view' → post neprojde.
        'hlaseni' => [
            'label' => 'Hlášení', 'hint' => 'Chyby a návrhy vylepšení od žáků, odměny body a XP', 'group' => 'podpora',
            'files' => ['feedback_v60.php', 'feedback_v60_teacher_views.php', 'points_v53.php'],
            'css' => ['assets/feedback-v60.css'],
            'post' => ['fb60_' => 'fb60_teacher_handle_post'],
            'render' => static function (array $m, string $c) use ($csrf): void { fb60_render_teacher_tab($c, $csrf()); },
        ],
    ];
}

/** Modul je dostupný, když existují všechny jeho soubory. */
function teacher58_available(string $tab): bool
{
    $mod = teacher58_modules()[$tab] ?? null;
    if (!is_array($mod)) return false;
    // v59 · AUTHZ58-07: modul účtů jen v režimu účtů, admin moduly jen administrátorovi (v legacy vidí vše jako dosud).
    if (!empty($mod['accounts_only']) && (!function_exists('teacher59_mode') || teacher59_mode() === 'legacy')) return false;
    if (!empty($mod['admin']) && function_exists('teacher59_is_admin') && !teacher59_is_admin()) return false;
    foreach ((array)($mod['files'] ?? []) as $file) {
        if (!is_file(__DIR__ . '/' . $file)) return false;
    }
    return true;
}

/** v60: záložka jen pro administrátora, kterou aktuální (neadmin) učitel dostat nemá – teacher.php pro ni vrací výslovné 403 místo tichého přehledu. */
function teacher58_is_admin_denied(string $tab): bool
{
    $mod = teacher58_modules()[$tab] ?? null;
    if (!is_array($mod) || empty($mod['admin'])) return false;
    if (!empty($mod['accounts_only']) && (!function_exists('teacher59_mode') || teacher59_mode() === 'legacy')) return false;
    return function_exists('teacher59_is_admin') && !teacher59_is_admin();
}

/** @return list<string> dostupné záložky v58 */
function teacher58_tabs(): array
{
    return array_values(array_filter(array_keys(teacher58_modules()), 'teacher58_available'));
}

function teacher58_is_tab(string $tab): bool
{
    return in_array($tab, teacher58_tabs(), true);
}

/** Záložky v58 mají vlastní přepínač tříd nebo jsou globální – obecný kontextový pruh se u nich nezobrazuje. */
function teacher58_own_class_tabs(): array
{
    return teacher58_tabs();
}

function teacher58_load(string $tab): void
{
    if (!teacher58_available($tab)) return;
    foreach ((array)(teacher58_modules()[$tab]['files'] ?? []) as $file) require_once __DIR__ . '/' . $file;
}

/** Líné načtení modulu podle záložky nebo prefixu POST akce. */
function teacher58_require_modules(string $tab, string $action): void
{
    if (teacher58_is_tab($tab)) teacher58_load($tab);
    if ($action === '') return;
    foreach (teacher58_modules() as $key => $mod) {
        foreach (array_keys((array)($mod['post'] ?? [])) as $prefix) {
            if (str_starts_with($action, (string)$prefix)) teacher58_load((string)$key);
        }
    }
}

/** POST akce modulů v58 (oprávnění už ověřil teacher.php přes teacher_action_permission). */
function teacher58_handle_post(string $action, array $modules): void
{
    foreach (teacher58_modules() as $key => $mod) {
        foreach ((array)($mod['post'] ?? []) as $prefix => $handler) {
            if (!str_starts_with($action, (string)$prefix)) continue;
            teacher58_load((string)$key);
            if (is_string($handler) && function_exists($handler)) { $handler($action, $modules); return; }
            throw new RuntimeException('Modul pro tuto akci není dostupný.');
        }
    }
}

/** Zvláštní GET (projektor, polling, tisk, export) – volá se po ověření přihlášení; true = obslouženo. */
function teacher58_handle_get(string $tab, array $modules): bool
{
    if (!teacher58_is_tab($tab)) return false;
    foreach ((array)(teacher58_modules()[$tab]['get'] ?? []) as $param => $call) {
        if (!isset($_GET[$param])) continue;
        teacher58_load($tab);
        $call($modules);
        return true;
    }
    return false;
}

function teacher58_render_tab(string $tab, array $modules, string $classId): void
{
    teacher58_load($tab);
    $render = teacher58_modules()[$tab]['render'] ?? null;
    try {
        if (is_callable($render)) { $render($modules, $classId); return; }
    } catch (Error $e) {
        error_log('EDUCANET v58 záložka ' . $tab . ': ' . $e->getMessage());
    }
    echo '<section class="teacher-empty wide">Modul se připravuje.</section>';
}

/** Odkazy modulů dané skupiny navigace (bez obalu). */
function teacher58_nav_links(string $group, string $tab, string $classId): void
{
    foreach (teacher58_tabs() as $key) {
        $mod = teacher58_modules()[$key];
        if ((string)($mod['group'] ?? '') !== $group) continue;
        $href = '?tab=' . rawurlencode($key) . ($classId !== '' ? '&class=' . rawurlencode($classId) : '');
        echo '<a class="' . ($tab === $key ? 'active' : '') . '" href="' . e($href) . '">' . e((string)$mod['label'])
            . '<small>' . e((string)($mod['hint'] ?? '')) . '</small></a>';
    }
}

/** Skupina „Hry a Lab“ v hlavní navigaci (Aréna + moduly v58 skupiny „hry“). */
function teacher58_render_nav(string $tab, string $classId): void
{
    $keys = array_values(array_filter(teacher58_tabs(), static fn(string $k): bool => (string)(teacher58_modules()[$k]['group'] ?? '') === 'hry'));
    if (!$keys) return;
    $active = $tab === 'arena' || in_array($tab, $keys, true);
    echo '<details class="teacher-nav-group"' . ($active ? ' data-active="1"' : '') . '><summary>Hry a Lab <i>⌄</i></summary>'
        . '<div class="teacher-nav-dropdown"><span class="teacher-nav-label">Soutěže, hry a Linux Lab</span>'
        . '<a class="' . ($tab === 'arena' ? 'active' : '') . '" href="?tab=arena">Aréna<small>Závody třídy, týdenní hádanka, záznam</small></a>';
    teacher58_nav_links('hry', $tab, $classId);
    echo '</div></details>';
}

/** CSS aktivní záložky v58 (s verzí podle filemtime). */
function teacher58_head_assets(string $tab): void
{
    if (!teacher58_is_tab($tab)) return;
    foreach ((array)(teacher58_modules()[$tab]['css'] ?? []) as $css) {
        $file = __DIR__ . '/' . $css;
        $v = is_file($file) ? (string)filemtime($file) : '58';
        echo '<link rel="stylesheet" href="' . e($css . '?v=' . $v) . '">';
    }
}

/** JS aktivní záložky v58. */
function teacher58_foot_assets(string $tab): void
{
    if (!teacher58_is_tab($tab)) return;
    foreach ((array)(teacher58_modules()[$tab]['js'] ?? []) as $js) {
        $file = __DIR__ . '/' . $js;
        $v = is_file($file) ? (string)filemtime($file) : '58';
        echo '<script src="' . e($js . '?v=' . $v) . '" defer></script>';
    }
}
