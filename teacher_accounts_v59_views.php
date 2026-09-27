<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v59 · AUTHZ58-07 – šablony učitelských účtů (jen čeština, výstup přes e()).
 * Přihlášení (režim účtů), vynucená změna hesla, záložka „ucet“ (vlastní účet), záložka „ucitele“ (admin),
 * tisk jednorázové kartičky, stránka „Nemáte přiřazené třídy“, upozornění v záložce Tým a role.
 */

function teacher59_class_label(string $classId): string
{
    if (function_exists('teacher_class_label')) return teacher_class_label($classId);
    return (string)(teacher59_all_modules()[$classId]['name'] ?? $classId);
}

function teacher59_date(?string $iso): string
{
    $ts = is_string($iso) && $iso !== '' ? strtotime($iso) : false;
    return $ts ? date('j. n. Y H:i', $ts) : '—';
}

function teacher59_assignment_text(array $account): string
{
    if ((string)($account['role'] ?? '') === 'admin') return 'Všechny třídy (administrátor)';
    $parts = [];
    foreach ((array)($account['assignments'] ?? []) as $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $subject = (string)($row['subject_id'] ?? '*');
        $parts[] = teacher59_class_label($classId) . ' · ' . teacher59_subject_label($subject === '*' ? teacher59_subject_of_class($classId) : $subject);
    }
    return $parts ? implode(', ', $parts) : 'Zatím žádné třídy';
}

/** CSS/JS účtů v hlavičce teacher.php (v legacy nic – vzhled beze změny). */
function teacher59_head_assets(string $tab): void
{
    if (teacher59_mode() === 'legacy' || $tab === 'ucitele') return; // záložka Učitelé má CSS/JS z registru v58
    $css = __DIR__ . '/assets/teacher-accounts-v59.css';
    echo '<link rel="stylesheet" href="' . e('assets/teacher-accounts-v59.css?v=' . (is_file($css) ? (string)filemtime($css) : '59')) . '">';
    if ($tab === 'ucet') {
        $js = __DIR__ . '/assets/teacher-accounts-v59.js';
        echo '<script src="' . e('assets/teacher-accounts-v59.js?v=' . (is_file($js) ? (string)filemtime($js) : '59')) . '" defer></script>';
    }
}

function teacher59_hidden(string $action): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">';
}

// ---------------------------------------------------------------------------
// Přihlášení a vynucená změna hesla
// ---------------------------------------------------------------------------

function teacher59_render_login_card(?array $flash): void
{
    teacher59_no_store();
    $notice = is_string($_SESSION['teacher59_notice'] ?? null) ? (string)$_SESSION['teacher59_notice'] : '';
    unset($_SESSION['teacher59_notice']);
    ?>
<section class="teacher-login-card t59-login"><div class="teacher-login-brand"><span>EDUCANET</span><h1 class="teacher-login-title">Učitelský hodnoticí cockpit</h1><p>Přihlaste se vlastním učitelským účtem.</p></div>
<?php if ($notice !== ''): ?><div class="teacher-flash error" role="status"><?= e($notice) ?></div><?php endif; ?>
<?php if ($flash): ?><div class="teacher-flash <?= e((string)$flash['type']) ?>" role="alert"><?= e((string)$flash['message']) ?></div><?php endif; ?>
<form method="post" class="t59-form"><?= teacher59_hidden('teacher_login') ?>
<label for="t59-login">Přihlašovací jméno<input id="t59-login" name="login" required maxlength="40" autocomplete="username" autocapitalize="none" spellcheck="false" inputmode="email"></label>
<label for="t59-password">Heslo<input id="t59-password" type="password" name="password" required maxlength="200" autocomplete="current-password"></label>
<button class="btn primary t59-btn" type="submit">Přihlásit se →</button>
<p class="t59-muted">Zapomenuté heslo? Požádejte administrátora školy o nové jednorázové heslo.</p></form></section>
<?php
}

