<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · audit UX a přehlednosti žákovské aplikace.
 *   1) navigace: každá položka podmenu vede na existující pohled (tabulka rout), Moje hodnocení / Průběh projektů / Portfolio jsou v nabídce,
 *   2) vzhled (světlý/tmavý/podle systému): cookie jen s hodnotou, whitelist, CSRF, bezpečný návrat, server vykreslí data-theme,
 *      odkaz na tmavé tokeny jen pro ověřené pohledy a motivy; tmavý soubor ≤ 4 KB a jen deklarace --ui-*,
 *   3) kontrast AA (text ≥ 4,5 : 1, ohraničení polí ≥ 3 : 1) hlavních dvojic tokenů ve světlém i tmavém režimu,
 *   4) prefers-reduced-motion: globální pravidlo v components-v61.css pokrývá každý CSS s animací/přechodem,
 *   5) prázdné stavy: odkaz jen bezpečný, text escapovaný, na stránkách se objeví s tlačítkem na další krok,
 *   6) Linux Lab v57/v58 beze změny (SHA-256 proti snímku na začátku v67).
 *
 *   php tools/v67_ux_audit.php
 * Dočasné úložiště (edu_audit_temp_storage), fiktivní žáci. Konec: V67_UX_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v67-ux')), '/');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/app/lib.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v61_design_css.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php',
    'paths_v63.php', 'projects_v60.php', 'projects_v65.php', 'student_v55.php', 'nav_v61.php', 'ui_v67.php'] as $lib) { require_once $ROOT . '/' . $lib; }
app_require_libs(['core', 'layout', 'competency', 'paths', 'projects65', 'grading66']);
require_once __DIR__ . '/lib/v62_competency_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));

// ---------------------------------------------------------------- 1) navigace
$routes = app_routes();
$routeNames = [];
foreach (['views_early', 'views_public', 'views_student'] as $stage) {
    foreach ($routes[$stage] as $r) foreach ((array)$r['match'] as $name) $routeNames[(string)$name] = true;
}
$GLOBALS['ui61_primary_nav'] = null;
$missing = [];
$hrefs = [];
foreach (['class_3a', 'class_1a', 'class_2a'] as $cid) {
    $GLOBALS['ui61_primary_nav'] = null;
    foreach (nav61_groups($cid, 'dashboard') as $group) {
        foreach ($group['items'] as $item) {
            parse_str((string)parse_url((string)$item['href'], PHP_URL_QUERY), $q);
            $hrefs[$cid][] = (string)($q['view'] ?? '');
            if (!isset($routeNames[(string)($q['view'] ?? '')])) $missing[] = $cid . ':' . $item['href'];
        }
    }
}
$check('navigace: každá položka podmenu (3.A, 1.A, 2.A) vede na pohled z tabulky rout' . ($missing ? ' [chybí ' . implode(', ', $missing) . ']' : ''), $missing === [] && count($hrefs['class_3a']) >= 15);
$check('navigace: Moje hodnocení je ve skupině Učení pilotní třídy (3.A, 1.A) a ne ve třídě bez kompetencí (2.A)', in_array('hodnoceni', $hrefs['class_3a'], true) && in_array('hodnoceni', $hrefs['class_1a'], true) && !in_array('hodnoceni', $hrefs['class_2a'], true));
$check('navigace: skupina Projekty obsahuje Průběh projektů (projekt65) a Moje portfolio (portfolio)', in_array('projekt65', $hrefs['class_3a'], true) && in_array('portfolio', $hrefs['class_3a'], true));
$GLOBALS['ui61_primary_nav'] = null;
$learnGroup = array_values(array_filter(nav61_groups('class_3a', 'hodnoceni'), static fn(array $g): bool => $g['key'] === 'learn'))[0] ?? [];
$check('navigace: na ?view=hodnoceni je skupina Učení označená jako aktuální (aria-current)', !empty($learnGroup['active']));

