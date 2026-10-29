<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v47.1 · Adaptive Study Accelerator
 *
 * Evidence-driven guidance. Read-only planning never changes grades, XP or
 * mastery. Only explicit probe/comfort feedback writes to storage.
 */

function v471_feedback_store(): array { return coach_store('difficulty_feedback'); }
function v471_feedback_key(string $classId,string $studentKey,string $topic): string { return $classId.'|'.$studentKey.'|'.$topic; }

function v471_difficulty_feedback(string $classId,string $studentKey,string $topic): array
{
    $row=v471_feedback_store()[v471_feedback_key($classId,$studentKey,$topic)]??[];
    return is_array($row)?$row:[];
}

function v471_difficulty_feedback_save(string $classId,string $studentKey,string $topic,string $rating): void
{
    if($studentKey==='') throw new RuntimeException(tr('Studentský profil nebyl nalezen.'));
    if(!in_array($rating,['too_easy','right','too_hard'],true)) throw new RuntimeException(tr('Neplatná zpětná vazba.'));
    $id=v471_feedback_key($classId,$studentKey,$topic);
    coach_store_update_row('difficulty_feedback',$id,static function(?array $old) use($id,$classId,$studentKey,$topic,$rating): array {
        $history=is_array($old['history']??null)?$old['history']:[];$history[]=['rating'=>$rating,'at'=>date(DATE_ATOM)];if(count($history)>20)$history=array_slice($history,-20);
        return ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'rating'=>$rating,'history'=>$history,'updated_at'=>date(DATE_ATOM)];
    });
}

function v471_topic_lesson_cluster(string $classId,string $topic): string
{
    foreach(v42_lessons_for_class($classId) as $lesson){
        if(!is_array($lesson))continue;
        foreach((array)($lesson['knowledge']??[]) as $t)if((string)$t===$topic)return 'lesson_'.(int)($lesson['number']??0);
    }
    return 'topic_'.preg_replace('/[^a-z0-9]+/i','_',substr($topic,0,24));
}

function v471_topic_evidence(string $classId,string $studentKey,string $topic,array $module,array $tourMap): array
{
    $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];
    $tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];
    $progress=learning_kb_progress($classId,$topic);
    $state=is_array(adaptive_retrieval_state_rows()[adaptive_retrieval_key($classId,$studentKey,$topic)]??null)?adaptive_retrieval_state_rows()[adaptive_retrieval_key($classId,$studentKey,$topic)]:[];
    $events=function_exists('ml_student_events')?ml_student_events($classId,$studentKey):[];
    $wrong=0;$correct=0;$highWrong=0;$latest='';
    foreach($events as $e){
        if(!is_array($e)||(string)($e['topic']??'')!==$topic)continue;
        $at=(string)($e['created_at']??'');if($at>$latest)$latest=$at;
        if(!empty($e['correct']))$correct++;else{$wrong++;if((int)($e['confidence']??0)>=3)$highWrong++;}
    }
    $streak=(int)($state['streak']??0);$incorrect=max((int)($state['incorrect_count']??0),$wrong);
    $feedback=v471_difficulty_feedback($classId,$studentKey,$topic);$rating=(string)($feedback['rating']??'');
    $complete=!empty($progress['complete']);$started=$complete||!empty($progress['visual'])||!empty($progress['steps'])||!empty($progress['check']);
    $support=0;
    if(!$started)$support+=45;
    if(!$complete)$support+=20;
    $support+=min(36,$incorrect*9);
    if($highWrong>0)$support+=min(20,$highWrong*7);
    $support-=min(32,$streak*10);
    if($rating==='too_hard')$support+=22;elseif($rating==='too_easy')$support-=18;elseif($rating==='right')$support-=4;
    $stage=$support>=55?'model':($support>=32?'guided':($support>=12?'faded':'independent'));
    $due=(string)($state['due_date']??'');$today=date('Y-m-d');$risk=0;
    if($due!==''&&$due<=$today)$risk+=35;
    $risk+=min(40,$incorrect*8);$risk+=max(0,18-$streak*5);
    if($complete)$risk-=8;
    $masteryReady=$complete&&$streak>=2&&$incorrect<=2&&$rating!=='too_hard';
    return [
        'topic'=>$topic,'title'=>(string)($article['title']??$topic),'summary'=>(string)($article['summary']??$tour['mental']??''),'mental'=>(string)($tour['mental']??$article['summary']??''),
        'steps'=>array_values(array_filter(array_map('strval',(array)($tour['steps']??[])))),'mistakes'=>array_values(array_filter(array_map('strval',(array)($tour['mistakes']??[])))),'check'=>is_array($tour['check']??null)?$tour['check']:[],
        'complete'=>$complete,'started'=>$started,'streak'=>$streak,'incorrect'=>$incorrect,'high_confidence_wrong'=>$highWrong,'last_at'=>$latest,'due_date'=>$due,'feedback'=>$rating,
        'support_score'=>$support,'stage'=>$stage,'risk_score'=>$risk,'mastery_ready'=>$masteryReady,'cluster'=>v471_topic_lesson_cluster($classId,$topic),
    ];
}

