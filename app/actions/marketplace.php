<?php

declare(strict_types=1);

/**
 * POST akce žáka pro obchod bodů (v60). Router: app/routes.php ('actions_class').
 * CSRF a třída/identita žáka ($classId, $module) už zajistil index.php.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'mkt60_buy') {
    $studentKey = adaptive_student_key((string)$classId);
    $itemId = is_string($_POST['item_id'] ?? null) ? (string)$_POST['item_id'] : '';
    $requestId = is_string($_POST['request_id'] ?? null) ? preg_replace('/[^a-f0-9]/', '', (string)$_POST['request_id']) : '';
    $qty = max(1, min(20, (int)($_POST['qty'] ?? 1)));
    $result = mkt60_buy((string)$classId, $studentKey, $itemId, $qty, (string)$requestId);
    $_SESSION['flash'] = $result['ok']
        ? tr('Nákup proběhl. Zůstatek: {n} bodů.', ['n' => (int)$result['balance']])
        : tr('Nákup se nepodařil.') . ' (' . (string)($result['error'] ?? '') . ')';
    redirect_to('?view=obchod');
}

if ($action === 'mkt60_cosmetic_set') {
    $studentKey = adaptive_student_key((string)$classId);
    $slot = is_string($_POST['slot'] ?? null) ? (string)$_POST['slot'] : '';
    $itemId = is_string($_POST['item_id'] ?? null) ? (string)$_POST['item_id'] : '';
    if (!mkt60_set_cosmetic((string)$classId, $studentKey, $slot, $itemId)) {
        $_SESSION['flash'] = tr('Kosmetiku se nepodařilo nastavit.');
    }
    // Návrat jen na povolené místo (whitelist) – z nastavení profilu zpět do nastavení, jinak do obchodu.
    $returnToSettings = ($_POST['return_tab'] ?? '') === 'nastaveni';
    redirect_to($returnToSettings ? '?view=profile&tab=nastaveni' : '?view=obchod');
}
