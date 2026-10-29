<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v81 · audit modernějšího vzhledu (jen prezentace).
 *   1) statika: sprite ikon (jeden soubor, currentColor, bez skriptů a cizích odkazů), pomocníci ui_v81.php (escapování, nejvýš jedna primární
 *      akce, nápověda skrytá bez JS, stavy s rolí), CSS jen s tokeny a breakpointy v78 + prefers-reduced-motion, JS bez innerHTML a s localStorage
 *      v try/catch, rozpočty nových souborů na stránku (CSS ≤ 15 kB gzip, JS ≤ 5 kB gzip), dokumentace,
 *   2) HTTP: hlavička (nadpis + účel + primární akce) na hlavních stránkách, nápovědy, přepínač hustoty, ikony, toast bez JS (hláška v textu),
 *      kouč v47 jen na podrobném progresu, Domů s jedním primárním tlačítkem,
 *   3) prohlížeč (lib/v81_visual_browser.cjs): ikony se vykreslí, hustota se přepne a pamatuje, nápověda jde zavřít a zůstane zavřená,
 *      toast s JS / hláška bez JS, reduced-motion vypne přechody; volitelně snímky (EDUCANET_V81_SHOTS=<adresář>).
 *   php tools/v81_visual_audit.php      Prohlížeč: EDUCANET_V81_BROWSER=require|skip.
 * Dočasné úložiště (edu_audit_temp_storage), fiktivní žáci; ostrou storage/ nikdy nečte ani nezapisuje. Konec: V81_VISUAL_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v81-visual')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/ops/runtime_content.php', 'app/lib.php', 'src/ui/ui_v67.php', 'src/ui/nav_v61.php', 'src/student/identity_v58.php',
    'src/teacher/cockpit_v70.php', 'src/teacher/teacher_prefs_v72_1.php', 'src/assessment/competencies_v62.php', 'src/assessment/evidence_v62.php', 'src/assessment/mastery_v62.php', 'src/lessons/lesson_model_v71_sources.php', 'src/lessons/lesson_model_v71.php', 'src/assessment/competencies_v75_views.php',
    'src/assessment/taskbank_v75_views.php', 'src/assessment/taskbank_v76.php', 'src/lessons/learning_v80_views.php', 'src/ui/widgets_v80_views.php', 'src/student/progress_v80_views.php', 'src/student/retention_v76.php', 'src/projects/projects_v65.php',
    'src/teacher/classmap_v80_1.php', 'src/student/support_v80_1.php', 'src/student/support_v80_1_teacher.php', 'src/student/support_v80_1_views.php', 'src/teacher/classmap_v80_1_views.php', 'src/teacher/insights_v74_views.php', 'src/student/student_v55.php', 'src/ui/ui_v81.php'] as $lib) require_once $ROOT . '/' . $lib;
app_require_libs(['core', 'layout', 'paths', 'projects65']);
require_once __DIR__ . '/lib/v80_1_fixture.php';

const V81A_KEY = 'v81-visual-audit-key';
const V81A_CSS_GZ = 15360;   // nové CSS v81 na stránku (gzip)
const V81A_JS_GZ = 5120;     // nové JS v81 na stránku (gzip)

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$gz = static fn(string $s): int => strlen((string)gzencode($s, 6));
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-') && STORAGE_DIR === $tmp);

// ---------------------------------------------------------------- 1) statika: sprite a pomocníci
$svg = $read('assets/icons-v81.svg');
$xml = $svg !== '' ? @simplexml_load_string($svg) : false;
$ids = preg_match_all('~<symbol id="i-([a-z]+)" viewBox="0 0 24 24">~', $svg, $mm) ? $mm[1] : [];
$check('sprite assets/icons-v81.svg: platné XML, jeden soubor se ' . count($ids) . ' symboly = UI81_ICONS, tah currentColor, bez výplně barvou, skriptů a cizích odkazů, < 8 kB',
    $xml !== false && $ids === UI81_ICONS && str_contains($svg, 'stroke:currentColor') && !preg_match('~<script|href=|url\(|#[0-9a-f]{3,6}\b~i', $svg) && strlen($svg) < 8192);
