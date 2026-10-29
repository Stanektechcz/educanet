<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v481_task_name(array $task): string
{
    return match ((string)($task['type'] ?? '')) {
        'sequence'=>tr('Seřaď postup'),'multi'=>tr('Vyber všechny správné'),'pair'=>tr('Propoj dvojice'),'number'=>tr('Spočítej hodnotu'),'command'=>tr('Napiš příkaz'),'matrix'=>tr('Nastav matici toků'),default=>tr('Rozhodni podle evidence'),
    };
}

function v481_render_scene(array $spec, string $mode): void
{
    $scene=(array)($spec['scene']??[]);$nodes=(array)($scene['nodes']??[]);$edges=(array)($scene['edges']??[]);$layout=preg_replace('/[^a-z0-9_-]/i','',(string)($scene['layout']??'flow'));
    ?>
    <div class="v481-scene v481-layout-<?=e((string)$layout)?>">
      <div class="v481-scene-head"<?=edu_content_lang_attr()?>><div><span><?=e(tr('Interaktivní model'))?></span><strong><?=e((string)($scene['caption']??''))?></strong></div><small><?=e((string)($spec['archetype']??''))?></small></div>
      <div class="v481-node-grid"<?=edu_content_lang_attr()?>>
        <?php foreach($nodes as $i=>$node): if(!is_array($node))continue; ?>
          <article class="v481-node" data-v481-node="<?=e((string)($node['id']??$i))?>"<?=edu_content_lang_attr()?>><i><?=($i+1)?></i><div><strong><?=e((string)($node['label']??''))?></strong><?php if($mode!=='challenge'&&!empty($node['meta'])):?><small><?=e((string)$node['meta'])?></small><?php endif;?></div></article>
        <?php endforeach; ?>
      </div>
      <?php if($edges):?><div class="v481-edge-list"<?=edu_content_lang_attr()?>><?php foreach($edges as $edge):if(!is_array($edge))continue;?><span><b><?=e((string)($edge['from']??''))?></b> → <b><?=e((string)($edge['to']??''))?></b><?php if(!empty($edge['label'])):?><em><?=e((string)$edge['label'])?></em><?php endif;?></span><?php endforeach;?></div><?php endif;?>
      <?php if(trim((string)($scene['artifact']??''))!==''):?><pre class="v481-artifact"><code><?=e((string)$scene['artifact'])?></code></pre><?php endif;?>
    </div>
    <?php
}

function v481_render_fault_bank(array $spec, string $mode): void
{
    $faults=(array)($spec['faults']??[]);if(!$faults)return;
    ?>
    <section class="v481-fault-bank" data-v481-fault-bank>
      <div class="v481-subhead"><div><span>Fault bank</span><h3><?=e(tr('Stejný povrch, jiná příčina'))?></h3><p><?=e(tr('Vyber závadu. Nejdřív pracuj jen se symptomem; řešení odhal až po vlastní hypotéze.'))?></p></div><strong><?=e(trn(['one'=>'{n} scénář','few'=>'{n} scénáře','other'=>'{n} scénářů'],count($faults)))?></strong></div>
      <div class="v481-fault-tabs" role="tablist"><?php foreach($faults as $i=>$fault):?><button type="button" class="<?=$i===0?'active':''?>" data-v481-fault-tab="<?=$i?>"><?=edu_cs((string)($fault['title']??tr('Fault {n}',['n'=>$i+1])))?></button><?php endforeach;?></div>
      <div class="v481-fault-scenes"><?php foreach($faults as $i=>$fault): if(!is_array($fault))continue;?><article data-v481-fault-panel="<?=$i?>" <?=$i===0?'':'hidden'?>>
        <div class="v481-symptom"><span>Symptom</span><strong<?=edu_content_lang_attr()?>><?=e((string)($fault['symptom']??''))?></strong></div>
        <?php if($mode==='guided'):?><div class="v481-guided-hint"<?=edu_content_lang_attr()?>><span>Guided hint</span><?=e((string)($fault['hint']??''))?></div><?php elseif($mode==='practice'):?><details><summary><?=e(tr('Potřebuji malý hint'))?></summary><p<?=edu_content_lang_attr()?>><?=e((string)($fault['hint']??''))?></p></details><?php endif;?>
        <button type="button" class="btn secondary" data-v481-fault-reveal><?=e(tr('Odhalit root cause až po hypotéze'))?></button>
        <div class="v481-fault-answer" data-v481-fault-answer hidden><div><span>Root cause</span><strong<?=edu_content_lang_attr()?>><?=e((string)($fault['root_cause']??''))?></strong></div><div><span>Evidence</span><p<?=edu_content_lang_attr()?>><?=e((string)($fault['evidence']??''))?></p></div><div><span><?=e(tr('Minimální fix'))?></span><p<?=edu_content_lang_attr()?>><?=e((string)($fault['minimal_fix']??''))?></p></div></div>
      </article><?php endforeach;?></div>
    </section>
    <?php
}

