<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Linux Lab UI (LABUI) – audit A11Y-01..03, EDU-05, integrace jádra (LEARN/rozcestník her).
 *
 * Jen CLI, izolované dočasné úložiště (nikdy nesahá na storage/). Nezávisí na app/ (SPLIT), bootstrap.php
 * ani na relačním/účtovém systému – renderovací funkce z linux_v57_views.php se testují přímo, s lokálními
 * náhradami e()/render_header()/render_footer()/redirect_to()/adaptive_student_key() (stejný princip izolace
 * jako u ostatních v58 auditů: $GLOBALS['lab57_storage_override'], $GLOBALS['lab57_secret_override']).
 *
 * Spuštění: php tools/v58_lab_ui_audit.php   → poslední řádek V58_LAB_UI_AUDIT_OK checks=N failed=0
 * Spusť i: php tools/v57_linux_lab_audit.php (regrese jádra Labu).
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Prague');

$ROOT = dirname(__DIR__);
$TMP = sys_get_temp_dir() . '/v58_lab_ui_audit_' . bin2hex(random_bytes(6));
mkdir($TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $TMP;
$GLOBALS['lab57_secret_override'] = 'v58-lab-ui-audit-secret';
ini_set('log_errors', '1');
ini_set('error_log', $TMP . '/php_errors.log');

register_shutdown_function(static function () use ($TMP): void {
    if (!is_dir($TMP)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($TMP, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($TMP);
});

$GLOBALS['v58ui_warnings'] = [];
set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) return true;
    $GLOBALS['v58ui_warnings'][] = $str . ' (' . basename($file) . ':' . $line . ')';
    return true;
});

require_once $ROOT . '/linux_v57_lab.php';
require_once $ROOT . '/linux_v57_views.php';

// ---------------------------------------------------------------------------
// Lokální náhrady závislostí (žádný app/, bootstrap.php ani session).
// ---------------------------------------------------------------------------

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

final class V58UiRedirect extends RuntimeException {}

function redirect_to(string $url): never
{
    throw new V58UiRedirect($url);
}

function adaptive_student_key(string $classId): string
{
    return 'uiauditstudent';
}

function render_header(string $title, ?array $module = null): void
{
    echo '<!doctype html><html lang="cs"><head><title>' . e($title) . "</title></head><body><main>\n";
}

function render_footer(): void
{
    echo "</main></body></html>\n";
}

// ---------------------------------------------------------------------------
// Pomocníci auditu
// ---------------------------------------------------------------------------

$GLOBALS['v58ui'] = ['checks' => 0, 'failed' => 0];
function v58ui_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['v58ui']['checks']++;
    if (!$ok) $GLOBALS['v58ui']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}

/** Zavolá $fn, zachytí výstup i PHP varování vzniklá při renderu. @return array{html:string,warnings:list<string>} */
function v58ui_render(callable $fn): array
{
    $GLOBALS['v58ui_warnings'] = [];
    ob_start();
    $fn();
    $html = (string)ob_get_clean();
    return ['html' => $html, 'warnings' => $GLOBALS['v58ui_warnings']];
}

function v58ui_luminance(string $hex): float
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    $chan = static function (int $v) use (&$chan): float {
        $c = $v / 255;
        return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    };
    $r = $chan((int)hexdec(substr($hex, 0, 2)));
    $g = $chan((int)hexdec(substr($hex, 2, 2)));
    $b = $chan((int)hexdec(substr($hex, 4, 2)));
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

function v58ui_contrast(string $hexA, string $hexB): float
{
    $a = v58ui_luminance($hexA) + 0.05;
    $b = v58ui_luminance($hexB) + 0.05;
    return $a > $b ? $a / $b : $b / $a;
}

/** Vytáhne --lab57-<jméno>: #hex; páry z prvního bloku { … } za doslovným výskytem $selector. */
function v58ui_css_vars(string $css, string $selector): array
{
    $pos = strpos($css, $selector);
    if ($pos === false) return [];
    $open = strpos($css, '{', $pos);
    $close = $open === false ? false : strpos($css, '}', $open);
    if ($open === false || $close === false) return [];
    $block = substr($css, $open, $close - $open);
    preg_match_all('/--lab57-([a-z]+):\s*(#[0-9a-fA-F]{3,8})/', $block, $m, PREG_SET_ORDER);
    $out = [];
    foreach ($m as $row) $out[$row[1]] = $row[2];
    return $out;
}

/**
 * Vlna 1b běží paralelně – linux_v58_ext.php při require_once výše už automaticky natáhlo
 * VŠECHNY linux_v58_cmd_*.php/linux_v58_levels_*.php z kořene projektu, tedy i případné
 * již hotové soubory jiných agentů (LEARN, ARENA, TG, ROBOTS). Proto se nesmí předpokládat,
 * že lab58_badges()/arena58_*_render_student() apod. NEJSOU definované – zjistíme to a testy
 * podle toho přizpůsobíme (degradace se testuje, jen když funkce opravdu chybí; obsah přesných
 * dat testujeme, jen když ho dodáváme my sami – u reálné implementace stačí strukturální kontrola).
 */