function teacher59_render_password_fields(bool $forced): void
{
    ?>
<label for="t59-current"><?= $forced ? 'Jednorázové heslo z kartičky' : 'Stávající heslo' ?><input id="t59-current" type="password" name="current_password" required maxlength="200" autocomplete="current-password"></label>
<label for="t59-new">Nové heslo<input id="t59-new" type="password" name="new_password" required minlength="<?= TEACHER59_PASSWORD_MIN ?>" maxlength="200" autocomplete="new-password" aria-describedby="t59-pw-hint"></label>
<label for="t59-new2">Nové heslo znovu<input id="t59-new2" type="password" name="new_password_confirm" required minlength="<?= TEACHER59_PASSWORD_MIN ?>" maxlength="200" autocomplete="new-password"></label>
<p id="t59-pw-hint" class="t59-muted">Alespoň <?= TEACHER59_PASSWORD_MIN ?> znaků, písmeno i číslice, žádné běžné heslo ani vaše jméno. Dobře funguje spojení několika slov a čísel.</p>
<?php
}

/** Relace s vynucenou změnou hesla: jen tahle stránka (polling dostane 403 JSON). 503 při nečitelném úložišti. */
function teacher59_guard_forced_get(?array $flash = null): void
{
    $mode = teacher59_mode();
    if ($mode === 'legacy') return;
    if ($mode === 'broken') teacher59_render_unavailable();
    if (teacher59_session_state() !== 'must_change') return;
    if (teacher59_wants_json()) teacher59_deny('must_change_password', true, 'Nejdřív si nastavte vlastní heslo.');
    $account = (array)teacher59_current();
    teacher59_no_store();
    ob_start();
    ?>
<section class="t59-card t59-forced" aria-labelledby="t59-forced-title"><div class="eyebrow">První přihlášení</div><h1 id="t59-forced-title">Nastavte si vlastní heslo</h1>
<p>Dobrý den, <?= e((string)($account['display_name'] ?? '')) ?>. Jednorázové heslo z kartičky platí jen pro první přihlášení.</p>
<?php if ($flash): ?><div class="teacher-flash <?= e((string)$flash['type']) ?>" role="alert"><?= e((string)$flash['message']) ?></div><?php endif; ?>
<form method="post" class="t59-form"><?= teacher59_hidden('teacher59_self_password') ?><?php teacher59_render_password_fields(true); ?><button class="btn primary t59-btn" type="submit">Uložit heslo</button></form>
<form method="post" class="t59-inline"><?= teacher59_hidden('teacher_logout') ?><button class="btn secondary t59-btn" type="submit">Odhlásit se</button></form></section>
<?php
    echo teacher59_page('Nastavení hesla', (string)ob_get_clean());
    exit;
}

/** Učitel bez přiřazených tříd (neadmin). */
function teacher59_render_no_classes(): never
{
    teacher59_no_store();
    echo teacher59_page('Nemáte přiřazené třídy', '<section class="t59-card" role="status"><div class="eyebrow">Učitelský účet</div>'
        . '<h1>Nemáte přiřazené třídy</h1><p>Váš účet zatím nemá žádnou třídu ani předmět. Požádejte administrátora školy, aby vám třídy přiřadil v záložce Učitelé.</p>'
        . '<div class="t59-actions"><a class="btn secondary t59-btn" href="teacher.php?tab=ucet">Můj účet</a>'
        . '<form method="post" class="t59-inline">' . teacher59_hidden('teacher_logout') . '<button class="btn secondary t59-btn" type="submit">Odhlásit se</button></form></div></section>');
    exit;
}

function teacher59_render_team_admin_notice(): void
{
    ?>
<section class="t59-card"><div class="eyebrow">Tým a role</div><h1>Role se nastavují u učitelských účtů</h1>
<p>Od verze 59 má každý učitel vlastní účet. Role a přiřazené třídy spravuje administrátor v záložce <?php if (teacher59_is_admin()): ?><a href="?tab=ucitele">Učitelé</a><?php else: ?>Učitelé<?php endif; ?>.</p></section>
<?php
}

