<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 · Učitelská záložka „Přístupy“ (teacher.php?tab=pristupy) a tisk kartiček s jednorázovými hesly.
 *   acc58_render_teacher_accounts(string $classId, string $csrf, array $modules = []): void
 *   acc58_render_cards_print(string $classId, array $modules = []): void   – samostatná stránka A4
 *   acc58_teacher_handle_post(string $action, array $modules): void        – akce acc58_issue_one, acc58_issue_class
 *   acc58_teacher_apply(string $action, array $post, array $modules): array – jádro akce bez přesměrování (testy)
 * Heslo se učiteli ukáže jednou v oznámení po vydání a na kartičkách (jen dokud je 'pending').
 */

require_once __DIR__ . '/accounts_v58.php';

const ACC58_REVEAL_TTL = 300;
const ACC58_ASSET_VERSION = '58.0';

function acc58_teacher_can_manage(string $classId, array $modules): bool
{
    if ($classId === '' || !isset($modules[$classId])) return false;
    if (!function_exists('teacher_export_authenticated') || !teacher_export_authenticated()) return false;
    // v59 · AUTHZ58-07: třída musí být v rozsahu přihlášeného učitele (legacy = všechny).
    if (function_exists('teacher59_can_class') && !teacher59_can_class($classId)) return false;
    // Deny-by-default: bez matice oprávnění (teacher_operations_v46.php) se nic nevydá.
    return function_exists('teacher_permission') && teacher_permission('students.manage');
}

function acc58_verify_post_csrf(array $post): void
{
    $token = $post['csrf'] ?? '';
    $expected = $_SESSION['csrf'] ?? '';
    if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
        throw new RuntimeException('Neplatný nebo expirovaný formulář. Obnovte stránku a zkuste to znovu.');
    }
}

function acc58_date(?int $ts): string
{
    return $ts ? date('j. n. Y', $ts) : '—';
}

function acc58_class_name(string $classId, array $modules): string
{
    $m = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    return trim((string)($m['name'] ?? $classId) . (isset($m['subject']) ? ' · ' . (string)$m['subject'] : ''));
}

/** Adresa aplikace pro kartičky: EDUCANET_APP_URL, jinak odvozená z požadavku (hostitel očištěný v request_base_url). */
function acc58_app_url(): string
{
    return rtrim(request_base_url(), '/') . '/';
}

/** Provede učitelskou akci. Vrací ['class','flash','reveal'?,'once'?]. Výjimka = odmítnuto. */
function acc58_teacher_apply(string $action, array $post, array $modules): array
{
    $classId = is_string($post['class_id'] ?? null) ? $post['class_id'] : '';
    if (!acc58_teacher_can_manage($classId, $modules)) throw new RuntimeException('Na správu přístupů této třídy nemáte oprávnění.');
    $noCopy = acc58_crypto_backend() === 'none';
    if ($action === 'acc58_issue_one') {
        $email = local_email_normalize(is_string($post['email'] ?? null) ? $post['email'] : '');
        $row = local_accounts()[$email] ?? null;
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) throw new RuntimeException('Žák v této třídě nebyl nalezen.');
        $plain = acc58_issue_otp($email, 'teacher');
        $label = (string)($row['student_label'] ?? $row['name'] ?? $email);
        return ['class' => $classId, 'flash' => 'Nové jednorázové heslo je připravené (' . $label . ').',
            'reveal' => ['email' => $email, 'label' => $label, 'at' => time(), 'plain' => $noCopy ? $plain : null]];
    }
    if ($action === 'acc58_issue_class') {
        $rows = acc58_issue_for_class($classId, false, 'teacher');
        $flash = $rows ? 'Nová jednorázová hesla: ' . count($rows) . '. Vytiskněte kartičky a rozdejte je žákům.' : 'Všichni žáci už mají vlastní heslo – není co generovat.';
        return ['class' => $classId, 'flash' => $flash, 'once' => $noCopy ? $rows : []];
    }
    throw new RuntimeException('Neznámá akce.');
}

