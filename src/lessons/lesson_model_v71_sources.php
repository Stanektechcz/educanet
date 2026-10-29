<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v71 · mapa zdrojů obsahu lekcí a jejich precedence (jen čtení, bez výstupu).
 *
 * Jediné místo, které ví, odkud se berou lekce 1–28 všech tříd. Čte ho lesson_model_v71.php (lm71_*),
 * tím i Dnešní hodina, Režim hodiny a Plán a kurikulum (teacher_curriculum_lessons()) – konec paralelních loaderů.
 *
 * Precedence (vyšší vyhrává):
 *   1. overlay obsahové stopy `lesson_content_v7[2-9]_*.php` (pole po poli, pozdější soubor podle názvu vyhrává),
 *   2. zdroj, který lekci deklaruje jako první v pořadí lm71_sources() (stejně jako v56_lesson_bundle pro žáka),
 *   3. další výskyt stejného čísla lekce je „konflikt zdrojů“ – nepoužije se, jen ho hlásí report.
 * Overlay v71 mění jen to, co vidí učitel (plán, poznámky, kritéria…); žákovská posloupnost (témata, kroky, test)
 * zůstává z původních zdrojů, dokud ji neschválí učitel (fáze B, v72).
 */

const LM71_LESSONS = 28;
const LM71_CLASSES = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
const LM71_OVERLAY_GLOB = 'src/content/lesson_content_v7[2-9]_*.php';
const LM77_OVERLAY_LIST_SETTLE = 2;   // s – seznam overlaye z cache jen když se kořen projektu naposledy změnil dřív (v77, výkon)
/** Verze tvaru cache a modelu – zvýšit při změně struktury (stará cache se tím zneplatní). */
const LM71_MODEL_VERSION = 2;   // v72: overlay nese i `glossary` a `assessment.formative`

/**
 * Zdroje lekcí v pořadí precedence. `shape`: primary = lekce 1 z v56_primary_lesson(), single = jedna lekce na třídu
 * (číslo doplní `lessons[0]`), list = seznam lekcí s polem `number`. `template` = obsah známý jako generovaný.
 * @return array<string, array{file:string,lessons:array{0:int,1:int},shape:string,label:string,template?:bool}>
 */
function lm71_sources(): array
{
    return [
        'primary' => ['file' => 'src/lessons/learning_v56.php', 'lessons' => [1, 1], 'shape' => 'primary', 'label' => 'Úvodní blok (v56_primary_lesson + modules.php / first_year.php)'],
        'next' => ['file' => 'src/content/next_lessons.php', 'lessons' => [2, 2], 'shape' => 'single', 'label' => 'Lekce 2'],
        'extended' => ['file' => 'src/content/extended_lessons.php', 'lessons' => [3, 4], 'shape' => 'list', 'label' => 'Lekce 3–4'],
        'plus' => ['file' => 'src/lessons/lessons_plus.php', 'lessons' => [5, 6], 'shape' => 'list', 'label' => 'Lekce 5–6'],
        'more' => ['file' => 'src/lessons/lessons_more.php', 'lessons' => [7, 7], 'shape' => 'list', 'label' => 'Lekce 7'],
        'ecosystem' => ['file' => 'src/lessons/lessons_ecosystem.php', 'lessons' => [8, 9], 'shape' => 'list', 'label' => 'Lekce 8–9'],
        'yearpack' => ['file' => 'src/lessons/lessons_yearpack.php', 'lessons' => [10, 18], 'shape' => 'list', 'label' => 'Lekce 10–18'],
        'v30' => ['file' => 'src/lessons/lessons_v30.php', 'lessons' => [19, 28], 'shape' => 'list', 'label' => 'Lekce 19–28 (generováno)', 'template' => true],
    ];
}

/**
 * Soubory overlaye obsahové stopy (relativní cesty, seřazené podle názvu). @return list<string>
 * v77 (výkon): glob prochází src/content při každém
 * požadavku s modelem lekce. Seznam se proto pamatuje v cache modelu (JSON) spolu s časem změny adresáře obsahu –
 * přidání, smazání i přejmenování souboru čas adresáře změní a seznam se sestaví znovu globem. Seznam se ukládá a používá jen
 * tehdy, když je změna adresáře starší než LM77_OVERLAY_LIST_SETTLE s (filemtime má rozlišení 1 s). Bez zapisovatelné cache = glob.
 */
