<?php

declare(strict_types=1);

/**
 * EDUCANET v63 · audit „Přihlášení a patička“ (jednoobrazovková přihlašovací stránka, segmentovaný přepínač jazyka, patička).
 *   php tools/v63_login_audit.php
 * Běží v dočasném úložišti (edu_audit_temp_storage), bez dev bypassu (skutečná přihlašovací stránka).
 * Konec: V63_LOGIN_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$storageDir = edu_audit_temp_storage('v63-login');
$root = dirname(__DIR__);
$state = audit_counter();
$check = audit_checker($state);

$css = (string)@file_get_contents($root . '/assets/login-v63.css');
$check('login-v63.css existuje a má ≤ 10 kB (' . strlen($css) . ' B)', $css !== '' && strlen($css) <= 10240);
$check('CSS nenačítá nic z internetu (@import, url(http), CDN)', !preg_match('~@import|url\(\s*[\'"]?(https?:)?//~i', $css));
$check('CSS respektuje prefers-reduced-motion a má viditelný focus (outline na :focus-visible)', str_contains($css, 'prefers-reduced-motion: reduce') && preg_match('/:focus-visible[^{]*\{[^}]*outline:\s*3px/', $css) === 1);
$check('CSS: cíle přepínače jazyka mají min-height ≥ 44 px', preg_match('/\.login63-lang button\s*\{[^}]*min-height:\s*44px/', $css) === 1);

$h = Harness::start(['EDUCANET_STORAGE_DIR' => $storageDir, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $home = $h->request('GET', '/?view=home');
    $body = (string)$home['body'];
    $check('přihlašovací stránka vrací 200 bez PHP chyb', $home['status'] === 200 && !preg_match('/Warning:|Notice:|Fatal error|Deprecated:/', $body));
    $check('login-v63.css je připojen a vrací 200', str_contains($body, 'assets/login-v63.css') && $h->request('GET', '/assets/login-v63.css')['status'] === 200);
    $check('běžná horní lišta a stará patička se na přihlášení nevykreslují (jen login63)', !str_contains($body, 'class="topbar') && !str_contains($body, 'class="footer"') && str_contains($body, 'login63-footer'));
    $check('bez dev bypassu není na stránce vývojový vstup', !str_contains($body, 'dev-entry'));
    $check('přepínač jazyka: 3 tlačítka v POST formuláři s CSRF a edu_set_lang', preg_match_all('/<button type="submit" name="lang" value="(cs|en|uk)"/', $body) === 3 && str_contains($body, 'name="action" value="edu_set_lang"') && $h->csrfToken($body) !== null);
    $check('aktivní jazyk (cs) má aria-current, ostatní ne', preg_match_all('/aria-current="true"/', $body) === 1 && preg_match('/value="cs"[^>]*aria-current="true"/', $body) === 1);
    $check('každé tlačítko jazyka má kód i název a kód je pro čtečky skrytý (aria-hidden)', str_contains($body, '<b aria-hidden="true">CS</b><span>Čeština</span>') && str_contains($body, '<span>Українська</span>'));
    $check('patička: odkaz na stanektech.cz s rel noopener, srdce aria-hidden + text pro čtečky', preg_match('~<a href="https://stanektech\.cz" target="_blank" rel="noopener noreferrer">stanektech\.cz</a>~', $body) === 1 && str_contains($body, 'login63-heart" aria-hidden="true"') && str_contains($body, '>láskou</span>'));
    $check('patička: školní rok, verze a odkaz na soukromí', preg_match('/Školní rok \d{4}\/\d{4} · EDUCANET v63/u', $body) === 1 && str_contains($body, 'href="?view=privacy"'));
    $check('zachováno: formulář local_login s autocomplete username/current-password a CSRF', str_contains($body, 'name="action" value="local_login"') && str_contains($body, 'autocomplete="username"') && str_contains($body, 'autocomplete="current-password"'));
    $check('zachováno: kód hodiny, aktivační kód, zapomenuté heslo, odkaz na soukromí v kartě', str_contains($body, 'href="?view=join"') && str_contains($body, 'href="?view=activate"') && str_contains($body, 'local_forgot_password') && str_contains($body, 'pravidly zpracování údajů'));
    $check('stránka má právě jeden h1 a skip odkaz na #main-content', substr_count($body, '<h1') === 1 && str_contains($body, 'href="#main-content"'));

    $privacy = $h->request('GET', '/?view=privacy');
    $check('ostatní veřejné stránky (soukromí) nejsou dotčeny: bez login-v63.css, s běžnou patičkou', $privacy['status'] === 200 && !str_contains((string)$privacy['body'], 'login-v63.css') && str_contains((string)$privacy['body'], 'class="footer"'));

    $csrf = (string)$h->csrfToken($body);
    $noCsrf = $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'en', 'return' => '?view=home']);
    $check('POST edu_set_lang bez CSRF je odmítnut (419)', $noCsrf['status'] === 419);
    $bad = $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'xx', 'return' => '?view=home', 'csrf' => $csrf]);
    $check('neplatný jazyk se nepřijme (zůstává čeština)', str_contains((string)$bad['body'], '<html lang="cs"'));
    foreach (['en' => ['en', 'Made with', 'Sign in'], 'uk' => ['uk', 'Створено з', 'Вхід']] as $code => [$htmlLang, $credit, $word]) {
        $r = $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => $code, 'return' => '?view=home', 'csrf' => $csrf]);
        $b = (string)$r['body'];
        $check("přepnutí na $code: html lang, aria-current, přeložená patička a UI", str_contains($b, '<html lang="' . $htmlLang . '"') && preg_match('/value="' . $code . '"[^>]*aria-current="true"/', $b) === 1 && str_contains($b, $credit) && str_contains($b, $word) && !str_contains($b, '{heart}') && !str_contains($b, '{a}'));
    }
    $back = $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'cs', 'return' => '?view=home', 'csrf' => $csrf]);
    $check('návrat na češtinu funguje', str_contains((string)$back['body'], '<html lang="cs"') && str_contains((string)$back['body'], 'S <span class="login63-heart"'));

    $fail = $h->request('POST', '/', ['action' => 'local_login', 'email' => 'nikdo.neexistuje@' . 'educanet.cz', 'password' => 'SpatneHeslo123', 'csrf' => $csrf]);
    $fb = (string)$fail['body'];
    $check('chybné přihlášení: hláška v role="alert" a pole ji odkazují přes aria-describedby', str_contains($fb, 'id="login63-flash" role="alert"') && substr_count($fb, 'aria-describedby="login63-flash"') >= 2);
    $noCsrfLogin = $h->request('POST', '/', ['action' => 'local_login', 'email' => 'a@b.cz', 'password' => 'x']);
    $check('POST local_login bez CSRF je odmítnut (419)', $noCsrfLogin['status'] === 419);
} finally {
    $h->stop();
}

exit(audit_summary($state, 'V63_LOGIN'));
