<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v46 · Teacher Operations Suite
 * Attention Center, Student 360, interventions, analytics, automations,
 * teacher notes and role/permission helpers. Functions are intentionally
 * side-effect free unless their name explicitly contains save/add/update/run.
 */

function teacher_ops_team_members_path(): string { return STORAGE_DIR.'/teacher_team_members.json.php'; }
function teacher_role(): string
{
    // v59 · AUTHZ58-07: v režimu účtů roli určuje účet (admin → *, teacher, assistant); bez platné relace asistent.
    if(function_exists('teacher59_role_for_v46')){$v59Role=teacher59_role_for_v46();if($v59Role!==null)return $v59Role;}
    $fallback=strtolower(trim(educanet_secret('teacher_role','teacher')));if(!in_array($fallback,['admin','lead','teacher','assistant'],true))$fallback='teacher';
    if(!function_exists('teacher_export_authenticated')||!teacher_export_authenticated()||!function_exists('teacher_saved_filter_owner_key'))return $fallback;
    $owner=teacher_saved_filter_owner_key();$team=teacher_team_key();
    foreach(teacher_ops_rows(teacher_ops_team_members_path()) as $row){if(!is_array($row)||(string)($row['owner_key']??'')!==$owner)continue;if((string)($row['team_key']??'')!==''&&!hash_equals((string)$row['team_key'],$team))continue;$role=strtolower((string)($row['role']??''));if(in_array($role,['admin','lead','teacher','assistant'],true))return $role;}
    return $fallback;
}
function teacher_role_label(?string $role=null): string
{
    return match($role??teacher_role()){'admin'=>'Administrátor','lead'=>'Vedoucí učitel','assistant'=>'Asistent','teacher'=>'Učitel',default=>'Učitel'};
}
/** Oprávnění, které nemá nikdo (ani admin s „*“) – vrací se pro neznámou akci (SEC-12, deny-by-default). */
const TEACHER_PERMISSION_DENY = '__deny__';

function teacher_permission(string $permission): bool
{
    return teacher_permission_for_role(teacher_role(),$permission);
}
/** v68: oprávnění pro zadanou roli (čistá funkce; menu cockpitu a audit ji volají bez relace). */
function teacher_permission_for_role(string $role,string $permission): bool
{
    if($permission===TEACHER_PERMISSION_DENY)return false;
    $matrix=[
        'admin'=>['*'],
        'lead'=>['view','grading.manage','projects.manage','curriculum.manage','skills.manage','content.manage','students.manage','filters.manage','team_filters.manage','interventions.manage','automations.manage','analytics.view','notes.manage','roles.manage','audit.view','templates.manage','watchlist.manage','followups.manage','bulk.undo'],
        'teacher'=>['view','grading.manage','projects.manage','curriculum.manage','skills.manage','content.manage','students.manage','filters.manage','team_filters.manage','interventions.manage','automations.manage','analytics.view','notes.manage','audit.view','templates.manage','watchlist.manage','followups.manage','bulk.undo'],
        'assistant'=>['view','analytics.view','filters.manage','notes.manage','students.mark','interventions.view','audit.view','watchlist.manage','followups.manage','bulk.undo'],
    ];
    $allowed=$matrix[$role]??$matrix['teacher'];
    return in_array('*',$allowed,true)||in_array($permission,$allowed,true);
}
function teacher_require_permission(string $permission): void
{
    if($permission===TEACHER_PERMISSION_DENY) throw new RuntimeException('Tuto akci učitelské rozhraní nezná, proto byla z bezpečnostních důvodů zamítnuta.');
    if(!teacher_permission($permission)) throw new RuntimeException('Tato akce není dostupná pro roli „'.teacher_role_label().'“.');
}

/**
 * v58 · SEC-12: mapa POST akcí učitele → oprávnění. Přesné názvy mají přednost před prefixy.
 * JAK PŘIDAT NOVOU AKCI (např. týmové hry, editor úrovní, Robotí liga, ARN režimy):
 *   1. přesný název do teacher_action_permission_exact(), nebo prefix celé rodiny akcí
 *      (např. 'v58_league_' => 'students.manage') do teacher_action_permission_prefixes();
 *   2. spusť `php tools/v58_accounts_audit.php` – kontrola SEC-12 najde každou akci obsluhovanou
 *      v teacher.php a v handlerech (*_teacher_handle_post) a selže, pokud nemá mapování.
 * Akce bez mapování dostane TEACHER_PERMISSION_DENY → teacher_require_permission() ji zamítne všem.
 * teacher_login/teacher_logout obsluhuje teacher.php ještě před kontrolou oprávnění.
 */
function teacher_action_permission_exact(): array
{
    return [
        'save_grade'=>'grading.manage','v55_t_bonus_grade'=>'grading.manage',
        'save_group'=>'projects.manage','delete_group'=>'projects.manage',
        'teacher_saved_filter_save'=>'filters.manage','teacher_saved_filter_delete'=>'filters.manage',
        'teacher_bulk_undo'=>'bulk.undo',
        'teacher_ops_report_export'=>'analytics.view',
        'v48_orchestrate'=>'content.manage',
        // v66: přepočet cache analýzy = analytika; nastavení návrhu hodnocení = jen administrátor (accounts.manage má jen role admin; navíc politika g66_settings).
        'a66_recompute'=>'analytics.view','g66_settings'=>'accounts.manage',
    ];
}
function teacher_action_permission_prefixes(): array
{
    return [
        'project_teacher_'=>'projects.manage','teacher_curriculum_'=>'curriculum.manage','teacher_calendar_'=>'curriculum.manage',
        'ml_'=>'content.manage','v42_lesson_resource_'=>'content.manage',
        'teacher_demo_account_'=>'students.manage','teacher_bulk_'=>'students.manage','teacher_intervention_'=>'interventions.manage',
        'teacher_note_'=>'notes.manage',
        'teacher_team_member_'=>'roles.manage','teacher_watchlist_'=>'watchlist.manage','teacher_followup_'=>'followups.manage',
        'teacher_message_template_'=>'templates.manage','teacher_template_'=>'templates.manage',
        'intake_t_'=>'students.manage','sess53_t_'=>'students.manage','arena57_'=>'students.manage','acc58_'=>'students.manage',
        // v58 moduly (integrátor): hry a soutěže řídí třídu, editor mění obsah, analytika jen čte/exportuje.
        'robots58_'=>'students.manage','tg58_'=>'students.manage','arena58_'=>'students.manage',
        'lab58e_'=>'content.manage','lab58t_'=>'analytics.view','ops58_'=>'audit.view','identity58_'=>'students.manage',
        // v59 · AUTHZ58-07: správa účtů jen admin (accounts.manage má jen „*“), vlastní účet každý přihlášený.
        'teacher59_admin_'=>'accounts.manage','teacher59_self_'=>'view',
        // v60 · obchod bodů: správa katalogu a vrácení nákupu (teacher má content.manage, assistant ne = jen čte).
        'mkt60_'=>'content.manage',
        // v60 · projekty podle levelu: správa katalogu a rozhodování o přihláškách (assistant jen čte).
        'proj60_'=>'content.manage',
        // v60 · hlášení chyb a návrhů: rozhodování (odměna body + XP) jako správa obsahu (assistant jen čte).
        'fb60_'=>'content.manage',
        // v61 · přehled třídy: hromadné potvrzení hlášení = rozhodování o hlášeních (assistant jen čte).
        'ov61_'=>'content.manage',
        // v62 · kompetence: ruční přepočet důkazů třídy (jen čtení zdrojů + zápis odvozených důkazů), jako analytika labu.
        'comp62_'=>'analytics.view',
        // v63 · výukové cesty: přiřazení a zrušení přiřazení cesty třídě = správa obsahu (assistant jen čte).
        'p63_'=>'content.manage',
        // v64 · ekonomika her: zapnutí/vypnutí absolutního žebříčku u akce = řízení třídy (assistant jen čte).
        'v64_'=>'students.manage',
        // v65 · projekty: schvalování, rubriky, hodnocení, moderace peer review a přínos týmů = správa projektů (assistant jen čte).
        'proj65_'=>'projects.manage',
        // v66 · testy a hodnocení: druh testu = správa obsahu, přepočet analýzy = analytika, převzetí návrhu = hodnocení, nastavení návrhu = jen administrátor (viz exact).
        'a66_'=>'content.manage','g66_'=>'grading.manage',
        // v68 · přepínač vzhledu cockpitu (cookie edu_theme, žádná data): smí každá přihlášená role.
        'teacher68_'=>'view',
    ];
}
function teacher_action_permission(string $action): ?string
{
    if($action==='')return null;
    if($action==='teacher_bulk_mark')return teacher_permission('students.manage')?'students.manage':'students.mark';
    $exact=teacher_action_permission_exact();
    if(isset($exact[$action]))return $exact[$action];
    foreach(teacher_action_permission_prefixes() as $prefix=>$permission){if(str_starts_with($action,$prefix))return $permission;}
    return TEACHER_PERMISSION_DENY;
}

