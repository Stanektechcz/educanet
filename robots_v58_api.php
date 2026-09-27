<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Robotí liga – JSON API žákovské stránky.
 * POST (csrf, op=validate|test|save|submit|state|replay, code, match, turns, map, sparring, v).
 * Identita žáka je vždy ze session, nikdy z parametru. Skripty interpretuje robots_v58_lang.php – nic se nespouští.
 */

require __DIR__ . '/bootstrap.php';
if (is_file(__DIR__ . '/arena_v57.php')) require_once __DIR__ . '/arena_v57.php';
require_once __DIR__ . '/robots_v58.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

const ROBOTS58_API_LIMITS = ['validate' => [40, 60], 'test' => [12, 60], 'save' => [20, 60], 'submit' => [10, 60], 'state' => [40, 60], 'replay' => [20, 60]];
const ROBOTS58_API_MAX_CODE = 8192;

function robots58_api_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') robots58_api_out(['ok' => false, 'error' => tr('Použij POST.')], 405);
if (!robots58_csrf_ok($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) robots58_api_out(['ok' => false, 'error' => tr('Relace vypršela. Obnov stránku.')], 419);

$classId = current_class_id($modules);
$studentKey = is_string($classId) ? adaptive_student_key($classId) : '';
if (!is_string($classId) || $studentKey === '') robots58_api_out(['ok' => false, 'error' => tr('Nejsi přihlášen(a) ke třídě.')], 401);
if (function_exists('acc53_must_change_password') && acc53_must_change_password()) robots58_api_out(['ok' => false, 'error' => tr('Nejdřív si nastav vlastní heslo.')], 403);
if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled($classId)) robots58_api_out(['ok' => false, 'error' => tr('Linux Lab a Robotí liga jsou pro tvou třídu vypnuté.')], 403);

$op = is_string($_POST['op'] ?? null) ? $_POST['op'] : '';
if (!isset(ROBOTS58_API_LIMITS[$op])) robots58_api_out(['ok' => false, 'error' => tr('Neznámá operace.')], 400);
$now = robots58_now();
[$max, $window] = ROBOTS58_API_LIMITS[$op];
if (!robots58_rate_ok($op, $max, $window, $now)) robots58_api_out(['ok' => false, 'error' => tr('Moc rychle za sebou – chvilku počkej a zkus to znovu.')], 429);

$code = is_string($_POST['code'] ?? null) ? $_POST['code'] : '';
if (strlen($code) > ROBOTS58_API_MAX_CODE) robots58_api_out(['ok' => false, 'error' => tr('Skript je příliš dlouhý (limit 4 KB).')], 413);
$matchId = is_string($_POST['match'] ?? null) ? $_POST['match'] : '';
if ($matchId !== '' && !robots58_valid_id($matchId)) robots58_api_out(['ok' => false, 'error' => tr('Neplatný zápas.')], 400);
$label = trim((string)($_SESSION['student_label'] ?? ''));

try {
    if ($op === 'replay') {
        // Vyzvednutí XP za odehrané zápasy – jen v požadavku tohoto žáka (profil žije v jeho session).
        $claimed = robots58_claim_xp($classId, $studentKey);
        session_write_close();
        $match = robots58_match($matchId);
        if ($match === null || (string)$match['class_id'] !== $classId) robots58_api_out(['ok' => false, 'error' => tr('Zápas nebyl nalezen.')], 404);
        $payload = robots58_replay_payload($matchId, $studentKey);
        if ($payload === null) robots58_api_out(['ok' => false, 'error' => tr('Záznam zatím není – zápas se ještě nehrál.')], 404);
        robots58_api_out(['ok' => true, 'xp_claimed' => $claimed] + $payload);
    }
    session_write_close();
    switch ($op) {
        case 'state':
            $state = robots58_student_state($classId, $studentKey, $now);
            if (($_POST['v'] ?? '') === $state['version']) robots58_api_out(['ok' => true, 'changed' => false, 'version' => $state['version']]);
            robots58_api_out(['ok' => true, 'changed' => true, 'version' => $state['version'], 'state' => $state]);
        case 'validate':
            $parsed = robots58_parse($code, false);
            robots58_api_out(['ok' => true, 'valid' => $parsed['ok'], 'error' => $parsed['error'], 'text' => $parsed['ok'] ? tr('Skript je v pořádku.') : robots58_error_text($parsed['error']), 'warnings' => $parsed['warnings'], 'stats' => $parsed['stats']]);
        case 'test':
            $result = robots58_training($code, (int)($_POST['turns'] ?? 150), (int)($_POST['map'] ?? 1), ($_POST['sparring'] ?? '1') === '1');
            if (strlen($code) <= ROBOTS58_MAX_BYTES) robots58_save_draft($classId, $studentKey, $code, $now);
            robots58_api_out($result['ok'] ? $result : ['ok' => true, 'valid' => false] + $result);
        case 'save':
            if (strlen($code) > ROBOTS58_MAX_BYTES) robots58_api_out(['ok' => false, 'error' => tr('Skript je delší než 4 KB.')], 413);
            $row = robots58_save_draft($classId, $studentKey, $code, $now);
            robots58_api_out(['ok' => true, 'at' => $row['at']]);
        case 'submit':
            $result = robots58_submit($classId, $studentKey, $label, $matchId, $code, $now);
            robots58_api_out($result, $result['ok'] ? 200 : 422);
    }
} catch (Throwable $e) {
    error_log('EDUCANET v58 roboti API: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    robots58_api_out(['ok' => false, 'error' => tr('Něco se pokazilo. Zkus to za chvilku znovu.')], 500);
}
robots58_api_out(['ok' => false, 'error' => tr('Neznámá operace.')], 400);
