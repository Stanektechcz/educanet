<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
// v59 · SEC59-18: žádná učitelská stránka (jména žáků, hodnocení, jednorázová hesla) se neukládá do cache – před jakýmkoli výstupem.
if (PHP_SAPI !== 'cli' && !headers_sent()) { header('Cache-Control: no-store, max-age=0'); header('Pragma: no-cache'); }
require_once __DIR__ . '/one_task_v50_5.php';
require_once __DIR__ . '/teacher_operations_v46.php';
require_once __DIR__ . '/teacher_operations_plus_v46_1.php';
require_once __DIR__ . '/teacher_operations_views_v46.php';
require_once __DIR__ . '/teacher_operations_plus_views_v46_1.php';
require_once __DIR__ . '/teacher_operations_control_v46_2.php';
require_once __DIR__ . '/teacher_operations_control_views_v46_2.php';
require_once __DIR__ . '/intake_v51.php';
require_once __DIR__ . '/intake_v51_teacher.php';
require_once __DIR__ . '/tutorial_v52.php';
require_once __DIR__ . '/tutorial_v52_views.php';
require_once __DIR__ . '/tutorial_v52_teacher.php';
require_once __DIR__ . '/accounts_v53.php';
require_once __DIR__ . '/accounts_v58_views.php';
require_once __DIR__ . '/points_v53.php';
require_once __DIR__ . '/session_v53.php';
require_once __DIR__ . '/session_v53_teacher.php';
require_once __DIR__ . '/student_v55.php';
require_once __DIR__ . '/student_v55_views.php';
require_once __DIR__ . '/runtime_content.php';
require_once __DIR__ . '/learning_v56.php';
require_once __DIR__ . '/learning_v56_views.php';
// v57 · Linux Lab + Aréna (třídní závody); simulace, nic se nespouští doopravdy.
require_once __DIR__ . '/linux_v57_lab.php';
if (is_file(__DIR__ . '/arena_v57.php')) require_once __DIR__ . '/arena_v57.php';
if (is_file(__DIR__ . '/arena_v57_views.php')) require_once __DIR__ . '/arena_v57_views.php';
// v58 · registr učitelských modulů (Robotí liga, týmové hry, CTF, incidenty, analytika labu, editor, provoz).
require_once __DIR__ . '/teacher_v58.php';
// v68 · navigace cockpitu (šest sekcí, tmavý režim): logika a vykreslení rámce.
require_once __DIR__ . '/teacher_shell_v68.php';
// v59 · AUTHZ58-07: akce a pohledy učitelských účtů (knihovny účtů a rozsahu načítá bootstrap.php).
require_once __DIR__ . '/teacher_accounts_v59_admin.php';
require_once __DIR__ . '/teacher_accounts_v59_views.php';
// v61: upozornění administrátorovi z týdenní kontroly provozu (tools/v61_weekly_health.php).
require_once __DIR__ . '/ops_v61.php';
// Obsah lekcí pro učitelské přehledy (stejný zdroj jako u žáka).
$teacherRuntime = runtime_content_load_classes(array_keys($modules));
$GLOBALS['nextLessons'] = $teacherRuntime['nextLessons'];
$GLOBALS['extendedLessons'] = $teacherRuntime['extendedLessons'];
try { acc53_provision_all($modules); } catch (Throwable $accError) { error_log('EDUCANET v53 účty: ' . $accError->getMessage()); }
try { if (function_exists('identity58_ensure')) identity58_ensure(); } catch (Throwable $idError) { error_log('EDUCANET identita: ' . $idError->getMessage()); }
acc58_auto_migrate();
try { intake_v51_sync_v1($modules); } catch (Throwable $intakeSyncError) { error_log('EDUCANET v51 V1 import: ' . $intakeSyncError->getMessage()); }

$teacherAllowedTabs=['sekce','session','arena','pristupy','intake','attention','control','reports','communications','quality','overview','class_overview','class_results','analytics','student360','interventions','automations','filters','team_admin','demo_accounts','ops_audit','calendar','curriculum','teach','growth','grade','groups','workspace','skills','mastery','authoring','history','ucet'];
$teacherAllowedTabs=array_merge($teacherAllowedTabs,teacher58_tabs());
$teacherRequestTab=(string)($_GET['tab']??'attention');
$teacherRawTab=$teacherRequestTab;
if(!in_array($teacherRequestTab,$teacherAllowedTabs,true))$teacherRequestTab='attention';
$teacherRequestAction=(string)($_POST['action']??'');
teacher58_require_modules($teacherRequestTab,$teacherRequestAction);

function teacher_redirect(array $params = []): never {
    $url = 'teacher.php' . ($params ? '?' . http_build_query($params) : '');
    header('Location: ' . $url, true, 303); exit;
}
function teacher_flash(string $message, string $type = 'ok'): void {
    $_SESSION['teacher_export_flash'] = ['message'=>$message,'type'=>$type];
}
function teacher_class_label(string $classId): string {
    return match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId};
}
/** v60: krátký popis rozsahu do nadpisu přehledu – „1.A–4.A“ pro všechny čtyři třídy, jinak výčet („1.A“, „1.A, 3.A“). */
function teacher_overview_scope_label(array $classIds): string
{
    $short = [];
    foreach ($classIds as $id) {
        if (is_string($id) && preg_match('/^class_(\d)([a-z])$/', $id, $m)) $short[$m[1] . $m[2]] = $m[1] . '.' . strtoupper($m[2]);
    }
    ksort($short);
    if (count($short) >= 4) return '1.A–4.A';
    return $short === [] ? '—' : implode(', ', $short);
}

function teacher_status_label(string $status): string {
    return match($status){'published'=>'Publikováno','returned'=>'Vráceno k dopracování','draft'=>'Koncept',default=>$status};
}

function teacher_class_tab_url(string $tab, string $classId): string {
    $params=['tab'=>$tab,'class'=>$classId];
    if(in_array($tab,['class_overview','class_results'],true)){
        $status=teacher_saved_filter_status((string)($_GET['status']??'all'));
        $priority=teacher_saved_filter_priority((string)($_GET['priority']??'all'));
        $taskStatus=teacher_saved_filter_task_status((string)($_GET['task_status']??'all'));
        $taskDue=teacher_saved_filter_task_due((string)($_GET['task_due']??'all'));
        if($status!=='all')$params['status']=$status;
        if($priority!=='all')$params['priority']=$priority;
        if($taskStatus!=='all')$params['task_status']=$taskStatus;
        if($taskDue!=='all')$params['task_due']=$taskDue;
        $preset=trim((string)($_GET['preset']??'')); if($preset!=='')$params['preset']=$preset;
    }
    if(in_array($tab,['grade','groups','workspace'],true)){
        $projects=array_values((array)(project_catalog()[$classId]??[]));
        if(in_array($tab,['groups','workspace'],true)) $projects=array_values(array_filter($projects,static fn($p):bool=>is_array($p)&&(string)($p['type']??'')==='group'));
        if($projects && is_array($projects[0]) && !empty($projects[0]['id'])) $params['project']=(string)$projects[0]['id'];
    }
    return 'teacher.php?'.http_build_query($params);
}

function teacher_change_label(string $field): string {
    return match($field){
        'rubric_scores'=>'Rubrika','points'=>'Body','max_points'=>'Maximum','suggested_grade'=>'Navržená známka','grade'=>'Známka','grade_overridden'=>'Ruční známka','status'=>'Stav','teacher_comment'=>'Komentář pro studenta','strengths'=>'Silné stránky','next_step'=>'Další krok','private_note'=>'Soukromá poznámka','member_adjustments'=>'Individuální korekce',default=>$field
    };
}
function teacher_change_value(string $field, mixed $value): string {
    if ($value === null || $value === '') return '—';
    if ($field === 'status' && is_string($value)) return teacher_status_label($value);
    if ($field === 'grade_overridden') return $value ? 'ano' : 'ne';
    if ($field === 'rubric_scores' && is_array($value)) return implode(' · ', array_map(static fn($k,$v)=>$k.': '.$v, array_keys($value), array_values($value)));
    if ($field === 'member_adjustments' && is_array($value)) {
        $parts=[]; foreach($value as $member=>$row){ if(!is_array($row)) continue; $bits=[]; $delta=(int)($row['points_delta']??0); if($delta!==0)$bits[]='body '.($delta>0?'+':'').$delta; if(isset($row['grade_override'])&&$row['grade_override']!==null)$bits[]='známka '.$row['grade_override']; if(trim((string)($row['comment']??''))!=='')$bits[]='komentář'; if($bits)$parts[]=$member.' ('.implode(', ',$bits).')'; }
        return $parts ? implode(' · ',$parts) : 'bez individuálních korekcí';
    }
    if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?: '—';
    $text=trim((string)$value); return u_strlen($text)>180 ? u_substr($text,0,177).'…' : $text;
}
function teacher_record_target_label(array $record): string {
    $classId=(string)($record['class_id']??'');
    if((string)($record['target_type']??'')==='group'){
        $group=project_group_find((string)($record['target_id']??''));
        return $group ? (string)$group['name'] : 'Neznámý tým';
    }
    $students=project_students_for_class($classId);
    return (string)($students[(string)($record['target_id']??'')]['label']??'Neznámý student');
}

