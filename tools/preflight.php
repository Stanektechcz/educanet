<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v59 · tools/preflight.php
 *
 * Kontrola připravenosti prostředí na nasazení do produkce. Nikdy netiskne
 * hodnoty secretů ani data žáků – jen jestli jsou nastavené / jestli kontrola
 * prošla.
 *
 * Použití:
 *   php tools/preflight.php [--strict] [--php-ini=<soubor>] [--env-file=<soubor>]
 *
 * --strict      WARN se počítá jako chyba (exit 2), jinak jen FAIL vrací nenulový kód.
 * --php-ini=    Cesta k php.ini produkčního PHP-FPM poolu (CLI vidí jen své vlastní
 *               php.ini, ne FPM), použije se navíc k php_ini_loaded_file().
 * --env-file=   Cesta k souboru se ENV proměnnými ve tvaru NAME=hodnota (např.
 *               /etc/educanet.env), protože CLI běh nevidí prostředí FPM poolu.
 *
 * Výstup: řádky "PASS ...", "WARN ..." nebo "FAIL ...". Na konci souhrn a
 * exit 0 (vše OK, případně jen WARN bez --strict), 1 (aspoň jeden FAIL),
 * 2 (jen WARN, ale --strict).
 */

require_once dirname(__DIR__) . '/bootstrap.php';

$argv = $argv ?? [];

function pf_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) {
            return substr($a, strlen($name) + 3);
        }
    }
    return null;
}

function pf_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

/** Načte NAME=hodnota řádky (bez exportu do skutečného prostředí procesu) do pole. */
function pf_read_env_file(?string $path): array
{
    $out = [];
    if ($path === null || $path === '' || !is_file($path)) {
        return $out;
    }
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_starts_with($line, 'export ')) $line = trim(substr($line, 7));
        $eq = strpos($line, '=');
        if ($eq === false) continue;
        $key = trim(substr($line, 0, $eq));
        $val = trim(substr($line, $eq + 1));
        if (strlen($val) >= 2 && (($val[0] === '"' && str_ends_with($val, '"')) || ($val[0] === "'" && str_ends_with($val, "'")))) {
            $val = substr($val, 1, -1);
        }
        if ($key !== '') $out[$key] = $val;
    }
    return $out;
}

/** ENV proměnná: skutečné prostředí má přednost, jinak hodnota ze --env-file. */
function pf_env(string $name, array $fileEnv): ?string
{
    $value = getenv($name);
    if (is_string($value) && $value !== '') return $value;
    return $fileEnv[$name] ?? null;
}

$checks = 0;
$failed = 0;
$warned = 0;

function pf_pass(string $label, string $detail = ''): void
{
    global $checks;
    $checks++;
    echo 'PASS ' . $label . ($detail !== '' ? ' – ' . $detail : '') . "\n";
}

function pf_warn(string $label, string $detail = ''): void
{
    global $checks, $warned;
    $checks++;
    $warned++;
    echo 'WARN ' . $label . ($detail !== '' ? ' – ' . $detail : '') . "\n";
}

function pf_fail(string $label, string $detail = ''): void
{
    global $checks, $failed;
    $checks++;
    $failed++;
    echo 'FAIL ' . $label . ($detail !== '' ? ' – ' . $detail : '') . "\n";
}

function pf_check(bool $ok, string $label, string $detail = '', bool $warnOnly = false): void
{
    if ($ok) {
        pf_pass($label, $detail);
    } elseif ($warnOnly) {
        pf_warn($label, $detail);
    } else {
        pf_fail($label, $detail);
    }
}

$fileEnv = pf_read_env_file(pf_arg($argv, 'env-file'));
$phpIniArg = pf_arg($argv, 'php-ini');
$strict = pf_flag($argv, 'strict');

echo "EDUCANET · preflight produkce\n";
echo str_repeat('-', 60) . "\n";

