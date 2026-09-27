<?php

declare(strict_types=1);

/**
 * Vynucená / dobrovolná změna hesla (v53). Podmínku drží index.php.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

    if (!auth_is_signed_in()) redirect_to('?view=home');
    render_header(tr('Nastavení hesla'));
    ?>
    <section class="u51-narrow">
      <ol class="u51-progress-dots"><li class="done"><?= e(tr('Přihlášení')) ?></li><li class="current"><?= e(tr('Nové heslo')) ?></li><li><?= e(tr('Hotovo')) ?></li></ol>
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <form class="u51-card" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="acc53_change_password">
        <span class="u51-kicker"><?= e(tr('První přihlášení')) ?></span>
        <h1><?= e(tr('Nastav si vlastní heslo')) ?></h1>
        <p class="u51-lead"><?= tr_html('Účet {email} má zatím jednorázové heslo od učitele. Vyber si vlastní – bez něj se dál nedostaneš.', ['email' => '<strong>' . e((string)(auth_user()['email'] ?? '')) . '</strong>']) ?></p>
        <?php if (!acc58_session_proven((string)(auth_user()['email'] ?? ''))): ?>
        <label class="u51-field"><span><?= e(tr('Jednorázové heslo z kartičky')) ?></span><input type="password" name="current_password" required autocomplete="current-password" value=""><small><?= e(tr('Opiš ho přesně i s pomlčkami.')) ?></small></label>
        <?php endif; ?>
        <label class="u51-field"><span><?= e(tr('Nové heslo')) ?></span><input type="password" name="password" required minlength="10" maxlength="200" autocomplete="new-password" aria-describedby="acc58-pw-hint"><small id="acc58-pw-hint"><?= e(tr('Aspoň 10 znaků, písmeno i číslice. Ne jméno, ne „heslo123“ – třeba dvě slova a číslo.')) ?></small></label>
        <label class="u51-field"><span><?= e(tr('Nové heslo znovu')) ?></span><input type="password" name="password_confirm" required minlength="10" maxlength="200" autocomplete="new-password"></label>
        <button class="btn primary wide" type="submit"><?= e(tr('Uložit heslo a pokračovat')) ?></button>
      </form>
    </section>
    <?php
    render_footer();
    exit;
