<?php

declare(strict_types=1);

/**
 * Pomocné funkce routeru žákovské aplikace (v58 · F5).
 *
 * index.php si přes app_segments() vyžádá seřazený seznam souborů (pohledů nebo POST akcí),
 * které odpovídají aktuálnímu ?view= / action=, a každý z nich vloží příkazem `require`
 * v globálním rozsahu – soubory tak vidí stejné proměnné ($modules, $classId, $module, $flash, …)
 * jako dřív kód přímo v index.php. Knihovny, které segment potřebuje, načte app_segment_file()
 * líně (require_once) těsně před vložením. Tabulka rout je v app/routes.php.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Tabulka rout (app/routes.php), načtená jednou za request. */
function app_routes(): array
{
    static $routes = null;
    if ($routes === null) {
        $loaded = require __DIR__ . '/routes.php';
        $routes = is_array($loaded) ? $loaded : [];
    }
    return $routes;
}

/**
 * Soubory knihoven (cesty relativní ke kořeni projektu) pro zadané skupiny z app/routes.php['libs'].
 * Neznámá skupina je chyba v tabulce rout – vyhodí výjimku, aby se nepřehlédla.
 */
function app_lib_files(array $groups): array
{
    $defined = (array)(app_routes()['libs'] ?? []);
    $files = [];
    foreach ($groups as $group) {
        if (!isset($defined[$group]) || !is_array($defined[$group])) {
            throw new LogicException('Neznámá skupina knihoven v app/routes.php: ' . (string)$group);
        }
        foreach ($defined[$group] as $file) $files[(string)$file] = true;
    }
    return array_keys($files);
}

/** Líně načte knihovny zadaných skupin (jen definice funkcí – knihovny nemají vedlejší efekty při načtení). */
function app_require_libs(array $groups): void
{
    $root = dirname(__DIR__);
    foreach (app_lib_files($groups) as $file) {
        require_once $root . '/' . $file;
    }
}

/** Odpovídá název (pohled nebo akce) vzoru? Vzor: přesný název, 'prefix_*' nebo '*'. */
function app_route_matches(array $patterns, string $name): bool
{
    foreach ($patterns as $pattern) {
        $pattern = (string)$pattern;
        if ($pattern === '*' || $pattern === $name) return true;
        if (str_ends_with($pattern, '*') && str_starts_with($name, substr($pattern, 0, -1))) return true;
    }
    return false;
}

/**
 * Seřazené segmenty dané fáze routeru, které odpovídají názvu pohledu / akce.
 * Fáze: views_early, views_public, views_student, actions_pre, actions_class (viz app/routes.php).
 */
function app_segments(string $stage, string $name): array
{
    $out = [];
    foreach ((array)(app_routes()[$stage] ?? []) as $segment) {
        if (is_array($segment) && app_route_matches((array)($segment['match'] ?? []), $name)) $out[] = $segment;
    }
    return $out;
}

/** Pojmenovaná stránka z app/routes.php['pages'] (např. vynucená změna hesla, přihlášení). */
function app_page(string $name): array
{
    $page = app_routes()['pages'][$name] ?? null;
    if (!is_array($page)) throw new LogicException('Neznámá stránka v app/routes.php: ' . $name);
    return $page;
}

/**
 * Připraví segment k vložení: načte jeho knihovny a u pohledů označených 'session' => 'read'
 * uvolní zámek session (DAT-04). Vrací absolutní cestu k souboru, který index.php vloží přes require.
 */
function app_segment_file(array $segment): string
{
    app_require_libs((array)($segment['libs'] ?? []));
    if (($segment['session'] ?? '') === 'read') app_release_session();
    return __DIR__ . '/' . (string)$segment['file'];
}

/**
 * DAT-04: uloží session a uvolní její zámek, aby paralelní požadavky téhož žáka nečekaly.
 * CSRF token se vytvoří předem (hlavička stránky ho vypisuje), po uzavření už se do session nezapisuje.
 * Použít jen u pohledů, u kterých je ověřeno, že do session nic nezapisují (viz tests/app_router_audit.php).
 */
function app_release_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    csrf_token();
    session_write_close();
}

/**
 * DAT-05: URL assetu s verzí podle času poslední změny souboru, takže se po úpravě souboru
 * nenačte stará verze z cache. $path je cesta relativní ke kořeni webu, volitelně se stávající
 * verzí `?v=…`, která se použije jako záloha, když soubor nejde přečíst.
 * Příklad: asset_url('assets/app.css?v=46') → 'assets/app.css?v=1758812345'.
 */
function asset_url(string $path): string
{
    static $cache = [];
    if (isset($cache[$path])) return $cache[$path];
    $parts = explode('?', $path, 2);
    $file = ltrim($parts[0], '/');
    parse_str($parts[1] ?? '', $query);
    $fallback = isset($query['v']) && is_string($query['v']) ? $query['v'] : '';
    $mtime = false;
    if ($file !== '' && !str_contains($file, '..') && preg_match('~^[A-Za-z0-9_./-]+$~', $file) === 1) {
        $absolute = dirname(__DIR__) . '/' . $file;
        $mtime = is_file($absolute) ? @filemtime($absolute) : false;
    }
    $version = $mtime !== false ? (string)$mtime : $fallback;
    return $cache[$path] = $version !== '' ? $file . '?v=' . rawurlencode($version) : $file;
}
