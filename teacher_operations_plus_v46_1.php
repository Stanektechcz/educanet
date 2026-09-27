<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** EDUCANET v46.1 · production operations layer. */
function teacher_ops_audit_path(): string { return STORAGE_DIR.'/teacher_ops_audit.json.php'; }
function teacher_ops_templates_path(): string { return STORAGE_DIR.'/teacher_intervention_templates.json.php'; }
function teacher_ops_watchlist_path(): string { return STORAGE_DIR.'/teacher_watchlist.json.php'; }
function teacher_ops_followups_path(): string { return STORAGE_DIR.'/teacher_followups.json.php'; }
function teacher_ops_undo_path(): string { return STORAGE_DIR.'/teacher_bulk_undo.json.php'; }

function teacher_ops_trim_meta(mixed $value,int $depth=0): mixed
{
    if($depth>4)return null;
    if(is_array($value)){$out=[];$n=0;foreach($value as $k=>$v){if($n++>=40)break;$out[is_int($k)?$k:u_substr((string)$k,0,80)]=teacher_ops_trim_meta($v,$depth+1);}return $out;}
    if(is_string($value))return u_substr($value,0,700);
    if(is_scalar($value)||$value===null)return $value;
    return null;
}
/** v59: řádek s class_id jen v rozsahu učitele (řádky bez třídy rozhoduje vlastník/tým); legacy = vždy true. */
function teacher_ops_row_in_scope(array $row): bool
{
    $classId=(string)($row['class_id']??'');
    return $classId===''||!function_exists('teacher59_can_class')||teacher59_can_class($classId);
}
function teacher_ops_audit_event(string $action,string $entityType,string $entityId,string $classId,string $summary,array $meta=[]): array
{
    if(!function_exists('teacher_export_authenticated')||!teacher_export_authenticated())return [];
    $row=['id'=>teacher_ops_id('oa'),'action'=>u_substr($action,0,80),'entity_type'=>u_substr($entityType,0,60),'entity_id'=>u_substr($entityId,0,120),'class_id'=>isset(project_catalog()[$classId])?$classId:'','summary'=>u_substr(trim($summary),0,500),'meta'=>teacher_ops_trim_meta($meta),'owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'role'=>teacher_role(),'team_key'=>teacher_team_key(),'created_at'=>date(DATE_ATOM)];
    $teacherId=function_exists('teacher59_current_id')?teacher59_current_id():null;if($teacherId!==null)$row['teacher_id']=$teacherId; // v59: účet učitele
    teacher_ops_update_rows(teacher_ops_audit_path(),static function(array $rows) use($row): array {$rows[]=$row;if(count($rows)>7000)$rows=array_slice($rows,-6000);return $rows;});return $row;
}
function teacher_ops_audit_visible(array $filters=[]): array
{
    teacher_require_permission('audit.view');$team=teacher_team_key();$owner=teacher_saved_filter_owner_key();$role=teacher_role();$out=[];
    foreach(teacher_ops_rows(teacher_ops_audit_path()) as $row){if(!is_array($row)||(string)($row['team_key']??'')!==$team)continue;if(in_array($role,['teacher','assistant'],true)&&(string)($row['owner_key']??'')!==$owner)continue;if(!teacher_ops_row_in_scope($row))continue;if(($filters['class_id']??'')!==''&&(string)($row['class_id']??'')!==(string)$filters['class_id'])continue;if(($filters['action']??'')!==''&&!str_contains((string)($row['action']??''),(string)$filters['action']))continue;$out[]=$row;}
    usort($out,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));return array_slice($out,0,500);
}