function teacher_require_modules(string $tab,string $action=''): void {
    $curriculumActions=['teacher_curriculum_toggle','teacher_curriculum_lesson_toggle','teacher_curriculum_note'];
    if(in_array($tab,['overview','class_overview','calendar','curriculum','teach'],true)||in_array($action,$curriculumActions,true)) require_once __DIR__.'/teacher_curriculum.php';
    if(in_array($tab,['overview','class_overview'],true)) require_once __DIR__.'/teacher_overview_dashboard.php';
    $opsTabs=['attention','control','reports','communications','quality','student360','interventions','analytics','automations','filters','team_admin','ops_audit'];
    $opsActions=['teacher_note_add','teacher_intervention_create','teacher_intervention_step','teacher_intervention_status','teacher_intervention_note','teacher_bulk_task_due','teacher_bulk_task_priority','teacher_bulk_remind','teacher_bulk_note','teacher_bulk_resource','teacher_bulk_intervention','teacher_saved_filter_pin','teacher_saved_filter_default','teacher_automation_save','teacher_automation_delete','teacher_automation_run','teacher_notification_read','teacher_notification_read_all','teacher_team_member_role','teacher_watchlist_save','teacher_watchlist_delete','teacher_followup_create','teacher_followup_update','teacher_template_save','teacher_template_delete','teacher_bulk_undo','teacher_review_ack','teacher_message_template_save','teacher_message_template_delete','teacher_ops_report_export','teacher_sla_policy_save'];
    if(in_array($tab,array_merge(['class_overview','class_results'],$opsTabs),true)||in_array($action,$opsActions,true)) require_once __DIR__.'/teacher_class_dashboard.php';
    if(in_array($tab,$opsTabs,true)||in_array($action,$opsActions,true)) require_once __DIR__.'/teacher_operations_views_v46.php';
    if($tab==='calendar') require_once __DIR__.'/teacher_calendar.php';
    if($tab==='skills') require_once __DIR__.'/teacher_skill_views.php';
    if($tab==='workspace') require_once __DIR__.'/teacher_project_workspace_views.php';
    if($tab==='demo_accounts'||str_starts_with($action,'teacher_demo_account_')) require_once __DIR__.'/teacher_demo_accounts.php';
    if(in_array($tab,['mastery','authoring'],true)) require_once __DIR__.'/mastery_learning_views_v41.php';
    $v50Actions=['v50_teacher_growth_control'];
    if(in_array($tab,['analytics','growth','teach'],true)||in_array($action,$v50Actions,true)){
        require_once __DIR__.'/hands_on_learning_v50.php';
        require_once __DIR__.'/independent_growth_v50.php';
        require_once __DIR__.'/hands_on_learning_views_v50.php';
        require_once __DIR__.'/independent_growth_views_v50.php';
    }
    if($tab==='teach') {
        require_once __DIR__.'/assessment_visuals.php';
        require_once __DIR__.'/reality_demos.php';
        require_once __DIR__.'/cognitive_visualization_views_v43.php';
        require_once __DIR__.'/learning_studio_views_v44.php';
        require_once __DIR__.'/visual_simulation_views_v45.php';
        require_once __DIR__.'/visual_practical_learning_views_v48.php';
        require_once __DIR__.'/visual_labs_3a_views_v48_1.php';
        require_once __DIR__.'/teacher_lesson_mode.php';
    }
}

if($teacherRequestAction!=='') teacher_require_modules($teacherRequestTab,$teacherRequestAction);

