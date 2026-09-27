<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function teacher_class_pct(?float $value, int $decimals = 0): string
{
    return $value === null ? '—' : number_format($value, $decimals, ',', ' ') . ' %';
}

function teacher_class_grade(?float $value): string
{
    return $value === null ? '—' : number_format($value, 2, ',', ' ');
}

function teacher_class_result_timestamp(array $row): int
{
    foreach (['finished_at','attempted_at','submitted_at','updated_at','created_at'] as $key) {
        $ts = strtotime((string)($row[$key] ?? '')) ?: 0;
        if ($ts > 0) return $ts;
    }
    return 0;
}

function teacher_class_activity_index(string $classId): array
{
    $cacheKey = 'teacher_class_activity:' . $classId;
    if (isset($GLOBALS['educanet_runtime_indexes'][$cacheKey]) && is_array($GLOBALS['educanet_runtime_indexes'][$cacheKey])) {
        return $GLOBALS['educanet_runtime_indexes'][$cacheKey];
    }

    $students = project_students_for_class($classId);
    $byName = [];
    foreach ($students as $key => $student) {
        $normalized = normalized_person_name((string)($student['label'] ?? ''));
        if ($normalized !== '') $byName[$normalized] = (string)$key;
    }

    $base = static fn(): array => [
        'tests'=>0,'best_test'=>null,'test_sum'=>0.0,'test_scored'=>0,
        'labs'=>0,'prestige_attempts'=>0,'prestige_passed'=>0,'best_prestige'=>null,
        'last_activity'=>0,
    ];
    $out = [];
    foreach (array_keys($students) as $key) $out[(string)$key] = $base();

    $resolveKey = static function(array $row) use ($classId, $byName, $students): ?string {
        if ((string)($row['class_id'] ?? '') !== $classId) return null;
        $label = trim((string)($row['student_label'] ?? ''));
        if ($label !== '') {
            $normalized = normalized_person_name($label);
            if (isset($byName[$normalized])) return $byName[$normalized];
        }
        $email = strtolower(trim((string)($row['student_email'] ?? '')));
        if ($email !== '') {
            foreach ($students as $key => $student) if (strtolower((string)($student['email'] ?? '')) === $email) return (string)$key;
        }
        return null;
    };

    $tests = storage_stream_rows('practice_results');
    foreach (is_array($tests) ? $tests : [] as $row) {
        if (!is_array($row) || !($key = $resolveKey($row))) continue;
        $out[$key]['tests']++;
        $score = (float)($row['score'] ?? 0); $max = max(1.0, (float)($row['max_score'] ?? 1)); $pct = max(0.0, min(100.0, $score / $max * 100));
        $out[$key]['best_test'] = $out[$key]['best_test'] === null ? $pct : max((float)$out[$key]['best_test'], $pct);
        $out[$key]['test_sum'] += $pct; $out[$key]['test_scored']++;
        $out[$key]['last_activity'] = max((int)$out[$key]['last_activity'], teacher_class_result_timestamp($row));
    }

    $labs = storage_stream_rows('lab_results');
    foreach (is_array($labs) ? $labs : [] as $row) {
        if (!is_array($row) || !($key = $resolveKey($row))) continue;
        $out[$key]['labs']++;
        $out[$key]['last_activity'] = max((int)$out[$key]['last_activity'], teacher_class_result_timestamp($row));
    }

    $prestige = special_exam_results();
    foreach ($prestige as $row) {
        if (!is_array($row) || !($key = $resolveKey($row))) continue;
        $out[$key]['prestige_attempts']++;
        if (!empty($row['passed'])) $out[$key]['prestige_passed']++;
        $score = (float)($row['score'] ?? 0); $max = max(1.0, (float)($row['max_score'] ?? 1)); $pct = max(0.0, min(100.0, $score / $max * 100));
        $out[$key]['best_prestige'] = $out[$key]['best_prestige'] === null ? $pct : max((float)$out[$key]['best_prestige'], $pct);
        $out[$key]['last_activity'] = max((int)$out[$key]['last_activity'], teacher_class_result_timestamp($row));
    }

    foreach ($out as &$row) {
        $row['avg_test'] = $row['test_scored'] > 0 ? $row['test_sum'] / $row['test_scored'] : null;
        unset($row['test_sum'], $row['test_scored']);
    }
    unset($row);

    return $GLOBALS['educanet_runtime_indexes'][$cacheKey] = $out;
}

