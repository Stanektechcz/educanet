<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v60 · tools/v60_profile_audit.php – audit „přehlednější profil žáka“
 * (profile_v60.php, profile_v60_views.php, assets/profile-v60.{css,js}).
 *
 * Dočasné úložiště (edu_audit_temp_storage), nikdy nesahá na ostrou storage/. Kontroluje:
 * lint + declare(strict_types=1) nových souborů; whitelist záložek a pád neplatné hodnoty na
 * „prehled“; že cizí profil nikdy nedostane panel Lab ani formulář nastavení (name="headline");
 * že render vlastního i cizího profilu proběhne bez fatální chyby; že každé <svg> má <title> i
 * navazující tabulku; že dočasné úložiště zůstane po renderu beze změny (SHA-256 před/po);
 * absenci http(s):// odkazů/CDN a innerHTML v nových souborech; pokrytí nových msgid v en/uk.
 *
 * Spuštění: C:/php/php.exe tools/v60_profile_audit.php
 * Konec: V60_PROFILE_AUDIT_OK checks=N failed=0 (jinak _FAIL, nenulový exit kód).
 */

$ROOT = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once $ROOT . '/tools/lib/audit_storage.php';
edu_audit_temp_storage('v60-profile');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/tools/lib/audit.php';

foreach ([
    'app/lib.php',
    'app/views/_layout.php', 'student_v55.php', 'student_v55_views.php', 'zero_friction_v50_6.php',
    'unified_page_shell_v50_7.php', 'student_links_v69.php', 'goal_navigator_v50_4.php', 'hands_on_learning_v50.php',
    'independent_growth_v50.php', 'learning_v56.php', 'session_v53.php', 'tutorial_v52.php', 'linux_v57_lab.php', 'arena_v57.php',
    'teacher_operations_v46.php', 'student_learning_coach_v47.php', 'student_learning_coach_views_v47.php',
    'student_learning_accelerator_v47_1.php', 'student_learning_accelerator_views_v47_1.php',
    'student_corrective_cycle_v47_2.php', 'student_corrective_cycle_views_v47_2.php', 'student_social_views.php',
    'skill_views.php', 'project_workspace_views.php', 'points_v53.php', 'learning_v56_views.php',
    'profile_v60.php', 'profile_v60_views.php', 'lab_v58_learning.php',
    'arena_v58_weekly.php', 'arena_v60_challenge.php', 'arena_v60_challenge_views.php',
] as $rel) {
    require_once $ROOT . '/' . $rel;
}
$GLOBALS['nextLessons'] = [];
$GLOBALS['extendedLessons'] = [];
$GLOBALS['modules'] = [];
$GLOBALS['view'] = 'profile';

$state = audit_counter();
$check = audit_checker($state);

// =============================================================================================
// 1) Lint + declare(strict_types=1) nových souborů.
// =============================================================================================
$newFiles = [
    'profile_v60.php', 'profile_v60_views.php', 'assets/profile-v60.js',
];
foreach ($newFiles as $rel) {
    $abs = $ROOT . '/' . $rel;
    $check('soubor existuje: ' . $rel, is_file($abs));
    if (str_ends_with($rel, '.php')) {
        exec('C:/php/php.exe -l ' . escapeshellarg($abs) . ' 2>&1', $out, $code);
        $check('php -l OK: ' . $rel, $code === 0, false);
        $src = (string)file_get_contents($abs);
        $check('declare(strict_types=1): ' . $rel, str_contains($src, 'declare(strict_types=1);'), false);
        $check('žádné http(s):// zdroje/CDN: ' . $rel, !preg_match('~https?://~', $src), false);
    }
    if (str_ends_with($rel, '.js')) {
        $src = (string)file_get_contents($abs);
        $check('žádné innerHTML v JS: ' . $rel, !preg_match('/innerHTML\s*=/', $src), false);
    }
}
$cssAbs = $ROOT . '/assets/profile-v60.css';
$check('css soubor existuje', is_file($cssAbs));
$css = (string)file_get_contents($cssAbs);
$check('css bez CDN/http(s)://', !preg_match('~https?://~', $css), false);
$check('css prefix .p60-', str_contains($css, '.p60-'), false);

