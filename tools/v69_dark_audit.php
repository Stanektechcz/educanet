<?php

declare(strict_types=1);

/**
 * EDUCANET v69 · audit tmavé vrstvy po pohledech a kontrastu popisků cockpitu.
 *   1) assets/dark/student-dark-<pohled>-v69.css odpovídají zdrojům (--check), pro každý ui67 tmavý pohled existuje soubor, v68 soubor je pryč,
 *   2) velikost: tokeny + vrstva pohledu ≤ 15 KB gzip (úroveň 6) na stránku, každá vrstva menší než čtvrtina původních ~420 KB,
 *   3) odkaz: „tmavý“ načte jen soubor svého pohledu, „podle systému“ ho přidá skriptem, světlý nic,
 *   4) úplnost pruningu na skutečně vykreslených stránkách (přihlášení, přehled, profil): každá třída/id z HTML, která má pravidlo v CSS pohledu, je mezi použitelnými,
 *   5) AA: --ui-muted (světle i tmavě) ≥ 4,5 : 1 na všech plochách cockpitu, soubor teacher-contrast-v69.css je načtený a bez pevných barev,
 *      tmavá scéna modelu systému v Režimu hodiny drží původní paletu.
 *   php tools/v69_dark_audit.php        Konec: V69_DARK_AUDIT_OK checks=N failed=0.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v69-dark')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v69_css_usage.php';
require_once $ROOT . '/app/lib.php';
require_once $ROOT . '/ui_v67.php';

const V69D_GZIP_BUDGET = 15360;

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$views = ['home', 'dashboard', 'profile'];

// 1) aktuálnost a pokrytí
$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($ROOT . '/tools/v69_dark_overlay.php') . ' --check 2>&1';
exec($cmd, $o, $code);
$check('overlay po pohledech je aktuální (V69_OVERLAY_CURRENT): ' . trim(implode(' ', $o)), $code === 0 && str_contains(implode("\n", $o), 'V69_OVERLAY_CURRENT'));
$missing = [];
foreach (ui67_dark_views() as $v) if (!is_file($ROOT . '/' . ui69_student_dark_css($v))) $missing[] = $v;
$check('každý tmavý pohled (ui67_dark_views) má svůj soubor' . ($missing ? ' [chybí: ' . implode(', ', $missing) . ']' : ''), $missing === [] && ui67_dark_views() === $views);
$check('původní assets/student-dark-v68.css (420 KB) už není v assets', !is_file($ROOT . '/assets/student-dark-v68.css'));

// 2) velikost
$tokensGz = strlen((string)gzencode($read('assets/tokens-dark-v67.css'), 6));
$sizes = [];
foreach ($views as $v) {
    $css = $read(ui69_student_dark_css($v));
    $sizes[$v] = [strlen($css), strlen((string)gzencode($css, 6)) + $tokensGz];
}
$over = array_filter($sizes, static fn(array $s): bool => $s[1] > V69D_GZIP_BUDGET || $s[0] > 110000);
echo 'INFO  tmavé CSS (raw B / gzip B vč. tokenů): ' . implode(', ', array_map(static fn(string $v): string => $v . ' ' . $sizes[$v][0] . '/' . $sizes[$v][1], $views)) . PHP_EOL;
$check('tmavé CSS na stránku ≤ 15 KB gzip a < 110 KB raw (' . implode(', ', array_map(static fn(string $v): string => $v . '=' . $sizes[$v][1], $views)) . ')', $over === []);

// 3) odkazy
$darkHome = ui67_dark_link_html('home', 'dark');
$sys = ui67_dark_link_html('profile', 'system');
$check('odkaz: „tmavý“ načte tokeny + jen vrstvu svého pohledu, „podle systému“ skript, „světlý“ nic, pohled mimo seznam nic',
    str_contains($darkHome, 'assets/dark/student-dark-home-v69.css') && !str_contains($darkHome, 'dashboard-v69') && !str_contains($darkHome, 'profile-v69')
    && str_contains($sys, 'student-dark-profile-v69.css') && !str_contains($sys, '<link') && ui67_dark_link_html('dashboard', 'light') === '' && ui67_dark_link_html('materialy', 'dark') === '');
$check('ui69_student_dark_css přijme jen název pohledu (žádná cesta ani speciální znaky)', ui69_student_dark_css('../x') === '' && ui69_student_dark_css('a b') === '' && ui69_student_dark_css('home') === 'assets/dark/student-dark-home-v69.css');

// 4) úplnost na vykreslených stránkách
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1']);
$pages = [];
try {
    $pages['home'] = (string)$h->request('GET', '/?view=home')['body'];
    audit_login_student($h, 'class_3a', 'Audit Žák');
    $pages['dashboard'] = (string)$h->request('GET', '/?view=dashboard')['body'];
    // profil je pro čerstvého žáka přesměrovaný na přehled → třídy se berou ze šablon profilu (literály class="…" v profile_v60*.php)
    foreach (['profile_v60.php', 'profile_v60_views.php', 'growth_v67_views.php'] as $tpl) if (preg_match_all('~class="([^"<>?]*)"~', (string)file_get_contents($ROOT . '/' . $tpl), $pm) > 0) $pages['profile'] = ($pages['profile'] ?? '') . implode(' ', array_map(static fn(string $c): string => 'class="' . $c . '"', $pm[1]));
} finally {
    $h->stop();
}
$index = u69_index($ROOT);
require_once __DIR__ . '/v69_dark_overlay.php';
foreach ($views as $v) {
    $html = $pages[$v] ?? '';
    [$words, $prefixes] = do69_view_words($ROOT, $v);
    $rendered = [];
    if (preg_match_all('~\b(?:class|id)="([^"]*)"~', $html, $m) > 0) foreach ($m[1] as $attr) foreach (preg_split('~\s+~', trim($attr)) ?: [] as $c) if ($c !== '' && !str_contains($c, '<')) $rendered[$c] = true;
    $css = '';
    foreach (DO69_VIEWS[$v]['css'] as $name) $css .= $read('assets/' . $name);
    $cssNames = [];
    if (preg_match_all('~[.#]([A-Za-z_-][A-Za-z0-9_-]*)~', (string)preg_replace('~/\*.*?\*/~s', '', $css), $cm) > 0) foreach ($cm[1] as $n) $cssNames[$n] = true;
    $lost = [];
    foreach (array_keys($rendered) as $c) if (isset($cssNames[$c]) && !u69_name_used($c, $words, $prefixes)) $lost[] = $c;
    $check('úplnost pruningu: pohled ' . $v . ' (' . count($rendered) . ' tříd/id v HTML) – žádná vykreslená třída s pravidlem nechybí' . ($lost ? ' [' . implode(', ', array_slice($lost, 0, 10)) . ']' : ''), count($rendered) > 15 && $lost === []);
}