if (!teacher_export_configured()) {
    http_response_code(503);
    ?><!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Učitelské rozhraní</title><link rel="stylesheet" href="assets/app.css?v=46"><link rel="stylesheet" href="assets/mastery.css?v=46"><link rel="stylesheet" href="assets/cognitive-v43.css?v=46"><link rel="stylesheet" href="assets/learning-studio-v44.css?v=46"><link rel="stylesheet" href="assets/visual-simulation-v45.css?v=46"></head><body><main class="shell"><section class="card"><div class="eyebrow">První spuštění</div><h1>Učitelský přístup ještě není nastavený</h1><p>V distribučním ZIPu je bezpečný template <code>private/educanet.secrets.php</code>. Skutečný klíč se záměrně negeneruje do web rootu.</p><h2>Doporučený postup přes SSH</h2><pre><code>cd /cesta/k/EDUCANET
bash tools/install_teacher_secret.sh
php tools/check_install.php</code></pre><p>Instalátor automaticky rozpozná ISPConfig i aaPanel a uloží skutečný secret mimo veřejný web root. U ISPConfig používá adresář <code>private/</code> daného web účtu; u aaPanelu <code>/www/server/educanet/</code>.</p><p><strong>Neotvírej <code>tools/check_install.php</code> přes prohlížeč.</strong> Je to serverový diagnostický nástroj určený pouze pro SSH/CLI.</p></section></main></body></html><?php
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if ($action === 'teacher_login') {
        // v59 · AUTHZ58-07: v režimu účtů přihlašovací jméno + heslo (sdílený klíč odmítnut); v legacy beze změny níže.
        if (teacher59_mode() !== 'legacy') teacher59_handle_login();
        $key = is_string($_POST['teacher_key'] ?? null) ? $_POST['teacher_key'] : '';
        // Serverový limit pokusů (IP + bucket) – smazání cookie ho neobejde.
        // v58 · SEC58-12: žáci jsou za stejnou školní IP – limit je na IP + otisk prohlížeče (20/10 min)
        // a jen vysoký strop na samotnou IP (200/10 min); dlouhý klíč hádáním stejně neprolomí. Viz INSTALL.md.
        $teacherLoginBuckets = teacher_login_buckets();
        $teacherLoginBucket = (string)array_key_first($teacherLoginBuckets);
        if (!auth_rate_limit_check_all($teacherLoginBuckets, TEACHER_LOGIN_WINDOW)) { teacher_flash('Příliš mnoho pokusů, zkuste to za pár minut.','error'); teacher_redirect(); }
        if (hash_equals(teacher_export_key(), $key)) {
            auth_rate_limit_clear($teacherLoginBucket);
            session_regenerate_id(true);
            $_SESSION['teacher_export_authenticated'] = true;
            $name=trim((string)($_POST['teacher_name']??''));
            if($name!=='') $_SESSION['teacher_display_name']=u_substr($name,0,80);
            teacher_saved_filter_actor_token();
            teacher_ops_team_member_touch();
            teacher_redirect();
        }
        auth_rate_limit_fail_all($teacherLoginBuckets);
        teacher_flash('Nesprávný učitelský klíč.','error'); teacher_redirect();
    }
    if ($action === 'teacher_logout') {
        unset($_SESSION['teacher_export_authenticated'],$_SESSION['teacher_display_name']);
        // v58 · SEC58-15: po odhlášení nové ID session (starý identifikátor už nic neotevře).
        session_regenerate_id(true);
        teacher59_logout();
        teacher_redirect();
    }
    // v59 · AUTHZ58-07: relace s vynucenou změnou hesla smí jen změnit heslo nebo se odhlásit.
    teacher59_handle_forced_post($action);
    if (!teacher_export_authenticated()) { teacher_flash('Nejdřív se přihlaste.','error'); teacher_redirect(); }
    // v59: $modules jen s povolenými třídami – platí pro všechny handlery níže (i $GLOBALS['modules']).
    $modules = teacher59_scope_modules($modules);

    try {
        teacher59_guard_post($action);
        $requiredPermission=teacher_action_permission($action); if($requiredPermission!==null) teacher_require_permission($requiredPermission);
        teacher68_handle_post($action);
        intake_v51_teacher_handle_post($action,$modules);
        sess53_teacher_handle_post($action,$modules);
        acc58_teacher_handle_post($action,$modules);
        if((str_starts_with($action,'arena57_')||str_starts_with($action,'arena58_weekly_'))&&function_exists('arena57_teacher_handle_post'))arena57_teacher_handle_post($action,$modules);
        teacher58_handle_post($action,$modules);
        teacher59_handle_post($action);
        if($action==='teacher_demo_account_create'){
            $row=teacher_demo_account_create($modules,(string)($_POST['class_id']??''),(string)($_POST['demo_name']??''),(string)($_POST['demo_email']??''),(string)($_POST['demo_password']??''));
            teacher_demo_accounts_credentials_store($row,'create');
            teacher_flash('Demo účet „'.(string)$row['email'].'“ byl vytvořen a propojen s '.teacher_class_label((string)$row['class_id']).'.');
            teacher_redirect(['tab'=>'demo_accounts']);
        }
        if($action==='teacher_demo_account_reset'){
            $row=teacher_demo_account_reset_password((string)($_POST['demo_email']??''),(string)($_POST['demo_password']??''));
            teacher_demo_accounts_credentials_store($row,'reset');
            teacher_flash('Heslo demo účtu „'.(string)$row['email'].'“ bylo bezpečně resetováno.');
            teacher_redirect(['tab'=>'demo_accounts']);
        }
        if($action==='teacher_demo_account_delete'){
            $row=teacher_demo_account_delete((string)($_POST['demo_email']??''));
            teacher_flash('Demo účet „'.(string)$row['email'].'“ byl odstraněn. Reálné studentské účty nebyly dotčeny.');
            teacher_redirect(['tab'=>'demo_accounts']);
        }
        if($action==='v50_teacher_growth_control'){
            $v50Class=(string)($_POST['class_id']??'');$studentKey=(string)($_POST['student_key']??'');$pathId=(string)($_POST['path_id']??'');
            if(!isset(project_catalog()[$v50Class])||$studentKey===''||!isset(v50_growth_catalog()[$pathId]))throw new RuntimeException('Neplatný student nebo rozvojová cesta.');
            v50_growth_control_save($v50Class,$studentKey,$pathId,(string)($_POST['control_mode']??'recommend'),(string)($_POST['note']??''));
            teacher_flash('Dobrovolné doporučení bylo uloženo. Standardní kurikulum ani klasifikace se nemění.');teacher_redirect(['tab'=>'growth','class'=>$v50Class]);
        }
        if ($action === 'save_group') {
            $classId=(string)($_POST['class_id']??''); $projectId=(string)($_POST['project_id']??'');
            $project=project_find($classId,$projectId);
            if(!$project || (string)($project['type']??'')!=='group') throw new RuntimeException('Vyberte platný skupinový projekt.');
            $students=project_students_for_class($classId); $members=[];
            foreach((array)($_POST['member_keys']??[]) as $key) if(isset($students[(string)$key])) $members[]=(string)$key;
            if(count($members)<2) throw new RuntimeException('Tým musí mít alespoň dva studenty.');
            $name=trim((string)($_POST['group_name']??'')); if($name==='') throw new RuntimeException('Doplňte název týmu.');
            $group=project_save_group(['id'=>(string)($_POST['group_id']??''),'class_id'=>$classId,'project_id'=>$projectId,'name'=>$name,'member_keys'=>$members]);
            teacher_flash('Tým „'.$group['name'].'“ byl uložen.');
            teacher_redirect(['tab'=>'groups','class'=>$classId,'project'=>$projectId]);
        }
        if ($action === 'delete_group') {
            $groupId=(string)($_POST['group_id']??''); $group=project_group_find($groupId);
            if(!$group) throw new RuntimeException('Tým nebyl nalezen.');
            foreach(project_grade_records() as $record){ if(is_array($record) && (string)($record['target_type']??'')==='group' && (string)($record['target_id']??'')===$groupId) throw new RuntimeException('Tým už má hodnocení. Nejdřív jej ponechte kvůli historii; členy můžete upravit.'); }
            project_delete_group($groupId); teacher_flash('Tým byl odstraněn.');
            teacher_redirect(['tab'=>'groups','class'=>(string)$group['class_id'],'project'=>(string)$group['project_id']]);
        }
        if ($action === 'save_grade') {
            $classId=(string)($_POST['class_id']??''); $projectId=(string)($_POST['project_id']??'');
            $project=project_find($classId,$projectId); if(!$project) throw new RuntimeException('Projekt nebyl nalezen.');
            $targetType=(string)($project['type']??'individual'); $targetId=(string)($_POST['target_id']??'');
            $scores=[]; foreach((array)($_POST['rubric']??[]) as $k=>$v) if(is_scalar($v)) $scores[(string)$k]=(int)$v;
            $memberAdjustments=[];
            foreach((array)($_POST['member_delta']??[]) as $memberKey=>$delta){
                $override=(array)($_POST['member_grade']??[]); $comments=(array)($_POST['member_comment']??[]);
                $memberAdjustments[(string)$memberKey]=['points_delta'=>(int)$delta,'grade_override'=>isset($override[$memberKey])&&$override[$memberKey]!==''?(int)$override[$memberKey]:null,'comment'=>(string)($comments[$memberKey]??'')];
            }
            $record=project_save_grade([
                'class_id'=>$classId,'project_id'=>$projectId,'target_type'=>$targetType,'target_id'=>$targetId,'rubric_scores'=>$scores,
                'grade'=>($_POST['grade']??'')!==''?(int)$_POST['grade']:null,'status'=>(string)($_POST['status']??'draft'),
                'teacher_comment'=>(string)($_POST['teacher_comment']??''),'strengths'=>(string)($_POST['strengths']??''),'next_step'=>(string)($_POST['next_step']??''),'private_note'=>(string)($_POST['private_note']??''),
                'member_adjustments'=>$memberAdjustments,
            ]);
            teacher_flash($record['status']==='published'?'Hodnocení bylo uloženo a zpřístupněno studentovi.':($record['status']==='returned'?'Práce byla vrácena k dopracování a student uvidí zpětnou vazbu.':'Koncept hodnocení byl uložen.'));
            teacher_redirect(['tab'=>'grade','class'=>$classId,'project'=>$projectId,'target'=>$targetId]);
        }

        if ($action === 'teacher_saved_filter_save') {
            $classId=(string)($_POST['class_id']??'class_2a');
            if((string)($_POST['filter_scope']??'personal')==='team') teacher_require_permission('team_filters.manage');
            $row=teacher_saved_filter_save($_POST);
            teacher_flash((string)$row['scope']==='team'?'Týmový filtr „'.(string)$row['name'].'“ byl sdílen s '.teacher_team_label().'.':'Osobní filtr „'.(string)$row['name'].'“ byl uložen.');
            $returnTab=(string)($_POST['return_tab']??'class_results');
            if(!in_array($returnTab,['class_overview','class_results','filters'],true))$returnTab='class_results';
            teacher_redirect(['tab'=>$returnTab,'class'=>(string)$row['class_id'],'status'=>(string)$row['status'],'priority'=>(string)$row['priority'],'task_status'=>(string)($row['task_status']??'all'),'task_due'=>(string)($row['task_due']??'all')]);
        }
        if ($action === 'teacher_saved_filter_delete') {
            $classId=(string)($_POST['class_id']??'class_2a');
            teacher_saved_filter_delete((string)($_POST['filter_id']??''));
            teacher_flash('Vlastní uložený filtr byl odstraněn.');
            $returnTab=(string)($_POST['return_tab']??'class_results');
            if(!in_array($returnTab,['class_overview','class_results','filters'],true))$returnTab='class_results';
            teacher_redirect(['tab'=>$returnTab,'class'=>$classId,'status'=>teacher_saved_filter_status((string)($_POST['filter_status']??'all')),'priority'=>teacher_saved_filter_priority((string)($_POST['filter_priority']??'all')),'task_status'=>teacher_saved_filter_task_status((string)($_POST['filter_task_status']??'all')),'task_due'=>teacher_saved_filter_task_due((string)($_POST['filter_task_due']??'all'))]);
        }

        if ($action === 'teacher_bulk_mark') {
            $classId=(string)($_POST['class_id']??'class_2a');
            $keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);
            $result=teacher_ops_bulk_mark_with_undo($classId,$keys,(string)($_POST['marker']??'none'));
            teacher_flash('Označení bylo aktualizováno u '.(int)$result['count'].' studentů. Bezpečné Undo je 15 minut dostupné v Dnes.');
            teacher_redirect(['tab'=>'class_results','class'=>$classId]);
        }
        if ($action === 'teacher_bulk_export') {
            $classId=(string)($_POST['class_id']??'class_2a');
            $keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);
            teacher_class_export_csv($classId,$keys);
        }
        if ($action === 'teacher_bulk_assign_task') {
            $classId=(string)($_POST['class_id']??'class_2a');
            $keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);
            $result=teacher_ops_bulk_assign_task_with_undo($classId,$keys,$_POST);
            teacher_flash('Úkol byl přiřazen '.(int)$result['count'].' studentům. Bezpečné Undo je 15 minut dostupné v Dnes.');
            teacher_redirect(['tab'=>'class_results','class'=>$classId]);
        }

        if ($action === 'teacher_note_add') {
            $classId=(string)($_POST['class_id']??'class_2a');$keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);$count=teacher_ops_note_add($classId,$keys,(string)($_POST['note_text']??''),(string)($_POST['note_tag']??'general'));teacher_flash('Týmová poznámka byla přidána '.$count.' studentům.');$params=['tab'=>(string)($_POST['return_tab']??'class_results'),'class'=>$classId];if(!empty($_POST['return_student']))$params['student']=(string)$_POST['return_student'];teacher_redirect($params);
        }
        if ($action === 'teacher_intervention_create') {
            $classId=(string)($_POST['class_id']??'class_2a');$keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);$row=teacher_ops_intervention_create($classId,$keys,$_POST);teacher_ops_audit_event('intervention.create','intervention',(string)$row['id'],$classId,'Intervence vytvořena',['students'=>count($keys)]);teacher_flash('Intervence „'.(string)$row['title'].'“ byla vytvořena.');teacher_redirect(['tab'=>'interventions','class'=>$classId,'intervention'=>(string)$row['id']]);
        }
        if ($action === 'teacher_intervention_step') {
            $classId=(string)($_POST['class_id']??'class_2a');$row=teacher_ops_intervention_step((string)($_POST['intervention_id']??''),(string)($_POST['step_id']??''),(string)($_POST['step_status']??'pending'),(string)($_POST['step_note']??''));teacher_ops_audit_event('intervention.step','intervention',(string)$row['id'],$classId,'Krok intervence aktualizován');teacher_flash('Krok intervence byl aktualizován.');teacher_redirect(['tab'=>'interventions','class'=>$classId,'intervention'=>(string)$row['id']]);
        }
        if ($action === 'teacher_intervention_status') {
            $classId=(string)($_POST['class_id']??'class_2a');$row=teacher_ops_intervention_status((string)($_POST['intervention_id']??''),(string)($_POST['intervention_status']??'active'));teacher_ops_audit_event('intervention.status','intervention',(string)$row['id'],$classId,'Stav intervence změněn',['status'=>$row['status']??'']);teacher_flash('Stav intervence byl změněn.');teacher_redirect(['tab'=>'interventions','class'=>$classId,'intervention'=>(string)$row['id']]);
        }
        if ($action === 'teacher_intervention_note') {
            $classId=(string)($_POST['class_id']??'class_2a');$row=teacher_ops_intervention_note((string)($_POST['intervention_id']??''),(string)($_POST['intervention_note']??''));teacher_ops_audit_event('intervention.note','intervention',(string)$row['id'],$classId,'Poznámka k intervenci přidána');teacher_flash('Poznámka k intervenci byla přidána.');teacher_redirect(['tab'=>'interventions','class'=>$classId,'intervention'=>(string)$row['id']]);
        }
        if (in_array($action,['teacher_bulk_task_due','teacher_bulk_task_priority','teacher_bulk_remind'],true)) {
            $classId=(string)($_POST['class_id']??'class_2a');$keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);$_POST['bulk_task_update']=$action==='teacher_bulk_task_due'?'due':($action==='teacher_bulk_task_priority'?'priority':'remind');$result=teacher_ops_bulk_update_tasks_with_undo($classId,$keys,$_POST);teacher_flash('Aktualizováno '.(int)$result['count'].' aktivních úkolů. Bezpečné Undo je 15 minut dostupné v Dnes.');teacher_redirect(['tab'=>'class_results','class'=>$classId]);
        }
        if ($action === 'teacher_bulk_note') {
            $classId=(string)($_POST['class_id']??'class_2a');$keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);$result=teacher_ops_bulk_note_with_undo($classId,$keys,(string)($_POST['note_text']??''),(string)($_POST['note_tag']??'followup'));teacher_flash('Poznámka byla přidána '.(int)$result['count'].' studentům. Bezpečné Undo je 15 minut dostupné v Dnes.');teacher_redirect(['tab'=>'class_results','class'=>$classId]);
        }
        if ($action === 'teacher_bulk_resource') {
            $classId=(string)($_POST['class_id']??'class_2a');$keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);$result=teacher_ops_bulk_resource_with_undo($classId,$keys,$_POST);teacher_flash('Materiál byl přiřazen '.(int)$result['count'].' studentům. Bezpečné Undo je 15 minut dostupné v Dnes.');teacher_redirect(['tab'=>'class_results','class'=>$classId]);
        }
        if ($action === 'teacher_bulk_intervention') {
            $classId=(string)($_POST['class_id']??'class_2a');$keys=teacher_selected_student_keys($classId,$_POST['student_keys']??[]);$result=teacher_ops_bulk_intervention_with_undo($classId,$keys,$_POST);$row=$result['row'];teacher_flash('Skupinová intervence byla vytvořena. Bezpečné Undo je 15 minut dostupné v Dnes.');teacher_redirect(['tab'=>'interventions','class'=>$classId,'intervention'=>(string)$row['id']]);
        }
        if ($action === 'teacher_saved_filter_pin') { teacher_saved_filter_set_pin((string)($_POST['filter_id']??''),(string)($_POST['pinned']??'0')==='1'); teacher_flash('Připnutí filtru bylo aktualizováno.'); teacher_redirect(['tab'=>'filters','class'=>(string)($_POST['class_id']??'class_2a')]); }
        if ($action === 'teacher_saved_filter_default') { teacher_saved_filter_set_default((string)($_POST['filter_id']??''),(string)($_POST['default_for']??'none')); teacher_flash('Výchozí filtr byl aktualizován.'); teacher_redirect(['tab'=>'filters','class'=>(string)($_POST['class_id']??'class_2a')]); }
        if ($action === 'teacher_automation_save') { $row=teacher_ops_automation_save((string)($_POST['filter_id']??''),(string)($_POST['automation_trigger']??'new_match'),!isset($_POST['automation_enabled'])||(string)$_POST['automation_enabled']==='1'); teacher_flash('Automatizace byla uložena.'); teacher_redirect(['tab'=>'automations']); }
        if ($action === 'teacher_automation_delete') { teacher_ops_automation_delete((string)($_POST['automation_id']??'')); teacher_flash('Automatizace byla odstraněna.'); teacher_redirect(['tab'=>'automations']); }
        if ($action === 'teacher_automation_run') { $result=teacher_ops_automation_tick(teacher_saved_filter_owner_key()); teacher_flash('Kontrola dokončena: '.(int)$result['checked'].' pravidel, '.(int)$result['notifications'].' nových upozornění.'); teacher_redirect(['tab'=>'automations']); }
        if ($action === 'teacher_notification_read' || $action === 'teacher_notification_read_all') { teacher_ops_notification_read((string)($_POST['notification_id']??''),$action==='teacher_notification_read_all'); teacher_redirect(['tab'=>'automations']); }

        if ($action === 'teacher_watchlist_save') { $classId=(string)($_POST['class_id']??'class_2a');$studentKey=(string)($_POST['student_key']??'');teacher_ops_watchlist_save($classId,$studentKey,(string)($_POST['watchlist_scope']??'personal'),(string)($_POST['watchlist_reason']??''),(string)($_POST['watchlist_priority']??'normal'));teacher_flash('Watchlist byl aktualizován.');teacher_redirect(['tab'=>'student360','class'=>$classId,'student'=>$studentKey]); }
        if ($action === 'teacher_watchlist_delete') { $classId=(string)($_POST['class_id']??'class_2a');$studentKey=(string)($_POST['student_key']??'');teacher_ops_watchlist_delete((string)($_POST['watchlist_id']??''));teacher_flash('Vlastní watchlist záznam byl odebrán.');teacher_redirect(['tab'=>'student360','class'=>$classId,'student'=>$studentKey]); }
        if ($action === 'teacher_followup_create') { $row=teacher_ops_followup_save($_POST);teacher_flash('Follow-up byl přidán do fronty.');teacher_redirect(['tab'=>'student360','class'=>(string)$row['class_id'],'student'=>(string)($row['student_key']??'')]); }
        if ($action === 'teacher_followup_update') { $row=teacher_ops_followup_update((string)($_POST['followup_id']??''),(string)($_POST['followup_mode']??'done'));teacher_flash('Follow-up byl aktualizován.');$rt=(string)($_POST['return_tab']??'attention');teacher_redirect(['tab'=>in_array($rt,['attention','student360'],true)?$rt:'attention','class'=>(string)$row['class_id'],'student'=>(string)($row['student_key']??'')]); }
        if ($action === 'teacher_template_save') { teacher_ops_template_save($_POST);teacher_flash('Intervenční šablona byla uložena.');teacher_redirect(['tab'=>'interventions','class'=>(string)($_POST['class_id']??'class_2a')]); }
        if ($action === 'teacher_template_delete') { teacher_ops_template_delete((string)($_POST['template_id']??''));teacher_flash('Vlastní intervenční šablona byla odstraněna.');teacher_redirect(['tab'=>'interventions','class'=>(string)($_POST['class_id']??'class_2a')]); }
        if ($action === 'teacher_bulk_undo') { $row=teacher_ops_undo_execute((string)($_POST['undo_id']??''));teacher_flash('Hromadná akce byla bezpečně vrácena.');$rt=(string)($_POST['return_tab']??'attention');teacher_redirect(['tab'=>in_array($rt,['attention','class_results'],true)?$rt:'attention','class'=>(string)($row['class_id']??'class_2a')]); }

        if ($action === 'teacher_review_ack') { $raw=(string)($_POST['class_id']??'all');teacher_ops_review_ack($raw==='all'?null:$raw);teacher_flash('Aktuální provozní stav byl uložen jako nový kontrolní baseline.');teacher_redirect(['tab'=>'control']+($raw==='all'?[]:['class'=>$raw])); }
        if ($action === 'teacher_message_template_save') { teacher_ops_message_template_save($_POST);teacher_flash('Komunikační šablona byla uložena.');teacher_redirect(['tab'=>'communications','class'=>(string)($_POST['class_id']??'class_2a')]); }
        if ($action === 'teacher_message_template_delete') { teacher_ops_message_template_delete((string)($_POST['template_id']??''));teacher_flash('Vlastní komunikační šablona byla odstraněna.');teacher_redirect(['tab'=>'communications','class'=>(string)($_POST['class_id']??'class_2a')]); }
        if ($action === 'teacher_ops_report_export') { teacher_ops_report_export((string)($_POST['report_format']??'csv')); }
        if ($action === 'teacher_sla_policy_save') { teacher_ops_sla_policy_save($_POST);teacher_flash('Týmová SLA politika byla aktualizována.');teacher_redirect(['tab'=>'control']); }

        if ($action === 'teacher_team_member_role') { $member=(string)($_POST['member_owner_key']??'');$role=(string)($_POST['member_role']??'teacher');teacher_ops_team_member_set_role($member,$role);teacher_ops_audit_event('team.role','teacher_actor',$member,'','Role člena učitelského týmu změněna',['role'=>$role]); teacher_flash('Role člena týmu byla aktualizována.'); teacher_redirect(['tab'=>'team_admin']); }

        if ($action === 'v48_orchestrate') {
            $classId=(string)($_POST['class_id']??'class_2a');$lessonNo=max(1,min(28,(int)($_POST['lesson_number']??1)));
            if(!isset($modules[$classId]))throw new RuntimeException('Neplatná třída.');
            $lesson=v42_find_lesson($classId,$lessonNo);if(!$lesson)throw new RuntimeException('Lekce nebyla nalezena.');
            $mode=(string)($_POST['mode']??'phase');$phase=(string)($_POST['phase']??'predict');v48_orchestration_update($classId,$lesson,$mode,$phase);
            teacher_flash($mode==='stop'?'Živá v48 sekvence byla ukončena.':'Fáze živé v48 sekvence byla aktualizována.');teacher_redirect(['tab'=>'teach','class'=>$classId,'lesson'=>$lessonNo]);
        }
        if ($action === 'project_teacher_role_assign') {
            $classId=(string)($_POST['class_id']??'');$projectId=(string)($_POST['project_id']??'');$groupId=(string)($_POST['group_id']??'');$studentKey=(string)($_POST['student_key']??'');
            project_role_assign($classId,$projectId,$groupId,$studentKey,(string)($_POST['role']??''),(string)($_POST['role_type']??'secondary'),'teacher');
            teacher_flash('Projektová role byla studentovi přidána.');
            teacher_redirect(['tab'=>'workspace','class'=>$classId,'project'=>$projectId,'group'=>$groupId]);
        }
        if ($action === 'project_teacher_role_remove') {
            $classId=(string)($_POST['class_id']??'');$projectId=(string)($_POST['project_id']??'');$groupId=(string)($_POST['group_id']??'');$studentKey=(string)($_POST['student_key']??'');
            project_role_remove($classId,$projectId,$groupId,$studentKey,(string)($_POST['role']??''),'teacher');
            teacher_flash('Projektová role byla odebrána.');
            teacher_redirect(['tab'=>'workspace','class'=>$classId,'project'=>$projectId,'group'=>$groupId]);
        }
        if ($action === 'project_teacher_retro') {
            $classId=(string)($_POST['class_id']??'');$projectId=(string)($_POST['project_id']??'');$groupId=(string)($_POST['group_id']??'');
            project_teacher_set_retro($groupId,(string)($_POST['retro_status']??'closed'));
            teacher_flash((string)($_POST['retro_status']??'')==='open'?'Retrospektiva byla otevřena.':'Retrospektiva byla uzavřena.');
            teacher_redirect(['tab'=>'workspace','class'=>$classId,'project'=>$projectId,'group'=>$groupId]);
        }
        if ($action === 'project_teacher_peer_moderate') {
            $classId=(string)($_POST['class_id']??'');$projectId=(string)($_POST['project_id']??'');$groupId=(string)($_POST['group_id']??'');
            project_teacher_moderate_feedback((string)($_POST['feedback_id']??''),(string)($_POST['moderation']??'hidden'));
            teacher_flash('Peer feedback byl zkontrolován.');
            teacher_redirect(['tab'=>'workspace','class'=>$classId,'project'=>$projectId,'group'=>$groupId]);
        }
        if ($action === 'project_teacher_role_evaluation') {
            $classId=(string)($_POST['class_id']??'');$projectId=(string)($_POST['project_id']??'');$groupId=(string)($_POST['group_id']??'');$studentKey=(string)($_POST['student_key']??'');$role=(string)($_POST['role']??'');
            $row=project_teacher_save_role_evaluation($classId,$projectId,$groupId,$studentKey,$role,$_POST);
            teacher_flash((string)$row['status']==='published'?'Hodnocení role bylo publikováno a relevantní Skill Evidence přepočítána.':'Koncept hodnocení role byl uložen.');
            teacher_redirect(['tab'=>'workspace','class'=>$classId,'project'=>$projectId,'group'=>$groupId,'student'=>$studentKey,'role'=>$role]);
        }
        if ($action === 'skill_validation') {
            $classId=(string)($_POST['class_id']??'class_2a');
            $state=(string)($_POST['state']??'rejected');
            skill_update_evidence_state((string)($_POST['evidence_id']??''),$state,(string)($_POST['reason']??''));
            teacher_flash($state==='approved'?'Mastery evidence byla schválena.':($state==='revoked'?'Evidence byla revokována.':'Evidence byla vrácena studentovi.'));
            teacher_redirect(['tab'=>'skills','class'=>$classId]);
        }
        if ($action === 'skill_teacher_evidence') {
            $classId=(string)($_POST['class_id']??'class_2a');$studentKey=(string)($_POST['student_key']??'');$slug=(string)($_POST['skill']??'');
            skill_teacher_add_evidence($classId,$studentKey,$slug,(string)($_POST['evidence_type']??'lab'),(float)($_POST['score']??100),(string)($_POST['note']??''));
            teacher_flash('Validovaná Mastery Evidence byla přidána a progress přepočítán.');
            teacher_redirect(['tab'=>'skills','class'=>$classId,'student'=>$studentKey,'skill'=>$slug]);
        }
        if ($action === 'skill_assign') {
            $classId=(string)($_POST['class_id']??'class_2a');
            $targetType=(string)($_POST['target_type']??'class');
            $targetKey=(string)($_POST['target_key']??'');
            $row=skill_teacher_assign(
                $classId,
                (string)($_POST['skill']??''),
                $targetType,
                $targetKey,
                (string)($_POST['assignment_type']??'recommended'),
                trim((string)($_POST['due_at']??'')) ?: null
            );
            teacher_flash(($row['assignment_type']==='required'?'Povinný':'Doporučený').' skill byl zadán '.($targetType==='student'?'studentovi':'celé třídě').'.');
            $redirect=['tab'=>'skills','class'=>$classId];
            if($targetType==='student'&&$targetKey!=='')$redirect['student']=$targetKey;
            teacher_redirect($redirect);
        }
        if ($action === 'skill_unassign') {
            $classId=(string)($_POST['class_id']??'class_2a');
            skill_teacher_unassign((string)($_POST['assignment_id']??''));
            teacher_flash('Skill Assignment byl zrušen.');
            teacher_redirect(['tab'=>'skills','class'=>$classId]);
        }
        if ($action === 'teacher_curriculum_toggle') {
            $classId=(string)($_POST['class_id']??'class_2a');
            teacher_curriculum_toggle_item($classId,(string)($_POST['item_id']??''),(string)($_POST['checked']??'0')==='1');
            teacher_flash('Checklist byl aktualizován.');
            teacher_redirect(['tab'=>'curriculum','class'=>$classId]);
        }
        if ($action === 'teacher_curriculum_lesson_toggle') {
            $classId=(string)($_POST['class_id']??'class_2a');
            teacher_curriculum_toggle_lesson($classId,(int)($_POST['lesson_number']??0),(string)($_POST['checked']??'0')==='1');
            teacher_flash('Stav přípravy lekce byl aktualizován.');
            teacher_redirect(['tab'=>'curriculum','class'=>$classId]);
        }
        if ($action === 'teacher_curriculum_note') {
            $classId=(string)($_POST['class_id']??'class_2a');
            teacher_curriculum_save_note($classId,(string)($_POST['note']??''));
            teacher_flash('Učitelská poznámka byla uložena.');
            teacher_redirect(['tab'=>'curriculum','class'=>$classId]);
        }
        if ($action === 'v42_lesson_resource_save') {
            $classId=(string)($_POST['class_id']??'class_2a');$lessonNumber=(int)($_POST['lesson_number']??0);
            $lesson=v42_find_lesson($classId,$lessonNumber);if(!$lesson) throw new RuntimeException('Lekce nebyla nalezena.');
            v42_teacher_resource_save($classId,$lesson,(string)($_POST['video_url']??''),(string)($_POST['video_title']??''),(string)($_POST['video_note']??''));
            teacher_flash('Doporučené video pro lekci bylo uloženo.');
            teacher_redirect(['tab'=>'teach','class'=>$classId,'lesson'=>$lessonNumber]);
        }
        if ($action === 'v42_lesson_resource_remove') {
            $classId=(string)($_POST['class_id']??'class_2a');$lessonNumber=(int)($_POST['lesson_number']??0);
            $lesson=v42_find_lesson($classId,$lessonNumber);if(!$lesson) throw new RuntimeException('Lekce nebyla nalezena.');
            v42_teacher_resource_remove($classId,$lesson);
            teacher_flash('Vlastní video bylo odebráno; lekce znovu používá kurátorované zdroje.');
            teacher_redirect(['tab'=>'teach','class'=>$classId,'lesson'=>$lessonNumber]);
        }
        if ($action === 'teacher_calendar_exception_save') {
            $classId=(string)($_POST['class_id']??'class_2a');
            adaptive_calendar_exception_save($classId,(string)($_POST['date']??''),(string)($_POST['type']??'note'),(string)($_POST['title']??''),(string)($_POST['note']??''));
            teacher_flash('Výjimka v kalendáři byla uložena.');
            teacher_redirect(['tab'=>'calendar']);
        }
        if ($action === 'teacher_calendar_exception_remove') {
            $classId=(string)($_POST['class_id']??'class_2a');
            adaptive_calendar_exception_remove($classId,(string)($_POST['date']??''));
            teacher_flash('Výjimka byla z kalendáře odebrána.');
            teacher_redirect(['tab'=>'calendar']);
        }
        if ($action === 'ml_live_start') {
            $classId=(string)($_POST['class_id']??'class_2a');
            ml_live_start($classId,(string)($_POST['question']??''),[(string)($_POST['option_0']??''),(string)($_POST['option_1']??''),(string)($_POST['option_2']??'')],(int)($_POST['correct']??-1));
            teacher_flash('Živá otázka byla spuštěna.');
            teacher_redirect(['tab'=>'mastery','class'=>$classId]);
        }
        if ($action === 'ml_live_phase') {
            $classId=(string)($_POST['class_id']??'class_2a');
            ml_live_set_phase((string)($_POST['live_id']??''),(string)($_POST['phase']??'closed'));
            teacher_redirect(['tab'=>'mastery','class'=>$classId]);
        }
        if ($action === 'ml_scenario_save') {
            $classId=(string)($_POST['class_id']??'class_2a');
            ml_scenario_save($_POST,true);
            teacher_flash('Scénář byl uložen.');
            teacher_redirect(['tab'=>'authoring','class'=>$classId]);
        }
        if ($action === 'ml_scenario_state') {
            $classId=(string)($_POST['class_id']??'class_2a'); ml_scenario_set_status((string)($_POST['scenario_id']??''),(string)($_POST['status']??'draft')); teacher_flash('Stav scénáře byl změněn.'); teacher_redirect(['tab'=>'authoring','class'=>$classId]);
        }
        if ($action === 'ml_failure_inject') {
            $classId=(string)($_POST['class_id']??'class_2a'); ml_failure_inject($classId); teacher_flash('Bezpečný náhodný problém byl spuštěn jako živá otázka.'); teacher_redirect(['tab'=>'mastery','class'=>$classId]);
        }
        if ($action === 'ml_tip_state') {
            $classId=(string)($_POST['class_id']??'class_2a'); ml_tip_set_status((string)($_POST['tip_id']??''),(string)($_POST['status']??'pending')); teacher_flash('Mikrotip byl zmoderován.'); teacher_redirect(['tab'=>'authoring','class'=>$classId]);
        }
    } catch (Throwable $e) {
        // v59 · SEC59-16: text jen u RuntimeException (hlášky pro uživatele); jiné chyby (TypeError, I/O s cestami) jen do logu.
        if (!$e instanceof RuntimeException) error_log('EDUCANET teacher.php akce ' . preg_replace('/[^a-z0-9_]/', '', $action) . ': ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
        teacher_flash($e instanceof RuntimeException ? $e->getMessage() : 'Akci se nepodařilo dokončit.','error');
        teacher_redirect(['tab'=>(string)($_POST['return_tab']??'overview'),'class'=>teacher59_redirect_class(is_string($_POST['class_id']??null)?$_POST['class_id']:'class_2a'),'project'=>(string)($_POST['project_id']??'')]);
    }
}