function teacher_ops_interventions_path(): string { return STORAGE_DIR.'/teacher_interventions.json.php'; }
function teacher_ops_notes_path(): string { return STORAGE_DIR.'/teacher_student_notes.json.php'; }
function teacher_ops_notifications_path(): string { return STORAGE_DIR.'/teacher_notifications.json.php'; }
function teacher_ops_automations_path(): string { return STORAGE_DIR.'/teacher_automations.json.php'; }

function teacher_ops_rows(string $path): array
{
    $rows=load_php_json($path);return is_array($rows)?$rows:[];
}
/** v58 (F2): RMW seznamu pod jedním zámkem – $fn(array $rows): array (náhrada za teacher_ops_save_rows). */
function teacher_ops_update_rows(string $path,callable $fn): array
{
    return storage_update($path,static fn(array $rows): array => array_values($fn(array_values($rows))));
}
function teacher_ops_id(string $prefix): string { return $prefix.'_'.bin2hex(random_bytes(7)); }

function teacher_ops_team_members(): array
{
    $team=teacher_team_key();$out=[];foreach(teacher_ops_rows(teacher_ops_team_members_path()) as $row){if(!is_array($row)||(string)($row['team_key']??'')!==$team)continue;$out[]=$row;}usort($out,static fn($a,$b)=>strcmp((string)($a['label']??''),(string)($b['label']??'')));return $out;
}
function teacher_ops_team_member_touch(): array
{
    $owner=teacher_saved_filter_owner_key();$team=teacher_team_key();$now=date(DATE_ATOM);$fallback=strtolower(trim(educanet_secret('teacher_role','teacher')));$label=teacher_display_name();$teamLabel=teacher_team_label();$row=[];teacher_ops_update_rows(teacher_ops_team_members_path(),static function(array $rows) use($owner,$team,$now,$fallback,$label,$teamLabel,&$row): array {$idx=null;foreach($rows as $i=>$row)if(is_array($row)&&(string)($row['owner_key']??'')===$owner){$idx=$i;break;}if(!in_array($fallback,['admin','lead','teacher','assistant'],true))$fallback='teacher';$base=$idx!==null?$rows[$idx]:[];$row=['owner_key'=>$owner,'label'=>$label,'team_key'=>$team,'team_label'=>$teamLabel,'role'=>(string)($base['role']??$fallback),'created_at'=>(string)($base['created_at']??$now),'last_seen_at'=>$now];if($idx!==null)$rows[$idx]=$row;else$rows[]=$row;return $rows;});return $row;
}
function teacher_ops_team_member_set_role(string $ownerKey,string $role): void
{
    if(function_exists('teacher59_mode')&&teacher59_mode()!=='legacy')throw new RuntimeException('Role se nastavují v záložce Učitelé.');
    teacher_require_permission('roles.manage');$role=strtolower(trim($role));if(!in_array($role,['admin','lead','teacher','assistant'],true))throw new RuntimeException('Neplatná role.');$current=teacher_role();$team=teacher_team_key();$self=teacher_saved_filter_owner_key();$by=teacher_display_name();teacher_ops_update_rows(teacher_ops_team_members_path(),static function(array $rows) use($ownerKey,$role,$current,$team,$self,$by): array {$found=false;
    foreach($rows as &$row){if(!is_array($row)||(string)($row['owner_key']??'')!==$ownerKey||(string)($row['team_key']??'')!==$team)continue;$old=(string)($row['role']??'teacher');if($current==='lead'&&(in_array($old,['admin','lead'],true)||in_array($role,['admin','lead'],true)))throw new RuntimeException('Vedoucí učitel může spravovat pouze role Učitel a Asistent.');if($ownerKey===$self&&$role==='assistant')throw new RuntimeException('Nelze si touto cestou odebrat vlastní správcovská oprávnění.');$row['role']=$role;$row['updated_at']=date(DATE_ATOM);$row['updated_by']=$by;$found=true;break;}unset($row);if(!$found)throw new RuntimeException('Člen učitelského týmu nebyl nalezen.');return $rows;});
}

function teacher_ops_class_ids(): array { $ids=array_keys(project_catalog()); return function_exists('teacher59_can_class')?array_values(array_filter($ids,static fn($c):bool=>teacher59_can_class((string)$c))):$ids; }
function teacher_ops_valid_class(string $classId): string
{
    // v59 · AUTHZ58-07: třída mimo rozsah učitele → výchozí povolená třída (nikdy cizí data); bez tříd výjimka.
    if(!function_exists('teacher59_can_class'))return isset(project_catalog()[$classId])?$classId:'class_2a';
    if(isset(project_catalog()[$classId])&&teacher59_can_class($classId))return $classId;
    $fallback=teacher59_default_class('class_2a',array_keys(project_catalog()));
    if($fallback==='')throw new RuntimeException('Nemáte přiřazené žádné třídy.');
    return $fallback;
}
function teacher_ops_student_map(string $classId): array { return project_students_for_class(teacher_ops_valid_class($classId)); }

function teacher_ops_student_row(string $classId,string $studentKey): ?array
{
    if(!function_exists('teacher_class_results_snapshot'))return null;
    $snapshot=teacher_class_results_snapshot($classId);
    foreach((array)($snapshot['students']??[]) as $row)if(is_array($row)&&(string)($row['key']??'')===$studentKey)return $row;
    return null;
}