function teacher_class_results_snapshot(string $classId): array
{
    $cacheKey = 'teacher_class_results:' . $classId;
    if (isset($GLOBALS['educanet_runtime_indexes'][$cacheKey]) && is_array($GLOBALS['educanet_runtime_indexes'][$cacheKey])) {
        return $GLOBALS['educanet_runtime_indexes'][$cacheKey];
    }

    $students = project_students_for_class($classId);
    $catalog = array_values((array)(project_catalog()[$classId] ?? []));
    $activity = teacher_class_activity_index($classId);
    $skillRows = skill_class_matrix($classId);
    $masteryByName = [];
    foreach ($skillRows as $row) $masteryByName[normalized_person_name((string)($row['label'] ?? ''))] = (float)($row['overall'] ?? 0);

    $groupsById = [];
    foreach ($catalog as $project) {
        if (!is_array($project) || (string)($project['type'] ?? '') !== 'group') continue;
        foreach (project_groups_for($classId, (string)($project['id'] ?? '')) as $group) if (is_array($group)) $groupsById[(string)($group['id'] ?? '')] = $group;
    }

    $studentRows = [];
    foreach ($students as $key => $student) {
        $studentRows[(string)$key] = [
            'key'=>(string)$key,
            'label'=>(string)($student['label'] ?? ''),
            'seat_label'=>(string)($student['seat_label'] ?? ''),
            'email'=>(string)($student['email'] ?? ''),
            'published'=>0,'returned'=>0,'drafts'=>0,'project_pct_sum'=>0.0,'project_pct_count'=>0,'grade_sum'=>0.0,'grade_count'=>0,
            'excellent'=>0,'needs_work'=>0,'last_project_activity'=>0,
            'activity'=>$activity[(string)$key] ?? [],
            'mastery'=>$masteryByName[normalized_person_name((string)($student['label'] ?? ''))] ?? null,
        ];
    }

    $projectStats = [];
    foreach ($catalog as $project) {
        if (!is_array($project)) continue;
        $id = (string)($project['id'] ?? '');
        if ($id === '') continue;
        $projectStats[$id] = [
            'id'=>$id,'title'=>(string)($project['title'] ?? 'Projekt'),'type'=>(string)($project['type'] ?? 'individual'),
            'published'=>0,'returned'=>0,'drafts'=>0,'pct_sum'=>0.0,'pct_count'=>0,'grade_counts'=>[1=>0,2=>0,3=>0,4=>0,5=>0],
        ];
    }

    foreach (project_grade_records_for_class($classId) as $record) {
        if (!is_array($record)) continue;
        $projectId = (string)($record['project_id'] ?? '');
        $status = (string)($record['status'] ?? 'draft');
        if (isset($projectStats[$projectId][$status])) $projectStats[$projectId][$status]++;
        $maxPoints = max(1, (int)($record['max_points'] ?? 1));
        $basePoints = max(0, (int)($record['points'] ?? 0));
        $baseGrade = max(1, min(5, (int)($record['grade'] ?? 5)));
        $updated = strtotime((string)($record['updated_at'] ?? '')) ?: 0;

        $targets = [];
        if ((string)($record['target_type'] ?? '') === 'individual') {
            $target = (string)($record['target_id'] ?? '');
            if (isset($studentRows[$target])) $targets[$target] = ['points'=>$basePoints,'grade'=>$baseGrade];
        } else {
            $group = $groupsById[(string)($record['target_id'] ?? '')] ?? null;
            if (is_array($group)) foreach ((array)($group['member_keys'] ?? []) as $memberKey) {
                $memberKey = (string)$memberKey; if (!isset($studentRows[$memberKey])) continue;
                $adjustment = is_array(($record['member_adjustments'] ?? [])[$memberKey] ?? null) ? $record['member_adjustments'][$memberKey] : [];
                $points = max(0, min($maxPoints, $basePoints + (int)($adjustment['points_delta'] ?? 0)));
                $grade = isset($adjustment['grade_override']) && is_numeric($adjustment['grade_override'])
                    ? max(1, min(5, (int)$adjustment['grade_override']))
                    : ((bool)($record['grade_overridden'] ?? false) ? $baseGrade : project_suggested_grade($points, $maxPoints));
                $targets[$memberKey] = ['points'=>$points,'grade'=>$grade];
            }
        }

        foreach ($targets as $studentKey => $result) {
            if ($status === 'published') $studentRows[$studentKey]['published']++;
            elseif ($status === 'returned') $studentRows[$studentKey]['returned']++;
            else $studentRows[$studentKey]['drafts']++;
            $studentRows[$studentKey]['last_project_activity'] = max((int)$studentRows[$studentKey]['last_project_activity'], $updated);
            if ($status !== 'published') continue;
            $pct = max(0.0, min(100.0, ((float)$result['points'] / $maxPoints) * 100));
            $grade = (int)$result['grade'];
            $studentRows[$studentKey]['project_pct_sum'] += $pct; $studentRows[$studentKey]['project_pct_count']++;
            $studentRows[$studentKey]['grade_sum'] += $grade; $studentRows[$studentKey]['grade_count']++;
            if ($grade === 1) $studentRows[$studentKey]['excellent']++;
            if ($grade >= 4) $studentRows[$studentKey]['needs_work']++;
        }

        if ($status === 'published' && isset($projectStats[$projectId])) {
            $pct = max(0.0, min(100.0, ($basePoints / $maxPoints) * 100));
            $projectStats[$projectId]['pct_sum'] += $pct; $projectStats[$projectId]['pct_count']++;
            $projectStats[$projectId]['grade_counts'][$baseGrade] = ($projectStats[$projectId]['grade_counts'][$baseGrade] ?? 0) + 1;
        }
    }

    $gradeCounts = [1=>0,2=>0,3=>0,4=>0,5=>0];
    $officialPct = []; $officialGrades = []; $masteryValues = [];
    foreach ($studentRows as &$row) {
        $row['avg_project_pct'] = $row['project_pct_count'] > 0 ? $row['project_pct_sum'] / $row['project_pct_count'] : null;
        $row['avg_grade'] = $row['grade_count'] > 0 ? $row['grade_sum'] / $row['grade_count'] : null;
        $row['last_activity'] = max((int)$row['last_project_activity'], (int)($row['activity']['last_activity'] ?? 0));
        if ($row['avg_project_pct'] !== null) $officialPct[] = (float)$row['avg_project_pct'];
        if ($row['avg_grade'] !== null) $officialGrades[] = (float)$row['avg_grade'];
        if ($row['mastery'] !== null) $masteryValues[] = (float)$row['mastery'];
        if ($row['avg_grade'] !== null) $gradeCounts[max(1,min(5,(int)round((float)$row['avg_grade'])))]++;
        $hasEvidence = (int)$row['published'] > 0 || (int)($row['activity']['tests'] ?? 0) > 0 || (int)($row['activity']['labs'] ?? 0) > 0 || (int)($row['activity']['prestige_attempts'] ?? 0) > 0;
        $row['attention'] = $row['returned'] > 0 || $row['needs_work'] > 0 || ($hasEvidence && $row['mastery'] !== null && (float)$row['mastery'] < 40);
        $row['has_evidence'] = $hasEvidence;
        $flag = teacher_student_flag($classId, (string)$row['key']);
        $row['marker'] = (string)($flag['marker'] ?? 'none');
        $row['status_key'] = teacher_student_result_status($row);
        $row['priority'] = teacher_student_priority($row, $row['marker']);
        $row['task_summary'] = teacher_student_task_summary($classId, (string)$row['key']);
        $row['active_tasks'] = (int)($row['task_summary']['active'] ?? 0);
        $row['done_tasks'] = (int)($row['task_summary']['done'] ?? 0);
        $row['inactive_days'] = $row['last_activity'] > 0 ? max(0,(int)floor((time()-(int)$row['last_activity'])/86400)) : null;
        $row['score_drop'] = teacher_student_score_drop_value($classId,(string)$row['key']);
        unset($row['project_pct_sum'],$row['project_pct_count'],$row['grade_sum'],$row['grade_count'],$row['last_project_activity']);
    }
    unset($row);

    foreach ($projectStats as &$project) {
        $project['avg_pct'] = $project['pct_count'] > 0 ? $project['pct_sum'] / $project['pct_count'] : null;
        unset($project['pct_sum'],$project['pct_count']);
    }
    unset($project);

    uasort($studentRows, static function(array $a,array $b): int {
        $priorityRank=['high'=>3,'medium'=>2,'normal'=>1];
        $priority=($priorityRank[(string)($b['priority']??'normal')]??1)<=>($priorityRank[(string)($a['priority']??'normal')]??1);
        if($priority!==0)return $priority;
        if ((bool)$a['attention'] !== (bool)$b['attention']) return $a['attention'] ? -1 : 1;
        return strnatcasecmp((string)$a['label'], (string)$b['label']);
    });

    return $GLOBALS['educanet_runtime_indexes'][$cacheKey] = [
        'students'=>array_values($studentRows),
        'projects'=>array_values($projectStats),
        'student_count'=>count($students),
        'avg_project_pct'=>$officialPct ? array_sum($officialPct) / count($officialPct) : null,
        'avg_grade'=>$officialGrades ? array_sum($officialGrades) / count($officialGrades) : null,
        'avg_mastery'=>$masteryValues ? array_sum($masteryValues) / count($masteryValues) : null,
        'grade_counts'=>$gradeCounts,
        'published_records'=>array_sum(array_column($projectStats,'published')),
        'returned_records'=>array_sum(array_column($projectStats,'returned')),
        'draft_records'=>array_sum(array_column($projectStats,'drafts')),
        'students_with_results'=>count($officialPct),
        'attention_count'=>count(array_filter($studentRows,static fn(array $r): bool => (bool)$r['attention'])),
    ];
}

