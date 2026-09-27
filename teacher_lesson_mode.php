<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function teacher_lesson_mode_find(string $classId, int $number): ?array
{
    foreach (teacher_curriculum_lessons($classId) as $lesson) {
        if (is_array($lesson) && (int)($lesson['number'] ?? 0) === $number) return $lesson;
    }
    return null;
}

function teacher_lesson_mode_schedule(array $lesson): array
{
    $schedule = array_values(array_filter((array)($lesson['schedule'] ?? []), 'is_array'));
    if ($schedule) return $schedule;

    $steps = array_values(array_filter((array)($lesson['steps'] ?? []), 'is_array'));
    if (!$steps) {
        return [
            ['time'=>'0–10','title'=>'Start','text'=>'Cíl, aktivace předchozí znalosti a rychlá orientace.'],
            ['time'=>'10–30','title'=>'Vysvětlení','text'=>'Mentální model + konkrétní ukázka.'],
            ['time'=>'30–55','title'=>'Guided practice','text'=>'Společně jeden případ krok za krokem.'],
            ['time'=>'55–80','title'=>'Samostatná práce','text'=>'Student aplikuje princip do vlastního případu.'],
            ['time'=>'80–90','title'=>'Exit ticket','text'=>'Krátká validace pochopení a další krok.'],
        ];
    }

    $out=[]; $cursor=0; $count=count($steps);
    foreach ($steps as $i=>$step) {
        $raw=(string)($step['time'] ?? '');
        $minutes=(int)preg_replace('/\D+.*/','',$raw);
        if ($minutes <= 0) $minutes=(int)floor(90/max(1,$count));
        if ($i === $count-1) $end=90; else $end=min(90,$cursor+$minutes);
        $out[]=['time'=>$cursor.'–'.$end,'title'=>(string)($step['title']??('Krok '.($i+1))),'text'=>implode(' · ',array_slice(array_map('strval',(array)($step['tasks']??[])),0,2))];
        $cursor=$end;
    }
    return $out;
}