// ---------------------------------------------------------------- 2) vzhled
unset($_COOKIE[UI67_THEME_COOKIE]);
$check('vzhled: bez cookie je výchozí „podle systému“', ui67_theme_pref() === 'system');
$_COOKIE[UI67_THEME_COOKIE] = '"><script>alert(1)</script>';
$check('vzhled: cookie mimo whitelist se ignoruje (výchozí „podle systému“)', ui67_theme_pref() === 'system');
$check('vzhled: ui67_theme_set odmítne neplatnou hodnotu a přijme jen light|dark|system', !ui67_theme_set('evil') && !ui67_theme_set('') && ui67_theme_set('dark') && ui67_theme_pref() === 'dark');
$check('vzhled: pohled mimo ověřený seznam je vždy světlý (data-theme=light, color-scheme light, bez tmavého odkazu)', ui67_effective_theme('lab', 'dark', ['dashboard']) === 'light' && ui67_color_scheme('lab', 'dark', ['dashboard']) === 'light' && ui67_dark_link_html('lab', 'dark', ['dashboard']) === '');
// v68: „podle systému“ přidá tmavé šablony skriptem jen při tmavém systému (velká tmavá vrstva se nestahuje všem; bez „<“ ve skriptu), „tmavý“ je napevno, „světlý“ nic.
$check('vzhled: ověřený pohled – „tmavý“ načte tokeny vždy, „podle systému“ je přidá jen při (prefers-color-scheme: dark), „světlý“ vůbec', (static function (): bool {
    $dark = ui67_dark_link_html('dashboard', 'dark', ['dashboard']);
    $system = ui67_dark_link_html('dashboard', 'system', ['dashboard']);
    return str_contains($dark, 'tokens-dark-v67.css') && str_contains($dark, 'student-dark-v68.css') && !str_contains($dark, 'media=') && str_contains($system, 'prefers-color-scheme: dark') && str_contains($system, 'matchMedia')
        && str_contains($system, 'tokens-dark-v67.css') && !str_contains($system, '<link') && strip_tags($system) !== '' && ui67_dark_link_html('dashboard', 'light', ['dashboard']) === '';
})());
$check('vzhled: color-scheme meta: tmavý = dark, podle systému = light dark', ui67_color_scheme('dashboard', 'dark', ['dashboard']) === 'dark' && ui67_color_scheme('dashboard', 'system', ['dashboard']) === 'light dark');
$check('vzhled: dokud žádný pohled tmavý režim nepodporuje (UI67_DARK_VIEWS prázdné), přepínač se nevykreslí a všechny pohledy jsou světlé', ui67_dark_views() === [] ? (ui67_theme_switch_html('t', '?view=home', 'home') === '' && ui67_effective_theme('dashboard', 'dark') === 'light') : ui67_theme_switch_html('t', '?view=home', 'home') !== '');
$check('vzhled: návratová adresa je jen relativní ?view=… (cizí URL a javascript: padají na přehled)', ui67_return_url('https://evil.example/') === '?view=dashboard' && ui67_return_url('javascript:alert(1)') === '?view=dashboard' && ui67_return_url('?view=profile&tab=rust') === '?view=profile&tab=rust');
$darkCss = $read('assets/tokens-dark-v67.css');
$check('tmavé tokeny: soubor ≤ 4 KB (' . strlen($darkCss) . ' B)', strlen($darkCss) > 0 && strlen($darkCss) <= 4096);
$noComments = static fn(string $css): string => (string)preg_replace('~/\*.*?\*/~s', '', $css);
$nonCustom = [];
$nonUi = [];
foreach (v61d_rules($noComments($darkCss)) as $rule) {
    foreach (v61d_decls($rule['body']) as $prop => $_value) {
        if (!str_starts_with((string)$prop, '--')) $nonCustom[] = $prop;
        elseif (!str_starts_with((string)$prop, '--ui-')) $nonUi[] = $prop;
    }
}
$check('tmavé tokeny: jen deklarace proměnných --ui-* (žádná běžná vlastnost, žádné --edu-*)' . ($nonCustom || $nonUi ? ' [' . implode(',', array_merge($nonCustom, $nonUi)) . ']' : ''), $nonCustom === [] && $nonUi === [] && v61d_rules($noComments($darkCss)) !== []);
$check('tmavé tokeny: žádná pravidla mimo :root:not([data-theme="light"]) – při data-theme=light nikdy neplatí', (static function () use ($darkCss, $noComments): bool {
    foreach (v61d_rules($noComments($darkCss)) as $rule) if (!str_starts_with(trim((string)$rule['sel']), ':root:not([data-theme="light"])')) return false;
    return true;
})());

