<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v75 · HTTP audit vrstvy: žák (úlohy s variantami, úroveň v lekci, portfolio, opakování, cesty 2.A, en/uk) a cockpit
 * (5 záložek, schválení, komentář, zveřejnění, stránky k tisku, CSRF 419, rozsah tříd a role v režimu účtů).
 * Dočasné úložiště (edu_audit_temp_storage) a vývojový server přes tools/lib/http_harness.php. Konec: V75_HTTP_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v75-http')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V75H_KEY = 'v75h-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V75H_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php', 'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php',
    'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (['kompetence75', 'ulohy75', 'urovne75', 'portfolia75', 'opakovani75'] as $m) foreach ((array)$mods[$m]['files'] as $f) require_once $ROOT . '/' . $f;
require_once $ROOT . '/src/paths/paths_v63.php';
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-'));
$students3 = array_values(project_students_for_class('class_3a'));
$label3 = (string)($students3[0]['label'] ?? '');
$key3 = (string)(array_keys(project_students_for_class('class_3a'))[0] ?? '');
$students2 = array_values(project_students_for_class('class_2a'));
$label2 = (string)($students2[0]['label'] ?? '');
$check('fixture: třídy 3.A a 2.A mají žáky v seznamu', $label3 !== '' && $label2 !== '' && $key3 !== '');

require __DIR__ . '/lib/v75_http.php';

exit(audit_summary($state, 'V75_HTTP'));
