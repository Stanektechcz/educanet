<?php

declare(strict_types=1);

/**
 * POST teacher_task_complete a coach_* (Learning Coach).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'teacher_task_complete') {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $studentKey=social_current_student_key($actionClassId);
    if($studentKey===''){$_SESSION['flash']=tr('Nepodařilo se určit studentský profil.');redirect_to('?view=dashboard');}
    try{teacher_student_task_set_status((string)($_POST['task_id']??''),$actionClassId,$studentKey,'done');$_SESSION['flash']=tr('Úkol je označený jako hotový.');}catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to('?view=dashboard');
}
$coachActions=['coach_preferences_save','coach_step_toggle','coach_exit_ticket','coach_probe_submit','coach_difficulty_feedback','coach_corrective_complete'];
if(in_array($action,$coachActions,true)){
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $studentKey=adaptive_student_key($actionClassId);
    if($studentKey===''){$_SESSION['flash']=tr('Nepodařilo se určit studentský profil.');redirect_to('?view=dashboard');}
    $minutes=(int)($_POST['minutes']??25);if(!in_array($minutes,[10,25,45],true))$minutes=25;
    $energy=(string)($_POST['energy']??'normal');if(!in_array($energy,['low','normal','high'],true))$energy='normal';
    $intent=(string)($_POST['intent']??'balanced');if(!in_array($intent,['balanced','exam'],true))$intent='balanced';
    try{
        if($action==='coach_preferences_save'){coach_preferences_save($actionClassId,$studentKey,$minutes,$intent);$_SESSION['flash']=tr('Výchozí délka Learning Coach plánu je uložená.');}
        elseif($action==='coach_step_toggle'){coach_step_set($actionClassId,$studentKey,(string)($_POST['item_id']??''),(string)($_POST['done']??'1')==='1');}
        elseif($action==='coach_probe_submit'){
            $topic=(string)($_POST['topic']??'');$answer=(int)($_POST['answer']??-1);$confidence=max(1,min(3,(int)($_POST['confidence']??2)));
            $_SESSION['v471_probe_result']=v471_probe_submit($actionClassId,$studentKey,$topic,$answer,$confidence,$modules[$actionClassId],is_array($knowledgeTours[$actionClassId]??null)?$knowledgeTours[$actionClassId]:[]);
            $_SESSION['flash']=!empty($_SESSION['v471_probe_result']['correct'])?tr('Mikrodiagnostika: princip sis vybavil/a správně. Podpora se může postupně stáhnout.'):tr('Mikrodiagnostika našla mezeru. To je přesně chvíle, kdy má smysl použít více opory.');
            redirect_to(module_url('study_loop',['topic'=>$topic]));
        }
        elseif($action==='coach_difficulty_feedback'){
            $topic=(string)($_POST['topic']??'');$rating=(string)($_POST['rating']??'right');v471_difficulty_feedback_save($actionClassId,$studentKey,$topic,$rating);
            $_SESSION['flash']=tr('Množství podpory se pro další cyklus přizpůsobí.');redirect_to(module_url('study_loop',['topic'=>$topic]));
        }
        elseif($action==='coach_corrective_complete'){
            $topic=(string)($_POST['topic']??'');$outcome=(string)($_POST['transfer_outcome']??'self_checked');
            v472_corrective_cycle_complete($actionClassId,$studentKey,$topic,$outcome);unset($_SESSION['v471_probe_result']);
            $_SESSION['flash']=$outcome==='needs_review'?tr('Opravný cyklus je uložený. Systém nechá u tématu více opory.'):tr('Opravný cyklus je hotový. Výsledek je pouze formativní a nemění známku.');
            redirect_to(module_url('study_loop',['topic'=>$topic,'recovered'=>1]));
        }
        else{coach_exit_ticket_save($actionClassId,$studentKey,$_POST);$_SESSION['flash']=tr('Dnešní exit ticket je uložený. Teď můžeš s klidem skončit.');}
    }catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to(module_url('study',['minutes'=>$minutes,'energy'=>$energy,'intent'=>$intent]));
}
