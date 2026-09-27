<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v48 · Visual & Practical Learning Engine
 *
 * One formative lesson contract for all 112 structured lessons. v48 deliberately
 * reuses the lesson-specific cognitive model from v43 and the deterministic
 * parameter simulation from v45. It adds the learning sequence around them:
 * predict → compare → debug → manipulate → build → transfer.
 *
 * v48 evidence is formative only. It never writes grades, XP or mastery.
 */

function v48_is_graphics(string $classId): bool
{
    return in_array($classId,['class_1a','class_2a'],true);
}

function v48_rotate_options(array $options,int $correct,string $seed): array
{
    $options=array_values(array_map('strval',$options));
    $count=count($options);if($count<2)return ['options'=>$options,'correct'=>max(0,$correct)];
    $shift=abs(crc32($seed))%$count;
    $rot=array_merge(array_slice($options,$shift),array_slice($options,0,$shift));
    $newCorrect=((($correct-$shift)%$count)+$count)%$count;
    return ['options'=>$rot,'correct'=>$newCorrect];
}

function v48_compare_contract(array $lab,string $seed): array
{
    $layers=array_values((array)($lab['layers']??[]));
    $last=is_array(end($layers))?(array)end($layers):[];
    $lastLabel=trim((string)($last['label']??'poslední viditelnou vrstvu'));
    $correct=trim((string)($lab['compare']['why']??$lab['princip']??''));
    if($correct==='')$correct='Nejdřív je potřeba rozlišit příčinu, důsledek a důkaz; až potom volit zásah.';
    $set=v48_rotate_options([
        $correct,
        'Stačí upravit '.$lastLabel.'; ostatní vrstvy není potřeba ověřovat.',
        'Nejspolehlivější je změnit několik věcí současně a sledovat, zda problém zmizí.',
    ],0,$seed.'|compare');
    return [
        'bad'=>(string)($lab['compare']['bad']??'Varianta řeší spíš symptom než příčinu.'),
        'good'=>(string)($lab['compare']['good']??'Varianta pracuje s příčinou a ověřitelnou evidencí.'),
        'why'=>$correct,
        'question'=>'Který rozdíl nejlépe vysvětluje, proč je druhá varianta spolehlivější?',
        'options'=>$set['options'],'correct'=>$set['correct'],
    ];
}

function v48_debug_contract(array $lab,array $sim,string $seed): array
{
    $timeline=array_values(array_filter(array_map('strval',(array)($lab['timeline']??[])),static fn($v)=>trim($v)!==''));
    if(!$timeline)$timeline=['Pozoruj symptom','Odděl vrstvy problému','Ověř nejmenším testem','Změň jednu věc','Znovu ověř'];
    $correctStep=(string)$timeline[0];
    $distractors=[];
    foreach([2,count($timeline)-1,1] as $idx){if(isset($timeline[$idx])&&$timeline[$idx]!==$correctStep&&!in_array($timeline[$idx],$distractors,true))$distractors[]=$timeline[$idx];}
    while(count($distractors)<2)$distractors[]='Změnit několik věcí současně bez dalšího měření';
    $set=v48_rotate_options([$correctStep,$distractors[0],$distractors[1]],0,$seed.'|debug');
    $stress=(array)($sim['what_if'][0]['values']??$sim['baseline']??[]);
    $stressEval=v45_evaluate_state($sim,$stress);
    $weak='quality';$weakValue=101;
    foreach((array)$stressEval['metrics'] as $metric=>$value){
        $normalized=$metric==='risk'?100-(int)$value:(int)$value;
        if($normalized<$weakValue){$weakValue=$normalized;$weak=(string)$metric;}
    }
    return [
        'title'=>v48_is_graphics((string)($lab['class_id']??''))?'Najdi příčinu vadného návrhu':'Najdi příčinu incidentu',
        'symptom'=>(string)($lab['problem']??''),
        'question'=>'Jaký je nejrozumnější první diagnostický krok?',
        'options'=>$set['options'],'correct'=>$set['correct'],'why'=>'Začni krokem, který nejrychleji zpřesní model problému. Náhodné opravy schovávají příčinu a zhoršují přenos do další situace.',
        'fault_values'=>$stress,
        'weak_metric'=>$weak,
        'weak_metric_label'=>(string)($sim['metric_labels'][$weak]??$weak),
        'timeline'=>$timeline,
    ];
}

