<?php

declare(strict_types=1);

/**
 * POST v56_* – teorie, test, projekt a odevzdání lekce.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (str_starts_with($action, 'v56_')) {
    $v56Class = current_class_id($modules);
    if ($v56Class === null) { $_SESSION['flash'] = tr('Nejdřív se přihlas.'); redirect_to('?view=home'); }
    $v56Key = adaptive_student_key((string)$v56Class);
    if ($v56Key === '') { $_SESSION['flash'] = tr('Nepodařilo se určit studentský profil.'); redirect_to('?view=dashboard'); }
    $v56No = max(1, min(28, (int)($_POST['lesson'] ?? 1)));
    $v56Bundle = v56_lesson_bundle((string)$v56Class, $modules[$v56Class], $v56No, $nextLessons, $extendedLessons);
    $v56Today = sess53_for_class_date((string)$v56Class, date('Y-m-d'));
    $v56IsToday = is_array($v56Today) && (int)($v56Today['lesson_number'] ?? 0) === $v56No && (string)($v56Today['kind'] ?? '') === 'work';
    $v56Back = $v56IsToday ? '?view=hodina' : v56_lesson_url($v56No);
    try {
        if ($action === 'v56_theory_done') {
            v56_mark_theory((string)$v56Class, $v56Key, $v56No, (string)($_POST['topic'] ?? ''));
            $v56State = v56_lesson_state((string)$v56Class, $v56Key, $v56Bundle);
            $v56Back = $v56IsToday ? '?view=hodina' : v56_lesson_url($v56No, (string)($v56State['current'] ?? 'theory'));
            if (!$v56IsToday && (string)($v56State['current'] ?? '') === 'theory' && (string)$v56State['next_topic'] !== '') {
                $v56Back .= '&tema=' . rawurlencode((string)$v56State['next_topic']);
            }
        } elseif ($action === 'v56_test_submit') {
            $v56Result = v56_submit_test((string)$v56Class, $v56Key, $v56No, v56_test_questions((string)$v56Class, $v56Key, $v56No, (array)$v56Bundle['questions']), (array)($_POST['answers'] ?? []));
            $_SESSION['flash'] = !empty($v56Result['passed'])
                ? tr('Test máš splněný: {score} / {max}.', ['score' => (int)$v56Result['score'], 'max' => (int)$v56Result['max']])
                : tr('Zatím to nestačí. Projdi si témata, kde ses spletl/a, a zkus test znovu.');
            if (!$v56IsToday) $v56Back = v56_lesson_url($v56No, 'test');
        } elseif ($action === 'v56_project_step') {
            v56_toggle_project_step((string)$v56Class, $v56Key, $v56No, (int)($_POST['step'] ?? 0), (string)($_POST['done'] ?? '1') === '1');
            if (!$v56IsToday) $v56Back = v56_lesson_url($v56No, 'project');
        } elseif ($action === 'v56_submit') {
            $v56Final = (string)($_POST['final'] ?? '0') === '1';
            v56_save_submit((string)$v56Class, $v56Key, $v56No, (string)($_POST['note'] ?? ''), (string)($_POST['link'] ?? ''), $v56Final);
            $v56Session = sess53_find((string)($_POST['session'] ?? ''));
            if (is_array($v56Session) && (string)$v56Session['class_id'] === $v56Class) {
                sess53_submit((string)$v56Session['id'], $v56Key, trim((string)($_SESSION['student_label'] ?? '')), [
                    'checks' => array_map('strval', array_keys((array)v56_progress((string)$v56Class, $v56Key, $v56No)['project'])),
                    'note' => (string)($_POST['note'] ?? ''),
                    'link' => (string)($_POST['link'] ?? ''),
                    'status' => $v56Final ? 'submitted' : 'draft',
                ]);
            }
            $_SESSION['flash'] = $v56Final ? tr('Odevzdáno. Učitel ti dá zpětnou vazbu a body.') : tr('Rozpracované uloženo.');
            if (!$v56IsToday) $v56Back = v56_lesson_url($v56No, 'submit');
        }
    } catch (Throwable $e) {
        $_SESSION['flash'] = $e->getMessage();
    }
    redirect_to($v56Back);
}
