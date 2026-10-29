<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function coach_energy_label(string $energy): string
{
    return match($energy){'low'=>tr('Mám málo energie'),'high'=>tr('Mám energii na výzvu'),default=>tr('Normální tempo')};
}

function coach_item_mark(string $type): string
{
    return match($type){'task'=>'✓','intervention'=>'↗','retrieval'=>'↺','mistake'=>'!','skill'=>'◆',default=>'→'};
}

function render_student_coach_dashboard(string $classId,array $module,array $tourMap,string $studentKey,?array $snapshot=null): void
{
    if($studentKey==='')return;$s=is_array($snapshot)?$snapshot:coach_dashboard_snapshot($classId,$studentKey,$module,$tourMap);$plan=$s['plan'];$prefs=$s['preferences'];$items=(array)$plan['items'];
    ?>
    <section class="dashboard-panel coach-dashboard-panel" id="learning-coach">
      <div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Learning Coach · dnes bez přemýšlení co dělat'))?></div><h2><?=e(tr('{minutes} minut, které mají největší smysl',['minutes'=>(int)$prefs['minutes']]))?></h2><p><?=e(tr('Systém skládá krátký plán z termínů, rozloženého opakování, opakovaných chyb a dalšího odemčeného kroku. Nejde o známku ani další povinnou metriku.'))?></p></div><a class="btn primary" href="<?=e(module_url('study',['minutes'=>(int)$prefs['minutes'],'energy'=>'normal','intent'=>(string)$prefs['intent']]))?>"><?=e(tr('Spustit plán →'))?></a></div>
      <div class="coach-dashboard-grid">
        <div class="coach-mini-plan"><?php foreach(array_slice($items,0,3) as $item): ?><a class="coach-mini-step <?=!empty($item['done'])?'done':''?>" href="<?=e((string)$item['href'])?>"><i><?=e(coach_item_mark((string)$item['type']))?></i><div><strong><?=isset($item['title_html'])?$item['title_html']:e((string)$item['title'])?></strong><span><?=e((string)$item['reason'])?></span></div><b><?=e(tr('{minutes} min',['minutes'=>(int)$item['minutes']]))?></b></a><?php endforeach; ?><?php if(!$items):?><div class="coach-empty-mini"><strong><?=e(tr('Dnes není nic urgentního.'))?></strong><span><?=e(tr('Můžeš pokračovat v mapě kurzu nebo si dát volno.'))?></span></div><?php endif;?></div>
        <div class="coach-dashboard-stats"><a href="?view=review"><strong><?= (int)$s['forecast']['buckets'][0] ?></strong><span><?=e(tr('opakování dnes'))?></span></a><a href="?view=mistakes"><strong><?= (int)$s['open_mistakes'] ?></strong><span><?=e(tr('mentálních modelů k opravě'))?></span></a><div><strong><?=e(tr('{days}/7',['days'=>(int)$s['week']['active_days']]))?></strong><span><?=e(tr('dnů s učením tento týden'))?></span></div></div>
      </div>
      <div class="coach-comfort-row"><a href="?view=study&minutes=10&energy=low&intent=balanced"><?=e(tr('Dnes mám málo energie · dej mi 10 minut'))?></a><a href="?view=study&minutes=45&energy=high&intent=exam"><?=e(tr('Připravuji se na test · 45 minut'))?></a><a href="?view=mistakes"><?=e(tr('Otevřít Mistake Notebook'))?></a><a href="?view=study_loop"><?=e(tr('8min adaptivní trénink'))?></a></div>
    </section>
    <?php
}

