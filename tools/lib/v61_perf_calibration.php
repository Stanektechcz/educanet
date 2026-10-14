<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · kalibrace měření výkonu (sdílí tools/v61_perf_audit.php, tools/v67_perf_audit.php a tools/lib/v61_perf_worker.php).
 *
 * Problém: časové limity v61 (žák ≤ 60 ms, učitel ≤ 150 ms, s tolerancí šumu 25 %) jsou v absolutních ms. Na stroji vytíženém
 * jinými procesy (prohlížeče, node, antivirus) se zpomalí každý běh PHP – audit padal, i když se kód nezměnil (v79.1–v80.1).
 *
 * Řešení – pevná **referenční stránka** ve stejném procesu: každý měřicí proces hned po vykreslení měřené stránky načte
 * V61_REF_PAGE_FILES vygenerovaných knihoven PHP přes opcache (jako aplikace), přečte V61_REF_PAGE_DATA souborů ve formátu
 * úložiště, vykreslí HTML a přidá pevnou práci CPU ({@see v61_ref_measure()}). Obsah reference je deterministický a nezávisí
 * na kódu aplikace, takže její čas `ref_ms` říká jen, jak rychlý je stroj právě teď (CPU, disk, antivirus, opcache):
 *   - zpomalení stroje  s = medián(ref_ms) / V61_REF_NOMINAL_MS, nikdy < 1 (na rychlém stroji limity beze změny), nejvýš
 *     V61_SLOWNESS_MAX – při větším přetížení se měření neuznává a kontrola selže s jasným důvodem,
 *   - normalizovaný čas stránky norm_ms = ms / s (≈ čas na nevytíženém referenčním stroji) se porovná s nezměněnými limity,
 *   - poměr ratio = medián(ms) / medián(ref_ms) se porovná s uloženou základnou (tools/lib/v61_perf_baseline.php):
 *     zhoršení o víc než V61_REGRESSION_TOLERANCE je regrese i u stránky hluboko pod absolutním limitem.
 * Umělé zpomalení samotestu je **práce CPU** v ms referenčního stroje ({@see v61_ref_units_for_ms()}), takže se na vytíženém
 * stroji zpomalí stejně jako skutečný kód (spánek by normalizace zmenšila).
 */

/** Jednotek práce CPU, když se reference měří bez připraveného adresáře (jen CPU, ≈ 17 ms na referenčním stroji). */
const V61_REF_UNITS = 20;
/** Čas jedné jednotky práce CPU (v61_ref_work(1)) na referenčním stroji v ms (14. 10. 2026: 20 jednotek = 16,6–18 ms). */
const V61_REF_UNIT_MS = 0.85;
/**
 * Čas referenční stránky na referenčním stroji (vývojový stroj školy, PHP 8.4 + opcache v souborové cache, Windows 10),
 * změřeno 14. 10. 2026 po stránce ve stejném procesu: p10 ≈ 28 ms při 17–28 % CPU (medián 29–37 ms; při 48 % CPU p10 34 ms,
 * při 100 % CPU medián 46–55 ms). Na rychlejším stroji je s = 1 a limity platí beze změny.
 */
const V61_REF_NOMINAL_MS = 28.0;
/** Nejvyšší uznané zpomalení stroje; nad ním se měření času neuznává (stroj je přetížený – zopakovat později). */
const V61_SLOWNESS_MAX = 3.0;
/** Povolené zhoršení poměru stránka / reference proti uložené základně (umělá regrese +30 % musí vždy selhat). */
const V61_REGRESSION_TOLERANCE = 0.20;

/**
 * Jedna nebo víc jednotek pevné práce CPU (≈ V61_REF_UNIT_MS na referenčním stroji). Deterministická, bez I/O a bez sítě.
 * Vrací kontrolní součet, aby ji optimalizace nemohla vynechat.
 */
function v61_ref_work(int $units): int
{
    $acc = 0;
    for ($u = 0; $u < $units; $u++) {
        $rows = [];
        for ($i = 0; $i < 320; $i++) {
            $rows[] = ['id' => 's' . $i, 'name' => str_repeat('ab', ($i % 7) + 1) . $i, 'xp' => ($i * 37 + $u) % 101, 'tags' => ['a' . ($i % 5), 'b' . ($i % 3)]];
        }
        usort($rows, static fn(array $a, array $b): int => ($a['xp'] <=> $b['xp']) ?: strcmp($a['id'], $b['id']));
        $back = json_decode((string)json_encode($rows), true);
        $html = '';
        foreach ($back as $r) $html .= '<li data-x="' . $r['xp'] . '">' . htmlspecialchars($r['name'] . ' & ' . $r['id'], ENT_QUOTES) . '</li>';
        $acc += (int)preg_match_all('/<li data-x="(\d+)">([a-z]+)/', $html) + strlen(md5($html)) + count(array_filter($back, static fn(array $r): bool => $r['xp'] > 50));
    }
    return $acc;
}

