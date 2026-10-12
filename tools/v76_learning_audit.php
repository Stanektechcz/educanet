<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v76 · audit vrstvy (bez HTTP): plán vs. skutečnost, plnění ŠVP, otevřené body v75 a registr cockpitu.
 *   1) Plán vs. skutečnost (pv76_*): stavy lekcí, zpoždění, prognóza a návrhy dohnání na vymyšlených datech; kalendář se nemění,
 *   2) Plnění ŠVP (sx76_*): naplněno jen se schválenou vazbou a odučenou lekcí, mezery, pololetí, CSV bez vzorců a jmen,
 *   3) cesty: 1.A ≥ 4, 3.A ≥ 5, výpočty statických předpovědí (box model, flexbox, mřížka, velikost obrázku, tisk) přepočítané nezávisle,
 *   4) grafické úlohy 1.A/2.A: deterministické varianty, klíč přepočítaný nezávisle (HEX/RGB, px/em/rem/%, specificita, DPI), tolerance zápisu,
 *   5) důkazy kompetencí: úloha (zdroj task) a ruční pozorování učitele (zdroj observation) – zápis, validace, váhy, žádný volný text,
 *   6) portfolio: připnuté práce v lokálním HTML exportu v65 (bez odkazů a skriptů); retence v75 dat po odchodu žáka (dry-run / apply),
 *   7) registr: moduly plan76 / svp76, tmavý režim, politiky GET a POST (deny-by-default), oprávnění, rozpočet CSS+JS.
 *   php tools/v76_learning_audit.php     Dočasné úložiště. Konec: V76_LEARNING_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v76-learning')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
foreach (['adaptive_learning.php', 'tutorial_v52.php', 'learning_v56.php', 'lesson_model_v71_sources.php', 'lesson_model_v71.php', 'lesson_today_v70.php', 'lesson_reflect_v73.php',
    'svp_v72.php', 'lesson_approval_v72.php', 'competencies_v62.php', 'competencies_v75.php', 'evidence_v62.php', 'mastery_v62.php', 'paths_v63.php', 'paths_v63_class.php',
    'taskbank_v75_gen.php', 'taskbank_v75.php', 'taskbank_v76.php', 'levels_v75.php', 'projects_v65.php', 'projects_v65_evidence.php', 'portfolio_v65.php', 'portfolio_v75.php',
    'portfolio_v75_views.php', 'portfolio_v65_views.php', 'retention_v76.php', 'observation_v76.php', 'plan_v76.php', 'svp_export_v76.php',
    'teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php'] as $lib) require_once $ROOT . '/' . $lib;
require_once __DIR__ . '/lib/v63_paths_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$throws = static function (callable $fn, string $needle = ''): bool { try { $fn(); } catch (RuntimeException $e) { return $needle === '' || str_contains($e->getMessage(), $needle); } return false; };
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-'));
$bad = [];
foreach (['plan_v76.php', 'plan_v76_views.php', 'svp_export_v76.php', 'svp_export_v76_views.php', 'observation_v76.php', 'taskbank_v76.php', 'taskbank_v76_gen.php', 'retention_v76.php',
    'paths_v76_content_gfx.php', 'paths_v76_content_os.php', 'tools/v76_learning_audit.php', 'tools/lib/v76_learning_part2.php', 'tools/v76_http_audit.php', 'tools/lib/v76_http.php'] as $f) {
    $s = $read($f);
    $guard = str_starts_with($f, 'tools/v76_') ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if ($s === '' || !str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800
        || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]' . (str_starts_with($f, 'tools/') ? '' : '|innerHTML') . '/', $s) === 1) $bad[] = $f;
}
$check('soubory v76: strict_types, guard (knihovna 403 / CLI), ≤ 800 řádků, PHP 8.1, bez innerHTML' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);

