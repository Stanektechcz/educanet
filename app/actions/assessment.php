<?php

declare(strict_types=1);

/**
 * POST startovní test a praktická laboratoř (vyžaduje třídu).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'start_test') {
    $_SESSION['next_test'] = [
        'active' => true,
        'class_id' => $classId,
        'index' => 0,
        'correct' => 0,
        'answers' => [],
        'feedback' => null,
        'option_orders' => build_test_option_orders((array)($module['questions'] ?? [])),
        'started_at' => date(DATE_ATOM),
    ];
    unset($_SESSION['next_result']);
    redirect_to('?view=test');
}

if ($action === 'answer') {
    $test = active_test();
    if (!$test || $test['class_id'] !== $classId) {
        redirect_to('?view=dashboard');
    }
    if (!empty($test['feedback'])) {
        redirect_to('?view=test');
    }
    $index = (int)$test['index'];
    $question = $module['questions'][$index] ?? null;
    if (!is_array($question)) {
        redirect_to('?view=dashboard');
    }
    $selected = isset($_POST['answer']) && is_string($_POST['answer']) ? $_POST['answer'] : '';
    if (!array_key_exists($selected, $question['options'])) {
        $_SESSION['flash'] = tr('Vyber jednu odpověď.');
        redirect_to('?view=test');
    }
    $isCorrect = hash_equals((string)$question['correct'], $selected);
    if ($isCorrect) {
        $test['correct']++;
        learning_award_once($classId, 'test_correct:' . (string)$question['id'], 10);
    }
    $test['answers'][] = [
        'question_id' => $question['id'],
        'selected' => $selected,
        'correct' => $isCorrect,
    ];
    $test['feedback'] = [
        'correct' => $isCorrect,
        'selected' => $selected,
        'correct_key' => $question['correct'],
        'explanation' => $question['explanation'],
    ];
    $_SESSION['next_test'] = $test;
    redirect_to('?view=test');
}

if ($action === 'continue_test') {
    $test = active_test();
    if (!$test || $test['class_id'] !== $classId) {
        redirect_to('?view=dashboard');
    }
    if (empty($test['feedback'])) {
        redirect_to('?view=test');
    }
    $test['feedback'] = null;
    $test['index'] = (int)$test['index'] + 1;
    if ($test['index'] >= count($module['questions'])) {
        $test['active'] = false;
        $finishedAt = date(DATE_ATOM);
        $result = [
            'id' => result_id(),
            'class_id' => $classId,
            'student_label' => (string)($_SESSION['student_label'] ?? 'Anonymní student'),
            'student_email' => (string)(auth_user()['email'] ?? ''),
            'auth_key' => (string)(auth_user()['auth_key'] ?? ''),
            'google_sub' => ((auth_user()['provider'] ?? '') === 'google') ? (string)(auth_user()['sub'] ?? '') : '',
            'started_at' => $test['started_at'],
            'finished_at' => $finishedAt,
            'score' => (int)$test['correct'],
            'max_score' => count($module['questions']),
            'answers' => $test['answers'],
        ];
        storage_append('practice_results', $result);
        learning_award_once($classId, 'test_complete', 25);
        $_SESSION['next_result'] = $result;
        $_SESSION['next_test'] = $test;
        redirect_to('?view=result');
    }
    $_SESSION['next_test'] = $test;
    redirect_to('?view=test');
}

if ($action === 'abort_test') {
    unset($_SESSION['next_test']);
    $_SESSION['flash'] = tr('Test byl ukončen bez uložení výsledku.');
    redirect_to('?view=dashboard');
}

if ($action === 'start_practice') {
    if (empty($module['practice']) || !is_array($module['practice'])) {
        redirect_to('?view=dashboard');
    }
    $testResult = $_SESSION['next_result'] ?? null;
    if (!is_array($testResult) || (($testResult['class_id'] ?? null) !== $classId)) {
        $_SESSION['flash'] = tr('Nejdřív dokonči startovní test.');
        redirect_to('?view=dashboard');
    }
    if (!empty($module['practice']['case_study']) && !learning_journey_step_done($classId, 'case_study')) {
        $_SESSION['flash'] = tr('Před praktickou laboratoří projdi briefing případové studie.');
        redirect_to('?view=case_study');
    }
    if (active_test()) {
        $_SESSION['flash'] = tr('Nejdřív dokonči nebo ukonči startovní test.');
        redirect_to('?view=test');
    }
    $_SESSION['next_practice'] = [
        'active' => true,
        'class_id' => $classId,
        'task_index' => 0,
        'step_index' => 0,
        'answers' => [],
        'feedback' => null,
        'hint_level' => 0,
        'current_attempts' => 0,
        'started_at' => date(DATE_ATOM),
    ];
    unset($_SESSION['next_practice_result']);
    redirect_to('?view=practice');
}

if ($action === 'practice_hint') {
    $practiceState = active_practice();
    if (!$practiceState || $practiceState['class_id'] !== $classId || empty($module['practice'])) {
        redirect_to('?view=dashboard');
    }
    $task = $module['practice']['tasks'][(int)$practiceState['task_index']] ?? null;
    $step = is_array($task) ? ($task['steps'][(int)$practiceState['step_index']] ?? null) : null;
    if (!is_array($step)) {
        redirect_to('?view=practice');
    }
    $maxHints = count($step['hints'] ?? []);
    $practiceState['hint_level'] = min($maxHints, (int)$practiceState['hint_level'] + 1);
    $_SESSION['next_practice'] = $practiceState;
    redirect_to('?view=practice#checkpoint');
}

if ($action === 'practice_answer') {
    $practiceState = active_practice();
    if (!$practiceState || $practiceState['class_id'] !== $classId || empty($module['practice'])) {
        redirect_to('?view=dashboard');
    }
    if (!empty($practiceState['feedback']['correct'])) {
        redirect_to('?view=practice#checkpoint');
    }
    $task = $module['practice']['tasks'][(int)$practiceState['task_index']] ?? null;
    $step = is_array($task) ? ($task['steps'][(int)$practiceState['step_index']] ?? null) : null;
    if (!is_array($step)) {
        redirect_to('?view=practice');
    }
    $selected = isset($_POST['answer']) && is_string($_POST['answer']) ? $_POST['answer'] : '';
    if (!array_key_exists($selected, $step['options'])) {
        $_SESSION['flash'] = tr('Vyber jednu odpověď.');
        redirect_to('?view=practice#checkpoint');
    }
    $practiceState['current_attempts'] = (int)$practiceState['current_attempts'] + 1;
    $isCorrect = hash_equals((string)$step['correct'], $selected);
    $practiceState['feedback'] = [
        'correct' => $isCorrect,
        'selected' => $selected,
        'explanation' => (string)$step['explanation'],
    ];
    if ($isCorrect) {
        learning_award_once($classId, 'practice:' . (string)$task['id'] . ':' . (string)$practiceState['step_index'], 15);
        $practiceState['answers'][] = [
            'task_id' => (string)$task['id'],
            'step_index' => (int)$practiceState['step_index'],
            'selected' => $selected,
            'attempts' => (int)$practiceState['current_attempts'],
            'hints_used' => (int)$practiceState['hint_level'],
            'kb' => (string)($step['kb'] ?? ''),
        ];
    }
    $_SESSION['next_practice'] = $practiceState;
    redirect_to('?view=practice#checkpoint');
}

if ($action === 'continue_practice') {
    $practiceState = active_practice();
    if (!$practiceState || $practiceState['class_id'] !== $classId || empty($module['practice'])) {
        redirect_to('?view=dashboard');
    }
    if (empty($practiceState['feedback']['correct'])) {
        redirect_to('?view=practice#checkpoint');
    }
    $tasks = $module['practice']['tasks'];
    $taskIndex = (int)$practiceState['task_index'];
    $stepIndex = (int)$practiceState['step_index'];
    $task = $tasks[$taskIndex] ?? null;
    if (!is_array($task)) {
        redirect_to('?view=dashboard');
    }
    $stepIndex++;
    if ($stepIndex >= count($task['steps'])) {
        $taskIndex++;
        $stepIndex = 0;
    }
    $practiceState['feedback'] = null;
    $practiceState['hint_level'] = 0;
    $practiceState['current_attempts'] = 0;
    $practiceState['task_index'] = $taskIndex;
    $practiceState['step_index'] = $stepIndex;

    if ($taskIndex >= count($tasks)) {
        $practiceState['active'] = false;
        $finishedAt = date(DATE_ATOM);
        $practiceResult = [
            'id' => 'lab_' . bin2hex(random_bytes(8)),
            'class_id' => $classId,
            'student_label' => (string)($_SESSION['student_label'] ?? 'Anonymní student'),
            'student_email' => (string)(auth_user()['email'] ?? ''),
            'auth_key' => (string)(auth_user()['auth_key'] ?? ''),
            'google_sub' => ((auth_user()['provider'] ?? '') === 'google') ? (string)(auth_user()['sub'] ?? '') : '',
            'started_at' => $practiceState['started_at'],
            'finished_at' => $finishedAt,
            'completed_steps' => count($practiceState['answers']),
            'answers' => $practiceState['answers'],
        ];
        storage_append('lab_results', $practiceResult);
        learning_award_once($classId, 'practice:complete', 50);
        learning_refresh_badges($classId, $module);
        $_SESSION['next_practice_result'] = $practiceResult;
        $_SESSION['next_practice'] = $practiceState;
        redirect_to('?view=practice_done');
    }
    $_SESSION['next_practice'] = $practiceState;
    redirect_to('?view=practice');
}

if ($action === 'abort_practice') {
    unset($_SESSION['next_practice']);
    $_SESSION['flash'] = tr('Praktická část byla ukončena. Můžeš ji kdykoli spustit znovu od začátku.');
    redirect_to('?view=dashboard');
}