/** Počet generovaných knihoven a datových souborů referenční stránky (stránka aplikace načítá 130–160 souborů a ~10–30× úložiště). */
const V61_REF_PAGE_FILES = 140;
const V61_REF_PAGE_DATA = 16;

/**
 * Připraví pevnou referenční „stránku“ do $dir: V61_REF_PAGE_FILES knihoven PHP (~13 kB, funkce + pole textů) a
 * V61_REF_PAGE_DATA souborů dat ve formátu úložiště (ochranný první řádek + JSON). Obsah je deterministický – stejný v každém běhu
 * i vydání, takže čas reference se mění jen se strojem (CPU, disk, antivirus, opcache), ne s kódem aplikace.
 */
function v61_ref_prepare(string $dir): bool
{
    if (!is_dir($dir) && !@mkdir($dir, 0700, true)) return false;
    $words = ['žák', 'úloha', 'hodina', 'příprava', 'výsledek', 'kompetence', 'opakování', 'třída', 'cvičení', 'zpětná vazba', 'materiál', 'projekt'];
    for ($f = 0; $f < V61_REF_PAGE_FILES; $f++) {
        $texts = [];
        for ($i = 0; $i < 60; $i++) {
            $s = '';
            for ($w = 0; $w < 24; $w++) $s .= $words[($f * 7 + $i * 3 + $w) % count($words)] . ' ';
            $texts[] = trim($s) . ' #' . $f . '-' . $i;
        }
        $fn = sprintf('v61ref_f%03d', $f);
        $code = "<?php\n\ndeclare(strict_types=1);\n\nif (!function_exists('" . $fn . "')) {\n    function " . $fn . "(array \$rows): string\n    {\n        \$o = '';\n"
            . "        foreach (\$rows as \$k => \$v) \$o .= '<li data-k=\"' . htmlspecialchars((string)\$k, ENT_QUOTES) . '\">' . htmlspecialchars(mb_substr((string)\$v, 0, 80), ENT_QUOTES) . '</li>';\n"
            . "        return \$o;\n    }\n}\n\nreturn " . var_export(['id' => $f, 'texts' => $texts], true) . ";\n";
        $path = sprintf('%s/ref_%03d.php', $dir, $f);
        if (!is_file($path) || (string)file_get_contents($path) !== $code) file_put_contents($path, $code);
    }
    for ($d = 0; $d < V61_REF_PAGE_DATA; $d++) {
        $rows = [];
        for ($i = 0; $i < 40; $i++) $rows['k' . $d . '_' . $i] = ['xp' => ($d * 31 + $i * 7) % 211, 'label' => $words[($d + $i) % count($words)], 'at' => '2026-10-' . sprintf('%02d', 1 + $i % 28)];
        $data = "<?php http_response_code(403); exit; ?>\n" . json_encode($rows, JSON_UNESCAPED_UNICODE);
        $path = sprintf('%s/data_%02d.json.php', $dir, $d);
        if (!is_file($path) || (string)file_get_contents($path) !== $data) file_put_contents($path, $data);
    }
    return is_file(sprintf('%s/ref_%03d.php', $dir, V61_REF_PAGE_FILES - 1));
}

/**
 * Změří referenční zátěž v ms: s $dir (připraveným přes v61_ref_prepare) načte knihovny (přes opcache jako aplikace),
 * přečte data ve formátu úložiště, vykreslí HTML a přidá pevnou práci CPU; bez $dir jen práci CPU.
 */