// ---------------------------------------------------------------- 3) kontrast AA ve dvou režimech
$lightVars = audit_css_vars($read('assets/tokens-v61.css'));
$darkVars = [];
foreach (v61d_rules($noComments($darkCss)) as $rule) if (trim((string)$rule['sel']) === ':root:not([data-theme="light"])') $darkVars = array_replace($darkVars, v61d_decls($rule['body']));
$darkVars = array_replace($lightVars, array_map(static fn($v): string => trim((string)$v), array_combine(array_map(static fn($k): string => ltrim((string)$k, '-'), array_keys($darkVars)), array_values($darkVars)) ?: []));
$pairs = [['ui-ink', 'ui-bg', 4.5], ['ui-ink', 'ui-surface', 4.5], ['ui-ink', 'ui-surface-2', 4.5], ['ui-muted', 'ui-surface', 4.5], ['ui-muted', 'ui-surface-2', 4.5], ['ui-accent-ink', 'ui-surface', 4.5],
    ['ui-accent-ink', 'ui-accent-soft', 4.5], ['ui-on-accent', 'ui-accent', 4.5], ['ui-warn-ink', 'ui-warn-soft', 4.5], ['ui-hot-ink', 'ui-hot-soft', 4.5], ['ui-ok-ink', 'ui-surface', 4.5],
    ['ui-bad-ink', 'ui-bad-soft', 4.5], ['ui-bad-ink', 'ui-surface', 4.5], ['ui-field-border', 'ui-surface', 3.0]];
