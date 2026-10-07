<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v71 · report kvality obsahu lekcí a materiálů (CLI nástroj, NE audit – nikdy není v run_audits).
 *
 *   php tools/v71_content_report.php --out=<adresář mimo projekt> [--storage=<cesta k úložišti>]
 *
 * Jen čtení: obsah lekcí čte přes jednotný model lm71 (cache modelu jde do dočasného adresáře, ne do projektu),
 * aplikace startuje nad prázdným dočasným úložištěm (ostrá storage/ se neotevře). Volitelné --storage se jen čte
 * (videa učitelů v42 – počet a metadata). Výstup: content-report-<čas>.json + .txt do --out, souhrn na stdout.
 * Metriky: úplnost (12 polí modelu), konflikty a duplicity, šablony, anglické titulky, rozložení `correct`,
 * témata bez materiálu, chybějící ŠVP, materiály bez metadat, šablonové MD v materials/, karty dnů, T-14, stav cache.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/report_v71.php';

$storageArg = rp71_arg($argv, 'storage');
if ($storageArg !== null && (!is_dir($storageArg) || (glob(rtrim($storageArg, '/\\') . '/*.json.php') ?: []) === [])) rp71_fail('--storage neukazuje na úložiště EDUCANET.');
$outDir = rp71_out_dir(rp71_arg($argv, 'out'), $ROOT, $storageArg !== null ? [$storageArg] : [$ROOT . '/storage']);
$storageBefore = $storageArg !== null ? rp71_tree_hash($storageArg) : '';
$tmp = rp71_temp_dir('content');
mkdir($tmp . '/storage', 0700);
putenv('EDUCANET_STORAGE_DIR=' . $tmp . '/storage');
$_ENV['EDUCANET_STORAGE_DIR'] = $tmp . '/storage';
putenv('EDUCANET_LM71_CACHE_DIR=' . $tmp . '/lesson_model');

require_once $ROOT . '/bootstrap.php';
foreach (['tutorial_v52.php', 'learning_v56.php', 'lesson_model_v71.php', 'calendar_days_v71.php'] as $lib) require_once $ROOT . '/' . $lib;

$year = require $ROOT . '/school_year.php';
$lessons = [];
foreach (LM71_CLASSES as $c) foreach (lm71_lessons($c) as $n => $l) $lessons[$c][$n] = $l;
$all = array_merge(...array_values(array_map('array_values', $lessons)));
$total = count($all);

// ------------------------------------------------------------------ 1) úplnost
$fields = lm71_completeness_fields();
$coverage = array_fill_keys(array_keys($fields), 0);
$histogram = array_fill(0, count($fields) + 1, 0);
$byClass = [];
foreach ($lessons as $c => $rows) {
    $byClass[$c] = ['lekce' => count($rows), 'uplne' => 0, 'prumer' => 0.0];
    foreach ($rows as $l) {
        $cp = $l['completeness'];
        $histogram[$cp['score']]++;
        $byClass[$c]['uplne'] += $cp['complete'] ? 1 : 0;
        $byClass[$c]['prumer'] += $cp['score'] / max(1, count($rows));
        foreach ($cp['checks'] as $k => $ok) $coverage[$k] += $ok ? 1 : 0;
    }
    $byClass[$c]['prumer'] = round($byClass[$c]['prumer'], 2);
}
$complete = (int)array_sum(array_column($byClass, 'uplne'));
$avgScore = round(array_sum(array_map(static fn(array $l): int => $l['completeness']['score'], $all)) / max(1, $total), 2);