function acc58_teacher_handle_post(string $action, array $modules): void
{
    if (!str_starts_with($action, 'acc58_')) return;
    acc58_verify_post_csrf($_POST);
    $result = acc58_teacher_apply($action, $_POST, $modules);
    if (!empty($result['reveal'])) $_SESSION['acc58_reveal'] = $result['reveal'];
    // Bez šifrované kopie (žádné sodium/openssl) se hesla třídy ukážou jen jednou na tiskové stránce.
    if (!empty($result['once'])) $_SESSION['acc58_once_cards'] = [(string)$result['class'] => $result['once']];
    if (function_exists('teacher_flash')) teacher_flash((string)$result['flash']);
    $params = ['tab' => 'pristupy', 'class' => (string)$result['class']];
    if (function_exists('teacher_redirect')) teacher_redirect($params);
    header('Location: teacher.php?' . http_build_query($params), true, 303);
    exit;
}

/** Jednorázové oznámení s novým heslem (po akci „Nové jednorázové heslo“). */
function acc58_take_reveal(string $classId): ?array
{
    $reveal = $_SESSION['acc58_reveal'] ?? null;
    unset($_SESSION['acc58_reveal']);
    if (!is_array($reveal) || (int)($reveal['at'] ?? 0) < time() - ACC58_REVEAL_TTL) return null;
    $row = local_accounts()[(string)($reveal['email'] ?? '')] ?? null;
    if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) return null;
    $password = is_string($reveal['plain'] ?? null) ? $reveal['plain'] : acc58_reveal_otp((string)$reveal['email']);
    if ($password === null) return null;
    return ['label' => (string)$reveal['label'], 'password' => $password, 'expires_at' => (int)($row['otp']['expires_at'] ?? 0)];
}

function acc58_render_class_switch(string $classId, array $modules): void
{
    if (count($modules) < 2) return;
    ?>
    <nav class="a58-classes" aria-label="Třída">
      <?php foreach ($modules as $cid => $m): if (!is_array($m)) continue; ?>
        <a href="?<?= e(http_build_query(['tab' => 'pristupy', 'class' => (string)$cid])) ?>"<?= (string)$cid === $classId ? ' aria-current="page" class="active"' : '' ?>><?= e((string)($m['name'] ?? $cid)) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php
}

function acc58_render_account_row(array $row, string $classId, string $csrf): void
{
    $status = (string)$row['status'];
    $when = $row['last_login_at'] !== '' ? date('j. n. Y H:i', (int)strtotime((string)$row['last_login_at'])) : 'zatím ne';
    ?>
    <tr>
      <th scope="row" data-label="Jméno"><?= e((string)$row['label']) ?><?= $row['demo'] ? ' <small class="a58-tag">demo</small>' : '' ?></th>
      <td data-label="Přihlašovací e-mail"><code><?= e((string)$row['email']) ?></code></td>
      <td data-label="Stav hesla"><span class="a58-status a58-status-<?= e($status) ?>"><?= e(acc58_status_label($status)) ?></span>
        <?php if ($status === 'pending'): ?><small>platí do <?= e(acc58_date($row['expires_at'])) ?></small><?php elseif ($status === 'expired'): ?><small>vypršelo <?= e(acc58_date($row['expires_at'])) ?></small><?php endif; ?></td>
      <td data-label="Poslední přihlášení"><?= e($when) ?></td>
      <td data-label="Akce">
        <form method="post" class="a58-inline">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="acc58_issue_one">
          <input type="hidden" name="return_tab" value="pristupy"><input type="hidden" name="class_id" value="<?= e($classId) ?>"><input type="hidden" name="email" value="<?= e((string)$row['email']) ?>">
          <button class="a58-btn" type="submit" aria-label="Nové jednorázové heslo pro <?= e((string)$row['label']) ?>">Nové jednorázové heslo</button>
        </form>
      </td>
    </tr>
    <?php
}

