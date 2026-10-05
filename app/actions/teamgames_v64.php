<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v64 · POST tg64_retro – retrospektiva týmové hry (CSRF už ověřil index.php).
 * Identita žáka vždy ze session; hra musí patřit jeho třídě, být dohraná a žák musí být v jejím týmu.
 */

if ($action === 'tg64_retro') {
    $actionClassId = current_class_id($modules);
    if ($actionClassId === null || !isset($modules[$actionClassId])) { $_SESSION['flash'] = tr('Nejdřív se přihlas do své třídy.'); redirect_to('?view=home'); }
    $studentKey = adaptive_student_key($actionClassId);
    $gameId = is_string($_POST['game'] ?? null) ? $_POST['game'] : '';
    $back = '?view=hry';
    try {
        $game = tg58_valid_id($gameId) ? tg58_get($gameId) : null;
        if ($studentKey === '' || $game === null || (string)$game['class_id'] !== $actionClassId) throw new RuntimeException(tr('Hra nebyla nalezena.'));
        $back = '?view=hry&hra=' . rawurlencode($gameId);
        if (tg58_status($game, tg58_now()) !== 'finished' && (string)$game['status'] !== 'finished') throw new RuntimeException(tr('Retrospektiva jde odeslat, až hra skončí.'));
        $teamId = tg58_team_of($game, $studentKey);
        if ($teamId === null) throw new RuntimeException(tr('Ještě nejsi v žádném týmu.'));
        $answers = is_array($_POST['a'] ?? null) ? array_values((array)$_POST['a']) : [];
        tg64_retro_save($actionClassId, $gameId, $teamId, $studentKey, $answers);
        $_SESSION['flash'] = tr('Díky, retrospektiva je uložená.');
    } catch (Throwable $e) {
        $_SESSION['flash'] = $e instanceof RuntimeException ? $e->getMessage() : tr('Něco se pokazilo. Zkus to za chvilku znovu.');
    }
    redirect_to($back);
}