// --- PHP verze a rozšíření --------------------------------------------------
pf_check(version_compare(PHP_VERSION, '8.1.0', '>='), 'PHP >= 8.1', PHP_VERSION);
foreach (['mbstring', 'json', 'session', 'fileinfo', 'hash'] as $ext) {
    pf_check(extension_loaded($ext), "PHP rozšíření $ext (povinné)");
}
$hasSodium = extension_loaded('sodium');
$hasOpensslGcm = extension_loaded('openssl') && in_array('aes-256-gcm', @openssl_get_cipher_methods() ?: [], true);
pf_check($hasSodium || $hasOpensslGcm, 'sodium nebo openssl s aes-256-gcm (šifrování e-mailů účtů)', $hasSodium ? 'sodium' : ($hasOpensslGcm ? 'openssl aes-256-gcm' : 'chybí obě'));
if (function_exists('google_auth_configured') && google_auth_configured()) {
    $hasCurl = extension_loaded('curl');
    $allowUrlFopen = filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);
    pf_check($hasCurl || $allowUrlFopen, 'curl nebo allow_url_fopen (Google přihlášení je nakonfigurované)', $hasCurl ? 'curl' : ($allowUrlFopen ? 'allow_url_fopen' : 'chybí obě'));
} else {
    pf_pass('Google přihlášení nenakonfigurováno – curl/allow_url_fopen se nevyžaduje');
}
foreach (['intl', 'apcu'] as $ext) {
    pf_check(extension_loaded($ext), "PHP rozšíření $ext (doporučené)", '', true);
}

// --- Zapisovatelné adresáře --------------------------------------------------
$root = dirname(__DIR__);
foreach (['storage' => STORAGE_DIR, 'uploads' => UPLOAD_DIR, 'cache/runtime' => $root . '/cache/runtime'] as $label => $dir) {
    pf_check(is_dir($dir) && is_writable($dir), "$label je adresář a je zapisovatelný", $dir);
}

// --- Režim učitelských účtů (v59) ---------------------------------------------
// Načteno už zde (ne až u sekce "Učitelské účty" níže), aby SEC59-05 mohlo rozlišit
// legacy mód (klíč je jediný způsob přihlášení/exportu) od accounts módu (klíč se
// pro login ani export už nepoužívá, takže "nenastaveno" je tam v pořádku).
if (!function_exists('teacher59_mode') && is_file($root . '/teacher_accounts_v59.php')) {
    require_once $root . '/teacher_accounts_v59.php';
}
$teacher59ModeEarly = function_exists('teacher59_mode') ? teacher59_mode() : null;

// --- Secrets mimo docroot -----------------------------------------------------
$secretsPath = (string)($GLOBALS['educanet_secrets_path'] ?? '');
$secretsConfiguredViaEnv = is_string(getenv('EDUCANET_TEACHER_EXPORT_KEY')) && trim((string)getenv('EDUCANET_TEACHER_EXPORT_KEY')) !== '';
if ($secretsPath !== '') {
    $realSecrets = realpath($secretsPath) ?: $secretsPath;
    $realRoot = realpath($root) ?: $root;
    $outsideDocroot = !str_starts_with(str_replace('\\', '/', $realSecrets), str_replace('\\', '/', $realRoot) . '/');
    pf_check($outsideDocroot, 'Soubor se secrety leží mimo webový docroot', $secretsPath);
} elseif ($secretsConfiguredViaEnv) {
    pf_pass('Secrets nastaveny přes environment (EDUCANET_TEACHER_EXPORT_KEY), soubor se secrety se nepoužívá');
} elseif ($teacher59ModeEarly === 'accounts') {
    // Accounts mód sdílený klíč nepoužívá, soubor se secrety tedy není povinný –
    // doporučený je kvůli otp_card_key (viz níže).
    pf_warn('Soubor se secrety nenalezen', 'accounts mód ho k přihlášení nepotřebuje; doporučený je kvůli otp_card_key – viz docs/NASAZENI_PRODUKCE.md §3');
} else {
    pf_fail('Nenalezen soubor se secrety ani EDUCANET_TEACHER_EXPORT_KEY v prostředí');
}

