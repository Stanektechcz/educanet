<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v47 · Student Learning Coach
 *
 * Read-only planning is derived from existing course evidence. Student writes
 * happen only after an explicit preference, completion or exit-ticket action.
 * Coach completion never awards XP, changes grades or modifies mastery.
 */

function coach_store(string $name): array
{
    return adaptive_store('coach_' . preg_replace('/[^a-z0-9_\-]/i', '', $name));
}

function coach_store_path(string $name): string
{
    return adaptive_store_path('coach_' . preg_replace('/[^a-z0-9_\-]/i', '', $name));
}

/** v58 (F2): RMW jednoho záznamu kolekce kouče pod zámkem – $fn(?array $current): ?array. */
function coach_store_update_row(string $name, string $id, callable $fn): ?array
{
    return storage_map_update(coach_store_path($name), $id, $fn);
}

function coach_day_key(string $classId, string $studentKey, ?string $date = null): string
{
    return $classId . '|' . $studentKey . '|' . ($date ?? date('Y-m-d'));
}

function coach_preferences(string $classId, string $studentKey): array
{
    $row = coach_store('preferences')[$classId . '|' . $studentKey] ?? [];
    if (!is_array($row)) $row = [];
    $minutes = (int)($row['minutes'] ?? 25);
    if (!in_array($minutes, [10,25,45], true)) $minutes = 25;
    $intent = (string)($row['intent'] ?? 'balanced');
    if (!in_array($intent, ['balanced','exam'], true)) $intent = 'balanced';
    return array_replace([
        'class_id'=>$classId,
        'student_key'=>$studentKey,
        'minutes'=>$minutes,
        'intent'=>$intent,
        'updated_at'=>null,
    ], $row, ['minutes'=>$minutes,'intent'=>$intent]);
}

function coach_preferences_save(string $classId, string $studentKey, int $minutes, string $intent): array
{
    if ($studentKey === '') throw new RuntimeException(tr('Studentský profil nebyl nalezen.'));
    if (!in_array($minutes, [10,25,45], true)) $minutes = 25;
    if (!in_array($intent, ['balanced','exam'], true)) $intent = 'balanced';
    $id = $classId . '|' . $studentKey;
    return (array)coach_store_update_row('preferences', $id, static fn(?array $current): array => ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'minutes'=>$minutes,'intent'=>$intent,'updated_at'=>date(DATE_ATOM)]);
}

function coach_daily_row(string $classId, string $studentKey, ?string $date = null): array
{
    $key = coach_day_key($classId,$studentKey,$date);
    $row = coach_store('daily')[$key] ?? [];
    return is_array($row) ? $row : ['id'=>$key,'class_id'=>$classId,'student_key'=>$studentKey,'date'=>$date ?? date('Y-m-d'),'completed'=>[]];
}

function coach_item_id(string $type, string $ref): string
{
    return 'ci_' . substr(hash('sha256', $type . '|' . $ref), 0, 16);
}

function coach_step_set(string $classId, string $studentKey, string $itemId, bool $done): void
{
    if ($studentKey === '' || !preg_match('/^ci_[a-f0-9]{16}$/D', $itemId)) throw new RuntimeException(tr('Neplatný studijní krok.'));
    $id = coach_day_key($classId,$studentKey);
    coach_store_update_row('daily', $id, static function (?array $row) use ($id, $classId, $studentKey, $itemId, $done): array {
        $row = $row ?? ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'date'=>date('Y-m-d'),'completed'=>[]];
        if (!is_array($row['completed'] ?? null)) $row['completed'] = [];
        if ($done) $row['completed'][$itemId] = date(DATE_ATOM); else unset($row['completed'][$itemId]);
        $row['updated_at'] = date(DATE_ATOM);
        return $row;
    });
}

