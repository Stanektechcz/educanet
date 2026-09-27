<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function ml_render_worked_example(string $classId,string $studentKey,string $topic,array $article): void
{
    $s=ml_worked_example_spec($classId,$topic,$article);
    ?>
    <section class="ml-card ml-worked" data-ml-worked>
      <header><div><span class="eyebrow"><?=e(tr('Worked example → postupně méně podpory'))?></span><h3<?=edu_content_lang_attr()?>><?=e($s['title'])?></h3><p<?=edu_content_lang_attr()?>><?=e($s['brief'])?></p></div><span class="ml-chip"><?=e(tr('5 kroků'))?></span></header>
      <div class="ml-fade-switch" role="tablist" aria-label="<?=e(tr('Míra podpory'))?>">
        <button type="button" class="active" data-ml-fade="full"><?=e(tr('Ukázat celý vzor'))?></button>
        <button type="button" data-ml-fade="partial"><?=e(tr('Doplň chybějící kroky'))?></button>
        <button type="button" data-ml-fade="independent"><?=e(tr('Zkus samostatně'))?></button>
      </div>
      <div class="ml-worked-grid" data-ml-worked-grid>
        <?php foreach($s['steps'] as $i=>$step): ?><article data-ml-worked-step="<?=$i?>"><i><?=$i+1?></i><div><strong<?=edu_content_lang_attr()?>><?=e($step[0])?></strong><p<?=edu_content_lang_attr()?>><?=e($step[1])?></p><input hidden data-ml-completion-input placeholder="<?=e(tr('Co sem patří podle tebe?'))?>"></div></article><?php endforeach; ?>
      </div>
      <div class="ml-inline-note"><strong><?=e(tr('Princip:'))?></strong> <?=e(tr('nejdřív vidíš celý model, potom část podpory zmizí a nakonec řešíš nový problém sám/sama.'))?></div>
    </section>
    <?php
}

function ml_render_transfer(string $classId,string $studentKey,string $topic,array $article): void
{
    $s=ml_transfer_spec($classId,$studentKey,$topic,$article);
    ?>
    <section class="ml-card ml-transfer" data-ml-transfer data-topic="<?=e($topic)?>">
      <header><div><span class="eyebrow"><?=e(tr('Transfer · stejný princip, nový kontext'))?></span><h3<?=edu_content_lang_attr()?>><?=e($s['question'])?></h3></div><span class="ml-chip"><?=tr_html('varianta {v}',['v'=>e((string)$s['variant'])])?></span></header>
      <div class="ml-confidence"><span><?=e(tr('Než odpovíš: jak moc si věříš?'))?></span><div><?php foreach([1=>tr('Spíš hádám'),2=>tr('Docela'),3=>tr('Jsem si jistý/á')] as $v=>$lab): ?><button type="button" data-ml-confidence="<?=$v?>"><?=e($lab)?></button><?php endforeach; ?></div></div>
      <div class="ml-choice-list"><?php foreach($s['options'] as $i=>$opt): ?><button type="button" data-ml-transfer-choice="<?=$i?>"><i><?=chr(65+$i)?></i><span<?=edu_content_lang_attr()?>><?=e($opt)?></span></button><?php endforeach; ?></div>
      <div class="ml-feedback" data-ml-transfer-feedback hidden></div>
    </section>
    <?php
}

function ml_render_explain_back(string $classId,string $studentKey,string $topic,array $article): void
{
    $saved=ml_store('explain_back')[$classId.'|'.$studentKey.'|'.$topic]??null;
    ?>
    <section class="ml-card ml-explain-back">
      <header><div><span class="eyebrow"><?=e(tr('Explain it back'))?></span><h3<?=edu_content_lang_attr()?>><?=e(ml_explain_back_prompt($classId,$topic,$article))?></h3><p><?=e(tr('Nejde o sloh. Jedna až tři konkrétní věty stačí.'))?></p></div><?php if(is_array($saved)): ?><span class="ml-chip ok"><?=e(tr('uloženo'))?></span><?php endif; ?></header>
      <form method="post" class="ml-inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_explain_back"><input type="hidden" name="topic" value="<?=e($topic)?>"><textarea name="text" rows="3" placeholder="<?=e(tr('Myslím, že… protože… Jak bych to ověřil/a…'))?>"><?=is_array($saved)?e((string)$saved['text']):''?></textarea><button class="btn secondary" type="submit"><?=e(tr('Uložit vysvětlení'))?></button></form>
    </section>
    <?php
}

