<?php

declare(strict_types=1);

/**
 * POST profil, přátelé, týmová lobby, prestižní zkoušky.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$socialActions = ['save_student_profile','friend_request','friend_accept','friend_reject','friend_remove','team_vote_plan','team_create_lobby','team_join_lobby','team_leave_lobby','team_regenerate_code','team_finalize_lobby','submit_prestige_exam'];
if (in_array($action, $socialActions, true)) {
    $actionClassId = current_class_id($modules);
    if ($actionClassId === null || !isset($modules[$actionClassId])) { $_SESSION['flash']=tr('Nejdřív se přihlas do své třídy.'); redirect_to('?view=home'); }
    $studentKey = social_current_student_key($actionClassId);
    if ($studentKey === '' || !social_student_exists($actionClassId,$studentKey)) { $_SESSION['flash']=tr('Studentský profil není propojený se třídou.'); redirect_to('?view=link_account'); }
    try {
        if ($action === 'save_student_profile') {
            social_profile_save($actionClassId,$studentKey,$_POST);
            $_SESSION['flash']=tr('Profil byl uložen.');
            redirect_to('?view=profile&tab=nastaveni');
        }
        if ($action === 'friend_request') {
            friendship_request($actionClassId,$studentKey,(string)($_POST['student_key']??''));
            $_SESSION['flash']=tr('Žádost o propojení byla odeslána.');
            redirect_to('?view=community');
        }
        if ($action === 'friend_accept' || $action === 'friend_reject') {
            friendship_respond($actionClassId,$studentKey,(string)($_POST['student_key']??''),$action==='friend_accept');
            $_SESSION['flash']=$action==='friend_accept'?tr('Propojení je potvrzené.'):tr('Žádost byla odmítnuta.');
            redirect_to('?view=community');
        }
        if ($action === 'friend_remove') {
            friendship_remove($actionClassId,$studentKey,(string)($_POST['student_key']??''));
            $_SESSION['flash']=tr('Propojení bylo odebráno.');
            redirect_to('?view=community');
        }
        $projectId = trim((string)($_POST['project_id']??''));
        if (str_starts_with($action,'team_')) {
            $project=project_find($actionClassId,$projectId);
            if(!$project||(string)($project['type']??'')!=='group') throw new RuntimeException(tr('Skupinový projekt nebyl nalezen.'));
            if ($action === 'team_vote_plan') { team_plan_vote($actionClassId,$projectId,$studentKey,(string)($_POST['plan_id']??'')); $_SESSION['flash']=tr('Tvoje preference rozdělení byla uložená.'); }
            elseif ($action === 'team_create_lobby') { team_lobby_create($actionClassId,$projectId,$studentKey,(string)($_POST['lobby_name']??''),(int)($_POST['capacity']??0)); $_SESSION['flash']=tr('Lobby je založené. Nasdílej spolužákům invite kód.'); }
            elseif ($action === 'team_join_lobby') { team_lobby_join_code($actionClassId,$projectId,$studentKey,(string)($_POST['invite_code']??'')); $_SESSION['flash']=tr('Připojil/a ses do lobby.'); }
            elseif ($action === 'team_leave_lobby') { team_lobby_leave($actionClassId,(string)($_POST['lobby_id']??''),$studentKey); $_SESSION['flash']=tr('Lobby jsi opustil/a.'); }
            elseif ($action === 'team_regenerate_code') { team_lobby_regenerate_code($actionClassId,(string)($_POST['lobby_id']??''),$studentKey); $_SESSION['flash']=tr('Invite kód byl změněn. Starý kód už neplatí.'); }
            elseif ($action === 'team_finalize_lobby') { team_lobby_finalize($actionClassId,(string)($_POST['lobby_id']??''),$studentKey); $_SESSION['flash']=tr('Tým je uzamčený a připravený pro projekt.'); }
            redirect_to('?view=project_lobbies&project='.rawurlencode($projectId));
        }
        if ($action === 'submit_prestige_exam') {
            $examId=(string)($_POST['exam_id']??'');
            $answers=is_array($_POST['answers']??null)?$_POST['answers']:[];
            $result=special_exam_submit($actionClassId,$examId,$answers);
            $simMap=is_array($simulations[$actionClassId]??null)?$simulations[$actionClassId]:[];
            learning_refresh_badges($actionClassId,$modules[$actionClassId],$simMap);
            $_SESSION['flash']=!empty($result['passed'])?tr('Prestižní zkouška splněna: {score} / {max}.',['score'=>(int)$result['score'],'max'=>(int)$result['max_score']]):tr('Výsledek {score} / {max}. Zkoušku můžeš po přípravě zopakovat.',['score'=>(int)$result['score'],'max'=>(int)$result['max_score']]);
            redirect_to('?view=prestige_exams&exam='.rawurlencode($examId));
        }
    } catch (Throwable $e) {
        $_SESSION['flash']=$e->getMessage();
        $fallback=$projectId??'';
        redirect_to($fallback!==''?'?view=project_lobbies&project='.rawurlencode($fallback):'?view=community');
    }
}