function teacher_ops_class_health(string $classId): array
{
    static $cache=[];
    $classId=teacher_ops_valid_class($classId);
    if(isset($cache[$classId]))return $cache[$classId];
    $attention=teacher_ops_attention_class($classId);$students=(array)$attention['students'];$studentCount=count($students);$n=max(1,$studentCount);
    if($studentCount===0)return $cache[$classId]=['score'=>0.0,'status'=>'empty','components'=>['results'=>0.0,'deadlines'=>0.0,'mastery'=>0.0,'activity'=>0.0],'counts'=>['students'=>0,'overdue_students'=>0,'inactive_7'=>0,'returned'=>0]];
    $project=[];$mastery=[];$inactive7=0;$inactive14=0;$overdueStudents=0;$due3Students=0;$returned=0;
    foreach($students as $row){if(($row['avg_project_pct']??null)!==null)$project[]=(float)$row['avg_project_pct'];if(!empty($row['has_evidence'])&&($row['mastery']??null)!==null)$mastery[]=(float)$row['mastery'];$inactive=(int)($row['inactive_days']??0);if($inactive>=7)$inactive7++;if($inactive>=14)$inactive14++;$task=(array)($row['task_summary']??[]);if((int)($task['overdue']??0)>0)$overdueStudents++;elseif((int)($task['due_3']??0)>0)$due3Students++;if((int)($row['returned']??0)>0)$returned++;}
    $results=$project?max(0,min(100,array_sum($project)/count($project))):50.0;
    $deadline=max(0,100-($overdueStudents/$n*75)-($due3Students/$n*20));
    $masteryScore=$mastery?max(0,min(100,array_sum($mastery)/count($mastery))):50.0;
    $activity=max(0,100-($inactive7/$n*45)-($inactive14/$n*35)-($returned/$n*20));
    $score=round($results*.30+$deadline*.25+$masteryScore*.25+$activity*.20,1);$status=$score>=80?'healthy':($score>=65?'watch':'risk');
    return $cache[$classId]=['score'=>$score,'status'=>$status,'components'=>['results'=>round($results,1),'deadlines'=>round($deadline,1),'mastery'=>round($masteryScore,1),'activity'=>round($activity,1)],'counts'=>['students'=>$studentCount,'overdue_students'=>$overdueStudents,'inactive_7'=>$inactive7,'returned'=>$returned]];
}
function teacher_ops_health_label(string $status): string { return match($status){'healthy'=>'Stabilní','watch'=>'Sledovat','empty'=>'Bez dat',default=>'Riziko'}; }

function teacher_ops_scope(string $scope): string { return $scope==='team'?'team':'personal'; }
function teacher_ops_owner_visible(array $row): bool
{
    if(!teacher_ops_row_in_scope($row))return false; // v59: řádky cizích tříd nejsou vidět ani v týmovém rozsahu
    $scope=teacher_ops_scope((string)($row['scope']??'personal'));if($scope==='personal')return (string)($row['owner_key']??'')===teacher_saved_filter_owner_key();$team=(string)($row['team_key']??'');return $team!==''&&hash_equals($team,teacher_team_key());
}
function teacher_ops_is_owner(array $row): bool { return (string)($row['owner_key']??'')!==''&&hash_equals((string)$row['owner_key'],teacher_saved_filter_owner_key()); }

