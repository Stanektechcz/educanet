<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/v58_smoke_audit.php (F1)
 *
 * Chování aplikace přes skutečné HTTP požadavky na vestavěný dev server (dev bypass,
 * dočasná kopie storage/). Testuje URL → výsledek, ne strukturu souborů, takže přežije
 * drobné změny šablon. Pokud probíhající refaktoring index.php jinde způsobí neočekávanou
 * 500/parse chybu, audit to nahlásí jako FAIL s poznámkou a pokračuje dál – nevzdává se.
 *
 * Použití: php tools/v58_smoke_audit.php [--tmp=<adresář>]
 * --tmp   Základní adresář pro dočasnou kopii storage (výchozí: systémový temp aktuálního
 *         uživatele, nebo env EDUCANET_AUDIT_TMP_DIR).
 *
 * POZOR – citlivá data: dočasná kopie storage/ obsahuje stejná osobní data žáků jako
 * ostrá storage/. Vytváří se jen pod vlastním podadresářem v temp aktuálního uživatele
 * (nikdy sdílený adresář jiného účtu), s právy 0700 tam, kde to OS dovolí, a vždy se
 * smaže po doběhnutí – i při chybě/výjimce (finally + register_shutdown_function).
 *
 * PASS …/FAIL … po řádcích, konec V58_SMOKE_AUDIT_OK checks=N failed=0 (jinak FAILED, exit 1).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/backup_storage.php';
require_once __DIR__ . '/lib/http_harness.php';

$checks = 0;
$failed = 0;
$notes = [];

function smoke_check(bool $cond, string $label): void
{
    global $checks, $failed;
    $checks++;
    if ($cond) {
        echo "PASS $label\n";
    } else {
        $failed++;
        echo "FAIL $label\n";
    }
}

function smoke_note(string $text): void
{
    global $notes;
    $notes[] = $text;
    echo "NOTE $text\n";
}

function smoke_body_is_clean(string $body): bool
{
    return !preg_match('/\b(Fatal error|Parse error|Warning:|Notice:|Deprecated:)\b/', $body);
}

// ---------------------------------------------------------------------------
// Izolovaná dočasná storage (citlivá data – viz upozornění výše)
// ---------------------------------------------------------------------------
function smoke_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) {
            return substr($a, strlen($name) + 3);
        }
    }
    return $default;
}

$tmpBaseRoot = rtrim((string)(smoke_arg($GLOBALS['argv'] ?? [], 'tmp') ?: (getenv('EDUCANET_AUDIT_TMP_DIR') ?: sys_get_temp_dir())), '/\\');
$tmpBaseDir = $tmpBaseRoot . '/educanet-audit-tmp';
if (!is_dir($tmpBaseDir)) {
    mkdir($tmpBaseDir, 0700, true);
}
@chmod($tmpBaseDir, 0700);
$tmpStorage = $tmpBaseDir . '/smoke_' . bin2hex(random_bytes(6));
mkdir($tmpStorage, 0700, true);
@chmod($tmpStorage, 0700);
foreach (bkp_rcopy_list(STORAGE_DIR) as $rel) {
    $to = $tmpStorage . '/' . $rel;
    $dir = dirname($to);
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    copy(STORAGE_DIR . '/' . $rel, $to);
}
$cleanupStorage = static function () use ($tmpStorage): void {
    if (!is_dir($tmpStorage)) {
        return;
    }
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmpStorage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($items as $item) {
        $item->isDir() ? @rmdir((string)$item) : @unlink((string)$item);
    }
    @rmdir($tmpStorage);
};
register_shutdown_function($cleanupStorage);

$teacherKey = 'ucitel-test-58-smoke';
$env = [
    'EDUCANET_STORAGE_DIR' => $tmpStorage,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey,
];