function v481_render_task(array $task, string $mode, ?array $latest): void
{
    $id=(string)($task['id']??'task');$type=(string)($task['type']??'choice');$result=is_array($latest)?($latest['results'][$id]??null):null;
    ?>
    <fieldset class="v481-task v481-task-<?=e($type)?>" data-v481-task="<?=e($id)?>">
      <legend><span><?=e(v481_task_name($task))?></span><strong<?=edu_content_lang_attr()?>><?=e((string)($task['prompt']??''))?></strong></legend>
      <?php if($type==='choice'):?>
        <div class="v481-choice-grid"<?=edu_content_lang_attr()?>><?php foreach((array)($task['options']??[]) as $option):?><label><input type="radio" name="answers[<?=e($id)?>]" value="<?=e((string)$option)?>" required><span><?=e((string)$option)?></span></label><?php endforeach;?></div>
      <?php elseif($type==='multi'):?>
        <div class="v481-choice-grid"<?=edu_content_lang_attr()?>><?php foreach((array)($task['options']??[]) as $option):?><label><input type="checkbox" name="answers[<?=e($id)?>][]" value="<?=e((string)$option)?>"><span><?=e((string)$option)?></span></label><?php endforeach;?></div>
      <?php elseif($type==='sequence'):?>
        <div class="v481-sequence" data-v481-sequence data-task-id="<?=e($id)?>"><div class="v481-token-bank"<?=edu_content_lang_attr()?>><?php foreach((array)($task['items']??[]) as $item):?><button type="button" data-v481-sequence-token="<?=e((string)$item)?>"><?=e((string)$item)?></button><?php endforeach;?></div><div class="v481-sequence-canvas" data-v481-sequence-canvas><span><?=e(tr('Poskládej pořadí…'))?></span></div><div data-v481-sequence-inputs></div><button type="button" class="btn secondary" data-v481-sequence-reset><?=e(tr('Reset pořadí'))?></button></div>
      <?php elseif($type==='pair'):?>
        <div class="v481-pair-grid"><?php foreach((array)($task['left']??[]) as $left):?><label><span<?=edu_content_lang_attr()?>><?=e((string)$left)?></span><select name="answers[<?=e($id)?>][<?=e((string)$left)?>]" required><option value=""><?=e(tr('— vyber —'))?></option><?php foreach((array)($task['right']??[]) as $right):?><option value="<?=e((string)$right)?>"<?=edu_content_lang_attr()?>><?=e((string)$right)?></option><?php endforeach;?></select></label><?php endforeach;?></div>
      <?php elseif($type==='number'):?>
        <label class="v481-number"><input type="number" step="any" min="<?=e((string)($task['min']??''))?>" max="<?=e((string)($task['max']??''))?>" name="answers[<?=e($id)?>]" required><span<?=edu_content_lang_attr()?>><?=e((string)($task['suffix']??''))?></span></label>
      <?php elseif($type==='command'):?>
        <label class="v481-command"><code>$</code><input type="text" name="answers[<?=e($id)?>]" autocomplete="off" spellcheck="false" placeholder="<?=e((string)($task['placeholder']??tr('příkaz')))?>" required<?=edu_content_lang_attr()?>></label>
      <?php elseif($type==='matrix'):?>
        <div class="v481-matrix" style="--v481-cols:<?=max(1,count((array)($task['columns']??[])))?>"<?=edu_content_lang_attr()?>><div></div><?php foreach((array)($task['columns']??[]) as $col):?><strong><?=e((string)$col)?></strong><?php endforeach;?><?php foreach((array)($task['rows']??[]) as $row):?><b><?=e((string)$row)?></b><?php foreach((array)($task['columns']??[]) as $col):$value=(string)$row.'|'.(string)$col;?><label title="<?=e($value)?>"><input type="checkbox" name="answers[<?=e($id)?>][]" value="<?=e($value)?>"><span>✓</span></label><?php endforeach;?><?php endforeach;?></div>
      <?php endif;?>
      <?php if($mode==='guided'):?><small class="v481-task-support"><?=e(tr('Guided: nejdřív si pojmenuj, jakou vrstvu nebo pravidlo tímto krokem ověřuješ.'))?></small><?php endif;?>
      <?php if($result!==null):?><div class="v481-prior-result <?=$result?'ok':'needs-work'?>"><strong><?=e($result?tr('✓ Minule správně'):tr('↻ Minule ještě ne'))?></strong><span<?=edu_content_lang_attr()?>><?=e((string)($task['why']??''))?></span></div><?php endif;?>
    </fieldset>
    <?php
}

