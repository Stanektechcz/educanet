<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function teacher_student_flags_path(): string { return STORAGE_DIR . '/teacher_student_flags.json.php'; }

function teacher_selected_student_keys(string $classId, mixed $raw): array
{
    $students=project_students_for_class($classId);$out=[];
    foreach(is_array($raw)?$raw:[] as $key){$key=(string)$key;if(isset($students[$key]))$out[$key]=true;}
    return array_keys($out);
}
function teacher_tasks_path(): string { return STORAGE_DIR . '/teacher_tasks.json.php'; }

function teacher_student_flag_rows(): array
{
    $rows = load_php_json(teacher_student_flags_path());
    return is_array($rows) ? $rows : [];
}

function teacher_student_flag_map(string $classId): array
{
    $cacheKey = 'teacher_student_flags:' . $classId;
    if (isset($GLOBALS['educanet_runtime_indexes'][$cacheKey]) && is_array($GLOBALS['educanet_runtime_indexes'][$cacheKey])) return $GLOBALS['educanet_runtime_indexes'][$cacheKey];
    $out = [];
    foreach (teacher_student_flag_rows() as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
        $studentKey = (string)($row['student_key'] ?? '');
        if ($studentKey !== '') $out[$studentKey] = $row;
    }
    return $GLOBALS['educanet_runtime_indexes'][$cacheKey] = $out;
}

function teacher_student_flag(string $classId, string $studentKey): array
{
    $map = teacher_student_flag_map($classId);
    return is_array($map[$studentKey] ?? null) ? $map[$studentKey] : ['class_id'=>$classId,'student_key'=>$studentKey,'marker'=>'none','updated_at'=>null,'updated_by'=>null];
}

function teacher_student_marker_label(string $marker): string
{
    return match($marker){
        'urgent'=>'Urgentní',
        'watch'=>'Sledovat',
        'resolved'=>'Vyřešeno',
        default=>'Bez označení',
    };
}

function teacher_student_flags_set(string $classId, array $studentKeys, string $marker): int
{
    if (!teacher_export_authenticated()) throw new RuntimeException('Pouze učitel může měnit označení studentů.');
    if (!in_array($marker,['none','watch','urgent','resolved'],true)) $marker='none';
    $students = project_students_for_class($classId);
    $valid = [];
    foreach ($studentKeys as $key) { $key=(string)$key; if(isset($students[$key])) $valid[$key]=true; }
    if (!$valid) throw new RuntimeException('Vyberte alespoň jednoho studenta.');

    $updated=0;
    storage_update(teacher_student_flags_path(),static function(array $rows) use($valid,$classId,$marker,&$updated): array {
    $index = [];
    foreach ($rows as $i => $row) if(is_array($row)) $index[(string)($row['class_id']??'').'|'.(string)($row['student_key']??'')]=$i;
    $now=date(DATE_ATOM); $updated=0;
    foreach(array_keys($valid) as $studentKey){
        $compound=$classId.'|'.$studentKey;
        if($marker==='none'){
            if(isset($index[$compound])){ unset($rows[$index[$compound]]); $updated++; }
            continue;
        }
        $row=['class_id'=>$classId,'student_key'=>$studentKey,'marker'=>$marker,'updated_at'=>$now,'updated_by'=>teacher_display_name()];
        if(isset($index[$compound])) $rows[$index[$compound]]=$row; else $rows[]=$row;
        $updated++;
    }
    return array_values($rows);});
    return $updated;
}

function teacher_student_result_status(array $row): string
{
    if ((int)($row['returned'] ?? 0) > 0) return 'returned';
    if (!empty($row['attention'])) return 'attention';
    if ((int)($row['published'] ?? 0) > 0) return 'ok';
    return 'no_result';
}

function teacher_student_status_label(string $status): string
{
    return match($status){
        'returned'=>'Vráceno',
        'attention'=>'Prověřit',
        'ok'=>'V pořádku',
        'no_result'=>'Bez výsledku',
        default=>'Neznámý stav',
    };
}

