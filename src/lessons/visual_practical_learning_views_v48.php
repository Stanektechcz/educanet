<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v48_answer_feedback(?array $row,array $contract,string $whyKey='why'): string
{
    if(!is_array($row))return '';
    $correct=!empty($row['correct']);
    $meta=is_array($row['meta']??null)?$row['meta']:[];
    $answer=(int)($meta['answer']??-1);
    $correctIndex=(int)($contract['correct']??0);
    $options=(array)($contract['options']??[]);
    $selected=isset($options[$answer])?(string)$options[$answer]:'';
    $correctText=isset($options[$correctIndex])?(string)$options[$correctIndex]:'';
    $why=(string)($contract[$whyKey]??$contract['reveal']??'');
    $statusText=$correct?tr('✓ Predikce sedí'):tr('↻ Tady se vyplatí upravit model');
    return '<div class="v48-feedback '.($correct?'ok':'needs-work').'"><strong>'.e($statusText).'</strong>'.($selected!==''?'<span>'.tr_html('Tvoje volba: {hodnota}',['hodnota'=>edu_cs($selected)]).'</span>':'').(!$correct&&$correctText!==''?'<span>'.tr_html('Funkčnější volba: {hodnota}',['hodnota'=>edu_cs($correctText)]).'</span>':'').($why!==''?'<p'.edu_content_lang_attr().'>'.e($why).'</p>':'').'</div>';
}

function v48_render_teacher_banner(array $orchestration): void
{
    $active=!empty($orchestration['active']);
    ?>
    <aside class="v48-live-banner <?=$active?'active':''?>" data-v48-live data-v48-lesson="<?= (int)($orchestration['lesson_number']??0) ?>" <?= $active?'':'hidden' ?>>
      <span><?=e(tr('ŽIVÁ HODINA'))?></span><strong data-v48-live-phase<?=edu_content_lang_attr()?>><?=e((string)($orchestration['phase']??'predict'))?></strong><p data-v48-live-prompt<?=edu_content_lang_attr()?>><?=e((string)($orchestration['prompt']??''))?></p><small><?=e(tr('Odpověz nejdřív sám/sama. Učitel vidí pouze agregované počty.'))?></small>
    </aside>
    <?php
}

