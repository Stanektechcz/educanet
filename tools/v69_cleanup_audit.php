<?php

declare(strict_types=1);

/**
 * EDUCANET v69 · audit vyřazení: skryté učitelské záložky, režim One Task, rozdělená tmavá vrstva.
 *   1) vyřazené soubory neexistují na původním místě, kopie v retired/v69 (tools/legacy) mají očekávané SHA-256,
 *   2) tools/v69_retire.php odmítne neschválený soubor i cestu mimo projekt a v dry-run nic nemění,
 *   3) staré adresy dávají 302 (One Task, continue, v48_state; učitelské záložky), POST akce vyřazených záložek jsou zamítnuté (deny-by-default),
 *   4) v živém kódu nezůstal odkaz na vyřazené soubory, v69_task_url vede přímo na cílové pohledy,
 *   5) Linux Lab beze změny (hlídá v68_theme_audit / v67_ux_audit).
 *   php tools/v69_cleanup_audit.php        Konec: V69_CLEANUP_AUDIT_OK checks=N failed=0.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
edu_audit_temp_storage('v69-cleanup');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once $ROOT . '/app/redirects_v68.php';
require_once $ROOT . '/student_links_v69.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php'] as $lib) require_once $ROOT . '/' . $lib;
require_once __DIR__ . '/v69_retire.php';

const V69C_RETIRED_SHA = [
    'retired/v69/teacher_skill_views.php' => '71111f54c18d4322c1746157ff669d0897fc96d318d87b648e93a12869dad603',
    'retired/v69/app/views/one_task.php' => '800cdc06a5f00b57acfa11379ac0377af60fa84c6f3fd2cdc086d5851221ac55',
    'retired/v69/app/actions/one_task.php' => 'd9e027acc585394ec4d7511239957350070187673e321bf9acd025083bd1163b',
    'retired/v69/one_task_v50_5.php' => '90ebf12d9f964fa2af78e8ec114cdc0c85eed88523e95e16a6b54ed3d1021924',
    'retired/v69/assets/one-task-v50-5.css' => '1b8de1e7af9494155a92f52dccae7650fc4c21a456ad2453571488043cf6068e',
    'retired/v69/assets/one-task-v50-5.js' => 'ce26f685c589927ce90d8d9c80683676f7147abe0e43734c5ead0282160fa64b',
    'retired/v69/assets/one-task-v50-7-7.css' => '63f61d7f2e996b07dc3d88602c3c05ffe2d67d67d9f622f847ae939c2a59a8c2',
    'retired/v69/assets/student-dark-v68.css' => '29c1a451952ecaef459cea4855a5288598976d463c10319067d2d03ec01936f0',
    'tools/legacy/v50_5_one_task_audit.php' => '25381e92491cf3edb9fdbfc8a5747e1b9743e1f3925792a11e39c1bddcbb23d7',
];

$state = audit_counter();
$check = audit_checker($state);

// 1) soubory a kopie
$gone = [];
foreach (array_merge(RETIRE69_APPROVED, RETIRE69_LEGACY) as $rel) if (is_file($ROOT . '/' . $rel)) $gone[] = $rel;
$check('vyřazené soubory neexistují na původním místě (' . (count(RETIRE69_APPROVED) + count(RETIRE69_LEGACY)) . ')' . ($gone ? ' [' . implode(', ', $gone) . ']' : ''), $gone === []);
$badSha = [];
foreach (V69C_RETIRED_SHA as $rel => $sha) if (!is_file($ROOT . '/' . $rel) || !hash_equals($sha, (string)hash_file('sha256', $ROOT . '/' . $rel))) $badSha[] = $rel;
$check('kopie vyřazených souborů mají očekávané SHA-256 (' . count(V69C_RETIRED_SHA) . ')' . ($badSha ? ' [' . implode(', ', $badSha) . ']' : ''), $badSha === [] && count(V69C_RETIRED_SHA) === count(RETIRE69_APPROVED) + count(RETIRE69_LEGACY));

// 2) nástroj
$dry = retire69_apply($ROOT, 'bootstrap.php', RETIRE69_APPROVED, RETIRE69_LEGACY, true);
$out = retire69_apply($ROOT, '../x.php', RETIRE69_APPROVED, RETIRE69_LEGACY, true);
$store = retire69_apply($ROOT, 'storage/x.json.php', RETIRE69_APPROVED, RETIRE69_LEGACY, true);
$check('v69_retire: neschválený soubor, cesta mimo projekt a storage/ jsou odmítnuty; bootstrap.php zůstal', $dry['reason'] === 'not_approved' && $out['reason'] === 'bad_path' && $store['reason'] === 'forbidden_dir' && is_file($ROOT . '/bootstrap.php'));
$tmpRoot = sys_get_temp_dir() . '/educanet-audit-v69-retire-' . bin2hex(random_bytes(4));
mkdir($tmpRoot . '/tools', 0777, true);
file_put_contents($tmpRoot . '/a.txt', 'x');
file_put_contents($tmpRoot . '/tools/au_audit.php', 'y');
$r1 = retire69_apply($tmpRoot, 'a.txt', ['a.txt'], [], false);
$r2 = retire69_apply($tmpRoot, 'a.txt', ['a.txt'], [], true);
$r3 = retire69_apply($tmpRoot, 'tools/au_audit.php', [], ['tools/au_audit.php'], true);
$check('v69_retire: dry-run nic nemění; --apply = kopie → SHA-256 → smazání; audit jde do tools/legacy',
    $r1['reason'] === 'dryrun' && $r2['ok'] && !is_file($tmpRoot . '/a.txt') && is_file($tmpRoot . '/retired/v69/a.txt') && $r3['ok'] && is_file($tmpRoot . '/tools/legacy/au_audit.php') && !is_file($tmpRoot . '/tools/au_audit.php'));
edu_audit_remove_dir($tmpRoot);

// 3) přesměrování a deny-by-default
$redir = [
    routes68_redirect_target('one_task', 'GET', ['task' => 'course']),
    routes68_redirect_target('continue', 'GET', []),
    routes68_redirect_target('v48_state', 'GET', []),
    routes68_redirect_target('one_task', 'GET', ['task' => 'kb', 'topic' => 'ip_adresy']),
    routes68_redirect_target('one_task', 'POST', []),
];
$check('žák: ?view=one_task / continue / v48_state → 302 na přehled, kb → kb_lesson, POST se nepřesměrovává',
    $redir === ['?view=dashboard', '?view=dashboard', '?view=dashboard', '?view=kb_lesson&topic=ip_adresy', null]);
$tabs = teacher68_tab_redirects();
$check('učitel: starých 7 záložek má přesměrování (control, class_overview, growth, skills, mastery, filters, automations)',
    array_keys($tabs) === ['control', 'class_overview', 'growth', 'skills', 'mastery', 'filters', 'automations']);
$denied = [];
foreach (['skill_validation', 'skill_teacher_evidence', 'skill_assign', 'skill_unassign', 'ml_live_start', 'ml_live_phase', 'ml_failure_inject', 'v50_teacher_growth_control',
    'teacher_automation_save', 'teacher_automation_delete', 'teacher_automation_run', 'teacher_notification_read', 'teacher_notification_read_all', 'teacher_saved_filter_pin',
    'teacher_saved_filter_default', 'teacher_review_ack', 'teacher_sla_policy_save', 'v505_draft_save'] as $action) {
    if (empty(teacher59_action_policy($action)['deny'])) $denied[] = $action;
}
$check('POST akce vyřazených záložek jsou zamítnuté (deny-by-default)' . ($denied ? ' – povolené: ' . implode(', ', $denied) : ''), $denied === []);
$kept = [];
foreach (['teacher_saved_filter_save', 'teacher_saved_filter_delete', 'ml_scenario_save', 'ml_tip_state', 'teacher_ops_report_export'] as $action) if (!empty(teacher59_action_policy($action)['deny'])) $kept[] = $action;
$check('akce, které mají živé UI (uložení filtru ve výsledcích třídy, autorování, export), zůstaly povolené' . ($kept ? ' – zamítnuté: ' . implode(', ', $kept) : ''), $kept === []);
$check('teacher_scope: politika entity pro skill_evidence / skill_assignment / ml_live už neexistuje',
    in_array(teacher59_entity_classes('skill_evidence', 'x'), [null, []], true) && in_array(teacher59_entity_classes('ml_live', 'x'), [null, []], true));

// 4) odkazy v živém kódu
$stale = [];
$needles = ['one_task_v50_5', 'teacher_skill_views', 'app/views/one_task', 'actions/one_task', 'one-task-v50', 'student-dark-v68', 'v505_task_url', 'v505_local_href_to_task', 'v505_latest_draft'];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ROOT, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    $rel = substr(str_replace(chr(92), '/', $f->getPathname()), strlen($ROOT) + 1);
    if (preg_match('~^(storage|V1|retired|cache|docs|lang|database|tools|tests|\.claude)/|\.md$~', $rel) || !preg_match('~\.(php|js)$~', $rel)) continue;
    $src = (string)file_get_contents($f->getPathname());
    foreach ($needles as $n) if (str_contains($src, $n) && $rel !== 'app/redirects_v68.php') $stale[$rel . ':' . $n] = true;
}
$check('v živém kódu (mimo tools, tests, docs, lang) nezůstal odkaz na vyřazené soubory a funkce' . ($stale ? ' [' . implode(', ', array_keys($stale)) . ']' : ''), $stale === []);
$u = [
    v69_task_url('course', ['lesson' => 'next']), v69_task_url('course', ['lesson' => 'l3']), v69_task_url('kb', ['topic' => 'dns']), v69_task_url('skill', ['skill' => 'css-grid']),
    v69_task_url('growth', ['path' => 'webdesign']), v69_task_url('lab', ['lesson' => '4']), v69_task_url('result', ['record' => 'r1']), v69_task_url('neznamy', ['x' => '1']), v69_task_url('kb', []),
];
$check('v69_task_url míří přímo na cílové pohledy, neznámý druh a chybějící parametr na přehled', $u === ['?view=next_lesson', '?view=course_lesson&lesson=l3', '?view=kb_lesson&topic=dns', '?view=skill_detail&skill=css-grid',
    '?view=growth_path&path=webdesign', '?view=hands_on&lesson=4', '?view=project_result&record=r1', '?view=dashboard', '?view=dashboard']);
$sw = (string)file_get_contents($ROOT . '/sw.js');
$check('sw.js: bez přednačtení vyřazených assetů, cache verze v69', !str_contains($sw, 'one-task') && str_contains($sw, 'educanet-v69-ui'));
$routes = (string)file_get_contents($ROOT . '/app/routes.php');
$check('routes.php: žádná route ani skupina knihoven One Task; layout načítá student_links_v69.php', !str_contains($routes, 'one_task') && str_contains($routes, 'student_links_v69.php'));

exit(audit_summary($state, 'V69_CLEANUP'));