// ---------------------------------------------------------------------------
// Záložka „ucet“ – vlastní účet
// ---------------------------------------------------------------------------

function teacher59_render_account_tab(): void
{
    teacher59_no_store();
    if (teacher59_mode() === 'legacy') {
        echo '<section class="t59-card"><h1>Můj účet</h1><p>Vlastní učitelské účty zatím nejsou zapnuté – přihlašuje se sdíleným klíčem.</p></section>';
        return;
    }
    $account = teacher59_current();
    if ($account === null) return;
    $session = is_array($_SESSION['teacher59'] ?? null) ? $_SESSION['teacher59'] : [];
    $loginAt = (int)($session['login_at'] ?? time());
    $hasToken = teacher59_actor_token() !== '';
    ?>
<section class="teacher-page-head"><div><div class="eyebrow">Můj účet</div><h1><?= e((string)$account['display_name']) ?></h1><p>Heslo, přihlášení a převzetí dat ze starého sdíleného přístupu.</p></div></section>
<div class="t59-grid">
<section class="t59-card" aria-labelledby="t59-info"><h2 id="t59-info">Účet</h2><dl class="t59-dl">
<dt>Přihlašovací jméno</dt><dd><code><?= e((string)$account['login']) ?></code></dd>
<dt>Role</dt><dd><?= e(teacher59_role_label((string)$account['role'])) ?></dd>
<dt>Třídy a předměty</dt><dd><?= e(teacher59_assignment_text($account)) ?></dd>
<dt>Přihlášení</dt><dd><?= e(date('j. n. Y H:i', $loginAt)) ?></dd>
<dt>Relace skončí nejpozději</dt><dd><?= e(date('j. n. Y H:i', $loginAt + TEACHER59_ABSOLUTE_TIMEOUT)) ?> (po 60 minutách nečinnosti dřív)</dd>
<dt>Heslo změněno</dt><dd><?= e(teacher59_date($account['password_changed_at'] ?? null)) ?></dd></dl>
<form method="post" class="t59-inline"><?= teacher59_hidden('teacher59_self_logout_others') ?><button class="btn secondary t59-btn" type="submit" data-t59-confirm="Odhlásit všechna ostatní přihlášení tohoto účtu?">Odhlásit ostatní přihlášení</button></form></section>
<section class="t59-card" aria-labelledby="t59-pw"><h2 id="t59-pw">Změna hesla</h2><form method="post" class="t59-form"><?= teacher59_hidden('teacher59_self_password') ?><?php teacher59_render_password_fields(false); ?><button class="btn primary t59-btn" type="submit">Změnit heslo</button></form></section>
<section class="t59-card" aria-labelledby="t59-reclaim"><h2 id="t59-reclaim">Převzít moje stará data</h2>
<p>Uložené filtry, šablony, watchlist, automatizace a další osobní data ze sdíleného přístupu byla vázaná na tento prohlížeč a jméno, pod kterým jste se přihlašoval(a). Tady je převedete na svůj účet – jen pokud jsou vedená pod stejným jménem, jaké má váš účet, a jen pro vaše třídy. Jinak je převede administrátor.</p>
<?php if ($hasToken): ?><form method="post" class="t59-form"><?= teacher59_hidden('teacher59_self_reclaim') ?><label for="t59-legacy">Dřívější jméno při přihlášení<input id="t59-legacy" name="legacy_name" required maxlength="80" value="<?= e((string)$account['display_name']) ?>"></label><button class="btn secondary t59-btn" type="submit">Převzít data</button></form>
<?php else: ?><p class="t59-muted">V tomto prohlížeči nejsou žádná stará data. Otevřete stránku v prohlížeči, ve kterém jste pracoval(a) se sdíleným klíčem.</p><?php endif; ?></section>
</div>
<?php
}

// ---------------------------------------------------------------------------
// Záložka „ucitele“ – správa účtů (admin)
// ---------------------------------------------------------------------------