function v58ui_registry_new_errors(array $before): array
{
    return array_values(array_diff(lab58_registry_errors(), $before));
}

const V58UI_CLASS = 'class_9z_uiaudit';
const V58UI_STUDENT = 'uiauditstudent';
const V58UI_NOW = 1790000000;

// ---------------------------------------------------------------------------
// 1) Existence veřejných funkcí LABUI
// ---------------------------------------------------------------------------

foreach ([
    'lab57_render_home', 'lab57_render_level', 'lab57_render_workspace', 'lab57_render_manual',
    'lab57_render_v58_assets', 'lab57_render_terminal_tools', 'lab57_render_topology_block',
    'lab57_render_hub_section', 'lab57_render_badges_section', 'lab57_render_review_card',
    'lab58_render_section', 'lab58_render_skill_section', 'lab58_render_review_section', 'lab57_plural_cs',
] as $fn) {
    v58ui_check('api:exists:' . $fn, function_exists($fn));
}

$module = ['accent' => 'default'];

// Zjištění, co už je (souběžně s jinými agenty vlny 1b) v tomto běhu opravdu k dispozici.
// LEARN (lab58_badges/review_today/skill_map): přes function_exists – volají se tak i z view.
// Herní rozcestník: přes is_file() na vlastnický soubor modulu (stejně jako lab57_v58_hub_items()).
$hasRealBadges = function_exists('lab58_badges');
$hasRealReview = function_exists('lab58_review_today');
$hasRealSkillMap = function_exists('lab58_skill_map');
$hubFiles = ['arena_v58_views.php', 'arena_v58_events_views.php', 'teamgames_v58_views.php', 'robots_v58_views.php'];
$anyGameReal = false;
foreach ($hubFiles as $hf) { if (is_file($ROOT . '/' . $hf)) $anyGameReal = true; }
$offlineExists = is_file($ROOT . '/lab-offline.html');
foreach (['lab58_badges' => $hasRealBadges, 'lab58_review_today' => $hasRealReview, 'lab58_skill_map' => $hasRealSkillMap] as $fn => $has) {
    if ($has) echo "INFO $fn je už definovaná souběžnou prací jiného agenta – testuje se proti reálné implementaci.\n";
}

// ---------------------------------------------------------------------------
// 2) Domovská stránka BEZ funkcí LEARN/her – musí fungovat bez chyb, s vysvětlující degradací
// ---------------------------------------------------------------------------

$homeWithout = v58ui_render(static function () use ($module): void {
    lab57_render_home(V58UI_CLASS, $module, '');
});
v58ui_check('home:no-warnings-without-learn', $homeWithout['warnings'] === [], implode(' | ', $homeWithout['warnings']));
v58ui_check('home:has-hero', str_contains($homeWithout['html'], 'lab57-hero'));
v58ui_check('home:has-packs-list', str_contains($homeWithout['html'], 'lab57-packs'));
if (!$hasRealBadges) v58ui_check('home:no-badges-without-learn', !str_contains($homeWithout['html'], 'lab57-badges'));
if (!$hasRealReview) v58ui_check('home:no-review-card-without-learn', !str_contains($homeWithout['html'], 'lab57-review-card'));
if (!$anyGameReal && !$offlineExists) v58ui_check('home:no-hub-without-games', !str_contains($homeWithout['html'], 'lab57-hub'));
if (!$hasRealSkillMap) v58ui_check('home:no-skillmap-link-without-learn', !str_contains($homeWithout['html'], 'sekce=dovednosti'));

// ---------------------------------------------------------------------------
// 3) Integrace jádra: lab58_packs_for_class – balíček podle classes se ukáže jen povolené třídě
// ---------------------------------------------------------------------------

$miniLevel = static fn(string $id): array => [
    'id' => $id, 'type' => 'code', 'title' => 'UI audit level', 'difficulty' => 1, 'points' => 10,
    'story' => 'test', 'task' => 'test',
    'generate' => [['code_file', ['dirs' => ['~'], 'names' => ['kod.txt']]]],
    'solution' => ['cat {f:code_path}', 'submit {CODE}'],
];
$errorsBefore = lab58_registry_errors();
lab58_register_pack(
    ['id' => 'v58ui-allowed', 'title' => 'V58UI Allowed Pack', 'order' => 500, 'classes' => [V58UI_CLASS]],
    static fn(): array => [$miniLevel('v58ui-allowed-1')]
);
lab58_register_pack(
    ['id' => 'v58ui-blocked', 'title' => 'V58UI Blocked Pack', 'order' => 501, 'classes' => ['class_9z_other']],
    static fn(): array => [$miniLevel('v58ui-blocked-1')]
);
$newErrors = v58ui_registry_new_errors($errorsBefore);
v58ui_check('registry:no-errors-after-test-packs', $newErrors === [], implode(' | ', $newErrors));