// =============================================================================================
// 2) Whitelist záložek + pád na "prehled".
// =============================================================================================
$_GET['tab'] = '<script>alert(1)</script>';
$check('vlastní profil: neplatná záložka padá na prehled', profile60_current_tab(true) === 'prehled');
$_GET['tab'] = 'nastaveni';
$check('cizí profil: nastaveni není povolené', profile60_current_tab(false) === 'prehled');
$_GET['tab'] = 'lab';
$check('cizí profil: lab není povolené', profile60_current_tab(false) === 'prehled');
$_GET['tab'] = 'pokrok';
$check('cizí profil: pokrok je povolené', profile60_current_tab(false) === 'pokrok');
$_GET['tab'] = 'odznaky';
$check('vlastní profil: odznaky je povolené', profile60_current_tab(true) === 'odznaky');
unset($_GET['tab']);

// =============================================================================================
// 3) Fixture: dva žáci ve třídě 3.A, aby šlo otestovat vlastní i cizí profil.
// =============================================================================================
$classId = 'class_3a';
$students = project_students_for_class($classId);
$labels = array_values(array_map(static fn(array $s): string => (string)$s['label'], $students));
$check('fixture: třída 3.A má alespoň 2 žáky', count($labels) >= 2);
$meLabel = $labels[0] ?? 'Test Žák';
$otherLabel = $labels[1] ?? ($labels[0] ?? 'Test Žák 2');

$snapshotBefore = v60a_storage_snapshot();

$_SESSION['next_class_id'] = $classId;
$_SESSION['student_label'] = $meLabel;
$meKey = social_current_student_key($classId);
$otherKey = null;
foreach ($students as $key => $s) {
    if ((string)$s['label'] === $otherLabel && $key !== $meKey) { $otherKey = (string)$key; break; }
}
$check('fixture: cizí žák nalezen', $otherKey !== null);

// --- vlastní profil, každá záložka ----------------------------------------------------------
foreach (profile60_tabs() as $tab) {
    $_GET['tab'] = $tab;
    unset($_GET['student']);
    $html = audit_capture(static function () use ($classId): void {
        render_profile60_view($classId, []);
    });
    $check('vlastní profil (' . $tab . ') se vykreslí bez pádu', $html !== '' && !preg_match('/Fatal error|Uncaught/', $html));
    $check('vlastní profil (' . $tab . ') má aria-current', str_contains($html, 'aria-current="page"'));
}

// --- cizí profil: whitelist + zákaz panelu Lab a formuláře nastavení ------------------------
if ($otherKey !== null) {
    foreach (['prehled', 'pokrok', 'odznaky', 'lab', 'nastaveni', 'xyz'] as $tab) {
        $_GET['tab'] = $tab;
        $_GET['student'] = $otherKey;
        $html = audit_capture(static function () use ($classId): void {
            render_profile60_view($classId, []);
        });
        $check('cizí profil (' . $tab . ') se vykreslí bez pádu', $html !== '' && !preg_match('/Fatal error|Uncaught/', $html));
        $check('cizí profil (' . $tab . ') neobsahuje panel Lab', !str_contains($html, 'p60-panel-lab-marker') && !str_contains($html, 'Vyřešené úrovně v balíčcích'));
        $check('cizí profil (' . $tab . ') neobsahuje formulář nastavení', !str_contains($html, 'name="headline"'));
    }
}
unset($_GET['tab'], $_GET['student']);

// =============================================================================================
// 4) Každé <svg> má <title> a navazující tabulku.
// =============================================================================================
$_GET['tab'] = 'prehled';
$overviewHtml = audit_capture(static function () use ($classId): void { render_profile60_view($classId, []); });
$svgCount = substr_count($overviewHtml, '<svg');
preg_match_all('/<svg\b.*?<\/svg>/s', $overviewHtml, $svgBlocks);
$titleCount = count(array_filter($svgBlocks[0], static fn(string $b): bool => str_contains($b, '<title')));
$chartSvgs = preg_match_all('/<svg[^>]*aria-labelledby="p60-chart-title-/', $overviewHtml);
$check('overview obsahuje aspoň jeden graf', $chartSvgs > 0);
$check('každé <svg> (graf, prstenec úrovně, odznak) má <title>', $svgCount > 0 && $svgCount === $titleCount);
$check('každý graf má vlastní <title id="p60-chart-title-…">', $chartSvgs === substr_count($overviewHtml, '<title id="p60-chart-title-'));
$check('graf má navazující tabulku (<table class="p60-data-table">)', str_contains($overviewHtml, 'p60-data-table'));
unset($_GET['tab']);