$icon = i81('ok');
$check('i81(): aria-hidden, focusable=false, odkaz na sprite s verzí; neznámá ikona = prázdný řetězec (nic se neinterpoluje)',
    str_contains($icon, 'aria-hidden="true"') && str_contains($icon, 'focusable="false"') && preg_match('~<use href="assets/icons-v81\.svg\?v=\d+#i-ok"/>~', $icon) === 1 && i81('<x>') === '' && i81('ok"') === '');
$head = v81_head_html('Nadpis <b>', 'Účel <i>', [['?tab=hodina', 'Akce 1', 'primary'], ['?tab=x', 'Akce 2', 'primary'], ['javascript:alert(1)', 'Zlé', 'secondary']], 'Dnes');
$check('v81_head_html(): h1 + věta o účelu, escapování, nejvýš jedna primární akce, nebezpečná URL vynechána',
    str_contains($head, '<h1>Nadpis &lt;b&gt;</h1>') && str_contains($head, '<p class="v81-head__lead">Účel &lt;i&gt;</p>') && substr_count($head, 'ds-btn--primary') === 1 && !str_contains($head, 'javascript:'));
$hint = v81_hint_html('test', 'Jak to funguje', ['Krok <1>', 'Krok 2']);
$check('v81_hint_html(): bez JS skrytá (hidden), zavírací tlačítko s aria-label, body escapované; neplatné id = nic',
    str_contains($hint, 'data-v81-hint="test"') && str_contains($hint, ' hidden>') && preg_match('~<button type="button" class="v81-hint__close" data-v81-hint-close aria-label="[^"]+">~', $hint) === 1
    && str_contains($hint, 'Krok &lt;1&gt;') && v81_hint_html('Zlé id', 'x', ['a']) === '' && v81_hint_html('ok', 'x', []) === '');
$st = v81_state_html('error', 'Chyba', 'Proč', [['?tab=attention', 'Otevřít Co řešit dnes']]) . v81_state_html('empty', 'Prázdno');
$check('v81_state_html(): chyba role=alert, prázdný stav role=status, ikona + nadpis + text + akce', str_contains($st, 'role="alert"') && str_contains($st, 'role="status"') && substr_count($st, 'class="i81"') >= 3 && str_contains($st, 'Otevřít Co řešit dnes'));
$dens = v81_density_html();
$check('v81_density_html(): skupina s popiskem, Komfortní/Kompaktní s aria-pressed, bez JS skrytá; skript hlavičky jen s try/catch',
    str_contains($dens, 'role="group" aria-label="Hustota tabulek"') && str_contains($dens, ' hidden>') && str_contains($dens, 'data-v81-density="comfortable" aria-pressed="true">Komfortní')
    && str_contains($dens, 'data-v81-density="compact" aria-pressed="false">Kompaktní') && str_contains(v81_density_head_js(), 'try{') && str_contains(v81_density_head_js(), 'catch(e){}'));

// CSS a JS
$cssFiles = ['assets/ui-v81.css', 'assets/home-v81.css', 'assets/cockpit-v81.css'];
foreach ($cssFiles as $f) {
    $css = (string)preg_replace('~/\*.*?\*/~s', '', $read($f));
    $medias = preg_match_all('~@media\s*\(\s*(?:min|max)-width:\s*(\d+)px~', $css, $mq) ? array_map('intval', $mq[1]) : [];
    $check($f . ': jen tokeny (žádné pevné barvy #hex), @media jen s breakpointy v78, prefers-reduced-motion, tisk/fokus se nerozbijí, ≤ 14 kB',
        $css !== '' && preg_match('~#[0-9a-fA-F]{3,8}\b~', $css) !== 1 && array_diff($medias, [320, 480, 768, 1024, 1366, 1920]) === [] && str_contains($css, 'prefers-reduced-motion: reduce')
        && !preg_match('~outline:\s*(?:none|0)~', $css) && strlen($read($f)) <= 14336);
}
$js = $read('assets/ui-v81.js');
$check('assets/ui-v81.js: bez innerHTML/eval/sítě, localStorage jen v try/catch, texty přes textContent, ≤ 5 kB',
    $js !== '' && !preg_match('~innerHTML|eval\(|fetch\(|XMLHttpRequest|document\.write~', $js) && preg_match('~try \{\s*if \(val === undefined\) return window\.localStorage~', $js) === 1
    && substr_count($js, 'window.localStorage') === 3 && str_contains($js, 'textContent') && strlen($js) <= 5120);
