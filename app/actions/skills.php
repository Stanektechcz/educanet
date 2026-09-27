<?php

declare(strict_types=1);

/**
 * POST skill_* – dovednosti.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$skillActions = ['skill_mark_learning','skill_quick_check','skill_request_validation','skill_branch_challenge'];
if (in_array($action, $skillActions, true)) {
    $actionClassId = current_class_id($modules);
    if ($actionClassId === null || !isset($modules[$actionClassId])) { $_SESSION['flash']=tr('Nejdřív se přihlas do své třídy.'); redirect_to('?view=home'); }
    try {
        if ($action === 'skill_mark_learning') {
            $slug=(string)($_POST['skill']??''); skill_learning_mark($actionClassId,$slug);
            $_SESSION['flash']=tr('Studijní evidence byla započítána. Mastery se přepočítala.');
            redirect_to(module_url('skill_detail',['skill'=>$slug]));
        }
        if ($action === 'skill_quick_check') {
            $slug=(string)($_POST['skill']??''); $mode=(string)($_POST['mode']??'practice'); $answers=is_array($_POST['answers']??null)?$_POST['answers']:[];
            $result=skill_submit_quick_check($actionClassId,$slug,$answers,$mode==='mastery'?'mastery':'practice');
            $_SESSION['flash']=tr('Výsledek: {score} %.',['score'=>edu_number((float)$result['score'],0)]).' '.(!empty($result['passed'])?tr('Evidence byla započítána.'):tr('Pro mastery potřebuješ alespoň 80 %.'));
            redirect_to(module_url('skill_detail',['skill'=>$slug]));
        }
        if ($action === 'skill_request_validation') {
            $slug=(string)($_POST['skill']??''); skill_request_validation($actionClassId,$slug,(string)($_POST['note']??''));
            $_SESSION['flash']=tr('Žádost o praktickou validaci byla odeslána učiteli.');
            redirect_to(module_url('skill_detail',['skill'=>$slug]));
        }
        if ($action === 'skill_branch_challenge') {
            $challenge=(string)($_POST['challenge']??''); $answers=is_array($_POST['answers']??null)?$_POST['answers']:[]; $result=skill_submit_branch_challenge($actionClassId,$challenge,$answers,(int)($_POST['hints']??0));
            redirect_to(module_url('mastery_result',['attempt'=>(string)$result['id']]));
        }
    } catch (Throwable $e) {
        $_SESSION['flash']=$e->getMessage();
        redirect_to('?view=skills');
    }
}