function coach_exit_ticket_save(string $classId, string $studentKey, array $input): void
{
    if ($studentKey === '') throw new RuntimeException(tr('Studentský profil nebyl nalezen.'));
    $id = coach_day_key($classId,$studentKey);
    $learned = trim(u_substr((string)($input['learned'] ?? ''),0,700));
    $unclear = trim(u_substr((string)($input['unclear'] ?? ''),0,700));
    $next = trim(u_substr((string)($input['next'] ?? ''),0,700));
    $confidence = max(1,min(3,(int)($input['confidence'] ?? 2)));
    $minutes = (int)($input['minutes'] ?? 25); if(!in_array($minutes,[10,25,45],true))$minutes=25;
    $energy = (string)($input['energy'] ?? 'normal'); if(!in_array($energy,['low','normal','high'],true))$energy='normal';
    $intent = (string)($input['intent'] ?? 'balanced'); if(!in_array($intent,['balanced','exam'],true))$intent='balanced';
    $ticket=['learned'=>$learned,'unclear'=>$unclear,'next'=>$next,'confidence'=>$confidence,'minutes'=>$minutes,'energy'=>$energy,'intent'=>$intent,'saved_at'=>date(DATE_ATOM)];
    coach_store_update_row('daily', $id, static function (?array $row) use ($id, $classId, $studentKey, $ticket): array {
        $row = $row ?? ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'date'=>date('Y-m-d'),'completed'=>[]];
        $row['exit_ticket']=$ticket;$row['updated_at']=date(DATE_ATOM);
        return $row;
    });
    // Reuse the existing reflection signal so the student's uncertainty can
    // support teacher interventions without creating a parallel private silo.
    adaptive_journal_save($classId,$studentKey,date('Y-m-d'),['understood'=>$learned,'unclear'=>$unclear,'next'=>$next]);
}

function coach_mistake_notebook(string $classId, string $studentKey, array $module, int $limit = 8): array
{
    if (!function_exists('ml_student_events')) return [];
    $events = ml_student_events($classId,$studentKey);
    if (!$events) return [];
    $latestCorrectByTopic=[];
    foreach($events as $e){
        if(!is_array($e))continue;$topic=(string)($e['topic']??'');if($topic==='')continue;
        if(!empty($e['correct'])){$at=(string)($e['created_at']??'');if($at>($latestCorrectByTopic[$topic]??''))$latestCorrectByTopic[$topic]=$at;}
    }
    $groups=[];
    foreach($events as $e){
        if(!is_array($e)||!empty($e['correct'])||empty($e['misconception']))continue;
        $topic=(string)($e['topic']??'');$code=(string)$e['misconception'];if($topic===''||$code==='')continue;
        $key=$topic.'|'.$code;$at=(string)($e['created_at']??'');
        if(!isset($groups[$key]))$groups[$key]=['topic'=>$topic,'code'=>$code,'label'=>(string)($e['misconception_label']??$code),'count'=>0,'overconfident'=>0,'last_at'=>'','last_event'=>$e];
        $groups[$key]['count']++;
        if((int)($e['confidence']??0)>=3)$groups[$key]['overconfident']++;
        if($at>=(string)$groups[$key]['last_at']){$groups[$key]['last_at']=$at;$groups[$key]['last_event']=$e;}
    }
    $out=[];$now=time();
    foreach($groups as $g){
        $topic=(string)$g['topic'];$article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];
        $lastWrong=(string)$g['last_at'];$lastCorrect=(string)($latestCorrectByTopic[$topic]??'');$recovered=$lastCorrect!==''&&$lastCorrect>$lastWrong;
        $spec=function_exists('ml_misconception_spec')?ml_misconception_spec($topic,$article,(int)($g['last_event']['selected']??-1),(int)($g['last_event']['correct_index']??-2)):['coach'=>tr('Zkus princip znovu vysvětlit vlastními slovy a ověřit na novém příkladu.')];
        $ts=strtotime($lastWrong)?:0;$ageDays=$ts?(int)floor(($now-$ts)/86400):999;
        $priority=(int)$g['count']*10+(int)$g['overconfident']*12+($ageDays<=7?12:0)-($recovered?20:0);
        $out[]=[
            'topic'=>$topic,
            'title'=>(string)($article['title']??$topic),
            'code'=>(string)$g['code'],
            'label'=>(string)$g['label'],
            'count'=>(int)$g['count'],
            'overconfident'=>(int)$g['overconfident'],
            'last_at'=>$lastWrong,
            'recovered'=>$recovered,
            'priority'=>$priority,
            'coach'=>(string)($spec['coach']??tr('Vrať se k principu, vybav si ho bez poznámek a ověř ho na jiném příkladu.')),
            'href'=>module_url('kb_lesson',['topic'=>$topic,'reference'=>1]),
        ];
    }
    usort($out,static function(array $a,array $b):int{
        if((bool)$a['recovered']!==(bool)$b['recovered'])return $a['recovered']?1:-1;
        return (int)$b['priority']<=>(int)$a['priority'];
    });
    return array_slice($out,0,max(1,$limit));
}