function ml_render_browser_lab(string $classId,string $studentKey,string $topic,array $article): void
{
    $lab=ml_browser_lab_spec($classId,$topic,$article);
    ?>
    <section class="ml-card ml-browser-lab" data-ml-browser-lab data-lab-type="<?=e($lab['type'])?>" data-topic="<?=e($topic)?>">
      <header><div><span class="eyebrow"><?=e(tr('Browser Lab'))?></span><h3<?=edu_content_lang_attr()?>><?=e($lab['title'])?></h3><p<?=edu_content_lang_attr()?>><?=e($lab['brief'])?></p></div><span class="ml-chip"><?=e(tr('bezpečný sandbox'))?></span></header>
      <?php if($lab['type']==='terminal'): ?>
        <div class="ml-terminal"><div class="ml-terminal-top"><span>scenario</span><strong<?=edu_content_lang_attr()?>><?=e($lab['scenario'])?></strong></div><pre data-ml-terminal-output>$ <span><?=e(tr('Začni příkazem podle své hypotézy.'))?></span></pre><form data-ml-terminal-form><label><span>$</span><input name="command" autocomplete="off" placeholder="status / logs / listen / network / test"></label><button type="submit"><?=e(tr('Spustit'))?></button></form><small data-ml-terminal-hint><?=e(tr('Sandbox nic nespouští na serveru. Simuluje realistické výstupy.'))?></small></div>
      <?php else: ?>
        <div class="ml-visual-editor">
          <div class="ml-visual-controls"><label><?=e(tr('Spacing'))?> <input type="range" min="4" max="40" value="12" data-ml-design="gap"></label><label><?=e(tr('Velikost nadpisu'))?> <input type="range" min="24" max="72" value="38" data-ml-design="size"></label><label><?=e(tr('Šířka'))?> <input type="range" min="280" max="760" value="520" data-ml-design="width"></label><div class="button-row"><button type="button" class="btn secondary small" data-ml-before><?=e(tr('Ukázat před'))?></button><button type="button" class="btn secondary small" data-ml-after><?=e(tr('Ukázat moje změny'))?></button></div></div>
          <div class="ml-design-preview" data-ml-design-preview data-baseline-gap="12" data-baseline-size="38" data-baseline-width="520"><span>EDUCANET PROJECT</span><h4><?=e(tr('Jedna jasná informace'))?></h4><p><?=e(tr('Uprav vztahy, ne dekoraci. Sleduj hierarchy, čitelnost a dostupný prostor.'))?></p><button><?=e(tr('Pokračovat'))?></button></div>
          <div class="ml-inline-note"><?=e(tr('Porovnej změnu s cílem. „Vypadá to jinak“ ještě není evidence, že je to lepší.'))?></div>
        </div>
      <?php endif; ?>
    </section>
    <?php
}

function ml_render_class_tips(string $classId,string $studentKey,string $topic): void
{
    $tips=ml_tips_for_topic($classId,$topic,'published');
    ?>
    <details class="ml-card ml-class-tips"><summary><span><?=e(tr('Mikrotipy třídy'))?></span><strong><?=e(trn(['one'=>'{n} ověřený tip','few'=>'{n} ověřené tipy','other'=>'{n} ověřených tipů'],count($tips)))?></strong></summary><?php if($tips): ?><div class="ml-journal-list"><?php foreach(array_slice(array_reverse($tips),0,5) as $t): ?><article><i>💡</i><div><p<?=edu_content_lang_attr()?>><?=e((string)$t['text'])?></p><small><?=e(tr('schváleno učitelem'))?></small></div></article><?php endforeach; ?></div><?php else: ?><p class="muted"><?=e(tr('Zatím tu není žádný schválený tip. Můžeš přidat vlastní krátkou pomůcku.'))?></p><?php endif; ?><form method="post" class="ml-inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_class_tip"><input type="hidden" name="topic" value="<?=e($topic)?>"><textarea name="text" rows="2" maxlength="700" placeholder="<?=e(tr('Krátký tip, který pomohl tobě – konkrétní a k tématu.'))?>"></textarea><button class="btn secondary small" type="submit"><?=e(tr('Poslat učiteli ke schválení'))?></button></form></details>
    <?php
}