// --- otp_card_key: klíč šifrovaných kopií jednorázových hesel na kartičkách ----
// SEC58-10: bez něj si accounts_v58.php klíč vytvoří v storage/accounts_v58_key.json.php –
// funguje to, ale klíč pak leží vedle dat a putuje s nimi v každé záloze. Hodnoty se nevypisují.
$otpCardKeyRaw = educanet_secret('otp_card_key');
$otpCardKey = $otpCardKeyRaw === '' ? false : base64_decode($otpCardKeyRaw, true);
$otpCardKeyValid = is_string($otpCardKey) && strlen($otpCardKey) === 32;
if ($otpCardKeyRaw === '') {
    pf_warn('otp_card_key není nastavený', 'klíč kartiček pak leží v storage/ a putuje se zálohami – viz docs/NASAZENI_PRODUKCE.md §3');
} else {
    pf_check($otpCardKeyValid, 'otp_card_key je platný (base64, 32 bajtů)', $otpCardKeyValid ? '' : 'neplatná hodnota – aplikace ji ignoruje a použije klíč ze storage/');
}
$storageOtpKeyPath = STORAGE_DIR . '/accounts_v58_key.json.php';
if ($otpCardKeyValid && is_file($storageOtpKeyPath)) {
    $storedOtpKey = base64_decode((string)(load_php_json($storageOtpKeyPath)['key'] ?? ''), true);
    if ($storedOtpKey === $otpCardKey) {
        pf_warn('Klíč kartiček je pořád i v storage/accounts_v58_key.json.php', 'je shodný s otp_card_key – soubor ze storage/ smaž, ať neputuje se zálohami');
    } else {
        pf_warn('Klíč kartiček v storage/ se liší od otp_card_key', 'dříve vydané kartičky nepůjde znovu vytisknout (hesla fungují dál) – přenes starý klíč do otp_card_key, nebo hesla vydej znovu; viz docs/NASAZENI_PRODUKCE.md §3');
    }
}

// --- teacher_export_key síla a testovací hodnoty ------------------------------
$teacherKey = function_exists('teacher_export_key') ? teacher_export_key() : '';
$knownTestValues = ['ucitel-test-57', 'testovaci-lokalni-klic', 'CHANGE_ME_WITH_A_LONG_RANDOM_SECRET', 'VLOZ_SEM_NOVY_DLOUHY_NAHODNY_KLIC'];
if ($teacherKey === '') {
    if ($teacher59ModeEarly !== 'accounts') {
        pf_warn('teacher_export_key není nastavený', 'učitelský CSV export bude nedostupný, dokud se nenastaví (nebo dokud neběží učitelské účty v59 v accounts módu)');
    }
    // V accounts módu je "nenastaveno" v pořádku – viz FAIL/PASS o pár řádků níže
    // v sekci "Učitelské účty (v59)", který k tomu dává jednoznačnou zprávu.
} else {
    pf_check(strlen($teacherKey) >= 32, 'teacher_export_key má alespoň 32 znaků', 'délka=' . strlen($teacherKey));
    pf_check(!in_array($teacherKey, $knownTestValues, true), 'teacher_export_key není známá testovací/placeholder hodnota');
}