function teacher_csv_safe(string $value): string
{
    $trimmed=ltrim($value);
    return $trimmed!=='' && in_array($trimmed[0],['=','+','-','@'],true) ? "'".$value : $value;
}

function teacher_class_filter_state(string $classId='',string $tab=''): array
{
    $classId=$classId!==''?$classId:(string)($_GET['class']??'class_2a');$tab=$tab!==''?$tab:(string)($_GET['tab']??'class_results');
    $presetId=trim((string)($_GET['preset']??''));$preset=$presetId!==''?teacher_saved_filter_find_visible($presetId):null;if($preset&&$classId!==''&&(string)($preset['class_id']??'')!==$classId){$preset=null;$presetId='';}
    $hasExplicit=$presetId!==''||isset($_GET['status'])||isset($_GET['priority'])||isset($_GET['task_status'])||isset($_GET['task_due']);
    if(!$hasExplicit&&in_array($tab,['class_overview','class_results'],true)){$preset=teacher_saved_filter_default($tab,$classId);if($preset)$presetId=(string)$preset['id'];}
    return [
        'status'=>teacher_saved_filter_status((string)($preset['status']??($_GET['status']??'all'))),
        'priority'=>teacher_saved_filter_priority((string)($preset['priority']??($_GET['priority']??'all'))),
        'task_status'=>teacher_saved_filter_task_status((string)($preset['task_status']??($_GET['task_status']??'all'))),
        'task_due'=>teacher_saved_filter_task_due((string)($preset['task_due']??($_GET['task_due']??'all'))),
        'preset_id'=>$presetId,'rules'=>(array)($preset['rules']??[]),'rule_mode'=>(string)($preset['rule_mode']??'all'),'preset'=>$preset,
    ];
}

function teacher_class_filter_students(array $students,string $status,string $priority,string $taskStatus='all',string $taskDue='all',array $rules=[],string $ruleMode='all'): array
{
    $filter=['status'=>$status,'priority'=>$priority,'task_status'=>$taskStatus,'task_due'=>$taskDue,'rules'=>$rules,'rule_mode'=>$ruleMode,'scope'=>'personal'];
    return array_values(array_filter($students,static fn(array $row):bool=>teacher_saved_filter_matches_student($filter,$row)));
}

function teacher_saved_filter_link(string $tab,array $filter): string
{
    $params=['tab'=>$tab,'class'=>(string)($filter['class_id']??'class_2a'),'preset'=>(string)($filter['id']??'')];
    $status=teacher_saved_filter_status((string)($filter['status']??'all'));if($status!=='all')$params['status']=$status;
    $priority=teacher_saved_filter_priority((string)($filter['priority']??'all'));if($priority!=='all')$params['priority']=$priority;
    $taskStatus=teacher_saved_filter_task_status((string)($filter['task_status']??'all'));if($taskStatus!=='all')$params['task_status']=$taskStatus;
    $taskDue=teacher_saved_filter_task_due((string)($filter['task_due']??'all'));if($taskDue!=='all')$params['task_due']=$taskDue;
    return 'teacher.php?'.http_build_query($params);
}

function teacher_render_saved_filter_items(array $filters,string $tab,string $classId,string $status,string $priority,string $taskStatus,string $taskDue,string $scope): void
{
    if(!$filters){ ?><div class="saved-filter-empty"><?= $scope==='team'?'Tým zatím nemá žádný sdílený preset.':'Zatím nemáš žádný osobní preset.' ?></div><?php return; }
    ?><div class="saved-filter-list"><?php foreach($filters as $filter):
        $active=teacher_saved_filter_match($filter,$classId,$status,$priority,$taskStatus,$taskDue);
        $isOwner=!empty($filter['is_owner']);$filterScope=teacher_saved_filter_scope((string)($filter['scope']??'personal'));
        $ownerLabel=trim((string)($filter['owner_label']??''));
        ?><article class="saved-filter-item <?=$active?'active':''?> scope-<?=e($filterScope)?>" data-saved-filter-item data-filter-id="<?=e((string)$filter['id'])?>" data-filter-smart-count="<?=count((array)($filter['rules']??[]))?>" data-filter-class="<?=e((string)$filter['class_id'])?>" data-filter-status="<?=e((string)$filter['status'])?>" data-filter-priority="<?=e((string)$filter['priority'])?>" data-filter-task-status="<?=e((string)($filter['task_status']??'all'))?>" data-filter-task-due="<?=e((string)($filter['task_due']??'all'))?>"><a class="saved-filter-apply" href="<?=e(teacher_saved_filter_link($tab,$filter))?>"><span class="saved-filter-meta"><em class="saved-filter-scope scope-<?=e($filterScope)?>"><?=e(teacher_saved_filter_scope_label($filterScope))?></em><?php if($filterScope==='team'): ?><i><?=e($ownerLabel!==''?$ownerLabel:($isOwner?teacher_display_name():'Člen týmu'))?></i><?php endif; ?></span><strong><?=e((string)$filter['name'])?></strong><small><?=e(teacher_class_label((string)$filter['class_id']))?> · <?=e(teacher_saved_filter_status_label((string)$filter['status']))?> · <?=e(teacher_saved_filter_priority_label((string)$filter['priority']))?><br><?=e(teacher_saved_filter_task_status_label((string)($filter['task_status']??'all')))?> · <?=e(teacher_saved_filter_task_due_label((string)($filter['task_due']??'all')))?><?php if(!empty($filter['rules'])): ?> · <?=count((array)$filter['rules'])?> smart<?php endif; ?></small></a><div class="saved-filter-context"><a class="<?=$tab==='class_overview'?'current':''?>" href="<?=e(teacher_saved_filter_link('class_overview',$filter))?>" title="Použít v Přehledu třídy">Přehled</a><a class="<?=$tab==='class_results'?'current':''?>" href="<?=e(teacher_saved_filter_link('class_results',$filter))?>" title="Použít ve Výsledcích třídy">Výsledky</a><?php if($isOwner): ?><form method="post" onsubmit="return confirm('Smazat vlastní uložený filtr?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_saved_filter_delete"><input type="hidden" name="filter_id" value="<?=e((string)$filter['id'])?>"><input type="hidden" name="return_tab" value="<?=e($tab)?>"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="filter_status" value="<?=e($status)?>"><input type="hidden" name="filter_priority" value="<?=e($priority)?>"><input type="hidden" name="filter_task_status" value="<?=e($taskStatus)?>"><input type="hidden" name="filter_task_due" value="<?=e($taskDue)?>"><button type="submit" aria-label="Smazat vlastní filtr <?=e((string)$filter['name'])?>">×</button></form><?php else: ?><span class="saved-filter-owned-by" title="Týmový preset může odstranit pouze jeho autor">jen autor</span><?php endif; ?></div></article><?php endforeach; ?></div><?php
}