// ---------------------------------------------------------------- 1) Plán vs. skutečnost (čisté funkce, vymyšlená data)
$plan = [];
for ($n = 1; $n <= 28; $n++) $plan[$n] = date('Y-m-d', (int)strtotime('2026-09-16 +' . (7 * ($n - 1)) . ' days'));
$calRows = [];
foreach ($plan as $n => $d) $calRows[] = ['date' => $d, 'status' => 'teaching', 'kind' => 'lesson', 'lesson_number' => $n];
$calRows[] = ['date' => '2027-05-19', 'status' => 'teaching', 'kind' => 'mastery', 'title' => 'Mastery clinic'];
$today = '2026-10-12';
$cal = pv76_calendar($calRows, $today);
$lessons = pv76_lessons('class_3a');
$states = pv76_reflection_states([['lesson' => 1, 'date' => '2026-09-16', 'state' => 'cele'], ['lesson' => 2, 'date' => '2026-09-23', 'state' => 'neoduceno'],
    ['lesson' => 3, 'date' => '2026-09-30', 'state' => 'cast'], ['lesson' => 3, 'date' => '2026-09-01', 'state' => 'cele'], ['lesson' => 40, 'date' => '2026-09-30', 'state' => 'cele'], ['lesson' => 5, 'date' => '2026-10-01', 'state' => 'hack']]);
$a = pv76_analyze($plan, $cal, $states, $lessons, $today);
$check('plán vs. skutečnost: stav lekce z nejnovější reflexe (L1 odučeno, L2 neodučeno, L3 část), L4 bez reflexe = nepotvrzeno, budoucí = naplánováno; neplatná lekce i stav se ignorují',
    $a['rows'][1]['status'] === 'odučeno' && $a['rows'][2]['status'] === 'neodučeno' && $a['rows'][3]['status'] === 'část' && $a['rows'][4]['status'] === 'nepotvrzeno'
    && $a['rows'][5]['status'] === 'naplánováno' && count($states) === 3 && $a['count']['naplánováno'] === 24);
$check('plán vs. skutečnost: zpoždění = plán do dneška (4) − odučeno (1 + ½ + nepotvrzená 1) = 1,5; prognóza 27 z 28 (neodučená + část potřebují 2 hodiny, rezerva Mastery clinic 1)',
    $a['planned_by_today'] === 4 && abs($a['done'] - 2.5) < 1e-9 && abs($a['delay'] - 1.5) < 1e-9 && $a['forecast'] === ['fits' => 27, 'needed' => 2, 'reserve' => 1, 'reserve_left' => 0, 'missing' => 1]
    && count($a['warnings']) === 2 && pv76_num(1.5) === '1,5');
$kinds = array_column($a['suggestions'], 'kind');
$check('návrhy dohnání: rezerva, sloučení sousedních lekcí se společnou kompetencí, zkrácení nejdelšího úseku (ne u sloučených), max 6, s úsporou v minutách',
    in_array('rezerva', $kinds, true) && in_array('sloučit', $kinds, true) && in_array('zkrátit', $kinds, true) && count($a['suggestions']) <= PV76_SUGGEST_MAX
    && array_reduce($a['suggestions'], static fn(bool $ok, array $s): bool => $ok && $s['label'] !== '' && $s['detail'] !== '', true));
$cancelRows = $calRows;
$cancelRows[3] = ['date' => $plan[4], 'status' => 'no_school', 'kind' => 'cancelled', 'original_lesson_number' => 4, 'title' => 'Odpadlá hodina'];
for ($i = 4; $i < 28; $i++) $cancelRows[$i]['lesson_number'] = $i;   // fronta obsahu se posune o týden
$cancelRows[] = ['date' => '2027-06-30', 'status' => 'teaching', 'kind' => 'close', 'unscheduled_agenda' => [['lesson_number' => 28]]];
$b = pv76_analyze($plan, pv76_calendar($cancelRows, $today), [], $lessons, $today);
$check('odpadlá hodina: lekce za ní jsou „posunuto“ (+7 dní), L28 „mimo kalendář“, prognóza ji počítá k dohnání; posun se jen ukazuje (kalendář ani úložiště se nemění)',
    $b['rows'][5]['status'] === 'posunuto' && $b['rows'][5]['shift_days'] === 7 && $b['rows'][28]['status'] === 'mimo kalendář' && $b['forecast']['needed'] === 1
    && count($b['cancelled']) === 1 && !is_file(lr73_path()) && !is_file(STORAGE_DIR . '/calendar_exceptions.json.php'));
$empty = pv76_analyze($plan, $cal, [], $lessons, '2026-09-01');
$check('prázdný stav: před první lekcí žádné zpoždění, prognóza 28 z 28 + rezerva, žádné návrhy ani varování', $empty['delay'] == 0.0 && $empty['forecast']['fits'] === 28
    && $empty['forecast']['reserve_left'] === 1 && $empty['suggestions'] === [] && $empty['warnings'] === []);

