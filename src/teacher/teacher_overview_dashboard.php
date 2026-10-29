<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * One-page snapshot for the teacher cockpit. All values are derived from the
 * same curriculum, project and Skill Mastery sources used by their detail views.
 */
function teacher_overview_class_snapshot(string $classId): array
{
    $specs = teacher_curriculum_spec();
    $spec = is_array($specs[$classId] ?? null) ? $specs[$classId] : [];
    $lessons = teacher_curriculum_lessons($classId);
    $curriculumState = teacher_curriculum_state($classId);

    $contentReady = 0;
    $firstContentIssue = null;
    foreach ($lessons as $lesson) {
        $readiness = teacher_curriculum_lesson_readiness($lesson);
        if (!empty($readiness['ready'])) {
            $contentReady++;
        } elseif ($firstContentIssue === null) {
            $firstContentIssue = [
                'number' => (int)($lesson['number'] ?? 0),
                'title' => (string)($lesson['title'] ?? 'Lekce'),
            ];
        }
    }

    $prepared = 0;
    $nextLesson = null;
    foreach ($lessons as $lesson) {
        $n = (int)($lesson['number'] ?? 0);
        $isPrepared = !empty($curriculumState['lessons'][(string)$n]['prepared']);
        if ($isPrepared) $prepared++;
        elseif ($nextLesson === null) $nextLesson = ['number'=>$n,'title'=>(string)($lesson['title'] ?? ('Lekce '.$n))];
    }

    $checklistDefs = teacher_curriculum_checklist_definitions($classId);
    $checklistItems = [];
    foreach ($checklistDefs as $section) {
        foreach ((array)($section['items'] ?? []) as $id => $text) $checklistItems[(string)$id] = (string)$text;
    }
    $checklistDone = 0;
    foreach (array_keys($checklistItems) as $id) if (!empty($curriculumState['items'][$id]['done'])) $checklistDone++;
    $checklistTotal = count($checklistItems);

    $catalog = project_catalog();
    $projects = array_values((array)($catalog[$classId] ?? []));
    $records = project_grade_records_for_class($classId);
    $recordStats = ['published'=>0,'draft'=>0,'returned'=>0];
    foreach ($records as $record) {
        $status = (string)($record['status'] ?? 'draft');
        if (isset($recordStats[$status])) $recordStats[$status]++;
    }

    $groupCount = 0;
    $workspaceCompletions = [];
    $workspaceSignals = [];
    $workspaceHigh = 0;
    $workspaceMedium = 0;
    foreach ($projects as $project) {
        if ((string)($project['type'] ?? '') !== 'group') continue;
        $projectId = (string)($project['id'] ?? '');
        if ($projectId === '') continue;
        $groups = project_groups_for($classId, $projectId);
        $groupCount += count($groups);
        foreach ($groups as $group) {
            if (!is_array($group) || empty($group['id'])) continue;
            try {
                $health = project_workspace_health($classId, $projectId, (string)$group['id']);
                $workspaceCompletions[] = (int)($health['completion'] ?? 0);
                foreach ((array)($health['signals'] ?? []) as $signal) {
                    if (!is_array($signal)) continue;
                    $severity = (string)($signal['severity'] ?? 'ok');
                    if ($severity === 'high') $workspaceHigh++;
                    elseif ($severity === 'medium') $workspaceMedium++;
                    if ($severity !== 'ok') {
                        $workspaceSignals[] = [
                            'severity'=>$severity,
                            'team'=>(string)($group['name'] ?? 'Tým'),
                            'text'=>(string)($signal['text'] ?? ''),
                            'project_id'=>$projectId,
                            'group_id'=>(string)$group['id'],
                        ];
                    }
                }
            } catch (Throwable) {
                // Older/non-workspace group projects should never break the overview.
            }
        }
    }
    $workspaceAverage = $workspaceCompletions ? (int)round(array_sum($workspaceCompletions) / count($workspaceCompletions)) : null;

    $capstone = null;
    $capstoneId = (string)($spec['capstone_project_id'] ?? '');
    if ($capstoneId !== '') $capstone = project_find($classId, $capstoneId);
    $capstoneGroups = $capstone ? project_groups_for($classId, $capstoneId) : [];
    $capstoneCompletionValues = [];
    foreach ($capstoneGroups as $group) {
        if (!is_array($group) || empty($group['id'])) continue;
        try { $capstoneCompletionValues[] = project_workspace_completion((string)$group['id']); } catch (Throwable) {}
    }
    $capstoneCompletion = $capstoneCompletionValues ? (int)round(array_sum($capstoneCompletionValues)/count($capstoneCompletionValues)) : null;

    $matrix = skill_class_matrix($classId);
    $skillIndexes = skill_runtime_indexes();
    $classEvidence = array_values((array)($skillIndexes['evidence_by_class'][$classId] ?? []));
    $hasMasteryData = !empty($classEvidence);
    $relevantBranches = skill_relevant_branches($classId);
    $branchDefs = skill_branches();
    $branchAverages = [];
    foreach ($relevantBranches as $branch) {
        $values = [];
        foreach ($matrix as $row) $values[] = (float)($row['branches'][$branch]['mastery_percent'] ?? 0);
        $branchAverages[$branch] = [
            'label'=>(string)($branchDefs[$branch]['name'] ?? $branch),
            'icon'=>(string)($branchDefs[$branch]['icon'] ?? '◇'),
            'value'=>$hasMasteryData && $values ? round(array_sum($values)/count($values), 1) : null,
        ];
    }
    $overallValues = array_map(static fn($row): float => (float)($row['overall'] ?? 0), $matrix);
    $overallMastery = $hasMasteryData && $overallValues ? round(array_sum($overallValues)/count($overallValues), 1) : null;
    $pendingValidations = count(skill_pending_validations($classId));
    $students = project_students_for_class($classId);

    $attention = [];
    if ($contentReady < count($lessons)) $attention[] = ['severity'=>'high','label'=>'Obsah','text'=>'Některá lekce nemá kompletní strukturu.'];
    if ($workspaceHigh > 0) {
        $firstSignal=$workspaceSignals[0]??null;
        $attention[] = ['severity'=>'high','label'=>'Projekty','text'=>$workspaceHigh.' týmových signálů vyžaduje rychlou kontrolu.','url'=>$firstSignal?'?tab=workspace&class='.rawurlencode($classId).'&project='.rawurlencode((string)$firstSignal['project_id']).'&group='.rawurlencode((string)$firstSignal['group_id']):'?tab=workspace&class='.rawurlencode($classId)];
    } elseif ($workspaceMedium > 0) {
        $firstSignal=$workspaceSignals[0]??null;
        $attention[] = ['severity'=>'medium','label'=>'Projekty','text'=>$workspaceMedium.' týmových signálů stojí za kontrolu.','url'=>$firstSignal?'?tab=workspace&class='.rawurlencode($classId).'&project='.rawurlencode((string)$firstSignal['project_id']).'&group='.rawurlencode((string)$firstSignal['group_id']):'?tab=workspace&class='.rawurlencode($classId)];
    }
    if ($pendingValidations > 0) $attention[] = ['severity'=>'medium','label'=>'Mastery','text'=>$pendingValidations.' praktických evidencí čeká na validaci.','url'=>'?tab=skills&class='.rawurlencode($classId)];
    if ($prepared < count($lessons)) $attention[] = ['severity'=>'info','label'=>'Příprava','text'=>'Připraveno '.$prepared.' z '.count($lessons).' lekcí.','url'=>'?tab=curriculum&class='.rawurlencode($classId).'#lesson-plan'];
    if ($checklistDone < $checklistTotal) $attention[] = ['severity'=>'info','label'=>'Checklist','text'=>'Hotovo '.$checklistDone.' z '.$checklistTotal.' položek.','url'=>'?tab=curriculum&class='.rawurlencode($classId)];

    if ($firstContentIssue !== null) {
        $nextAction = [
            'label'=>'Opravit obsah lekce '.$firstContentIssue['number'],
            'detail'=>$firstContentIssue['title'],
            'url'=>'?tab=curriculum&class='.rawurlencode($classId).'#lesson-plan',
            'severity'=>'high',
        ];
    } elseif ($workspaceHigh > 0 && $workspaceSignals) {
        $signal = $workspaceSignals[0];
        $nextAction = [
            'label'=>'Zkontrolovat tým '.$signal['team'],
            'detail'=>$signal['text'],
            'url'=>'?tab=workspace&class='.rawurlencode($classId).'&project='.rawurlencode((string)$signal['project_id']).'&group='.rawurlencode((string)$signal['group_id']),
            'severity'=>'high',
        ];
    } elseif ($pendingValidations > 0) {
        $nextAction = [
            'label'=>'Vyřídit Mastery validace',
            'detail'=>$pendingValidations.' čeká na kontrolu',
            'url'=>'?tab=skills&class='.rawurlencode($classId),
            'severity'=>'medium',
        ];
    } elseif ($nextLesson !== null) {
        $nextAction = [
            'label'=>'Připravit lekci '.$nextLesson['number'],
            'detail'=>$nextLesson['title'],
            'url'=>'?tab=curriculum&class='.rawurlencode($classId).'#lesson-plan',
            'severity'=>'info',
        ];
    } elseif ($checklistDone < $checklistTotal) {
        $nextAction = [
            'label'=>'Dokončit učitelský checklist',
            'detail'=>($checklistTotal-$checklistDone).' položek zbývá',
            'url'=>'?tab=curriculum&class='.rawurlencode($classId),
            'severity'=>'info',
        ];
    } else {
        $nextAction = [
            'label'=>'Bez kritických úkolů',
            'detail'=>'Výuka a checklist jsou připravené.',
            'url'=>'?tab=curriculum&class='.rawurlencode($classId),
            'severity'=>'ok',
        ];
    }

    return [
        'class_id'=>$classId,
        'class_label'=>teacher_class_label($classId),
        'subject'=>(string)($spec['subject'] ?? ''),
        'focus'=>(string)($spec['focus'] ?? ''),
        'lessons_total'=>count($lessons),
        'content_ready'=>$contentReady,
        'prepared'=>$prepared,
        'checklist_done'=>$checklistDone,
        'checklist_total'=>$checklistTotal,
        'projects_total'=>count($projects),
        'project_records_total'=>count($records),
        'record_stats'=>$recordStats,
        'groups_total'=>$groupCount,
        'workspace_average'=>$workspaceAverage,
        'workspace_high'=>$workspaceHigh,
        'workspace_medium'=>$workspaceMedium,
        'workspace_signals'=>$workspaceSignals,
        'capstone_name'=>(string)($spec['capstone'] ?? ($capstone['title'] ?? 'Capstone')),
        'capstone_id'=>$capstoneId,
        'capstone_groups'=>count($capstoneGroups),
        'capstone_completion'=>$capstoneCompletion,
        'students'=>count($students),
        'mastery_overall'=>$overallMastery,
        'mastery_branches'=>$branchAverages,
        'pending_validations'=>$pendingValidations,
        'attention'=>$attention,
        'next_action'=>$nextAction,
        'curriculum_updated_at'=>$curriculumState['updated_at'] ?? null,
    ];
}