function teacher_ops_notes(): array { return teacher_ops_rows(teacher_ops_notes_path()); }
function teacher_ops_notes_for_student(string $classId,string $studentKey): array
{
    $team=teacher_team_key();$out=[];
    foreach(teacher_ops_notes() as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;
        if((string)($row['team_key']??'')!==''&&!hash_equals((string)$row['team_key'],$team))continue;
        $out[]=$row;
    }
    usort($out,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));
    return $out;
}
function teacher_ops_note_add(string $classId,array $studentKeys,string $text,string $tag='general'): int
{
    teacher_require_permission('notes.manage');$students=teacher_ops_student_map($classId);$text=trim(u_substr($text,0,1800));
    if($text==='')throw new RuntimeException('Doplňte text poznámky.');
    $tag=in_array($tag,['general','followup','parent','support','achievement'],true)?$tag:'general';$targets=[];
    foreach($studentKeys as $key){$key=(string)$key;if(isset($students[$key]))$targets[$key]=true;}
    if(!$targets)throw new RuntimeException('Vyberte alespoň jednoho studenta.');
    $new=[];$now=date(DATE_ATOM);
    foreach(array_keys($targets) as $studentKey)$new[]=['id'=>teacher_ops_id('tn'),'class_id'=>$classId,'student_key'=>$studentKey,'tag'=>$tag,'text'=>$text,'owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'team_key'=>teacher_team_key(),'created_at'=>$now];
    teacher_ops_update_rows(teacher_ops_notes_path(),static fn(array $rows): array => array_merge($rows,$new));return count($targets);
}

function teacher_ops_intervention_rows(): array { return teacher_ops_rows(teacher_ops_interventions_path()); }
function teacher_ops_intervention_find(string $id): ?array
{
    foreach(teacher_ops_intervention_rows() as $row)if(is_array($row)&&(string)($row['id']??'')===$id)return $row;return null;
}
function teacher_ops_intervention_visible(array $row): bool
{
    $team=(string)($row['team_key']??'');if(function_exists('teacher59_can_class')&&!teacher59_can_class((string)($row['class_id']??'')))return false;return $team===''||hash_equals($team,teacher_team_key());
}
function teacher_ops_interventions_for_student(string $classId,string $studentKey,bool $includeClosed=true): array
{
    $out=[];foreach(teacher_ops_intervention_rows() as $row){
        if(!is_array($row)||!teacher_ops_intervention_visible($row)||(string)($row['class_id']??'')!==$classId)continue;
        if(!in_array($studentKey,array_map('strval',(array)($row['student_keys']??[])),true))continue;
        if(!$includeClosed&&!in_array((string)($row['status']??'active'),['draft','active','review'],true))continue;
        $out[]=$row;
    }
    usort($out,static fn($a,$b)=>strcmp((string)($b['updated_at']??''),(string)($a['updated_at']??'')));
    return $out;
}
function teacher_ops_intervention_baseline(string $classId,array $studentKeys): array
{
    $snapshot=teacher_class_results_snapshot($classId);$map=[];foreach((array)($snapshot['students']??[]) as $row)if(is_array($row))$map[(string)($row['key']??'')]=$row;
    $out=[];foreach($studentKeys as $key){$row=$map[(string)$key]??[];$summary=(array)($row['task_summary']??[]);$out[(string)$key]=[
        'project_pct'=>$row['avg_project_pct']??null,'avg_grade'=>$row['avg_grade']??null,'mastery'=>$row['mastery']??null,'overdue'=>(int)($summary['overdue']??0),'active_tasks'=>(int)($summary['active']??0),'captured_at'=>date(DATE_ATOM),
    ];}
    return $out;
}
function teacher_ops_intervention_create(string $classId,array $studentKeys,array $input): array
{
    teacher_require_permission('interventions.manage');if(function_exists('teacher_ops_intervention_input_with_template'))$input=teacher_ops_intervention_input_with_template($input);$classId=teacher_ops_valid_class($classId);$students=teacher_ops_student_map($classId);$targets=[];
    foreach($studentKeys as $key){$key=(string)$key;if(isset($students[$key]))$targets[$key]=true;}
    if(!$targets)throw new RuntimeException('Vyberte alespoň jednoho studenta.');
    $title=trim(u_substr((string)($input['intervention_title']??$input['title']??''),0,160));if($title==='')throw new RuntimeException('Doplňte název intervence.');
    $problem=trim(u_substr((string)($input['intervention_problem']??$input['problem']??''),0,1600));
    $success=trim(u_substr((string)($input['intervention_success']??$input['success_metric']??''),0,900));
    $reviewDue=trim((string)($input['intervention_review_due']??$input['review_due']??''));if($reviewDue!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$reviewDue))throw new RuntimeException('Kontrolní termín má neplatný formát.');
    $stepDefs=[
        ['type'=>'material','title'=>'Doplňující materiál','detail'=>(string)($input['intervention_material']??$input['material']??'')],
        ['type'=>'micro_task','title'=>'Mikroúkol','detail'=>(string)($input['intervention_micro_task']??$input['micro_task']??'')],
        ['type'=>'checkpoint','title'=>'Checkpoint','detail'=>(string)($input['intervention_checkpoint']??$input['checkpoint']??'')],
        ['type'=>'retry','title'=>'Nový pokus / ověření','detail'=>(string)($input['intervention_retry']??$input['retry']??'')],
    ];$steps=[];
    foreach($stepDefs as $def){$detail=trim(u_substr((string)$def['detail'],0,1200));if($detail==='')continue;$steps[]=['id'=>teacher_ops_id('is'),'type'=>$def['type'],'title'=>$def['title'],'detail'=>$detail,'status'=>'pending','updated_at'=>null,'updated_by'=>null,'note'=>''];}
    if(!$steps)$steps=[['id'=>teacher_ops_id('is'),'type'=>'checkpoint','title'=>'Checkpoint','detail'=>'Krátce ověřit pochopení a domluvit další krok.','status'=>'pending','updated_at'=>null,'updated_by'=>null,'note'=>'']];
    $now=date(DATE_ATOM);$row=['id'=>teacher_ops_id('iv'),'class_id'=>$classId,'student_keys'=>array_keys($targets),'title'=>$title,'problem'=>$problem,'success_metric'=>$success,'review_due'=>$reviewDue?:null,'status'=>'active','owner_key'=>teacher_saved_filter_owner_key(),'owner_label'=>teacher_display_name(),'team_key'=>teacher_team_key(),'baseline'=>teacher_ops_intervention_baseline($classId,array_keys($targets)),'steps'=>$steps,'timeline'=>[['id'=>teacher_ops_id('ie'),'type'=>'created','text'=>'Intervence vytvořena','by'=>teacher_display_name(),'at'=>$now]],'created_at'=>$now,'updated_at'=>$now,'completed_at'=>null];
    storage_list_push(teacher_ops_interventions_path(),$row);
    if(!empty($input['create_student_task'])){
        teacher_bulk_assign_task($classId,array_keys($targets),['task_title'=>$title,'task_instructions'=>$steps[0]['detail']??$problem,'task_priority'=>(string)($input['task_priority']??'normal'),'task_due_at'=>$reviewDue,'intervention_id'=>$row['id']]);
    }
    return $row;
}
function teacher_ops_intervention_mutate(string $id,callable $mutator): array
{
    teacher_require_permission('interventions.manage');$found=null;teacher_ops_update_rows(teacher_ops_interventions_path(),static function(array $rows) use($id,$mutator,&$found): array {
    foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if(!teacher_ops_intervention_visible($row))throw new RuntimeException('Intervence není dostupná pro tento učitelský tým.');$new=$mutator($row);if(!is_array($new))$new=$row;$new['updated_at']=date(DATE_ATOM);$rows[$i]=$new;$found=$new;break;}
    if($found===null)throw new RuntimeException('Intervence nebyla nalezena.');return $rows;});return $found;
}
function teacher_ops_intervention_step(string $id,string $stepId,string $status,string $note=''): array
{
    $status=in_array($status,['pending','done','skipped'],true)?$status:'pending';$note=trim(u_substr($note,0,900));
    return teacher_ops_intervention_mutate($id,static function(array $row)use($stepId,$status,$note):array{
        $changed=false;foreach((array)($row['steps']??[]) as $i=>$step){if(!is_array($step)||(string)($step['id']??'')!==$stepId)continue;$step['status']=$status;$step['note']=$note;$step['updated_at']=date(DATE_ATOM);$step['updated_by']=teacher_display_name();$row['steps'][$i]=$step;$changed=true;break;}if(!$changed)throw new RuntimeException('Krok intervence nebyl nalezen.');
        $row['timeline'][]=['id'=>teacher_ops_id('ie'),'type'=>'step','text'=>'Krok změněn na '.$status,'by'=>teacher_display_name(),'at'=>date(DATE_ATOM),'step_id'=>$stepId];return $row;
    });
}
function teacher_ops_intervention_status(string $id,string $status): array
{
    $status=in_array($status,['draft','active','review','completed','cancelled'],true)?$status:'active';
    return teacher_ops_intervention_mutate($id,static function(array $row)use($status):array{$row['status']=$status;$row['completed_at']=$status==='completed'?date(DATE_ATOM):null;$row['timeline'][]=['id'=>teacher_ops_id('ie'),'type'=>'status','text'=>'Stav: '.$status,'by'=>teacher_display_name(),'at'=>date(DATE_ATOM)];return $row;});
}
function teacher_ops_intervention_note(string $id,string $text): array
{
    $text=trim(u_substr($text,0,1400));if($text==='')throw new RuntimeException('Poznámka je prázdná.');
    return teacher_ops_intervention_mutate($id,static function(array $row)use($text):array{$row['timeline'][]=['id'=>teacher_ops_id('ie'),'type'=>'note','text'=>$text,'by'=>teacher_display_name(),'at'=>date(DATE_ATOM)];return $row;});
}
function teacher_ops_intervention_outcome(array $row): array
{
    $classId=(string)($row['class_id']??'');$baseline=(array)($row['baseline']??[]);$snapshot=teacher_class_results_snapshot($classId);$current=[];foreach((array)($snapshot['students']??[]) as $student)if(is_array($student))$current[(string)($student['key']??'')]=$student;
    $deltas=[];foreach((array)($row['student_keys']??[]) as $key){$key=(string)$key;$base=is_array($baseline[$key]??null)?$baseline[$key]:[];$cur=$current[$key]??[];$task=(array)($cur['task_summary']??[]);$deltas[$key]=[
        'project_delta'=>isset($base['project_pct'],$cur['avg_project_pct'])&&$base['project_pct']!==null&&$cur['avg_project_pct']!==null?(float)$cur['avg_project_pct']-(float)$base['project_pct']:null,
        'grade_delta'=>isset($base['avg_grade'],$cur['avg_grade'])&&$base['avg_grade']!==null&&$cur['avg_grade']!==null?(float)$cur['avg_grade']-(float)$base['avg_grade']:null,
        'mastery_delta'=>isset($base['mastery'],$cur['mastery'])&&$base['mastery']!==null&&$cur['mastery']!==null?(float)$cur['mastery']-(float)$base['mastery']:null,
        'overdue_delta'=>(int)($task['overdue']??0)-(int)($base['overdue']??0),
    ];}
    return $deltas;
}

