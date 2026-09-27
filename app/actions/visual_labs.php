<?php

declare(strict_types=1);

/**
 * POST v42_set_lane, v481_deep_submit, v48_*, v45_sim_save, cv43_*, v44_*.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'v42_set_lane') {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $lesson=v42_find_lesson($actionClassId,(int)($_POST['lesson_number']??0));
    if(!$lesson){$_SESSION['flash']=tr('Lekce nebyla nalezena.');redirect_to('?view=course');}
    v42_lane_preference_save($actionClassId,adaptive_student_key($actionClassId),$lesson,(string)($_POST['lane']??'auto'));
    $_SESSION['flash']=tr('Cesta lekcí byla upravena.');
    redirect_to(v42_lesson_url($lesson));
}


if($action==='v481_deep_submit'){
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $lessonNo=max(1,min(28,(int)($_POST['lesson_number']??0)));$lesson=v42_find_lesson($actionClassId,$lessonNo);
    if(!$lesson||!v481_available_for($actionClassId,$lesson)){$_SESSION['flash']=tr('Deep Visual Lab nebyl nalezen.');redirect_to('?view=course');}
    try{
        $result=v481_submit($actionClassId,adaptive_student_key($actionClassId),$lesson,(string)($_POST['lab_mode']??'practice'),is_array($_POST['answers']??null)?$_POST['answers']:[],(string)($_POST['reflection']??''));
        $row=(array)($result['row']??[]);
        $_SESSION['flash']=!empty($row['completed'])?tr('Deep Visual Lab je dokončený. Praktická evidence je pouze formativní.'):tr('Pokus je uložený bez dopadu na známku. Oprav checkpointy, které ještě nesedí, a zkus to znovu.');
    }catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to(module_url('visual_lab',['lesson'=>$lessonNo,'lab_mode'=>(string)($_POST['lab_mode']??'practice')]).'#v481-deep');
}


$v48Actions=['v48_prediction','v48_compare','v48_debug','v48_build','v48_transfer'];
if(in_array($action,$v48Actions,true)){
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $lessonNo=max(1,min(28,(int)($_POST['lesson_number']??0)));$lesson=v42_find_lesson($actionClassId,$lessonNo);
    if(!$lesson){$_SESSION['flash']=tr('Visual Lab nebyl nalezen.');redirect_to('?view=course');}
    $studentKey=adaptive_student_key($actionClassId);
    try{
        if($action==='v48_prediction')$result=v48_prediction_submit($actionClassId,$studentKey,$lesson,(int)($_POST['answer']??-1));
        elseif($action==='v48_compare')$result=v48_compare_submit($actionClassId,$studentKey,$lesson,(int)($_POST['answer']??-1));
        elseif($action==='v48_debug')$result=v48_debug_submit($actionClassId,$studentKey,$lesson,(int)($_POST['answer']??-1));
        elseif($action==='v48_build')$result=v48_build_submit($actionClassId,$studentKey,$lesson,array_slice(array_map('strval',(array)($_POST['sequence']??[])),0,16));
        else $result=v48_transfer_submit($actionClassId,$studentKey,$lesson,(string)($_POST['reflection']??''));
        $_SESSION['flash']=!empty($result['correct'])?tr('Formativní krok je hotový. Pokračuj dál v praktickém cyklu.'):tr('Výsledek nemění známku. Uprav mentální model a zkus další část.');
    }catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    $anchor=match($action){'v48_prediction'=>'v48-predict','v48_compare'=>'v48-contrast','v48_debug'=>'v48-debug','v48_build'=>'v48-build',default=>'v48-transfer'};
    redirect_to(module_url('visual_lab',['lesson'=>$lessonNo]).'#'.$anchor);
}

if ($action === 'v45_sim_save') {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){http_response_code(401);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>tr('Nejdřív se přihlas.')],JSON_UNESCAPED_UNICODE);exit;}
    $lesson=v42_find_lesson($actionClassId,max(1,min(28,(int)($_POST['lesson_number']??0))));
    if(!$lesson){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>tr('Lekce nebyla nalezena.')],JSON_UNESCAPED_UNICODE);exit;}
    $studentKey=adaptive_student_key($actionClassId);
    try {
        $params=json_decode((string)($_POST['params']??'{}'),true);if(!is_array($params))$params=[];
        $result=v45_sim_snapshot_save($actionClassId,$studentKey,$lesson,(string)($_POST['slot']??'best'),$params,(string)($_POST['scenario']??'manual'),(string)($_POST['note']??''));
        header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]+$result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
    } catch(Throwable $e){http_response_code(400);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);exit;}
}


$cv43Actions = ['cv43_prediction','cv43_model_submit'];
if (in_array($action,$cv43Actions,true)) {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){http_response_code(401);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>tr('Nejdřív se přihlas.')],JSON_UNESCAPED_UNICODE);exit;}
    $lesson=v42_find_lesson($actionClassId,max(1,min(28,(int)($_POST['lesson_number']??0))));
    if(!$lesson){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>tr('Lekce nebyla nalezena.')],JSON_UNESCAPED_UNICODE);exit;}
    $studentKey=adaptive_student_key($actionClassId);
    try {
        if($action==='cv43_prediction'){
            $result=cv43_prediction_submit($actionClassId,$studentKey,$lesson,(int)($_POST['answer']??-1),(int)($_POST['confidence']??0));
        } else {
            $seq=json_decode((string)($_POST['sequence']??'[]'),true);if(!is_array($seq))$seq=[];
            $result=cv43_model_submit($actionClassId,$studentKey,$lesson,array_slice(array_map('strval',$seq),0,12),(string)($_POST['phase']??'current'));
        }
        header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]+$result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
    } catch(Throwable $e){http_response_code(400);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);exit;}
}

$v44Actions = ['v44_teachback','v44_memory_save','v44_stage_complete'];
if (in_array($action,$v44Actions,true)) {
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!isset($modules[$actionClassId])){http_response_code(401);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>tr('Nejdřív se přihlas.')],JSON_UNESCAPED_UNICODE);exit;}
    $lesson=v42_find_lesson($actionClassId,max(1,min(28,(int)($_POST['lesson_number']??0))));
    if(!$lesson){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>tr('Lekce nebyla nalezena.')],JSON_UNESCAPED_UNICODE);exit;}
    $studentKey=adaptive_student_key($actionClassId);
    try {
        if($action==='v44_teachback')$result=v44_teachback_submit($actionClassId,$studentKey,$lesson,(string)($_POST['text']??''));
        elseif($action==='v44_memory_save')$result=v44_memory_save($actionClassId,$studentKey,$lesson,$_POST);
        else $result=v44_stage_complete($actionClassId,$studentKey,$lesson,(string)($_POST['stage']??''));
        header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]+$result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
    } catch(Throwable $e){http_response_code(400);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);exit;}
}