$rawFlash=$_SESSION['teacher_export_flash']??null; unset($_SESSION['teacher_export_flash']);
$flash=is_array($rawFlash)?$rawFlash:($rawFlash?['message'=>(string)$rawFlash,'type'=>'error']:null);
$authenticated=teacher_export_authenticated();
// v59 · AUTHZ58-07: nečitelné úložiště účtů → 503, vynucená změna hesla → jen její stránka, pak rozsah tříd a guard GET.
teacher59_guard_forced_get($flash);
if($authenticated){$modules=teacher59_scope_modules($modules);teacher59_guard_get($teacherRequestTab);teacher68_redirect_legacy_tab($teacherRawTab,$_GET);if($teacherRawTab!==$teacherRequestTab&&teacher58_is_admin_denied($teacherRawTab))teacher59_deny('admin_only');if(teacher59_mode()!=='legacy'&&teacher59_allowed_class_ids()===[]&&!in_array($teacherRequestTab,['ucet','ucitele'],true))teacher59_render_no_classes();}
if($authenticated)teacher_saved_filter_actor_token();
if($authenticated)intake_v51_teacher_handle_get($modules);
$teacherNeedsSimulationAssets=$authenticated && $teacherRequestTab==='teach';
if($authenticated&&$teacherRequestTab==='teach'&&(string)($_GET['v48_state']??'')==='1'){
    $pollClass=(string)($_GET['class']??'class_2a');$pollLesson=max(1,min(28,(int)($_GET['lesson']??1)));
    if(!isset($modules[$pollClass])){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false]);exit;}
    $lesson=v42_find_lesson($pollClass,$pollLesson);if(!$lesson){http_response_code(404);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false]);exit;}
    $summary=v48_teacher_summary($pollClass,$pollLesson);$spec=v48_lab_spec($pollClass,$lesson,$modules[$pollClass]);$phase=(string)($summary['state']['phase']??'predict');$summary['state']['label']=(string)($spec['teacher']['phases'][$phase]['label']??$phase);if(function_exists('v481_teacher_summary')&&$pollClass==='class_3a')$summary['deep']=v481_teacher_summary($pollClass,$pollLesson);
    header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'summary'=>$summary],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
}
if($authenticated&&$teacherRequestTab==='pristupy'&&isset($_GET['print'])){acc58_render_cards_print((string)($_GET['class']??''),$modules);exit;}
if($authenticated&&$teacherRequestTab==='arena'&&isset($_GET['arena_poll'])&&function_exists('arena57_teacher_poll')){arena57_teacher_poll();exit;}
if($authenticated&&$teacherRequestTab==='arena'&&isset($_GET['hadanka_poll'])&&function_exists('arena58_weekly_teacher_poll')){arena58_weekly_teacher_poll();exit;}
if($authenticated&&$teacherRequestTab==='arena'&&isset($_GET['zaznam'])&&function_exists('arena58_replay_render')){arena58_replay_render((string)($_GET['race']??''));exit;}
if($authenticated&&$teacherRequestTab==='arena'&&isset($_GET['projector'])&&function_exists('arena57_render_projector')){arena57_render_projector((string)($_GET['race']??''));exit;}
if($authenticated&&teacher58_handle_get($teacherRequestTab,$modules))exit;
?><!doctype html>
<html lang="cs"<?=teacher68_theme_attr($teacherRequestTab)?>><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>EDUCANET · Cockpit učitele</title><?=teacher68_stylesheets_html($teacherRequestTab)?><?php teacher58_head_assets($teacherRequestTab); teacher59_head_assets($teacherRequestTab); echo teacher68_shell_css_html($teacherRequestTab); ?></head>
<body class="teacher-app t68"><?php if(!$authenticated): ?><main class="teacher-shell"><?php endif; ?>
<?php if(!$authenticated && teacher59_mode()!=='legacy'): teacher59_render_login_card($flash); elseif(!$authenticated): ?>
<section class="teacher-login-card"><div class="teacher-login-brand"><span>EDUCANET</span><h1 class="teacher-login-title">Učitelský hodnoticí cockpit</h1><p>Projekty · rubriky · týmy · historie · publikované výsledky</p></div><?php if($flash): ?><div class="teacher-flash <?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div><?php endif; ?><form method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_login"><label>Jméno učitele<input name="teacher_name" maxlength="80" value="<?=e(teacher_display_name()==='Učitel'?'':teacher_display_name())?>" placeholder="např. Stanislav Novák"></label><label>Učitelský klíč<input type="password" name="teacher_key" required autocomplete="current-password"></label><button class="btn primary" type="submit">Otevřít učitelské rozhraní →</button></form></section>
<?php else:
$tab=$teacherRequestTab; teacher_require_modules($tab);
$schoolYear=$tab==='calendar'?(require __DIR__.'/school_year.php'):[];
$catalog=teacher59_filter_class_map(project_catalog()); $classId=teacher59_default_class((string)($_GET['class']??'class_2a'),array_keys($catalog));
$projects=(array)($catalog[$classId]??[]); $projectId=(string)($_GET['project']??($projects[0]['id']??''));
$students=in_array($tab,['sekce','overview','calendar','attention','control','reports','quality','automations','team_admin','demo_accounts'],true)?[]:project_students_for_class($classId);
$project=null;$groups=[];$targetId=(string)($_GET['target']??'');$editGroupId=(string)($_GET['edit_group']??'');$editGroup=null;
if(in_array($tab,['grade','groups','workspace'],true)){
    $project=project_find($classId,$projectId);if(!$project&&$projects){$project=$projects[0];$projectId=(string)$project['id'];}
    $groups=$project?project_groups_for($classId,$projectId):[];
    if($tab==='groups'&&$editGroupId!=='')$editGroup=project_group_find($editGroupId);
}
?>
<?php $t68Visible=teacher68_visible_tabs_current(); $t68Section=(string)($_GET['sekce']??''); ?>
<a class="t68-skip" href="#t68-main">Přeskočit na obsah</a>
<div class="t68-frame" data-t68-frame>
<?=teacher68_topbar_html($tab,$classId,$t68Visible,$t68Section)?>
<div class="t68-body"><aside class="t68-side" id="t68-side"><?=teacher68_nav_html($tab,$classId,$t68Visible,$t68Section,'Hlavní navigace')?></aside>
<main class="teacher-shell t68-main" id="t68-main" tabindex="-1">
<?=teacher68_breadcrumb_html($tab,$classId,$t68Section)?>
<?=teacher68_class_bar_html($tab,$classId,$catalog,$t68Visible)?>
<?php if($flash): ?><div class="teacher-flash <?=e((string)$flash['type'])?>"><?=e((string)$flash['message'])?></div><?php endif; ?>
<?php ops61_render_health_banner(); ?>

