<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';
$tours = require dirname(__DIR__) . '/knowledge_tours.php';
$extraTours = require dirname(__DIR__) . '/knowledge_tours_next.php';
foreach ($extraTours as $classId => $items) {
    $tours[$classId] = array_replace($tours[$classId] ?? [], $items);
}
$plusTours = require dirname(__DIR__) . '/knowledge_tours_plus.php';
foreach ($plusTours as $classId => $items) {
    $tours[$classId] = array_replace($tours[$classId] ?? [], $items);
}
$moreTours = require dirname(__DIR__) . '/knowledge_tours_more.php';
foreach ($moreTours as $classId => $items) {
    $tours[$classId] = array_replace($tours[$classId] ?? [], $items);
}
$ecoTours = require dirname(__DIR__) . '/knowledge_tours_ecosystem.php';
foreach ($ecoTours as $classId => $items) {
    $tours[$classId] = array_replace($tours[$classId] ?? [], $items);
}
$yearTours = require dirname(__DIR__) . '/knowledge_tours_yearpack.php';
foreach ($yearTours as $classId => $items) {
    $tours[$classId] = array_replace($tours[$classId] ?? [], $items);
}
$v30Tours = require dirname(__DIR__) . '/knowledge_tours_v30.php';
foreach ($v30Tours as $classId => $items) {
    $tours[$classId] = array_replace($tours[$classId] ?? [], $items);
}
if (isset($tours['class_2a'])) {
    $tours['class_1a'] = array_replace($tours['class_2a'], $tours['class_1a'] ?? []);
}
$nextLessons = require dirname(__DIR__) . '/next_lessons.php';
$extendedLessons = require dirname(__DIR__) . '/extended_lessons.php';
$lessonPlus = require dirname(__DIR__) . '/lessons_plus.php';
$plusKnowledge = require dirname(__DIR__) . '/knowledge_extensions_plus.php';
$plusSimulations = require dirname(__DIR__) . '/simulations_plus.php';
$lessonMore = require dirname(__DIR__) . '/lessons_more.php';
$moreKnowledge = require dirname(__DIR__) . '/knowledge_extensions_more.php';
$moreSimulations = require dirname(__DIR__) . '/simulations_more.php';
$ecoKnowledge = require dirname(__DIR__) . '/knowledge_extensions_ecosystem.php';
$ecoSimulations = require dirname(__DIR__) . '/simulations_ecosystem.php';
$lessonEcosystem = require dirname(__DIR__) . '/lessons_ecosystem.php';
$yearKnowledge = require dirname(__DIR__) . '/knowledge_extensions_yearpack.php';
$yearSimulations = require dirname(__DIR__) . '/simulations_yearpack.php';
$lessonYearpack = require dirname(__DIR__) . '/lessons_yearpack.php';
$lessonV30 = require dirname(__DIR__) . '/lessons_v30.php';
$learningResources = require dirname(__DIR__) . '/learning_resources.php';
foreach ($lessonPlus as $classId => $items) {
    if (!isset($extendedLessons[$classId])) $extendedLessons[$classId] = [];
    $extendedLessons[$classId] = array_merge($extendedLessons[$classId], is_array($items) ? $items : []);
}
foreach ($lessonMore as $classId => $items) {
    if (!isset($extendedLessons[$classId])) $extendedLessons[$classId] = [];
    $extendedLessons[$classId] = array_merge($extendedLessons[$classId], is_array($items) ? $items : []);
}
foreach ($lessonEcosystem as $classId => $items) {
    if (!isset($extendedLessons[$classId])) $extendedLessons[$classId] = [];
    $extendedLessons[$classId] = array_merge($extendedLessons[$classId], is_array($items) ? $items : []);
}
foreach ($lessonYearpack as $classId => $items) {
    if (!isset($extendedLessons[$classId])) $extendedLessons[$classId] = [];
    $extendedLessons[$classId] = array_merge($extendedLessons[$classId], is_array($items) ? $items : []);
}
foreach ($lessonV30 as $classId => $items) {
    if (!isset($extendedLessons[$classId])) $extendedLessons[$classId] = [];
    $extendedLessons[$classId] = array_merge($extendedLessons[$classId], is_array($items) ? $items : []);
}

$errors = [];
$checkedQuestions = 0;
$checkedMaterials = 0;