function v48_build_contract(array $lab): array
{
    $expected=array_values(array_map('strval',(array)($lab['model']['expected']??[])));
    if(!$expected){foreach((array)($lab['layers']??[]) as $layer)if(is_array($layer))$expected[]=(string)($layer['label']??'');}
    $expected=array_values(array_filter($expected,static fn($v)=>trim($v)!==''));
    $tokens=$expected;
    if(count($tokens)>1){$first=array_shift($tokens);$tokens[]=$first;}
    return ['expected'=>$expected,'tokens'=>$tokens,'prompt'=>v48_is_graphics((string)($lab['class_id']??''))?'Sestav pořadí, ve kterém máš návrh rozebrat od záměru po výsledek.':'Sestav diagnostický model od vstupní informace po ověřený závěr.'];
}

function v48_lab_spec(string $classId,array $lesson,array $module): array
{
    $lab=cv43_lab_spec($classId,$lesson,$module);
    $sim=v45_simulation_spec($classId,$lesson,$module);
    $lessonNo=(int)($lesson['number']??0);
    $seed=$classId.'|'.$lessonNo.'|'.(string)($lab['topic']??'');
    return [
        'id'=>$classId.'-L'.str_pad((string)$lessonNo,2,'0',STR_PAD_LEFT).'-v48',
        'class_id'=>$classId,'lesson_number'=>$lessonNo,'lesson_title'=>(string)($lesson['title']??('Lekce '.$lessonNo)),
        'topic'=>(string)($lab['topic']??''),'family'=>(string)($lab['family']??''),'renderer'=>(string)($sim['renderer']??'system'),
        'goal'=>(string)($lesson['goal']??''),'problem'=>(string)($lab['problem']??''),'princip'=>(string)($lab['compare']['why']??''),
        'prediction'=>['question'=>(string)($lab['question']??''),'options'=>array_values(array_map('strval',(array)($lab['options']??[]))),'correct'=>(int)($lab['correct']??0),'reveal'=>(string)($lab['compare']['why']??'')],
        'compare'=>v48_compare_contract($lab,$seed),'debug'=>v48_debug_contract($lab,$sim,$seed),'sandbox'=>$sim,'build'=>v48_build_contract($lab),
        'transfer'=>['prompt'=>(string)($lab['transfer']??'Použij princip v nové situaci.'),'criteria'=>[
            'Pojmenuj, co se v nové situaci změnilo.','Řekni, který princip zůstává stejný.','Navrhni jeden ověřitelný krok nebo důkaz.',
        ]],
        'teacher'=>[
            'phases'=>[
                'predict'=>['label'=>'Predikce','prompt'=>'Nech každého nejdřív zvolit odpověď bez reveal.'],
                'discuss'=>['label'=>'Diskuse','prompt'=>'Neukazuj řešení. Sbírej dvě odlišné hypotézy a jejich důvody.'],
                'compare'=>['label'=>'Kontrast','prompt'=>'Zobraz dvě varianty a ptej se na rozhodující rozdíl.'],
                'debug'=>['label'=>'Debug','prompt'=>'Třída navrhne první diagnostický krok před jakýmkoli zásahem.'],
                'sandbox'=>['label'=>'Sandbox','prompt'=>'Měň jen jednu proměnnou a nech třídu předpovědět směr změny.'],
                'build'=>['label'=>'Build','prompt'=>'Nech třídu sestavit model dřív, než odhalíš správné pořadí.'],
                'transfer'=>['label'=>'Transfer','prompt'=>'Změň kontext a ověř, zda princip přežije bez kopírování postupu.'],
            ],
        ],
        'assessment'=>['grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'mode'=>'formative'],
    ];
}

function v48_all_lab_specs(): array
{
    static $cache=null;if(is_array($cache))return $cache;
    global $modules;$out=[];
    foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
        foreach(v42_lessons_for_class($classId) as $lesson){$s=v48_lab_spec($classId,$lesson,$modules[$classId]);$out[$s['id']]=$s;}
    }
    return $cache=$out;
}

