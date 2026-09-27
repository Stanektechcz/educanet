<?php

declare(strict_types=1);

/**
 * ?view=extra_challenge.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'extra_challenge') {
    $extra = $module['practice']['extra'] ?? null;
    $practiceResult = $_SESSION['next_practice_result'] ?? null;
    if (!is_array($extra) || !is_array($practiceResult) || ($practiceResult['class_id'] ?? null) !== $classId) {
        $_SESSION['flash'] = tr('Extra challenge se odemkne až po dokončení hlavní praktické části.');
        redirect_to('?view=dashboard');
    }
    $submitted = $_SESSION['next_extra_submission'] ?? null;
    render_header(tr('Extra challenge na známku'), $module);
    ?>
    <?php if ($flash !== ''): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
    <section class="hero">
        <div class="eyebrow"><?= edu_cs((string)$module['name']) ?> · <?= e(tr('dobrovolné · lze hodnotit známkou')) ?></div>
        <h1<?= edu_content_lang_attr() ?>><?= e($extra['title']) ?></h1>
        <p<?= edu_content_lang_attr() ?>><?= e($extra['intro']) ?></p>
        <div class="stats-row">
            <div class="stat"><span><?= e(tr('Doporučený čas')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e($extra['duration']) ?></strong></div>
            <div class="stat"><span><?= e(tr('Maximum')) ?></span><strong><?= e(trn(['one'=>'{n} bod','few'=>'{n} body','other'=>'{n} bodů'],(int)$extra['max_points'])) ?></strong></div>
            <div class="stat wide"><span><?= e(tr('Pravidlo')) ?></span><strong><?= e(tr('Známka je za kvalitu řešení, ne za rychlost dokončení.')) ?></strong></div>
        </div>
    </section>

    <section class="panel">
        <div class="eyebrow"><?= e(tr('Zadání incidentu')) ?></div>
        <h2<?= edu_content_lang_attr() ?>><?= e($extra['brief']) ?></h2>
        <div class="evidence-box">
            <div class="evidence-title"><?= e(tr('Dostupné důkazy / výpisy')) ?></div>
            <pre><?php foreach (($extra['evidence'] ?? []) as $line) echo e((string)$line) . "\n"; ?></pre>
        </div>
    </section>

    <section class="panel">
        <h2><?= e(tr('Co musí řešení obsahovat')) ?></h2>
        <ol class="check-list static">
            <?php foreach (($extra['requirements'] ?? []) as $item): ?><li<?= edu_content_lang_attr() ?>><?= e((string)$item) ?></li><?php endforeach; ?>
        </ol>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div><div class="eyebrow"><?= e(tr('Hodnoticí rubrika')) ?></div><h2><?= e(trn(['one'=>'{n} bod','few'=>'{n} body','other'=>'{n} bodů'],(int)$extra['max_points'])) ?></h2></div>
            <div class="duration-pill"><?= e(tr('na známku')) ?></div>
        </div>
        <div class="timeline-grid">
            <?php foreach (($extra['rubric'] ?? []) as $row): ?>
                <div class="timeline-item"><strong><?= e(tr('{n} b',['n'=>(int)$row['points']])) ?></strong><span<?= edu_content_lang_attr() ?>><?= e($row['area']) ?></span><small<?= edu_content_lang_attr() ?>><?= e($row['text']) ?></small></div>
            <?php endforeach; ?>
        </div>
        <div class="topic-links">
            <?php foreach (($extra['grade_scale'] ?? []) as $scale): ?><span class="soft-warning"<?= edu_content_lang_attr() ?>><?= e((string)$scale) ?></span><?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2><?= e(tr('Povolená knowledgebase')) ?></h2>
        <p><?= e(tr('U známkovaného challenge nejsou automatické nápovědy, ale dokumentaci můžeš používat. V reportu musí být vidět vlastní práce s důkazy.')) ?></p>
        <div class="topic-links">
            <?php foreach (($extra['kb'] ?? []) as $kbKey): $article = $module['knowledgebase'][$kbKey] ?? null; if (!$article) continue; ?>
                <a href="<?= e(module_url('knowledgebase', ['topic' => $kbKey])) ?>" target="_blank" rel="noopener"><?= edu_cs((string)$article['title']) ?><span>↗</span></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel submit-panel" id="submit">
        <div class="section-head"><span><?= e(tr('Odevzdání na známku')) ?></span><h2>Incident report</h2></div>
        <?php if (is_array($submitted) && ($submitted['class_id'] ?? null) === $classId): ?>
            <div class="success-banner"><?= tr_html('Poslední verze byla odevzdána k hodnocení {kdy}. Pokud chceš řešení dopracovat, můžeš odeslat novou verzi.',['kdy'=>e((string)($submitted['submitted_at'] ?? ''))]) ?></div>
        <?php endif; ?>
        <form method="post" class="form-panel">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="submit_extra">
            <?php foreach (($extra['fields'] ?? []) as $field): ?>
                <label<?= edu_content_lang_attr() ?>><?= e($field['label']) ?>
                    <textarea name="<?= e($field['name']) ?>" rows="6" minlength="<?= (int)$field['min'] ?>" maxlength="6000" required placeholder="<?= e($field['placeholder'] ?? '') ?>"<?= edu_content_lang_attr() ?>></textarea>
                </label>
            <?php endforeach; ?>
            <div class="soft-warning"><strong><?= e(tr('Odesláním žádáš o hodnocení tohoto dobrovolného úkolu.')) ?></strong> <?= e(tr('Učitel přidělí body podle rubriky výše.')) ?></div>
            <button class="btn primary" type="submit"><?= e(tr('Odevzdat Extra challenge k hodnocení')) ?></button>
        </form>
    </section>
    <div class="button-row">
        <a class="btn secondary" href="?view=dashboard"><?= e(tr('Zpět na přehled')) ?></a>
        <a class="btn secondary" href="?view=knowledgebase">Knowledgebase</a>
    </div>
    <?php
    render_footer();
    exit;
}
