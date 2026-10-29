<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v78 · inventura vrstev CSS (fáze A, jen čtení assets/ a zdrojů PHP/JS; úložiště nečte).
 *   php tools/v78_css_inventory.php [--out=docs/CSS_INVENTORY_V78.md] [--json=<soubor>]
 * Pro každý soubor assets/*.css a assets/dark/*.css: velikost (raw/gzip), řádky, pravidla, selektory, deklarace, vlastní proměnné,
 * pevné barvy, !important, @media/@container, kdo soubor načítá. Dále duplicity (stejný selektor ve více souborech, shodné bloky),
 * konflikty (stejný selektor + vlastnost s jinou hodnotou v různých souborech), nepoužité soubory a návrh sloučení pro fázi F.
 * Odvozené kopie cockpitu assets/cx-*.css (tools/v68_tokenize_css.php) se do duplicit a konfliktů nepočítají – mají vlastní řádek.
 * Nic nemaže ani nepřesouvá (vyřazení až ve fázi F: kopie → SHA-256 → unlink, po jednom souboru).
 */

$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$opts = getopt('', ['out:', 'json:']);

/** Odstraní komentáře. */
function inv_strip(string $css): string
{
    return (string)preg_replace('~/\*.*?\*/~s', '', $css);
}

/** Rozdělí řetězec podle oddělovače mimo závorky a uvozovky. @return list<string> */
function inv_split(string $s, string $sep): array
{
    $out = [];
    $buf = '';
    $depth = 0;
    $quote = '';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $c = $s[$i];
        if ($quote !== '') { $buf .= $c; if ($c === $quote && ($i === 0 || $s[$i - 1] !== '\\')) $quote = ''; continue; }
        if ($c === '"' || $c === "'") { $quote = $c; $buf .= $c; continue; }
        if ($c === '(' || $c === '[') $depth++;
        if ($c === ')' || $c === ']') $depth--;
        if ($c === $sep && $depth === 0) { $out[] = trim($buf); $buf = ''; continue; }
        $buf .= $c;
    }
    if (trim($buf) !== '') $out[] = trim($buf);
    return array_values(array_filter($out, static fn(string $x): bool => $x !== ''));
}

/** Deklarace bloku. @return list<array{0:string,1:string,2:bool}> [vlastnost, hodnota, important] */
function inv_decls(string $block): array
{
    $out = [];
    foreach (inv_split($block, ';') as $d) {
        $p = strpos($d, ':');
        if ($p === false) continue;
        $prop = strtolower(trim(substr($d, 0, $p)));
        $val = trim(substr($d, $p + 1));
        $imp = (bool)preg_match('/!\s*important\s*$/i', $val);
        $out[] = [$prop, trim((string)preg_replace('/\s+/', ' ', (string)preg_replace('/!\s*important\s*$/i', '', $val))), $imp];
    }
    return $out;
}

/** Pravidla souboru (vnořené @media/@supports/@container/@layer rozbalí do kontextu). @return list<array{ctx:string, at:string, sel:list<string>, decl:list<array>}> */
function inv_rules(string $css, string $ctx = ''): array
{
    $rules = [];
    $len = strlen($css);
    $i = 0;
    while ($i < $len) {
        $open = strpos($css, '{', $i);
        if ($open === false) break;
        $semi = strpos($css, ';', $i);
        if ($semi !== false && $semi < $open) { $i = $semi + 1; continue; }
        $prelude = trim((string)preg_replace('/\s+/', ' ', substr($css, $i, $open - $i)));
        $depth = 1;
        $j = $open + 1;
        while ($j < $len && $depth > 0) { $c = $css[$j]; if ($c === '{') $depth++; elseif ($c === '}') $depth--; $j++; }
        $inner = substr($css, $open + 1, max(0, $j - $open - 2));
        if (str_starts_with($prelude, '@')) {
            if (preg_match('/^@(media|supports|container|layer)\b/i', $prelude) === 1) {
                foreach (inv_rules($inner, trim($ctx . ' ' . $prelude)) as $r) $rules[] = $r;
            } else {
                $rules[] = ['ctx' => $ctx, 'at' => $prelude, 'sel' => [], 'decl' => []];
            }
        } elseif ($prelude !== '') {
            $rules[] = ['ctx' => $ctx, 'at' => '', 'sel' => inv_split($prelude, ','), 'decl' => inv_decls($inner)];
        }
        $i = $j;
    }
    return $rules;
}