function teacher_student_priority(array $row, string $marker='none'): string
{
    if ($marker === 'resolved') return 'normal';
    if ($marker === 'urgent') return 'high';

    $mastery = $row['mastery'] ?? null;
    $avgGrade = $row['avg_grade'] ?? null;
    $activity=is_array($row['activity']??null)?$row['activity']:[];
    $hasEvidence=(bool)($row['has_evidence']??((int)($row['published']??0)>0||(int)($activity['tests']??0)>0||(int)($activity['labs']??0)>0||(int)($activity['prestige_attempts']??0)>0));
    $high = (int)($row['returned'] ?? 0) > 0
        || (int)($row['needs_work'] ?? 0) >= 2
        || ($avgGrade !== null && (float)$avgGrade >= 4.25)
        || ($hasEvidence && $mastery !== null && (float)$mastery < 25);
    if ($high) return 'high';
    if ($marker === 'watch') return 'medium';

    $medium = !empty($row['attention'])
        || (int)($row['drafts'] ?? 0) > 0
        || (int)($row['published'] ?? 0) === 0
        || ($avgGrade !== null && (float)$avgGrade >= 3.5)
        || ($hasEvidence && $mastery !== null && (float)$mastery < 40);
    return $medium ? 'medium' : 'normal';
}


function teacher_student_score_drop_value(string $classId,string $studentKey): ?float
{
    $events=[];
    foreach(project_grade_records_for_class($classId) as $record){if(!is_array($record)||(string)($record['status']??'')!=='published')continue;$targets=[];if((string)($record['target_type']??'')==='individual')$targets=[(string)($record['target_id']??'')];else{$group=project_group_find((string)($record['target_id']??''));$targets=is_array($group)?array_map('strval',(array)($group['member_keys']??[])):[];}if(!in_array($studentKey,$targets,true))continue;$max=max(1,(int)($record['max_points']??1));$events[]=['pct'=>((int)($record['points']??0)/$max)*100,'at'=>(string)($record['updated_at']??$record['created_at']??'')];}
    if(count($events)<2)return null;usort($events,static fn($a,$b)=>strcmp((string)$a['at'],(string)$b['at']));$last=array_pop($events);$avg=array_sum(array_column($events,'pct'))/count($events);return (float)$last['pct']-$avg;
}

function teacher_student_priority_label(string $priority): string
{
    return match($priority){'high'=>'Vysoká','medium'=>'Střední',default=>'Běžná'};
}

function teacher_task_rows(): array
{
    $rows=load_php_json(teacher_tasks_path());
    return is_array($rows)?$rows:[];
}

function teacher_tasks_for_student(string $classId,string $studentKey,bool $includeDone=false): array
{
    $rows=[];
    foreach(teacher_task_rows() as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;
        if(!$includeDone && (string)($row['status']??'assigned')!=='assigned')continue;
        $rows[]=$row;
    }
    usort($rows,static function(array $a,array $b):int{
        $rank=['high'=>3,'normal'=>2,'low'=>1];
        $p=($rank[(string)($b['priority']??'normal')]??2)<=>($rank[(string)($a['priority']??'normal')]??2);
        if($p!==0)return $p;
        $ad=(string)($a['due_at']??'');$bd=(string)($b['due_at']??'');
        if($ad!==$bd){if($ad==='')return 1;if($bd==='')return -1;return strcmp($ad,$bd);}
        return strcmp((string)($b['created_at']??''),(string)($a['created_at']??''));
    });
    return $rows;
}

function teacher_task_summary_map(string $classId): array
{
    $cacheKey='teacher_task_summary:'.$classId;
    if(isset($GLOBALS['educanet_runtime_indexes'][$cacheKey])&&is_array($GLOBALS['educanet_runtime_indexes'][$cacheKey]))return $GLOBALS['educanet_runtime_indexes'][$cacheKey];
    $out=[];$today=new DateTimeImmutable('today');
    foreach(teacher_task_rows() as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId)continue;
        $key=(string)($row['student_key']??'');if($key==='')continue;
        if(!isset($out[$key]))$out[$key]=['active'=>0,'done'=>0,'total'=>0,'overdue'=>0,'due_today'=>0,'due_3'=>0,'due_7'=>0,'no_due_active'=>0,'nearest_due'=>null,'nearest_due_days'=>null];
        $out[$key]['total']++;
        $status=(string)($row['status']??'assigned');
        if($status==='done'){$out[$key]['done']++;continue;}
        $out[$key]['active']++;
        $due=trim((string)($row['due_at']??''));
        if($due===''){$out[$key]['no_due_active']++;continue;}
        $dueDate=DateTimeImmutable::createFromFormat('!Y-m-d',$due);
        if(!$dueDate||$dueDate->format('Y-m-d')!==$due)continue;
        $days=(int)$today->diff($dueDate)->format('%r%a');
        if($days<0)$out[$key]['overdue']++;
        if($days===0)$out[$key]['due_today']++;
        if($days>=0&&$days<=3)$out[$key]['due_3']++;
        if($days>=0&&$days<=7)$out[$key]['due_7']++;
        $current=$out[$key]['nearest_due'];
        if($current===null||strcmp($due,(string)$current)<0){$out[$key]['nearest_due']=$due;$out[$key]['nearest_due_days']=$days;}
    }
    return $GLOBALS['educanet_runtime_indexes'][$cacheKey]=$out;
}