function coach_confidence_calibration(string $classId, string $studentKey): array
{
    $bins=function_exists('ml_confidence_calibration')?ml_confidence_calibration($classId,$studentKey):['underconfident'=>0,'calibrated'=>0,'overconfident'=>0];
    $under=(int)($bins['underconfident']??0);$ok=(int)($bins['calibrated']??0);$over=(int)($bins['overconfident']??0);$total=$under+$ok+$over;
    $state='new';$title=tr('Sbíráme první signály');$text=tr('U každého krátkého checku označ, jak moc si věříš. Systém pak porovná jistotu s výsledkem.');
    if($total>=3){
        if($over>$under+max(1,(int)round($total*.15))){$state='over';$title=tr('Jistotu vždy opři o důkaz');$text=tr('Častěji se objevila vysoká jistota u chybné odpovědi. Před potvrzením si polož otázku: „Jaký důkaz by mě přesvědčil o opaku?“');}
        elseif($under>$over+max(1,(int)round($total*.15))){$state='under';$title=tr('Důvěřuj tomu, co umíš ověřit');$text=tr('Několikrát byla správná odpověď doprovázená nízkou jistotou. Po správném řešení si pojmenuj, který důkaz ti dovolí být příště jistější.');}
        else{$state='calibrated';$title=tr('Jistota odpovídá výsledkům');$text=tr('Tvoje sebehodnocení je zatím dobře vyvážené. Udržuj zvyk nejdřív odpovědět a až potom kontrolovat.');}
    }
    return ['state'=>$state,'title'=>$title,'text'=>$text,'total'=>$total,'under'=>$under,'calibrated'=>$ok,'over'=>$over];
}

function coach_review_forecast(string $classId, string $studentKey, int $days = 7): array
{
    $today=new DateTimeImmutable('today');$buckets=array_fill(0,$days+1,0);$overdue=0;$topics=[];
    foreach(adaptive_retrieval_state_rows() as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;
        $due=(string)($row['due_date']??'');if($due==='')continue;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$due);if(!$d)continue;$delta=(int)$today->diff($d)->format('%r%a');
        if($delta<0){$overdue++;$delta=0;}
        if($delta<=$days){$buckets[$delta]++;$topics[]=(string)($row['topic']??'');}
    }
    return ['overdue'=>$overdue,'buckets'=>$buckets,'total'=>array_sum($buckets),'topics'=>array_values(array_unique(array_filter($topics)))];
}

function coach_week_summary(string $classId, string $studentKey): array
{
    $from=strtotime('-6 days midnight');$active=[];$steps=0;$reflections=0;
    foreach(coach_store('daily') as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;$ts=strtotime((string)($row['date']??''));if(!$ts||$ts<$from)continue;
        if(!empty($row['completed'])||!empty($row['exit_ticket']))$active[date('Y-m-d',$ts)]=true;
        $steps+=count((array)($row['completed']??[]));if(!empty($row['exit_ticket']))$reflections++;
    }
    $retrievalQ=0;$retrievalCorrect=0;
    foreach(adaptive_store('retrieval_attempts') as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;$ts=strtotime((string)($row['at']??''));if(!$ts||$ts<$from)continue;$retrievalQ+=(int)($row['total']??0);$retrievalCorrect+=(int)($row['score']??0);$active[date('Y-m-d',$ts)]=true;
    }
    $tasks=0;foreach(teacher_tasks_for_student($classId,$studentKey,true) as $row){if((string)($row['status']??'')!=='done')continue;$ts=strtotime((string)($row['completed_at']??''));if($ts&&$ts>=$from){$tasks++;$active[date('Y-m-d',$ts)]=true;}}
    return ['active_days'=>count($active),'coach_steps'=>$steps,'reflections'=>$reflections,'retrieval_total'=>$retrievalQ,'retrieval_correct'=>$retrievalCorrect,'tasks_done'=>$tasks];
}