$budget = [
    'žák Domů' => [['assets/ui-v81.css', 'assets/home-v81.css'], ['assets/ui-v81.js']],
    'žák Můj pokrok' => [['assets/ui-v81.css'], ['assets/ui-v81.js']],
    'cockpit' => [['assets/ui-v81.css', 'assets/cockpit-v81.css'], ['assets/ui-v81.js']],
];
foreach ($budget as $page => [$c, $j]) {
    $cz = array_sum(array_map(static fn(string $f): int => $gz($read($f)), $c));
    $jz = array_sum(array_map(static fn(string $f): int => $gz($read($f)), $j));
    $check(sprintf('rozpočet %s: nové CSS %d B gzip (≤ %d), nové JS %d B gzip (≤ %d)', $page, $cz, V81A_CSS_GZ, $jz, V81A_JS_GZ), $cz > 0 && $cz <= V81A_CSS_GZ && $jz > 0 && $jz <= V81A_JS_GZ);
}
$layout = $read('app/views/_layout.php');
$check('kostra žáka: ui_v81.php, vrstva v81 jen na Domů a Můj pokrok (UI81_STUDENT_VIEWS) po opravách v80.2; kouč v47 jen na ?details=1',
    str_contains($layout, "require_once dirname(__DIR__, 2) . '/src/ui/ui_v81.php';") && str_contains($layout, 'in_array((string)$view, UI81_STUDENT_VIEWS, true)) echo v81_assets_html(')
    && strpos($layout, 'a11y-v80-2.css') < strpos($layout, 'v81_assets_html(') && substr_count($layout, "(string)(\$_GET['details']??'')==='1'") === 2 && substr_count($layout, "\$_GET['details']??'')==='1'") === 2);
$teacher = $read('teacher.php');
$check('toast bez JS: hláška cockpitu zůstává v textu stránky (role=status) a nese data-v81-toast; chyba zůstává viditelná i s JS',
    str_contains($teacher, '<div class="teacher-flash <?=e((string)$flash[\'type\'])?>" role="status" data-v81-toast="') && str_contains($js, "if (kind !== 'error') el.classList.add('v81-toasted')"));
$docs = ['CHANGELOG_V81.md', 'BUILD_MANIFEST_V81.md', 'docs/NAVOD_V81.md'];
$missingDocs = array_filter($docs, static fn(string $d): bool => !is_file($ROOT . '/' . $d));
$check('dokumentace: CHANGELOG_V81, BUILD_MANIFEST_V81 (Změny limitů auditů), NAVOD_V81, INSTALL.md sekce v81, DESIGN_SYSTEM_V78 §13 v81, index snímků v81-*',
    $missingDocs === [] && str_contains($read('BUILD_MANIFEST_V81.md'), 'Změny limitů auditů') && str_contains($read('INSTALL.md'), 'v81') && str_contains($read('docs/DESIGN_SYSTEM_V78.md'), '## 13.')
    && substr_count($read('docs/screens-v78/INDEX.md'), '(v81-') >= 18);
$shots81 = glob($ROOT . '/docs/screens-v78/v81-*.jpg') ?: [];
$check('snímky v81 (před/po, 3 stránky × 375/1280/1920 × světlý/tmavý): ≥ 36 souborů, každý ≤ 150 kB (' . count($shots81) . ')',
    count($shots81) >= 36 && array_filter($shots81, static fn(string $f): bool => filesize($f) > 150 * 1024) === []);

