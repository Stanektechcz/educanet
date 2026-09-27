<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function v472_render_corrective_cycle(string $classId,string $studentKey,string $topic,array $probe,array $module,array $tourMap): void
{
    if(!empty($probe['correct']))return;
    $c=v472_corrective_cycle_spec($classId,$studentKey,$topic,$probe,$module,$tourMap);if(!$c)return;
    $similar=(array)$c['similar'];$contrast=(array)$c['contrast'];$transfer=(array)$c['transfer'];$choiceMode=(string)$c['transfer_mode']==='choice';
    ?>
    <section class="corrective-cycle" data-corrective-cycle>
      <header class="corrective-head"><div><div class="accelerator-step-tag"><?=e(tr('Opravný cyklus · 3–5 min · bez známky'))?></div><h2><?=e(tr('Chyba je signál: oprav princip a hned ho přenes jinam.'))?></h2><p><?=e(tr('Čtyři krátké kroky. Nevrací tě na začátek lekce a nic z tohoto bloku nemění známku, XP ani Mastery.'))?></p></div><span class="corrective-safe"><?=e(tr('0 bodů · čistě formativní'))?></span></header>

      <div class="corrective-grid">
        <article class="corrective-step"><span class="corrective-index">1</span><div><strong><?=e(tr('Princip'))?></strong><h3><?=e((string)$c['title'])?></h3><p><?=e((string)($c['principle']['text']??''))?></p><?php if((string)($c['principle']['why']??'')!==''):?><p class="corrective-why"><b><?=e(tr('Proč původní odpověď nevyšla:'))?></b> <?=e((string)$c['principle']['why'])?></p><?php endif;?><?php if(!empty($c['principle']['steps'])):?><ul><?php foreach((array)$c['principle']['steps'] as $step):?><li><?=e((string)$step)?></li><?php endforeach;?></ul><?php endif;?></div></article>

        <article class="corrective-step"><span class="corrective-index">2</span><div><strong><?=e(tr('Podobný příklad'))?></strong>
        <?php if(!empty($similar['worked'])):?><h3><?=e((string)$similar['question'])?></h3><p><?=e((string)$similar['scenario'])?></p><div class="corrective-answer"><b><?=e(tr('Model řešení:'))?></b> <?=e((string)$similar['why'])?></div>
        <?php else:?><h3><?=e((string)$similar['question'])?></h3><div class="corrective-options worked"><?php foreach((array)$similar['options'] as $i=>$opt):?><div class="<?=((int)$similar['correct']===$i)?'correct':''?>"><span><?=chr(65+$i)?></span><?=e((string)$opt)?><?=((int)$similar['correct']===$i)?'<b>✓</b>':''?></div><?php endforeach;?></div><?php if((string)($similar['why']??'')!==''):?><div class="corrective-answer"><b><?=e(tr('Proč:'))?></b> <?=e((string)$similar['why'])?></div><?php endif;?><?php endif;?></div></article>

        <article class="corrective-step contrast"><span class="corrective-index">3</span><div><strong><?=e(tr('Kontrastní příklad'))?></strong><h3><?=e(tr('Co přesně odlišuje slepou uličku od správného principu?'))?></h3><div class="contrast-pair"><div class="bad"><small><?=e(tr('Tvoje původní volba / typická past'))?></small><b><?=e((string)($contrast['selected']!==''?$contrast['selected']:$contrast['mistake']))?></b><p><?=e((string)$contrast['mistake'])?></p></div><div class="good"><small><?=e(tr('Rozhodující princip'))?></small><b><?=e((string)($contrast['correct']!==''?$contrast['correct']:tr('Správná aplikace principu')))?></b><p><?=e((string)$contrast['mental'])?></p></div></div></div></article>

        <article class="corrective-step transfer"><span class="corrective-index">4</span><div><strong><?=e(tr('Transfer do nové situace'))?></strong>
        <?php if($choiceMode):?><h3><?=e((string)$transfer['question'])?></h3><div class="corrective-options" data-corrective-transfer data-correct="<?= (int)$transfer['correct'] ?>" data-why="<?=e((string)($transfer['why']??''))?>"><?php foreach((array)$transfer['options'] as $i=>$opt):?><label><input type="radio" name="v472_transfer_choice" value="<?=$i?>"><span><?=e((string)$opt)?></span></label><?php endforeach;?><button type="button" class="btn secondary small" data-corrective-check><?=e(tr('Ověřit přenos'))?></button><div class="corrective-transfer-result" data-corrective-result hidden></div></div>
        <?php else:?><h3><?=e((string)$transfer['prompt'])?></h3><textarea rows="3" data-corrective-transfer-note placeholder="<?=e(tr('Co bys udělal/a v nové situaci a proč?'))?>"></textarea><details class="corrective-criteria"><summary><?=e(tr('Jak si odpověď zkontrolovat'))?></summary><ul><?php foreach((array)$transfer['criteria'] as $criterion):?><li><?=e((string)$criterion)?></li><?php endforeach;?></ul></details><?php endif;?>
        <form method="post" class="corrective-complete" data-corrective-complete><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="coach_corrective_complete"><input type="hidden" name="topic" value="<?=e($topic)?>"><input type="hidden" name="transfer_outcome" value="self_checked" data-corrective-outcome><button class="btn primary" type="submit" data-corrective-finish disabled><?=e(tr('Cyklus hotový — pokračovat'))?></button><button class="btn secondary" type="submit" name="transfer_outcome" value="needs_review"><?=e(tr('Ještě potřebuji oporu'))?></button></form>
        </div></article>
      </div>
    </section>
    <?php
}