// ------------------------------------------------------------------ 2) konflikty zdrojů, duplicity
$conflicts = [];
foreach (LM71_CLASSES as $c) foreach (lm71_raw($c)['conflicts'] as $x) $conflicts[] = $c . ' L' . (int)$x['number'] . ' (' . $x['type'] . ', ' . $x['source'] . ')';
$dupTitles = 0;
$dupQuestions = ['texty' => 0, 'polozky' => 0];
$exitIsQuiz = 0;
$correct = ['L2_28' => [], 'L3_28' => []];
$quizTotal = 0;
foreach ($lessons as $c => $rows) {
    $titles = array_count_values(array_map(static fn(array $l): string => lm71_norm_text($l['title']), $rows));
    $dupTitles += count(array_filter($titles, static fn(int $n): bool => $n > 1));
    $qLessons = [];
    foreach ($rows as $n => $l) {
        $qs = array_values(array_unique(array_map(static fn(array $q): string => lm71_norm_text($q['question']), $l['assessment']['checks'])));
        foreach ($qs as $q) $qLessons[$q] = ($qLessons[$q] ?? 0) + 1;
        foreach ($l['assessment']['checks'] as $q) {
            $quizTotal++;
            $correct['L2_28'][$q['correct']] = ($correct['L2_28'][$q['correct']] ?? 0) + 1;
            if ($n >= 3) $correct['L3_28'][$q['correct']] = ($correct['L3_28'][$q['correct']] ?? 0) + 1;
        }
        $quizOnly = array_values(array_filter($l['assessment']['checks'], static fn(array $q): bool => $q['step'] !== 'exit'));
        $quizTexts = array_map(static fn(array $q): string => lm71_norm_text($q['question']), $quizOnly);
        foreach ($l['exit_ticket'] as $e) if (in_array(lm71_norm_text($e), $quizTexts, true)) { $exitIsQuiz++; break; }
    }
    foreach ($qLessons as $count) if ($count > 1) { $dupQuestions['texty']++; $dupQuestions['polozky'] += $count; }
}
foreach ($correct as &$dist) ksort($dist);
unset($dist);

// ------------------------------------------------------------------ 3) šablony a anglické titulky
$templateTasks = 0;
$tasksTotal = 0;
$templateTexts = [];
$skeletons = [];
$english = [];
$templateSourceLessons = 0;
foreach ($all as $l) {
    foreach ($l['tasks'] as $t) {
        $tasksTotal++;
        if ($t['template']) { $templateTasks++; $templateTexts[$l['class_id'] . '|' . lm71_norm_text($t['text'])] = true; }
    }
    if ($l['timeline'] !== [] && empty($l['timeline'][0]['derived'])) {
        $sk = implode('>', array_map(static fn(array $s): string => lm71_norm_text($s['phase']), $l['timeline']));
        $skeletons[$sk] = ($skeletons[$sk] ?? 0) + 1;
    }
    if (lm71_is_english_title($l['title'])) $english[] = $l['id'];
    $templateSourceLessons += $l['meta']['template'] ? 1 : 0;
}
$skeletonLessons = array_sum(array_filter($skeletons, static fn(int $n): bool => $n >= LM71_TEMPLATE_MIN_LESSONS));

// ------------------------------------------------------------------ 4) materiály a témata
$resources = require $ROOT . '/learning_resources.php';
$resTotal = 0;
$resUrls = [];
$resNoMeta = 0;
foreach ((array)$resources as $c => $map) foreach ((array)$map as $topic => $list) foreach ((array)$list as $r) {
    if (!is_array($r)) continue;
    $resTotal++;
    $resUrls[(string)($r['url'] ?? '')] = true;
    if (empty($r['checked_at']) || empty($r['license'])) $resNoMeta++;
}
$topicsNoMaterial = [];
$lessonsNoMaterial = 0;
foreach ($lessons as $c => $rows) {
    $topics = [];
    foreach ($rows as $l) {
        foreach ($l['topics'] as $t) $topics[$t['key']] = true;
        $lessonsNoMaterial += $l['materials'] === [] ? 1 : 0;
    }
    $missing = array_values(array_filter(array_keys($topics), static fn(string $t): bool => empty($resources[$c][$t])));
    $topicsNoMaterial[$c] = ['temat' => count($topics), 'bez_materialu' => count($missing)];
}
$svpMissing = count(array_filter($all, static fn(array $l): bool => empty($l['curriculum']['svp'])));
$teacherVideos = null;
if ($storageArg !== null) {
    $raw = (string)@file_get_contents(rtrim($storageArg, '/\\') . '/adaptive_lesson_resources.json.php');
    $data = json_decode((string)preg_replace('/^<\?php[^\n]*\n/', '', $raw), true);
    $rows = is_array($data) ? array_filter($data, 'is_array') : [];
    $teacherVideos = ['celkem' => count($rows), 'bez_metadat' => count(array_filter($rows, static fn(array $r): bool => empty($r['checked_at']) || empty($r['license']))),
        'http_bez_tls' => count(array_filter($rows, static fn(array $r): bool => str_starts_with(strtolower((string)($r['url'] ?? '')), 'http://')))];
}

