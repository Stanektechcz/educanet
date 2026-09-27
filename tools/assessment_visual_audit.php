<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/assessment_visuals.php';

$validKinds = ['dns','dhcp','packet','route','ssh','service','probe','decision','contrast','hierarchy','typography','layout','raster','assets','crop'];
$errors = [];
$questionCount = 0;
$materialCount = 0;
$kindCounts = [];

foreach ($modules as $classId => $module) {
    foreach (($module['questions'] ?? []) as $q) {
        if (!is_array($q)) continue;
        $questionCount++;
        $spec = assessment_visual_spec((string)$classId, (string)($q['kb'] ?? $q['id'] ?? ''), (string)($q['question'] ?? ''));
        $kind = (string)($spec['kind'] ?? '');
        $kindCounts[$kind] = ($kindCounts[$kind] ?? 0) + 1;
        if (!in_array($kind, $validKinds, true)) {
            $errors[] = "Question {$classId}/" . ($q['id'] ?? '?') . " uses unsupported/generic visual kind: {$kind}";
        }
        foreach (['title','concept','prompt','evidence'] as $field) {
            if (trim((string)($spec[$field] ?? '')) === '') $errors[] = "Question {$classId}/" . ($q['id'] ?? '?') . " missing visual {$field}";
        }
    }
    foreach (($module['knowledgebase'] ?? []) as $topic => $article) {
        $materialCount++;
        $summary = is_array($article) ? (string)($article['summary'] ?? '') : '';
        $spec = assessment_visual_spec((string)$classId, (string)$topic, $summary);
        $kind = (string)($spec['kind'] ?? '');
        $kindCounts[$kind] = ($kindCounts[$kind] ?? 0) + 1;
        if (!in_array($kind, $validKinds, true)) {
            $errors[] = "Material {$classId}/{$topic} uses unsupported/generic visual kind: {$kind}";
        }
        foreach (['title','concept','prompt','evidence'] as $field) {
            if (trim((string)($spec[$field] ?? '')) === '') $errors[] = "Material {$classId}/{$topic} missing visual {$field}";
        }
    }
}

ksort($kindCounts);
echo "Assessment questions: {$questionCount}\n";
echo "Knowledge materials: {$materialCount}\n";
echo "Visual kinds: " . json_encode($kindCounts, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\n";
if ($errors) {
    foreach ($errors as $error) echo "ERROR: {$error}\n";
    exit(1);
}
echo "OK: every assessment/material maps to a concrete interactive visual with title, concept, prompt and evidence.\n";
