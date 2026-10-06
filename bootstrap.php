<?php

declare(strict_types=1);

// Rozvrh, bloky hodin i časy v Labu/Aréně jsou v místním čase školy – nespoléhat na php.ini (lokálně často UTC).
if (!@date_default_timezone_set((string)(getenv('EDUCANET_TIMEZONE') ?: 'Europe/Prague'))) date_default_timezone_set('Europe/Prague');

/**
 * v59 · Vývojový vstup (?class=&student=, adresy z hlavičky Host, volný vstup do třídy) jen lokálně:
 * CLI (audity) nebo vestavěný server PHP z loopbacku. Na produkčním serveru (FPM/Apache) nejde zapnout ani omylem.
 */
function educanet_dev_bypass_enabled(): bool
{
    if (getenv('EDUCANET_DEV_BYPASS') !== '1') return false;
    if (PHP_SAPI === 'cli') return true;
    if (PHP_SAPI !== 'cli-server') return false;
    return in_array((string)($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);
}

/** v59 · Požadavek přišel přes HTTPS (přímo, nebo přes reverzní proxy s X-Forwarded-Proto). */
function educanet_is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

// v58 (F2): jádro úložiště – storage_read / storage_update / storage_update_many / storage_append / storage_scan.
require_once __DIR__ . '/storage_v58.php';
// v58 OPS-02: vícejazyčnost UI (t(), katalogy lang/<jazyk>/<doména>.php, výchozí čeština).
require_once __DIR__ . '/i18n_v58.php';
// v59 OPS-02: převod UI ve stylu gettext – tr()/trn()/trm(), katalogy lang/<en|uk>/ui/<doména>.php (msgid = čeština).
require_once __DIR__ . '/i18n_v59.php';

// v59 · AUTHZ58-07: učitelské účty (úložiště, přihlášení, relace) a rozsah tříd/předmětů (guardy teacher.php).
require_once __DIR__ . '/teacher_accounts_v59.php';
require_once __DIR__ . '/teacher_scope_v59.php';

// Bezpečná session cookie + základní bezpečnostní hlavičky (jen pro webové požadavky).
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $educanetHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $educanetCookie = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => (int)($educanetCookie['lifetime'] ?? 0),
        'path' => (string)(($educanetCookie['path'] ?? '') !== '' ? $educanetCookie['path'] : '/'),
        'domain' => (string)($educanetCookie['domain'] ?? ''),
        'secure' => $educanetHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($educanetHttps, $educanetCookie);
}
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: DENY');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    // Úplná CSP (script-src/style-src) by rozbila inline skripty, styly a Google Sign-In;
    // posíláme jen direktivy, které inline obsah ani externí zdroje neomezují.
    header("Content-Security-Policy: base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'");
    // v59: HSTS jen přes HTTPS a jen po vědomém zapnutí (EDUCANET_HSTS=1) – po ověření domény školou.
    if (educanet_is_https() && getenv('EDUCANET_HSTS') === '1') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

session_start();

// Secrets must live outside the public application root. The loader supports
// an explicit EDUCAnet path and safe defaults for ISPConfig and aaPanel.
function educanet_recommended_secret_path(): string
{
    $configured = getenv('EDUCANET_SECRETS_FILE');
    if (is_string($configured) && trim($configured) !== '') {
        return trim($configured);
    }

    $appRoot = realpath(__DIR__) ?: __DIR__;
    $cursor = $appRoot;
    for ($i = 0; $i < 10; $i++) {
        if (basename($cursor) === 'web' && preg_match('/^web\d+$/', basename(dirname($cursor)))) {
            // ISPConfig: /var/www/clients/clientX/webY/private/educanet.secrets.php
            return dirname($cursor) . '/private/educanet.secrets.php';
        }
        $parent = dirname($cursor);
        if ($parent === $cursor) break;
        $cursor = $parent;
    }

    if (str_starts_with($appRoot, '/www/wwwroot/')) {
        // aaPanel-safe default outside /www/wwwroot.
        return '/www/server/educanet/educanet.secrets.php';
    }

    // Portable fallback. Administrators should still verify that this path is
    // outside their web server's public document root.
    return dirname($appRoot) . '/educanet.secrets.php';
}

$educanetSecrets = [];
$educanetSecretCandidates = [educanet_recommended_secret_path()];
// Never probe hosting-specific secret paths outside the active hosting layout.
// On ISPConfig an aaPanel path would violate open_basedir and leak a warning.
$educanetAppRoot = realpath(__DIR__) ?: __DIR__;
if (str_starts_with($educanetAppRoot, '/www/wwwroot/')) {
    $educanetSecretCandidates[] = '/www/server/educanet/educanet.secrets.php';
}
$educanetSecretsPath = '';
foreach (array_unique($educanetSecretCandidates) as $candidate) {
    if (!is_string($candidate) || $candidate === '' || !is_file($candidate)) continue;
    $educanetSecretsPath = $candidate;
    $loadedSecrets = require $candidate;
    if (is_array($loadedSecrets)) $educanetSecrets = $loadedSecrets;
    break;
}
$GLOBALS['educanet_secrets'] = $educanetSecrets;
$GLOBALS['educanet_secrets_path'] = $educanetSecretsPath;

function educanet_secret(string $key, string $default = ''): string
{
    $envMap = [
        'teacher_export_key' => 'EDUCANET_TEACHER_EXPORT_KEY',
        'teacher_name' => 'EDUCANET_TEACHER_NAME',
        'teacher_team_id' => 'EDUCANET_TEACHER_TEAM_ID',
        'teacher_team_name' => 'EDUCANET_TEACHER_TEAM_NAME',
        'teacher_role' => 'EDUCANET_TEACHER_ROLE',
        'tutor_endpoint' => 'EDUCANET_TUTOR_ENDPOINT',
        'tutor_token' => 'EDUCANET_TUTOR_TOKEN',
        'tutor_model' => 'EDUCANET_TUTOR_MODEL',
    ];
    $envName = $envMap[$key] ?? '';
    if ($envName !== '') {
        $value = getenv($envName);
        if (is_string($value) && trim($value) !== '') return trim($value);
        if (isset($_SERVER[$envName]) && is_string($_SERVER[$envName]) && trim($_SERVER[$envName]) !== '') return trim($_SERVER[$envName]);
    }
    $all = is_array($GLOBALS['educanet_secrets'] ?? null) ? $GLOBALS['educanet_secrets'] : [];
    $value = $all[$key] ?? $default;
    return is_scalar($value) ? trim((string)$value) : $default;
}

$modules = require __DIR__ . '/modules.php';
$firstYearModule = require __DIR__ . '/first_year.php';
if (is_array($firstYearModule) && $firstYearModule) {
    $modules['class_1a'] = $firstYearModule;
}
$graphicsCurriculum = require __DIR__ . '/graphics_curriculum.php';
if (isset($modules['class_2a'])) $modules['class_2a']['subject'] = 'Grafika a webdesign';
foreach ($graphicsCurriculum as $graphicsClassId => $guideOverrides) {
    if (!isset($modules[$graphicsClassId]) || !is_array($guideOverrides)) {
        continue;
    }
    $existingGuide = is_array($modules[$graphicsClassId]['guide'] ?? null) ? $modules[$graphicsClassId]['guide'] : [];
    $modules[$graphicsClassId]['guide'] = array_replace($existingGuide, $guideOverrides);
}
$practiceModules = require __DIR__ . '/practice.php';
foreach ($practiceModules as $practiceClassId => $practiceConfig) {
    if (isset($modules[$practiceClassId])) {
        $modules[$practiceClassId]['practice'] = $practiceConfig;
        $modules[$practiceClassId]['lesson_note'] = 'Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku.';
    }
}

$knowledgeExtensions = require __DIR__ . '/knowledge_extensions.php';
foreach ($knowledgeExtensions as $knowledgeClassId => $articles) {
    if (!isset($modules[$knowledgeClassId]) || !is_array($articles)) {
        continue;
    }
    $current = is_array($modules[$knowledgeClassId]['knowledgebase'] ?? null) ? $modules[$knowledgeClassId]['knowledgebase'] : [];
    $modules[$knowledgeClassId]['knowledgebase'] = array_replace($current, $articles);
}

$knowledgeExtensionsPlus = require __DIR__ . '/knowledge_extensions_plus.php';
foreach ($knowledgeExtensionsPlus as $knowledgeClassId => $articles) {
    if (!isset($modules[$knowledgeClassId]) || !is_array($articles)) {
        continue;
    }
    $current = is_array($modules[$knowledgeClassId]['knowledgebase'] ?? null) ? $modules[$knowledgeClassId]['knowledgebase'] : [];
    $modules[$knowledgeClassId]['knowledgebase'] = array_replace($current, $articles);
}

$knowledgeExtensionsMore = require __DIR__ . '/knowledge_extensions_more.php';
foreach ($knowledgeExtensionsMore as $knowledgeClassId => $articles) {
    if (!isset($modules[$knowledgeClassId]) || !is_array($articles)) continue;
    $current = is_array($modules[$knowledgeClassId]['knowledgebase'] ?? null) ? $modules[$knowledgeClassId]['knowledgebase'] : [];
    $modules[$knowledgeClassId]['knowledgebase'] = array_replace($current, $articles);
}

$knowledgeExtensionsEcosystem = require __DIR__ . '/knowledge_extensions_ecosystem.php';
foreach ($knowledgeExtensionsEcosystem as $knowledgeClassId => $articles) {
    if (!isset($modules[$knowledgeClassId]) || !is_array($articles)) continue;
    $current = is_array($modules[$knowledgeClassId]['knowledgebase'] ?? null) ? $modules[$knowledgeClassId]['knowledgebase'] : [];
    $modules[$knowledgeClassId]['knowledgebase'] = array_replace($current, $articles);
}

$knowledgeExtensionsYearpack = require __DIR__ . '/knowledge_extensions_yearpack.php';
foreach ($knowledgeExtensionsYearpack as $knowledgeClassId => $articles) {
    if (!isset($modules[$knowledgeClassId]) || !is_array($articles)) continue;
    $current = is_array($modules[$knowledgeClassId]['knowledgebase'] ?? null) ? $modules[$knowledgeClassId]['knowledgebase'] : [];
    $modules[$knowledgeClassId]['knowledgebase'] = array_replace($current, $articles);
}

$knowledgeExtensionsV30 = require __DIR__ . '/knowledge_extensions_v30.php';
foreach ($knowledgeExtensionsV30 as $knowledgeClassId => $articles) {
    if (!isset($modules[$knowledgeClassId]) || !is_array($articles)) continue;
    $current = is_array($modules[$knowledgeClassId]['knowledgebase'] ?? null) ? $modules[$knowledgeClassId]['knowledgebase'] : [];
    $modules[$knowledgeClassId]['knowledgebase'] = array_replace($current, $articles);
}

// v58: testy a audity mohou přesměrovat úložiště na dočasnou kopii (proměnnou nastavuje jen správce serveru / test).
define('STORAGE_DIR', (static function (): string {
    $override = trim((string)getenv('EDUCANET_STORAGE_DIR'));
    return $override !== '' && is_dir($override) ? rtrim(str_replace('\\', '/', $override), '/') : __DIR__ . '/storage';
})());
const UPLOAD_DIR = __DIR__ . '/uploads';
const MAX_UPLOAD_BYTES = 12 * 1024 * 1024;



// Google Workspace authentication -------------------------------------------
function google_client_id(): string
{
    $value = getenv('EDUCANET_GOOGLE_CLIENT_ID');
    return is_string($value) ? trim($value) : '';
}

function google_auth_configured(): bool
{
    return google_client_id() !== '';
}

function google_workspace_domain(): string
{
    $value = getenv('EDUCANET_GOOGLE_DOMAIN');
    return is_string($value) && trim($value) !== '' ? strtolower(trim($value)) : 'educanet.cz';
}

function google_user(): ?array
{
    $user = $_SESSION['google_user'] ?? null;
    return is_array($user) && !empty($user['sub']) ? $user : null;
}

function local_auth_enabled(): bool
{
    $value = getenv('EDUCANET_LOCAL_AUTH_ENABLED');
    return !is_string($value) || trim($value) === '' || !in_array(strtolower(trim($value)), ['0', 'false', 'off', 'no'], true);
}

function local_auth_require_email_verification(): bool
{
    $value = getenv('EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY');
    return !is_string($value) || trim($value) === '' || !in_array(strtolower(trim($value)), ['0', 'false', 'off', 'no'], true);
}

function local_user(): ?array
{
    $user = $_SESSION['local_user'] ?? null;
    return is_array($user) && !empty($user['id']) && !empty($user['email']) ? $user : null;
}

/**
 * Sjednocená identita pro Google i lokální @educanet.cz účet.
 * `auth_key` je stabilní interní identifikátor používaný pro progress a vazbu na třídu.
 */
function auth_user(): ?array
{
    $google = google_user();
    if ($google) {
        return array_replace($google, [
            'provider' => 'google',
            'id' => (string)$google['sub'],
            'auth_key' => 'google:' . (string)$google['sub'],
        ]);
    }
    $local = local_user();
    if ($local) {
        return array_replace($local, [
            'provider' => 'local',
            'auth_key' => 'local:' . (string)$local['id'],
            'picture' => '',
        ]);
    }
    return null;
}

function auth_is_signed_in(): bool
{
    return auth_user() !== null;
}

function local_accounts_path(): string
{
    return STORAGE_DIR . '/local_accounts.json.php';
}

function local_accounts(): array
{
    return load_php_json(local_accounts_path());
}

function local_email_normalize(string $email): string
{
    return strtolower(trim($email));
}

