<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v45_json_script(array $value): string
{
    return (string)json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
}

function v45_render_design_stage(array $spec): void
{
    $family=(string)$spec['family'];
    $mode=match($family){
        'typography','layout','responsive'=>'page',
        'accessibility','forms'=>'form',
        'components','handoff'=>'components',
        'information','usability'=>'ia',
        'image'=>'image',
        'motion'=>'motion',
        default=>'page',
    };
    ?><div class="v45-design-stage mode-<?=e($mode)?> family-<?=e($family)?>" data-v45-design-stage data-v45-design-mode="<?=e($mode)?>">
      <?php if($mode==='form'): ?>
        <div class="v45-form-demo" data-v45-device><div class="v45-form-copy"><small><?=e(tr('Úkol uživatele'))?></small><h3><?=edu_cs((string)$spec['lesson_title'])?></h3><p><?=e(tr('Vyplň údaje a oprav chybu bez ztráty kontextu.'))?></p></div><label><span><?=e(tr('Školní e-mail'))?></span><input value="student@educanet.cz" readonly></label><label class="v45-field-error"><span><?=e(tr('Heslo'))?></span><input value="••••••" readonly><small><?=e(tr('Chyba musí říct, co změnit — ne jen že „něco je špatně“.'))?></small></label><button type="button"><?=e(tr('Pokračovat'))?></button><i class="v45-keyboard-focus">keyboard focus</i></div>
      <?php elseif($mode==='components'): ?>
        <div class="v45-component-demo" data-v45-device><div class="v45-component-spec"><span>Design token</span><strong>space / radius / type</strong><i></i><i></i><i></i></div><div class="v45-component-variants"><article><small>default</small><button><?=e(tr('Akce'))?></button></article><article><small>focus</small><button><?=e(tr('Akce'))?></button></article><article><small>error</small><button><?=e(tr('Akce'))?></button></article></div><div class="v45-handoff-line"><span>Design</span><i>→</i><span>Spec</span><i>→</i><span><?=e(tr('Implementace'))?></span></div></div>
      <?php elseif($mode==='ia'): ?>
        <div class="v45-ia-demo" data-v45-device><div class="v45-ia-goal"><small><?=e(tr('Úkol'))?></small><strong><?=e(tr('Najít správnou cestu bez hádání'))?></strong></div><div class="v45-ia-map"><span><?=e(tr('Start'))?></span><i></i><span><?=e(tr('Skupina A'))?></span><i></i><span><?=e(tr('Cíl'))?></span><b class="v45-ia-detour"><?=e(tr('Slepá větev'))?></b></div><div class="v45-ia-evidence"><small>task success</small><strong data-v45-stage-state><?=e(tr('výchozí problém'))?></strong></div></div>
      <?php elseif($mode==='image'): ?>
        <div class="v45-image-demo" data-v45-device><div class="v45-image-frame"><div class="v45-image-subject">FOCAL</div><div class="v45-crop-window"></div></div><div class="v45-image-meta"><span>crop / scale / focal point</span><strong data-v45-stage-state><?=e(tr('výchozí problém'))?></strong></div></div>
      <?php elseif($mode==='motion'): ?>
        <div class="v45-motion-demo" data-v45-device><div class="v45-motion-track"><article><?=e(tr('STAV A'))?></article><i>→</i><article class="v45-motion-object"><?=e(tr('STAV B'))?></article></div><p><?=e(tr('Pohyb má vysvětlit změnu stavu. Pokud jej vypneš, význam musí zůstat čitelný.'))?></p><strong data-v45-stage-state><?=e(tr('výchozí problém'))?></strong></div>
      <?php else: ?>
        <div class="v45-device-frame" data-v45-device><div class="v45-demo-nav"><i></i><span></span><span></span></div><div class="v45-demo-layout"><div class="v45-demo-copy"><small>EDUCANET · <?=e((string)$spec['family'])?></small><h3 data-v45-demo-title<?= edu_content_lang_attr() ?>><?=e((string)$spec['lesson_title'])?></h3><p><?=e(tr('Jeden parametr mění pozorovatelný výsledek. Sleduj funkčnost, ne efekt.'))?></p><button type="button"><?=e(tr('Primární akce'))?></button></div><div class="v45-demo-visual"><span></span><i></i><b></b></div></div><div class="v45-focus-ring">focus</div></div>
      <?php endif; ?>
      <div class="v45-stage-note"><span data-v45-stage-state><?=e(tr('výchozí problém'))?></span><strong data-v45-stage-proof><?=e(tr('Začni změnou jediné proměnné.'))?></strong></div>
    </div><?php
}