function teacher_task_count_map(string $classId): array
{
    $summary=teacher_task_summary_map($classId);$out=[];
    foreach($summary as $key=>$row)$out[$key]=['active'=>(int)($row['active']??0),'done'=>(int)($row['done']??0)];
    return $out;
}
function teacher_student_task_summary(string $classId,string $studentKey): array
{
    $map=teacher_task_summary_map($classId);
    return is_array($map[$studentKey]??null)?$map[$studentKey]:['active'=>0,'done'=>0,'total'=>0,'overdue'=>0,'due_today'=>0,'due_3'=>0,'due_7'=>0,'no_due_active'=>0,'nearest_due'=>null,'nearest_due_days'=>null];
}
function teacher_student_active_task_count(string $classId,string $studentKey): int
{
    $summary=teacher_student_task_summary($classId,$studentKey);return (int)($summary['active']??0);
}
function teacher_student_done_task_count(string $classId,string $studentKey): int
{
    $summary=teacher_student_task_summary($classId,$studentKey);return (int)($summary['done']??0);
}

function teacher_bulk_assign_task(string $classId,array $studentKeys,array $input): int
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může přiřazovat úkoly.');
    $students=project_students_for_class($classId);$targets=[];
    foreach($studentKeys as $key){$key=(string)$key;if(isset($students[$key]))$targets[$key]=true;}
    if(!$targets)throw new RuntimeException('Vyberte alespoň jednoho studenta.');

    $title=trim(u_substr((string)($input['task_title']??''),0,160));
    if($title==='')throw new RuntimeException('Doplňte název úkolu.');
    $instructions=trim(u_substr((string)($input['task_instructions']??''),0,1800));
    $priority=(string)($input['task_priority']??'normal');if(!in_array($priority,['high','normal','low'],true))$priority='normal';
    $dueAt=trim((string)($input['task_due_at']??''));
    if($dueAt!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$dueAt))throw new RuntimeException('Termín úkolu má neplatný formát.');
    $now=date(DATE_ATOM);$batch='tb_'.bin2hex(random_bytes(6));$new=[];
    $resourceType=(string)($input['resource_type']??'');if(!in_array($resourceType,['','material','lesson','lab','url'],true))$resourceType='';
    $resourceRef=trim(u_substr((string)($input['resource_ref']??''),0,500));
    $interventionId=trim(u_substr((string)($input['intervention_id']??''),0,80));
    foreach(array_keys($targets) as $studentKey){
        $new[]=['id'=>'tt_'.bin2hex(random_bytes(7)),'batch_id'=>$batch,'class_id'=>$classId,'student_key'=>$studentKey,'title'=>$title,'instructions'=>$instructions,'priority'=>$priority,'due_at'=>$dueAt!==''?$dueAt:null,'status'=>'assigned','assigned_by'=>teacher_display_name(),'created_at'=>$now,'completed_at'=>null,'resource_type'=>$resourceType?:null,'resource_ref'=>$resourceRef?:null,'intervention_id'=>$interventionId?:null,'reminder_count'=>0,'last_reminded_at'=>null];
    }
    storage_update(teacher_tasks_path(),static fn(array $rows): array => array_merge(array_values($rows),$new));
    return count($targets);
}

