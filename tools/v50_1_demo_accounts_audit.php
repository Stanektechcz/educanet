<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/teacher_operations_v46.php';
require dirname(__DIR__) . '/teacher_demo_accounts.php';

$root = dirname(__DIR__);
$checks = [];
$check = static function (bool $ok, string $label) use (&$checks): void {
    $checks[] = [$ok, $label];
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};

$teacher = (string)file_get_contents($root . '/teacher.php');
$ops = (string)file_get_contents($root . '/teacher_operations_v46.php');
$module = (string)file_get_contents($root . '/teacher_demo_accounts.php');

$check(function_exists('teacher_demo_account_create'), 'create helper exists');
$check(function_exists('teacher_demo_account_reset_password'), 'password reset helper exists');
$check(function_exists('teacher_demo_account_delete'), 'delete helper exists');
$check(function_exists('teacher_demo_accounts_list'), 'list helper exists');
$check(str_contains($teacher, "'demo_accounts'"), 'teacher demo_accounts tab registered');
$check(str_contains($teacher, 'teacher_demo_account_create'), 'teacher create action wired');
$check(str_contains($teacher, 'teacher_demo_account_reset'), 'teacher reset action wired');
$check(str_contains($teacher, 'teacher_demo_account_delete'), 'teacher delete action wired');
$check(teacher_action_permission('teacher_demo_account_create') === 'students.manage' && teacher_action_permission('teacher_demo_account_reset') === 'students.manage', 'demo mutations require students.manage');
$check(str_contains($module, "empty(\$existing['is_test_account'])"), 'real account collision protection present');
$check(str_contains($module, "empty(\$current['is_test_account'])"), 'delete restricted to test accounts');
$check(str_contains($module, 'local_password_hash($password)'), 'password stored as hash');
$check(!str_contains($module, "'password' => \$password,\n        'created_at'"), 'plaintext password not stored in account record');
// v68: CSS cockpitu načítá teacher68_stylesheets_html() s verzí podle času změny souboru (filemtime) – cache key se mění sám.
$shell = (string)file_get_contents($root . '/teacher_shell_v68.php');
$check(str_contains($shell, "teacher68_link_html('assets/teacher.css')") && str_contains($shell, 'filemtime'), 'teacher CSS cache key bumped (version = filemtime)');

$failed = array_values(array_filter($checks, static fn(array $row): bool => !$row[0]));
if ($failed) {
    fwrite(STDERR, 'V50_1_DEMO_ACCOUNTS_AUDIT_FAILED=' . count($failed) . PHP_EOL);
    exit(1);
}
echo 'V50_1_DEMO_ACCOUNTS_AUDIT_OK checks=' . count($checks) . PHP_EOL;