function teacher_ops_student_project_events(string $classId,string $studentKey): array
{
    $events=[];$students=project_students_for_class($classId);if(!isset($students[$studentKey]))return [];
    $projects=[];foreach((array)(project_catalog()[$classId]??[]) as $p)if(is_array($p))$projects[(string)($p['id']??'')]=$p;
    foreach(project_grade_records_for_class($classId) as $record){if(!is_array($record))continue;$targets=[];
        if((string)($record['target_type']??'')==='individual')$targets=[(string)($record['target_id']??'')];else{$group=project_group_find((string)($record['target_id']??''));$targets=is_array($group)?array_map('strval',(array)($group['member_keys']??[])):[];}
        if(!in_array($studentKey,$targets,true))continue;$max=max(1,(int)($record['max_points']??1));$points=(int)($record['points']??0);$pct=max(0,min(100,$points/$max*100));$events[]=['project_id'=>(string)($record['project_id']??''),'title'=>(string)($projects[(string)($record['project_id']??'')]['title']??'Projekt'),'status'=>(string)($record['status']??'draft'),'pct'=>$pct,'grade'=>(int)($record['grade']??5),'at'=>(string)($record['updated_at']??$record['created_at']??'')];
    }
    usort($events,static fn($a,$b)=>strcmp((string)($a['at']??''),(string)($b['at']??'')));return $events;
}
function teacher_ops_student_score_drop(string $classId,string $studentKey): ?float
{
    $events=array_values(array_filter(teacher_ops_student_project_events($classId,$studentKey),static fn($e)=>(string)($e['status']??'')==='published'));if(count($events)<2)return null;$last=array_pop($events);$prev=array_map(static fn($e)=>(float)$e['pct'],$events);return (float)$last['pct']-(array_sum($prev)/count($prev));
}
function teacher_ops_days_since(int $timestamp): ?int
{
    if($timestamp<=0)return null;return max(0,(int)floor((time()-$timestamp)/86400));
}
function teacher_ops_attention_class(string $classId): array
{
    static $cache=[];
    $classId=teacher_ops_valid_class($classId);
    if(isset($cache[$classId]))return $cache[$classId];
    $snapshot=teacher_class_results_snapshot($classId);$students=[];$now=time();
    foreach((array)($snapshot['students']??[]) as $row){if(!is_array($row))continue;$key=(string)($row['key']??'');$task=(array)($row['task_summary']??[]);$drop=isset($row['score_drop'])?$row['score_drop']:teacher_ops_student_score_drop($classId,$key);$inactive=isset($row['inactive_days'])?$row['inactive_days']:teacher_ops_days_since((int)($row['last_activity']??0));$signals=[];$score=0;
        if((int)($task['overdue']??0)>0){$signals[]=['type'=>'overdue','label'=>(int)$task['overdue'].' úkolů po termínu','weight'=>5];$score+=5;}
        elseif((int)($task['due_3']??0)>0){$signals[]=['type'=>'due','label'=>'Termín do 3 dnů','weight'=>2];$score+=2;}
        if((int)($row['returned']??0)>0){$signals[]=['type'=>'returned','label'=>'Vrácená práce','weight'=>4];$score+=4;}
        if((string)($row['priority']??'normal')==='high'){$signals[]=['type'=>'priority','label'=>'Vysoká priorita','weight'=>3];$score+=3;}
        if($drop!==null&&(float)$drop<=-15){$signals[]=['type'=>'drop','label'=>'Propad '.number_format(abs((float)$drop),0,',',' ').' p. b.','weight'=>4];$score+=4;}
        if($inactive!==null&&(int)$inactive>=7){$w=(int)$inactive>=14?4:2;$signals[]=['type'=>'inactive','label'=>'Bez aktivity '.(int)$inactive.' dní','weight'=>$w];$score+=$w;}
        if(!empty($row['has_evidence'])&&$row['mastery']!==null&&(float)$row['mastery']<40){$signals[]=['type'=>'mastery','label'=>'Mastery '.number_format((float)$row['mastery'],0).'%','weight'=>2];$score+=2;}
        if((string)($row['marker']??'none')==='urgent'){$signals[]=['type'=>'marker','label'=>'Označeno urgentně','weight'=>4];$score+=4;}
        $waiting=(int)($row['returned']??0)>0||(int)($task['active']??0)>0?'student':((int)($row['drafts']??0)>0?'teacher':'none');
        $row['score_drop']=$drop;$row['inactive_days']=$inactive;$row['signals']=$signals;$row['attention_score']=$score;$row['waiting_on']=$waiting;$students[]=$row;
    }
    usort($students,static fn($a,$b)=>(int)($b['attention_score']??0)<=>(int)($a['attention_score']??0));
    $resolved=[];$flags=teacher_student_flag_map($classId);foreach($students as $row){$flag=$flags[(string)$row['key']]??null;if(is_array($flag)&&(string)($flag['marker']??'')==='resolved'&&(strtotime((string)($flag['updated_at']??''))?:0)>=$now-7*86400)$resolved[(string)$row['key']]=$row;}
    foreach(teacher_ops_intervention_rows() as $iv){if(!is_array($iv)||(string)($iv['class_id']??'')!==$classId||(string)($iv['status']??'')!=='completed'||(strtotime((string)($iv['completed_at']??''))?:0)<$now-7*86400)continue;foreach((array)($iv['student_keys']??[]) as $key){foreach($students as $row)if((string)$row['key']===(string)$key)$resolved[(string)$key]=$row;}}
    return $cache[$classId]=['class_id'=>$classId,'students'=>$students,'critical'=>array_values(array_filter($students,static fn($r)=>(int)($r['attention_score']??0)>=6)),'deadlines'=>array_values(array_filter($students,static fn($r)=>((int)($r['task_summary']['overdue']??0)>0||(int)($r['task_summary']['due_3']??0)>0))),'drops'=>array_values(array_filter($students,static fn($r)=>$r['score_drop']!==null&&(float)$r['score_drop']<=-15)),'waiting_teacher'=>array_values(array_filter($students,static fn($r)=>(string)($r['waiting_on']??'')==='teacher')),'waiting_student'=>array_values(array_filter($students,static fn($r)=>(string)($r['waiting_on']??'')==='student')),'resolved_recent'=>array_values($resolved)];
}
function teacher_ops_attention_center(?string $classId=null): array
{
    $classes=$classId!==null?[teacher_ops_valid_class($classId)]:teacher_ops_class_ids();$out=['classes'=>[],'critical'=>0,'deadlines'=>0,'drops'=>0,'waiting_teacher'=>0,'waiting_student'=>0,'resolved_recent'=>0];foreach($classes as $cid){$row=teacher_ops_attention_class($cid);$out['classes'][$cid]=$row;foreach(['critical','deadlines','drops','waiting_teacher','waiting_student','resolved_recent'] as $key)$out[$key]+=count((array)$row[$key]);}return $out;
}