function teacher_student_task_set_status(string $taskId,string $classId,string $studentKey,string $status): void
{
    if(!in_array($status,['assigned','done'],true))throw new RuntimeException('Neplatný stav úkolu.');
    $now=date(DATE_ATOM);
    storage_update(teacher_tasks_path(),static function(array $rows) use($taskId,$classId,$studentKey,$status,$now): array {$found=false;
    foreach($rows as &$row){
        if(!is_array($row)||(string)($row['id']??'')!==$taskId)continue;
        if((string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)throw new RuntimeException('Tento úkol nepatří přihlášenému studentovi.');
        $row['status']=$status;$row['completed_at']=$status==='done'?$now:null;$found=true;break;
    }
    unset($row);
    if(!$found)throw new RuntimeException('Úkol nebyl nalezen.');
    return $rows;});
}

function teacher_task_priority_label(string $priority): string
{
    return match($priority){'high'=>'Vysoká','low'=>'Nízká',default=>'Běžná'};
}

function teacher_saved_filters_path(): string { return STORAGE_DIR . '/teacher_saved_filters.json.php'; }

function teacher_saved_filter_actor_token(): string
{
    $sessionToken=(string)($_SESSION['teacher_saved_filter_actor_token']??'');
    if(preg_match('/^[a-f0-9]{64}$/D',$sessionToken))return $sessionToken;
    $cookieToken=(string)($_COOKIE['educanet_teacher_actor']??'');
    if(preg_match('/^[a-f0-9]{64}$/D',$cookieToken)){$_SESSION['teacher_saved_filter_actor_token']=$cookieToken;return $cookieToken;}
    $token=bin2hex(random_bytes(32));$_SESSION['teacher_saved_filter_actor_token']=$token;
    if(PHP_SAPI!=='cli'&&!headers_sent())setcookie('educanet_teacher_actor',$token,[
        'expires'=>time()+31536000,'path'=>'/','secure'=>educanet_is_https(),'httponly'=>true,'samesite'=>'Strict',
    ]);
    return $token;
}

function teacher_saved_filter_legacy_owner_key(): string
{
    return hash('sha256', normalized_person_name(teacher_display_name()));
}

/** v59: v režimu účtů je vlastník z id účtu (v3); bez platné relace klíč, který nic nevlastní; legacy = v2 (token prohlížeče + jméno). */
function teacher_saved_filter_accounts_mode(): bool
{
    return function_exists('teacher59_mode')&&teacher59_mode()!=='legacy';
}

function teacher_saved_filter_owner_key(): string
{
    if(teacher_saved_filter_accounts_mode()){
        static $none=null;
        $v3=function_exists('teacher59_owner_key')?teacher59_owner_key():null;
        return $v3??($none??=hash('sha256','v3|none|'.bin2hex(random_bytes(16))));
    }
    return hash('sha256','v2|'.teacher_saved_filter_actor_token().'|'.normalized_person_name(teacher_display_name()));
}

function teacher_saved_filter_owner_label(): string
{
    $label=trim(u_substr(teacher_display_name(),0,80));
    return $label!==''?$label:'Učitel';
}

function teacher_team_key(): string
{
    $configured=trim(educanet_secret('teacher_team_id','educanet-teachers'));
    if($configured==='')$configured='educanet-teachers';
    return hash('sha256','team|'.normalized_person_name($configured));
}

function teacher_team_label(): string
{
    $label=trim(u_substr(educanet_secret('teacher_team_name','Učitelský tým'),0,80));
    return $label!==''?$label:'Učitelský tým';
}

function teacher_saved_filter_scope(string $scope): string
{
    return $scope==='team'?'team':'personal';
}

function teacher_saved_filter_scope_label(string $scope): string
{
    return teacher_saved_filter_scope($scope)==='team'?'Týmový':'Osobní';
}

function teacher_saved_filter_status(string $status): string
{
    return in_array($status,['all','attention','returned','ok','no_result'],true) ? $status : 'all';
}

function teacher_saved_filter_priority(string $priority): string
{
    return in_array($priority,['all','high','medium','normal'],true) ? $priority : 'all';
}

function teacher_saved_filter_task_status(string $status): string
{
    return in_array($status,['all','active','done','no_task'],true) ? $status : 'all';
}

function teacher_saved_filter_task_due(string $due): string
{
    return in_array($due,['all','overdue','today','next3','next7','no_due'],true) ? $due : 'all';
}

function teacher_saved_filter_status_label(string $status): string
{
    return match(teacher_saved_filter_status($status)){
        'attention'=>'Prověřit','returned'=>'Vráceno','ok'=>'V pořádku','no_result'=>'Bez výsledku',default=>'Všechny stavy',
    };
}

function teacher_saved_filter_priority_label(string $priority): string
{
    return match(teacher_saved_filter_priority($priority)){
        'high'=>'Vysoká','medium'=>'Střední','normal'=>'Běžná',default=>'Všechny priority',
    };
}

function teacher_saved_filter_task_status_label(string $status): string
{
    return match(teacher_saved_filter_task_status($status)){
        'active'=>'Aktivní úkol','done'=>'Dokončený úkol','no_task'=>'Bez úkolu',default=>'Všechny úkoly',
    };
}

function teacher_saved_filter_task_due_label(string $due): string
{
    return match(teacher_saved_filter_task_due($due)){
        'overdue'=>'Po termínu','today'=>'Termín dnes','next3'=>'Do 3 dnů','next7'=>'Do 7 dnů','no_due'=>'Bez termínu',default=>'Všechny termíny',
    };
}

function teacher_task_summary_matches(array $summary,string $taskStatus,string $taskDue): bool
{
    $taskStatus=teacher_saved_filter_task_status($taskStatus);$taskDue=teacher_saved_filter_task_due($taskDue);
    $active=(int)($summary['active']??0);$done=(int)($summary['done']??0);$total=(int)($summary['total']??($active+$done));
    $statusMatch=match($taskStatus){'active'=>$active>0,'done'=>$done>0,'no_task'=>$total===0,default=>true};
    if(!$statusMatch)return false;
    return match($taskDue){
        'overdue'=>(int)($summary['overdue']??0)>0,
        'today'=>(int)($summary['due_today']??0)>0,
        'next3'=>(int)($summary['due_3']??0)>0,
        'next7'=>(int)($summary['due_7']??0)>0,
        'no_due'=>(int)($summary['no_due_active']??0)>0,
        default=>true,
    };
}

function teacher_task_deadline_label(array $summary): string
{
    if((int)($summary['overdue']??0)>0)return (int)$summary['overdue'].' po termínu';
    if((int)($summary['due_today']??0)>0)return 'termín dnes';
    $days=$summary['nearest_due_days']??null;
    if(is_int($days)||is_numeric($days)){
        $days=(int)$days;
        if($days>0&&$days<=7)return 'nejbližší za '.$days.' d';
    }
    if((int)($summary['no_due_active']??0)>0&&empty($summary['nearest_due']))return 'bez termínu';
    $due=(string)($summary['nearest_due']??'');
    return $due!==''?date('d.m.Y',strtotime($due)):'bez aktivního termínu';
}

function teacher_saved_filter_rows(): array
{
    $rows=load_php_json(teacher_saved_filters_path());
    return is_array($rows)?$rows:[];
}

function teacher_saved_filter_rule_field(string $field): string
{
    return in_array($field,['status','priority','task_status','task_due','mastery','avg_grade','inactive_days','score_drop','marker','active_tasks','returned','published','tests','labs'],true)?$field:'';
}
function teacher_saved_filter_rule_op(string $op): string
{
    return in_array($op,['eq','neq','gt','gte','lt','lte'],true)?$op:'';
}
function teacher_saved_filter_default_for(string $value): string
{
    return in_array($value,['none','overview','results','both'],true)?$value:'none';
}
function teacher_saved_filter_rules_from_input(array $input): array
{
    $fields=is_array($input['rule_field']??null)?$input['rule_field']:[];$ops=is_array($input['rule_op']??null)?$input['rule_op']:[];$values=is_array($input['rule_value']??null)?$input['rule_value']:[];$out=[];
    for($i=0;$i<min(8,max(count($fields),count($ops),count($values)));$i++){$field=teacher_saved_filter_rule_field((string)($fields[$i]??''));$op=teacher_saved_filter_rule_op((string)($ops[$i]??''));$value=u_substr(trim((string)($values[$i]??'')),0,120);if($field!==''&&$op!==''&&$value!=='')$out[]=['field'=>$field,'op'=>$op,'value'=>$value];}
    return $out;
}
function teacher_saved_filter_rule_value(array $row,string $field): mixed
{
    $activity=is_array($row['activity']??null)?$row['activity']:[];$task=is_array($row['task_summary']??null)?$row['task_summary']:[];
    return match($field){
        'status'=>(string)($row['status_key']??teacher_student_result_status($row)),
        'priority'=>(string)($row['priority']??'normal'),
        'task_status'=>(int)($task['active']??0)>0?'active':((int)($task['done']??0)>0?'done':'no_task'),
        'task_due'=>(int)($task['overdue']??0)>0?'overdue':((int)($task['due_today']??0)>0?'today':((int)($task['due_3']??0)>0?'next3':((int)($task['due_7']??0)>0?'next7':((int)($task['no_due_active']??0)>0?'no_due':'all')))),
        'mastery'=>$row['mastery']??null,'avg_grade'=>$row['avg_grade']??null,'inactive_days'=>$row['inactive_days']??null,'score_drop'=>$row['score_drop']??null,'marker'=>(string)($row['marker']??'none'),'active_tasks'=>(int)($task['active']??0),'returned'=>(int)($row['returned']??0),'published'=>(int)($row['published']??0),'tests'=>(int)($activity['tests']??0),'labs'=>(int)($activity['labs']??0),default=>null,
    };
}
function teacher_saved_filter_rule_match(array $row,array $rule): bool
{
    $field=teacher_saved_filter_rule_field((string)($rule['field']??''));$op=teacher_saved_filter_rule_op((string)($rule['op']??''));if($field===''||$op==='')return true;$actual=teacher_saved_filter_rule_value($row,$field);$expected=(string)($rule['value']??'');
    $numeric=in_array($field,['mastery','avg_grade','inactive_days','score_drop','active_tasks','returned','published','tests','labs'],true);
    if($numeric){if($actual===null||!is_numeric($expected))return false;$a=(float)$actual;$b=(float)$expected;return match($op){'eq'=>abs($a-$b)<.0001,'neq'=>abs($a-$b)>=.0001,'gt'=>$a>$b,'gte'=>$a>=$b,'lt'=>$a<$b,'lte'=>$a<=$b,default=>true};}
    $a=(string)$actual;$b=$expected;return match($op){'eq'=>$a===$b,'neq'=>$a!==$b,default=>false};
}
function teacher_saved_filter_matches_student(array $filter,array $row): bool
{
    $filter=teacher_saved_filter_normalize_row($filter);$status=(string)$filter['status'];$priority=(string)$filter['priority'];$taskStatus=(string)$filter['task_status'];$taskDue=(string)$filter['task_due'];$rowStatus=(string)($row['status_key']??teacher_student_result_status($row));
    if($status!=='all'&&($status==='attention'?!empty($row['attention']):$rowStatus===$status)===false)return false;
    if($priority!=='all'&&(string)($row['priority']??'normal')!==$priority)return false;
    if(!teacher_task_summary_matches((array)($row['task_summary']??[]),$taskStatus,$taskDue))return false;
    $rules=(array)$filter['rules'];if(!$rules)return true;$matches=array_map(static fn($rule):bool=>teacher_saved_filter_rule_match($row,(array)$rule),$rules);return (string)$filter['rule_mode']==='any'?in_array(true,$matches,true):!in_array(false,$matches,true);
}
function teacher_saved_filter_find_visible(string $id): ?array
{
    foreach(teacher_saved_filter_rows() as $raw){if(!is_array($raw)||(string)($raw['id']??'')!==$id||!teacher_saved_filter_visible_to_current_teacher($raw))continue;$row=teacher_saved_filter_normalize_row($raw);$row['is_owner']=teacher_saved_filter_is_owner($raw);return $row;}return null;
}
function teacher_saved_filter_default(string $tab,string $classId): ?array
{
    $needle=$tab==='class_overview'?'overview':'results';foreach(teacher_saved_filters_for_current_teacher() as $row){if((string)($row['scope']??'personal')!=='personal'||(string)($row['class_id']??'')!==$classId)continue;$d=teacher_saved_filter_default_for((string)($row['default_for']??'none'));if($d==='both'||$d===$needle)return $row;}return null;
}
function teacher_saved_filter_set_pin(string $id,bool $pinned): void
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může měnit filtry.');storage_update(teacher_saved_filters_path(),static function(array $rows) use($id,$pinned): array {$found=false;foreach($rows as &$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if(!teacher_saved_filter_is_owner($row))throw new RuntimeException('Můžete upravovat pouze vlastní uložené filtry.');$row['pinned']=$pinned;$row['updated_at']=date(DATE_ATOM);$found=true;break;}unset($row);if(!$found)throw new RuntimeException('Uložený filtr nebyl nalezen.');return array_values($rows);});
}
function teacher_saved_filter_set_default(string $id,string $defaultFor): void
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může měnit filtry.');$defaultFor=teacher_saved_filter_default_for($defaultFor);storage_update(teacher_saved_filters_path(),static function(array $rows) use($id,$defaultFor): array {$target=null;foreach($rows as $i=>$row){if(is_array($row)&&(string)($row['id']??'')===$id){if(!teacher_saved_filter_is_owner($row))throw new RuntimeException('Můžete upravovat pouze vlastní uložené filtry.');if(teacher_saved_filter_scope((string)($row['scope']??'personal'))!=='personal')throw new RuntimeException('Výchozí filtr musí být osobní.');$target=$i;break;}}if($target===null)throw new RuntimeException('Uložený filtr nebyl nalezen.');$classId=(string)($rows[$target]['class_id']??'');$owner=(string)($rows[$target]['owner_key']??'');foreach($rows as &$row){if(!is_array($row)||(string)($row['owner_key']??'')!==$owner||(string)($row['class_id']??'')!==$classId)continue;$existing=teacher_saved_filter_default_for((string)($row['default_for']??'none'));if($defaultFor==='overview'&&in_array($existing,['overview','both'],true))$row['default_for']=$existing==='both'?'results':'none';elseif($defaultFor==='results'&&in_array($existing,['results','both'],true))$row['default_for']=$existing==='both'?'overview':'none';elseif($defaultFor==='both')$row['default_for']='none';}unset($row);$rows[$target]['default_for']=$defaultFor;$rows[$target]['updated_at']=date(DATE_ATOM);return array_values($rows);});
}