// ------------------------------------------------------------------ 5) šablonové MD v materials/
$mdFamilies = [];
foreach (['lesson_kits', 'visual_simulations', 'learning_studios', 'cognitive_labs'] as $family) {
    $files = glob($ROOT . '/materials/' . $family . '/class_*/*.md') ?: [];
    $lineFiles = [];
    $perFile = [];
    $aiPrompts = 0;
    $dupQ = 0;
    foreach ($files as $f) {
        $text = (string)file_get_contents($f);
        if (preg_match('/^#+\s.*AI prompt/miu', $text) === 1) $aiPrompts++;
        $lines = [];
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $norm = lm71_norm_text((string)preg_replace('/\d+/', '#', (string)$line));
            if (mb_strlen($norm) >= 12) $lines[$norm] = true;
        }
        $qCounts = [];
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $q = lm71_norm_text((string)preg_replace('/^[-*#>\d.\s]+/u', '', (string)$line));
            if ($q !== '' && str_ends_with($q, '?')) $qCounts[$q] = ($qCounts[$q] ?? 0) + 1;
        }
        if (array_filter($qCounts, static fn(int $n): bool => $n > 1) !== []) $dupQ++;
        $perFile[$f] = array_keys($lines);
        foreach ($lines as $norm => $_) $lineFiles[$norm] = ($lineFiles[$norm] ?? 0) + 1;
    }
    $threshold = max(2, (int)ceil(count($files) / 2));
    $shares = [];
    foreach ($perFile as $lines) {
        if ($lines === []) continue;
        $shared = count(array_filter($lines, static fn(string $l): bool => ($lineFiles[$l] ?? 0) >= $threshold));
        $shares[] = $shared / count($lines);
    }
    $mdFamilies[$family] = ['souboru' => count($files), 'sablonovy_podil_radku' => $shares !== [] ? round(array_sum($shares) / count($shares) * 100, 1) : 0.0,
        'se_sekci_ai_prompty' => $aiPrompts, 's_duplicitni_otazkou' => $dupQ];
}

// ------------------------------------------------------------------ 6) dny bez lekce, T-14, cache
$calendarDays = cd71_calendar_days($year);
$dayCards = 0;
foreach (LM71_CLASSES as $c) foreach ($calendarDays as $row) $dayCards += lm71_day($c, $row)['flow'] !== [] ? 1 : 0;
$today = date('Y-m-d');
$horizon = date('Y-m-d', strtotime($today . ' +14 days'));
$t14 = ['lekci' => 0, 'navrh' => 0];
foreach (LM71_CLASSES as $c) foreach ((array)$year['calendar'] as $row) {
    $n = (int)($row['lesson_number'] ?? 0);
    if ($n < 1 || (string)$row['date'] < $today || (string)$row['date'] > $horizon) continue;
    $t14['lekci']++;
    $t14['navrh'] += $lessons[$c][$n]['meta']['status'] === 'navrh' ? 1 : 0;
}
$cache = lm71_cache_status($ROOT . '/cache/lesson_model');

