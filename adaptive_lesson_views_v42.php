<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v42_render_lane_panel(string $classId,array $lesson,array $pack): void
{
    $lane=$pack['lane'];$signals=(array)($lane['signals']??[]);$number=(int)($lesson['number']??0);
    ?>
    <section class="v42-path-card v42-path-<?= e((string)$lane['lane']) ?>">
      <div class="v42-path-copy"><div class="eyebrow"><?= tr_html('Adaptivní cesta · {tag}',['tag'=>edu_cs((string)$lane['tag'])]) ?></div><h2><?= edu_cs((string)$lane['label']) ?></h2><p><?= edu_cs((string)$lane['description']) ?></p><small><?= edu_cs((string)$lane['reason']) ?></small></div>
      <div class="v42-path-actions">
        <details><summary><?= e(tr('Upravit obtížnost')) ?></summary><div class="v42-path-buttons">
          <?php foreach(['auto'=>tr('Automaticky'),'guided'=>tr('Více podpory'),'standard'=>tr('Standard'),'challenge'=>tr('Větší výzva')] as $key=>$label): ?>
          <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v42_set_lane"><input type="hidden" name="lesson_number" value="<?= $number ?>"><input type="hidden" name="lane" value="<?= e($key) ?>"><button class="btn secondary small" type="submit"><?= e($label) ?></button></form>
          <?php endforeach; ?>
        </div></details>
      </div>
      <div class="v42-lane-plan">
        <?php $plan = $lane['lane']==='guided' ? [tr('1 · krátké video / jiný výklad'),tr('2 · celý worked example'),tr('3 · guided practice po menších krocích'),tr('4 · teprve potom transfer')] : ($lane['lane']==='challenge' ? [tr('1 · Reality Demo bez dlouhé rekapitulace'),tr('2 · rychlý self-check'),tr('3 · náročnější transfer'),tr('4 · extension / tvorba vlastního scénáře')] : [tr('1 · Reality Demo'),tr('2 · guided practice'),tr('3 · samostatná aplikace'),tr('4 · transfer + exit ticket')]); foreach($plan as $p): ?><span><?= e($p) ?></span><?php endforeach; ?>
      </div>
      <?php if(($signals['events']??0)>0): ?><div class="v42-signal-strip"><span><?= tr_html('Evidence: {n}',['n'=>'<strong>'.e((string)(int)$signals['events']).'</strong>']) ?></span><span><?= tr_html('Správně: {pct}',['pct'=>'<strong>'.($signals['accuracy']===null?'—':e(round((float)$signals['accuracy']*100).' %')).'</strong>']) ?></span><span><?= tr_html('Transfer: {n}',['n'=>'<strong>'.e((string)(int)$signals['transfer_ok']).'</strong>']) ?></span></div><?php endif; ?>
    </section>
    <?php
}

function v42_render_video_section(array $pack,bool $open=true): void
{
    $videos=(array)$pack['videos'];$story=(array)$pack['microvideo'];
    ?>
    <details class="v42-kit-section" <?= $open?'open':'' ?>><summary><span>▶</span><div><strong><?= e(tr('Video + 90s micro-video')) ?></strong><small><?= e(tr('Jiný způsob vysvětlení, ne povinná náhrada praktické práce.')) ?></small></div></summary><div class="v42-kit-body">
      <div class="v42-video-grid">
      <?php foreach($videos as $v): ?>
        <article class="v42-video-card"><div class="v42-video-top"><span><?= tr_html('Externí video · {lang}',['lang'=>e((string)($v['lang']??'CZ'))]) ?></span><strong><?= edu_cs((string)($v['title']??tr('Video'))) ?></strong><small><?= edu_cs((string)($v['duration']??'')) ?><?= !empty($v['level'])?' · '.edu_cs((string)$v['level']):'' ?></small></div><p><?= edu_cs((string)($v['description']??'')) ?></p><div class="v42-watch-guide"><b><?= e(tr('Před')) ?></b><span><?= edu_cs((string)$v['before']) ?></span><b><?= e(tr('Během')) ?></b><span><?= edu_cs((string)$v['during']) ?></span><b><?= e(tr('Po')) ?></b><span><?= edu_cs((string)$v['after']) ?></span></div><a class="btn secondary small" target="_blank" rel="noopener noreferrer" href="<?= e((string)$v['url']) ?>"><?= e(tr('Přehrát video ↗')) ?></a></article>
      <?php endforeach; ?>
      </div>
      <div class="v42-storyboard"><div class="eyebrow"><?= e(tr('Micro-video storyboard · 90 sekund')) ?></div><?php foreach($story as $scene): ?><div class="v42-story-row"><strong><?= edu_cs((string)$scene['time']) ?></strong><div><b><?= edu_cs((string)$scene['title']) ?></b><span><?= edu_cs((string)$scene['voice']) ?></span><small><?= edu_cs((string)$scene['visual']) ?></small></div></div><?php endforeach; ?></div>
    </div></details>
    <?php
}