foreach ($modules as $classId => $module) {
    $kb = (array)($module['knowledgebase'] ?? []);
    foreach ((array)($plusKnowledge[$classId] ?? []) as $plusTopic => $_plusArticle) {
        if (!isset($plusSimulations[$classId][$plusTopic]) || !is_array($plusSimulations[$classId][$plusTopic])) {
            $errors[] = "$classId: nový KB materiál '$plusTopic' nemá povinnou interaktivní simulaci.";
        }
    }
    foreach ((array)($moreKnowledge[$classId] ?? []) as $moreTopic => $_moreArticle) {
        if (!isset($moreSimulations[$classId][$moreTopic]) || !is_array($moreSimulations[$classId][$moreTopic])) {
            $errors[] = "$classId: nový KB materiál '$moreTopic' nemá povinnou interaktivní simulaci.";
        }
    }
    foreach ((array)($ecoKnowledge[$classId] ?? []) as $ecoTopic => $_ecoArticle) {
        if (!isset($ecoSimulations[$classId][$ecoTopic]) || !is_array($ecoSimulations[$classId][$ecoTopic])) {
            $errors[] = "$classId: ecosystem KB materiál '$ecoTopic' nemá povinnou interaktivní simulaci.";
        }
    }
    foreach ((array)($yearKnowledge[$classId] ?? []) as $yearTopic => $_yearArticle) {
        if (!isset($yearSimulations[$classId][$yearTopic]) || !is_array($yearSimulations[$classId][$yearTopic])) {
            $errors[] = "$classId: yearpack KB materiál '$yearTopic' nemá povinnou interaktivní simulaci.";
        }
    }
    foreach ((array)($module['questions'] ?? []) as $q) {
        if (!is_array($q)) continue;
        $checkedQuestions++;
        $topic = (string)($q['kb'] ?? '');
        if ($topic === '' || !isset($kb[$topic])) {
            $errors[] = "$classId: otázka {$q['id']} odkazuje na chybějící KB téma '$topic'.";
        }
        $labels = balanced_quiz_option_labels((array)($q['options'] ?? []), $q['correct'] ?? '', subject_family($module));
        if ($labels) {
            $lens = [];
            foreach ($labels as $key => $label) $lens[(string)$key] = u_strlen((string)$label);
            $max = max($lens);
            $maxKeys = array_keys($lens, $max, true);
            if (count($maxKeys) === 1 && (string)$maxKeys[0] === (string)($q['correct'] ?? '')) {
                $errors[] = "$classId: otázka {$q['id']} má po normalizaci správnou odpověď jako jedinou nejdelší.";
            }
        }
    }

    foreach ($kb as $topic => $article) {
        $checkedMaterials++;
        $resources = (array)($learningResources[$classId][$topic] ?? []);
        $videosForTopic = array_values(array_filter($resources, static fn($r): bool => is_array($r) && (($r['type'] ?? '') === 'video')));
        if (!$videosForTopic) {
            $errors[] = "$classId: KB '$topic' nemá české YouTube video.";
        } else {
            $video = $videosForTopic[0];
            foreach (['youtube_id','duration','level','description'] as $requiredVideoField) {
                if (trim((string)($video[$requiredVideoField] ?? '')) === '') {
                    $errors[] = "$classId: KB '$topic' video nemá pole '$requiredVideoField'.";
                }
            }
            if (strtoupper((string)($video['lang'] ?? '')) !== 'CZ') {
                $errors[] = "$classId: KB '$topic' video není označené jako CZ.";
            }
            if (!str_contains((string)($video['url'] ?? ''), 'youtube.com/watch')) {
                $errors[] = "$classId: KB '$topic' video není YouTube watch URL.";
            }
        }
        $tour = $tours[$classId][$topic] ?? null;
        if (!is_array($tour)) {
            $errors[] = "$classId: KB '$topic' nemá Knowledge Tour.";
            continue;
        }
        if (!is_array($tour['check'] ?? null) || empty($tour['check']['options'])) {
            $errors[] = "$classId: KB '$topic' nemá samostatný knowledge check.";
        }
    }

    foreach ((array)($module['practice']['tasks'] ?? []) as $task) {
        if (!is_array($task) || empty($task['kb'])) continue;
        if (!isset($kb[(string)$task['kb']])) {
            $errors[] = "$classId: practice task {$task['id']} odkazuje na chybějící KB '{$task['kb']}'.";
        }
    }

    foreach (($extendedLessons[$classId] ?? []) as $extendedLesson) {
        if (!is_array($extendedLesson)) continue;
        $refs = [];
        foreach ((array)($extendedLesson['knowledge'] ?? []) as $topic) $refs[] = (string)$topic;
        foreach ((array)($extendedLesson['steps'] ?? []) as $step) {
            if (!is_array($step)) continue;
            foreach ((array)($step['knowledge'] ?? []) as $topic) $refs[] = (string)$topic;
            if (($step['kind'] ?? '') === 'quiz' && is_array($step['options'] ?? null)) {
                $labels = balanced_quiz_option_labels($step['options'], $step['correct'] ?? -1, subject_family($module));
                $lens=[]; foreach($labels as $k=>$label) $lens[(string)$k]=u_strlen((string)$label);
                if ($lens) { $max=max($lens); $keys=array_keys($lens,$max,true); if(count($keys)===1 && (string)$keys[0]===(string)($step['correct']??'')) $errors[]="$classId: {$extendedLesson['id']} / {$step['id']} má správnou odpověď jako jedinou nejdelší."; }
            }
        }
        foreach (array_unique($refs) as $topic) if ($topic !== '' && !isset($kb[$topic])) $errors[] = "$classId: rozšířená lekce {$extendedLesson['id']} odkazuje na chybějící KB '$topic'.";
    }

    $lesson = $nextLessons[$classId] ?? null;
    if (is_array($lesson)) {
        $refs = [];
        foreach ((array)($lesson['knowledge'] ?? []) as $topic) $refs[] = (string)$topic;
        foreach ((array)($lesson['steps'] ?? []) as $step) {
            if (!is_array($step)) continue;
            foreach ((array)($step['knowledge'] ?? []) as $topic) $refs[] = (string)$topic;
        }
        foreach (array_unique($refs) as $topic) {
            if ($topic !== '' && !isset($kb[$topic])) {
                $errors[] = "$classId: navazující lekce odkazuje na chybějící KB '$topic'.";
            }
        }
    }
}

fwrite(STDOUT, "Knowledge materials: $checkedMaterials\nQuestions: $checkedQuestions\n");
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, "ERROR: $error\n");
    exit(1);
}
fwrite(STDOUT, "OK: content links, knowledge checks, Czech YouTube coverage and answer-length guard passed.\n");