// ------------------------------------------------------------------ výstup
$report = [
    'report' => 'v71_content_report', 'vytvoreno' => date(DATE_ATOM), 'model_verze' => LM71_MODEL_VERSION,
    'lekce' => $total, 'uplnost' => ['uplne_lekce' => $complete, 'uplne_procent' => $total > 0 ? round($complete / $total * 100, 1) : 0, 'prumer_poli' => $avgScore,
        'z_poli' => count($fields), 'histogram_skore' => $histogram, 'pokryti_poli' => $coverage, 'tridy' => $byClass],
    'konflikty_zdroju' => $conflicts, 'duplicitni_titulky' => $dupTitles, 'duplicitni_kvizove_otazky' => $dupQuestions, 'exit_ticket_shodny_s_kvizem' => $exitIsQuiz,
    'sablony' => ['ukolu_celkem' => $tasksTotal, 'sablonovych_ukolu' => $templateTasks, 'unikatnich_sablonovych_textu' => count($templateTexts),
        'lekci_se_sablonovou_kostrou_planu' => $skeletonLessons, 'lekci_z_generovaneho_zdroje' => $templateSourceLessons],
    'anglicke_titulky' => ['pocet' => count($english), 'lekce' => $english],
    'kviz_correct' => ['polozek' => $quizTotal, 'rozlozeni_L2_28' => $correct['L2_28'], 'rozlozeni_L3_28' => $correct['L3_28']],
    'materialy' => ['learning_resources_zaznamu' => $resTotal, 'unikatnich_url' => count(array_filter(array_keys($resUrls))), 'bez_metadat' => $resNoMeta,
        'lekci_bez_materialu' => $lessonsNoMaterial, 'temata' => $topicsNoMaterial, 'videa_ucitelu_v42' => $teacherVideos],
    'chybi_svp' => $svpMissing, 'materials_md' => $mdFamilies,
    'dny_bez_lekce' => ['v_kalendari' => count($calendarDays), 'karet' => $dayCards, 'ocekavano' => count($calendarDays) * count(LM71_CLASSES)],
    't14' => $t14, 'cache' => $cache,
];
$lines = [
    'EDUCANET v71 · report obsahu (' . date('j. n. Y H:i') . ')',
    sprintf('Lekce: %d · úplné %d (%s) · průměr %.2f z %d polí', $total, $complete, rp71_pct($complete, $total), $avgScore, count($fields)),
    'Pokrytí polí: ' . implode(', ', array_map(static fn(string $k, int $v): string => $k . ' ' . $v, array_keys($coverage), $coverage)),
    'Konflikty zdrojů: ' . count($conflicts) . ' · duplicitní titulky: ' . $dupTitles . ' · duplicitní kvízové otázky: ' . $dupQuestions['texty'] . ' textů / ' . $dupQuestions['polozky'] . ' výskytů',
    'Exit ticket shodný s kvízem lekce: ' . $exitIsQuiz . ' lekcí',
    sprintf('Šablonové úkoly: %d z %d (%d unikátních textů) · šablonová kostra plánu: %d lekcí · generovaný zdroj: %d lekcí', $templateTasks, $tasksTotal, count($templateTexts), $skeletonLessons, $templateSourceLessons),
    'Anglické titulky: ' . count($english),
    'Kvíz correct (L2–28, ' . $quizTotal . ' položek): ' . json_encode($correct['L2_28']) . ' · L3–28: ' . json_encode($correct['L3_28']),
    sprintf('Materiály: %d záznamů, %d unikátních URL, bez metadat (checked_at + licence) %d · lekcí bez materiálu %d', $resTotal, count(array_filter(array_keys($resUrls))), $resNoMeta, $lessonsNoMaterial),
    'Témata bez materiálu: ' . implode(', ', array_map(static fn(string $c, array $r): string => $c . ' ' . $r['bez_materialu'] . '/' . $r['temat'], array_keys($topicsNoMaterial), $topicsNoMaterial)),
    'Videa učitelů v42: ' . ($teacherVideos === null ? 'nezjišťováno (bez --storage)' : $teacherVideos['celkem'] . ' (bez metadat ' . $teacherVideos['bez_metadat'] . ', http bez TLS ' . $teacherVideos['http_bez_tls'] . ')'),
    'Chybí vazba na ŠVP: ' . $svpMissing . ' lekcí',
    'materials/ MD: ' . implode(' · ', array_map(static fn(string $f, array $r): string => $f . ' ' . $r['souboru'] . ' souborů, šablonový podíl ' . $r['sablonovy_podil_radku'] . ' %, AI prompty ' . $r['se_sekci_ai_prompty'] . ', dupl. otázka ' . $r['s_duplicitni_otazkou'], array_keys($mdFamilies), $mdFamilies)),
    'Dny bez lekce: ' . count($calendarDays) . ' v kalendáři, karet ' . $dayCards . ' z ' . count($calendarDays) * count(LM71_CLASSES),
    'T-14: lekcí v příštích 14 dnech ' . $t14['lekci'] . ', z toho ve stavu návrh ' . $t14['navrh'],
    'Cache modelu: ' . implode(', ', array_map(static fn(string $c, string $s): string => $c . '=' . $s, array_keys($cache['model']), $cache['model']))
        . ' · runtime: ' . implode(', ', array_map(static fn(string $c, string $s): string => $c . '=' . $s, array_keys($cache['runtime']), $cache['runtime'])),
];
if ($storageArg !== null && rp71_tree_hash($storageArg) !== $storageBefore) rp71_fail('Úložiště se během reportu změnilo (souběžný zápis aplikace?) – report spusť znovu.', 3);
$paths = rp71_write($outDir, 'content-report', $report, $lines);
echo implode(PHP_EOL, $lines) . PHP_EOL . 'Zapsáno: ' . basename($paths['json']) . ', ' . basename($paths['txt']) . PHP_EOL . 'V71_CONTENT_REPORT_OK' . PHP_EOL;
exit(0);