function teacher_ops_watchlist_rows(): array { return teacher_ops_rows(teacher_ops_watchlist_path()); }
function teacher_ops_watchlist_visible(?string $classId=null): array
{
    $out=[];foreach(teacher_ops_watchlist_rows() as $row){if(!is_array($row)||!teacher_ops_owner_visible($row))continue;if($classId!==null&&(string)($row['class_id']??'')!==$classId)continue;$row['is_owner']=teacher_ops_is_owner($row);$out[]=$row;}usort($out,static fn($a,$b)=>strcmp((string)($b['updated_at']??''),(string)($a['updated_at']??'')));return $out;
}
function teacher_ops_watchlist_for_student(string $classId,string $studentKey): array { return array_values(array_filter(teacher_ops_watchlist_visible($classId),static fn($r)=>(string)($r['student_key']??'')===$studentKey)); }
function teacher_ops_watchlist_save(string $classId,string $studentKey,string $scope,string $reason,string $priority='normal'): array
{
    teacher_require_permission('watchlist.manage');$classId=teacher_ops_valid_class($classId);if(!isset(project_students_for_class($classId)[$studentKey]))throw new RuntimeException('Student nebyl nalezen.');$scope=teacher_ops_scope($scope);$reason=trim(u_substr($reason,0,600));$priority=in_array($priority,['high','normal','low'],true)?$priority:'normal';$owner=teacher_saved_filter_owner_key();$team=$scope==='team'?teacher_team_key():'';$label=teacher_display_name();$row=[];teacher_ops_update_rows(teacher_ops_watchlist_path(),static function(array $rows) use($classId,$studentKey,$scope,$reason,$priority,$owner,$team,$label,&$row): array {$match=null;foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey||(string)($row['owner_key']??'')!==$owner||teacher_ops_scope((string)($row['scope']??''))!==$scope)continue;$match=$i;break;}$now=date(DATE_ATOM);$base=$match!==null?$rows[$match]:[];$row=['id'=>(string)($base['id']??teacher_ops_id('wl')),'class_id'=>$classId,'student_key'=>$studentKey,'scope'=>$scope,'team_key'=>$team,'reason'=>$reason,'priority'=>$priority,'owner_key'=>$owner,'owner_label'=>$label,'created_at'=>(string)($base['created_at']??$now),'updated_at'=>$now];if($match!==null)$rows[$match]=$row;else$rows[]=$row;return $rows;});teacher_ops_audit_event('watchlist.save','student',$studentKey,$classId,'Student přidán/aktualizován ve watchlistu',['scope'=>$scope,'priority'=>$priority]);return $row;
}
function teacher_ops_watchlist_delete(string $id): void
{
    teacher_require_permission('watchlist.manage');$found=null;teacher_ops_update_rows(teacher_ops_watchlist_path(),static function(array $rows) use($id,&$found): array {foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if(!teacher_ops_is_owner($row))throw new RuntimeException('Můžete odebrat pouze vlastní watchlist záznam.');$found=$row;unset($rows[$i]);break;}if(!$found)throw new RuntimeException('Watchlist záznam nebyl nalezen.');return $rows;});teacher_ops_audit_event('watchlist.delete','student',(string)$found['student_key'],(string)$found['class_id'],'Student odebrán z watchlistu');
}

