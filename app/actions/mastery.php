<?php

declare(strict_types=1);

/**
 * POST ml_* – mastery learning.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$mlActions = ['ml_transfer','ml_explain_back','ml_terminal','ml_live_answer','ml_student_scenario','ml_goal_save','ml_class_tip','ml_portfolio_narrative','ml_scenario_answer'];
if (in_array($action,$mlActions,true)) {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $studentKey=adaptive_student_key($actionClassId);
    try {
        if($action==='ml_transfer'){
            $result=ml_transfer_submit($actionClassId,$studentKey,(string)($_POST['topic']??''),(int)($_POST['answer']??-1),(int)($_POST['confidence']??0),$modules[$actionClassId]);
            header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]+$result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
        }
        if($action==='ml_scenario_answer'){
            $result=ml_scenario_submit($actionClassId,$studentKey,(string)($_POST['scenario_id']??''),(int)($_POST['answer']??-1),(int)($_POST['confidence']??0));
            header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]+$result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
        }
        if($action==='ml_terminal'){
            $result=ml_terminal_command((string)($_POST['topic']??''),(string)($_POST['command']??''));
            header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]+$result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
        }
        if($action==='ml_explain_back'){
            $topic=(string)($_POST['topic']??'');ml_explain_back_save($actionClassId,$studentKey,$topic,(string)($_POST['text']??''));$_SESSION['flash']=tr('Vysvětlení je uložené.');redirect_to(module_url('kb_lesson',['topic'=>$topic,'reference'=>1]));
        }
        if($action==='ml_live_answer'){
            ml_live_answer($actionClassId,$studentKey,(string)($_POST['live_id']??''),(int)($_POST['answer']??-1));$_SESSION['flash']=tr('Odpověď je uložená.');redirect_to('?view=dashboard');
        }
        if($action==='ml_student_scenario'){
            ml_scenario_save(['class_id'=>$actionClassId,'title'=>$_POST['title']??'','brief'=>$_POST['brief']??'','choice_0'=>$_POST['choice_0']??'','choice_1'=>$_POST['choice_1']??'','choice_2'=>$_POST['choice_2']??'','correct'=>$_POST['correct']??0,'why'=>$_POST['why']??'','student_key'=>$studentKey],false);$_SESSION['flash']=tr('Challenge byl odeslán učiteli ke schválení.');redirect_to('?view=create_challenge');
        }
        if($action==='ml_goal_save'){
            ml_goal_save($actionClassId,$studentKey,(string)($_POST['strength']??''),(string)($_POST['focus']??''),(string)($_POST['evidence']??''));$_SESSION['flash']=tr('Osobní mastery cíl je uložený.');redirect_to('?view=dashboard');
        }
        if($action==='ml_class_tip'){
            $topic=(string)($_POST['topic']??'');ml_tip_save($actionClassId,$studentKey,$topic,(string)($_POST['text']??''),false);$_SESSION['flash']=tr('Tip byl odeslán učiteli ke schválení.');redirect_to(module_url('kb_lesson',['topic'=>$topic,'reference'=>1]));
        }
        if($action==='ml_portfolio_narrative'){
            $recordId=(string)($_POST['record_id']??'');ml_portfolio_narrative_save($actionClassId,$studentKey,$recordId,$_POST);$_SESSION['flash']=tr('Příběh projektu je uložený.');redirect_to(module_url('project_result',['record'=>$recordId]));
        }
    } catch(Throwable $e){if(in_array($action,['ml_transfer','ml_terminal','ml_scenario_answer'],true)){http_response_code(400);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);exit;}$_SESSION['flash']=$e->getMessage();redirect_to('?view=dashboard');}
}
