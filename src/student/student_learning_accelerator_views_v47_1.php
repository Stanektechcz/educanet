<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v471_render_accelerator_teaser(string $classId,string $studentKey,array $module,array $tourMap): void
{
    if($studentKey==='')return;$s=v471_session_snapshot($classId,$studentKey,$module,$tourMap);$f=$s['focus'];if(!$f)return;
    ?>
    <section class="coach-side-card accelerator-teaser"><div class="eyebrow">Adaptive Study Loop · v47.1</div><h3><?=e(v471_stage_label((string)$f['stage']))?></h3><p><strong><?=edu_cs((string)$f['title'])?></strong><br><?=e(v471_stage_copy((string)$f['stage']))?></p><div class="accelerator-mini-meta"><span><?=e(tr('{count}× retrieval streak',['count'=>(int)$f['streak']]))?></span><span><?=e(tr('{count} chyb',['count'=>(int)$f['incorrect']]))?></span></div><a class="coach-card-link" href="<?=e(module_url('study_loop',['topic'=>(string)$f['topic']]))?>"><?=e(tr('Spustit 8min adaptivní cyklus →'))?></a></section>
    <?php
}

function v471_render_study_loop(string $classId,array $module,array $tourMap,string $flash=''): void
{
    $studentKey=adaptive_student_key($classId);if($studentKey==='')redirect_to('?view=dashboard');
    $requested=isset($_GET['topic'])&&is_string($_GET['topic'])?(string)$_GET['topic']:'';$s=v471_session_snapshot($classId,$studentKey,$module,$tourMap,$requested);$f=$s['focus'];
    render_header(tr('Adaptive Study Loop'),$module);
    ?>
    <?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <section class="accelerator-shell">
      <header class="coach-hero accelerator-hero"><div><div class="eyebrow">Adaptive Study Loop · v47.1</div><h1><?=e(tr('Nejdřív zkus. Potom dostaň přesně tolik podpory, kolik potřebuješ.'))?></h1><p><?=e(tr('Mikrodiagnostika → generation-first → postupně ubíraná opora → samostatný check. Bez nové známky, bez trestu za chybu.'))?></p></div><div class="coach-hero-score"><strong><?= $f?e(v471_stage_label((string)$f['stage'])):'—' ?></strong><span><?=e(tr('aktuální úroveň podpory'))?></span><small><?=e(tr('adaptace podle evidence, ne „learning style“'))?></small></div></header>
      <?php if(!$f):?><div class="coach-zero"><strong><?=e(tr('Zatím nemáme téma pro adaptivní cyklus.'))?></strong><p><?=e(tr('Začni první lekcí nebo diagnostikou.'))?></p><a class="btn primary" href="?view=course"><?=e(tr('Otevřít kurz →'))?></a></div><?php else: ?>
      <div class="accelerator-layout">
        <main class="accelerator-main">
          <section class="accelerator-card focus-card"><div class="accelerator-step-tag">1 · mikrodiagnostika</div><div class="accelerator-title-row"><div><h2><?=e((string)$f['title'])?></h2><p><?=e((string)$f['mental'])?></p></div><span class="guidance-badge stage-<?=e((string)$f['stage'])?>"><?=e(v471_stage_label((string)$f['stage']))?></span></div>
            <?php $check=(array)$s['check']; if(!empty($check['q'])&&is_array($check['options']??null)): ?>
            <form method="post" class="accelerator-probe"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="coach_probe_submit"><input type="hidden" name="topic" value="<?=e((string)$f['topic'])?>"><h3><?=e((string)$check['q'])?></h3><div class="accelerator-options"><?php foreach((array)$check['options'] as $i=>$opt):?><label><input type="radio" name="answer" value="<?=$i?>" required><span><?=e((string)$opt)?></span></label><?php endforeach;?></div><fieldset><legend><?=e(tr('Jak moc si věříš?'))?></legend><label><input type="radio" name="confidence" value="1"> <?=e(tr('Spíš tipuji'))?></label><label><input type="radio" name="confidence" value="2" checked> <?=e(tr('Docela si věřím'))?></label><label><input type="radio" name="confidence" value="3"> <?=e(tr('Jsem si jistý/á'))?></label></fieldset><button class="btn primary" type="submit"><?=e(tr('Ověřit a upravit podporu →'))?></button></form>
            <?php else:?><p><?=e(tr('Pro toto téma zatím není mikrocheck. Pokračuj generation-first krokem níže.'))?></p><?php endif;?>
            <?php $probe=is_array($_SESSION['v471_probe_result']??null)?$_SESSION['v471_probe_result']:null;if($probe&&(string)($probe['topic']??'')===(string)$f['topic']):?><div class="probe-result <?=$probe['correct']?'ok':'needs-work'?>"><strong><?=e($probe['correct']?tr('✓ Vybavení funguje'):tr('↻ Tady má smysl opora navíc'))?></strong><p><?=e((string)$probe['why'])?></p></div><?php endif;?>
          </section>

          <?php if($probe&&(string)($probe['topic']??'')===(string)$f['topic']&&!empty($probe)&&empty($probe['correct']))v472_render_corrective_cycle($classId,$studentKey,(string)$f['topic'],$probe,$module,$tourMap); ?>

          <section class="accelerator-card"><div class="accelerator-step-tag">2 · generation-first</div><h2><?=e(tr('Než cokoli odkryješ, napiš vlastní verzi.'))?></h2><p><?=e(tr('Jedna až tři věty. Text zůstává jen v tomto prohlížeči; neposílá se učiteli ani do známek.'))?></p><textarea rows="4" data-generation-note data-generation-key="<?=e($classId.'|'.$studentKey.'|'.(string)$f['topic'])?>" placeholder="<?=e(tr('Myslím, že princip funguje tak, že…'))?>"></textarea><div class="generation-actions"><button class="btn primary" type="button" data-reveal-scaffold><?=e(tr('Hotovo — ukaž mi oporu'))?></button><small><?=e(tr('Nejdřív vytvořit odpověď, až potom kontrolovat.'))?></small></div></section>

          <section class="accelerator-card scaffold-card" data-scaffold hidden><div class="accelerator-step-tag">3 · <?=e(v471_stage_label((string)$f['stage']))?></div><h2><?=e(tr('Model postupu se automaticky zkracuje podle důkazů.'))?></h2><p><?=e(v471_stage_copy((string)$f['stage']))?></p>
            <ol class="faded-steps"><?php foreach((array)$f['steps'] as $i=>$step): $visible=array_key_exists($i,(array)$s['visible_steps']); ?><li class="<?=$visible?'visible':'faded'?>"><?php if($visible):?><span><?=e((string)$step)?></span><?php else:?><button type="button" data-reveal-step><span><?=e(tr('Doplň krok {n} z paměti',['n'=>$i+1]))?></span><b><?=e(tr('odkrýt'))?></b></button><span class="faded-answer" hidden><?=e((string)$step)?></span><?php endif;?></li><?php endforeach;?></ol>
            <?php if(!empty($f['mistakes'])):?><div class="contrast-box"><strong><?=e(tr('Kontrast: na co si dát pozor'))?></strong><p><?=e(tr('Častá slepá ulička: {mistake}',['mistake'=>(string)$f['mistakes'][0]]))?></p><p><?=e(tr('Správný mentální model: {model}',['model'=>(string)$f['mental']]))?></p></div><?php endif;?>
          </section>

          <section class="accelerator-card"><div class="accelerator-step-tag">4 · transfer</div><h2><?=e(tr('Použij princip bez kopírování příkladu.'))?></h2><p><?=e(tr('Zkus si vlastními slovy odpovědět:'))?> <strong><?=e(tr('Kde by tento princip přestal fungovat nebo co by se muselo změnit, aby výsledek byl jiný?'))?></strong></p><textarea rows="3" data-transfer-note placeholder="<?=e(tr('Nová situace / hranice principu…'))?>"></textarea><?php if(!empty($s['mastery_ready'])):?><div class="mastery-ready"><strong><?=e(tr('Checkpoint připraven'))?></strong><span><?=e(tr('Retrieval je stabilní a podpora může jít téměř pryč. Další krok je samostatná aplikace.'))?></span><a class="btn secondary small" href="<?=e(module_url('kb_lesson',['topic'=>(string)$f['topic'],'reference'=>1]))?>"><?=e(tr('Samostatný knowledge check →'))?></a></div><?php endif;?></section>

          <section class="accelerator-card comfort-feedback"><div><div class="accelerator-step-tag">5 · kalibrace obtížnosti</div><h2><?=e(tr('Jaký byl tento cyklus?'))?></h2><p><?=e(tr('Nemění známku ani mastery. Pouze upraví množství opory příště.'))?></p></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="coach_difficulty_feedback"><input type="hidden" name="topic" value="<?=e((string)$f['topic'])?>"><button name="rating" value="too_easy" type="submit"><?=e(tr('Příliš lehké'))?></button><button name="rating" value="right" type="submit"><?=e(tr('Akorát'))?></button><button name="rating" value="too_hard" type="submit"><?=e(tr('Příliš těžké'))?></button></form></section>
        </main>
        <aside class="accelerator-side">
          <section class="coach-side-card"><div class="eyebrow"><?=e(tr('Proč právě toto?'))?></div><h3><?=e((string)$f['title'])?></h3><ul class="evidence-list"><li><?=e(tr('Retrieval streak:'))?> <b><?= (int)$f['streak'] ?></b></li><li><?=e(tr('Zachycené chyby:'))?> <b><?= (int)$f['incorrect'] ?></b></li><li><?=e(tr('Riziko zapomenutí:'))?> <b><?= max(0,(int)$f['risk_score']) ?></b></li><li><?=e(tr('Podpora:'))?> <b><?=e(v471_stage_label((string)$f['stage']))?></b></li></ul></section>
          <section class="coach-side-card"><div class="eyebrow"><?=e(tr('Interleaving'))?></div><h3><?=e(tr('Střídej typ problému, ne jen stránku.'))?></h3><p><?=e(tr('Po tomto tématu přejdi na jiný blok. Návrat později je užitečnější než pět téměř stejných pokusů za sebou.'))?></p><?php foreach((array)$s['mix'] as $mix):?><a class="interleave-link <?=((string)$mix['topic']===(string)$f['topic'])?'active':''?>" href="<?=e(module_url('study_loop',['topic'=>(string)$mix['topic']]))?>"><span><?=e((string)$mix['title'])?></span><small><?=e(v471_stage_label((string)$mix['stage']))?></small></a><?php endforeach;?></section>
          <section class="coach-side-card"><div class="eyebrow"><?=e(tr('Pohodlný rytmus'))?></div><h3><?=e(tr('20–25 min fokus → krátká pauza'))?></h3><p><?=e(tr('Timer připomene pauzu, ale nic neblokuje. Při 10min režimu žádnou povinnou pauzu nevynucujeme.'))?></p><button class="coach-timer-launch" type="button" data-coach-timer-start="20"><?=e(tr('▶ 20min fokus'))?></button></section>
        </aside>
      </div>
      <?php endif;?>
    </section>
    <?php render_footer();
}