function teacher59_render_class_picker(string $prefix, array $selected): void
{
    $chosen = [];
    foreach ($selected as $row) if (is_array($row)) $chosen[(string)($row['class_id'] ?? '')] = (string)($row['subject_id'] ?? '*');
    echo '<fieldset class="t59-classes"><legend>Třídy a předmět</legend>';
    foreach (teacher59_all_class_ids() as $classId) {
        $subject = teacher59_subject_of_class($classId);
        $id = $prefix . '-' . $classId;
        echo '<div class="t59-class-row"><label for="' . e($id) . '"><input type="checkbox" id="' . e($id) . '" name="assign[]" value="' . e($classId) . '"'
            . (isset($chosen[$classId]) ? ' checked' : '') . ' data-t59-class> ' . e(teacher59_class_label($classId)) . '</label>'
            . '<label class="t59-sr" for="' . e($id . '-s') . '">Předmět pro ' . e(teacher59_class_label($classId)) . '</label>'
            . '<select id="' . e($id . '-s') . '" name="subject[' . e($classId) . ']">'
            . '<option value="*"' . (($chosen[$classId] ?? '*') === '*' ? ' selected' : '') . '>' . e(teacher59_subject_label('*')) . '</option>'
            . ($subject !== '' ? '<option value="' . e($subject) . '"' . (($chosen[$classId] ?? '') === $subject ? ' selected' : '') . '>' . e(teacher59_subject_label($subject)) . '</option>' : '')
            . '</select></div>';
    }
    echo '</fieldset>';
}

function teacher59_render_role_select(string $id, string $current): void
{
    echo '<label for="' . e($id) . '">Role<select id="' . e($id) . '" name="role">';
    foreach (TEACHER59_ROLES as $role) echo '<option value="' . e($role) . '"' . ($role === $current ? ' selected' : '') . '>' . e(teacher59_role_label($role)) . '</option>';
    echo '</select></label>';
}

function teacher59_render_account_actions(array $row, string $selfId): void
{
    $id = (string)$row['id'];
    $name = (string)$row['display_name'];
    $button = static function (string $action, string $label, string $confirm = '', string $class = 'secondary') use ($id): void {
        echo '<form method="post" class="t59-inline">' . teacher59_hidden($action) . '<input type="hidden" name="account_id" value="' . e($id) . '">'
            . '<button class="btn ' . e($class) . ' small t59-btn" type="submit"' . ($confirm !== '' ? ' data-t59-confirm="' . e($confirm) . '"' : '') . '>' . e($label) . '</button></form>';
    };
    echo '<details class="t59-edit"><summary>Upravit přístup</summary><form method="post" class="t59-form">' . teacher59_hidden('teacher59_admin_update')
        . '<input type="hidden" name="account_id" value="' . e($id) . '">';
    teacher59_render_role_select('t59-role-' . $id, (string)$row['role']);
    teacher59_render_class_picker('t59-e-' . $id, (array)($row['assignments'] ?? []));
    echo '<button class="btn primary small t59-btn" type="submit">Uložit přístup</button></form></details><div class="t59-actions">';
    // SEC59-21: vlastní účet se neresetuje (admin by se zamkl) – vlastní heslo se mění v záložce Můj účet.
    if ($id !== $selfId) $button('teacher59_admin_reset', 'Nové jednorázové heslo', 'Vydat nové jednorázové heslo pro ' . $name . '? Staré heslo přestane platit a účet se odhlásí.');
    else echo '<a class="btn secondary small t59-btn" href="?tab=ucet">Změnit vlastní heslo</a>';
    if ((string)$row['status'] === 'active' && $id !== $selfId) $button('teacher59_admin_disable', 'Deaktivovat', 'Deaktivovat účet ' . $name . '?', 'danger');
    if ((string)$row['status'] !== 'active') $button('teacher59_admin_enable', 'Aktivovat');
    if (teacher59_is_locked((string)$row['login'])) $button('teacher59_admin_unlock', 'Zrušit zámek přihlášení');
    $button('teacher59_admin_revoke', 'Odhlásit všechny relace', 'Odhlásit všechna přihlášení účtu ' . $name . '?');
    echo '</div>';
}

