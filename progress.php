<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$currentClassId = current_class_id($modules);
if ($currentClassId === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => tr('Nejsi přihlášen/a do třídy.')], JSON_UNESCAPED_UNICODE);
    exit;
}
// v58 · SEC58-08: účet s vynucenou změnou hesla nesmí používat API (stejně jako lab_v57_api.php).
require_once __DIR__ . '/accounts_v53.php';
if (acc53_must_change_password()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => tr('Nejdřív si nastav vlastní heslo.')], JSON_UNESCAPED_UNICODE);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') { verify_csrf(); }
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$requestedKbClass = is_string($_POST['kb_class'] ?? null) ? (string)$_POST['kb_class'] : (is_string($_GET['kb_class'] ?? null) ? (string)$_GET['kb_class'] : '');
$classId = $currentClassId;
if (($action === 'kb_step' || $action === 'kb_check' || $method === 'GET') && $requestedKbClass !== '' && isset($modules[$requestedKbClass])) {
    if (can_access_subject_class($currentClassId, $requestedKbClass, $modules)) {
        $classId = $requestedKbClass;
    }
}
$module = $modules[$classId];
$simulations = require __DIR__ . '/simulations.php';
if (isset($simulations['class_2a'])) {
    $simulations['class_1a'] = array_replace($simulations['class_2a'], $simulations['class_1a'] ?? []);
}
$simulationExtras = require __DIR__ . '/simulations_extra.php';
foreach ($simulationExtras as $simClassId => $extraMap) {
    if (!isset($simulations[$simClassId])) {
        $simulations[$simClassId] = [];
    }
    $simulations[$simClassId] = array_replace($simulations[$simClassId], $extraMap);
}
$simulationPlus = require __DIR__ . '/simulations_plus.php';
foreach ($simulationPlus as $simClassId => $extraMap) {
    if (!isset($simulations[$simClassId])) $simulations[$simClassId] = [];
    $simulations[$simClassId] = array_replace($simulations[$simClassId], $extraMap);
}
$simulationMore = require __DIR__ . '/simulations_more.php';
foreach ($simulationMore as $simClassId => $extraMap) {
    if (!isset($simulations[$simClassId])) $simulations[$simClassId] = [];
    $simulations[$simClassId] = array_replace($simulations[$simClassId], $extraMap);
}
$simulationEcosystem = require __DIR__ . '/simulations_ecosystem.php';
foreach ($simulationEcosystem as $simClassId => $extraMap) {
    if (!isset($simulations[$simClassId])) $simulations[$simClassId] = [];
    $simulations[$simClassId] = array_replace($simulations[$simClassId], $extraMap);
}
$simulationYearpack = require __DIR__ . '/simulations_yearpack.php';
foreach ($simulationYearpack as $simClassId => $extraMap) {
    if (!isset($simulations[$simClassId])) $simulations[$simClassId] = [];
    $simulations[$simClassId] = array_replace($simulations[$simClassId], $extraMap);
}
$simulationMap = is_array($simulations[$classId] ?? null) ? $simulations[$classId] : [];
$tours = require __DIR__ . '/knowledge_tours.php';
$tourExtras = require __DIR__ . '/knowledge_tours_next.php';
foreach ($tourExtras as $tourClassId => $tourArticles) {
    if (!isset($tours[$tourClassId])) { $tours[$tourClassId] = []; }
    $tours[$tourClassId] = array_replace($tours[$tourClassId], $tourArticles);
}
$tourPlus = require __DIR__ . '/knowledge_tours_plus.php';
foreach ($tourPlus as $tourClassId => $tourArticles) {
    if (!isset($tours[$tourClassId])) $tours[$tourClassId] = [];
    $tours[$tourClassId] = array_replace($tours[$tourClassId], $tourArticles);
}
$tourMore = require __DIR__ . '/knowledge_tours_more.php';
foreach ($tourMore as $tourClassId => $tourArticles) {
    if (!isset($tours[$tourClassId])) $tours[$tourClassId] = [];
    $tours[$tourClassId] = array_replace($tours[$tourClassId], $tourArticles);
}
$tourEcosystem = require __DIR__ . '/knowledge_tours_ecosystem.php';
foreach ($tourEcosystem as $tourClassId => $tourArticles) {
    if (!isset($tours[$tourClassId])) $tours[$tourClassId] = [];
    $tours[$tourClassId] = array_replace($tours[$tourClassId], $tourArticles);
}
$tourYearpack = require __DIR__ . '/knowledge_tours_yearpack.php';
foreach ($tourYearpack as $tourClassId => $tourArticles) {
    if (!isset($tours[$tourClassId])) $tours[$tourClassId] = [];
    $tours[$tourClassId] = array_replace($tours[$tourClassId], $tourArticles);
}
$nextLessons = require __DIR__ . '/next_lessons.php';
$extendedLessons = require __DIR__ . '/extended_lessons.php';
$lessonPlus = require __DIR__ . '/lessons_plus.php';
foreach ($lessonPlus as $plusClassId => $plusLessons) {
    if (!isset($extendedLessons[$plusClassId])) $extendedLessons[$plusClassId] = [];
    $extendedLessons[$plusClassId] = array_merge($extendedLessons[$plusClassId], is_array($plusLessons) ? $plusLessons : []);
}
$lessonMore = require __DIR__ . '/lessons_more.php';
foreach ($lessonMore as $moreClassId => $moreLessons) {
    if (!isset($extendedLessons[$moreClassId])) $extendedLessons[$moreClassId] = [];
    $extendedLessons[$moreClassId] = array_merge($extendedLessons[$moreClassId], is_array($moreLessons) ? $moreLessons : []);
}
$lessonEcosystem = require __DIR__ . '/lessons_ecosystem.php';
foreach ($lessonEcosystem as $ecoClassId => $ecoLessons) {
    if (!isset($extendedLessons[$ecoClassId])) $extendedLessons[$ecoClassId] = [];
    $extendedLessons[$ecoClassId] = array_merge($extendedLessons[$ecoClassId], is_array($ecoLessons) ? $ecoLessons : []);
}
$lessonYearpack = require __DIR__ . '/lessons_yearpack.php';
foreach ($lessonYearpack as $yearClassId => $yearLessons) {
    if (!isset($extendedLessons[$yearClassId])) $extendedLessons[$yearClassId] = [];
    $extendedLessons[$yearClassId] = array_merge($extendedLessons[$yearClassId], is_array($yearLessons) ? $yearLessons : []);
}
if (isset($tours['class_2a'])) {
    $tours['class_1a'] = array_replace($tours['class_2a'], $tours['class_1a'] ?? []);
}
$tourMap = is_array($tours[$classId] ?? null) ? $tours[$classId] : [];