function teacher_saved_filter_normalize_row(array $row): array
{
    $row['scope']=teacher_saved_filter_scope((string)($row['scope']??'personal'));
    $row['status']=teacher_saved_filter_status((string)($row['status']??'all'));
    $row['priority']=teacher_saved_filter_priority((string)($row['priority']??'all'));
    $row['task_status']=teacher_saved_filter_task_status((string)($row['task_status']??'all'));
    $row['task_due']=teacher_saved_filter_task_due((string)($row['task_due']??'all'));
    $ruleMode=(string)($row['rule_mode']??'all');$row['rule_mode']=in_array($ruleMode,['all','any'],true)?$ruleMode:'all';
    $rules=[];foreach((array)($row['rules']??[]) as $rule)if(is_array($rule)){$field=teacher_saved_filter_rule_field((string)($rule['field']??''));$op=teacher_saved_filter_rule_op((string)($rule['op']??''));if($field!==''&&$op!=='')$rules[]=['field'=>$field,'op'=>$op,'value'=>u_substr(trim((string)($rule['value']??'')),0,120)];}$row['rules']=array_slice($rules,0,8);
    $row['pinned']=(bool)($row['pinned']??false);
    $row['default_for']=teacher_saved_filter_default_for((string)($row['default_for']??'none'));
    $row['owner_label']=trim((string)($row['owner_label']??''));
    $row['team_key']=trim((string)($row['team_key']??''));
    return $row;
}

