<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** EDUCANET v46.2 · Control Tower, SLA, reporting and data quality. */
function teacher_ops_review_snapshots_path(): string { return STORAGE_DIR.'/teacher_review_snapshots.json.php'; }
function teacher_ops_message_templates_path(): string { return STORAGE_DIR.'/teacher_message_templates.json.php'; }
function teacher_ops_sla_policy_path(): string { return STORAGE_DIR.'/teacher_sla_policy.json.php'; }
function teacher_ops_class_label(string $classId): string { return function_exists('teacher_class_label')?teacher_class_label($classId):match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId}; }

/** v59: třída v rozsahu učitele (legacy/CLI bez modulu rozsahu = vždy true). */
function teacher_ops_class_in_scope(string $classId): bool { return !function_exists('teacher59_can_class')||teacher59_can_class($classId); }

function teacher_ops_review_metrics(?string $classId=null): array
{
    $attention=teacher_ops_attention_center($classId);
    $classes=$classId!==null?[teacher_ops_valid_class($classId)]:teacher_ops_class_ids();
    $health=[];$healthAvg=[];
    foreach($classes as $cid){$h=teacher_ops_class_health($cid);$health[$cid]=['score'=>$h['score'],'status'=>$h['status']];if((string)$h['status']!=='empty')$healthAvg[]=(float)$h['score'];}
    $followups=teacher_ops_followups_visible($classId,false);$today=date('Y-m-d');$followupOverdue=0;$followupToday=0;
    foreach($followups as $row){$due=(string)($row['due_at']??'');if($due!==''&&$due<$today)$followupOverdue++;elseif($due===$today)$followupToday++;}
    return [
        'critical'=>(int)$attention['critical'],'deadlines'=>(int)$attention['deadlines'],'drops'=>(int)$attention['drops'],
        'waiting_teacher'=>(int)$attention['waiting_teacher'],'waiting_student'=>(int)$attention['waiting_student'],'resolved_recent'=>(int)$attention['resolved_recent'],
        'followup_overdue'=>$followupOverdue,'followup_today'=>$followupToday,'health_avg'=>$healthAvg?round(array_sum($healthAvg)/count($healthAvg),1):null,'health'=>$health,
    ];
}
function teacher_ops_review_snapshot_latest(?string $classId=null): ?array
{
    $owner=teacher_saved_filter_owner_key();$scope=$classId===null?'all':teacher_ops_valid_class($classId);$latest=null;
    foreach(teacher_ops_rows(teacher_ops_review_snapshots_path()) as $row){if(!is_array($row)||(string)($row['owner_key']??'')!==$owner||(string)($row['scope']??'all')!==$scope)continue;if($latest===null||strcmp((string)($row['created_at']??''),(string)($latest['created_at']??''))>0)$latest=$row;}
    return $latest;
}
function teacher_ops_review_digest(?string $classId=null): array
{
    $current=teacher_ops_review_metrics($classId);$previous=teacher_ops_review_snapshot_latest($classId);$deltas=[];
    foreach(['critical','deadlines','drops','waiting_teacher','waiting_student','resolved_recent','followup_overdue','followup_today','health_avg'] as $key){$now=$current[$key]??null;$before=is_array($previous)?($previous['metrics'][$key]??null):null;$deltas[$key]=($now!==null&&$before!==null&&is_numeric($now)&&is_numeric($before))?round((float)$now-(float)$before,1):null;}
    return ['scope'=>$classId===null?'all':teacher_ops_valid_class($classId),'current'=>$current,'previous'=>$previous,'deltas'=>$deltas];
}
function teacher_ops_review_ack(?string $classId=null): array
{
    teacher_require_permission('view');$scope=$classId===null?'all':teacher_ops_valid_class($classId);$row=['id'=>teacher_ops_id('rv'),'scope'=>$scope,'metrics'=>teacher_ops_review_metrics($classId),'owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'team_key'=>teacher_team_key(),'created_at'=>date(DATE_ATOM)];
    $cut=time()-120*86400;teacher_ops_update_rows(teacher_ops_review_snapshots_path(),static function(array $rows) use($row,$cut): array {$rows[]=$row;return array_values(array_filter($rows,static fn($r)=>is_array($r)&&(strtotime((string)($r['created_at']??''))?:0)>=$cut));});teacher_ops_audit_event('review.ack','review',$row['id'],$scope==='all'?'':$scope,'Stav administrace označen jako zkontrolovaný');return $row;
}

function teacher_ops_sla_policy(): array
{
    $defaults=['task_high'=>3,'task_critical'=>7,'followup_critical'=>5,'intervention_stale'=>7,'watchlist_high'=>3,'watchlist_critical'=>7];$team=teacher_team_key();foreach(teacher_ops_rows(teacher_ops_sla_policy_path()) as $row){if(!is_array($row)||(string)($row['team_key']??'')!==$team)continue;foreach($defaults as $key=>$value)$defaults[$key]=max(1,min(60,(int)($row[$key]??$value)));break;}return $defaults;
}
function teacher_ops_sla_policy_save(array $input): array
{
    teacher_require_permission('roles.manage');if(function_exists('teacher59_is_admin')&&!teacher59_is_admin())throw new RuntimeException('SLA politiku mění jen administrátor.');$policy=teacher_ops_sla_policy();foreach(array_keys($policy) as $key)$policy[$key]=max(1,min(60,(int)($input['sla_'.$key]??$policy[$key])));if($policy['task_critical']<$policy['task_high'])$policy['task_critical']=$policy['task_high'];if($policy['watchlist_critical']<$policy['watchlist_high'])$policy['watchlist_critical']=$policy['watchlist_high'];$team=teacher_team_key();$row=$policy+['team_key'=>$team,'team_label'=>teacher_team_label(),'updated_at'=>date(DATE_ATOM),'updated_by'=>teacher_display_name()];teacher_ops_update_rows(teacher_ops_sla_policy_path(),static function(array $rows) use($team,$row): array {$found=null;foreach($rows as $i=>$r)if(is_array($r)&&(string)($r['team_key']??'')===$team){$found=$i;break;}if($found===null)$rows[]=$row;else$rows[$found]=$row;return $rows;});teacher_ops_audit_event('sla.update','sla_policy',$team,'','SLA politika učitelského týmu aktualizována',$policy);return $row;
}

function teacher_ops_escalations(?string $classId=null): array
{
    $policy=teacher_ops_sla_policy();$classes=$classId!==null?[teacher_ops_valid_class($classId)]:teacher_ops_class_ids();$today=new DateTimeImmutable('today');$out=[];
    foreach($classes as $cid){$students=project_students_for_class($cid);foreach(teacher_task_rows() as $task){if(!is_array($task)||(string)($task['class_id']??'')!==$cid||(string)($task['status']??'assigned')!=='assigned')continue;$due=(string)($task['due_at']??'');if($due==='')continue;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$due);if(!$d)continue;$days=(int)$d->diff($today)->format('%r%a');if($days<(int)$policy['task_high'])continue;$key=(string)($task['student_key']??'');$out[]=['severity'=>$days>=(int)$policy['task_critical']?'critical':'high','kind'=>'task','class_id'=>$cid,'student_key'=>$key,'student_label'=>(string)($students[$key]['label']??$key),'title'=>'Úkol '.$days.' dní po termínu','detail'=>(string)($task['title']??'Úkol'),'days'=>$days,'due_at'=>$due,'url'=>teacher_ops_student_url($cid,$key)];}
        foreach(teacher_ops_followups_visible($cid,false) as $fu){$due=(string)($fu['due_at']??'');if($due===''||$due>=date('Y-m-d'))continue;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$due);$days=$d?(int)$d->diff($today)->format('%r%a'):0;if($days<1)continue;$key=(string)($fu['student_key']??'');$out[]=['severity'=>$days>=(int)$policy['followup_critical']?'critical':'high','kind'=>'followup','class_id'=>$cid,'student_key'=>$key,'student_label'=>$key!==''?(string)($students[$key]['label']??$key):teacher_ops_class_label($cid),'title'=>'Follow-up '.$days.' dní po termínu','detail'=>(string)($fu['title']??'Follow-up'),'days'=>$days,'due_at'=>$due,'url'=>$key!==''?teacher_ops_student_url($cid,$key):'teacher.php?tab=attention&class='.rawurlencode($cid)];}
        foreach(teacher_ops_intervention_rows() as $iv){if(!is_array($iv)||(string)($iv['class_id']??'')!==$cid||!in_array((string)($iv['status']??''),['active','review'],true))continue;$due=(string)($iv['review_due']??'');$updated=strtotime((string)($iv['updated_at']??$iv['created_at']??''))?:0;$stale=$updated>0?(int)floor((time()-$updated)/86400):0;$overdue=$due!==''&&$due<date('Y-m-d');if(!$overdue&&$stale<(int)$policy['intervention_stale'])continue;$out[]=['severity'=>($overdue&&$stale>=(int)$policy['intervention_stale'])?'critical':'high','kind'=>'intervention','class_id'=>$cid,'student_key'=>'','student_label'=>count((array)($iv['student_keys']??[])).' studentů','title'=>$overdue?'Intervence čeká na review':'Intervence bez aktualizace','detail'=>(string)($iv['title']??'Intervence'),'days'=>$stale,'due_at'=>$due?:null,'url'=>'teacher.php?tab=interventions&class='.rawurlencode($cid).'&intervention='.rawurlencode((string)($iv['id']??''))];}
        foreach(teacher_ops_watchlist_visible($cid) as $wl){if((string)($wl['priority']??'normal')!=='high')continue;$created=strtotime((string)($wl['updated_at']??$wl['created_at']??''))?:0;$days=$created>0?(int)floor((time()-$created)/86400):0;if($days<(int)$policy['watchlist_high'])continue;$key=(string)($wl['student_key']??'');$out[]=['severity'=>$days>=(int)$policy['watchlist_critical']?'critical':'high','kind'=>'watchlist','class_id'=>$cid,'student_key'=>$key,'student_label'=>(string)($students[$key]['label']??$key),'title'=>'High-priority watchlist '.$days.' dní','detail'=>(string)($wl['reason']??''),'days'=>$days,'due_at'=>null,'url'=>teacher_ops_student_url($cid,$key)];}
    }
    usort($out,static function($a,$b){$rank=['critical'=>2,'high'=>1];$r=($rank[(string)$b['severity']]??0)<=>($rank[(string)$a['severity']]??0);return $r!==0?$r:((int)($b['days']??0)<=>(int)($a['days']??0));});return $out;
}