// ---------------------------------------------------------------- 2) Plnění ŠVP (čisté funkce)
$outcomes = ['o-a' => ['subject' => 'SAV', 'year' => 3, 'page' => 1, 'text' => 'výsledek A'], 'o-b' => ['subject' => 'SAV', 'year' => 3, 'page' => 2, 'text' => 'výsledek B'],
    'o-c' => ['subject' => 'SAV', 'year' => 3, 'page' => 3, 'text' => 'výsledek C'], 'o-d' => ['subject' => 'SAV', 'year' => 3, 'page' => 4, 'text' => '=HYPERLINK("x")'],
    'o-e' => ['subject' => 'SBE', 'year' => 4, 'page' => 5, 'text' => 'předstih'], 'o-f' => ['subject' => 'ASY', 'year' => 3, 'page' => 6, 'text' => 'cizí předmět']];
$in = [1 => ['date' => '2026-09-16', 'ids' => ['o-a'], 'svp' => 'schvaleno', 'state' => 'cele'], 2 => ['date' => '2026-09-23', 'ids' => ['o-b'], 'svp' => 'navrh', 'state' => 'cast'],
    3 => ['date' => '2026-09-30', 'ids' => ['o-c'], 'svp' => 'schvaleno', 'state' => ''], 4 => ['date' => '2026-11-04', 'ids' => ['o-d', 'o-e'], 'svp' => 'schvaleno', 'state' => ''],
    5 => ['date' => '2027-03-03', 'ids' => ['o-a'], 'svp' => 'schvaleno', 'state' => '']];
$sx = sx76_analyze($in, $outcomes, 3, ['2026-09-01', '2027-01-31'], '2026-10-12');
$st = array_column($sx['rows'], 'status', 'id');
$check('plnění ŠVP: naplněno jen odučená lekce se schválenou vazbou (A); odučeno s návrhem vazby = čeká na vazbu (B); minulý termín bez reflexe = nepotvrzeno (C); budoucí = naplánováno (D, E jiný ročník)',
    $st === ['o-a' => 'naplněno', 'o-b' => 'čeká na vazbu', 'o-c' => 'nepotvrzeno', 'o-d' => 'naplánováno', 'o-e' => 'naplánováno'] && $sx['lessons_total'] === 4 && $sx['lessons_taught'] === 2 && $sx['approved'] === 3
    && $sx['expected'] === 4 && array_column($sx['rows'], 'other_year', 'id')['o-e'] === true);
$sx2 = sx76_analyze($in, $outcomes, 3, ['2027-02-01', '2027-08-31'], '2027-03-10');
$st2 = array_column($sx2['rows'], 'status', 'id');
$check('plnění ŠVP 2. pololetí: jen lekce v pololetí; výsledek ročníku bez lekce = mezera (B, C, D); předmět mimo plán třídy (ASY) se nepočítá', ($st2['o-a'] ?? '') === 'nepotvrzeno'
    && ($st2['o-b'] ?? '') === 'mezera' && ($st2['o-c'] ?? '') === 'mezera' && ($st2['o-d'] ?? '') === 'mezera' && !isset($st2['o-f']) && sx76_current_half('2027-03-10') === 2 && sx76_current_half('2026-10-12') === 1
    && sx76_half_range(['calendar' => [['date' => '2026-09-02']]], 2) === ['2027-02-01', '2027-08-31']);
$csv = sx76_csv_body($sx + ['class' => 'class_3a', 'half' => 1]);
$check('CSV: UTF-8 s BOM, středník, hlavička bez jmen, buňka začínající = se zneškodní apostrofem', str_starts_with($csv, "\xEF\xBB\xBFtrida;pololeti;vysledek_id") && str_contains($csv, "'=HYPERLINK")
    && !str_contains($csv, ';=HYPERLINK') && substr_count($csv, "\n") === count($sx['rows']) + 1 && sx76_csv_cell('+1') === "'+1" && sx76_csv_cell("a\x01b") === 'a b');

require __DIR__ . '/lib/v76_learning_part2.php';

exit(audit_summary($state, 'V76_LEARNING'));
