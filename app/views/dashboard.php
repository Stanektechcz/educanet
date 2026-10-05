<?php

declare(strict_types=1);

/**
 * ?view=dashboard – domovská stránka žáka.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'dashboard') {
    $dashSimulationMap = is_array($simulations[$classId] ?? null) ? $simulations[$classId] : [];
    learning_refresh_badges((string)$classId, $module, $dashSimulationMap);
    $dashProfile = learning_profile((string)$classId);
    $dashXp = (int)($dashProfile['xp'] ?? 0);
    $dashLevel = learning_level($dashXp);
    $dashTestDone = is_array($completedTestResult);
    $dashTests = learning_student_rows(STORAGE_DIR . '/practice_results.json.php', (string)$classId, 8);
    $dashLabs = learning_student_rows(STORAGE_DIR . '/lab_results.json.php', (string)$classId, 8);
    $dashGraphicsSubmissions = learning_student_rows(STORAGE_DIR . '/graphics_submissions.json.php', (string)$classId, 8);

    $dashKbTotal = count((array)($module['knowledgebase'] ?? []));
    $dashKbDone = 0;
    $dashLearned = [];
    foreach ((array)($module['knowledgebase'] ?? []) as $dashTopic => $dashArticle) {
        $dashHasSim = isset($dashSimulationMap[$dashTopic]) && is_array($dashSimulationMap[$dashTopic]);
        if (learning_kb_complete((string)$classId, (string)$dashTopic, $dashHasSim)) {
            $dashKbDone++;
            $dashProgress = learning_kb_progress((string)$classId, (string)$dashTopic);
            $dashLearned[] = [
                'topic'=>(string)$dashTopic,
                'title'=>(string)($dashArticle['title'] ?? $dashTopic),
                'summary'=>(string)($dashArticle['summary'] ?? ''),
                'complete'=>!empty($dashProgress['complete']),
            ];
        }
    }
    $dashLearned = array_reverse($dashLearned);

    $dashPrimaryDone = learning_primary_block_complete((string)$classId);
    $dashCoreDone = in_array($classId, ['class_1a','class_2a'], true) ? learning_graphics_core_complete((string)$classId, $dashSimulationMap) : false;
    $dashStudioDone = in_array($classId, ['class_1a','class_2a'], true) ? learning_studio_complete((string)$classId) : false;
    $dashCaseDone = learning_journey_step_done((string)$classId, 'case_study');

    $dashCourseRows = [];
    $dashCourseDoneUnits = $dashPrimaryDone ? 1 : 0;
    $dashCourseTotalUnits = 1;
    $dashCourseRows[] = [
        'number'=>1,'title'=>tr('Základní dvouhodinový blok'),'goal'=>(string)$module['lesson_note'],
        'done'=>$dashPrimaryDone,'unlocked'=>true,'done_steps'=>$dashPrimaryDone?1:0,'total_steps'=>1,'href'=>'?view=dashboard'
    ];
    $dashLesson2 = is_array($nextLessons[$classId] ?? null) ? $nextLessons[$classId] : null;
    if (is_array($dashLesson2)) {
        $p = learning_next_lesson_progress((string)$classId, (string)$dashLesson2['id'], (array)($dashLesson2['steps'] ?? []));
        $doneSteps = count(array_filter($p)); $totalSteps = max(1, count($p));
        $done = learning_next_lesson_complete((string)$classId, $dashLesson2);
        $dashCourseRows[] = ['number'=>2,'title'=>(string)$dashLesson2['title'],'goal'=>(string)$dashLesson2['goal'],'done'=>$done,'unlocked'=>$dashPrimaryDone,'done_steps'=>$doneSteps,'total_steps'=>$totalSteps,'href'=>'?view=next_lesson'];
        $dashCourseDoneUnits += $doneSteps; $dashCourseTotalUnits += $totalSteps;
    }
    foreach ((array)($extendedLessons[$classId] ?? []) as $dashLesson) {
        if (!is_array($dashLesson)) continue;
        $num=(int)($dashLesson['number']??0); if($num<3) continue;
        $p=learning_course_lesson_progress((string)$classId,$dashLesson);
        $doneSteps=count(array_filter($p)); $totalSteps=max(1,count($p));
        $done=learning_course_lesson_complete((string)$classId,$dashLesson);
        $unlocked=learning_course_lesson_unlocked((string)$classId,$num,$nextLessons,$extendedLessons);
        $dashCourseRows[]=['number'=>$num,'title'=>(string)($dashLesson['title']??tr('Lekce {n}', ['n' => $num])),'goal'=>(string)($dashLesson['goal']??''),'done'=>$done,'unlocked'=>$unlocked,'done_steps'=>$doneSteps,'total_steps'=>$totalSteps,'href'=>module_url('course_lesson',['lesson'=>(string)$dashLesson['id']])];
        $dashCourseDoneUnits += $doneSteps; $dashCourseTotalUnits += $totalSteps;
    }
    usort($dashCourseRows, static fn(array $a,array $b):int => $a['number'] <=> $b['number']);
    $dashLessonsDone = count(array_filter($dashCourseRows, static fn(array $r):bool => !empty($r['done'])));
    $dashLessonsTotal = count($dashCourseRows);
    $dashCoursePercent = $dashCourseTotalUnits ? (int)round(($dashCourseDoneUnits/$dashCourseTotalUnits)*100) : 0;
    $dashKbPercent = $dashKbTotal ? (int)round(($dashKbDone/$dashKbTotal)*100) : 0;
    $dashOverallPercent = (int)round(($dashCoursePercent * .72) + ($dashKbPercent * .28));

    $dashNextRow = null;
    foreach ($dashCourseRows as $row) if (!$row['done'] && $row['unlocked']) { $dashNextRow=$row; break; }
    $dashNextAction = ['kind'=>'link','href'=>'?view=course','label'=>tr('Pokračovat v kurzu'),'title'=>tr('Otevři další odemčenou lekci'),'text'=>tr('Kurz ti ukáže přesně, kde pokračovat.')];
    $dashIntakeClass = intake_v51_classes($modules)[(string)$classId] ?? null;
    if (is_array($dashIntakeClass) && !empty($dashIntakeClass['open']) && !intake_v51_student_has_response((string)$classId)) {
        $dashNextAction=['kind'=>'link','href'=>'?view=intake','label'=>tr('Vyplnit dotazník'),'title'=>tr('Seznamovací dotazník'),'text'=>tr('První krok roku: pomoz učiteli poznat, co tě baví a jak se ti nejlépe učí. Zabere asi 15 minut.')];
    } elseif (active_test()) {
        $dashNextAction=['kind'=>'link','href'=>'?view=test','label'=>tr('Pokračovat v testu'),'title'=>tr('Máš rozpracovaný startovní test'),'text'=>tr('Dokonči aktuální otázku; Materiály je během testu zamčená.')];
    } elseif (!$dashTestDone) {
        $dashNextAction=['kind'=>'test','href'=>'','label'=>tr('Spustit startovní test'),'title'=>tr('Začni vstupní diagnostikou'),'text'=>tr('Krátký adaptivní průchod ukáže, které základy už máš jisté.')];
    } elseif (in_array($classId,['class_1a','class_2a'],true) && !$dashCoreDone) {
        $coreTopics=learning_graphics_core_topics(); $target='hierarchy';
        foreach($coreTopics as $t){$has=isset($dashSimulationMap[$t]); if(!learning_kb_complete((string)$classId,$t,$has)){ $target=$t; break; }}
        $dashNextAction=['kind'=>'link','href'=>module_url('kb_lesson',['topic'=>$target]),'label'=>tr('Pokračovat v Knowledge lekci'),'title'=>tr('Dokonči základní design principy'),'text'=>tr('Nejdřív upevni hierarchii, kompozici a kontrast.')];
    } elseif (in_array($classId,['class_1a','class_2a'],true) && !$dashStudioDone) {
        $dashNextAction=['kind'=>'link','href'=>'?view=graphics_studio','label'=>tr('Otevřít Studio'),'title'=>tr('Převeď znalosti do návrhu'),'text'=>tr('Experimentuj s hierarchií, gridem, kontrastem a exportem.')];
    } elseif (!in_array($classId,['class_1a','class_2a'],true) && !$dashCaseDone) {
        $dashNextAction=['kind'=>'link','href'=>'?view=case_study','label'=>tr('Otevřít případovou studii'),'title'=>tr('Přejdi od testu k reálnému incidentu'),'text'=>tr('Topologie, symptom a evidence tě připraví na praktický lab.')];
    } elseif (!$dashPrimaryDone) {
        $dashNextAction=['kind'=>'link','href'=>in_array($classId,['class_1a','class_2a'],true)?'?view=graphics_guide':'?view=practice','label'=>in_array($classId,['class_1a','class_2a'],true)?tr('Dokončit projekt'):tr('Pokračovat v labu'),'title'=>tr('Dokonči první dvouhodinový blok'),'text'=>tr('Po jeho dokončení se odemkne dlouhodobá mapa dalších lekcí.')];
    } elseif (is_array($dashNextRow)) {
        $dashNextAction=['kind'=>'link','href'=>(string)$dashNextRow['href'],'label'=>tr('Pokračovat v lekci {n}', ['n' => $dashNextRow['number']]),'title'=>(string)$dashNextRow['title'],'text'=>(string)$dashNextRow['goal']];
    }

    // v47: after the start diagnostic, one primary CTA should lead to the personal plan.
    // Keep an active/start diagnostic above the coach, but avoid competing recommendations afterwards.
    $coachDashboardStudentKey=adaptive_student_key((string)$classId);
    $coachDashboardTourMap=is_array($knowledgeTours[$classId]??null)?$knowledgeTours[$classId]:[];
    $coachDashboardSnapshot=$coachDashboardStudentKey!==''?coach_dashboard_snapshot((string)$classId,$coachDashboardStudentKey,$module,$coachDashboardTourMap):null;
    $dashPrimaryAction=$dashNextAction;
    if($dashTestDone&&!active_test()&&is_array($coachDashboardSnapshot)&&!(($dashPrimaryAction['href']??'')==='?view=intake')){
        $coachPlan=(array)($coachDashboardSnapshot['plan']??[]);
        $coachItems=(array)($coachPlan['items']??[]);
        if($coachItems){
            $coachFirst=(array)$coachItems[0];
            $coachPlanned=max(1,(int)($coachPlan['planned_minutes']??$coachPlan['minutes']??25));
            $dashNextAction=[
                'kind'=>'link',
                'href'=>v505_local_href_to_task((string)($coachFirst['href']??module_url('study',['minutes'=>(int)($coachPlan['minutes']??25),'energy'=>'normal','intent'=>(string)($coachPlan['intent']??'balanced')])), '?view=dashboard'),
                'label'=>tr('Spustit dnešní plán'),
                'title'=>tr('Dnes má největší smysl: {title}', ['title' => (string)($coachFirst['title']??tr('osobní studijní plán'))]),
                'text'=>tr('{reason} · celý plán má asi {minutes} min.', ['reason' => (string)($coachFirst['reason']??tr('Nejdůležitější krok na dnešek')), 'minutes' => $coachPlanned]),
            ];
            // v51: 1. lekce nemá vlastní stránku (odkaz na přehled = smyčka) – použij konkrétní další krok bloku.
            if($dashNextAction['href']==='?view=dashboard'&&!in_array((string)($dashPrimaryAction['href']??''),['','?view=dashboard'],true)){
                $dashNextAction['href']=(string)$dashPrimaryAction['href'];
                $dashNextAction['label']=(string)($dashPrimaryAction['label']??tr('Pokračovat'));
            }
        }
    }

    // v50.5: unfinished One Task draft becomes the next action after mandatory diagnostic.
    $dashOneTaskDraft=$coachDashboardStudentKey!==''?v505_latest_draft((string)$classId,$coachDashboardStudentKey):null;
    if($dashTestDone&&!active_test()&&is_array($dashOneTaskDraft)&&!empty($dashOneTaskDraft['task_url'])){
        $dashNextAction=['kind'=>'link','href'=>(string)$dashOneTaskDraft['task_url'],'label'=>tr('Pokračovat v rozdělaném'),'title'=>(string)($dashOneTaskDraft['task_title']?:tr('Rozdělaný úkol')),'text'=>tr('Pracovní plocha se otevře tam, kde jsi skončil/a. Průběžný draft je uložený automaticky.')];
    }
    if(($dashNextAction['kind']??'')==='link')$dashNextAction['href']=v505_local_href_to_task((string)($dashNextAction['href']??''),'?view=dashboard');
    // v53: když učitel otevřel hodinu, je dnešní zadání prvním krokem na přehledu.
    $dashSession = sess53_for_class_date((string)$classId, date('Y-m-d'));
    if (is_array($dashSession) && !empty($dashSession['open']) && (string)$dashSession['kind'] === 'work') {
        $dashSessionSub = sess53_submission((string)$dashSession['id'], adaptive_student_key((string)$classId));
        $dashNextAction = ['kind'=>'link','href'=>'?view=hodina','label'=>($dashSessionSub['status'] ?? '') === 'submitted' ? tr('Zobrazit dnešní práci') : tr('Otevřít dnešní hodinu'),
            'title'=>tr('Dnešní hodina: {title}', ['title' => (string)$dashSession['title']]),
            'text'=>($dashSessionSub['status'] ?? '') === 'submitted' ? tr('Práci máš odevzdanou. Můžeš ji ještě doplnit nebo pokračovat v kurzu.') : (string)$dashSession['goal']];
    }

    $dashTimeline = learning_xp_timeline($dashProfile, 14);
    $chartW=720; $chartH=210; $padX=28; $padY=24;
    $maxTotal=max(1,max(array_map(static fn(array $r):int=>(int)$r['total'],$dashTimeline)));
    $minTotal=min(array_map(static fn(array $r):int=>(int)$r['total'],$dashTimeline));
    if($maxTotal===$minTotal){$minTotal=max(0,$minTotal-10);$maxTotal=$minTotal+20;}
    $chartPoints=[]; $barWidth=max(8,(($chartW-$padX*2)/max(1,count($dashTimeline)))*.46);
    foreach($dashTimeline as $i=>$r){
        $x=$padX+($i/max(1,count($dashTimeline)-1))*($chartW-$padX*2);
        $y=$chartH-$padY-(((int)$r['total']-$minTotal)/max(1,$maxTotal-$minTotal))*($chartH-$padY*2);
        $chartPoints[]=[round($x,1),round($y,1),$r];
    }
    $poly=implode(' ',array_map(static fn(array $p):string=>$p[0].','.$p[1],$chartPoints));
    $area=$poly!=='' ? $padX.','.($chartH-$padY).' '.$poly.' '.($chartW-$padX).','.($chartH-$padY) : '';

    $badgeDefs=learning_badge_definitions(); $earnedBadges=(array)($dashProfile['badges']??[]);
    $achievementDefs=learning_achievement_definitions(); $earnedAchievements=(array)($dashProfile['achievements']??[]);
    $achievementProgress=learning_achievement_progress((string)$classId,$module,$dashSimulationMap);
    $nextLevelMilestone=(int)(ceil(max(1,(int)$dashLevel['level'])/10)*10); if($nextLevelMilestone<10)$nextLevelMilestone=10; if($nextLevelMilestone>200)$nextLevelMilestone=200;
    $badgeDisplayIds=array_keys($earnedBadges);
    foreach(['level_'.$nextLevelMilestone,'prestige_exam_certified','prestige_exam_perfect','prestige_exam_double','exam_perfect','challenge_distinction','project_masterpiece','triple_distinction','knowledge_grandmaster','course_mastery'] as $bid){ if(isset($badgeDefs[$bid])&&!in_array($bid,$badgeDisplayIds,true))$badgeDisplayIds[]=$bid; }
    $badgeDisplayIds=array_slice($badgeDisplayIds,0,9);
    $dashActivity=[];
    foreach((array)($dashProfile['events']??[]) as $key=>$event){ if(!is_array($event))continue; $dashActivity[]=['key'=>(string)$key,'xp'=>(int)($event['xp']??0),'at'=>(string)($event['at']??'')]; }
    usort($dashActivity,static fn(array $a,array $b):int=>(strtotime($b['at'])?:0)<=>(strtotime($a['at'])?:0));
    $dashActivity=array_slice($dashActivity,0,8);
    $studentName=(string)($_SESSION['student_label']??(auth_user()['name']??'Student'));
    $dashProjectResults=project_student_results((string)$classId,$studentName);
    $dashTeacherStudentKey=social_current_student_key((string)$classId);
    $dashTeacherTasks=$dashTeacherStudentKey!==''?teacher_tasks_for_student((string)$classId,$dashTeacherStudentKey,false):[];
    $dashTeacherInterventions=$dashTeacherStudentKey!==''?teacher_ops_student_interventions_public((string)$classId,$dashTeacherStudentKey):[];
    $studentEmail=(string)(auth_user()['email']??'');
    $studentInitial=u_substr(trim($studentName)!==''?$studentName:'S',0,1);
    $dashClassSchedule=adaptive_class_schedule($schoolYear,(string)$classId);
    $dashClassScheduleTime=trim((string)($dashClassSchedule['start']??''))!==''?((string)$dashClassSchedule['start'].'–'.(string)$dashClassSchedule['end']):'2 × 45 min';

    // v50.2: calculate the few signals a student needs before rendering the simplified dashboard.
    $adaptiveStudentKey=adaptive_student_key((string)$classId);
    $adaptiveTourMap=is_array($knowledgeTours[$classId]??null)?$knowledgeTours[$classId]:[];
    $adaptiveReviewQueue=adaptive_retrieval_queue((string)$classId,$adaptiveStudentKey,$module,$adaptiveTourMap,3);
    $adaptiveJournal=adaptive_journal_get((string)$classId,$adaptiveStudentKey,date('Y-m-d'));
    $adaptivePending=[];
    foreach(adaptive_absence_rows() as $a){
        if(!is_array($a)||(string)($a['class_id']??'')!==(string)$classId||(string)($a['student_key']??'')!==$adaptiveStudentKey||(string)($a['status']??'')==='complete')continue;
        $adaptivePending[]=$a;
    }
    skill_sync_existing_learning((string)$classId);
    $dashSkillBranches=skill_branch_progress_map((string)$classId);
    $dashSkillDefs=skill_branches();
    $dashSkillNext=skill_next_recommendation((string)$classId);

    $dashJourneyAfter=null;
    if(is_array($dashNextRow)){
        $found=false;
        foreach($dashCourseRows as $jr){
            if($found){$dashJourneyAfter=$jr;break;}
            if((int)$jr['number']===(int)$dashNextRow['number'])$found=true;
        }
    }
    $dashAttentionCount=count($dashTeacherTasks)+count($dashTeacherInterventions)+count($adaptivePending)+count($adaptiveReviewQueue);
    $dashDetailsOpen=(string)($_GET['details']??'')==='1';
    $dashPracticeHref=in_array($classId,['class_1a','class_2a'],true)?'?view=graphics_studio':'?view=practice';
    $dashPracticeLabel=in_array($classId,['class_1a','class_2a'],true)?'Chci něco vytvořit':'Chci si to vyzkoušet v labu';
    $dashGoal=v504_goal_selected((string)$classId,$adaptiveStudentKey);
    $dashGoalPlan=is_array($dashGoal)?v504_goal_plan((string)$classId,$adaptiveStudentKey,$dashGoal):[];
    $dashGoalProgress=is_array($dashGoal)?v504_goal_progress((string)$classId,$adaptiveStudentKey,$dashGoal):0;

    render_header(tr('Přehled'), $module);
    ?>
    <?php if ($flash !== ''): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
    <section class="student-cockpit student-cockpit-v502">
      <div class="student-cockpit-main">
        <div class="student-welcome-row v5075-welcome">
          <div class="student-welcome-copy">
            <span<?= edu_content_lang_attr() ?>><?=e((string)$module['subject'])?></span>
            <h1><?= e(tr('Ahoj, {name}.', ['name' => explode(' ',trim($studentName))[0] ?: tr('studente')])) ?></h1>
          </div>
        </div>

        <article class="student-do-now-card v5075-do-now">
          <div class="student-do-now-copy"><div class="eyebrow"><?= e(tr('Teď')) ?></div><h2><?= e((string)$dashNextAction['title']) ?></h2><p><?= e((string)$dashNextAction['text']) ?></p></div>
          <div class="student-do-now-action">
          <?php if($dashNextAction['kind']==='test'): ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="start_test"><button class="btn primary student-primary-cta" type="submit"><?= e((string)$dashNextAction['label']) ?></button></form><?php else: ?><a class="btn primary student-primary-cta" href="<?= e((string)$dashNextAction['href']) ?>"><?= e((string)$dashNextAction['label']) ?></a><?php endif; ?>
          </div>
        </article>

        <?php if (function_exists('p63_render_next_card')) p63_render_next_card((string)$classId, (string)$adaptiveStudentKey); // v63 (rozhodnutí školy Q6): karta Co dál přímo POD kartou Teď, jen čtení ?>

        <?php if (function_exists('ch64_render_card')) ch64_render_card((string)$classId, (string)$adaptiveStudentKey); // v64: výzva týdne navázaná na Co dál (třídy karty Co dál, bez nového CSS) ?>

        <?php if (function_exists('mot61_render_goals_card')) mot61_render_goals_card((string)$classId); // v61: dnešní cíle ?>

        <div class="v506-dashboard-quiet-links v5075-dashboard-detail-link">
          <a href="?view=dashboard&details=1"><?= e(tr('Zobrazit můj progres')) ?></a>
        </div>

        <?php if($dashAttentionCount>0): ?>
        <details class="student-attention-bar v5076-attention">
          <summary><span><strong><?= e(tr('{n} k řešení', ['n' => $dashAttentionCount])) ?></strong></span><b><?= e(tr('Otevřít')) ?></b></summary>
          <div class="student-attention-actions">
            <?php if($dashTeacherTasks): ?><a href="?view=dashboard&details=1#teacher-tasks"><b><?=count($dashTeacherTasks)?></b> <?= e(tr('úkoly od učitele')) ?></a><?php endif; ?>
            <?php if($dashTeacherInterventions): ?><a href="?view=dashboard&details=1#support-plan"><b><?=count($dashTeacherInterventions)?></b> <?= e(tr('plán podpory')) ?></a><?php endif; ?>
            <?php if($adaptivePending): $pa=$adaptivePending[0]; ?><a href="<?=e(module_url('recovery',['date'=>(string)$pa['date']]))?>"><b><?=count($adaptivePending)?></b> <?= e(tr('k doplnění')) ?></a><?php endif; ?>
            <?php if($adaptiveReviewQueue): ?><a href="?view=review"><b><?=count($adaptiveReviewQueue)?></b> <?= e(tr('krátké opakování')) ?></a><?php endif; ?>
          </div>
        </details>
        <?php endif; ?>
      </div>

      <details class="student-dashboard-more" <?= $dashDetailsOpen?'open':'' ?> data-dashboard-more>
        <summary><span class="student-dashboard-more-copy"><strong><?= e(tr('Můj progres')) ?></strong></span><b><?= e(tr('Otevřít')) ?></b></summary>
        <div class="student-dashboard-more-body">
      <div class="dashboard-metrics v5076-core-metrics">
        <article><div><strong><?= $dashLessonsDone ?>/<?= $dashLessonsTotal ?></strong><span><?= e(tr('Lekce')) ?></span></div></article>
        <article><div><strong><?= $dashKbDone ?>/<?= $dashKbTotal ?></strong><span><?= e(tr('Témata')) ?></span></div></article>
        <article><div><strong><?= count($dashProjectResults) ?></strong><span><?= e(tr('Projekty')) ?></span></div></article>
      </div>

      <?php if($dashTeacherTasks): ?>
      <section class="dashboard-panel teacher-task-panel" id="teacher-tasks">
        <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Úkoly od učitele')) ?></div><h2><?= e(tr('{n} aktivní {word}', ['n' => count($dashTeacherTasks), 'word' => count($dashTeacherTasks)===1?tr('úkol'):tr('úkoly')])) ?></h2><p><?= e(tr('Úkoly přiřazené přímo z učitelské administrace. Po dokončení je můžeš jedním kliknutím uzavřít.')) ?></p></div></div>
        <div class="teacher-task-list"><?php foreach($dashTeacherTasks as $task): $due=(string)($task['due_at']??''); $overdue=$due!==''&&$due<date('Y-m-d'); ?>
          <article class="teacher-task-card priority-<?=e((string)($task['priority']??'normal'))?>"><i></i><div><strong><?=e((string)$task['title'])?></strong><?php if(trim((string)($task['instructions']??''))!==''): ?><p><?=nl2br(e((string)$task['instructions']))?></p><?php endif; ?><div class="teacher-task-meta"><span><?= e(tr('Priorita: {priority}', ['priority' => teacher_task_priority_label((string)($task['priority']??'normal'))])) ?></span><?php if($due!==''): ?><span><?= e(tr('{label}: {date}', ['label' => $overdue?tr('Po termínu'):tr('Termín'), 'date' => date('d.m.Y',strtotime($due))])) ?></span><?php endif; ?><?php if((int)($task['reminder_count']??0)>0): ?><span><?= e(tr('Připomenuto {n}×', ['n' => (int)$task['reminder_count']])) ?></span><?php endif; ?><span><?= e(tr('Zadal: {who}', ['who' => (string)($task['assigned_by']??tr('Učitel'))])) ?></span></div><?php if(!empty($task['resource_ref'])): ?><div class="teacher-task-resource"><strong><?=e(match((string)($task['resource_type']??'')){'lesson'=>tr('Lekce'),'lab'=>tr('Laboratoř'),'url'=>tr('Odkaz'),default=>tr('Materiál')})?>:</strong> <?php if((string)($task['resource_type']??'')==='url'): ?><a href="<?=e((string)$task['resource_ref'])?>" target="_blank" rel="noopener noreferrer"><?=e((string)$task['resource_ref'])?></a><?php else: ?><span><?=e((string)$task['resource_ref'])?></span><?php endif; ?></div><?php endif; ?></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_task_complete"><input type="hidden" name="task_id" value="<?=e((string)$task['id'])?>"><button class="btn secondary small" type="submit"><?= e(tr('Označit hotovo ✓')) ?></button></form></article>
        <?php endforeach; ?></div>
      </section>
      <?php endif; ?>

      <?php if($dashTeacherInterventions): ?>
      <section class="dashboard-panel teacher-intervention-panel" id="support-plan"><div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Můj plán podpory')) ?></div><h2><?= e(tr('{n} aktivní {word}', ['n' => count($dashTeacherInterventions), 'word' => count($dashTeacherInterventions)===1?tr('plán'):tr('plány')])) ?></h2><p><?= e(tr('Krátké kroky domluvené s učitelem. Nejde o automatickou známku ani sankci.')) ?></p></div></div><div class="teacher-task-list"><?php foreach($dashTeacherInterventions as $plan): $pending=array_values(array_filter((array)($plan['steps']??[]),static fn($st)=>is_array($st)&&(string)($st['status']??'pending')==='pending'));$next=$pending[0]??null; ?><article class="teacher-task-card"><i></i><div><strong><?=e((string)$plan['title'])?></strong><?php if(!empty($plan['problem'])): ?><p><?=e((string)$plan['problem'])?></p><?php endif; ?><?php if($next): ?><div class="teacher-task-resource"><strong><?= e(tr('Další krok:')) ?></strong> <span><?=e((string)$next['title'])?> · <?=e((string)$next['detail'])?></span></div><?php endif; ?><div class="teacher-task-meta"><?php if(!empty($plan['review_due'])): ?><span><?= e(tr('Kontrola: {date}', ['date' => date('d.m.Y',strtotime((string)$plan['review_due']))])) ?></span><?php endif; ?><?php if(!empty($plan['success_metric'])): ?><span><?= e(tr('Cíl: {goal}', ['goal' => (string)$plan['success_metric']])) ?></span><?php endif; ?></div></div></article><?php endforeach; ?></div></section>
      <?php endif; ?>

      <details class="v5076-progress-advanced">
        <summary><strong><?= e(tr('Další detail')) ?></strong><span><?= e(tr('Grafy, XP, historie a rozvoj')) ?></span></summary>
        <div class="v5076-progress-advanced-body">
          <?php if(is_array($dashNextRow)){ $v44DashLesson=v42_find_lesson((string)$classId,(int)$dashNextRow['number']); if($v44DashLesson) v44_render_dashboard_gps((string)$classId,$v44DashLesson); } ?>
          <?php render_student_coach_dashboard((string)$classId,$module,$coachDashboardTourMap,$coachDashboardStudentKey,$coachDashboardSnapshot); ?>

      <section class="dashboard-panel adaptive-today-panel">
        <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Dnes · bez zahlcení')) ?></div><h2><?= e(tr('Jedna malá věc pro udržení tempa')) ?></h2><p><?= e(tr('Opakování, recovery a reflexe jsou krátké podpůrné kroky. Nejsou to další známkované úkoly.')) ?></p></div></div>
        <div class="adaptive-today-grid">
          <a class="adaptive-today-action" href="?view=review"><i>↺</i><div><span><?= e(tr('3min opakování')) ?></span><strong><?= e(tr('{n} témata dnes', ['n' => count($adaptiveReviewQueue)])) ?></strong><small><?= $adaptiveReviewQueue?e(tr('Vybav si je bez poznámek →')):e(tr('Dnes máš hotovo')) ?></small></div></a>
          <?php if($adaptivePending): $pa=$adaptivePending[0]; ?><a class="adaptive-today-action alert" href="<?=e(module_url('recovery',['date'=>(string)$pa['date']]))?>"><i>↻</i><div><span><?= e(tr('Recovery Path')) ?></span><strong><?= e(tr('{n} zmeškaná lekce', ['n' => count($adaptivePending)])) ?></strong><small><?= e(tr('Krátká cesta zpět do tempa →')) ?></small></div></a><?php else: ?><div class="adaptive-today-action calm"><i>✓</i><div><span><?= e(tr('Recovery')) ?></span><strong><?= e(tr('Nic nedoháníš')) ?></strong><small><?= e(tr('Žádná zmeškaná lekce čekající na doplnění')) ?></small></div></div><?php endif; ?>
          <details class="adaptive-journal-card" <?=empty($adaptiveJournal)?'':'open'?>>
            <summary><i>✎</i><div><span><?= e(tr('Learning Journal')) ?></span><strong><?=empty($adaptiveJournal)?e(tr('60 sekund reflexe')):e(tr('Dnešní reflexe uložená'))?></strong><small><?=empty($adaptiveJournal)?e(tr('Co chápu a co ještě ne?')):e(tr('Můžeš ji kdykoliv upravit'))?></small></div></summary>
            <form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="adaptive_journal_save"><label><?= e(tr('Co mi dnes dává smysl?')) ?><textarea name="understood" rows="2" maxlength="900"><?=e((string)($adaptiveJournal['understood']??''))?></textarea></label><label><?= e(tr('Co mi ještě není jasné?')) ?><textarea name="unclear" rows="2" maxlength="900"><?=e((string)($adaptiveJournal['unclear']??''))?></textarea></label><label><?= e(tr('Co zkusím příště?')) ?><input name="next" maxlength="900" value="<?=e((string)($adaptiveJournal['next']??''))?>"></label><button class="btn primary small" type="submit"><?= e(tr('Uložit reflexi')) ?></button></form>
          </details>
        </div>
      </section>

      <?php ml_render_live_student((string)$classId,$adaptiveStudentKey); ml_render_custom_scenarios((string)$classId); ml_render_error_journal((string)$classId,$adaptiveStudentKey); ?>
      <?php $mlGoal=ml_goal_get((string)$classId,$adaptiveStudentKey); ?><details class="ml-card ml-goal-card"><summary><span><?= e(tr('Mastery conference')) ?></span><strong><?=empty($mlGoal)?e(tr('Můj další cíl')):e(tr('Cíl uložený'))?></strong></summary><form method="post" class="ml-inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_goal_save"><label><?= e(tr('Moje nejsilnější dovednost')) ?><input name="strength" maxlength="500" value="<?=e((string)($mlGoal['strength']??''))?>"></label><label><?= e(tr('Co chci zlepšit')) ?><input name="focus" maxlength="500" value="<?=e((string)($mlGoal['focus']??''))?>"></label><label><?= e(tr('Jakou evidence pro to mám / chci získat')) ?><textarea name="evidence" rows="2" maxlength="700"><?=e((string)($mlGoal['evidence']??''))?></textarea></label><button class="btn secondary" type="submit"><?= e(tr('Uložit cíl')) ?></button></form></details>

      <section class="dashboard-panel dashboard-skill-preview"><div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Tvoje dovednosti')) ?></div><h2><?= e(tr('Skill Trees · {percent} % mastery', ['percent' => number_format(skill_overall_mastery((string)$classId),0)])) ?></h2><p><?= e(tr('Kompetence se počítají z konkrétní evidence, ne z XP.')) ?></p></div><a class="btn secondary" href="?view=skills"><?= e(tr('Celá mapa →')) ?></a></div><div class="dashboard-skill-branches"<?= edu_content_lang_attr() ?>><?php foreach($dashSkillBranches as $branch=>$bp):?><article><span><?=e((string)($dashSkillDefs[$branch]['icon']??'◇'))?> <?=e((string)($dashSkillDefs[$branch]['name']??$branch))?></span><strong><?=number_format((float)$bp['mastery_percent'],0)?>%</strong><?=skill_percent_bar((float)$bp['mastery_percent'])?></article><?php endforeach;?></div><?php if($dashSkillNext):?><a class="dashboard-skill-next" href="<?=e(module_url('skill_detail',['skill'=>(string)$dashSkillNext['skill']['slug']]))?>"><span><?= e(tr('Doporučeno')) ?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$dashSkillNext['skill']['name'])?></strong><small<?= edu_content_lang_attr() ?>><?=e((string)$dashSkillNext['reason'])?> →</small></a><?php endif;?></section>
      <?php $dashPwMe=social_current_student_key((string)$classId);$dashPwProjects=project_workspace_available_projects((string)$classId,$dashPwMe);if($dashPwProjects):$dashPw=$dashPwProjects[0];$dashPwProject=$dashPw['project'];$dashPwGroup=$dashPw['group'];$dashPwHealth=project_workspace_health((string)$classId,(string)$dashPwProject['id'],(string)$dashPwGroup['id']);$dashPwNext=project_next_action((string)$dashPwGroup['id'],$dashPwMe);$dashPwRoles=project_roles_for_student((string)$dashPwGroup['id'],$dashPwMe);?>
      <section class="dashboard-panel dashboard-project-workspace"><div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Aktivní týmový projekt')) ?></div><h2<?= edu_content_lang_attr() ?>><?=e((string)$dashPwProject['title'])?></h2><p<?= edu_content_lang_attr() ?>><?=e((string)$dashPwGroup['name'])?> · <?= e(tr('{done}/{total} úkolů hotovo', ['done' => (int)$dashPwHealth['tasks_done'], 'total' => (int)$dashPwHealth['tasks_total']])) ?></p></div><a class="btn secondary" href="<?=e(project_workspace_url((string)$dashPwProject['id'],'overview'))?>"><?= e(tr('Workspace →')) ?></a></div><div class="dashboard-project-workspace-grid"><div><span><?= e(tr('Moje role')) ?></span><strong><?= $dashPwRoles?e(implode(' + ',array_map(static fn($r)=>project_role_label((string)$r['role']),$dashPwRoles))):e(tr('Vyber si roli')) ?></strong></div><div><span>QA</span><strong><?= e(tr('{n} otevřeno', ['n' => (int)$dashPwHealth['qa_open']])) ?></strong></div><div><span><?= e(tr('Projekt')) ?></span><strong><?= (int)$dashPwHealth['completion'] ?> %</strong></div></div><?php if($dashPwNext):?><a class="dashboard-project-next" href="<?=e(project_workspace_url((string)$dashPwProject['id'],'work',['task'=>(string)$dashPwNext['id']]))?>"><span><?= e(tr('Moje další akce')) ?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$dashPwNext['title'])?></strong><small><?=e(project_workspace_task_status_label((string)$dashPwNext['status']))?> · <?= e(tr('pokračovat →')) ?></small></a><?php else:?><a class="dashboard-project-next clear" href="<?=e(project_workspace_url((string)$dashPwProject['id'],'work'))?>"><span><?= e(tr('Moje práce')) ?></span><strong><?= e(tr('Nemáš otevřený vlastní úkol')) ?></strong><small><?= e(tr('Podívej se na týmový board →')) ?></small></a><?php endif;?></section>
      <?php endif;?>

      <div class="dashboard-primary-grid">
        <article class="dashboard-panel xp-chart-panel">
          <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Tempo učení')) ?></div><h2><?= e(tr('XP za posledních 14 dní')) ?></h2><p><?= e(tr('Křivka ukazuje celkové XP, sloupce denní přírůstky.')) ?></p></div><div class="chart-total"><strong><?= $dashXp ?></strong><span><?= e(tr('XP celkem')) ?></span></div></div>
          <div class="xp-chart-wrap">
            <svg class="xp-chart" viewBox="0 0 <?= $chartW ?> <?= $chartH ?>" role="img" aria-label="<?= e(tr('Vývoj XP za posledních 14 dní')) ?>">
              <defs><linearGradient id="xpArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="currentColor" stop-opacity=".22"/><stop offset="1" stop-color="currentColor" stop-opacity="0"/></linearGradient></defs>
              <line class="chart-gridline" x1="<?= $padX ?>" y1="<?= $chartH-$padY ?>" x2="<?= $chartW-$padX ?>" y2="<?= $chartH-$padY ?>"/>
              <?php foreach($chartPoints as $p): $earned=(int)$p[2]['earned']; $bh=min(70,max(0,$earned)); ?>
                <?php if($earned>0): ?><rect class="xp-day-bar" x="<?= $p[0]-$barWidth/2 ?>" y="<?= $chartH-$padY-$bh ?>" width="<?= $barWidth ?>" height="<?= $bh ?>" rx="5"><title><?= e(tr('{date} · +{xp} XP', ['date' => date('d.m.',strtotime($p[2]['date'])), 'xp' => $earned])) ?></title></rect><?php endif; ?>
              <?php endforeach; ?>
              <polygon class="xp-area" points="<?= e($area) ?>"/>
              <polyline class="xp-line" points="<?= e($poly) ?>"/>
              <?php foreach($chartPoints as $p): ?><circle class="xp-dot" cx="<?= $p[0] ?>" cy="<?= $p[1] ?>" r="3.5"><title><?= e(tr('{date} · {total} XP celkem', ['date' => date('d.m.',strtotime($p[2]['date'])), 'total' => (int)$p[2]['total']])) ?></title></circle><?php endforeach; ?>
            </svg>
            <div class="chart-axis"><span><?= e(date('d.m.',strtotime($dashTimeline[0]['date']))) ?></span><span><?= e(tr('dnes · {date}', ['date' => date('d.m.')])) ?></span></div>
          </div>
        </article>

        <article class="dashboard-panel badge-showcase prestige-showcase">
          <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Trofeje · vzácné badge')) ?></div><h2><?= e(tr('{n} získáno', ['n' => count($earnedBadges)])) ?></h2><p><?= e(tr('Badge nejsou za běžné úkoly. Odemknou se až za level 10/20/30…, prestižní zkoušku, mimořádný test, mistrovský projekt nebo úplné zvládnutí části kurzu.')) ?></p></div><a class="btn secondary" href="?view=prestige_exams"><?= e(tr('Prestižní zkoušky →')) ?></a></div>
          <div class="badge-wall prestige-wall"<?= edu_content_lang_attr() ?>>
            <?php foreach($badgeDisplayIds as $badgeId): if(!isset($badgeDefs[$badgeId]))continue; $b=$badgeDefs[$badgeId]; $earned=isset($earnedBadges[$badgeId]); $rarity=(string)($b['rarity']??'epic'); ?>
              <div class="dashboard-badge prestige-badge <?= $earned?'earned':'locked' ?> rarity-<?= e($rarity) ?>"><i><?= e((string)$b['mark']) ?></i><div><small><?= e(strtoupper($rarity)) ?></small><strong><?= e((string)$b['title']) ?></strong><span><?= $earned?e((string)$b['text']):e((string)($b['condition']??tr('Vzácná podmínka'))) ?></span></div></div>
            <?php endforeach; ?>
          </div>
          <div class="prestige-note"><span>◆</span><p><strong><?= e(tr('Badge mají být výjimečné.')) ?></strong> <?= e(tr('Achievementy níže zachycují menší pokrok, takže nepřijdeš o průběžnou motivaci.')) ?></p></div>
        </article>
      </div>

      <section class="dashboard-panel achievements-panel">
        <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Achievementy')) ?></div><h2><?= e(tr('{earned}/{total} průběžných úspěchů', ['earned' => count($earnedAchievements), 'total' => count($achievementDefs)])) ?></h2><p><?= e(tr('Častější milníky ukazují, že se posouváš. Na rozdíl od badge nejsou vzácnou trofejí.')) ?></p></div></div>
        <div class="achievement-grid">
          <?php foreach($achievementDefs as $aid=>$a): $ap=$achievementProgress[$aid]??['current'=>0,'target'=>1,'percent'=>0,'earned'=>false]; $earned=!empty($ap['earned']); ?>
          <article class="achievement-card <?= $earned?'earned':'' ?>">
            <i><?= e((string)$a['mark']) ?></i><div><div class="achievement-title"><strong<?= edu_content_lang_attr() ?>><?= e((string)$a['title']) ?></strong><span><?= $earned?e(tr('✓ SPLNĚNO')):e(tr('{current} / {target}', ['current' => (int)$ap['current'], 'target' => (int)$ap['target']])) ?></span></div><p<?= edu_content_lang_attr() ?>><?= e((string)$a['text']) ?></p><b><em style="width:<?= (int)$ap['percent'] ?>%"></em></b></div>
          </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="dashboard-panel course-roadmap-panel">
        <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Dlouhodobá cesta')) ?></div><h2><?= e(tr('{n} lekcí · každý blok staví na předchozím', ['n' => count($dashCourseRows)])) ?></h2><p><?= in_array($classId,['class_1a','class_2a'],true) ? e(tr('Grafika a webdesign postupují od vizuálních principů přes responsive UI až k testování, handoffu a obhajitelnému capstone.')) : e(tr('Seminář OS a sítí postupuje od konektivity přes Linux správu až k bezpečné diagnostice, automatizaci a reliability.')) ?></p></div><a class="btn secondary" href="?view=course"><?= e(tr('Celá mapa kurzu →')) ?></a></div>
        <div class="dashboard-course-strip">
          <?php foreach($dashCourseRows as $r): $pct=(int)round(($r['done_steps']/max(1,$r['total_steps']))*100); ?>
          <a class="dash-course-node <?= $r['done']?'done':($r['unlocked']?'current':'locked') ?>" href="<?= $r['unlocked']?e((string)$r['href']):'#' ?>" <?= !$r['unlocked']?'aria-disabled="true"':'' ?>>
            <div class="dash-course-number"><?= str_pad((string)$r['number'],2,'0',STR_PAD_LEFT) ?></div><div><span><?= $r['done']?e(tr('Dokončeno')):($r['unlocked']?e(tr('Odemčeno')):e(tr('Zamčeno'))) ?></span><strong<?= edu_content_lang_attr() ?>><?= e(preg_replace('/^Lekce\s+\d+\s*·\s*/u','',(string)$r['title'])) ?></strong><small><?= e(tr('{done}/{total} kroků', ['done' => (int)$r['done_steps'], 'total' => (int)$r['total_steps']])) ?></small></div><i><em style="width:<?= $pct ?>%"></em></i>
          </a>
          <?php endforeach; ?>
        </div>
      </section>

      <div class="dashboard-secondary-grid">
        <article class="dashboard-panel learned-panel">
          <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Co už umíš')) ?></div><h2><?= e(tr('Zvládnuté znalosti')) ?></h2><p><?= e(tr('Dokončené Knowledge Tours z tvého aktuálního ročníku.')) ?></p></div><a class="text-link" href="?view=knowledgebase&reference=1"><?= e(tr('Knihovna →')) ?></a></div>
          <div class="learned-list">
            <?php if(!$dashLearned): ?><div class="dashboard-empty"><strong><?= e(tr('Zatím bez dokončené Knowledge lekce.')) ?></strong><span><?= e(tr('První se objeví hned po dokončení knowledge checku.')) ?></span></div><?php endif; ?>
            <?php foreach(array_slice($dashLearned,0,8) as $item): ?><a href="<?= e(module_url('kb_lesson',['topic'=>$item['topic']])) ?>"<?= edu_content_lang_attr() ?>><i>✓</i><div><strong><?= e((string)$item['title']) ?></strong><span><?= e(u_substr((string)$item['summary'],0,110)) ?><?= u_strlen((string)$item['summary'])>110?'…':'' ?></span></div></a><?php endforeach; ?>
          </div>
        </article>

        <article class="dashboard-panel test-panel">
          <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Výsledky')) ?></div><h2><?= e(tr('Testy a praktická práce')) ?></h2><p><?= e(tr('Historie se váže ke školnímu profilu, ne jen k aktuální session.')) ?></p></div></div>
          <div class="test-history">
            <?php if(!$dashTests): ?><div class="dashboard-empty"><strong><?= e(tr('Startovní test zatím není uložený.')) ?></strong><span><?= e(tr('Po dokončení tu uvidíš skóre i datum.')) ?></span></div><?php endif; ?>
            <?php foreach(array_slice($dashTests,0,4) as $t): $score=(int)($t['score']??0);$max=max(1,(int)($t['max_score']??1));$pct=(int)round($score/$max*100); ?><div class="test-history-row"><div class="test-score-ring" style="--score:<?= $pct ?>%"><strong><?= $pct ?>%</strong></div><div><strong><?= e(tr('Startovní test')) ?></strong><span><?= e(tr('{score}/{max} správně · {date}', ['score' => $score, 'max' => $max, 'date' => date('d.m.Y',strtotime((string)($t['finished_at']??'now')))])) ?></span></div></div><?php endforeach; ?>
            <div class="practice-summary"><span><b><?= count($dashLabs) ?></b> <?= e(tr('praktických labů')) ?></span><span><b><?= count($dashGraphicsSubmissions) ?></b> <?= e(tr('grafických odevzdání')) ?></span></div>
          </div>
        </article>
      </div>

      <section class="dashboard-panel project-results-preview">
        <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Projektové hodnocení')) ?></div><h2><?= e(tr('Rubriky, známky a zpětná vazba')) ?></h2><p><?= e(tr('Vidíš pouze hodnocení, která učitel publikoval nebo vrátil k dopracování.')) ?></p></div><a class="btn secondary" href="?view=project_results"><?= e(tr('Všechny výsledky →')) ?></a></div>
        <div class="project-result-preview-grid">
          <?php if(!$dashProjectResults): ?><div class="dashboard-empty"><strong><?= e(tr('Zatím žádné publikované projektové hodnocení.')) ?></strong><span><?= e(tr('Až učitel zveřejní rubriku, objeví se tu.')) ?></span></div><?php endif; ?>
          <?php foreach(array_slice($dashProjectResults,0,3) as $item): $r=$item['record'];$p=$item['project'];$returned=(string)($r['status']??'')==='returned'; ?><a href="<?= e(module_url('project_result',['record'=>(string)$r['id']])) ?>"><div><span><?= $returned?e(tr('K dopracování')):(((string)$r['target_type']==='group')?e(tr('Týmový projekt')):e(tr('Projekt'))) ?></span><strong><?= e((string)$p['title']) ?></strong><small><?= e(tr('{points}/{max} bodů', ['points' => (int)$item['points'], 'max' => (int)$item['max_points']])) ?></small></div><b class="<?= $returned?'returned':'' ?>"><?= $returned?'↻':e((string)$item['grade']) ?></b></a><?php endforeach; ?>
        </div>
      </section>

      <div class="dashboard-secondary-grid dashboard-bottom-grid">
        <article class="dashboard-panel activity-panel">
          <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Poslední aktivita')) ?></div><h2><?= e(tr('Co se ti právě započítalo')) ?></h2></div></div>
          <div class="activity-feed">
            <?php if(!$dashActivity): ?><div class="dashboard-empty"><strong><?= e(tr('Aktivita se začne zapisovat s prvními XP.')) ?></strong></div><?php endif; ?>
            <?php foreach($dashActivity as $a): $label=str_replace(['kb:','course:','next:','studio:','test_correct:','journey:'],[tr('Knowledge · '),tr('Lekce · '),tr('Lekce 2 · '),tr('Studio · '),tr('Test · '),tr('Cesta · ')],$a['key']); ?><div><i>+<?= (int)$a['xp'] ?></i><span><strong><?= e(u_substr($label,0,68)) ?></strong><small><?= e(date('d.m. H:i',strtotime($a['at'])?:time())) ?></small></span></div><?php endforeach; ?>
          </div>
        </article>
        <article class="dashboard-panel quick-panel">
          <div class="dashboard-panel-head"><div><div class="eyebrow"><?= e(tr('Rychlý přístup')) ?></div><h2><?= e(tr('Kam chceš pokračovat?')) ?></h2></div></div>
          <div class="quick-action-grid"><a href="?view=course"><i>↗</i><strong><?= e(tr('Kurz')) ?></strong><span><?= e(tr('{n} navazujících lekcí', ['n' => count($dashCourseRows)])) ?></span></a><a href="?view=knowledgebase&reference=1"><i>▦</i><strong><?= e(tr('Materiály')) ?></strong><span><?= e(tr('{n} materiálů pro ročník', ['n' => $dashKbTotal])) ?></span></a><?php if(in_array($classId,['class_1a','class_2a'],true)): ?><a href="?view=graphics_studio"><i>◇</i><strong><?= e(tr('Studio')) ?></strong><span><?= e(tr('Interaktivní sandbox')) ?></span></a><?php else: ?><a href="?view=practice"><i>⌘</i><strong><?= e(tr('Laboratoř')) ?></strong><span><?= e(tr('Evidence-first diagnostika')) ?></span></a><?php endif; ?></div>
        </article>
      </div>
        </div>
      </details>
    </section>

    <form class="inline-form dashboard-logout" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="logout_class"><span><?= tr_html('Přihlášen/a jako {name}', ['name' => '<strong>' . e($studentName) . '</strong>']) ?></span><button class="link-button" type="submit"><?= e(tr('Odhlásit')) ?></button></form>
    <script>(()=>{const b=document.querySelector('[data-focus-mode-toggle]');if(!b)return;const key='educanetFocusMode';const T_OVERVIEW=<?= json_encode(tr('Zobrazit celý přehled')) ?>;const T_FOCUS=<?= json_encode(tr('Soustředěný režim')) ?>;const apply=on=>{document.body.classList.toggle('student-focus-mode',on);b.setAttribute('aria-pressed',on?'true':'false');b.textContent=on?T_OVERVIEW:T_FOCUS};const saved=localStorage.getItem(key);apply(saved===null?true:saved==='1');b.addEventListener('click',()=>{const on=!document.body.classList.contains('student-focus-mode');localStorage.setItem(key,on?'1':'0');apply(on)})})();</script>
    <?php
    render_footer();
    exit;
}
