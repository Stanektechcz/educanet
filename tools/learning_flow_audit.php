<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$index = edu_app_source() ?: '';
$js = file_get_contents($root . '/assets/app.js') ?: '';
$progress = file_get_contents($root . '/progress.php') ?: '';
$errors = [];

if (str_contains($index, 'data-kb-commit')) $errors[] = 'Knowledge Tour stále obsahuje ruční XP commit tlačítko.';
if (str_contains($index, 'data-next-complete')) $errors[] = 'Strukturovaná lekce stále obsahuje ruční tlačítko Ověřit a pokračovat.';
if (!str_contains($js, 'kb:visual-complete')) $errors[] = 'Chybí automatické dokončení vizuálního kroku.';
if (!str_contains($js, 'armDeepAutoComplete')) $errors[] = 'Chybí automatické dokončení Deep Dive po aktivním čtení.';
if (!str_contains($progress, "'awarded_events'")) $errors[] = 'Progress API nevrací detailní XP události.';
if (!str_contains($progress, "lessons_more.php")) $errors[] = 'Progress API nemá načtené Lekce 7.';
if (!str_contains($progress, "lessons_ecosystem.php")) $errors[] = 'Progress API nemá načtené Lekce 8–9.';
if (!str_contains($progress, "lessons_yearpack.php")) $errors[] = 'Progress API nemá načtené Lekce 10–18.';
if (!str_contains($progress, "knowledge_tours_yearpack.php")) $errors[] = 'Progress API nemá načtené v27 Knowledge Tours.';
if (!str_contains($progress, "simulations_yearpack.php")) $errors[] = 'Progress API nemá načtené v27 simulace.';

$files = ['next_lessons.php','extended_lessons.php','lessons_plus.php','lessons_more.php','lessons_ecosystem.php','lessons_yearpack.php'];
$totalSteps = 0;
$autoReady = 0;
foreach ($files as $file) {
    $data = require $root . '/' . $file;
    foreach ($data as $classId => $lessons) {
        $lessonList = isset($lessons['steps']) ? [$lessons] : $lessons;
        foreach ($lessonList as $lesson) {
            if (!is_array($lesson) || !is_array($lesson['steps'] ?? null)) continue;
            foreach ($lesson['steps'] as $step) {
                if (!is_array($step)) continue;
                $totalSteps++;
                $hasTasks = !empty($step['tasks']) && is_array($step['tasks']);
                $hasQuiz = !empty($step['question']) && is_array($step['options'] ?? null);
                if ($hasTasks || $hasQuiz) $autoReady++;
                else $errors[] = sprintf('%s / %s / %s nemá checklist ani quiz trigger pro automatické dokončení.', $classId, (string)($lesson['id'] ?? '?'), (string)($step['id'] ?? '?'));
            }
        }
    }
}

echo "Structured lesson steps: {$totalSteps}\n";
echo "Auto-submit ready: {$autoReady}\n";
if ($errors) {
    foreach ($errors as $error) echo "ERROR: {$error}\n";
    exit(1);
}
echo "OK: XP is server-awarded, detailed in API responses, and KB/course progression no longer requires claim-XP buttons.\n";
