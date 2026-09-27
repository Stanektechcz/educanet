<?php

declare(strict_types=1);

/**
 * ?view=case_study – případová studie.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'case_study' && !is_array($completedTestResult)) { $_SESSION['flash'] = tr('Případová studie se odemkne po dokončení startovního testu.'); redirect_to('?view=dashboard'); }

if ($view === 'case_study') {
    $case = $module['practice']['case_study'] ?? null;
    if (!is_array($case)) {
        redirect_to('?view=dashboard');
    }
    render_header((string)($case['title'] ?? tr('Případová studie')), $module);
    ?>
    <section class="case-hero">
        <div>
            <div class="eyebrow"<?= edu_content_lang_attr() ?>><?= e((string)($case['eyebrow'] ?? tr('Případová studie'))) ?></div>
            <h1<?= edu_content_lang_attr() ?>><?= e((string)$case['title']) ?></h1>
            <p<?= edu_content_lang_attr() ?>><?= e((string)($case['summary'] ?? '')) ?></p>
            <div class="case-meta-grid">
                <div><span><?= e(tr('Role')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)($case['role'] ?? tr('Student'))) ?></strong></div>
                <div><span><?= e(tr('Mise')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)($case['mission'] ?? '')) ?></strong></div>
                <div><span><?= e(tr('SLA / cíl')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)($case['sla'] ?? '')) ?></strong></div>
            </div>
        </div>
        <div class="case-reality-badge"><strong><?= e(tr('REALISTICKÁ SIMULACE')) ?></strong><span><?= e(tr('Fiktivní infrastruktura, reálné typy závad, příkazy a diagnostická logika.')) ?></span></div>
    </section>

    <section class="panel case-rules">
        <div class="section-heading compact-heading"><div><div class="eyebrow"><?= e(tr('Pravidla práce')) ?></div><h2><?= e(tr('Nejdřív důkaz, potom změna')) ?></h2></div></div>
        <ul class="check-list">
            <?php foreach (($case['rules'] ?? []) as $rule): ?><li<?= edu_content_lang_attr() ?>><?= e((string)$rule) ?></li><?php endforeach; ?>
        </ul>
    </section>

    <?php render_case_diagram((array)($case['diagram'] ?? [])); ?>

    <section class="timeline-section case-shift-timeline">
        <div class="section-head"><span><?= e(tr('Časová osa')) ?></span><h2><?= $classId === 'class_3a' ? e(tr('Jedna směna, čtyři rozhodnutí')) : e(tr('Incident se vyvíjí v čase')) ?></h2></div>
        <div class="case-ticket-timeline">
            <?php foreach (($case['timeline'] ?? []) as $event): ?>
                <article<?= edu_content_lang_attr() ?>>
                    <div class="case-ticket-time"><time><?= e((string)($event['time'] ?? '')) ?></time><span><?= e((string)($event['priority'] ?? '')) ?></span></div>
                    <div>
                        <div class="case-ticket-id"><?= e((string)($event['ticket'] ?? '')) ?></div>
                        <h3><?= e((string)($event['title'] ?? '')) ?></h3>
                        <p><?= e((string)($event['text'] ?? '')) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php render_terminal_cards((array)($case['terminal'] ?? [])); ?>

    <section class="panel case-transfer">
        <div><div class="eyebrow"><?= e(tr('Přenos do labu')) ?></div><h2><?= e(tr('Teď stejný incident vyřešíš krok za krokem')) ?></h2><p><?= e(tr('V praktické části se topologie i tiket zobrazí znovu. Můžeš použít knowledgebase jako dokumentaci, ale systém po tobě bude chtít zvolit další diagnostický krok a vysvětlí, co výsledek skutečně dokazuje.')) ?></p></div>
        <div class="step-commit"><div><strong><?= e(tr('Briefing dokončen?')) ?></strong><span><?= e(tr('Potvrď až ve chvíli, kdy umíš popsat symptom, topologii a pravidlo „nejdřív důkaz, potom změna“.')) ?></span></div><button type="button" class="btn primary" data-journey-complete="case_study" <?= learning_journey_step_done((string)$classId, 'case_study') ? 'disabled' : '' ?>><?= learning_journey_step_done((string)$classId, 'case_study') ? e(tr('Briefing splněn ✓')) : e(tr('Dokončit briefing')) ?></button></div>
        <div class="button-row">
            <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="start_practice">
                <button class="btn primary" type="submit" <?= learning_journey_step_done((string)$classId, 'case_study') ? '' : 'disabled' ?> data-case-practice-button><?= e(tr('Spustit praktickou laboratoř')) ?></button>
            </form>
            <a class="btn secondary" href="?view=dashboard"><?= e(tr('Zpět na přehled')) ?></a>
        </div>
    </section>
    <?php
    render_footer();
    exit;
}