function v481_render_deep_lab(string $classId, array $lesson, array $module, string $studentKey): void
{
    if(!v481_available_for($classId,$lesson))return;
    $spec=v481_3a_spec((int)$lesson['number']);if(!$spec)return;
    $mode=v481_mode($classId,$studentKey,$lesson,(string)($_GET['lab_mode']??''));$latest=v481_latest_attempt($classId,$studentKey,(int)$lesson['number']);$best=v481_student_best($classId,$studentKey,(int)$lesson['number']);
    $base=['view'=>'visual_lab','lesson'=>(int)$lesson['number']];
    ?>
    <section class="v481-deep-lab mode-<?=e($mode)?>" id="v481-deep" data-v481-deep-lab>
      <div class="v481-hero"><div><span class="v481-version">v48.1 · 3.A Deep Visual Lab</span><h2<?=edu_content_lang_attr()?>><?=e((string)$spec['title'])?></h2><p<?=edu_content_lang_attr()?>><?=e((string)$spec['mission'])?></p><div class="v481-mode-switch" aria-label="<?=e(tr('Úroveň podpory'))?>"><?php foreach(['guided','practice','challenge'] as $m):?><a class="<?=$mode===$m?'active':''?>" href="?<?=e(http_build_query($base+['lab_mode'=>$m]))?>#v481-deep"><?=e(v481_mode_label($m))?></a><?php endforeach;?></div></div><div class="v481-scorecard"><span><?=e(tr('Formativní praktická evidence'))?></span><?php if($best):?><strong><?= (int)$best['score'] ?> / <?= (int)$best['total'] ?></strong><small><?=e(!empty($best['completed'])?tr('nejlepší pokus dokončen'):tr('můžeš bezpečně opakovat'))?></small><?php else:?><strong><?=e(tr('0 bodů do známky'))?></strong><small><?=e(tr('chyby jsou očekávaná součást laboratoře'))?></small><?php endif;?></div></div>
      <div class="v481-mode-note"><strong><?=e(v481_mode_label($mode))?></strong><span<?=edu_content_lang_attr()?>><?=e((string)($spec['mode_notes'][$mode]??''))?></span></div>
      <?php v481_render_scene($spec,$mode); ?>
      <?php v481_render_fault_bank($spec,$mode); ?>
      <section class="v481-checkpoints"><div class="v481-subhead"><div><span><?=e(tr('Serverově validované checkpointy'))?></span><h3><?=e(tr('Proveď, ne jen přečti'))?></h3><p><?=e(tr('Checkpointy pracují s konkrétní situací této lekce. Pokus můžeš kdykoli zopakovat bez penalizace.'))?></p></div><strong><?=e(trn(['one'=>'{n} úloha','few'=>'{n} úlohy','other'=>'{n} úloh'],count((array)$spec['tasks'])))?></strong></div>
        <?php if($latest):?><div class="v481-attempt-summary <?=!empty($latest['completed'])?'ok':'needs-work'?>"><strong><?=tr_html('Poslední pokus: {vysledek}',['vysledek'=>(int)$latest['score'].' / '.(int)$latest['total']])?></strong><span><?=e(!empty($latest['reflection_ok'])?tr('Transferová reflexe zachycena.'):tr('Doplň ještě krátký transfer.'))?></span></div><?php endif;?>
        <form method="post" class="v481-lab-form" data-v481-lab-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v481_deep_submit"><input type="hidden" name="lesson_number" value="<?= (int)$lesson['number'] ?>"><input type="hidden" name="lab_mode" value="<?=e($mode)?>">
          <?php foreach((array)$spec['tasks'] as $task):if(is_array($task))v481_render_task($task,$mode,$latest);endforeach;?>
          <fieldset class="v481-task v481-transfer"><legend><span>Transfer</span><strong><?=e(tr('Přeneste stejný princip do nové situace.'))?></strong></legend><p<?=edu_content_lang_attr()?>><?=e((string)($spec['transfer']??''))?></p><textarea name="reflection" rows="4" minlength="24" maxlength="1400" required placeholder="<?=e(tr('Co bys ověřil/a, proč právě to a jaká evidence by potvrdila nebo vyvrátila hypotézu?'))?>"></textarea></fieldset>
          <div class="v481-success-criteria"><span>Definition of done</span><ul><?php foreach((array)($spec['success_criteria']??[]) as $criterion):?><li<?=edu_content_lang_attr()?>><?=e((string)$criterion)?></li><?php endforeach;?></ul></div>
          <button type="submit" class="btn primary"><?=e(tr('Ověřit praktický pokus'))?></button><small class="v48-formative-note"><?=e(tr('Výsledek je čistě formativní: grade_impact=false · xp_impact=false · mastery_impact=false.'))?></small>
        </form>
      </section>
    </section>
    <?php
}

