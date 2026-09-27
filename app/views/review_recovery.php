<?php

declare(strict_types=1);

/**
 * ?view=review, recovery, create_challenge.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'review') {
    guarded_study_redirect();
    $studentKey=adaptive_student_key((string)$classId);
    $tourMap=is_array($knowledgeTours[$classId]??null)?$knowledgeTours[$classId]:[];
    $queue=adaptive_retrieval_queue((string)$classId,$studentKey,$module,$tourMap,3);
    $result=$_SESSION['adaptive_retrieval_result']??null; unset($_SESSION['adaptive_retrieval_result']);
    render_header(tr('3min opakování'),$module);
    ?>
    <section class="adaptive-review-shell">
      <header class="adaptive-review-hero"><div><div class="eyebrow"><?=e(tr('Spaced Retrieval · 3 minuty'))?></div><h1><?=e(tr('Krátce si vybav, co už jsi jednou pochopil/a.'))?></h1><p><?=e(tr('Bez poznámek. Když něco nevyjde, nic se neděje — systém téma jen vrátí dřív. Nejde o známku ani XP.'))?></p></div><a class="btn secondary" href="?view=dashboard">← <?=e(tr('Přehled'))?></a></header>
      <?php if(is_array($result)): ?><div class="adaptive-result-banner"><strong><?= (int)$result['score'] ?> / <?= (int)$result['total'] ?></strong><span><?= (int)$result['score']===(int)$result['total']?e(tr('Skvělé. Další opakování se posune dál.')):e(tr('Témata, která nevyšla, se vrátí brzy znovu.')) ?></span></div><?php endif; ?>
      <?php if(!$queue): ?><section class="adaptive-empty"><span>✓</span><h2><?=e(tr('Dnes není co opakovat.'))?></h2><p><?=e(tr('Nejdřív dokonči pár Knowledge Tours. Retrieval se potom začne skládat automaticky.'))?></p><a class="btn primary" href="?view=knowledgebase"><?=e(tr('Otevřít Materiály →'))?></a></section>
      <?php else: ?><form method="post" class="adaptive-review-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="adaptive_retrieval_submit">
        <?php foreach($queue as $i=>$item): $check=$item['check']; ?><article class="adaptive-review-card"><div class="adaptive-review-count"><?=str_pad((string)($i+1),2,'0',STR_PAD_LEFT)?></div><div><span<?=edu_content_lang_attr()?>><?=e((string)($item['article']['title']??$item['topic']))?></span><h2<?=edu_content_lang_attr()?>><?=e((string)($check['q']??tr('Ověř si princip')))?></h2><div class="adaptive-review-options"><?php foreach((array)$check['options'] as $oi=>$option): ?><label><input type="radio" name="answers[<?=e((string)$item['topic'])?>]" value="<?= (int)$oi ?>" required><span<?=edu_content_lang_attr()?>><?=e((string)$option)?></span></label><?php endforeach; ?></div><div class="ml-quiz-confidence"><span><?=e(tr('Jak moc si věříš?'))?></span><label><input type="radio" name="confidence[<?=e((string)$item['topic'])?>]" value="1" required> <?=e(tr('Spíš hádám'))?></label><label><input type="radio" name="confidence[<?=e((string)$item['topic'])?>]" value="2"> <?=e(tr('Docela'))?></label><label><input type="radio" name="confidence[<?=e((string)$item['topic'])?>]" value="3"> <?=e(tr('Jsem si jistý/á'))?></label></div></div></article><?php endforeach; ?>
        <div class="adaptive-review-submit"><button class="btn primary" type="submit"><?=e(tr('Vyhodnotit 3min opakování →'))?></button><small><?=e(tr('Správné odpovědi se neukládají jako známka. Jen určují, kdy se téma vrátí.'))?></small></div>
      </form><?php endif; ?>
    </section>
    <?php render_footer(); exit;
}

if ($view === 'recovery') {
    guarded_study_redirect();
    $studentKey=adaptive_student_key((string)$classId);$date=(string)($_GET['date']??'');
    $plan=adaptive_recovery_plan((string)$classId,$studentKey,$date,$extendedLessons,$nextLessons,$module);
    if(!$plan){$_SESSION['flash']=tr('Recovery Path pro tuto hodinu nebyl nalezen.');redirect_to('?view=calendar');}
    render_header(tr('Recovery Path'),$module);
    ?>
    <section class="recovery-shell">
      <header class="recovery-hero"><div><div class="eyebrow"><?=tr_html('Chyběl/a jsem · {datum}',['datum'=>e(date('d.m.Y',strtotime($date)))])?></div><h1<?=edu_content_lang_attr()?>><?=e((string)($plan['lesson']['title']??tr('Zmeškaná lekce')))?></h1><p><?=e(tr('Nemusíš dohánět 90 minut videa nebo textu. Projdi pět krátkých kroků a ověř si, že se můžeš bezpečně vrátit do běžné výuky.'))?></p></div><div class="recovery-progress"><strong><?= (int)$plan['done'] ?>/<?= (int)$plan['total'] ?></strong><span><?=e(tr('kroků'))?></span></div></header>
      <div class="recovery-roadmap">
      <?php foreach($plan['steps'] as $i=>$step): $done=!empty($step['done']); $topic=(string)($step['topic']??''); ?><article class="recovery-step <?=$done?'done':''?>"><i><?=$done?'✓':($i+1)?></i><div><span<?=edu_content_lang_attr()?>><?=e((string)$step['title'])?></span><p<?=edu_content_lang_attr()?>><?=e((string)$step['text'])?></p><?php if($topic!==''&&isset($module['knowledgebase'][$topic])): ?><a href="<?=e(module_url('kb_lesson',['topic'=>$topic,'return'=>'recovery','recovery_date'=>$date]))?>"><?=tr_html('Otevřít {nazev} →',['nazev'=>edu_cs((string)$module['knowledgebase'][$topic]['title'])])?></a><?php elseif((string)$step['id']==='retrieve'): ?><a href="?view=review"><?=e(tr('Spustit 3min opakování →'))?></a><?php endif; ?></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="adaptive_recovery_step"><input type="hidden" name="date" value="<?=e($date)?>"><input type="hidden" name="step" value="<?=e((string)$step['id'])?>"><input type="hidden" name="done" value="<?=$done?'0':'1'?>"><?php if((string)$step['id']==='ready'): ?><input name="reflection" maxlength="1000" value="<?=e((string)$plan['reflection'])?>" placeholder="<?=e(tr('Jednou větou: co bylo nejdůležitější / co ještě není jasné?'))?>"><?php endif; ?><button class="btn <?=$done?'secondary':'primary'?> small" type="submit"><?=$done?e(tr('Vrátit krok')):e(tr('Mám hotovo'))?></button></form></article><?php endforeach; ?>
      </div>
      <?php if($plan['complete']): ?><div class="recovery-complete"><span>✓</span><div><strong><?=e(tr('Recovery Path dokončen.'))?></strong><p><?=e(tr('Můžeš pokračovat v běžném kurzu. Pokud něco pořád není jasné, použij u Knowledge Tour „Vysvětli jinak“ nebo EDU Tutora.'))?></p></div><a class="btn primary" href="?view=dashboard"><?=e(tr('Pokračovat →'))?></a></div><?php endif; ?>
    </section>
    <?php render_footer(); exit;
}

if ($view === 'create_challenge') {
    guarded_study_redirect();
    render_header(tr('Vytvořit challenge'),$module);
    ?>
    <section class="ml-create-challenge-shell"><header class="adaptive-review-hero"><div><div class="eyebrow"><?=e(tr('Student-created challenge'))?></div><h1><?=e(tr('Vytvoř problém, který musí někdo opravdu pochopit.'))?></h1><p><?=e(tr('Nejdřív navrhni situaci, potom možnosti a vysvětli, proč je správný závěr správný. Učitel challenge před zveřejněním schválí.'))?></p></div><a class="btn secondary" href="?view=dashboard">← <?=e(tr('Přehled'))?></a></header>
    <form method="post" class="ml-card ml-create-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_student_scenario"><label><?=e(tr('Název'))?><input name="title" required maxlength="180"></label><label><?=e(tr('Situace'))?><textarea name="brief" rows="4" required maxlength="1000" placeholder="<?=e(tr('Co student vidí? Co ještě neví?'))?>"></textarea></label><div class="ml-form-grid"><label><?=e(tr('Možnost A'))?><input name="choice_0" required></label><label><?=e(tr('Možnost B'))?><input name="choice_1" required></label><label><?=e(tr('Možnost C'))?><input name="choice_2"></label><label><?=e(tr('Správná'))?><select name="correct"><option value="0">A</option><option value="1">B</option><option value="2">C</option></select></label></div><label><?=e(tr('Proč?'))?><textarea name="why" rows="3" placeholder="<?=e(tr('Jaký důkaz odlišuje správný závěr od ostatních?'))?>"></textarea></label><button class="btn primary"><?=e(tr('Odeslat ke schválení'))?></button></form></section>
    <?php render_footer(); exit;
}