function teacher_render_saved_filters(string $tab,string $classId,string $status,string $priority,string $taskStatus='all',string $taskDue='all'): void
{
    $filters=teacher_saved_filters_for_current_teacher();
    $personal=array_values(array_filter($filters,static fn(array $row):bool=>teacher_saved_filter_scope((string)($row['scope']??'personal'))==='personal'));
    $team=array_values(array_filter($filters,static fn(array $row):bool=>teacher_saved_filter_scope((string)($row['scope']??'personal'))==='team'));
    $status=teacher_saved_filter_status($status);$priority=teacher_saved_filter_priority($priority);
    $taskStatus=teacher_saved_filter_task_status($taskStatus);$taskDue=teacher_saved_filter_task_due($taskDue);
    $isDefault=$status==='all'&&$priority==='all'&&$taskStatus==='all'&&$taskDue==='all';
    ?>
    <section class="teacher-saved-filters" data-saved-filter-panel data-current-class="<?=e($classId)?>">
      <div class="saved-filter-head"><div><span>Uložené filtry</span><h2>Osobní a týmové pohledy</h2><p>Osobní preset vidíš jen ty. Týmový preset sdílíš s <?=e(teacher_team_label())?>; každý učitel může mazat pouze filtry, které sám vytvořil.</p></div><div class="saved-filter-head-actions"><?php if(!$isDefault): ?><a class="text-link" href="?tab=<?=e($tab)?>&class=<?=e($classId)?>">Vymazat aktivní filtr</a><?php endif; ?><details class="saved-filter-create"><summary>＋ Uložit aktuální</summary><form method="post" data-save-filter-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_saved_filter_save"><input type="hidden" name="return_tab" value="<?=e($tab)?>"><input type="hidden" name="class_id" value="<?=e($classId)?>" data-save-filter-class><input type="hidden" name="filter_status" value="<?=e($status)?>" data-save-filter-status><input type="hidden" name="filter_priority" value="<?=e($priority)?>" data-save-filter-priority><input type="hidden" name="filter_task_status" value="<?=e($taskStatus)?>" data-save-filter-task-status><input type="hidden" name="filter_task_due" value="<?=e($taskDue)?>" data-save-filter-task-due><label>Název filtru<input name="filter_name" required maxlength="80" placeholder="např. Po termínu · vysoká"></label><label>Viditelnost<select name="filter_scope"><option value="personal">Osobní · pouze pro mě</option><option value="team">Týmový · <?=e(teacher_team_label())?></option></select></label><div class="saved-filter-preview" data-save-filter-preview><?=e(teacher_class_label($classId))?> · <?=e(teacher_saved_filter_status_label($status))?> · <?=e(teacher_saved_filter_priority_label($priority))?> · <?=e(teacher_saved_filter_task_status_label($taskStatus))?> · <?=e(teacher_saved_filter_task_due_label($taskDue))?></div><button class="btn primary small" type="submit">Uložit filtr</button></form></details></div></div>
      <div class="saved-filter-groups">
        <section class="saved-filter-group"><header><div><span class="saved-filter-group-icon">●</span><strong>Osobní</strong><small><?=count($personal)?> presetů · pouze pro <?=e(teacher_display_name())?></small></div></header><?php teacher_render_saved_filter_items($personal,$tab,$classId,$status,$priority,$taskStatus,$taskDue,'personal'); ?></section>
        <section class="saved-filter-group team"><header><div><span class="saved-filter-group-icon">◆</span><strong>Týmové · <?=e(teacher_team_label())?></strong><small><?=count($team)?> sdílených presetů · cizí filtry jsou pouze pro použití</small></div></header><?php teacher_render_saved_filter_items($team,$tab,$classId,$status,$priority,$taskStatus,$taskDue,'team'); ?></section>
      </div>
    </section>
    <?php
}

function teacher_class_export_csv(string $classId,array $studentKeys): never
{
    $selected=array_flip($studentKeys);$snapshot=teacher_class_results_snapshot($classId);$rows=[];
    foreach((array)($snapshot['students']??[]) as $row)if(is_array($row)&&isset($selected[(string)($row['key']??'')]))$rows[]=$row;
    if(!$rows)throw new RuntimeException('Vyberte alespoň jednoho studenta pro export.');
    $filename='educanet-'.strtolower(str_replace(['.',' '],'-',teacher_class_label($classId))).'-vysledky-'.date('Y-m-d').'.csv';
    header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$filename.'"');header('X-Content-Type-Options: nosniff');
    $out=fopen('php://output','wb');if($out===false)throw new RuntimeException('Export nelze vytvořit.');fwrite($out,"\xEF\xBB\xBF");
    fputcsv($out,['Třída','Student','Místo / e-mail','Publikováno','Koncepty','Vráceno','Průměr projektů %','Průměrná známka','Testy','Nejlepší test %','Laboratoře','Prestige úspěšně','Prestige pokusy','Mastery %','Priorita','Stav','Označení','Aktivní úkoly','Dokončené úkoly','Po termínu','Nejbližší aktivní termín','Poslední aktivita'],';');
    foreach($rows as $row){$activity=(array)($row['activity']??[]);$last=(int)($row['last_activity']??0);$csv=[teacher_class_label($classId),(string)($row['label']??''),(string)(($row['seat_label']??'')?:($row['email']??'')),(int)($row['published']??0),(int)($row['drafts']??0),(int)($row['returned']??0),$row['avg_project_pct']===null?'':round((float)$row['avg_project_pct'],1),$row['avg_grade']===null?'':round((float)$row['avg_grade'],2),(int)($activity['tests']??0),isset($activity['best_test'])&&$activity['best_test']!==null?round((float)$activity['best_test'],1):'',(int)($activity['labs']??0),(int)($activity['prestige_passed']??0),(int)($activity['prestige_attempts']??0),$row['mastery']===null?'':round((float)$row['mastery'],1),teacher_student_priority_label((string)($row['priority']??'normal')),teacher_student_status_label((string)($row['status_key']??'no_result')),teacher_student_marker_label((string)($row['marker']??'none')),(int)($row['active_tasks']??0),(int)($row['done_tasks']??0),(int)(($row['task_summary']['overdue']??0)),(string)(($row['task_summary']['nearest_due']??'')),$last>0?date('Y-m-d H:i',$last):''];$csv=array_map(static fn($value)=>is_string($value)?teacher_csv_safe($value):$value,$csv);fputcsv($out,$csv,';');}
    fclose($out);exit;
}

