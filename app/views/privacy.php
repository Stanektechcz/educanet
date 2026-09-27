<?php

declare(strict_types=1);

/**
 * ?view=privacy – soukromí.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'privacy') {
    render_header(tr('Soukromí'));
    ?>
    <section class="hero"><div class="eyebrow">EDUCANET Learning Lab</div><h1><?= e(tr('Soukromí a školní účet')) ?></h1><p><?= e(tr('Aplikace umožňuje přihlášení přes Google Workspace nebo lokální školní účet @educanet.cz s heslem. Oba způsoby slouží k ověření identity a propojení se studijním profilem.')) ?></p></section>
    <?php if (edu_locale() !== 'cs'): ?><p class="legal-note" role="note"><?= e(tr('Závazné je české znění.')) ?></p><?php endif; ?>
    <section class="panel privacy-copy"><h2><?= e(tr('Jaká data používáme')) ?></h2><ul class="check-list static"><li><?= tr_html('u Google přihlášení stabilní identifikátor účtu ({sub}), školní e-mail, jméno a případně profilový obrázek; u lokálního účtu interní ID, školní e-mail a jméno,', ['sub' => '<code>sub</code>']) ?></li><li><?= e(tr('přiřazení ke třídě a studijní postup: XP, badge, dokončené lekce a výsledky školních aktivit,')) ?></li><li><?= e(tr('odevzdané soubory pouze u úloh, kde student výslovně odevzdává práci.')) ?></li></ul><h2><?= e(tr('Co aplikace nedělá')) ?></h2><p><?= e(tr('Nepožaduje přístup ke Gmailu, Disku, Kalendáři ani jiným datům Google účtu. Google přihlášení slouží pouze k autentizaci. Lokální hesla se ukládají výhradně jako jednosměrný bezpečný hash a aplikace je neumí zpětně zobrazit.')) ?></p><h2><?= e(tr('Správa účtu')) ?></h2><p><?= e(tr('Přiřazení účtu ke třídě je uloženo na školním serveru. Učitel/správce může chybné přiřazení odstranit v chráněném úložišti.')) ?></p></section>
    <div class="button-row"><a class="btn secondary" href="?view=home">← <?= e(tr('Zpět na přihlášení')) ?></a></div>
    <?php render_footer(); exit;
}