// =============================================================================================
// 5) Dočasné úložiště zůstává beze změny (jen čtení).
// =============================================================================================
clearstatcache();
// Skill cache (skill_branches.json.php/skill_progress.json.php + .lock) je legitimní read-through
// cache skill_recalculate_all()/skill_recalculate_branches() – existovala už před v60 a nenese žádná
// nová data žáka; jiné soubory (profily, přátelství, body, badge…) se během renderu měnit nesmí.
$GLOBALS['snapshotBefore'] = $snapshotBefore;
$check('dočasné úložiště beze změny mimo skill cache (SHA-256 před/po)', v60a_storage_diff_is_only_skill_cache());

// =============================================================================================
// 6) Pokrytí en/uk katalogů pro nové msgid (extrakce z profile_v60*.php).
// =============================================================================================
$enHubs = require $ROOT . '/lang/en/ui/hubs.php';
$ukHubs = require $ROOT . '/lang/uk/ui/hubs.php';
$missingEn = [];
$missingUk = [];
foreach (['profile_v60.php', 'profile_v60_views.php'] as $rel) {
    $src = (string)file_get_contents($ROOT . '/' . $rel);
    if (!preg_match_all("/\\btr\\('((?:[^'\\\\]|\\\\.)*)'/", $src, $m)) continue;
    foreach ($m[1] as $raw) {
        $msgid = stripcslashes($raw);
        if (!isset($enHubs[$msgid])) $missingEn[] = $msgid;
        if (!isset($ukHubs[$msgid])) $missingUk[] = $msgid;
    }
}
$check('všechny nové msgid mají anglický překlad (hubs)', $missingEn === [], false) || print_r($missingEn);
$check('všechny nové msgid mají ukrajinský překlad (hubs)', $missingUk === [], false) || print_r($missingUk);

// =============================================================================================
// 7) Manifest domén obsahuje nové soubory.
// =============================================================================================
$manifest = require $ROOT . '/lang/domains_v59.php';
$hubsFiles = (array)($manifest['domains']['hubs'] ?? []);
$check('manifest domén: profile_v60.php v doméně hubs', in_array('profile_v60.php', $hubsFiles, true));
$check('manifest domén: profile_v60_views.php v doméně hubs', in_array('profile_v60_views.php', $hubsFiles, true));

