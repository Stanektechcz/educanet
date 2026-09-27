<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v50.1 · Demo account administration
 *
 * Demo accounts are regular local student accounts explicitly marked by
 * is_test_account=true. Passwords are never stored in plaintext; generated
 * credentials are returned to the caller only once so the UI can display them
 * after create/reset.
 */

function teacher_demo_account_generate_password(): string
{
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $digits = '23456789';
    $all = $letters . $digits;
    $password = 'EduDemo-';
    for ($i = 0; $i < 14; $i++) $password .= $all[random_int(0, strlen($all) - 1)];
    $password .= '-' . $digits[random_int(0, strlen($digits) - 1)] . $digits[random_int(0, strlen($digits) - 1)];
    return $password;
}

function teacher_demo_account_generate_email(string $classId): string
{
    $domain = google_workspace_domain();
    $class = strtolower(str_replace('class_', '', $classId));
    $accounts = local_accounts();
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $suffix = strtolower(bin2hex(random_bytes(3)));
        $email = "demo.{$class}.{$suffix}@{$domain}";
        if (!isset($accounts[$email])) return $email;
    }
    throw new RuntimeException('Nepodařilo se vygenerovat unikátní demo e-mail. Zkuste akci zopakovat.');
}

function teacher_demo_account_default_name(string $classId, array $modules): string
{
    $class = (string)($modules[$classId]['name'] ?? strtoupper(str_replace('class_', '', $classId)));
    return 'Demo student ' . $class;
}

function teacher_demo_account_validate_class(string $classId, array $modules): void
{
    if (!isset($modules[$classId]) || !is_array($modules[$classId]) || !teacher_demo_account_class_in_scope($classId)) {
        throw new RuntimeException('Vyberte platnou třídu.');
    }
}

/** v59 · AUTHZ58-07: třída v rozsahu přihlášeného učitele (legacy/CLI = vždy true). */
function teacher_demo_account_class_in_scope(string $classId): bool
{
    return !function_exists('teacher59_can_class') || teacher59_can_class($classId);
}

/** Třída demo účtu (vazba na žáka, jinak demo_class_id). */
function teacher_demo_account_class_of(array $found): string
{
    $binding = is_array($found['binding'] ?? null) ? $found['binding'] : [];
    return (string)($binding['class_id'] ?? $found['account']['demo_class_id'] ?? '');
}

function teacher_demo_account_audit(string $event, string $email, string $classId = '', array $meta = []): void
{
    if (!function_exists('teacher_ops_audit_event')) return;
    try {
        teacher_ops_audit_event($event, 'demo_account', $email, $classId, match ($event) {
            'demo_account.create' => 'Demo účet vytvořen',
            'demo_account.password_reset' => 'Heslo demo účtu resetováno',
            'demo_account.delete' => 'Demo účet odstraněn',
            default => 'Změna demo účtu',
        }, $meta);
    } catch (Throwable) {
        // Audit nesmí znefunkčnit správu účtů; hlavní data jsou již chráněna
        // vlastními atomickými storage zápisy.
    }
}