function coach_course_candidate(string $classId): ?array
{
    global $nextLessons,$extendedLessons;
    foreach(v42_lessons_for_class($classId) as $lesson){
        if(!is_array($lesson))continue;$n=(int)($lesson['number']??0);$done=false;$unlocked=false;
        if($n<=1){$done=learning_primary_block_complete($classId);$unlocked=true;}
        elseif($n===2){$done=learning_next_lesson_complete($classId,$lesson);$unlocked=learning_primary_block_complete($classId);}
        else{$done=learning_course_lesson_complete($classId,$lesson);$unlocked=learning_course_lesson_unlocked($classId,$n,$nextLessons,$extendedLessons);}
        if(!$done&&$unlocked)return ['lesson'=>$lesson,'href'=>v42_lesson_url($lesson)];
    }
    return null;
}

function coach_skill_candidate(string $classId, string $studentKey): ?array
{
    if(!function_exists('skill_progress_snapshot_map')||!function_exists('skill_relevant_skills'))return null;
    $map=skill_progress_snapshot_map($classId,$studentKey);if(!$map)return null;$best=null;$bestScore=-999;
    foreach(skill_relevant_skills($classId) as $skill){
        if(!is_array($skill))continue;$slug=(string)($skill['slug']??'');$p=is_array($map[$slug]??null)?$map[$slug]:[];$state=(string)($p['unlock_state']??'locked');if(in_array($state,['locked','mastered'],true))continue;
        $m=(float)($p['mastery_percent']??0);$score=$m>=65?45:($m>0?35:20);$score-=((int)($skill['tier']??1))*2;
        if($score>$bestScore){$bestScore=$score;$best=['skill'=>$skill,'progress'=>$p];}
    }
    return $best;
}

