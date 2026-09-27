<?php

declare(strict_types=1);

/**
 * ?view=kb_quiz – knowledge check tématu.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'kb_quiz') {
    guarded_study_redirect();
    $referenceMode = isset($_GET['reference']) && (string)$_GET['reference'] === '1';
    $returnToNext = isset($_GET['return']) && (string)$_GET['return'] === 'next';
    $allowedKbClasses = allowed_subject_class_ids((string)$classId, $modules);
    $requestedKbClass = isset($_GET['kb_class']) && is_string($_GET['kb_class']) ? $_GET['kb_class'] : (string)$classId;
    $kbClassId = in_array($requestedKbClass, $allowedKbClasses, true) && isset($modules[$requestedKbClass]) ? $requestedKbClass : (string)$classId;
    if ($kbClassId !== (string)$classId) $referenceMode = true;
    $kbModule = $modules[$kbClassId];
    $topic = isset($_GET['topic']) && is_string($_GET['topic']) ? $_GET['topic'] : '';
    if ($topic === '' || !isset($kbModule['knowledgebase'][$topic])) {
        redirect_to(module_url('knowledgebase', ['reference'=>1, 'kb_class'=>$kbClassId]));
    }
    $tourMap = is_array($knowledgeTours[$kbClassId] ?? null) ? $knowledgeTours[$kbClassId] : [];
    $tour = is_array($tourMap[$topic] ?? null) ? $tourMap[$topic] : [];
    $check = is_array($tour['check'] ?? null) ? $tour['check'] : null;
    if (!is_array($check) || empty($check['options'])) {
        $_SESSION['flash'] = tr('Tento materiál zatím nemá samostatný knowledge check.');
        redirect_to(module_url('kb_lesson', ['topic'=>$topic, 'reference'=>1, 'kb_class'=>$kbClassId]));
    }
    $topicProgress = learning_kb_progress($kbClassId, $topic);
    if (empty($topicProgress['deep'])) {
        $_SESSION['flash'] = tr('Knowledge check se odemkne po dokončení vysvětlení v materiálu.');
        redirect_to(module_url('kb_lesson', ['topic'=>$topic, 'reference'=>1, 'kb_class'=>$kbClassId]));
    }
    $article = $kbModule['knowledgebase'][$topic];
    $optionOrder = shuffled_keys((array)$check['options']);
    $displayCheckOptions = balanced_quiz_option_labels((array)$check['options'], (int)($check['correct'] ?? -1), subject_family($kbModule));
    $kbKeys = array_keys((array)$kbModule['knowledgebase']);
    $idx = array_search($topic, $kbKeys, true);
    $nextTopic = ($idx !== false && $idx < count($kbKeys)-1) ? (string)$kbKeys[(int)$idx+1] : '';
    $backParams = array_filter(['topic'=>$topic, 'reference'=>$referenceMode?1:null, 'return'=>$returnToNext?'next':null]);
    if ($kbClassId !== (string)$classId) $backParams['kb_class']=$kbClassId;
    $nextParams = array_filter(['reference'=>$referenceMode?1:null, 'return'=>$returnToNext?'next':null]);
    if ($nextTopic !== '') $nextParams['topic']=$nextTopic;
    if ($kbClassId !== (string)$classId) $nextParams['kb_class']=$kbClassId;
    render_header(tr('Knowledge check'), $module);
    ?>
    <section class="assessment-page kb-quiz-page" data-kb-quiz data-topic="<?= e($topic) ?>" data-kb-class="<?= e($kbClassId) ?>" data-why="<?= e((string)($check['why'] ?? '')) ?>">
        <div class="assessment-rail">
            <a class="assessment-back" href="<?= e(module_url('kb_lesson',$backParams)) ?>">← <?= e(tr('Zpět k materiálu')) ?></a>
            <div class="assessment-context"><span><?= e(tr('Knowledge check')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)$kbModule['name']) ?> · <?= e((string)$article['title']) ?></strong></div>
            <div class="assessment-xp"><?= e(tr('+15 XP za správný check')) ?></div>
        </div>
        <div class="assessment-layout">
            <aside class="assessment-demo-panel">
                <?php render_assessment_visual($kbClassId, $topic, (string)($check['q'] ?? '')); ?>
                <div class="quiz-method-card"><span><?= e(tr('Jak postupovat')) ?></span><ol><li><?= e(tr('Podívej se na vizuální důkaz.')) ?></li><li><?= e(tr('Vyber závěr, který z něj opravdu plyne.')) ?></li><li><?= e(tr('Pokud si nejsi jistý/á, nepřidávej domněnky.')) ?></li></ol></div>
            </aside>
            <article class="assessment-question-panel">
                <div class="question-kicker"><?= e(tr('Jedna otázka · bez scrollovacího testu')) ?></div>
                <h1<?= edu_content_lang_attr() ?>><?= e((string)($check['q'] ?? tr('Ověř si pochopení'))) ?></h1>
                <p class="assessment-instruction"><?= e(tr('Vyber nejlepší odpověď. Pořadí možností je náhodné a při dalším pokusu se může změnit.')) ?></p>
                <div class="ml-quiz-confidence" data-ml-quiz-confidence><span><?= e(tr('Jak moc si před odpovědí věříš?')) ?></span><button type="button" data-confidence="1"><?= e(tr('Spíš hádám')) ?></button><button type="button" data-confidence="2"><?= e(tr('Docela')) ?></button><button type="button" data-confidence="3"><?= e(tr('Jsem si jistý/á')) ?></button></div>
                <div class="assessment-answer-grid kb-answer-grid">
                    <?php foreach ($optionOrder as $displayIndex => $originalIndex): $label=(string)$displayCheckOptions[$originalIndex]; ?>
                        <button type="button" class="assessment-answer" data-kb-quiz-answer="<?= (int)$originalIndex ?>"><span><?= chr(65 + (int)$displayIndex) ?></span><strong<?= edu_content_lang_attr() ?>><?= e($label) ?></strong></button>
                    <?php endforeach; ?>
                </div>
                <div class="kb-quiz-feedback" data-kb-quiz-feedback hidden></div>
                <div class="kb-quiz-hint" data-kb-quiz-hint hidden><strong><?= e(tr('Nápověda')) ?></strong><p><?= e(tr('Vrať se k principu: správná odpověď musí vysvětlovat pozorovaný stav, ne pouze obsahovat známý odborný pojem.')) ?></p></div>
                <div class="assessment-continue" data-kb-quiz-continue hidden>
                    <a class="btn secondary" href="<?= e(module_url('kb_lesson',$backParams)) ?>"><?= e(tr('Zpět k materiálu')) ?></a>
                    <?php if ($nextTopic !== ''): ?><a class="btn primary" href="<?= e(module_url('kb_lesson',$nextParams)) ?>"><?= e(tr('Pokračovat na další materiál →')) ?></a><?php else: ?><a class="btn primary" href="?view=dashboard"><?= e(tr('Zpět na přehled →')) ?></a><?php endif; ?>
                </div>
            </article>
        </div>
    </section>
    <?php
    render_footer();
    exit;
}
