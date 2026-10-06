<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v68 · audit tmavého režimu (cockpit učitele/administrátora a žákovské pohledy přihlášení, přehled, profil).
 *   1) paleta: světlé a tmavé hodnoty mají stejné tokeny, každé var(--kN) v převedených CSS je definované,
 *   2) převedená CSS (cockpit) neobsahují pevné barvy mimo var(); odvozené kopie assets/cx-*.css a overlay assets/dark/student-dark-*-v69.css jsou aktuální,
 *   3) kontrast AA: dvojice tokenů rámce (text ≥ 4,5 : 1, ohraničení polí ≥ 3 : 1) ve světlém i tmavém režimu; u pravidel s tokeny barvy i pozadí
 *      kontrast v tmavém režimu není nižší než ve světlém a kde ve světlém splňuje AA, splňuje ho i v tmavém; totéž pro pevné dvojice žákovských CSS,
 *   4) žákovský světlý vzhled se nezměnil (SHA-256 zdrojových CSS, rozpočet CSS hlídá v61_design_audit), cockpitová CSS nenarostla o víc než 10 %,
 *   5) pohledy: UI67_DARK_VIEWS = home/dashboard/profile, UI68_TEACHER_DARK_TABS pokrývá celou mapu cockpitu, server vykreslí data-theme a tmavé CSS jen v tmavém motivu,
 *   6) vyřazený pohled v retired/v68 má shodné SHA-256, Linux Lab beze změny.
 *   php tools/v68_theme_audit.php        Konec: V68_THEME_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v68-theme')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v68_color.php';
require_once $ROOT . '/app/lib.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'ui_v67.php'] as $lib) { require_once $ROOT . '/' . $lib; }

/** Velikosti cockpitových CSS před v68 (B) a SHA-256 žákovských CSS, která v68 nesmí změnit. */
const V68T_SIZES = ['teacher.css' => 29613, 'teacher-ops-v46.css' => 18875, 'teacher-overview-v61.css' => 2962, 'teacher-accounts-v59.css' => 5119, 'lab-teacher-v58.css' => 4958, 'ops-v58.css' => 1409, 'identity-v58.css' => 2409, 'accounts-v58.css' => 6721];
const V68T_STUDENT_SHA = [
    'app.css' => '9a16b9b98494f2a343a7e363a6559bed551b43b41b2c981664d74460447f1bed', 'mastery.css' => '27355d611cef59fe7aa0da58ebdf876d940f8424ec166d3220a8cf38fe7a44a1',
    'student-ui-v50-7-7.css' => '71ade236237a47aac7a06a97f7654b75e644884bf0bc2412310ccef7286e0ba6', 'brand-v54.css' => '0b4348884a0c8b111933a546309500661afac0919ca5ff3ede80f1d4d16d5eea',
    'login-v63.css' => 'f7c02db8a26c46afb2fc146a31bd60227e65bea0b8984261a85e967dc5061931', 'student-v55.css' => 'af6c6f2f69ca14f7690a4e4d1bd5a3e28f7c9025f907a00e6efd83845d6e8309',
    'ui-v51.css' => 'c33d507419aa3097e1573108ce0f1d3bd54856dc00c6090666d7b816afcf3fcf', 'session-v53.css' => 'f1f7b4ed2ab8fc8a21b54d6ea8c99af33537ddfc4c84d28f711cb5007793bc91',
    'learning-v56.css' => '8acf4703d512ce50c9d25badb92a2010c223185618bd20bc39382826d68497fd', 'profile-v60.css' => '2c80cbe4b0f6eaa83df6a32fbd33e0a50a241b79786324e9fff13cdb09832fec',
    'components-v61.css' => 'b760f9084777b6c04bba3aebdc30bf9bdc200ab5a03cbfafd210725febf1f237', 'nav-v61.css' => '8afd79e49431e65bdd518bfcf43a7dfb67282d868688186e67cd191f114eb0ff',
    'tokens-v61.css' => 'e7905e9dc563f26c24ca3dc8fc17cc4fe25b1919229e40d59fee65e05b0b002d', 'tokens-dark-v67.css' => '5fc4fe79666dafe3c87f69920dc250eefbd03242a84c6c0b8ab2dc70e4397093',
];
const V68T_RETIRED_SHA = '675d9a59e955e7ffed7252538a331bdd50e4cdf05f2aaea2e84f8285adbe9848';
const V68T_INPLACE = ['teacher.css', 'teacher-ops-v46.css', 'teacher-overview-v61.css', 'teacher-accounts-v59.css', 'lab-teacher-v58.css', 'ops-v58.css', 'identity-v58.css', 'accounts-v58.css', 'teacher-shell-v68.css'];

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$noComments = static fn(string $css): string => (string)preg_replace('~/\*.*?\*/~s', '', $css);
/** Dynamické barvy s proměnnou uvnitř (hsl(292 var(--sat) 53%), rgba(37,99,235,var(--a))) nejde převést na token – jediná dovolená výjimka. */
$noDynamic = static fn(string $css): string => (string)preg_replace('~(?:rgba?|hsla?)\([^()]*var\([^()]*\)[^()]*\)~', '', (string)preg_replace('~url\([^)]*\)~', '', $css));
$run = static function (string $script, array $args = []) use ($ROOT): array {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($ROOT . '/tools/' . $script) . ' ' . implode(' ', array_map('escapeshellarg', $args)) . ' 2>&1';
    $out = [];
    exec($cmd, $out, $code);
    return [$code, implode("\n", $out)];
};
/** @return array<int,string> číslo tokenu → hodnota */
$palette = static function (string $css): array {
    preg_match_all('/--k(\d+)\s*:\s*([^;]+);/', $css, $m, PREG_SET_ORDER);
    return array_column(array_map(static fn(array $r): array => [(int)$r[1], trim($r[2])], $m), 1, 0);
};
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));

