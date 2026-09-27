<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
require $root . '/bootstrap.php';

$next = require $root . '/next_lessons.php';
$extended = require $root . '/extended_lessons.php';
foreach (['lessons_plus.php','lessons_more.php','lessons_ecosystem.php','lessons_yearpack.php'] as $file) {
    $data = require $root . '/' . $file;
    foreach ($data as $classId => $lessons) {
        if (!isset($extended[$classId])) $extended[$classId] = [];
        $extended[$classId] = array_merge($extended[$classId], is_array($lessons) ? $lessons : []);
    }
}

$yearKnowledge = require $root . '/knowledge_extensions_yearpack.php';
$yearTours = require $root . '/knowledge_tours_yearpack.php';
$yearSims = require $root . '/simulations_yearpack.php';

$expect = [
    'class_1a' => ['name' => '1.A', 'subject_contains' => ['Grafika', 'webdesign']],
    'class_2a' => ['name' => '2.A', 'subject_contains' => ['Grafika', 'webdesign']],
    'class_3a' => ['name' => '3.A', 'subject_contains' => ['Seminář', 'OS', 'sítě']],
    'class_4a' => ['name' => '4.A', 'subject_contains' => ['Seminář', 'OS', 'sítě']],
];
$errors = [];

foreach ($expect as $classId => $cfg) {
    $module = $modules[$classId] ?? null;
    if (!is_array($module)) {
        $errors[] = "$classId: chybí modul.";
        continue;
    }
    $subject = (string)($module['subject'] ?? '');
    foreach ($cfg['subject_contains'] as $needle) {
        if (stripos($subject, $needle) === false) {
            $errors[] = "$classId: předmět '$subject' neobsahuje '$needle'.";
        }
    }
    if (!isset($next[$classId])) $errors[] = "$classId: chybí lekce 2.";
    $extras = array_values(array_filter($extended[$classId] ?? [], 'is_array'));
    $numbers = array_map(static fn(array $l): int => (int)($l['number'] ?? 0), $extras);
    sort($numbers);
    $expectedNumbers = range(3, 18);
    if ($numbers !== $expectedNumbers) {
        $errors[] = "$classId: očekávány rozšířené lekce 3–18, nalezeno " . implode(',', $numbers) . '.';
    }
    $total = 1 + (isset($next[$classId]) ? 1 : 0) + count($extras);
    if ($total !== 18) $errors[] = "$classId: očekáváno 18 lekcí, nalezeno $total.";

    $newLessons = array_values(array_filter($extras, static fn(array $l): bool => (int)($l['number'] ?? 0) >= 10));
    if (count($newLessons) !== 9) $errors[] = "$classId: v27 má mít 9 nových lekcí (10–18).";
    foreach ($newLessons as $lesson) {
        $id = (string)($lesson['id'] ?? '');
        $steps = $lesson['steps'] ?? [];
        $knowledge = $lesson['knowledge'] ?? [];
        if ($id === '' || !is_array($steps) || count($steps) < 3) $errors[] = "$classId: nekompletní v27 lekce '$id'.";
        if (!is_array($knowledge) || !$knowledge) $errors[] = "$classId/$id: chybí knowledge vazby.";
    }

    $kbCount = count($yearKnowledge[$classId] ?? []);
    $tourCount = count($yearTours[$classId] ?? []);
    $simCount = count($yearSims[$classId] ?? []);
    if ($kbCount < 7) $errors[] = "$classId: příliš málo nových KB materiálů ($kbCount).";
    if ($tourCount !== $kbCount) $errors[] = "$classId: Knowledge Tours ($tourCount) neodpovídají KB ($kbCount).";
    if ($simCount !== $kbCount) $errors[] = "$classId: simulace ($simCount) neodpovídají KB ($kbCount).";

    $materialDir = $root . '/materials/' . $classId;
    foreach (['COURSE_MAP.md','TEACHER_GUIDE.md','STUDENT_WORKBOOK.md','ASSESSMENT_BANK.md','PROJECT_BRIEFS.md'] as $file) {
        $path = $materialDir . '/' . $file;
        if (!is_file($path) || filesize($path) < 200) $errors[] = "$classId: chybí/nekompletní materials/$classId/$file.";
    }

    echo sprintf("%s · %s · 18 lessons · v27 KB %d · tours %d · simulations %d\n", $cfg['name'], $subject, $kbCount, $tourCount, $simCount);
}

if ($errors) {
    foreach ($errors as $error) echo "ERROR: $error\n";
    exit(1);
}

echo "OK: v27 curriculum is class-correct, complete to lesson 18 and backed by teacher/student materials.\n";
