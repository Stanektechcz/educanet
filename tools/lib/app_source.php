<?php

declare(strict_types=1);

/**
 * Zdrojový kód žákovské aplikace pro audity (v58 · F5).
 *
 * Po rozdělení index.php (router) na app/actions/* a app/views/* už kód žákovské aplikace není
 * v jednom souboru. Audity, které dřív četly file_get_contents('index.php'), čtou edu_app_source():
 * index.php + soubory z app/ ve stejném pořadí, v jakém je vkládá router (POST akce, pomocné šablony,
 * pohledy – odpovídá pořadí v původním index.php), a nakonec zbylé soubory z app/ abecedně.
 *
 *   require_once __DIR__ . '/lib/app_source.php';
 *   $index = edu_app_source();            // kořen = o dvě úrovně výš (tools/lib → kořen projektu)
 *   $index = edu_app_source('/jiný/kořen');
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Seznam souborů žákovské aplikace (absolutní cesty) v pořadí routeru. */
function edu_app_source_files(?string $root = null): array
{
    $root = rtrim(str_replace('\\', '/', $root ?? dirname(__DIR__, 2)), '/');
    $files = [];
    if (is_file($root . '/index.php')) $files[] = $root . '/index.php';
    $app = $root . '/app';
    if (!is_dir($app)) return $files;
    foreach (['lib.php', 'routes.php'] as $name) if (is_file($app . '/' . $name)) $files[] = $app . '/' . $name;
    $routes = [];
    if (is_file($app . '/routes.php')) {
        $loaded = (static function (string $path) { return require $path; })($app . '/routes.php');
        if (is_array($loaded)) $routes = $loaded;
    }
    $add = static function (string $relative) use (&$files, $app): void {
        $path = $app . '/' . ltrim($relative, '/');
        if (is_file($path) && !in_array($path, $files, true)) $files[] = $path;
    };
    foreach (['actions_pre', 'actions_class'] as $stage) {
        foreach ((array)($routes[$stage] ?? []) as $segment) if (is_array($segment)) $add((string)($segment['file'] ?? ''));
    }
    $helpers = glob($app . '/views/_*.php') ?: [];
    sort($helpers, SORT_STRING);
    foreach ($helpers as $helper) $add('views/' . basename($helper));
    $pages = (array)($routes['pages'] ?? []);
    foreach ((array)($routes['views_early'] ?? []) as $segment) if (is_array($segment)) $add((string)($segment['file'] ?? ''));
    if (isset($pages['change_password']['file'])) $add((string)$pages['change_password']['file']);
    foreach ((array)($routes['views_public'] ?? []) as $segment) if (is_array($segment)) $add((string)($segment['file'] ?? ''));
    if (isset($pages['home']['file'])) $add((string)$pages['home']['file']);
    foreach ((array)($routes['views_student'] ?? []) as $segment) if (is_array($segment)) $add((string)($segment['file'] ?? ''));
    foreach ($pages as $page) if (is_array($page)) $add((string)($page['file'] ?? ''));
    $rest = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($app, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') $rest[] = str_replace('\\', '/', $file->getPathname());
    }
    sort($rest, SORT_STRING);
    foreach ($rest as $path) if (!in_array($path, $files, true)) $files[] = $path;
    return $files;
}

/** Tabulka rout app/routes.php (prázdné pole, pokud neexistuje). */
function edu_app_routes(?string $root = null): array
{
    $path = rtrim(str_replace('\\', '/', $root ?? dirname(__DIR__, 2)), '/') . '/app/routes.php';
    if (!is_file($path)) return [];
    $loaded = (static function (string $file) { return require $file; })($path);
    return is_array($loaded) ? $loaded : [];
}

/** Soubory knihoven (cesty od kořene) pro skupiny z app/routes.php['libs']. */
function edu_app_group_files(array $groups, ?string $root = null): array
{
    $defined = (array)(edu_app_routes($root)['libs'] ?? []);
    $files = [];
    foreach ($groups as $group) foreach ((array)($defined[(string)$group] ?? []) as $file) $files[(string)$file] = true;
    return array_keys($files);
}

/**
 * Knihovny, které router načte pro pohled ?view=$view: jádro ('core' a přímé require v index.php)
 * plus skupiny všech segmentů, které pohled obsluhují (včetně stránek 'pages' pro home/change_password).
 */
function edu_app_view_libs(string $view, ?string $root = null): array
{
    $routes = edu_app_routes($root);
    $groups = ['core'];
    $matches = static function (array $patterns, string $name): bool {
        foreach ($patterns as $p) {
            $p = (string)$p;
            if ($p === '*' || $p === $name || (str_ends_with($p, '*') && str_starts_with($name, substr($p, 0, -1)))) return true;
        }
        return false;
    };
    foreach (['views_early', 'views_public', 'views_student'] as $stage) {
        foreach ((array)($routes[$stage] ?? []) as $segment) {
            if (is_array($segment) && $matches((array)($segment['match'] ?? []), $view)) $groups = array_merge($groups, (array)($segment['libs'] ?? []));
        }
    }
    foreach ((array)($routes['pages'] ?? []) as $name => $page) {
        if ((string)$name === $view && is_array($page)) $groups = array_merge($groups, (array)($page['libs'] ?? []));
    }
    return array_values(array_unique(array_merge(edu_app_index_requires($root), edu_app_group_files($groups, $root))));
}

/** Knihovny, které index.php načítá přímo příkazem require/require_once. */
function edu_app_index_requires(?string $root = null): array
{
    $index = rtrim(str_replace('\\', '/', $root ?? dirname(__DIR__, 2)), '/') . '/index.php';
    $source = is_file($index) ? (string)file_get_contents($index) : '';
    preg_match_all("~require(?:_once)?\\s+__DIR__\\s*\\.\\s*'/([A-Za-z0-9_./-]+\\.php)'~", $source, $m);
    return array_values(array_unique($m[1]));
}

/** Načítá žákovská aplikace knihovnu $lib (přímo v index.php, nebo ve skupině knihoven některé routy)? */
function edu_app_loads(string $lib, ?string $root = null): bool
{
    $root = rtrim(str_replace('\\', '/', $root ?? dirname(__DIR__, 2)), '/');
    if (!is_file($root . '/' . $lib)) return false;
    if (in_array($lib, edu_app_index_requires($root), true)) return true;
    foreach ((array)(edu_app_routes($root)['libs'] ?? []) as $files) {
        if (in_array($lib, (array)$files, true)) return true;
    }
    return false;
}

/** Spojený zdroják index.php + app/**. Výsledek se cachuje pro daný kořen. */
function edu_app_source(?string $root = null): string
{
    static $cache = [];
    $key = (string)$root;
    if (isset($cache[$key])) return $cache[$key];
    $parts = [];
    foreach (edu_app_source_files($root) as $path) {
        $content = file_get_contents($path);
        if ($content !== false) $parts[] = $content;
    }
    return $cache[$key] = implode("\n", $parts);
}
