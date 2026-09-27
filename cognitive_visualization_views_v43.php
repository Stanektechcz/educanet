<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function cv43_json_attr(mixed $value): string
{
    return e((string)json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_HEX_TAG));
}

function cv43_render_flow_canvas(array $spec): void
{
    $layers=(array)$spec['layers'];
    ?>
    <div class="cv43-system-canvas" data-cv43-canvas>
      <svg viewBox="0 0 920 290" role="img" aria-label="<?=e(tr('Vizuální model procesu'))?>">
        <defs><marker id="cv43-arrow-<?=e((string)$spec['id'])?>" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 z" class="cv43-arrow-head"/></marker></defs>
        <?php $count=max(1,count($layers)); foreach($layers as $i=>$layer): $x=55+$i*(810/max(1,$count-1)); ?>
          <?php if($i>0): $px=55+($i-1)*(810/max(1,$count-1)); ?><line x1="<?= $px+115 ?>" y1="145" x2="<?= $x-20 ?>" y2="145" marker-end="url(#cv43-arrow-<?=e((string)$spec['id'])?>)" class="cv43-edge" data-cv43-edge="<?=$i-1?>" /><?php endif; ?>
          <g class="cv43-node" data-cv43-node="<?=$i?>" data-layer="<?=e((string)$layer['id'])?>" transform="translate(<?=$x?>,95)"<?= edu_content_lang_attr() ?>>
            <rect width="145" height="100" rx="18"/><text x="72.5" y="36" text-anchor="middle" class="cv43-node-title"><?=e((string)$layer['label'])?></text><foreignObject x="12" y="49" width="121" height="42"><div xmlns="http://www.w3.org/1999/xhtml" class="cv43-node-copy"><?=e((string)$layer['text'])?></div></foreignObject>
          </g>
        <?php endforeach; ?>
      </svg>
      <div class="cv43-canvas-caption" data-cv43-stage-copy<?= edu_content_lang_attr() ?>><?=e((string)($layers[0]['text']??''))?></div>
    </div>
    <?php
}

function cv43_render_design_canvas(array $spec): void
{
    $family=(string)$spec['family'];
    ?>
    <div class="cv43-design-canvas family-<?=e($family)?>" data-cv43-design-preview style="--lab-gap:12px;--lab-title:34px;--lab-width:680px;--lab-radius:18px;--lab-contrast:.45;--lab-focus:25%;--lab-duration:900ms;--lab-distance:90px">
      <div class="cv43-preview-shell">
        <div class="cv43-preview-media"><span></span><i>focal</i></div>
        <div class="cv43-preview-copy"><small>EDUCANET · <?=edu_cs((string)$spec['lesson_title'])?></small><h3><?=e(tr('Jedno pravidlo mění celý výsledek'))?></h3><p><?=e(tr('Manipuluj s parametry a sleduj, která změna řeší příčinu problému místo jeho maskování.'))?></p><div class="cv43-preview-actions"><button type="button"><?=e(tr('Primární akce'))?></button><a><?=e(tr('Další informace'))?></a></div></div>
      </div>
      <div class="cv43-preview-status"><span data-cv43-change><?=e(tr('Začni jedním parametrem.'))?></span><strong data-cv43-quality><?=e(tr('model před úpravou'))?></strong></div>
    </div>
    <?php
}

function cv43_render_map_canvas(array $spec): void
{
    $tokens=(array)$spec['model']['tokens'];
    ?>
    <div class="cv43-map-canvas" data-cv43-canvas>
      <div class="cv43-map-user"><span><?=e(tr('Úkol'))?></span><strong><?=edu_cs((string)($tokens[0]??tr('Uživatel')))?></strong></div>
      <div class="cv43-map-groups"<?= edu_content_lang_attr() ?>>
        <?php foreach(array_slice($tokens,1) as $i=>$token): ?><article data-cv43-node="<?=$i?>"><span><?=str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?></span><strong><?=e((string)$token)?></strong><small><?=e((string)($spec['layers'][$i%max(1,count($spec['layers']))]['text']??''))?></small></article><?php endforeach; ?>
      </div>
    </div>
    <?php
}