function teacher_ops_planner(int $days=14): array
{
    $days=max(1,min(31,$days));$today=new DateTimeImmutable('today');$start=$today->modify('-7 days');$end=$today->modify('+'.$days.' days');$events=[];
    foreach(teacher_ops_followups_visible(null,false) as $row){$due=(string)($row['due_at']??'');if($due==='')continue;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$due);if(!$d||$d<$start||$d>$end)continue;$events[]=['date'=>$due,'type'=>'followup','class_id'=>(string)$row['class_id'],'title'=>(string)$row['title'],'detail'=>(string)($row['student_key']??''),'priority'=>(string)($row['priority']??'normal'),'url'=>'teacher.php?tab=attention&class='.rawurlencode((string)$row['class_id'])];}
    foreach(teacher_ops_intervention_rows() as $row){if(!is_array($row)||!in_array((string)($row['status']??''),['active','review'],true)||!teacher_ops_intervention_visible($row))continue;$due=(string)($row['review_due']??'');if($due==='')continue;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$due);if(!$d||$d<$start||$d>$end)continue;$events[]=['date'=>$due,'type'=>'intervention','class_id'=>(string)$row['class_id'],'title'=>(string)$row['title'],'detail'=>count((array)($row['student_keys']??[])).' studentů','priority'=>'normal','url'=>'teacher.php?tab=interventions&class='.rawurlencode((string)$row['class_id']).'&intervention='.rawurlencode((string)$row['id'])];}
    $taskBuckets=[];foreach(teacher_task_rows() as $task){if(!is_array($task)||(string)($task['status']??'assigned')!=='assigned')continue;$due=(string)($task['due_at']??'');if($due==='')continue;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$due);if(!$d||$d<$start||$d>$end)continue;$cid=(string)($task['class_id']??'');if(!teacher_ops_class_in_scope($cid))continue;$bucket=$due.'|'.$cid;if(!isset($taskBuckets[$bucket]))$taskBuckets[$bucket]=['date'=>$due,'type'=>'tasks','class_id'=>$cid,'title'=>'Termíny studentských úkolů','detail'=>0,'priority'=>'normal','url'=>'teacher.php?tab=class_results&class='.rawurlencode($cid).'&task_status=active'];$taskBuckets[$bucket]['detail']++;}
    foreach($taskBuckets as $row){$row['detail']=(int)$row['detail'].' úkolů';$events[]=$row;}
    usort($events,static fn($a,$b)=>strcmp((string)$a['date'],(string)$b['date']));$byDate=[];foreach($events as $event)$byDate[(string)$event['date']][]=$event;return ['days'=>$days,'from'=>$today->format('Y-m-d'),'to'=>$end->format('Y-m-d'),'events'=>$events,'by_date'=>$byDate];
}

