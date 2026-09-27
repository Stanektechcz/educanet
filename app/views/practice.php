<?php

declare(strict_types=1);

/**
 * ?view=practice a ?view=practice_done – praktická laboratoř.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'practice') {
    $practiceState = active_practice();
    // v51: přímý odkaz „Pokračovat v labu“ spustí praktickou část, pokud jsou splněné předchozí kroky.
    if ((!$practiceState || $practiceState['class_id'] !== $classId) && !empty($module['practice']) && is_array($completedTestResult) && !active_test()) {
        if (!empty($module['practice']['case_study']) && !learning_journey_step_done((string)$classId, 'case_study')) {
            $_SESSION['flash'] = tr('Před praktickou laboratoří projdi briefing případové studie.');
            redirect_to('?view=case_study');
        }
        $_SESSION['next_practice'] = ['active'=>true,'class_id'=>$classId,'task_index'=>0,'step_index'=>0,'answers'=>[],'feedback'=>null,'hint_level'=>0,'current_attempts'=>0,'started_at'=>date(DATE_ATOM)];
        unset($_SESSION['next_practice_result']);
        $practiceState = active_practice();
    }
    if (!$practiceState || $practiceState['class_id'] !== $classId || empty($module['practice'])) {
        redirect_to('?view=dashboard');
    }
    $practiceConfig = $module['practice'];
    $tasks = $practiceConfig['tasks'];
    $taskIndex = (int)$practiceState['task_index'];
    $stepIndex = (int)$practiceState['step_index'];
    $task = $tasks[$taskIndex] ?? null;
    $step = is_array($task) ? ($task['steps'][$stepIndex] ?? null) : null;
    if (!is_array($task) || !is_array($step)) {
        redirect_to('?view=dashboard');
    }
    $totalSteps = 0;
    foreach ($tasks as $t) {
        $totalSteps += count($t['steps'] ?? []);
    }
    $completedSteps = count($practiceState['answers'] ?? []);
    $progress = $totalSteps > 0 ? (int)round(($completedSteps / $totalSteps) * 100) : 0;
    $feedback = $practiceState['feedback'] ?? null;
    $hintLevel = (int)($practiceState['hint_level'] ?? 0);
    $hints = $step['hints'] ?? [];
    $evidence = array_merge($task['evidence'] ?? [], $step['evidence'] ?? []);
    $kbKey = (string)($step['kb'] ?? '');
    $kbArticle = $module['knowledgebase'][$kbKey] ?? null;
    $caseStudy = is_array($practiceConfig['case_study'] ?? null) ? $practiceConfig['case_study'] : null;
    $taskContext = is_array($caseStudy) && is_array($caseStudy['task_meta'][$task['id']] ?? null) ? $caseStudy['task_meta'][$task['id']] : [];
    render_header(tr('Praktická laboratoř'), $module);
    ?>
    <?php if ($flash !== ''): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
    <section class="practice-top">
        <div>
            <div class="eyebrow"><?= tr_html('Praktická část · {trida} · {cas}', ['trida' => edu_cs((string)$module['name']), 'cas' => edu_cs((string)$task['time'])]) ?></div>
            <?php if ($taskContext): ?><div class="incident-strip"<?= edu_content_lang_attr() ?>><span><?= e((string)($taskContext['time'] ?? '')) ?></span><strong><?= e((string)($taskContext['ticket'] ?? 'INC')) ?></strong><em><?= e((string)($taskContext['priority'] ?? '')) ?></em></div><?php endif; ?>
            <h1<?= edu_content_lang_attr() ?>><?= e($task['title']) ?></h1>
            <p<?= edu_content_lang_attr() ?>><?= e($task['scenario']) ?></p>
        </div>
        <?php $u51PracticePoints = u51_practice_points((array)($practiceState['answers'] ?? [])); ?>
        <div class="practice-progress-box"><strong><?= $completedSteps ?> / <?= $totalSteps ?></strong><span><?= e(tr('checkpointů dokončeno')) ?></span><small class="u51-score-chip"><b><?= $u51PracticePoints ?></b> / <?= $totalSteps * U51_PRACTICE_MAX_POINTS ?> <?= e(tr('b.')) ?></small></div>
    </section>
    <div class="progress"><span style="width: <?= $progress ?>%"></span></div>
    <section class="guided-practice-now"><div><span><?= e(tr('TEĎ ŘEŠ JEN TOTO')) ?></span><strong><?= e(tr('Checkpoint {n} z {total}', ['n' => $completedSteps + 1, 'total' => $totalSteps])) ?></strong><p<?= edu_content_lang_attr() ?>><?= e((string)$step['question']) ?></p></div><a class="btn primary" href="#checkpoint"><?= e(tr('Vyřešit checkpoint ↓')) ?></a></section>

    <section class="practice-layout guided-practice-layout">
        <details class="practice-sidebar panel guided-practice-context">
            <summary><span><strong><?= e(tr('Kontext a průběh')) ?></strong><small><?= e(tr('Otevři jen když potřebuješ vidět celý incident nebo plán.')) ?></small></span><b><?= e(tr('Rozbalit')) ?></b></summary><div class="guided-practice-context-body">
            <h2><?= e(tr('Průběh 90 minut')) ?></h2>
            <div class="mini-timeline">
                <?php foreach ($practiceConfig['schedule'] as $segment): ?>
                    <div><strong><?= e($segment['time']) ?></strong><span<?= edu_content_lang_attr() ?>><?= e($segment['title']) ?></span></div>
                <?php endforeach; ?>
            </div>
            <h3><?= e(tr('Incidenty')) ?></h3>
            <ol class="task-nav">
                <?php foreach ($tasks as $i => $navTask): ?>
                    <li class="<?= $i < $taskIndex ? 'done' : ($i === $taskIndex ? 'active' : '') ?>"<?= edu_content_lang_attr() ?>>
                        <span><?= $i < $taskIndex ? '✓' : str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <?= e($navTask['title']) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
            </div>
        </details>

        <div class="practice-main">
            <article class="panel">
                <div class="section-heading compact-heading">
                    <div><div class="eyebrow"><?= e(tr('Cíl incidentu')) ?></div><h2<?= edu_content_lang_attr() ?>><?= e($task['goal']) ?></h2></div>
                    <div class="duration-pill"<?= edu_content_lang_attr() ?>><?= e($task['time']) ?></div>
                </div>
                <?php if (is_array($caseStudy) && !empty($caseStudy['diagram'])): ?>
                    <details class="practice-diagram-toggle">
                        <summary><?= e(tr('Zobrazit topologii incidentu')) ?></summary>
                        <?php render_case_diagram((array)$caseStudy['diagram'], (array)($taskContext['focus'] ?? [])); ?>
                    </details>
                <?php endif; ?>
                <?php if (is_array($caseStudy) && !empty($caseStudy['terminal'])): ?>
                    <?php render_terminal_cards((array)$caseStudy['terminal'], (string)$task['id']); ?>
                <?php endif; ?>
                <?php if ($evidence): ?>
                    <div class="evidence-box">
                        <div class="evidence-title"><?= e(tr('Dostupné důkazy / výpisy')) ?></div>
                        <pre<?= edu_content_lang_attr() ?>><?php foreach ($evidence as $line) echo e((string)$line) . "\n"; ?></pre>
                    </div>
                <?php endif; ?>
            </article>

            <article class="question-card practice-checkpoint" id="checkpoint">
                <div class="question-number"><?= e(tr('KROK {n} / {total} · za {body} b.', ['n' => $stepIndex + 1, 'total' => count($task['steps']), 'body' => max(1, U51_PRACTICE_MAX_POINTS - max(0, (int)($practiceState['current_attempts'] ?? 0) - (is_array($feedback) && !empty($feedback['correct']) ? 1 : 0)) - $hintLevel)])) ?></div>
                <h2<?= edu_content_lang_attr() ?>><?= e($step['question']) ?></h2>

                <?php if (is_array($feedback) && empty($feedback['correct'])): ?>
                    <div class="feedback bad">
                        <strong><?= e(tr('Tahle volba zatím nesedí ke všem důkazům.')) ?></strong>
                        <p><?= tr_html('Vybral/a jsi: {volba}', ['volba' => '<b>' . edu_cs((string)($step['options'][$feedback['selected']] ?? '')) . '</b>']) ?></p>
                        <p><?= e(tr('Zkus krok znovu. Chybný pokus se nepenalizuje; pokud se zasekneš, odkryj postupnou nápovědu.')) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!is_array($feedback) || empty($feedback['correct'])): ?>
                    <form method="post" class="answers">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="practice_answer">
                        <?php foreach ($step['options'] as $key => $label): ?>
                            <label class="answer-option">
                                <input type="radio" name="answer" value="<?= e((string)$key) ?>" required>
                                <span class="option-key"><?= e(strtoupper((string)$key)) ?></span>
                                <span<?= edu_content_lang_attr() ?>><?= e((string)$label) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <button class="btn primary full" type="submit"><?= e(tr('Zkontrolovat řešení')) ?></button>
                    </form>

                    <div class="hint-panel">
                        <div class="hint-head"><strong><?= e(tr('Nápovědy')) ?></strong><span><?= e(tr('{n} / {total} odkryto', ['n' => min($hintLevel, count($hints)), 'total' => count($hints)])) ?></span></div>
                        <?php if ($hintLevel > 0): ?>
                            <ol>
                                <?php foreach (array_slice($hints, 0, $hintLevel) as $hint): ?><li<?= edu_content_lang_attr() ?>><?= e((string)$hint) ?></li><?php endforeach; ?>
                            </ol>
                        <?php else: ?>
                            <p><?= e(tr('Nejdřív se pokus formulovat vlastní hypotézu. Když nevíš, odkryj první nápovědu.')) ?></p>
                        <?php endif; ?>
                        <?php if ($hintLevel < count($hints)): ?>
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="practice_hint">
                                <button class="btn tertiary" type="submit"><?= e(tr('Odkryt nápovědu {n}', ['n' => $hintLevel + 1])) ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="feedback ok">
                        <strong><?= e(tr('Checkpoint splněn · +{body} b.', ['body' => u51_practice_points([['attempts' => (int)($practiceState['current_attempts'] ?? 1), 'hints_used' => $hintLevel]])])) ?></strong>
                        <p<?= edu_content_lang_attr() ?>><?= e((string)$feedback['explanation']) ?></p>
                    </div>
                    <?php if (is_array($kbArticle)): ?>
                        <div class="kb-bridge">
                            <span><?= e(tr('Souvislost v knowledgebase')) ?></span>
                            <a href="<?= e(module_url('knowledgebase', ['topic' => $kbKey])) ?>"<?= edu_content_lang_attr() ?>><?= e($kbArticle['title']) ?> →</a>
                        </div>
                    <?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="continue_practice">
                        <?php
                        $lastStep = $stepIndex + 1 >= count($task['steps']);
                        $lastTask = $taskIndex + 1 >= count($tasks);
                        $continueLabel = $lastTask && $lastStep ? tr('Dokončit dvouhodinový blok') : ($lastStep ? tr('Další incident') : tr('Další krok'));
                        ?>
                        <button class="btn primary full" type="submit"><?= e($continueLabel) ?></button>
                    </form>
                <?php endif; ?>

                <?php if (is_array($kbArticle) && (!is_array($feedback) || empty($feedback['correct']))): ?>
                    <div class="doc-link"><?= e(tr('Dokumentace je v praktické části povolená:')) ?> <a href="<?= e(module_url('knowledgebase', ['topic' => $kbKey])) ?>" target="_blank" rel="noopener"<?= edu_content_lang_attr() ?>><?= e($kbArticle['title']) ?> ↗</a></div>
                <?php endif; ?>
            </article>
        </div>
    </section>

    <form method="post" class="abort-form" onsubmit="return confirm('<?= e(tr('Opravdu ukončit praktickou část? Průběh se neuloží jako dokončený.')) ?>');">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="abort_practice">
        <button class="link-button danger" type="submit"><?= e(tr('Ukončit praktickou část')) ?></button>
    </form>
    <?php
    render_footer();
    exit;
}

if ($view === 'practice_done') {
    $practiceResult = $_SESSION['next_practice_result'] ?? null;
    if (!is_array($practiceResult) || ($practiceResult['class_id'] ?? null) !== $classId || empty($module['practice'])) {
        redirect_to('?view=dashboard');
    }
    $totalHints = 0;
    $extraAttempts = 0;
    foreach ($practiceResult['answers'] as $answer) {
        $totalHints += (int)($answer['hints_used'] ?? 0);
        $extraAttempts += max(0, (int)($answer['attempts'] ?? 1) - 1);
    }
    render_header(tr('Praktická část dokončena'), $module);
    ?>
    <section class="result-hero">
        <div class="score-ring"><strong>✓</strong><span><?= e(tr('90 min')) ?></span></div>
        <div>
            <div class="eyebrow"><?= e(tr('Hotovo · bez známky')) ?></div>
            <h1><?= e(tr('Dvouhodinový blok je dokončený.')) ?></h1>
            <p><?= e(tr('Prošel/prošla jsi {n} kontrolními checkpointy v praktických incidentech.', ['n' => (int)$practiceResult['completed_steps']])) ?></p>
        </div>
    </section>
    <section class="stats-row completion-stats">
        <div class="stat"><span><?= e(tr('Body')) ?></span><strong><?= u51_practice_points((array)$practiceResult['answers']) ?> / <?= (int)$practiceResult['completed_steps'] * U51_PRACTICE_MAX_POINTS ?></strong></div>
        <div class="stat"><span><?= e(tr('Checkpointy')) ?></span><strong><?= (int)$practiceResult['completed_steps'] ?></strong></div>
        <div class="stat"><span><?= e(tr('Použité nápovědy')) ?></span><strong><?= $totalHints ?></strong></div>
        <div class="stat"><span><?= e(tr('Opakované pokusy')) ?></span><strong><?= $extraAttempts ?></strong></div>
    </section>
    <section class="panel">
        <h2><?= e(tr('Co si z hodiny odnést')) ?></h2>
        <p><?= e(tr('Nejdůležitější není zapamatovat si všechny příkazy. Důležité je umět rozdělit problém na vrstvy, formulovat hypotézu a zvolit měření, které ji potvrdí nebo vyvrátí.')) ?></p>
        <div class="topic-links">
            <?php foreach ($module['practice']['tasks'] as $doneTask):
                $firstKb = $doneTask['steps'][0]['kb'] ?? '';
                $article = $module['knowledgebase'][$firstKb] ?? null;
                if (!$article) continue;
            ?>
                <a href="<?= e(module_url('knowledgebase', ['topic' => $firstKb])) ?>"<?= edu_content_lang_attr() ?>><?= e($article['title']) ?><span>→</span></a>
            <?php endforeach; ?>
        </div>
    </section>
    <div class="button-row">
        <a class="btn secondary" href="?view=dashboard"><?= e(tr('Zpět na přehled')) ?></a>
        <a class="btn secondary" href="?view=knowledgebase"><?= e(tr('Otevřít knowledgebase')) ?></a>
        <?php if (!empty($module['practice']['extra'])): ?>
            <a class="btn primary" href="?view=extra_challenge"><?= e(tr('Jsem rychlejší → Extra challenge na známku')) ?></a>
        <?php endif; ?>
    </div>
    <?php
    render_footer();
    exit;
}
