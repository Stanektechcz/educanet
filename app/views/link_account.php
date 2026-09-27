<?php

declare(strict_types=1);

/**
 * ?view=link_account – propojení účtu se třídou.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'link_account') {
    $user = auth_user();
    if (!$user) redirect_to('?view=home');
    render_header(tr('Přiřazení ke třídě'));
    // v58 · PRIV58-13: stránka nevypisuje adresář žáků – jméno se píše a server ho ověří
    // proti seznamu vybrané třídy až po kontrole kódu třídy.
    ?>
    <section class="hero hero-center compact-hero"><div class="eyebrow"><?= e(tr('První přihlášení')) ?></div><h1><?= e(tr('Propoj školní účet s třídou')) ?></h1><p><?= tr_html('Přihlášený účet: {email}. Toto přiřazení se uloží a příště proběhne automaticky.', ['email' => '<strong>' . e((string)$user['email']) . '</strong>']) ?></p></section>
    <?php if ($flash !== ''): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
    <form method="post" class="panel form-panel narrow account-link-form" data-account-link-form>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="link_account">
        <label><?= e(tr('Třída')) ?><select name="class_id" required data-link-class><option value=""><?= e(tr('Vyber třídu…')) ?></option><?php foreach ($modules as $id=>$m): ?><option value="<?= e((string)$id) ?>"><?= e((string)$m['name']) ?> · <?= e((string)$m['subject']) ?></option><?php endforeach; ?></select></label>
        <label><?= e(tr('Kód třídy')) ?><input name="class_code" maxlength="16" autocomplete="off" placeholder="<?= e(tr('Kód od učitele')) ?>" required></label>
        <label data-link-student-wrap><?= e(tr('Jméno a příjmení')) ?><input name="student_name" maxlength="120" autocomplete="name" placeholder="<?= e(tr('Jak tě má učitel v seznamu třídy')) ?>"><small><?= e(tr('Napiš jméno tak, jak ho má učitel v seznamu třídy. U 1.A ho nevyplňuj – použije se jméno z přihlášeného školního účtu.')) ?></small></label>
        <label><?= e(tr('Heslo ke školnímu účtu')) ?> <small>(<?= e(tr('vyplň, pokud už máš kartičku od učitele nebo vlastní heslo')) ?>)</small><input type="password" name="link_password" maxlength="200" autocomplete="off"></label>
        <button class="btn primary" type="submit"><?= e(tr('Propojit účet a pokračovat')) ?></button>
    </form>
    <form method="post" class="panel form-panel narrow u51-link-code">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="intake_link_code">
        <label><?= e(tr('Máš aktivační kód od učitele?')) ?><input name="code" maxlength="12" autocomplete="off" placeholder="ABCD-1234" required></label>
        <button class="btn secondary" type="submit"><?= e(tr('Propojit kódem')) ?></button>
    </form>
    <?php render_footer(); exit;
}
