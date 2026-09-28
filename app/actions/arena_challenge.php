<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · ARN-07 – POST akce výzev spolužákům (CSRF už ověřil index.php).
 * Identita žáka vždy ze session ($actionClassId/$studentKey), nikdy z parametru.
 */

if (in_array($action, ['arena60_challenge_create', 'arena60_challenge_respond', 'arena60_challenge_cancel', 'arena60_optin_set'], true)) {
    $actionClassId = current_class_id($modules);
    if ($actionClassId === null || !isset($modules[$actionClassId])) { $_SESSION['flash'] = tr('Nejdřív se přihlas do své třídy.'); redirect_to('?view=home'); }
    $studentKey = social_current_student_key($actionClassId);
    if ($studentKey === '' || !social_student_exists($actionClassId, $studentKey)) { $_SESSION['flash'] = tr('Studentský profil není propojený se třídou.'); redirect_to('?view=link_account'); }
    $now = arena57_now();
    $back = '?view=profile&tab=arena';
    try {
        if ($action === 'arena60_optin_set') {
            arena60_optin_set($actionClassId, $studentKey, (string)($_POST['on'] ?? '') === '1');
            $_SESSION['flash'] = tr('Nastavení výzev bylo uloženo.');
        } elseif ($action === 'arena60_challenge_create') {
            $toKey = (string)($_POST['to_key'] ?? '');
            arena60_challenge_create($actionClassId, $studentKey, $toKey, $now);
            $_SESSION['flash'] = tr('Výzva byla odeslána.');
            $back = '?view=profile&student=' . rawurlencode($toKey);
        } elseif ($action === 'arena60_challenge_respond') {
            $id = (string)($_POST['id'] ?? '');
            $accept = (string)($_POST['accept'] ?? '') === '1';
            arena60_challenge_respond($actionClassId, $studentKey, $id, $accept, $now);
            $_SESSION['flash'] = $accept ? tr('Výzva přijata, hodně štěstí!') : tr('Výzva byla odmítnuta.');
        } elseif ($action === 'arena60_challenge_cancel') {
            $id = (string)($_POST['id'] ?? '');
            arena60_challenge_cancel($actionClassId, $studentKey, $id, $now);
            $_SESSION['flash'] = tr('Výzva byla zrušena.');
        }
    } catch (Throwable $e) {
        $_SESSION['flash'] = $e->getMessage();
    }
    redirect_to($back);
}