function teacher_saved_filter_is_owner(array $row): bool
{
    $stored=(string)($row['owner_key']??'');if($stored==='')return false;
    $version=(int)($row['owner_version']??1);
    // v59: v režimu účtů patří filtr jen s klíčem účtu (owner_version 3 – nový nebo převzatý v záložce Můj účet).
    if(teacher_saved_filter_accounts_mode()&&$version<3)return false;
    $expected=$version>=2?teacher_saved_filter_owner_key():teacher_saved_filter_legacy_owner_key();
    return hash_equals($stored,$expected);
}

function teacher_saved_filter_visible_to_current_teacher(array $row): bool
{
    $row=teacher_saved_filter_normalize_row($row);
    if(function_exists('teacher59_can_class')&&!teacher59_can_class((string)($row['class_id']??'')))return false; // v59: jen třídy v rozsahu
    if((string)$row['scope']==='personal')return teacher_saved_filter_is_owner($row);
    $teamKey=(string)($row['team_key']??'');
    return $teamKey!==''&&hash_equals($teamKey,teacher_team_key());
}

function teacher_saved_filters_for_current_teacher(): array
{
    $owner=teacher_saved_filter_owner_key();$out=[];
    foreach(teacher_saved_filter_rows() as $raw){
        if(!is_array($raw))continue;
        $row=teacher_saved_filter_normalize_row($raw);
        $classId=(string)($row['class_id']??'');
        if(!isset(project_catalog()[$classId]))continue;
        if(!teacher_saved_filter_visible_to_current_teacher($row))continue;
        $row['is_owner']=teacher_saved_filter_is_owner($row);
        $out[]=$row;
    }
    usort($out,static function(array $a,array $b):int{
        $scopeRank=['personal'=>0,'team'=>1];
        $scope=($scopeRank[(string)($a['scope']??'personal')]??9)<=>($scopeRank[(string)($b['scope']??'personal')]??9);if($scope!==0)return $scope;
        $pin=(int)!empty($b['pinned'])<=>(int)!empty($a['pinned']);if($pin!==0)return $pin;
        return strnatcasecmp((string)($a['name']??''),(string)($b['name']??''));
    });
    return $out;
}

