<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Týmové hry – JSON API žákovské stránky.
 * POST (csrf, op=state|signal|action, game=<id>, v=<verze pro state>, kind=<signál>, action=<jméno>, payload=<JSON>).
 * Identita žáka je vždy ze session (adaptive_student_key), nikdy z parametru. `session_write_close()`
 * hned po ověření identity a rate limitu (DAT-04); state vrací {changed:false}, když se verze nezměnila.
 */

require __DIR__ . '/bootstrap.php';
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }
require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';
require_once __DIR__ . '/teamgames_v58_game_relay.php';
require_once __DIR__ . '/teamgames_v58_game_bingo.php';
require_once __DIR__ . '/teamgames_v58_game_jeopardy.php';
require_once __DIR__ . '/teamgames_v58_game_tug.php';
require_once __DIR__ . '/teamgames_v58_game_netadmin.php';
require_once __DIR__ . '/teamgames_v58_game_escape.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

const TG58_API_LIMITS = ['state' => [40, 60], 'signal' => [8, 60], 'action' => [40, 60]];

function tg58_api_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') tg58_api_out(['ok' => false, 'error' => tr('Použij POST.')], 405);
if (!tg58_csrf_ok($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) tg58_api_out(['ok' => false, 'error' => tr('Relace vypršela. Obnov stránku.')], 419);

$classId = current_class_id($modules);
$studentKey = is_string($classId) && function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
if (!is_string($classId) || $studentKey === '') tg58_api_out(['ok' => false, 'error' => tr('Nejsi přihlášen(a) ke třídě.')], 401);
if (function_exists('acc53_must_change_password') && acc53_must_change_password()) tg58_api_out(['ok' => false, 'error' => tr('Nejdřív si nastav vlastní heslo.')], 403);

$op = is_string($_POST['op'] ?? null) ? $_POST['op'] : '';
if (!isset(TG58_API_LIMITS[$op])) tg58_api_out(['ok' => false, 'error' => tr('Neznámá operace.')], 400);
$now = tg58_now();
[$max, $window] = TG58_API_LIMITS[$op];
if (!tg58_rate_ok('api:' . $op, $max, $window, $now)) tg58_api_out(['ok' => false, 'error' => tr('Moc rychle za sebou – chvilku počkej a zkus to znovu.')], 429);

$gameId = is_string($_POST['game'] ?? null) ? $_POST['game'] : '';
if (!tg58_valid_id($gameId)) tg58_api_out(['ok' => false, 'error' => tr('Neplatná hra.')], 400);

// XP jen v požadavku žáka (profil žije v jeho session) – hned po ověření identity, před uvolněním zámku session.
tg58_claim_xp($classId, $studentKey);
session_write_close();

try {
    $session = tg58_get($gameId);
    if ($session === null || (string)$session['class_id'] !== $classId) tg58_api_out(['ok' => false, 'error' => tr('Hra nebyla nalezena.')], 404);

    if ($op === 'state') {
        $view = tg58_student_view($session, $studentKey, $now);
        $version = tg58_version_of($view);
        $since = is_string($_POST['v'] ?? null) ? $_POST['v'] : '';
        if ($since === $version) tg58_api_out(['ok' => true, 'changed' => false, 'version' => $version]);
        tg58_api_out(['ok' => true, 'changed' => true, 'version' => $version, 'state' => $view]);
    }

    if ($op === 'signal') {
        $kind = is_string($_POST['kind'] ?? null) ? $_POST['kind'] : '';
        $teamId = tg58_team_of($session, $studentKey);
        if ($teamId === null) tg58_api_out(['ok' => false, 'error' => tr('Ještě nejsi v žádném týmu.')], 409);
        tg58_send_signal($gameId, $teamId, $studentKey, $kind, $now);
        tg58_api_out(['ok' => true]);
    }

    if ($op === 'action') {
        $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
        if ($action === '' || mb_strlen($action) > 40) tg58_api_out(['ok' => false, 'error' => tr('Neplatná akce.')], 400);
        $payloadRaw = is_string($_POST['payload'] ?? null) ? $_POST['payload'] : '[]';
        if (strlen($payloadRaw) > 2000) tg58_api_out(['ok' => false, 'error' => tr('Data akce jsou příliš velká.')], 413);
        $payload = json_decode($payloadRaw, true);
        $payload = is_array($payload) ? $payload : [];
        $response = tg58_student_action($gameId, $action, $studentKey, $payload, $now);
        tg58_api_out($response === [] ? ['ok' => true] : $response);
    }
} catch (Throwable $e) {
    if ($e instanceof RuntimeException) tg58_api_out(['ok' => false, 'error' => $e->getMessage()], 422);
    error_log('EDUCANET v58 tg API: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    tg58_api_out(['ok' => false, 'error' => tr('Něco se pokazilo. Zkus to za chvilku znovu.')], 500);
}
tg58_api_out(['ok' => false, 'error' => tr('Neznámá operace.')], 400);