function ml_render_knowledge_map(string $classId,string $topic,array $module): void
{
    $related=ml_related_topics($classId,$topic,$module,5);if(!$related)return;
    ?>
    <details class="ml-card ml-knowledge-map"><summary><span><?=e(tr('Co spolu souvisí?'))?></span><strong><?=e(tr('Mapa pojmů'))?></strong></summary><div class="ml-map"><div class="ml-map-center"<?=edu_content_lang_attr()?>><?=e((string)($module['knowledgebase'][$topic]['title']??$topic))?></div><?php foreach($related as $r): ?><a href="<?=e(module_url('kb_lesson',['topic'=>$r['topic'],'reference'=>1]))?>"<?=edu_content_lang_attr()?>><?=e($r['title'])?></a><?php endforeach; ?></div></details>
    <?php
}

function ml_render_error_journal(string $classId,string $studentKey): void
{
    $rows=ml_error_journal($classId,$studentKey);if(!$rows)return;
    ?>
    <details class="ml-card ml-error-journal"><summary><span><?=e(tr('Moje chyby, které už poznám'))?></span><strong><?=e(trn(['one'=>'{n} mentální model','few'=>'{n} mentální modely','other'=>'{n} mentálních modelů'],count($rows)))?></strong></summary><div class="ml-journal-list"><?php foreach($rows as $r): ?><article><i>↺</i><div><strong<?=edu_content_lang_attr()?>><?=e($r['label'])?></strong><p<?=edu_content_lang_attr()?>><?=e($r['reframe'])?></p><small><?=tr_html('zachyceno {n}×',['n'=>(string)$r['count']])?></small></div></article><?php endforeach; ?></div></details>
    <?php
}

function ml_render_live_student(string $classId,string $studentKey): void
{
    $live=ml_live_active($classId);if(!$live)return;$phase=(string)($live['phase']??'individual');$mine=(array)($live['responses'][$studentKey]??[]);$answered=isset($mine[$phase]);
    ?>
    <section class="ml-card ml-live-card"><header><div><span class="eyebrow"><?=e(tr('Živá otázka ve třídě'))?></span><h3<?=edu_content_lang_attr()?>><?=e((string)$live['question'])?></h3><p><?= $phase==='discuss'?e(tr('Teď porovnej argument se spolužákem. Učitel následně může otevřít druhé hlasování.')):($phase==='revote'?e(tr('Odpověz znovu po krátké diskusi.')):e(tr('Nejdřív odpověz sám/sama.'))) ?></p></div><span class="ml-live-dot">LIVE</span></header><?php if(!$answered && $phase!=='discuss'): ?><form method="post" class="ml-choice-list"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_live_answer"><input type="hidden" name="live_id" value="<?=e((string)$live['id'])?>"><?php foreach((array)$live['options'] as $i=>$opt): ?><button type="submit" name="answer" value="<?=$i?>"><i><?=chr(65+$i)?></i><span<?=edu_content_lang_attr()?>><?=e((string)$opt)?></span></button><?php endforeach; ?></form><?php elseif($answered): ?><div class="ml-inline-note ok">✓ <?=e(tr('Odpověď je uložená. Výsledek třídy ukáže učitel až ve vhodný moment.'))?></div><?php else: ?><div class="ml-inline-note"><?=e(tr('Promluvte si ve dvojici: neříkejte jen volbu, ale jeden konkrétní důkaz pro svůj závěr.'))?></div><?php endif; ?></section>
    <?php
}

