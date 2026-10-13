<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v79 · audit krokových postupů (fáze C z docs/PROMPT_V78_RESPONSIVE.md) – 7 toků jako stepper:
 *   1) soubory, rozpočty (CSS ≤ 15 kB gzip, JS ≤ 5 kB gzip), jen tokeny v78, JS bez innerHTML/sítě, registr, politiky, oprávnění,
 *   2) logika konceptu (sp79_apply): Další ověří krok, Zpět data drží, jiný rozsah = nový koncept, Zahodit, vypršení, čištění vstupu,
 *   3) HTTP (lib/v79_stepper_http.php): každý tok – kroky se vykreslí (pruh, „Krok N z M“, panely), Zpět/Další drží data, chyby u pole
 *      i v souhrnu (role=alert, aria-live), cesta bez JS (POST/GET + přesměrování), koncept nic nezapisuje do úložiště, poslední krok
 *      dá STEJNÝ výsledek v úložišti jako původní (pre-v79) požadavek – ověřeno na dvou dočasných úložištích,
 *   4) prohlížeč (lib/v79_stepper_browser.cjs, Chrome + playwright-core): 11 šířek – bez vodorovného posunu, cíle ≥ 44 px, kompaktní
 *      stepper pod 480 px kontejneru; klávesnice: kontrola kroku v JS (souhrn s fokusem, aria-invalid), Enter/mezerník, Zpět drží data.
 *   php tools/v79_stepper_audit.php     Dočasné úložiště, fiktivní data. Konec: V79_STEPPER_AUDIT_OK checks=N failed=0.
 *   Bez prohlížeče je část 4 SKIP (ne FAIL); vynutit: EDUCANET_STEPPER_BROWSER=require, přeskočit: =skip.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v79-stepper')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'cockpit_v70.php', 'tutorial_v52.php', 'learning_v56.php', 'runtime_content.php',
    'session_v53.php', 'accounts_v53.php', 'intake_v51.php', 'app/lib.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (['priprava79', 'reflexe73', 'hromadne74', 'opakovani75', 'identita'] as $m) foreach ((array)$mods[$m]['files'] as $f) require_once $ROOT . '/' . $f;
foreach (['taskbank_v75_gen.php', 'taskbank_v75.php', 'taskbank_v76.php', 'taskbank_v75_views.php', 'portfolio_v75.php', 'portfolio_v75_views.php', 'stepper_v79_student.php', 'portfolio_v79.php'] as $f) require_once $ROOT . '/' . $f;

const V79S_KEY = 'v79-stepper-audit-key';
const V79S_CSS_BUDGET = 15 * 1024;
const V79S_JS_BUDGET = 5 * 1024;

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with($tmp, $ROOT . '/storage'));

// ---------------------------------------------------------------- 1) soubory, rozpočty, registr, politiky
$php = ['stepper_v79.php', 'stepper_v79_views.php', 'stepper_v79_student.php', 'lesson_reflect_v79.php', 'bulk_approval_v79.php', 'review_v79.php', 'identity_v79.php', 'priprava_v79.php', 'portfolio_v79.php', 'app/actions/stepper_v79.php'];
$guards = array_filter($php, static fn(string $f): bool => str_contains($read($f), 'declare(strict_types=1);') && str_contains($read($f), "=== basename(__FILE__)) { http_response_code(403); exit; }"));
$check('PHP vrstvy v79: strict_types a 403 guard (' . count($php) . ' souborů)', count($guards) === count($php));
$all = array_merge($php, ['assets/stepper-v79.css', 'assets/stepper-v79.js', 'tools/v79_stepper_audit.php', 'tools/lib/v79_stepper_http.php', 'tools/lib/v79_stepper_browser.cjs',
    'lesson_reflect_v73_views.php', 'bulk_approval_v74_views.php', 'review_v75_teacher_views.php', 'identity_v58_views.php', 'taskbank_v75_views.php', 'portfolio_v75_views.php']);