<?php if($tab==='sekce'): ?>
<?php teacher68_render_section_hub($t68Section,$t68Visible,$classId); ?>
<?php elseif($tab==='session'): ?>
<?php sess53_render_teacher_tab($modules,$classId); ?>
<?php elseif($tab==='arena'): ?>
<?php if(function_exists('arena57_render_teacher_tab')) arena57_render_teacher_tab($modules,$classId); else echo '<section class="teacher-empty wide">Modul Aréna se připravuje.</section>'; ?>
<?php elseif(teacher58_is_tab($tab)): ?>
<?php teacher58_render_tab($tab,$modules,$classId); ?>
<?php elseif($tab==='ucet'): ?>
<?php teacher59_render_account_tab(); ?>
<?php elseif($tab==='pristupy'): ?>
<?php acc58_render_teacher_accounts((string)($_GET['class']??$classId), csrf_token(), $modules); ?>
<?php elseif($tab==='intake'): ?>
<?php intake_v51_render_teacher_tab($modules); ?>
<?php elseif($tab==='attention'): ?>
<?php teacher_render_attention_center(isset($_GET['class'])?$classId:null); teacher_render_ops_plus_attention(isset($_GET['class'])?$classId:null); ?>
<?php elseif($tab==='control'): ?>
<?php teacher_render_control_tower(isset($_GET['class'])?$classId:null); ?>
<?php elseif($tab==='reports'): ?>
<?php teacher_render_reports(); ?>
<?php elseif($tab==='communications'): ?>
<?php teacher_render_communications($classId); ?>
<?php elseif($tab==='quality'): ?>
<?php teacher_render_quality(); ?>
<?php elseif($tab==='student360'): $studentKey=(string)($_GET['student']??''); if($studentKey===''){ $map=project_students_for_class($classId); $studentKey=(string)(array_key_first($map)??''); } ?>
<?php if($studentKey!==''): teacher_render_student360($classId,$studentKey); teacher_render_ops_plus_student360($classId,$studentKey); echo teacher68_student_hub_html($classId,$studentKey,$t68Visible); else: ?><div class="teacher-empty wide">V této třídě není dostupný student.</div><?php endif; ?>
<?php elseif($tab==='interventions'): ?>
<?php teacher_render_interventions($classId); teacher_render_ops_plus_interventions($classId); ?>
<?php elseif($tab==='analytics'): ?>
<?php teacher_render_analytics($classId); v50_render_teacher_heatmap($classId); v505_render_teacher_metrics($classId); ?>
<?php elseif($tab==='growth'): ?>
<?php v50_render_teacher_growth($classId); ?>
<?php elseif($tab==='automations'): ?>
<?php teacher_render_automations(); ?>
<?php elseif($tab==='filters'): ?>
<?php teacher_render_filters_manager($classId); ?>
<?php elseif($tab==='team_admin'): ?>
<?php if(teacher59_mode()!=='legacy') teacher59_render_team_admin_notice(); else teacher_render_team_admin(); ?>
<?php elseif($tab==='demo_accounts'): ?>
<?php teacher_render_demo_accounts($modules); ?>
<?php elseif($tab==='ops_audit'): ?>
<?php teacher_render_ops_audit(); ?>
<?php elseif($tab==='overview'): ?>
<?php teacher_render_global_overview(); teacher_render_adaptive_interventions(); ?>
<?php elseif($tab==='class_overview'): ?>
<?php teacher_render_ops_class_health_strip($classId); teacher_render_class_overview($classId); ?>
<?php elseif($tab==='class_results'): ?>
<?php teacher_render_class_results($classId); ?>
<?php elseif($tab==='calendar'): ?>
<?php teacher_render_school_calendar($schoolYear); ?>
<?php elseif($tab==='groups'): ?>
<section class="teacher-page-head"><div><div class="eyebrow">Týmy</div><h1>Skupinové projekty</h1><p>Členství se nastavuje jednou. Společná rubrika se potom propíše všem členům s možností individuální korekce.</p></div></section>
<div class="teacher-filter-row"><label>Projekt<select onchange="location.href='?tab=groups&class=<?=e($classId)?>&project='+encodeURIComponent(this.value)"><?php foreach($projects as $p): if((string)$p['type']!=='group')continue; ?><option value="<?=e((string)$p['id'])?>" <?=((string)$p['id']===$projectId?'selected':'')?>><?=e((string)$p['title'])?></option><?php endforeach; ?></select></label></div>
<?php if(!$project || (string)$project['type']!=='group'): ?><div class="teacher-empty">Vyberte skupinový projekt.</div><?php else: ?>
<?php $teamPlans=project_group_size_plans($classId); $teamVotes=team_plan_vote_summary($classId,$projectId); $studentLobbies=team_lobbies_for($classId,$projectId); $activeStudentLobbies=array_values(array_filter($studentLobbies,static fn($l):bool=>is_array($l)&&in_array((string)($l['status']??''),['open','ready'],true))); ?>
<section class="teacher-panel teacher-team-control"><div class="teacher-panel-head"><div><span>Team Control Center</span><h2>Jak se třída skládá do týmů</h2></div><div class="teacher-team-control-stats"><b><?=count($activeStudentLobbies)?></b><small>aktivní lobby</small></div></div><p class="teacher-muted">Studenti si mohou sami vybrat doporučené rozdělení, založit lobby a pozvat spolužáky kódem. Zde vidíte průběžný stav ještě před vznikem oficiálních týmů.</p>
<div class="teacher-team-plan-grid"><?php foreach($teamPlans as $plan): $votes=(int)($teamVotes[(string)$plan['id']]??0); ?><article class="<?=!empty($plan['recommended'])?'recommended':''?>"><span><?=!empty($plan['recommended'])?'Doporučeno':'Varianta'?></span><strong><?=e((string)$plan['label'])?></strong><small><?=count((array)$plan['sizes'])?> týmů · <?=$plan['students']?> studentů · <?=$votes?> hlasů</small></article><?php endforeach; ?><?php if(!$teamPlans): ?><div class="teacher-empty">Pro aktuální počet studentů zatím nelze vytvořit návrh rozdělení.</div><?php endif; ?></div>
<div class="teacher-lobby-watch"><h3>Studentská lobby</h3><?php if(!$activeStudentLobbies): ?><div class="teacher-empty">Žádné rozpracované lobby. Jakmile studenti začnou skládat týmy, objeví se zde.</div><?php endif; ?><?php foreach($activeStudentLobbies as $lobby): $memberKeys=array_values(array_map('strval',(array)($lobby['member_keys']??[]))); ?><article><div><strong><?=e((string)($lobby['name']??'Tým'))?></strong><span class="teacher-lobby-state <?=e((string)($lobby['status']??'open'))?>"><?=((string)($lobby['status']??'open')==='ready'?'Připraveno':'Skládá se')?></span><p><?php foreach($memberKeys as $mk): ?><?=e((string)($students[$mk]['label']??'Neznámý'))?><?= $mk===(string)($lobby['owner_key']??'')?' ★':''?> · <?php endforeach; ?></p></div><div class="teacher-lobby-meta"><code><?=e((string)($lobby['invite_code']??''))?></code><span><?=count($memberKeys)?> / <?=e((string)($lobby['capacity']??2))?></span></div></article><?php endforeach; ?></div></section>
<div class="teacher-two-col"><section class="teacher-panel"><div class="teacher-panel-head"><div><span>Existující týmy</span><h2><?=e((string)$project['title'])?></h2></div></div><div class="teacher-group-list"><?php if(!$groups): ?><div class="teacher-empty">Zatím není vytvořen žádný tým.</div><?php endif; ?><?php foreach($groups as $g): ?><article><div><strong><?=e((string)$g['name'])?></strong><span><?=count((array)$g['member_keys'])?> členů</span><p><?php foreach((array)$g['member_keys'] as $mk): ?><?=e((string)($students[(string)$mk]['label']??'Neznámý'))?> · <?php endforeach; ?></p></div><div><a class="btn secondary small" href="?tab=grade&class=<?=e($classId)?>&project=<?=e($projectId)?>&target=<?=e((string)$g['id'])?>">Hodnotit</a><a class="btn secondary small" href="?tab=workspace&class=<?=e($classId)?>&project=<?=e($projectId)?>&group=<?=e((string)$g['id'])?>">Workspace</a><a class="text-link" href="?tab=groups&class=<?=e($classId)?>&project=<?=e($projectId)?>&edit_group=<?=e((string)$g['id'])?>">Upravit</a><form method="post" onsubmit="return confirm('Odstranit tým?');"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_group"><input type="hidden" name="group_id" value="<?=e((string)$g['id'])?>"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="return_tab" value="groups"><button class="link-button danger" type="submit">Odstranit</button></form></div></article><?php endforeach; ?></div></section>
<section class="teacher-panel"><div class="teacher-panel-head"><div><span><?= $editGroup?'Upravit tým':'Nový tým' ?></span><h2><?= $editGroup?e((string)$editGroup['name']):'Sestavit skupinu' ?></h2></div><?php if($editGroup): ?><a class="text-link" href="<?=e(teacher_class_tab_url('groups',$classId))?>">Zrušit úpravu</a><?php endif; ?></div><form method="post" class="teacher-group-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_group"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="group_id" value="<?=e((string)($editGroup['id']??''))?>"><input type="hidden" name="return_tab" value="groups"><label>Název týmu<input name="group_name" required maxlength="100" placeholder="např. Tým Orion" value="<?=e((string)($editGroup['name']??''))?>"></label><fieldset><legend>Členové</legend><?php if(!$students): ?><div class="teacher-empty">Roster této třídy zatím není dostupný. Členové se načtou po propojení školních účtů studentů.</div><?php endif; ?><div class="teacher-student-checks"><?php foreach($students as $student): $selected=$editGroup&&in_array((string)$student['key'],(array)($editGroup['member_keys']??[]),true); ?><label><input type="checkbox" name="member_keys[]" value="<?=e((string)$student['key'])?>" <?=$selected?'checked':''?>><span><strong><?=e((string)$student['label'])?></strong><?php if($student['email']): ?><small><?=e((string)$student['email'])?></small><?php endif; ?></span></label><?php endforeach; ?></div></fieldset><button class="btn primary" type="submit"><?= $editGroup?'Uložit změny týmu':'Vytvořit tým' ?></button></form></section></div>
<?php endif; ?>

