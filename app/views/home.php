<?php

declare(strict_types=1);

/**
 * Přihlašovací stránka (?view=home a každý pohled bez třídy). Podmínku drží index.php.
 * Přesunuto z index.php (v58 · F5); jediná změna: odkazy na CSS/JS jdou přes asset_url() (DAT-05).
 * Spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

    render_header(tr('Přihlášení'));
    ?>
    <div class="auth54">
      <section class="auth54-hero">
        <span class="auth54-badge">EDUCANET Learning Lab</span>
        <h1><?= e(tr('Přihlas se a pokračuj tam, kde jsi skončil')) ?><span>.</span></h1>
        <p><?= e(tr('Kurz vedený krok za krokem: animované ukázky, interaktivní úkoly za body a dnešní hodina vždy po ruce.')) ?></p>
        <ul class="auth54-points">
          <li><b>1</b> <?= e(tr('Přihlaš se školním e-mailem')) ?></li>
          <li><b>2</b> <?= e(tr('Zadej kód hodiny od učitele')) ?></li>
          <li><b>3</b> <?= e(tr('Pracuj krok za krokem')) ?></li>
        </ul>
      </section>

      <div class="auth54-panel">
        <?php if ($flash !== ''): ?><div class="auth54-flash"><?= e($flash) ?></div><?php endif; ?>

        <div class="auth54-tabs" role="tablist">
          <button type="button" role="tab" class="active" data-auth54-tab="login" aria-selected="true"><?= e(tr('Přihlášení')) ?></button>
          <?php if (local_auth_enabled()): ?><button type="button" role="tab" data-auth54-tab="register" aria-selected="false"><?= e(tr('Nový účet')) ?></button><?php endif; ?>
        </div>

        <section class="auth54-pane active" data-auth54-pane="login">
          <?php if (google_auth_configured()): ?>
            <div class="google-button-wrap"><div id="g_id_onload" data-client_id="<?= e(google_client_id()) ?>" data-callback="handleGoogleCredential" data-auto_prompt="false" data-hd="<?= e(google_workspace_domain()) ?>"></div><div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="outline" data-text="signin_with" data-size="large" data-logo_alignment="left" data-locale="<?= e(edu_html_lang()) ?>"></div></div>
            <div class="auth54-divider"><span><?= e(tr('nebo školním heslem')) ?></span></div>
          <?php endif; ?>
          <?php if (local_auth_enabled()): ?>
          <form class="auth54-form" method="post" id="local-login">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="local_login">
            <label class="auth54-field"><span><?= e(tr('Školní e-mail')) ?></span><input type="email" name="email" inputmode="email" autocomplete="username" placeholder="jmeno.prijmeni@<?= e(google_workspace_domain()) ?>" required autofocus></label>
            <label class="auth54-field"><span><?= e(tr('Heslo')) ?></span><input type="password" name="password" autocomplete="current-password" maxlength="200" required></label>
            <button class="btn primary wide" type="submit"><?= e(tr('Přihlásit se')) ?></button>
          </form>
          <?php endif; ?>
          <a class="auth54-code" href="?view=join"><span class="auth54-code-icon" aria-hidden="true">#</span><span><strong><?= e(tr('Mám kód hodiny')) ?></strong><small><?= e(tr('Kód promítá učitel na tabuli.')) ?></small></span><b>→</b></a>
          <?php if (local_auth_enabled()): ?>
          <details class="auth54-help">
            <summary><span class="auth54-help-icon" aria-hidden="true">?</span><?= e(tr('Nemůžu se přihlásit')) ?></summary>
            <div class="auth54-help-body">
              <p><?= tr_html('Školní účet má tvar {domain}. Jednorázové heslo ti dá učitel na kartičce. Po přihlášení si nastavíš vlastní.', ['domain' => '<strong>jmeno.prijmeni@' . e(google_workspace_domain()) . '</strong>']) ?></p>
              <form method="post" class="auth54-inline-form">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="local_forgot_password">
                <label class="auth54-field"><span><?= e(tr('Zapomenuté heslo – pošleme odkaz')) ?></span><input type="email" name="email" placeholder="jmeno.prijmeni@<?= e(google_workspace_domain()) ?>" required></label>
                <button class="btn secondary" type="submit"><?= e(tr('Poslat odkaz')) ?></button>
              </form>
              <?php if (local_auth_require_email_verification()): ?>
              <form method="post" class="auth54-inline-form">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="local_resend_verification">
                <label class="auth54-field"><span><?= e(tr('Nepřišel ověřovací e-mail?')) ?></span><input type="email" name="email" placeholder="jmeno.prijmeni@<?= e(google_workspace_domain()) ?>" required></label>
                <button class="btn secondary" type="submit"><?= e(tr('Poslat znovu')) ?></button>
              </form>
              <?php endif; ?>
              <p class="auth54-help-note"><?= e(tr('Pořád to nejde? Řekni si učiteli o reset hesla.')) ?></p>
            </div>
          </details>
          <?php endif; ?>
        </section>

        <?php if (local_auth_enabled()): ?>
        <section class="auth54-pane" data-auth54-pane="register" hidden>
          <p class="auth54-lead"><?= tr_html('Účet si zakládej jen tehdy, když ti ho učitel ještě nevytvořil. Funguje pouze školní e-mail {domain}.', ['domain' => '<strong>@' . e(google_workspace_domain()) . '</strong>']) ?></p>
          <form class="auth54-form" method="post" id="local-register">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="local_register">
            <label class="auth54-field"><span><?= e(tr('Jméno a příjmení')) ?></span><input type="text" name="name" autocomplete="name" maxlength="120" required></label>
            <label class="auth54-field"><span><?= e(tr('Školní e-mail')) ?></span><input type="email" name="email" autocomplete="email" placeholder="jmeno.prijmeni@<?= e(google_workspace_domain()) ?>" required></label>
            <div class="auth54-grid">
              <label class="auth54-field"><span><?= e(tr('Heslo')) ?></span><input type="password" name="password" autocomplete="new-password" minlength="10" maxlength="200" required aria-describedby="auth54-pw-hint"></label>
              <label class="auth54-field"><span><?= e(tr('Heslo znovu')) ?></span><input type="password" name="password_confirm" autocomplete="new-password" minlength="10" maxlength="200" required></label>
            </div>
            <p class="auth54-hint" id="auth54-pw-hint"><?= e(tr('Aspoň 10 znaků, písmeno i číslice. Ne jméno ani běžné heslo.')) ?></p>
            <button class="btn primary wide" type="submit"><?= e(tr('Vytvořit účet')) ?></button>
          </form>
          <a class="auth54-code" href="?view=activate"><span class="auth54-code-icon" aria-hidden="true">✓</span><span><strong><?= e(tr('Mám aktivační kód')) ?></strong><small><?= e(tr('Pro žáky s připraveným účtem.')) ?></small></span><b>→</b></a>
          <?php if (local_auth_require_email_verification()): ?><p class="auth54-help-note"><?= e(tr('Po registraci pošleme ověřovací odkaz na školní e-mail.')) ?></p><?php endif; ?>
        </section>
        <?php endif; ?>

        <p class="auth54-foot"><?= tr_html('Přihlášením souhlasíš s {a}pravidly zpracování údajů{/a}.', ['a' => '<a href="?view=privacy">', '/a' => '</a>']) ?></p>
      </div>
    </div>
    <script src="<?= e(asset_url('assets/auth-v54.js?v=54.0')) ?>" defer></script>
    <?php if (educanet_dev_bypass_enabled() || getenv('EDUCANET_ALLOW_FREE_CLASS_ENTRY') === '1'): ?>
    <details class="panel narrow dev-entry"><summary><?= e(tr('Vývojový vstup bez účtu')) ?></summary><form class="form-panel" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="enter_class"><label><?= e(tr('Jméno nebo přezdívka')) ?><input name="student_label" maxlength="120" autocomplete="name" required></label><label><?= e(tr('Kód třídy')) ?><input name="class_code" maxlength="16" autocomplete="off" required></label><button class="btn secondary" type="submit"><?= e(tr('Pokračovat')) ?></button></form></details>
    <?php endif; ?>
    <?php
    render_footer();
    exit;