function teacher_render_class_overview(string $classId): void
{
    $snapshot = teacher_overview_class_snapshot($classId);
    $results = teacher_class_results_snapshot($classId);
    $students = (array)$results['students'];
    $filterState=teacher_class_filter_state($classId,'class_overview');
    $filterStatus=(string)$filterState['status'];$filterPriority=(string)$filterState['priority'];$filterTaskStatus=(string)$filterState['task_status'];$filterTaskDue=(string)$filterState['task_due'];
    $filterRules=(array)($filterState['rules']??[]);$filterRuleMode=(string)($filterState['rule_mode']??'all');$filterPresetId=(string)($filterState['preset_id']??'');
    $filterActive=$filterStatus!=='all'||$filterPriority!=='all'||$filterTaskStatus!=='all'||$filterTaskDue!=='all'||!empty($filterRules);
    $attention = $filterActive
        ? teacher_class_filter_students($students,$filterStatus,$filterPriority,$filterTaskStatus,$filterTaskDue,$filterRules,$filterRuleMode)
        : array_values(array_filter($students, static fn(array $row): bool => (bool)($row['attention'] ?? false)));
    $projects = (array)$results['projects'];
    ?>
    <section class="teacher-hero teacher-class-hero"><div><div class="eyebrow">Přehled třídy · <?=e((string)$snapshot['class_label'])?></div><h1><?=e((string)$snapshot['subject'])?></h1><p><?=e((string)$snapshot['focus'])?></p></div><div class="teacher-hero-actions"><a class="btn secondary" href="?tab=class_results&class=<?=e($classId)?>&status=<?=e($filterStatus)?>&priority=<?=e($filterPriority)?>&task_status=<?=e($filterTaskStatus)?>&task_due=<?=e($filterTaskDue)?>">Výsledky třídy</a><a class="btn primary" href="?tab=teach&class=<?=e($classId)?>">Spustit hodinu →</a></div></section>

    <?php teacher_render_saved_filters('class_overview',$classId,$filterStatus,$filterPriority,$filterTaskStatus,$filterTaskDue); ?>

    <section class="class-overview-kpis">
      <article><span>Studenti</span><strong><?= (int)$results['student_count'] ?></strong><small><?= (int)$results['students_with_results'] ?> s publikovaným výsledkem</small></article>
      <article><span>Průměr projektů</span><strong><?=teacher_class_pct($results['avg_project_pct'],0)?></strong><small>jen publikované výsledky</small></article>
      <article><span>Průměrná známka</span><strong><?=teacher_class_grade($results['avg_grade'])?></strong><small>publikované projekty</small></article>
      <article><span>Skill Mastery</span><strong><?=teacher_class_pct($results['avg_mastery'],0)?></strong><small><?= (int)$snapshot['pending_validations'] ?> validací čeká</small></article>
      <article class="<?=((int)$results['attention_count']>0?'needs-attention':'')?>"><span>K pozornosti</span><strong><?= (int)$results['attention_count'] ?></strong><small>výsledky, vrácení nebo mastery</small></article>
    </section>

    <div class="class-overview-layout">
      <section class="teacher-panel class-action-panel"><div class="teacher-panel-head"><div><span>Rychlá navigace</span><h2>Co chcete se třídou udělat?</h2></div></div><div class="class-quick-actions">
        <a href="?tab=curriculum&class=<?=e($classId)?>"><i>01</i><strong>Připravit výuku</strong><small><?= (int)$snapshot['prepared'] ?> / <?= (int)$snapshot['lessons_total'] ?> lekcí připraveno</small></a>
        <a href="?tab=class_results&class=<?=e($classId)?>"><i>02</i><strong>Projít výsledky</strong><small><?= (int)$results['published_records'] ?> publikovaných hodnocení</small></a>
        <a href="<?=e(teacher_class_tab_url('grade',$classId))?>"><i>03</i><strong>Hodnotit projekty</strong><small><?= (int)$results['draft_records'] ?> konceptů</small></a>
        <a href="?tab=skills&class=<?=e($classId)?>"><i>04</i><strong>Skills & validace</strong><small><?= (int)$snapshot['pending_validations'] ?> čeká na kontrolu</small></a>
        <a href="<?=e(teacher_class_tab_url('groups',$classId))?>"><i>05</i><strong>Týmy</strong><small><?= (int)$snapshot['groups_total'] ?> aktivních týmů</small></a>
        <a href="<?=e(teacher_class_tab_url('workspace',$classId))?>"><i>06</i><strong>Projektový workspace</strong><small><?= (int)$snapshot['workspace_high'] + (int)$snapshot['workspace_medium'] ?> signálů</small></a>
      </div></section>

      <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Stav výuky</span><h2>Příprava a obsah</h2></div><a class="text-link" href="?tab=curriculum&class=<?=e($classId)?>">Detail →</a></div><div class="class-progress-list">
        <?php $items=[['Lekce připravené',(int)$snapshot['prepared'],(int)$snapshot['lessons_total']],['Obsah kompletní',(int)$snapshot['content_ready'],(int)$snapshot['lessons_total']],['Checklist',(int)$snapshot['checklist_done'],(int)$snapshot['checklist_total']]]; foreach($items as [$label,$value,$max]): $pct=$max>0?(int)round($value/$max*100):0; ?>
        <div><div><span><?=e($label)?></span><b><?=$value?> / <?=$max?></b></div><progress max="100" value="<?=$pct?>"></progress><small><?=$pct?> %</small></div>
        <?php endforeach; ?>
      </div></section>
    </div>

    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Studenti</span><h2><?=$filterActive?'Studenti podle aktivního filtru':'Potřebují pozornost'?></h2><?php if($filterActive): ?><p class="teacher-muted"><?=count($attention)?> studentů · <?=e(teacher_saved_filter_status_label($filterStatus))?> · <?=e(teacher_saved_filter_priority_label($filterPriority))?> · <?=e(teacher_saved_filter_task_status_label($filterTaskStatus))?> · <?=e(teacher_saved_filter_task_due_label($filterTaskDue))?></p><?php endif; ?></div><a class="text-link" href="?tab=class_results&class=<?=e($classId)?>&status=<?=e($filterStatus)?>&priority=<?=e($filterPriority)?>&task_status=<?=e($filterTaskStatus)?>&task_due=<?=e($filterTaskDue)?>">Otevřít ve Výsledcích →</a></div>
      <?php if(!$attention): ?><div class="teacher-empty"><?=$filterActive?'Aktivnímu filtru neodpovídá žádný student.':'Z dostupných výsledků aktuálně nevychází žádný prioritní signál.'?></div><?php else: ?><div class="class-attention-grid"><?php foreach(array_slice($attention,0,12) as $row): ?><article><div class="teacher-avatar"><?=e(strtoupper(u_substr((string)$row['label'],0,1)))?></div><div><strong><?=e((string)$row['label'])?></strong><span><b class="priority-dot priority-<?=e((string)($row['priority']??'normal'))?>"></b><?=e(teacher_student_priority_label((string)($row['priority']??'normal')))?> · <?=e(teacher_student_status_label((string)($row['status_key']??teacher_student_result_status($row))))?> · <?php if((string)($row['marker']??'none')!=='none'): ?><?=e(teacher_student_marker_label((string)$row['marker']))?> · <?php endif; ?>Mastery <?=teacher_class_pct($row['mastery'],0)?></span></div><a href="?tab=skills&class=<?=e($classId)?>&student=<?=e(skill_student_key_for_label($classId,(string)$row['label']))?>">Detail →</a></article><?php endforeach; ?></div><?php if(count($attention)>12): ?><div class="saved-filter-more">+ <?=count($attention)-12?> dalších studentů · <a href="?tab=class_results&class=<?=e($classId)?>&status=<?=e($filterStatus)?>&priority=<?=e($filterPriority)?>&task_status=<?=e($filterTaskStatus)?>&task_due=<?=e($filterTaskDue)?>">zobrazit všechny</a></div><?php endif; ?><?php endif; ?>
    </section>

    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Projekty</span><h2>Stav hodnocení</h2></div><a class="text-link" href="?tab=grade&class=<?=e($classId)?>">Hodnotit →</a></div><div class="class-project-status-grid"><?php foreach($projects as $project): ?><article><div><span><?=e((string)$project['type']==='group'?'Týmový projekt':'Individuální projekt')?></span><strong><?=e((string)$project['title'])?></strong></div><div class="class-project-score"><b><?=teacher_class_pct($project['avg_pct'],0)?></b><small>průměr publikovaných</small></div><footer><span><?= (int)$project['published'] ?> publikováno</span><span><?= (int)$project['drafts'] ?> konceptů</span><span><?= (int)$project['returned'] ?> vráceno</span></footer><a href="?tab=grade&class=<?=e($classId)?>&project=<?=e((string)$project['id'])?>">Otevřít hodnocení →</a></article><?php endforeach; ?></div></section>
    <?php
}

