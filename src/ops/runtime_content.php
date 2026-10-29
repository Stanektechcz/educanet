<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function runtime_content_cache_dir(): string
{
    return __DIR__ . '/cache/runtime';
}

function runtime_content_cache_path(string $classId): string
{
    $safe = preg_replace('/[^a-z0-9_\-]/i', '', $classId) ?: 'unknown';
    return runtime_content_cache_dir() . '/' . $safe . '.php';
}

function runtime_content_empty_bundle(): array
{
    return [
        'knowledgeTours' => [],
        'nextLessons' => [],
        'extendedLessons' => [],
        'simulations' => [],
        'learningResources' => [],
    ];
}

function runtime_content_load_classes(array $classIds): array
{
    $bundle = runtime_content_empty_bundle();
    foreach (array_values(array_unique(array_filter(array_map('strval', $classIds)))) as $classId) {
        $path = runtime_content_cache_path($classId);
        if (!is_file($path)) continue;
        $row = require $path;
        if (!is_array($row)) continue;
        $bundle['knowledgeTours'][$classId] = is_array($row['knowledgeTours'] ?? null) ? $row['knowledgeTours'] : [];
        if (is_array($row['nextLesson'] ?? null)) $bundle['nextLessons'][$classId] = $row['nextLesson'];
        $bundle['extendedLessons'][$classId] = is_array($row['extendedLessons'] ?? null) ? $row['extendedLessons'] : [];
        $bundle['simulations'][$classId] = is_array($row['simulations'] ?? null) ? $row['simulations'] : [];
        $bundle['learningResources'][$classId] = is_array($row['learningResources'] ?? null) ? $row['learningResources'] : [];
    }
    return $bundle;
}
