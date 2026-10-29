<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

// v59 OPS-02: tr() i při načtení bez bootstrap.php (CLI audity).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v48.1 · 3.A Deep Visual Labs
 *
 * Content-heavy, lesson-specific practical labs layered on top of v48.
 * All evidence is formative: never grades, XP or mastery.
 */

function v481_content_path(): string
{
    return __DIR__ . '/materials/v48_1_3a_visual_labs.json';
}

function v481_3a_specs(): array
{
    static $cache = null;
    if (is_array($cache)) return $cache;
    $raw = @file_get_contents(v481_content_path());
    if (!is_string($raw) || $raw === '') throw new RuntimeException('Chybí obsah 3.A Deep Visual Labs.');
    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !is_array($decoded['labs'] ?? null)) throw new RuntimeException('Obsah 3.A Deep Visual Labs je neplatný.');
    $labs = [];
    foreach ($decoded['labs'] as $number => $spec) {
        $n = (int)$number;
        if ($n < 1 || $n > 28 || !is_array($spec)) continue;
        $spec['lesson_number'] = $n;
        $spec['id'] = 'class_3a-L' . str_pad((string)$n, 2, '0', STR_PAD_LEFT) . '-deep-v48-1';
        $spec['class_id'] = 'class_3a';
        $spec['version'] = '48.1';
        $spec['assessment'] = ['grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'mode'=>'formative'];
        $labs[$n] = $spec;
    }
    ksort($labs);
    return $cache = $labs;
}

function v481_3a_spec(int $lessonNumber): ?array
{
    $spec = v481_3a_specs()[$lessonNumber] ?? null;
    return is_array($spec) ? $spec : null;
}

function v481_available_for(string $classId, array $lesson): bool
{
    return $classId === 'class_3a' && v481_3a_spec((int)($lesson['number'] ?? 0)) !== null;
}

function v481_mode(string $classId, string $studentKey, array $lesson, ?string $requested = null): string
{
    $requested = strtolower(trim((string)$requested));
    if (in_array($requested, ['guided','practice','challenge'], true)) return $requested;
    $lane = v42_learning_lane($classId, $studentKey, $lesson);
    return match ((string)($lane['lane'] ?? 'standard')) {
        'guided' => 'guided',
        'challenge' => 'challenge',
        default => 'practice',
    };
}

function v481_mode_label(string $mode): string
{
    return match ($mode) {
        'guided' => tr('Guided · více opory'),
        'challenge' => tr('Challenge · minimum opory'),
        default => tr('Practice · vyváženě'),
    };
}

function v481_event_rows(): array
{
    return adaptive_store('v481_deep_lab_events');
}

function v481_latest_attempt(string $classId, string $studentKey, int $lessonNumber): ?array
{
    $latest = null;
    foreach (v481_event_rows() as $row) {
        if (!is_array($row)) continue;
        if ((string)($row['class_id'] ?? '') !== $classId || (string)($row['student_key'] ?? '') !== $studentKey || (int)($row['lesson_number'] ?? 0) !== $lessonNumber) continue;
        if ($latest === null || strcmp((string)($row['created_at'] ?? ''), (string)($latest['created_at'] ?? '')) > 0) $latest = $row;
    }
    return is_array($latest) ? $latest : null;
}

function v481_norm_scalar(mixed $value): string
{
    $v = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$value)) ?? '');
    return function_exists('mb_strtolower') ? mb_strtolower($v, 'UTF-8') : strtolower($v);
}

function v481_norm_list(mixed $value): array
{
    $rows = is_array($value) ? $value : [$value];
    $out = [];
    foreach ($rows as $v) {
        if (is_array($v)) continue;
        $n = v481_norm_scalar($v);
        if ($n !== '') $out[] = $n;
    }
    return $out;
}

