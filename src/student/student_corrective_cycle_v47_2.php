<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v47.2 · Corrective Transfer Loop
 *
 * Short, non-graded remediation shown immediately after an incorrect
 * microdiagnostic answer. The loop never changes grades, XP or mastery.
 */

function v472_question_signature(string $text): string
{
    return preg_replace('/[^a-z0-9]+/i','',strtolower(trim($text))) ?: '';
}

function v472_normalize_question(array $row,string $source='bank'): ?array
{
    $q=trim((string)($row['question']??$row['q']??''));
    $rawOptions=$row['options']??null;
    if($q===''||!is_array($rawOptions)||count($rawOptions)<2)return null;
    $keys=array_keys($rawOptions);$options=array_values(array_map('strval',$rawOptions));
    $rawCorrect=$row['correct']??-1;$correct=-1;
    if(is_int($rawCorrect)||ctype_digit((string)$rawCorrect))$correct=(int)$rawCorrect;
    elseif(is_string($rawCorrect)){$idx=array_search($rawCorrect,$keys,true);if($idx!==false)$correct=(int)$idx;}
    if($correct<0||$correct>=count($options))return null;
    return [
        'question'=>$q,
        'options'=>$options,
        'correct'=>$correct,
        'why'=>trim((string)($row['explanation']??$row['why']??'')),
        'source'=>$source,
        'signature'=>v472_question_signature($q),
    ];
}

function v472_topic_question_bank(string $classId,string $topic,array $module,array $tourMap): array
{
    $out=[];$seen=[];
    $tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];
    if(is_array($tour['check']??null)){
        $q=v472_normalize_question((array)$tour['check'],'tour');
        if($q){$seen[$q['signature']]=true;$out[]=$q;}
    }
    foreach((array)($module['questions']??[]) as $row){
        if(!is_array($row)||(string)($row['kb']??'')!==$topic)continue;
        $q=v472_normalize_question($row,'diagnostic');if(!$q||isset($seen[$q['signature']]))continue;
        $seen[$q['signature']]=true;$out[]=$q;
    }
    // If the lesson-level cognitive lab is anchored to this exact topic, it is
    // a useful novel transfer item and remains separate from grading here.
    if(function_exists('v42_lessons_for_class')&&function_exists('cv43_lab_spec')){
        foreach(v42_lessons_for_class($classId) as $lesson){
            if(!is_array($lesson)||!in_array($topic,array_map('strval',(array)($lesson['knowledge']??[])),true))continue;
            $spec=cv43_lab_spec($classId,$lesson,$module);
            if((string)($spec['topic']??'')!==$topic)continue;
            $q=v472_normalize_question(['q'=>$spec['question']??'','options'=>$spec['options']??[],'correct'=>$spec['correct']??-1,'why'=>$spec['compare']['why']??''],'cognitive');
            if($q&&!isset($seen[$q['signature']])){$seen[$q['signature']]=true;$out[]=$q;}
            break;
        }
    }
    return $out;
}

function v472_topic_transfer_prompt(string $classId,string $topic,array $module): array
{
    $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];
    $prompt=tr('Přeneste princip do nové situace: změň konkrétní hodnoty nebo kontext a popiš, které rozhodnutí zůstává stejné a proč.');
    $context='';
    if(function_exists('v42_lessons_for_class')&&function_exists('cv43_lab_spec')){
        foreach(v42_lessons_for_class($classId) as $lesson){
            if(!is_array($lesson)||!in_array($topic,array_map('strval',(array)($lesson['knowledge']??[])),true))continue;
            $spec=cv43_lab_spec($classId,$lesson,$module);
            $context=trim((string)($spec['transfer']??''));
            if($context!=='')$prompt=tr('{context} Popiš konkrétně, co bys rozhodl/a nebo ověřil/a a proč.',['context'=>$context]);
            break;
        }
    }
    return ['prompt'=>$prompt,'criteria'=>array_values(array_filter([
        trim((string)($article['summary']??'')),
        tr('Pojmenuj rozhodující princip, ne jen konkrétní hodnotu z předchozího příkladu.'),
        tr('Uveď, co se v nové situaci změnilo a co naopak zůstává stejné.'),
    ]))];
}