function acc58_render_teacher_accounts(string $classId, string $csrf, array $modules = []): void
{
    if ($modules && !isset($modules[$classId])) $classId = (string)array_key_first($modules);
    $rows = acc58_class_rows($classId);
    $counts = array_count_values(array_map(static fn(array $r): string => (string)$r['status'], $rows));
    $reveal = acc58_take_reveal($classId);
    $printUrl = '?' . http_build_query(['tab' => 'pristupy', 'print' => '1', 'class' => $classId]);
    ?>
    <link rel="stylesheet" href="assets/tokens-palette-v68.css?v=68.0">
    <link rel="stylesheet" href="assets/accounts-v58.css?v=<?= e(ACC58_ASSET_VERSION) ?>">
    <section class="a58" aria-labelledby="a58-title">
      <header class="a58-head">
        <div>
          <span class="a58-kicker">Přístupy žáků</span>
          <h1 id="a58-title">Jednorázová hesla · <?= e(acc58_class_name($classId, $modules)) ?></h1>
          <p>Každý žák dostane vlastní heslo na kartičce. Platí <?= ACC58_OTP_TTL_DAYS ?> dní a po prvním přihlášení si ho žák změní na své.</p>
        </div>
        <div class="a58-actions">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="acc58_issue_class">
            <input type="hidden" name="return_tab" value="pristupy"><input type="hidden" name="class_id" value="<?= e($classId) ?>">
            <button class="a58-btn primary" type="submit" aria-describedby="a58-class-note">Vygenerovat pro celou třídu (jen žáci bez vlastního hesla)</button>
          </form>
          <a class="a58-btn" href="<?= e($printUrl) ?>" target="_blank" rel="noopener">Tisk kartiček</a>
          <p id="a58-class-note" class="a58-note">Dosud nevyužité kartičky přestanou platit. Žáků s vlastním heslem se to netýká.</p>
        </div>
      </header>
      <?php acc58_render_class_switch($classId, $modules); ?>
      <div role="status" aria-live="polite">
        <?php if ($reveal !== null): ?>
          <div class="a58-reveal">
            <strong>Nové jednorázové heslo · <?= e($reveal['label']) ?></strong>
            <code class="a58-otp"><?= e($reveal['password']) ?></code>
            <span>Platí do <?= e(acc58_date($reveal['expires_at'])) ?>. Tady se ukazuje jen teď – předejte ho žákovi nebo vytiskněte kartičku.</span>
          </div>
        <?php endif; ?>
      </div>
      <ul class="a58-summary" aria-label="Souhrn třídy">
        <li><b><?= (int)($counts['own'] ?? 0) ?></b> vlastní heslo</li>
        <li><b><?= (int)($counts['pending'] ?? 0) ?></b> čeká na první přihlášení</li>
        <li><b><?= (int)($counts['expired'] ?? 0) ?></b> vypršelo</li>
        <li><b><?= (int)($counts['legacy'] ?? 0) ?></b> bez hesla od učitele</li>
      </ul>
      <?php if (!$rows): ?>
        <p class="a58-empty">V této třídě zatím nejsou žádné školní účty.</p>
      <?php else: ?>
        <div class="a58-table-wrap">
          <table class="a58-table">
            <caption class="a58-sr">Účty žáků třídy <?= e(acc58_class_name($classId, $modules)) ?></caption>
            <thead><tr><th scope="col">Jméno</th><th scope="col">Přihlašovací e-mail</th><th scope="col">Stav hesla</th><th scope="col">Poslední přihlášení</th><th scope="col">Akce</th></tr></thead>
            <tbody><?php foreach ($rows as $row) acc58_render_account_row($row, $classId, $csrf); ?></tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
    <?php
}

/** Hesla z jednorázového výpisu (jen když chybí šifrovaná kopie). Po zobrazení se ze session smažou. */
function acc58_take_once_cards(string $classId): array
{
    $once = $_SESSION['acc58_once_cards'][$classId] ?? [];
    unset($_SESSION['acc58_once_cards']);
    return is_array($once) ? $once : [];
}