function ml_render_custom_scenarios(string $classId): void
{
    $rows=ml_scenarios_for_class($classId,'published');if(!$rows)return;
    ?><section class="ml-card"><header><div><span class="eyebrow"><?=e(tr('Challenge od učitele'))?></span><h3><?=e(tr('Nová situace k procvičení'))?></h3><p><?=e(tr('Nejdřív odhadni jistotu. Správná odpověď sama o sobě nezvyšuje známku; ukládá se pouze evidence mentálního modelu.'))?></p></div><span class="ml-chip"><?=count($rows)?></span></header><div class="ml-custom-grid"><?php foreach(array_slice($rows,-3) as $r): ?><article data-ml-custom-scenario data-scenario-id="<?=e((string)$r['id'])?>"><strong<?=edu_content_lang_attr()?>><?=e((string)$r['title'])?></strong><p<?=edu_content_lang_attr()?>><?=e((string)$r['brief'])?></p><div class="ml-confidence compact"><span><?=e(tr('Jistota'))?></span><div><?php foreach([1=>tr('Hádám'),2=>tr('Docela'),3=>tr('Jistě')] as $v=>$lab): ?><button type="button" data-ml-scenario-confidence="<?=$v?>"><?=e($lab)?></button><?php endforeach; ?></div></div><div class="ml-choice-list compact"><?php foreach((array)$r['choices'] as $i=>$o): ?><button type="button" data-ml-scenario-choice="<?=$i?>"><i><?=chr(65+$i)?></i><span<?=edu_content_lang_attr()?>><?=e((string)$o)?></span></button><?php endforeach; ?></div><div class="ml-feedback" data-ml-scenario-feedback hidden></div></article><?php endforeach; ?></div></section><?php
}

function ml_render_teacher_hub(string $classId): void
{
    global $schoolYear; $currentLesson=ml_current_lesson_number($classId,is_array($schoolYear??null)?$schoolYear:[]);
    $starter=ml_class_starter($classId);$mis=ml_misconception_summary($classId);$groups=ml_intervention_groups($classId);$live=ml_live_active($classId);$goals=ml_goal_rows_for_class($classId);$studentMap=project_students_for_class($classId);$pendingTips=ml_tips_for_class($classId,'pending');
    ?>
    <section class="teacher-hero"><div><div class="eyebrow">Mastery Learning · <?=e(teacher_class_label($classId))?></div><h1>Co má největší výukový efekt právě teď</h1><p>Misconceptions, retrieval, confidence a transfer. Bez skrytého skóre a bez automatického známkování.</p></div><div class="teacher-hero-actions"><a class="btn primary" href="?tab=teach&class=<?=e($classId)?>&lesson=<?=$currentLesson?>">▶ Učit teď</a></div></section>
    <section class="overview-global-kpis"><article><span>Starter</span><strong><?=$starter['minutes']?> min</strong><small><?=e($starter['title'])?></small></article><article><span>Misconceptions</span><strong><?=count($mis)?></strong><small>vzorců k rozboru</small></article><article><span>Mini-skupiny</span><strong><?=count($groups)?></strong><small>podobný problém</small></article><article><span>Live</span><strong><?=$live?'ON':'—'?></strong><small><?=$live?e((string)$live['phase']):'bez aktivní otázky'?></small></article></section>
    <section class="teacher-panel ml-teach-actions"><div class="teacher-panel-head"><div><span>Teach now</span><h2>Jedna akce pro dnešní hodinu</h2></div></div><div class="button-row"><a class="btn primary" href="?tab=teach&class=<?=e($classId)?>&lesson=<?=$currentLesson?>">▶ Spustit aktuální lekci</a><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_failure_inject"><input type="hidden" name="class_id" value="<?=e($classId)?>"><button class="btn secondary" type="submit">⚡ Vložit náhodný problém</button></form></div><p class="muted">Náhodný problém spustí bezpečnou konceptuální situaci jako peer-instruction otázku; nic nemění na serveru ani ve známkách.</p></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Starter</span><h2><?=e($starter['title'])?></h2></div><b><?=$starter['minutes']?> min</b></div><p><?=e($starter['detail'])?></p></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Misconception engine</span><h2>Nejčastější chybné mentální modely</h2></div></div><?php if(!$mis): ?><div class="teacher-empty">Zatím nejsou data z nového learning-event modelu.</div><?php else: ?><div class="ml-teacher-mis"><?php foreach(array_slice($mis,0,8) as $m): ?><article><strong><?=e($m['label'])?></strong><span><?=$m['student_count']?> studentů · <?=$m['count']?> výskytů</span><small><?=e(implode(', ',array_slice($m['topics'],0,3)))?></small></article><?php endforeach; ?></div><?php endif; ?></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Intervention groups</span><h2>Koho má smysl na 5 minut spojit</h2></div></div><?php if(!$groups): ?><div class="teacher-empty">Zatím není potřeba vytvářet mini-skupinu.</div><?php else: ?><div class="ml-intervention-groups"><?php foreach($groups as $g): ?><article><strong><?=e($g['label'])?></strong><p><?=e(implode(' · ',array_column($g['students'],'label')))?></p><small>Krátké vysvětlení + jeden nový příklad + okamžitý transfer.</small></article><?php endforeach; ?></div><?php endif; ?></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Mastery conference</span><h2>Osobní cíle studentů</h2></div><b><?=count($goals)?></b></div><?php if(!$goals): ?><div class="teacher-empty">Studenti zatím nemají uložený mastery cíl.</div><?php else: ?><div class="ml-custom-grid"><?php foreach(array_slice(array_reverse($goals),0,12) as $g): ?><article><strong><?=e((string)($studentMap[(string)($g['student_key']??'')]['label']??($g['student_key']??'student')))?></strong><p><b>Silná stránka:</b> <?=e((string)($g['strength']??''))?></p><p><b>Další fokus:</b> <?=e((string)($g['focus']??''))?></p><small><?=e((string)($g['evidence']??''))?></small></article><?php endforeach; ?></div><?php endif; ?></section>
    <?php if($pendingTips): ?><section class="teacher-panel"><div class="teacher-panel-head"><div><span>Class Knowledge Base</span><h2><?=count($pendingTips)?> tipů čeká na schválení</h2></div><a class="btn secondary small" href="?tab=authoring&class=<?=e($classId)?>">Moderovat →</a></div></section><?php endif; ?>
    <?php ml_render_teacher_live($classId,$live); ?>
    <?php
}

