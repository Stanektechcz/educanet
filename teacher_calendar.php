<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function teacher_render_school_calendar(array $schoolYear): void
{
    $rows=is_array($schoolYear['calendar']??null)?$schoolYear['calendar']:[];
    $classes=['class_1a','class_2a','class_3a','class_4a'];
    // v59: rozvrh všech tříd je jen pro čtení (bez osobních dat); výjimky zakládá a ruší učitel jen ve svých třídách.
    $editableClasses=function_exists('teacher59_can_class')?array_values(array_filter($classes,static fn(string $c):bool=>teacher59_can_class($c))):$classes;
    $lessonMaps=[];
    $effectiveByClass=[];
    foreach($classes as $cid){
        $effectiveByClass[$cid]=[];
        foreach(adaptive_school_year_rows($schoolYear,$cid) as $effectiveRow){if(is_array($effectiveRow)&&!empty($effectiveRow['date']))$effectiveByClass[$cid][(string)$effectiveRow['date']]=$effectiveRow;}
        $lessonMaps[$cid]=[];
        foreach(teacher_curriculum_lessons($cid) as $lesson){
            if(!is_array($lesson))continue;
            $lessonMaps[$cid][(int)($lesson['number']??0)]=(string)($lesson['title']??'');
        }
    }
    $schedules=[]; foreach($classes as $cid)$schedules[$cid]=adaptive_class_schedule($schoolYear,$cid);
    $today=date('Y-m-d');$futureMarked=false;$slot=0;
    ?>
    <section class="teacher-page-head"><div><div class="eyebrow">Kalendář · školní rok <?=e((string)($schoolYear['meta']['school_year']??'2026/2027'))?></div><h1>Středeční rozvrh 1.A–4.A</h1><p>Kalendář teď respektuje skutečný rozvrh: každá třída má vlastní stabilní čas a student uvidí pouze svůj blok. Obsah lekcí, výjimky a posuny zůstávají vedené po třídách.</p></div><div class="curriculum-head-actions"><button class="btn secondary" type="button" onclick="window.print()">Tisk / PDF</button><a class="btn secondary" href="calendar.ics.php?class=class_1a">ICS 1.A</a><a class="btn secondary" href="calendar.ics.php?class=class_2a">ICS 2.A</a><a class="btn secondary" href="calendar.ics.php?class=class_3a">ICS 3.A</a><a class="btn secondary" href="calendar.ics.php?class=class_4a">ICS 4.A</a><a class="btn primary" href="materials/school_year/SCHOOL_YEAR_2026_2027.md" target="_blank">Roční plán ↗</a></div></section>
    <section class="teacher-kpi-grid curriculum-kpis"><article><span>Kalendářní středy</span><strong><?= (int)($schoolYear['meta']['wednesdays_total']??44) ?></strong><small>2. 9. 2026 – 30. 6. 2027</small></article><article><span>Výukové středy</span><strong><?= (int)($schoolYear['meta']['teaching_wednesdays']??40) ?></strong><small>po odečtení svátků/prázdnin</small></article><article><span>Strukturované</span><strong>28</strong><small>lekce s workflow a evidence</small></article><article><span>Aplikované</span><strong>12</strong><small>projekty · Mastery · portfolio · rezerva</small></article></section>
    <section class="teacher-schedule-strip"><?php foreach(['class_1a','class_4a','class_2a','class_3a'] as $cid): $sc=$schedules[$cid]; ?><article><span><?=e((string)$sc['label'])?> · středa</span><strong><?=e((string)$sc['start'])?>–<?=e((string)$sc['end'])?></strong><small><?=e((string)$sc['subject'])?><?php if(!empty($sc['periods'])): ?> · <?=e(implode('–',(array)$sc['periods']))?>. hodina<?php endif; ?></small></article><?php endforeach; ?></section>
    <?php $periodSlots=[1=>'08:00–08:45',2=>'08:55–09:40',3=>'10:00–10:45',4=>'10:55–11:40',5=>'11:50–12:35',6=>'12:45–13:30',7=>'13:35–14:20',8=>'14:30–15:15',9=>'15:20–16:05']; ?>
    <section class="teacher-week-timetable" aria-label="Středeční rozvrh"><div class="teacher-week-timetable-head"><?php foreach($periodSlots as $n=>$time): ?><span><b><?=$n?>.</b><small><?=e($time)?></small></span><?php endforeach; ?></div><div class="teacher-week-timetable-grid"><article class="timetable-block class-1a" style="--from:1;--span:2"><strong>DGD · 1.A</strong><span>08:00–09:40</span></article><article class="timetable-block class-4a" style="--from:3;--span:2"><strong>SOSaPS · 4.A</strong><span>10:00–11:40</span></article><div class="timetable-break" style="--from:5"><span>pauza</span></div><article class="timetable-block class-2a" style="--from:6;--span:2"><strong>GRA · 2.A</strong><span>12:45–14:20</span></article><article class="timetable-block class-3a" style="--from:8;--span:2"><strong>SOSaPS · 3.A</strong><span>14:30–16:05</span></article></div></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Plán roku</span><h2>1.A–4.A na jedné časové ose</h2></div><small>Neznámé ředitelské volno/exkurze přesouvej do rezervního bloku, ne přeskočením důležité praxe.</small></div>
    <div class="teacher-year-calendar">
      <div class="teacher-year-head"><span>Datum</span><span>Blok</span><?php foreach($classes as $cid): ?><span><?=e(teacher_class_label($cid))?><small><?=e((string)($schedules[$cid]['start']??''))?>–<?=e((string)($schedules[$cid]['end']??''))?></small></span><?php endforeach; ?></div>
      <?php foreach($rows as $r): if(!is_array($r))continue;$date=(string)($r['date']??'');$teaching=(string)($r['status']??'')==='teaching';if($teaching)$slot++;$current=false;if(!$futureMarked&&$date>=$today){$current=true;$futureMarked=true;}$ln=(int)($r['lesson_number']??0); ?>
      <article class="teacher-year-row<?=!$teaching?' no-school':''?><?=$current?' current':''?>">
        <div><strong><?=e(date('d.m.Y',strtotime($date)))?></strong><small>středa</small></div>
        <div><strong><?=$teaching?'#'.str_pad((string)$slot,2,'0',STR_PAD_LEFT):'—'?></strong><small><?=e($teaching?($ln>0?'strukturovaná lekce':(string)($r['title']??'aplikovaný blok')):(string)($r['title']??'bez výuky'))?></small></div>
        <?php foreach($classes as $cid): $cell=$effectiveByClass[$cid][$date]??$r;$cellTeaching=(string)($cell['status']??'')==='teaching';$cellLn=(int)($cell['lesson_number']??0); ?><div><?php if(!$cellTeaching): ?><strong><?=e((string)($cell['title']??'Bez výuky'))?></strong><small><?=e(teacher_class_label($cid))?></small><?php if(!empty($cell['exception'])): ?><span class="calendar-exception-badge"><?=e(adaptive_calendar_exception_label((string)($cell['exception']['type']??'note')))?></span><?php endif; ?><?php elseif($cellLn>0): ?><strong><?=e((string)($lessonMaps[$cid][$cellLn]??('Lekce '.$cellLn)))?></strong><small><?=e(teacher_class_label($cid))?> · <?=e((string)($schedules[$cid]['start']??''))?>–<?=e((string)($schedules[$cid]['end']??''))?><?php if(!empty($cell['auto_shifted'])): ?> · posunuto z <?=e(date('d.m.',strtotime((string)$cell['shifted_from'])))?><?php endif; ?></small><?php if(!empty($cell['auto_shifted'])): ?><span class="calendar-exception-badge shifted">automaticky posunuto</span><?php endif; ?><?php else: ?><strong><?=e((string)($cell['title']??'Aplikovaný blok'))?></strong><small><?=e((string)($schedules[$cid]['start']??''))?>–<?=e((string)($schedules[$cid]['end']??''))?> · <?=e((string)($schoolYear['class_tail_focus'][$cid]??''))?><?php if(!empty($cell['auto_shifted'])): ?> · posunuto<?php endif; ?></small><?php endif; ?><?php if(!empty($cell['schedule_warning'])): ?><span class="calendar-warning-badge"><?=e((string)$cell['schedule_warning'])?></span><?php endif; ?></div><?php endforeach; ?>
      </article>
      <?php endforeach; ?>
    </div></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Výjimky kalendáře</span><h2>Odpadlá hodina, exkurze nebo posun</h2></div><small>Změna se propíše pouze vybrané třídě. Při zrušení středy se obsah automaticky posune a nejdřív spotřebuje rezervní blok.</small></div>
      <form method="post" class="teacher-calendar-editor"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_calendar_exception_save">
        <label>Třída<select name="class_id"><?php foreach($editableClasses as $cid):?><option value="<?=e($cid)?>"><?=e(teacher_class_label($cid))?></option><?php endforeach;?></select></label>
        <label>Datum<input type="date" name="date" min="<?=e((string)($schoolYear['meta']['start']??'2026-09-01'))?>" max="<?=e((string)($schoolYear['meta']['end']??'2027-06-30'))?>" required></label>
        <label>Typ<select name="type"><option value="cancelled">Výuka odpadá</option><option value="trip">Exkurze / školní akce</option><option value="director_day">Ředitelské volno</option><option value="shifted">Upravený blok</option><option value="note">Pouze poznámka</option></select></label>
        <label>Název<input name="title" maxlength="140" placeholder="např. Exkurze VIDA!"></label>
        <button class="btn primary" type="submit">Uložit změnu</button>
      </form>
      <?php $exceptions=adaptive_store('calendar_exceptions');$visible=[];foreach($exceptions as $ex)if(is_array($ex)&&in_array((string)($ex['class_id']??''),$editableClasses,true))$visible[]=$ex;usort($visible,fn($a,$b)=>strcmp((string)($a['date']??''),(string)($b['date']??''))); ?>
      <?php if($visible): ?><div class="teacher-exception-list"><?php foreach($visible as $ex): ?><article><div><strong><?=e(date('d.m.Y',strtotime((string)$ex['date'])))?> · <?=e(teacher_class_label((string)$ex['class_id']))?></strong><span><?=e((string)$ex['title'])?> · <?=e(adaptive_calendar_exception_label((string)$ex['type']))?></span></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_calendar_exception_remove"><input type="hidden" name="class_id" value="<?=e((string)$ex['class_id'])?>"><input type="hidden" name="date" value="<?=e((string)$ex['date'])?>"><button class="link-button danger" type="submit">Odebrat</button></form></article><?php endforeach;?></div><?php endif; ?>
    </section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Pravidlo pro změny</span><h2>Kalendář je plán, ne past</h2></div></div><div class="curriculum-assessment-grid"><article><strong>Když hodina odpadne</strong><p>Nejdřív použij rezervní blok. Pokud je třeba přesouvat látku, slučuj opakování a reflexi, ne guided practice nebo praktickou evidence.</p></article><article><strong>Když třída potřebuje víc času</strong><p>Mastery má přednost před rychlostí. Použij alternativní vysvětlení, mini-practice a projektový checkpoint místo mechanického pokračování.</p></article><article><strong>Když je třída rychlejší</strong><p>Rozšiřuj přes role, peer teaching, QA, hlubší challenge a portfolio – ne přes bezcílné další úkoly.</p></article></div></section>
    <?php
}