function acc58_render_card(array $card, string $className, string $appUrl): void
{
    ?>
    <article class="a58-card" aria-label="Kartička: <?= e((string)$card['label']) ?>">
      <header><strong><?= e((string)$card['label']) ?></strong><span><?= e($className) ?></span></header>
      <dl>
        <dt>Přihlašovací e-mail</dt><dd><code><?= e((string)$card['email']) ?></code></dd>
        <dt class="a58-wide">Jednorázové heslo</dt><dd class="a58-wide"><code class="a58-otp"><?= e((string)$card['password']) ?></code></dd>
        <dt>Platí do</dt><dd><?= e(acc58_date((int)$card['expires_at'])) ?></dd>
        <dt>Adresa aplikace</dt><dd><code><?= e($appUrl) ?></code></dd>
      </dl>
      <ol>
        <li>Otevři adresu aplikace.</li>
        <li>Přihlas se e-mailem a jednorázovým heslem.</li>
        <li>Nastav si vlastní heslo (aspoň <?= LOCAL_PASSWORD_MIN_LENGTH ?> znaků, písmeno i číslice).</li>
      </ol>
    </article>
    <?php
}

/** Samostatná tisková stránka A4 s kartičkami k rozstříhání. */
function acc58_render_cards_print(string $classId, array $modules = []): void
{
    if (!acc58_teacher_can_manage($classId, $modules)) { http_response_code(403); exit('Na tisk kartiček této třídy nemáte oprávnění.'); }
    if (!headers_sent()) {
        header('Cache-Control: no-store, max-age=0');
        header('Pragma: no-cache');
        header('Referrer-Policy: no-referrer');
        header('X-Robots-Tag: noindex, nofollow');
    }
    $once = acc58_take_once_cards($classId);
    $cards = [];
    $missing = 0;
    foreach (acc58_class_cards($classId) as $card) {
        if ($card['password'] === null && isset($once[$card['email']]['password'])) {
            $card['password'] = (string)$once[$card['email']]['password'];
            $card['expires_at'] = (int)$once[$card['email']]['expires_at'];
        }
        if ($card['password'] !== null) $cards[] = $card; elseif ($card['status'] !== 'own') $missing++;
    }
    acc58_log_many([array_replace(acc58_log_entry('cards_printed', '', $classId, 'teacher'), ['count' => count($cards)])]);
    $className = acc58_class_name($classId, $modules);
    $appUrl = acc58_app_url();
    ?><!doctype html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>Kartičky s hesly · <?= e($className) ?></title>
<link rel="stylesheet" href="assets/tokens-palette-v68.css?v=68.0"><link rel="stylesheet" href="assets/accounts-v58.css?v=<?= e(ACC58_ASSET_VERSION) ?>"></head>
<body class="a58-print-body"><main class="a58-print">
  <header class="a58-print-head">
    <div><h1>Kartičky s jednorázovými hesly · <?= e($className) ?></h1>
      <p>Rozstříhejte podél čárkovaných čar a každému žákovi dejte jen jeho kartičku. Hesla platí <?= ACC58_OTP_TTL_DAYS ?> dní.</p></div>
    <div class="a58-print-actions"><button type="button" class="a58-btn primary" data-a58-print>Vytisknout</button><a class="a58-btn" href="?<?= e(http_build_query(['tab' => 'pristupy', 'class' => $classId])) ?>">Zpět na přístupy</a></div>
  </header>
  <?php if ($missing > 0): ?><p class="a58-note a58-screen-only" role="note">Bez kartičky: <?= (int)$missing ?> (heslo vypršelo nebo ještě nebylo vydáno). V záložce Přístupy vygenerujte nová hesla.</p><?php endif; ?>
  <?php if (!$cards): ?>
    <p class="a58-empty">Žádná kartička k tisku. Nejdřív vygenerujte jednorázová hesla.</p>
  <?php else: ?>
    <div class="a58-cards"><?php foreach ($cards as $card) acc58_render_card($card, $className, $appUrl); ?></div>
  <?php endif; ?>
</main><script src="assets/accounts-v58.js?v=<?= e(ACC58_ASSET_VERSION) ?>" defer></script></body></html>
<?php
}
