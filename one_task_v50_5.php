<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v50.5 · One Task Mode
 * Minimal full-screen orchestration layer over existing curriculum sources of truth.
 */

function v505_store_path(string $name): string
{
    $safe=preg_replace('/[^a-z0-9_\-]/i','',$name) ?: 'events';
    return STORAGE_DIR.'/adaptive_v505_'.$safe.'.json.php';
}
/** Čtení kolekce v50.5; události jsou pouze přidávané → měsíční JSONL proud (v58). */
function v505_rows(string $name): array
{
    return storage_rows(v505_store_path($name));
}
function v505_clean(string $value,int $max=4000): string
{
    $value=trim(strip_tags($value));
    return u_substr($value,0,$max);
}
function v505_student_key(string $classId): string
{
    return adaptive_student_key($classId);
}
function v505_safe_return_url(string $raw,string $fallback='?view=dashboard'): string
{
    $raw=trim($raw);
    if($raw===''||!str_starts_with($raw,'?'))return $fallback;
    if(str_contains($raw,"\n")||str_contains($raw,"\r")||str_contains($raw,'//'))return $fallback;
    parse_str(ltrim($raw,'?'),$parts);
    $view=is_string($parts['view']??null)?$parts['view']:'';
    $allowed=['dashboard','course','course_lesson','next_lesson','skills','skill_branch','skill_detail','goal_nav','growth','growth_path','hands_on','visual_lab','project_results','project_result','project_workspace','study','study_loop','knowledgebase','kb_lesson'];
    if(!in_array($view,$allowed,true))return $fallback;
    $safe=['view'=>$view];
    foreach(['lesson','skill','branch','path','record','project','section','topic','kb_class','reference'] as $key){
        if(!isset($parts[$key])||!is_scalar($parts[$key]))continue;
        $value=u_substr(trim((string)$parts[$key]),0,160);
        if($value!=='')$safe[$key]=$value;
    }
    return '?'.http_build_query($safe);
}
function v505_task_url(string $task,array $params=[],string $returnUrl='?view=dashboard'): string
{
    $payload=['view'=>'one_task','task'=>$task];
    foreach($params as $k=>$v)if(is_scalar($v)&&trim((string)$v)!=='')$payload[(string)$k]=(string)$v;
    $payload['return']=v505_safe_return_url($returnUrl);
    return '?'.http_build_query($payload);
}
function v505_safe_task_url(string $raw): string
{
    $raw=trim($raw);if($raw===''||!str_starts_with($raw,'?')||str_contains($raw,"\n")||str_contains($raw,"\r")||str_contains($raw,'//'))return '';
    parse_str(ltrim($raw,'?'),$p);if((string)($p['view']??'')!=='one_task')return '';
    $task=(string)($p['task']??'');if(!in_array($task,['course','kb','skill','growth','lab','result'],true))return '';
    $params=[];foreach(['lesson','skill','path','record','topic'] as $k)if(isset($p[$k])&&is_scalar($p[$k])){$v=u_substr(trim((string)$p[$k]),0,160);if($v!=='')$params[$k]=$v;}
    return v505_task_url($task,$params,v505_safe_return_url((string)($p['return']??'?view=dashboard')));
}
function v505_current_url(array $task): string
{
    return v505_task_url((string)$task['task_type'],(array)($task['route_params']??[]),(string)($task['return_url']??'?view=dashboard'));
}
function v505_local_href_to_task(string $href,string $returnUrl='?view=dashboard'): string
{
    if($href===''||!str_starts_with($href,'?'))return $href;
    parse_str(ltrim($href,'?'),$p);$view=(string)($p['view']??'');
    return match($view){
        'course_lesson'=>v505_task_url('course',['lesson'=>(string)($p['lesson']??'')],$returnUrl),
        'next_lesson'=>v505_task_url('course',['lesson'=>'next'],$returnUrl),
        'skill_detail'=>v505_task_url('skill',['skill'=>(string)($p['skill']??'')],$returnUrl),
        'growth_path'=>v505_task_url('growth',['path'=>(string)($p['path']??'')],$returnUrl),
        'hands_on'=>v505_task_url('lab',['lesson'=>(string)($p['lesson']??'1')],$returnUrl),
        'kb_lesson'=>v505_task_url('kb',['topic'=>(string)($p['topic']??'')],$returnUrl),
        'project_result'=>v505_task_url('result',['record'=>(string)($p['record']??'')],$returnUrl),
        default=>$href,
    };
}
function v505_event_record(string $classId,string $studentKey,string $taskId,string $kind,array $meta=[]): void
{
    if($studentKey===''||$taskId==='')return;
    $allowed=['started','verified','passed','retry','help','returned','result_reviewed','paused'];
    if(!in_array($kind,$allowed,true))return;
    $id='ot_'.bin2hex(random_bytes(7));
    $cleanMeta=[];foreach($meta as $k=>$v){if(is_bool($v)||is_int($v)||is_float($v)||$v===null)$cleanMeta[(string)$k]=$v;elseif(is_scalar($v))$cleanMeta[(string)$k]=v505_clean((string)$v,600);}
    // v58 (DAT-03): událost jen připojí řádek (dřív přepis celého souboru i při pouhém zobrazení úkolu).
    storage_append('adaptive_v505_events',['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'task_id'=>$taskId,'kind'=>$kind,'meta'=>$cleanMeta,'created_at'=>date(DATE_ATOM)]);
}
function v505_task_events(string $classId,string $studentKey,string $taskId): array
{
    $out=[];foreach(v505_rows('events') as $row){if(!is_array($row))continue;if((string)($row['class_id']??'')===$classId&&(string)($row['student_key']??'')===$studentKey&&(string)($row['task_id']??'')===$taskId)$out[]=$row;}return $out;
}
function v505_result_reviewed(string $classId,string $studentKey,string $recordId): bool
{
    foreach(v505_task_events($classId,$studentKey,'result:'.$recordId) as $row)if((string)($row['kind']??'')==='result_reviewed')return true;
    return false;
}
function v505_draft_key(string $classId,string $studentKey,string $taskId): string { return $classId.'|'.$studentKey.'|'.$taskId; }
function v505_draft_get(string $classId,string $studentKey,string $taskId): array
{
    $rows=v505_rows('drafts');$row=$rows[v505_draft_key($classId,$studentKey,$taskId)]??null;return is_array($row)?(array)($row['payload']??[]):[];
}
function v505_draft_save(string $classId,string $studentKey,string $taskId,array $payload,string $taskUrl='',string $taskTitle=''): void
{
    if($studentKey===''||$taskId==='')return;$safe=[];
    foreach(array_slice($payload,0,30,true) as $k=>$v){if(!is_scalar($v))continue;$safe[u_substr((string)$k,0,80)]=v505_clean((string)$v,12000);}
    $key=v505_draft_key($classId,$studentKey,$taskId);$draft=['class_id'=>$classId,'student_key'=>$studentKey,'task_id'=>$taskId,'task_url'=>v505_safe_task_url($taskUrl),'task_title'=>v505_clean($taskTitle,180),'payload'=>$safe,'updated_at'=>date(DATE_ATOM)];
    storage_update(v505_store_path('drafts'),static function(array $rows) use($key,$draft): array {unset($rows[$key]);$rows[$key]=$draft;if(count($rows)>6000)$rows=array_slice($rows,-4000,null,true);return $rows;});
}
function v505_latest_draft(string $classId,string $studentKey): ?array
{
    $best=null;$bestTs=0;foreach(v505_rows('drafts') as $row){if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;$url=v505_safe_task_url((string)($row['task_url']??''));if($url==='')continue;$ts=strtotime((string)($row['updated_at']??''))?:0;if($ts>$bestTs){$row['task_url']=$url;$best=$row;$bestTs=$ts;}}return $best;
}
function v505_draft_clear(string $classId,string $studentKey,string $taskId): void
{
    $key=v505_draft_key($classId,$studentKey,$taskId);if(isset(v505_rows('drafts')[$key]))storage_map_update(v505_store_path('drafts'),$key,static fn(?array $current): ?array => null);
}

function v505_find_lesson(string $classId,string $ref,array $nextLessons,array $extendedLessons): ?array
{
    if($ref==='next')return is_array($nextLessons[$classId]??null)?$nextLessons[$classId]:null;
    foreach((array)($extendedLessons[$classId]??[]) as $lesson)if(is_array($lesson)&&(string)($lesson['id']??'')===$ref)return $lesson;
    if(ctype_digit($ref)&&function_exists('v42_find_lesson'))return v42_find_lesson($classId,(int)$ref) ?: null;
    return null;
}
function v505_course_task(string $classId,array $module,string $lessonRef,array $nextLessons,array $extendedLessons,string $returnUrl): array
{
    $lesson=v505_find_lesson($classId,$lessonRef,$nextLessons,$extendedLessons);
    if(!$lesson)return v505_bridge_task('course-missing',tr('Lekce nebyla nalezena'),tr('Vrať se do kurzu a vyber dostupnou lekci.'),'?view=course',$returnUrl);
    $isNext=(string)($lesson['id']??'')===(string)($nextLessons[$classId]['id']??'__none__');
    $lessonNo=(int)($lesson['number']??($isNext?2:0));
    if($isNext&&!learning_primary_block_complete($classId))return v505_bridge_task('course-locked',tr('Nejdřív dokonči předchozí krok'),tr('Lekce 2 se odemkne až po dokončení základního bloku.'),'?view=dashboard',$returnUrl);
    if(!$isNext&&$lessonNo>=3&&!learning_course_lesson_unlocked($classId,$lessonNo,$nextLessons,$extendedLessons))return v505_bridge_task('course-locked',tr('Nejdřív dokonči předchozí lekci'),tr('Tento krok ještě není odemčený. Nejdřív dokonči předchozí část.'),'?view=course',$returnUrl);
    $steps=array_values(array_filter((array)($lesson['steps']??[]),'is_array'));
    $progress=$isNext?learning_next_lesson_progress($classId,(string)$lesson['id'],$steps):learning_course_lesson_progress($classId,$lesson);
    $current=null;$idx=0;foreach($steps as $i=>$s){$sid=(string)($s['id']??'');if($sid!==''&&empty($progress[$sid])){$current=$s;$idx=$i;break;}}
    $lessonId=(string)($lesson['id']??$lessonRef);$taskId='course:'.$lessonId.':'.(string)($current['id']??'complete');
    if(!$current){
        return ['task_type'=>'course','route_params'=>['lesson'=>$lessonRef],'id'=>$taskId,'title'=>(string)($lesson['title']??tr('Lekce')),'instruction'=>tr('Lekce je dokončená. Pokračuj na nejbližší další odemčený krok.'),'estimate'=>tr('hotovo'),'state'=>'PASSED','workspace_type'=>'complete','workspace'=>[],'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Kurz'),'next_url'=>'?view=course','next_label'=>tr('Najít další krok'),'goal_refs'=>[],'skill_refs'=>[]];
    }
    $support=[];foreach((array)($current['knowledge']??[]) as $topic){$a=$module['knowledgebase'][(string)$topic]??null;if(is_array($a))$support[]=['label'=>(string)($a['title']??$topic),'href'=>v505_task_url('kb',['topic'=>(string)$topic],v505_task_url('course',['lesson'=>$lessonRef],$returnUrl))];}
    $task=['task_type'=>'course','route_params'=>['lesson'=>$lessonRef],'id'=>$taskId,'title'=>(string)($current['title']??$lesson['title']??tr('Aktuální krok')),'instruction'=>(string)($current['intro']??$current['question']??tr('Dokonči tento jediný krok.')),'estimate'=>(string)($current['time']??tr('5–10 min')),'state'=>'IN_PROGRESS','workspace_type'=>'course_step','workspace'=>['lesson'=>$lesson,'lesson_ref'=>$lessonRef,'lesson_id'=>$lessonId,'lesson_number'=>(int)($lesson['number']??0),'step'=>$current,'step_index'=>$idx,'step_total'=>count($steps),'step_action'=>$isNext?'next_lesson_step':'course_lesson_step'],'help'=>$support,'return_url'=>$returnUrl,'source'=>tr('Kurz'),'next_url'=>v505_task_url('course',['lesson'=>$lessonRef],$returnUrl),'next_label'=>tr('Další krok'),'goal_refs'=>[],'skill_refs'=>[]];
    return $task;
}
function v505_kb_task(string $classId,array $module,string $topic,array $knowledgeTours,array $simulations,string $returnUrl): array
{
    $article=$module['knowledgebase'][$topic]??null;if(!is_array($article))return v505_bridge_task('kb-missing',tr('Vysvětlení nebylo nalezeno'),tr('Vrať se do knihovny znalostí.'),'?view=knowledgebase',$returnUrl);
    $tour=is_array($knowledgeTours[$classId][$topic]??null)?$knowledgeTours[$classId][$topic]:[];$sim=is_array($simulations[$classId][$topic]??null)?$simulations[$classId][$topic]:null;$p=learning_kb_progress($classId,$topic);
    $phases=['visual'];if($sim)$phases[]='simulation';$phases[]='steps';$phases[]='deep';$phases[]='check';$phase='complete';foreach($phases as $candidate){if(empty($p[$candidate])){$phase=$candidate;break;}}
    $taskId='kb:'.$topic.':'.$phase;$check=is_array($tour['check']??null)?$tour['check']:null;
    $labels=['visual'=>tr('Pochop model'),'simulation'=>tr('Zkus princip'),'steps'=>tr('Projdi postup'),'deep'=>tr('Vysvětli si princip'),'check'=>tr('Ověř pochopení'),'complete'=>tr('Hotovo')];
    return ['task_type'=>'kb','route_params'=>['topic'=>$topic],'id'=>$taskId,'title'=>(string)($article['title']??$topic),'instruction'=>$phase==='complete'?tr('Toto vysvětlení máš dokončené.'):(string)($labels[$phase]??tr('Pokračuj krokem')),'estimate'=>(string)($tour['time']??tr('5–10 min')),'state'=>$phase==='complete'?'PASSED':'IN_PROGRESS','workspace_type'=>'kb','workspace'=>['topic'=>$topic,'phase'=>$phase,'article'=>$article,'tour'=>$tour,'simulation'=>$sim,'check'=>$check],'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Vysvětlení'),'next_url'=>$phase==='complete'?$returnUrl:v505_task_url('kb',['topic'=>$topic],$returnUrl),'next_label'=>$phase==='complete'?tr('Vrátit se k úkolu'):tr('Další krok'),'goal_refs'=>[],'skill_refs'=>[]];
}
function v505_skill_task(string $classId,array $module,string $slug,string $returnUrl): array
{
    $skill=skill_find($slug);if(!$skill)return v505_bridge_task('skill-missing',tr('Dovednost nebyla nalezena'),tr('Vrať se na přehled dovedností.'),'?view=skills',$returnUrl);
    skill_sync_existing_learning($classId);$p=skill_progress($classId,$slug);$knowledge=skill_knowledge_topic($classId,$slug);$challenge=skill_challenge_for_skill($slug);$mode='complete';$workspace=[];$state='IN_PROGRESS';$instruction='';
    if((string)($p['unlock_state']??'locked')==='locked'){$mode='locked';$state='BLOCKED';$instruction=tr('Nejdřív odemkni prerequisite. Systém ti ukáže nejbližší požadavek.');$workspace['missing']=(array)($p['missing_requirements']??[]);}
    elseif(empty($p['evidence_by_type']['lesson'])){if($knowledge){$mode='knowledge';$instruction=tr('Nejdřív upevni základní princip v krátkém vysvětlení.');$workspace['topic']=$knowledge;}else{$mode='mark_learning';$instruction=tr('Dokonči studijní krok této dovednosti.');}}
    elseif(empty($p['evidence_by_type']['practice'])||empty($p['evidence_by_type']['quiz'])){$mode='quick_check';$instruction=tr('Ověř si princip několika krátkými otázkami.');$workspace['questions']=skill_quick_questions($classId,$skill,'practice');$workspace['check_mode']='practice';}
    elseif(empty($p['evidence_by_type']['lab'])&&empty($p['evidence_by_type']['project'])){$mode='validation';$instruction=tr('Popiš jeden konkrétní praktický důkaz, který může učitel ověřit.');}
    elseif((float)($p['mastery_percent']??0)>=70 && empty($p['evidence_by_type']['challenge'])){if($challenge){$mode='challenge';$instruction=tr('Dokonči mastery challenge a obhaj rozhodnutí podle důkazů.');$workspace['challenge']=$challenge;}else{$mode='quick_check';$instruction=tr('Ověř mastery krátkým checkem.');$workspace['questions']=skill_quick_questions($classId,$skill,'mastery');$workspace['check_mode']='mastery';}}
    else{$mode='complete';$state='PASSED';$instruction=tr('Evidence této dovednosti je pro tuto chvíli kompletní.');}
    $id='skill:'.$slug.':'.$mode;$next='?view=skills';
    return ['task_type'=>'skill','route_params'=>['skill'=>$slug],'id'=>$id,'title'=>(string)$skill['name'],'instruction'=>$instruction,'estimate'=>tr('5–15 min'),'state'=>$state,'workspace_type'=>'skill','workspace'=>array_merge($workspace,['mode'=>$mode,'skill'=>$skill,'progress'=>$p]),'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Dovednosti'),'next_url'=>$next,'next_label'=>tr('Další dovednost'),'goal_refs'=>[],'skill_refs'=>[$slug]];
}
function v505_growth_task(string $classId,array $module,string $pathId,string $returnUrl): array
{
    $studentKey=v505_student_key($classId);$state=v50_growth_state($classId,$studentKey,$pathId);$path=$state['path']??null;if(!is_array($path))return v505_bridge_task('growth-missing',tr('Rozvojová cesta nebyla nalezena'),tr('Vrať se na přehled rozvoje.'),'?view=growth',$returnUrl);
    if(empty($state['unlocked']))return ['task_type'=>'growth','route_params'=>['path'=>$pathId],'id'=>'growth:'.$pathId.':locked','title'=>(string)$path['title'],'instruction'=>tr('Tato dobrovolná cesta ještě není odemčená.'),'estimate'=>'—','state'=>'BLOCKED','workspace_type'=>'growth','workspace'=>['mode'=>'locked','path'=>$path,'reasons'=>(array)($state['reasons']??[])],'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Rozvoj'),'next_url'=>'?view=skills','next_label'=>tr('Posílit základy'),'goal_refs'=>[$pathId],'skill_refs'=>[]];
    $completed=(array)($state['completed']??[]);$current=null;foreach((array)$path['modules'] as $m)if(is_array($m)&&empty($completed[(string)($m['slug']??'')])){$current=$m;break;}
    if(!$current)return ['task_type'=>'growth','route_params'=>['path'=>$pathId],'id'=>'growth:'.$pathId.':complete','title'=>(string)$path['title'],'instruction'=>tr('Všechny moduly této cesty máš dokončené.'),'estimate'=>tr('hotovo'),'state'=>'PASSED','workspace_type'=>'complete','workspace'=>[],'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Rozvoj'),'next_url'=>'?view=skill_passport','next_label'=>tr('Otevřít důkazy'),'goal_refs'=>[$pathId],'skill_refs'=>[]];
    return ['task_type'=>'growth','route_params'=>['path'=>$pathId],'id'=>'growth:'.$pathId.':'.(string)$current['slug'],'title'=>(string)$current['title'],'instruction'=>(string)$current['goal'],'estimate'=>tr('{minutes} min',['minutes'=>(int)($current['minutes']??45)]),'state'=>'IN_PROGRESS','workspace_type'=>'growth','workspace'=>['mode'=>'module','path'=>$path,'module'=>$current],'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Rozvoj'),'next_url'=>v505_task_url('growth',['path'=>$pathId],$returnUrl),'next_label'=>tr('Další modul'),'goal_refs'=>[$pathId],'skill_refs'=>[]];
}
function v505_lab_task(string $classId,array $module,int $lessonNo,string $returnUrl): array
{
    if(!in_array($classId,['class_3a','class_4a'],true))return v505_bridge_task('lab-unavailable',tr('Hands-on Lab je dostupný pro SOSaPS'),tr('Vrať se na dashboard.'),'?view=dashboard',$returnUrl);
    $lesson=v42_find_lesson($classId,max(1,min(28,$lessonNo)));if(!$lesson)return v505_bridge_task('lab-missing',tr('Lab nebyl nalezen'),tr('Vrať se do kurzu.'),'?view=course',$returnUrl);
    $studentKey=v505_student_key($classId);$spec=v50_lab_spec($classId,$lesson,$studentKey);$events=v50_student_events($classId,$studentKey,$lessonNo);
    $hasHyp=false;$hasCommand=false;$validated=false;$explained=false;$transferred=false;foreach($events as $e){if(!is_array($e))continue;$kind=(string)($e['kind']??'');if($kind==='hypothesis')$hasHyp=true;if($kind==='command')$hasCommand=true;if($kind==='validation'&&!empty($e['correct']))$validated=true;if($kind==='explain')$explained=true;if($kind==='transfer')$transferred=true;}
    $state=v50_latest_state($classId,$studentKey,$spec);$phase=!$hasHyp?'hypothesis':(!$validated?'diagnose':(!$explained?'explain':(!$transferred?'transfer':'complete')));
    $id='lab:'.$classId.':'.$lessonNo.':'.$phase;$instruction=match($phase){'hypothesis'=>(string)$spec['hypothesis_prompt'],'diagnose'=>tr('Získej minimální důkaz, proveď bezpečnou opravu a ověř výsledný stav.'),'explain'=>tr('Vysvětli root cause vlastními slovy a pojmenuj důkaz.'),'transfer'=>(string)$spec['transfer'],default=>tr('Checkpoint je dokončený.')};
    return ['task_type'=>'lab','route_params'=>['lesson'=>(string)$lessonNo],'id'=>$id,'title'=>(string)$spec['lesson_title'],'instruction'=>$instruction,'estimate'=>tr('10–25 min'),'state'=>$phase==='complete'?'PASSED':'IN_PROGRESS','workspace_type'=>'lab','workspace'=>['phase'=>$phase,'spec'=>$spec,'sim_state'=>$state,'has_command'=>$hasCommand,'repair_ready'=>(string)($state['last_command']??'')===(string)$spec['repair_command']],'help'=>(array)$spec['hints'],'return_url'=>$returnUrl,'source'=>tr('Praktický lab'),'next_url'=>$phase==='complete'?'?view=course':v505_task_url('lab',['lesson'=>(string)$lessonNo],$returnUrl),'next_label'=>$phase==='complete'?tr('Pokračovat v kurzu'):tr('Další krok labu'),'goal_refs'=>[],'skill_refs'=>[]];
}
function v505_result_task(string $classId,array $module,string $recordId,string $returnUrl): array
{
    $studentName=(string)($_SESSION['student_label']??(auth_user()['name']??tr('Student')));$results=project_student_results($classId,$studentName);$item=null;foreach($results as $candidate)if(is_array($candidate)&&(string)($candidate['record']['id']??'')===$recordId){$item=$candidate;break;}
    if(!$item)return v505_bridge_task('result-missing',tr('Výsledek nebyl nalezen'),tr('Vrať se na výsledky.'),'?view=project_results',$returnUrl);
    $studentKey=v505_student_key($classId);$reviewed=v505_result_reviewed($classId,$studentKey,$recordId);$record=(array)$item['record'];$project=(array)$item['project'];$returned=(string)($record['status']??'')==='returned';$next='?view=project_results';if($returned&&!empty($project['id'])&&function_exists('project_workspace_url'))$next=project_workspace_url((string)$project['id'],'work');
    return ['task_type'=>'result','route_params'=>['record'=>$recordId],'id'=>'result:'.$recordId,'title'=>tr('Feedback · {project}',['project'=>(string)($project['title']??tr('Projekt'))]),'instruction'=>$returned?tr('Přečti si jeden konkrétní další krok a potom se vrať k úpravě projektu.'):tr('Přečti si feedback a pojmenuj, co si z něj bereš do další práce.'),'estimate'=>tr('2–5 min'),'state'=>$reviewed?'PASSED':'IN_PROGRESS','workspace_type'=>'result','workspace'=>['item'=>$item,'reviewed'=>$reviewed,'returned'=>$returned],'help'=>[],'return_url'=>$returnUrl,'source'=>tr('Výsledky'),'next_url'=>$next,'next_label'=>$returned?tr('Upravit projekt'):tr('Zpět na výsledky'),'goal_refs'=>[],'skill_refs'=>[]];
}
function v505_bridge_task(string $id,string $title,string $instruction,string $target,string $returnUrl): array
{
    return ['task_type'=>'bridge','route_params'=>['target'=>$target],'id'=>'bridge:'.$id,'title'=>$title,'instruction'=>$instruction,'estimate'=>'—','state'=>'BLOCKED','workspace_type'=>'bridge','workspace'=>['target'=>$target],'help'=>[],'return_url'=>$returnUrl,'source'=>'EDUCANET','next_url'=>$target,'next_label'=>tr('Pokračovat'),'goal_refs'=>[],'skill_refs'=>[]];
}
function v505_task_from_request(string $classId,array $module,array $nextLessons,array $extendedLessons,array $knowledgeTours,array $simulations): array
{
    $task=(string)($_GET['task']??'course');$return=v505_safe_return_url((string)($_GET['return']??'?view=dashboard'));
    return match($task){
        'course'=>v505_course_task($classId,$module,(string)($_GET['lesson']??'next'),$nextLessons,$extendedLessons,$return),
        'kb'=>v505_kb_task($classId,$module,(string)($_GET['topic']??''),$knowledgeTours,$simulations,$return),
        'skill'=>v505_skill_task($classId,$module,(string)($_GET['skill']??''),$return),
        'growth'=>v505_growth_task($classId,$module,(string)($_GET['path']??''),$return),
        'lab'=>v505_lab_task($classId,$module,max(1,min(28,(int)($_GET['lesson']??1))),$return),
        'result'=>v505_result_task($classId,$module,(string)($_GET['record']??''),$return),
        default=>v505_bridge_task('unknown',tr('Úkol nebyl nalezen'),tr('Vrať se na dashboard a pokračuj z doporučeného kroku.'),'?view=dashboard',$return),
    };
}
function v505_primary_action(array $task): array
{
    $state=(string)($task['state']??'IN_PROGRESS');$type=(string)($task['workspace_type']??'bridge');$w=(array)($task['workspace']??[]);
    if($state==='PASSED')return ['code'=>'NEXT','label'=>(string)($task['next_label']??tr('Pokračovat')),'mode'=>'link','href'=>(string)($task['next_url']??'?view=dashboard')];
    if($state==='BLOCKED')return ['code'=>'CONTINUE','label'=>(string)($task['next_label']??tr('Pokračovat')),'mode'=>'link','href'=>(string)($task['next_url']??'?view=dashboard')];
    if($type==='course_step'||$type==='kb')return ['code'=>'CHECK','label'=>tr('Ověřit →'),'mode'=>'js','href'=>''];
    if($type==='skill'){
        $m=(string)($w['mode']??'complete');if($m==='knowledge')return ['code'=>'CONTINUE','label'=>tr('Začít vysvětlení →'),'mode'=>'link','href'=>v505_task_url('kb',['topic'=>(string)$w['topic']],v505_current_url($task))];
        if($m==='complete')return ['code'=>'NEXT','label'=>tr('Další dovednost →'),'mode'=>'link','href'=>(string)$task['next_url']];
        if($m==='locked')return ['code'=>'CONTINUE','label'=>tr('Otevřít prerequisite →'),'mode'=>'link','href'=>'?view=skills'];
        return ['code'=>'CHECK','label'=>$m==='validation'?tr('Odeslat k ověření →'):tr('Ověřit →'),'mode'=>'form','href'=>''];
    }
    if($type==='growth')return ['code'=>'COMPLETE','label'=>tr('Dokončit modul →'),'mode'=>'form','href'=>''];
    if($type==='lab'){
        $phase=(string)($w['phase']??'hypothesis');$label=match($phase){'hypothesis'=>tr('Uložit hypotézu →'),'diagnose'=>(!empty($w['repair_ready'])?tr('Ověřit stav →'):tr('Spustit příkaz →')),'explain'=>tr('Uložit vysvětlení →'),'transfer'=>tr('Dokončit transfer →'),default=>tr('Pokračovat →')};return ['code'=>'CHECK','label'=>$label,'mode'=>'form','href'=>''];
    }
    if($type==='result')return ['code'=>'COMPLETE','label'=>tr('Rozumím feedbacku →'),'mode'=>'form','href'=>''];
    return ['code'=>'CONTINUE','label'=>(string)($task['next_label']??tr('Pokračovat →')),'mode'=>'link','href'=>(string)($task['next_url']??'?view=dashboard')];
}
function v505_stage_index(array $task): int
{
    return match((string)($task['state']??'')){'READY'=>0,'PASSED'=>3,'BLOCKED'=>0,default=>1};
}
function v505_post_task_url(array $post): string
{
    $type=v505_clean((string)($post['task_type']??'course'),30);$params=[];foreach(['lesson','skill','path','record','topic'] as $k)if(isset($post[$k])&&is_scalar($post[$k]))$params[$k]=v505_clean((string)$post[$k],160);
    return v505_task_url($type,$params,v505_safe_return_url((string)($post['return_url']??'?view=dashboard')));
}
function v505_handle_post(string $classId,array $module,string $action): bool
{
    if(!str_starts_with($action,'v505_'))return false;$studentKey=v505_student_key($classId);
    if($action==='v505_draft_save'){$taskId=v505_clean((string)($_POST['task_id']??''),220);$payload=json_decode((string)($_POST['payload']??'{}'),true);v505_draft_save($classId,$studentKey,$taskId,is_array($payload)?$payload:[],(string)($_POST['task_url']??''),(string)($_POST['task_title']??''));header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'saved_at'=>date(DATE_ATOM)],JSON_UNESCAPED_UNICODE);exit;}
    if($action==='v505_event'){$taskId=v505_clean((string)($_POST['task_id']??''),220);$kind=v505_clean((string)($_POST['kind']??'help'),30);v505_event_record($classId,$studentKey,$taskId,$kind);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true],JSON_UNESCAPED_UNICODE);exit;}
    $redirect=v505_post_task_url($_POST);$taskId=v505_clean((string)($_POST['task_id']??''),220);
    try{
        if($action==='v505_skill_mark'){skill_learning_mark($classId,(string)($_POST['skill']??''));}
        elseif($action==='v505_skill_check'){skill_submit_quick_check($classId,(string)($_POST['skill']??''),is_array($_POST['answers']??null)?$_POST['answers']:[],(string)($_POST['check_mode']??'practice'));}
        elseif($action==='v505_skill_validation'){skill_request_validation($classId,(string)($_POST['skill']??''),(string)($_POST['note']??''));}
        elseif($action==='v505_skill_challenge'){$r=skill_submit_branch_challenge($classId,(string)($_POST['challenge']??''),is_array($_POST['answers']??null)?$_POST['answers']:[],(int)($_POST['hints']??0));$_SESSION['flash']=tr('Mastery challenge byla vyhodnocena: {percent} %.',['percent'=>edu_number((float)($r['score']??0),0)]);}
        elseif($action==='v505_growth_complete'){v50_growth_complete_module($classId,$studentKey,(string)($_POST['path']??''),(string)($_POST['module_slug']??''),(string)($_POST['reflection']??''));}
        elseif(in_array($action,['v505_lab_hypothesis','v505_lab_command','v505_lab_validate','v505_lab_explain','v505_lab_transfer'],true)){
            $lessonNo=max(1,min(28,(int)($_POST['lesson']??1)));$lesson=v42_find_lesson($classId,$lessonNo);if(!$lesson)throw new RuntimeException(tr('Lab nebyl nalezen.'));$spec=v50_lab_spec($classId,$lesson,$studentKey);
            if($action==='v505_lab_hypothesis')v50_hypothesis_submit($classId,$studentKey,$spec,(string)($_POST['hypothesis']??''));
            elseif($action==='v505_lab_command'){$r=v50_command_submit($classId,$studentKey,$spec,(string)($_POST['command']??''));if(empty($r['ok']))throw new RuntimeException((string)($r['output']??tr('Příkaz nebyl povolen.')));}
            elseif($action==='v505_lab_validate'){$r=v50_validate_state_submit($classId,$studentKey,$spec);if(empty($r['correct'])){v505_event_record($classId,$studentKey,$taskId,'retry',['reason'=>'lab_validation']);$_SESSION['flash']=tr('Checkpoint ještě nesedí. Vrať se k evidenci a oprav skutečnou příčinu.');header('Location: '.$redirect,true,303);exit;}}
            elseif($action==='v505_lab_explain')v50_explain_submit($classId,$studentKey,$spec,(string)($_POST['explanation']??''));
            else v50_transfer_submit($classId,$studentKey,$spec,(string)($_POST['transfer']??''));
        }
        elseif($action==='v505_result_review'){v505_event_record($classId,$studentKey,'result:'.(string)($_POST['record']??''),'result_reviewed');}
        else return false;
        if($taskId!==''){v505_event_record($classId,$studentKey,$taskId,'passed',['action'=>$action]);v505_draft_clear($classId,$studentKey,$taskId);}$_SESSION['flash']=$_SESSION['flash']??tr('Krok je uložený. Pokračuj dál.');
    }catch(Throwable $e){$_SESSION['flash']=$e->getMessage();v505_event_record($classId,$studentKey,$taskId,'retry',['action'=>$action]);}
    header('Location: '.$redirect,true,303);exit;
}