$homeAfterPacks = v58ui_render(static function () use ($module): void { lab57_render_home(V58UI_CLASS, $module, ''); });
v58ui_check('home:class-pack-visible', str_contains($homeAfterPacks['html'], 'V58UI Allowed Pack'));
v58ui_check('home:foreign-class-pack-hidden', !str_contains($homeAfterPacks['html'], 'V58UI Blocked Pack'));

// ---------------------------------------------------------------------------
// 4) Stránka úrovně (pracovní plocha) – s topologií (sandbox) a bez topologie
// ---------------------------------------------------------------------------

$sandboxPage = v58ui_render(static function () use ($module): void {
    lab57_render_level(V58UI_CLASS, $module, 'sandbox', '');
});
v58ui_check('level:sandbox:no-warnings', $sandboxPage['warnings'] === [], implode(' | ', $sandboxPage['warnings']));
$sandboxChecks = [
    'assets:css' => 'lab-a11y-v58.css',
    'assets:js-a11y' => 'lab-a11y-v58.js',
    'assets:js-explain' => 'lab-explain-v58.js',
    'adaptive-hint-container' => 'data-lab57-adaptive-hint',
    'term-context-span' => 'data-lab57-term-context',
    'explain-toggle' => 'data-lab57-explain-toggle',
    'explain-toggle-aria-pressed' => 'aria-pressed="false"',
    'explain-panel' => 'data-lab57-explain-panel',
    'a11y-panel' => 'data-lab57-a11y',
    'a11y-announce-select' => 'data-lab57-a11y-announce',
    'a11y-read-last-button' => 'data-lab57-a11y-read-last',
    'a11y-plain-checkbox' => 'data-lab57-a11y-plain',
    'a11y-fontsize-select' => 'data-lab57-a11y-fontsize',
    'a11y-contrast-checkbox' => 'data-lab57-a11y-contrast',
    'a11y-dyslexia-checkbox' => 'data-lab57-a11y-dyslexia',
    'live-region' => 'data-lab57-live',
    'live-region-aria-live' => 'aria-live="polite"',
    'output-role-log' => 'role="log"',
    'output-aria-live-off' => 'aria-live="off"',
    'shortcut-documented' => 'Alt+Shift+P',
    'net-topology-block' => 'data-lab57-net',
    'net-table' => 'data-lab57-net-table',
    'net-table-head' => 'Zpoždění</th>',
    'mission-toggle-aria-expanded' => 'aria-expanded="true"',
];
foreach ($sandboxChecks as $label => $needle) {
    v58ui_check('level:sandbox:' . $label, str_contains($sandboxPage['html'], $needle));
}

// ---------------------------------------------------------------------------
// 4b) A11Y-A7: „Mise“ toggle má aria-controls navázané na SKUTEČNÉ id vykresleného těla –
// ne jen přítomnost obou atributů někde v HTML (to by prošlo i při nesouhlasícím/chybějícím id).
// ---------------------------------------------------------------------------
if (preg_match('/<button[^>]*data-lab57-mission-toggle[^>]*>/', $sandboxPage['html'], $mMissionBtn)) {
    $hasControls = (bool)preg_match('/aria-controls="([^"]+)"/', $mMissionBtn[0], $mControlsId);
    v58ui_check('a11y:mission-toggle-has-aria-controls', $hasControls, $mMissionBtn[0]);
    if ($hasControls) {
        $bodyId = $mControlsId[1];
        $targetOk = (bool)preg_match('/<div\b(?=[^>]*\bid="' . preg_quote($bodyId, '/') . '")(?=[^>]*\bdata-lab57-mission-body\b)/', $sandboxPage['html']);
        v58ui_check('a11y:mission-toggle-aria-controls-target-exists', $targetOk, 'aria-controls="' . $bodyId . '" nemá odpovídající <div id="' . $bodyId . '" data-lab57-mission-body>');
    }
} else {
    v58ui_check('a11y:mission-toggle-has-aria-controls', false, 'tlačítko data-lab57-mission-toggle nenalezeno');
}

$firstStart = lab57_pack_levels('start')[0]['id'] ?? null;
v58ui_check('fixture:start-pack-has-level', $firstStart !== null);
if ($firstStart !== null) {
    $plainPage = v58ui_render(static function () use ($module, $firstStart): void {
        lab57_render_level(V58UI_CLASS, $module, (string)$firstStart, '');
    });
    v58ui_check('level:no-topology:no-warnings', $plainPage['warnings'] === [], implode(' | ', $plainPage['warnings']));
    v58ui_check('level:no-topology:net-block-absent', !str_contains($plainPage['html'], 'data-lab57-net-svg'));
    v58ui_check('level:no-topology:tools-still-present', str_contains($plainPage['html'], 'data-lab57-explain-toggle'));
    v58ui_check('level:breadcrumb-uses-lab58-pack', str_contains($plainPage['html'], 'Start v terminálu'));
}

