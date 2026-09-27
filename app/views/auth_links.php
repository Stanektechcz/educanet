<?php

declare(strict_types=1);

/**
 * ?view=verify_email a ?view=reset_password – odkazy z e-mailu.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'verify_email') {
    // v58 · SEC58-02: účet se najde jen podle tokenu (e-mail v odkazu už není; starý parametr se ignoruje).
    local_password_reset_headers();
    $account = local_email_verification_consume(is_string($_GET['token'] ?? null) ? $_GET['token'] : '');
    if ($account === null) {
        $_SESSION['flash'] = tr('Ověřovací odkaz není platný nebo už vypršel. Pošli si nový z přihlašovací stránky.');
        redirect_to('?view=home#local-login');
    }
    session_regenerate_id(true);
    $_SESSION['local_user'] = local_account_public($account);
    unset($_SESSION['google_user']);
    $_SESSION['flash'] = tr('Školní e-mail je ověřený. Účet je aktivní.');
    if (try_restore_auth_assignment($modules)) redirect_to('?view=dashboard');
    redirect_to('?view=link_account');
}

if ($view === 'reset_password') {
    local_password_reset_headers();
    if (isset($_GET['token'])) {
        if (!local_password_reset_exchange(is_string($_GET['token']) ? $_GET['token'] : '')) $_SESSION['flash'] = tr('Odkaz pro obnovení hesla není platný nebo už vypršel.');
        redirect_to('?view=reset_password');
    }
    $valid = local_password_reset_session_email() !== null;
    render_header(tr('Nové heslo'));
    ?>
    <section class="hero hero-center compact-hero"><div class="eyebrow"><?= e(tr('Školní účet')) ?></div><h1><?= e(tr('Nastavení nového hesla')) ?></h1><p><?= tr_html('Obnova platí jen pro lokální účet {domain}.', ['domain' => '<strong>@' . e(google_workspace_domain()) . '</strong>']) ?></p></section>
    <?php if ($flash !== ''): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
    <?php if (!$valid): ?>
        <section class="panel narrow"><div class="soft-warning"><strong><?= e(tr('Odkaz není platný nebo vypršel.')) ?></strong><br><?= e(tr('Vrať se na přihlášení a požádej o nový reset hesla.')) ?></div><div class="button-row"><a class="btn primary" href="?view=home#local-login"><?= e(tr('Zpět na přihlášení')) ?></a></div></section>
    <?php else: ?>
        <form class="panel form-panel narrow" method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="local_reset_password">
            <label><?= e(tr('Nové heslo')) ?><input type="password" name="password" minlength="10" maxlength="200" autocomplete="new-password" required><small><?= e(tr('Aspoň 10 znaků, písmeno i číslice. Ne jméno ani běžné heslo.')) ?></small></label>
            <label><?= e(tr('Nové heslo znovu')) ?><input type="password" name="password_confirm" minlength="10" maxlength="200" autocomplete="new-password" required></label>
            <button class="btn primary" type="submit"><?= e(tr('Uložit nové heslo')) ?></button>
        </form>
    <?php endif; ?>
    <?php render_footer(); exit;
}