function v48_event_rows(): array { return adaptive_store('v48_events'); }
function v48_orchestration_rows(): array { return adaptive_store('v48_orchestration'); }
function v48_orchestration_key(string $classId,int $lessonNo): string { return $classId.'|L'.$lessonNo; }

function v48_clean_meta(array $meta): array
{
    $out=[];
    foreach($meta as $k=>$v){
        $key=preg_replace('/[^a-z0-9_\-]/i','',(string)$k);if($key==='')continue;
        if(is_bool($v)||is_int($v)||is_float($v)||$v===null)$out[$key]=$v;
        elseif(is_string($v))$out[$key]=u_substr(trim(strip_tags($v)),0,1200);
        elseif(is_array($v))$out[$key]=array_slice(array_values(array_map(static fn($x)=>is_scalar($x)?u_substr(trim(strip_tags((string)$x)),0,300):'', $v)),0,20);
    }
    return $out;
}

function v48_record_event(string $classId,string $studentKey,array $lesson,string $kind,bool $correct,array $meta=[]): array
{
    $allowed=['prediction','compare','debug','build','transfer'];if(!in_array($kind,$allowed,true))throw new RuntimeException('Neplatný typ v48 evidence.');
    $id='v48_'.bin2hex(random_bytes(7));$session=v48_orchestration_state($classId,(int)$lesson['number']);
    $row=['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$lesson['number'],'kind'=>$kind,'correct'=>$correct,'meta'=>v48_clean_meta($meta),'session_id'=>(string)($session['session_id']??''),'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'created_at'=>date(DATE_ATOM)];
    adaptive_store_update('v48_events',static function(array $rows) use($id,$row): array {$rows[$id]=$row;if(count($rows)>8000)$rows=array_slice($rows,-6500,null,true);return $rows;});return $row;
}

function v48_prediction_submit(string $classId,string $studentKey,array $lesson,int $answer): array
{
    $spec=v48_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$correct=$answer===(int)$spec['prediction']['correct'];
    v48_record_event($classId,$studentKey,$lesson,'prediction',$correct,['answer'=>$answer]);
    return ['correct'=>$correct,'correct_index'=>(int)$spec['prediction']['correct'],'reveal'=>(string)$spec['prediction']['reveal']];
}

function v48_compare_submit(string $classId,string $studentKey,array $lesson,int $answer): array
{
    $spec=v48_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$correct=$answer===(int)$spec['compare']['correct'];
    v48_record_event($classId,$studentKey,$lesson,'compare',$correct,['answer'=>$answer]);
    return ['correct'=>$correct,'correct_index'=>(int)$spec['compare']['correct'],'why'=>(string)$spec['compare']['why']];
}

function v48_debug_submit(string $classId,string $studentKey,array $lesson,int $answer): array
{
    $spec=v48_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$correct=$answer===(int)$spec['debug']['correct'];
    v48_record_event($classId,$studentKey,$lesson,'debug',$correct,['answer'=>$answer,'weak_metric'=>(string)$spec['debug']['weak_metric']]);
    return ['correct'=>$correct,'correct_index'=>(int)$spec['debug']['correct'],'why'=>(string)$spec['debug']['why']];
}

function v48_build_submit(string $classId,string $studentKey,array $lesson,array $sequence): array
{
    $spec=v48_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$sequence=array_values(array_map('strval',$sequence));$expected=array_values(array_map('strval',(array)$spec['build']['expected']));$correct=$sequence===$expected;
    v48_record_event($classId,$studentKey,$lesson,'build',$correct,['sequence'=>$sequence]);
    return ['correct'=>$correct,'expected'=>$expected];
}