$long = array_filter($all, static fn(string $f): bool => substr_count($read($f), "\n") >= 800 || $read($f) === '');
$check('soubory existují a mají do 800 řádků' . ($long ? ' [' . implode(', ', $long) . ']' : ''), $long === []);
$css = $read('assets/stepper-v79.css');
$js = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/stepper-v79.js'));
$cssGz = strlen((string)gzencode($css, 9));
$jsGz = strlen((string)gzencode($read('assets/stepper-v79.js'), 9));
$check('rozpočet fáze: CSS ' . round($cssGz / 1024, 2) . ' kB gzip (≤ 15), JS ' . round($jsGz / 1024, 2) . ' kB gzip (≤ 5)', $cssGz <= V79S_CSS_BUDGET && $jsGz <= V79S_JS_BUDGET);
$noComments = (string)preg_replace('~/\*.*?\*/~s', '', $css);
$check('CSS v79: žádné pevné barvy (hex, rgb, hsl), @media žádné, @container jen 480 px (komponenta v78), barvy jen tokeny uvnitř .ds78',
    preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', $noComments) !== 1 && !str_contains($noComments, '@media') && preg_match_all('~@container \(width < (\d+)px\)~', $noComments, $cm) === 1 && $cm[1] === ['480']
    && preg_match_all('~(?<![-\w])(color|background|border(?:-[a-z]+)?)\s*:\s*var\(--(?!space|radius|border-width)~', $noComments) > 5);
$check('CSS v79: cíle dotyku (--target-min / 44px) pro odkazy kroků, úpravy, souhrn chyb a pole formuláře; skrytý panel display:none',
    substr_count($css, 'var(--target-min)') >= 4 && str_contains($css, 'min-height: 44px') && str_contains($css, '.sp79-panel[hidden] { display: none !important; }'));
$check('JS v79: bez innerHTML/outerHTML/eval/sítě/úložiště prohlížeče; texty jen textContent; Zpět bez kontroly, fokus na souhrn',
    !preg_match('~innerHTML|outerHTML|insertAdjacentHTML|eval\(|fetch\(|XMLHttpRequest|sendBeacon|localStorage|sessionStorage|document\.write~', $js)
    && str_contains($js, 'textContent') && str_contains($js, "hasAttribute('data-sp79-validate')") && str_contains($js, 'summary.focus()') && str_contains($js, 'form.noValidate = true'));
$pp = $mods['priprava79'] ?? [];
$map = teacher68_tab_map();
$check('registr: nový modul priprava79 (Příprava hodiny, sekce Dnes, tmavý režim, „Související“), POST jen prefix sp79_, bez GET registru, soubory existují',
    array_keys((array)($pp['post'] ?? [])) === ['sp79_'] && !isset($pp['get']) && ($map['priprava79']['section'] ?? '') === 'dnes' && in_array('priprava79', UI68_TEACHER_DARK_TABS, true)
    && isset(c70_related_rules()['priprava79']) && array_filter((array)$pp['files'], static fn(string $f): bool => !is_file($GLOBALS['ROOT'] . '/' . $f)) === []);
$flowTabs = ['reflexe73' => [['lr73_'], []], 'hromadne74' => [['ba74_'], []], 'opakovani75' => [['op75_'], ['tisk']], 'identita' => [['identity58_'], []]];
$regBad = [];
foreach ($flowTabs as $tab => [$post, $getKeys]) {
    $m = $mods[$tab];
    if (array_keys((array)($m['post'] ?? [])) !== $post || array_keys((array)($m['get'] ?? [])) !== $getKeys || !in_array('assets/stepper-v79.css', (array)$m['css'], true) || !in_array('assets/stepper-v79.js', (array)($m['js'] ?? []), true)
        || !in_array('stepper_v79_views.php', (array)$m['files'], true)) $regBad[] = $tab;
}
$check('registr: toky Reflexe / Hromadné / Opakování / Identita mají stejné POST prefixy a GET registr jako dřív, jen CSS/JS a soubory stepperu' . ($regBad ? ' [' . implode(', ', $regBad) . ']' : ''), $regBad === []);
$pol = teacher59_action_policies();
$get = teacher59_get_policies();
$check('politiky: sp79_next / sp79_back / sp79_reset = třída nepovinná (je-li v požadavku, musí být v rozsahu), jiné sp79_* zakázané; oprávnění view',
    ($pol['sp79_next'] ?? null) === ['class' => 'optional'] && ($pol['sp79_back'] ?? null) === ['class' => 'optional'] && ($pol['sp79_reset'] ?? null) === ['class' => 'optional']
    && (teacher59_action_policy('sp79_save')['deny'] ?? false) === true && teacher_action_permission('sp79_next') === 'view');