function local_email_is_allowed(string $email): bool
{
    $email = local_email_normalize($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    return str_ends_with($email, '@' . google_workspace_domain());
}

function local_password_hash(string $password): string
{
    if (defined('PASSWORD_ARGON2ID')) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    return password_hash($password, PASSWORD_DEFAULT);
}

// v58 · SEC-16: pravidla hesel. Minimum 10 znaků, písmeno + číslice, žádné běžné heslo,
// žádné bývalé společné heslo, nic ze jména ani z e-mailu, nic shodného s jednorázovým heslem.
const LOCAL_PASSWORD_MIN_LENGTH = 10;
const LOCAL_PASSWORD_MAX_BYTES = 200;
const LOCAL_PASSWORD_LEGACY_SHARED = 'demo001';

/** Malá písmena bez diakritiky – pro porovnávání se jménem a seznamem běžných hesel. */
function local_password_fold(string $value): string
{
    if (class_exists('Transliterator')) {
        $t = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Lower()');
        if ($t) $value = (string)$t->transliterate($value);
    } else {
        $value = (string)(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value);
    }
    return strtolower($value);
}

/** ~100 nejčastějších hesel (EN + CZ), porovnává se po odstranění číslic a znaků na konci i bez nich. */
function local_password_common(): array
{
    static $list = null;
    if ($list !== null) return $list;
    $raw = '123456 1234567890 12345678 123456789 password heslo heslo123 qwerty qwertz qwertyuiop asdfgh asdfghjkl yxcvbnm '
        . 'abc123 abcd1234 password1 passw0rd iloveyou admin administrator welcome welcome1 letmein monkey dragon master '
        . 'sunshine princess football fotbal hokej baseball superman batman starwars pokemon minecraft fortnite roblox '
        . 'google facebook instagram tiktok youtube samsung iphone apple microsoft windows linux ubuntu internet '
        . 'secret tajne tajneheslo mojeheslo noveheslo heslo1 heslicko skola skolaheslo educanet educanet1 student studenti '
        . 'ucitel trida zak zakyne demo demo001 test test123 tester guest login user uzivatel changeme zmenit zmenheslo '
        . 'praha brno ostrava plzen olomouc ceskarepublika cesko czech sparta slavia banik kocicka pejsek zlaticko miluju '
        . 'milacek laska baruska honzik petr jana tomas lucie martin veronika jakub tereza michal kristyna '
        . 'aaaaaa abcdef abcdefgh 111111 000000 121212 654321 987654321 zaq12wsx 1q2w3e4r 1qaz2wsx qazwsx trustno1 shadow '
        . 'killer hunter ranger jordan michael charlie freedom whatever ninja mustang access flower hello hellokitty lovely';
    $list = array_fill_keys(array_filter(explode(' ', $raw), static fn(string $w): bool => $w !== ''), true);
    return $list;
}

/** Části jména a e-mailu (≥ 3 znaky), které heslo nesmí obsahovat. */
function local_password_context_parts(array $context): array
{
    $sources = [(string)($context['name'] ?? '')];
    $email = (string)($context['email'] ?? '');
    if ($email !== '') $sources[] = (string)strstr($email . '@', '@', true);
    $parts = [];
    foreach ($sources as $source) {
        foreach (preg_split('/[^a-z]+/', local_password_fold($source)) ?: [] as $part) {
            if (strlen($part) >= 3) $parts[$part] = true;
        }
    }
    return array_keys($parts);
}

/**
 * Vrací českou chybovou hlášku, nebo null, když heslo vyhovuje.
 * $context (volitelný, zpětně kompatibilní): 'name', 'email', 'current_hash' (hash stávajícího/jednorázového
 * hesla – nové heslo s ním nesmí souhlasit), 'otp' (jednorázové heslo v otevřené podobě, jen v paměti).
 */
function local_password_validate(string $password, array $context = []): ?string
{
    if (u_strlen($password) < LOCAL_PASSWORD_MIN_LENGTH) return tr('Heslo musí mít alespoň {min} znaků.', ['min' => LOCAL_PASSWORD_MIN_LENGTH]);
    if (strlen($password) > LOCAL_PASSWORD_MAX_BYTES) return tr('Heslo je příliš dlouhé.');
    if (!preg_match('/\p{L}/u', $password) || !preg_match('/\d/', $password)) {
        return tr('Heslo musí obsahovat alespoň jedno písmeno a jednu číslici.');
    }
    $folded = local_password_fold($password);
    if (str_contains($folded, LOCAL_PASSWORD_LEGACY_SHARED)) return tr('Bývalé společné školní heslo použít nejde. Vymysli si vlastní.');
    $core = (string)preg_replace('/[^a-z0-9]+/', '', $folded);
    $letters = (string)preg_replace('/[^a-z]+/', '', $folded);
    $common = local_password_common();
    $trimmed = (string)preg_replace('/[^a-z]+$/', '', (string)preg_replace('/^[^a-z]+/', '', $core));
    if (isset($common[$core]) || isset($common[$letters]) || isset($common[$trimmed]) || count(array_unique(str_split($core))) <= 3) {
        return tr('Tohle heslo je moc běžné a snadno se uhodne. Zkus delší spojení slov a čísel.');
    }
    foreach (local_password_context_parts($context) as $part) {
        if (str_contains($core, $part)) return tr('Heslo nesmí obsahovat tvoje jméno ani část e-mailu.');
    }
    if (isset($context['otp']) && is_string($context['otp']) && $context['otp'] !== '' && hash_equals($context['otp'], $password)) {
        return tr('Nové heslo musí být jiné než jednorázové heslo z kartičky.');
    }
    if (!empty($context['current_hash']) && password_verify($password, (string)$context['current_hash'])) {
        return tr('Nové heslo musí být jiné než jednorázové heslo z kartičky.');
    }
    return null;
}

function local_account_public(array $account): array
{
    return [
        'id' => (string)($account['id'] ?? ''),
        'email' => (string)($account['email'] ?? ''),
        'name' => (string)($account['name'] ?? ''),
        'given_name' => '',
        'family_name' => '',
        'provider' => 'local',
    ];
}

function request_base_url(): string
{
    $configured = getenv('EDUCANET_APP_URL');
    if (is_string($configured) && trim($configured) !== '') return rtrim(trim($configured), '/');
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') || (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $scheme = $https ? 'https' : 'http';
    $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/.');
    return $scheme . '://' . $host . ($dir !== '' ? $dir : '');
}

/**
 * v58 · SEC58-02: základ odkazů do e-mailu (ověření, obnova hesla). Hlavička Host je pod kontrolou
 * klienta, proto se v e-mailu používá JEN nastavené EDUCANET_APP_URL (http/https). Bez něj se
 * e-mail s odkazem neposílá (volající zaloguje varování) – výjimkou je lokální vývoj (EDUCANET_DEV_BYPASS=1).
 */
function auth_mail_base_url(): ?string
{
    $configured = getenv('EDUCANET_APP_URL');
    if (is_string($configured) && trim($configured) !== '') {
        $url = rtrim(trim($configured), '/');
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        $host = (string)parse_url($url, PHP_URL_HOST);
        if (in_array($scheme, ['http', 'https'], true) && $host !== '' && !preg_match('/[\s<>"\'\\\\]/', $url)) return $url;
        error_log('EDUCANET mail: EDUCANET_APP_URL není platná http(s) adresa, odkaz do e-mailu se neposílá.');
        return null;
    }
    if (educanet_dev_bypass_enabled()) return request_base_url();
    error_log('EDUCANET mail: chybí EDUCANET_APP_URL, e-mail s odkazem se neposílá (Host z požadavku se nepoužívá).');
    return null;
}

// v58 · SEC58-02: nejvýš 3 e-maily s odkazem za hodinu na jednu adresu (napříč IP) a 20 z jedné IP.
const AUTH_MAIL_LIMIT_PER_ADDRESS = 3;
const AUTH_MAIL_LIMIT_PER_IP = 20;
const AUTH_MAIL_LIMIT_WINDOW = 3600;

/**
 * Smí se teď na $email poslat e-mail s odkazem? Každé volání se započítá (i pro neexistující účet),
 * aby limit neprozradil existenci účtu. Vrací false, když je limit vyčerpaný.
 */
function auth_mail_rate_limit_hit(string $email): bool
{
    $addressBucket = 'global:mail:' . substr(hash('sha256', local_email_normalize($email)), 0, 32);
    $ok = auth_rate_limit_check($addressBucket, AUTH_MAIL_LIMIT_PER_ADDRESS, AUTH_MAIL_LIMIT_WINDOW)
        && auth_rate_limit_check('mail-ip', AUTH_MAIL_LIMIT_PER_IP, AUTH_MAIL_LIMIT_WINDOW);
    if ($ok) {
        auth_rate_limit_fail($addressBucket);
        auth_rate_limit_fail('mail-ip');
    }
    return $ok;
}

function local_auth_mail_from(): string
{
    $from = getenv('EDUCANET_MAIL_FROM');
    return is_string($from) && filter_var(trim($from), FILTER_VALIDATE_EMAIL) ? trim($from) : ('noreply@' . google_workspace_domain());
}

function send_local_auth_mail(string $to, string $subject, string $body): bool
{
    if (!function_exists('mail')) return false;
    $headers = [
        'From: EDUCANET Learning Lab <' . local_auth_mail_from() . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: EDUCANET-Learning-Lab',
    ];
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}

function issue_local_email_verification(array &$account): string
{
    $token = bin2hex(random_bytes(32));
    $account['verification_token_hash'] = hash('sha256', $token);
    $account['verification_expires_at'] = time() + 86400;
    return $token;
}

function send_local_email_verification(array $account, string $token): bool
{
    $email = (string)$account['email'];
    $base = auth_mail_base_url();
    if ($base === null) return false;
    // v58 · SEC58-02: e-mail v odkazu není – účet se najde podle hashe tokenu.
    $link = $base . '/?view=verify_email&token=' . rawurlencode($token);
    $name = (string)($account['name'] ?? '');
    $greeting = $name !== '' ? tr('Ahoj {jmeno},', ['jmeno' => $name]) : tr('Ahoj,');
    $body = $greeting . "\n\n"
        . tr('Pro dokončení registrace do EDUCANET Learning Lab ověř školní e-mail:') . "\n\n"
        . $link . "\n\n" . tr('Odkaz platí 24 hodin. Pokud jsi registraci nevytvářel/a, zprávu ignoruj.');
    return send_local_auth_mail($email, tr('Ověření EDUCANET Learning Lab účtu'), $body);
}

// v58 · SEC-17: odkaz pro obnovu hesla platí 30 minut a jen jednou. Token z odkazu se hned
// vymění za záznam v session (jen hash) a prohlížeč se přesměruje na adresu bez tokenu.
const LOCAL_PASSWORD_RESET_TTL = 1800;
const LOCAL_PASSWORD_RESET_SESSION_KEY = 'local_password_reset';

function issue_local_password_reset(array &$account): string
{
    $token = bin2hex(random_bytes(32));
    $account['reset_token_hash'] = hash('sha256', $token);
    $account['reset_expires_at'] = time() + LOCAL_PASSWORD_RESET_TTL;
    return $token;
}

function send_local_password_reset(array $account, string $token): bool
{
    $email = (string)$account['email'];
    $base = auth_mail_base_url();
    if ($base === null) return false;
    // E-mail v odkazu není – účet se najde podle hashe tokenu.
    $link = $base . '/?view=reset_password&token=' . rawurlencode($token);
    $body = tr('Pro nastavení nového hesla do EDUCANET Learning Lab otevři:') . "\n\n" . $link
        . "\n\n" . tr('Odkaz platí {min} minut a funguje jen jednou. Pokud jsi reset nepožadoval/a, zprávu ignoruj.', ['min' => intdiv(LOCAL_PASSWORD_RESET_TTL, 60)]);
    return send_local_auth_mail($email, tr('Obnovení hesla EDUCANET Learning Lab'), $body);
}

/** Vydá token a pošle odkaz. Nikdy neprozradí, jestli účet existuje. Vrací token jen pro testy (null = nic neodesláno). */
function local_password_reset_request(string $email, bool $send = true): ?string
{
    $email = local_email_normalize($email);
    if (!local_email_is_allowed($email)) return null;
    if ($send && !auth_mail_rate_limit_hit($email)) return null;
    $token = null;
    $account = null;
    storage_update(local_accounts_path(), static function (array $accounts) use ($email, &$token, &$account): array {
        $row = $accounts[$email] ?? null;
        if (!is_array($row) || empty($row['verified_at'])) return $accounts;
        $token = issue_local_password_reset($row);
        $accounts[$email] = $row;
        $account = $row;
        return $accounts;
    });
    if ($token !== null && $send && is_array($account)) send_local_password_reset($account, $token);
    return $token;
}

/**
 * v58 · SEC58-02: ověří e-mail podle tokenu z odkazu (bez e-mailu v URL). Token se spotřebuje ve stejném
 * zámku, ve kterém se účet označí jako ověřený. Vrací uložený účet, nebo null (neplatný/vypršelý token).
 */
function local_email_verification_consume(string $token, ?int $now = null): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $now ??= time();
    $hash = hash('sha256', $token);
    $out = null;
    storage_update(local_accounts_path(), static function (array $accounts) use ($hash, $now, &$out): array {
        foreach ($accounts as $email => $row) {
            if (!is_array($row) || empty($row['verification_token_hash']) || !hash_equals((string)$row['verification_token_hash'], $hash)) continue;
            if ((int)($row['verification_expires_at'] ?? 0) < $now) return $accounts;
            $row = array_replace($row, ['verified_at' => date(DATE_ATOM), 'verification_token_hash' => null, 'verification_expires_at' => null]);
            $accounts[$email] = $row;
            $out = $row;
            return $accounts;
        }
        return $accounts;
    });
    return $out;
}

/** E-mail účtu s platným (nevypršelým) resetem podle hashe tokenu, jinak null. */
function local_password_reset_lookup_hash(string $tokenHash, ?int $now = null): ?string
{
    if (!preg_match('/^[a-f0-9]{64}$/', $tokenHash)) return null;
    $now ??= time();
    foreach (local_accounts() as $email => $row) {
        if (!is_array($row) || empty($row['reset_token_hash'])) continue;
        if (hash_equals((string)$row['reset_token_hash'], $tokenHash) && (int)($row['reset_expires_at'] ?? 0) >= $now) return (string)$email;
    }
    return null;
}

/** GET s tokenem: uloží do session jen hash tokenu. Volající pak přesměruje na ?view=reset_password bez tokenu. */
function local_password_reset_exchange(string $token): bool
{
    unset($_SESSION[LOCAL_PASSWORD_RESET_SESSION_KEY]);
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return false;
    $hash = hash('sha256', $token);
    $email = local_password_reset_lookup_hash($hash);
    if ($email === null) return false;
    $_SESSION[LOCAL_PASSWORD_RESET_SESSION_KEY] = ['email' => $email, 'token_hash' => $hash, 'at' => time()];
    return true;
}

/** E-mail, pro který má tato session rozpracovaný platný reset (ověřuje se proti úložišti), jinak null. */
function local_password_reset_session_email(): ?string
{
    $state = $_SESSION[LOCAL_PASSWORD_RESET_SESSION_KEY] ?? null;
    if (!is_array($state) || (int)($state['at'] ?? 0) < time() - LOCAL_PASSWORD_RESET_TTL) return null;
    $email = local_password_reset_lookup_hash((string)($state['token_hash'] ?? ''));
    return $email !== null && hash_equals($email, (string)($state['email'] ?? '')) ? $email : null;
}

/**
 * POST nového hesla: token se spotřebuje (jednorázový) ve stejném zámku, ve kterém se mění heslo.
 * Vrací null při úspěchu, jinak českou hlášku. Úspěch zruší vynucenou změnu i jednorázové heslo (v58).
 */
function local_password_reset_complete(string $password, string $confirm): ?string
{
    $state = $_SESSION[LOCAL_PASSWORD_RESET_SESSION_KEY] ?? null;
    $email = local_password_reset_session_email();
    if ($email === null || !is_array($state)) return 'Odkaz pro obnovení hesla není platný nebo už vypršel.';
    if (!hash_equals($password, $confirm)) return 'Hesla se neshodují.';
    $account = local_accounts()[$email] ?? [];
    $error = local_password_validate($password, ['email' => $email, 'name' => (string)($account['name'] ?? ''), 'current_hash' => (string)($account['password_hash'] ?? '')]);
    if ($error !== null) return $error;
    $hash = local_password_hash($password);
    $tokenHash = (string)$state['token_hash'];
    $done = false;
    storage_update(local_accounts_path(), static function (array $accounts) use ($email, $tokenHash, $hash, &$done): array {
        $row = $accounts[$email] ?? null;
        if (!is_array($row) || empty($row['reset_token_hash']) || !hash_equals((string)$row['reset_token_hash'], $tokenHash) || (int)($row['reset_expires_at'] ?? 0) < time()) return $accounts;
        unset($row['otp'], $row['initial_password']);
        $accounts[$email] = array_replace($row, [
            'password_hash' => $hash, 'reset_token_hash' => null, 'reset_expires_at' => null,
            'must_change_password' => false, 'password_changed_at' => date(DATE_ATOM),
        ]);
        $done = true;
        return $accounts;
    });
    unset($_SESSION[LOCAL_PASSWORD_RESET_SESSION_KEY]);
    return $done ? null : 'Odkaz pro obnovení hesla není platný nebo už vypršel.';
}

/** Hlavičky stránky obnovy hesla: žádný referrer (ani bez tokenu) a žádné cachování. */
function local_password_reset_headers(): void
{
    if (headers_sent()) return;
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store, max-age=0');
    header('Pragma: no-cache');
}

// Pokusy o přihlášení se evidují na serveru (klíč = IP klienta + bucket), takže
// smazání cookie limit neobejde. Session slouží jen jako záloha, když úložiště selže.
const AUTH_RATE_LIMIT_RETENTION_SECONDS = 86400;

// v58 · SEC58-12: přihlášení žáka má tři vrstvy limitu (okno 15 min):
//  - e-mail + IP: 8 neúspěchů (původní),
//  - IP napříč účty: 60 neúspěchů (celá třída za jednou školní IP se občas splete, plošné hádání ne),
//  - účet napříč IP (bucket global:): 25 neúspěchů – distribuované hádání jednoho účtu.
//    Spolužák za stejnou IP naráží dřív na limit e-mail + IP (8), takže cizí účet globálně nezamkne.
const AUTH_LOGIN_WINDOW = 900;
const AUTH_LOGIN_LIMIT_EMAIL_IP = 8;
const AUTH_LOGIN_LIMIT_IP = 60;
const AUTH_LOGIN_LIMIT_ACCOUNT = 25;
// Učitel: limit IP + otisk prohlížeče (User-Agent) – žák za stejnou školní IP s jiným prohlížečem učitele
// nezamkne; tvrdý strop na IP je vysoký (klíč je dlouhý, hádáním se neprolomí). Kompromis viz INSTALL.md.
const TEACHER_LOGIN_LIMIT_CLIENT = 20;
const TEACHER_LOGIN_LIMIT_IP = 200;
const TEACHER_LOGIN_WINDOW = 600;

/** Limity přihlášení žáka pro e-mail: [bucket => max pokusů]. */
function auth_login_buckets(string $email): array
{
    $email = local_email_normalize($email);
    return [
        'local-login:' . substr(hash('sha256', $email . '|' . (string)($_SERVER['REMOTE_ADDR'] ?? '')), 0, 24) => AUTH_LOGIN_LIMIT_EMAIL_IP,
        'local-login-ip' => AUTH_LOGIN_LIMIT_IP,
        'global:local-login-acct:' . substr(hash('sha256', $email), 0, 32) => AUTH_LOGIN_LIMIT_ACCOUNT,
    ];
}

/** Učitelské limity: [bucket => max pokusů]. */
function teacher_login_buckets(): array
{
    // v59 (SEC59-03): v režimu učitelských účtů chrání hesla zámky podle účtu + IP (teacher_accounts_v59_limits.php);
    // hrubé limity tu jen brzdí záplavu – jinak by žáci za sdílenou školní IP zablokovali přihlašování všem učitelům.
    $accounts = function_exists('teacher59_mode') && teacher59_mode() === 'accounts';
    return [
        'teacher-login:' . substr(hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16) => $accounts ? TEACHER_LOGIN_LIMIT_CLIENT * 10 : TEACHER_LOGIN_LIMIT_CLIENT,
        'teacher-login-ip' => $accounts ? TEACHER_LOGIN_LIMIT_IP * 10 : TEACHER_LOGIN_LIMIT_IP,
    ];
}

/** true = všechny limity ještě dovolují pokus. */
function auth_rate_limit_check_all(array $buckets, int $windowSeconds): bool
{
    foreach ($buckets as $bucket => $max) {
        if (!auth_rate_limit_check((string)$bucket, (int)$max, $windowSeconds)) return false;
    }
    return true;
}

function auth_rate_limit_fail_all(array $buckets): void
{
    foreach (array_keys($buckets) as $bucket) auth_rate_limit_fail((string)$bucket);
}

function auth_rate_limit_path(): string
{
    return STORAGE_DIR . '/rate_limits.json.php';
}

/** Klíč limitu: IP + bucket. Bucket s prefixem „global:“ platí napříč IP (např. na jeden účet). */
function auth_rate_limit_key(string $bucket): string
{
    if (str_starts_with($bucket, 'global:')) return hash('sha256', '*|' . $bucket);
    return hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'cli') . '|' . $bucket);
}

function auth_rate_limit_recent(array $times, int $windowSeconds, int $now): array
{
    return array_values(array_filter($times, static fn($t) => is_int($t) && $t > $now - $windowSeconds));
}

function auth_rate_limit_check(string $bucket, int $maxAttempts = 8, int $windowSeconds = 900): bool
{
    $now = time();
    $all = is_array($_SESSION['auth_rate_limits'] ?? null) ? $_SESSION['auth_rate_limits'] : [];
    $sessionTimes = auth_rate_limit_recent(is_array($all[$bucket] ?? null) ? $all[$bucket] : [], $windowSeconds, $now);
    $all[$bucket] = $sessionTimes;
    $_SESSION['auth_rate_limits'] = $all;
    $stored = load_php_json(auth_rate_limit_path());
    $storedTimes = auth_rate_limit_recent(is_array($stored[auth_rate_limit_key($bucket)] ?? null) ? $stored[auth_rate_limit_key($bucket)] : [], $windowSeconds, $now);
    return max(count($sessionTimes), count($storedTimes)) < $maxAttempts;
}

