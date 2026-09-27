<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

// v54 audit: firemní barvy EDUCANET, přihlašovací stránka, pravidla hesel a klasický kalendář.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
// v58: audit běží v dočasném úložišti (nikdy ne v ostrých datech žáků) a hodiny s kódem ověřuje
// na pevném vyučovacím dni s kontrolovaným vstupem – výsledek nezávisí na tom, jaký je dnes den.
require_once __DIR__ . '/lib/audit_storage.php';
$storageDir = edu_audit_temp_storage('v54');

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require $root . '/runtime_content.php';
require_once $root . '/tutorial_v52.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/points_v53.php';
require_once $root . '/session_v53.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($root . '/' . $rel);
$views = $read('tutorial_v52_views.php');
$brand = $read('assets/brand-v54.css');

// --- Firemní barvy: skutečné rozpuštění vlastních CSS proměnných, ne hledání podřetězce ---
$rootVars = audit_css_vars($brand);
foreach (['edu-yellow' => '#f3b21f', 'edu-orange' => '#ec6b10', 'edu-teal' => '#00a8b9'] as $token => $hex) {
    $resolved = strtolower(audit_css_resolve($rootVars, $rootVars[$token] ?? ''));
    $check('token --' . $token . ' se rozpouští na ' . $hex, $resolved === $hex);
}
preg_match('/body\.accent-graphics\s*\{([^}]*)\}/', $brand, $mg);
preg_match('/body\.accent-network\s*\{([^}]*)\}/', $brand, $mn);
$graphicsVars = array_merge($rootVars, audit_css_vars((string)($mg[1] ?? '')));
$networkVars = array_merge($rootVars, audit_css_vars((string)($mn[1] ?? '')));
$graphicsAccent = strtolower(audit_css_resolve($graphicsVars, $graphicsVars['u-accent'] ?? ''));
$networkAccent = strtolower(audit_css_resolve($networkVars, $networkVars['u-accent'] ?? ''));
$check('grafika a webdesign má tmavě oranžový accent (#c4550a)', $graphicsAccent === '#c4550a');
$check('OS a sítě má tmavě tyrkysový accent (#007a87)', $networkAccent === '#007a87');
$contrastGraphics = audit_contrast_ratio($graphicsAccent, '#ffffff');
$contrastNetwork = audit_contrast_ratio($networkAccent, '#ffffff');
$check('accent grafiky má kontrast na bílé aspoň 4.5:1 (' . ($contrastGraphics ?? 0) . ')', $contrastGraphics !== null && $contrastGraphics >= 4.5);
$check('accent sítí má kontrast na bílé aspoň 4.5:1 (' . ($contrastNetwork ?? 0) . ')', $contrastNetwork !== null && $contrastNetwork >= 4.5);

// --- Hesla (v58 SEC-16: min. 10 znaků, písmeno + číslice, bez běžných hesel a sdíleného demo001) ---
$check('heslo od 10 znaků projde', local_password_validate('ZelenyKopec42') === null);
$check('heslo kratší než 10 znaků neprojde', local_password_validate('abc12345') !== null);
$check('heslo musí mít písmeno i číslici', local_password_validate('abcdefghijk') !== null && local_password_validate('12345678901') !== null);
$check('sdílené heslo demo001 je zakázané', local_password_validate(ACC53_DEFAULT_PASSWORD) !== null);
$index = edu_app_source();
$check('nikde nezůstal starý požadavek na 6 znaků', !preg_match('/alespoň\s*6\s*znak/iu', $index . $read('bootstrap.php') . $read('intake_v51_views.php') . $read('session_v53_views.php')), false);
$minlen = [];
foreach (['index.php', 'session_v53_views.php', 'intake_v51_views.php'] as $f) {
    if (preg_match_all('/<input[^>]*type="password"[^>]*>/i', $f === 'index.php' ? $index : $read($f), $inputs)) {
        foreach ($inputs[0] as $tag) {
            // Jen pole pro nové heslo; přihlašovací pole (current-password) minlength mít nemá.
            if (!str_contains($tag, 'new-password')) continue;
            $minlen[] = preg_match('/minlength="(\d+)"/', $tag, $mm) ? (int)$mm[1] : 0;
        }
    }
}
$check('nová hesla vyžadují 10 znaků napříč aplikací (' . implode(',', array_unique($minlen)) . ')', $minlen !== [] && !array_filter($minlen, static fn(int $v): bool => $v !== 10), false);