function teacher_ops_followup_rows(): array { return teacher_ops_rows(teacher_ops_followups_path()); }
function teacher_ops_followups_visible(?string $classId=null,bool $includeDone=false): array
{
    $out=[];foreach(teacher_ops_followup_rows() as $row){if(!is_array($row)||!teacher_ops_owner_visible($row))continue;if($classId!==null&&(string)($row['class_id']??'')!==$classId)continue;if(!$includeDone&&(string)($row['status']??'open')==='done')continue;$row['is_owner']=teacher_ops_is_owner($row);$out[]=$row;}usort($out,static function($a,$b){$rank=['high'=>0,'normal'=>1,'low'=>2];$ad=(string)($a['due_at']??'9999-12-31');$bd=(string)($b['due_at']??'9999-12-31');$d=strcmp($ad,$bd);if($d!==0)return $d;return ($rank[(string)($a['priority']??'normal')]??1)<=>($rank[(string)($b['priority']??'normal')]??1);});return $out;
}
function teacher_ops_followup_save(array $input): array
{
    teacher_require_permission('followups.manage');$classId=teacher_ops_valid_class((string)($input['class_id']??'class_2a'));$studentKey=(string)($input['student_key']??'');if($studentKey!==''&&!isset(project_students_for_class($classId)[$studentKey]))throw new RuntimeException('Student nebyl nalezen.');$title=trim(u_substr((string)($input['followup_title']??''),0,180));if($title==='')throw new RuntimeException('Doplňte název follow-upu.');$due=trim((string)($input['followup_due_at']??''));if($due!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$due))throw new RuntimeException('Termín follow-upu má neplatný formát.');$scope=teacher_ops_scope((string)($input['followup_scope']??'personal'));$priority=(string)($input['followup_priority']??'normal');if(!in_array($priority,['high','normal','low'],true))$priority='normal';$now=date(DATE_ATOM);$row=['id'=>teacher_ops_id('fu'),'class_id'=>$classId,'student_key'=>$studentKey?:null,'title'=>$title,'detail'=>trim(u_substr((string)($input['followup_detail']??''),0,1000)),'due_at'=>$due?:null,'priority'=>$priority,'status'=>'open','scope'=>$scope,'team_key'=>$scope==='team'?teacher_team_key():'','owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'created_at'=>$now,'updated_at'=>$now,'completed_at'=>null];storage_list_push(teacher_ops_followups_path(),$row);teacher_ops_audit_event('followup.create','followup',$row['id'],$classId,'Follow-up vytvořen',['student_key'=>$studentKey,'scope'=>$scope]);return $row;
}
function teacher_ops_followup_update(string $id,string $mode): array
{
    teacher_require_permission('followups.manage');$found=null;$now=date(DATE_ATOM);teacher_ops_update_rows(teacher_ops_followups_path(),static function(array $rows) use($id,$mode,$now,&$found): array {foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if(!teacher_ops_is_owner($row))throw new RuntimeException('Můžete měnit pouze vlastní follow-up.');if($mode==='done'){$row['status']='done';$row['completed_at']=$now;}elseif($mode==='reopen'){$row['status']='open';$row['completed_at']=null;}elseif($mode==='tomorrow'){$row['status']='open';$row['due_at']=(new DateTimeImmutable('tomorrow'))->format('Y-m-d');}elseif($mode==='week'){$row['status']='open';$row['due_at']=(new DateTimeImmutable('+7 days'))->format('Y-m-d');}else throw new RuntimeException('Neplatná follow-up akce.');$row['updated_at']=$now;$rows[$i]=$row;$found=$row;break;}if(!$found)throw new RuntimeException('Follow-up nebyl nalezen.');return $rows;});teacher_ops_audit_event('followup.'.$mode,'followup',$id,(string)$found['class_id'],'Follow-up aktualizován');return $found;
}