function teacher_overview_percent(int $done,int $total): int
{
    return $total > 0 ? (int)round(($done / $total) * 100) : 0;
}

function teacher_render_global_overview(): void
{
    $classIds = ['class_1a','class_2a','class_3a','class_4a'];
    if (function_exists('teacher59_can_class')) $classIds = array_values(array_filter($classIds, static fn(string $c): bool => teacher59_can_class($c))); // v59: jen třídy v rozsahu
    $snapshots = [];
    foreach ($classIds as $classId) $snapshots[$classId] = teacher_overview_class_snapshot($classId);

    $lessonsPrepared = array_sum(array_column($snapshots,'prepared'));
    $lessonsTotal = array_sum(array_column($snapshots,'lessons_total'));
    $contentReady = array_sum(array_column($snapshots,'content_ready'));
    $checklistDone = array_sum(array_column($snapshots,'checklist_done'));
    $checklistTotal = array_sum(array_column($snapshots,'checklist_total'));
    $groups = array_sum(array_column($snapshots,'groups_total'));
    $pending = array_sum(array_column($snapshots,'pending_validations'));
    $highSignals = array_sum(array_column($snapshots,'workspace_high'));
    $mediumSignals = array_sum(array_column($snapshots,'workspace_medium'));
    $masteryValues = array_values(array_filter(array_column($snapshots,'mastery_overall'),static fn($v): bool => $v !== null));
    $masteryOverall = $masteryValues ? round(array_sum($masteryValues)/count($masteryValues),1) : null;

    $attention = [];
    foreach ($snapshots as $snapshot) {
        foreach ((array)$snapshot['attention'] as $item) {
            if (($item['severity'] ?? '') === 'info') continue;
            $attention[] = array_merge($item,['class_id'=>$snapshot['class_id'],'class_label'=>$snapshot['class_label']]);
        }
    }
    usort($attention, static function(array $a,array $b): int {
        $rank=['high'=>0,'medium'=>1,'info'=>2,'ok'=>3];
        return ($rank[$a['severity']??'info']??9) <=> ($rank[$b['severity']??'info']??9);
    });
    ?>
    <section class="teacher-hero overview-global-hero"><div><div class="eyebrow">Teacher Overview · <?=e(teacher_overview_scope_label(array_keys($snapshots)))?></div><h1>Celá výuka na jedné stránce</h1><p>Stav <?=(int)$lessonsTotal?> strukturovaných dvouhodinových lekcí, učitelských checklistů, 40 středečních bloků na třídu, týmových projektů a Skill Mastery. Detailní pohledy zůstávají dostupné jedním kliknutím.</p></div><div class="teacher-hero-actions"><a class="btn secondary" href="materials/TEACHER_OVERVIEW.md" target="_blank">Materiálový přehled ↗</a><button class="btn primary" type="button" onclick="window.print()">Tisk / PDF</button></div></section>

    <section class="overview-global-kpis">
      <article><span>Obsah lekcí</span><strong><?=$contentReady?> / <?=$lessonsTotal?></strong><small><?=teacher_overview_percent($contentReady,$lessonsTotal)?> % strukturálně připraveno</small></article>
      <article><span>Moje příprava</span><strong><?=$lessonsPrepared?> / <?=$lessonsTotal?></strong><small><?=teacher_overview_percent($lessonsPrepared,$lessonsTotal)?> % lekcí potvrzeno</small></article>
      <article><span>Checklisty</span><strong><?=$checklistDone?> / <?=$checklistTotal?></strong><small><?=teacher_overview_percent($checklistDone,$checklistTotal)?> % učitelské přípravy</small></article>
      <article><span>Aktivní týmy</span><strong><?=$groups?></strong><small><?=$highSignals?> kritických · <?=$mediumSignals?> k prověření</small></article>
      <article><span>Skill Mastery</span><strong><?=$masteryOverall===null?'—':number_format($masteryOverall,0).' %'?></strong><small><?=$masteryOverall===null?'čeká na studentská data':'průměr tříd s dostupnými daty'?></small></article>
      <article><span>Validace</span><strong><?=$pending?></strong><small>praktických evidencí čeká</small></article>
    </section>

    <?php if($attention): ?>
    <section class="teacher-panel overview-attention-panel"><div class="teacher-panel-head"><div><span>Attention feed</span><h2>Co potřebuje pozornost napříč třídami</h2></div><b><?=count($attention)?></b></div><div class="overview-attention-list">
      <?php foreach(array_slice($attention,0,8) as $item): ?>
      <a class="severity-<?=e((string)$item['severity'])?>" href="<?=e((string)($item['url']??('?tab=curriculum&class='.rawurlencode((string)$item['class_id']))))?>"><span><?=e((string)$item['class_label'])?></span><strong><?=e((string)$item['label'])?></strong><p><?=e((string)$item['text'])?></p><i>→</i></a>
      <?php endforeach; ?>
    </div></section>
    <?php endif; ?>

    <section class="overview-class-grid">
    <?php foreach($snapshots as $snapshot):
        $lessonPct=teacher_overview_percent((int)$snapshot['prepared'],(int)$snapshot['lessons_total']);
        $checkPct=teacher_overview_percent((int)$snapshot['checklist_done'],(int)$snapshot['checklist_total']);
        $action=(array)$snapshot['next_action'];
    ?>
      <article class="overview-class-card">
        <header><div><span><?=e((string)$snapshot['class_label'])?></span><h2><?=e((string)$snapshot['subject'])?></h2><p><?=e((string)$snapshot['focus'])?></p></div><a class="overview-open-class" href="?tab=class_overview&class=<?=e((string)$snapshot['class_id'])?>" aria-label="Otevřít detail <?=e((string)$snapshot['class_label'])?>">→</a></header>

        <div class="overview-progress-stack">
          <div><div><span>Lekce připravené učitelem</span><b><?=$snapshot['prepared']?> / <?=$snapshot['lessons_total']?></b></div><progress max="100" value="<?=$lessonPct?>"></progress></div>
          <div><div><span>Checklist</span><b><?=$snapshot['checklist_done']?> / <?=$snapshot['checklist_total']?></b></div><progress max="100" value="<?=$checkPct?>"></progress></div>
        </div>

        <div class="overview-class-metrics">
          <div><span>Obsah</span><strong><?=$snapshot['content_ready']?> / <?=$snapshot['lessons_total']?></strong><small>lekcí OK</small></div>
          <div><span>Projekty</span><strong><?=$snapshot['projects_total']?></strong><small><?=$snapshot['groups_total']?> týmů</small></div>
          <div><span>Mastery</span><strong><?=$snapshot['mastery_overall']===null?'—':number_format((float)$snapshot['mastery_overall'],0).'%'?></strong><small><?=$snapshot['students']?> studentů</small></div>
        </div>

        <section class="overview-capstone"><div><span>Capstone</span><strong><?=e((string)$snapshot['capstone_name'])?></strong></div><div class="overview-capstone-state"><?php if($snapshot['capstone_groups']>0): ?><b><?=$snapshot['capstone_completion']===null?'0':$snapshot['capstone_completion']?>%</b><small><?=$snapshot['capstone_groups']?> týmů</small><?php else: ?><b>—</b><small>týmy zatím nevytvořeny</small><?php endif; ?></div></section>
        <div class="overview-project-status"><span><i>Publikováno</i><b><?= (int)$snapshot['record_stats']['published'] ?></b></span><span><i>Koncepty</i><b><?= (int)$snapshot['record_stats']['draft'] ?></b></span><span><i>K dopracování</i><b><?= (int)$snapshot['record_stats']['returned'] ?></b></span><span class="<?=((int)$snapshot['workspace_high']>0?'danger':((int)$snapshot['workspace_medium']>0?'warn':'ok'))?>"><i>Team signals</i><b><?= (int)$snapshot['workspace_high'] + (int)$snapshot['workspace_medium'] ?></b></span></div>

        <section class="overview-mastery-branches"><div class="overview-section-label"><span>Skill Mastery</span><a href="?tab=skills&class=<?=e((string)$snapshot['class_id'])?>">Detail →</a></div><?php foreach($snapshot['mastery_branches'] as $branch): $v=$branch['value']; ?><div class="overview-mastery-row"><span><i><?=e((string)$branch['icon'])?></i><?=e((string)$branch['label'])?></span><div><progress max="100" value="<?=$v===null?0:(float)$v?>"></progress><b><?=$v===null?'—':number_format((float)$v,0).'%'?></b></div></div><?php endforeach; ?><?php if(!$snapshot['mastery_branches']): ?><div class="teacher-empty">Pro třídu nejsou definované Skill Mastery větve.</div><?php endif; ?></section>

        <a class="overview-next-action severity-<?=e((string)($action['severity']??'info'))?>" href="<?=e((string)($action['url']??'#'))?>"><span>Další doporučená akce</span><strong><?=e((string)($action['label']??''))?></strong><small><?=e((string)($action['detail']??''))?></small><i>→</i></a>

        <footer><a href="?tab=class_overview&class=<?=e((string)$snapshot['class_id'])?>">Třída</a><a href="?tab=class_results&class=<?=e((string)$snapshot['class_id'])?>">Výsledky</a><a href="?tab=curriculum&class=<?=e((string)$snapshot['class_id'])?>">Výuka</a><a href="?tab=skills&class=<?=e((string)$snapshot['class_id'])?>">Skills</a></footer>
      </article>
    <?php endforeach; ?>
    </section>
    <?php
}