function cv43_render_metrics_canvas(array $spec): void
{
    ?>
    <div class="cv43-metrics-canvas" data-cv43-canvas>
      <div class="cv43-metric-grid"<?= edu_content_lang_attr() ?>>
      <?php foreach((array)$spec['layers'] as $i=>$l): ?><article data-cv43-node="<?=$i?>"><div class="cv43-spark" aria-hidden="true"><?php for($b=0;$b<12;$b++): $h=18+(($b*13+$i*17)%62); ?><i style="--h:<?=$h?>%"></i><?php endfor;?></div><strong><?=e((string)$l['label'])?></strong><small><?=e((string)$l['text'])?></small></article><?php endforeach; ?>
      </div>
      <div class="cv43-metric-event"><span></span><b data-cv43-stage-copy><?=e(tr('Baseline → změna → dopad'))?></b></div>
    </div>
    <?php
}

function cv43_render_incident_canvas(array $spec): void
{
    ?>
    <div class="cv43-incident-canvas" data-cv43-canvas<?= edu_content_lang_attr() ?>>
      <?php foreach((array)$spec['layers'] as $i=>$l): ?><article data-cv43-node="<?=$i?>"><span><?=$i+1?></span><div><strong><?=e((string)$l['label'])?></strong><small><?=e((string)$l['text'])?></small></div></article><?php endforeach; ?>
    </div>
    <?php
}

function cv43_render_canvas(array $spec): void
{
    $renderer=(string)($spec['renderer']??'flow');
    if($renderer==='design') { cv43_render_design_canvas($spec); return; }
    if($renderer==='map') { cv43_render_map_canvas($spec); return; }
    if($renderer==='metrics') { cv43_render_metrics_canvas($spec); return; }
    if($renderer==='timeline'||$renderer==='incident') { cv43_render_incident_canvas($spec); return; }
    cv43_render_flow_canvas($spec);
}

function cv43_render_layer_controls(array $spec): void
{
    ?><div class="cv43-layer-controls" aria-label="<?=e(tr('X-Ray vrstvy'))?>"><span>X-Ray</span><?php foreach((array)$spec['layers'] as $i=>$l): ?><button type="button" class="<?= $i===0?'active':'' ?>" data-cv43-layer="<?=e((string)$l['id'])?>" data-index="<?=$i?>"<?= edu_content_lang_attr() ?>><b><?=e((string)$l['label'])?></b><small><?=e((string)$l['text'])?></small></button><?php endforeach;?></div><?php
}

function cv43_render_controls(array $spec): void
{
    if(empty($spec['controls'])) return;
    ?><div class="cv43-control-panel"><div class="eyebrow">Cause → effect</div><h3><?=e(tr('Změň jednu věc a sleduj důsledek'))?></h3><p><?=e(tr('Nezkoušej všechno najednou. Jeden parametr = jeden pozorovatelný důsledek.'))?></p><div class="cv43-control-list"><?php foreach((array)$spec['controls'] as $c): ?><label<?= edu_content_lang_attr() ?>><span><strong><?=e((string)$c['label'])?></strong><output data-cv43-output><?=e((string)$c['value']).e((string)$c['unit'])?></output></span><input type="range" min="<?=e((string)$c['min'])?>" max="<?=e((string)$c['max'])?>" value="<?=e((string)$c['value'])?>" data-target="<?=e((string)$c['target'])?>" data-unit="<?=e((string)$c['unit'])?>" data-cv43-control="<?=e((string)$c['id'])?>"></label><?php endforeach;?></div><div class="cv43-control-actions"><button type="button" class="btn secondary small" data-cv43-before><?=e(tr('Před'))?></button><button type="button" class="btn secondary small" data-cv43-after><?=e(tr('Moje změny'))?></button><button type="button" class="btn secondary small" data-cv43-target><?=e(tr('Ukázat funkční variantu'))?></button></div></div><?php
}