function v481_render_teacher_extension(string $classId,array $lesson): void
{
    if($classId!=='class_3a')return;$spec=v481_3a_spec((int)$lesson['number']);if(!$spec)return;$summary=v481_teacher_summary($classId,(int)$lesson['number']);$teacher=(array)($spec['teacher']??[]);
    ?>
    <section class="teacher-panel v481-teacher-extension" data-v481-teacher-extension>
      <div class="teacher-panel-head"><div><span>v48.1 · Deep Visual Lab</span><h2><?=e((string)$spec['title'])?></h2><p><?=e((string)$spec['mission'])?></p></div><span class="v48-live-status">3.A · lesson-specific</span></div>
      <div class="v481-teacher-stats"><div><span>Pokusilo se</span><strong data-v481-attempted><?= (int)$summary['attempted'] ?></strong></div><div><span>Dokončilo</span><strong data-v481-completed><?= (int)$summary['completed'] ?></strong></div><div><span>Průměr checkpointů</span><strong data-v481-average><?= (int)$summary['avg_percent'] ?> %</strong></div><div><span>Guided / Practice / Challenge</span><strong><?= (int)($summary['modes']['guided']??0) ?> / <?= (int)($summary['modes']['practice']??0) ?> / <?= (int)($summary['modes']['challenge']??0) ?></strong></div></div>
      <div class="v481-teacher-prompts"><article><span>Cold call</span><p><?=e((string)($teacher['cold_call']??''))?></p></article><article><span>Diskuse</span><p><?=e((string)($teacher['discussion']??''))?></p></article><article><span>Extension</span><p><?=e((string)($teacher['extension']??''))?></p></article></div>
      <small class="muted">Přehled je formativní. Nezobrazuje pořadí studentů ani z něj nevzniká známka.</small>
    </section>
    <?php
}
