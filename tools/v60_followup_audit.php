<?php

declare(strict_types=1);

/**
 * EDUCANET v60 · audit dokončovacích úprav (přesměrování přihlášeného žáka z úvodní stránky, světlé plochy
 * a kontrast v60 stylů, produkční šablony aaPanelu).
 *   php tools/v60_followup_audit.php
 * Dočasné úložiště + vestavěný server (tools/lib/http_harness.php); ostrou storage/ nikdy nečte ani nezapisuje.
 * Rozsahové nadpisy učitelského přehledu a 403 u admin záložek hlídá tools/v59_teacher_scope_audit.php.
 * Konec: V60_FOLLOWUP_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = str_replace('\\', '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v60-followup')), '/');
$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $path): string => (string)@file_get_contents($path);

$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $root . '/storage'));

// --- 1) Úvodní stránka: nepřihlášený vidí přihlášení, přihlášený žák je přesměrován na přehled -------------
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $noFollow = ['follow_redirects' => false];
    $anonHome = $h->request('GET', '/?view=home', [], $noFollow);
    $anonRoot = $h->request('GET', '/', [], $noFollow);
    $isLogin = static fn(array $r): bool => (int)$r['status'] === 200 && str_contains((string)$r['body'], 'value="local_login"');
    $check('nepřihlášený: ?view=home i / ukazují přihlašovací formulář (bez přesměrování)', $isLogin($anonHome) && $isLogin($anonRoot));

    $login = audit_login_student($h, 'class_3a', 'Audit Zak');
    $check('přihlášený žák: přehled (dashboard) se načte', (int)$login['response']['status'] === 200 && is_string($login['csrf']));
    $homeSigned = $h->request('GET', '/?view=home', [], $noFollow);
    $rootSigned = $h->request('GET', '/', [], $noFollow);
    $location = static function (array $r): string {
        foreach ((array)($r['headers'] ?? []) as $k => $v) if (strtolower((string)$k) === 'location') return (string)$v;
        return '';
    };
    $isRedirect = static fn(array $r): bool => (int)$r['status'] === 302 && str_contains($location($r), 'view=dashboard');
    $check('přihlášený žák: ?view=home → 302 na dashboard', $isRedirect($homeSigned));
    $check('přihlášený žák: / → 302 na dashboard', $isRedirect($rootSigned));
    $followed = $h->request('GET', '/?view=home');
    $check('přihlášený žák: po přesměrování 200 a žádný přihlašovací formulář', (int)$followed['status'] === 200 && !str_contains((string)$followed['body'], 'value="local_login"'));

    $out = $h->request('POST', '/', ['action' => 'logout_class', 'csrf' => (string)$login['csrf']], $noFollow);
    $afterLogout = $h->request('GET', '/?view=home', [], $noFollow);
    $check('po odhlášení z třídy se zase ukáže přihlášení (žádná smyčka přesměrování)', in_array((int)$out['status'], [302, 303], true) && $isLogin($afterLogout));
} finally {
    $h->stop();
}

// --- 2) v60 styly: světlé plochy, kontrast, žádný přetok --------------------------------------------------
$css = [];
foreach (['marketplace', 'projects', 'feedback', 'arena', 'profile'] as $name) $css[$name] = $read($root . '/assets/' . $name . '-v60.css');
$allCss = implode("\n", $css);
foreach ($css as $name => $text) {
    $check($name . '-v60.css: neobsahuje nedefinované tmavé tokeny (--panel-bg/--card-bg) ani průhledně bílé plochy', $text !== '' && !preg_match('/--panel-bg|--card-bg|background:\s*rgba\(255,\s*255,\s*255/', $text));
    $check($name . '-v60.css: prefers-reduced-motion je ošetřeno', str_contains($text, 'prefers-reduced-motion') || $name === 'profile');
}
$pairs = [ // [popis, popředí, pozadí] – barvy použité v v60 stylech
    ['text #111827 na bílé', '#111827', '#ffffff'], ['tlumený text #4b5563 na bílé', '#4b5563', '#ffffff'], ['text #374151 na bílé', '#374151', '#ffffff'],
    ['tlumený text #4b5563 na #eef2f5', '#4b5563', '#eef2f5'], ['text na #eef2f5', '#111827', '#eef2f5'], ['text na #e6f6ef', '#111827', '#e6f6ef'],
    ['text na #fff3d6', '#111827', '#fff3d6'], ['text na #cdeedb', '#111827', '#cdeedb'], ['text na #f9d9d3', '#111827', '#f9d9d3'],
    ['text na #dde3e9', '#111827', '#dde3e9'], ['zakázané tlačítko', '#4b5563', '#e5e9ee'], ['odznak počtu', '#ffffff', '#1d4ed8'],
    ['+ body', '#0b6b49', '#eef2f5'], ['− body', '#a92f2f', '#eef2f5'],
];
foreach ($pairs as [$label, $fg, $bg]) {
    $ratio = audit_contrast_ratio($fg, $bg);
    $has = static fn(string $c): bool => str_contains(strtolower($allCss), $c) || ($c === '#ffffff' && preg_match('/#fff(?![0-9a-f])/i', $allCss) === 1);
    $used = $has(strtolower($fg)) && $has(strtolower($bg));
    $check('kontrast ' . $label . ' ≥ 4,5 : 1 (' . ($ratio === null ? '?' : number_format($ratio, 2)) . ') a dvojice je v v60 stylech použita', $ratio !== null && $ratio >= 4.5 && $used);
}
$check('okraj volby (#8b96a3) na bílé ≥ 3 : 1 (WCAG 1.4.11)', (audit_contrast_ratio('#8b96a3', '#ffffff') ?? 0) >= 3.0);
$check('mřížky obchodu/projektů/profilu používají minmax(0,…)/min(100%,…) – na 390 px nepřetečou', str_contains($css['marketplace'], 'minmax(min(100%,240px),1fr)') && str_contains($css['projects'], 'minmax(min(100%,260px),1fr)') && str_contains($css['projects'], 'minmax(0,1fr)') && substr_count($css['profile'], 'minmax(0,1fr)') >= 3);
$check('dlouhá slova: karty, seznamy a statistiky mají overflow-wrap + min-width:0', substr_count($allCss, 'overflow-wrap') >= 10 && substr_count($css['marketplace'] . $css['projects'], 'min-width:0') >= 8);
$check('cíle ≥ 44 px i proti globálnímu .btn{min-height:38px!important}', str_contains($css['marketplace'], 'body:not(.assessment-mode) .mkt60-card .btn') && str_contains($css['projects'], 'body:not(.assessment-mode) .proj60-card .btn') && str_contains($css['arena'], 'min-height: 44px !important'));
$focus = substr_count($css['marketplace'] . $css['projects'] . $css['feedback'], 'outline:3px solid #2459ff!important') + substr_count($css['arena'], 'outline: 3px solid #2459ff !important');
$check('viditelný focus 3 px #2459ff přebíjí průhledný globální focus (!important)', $focus >= 4);
$check('tlumený text nepoužívá opacity (snižovala by kontrast)', !preg_match('/\.(mkt60|proj60|fb60|arena60)-(note|type|client|hint)[^{]*\{[^}]*opacity/', $allCss));

// --- 3) Produkční šablony aaPanelu -----------------------------------------------------------------------------
$dep = $root . '/docs/deploy/aapanel';
$env = $read($dep . '/educanet.env.example');
$nginx = $read($dep . '/educanet-rules.nginx.conf.example');
$doc = $read($root . '/docs/NASAZENI_AAPANEL.md');
$envVars = [];
foreach (preg_split('/\R/', $env) ?: [] as $line) if (preg_match('/^([A-Z0-9_]+)=(.*)$/', trim($line), $m)) $envVars[$m[1]] = $m[2];
$nginxVars = [];
foreach (preg_split('/\R/', $nginx) ?: [] as $line) if (preg_match('/^\s*fastcgi_param\s+(EDUCANET_[A-Z0-9_]+)\s+([^;]+);/', $line, $m)) $nginxVars[$m[1]] = trim($m[2]);
$check('env i nginx: záložní adresář /www/educanet-backup', ($envVars['EDUCANET_BACKUP_DIR'] ?? '') === '/www/educanet-backup' && ($nginxVars['EDUCANET_BACKUP_DIR'] ?? '') === '/www/educanet-backup');
$check('env i nginx: EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1 je aktivní (produkční stav)', ($envVars['EDUCANET_TEACHER_ACCOUNTS_REQUIRED'] ?? '') === '1' && ($nginxVars['EDUCANET_TEACHER_ACCOUNTS_REQUIRED'] ?? '') === '1');
$shared = array_intersect_key($envVars, $nginxVars);
$check('env a nginx nastavují stejné hodnoty všech sdílených proměnných (' . count($shared) . ')', count($shared) >= 5 && $shared === array_intersect_key($nginxVars, $envVars));
$check('env: nebezpečné volby (DEV_BYPASS, FREE_CLASS_ENTRY, EMAIL_VERIFY) zůstávají zakomentované', !isset($envVars['EDUCANET_DEV_BYPASS']) && !isset($envVars['EDUCANET_ALLOW_FREE_CLASS_ENTRY']) && !isset($envVars['EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY']));
$oldPath = substr_count($doc, '/www/backup/educanet');
$check('NASAZENI_AAPANEL.md: mkdir/chown/restore používají /www/educanet-backup; stará cesta jen jako varování', str_contains($doc, 'mkdir -p /www/server/educanet /www/educanet-backup ') && str_contains($doc, 'chown www:www /www/educanet-backup') && str_contains($doc, '--from=/www/educanet-backup/<záloha>') && $oldPath === 1 && str_contains($doc, '(ne `/www/backup/educanet`'));
$cron = $read($dep . '/educanet-cron.sh.example');
$check('cron šablona: záloha bere adresář z env souboru (žádná pevná cesta) a běží jako www', str_contains($cron, 'tools/backup_storage.php') && !str_contains($cron, '/www/backup') && str_contains($cron, 'runuser -u www'));

$check('profil v60.2: CSS ≤ 24 KB (' . strlen($css['profile']) . ' B), JS ≤ 6 KB, CSS bez @import', strlen($css['profile']) <= 24576 && !str_contains($css['profile'], '@import') && filesize($root . '/assets/profile-v60.js') <= 6144);
exit(audit_summary($state, 'V60_FOLLOWUP'));