function v472_corrective_cycle_spec(string $classId,string $studentKey,string $topic,array $probe,array $module,array $tourMap): array
{
    if($topic===''||!isset($module['knowledgebase'][$topic]))return [];
    $focus=v471_topic_evidence($classId,$studentKey,$topic,$module,$tourMap);
    $bank=v472_topic_question_bank($classId,$topic,$module,$tourMap);
    $probeSignature=v472_question_signature((string)($probe['question']??(($focus['check']['q']??''))));
    $alternates=array_values(array_filter($bank,static fn($q)=>($q['signature']??'')!==$probeSignature));
    $similar=$alternates[0]??null;
    $transfer=$alternates[1]??null;
    if(!$transfer&&$similar&&count($alternates)===1){
        // Prefer the only fresh closed question for the final transfer check;
        // the similar example can fall back to an explained worked scenario.
        $transfer=$similar;$similar=null;
    }
    $steps=array_values(array_filter(array_map('strval',(array)($focus['steps']??[]))));
    $fallbackSimilar=[
        'question'=>tr('Podobný případ se stejným principem'),
        'worked'=>true,
        'scenario'=>tr('Představ si stejný typ problému s jinými konkrétními hodnotami. ').($steps?tr('Začni krokem „{first}“ a potom pokračuj „{second}“.',['first'=>$steps[0],'second'=>$steps[1]??tr('ověř rozhodující podmínku')]):tr('Nejdřív určující podmínku pojmenuj, potom ji ověř na konkrétních datech.')),
        'why'=>trim((string)($focus['mental']??$focus['summary']??'')),
    ];
    $selectedLabel=trim((string)($probe['selected_label']??''));
    $correctLabel=trim((string)($probe['correct_label']??''));
    $mistake=trim((string)(($focus['mistakes'][0]??'') ?: tr('Přenést konkrétní odpověď bez kontroly, zda platí stejné podmínky.')));
    $principle=trim((string)($focus['mental']??$focus['summary']??''));
    $why=trim((string)($probe['why']??''));
    return [
        'topic'=>$topic,'title'=>(string)($focus['title']??$topic),
        'principle'=>['text'=>$principle,'why'=>$why,'steps'=>array_slice($steps,0,3)],
        'similar'=>$similar ?: $fallbackSimilar,
        'contrast'=>['selected'=>$selectedLabel,'correct'=>$correctLabel,'mistake'=>$mistake,'why'=>$why,'mental'=>$principle],
        'transfer'=>$transfer ?: v472_topic_transfer_prompt($classId,$topic,$module),
        'transfer_mode'=>$transfer?'choice':'open',
        'grade_impact'=>false,
    ];
}

function v472_corrective_cycle_complete(string $classId,string $studentKey,string $topic,string $outcome): array
{
    if($studentKey==='')throw new RuntimeException(tr('Studentský profil nebyl nalezen.'));
    if($topic===''||!isset(($GLOBALS['modules'][$classId]['knowledgebase']??[])[$topic]))throw new RuntimeException(tr('Téma nebylo nalezeno.'));
    if(!in_array($outcome,['correct','needs_review','self_checked'],true))$outcome='self_checked';
    $id='cc_'.bin2hex(random_bytes(7));
    $row=[
        'id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'outcome'=>$outcome,
        'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'completed_at'=>date(DATE_ATOM),
    ];
    storage_update(coach_store_path('corrective_cycles'),static function(array $rows) use($id,$row): array {$rows[$id]=$row;if(count($rows)>2500)$rows=array_slice($rows,-2200,null,true);return $rows;});
    return $row;
}