// ---------------------------------------------------------------- 1) paleta
$light = $palette($read('assets/tokens-palette-v68.css'));
$dark = $palette($read('assets/tokens-palette-dark-v68.css'));
$check('paleta: světlé a tmavé tokeny mají stejná čísla (' . count($light) . ' tokenů) a hodnoty jsou platné barvy', $light !== [] && array_keys($light) === array_keys($dark)
    && array_reduce(array_merge($light, $dark), static fn(bool $c, string $v): bool => $c && v68c_parse($v) !== null, true));
$used = [];
$cxFiles = array_map('basename', glob($ROOT . '/assets/cx-*.css') ?: []);
foreach (array_merge(array_map(static fn(string $f): string => 'assets/' . $f, V68T_INPLACE), array_map(static fn(string $f): string => 'assets/' . $f, $cxFiles)) as $rel) {
    if (preg_match_all('/var\(--k(\d+)\)/', $read($rel), $m) > 0) foreach ($m[1] as $n) $used[(int)$n] = $rel;
}
$undefined = array_keys(array_diff_key($used, $light));
$check('paleta: každý použitý var(--kN) je definovaný (' . count($used) . ' použitých)' . ($undefined ? ' [chybí ' . implode(',', array_slice($undefined, 0, 5)) . ']' : ''), $undefined === [] && count($used) > 100);
$check('paleta: nepoužité tokeny jsou jen rezerva (≤ 5 % palety)', count(array_diff_key($light, $used)) <= max(5, (int)(count($light) * 0.05)));