function teacher_demo_account_create(array $modules, string $classId, string $name = '', string $email = '', string $password = ''): array
{
    teacher_demo_account_validate_class($classId, $modules);

    $name = trim($name);
    if ($name === '') $name = teacher_demo_account_default_name($classId, $modules);
    if (u_strlen($name) > 100) throw new RuntimeException('Jméno demo studenta může mít maximálně 100 znaků.');

    $email = local_email_normalize($email);
    if ($email === '') $email = teacher_demo_account_generate_email($classId);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('E-mail demo účtu není platný.');
    if (!local_email_is_allowed($email)) throw new RuntimeException('Demo účet musí používat povolenou školní doménu @' . google_workspace_domain() . '.');

    $accounts = local_accounts();
    if (isset($accounts[$email])) {
        $existing = is_array($accounts[$email]) ? $accounts[$email] : [];
        if (empty($existing['is_test_account'])) {
            throw new RuntimeException('Tento e-mail už používá skutečný účet. Demo účet jej nesmí přepsat.');
        }
        throw new RuntimeException('Demo účet s tímto e-mailem už existuje. Použijte reset hesla nebo jiný e-mail.');
    }

    if ($password === '') $password = teacher_demo_account_generate_password();
    if (($error = local_password_validate($password)) !== null) throw new RuntimeException($error);

    $id = bin2hex(random_bytes(16));
    $now = date(DATE_ATOM);
    $actor = function_exists('teacher_display_name') ? teacher_display_name() : 'CLI';
    $account = [
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'password_hash' => local_password_hash($password),
        'created_at' => $now,
        'verified_at' => $now,
        'verification_token_hash' => null,
        'verification_expires_at' => null,
        'reset_token_hash' => null,
        'reset_expires_at' => null,
        'is_test_account' => true,
        'demo_created_by' => $actor,
        'demo_class_id' => $classId,
        'updated_at' => $now,
    ];

    // v58 (F2): účet i vazba na třídu vznikají atomicky pod zámky obou souborů (bez ručního rollbacku).
    $accountsPath = local_accounts_path();
    $mapPath = STORAGE_DIR . '/student_accounts.json.php';
    storage_update_many([$accountsPath, $mapPath], static function (array $data) use ($accountsPath, $mapPath, $email, $account, $id, $classId, $name, $now, $actor): array {
        if (isset($data[$accountsPath][$email])) throw new RuntimeException('Demo účet s tímto e-mailem už existuje. Použijte reset hesla nebo jiný e-mail.');
        $data[$accountsPath][$email] = $account;
        $data[$mapPath]['local:' . $id] = [
            'provider' => 'local',
            'email' => $email,
            'class_id' => $classId,
            'student_label' => $name,
            'linked_at' => $now,
            'is_test_account' => true,
            'demo_created_by' => $actor,
        ];
        return $data;
    });

    teacher_demo_account_audit('demo_account.create', $email, $classId, ['name' => $name]);

    return [
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'password' => $password,
        'class_id' => $classId,
        'class_name' => (string)($modules[$classId]['name'] ?? $classId),
        'subject' => (string)($modules[$classId]['subject'] ?? ''),
        'login_url' => '/?view=home#local-login',
        'created_at' => $now,
    ];
}

function teacher_demo_account_find(string $email): ?array
{
    $email = local_email_normalize($email);
    $accounts = local_accounts();
    $account = is_array($accounts[$email] ?? null) ? $accounts[$email] : null;
    if (!$account || empty($account['is_test_account'])) return null;

    $id = (string)($account['id'] ?? '');
    $binding = $id !== '' ? (student_account_map()['local:' . $id] ?? null) : null;
    return [
        'account' => $account,
        'binding' => is_array($binding) ? $binding : null,
    ];
}

function teacher_demo_account_reset_password(string $email, string $password = ''): array
{
    $email = local_email_normalize($email);
    $found = teacher_demo_account_find($email);
    if (!$found || !teacher_demo_account_class_in_scope(teacher_demo_account_class_of($found))) throw new RuntimeException('Demo účet nebyl nalezen nebo není označen jako testovací.');

    if ($password === '') $password = teacher_demo_account_generate_password();
    if (($error = local_password_validate($password)) !== null) throw new RuntimeException($error);

    $hash = local_password_hash($password);
    $saved = (array)storage_map_update(local_accounts_path(), $email, static function (?array $acc) use ($hash): array {
        if (!is_array($acc) || empty($acc['is_test_account'])) throw new RuntimeException('Demo účet nebyl nalezen nebo není označen jako testovací.');
        $acc['password_hash'] = $hash;
        $acc['updated_at'] = date(DATE_ATOM);
        $acc['reset_token_hash'] = null;
        $acc['reset_expires_at'] = null;
        return $acc;
    });
    $accounts = [$email => $saved];

    $binding = is_array($found['binding']) ? $found['binding'] : [];
    teacher_demo_account_audit('demo_account.password_reset', $email, (string)($binding['class_id'] ?? ''));

    return [
        'email' => $email,
        'name' => (string)($accounts[$email]['name'] ?? 'Demo student'),
        'password' => $password,
        'class_id' => (string)($binding['class_id'] ?? ''),
        'login_url' => '/?view=home#local-login',
    ];
}