function teacher_saved_filter_save(array $input): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může ukládat filtry.');
    $catalog=project_catalog();$classId=(string)($input['class_id']??'class_2a');
    if(!isset($catalog[$classId]))throw new RuntimeException('Vyberte platnou třídu.');
    if(function_exists('teacher59_can_class')&&!teacher59_can_class($classId))throw new RuntimeException('K této třídě nemáte přístup.');
    $name=trim(u_substr((string)($input['filter_name']??''),0,80));
    if($name==='')throw new RuntimeException('Doplňte název uloženého filtru.');
    $scope=teacher_saved_filter_scope((string)($input['filter_scope']??'personal'));
    $status=teacher_saved_filter_status((string)($input['filter_status']??$input['status']??'all'));
    $priority=teacher_saved_filter_priority((string)($input['filter_priority']??$input['priority']??'all'));
    $taskStatus=teacher_saved_filter_task_status((string)($input['filter_task_status']??$input['task_status']??'all'));
    $taskDue=teacher_saved_filter_task_due((string)($input['filter_task_due']??$input['task_due']??'all'));
    $ruleMode=in_array((string)($input['filter_rule_mode']??$input['rule_mode']??'all'),['all','any'],true)?(string)($input['filter_rule_mode']??$input['rule_mode']??'all'):'all';
    $rules=teacher_saved_filter_rules_from_input($input);$pinned=!empty($input['filter_pinned']);$defaultFor=teacher_saved_filter_default_for((string)($input['filter_default_for']??'none'));if($scope==='team')$defaultFor='none';
    $owner=teacher_saved_filter_owner_key();$team=$scope==='team'?teacher_team_key():'';$now=date(DATE_ATOM);$row=[];
    storage_update(teacher_saved_filters_path(),static function(array $rows) use($scope,$team,$name,$owner,$classId,$status,$priority,$taskStatus,$taskDue,$ruleMode,$rules,$input,$pinned,$defaultFor,$now,&$row): array {$match=null;
    foreach($rows as $i=>$raw){
        if(!is_array($raw)||!teacher_saved_filter_is_owner($raw))continue;
        $existingScope=teacher_saved_filter_scope((string)($raw['scope']??'personal'));
        if($existingScope!==$scope)continue;
        if($scope==='team' && !hash_equals((string)($raw['team_key']??''),$team))continue;
        if(normalized_person_name((string)($raw['name']??''))===normalized_person_name($name)){$match=$i;break;}
    }
    $base=$match!==null&&is_array($rows[$match])?$rows[$match]:[];
    $row=[
        'id'=>(string)($base['id']??('sf_'.bin2hex(random_bytes(6)))),
        'owner_key'=>$owner,
        'owner_version'=>teacher_saved_filter_accounts_mode()?3:2,
        'owner_label'=>teacher_saved_filter_owner_label(),
        'scope'=>$scope,
        'team_key'=>$team,
        'name'=>$name,
        'class_id'=>$classId,
        'status'=>$status,
        'priority'=>$priority,
        'task_status'=>$taskStatus,
        'task_due'=>$taskDue,
        'rule_mode'=>$ruleMode,'rules'=>$rules,
        'pinned'=>array_key_exists('filter_pinned',$input)?$pinned:(bool)($base['pinned']??false),
        'default_for'=>$defaultFor!=='none'?$defaultFor:teacher_saved_filter_default_for((string)($base['default_for']??'none')),
        'created_at'=>(string)($base['created_at']??$now),
        'updated_at'=>$now,
    ];
    if($match!==null)$rows[$match]=$row;else{
        $owned=0;foreach($rows as $existing){
            if(!is_array($existing)||!teacher_saved_filter_is_owner($existing))continue;
            $existingScope=teacher_saved_filter_scope((string)($existing['scope']??'personal'));
            if($existingScope===$scope)$owned++;
        }
        if($owned>=24)throw new RuntimeException('Máte uložený maximální počet 24 '.($scope==='team'?'vlastních týmových':'osobních').' filtrů. Některý nejdřív smažte.');
        $rows[]=$row;
    }
    return array_values($rows);});
    return $row;
}