// ---------------------------------------------------------------- 2) převedená CSS
$literal = '~#[0-9a-fA-F]{3,8}\b|\brgba?\s*\(|\bhsla?\s*\(|(?<![-\w.#%])(?:white|black)(?![-\w(])~';
foreach (V68T_INPLACE as $name) {
    $css = $noDynamic($noComments($read('assets/' . $name)));
    $check('převedené CSS ' . $name . ': žádná pevná barva mimo var(); velikost ' . strlen($read('assets/' . $name)) . ' B', preg_match($literal, (string)$css) !== 1 && is_file($ROOT . '/assets/' . $name));
}
$bad = [];
foreach ($cxFiles as $name) if (preg_match($literal, $noDynamic($noComments($read('assets/' . $name)))) === 1) $bad[] = $name;
$check('odvozené kopie assets/cx-*.css (' . count($cxFiles) . '): žádná pevná barva mimo var() (výjimka: dynamické hsl()/rgba() s var() uvnitř)' . ($bad ? ' [' . implode(',', $bad) . ']' : ''), $bad === [] && count($cxFiles) >= 20);
[$code1, $out1] = $run('v68_tokenize_css.php', ['--derive', '--check']);
$check('odvozené kopie jsou aktuální vůči originálům (V68_DERIVE_CURRENT): ' . trim($out1), $code1 === 0 && str_contains($out1, 'V68_DERIVE_CURRENT'));
[$code2, $out2] = $run('v68_dark_overlay.php', ['--check']);
$check('tmavý overlay žákovské části a Labu je aktuální (V68_OVERLAY_CURRENT): ' . trim($out2), $code2 === 0 && str_contains($out2, 'V68_OVERLAY_CURRENT'));
$overlay = $read('assets/dark/student-dark-home-v69.css') . $read('assets/dark/student-dark-dashboard-v69.css') . $read('assets/dark/student-dark-profile-v69.css');   // v69: tmavá vrstva po pohledech
$prefix = 'html:not([data-theme="light"])';
$unprefixed = 0;
$rules = 0;
if (preg_match_all('~([^{}]+)\{~', $noComments($overlay), $pm) > 0) {
    foreach ($pm[1] as $prelude) {
        $prelude = trim($prelude);
        if ($prelude === '' || $prelude[0] === '@') continue;
        $rules++;
        $depth = 0;
        foreach (str_split($prelude) as $i => $ch) { if ($ch === '(' || $ch === '[') $depth++; elseif ($ch === ')' || $ch === ']') $depth--; elseif ($ch === ',' && $depth === 0 && !str_starts_with(ltrim(substr($prelude, $i + 1)), $prefix)) $unprefixed++; }
        if (!str_starts_with($prelude, $prefix)) $unprefixed++;
    }
}
$check('overlay: každý selektor má předponu tmavého režimu (' . $rules . ' pravidel, světlý vzhled se nezasahuje; soubor se linkuje jen při tmavém motivu)', $unprefixed === 0 && $rules > 1000);

// ---------------------------------------------------------------- 3) kontrast AA
$vars = static function (string $css): array {
    $raw = [];
    if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;{}]+);/', $css, $m, PREG_SET_ORDER) > 0) foreach ($m as $r) $raw[$r[1]] ??= trim($r[2]);
    $out = [];
    foreach ($raw as $name => $value) {
        for ($i = 0; $i < 4 && preg_match('/^var\(--([a-z0-9-]+)\)$/', $value, $vm) === 1; $i++) $value = $raw[$vm[1]] ?? $value;
        if (str_starts_with($value, '#')) $out[$name] = $value;
    }
    return $out;
};
$lv = $vars($read('assets/tokens-v61.css'));
$dv = array_merge($lv, $vars($noComments($read('assets/tokens-dark-v67.css'))));   // tmavé tokeny jsou přímé hodnoty --ui-*
$pairs = [['ui-ink', 'ui-bg', 4.5], ['ui-ink', 'ui-surface', 4.5], ['ui-muted', 'ui-surface', 4.5], ['ui-muted', 'ui-bg', 4.5], ['ui-accent-ink', 'ui-surface', 4.5], ['ui-accent-ink', 'ui-accent-soft', 4.5],
    ['ui-on-accent', 'ui-accent', 4.5], ['ui-warn-ink', 'ui-warn-soft', 4.5], ['ui-hot-ink', 'ui-hot-soft', 4.5], ['ui-ok-ink', 'ui-surface', 4.5], ['ui-bad-ink', 'ui-surface', 4.5], ['ui-bad-ink', 'ui-bad-soft', 4.5],
    ['ui-ink', 'ui-surface-2', 4.5], ['ui-field-border', 'ui-surface', 3.0], ['ui-field-border', 'ui-bg', 3.0]];