function teacher_demo_account_delete(string $email): array
{
    $email = local_email_normalize($email);
    $found = teacher_demo_account_find($email);
    if (!$found || !teacher_demo_account_class_in_scope(teacher_demo_account_class_of($found))) throw new RuntimeException('Demo účet nebyl nalezen nebo není označen jako testovací.');

    $account = $found['account'];
    $binding = is_array($found['binding']) ? $found['binding'] : [];
    $id = (string)($account['id'] ?? '');

    // v58 (F2): kontrola, smazání účtu i vazby proběhnou atomicky pod zámky obou souborů.
    $accountsPath = local_accounts_path();
    $mapPath = STORAGE_DIR . '/student_accounts.json.php';
    storage_update_many([$accountsPath, $mapPath], static function (array $data) use ($accountsPath, $mapPath, $email, $id): array {
        $current = is_array($data[$accountsPath][$email] ?? null) ? $data[$accountsPath][$email] : null;
        if (!$current || empty($current['is_test_account']) || !hash_equals((string)($current['id'] ?? ''), $id)) {
            throw new RuntimeException('Účet se mezitím změnil. Obnovte stránku a zkuste to znovu.');
        }
        unset($data[$accountsPath][$email]);
        if ($id !== '') unset($data[$mapPath]['local:' . $id]);
        return $data;
    });

    teacher_demo_account_audit('demo_account.delete', $email, (string)($binding['class_id'] ?? ''), ['name' => (string)($account['name'] ?? '')]);
    return ['email' => $email, 'name' => (string)($account['name'] ?? ''), 'class_id' => (string)($binding['class_id'] ?? '')];
}

function teacher_demo_accounts_list(array $modules): array
{
    $accounts = local_accounts();
    $map = student_account_map();
    $rows = [];
    foreach ($accounts as $email => $account) {
        if (!is_array($account) || empty($account['is_test_account'])) continue;
        $id = (string)($account['id'] ?? '');
        $binding = $id !== '' && is_array($map['local:' . $id] ?? null) ? $map['local:' . $id] : [];
        $classId = (string)($binding['class_id'] ?? $account['demo_class_id'] ?? '');
        if (!teacher_demo_account_class_in_scope($classId)) continue; // v59: jen třídy v rozsahu
        $rows[] = [
            'id' => $id,
            'email' => (string)$email,
            'name' => (string)($account['name'] ?? 'Demo student'),
            'class_id' => $classId,
            'class_name' => (string)($modules[$classId]['name'] ?? $classId ?: '—'),
            'subject' => (string)($modules[$classId]['subject'] ?? ''),
            'created_at' => (string)($account['created_at'] ?? ''),
            'updated_at' => (string)($account['updated_at'] ?? ''),
            'created_by' => (string)($account['demo_created_by'] ?? $binding['demo_created_by'] ?? '—'),
            'binding_ok' => $id !== '' && !empty($binding) && !empty($binding['class_id']),
        ];
    }
    usort($rows, static function (array $a, array $b): int {
        $class = strcmp((string)$a['class_id'], (string)$b['class_id']);
        return $class !== 0 ? $class : strcmp((string)$a['email'], (string)$b['email']);
    });
    return $rows;
}

function teacher_demo_accounts_credentials_take(): ?array
{
    $credentials = $_SESSION['teacher_demo_credentials'] ?? null;
    unset($_SESSION['teacher_demo_credentials']);
    return is_array($credentials) ? $credentials : null;
}

function teacher_demo_accounts_credentials_store(array $credentials, string $mode): void
{
    $_SESSION['teacher_demo_credentials'] = $credentials + ['mode' => $mode];
}

function teacher_demo_accounts_date(string $value): string
{
    if ($value === '') return '—';
    $ts = strtotime($value);
    return $ts ? date('d.m.Y H:i', $ts) : $value;
}