function v481_validate_task(array $task, mixed $answer): array
{
    $type = (string)($task['type'] ?? 'choice');
    $correct = false;
    $normalized = null;
    if ($type === 'choice') {
        $normalized = v481_norm_scalar($answer);
        $correct = $normalized !== '' && $normalized === v481_norm_scalar($task['expected'] ?? '');
    } elseif ($type === 'sequence') {
        $normalized = v481_norm_list($answer);
        $expected = v481_norm_list($task['expected'] ?? []);
        $correct = $normalized === $expected && $expected !== [];
    } elseif ($type === 'multi') {
        $normalized = array_values(array_unique(v481_norm_list($answer)));
        $expected = array_values(array_unique(v481_norm_list($task['expected'] ?? [])));
        sort($normalized); sort($expected);
        $correct = $normalized === $expected && $expected !== [];
    } elseif ($type === 'pair') {
        $normalized = [];
        if (is_array($answer)) {
            foreach ($answer as $left => $right) $normalized[v481_norm_scalar($left)] = v481_norm_scalar($right);
        }
        $expected = [];
        foreach ((array)($task['expected'] ?? []) as $left => $right) $expected[v481_norm_scalar($left)] = v481_norm_scalar($right);
        ksort($normalized); ksort($expected);
        $correct = $normalized === $expected && $expected !== [];
    } elseif ($type === 'number') {
        $raw = is_scalar($answer) ? str_replace(',', '.', trim((string)$answer)) : '';
        $number = is_numeric($raw) ? (float)$raw : null;
        $target = (float)($task['expected'] ?? 0);
        $tol = max(0.0, (float)($task['tolerance'] ?? 0));
        $normalized = $number;
        $correct = $number !== null && abs($number - $target) <= $tol;
    } elseif ($type === 'command') {
        $normalized = v481_norm_scalar($answer);
        $accepted = array_map('v481_norm_scalar', (array)($task['accepted'] ?? []));
        $correct = $normalized !== '' && in_array($normalized, $accepted, true);
    } elseif ($type === 'matrix') {
        $normalized = array_values(array_unique(v481_norm_list($answer)));
        $expected = array_values(array_unique(v481_norm_list($task['expected'] ?? [])));
        sort($normalized); sort($expected);
        $correct = $normalized === $expected && $expected !== [];
    }
    return [
        'id'=>(string)($task['id'] ?? ''),
        'type'=>$type,
        'correct'=>$correct,
        'why'=>(string)($task['why'] ?? ''),
        'normalized'=>$normalized,
    ];
}

function v481_validate_answers(array $spec, array $answers): array
{
    $results = [];
    $score = 0;
    $total = 0;
    foreach ((array)($spec['tasks'] ?? []) as $task) {
        if (!is_array($task) || trim((string)($task['id'] ?? '')) === '') continue;
        $id = (string)$task['id'];
        $result = v481_validate_task($task, $answers[$id] ?? null);
        $results[$id] = $result;
        $total++;
        if (!empty($result['correct'])) $score++;
    }
    return ['score'=>$score,'total'=>$total,'all_correct'=>$total > 0 && $score === $total,'results'=>$results];
}

function v481_clean_answer_snapshot(mixed $answer): mixed
{
    if (is_array($answer)) {
        $out = [];
        foreach (array_slice($answer, 0, 30, true) as $k => $v) {
            $key = is_string($k) ? u_substr(trim(strip_tags($k)), 0, 120) : $k;
            if (is_array($v)) $out[$key] = v481_clean_answer_snapshot($v);
            elseif (is_scalar($v)) $out[$key] = u_substr(trim(strip_tags((string)$v)), 0, 400);
        }
        return $out;
    }
    return is_scalar($answer) ? u_substr(trim(strip_tags((string)$answer)), 0, 500) : '';
}

