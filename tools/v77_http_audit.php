<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v77 · HTTP audit vrstvy: Středa T-14 (záložka, filtr třídy, odznak v menu, odkaz do Hromadného schválení s rozsahem t14,
 * schválení CSRF a pokles odznaku), Režim hodiny bez odkazu na vyřazené kity, žák (L1 po schválení: obsah, exit ticket, AI prompty z obsahu;
 * hlavička s LVL prstencem) a režim účtů (rozsah tříd 403, asistent jen čte).
 * Dočasné úložiště (edu_audit_temp_storage) a vývojový server přes tools/lib/http_harness.php. Konec: V77_HTTP_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v77-http')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V77H_KEY = 'v77h-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V77H_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php', 'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php',
    'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php', 'src/lessons/lesson_model_v71.php', 'src/lessons/lesson_approval_v72.php', 'src/assessment/svp_v72.php', 'src/assessment/svp_v72_map.php', 'src/teacher/bulk_approval_v74.php',
    'src/lessons/lesson_glossary_v72.php', 'src/teacher/ai_prompts_v72_1.php', 'src/teacher/ai_prompts_v76.php', 'src/teacher/ai_prompts_v77.php', 'src/teacher/t14_v77.php'] as $lib) require_once $ROOT . '/' . $lib;
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-'));

require __DIR__ . '/lib/v77_http.php';

exit(audit_summary($state, 'V77_HTTP'));