function cv43_render_prediction(array $spec): void
{
    ?><section class="cv43-prediction" data-cv43-prediction><div class="eyebrow"><?=e(tr('Freeze & predict · nejdřív hypotéza'))?></div><h3<?= edu_content_lang_attr() ?>><?=e((string)$spec['question'])?></h3><div class="cv43-confidence"><span><?=e(tr('Jak moc si věříš?'))?></span><button type="button" data-cv43-confidence="1"><?=e(tr('Hádám'))?></button><button type="button" data-cv43-confidence="2"><?=e(tr('Trochu'))?></button><button type="button" data-cv43-confidence="3"><?=e(tr('Hodně'))?></button></div><div class="cv43-choice-grid"><?php foreach((array)$spec['options'] as $i=>$o): ?><button type="button" data-cv43-predict="<?=$i?>"<?= edu_content_lang_attr() ?>><span><?=chr(65+$i)?></span><strong><?=e((string)$o)?></strong></button><?php endforeach;?></div><div class="cv43-feedback" data-cv43-predict-feedback hidden></div></section><?php
}

function cv43_render_timeline(array $spec): void
{
    $steps=(array)$spec['timeline'];
    ?><section class="cv43-timeline"><div class="cv43-section-head"><div><div class="eyebrow"><?=e(tr('Timeline scrubber'))?></div><h3><?=e(tr('Zastav proces v libovolném kroku'))?></h3></div><span data-cv43-timeline-count>1 / <?=count($steps)?></span></div><input type="range" min="0" max="<?=max(0,count($steps)-1)?>" value="0" step="1" data-cv43-timeline aria-label="<?=e(tr('Krok procesu'))?>"><div class="cv43-timeline-labels"><?php foreach($steps as $i=>$s): ?><button type="button" class="<?= $i===0?'active':'' ?>" data-cv43-timeline-step="<?=$i?>"<?= edu_content_lang_attr() ?>><span><?=$i+1?></span><strong><?=e((string)$s)?></strong></button><?php endforeach;?></div><div class="cv43-timeline-explain"><strong data-cv43-timeline-title<?= edu_content_lang_attr() ?>><?=e((string)($steps[0]??''))?></strong><span data-cv43-timeline-help><?=e(tr('Co je v tomto kroku pozorovatelné? Která vrstva právě rozhoduje o dalším stavu?'))?></span></div></section><?php
}