function v48_render_student_lab(string $classId,array $lesson,array $module,string $studentKey): void
{
    $spec=v48_lab_spec($classId,$lesson,$module);$state=v48_student_state($classId,$studentKey,(int)$lesson['number']);$orch=v48_orchestration_state($classId,(int)$lesson['number']);
    $pred=(array)$spec['prediction'];$compare=(array)$spec['compare'];$debug=(array)$spec['debug'];$build=(array)$spec['build'];
    $predRow=is_array($state['prediction']??null)?$state['prediction']:null;$compareRow=is_array($state['compare']??null)?$state['compare']:null;$debugRow=is_array($state['debug']??null)?$state['debug']:null;$buildRow=is_array($state['build']??null)?$state['build']:null;$transferRow=is_array($state['transfer']??null)?$state['transfer']:null;
    ?>
    <section class="v48-shell" data-v48-lab data-v48-state-url="<?=e(module_url('v48_state',['lesson'=>(int)$lesson['number']]))?>">
      <?php v48_render_teacher_banner($orch); ?>
      <header class="v48-hero"><div><div class="eyebrow">Visual & Practical Learning Engine · v48</div><h1<?=edu_content_lang_attr()?>><?=e((string)$spec['lesson_title'])?></h1><p<?=edu_content_lang_attr()?>><?=e((string)$spec['goal'])?></p><div class="v48-badges"><span><?=e(tr('formativní'))?></span><span><?=e(tr('bez známky'))?></span><span><?=e((string)$spec['family'])?></span></div></div><div class="v48-journey" aria-label="<?=e(tr('Výukový cyklus'))?>"><span class="<?= $predRow?'done':'' ?>"><?=e(tr('1 Predikce'))?></span><span class="<?= $compareRow?'done':'' ?>"><?=e(tr('2 Kontrast'))?></span><span class="<?= $debugRow?'done':'' ?>"><?=e(tr('3 Debug'))?></span><span><?=e(tr('4 Sandbox'))?></span><span class="<?= $buildRow?'done':'' ?>"><?=e(tr('5 Build'))?></span><span class="<?= $transferRow?'done':'' ?>"><?=e(tr('6 Transfer'))?></span></div></header>

      <section class="v48-grid v48-first-grid">
        <article class="v48-card v48-predict" id="v48-predict"><div class="v48-card-kicker">1 · Prediction Before Reveal</div><h2><?=e(tr('Nejdřív předpověz. Až potom se dívej na model.'))?></h2><p<?=edu_content_lang_attr()?>><?=e((string)$pred['question'])?></p>
          <?php if(!$predRow): ?><form method="post" class="v48-option-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_prediction"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><?php foreach((array)$pred['options'] as $i=>$option):?><button type="submit" name="answer" value="<?=$i?>"><span><?=chr(65+$i)?></span><strong><?=edu_cs((string)$option)?></strong></button><?php endforeach;?></form>
          <?php else: echo v48_answer_feedback($predRow,$pred,'reveal'); ?><a class="text-link" href="#v48-visual"><?=e(tr('Pokračovat k vizuálnímu modelu ↓'))?></a><?php endif; ?>
        </article>
        <article class="v48-card v48-concept-card"><div class="v48-card-kicker"><?=e(tr('Mentální model'))?></div><h2><?=e(tr('Co se skutečně rozhoduje?'))?></h2><p<?=edu_content_lang_attr()?>><?=e((string)$spec['princip'])?></p><div class="v48-layer-stack"<?=edu_content_lang_attr()?>><?php foreach((array)$spec['sandbox']['layers'] as $i=>$layer):?><div><i><?=$i+1?></i><span><strong><?=e((string)($layer['label']??''))?></strong><small><?=e((string)($layer['text']??''))?></small></span></div><?php endforeach;?></div></article>
      </section>

      <section class="v48-card v48-contrast" id="v48-contrast"><div class="v48-card-kicker">2 · Compare & Contrast Lab</div><div class="v48-section-head"><div><h2><?=e(tr('Nehledej jen správnou variantu. Najdi rozhodující rozdíl.'))?></h2><p<?=edu_content_lang_attr()?>><?=e((string)$compare['question'])?></p></div></div><div class="v48-contrast-pair"<?=edu_content_lang_attr()?>><article class="bad"><span><?=e(tr('Slepá ulička'))?></span><strong><?=e((string)$compare['bad'])?></strong></article><article class="good"><span><?=e(tr('Funkční model'))?></span><strong><?=e((string)$compare['good'])?></strong></article></div>
        <?php if(!$compareRow):?><form method="post" class="v48-option-form compact"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_compare"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><?php foreach((array)$compare['options'] as $i=>$option):?><button type="submit" name="answer" value="<?=$i?>"><span><?=chr(65+$i)?></span><strong><?=edu_cs((string)$option)?></strong></button><?php endforeach;?></form><?php else: echo v48_answer_feedback($compareRow,$compare); endif;?>
      </section>

      <section class="v48-grid">
        <article class="v48-card v48-debug" id="v48-debug"><div class="v48-card-kicker">3 · Debugging Mission</div><h2<?=edu_content_lang_attr()?>><?=e((string)$debug['title'])?></h2><div class="v48-symptom"><span>SYMPTOM</span><p<?=edu_content_lang_attr()?>><?=e((string)$debug['symptom'])?></p></div><p<?=edu_content_lang_attr()?>><strong><?=e((string)$debug['question'])?></strong></p>
          <?php if(!$debugRow):?><form method="post" class="v48-option-form vertical"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_debug"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><?php foreach((array)$debug['options'] as $i=>$option):?><button type="submit" name="answer" value="<?=$i?>"><span><?=chr(65+$i)?></span><strong><?=edu_cs((string)$option)?></strong></button><?php endforeach;?></form><?php else: echo v48_answer_feedback($debugRow,$debug);?><div class="v48-fault-note"><span>Fault injection</span><strong><?=tr_html('Ve stresovém scénáři je nejslabší signál: {metrika}',['metrika'=>e((string)$debug['weak_metric_label'])])?></strong><p><?=e(tr('V sandboxu aplikuj scénář „podmínky se zhorší“ a ověř, zda to skutečně vidíš v metrikách.'))?></p></div><?php endif;?>
        </article>
        <article class="v48-card"><div class="v48-card-kicker"><?=e(tr('Diagnostický algoritmus'))?></div><h2><?=e(tr('Postup místo náhodných oprav'))?></h2><ol class="v48-debug-timeline"><?php foreach((array)$debug['timeline'] as $i=>$step):?><li><i><?=$i+1?></i><span<?=edu_content_lang_attr()?>><?=e((string)$step)?></span></li><?php endforeach;?></ol></article>
      </section>

      <section class="v48-visual-wrap" id="v48-visual"><div class="v48-section-head"><div><div class="v48-card-kicker">4 · Visual Concept Studio + Sandbox</div><h2><?=e(tr('Manipuluj parametry, dělej co-když scénáře a porovnávej A/B.'))?></h2><p><?=e(tr('Měň ideálně jednu proměnnou. Před každým zásahem si řekni očekávaný směr změny.'))?></p></div><span><?=e(tr('bez penalizace · bezpečný experiment'))?></span></div><?php v45_render_visual_simulation($classId,$lesson,$module,$studentKey); ?></section>

      <?php if(function_exists('v481_render_deep_lab')) v481_render_deep_lab($classId,$lesson,$module,$studentKey); ?>

      <section class="v48-card v48-build" id="v48-build"><div class="v48-card-kicker">5 · Draw / Build Mode</div><h2<?=edu_content_lang_attr()?>><?=e((string)$build['prompt'])?></h2><p><?=e(tr('Klikej na bloky ve správném pořadí. Neskládáš definici, ale vztahy v mentálním modelu.'))?></p>
        <?php if(!$buildRow):?><form method="post" data-v48-build-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_build"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><div class="v48-build-bank"><?php foreach((array)$build['tokens'] as $token):?><button type="button" data-v48-build-token="<?=e((string)$token)?>"<?=edu_content_lang_attr()?>><?=e((string)$token)?></button><?php endforeach;?></div><div class="v48-build-canvas" data-v48-build-canvas><span><?=e(tr('Sem sestav model…'))?></span></div><div data-v48-build-inputs></div><div class="button-row"><button type="button" class="btn secondary" data-v48-build-undo><?=e(tr('← Zpět'))?></button><button type="reset" class="btn secondary" data-v48-build-reset><?=e(tr('Reset'))?></button><button type="submit" class="btn primary" data-v48-build-submit disabled><?=e(tr('Ověřit model'))?></button></div></form><?php else:?><div class="v48-feedback <?=!empty($buildRow['correct'])?'ok':'needs-work'?>"><strong><?=e(!empty($buildRow['correct'])?tr('✓ Model drží pohromadě'):tr('↻ Model potřebuje ještě jednu opravu'))?></strong><?php if(empty($buildRow['correct'])):?><p><?=tr_html('Referenční tok: {tok}',['tok'=>edu_cs(implode(' → ',(array)$build['expected']))])?></p><?php else:?><p><?=e(tr('Teď zkus stejnou strukturu použít v jiné situaci.'))?></p><?php endif;?></div><?php endif;?>
      </section>

      <section class="v48-card v48-transfer" id="v48-transfer"><div class="v48-card-kicker">6 · Transfer</div><h2><?=e(tr('Použij princip v nové situaci.'))?></h2><p<?=edu_content_lang_attr()?>><?=e((string)$spec['transfer']['prompt'])?></p><?php if(!$transferRow):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_transfer"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><textarea name="reflection" rows="4" minlength="24" maxlength="1400" required placeholder="<?=e(tr('Co se změnilo, co zůstává stejné a jak bys to ověřil/a?'))?>"></textarea><details><summary><?=e(tr('Kontrolní kritéria'))?></summary><ul><?php foreach((array)$spec['transfer']['criteria'] as $criterion):?><li<?=edu_content_lang_attr()?>><?=e((string)$criterion)?></li><?php endforeach;?></ul></details><button class="btn primary" type="submit"><?=e(tr('Uložit formativní transfer'))?></button></form><?php else:?><div class="v48-feedback <?=!empty($transferRow['correct'])?'ok':'needs-work'?>"><strong><?=e(!empty($transferRow['correct'])?tr('✓ Transfer zachycen'):tr('↻ Ještě doplň důkaz'))?></strong><p><?=e((string)($transferRow['meta']['reflection']??''))?></p></div><?php endif;?><small class="v48-formative-note"><?=e(tr('Tato aktivita nemění známku, XP ani Mastery. Je to bezpečný prostor pro přemýšlení a praktický přenos.'))?></small></section>
    </section>
    <?php
}