// --- Vývojové a rizikové proměnné prostředí, které nesmí být nastaveny -------
$forbiddenVars = [
    'EDUCANET_DEV_BYPASS',
    'EDUCANET_ALLOW_FREE_CLASS_ENTRY',
    'EDUCANET_LAB_BROKER_URL',
    'EDUCANET_LAB_BROKER_TOKEN',
];
foreach ($forbiddenVars as $var) {
    $value = pf_env($var, $fileEnv);
    pf_check($value === null || $value === '', "$var není nastaveno", $value !== null ? 'nastaveno' : '');
}
$emailVerify = pf_env('EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY', $fileEnv);
pf_check($emailVerify === null || $emailVerify !== '0', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY není vypnuté (0)', $emailVerify === null ? 'výchozí (zapnuto)' : (string)$emailVerify);

// --- EDUCANET_APP_URL musí být https -----------------------------------------
$appUrl = pf_env('EDUCANET_APP_URL', $fileEnv);
if ($appUrl === null || trim($appUrl) === '') {
    pf_warn('EDUCANET_APP_URL není nastavené', 'e-maily s odkazy (reset hesla, ověření) se nebudou odesílat');
} else {
    pf_check(str_starts_with($appUrl, 'https://'), 'EDUCANET_APP_URL používá https://', $appUrl);
}

// --- STORAGE_DIR ---------------------------------------------------------------
$storageOverride = pf_env('EDUCANET_STORAGE_DIR', $fileEnv);
if ($storageOverride !== null && trim($storageOverride) !== '') {
    pf_pass('EDUCANET_STORAGE_DIR je vědomě nastaveno', $storageOverride);
} else {
    pf_pass('EDUCANET_STORAGE_DIR není nastaveno – použije se výchozí storage/ v aplikaci');
}

// --- php.ini nastavení -----------------------------------------------------
$loadedIni = php_ini_loaded_file();
$iniFilesToCheck = [$loadedIni ?: null, $phpIniArg];
$iniSettings = [
    'display_errors' => ['expected' => '0', 'label' => 'display_errors=Off'],
    'log_errors' => ['expected' => '1', 'label' => 'log_errors=On'],
    'expose_php' => ['expected' => '0', 'label' => 'expose_php=Off'],
    'session.use_strict_mode' => ['expected' => '1', 'label' => 'session.use_strict_mode=1'],
];
foreach ($iniSettings as $key => $spec) {
    $current = ini_get($key);
    $normalized = strtolower(trim((string)$current));
    $ok = in_array($normalized, [$spec['expected'], $spec['expected'] === '1' ? 'on' : 'off'], true);
    $note = 'CLI php.ini' . ($phpIniArg ? '; ověř i ' . $phpIniArg . ' ručně (CLI nevidí FPM pool)' : '; produkční FPM pool ověř zvlášť (--php-ini jen dokumentuje cestu)');
    pf_check($ok, $spec['label'], $current === false ? 'nezjištěno' : (string)$current . ' (' . $note . ')', true);
}
// SEC59-18: session.cache_limiter=nocache – zvlášť (přesná shoda, ne on/off pár jako výše).
$cacheLimiter = ini_get('session.cache_limiter');
$cacheLimiterNorm = strtolower(trim((string)$cacheLimiter));
$cacheLimiterNote = 'CLI php.ini' . ($phpIniArg ? '; ověř i ' . $phpIniArg . ' ručně (CLI nevidí FPM pool)' : '; produkční FPM pool ověř zvlášť (--php-ini jen dokumentuje cestu)');
pf_check($cacheLimiterNorm === 'nocache', 'session.cache_limiter=nocache', $cacheLimiter === false ? 'nezjištěno' : (string)$cacheLimiter . ' (' . $cacheLimiterNote . ')', true);
if ($phpIniArg !== null) {
    pf_check(is_file($phpIniArg), '--php-ini soubor existuje (jen informativní, CLI ho nenačítá)', $phpIniArg, true);
}
pf_check(extension_loaded('Zend OPcache'), 'OPcache je aktivní (doporučeno)', '', true);

// --- Migrace ------------------------------------------------------------------
if (function_exists('migrate_load_all') || is_file($root . '/tools/migrate.php')) {
    require_once __DIR__ . '/migrate.php';
} else {
    // migrate.php má vlastní CLI blok chráněný basename() kontrolou – require je bezpečné.
}
if (function_exists('migrate_load_all')) {
    $migrations = migrate_load_all($root . '/migrations');
    $done = storage_read(migrate_log_path());
    $pending = [];
    foreach ($migrations as $id => $def) {
        if (!isset($done[$id])) $pending[] = $id;
    }
    pf_check($pending === [], 'Všechny migrace úložiště jsou aplikované', $pending === [] ? '' : 'čekající: ' . implode(', ', $pending));
} else {
    pf_warn('Nepodařilo se ověřit stav migrací (migrate_load_all nenalezeno)');
}

// --- Poslední záloha < 24 h ----------------------------------------------------
$backupDirEnv = pf_env('EDUCANET_BACKUP_DIR', $fileEnv);
$backupDir = $backupDirEnv !== null && trim($backupDirEnv) !== '' ? rtrim($backupDirEnv, '/\\') : rtrim(dirname($root), '/\\') . '/educanet-backups';
if (is_dir($backupDir)) {
    $newest = 0;
    foreach (glob($backupDir . '/*') ?: [] as $entry) {
        $mtime = @filemtime($entry) ?: 0;
        if ($mtime > $newest) $newest = $mtime;
    }
    if ($newest === 0) {
        pf_warn('V adresáři záloh nebyla nalezena žádná záloha', $backupDir);
    } else {
        $ageHours = (time() - $newest) / 3600;
        pf_check($ageHours < 24, 'Poslední záloha storage/ je mladší než 24 h', sprintf('%.1f h', $ageHours));
    }
} else {
    pf_warn('Adresář záloh neexistuje', $backupDir);
}

// --- Učitelské účty (v59) -------------------------------------------------------
// (modul už je načtený výše u kontroly teacher_export_key, viz SEC59-05)
if (function_exists('teacher59_mode')) {
    $mode = teacher59_mode();
    if ($mode === 'accounts') {
        $accounts = teacher59_accounts();
        $activeAdmins = teacher59_active_admin_ids($accounts);
        pf_check($activeAdmins !== [], 'Existuje aspoň jeden aktivní učitelský administrátorský účet', 'počet=' . count($activeAdmins));

        // SEC59-05: v accounts módu se sdílený teacher_export_key už NEPOUŽÍVÁ pro
        // přihlášení na teacher.php ani pro CSV export (export_extra_csv.php čte
        // identitu z učitelské session, ne z klíče) – ponechaný klíč je zbytečné
        // riziko (starý sdílený secret, který nikdo neruší).
        pf_check($teacherKey === '', 'teacher_export_key není nastavený (accounts mód ho už nepoužívá pro login ani export)', $teacherKey !== '' ? 'nastaveno' : '');

        $accountsRequired = pf_env('EDUCANET_TEACHER_ACCOUNTS_REQUIRED', $fileEnv);
        pf_check($accountsRequired === '1' || strtolower((string)$accountsRequired) === 'true', 'EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1 je nastaveno (accounts mód nespadne tiše zpět do legacy)', $accountsRequired === null ? 'nenastaveno' : (string)$accountsRequired, true);
    } else {
        pf_fail('Učitelský modul účtů je nainstalovaný, ale běží v legacy režimu (ne "accounts")', 'mode=' . $mode);
    }
} else {
    pf_warn('Modul učitelských účtů (teacher_accounts_v59.php) nebyl nalezen – ověř přístup na teacher.php ručně');
}

// --- lab_v57_secret (pokud je vyžadovaný) --------------------------------------
// Poznámka: nevoláme lab57_secret() přímo – při první invokaci by si sám vytvořil
// storage/linux_v57/lab_v57_secret.json.php (stejně jako první reálný request na
// lab_v57_api.php). Preflight je jen kontrola, ne generátor – jen ověříme, že
// úložiště labu existuje a je zapisovatelné, aby se secret mohl vytvořit při
// prvním použití.
$lab57Dir = STORAGE_DIR . '/linux_v57';
pf_check(!is_dir($lab57Dir) || is_writable($lab57Dir), 'Úložiště Linux Labu (storage/linux_v57) je zapisovatelné, pokud existuje', $lab57Dir, true);

// --- .htaccess soubory ----------------------------------------------------------
$htaccessDirs = ['', 'storage', 'uploads', 'private', 'tools', 'tests', 'app', 'docs', 'lab-runtime', '.claude', 'database', 'lang', 'migrations', 'materials', 'V1'];
foreach ($htaccessDirs as $dir) {
    $dirPath = $root . ($dir === '' ? '' : '/' . $dir);
    $label = $dir === '' ? '(kořen)' : $dir;
    if ($dir !== '' && !is_dir($dirPath)) {
        // tools/build_release.php některé adresáře do vydání vůbec nedává (tests, lab-runtime,
        // .claude, database, V1) – adresář, který v instalaci není, nemá co chránit.
        pf_pass('.htaccess není potřeba: ' . $label . ' v této instalaci není');
        continue;
    }
    pf_check(is_file($dirPath . '/.htaccess'), '.htaccess existuje: ' . $label, '', $dir === 'V1' || $dir === 'materials' || $dir === 'lang' || $dir === 'migrations');
}

// --- lab-runtime/ nenasazeno ----------------------------------------------------
pf_check(!is_dir($root . '/lab-runtime') || is_file($root . '/lab-runtime/.htaccess'), 'lab-runtime/ buď chybí, nebo je zablokované .htaccess', '', true);

// --- Žádné ponechané dočasné soubory ve storage/ -------------------------------
$tmpLeftovers = glob(STORAGE_DIR . '/*.tmp') ?: [];
pf_check($tmpLeftovers === [], 'storage/ neobsahuje žádné ponechané *.tmp soubory', $tmpLeftovers === [] ? '' : (string)count($tmpLeftovers) . ' souborů');

echo str_repeat('-', 60) . "\n";
$status = $failed > 0 ? 'FAIL' : ($warned > 0 ? 'WARN' : 'OK');
echo "PREFLIGHT_$status checks=$checks failed=$failed warned=$warned\n";

if ($failed > 0) {
    exit(1);
}
if ($warned > 0 && $strict) {
    exit(2);
}
exit(0);