function teacher_ops_message_template_rows(): array { return teacher_ops_rows(teacher_ops_message_templates_path()); }
function teacher_ops_message_templates_visible(): array
{
    $out=[];foreach(teacher_ops_message_template_rows() as $row){if(is_array($row)&&teacher_ops_owner_visible($row))$out[]=$row;}usort($out,static fn($a,$b)=>strcmp((string)($a['name']??''),(string)($b['name']??'')));return $out;
}
function teacher_ops_message_template_save(array $input): array
{
    teacher_require_permission('templates.manage');$name=trim(u_substr((string)($input['message_template_name']??''),0,120));$body=trim(u_substr((string)($input['message_template_body']??''),0,4000));if($name===''||$body==='')throw new RuntimeException('Doplňte název a text komunikační šablony.');$scope=teacher_ops_scope((string)($input['message_template_scope']??'personal'));$row=['id'=>teacher_ops_id('mt'),'name'=>$name,'body'=>$body,'scope'=>$scope,'team_key'=>$scope==='team'?teacher_team_key():'','owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'created_at'=>date(DATE_ATOM),'updated_at'=>date(DATE_ATOM)];storage_list_push(teacher_ops_message_templates_path(),$row);teacher_ops_audit_event('message_template.create','message_template',$row['id'],'','Komunikační šablona vytvořena',['scope'=>$scope]);return $row;
}
function teacher_ops_message_template_delete(string $id): void
{
    teacher_require_permission('templates.manage');$found=null;teacher_ops_update_rows(teacher_ops_message_templates_path(),static function(array $rows) use($id,&$found): array {foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if(!teacher_ops_is_owner($row))throw new RuntimeException('Můžete smazat pouze vlastní komunikační šablonu.');$found=$row;unset($rows[$i]);break;}if(!$found)throw new RuntimeException('Šablona nebyla nalezena.');return $rows;});teacher_ops_audit_event('message_template.delete','message_template',$id,'','Komunikační šablona odstraněna');
}
function teacher_ops_message_render(string $body,string $classId='',string $studentKey=''): string
{
    $student=$classId!==''?(project_students_for_class($classId)[$studentKey]??[]):[];$summary=($classId!==''&&$studentKey!=='')?(teacher_ops_student_row($classId,$studentKey)??[]):[];$task=(array)($summary['task_summary']??[]);$repl=['{{student}}'=>(string)($student['label']??'student'),'{{class}}'=>$classId!==''?teacher_ops_class_label($classId):'třída','{{teacher}}'=>teacher_display_name(),'{{due}}'=>(string)($task['nearest_due']??'—'),'{{active_tasks}}'=>(string)($task['active']??0),'{{overdue}}'=>(string)($task['overdue']??0),'{{mastery}}'=>isset($summary['mastery'])&&$summary['mastery']!==null?number_format((float)$summary['mastery'],0,',',' ').' %':'—'];return strtr($body,$repl);
}

