<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v59 · tools/v59_preflight_audit.php
 *
 * Ověřuje produkční nástroje (tools/preflight.php, tools/build_release.php),
 * chování dev bypass helperu a kořenový .htaccess – vždy v izolovaném dočasném
 * úložišti/adresáři, NIKDY v ostré storage/. Jediná výjimka: test "secret
 * soubor uvnitř docrootu" na chvíli vytvoří a hned smaže neškodný testovací
 * soubor přímo v kořeni projektu (nejde o data žáků), protože tools/preflight.php
 * kontroluje vždy skutečný kořen aplikace, ne dočasnou kopii.
 *
 * PASS …/FAIL … po řádcích, konec V59_PREFLIGHT_AUDIT_OK checks=N failed=0
 * (jinak V59_PREFLIGHT_AUDIT_FAILED, exit 1).
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__);
$PHP_BIN = getenv('PHP_BINARY') ?: 'C:/php/php.exe';
if (!is_file($PHP_BIN)) $PHP_BIN = PHP_BINARY;

$AUDIT_TMP = sys_get_temp_dir() . '/v59_preflight_audit_' . bin2hex(random_bytes(6));
mkdir($AUDIT_TMP, 0770, true);
register_shutdown_function(static function () use ($AUDIT_TMP): void {
    pfaudit_rrmdir($AUDIT_TMP);
});

function pfaudit_rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = @scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . '/' . $item;
        if (is_dir($full) && !is_link($full)) pfaudit_rrmdir($full);
        else @unlink($full);
    }
    @rmdir($dir);
}

$checks = 0;
$failed = 0;
$skipped = 0;

function pfaudit_check(bool $cond, string $label): void
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

function pfaudit_skip(string $label, string $reason): void
{
    global $checks, $skipped;
    $checks++;
    $skipped++;
    echo "SKIP $label – $reason\n";
}

/**
 * Spustí tools/preflight.php jako podproces s daným prostředím (nahrazuje aktuální
 * getenv() jen pro tento běh; po volání se hodnoty vždy vrátí zpět).
 *
 * @param array<string,?string> $env NAME=>hodnota, nebo NAME=>null pro explicitní unset.
 * @return array{exit:int, output:string}
 */
function pfaudit_run_preflight(string $root, string $phpBin, array $env, array $extraArgs = []): array
{
    $previous = [];
    foreach ($env as $name => $value) {
        $previous[$name] = getenv($name);
        if ($value === null) {
            putenv($name);
        } else {
            putenv($name . '=' . $value);
        }
    }
    $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($root . '/tools/preflight.php');
    foreach ($extraArgs as $a) $cmd .= ' ' . escapeshellarg($a);
    $cmd .= ' 2>&1';
    $output = shell_exec($cmd) ?: '';
    $exit = 0;
    // shell_exec neposkytuje exit kód přímo – odvodíme ho z výstupní hlášky.
    if (preg_match('/PREFLIGHT_(OK|WARN|FAIL) checks=\d+ failed=(\d+) warned=(\d+)/', $output, $m)) {
        $exit = ((int)$m[2] > 0) ? 1 : 0;
    } else {
        $exit = 1; // nerozpoznaný výstup = považujeme za chybu
    }
    foreach ($previous as $name => $value) {
        if ($value === false) putenv($name);
        else putenv($name . '=' . $value);
    }
    return ['exit' => $exit, 'output' => $output];
}

// ---------------------------------------------------------------------------
// 1) preflight.php – dev bypass zapnutý => FAIL
// ---------------------------------------------------------------------------
$r = pfaudit_run_preflight($ROOT, $PHP_BIN, [
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LAB_BROKER_URL' => null,
    'EDUCANET_LAB_BROKER_TOKEN' => null,
]);
pfaudit_check($r['exit'] === 1 && str_contains($r['output'], 'FAIL EDUCANET_DEV_BYPASS'), 'preflight: EDUCANET_DEV_BYPASS=1 vede na FAIL');