function teacher_ops_builtin_templates(): array
{
    return [
      ['id'=>'builtin:deadline','builtin'=>true,'name'=>'Recovery po termínu','problem'=>'Student nestihl termín nebo kumuluje nedokončené úkoly.','success_metric'=>'Aktivní úkoly dokončeny nebo přeplánovány a checkpoint splněn.','material'=>'Krátké shrnutí požadavků a nejmenší další krok.','micro_task'=>'Dokonči nejmenší blok práce a odevzdej důkaz postupu.','checkpoint'=>'5min kontrola: co je hotové, co blokuje, co bude další krok.','retry'=>'Nový termín / nový pokus s jasným minimem kvality.','review_days'=>3,'create_task'=>true,'task_priority'=>'high'],
      ['id'=>'builtin:mastery','builtin'=>true,'name'=>'Mastery gap','problem'=>'Student má důkaz aktivity, ale nízkou mastery v cílové oblasti.','success_metric'=>'≥ 80 % v kontrolním pokusu + vlastní vysvětlení postupu.','material'=>'Cílené vysvětlení konceptu + jeden vizuální příklad.','micro_task'=>'Vyřeš 2 krátké varianty bez nápovědy.','checkpoint'=>'Explain-back: vysvětli princip vlastními slovy.','retry'=>'Opakuj challenge s novými vstupy.','review_days'=>7,'create_task'=>true,'task_priority'=>'normal'],
      ['id'=>'builtin:project','builtin'=>true,'name'=>'Project recovery','problem'=>'Projekt byl vrácen nebo se výkon výrazně propadl.','success_metric'=>'Opravena kritická část rubriky a nový výsledek bez stejné chyby.','material'=>'Projdi rubriku a označ konkrétní nesplněné kritérium.','micro_task'=>'Oprav pouze jednu nejrizikovější část projektu.','checkpoint'=>'Krátký review diffu před dalším odevzdáním.','retry'=>'Nové odevzdání / kontrola podle stejné rubriky.','review_days'=>5,'create_task'=>false,'task_priority'=>'high'],
      ['id'=>'builtin:confidence','builtin'=>true,'name'=>'Confidence calibration','problem'=>'Studentův odhad jistoty neodpovídá skutečnému výkonu.','success_metric'=>'Predikce jistoty se přibližuje výsledku a student pojmenuje nejisté místo.','material'=>'Krátký příklad správného self-checku.','micro_task'=>'Před odpovědí odhadni jistotu a napiš proč.','checkpoint'=>'Porovnej predikci jistoty se skutečným výsledkem.','retry'=>'Nový pokus s explicitním confidence odhadem.','review_days'=>7,'create_task'=>false,'task_priority'=>'normal'],
    ];
}
function teacher_ops_template_rows(): array { return teacher_ops_rows(teacher_ops_templates_path()); }
function teacher_ops_templates_visible(): array
{
    $out=teacher_ops_builtin_templates();foreach(teacher_ops_template_rows() as $row){if(!is_array($row)||!teacher_ops_owner_visible($row))continue;$row['builtin']=false;$row['is_owner']=teacher_ops_is_owner($row);$out[]=$row;}return $out;
}
function teacher_ops_template_find(string $id): ?array { foreach(teacher_ops_templates_visible() as $row)if((string)($row['id']??'')===$id)return $row;return null; }
function teacher_ops_template_save(array $input): array
{
    teacher_require_permission('templates.manage');$name=trim(u_substr((string)($input['template_name']??''),0,100));if($name==='')throw new RuntimeException('Doplňte název šablony.');$scope=teacher_ops_scope((string)($input['template_scope']??'personal'));$days=max(1,min(60,(int)($input['template_review_days']??7)));$priority=(string)($input['template_task_priority']??'normal');if(!in_array($priority,['high','normal','low'],true))$priority='normal';$row=['id'=>teacher_ops_id('it'),'name'=>$name,'scope'=>$scope,'team_key'=>$scope==='team'?teacher_team_key():'','owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'problem'=>trim(u_substr((string)($input['template_problem']??''),0,1600)),'success_metric'=>trim(u_substr((string)($input['template_success']??''),0,900)),'material'=>trim(u_substr((string)($input['template_material']??''),0,1200)),'micro_task'=>trim(u_substr((string)($input['template_micro_task']??''),0,1200)),'checkpoint'=>trim(u_substr((string)($input['template_checkpoint']??''),0,1200)),'retry'=>trim(u_substr((string)($input['template_retry']??''),0,1200)),'review_days'=>$days,'create_task'=>!empty($input['template_create_task']),'task_priority'=>$priority,'created_at'=>date(DATE_ATOM),'updated_at'=>date(DATE_ATOM)];storage_list_push(teacher_ops_templates_path(),$row);teacher_ops_audit_event('template.create','template',$row['id'],'','Intervenční šablona vytvořena',['scope'=>$scope]);return $row;
}
function teacher_ops_template_delete(string $id): void
{
    teacher_require_permission('templates.manage');$found=null;teacher_ops_update_rows(teacher_ops_templates_path(),static function(array $rows) use($id,&$found): array {foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if(!teacher_ops_is_owner($row))throw new RuntimeException('Můžete smazat pouze vlastní šablonu.');$found=$row;unset($rows[$i]);break;}if(!$found)throw new RuntimeException('Šablona nebyla nalezena.');return $rows;});teacher_ops_audit_event('template.delete','template',$id,'','Intervenční šablona odstraněna');
}
function teacher_ops_intervention_input_with_template(array $input): array
{
    $id=trim((string)($input['intervention_template_id']??''));if($id==='')return $input;$tpl=teacher_ops_template_find($id);if(!$tpl)return $input;$map=['intervention_problem'=>'problem','intervention_success'=>'success_metric','intervention_material'=>'material','intervention_micro_task'=>'micro_task','intervention_checkpoint'=>'checkpoint','intervention_retry'=>'retry'];foreach($map as $dest=>$src)if(trim((string)($input[$dest]??''))==='')$input[$dest]=(string)($tpl[$src]??'');if(trim((string)($input['intervention_title']??''))==='')$input['intervention_title']=(string)($tpl['name']??'Intervence');if(trim((string)($input['intervention_review_due']??''))===''&&!empty($tpl['review_days']))$input['intervention_review_due']=(new DateTimeImmutable('+'.(int)$tpl['review_days'].' days'))->format('Y-m-d');if(!isset($input['create_student_task'])&&!empty($tpl['create_task']))$input['create_student_task']='1';if(empty($input['task_priority']))$input['task_priority']=(string)($tpl['task_priority']??'normal');return $input;
}