function v42_render_materials(array $pack,bool $open=false): void
{
    $m=(array)$pack['materials'];$one=(array)($m['one_pager']??[]);
    ?>
    <details class="v42-kit-section" <?= $open?'open':'' ?>><summary><span>▤</span><div><strong><?= e(tr('Tahák + pracovní list')) ?></strong><small><?= e(tr('Jedna stránka, kterou můžeš mít otevřenou při práci.')) ?></small></div></summary><div class="v42-kit-body">
      <div class="v42-two-col"><article class="v42-onepager"><div class="eyebrow">One-pager</div><h3><?= e(tr('Cíl')) ?></h3><p><?= edu_cs((string)($one['goal']??'')) ?></p><h3><?= e(tr('Klíčové principy')) ?></h3><ul><?php foreach((array)($one['principles']??[]) as $x):?><li><?= edu_cs((string)$x) ?></li><?php endforeach;?></ul><h3><?= e(tr('Hotovo znamená')) ?></h3><ul><?php foreach((array)($one['definition_of_done']??[]) as $x):?><li><?= edu_cs((string)$x) ?></li><?php endforeach;?></ul></article>
      <article><div class="eyebrow">Worksheet</div><ol class="v42-workbook-list"><?php foreach((array)($m['worksheet']??[]) as $x):?><li><?= edu_cs((string)$x) ?></li><?php endforeach;?></ol><div class="soft-info"><strong><?= e(tr('Rozšíření')) ?></strong><br><?= edu_cs((string)($m['extension']??'')) ?></div></article></div>
      <?php if(!empty($m['glossary'])):?><div class="v42-glossary"><?php foreach((array)$m['glossary'] as $g):?><div><strong><?= edu_cs((string)$g['term']) ?></strong><span><?= edu_cs((string)$g['definition']) ?></span></div><?php endforeach;?></div><?php endif;?>
    </div></details>
    <?php
}

function v42_render_prompt_pack(array $pack): void
{
    ?>
    <details class="v42-kit-section"><summary><span>✦</span><div><strong><?= e(tr('Prompty pro rozšířené vysvětlení')) ?></strong><small><?= e(tr('Pro ChatGPT / jiného asistenta. Prompty jsou navržené tak, aby tě vedly, ne opisovaly test.')) ?></small></div></summary><div class="v42-kit-body"><div class="v42-prompt-grid">
    <?php foreach((array)$pack['prompts'] as $p): ?><article class="v42-prompt-card"><strong><?= edu_cs((string)$p['title']) ?></strong><textarea readonly rows="7"<?= edu_content_lang_attr() ?>><?= e((string)$p['prompt']) ?></textarea><button class="btn secondary small" type="button" data-v42-copy-prompt><?= e(tr('Kopírovat prompt')) ?></button></article><?php endforeach; ?>
    </div></div></details>
    <?php
}

function v42_render_exam_pack(array $pack,bool $open=false): void
{
    $e=(array)$pack['exam'];
    ?>
    <details class="v42-kit-section" <?= $open?'open':'' ?>><summary><span>◎</span><div><strong><?= e(tr('Zkouška nanečisto')) ?></strong><small><?= e(tr('Ústní otázky, praktický scénář a transparentní mastery rubrika.')) ?></small></div></summary><div class="v42-kit-body"><div class="v42-two-col"><article><div class="eyebrow"><?= e(tr('Ústní část')) ?></div><ol class="v42-workbook-list"><?php foreach((array)$e['oral'] as $q):?><li><?= edu_cs((string)$q) ?></li><?php endforeach;?></ol></article><article><div class="eyebrow"><?= e(tr('Praktická část')) ?></div><p><?= edu_cs((string)$e['practical']) ?></p><h3><?= e(tr('Před zkouškou si ověř')) ?></h3><ul><?php foreach((array)$e['self_check'] as $x):?><li><?= edu_cs((string)$x) ?></li><?php endforeach;?></ul></article></div><div class="v42-rubric"><?php foreach((array)$e['rubric'] as $r):?><div><strong><?= edu_cs((string)$r['level']) ?></strong><span><?= edu_cs((string)$r['text']) ?></span></div><?php endforeach;?></div></div></details>
    <?php
}

function render_v42_lesson_companion(string $classId,array $lesson,array $module,array $learningResources,string $studentKey): void
{
    $pack=v42_lesson_pack($classId,$lesson,$module,$learningResources,$studentKey);v42_render_lane_panel($classId,$lesson,$pack);
    ?>
    <section class="v42-kit-shell"><header><div><div class="eyebrow"><?= e(tr('Lesson Kit · všechno k jedné lekci')) ?></div><h2><?= e(tr('Vysvětlení, prezentace, materiály a příprava na zkoušku')) ?></h2><p><?= e(tr('Otevři jen to, co právě potřebuješ. Zbytek může zůstat sbalený.')) ?></p></div><div class="button-row"><a class="btn primary" href="<?= e(module_url('visual_lab',['lesson'=>(int)($lesson['number']??0)])) ?>">Visual & Practical Lab</a><a class="btn secondary" href="<?= e(module_url('cognitive_lab',['lesson'=>(int)($lesson['number']??0)])) ?>">Cognitive Lab</a><a class="btn secondary" href="<?= e(module_url('lesson_slides',['lesson'=>(int)($lesson['number']??0)])) ?>"><?= e(tr('Prezentace celé lekce ↗')) ?></a></div></header>
    <?php $laneKey=(string)($pack['lane']['lane']??'standard'); v42_render_video_section($pack,$laneKey!=='challenge');v42_render_materials($pack,$laneKey==='guided');v42_render_prompt_pack($pack);v42_render_exam_pack($pack,$laneKey==='challenge'); ?>
    </section>
    <?php
}