function teacher_ops_cross_class_report(): array
{
    $rows=[];foreach(teacher_ops_class_ids() as $cid){$health=teacher_ops_class_health($cid);$att=teacher_ops_attention_class($cid);$rows[]=['class_id'=>$cid,'label'=>teacher_ops_class_label($cid),'students'=>count((array)$att['students']),'health'=>$health['score'],'health_status'=>$health['status'],'critical'=>count((array)$att['critical']),'deadlines'=>count((array)$att['deadlines']),'drops'=>count((array)$att['drops']),'waiting_teacher'=>count((array)$att['waiting_teacher']),'waiting_student'=>count((array)$att['waiting_student'])];}usort($rows,static fn($a,$b)=>(float)$a['health']<=>(float)$b['health']);return $rows;
}
function teacher_ops_report_export(string $format='csv'): never
{
    teacher_require_permission('analytics.view');$rows=teacher_ops_cross_class_report();$format=strtolower($format);
    if($format==='json'){header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="educanet-operations-'.date('Y-m-d').'.json"');echo json_encode(['generated_at'=>date(DATE_ATOM),'team'=>teacher_team_label(),'classes'=>$rows,'escalations'=>teacher_ops_escalations()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);exit;}
    header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="educanet-operations-'.date('Y-m-d').'.csv"');echo "\xEF\xBB\xBF";$fh=fopen('php://output','wb');fputcsv($fh,['Třída','Studentů','Health','Stav','Kritické','Termíny','Propady','Čeká na učitele','Čeká na studenty'],';','"','\\');foreach($rows as $r)fputcsv($fh,[$r['label'],$r['students'],$r['health'],$r['health_status'],$r['critical'],$r['deadlines'],$r['drops'],$r['waiting_teacher'],$r['waiting_student']],';','"','\\');fclose($fh);exit;
}

function teacher_ops_data_quality(): array
{
    $issues=[];$classes=teacher_ops_class_ids();$studentMaps=[];foreach($classes as $cid)$studentMaps[$cid]=project_students_for_class($cid);
    $seen=[];foreach(teacher_task_rows() as $row){if(!is_array($row)||!teacher_ops_class_in_scope((string)($row['class_id']??'')))continue;$id=(string)($row['id']??'');if($id!==''&&isset($seen['task:'.$id]))$issues[]=['severity'=>'high','type'=>'duplicate','label'=>'Duplicitní ID úkolu','detail'=>$id];$seen['task:'.$id]=true;$cid=(string)($row['class_id']??'');$key=(string)($row['student_key']??'');if(!isset($studentMaps[$cid][$key]))$issues[]=['severity'=>'high','type'=>'orphan','label'=>'Úkol bez platného studenta','detail'=>$cid.' · '.$key.' · '.(string)($row['title']??'')];$due=(string)($row['due_at']??'');if($due!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$due))$issues[]=['severity'=>'medium','type'=>'date','label'=>'Neplatný termín úkolu','detail'=>$id.' · '.$due];}
    foreach(teacher_ops_intervention_rows() as $row){if(!is_array($row)||!teacher_ops_class_in_scope((string)($row['class_id']??'')))continue;$cid=(string)($row['class_id']??'');foreach((array)($row['student_keys']??[]) as $key)if(!isset($studentMaps[$cid][(string)$key]))$issues[]=['severity'=>'high','type'=>'orphan','label'=>'Intervence odkazuje na neznámého studenta','detail'=>(string)($row['title']??'').' · '.(string)$key];}
    foreach(teacher_ops_followup_rows() as $row){if(!is_array($row)||!teacher_ops_class_in_scope((string)($row['class_id']??'')))continue;$cid=(string)($row['class_id']??'');$key=(string)($row['student_key']??'');if($key!==''&&!isset($studentMaps[$cid][$key]))$issues[]=['severity'=>'medium','type'=>'orphan','label'=>'Follow-up bez platného studenta','detail'=>(string)($row['title']??'').' · '.$key];}
    foreach(teacher_saved_filter_rows() as $row){if(!is_array($row)||(($row['class_id']??'')!==''&&!teacher_ops_class_in_scope((string)$row['class_id'])))continue;$cid=(string)($row['class_id']??'');if($cid!==''&&!in_array($cid,$classes,true))$issues[]=['severity'=>'low','type'=>'filter','label'=>'Preset odkazuje na neexistující třídu','detail'=>(string)($row['name']??'').' · '.$cid];}
    foreach(teacher_ops_team_members() as $row){$last=strtotime((string)($row['last_seen_at']??''))?:0;if($last>0&&$last<time()-120*86400)$issues[]=['severity'=>'low','type'=>'team','label'=>'Dlouho neaktivní učitelský actor','detail'=>(string)($row['label']??'').' · '.(string)($row['role']??'')];}
    $counts=['high'=>0,'medium'=>0,'low'=>0];foreach($issues as $issue)$counts[(string)$issue['severity']]++;return ['issues'=>$issues,'counts'=>$counts,'ok'=>count($issues)===0,'checked_at'=>date(DATE_ATOM)];
}