function v48_render_student_lab_page(string $classId,array $lesson,array $module): void
{
    render_header('Visual Lab · '.(string)($lesson['title']??tr('Lekce')),$module,true);
    v48_render_student_lab($classId,$lesson,$module,adaptive_student_key($classId));
    echo '<div class="button-row"><a class="btn secondary" href="'.e(module_url('course_lesson',['lesson'=>(string)($lesson['id']??'')])).'">'.e(tr('← Zpět do lekce')).'</a><a class="btn secondary" href="'.e(module_url('cognitive_lab',['lesson'=>(int)($lesson['number']??1)])).'">Cognitive Lab</a></div>';
    render_footer();
}

function v48_render_teacher_orchestration(string $classId,array $lesson,array $module): void
{
    $spec=v48_lab_spec($classId,$lesson,$module);$summary=v48_teacher_summary($classId,(int)$lesson['number']);$state=(array)$summary['state'];$active=!empty($state['active']);$phase=(string)($state['phase']??'predict');$phases=(array)$spec['teacher']['phases'];
    ?>
    <section class="teacher-panel v48-teacher-panel" data-v48-teacher data-v48-poll-url="teacher.php?tab=teach&amp;class=<?=e($classId)?>&amp;lesson=<?= (int)$lesson['number'] ?>&amp;v48_state=1">
      <div class="teacher-panel-head"><div><span>Visual & Practical Learning Engine v48</span><h2>Live Teacher Orchestration</h2><p>Řiď jednu společnou sekvenci: predikce → diskuse → kontrast → debug → sandbox → build → transfer.</p></div><span class="v48-live-status <?=$active?'active':''?>" data-v48-teacher-status><?=$active?'živě':'připraveno'?></span></div>
      <div class="v48-teacher-stats" data-v48-teacher-stats><?php foreach(['prediction'=>'Predikce','compare'=>'Kontrast','debug'=>'Debug','build'=>'Build','transfer'=>'Transfer'] as $key=>$label):?><div><span><?=$label?></span><strong data-v48-count="<?=$key?>"><?= (int)($summary['counts'][$key]??0) ?> / <?= (int)$summary['students'] ?></strong></div><?php endforeach;?></div>
      <div class="v48-phase-grid"><?php foreach($phases as $key=>$row):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_orchestrate"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><input type="hidden" name="mode" value="<?=$active?'phase':'start'?>"><input type="hidden" name="phase" value="<?=e((string)$key)?>"><button type="submit" class="<?= $active&&$phase===$key?'active':'' ?>"><b><?=e((string)$row['label'])?></b><small><?=e((string)$row['prompt'])?></small></button></form><?php endforeach;?></div>
      <div class="v48-teacher-prompt"><span>Aktuální instrukce pro třídu</span><strong data-v48-current-phase><?=e((string)($phases[$phase]['label']??'Predikce'))?></strong><p data-v48-current-prompt><?=e((string)($state['prompt']??($phases[$phase]['prompt']??'')))?></p></div>
      <div class="button-row"><a class="btn secondary" target="_blank" href="<?=e(module_url('visual_lab',['lesson'=>(int)$lesson['number']]))?>">Otevřít studentský Visual Lab ↗</a><?php if($active):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v48_orchestrate"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><input type="hidden" name="mode" value="stop"><button class="btn secondary" type="submit">Ukončit živou sekvenci</button></form><?php endif;?></div>
      <small class="muted">Živý režim nepřiděluje body. Na projektoru se zobrazují jen agregované počty, nikoli jména studentů.</small>
    </section>
    <?php
}
