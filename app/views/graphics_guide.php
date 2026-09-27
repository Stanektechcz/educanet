<?php

declare(strict_types=1);

/**
 * ?view=graphics_guide – odevzdání grafického projektu (1.A/2.A).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'graphics_guide' && in_array($classId, ['class_1a', 'class_2a'], true) && !learning_studio_complete((string)$classId)) {
    $_SESSION['flash'] = tr('Nejdřív dokonči celý Studio flow. Zadání v Canvě se odemkne až po splnění všech kroků.');
    redirect_to('?view=graphics_studio');
}

if ($view === 'graphics_guide' && in_array($classId, ['class_1a', 'class_2a'], true)) {
    // $guide je obsah zadání (modules.php) – nepřekládá se, jen se obalí edu_content_lang_attr().
    $guide = $module['guide'];
    render_header(tr('Projekt na dvě hodiny'), $module);
    ?>
    <?php if ($flash !== ''): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
    <section class="hero"<?= edu_content_lang_attr() ?>>
        <div class="eyebrow"><?= e($module['name']) ?> Grafika · 2×45 minut</div>
        <h1><?= e($guide['title']) ?></h1>
        <p><?= e($guide['goal']) ?></p>
        <div class="tool-note"><strong><?= e(tr('Nástroje:')) ?></strong> <?= e($guide['tools']) ?></div>
        <div class="button-row"><a class="btn primary" href="?view=graphics_studio"><?= e(tr('Otevřít Studio')) ?></a><a class="btn secondary" href="?view=knowledgebase">Knowledge Tour</a></div>
    </section>
    <?= edu_content_note_html() ?>

    <section class="guide-route" aria-label="<?= e(tr('Průchod hodinou')) ?>">
        <article><span>01</span><strong><?= e(tr('Zjisti')) ?></strong><p><?= e(tr('Krátký test ukáže, co už znáš.')) ?></p></article>
        <article><span>02</span><strong><?= e(tr('Pochop')) ?></strong><p><?= e(tr('Knowledge Tour ukáže princip vizuálně.')) ?></p></article>
        <article><span>03</span><strong><?= e(tr('Otestuj')) ?></strong><p><?= e(tr('Studio prověří hierarchii, kontrast a export.')) ?></p></article>
        <article><span>04</span><strong><?= e(tr('Vytvoř')) ?></strong><p><?= e(tr('Canva je místo pro skutečný vizuální návrh.')) ?></p></article>
        <article><span>05</span><strong><?= e(tr('Ověř')) ?></strong><p><?= e(tr('Thumbnail, grayscale a export mimo editor.')) ?></p></article>
    </section>

    <section class="panel">
        <h2><?= e(tr('Povinné minimum')) ?></h2>
        <ul class="check-list static"<?= edu_content_lang_attr() ?>>
            <?php foreach ($guide['mandatory'] as $item): ?><li><?= e((string)$item) ?></li><?php endforeach; ?>
        </ul>
    </section>

    <section class="timeline-section">
        <div class="section-head"><span><?= e(tr('Hodina 1')) ?></span><h2><?= e(tr('Nejdřív struktura, potom efekt')) ?></h2></div>
        <div class="timeline"<?= edu_content_lang_attr() ?>>
            <?php foreach ($guide['lesson1'] as $step): ?>
                <article><time><?= e($step['time']) ?></time><div><h3><?= e($step['title']) ?></h3><p><?= e($step['text']) ?></p></div></article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="timeline-section">
        <div class="section-head"><span><?= e(tr('Hodina 2')) ?></span><h2><?= e(tr('Dokončení, kontrola a odevzdání')) ?></h2></div>
        <div class="timeline"<?= edu_content_lang_attr() ?>>
            <?php foreach ($guide['lesson2'] as $step): ?>
                <article><time><?= e($step['time']) ?></time><div><h3><?= e($step['title']) ?></h3><p><?= e($step['text']) ?></p></div></article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2><?= e(tr('Kontrolní checklist')) ?></h2>
        <p><?= e(tr('Než exportuješ, projdi všech deset bodů. Nejde o bodování – je to kontrola kvality práce.')) ?></p>
        <ul class="check-list static"<?= edu_content_lang_attr() ?>>
            <?php foreach ($guide['checklist'] as $item): ?><li><?= e((string)$item) ?></li><?php endforeach; ?>
        </ul>
    </section>

    <section class="panel extension">
        <div class="eyebrow"><?= e(tr('Pro rychlejší')) ?></div>
        <h2><?= e(tr('Rozšiřující varianta')) ?></h2>
        <ol<?= edu_content_lang_attr() ?>>
            <?php foreach ($guide['extension'] as $item): ?><li><?= e((string)$item) ?></li><?php endforeach; ?>
        </ol>
    </section>
    <?php if (!empty($guide['end_challenge']) && is_array($guide['end_challenge'])): $challenge=$guide['end_challenge']; ?>
    <section class="panel finisher-challenge"<?= edu_content_lang_attr() ?>>
        <div class="section-heading"><div><div class="eyebrow"><?= e(tr('Konec hodiny · dobrovolné')) ?></div><h2><?= e((string)$challenge['title']) ?></h2><p><?= e((string)$challenge['brief']) ?></p></div><div class="duration-pill"><?= e((string)$challenge['duration']) ?></div></div>
        <div class="finisher-grid"><div><strong><?= e(tr('Odevzdej')) ?></strong><ul><?php foreach (($challenge['deliverables'] ?? []) as $x): ?><li><?= e((string)$x) ?></li><?php endforeach; ?></ul></div><div><strong><?= e(tr('Mini-rubrika / 10')) ?></strong><ul><?php foreach (($challenge['rubric'] ?? []) as $x): ?><li><?= e((string)$x) ?></li><?php endforeach; ?></ul></div></div>
        <p class="muted-note"><?= e(tr('Tento úkol je až po dokončení hlavního dvouhodinového cíle nebo pro studenty, kteří postupují výrazně rychleji. Neodměňuje rychlost, ale kvalitu varianty a schopnost vysvětlit rozhodnutí.')) ?></p>
    </section>
    <?php endif; ?>

    <section class="panel submit-panel" id="submit">
        <div class="section-head"><span><?= e(tr('Odevzdání')) ?></span><h2><?= e(tr('Finální soubor + krátká reflexe')) ?></h2></div>
        <form method="post" enctype="multipart/form-data" class="form-panel">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="submit_graphics">
            <label><?= e(tr('Název práce')) ?>
                <input type="text" name="project_title" maxlength="160" required placeholder="<?= e(tr('např. EDUCANET Gaming Night')) ?>">
            </label>
            <label><?= e(tr('Finální soubor (PNG, JPG, WEBP nebo PDF · max 12 MB)')) ?>
                <input type="file" name="artifact" accept="image/png,image/jpeg,image/webp,application/pdf" required>
            </label>
            <fieldset>
                <legend><?= e(tr('Zaškrtni, co jsi před odevzdáním ověřil/a')) ?></legend>
                <div class="submit-checks"<?= edu_content_lang_attr() ?>>
                    <?php foreach ($guide['checklist'] as $i => $item): ?>
                        <label><input type="checkbox" name="checklist[]" value="<?= e((string)$i) ?>" required><span><?= e((string)$item) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <label><?= e(tr('Krátká reflexe')) ?>
                <textarea name="reflection" rows="5" minlength="20" maxlength="2000" required placeholder="<?= e(tr('Popiš alespoň jedno rozhodnutí, které jsi udělal/a vědomě kvůli hierarchii, kontrastu nebo kompozici.')) ?>"></textarea>
            </label>
            <button class="btn primary" type="submit"><?= e(tr('Odevzdat práci')) ?></button>
        </form>
    </section>
    <div class="button-row">
        <a class="btn primary" href="?view=graphics_studio"><?= e(tr('Studio')) ?></a>
        <a class="btn secondary" href="?view=knowledgebase">Knowledgebase</a>
        <a class="btn secondary" href="?view=dashboard"><?= e(tr('Zpět na přehled')) ?></a>
    </div>
    <?php
    render_footer();
    exit;
}