function teacher_saved_filter_delete(string $id): void
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může mazat filtry.');
    $owner=teacher_saved_filter_owner_key();storage_update(teacher_saved_filters_path(),static function(array $rows) use($id): array {$found=false;
    foreach($rows as $i=>$row){
        if(!is_array($row)||(string)($row['id']??'')!==$id)continue;
        if(!teacher_saved_filter_is_owner($row))throw new RuntimeException('Můžete mazat pouze vlastní uložené filtry.');
        unset($rows[$i]);$found=true;break;
    }
    if(!$found)throw new RuntimeException('Uložený filtr nebyl nalezen.');
    return array_values($rows);});
}

function teacher_saved_filter_match(array $row,string $classId,string $status,string $priority,string $taskStatus='all',string $taskDue='all'): bool
{
    return (string)($row['class_id']??'')===$classId
        && teacher_saved_filter_status((string)($row['status']??'all'))===teacher_saved_filter_status($status)
        && teacher_saved_filter_priority((string)($row['priority']??'all'))===teacher_saved_filter_priority($priority)
        && teacher_saved_filter_task_status((string)($row['task_status']??'all'))===teacher_saved_filter_task_status($taskStatus)
        && teacher_saved_filter_task_due((string)($row['task_due']??'all'))===teacher_saved_filter_task_due($taskDue);
}