function teacher59_render_reveal(array $card): void
{
    ?>
<section class="t59-card t59-reveal" role="status" aria-live="polite"><h2>Jednorázové heslo – <?= e($card['name']) ?></h2>
<p>Přihlašovací jméno <code><?= e($card['login']) ?></code>, heslo <code class="t59-otp"><?= e($card['otp']) ?></code></p>
<p class="t59-muted">Platí do <?= e(date('j. n. Y H:i', (int)$card['expires_at'])) ?>. Po obnovení stránky se už neukáže – předejte ho osobně nebo vytiskněte kartičku (jen jednou).</p>
<p><a class="btn secondary t59-btn" href="?<?= e(http_build_query(['tab' => 'ucitele', 'karticka' => (string)$card['token']])) ?>" target="_blank" rel="noopener">Vytisknout kartičku</a></p></section>
<?php
}

function teacher59_render_admin_tab(): void
{
    teacher59_no_store();
    if (!teacher59_is_admin() || teacher59_mode() !== 'accounts') { echo '<section class="t59-card"><p>Správa účtů je jen pro administrátora.</p></section>'; return; }
    $selfId = (string)teacher59_current_id();
    $accounts = teacher59_accounts();
    uasort($accounts, static fn(array $a, array $b): int => strnatcasecmp((string)$a['display_name'], (string)$b['display_name']));
    $card = teacher59_card_take_reveal();
    $logins = [];
    foreach ($accounts as $row) $logins[(string)$row['id']] = (string)$row['login'];
    ?>
<section class="teacher-page-head"><div><div class="eyebrow">Správa</div><h1>Učitelé a přístupy</h1><p>Každý učitel vidí jen své třídy a předměty. Admin vidí vše. Hesla se nikde neukládají v čitelné podobě.</p></div></section>
<?php if ($card !== null) teacher59_render_reveal($card); ?>
<details class="t59-card t59-create"<?= count($accounts) <= 1 ? ' open' : '' ?>><summary><h2>Nový učitelský účet</h2></summary>
<form method="post" class="t59-form"><?= teacher59_hidden('teacher59_admin_create') ?>
<label for="t59-new-login">Přihlašovací jméno<input id="t59-new-login" name="login" required pattern="[a-z0-9._\-]{3,40}" maxlength="40" placeholder="např. jan.novak" autocomplete="off" autocapitalize="none" spellcheck="false"></label>
<label for="t59-new-name">Jméno a příjmení<input id="t59-new-name" name="display_name" required minlength="2" maxlength="80" autocomplete="off"></label>
<?php teacher59_render_role_select('t59-new-role', 'teacher'); teacher59_render_class_picker('t59-n', []); ?>
<button class="btn primary t59-btn" type="submit">Založit účet a vydat jednorázové heslo</button></form></details>
<section class="t59-list" aria-label="Učitelské účty">
<?php foreach ($accounts as $row): $status = teacher59_otp_status($row); ?>
<article class="t59-card t59-account<?= (string)$row['status'] !== 'active' ? ' is-disabled' : '' ?>">
<header><h3><?= e((string)$row['display_name']) ?><?= (string)$row['id'] === $selfId ? ' <small>(vy)</small>' : '' ?></h3><code><?= e((string)$row['login']) ?></code></header>
<ul class="t59-chips"><li><?= e(teacher59_role_label((string)$row['role'])) ?></li><li><?= (string)$row['status'] === 'active' ? 'Aktivní' : 'Deaktivovaný' ?></li>
<?php if ($status === 'otp'): ?><li>Čeká na první přihlášení</li><?php elseif ($status === 'expired'): ?><li class="warn">Jednorázové heslo vypršelo</li><?php endif; ?>
<?php if (teacher59_is_locked((string)$row['login'])): ?><li class="warn">Dočasně zamčeno</li><?php endif; ?></ul>
<p><?= e(teacher59_assignment_text($row)) ?></p><p class="t59-muted">Poslední přihlášení: <?= e(teacher59_date($row['last_login_at'] ?? null)) ?></p>
<?php teacher59_render_account_actions($row, $selfId); ?></article>
<?php endforeach; ?></section>
<details class="t59-card"><summary><h2>Poslední události</h2></summary><ul class="t59-log">
<?php foreach (teacher59_log_rows(40) as $log): ?><li><time><?= e(teacher59_date((string)($log['at'] ?? ''))) ?></time> <?= e((string)($log['event'] ?? '')) ?> <?= e($logins[(string)($log['target_id'] ?? '')] ?? '') ?> <?= e(implode(', ', (array)($log['class_ids'] ?? []))) ?> <span class="t59-muted"><?= e((string)($log['reason'] ?? '')) ?></span></li><?php endforeach; ?>
</ul></details>
<details class="t59-card"><summary><h2>Neúspěšná přihlášení a zamítnutí</h2></summary><p class="t59-muted">Zaznamenává se nejvýš pět stejných událostí za minutu na účet nebo adresu.</p><ul class="t59-log">
<?php foreach (teacher59_log_rows(40, true) as $log): ?><li><time><?= e(teacher59_date((string)($log['at'] ?? ''))) ?></time> <?= e((string)($log['event'] ?? '')) ?> <?= e($logins[(string)($log['target_id'] ?? '')] ?? ($logins[(string)($log['actor_id'] ?? '')] ?? '')) ?> <?= e(implode(', ', (array)($log['class_ids'] ?? []))) ?> <span class="t59-muted"><?= e((string)($log['reason'] ?? '')) ?></span></li><?php endforeach; ?>
</ul></details>
<?php
}

