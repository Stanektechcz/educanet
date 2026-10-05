<?php

declare(strict_types=1);

/**
 * EDUCANET v64 · behaviorální audit vrstvy „hry, ligy a férová ekonomika“.
 *   php tools/v64_games_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte ani nezapisuje ostrou storage/. Fiktivní žáci „Audit …“.
 * Sekce jsou v tools/lib/v64_audit_*.php (každá dostane $check, $tmp, $root). Konec: V64_GAMES_AUDIT_OK checks=N failed=0.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v64-games')), '/');
require $root . '/bootstrap.php';
require_once $root . '/economy_v64.php';
require_once $root . '/arena_v64_fair.php';
if (session_status() !== PHP_SESSION_ACTIVE) { $_SESSION = []; }

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_contains(STORAGE_DIR, '/Educanet systém/storage'));

foreach (glob(__DIR__ . '/lib/v64_audit_*.php') ?: [] as $section) {
    require $section;
}

exit(audit_summary($state, 'V64_GAMES'));