function teacher_ops_match_student_identity(string $classId,array $source,string $studentKey): bool
{
    $students=project_students_for_class($classId);$student=$students[$studentKey]??null;if(!is_array($student))return false;$label=normalized_person_name((string)($student['label']??''));$email=strtolower(trim((string)($student['email']??'')));$sourceLabel=normalized_person_name((string)($source['student_label']??''));$sourceEmail=strtolower(trim((string)($source['student_email']??'')));return ($label!==''&&$sourceLabel===$label)||($email!==''&&$sourceEmail===$email);
}
function teacher_ops_student360(string $classId,string $studentKey): array
{
    $classId=teacher_ops_valid_class($classId);$students=project_students_for_class($classId);
    if(!isset($students[$studentKey]))throw new RuntimeException('Student nebyl nalezen.');
    $student=$students[$studentKey];$row=teacher_ops_student_row($classId,$studentKey)??[];
    $skillKey=skill_student_key_for_label($classId,(string)$student['label']);
    $branches=skill_branch_snapshot_map($classId,$skillKey);
    $projects=teacher_ops_student_project_events($classId,$studentKey);
    $tasks=teacher_tasks_for_student($classId,$studentKey,true);
    $notes=teacher_ops_notes_for_student($classId,$studentKey);
    $interventions=teacher_ops_interventions_for_student($classId,$studentKey,true);
    $timeline=[];
    foreach($projects as $event){$timeline[]=['type'=>'grade','at'=>(string)($event['at']??''),'title'=>(string)($event['title']??'Projekt'),'text'=>teacher_status_label((string)($event['status']??'draft')).' · '.number_format((float)($event['pct']??0),0,',',' ').' % · známka '.(int)($event['grade']??5)];}
    foreach($tasks as $task){$timeline[]=['type'=>'task','at'=>(string)($task['created_at']??''),'title'=>'Úkol: '.(string)($task['title']??''),'text'=>(string)($task['status']??'assigned')==='done'?'Dokončeno':'Přiřazeno'];if((string)($task['completed_at']??'')!=='')$timeline[]=['type'=>'task_done','at'=>(string)$task['completed_at'],'title'=>'Dokončen úkol','text'=>(string)($task['title']??'')];}
    foreach($notes as $note)$timeline[]=['type'=>'note','at'=>(string)($note['created_at']??''),'title'=>'Poznámka učitele','text'=>(string)($note['text']??'')];
    foreach($interventions as $iv){$timeline[]=['type'=>'intervention','at'=>(string)($iv['created_at']??''),'title'=>'Intervence: '.(string)($iv['title']??''),'text'=>'Stav '.(string)($iv['status']??'active')];foreach((array)($iv['timeline']??[]) as $event)if(is_array($event)&&(string)($event['type']??'')!=='created')$timeline[]=['type'=>'intervention_event','at'=>(string)($event['at']??''),'title'=>(string)($iv['title']??'Intervence'),'text'=>(string)($event['text']??'')];}
    foreach(storage_stream_rows('practice_results') as $test){if(!is_array($test)||!teacher_ops_match_student_identity($classId,$test,$studentKey))continue;$max=max(1,(int)($test['max_score']??1));$timeline[]=['type'=>'test','at'=>(string)($test['finished_at']??$test['started_at']??''),'title'=>'Startovní test','text'=>number_format(((int)($test['score']??0)/$max)*100,0,',',' ').' %'];}
    foreach(storage_stream_rows('lab_results') as $lab){if(!is_array($lab)||!teacher_ops_match_student_identity($classId,$lab,$studentKey))continue;$timeline[]=['type'=>'lab','at'=>(string)($lab['finished_at']??$lab['started_at']??''),'title'=>'Laboratoř dokončena','text'=>(int)($lab['completed_steps']??0).' kroků'];}
    usort($timeline,static fn($a,$b)=>strcmp((string)($b['at']??''),(string)($a['at']??'')));
    return ['class_id'=>$classId,'student_key'=>$studentKey,'student'=>$student,'summary'=>$row,'branches'=>$branches,'projects'=>$projects,'tasks'=>$tasks,'notes'=>$notes,'interventions'=>$interventions,'timeline'=>$timeline,'score_drop'=>teacher_ops_student_score_drop($classId,$studentKey),'misconceptions'=>ml_misconception_summary($classId,$skillKey),'confidence'=>ml_confidence_calibration($classId,$skillKey)];
}

function teacher_ops_student_interventions_public(string $classId,string $studentKey): array
{
    $out=[];foreach(teacher_ops_interventions_for_student($classId,$studentKey,false) as $row){$steps=[];foreach((array)($row['steps']??[]) as $step)if(is_array($step))$steps[]=['type'=>(string)($step['type']??''),'title'=>(string)($step['title']??''),'detail'=>(string)($step['detail']??''),'status'=>(string)($step['status']??'pending')];$out[]=['id'=>(string)$row['id'],'title'=>(string)$row['title'],'success_metric'=>(string)($row['success_metric']??''),'review_due'=>$row['review_due']??null,'status'=>(string)($row['status']??'active'),'steps'=>$steps];}return $out;
}