// ---------------------------------------------------------------------------
// 5) Neplatný vstup se přesměruje, nezhroutí se
// ---------------------------------------------------------------------------

$redirected = false;
$redirectTarget = '';
try {
    v58ui_render(static function () use ($module): void { lab57_render_level(V58UI_CLASS, $module, 'neexistuje-xyz', ''); });
} catch (V58UiRedirect $e) {
    $redirected = true;
    $redirectTarget = $e->getMessage();
}
v58ui_check('level:invalid-id-redirects', $redirected && $redirectTarget === '?view=lab', $redirectTarget);

$redirected = false;
$redirectTarget = '';
try {
    v58ui_render(static function () use ($module): void { lab58_render_section(V58UI_CLASS, $module, 'neco-jineho'); });
} catch (V58UiRedirect $e) {
    $redirected = true;
    $redirectTarget = $e->getMessage();
}
v58ui_check('section:invalid-name-redirects', $redirected && $redirectTarget === '?view=lab', $redirectTarget);

// ---------------------------------------------------------------------------
// 6) Sekce dovednosti/opakování BEZ funkcí LEARN – vysvětlující degradace, žádná chyba
// ---------------------------------------------------------------------------

$skillsWithout = v58ui_render(static function () use ($module): void {
    lab58_render_section(V58UI_CLASS, $module, 'dovednosti');
});
v58ui_check('section:skills:no-warnings-without-learn', $skillsWithout['warnings'] === [], implode(' | ', $skillsWithout['warnings']));
if (!$hasRealSkillMap) v58ui_check('section:skills:fallback-message-without-learn', str_contains($skillsWithout['html'], 'připravuje'));

$reviewWithout = v58ui_render(static function () use ($module): void {
    lab58_render_section(V58UI_CLASS, $module, 'opakovani');
});
v58ui_check('section:review:no-warnings-without-learn', $reviewWithout['warnings'] === [], implode(' | ', $reviewWithout['warnings']));
if (!$hasRealReview) v58ui_check('section:review:fallback-message-without-learn', str_contains($reviewWithout['html'], 'připravuje'));

// ---------------------------------------------------------------------------
// 7) Herní rozcestník bez existujících her – žádná sekce; „Offline trénink“ podmíněně podle souboru
// ---------------------------------------------------------------------------

$hubWithoutGames = v58ui_render(static fn() => lab57_render_hub_section());
if (!$anyGameReal && !$offlineExists) v58ui_check('hub:empty-without-any-game', trim($hubWithoutGames['html']) === '');
$hasOfflineLink = str_contains($hubWithoutGames['html'], 'lab-offline.html');
v58ui_check('hub:offline-link-matches-file-existence', $offlineExists === $hasOfflineLink, 'soubor existuje=' . ($offlineExists ? 'ano' : 'ne') . ', odkaz vykreslen=' . ($hasOfflineLink ? 'ano' : 'ne'));

// ---------------------------------------------------------------------------
// 8) Zavedení náhrad LEARN (lab58_badges/review_today/skill_map) a her (function_exists brány)
// ---------------------------------------------------------------------------

if (!$hasRealBadges) {
    function lab58_badges(string $classId, string $studentKey): array
    {
        return [
            ['id' => 'prieskumnik', 'title' => 'Průzkumník', 'icon' => '⌕', 'earned' => true, 'progress' => 1.0, 'hint' => 'hotovo'],
            ['id' => 'golfista', 'title' => 'Golfista', 'icon' => '⛳', 'earned' => false, 'progress' => 0.5, 'hint' => 'v půlce'],
        ];
    }
}
if (!$hasRealReview) {
    function lab58_review_today(string $classId, string $studentKey): array
    {
        return ['date' => '2026-09-25', 'streak' => 4, 'items' => [
            ['level' => 'quest-1', 'title' => 'Najdi soubor', 'box' => 2, 'done' => false, 'url' => '?view=lab&uroven=quest-1'],
            ['level' => 'quest-2', 'title' => 'Práva souborů', 'box' => 1, 'done' => true, 'url' => '?view=lab&uroven=quest-2'],
        ]];
    }
}
if (!$hasRealSkillMap) {
    function lab58_skill_map(string $classId, string $studentKey): array
    {
        return [
            'commands' => ['ls' => ['uses' => 10, 'ok' => 9, 'state' => 'mastered'], 'grep' => ['uses' => 2, 'ok' => 1, 'state' => 'learning']],
            'concepts' => ['permissions' => ['title' => 'Oprávnění', 'state' => 'learning', 'progress' => 0.4]],
            'summary' => ['mastered' => 1, 'learning' => 1, 'total' => 2],
        ];
    }
}
v58ui_check('fixture:learn-stubs-defined', function_exists('lab58_badges') && function_exists('lab58_review_today') && function_exists('lab58_skill_map'));

// ---------------------------------------------------------------------------
// 9) Domovská stránka a sekce S daty LEARN a her
// ---------------------------------------------------------------------------