function ml_render_teacher_live(string $classId,?array $live=null): void
{
    $live=$live??ml_live_active($classId);
    ?>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Peer instruction</span><h2>Živá otázka → diskuse → druhé hlasování</h2></div></div>
      <?php if(!$live): ?><form method="post" class="teacher-form-grid"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_live_start"><input type="hidden" name="class_id" value="<?=e($classId)?>"><label class="wide">Otázka<input name="question" required placeholder="Např. Co tento výsledek skutečně dokazuje?"></label><label>Možnost A<input name="option_0" required></label><label>Možnost B<input name="option_1" required></label><label>Možnost C<input name="option_2"></label><label>Správná možnost<select name="correct"><option value="-1">bez automatického odhalení</option><option value="0">A</option><option value="1">B</option><option value="2">C</option></select></label><div class="wide"><button class="btn primary">Spustit individuální hlasování</button></div></form>
      <?php else: $phase=(string)$live['phase'];$dist=ml_live_distribution($live,$phase);$total=array_sum($dist); ?><div class="ml-live-teacher"><h3><?=e((string)$live['question'])?></h3><div class="ml-live-bars"><?php foreach((array)$live['options'] as $i=>$opt): $n=$dist[$i]??0;$pct=$total?round($n/$total*100):0; ?><div><span><?=chr(65+$i)?> · <?=e((string)$opt)?></span><progress max="100" value="<?=$pct?>"></progress><b><?=$n?> · <?=$pct?>%</b></div><?php endforeach; ?></div><form method="post" class="button-row"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_live_phase"><input type="hidden" name="live_id" value="<?=e((string)$live['id'])?>"><input type="hidden" name="class_id" value="<?=e($classId)?>"><?php if($phase==='individual'): ?><button class="btn secondary" name="phase" value="discuss">2 min diskuse ve dvojici</button><?php elseif($phase==='discuss'): ?><button class="btn primary" name="phase" value="revote">Spustit druhé hlasování</button><?php endif; ?><button class="btn secondary" name="phase" value="closed">Ukončit</button></form></div><?php endif; ?>
    </section>
    <?php
}