// ---------------------------------------------------------------- 2) HTTP
$fx = v801_fx_seed('class_3a', time());
unset($GLOBALS['educanet_json_request_cache']);
$A = $fx[0] ?? ['label' => ''];
$check('fixture: fiktivní žáci 3.A (' . count($fx) . ')', count($fx) >= 6 && $A['label'] !== '');
$cache = $tmp . '-lm71';
@mkdir($cache, 0700, true);
register_shutdown_function(static function () use ($cache): void { edu_audit_remove_dir($cache); });
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V81A_KEY, 'EDUCANET_TEACHER_ROLE' => 'admin', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cache];
$h = Harness::start($env);
$jar = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
$b = [];
try {
    $jar([]);
    audit_login_teacher($h, V81A_KEY, 'Audit Vzhled');
    $jarT = $jar(null);
    foreach (['t-dnes' => '/teacher.php?tab=attention', 't-hodina' => '/teacher.php?tab=hodina&class=class_3a', 't-vysledky' => '/teacher.php?tab=class_results&class=class_3a', 't-streda' => '/teacher.php?tab=streda77'] as $id => $url) {
        $r = $h->request('GET', $url);
        $b[$id] = audit_response_clean($r) ? (string)$r['body'] : '';
    }
    $page = $h->request('GET', '/teacher.php?tab=attention');
    $csrf = (string)$h->csrfToken((string)$page['body']);
    $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => 'system', 'return' => '?tab=attention', 'csrf' => $csrf], ['follow_redirects' => false]);
    $b['t-flash'] = (string)$h->request('GET', '/teacher.php?tab=attention')['body'];
    $jar([]);
    audit_login_student($h, 'class_3a', (string)$A['label']);
    foreach (['s-domu' => '/?view=dashboard', 's-detail' => '/?view=dashboard&details=1', 's-pokrok' => '/?view=pokrok', 's-materialy' => '/?view=materialy'] as $id => $url) {
        $r = $h->request('GET', $url);
        $b[$id] = audit_response_clean($r) ? (string)$r['body'] : '';
    }
} finally {
    $h->stop();
}
$check('HTTP: 9 odpovědí (učitel Co řešit dnes, Dnešní hodina, Výsledky, T-14, hláška po POST; žák Domů, podrobný progres, Můj pokrok, Materiály) 200 bez chyby', count(array_filter($b, static fn(string $x): bool => $x !== '')) === 9);
$headOk = static fn(string $html, string $cls): string|false => preg_match('~<(?:header|section) class="[^"]*' . preg_quote($cls, '~') . '[^"]*">(.*?)</(?:header|section)>~s', $html, $m) === 1
    && preg_match('~<h1[^>]*>[^<]{3,}~', $m[1]) === 1 && preg_match('~<p[^>]*>[^<]{10,}~', $m[1]) === 1 ? $m[1] : false;
$dnesHead = $headOk($b['t-dnes'], 'v81-head');
$check('hlavička Co řešit dnes: h1 „Co mám dnes řešit?“, věta o účelu, jediná primární akce „Otevřít Dnešní hodinu“ s ikonou',
    is_string($dnesHead) && str_contains($dnesHead, 'Co mám dnes řešit?') && substr_count($dnesHead, 'ds-btn--primary') === 1 && str_contains($dnesHead, '<span>Otevřít Dnešní hodinu</span>') && str_contains($dnesHead, 'icons-v81.svg'));
$check('hlavička Dnešní hodina: hero s h1, lekcí a jedinou primární akcí', preg_match('~<section class="ds78 lt791-hero"[^>]*>.*?<h1[^>]*>.*?lt791-hero__what.*?</section>~s', $b['t-hodina'], $hm) === 1 && substr_count($hm[0], 'ds-btn--primary') === 1);
foreach (['t-vysledky' => 'Výsledky', 't-streda' => 'Středa T-14'] as $id => $label) {
    $lh = $headOk($b[$id], 'v81-legacy-head');
    $check('hlavička ' . $label . ': nadpis, účel a jedna primární akce (jednotný vzor starších záložek)', is_string($lh) && substr_count($lh, 'btn primary') === 1);
    $check('hustota ' . $label . ': přepínač Komfortní/Kompaktní (bez JS skrytý) + skript hlavičky před vykreslením', str_contains($b[$id], 'data-v81-density-wrap') && preg_match('~<head>.*localStorage\.getItem\("v81-density-[0-9a-fx]{1,8}"\).*</head>~s', $b[$id]) === 1);
}
$check('hustota: na Co řešit dnes a Dnešní hodině přepínač není (jen husté tabulky)', !str_contains($b['t-dnes'], 'data-v81-density-wrap') && !str_contains($b['t-hodina'], 'data-v81-density-wrap'));
$domHead = $headOk($b['s-domu'], 'v81-head');
$check('Domů: pozdrav v hlavičce v81 (h1 + účel), karta Dnes s jediným primárním tlačítkem, kruh pokroku až v detailu, 4 dlaždice, bez patičkového Pokračovat',
    is_string($domHead) && substr_count($b['s-domu'], 'student-primary-cta') === 1 && !str_contains($b['s-domu'], 'v55-page-cta') && !str_contains($b['s-domu'], 'h81-ring__value') && preg_match('~class="h81-ring__value"><strong>\d+ %</strong>~', $b['s-detail']) === 1
    && preg_match_all('~<a class="h81-tile v81-lift" href="\?view=(ulohy|cviceni|opakovat|portfolio)">~', $b['s-domu']) === 4 && str_contains($b['s-detail'], 'href="?view=pokrok"'));
