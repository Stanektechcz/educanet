<?php

declare(strict_types=1);

/**
 * POST adaptive_* – absence, recovery, retrieval, deník, nápověda.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$adaptiveActions = ['adaptive_absence_mark','adaptive_absence_clear','adaptive_recovery_step','adaptive_retrieval_submit','adaptive_journal_save','adaptive_help_event','adaptive_tutor'];
if (in_array($action, $adaptiveActions, true)) {
    $actionClassId = current_class_id($modules);
    if ($actionClassId === null || !isset($modules[$actionClassId])) {
        if (in_array($action,['adaptive_help_event','adaptive_tutor'],true)) { http_response_code(401); header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>tr('Nejdřív se přihlas.')]); exit; }
        $_SESSION['flash']=tr('Nejdřív se přihlas do své třídy.'); redirect_to('?view=home');
    }
    $studentKey = adaptive_student_key($actionClassId);
    try {
        if ($action === 'adaptive_help_event') {
            adaptive_help_event($actionClassId,$studentKey,(string)($_POST['topic']??''),(string)($_POST['mode']??''));
            header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true]); exit;
        }
        if ($action === 'adaptive_tutor') {
            $topic=(string)($_POST['topic']??''); $tourMap=is_array($knowledgeTours[$actionClassId]??null)?$knowledgeTours[$actionClassId]:[];
            if(!isset($modules[$actionClassId]['knowledgebase'][$topic])) throw new RuntimeException(tr('Téma nebylo nalezeno.'));
            $answer=adaptive_tutor_answer($actionClassId,$studentKey,$topic,(string)($_POST['question']??''),$modules[$actionClassId],$tourMap,(string)($_POST['mode']??'guide'));
            header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true,'answer'=>$answer],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
        }
        if ($action === 'adaptive_absence_mark') {
            adaptive_absence_mark($actionClassId,$studentKey,(string)($_POST['date']??''),(int)($_POST['lesson_number']??0),(string)($_POST['lesson_title']??tr('Lekce')));
            $_SESSION['flash']=tr('Recovery Path je připravený. Nemusíš dohánět celou hodinu najednou.');
            redirect_to(module_url('recovery',['date'=>(string)($_POST['date']??'')]));
        }
        if ($action === 'adaptive_absence_clear') {
            adaptive_absence_clear($actionClassId,$studentKey,(string)($_POST['date']??'')); $_SESSION['flash']=tr('Absence byla z recovery seznamu odebrána.'); redirect_to('?view=calendar');
        }
        if ($action === 'adaptive_recovery_step') {
            $date=(string)($_POST['date']??''); adaptive_recovery_step($actionClassId,$studentKey,$date,(string)($_POST['step']??''),(string)($_POST['done']??'1')==='1',(string)($_POST['reflection']??''));
            redirect_to(module_url('recovery',['date'=>$date]));
        }
        if ($action === 'adaptive_retrieval_submit') {
            $tourMap=is_array($knowledgeTours[$actionClassId]??null)?$knowledgeTours[$actionClassId]:[];$result=adaptive_retrieval_submit($actionClassId,$studentKey,is_array($_POST['answers']??null)?$_POST['answers']:[],$modules[$actionClassId],$tourMap,is_array($_POST['confidence']??null)?$_POST['confidence']:[]);$_SESSION['adaptive_retrieval_result']=$result;
            redirect_to('?view=review');
        }
        if ($action === 'adaptive_journal_save') {
            adaptive_journal_save($actionClassId,$studentKey,date('Y-m-d'),$_POST); $_SESSION['flash']=tr('Krátká reflexe je uložená.'); redirect_to('?view=dashboard');
        }
    } catch (Throwable $e) {
        if (in_array($action,['adaptive_help_event','adaptive_tutor'],true)) { http_response_code(400); header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE); exit; }
        $_SESSION['flash']=$e->getMessage(); redirect_to('?view=dashboard');
    }
}