function coach_plan(string $classId, string $studentKey, array $module, array $tourMap, int $minutes = 25, string $energy = 'normal', string $intent = 'balanced'): array
{
    if(!in_array($minutes,[10,25,45],true))$minutes=25;
    if(!in_array($energy,['low','normal','high'],true))$energy='normal';
    if(!in_array($intent,['balanced','exam'],true))$intent='balanced';
    $candidates=[];$today=date('Y-m-d');

    foreach(array_slice(teacher_tasks_for_student($classId,$studentKey,false),0,5) as $task){
        $due=(string)($task['due_at']??'');$days=null;if($due!==''){$days=(int)((new DateTimeImmutable('today'))->diff(new DateTimeImmutable($due)))->format('%r%a');}
        $priority=90;if((string)($task['priority']??'normal')==='high')$priority+=20;if($days!==null&&$days<0)$priority+=45;elseif($days===0)$priority+=35;elseif($days!==null&&$days<=3)$priority+=25;
        $mins=$energy==='low'?8:12;
        $ref=(string)($task['id']??'');
        $candidates[]=['id'=>coach_item_id('task',$ref),'type'=>'task','ref'=>$ref,'score'=>$priority,'minutes'=>$mins,'title'=>(string)($task['title']??tr('Úkol od učitele')),'title_html'=>(string)($task['title']??'')!==''?edu_cs((string)$task['title']):tr_html('Úkol od učitele'),'text_is_content'=>trim((string)($task['instructions']??''))!=='','text'=>trim((string)($task['instructions']??''))!==''?(string)$task['instructions']:tr('Dokonči konkrétní krok zadaný učitelem.'),'reason'=>$days!==null&&$days<0?tr('Termín už prošel'):($days===0?tr('Termín je dnes'):((string)($task['priority']??'')==='high'?tr('Vysoká priorita'):tr('Aktivní úkol'))),'href'=>'?view=dashboard#teacher-tasks','task'=>$task];
    }

    if(function_exists('teacher_ops_student_interventions_public')){
        foreach(array_slice(teacher_ops_student_interventions_public($classId,$studentKey),0,2) as $plan){
            $pending=array_values(array_filter((array)($plan['steps']??[]),static fn($st)=>is_array($st)&&(string)($st['status']??'pending')==='pending'));$step=$pending[0]??null;if(!$step)continue;
            $ref=(string)($plan['id']??'').'|'.(string)($step['id']??$step['title']??'next');
            $candidates[]=['id'=>coach_item_id('intervention',$ref),'type'=>'intervention','ref'=>$ref,'score'=>128,'minutes'=>$energy==='low'?7:10,'title'=>(string)($step['title']??tr('Další krok podpory')),'title_html'=>(string)($step['title']??'')!==''?edu_cs((string)$step['title']):tr_html('Další krok podpory'),'text_is_content'=>((string)($step['detail']??$plan['problem']??''))!=='','text'=>(string)($step['detail']??$plan['problem']??''),'reason'=>tr('Domluvený krok s učitelem'),'href'=>'?view=dashboard#support-plan','plan'=>$plan];
        }
    }

    $reviewQueue=adaptive_retrieval_queue($classId,$studentKey,$module,$tourMap,$minutes>=45?5:3);
    if($reviewQueue){
        $wrong=0;foreach($reviewQueue as $r)$wrong+=(int)($r['state']['incorrect_count']??0);
        $candidates[]=['id'=>coach_item_id('retrieval',$today),'type'=>'retrieval','ref'=>$today,'score'=>($intent==='exam'?145:120)+min(20,$wrong*2),'minutes'=>$minutes===10?4:5,'title'=>tr('Vybav si {count} témata bez poznámek',['count'=>count($reviewQueue)]),'title_html'=>tr_html('Vybav si {count} témata bez poznámek',['count'=>count($reviewQueue)]),'text'=>tr('Krátké retrieval opakování. Nejdřív odpověz z paměti, až potom kontroluj.'),'reason'=>$wrong>0?tr('Témata se vrací podle chyb a rozestupů'):tr('Dnes jsou témata zralá na opakování'),'href'=>'?view=review','review'=>$reviewQueue];
    }

    $mistakes=coach_mistake_notebook($classId,$studentKey,$module,6);
    foreach(array_slice(array_values(array_filter($mistakes,static fn($m)=>empty($m['recovered']))),0,2) as $mistake){
        $candidates[]=['id'=>coach_item_id('mistake',(string)$mistake['topic'].'|'.(string)$mistake['code']),'type'=>'mistake','ref'=>(string)$mistake['topic'],'score'=>($intent==='exam'?125:104)+min(24,(int)$mistake['count']*4)+(int)$mistake['overconfident']*5,'minutes'=>$energy==='low'?6:9,'title'=>tr('Oprav mentální model: {title}',['title'=>(string)$mistake['title']]),'title_html'=>tr_html('Oprav mentální model: {title}',['title'=>edu_cs((string)$mistake['title'])]),'text_is_content'=>true,'text'=>(string)$mistake['coach'],'reason'=>(int)$mistake['overconfident']>0?tr('{count}× zachycená stejná chyba · vysoká jistota u chyby',['count'=>(int)$mistake['count']]):tr('{count}× zachycená stejná chyba',['count'=>(int)$mistake['count']]),'href'=>module_url('study_loop',['topic'=>(string)$mistake['topic']]),'mistake'=>$mistake];
    }

    $course=coach_course_candidate($classId);
    if($course){$lesson=$course['lesson'];$lessonTitleIsSet=array_key_exists('title',$lesson)&&$lesson['title']!==null;$lessonTitleHtml=$lessonTitleIsSet?edu_cs((string)$lesson['title']):tr_html('Další lekce');$candidates[]=['id'=>coach_item_id('lesson',(string)($lesson['id']??$lesson['number']??'')),'type'=>'lesson','ref'=>(string)($lesson['id']??''),'score'=>$intent==='exam'?62:82,'minutes'=>$energy==='low'?10:($minutes>=45?20:15),'title'=>tr('Pokračuj: {title}',['title'=>(string)($lesson['title']??tr('Další lekce'))]),'title_html'=>tr_html('Pokračuj: {title}',['title'=>$lessonTitleHtml]),'text_is_content'=>isset($lesson['goal']),'text'=>(string)($lesson['goal']??tr('Posuň se o jeden konkrétní krok v kurzu.')),'reason'=>tr('Nejbližší odemčený krok kurzu'),'href'=>(string)$course['href'],'lesson'=>$lesson];}

    $skill=coach_skill_candidate($classId,$studentKey);
    if($skill){$s=$skill['skill'];$m=(float)($skill['progress']['mastery_percent']??0);$candidates[]=['id'=>coach_item_id('skill',(string)$s['slug']),'type'=>'skill','ref'=>(string)$s['slug'],'score'=>($intent==='exam'?95:70)+($m>=60?12:0),'minutes'=>8,'title'=>tr('Upevni skill: {name}',['name'=>(string)($s['name']??$s['slug'])]),'title_html'=>tr_html('Upevni skill: {name}',['name'=>edu_cs((string)($s['name']??$s['slug']))]),'text'=>tr('Krátký practice check na dovednosti, která už je odemčená.'),'reason'=>$m>0?tr('Rozpracované mastery {percent} %',['percent'=>edu_number($m,0)]):tr('Dostupný další skill'),'href'=>module_url('skill_detail',['skill'=>(string)$s['slug']]),'skill'=>$skill];}

    usort($candidates,static fn(array $a,array $b):int=>(int)$b['score']<=>(int)$a['score']);
    // Keep variety: one candidate of the same type (tasks may use two if urgent).
    $selected=[];$used=0;$typeCounts=[];
    foreach($candidates as $candidate){
        $type=(string)$candidate['type'];$limit=$type==='task'?2:1;if(($typeCounts[$type]??0)>=$limit)continue;
        $cost=(int)$candidate['minutes'];if($selected&&$used+$cost>$minutes)continue;
        if(!$selected&&$cost>$minutes){$candidate['minutes']=$minutes;$cost=$minutes;}
        $selected[]=$candidate;$used+=$cost;$typeCounts[$type]=($typeCounts[$type]??0)+1;
        if($used>=max(6,$minutes-3))break;
    }
    if(!$selected&&$course){$lesson=$course['lesson'];$selected[]=['id'=>coach_item_id('lesson',(string)($lesson['id']??'next')),'type'=>'lesson','ref'=>(string)($lesson['id']??''),'score'=>1,'minutes'=>$minutes,'title'=>tr('Posuň se o jeden malý krok'),'title_html'=>tr_html('Posuň se o jeden malý krok'),'text_is_content'=>isset($lesson['goal']),'text'=>(string)($lesson['goal']??''),'reason'=>tr('Nejbližší odemčená lekce'),'href'=>(string)$course['href'],'lesson'=>$lesson];$used=$minutes;}

    $daily=coach_daily_row($classId,$studentKey);$completed=(array)($daily['completed']??[]);foreach($selected as &$item)$item['done']=isset($completed[(string)$item['id']]);unset($item);
    $doneCount=count(array_filter($selected,static fn($x)=>!empty($x['done'])));
    return ['minutes'=>$minutes,'energy'=>$energy,'intent'=>$intent,'items'=>$selected,'planned_minutes'=>$used,'done_count'=>$doneCount,'total_count'=>count($selected),'complete'=>$selected&&$doneCount===count($selected),'candidate_count'=>count($candidates)];
}

function coach_dashboard_snapshot(string $classId, string $studentKey, array $module, array $tourMap): array
{
    static $cache=[];
    $key=$classId.'|'.$studentKey;
    if(isset($cache[$key]))return $cache[$key];
    $prefs=coach_preferences($classId,$studentKey);$plan=coach_plan($classId,$studentKey,$module,$tourMap,(int)$prefs['minutes'],'normal',(string)$prefs['intent']);
    $mistakes=coach_mistake_notebook($classId,$studentKey,$module,6);$openMistakes=count(array_filter($mistakes,static fn($m)=>empty($m['recovered'])));
    return $cache[$key]=['preferences'=>$prefs,'plan'=>$plan,'open_mistakes'=>$openMistakes,'forecast'=>coach_review_forecast($classId,$studentKey,7),'week'=>coach_week_summary($classId,$studentKey),'calibration'=>coach_confidence_calibration($classId,$studentKey)];
}