function auth_rate_limit_fail(string $bucket): void
{
    $now = time();
    if (!isset($_SESSION['auth_rate_limits']) || !is_array($_SESSION['auth_rate_limits'])) $_SESSION['auth_rate_limits'] = [];
    if (!isset($_SESSION['auth_rate_limits'][$bucket]) || !is_array($_SESSION['auth_rate_limits'][$bucket])) $_SESSION['auth_rate_limits'][$bucket] = [];
    $_SESSION['auth_rate_limits'][$bucket][] = $now;
    $key = auth_rate_limit_key($bucket);
    try {
        storage_update(auth_rate_limit_path(), static function (array $rows) use ($key, $now): array {
            $out = [];
            foreach ($rows as $k => $times) {
                if (!is_array($times)) continue;
                $recent = auth_rate_limit_recent($times, AUTH_RATE_LIMIT_RETENTION_SECONDS, $now);
                if ($recent) $out[(string)$k] = $recent;
            }
            $out[$key] = array_slice(array_merge($out[$key] ?? [], [$now]), -100);
            return $out;
        });
    } catch (Throwable $e) {
        error_log('EDUCANET rate limit: ' . $e->getMessage());
    }
}

function auth_rate_limit_clear(string $bucket): void
{
    if (isset($_SESSION['auth_rate_limits'][$bucket])) unset($_SESSION['auth_rate_limits'][$bucket]);
    $key = auth_rate_limit_key($bucket);
    if (!array_key_exists($key, load_php_json(auth_rate_limit_path()))) return;
    try {
        storage_update(auth_rate_limit_path(), static function (array $rows) use ($key): array {
            unset($rows[$key]);
            return $rows;
        });
    } catch (Throwable $e) {
        error_log('EDUCANET rate limit: ' . $e->getMessage());
    }
}

function base64url_decode_str(string $value): string|false
{
    $pad = strlen($value) % 4;
    if ($pad) $value .= str_repeat('=', 4 - $pad);
    return base64_decode(strtr($value, '-_', '+/'), true);
}

function google_fetch_certs(): array
{
    $cache = STORAGE_DIR . '/google_certs_cache.json.php';
    $cached = load_php_json($cache);
    if (!empty($cached['fetched_at']) && (time() - (int)$cached['fetched_at']) < 21600 && is_array($cached['certs'] ?? null)) {
        return $cached['certs'];
    }
    $url = 'https://www.googleapis.com/oauth2/v1/certs';
    $raw = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => true]);
        $raw = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 6], 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $raw = @file_get_contents($url, false, $ctx);
    }
    $certs = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($certs) || !$certs) {
        return is_array($cached['certs'] ?? null) ? $cached['certs'] : [];
    }
    storage_write($cache, ['fetched_at' => time(), 'certs' => $certs]); // cache, ne RMW
    return $certs;
}

function verify_google_id_token(string $jwt): array
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) throw new RuntimeException(tr('Neplatný Google token.'));
    [$h64, $p64, $s64] = $parts;
    $headerRaw = base64url_decode_str($h64);
    $payloadRaw = base64url_decode_str($p64);
    $signature = base64url_decode_str($s64);
    if ($headerRaw === false || $payloadRaw === false || $signature === false) throw new RuntimeException(tr('Google token nelze přečíst.'));
    $header = json_decode($headerRaw, true);
    $payload = json_decode($payloadRaw, true);
    if (!is_array($header) || !is_array($payload) || ($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) throw new RuntimeException(tr('Google token má neplatnou hlavičku.'));
    $certs = google_fetch_certs();
    $cert = $certs[(string)$header['kid']] ?? null;
    if (!is_string($cert) || $cert === '') throw new RuntimeException(tr('Nelze ověřit podpis Google účtu. Zkus přihlášení znovu.'));
    $verified = openssl_verify($h64 . '.' . $p64, $signature, $cert, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) throw new RuntimeException(tr('Podpis Google účtu není platný.'));
    $now = time();
    $iss = (string)($payload['iss'] ?? '');
    if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) throw new RuntimeException(tr('Neplatný vydavatel Google tokenu.'));
    $aud = $payload['aud'] ?? '';
    $clientId = google_client_id();
    $audOk = is_array($aud) ? in_array($clientId, $aud, true) : hash_equals($clientId, (string)$aud);
    if (!$audOk) throw new RuntimeException(tr('Google token není určen pro tuto aplikaci.'));
    if ((int)($payload['exp'] ?? 0) <= $now || (int)($payload['iat'] ?? 0) > $now + 120) throw new RuntimeException(tr('Google token expiroval.'));
    $domain = google_workspace_domain();
    $email = strtolower(trim((string)($payload['email'] ?? '')));
    $hd = strtolower(trim((string)($payload['hd'] ?? '')));
    if (empty($payload['email_verified']) || $hd !== $domain || !str_ends_with($email, '@' . $domain)) {
        throw new RuntimeException(tr('Použij školní Google Workspace účet @{domain}.', ['domain' => $domain]));
    }
    return [
        'sub' => (string)$payload['sub'],
        'email' => $email,
        'name' => trim((string)($payload['name'] ?? '')),
        'given_name' => trim((string)($payload['given_name'] ?? '')),
        'family_name' => trim((string)($payload['family_name'] ?? '')),
        'picture' => (string)($payload['picture'] ?? ''),
        'hd' => $hd,
    ];
}

function normalized_person_name(string $value): string
{
    static $translit = false; // v61: instance se vytváří jednou (false = ještě nezkoušeno, null = nejde vytvořit)
    static $memo = [];        // v61: čistá funkce – stejný vstup (adresář žáků ji volá stokrát) se přepočítá jen jednou
    if (isset($memo[$value])) return $memo[$value];
    $input = $value;
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    if (class_exists('Transliterator')) {
        if ($translit === false) $translit = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Lower()');
        if ($translit) $value = $translit->transliterate($value);
    } else {
        $value = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value);
    }
    $result = preg_replace('/[^a-z0-9]+/', '', strtolower($value)) ?? '';
    if (count($memo) > 4000) $memo = [];
    return $memo[$input] = $result;
}

function student_directory(): array
{
    static $rows = null;
    if (is_array($rows)) return $rows;
    $path = __DIR__ . '/student_directory.php';
    $rows = is_file($path) ? (require $path) : [];
    $rows = is_array($rows) ? $rows : [];
    // v51: žáci z importovaného V1 dotazníku a noví respondenti (např. 1.A) doplní adresář.
    if (function_exists('intake_v51_directory_rows')) {
        $seen = [];
        foreach ($rows as $row) {
            if (is_array($row)) $seen[(string)($row['class_id'] ?? '') . '|' . normalized_person_name(trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')))] = true;
        }
        foreach (intake_v51_directory_rows() as $row) {
            $key = $row['class_id'] . '|' . normalized_person_name(trim($row['first_name'] . ' ' . $row['last_name']));
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $rows[] = $row;
        }
    }
    // v58 F6: po přechodu roku / rozlišení jmenovců upraví seznam tříd podle registru identity (bez něj no-op).
    if (function_exists('identity58_directory_overlay')) {
        try { $rows = identity58_directory_overlay($rows); } catch (Throwable $e) { error_log('EDUCANET identita: ' . $e->getMessage()); }
    }
    return $rows;
}

function student_account_map(): array
{
    return load_php_json(STORAGE_DIR . '/student_accounts.json.php');
}

// v58 (F2): STORAGE_GUARD_LINE, storage_encode_payload, storage_decode_raw, storage_read_locked_raw,
// storage_write_locked, storage_update a storage_update_many jsou v jádře storage_v58.php.

/**
 * Kompatibilní obal: zapíše CELÝ obsah souboru. Jen pro cache/snapshoty/fixtures – nikdy ne pro
 * read-modify-write (to patří do storage_update). Nová volání hlídá tools/v58_storage_audit.php.
 */
function save_php_json_map(string $path, array $data): void
{
    storage_write($path, $data);
}

/**
 * Bezpečný odkaz pro href: pouze http/https. Odkaz bez schématu (např. „www.canva.com/…“)
 * doplní na https://, cokoli jiného (javascript:, data:, …) vrátí jako ''.
 */
function safe_url(string $url): string
{
    $url = trim($url);
    if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) return '';
    if (preg_match('~^https?://[^\s/?#]+~i', $url)) return $url;
    if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url) || preg_match('/\s/', $url) || str_starts_with($url, '\\')) return '';
    $candidate = 'https://' . ltrim($url, '/');
    return preg_match('~^https://[^\s/?#.]+\.[^\s/?#]+~i', $candidate) ? $candidate : '';
}

function auth_assignment_key(array $user): string
{
    if (($user['provider'] ?? '') === 'local') return 'local:' . (string)($user['id'] ?? '');
    // Zachování zpětné kompatibility se starší mapou Google účtů, která používala přímo `sub`.
    return (string)($user['sub'] ?? $user['id'] ?? '');
}

function bind_auth_account(array $user, string $classId, string $studentLabel): void
{
    $key = auth_assignment_key($user);
    if ($key === '') throw new RuntimeException('Účet nemá platný interní identifikátor.');
    $entry = [
        'provider' => (string)($user['provider'] ?? 'google'),
        'email' => (string)($user['email'] ?? ''),
        'class_id' => $classId,
        'student_label' => $studentLabel,
        'linked_at' => date(DATE_ATOM),
    ];
    // v58 F6: archivovaný účet (absolvent) nejde znovu propojit; nový záznam dostane stabilní student_id.
    $existing = student_account_map()[$key] ?? null;
    if (is_array($existing) && function_exists('identity58_assignment_allowed') && !identity58_assignment_allowed($existing)) {
        $_SESSION['flash'] = 'Účet je archivovaný – propojení není možné. Obrať se na učitele.';
        redirect_to('?view=home');
    }
    $studentId = function_exists('identity58_id_for_student') ? identity58_id_for_student($classId, $studentLabel) : null;
    if ($studentId !== null) $entry['student_id'] = $studentId;
    // v58: zápis pod zámkem (souběžná propojení celé třídy se nepřepíšou).
    storage_update(STORAGE_DIR . '/student_accounts.json.php', static function (array $map) use ($key, $entry): array {
        $map[$key] = $entry;
        return $map;
    });
    if (function_exists('identity58_ensure')) { try { identity58_ensure(); } catch (Throwable $e) { error_log('EDUCANET identita: ' . $e->getMessage()); } }
    $_SESSION['next_class_id'] = $classId;
    $_SESSION['student_label'] = $studentLabel;
}

function bind_google_account(array $user, string $classId, string $studentLabel): void
{
    $user['provider'] = 'google';
    bind_auth_account($user, $classId, $studentLabel);
}

function try_restore_auth_assignment(array $modules): bool
{
    $user = auth_user();
    if (!$user) return false;
    $map = student_account_map();
    $row = $map[auth_assignment_key($user)] ?? null;
    if (is_array($row) && function_exists('identity58_assignment_allowed') && !identity58_assignment_allowed($row)) return false;
    if (is_array($row) && isset($modules[$row['class_id'] ?? ''])) {
        $_SESSION['next_class_id'] = (string)$row['class_id'];
        $_SESSION['student_label'] = (string)($row['student_label'] ?? $user['name'] ?? $user['email']);
        return true;
    }

    // Automatické spárování podle jména používáme jen u Google Workspace,
    // kde jméno přichází z ověřené školní identity. U lokální registrace je jméno
    // uživatelský vstup, proto student vždy projde jednorázovým propojením přes kód třídy.
    if (($user['provider'] ?? '') === 'google') {
        $needle = normalized_person_name((string)($user['name'] ?? ''));
        if ($needle === '') {
            $needle = normalized_person_name(trim((string)($user['given_name'] ?? '') . ' ' . (string)($user['family_name'] ?? '')));
        }
        $matches = [];
        foreach (student_directory() as $student) {
            $full = trim((string)($student['first_name'] ?? '') . ' ' . (string)($student['last_name'] ?? ''));
            if ($needle !== '' && normalized_person_name($full) === $needle && isset($modules[$student['class_id'] ?? ''])) $matches[] = $student;
        }
        if (count($matches) === 1) {
            $m = $matches[0];
            bind_auth_account($user, (string)$m['class_id'], trim((string)$m['first_name'] . ' ' . (string)$m['last_name']));
            return true;
        }
    }
    return false;
}

