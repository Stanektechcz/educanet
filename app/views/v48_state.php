<?php

declare(strict_types=1);

/**
 * ?view=v48_state – JSON stav orchestrace Visual Labu.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'v48_state') {
    $stateClass=current_class_id($modules);if($stateClass===null||!isset($modules[$stateClass])){http_response_code(401);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false]);exit;}
    $lessonNo=max(1,min(28,(int)($_GET['lesson']??1)));$lesson=v42_find_lesson($stateClass,$lessonNo);if(!$lesson){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false]);exit;}
    $state=v48_orchestration_state($stateClass,$lessonNo);$spec=v48_lab_spec($stateClass,$lesson,$modules[$stateClass]);$phase=(string)($state['phase']??'predict');
    header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'state'=>['active'=>!empty($state['active']),'phase'=>$phase,'label'=>(string)($spec['teacher']['phases'][$phase]['label']??$phase),'prompt'=>(string)($state['prompt']??'')]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
}