$check('Domů bez sérií: karta cílů ani výzva týdne neukazují „v sérii“ / „Série týdnů“ (motivace bez streaků, až v detailu)', !str_contains($b['s-domu'], 'mot61-streak') && !str_contains($b['s-domu'], 'Série týdnů') && !str_contains($b['s-domu'], 'Dnešní cíle') && str_contains($b['s-detail'], 'Dnešní cíle'));
$check('Domů: vrstva v81 (ui + home CSS, JS), kouč v47 se na běžném přehledu nenačítá, na ?details=1 ano', str_contains($b['s-domu'], 'assets/ui-v81.css') && str_contains($b['s-domu'], 'assets/home-v81.css') && str_contains($b['s-domu'], 'assets/ui-v81.js')
    && !str_contains($b['s-domu'], 'student-coach-v47') && str_contains($b['s-detail'], 'student-coach-v47.css') && !str_contains($b['s-materialy'], 'ui-v81.css'));
foreach (['s-pokrok' => 'pokrok', 't-dnes' => 'dnes', 't-hodina' => 'hodina'] as $id => $hid) {
    $check('nápověda „Jak to funguje“ na ' . $id . ': skrytá bez JS, se zavíracím tlačítkem, až po h1', preg_match('~<aside class="v81-hint ds78" data-v81-hint="' . $hid . '" aria-label="[^"]+" hidden>~', $b[$id]) === 1
        && strpos($b[$id], 'data-v81-hint="' . $hid . '"') > strpos($b[$id], '<h1'));
}
$check('Domů: nápověda je nativně sbalená a dostupná i bez JS', str_contains($b['s-domu'], '<details class="ds-acc h81-help"><summary>') && !str_contains($b['s-domu'], 'data-v81-hint="domu"'));
$check('ikony: hlavní stránky používají sprite (Domů ≥ 7, Co řešit dnes ≥ 8 ikon)', substr_count($b['s-domu'], 'icons-v81.svg') >= 7 && substr_count($b['t-dnes'], 'icons-v81.svg') >= 8);
$check('Co řešit dnes: mřížka priorit seřazená podle naléhavosti (ol, 6 karet, stav textem), třídy bez signálů v jednom bloku „V pořádku“, prázdné stavy s ikonou',
    preg_match_all('~<li class="v81-prio__item is-(danger|warn|info|ok)" data-v81-prio="[a-z_]+">~', $b['t-dnes']) === 6 && str_contains($b['t-dnes'], '<ol class="v81-prio__list">')
    && preg_match_all('~class="v81-chip is-[a-z]+">~', $b['t-dnes']) === 6 && (str_contains($b['t-dnes'], 'class="v81-calm ds78"') || str_contains($b['t-dnes'], 'ops-attention-class')) && str_contains($b['t-dnes'], 'v81-state ds78 is-empty'));
$check('toast po POST bez JS: hláška „Vzhled je uložený“ je v textu stránky (role=status) s data-v81-toast="ok"', preg_match('~<div class="teacher-flash ok" role="status" data-v81-toast="ok">Vzhled je uložený~', $b['t-flash']) === 1);
$check('cockpit: ui-v81.css + cockpit-v81.css + ui-v81.js načtené po opravách v80.2', strpos($b['t-dnes'], 'a11y-cockpit-v80-2.css') < strpos($b['t-dnes'], 'assets/cockpit-v81.css') && str_contains($b['t-dnes'], 'assets/ui-v81.js'));