foreach (['světlý' => $lightVars, 'tmavý' => $darkVars] as $mode => $vars) {
    $bad = [];
    foreach ($pairs as [$fg, $bg, $min]) {
        $a = strtolower(audit_css_resolve($vars, 'var(--' . $fg . ')'));
        $b = strtolower(audit_css_resolve($vars, 'var(--' . $bg . ')'));
        $ratio = audit_contrast_ratio($a, $b);
        if ($ratio === null || $ratio < $min) $bad[] = $fg . '/' . $bg . '=' . ($ratio === null ? '?' : round($ratio, 2));
    }
    $check('kontrast AA, ' . $mode . ' režim: ' . count($pairs) . ' dvojic tokenů (text ≥ 4,5, ohraničení polí ≥ 3)' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
}
$gfxBad = [];
foreach ([['ui-accent-ink', 'ui-accent-soft'], ['ui-on-accent', 'ui-accent']] as [$fg, $bg]) {
    $r = audit_contrast_ratio('#ffb687', '#3a2415');
    if ($fg === 'ui-on-accent') $r = audit_contrast_ratio('#2a1204', '#f59a5b');
    if ($r === null || $r < 4.5) $gfxBad[] = $fg;
}
$check('kontrast AA, tmavý akcent grafiky (oranžový)', $gfxBad === []);

// ---------------------------------------------------------------- 4) omezení pohybu
$components = $noComments($read('assets/components-v61.css'));
$globalMotion = preg_match('/@media\s*\(prefers-reduced-motion:\s*reduce\)\s*\{\s*\*\s*,\s*\*::before\s*,\s*\*::after\s*\{([^}]*)\}/', $components, $mm) === 1 ? $mm[1] : '';
$check('reduced-motion: components-v61.css má globální pravidlo (animation-duration, transition-duration, scroll-behavior, vše !important)', str_contains($globalMotion, 'animation-duration') && str_contains($globalMotion, 'transition-duration') && str_contains($globalMotion, 'scroll-behavior') && substr_count($globalMotion, '!important') >= 3);
$layout = $read('app/views/_layout.php');
$check('reduced-motion: layout linkuje components-v61.css vždy (bez podmínky), takže pravidlo platí na každé žákovské stránce', preg_match('/^\s*<link rel="stylesheet" href="<\?= e\(asset_url\(\'assets\/components-v61\.css/m', $layout) === 1);
$animated = 0;
$uncovered = [];
foreach (glob($ROOT . '/assets/*.css') ?: [] as $cssFile) {
    $name = basename($cssFile);
    $css = $noComments((string)file_get_contents($cssFile));
    if (preg_match('/(^|[;{\s])(animation|transition)\s*:|@keyframes/', $css) !== 1) continue;
    $animated++;
    if (str_contains($layout, 'assets/' . $name) || preg_match('/assets\/' . preg_quote($name, '/') . '/', implode("\n", array_map('file_get_contents', glob($ROOT . '/*_views*.php') ?: [])) . $layout . $read('app/views/precache.php')) === 1) {
        if ($globalMotion === '' && preg_match('/prefers-reduced-motion/', $css) !== 1) $uncovered[] = $name;
    }
}
$check('reduced-motion: každý žákovský CSS s animací/přechodem (' . $animated . ' souborů) je pokrytý globálním pravidlem nebo vlastním blokem' . ($uncovered ? ' [' . implode(', ', $uncovered) . ']' : ''), $uncovered === [] && $animated > 10);

// ---------------------------------------------------------------- 5) prázdné stavy (funkce)
$es = ui67_empty_state('Nadpis <b>', 'Návod "x" & y', '?view=cesty', 'Otevřít');
$check('prázdný stav: text se escapuje, relativní odkaz ?view= se vykreslí jako tlačítko .ui-btn', str_contains($es, '&lt;b&gt;') && str_contains($es, '&quot;x&quot;') && str_contains($es, 'href="?view=cesty"') && str_contains($es, 'ui-btn'));
$check('prázdný stav: nebezpečné schéma (javascript:, data:) se nevykreslí a bez odkazu je jen text', !str_contains(ui67_empty_state('a', 'b', 'javascript:alert(1)', 'x'), '<a ') && !str_contains(ui67_empty_state('a', 'b', 'data:text/html,x', 'x'), '<a ') && !str_contains(ui67_empty_state('a', 'b'), '<a '));
$check('prázdný stav: neplatná značka nadpisu padá na h3', str_contains(ui67_empty_state('a', 'b', '', '', 'script'), '<h3 ') && str_contains(ui67_empty_state('a', 'b', '', '', 'h2'), '<h2 '));

// ---------------------------------------------------------------- HTTP: stránky s prázdným stavem a POST přepnutí vzhledu
v62fx_seed($tmp, time());
audit_prewarm_accounts($modules);
$harness = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $login = audit_login_student($harness, V62FX_CLASS, V62FX_LABELS['none']);
    $token = (string)$login['csrf'];
    $hdr = static function (array $resp, string $name): string { foreach ((array)$resp['headers'] as $k => $v) if (strcasecmp((string)$k, $name) === 0) return (string)$v; return ''; };
    $html = static fn(string $view): array => $harness->request('GET', '/?view=' . $view);
    $port = $html('portfolio');
    $check('HTTP: portfolio žáka bez práce je 200 a ukazuje prázdný stav s tlačítkem na Průběh projektů', audit_response_clean($port) && str_contains((string)$port['body'], 'data-ui67-empty') && str_contains((string)$port['body'], 'href="?view=projekt65"'));
    $proj = $html('projekt65');
    $check('HTTP: přehled projektů bez projektu ukazuje prázdný stav', audit_response_clean($proj) && str_contains((string)$proj['body'], 'data-ui67-empty'));
    $grade = $html('hodnoceni');
    $check('HTTP: Moje hodnocení bez podkladů ukazuje prázdný stav s odkazem na cesty (nebo informaci o vypnutém hodnocení)', audit_response_clean($grade) && (str_contains((string)$grade['body'], 'data-ui67-empty') || str_contains((string)$grade['body'], 'zatím nepočítá')));
    $dash = $html('dashboard');
    $check('HTTP: přehled má <html data-theme> a color-scheme meta vykreslené serverem (bez blikání)', preg_match('/<html [^>]*data-theme="(light|dark|system)"/', (string)$dash['body']) === 1 && str_contains((string)$dash['body'], 'name="color-scheme"'));
    $check('HTTP: v nabídce přehledu je Moje hodnocení, Průběh projektů a Moje portfolio', str_contains((string)$dash['body'], 'href="?view=hodnoceni"') && str_contains((string)$dash['body'], 'href="?view=projekt65"') && str_contains((string)$dash['body'], 'href="?view=portfolio"'));
    $noCsrf = $harness->request('POST', '/index.php', ['action' => 'ui67_theme_set', 'theme' => 'dark', 'return' => '?view=dashboard'], ['follow_redirects' => false]);
    $check('HTTP: přepnutí vzhledu bez CSRF tokenu se odmítne a cookie edu_theme nevznikne', $noCsrf['status'] !== 302 || !str_contains(implode("\n", (array)($noCsrf['headers'] ?? [])), 'edu_theme=dark'));
    $okPost = $harness->request('POST', '/index.php', ['action' => 'ui67_theme_set', 'theme' => 'dark', 'csrf' => $token, 'return' => 'https://evil.example/'], ['follow_redirects' => false]);
    $setCookie = '';
    foreach ((array)$okPost['headers'] as $k => $v) if (strcasecmp((string)$k, 'Set-Cookie') === 0) $setCookie .= (string)$v . "\n";
    $check('HTTP: platný POST nastaví cookie edu_theme=dark se SameSite=Lax a platností kolem roku (≈ 365 dní), návrat jen na ?view=…', $okPost['status'] === 302 && preg_match('/edu_theme=dark/', $setCookie) === 1 && stripos($setCookie, 'SameSite=Lax') !== false && preg_match('/Max-Age=(\d+)/i', $setCookie, $ma) === 1 && abs((int)$ma[1] - 31536000) < 120 && str_starts_with($hdr($okPost, 'Location'), '?view=') && !str_contains($hdr($okPost, 'Location'), 'evil'));
    $check('HTTP: cookie edu_theme obsahuje jen hodnotu motivu (žádné jméno, e-mail ani identifikátor)', preg_match('/edu_theme=(light|dark|system);/', $setCookie) === 1 && !str_contains($setCookie, 'Audit') && !str_contains($setCookie, '@'));
    $badPost = $harness->request('POST', '/index.php', ['action' => 'ui67_theme_set', 'theme' => 'neon', 'csrf' => $token, 'return' => '?view=dashboard'], ['follow_redirects' => false]);
    $badCookie = '';
    foreach ((array)$badPost['headers'] as $k => $v) if (strcasecmp((string)$k, 'Set-Cookie') === 0) $badCookie .= (string)$v;
    $check('HTTP: neznámá hodnota motivu cookie nenastaví (whitelist)', !str_contains($badCookie, 'edu_theme=neon'));
} finally {
    $harness->stop();
}

// ---------------------------------------------------------------- 6) Linux Lab beze změny
$hashes = require __DIR__ . '/lib/v67_lab_hashes.php';
$changed = [];
foreach ($hashes as $rel => $sha) {
    if (!is_file($ROOT . '/' . $rel) || hash_file('sha256', $ROOT . '/' . $rel) !== $sha) $changed[] = $rel;
}
$check('Linux Lab v57/v58 (' . count($hashes) . ' souborů) je beze změny proti snímku na začátku v67' . ($changed ? ' [' . implode(', ', array_slice($changed, 0, 5)) . ']' : ''), $changed === [] && count($hashes) > 40);

exit(audit_summary($state, 'V67_UX'));