// --- HTTP: přihlašovací stránka, registrace, CSRF, assety, kalendář ---
audit_prewarm_accounts($modules);
$schoolYear = require $root . '/school_year.php';
$today = EDU_AUDIT_TEACHING_DATE;
edu_audit_open_sessions($modules, $schoolYear, $today);
$harness = Harness::start([
    'EDUCANET_STORAGE_DIR' => $storageDir,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
]);
try {
    foreach (['assets/brand-v54.css', 'assets/auth-v54.js'] as $asset) {
        $resp = $harness->request('GET', '/' . $asset);
        $check('asset ' . $asset . ' je dostupný (HTTP 200)', $resp['status'] === 200);
    }
    $sw = $harness->request('GET', '/sw.js');
    $check('service worker předcachuje v54', $sw['status'] === 200 && str_contains((string)$sw['body'], 'brand-v54.css') && str_contains((string)$sw['body'], 'auth-v54.js'));

    $home = $harness->request('GET', '/?view=home');
    $homeBody = (string)$home['body'];
    $check('přihlašovací stránka (?view=home) se vykreslí bez chyb', audit_response_clean($home));
    $check('brand-v54.css je připojen na přihlašovací stránce', str_contains($homeBody, 'brand-v54.css'));
    $check('auth-v54.js je připojen na přihlašovací stránce', str_contains($homeBody, 'auth-v54.js'));
    $check('přihlášení a registrace jsou v záložkách', str_contains($homeBody, 'auth54-tabs') && str_contains($homeBody, 'data-auth54-tab'));
    $check('hero s přínosy aplikace', str_contains($homeBody, 'auth54-hero') && str_contains($homeBody, 'auth54-points'));
    $check('vstup kódem hodiny je na přihlašovací stránce', str_contains($homeBody, 'auth54-code') && str_contains($homeBody, 'href="?view=join"'));
    $check('aktivační kód je na přihlašovací stránce', str_contains($homeBody, 'href="?view=activate"'));
    $check('„Nemůžu se přihlásit“ je sbalená nápověda', str_contains($homeBody, 'auth54-help') && str_contains($homeBody, 'Nemůžu se přihlásit'));

    $pwFieldsOk = false;
    if (preg_match_all('/<input[^>]*type="password"[^>]*>/i', $homeBody, $pwInputs)) {
        $newPw = array_values(array_filter($pwInputs[0], static fn(string $t): bool => str_contains($t, 'new-password')));
        $curPw = array_values(array_filter($pwInputs[0], static fn(string $t): bool => str_contains($t, 'current-password')));
        $newOk = count($newPw) === 2 && !array_filter($newPw, static fn(string $t): bool => !str_contains($t, 'minlength="10"'));
        $curOk = count($curPw) === 1 && !array_filter($curPw, static fn(string $t): bool => str_contains($t, 'minlength'));
        $pwFieldsOk = $newOk && $curOk;
    }
    $check('vykreslená stránka: nové heslo vyžaduje 10 znaků, přihlašovací pole minlength nemá', $pwFieldsOk);

    $csrfHome = (string)$harness->csrfToken($homeBody);
    $weakEmail = 'audit.v54.test@' . google_workspace_domain();
    $weakReg = $harness->request('POST', '/', [
        'action' => 'local_register', 'email' => $weakEmail, 'name' => 'Audit Test 54',
        'password' => 'kratke1', 'password_confirm' => 'kratke1', 'csrf' => $csrfHome,
    ]);
    $check('registrace s krátkým heslem se odmítne s hláškou o min. délce', str_contains((string)$weakReg['body'], 'Heslo musí mít alespoň ' . LOCAL_PASSWORD_MIN_LENGTH . ' znaků.'));
    $noCsrfReg = $harness->request('POST', '/', ['action' => 'local_register', 'email' => $weakEmail, 'name' => 'X', 'password' => 'irelevantni1', 'password_confirm' => 'irelevantni1']);
    $check('POST local_register bez CSRF je odmítnut (419)', $noCsrfReg['status'] === 419);

    $studentLogin = audit_login_student($harness, 'class_3a', 'Audit Brand 54');
    $check('žák se přihlásí a přehled se vykreslí bez chyb', audit_response_clean($studentLogin['response']));
    $calendar = $harness->request('GET', '/?view=calendar');
    $calBody = (string)$calendar['body'];
    $check('kalendář (?view=calendar) se vykreslí bez chyb', audit_response_clean($calendar));
    $check('kalendář je mřížka měsíce', str_contains($calBody, 'cal54-grid') && str_contains($calBody, 'cal54-cell'));
    $dowHeaders = ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'];
    $hasAllDow = true;
    foreach ($dowHeaders as $dow) { if (!str_contains($calBody, '>' . $dow . '<')) { $hasAllDow = false; break; } }
    $check('kalendář má hlavičku Po–Ne', $hasAllDow);
    $check('legenda kalendáře', str_contains($calBody, 'cal54-legend') && str_contains($calBody, 'cal54-dot planned') && str_contains($calBody, 'cal54-dot off'));
    $check('kalendář je pro konkrétní třídu', str_contains($calBody, 'Kalendář třídy') && str_contains($calBody, (string)$modules['class_3a']['name']));
    $check('klik na den otevře detail hodiny', (bool)preg_match('/href="#den-\d{4}-\d{2}-\d{2}"/', $calBody));
} finally {
    $harness->stop();
}