function try_restore_google_assignment(array $modules): bool
{
    return try_restore_auth_assignment($modules);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function u_substr(string $value, int $start, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    preg_match_all('/./us', $value, $matches);
    return implode('', array_slice($matches[0] ?? [], $start, $length));
}

function u_strlen(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    preg_match_all('/./us', $value, $matches);
    return count($matches[0] ?? []);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    $expected = $_SESSION['csrf'] ?? '';
    // v59 (login CSRF): v úplně nové session je token prázdný – hash_equals('', '') by prošlo.
    if (!is_string($token) || !is_string($expected) || $token === '' || $expected === '' || !hash_equals($expected, $token)) {
        http_response_code(419);
        exit(tr('Neplatný nebo expirovaný formulář. Obnov stránku a zkus to znovu.'));
    }
}

/**
 * v68: cockpit učitele si zaregistruje funkci (teacher68_css_href → assets/cx-*.css, tokenizované kopie pro tmavý režim); žákovská aplikace ji nemá,
 * takže pro ni platí $default (původní URL). Sdílené šablony (robots, týmové hry, události Arény) tak nemusí znát cockpit.
 */
function edu_css_href(string $path, string $default): string
{
    $mapper = $GLOBALS['edu_css_mapper'] ?? null;
    return is_string($mapper) && is_callable($mapper) ? (string)$mapper($path) : $default;
}

function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function class_from_code(array $modules, string $code): ?string
{
    $needle = strtoupper(trim($code));
    foreach ($modules as $id => $module) {
        // v58 F6: po přechodu roku platí nové kódy tříd z registru identity.
        $expected = function_exists('identity58_class_code') ? identity58_class_code((string)$id, (string)$module['code']) : (string)$module['code'];
        if (strtoupper($expected) === $needle) {
            return $id;
        }
    }
    return null;
}

function current_class_id(array $modules): ?string
{
    $id = $_SESSION['next_class_id'] ?? null;
    return is_string($id) && isset($modules[$id]) ? $id : null;
}

/**
 * Vrací logickou rodinu předmětu. Materiály z jedné rodiny lze sdílet napříč ročníky,
 * ale student nikdy neuvidí knowledgebase jiné rodiny předmětu.
 */
function subject_family(array $module): string
{
    $type = strtolower((string)($module['course_type'] ?? ''));
    if (str_starts_with($type, 'graphics')) {
        return 'graphics';
    }
    if (str_contains($type, 'network') || str_contains($type, 'os') || str_contains($type, 'server')) {
        return 'networks';
    }
    return preg_replace('/[^a-z0-9_-]+/i', '-', strtolower((string)($module['subject'] ?? 'general'))) ?: 'general';
}

function allowed_subject_class_ids(string $currentClassId, array $modules): array
{
    if (!isset($modules[$currentClassId])) return [];
    $family = subject_family($modules[$currentClassId]);
    preg_match('/class_(\d+)/', $currentClassId, $m);
    $currentYear = (int)($m[1] ?? 99);
    $ids = [];
    foreach ($modules as $id => $module) {
        if (subject_family((array)$module) !== $family) continue;
        preg_match('/class_(\d+)/', (string)$id, $tm);
        $targetYear = (int)($tm[1] ?? 99);
        // Student vidí vlastní a předchozí ročníky stejného předmětu, nikdy budoucí ročník.
        if ($targetYear <= $currentYear) $ids[] = (string)$id;
    }
    usort($ids, static function (string $a, string $b) use ($modules): int {
        $an = (string)($modules[$a]['name'] ?? $a);
        $bn = (string)($modules[$b]['name'] ?? $b);
        return strnatcasecmp($an, $bn);
    });
    return $ids;
}

function can_access_subject_class(string $currentClassId, string $targetClassId, array $modules): bool
{
    return in_array($targetClassId, allowed_subject_class_ids($currentClassId, $modules), true);
}

/** @return array<int|string> */
function shuffled_keys(array $items): array
{
    $keys = array_keys($items);
    if (count($keys) > 1) {
        shuffle($keys);
    }
    return $keys;
}

/**
 * Pořadí odpovědí se vytvoří jednou při startu testu a drží se v session,
 * takže správná možnost nemá stabilní písmeno ani pozici.
 */
function build_test_option_orders(array $questions): array
{
    $orders = [];
    foreach ($questions as $question) {
        if (!is_array($question) || empty($question['id']) || !is_array($question['options'] ?? null)) {
            continue;
        }
        $orders[(string)$question['id']] = shuffled_keys($question['options']);
    }
    return $orders;
}

function test_option_order(array $test, array $question): array
{
    $qid = (string)($question['id'] ?? '');
    $order = $test['option_orders'][$qid] ?? null;
    if (is_array($order) && count($order) === count($question['options'] ?? [])) {
        return $order;
    }
    return shuffled_keys((array)($question['options'] ?? []));
}

/**
 * Authoring guard proti časté MCQ chybě „nejdelší odpověď = správná“.
 * Pokud je správná možnost jediná nejdelší, prodloužíme jeden věrohodný distraktor
 * neutrálním kontextem. Hodnota/klíč odpovědi se nemění.
 */
function balanced_quiz_option_labels(array $options, int|string $correct, string $family = 'general'): array
{
    $labels = $options;
    if (!array_key_exists($correct, $labels) || count($labels) < 2) {
        return $labels;
    }
    $lengths = [];
    foreach ($labels as $key => $value) {
        $lengths[$key] = u_strlen(trim((string)$value));
    }
    $max = max($lengths);
    $maxKeys = array_keys($lengths, $max, true);
    if (count($maxKeys) !== 1 || (string)$maxKeys[0] !== (string)$correct) {
        return $labels;
    }
    $wrong = array_filter(array_keys($labels), static fn($key) => (string)$key !== (string)$correct);
    usort($wrong, static fn($a, $b) => ($lengths[$b] ?? 0) <=> ($lengths[$a] ?? 0));
    $target = $wrong[0] ?? null;
    if ($target === null) return $labels;
    $suffixes = $family === 'graphics'
        ? [' — v praxi by se tato varianta ještě ověřila na cílovém formátu.', ' — při návrhu by se tento závěr kontroloval i v malém náhledu.']
        : [' — tento závěr by se v praxi ještě ověřil konkrétním měřením.', ' — při troubleshootingu by se tato možnost kontrolovala samostatným testem.'];
    $seed = abs(crc32((string)$correct . '|' . implode('|', array_map('strval', $labels))));
    $suffix = $suffixes[$seed % count($suffixes)];
    $labels[$target] = rtrim((string)$labels[$target]) . $suffix;
    if (u_strlen((string)$labels[$target]) <= $max) {
        $labels[$target] .= ' Rozhodnutí musí vycházet z důkazu, ne z délky formulace.';
    }
    return $labels;
}


function active_practice(): ?array
{
    $practice = $_SESSION['next_practice'] ?? null;
    return is_array($practice) && !empty($practice['active']) ? $practice : null;
}

function active_test(): ?array
{
    $test = $_SESSION['next_test'] ?? null;
    return is_array($test) && !empty($test['active']) ? $test : null;
}

function guarded_study_redirect(): void
{
    if (active_test()) {
        $_SESSION['flash'] = 'Během spuštěného testu jsou studijní materiály skryté. Test nejprve dokonči nebo ukonči.';
        redirect_to('?view=test');
    }
}

function educanet_apcu_enabled(): bool
{
    static $enabled = null;
    if (is_bool($enabled)) return $enabled;
    $enabled = function_exists('apcu_fetch') && function_exists('apcu_store') && function_exists('apcu_delete');
    if ($enabled && PHP_SAPI === 'cli') {
        $cli = strtolower(trim((string)ini_get('apc.enable_cli')));
        $enabled = in_array($cli, ['1','on','true','yes'], true);
    }
    return $enabled;
}

function php_json_cache_forget(string $path): void
{
    if (isset($GLOBALS['educanet_json_request_cache']) && is_array($GLOBALS['educanet_json_request_cache'])) {
        unset($GLOBALS['educanet_json_request_cache'][$path]);
    }
    if (educanet_apcu_enabled()) {
        $pointerKey = 'educanet:json:pointer:' . hash('sha256', $path);
        $dataKey = apcu_fetch($pointerKey, $ok);
        if ($ok && is_string($dataKey) && $dataKey !== '') apcu_delete($dataKey);
        apcu_delete($pointerKey);
    }
    if (isset($GLOBALS['educanet_runtime_indexes']) && is_array($GLOBALS['educanet_runtime_indexes'])) {
        $GLOBALS['educanet_runtime_indexes'] = [];
    }
}

/**
 * Kompatibilní obal nad storage_read(): čtení pod LOCK_SH, cache v requestu (educanet_json_request_cache)
 * a v APCu podle podpisu souboru; poškozený JSON = výjimka (Z1).
 */
function load_php_json(string $path): array
{
    return storage_read_request($path);
}

/**
 * Kompatibilní obal (DAT-03): registrovaný „pouze přidávaný“ soubor → storage_append do měsíčního JSONL,
 * jinak přidání do JSON seznamu pod jedním zámkem. Nová volání hlídá tools/v58_storage_audit.php.
 */
function append_php_json(string $path, array $record): void
{
    $stream = storage_stream_for_path($path);
    if ($stream !== null) {
        storage_append($stream, $record);
        return;
    }
    storage_list_push($path, $record);
}

function result_id(): string
{
    return 'next_' . bin2hex(random_bytes(8));
}

function module_url(string $view, array $params = []): string
{
    return '?' . http_build_query(array_merge(['view' => $view], $params));
}

function teacher_export_key(): string
{
    return educanet_secret('teacher_export_key');
}

function teacher_export_configured(): bool
{
    // v59: rozhraní je nastavené i bez sdíleného klíče, když existují učitelské účty (nebo jejich úložiště – nečitelné = 503).
    return teacher_export_key() !== '' || (function_exists('teacher59_mode') && teacher59_mode() !== 'legacy');
}

function teacher_export_authenticated(): bool
{
    // v59 · AUTHZ58-07: v režimu účtů jen platná relace účtu (aktivní, session_version, timeouty, bez vynucené změny hesla).
    if (function_exists('teacher59_mode') && teacher59_mode() !== 'legacy') return teacher59_session_state() === 'ok';
    return !empty($_SESSION['teacher_export_authenticated']);
}

function extra_grade_from_points(?int $points, int $maxPoints = 25): ?int
{
    if ($points === null || $maxPoints <= 0) {
        return null;
    }
    $points = max(0, min($maxPoints, $points));
    if ($maxPoints === 25) {
        return match (true) {
            $points >= 23 => 1,
            $points >= 20 => 2,
            $points >= 16 => 3,
            $points >= 12 => 4,
            default => 5,
        };
    }
    $ratio = $points / $maxPoints;
    return match (true) {
        $ratio >= 0.92 => 1,
        $ratio >= 0.80 => 2,
        $ratio >= 0.64 => 3,
        $ratio >= 0.48 => 4,
        default => 5,
    };
}

function extra_submission_points(array $submission): ?int
{
    $value = $submission['points'] ?? ($submission['grading']['points'] ?? null);
    if ($value === null || $value === '' || !is_numeric($value)) {
        return null;
    }
    return (int)$value;
}

function extra_submission_grade(array $submission): ?int
{
    $value = $submission['grade'] ?? ($submission['grading']['grade'] ?? null);
    if ($value !== null && $value !== '' && is_numeric($value)) {
        return max(1, min(5, (int)$value));
    }
    $points = extra_submission_points($submission);
    if ($points === null) {
        return null;
    }
    return extra_grade_from_points($points, (int)($submission['max_points'] ?? 25));
}

function extra_status_label(string $status): string
{
    return match ($status) {
        'awaiting_teacher_grading' => tr('Čeká na hodnocení'),
        'graded' => tr('Ohodnoceno'),
        'returned_for_revision' => tr('Vráceno k dopracování'),
        'revision_submitted' => tr('Dopracování odevzdáno'),
        'cancelled' => tr('Zrušeno'),
        default => $status !== '' ? $status : tr('Neznámý stav'),
    };
}

function csv_safe_cell(string $value): string
{
    // Ochrana před CSV/Excel formula injection u textu pocházejícího od studentů.
    if ($value !== '' && preg_match('/^[=+\-@\t\r]/u', $value) === 1) {
        return "'" . $value;
    }
    return $value;
}

// Learning journey / gamification -------------------------------------------
function learning_profile_key(string $classId): string
{
    $student = trim((string)($_SESSION['student_label'] ?? ''));
    $user = auth_user();
    if ($user && $student !== '') {
        // Kanonický profil studenta je navázaný na jeho přiřazenou třídu + profil,
        // nikoli na poskytovatele přihlášení. Google a lokální účet tak sdílejí progress.
        return $classId . ':s:' . substr(hash('sha256', $classId . '|' . normalized_person_name($student)), 0, 24);
    }
    if ($user && ($user['provider'] ?? '') === 'google' && !empty($user['sub'])) {
        return $classId . ':g:' . substr(hash('sha256', (string)$user['sub']), 0, 24);
    }
    if ($user && ($user['provider'] ?? '') === 'local' && !empty($user['id'])) {
        return $classId . ':l:' . substr(hash('sha256', (string)$user['id']), 0, 24);
    }
    $student = $student !== '' ? $student : 'anonymous';
    return $classId . ':n:' . substr(hash('sha256', strtolower($student)), 0, 20);
}

function learning_legacy_profile_keys(string $classId): array
{
    $keys = [];
    $user = auth_user();
    if ($user && ($user['provider'] ?? '') === 'google' && !empty($user['sub'])) {
        $keys[] = $classId . ':g:' . substr(hash('sha256', (string)$user['sub']), 0, 24);
    }
    if ($user && ($user['provider'] ?? '') === 'local' && !empty($user['id'])) {
        $keys[] = $classId . ':l:' . substr(hash('sha256', (string)$user['id']), 0, 24);
    }
    $student = trim((string)($_SESSION['student_label'] ?? ''));
    if ($student !== '') $keys[] = $classId . ':n:' . substr(hash('sha256', strtolower($student)), 0, 20);
    return array_values(array_unique($keys));
}

/*
 * v58 (Z2): profil (XP, odznaky, postup) je v úložišti zdrojem pravdy. Kopie v $_SESSION je jen zrcadlo
 * (a úložiště pro nepřihlášené). Každý profil nese 'version'; zápis přes learning_save_profile() přenese
 * do čerstvých dat JEN změny, které volající udělal proti verzi, kterou dostal (tříbodové sloučení),
 * a learning_award_once() mění pod zámkem jen události + XP daného profilu. Změny od učitele nebo
 * z jiného zařízení se tak nepřepíšou a zastaralá kopie v session se po zápisu obnoví z úložiště.
 */
function learning_profiles_path(): string
{
    return STORAGE_DIR . '/learning_profiles.json.php';
}

function learning_profile_default(): array
{
    return ['xp'=>0,'events'=>[],'kb'=>[],'studio'=>[],'journey'=>[],'badges'=>[],'achievements'=>[],'version'=>0,'updated_at'=>date(DATE_ATOM)];
}

/** Zapamatuje si verzi profilu, kterou dostal volající (výchozí kopie pro pozdější sloučení). */
function learning_profile_remember(string $key, array $profile): array
{
    $profile['version'] = (int)($profile['version'] ?? 0);
    $bases = $GLOBALS['educanet_profile_bases'][$key] ?? [];
    $bases[$profile['version']] = $profile;
    if (count($bases) > 6) $bases = array_slice($bases, -6, null, true);
    $GLOBALS['educanet_profile_bases'][$key] = $bases;
    return $profile;
}

/** Profil z úložiště (bez zápisu); null = neexistuje. */
function learning_profile_stored(string $key): ?array
{
    $row = storage_read(learning_profiles_path())[$key] ?? null;
    return is_array($row) ? learning_profile_remember($key, $row) : null;
}

/**
 * Uloží profil libovolného klíče sloučením: do aktuálního stavu v úložišti se přenesou jen změny proti
 * verzi, ze které volající vycházel. Beze změn se nic nezapisuje. Vrací uložený (čerstvý) profil.
 */
function learning_profile_commit(string $key, array $profile): array
{
    $ignore = ['updated_at', 'version'];
    $version = (int)($profile['version'] ?? 0);
    $base = $GLOBALS['educanet_profile_bases'][$key][$version] ?? null;
    if (is_array($base) && storage_changes_empty($base, $profile, $ignore)) {
        return learning_profile_stored($key) ?? learning_profile_remember($key, $base);
    }
    $saved = null;
    storage_update(learning_profiles_path(), static function (array $all) use ($key, $profile, $base, $ignore, &$saved): array {
        $exists = is_array($all[$key] ?? null);
        $fresh = $exists ? $all[$key] : learning_profile_default();
        $merged = is_array($base)
            ? storage_merge_changes($fresh, $base, $profile, ['xp'], $ignore)
            : array_replace($fresh, array_diff_key($profile, array_flip($ignore)));
        if ($exists && storage_changes_empty($fresh, $merged, $ignore)) {
            $saved = $fresh;
            return $all;
        }
        $merged['version'] = (int)($fresh['version'] ?? 0) + 1;
        $merged['updated_at'] = date(DATE_ATOM);
        $all[$key] = $merged;
        $saved = $merged;
        return $all;
    });
    return learning_profile_remember($key, (array)$saved);
}

function learning_profile(string $classId): array
{
    $memoKey = learning_profile_key($classId) . '|' . (auth_is_signed_in() ? 's' : 'a');
    $memo = storage_request_memo_enabled() ? ($GLOBALS['educanet_learning_profile_memo'][$memoKey] ?? null) : null;
    if (is_array($memo) && $memo['epoch'] === storage_epoch()) return $memo['profile'];
    $profile = learning_profile_load($classId);
    $GLOBALS['educanet_learning_profile_memo'][$memoKey] = ['epoch' => storage_epoch(), 'profile' => $profile];
    return $profile;
}

/** Načtení profilu bez paměti požadavku (learning_profile() ho obaluje memoizací podle epochy zápisů). */
function learning_profile_load(string $classId): array
{
    if (!isset($_SESSION['learning_profiles']) || !is_array($_SESSION['learning_profiles'])) $_SESSION['learning_profiles'] = [];
    $key = learning_profile_key($classId);
    if (auth_is_signed_in()) {
        $stored = learning_profile_stored($key);
        if ($stored === null) {
            // Jednorázová kompatibilní migrace ze staršího klíče poskytovatele (pod zámkem, nic nepřepíše).
            $all = storage_read(learning_profiles_path());
            foreach (learning_legacy_profile_keys($classId) as $legacyKey) {
                if (!is_array($all[$legacyKey] ?? null)) continue;
                $row = storage_map_update(learning_profiles_path(), $key, static function (?array $current) use ($legacyKey): ?array {
                    return $current ?? (storage_read(learning_profiles_path())[$legacyKey] ?? null);
                });
                if (is_array($row)) $stored = learning_profile_remember($key, $row);
                break;
            }
        }
        if ($stored !== null) {
            $_SESSION['learning_profiles'][$key] = $stored;
            return $stored;
        }
    }
    if (!isset($_SESSION['learning_profiles'][$key]) || !is_array($_SESSION['learning_profiles'][$key])) {
        $_SESSION['learning_profiles'][$key] = learning_profile_default();
    }
    return learning_profile_remember($key, $_SESSION['learning_profiles'][$key]);
}

function learning_save_profile(string $classId, array $profile): void
{
    unset($GLOBALS['educanet_learning_profile_memo'], $GLOBALS['educanet_learning_refresh_memo']);
    if (!isset($_SESSION['learning_profiles']) || !is_array($_SESSION['learning_profiles'])) $_SESSION['learning_profiles'] = [];
    $key = learning_profile_key($classId);
    if (!auth_is_signed_in()) {
        $profile['updated_at'] = date(DATE_ATOM);
        $profile['version'] = (int)($profile['version'] ?? 0) + 1;
        $_SESSION['learning_profiles'][$key] = learning_profile_remember($key, $profile);
        return;
    }
    $_SESSION['learning_profiles'][$key] = learning_profile_commit($key, $profile);
}

/** Udělí XP za událost nejvýš jednou. Přihlášený žák: pod zámkem se mění jen events + xp jeho profilu. */
function learning_award_once(string $classId, string $eventKey, int $xp): bool
{
    $profile = learning_profile($classId);
    $key = learning_profile_key($classId);
    $xp = max(0, $xp);
    if (!auth_is_signed_in()) {
        if (isset($profile['events'][$eventKey])) return false;
        $profile['events'] = (is_array($profile['events'] ?? null) ? $profile['events'] : []) + [$eventKey => ['xp' => $xp, 'at' => date(DATE_ATOM)]];
        $profile['xp'] = max(0, (int)($profile['xp'] ?? 0) + $xp);
        learning_save_profile($classId, $profile);
        return true;
    }
    $awarded = false;
    $row = storage_map_update(learning_profiles_path(), $key, static function (?array $current) use ($eventKey, $xp, &$awarded): array {
        $p = $current ?? learning_profile_default();
        $events = is_array($p['events'] ?? null) ? $p['events'] : [];
        if (isset($events[$eventKey])) return $p;
        $events[$eventKey] = ['xp' => $xp, 'at' => date(DATE_ATOM)];
        $p['events'] = $events;
        $p['xp'] = max(0, (int)($p['xp'] ?? 0) + $xp);
        $p['version'] = (int)($p['version'] ?? 0) + 1;
        $p['updated_at'] = date(DATE_ATOM);
        $awarded = true;
        return $p;
    });
    $_SESSION['learning_profiles'][$key] = learning_profile_remember($key, (array)$row);
    return $awarded;
}

function learning_kb_progress(string $classId, string $topic): array
{
    $profile = learning_profile($classId);
    $kb = is_array($profile['kb'] ?? null) ? $profile['kb'] : [];
    $row = is_array($kb[$topic] ?? null) ? $kb[$topic] : [];
    return array_replace([
        'visual' => false,
        'simulation' => false,
        'steps' => false,
        'deep' => false,
        'check' => false,
        'complete' => false,
    ], $row);
}

function learning_set_kb_step(string $classId, string $topic, string $step, bool $value = true): array
{
    $allowed = ['visual', 'simulation', 'steps', 'deep', 'check', 'complete'];
    if (!in_array($step, $allowed, true)) {
        return learning_kb_progress($classId, $topic);
    }
    $profile = learning_profile($classId);
    if (!isset($profile['kb']) || !is_array($profile['kb'])) {
        $profile['kb'] = [];
    }
    $current = learning_kb_progress($classId, $topic);
    $current[$step] = $value;
    $profile['kb'][$topic] = $current;
    learning_save_profile($classId, $profile);
    return $current;
}

function learning_kb_complete(string $classId, string $topic, bool $hasSimulation): bool
{
    $p = learning_kb_progress($classId, $topic);
    $required = ['visual', 'steps', 'deep', 'check'];
    if ($hasSimulation) {
        $required[] = 'simulation';
    }
    foreach ($required as $key) {
        if (empty($p[$key])) {
            return false;
        }
    }
    return true;
}

function learning_first_incomplete_topic(string $classId, array $module, array $simulationMap): ?string
{
    foreach (array_keys($module['knowledgebase'] ?? []) as $topic) {
        $hasSimulation = isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
        if (!learning_kb_complete($classId, (string)$topic, $hasSimulation)) {
            return (string)$topic;
        }
    }
    return null;
}

function learning_set_studio_step(string $classId, string $step, bool $value = true): array
{
    $profile = learning_profile($classId);
    if (!isset($profile['studio']) || !is_array($profile['studio'])) {
        $profile['studio'] = [];
    }
    $profile['studio'][$step] = $value;
    learning_save_profile($classId, $profile);
    return $profile['studio'];
}

function learning_studio_required_steps(): array
{
    return [
        'brief', 'visual', 'export',
        'dimensions', 'grid', 'type', 'palette', 'handoff_export', 'check', 'compare',
    ];
}

function learning_studio_complete(string $classId): bool
{
    $profile = learning_profile($classId);
    $studio = is_array($profile['studio'] ?? null) ? $profile['studio'] : [];
    foreach (learning_studio_required_steps() as $step) {
        if (empty($studio[$step])) {
            return false;
        }
    }
    return true;
}

function learning_level(int $xp): array
{
    $xp = max(0, $xp);
    $size = 250;
    $level = intdiv($xp, $size) + 1;
    $inLevel = $xp % $size;
    return [
        'level' => $level,
        'current' => $inLevel,
        'next' => $size,
        'percent' => (int)round(($inLevel / $size) * 100),
    ];
}

function learning_badge_definitions(): array
{
    // Badge = vzácná trofej. Běžné studijní milníky patří do achievementů níže.
    $defs = [
        'exam_perfect' => [
            'title' => trm('Bezchybný test'), 'mark' => '100', 'rarity' => 'legendary',
            'text' => trm('Dokončen celý startovní test bez jediné chyby.'),
            'condition' => trm('100 % správných odpovědí v celém startovním testu.'),
        ],
        'challenge_distinction' => [
            'title' => trm('Challenge Distinction'), 'mark' => '★', 'rarity' => 'epic',
            'text' => trm('Dobrovolná rozšiřující challenge byla ohodnocena známkou 1.'),
            'condition' => trm('Získej jedničku z dobrovolné Extra challenge.'),
        ],
        'project_masterpiece' => [
            'title' => trm('Masterpiece'), 'mark' => '◆', 'rarity' => 'epic',
            'text' => trm('Publikované projektové hodnocení alespoň 95 % se známkou 1.'),
            'condition' => trm('Projekt alespoň 95 % bodů a výsledná známka 1.'),
        ],
        'triple_distinction' => [
            'title' => trm('Triple Distinction'), 'mark' => 'III', 'rarity' => 'legendary',
            'text' => trm('Tři publikované projekty za 1 s výsledkem alespoň 90 %.'),
            'condition' => trm('3 výborné projektové výsledky (≥90 %, známka 1).'),
        ],
        'knowledge_grandmaster' => [
            'title' => trm('Knowledge Grandmaster'), 'mark' => '◇', 'rarity' => 'epic',
            'text' => trm('Dokončena celá Knowledge Base aktuálního ročníku.'),
            'condition' => trm('Dokonči všechny Knowledge materiály aktuálního ročníku.'),
        ],
        'course_mastery' => [
            'title' => trm('Course Mastery'), 'mark' => 'IX', 'rarity' => 'epic',
            'text' => trm('Dokončena celá 28lekcová strukturovaná učební cesta aktuálního ročníku.'),
            'condition' => trm('Dokonči lekce 1–28 aktuálního ročníku.'),
        ],
        'prestige_exam_certified' => ['title' => trm('Prestige Certified'),'mark'=>'CERT','rarity'=>'epic','text' => trm('Úspěšně dokončena první prestižní certifikační zkouška.'),'condition' => trm('Splň libovolnou prestižní zkoušku.')],
        'prestige_exam_perfect' => ['title' => trm('Perfect Certification'),'mark'=>'100','rarity'=>'legendary','text' => trm('Prestižní zkouška dokončena bez jediné chyby.'),'condition' => trm('Získej 100 % v libovolné prestižní zkoušce.')],
        'prestige_exam_double' => ['title' => trm('Double Certified'),'mark'=>'II','rarity'=>'legendary','text' => trm('Úspěšně dokončeny dvě různé prestižní zkoušky.'),'condition' => trm('Splň dvě odlišné prestižní zkoušky.')],
    ];

    $levelNames = [
        10=>trm('Momentum'),20=>trm('Specialista'),30=>trm('Expert'),40=>trm('Master'),50=>trm('Elite'),
        60=>trm('Architekt'),70=>trm('Mentor'),80=>trm('Vanguard'),90=>trm('Legenda'),100=>trm('EDUCANET Icon'),
    ];
    for ($level = 10; $level <= 200; $level += 10) {
        $name = $levelNames[$level] ?? tr('Level {level} Milestone', ['level' => $level]);
        $rarity = $level >= 100 ? 'mythic' : ($level >= 50 ? 'legendary' : 'epic');
        $defs['level_' . $level] = [
            'title' => $name,
            'mark' => (string)$level,
            'rarity' => $rarity,
            'text' => tr('Dosažen level {level} dlouhodobým studiem.', ['level' => $level]),
            'condition' => tr('Dosáhni levelu {level}.', ['level' => $level]),
        ];
    }
    if (function_exists('skill_badge_definitions')) $defs = array_replace($defs, skill_badge_definitions());
    if (function_exists('project_badge_definitions')) $defs = array_replace($defs, project_badge_definitions());
    return $defs;
}

function learning_achievement_definitions(): array
{
    return array_replace([
        'xp_250' => ['title' => trm('Rozjezd'),'mark'=>'250','text' => trm('Získej prvních 250 XP.'),'target'=>250,'kind'=>'xp'],
        'xp_1000' => ['title' => trm('Tisícovka'),'mark'=>'1K','text' => trm('Nasbírej 1 000 XP.'),'target'=>1000,'kind'=>'xp'],
        'xp_2500' => ['title' => trm('Tah na branku'),'mark'=>'2.5K','text' => trm('Nasbírej 2 500 XP.'),'target'=>2500,'kind'=>'xp'],
        'xp_5000' => ['title' => trm('Vytrvalec'),'mark'=>'5K','text' => trm('Nasbírej 5 000 XP.'),'target'=>5000,'kind'=>'xp'],
        'kb_5' => ['title' => trm('Znalostní start'),'mark'=>'05','text' => trm('Dokonči 5 Knowledge lekcí.'),'target'=>5,'kind'=>'kb'],
        'kb_15' => ['title' => trm('Knowledge Explorer'),'mark'=>'15','text' => trm('Dokonči 15 Knowledge lekcí.'),'target'=>15,'kind'=>'kb'],
        'kb_30' => ['title' => trm('Deep Learner'),'mark'=>'30','text' => trm('Dokonči 30 Knowledge lekcí.'),'target'=>30,'kind'=>'kb'],
        'sim_5' => ['title' => trm('Experimentátor'),'mark'=>'◎5','text' => trm('Dokonči 5 interaktivních simulací.'),'target'=>5,'kind'=>'sim'],
        'sim_15' => ['title' => trm('Lab Regular'),'mark'=>'◎15','text' => trm('Dokonči 15 interaktivních simulací.'),'target'=>15,'kind'=>'sim'],
        'active_5' => ['title' => trm('Pět aktivních dnů'),'mark'=>'5D','text' => trm('Studuj v pěti různých dnech.'),'target'=>5,'kind'=>'days'],
        'active_10' => ['title' => trm('Studijní rytmus'),'mark'=>'10D','text' => trm('Studuj v deseti různých dnech.'),'target'=>10,'kind'=>'days'],
        'active_20' => ['title' => trm('Konzistence'),'mark'=>'20D','text' => trm('Studuj ve dvaceti různých dnech.'),'target'=>20,'kind'=>'days'],
        'diagnostic_done' => ['title' => trm('Diagnostika hotová'),'mark'=>'✓','text' => trm('Dokonči celý startovní test.'),'target'=>1,'kind'=>'event','event'=>'test_complete'],
        'project_first' => ['title' => trm('První hodnocený projekt'),'mark'=>'P1','text' => trm('Získej první publikované projektové hodnocení.'),'target'=>1,'kind'=>'projects'],
        'project_three' => ['title' => trm('Projektová série'),'mark'=>'P3','text' => trm('Získej tři publikovaná projektová hodnocení.'),'target'=>3,'kind'=>'projects'],
        'project_excellent' => ['title' => trm('Výborný projekt'),'mark'=>'A+','text' => trm('Získej z projektu známku 1.'),'target'=>1,'kind'=>'excellent_projects'],
        'course_3' => ['title' => trm('Tři lekce za tebou'),'mark'=>'L3','text' => trm('Dokonči tři navazující lekce.'),'target'=>3,'kind'=>'lessons'],
        'course_6' => ['title' => trm('První třetina'),'mark'=>'L6','text' => trm('Dokonči šest navazujících lekcí.'),'target'=>6,'kind'=>'lessons'],
        'course_9' => ['title' => trm('První polovina kurzu'),'mark'=>'L9','text' => trm('Dokonči devět lekcí a uzavři první polovinu cesty.'),'target'=>9,'kind'=>'lessons'],
        'course_12' => ['title' => trm('Druhá etapa'),'mark'=>'L12','text' => trm('Dokonči dvanáct lekcí.'),'target'=>12,'kind'=>'lessons'],
        'course_18' => ['title' => trm('Core Path'),'mark'=>'L18','text' => trm('Dokonči prvních osmnáct lekcí ročníku.'),'target'=>18,'kind'=>'lessons'],
        'course_24' => ['title' => trm('Deep Path'),'mark'=>'L24','text' => trm('Dokonči dvacet čtyři strukturovaných lekcí.'),'target'=>24,'kind'=>'lessons'],
        'course_28' => ['title' => trm('Celá cesta'),'mark'=>'L28','text' => trm('Dokonči všech 28 strukturovaných lekcí ročníku.'),'target'=>28,'kind'=>'lessons'],
        'friend_first' => ['title' => trm('Study Buddy'),'mark'=>'F1','text' => trm('Propoj se s prvním spolužákem v rámci třídy.'),'target'=>1,'kind'=>'friends'],
        'friend_five' => ['title' => trm('Studijní kruh'),'mark'=>'F5','text' => trm('Měj pět vzájemně potvrzených propojení se spolužáky.'),'target'=>5,'kind'=>'friends'],
        'team_first' => ['title' => trm('Team Player'),'mark'=>'T1','text' => trm('Staň se členem prvního uzamčeného projektového týmu.'),'target'=>1,'kind'=>'teams'],
        'team_founder' => ['title' => trm('Team Builder'),'mark'=>'TB','text' => trm('Založ lobby, které se promění v oficiální projektový tým.'),'target'=>1,'kind'=>'teams_founded'],
    ], function_exists('skill_achievement_definitions') ? skill_achievement_definitions() : [], function_exists('project_achievement_definitions') ? project_achievement_definitions() : []);
}

function learning_project_result_summary(string $classId): array
{
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    if ($label === '') return ['count'=>0,'excellent'=>0,'masterpiece'=>0];
    $rows = project_student_results($classId, $label);
    $excellent = 0; $masterpiece = 0;
    foreach ($rows as $row) {
        $max = max(1, (int)($row['max_points'] ?? 0));
        $points = max(0, (int)($row['points'] ?? 0));
        $ratio = $points / $max;
        if ((int)($row['grade'] ?? 5) === 1) $excellent++;
        if ((int)($row['grade'] ?? 5) === 1 && $ratio >= .95) $masterpiece++;
    }
    return ['count'=>count($rows),'excellent'=>$excellent,'masterpiece'=>$masterpiece];
}

function learning_course_completed_count(string $classId): int
{
    $count = learning_primary_block_complete($classId) ? 1 : 0;
    if (!function_exists('runtime_content_load_classes') && is_file(__DIR__ . '/runtime_content.php')) require_once __DIR__ . '/runtime_content.php';
    if (function_exists('runtime_content_load_classes')) {
        $runtime = runtime_content_load_classes([$classId]);
        $nextLessons = (array)($runtime['nextLessons'] ?? []);
        $extended = (array)($runtime['extendedLessons'] ?? []);
    } else {
        $nextLessons = require __DIR__ . '/next_lessons.php';
        $extended = require __DIR__ . '/extended_lessons.php';
    }
    if (isset($nextLessons[$classId]) && is_array($nextLessons[$classId]) && learning_next_lesson_complete($classId, $nextLessons[$classId])) $count++;
    foreach ((array)($extended[$classId] ?? []) as $lesson) {
        if (is_array($lesson) && learning_course_lesson_complete($classId,$lesson)) $count++;
    }
    return $count;
}

function learning_progress_metrics(string $classId, array $module, array $simulationMap = []): array
{
    $profile = learning_profile($classId);
    $kb = is_array($profile['kb'] ?? null) ? $profile['kb'] : [];
    $kbDone = 0; $simDone = 0;
    foreach ((array)($module['knowledgebase'] ?? []) as $topic => $_article) {
        $hasSimulation = isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
        if (learning_kb_complete($classId,(string)$topic,$hasSimulation)) $kbDone++;
        if (!empty($kb[$topic]['simulation'])) $simDone++;
    }
    $projects = learning_project_result_summary($classId);
    $extraDistinction = 0;
    foreach (learning_student_rows(STORAGE_DIR . '/extra_submissions.json.php',$classId,100) as $submission) {
        if (extra_submission_grade($submission) === 1) $extraDistinction++;
    }
    return [
        'xp'=>max(0,(int)($profile['xp'] ?? 0)),
        'kb'=>$kbDone,
        'kb_total'=>count((array)($module['knowledgebase'] ?? [])),
        'sim'=>$simDone,
        'days'=>learning_activity_days($profile),
        'projects'=>(int)$projects['count'],
        'excellent_projects'=>(int)$projects['excellent'],
        'masterpiece_projects'=>(int)$projects['masterpiece'],
        'extra_distinction'=>$extraDistinction,
        'lessons'=>learning_course_completed_count($classId),
        'friends'=>student_friend_count($classId),
        'teams'=>student_formed_team_count($classId),
        'teams_founded'=>student_founded_team_count($classId),
        'special_exams'=>special_exam_passed_count($classId),
        'special_exam_perfect'=>special_exam_perfect_count($classId),
        'event_test_complete'=>isset(($profile['events'] ?? [])['test_complete']) ? 1 : 0,
    ];
}

/**
 * v61: obnova odznaků/úspěchů je idempotentní – dokud tento požadavek nic nezapsal (storage_epoch()) a předchozí běh sám
 * nic nezměnil, další volání (hlavička, přehled, widgety) nic nového nenajde, takže se přeskočí.
 * Běh, který něco zapsal, se nepamatuje (příští volání ověří stav znovu).
 */
function learning_refresh_once(string $name, string $classId, array $module, array $simulationMap, callable $run): array
{
    if (!storage_request_memo_enabled()) return $run();
    $key = $name . '|' . $classId . '|' . count($simulationMap) . '|' . count($module);
    $epoch = storage_epoch();
    if (($GLOBALS['educanet_learning_refresh_memo'][$key] ?? null) === $epoch) return [];
    $new = $run();
    if (storage_epoch() === $epoch) $GLOBALS['educanet_learning_refresh_memo'][$key] = $epoch;
    return $new;
}

function learning_refresh_achievements(string $classId, array $module, array $simulationMap = []): array
{
    return learning_refresh_once('ach', $classId, $module, $simulationMap, static fn(): array => learning_refresh_achievements_run($classId, $module, $simulationMap));
}

function learning_refresh_achievements_run(string $classId, array $module, array $simulationMap): array
{
    $profile = learning_profile($classId);
    $earned = is_array($profile['achievements'] ?? null) ? $profile['achievements'] : [];
    $new = [];
    $m = learning_progress_metrics($classId,$module,$simulationMap);
    foreach (learning_achievement_definitions() as $id => $def) {
        $kind = (string)($def['kind'] ?? '');
        $value = match ($kind) {
            'xp' => (int)$m['xp'], 'kb' => (int)$m['kb'], 'sim' => (int)$m['sim'],
            'days' => (int)$m['days'], 'projects' => (int)$m['projects'],
            'excellent_projects' => (int)$m['excellent_projects'], 'lessons' => (int)$m['lessons'],
            'friends' => (int)$m['friends'], 'teams' => (int)$m['teams'], 'teams_founded' => (int)$m['teams_founded'], 'special_exams' => (int)$m['special_exams'],
            'event' => !empty($def['event']) && isset(($profile['events'] ?? [])[(string)$def['event']]) ? 1 : 0,
            'skill_dynamic' => function_exists('skill_achievement_value') ? skill_achievement_value($classId,$id) : 0,
            'project_dynamic' => function_exists('project_achievement_value') ? project_achievement_value($classId,$id) : 0,
            default => 0,
        };
        if ($value >= (int)($def['target'] ?? 1) && empty($earned[$id])) {
            $earned[$id] = ['earned_at'=>date(DATE_ATOM)];
            $new[] = $id;
        }
    }
    $profile['achievements']=$earned;
    learning_save_profile($classId,$profile);
    return $new;
}

function learning_achievement_progress(string $classId, array $module, array $simulationMap = []): array
{
    $m = learning_progress_metrics($classId,$module,$simulationMap);
    $profile = learning_profile($classId);
    $out=[];
    foreach (learning_achievement_definitions() as $id=>$def) {
        $kind=(string)($def['kind']??'');
        $current = match ($kind) {
            'xp'=>(int)$m['xp'],'kb'=>(int)$m['kb'],'sim'=>(int)$m['sim'],'days'=>(int)$m['days'],
            'projects'=>(int)$m['projects'],'excellent_projects'=>(int)$m['excellent_projects'],'lessons'=>(int)$m['lessons'],
            'friends'=>(int)$m['friends'],'teams'=>(int)$m['teams'],'teams_founded'=>(int)$m['teams_founded'],'special_exams'=>(int)$m['special_exams'],
            'event'=>!empty($def['event']) && isset(($profile['events']??[])[(string)$def['event']]) ? 1 : 0,
            'skill_dynamic'=>function_exists('skill_achievement_value') ? skill_achievement_value($classId,$id) : 0,
            'project_dynamic'=>function_exists('project_achievement_value') ? project_achievement_value($classId,$id) : 0,
            default=>0,
        };
        $target=max(1,(int)($def['target']??1));
        $out[$id]=['current'=>min($current,$target),'target'=>$target,'percent'=>(int)round(min(1,$current/$target)*100),'earned'=>isset(($profile['achievements']??[])[$id])];
    }
    return $out;
}

function learning_refresh_badges(string $classId, array $module, array $simulationMap = []): array
{
    return learning_refresh_once('badges', $classId, $module, $simulationMap, static fn(): array => learning_refresh_badges_run($classId, $module, $simulationMap));
}

function learning_refresh_badges_run(string $classId, array $module, array $simulationMap): array
{
    $profile = learning_profile($classId);
    $badges = is_array($profile['badges'] ?? null) ? $profile['badges'] : [];
    $badges = array_intersect_key($badges, learning_badge_definitions()); // migrace: staré běžné badge se už nepočítají jako trofeje
    $new = [];
    $events = is_array($profile['events'] ?? null) ? $profile['events'] : [];
    $metrics = learning_progress_metrics($classId,$module,$simulationMap);
    $correctCount = count(array_filter(array_keys($events), static fn($k) => str_starts_with((string)$k,'test_correct:')));
    $questionCount = count((array)($module['test'] ?? []));

    $rules = [
        'exam_perfect' => $questionCount > 0 && isset($events['test_complete']) && $correctCount >= $questionCount,
        'challenge_distinction' => (int)$metrics['extra_distinction'] >= 1,
        'project_masterpiece' => (int)$metrics['masterpiece_projects'] >= 1,
        'triple_distinction' => (int)$metrics['excellent_projects'] >= 3,
        'knowledge_grandmaster' => (int)$metrics['kb_total'] > 0 && (int)$metrics['kb'] >= (int)$metrics['kb_total'],
        'course_mastery' => (int)$metrics['lessons'] >= 28,
        'prestige_exam_certified' => (int)$metrics['special_exams'] >= 1,
        'prestige_exam_perfect' => (int)$metrics['special_exam_perfect'] >= 1,
        'prestige_exam_double' => (int)$metrics['special_exams'] >= 2,
    ];
    $level = (int)(learning_level((int)($profile['xp'] ?? 0))['level'] ?? 1);
    for ($milestone=10; $milestone<=200; $milestone+=10) $rules['level_'.$milestone] = $level >= $milestone;

    foreach ($rules as $id=>$isEarned) {
        if ($isEarned && empty($badges[$id])) {
            $badges[$id]=['earned_at'=>date(DATE_ATOM)];
            $new[]=$id;
        }
    }
    $profile['badges']=$badges;
    learning_save_profile($classId,$profile);
    learning_refresh_achievements($classId,$module,$simulationMap);
    return $new;
}

function learning_public_state(string $classId, array $module, array $simulationMap = []): array
{
    $newAchievements = learning_refresh_achievements($classId,$module,$simulationMap);
    learning_refresh_badges($classId, $module, $simulationMap);
    $profile = learning_profile($classId);
    $xp = (int)($profile['xp'] ?? 0);
    return [
        'xp' => $xp,
        'level' => learning_level($xp),
        'badges' => array_keys(is_array($profile['badges'] ?? null) ? $profile['badges'] : []),
        'badge_definitions' => learning_badge_definitions(),
        'achievements' => array_keys(is_array($profile['achievements'] ?? null) ? $profile['achievements'] : []),
        'achievement_definitions' => learning_achievement_definitions(),
        'achievement_progress' => learning_achievement_progress($classId,$module,$simulationMap),
        'new_achievements' => $newAchievements,
        'kb' => is_array($profile['kb'] ?? null) ? $profile['kb'] : [],
        'studio' => is_array($profile['studio'] ?? null) ? $profile['studio'] : [],
        'journey' => is_array($profile['journey'] ?? null) ? $profile['journey'] : [],
    ];
}

function learning_graphics_core_topics(): array
{
    return ['hierarchy', 'composition', 'contrast-color'];
}

function learning_graphics_core_complete(string $classId, array $simulationMap = []): bool
{
    foreach (learning_graphics_core_topics() as $topic) {
        $hasSimulation = isset($simulationMap[$topic]) && is_array($simulationMap[$topic]);
        if (!learning_kb_complete($classId, $topic, $hasSimulation)) {
            return false;
        }
    }
    return true;
}

function learning_set_journey_step(string $classId, string $step, bool $value = true): array
{
    $profile = learning_profile($classId);
    if (!isset($profile['journey']) || !is_array($profile['journey'])) {
        $profile['journey'] = [];
    }
    $profile['journey'][$step] = $value;
    learning_save_profile($classId, $profile);
    return $profile['journey'];
}

function learning_journey_step_done(string $classId, string $step): bool
{
    $profile = learning_profile($classId);
    return !empty($profile['journey'][$step]);
}


function learning_primary_block_complete(string $classId): bool
{
    $profile = learning_profile($classId);
    $events = is_array($profile['events'] ?? null) ? $profile['events'] : [];
    if (in_array($classId, ['class_1a', 'class_2a'], true)) {
        // Lekce 1 je hotová až po skutečném odevzdání Canva/grafického výstupu.
        return isset($events['graphics:submit']);
    }
    return isset($events['practice:complete']) || (!empty($_SESSION['next_practice_result']) && (($_SESSION['next_practice_result']['class_id'] ?? null) === $classId));
}

function learning_next_lesson_progress(string $classId, string $lessonId, array $steps): array
{
    $profile = learning_profile($classId);
    $journey = is_array($profile['journey'] ?? null) ? $profile['journey'] : [];
    $out = [];
    foreach ($steps as $step) {
        if (!is_array($step) || empty($step['id'])) continue;
        $id = (string)$step['id'];
        $out[$id] = !empty($journey['next:' . $lessonId . ':' . $id]);
    }
    return $out;
}

function learning_next_lesson_complete(string $classId, array $lesson): bool
{
    $lessonId = (string)($lesson['id'] ?? 'next');
    $steps = is_array($lesson['steps'] ?? null) ? $lesson['steps'] : [];
    if (!$steps) return false;
    $progress = learning_next_lesson_progress($classId, $lessonId, $steps);
    foreach ($steps as $step) {
        if (!is_array($step) || empty($step['id'])) continue;
        if (empty($progress[(string)$step['id']])) return false;
    }
    return true;
}

function learning_course_lesson_progress(string $classId, array $lesson): array
{
    $lessonId = (string)($lesson['id'] ?? 'lesson');
    $profile = learning_profile($classId);
    $journey = is_array($profile['journey'] ?? null) ? $profile['journey'] : [];
    $out = [];
    foreach (($lesson['steps'] ?? []) as $step) {
        if (!is_array($step) || empty($step['id'])) continue;
        $out[(string)$step['id']] = !empty($journey['course:' . $lessonId . ':' . (string)$step['id']]);
    }
    return $out;
}

function learning_course_lesson_complete(string $classId, array $lesson): bool
{
    $steps = is_array($lesson['steps'] ?? null) ? $lesson['steps'] : [];
    if (!$steps) return false;
    $progress = learning_course_lesson_progress($classId, $lesson);
    foreach ($steps as $step) {
        if (!is_array($step) || empty($step['id'])) continue;
        if (empty($progress[(string)$step['id']])) return false;
    }
    return true;
}

function learning_course_lesson_unlocked(string $classId, int $number, array $nextLessons, array $extendedLessons): bool
{
    if ($number <= 1) return true;
    if ($number === 2) return learning_primary_block_complete($classId);
    if ($number === 3) {
        $l2 = $nextLessons[$classId] ?? null;
        return is_array($l2) && learning_next_lesson_complete($classId, $l2);
    }
    $extras = is_array($extendedLessons[$classId] ?? null) ? $extendedLessons[$classId] : [];
    $prev = null;
    foreach ($extras as $lesson) {
        if ((int)($lesson['number'] ?? 0) === $number - 1) { $prev = $lesson; break; }
    }
    return is_array($prev) && learning_course_lesson_complete($classId, $prev);
}
// Student dashboard analytics ------------------------------------------------
function learning_row_belongs_to_student(array $row, string $classId): bool
{
    if ((string)($row['class_id'] ?? '') !== $classId) return false;
    $user = auth_user();
    $rowAuth = trim((string)($row['auth_key'] ?? ''));
    if ($user && $rowAuth !== '' && hash_equals((string)($user['auth_key'] ?? ''), $rowAuth)) return true;
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    $rowLabel = trim((string)($row['student_label'] ?? ''));
    return $label !== '' && $rowLabel !== '' && normalized_person_name($label) === normalized_person_name($rowLabel);
}

function learning_student_rows(string $path, string $classId, int $limit = 20): array
{
    $rows = storage_rows($path); // v58: registrovaný proud (JSONL) i legacy soubor
    $matched = [];
    foreach ($rows as $row) {
        if (!is_array($row) || !learning_row_belongs_to_student($row, $classId)) continue;
        $matched[] = $row;
    }
    usort($matched, static function(array $a, array $b): int {
        $ta = strtotime((string)($a['finished_at'] ?? $a['submitted_at'] ?? $a['created_at'] ?? '')) ?: 0;
        $tb = strtotime((string)($b['finished_at'] ?? $b['submitted_at'] ?? $b['created_at'] ?? '')) ?: 0;
        return $tb <=> $ta;
    });
    return array_slice($matched, 0, max(1, $limit));
}

function learning_xp_timeline(array $profile, int $days = 14): array
{
    $days = max(7, min(60, $days));
    $today = new DateTimeImmutable('today');
    $daily = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $day = $today->modify('-' . $i . ' days')->format('Y-m-d');
        $daily[$day] = 0;
    }
    foreach ((array)($profile['events'] ?? []) as $event) {
        if (!is_array($event)) continue;
        $ts = strtotime((string)($event['at'] ?? ''));
        if (!$ts) continue;
        $day = date('Y-m-d', $ts);
        if (array_key_exists($day, $daily)) $daily[$day] += max(0, (int)($event['xp'] ?? 0));
    }
    $cumulativeBefore = max(0, (int)($profile['xp'] ?? 0) - array_sum($daily));
    $running = $cumulativeBefore;
    $out = [];
    foreach ($daily as $day => $earned) {
        $running += $earned;
        $out[] = ['date'=>$day,'earned'=>$earned,'total'=>$running];
    }
    return $out;
}