function teacher_ops_hash_row(array $row): string
{
    $sort=function(&$value)use(&$sort):void{if(!is_array($value))return;if(array_is_list($value)){foreach($value as &$v)$sort($v);unset($v);return;}ksort($value);foreach($value as &$v)$sort($v);unset($v);};$copy=$row;$sort($copy);return hash('sha256',json_encode($copy,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?:'');
}
function teacher_ops_undo_rows(): array { return teacher_ops_rows(teacher_ops_undo_path()); }
function teacher_ops_undo_record(string $classId,string $summary,array $ops): array
{
    $now=time();$row=['id'=>teacher_ops_id('un'),'class_id'=>$classId,'summary'=>u_substr($summary,0,300),'ops'=>$ops,'owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'team_key'=>teacher_team_key(),'created_at'=>date(DATE_ATOM,$now),'expires_at'=>date(DATE_ATOM,$now+900),'used_at'=>null];teacher_ops_update_rows(teacher_ops_undo_path(),static function(array $rows) use($row): array {$rows[]=$row;if(count($rows)>1200)$rows=array_slice($rows,-900);return $rows;});return $row;
}
function teacher_ops_undo_available(?string $classId=null,int $limit=8): array
{
    $owner=teacher_saved_filter_owner_key();$now=time();$out=[];foreach(teacher_ops_undo_rows() as $row){if(!is_array($row)||(string)($row['owner_key']??'')!==$owner||!empty($row['used_at'])||!teacher_ops_row_in_scope($row))continue;if((strtotime((string)($row['expires_at']??''))?:0)<$now)continue;if($classId!==null&&(string)($row['class_id']??'')!==$classId)continue;$out[]=$row;}usort($out,static fn($a,$b)=>strcmp((string)$b['created_at'],(string)$a['created_at']));return array_slice($out,0,$limit);
}
function teacher_ops_store_rows_by_key(string $store,array $rows): array
{
    $out=[];foreach($rows as $row){if(!is_array($row))continue;$key=$store==='flags'?(string)($row['class_id']??'').'|'.(string)($row['student_key']??''):(string)($row['id']??'');if($key!=='')$out[$key]=$row;}return $out;
}
function teacher_ops_store_path(string $store): string
{
    return match($store){'tasks'=>teacher_tasks_path(),'flags'=>teacher_student_flags_path(),'notes'=>teacher_ops_notes_path(),'interventions'=>teacher_ops_interventions_path(),default=>throw new RuntimeException('Neznámý undo store.')};
}
function teacher_ops_capture_store(string $store): array { return teacher_ops_store_rows_by_key($store,load_php_json(teacher_ops_store_path($store))); }
function teacher_ops_diff_undo_op(string $store,array $before,array $after,array $keys=[]): ?array
{
    $all=$keys?:array_values(array_unique(array_merge(array_keys($before),array_keys($after))));$b=[];$h=[];$changed=[];foreach($all as $key){$bv=$before[$key]??null;$av=$after[$key]??null;if($bv===$av)continue;$b[$key]=$bv;$h[$key]=is_array($av)?teacher_ops_hash_row($av):null;$changed[]=$key;}return $changed?['store'=>$store,'before'=>$b,'after_hash'=>$h,'keys'=>$changed]:null;
}
function teacher_ops_undo_execute(string $id): array
{
    teacher_require_permission('bulk.undo');$rows=teacher_ops_undo_rows();$idx=null;$entry=null;foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;$idx=$i;$entry=$row;break;}if(!$entry)throw new RuntimeException('Undo záznam nebyl nalezen.');if((string)($entry['owner_key']??'')!==teacher_saved_filter_owner_key())throw new RuntimeException('Můžete vrátit pouze vlastní hromadnou akci.');if(!empty($entry['used_at']))throw new RuntimeException('Tato akce už byla vrácena.');if((strtotime((string)($entry['expires_at']??''))?:0)<time())throw new RuntimeException('Bezpečné undo již vypršelo.');
    // v58 (F2): kontrola hashů, obnova i označení undo proběhnou atomicky pod zámky všech dotčených souborů.
    $paths=[teacher_ops_undo_path()];foreach((array)($entry['ops']??[]) as $op)if(is_array($op))$paths[]=teacher_ops_store_path((string)($op['store']??''));$undoPath=teacher_ops_undo_path();$used=null;
    storage_update_many($paths,static function(array $data) use($entry,$id,$undoPath,&$used): array {
    foreach((array)($entry['ops']??[]) as $op){if(!is_array($op))continue;$store=(string)($op['store']??'');$current=teacher_ops_store_rows_by_key($store,$data[teacher_ops_store_path($store)]);foreach((array)($op['keys']??[]) as $key){$expected=$op['after_hash'][$key]??null;$cur=$current[(string)$key]??null;$hash=is_array($cur)?teacher_ops_hash_row($cur):null;if($hash!==$expected)throw new RuntimeException('Undo bylo zablokováno: některý záznam se mezitím změnil.');}}
    foreach((array)($entry['ops']??[]) as $op){$store=(string)$op['store'];$path=teacher_ops_store_path($store);$current=teacher_ops_store_rows_by_key($store,$data[$path]);foreach((array)$op['keys'] as $key){$key=(string)$key;$before=$op['before'][$key]??null;if(is_array($before))$current[$key]=$before;else unset($current[$key]);}$data[$path]=array_values($current);}
    $undo=array_values($data[$undoPath]);foreach($undo as $i=>$u){if(is_array($u)&&(string)($u['id']??'')===$id){if(!empty($u['used_at']))throw new RuntimeException('Tato akce už byla vrácena.');$undo[$i]['used_at']=date(DATE_ATOM);$used=$undo[$i];}}$data[$undoPath]=$undo;
    return $data;});$rows[$idx]=$used??$rows[$idx];teacher_ops_audit_event('bulk.undo','undo',$id,(string)($entry['class_id']??''),'Vrácena hromadná akce',['summary'=>$entry['summary']??'']);return $rows[$idx];
}
function teacher_ops_bulk_mark_with_undo(string $classId,array $keys,string $marker): array
{
    $before=teacher_ops_capture_store('flags');$count=teacher_student_flags_set($classId,$keys,$marker);$after=teacher_ops_capture_store('flags');$synthetic=array_map(static fn($k)=>$classId.'|'.$k,$keys);$op=teacher_ops_diff_undo_op('flags',$before,$after,$synthetic);$undo=$op?teacher_ops_undo_record($classId,'Označení '.$count.' studentů',[$op]):null;teacher_ops_audit_event('bulk.mark','student_group','',$classId,'Hromadně změněno označení studentů',['count'=>$count,'marker'=>$marker,'undo_id'=>$undo['id']??null]);return ['count'=>$count,'undo'=>$undo];
}
function teacher_ops_bulk_assign_task_with_undo(string $classId,array $keys,array $input): array
{
    $before=teacher_ops_capture_store('tasks');$count=teacher_bulk_assign_task($classId,$keys,$input);$after=teacher_ops_capture_store('tasks');$op=teacher_ops_diff_undo_op('tasks',$before,$after);$undo=$op?teacher_ops_undo_record($classId,'Přiřazení úkolu '.$count.' studentům',[$op]):null;teacher_ops_audit_event('bulk.task.assign','task_group','',$classId,'Hromadně přiřazen úkol',['count'=>$count,'undo_id'=>$undo['id']??null]);return ['count'=>$count,'undo'=>$undo];
}
function teacher_ops_bulk_update_tasks_with_undo(string $classId,array $keys,array $input): array
{
    $before=teacher_ops_capture_store('tasks');$count=teacher_ops_bulk_update_tasks($classId,$keys,$input);$after=teacher_ops_capture_store('tasks');$op=teacher_ops_diff_undo_op('tasks',$before,$after);$undo=$op?teacher_ops_undo_record($classId,'Úprava '.$count.' aktivních úkolů',[$op]):null;teacher_ops_audit_event('bulk.task.update','task_group','',$classId,'Hromadně upraveny aktivní úkoly',['count'=>$count,'mode'=>$input['bulk_task_update']??'','undo_id'=>$undo['id']??null]);return ['count'=>$count,'undo'=>$undo];
}
function teacher_ops_bulk_note_with_undo(string $classId,array $keys,string $text,string $tag): array
{
    $before=teacher_ops_capture_store('notes');$count=teacher_ops_note_add($classId,$keys,$text,$tag);$after=teacher_ops_capture_store('notes');$op=teacher_ops_diff_undo_op('notes',$before,$after);$undo=$op?teacher_ops_undo_record($classId,'Přidání poznámky '.$count.' studentům',[$op]):null;teacher_ops_audit_event('bulk.note','note_group','',$classId,'Hromadně přidána poznámka',['count'=>$count,'undo_id'=>$undo['id']??null]);return ['count'=>$count,'undo'=>$undo];
}
function teacher_ops_bulk_resource_with_undo(string $classId,array $keys,array $input): array
{
    $before=teacher_ops_capture_store('tasks');$count=teacher_ops_bulk_assign_resource($classId,$keys,$input);$after=teacher_ops_capture_store('tasks');$op=teacher_ops_diff_undo_op('tasks',$before,$after);$undo=$op?teacher_ops_undo_record($classId,'Přiřazení materiálu '.$count.' studentům',[$op]):null;teacher_ops_audit_event('bulk.resource','task_group','',$classId,'Hromadně přiřazen materiál',['count'=>$count,'undo_id'=>$undo['id']??null]);return ['count'=>$count,'undo'=>$undo];
}
function teacher_ops_bulk_intervention_with_undo(string $classId,array $keys,array $input): array
{
    $beforeIv=teacher_ops_capture_store('interventions');$beforeTasks=teacher_ops_capture_store('tasks');$row=teacher_ops_intervention_create($classId,$keys,$input);$afterIv=teacher_ops_capture_store('interventions');$afterTasks=teacher_ops_capture_store('tasks');$ops=[];$a=teacher_ops_diff_undo_op('interventions',$beforeIv,$afterIv);if($a)$ops[]=$a;$b=teacher_ops_diff_undo_op('tasks',$beforeTasks,$afterTasks);if($b)$ops[]=$b;$undo=$ops?teacher_ops_undo_record($classId,'Vytvoření skupinové intervence', $ops):null;teacher_ops_audit_event('bulk.intervention','intervention',(string)$row['id'],$classId,'Hromadně vytvořena intervence',['students'=>count($keys),'undo_id'=>$undo['id']??null]);return ['row'=>$row,'undo'=>$undo];
}
