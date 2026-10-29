<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v76 · HTTP audit vrstvy: cockpit (Plán vs. skutečnost, Plnění ŠVP, pozorování jedním klikem, tisk a CSV, CSRF 419,
 * rozsah tříd a role v režimu účtů) a žák (grafické úlohy 1.A, export portfolia s připnutými pracemi).
 * Dočasné úložiště (edu_audit_temp_storage) a vývojový server přes tools/lib/http_harness.php. Konec: V76_HTTP_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v76-http')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V76H_KEY = 'v76h-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V76H_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php', 'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php',
    'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php', 'src/assessment/competencies_v62.php', 'src/assessment/evidence_v62.php', 'src/paths/paths_v63.php', 'src/assessment/taskbank_v75_gen.php', 'src/assessment/taskbank_v75.php', 'src/student/portfolio_v75.php'] as $lib) require_once $ROOT . '/' . $lib;
require_once __DIR__ . '/lib/v59_scope_fixtures.php';
require_once __DIR__ . '/lib/v63_paths_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-'));
v63fx_seed($tmp);
$label3 = V63FX_LABELS['class_3a']['a'];
$label1 = V63FX_LABELS['class_1a']['a'];
$sid3 = v63fx_sid('class_3a', 'a');
$check('fixture: žáci 3.A a 1.A mají stabilní identitu (student_id)', preg_match('/^stu_[0-9a-f]{16}$/', $sid3) === 1 && preg_match('/^stu_[0-9a-f]{16}$/', v63fx_sid('class_1a', 'a')) === 1);

require __DIR__ . '/lib/v76_http.php';

exit(audit_summary($state, 'V76_HTTP'));