function learning_activity_days(array $profile): int
{
    $days = [];
    foreach ((array)($profile['events'] ?? []) as $event) {
        if (!is_array($event)) continue;
        $ts = strtotime((string)($event['at'] ?? ''));
        if ($ts) $days[date('Y-m-d', $ts)] = true;
    }
    return count($days);
}


// Manual project assessment -------------------------------------------------
function project_catalog(): array
{
    static $catalog = null;
    if (is_array($catalog)) return $catalog;
    $path = __DIR__ . '/project_assessments.php';
    $catalog = is_file($path) ? (require $path) : [];
    return is_array($catalog) ? $catalog : [];
}

function project_find(string $classId, string $projectId): ?array
{
    foreach ((array)(project_catalog()[$classId] ?? []) as $project) {
        if (is_array($project) && (string)($project['id'] ?? '') === $projectId) return $project;
    }
    return null;
}

function project_max_points(array $project): int
{
    $max = 0;
    foreach ((array)($project['rubric'] ?? []) as $criterion) {
        if (is_array($criterion)) $max += max(0, (int)($criterion['max'] ?? 0));
    }
    return max(1, $max);
}

function project_suggested_grade(int $points, int $maxPoints): int
{
    return extra_grade_from_points($points, $maxPoints) ?? 5;
}