function cv43_render_compare(array $spec): void
{
    $c=(array)$spec['compare'];
    ?><section class="cv43-compare"><div class="cv43-section-head"><div><div class="eyebrow"><?=e(tr('Contrast case'))?></div><h3><?=e(tr('Podobné na pohled, jiné v principu'))?></h3></div><div class="cv43-segment"><button type="button" class="active" data-cv43-compare="bad"><?=e(tr('Chybný model'))?></button><button type="button" data-cv43-compare="good"><?=e(tr('Funkční model'))?></button></div></div><div class="cv43-compare-stage"><article data-cv43-compare-panel="bad"><span><?=e(tr('Typická past'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$c['bad'])?></strong></article><article data-cv43-compare-panel="good" hidden><span><?=e(tr('Funkční mentální model'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$c['good'])?></strong></article><p><b><?=e(tr('Proč:'))?></b> <?=edu_cs((string)$c['why'])?></p></div></section><?php
}

function cv43_render_model_builder(array $spec,array $state=[]): void
{
    $tokens=(array)$spec['model']['tokens'];$expected=(array)$spec['model']['expected'];$saved=is_array($state['model']??null)?(array)$state['model']['sequence']:[];
    ?><section class="cv43-model-builder" data-cv43-model data-expected="<?=cv43_json_attr($expected)?>" data-saved="<?=cv43_json_attr($saved)?>"><div class="cv43-section-head"><div><div class="eyebrow">Build the model</div><h3><?=e(tr('Sestav princip bez hotového diagramu'))?></h3><p><?=e(tr('Klikni na prvky v pořadí, ve kterém podle tebe systém skutečně funguje.'))?></p></div><?php if($saved):?><span class="cv43-saved"><?=e(tr('uložený pokus'))?></span><?php endif;?></div><div class="cv43-token-bank"><?php foreach($tokens as $t):?><button type="button" data-cv43-token="<?=e((string)$t)?>"<?= edu_content_lang_attr() ?>><?=e((string)$t)?></button><?php endforeach;?></div><div class="cv43-model-sequence" data-cv43-sequence><span class="cv43-placeholder"><?=e(tr('Tvůj model se objeví tady…'))?></span></div><div class="button-row"><button type="button" class="btn secondary small" data-cv43-model-reset><?=e(tr('Reset'))?></button><button type="button" class="btn primary small" data-cv43-model-check><?=e(tr('Ověřit model'))?></button></div><div class="cv43-feedback" data-cv43-model-feedback hidden></div></section><?php
}

function cv43_render_reverse(array $spec): void
{
    $r=(array)$spec['reverse'];
    ?><details class="cv43-reverse"><summary><span>↶</span><div><strong><?=e(tr('Reverse engineering'))?></strong><small><?=e(tr('Dostaneš výsledek. Hledej možnou příčinu zpětně.'))?></small></div></summary><div class="cv43-reverse-body"><div class="cv43-reverse-result"><span><?=e(tr('Výsledek'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$r['result'])?></strong></div><p<?= edu_content_lang_attr() ?>><?=e((string)$r['prompt'])?></p><div class="cv43-clues"><?php foreach((array)$r['clues'] as $i=>$clue):?><button type="button" data-cv43-clue="<?=$i?>"><?=tr_html('Nápověda {n}',['n'=>(string)($i+1)])?></button><p data-cv43-clue-text="<?=$i?>" hidden<?= edu_content_lang_attr() ?>><?=e((string)$clue)?></p><?php endforeach;?></div></div></details><?php
}

function cv43_render_memory(array $spec): void
{
    $m=(array)$spec['memory'];
    ?><section class="cv43-memory"><div class="eyebrow">Memory Snapshot</div><div class="cv43-memory-grid"><article><span><?=e(tr('Jedna věta'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$m['sentence'])?></strong></article><article><span><?=e(tr('Typická past'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$m['trap'])?></strong></article><article><span><?=e(tr('Za pár dní'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$m['retrieval'])?></strong></article></div><div class="cv43-transfer"><span>Transfer</span><p<?= edu_content_lang_attr() ?>><?=e((string)$spec['transfer'])?></p></div></section><?php
}

function cv43_render_vocab(array $spec): void
{
    if(empty($spec['vocabulary']))return;
    ?><details class="cv43-vocab"><summary><?=tr_html('Slovník na vyžádání · {n} pojmů',['n'=>(string)count((array)$spec['vocabulary'])])?></summary><div><?php foreach((array)$spec['vocabulary'] as $v):?><button type="button" data-cv43-vocab data-layer="<?=e((string)$v['layer'])?>"><strong><?=edu_cs((string)$v['term'])?></strong><span><?=edu_cs((string)$v['definition'])?></span><small><?=e(tr('Ukázat v modelu'))?></small></button><?php endforeach;?></div></details><?php
}

function cv43_render_lab(string $classId,array $lesson,array $module,string $studentKey,bool $full=true): void
{
    $spec=cv43_lab_spec($classId,$lesson,$module);$state=cv43_student_lab_state($classId,$studentKey,$lesson);
    ?>
    <section class="cv43-lab <?= $full?'cv43-full':'cv43-embed' ?>" data-cv43-lab data-lab-id="<?=e((string)$spec['id'])?>" data-lesson="<?=(int)$spec['lesson_number']?>" data-family="<?=e((string)$spec['family'])?>" data-renderer="<?=e((string)$spec['renderer'])?>">
      <header class="cv43-lab-head"><div><div class="eyebrow"><?=tr_html('Cognitive Lab · {family}',['family'=>e(strtoupper((string)$spec['family']))])?></div><h2><?=edu_cs((string)$spec['lesson_title'])?></h2><p><?=edu_cs((string)$spec['problem'])?></p></div><div class="cv43-lab-actions"><button type="button" class="btn secondary small" data-cv43-motion><?=e(tr('Motion: auto'))?></button><?php if(!$full):?><a class="btn primary small" href="<?=e(module_url('cognitive_lab',['lesson'=>(int)$spec['lesson_number']]))?>"><?=e(tr('Otevřít celou laboratoř →'))?></a><?php endif;?></div></header>
      <?php cv43_render_prediction($spec); ?>
      <div class="cv43-workbench">
        <section class="cv43-visual-stage"><div class="cv43-stage-toolbar"><span><?=e(tr('Realita → model → princip'))?></span><div><button type="button" class="active" data-cv43-zoom="overview"><?=e(tr('Přehled'))?></button><button type="button" data-cv43-zoom="detail"><?=e(tr('Detail'))?></button></div></div><?php cv43_render_canvas($spec); cv43_render_layer_controls($spec); ?></section>
        <?php cv43_render_controls($spec); ?>
      </div>
      <?php cv43_render_timeline($spec); ?>
      <?php if($full): ?>
        <?php cv43_render_compare($spec); ?>
        <?php cv43_render_model_builder($spec,$state); ?>
        <?php cv43_render_reverse($spec); ?>
        <?php cv43_render_vocab($spec); ?>
        <?php cv43_render_memory($spec); ?>
        <?php if(function_exists('v44_render_studio_extension')) v44_render_studio_extension($classId,$lesson,$module,$studentKey); ?>
        <?php if(function_exists('v45_render_visual_simulation')) v45_render_visual_simulation($classId,$lesson,$module,$studentKey); ?>
      <?php else: ?>
        <footer class="cv43-embed-footer"><span><?=e(tr('Další krok: sestavit vlastní model a použít ho v jiné situaci.'))?></span><a href="<?=e(module_url('cognitive_lab',['lesson'=>(int)$spec['lesson_number']]))?>"><?=e(tr('Pokračovat v Cognitive Lab →'))?></a></footer>
      <?php endif; ?>
    </section>
    <?php
}

function cv43_render_lab_page(string $classId,array $lesson,array $module): void
{
    render_header(tr('Cognitive Lab · {nazev}',['nazev'=>(string)($lesson['title']??tr('Lekce'))]),$module,true);
    ?><section class="hero compact-hero cv43-page-hero"><div><div class="eyebrow"><?=tr_html('Vizuální laboratoř · Lekce {n}',['n'=>e(str_pad((string)((int)$lesson['number']),2,'0',STR_PAD_LEFT))])?></div><h1><?=e(tr('Vidět → změnit → předpovědět → vysvětlit'))?></h1><p><?=e(tr('Laboratoř ukazuje jednu konkrétní situaci z lekce. Pohyb není dekorace: každý krok reprezentuje změnu stavu nebo důkaz.'))?></p></div><div class="button-row"><a class="btn secondary" href="<?=e(v42_lesson_url($lesson))?>">← <?=e(tr('Lekce'))?></a><a class="btn secondary" href="<?=e(module_url('lesson_kit',['lesson'=>(int)$lesson['number']]))?>">Lesson Kit</a></div></section><?php
    cv43_render_lab($classId,$lesson,$module,adaptive_student_key($classId),true);
    ?><div class="button-row"><a class="btn secondary" href="?view=course"><?=e(tr('Mapa kurzu'))?></a><a class="btn primary" href="<?=e(v42_lesson_url($lesson))?>"><?=e(tr('Pokračovat v lekci →'))?></a></div><?php
    render_footer();
}

function cv43_render_teacher_explainer(string $classId,array $lesson,array $module): void
{
    $spec=cv43_lab_spec($classId,$lesson,$module);$summary=cv43_teacher_class_lab_summary($classId,(int)$lesson['number']);
    ?><section class="teacher-panel cv43-teacher-explainer"><div class="teacher-panel-head"><div><span>Cognitive Lab v43 · projektor</span><h2>Live Explainer · <?=e((string)$spec['family'])?></h2></div><small><?= (int)$summary['attempts'] ?> evidencí od studentů</small></div><div class="cv43-teacher-grid"><article><span>Situace</span><strong><?=e((string)$spec['problem'])?></strong></article><article><span>Stop & predict</span><strong><?=e((string)$spec['teacher']['ask'])?></strong></article><article><span>Typická misconception</span><strong><?=e((string)$spec['teacher']['misconception'])?></strong></article></div><details><summary><strong>Model pro projektor</strong> · odkrývej po vrstvách</summary><div class="cv43-teacher-stage"><?php cv43_render_canvas($spec); cv43_render_layer_controls($spec); ?></div><div class="cv43-teacher-timeline"><?php foreach((array)$spec['timeline'] as $i=>$s):?><span><b><?=$i+1?></b><?=e((string)$s)?></span><?php endforeach;?></div></details><p class="muted"><?=e((string)$spec['teacher']['pause'])?></p></section><?php
}