$fails = [];
foreach (['světlý' => $lv, 'tmavý' => $dv] as $mode => $set) {
    foreach ($pairs as [$fg, $bg, $min]) {
        $a = v68c_parse((string)($set[$fg] ?? '')); $b = v68c_parse((string)($set[$bg] ?? ''));
        if ($a === null || $b === null || v68c_ratio($a, $b) < $min) $fails[] = $mode . ' ' . $fg . '/' . $bg;
    }
}
$check('kontrast AA rámce (' . count($pairs) . ' dvojic tokenů × světlý/tmavý): text ≥ 4,5 : 1, ohraničení polí ≥ 3 : 1' . ($fails ? ' [' . implode(', ', $fails) . ']' : ''), $fails === []);
/** @return list<array{string,string,string}> [selektor, tok. barvy, tok. pozadí] z pravidel s var(--kN) u color i background */
$tokenPairs = static function (string $css): array {
    $out = [];
    if (preg_match_all('~([^{}]+)\{([^{}]*)\}~', $css, $rules, PREG_SET_ORDER) > 0) {
        foreach ($rules as $r) {
            if (preg_match('~(?<![-\w])color\s*:\s*var\(--k(\d+)\)~', $r[2], $c) === 1 && preg_match('~background(?:-color)?\s*:\s*var\(--k(\d+)\)\s*(?:;|$)~', $r[2], $b) === 1) $out[] = [trim($r[1]), $c[1], $b[1]];
        }
    }
    return $out;
};
$worse = [];
$aaLost = [];
$total = 0;
foreach (array_merge(array_map(static fn(string $f): string => 'assets/' . $f, V68T_INPLACE), array_map(static fn(string $f): string => 'assets/' . $f, $cxFiles)) as $rel) {
    foreach ($tokenPairs($noComments($read($rel))) as [$sel, $c, $b]) {
        $lc = v68c_parse($light[(int)$c] ?? ''); $lb = v68c_parse($light[(int)$b] ?? ''); $dc = v68c_parse($dark[(int)$c] ?? ''); $db = v68c_parse($dark[(int)$b] ?? '');
        if ($lc === null || $lb === null || $dc === null || $db === null || $lc[3] < 1.0 || $lb[3] < 1.0) continue;
        $total++;
        $rl = v68c_ratio($lc, $lb);
        $rd = v68c_ratio($dc, $db);
        if ($rl < 4.5 && $rd + 0.02 < $rl) $worse[] = basename($rel) . ' ' . $sel;   // dvojice pod AA (už ve světlém) se v tmavém nesmí zhoršit; AAA se nevyžaduje
        if ($rl >= 4.5 && $rd < 4.5) $aaLost[] = basename($rel) . ' ' . $sel;
    }
}
$check('kontrast pravidel s var(--kN): v tmavém režimu se dvojice pod AA nezhorší (' . $total . ' dvojic)' . ($worse ? ' [' . implode(' | ', array_slice($worse, 0, 4)) . ']' : ''), $worse === [] && $total > 150);
$check('kontrast pravidel: dvojice s AA ve světlém režimu mají AA i v tmavém' . ($aaLost ? ' [' . implode(' | ', array_slice($aaLost, 0, 4)) . ']' : ''), $aaLost === []);
$stuWorse = [];
$stuTotal = 0;
foreach (V68T_STUDENT_SHA as $name => $sha) {
    if (!str_ends_with($name, '.css') || str_starts_with($name, 'tokens')) continue;
    if (preg_match_all('~([^{}]+)\{([^{}]*)\}~', $noComments($read('assets/' . $name)), $rules, PREG_SET_ORDER) < 1) continue;
    foreach ($rules as $r) {
        if (preg_match('~(?<![-\w])color\s*:\s*(#[0-9a-fA-F]{3,8}|white|black)\b~', $r[2], $c) !== 1 || preg_match('~background(?:-color)?\s*:\s*(#[0-9a-fA-F]{3,8})\s*(?:;|!|$)~', $r[2], $b) !== 1) continue;
        $fg = v68c_parse($c[1]); $bg = v68c_parse($b[1]);
        if ($fg === null || $bg === null) continue;
        $stuTotal++;
        $rl = v68c_ratio($fg, $bg);
        $rd = v68c_ratio(v68c_dark($fg), v68c_dark($bg));
        if (($rl < 4.5 && $rd + 0.02 < $rl) || ($rl >= 4.5 && $rd < 4.5)) $stuWorse[] = $name . ' ' . trim($r[1]);
    }
}
$check('žákovské CSS: pevné dvojice color/background mají v tmavé variantě (overlay) kontrast ≥ světlého a AA se neztratí (' . $stuTotal . ' dvojic)' . ($stuWorse ? ' [' . implode(' | ', array_slice($stuWorse, 0, 4)) . ']' : ''), $stuWorse === [] && $stuTotal > 50);