function teacher_ops_week_key(int $timestamp): string
{
    return date('o-\WW',$timestamp);
}
function teacher_ops_week_label(string $key): string
{
    if(!preg_match('/^(\d{4})-W(\d{2})$/',$key,$m))return $key;$d=(new DateTimeImmutable())->setISODate((int)$m[1],(int)$m[2]);return $d->format('d.m.');
}
function teacher_ops_class_analytics(string $classId): array
{
    teacher_require_permission('analytics.view');$classId=teacher_ops_valid_class($classId);$snapshot=teacher_class_results_snapshot($classId);$weeks=[];
    $now=new DateTimeImmutable('monday this week');for($i=7;$i>=0;$i--){$d=$now->modify('-'.$i.' weeks');$key=$d->format('o-\WW');$weeks[$key]=['key'=>$key,'label'=>$d->format('d.m.'),'project_sum'=>0.0,'project_n'=>0,'test_sum'=>0.0,'test_n'=>0,'labs'=>0,'learning_correct'=>0,'learning_n'=>0];}
    foreach(project_grade_records_for_class($classId) as $record){if(!is_array($record)||(string)($record['status']??'')!=='published')continue;$ts=strtotime((string)($record['updated_at']??''))?:0;$key=teacher_ops_week_key($ts);if(!isset($weeks[$key]))continue;$max=max(1,(int)($record['max_points']??1));$weeks[$key]['project_sum']+=((int)($record['points']??0)/$max)*100;$weeks[$key]['project_n']++;}
    foreach(storage_stream_rows('practice_results') as $test){if(!is_array($test)||(string)($test['class_id']??'')!==$classId)continue;$ts=strtotime((string)($test['finished_at']??''))?:0;$key=teacher_ops_week_key($ts);if(!isset($weeks[$key]))continue;$max=max(1,(int)($test['max_score']??1));$weeks[$key]['test_sum']+=((int)($test['score']??0)/$max)*100;$weeks[$key]['test_n']++;}
    foreach(storage_stream_rows('lab_results') as $lab){if(!is_array($lab)||(string)($lab['class_id']??'')!==$classId)continue;$key=teacher_ops_week_key(strtotime((string)($lab['finished_at']??''))?:0);if(isset($weeks[$key]))$weeks[$key]['labs']++;}
    foreach(ml_store('events') as $event){if(!is_array($event)||(string)($event['class_id']??'')!==$classId)continue;$key=teacher_ops_week_key(strtotime((string)($event['created_at']??''))?:0);if(!isset($weeks[$key]))continue;$weeks[$key]['learning_n']++;if(!empty($event['correct']))$weeks[$key]['learning_correct']++;}
    foreach($weeks as &$week){$week['project_avg']=$week['project_n']?round($week['project_sum']/$week['project_n'],1):null;$week['test_avg']=$week['test_n']?round($week['test_sum']/$week['test_n'],1):null;$week['learning_accuracy']=$week['learning_n']?round($week['learning_correct']/$week['learning_n']*100,1):null;unset($week['project_sum'],$week['test_sum']);}unset($week);

    $matrix=skill_class_matrix($classId);$branchSums=[];$branchNs=[];$heatmap=[];
    foreach($matrix as $student){$cells=[];foreach((array)($student['branches']??[]) as $slug=>$branch){$value=(float)($branch['mastery_percent']??0);$branchSums[$slug]=($branchSums[$slug]??0)+$value;$branchNs[$slug]=($branchNs[$slug]??0)+1;$cells[$slug]=$value;}$heatmap[]=['label'=>(string)($student['label']??''),'student_key'=>(string)($student['student_key']??''),'cells'=>$cells];}
    $branchAverages=[];foreach($branchSums as $slug=>$sum)$branchAverages[$slug]=$branchNs[$slug]?round($sum/$branchNs[$slug],1):0;
    asort($branchAverages);$weakBranches=array_slice($branchAverages,0,3,true);

    $tasks=teacher_task_rows();$taskStats=['total'=>0,'done'=>0,'active'=>0,'overdue'=>0];$today=date('Y-m-d');foreach($tasks as $task){if(!is_array($task)||(string)($task['class_id']??'')!==$classId)continue;$taskStats['total']++;if((string)($task['status']??'assigned')==='done')$taskStats['done']++;else{$taskStats['active']++;$due=(string)($task['due_at']??'');if($due!==''&&$due<$today)$taskStats['overdue']++;}}

    $first=0;$last=0;$pairs=0;$groups=[];foreach(ml_store('events') as $event){if(!is_array($event)||(string)($event['class_id']??'')!==$classId)continue;$key=(string)($event['student_key']??'').'|'.(string)($event['topic']??'').'|'.(string)($event['source']??'');$groups[$key][]=$event;}foreach($groups as $rows){if(count($rows)<2)continue;usort($rows,static fn($a,$b)=>strcmp((string)($a['created_at']??''),(string)($b['created_at']??'')));$pairs++;$first+=!empty($rows[0]['correct'])?1:0;$last+=!empty($rows[array_key_last($rows)]['correct'])?1:0;}
    $retry=['pairs'=>$pairs,'first_accuracy'=>$pairs?round($first/$pairs*100,1):null,'latest_accuracy'=>$pairs?round($last/$pairs*100,1):null,'delta'=>$pairs?round(($last-$first)/$pairs*100,1):null];

    return ['class_id'=>$classId,'snapshot'=>$snapshot,'weeks'=>array_values($weeks),'branch_averages'=>$branchAverages,'weak_branches'=>$weakBranches,'heatmap'=>$heatmap,'task_stats'=>$taskStats,'retry'=>$retry,'misconceptions'=>array_slice(ml_misconception_summary($classId),0,6)];
}

function teacher_ops_lesson_intelligence(string $classId): array
{
    teacher_require_permission('analytics.view');$classId=teacher_ops_valid_class($classId);$lessons=v42_lessons_for_class($classId);$cv=cv43_attempt_rows();$teach=v44_teachback_rows();$stages=v44_stage_rows();$sim=v45_event_rows();$totalStudents=max(1,count(project_students_for_class($classId)));$out=[];
    foreach($lessons as $lesson){if(!is_array($lesson))continue;$no=(int)($lesson['number']??0);$cvRows=array_values(array_filter($cv,static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(int)($r['lesson_number']??0)===$no));$pred=array_values(array_filter($cvRows,static fn($r)=>(string)($r['kind']??'')==='prediction'));$model=array_values(array_filter($cvRows,static fn($r)=>(string)($r['kind']??'')==='mental_model'));
        $teachRows=array_values(array_filter($teach,static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(int)($r['lesson_number']??0)===$no));$strong=count(array_filter($teachRows,static fn($r)=>in_array((string)($r['state']??''),['strong','developing'],true)));$needs=count(array_filter($teachRows,static fn($r)=>(string)($r['state']??'')==='needs_review'));
        $stageStudents=[];foreach($stages as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(int)($r['lesson_number']??0)===$no)$stageStudents[(string)($r['student_key']??'')]=true;
        $simRows=array_values(array_filter($sim,static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(int)($r['lesson_number']??0)===$no));$ready=count(array_filter($simRows,static fn($r)=>!empty($r['ready'])));$qualities=[];foreach($simRows as $r)if(isset($r['metrics']['quality']))$qualities[]=(float)$r['metrics']['quality'];
        $predAcc=$pred?count(array_filter($pred,static fn($r)=>!empty($r['correct'])))/count($pred)*100:null;$modelAcc=$model?count(array_filter($model,static fn($r)=>!empty($r['correct'])))/count($model)*100:null;$teachRate=$teachRows?$strong/count($teachRows)*100:null;$simReady=$simRows?$ready/count($simRows)*100:null;
        $friction=0;if($predAcc!==null)$friction+=max(0,70-$predAcc)*.35;if($modelAcc!==null)$friction+=max(0,70-$modelAcc)*.3;if($teachRate!==null)$friction+=max(0,75-$teachRate)*.2;if($simReady!==null)$friction+=max(0,70-$simReady)*.15;$friction=min(100,round($friction,1));
        $suggestion='Pokračovat podle plánu.';if($predAcc!==null&&$predAcc<55)$suggestion='Začněte znovu predikcí a nechte třídu vysvětlit proč.';elseif($modelAcc!==null&&$modelAcc<60)$suggestion='Vraťte se k mentálnímu modelu a porovnejte dvě varianty.';elseif($teachRows&&$needs>$strong)$suggestion='Použijte explain-back ve dvojicích a krátký checkpoint.';elseif($simReady!==null&&$simReady<60)$suggestion='Promítněte simulaci v režimu Predict → Discuss → Reveal.';
        $out[]=['lesson_number'=>$no,'title'=>(string)($lesson['title']??('Lekce '.$no)),'prediction_accuracy'=>$predAcc,'model_accuracy'=>$modelAcc,'teachback_rate'=>$teachRate,'simulation_ready'=>$simReady,'avg_sim_quality'=>$qualities?array_sum($qualities)/count($qualities):null,'engaged_students'=>count($stageStudents),'friction'=>$friction,'suggestion'=>$suggestion,'attempts'=>count($cvRows)+count($teachRows)+count($simRows),'coverage'=>min(100,round(count($stageStudents)/$totalStudents*100,1))];
    }
    usort($out,static fn($a,$b)=>(float)$b['friction']<=>(float)$a['friction']);return $out;
}

