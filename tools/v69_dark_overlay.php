<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v69 · tools/v69_dark_overlay.php – tmavá vrstva žáka rozdělená podle pohledu.
 *
 *   php tools/v69_dark_overlay.php            vygeneruje assets/dark/student-dark-<pohled>-v69.css (home, dashboard, profile)
 *   php tools/v69_dark_overlay.php --check    ověří, že soubory odpovídají zdrojům (V69_OVERLAY_CURRENT / V69_OVERLAY_STALE, exit 1)
 *   php tools/v69_dark_overlay.php --stats    velikost a gzip každého souboru
 *
 * Proti v68 (jediný soubor ~420 KB se všemi pravidly) má každý pohled vlastní soubor, který obsahuje jen:
 *   1. pravidla ze stylů, které ten pohled opravdu načítá (viz _layout.php; DO69_VIEWS),
 *   2. z nich jen ta, jejichž třídy a identifikátory se v kódu pohledu vyskytují (řetězce v šablonách a knihovnách pohledu, v JS stránky);
 *      název končící „-“ nebo „_“ (skládaný za běhu) platí jako předpona. Pravidlo bez tříd a id (značky, :root, atributy) zůstává vždy.
 *   3. stejné transformace barev jako v68 (tools/v68_dark_overlay.php: do68_decls, do68_selector).
 * Úplnost pruningu hlídá tools/v69_dark_audit.php na skutečně vykreslených stránkách. Zdrojová CSS se nemění.
 */

require_once __DIR__ . '/v68_dark_overlay.php';
require_once __DIR__ . '/lib/v69_css_usage.php';
require_once dirname(__DIR__) . '/app/lib.php';

/** Pohled → styly (assets/), JS stránky a výstup. Pohledy musí odpovídat ui67_dark_views(). @var array<string,array{css:list<string>,js:list<string>,out:string}> */
const DO69_VIEWS = [
    'home' => [
        'css' => ['app.css', 'mastery.css', 'cognitive-v43.css', 'learning-studio-v44.css', 'visual-simulation-v45.css', 'ui-v51.css', 'session-v53.css', 'brand-v54.css',
            'student-v55.css', 'learning-v56.css', 'components-v61.css', 'nav-v61.css', 'i18n-v59.css', 'login-v63.css'],
        'js' => ['ui-v51.js', 'student-v55.js', 'learning-v56.js', 'i18n-v58.js'],
        'out' => 'assets/dark/student-dark-home-v69.css',
    ],
    'dashboard' => [
        'css' => ['app.css', 'mastery.css', 'cognitive-v43.css', 'learning-studio-v44.css', 'visual-simulation-v45.css', 'student-coach-v47.css', 'student-ui-v50-7-7.css', 'ui-v51.css',
            'tutorial-v52.css', 'session-v53.css', 'brand-v54.css', 'student-v55.css', 'learning-v56.css', 'components-v61.css', 'nav-v61.css', 'motivation-v61.css', 'i18n-v59.css'],
        'js' => ['student-ui-v50-7-7.js', 'ui-v51.js', 'student-v55.js', 'learning-v56.js', 
            'nav-v61.js', 'student-coach-v47.js', 'i18n-v58.js'],
        'out' => 'assets/dark/student-dark-dashboard-v69.css',
    ],
    'profile' => [
        'css' => ['app.css', 'mastery.css', 'cognitive-v43.css', 'learning-studio-v44.css', 'visual-simulation-v45.css', 'student-ui-v50-7-7.css', 'ui-v51.css', 'tutorial-v52.css',
            'session-v53.css', 'brand-v54.css', 'student-v55.css', 'learning-v56.css', 'components-v61.css', 'nav-v61.css', 'motivation-v61.css', 'i18n-v59.css', 'profile-v60.css',
            'competency-v62.css', 'growth-v67.css', 'paths-card-v63.css', 'paths-v63.css'],
        'js' => ['student-ui-v50-7-7.js', 'ui-v51.js', 'student-v55.js', 'learning-v56.js', 
            'nav-v61.js', 'i18n-v58.js'],
        'out' => 'assets/dark/student-dark-profile-v69.css',
    ],
];

/** Obecný JS načítaný na všech stránkách (widgety vytvářejí DOM jen tam, kde šablona nese jejich kořen): jeho třídy samy pravidlo nezachrání. */
const DO69_WIDGET_JS = ['app.js', 'cognitive-v43.js', 'learning-studio-v44.js', 'visual-simulation-v45.js'];