/** GET ?tab=ucitele&karticka=<token> – tisk jednorázové kartičky (token se spotřebuje). */
function teacher59_render_card_print(): void
{
    teacher59_no_store();
    if (!teacher59_is_admin()) teacher59_deny('admin_only', false);
    $card = teacher59_card_consume(is_string($_GET['karticka'] ?? null) ? (string)$_GET['karticka'] : '');
    if ($card === null) {
        echo teacher59_page('Kartička', '<section class="t59-card" role="alert"><h1>Kartička už není k dispozici</h1><p>Kartičku lze vytisknout jen jednou a do 10 minut od vydání hesla. Vydejte nové jednorázové heslo.</p><p><a class="btn secondary t59-btn" href="teacher.php?tab=ucitele">Zpět</a></p></section>');
        return;
    }
    $js = __DIR__ . '/assets/teacher-accounts-v59.js';
    echo teacher59_page('Kartička učitele', '<section class="t59-print-card"><div class="eyebrow">EDUCANET · učitelský účet</div><h1>' . e($card['name']) . '</h1>'
        . '<dl class="t59-dl"><dt>Adresa</dt><dd>teacher.php</dd><dt>Přihlašovací jméno</dt><dd><code>' . e($card['login']) . '</code></dd>'
        . '<dt>Jednorázové heslo</dt><dd><code class="t59-otp">' . e($card['otp']) . '</code></dd><dt>Platí do</dt><dd>' . e(date('j. n. Y H:i', (int)$card['expires_at'])) . '</dd></dl>'
        . '<ol><li>Přihlaste se jménem a jednorázovým heslem.</li><li>Hned si nastavte vlastní heslo (alespoň ' . TEACHER59_PASSWORD_MIN . ' znaků).</li><li>Kartičku potom skartujte.</li></ol>'
        . '<p class="t59-noprint"><button type="button" class="btn primary t59-btn" data-t59-print>Tisknout</button></p></section>'
        . '<script src="' . e('assets/teacher-accounts-v59.js?v=' . (is_file($js) ? (string)filemtime($js) : '59')) . '" defer></script>');
}
