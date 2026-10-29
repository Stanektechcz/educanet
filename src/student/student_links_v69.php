<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v69 · odkazy na jednotlivé kroky výuky.
 *
 * Nahrazuje odkazovou funkci vyřazeného režimu One Task: odkaz míří rovnou na cílový pohled
 * (lekce, vysvětlení, dovednost, rozvojová cesta, lab, výsledek projektu). Návratová adresa se
 * nepoužívá – cílové pohledy mají vlastní drobečkovou navigaci.
 */

/** Cílový pohled a název jeho parametru podle druhu kroku. @return array{0:string,1:string} */
function v69_task_target(string $task): array
{
    return match ($task) {
        'course' => ['course_lesson', 'lesson'],
        'kb' => ['kb_lesson', 'topic'],
        'skill' => ['skill_detail', 'skill'],
        'growth' => ['growth_path', 'path'],
        'lab' => ['hands_on', 'lesson'],
        'result' => ['project_result', 'record'],
        default => ['dashboard', ''],
    };
}

/**
 * Relativní URL cílového pohledu. Neznámý druh nebo chybějící parametr vede na přehled.
 * $returnUrl je tu jen kvůli souladu s původním podpisem a nepoužívá se.
 *
 * @param array<string,mixed> $params
 */
function v69_task_url(string $task, array $params = [], string $returnUrl = ''): string
{
    [$view, $key] = v69_task_target($task);
    if ($key === '') return module_url('dashboard');
    $value = isset($params[$key]) && is_scalar($params[$key]) ? trim((string)$params[$key]) : '';
    if ($value === '') return module_url('dashboard');
    if ($task === 'course' && $value === 'next') return module_url('next_lesson');
    return module_url($view, [$key => u_substr($value, 0, 160)]);
}
