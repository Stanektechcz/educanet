<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v68 · tools/v68_dead_code_report.php – kandidáti na vyřazení starších vrstev v42–v50 (JEN ČTE, nic nemění ani nemaže).
 *
 *   php tools/v68_dead_code_report.php [--md]
 *
 * Kandidát = soubor *_v42…v50* (PHP v kořeni, assets/*-v4x|v50*.{css,js}) nebo pohled v app/views. Pro každý spočítá odkazy:
 *   routes   zmínky v app/routes.php (skupiny knihoven, pohledy),
 *   code     zmínky v ostatních PHP souborech aplikace, které NEJSOU kandidáty (require, include, odkaz na asset, ?view=),
 *   links    odkazy ?view=<název>, module_url('<název>') z živého kódu na pohled (jen pro app/views; názvy podle app/routes.php),
 *   tools    zmínky v tools/ a tests/ (audity),
 *   precache zmínky v sw.js a app/views/precache.php.
 * Verdikt: soubor je „kandidát“, když na něj nevede žádný odkaz z routes ani z živého kódu a není v precache; pohled je kandidát, když na něj
 * nevede žádný odkaz ?view=… z kódu; „živý“ jinak. Tabulka je podklad pro rozhodnutí školy/uživatele,
 * ne příkaz ke smazání: vyřazení dělá až tools/v68_retire.php --apply po výslovném souhlasu.
 * Konec: V68_DEAD_CODE_REPORT_OK candidates=N live=M
 */

$root = str_replace(chr(92), '/', dirname(__DIR__));
$skipDirs = ['storage', 'V1', 'lab-runtime', 'uploads', 'cache', 'private', 'retired', 'docs', 'database', 'materials', 'tools/legacy', '.claude'];

/** @return list<string> relativní cesty zdrojových souborů */
function dc68_files(string $root, array $skipDirs): array
{
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $rel = ltrim(substr(str_replace(chr(92), '/', $f->getPathname()), strlen($root)), '/');
        foreach ($skipDirs as $d) if ($rel === $d || str_starts_with($rel, $d . '/')) continue 2;
        if (preg_match('/\.(php|js|css)$/', $rel) === 1) $out[] = $rel;
    }
    sort($out);
    return $out;
}

function dc68_is_candidate(string $rel): bool
{
    if (str_starts_with($rel, 'tools/') || str_starts_with($rel, 'tests/') || str_starts_with($rel, 'lang/')) return false;
    $base = basename($rel);
    if (str_starts_with($base, 'cx-') || str_contains($base, '-v68')) return false;   // v68: odvozené kopie cockpitu se linkují dynamicky (teacher68_css_href), ne textem
    if (preg_match('/(_|-)v(4[2-9]|50)(\D|$)/', $base) === 1) return true;
    return str_starts_with($rel, 'app/views/') && !str_starts_with($base, '_');
}

$files = dc68_files($root, $skipDirs);
$content = [];
foreach ($files as $rel) $content[$rel] = (string)file_get_contents($root . '/' . $rel);
$candidates = array_values(array_filter($files, 'dc68_is_candidate'));
$rows = [];
/** Názvy pohledů (?view=) podle souboru z app/routes.php: 'views/x.php' => ['a','b']. @return array<string,list<string>> */
function dc68_route_names(string $routes): array
{
    $out = [];
    if (preg_match_all("/'match'\s*=>\s*\[([^\]]*)\],\s*'file'\s*=>\s*'(views\/[a-z0-9_]+\.php)'/", $routes, $m, PREG_SET_ORDER) > 0) {
        foreach ($m as $row) {
            preg_match_all("/'([a-z0-9_*]+)'/", $row[1], $names);
            $out[$row[2]] = array_merge($out[$row[2]] ?? [], $names[1]);
        }
    }
    return $out;
}

