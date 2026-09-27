<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Aréna – JSON API pro žákovské akce Incidentů (spuštění scénáře, pauza kvůli přístupnosti,
 * odevzdání postmortemu). CTF vlastní API nepotřebuje – terminál i žebříček jedou přes existující
 * lab_v57_api.php (ctx=ctf:<id>, op=state|run|board), stejně jako terminál Incidentů (ctx=incident:<id>).
 * Tenhle soubor obsluhuje jen akce KOLEM terminálu, které v generickém API nejsou (op=start|pause|resume|postmortem).
 */

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/arena_v58_incident.php';
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function arena58ev_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

/** Zúžený pohled na pokus pro klienta – bez textu postmortemu (klient ho zná, jen ho odeslal). */
function arena58ev_attempt_view(array $attempt, int $now): array
{
    return [
        'started' => !empty($attempt['started_at']),
        'paused' => !empty($attempt['paused_at']),
        'solved' => !empty($attempt['solved_at']),
        'remaining' => function_exists('arena58_inc_remaining') ? arena58_inc_remaining($attempt, $now) : null,
        'postmortem_submitted' => is_array($attempt['postmortem'] ?? null),
        'points' => (int)($attempt['postmortem']['points'] ?? 0),
    ];
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') arena58ev_out(['ok' => false, 'error' => tr('Použij POST.')], 405);
$csrf = (string)($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
if (!is_string($_SESSION['csrf'] ?? null) || !hash_equals((string)$_SESSION['csrf'], $csrf)) arena58ev_out(['ok' => false, 'error' => tr('Relace vypršela. Obnov stránku.')], 419);

$classId = current_class_id($modules);
$studentKey = is_string($classId) ? adaptive_student_key($classId) : '';
if (!is_string($classId) || $studentKey === '') arena58ev_out(['ok' => false, 'error' => tr('Nejsi přihlášen(a) ke třídě.')], 401);
// DAT-04: identita a CSRF jsou ověřené, session hned uvolníme; XP se udílí přes lab58_with_session().
session_write_close();
$GLOBALS['lab58_session_closed'] = true;
// SEC58-08: stejné brány jako lab_v57_api.php – povinná změna hesla a Lab vypnutý pro třídu.
if (function_exists('acc53_must_change_password') && acc53_must_change_password()) arena58ev_out(['ok' => false, 'error' => tr('Nejdřív si nastav vlastní heslo.')], 403);
if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled($classId)) arena58ev_out(['ok' => false, 'error' => tr('Linux Lab je pro tvou třídu vypnutý.')], 403);

$op = (string)($_POST['op'] ?? '');
$sessionId = (string)($_POST['session'] ?? '');
$scenarioId = (string)($_POST['scenario'] ?? '');
if (!arena58_inc_valid_id($sessionId)) arena58ev_out(['ok' => false, 'error' => tr('Neplatná směna.')], 400);
if (preg_match('/^[a-z0-9-]{2,32}$/', $scenarioId) !== 1) arena58ev_out(['ok' => false, 'error' => tr('Neplatný scénář.')], 400);

$session = arena58_inc_session($sessionId);
if ($session === null || (string)$session['class_id'] !== $classId) arena58ev_out(['ok' => false, 'error' => tr('Směna nebyla nalezena.')], 404);

$stateKey = arena58_inc_state_key(['id' => $sessionId, 'student' => $studentKey]);
$now = arena58_inc_now();

try {
    switch ($op) {
        case 'start':
            $attempt = arena58_inc_start_attempt($sessionId, $classId, $stateKey, $scenarioId, $now);
            arena58ev_out(['ok' => true, 'attempt' => arena58ev_attempt_view($attempt, $now)]);
        case 'pause':
        case 'resume':
            $attempt = arena58_inc_toggle_pause($sessionId, $stateKey, $scenarioId, $op === 'pause', $now);
            arena58ev_out(['ok' => true, 'attempt' => arena58ev_attempt_view($attempt, $now)]);
        case 'postmortem':
            $text = ['cause' => (string)($_POST['cause'] ?? ''), 'fix' => (string)($_POST['fix'] ?? ''), 'prevention' => (string)($_POST['prevention'] ?? '')];
            foreach ($text as $v) if (strlen($v) > 4000) arena58ev_out(['ok' => false, 'error' => tr('Text je příliš dlouhý.')], 413);
            $attempt = arena58_inc_submit_postmortem($sessionId, $classId, $stateKey, $scenarioId, $text, $now);
            if (function_exists('lab58_with_session')) lab58_with_session(static function () use ($classId, $studentKey): void { arena58_inc_award_xp($classId, $studentKey); });
            arena58ev_out(['ok' => true, 'attempt' => arena58ev_attempt_view($attempt, $now)]);
        default:
            arena58ev_out(['ok' => false, 'error' => tr('Neznámá operace.')], 400);
    }
} catch (RuntimeException $e) {
    arena58ev_out(['ok' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('EDUCANET v58 arena events API: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    arena58ev_out(['ok' => false, 'error' => tr('Nastala chyba, zkus to znovu.')], 500);
}
