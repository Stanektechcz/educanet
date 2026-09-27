<?php

declare(strict_types=1);

/**
 * POST v50_* (Hands-on, peer, growth) a v504_goal_*.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$v50HandsActions=['v50_hypothesis','v50_command','v50_validate','v50_explain','v50_transfer','v50_hint'];
if(in_array($action,$v50HandsActions,true)){
    $actionClassId=current_class_id($modules);
    if($actionClassId===null||!in_array($actionClassId,['class_3a','class_4a'],true)){$_SESSION['flash']=tr('Hands-on Lab je dostupný pro SOSaPS.');redirect_to('?view=dashboard');}
    $studentKey=adaptive_student_key($actionClassId);$lessonNo=max(1,min(28,(int)($_POST['lesson_number']??0)));$lesson=v42_find_lesson($actionClassId,$lessonNo);
    if($studentKey===''||!$lesson){$_SESSION['flash']=tr('Hands-on Lab nebyl nalezen.');redirect_to('?view=course');}
    $spec=v50_lab_spec($actionClassId,$lesson,$studentKey);
    try{
        if($action==='v50_hypothesis'){v50_hypothesis_submit($actionClassId,$studentKey,$spec,(string)($_POST['hypothesis']??''));$_SESSION['flash']=tr('Hypotéza je commitnutá. Teď ji zkus vyvrátit důkazem.');}
        elseif($action==='v50_command'){$r=v50_command_submit($actionClassId,$studentKey,$spec,(string)($_POST['command']??''));$_SESSION['flash']=!empty($r['ok'])?tr('Příkaz proběhl. Neřeš jen výstup — pojmenuj, co skutečně dokazuje.'):(string)($r['output']??tr('Příkaz nebyl povolen v tomto sandboxu.'));}
        elseif($action==='v50_validate'){$r=v50_validate_state_submit($actionClassId,$studentKey,$spec);$_SESSION['flash']=!empty($r['correct'])?tr('Výsledný stav je správný. Teď vysvětli kauzalitu.'):tr('Checkpoint ještě nesedí. Vrať se k evidenci, ne k náhodným změnám.');}
        elseif($action==='v50_explain'){v50_explain_submit($actionClassId,$studentKey,$spec,(string)($_POST['explanation']??''));$_SESSION['flash']=tr('Vysvětlení root cause je uložené.');}
        elseif($action==='v50_transfer'){v50_transfer_submit($actionClassId,$studentKey,$spec,(string)($_POST['transfer']??''));$_SESSION['flash']=tr('Transfer je uložený. Pokud máš i validaci a vysvětlení, pro dnešek může stačit.');}
        else{v50_hint_record($actionClassId,$studentKey,$spec,(int)($_POST['hint_level']??1));$_SESSION['flash']=tr('Použití nápovědy je zaznamenané pouze formativně.');}
    }catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to(module_url('hands_on',['lesson'=>$lessonNo]));
}

if(in_array($action,['v50_peer_join','v50_peer_note'],true)){
    $actionClassId=current_class_id($modules);if($actionClassId===null||!in_array($actionClassId,['class_3a','class_4a'],true)){$_SESSION['flash']=tr('Peer debugging je dostupný pro SOSaPS.');redirect_to('?view=dashboard');}
    $studentKey=adaptive_student_key($actionClassId);$room=(string)($_POST['room_code']??'');
    try{if($action==='v50_peer_join'){$r=v50_peer_join($actionClassId,$studentKey,$room);$room=(string)$r['code'];$_SESSION['flash']=tr('Peer room je připravený. Každý člen má jinou roli.');}else{v50_peer_note($actionClassId,$studentKey,$room,(string)($_POST['note_type']??'evidence'),(string)($_POST['note']??''));$_SESSION['flash']=tr('Krok je sdílený se skupinou.');}}catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to(module_url('peer_lab',['room'=>$room]));
}

if(in_array($action,['v504_goal_select','v504_goal_clear'],true)){
    $actionClassId=current_class_id($modules);if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $studentKey=v504_goal_student_key($actionClassId);
    try{
        if($action==='v504_goal_select'){$goal=v504_goal_select($actionClassId,$studentKey,(string)($_POST['goal_id']??''));$_SESSION['flash']=tr('Cíl „{goal}“ je nastavený. Teď už jen pokračuj po jednotlivých krocích.',['goal'=>(string)$goal['title']]);}
        else{v504_goal_clear($actionClassId,$studentKey);$_SESSION['flash']=tr('Cíl je uvolněný. Vyber si nový směr, až budeš chtít.');}
    }catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to('?view=goal_nav');
}

if(in_array($action,['v50_growth_complete','v50_growth_capstone'],true)){
    $actionClassId=current_class_id($modules);if($actionClassId===null||!isset($modules[$actionClassId])){$_SESSION['flash']=tr('Nejdřív se přihlas.');redirect_to('?view=home');}
    $studentKey=adaptive_student_key($actionClassId);$pathId=(string)($_POST['path_id']??'');
    try{if($action==='v50_growth_complete'){v50_growth_complete_module($actionClassId,$studentKey,$pathId,(string)($_POST['module_slug']??''),(string)($_POST['reflection']??''));$_SESSION['flash']=tr('Dobrovolný modul je uložený do Growth Progressu. Školní Mastery se nemění.');}else{v50_growth_submit_capstone($actionClassId,$studentKey,$pathId,(string)($_POST['title']??''),(string)($_POST['reflection']??''),(string)($_POST['url']??''));$_SESSION['flash']=tr('Capstone je uložený ve Skill Passportu jako portfolio evidence.');}}catch(Throwable $e){$_SESSION['flash']=$e->getMessage();}
    redirect_to(module_url('growth_path',['path'=>$pathId]));
}