function teacher_ops_notifications(): array { return teacher_ops_rows(teacher_ops_notifications_path()); }
function teacher_ops_notifications_for_current_teacher(bool $unreadOnly=false): array
{
    $owner=teacher_saved_filter_owner_key();$out=[];foreach(teacher_ops_notifications() as $row){if(!is_array($row)||(string)($row['owner_key']??'')!==$owner)continue;if($unreadOnly&&!empty($row['read_at']))continue;if(!teacher_ops_notification_in_scope($row))continue;$out[]=$row;}usort($out,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));return $out;
}
/** v59 · SEC59-06: upozornění vázané na třídu (class_id, u starších ?class= v odkazu) jen v rozsahu učitele; legacy beze změny. */
function teacher_ops_notification_in_scope(array $row): bool
{
    if(!function_exists('teacher59_can_class'))return true;$classId=function_exists('teacher59_row_class')?teacher59_row_class($row):(string)($row['class_id']??'');return $classId===''||teacher59_can_class($classId);
}
function teacher_ops_notification_add(string $ownerKey,string $title,string $text,string $url='',string $kind='info',string $classId=''): array
{
    $row=['id'=>teacher_ops_id('nt'),'owner_key'=>$ownerKey,'class_id'=>$classId,'title'=>trim(u_substr($title,0,160)),'text'=>trim(u_substr($text,0,1000)),'url'=>trim(u_substr($url,0,500)),'kind'=>$kind,'created_at'=>date(DATE_ATOM),'read_at'=>null];teacher_ops_update_rows(teacher_ops_notifications_path(),static function(array $rows) use($row): array {$rows[]=$row;if(count($rows)>5000)$rows=array_slice($rows,-4200);return $rows;});return $row;
}
function teacher_ops_notification_read(string $id,bool $all=false): int
{
    $owner=teacher_saved_filter_owner_key();$count=0;$now=date(DATE_ATOM);teacher_ops_update_rows(teacher_ops_notifications_path(),static function(array $rows) use($owner,$id,$all,$now,&$count): array {foreach($rows as &$row){if(!is_array($row)||(string)($row['owner_key']??'')!==$owner)continue;if(!$all&&(string)($row['id']??'')!==$id)continue;if(empty($row['read_at'])){$row['read_at']=$now;$count++;}}unset($row);return $rows;});return $count;
}

function teacher_ops_automation_rows(): array { return teacher_ops_rows(teacher_ops_automations_path()); }
function teacher_ops_automations_for_current_teacher(): array
{
    $owner=teacher_saved_filter_owner_key();$out=[];foreach(teacher_ops_automation_rows() as $row)if(is_array($row)&&(string)($row['owner_key']??'')===$owner)$out[]=$row;usort($out,static fn($a,$b)=>strcmp((string)($b['updated_at']??''),(string)($a['updated_at']??'')));return $out;
}
function teacher_ops_automation_save(string $filterId,string $trigger='new_match',bool $enabled=true): array
{
    teacher_require_permission('automations.manage');$filter=null;foreach(teacher_saved_filter_rows() as $raw)if(is_array($raw)&&(string)($raw['id']??'')===$filterId&&teacher_saved_filter_visible_to_current_teacher($raw)){$filter=teacher_saved_filter_normalize_row($raw);break;}if(!$filter)throw new RuntimeException('Uložený filtr nebyl nalezen.');$trigger=in_array($trigger,['new_match','count_increase','any_change'],true)?$trigger:'new_match';$owner=teacher_saved_filter_owner_key();$now=date(DATE_ATOM);$label=teacher_display_name();$team=teacher_team_key();$row=[];teacher_ops_update_rows(teacher_ops_automations_path(),static function(array $rows) use($owner,$filterId,$filter,$trigger,$enabled,$now,$label,$team,&$row): array {$found=null;foreach($rows as $i=>$row)if(is_array($row)&&(string)($row['owner_key']??'')===$owner&&(string)($row['filter_id']??'')===$filterId){$found=$i;break;}$base=$found!==null?$rows[$found]:[];$row=['id'=>(string)($base['id']??teacher_ops_id('au')),'owner_key'=>$owner,'owner_label'=>$label,'team_key'=>$team,'filter_id'=>$filterId,'filter_name'=>(string)($filter['name']??'Filtr'),'trigger'=>$trigger,'enabled'=>$enabled,'last_student_keys'=>(array)($base['last_student_keys']??[]),'last_count'=>(int)($base['last_count']??0),'last_run_at'=>$base['last_run_at']??null,'created_at'=>(string)($base['created_at']??$now),'updated_at'=>$now];if($found!==null)$rows[$found]=$row;else$rows[]=$row;return $rows;});return $row;
}
function teacher_ops_automation_delete(string $id): void
{
    teacher_require_permission('automations.manage');$owner=teacher_saved_filter_owner_key();teacher_ops_update_rows(teacher_ops_automations_path(),static function(array $rows) use($id,$owner): array {$found=false;foreach($rows as $i=>$row){if(!is_array($row)||(string)($row['id']??'')!==$id)continue;if((string)($row['owner_key']??'')!==$owner)throw new RuntimeException('Můžete mazat pouze vlastní automatizace.');unset($rows[$i]);$found=true;break;}if(!$found)throw new RuntimeException('Automatizace nebyla nalezena.');return $rows;});
}
function teacher_ops_filter_matching_keys(array $filter): array
{
    if(!function_exists('teacher_class_results_snapshot'))require_once __DIR__.'/teacher_class_dashboard.php';$classId=(string)($filter['class_id']??'class_2a');$snapshot=teacher_class_results_snapshot($classId);$out=[];foreach((array)($snapshot['students']??[]) as $row)if(is_array($row)&&teacher_saved_filter_matches_student($filter,$row))$out[]=(string)($row['key']??'');sort($out);return $out;
}
function teacher_ops_automation_tick(?string $onlyOwner=null): array
{
    if(!function_exists('teacher_class_results_snapshot'))require_once __DIR__.'/teacher_class_dashboard.php';$automations=teacher_ops_automation_rows();$filters=[];foreach(teacher_saved_filter_rows() as $filter)if(is_array($filter))$filters[(string)($filter['id']??'')]=$filter;$changed=0;$notifications=0;$now=date(DATE_ATOM);$updates=[];
    // v59 · SEC59-06: vlastník v3 (účet) → jen filtry tříd z jeho přiřazení a jen aktivní účet; legacy vlastníci beze změny; nečitelné účty = nic.
    $ownerAccounts=function_exists('teacher59_owner_account_map')?teacher59_owner_account_map():[];if($ownerAccounts===null)return ['checked'=>0,'notifications'=>0];
    foreach($automations as $i=>$row){if(!is_array($row)||empty($row['enabled']))continue;$owner=(string)($row['owner_key']??'');if($onlyOwner!==null&&$owner!==$onlyOwner)continue;$filter=$filters[(string)($row['filter_id']??'')]??null;if(!is_array($filter))continue;if($onlyOwner!==null&&function_exists('teacher59_can_class')&&!teacher59_can_class((string)($filter['class_id']??'')))continue;$ownerAccount=$ownerAccounts[$owner]??null;if(is_array($ownerAccount)&&((string)($ownerAccount['status']??'')!=='active'||!teacher59_account_can_class($ownerAccount,(string)($filter['class_id']??''))))continue;$current=teacher_ops_filter_matching_keys($filter);$previous=array_values(array_map('strval',(array)($row['last_student_keys']??[])));sort($previous);$new=array_values(array_diff($current,$previous));$countChanged=count($current)!==(int)($row['last_count']??0);$trigger=(string)($row['trigger']??'new_match');$fire=($trigger==='new_match'&&$new)||($trigger==='count_increase'&&count($current)>(int)($row['last_count']??0))||($trigger==='any_change'&&($new||array_diff($previous,$current)));
        if($fire){$classId=(string)($filter['class_id']??'class_2a');$students=project_students_for_class($classId);$names=[];foreach(array_slice($new,0,4) as $key)$names[]=(string)($students[$key]['label']??$key);$text=count($current).' studentů nyní odpovídá filtru';if($new)$text.=' · nově: '.implode(', ',$names).(count($new)>4?'…':'');teacher_ops_notification_add($owner,'Změna ve filtru „'.(string)($filter['name']??'Filtr').'“',$text,'teacher.php?'.http_build_query(['tab'=>'class_results','class'=>$classId,'preset'=>(string)($filter['id']??'')]),'filter',$classId);$notifications++;}
        $updates[(string)($row['id']??'')]=['last_student_keys'=>$current,'last_count'=>count($current),'last_run_at'=>$now,'updated_at'=>$now];$changed++;
    }
    // v58: výsledky běhu se slučují do aktuálních dat pod zámkem (souběžná úprava automatizace se neztratí).
    if($updates)teacher_ops_update_rows(teacher_ops_automations_path(),static function(array $rows) use($updates): array {foreach($rows as $i=>$r){$rid=is_array($r)?(string)($r['id']??''):'';if($rid!==''&&isset($updates[$rid]))$rows[$i]=array_replace($r,$updates[$rid]);}return $rows;});return ['checked'=>$changed,'notifications'=>$notifications];
}

