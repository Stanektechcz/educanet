<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

putenv('EDUCANET_DEV_BYPASS=1');
require dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/hands_on_learning_v50.php';
require_once dirname(__DIR__) . '/independent_growth_v50.php';

function v50_assert(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}

$protected = [];
foreach (['storage','private','uploads','cache'] as $dir) {
    $path = dirname(__DIR__) . '/' . $dir;
    if (!is_dir($path)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $rel = substr($file->getPathname(), strlen(dirname(__DIR__)) + 1);
        $protected[$rel] = hash_file('sha256', $file->getPathname());
    }
}

$specs = v50_all_lab_specs();
v50_assert(count($specs) === 56, 'V50 musí mít 56 hands-on labů.');
$counts = ['class_3a'=>0,'class_4a'=>0];
$families = [];
foreach ($specs as $spec) {
    v50_assert(is_array($spec), 'Lab spec musí být pole.');
    $cid = (string)($spec['class_id'] ?? '');
    v50_assert(isset($counts[$cid]), 'Hands-on lab smí patřit jen 3.A/4.A.');
    $counts[$cid]++;
    $family = (string)($spec['family'] ?? '');
    v50_assert($family !== '', 'Lab musí mít problem family.');
    $families[$family] = true;
    v50_assert(count((array)($spec['topology']['nodes'] ?? [])) >= 3, 'Každý lab musí mít alespoň 3 uzly topologie.');
    v50_assert(count((array)($spec['allowed_commands'] ?? [])) >= 3, 'Každý lab musí mít allowlist příkazů.');
    v50_assert(count((array)($spec['hints'] ?? [])) === 4, 'Každý lab musí mít 4stupňový least-help-first hint ladder.');
    foreach (['grade_impact','xp_impact','mastery_impact','blocks_curriculum','can_be_required'] as $flag) {
        v50_assert(($spec['assessment'][$flag] ?? true) === false, 'Curriculum firewall selhal: '.$flag);
    }
}
v50_assert($counts['class_3a'] === 28 && $counts['class_4a'] === 28, 'Každá SOSaPS třída musí mít 28 hands-on labů.');
v50_assert(count($families) >= 15, 'Hands-on vrstva musí mít alespoň 15 problem families.');

$paths = v50_growth_catalog();
v50_assert(count($paths) >= 12, 'Growth musí mít alespoň 12 cest.');
$phpCards = v50_php_learning_cards();
v50_assert(count($phpCards) >= 22, 'PHP Learning Lab musí mít alespoň 22 vizuálních karet.');

$sample = reset($specs);
v50_assert(is_array($sample), 'Chybí sample lab.');
$state = v50_initial_sim_state($sample, 'audit-student');
$beforeState = $state;
$cmd = (string)($sample['allowed_commands'][0] ?? '');
v50_assert($cmd !== '', 'Sample lab nemá příkaz.');
$result = v50_simulate_command($sample, $state, $cmd);
v50_assert(is_array($result) && array_key_exists('output', $result), 'Simulator musí vracet output.');
v50_assert(isset($result['state']) && is_array($result['state']), 'Simulator musí vracet stav.');
v50_assert($beforeState !== [] && $result['state'] !== [], 'Simulator musí mít explicitní stav.');

$passport = v50_passport_from_events([]);
foreach (['principle','recognition','diagnosis','repair','explain','transfer'] as $dim) {
    v50_assert(array_key_exists($dim, $passport), 'Skill Passport postrádá dimenzi '.$dim);
}

$after = [];
foreach (['storage','private','uploads','cache'] as $dir) {
    $path = dirname(__DIR__) . '/' . $dir;
    if (!is_dir($path)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $rel = substr($file->getPathname(), strlen(dirname(__DIR__)) + 1);
        $after[$rel] = hash_file('sha256', $file->getPathname());
    }
}
v50_assert($protected === $after, 'Read-only audit nesmí změnit runtime data.');

echo 'V50_HANDS_ON_AUDIT_OK specs='.count($specs).' families='.count($families).' paths='.count($paths).' php_cards='.count($phpCards).PHP_EOL;
