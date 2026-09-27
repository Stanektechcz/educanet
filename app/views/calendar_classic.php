<?php

declare(strict_types=1);

/**
 * Starý kalendář (?view=calendar). Nedosažitelné: calendar obsluhuje tutorial.php; ponecháno beze změny.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'calendar') {
    guarded_study_redirect();
    render_header('Kalendář školního roku', $module);
    $rows = adaptive_school_year_rows($schoolYear,(string)$classId);
    $classSchedule = adaptive_class_schedule($schoolYear,(string)$classId);
    $classScheduleTime = trim((string)($classSchedule['start']??'')) !== '' ? ((string)$classSchedule['start'].'–'.(string)$classSchedule['end']) : '2 × 45 minut';
    $classSchedulePeriods = array_map('intval',(array)($classSchedule['periods']??[]));
    $lessonTitles=[1=>'Základní dvouhodinový blok'];
    if (is_array($nextLessons[$classId] ?? null)) $lessonTitles[2]=(string)($nextLessons[$classId]['title']??'Lekce 2');
    foreach((array)($extendedLessons[$classId]??[]) as $l) if(is_array($l)&&isset($l['number'])) $lessonTitles[(int)$l['number']]=(string)($l['title']??('Lekce '.(int)$l['number']));
    $today=date('Y-m-d'); $futureMarked=false; $teachingCount=0;$nextCalendarRow=null;
    foreach($rows as $r){if(!is_array($r))continue;if(($r['status']??'')==='teaching')$teachingCount++;if($nextCalendarRow===null&&($r['status']??'')==='teaching'&&(string)($r['date']??'')>=$today)$nextCalendarRow=$r;}
    ?>
    <section class="hero school-calendar-hero">
      <div><div class="eyebrow">Školní rok <?=e((string)($schoolYear['meta']['school_year']??'2026/2027'))?> · <?=e((string)($classSchedule['label']??''))?></div><h1>Každou středu · <?=e($classScheduleTime)?></h1><p><strong><?=e((string)($classSchedule['subject']??$module['subject']??''))?></strong><?php if($classSchedulePeriods): ?> · <?=e(implode('.–',$classSchedulePeriods))?>. vyučovací hodina<?php endif; ?>. V kalendáři vidíš pouze výuku své třídy, její projekty a změny rozvrhu.</p><div class="button-row"><a class="btn secondary" href="calendar.ics.php?class=<?=e((string)$classId)?>">Přidat svůj rozvrh (.ics)</a></div></div>
      <div class="school-calendar-summary"><div><span>Stálý čas</span><strong><?=e($classScheduleTime)?></strong></div><div><span>Strukturované lekce</span><strong>28</strong></div><div><span>Aplikované bloky</span><strong>12</strong></div></div>
    </section>
    <?php if(is_array($nextCalendarRow)): $nextNo=(int)($nextCalendarRow['lesson_number']??0);$nextTitle=$nextNo>0?(string)($lessonTitles[$nextNo]??('Lekce '.$nextNo)):(string)($nextCalendarRow['title']??'Výuka'); ?>
    <section class="v506-calendar-next"><div><span>NEJBLIŽŠÍ VÝUKA</span><strong><?=e(date('d.m.Y',strtotime((string)$nextCalendarRow['date'])))?> · <?=e($classScheduleTime)?></strong><p><?=e($nextTitle)?></p></div><a class="btn primary" href="?view=continue">Pokračovat ve výuce →</a></section>
    <?php endif; ?>
    <details class="v506-progressive v506-map-details v506-calendar-map"><summary><span><strong>Celý školní rok</strong><small>Termíny, projekty, volna a změny rozvrhu.</small></span><b>Zobrazit kalendář</b></summary>
    <div class="school-calendar-legend"><span>● strukturovaná lekce</span><span>◆ projekt / mastery</span><span>— bez výuky</span></div>
    <section class="school-year-grid">
      <?php $slot=0; foreach($rows as $r): if(!is_array($r))continue; $date=(string)($r['date']??''); $noSchool=(string)($r['status']??'')!=='teaching'; if(!$noSchool)$slot++; $isCurrent=false; if(!$futureMarked && $date>=$today){$isCurrent=true;$futureMarked=true;} $lessonNum=(int)($r['lesson_number']??0); $title=$noSchool?(string)($r['title']??'Bez výuky'):($lessonNum>0?(string)($lessonTitles[$lessonNum]??('Lekce '.$lessonNum)):(string)($r['title']??'Aplikovaný blok')); ?>
      <article class="school-week<?= $noSchool?' no-school':'' ?><?= $isCurrent?' current':'' ?>">
        <div class="week-date"><strong><?=e(date('d.m.',strtotime($date)))?></strong><span><?=e(date('Y',strtotime($date)))?></span><?php if(!$noSchool): ?><small class="week-time"><?=e($classScheduleTime)?></small><?php endif; ?></div>
        <div class="week-index"><?= $noSchool?'—':'#'.str_pad((string)$slot,2,'0',STR_PAD_LEFT) ?></div>
        <div class="week-plan"><strong><?=e($title)?></strong><span><?=e((string)($r['description']??($noSchool?'Výuka se nekoná.':'2 × 45 minut')))?></span><?php if(!empty($r['auto_shifted'])): ?><small class="calendar-shift-note">↪ Automaticky posunuto z <?=e(date('d.m.',strtotime((string)$r['shifted_from'])))?></small><?php endif; ?><?php if(!empty($r['schedule_warning'])): ?><small class="calendar-schedule-warning">⚠ <?=e((string)$r['schedule_warning'])?></small><?php endif; ?></div>
        <div class="week-class-focus"><?php if($lessonNum>0): ?><?=e((string)($lessonTitles[$lessonNum]??''))?><?php elseif(!$noSchool): ?><?=e((string)($schoolYear['class_tail_focus'][$classId]??''))?><?php else: ?><?=!empty($r['exception'])?e((string)($r['exception']['title']??'Změna výuky')):'Oficiální volno / prázdniny'?><?php endif; ?>
          <?php if($lessonNum>0 && $date<$today): $absence=adaptive_absence_get((string)$classId,adaptive_student_key((string)$classId),$date); ?>
            <div class="week-recovery-actions"><?php if($absence): ?><a class="mini-action" href="<?=e(module_url('recovery',['date'=>$date]))?>"><?=((string)($absence['status']??'')==='complete'?'✓ Recovery hotovo':'↻ Dokončit recovery')?></a><?php else: ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="adaptive_absence_mark"><input type="hidden" name="date" value="<?=e($date)?>"><input type="hidden" name="lesson_number" value="<?=$lessonNum?>"><input type="hidden" name="lesson_title" value="<?=e($title)?>"><button class="mini-action" type="submit">Chyběl/a jsem</button></form><?php endif; ?></div>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </section></details>
    <details class="v506-progressive v506-map-details"><summary><span><strong>Jak funguje tempo výuky?</strong><small>Co se stane při odpadlé hodině nebo absenci.</small></span><b>Vysvětlit</b></summary><section class="panel"><div class="section-heading compact-heading"><div><div class="eyebrow">Tempo výuky</div><h2>Výuka se nepřeskakuje.</h2><p>Pokud odpadne hodina, použije se rezervní blok. Při absenci se nabídne krátký Recovery Path místo dohánění celé hodiny.</p></div></div></section></details>
    <?php render_footer(); exit;
}