// ---------------------------------------------------------------- 4) beze změny / velikost
$changed = [];
foreach (V68T_STUDENT_SHA as $name => $sha) if (hash_file('sha256', $ROOT . '/assets/' . $name) !== $sha) $changed[] = $name;
$check('žákovský světlý vzhled: zdrojová CSS beze změny (SHA-256 ' . count(V68T_STUDENT_SHA) . ' souborů; rozpočet 28 672 B nezvýšen)' . ($changed ? ' [změněno: ' . implode(', ', $changed) . ']' : ''), $changed === []);
$grew = [];
foreach (V68T_SIZES as $name => $before) if (filesize($ROOT . '/assets/' . $name) > $before * 1.10) $grew[] = $name . ' ' . filesize($ROOT . '/assets/' . $name) . '>' . $before;
$check('cockpitová CSS po převodu nenarostla o víc než 10 % (var(--kN) je delší než #fff)' . ($grew ? ' [' . implode(', ', $grew) . ']' : ''), $grew === []);

// ---------------------------------------------------------------- 5) pohledy a render
$map = teacher68_tab_map();
$check('žák: UI67_DARK_VIEWS = přihlášení, přehled, profil; přepínač vzhledu se vykresluje (CSRF, 3 tlačítka, aria-pressed)',
    ui67_dark_views() === ['home', 'dashboard', 'profile'] && preg_match('~name="csrf".*aria-pressed="(true|false)".*aria-pressed.*aria-pressed~s', ui67_theme_switch_html('tok', '?view=dashboard', 'dashboard')) === 1);
$sys = ui67_dark_link_html('profile', 'system');
$check('žák: efektivní motiv jen pro ověřené pohledy (home/dashboard/profile), ostatní světlé; tmavé CSS: „tmavý“ napevno, „podle systému“ skriptem při tmavém systému (bez <link>, bez „<“ ve skriptu), „světlý“ nic',
    ui67_effective_theme('dashboard', 'dark') === 'dark' && ui67_effective_theme('materialy', 'dark') === 'light' && ui67_dark_link_html('dashboard', 'light') === ''
    && str_contains(ui67_dark_link_html('home', 'dark'), 'assets/dark/student-dark-home-v69.css') && !str_contains(ui67_dark_link_html('home', 'dark'), 'media=') && ui67_dark_link_html('materialy', 'dark') === ''
    && str_contains($sys, 'matchMedia("(prefers-color-scheme: dark)")') && str_contains($sys, 'assets/dark/student-dark-profile-v69.css') && !str_contains($sys, '<link') && substr_count(substr($sys, 8, -9), '<') === 0);
$check('cockpit: UI68_TEACHER_DARK_TABS pokrývá celou mapu záložek (plán: celý cockpit) a neobsahuje neexistující záložky',
    array_diff(array_keys($map), UI68_TEACHER_DARK_TABS) === [] && array_diff(UI68_TEACHER_DARK_TABS, array_keys($map), ['ucet', 'sekce']) === []);