function v45_render_system_stage(array $spec): void
{
    $layers=array_values((array)$spec['layers']);
    ?><div class="v45-system-stage" data-v45-system-stage>
      <svg viewBox="0 0 960 300" role="img" aria-label="<?=e(tr('Manipulovatelný model systému'))?>">
        <defs><marker id="v45-arrow-<?=e((string)$spec['id'])?>" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 z"/></marker></defs>
        <?php $n=max(1,count($layers));foreach($layers as $i=>$l):$x=60+$i*(820/max(1,$n-1));if($i>0):$px=60+($i-1)*(820/max(1,$n-1));?><line x1="<?=$px+130?>" y1="148" x2="<?=$x-22?>" y2="148" marker-end="url(#v45-arrow-<?=e((string)$spec['id'])?>)" data-v45-edge="<?=$i-1?>"/><?php endif;?><g transform="translate(<?=$x?>,98)" data-v45-node="<?=$i?>"<?= edu_content_lang_attr() ?>><rect width="150" height="102" rx="20"/><text x="75" y="38" text-anchor="middle"><?=e((string)$l['label'])?></text><foreignObject x="14" y="50" width="122" height="42"><div xmlns="http://www.w3.org/1999/xhtml"><?=e((string)$l['text'])?></div></foreignObject></g><?php endforeach;?>
        <circle r="6" cx="82" cy="148" class="v45-packet" data-v45-packet></circle>
      </svg>
      <div class="v45-system-signals"><span><i></i> request</span><span><i></i> response/evidence</span><strong data-v45-stage-proof><?=e(tr('Nejdřív zjisti, ve které vrstvě se stav mění.'))?></strong></div>
    </div><?php
}

function v45_render_controls(array $spec): void
{
    ?><section class="v45-control-rack">
      <div class="v45-section-head"><div><span><?=e(tr('Manipulovatelné parametry'))?></span><h3><?=e(tr('Změň jednu proměnnou'))?></h3><p><?=e(tr('Každá změna okamžitě přepočítá výsledek. Cílem je popsat kauzalitu, ne trefit číslo.'))?></p></div><div class="v45-control-actions"><button type="button" class="btn secondary small" data-v45-undo disabled>↶ <?=e(tr('Zpět'))?></button><button type="button" class="btn secondary small" data-v45-redo disabled>↷ <?=e(tr('Vpřed'))?></button><button type="button" class="btn secondary small" data-v45-reset><?=e(tr('Reset'))?></button></div></div>
      <div class="v45-controls"><?php foreach((array)$spec['params'] as $p):$span=max(.0001,(float)$p['max']-(float)$p['min']);$targetPct=max(0,min(100,(((float)$p['target']-(float)$p['min'])/$span)*100));?><label data-v45-control-wrap="<?=e((string)$p['id'])?>" style="--v45-target-pos:<?=e(number_format($targetPct,2,'.',''))?>%"><div><strong><?=edu_cs((string)$p['label'])?></strong><output><?=edu_cs((string)$p['value'].(string)$p['unit'])?></output></div><div class="v45-range-shell"><input type="range" min="<?=e((string)$p['min'])?>" max="<?=e((string)$p['max'])?>" step="<?=e((string)$p['step'])?>" value="<?=e((string)$p['value'])?>" data-v45-control="<?=e((string)$p['id'])?>" aria-label="<?=e((string)$p['label'])?>"<?= edu_content_lang_attr() ?>><i aria-hidden="true"></i></div><small><?=edu_cs((string)$p['hint'])?></small><div class="v45-param-meta"><span><?=e(tr('cíl'))?> <b><?=edu_cs((string)$p['target'].(string)$p['unit'])?></b></span><button type="button" data-v45-set-target="<?=e((string)$p['id'])?>"><?=e(tr('nastavit cíl'))?></button></div></label><?php endforeach;?></div>
    </section><?php
}