function v471_stage_label(string $stage): string
{
    return match($stage){'model'=>tr('Ukázka krok za krokem'),'guided'=>tr('S vedením'),'faded'=>tr('Doplň chybějící kroky'),'independent'=>tr('Samostatná aplikace'),default=>tr('Adaptivní trénink')};
}

function v471_stage_copy(string $stage): string
{
    return match($stage){
        'model'=>tr('Nejdřív si prohlédni jasný model postupu. Cílem je pochopit rozhodnutí, ne memorovat text.'),
        'guided'=>tr('Princip už máš částečně zachycený. Některé kroky zůstanou jako opora, zbytek doplníš sám/sama.'),
        'faded'=>tr('Podpora se stahuje. Zkus nejdřív doplnit chybějící kroky z paměti a až potom je odkryj.'),
        'independent'=>tr('Důkazy ukazují, že už nepotřebuješ plnou ukázku. Začni bez nápovědy a kontroluj až po pokusu.'),
        default=>tr('Podpora se přizpůsobuje skutečným výsledkům.')};
}

function v471_interleaved_topics(string $classId,string $studentKey,array $module,array $tourMap,int $limit=4): array
{
    $rows=[];
    foreach((array)($module['knowledgebase']??[]) as $topic=>$_){
        $topic=(string)$topic;$e=v471_topic_evidence($classId,$studentKey,$topic,$module,$tourMap);
        $score=(int)$e['risk_score']+($e['feedback']==='too_hard'?18:0)+(!$e['complete']?12:0)+(!$e['started']?-30:0);$e['priority']=$score;$rows[]=$e;
    }
    usort($rows,static fn($a,$b)=>(int)$b['priority']<=>(int)$a['priority']);
    $out=[];$used=[];
    foreach($rows as $row){if(isset($used[$row['cluster']]))continue;$out[]=$row;$used[$row['cluster']]=true;if(count($out)>=$limit)break;}
    if(count($out)<$limit){foreach($rows as $row){if(in_array($row['topic'],array_column($out,'topic'),true))continue;$out[]=$row;if(count($out)>=$limit-1)break;}}
    return $out;
}

function v471_focus_topic(string $classId,string $studentKey,array $module,array $tourMap,string $requested=''): ?array
{
    if($requested!==''&&isset($module['knowledgebase'][$requested]))return v471_topic_evidence($classId,$studentKey,$requested,$module,$tourMap);
    $mistakes=coach_mistake_notebook($classId,$studentKey,$module,10);
    foreach($mistakes as $m)if(empty($m['recovered'])&&isset($module['knowledgebase'][(string)$m['topic']]))return v471_topic_evidence($classId,$studentKey,(string)$m['topic'],$module,$tourMap);
    $mix=v471_interleaved_topics($classId,$studentKey,$module,$tourMap,4);if($mix)return $mix[0];
    foreach((array)($module['knowledgebase']??[]) as $topic=>$_)return v471_topic_evidence($classId,$studentKey,(string)$topic,$module,$tourMap);
    return null;
}

function v471_probe_submit(string $classId,string $studentKey,string $topic,int $answer,int $confidence,array $module,array $tourMap): array
{
    if(!isset($module['knowledgebase'][$topic]))throw new RuntimeException(tr('Téma nebylo nalezeno.'));
    $tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];$check=is_array($tour['check']??null)?$tour['check']:[];
    $options=array_values(array_map('strval',(array)($check['options']??[])));$correct=(int)($check['correct']??-1);
    $result=adaptive_retrieval_submit($classId,$studentKey,[$topic=>$answer],$module,$tourMap,[$topic=>$confidence]);
    $row=is_array($result['results'][0]??null)?$result['results'][0]:[];
    return [
        'topic'=>$topic,'question'=>(string)($check['q']??''),'correct'=>!empty($row['correct']),'why'=>(string)($row['why']??''),
        'selected'=>$answer,'correct_index'=>$correct,'selected_label'=>(string)($options[$answer]??''),'correct_label'=>(string)($options[$correct]??''),'confidence'=>$confidence,
        'score'=>(int)($result['score']??0),'total'=>(int)($result['total']??0),'at'=>date(DATE_ATOM),
    ];
}

function v471_session_snapshot(string $classId,string $studentKey,array $module,array $tourMap,string $requested=''): array
{
    $focus=v471_focus_topic($classId,$studentKey,$module,$tourMap,$requested);$mix=v471_interleaved_topics($classId,$studentKey,$module,$tourMap,4);
    if(!$focus)return ['focus'=>null,'mix'=>$mix];
    $steps=(array)$focus['steps'];$stage=(string)$focus['stage'];$visible=[];$hidden=[];
    foreach($steps as $i=>$step){
        $show=$stage==='model'||($stage==='guided'&&$i<max(1,count($steps)-1))||($stage==='faded'&&$i%2===0);
        if($show)$visible[$i]=$step;else$hidden[$i]=$step;
    }
    $check=is_array($focus['check']??null)?$focus['check']:[];
    return ['focus'=>$focus,'mix'=>$mix,'visible_steps'=>$visible,'hidden_steps'=>$hidden,'check'=>$check,'mastery_ready'=>!empty($focus['mastery_ready'])];
}