$check('politiky GET: <tab>|krok jen informativní (bez omezení) pro 5 záložek, žádný nový GET registr modulů',
    array_values(array_filter(array_keys($get), static fn(string $k): bool => str_ends_with($k, '|krok'))) === ['priprava79|krok', 'reflexe73|krok', 'hromadne74|krok', 'opakovani75|krok', 'identita|krok']
    && array_filter(['priprava79|krok', 'reflexe73|krok', 'hromadne74|krok', 'opakovani75|krok', 'identita|krok'], static fn(string $k): bool => $get[$k] !== []) === []);
$routes = app_routes();
$act = array_values(array_filter($routes['actions_class'], static fn(array $s): bool => in_array('sp79_s_next', (array)$s['match'], true)));
$check('router žáka: sp79_s_next / _back / _reset v actions_class (CSRF a třída z index.php), knihovny stepperu ve skupinách stepper79 / projects65',
    count($act) === 1 && $act[0]['match'] === ['sp79_s_next', 'sp79_s_back', 'sp79_s_reset'] && in_array('stepper_v79_student.php', $routes['libs']['stepper79'], true) && in_array('portfolio_v79.php', $routes['libs']['projects65'], true));
$manifest = require $ROOT . '/lang/domains_v59.php';
$en = require $ROOT . '/lang/en/ui/stepper_v79.php';
$uk = require $ROOT . '/lang/uk/ui/stepper_v79.php';
$check('i18n žáka: doména stepper_v79 v manifestu, katalogy en/uk mají stejné klíče (' . count($en) . ')', in_array('stepper_v79_student.php', (array)($manifest['domains']['stepper_v79'] ?? []), true)
    && array_keys($en) === array_keys($uk) && isset($en['Krok {n} ze {m}'], $en['Zadání'], $en['Odkaz'], $uk['Co dál']));
$check('vyřazené formuláře v73/v75 nahradil stepper (lr73_form_html, pf75_forms_html už nejsou); úloha v opakování zůstává bez stepperu',
    !function_exists('lr73_form_html') && !function_exists('pf75_forms_html') && str_contains($read('taskbank_v75_views.php'), "if (\$returnView === 'ulohy')"));

// ---------------------------------------------------------------- 2) logika konceptu
$_SESSION = [];
$def = sp79_def('reflexe');
$base = ['class_id' => 'class_3a', 'lesson' => '4'];
$r1 = sp79_apply('reflexe', $def, 'next', 1, $base);
$r2 = sp79_apply('reflexe', $def, 'next', 1, $base + ['state' => 'cast']);
sp79_draft_put('reflexe', $r2['draft']);
$r3 = sp79_apply('reflexe', $def, 'back', 2, $base + ['state' => 'cast', 'stop_seg' => '1']);
$r4 = sp79_apply('reflexe', $def, 'next', 3, $base + ['state' => 'cast', 'worked' => str_repeat('x', 301)]);
$check('koncept: Další bez stavu = chyba u pole a zůstává krok 1; se stavem krok 2 (max 2); Zpět drží data; text 301 znaků = chyba kroku 3, text v konceptu zůstane',
    $r1['step'] === 1 && isset($r1['draft']['errors']['lr73-state-cele']) && $r2['step'] === 2 && $r2['draft']['max'] === 2 && $r3['step'] === 1 && $r3['draft']['data']['stop_seg'] === '1'
    && $r3['draft']['max'] === 2 && $r4['step'] === 3 && isset($r4['draft']['errors']['lr73-worked']) && mb_strlen($r4['draft']['data']['worked']) === 301);