function teacher_render_demo_accounts(array $modules): void
{
    $canManage = function_exists('teacher_permission') ? teacher_permission('students.manage') : false;
    if (!$canManage) {
        ?><section class="teacher-page-head"><div><div class="eyebrow">Správa testování</div><h1>Demo účty studentů</h1><p>Tato část je dostupná pouze rolím s oprávněním spravovat studenty.</p></div></section><div class="teacher-empty wide">Vaše role nemá oprávnění <code>students.manage</code>.</div><?php
        return;
    }
    $rows = teacher_demo_accounts_list($modules);
    $credentials = teacher_demo_accounts_credentials_take();
    ?>
    <section class="teacher-page-head demo-account-head"><div><div class="eyebrow">Správa testování</div><h1>Demo účty studentů</h1><p>Vytvořte izolovaný studentský účet pro libovolnou třídu a otestujte dashboard, lekce, projekty, mastery i studentské workflow bez zásahu do reálného účtu.</p></div><div class="demo-account-head-stat"><strong><?=count($rows)?></strong><span>aktivních demo účtů</span></div></section>

    <?php if ($credentials): ?>
    <section class="teacher-panel demo-credential-panel" data-demo-credentials>
      <div class="teacher-panel-head"><div><span><?=($credentials['mode']??'create')==='reset'?'Nové přihlašovací údaje':'Demo účet je připravený'?></span><h2>Heslo se zobrazuje pouze teď</h2></div><span class="demo-safe-badge">Jednorázové zobrazení</span></div>
      <div class="demo-credential-grid">
        <div><small>Jméno</small><strong><?=e((string)($credentials['name']??''))?></strong></div>
        <div><small>Třída</small><strong><?=e(teacher_class_label((string)($credentials['class_id']??'')))?></strong></div>
        <div><small>E-mail</small><code data-copy-value><?=e((string)($credentials['email']??''))?></code></div>
        <div><small>Heslo</small><code data-copy-value><?=e((string)($credentials['password']??''))?></code></div>
      </div>
      <div class="demo-credential-actions"><button type="button" class="btn secondary small" data-copy-demo>Copy login + heslo</button><a class="btn primary small" href="./?view=home#local-login" target="_blank" rel="noopener">Otevřít studentský login ↗</a></div>
      <p class="teacher-muted">Heslo ukládáme pouze jako bezpečný hash. Po opuštění nebo obnovení této stránky jej administrace znovu nezobrazí; v případě potřeby použijte „Reset hesla“.</p>
    </section>
    <?php endif; ?>

    <div class="demo-account-layout">
      <section class="teacher-panel demo-account-create">
        <div class="teacher-panel-head"><div><span>Nový sandbox student</span><h2>Vytvořit demo účet</h2></div><span class="demo-safe-badge">Bez reálných dat</span></div>
        <?php if (!$canManage): ?><div class="teacher-empty">Vaše role nemá oprávnění spravovat studentské účty.</div><?php else: ?>
        <form method="post" class="demo-account-form" autocomplete="off">
          <input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_demo_account_create"><input type="hidden" name="return_tab" value="demo_accounts">
          <label>Třída<select name="class_id" required><?php foreach($modules as $cid=>$module): if(!is_array($module))continue; ?><option value="<?=e((string)$cid)?>"><?=e(teacher_class_label((string)$cid))?> · <?=e((string)($module['subject']??''))?></option><?php endforeach; ?></select></label>
          <label>Jméno <small>volitelné</small><input name="demo_name" maxlength="100" placeholder="Automaticky: Demo student 1.A"></label>
          <label>E-mail <small>volitelné</small><input type="email" name="demo_email" maxlength="190" placeholder="Automaticky vygenerujeme @<?=e(google_workspace_domain())?>"></label>
          <label>Heslo <small>volitelné</small><input type="text" name="demo_password" minlength="10" maxlength="200" placeholder="Bezpečné heslo vygenerujeme automaticky" autocomplete="new-password"></label>
          <div class="demo-account-info"><b>Co se stane?</b><span>Účet bude ihned ověřený, automaticky propojený s vybranou třídou a označený jako demo. Nelze jím přepsat existující skutečný účet.</span></div>
          <button class="btn primary" type="submit">＋ Vytvořit demo účet</button>
        </form>
        <?php endif; ?>
      </section>

      <section class="teacher-panel demo-account-guide">
        <div class="teacher-panel-head"><div><span>Testovací workflow</span><h2>Jak demo používat</h2></div></div>
        <ol class="demo-account-steps">
          <li><b>1</b><span><strong>Vyberte třídu</strong><small>1.A / 2.A Grafika nebo 3.A / 4.A SOSaPS.</small></span></li>
          <li><b>2</b><span><strong>Vytvořte účet</strong><small>E-mail a silné heslo mohou vzniknout automaticky.</small></span></li>
          <li><b>3</b><span><strong>Přihlaste se jako student</strong><small>Účet skočí rovnou do propojené třídy bez ručního párování.</small></span></li>
          <li><b>4</b><span><strong>Po testu resetujte nebo smažte</strong><small>Administrace dovolí změnit heslo nebo odstranit pouze účty označené jako demo.</small></span></li>
        </ol>
      </section>
    </div>

    <section class="teacher-panel demo-account-list">
      <div class="teacher-panel-head"><div><span>Sandbox identity</span><h2>Aktivní demo účty</h2></div><span class="demo-count-badge"><?=count($rows)?> účtů</span></div>
      <?php if (!$rows): ?><div class="teacher-empty">Zatím není vytvořen žádný demo účet. Vytvořte první vlevo nahoře.</div><?php else: ?>
      <div class="demo-account-table-wrap"><table class="demo-account-table"><thead><tr><th>Student</th><th>Třída</th><th>E-mail</th><th>Vazba</th><th>Vytvořeno</th><th>Akce</th></tr></thead><tbody>
        <?php foreach($rows as $row): ?><tr>
          <td><span class="teacher-avatar demo"><?=e(u_substr((string)$row['name'],0,1))?></span><div><strong><?=e((string)$row['name'])?></strong><small>demo · <?=e((string)$row['created_by'])?></small></div></td>
          <td><strong><?=e(teacher_class_label((string)$row['class_id']))?></strong><small><?=e((string)$row['subject'])?></small></td>
          <td><code><?=e((string)$row['email'])?></code></td>
          <td><span class="demo-binding <?=!empty($row['binding_ok'])?'ok':'warn'?>"><?=!empty($row['binding_ok'])?'✓ připraveno':'! chybí vazba'?></span></td>
          <td><small><?=e(teacher_demo_accounts_date((string)$row['created_at']))?></small></td>
          <td><div class="demo-account-actions">
            <?php if($canManage): ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_demo_account_reset"><input type="hidden" name="return_tab" value="demo_accounts"><input type="hidden" name="demo_email" value="<?=e((string)$row['email'])?>"><button class="btn secondary small" type="submit">Reset hesla</button></form>
            <form method="post" onsubmit="return confirm('Odstranit demo účet <?=e((string)$row['email'])?>? Přihlášení a vazba na třídu budou odstraněny.');"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_demo_account_delete"><input type="hidden" name="return_tab" value="demo_accounts"><input type="hidden" name="demo_email" value="<?=e((string)$row['email'])?>"><button class="link-button danger" type="submit">Odstranit</button></form><?php endif; ?>
          </div></td>
        </tr><?php endforeach; ?>
      </tbody></table></div>
      <?php endif; ?>
    </section>
    <script>
    document.addEventListener('click',function(e){var b=e.target.closest('[data-copy-demo]');if(!b)return;var p=b.closest('[data-demo-credentials]');if(!p)return;var vals=p.querySelectorAll('[data-copy-value]');var t='E-mail: '+(vals[0]?.textContent||'')+'\nHeslo: '+(vals[1]?.textContent||'');navigator.clipboard?.writeText(t).then(function(){var old=b.textContent;b.textContent='✓ Zkopírováno';setTimeout(function(){b.textContent=old},1400)});});
    </script>
    <?php
}