$homeWith = v58ui_render(static function () use ($module): void { lab57_render_home(V58UI_CLASS, $module, ''); });
v58ui_check('home:no-warnings-with-learn', $homeWith['warnings'] === [], implode(' | ', $homeWith['warnings']));
if (!$hasRealBadges) {
    v58ui_check('home:badges-shown', str_contains($homeWith['html'], 'Průzkumník') && str_contains($homeWith['html'], 'is-earned'));
    v58ui_check('home:badge-progress-percent-shown', str_contains($homeWith['html'], '50 %'));
} else {
    v58ui_check('home:badges-shown-generic', str_contains($homeWith['html'], 'lab57-badges'));
}
if (!$hasRealReview) {
    v58ui_check('home:review-card-shown', str_contains($homeWith['html'], 'Dnešní opakování'));
    v58ui_check('home:review-card-count-and-plural', str_contains($homeWith['html'], '1 úloha ke zopakování'));
} else {
    v58ui_check('home:review-card-shown-generic', str_contains($homeWith['html'], 'lab57-review-card') || str_contains($homeWith['html'], 'opakování'));
}
v58ui_check('home:skillmap-link-shown', str_contains($homeWith['html'], 'sekce=dovednosti'));

// Rozcestník se řídí is_file() na vlastnický soubor modulu – ověříme shodu v obou směrech (ne jen "existuje-li").
$hubHtml = v58ui_render(static fn() => lab57_render_hub_section())['html'];
$hubFileToHrefs = [
    'arena_v58_views.php' => ['?view=hadanka'],
    'arena_v58_events_views.php' => ['?view=ctf', '?view=incident'],
    'teamgames_v58_views.php' => ['?view=hry'],
    'robots_v58_views.php' => ['?view=roboti'],
];
foreach ($hubFileToHrefs as $file => $hrefs) {
    $exists = is_file($ROOT . '/' . $file);
    foreach ($hrefs as $href) {
        $present = str_contains($hubHtml, $href);
        v58ui_check("hub:$href:matches-is_file($file)", $exists === $present, 'soubor existuje=' . ($exists ? 'ano' : 'ne') . ', odkaz=' . ($present ? 'ano' : 'ne'));
    }
}

$skillsWith = v58ui_render(static function () use ($module): void { lab58_render_section(V58UI_CLASS, $module, 'dovednosti'); });
v58ui_check('section:skills:no-warnings-with-learn', $skillsWith['warnings'] === [], implode(' | ', $skillsWith['warnings']));
if (!$hasRealSkillMap) {
    v58ui_check('section:skills:command-shown', str_contains($skillsWith['html'], '<code>ls</code>') && str_contains($skillsWith['html'], 'lab57-skill-mastered'));
    v58ui_check('section:skills:concept-progressbar', (bool)preg_match('/aria-valuenow="40"/', $skillsWith['html']));
} else {
    v58ui_check('section:skills:renders-generic', str_contains($skillsWith['html'], 'Mapa dovedností'));
}

$reviewWith = v58ui_render(static function () use ($module): void { lab58_render_section(V58UI_CLASS, $module, 'opakovani'); });
v58ui_check('section:review:no-warnings-with-learn', $reviewWith['warnings'] === [], implode(' | ', $reviewWith['warnings']));
if (!$hasRealReview) {
    v58ui_check('section:review:item-open-link', str_contains($reviewWith['html'], '?view=lab&amp;uroven=quest-1'));
    v58ui_check('section:review:done-item-marked', (bool)preg_match('/is-done">\s*<span>✔/', $reviewWith['html']));
    v58ui_check('section:review:no-link-for-done-item', !str_contains($reviewWith['html'], 'uroven=quest-2'));
} else {
    v58ui_check('section:review:renders-generic', str_contains($reviewWith['html'], 'Dnešní opakování'));
}

v58ui_check('plural:1', lab57_plural_cs(1, 'úloha', 'úlohy', 'úloh') === 'úloha');
v58ui_check('plural:2', lab57_plural_cs(2, 'úloha', 'úlohy', 'úloh') === 'úlohy');
v58ui_check('plural:5', lab57_plural_cs(5, 'úloha', 'úlohy', 'úloh') === 'úloh');

// ---------------------------------------------------------------------------
// 10) Datový kontrakt: kontext (label/role/shared) a adaptivní nápověda v op=state
// ---------------------------------------------------------------------------

