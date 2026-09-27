<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v59 · tools/http_smoke.php
 *
 * Externí HTTP smoke test proti běžícímu nasazení (Apache/nginx nebo `php -S`
 * pro lokální ověření). Nikdy nezasahuje do jiné URL než --base a nikdy neposílá
 * přihlašovací údaje – jen ověřuje viditelné chování (kódy, hlavičky, cookies).
 *
 * Použití:
 *   php tools/http_smoke.php --base=<URL> [--insecure]
 *
 * http:// je povoleno jen pro 127.0.0.1/localhost; jiné hostitele musí testovat
 * přes https://. Konec: HTTP_SMOKE_OK checks=N failed=0 (exit 0), jinak
 * HTTP_SMOKE_FAIL checks=N failed=M (exit 1).
 *
 * Vestavěný server PHP (`php -S`) IGNORUJE .htaccess – 403/404 pravidla z Apache
 * se přes něj neuplatní. Test to rozpozná (--base obsahuje 127.0.0.1/localhost a
 * hlavička Server neobsahuje Apache/nginx) a takové kontroly ohlásí jako
 * "ENFORCED_BY_WEBSERVER_ONLY" místo FAILu.
 */

function hs_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    }
    return null;
}

function hs_request(string $url, array $headers = []): array
{
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 10,
            'ignore_errors' => true,
            'follow_location' => 0,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    $respHeaders = [];
    $rawLines = $http_response_header ?? [];
    $cookies = [];
    foreach ($rawLines as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
            $status = (int)$m[1];
        } elseif (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $respHeaders[strtolower(trim($k))] = trim($v);
            if (strtolower(trim($k)) === 'set-cookie' && preg_match('/^\s*([^=]+)=([^;]+)/', $v, $cm)) {
                $cookies[trim($cm[1])] = trim($cm[2]);
            }
        }
    }
    // $http_response_header je jen v rámci téhle funkce (magická proměnná vázaná
    // na scope volání file_get_contents) – volající proto dostávají syrové
    // hlavičky i cookies přímo ve výsledku, ne přes globální proměnnou.
    return ['status' => $status, 'headers' => $respHeaders, 'body' => (string)$body, 'raw_headers' => $rawLines, 'cookies' => $cookies];
}