// ---------------------------------------------------------------------------
// 2) preflight.php – EDUCANET_APP_URL přes http:// => FAIL
// ---------------------------------------------------------------------------
$r = pfaudit_run_preflight($ROOT, $PHP_BIN, [
    'EDUCANET_DEV_BYPASS' => null,
    'EDUCANET_APP_URL' => 'http://educanet.example.test',
]);
pfaudit_check(str_contains($r['output'], 'FAIL EDUCANET_APP_URL používá https://'), 'preflight: EDUCANET_APP_URL=http:// vede na FAIL');

// ---------------------------------------------------------------------------
// 3) preflight.php – secret soubor uvnitř docrootu => FAIL
//    (dočasný neškodný soubor přímo v kořeni projektu, smazaný hned po testu)
//
// SEC59-20: jméno nesmí vypadat jako povolený release dotfile (jen ".htaccess"
// smí do vydání) ani skončit na jednu z přípon, kterou by tools/build_release.php
// tiše vynechal beze slova (*.tmp apod.) – naopak chceme, aby ho případný
// zapomenutý úklid nechal spadnout do "neočekávaný dotfile" větve a shodil build,
// ne aby zmizel beze stopy. Navíc: náhodný název (nejde uhodnout/přepsat souběžně
// spuštěným auditem) a úklid navázaný přes register_shutdown_function, aby proběhl
// i při fatal erroru/timeoutu (finally blok níže ho pak jen zopakuje – @unlink na
// již smazaném souboru je neškodné).
// ---------------------------------------------------------------------------
$insideSecret = $ROOT . '/.v59_preflight_audit_secret_' . bin2hex(random_bytes(8)) . '.php';
register_shutdown_function(static function () use ($insideSecret): void {
    if (is_file($insideSecret)) @unlink($insideSecret);
});
file_put_contents($insideSecret, "<?php\nreturn ['teacher_export_key' => 'audit-inside-docroot-secret-value-000000'];\n");
try {
    $r = pfaudit_run_preflight($ROOT, $PHP_BIN, [
        'EDUCANET_SECRETS_FILE' => $insideSecret,
        'EDUCANET_TEACHER_EXPORT_KEY' => null,
    ]);
    pfaudit_check(str_contains($r['output'], 'FAIL Soubor se secrety leží mimo webový docroot'), 'preflight: secret soubor uvnitř docrootu vede na FAIL');
} finally {
    @unlink($insideSecret);
}

// ---------------------------------------------------------------------------
// 4) preflight.php – kompletně dobré prostředí => bez FAILu (exit 0)
// ---------------------------------------------------------------------------
$goodStorage = $AUDIT_TMP . '/storage-good';
$goodBackups = $AUDIT_TMP . '/backups-good';
mkdir($goodStorage, 0770, true);
mkdir($goodBackups, 0770, true);

// a) migrace + čerstvá záloha přes skutečné CLI nástroje (nikdy živá storage/).
$migrateCmd = escapeshellarg($PHP_BIN) . ' ' . escapeshellarg($ROOT . '/tools/migrate.php') . ' --apply --backup-now'
    . ' --backup-dir=' . escapeshellarg($goodBackups);
$migrateEnv = [
    'EDUCANET_STORAGE_DIR' => $goodStorage,
    'EDUCANET_BACKUP_DIR' => $goodBackups,
];
$prevMigrateEnv = [];
foreach ($migrateEnv as $k => $v) { $prevMigrateEnv[$k] = getenv($k); putenv("$k=$v"); }
$migrateOut = shell_exec($migrateCmd . ' 2>&1') ?: '';
foreach ($prevMigrateEnv as $k => $v) { if ($v === false) putenv($k); else putenv("$k=$v"); }
pfaudit_check(str_contains($migrateOut, 'MIGRATE_OK'), 'good-env příprava: migrace na dočasném úložišti proběhly (MIGRATE_OK)');