<?php elseif($tab==='grade'): ?>
<section class="teacher-page-head"><div><div class="eyebrow">Rubrikové hodnocení</div><h1><?=e((string)($project['title']??'Projekt'))?></h1><p><?=e((string)($project['summary']??''))?></p></div><div class="teacher-project-score"><strong><?=project_max_points($project??[])?> b</strong><span><?=e((string)($project['type']??'')==='group'?'skupinový projekt':'individuální projekt')?></span></div></section>
<div class="teacher-filter-row"><label>Projekt<select onchange="location.href='?tab=grade&class=<?=e($classId)?>&project='+encodeURIComponent(this.value)"><?php foreach($projects as $p): ?><option value="<?=e((string)$p['id'])?>" <?=((string)$p['id']===$projectId?'selected':'')?>><?=e((string)$p['title'])?> · <?=($p['type']==='group'?'tým':'student')?></option><?php endforeach; ?></select></label></div>
<?php if(!$targetId): ?>
<section class="teacher-target-grid"><?php if((string)$project['type']==='individual'): if(!$students): ?><div class="teacher-empty wide">V této třídě zatím není dostupný roster. Studenti se zde objeví automaticky po propojení školního účtu s třídou.</div><?php endif; foreach($students as $student): $r=project_grade_find($classId,$projectId,'individual',(string)$student['key']); ?><a href="?tab=grade&class=<?=e($classId)?>&project=<?=e($projectId)?>&target=<?=e((string)$student['key'])?>"><span class="teacher-avatar"><?=e(u_substr((string)$student['label'],0,1))?></span><div><strong><?=e((string)$student['label'])?></strong><small><?=e($r?teacher_status_label((string)$r['status']):'Nehodnoceno')?></small></div><?php if($r): ?><b class="grade-pill grade-<?=e((string)$r['grade'])?>"><?=e((string)$r['grade'])?></b><?php else: ?><i>→</i><?php endif; ?></a><?php endforeach; else: if(!$groups): ?><div class="teacher-empty wide">Nejdřív vytvořte týmy. <a href="<?=e(teacher_class_tab_url('groups',$classId))?>">Přejít do týmů →</a></div><?php endif; foreach($groups as $g): $r=project_grade_find($classId,$projectId,'group',(string)$g['id']); ?><a href="?tab=grade&class=<?=e($classId)?>&project=<?=e($projectId)?>&target=<?=e((string)$g['id'])?>"><span class="teacher-avatar group">G</span><div><strong><?=e((string)$g['name'])?></strong><small><?=count((array)$g['member_keys'])?> členů · <?=e($r?teacher_status_label((string)$r['status']):'Nehodnoceno')?></small></div><?php if($r): ?><b class="grade-pill grade-<?=e((string)$r['grade'])?>"><?=e((string)$r['grade'])?></b><?php else: ?><i>→</i><?php endif; ?></a><?php endforeach; endif; ?></section>
<?php else:
$targetType=(string)$project['type']; $record=project_grade_find($classId,$projectId,$targetType,$targetId); $targetLabel='';$targetGroup=null;
if($targetType==='individual'){ if(!isset($students[$targetId])){echo '<div class="teacher-empty">Student nebyl nalezen.</div>'; } else $targetLabel=(string)$students[$targetId]['label']; }
else{$targetGroup=project_group_find($targetId);$targetLabel=$targetGroup?(string)$targetGroup['name']:'Neznámý tým';}
$history=$record?project_grade_history((string)$record['id']):[];
?>
<a class="teacher-back" href="?tab=grade&class=<?=e($classId)?>&project=<?=e($projectId)?>">← Zpět na seznam</a>
<form method="post" class="teacher-grade-layout" data-rubric-form data-max="<?=project_max_points($project)?>"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_grade"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="target_id" value="<?=e($targetId)?>"><input type="hidden" name="return_tab" value="grade">
<section class="teacher-panel grade-main"><div class="teacher-panel-head"><div><span><?=e($targetType==='group'?'Tým':'Student')?></span><h2><?=e($targetLabel)?></h2></div><div class="live-score"><strong data-score><?= (int)($record['points']??0) ?></strong><span>/ <?=project_max_points($project)?> b</span><b data-suggested>návrh <?= (int)($record['suggested_grade']??5) ?></b></div></div>
<div class="teacher-rubric"><?php foreach((array)$project['rubric'] as $criterion): $cid=(string)$criterion['id'];$max=(int)$criterion['max'];$value=(int)($record['rubric_scores'][$cid]??0); ?><article><div><span><?=e((string)$criterion['title'])?></span><p><?=e((string)$criterion['description'])?></p></div><label><input type="number" name="rubric[<?=e($cid)?>]" value="<?=$value?>" min="0" max="<?=$max?>" step="1" data-rubric-score><small>/ <?=$max?></small></label></article><?php endforeach; ?></div>
<div class="teacher-feedback-grid"><label>Silné stránky<textarea name="strengths" rows="3" placeholder="Co student/tým zvládl dobře?"> <?=e((string)($record['strengths']??''))?></textarea></label><label>Další krok<textarea name="next_step" rows="3" placeholder="Jedna konkrétní priorita pro další iteraci."><?=e((string)($record['next_step']??''))?></textarea></label></div><label>Souhrnný komentář pro studenta<textarea name="teacher_comment" rows="4" placeholder="Vysvětlete hodnocení s odkazem na důkazy a rubriku."><?=e((string)($record['teacher_comment']??''))?></textarea></label>
<?php if($targetType==='group' && $targetGroup): ?><div class="teacher-member-adjust"><div class="teacher-panel-head compact"><div><span>Individuální korekce</span><h3>Členové týmu</h3></div><small>Volitelné. Výchozí je společný výsledek týmu.</small></div><?php foreach((array)$targetGroup['member_keys'] as $mk): $s=$students[(string)$mk]??['label'=>'Neznámý'];$adj=is_array($record['member_adjustments'][$mk]??null)?$record['member_adjustments'][$mk]:[]; ?><article><strong><?=e((string)$s['label'])?></strong><label>± body<input type="number" name="member_delta[<?=e((string)$mk)?>]" min="-<?=project_max_points($project)?>" max="<?=project_max_points($project)?>" value="<?= (int)($adj['points_delta']??0) ?>"></label><label>Vlastní známka<select name="member_grade[<?=e((string)$mk)?>]"><option value="">podle bodů</option><?php for($g=1;$g<=5;$g++): ?><option value="<?=$g?>" <?=((string)($adj['grade_override']??'')===(string)$g?'selected':'')?>><?=$g?></option><?php endfor; ?></select></label><label class="member-comment">Osobní komentář<input name="member_comment[<?=e((string)$mk)?>]" value="<?=e((string)($adj['comment']??''))?>" maxlength="1200"></label></article><?php endforeach; ?></div><?php endif; ?>
</section>
<aside class="teacher-grade-side"><section class="teacher-panel"><div class="teacher-panel-head compact"><div><span>Výsledek</span><h3>Publikace</h3></div></div><label>Známka<select name="grade"><option value="">Automaticky podle bodů</option><?php for($g=1;$g<=5;$g++): ?><option value="<?=$g?>" <?=((bool)($record['grade_overridden']??false)&&(int)($record['grade']??0)===$g?'selected':'')?>><?=$g?> · <?=['','výborný','chvalitebný','dobrý','dostatečný','nedostatečný'][$g]?></option><?php endfor; ?></select><small>Automatický návrh se počítá z bodů. Ruční přepsání zůstane v historii.</small></label><label>Stav<select name="status"><option value="draft" <?=((string)($record['status']??'draft')==='draft'?'selected':'')?>>Koncept · student nevidí</option><option value="published" <?=((string)($record['status']??'')==='published'?'selected':'')?>>Publikovat · student vidí</option><option value="returned" <?=((string)($record['status']??'')==='returned'?'selected':'')?>>Vrátit k dopracování</option></select></label><label>Soukromá poznámka učitele<textarea name="private_note" rows="4" placeholder="Tuto poznámku student nikdy neuvidí."><?=e((string)($record['private_note']??''))?></textarea></label><button class="btn primary wide" type="submit">Uložit hodnocení</button><?php if($record): ?><div class="record-meta"><span>Verze <?= (int)$record['version'] ?></span><span>Upraveno <?=e(date('d.m.Y H:i',strtotime((string)$record['updated_at'])))?></span><span><?=e((string)$record['teacher'])?></span></div><?php endif; ?></section>
<section class="teacher-panel"><div class="teacher-panel-head compact"><div><span>Historie</span><h3><?=count($history)?> změn</h3></div></div><div class="mini-history"><?php if(!$history): ?><div class="teacher-empty">První záznam vznikne po uložení.</div><?php endif; ?><?php foreach(array_slice($history,0,6) as $h): ?><article><i><?=e((string)($h['action']==='create'?'+':'↻'))?></i><div><strong><?=e((string)$h['teacher'])?></strong><span><?=e(date('d.m.Y H:i',strtotime((string)$h['at'])))?></span><small><?=count((array)$h['changes'])?> změněných polí</small></div></article><?php endforeach; ?></div><?php if($record): ?><a class="text-link" href="?tab=history&class=<?=e($classId)?>&record=<?=e((string)$record['id'])?>">Celá historie →</a><?php endif; ?></section></aside></form>
<?php endif; ?>