// --- Zakázaná věta (regrese napříč celým zdrojovým kódem, ne jen jednou stránkou) ---
$forbidden = 'Startovní test a hlavní praktický blok';
$hits = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
    $path = str_replace('\\', '/', (string)$file);
    if (str_contains($path, '/V1/') || str_contains($path, '/storage/') || str_contains($path, '/backup')) continue;
    if (str_contains($path, 'v54_brand_login_audit.php')) continue;
    if (!preg_match('/\.(php|js|css|md|html)$/', $path)) continue;
    if (str_contains((string)@file_get_contents($path), $forbidden)) $hits[] = $path;
}
$check('zakázaná věta o známkování se nikde neuvádí', !$hits, false);

// --- Kalendář: vlastnosti nezávislé na dnešním datu (styl, ne aktuální den) ---
$check('pole mají jednotné odsazení a styl', str_contains($brand, '.auth54-field input') && str_contains($brand, '.auth54-form'), false);
$check('přihlašovací stránka je responzivní', str_contains($brand, '@media (max-width: 900px)'), false);
$check('prázdné dny na začátku měsíce', str_contains($views, "date('N', \$first) - 1"), false);
$check('detail dne nabízí přípravu i doplnění absence', str_contains($views, 'tut52_tutorial_url') && str_contains($views, 'adaptive_absence_mark'), false);
$check('stavy dnů: hodina, proběhlo, bez výuky', str_contains($brand, '.cal54-cell.planned') && str_contains($brand, '.cal54-cell.past') && str_contains($brand, '.cal54-cell.off'), false);
$check('dnešek je zvýrazněn (styl bez závislosti na datu auditu)', str_contains($views, 'is-today') && str_contains($brand, '.cal54-cell.is-today'), false);
$check('kalendář je použitelný na mobilu', str_contains($brand, '@media (max-width: 760px)'), false);

// --- Vyučovací den (pevné datum, hodiny otevřené jako v učitelské záložce) ---
$ready = 0;
foreach ($modules as $classId => $module) {
    $row = sess53_for_class_date((string)$classId, $today);
    if (!is_array($row)) continue;
    $ready++;
    $kind = (string)$row['kind'];
    $ok = trim((string)$row['code']) !== '' && ($kind === 'intake' || count((array)$row['tasks']) >= 3);
    $check((string)$module['name'] . ': hodina je připravená (' . $kind . ')', $ok);
}
$check('na vyučovací den ' . $today . ' jsou připravené hodiny (' . $ready . ')', $ready > 0);

exit(audit_summary($state, 'V54_BRAND_LOGIN'));
