<?php

declare(strict_types=1);

/**
 * POST sess53_* – kód hodiny, registrace, vstup, odevzdání.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (str_starts_with($action, 'sess53_')) {
    if ($action === 'sess53_code') {
        $joinSession = sess53_find_by_code((string)($_POST['code'] ?? ''));
        if (!$joinSession || empty($joinSession['open'])) { $_SESSION['flash'] = tr('Tento kód hodiny neplatí. Zkontroluj ho na tabuli.'); redirect_to('?view=join'); }
        $_SESSION['sess53_code'] = (string)$joinSession['code'];
        redirect_to('?view=join');
    }
    if ($action === 'sess53_register') {
        $joinSession = sess53_find_by_code((string)($_POST['code'] ?? ''));
        if (!$joinSession || empty($joinSession['open'])) { $_SESSION['flash'] = tr('Hodina už není otevřená.'); redirect_to('?view=join'); }
        // v58 · SEC58-01: limit pokusů o registraci na IP (počítá se každý pokus, i úspěšný).
        if (!auth_rate_limit_check('sess53-register', SESS53_REGISTER_LIMIT, SESS53_REGISTER_WINDOW)) { $_SESSION['flash'] = tr('Příliš mnoho pokusů o registraci. Zkus to za pár minut, nebo se obrať na učitele.'); redirect_to('?view=join'); }
        auth_rate_limit_fail('sess53-register');
        try {
            sess53_join_register($joinSession, $modules, $_POST);
        } catch (Throwable $joinError) {
            // v59 · SEC59-16: žákovi jen hláška z RuntimeException; jiné chyby (TypeError, I/O) jen do logu.
            if (!$joinError instanceof RuntimeException) error_log('EDUCANET v53 registrace: ' . get_class($joinError) . ': ' . $joinError->getMessage() . ' @ ' . basename($joinError->getFile()) . ':' . $joinError->getLine());
            $joinErrorMessage = $joinError instanceof RuntimeException ? $joinError->getMessage() : '';
            $_SESSION['flash'] = $joinErrorMessage !== '' ? tr($joinErrorMessage) : tr('Akci se nepodařilo dokončit.');
            redirect_to('?view=join');
        }
        $_SESSION['flash'] = tr('Účet je hotový. Vyplň prosím seznamovací dotazník.');
        redirect_to('?view=intake');
    }
    if ($action === 'sess53_enter') {
        $joinSession = sess53_find_by_code((string)($_POST['code'] ?? ''));
        $joinUser = auth_user();
        if (!$joinSession || empty($joinSession['open']) || !$joinUser) { $_SESSION['flash'] = tr('Nejdřív se přihlas a použij platný kód.'); redirect_to('?view=join'); }
        $joinClass = (string)$joinSession['class_id'];
        if (current_class_id($modules) !== $joinClass) {
            $map = student_account_map();
            $row = $map[auth_assignment_key($joinUser)] ?? null;
            if (!is_array($row) || (string)($row['class_id'] ?? '') !== $joinClass) { $_SESSION['flash'] = tr('Tento kód patří jiné třídě.'); redirect_to('?view=dashboard'); }
            $_SESSION['next_class_id'] = $joinClass;
            $_SESSION['student_label'] = (string)($row['student_label'] ?? '');
        }
        $_SESSION['sess53_joined'] = (string)$joinSession['id'];
        unset($_SESSION['sess53_code']);
        redirect_to((string)$joinSession['kind'] === 'intake' ? '?view=intake' : '?view=hodina');
    }
    if ($action === 'sess53_submit') {
        $workClass = current_class_id($modules);
        if ($workClass === null) { $_SESSION['flash'] = tr('Nejdřív se přihlas.'); redirect_to('?view=home'); }
        $workSession = sess53_find((string)($_POST['session'] ?? ''));
        if (!$workSession || (string)$workSession['class_id'] !== $workClass) { $_SESSION['flash'] = tr('Hodina nebyla nalezena.'); redirect_to('?view=dashboard'); }
        $status = (string)($_POST['status'] ?? 'draft') === 'submitted' ? 'submitted' : 'draft';
        sess53_submit((string)$workSession['id'], adaptive_student_key($workClass), trim((string)($_SESSION['student_label'] ?? '')), [
            'checks' => (array)($_POST['checks'] ?? []), 'note' => $_POST['note'] ?? '', 'link' => $_POST['link'] ?? '', 'status' => $status,
        ]);
        $_SESSION['flash'] = $status === 'submitted' ? tr('Odevzdáno. Učitel to uvidí hned v Režimu hodiny.') : tr('Rozpracovaná práce je uložená.');
        redirect_to('?view=hodina');
    }
}