function teacher_ops_bulk_update_tasks(string $classId,array $studentKeys,array $input): int
{
    teacher_require_permission('students.manage');$selected=array_fill_keys(teacher_selected_student_keys($classId,$studentKeys),true);if(!$selected)throw new RuntimeException('Vyberte alespoň jednoho studenta.');$mode=(string)($input['bulk_task_update']??'');$count=0;$now=date(DATE_ATOM);$by=teacher_display_name();storage_update(teacher_tasks_path(),static function(array $rows) use($classId,$selected,$mode,$input,$now,$by,&$count): array {
    foreach($rows as &$task){if(!is_array($task)||(string)($task['class_id']??'')!==$classId||!isset($selected[(string)($task['student_key']??'')])||(string)($task['status']??'assigned')!=='assigned')continue;
        if($mode==='due'){$due=trim((string)($input['bulk_task_due_at']??''));if($due!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$due))throw new RuntimeException('Termín má neplatný formát.');$task['due_at']=$due?:null;$task['updated_at']=$now;$task['updated_by']=$by;$count++;}
        elseif($mode==='priority'){$priority=(string)($input['bulk_task_priority']??'normal');if(!in_array($priority,['high','normal','low'],true))$priority='normal';$task['priority']=$priority;$task['updated_at']=$now;$task['updated_by']=$by;$count++;}
        elseif($mode==='remind'){$task['reminder_count']=(int)($task['reminder_count']??0)+1;$task['last_reminded_at']=$now;$task['last_reminded_by']=$by;$count++;}
    }unset($task);if(!$count)throw new RuntimeException('Vybraní studenti nemají žádný aktivní úkol pro tuto akci.');return $rows;});return $count;
}
function teacher_ops_bulk_assign_resource(string $classId,array $studentKeys,array $input): int
{
    teacher_require_permission('students.manage');$type=(string)($input['resource_type']??'material');if(!in_array($type,['material','lesson','lab','url'],true))$type='material';$ref=trim(u_substr((string)($input['resource_ref']??''),0,500));if($ref==='')throw new RuntimeException('Doplňte odkaz nebo označení materiálu.');$title=trim((string)($input['resource_title']??''));if($title==='')$title='Doporučený materiál';return teacher_bulk_assign_task($classId,$studentKeys,['task_title'=>$title,'task_instructions'=>(string)($input['resource_instructions']??'Projdi přidělený materiál a připrav se na kontrolní checkpoint.'),'task_priority'=>(string)($input['resource_priority']??'normal'),'task_due_at'=>(string)($input['resource_due_at']??''),'resource_type'=>$type,'resource_ref'=>$ref]);
}

function teacher_ops_command_entries(): array
{
    $currentClass=teacher_ops_valid_class((string)($_GET['class']??'class_2a'));
    $entries=[
        ['label'=>'Attention Center','hint'=>'Co mám dnes řešit','url'=>'teacher.php?tab=attention','keywords'=>'dnes kritické termíny priority'],
        ['label'=>'Přehled školy','hint'=>'Všechny třídy','url'=>'teacher.php?tab=overview','keywords'=>'dashboard school'],
        ['label'=>'Analytika třídy','hint'=>'Trendy a Lesson Intelligence','url'=>'teacher.php?'.http_build_query(['tab'=>'analytics','class'=>$currentClass]),'keywords'=>'grafy trendy lekce intelligence'],
        ['label'=>'Intervence','hint'=>'Plány podpory studentů','url'=>'teacher.php?'.http_build_query(['tab'=>'interventions','class'=>$currentClass]),'keywords'=>'podpora intervention workflow'],
        ['label'=>'Cross-class report','hint'=>'Srovnání tříd a export','url'=>'teacher.php?tab=reports','keywords'=>'reporty export csv json třídy health'],
        ['label'=>'Komunikační šablony','hint'=>'Drafty pro studenty','url'=>'teacher.php?'.http_build_query(['tab'=>'communications','class'=>$currentClass]),'keywords'=>'komunikace zpráva šablona student'],
        ['label'=>'Kvalita dat','hint'=>'Integrity diagnostika','url'=>'teacher.php?tab=quality','keywords'=>'kvalita data orphan diagnostika integrity'],
        ['label'=>'Tým a role','hint'=>'Oprávnění učitelů','url'=>'teacher.php?tab=team_admin','keywords'=>'role učitel asistent admin permissions'],
        ['label'=>'Audit operací','hint'=>'Změny, bulk akce a zásahy','url'=>'teacher.php?tab=ops_audit','keywords'=>'audit historie undo změny operace'],
    ];
    foreach(teacher_ops_class_ids() as $cid){$entries[]=['label'=>'Přehled '.teacher_class_label($cid),'hint'=>'Přehled třídy','url'=>'teacher.php?tab=prehled&class='.$cid,'keywords'=>$cid.' třída'];foreach(project_students_for_class($cid) as $key=>$student)$entries[]=['label'=>(string)$student['label'],'hint'=>teacher_class_label($cid).' · Student 360°','url'=>'teacher.php?'.http_build_query(['tab'=>'student360','class'=>$cid,'student'=>(string)$key]),'keywords'=>'student '.$cid.' '.(string)($student['email']??'')];}
    return $entries;
}