function project_student_key(string $classId, string $label): string
{
    return $classId . ':student:' . substr(hash('sha256', $classId . '|' . normalized_person_name($label)), 0, 24);
}

/** E-maily demo účtů (náhled studentského rozhraní), které se nepočítají mezi žáky. */
function demo_account_emails(): array
{
    static $emails = null;
    if (is_array($emails)) return $emails;
    $emails = [];
    foreach (local_accounts() as $email => $account) {
        if (is_array($account) && !empty($account['demo_account'])) $emails[strtolower((string)$email)] = true;
    }
    return $emails;
}

function project_students_for_class(string $classId): array
{
    $cacheKey = 'project_students:' . $classId;
    if (isset($GLOBALS['educanet_runtime_indexes'][$cacheKey]) && is_array($GLOBALS['educanet_runtime_indexes'][$cacheKey])) {
        return $GLOBALS['educanet_runtime_indexes'][$cacheKey];
    }
    $students = [];
    foreach (student_directory() as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        if ($label === '') continue;
        $key = project_student_key($classId, $label);
        $students[$key] = [
            'key'=>$key,
            'class_id'=>$classId,
            'label'=>$label,
            'preferred_name'=>trim((string)($row['preferred_name'] ?? '')),
            'email'=>'',
            'seat_label'=>(string)($row['seat_label'] ?? ''),
        ];
    }
    $demoEmails = demo_account_emails();
    foreach (student_account_map() as $account) {
        if (!is_array($account) || (string)($account['class_id'] ?? '') !== $classId) continue;
        $label = trim((string)($account['student_label'] ?? ''));
        if ($label === '') continue;
        // Demo účet je náhled rozhraní, ne žák: nesmí zkreslovat počty třídy ani přehled odevzdání.
        if (isset($demoEmails[strtolower((string)($account['email'] ?? ''))])) continue;
        $key = project_student_key($classId, $label);
        if (!isset($students[$key])) {
            $students[$key] = ['key'=>$key,'class_id'=>$classId,'label'=>$label,'preferred_name'=>'','email'=>'','seat_label'=>''];
        }
        if (!empty($account['email'])) $students[$key]['email'] = (string)$account['email'];
    }
    uasort($students, static fn(array $a, array $b): int => strnatcasecmp((string)$a['label'], (string)$b['label']));
    $GLOBALS['educanet_runtime_indexes'][$cacheKey] = $students;
    return $students;
}

function project_groups(): array
{
    $rows = load_php_json(STORAGE_DIR . '/project_groups.json.php');
    return is_array($rows) ? $rows : [];
}

function project_group_indexes(): array
{
    $key = 'project_groups:index';
    if (isset($GLOBALS['educanet_runtime_indexes'][$key]) && is_array($GLOBALS['educanet_runtime_indexes'][$key])) {
        return $GLOBALS['educanet_runtime_indexes'][$key];
    }
    $byId = [];
    $byProject = [];
    foreach (project_groups() as $row) {
        if (!is_array($row)) continue;
        $id = (string)($row['id'] ?? '');
        $classId = (string)($row['class_id'] ?? '');
        $projectId = (string)($row['project_id'] ?? '');
        if ($id !== '') $byId[$id] = $row;
        if ($classId !== '' && $projectId !== '') $byProject[$classId . '|' . $projectId][] = $row;
    }
    return $GLOBALS['educanet_runtime_indexes'][$key] = ['by_id'=>$byId,'by_project'=>$byProject];
}

function project_group_find(string $groupId): ?array
{
    $index = project_group_indexes();
    $row = $index['by_id'][$groupId] ?? null;
    return is_array($row) ? $row : null;
}

function project_groups_for(string $classId, string $projectId): array
{
    $index = project_group_indexes();
    $rows = $index['by_project'][$classId . '|' . $projectId] ?? [];
    return is_array($rows) ? array_values($rows) : [];
}

function project_groups_path(): string
{
    return STORAGE_DIR . '/project_groups.json.php';
}

/** Čistá funkce: vloží/aktualizuje tým v seznamu. Vrací [nové řádky, záznam]. Volá se pod zámkem. */
function project_group_upsert(array $groups, array $group): array
{
    $id = trim((string)($group['id'] ?? ''));
    if ($id === '') $id = 'grp_' . bin2hex(random_bytes(6));
    $now = date(DATE_ATOM);
    $existingIndex = null;
    foreach ($groups as $i => $row) {
        if (is_array($row) && (string)($row['id'] ?? '') === $id) { $existingIndex = $i; break; }
    }
    $classId = (string)($group['class_id'] ?? '');
    $projectId = (string)($group['project_id'] ?? '');
    $memberKeys = array_values(array_unique(array_filter(array_map('strval', (array)($group['member_keys'] ?? [])))));
    if (count($memberKeys) < 2) throw new RuntimeException(tr('Tým musí mít alespoň dva studenty.'));
    foreach ($groups as $existing) {
        if (!is_array($existing) || (string)($existing['id'] ?? '') === $id) continue;
        if ((string)($existing['class_id'] ?? '') !== $classId || (string)($existing['project_id'] ?? '') !== $projectId) continue;
        if (array_intersect($memberKeys, (array)($existing['member_keys'] ?? []))) {
            throw new RuntimeException(tr('Student může být v jednom projektu pouze v jednom týmu. Upravte členství existujícího týmu.'));
        }
    }
    $record = [
        'id'=>$id,
        'class_id'=>$classId,
        'project_id'=>$projectId,
        'name'=>u_substr(trim((string)($group['name'] ?? 'Tým')), 0, 100),
        'member_keys'=>$memberKeys,
        'created_at'=>$existingIndex === null ? $now : (string)($groups[$existingIndex]['created_at'] ?? $now),
        'updated_at'=>$now,
    ];
    if ($existingIndex === null) $groups[] = $record; else $groups[$existingIndex] = $record;
    return [$groups, $record];
}

function project_save_group(array $group): array
{
    $record = [];
    storage_update(project_groups_path(), static function (array $groups) use ($group, &$record): array {
        [$groups, $record] = project_group_upsert($groups, $group);
        return $groups;
    });
    if (function_exists('project_workspace_sync_group_members')) project_workspace_sync_group_members((string)$record['id'],(array)$record['member_keys'],teacher_export_authenticated()?teacher_display_name():'system');
    return $record;
}

function project_delete_group(string $groupId): void
{
    if(function_exists('project_tasks_for_group')){
        $hasWorkspace = project_tasks_for_group($groupId) || project_roles_for_group($groupId,false) || project_qa_for_group($groupId) || project_role_evaluations_for_group($groupId) || project_self_retro_for_group($groupId) || project_peer_feedback_for_group($groupId);
        if($hasWorkspace) throw new RuntimeException(tr('Tým už má historii v Project Workspace. Kvůli auditu jej nemažte; upravte členství nebo vytvořte nový tým.'));
    }
    storage_update(project_groups_path(), static fn(array $groups): array => array_values(array_filter($groups, static fn($row): bool => !is_array($row) || (string)($row['id'] ?? '') !== $groupId)));
}

// Student social profiles, friendships and project lobby system ----------------
function social_profiles(): array
{
    $rows = load_php_json(STORAGE_DIR . '/student_profiles.json.php');
    return is_array($rows) ? $rows : [];
}

function social_student_key(string $classId, string $label): string
{
    return project_student_key($classId, $label);
}

function social_current_student_key(string $classId): string
{
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    return $label !== '' ? social_student_key($classId, $label) : '';
}

function social_student_exists(string $classId, string $studentKey): bool
{
    return isset(project_students_for_class($classId)[$studentKey]);
}

function social_profile_default(string $classId, string $studentKey): array
{
    $students = project_students_for_class($classId);
    $student = $students[$studentKey] ?? [];
    return [
        'class_id'=>$classId,
        'student_key'=>$studentKey,
        'headline'=>'',
        'bio'=>'',
        'skills'=>[],
        'interests'=>[],
        'preferred_role'=>'flexible',
        'team_status'=>'available',
        'featured_badges'=>[],
        'featured_skills'=>[],
        'updated_at'=>null,
        'label'=>(string)($student['label'] ?? ''),
    ];
}

function social_profile_get(string $classId, string $studentKey): array
{
    $base = social_profile_default($classId, $studentKey);
    $all = social_profiles();
    $row = is_array($all[$studentKey] ?? null) ? $all[$studentKey] : [];
    return array_replace($base, $row, ['label'=>$base['label']]);
}