function render_student_coach_view(string $classId,array $module,array $tourMap,string $flash=''): void
{
    $studentKey=adaptive_student_key($classId);if($studentKey===''){redirect_to('?view=dashboard');}
    $prefs=coach_preferences($classId,$studentKey);
    $minutes=(int)($_GET['minutes']??$prefs['minutes']);if(!in_array($minutes,[10,25,45],true))$minutes=(int)$prefs['minutes'];
    $energy=(string)($_GET['energy']??'normal');if(!in_array($energy,['low','normal','high'],true))$energy='normal';
    $intent=(string)($_GET['intent']??$prefs['intent']);if(!in_array($intent,['balanced','exam'],true))$intent='balanced';
    $plan=coach_plan($classId,$studentKey,$module,$tourMap,$minutes,$energy,$intent);$daily=coach_daily_row($classId,$studentKey);$forecast=coach_review_forecast($classId,$studentKey,7);$week=coach_week_summary($classId,$studentKey);$cal=coach_confidence_calibration($classId,$studentKey);$mistakes=coach_mistake_notebook($classId,$studentKey,$module,4);
    render_header(tr('Learning Coach'),$module);
    ?>
    <?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <section class="coach-shell">
      <header class="coach-hero"><div><div class="eyebrow"><?=e(tr('Student Learning Coach · v47'))?></div><h1><?=e(tr('Neuč se déle. Uč se v lepším pořadí.'))?></h1><p><?=e(tr('Vyber jen čas a energii. Plán se složí z toho, co právě nejvíc zvyšuje šanci, že si látku vybavíš i později.'))?></p></div><div class="coach-hero-score"><strong><?=e(tr('{done}/{total}',['done'=>(int)$plan['done_count'],'total'=>(int)$plan['total_count']]))?></strong><span><?=e(tr('dnešních kroků'))?></span><small><?=e(tr('{minutes} min plán',['minutes'=>(int)$plan['planned_minutes']]))?></small></div></header>

      <form method="get" class="coach-settings"><input type="hidden" name="view" value="study"><label><span><?=e(tr('Kolik máš času?'))?></span><select name="minutes"><option value="10" <?=$minutes===10?'selected':''?>><?=e(tr('10 min · minimum'))?></option><option value="25" <?=$minutes===25?'selected':''?>><?=e(tr('25 min · doporučeno'))?></option><option value="45" <?=$minutes===45?'selected':''?>><?=e(tr('45 min · hlubší blok'))?></option></select></label><label><span><?=e(tr('Jak se dnes cítíš?'))?></span><select name="energy"><option value="low" <?=$energy==='low'?'selected':''?>><?=e(tr('Málo energie'))?></option><option value="normal" <?=$energy==='normal'?'selected':''?>><?=e(tr('Normálně'))?></option><option value="high" <?=$energy==='high'?'selected':''?>><?=e(tr('Mám energii'))?></option></select></label><label><span><?=e(tr('Cíl'))?></span><select name="intent"><option value="balanced" <?=$intent==='balanced'?'selected':''?>><?=e(tr('Průběžně se zlepšovat'))?></option><option value="exam" <?=$intent==='exam'?'selected':''?>><?=e(tr('Příprava na test / zkoušku'))?></option></select></label><button class="btn secondary" type="submit"><?=e(tr('Přepočítat plán'))?></button></form>

      <div class="coach-layout">
        <main class="coach-main">
          <section class="coach-plan-panel"><div class="coach-section-head"><div><span><?=e(tr('Dnes stačí toto'))?></span><h2><?=e(coach_energy_label($energy))?> · <?=e($intent==='exam'?tr('víc vybavování a oprav chyb'):tr('vyvážený postup'))?></h2></div><button class="coach-timer-launch" type="button" data-coach-timer-start="<?=max(1,(int)$plan['planned_minutes'])?>"><?=e(tr('▶ Focus timer {minutes} min',['minutes'=>(int)$plan['planned_minutes']]))?></button></div>
          <div class="coach-timer" hidden data-coach-timer><strong data-coach-timer-value>00:00</strong><span><?=e(tr('Jeden úkol. Bez přepínání.'))?></span><button type="button" data-coach-timer-stop><?=e(tr('Ukončit timer'))?></button></div>
          <div class="coach-plan-list">
            <?php foreach((array)$plan['items'] as $i=>$item):?>
            <article class="coach-plan-step <?=!empty($item['done'])?'done':''?>" data-coach-step>
              <div class="coach-step-number"><?=!empty($item['done'])?'✓':str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?></div>
              <div class="coach-step-copy"><div class="coach-step-kicker"><span><?=e((string)$item['reason'])?></span><b><?=e(tr('{minutes} min',['minutes'=>(int)$item['minutes']]))?></b></div><h3><?=isset($item['title_html'])?$item['title_html']:e((string)$item['title'])?></h3><p<?=!empty($item['text_is_content'])?edu_content_lang_attr():''?>><?=e((string)$item['text'])?></p>
                <?php if((string)$item['type']==='task'&&!empty($item['task']['due_at'])):?><small class="coach-due"><?=e(tr('Termín {date}',['date'=>date('d.m.Y',strtotime((string)$item['task']['due_at']))]))?></small><?php endif;?>
                <div class="coach-step-actions"><a class="btn primary small" href="<?=e((string)$item['href'])?>"><?=e((string)$item['type']==='retrieval'?tr('Spustit bez poznámek →'):tr('Otevřít krok →'))?></a><button type="button" class="btn secondary small" data-coach-item-timer="<?=max(1,(int)$item['minutes'])?>"><?=e(tr('Timer {minutes} min',['minutes'=>(int)$item['minutes']]))?></button></div>
              </div>
              <form method="post" class="coach-done-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="coach_step_toggle"><input type="hidden" name="item_id" value="<?=e((string)$item['id'])?>"><input type="hidden" name="done" value="<?=!empty($item['done'])?'0':'1'?>"><input type="hidden" name="minutes" value="<?=$minutes?>"><input type="hidden" name="energy" value="<?=e($energy)?>"><input type="hidden" name="intent" value="<?=e($intent)?>"><button type="submit"><?=e(!empty($item['done'])?tr('Vrátit'):tr('Mám hotovo'))?></button></form>
            </article>
            <?php endforeach;?>
            <?php if(empty($plan['items'])):?><div class="coach-zero"><strong><?=e(tr('Nemáš nic naléhavého.'))?></strong><p><?=e(tr('To je dobrý signál. Vyber si další odemčenou lekci nebo si dej skutečnou pauzu.'))?></p><a class="btn primary" href="?view=course"><?=e(tr('Mapa kurzu →'))?></a></div><?php endif;?>
          </div></section>

          <section class="coach-exit-panel"><div><span><?=e(tr('Exit ticket · 60 sekund'))?></span><h2><?=e(tr('Zavři studium tím, že pojmenuješ, co se změnilo.'))?></h2><p><?=e(tr('Není to sloh. Stačí krátká věta; pomáhá rozlišit „viděl/a jsem to“ od „umím to vysvětlit“.'))?></p></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="coach_exit_ticket"><input type="hidden" name="minutes" value="<?=$minutes?>"><input type="hidden" name="energy" value="<?=e($energy)?>"><input type="hidden" name="intent" value="<?=e($intent)?>"><label><?=e(tr('Co už teď umím říct bez poznámek?'))?><textarea name="learned" rows="2" maxlength="700"><?=e((string)($daily['exit_ticket']['learned']??''))?></textarea></label><label><?=e(tr('Co je ještě nejasné?'))?><textarea name="unclear" rows="2" maxlength="700"><?=e((string)($daily['exit_ticket']['unclear']??''))?></textarea></label><label><?=e(tr('Jaký bude můj nejbližší další krok?'))?><input name="next" maxlength="700" value="<?=e((string)($daily['exit_ticket']['next']??''))?>"></label><fieldset><legend><?=e(tr('Jak moc věříš, že bys to vysvětlil/a zítra?'))?></legend><?php foreach([1=>tr('Spíš ne'),2=>tr('Asi ano'),3=>tr('Ano')] as $v=>$label):?><label><input type="radio" name="confidence" value="<?=$v?>" <?=((int)($daily['exit_ticket']['confidence']??2)===$v)?'checked':''?>> <?=e($label)?></label><?php endforeach;?></fieldset><button class="btn primary" type="submit"><?=e(tr('Uložit a pro dnešek končím'))?></button></form></section>
        </main>

        <aside class="coach-side">
          <section class="coach-side-card"><div class="eyebrow"><?=e(tr('Rozložené opakování'))?></div><h3><?=e(tr('{count} témata dnes',['count'=>(int)$forecast['buckets'][0]]))?></h3><div class="coach-forecast"><?php foreach(array_slice((array)$forecast['buckets'],0,7,true) as $d=>$count):?><div><span><?=e($d===0?tr('Dnes'):tr('+{days} d',['days'=>$d]))?></span><b><?= (int)$count ?></b></div><?php endforeach;?></div><a href="?view=review"><?=e(tr('3min opakování →'))?></a></section>
          <section class="coach-side-card calibration-<?=e((string)$cal['state'])?>"><div class="eyebrow"><?=e(tr('Kalibrace jistoty'))?></div><h3><?=e((string)$cal['title'])?></h3><p><?=e((string)$cal['text'])?></p><div class="coach-calibration-bars"><span><b style="--v:<?= $cal['total']?round($cal['under']/$cal['total']*100):0 ?>%"></b><?=e(tr('Podhodnocení'))?></span><span><b style="--v:<?= $cal['total']?round($cal['calibrated']/$cal['total']*100):0 ?>%"></b><?=e(tr('Kalibrovaně'))?></span><span><b style="--v:<?= $cal['total']?round($cal['over']/$cal['total']*100):0 ?>%"></b><?=e(tr('Nadhodnocení'))?></span></div></section>
          <section class="coach-side-card"><div class="eyebrow"><?=e(tr('Tento týden'))?></div><div class="coach-week-number"><strong><?= (int)$week['active_days'] ?></strong><span><?=e(tr('aktivních dnů ze 7'))?></span></div><ul><li><?=e(tr('{correct}/{total} retrieval odpovědí správně',['correct'=>(int)$week['retrieval_correct'],'total'=>(int)$week['retrieval_total']]))?></li><li><?=e(tr('{count} dokončených úkolů od učitele',['count'=>(int)$week['tasks_done']]))?></li><li><?=e(tr('{count} krátkých reflexí',['count'=>(int)$week['reflections']]))?></li></ul><small><?=e(tr('Žádný „streak“ se nemaže. Cíl je pravidelnost, ne perfektní série.'))?></small></section>
          <section class="coach-side-card"><div class="eyebrow"><?=e(tr('Mistake Notebook'))?></div><h3><?=e(tr('{count} otevřené vzorce chyb',['count'=>count(array_filter($mistakes,static fn($m)=>empty($m['recovered'])))]))?></h3><?php foreach(array_slice($mistakes,0,3) as $m):?><a class="coach-mistake-mini <?=$m['recovered']?'recovered':''?>" href="<?=e(module_url('study_loop',['topic'=>(string)$m['topic']]))?>"><strong><?=e((string)$m['title'])?></strong><span><?=e((string)$m['label'])?> · <?= (int)$m['count'] ?>×</span></a><?php endforeach;?><a class="coach-card-link" href="?view=mistakes"><?=e(tr('Celý notebook →'))?></a></section>
          <?php v471_render_accelerator_teaser($classId,$studentKey,$module,$tourMap); ?>
          <form method="post" class="coach-defaults"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="coach_preferences_save"><input type="hidden" name="minutes" value="<?=$minutes?>"><input type="hidden" name="intent" value="<?=e($intent)?>"><button type="submit"><?=e($intent==='exam'?tr('Používat {minutes} min jako výchozí · test mode',['minutes'=>$minutes]):tr('Používat {minutes} min jako výchozí',['minutes'=>$minutes]))?></button></form>
        </aside>
      </div>
    </section>
    <?php render_footer();
}