function teacher_render_class_results(string $classId): void
{
    $results = teacher_class_results_snapshot($classId);
    $students = (array)$results['students'];
    $projects = (array)$results['projects'];
    $gradeCounts = (array)$results['grade_counts'];
    $maxGradeCount = max(1, ...array_values($gradeCounts ?: [1]));
    $filterState=teacher_class_filter_state($classId,'class_results');$filterStatus=(string)$filterState['status'];$filterPriority=(string)$filterState['priority'];$filterTaskStatus=(string)$filterState['task_status'];$filterTaskDue=(string)$filterState['task_due'];$filterRules=(array)($filterState['rules']??[]);$filterRuleMode=(string)($filterState['rule_mode']??'all');$filterPresetId=(string)($filterState['preset_id']??'');
    ?>
    <section class="teacher-page-head class-results-head"><div><div class="eyebrow">Výsledky třídy · <?=e(teacher_class_label($classId))?></div><h1>Výsledky, progres a signály studentů</h1><p>Jeden přehled spojuje publikovaná projektová hodnocení, testy, dokončené laboratoře, prestižní zkoušky a Skill Mastery. Koncepty nejsou zahrnuté do třídních průměrů.</p></div><div class="teacher-hero-actions"><a class="btn secondary" href="?tab=class_overview&class=<?=e($classId)?>">Přehled třídy</a><button class="btn primary" type="button" onclick="window.print()">Tisk / PDF</button></div></section>

    <section class="class-results-kpis">
      <article><span>Studenti s výsledkem</span><strong><?= (int)$results['students_with_results'] ?> / <?= (int)$results['student_count'] ?></strong><small>alespoň 1 publikovaný projekt</small></article>
      <article><span>Průměr projektů</span><strong><?=teacher_class_pct($results['avg_project_pct'],0)?></strong><small>publikované výsledky</small></article>
      <article><span>Průměrná známka</span><strong><?=teacher_class_grade($results['avg_grade'])?></strong><small>nižší je lepší</small></article>
      <article><span>Skill Mastery</span><strong><?=teacher_class_pct($results['avg_mastery'],0)?></strong><small>průměr dostupných dat</small></article>
      <article><span>Vráceno</span><strong><?= (int)$results['returned_records'] ?></strong><small>čeká na dopracování</small></article>
      <article><span>Koncepty</span><strong><?= (int)$results['draft_records'] ?></strong><small>zatím nepublikováno</small></article>
    </section>

    <div class="class-results-top-grid">
      <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Distribuce</span><h2>Průměrná projektová známka studentů</h2></div></div><div class="grade-distribution"><?php for($grade=1;$grade<=5;$grade++): $count=(int)($gradeCounts[$grade]??0); $width=(int)round($count/$maxGradeCount*100); ?><div><span><?=$grade?></span><progress max="100" value="<?=$width?>"></progress><b><?=$count?></b></div><?php endfor; ?></div></section>
      <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Projekty</span><h2>Výkon po projektech</h2></div></div><div class="project-result-mini-list"><?php foreach($projects as $project): ?><a href="?tab=grade&class=<?=e($classId)?>&project=<?=e((string)$project['id'])?>"><div><strong><?=e((string)$project['title'])?></strong><span><?= (int)$project['published'] ?> publikováno · <?= (int)$project['returned'] ?> vráceno</span></div><b><?=teacher_class_pct($project['avg_pct'],0)?></b><i>→</i></a><?php endforeach; ?></div></section>
    </div>

    <?php teacher_render_saved_filters('class_results',$classId,$filterStatus,$filterPriority,$filterTaskStatus,$filterTaskDue); ?>

    <section class="teacher-panel class-results-table-panel">
      <form method="post" class="class-results-bulk-form" data-bulk-form>
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <input type="hidden" name="class_id" value="<?=e($classId)?>">
        <input type="hidden" name="return_tab" value="class_results">
        <div class="teacher-panel-head class-results-panel-head"><div><span>Studenti</span><h2>Kompletní tabulka výsledků</h2><p>Filtruj podle třídy, výsledku, priority, stavu úkolu a termínu. Výběr studentů zůstává lokální, dokud nespustíš hromadnou akci.</p></div></div>

        <div class="class-results-filterbar" data-class-filterbar data-active-preset="<?=e($filterPresetId)?>" data-smart-mode="<?=e($filterRuleMode)?>" data-smart-rules="<?=e(json_encode($filterRules,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?:'[]')?>">
          <label><span>Třída</span><select data-admin-class-jump aria-label="Filtrovat podle třídy"><?php foreach(array_keys(function_exists('teacher59_filter_class_map')?teacher59_filter_class_map(project_catalog()):project_catalog()) as $cid): /* v59: jen třídy v rozsahu */ ?><option value="<?=e($cid)?>" <?=$cid===$classId?'selected':''?>><?=e(teacher_class_label($cid))?></option><?php endforeach; ?></select></label>
          <label><span>Stav</span><select data-class-result-status><option value="all">Všechny stavy</option><option value="attention">Prověřit</option><option value="returned">Vráceno</option><option value="ok">V pořádku</option><option value="no_result">Bez výsledku</option></select></label>
          <label><span>Priorita</span><select data-class-result-priority><option value="all">Všechny priority</option><option value="high">Vysoká</option><option value="medium">Střední</option><option value="normal">Běžná</option></select></label><label><span>Stav úkolu</span><select data-class-task-status><option value="all">Všechny úkoly</option><option value="active">Aktivní úkol</option><option value="done">Dokončený úkol</option><option value="no_task">Bez úkolu</option></select></label><label><span>Termín úkolu</span><select data-class-task-due><option value="all">Všechny termíny</option><option value="overdue">Po termínu</option><option value="today">Dnes</option><option value="next3">Do 3 dnů</option><option value="next7">Do 7 dnů</option><option value="no_due">Bez termínu</option></select></label>
          <label class="class-results-search"><span>Hledat</span><input id="class-result-search" type="search" placeholder="Jméno, místo, e-mail…" data-class-result-search></label>
          <label><span>Řazení</span><select data-class-result-sort><option value="priority">Priorita ↓</option><option value="attention">Nejdřív k pozornosti</option><option value="name">Jméno A–Z</option><option value="project_desc">Projektové % ↓</option><option value="grade_asc">Známka ↑</option><option value="mastery_desc">Mastery ↓</option><option value="activity_desc">Poslední aktivita</option></select></label>
          <button class="text-button class-filter-clear" type="button" data-class-filter-clear>Vymazat filtry</button>
        </div>

        <div class="class-bulk-actionbar" data-bulk-actionbar>
          <label class="bulk-select-visible"><input type="checkbox" data-select-visible> <span>Vybrat viditelné</span></label>
          <div class="bulk-selection-summary"><strong data-bulk-count>0</strong><span>vybráno</span><small><b data-visible-count><?=count($students)?></b> z <?=count($students)?> viditelných</small></div>
          <div class="bulk-actions">
            <details class="bulk-action-menu"><summary>Označit <i>⌄</i></summary><div class="bulk-action-popover"><label>Označení<select name="marker"><option value="urgent">Urgentní</option><option value="watch">Sledovat</option><option value="resolved">Vyřešeno</option><option value="none">Zrušit označení</option></select></label><p>Označení je interní pro učitele a mění prioritu v tomto přehledu.</p><button class="btn secondary small" type="submit" name="action" value="teacher_bulk_mark" data-requires-selection disabled>Použít na vybrané</button></div></details>
            <button class="btn secondary small" type="submit" name="action" value="teacher_bulk_export" data-requires-selection disabled>Export CSV</button>
            <details class="bulk-action-menu bulk-task-menu"><summary>Přiřadit úkol <i>＋</i></summary><div class="bulk-action-popover task-popover"><label>Název úkolu<input name="task_title" maxlength="160" placeholder="Např. Doplnit laboratorní protokol"></label><label class="wide">Instrukce<textarea name="task_instructions" rows="3" maxlength="1800" placeholder="Co má student udělat a co odevzdat"></textarea></label><label>Priorita<select name="task_priority"><option value="normal">Běžná</option><option value="high">Vysoká</option><option value="low">Nízká</option></select></label><label>Termín<input type="date" name="task_due_at"></label><p class="wide">Úkol se zobrazí vybraným studentům přímo v jejich dashboardu.</p><button class="btn primary small wide" type="submit" name="action" value="teacher_bulk_assign_task" data-requires-selection disabled>Přiřadit vybraným</button></div></details>
            <details class="bulk-action-menu"><summary>Úkoly 2.0 <i>⌄</i></summary><div class="bulk-action-popover ops-bulk-grid"><label>Nový termín<input type="date" name="bulk_task_due_at"></label><button class="btn secondary small" type="submit" name="action" value="teacher_bulk_task_due" data-requires-selection disabled>Změnit termín aktivních</button><label>Nová priorita<select name="bulk_task_priority"><option value="high">Vysoká</option><option value="normal">Běžná</option><option value="low">Nízká</option></select></label><button class="btn secondary small" type="submit" name="action" value="teacher_bulk_task_priority" data-requires-selection disabled>Změnit prioritu</button><button class="btn secondary small wide" type="submit" name="action" value="teacher_bulk_remind" data-requires-selection disabled>Připomenout aktivní úkoly</button></div></details>
            <details class="bulk-action-menu"><summary>Podpora <i>⌄</i></summary><div class="bulk-action-popover task-popover"><label class="wide">Týmová poznámka<textarea name="note_text" rows="2" maxlength="1800" placeholder="Interní follow-up pro vybrané studenty"></textarea></label><label>Typ<select name="note_tag"><option value="followup">Follow-up</option><option value="support">Podpora</option><option value="general">Obecná</option></select></label><button class="btn secondary small" type="submit" name="action" value="teacher_bulk_note" data-requires-selection disabled>Přidat poznámku</button><hr class="wide"><label>Materiál<select name="resource_type"><option value="material">Materiál</option><option value="lesson">Lekce</option><option value="lab">Laboratoř</option><option value="url">URL</option></select></label><label>Odkaz / ID<input name="resource_ref" maxlength="500" placeholder="URL nebo označení"></label><label class="wide">Název<input name="resource_title" maxlength="160" placeholder="Doporučený materiál"></label><button class="btn secondary small wide" type="submit" name="action" value="teacher_bulk_resource" data-requires-selection disabled>Přiřadit materiál</button><hr class="wide"><label class="wide">Intervence<input name="intervention_title" maxlength="160" placeholder="Např. Recovery · subnetting"></label><label class="wide">Pozorovaný problém<textarea name="intervention_problem" rows="2" maxlength="1600"></textarea></label><label>Mikroúkol<textarea name="intervention_micro_task" rows="2" maxlength="1200"></textarea></label><label>Checkpoint<textarea name="intervention_checkpoint" rows="2" maxlength="1200"></textarea></label><label class="wide">Definice úspěchu<input name="intervention_success" maxlength="900"></label><button class="btn primary small wide" type="submit" name="action" value="teacher_bulk_intervention" data-requires-selection disabled>Vytvořit intervenci</button></div></details>
            <button class="text-button" type="button" data-clear-selection disabled>Zrušit výběr</button>
          </div>
        </div>

        <div class="class-results-table-wrap"><table class="class-results-table class-results-table-v45-4" data-class-results-table><thead><tr><th class="result-check-col"><span class="sr-only">Vybrat</span></th><th>Student</th><th>Projekty</th><th>Průměr</th><th>Známka</th><th>Testy</th><th>Laboratoře</th><th>Prestige</th><th>Mastery</th><th>Priorita</th><th>Stav</th><th>Úkoly</th><th></th></tr></thead><tbody>
        <?php foreach($students as $row): $activity=(array)($row['activity']??[]); $last=(int)($row['last_activity']??0); $status=(string)($row['status_key']??teacher_student_result_status($row)); $priority=(string)($row['priority']??'normal'); $marker=(string)($row['marker']??'none'); $taskSummary=(array)($row['task_summary']??[]); ?>
          <tr data-result-row data-name="<?=e(normalized_person_name((string)$row['label']))?>" data-search="<?=e(normalized_person_name((string)$row['label'].' '.(string)($row['seat_label']??'').' '.(string)($row['email']??'')))?>" data-attention="<?=!empty($row['attention'])?'1':'0'?>" data-status="<?=e($status)?>" data-priority="<?=e($priority)?>" data-marker="<?=e($marker)?>" data-project="<?=e($row['avg_project_pct']===null?'-1':(string)$row['avg_project_pct'])?>" data-grade="<?=e($row['avg_grade']===null?'99':(string)$row['avg_grade'])?>" data-mastery="<?=e($row['mastery']===null?'-1':(string)$row['mastery'])?>" data-activity="<?=$last?>" data-task-active="<?= (int)($taskSummary['active']??0) ?>" data-task-done="<?= (int)($taskSummary['done']??0) ?>" data-task-total="<?= (int)($taskSummary['total']??0) ?>" data-task-overdue="<?= (int)($taskSummary['overdue']??0) ?>" data-task-today="<?= (int)($taskSummary['due_today']??0) ?>" data-task-next3="<?= (int)($taskSummary['due_3']??0) ?>" data-task-next7="<?= (int)($taskSummary['due_7']??0) ?>" data-task-no-due="<?= (int)($taskSummary['no_due_active']??0) ?>" data-inactive-days="<?=e($row['inactive_days']===null?'-1':(string)$row['inactive_days'])?>" data-score-drop="<?=e($row['score_drop']===null?'999':(string)$row['score_drop'])?>" data-returned="<?= (int)($row['returned']??0) ?>" data-published="<?= (int)($row['published']??0) ?>" data-tests="<?= (int)($activity['tests']??0) ?>" data-labs="<?= (int)($activity['labs']??0) ?>">
            <td class="result-check-col"><input type="checkbox" name="student_keys[]" value="<?=e((string)$row['key'])?>" data-row-select aria-label="Vybrat <?=e((string)$row['label'])?>"></td>
            <td><div class="result-student-cell"><span class="teacher-avatar"><?=e(strtoupper(u_substr((string)$row['label'],0,1)))?></span><div><strong><?=e((string)$row['label'])?></strong><small><?=e((string)($row['seat_label']?:$row['email']?:'bez doplňujících údajů'))?></small><?php if($marker!=='none'): ?><em class="result-marker marker-<?=e($marker)?>"><?=e(teacher_student_marker_label($marker))?></em><?php endif; ?></div></div></td>
            <td><strong><?= (int)$row['published'] ?></strong><small><?= (int)$row['drafts'] ?> koncept · <?= (int)$row['returned'] ?> vráceno</small></td>
            <td><b><?=teacher_class_pct($row['avg_project_pct'],0)?></b></td>
            <td><?php if($row['avg_grade']===null): ?>—<?php else: ?><span class="grade-pill grade-<?=max(1,min(5,(int)round((float)$row['avg_grade'])))?>"><?=teacher_class_grade($row['avg_grade'])?></span><?php endif; ?></td>
            <td><strong><?= (int)($activity['tests']??0) ?>×</strong><small>best <?=teacher_class_pct(isset($activity['best_test'])&&$activity['best_test']!==null?(float)$activity['best_test']:null,0)?></small></td>
            <td><strong><?= (int)($activity['labs']??0) ?>×</strong><small>dokončeno</small></td>
            <td><strong><?= (int)($activity['prestige_passed']??0) ?> / <?= (int)($activity['prestige_attempts']??0) ?></strong><small>úspěšně / pokusy</small></td>
            <td><b><?=teacher_class_pct($row['mastery'],0)?></b></td>
            <td><span class="result-priority priority-<?=e($priority)?>"><i></i><?=e(teacher_student_priority_label($priority))?></span></td>
            <td><span class="result-status <?=e($status==='ok'?'ok':($status==='no_result'?'neutral':'attention'))?>"><?=e(teacher_student_status_label($status))?></span><?php if($last>0): ?><small><?=e(date('d.m.Y',$last))?></small><?php endif; ?></td>
            <td><strong><?= (int)($row['active_tasks']??0) ?></strong><small>aktivní · <?= (int)($row['done_tasks']??0) ?> hotovo</small><?php if((int)($row['active_tasks']??0)>0): ?><em class="task-deadline <?=((int)($taskSummary['overdue']??0)>0?'overdue':((int)($taskSummary['due_3']??0)>0?'soon':''))?>"><?=e(teacher_task_deadline_label($taskSummary))?></em><?php endif; ?></td>
            <td><div class="result-row-actions"><a href="?tab=student360&class=<?=e($classId)?>&student=<?=e((string)$row['key'])?>">360°</a><a href="?tab=skills&class=<?=e($classId)?>&student=<?=e(skill_student_key_for_label($classId,(string)$row['label']))?>">Skills</a><a href="?tab=grade&class=<?=e($classId)?>">Hodnotit</a></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table><div class="teacher-empty class-results-no-match" data-class-results-empty hidden>Žádný student neodpovídá aktivním filtrům.</div></div>
      </form>
    </section>
    <?php
}
