<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · jednotný ověřovací audit přístupnosti a responzivity (fáze E zadání docs/PROMPT_V78_RESPONSIVE.md).
 *   1) statika: lang na všech vstupech, skript prohlížeče a seznam stránek, ruční checklist (docs/MANUAL_CHECKLIST_V80.md), index snímků,
 *   2) HTTP: každá hlavní stránka učitele (cockpit) i žáka vrací 200 bez chyby PHP, má <html lang>, <title>, právě jeden <main> a <h1>,
 *   3) prohlížeč (lib/v80_2_a11y_browser.cjs, Chrome přes playwright-core): stránky × 11 šířek (320–1920, 1366 × 600) × světlý/tmavý:
 *      0 vodorovných posunů, cíle ≥ 44 px, písmo ≥ 14 px, kontrast vykresleného textu (vzorky, AA 4,5 : 1 / velký 3 : 1), lang,
 *      hierarchie nadpisů (h1, bez přeskočené úrovně), landmarky (1 main, popsané nav), žádný kladný tabindex, tmavé pozadí podle zařízení
 *      tam, kde má stránka tmavý režim; reflow 200 % (640 × 512) a 400 % (320 × 256 CSS px) bez posunu a s pevnými lištami ≤ 50 % výšky;
 *      Tab (1280 a 390 px): fokus viditelný (obrys/stín), na obrazovce, pořadí bez skoků zpět.
 *   Výjimky jsou jen ve V802_EXCEPTIONS (každá s důvodem, viz BUILD_MANIFEST_V80_2.md) – nic se nepovoluje plošně.
 *
 *   php tools/v80_2_a11y_audit.php
 *   Prohlížeč: EDUCANET_V802_BROWSER=require|skip; snímky: EDUCANET_V802_SHOTS=<adresář> (JPEG ≤ 150 kB); surová data: EDUCANET_V802_RAW=<soubor.json>.
 * Dočasné úložiště (edu_audit_temp_storage), fiktivní žáci ze student_directory.php; ostrou storage/ nikdy nečte ani nezapisuje.
 * Konec: V80_2_A11Y_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v80-2-a11y')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'runtime_content.php', 'app/lib.php', 'ui_v67.php', 'nav_v61.php', 'identity_v58.php',
    'cockpit_v70.php', 'teacher_prefs_v72_1.php', 'competencies_v62.php', 'evidence_v62.php', 'mastery_v62.php', 'lesson_model_v71_sources.php', 'lesson_model_v71.php', 'competencies_v75_views.php',
    'taskbank_v75_views.php', 'taskbank_v76.php', 'learning_v80_views.php', 'widgets_v80_views.php', 'progress_v80_views.php', 'retention_v76.php', 'projects_v65.php',
    'classmap_v80_1.php', 'support_v80_1.php', 'support_v80_1_teacher.php', 'support_v80_1_views.php', 'classmap_v80_1_views.php', 'insights_v74_views.php', 'student_v55.php'] as $lib) require_once $ROOT . '/' . $lib;
app_require_libs(['core', 'layout', 'paths', 'projects65']);
require_once __DIR__ . '/lib/v80_1_fixture.php';
require_once __DIR__ . '/lib/v80_2_a11y_pages.php';

const V802_KEY = 'v80-2-a11y-audit-key';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-') && STORAGE_DIR === $tmp);

// ---------------------------------------------------------------- 1) statika
$pages = v802_pages();
$teacherPages = array_filter($pages, static fn(array $p): bool => $p['who'] === 't');
$studentPages = array_filter($pages, static fn(array $p): bool => $p['who'] !== 't');
$check('seznam stránek: ' . count($teacherPages) . ' hlavních stránek učitele (všechny 4 sekce Dnes · Výuka · Žáci · Správa) a ' . count($studentPages) . ' žáka (Domů · Dnes · Úlohy · Profil, úloha, přihlášení)',
    count($teacherPages) >= 12 && count($studentPages) >= 12 && array_diff(['dnes', 'vyuka', 'trida', 'sprava'], array_unique(array_filter(array_column($teacherPages, 'section')))) === []
    && array_filter($teacherPages, static fn(array $p): bool => $p['tab'] !== '' && !isset(teacher68_tab_map()[$p['tab']])) === []);
$check('lang: kostra žáka vypisuje <html lang> podle jazyka rozhraní (edu_html_lang), cockpit <html lang="cs">', str_contains($read('app/views/_layout.php'), '<html lang="<?= e(edu_html_lang()) ?>"') && str_contains($read('teacher.php'), '<html lang="cs"<?=teacher68_theme_attr('));
$js = $read('tools/lib/v80_2_a11y_browser.cjs');
$check('skript prohlížeče: 11 šířek, reflow 200 % a 400 %, Tab, kontrast, bez sítě mimo dev server a bez zápisu mimo adresář snímků',
    substr_count($js, '], [') >= 10 && str_contains($js, '[320, 256, 400]') && str_contains($js, "keyboard.press('Tab')") && str_contains($js, 'lowC') && !preg_match('~https?://(?!127\.0\.0\.1)~', $js));
$manual = $read('docs/MANUAL_CHECKLIST_V80.md');
$check('ruční checklist docs/MANUAL_CHECKLIST_V80.md: klávesnice, NVDA, skutečný telefon a tablet, zoom 200/400 %, tabulka výsledků k vyplnění',
    $manual !== '' && str_contains($manual, 'NVDA') && str_contains($manual, 'Klávesnice') && str_contains($manual, 'telefon') && str_contains($manual, 'tablet') && str_contains($manual, '400 %') && substr_count($manual, '| ☐') >= 10);
$index = $read('docs/screens-v78/INDEX.md');
$missing = [];
if (preg_match_all('~\(([a-z0-9_.-]+\.(?:jpg|png))\)~', $index, $mm)) foreach ($mm[1] as $f) if (!is_file($ROOT . '/docs/screens-v78/' . $f)) $missing[] = $f;
$check('index snímků docs/screens-v78/INDEX.md: odkazy na existující soubory, sada v80.2 (e802-*) 375/768/1280/1920 světlý i tmavý' . ($missing ? ' [chybí ' . implode(', ', array_slice($missing, 0, 3)) . ']' : ''),
    $index !== '' && $missing === [] && substr_count($index, 'e802-') >= 40);
$big = array_filter(glob($ROOT . '/docs/screens-v78/e802-*.jpg') ?: [], static fn(string $f): bool => filesize($f) > 150 * 1024);
$check('snímky v80.2: každý ≤ 150 kB (' . count(glob($ROOT . '/docs/screens-v78/e802-*.jpg') ?: []) . ' souborů)', count(glob($ROOT . '/docs/screens-v78/e802-*.jpg') ?: []) >= 40 && $big === []);

// ---------------------------------------------------------------- 2) fixture + HTTP
$fx = v801_fx_seed('class_3a', time());
unset($GLOBALS['educanet_json_request_cache']);
$A = $fx[0] ?? ['label' => ''];
$check('fixture: fiktivní žáci 3.A ze student_directory.php (' . count($fx) . ')', count($fx) >= 6 && $A['label'] !== '');
$cache = $tmp . '-lm71';
@mkdir($cache, 0700, true);
register_shutdown_function(static function () use ($cache): void { edu_audit_remove_dir($cache); });
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V802_KEY, 'EDUCANET_TEACHER_ROLE' => 'admin', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cache];
require __DIR__ . '/lib/v80_2_a11y_http.php';

exit(audit_summary($state, 'V80_2_A11Y'));