function v45_render_metrics(array $spec): void
{
    ?><div class="v45-metrics" data-v45-metrics><?php foreach((array)$spec['metric_labels'] as $id=>$label):?><article data-v45-metric="<?=e((string)$id)?>"><span<?= edu_content_lang_attr() ?>><?=e((string)$label)?></span><strong>—</strong><small data-v45-metric-delta>—</small><i><em></em></i></article><?php endforeach;?></div><?php
}

function v45_render_causal_console(): void
{
    ?><section class="v45-causal-console" aria-live="polite">
      <article><span><?=e(tr('Aktivní příčina'))?></span><strong data-v45-cause><?=e(tr('zatím žádná změna'))?></strong><p data-v45-cause-detail><?=e(tr('Změň jeden parametr a sleduj směr i velikost dopadu.'))?></p></article>
      <article><span><?=e(tr('Nejslabší článek'))?></span><strong data-v45-weakest>—</strong><p data-v45-weakest-detail><?=e(tr('Engine průběžně hledá parametr nejdál od cíle.'))?></p></article>
      <article><span>Trade-off</span><strong data-v45-tradeoff>—</strong><p data-v45-tradeoff-detail><?=e(tr('Zlepšení jedné metriky nemusí znamenat lepší celek.'))?></p></article>
    </section><?php
}