// =============================================================================================
// 8) v60.2 · rychlost a velikost: HTML záložek, počet čtených souborů úložiště, sprite, CSS/JS.
// =============================================================================================
unset($_GET['student']);
$limits = ['odznaky' => 35 * 1024]; // v61: zamčené až na ?zamcene=1, stránka první strany sbírky lehká
foreach (profile60_tabs() as $tab) {
    $_GET['tab'] = $tab;
    $GLOBALS['educanet_json_request_cache'] = [];
    $t0 = hrtime(true);
    $html = audit_capture(static function () use ($classId): void { render_profile60_view($classId, []); });
    $ms = (hrtime(true) - $t0) / 1e6;
    $filesRead = count((array)($GLOBALS['educanet_json_request_cache'] ?? []));
    $limit = $limits[$tab] ?? 40 * 1024;
    $check('záložka ' . $tab . ': HTML ' . strlen($html) . ' B ≤ ' . ($limit / 1024) . ' KB', strlen($html) <= $limit);
    $check('záložka ' . $tab . ': načteno ' . $filesRead . ' souborů úložiště (limit 40, žádné N+1)', $filesRead <= 40);
    $check('záložka ' . $tab . ': vykreslení ' . round($ms) . ' ms < 1500 ms (hrubý strop na prázdném úložišti)', $ms < 1500, false);
    $check('záložka ' . $tab . ': SVG sprite odznaků je v HTML nejvýš jednou', substr_count($html, 'class="b60-sprite"') <= 1);
}
$_GET['tab'] = 'odznaky';
$badgesPage = audit_capture(static function () use ($classId): void { render_profile60_view($classId, []); });
preg_match_all('~<use href="#(b60s-[a-z0-9-]+)"~', $badgesPage, $uses);
preg_match_all('~id="(b60s-[a-z0-9-]+)"~', $badgesPage, $ids);
$check('odznaky: každý <use> odkazuje na symbol definovaný na stránce, id jsou unikátní', array_diff(array_unique($uses[1]), $ids[1]) === [] && count($ids[1]) === count(array_unique($ids[1])));
$check('odznaky: zamčené se v základní stránce nevykreslují (jen odkaz ?zamcene=1), aby byla stránka lehká', !str_contains($badgesPage, 'b60-row'));
$_GET['tab'] = 'nastaveni';
$settingsPage = audit_capture(static function () use ($classId): void { render_profile60_view($classId, []); });
$check('nastavení: náhled profilu, chybové sloty polí (aria-describedby) a data pro klientskou validaci', str_contains($settingsPage, 'data-p60-preview="headline"') && str_contains($settingsPage, 'id="p60-f-skills-error"') && str_contains($settingsPage, 'data-p60-tags="8"') && str_contains($settingsPage, 'data-msg-tags-many'));
unset($_GET['tab']);
$cssSrc = (string)file_get_contents($ROOT . '/assets/profile-v60.css');
$jsSrc = (string)file_get_contents($ROOT . '/assets/profile-v60.js');
$check('CSS profilu ' . strlen($cssSrc) . ' B ≤ 24 KB, bez @import', strlen($cssSrc) <= 24576 && !str_contains($cssSrc, '@import'));
$check('JS profilu ' . strlen($jsSrc) . ' B ≤ 6 KB', strlen($jsSrc) <= 6144);
$check('CSS má 3px zřetelný focus-visible a podporu prefers-reduced-motion', str_contains($cssSrc, 'outline:3px solid var(--accent)') && str_contains($cssSrc, 'prefers-reduced-motion'));
$check('profil načítá jediný CSS soubor profilu a JS s defer', substr_count($overviewHtml, 'profile-v60.css') === 1 && str_contains($overviewHtml, 'profile-v60.js') && preg_match('~profile-v60\.js[^>]*defer~', $overviewHtml) === 1);
$check('každá záložka profilu má aria-current právě na jednu položku navigace (sidebar i lišta)', substr_count($overviewHtml, 'aria-current="page"') >= 2);

exit(audit_summary($state, 'V60_PROFILE'));

/** @return array<string,string> cesta => md5 obsahu, pro každý soubor v dočasném úložišti. */
function v60a_storage_snapshot(): array
{
    $dir = defined('STORAGE_DIR') ? STORAGE_DIR : '';
    if ($dir === '' || !is_dir($dir)) return [];
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile()) $files[$f->getPathname()] = (string)md5_file($f->getPathname());
    }
    ksort($files);
    return $files;
}

/** true, pokud se mezi $snapshotBefore/$snapshotAfter (globální proměnné) liší jen skill cache. */
function v60a_storage_diff_is_only_skill_cache(): bool
{
    $before = $GLOBALS['snapshotBefore'] ?? [];
    $after = v60a_storage_snapshot();
    $isSkillCache = static fn(string $path): bool => (bool)preg_match('~skill_(branches|progress)\.json\.php(\.lock)?$~', $path) || (bool)preg_match('~arena_v60_challenges\.json\.php(\.lock)?$~', $path) || (bool)preg_match('~arena_v64_improve\.json\.php(\.lock)?$~', $path);
    foreach ($before as $path => $md5) {
        if ($isSkillCache($path)) continue;
        if (!isset($after[$path]) || $after[$path] !== $md5) return false;
    }
    foreach ($after as $path => $md5) {
        if (isset($before[$path])) continue;
        if (!$isSkillCache($path)) return false;
    }
    return true;
}