function lm71_overlay_files(): array
{
    static $memo = null;   // jednou za požadavek
    if ($memo !== null) return $memo;
    clearstatcache(true, dirname(__DIR__, 2) . '/src/content');
    $dirMtime = @filemtime(dirname(__DIR__, 2) . '/src/content');
    $settled = $dirMtime !== false && time() - $dirMtime >= LM77_OVERLAY_LIST_SETTLE;
    $listFile = function_exists('lm71_cache_dir') ? lm71_cache_dir() . '/_overlay_files.json' : '';
    if ($settled && $listFile !== '' && is_file($listFile)) {
        $cached = json_decode((string)@file_get_contents($listFile), true);
        if (is_array($cached) && ($cached['dir_mtime'] ?? null) === $dirMtime && ($cached['root'] ?? null) === md5(dirname(__DIR__, 2)) && is_array($cached['files'] ?? null)) {
            $list = array_values(array_filter($cached['files'], static fn($b): bool => is_string($b) && preg_match('~^src/content/lesson_content_v7[2-9]_[a-z0-9_]{1,40}\.php$~D', $b) === 1));
            if (count($list) === count($cached['files'])) return $memo = $list;
        }
    }
    $files = glob(dirname(__DIR__, 2) . '/' . LM71_OVERLAY_GLOB) ?: [];
    $out = [];
    foreach ($files as $f) {
        $base = basename($f);
        if (preg_match('/^lesson_content_v7[2-9]_[a-z0-9_]{1,40}\.php$/D', $base) === 1) $out[] = 'src/content/' . $base;
    }
    sort($out, SORT_STRING);
    if ($settled && $listFile !== '' && is_dir(dirname($listFile))) {
        $tmp = $listFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($tmp, (string)json_encode(['dir_mtime' => $dirMtime, 'root' => md5(dirname(__DIR__, 2)), 'files' => $out]), LOCK_EX) === false || !@rename($tmp, $listFile)) @unlink($tmp);
    }
    return $memo = $out;
}

/** Soubory, ze kterých vzniká surová vrstva modelu (a tedy klíč jeho cache). @return list<string> */
function lm71_model_files(): array
{
    $files = ['src/lessons/lesson_model_v71_sources.php'];
    foreach (lm71_sources() as $src) if ($src['shape'] !== 'primary') $files[] = $src['file'];
    return array_merge($files, lm71_overlay_files());
}

/**
 * Soubory, ze kterých tools/build_runtime_cache.php skládá cache/runtime (žákovský obsah).
 * Audit v71 hlídá, že se seznam shoduje se soubory, které builder opravdu načítá.
 * @return list<string>
 */
function lm71_runtime_cache_files(): array
{
    return [
        'src/knowledge/knowledge_tours.php', 'src/knowledge/knowledge_tours_next.php', 'src/knowledge/knowledge_tours_plus.php', 'src/knowledge/knowledge_tours_more.php', 'src/knowledge/knowledge_tours_ecosystem.php',
        'src/knowledge/knowledge_tours_yearpack.php', 'src/knowledge/knowledge_tours_v30.php',
        'src/content/next_lessons.php', 'src/content/extended_lessons.php', 'src/lessons/lessons_plus.php', 'src/lessons/lessons_more.php', 'src/lessons/lessons_ecosystem.php', 'src/lessons/lessons_yearpack.php', 'src/lessons/lessons_v30.php',
        'src/content/simulations.php', 'src/content/simulations_extra.php', 'src/content/simulations_plus.php', 'src/content/simulations_more.php', 'src/content/simulations_ecosystem.php', 'src/content/simulations_yearpack.php',
        'src/content/learning_resources.php',
    ];
}

/** Levný podpis souborů (velikost + čas změny) – rychlá cesta při čtení cache. */
function lm71_files_signature(array $relFiles): string
{
    static $memo = [];   // v77: stejný seznam souborů pro všechny třídy – stat() jednou za požadavek, ne za každou třídu
    $key = implode('|', $relFiles);
    if (isset($memo[$key])) return $memo[$key];
    $parts = [];
    foreach ($relFiles as $rel) {
        $st = @stat(dirname(__DIR__, 2) . '/' . $rel);   // jedno volání stat() na soubor (výkon: běží při každém zobrazení)
        $parts[] = $rel . '|' . (is_array($st) ? $st['size'] . '|' . $st['mtime'] : 'missing');
    }
    return $memo[$key] = sha1(LM71_MODEL_VERSION . "\n" . implode("\n", $parts));
}

/** Obsahový hash souborů (SHA-256 obsahu) – platí i po git checkoutu, kdy se změní časy souborů. */
function lm71_files_hash(array $relFiles): string
{
    $parts = [];
    foreach ($relFiles as $rel) {
        $path = dirname(__DIR__, 2) . '/' . $rel;
        $parts[] = $rel . '|' . (is_file($path) ? (string)hash_file('sha256', $path) : 'missing');
    }
    return hash('sha256', LM71_MODEL_VERSION . "\n" . implode("\n", $parts));
}

/** Zdroj, který lekci deklaruje (podle rozsahu), nebo ''. */
function lm71_source_for_number(int $number): string
{
    foreach (lm71_sources() as $id => $src) {
        if ($number >= $src['lessons'][0] && $number <= $src['lessons'][1]) return $id;
    }
    return '';
}