function v45_render_what_if(array $spec): void
{
    ?><section class="v45-what-if"><div class="v45-section-head"><div><span><?=e(tr('What-if scénáře'))?></span><h3><?=e(tr('Změň podmínky, ne zadání'))?></h3><p><?=e(tr('Scénář můžeš aplikovat celý, nebo ho rozložit na jednotlivé zásahy a sledovat, která změna byla rozhodující.'))?></p></div></div><div class="v45-what-grid"><?php foreach((array)$spec['what_if'] as $w):?><article data-v45-scenario-card="<?=e((string)$w['id'])?>"><span><?=e(tr('CO KDYŽ'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$w['title'])?></strong><small<?= edu_content_lang_attr() ?>><?=e((string)$w['text'])?></small><div class="button-row"><button type="button" class="btn secondary small" data-v45-scenario="<?=e((string)$w['id'])?>"><?=e(tr('Aplikovat vše'))?></button><button type="button" class="btn secondary small" data-v45-scenario-step="<?=e((string)$w['id'])?>"><?=e(tr('Krokovat'))?></button></div></article><?php endforeach;?></div>
      <div class="v45-scenario-coach" data-v45-scenario-coach hidden><div><span><?=e(tr('KROKOVANÝ SCÉNÁŘ'))?></span><strong data-v45-scenario-step-title>—</strong><p data-v45-scenario-step-copy>—</p></div><div class="v45-scenario-progress"><span data-v45-scenario-progress>0 / 0</span><button type="button" class="btn primary small" data-v45-scenario-next><?=e(tr('Další změna'))?></button><button type="button" class="btn secondary small" data-v45-scenario-cancel><?=e(tr('Ukončit'))?></button></div></div>
    </section><?php
}

function v45_render_compare_workspace(array $state,bool $teacher=false): void
{
    $slots=(array)($state['slots']??[]);
    ?><section class="v45-compare"><div class="v45-section-head"><div><span><?=e(tr('A / B comparison'))?></span><h3><?=e(tr('Porovnej varianty podle evidence'))?></h3><p><?=e(tr('Zamkni A, změň jednu či více podmínek a zamkni B. Níže uvidíš přesně, které parametry a metriky se změnily.'))?></p></div><?php if($teacher):?><span class="v45-local-badge"><?=e(tr('lokální projektor · nic se neukládá'))?></span><?php endif;?></div><div class="v45-compare-grid"><article data-v45-slot-card="a"><span><?=e(tr('VARIANTA A'))?></span><strong><?=isset($slots['a'])?e(tr('uloženo')):e(tr('zatím prázdná'))?></strong><div class="v45-mini-preview" data-v45-mini="a"><i></i></div><div data-v45-slot-metrics="a"></div><div class="button-row"><button type="button" class="btn secondary small" data-v45-save="a"><?=e(tr('Zamknout aktuální jako A'))?></button><button type="button" class="btn secondary small" data-v45-load="a"><?=e(tr('Načíst A'))?></button></div></article><article data-v45-slot-card="b"><span><?=e(tr('VARIANTA B'))?></span><strong><?=isset($slots['b'])?e(tr('uloženo')):e(tr('zatím prázdná'))?></strong><div class="v45-mini-preview" data-v45-mini="b"><i></i></div><div data-v45-slot-metrics="b"></div><div class="button-row"><button type="button" class="btn secondary small" data-v45-save="b"><?=e(tr('Zamknout aktuální jako B'))?></button><button type="button" class="btn secondary small" data-v45-load="b"><?=e(tr('Načíst B'))?></button></div></article><article class="v45-delta"><span><?=e(tr('ROZDÍL'))?></span><strong data-v45-delta-title><?=e(tr('Ulož A a B'))?></strong><p data-v45-delta-copy><?=e(tr('Potom vysvětli, který konkrétní parametr změnil výsledek.'))?></p><button type="button" class="btn secondary small" data-v45-swap><?=e(tr('Prohodit A ↔ B'))?></button></article></div>
      <div class="v45-ab-detail" data-v45-ab-detail hidden><div class="v45-ab-head"><strong><?=e(tr('Co přesně se mezi A a B změnilo?'))?></strong><span data-v45-ab-count><?=e(tr('0 změn'))?></span></div><div class="v45-ab-table" data-v45-ab-table></div></div>
    </section><?php
}

function v45_render_history(): void
{
    ?><section class="v45-history"><div class="v45-section-head"><div><span><?=e(tr('Experimentální stopa'))?></span><h3><?=e(tr('Poslední změny'))?></h3><p><?=e(tr('Historie je pouze v prohlížeči. Pomáhá vrátit se k okamžiku, kdy se výsledek zlomil.'))?></p></div><button type="button" class="btn secondary small" data-v45-history-clear><?=e(tr('Vyčistit historii'))?></button></div><ol data-v45-history-list><li class="is-empty"><?=e(tr('Zatím žádná změna.'))?></li></ol></section><?php
}

function v45_render_visual_simulation(string $classId,array $lesson,array $module,string $studentKey): void
{
    $spec=v45_simulation_spec($classId,$lesson,$module);$state=v45_student_state($classId,$studentKey,$lesson);
    ?><section class="v45-simulation" data-v45-sim data-lesson="<?=(int)$lesson['number']?>" data-renderer="<?=e((string)$spec['renderer'])?>" data-family="<?=e((string)$spec['family'])?>">
      <script type="application/json" data-v45-spec><?=v45_json_script($spec)?></script><script type="application/json" data-v45-state><?=v45_json_script($state)?></script>
      <header class="v45-hero"><div><span class="eyebrow"><?=e(tr('Visual Simulation Engine v45 · experimentální vrstva'))?></span><h2><?=e(tr('Co se změní, když změním jednu podmínku?'))?></h2><p<?= edu_content_lang_attr() ?>><?=e((string)$spec['problem'])?></p></div><div class="v45-hero-actions"><button type="button" class="btn secondary small" data-v45-motion><?=e(tr('Motion: auto'))?></button><button type="button" class="btn secondary small" data-v45-fullscreen><?=e(tr('Celá obrazovka'))?></button></div></header>
      <div class="v45-workbench"><section class="v45-stage"><div class="v45-stage-toolbar"><span><?=e(tr('živý model'))?></span><strong data-v45-scenario-label><?=e(tr('Výchozí problém'))?></strong></div><?php if($spec['renderer']==='design')v45_render_design_stage($spec);else v45_render_system_stage($spec);?><?php v45_render_metrics($spec);?></section><?php v45_render_controls($spec);?></div>
      <?php v45_render_causal_console(); ?>
      <div class="v45-feedback" aria-live="polite"><span><?=e(tr('Okamžitá zpětná vazba'))?></span><strong data-v45-feedback-title><?=e(tr('Změň první parametr.'))?></strong><p data-v45-feedback-copy><?=e(tr('Laboratoř ti řekne, co se změnilo, jakým směrem a který kompromis tím vznikl.'))?></p></div>
      <?php v45_render_what_if($spec); ?>
      <?php v45_render_compare_workspace($state); ?>
      <?php v45_render_history(); ?>
      <section class="v45-evidence"><div><span><?=e(tr('Evidence checkpoint'))?></span><h3><?=e(tr('Ulož variantu, kterou umíš obhájit'))?></h3><p><?=e(tr('„Nejlepší“ neznamená nejvyšší číslo. Napiš krátké vysvětlení vztahu mezi změnou, pozorováním a závěrem.'))?></p><label class="v45-evidence-note"><span><?=e(tr('Moje vysvětlení'))?></span><textarea rows="2" maxlength="600" data-v45-evidence-note placeholder="<?=e(tr('Změnil/a jsem…, pozoroval/a jsem…, proto tvrdím…'))?>"></textarea></label></div><button type="button" class="btn primary" data-v45-save="best"><?=e(tr('Uložit obhájenou variantu'))?></button><span data-v45-save-feedback></span></section>
    </section><?php
}

function v45_render_teacher_projection(string $classId,array $lesson,array $module): void
{
    $spec=v45_simulation_spec($classId,$lesson,$module);$summary=v45_teacher_summary($classId,(int)$lesson['number']);
    ?><section class="teacher-panel v45-teacher" data-v45-sim data-v45-teacher data-v45-teacher-phase="predict" data-lesson="<?=(int)$lesson['number']?>" data-renderer="<?=e((string)$spec['renderer'])?>" data-family="<?=e((string)$spec['family'])?>">
      <script type="application/json" data-v45-spec><?=v45_json_script($spec)?></script><script type="application/json" data-v45-state>{}</script>
      <div class="teacher-panel-head"><div><span>Visual Simulation v45 · projektor</span><h2>Predikce → zásah → vysvětlení → reveal</h2><p><?=e((string)$spec['projection']['ask'])?></p></div><div class="v45-teacher-stats"><span>prozkoumalo <b><?=(int)$summary['explored']?> / <?=(int)$summary['students']?></b></span><span>obhájilo variantu <b><?=(int)$summary['ready']?></b></span><span>prům. funkčnost <b><?=(int)$summary['avg_quality']?> %</b></span></div></div>
      <div class="v45-teacher-phasebar" role="group" aria-label="Fáze projekční aktivity"><button type="button" class="is-active" data-v45-phase="predict"><b>1</b> Predikce</button><button type="button" data-v45-phase="discuss"><b>2</b> Diskuse</button><button type="button" data-v45-phase="reveal"><b>3</b> Reveal</button></div>
      <div class="v45-projection-bar"><button type="button" class="btn primary" data-v45-fullscreen>Prezentovat</button><button type="button" class="btn secondary" data-v45-freeze>Freeze & ask</button><button type="button" class="btn secondary" data-v45-spotlight>Spotlight parametr</button><button type="button" class="btn secondary" data-v45-teacher-reset>Reset demo</button><span data-v45-teacher-prompt><?=e((string)$spec['projection']['reveal'])?></span></div>
      <aside class="v45-teacher-cue"><span data-v45-cue-kicker>PREDIKCE</span><strong data-v45-cue-title>Který parametr podle třídy změní výsledek nejvíc?</strong><p data-v45-cue-copy>Neukazuj čísla. Nech žáky formulovat očekávaný směr změny a důvod.</p><small>Klávesy: P predikce · D diskuse · R reveal · F freeze · ←/→ spotlight · 1–3 scénáře</small></aside>
      <div class="v45-workbench v45-projection-workbench"><section class="v45-stage"><div class="v45-stage-toolbar"><span>projekční model</span><strong data-v45-scenario-label>Výchozí problém</strong></div><?php if($spec['renderer']==='design')v45_render_design_stage($spec);else v45_render_system_stage($spec);?><?php v45_render_metrics($spec);?></section><?php v45_render_controls($spec);?></div>
      <?php v45_render_causal_console(); ?>
      <?php v45_render_what_if($spec); ?>
      <?php v45_render_compare_workspace([],true); ?>
      <div class="v45-feedback teacher-hidden" aria-live="polite"><span>Feedback pro třídu</span><strong data-v45-feedback-title>Zatím skryto.</strong><p data-v45-feedback-copy><?=e((string)$spec['projection']['compare'])?></p></div>
    </section><?php
}