function render_v42_lesson_hub_page(string $classId,array $lesson,array $module,array $learningResources): void
{
    $studentKey=adaptive_student_key($classId);$pack=v42_lesson_pack($classId,$lesson,$module,$learningResources,$studentKey);render_header(tr('Lesson Kit · {nazev}',['nazev'=>(string)($lesson['title']??tr('Lekce'))]),$module,true);
    ?>
    <section class="hero compact-hero"><div><div class="eyebrow"><?= tr_html('Lesson Kit · Lekce {n}',['n'=>e(str_pad((string)((int)($lesson['number']??0)),2,'0',STR_PAD_LEFT))]) ?></div><h1><?=edu_cs((string)($lesson['title']??tr('Lekce')))?></h1><p><?=edu_cs((string)($lesson['goal']??''))?></p></div><div class="button-row"><a class="btn primary" href="<?=e(v42_lesson_url($lesson))?>"><?=e(tr('Otevřít lekci'))?></a><a class="btn primary" href="<?=e(module_url('visual_lab',['lesson'=>(int)($lesson['number']??0)]))?>">Visual & Practical Lab</a><a class="btn secondary" href="<?=e(module_url('cognitive_lab',['lesson'=>(int)($lesson['number']??0)]))?>">Cognitive Lab</a><a class="btn secondary" href="<?=e(module_url('lesson_slides',['lesson'=>(int)($lesson['number']??0)]))?>"><?=e(tr('Prezentace'))?></a></div></section>
    <?php v42_render_lane_panel($classId,$lesson,$pack); ?>
    <section class="v42-kit-shell"><header><div><div class="eyebrow"><?= e(tr('Všechny podklady na jednom místě')) ?></div><h2><?= e(tr('Video, prezentace, tahák, prompty a zkouška')) ?></h2><p><?= e(tr('Nic z toho nemusíš projít celé. Otevři jen vrstvu, kterou právě potřebuješ.')) ?></p></div></header><?php $lane=(string)($pack['lane']['lane']??'standard');v42_render_video_section($pack,$lane!=='challenge');v42_render_materials($pack,$lane==='guided');v42_render_prompt_pack($pack);v42_render_exam_pack($pack,$lane==='challenge'); ?></section>
    <div class="button-row"><a class="btn secondary" href="?view=course">← <?= e(tr('Mapa kurzu')) ?></a><a class="btn primary" href="<?=e(v42_lesson_url($lesson))?>"><?= e(tr('Pokračovat v lekci →')) ?></a></div>
    <?php render_footer();
}

function render_v42_slide_page(string $classId,array $lesson,array $module,array $learningResources): void
{
    $pack=v42_lesson_pack($classId,$lesson,$module,$learningResources,adaptive_student_key($classId));$slides=(array)$pack['slides'];render_header(tr('Prezentace · {nazev}',['nazev'=>(string)($lesson['title']??tr('Lekce'))]),$module,true);
    ?>
    <section class="v42-slide-shell" data-v42-slides tabindex="0"><header><div><div class="eyebrow"><?= e(tr('Presentation mode')) ?></div><h1><?= edu_cs((string)($lesson['title']??tr('Lekce'))) ?></h1></div><div class="button-row"><button class="btn secondary small" type="button" data-v42-slide-prev>←</button><span data-v42-slide-count>1 / <?= count($slides) ?></span><button class="btn secondary small" type="button" data-v42-slide-next>→</button><button class="btn secondary small" type="button" onclick="window.print()"><?= e(tr('Tisk / PDF')) ?></button></div></header><div class="v42-slide-stage">
    <?php foreach($slides as $i=>$slide): ?><article class="v42-slide <?= $i===0?'active':'' ?>" data-v42-slide="<?= $i ?>" <?= $i===0?'':'hidden' ?>><span><?= edu_cs((string)$slide['kicker']) ?></span><h2><?= edu_cs((string)$slide['title']) ?></h2><p><?= edu_cs((string)$slide['body']) ?></p><footer><strong><?= e(tr('Poznámka pro výklad')) ?></strong><small><?= edu_cs((string)$slide['note']) ?></small></footer></article><?php endforeach; ?>
    </div><footer class="v42-slide-footer"><?= e(tr('← / → pro přechod · F pro fullscreen · Esc pro návrat')) ?></footer></section>
    <?php render_footer();
}