$errorsBeforeCtx = lab58_registry_errors();
// POZOR (nález pro integrátora/LABCORE): docs/LAB_V58_API.md u `access` píše výchozí „povoleno“, ale
// lab58_ctx_call() bez registrovaného callbacku vrací svůj $default, což je u lab58_context_access()
// rovnou chybová hláška – bez explicitního 'access' by tenhle kontext byl pro každého zamítnutý.
lab58_register_context('uiauditctx', [
    'label' => 'Testovací tým',
    'access' => static fn(array $level, array $ctx): ?string => null,
    'state_key' => static fn(array $ctx): string => 'team:' . $ctx['id'],
    'role' => static fn(array $ctx): string => 'sitar',
    'reset' => false,
]);
$newErrors = v58ui_registry_new_errors($errorsBeforeCtx);
v58ui_check('registry:no-errors-after-context', $newErrors === [], implode(' | ', $newErrors));
$teamLevelId = lab57_pack_levels('start')[0]['id'] ?? null;
if ($teamLevelId !== null) {
    $teamCtx = ['class' => V58UI_CLASS, 'student' => 'zak1', 'label' => 'Žák Jedna', 'context' => 'uiauditctx:mise1', 'level' => (string)$teamLevelId, 'now' => V58UI_NOW, 'cli' => true, 'classmates' => []];
    $teamState = lab57_session($teamCtx, 'state', []);
    $ctxPayload = (array)($teamState['context'] ?? []);
    v58ui_check('contract:context-shared-true-for-team', ($ctxPayload['shared'] ?? false) === true, json_encode($ctxPayload));
    v58ui_check('contract:context-role-passed-through', ($ctxPayload['role'] ?? '') === 'sitar');
    v58ui_check('contract:context-label-passed-through', ($ctxPayload['label'] ?? '') === 'Testovací tým');
}

$errorsBeforeFilter = lab58_registry_errors();
// Přiřazení (ne `+` sloučení) záměrně přepíše i to, co už do 'adaptive_hint' zapsal dřívější filtr
// (LEARN registruje lab58_review_state_payload_filter už při require_once výše) – jinak by `+` tichý
// existující klíč (třeba null bez chyby) nepřepsalo a test by nic neřekl o mé straně kontraktu.
lab58_add_filter('state_payload', static function (array $p, array $a): array {
    $p['adaptive_hint'] = ['text' => 'Zkus nejdřív ls -l.', 'reason' => 'opakovana-chyba'];
    return $p;
});
$newErrors = v58ui_registry_new_errors($errorsBeforeFilter);
v58ui_check('registry:no-errors-after-hint-filter', $newErrors === [], implode(' | ', $newErrors));
if ($firstStart !== null) {
    $hintCtx = ['class' => V58UI_CLASS, 'student' => 'zak-hint', 'label' => 'Test', 'context' => 'practice', 'level' => (string)$firstStart, 'now' => V58UI_NOW, 'cli' => true, 'classmates' => []];
    $hintState = lab57_session($hintCtx, 'state', []);
    $hint = (array)($hintState['adaptive_hint'] ?? []);
    v58ui_check('contract:adaptive-hint-text-field', ($hint['text'] ?? '') === 'Zkus nejdřív ls -l.', json_encode($hint));
}

// ---------------------------------------------------------------------------
// 11) Kontrast barev terminálu (proměnné --lab57-*) – běžný a vysoký kontrast
// ---------------------------------------------------------------------------

$cssNormal = (string)file_get_contents($ROOT . '/assets/linux-v57.css');
$cssContrast = (string)file_get_contents($ROOT . '/assets/lab-a11y-v58.css');
$normalVars = v58ui_css_vars($cssNormal, '.lab57-term {');
$contrastVars = v58ui_css_vars($cssContrast, '[data-lab57].lab57-contrast .lab57-term {');
v58ui_check('css:normal-vars-found', count($normalVars) >= 7, implode(',', array_keys($normalVars)));
v58ui_check('css:contrast-vars-found', count($contrastVars) >= 7, implode(',', array_keys($contrastVars)));

foreach (['normal' => $normalVars, 'contrast' => $contrastVars] as $mode => $vars) {
    if (!isset($vars['bg'])) { v58ui_check("contrast:$mode:has-bg", false); continue; }
    foreach (['fg', 'cmd', 'err', 'tip', 'muted', 'accent'] as $key) {
        if (!isset($vars[$key])) { v58ui_check("contrast:$mode:$key-defined", false); continue; }
        $ratio = v58ui_contrast($vars[$key], $vars['bg']);
        v58ui_check("contrast:$mode:$key-vs-bg>=4.5", $ratio >= 4.5, 'ratio=' . round($ratio, 2));
    }
    if (isset($vars['focus'])) {
        $ratio = v58ui_contrast($vars['focus'], $vars['bg']);
        v58ui_check("contrast:$mode:focus-vs-bg>=3.0", $ratio >= 3.0, 'ratio=' . round($ratio, 2));
    }
}

// ---------------------------------------------------------------------------
// 11b) A11Y-A8: ANSI .a-fg-30 (tmavě šedá, výstup „útlumu“) musí být čitelná na tmavém pozadí
// terminálu – skutečná vypočtená hodnota, ne jen přítomnost pravidla.
// ---------------------------------------------------------------------------
if (preg_match('/\.a-fg-30\s*\{\s*color:\s*(#[0-9a-fA-F]{3,8})/', $cssNormal, $mFg30) && isset($normalVars['bg'])) {
    $fg30Ratio = v58ui_contrast($mFg30[1], $normalVars['bg']);
    v58ui_check('css:a-fg-30-vs-term-bg>=4.5', $fg30Ratio >= 4.5, 'color=' . $mFg30[1] . ' bg=' . $normalVars['bg'] . ' ratio=' . round($fg30Ratio, 2));
} else {
    v58ui_check('css:a-fg-30-vs-term-bg>=4.5', false, '.a-fg-30 nebo --lab57-bg (normal) nenalezeno');
}