function v61_ref_measure(?string $dir = null): float
{
    $t = hrtime(true);
    if ($dir !== null && $dir !== '' && is_dir($dir)) {
        $html = '';
        for ($f = 0; $f < V61_REF_PAGE_FILES; $f++) {
            $lib = include sprintf('%s/ref_%03d.php', $dir, $f);
            $fn = sprintf('v61ref_f%03d', $f);
            $html .= $fn(array_slice((array)($lib['texts'] ?? []), 0, 6));
        }
        $sum = 0;
        for ($d = 0; $d < V61_REF_PAGE_DATA; $d++) {
            $raw = (string)@file_get_contents(sprintf('%s/data_%02d.json.php', $dir, $d));
            $rows = json_decode((string)substr($raw, (int)strpos($raw, "\n") + 1), true);
            foreach ((array)$rows as $r) $sum += (int)($r['xp'] ?? 0);
        }
        $GLOBALS['v61_ref_sink'] = strlen($html) + $sum;
        v61_ref_work(intdiv(V61_REF_UNITS, 2));
    } else {
        v61_ref_work(V61_REF_UNITS);
    }
    return (hrtime(true) - $t) / 1e6;
}

/** Kolik jednotek práce CPU odpovídá $ms milisekundám na referenčním stroji (umělé zpomalení samotestu). */
function v61_ref_units_for_ms(float $ms): int
{
    return max(0, (int)round($ms / V61_REF_UNIT_MS));
}

/**
 * Zpomalení stroje z naměřených referenčních časů (medián). Vrací ['raw' => skutečný poměr, 'factor' => použitý faktor ≥ 1, 'ok' => uznatelné].
 *
 * @param list<float> $refMs
 * @return array{raw:float,factor:float,ok:bool}
 */
function v61_slowness(array $refMs): array
{
    $vals = array_values(array_filter(array_map('floatval', $refMs), static fn(float $v): bool => $v > 0));
    if ($vals === []) return ['raw' => 0.0, 'factor' => 1.0, 'ok' => false];
    sort($vals);
    $n = count($vals);
    $med = $n % 2 === 1 ? $vals[intdiv($n, 2)] : ($vals[$n / 2 - 1] + $vals[$n / 2]) / 2;
    $raw = $med / V61_REF_NOMINAL_MS;
    return ['raw' => round($raw, 3), 'factor' => round(max(1.0, min($raw, V61_SLOWNESS_MAX)), 3), 'ok' => $raw <= V61_SLOWNESS_MAX];
}

/** Je poměr $ratio regresí proti základně $base? (základna ≤ 0 = neznámá → nic se netvrdí) */
function v61_is_regression(float $ratio, float $base): bool
{
    return $base > 0 && $ratio > $base * (1 + V61_REGRESSION_TOLERANCE);
}

/**
 * Otisk měřicího stroje pro základnu poměrů (poměr stránka / reference se mezi různými CPU, disky a verzemi PHP liší).
 * Jen hash – název počítače se do repozitáře neukládá.
 */
function v61_machine_id(): string
{
    return substr(hash('sha256', php_uname('n') . '|' . php_uname('m') . '|' . PHP_OS_FAMILY . '|' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION), 0, 12);
}

/**
 * Uložená základna poměrů (tools/lib/v61_perf_baseline.php, vytváří `php tools/v61_perf_audit.php --update-baseline`).
 * @return array{machine:string,created:string,pages:array<string,float>}
 */
function v61_perf_baseline(): array
{
    $file = __DIR__ . '/v61_perf_baseline.php';
    $data = is_file($file) ? require $file : null;
    if (!is_array($data) || !is_array($data['pages'] ?? null)) return ['machine' => '', 'created' => '', 'pages' => []];
    return ['machine' => (string)($data['machine'] ?? ''), 'created' => (string)($data['created'] ?? ''), 'pages' => array_map('floatval', $data['pages'])];
}

/** Zapíše základnu poměrů (id stránky => poměr) pro tento stroj. */
function v61_perf_write_baseline(array $pages, string $note): bool
{
    ksort($pages);
    $body = "<?php\n\ndeclare(strict_types=1);\nif (basename((string)(\$_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }\n\n"
        . "/**\n * EDUCANET v80.2 · základna poměrů ms stránky / ms referenční stránky pro audit v61 (regrese > "
        . (int)(V61_REGRESSION_TOLERANCE * 100) . " % = FAIL).\n * Generováno: php tools/v61_perf_audit.php --update-baseline (" . str_replace('*/', '', $note) . ").\n"
        . " * Platí jen pro stroj s otiskem 'machine'; na jiném stroji se porovnání se základnou jen vypíše.\n */\n\nreturn "
        . var_export(['machine' => v61_machine_id(), 'created' => date('Y-m-d'), 'pages' => array_map(static fn($v): float => round((float)$v, 3), $pages)], true) . ";\n";
    return file_put_contents(__DIR__ . '/v61_perf_baseline.php', $body) !== false;
}