function v48_transfer_submit(string $classId,string $studentKey,array $lesson,string $reflection): array
{
    $reflection=u_substr(trim(strip_tags($reflection)),0,1400);$ok=u_strlen($reflection)>=24;
    v48_record_event($classId,$studentKey,$lesson,'transfer',$ok,['reflection'=>$reflection]);
    return ['correct'=>$ok,'why'=>$ok?'Transfer je zachycený. Porovnej ho s kritérii a pokračuj.':'Zkus přidat, co se změnilo, co zůstává stejné a jak bys to ověřil/a.'];
}

function v48_student_state(string $classId,string $studentKey,int $lessonNo): array
{
    $state=['prediction'=>null,'compare'=>null,'debug'=>null,'build'=>null,'transfer'=>null,'attempts'=>0];
    foreach(v48_event_rows() as $row){if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey||(int)($row['lesson_number']??0)!==$lessonNo)continue;$kind=(string)($row['kind']??'');if(array_key_exists($kind,$state))$state[$kind]=$row;$state['attempts']++;}
    return $state;
}

function v48_orchestration_state(string $classId,int $lessonNo): array
{
    $key=v48_orchestration_key($classId,$lessonNo);$row=v48_orchestration_rows()[$key]??null;
    return is_array($row)?$row:['class_id'=>$classId,'lesson_number'=>$lessonNo,'active'=>false,'phase'=>'predict','session_id'=>'','prompt'=>'','updated_at'=>''];
}

function v48_orchestration_update(string $classId,array $lesson,string $mode,string $phase='predict'): array
{
    $spec=v48_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$valid=array_keys((array)$spec['teacher']['phases']);if(!in_array($phase,$valid,true))$phase='predict';
    $key=v48_orchestration_key($classId,(int)$lesson['number']);$teacherName=function_exists('teacher_display_name')?teacher_display_name():'';
    return (array)storage_map_update(adaptive_store_path('v48_orchestration'),$key,static function(?array $current) use($mode,$classId,$lesson,$phase,$spec,$teacherName): array {$current=$current??[];
    if($mode==='stop'){$current['active']=false;$current['updated_at']=date(DATE_ATOM);return $current;}
    $start=$mode==='start'||empty($current['session_id'])||empty($current['active']);
    $current=['class_id'=>$classId,'lesson_number'=>(int)$lesson['number'],'active'=>true,'phase'=>$phase,'session_id'=>$start?'v48s_'.bin2hex(random_bytes(6)):(string)$current['session_id'],'prompt'=>(string)($spec['teacher']['phases'][$phase]['prompt']??''),'started_by'=>$start&&$teacherName!==''?$teacherName:(string)($current['started_by']??''),'started_at'=>$start?date(DATE_ATOM):(string)($current['started_at']??date(DATE_ATOM)),'updated_at'=>date(DATE_ATOM)];
    return $current;});
}

function v48_teacher_summary(string $classId,int $lessonNo): array
{
    $state=v48_orchestration_state($classId,$lessonNo);$session=(string)($state['session_id']??'');$students=project_students_for_class($classId);$valid=array_fill_keys(array_keys($students),true);
    $by=['prediction'=>[],'compare'=>[],'debug'=>[],'build'=>[],'transfer'=>[]];$correct=['prediction'=>0,'compare'=>0,'debug'=>0,'build'=>0,'transfer'=>0];
    foreach(v48_event_rows() as $row){if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(int)($row['lesson_number']??0)!==$lessonNo)continue;if($session!==''&&(string)($row['session_id']??'')!==$session)continue;$sk=(string)($row['student_key']??'');if($valid&&!isset($valid[$sk]))continue;$kind=(string)($row['kind']??'');if(!isset($by[$kind]))continue;$by[$kind][$sk]=true;if(!empty($row['correct']))$correct[$kind]++;}
    $counts=[];foreach($by as $k=>$rows)$counts[$k]=count($rows);
    return ['students'=>count($students),'counts'=>$counts,'correct'=>$correct,'state'=>$state];
}