$other = sp79_apply('reflexe', $def, 'next', 1, ['class_id' => 'class_3a', 'lesson' => '5', 'state' => 'cele']);
$reset = sp79_apply('reflexe', $def, 'reset', 3, $base + ['state' => 'cele']);
$check('koncept: jiná lekce = nový koncept (max 1 → 2), Zahodit = prázdná data a krok 1', $other['draft']['max'] === 2 && $other['draft']['scope'] === 'class_3a|5' && $reset['step'] === 1 && $reset['draft']['data'] === []);
sp79_draft_put('reflexe', $r2['draft'], time() - SP79_TTL - 10);
$check('koncept: po ' . (SP79_TTL / 3600) . ' h vyprší (prázdný), čerstvý platí', sp79_draft('reflexe')['data'] === [] && (sp79_draft_put('reflexe', $r2['draft']) === null) && sp79_draft('reflexe')['data']['state'] === 'cast');
$san = sp79_sanitize(['a' => ['type' => 'enum', 'values' => ['x']], 'b' => ['type' => 'int', 'range' => [1, 28]], 'c' => ['type' => 'list', 're' => '/^k\d$/', 'max' => 2], 'd' => ['type' => 'text', 'max' => 5], 'e' => ['type' => 'flag']],
    ['a' => 'y', 'b' => '40', 'c' => ['k1', 'zz', 'k2', 'k3', ['x']], 'd' => "ab\x01c", 'e' => '1']);
$check('čištění vstupu: neznámá hodnota = prázdná, číslo mimo rozsah = „x“ (chyba kroku), seznam jen podle vzoru a s limitem, text bez řídicích znaků, příznak',
    $san === ['a' => '', 'b' => 'x', 'c' => ['k1', 'k2'], 'd' => 'abc', 'e' => '1']);
$check('krok z GET: jen 1–N, jinak 0; zobrazený krok nikdy za nejdál dosaženým', sp79_get_step('reflexe', ['krok' => '4']) === 4 && sp79_get_step('reflexe', ['krok' => '5']) === 0 && sp79_get_step('reflexe', ['krok' => '2x']) === 0
    && sp79_current_step('reflexe', 2, 1, ['krok' => '4']) === 2 && sp79_current_step('reflexe', 3, 2, []) === 2);
$bar = sp79_bar_html(['A', 'B', 'C', 'D'], 2, 3, static fn(int $k): string => '?krok=' . $k, sp79_words());
$check('pruh kroků: „Krok 2 ze 4“, kompaktní „2/4“ (aria-hidden), stav hotovo / aktuální krok / čeká, aria-current=step jednou, odkazy jen na dosažené kroky',
    str_contains($bar, 'Krok 2 ze 4: <strong>B</strong>') && str_contains($bar, '<p class="sp79-mini" aria-hidden="true"><span class="sp79-frac">2/4</span>') && substr_count($bar, 'aria-current="step"') === 1
    && str_contains($bar, 'is-done') && str_contains($bar, 'hotovo') && str_contains($bar, 'čeká') && substr_count($bar, 'class="sp79-link"') === 2 && !str_contains($bar, '?krok=4'));
$errs = sp79_errors_html(['f1' => 'Chyba <b>'], sp79_words());
$check('souhrn chyb: role=alert + aria-live, fokusovatelný, odkaz na pole, text escapovaný; prázdný souhrn je skrytý (pro JS)', str_contains($errs, 'role="alert" aria-live="assertive" tabindex="-1"')
    && str_contains($errs, '<a href="#f1">Chyba &lt;b&gt;</a>') && str_contains(sp79_errors_html([], sp79_words()), 'data-sp79-errors hidden'));
$nav = sp79_nav_html('teacher', 2, 4, ['A', 'B', 'C', 'D'], sp79_words(), '');
$check('tlačítka kroku: primární „Další: C“ první v DOM (Enter), Zpět bez kontroly; oba mají formnovalidate (skryté panely neblokují odeslání bez JS)',
    strpos($nav, 'sp79_next') < strpos($nav, 'sp79_back') && str_contains($nav, '>Další: C</button>') && substr_count($nav, 'formnovalidate') === 2 && str_contains($nav, 'data-sp79-validate'));
$_SESSION = [];

require __DIR__ . '/lib/v79_stepper_http.php';

exit(audit_summary($state, 'V79_STEPPER'));
