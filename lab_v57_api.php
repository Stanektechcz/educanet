<?php

declare(strict_types=1);

/**
 * EDUCANET v57 · Linux Lab – JSON API pro terminál v prohlížeči.
 * POST (csrf, op, level, ctx, …). Nic se nespouští v systému – vše obstará simulátor linux_v57_*.
 */

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/linux_v57_lab.php';
if (is_file(__DIR__ . '/arena_v57.php')) require_once __DIR__ . '/arena_v57.php';
// v58 · soutěžní kontexty labu (CTF týden, incidenty) potřebují svá pravidla přístupu i v API.
if (is_file(__DIR__ . '/arena_v58_ctf.php')) require_once __DIR__ . '/arena_v58_ctf.php';
if (is_file(__DIR__ . '/arena_v58_incident.php')) require_once __DIR__ . '/arena_v58_incident.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function lab57_api_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    $json = (string)json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    // Délka odpovědi umožní prohlížeči dokončit požadavek dřív, než se po ní zapíše odložený log (v58).
    if (!headers_sent() && !filter_var(ini_get('zlib.output_compression'), FILTER_VALIDATE_BOOLEAN)) header('Content-Length: ' . strlen($json));
    echo $json;
    exit;
}

// v58: zápis logu příkazů (lab_v58_log.php) proběhne až po odeslání odpovědi – op=run nezdržuje.
$GLOBALS['lab58_log_defer'] = true;
register_shutdown_function(static function (): void {
    if (function_exists('lab58_log_flush_after_response')) lab58_log_flush_after_response();
});

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') lab57_api_out(['ok' => false, 'error' => tr('Použij POST.')], 405);
$csrf = (string)($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
if (!is_string($_SESSION['csrf'] ?? null) || !hash_equals((string)$_SESSION['csrf'], $csrf)) lab57_api_out(['ok' => false, 'error' => tr('Relace vypršela. Obnov stránku.')], 419);

$classId = current_class_id($modules);
$studentKey = is_string($classId) ? adaptive_student_key($classId) : '';
if (!is_string($classId) || $studentKey === '') lab57_api_out(['ok' => false, 'error' => tr('Nejsi přihlášen(a) ke třídě.')], 401);
$studentLabel = (string)($_SESSION['student_label'] ?? '');
// DAT-04: identita a CSRF jsou ověřené – session hned uvolníme, aby souběžné požadavky žáka na sebe nečekaly.
// Jediný pozdější zápis do session (XP za vyřešení v procvičování) ji krátce otevře přes lab58_with_session().
session_write_close();
$GLOBALS['lab58_session_closed'] = true;
if (function_exists('acc53_must_change_password') && acc53_must_change_password()) lab57_api_out(['ok' => false, 'error' => tr('Nejdřív si nastav vlastní heslo.')], 403);
if (function_exists('arena57_lab_enabled') && !arena57_lab_enabled($classId)) lab57_api_out(['ok' => false, 'error' => tr('Linux Lab je pro tvou třídu vypnutý.')], 403);

$op = (string)($_POST['op'] ?? 'state');
$levelId = (string)($_POST['level'] ?? 'sandbox');
$context = (string)($_POST['ctx'] ?? 'practice');
if (preg_match('/^[a-z0-9-]{2,32}$/', $levelId) !== 1) lab57_api_out(['ok' => false, 'error' => tr('Neplatná úroveň.')], 400);
// v58: practice, nebo <prefix>:<id> (^[a-z]{2,12}:[a-z0-9_-]{4,40}$) s prefixem registrovaným přes lab58_register_context.
if (!lab58_context_valid($context)) lab57_api_out(['ok' => false, 'error' => tr('Neplatný režim.')], 400);

$ctx = [
    'class' => $classId,
    'student' => $studentKey,
    'label' => $studentLabel,
    'context' => $context,
    'level' => $levelId,
    'now' => time(),
    'classmates' => static fn(): array => array_keys(project_students_for_class($classId)),
];

try {
    if ($op === 'board') {
        $prepared = lab58_ctx_prepare($ctx);
        if (!isset((lab58_context_spec((string)$prepared['prefix']) ?? [])['board'])) lab57_api_out(['ok' => false, 'error' => tr('Žádný závod.')], 404);
        lab57_api_out(['ok' => true, 'board' => lab58_ctx_call($prepared, 'board', [$prepared], [])]);
    }
    $input = [];
    if ($op === 'run' || $op === 'complete') {
        $line = (string)($_POST['line'] ?? '');
        if (strlen($line) > LAB57_MAX_LINE * 2) lab57_api_out(['ok' => false, 'error' => tr('Příkaz je příliš dlouhý.')], 413);
        $input['line'] = $line;
    } elseif ($op === 'save') {
        $content = (string)($_POST['content'] ?? '');
        if (strlen($content) > 200000) lab57_api_out(['ok' => false, 'error' => tr('Soubor je příliš velký.')], 413);
        $input = ['path' => (string)($_POST['path'] ?? ''), 'token' => (string)($_POST['token'] ?? ''), 'content' => $content];
    } elseif (!in_array($op, ['state', 'reset'], true)) {
        lab57_api_out(['ok' => false, 'error' => tr('Neznámá operace.')], 400);
    }
    $result = lab57_session($ctx, $op, $input);
    lab57_api_out($result, !empty($result['ok']) ? 200 : 403);
} catch (Throwable $e) {
    error_log('EDUCANET v57 lab API: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    lab57_api_out(['ok' => false, 'error' => tr('Simulátor narazil na chybu. Zkus příkaz znovu, případně reset.')], 500);
}
