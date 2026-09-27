<?php

declare(strict_types=1);

/**
 * ?view=test a ?view=result – startovní test.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'test') {
    $test = active_test();
    if (!$test || $test['class_id'] !== $classId) {
        redirect_to('?view=dashboard');
    }
    $index = (int)$test['index'];
    $question = $module['questions'][$index] ?? null;
    if (!is_array($question)) {
        redirect_to('?view=dashboard');
    }
    $feedback = $test['feedback'] ?? null;
    $total = count($module['questions']);
    $progress = (int)round((($index + ($feedback ? 1 : 0)) / $total) * 100);
    $optionOrder = test_option_order($test, $question);
    $displayOptions = balanced_quiz_option_labels((array)$question['options'], $question['correct'], subject_family($module));
    render_header(tr('Test'), $module);
    ?>
    <section class="assessment-page main-test-page">
        <div class="assessment-rail">
            <div class="assessment-context"><span><?= e(tr('Startovní diagnostika')) ?></span><strong><?= tr_html('Otázka {i} z {n}',['i'=>(string)($index + 1),'n'=>(string)$total]) ?></strong></div>
            <div class="assessment-progress"><i><em style="width:<?= $progress ?>%"></em></i><span><?= $progress ?> %</span></div>
            <div class="u51-score-chip" title="<?= e(tr('Za každou správnou odpověď 1 bod')) ?>"><b><?= (int)($test['correct'] ?? 0) ?></b> / <?= tr_html('{n} b.',['n'=>(string)$total]) ?></div>
            <div class="assessment-lock-chip"><?= e(tr('KB zamčeno po dobu testu')) ?></div>
        </div>
        <?php if ($flash !== ''): ?><div class="notice compact-notice"><?= e($flash) ?></div><?php endif; ?>
        <div class="assessment-layout">
            <aside class="assessment-demo-panel">
                <?php render_assessment_visual((string)$classId, (string)($question['kb'] ?? $question['id'] ?? ''), (string)$question['question']); ?>
                <div class="quiz-method-card"><span><?= e(tr('Jak přemýšlet')) ?></span><ol><li><?= e(tr('Nejdřív si vytvoř vlastní závěr.')) ?></li><li><?= e(tr('Pak porovnej všechny možnosti.')) ?></li><li><?= e(tr('Nehledej „nejdelší“ odpověď — pořadí je náhodné.')) ?></li></ol></div>
            </aside>
            <article class="assessment-question-panel<?= $feedback ? ' has-feedback' : '' ?>">
                <div class="question-kicker">Q<?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?> · <?= e((string)($question['kb'] ?? tr('základ'))) ?> · <?= e(tr('1 bod')) ?></div>
                <h1<?= edu_content_lang_attr() ?>><?= e((string)$question['question']) ?></h1>
                <?php if (!$feedback): ?>
                    <p class="assessment-instruction"><?= e(tr('Vyber jednu nejlepší odpověď. Možnosti jsou při spuštění testu náhodně promíchané.')) ?></p>
                    <form method="post" class="assessment-form">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="answer">
                        <div class="assessment-answer-grid main-test-grid">
                            <?php foreach ($optionOrder as $displayIndex => $key): $label=(string)$displayOptions[$key]; ?>
                                <label class="assessment-answer">
                                    <input type="radio" name="answer" value="<?= e((string)$key) ?>" required>
                                    <span><?= chr(65 + (int)$displayIndex) ?></span>
                                    <strong<?= edu_content_lang_attr() ?>><?= e($label) ?></strong>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn primary assessment-submit" type="submit"><?= e(tr('Ověřit odpověď →')) ?></button>
                    </form>
                <?php else: ?>
                    <div class="assessment-feedback <?= $feedback['correct'] ? 'ok' : 'bad' ?>">
                        <div class="feedback-mark"><?= $feedback['correct'] ? '✓' : '!' ?></div>
                        <div>
                            <span><?= $feedback['correct'] ? e(tr('Správný závěr · +1 bod')) : e(tr('Tady je potřeba oprava · 0 bodů')) ?></span>
                            <?php if (!$feedback['correct']): ?><strong><?= tr_html('Správně: {odpoved}',['odpoved'=>edu_cs((string)$question['options'][$question['correct']])]) ?></strong><?php endif; ?>
                            <p<?= edu_content_lang_attr() ?>><?= e((string)$feedback['explanation']) ?></p>
                            <small><?= e(tr('Po dokončení testu se odemkne doporučené vysvětlení v Materiály.')) ?></small>
                        </div>
                    </div>
                    <form method="post" class="assessment-next-form">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="continue_test">
                        <button class="btn primary assessment-submit" type="submit"><?= $index + 1 >= $total ? e(tr('Zobrazit výsledek →')) : e(tr('Další otázka →')) ?></button>
                    </form>
                <?php endif; ?>
                <form method="post" class="assessment-abort" onsubmit="return confirm('<?= e(tr('Opravdu ukončit test? Rozpracovaný výsledek se neuloží.')) ?>');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="abort_test">
                    <button class="link-button danger" type="submit"><?= e(tr('Ukončit test')) ?></button>
                </form>
            </article>
        </div>
    </section>
    <?php
    render_footer();
    exit;
}

if ($view === 'result') {
    $result = $_SESSION['next_result'] ?? null;
    if (!is_array($result) || ($result['class_id'] ?? null) !== $classId) {
        redirect_to('?view=dashboard');
    }
    $questionsById = [];
    foreach ($module['questions'] as $q) {
        $questionsById[$q['id']] = $q;
    }
    $wrongTopics = [];
    foreach ($result['answers'] as $a) {
        if (empty($a['correct']) && isset($questionsById[$a['question_id']])) {
            $wrongTopics[$questionsById[$a['question_id']]['kb']] = true;
        }
    }
    render_header(tr('Výsledek'), $module);
    ?>
    <section class="result-hero">
        <div class="score-ring"><strong><?= (int)$result['score'] ?></strong><span>/ <?= (int)$result['max_score'] ?></span></div>
        <div>
            <div class="eyebrow"><?= e(tr('Hotovo · bez známky')) ?></div>
            <h1><?= e(tr('Teď už víš, co drží a co má smysl zopakovat.')) ?></h1>
            <?php $resultScorePhrase = tr('{skore} z {max} bodů', ['skore'=>(string)(int)$result['score'],'max'=>(string)(int)$result['max_score']]); ?>
            <p><?= tr_html('Získáno {vysledek} ({procenta} %). Každá správná odpověď = 1 bod. Výsledek je pouze orientační.',['vysledek'=>'<strong>'.e($resultScorePhrase).'</strong>','procenta'=>(string)((int)$result['max_score'] > 0 ? (int)round((int)$result['score'] / (int)$result['max_score'] * 100) : 0)]) ?></p>
        </div>
    </section>

    <?php if ($wrongTopics): ?>
        <section class="panel">
            <h2><?= e(tr('Doporučené části knowledgebase')) ?></h2>
            <div class="topic-links">
                <?php foreach (array_keys($wrongTopics) as $topic): $article = $module['knowledgebase'][$topic] ?? null; if (!$article) continue; ?>
                    <a href="<?= e(module_url('knowledgebase', ['topic' => $topic])) ?>"><?= edu_cs((string)$article['title']) ?><span>→</span></a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <div class="success-banner"><?= e(tr('Bez chyby. Knowledgebase můžeš použít jako referenci pro další praktické úlohy.')) ?></div>
    <?php endif; ?>

    <section class="review-list">
        <h2><?= e(tr('Rekapitulace')) ?></h2>
        <?php foreach ($result['answers'] as $i => $answer): $q = $questionsById[$answer['question_id']] ?? null; if (!$q) continue; ?>
            <details class="review-item <?= $answer['correct'] ? 'correct' : 'incorrect' ?>" <?= !$answer['correct'] ? 'open' : '' ?>>
                <summary><span><?= $answer['correct'] ? '✓' : '!' ?></span><?= edu_cs((string)$q['question']) ?><em class="u51-points <?= $answer['correct'] ? 'ok' : 'bad' ?>"><?= $answer['correct'] ? e(tr('1 b.')) : e(tr('0 b.')) ?></em></summary>
                <div class="review-body">
                    <p><b><?= e(tr('Tvoje odpověď:')) ?></b> <?= edu_cs((string)$q['options'][$answer['selected']]) ?></p>
                    <?php if (!$answer['correct']): ?><p><b><?= e(tr('Správně:')) ?></b> <?= edu_cs((string)$q['options'][$q['correct']]) ?></p><?php endif; ?>
                    <p<?= edu_content_lang_attr() ?>><?= e($q['explanation']) ?></p>
                    <a href="<?= e(module_url('knowledgebase', ['topic' => $q['kb']])) ?>"><?= e(tr('Otevřít vysvětlení v knowledgebase →')) ?></a>
                </div>
            </details>
        <?php endforeach; ?>
    </section>
    <div class="button-row">
        <a class="btn secondary" href="?view=dashboard"><?= e(tr('Zpět na přehled')) ?></a>
        <?php if (in_array($classId, ['class_1a', 'class_2a'], true)): ?>
            <a class="btn primary" href="?view=dashboard"><?= e(tr('Pokračovat dalším krokem')) ?></a>
        <?php elseif (!empty($module['practice'])): ?>
            <form method="post" style="display:inline">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="start_practice">
                <button class="btn primary" type="submit"><?= e(tr('Pokračovat praktickou částí')) ?></button>
            </form>
        <?php else: ?>
            <a class="btn primary" href="?view=knowledgebase"><?= e(tr('Otevřít knowledgebase')) ?></a>
        <?php endif; ?>
    </div>
    <?php
    render_footer();
    exit;
}