// ---------------------------------------------------------------------------
// 12) Statický sken JS: žádné innerHTML s daty, eval, new Function, vzdálený fetch
// ---------------------------------------------------------------------------

$jsFiles = ['assets/lab-explain-v58.js', 'assets/lab-a11y-v58.js', 'assets/linux-v57.js'];
foreach ($jsFiles as $rel) {
    $src = (string)file_get_contents($ROOT . '/' . $rel);
    // innerHTML = '' (mazání obsahu prázdným řetězcem) je bezpečné – nic se nevkládá; jde jen o dynamický obsah.
    preg_match_all('/\.innerHTML\s*=\s*([^;]+);/', $src, $mm);
    $unsafeInnerHtml = array_values(array_filter(array_map('trim', $mm[1]), static fn(string $rhs): bool => $rhs !== "''" && $rhs !== '""'));
    v58ui_check("jssafe:$rel:no-innerHTML", $unsafeInnerHtml === [], 'nebezpečné přiřazení innerHTML: ' . implode(', ', $unsafeInnerHtml));
    v58ui_check("jssafe:$rel:no-eval", !preg_match('/\beval\s*\(/', $src));
    v58ui_check("jssafe:$rel:no-new-function", !preg_match('/new\s+Function\s*\(/', $src));
    v58ui_check("jssafe:$rel:no-remote-fetch", !preg_match('/fetch\s*\(\s*[\'"]https?:\/\//', $src));
}
v58ui_check('jssafe:linux-v57.js:transport-contract', (bool)preg_match('/window\.Lab57\.setTransport\s*=\s*function/', (string)file_get_contents($ROOT . '/assets/linux-v57.js')));

// ---------------------------------------------------------------------------
// 13) Syntaxe JS přes node --check (jen když je node k dispozici; jinak INFO, ne FAIL)
// ---------------------------------------------------------------------------

$nodeOk = false;
if (function_exists('exec')) {
    @exec('node --version 2>&1', $verOut, $verCode);
    $nodeOk = $verCode === 0;
}
if (!$nodeOk) {
    echo "INFO node --check přeskočeno (node není k dispozici v tomto prostředí)\n";
} else {
    foreach ($jsFiles as $rel) {
        $path = $ROOT . '/' . $rel;
        @exec('node --check ' . escapeshellarg($path) . ' 2>&1', $checkOut, $checkCode);
        v58ui_check('node-check:' . $rel, $checkCode === 0, implode(' ', $checkOut));
    }
}

// ---------------------------------------------------------------------------
// 14) A11Y-A2 (chování, ne jen syntaxe): Tab/Shift+Tab v příkazové řádce nesmí být klávesnicová
// past. Skutečně spustí assets/linux-v57.js v Node (vm, minimální DOM náhrada) a ověří
// preventDefault() na syntetických klávesových událostech – chová se, ne jen „obsahuje řetězec“.
// Jen když je node k dispozici (jinak INFO, ne FAIL – stejně jako node --check výše).
// ---------------------------------------------------------------------------

