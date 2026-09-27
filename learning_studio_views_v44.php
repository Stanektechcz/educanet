<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v44_render_gps(string $classId,string $studentKey,array $lesson): void
{
    $gps=v44_learning_gps($classId,$studentKey,$lesson);$done=count(array_filter($gps['steps'],static fn($s)=>!empty($s['done'])));$total=count($gps['steps']);
    ?><section class="v44-gps" data-v44-gps>
      <div class="v44-gps-copy"><span>Learning GPS</span><strong><?= $gps['complete']?e(tr('Princip máš zpracovaný')):tr_html('Další nejlepší krok: {krok}',['krok'=>edu_cs((string)($gps['next']['label']??''))]) ?></strong><small<?= $gps['complete']?'':edu_content_lang_attr() ?>><?= $gps['complete']?e(tr('Teď má smysl vrátit se k němu později přes retrieval.')):e((string)($gps['next']['hint']??'')) ?></small></div>
      <div class="v44-gps-track" aria-label="<?=e(tr('Postup vizuální laboratoří'))?>"><?php foreach($gps['steps'] as $i=>$step):?><div class="<?=!empty($step['done'])?'done':(($gps['next']['id']??'')===$step['id']?'current':'')?>"><i><?=!empty($step['done'])?'✓':($i+1)?></i><span><?=edu_cs((string)$step['label'])?></span></div><?php endforeach;?></div>
      <span class="v44-gps-count"><?=$done?>/<?=$total?></span>
    </section><?php
}

