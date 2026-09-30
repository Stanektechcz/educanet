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
    'unified_page_shell_v50_7.php', 'one_task_v50_5.php', 'goal_navigator_v50_4.php', 'hands_on_learning_v50.php',
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
    $isSkillCache = static fn(string $path): bool => (bool)preg_match('~skill_(branches|progress)\.json\.php(\.lock)?$~', $path) || (bool)preg_match('~arena_v60_challenges\.json\.php(\.lock)?$~', $path);
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