$checks = 0;
$failed = 0;
function hs_check(bool $ok, string $label, string $detail = ''): void
{
    global $checks, $failed;
    $checks++;
    if ($ok) {
        echo "PASS $label" . ($detail !== '' ? " – $detail" : '') . "\n";
    } else {
        $failed++;
        echo "FAIL $label" . ($detail !== '' ? " – $detail" : '') . "\n";
    }
}
function hs_note(string $label, string $detail = ''): void
{
    echo "NOTE $label" . ($detail !== '' ? " – $detail" : '') . "\n";
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $base = hs_arg($argv, 'base');
    if ($base === null || trim($base) === '') {
        fwrite(STDERR, "HTTP_SMOKE_FAIL Chybí --base=<URL>\n");
        exit(1);
    }
    $base = rtrim($base, '/');
    $parts = parse_url($base);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower((string)($parts['host'] ?? ''));
    $isLocal = in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    if ($scheme !== 'https' && !$isLocal) {
        fwrite(STDERR, "HTTP_SMOKE_FAIL http:// je povolené jen pro 127.0.0.1/localhost, jinak použij https://\n");
        exit(1);
    }

    // Zjisti, jestli běžíme proti php -S (ignoruje .htaccess) nebo skutečnému webserveru.
    $probe = hs_request($base . '/');
    $serverHeader = strtolower((string)($probe['headers']['server'] ?? ''));
    $builtinServer = $isLocal && !str_contains($serverHeader, 'apache') && !str_contains($serverHeader, 'nginx');
    if ($builtinServer) {
        hs_note('Detekován vestavěný php -S server', 'kontroly vynucené jen přes .htaccess (Apache/nginx) se ohlásí jako ENFORCED_BY_WEBSERVER_ONLY');
    }

    $htaccessOnly = static function (bool $condActual, string $label, string $detail = '') use ($builtinServer): void {
        if ($builtinServer) {
            echo "ENFORCED_BY_WEBSERVER_ONLY $label (php -S neuplatňuje .htaccess)\n";
            return;
        }
        hs_check($condActual, $label, $detail);
    };

    // --- Musí být 200 -----------------------------------------------------
    $r = hs_request($base . '/');
    hs_check($r['status'] === 200, 'GET / vrací 200', 'status=' . $r['status']);

    $r = hs_request($base . '/lab-offline.html');
    hs_check($r['status'] === 200, 'GET /lab-offline.html vrací 200', 'status=' . $r['status']);

    $r = hs_request($base . '/sw.js');
    hs_check($r['status'] === 200, 'GET /sw.js vrací 200', 'status=' . $r['status']);

    $assetCandidates = ['/assets/app.css', '/assets/app.js'];
    $assetOk = false;
    $assetTried = [];
    foreach ($assetCandidates as $asset) {
        $r = hs_request($base . $asset);
        $assetTried[] = $asset . '=' . $r['status'];
        if ($r['status'] === 200) { $assetOk = true; break; }
    }
    hs_check($assetOk, 'GET jednoho assetu z /assets/ vrací 200', implode(', ', $assetTried));

    // --- Musí být 403/404 (chráněné cesty) ---------------------------------
    $protected = [
        '/bootstrap.php',
        '/INSTALL.md',
        '/CLAUDE.md',
        '/materials/README.md',
        '/V1/diagnostics.php',
        '/.claude/launch.json',
        '/storage/students.json.php',
        '/tools/preflight.php',
        '/lang/cs/core.php',
        // SEC59-01(e): V1/ (legacy aplikace s web-nastavitelným prvním heslem) musí
        // být nedostupný přes web i pro jeho vstupní skripty samotné, ne jen pro
        // interní soubory výše.
        '/V1/index.php',
        '/V1/teacher.php',
        // SEC59-01(e): storage/intake/index.php je vstupní skript uvnitř storage/ –
        // ukázka, že allowlist "^(index|...)\.php$" z kořenového .htaccess nesmí
        // přebít storage/.htaccess i v podadresáři.
        '/storage/intake/index.php',
        // SEC59-11: manifest vydání (sha256 každého souboru) nepatří na web.
        '/RELEASE_MANIFEST.json',
        // SEC59-22: rozvrh není veřejný (na rozdíl od SCHOOL_YEAR_*.md níže).
        '/materials/school_year/WEEKLY_SCHEDULE.md',
    ];
    foreach ($protected as $path) {
        $r = hs_request($base . $path);
        $blocked = in_array($r['status'], [403, 404], true);
        $htaccessOnly($blocked, "GET $path je zablokované (403/404)", 'status=' . $r['status']);
    }

    // SEC59-22: SCHOOL_YEAR_*.md je záměrná výjimka a musí zůstat čitelný – jen pokud
    // v tomto nasazení existuje (ne každá kopie musí mít stejný ročník ve jménu souboru).
    $schoolYearPath = '/materials/school_year/SCHOOL_YEAR_2026_2027.md';
    $r = hs_request($base . $schoolYearPath);
    if ($r['status'] === 404 && !$builtinServer) {
        hs_note("GET $schoolYearPath vrací 404 – soubor v tomto nasazení asi neexistuje (jiný školní rok?), kontrola přeskočena");
    } else {
        $htaccessOnly($r['status'] === 200, "GET $schoolYearPath vrací 200 (veřejná výjimka funguje)", 'status=' . $r['status']);
    }

    // --- Bezpečnostní hlavičky ----------------------------------------------
    $r = hs_request($base . '/');
    $h = $r['headers'];
    hs_check(($h['x-content-type-options'] ?? '') === 'nosniff', 'Hlavička X-Content-Type-Options: nosniff');
    hs_check(isset($h['x-frame-options']) || str_contains((string)($h['content-security-policy'] ?? ''), 'frame-ancestors'), 'X-Frame-Options nebo CSP frame-ancestors je nastavena');
    hs_check(!isset($h['x-powered-by']), 'Hlavička X-Powered-By chybí (expose_php vypnuté)');
    if ($scheme === 'https') {
        hs_check(isset($h['strict-transport-security']), 'Hlavička Strict-Transport-Security je nastavena (HTTPS)');
    } else {
        hs_note('HSTS se přes http:// neověřuje (jen přes https)');
    }

    // --- Cookie flags --------------------------------------------------------
    $rawCookies = array_values(array_filter($r['raw_headers'], static fn($line) => stripos($line, 'Set-Cookie:') === 0));
    if ($rawCookies === []) {
        // Zkus vynutit session cookie přes dotaz, který session pravděpodobně založí.
        $r2 = hs_request($base . '/?class=class_3a');
        $rawCookies = array_values(array_filter($r2['raw_headers'], static fn($line) => stripos($line, 'Set-Cookie:') === 0));
    }
    if ($rawCookies === []) {
        hs_note('Žádná Set-Cookie hlavička nebyla zachycena (session se možná zakládá jen po skutečné akci)');
    } else {
        foreach ($rawCookies as $cookieLine) {
            $lower = strtolower($cookieLine);
            hs_check(str_contains($lower, 'httponly'), 'Session cookie má HttpOnly', $cookieLine);
            hs_check(str_contains($lower, 'samesite'), 'Session cookie má SameSite', $cookieLine);
            if ($scheme === 'https') {
                hs_check(str_contains($lower, 'secure'), 'Session cookie má Secure (HTTPS)', $cookieLine);
            }
        }
    }

    // --- ?class=&student= nesmí přihlásit (dev bypass musí být vypnutý) -----
    // Úspěšné přihlášení může vrátit 200 s obsahem žákovského dashboardu, NEBO
    // přesměrování (302) na něj – proto při přesměrování ověříme i cílovou
    // stránku (s cookies z prvního požadavku), místo abychom se spokojili
    // s tím, že první odpověď sama o sobě nevypadá přihlášeně.
    $studentName = 'SmokeTest Student';
    $r = hs_request($base . '/?class=class_3a&student=' . rawurlencode($studentName));
    $finalBody = $r['body'];
    $finalStatus = $r['status'];
    if (in_array($r['status'], [301, 302, 303, 307, 308], true) && isset($r['headers']['location'])) {
        $location = $r['headers']['location'];
        $target = str_starts_with($location, 'http') ? $location : $base . '/' . ltrim($location, '/');
        $cookieHeader = [];
        foreach ($r['cookies'] as $name => $value) {
            $cookieHeader[] = $name . '=' . $value;
        }
        $r2 = hs_request($target, $cookieHeader ? ['Cookie: ' . implode('; ', $cookieHeader)] : []);
        $finalBody = $r2['body'];
        $finalStatus = $r2['status'];
    }
    $loggedIn = $finalStatus === 200 && (
        str_contains($finalBody, $studentName)
        || str_contains(strtolower($finalBody), 'odhl') // "Odhlásit se" apod. – žák je přihlášen
    );
    hs_check(!$loggedIn, '?class=&student= nepřihlašuje žáka (dev bypass je vypnutý)', 'status=' . $r['status'] . ($finalStatus !== $r['status'] ? (' -> ' . $finalStatus) : ''));

    echo str_repeat('-', 60) . "\n";
    $status = $failed > 0 ? 'FAIL' : 'OK';
    echo "HTTP_SMOKE_$status checks=$checks failed=$failed\n";
    exit($failed > 0 ? 1 : 0);
}