function v44_render_representation_deck(array $spec): void
{
    ?><section class="v44-card v44-representations" data-v44-representations>
      <header><div><span class="v44-kicker"><?=e(tr('Jeden koncept · více pohledů'))?></span><h3><?=e(tr('Nezůstaň u jedné reprezentace'))?></h3><p><?=e(tr('Přepínej mezi realitou, vnitřními vrstvami, principem, kontrastem a transferem. Informace zůstává stejná, mění se způsob, jak ji vidíš.'))?></p></div></header>
      <div class="v44-rep-tabs" role="tablist"><?php foreach($spec['representations'] as $i=>$r):?><button type="button" role="tab" aria-selected="<?=$i===0?'true':'false'?>" class="<?=$i===0?'active':''?>" data-v44-rep-tab="<?=e((string)$r['id'])?>"><?=edu_cs((string)$r['label'])?></button><?php endforeach;?></div>
      <div class="v44-rep-stage"><?php foreach($spec['representations'] as $i=>$r):?><article data-v44-rep-panel="<?=e((string)$r['id'])?>" <?=$i===0?'':'hidden'?>>
        <div class="v44-rep-visual v44-rep-<?=e((string)$r['id'])?>">
          <?php if($r['id']==='xray'): ?><div class="v44-layer-stack"><?php foreach((array)$spec['lab']['layers'] as $j=>$l):?><span style="--i:<?=$j?>"<?= edu_content_lang_attr() ?>><b><?=e((string)$l['label'])?></b><small><?=e((string)$l['text'])?></small></span><?php endforeach;?></div>
          <?php elseif($r['id']==='contrast'): ?><div class="v44-mini-contrast"><span><?=e(tr('CHYBNÝ MODEL'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$spec['lab']['compare']['bad'])?></strong><i>↔</i><span><?=e(tr('FUNKČNÍ MODEL'))?></span><strong<?= edu_content_lang_attr() ?>><?=e((string)$spec['lab']['compare']['good'])?></strong></div>
          <?php elseif($r['id']==='transfer'): ?><div class="v44-transfer-orbit"><span><?=e(tr('ZNÁMÝ PRINCIP'))?></span><b>→</b><span><?=e(tr('NOVÝ KONTEXT'))?></span></div>
          <?php elseif($r['id']==='principle'): ?><div class="v44-principle-mark"><i>∴</i><span><?=e(tr('Příčina'))?></span><b>→</b><span><?=e(tr('Důkaz'))?></span><b>→</b><span><?=e(tr('Závěr'))?></span></div>
          <?php else: ?><div class="v44-reality-scene"><span><?=e(tr('Situace'))?></span><b>?</b><span><?=e(tr('Rozhodnutí'))?></span><b>→</b><span><?=e(tr('Důsledek'))?></span></div><?php endif; ?>
        </div>
        <div class="v44-rep-copy"<?= edu_content_lang_attr() ?>><span><?=e((string)$r['kicker'])?></span><h4><?=e((string)$r['title'])?></h4><p><?=e((string)$r['text'])?></p></div>
      </article><?php endforeach;?></div>
    </section><?php
}

function v44_render_difference_lens(array $spec): void
{
    $before=(array)$spec['difference']['before'];$after=(array)$spec['difference']['after'];
    ?><section class="v44-card v44-difference" data-v44-difference>
      <header><div><span class="v44-kicker">Difference Lens</span><h3><?=e(tr('Co se změnilo?'))?></h3><p<?= edu_content_lang_attr() ?>><?=e((string)$spec['difference']['prompt'])?></p></div><output data-v44-diff-output><?=e(tr('50 % funkčního modelu'))?></output></header>
      <div class="v44-diff-stage" style="--reveal:50%">
        <div class="v44-diff-layer bad"><span><?=e(tr('Typická chyba'))?></span><div<?= edu_content_lang_attr() ?>><?php foreach($before as $i=>$token):?><b><i><?=$i+1?></i><?=e((string)$token)?></b><?php endforeach;?></div></div>
        <div class="v44-diff-layer good"><span><?=e(tr('Funkční vztah'))?></span><div<?= edu_content_lang_attr() ?>><?php foreach($after as $i=>$token):?><b><i><?=$i+1?></i><?=e((string)$token)?></b><?php endforeach;?></div></div>
        <i class="v44-diff-line" aria-hidden="true"></i>
      </div>
      <label class="v44-diff-slider"><span><?=e(tr('Chybný model'))?></span><input type="range" min="0" max="100" value="50" data-v44-diff-range aria-label="<?=e(tr('Porovnání chybného a funkčního modelu'))?>"><span><?=e(tr('Funkční model'))?></span></label>
      <button type="button" class="btn secondary small" data-v44-stage="compare"><?=e(tr('Rozdíl jsem našel/a'))?></button><span class="v44-stage-status" data-v44-stage-status="compare"></span>
    </section><?php
}

function v44_render_analogy(array $spec): void
{
    ?><details class="v44-card v44-analogy"><summary><span>◎</span><div><strong><?=e(tr('Přirovnání pro mentální model'))?></strong><small><?=e(tr('Pomocná zkratka, ne náhrada skutečného principu'))?></small></div></summary><div class="v44-analogy-grid"><article><span><?=e(tr('PŘIROVNÁNÍ'))?></span><p<?= edu_content_lang_attr() ?>><?=e((string)$spec['analogy'])?></p></article><article><span><?=e(tr('KDE PŘESTÁVÁ PLATIT'))?></span><p<?= edu_content_lang_attr() ?>><?=e((string)$spec['analogy_limit'])?></p></article></div></details><?php
}

function v44_render_model_snapshots(string $classId,string $studentKey,array $lesson,array $spec): void
{
    $state=v44_student_state($classId,$studentKey,$lesson);$before=$state['before']??null;$after=$state['after']??null;
    ?><section class="v44-card v44-model-snapshots" data-v44-model-snapshots data-lesson="<?=(int)$lesson['number']?>">
      <header><div><span class="v44-kicker"><?=e(tr('Můj model · před / po'))?></span><h3><?=e(tr('Ulož, jak tomu rozumíš'))?></h3><p><?=e(tr('Použij model, který jsi sestavil výše. Ulož ho jednou před opravou a jednou po ní — rozdíl je viditelný důkaz učení.'))?></p></div></header>
      <div class="v44-snapshot-grid"><article><span><?=e(tr('PŘED'))?></span><strong<?= is_array($before)?edu_content_lang_attr():'' ?>><?=is_array($before)?e(implode(' → ',(array)$before['sequence'])):e(tr('Zatím neuloženo'))?></strong><button type="button" class="btn secondary small" data-v44-snapshot="before"><?=e(tr('Uložit aktuální model jako PŘED'))?></button></article><article><span><?=e(tr('PO'))?></span><strong<?= is_array($after)?edu_content_lang_attr():'' ?>><?=is_array($after)?e(implode(' → ',(array)$after['sequence'])):e(tr('Zatím neuloženo'))?></strong><button type="button" class="btn secondary small" data-v44-snapshot="after"><?=e(tr('Uložit aktuální model jako PO'))?></button></article></div>
      <p class="v44-inline-feedback" data-v44-snapshot-feedback hidden></p>
    </section><?php
}

function v44_render_teachback(string $classId,string $studentKey,array $lesson,array $spec): void
{
    $state=v44_student_state($classId,$studentKey,$lesson);$saved=$state['teachback']??null;$score=is_array($saved)?(int)($saved['score']??0):0;
    ?><section class="v44-card v44-teachback" data-v44-teachback data-lesson="<?=(int)$lesson['number']?>">
      <header><div><span class="v44-kicker"><?=e(tr('Teach-back · 60 sekund'))?></span><h3><?=e(tr('Vysvětli to bez opisování'))?></h3><p<?= edu_content_lang_attr() ?>><?=e((string)$spec['teachback_question'])?></p></div><div class="v44-score-ring" style="--score:<?=$score?>%"><strong data-v44-teachback-score><?=$score?>%</strong><span>model</span></div></header>
      <textarea rows="5" maxlength="1800" data-v44-teachback-text placeholder="<?=e(tr('Např. Nejdřív bych ověřil…, protože…, důkazem by bylo…, z toho ale ještě neplyne…'))?>"><?=is_array($saved)?e((string)$saved['text']):''?></textarea>
      <div class="v44-actions"><button type="button" class="btn primary" data-v44-teachback-submit><?=e(tr('Vyhodnotit vysvětlení'))?></button><small><?=e(tr('Nejde o známku. Kontroluje se přítomnost mentálního modelu a vazby na evidence.'))?></small></div>
      <div class="v44-inline-feedback" data-v44-teachback-feedback <?=is_array($saved)?'':'hidden'?>><?=is_array($saved)?e((string)($saved['state']==='strong'?tr('Model je silný. Zkus transfer bez nápovědy.'):tr('Vysvětlení je uložené; můžeš ho ještě zpřesnit.'))):''?></div>
    </section><?php
}

function v44_render_memory_card(string $classId,string $studentKey,array $lesson,array $spec): void
{
    $state=v44_student_state($classId,$studentKey,$lesson);$saved=$state['memory']??null;$seed=$spec['memory_seed'];
    ?><section class="v44-card v44-memory-card" data-v44-memory data-lesson="<?=(int)$lesson['number']?>">
      <header><div><span class="v44-kicker"><?=e(tr('Memory Snapshot · osobní'))?></span><h3><?=e(tr('Zkomprimuj lekci na jednu obrazovku'))?></h3><p><?=e(tr('Jedna věta, jedna typická past a jedna otázka pro budoucí retrieval. Čím kratší, tím lépe.'))?></p></div><button type="button" class="btn secondary small" data-v44-print><?=e(tr('Vytisknout kartu'))?></button></header>
      <div class="v44-memory-preview" data-v44-memory-preview><span<?= edu_content_lang_attr() ?>><?=e((string)$lesson['title'])?></span><strong data-memory-sentence<?= edu_content_lang_attr() ?>><?=e((string)($saved['sentence']??$seed['sentence']))?></strong><p><b><?=e(tr('Past:'))?></b> <em data-memory-trap<?= edu_content_lang_attr() ?>><?=e((string)($saved['trap']??$seed['trap']))?></em></p><p><b><?=e(tr('Příště si polož:'))?></b> <em data-memory-cue<?= edu_content_lang_attr() ?>><?=e((string)($saved['cue']??$seed['cue']))?></em></p></div>
      <details><summary><?=e(tr('Upravit vlastní formulaci'))?></summary><div class="v44-memory-form"><label><?=e(tr('Jedna věta'))?><textarea rows="2" maxlength="420" data-v44-memory-field="sentence"><?=e((string)($saved['sentence']??$seed['sentence']))?></textarea></label><label><?=e(tr('Typická past'))?><textarea rows="2" maxlength="420" data-v44-memory-field="trap"><?=e((string)($saved['trap']??$seed['trap']))?></textarea></label><label><?=e(tr('Retrieval otázka'))?><textarea rows="2" maxlength="420" data-v44-memory-field="cue"><?=e((string)($saved['cue']??$seed['cue']))?></textarea></label><button type="button" class="btn primary small" data-v44-memory-save><?=e(tr('Uložit Memory Snapshot'))?></button><span class="v44-inline-feedback" data-v44-memory-feedback hidden></span></div></details>
    </section><?php
}

function v44_render_micro_replay(array $spec): void
{
    ?><section class="v44-card v44-replay" data-v44-replay>
      <header><div><span class="v44-kicker">90s Replay</span><h3><?=e(tr('Zopakuj princip bez celé lekce'))?></h3><p><?=e(tr('Tři krátké fáze: princip → typická past → transfer. Určeno pro pozdější opakování, ne pro první výklad.'))?></p></div><div class="v44-replay-clock"><strong data-v44-replay-time>01:30</strong><span><?=e(tr('zbývá'))?></span></div></header>
      <div class="v44-replay-stage"><?php foreach($spec['replay'] as $i=>$r):?><article class="<?=$i===0?'active':''?>" data-v44-replay-step="<?=$i?>"<?= edu_content_lang_attr() ?>><span><?=e((string)$r['label'])?></span><p><?=e((string)$r['text'])?></p></article><?php endforeach;?></div>
      <div class="v44-actions"><button type="button" class="btn primary small" data-v44-replay-start><?=e(tr('Spustit 90 s'))?></button><button type="button" class="btn secondary small" data-v44-replay-next><?=e(tr('Další fáze'))?></button><button type="button" class="btn secondary small" data-v44-stage="replay"><?=e(tr('Mám zopakováno'))?></button></div>
    </section><?php
}

function v44_render_studio_extension(string $classId,array $lesson,array $module,string $studentKey): void
{
    $spec=v44_studio_spec($classId,$lesson,$module);
    ?><section class="v44-studio" data-v44-studio data-lesson="<?=(int)$lesson['number']?>">
      <div class="v44-studio-intro"><div><span class="eyebrow">Learning Studio v44</span><h2><?=e(tr('Pochopit není totéž jako vidět správnou odpověď.'))?></h2><p><?=e(tr('Teď stejný princip otočíš z několika stran: porovnáš modely, vysvětlíš vztah vlastními slovy a vytvoříš si kompaktní paměťovou stopu.'))?></p></div></div>
      <?php v44_render_gps($classId,$studentKey,$lesson); ?>
      <?php v44_render_representation_deck($spec); ?>
      <?php v44_render_difference_lens($spec); ?>
      <button type="button" class="v44-experiment-done" data-v44-stage="experiment">✓ <?=e(tr('Experimentoval/a jsem s modelem a umím popsat, co se změnilo'))?></button><span class="v44-stage-status" data-v44-stage-status="experiment"></span>
      <?php v44_render_analogy($spec); ?>
      <?php v44_render_model_snapshots($classId,$studentKey,$lesson,$spec); ?>
      <?php v44_render_teachback($classId,$studentKey,$lesson,$spec); ?>
      <?php v44_render_micro_replay($spec); ?>
      <?php v44_render_memory_card($classId,$studentKey,$lesson,$spec); ?>
    </section><?php
}

function v44_render_dashboard_gps(string $classId,array $lesson): void
{
    $gps=v44_learning_gps($classId,adaptive_student_key($classId),$lesson);$next=$gps['next'];
    ?><section class="dashboard-panel v44-dashboard-gps"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=tr_html('Learning GPS · lekce {n}',['n'=>e(str_pad((string)((int)$lesson['number']),2,'0',STR_PAD_LEFT))])?></div><h2<?= edu_content_lang_attr() ?>><?=e((string)$lesson['title'])?></h2><p><?= $gps['complete']?e(tr('Vizuální reasoning cyklus je dokončený. Systém se k tématu vrátí později přes retrieval.')):tr_html('Teď nepotřebuješ další dashboard. Potřebuješ jeden konkrétní krok: {krok}.',['krok'=>'<strong>'.edu_cs((string)($next['label']??'')).'</strong>']) ?></p></div><a class="btn secondary" href="<?=e(module_url('cognitive_lab',['lesson'=>(int)$lesson['number']]))?>"><?= $gps['complete']?e(tr('Otevřít Memory Snapshot')):e(tr('Pokračovat →')) ?></a></div><div class="v44-dashboard-track"><?php foreach($gps['steps'] as $s):?><span class="<?=!empty($s['done'])?'done':(($next['id']??'')===$s['id']?'current':'')?>"><i><?=!empty($s['done'])?'✓':'•'?></i><?=edu_cs((string)$s['label'])?></span><?php endforeach;?></div></section><?php
}

function v44_render_teacher_live_board(string $classId,array $lesson,array $module): void
{
    $spec=v44_studio_spec($classId,$lesson,$module);$summary=v44_teacher_class_summary($classId,(int)$lesson['number']);$total=max(1,(int)$summary['total']);
    ?><section class="teacher-panel v44-live-board" data-v44-teacher-board>
      <div class="teacher-panel-head"><div><span>Learning Studio v44 · živá hodina</span><h2>Visual Reasoning Board</h2><p>Odkrývej pouze jeden vztah najednou. Nejdřív prediction, potom evidence, až nakonec závěr.</p></div><button type="button" class="btn primary small" data-v44-present>Prezentovat bez rušení</button></div>
      <div class="v44-teacher-pulse"><article><span>Největší bottleneck</span><strong><?=e((string)$summary['bottleneck_label'])?></strong><small>Sem teď zaměř krátké vysvětlení.</small></article><?php foreach(['prediction'=>'Předpověď','experiment'=>'Manipulace','model'=>'Model','explain'=>'Vysvětlení','transfer'=>'Transfer'] as $k=>$label):$n=(int)($summary['progress'][$k]??0);?><article><span><?=e($label)?></span><strong><?=$n?> / <?= (int)$summary['total'] ?></strong><small><?=round($n/$total*100)?> % evidence</small></article><?php endforeach;?></div>
      <div class="v44-reveal-board"><article class="revealed"><span>1 · SITUACE</span><strong><?=e((string)$spec['problem'])?></strong></article><article><span>2 · STOP & PREDICT</span><strong><?=e((string)$spec['lab']['question'])?></strong></article><article><span>3 · PRINCIP</span><strong><?=e((string)$spec['big_idea'])?></strong></article><article><span>4 · KONTRAST</span><strong><?=e((string)$spec['lab']['compare']['why'])?></strong></article><article><span>5 · TRANSFER</span><strong><?=e((string)$spec['lab']['transfer'])?></strong></article></div>
      <div class="v44-actions"><button type="button" class="btn secondary small" data-v44-reveal-next>Odkrýt další →</button><button type="button" class="btn secondary small" data-v44-reveal-reset>Začít znovu</button></div>
    </section><?php
}