function ml_render_authoring(string $classId): void
{
    $rows=ml_scenarios_for_class($classId,'');$templates=ml_scenario_templates($classId);$pendingTips=ml_tips_for_class($classId,'pending');
    ?>
    <section class="teacher-hero"><div><div class="eyebrow">Content Authoring Studio</div><h1>Nová situace bez programování PHP</h1><p>Vytvářej Reality/transfer scénáře jako situaci, důkazy, volby a vysvětlení. Publikovat lze jen validní obsah.</p></div></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Šablony</span><h2>Začni od ověřeného typu situace</h2></div></div><div class="ml-template-row"><?php foreach($templates as $key=>$t): ?><button type="button" class="btn secondary small" data-ml-template='<?=e(json_encode($t,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>'><?=e((string)$t['label'])?></button><?php endforeach; ?></div><form method="post" class="teacher-form-grid" data-ml-authoring-form><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_scenario_save"><input type="hidden" name="class_id" value="<?=e($classId)?>"><label class="wide">Název<input name="title" required placeholder="Např. DNS funguje jen přes IP"></label><label class="wide">Situace<textarea name="brief" rows="3" required placeholder="Co student přesně vidí a co ještě neví?"></textarea></label><label>Volba A<input name="choice_0" required></label><label>Volba B<input name="choice_1" required></label><label>Volba C<input name="choice_2"></label><label>Správná<select name="correct"><option value="0">A</option><option value="1">B</option><option value="2">C</option></select></label><label class="wide">Co výsledek dokazuje<textarea name="why" rows="2"></textarea></label><label>Stav<select name="status"><option value="draft">Koncept</option><option value="published">Publikovat třídě</option></select></label><div class="wide"><button class="btn primary">Uložit scénář</button></div></form></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Scénáře</span><h2><?=count($rows)?> položek</h2></div></div><?php if(!$rows): ?><div class="teacher-empty">Zatím žádný vlastní scénář.</div><?php else: ?><div class="ml-custom-grid"><?php foreach(array_reverse($rows) as $r): $v=ml_scenario_validate($r); ?><article><span class="ml-chip <?=e((string)$r['status'])?>"><?=e((string)$r['status'])?></span><strong><?=e((string)$r['title'])?></strong><p><?=e((string)$r['brief'])?></p><small><?=$v['ok']?'✓ validní':'⚠ '.e(implode(' ',$v['errors']))?><?= $v['warnings']?' · '.e(implode(' ',$v['warnings'])):'' ?></small><form method="post" class="button-row"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_scenario_state"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="scenario_id" value="<?=e((string)$r['id'])?>"><?php if((string)$r['status']!=='published'): ?><button class="btn primary small" name="status" value="published">Schválit / publikovat</button><?php endif; ?><?php if((string)$r['status']!=='rejected'): ?><button class="btn secondary small" name="status" value="rejected">Odmítnout</button><?php endif; ?></form></article><?php endforeach; ?></div><?php endif; ?></section>
    <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Class Knowledge Base</span><h2>Studentské mikrotipy ke schválení</h2></div><b><?=count($pendingTips)?></b></div><?php if(!$pendingTips): ?><div class="teacher-empty">Žádný tip nečeká na moderaci.</div><?php else: ?><div class="ml-custom-grid"><?php foreach(array_reverse($pendingTips) as $t): ?><article><span class="ml-chip"><?=e((string)$t['topic'])?></span><p><?=e((string)$t['text'])?></p><form method="post" class="button-row"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_tip_state"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="tip_id" value="<?=e((string)$t['id'])?>"><button class="btn primary small" name="status" value="published">Schválit</button><button class="btn secondary small" name="status" value="rejected">Odmítnout</button></form></article><?php endforeach; ?></div><?php endif; ?></section>

    <?php
}