function v505_render_hidden_context(array $task): void
{
    $p=(array)($task['route_params']??[]);?><input type="hidden" name="task_id" value="<?=e((string)$task['id'])?>"><input type="hidden" name="task_type" value="<?=e((string)$task['task_type'])?>"><input type="hidden" name="return_url" value="<?=e((string)$task['return_url'])?>"><?php foreach(['lesson','skill','path','record','topic'] as $k)if(isset($p[$k])):?><input type="hidden" name="<?=e($k)?>" value="<?=e((string)$p[$k])?>"><?php endif;
}
function v505_render_workspace(array $task,string $classId,array $module,array $draft): void
{
    $type=(string)$task['workspace_type'];$w=(array)$task['workspace'];
    if($type==='course_step'){$s=(array)$w['step'];$kind=(string)($s['kind']??'manual');?>
      <?php if(!empty($s['demo']['lines'])):?><div class="ot-evidence"><span><?=e((string)($s['demo']['label']??tr('Ukázka')))?></span><pre><?php foreach((array)$s['demo']['lines'] as $line)echo e((string)$line)."\n";?></pre></div><?php endif;?>
      <?php if(!empty($s['tasks'])):?><div class="ot-checklist" data-ot-checklist><?php foreach((array)$s['tasks'] as $i=>$item):?><label><input type="checkbox" data-ot-task value="1"><span><?=e((string)$item)?></span></label><?php endforeach;?></div><?php endif;?>
      <?php if(!empty($s['question'])&&is_array($s['options']??null)):?><fieldset class="ot-options"><legend><?=e((string)$s['question'])?></legend><?php foreach((array)$s['options'] as $i=>$opt):?><label><input type="radio" name="ot_answer" value="<?=$i?>" data-ot-answer><span><?=e((string)$opt)?></span></label><?php endforeach;?></fieldset><?php endif;?>
      <div class="ot-feedback" data-ot-feedback hidden></div>
      <div data-ot-course data-endpoint="progress.php" data-action="<?=e((string)$w['step_action'])?>" data-lesson="<?=e((string)$w['lesson_id'])?>" data-step="<?=e((string)($s['id']??''))?>" data-kind="<?=e($kind)?>"></div>
    <?php }
    elseif($type==='kb'){$phase=(string)$w['phase'];$tour=(array)$w['tour'];$article=(array)$w['article'];$sim=is_array($w['simulation']??null)?$w['simulation']:null;?>
      <?php if($phase==='visual'):?><div class="ot-reading"><strong><?=e(tr('Mentální model'))?></strong><p><?=e((string)($tour['mental']??$article['summary']??''))?></p><small><?=e(tr('Neřeš detaily. Zkus si vlastními slovy říct, co je hlavní princip.'))?></small></div><?php endif;?>
      <?php if($phase==='simulation'):?><div class="ot-reading"><strong><?=e((string)($sim['title']??tr('Krátký experiment')))?></strong><p><?=e((string)($sim['lead']??$sim['task']??tr('Vyzkoušej princip na konkrétní situaci.')))?></p><?php if(is_array($sim['prediction']??null)):?><p class="ot-prompt"><?=e((string)($sim['prediction']['q']??''))?></p><?php endif;?></div><?php endif;?>
      <?php if($phase==='steps'):?><ol class="ot-step-list"><?php foreach((array)($tour['steps']??[]) as $step):?><li><?=e((string)$step)?></li><?php endforeach;?></ol><?php endif;?>
      <?php if($phase==='deep'):?><div class="ot-reading"><strong><?=e(tr('Vysvětli si princip'))?></strong><p><?=e((string)($tour['mental']??$article['summary']??''))?></p><?php if(!empty($tour['mistakes'])):?><details><summary><?=e(tr('Na co si dát pozor'))?></summary><ul><?php foreach((array)$tour['mistakes'] as $m):?><li><?=e((string)$m)?></li><?php endforeach;?></ul></details><?php endif;?></div><?php endif;?>
      <?php if($phase==='check'&&is_array($w['check'])):$c=(array)$w['check'];?><fieldset class="ot-options"><legend><?=e((string)($c['q']??tr('Ověř pochopení')))?></legend><?php foreach((array)($c['options']??[]) as $i=>$opt):?><label><input type="radio" name="ot_answer" value="<?=$i?>" data-ot-answer><span><?=e((string)$opt)?></span></label><?php endforeach;?></fieldset><?php endif;?>
      <div class="ot-feedback" data-ot-feedback hidden></div><div data-ot-kb data-endpoint="progress.php" data-topic="<?=e((string)$w['topic'])?>" data-phase="<?=e($phase)?>"></div>
    <?php }
    elseif($type==='skill'){$mode=(string)$w['mode'];$skill=(array)$w['skill'];if($mode==='mark_learning'):?><form method="post" class="ot-native-form" data-ot-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_skill_mark"><input type="hidden" name="skill" value="<?=e((string)$skill['slug'])?>"><?php v505_render_hidden_context($task);?><div class="ot-reading"><strong><?=e(tr('Studijní krok'))?></strong><p><?=e((string)$skill['description'])?></p></div></form><?php
      elseif($mode==='quick_check'):?><form method="post" class="ot-native-form" data-ot-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_skill_check"><input type="hidden" name="skill" value="<?=e((string)$skill['slug'])?>"><input type="hidden" name="check_mode" value="<?=e((string)$w['check_mode'])?>"><?php v505_render_hidden_context($task);foreach((array)$w['questions'] as $i=>$q):?><fieldset class="ot-options"><legend><?=e((string)$q['q'])?></legend><?php foreach((array)$q['options'] as $oi=>$opt):?><label><input type="radio" name="answers[<?=$i?>]" value="<?=$oi?>" required><span><?=e((string)$opt)?></span></label><?php endforeach;?></fieldset><?php endforeach;?></form><?php
      elseif($mode==='validation'):?><form method="post" class="ot-native-form" data-ot-form data-ot-draft-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_skill_validation"><input type="hidden" name="skill" value="<?=e((string)$skill['slug'])?>"><?php v505_render_hidden_context($task);?><label class="ot-field"><span><?=e(tr('Co chceš prakticky předvést?'))?></span><textarea name="note" rows="6" maxlength="800" required placeholder="<?=e(tr('Konkrétní výstup, postup nebo důkaz…'))?>"><?=e((string)($draft['note']??''))?></textarea></label></form><?php
      elseif($mode==='challenge'):$c=(array)$w['challenge'];?><form method="post" class="ot-native-form" data-ot-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_skill_challenge"><input type="hidden" name="challenge" value="<?=e((string)$c['id'])?>"><?php v505_render_hidden_context($task);foreach((array)$c['questions'] as $i=>$q):?><fieldset class="ot-options"><legend><?=e((string)$q['q'])?></legend><?php foreach((array)$q['options'] as $oi=>$opt):?><label><input type="radio" name="answers[<?=$i?>]" value="<?=$oi?>" required><span><?=e((string)$opt)?></span></label><?php endforeach;?></fieldset><?php endforeach;?><label class="ot-compact-field"><span><?=e(tr('Nápovědy'))?></span><select name="hints"><option value="0"><?=e(tr('bez nápovědy'))?></option><option value="1"><?=e(tr('1 hint'))?></option><option value="2"><?=e(tr('2 hints'))?></option><option value="3"><?=e(tr('3 hints'))?></option></select></label></form><?php
      elseif($mode==='locked'):?><div class="ot-reading"><strong><?=e(tr('Ještě zamčeno'))?></strong><ul><?php foreach((array)($w['missing']??[]) as $m):?><li><?=e(($m['type']??'')==='level'?tr('Potřebuješ level {level}',['level'=>(int)($m['need']??0)]):tr('Nejdřív posil prerequisite skill {skill}',['skill'=>(string)($m['skill']??'')]))?></li><?php endforeach;?></ul></div><?php
      else:?><div class="ot-complete-mark">✓<strong><?=e(tr('Evidence je pro tuto chvíli kompletní.'))?></strong></div><?php endif;}
    elseif($type==='growth'){$mode=(string)$w['mode'];if($mode==='module'){$m=(array)$w['module'];$p=(array)$w['path'];?><form method="post" class="ot-native-form" data-ot-form data-ot-draft-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_growth_complete"><input type="hidden" name="path" value="<?=e((string)$p['id'])?>"><input type="hidden" name="module_slug" value="<?=e((string)$m['slug'])?>"><?php v505_render_hidden_context($task);?><div class="ot-mini-sequence"><div><span>1</span><strong><?=e(tr('Zkus'))?></strong><p><?=e((string)$m['practice'])?></p></div><div><span>2</span><strong><?=e(tr('Vytvoř'))?></strong><p><?=e((string)$m['build'])?></p></div></div><label class="ot-field"><span><?=e(tr('Co bylo rozhodující?'))?></span><textarea name="reflection" rows="6" minlength="20" required placeholder="<?=e(tr('Krátce popiš, co fungovalo, co ne a co bys ověřil/a příště.'))?>"><?=e((string)($draft['reflection']??''))?></textarea></label></form><?php } else { ?><div class="ot-reading"><strong><?=e(tr('Cesta je zatím zamčená'))?></strong><ul><?php foreach((array)($w['reasons']??[]) as $r):?><li><?=e((string)$r)?></li><?php endforeach;?></ul></div><?php } }
    elseif($type==='lab'){$phase=(string)$w['phase'];$spec=(array)$w['spec'];$state=(array)$w['sim_state'];if($phase==='hypothesis'):?><form method="post" class="ot-native-form" data-ot-form data-ot-draft-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_lab_hypothesis"><input type="hidden" name="lesson" value="<?=(int)$spec['lesson_number']?>"><?php v505_render_hidden_context($task);?><div class="ot-incident"><span>Symptom</span><strong><?=e((string)$spec['problem'])?></strong></div><label class="ot-field"><span><?=e(tr('Hypotéza + důkaz, který ji může vyvrátit'))?></span><textarea name="hypothesis" rows="6" minlength="18" required><?=e((string)($draft['hypothesis']??''))?></textarea></label></form><?php
      elseif($phase==='diagnose'):?><form method="post" class="ot-native-form" data-ot-form data-ot-draft-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="<?=!empty($w['repair_ready'])?'v505_lab_validate':'v505_lab_command'?>"><input type="hidden" name="lesson" value="<?=(int)$spec['lesson_number']?>"><?php v505_render_hidden_context($task);?><?php if(!empty($state['last_output'])):?><div class="ot-terminal"><div><span><?=e(tr('poslední výstup'))?></span><code><?=e((string)($state['last_command']??''))?></code></div><pre><?=e((string)$state['last_output'])?></pre></div><?php endif;?><?php if(empty($w['repair_ready'])):?><label class="ot-field"><span><?=e(tr('Jeden diagnostický nebo opravný příkaz'))?></span><input name="command" required autocomplete="off" spellcheck="false" list="ot-command-list" value="<?=e((string)($draft['command']??''))?>"></label><datalist id="ot-command-list"><?php foreach((array)$spec['allowed_commands'] as $cmd):?><option value="<?=e((string)$cmd)?>"><?php endforeach;?></datalist><div class="ot-command-palette"><?php foreach(array_slice((array)$spec['allowed_commands'],0,8) as $cmd):?><button type="button" data-ot-fill-command="<?=e((string)$cmd)?>"><code><?=e((string)$cmd)?></code></button><?php endforeach;?></div><?php else:?><div class="ot-reading"><strong><?=e(tr('Oprava je provedena.'))?></strong><p><?=e(tr('Teď neprováděj další náhodné změny. Ověř cílový stav:'))?> <?=e((string)$spec['proof'])?></p></div><?php endif;?></form><?php
      elseif($phase==='explain'):?><form method="post" class="ot-native-form" data-ot-form data-ot-draft-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_lab_explain"><input type="hidden" name="lesson" value="<?=(int)$spec['lesson_number']?>"><?php v505_render_hidden_context($task);?><label class="ot-field"><span><?=e(tr('Příčina → důkaz → proč oprava funguje'))?></span><textarea name="explanation" rows="7" minlength="28" required><?=e((string)($draft['explanation']??''))?></textarea></label></form><?php
      elseif($phase==='transfer'):?><form method="post" class="ot-native-form" data-ot-form data-ot-draft-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_lab_transfer"><input type="hidden" name="lesson" value="<?=(int)$spec['lesson_number']?>"><?php v505_render_hidden_context($task);?><label class="ot-field"><span><?=e(tr('Co by zůstalo stejné v jiném prostředí a co musíš znovu změřit?'))?></span><textarea name="transfer" rows="7" minlength="28" required><?=e((string)($draft['transfer']??''))?></textarea></label></form><?php else:?><div class="ot-complete-mark">✓<strong><?=e(tr('Lab checkpoint je dokončený.'))?></strong></div><?php endif;}
    elseif($type==='result'){$item=(array)$w['item'];$r=(array)$item['record'];$p=(array)$item['project'];?>
      <div class="ot-feedback-card"><div><span><?=e(tr('Výsledek'))?></span><strong><?=e((string)($item['grade']??'—'))?></strong><small><?=e(tr('{points} / {max} bodů',['points'=>(int)($item['points']??0),'max'=>(int)($item['max_points']??0)]))?></small></div><section><span><?=e(tr('Další krok'))?></span><p><?=e(trim((string)($r['next_step']??''))!==''?(string)$r['next_step']:tr('Pokračuj podle rubriky a doporučení učitele.'))?></p><?php if(trim((string)($r['teacher_comment']??''))!==''):?><details><summary><?=e(tr('Celý komentář učitele'))?></summary><p><?=e((string)$r['teacher_comment'])?></p></details><?php endif;?></section></div>
      <?php if(empty($w['reviewed'])):?><form method="post" class="ot-native-form" data-ot-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v505_result_review"><input type="hidden" name="record" value="<?=e((string)$r['id'])?>"><?php v505_render_hidden_context($task);?></form><?php endif;?>
    <?php } elseif($type==='complete') { ?><div class="ot-complete-mark">✓<strong><?=e(tr('Hotovo.'))?></strong><span><?=e(tr('Systém už aktualizoval navazující progress.'))?></span></div><?php } else { ?><div class="ot-reading"><p><?=e((string)$task['instruction'])?></p></div><?php }
}
function v505_render_page(array $task,string $classId,array $module): void
{
    $primary=v505_primary_action($task);$stage=v505_stage_index($task);$studentKey=v505_student_key($classId);$draft=v505_draft_get($classId,$studentKey,(string)$task['id']);v505_event_record($classId,$studentKey,(string)$task['id'],'started',['source'=>(string)$task['source']]);$goal=function_exists('v504_goal_selected')?v504_goal_selected($classId,$studentKey):null;
    ?><!doctype html><html lang="<?=e(edu_html_lang())?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light"><meta name="csrf-token" content="<?=e(csrf_token())?>"><title><?=e((string)$task['title'])?> · EDUCANET</title><link rel="stylesheet" href="assets/app.css?v=46"><link rel="stylesheet" href="assets/one-task-v50-5.css?v=50.5"><link rel="stylesheet" href="assets/one-task-v50-7-7.css?v=50.7.7"><link rel="stylesheet" href="assets/i18n-v59.css?v=59"><?=edu_tr_json_js()?></head><body class="one-task-mode accent-<?=e((string)($module['accent']??'default'))?>"><main class="ot-shell" data-one-task data-task-id="<?=e((string)$task['id'])?>" data-stage="<?=$stage?>">
      <header class="ot-topbar"><a class="ot-back" href="<?=e((string)$task['return_url'])?>" data-ot-return><span><?=e(tr('Zpět'))?></span></a><div class="ot-context"><strong><?=e((string)$task['title'])?></strong></div><details class="ot-more"><summary aria-label="<?=e(tr('Více'))?>"><?=e(tr('Více'))?></summary><div><?=edu_lang_switcher_html(csrf_token(), '?' . (string)($_SERVER['QUERY_STRING'] ?? 'view=dashboard'))?><button type="button" data-ot-help-open><?=e(tr('Pomoc'))?></button><?php if($task['task_type']==='course'):?><a href="?view=course"><?=e(tr('Kurz'))?></a><?php endif;?><a href="?view=dashboard"><?=e(tr('Domů'))?></a><a href="<?=e((string)$task['return_url'])?>"><?=e(tr('Zpět'))?></a></div></details></header>
      <nav class="ot-stagebar" aria-label="<?=e(tr('Pracovní postup'))?>"><?php foreach([trm('Zadání'),trm('Pracuj'),trm('Ověř'),trm('Další krok')] as $i=>$label):?><div class="<?=$i<$stage?'done':($i===$stage?'active':'')?>"><i><?=$i<$stage?'✓':$i+1?></i><span><?=e(tr($label))?></span></div><?php endforeach;?></nav>
      <section class="ot-task-head"><div><?php if(is_array($goal)):?><?php endif;?><div class="ot-eyebrow"><?=e(tr('Teď · {estimate}',['estimate'=>(string)$task['estimate']]))?></div><h1><?=e((string)$task['title'])?></h1><p><?=e((string)$task['instruction'])?></p></div></section>
      <?php if(!empty($_SESSION['flash'])):?><div class="ot-notice"><?=e((string)$_SESSION['flash'])?></div><?php unset($_SESSION['flash']);endif;?>
      <section class="ot-workspace" data-ot-workspace><?php v505_render_workspace($task,$classId,$module,$draft);?></section>
      <aside class="ot-help-panel" data-ot-help hidden><header><div><strong><?=e(tr('Pomoc bez ztracení postupu'))?></strong></div><button type="button" data-ot-help-close><?=e(tr('Zavřít'))?></button></header><?php if(!empty($task['help'])):?><div class="ot-help-list"><?php foreach((array)$task['help'] as $i=>$h):if(is_array($h)&&isset($h['href'])):?><a href="<?=e((string)$h['href'])?>"><strong><?=e((string)$h['label'])?></strong><span><?=e(tr('Otevřít vysvětlení →'))?></span></a><?php elseif(is_array($h)):?><details><summary><?=e(tr('Úroveň {n} · {label}',['n'=>$i+1,'label'=>(string)($h['label']??tr('Nápověda'))]))?></summary><p><?=e((string)($h['text']??''))?></p></details><?php endif;endforeach;?></div><?php else:?><p><?=e(tr('Vrať se k zadání a zkus pojmenovat jediný důkaz nebo krok, který ti chybí. Pokud to nestačí, otevři „Potřebuji pomoc“ v hlavním studijním menu po návratu.'))?></p><?php endif;?></aside>
      <footer class="ot-actionbar"><div class="ot-save-state" data-ot-save-state><span><?=e(tr('Průběžně ukládáno'))?></span></div><?php if((string)$primary['mode']==='link'):?><a class="ot-primary" href="<?=e((string)$primary['href'])?>" data-ot-primary data-action-code="<?=e((string)$primary['code'])?>"><?=e((string)$primary['label'])?></a><?php else:?><button class="ot-primary" type="button" data-ot-primary data-action-code="<?=e((string)$primary['code'])?>"><?=e((string)$primary['label'])?></button><?php endif;?></footer>
    </main><script src="assets/i18n-v58.js?v=59"></script><script src="assets/one-task-v50-5.js?v=50.5"></script></body></html><?php
}

function v505_teacher_metrics(string $classId): array
{
    $events=[];foreach(v505_rows('events') as $row)if(is_array($row)&&(string)($row['class_id']??'')===$classId)$events[]=$row;
    $students=[];$taskStarts=[];$passes=[];$retries=[];$help=[];foreach($events as $e){$sk=(string)($e['student_key']??'');if($sk!=='')$students[$sk]=true;$tid=(string)($e['task_id']??'');$kind=(string)($e['kind']??'');if($kind==='started')$taskStarts[$sk.'|'.$tid]=true;elseif(in_array($kind,['passed','result_reviewed'],true))$passes[$sk.'|'.$tid]=true;elseif($kind==='retry')$retries[]=$e;elseif($kind==='help')$help[]=$e;}
    return ['students'=>count($students),'started'=>count($taskStarts),'completed'=>count($passes),'retries'=>count($retries),'help'=>count($help),'completion_rate'=>$taskStarts?(int)round(count($passes)/max(1,count($taskStarts))*100):0];
}
function v505_render_teacher_metrics(string $classId): void
{
    $m=v505_teacher_metrics($classId);?><section class="teacher-ops-section v505-teacher-metrics"><div class="teacher-section-head"><div><div class="eyebrow">v50.5 · One Task Mode</div><h2>Průchod jednotlivými úkoly</h2><p>Telemetrie pracovního režimu: kolik studentů úkol otevřelo, dokončilo, potřebovalo nápovědu nebo retry. Nemění známky ani mastery.</p></div><span><?=$m['completion_rate']?> % dokončeno</span></div><div class="v50-teacher-dims"><article><span>Studenti</span><strong><?=$m['students']?></strong></article><article><span>Otevřené tasky</span><strong><?=$m['started']?></strong></article><article><span>Dokončené</span><strong><?=$m['completed']?></strong></article><article><span>Retry</span><strong><?=$m['retries']?></strong></article><article><span>Nápovědy</span><strong><?=$m['help']?></strong></article></div></section><?php
}