// ---------------------------------------------------------------- 3) prohlížeč
$mode = (string)getenv('EDUCANET_V81_BROWSER');
if ($mode === 'skip') { echo 'SKIP  prohlížečová část (EDUCANET_V81_BROWSER=skip)' . PHP_EOL; exit(audit_summary($state, 'V81_VISUAL')); }
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$shots = (string)getenv('EDUCANET_V81_SHOTS');
$bh = Harness::start($env);
try {
    $cmd = [$node, __DIR__ . '/lib/v81_visual_browser.cjs', $bh->baseUrl(), V81A_KEY, (string)$A['label'], $shots !== '' ? 'all' : 'checks'];
    if ($shots !== '') { $cmd[] = $shots; $cmd[] = (string)(getenv('EDUCANET_V81_SHOT_PREFIX') ?: 'v81-after-'); }
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    $errOut = is_resource($proc) ? (string)stream_get_contents($pipes[2]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
if ((string)getenv('EDUCANET_V81_RAW') !== '') file_put_contents((string)getenv('EDUCANET_V81_RAW'), $out);   // ladění: surová data prohlížeče
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn ' . substr(trim($errOut), 0, 200);
    if ($mode === 'require') $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_V81_BROWSER=require ji vynutí)' . PHP_EOL;
    exit(audit_summary($state, 'V81_VISUAL'));
}
$check('prohlížeč: běh bez chyby a bez chyb JS na stránkách' . (isset($d['error']) ? ' [' . $d['error'] . ']' : '') . ($d['errors'] ? ' [' . implode(' | ', array_slice($d['errors'], 0, 3)) . ']' : ''), !isset($d['error']) && $d['errors'] === []);
$pg = array_column($d['pages'], null, 'id');
$check('ikony se vykreslí (velikost ≥ 12 px, aria-hidden, <use>) na Domů a Co řešit dnes; bez vodorovného posunu na 7 stránkách',
    ($pg['s-domu']['iconOk'] ?? 0) >= 7 && ($pg['t-dnes']['iconOk'] ?? 0) >= 8 && ($pg['s-domu']['icons'] ?? 0) === ($pg['s-domu']['iconOk'] ?? -1) && count(array_filter($d['pages'], static fn(array $p): bool => $p['hscroll'])) === 0 && count($d['pages']) === 7);
$check('hlavička v81 s h1, účelem a primární akcí na Co řešit dnes; Domů h1 + účel', ($pg['t-dnes']['head'] ?? false) && $pg['t-dnes']['h1'] && $pg['t-dnes']['purpose'] && $pg['t-dnes']['primary'] === 1 && ($pg['s-domu']['h1'] ?? false) && $pg['s-domu']['purpose']);
foreach ($d['density'] as $x) {
    $check('hustota ' . $x['id'] . ': viditelný přepínač, výchozí Komfortní → Kompaktní (nižší řádky) → po obnovení zůstává → zpět Komfortní',
        $x['toggle'] && $x['before'] === '' && $x['after'] === 'compact' && $x['reload'] === 'compact' && $x['back'] === '' && count($x['rowH']) === 2 && $x['rowH'][1] <= $x['rowH'][0]);
}
foreach ($d['hints'] as $x) {
    $check('nápověda ' . $x['id'] . ': poprvé viditelná, po zavření skrytá, po obnovení stránky zůstane skrytá, fokus se přesune do obsahu', $x['first'] === true && $x['closed'] === false && $x['reload'] === false && $x['focusOk'] === true);
}
$tJs = array_values(array_filter($d['toast'], static fn(array $t): bool => $t['js']))[0] ?? [];
$tNo = array_values(array_filter($d['toast'], static fn(array $t): bool => !$t['js']))[0] ?? [];
$check('toast po POST: s JS se ukáže oznámení (role=status, aria-live=polite) a inline hláška se skryje; bez JS hláška zůstane v textu a nápověda/přepínač se nevykreslí',
    ($tJs['toasts'] ?? 0) === 1 && ($tJs['live'] ?? '') === 'status/polite' && ($tJs['flashVisible'] ?? 1) === 0 && ($tNo['toasts'] ?? 1) === 0 && ($tNo['flashVisible'] ?? 0) >= 1 && ($tNo['hint'] ?? true) === false && ($tNo['toggle'] ?? true) === false);
$check('prefers-reduced-motion: přechody dlaždic a tlačítek vypnuté (běžně zapnuté)', is_array($d['motion']) && $d['motion']['reduce'] === 0 && $d['motion']['normal'] > 0);
if ($shots !== '') {
    $check('snímky: ' . count($d['shots']) . ' souborů (3 stránky × 3 šířky × 2 režimy), největší ' . (int)round($d['maxShot'] / 1024) . ' kB ≤ 150 kB', count($d['shots']) === 18 && $d['maxShot'] <= 150 * 1024);
}

exit(audit_summary($state, 'V81_VISUAL'));
