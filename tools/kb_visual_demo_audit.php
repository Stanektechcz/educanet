<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/bootstrap.php';

$tours = require dirname(__DIR__) . '/knowledge_tours.php';
foreach (['knowledge_tours_next.php','knowledge_tours_plus.php','knowledge_tours_more.php','knowledge_tours_ecosystem.php','knowledge_tours_yearpack.php','knowledge_tours_v30.php'] as $file) {
    $extra = require dirname(__DIR__) . '/' . $file;
    foreach ($extra as $classId => $items) {
        $tours[$classId] = array_replace($tours[$classId] ?? [], is_array($items) ? $items : []);
    }
}
if (isset($tours['class_2a'])) {
    $tours['class_1a'] = array_replace($tours['class_2a'], $tours['class_1a'] ?? []);
}

$supported = [
    'assets' => 'step', 'color' => 'live', 'compare' => 'step', 'export' => 'live',
    'flow' => 'step', 'grid' => 'live', 'layers' => 'step', 'permissions' => 'step',
    'pixels' => 'live', 'ports' => 'step', 'poster' => 'live', 'sequence' => 'step', 'subnet' => 'step',
];

$seen = [];
$total = 0;
$errors = [];
foreach ($modules as $classId => $module) {
    foreach ((array)($module['knowledgebase'] ?? []) as $topic => $_article) {
        $total++;
        $tour = $tours[$classId][$topic] ?? null;
        if (!is_array($tour)) { $errors[] = "$classId/$topic: chybí Knowledge Tour"; continue; }
        $type = (string)($tour['visual']['type'] ?? '');
        if ($type === '') { $errors[] = "$classId/$topic: chybí visual.type"; continue; }
        $seen[$type] = ($seen[$type] ?? 0) + 1;
        if (!isset($supported[$type])) $errors[] = "$classId/$topic: nepodporovaný vizuální typ '$type'";
    }
}
ksort($seen);
echo "Knowledge materials: $total\n";
echo 'Visual types: ' . json_encode($seen, JSON_UNESCAPED_UNICODE) . "\n";
echo 'Live labs: ' . implode(', ', array_keys(array_filter($supported, static fn(string $m): bool => $m === 'live'))) . "\n";
echo 'Step models: ' . implode(', ', array_keys(array_filter($supported, static fn(string $m): bool => $m === 'step'))) . "\n";
if ($errors) { foreach ($errors as $error) fwrite(STDERR, "ERROR: $error\n"); exit(1); }
echo "OK: all {$total} materials use a supported interactive visual renderer.\n";