function teacher_render_lesson_mode(string $classId, int $number): void
{
    $lesson=teacher_lesson_mode_find($classId,$number);
    if(!$lesson){echo '<div class="teacher-empty wide">Lekce nebyla nalezena.</div>';return;}
    $schedule=teacher_lesson_mode_schedule($lesson);
    $notes=array_values(array_filter(array_map('strval',(array)($lesson['teacher_notes']??[])),static fn($v)=>trim($v)!==''));
    $outputs=array_values(array_filter(array_map('strval',(array)($lesson['worksheet']??[])),static fn($v)=>trim($v)!==''));
    $knowledge=array_values(array_filter(array_map('strval',(array)($lesson['knowledge']??[])),static fn($v)=>trim($v)!==''));
    global $modules;
    $lessonResources = require __DIR__ . '/learning_resources.php';
    $v42Pack = v42_lesson_pack($classId,$lesson,$modules[$classId],$lessonResources,'');
    $v42Override = v42_lesson_resource_override($classId,$lesson);
    $v42LaneCounts=['guided'=>0,'standard'=>0,'challenge'=>0];
    foreach(project_students_for_class($classId) as $student){$sk=(string)($student['key']??'');if($sk==='')continue;$ln=v42_learning_lane($classId,$sk,$lesson);$lk=(string)($ln['lane']??'standard');if(isset($v42LaneCounts[$lk]))$v42LaneCounts[$lk]++;}
    $copilotPlan=ml_teacher_copilot_plan($classId,$lesson);
    $realityPrompts=[];
    foreach(array_slice($knowledge,0,3) as $topic){
        $article=is_array($modules[$classId]['knowledgebase'][$topic]??null)?$modules[$classId]['knowledgebase'][$topic]:['title'=>$topic,'summary'=>''];
        $realityPrompts[]=reality_demo_spec($classId,$topic,$article);
    }
    // v56: učitel vidí stejný název a cíl lekce jako žáci.
    $v56Bundle = function_exists('v56_lesson_bundle')
        ? v56_lesson_bundle($classId, $modules[$classId], $number, (array)($GLOBALS['nextLessons'] ?? []), (array)($GLOBALS['extendedLessons'] ?? []))
        : null;
    $v56Title = is_array($v56Bundle) ? (string)$v56Bundle['title'] : (string)($lesson['title'] ?? ('Lekce ' . $number));
    $v56Goal = is_array($v56Bundle) && trim((string)$v56Bundle['goal']) !== '' ? (string)$v56Bundle['goal'] : (string)($lesson['goal'] ?? '');
    ?>
    <section class="lesson-mode" data-lesson-mode>
      <div class="lesson-mode-toolbar">
        <div><a class="teacher-back" href="?tab=curriculum&class=<?=e($classId)?>">← Zpět do Výuky</a><span class="eyebrow"><?=e(teacher_class_label($classId))?> · Lekce <?=str_pad((string)$number,2,'0',STR_PAD_LEFT)?></span></div>
        <div class="lesson-mode-actions"><button class="btn secondary" type="button" data-v42-teacher-deck-open>Prezentace</button><button class="btn secondary" type="button" data-lesson-fullscreen>Celá obrazovka</button><button class="btn primary" type="button" data-lesson-start>Spustit hodinu</button></div>
      </div>

      <div class="lesson-mode-hero">
        <div><span>Dnešní cíl</span><h1>Lekce <?=$number?> · <?=e($v56Title)?></h1><p><?=e($v56Goal)?></p></div>
        <div class="lesson-mode-clock" aria-live="polite"><strong data-lesson-clock>90:00</strong><span data-lesson-clock-label>připraveno</span><div><button type="button" data-lesson-pause disabled>Pauza</button><button type="button" data-lesson-reset>Reset</button></div></div>
      </div>

      <div class="v42-teacher-deck" data-v42-teacher-deck hidden role="dialog" aria-modal="true" aria-label="Prezentace lekce">
        <div class="v42-teacher-deck-toolbar"><strong><?=e((string)($lesson['title']??'Lekce'))?></strong><div><button type="button" class="btn secondary small" data-v42-deck-prev>←</button><span data-v42-deck-count>1 / <?=count((array)$v42Pack['slides'])?></span><button type="button" class="btn secondary small" data-v42-deck-next>→</button><button type="button" class="btn secondary small" data-v42-deck-full>Fullscreen</button><button type="button" class="btn secondary small" data-v42-teacher-deck-close>Zavřít</button></div></div>
        <div class="v42-teacher-deck-stage"><?php foreach((array)$v42Pack['slides'] as $i=>$sl):?><article class="v42-teacher-deck-slide <?= $i===0?'active':'' ?>" data-v42-deck-slide="<?=$i?>" <?= $i===0?'':'hidden' ?>><span><?=e((string)$sl['kicker'])?></span><h2><?=e((string)$sl['title'])?></h2><p><?=e((string)$sl['body'])?></p><footer><strong>Poznámka pro výklad</strong><small><?=e((string)$sl['note'])?></small></footer></article><?php endforeach;?></div>
      </div>

      <?php if (is_array($v56Bundle)): $v56Mins = 0; foreach ((array)$v56Bundle['steps'] as $v56St) { if (preg_match('/(\d+)/', (string)$v56St['time'], $v56M)) $v56Mins += (int)$v56M[1]; } ?>
      <section class="teacher-panel v56-teach-path">
        <div class="teacher-panel-head"><div><span>Co dnes projdou žáci</span><h2>Teorie → test → projekt → odevzdání</h2></div><small>Stejná posloupnost, jakou vidí ve svém rozhraní</small></div>
        <div class="v56-teach-grid">
          <article><b>1</b><div><strong>Teorie</strong><small><?=count((array)$v56Bundle['topics'])?> témat: <?=e(implode(', ', array_map(static fn(array $t): string => (string)$t['title'], (array)$v56Bundle['topics'])))?></small></div></article>
          <article><b>2</b><div><strong>Test</strong><small><?=count((array)$v56Bundle['questions'])?> otázek · na postup <?=V56_TEST_PASS_PERCENT?> %</small></div></article>
          <article><b>3</b><div><strong>Projekt</strong><small><?=count((array)$v56Bundle['steps'])?> kroků · <?=$v56Mins?> min: <?=e(implode(' → ', array_map(static fn(array $st): string => (string)$st['title'], (array)$v56Bundle['steps'])))?></small></div></article>
          <article><b>4</b><div><strong>Odevzdání</strong><small>Popis + odkaz na výstup. Pak bonus podle zbývajícího času.</small></div></article>
        </div>
      </section>
      <?php endif; ?>

      <section class="teacher-panel ml-teach-now"><div class="teacher-panel-head"><div><span>Teacher Copilot · návrh z aktuálních dat</span><h2>Dnešní výuková sekvence</h2></div><small>Učitel má vždy poslední slovo</small></div><div class="ml-copilot-plan"><?php foreach($copilotPlan as $row): ?><article><span><?=e((string)$row['minutes'])?> min</span><strong><?=e((string)$row['title'])?></strong><p><?=e((string)$row['text'])?></p></article><?php endforeach; ?></div></section>

      <?php cv43_render_teacher_explainer($classId,$lesson,$modules[$classId]); ?>
      <?php if(function_exists('v44_render_teacher_live_board')) v44_render_teacher_live_board($classId,$lesson,$modules[$classId]); ?>
      <?php if(function_exists('v45_render_teacher_projection')) v45_render_teacher_projection($classId,$lesson,$modules[$classId]); ?>
      <?php if(function_exists('v48_render_teacher_orchestration')) v48_render_teacher_orchestration($classId,$lesson,$modules[$classId]); ?>
      <?php if(function_exists('v481_render_teacher_extension')) v481_render_teacher_extension($classId,$lesson); ?>
      <?php if(function_exists('tut52_render_teacher_lesson')) tut52_render_teacher_lesson($classId,$modules[$classId],$number,$lesson); ?>

      <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Lesson Kit v42</span><h2>Video · prezentace · podklady · prompty · zkouška</h2></div><small><?=count((array)$v42Pack['slides'])?> slidů · <?=count((array)$v42Pack['prompts'])?> promptů</small></div><div class="v42-signal-strip"><span>Více podpory: <strong><?= (int)$v42LaneCounts['guided'] ?></strong></span><span>Standard: <strong><?= (int)$v42LaneCounts['standard'] ?></strong></span><span>Větší výzva: <strong><?= (int)$v42LaneCounts['challenge'] ?></strong></span><span>Bez studentů/dat se automaticky používá standard.</span></div>
        <div class="teacher-two-col"><div><h3>Doporučené video</h3><?php foreach(array_slice((array)$v42Pack['videos'],0,2) as $v):?><p><a class="text-link" target="_blank" rel="noopener noreferrer" href="<?=e((string)$v['url'])?>"><?=e((string)$v['title'])?> ↗</a><br><small><?=e((string)$v['watch_for'])?></small></p><?php endforeach;?></div><div><h3>Hotové podklady</h3><p><a class="text-link" target="_blank" href="materials/lesson_kits/<?=e($classId)?>/lesson_<?=str_pad((string)$number,2,'0',STR_PAD_LEFT)?>.md">Otevřít kompletní lesson kit ↗</a></p><p><strong>Zkouška:</strong> <?=count((array)$v42Pack['exam']['oral'])?> ústních otázek + praktický scénář.</p></div></div>
        <details><summary><strong>Prezentace · <?=count((array)$v42Pack['slides'])?> slidů</strong></summary><div class="v42-rubric"><?php foreach((array)$v42Pack['slides'] as $i=>$sl):?><div><strong><?=($i+1)?> · <?=e((string)$sl['title'])?></strong><span><?=e((string)$sl['body'])?></span></div><?php endforeach;?></div></details>
        <details class="v42-teacher-resource"><summary><strong>Upravit doporučené video</strong> · volitelné</summary>
          <p class="muted">Použij jen zdroj, který je pro tuto konkrétní lekci lepší než kurátorovaný výchozí materiál. Student vždy dostane i instrukci, co ve videu sledovat.</p>
          <form method="post" class="v42-resource-form">
            <input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v42_lesson_resource_save"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="lesson_number" value="<?=$number?>">
            <label>Název videa<input name="video_title" maxlength="180" value="<?=e((string)($v42Override['title']??''))?>" placeholder="např. DNS troubleshooting krok za krokem"></label>
            <label>URL videa<input name="video_url" type="url" required maxlength="1500" value="<?=e((string)($v42Override['url']??''))?>" placeholder="https://..."></label>
            <label>Poznámka / co má student sledovat<textarea name="video_note" maxlength="600" rows="2" placeholder="Volitelné – např. zaměř se na rozdíl mezi resolverem a autoritativním serverem."><?=e((string)($v42Override['note']??''))?></textarea></label>
            <div class="v42-resource-actions"><button class="btn primary" type="submit">Uložit video</button></div>
          </form>
          <?php if($v42Override):?><form method="post" class="v42-resource-remove" onsubmit="return confirm('Odebrat vlastní video a vrátit kurátorované zdroje?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v42_lesson_resource_remove"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="lesson_number" value="<?=$number?>"><button class="btn secondary" type="submit">Vrátit výchozí zdroj</button></form><?php endif;?>
        </details>
      </section>

      <div class="lesson-mode-grid">
        <section class="lesson-mode-timeline"><div class="teacher-panel-head compact"><div><span>90 minut</span><h2>Průběh hodiny</h2></div><small>Aktivní fáze se zvýrazní podle časovače.</small></div>
          <?php foreach($schedule as $i=>$phase): $parts=preg_split('/[^0-9]+/',(string)($phase['time']??''));$start=(int)($parts[0]??0);$end=(int)($parts[1]??min(90,$start+15)); ?>
          <article data-lesson-phase data-start="<?=$start?>" data-end="<?=$end?>"><div class="lesson-phase-time"><?=e((string)($phase['time']??''))?></div><div><strong><?=e((string)($phase['title']??''))?></strong><?php if(trim((string)($phase['text']??''))!==''):?><p><?=e((string)$phase['text'])?></p><?php endif;?></div><span class="lesson-phase-state">čeká</span></article>
          <?php endforeach; ?>
        </section>

        <aside class="lesson-mode-side">
          <section class="lesson-mode-card"><span>Student musí na konci umět</span><h3>Evidence / výstup</h3><?php if($outputs):?><ul><?php foreach($outputs as $out):?><li><?=e($out)?></li><?php endforeach;?></ul><?php else:?><p>Krátce vysvětlit princip vlastními slovy a ukázat jej na konkrétním příkladu.</p><?php endif;?></section>
          <?php if($knowledge):?><section class="lesson-mode-card"><span>Knowledge</span><h3>Opěrné body</h3><div class="lesson-mode-tags"><?php foreach($knowledge as $topic):?><code><?=e($topic)?></code><?php endforeach;?></div></section><?php endif;?>
          <?php if($realityPrompts):?><section class="lesson-mode-card lesson-reality-prompts"><span>Reality demo</span><h3>Situace pro projektor / diskusi</h3><p>Neprozrazuj řešení hned. Nejdřív nech třídu formulovat hypotézu a říct, jaký důkaz by ji potvrdil.</p><?php foreach($realityPrompts as $rp):$choices=(array)($rp['choices']??[]);?><details><summary><?=e((string)($rp['title']??'Situace'))?></summary><p><?=e((string)($rp['brief']??''))?></p><b>Otázka do třídy</b><p>Co byste udělali jako první — a co by tím vzniklo za důkaz?</p><?php if(isset($choices[0])&&is_array($choices[0])):?><details class="teacher-answer"><summary>Cesta pro učitele</summary><p><strong><?=e((string)($choices[0]['label']??''))?></strong></p><p><?=e((string)($rp['proof']??''))?></p></details><?php endif;?></details><?php endforeach;?></section><?php endif;?>
          <section class="lesson-mode-card"><span>Když to nefunguje</span><h3>Neopakuj stejnou větu</h3><ol><li>Jednodušeji.</li><li>Přirovnání.</li><li>Konkrétní příklad.</li><li>Typická chyba.</li><li>Guided mini-pokus.</li></ol></section>
          <?php if($notes):?><section class="lesson-mode-card"><span>Jen pro učitele</span><h3>Poznámky</h3><ul><?php foreach($notes as $note):?><li><?=e($note)?></li><?php endforeach;?></ul></section><?php endif;?>
        </aside>
      </div>
    </section>
    <script>
    (()=>{
      const root=document.querySelector('[data-lesson-mode]'); if(!root)return;
      const clock=root.querySelector('[data-lesson-clock]'), label=root.querySelector('[data-lesson-clock-label]');
      const startBtn=root.querySelector('[data-lesson-start]'), pauseBtn=root.querySelector('[data-lesson-pause]'), resetBtn=root.querySelector('[data-lesson-reset]');
      const phases=[...root.querySelectorAll('[data-lesson-phase]')];
      let remaining=90*60, timer=null, running=false;
      const render=()=>{
        const elapsed=90*60-remaining, elapsedMin=elapsed/60;
        const mm=Math.floor(remaining/60), ss=remaining%60; clock.textContent=String(mm).padStart(2,'0')+':'+String(ss).padStart(2,'0');
        let activeTitle='';
        phases.forEach(p=>{const s=Number(p.dataset.start||0),e=Number(p.dataset.end||90),state=p.querySelector('.lesson-phase-state'); const done=elapsedMin>=e,active=elapsedMin>=s&&elapsedMin<e; p.classList.toggle('active',active);p.classList.toggle('done',done);state.textContent=done?'hotovo':active?'teď':'čeká';if(active)activeTitle=p.querySelector('strong')?.textContent||'';});
        label.textContent=remaining<=0?'hodina dokončena':(running?(activeTitle||'probíhá'):'pozastaveno');
      };
      const tick=()=>{if(remaining<=0){clearInterval(timer);timer=null;running=false;pauseBtn.disabled=true;render();return;}remaining--;render();};
      startBtn.addEventListener('click',()=>{if(timer)return;running=true;startBtn.textContent='Běží';startBtn.disabled=true;pauseBtn.disabled=false;timer=setInterval(tick,1000);render();});
      pauseBtn.addEventListener('click',()=>{if(timer){clearInterval(timer);timer=null;running=false;pauseBtn.textContent='Pokračovat';startBtn.disabled=true;}else if(remaining>0){running=true;pauseBtn.textContent='Pauza';timer=setInterval(tick,1000);}render();});
      resetBtn.addEventListener('click',()=>{if(timer)clearInterval(timer);timer=null;running=false;remaining=90*60;startBtn.disabled=false;startBtn.textContent='Spustit hodinu';pauseBtn.disabled=true;pauseBtn.textContent='Pauza';render();});
      root.querySelector('[data-lesson-fullscreen]')?.addEventListener('click',()=>{if(!document.fullscreenElement){root.requestFullscreen?.();}else{document.exitFullscreen?.();}});
      const deck=root.querySelector('[data-v42-teacher-deck]'), deckSlides=deck?[...deck.querySelectorAll('[data-v42-deck-slide]')]:[]; let deckIndex=0;
      const deckRender=()=>{if(!deck)return;deckSlides.forEach((sl,i)=>{sl.hidden=i!==deckIndex;sl.classList.toggle('active',i===deckIndex);});const c=deck.querySelector('[data-v42-deck-count]');if(c)c.textContent=(deckIndex+1)+' / '+deckSlides.length;};
      root.querySelector('[data-v42-teacher-deck-open]')?.addEventListener('click',()=>{if(!deck)return;deck.hidden=false;deckIndex=0;deckRender();deck.querySelector('[data-v42-deck-next]')?.focus();});
      deck?.querySelector('[data-v42-teacher-deck-close]')?.addEventListener('click',()=>{deck.hidden=true;});
      deck?.querySelector('[data-v42-deck-prev]')?.addEventListener('click',()=>{deckIndex=(deckIndex-1+deckSlides.length)%deckSlides.length;deckRender();});
      deck?.querySelector('[data-v42-deck-next]')?.addEventListener('click',()=>{deckIndex=(deckIndex+1)%deckSlides.length;deckRender();});
      deck?.querySelector('[data-v42-deck-full]')?.addEventListener('click',()=>{if(!document.fullscreenElement){deck.requestFullscreen?.();}else{document.exitFullscreen?.();}});
      document.addEventListener('keydown',(ev)=>{if(!deck||deck.hidden)return;if(ev.key==='Escape'){deck.hidden=true;return;}if(ev.key==='ArrowRight'||ev.key==='PageDown'){ev.preventDefault();deckIndex=(deckIndex+1)%deckSlides.length;deckRender();}if(ev.key==='ArrowLeft'||ev.key==='PageUp'){ev.preventDefault();deckIndex=(deckIndex-1+deckSlides.length)%deckSlides.length;deckRender();}});
      render();
    })();
    </script>
    <?php
}