/** Rodina vrstvy podle jména souboru (pro návrh sloučení). */
function inv_family(string $name): string
{
    return match (true) {
        str_ends_with($name, '-v78.css') => 'design systém v78',
        str_starts_with($name, 'cx-') => 'cx (odvozené kopie cockpitu)',
        str_starts_with($name, 'dark/') || str_contains($name, '-dark-') || str_starts_with($name, 'tokens-dark') => 'tmavý režim',
        str_starts_with($name, 'tokens-') => 'tokeny',
        str_starts_with($name, 'cockpit-') || str_starts_with($name, 'teacher') => 'cockpit učitele',
        str_contains($name, 'print') || str_contains($name, 'projector') => 'tisk a projektor',
        (bool)preg_match('/^(linux|lab-|arena|robots|teamgames|ops|identity|accounts)/', $name) => 'Lab, Aréna a moduly v57–v58',
        (bool)preg_match('/^(app|ui-v51|student|mastery|cognitive|learning|visual|hands-on|tutorial|session|brand|nav-v61|components-v61|profile|login|learn-|i18n|paths|projects|marketplace|motivation|feedback|growth|competency|assessment|lesson-v72|ui-v67)/', $name) => 'žák a sdílené vrstvy',
        default => 'ostatní',
    };
}

$files = array_merge(glob($ROOT . '/assets/*.css') ?: [], glob($ROOT . '/assets/dark/*.css') ?: []);
sort($files);
$sources = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ROOT, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($ROOT) + 1));
    if (preg_match('~^(storage|tools|tests|V1|retired|cache|docs|lab-runtime|vendor|private|materials)/|^\.~', $rel) === 1) continue;
    if (preg_match('/\.(php|js)$/', $rel) !== 1 || str_starts_with($rel, 'assets/') && !str_ends_with($rel, '.js')) continue;
    $sources[$rel] = (string)file_get_contents($f->getPathname());
}

$stats = [];
$index = [];      // ctx|selector => [file => [prop => value]]
$blocks = [];     // hash bloku => [file...]
$colorRe = '~#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})\b|\b(?:rgba?|hsla?)\(~i';
foreach ($files as $path) {
    $name = substr(str_replace('\\', '/', $path), strlen($ROOT) + 8);
    $raw = (string)file_get_contents($path);
    $rules = inv_rules(inv_strip($raw));
    $sel = $decl = $vars = $imp = $colors = 0;
    $dup = [];
    foreach ($rules as $r) {
        if ($r['at'] !== '') continue;
        $sel += count($r['sel']);
        $decl += count($r['decl']);
        foreach ($r['decl'] as [$p, $v, $important]) {
            if (str_starts_with($p, '--')) $vars++;
            elseif (preg_match_all($colorRe, $v) > 0) $colors++;
            if ($important) $imp++;
        }
        $key = $r['ctx'] . '|' . implode(',', $r['sel']) . '|' . json_encode($r['decl']);
        $dup[$key] = ($dup[$key] ?? 0) + 1;
        if (str_starts_with($name, 'cx-')) continue;
        $blocks[hash('sha1', $key)][$name] = true;
        foreach ($r['sel'] as $s) foreach ($r['decl'] as [$p, $v]) $index[$r['ctx'] . '|' . $s][$name][$p] = $v;
    }
    $base = basename($name);
    $refs = array_keys(array_filter($sources, static fn(string $src): bool => str_contains($src, $base)));
    // dynamické odkazy: cx-<originál> přes teacher68_css_href(), tmavé vrstvy žáka přes sprintf(UI69_STUDENT_DARK_CSS, pohled)
    if ($refs === [] && str_starts_with($name, 'cx-') && is_file($ROOT . '/assets/' . substr($name, 3))) $refs = ['teacher_shell_v68.php (kopie ' . substr($name, 3) . ')'];
    if ($refs === [] && preg_match('~^dark/student-dark-([a-z]+)-v69\.css$~', $name, $dm) === 1 && str_contains($sources['src/ui/ui_v67.php'] ?? '', "'" . $dm[1] . "'")) $refs = ['ui_v67.php (UI69_STUDENT_DARK_CSS, pohled ' . $dm[1] . ')'];
    $stats[$name] = [
        'bytes' => strlen($raw), 'gzip' => strlen((string)gzencode($raw, 9)), 'lines' => substr_count($raw, "\n") + 1,
        'rules' => count(array_filter($rules, static fn(array $r): bool => $r['at'] === '')), 'selectors' => $sel, 'decls' => $decl, 'vars' => $vars,
        'colors' => $colors, 'important' => $imp, 'media' => preg_match_all('/@media\b/i', $raw), 'container' => preg_match_all('/@container\b/i', $raw),
        'dup_in_file' => array_sum(array_map(static fn(int $n): int => $n - 1, array_filter($dup, static fn(int $n): bool => $n > 1))),
        'refs' => $refs, 'family' => inv_family($name),
    ];
}