function v481_submit(string $classId, string $studentKey, array $lesson, string $mode, array $answers, string $reflection): array
{
    if ($classId !== 'class_3a') throw new RuntimeException('Deep Visual Labs v48.1 jsou v této verzi připravené pro 3.A.');
    $lessonNumber = (int)($lesson['number'] ?? 0);
    $spec = v481_3a_spec($lessonNumber);
    if (!$spec) throw new RuntimeException('Deep Visual Lab nebyl nalezen.');
    $mode = in_array($mode, ['guided','practice','challenge'], true) ? $mode : 'practice';
    $evaluation = v481_validate_answers($spec, $answers);
    $reflection = trim(strip_tags($reflection));
    $reflectionOk = u_strlen($reflection) >= 24;
    $id = 'v481_' . bin2hex(random_bytes(7));
    $resultFlags = [];
    foreach ($evaluation['results'] as $taskId => $result) $resultFlags[(string)$taskId] = !empty($result['correct']);
    $row = [
        'id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>$lessonNumber,
        'mode'=>$mode,'score'=>(int)$evaluation['score'],'total'=>(int)$evaluation['total'],
        'reflection_ok'=>$reflectionOk,'completed'=>!empty($evaluation['all_correct']) && $reflectionOk,
        'results'=>$resultFlags,'answers'=>v481_clean_answer_snapshot($answers),'reflection'=>u_substr($reflection,0,1400),
        'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'created_at'=>date(DATE_ATOM),
    ];
    adaptive_store_update('v481_deep_lab_events', static function (array $rows) use ($id, $row): array {
        $rows[$id] = $row;
        if (count($rows) > 6000) $rows = array_slice($rows, -5000, null, true);
        return $rows;
    });
    return ['row'=>$row,'evaluation'=>$evaluation];
}

function v481_student_best(string $classId, string $studentKey, int $lessonNumber): ?array
{
    $best = null;
    foreach (v481_event_rows() as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId || (string)($row['student_key'] ?? '') !== $studentKey || (int)($row['lesson_number'] ?? 0) !== $lessonNumber) continue;
        $pct = (int)($row['total'] ?? 0) > 0 ? ((int)$row['score'] / (int)$row['total']) : 0;
        $bestPct = is_array($best) && (int)($best['total'] ?? 0) > 0 ? ((int)$best['score'] / (int)$best['total']) : -1;
        if ($pct > $bestPct || ($pct === $bestPct && strcmp((string)($row['created_at'] ?? ''),(string)($best['created_at'] ?? '')) > 0)) $best = $row;
    }
    return is_array($best) ? $best : null;
}

function v481_teacher_summary(string $classId, int $lessonNumber): array
{
    if ($classId !== 'class_3a') return ['attempted'=>0,'completed'=>0,'avg_percent'=>0,'modes'=>[]];
    $byStudent = [];
    foreach (v481_event_rows() as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId || (int)($row['lesson_number'] ?? 0) !== $lessonNumber) continue;
        $student = (string)($row['student_key'] ?? ''); if ($student === '') continue;
        if (!isset($byStudent[$student]) || strcmp((string)($row['created_at'] ?? ''),(string)($byStudent[$student]['created_at'] ?? '')) > 0) $byStudent[$student] = $row;
    }
    $completed=0;$sum=0.0;$modes=['guided'=>0,'practice'=>0,'challenge'=>0];
    foreach ($byStudent as $row) {
        if (!empty($row['completed'])) $completed++;
        $total=max(1,(int)($row['total']??0));$sum+=(int)($row['score']??0)/$total;
        $mode=(string)($row['mode']??'practice');if(isset($modes[$mode]))$modes[$mode]++;
    }
    $attempted=count($byStudent);
    return ['attempted'=>$attempted,'completed'=>$completed,'avg_percent'=>$attempted?(int)round(($sum/$attempted)*100):0,'modes'=>$modes];
}

function v481_expected_answer(array $task): mixed
{
    return match ((string)($task['type'] ?? '')) {
        'command' => (array)($task['accepted'] ?? []) ? (string)((array)$task['accepted'])[0] : '',
        default => $task['expected'] ?? null,
    };
}