// b) minimální platný účet učitele-admina v accounts módu (ruční zápis ve formátu úložiště).
$teacherAccountsPath = $goodStorage . '/teacher_accounts_v59.json.php';
$teacherPayload = [
    'mode' => 'accounts',
    'version' => 1,
    'dummy_hash' => '$2y$10$' . str_repeat('a', 53),
    'accounts' => [
        't_' . bin2hex(random_bytes(8)) => [
            'id' => 'audit-admin', 'login' => 'audit-admin', 'display_name' => 'Audit Admin', 'role' => 'admin',
            'assignments' => [['class_id' => '*', 'subject_id' => '*']],
            'password_hash' => '$2y$10$' . str_repeat('a', 53), 'must_change_password' => false,
            'otp' => null, 'status' => 'active', 'session_version' => 1,
            'created_at' => date(DATE_ATOM), 'created_by' => 'audit', 'updated_at' => date(DATE_ATOM), 'updated_by' => 'audit',
            'last_login_at' => null, 'password_changed_at' => date(DATE_ATOM), 'disabled_at' => null,
        ],
    ],
];
file_put_contents($teacherAccountsPath, "<?php http_response_code(403); exit; ?>\n" . json_encode($teacherPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
pfaudit_check(is_file($teacherAccountsPath), 'good-env příprava: testovací účet admina zapsán');

// SEC59-05: accounts mód nepoužívá teacher_export_key – secrets soubor mimo docroot
// (v $AUDIT_TMP, tedy mimo projekt) bez tohoto klíče, aby "secrets mimo docroot"
// check prošel, ale teacher_export_key zůstal prázdný (jak má být v accounts módu).
$goodSecretsFile = $AUDIT_TMP . '/educanet.secrets.good.php';
file_put_contents($goodSecretsFile, "<?php\nreturn ['tutor_endpoint' => '', 'otp_card_key' => '" . base64_encode(random_bytes(32)) . "'];\n");

$goodEnv = [
    'EDUCANET_STORAGE_DIR' => $goodStorage,
    'EDUCANET_BACKUP_DIR' => $goodBackups,
    'EDUCANET_APP_URL' => 'https://educanet.example.test',
    // SEC59-05: accounts mód (viz testovací admin účet zapsaný výše) už
    // EDUCANET_TEACHER_EXPORT_KEY nepoužívá – ponechaný by teď byl FAIL. Secrets
    // soubor mimo docroot ($goodSecretsFile výše) drží "secrets mimo docroot"
    // check zelený, aniž by nastavoval teacher_export_key.
    'EDUCANET_TEACHER_EXPORT_KEY' => null,
    'EDUCANET_TEACHER_ACCOUNTS_REQUIRED' => '1',
    'EDUCANET_SECRETS_FILE' => $goodSecretsFile,
    'EDUCANET_DEV_BYPASS' => null,
    'EDUCANET_ALLOW_FREE_CLASS_ENTRY' => null,
    'EDUCANET_LAB_BROKER_URL' => null,
    'EDUCANET_LAB_BROKER_TOKEN' => null,
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => null,
    'EDUCANET_GOOGLE_CLIENT_ID' => null,
];
$r = pfaudit_run_preflight($ROOT, $PHP_BIN, $goodEnv);
if ($r['exit'] !== 0) {
    echo "---- preflight (good env) výstup pro diagnostiku ----\n" . $r['output'] . "----------------------------------------------------\n";
}
pfaudit_check($r['exit'] === 0, 'preflight: kompletně dobré prostředí neobsahuje žádný FAIL (WARN smí zůstat)');

// ---------------------------------------------------------------------------
// 5) build_release.php – vydání do dočasného adresáře MIMO projekt
// ---------------------------------------------------------------------------
$releaseOut = $AUDIT_TMP . '/release-out';
$cmd = escapeshellarg($PHP_BIN) . ' ' . escapeshellarg($ROOT . '/tools/build_release.php') . ' --out=' . escapeshellarg($releaseOut) . ' --quiet 2>&1';
$buildOut = shell_exec($cmd) ?: '';
pfaudit_check(str_contains($buildOut, 'BUILD_RELEASE_OK'), 'build_release: sestavení do dočasného adresáře uspělo', );
$manifestPath = $releaseOut . '/RELEASE_MANIFEST.json';
pfaudit_check(is_file($manifestPath), 'build_release: RELEASE_MANIFEST.json existuje');
$manifest = is_file($manifestPath) ? json_decode((string)file_get_contents($manifestPath), true) : null;
$manifestFiles = is_array($manifest) ? (array)($manifest['files'] ?? []) : [];
pfaudit_check(is_array($manifest) && isset($manifest['version'], $manifest['built_at']) && $manifestFiles !== [], 'build_release: manifest obsahuje verzi, čas a seznam souborů');

$excludedShouldBeAbsent = ['CLAUDE.md', '.claude/launch.json', 'tests/app_router_audit.php', 'V1/data/.gitkeep'];
foreach ($excludedShouldBeAbsent as $rel) {
    $inManifest = isset($manifestFiles[$rel]);
    $onDisk = is_file($releaseOut . '/' . $rel);
    pfaudit_check(!$inManifest && !$onDisk, "build_release: $rel je vyloučen z vydání");
}
pfaudit_check(!is_dir($releaseOut . '/storage') || (is_file($releaseOut . '/storage/.htaccess') && (@scandir($releaseOut . '/storage') ?: []) <= ['.', '..', '.htaccess']), 'build_release: storage/ ve vydání obsahuje nanejvýš .htaccess');
pfaudit_check(!is_file($releaseOut . '/private/educanet.secrets.php'), 'build_release: private/educanet.secrets.php je vyloučen');

// Ověření hashe: první soubor manifestu musí odpovídat aktuálnímu obsahu v projektu.
$sampleRel = null;
foreach ($manifestFiles as $rel => $hash) {
    if (is_file($ROOT . '/' . $rel)) { $sampleRel = $rel; break; }
}
if ($sampleRel !== null) {
    $expected = hash_file('sha256', $ROOT . '/' . $sampleRel);
    pfaudit_check($expected === $manifestFiles[$sampleRel], "build_release: sha256 v manifestu odpovídá souboru ($sampleRel)");
} else {
    pfaudit_check(false, 'build_release: nepodařilo se najít žádný soubor manifestu pro ověření hashe');
}

// Zkouška nasazení: preflight spuštěný nad SESTAVENÝM vydáním (tak, jak ho administrátor
// spustí na serveru) nesmí padat na adresářích, které vydání záměrně vynechává
// (tests, lab-runtime, .claude, database, V1), ani na chybějícím sdíleném klíči v accounts módu.
$r = pfaudit_run_preflight($releaseOut, $PHP_BIN, $goodEnv);
if ($r['exit'] !== 0) {
    echo "---- preflight nad vydáním – výstup pro diagnostiku ----\n" . $r['output'] . "--------------------------------------------------------\n";
}
pfaudit_check($r['exit'] === 0, 'preflight nad sestaveným vydáním: dobré prostředí neobsahuje žádný FAIL');
pfaudit_check(str_contains($r['output'], 'PASS .htaccess není potřeba: tests') && !str_contains($r['output'], 'FAIL .htaccess existuje'), 'preflight nad vydáním: adresáře vynechané z vydání nejsou chyba');

$r = pfaudit_run_preflight($releaseOut, $PHP_BIN, array_merge($goodEnv, ['EDUCANET_SECRETS_FILE' => $AUDIT_TMP . '/neexistuje.secrets.php']));
pfaudit_check($r['exit'] === 0 && str_contains($r['output'], 'WARN Soubor se secrety nenalezen') && !str_contains($r['output'], 'FAIL Nenalezen soubor se secrety'), 'preflight: accounts mód bez souboru se secrety je jen WARN (sdílený klíč se nepoužívá)');

$badOtpSecretsFile = $AUDIT_TMP . '/educanet.secrets.bad-otp.php';
file_put_contents($badOtpSecretsFile, "<?php\nreturn ['otp_card_key' => 'neplatny-klic'];\n");
$r = pfaudit_run_preflight($releaseOut, $PHP_BIN, array_merge($goodEnv, ['EDUCANET_SECRETS_FILE' => $badOtpSecretsFile]));
pfaudit_check($r['exit'] === 1 && str_contains($r['output'], 'FAIL otp_card_key je platný') && !str_contains($r['output'], 'neplatny-klic'), 'preflight: neplatný otp_card_key vede na FAIL a hodnota se nevypíše');
pfaudit_rrmdir($releaseOut);

// --- odmítnutí zápisu dovnitř projektu -------------------------------------
$insideOut = $ROOT . '/tools/.v59_should_not_be_created';
$cmd = escapeshellarg($PHP_BIN) . ' ' . escapeshellarg($ROOT . '/tools/build_release.php') . ' --out=' . escapeshellarg($insideOut) . ' --quiet 2>&1';
$refuseOut = shell_exec($cmd) ?: '';
pfaudit_check(str_contains($refuseOut, 'BUILD_RELEASE_FAIL') && !is_dir($insideOut), 'build_release: odmítá cíl uvnitř projektu a nic nevytvoří');
if (is_dir($insideOut)) pfaudit_rrmdir($insideOut);

// ---------------------------------------------------------------------------
// 6) dev bypass helper – chování jen cli-server + loopback (skutečný request)
// ---------------------------------------------------------------------------
require_once __DIR__ . '/lib/http_harness.php';
$devStorage = $AUDIT_TMP . '/storage-devbypass';
mkdir($devStorage, 0770, true);
try {
    $harness = Harness::start(['EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_STORAGE_DIR' => $devStorage]);
    try {
        $resp = $harness->request('GET', '/?class=class_3a&student=' . rawurlencode('Audit Zak'));
        $loggedIn = $resp['status'] === 200 && str_contains($resp['body'], 'Audit Zak');
        pfaudit_check($loggedIn, 'dev bypass: EDUCANET_DEV_BYPASS=1 + cli-server + loopback přihlásí přes ?class=&student=');
    } finally {
        $harness->stop();
    }
} catch (Throwable $e) {
    pfaudit_check(false, 'dev bypass: nepodařilo se spustit testovací server (' . $e->getMessage() . ')');
}

// Non-loopback REMOTE_ADDR: PHP vestavěný server (`php -S`) na Windows běžně
// naslouchá jen na adrese, na kterou byl vázán, a `php -S 0.0.0.0:port` mění
// naslouchací adresu, ne to, jak PHP_SAPI hlásí REMOTE_ADDR vzdáleného klienta
// (to je dáno tím, odkud klient skutečně přišel). Aby test byl věrohodný, musel
// by se provést request ze skutečně jiného síťového rozhraní, nebo by dev
// bypass helper musel jít zavolat izolovaně mimo PHP_SAPI cli-server (funkce
// sama kontroluje PHP_SAPI, což ve `php -r` je vždy "cli", ne "cli-server" –
// takový test by tedy testoval jen větev pro CLI, ne pro webový požadavek).
// Proto je tato větev zdokumentovaná jako SKIP, jak počítá plán F4/B.4.
pfaudit_skip('dev bypass: požadavek s ne-loopback REMOTE_ADDR se nepřihlásí', 'nelze věrohodně vynutit jiný REMOTE_ADDR na vestavěném php -S serveru z jednoho stroje bez druhého síťového rozhraní; funkce educanet_dev_bypass_enabled() navíc vždy vrací PHP_SAPI podle skutečného kontextu (cli-server pro webový požadavek, cli pro tento test), takže přímé volání z `php -r` by testovalo jinou větev, ne tu webovou');

// ---------------------------------------------------------------------------
// 7) token-sken: getenv('EDUCANET_DEV_BYPASS') jen v educanet_dev_bypass_enabled()
// ---------------------------------------------------------------------------
$hits = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ROOT, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    /** @var SplFileInfo $f */
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $p = str_replace('\\', '/', $f->getPathname());
    if (str_contains($p, '/tools/') || str_contains($p, '/tests/') || str_contains($p, '/.claude/')) continue;
    if (str_contains((string)file_get_contents($f->getPathname()), "getenv('EDUCANET_DEV_BYPASS')")) $hits[] = $p;
}
$onlyBootstrap = count($hits) === 1 && str_ends_with($hits[0] ?? '', '/bootstrap.php');
pfaudit_check($onlyBootstrap, 'token-sken: getenv(EDUCANET_DEV_BYPASS) se v aplikačním kódu vyskytuje jen v bootstrap.php', );
if (!$onlyBootstrap) echo 'DETAIL nálezy: ' . implode(', ', $hits) . "\n";

// ---------------------------------------------------------------------------
// 8) kořenový .htaccess – statická kontrola pravidel (bez Apache)
// ---------------------------------------------------------------------------
$rootHt = (string)@file_get_contents($ROOT . '/.htaccess');
pfaudit_check($rootHt !== '', 'kořenový .htaccess existuje a není prázdný');
pfaudit_check(str_contains($rootHt, 'md|sql|py|sh|lock|csv|ini|log|example|dist|bak|tmp') || (str_contains($rootHt, '\.md') && str_contains($rootHt, '\.sql')), 'kořenový .htaccess zakazuje *.md/*.sql/*.py/*.sh a další rizikové přípony');
pfaudit_check(str_contains($rootHt, 'json\.php'), 'kořenový .htaccess zakazuje *.json.php');
pfaudit_check(str_contains($rootHt, '^(index|teacher|lab_v57_api|arena_v58_events_api|robots_v58_api|teamgames_v58_api|progress|calendar\.ics|export_extra_csv)\.php$'), 'kořenový .htaccess povoluje jen ověřenou allowlistu vstupních PHP skriptů');
$rootHtNoComments = implode("\n", array_filter(explode("\n", $rootHt), static fn($l) => !str_starts_with(ltrim($l), '#')));
pfaudit_check(!stripos($rootHtNoComments, 'assets'), 'kořenový .htaccess neobsahuje (mimo komentáře) žádné pravidlo zmiňující assets/ – JS katalogy zůstávají čitelné');
pfaudit_check(str_contains($rootHt, '-Indexes'), 'kořenový .htaccess vypíná výpis adresářů (Options -Indexes)');

foreach (['lang', 'migrations', 'materials', 'V1'] as $dir) {
    $ht = (string)@file_get_contents($ROOT . '/' . $dir . '/.htaccess');
    pfaudit_check(str_contains($ht, 'Require all denied') && str_contains($ht, 'Deny from all'), "$dir/.htaccess zakazuje přístup z webu (Apache 2.2 i 2.4)");
}

// ---------------------------------------------------------------------------
// 9) SEC59-01 defense-in-depth: <FilesMatch "."> v každém interním adresáři +
//    mod_rewrite guard v kořeni.
// ---------------------------------------------------------------------------
pfaudit_check(
    (bool)preg_match('/RewriteCond\s+%\{REQUEST_URI\}.*\(V1\|storage\|private\|tools\|tests\|app\|docs\|lang\|migrations\|database\|lab-runtime\|cache\|\\\\\.claude\)/', $rootHt)
        && str_contains($rootHt, '[F,L]'),
    'kořenový .htaccess: mod_rewrite guard zakazuje interní adresáře nezávisle na FilesMatch merge'
);
pfaudit_check(
    (bool)preg_match('/materials\(\/\|\$\)/', $rootHt) && str_contains($rootHt, 'SCHOOL_YEAR_[0-9_]+\\.md$'),
    'kořenový .htaccess: mod_rewrite guard zakazuje materials/ s výjimkou SCHOOL_YEAR_*.md'
);

$filesMatchDotDirs = ['V1', 'storage', 'private', 'tools', 'tests', 'app', 'docs', 'lang', 'migrations', 'materials', 'database', 'lab-runtime', '.claude'];
foreach ($filesMatchDotDirs as $dir) {
    $ht = (string)@file_get_contents($ROOT . '/' . $dir . '/.htaccess');
    pfaudit_check(str_contains($ht, '<FilesMatch ".">') && substr_count($ht, 'Require all denied') >= 2, "$dir/.htaccess: <FilesMatch \".\"> Require all denied (přebíjí sloučený kořenový allowlist)");
}
$cacheRuntimeHt = (string)@file_get_contents($ROOT . '/cache/runtime/.htaccess');
pfaudit_check(str_contains($cacheRuntimeHt, '<FilesMatch ".">') && str_contains($cacheRuntimeHt, 'Require all denied'), 'cache/runtime/.htaccess: <FilesMatch "."> Require all denied');

// ---------------------------------------------------------------------------
// 10) SEC59-22: materials/school_year/.htaccess povoluje jen SCHOOL_YEAR_*.md
// ---------------------------------------------------------------------------
$syHt = (string)@file_get_contents($ROOT . '/materials/school_year/.htaccess');
// Komentáře smí zmiňovat WEEKLY_SCHEDULE.md (vysvětlují, proč NENÍ veřejný) – kontrolujeme
// jen text samotné <FilesMatch "..."> direktivy (mezi uvozovkami na jejím řádku), ne
// zbytek souboru.
$syFilesMatchLine = '';
foreach (explode("\n", $syHt) as $line) {
    if (str_contains($line, '<FilesMatch "')) { $syFilesMatchLine = $line; break; }
}
$syBackslashDotMd = "\\" . '.md$'; // jeden literální backslash + ".md$" – tak, jak je v Apache regexu.
pfaudit_check(
    str_contains($syFilesMatchLine, 'SCHOOL_YEAR_[0-9_]+' . $syBackslashDotMd)
        && !str_contains($syFilesMatchLine, 'WEEKLY_SCHEDULE'),
    'materials/school_year/.htaccess: <FilesMatch> povoluje jen SCHOOL_YEAR_*.md, ne WEEKLY_SCHEDULE.md'
);

// ---------------------------------------------------------------------------
// 11) SEC59-12: apache-vhost příklad má ukotvený PHP-FPM regex (bez úvodní \.)
// ---------------------------------------------------------------------------
$vhost = (string)@file_get_contents($ROOT . '/docs/deploy/apache-vhost-educanet.conf.example');
pfaudit_check(
    str_contains($vhost, '# <FilesMatch "^(index|teacher|')
        && !preg_match('/#\s*<FilesMatch "\\\\\.\(index\|teacher\|/', $vhost),
    'apache-vhost-educanet.conf.example: PHP-FPM FilesMatch regex je ukotvený (^...), ne s úvodní zpětně lomenou tečkou'
);
pfaudit_check(str_contains($vhost, 'Files "RELEASE_MANIFEST.json"'), 'apache-vhost-educanet.conf.example: RELEASE_MANIFEST.json je zakázaný');
pfaudit_check(str_contains($vhost, '# Header always set Strict-Transport-Security') && !preg_match('/^\s*Header always set Strict-Transport-Security/m', $vhost), 'apache-vhost-educanet.conf.example: HSTS hlavička je zakomentovaná');

// ---------------------------------------------------------------------------
// 12) SEC59-13/19: nginx příklad – HSTS zakomentovaná, ACME výjimka, RELEASE_MANIFEST.json
// ---------------------------------------------------------------------------
$nginx = (string)@file_get_contents($ROOT . '/docs/deploy/nginx-educanet.conf.example');
pfaudit_check(str_contains($nginx, '# add_header Strict-Transport-Security') && !preg_match('/^\s*add_header Strict-Transport-Security/m', $nginx), 'nginx-educanet.conf.example: HSTS hlavička je zakomentovaná');
pfaudit_check(str_contains($nginx, '.well-known/acme-challenge/'), 'nginx-educanet.conf.example: výjimka pro ACME HTTP-01 ověření existuje');
pfaudit_check(str_contains($nginx, '/RELEASE_MANIFEST.json'), 'nginx-educanet.conf.example: RELEASE_MANIFEST.json je zakázaný');

// ---------------------------------------------------------------------------
// 13) educanet.env.example: HSTS vypnutý ve výchozím stavu, accounts-required zmíněno
// ---------------------------------------------------------------------------
$envExample = (string)@file_get_contents($ROOT . '/docs/deploy/educanet.env.example');
pfaudit_check(str_contains($envExample, 'EDUCANET_HSTS=0'), 'educanet.env.example: EDUCANET_HSTS výchozí je 0');
pfaudit_check(str_contains($envExample, 'EDUCANET_TEACHER_ACCOUNTS_REQUIRED'), 'educanet.env.example: EDUCANET_TEACHER_ACCOUNTS_REQUIRED je zdokumentovaná');

// ---------------------------------------------------------------------------
// 14) SEC59-20: build_release odmítá neočekávaný dotfile a nezahrne V1/ ani *.pem/*.key
// ---------------------------------------------------------------------------
$strayDotfile = $ROOT . '/.v59_preflight_audit_stray_' . bin2hex(random_bytes(6)) . '.php';
file_put_contents($strayDotfile, "<?php\n// audit stray dotfile\n");
try {
    $cmd = escapeshellarg($PHP_BIN) . ' ' . escapeshellarg($ROOT . '/tools/build_release.php') . ' --out=' . escapeshellarg($AUDIT_TMP . '/release-dotfile-guard') . ' --quiet 2>&1';
    $out = shell_exec($cmd) ?: '';
    pfaudit_check(str_contains($out, 'BUILD_RELEASE_FAIL') && str_contains($out, 'dotfiles'), 'build_release: neočekávaný dotfile v kořeni shodí build (BUILD_RELEASE_FAIL)');
} finally {
    @unlink($strayDotfile);
    pfaudit_rrmdir($AUDIT_TMP . '/release-dotfile-guard');
}

$releaseOut2 = $AUDIT_TMP . '/release-out-2';
$cmd = escapeshellarg($PHP_BIN) . ' ' . escapeshellarg($ROOT . '/tools/build_release.php') . ' --out=' . escapeshellarg($releaseOut2) . ' --quiet 2>&1';
$out2 = shell_exec($cmd) ?: '';
pfaudit_check(str_contains($out2, 'BUILD_RELEASE_OK'), 'build_release: čisté vydání (bez cizích dotfiles) proběhne');
pfaudit_check(!is_dir($releaseOut2 . '/V1'), 'build_release: V1/ chybí ve vydání úplně (SEC59-01/d)');
pfaudit_rrmdir($releaseOut2);

echo str_repeat('-', 60) . "\n";
if ($failed === 0) {
    echo "V59_PREFLIGHT_AUDIT_OK checks=$checks failed=0 skipped=$skipped\n";
    exit(0);
}
echo "V59_PREFLIGHT_AUDIT_FAILED checks=$checks failed=$failed skipped=$skipped\n";
exit(1);