// duplicity a konflikty mezi soubory (bez cx-*)
$multi = [];
$pairs = [];
$conflicts = [];
$conflictPairs = [];
foreach ($index as $key => $byFile) {
    if (count($byFile) < 2) continue;
    $multi[$key] = count($byFile);
    $names = array_keys($byFile);
    sort($names);
    for ($a = 0; $a < count($names); $a++) {
        for ($b = $a + 1; $b < count($names); $b++) {
            $pk = $names[$a] . ' ↔ ' . $names[$b];
            $pairs[$pk] = ($pairs[$pk] ?? 0) + 1;
            foreach ($byFile[$names[$a]] as $prop => $v) {
                if (str_starts_with($prop, '--') || !isset($byFile[$names[$b]][$prop]) || $byFile[$names[$b]][$prop] === $v) continue;
                $conflictPairs[$pk] = ($conflictPairs[$pk] ?? 0) + 1;
                if (count($conflicts[$pk] ?? []) < 3) $conflicts[$pk][] = [$key, $prop, $names[$a], $v, $names[$b], $byFile[$names[$b]][$prop]];
            }
        }
    }
}
arsort($multi);
arsort($pairs);
arsort($conflictPairs);
/** Načítají se oba soubory na stejné stránce? (společný zdroj PHP/JS, který je odkazuje) */
$coLoaded = static function (string $pk) use (&$stats): bool { [$a, $b] = explode(' ↔ ', $pk); return array_intersect($stats[$a]['refs'] ?? [], $stats[$b]['refs'] ?? []) !== []; };
$sameBlocks = count(array_filter($blocks, static fn(array $f): bool => count($f) > 1));
$tot = ['bytes' => 0, 'gzip' => 0, 'selectors' => 0, 'colors' => 0, 'important' => 0];
foreach ($stats as $s) foreach ($tot as $k => $_) $tot[$k] += $s[$k];
$cx = array_filter($stats, static fn(array $s, string $n): bool => str_starts_with($n, 'cx-'), ARRAY_FILTER_USE_BOTH);
$unused = array_keys(array_filter($stats, static fn(array $s): bool => $s['refs'] === []));
$fam = [];
foreach ($stats as $n => $s) { $fam[$s['family']]['files'][] = $n; $fam[$s['family']]['bytes'] = ($fam[$s['family']]['bytes'] ?? 0) + $s['bytes']; $fam[$s['family']]['gzip'] = ($fam[$s['family']]['gzip'] ?? 0) + $s['gzip']; }
ksort($fam);