if (!$nodeOk) {
    echo "INFO A2 (chování Tab/Shift+Tab v Node) přeskočeno (node není k dispozici v tomto prostředí)\n";
} else {
    $a2HarnessPath = $TMP . '/v58ui_a2_harness.js';
    file_put_contents($a2HarnessPath, <<<'JSHARNESS'
'use strict';
var fs = require('fs');
var vm = require('vm');

var jsPath = process.argv[2];
var src = fs.readFileSync(jsPath, 'utf8');

function makeEl(tag) {
  var listeners = {};
  var kids = [];
  var node = {
    tagName: String(tag || 'div').toUpperCase(),
    dataset: {},
    style: {},
    className: '',
    hidden: false,
    disabled: false,
    value: '',
    textContent: '',
    scrollTop: 0,
    scrollHeight: 0,
    appendChild: function (child) { kids.push(child); return child; },
    removeChild: function (child) { var i = kids.indexOf(child); if (i >= 0) kids.splice(i, 1); return child; },
    setAttribute: function (name, val) { this['__attr_' + name] = String(val); },
    getAttribute: function (name) { return Object.prototype.hasOwnProperty.call(this, '__attr_' + name) ? this['__attr_' + name] : null; },
    hasAttribute: function (name) { return Object.prototype.hasOwnProperty.call(this, '__attr_' + name); },
    addEventListener: function (type, fn) { (listeners[type] = listeners[type] || []).push(fn); },
    removeEventListener: function () {},
    dispatchEvent: function (evt) { (listeners[evt.type] || []).forEach(function (fn) { fn(evt); }); return true; },
    querySelector: function () { return null; },
    querySelectorAll: function () { return []; },
    focus: function () {},
    closest: function () { return null; },
    setSelectionRange: function () {},
    classList: { add: function () {}, remove: function () {}, toggle: function () {}, contains: function () { return false; } }
  };
  return node;
}

var out = makeEl('div');
var form = makeEl('form');
var input = makeEl('input');
var promptEl = makeEl('span');
var root = makeEl('div');
root.dataset = { api: '', level: 'l1', ctx: 'practice' };

var selMap = {
  '[data-lab57-out]': out,
  '[data-lab57-form]': form,
  '[data-lab57-input]': input,
  '[data-lab57-prompt]': promptEl
};
root.querySelector = function (sel) { return Object.prototype.hasOwnProperty.call(selMap, sel) ? selMap[sel] : null; };

var fakeDocument = {
  readyState: 'complete',
  body: makeEl('body'),
  querySelector: function () { return null; },
  querySelectorAll: function (sel) { return sel === '[data-lab57]' ? [root] : []; },
  createElement: makeEl,
  createTextNode: function (text) { return { nodeType: 3, textContent: String(text), nodeValue: String(text) }; },
  addEventListener: function () {},
  dispatchEvent: function () { return true; }
};

global.window = { location: { search: '' } };
global.document = fakeDocument;
global.CSS = { escape: function (s) { return String(s); } };
global.CustomEvent = function (type, opts) { this.type = type; this.detail = opts && opts.detail; };
global.fetch = function () {
  return Promise.resolve({
    status: 200,
    json: function () { return Promise.resolve({ ok: true, level: {}, hints_used: 0, prompt: '$', history: [], tx: [] }); }
  });
};

vm.runInThisContext(src, { filename: jsPath });

function keyEvent(key, opts) {
  opts = opts || {};
  var evt = { type: 'keydown', key: key, shiftKey: !!opts.shift, ctrlKey: !!opts.ctrl, defaultPrevented: false };
  evt.preventDefault = function () { evt.defaultPrevented = true; };
  return evt;
}

var results = [];
function check(name, ok, detail) { results.push({ name: name, ok: !!ok, detail: detail || '' }); }

input.value = '';
var e1 = keyEvent('Tab');
input.dispatchEvent(e1);
check('a2:empty-tab-passes-through', e1.defaultPrevented === false, 'prázdný vstup + Tab nesmí zavolat preventDefault (jinak past)');

input.value = 'ls -l';
var e2 = keyEvent('Tab');
input.dispatchEvent(e2);
check('a2:nonempty-tab-completes', e2.defaultPrevented === true, 'neprázdný vstup + Tab má spustit doplňování (preventDefault)');

input.value = 'ls -l';
var e3 = keyEvent('Tab', { shift: true });
input.dispatchEvent(e3);
check('a2:shift-tab-never-trapped', e3.defaultPrevented === false, 'Shift+Tab nesmí nikdy zavolat preventDefault');

input.value = 'ls -l';
input.dispatchEvent(keyEvent('Escape'));
var e4 = keyEvent('Tab');
input.dispatchEvent(e4);
check('a2:escape-then-tab-leaves-terminal', e4.defaultPrevented === false, 'po Esc má i obyčejný Tab projít (dokumentovaná úniková cesta)');

var e5 = keyEvent('Tab');
input.dispatchEvent(e5);
check('a2:escape-arm-consumed-once', e5.defaultPrevented === true, 'úniková cesta se po jednom použití nesmí zůstat trvale otevřená');

process.stdout.write(JSON.stringify(results));
process.exit(results.every(function (r) { return r.ok; }) ? 0 : 1);
JSHARNESS
    );
    $jsTarget = $ROOT . '/assets/linux-v57.js';
    $a2Out = [];
    @exec('node ' . escapeshellarg($a2HarnessPath) . ' ' . escapeshellarg($jsTarget) . ' 2>&1', $a2Out, $a2Code);
    $a2LastLine = $a2Out !== [] ? (string)end($a2Out) : '';
    $a2Results = json_decode($a2LastLine, true);
    if (!is_array($a2Results)) {
        v58ui_check('a2:harness-runs', false, 'exit=' . $a2Code . ' output=' . implode(' | ', $a2Out));
    } else {
        v58ui_check('a2:harness-runs', true);
        foreach ($a2Results as $row) {
            v58ui_check((string)($row['name'] ?? 'a2:unknown'), (bool)($row['ok'] ?? false), (string)($row['detail'] ?? ''));
        }
    }
}

// ---------------------------------------------------------------------------

$checks = $GLOBALS['v58ui']['checks'];
$failed = $GLOBALS['v58ui']['failed'];
echo ($failed === 0 ? 'V58_LAB_UI_AUDIT_OK' : 'V58_LAB_UI_AUDIT_FAIL') . ' checks=' . $checks . ' failed=' . $failed . "\n";
exit($failed === 0 ? 0 : 1);