function teacher_render_adaptive_interventions(): void
{
    $classes=['class_1a','class_2a','class_3a','class_4a'];if(function_exists('teacher59_can_class'))$classes=array_values(array_filter($classes,static fn(string $c):bool=>teacher59_can_class($c)));$all=[]; // v59: jen třídy v rozsahu
    foreach($classes as $cid){foreach(adaptive_intervention_feed($cid) as $row){$row['class_id']=$cid;$all[]=$row;}}
    usort($all,static function($a,$b){$w=['high'=>3,'medium'=>2,'info'=>1];return ($w[$b['severity']]??0)<=>($w[$a['severity']]??0);});
    ?>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Adaptive learning</span><h2>Komu má smysl krátce pomoct</h2></div><small>Jen akční signály · žádné skryté skóre ani automatická penalizace</small></div>
      <?php if(!$all): ?><div class="teacher-empty">Zatím žádný student nepotřebuje adaptivní zásah podle dostupných dat.</div><?php else: ?><div class="adaptive-teacher-feed"><?php foreach(array_slice($all,0,12) as $row): ?><article class="adaptive-teacher-student <?=e((string)$row['severity'])?>"><div><strong><?=e((string)$row['student_label'])?></strong><small><?=e(teacher_class_label((string)$row['class_id']))?></small></div><div class="adaptive-teacher-signals"><?php foreach((array)$row['signals'] as $signal): ?><span><?=e((string)$signal['label'])?> · <?=e((string)$signal['text'])?></span><?php endforeach;?></div><a class="btn secondary small" href="?tab=skills&class=<?=e((string)$row['class_id'])?>&student=<?=e((string)$row['student_key'])?>">Detail →</a></article><?php endforeach;?></div><?php endif; ?>
      <div class="teacher-muted" style="margin-top:12px">Signál vznikne například při opakovaných retrieval chybách, nedokončeném Recovery Pathu nebo opakované nejasnosti v Learning Journalu. Učitel vždy rozhoduje, zda je zásah vůbec potřeba.</div>
    </section>
    <?php
}