$exitCode = 0;
try {
    $h = Harness::start($env);
    try {
        // -----------------------------------------------------------------
        // Žák (dev bypass)
        // -----------------------------------------------------------------
        $studentPaths = [
            '/?class=class_3a&student=' . rawurlencode('Test Zak'),
            '/?class=class_3a&student=' . rawurlencode('Test Zak') . '&view=lab',
            '/?class=class_3a&student=' . rawurlencode('Test Zak') . '&view=prikazy',
            '/?class=class_3a&student=' . rawurlencode('Test Zak') . '&view=prikazy&c=ls',
            '/?class=class_3a&student=' . rawurlencode('Test Zak') . '&view=lab&uroven=start-1',
        ];
        $lastBody = '';
        foreach ($studentPaths as $path) {
            $r = $h->request('GET', $path);
            smoke_check(in_array($r['status'], [200, 302], true), "GET $path -> HTTP {$r['status']}");
            smoke_check(smoke_body_is_clean($r['body']), "GET $path body bez chybových hlášek");
            if ($r['status'] === 200) {
                $lastBody = $r['body'];
            }
            $secHeaders = $r['headers'];
            smoke_check(($secHeaders['X-Content-Type-Options'] ?? '') === 'nosniff', "GET $path X-Content-Type-Options: nosniff");
            smoke_check(($secHeaders['X-Frame-Options'] ?? '') === 'DENY', "GET $path X-Frame-Options: DENY");
            foreach ($r['redirects'] as $redir) {
                $loc = $redir['location'];
                $hasQuery = str_contains($loc, '?');
                $looksLikeName = $hasQuery && preg_match('/student=[^&]{3,}/', $loc) === 1;
                smoke_check(!$looksLikeName || str_contains($loc, rawurlencode('Test Zak')) === false, "Redirect $loc neobsahuje osobní údaje navíc");
            }
        }
        $studentCsrf = $h->csrfToken($lastBody);
        smoke_check($studentCsrf !== null, 'Žákovská stránka obsahuje CSRF token');

        // -----------------------------------------------------------------
        // Lab API (lab_v57_api.php)
        // -----------------------------------------------------------------
        // Nejdřív se přihlásit jako žák, aby session měla class/student navázané.
        $h->request('GET', '/?class=class_3a&student=' . rawurlencode('Test Zak'));
        $home = $h->request('GET', '/?class=class_3a&student=' . rawurlencode('Test Zak'));
        $csrf = $h->csrfToken($home['body']);
        if ($csrf === null) {
            smoke_note('Nepodařilo se získat CSRF token pro lab API test – přeskočeno.');
        } else {
            $withCsrf = $h->request('POST', '/lab_v57_api.php', ['op' => 'state', 'csrf' => $csrf]);
            smoke_check($withCsrf['status'] === 200, 'POST lab_v57_api.php op=state s CSRF -> HTTP 200');
            $decoded = json_decode($withCsrf['body'], true);
            smoke_check(is_array($decoded) && !empty($decoded['ok']), 'POST lab_v57_api.php op=state vrací {"ok":true,...}');

            $withoutCsrf = $h->request('POST', '/lab_v57_api.php', ['op' => 'state']);
            smoke_check(in_array($withoutCsrf['status'], [401, 403, 419], true), 'POST lab_v57_api.php bez CSRF je odmítnuto (4xx)');

            $getReq = $h->request('GET', '/lab_v57_api.php', ['op' => 'state']);
            smoke_check($getReq['status'] === 405, 'GET lab_v57_api.php -> HTTP 405');

            $runReq = $h->request('POST', '/lab_v57_api.php', ['op' => 'run', 'line' => 'pwd', 'csrf' => $csrf]);
            smoke_check(in_array($runReq['status'], [200], true), 'POST lab_v57_api.php op=run line=pwd -> HTTP 200');
            $runDecoded = json_decode($runReq['body'], true);
            smoke_check(is_array($runDecoded), 'POST lab_v57_api.php op=run vrací platný JSON');
        }

        // -----------------------------------------------------------------
        // Učitel
        // -----------------------------------------------------------------
        $teacherHome = $h->request('GET', '/teacher.php');
        smoke_check(in_array($teacherHome['status'], [200], true), 'GET teacher.php (nepřihlášen) -> HTTP 200 (přihlašovací formulář)');
        $cacheControl = strtolower((string)($teacherHome['headers']['Cache-Control'] ?? ''));
        smoke_check(str_contains($cacheControl, 'no-store') || str_contains($cacheControl, 'no-cache') || $cacheControl === '', 'GET teacher.php přihlašovací stránka nemá agresivní cache (Cache-Control: ' . ($cacheControl ?: 'chybí') . ')');
        $teacherCsrf = $h->csrfToken($teacherHome['body']);
        smoke_check($teacherCsrf !== null, 'teacher.php login formulář obsahuje CSRF token');

        if ($teacherCsrf !== null) {
            $login = $h->request('POST', '/teacher.php', ['csrf' => $teacherCsrf, 'action' => 'teacher_login', 'teacher_key' => $teacherKey, 'teacher_name' => 'Audit Ucitel']);
            smoke_check(in_array($login['status'], [200, 302], true), 'POST teacher_login se správným klíčem -> 200/302');

            $arena = $h->request('GET', '/teacher.php?tab=arena');
            smoke_check($arena['status'] === 200, 'GET teacher.php?tab=arena (přihlášen) -> HTTP 200');
            smoke_check(smoke_body_is_clean($arena['body']), 'GET teacher.php?tab=arena body bez chybových hlášek');
        } else {
            smoke_note('Přeskočeno přihlášení učitele – chybí CSRF token na přihlašovací stránce.');
        }
    } finally {
        $h->stop();
    }
} catch (Throwable $e) {
    smoke_check(false, 'Smoke audit selhal na výjimce: ' . $e->getMessage());
    smoke_note('Pokud je příčinou rozpracovaný refaktoring index.php jiným agentem, spusť audit znovu později.');
}

if ($failed === 0) {
    echo "V58_SMOKE_AUDIT_OK checks={$checks} failed=0\n";
    exit(0);
}
echo "V58_SMOKE_AUDIT_FAILED checks={$checks} failed={$failed}\n";
exit(1);
