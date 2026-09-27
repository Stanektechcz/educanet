<?php

declare(strict_types=1);

/**
 * POST project_* – týmový pracovní prostor.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$projectWorkspaceActions = ['project_role_select','project_role_remove','project_task_create','project_task_update','project_task_status','project_decision_create','project_checkin','project_qa_create','project_qa_status','project_self_retro','project_team_retro','project_peer_feedback','project_portfolio_toggle'];
if (in_array($action,$projectWorkspaceActions,true)) {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas do své třídy.');redirect_to('?view=home');}
    $studentKey=social_current_student_key($actionClassId);
    if($studentKey===''||!social_student_exists($actionClassId,$studentKey)){$_SESSION['flash']=tr('Studentský profil není propojený se třídou.');redirect_to('?view=link_account');}
    $projectId=trim((string)($_POST['project_id']??''));$groupId=trim((string)($_POST['group_id']??''));$returnSection='overview';
    try {
        if($action==='project_role_select'){$returnSection='team';project_role_assign($actionClassId,$projectId,$groupId,$studentKey,(string)($_POST['role']??''),(string)($_POST['role_type']??'primary'),$studentKey);$_SESSION['flash']=tr('Role byla přidaná. Odpovědnost můžeš kdykoliv doplnit nebo změnit.');}
        elseif($action==='project_role_remove'){$returnSection='team';project_role_remove($actionClassId,$projectId,$groupId,$studentKey,(string)($_POST['role']??''),$studentKey);$_SESSION['flash']=tr('Role byla odebraná.');}
        elseif($action==='project_task_create'){$returnSection='work';project_task_create($actionClassId,$projectId,$groupId,$studentKey,$_POST);$_SESSION['flash']=tr('Týmový úkol byl vytvořen.');}
        elseif($action==='project_task_update'){$returnSection='work';project_task_update($actionClassId,$projectId,$groupId,$studentKey,(string)($_POST['task_id']??''),$_POST);$_SESSION['flash']=tr('Úkol byl upraven.');}
        elseif($action==='project_task_status'){$returnSection='work';project_task_set_status($actionClassId,$projectId,$groupId,$studentKey,(string)($_POST['task_id']??''),(string)($_POST['status']??'todo'),(string)($_POST['blocked_category']??''),(string)($_POST['blocked_reason']??''));$_SESSION['flash']=tr('Stav úkolu byl uložen.');}
        elseif($action==='project_decision_create'){$returnSection='team';project_decision_create($actionClassId,$projectId,$groupId,$studentKey,$_POST);$_SESSION['flash']=tr('Důležité rozhodnutí je v Decision Logu.');}
        elseif($action==='project_checkin'){$returnSection='team';$input=$_POST;if(isset($_POST['visibility'])&&$_POST['visibility']==='teacher')$input['visibility']='teacher';project_checkin_submit($actionClassId,$projectId,$groupId,$studentKey,$input);$_SESSION['flash']=tr('Check-in je uložený.');}
        elseif($action==='project_qa_create'){$returnSection='qa';project_qa_create($actionClassId,$projectId,$groupId,$studentKey,$_POST);$_SESSION['flash']=tr('QA nález byl vytvořen.');}
        elseif($action==='project_qa_status'){$returnSection='qa';project_qa_set_status($actionClassId,$projectId,$groupId,$studentKey,(string)($_POST['qa_id']??''),(string)($_POST['status']??'open'));$_SESSION['flash']=tr('QA stav byl aktualizovaný.');}
        elseif($action==='project_self_retro'){$returnSection='reflect';project_self_retro_submit($actionClassId,$projectId,$groupId,$studentKey,$_POST);$_SESSION['flash']=tr('Sebereflexe je uložená.');}
        elseif($action==='project_team_retro'){$returnSection='reflect';project_team_retro_submit($actionClassId,$projectId,$groupId,$studentKey,$_POST);$_SESSION['flash']=tr('Týmová retrospektiva je uložená.');}
        elseif($action==='project_peer_feedback'){$returnSection='reflect';project_peer_feedback_submit($actionClassId,$projectId,$groupId,$studentKey,(string)($_POST['reviewee_key']??''),$_POST);$_SESSION['flash']=project_feedback_flagged((string)($_POST['praise']??'').' '.(string)($_POST['improve']??''))?tr('Peer feedback je uložený. Čeká na kontrolu učitele.'):tr('Peer feedback je uložený.');}
        elseif($action==='project_portfolio_toggle'){$returnSection='reflect';$featured=project_portfolio_toggle($actionClassId,$projectId,$groupId,$studentKey);$_SESSION['flash']=$featured?tr('Projekt je nyní ve Featured portfoliu na tvém profilu.'):tr('Projekt byl z Featured portfolia odebraný.');}
        project_refresh_student_gamification($actionClassId,$studentKey);
    } catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to(project_workspace_url($projectId,$returnSection));
}