$kb = static fn(int $b): string => number_format($b / 1024, 1, ',', ' ');
$md = [];
$md[] = '# Inventura CSS v78 (fáze A)';
$md[] = '';
$md[] = 'Generováno `php tools/v78_css_inventory.php --out=docs/CSS_INVENTORY_V78.md` (' . date('j. n. Y') . '). Jen čtení: nic se nemaže ani nepřesouvá – vyřazení je až ve fázi F postupem kopie → SHA-256 → unlink, po jednom souboru.';
$md[] = '';
$md[] = '## Souhrn';
$md[] = sprintf('- **%d souborů**, %s kB raw / %s kB gzip, %s selektorů, %s deklarací s pevnou barvou, %s × `!important`.', count($stats), $kb($tot['bytes']), $kb($tot['gzip']), number_format($tot['selectors'], 0, ',', ' '), number_format($tot['colors'], 0, ',', ' '), number_format($tot['important'], 0, ',', ' '));
$md[] = sprintf('- **Odvozené kopie cockpitu `cx-*`**: %d souborů, %s kB raw (generuje `tools/v68_tokenize_css.php --derive` z originálů žáka; v duplicitách se nepočítají).', count($cx), $kb(array_sum(array_column($cx, 'bytes'))));
$md[] = sprintf('- **Selektory definované ve více souborech** (stejný kontext @media): %s; shodné bloky (selektor + deklarace) ve více souborech: %d.', number_format(count($multi), 0, ',', ' '), $sameBlocks);
$md[] = sprintf('- **Konflikty** (stejný selektor a vlastnost, jiná hodnota v jiném souboru, bez vlastních proměnných): %s ve %d dvojicích souborů.', number_format(array_sum($conflictPairs), 0, ',', ' '), count($conflictPairs));
$md[] = '- **Nenačítané soubory** (název se nevyskytuje v PHP ani JS aplikace): ' . ($unused === [] ? 'žádné' : implode(', ', array_map(static fn(string $n): string => '`' . $n . '`', $unused))) . '.';
$md[] = '';
$md[] = '## Rodiny vrstev';
$md[] = '| Rodina | Souborů | Raw kB | Gzip kB |';
$md[] = '|---|---:|---:|---:|';
foreach ($fam as $f => $d) $md[] = '| ' . $f . ' | ' . count($d['files']) . ' | ' . $kb($d['bytes']) . ' | ' . $kb($d['gzip']) . ' |';
$md[] = '';
$md[] = '## Soubory';
$md[] = '| Soubor | Rodina | Raw kB | Gzip kB | Řádky | Pravidla | Selektory | Proměnné | Pevné barvy | !important | @media | @container | Duplicity v souboru | Načítá |';
$md[] = '|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---|';
foreach ($stats as $n => $s) {
    $refs = $s['refs'] === [] ? '**nikdo**' : implode(', ', array_slice($s['refs'], 0, 3)) . (count($s['refs']) > 3 ? ' +' . (count($s['refs']) - 3) : '');
    $md[] = '| `' . $n . '` | ' . $s['family'] . ' | ' . $kb($s['bytes']) . ' | ' . $kb($s['gzip']) . ' | ' . $s['lines'] . ' | ' . $s['rules'] . ' | ' . $s['selectors'] . ' | ' . $s['vars'] . ' | '
        . $s['colors'] . ' | ' . $s['important'] . ' | ' . $s['media'] . ' | ' . $s['container'] . ' | ' . $s['dup_in_file'] . ' | ' . $refs . ' |';
}
$md[] = '';
$md[] = '## Duplicity: nejčastěji sdílené selektory (bez `cx-*`)';
$md[] = '| Selektor (kontext) | Souborů |';
$md[] = '|---|---:|';
foreach (array_slice($multi, 0, 20, true) as $k => $n) $md[] = '| `' . str_replace('|', ' ⟂ ', trim($k, '|')) . '` | ' . $n . ' |';
$md[] = '';
$md[] = '## Duplicity: dvojice souborů s nejvíce společnými selektory';
$md[] = '| Dvojice | Společné selektory | Z toho konfliktních vlastností | Na stejné stránce |';
$md[] = '|---|---:|---:|---|';
foreach (array_slice($pairs, 0, 20, true) as $k => $n) $md[] = '| ' . $k . ' | ' . $n . ' | ' . ($conflictPairs[$k] ?? 0) . ' | ' . ($coLoaded($k) ? 'ano' : 'ne') . ' |';
$md[] = '';
$md[] = '## Konflikty mezi soubory načítanými na stejné stránce (bez tmavých vrstev, které přepisují záměrně)';
$md[] = 'Pořadí kaskády rozhoduje, která hodnota platí – při sloučení ve fázi F se musí zachovat výsledná hodnota (ověřit v prohlížeči). Nejvýše 3 ukázky na dvojici.';
$md[] = '';
$md[] = '| Dvojice | Konfliktních vlastností |';
$md[] = '|---|---:|';
foreach ($conflictPairs as $k => $n) if ($coLoaded($k) && !str_contains($k, 'dark')) $md[] = '| ' . $k . ' | ' . $n . ' |';
$md[] = '';
$md[] = '| Selektor (kontext) | Vlastnost | Soubor A | Hodnota A | Soubor B | Hodnota B |';
$md[] = '|---|---|---|---|---|---|';
$cell = static fn(string $v): string => str_replace('|', '\|', mb_strimwidth($v, 0, 60, '…'));
$shown = 0;
foreach ($conflicts as $pk => $list) { if (!$coLoaded($pk) || str_contains($pk, 'dark') || $shown >= 30) continue; foreach ($list as [$k, $p, $a, $va, $b, $vb]) { $shown++; $md[] = '| `' . $cell(trim(str_replace('|', ' ⟂ ', $k), ' ⟂')) . '` | ' . $p . ' | `' . $a . '` | ' . $cell($va) . ' | `' . $b . '` | ' . $cell($vb) . ' |'; } }
$md[] = '';
$md[] = '## Návrh sloučení pro fázi F (nic se teď nevyřazuje)';
$md[] = '1. **Tokeny → `tokens-v78.css`.** `tokens-v61.css` (`--ui-*`), `tokens-palette-v68.css` / `tokens-palette-dark-v68.css` (paleta `--k*`) a `tokens-dark-v67.css` dostanou aliasy na sémantické tokeny v78 (`--ui-ink: var(--text)` …); po převodu stránek se vyřadí. Tmavé vrstvy `teacher-dark-v68.css` a `cockpit-dark-v68.css` zaniknou, protože tokeny v78 mají tmavý režim v sobě.';
$md[] = '2. **Cockpit učitele → jedna vrstva nad `components-v78.css`.** `teacher-shell-v68.css`, `teacher-contrast-v69.css` a `cockpit-v70.css` … `cockpit-v77.css` (rozvržení záložek v70–v77) sloučit do jednoho souboru rámce a komponent; kontrast v69 už řeší tokeny.';
$md[] = '3. **Odvozené kopie `cx-*` (největší úspora).** Cockpit dnes načítá tokenizované kopie žákovských vrstev (`cx-app.css` ~400 kB, `cx-ui-v51.css`, `cx-brand-v54.css` …) jen kvůli starým třídám. Až záložky cockpitu přejdou na komponenty v78, kopie i generátor `tools/v68_tokenize_css.php --derive` se vyřadí.';
$md[] = '4. **Žák.** `app.css` (~400 kB), `ui-v51.css`, `student-ui-v50-7-7.css`, `student-v55.css` a generované tmavé vrstvy `dark/student-dark-*-v69.css` nahradit vrstvou nad tokeny v78 po pohledech (Domů, Dnes, Úloha, Profil); tmavé vrstvy v69 pak nejsou potřeba. Vyřazovat po jednom pohledu s kontrolou v prohlížeči (390/1280 px, oba režimy).';
$md[] = '5. **Tisk a projektor.** `print-v75.css`, `print-v76.css`, `lesson-print-v73.css`, `assessment-v66-print.css` a `lesson-projector-v73.css` sjednotit na tokeny v78 (tisk vždy světlý) – funkce beze změny.';
$md[] = '6. **Moduly (Lab, Aréna, roboti, hry, účty).** Ponechat jako samostatné soubory, jen nahradit pevné barvy tokeny; sloučit až při další práci na modulu.';
$md[] = '7. **Nenačítané soubory:** ' . ($unused === [] ? 'žádné (kopie `cx-*` načítá `teacher68_css_href()`, tmavé vrstvy v69 `ui67_dark_link_html()` dynamicky).' : 'ověřit v prohlížeči a vyřadit jako první (nejnižší riziko).');
$md[] = '';
$md[] = 'Postup vyřazení (fáze F): vlastní PHP skript pro jeden soubor – kopie mimo projekt → ověření SHA-256 kopie → unlink originálu; nikdy shellová smyčka. Záznam (soubor, bajty, SHA-256) do manifestu vydání.';
$out = implode("\n", $md) . "\n";
if (isset($opts['json'])) file_put_contents((string)$opts['json'], json_encode(['stats' => $stats, 'conflict_pairs' => $conflictPairs, 'shared_pairs' => array_slice($pairs, 0, 50, true)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
if (isset($opts['out'])) {
    $target = (string)$opts['out'];
    $target = preg_match('~^([a-z]:)?/~i', $target) === 1 ? $target : $ROOT . '/' . $target;
    file_put_contents($target, $out);
    fwrite(STDOUT, 'V78_CSS_INVENTORY files=' . count($stats) . ' bytes=' . $tot['bytes'] . ' shared_selectors=' . count($multi) . ' conflicts=' . array_sum($conflictPairs) . ' unused=' . count($unused) . PHP_EOL);
} else {
    fwrite(STDOUT, $out);
}