/** Vstupní soubory pohledu: soubor pohledu ze segmentů routeru, _layout.php a index.php. @return list<string> */
function do69_entry_files(string $view): array
{
    $files = ['index.php' => 1, 'app/views/_layout.php' => 1];
    foreach (['views_early', 'views_public', 'views_student'] as $stage) {
        foreach (app_segments($stage, $view) as $segment) $files['app/' . (string)$segment['file']] = 1;
    }
    $page = app_routes()['pages'][$view] ?? null;   // pojmenované stránky (přihlášení)
    if (is_array($page)) $files['app/' . (string)$page['file']] = 1;
    return array_keys($files);
}

/** Slova (možné názvy tříd) pohledu a jejich předpony – viz tools/lib/v69_css_usage.php. @return array{0:array<string,true>,1:list<string>,2:array<string,true>} */
function do69_view_words(string $root, string $view): array
{
    static $index = null;
    $index ??= u69_index($root);
    $asset = static fn(string $f): string => 'assets/' . $f;
    $words = u69_view_words($root, $index, do69_entry_files($view), array_map($asset, DO69_VIEWS[$view]['js']));
    $widget = [];
    foreach (DO69_WIDGET_JS as $js) $widget += u69_js_words((string)file_get_contents($root . '/assets/' . $js));
    return [$words, u69_prefixes($words), $widget];
}

/** Jako do68_walk, ale vynechá části selektorů s nepoužitými třídami/id. */
function do69_walk(string $css, array $tokens, array $prefixes, array $widget = []): string
{
    $css = (string)preg_replace('~/\*.*?\*/~s', '', $css);
    $out = '';
    $pos = 0;
    $len = strlen($css);
    while ($pos < $len) {
        $open = strpos($css, '{', $pos);
        if ($open === false) break;
        $prelude = trim(substr($css, $pos, $open - $pos));
        $end = do68_block_end($css, $open + 1);
        $inner = substr($css, $open + 1, $end - $open - 1);
        $pos = $end + 1;
        if ($prelude === '') continue;
        if ($prelude[0] === '@') {
            if (preg_match('~^@(media|supports|layer)\b~', $prelude) === 1) {
                $sub = do69_walk($inner, $tokens, $prefixes, $widget);
                if ($sub !== '') $out .= $prelude . '{' . $sub . '}';
            }
            continue;
        }
        $parts = array_filter(array_map('trim', do68_split($prelude, ',')), static fn(string $p): bool => $p !== '' && u69_part_used($p, $tokens, $prefixes, $widget));
        if ($parts === []) continue;
        $decls = do68_decls($inner);
        if ($decls !== '') $out .= do68_selector(implode(', ', $parts)) . '{' . $decls . "}\n";
    }
    return $out;
}

/** Obsah výstupního souboru jednoho pohledu. */
function do69_build(string $root, string $view): string
{
    $def = DO69_VIEWS[$view];
    [$tokens, $prefixes, $widget] = do69_view_words($root, $view);
    $text = '/* EDUCANET v69 · tmavá vrstva pohledu „' . $view . '“ – GENEROVÁNO tools/v69_dark_overlay.php ze souborů assets/*.css, ručně neupravovat. Načítá se jen při tmavém motivu. */' . "\n";
    foreach ($def['css'] as $name) {
        $src = (string)file_get_contents($root . '/assets/' . $name);
        $text .= '/* ' . $name . ' (sha256 ' . substr(hash('sha256', $src), 0, 12) . ") */\n" . do69_walk($src, $tokens, $prefixes, $widget);
    }
    return $text . DO68_TAIL . "\n";
}

function do69_main(array $argv): int
{
    $root = str_replace(chr(92), '/', dirname(__DIR__));
    $check = in_array('--check', $argv, true);
    $stats = in_array('--stats', $argv, true);
    $stale = [];
    $sizes = [];
    foreach (DO69_VIEWS as $view => $def) {
        $text = do69_build($root, $view);
        $target = $root . '/' . $def['out'];
        $sizes[$view] = [strlen($text), strlen((string)gzencode($text, 6))];
        if (!is_file($target) || !hash_equals(hash('sha256', $text), (string)hash_file('sha256', $target))) $stale[] = $def['out'];
        if (!$check && !$stats) {
            if (!is_dir(dirname($target))) mkdir(dirname($target), 0775, true);
            file_put_contents($target, $text);
        }
    }
    if ($stats) {
        foreach ($sizes as $view => [$raw, $gz]) echo sprintf("%-10s raw=%7d gzip6=%6d\n", $view, $raw, $gz);
        return 0;
    }
    if ($check) {
        echo $stale === [] ? "V69_OVERLAY_CURRENT\n" : 'V69_OVERLAY_STALE ' . implode(',', $stale) . "\n";
        return $stale === [] ? 0 : 1;
    }
    echo 'V69_OVERLAY_OK ' . implode(' ', array_map(static fn(string $v): string => $v . '=' . $sizes[$v][0] . '/' . $sizes[$v][1], array_keys($sizes))) . "\n";
    return 0;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(do69_main($argv));