<?php elseif($tab==='curriculum'): ?>
<?php teacher_render_curriculum($classId); ?>

<?php elseif($tab==='teach'): ?>
<?php $teachLesson=max(1,(int)($_GET['lesson']??1)); teacher_render_lesson_mode($classId,$teachLesson); v50_render_teacher_lesson_card($classId,$teachLesson); ?>

<?php elseif($tab==='workspace'): ?>
<?php teacher_render_project_workspace($classId,$projectId,(string)($_GET['group']??'')); ?>

<?php elseif($tab==='skills'): ?>
<?php teacher_render_skills($classId); ?>
<?php elseif($tab==='mastery'): ?>
<?php ml_render_teacher_hub($classId); ?>
<?php elseif($tab==='authoring'): ?>
<?php ml_render_authoring($classId); ?>

<?php elseif($tab==='history'): $filterRecord=(string)($_GET['record']??'');$historyAll=load_php_json(STORAGE_DIR.'/project_grade_history.json.php');if(!is_array($historyAll))$historyAll=[];$historyAll=array_values(array_filter($historyAll,function($h)use($classId,$filterRecord){if(!is_array($h))return false;$snap=$h['snapshot']??[];if(!is_array($snap)||(string)($snap['class_id']??'')!==$classId)return false;return $filterRecord===''||(string)($h['record_id']??'')===$filterRecord;}));usort($historyAll,fn($a,$b)=>strcmp((string)($b['at']??''),(string)($a['at']??''))); ?>
<section class="teacher-page-head"><div><div class="eyebrow">Audit trail</div><h1>Historie změn</h1><p>Každé uložení vytváří neměnný záznam času, učitele a změněných hodnot.</p></div><?php if($filterRecord!==''): ?><a class="btn secondary" href="?tab=history&class=<?=e($classId)?>">Zobrazit vše</a><?php endif; ?></section>
<section class="teacher-history-list"><?php if(!$historyAll): ?><div class="teacher-empty wide">Zatím nejsou žádné změny hodnocení.</div><?php endif; ?><?php foreach($historyAll as $h): $snap=is_array($h['snapshot']??null)?$h['snapshot']:[];$p=project_find($classId,(string)($snap['project_id']??''));$changes=is_array($h['changes']??null)?$h['changes']:[]; ?><article><div class="history-time"><strong><?=e(date('d.m.Y',strtotime((string)$h['at'])))?></strong><span><?=e(date('H:i',strtotime((string)$h['at'])))?></span></div><div class="history-body"><div><span><?=e((string)$h['teacher'])?> · <?=e((string)$h['action'])?></span><h2><?=e((string)($p['title']??'Projekt'))?> · <?=e(teacher_record_target_label($snap))?></h2><details class="history-diff"><summary><?=count($changes)?> změněných polí · zobrazit detail</summary><div class="history-diff-list"><?php foreach($changes as $field=>$change): $change=is_array($change)?$change:[]; ?><div><strong><?=e(teacher_change_label((string)$field))?></strong><span><?=e(teacher_change_value((string)$field,$change['from']??null))?></span><i>→</i><span><?=e(teacher_change_value((string)$field,$change['to']??null))?></span></div><?php endforeach; ?></div></details></div><div class="history-change-chips"><?php foreach(array_keys($changes) as $field): ?><span><?=e(teacher_change_label((string)$field))?></span><?php endforeach; ?></div><div class="history-result"><b><?= (int)($snap['points']??0) ?>/<?= (int)($snap['max_points']??0) ?> b</b><strong class="grade-pill grade-<?=e((string)($snap['grade']??5))?>"><?=e((string)($snap['grade']??5))?></strong><span><?=e(teacher_status_label((string)($snap['status']??'')))?></span></div></div></article><?php endforeach; ?></section>
<?php endif; ?>
<?php if($authenticated): echo teacher68_next_steps_html($tab,$t68Visible,$classId); ?></main></div></div><?php endif; teacher_render_command_palette(); ?><?php if(!$authenticated): ?></main><?php endif; ?><?php if($tab==='arena'): ?><script src="assets/arena-v57.js?v=57.0" defer></script><?php endif; ?><?php teacher58_foot_assets($tab); ?><script src="assets/teacher-admin-v45-7.js?v=45.7" defer></script><script src="assets/teacher-admin-v46.js?v=46.2" defer></script><script src="assets/teacher-shell-v68.js?v=68.0" defer></script><?php if(in_array($tab,['analytics','teach'],true)): ?><script src="assets/hands-on-v50.js?v=50" defer></script><?php endif; ?><?php if($tab==='teach'): ?><script src="assets/cognitive-v43.js?v=46" defer></script><script src="assets/learning-studio-v44.js?v=46" defer></script><script src="assets/visual-simulation-v45.js?v=46" defer></script><script src="assets/visual-practical-v48.js?v=48" defer></script><script src="assets/visual-labs-3a-v48-1.js?v=48.1" defer></script><?php endif; ?></body></html>
<?php endif; ?>
