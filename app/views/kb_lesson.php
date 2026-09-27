<?php

declare(strict_types=1);

/**
 * ?view=kb_lesson – kroková lekce tématu.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'kb_lesson') {
    guarded_study_redirect();
    $referenceMode = isset($_GET['reference']) && (string)$_GET['reference'] === '1';
    $returnToNext = isset($_GET['return']) && (string)$_GET['return'] === 'next';
    $returnRecoveryDate = (isset($_GET['return']) && (string)$_GET['return'] === 'recovery' && isset($_GET['recovery_date'])) ? (string)$_GET['recovery_date'] : '';
    $allowedKbClasses = allowed_subject_class_ids((string)$classId, $modules);
    $requestedKbClass = isset($_GET['kb_class']) && is_string($_GET['kb_class']) ? $_GET['kb_class'] : (string)$classId;
    $kbClassId = isset($modules[$requestedKbClass]) && in_array($requestedKbClass, $allowedKbClasses, true) ? $requestedKbClass : (string)$classId;
    if ($kbClassId !== (string)$classId) $referenceMode = true;
    $kbModule = $modules[$kbClassId];
    $topic = isset($_GET['topic']) && is_string($_GET['topic']) ? $_GET['topic'] : '';
    if ($topic === '' || !isset($kbModule['knowledgebase'][$topic])) {
        redirect_to(module_url('knowledgebase', array_filter(['reference'=>$referenceMode?1:null,'kb_class'=>$kbClassId!==(string)$classId?$kbClassId:null])));
    }
    $kbKeys = array_keys((array)$kbModule['knowledgebase']);
    $tourMap = is_array($knowledgeTours[$kbClassId] ?? null) ? $knowledgeTours[$kbClassId] : [];
    $simulationMap = is_array($simulations[$kbClassId] ?? null) ? $simulations[$kbClassId] : [];
    $firstIncompleteTopic = learning_first_incomplete_topic($kbClassId, $kbModule, $simulationMap);
    $firstIncompleteIndex = $firstIncompleteTopic !== null ? array_search($firstIncompleteTopic, $kbKeys, true) : false;
    $requestedIndex = array_search($topic, $kbKeys, true);
    if (!$referenceMode && $kbClassId === (string)$classId && $firstIncompleteIndex !== false && $requestedIndex !== false && (int)$requestedIndex > (int)$firstIncompleteIndex) {
        $_SESSION['flash'] = tr('Tato lekce je zatím zamčená. Dokonči nejdřív předchozí Knowledge Tour.');
        redirect_to(module_url('kb_lesson', ['topic'=>$firstIncompleteTopic]));
    }
    $currentIndex = $requestedIndex === false ? 0 : (int)$requestedIndex;
    $article = $kbModule['knowledgebase'][$topic];
    $tour = is_array($tourMap[$topic] ?? null) ? $tourMap[$topic] : [
        'time'=>tr('5–10 min'),'level'=>tr('Základ'),'mental'=>(string)($article['summary']??''),
        'steps'=>$article['body']??[],'mistakes'=>[],'visual'=>['type'=>'flow','items'=>[]],
    ];
    $simulation = is_array($simulationMap[$topic] ?? null) ? $simulationMap[$topic] : null;
    $progress = learning_kb_progress($kbClassId, $topic);
    $goalsSource = is_array($tour['goals'] ?? null) ? $tour['goals'] : (is_array($tour['steps'] ?? null) ? $tour['steps'] : []);
    $goals=[]; foreach($goalsSource as $goal){$goal=trim((string)$goal); if($goal==='')continue; $goals[]=$goal; if(count($goals)>=3)break;}
    if(!$goals && !empty($article['summary'])) $goals[]=(string)$article['summary'];
    $prevKey = $currentIndex > 0 ? (string)$kbKeys[$currentIndex-1] : '';
    $nextKey = $currentIndex < count($kbKeys)-1 ? (string)$kbKeys[$currentIndex+1] : '';
    $backParams = array_filter(['reference'=>$referenceMode?1:null,'kb_class'=>$kbClassId!==(string)$classId?$kbClassId:null,'return'=>$returnToNext?'next':($returnRecoveryDate!==''?'recovery':null),'recovery_date'=>$returnRecoveryDate!==''?$returnRecoveryDate:null]);
    render_header((string)$article['title'], $module);
    ?>
    <section class="kb-lesson-screen">
        <header class="kb-lesson-toolbar">
            <a class="kb-lesson-back" href="<?= e(module_url('knowledgebase',$backParams)) ?>">← <?= e(tr('Knihovna')) ?></a>
            <div class="kb-lesson-title"><span><?= edu_cs((string)$kbModule['name']) ?> · <?= $currentIndex+1 ?>/<?= count($kbKeys) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)$article['title']) ?></strong></div>
            <div class="kb-lesson-meta"><span>⏱ <?= e((string)($tour['time']??tr('5–10 min'))) ?></span><span><?= e((string)($tour['level']??tr('Základ'))) ?></span><?php if(!empty($progress['complete'])):?><span class="done">✓ <?= e(tr('Hotovo')) ?></span><?php endif;?></div>
        </header>
        <div class="kb-lesson-context-strip" aria-label="<?= e(tr('Přehled lekce')) ?>">
            <div class="kb-context-summary"><span><?= e(tr('Cíl lekce')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)$article['summary']) ?></strong></div>
            <div class="kb-context-goals"><span><?= e(tr('Po lekci dokážeš')) ?></span><div<?= edu_content_lang_attr() ?>><?php foreach($goals as $goal):?><b><?= e((string)$goal) ?></b><?php endforeach;?></div></div>
            <details class="kb-lesson-resources kb-resources-popover"><summary><?= e(tr('Video a zdroje')) ?></summary><div class="kb-resources-flyout"><?php render_learning_resources(learning_resource_cards($kbClassId, [$topic]), tr('Doporučené zdroje')); ?></div></details>
        </div>
        <div class="kb-lesson-layout">
            <main class="kb-lesson-main">
                <?php render_kb_tour($kbClassId, $topic, $article, $tour, $simulation, $progress, true); ?>
            </main>
        </div>
        <footer class="kb-lesson-pager">
            <div><?php if($prevKey!==''): $p=array_merge($backParams,['topic'=>$prevKey]); ?><a class="btn secondary" href="<?= e(module_url('kb_lesson',$p)) ?>">← <?= e(tr('Předchozí')) ?></a><?php endif;?></div>
            <div class="kb-lesson-progress-note"><span><?= !empty($progress['complete'])?e(tr('Lekce dokončena')):e(tr('Dokonči všechny kroky a knowledge check')) ?></span></div>
            <div><?php if($nextKey!==''): $p=array_merge($backParams,['topic'=>$nextKey]); $canNext=!empty($progress['complete'])||$referenceMode; ?><?php if($canNext):?><a class="btn primary" data-kb-next-link href="<?= e(module_url('kb_lesson',$p)) ?>"><?= e(tr('Další →')) ?></a><?php else:?><button class="btn primary" data-kb-next-link data-next-url="<?= e(module_url('kb_lesson',$p)) ?>" disabled><?= e(tr('Další je zamčená')) ?></button><?php endif;?><?php endif;?></div>
        </footer>
    </section>
    <?php
    render_footer();
    exit;
}