function social_clean_tags($raw, int $max = 8): array
{
    if (is_string($raw)) $raw = preg_split('/[,;\n]+/u', $raw) ?: [];
    if (!is_array($raw)) return [];
    $out = [];
    foreach ($raw as $tag) {
        $tag = trim(u_substr((string)$tag, 0, 32));
        if ($tag === '') continue;
        $key = function_exists("mb_strtolower") ? mb_strtolower($tag, "UTF-8") : strtolower($tag);
        if (!isset($out[$key])) $out[$key] = $tag;
        if (count($out) >= $max) break;
    }
    return array_values($out);
}

function social_profile_save(string $classId, string $studentKey, array $input): array
{
    if ($studentKey === '' || !social_student_exists($classId, $studentKey)) throw new RuntimeException(tr('Studentský profil nebyl nalezen.'));
    if (!hash_equals(social_current_student_key($classId), $studentKey)) throw new RuntimeException(tr('Můžeš upravovat pouze svůj profil.'));
    $roles = ['flexible','leader','designer','researcher','developer','presenter','qa','tester','coordinator','documentarian'];
    $statuses = ['available','ask_me','full'];
    $role = (string)($input['preferred_role'] ?? 'flexible');
    $status = (string)($input['team_status'] ?? 'available');
    if (!in_array($role, $roles, true)) $role = 'flexible';
    if (!in_array($status, $statuses, true)) $status = 'available';
    $profile = social_profile_get($classId, $studentKey);
    $featured = array_values(array_unique(array_map('strval', (array)($input['featured_badges'] ?? []))));
    $earned = array_keys((array)(learning_profile($classId)['badges'] ?? []));
    $featured = array_slice(array_values(array_intersect($featured, $earned)), 0, 3);
    $featuredSkills = array_values(array_unique(array_map('strval', (array)($input['featured_skills'] ?? []))));
    $skillProgress = function_exists('skill_progress_map') ? skill_progress_map($classId, skill_current_student_key($classId)) : [];
    $featuredSkills = array_values(array_filter($featuredSkills, static fn($slug):bool => isset($skillProgress[$slug]) && (float)($skillProgress[$slug]['mastery_percent'] ?? 0) >= 60));
    $featuredSkills = array_slice($featuredSkills, 0, 5);
    $profile = array_replace($profile, [
        'headline'=>u_substr(trim((string)($input['headline'] ?? '')), 0, 80),
        'bio'=>u_substr(trim((string)($input['bio'] ?? '')), 0, 320),
        'skills'=>social_clean_tags($input['skills'] ?? [], 8),
        'interests'=>social_clean_tags($input['interests'] ?? [], 8),
        'preferred_role'=>$role,
        'team_status'=>$status,
        'featured_badges'=>$featured,
        'featured_skills'=>$featuredSkills,
        'updated_at'=>date(DATE_ATOM),
    ]);
    // Pod zámkem se přepíší jen pole z formuláře – ostatní (i souběžně změněná) pole profilu zůstanou.
    $fields = array_intersect_key($profile, array_flip(['headline','bio','skills','interests','preferred_role','team_status','featured_badges','featured_skills','updated_at']));
    return (array)storage_map_update(STORAGE_DIR . '/student_profiles.json.php', $studentKey, static fn(?array $current): array => array_replace($current ?? $profile, $fields));
}

function learning_profile_snapshot_for_student(string $classId, string $label): array
{
    $key = $classId . ':s:' . substr(hash('sha256', $classId . '|' . normalized_person_name($label)), 0, 24);
    $all = load_php_json(STORAGE_DIR . '/learning_profiles.json.php');
    $row = is_array($all[$key] ?? null) ? $all[$key] : [];
    $xp = max(0, (int)($row['xp'] ?? 0));
    return [
        'xp'=>$xp,
        'level'=>learning_level($xp),
        'badge_ids'=>array_keys((array)($row['badges'] ?? [])),
        'achievement_count'=>count((array)($row['achievements'] ?? [])),
    ];
}

function friendships(): array
{
    $rows = load_php_json(STORAGE_DIR . '/friendships.json.php');
    return is_array($rows) ? $rows : [];
}

function friendship_id(string $classId, string $a, string $b): string
{
    $pair = [$a,$b]; sort($pair, SORT_STRING);
    return 'fr_' . substr(hash('sha256', $classId . '|' . implode('|',$pair)), 0, 24);
}

function friendship_between(string $classId, string $a, string $b): ?array
{
    $id = friendship_id($classId,$a,$b);
    $all = friendships();
    return is_array($all[$id] ?? null) ? $all[$id] : null;
}

function friendships_path(): string
{
    return STORAGE_DIR . '/friendships.json.php';
}

function friendship_request(string $classId, string $from, string $to): void
{
    if ($from === '' || $to === '' || $from === $to) throw new RuntimeException(tr('Neplatná žádost o propojení.'));
    if (!social_student_exists($classId,$from) || !social_student_exists($classId,$to)) throw new RuntimeException(tr('Propojit lze pouze spolužáky ze stejné třídy.'));
    $id = friendship_id($classId,$from,$to);
    storage_map_update(friendships_path(), $id, static function (?array $existing) use ($id, $classId, $from, $to): array {
        if (is_array($existing) && (string)($existing['status'] ?? '') === 'accepted') return $existing;
        if (is_array($existing) && (string)($existing['status'] ?? '') === 'pending' && (string)($existing['requester_key'] ?? '') !== $from) {
            $existing['status']='accepted'; $existing['accepted_at']=date(DATE_ATOM); $existing['updated_at']=date(DATE_ATOM);
            return $existing;
        }
        return ['id'=>$id,'class_id'=>$classId,'requester_key'=>$from,'addressee_key'=>$to,'status'=>'pending','created_at'=>date(DATE_ATOM),'updated_at'=>date(DATE_ATOM),'accepted_at'=>null];
    });
}

function friendship_respond(string $classId, string $studentKey, string $otherKey, bool $accept): void
{
    $id=friendship_id($classId,$studentKey,$otherKey);
    storage_map_update(friendships_path(), $id, static function (?array $row) use ($studentKey, $accept): ?array {
        if (!is_array($row) || (string)($row['status']??'')!=='pending' || (string)($row['addressee_key']??'')!==$studentKey) throw new RuntimeException(tr('Tato žádost už není dostupná.'));
        if (!$accept) return null;
        $row['status']='accepted'; $row['accepted_at']=date(DATE_ATOM); $row['updated_at']=date(DATE_ATOM);
        return $row;
    });
}

function friendship_remove(string $classId, string $studentKey, string $otherKey): void
{
    $id=friendship_id($classId,$studentKey,$otherKey);
    if (!is_array(friendships()[$id] ?? null)) return;
    storage_map_update(friendships_path(), $id, static function (?array $row) use ($studentKey): ?array {
        if (!is_array($row)) return null;
        if (!in_array($studentKey,[(string)($row['requester_key']??''),(string)($row['addressee_key']??'')],true)) throw new RuntimeException(tr('Toto propojení ti nepatří.'));
        return null;
    });
}

function student_friend_keys(string $classId, ?string $studentKey = null): array
{
    $studentKey = $studentKey ?: social_current_student_key($classId); if ($studentKey==='') return [];
    $out=[]; foreach(friendships() as $row){
        if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['status']??'')!=='accepted')continue;
        $a=(string)($row['requester_key']??'');$b=(string)($row['addressee_key']??'');
        if($a===$studentKey&&$b!=='')$out[]=$b; elseif($b===$studentKey&&$a!=='')$out[]=$a;
    }
    return array_values(array_unique($out));
}

function student_friend_count(string $classId, ?string $studentKey = null): int
{
    return count(student_friend_keys($classId,$studentKey));
}

function student_pending_friend_requests(string $classId, string $studentKey): array
{
    return array_values(array_filter(friendships(), static fn($row): bool => is_array($row) && (string)($row['class_id']??'')===$classId && (string)($row['status']??'')==='pending' && (string)($row['addressee_key']??'')===$studentKey));
}

function balanced_group_sizes(int $studentCount, int $groupCount): array
{
    if ($studentCount < 2 || $groupCount < 1 || $groupCount > intdiv($studentCount,2)) return [];
    $base=intdiv($studentCount,$groupCount); $extra=$studentCount%$groupCount;
    $sizes=[]; for($i=0;$i<$groupCount;$i++)$sizes[]=$base+($i<$extra?1:0);
    sort($sizes); return $sizes;
}

function project_group_size_plans(string $classId): array
{
    $n=count(project_students_for_class($classId)); if($n<2)return [];
    $plans=[]; $seen=[];
    foreach([4,3,5,2] as $target){
        $groups=max(1,(int)round($n/$target)); $groups=min($groups,intdiv($n,2));
        for($delta=0;$delta<=2;$delta++){
            foreach(array_unique([$groups-$delta,$groups+$delta]) as $gc){
                $sizes=balanced_group_sizes($n,(int)$gc); if(!$sizes||min($sizes)<2||max($sizes)>5)continue;
                $sig=implode('-',$sizes); if(isset($seen[$sig]))continue; $seen[$sig]=true;
                $spread=max($sizes)-min($sizes); $avg=array_sum($sizes)/count($sizes);
                $score=abs($avg-3.7)*10+$spread*3+abs(count($sizes)-($n/3.7));
                $plans[]=['id'=>'plan_'.str_replace('-','_',$sig),'sizes'=>$sizes,'groups'=>count($sizes),'students'=>$n,'score'=>$score,'label'=>implode(' + ',array_map(static fn(int $x):string=>(string)$x,$sizes))];
            }
        }
    }
    usort($plans,static fn(array $a,array $b):int=>$a['score']<=>$b['score']);
    $plans=array_slice($plans,0,3); foreach($plans as $i=>&$p)$p['recommended']=$i===0; unset($p);
    return $plans;
}

function team_plan_votes(): array { $r=load_php_json(STORAGE_DIR.'/team_plan_votes.json.php'); return is_array($r)?$r:[]; }
function team_plan_vote(string $classId,string $projectId,string $studentKey,string $planId): void
{
    $plans=project_group_size_plans($classId); $valid=array_column($plans,'id');
    if(!in_array($planId,$valid,true)||!social_student_exists($classId,$studentKey))throw new RuntimeException(tr('Neplatná varianta rozdělení.'));
    $key=$classId.'|'.$projectId.'|'.$studentKey;
    storage_map_update(STORAGE_DIR.'/team_plan_votes.json.php',$key,static fn(?array $current): array => ['class_id'=>$classId,'project_id'=>$projectId,'student_key'=>$studentKey,'plan_id'=>$planId,'updated_at'=>date(DATE_ATOM)]);
}
function team_plan_vote_summary(string $classId,string $projectId): array
{
    $out=[]; foreach(team_plan_votes() as $row){if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['project_id']??'')!==$projectId)continue;$pid=(string)($row['plan_id']??'');$out[$pid]=($out[$pid]??0)+1;} return $out;
}

function team_lobbies(): array { $r=load_php_json(STORAGE_DIR.'/project_lobbies.json.php'); return is_array($r)?$r:[]; }
function team_lobby_find(string $id): ?array { foreach(team_lobbies() as $r)if(is_array($r)&&(string)($r['id']??'')===$id)return $r;return null; }
function team_lobbies_for(string $classId,string $projectId): array { return array_values(array_filter(team_lobbies(),static fn($r):bool=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['project_id']??'')===$projectId)); }
function team_lobby_code(): string
{
    $alphabet='ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do{$code='';for($i=0;$i<6;$i++)$code.=$alphabet[random_int(0,strlen($alphabet)-1)];$used=false;foreach(team_lobbies() as $r){if(is_array($r)&&strtoupper((string)($r['invite_code']??''))===$code&&in_array((string)($r['status']??''),['open','ready'],true)){$used=true;break;}}}while($used);
    return $code;
}
function project_group_for_student(string $classId,string $projectId,string $studentKey): ?array
{
    foreach(project_groups_for($classId,$projectId) as $g)if(in_array($studentKey,(array)($g['member_keys']??[]),true))return $g;return null;
}
function team_active_lobby_for_student(string $classId,string $projectId,string $studentKey): ?array
{
    foreach(team_lobbies_for($classId,$projectId) as $l)if(in_array((string)($l['status']??''),['open','ready'],true)&&in_array($studentKey,(array)($l['member_keys']??[]),true))return $l;return null;
}
function team_lobbies_path(): string { return STORAGE_DIR.'/project_lobbies.json.php'; }
/** RMW seznamu lobby pod jedním zámkem: $fn(array $rows): array. */
function team_lobbies_update(callable $fn): array { return storage_update(team_lobbies_path(),static fn(array $rows): array => array_values($fn($rows))); }
function team_lobby_create(string $classId,string $projectId,string $ownerKey,string $name,int $capacity): array
{
    $project=project_find($classId,$projectId); if(!$project||(string)($project['type']??'')!=='group')throw new RuntimeException(tr('Tento projekt není skupinový.'));
    if(!social_student_exists($classId,$ownerKey))throw new RuntimeException(tr('Student nebyl nalezen.'));
    if(project_group_for_student($classId,$projectId,$ownerKey)||team_active_lobby_for_student($classId,$projectId,$ownerKey))throw new RuntimeException(tr('U tohoto projektu už jsi v týmu nebo aktivním lobby.'));
    $allowed=[];foreach(project_group_size_plans($classId) as $p)foreach((array)$p['sizes'] as $sz)$allowed[(int)$sz]=true;
    if(!$allowed)$allowed=[2=>true]; if(!isset($allowed[$capacity]))throw new RuntimeException(tr('Vyber jednu z doporučených velikostí týmu.'));
    $l=[];
    team_lobbies_update(static function(array $rows) use($classId,$projectId,$ownerKey,$name,$capacity,&$l): array {
        foreach($rows as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['project_id']??'')===$projectId&&in_array((string)($r['status']??''),['open','ready'],true)&&in_array($ownerKey,(array)($r['member_keys']??[]),true))throw new RuntimeException(tr('U tohoto projektu už jsi v týmu nebo aktivním lobby.'));
        $l=['id'=>'lob_'.bin2hex(random_bytes(6)),'class_id'=>$classId,'project_id'=>$projectId,'owner_key'=>$ownerKey,'name'=>u_substr(trim($name)!==''?trim($name):'Nový tým',0,80),'invite_code'=>team_lobby_code(),'capacity'=>$capacity,'member_keys'=>[$ownerKey],'status'=>'open','group_id'=>null,'created_at'=>date(DATE_ATOM),'updated_at'=>date(DATE_ATOM)];
        $rows[]=$l;return $rows;
    });
    return $l;
}
function team_lobby_join_code(string $classId,string $projectId,string $studentKey,string $code): array
{
    $code=strtoupper(trim($code)); if($code==='')throw new RuntimeException(tr('Zadej invite kód.'));
    if(project_group_for_student($classId,$projectId,$studentKey)||team_active_lobby_for_student($classId,$projectId,$studentKey))throw new RuntimeException(tr('U tohoto projektu už jsi v týmu nebo lobby.'));
    $joined=null;
    team_lobbies_update(static function(array $rows) use($classId,$projectId,$studentKey,$code,&$joined): array {
        foreach($rows as $i=>$l){
            if(!is_array($l)||(string)($l['class_id']??'')!==$classId||(string)($l['project_id']??'')!==$projectId||strtoupper((string)($l['invite_code']??''))!==$code)continue;
            if(!in_array((string)($l['status']??''),['open','ready'],true))throw new RuntimeException(tr('Toto lobby už je uzavřené.'));
            $members=array_values(array_unique(array_map('strval',(array)($l['member_keys']??[])))); if(in_array($studentKey,$members,true))throw new RuntimeException(tr('U tohoto projektu už jsi v týmu nebo lobby.')); if(count($members)>=(int)($l['capacity']??2))throw new RuntimeException(tr('Lobby je plné.'));
            $members[]=$studentKey;$l['member_keys']=$members;$l['status']=count($members)>=(int)$l['capacity']?'ready':'open';$l['updated_at']=date(DATE_ATOM);$rows[$i]=$l;$joined=$l;return $rows;
        }
        throw new RuntimeException(tr('Invite kód nebyl nalezen pro tento projekt a třídu.'));
    });
    return (array)$joined;
}
function team_lobby_leave(string $classId,string $lobbyId,string $studentKey): void
{
    team_lobbies_update(static function(array $rows) use($classId,$lobbyId,$studentKey): array {
        foreach($rows as $i=>$l){if(!is_array($l)||(string)($l['id']??'')!==$lobbyId)continue;if((string)($l['class_id']??'')!==$classId||!in_array($studentKey,(array)($l['member_keys']??[]),true))throw new RuntimeException(tr('Do tohoto lobby nepatříš.'));
            $members=array_values(array_filter(array_map('strval',(array)$l['member_keys']),static fn(string $k):bool=>$k!==$studentKey));
            if(!$members){array_splice($rows,$i,1);}else{$l['member_keys']=$members;if((string)$l['owner_key']===$studentKey)$l['owner_key']=$members[0];$l['status']='open';$l['updated_at']=date(DATE_ATOM);$rows[$i]=$l;}return $rows;}
        throw new RuntimeException(tr('Lobby už neexistuje.'));
    });
}
function team_lobby_regenerate_code(string $classId,string $lobbyId,string $studentKey): array
{
    $out=null;
    team_lobbies_update(static function(array $rows) use($classId,$lobbyId,$studentKey,&$out): array {
        foreach($rows as $i=>$l){if(!is_array($l)||(string)($l['id']??'')!==$lobbyId)continue;if((string)($l['class_id']??'')!==$classId||(string)($l['owner_key']??'')!==$studentKey)throw new RuntimeException(tr('Invite kód může změnit jen zakladatel lobby.'));$l['invite_code']=team_lobby_code();$l['updated_at']=date(DATE_ATOM);$rows[$i]=$l;$out=$l;return $rows;}
        throw new RuntimeException(tr('Lobby nebylo nalezeno.'));
    });
    return (array)$out;
}
/** Uzamčení lobby a založení týmu jsou jedna atomická operace nad oběma soubory (storage_update_many). */
function team_lobby_finalize(string $classId,string $lobbyId,string $studentKey): array
{
    $lobbyPath=team_lobbies_path();$groupPath=project_groups_path();$group=[];
    storage_update_many([$lobbyPath,$groupPath],static function(array $data) use($classId,$lobbyId,$studentKey,$lobbyPath,$groupPath,&$group): array {
        $rows=array_values($data[$lobbyPath]);
        foreach($rows as $i=>$l){if(!is_array($l)||(string)($l['id']??'')!==$lobbyId)continue;if((string)($l['class_id']??'')!==$classId||(string)($l['owner_key']??'')!==$studentKey)throw new RuntimeException(tr('Tým může uzamknout jen zakladatel lobby.'));$members=array_values(array_unique(array_map('strval',(array)($l['member_keys']??[]))));if(count($members)<2)throw new RuntimeException(tr('Pro tým jsou potřeba alespoň dva studenti.'));$capacity=(int)($l['capacity']??2);if(count($members)!==$capacity)throw new RuntimeException(tr('Před uzamčením naplň zvolenou kapacitu týmu ({members} / {capacity}).', ['members' => count($members), 'capacity' => $capacity]));
            [$groups,$group]=project_group_upsert($data[$groupPath],['class_id'=>$classId,'project_id'=>(string)$l['project_id'],'name'=>(string)$l['name'],'member_keys'=>$members]);
            $l['status']='formed';$l['group_id']=$group['id'];$l['updated_at']=date(DATE_ATOM);$rows[$i]=$l;
            return [$lobbyPath=>$rows,$groupPath=>$groups];}
        throw new RuntimeException(tr('Lobby nebylo nalezeno.'));
    });
    if(function_exists('project_workspace_sync_group_members'))project_workspace_sync_group_members((string)$group['id'],(array)$group['member_keys'],'system');
    return $group;
}
function student_formed_team_count(string $classId, ?string $studentKey=null): int
{
    $studentKey=$studentKey?:social_current_student_key($classId);if($studentKey==='')return 0;$n=0;foreach(project_groups() as $g)if(is_array($g)&&(string)($g['class_id']??'')===$classId&&in_array($studentKey,(array)($g['member_keys']??[]),true))$n++;return $n;
}
function student_founded_team_count(string $classId, ?string $studentKey=null): int
{
    $studentKey=$studentKey?:social_current_student_key($classId);if($studentKey==='')return 0;$n=0;foreach(team_lobbies() as $l)if(is_array($l)&&(string)($l['class_id']??'')===$classId&&(string)($l['owner_key']??'')===$studentKey&&(string)($l['status']??'')==='formed')$n++;return $n;
}