// 5) AA a scéna
$hex = static function (string $css, string $var): string { return preg_match('/--' . preg_quote($var, '/') . '\s*:\s*(#[0-9a-fA-F]{3,6})\s*;/', $css, $m) === 1 ? $m[1] : ''; };
$light = $read('assets/tokens-v61.css');
$dark = $read('assets/tokens-dark-v67.css');
$surfaces = ['#ffffff', '#f6f7f9', '#f2f5f7', '#eef2f5', '#f3f6f9', '#f8fafb', '#fff8e8', '#edf8f2', '#f1f1fe'];
$weak = [];
foreach ($surfaces as $s) { $r = audit_contrast_ratio($hex($light, 'ui-muted'), $s); if ($r === null || $r < 4.5) $weak[] = 'světlý ' . $s; }
foreach (['ui-bg', 'ui-surface', 'ui-surface-2'] as $bg) { $r = audit_contrast_ratio($hex($dark, 'ui-muted'), $hex($dark, $bg)); if ($r === null || $r < 4.5) $weak[] = 'tmavý ' . $bg; }
$check('AA: --ui-muted ≥ 4,5 : 1 na plochách cockpitu (' . count($surfaces) . ' světlých, 3 tmavé)' . ($weak ? ' [' . implode(', ', $weak) . ']' : ''), $weak === []);
$contrast = $read('assets/teacher-contrast-v69.css');
$noComment = (string)preg_replace('~/\*.*?\*/~s', '', $contrast);
$check('teacher-contrast-v69.css: ≥ 25 selektorů, jediná deklarace color: var(--ui-muted), bez pevných barev, načítá se v rámci cockpitu',
    substr_count($noComment, ',') >= 25 && preg_match('~\{\s*color:\s*var\(--ui-muted\);\s*\}~', $noComment) === 1 && preg_match('~#[0-9a-fA-F]{3,6}\b|rgba?\(~', $noComment) !== 1
    && str_contains($read('teacher_shell_v68.php'), 'assets/teacher-contrast-v69.css'));
$td = $read('assets/teacher-dark-v68.css');
$check('Režim hodiny: tmavá scéna .v45-system-stage drží v tmavém režimu původní paletu (tmavé pozadí #0b1020, světlý text)',
    preg_match('~\.v45-system-stage\s*\{[^}]*--k579:\s*#0b1020[^}]*--k135:\s*#e2e8f0~s', $td) === 1);

exit(audit_summary($state, 'V69_DARK'));