$check('cockpit: tmavý motiv jen pro záložky ze seznamu (neznámá → světlá), pref „podle systému“ se zachová',
    teacher68_effective_theme('attention', 'dark') === 'dark' && teacher68_effective_theme('attention', 'system') === 'system' && teacher68_effective_theme('neexistuje', 'dark') === 'light');
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1']);
try {
    $anon = $h->request('GET', '/?view=home');
    $check('HTTP žák: přihlášení bez cookie = data-theme="system", color-scheme „light dark“, tmavé CSS jen skriptem při tmavém systému (žádný <link> na velkou tmavou vrstvu)', audit_response_clean($anon) && str_contains((string)$anon['body'], 'data-theme="system"')
        && str_contains((string)$anon['body'], 'content="light dark"') && str_contains((string)$anon['body'], 'matchMedia') && preg_match('~<link[^>]*student-dark-[a-z]+-v69\.css~', (string)$anon['body']) !== 1);
    $csrf = (string)$h->csrfToken((string)$anon['body']);
    $set = $h->request('POST', '/', ['action' => 'ui67_theme_set', 'theme' => 'dark', 'return' => '?view=home', 'csrf' => $csrf], ['follow_redirects' => false]);
    $home = $h->request('GET', '/?view=home');
    $check('HTTP žák: volba „tmavý“ → cookie, data-theme="dark", color-scheme dark, tmavé CSS bez media dotazu', in_array((int)$set['status'], [302, 303], true) && str_contains((string)$home['body'], 'data-theme="dark"')
        && str_contains((string)$home['body'], 'content="dark"') && preg_match('~student-dark-home-v69\.css[^>]*>~', (string)$home['body'], $lm) === 1 && !str_contains($lm[0], 'media='));
    audit_login_student($h, 'class_3a', 'Audit Žák');
    $dash = $h->request('GET', '/?view=dashboard');
    $other = $h->request('GET', '/?view=vysledky');
    $check('HTTP žák: přehled je tmavý a nese přepínač, pohled mimo seznam (výsledky) zůstává světlý a bez tmavých CSS',
        audit_response_clean($dash) && str_contains((string)$dash['body'], 'data-theme="dark"') && str_contains((string)$dash['body'], 'ui67-theme') && audit_response_clean($other)
        && str_contains((string)$other['body'], 'data-theme="light"') && !str_contains((string)$other['body'], 'assets/dark/student-dark-'));
    $dashLight = $h->request('POST', '/', ['action' => 'ui67_theme_set', 'theme' => 'light', 'return' => '?view=dashboard', 'csrf' => (string)$h->csrfToken((string)$dash['body'])], ['follow_redirects' => false]);
    $dashLightPage = $h->request('GET', '/?view=dashboard');
    $check('HTTP žák: volba „světlý“ nenačítá tmavé CSS (světlé stránky nic nenavíc nestahují)', in_array((int)$dashLight['status'], [302, 303], true) && str_contains((string)$dashLightPage['body'], 'data-theme="light"') && !str_contains((string)$dashLightPage['body'], 'assets/dark/student-dark-') && !str_contains((string)$dashLightPage['body'], 'tokens-dark-v67.css'));
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- 6) vyřazení, Lab
$check('vyřazený pohled: kopie v retired/v68 má očekávané SHA-256 a originál neexistuje', is_file($ROOT . '/retired/v68/app/views/v48_state.php') && hash_file('sha256', $ROOT . '/retired/v68/app/views/v48_state.php') === V68T_RETIRED_SHA && !is_file($ROOT . '/app/views/v48_state.php'));
$hashes = require __DIR__ . '/lib/v67_lab_hashes.php';
$lab = array_keys(array_filter($hashes, static fn(string $sha, string $f): bool => !is_file($ROOT . '/' . $f) || hash_file('sha256', $ROOT . '/' . $f) !== $sha, ARRAY_FILTER_USE_BOTH));
$check('Linux Lab beze změny (SHA-256 ' . count($hashes) . ' souborů)' . ($lab ? ' [' . implode(', ', $lab) . ']' : ''), $lab === []);

exit(audit_summary($state, 'V68_THEME'));