function render_student_mistakes_view(string $classId,array $module,string $flash=''): void
{
    $studentKey=adaptive_student_key($classId);if($studentKey==='')redirect_to('?view=dashboard');$rows=coach_mistake_notebook($classId,$studentKey,$module,30);$open=count(array_filter($rows,static fn($m)=>empty($m['recovered'])));$cal=coach_confidence_calibration($classId,$studentKey);
    render_header(tr('Mistake Notebook'),$module);?>
    <?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <section class="mistake-shell"><header class="coach-hero mistake-hero"><div><div class="eyebrow"><?=e(tr('Mistake Notebook · chyby jako mapa dalšího učení'))?></div><h1><?=e(tr('Neopakuj všechno. Oprav to, co se opakuje.'))?></h1><p><?=e(tr('Notebook vzniká z krátkých checků a retrieval practice. Není to seznam prohřešků ani známka — ukazuje vzorce, které stojí za jedno cílené vysvětlení navíc.'))?></p></div><div class="coach-hero-score"><strong><?=$open?></strong><span><?=e(tr('otevřených vzorců'))?></span><small><?=e(tr('{count} zachycených celkem',['count'=>count($rows)]))?></small></div></header>
    <div class="mistake-summary"><article><span><?=e(tr('Kalibrace'))?></span><strong><?=e((string)$cal['title'])?></strong><p><?=e((string)$cal['text'])?></p></article><article><span><?=e(tr('Jak s notebookem pracovat'))?></span><strong><?=e(tr('1 chyba → 1 princip → 1 nový příklad'))?></strong><p><?=e(tr('Nečti celou kapitolu znovu. Otevři konkrétní princip, vysvětli ho vlastními slovy a ověř na jiném příkladu.'))?></p></article></div>
    <div class="mistake-list"><?php foreach($rows as $m):?><article class="mistake-card <?=$m['recovered']?'recovered':''?>"><div class="mistake-mark"><?=$m['recovered']?'✓':'!'?></div><div><div class="mistake-meta"><span><?=e((string)$m['title'])?></span><b><?=e((int)$m['overconfident']>0?tr('{count}× zachyceno · {over}× vysoká jistota',['count'=>(int)$m['count'],'over'=>(int)$m['overconfident']]):tr('{count}× zachyceno',['count'=>(int)$m['count']]))?></b></div><h2><?=e((string)$m['label'])?></h2><p><?=e((string)$m['coach'])?></p><small><?=e(tr('Poslední výskyt {date}',['date'=>$m['last_at']!==''?date('d.m.Y',strtotime((string)$m['last_at'])):'—']))?><?=$m['recovered']?e(tr(' · novější správná evidence už existuje')):''?></small></div><a class="btn <?=$m['recovered']?'secondary':'primary'?>" href="<?=e(module_url('study_loop',['topic'=>(string)$m['topic']]))?>"><?=e($m['recovered']?tr('Znovu ověřit'):tr('Opravit princip →'))?></a></article><?php endforeach;?><?php if(!$rows):?><div class="coach-zero"><strong><?=e(tr('Zatím tu nic není.'))?></strong><p><?=e(tr('Notebook se začne skládat až po vlastních odpovědích. To je v pořádku — nejdřív potřebujeme skutečnou evidenci, ne odhad.'))?></p><a class="btn primary" href="?view=study"><?=e(tr('Otevřít Learning Coach →'))?></a></div><?php endif;?></div></section>
    <?php render_footer();
}