if ($method === 'GET') {
    echo json_encode(['ok' => true, 'test_active' => active_test() !== null, 'state' => learning_public_state($classId, $module, $simulationMap)], JSON_UNESCAPED_UNICODE);
    exit;
}

$awarded = 0;
$awardedEvents = [];
$awardXp = static function (string $eventKey, int $xp, string $label, string $detail = '') use (&$awarded, &$awardedEvents, $classId): bool {
    if (!learning_award_once($classId, $eventKey, $xp)) return false;
    $awarded += max(0, $xp);
    $awardedEvents[] = ['xp' => max(0, $xp), 'label' => $label, 'detail' => $detail];
    return true;
};
$newBadgesBefore = array_keys(learning_profile($classId)['badges'] ?? []);
$newAchievementsBefore = array_keys(learning_profile($classId)['achievements'] ?? []);

if (($action === 'kb_step' || $action === 'kb_check') && active_test()) {
    http_response_code(423);
    echo json_encode(['ok' => false, 'error' => tr('Knowledgebase je během spuštěného testu zamčená.')], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'kb_step') {
    $topic = is_string($_POST['topic'] ?? null) ? $_POST['topic'] : '';
    $step = is_string($_POST['step'] ?? null) ? $_POST['step'] : '';
    if (!isset($module['knowledgebase'][$topic])) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Neznámé téma.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $allowed = ['visual', 'simulation', 'steps', 'deep'];
    if (!in_array($step, $allowed, true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Neplatný krok.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($step === 'simulation' && !isset($simulationMap[$topic])) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Tato lekce nemá simulaci.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $currentProgress = learning_kb_progress($classId, $topic);
    $hasSimulation = isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
    $previousOk = match ($step) {
        'visual' => true,
        'simulation' => !empty($currentProgress['visual']),
        'steps' => $hasSimulation ? !empty($currentProgress['simulation']) : !empty($currentProgress['visual']),
        'deep' => !empty($currentProgress['steps']),
        default => false,
    };
    if (!$previousOk) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => tr('Nejdřív dokonči předchozí krok této lekce.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $xpMap = ['visual' => 5, 'simulation' => 20, 'steps' => 10, 'deep' => 5];
    learning_set_kb_step($classId, $topic, $step, true);
    $stepLabels = ['visual'=>'Vizuální model', 'simulation'=>'Interaktivní simulace', 'steps'=>'Postup krok za krokem', 'deep'=>'Vysvětlení principu'];
    $awardXp("kb:$topic:$step", $xpMap[$step], $stepLabels[$step] ?? 'Knowledge krok', 'Krok byl automaticky ověřen a uložen.');
} elseif ($action === 'kb_check') {
    $topic = is_string($_POST['topic'] ?? null) ? $_POST['topic'] : '';
    $answer = isset($_POST['answer']) && is_numeric($_POST['answer']) ? (int)$_POST['answer'] : -1;
    $check = $tourMap[$topic]['check'] ?? null;
    if (!isset($module['knowledgebase'][$topic]) || !is_array($check)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Neplatný mini-check.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $currentProgress = learning_kb_progress($classId, $topic);
    if (empty($currentProgress['deep'])) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => tr('Mini-check se odemkne až po dokončení vysvětlení.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $correct = (int)($check['correct'] ?? -2);
    $confidence = max(0,min(3,(int)($_POST['confidence'] ?? 0)));
    $latencyMs = max(0,min(600000,(int)($_POST['latency_ms'] ?? 0)));
    $studentKey = adaptive_student_key($classId);
    ml_record_learning_event($classId,$studentKey,$topic,'kb_check',$answer === $correct,['confidence'=>$confidence,'latency_ms'=>$latencyMs,'selected'=>$answer,'correct_index'=>$correct,'article'=>(array)$module['knowledgebase'][$topic]]);
    if ($answer !== $correct) {
        echo json_encode(['ok' => true, 'correct' => false, 'awarded_xp' => 0, 'state' => learning_public_state($classId, $module, $simulationMap)], JSON_UNESCAPED_UNICODE);
        exit;
    }
    learning_set_kb_step($classId, $topic, 'check', true);
    $awardXp("kb:$topic:check", 15, 'Knowledge check', 'Správná odpověď.');
} elseif ($action === 'next_lesson_step') {
    $lesson = is_array($nextLessons[$classId] ?? null) ? $nextLessons[$classId] : null;
    if (!is_array($lesson)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Navazující lekce není pro tuto třídu připravená.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!learning_primary_block_complete($classId)) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => tr('Nejdřív dokonči první dvouhodinový blok.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stepId = is_string($_POST['step'] ?? null) ? $_POST['step'] : '';
    $steps = is_array($lesson['steps'] ?? null) ? $lesson['steps'] : [];
    $stepIndex = null;
    $step = null;
    foreach ($steps as $i => $candidate) {
        if (is_array($candidate) && (string)($candidate['id'] ?? '') === $stepId) {
            $stepIndex = $i;
            $step = $candidate;
            break;
        }
    }
    if (!is_array($step) || $stepIndex === null) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Neplatný krok navazující lekce.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $lessonId = (string)($lesson['id'] ?? 'next');
    $progress = learning_next_lesson_progress($classId, $lessonId, $steps);
    if ($stepIndex > 0) {
        $prevId = (string)($steps[$stepIndex - 1]['id'] ?? '');
        if ($prevId === '' || empty($progress[$prevId])) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'error' => tr('Nejdřív dokonči předchozí krok navazující lekce.')], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    $kind = (string)($step['kind'] ?? 'manual');
    $requiredTasks = count(is_array($step['tasks'] ?? null) ? $step['tasks'] : []);
    $confirmedTasks = isset($_POST['tasks_done']) && is_numeric($_POST['tasks_done']) ? (int)$_POST['tasks_done'] : 0;
    if ($requiredTasks > 0 && $confirmedTasks < $requiredTasks) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => tr('Nejdřív potvrď všechny povinné body tohoto kroku.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!empty($step['knowledge'])) {
        foreach (($step['knowledge'] ?? []) as $topic) {
            $topic = (string)$topic;
            $hasSimulation = isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
            if (!isset($module['knowledgebase'][$topic]) || !learning_kb_complete($classId, $topic, $hasSimulation)) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'error' => tr('Nejdřív dokonči všechny Knowledge Tour uvedené v tomto kroku.')], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
    if ($kind === 'quiz') {
        $answer = isset($_POST['answer']) && is_numeric($_POST['answer']) ? (int)$_POST['answer'] : -1;
        if ($answer !== (int)($step['correct'] ?? -2)) {
            echo json_encode([
                'ok' => true,
                'correct' => false,
                'awarded_xp' => 0,
                'explanation' => (string)($step['explanation'] ?? 'Zkus se znovu podívat na důkazy.'),
                'state' => learning_public_state($classId, $module, $simulationMap),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    learning_set_journey_step($classId, 'next:' . $lessonId . ':' . $stepId, true);
    $xp = max(0, (int)($step['xp'] ?? 20));
    $awardXp('next:' . $lessonId . ':' . $stepId, $xp, (string)($step['title'] ?? 'Krok lekce'), 'Krok byl dokončen automaticky.');
    $nowProgress = learning_next_lesson_progress($classId, $lessonId, $steps);
    $complete = true;
    foreach ($steps as $candidate) {
        if (!is_array($candidate) || empty($candidate['id'])) continue;
        if (empty($nowProgress[(string)$candidate['id']])) { $complete = false; break; }
    }
    if ($complete) $awardXp('next:' . $lessonId . ':complete', 75, 'Dokončená lekce', 'Bonus za dokončení celého bloku.');
} elseif ($action === 'course_lesson_step') {
    $lessonId = is_string($_POST['lesson'] ?? null) ? $_POST['lesson'] : '';
    $lesson = null;
    foreach (($extendedLessons[$classId] ?? []) as $candidate) {
        if (is_array($candidate) && (string)($candidate['id'] ?? '') === $lessonId) { $lesson = $candidate; break; }
    }
    if (!is_array($lesson)) {
        http_response_code(422); echo json_encode(['ok'=>false,'error'=>tr('Lekce nebyla nalezena.')], JSON_UNESCAPED_UNICODE); exit;
    }
    $number = (int)($lesson['number'] ?? 0);
    if (!learning_course_lesson_unlocked($classId, $number, $nextLessons, $extendedLessons)) {
        http_response_code(409); echo json_encode(['ok'=>false,'error'=>tr('Nejdřív dokonči předchozí lekci kurzu.')], JSON_UNESCAPED_UNICODE); exit;
    }
    $stepId = is_string($_POST['step'] ?? null) ? $_POST['step'] : '';
    $steps = is_array($lesson['steps'] ?? null) ? $lesson['steps'] : [];
    $idx = null; $step = null;
    foreach ($steps as $i=>$candidate) if (is_array($candidate) && (string)($candidate['id'] ?? '') === $stepId) { $idx=$i; $step=$candidate; break; }
    if (!is_array($step) || $idx === null) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>tr('Neplatný krok lekce.')], JSON_UNESCAPED_UNICODE); exit; }
    $progress = learning_course_lesson_progress($classId, $lesson);
    if ($idx > 0) {
        $prevId = (string)($steps[$idx-1]['id'] ?? '');
        if ($prevId === '' || empty($progress[$prevId])) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>tr('Nejdřív dokonči předchozí krok.')], JSON_UNESCAPED_UNICODE); exit; }
    }
    $checks = isset($_POST['tasks_done']) && is_numeric($_POST['tasks_done']) ? (int)$_POST['tasks_done'] : 0;
    $required = count(is_array($step['tasks'] ?? null) ? $step['tasks'] : []);
    if ($required > 0 && $checks < $required) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>tr('Potvrď všechny povinné body kroku.')], JSON_UNESCAPED_UNICODE); exit; }
    foreach (($step['knowledge'] ?? []) as $topic) {
        $topic=(string)$topic; $hasSim=isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
        if (isset($module['knowledgebase'][$topic]) && !learning_kb_complete($classId,$topic,$hasSim)) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>'Nejdřív dokonči požadovanou Knowledge Tour: '.$topic], JSON_UNESCAPED_UNICODE); exit; }
    }
    if ((string)($step['kind'] ?? '') === 'quiz') {
        $answer = isset($_POST['answer']) && is_numeric($_POST['answer']) ? (int)$_POST['answer'] : -1;
        if ($answer !== (int)($step['correct'] ?? -2)) {
            echo json_encode(['ok'=>true,'correct'=>false,'awarded_xp'=>0,'explanation'=>(string)($step['explanation'] ?? 'Zkus to znovu.'),'state'=>learning_public_state($classId,$module,$simulationMap)], JSON_UNESCAPED_UNICODE); exit;
        }
    }
    learning_set_journey_step($classId, 'course:' . $lessonId . ':' . $stepId, true);
    $xp=max(0,(int)($step['xp'] ?? 20));
    $awardXp('course:'.$lessonId.':'.$stepId, $xp, (string)($step['title'] ?? 'Krok lekce'), 'Krok byl dokončen automaticky.');
    if (learning_course_lesson_complete($classId,$lesson)) $awardXp('course:'.$lessonId.':complete', 75, 'Dokončená lekce', 'Bonus za dokončení celého bloku.');
} elseif ($action === 'journey_step') {
    $step = is_string($_POST['step'] ?? null) ? $_POST['step'] : '';
    if (!in_array($step, ['case_study'], true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Neplatný krok průchodu.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $testResult = $_SESSION['next_result'] ?? null;
    if (!is_array($testResult) || (($testResult['class_id'] ?? null) !== $classId)) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => tr('Nejdřív dokonči startovní test.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    learning_set_journey_step($classId, $step, true);
    $awardXp('journey:' . $step, 15, 'Briefing dokončen', 'Postup byl uložen.');
} elseif ($action === 'studio_step') {
    if (!in_array($classId, ['class_1a', 'class_2a'], true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Design Studio není pro tuto třídu.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $step = is_string($_POST['step'] ?? null) ? $_POST['step'] : '';
    $allowed = learning_studio_required_steps();
    if (!in_array($step, $allowed, true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => tr('Neplatný krok Studia.')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $index = array_search($step, $allowed, true);
    if ($index !== false && $index > 0) {
        $profile = learning_profile($classId);
        $studio = is_array($profile['studio'] ?? null) ? $profile['studio'] : [];
        $prev = $allowed[$index - 1];
        if (empty($studio[$prev])) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'error' => tr('Nejdřív dokonči předchozí krok.')], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    learning_set_studio_step($classId, $step, true);
    $awardXp("studio:$step", 10, 'Design Studio · krok', 'Krok byl ověřen a uložen.');
} else {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => tr('Neznámá akce.')], JSON_UNESCAPED_UNICODE);
    exit;
}

// Automatické dokončení právě otevřeného tématu a bonus XP.
if (str_starts_with($action, 'kb_')) {
    $topic = is_string($_POST['topic'] ?? null) ? $_POST['topic'] : '';
    $hasSimulation = isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
    if ($topic !== '' && learning_kb_complete($classId, $topic, $hasSimulation)) {
        learning_set_kb_step($classId, $topic, 'complete', true);
        $awardXp("kb:$topic:complete", 25, 'Knowledge lekce dokončena', 'Bonus za dokončení celé lekce.');
    }
}
if ($action === 'studio_step' && learning_studio_complete($classId)) {
    $awardXp('studio:complete', 50, 'Design Studio dokončeno', 'Bonus za dokončení celé části.');
}

learning_refresh_badges($classId, $module, $simulationMap);
$profileAfter = learning_profile($classId);
$newBadgesAfter = array_keys($profileAfter['badges'] ?? []);
$newBadges = array_values(array_diff($newBadgesAfter, $newBadgesBefore));
$newAchievementsAfter = array_keys($profileAfter['achievements'] ?? []);
$newAchievements = array_values(array_diff($newAchievementsAfter, $newAchievementsBefore));

$topicProgress = null;
if (isset($_POST['topic']) && is_string($_POST['topic'])) {
    $topicProgress = learning_kb_progress($classId, $_POST['topic']);
}

echo json_encode([
    'ok' => true,
    'correct' => true,
    'awarded_xp' => $awarded,
    'awarded_events' => $awardedEvents,
    'new_badges' => $newBadges,
    'new_achievements' => $newAchievements,
    'topic_progress' => $topicProgress,
    'state' => learning_public_state($classId, $module, $simulationMap),
], JSON_UNESCAPED_UNICODE);