// Prestige assessments ------------------------------------------------------
function special_assessment_catalog(): array
{
    static $catalog=null;if(is_array($catalog))return $catalog;$path=__DIR__.'/special_assessments.php';$catalog=is_file($path)?(require $path):[];return is_array($catalog)?$catalog:[];
}
function special_assessments_for(string $classId): array { return array_values(array_filter((array)(special_assessment_catalog()[$classId]??[]),'is_array')); }
function special_assessment_find(string $classId,string $examId): ?array { foreach(special_assessments_for($classId) as $e)if((string)($e['id']??'')===$examId)return $e;return null; }
function special_exam_results(): array { return storage_stream_rows('special_exam_results'); }
function special_exam_student_results(string $classId): array
{
    $label=trim((string)($_SESSION['student_label']??''));$auth=(string)(auth_user()['auth_key']??'');$out=[];foreach(special_exam_results() as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId)continue;if($auth!==''&&(string)($r['auth_key']??'')===$auth){$out[]=$r;continue;}if($label!==''&&normalized_person_name((string)($r['student_label']??''))===normalized_person_name($label))$out[]=$r;}return $out;
}
function special_exam_best_map(string $classId): array
{
    $best=[];foreach(special_exam_student_results($classId) as $r){$id=(string)($r['exam_id']??'');if($id==='')continue;if(!isset($best[$id])||(int)($r['score']??0)>(int)($best[$id]['score']??0))$best[$id]=$r;}return $best;
}
function special_exam_passed_count(string $classId): int { $n=0;foreach(special_exam_best_map($classId) as $r)if(!empty($r['passed']))$n++;return $n; }
function special_exam_perfect_count(string $classId): int { $n=0;foreach(special_exam_best_map($classId) as $r)if((int)($r['score']??0)>0&&(int)($r['score']??0)===(int)($r['max_score']??-1))$n++;return $n; }
function special_exam_submit(string $classId,string $examId,array $answers): array
{
    $exam=special_assessment_find($classId,$examId);if(!$exam)throw new RuntimeException('Prestižní zkouška nebyla nalezena.');$level=(int)(learning_level((int)(learning_profile($classId)['xp']??0))['level']??1);if($level<(int)($exam['unlock_level']??10))throw new RuntimeException('Tato zkouška ještě není odemčená.');
    $questions=(array)($exam['questions']??[]);$score=0;foreach($questions as $i=>$q){if(!is_array($q))continue;$answer=isset($answers[$i])&&is_numeric($answers[$i])?(int)$answers[$i]:-1;if($answer===(int)($q['correct']??-2))$score++;}
    $max=max(1,count($questions));$passScore=max(1,(int)($exam['pass_score']??(int)ceil($max*.8)));$passed=$score>=$passScore;$row=['id'=>'pex_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'exam_id'=>$examId,'student_label'=>(string)($_SESSION['student_label']??''),'auth_key'=>(string)(auth_user()['auth_key']??''),'score'=>$score,'max_score'=>$max,'passed'=>$passed,'attempted_at'=>date(DATE_ATOM)];storage_append('special_exam_results',$row);if($passed)learning_award_once($classId,'prestige_exam:'.$examId.':pass',150);if($score===$max)learning_award_once($classId,'prestige_exam:'.$examId.':perfect',75);return $row;
}


function project_grade_records(): array
{
    $rows = load_php_json(STORAGE_DIR . '/project_grades.json.php');
    return is_array($rows) ? $rows : [];
}

function project_grade_indexes(): array
{
    $key = 'project_grades:index';
    if (isset($GLOBALS['educanet_runtime_indexes'][$key]) && is_array($GLOBALS['educanet_runtime_indexes'][$key])) {
        return $GLOBALS['educanet_runtime_indexes'][$key];
    }
    $byId = [];
    $byClass = [];
    $byProject = [];
    $statusCounts = [];
    foreach (project_grade_records() as $row) {
        if (!is_array($row)) continue;
        $id = (string)($row['id'] ?? '');
        $classId = (string)($row['class_id'] ?? '');
        $projectId = (string)($row['project_id'] ?? '');
        $status = (string)($row['status'] ?? 'draft');
        if ($id !== '') $byId[$id] = $row;
        if ($classId !== '') {
            $byClass[$classId][] = $row;
            $statusCounts[$classId][$status] = (int)($statusCounts[$classId][$status] ?? 0) + 1;
        }
        if ($classId !== '' && $projectId !== '') $byProject[$classId . '|' . $projectId][] = $row;
    }
    return $GLOBALS['educanet_runtime_indexes'][$key] = [
        'by_id'=>$byId,
        'by_class'=>$byClass,
        'by_project'=>$byProject,
        'status_counts'=>$statusCounts,
    ];
}

function project_grade_records_for_class(string $classId): array
{
    $index = project_grade_indexes();
    $rows = $index['by_class'][$classId] ?? [];
    return is_array($rows) ? array_values($rows) : [];
}

function project_grade_records_for_project(string $classId, string $projectId): array
{
    $index = project_grade_indexes();
    $rows = $index['by_project'][$classId . '|' . $projectId] ?? [];
    return is_array($rows) ? array_values($rows) : [];
}

function project_grade_status_counts(string $classId): array
{
    $index = project_grade_indexes();
    $counts = is_array($index['status_counts'][$classId] ?? null) ? $index['status_counts'][$classId] : [];
    return [
        'published'=>(int)($counts['published'] ?? 0),
        'draft'=>(int)($counts['draft'] ?? 0),
        'returned'=>(int)($counts['returned'] ?? 0),
    ];
}

function project_grade_record_id(string $classId, string $projectId, string $targetType, string $targetId): string
{
    return 'pgr_' . substr(hash('sha256', implode('|', [$classId,$projectId,$targetType,$targetId])), 0, 24);
}

function project_grade_find(string $classId, string $projectId, string $targetType, string $targetId): ?array
{
    $id = project_grade_record_id($classId, $projectId, $targetType, $targetId);
    $index = project_grade_indexes();
    $row = $index['by_id'][$id] ?? null;
    return is_array($row) ? $row : null;
}

function teacher_display_name(): string
{
    // v59: v režimu účtů jméno vždy z účtu (přejmenování adminem platí hned).
    $teacher59Account = function_exists('teacher59_current') ? teacher59_current() : null;
    if ($teacher59Account !== null) return (string)$teacher59Account['display_name'];
    $name = trim((string)($_SESSION['teacher_display_name'] ?? ''));
    if ($name !== '') return $name;
    $configured = educanet_secret('teacher_name');
    return $configured !== '' ? $configured : 'Učitel';
}

function project_grade_diff(array $before, array $after): array
{
    $watch = ['rubric_scores','points','max_points','suggested_grade','grade','grade_overridden','status','teacher_comment','strengths','next_step','private_note','member_adjustments'];
    $changes = [];
    foreach ($watch as $key) {
        $old = $before[$key] ?? null;
        $new = $after[$key] ?? null;
        if ($old !== $new) $changes[$key] = ['from'=>$old,'to'=>$new];
    }
    return $changes;
}

function project_grade_history_row(string $recordId, string $action, array $before, array $after): array
{
    return [
        'id'=>'hist_' . bin2hex(random_bytes(7)),
        'record_id'=>$recordId,
        'action'=>$action,
        'teacher'=>teacher_display_name(),
        'at'=>date(DATE_ATOM),
        'changes'=>project_grade_diff($before, $after),
        'snapshot'=>$after,
    ];
}

function project_append_history(string $recordId, string $action, array $before, array $after): void
{
    storage_list_push(STORAGE_DIR . '/project_grade_history.json.php', project_grade_history_row($recordId, $action, $before, $after));
}

function project_grade_history(string $recordId): array
{
    $history = load_php_json(STORAGE_DIR . '/project_grade_history.json.php');
    if (!is_array($history)) return [];
    $rows = array_values(array_filter($history, static fn($row): bool => is_array($row) && (string)($row['record_id'] ?? '') === $recordId));
    usort($rows, static fn(array $a,array $b): int => strcmp((string)($b['at'] ?? ''), (string)($a['at'] ?? '')));
    return $rows;
}

function project_save_grade(array $input): array
{
    $classId = (string)($input['class_id'] ?? '');
    $projectId = (string)($input['project_id'] ?? '');
    $targetType = in_array((string)($input['target_type'] ?? ''), ['individual','group'], true) ? (string)$input['target_type'] : 'individual';
    $targetId = (string)($input['target_id'] ?? '');
    $project = project_find($classId, $projectId);
    if (!$project || $targetId === '') throw new RuntimeException('Neplatný projekt nebo cíl hodnocení.');
    if ((string)($project['type'] ?? '') !== $targetType) throw new RuntimeException('Typ hodnocení neodpovídá projektu.');

    $rubricScores = [];
    $points = 0;
    foreach ((array)($project['rubric'] ?? []) as $criterion) {
        if (!is_array($criterion)) continue;
        $cid = (string)($criterion['id'] ?? '');
        $max = max(0, (int)($criterion['max'] ?? 0));
        $score = max(0, min($max, (int)(($input['rubric_scores'] ?? [])[$cid] ?? 0)));
        $rubricScores[$cid] = $score;
        $points += $score;
    }
    $maxPoints = project_max_points($project);
    $suggested = project_suggested_grade($points, $maxPoints);
    $manualGrade = isset($input['grade']) && is_numeric($input['grade']) ? max(1,min(5,(int)$input['grade'])) : null;
    $grade = $manualGrade ?? $suggested;
    $id = project_grade_record_id($classId,$projectId,$targetType,$targetId);
    $now = date(DATE_ATOM);

    $memberAdjustments = [];
    if ($targetType === 'group') {
        $group = project_group_find($targetId);
        if (!$group || (string)($group['project_id'] ?? '') !== $projectId || (string)($group['class_id'] ?? '') !== $classId) throw new RuntimeException('Vybraný tým nepatří k projektu.');
        foreach ((array)($group['member_keys'] ?? []) as $memberKey) {
            $memberKey = (string)$memberKey;
            $raw = is_array(($input['member_adjustments'] ?? [])[$memberKey] ?? null) ? $input['member_adjustments'][$memberKey] : [];
            $override = isset($raw['grade_override']) && is_numeric($raw['grade_override']) ? max(1,min(5,(int)$raw['grade_override'])) : null;
            $memberAdjustments[$memberKey] = [
                'points_delta'=>max(-$maxPoints,min($maxPoints,(int)($raw['points_delta'] ?? 0))),
                'grade_override'=>$override,
                'comment'=>u_substr(trim((string)($raw['comment'] ?? '')),0,1200),
            ];
        }
    }

    $status = in_array((string)($input['status'] ?? ''), ['draft','published','returned'], true) ? (string)$input['status'] : 'draft';
    $gradesPath = STORAGE_DIR . '/project_grades.json.php';
    $historyPath = STORAGE_DIR . '/project_grade_history.json.php';
    $record = [];
    // Předchozí verze, nový záznam i historie vznikají pod jedním zámkem obou souborů (verze se nezdvojí).
    storage_update_many([$gradesPath, $historyPath], static function (array $data) use ($gradesPath, $historyPath, $id, $classId, $projectId, $targetType, $targetId, $rubricScores, $points, $maxPoints, $suggested, $grade, $manualGrade, $status, $input, $memberAdjustments, $now, &$record): array {
        $all = $data[$gradesPath];
        $before = [];
        $index = null;
        foreach ($all as $i => $row) {
            if (is_array($row) && (string)($row['id'] ?? '') === $id) { $before = $row; $index = $i; break; }
        }
        $record = [
            'id'=>$id,
            'class_id'=>$classId,
            'project_id'=>$projectId,
            'target_type'=>$targetType,
            'target_id'=>$targetId,
            'rubric_scores'=>$rubricScores,
            'points'=>$points,
            'max_points'=>$maxPoints,
            'suggested_grade'=>$suggested,
            'grade'=>$grade,
            'grade_overridden'=>$manualGrade !== null,
            'status'=>$status,
            'teacher_comment'=>u_substr(trim((string)($input['teacher_comment'] ?? '')),0,4000),
            'strengths'=>u_substr(trim((string)($input['strengths'] ?? '')),0,2000),
            'next_step'=>u_substr(trim((string)($input['next_step'] ?? '')),0,2000),
            'private_note'=>u_substr(trim((string)($input['private_note'] ?? '')),0,2000),
            'member_adjustments'=>$memberAdjustments,
            'teacher'=>teacher_display_name(),
            'created_at'=>(string)($before['created_at'] ?? $now),
            'updated_at'=>$now,
            'published_at'=>((string)($input['status'] ?? '') === 'published') ? ($before['published_at'] ?? $now) : ($before['published_at'] ?? null),
            'version'=>max(1,(int)($before['version'] ?? 0)+1),
        ];
        if ($index === null) $all[] = $record; else $all[$index] = $record;
        $history = $data[$historyPath];
        $history[] = project_grade_history_row($id, $before ? 'update' : 'create', $before, $record);
        return [$gradesPath => $all, $historyPath => $history];
    });
    return $record;
}

function project_student_results(string $classId, string $studentLabel): array
{
    $studentKey = project_student_key($classId,$studentLabel);
    $groups = project_groups();
    $groupMap = [];
    foreach ($groups as $group) {
        if (!is_array($group)) continue;
        if (in_array($studentKey,(array)($group['member_keys'] ?? []),true)) $groupMap[(string)$group['id']]=$group;
    }
    $out = [];
    foreach (project_grade_records() as $record) {
        if (!is_array($record) || (string)($record['class_id'] ?? '') !== $classId || !in_array((string)($record['status'] ?? ''), ['published','returned'], true)) continue;
        $isMine = ((string)($record['target_type'] ?? '') === 'individual' && (string)($record['target_id'] ?? '') === $studentKey)
            || ((string)($record['target_type'] ?? '') === 'group' && isset($groupMap[(string)($record['target_id'] ?? '')]));
        if (!$isMine) continue;
        $project = project_find($classId,(string)$record['project_id']);
        if (!$project) continue;
        $points = (int)($record['points'] ?? 0);
        $grade = (int)($record['grade'] ?? 5);
        $personalComment = '';
        $pointsDelta = 0;
        if ((string)$record['target_type'] === 'group') {
            $adjustment = is_array(($record['member_adjustments'] ?? [])[$studentKey] ?? null) ? $record['member_adjustments'][$studentKey] : [];
            $pointsDelta = (int)($adjustment['points_delta'] ?? 0);
            $points = max(0,min((int)$record['max_points'],$points + $pointsDelta));
            $grade = isset($adjustment['grade_override']) && is_numeric($adjustment['grade_override']) ? max(1,min(5,(int)$adjustment['grade_override'])) : ((bool)($record['grade_overridden'] ?? false) ? (int)$record['grade'] : project_suggested_grade($points,(int)$record['max_points']));
            $personalComment = trim((string)($adjustment['comment'] ?? ''));
        }
        $out[] = [
            'record'=>$record,
            'project'=>$project,
            'points'=>$points,
            'max_points'=>(int)$record['max_points'],
            'grade'=>$grade,
            'points_delta'=>$pointsDelta,
            'personal_comment'=>$personalComment,
            'group'=>((string)$record['target_type']==='group') ? ($groupMap[(string)$record['target_id']] ?? null) : null,
        ];
    }
    usort($out, static fn(array $a,array $b): int => strcmp((string)($b['record']['updated_at'] ?? ''),(string)($a['record']['updated_at'] ?? '')));
    return $out;
}


// Skill Trees & Mastery v25 --------------------------------------------------
require_once __DIR__ . '/skill_trees.php';
require_once __DIR__ . '/project_workspace.php';
require_once __DIR__ . '/adaptive_learning.php';
require_once __DIR__ . '/mastery_learning_v41.php';
require_once __DIR__ . '/adaptive_lesson_kits_v42.php';
require_once __DIR__ . '/cognitive_visualization_v43.php';
require_once __DIR__ . '/learning_studio_v44.php';
require_once __DIR__ . '/visual_simulation_v45.php';
require_once __DIR__ . '/visual_practical_learning_v48.php';
require_once __DIR__ . '/visual_labs_3a_v48_1.php';
require_once __DIR__ . '/teacher_tasks.php';
// v58 F6: registr identity (stabilní student_id, přechod školního roku).
require_once __DIR__ . '/identity_v58.php';