$routeNames = dc68_route_names($content['app/routes.php'] ?? '');
foreach ($candidates as $cand) {
    $base = basename($cand);
    $isView = str_starts_with($cand, 'app/views/');
    $needle = '/' . preg_quote($base, '/') . '/';
    $names = $isView ? ($routeNames['views/' . $base] ?? []) : [];
    $ref = ['routes' => 0, 'code' => 0, 'family' => 0, 'tools' => 0, 'precache' => 0, 'links' => 0];
    foreach ($content as $rel => $text) {
        if ($rel === $cand) continue;
        $hit = preg_match($needle, $text) === 1;
        $isCode = !in_array($rel, $candidates, true) && !str_starts_with($rel, 'tools/') && !str_starts_with($rel, 'tests/') && $rel !== 'app/routes.php' && $rel !== 'sw.js';
        foreach ($names as $name) {
            if ($isCode && preg_match("/view=" . preg_quote($name, '/') . "(?![a-z0-9_])|module_url\(\s*'" . preg_quote($name, '/') . "'|'view'\s*=>\s*'" . preg_quote($name, '/') . "'/", $text) === 1) { $ref['links']++; break; }
        }
        if (!$hit) continue;
        if ($rel === 'app/routes.php') $ref['routes']++;
        elseif ($rel === 'sw.js' || $rel === 'app/views/precache.php') $ref['precache']++;
        elseif (str_starts_with($rel, 'tools/') || str_starts_with($rel, 'tests/')) $ref['tools']++;
        elseif (in_array($rel, $candidates, true)) $ref['family']++;
        else $ref['code']++;
    }
    // Pohled je živý, když na něj vede odkaz (?view=…) z kódu; ostatní soubory, když na ně míří routes/kód/precache.
    $entryPoints = ['home.php', 'change_password.php', 'precache.php']; // vkládá je přímo index.php / stahuje je service worker – bez ?view= odkazu jsou živé
    $noLink = $isView ? ($ref['links'] === 0 && $ref['precache'] === 0) : ($ref['routes'] === 0 && $ref['code'] === 0 && $ref['precache'] === 0);
    $noLink = $isView && in_array($base, $entryPoints, true) ? false : $noLink;
    // v68: kandidát = zcela bez vazby (0 routes, 0 kód, 0 odkazů, 0 precache); bez odkazu, ale s route/načtením = vázaný (jen přesměrovat/vypsat).
    $free = $noLink && $ref['routes'] === 0 && $ref['code'] === 0 && $ref['links'] === 0 && $ref['precache'] === 0;
    $dead = $free;
    $bound = $noLink && !$free;
    $rows[] = ['file' => $cand, 'ref' => $ref, 'dead' => $dead, 'bound' => $bound, 'lines' => substr_count($content[$cand], "\n"), 'names' => $names];
}
$md = in_array('--md', $argv, true);
if ($md) {
    echo "| Soubor | Řádků | routes | kód | rodina | audity | precache | Verdikt |\n|---|---:|---:|---:|---:|---:|---:|---|\n";
}
$dead = 0;
$boundCount = 0;
foreach ($rows as $r) {
    $v = $r['dead'] ? 'kandidát' : ($r['bound'] ? 'vázaný' : 'živý');
    if ($r['bound']) $boundCount++;
    if ($r['dead']) $dead++;
    $x = $r['ref'];
    echo $md ? sprintf("| `%s` | %d | %d | %d | %d | %d | %d | %s |\n", $r['file'], $r['lines'], $x['routes'], $x['code'], $x['links'], $x['tools'], $x['precache'], $v)
        : sprintf("%-9s %-52s routes=%d code=%d family=%d tools=%d precache=%d\n", $v, $r['file'], $x['routes'], $x['code'], $x['family'], $x['tools'], $x['precache']);
}
echo 'V68_DEAD_CODE_REPORT_OK candidates=' . $dead . ' bound=' . $boundCount . ' live=' . (count($rows) - $dead - $boundCount) . "
";
