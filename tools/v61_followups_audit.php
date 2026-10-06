<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · audit dotažení bodů z v60 (část A).
 *   1) skill_trees.php: paměť úrovně/jména/pokroku se po zápisu v rámci jednoho požadavku nedrží zastaralá,
 *   2) záložka Odznaky ≤ 35 KB i pro žáka s 30+ odznaky, zamčené až na ?zamcene=1, zkrácené popisy,
 *   3) Aréna: jeden prázdný stav, týdenní historie jen odehraná a sbalená v <details>,
 *   4) globální focus: 3 px, plná barva značky, žádný širší pravidlo ho na klíčových stránkách nepřebíjí,
 *   5) assets/arena-v60.css neexistuje a nikde se neodkazuje,
 *   6) účty žáků se na běžných stránkách přihlášeného žáka nezakládají (a tam, kde jsou potřeba, ano).
 *
 *   php tools/v61_followups_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) + vestavěný server (tools/lib/http_harness.php), fiktivní žáci;
 * ostrou storage/ nikdy nečte ani nezapisuje. Konec: V61_FOLLOWUPS_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace('\\', '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v61-followups')), '/');
require_once $ROOT . '/bootstrap.php';   // před jakýmkoli výstupem (session_start)
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage'));

const V61_LABEL = 'Vilém Testovací';     // fiktivní žák (nikdy skutečné jméno)
const V61_LABEL2 = 'Eliška Zkušební';
const V61_OBSERVER = 'Audit Pozorovatel';
const V61_CLASS = 'class_3a';

/** Fiktivní žáci do intake odpovědí dočasného úložiště (adresář žáků je pak vidí). */
function v61_seed_roster(string $dir, array $labels): void
{
    @mkdir($dir . '/intake', 0700, true);
    $rows = [];
    foreach ($labels as $i => $label) {
        [$first, $last] = explode(' ', $label, 2);
        $rows[] = ['id' => 'fx61_' . $i, 'class_id' => V61_CLASS, 'submitted_at' => date(DATE_ATOM), 'student' => ['first_name' => $first, 'last_name' => $last, 'preferred_name' => '', 'seat_id' => '', 'seat_label' => ''], 'answers' => [], 'assessment' => [], 'source' => 'fixture', 'imported_at' => date(DATE_ATOM)];
    }
    file_put_contents($dir . '/intake/responses.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode($rows, JSON_UNESCAPED_UNICODE));
}
v61_seed_roster($tmp, [V61_LABEL]);

foreach ([
    'bootstrap.php', 'app/lib.php',
    'app/views/_layout.php', 'student_v55.php', 'student_v55_views.php', 'zero_friction_v50_6.php',
    'unified_page_shell_v50_7.php', 'student_links_v69.php', 'goal_navigator_v50_4.php', 'hands_on_learning_v50.php',
    'independent_growth_v50.php', 'learning_v56.php', 'session_v53.php', 'tutorial_v52.php', 'linux_v57_lab.php', 'arena_v57.php',
    'teacher_operations_v46.php', 'student_learning_coach_v47.php', 'student_learning_coach_views_v47.php',
    'student_learning_accelerator_v47_1.php', 'student_learning_accelerator_views_v47_1.php',
    'student_corrective_cycle_v47_2.php', 'student_corrective_cycle_views_v47_2.php', 'student_social_views.php',
    'skill_views.php', 'project_workspace_views.php', 'points_v53.php', 'learning_v56_views.php',
    'profile_v60.php', 'profile_v60_views.php', 'lab_v58_learning.php',
    'arena_v58_weekly.php', 'arena_v60_challenge.php', 'arena_v60_challenge_views.php', 'accounts_v53.php',
] as $rel) {
    require_once $ROOT . '/' . $rel;
}
app_require_libs(['core']);   // accounts_v53, intake_v51 (adresář žáků z intake)
$GLOBALS['nextLessons'] = [];
$GLOBALS['extendedLessons'] = [];
$GLOBALS['view'] = 'profile';

$sidOf = static fn(string $label): string => skill_student_key_for_label(V61_CLASS, $label);
$profileKey = static fn(string $label): string => V61_CLASS . ':s:' . substr(hash('sha256', V61_CLASS . '|' . normalized_person_name($label)), 0, 24);
$setXp = static function (string $label, int $xp) use ($profileKey): void {
    storage_update(STORAGE_DIR . '/learning_profiles.json.php', static function (array $all) use ($label, $xp, $profileKey): array {
        $key = $profileKey($label);
        $all[$key] = array_replace(is_array($all[$key] ?? null) ? $all[$key] : [], ['xp' => $xp, 'version' => 3]);
        return $all;
    });
};

// =============================================================================================
// 1) skill_trees.php – paměť požadavku se po zápisu nedrží zastaralá
// =============================================================================================
$_SESSION['next_class_id'] = V61_CLASS;
$_SESSION['student_label'] = V61_OBSERVER;           // aktuální žák je jiný než sledovaný → úroveň ze snímku XP
$kT = $sidOf(V61_LABEL);
$check('žák jde z klíče zpět na jméno (paměť jmen)', skill_student_label_from_key(V61_CLASS, $kT) === V61_LABEL);
$check('neznámý klíč dá prázdné jméno (a prázdný výsledek se nepamatuje)', skill_student_label_from_key(V61_CLASS, 'neexistuje|x') === '' && !isset($GLOBALS['skill_label_memo'][V61_CLASS . '|neexistuje|x']));
$check('jméno se pamatuje po prvním nalezení (stabilní vazba klíč → jméno)', ($GLOBALS['skill_label_memo'][V61_CLASS . '|' . $kT] ?? '') === V61_LABEL);

$setXp(V61_LABEL, 0);
$check('úroveň bez XP = 1', skill_student_level(V61_CLASS, $kT) === 1);
$setXp(V61_LABEL, 2140);
$check('změna XP v témže požadavku se projeví hned (úroveň 9, žádná zastaralá paměť mimo přepočet)', skill_student_level(V61_CLASS, $kT) === 9);

// zastaralá hodnota v paměti přepočtu (po přerušeném přepočtu) nesmí přepsat čerstvě spočtenou úroveň
$levelGaps = static function (array $map): int {
    $n = 0;
    foreach ($map as $row) foreach ((array)($row['missing_requirements'] ?? []) as $miss) if (is_array($miss) && ($miss['type'] ?? '') === 'level') $n++;
    return $n;
};
$setXp(V61_LABEL, 5000);                                  // úroveň 21 → žádné dovednosti nechybí úroveň
$GLOBALS['skill_req_level'][V61_CLASS . '|' . $kT] = 1;   // „zbytek“ po přerušeném přepočtu
$map = skill_recalculate_all(V61_CLASS, $kT);
$check('přepočet ignoruje zastaralou paměť úrovně 1 (při XP 5000 žádné dovednosti nechybí úroveň)', $map !== [] && $levelGaps($map) === 0);
$check('po přepočtu paměť úrovně nezůstává', !isset($GLOBALS['skill_req_level'][V61_CLASS . '|' . $kT]));
$setXp(V61_LABEL, 0);
$GLOBALS['skill_req_level'][V61_CLASS . '|' . $kT] = 21;
$map = skill_recalculate_all(V61_CLASS, $kT);
$check('opačně: zastaralá vysoká úroveň nic neodemkne (při XP 0 chybí úroveň u dovedností tier ≥ 2)', $levelGaps($map) > 0);
$check('paměť úrovně po druhém přepočtu opět prázdná', !isset($GLOBALS['skill_req_level'][V61_CLASS . '|' . $kT]));

// zápis evidence bez přepočtu: pokrok z paměti se zahodí a čte se znovu
$tier1 = null;
foreach (skill_relevant_skills(V61_CLASS) as $sk) {
    if ((int)($sk['tier'] ?? 1) === 1 && (array)($sk['requires'] ?? []) === [] && empty($sk['mastery_node'])) { $tier1 = $sk; break; }
}
$check('fixture: existuje dovednost úrovně 1 bez předpokladů', $tier1 !== null);
if ($tier1 !== null) {
    $slug = (string)$tier1['slug'];
    $before = (float)(skill_progress_map(V61_CLASS, $kT)[$slug]['mastery_points'] ?? -1);
    skill_add_evidence(V61_CLASS, $kT, $slug, 'quiz', 'v61-audit-1', 100.0, 100.0, 'automatic', [], false);
    $after = (float)(skill_progress_map(V61_CLASS, $kT)[$slug]['mastery_points'] ?? -1);
    $check('po zápisu evidence (bez přepočtu) mapa pokroku odráží nová data, ne starou paměť (' . $before . ' → ' . $after . ')', $before >= 0.0 && $after > $before);
    $branchesBefore = skill_branch_progress_map(V61_CLASS, $kT);
    $check('větve se po zápisu přepočítají s pokrokem (mapa větví existuje)', is_array($branchesBefore) && $branchesBefore !== []);
}

// počty pro úspěchy jsou vázané na žáka (dřív jen na třídu) a po přepočtu se zahodí
$_SESSION['student_label'] = V61_OBSERVER;
skill_achievement_value(V61_CLASS, 'skill_first_mastered');
$_SESSION['student_label'] = V61_LABEL;
skill_achievement_value(V61_CLASS, 'skill_first_mastered');
$check('počty pro úspěchy se pamatují zvlášť pro každého žáka', count((array)($GLOBALS['skill_ach_cache'] ?? [])) === 2);
skill_recalculate_all(V61_CLASS, $kT);
$check('po přepočtu se paměť počtů pro úspěchy nepoužije zastaralá (je přepsána/čerstvá)', count((array)($GLOBALS['skill_ach_cache'] ?? [])) <= 1);
skill_memo_reset();
$check('skill_memo_reset() vyprázdní paměť požadavku', !isset($GLOBALS['skill_runtime_progress']) && !isset($GLOBALS['skill_label_memo']) && !isset($GLOBALS['skill_ach_cache']));
$_SESSION['student_label'] = V61_OBSERVER;

// =============================================================================================
// 2) Záložka Odznaky ≤ 35 KB i pro žáka s 30+ odznaky
// =============================================================================================
$_SESSION['student_label'] = V61_LABEL;
$_SESSION['local_user'] = ['id' => 'fx61', 'email' => 'vilem.testovaci@educanet.cz', 'name' => V61_LABEL];
$defs = learning_badge_definitions();
$badges = [];
foreach (array_slice(array_keys($defs), 0, 34) as $i => $id) $badges[$id] = ['earned_at' => date(DATE_ATOM, strtotime('-' . ($i + 1) . ' days'))];
$check('fixture: katalog má alespoň 34 odznaků', count($badges) >= 34);
storage_update(STORAGE_DIR . '/learning_profiles.json.php', static function (array $all) use ($profileKey, $badges): array {
    $all[$profileKey(V61_LABEL)] = ['xp' => 2140, 'events' => [], 'kb' => [], 'studio' => [], 'journey' => [], 'badges' => $badges, 'achievements' => [], 'version' => 3, 'updated_at' => date(DATE_ATOM)];
    return $all;
});
$renderBadges = static function (array $get) use ($ROOT): string {
    foreach (['zamcene', 'vsechny'] as $k) unset($_GET[$k]);
    $_GET = array_merge(['tab' => 'odznaky'], $get);
    unset($_GET['student']);
    $GLOBALS['educanet_json_request_cache'] = [];
    return audit_capture(static function (): void { render_profile60_view(V61_CLASS, []); });
};
$page = $renderBadges([]);
$earnedCount = substr_count($page, 'data-b60-state="earned"');
$check('Odznaky (žák s 34 odznaky): ' . strlen($page) . ' B ≤ 35 KB', strlen($page) <= 35 * 1024 && !preg_match('/Fatal error|Uncaught/', $page));
$check('Odznaky: první strana ukazuje jen část sbírky (' . $earnedCount . ' získaných karet) a odkaz na všechny', $earnedCount > 0 && $earnedCount < 20 && str_contains($page, 'vsechny=1'));
$check('Odznaky bez JS: zamčené se nenačítají, je jen odkaz na samostatnou URL ?zamcene=1', !str_contains($page, 'data-b60-state="locked"><strong>') && !str_contains($page, 'b60-rows') && (bool)preg_match('~<a class="btn" href="[^"]*zamcene=1[^"]*#zamcene"~', $page));
$locked = $renderBadges(['zamcene' => '1']);
$check('?zamcene=1: zamčené odznaky se vykreslí jako seznam a kotva #zamcene existuje', str_contains($locked, 'id="zamcene"') && str_contains($locked, 'b60-rows') && substr_count($locked, 'b60-row ') > 5);
$all = $renderBadges(['vsechny' => '1']);
$check('?vsechny=1: vykreslí se všech 34 získaných odznaků', substr_count($all, 'data-b60-state="earned"') >= 34);
$check('JS filtr: třetí volba je při nenačtených zamčených odkaz (ne tlačítko bez efektu)', (bool)preg_match('~<a class="b60-chip" href="[^"]*zamcene=1~', $page) && !str_contains($page, 'data-b60-show="locked"'));
$check('JS filtr: při načtených zamčených zůstává tlačítko', str_contains($locked, 'data-b60-show="locked"'));
$short = badge60_short(str_repeat('Dlouhý popis podmínky odznaku. ', 6));
$check('popis se zkrátí na ≤ ' . BADGE60_TEXT_MAX . ' znaků s výpustkou', u_strlen($short) <= BADGE60_TEXT_MAX && str_ends_with($short, '…'));
$check('krátký popis zůstává beze změny', badge60_short('Nasbírej 1 000 XP.') === 'Nasbírej 1 000 XP.');
$smalls = [];
preg_match_all('~<small>(.*?)</small>~u', $page, $m);
foreach ($m[1] as $t) $smalls[] = html_entity_decode($t, ENT_QUOTES, 'UTF-8');
$check('žádný popis karty na stránce není delší než limit + „Jak získat: “', $smalls !== [] && max(array_map('u_strlen', $smalls)) <= BADGE60_TEXT_MAX + 14);
$GLOBALS['educanet_json_request_cache'] = [];
$sprites = substr_count($page, 'class="b60-sprite"');
$check('SVG sprite je na stránce nejvýš jednou a každý <use> má definovaný symbol', $sprites <= 1 && (function (string $html): bool {
    preg_match_all('~<use href="#(b60s-[a-z0-9-]+)"~', $html, $u);
    preg_match_all('~id="(b60s-[a-z0-9-]+)"~', $html, $i);
    return array_diff(array_unique($u[1]), $i[1]) === [];
})($page));

// =============================================================================================
// 3) Aréna – jeden prázdný stav, týdenní historie jen odehraná a sbalená
// =============================================================================================
$_SESSION['student_label'] = V61_LABEL;
$meKey = social_current_student_key(V61_CLASS);
$arena = audit_capture(static function () use ($meKey): void { arena60_render_profile_tab(V61_CLASS, $meKey); });
$check('Aréna bez soubojů: právě jeden prázdný stav s výzvou k akci (odkaz na spolužáky)', substr_count($arena, 'p60-empty-state') === 1 && str_contains($arena, 'view=community'));
$check('Aréna bez soubojů: žádné tři prázdné sekce (nadpisy příchozí/odeslané/historie nejsou)', !str_contains($arena, 'Příchozí výzvy') && !str_contains($arena, 'Odeslané výzvy') && !str_contains($arena, 'Historie soubojů') && !str_contains($arena, 'Zatím tě nikdo nevyzval'));
$check('Aréna: nastavení výzev (opt-in) zůstává dostupné', str_contains($arena, 'arena60_optin_set'));
$weekly = [['title' => 'Týden 1', 'played' => true, 'len' => 41], ['title' => 'Týden 2', 'played' => false, 'len' => 0], ['title' => 'Týden 3', 'played' => true, 'len' => 38]];
$wHtml = audit_capture(static function () use ($weekly): void { arena60_render_weekly($weekly); });
$check('týdenní historie: sbalená v <details>, ukazuje jen odehrané týdny', str_contains($wHtml, '<details') && str_contains($wHtml, 'Týden 1') && str_contains($wHtml, 'Týden 3') && !str_contains($wHtml, 'Týden 2') && !str_contains($wHtml, 'nehráno'));
$check('týdenní historie: počet odehraných týdnů je v souhrnu (2)', str_contains($wHtml, '(2)'));
$check('týdenní historie bez odehraného týdne: panel se nevykreslí', audit_capture(static function (): void { arena60_render_weekly([['title' => 'Týden 9', 'played' => false, 'len' => 0]]); }) === '');

// =============================================================================================
// 4) Globální focus + deset klíčových stránek
// =============================================================================================
$uiCss = (string)file_get_contents($ROOT . '/assets/student-ui-v50-7-7.css');
$globalRule = '';
if (preg_match('~body:not\(\.assessment-mode\)\s*:focus-visible\s*\{([^}]*)\}~', $uiCss, $fm)) $globalRule = $fm[1];
$check('globální focus: 3 px plná čára v barvě značky (var(--accent)), ne průhledná', (bool)preg_match('~outline:\s*3px solid var\(--accent,\s*#[0-9a-f]{6}\)~i', $globalRule) && !str_contains($globalRule, 'transparent') && !str_contains($globalRule, 'color-mix'));
$check('globální focus: !important a odsazení ≥ 2 px (viditelný i na barevném pozadí)', str_contains($globalRule, '!important') && (bool)preg_match('~outline-offset:\s*[2-9]px~', $globalRule));
$accents = [];
foreach (glob($ROOT . '/assets/*.css') ?: [] as $cssFile) {
    if (str_contains(basename($cssFile), '-dark-')) continue;   // v68: tmavé vrstvy se posuzují proti tmavému pozadí (v68_theme_audit), ne proti bílé
    if (preg_match_all('~--accent:\s*(#[0-9a-fA-F]{6})\b~', (string)file_get_contents($cssFile), $am)) foreach ($am[1] as $c) $accents[strtolower($c)] = true;
}
$low = [];
foreach (array_keys($accents) as $c) if ((audit_contrast_ratio($c, '#ffffff') ?? 0) < 3.0) $low[] = $c;
$check('všechny barvy značky (' . implode(', ', array_keys($accents)) . ') mají proti bílé kontrast ≥ 3 : 1 (WCAG 1.4.11)', $accents !== [] && $low === []);

$hv = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    audit_login_student($hv, V61_CLASS, V61_LABEL);
    $pages = ['dashboard', 'profile', 'profile&tab=odznaky', 'profile&tab=arena', 'lab', 'skills', 'course', 'calendar', 'community', 'growth'];
    $broad = static function (string $selector): bool {
        $s = preg_replace('~:not\([^)]*\)~', '', $selector) ?? $selector;
        return !preg_match('~[.#\[]~', $s) && str_contains($s, ':focus');
    };
    $conflicts = [];
    $okPages = 0;
    foreach ($pages as $p) {
        $r = $hv->request('GET', '/?view=' . $p);
        if ((int)$r['status'] !== 200 || !preg_match_all('~<link[^>]+rel="stylesheet"[^>]+href="([^"?]+\.css)~', (string)$r['body'], $links)) { $conflicts[] = $p . ':HTTP ' . $r['status']; continue; }
        $hasGlobal = in_array('assets/student-ui-v50-7-7.css', $links[1], true);
        $okPages += $hasGlobal ? 1 : 0;
        if (!$hasGlobal) $conflicts[] = $p . ': chybí globální stylesheet';
        foreach (array_unique($links[1]) as $href) {
            $file = $ROOT . '/' . ltrim($href, '/');
            if (!is_file($file) || str_contains($file, 'student-ui-v50-7-7')) continue;
            preg_match_all('~([^{}]+)\{([^{}]*)\}~', (string)file_get_contents($file), $rules, PREG_SET_ORDER);
            foreach ($rules as [, $sel, $decl]) {
                if (!preg_match('~outline(-color)?\s*:\s*[^;]*(none|\b0\b(?!\.)|transparent|color-mix)~', $decl)) continue;
                foreach (explode(',', $sel) as $one) if ($broad(trim($one)) && str_contains($decl, '!important')) $conflicts[] = $p . ': ' . $href . ' · ' . trim($one);
            }
        }
    }
    $check('deset klíčových stránek: všechny odpoví 200 a načítají globální focus (' . $okPages . '/' . count($pages) . ')', $okPages === count($pages));
    $check('žádný další stylesheet na těchto stránkách globální focus nepřebíjí širokým pravidlem outline:none/transparent !important' . ($conflicts ? ' – ' . implode('; ', array_slice($conflicts, 0, 4)) : ''), $conflicts === []);
} finally {
    $hv->stop();
}

// =============================================================================================
// 5) assets/arena-v60.css vyřazen
// =============================================================================================
$retired = 'arena-' . 'v60.css';
$check('assets/' . $retired . ' neexistuje', !is_file($ROOT . '/assets/' . $retired));
$refs = [];
$it = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($ROOT, FilesystemIterator::SKIP_DOTS),
    static fn(SplFileInfo $f): bool => !in_array($f->getFilename(), ['storage', 'V1', 'legacy', 'node_modules', '.git', 'docs'], true)
));
foreach ($it as $f) {
    if (!$f->isFile() || !preg_match('~\.(php|js|css|json|html)$~', $f->getFilename()) || $f->getFilename() === basename(__FILE__)) continue;
    if (str_contains((string)file_get_contents($f->getPathname()), $retired)) $refs[] = substr($f->getPathname(), strlen($ROOT) + 1);
}
$check('na ' . $retired . ' se v kódu (php/js/css/json/html) nikdo neodkazuje' . ($refs ? ': ' . implode(', ', $refs) : ''), $refs === []);
$check('styly výzev (arena60-*) jsou sloučené do profile-v60.css', str_contains((string)file_get_contents($ROOT . '/assets/profile-v60.css'), '.arena60-list'));

// =============================================================================================
// 6) Účty žáků: jen tam, kde jsou potřeba
// =============================================================================================
$truth = [
    ['GET přihlášený: dashboard', 'dashboard', 'GET', true, false], ['GET přihlášený: profil', 'profile', 'GET', true, false],
    ['GET přihlášený: lab', 'lab', 'GET', true, false], ['GET přihlášený: dovednosti', 'skills', 'GET', true, false],
    ['POST přihlášený', 'dashboard', 'POST', true, true], ['GET nepřihlášený: dashboard', 'dashboard', 'GET', false, true],
    ['GET úvod', 'home', 'GET', true, true], ['GET propojení účtu', 'link_account', 'GET', true, true],
    ['GET aktivace', 'activate', 'GET', true, true], ['GET kód hodiny', 'join', 'GET', true, true],
    ['GET změna hesla', 'change_password', 'GET', true, true], ['GET obnova hesla', 'reset_password', 'GET', true, true],
    ['GET ověření e-mailu', 'verify_email', 'GET', true, true], ['GET dotazník', 'intake', 'GET', true, true],
];
$badTruth = [];
foreach ($truth as [$label, $view, $method, $signed, $expect]) {
    if (acc53_request_needs_provisioning($view, $method, $signed) !== $expect) $badTruth[] = $label;
}
$check('rozhodovací tabulka potřeby zakládání účtů (' . count($truth) . ' případů)' . ($badTruth ? ' – špatně: ' . implode(', ', $badTruth) : ''), $badTruth === []);
@unlink($tmp . '/accounts_v53_state.json.php');   // bez stavu zakládání se provisioning musí spustit
php_json_cache_forget($tmp . '/accounts_v53_state.json.php');
php_json_cache_forget(local_accounts_path());
$cliRun = acc53_provision_all($GLOBALS['modules']);
php_json_cache_forget(local_accounts_path());
$check('CLI/nástroje: acc53_provision_all běží vždy a založí účet žáka z adresáře', !empty($cliRun['ran']) && isset(load_php_json(local_accounts_path())[acc53_email(V61_LABEL, [])]));
// HTTP: stav zakládání účtů se mění jen tam, kde je potřeba
$pwd = 'Audit-heslo-61-Xy!';
$email = acc53_email(V61_LABEL, []);
storage_update(local_accounts_path(), static function (array $a) use ($email, $pwd): array {
    if (isset($a[$email])) { $a[$email]['must_change_password'] = false; $a[$email]['password_hash'] = password_hash($pwd, PASSWORD_DEFAULT); }
    return $a;
});
$statePath = $tmp . '/accounts_v53_state.json.php';
$stateSig = static function () use ($statePath): string { return is_file($statePath) ? (string)(json_decode((string)preg_replace('/^<\?php.*?\?>\s*/s', '', (string)file_get_contents($statePath)), true)['signature'] ?? '') : ''; };
$forceStale = static function () use ($statePath): void {
    file_put_contents($statePath, "<?php http_response_code(403); exit; ?>\n" . json_encode(['signature' => 'stale']));
};
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $home = $h->request('GET', '/?view=home', [], ['follow_redirects' => false]);
    $csrf = $h->csrfToken((string)$home['body']);
    $login = $h->request('POST', '/', ['csrf' => (string)$csrf, 'action' => 'local_login', 'email' => $email, 'password' => $pwd]);
    $dash = $h->request('GET', '/?view=dashboard', [], ['follow_redirects' => false]);
    $signedIn = $pwd !== '' && (int)$dash['status'] === 200;
    $check('fixture: fiktivní žák se přihlásí lokálním účtem (dashboard 200)', $signedIn);
    if ($signedIn) {
        $forceStale();
        $statuses = [];
        foreach (['dashboard', 'profile', 'lab', 'skills'] as $v) $statuses[$v] = (int)$h->request('GET', '/?view=' . $v, [], ['follow_redirects' => false])['status'];
        $check('běžné stránky přihlášeného žáka účty nezakládají (stav zůstal „stale“) a odpovídají 200', $stateSig() === 'stale' && !in_array(0, $statuses, true) && count(array_filter($statuses, static fn(int $s): bool => $s === 200)) >= 3);
        $h->request('GET', '/?view=privacy', [], ['follow_redirects' => false]);
        $check('lehká/přihlašovací stránka (soukromí) účty ověří: stav zakládání se obnovil', $stateSig() !== 'stale' && $stateSig() !== '');
        $forceStale();
        $h->request('POST', '/', ['csrf' => (string)$csrf, 'action' => 'nic_neznamy'], ['follow_redirects' => false]);
        $check('POST požadavek účty ověří vždy', $stateSig() !== 'stale');
    }
    // nový žák se objeví v adresáři; zakládání ho zachytí na prvním nepřihlášeném požadavku (login ho potřebuje)
    v61_seed_roster($tmp, [V61_LABEL, V61_LABEL2, 'Matěj Pokusný']);
    $anon = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
    try {
        $anon->request('GET', '/?view=home', [], ['follow_redirects' => false]);
        $newEmail = acc53_email('Matěj Pokusný', []);
        php_json_cache_forget(local_accounts_path());
        $accounts = load_php_json(local_accounts_path());
        $check('nový žák v adresáři dostane účet na prvním přihlašovacím požadavku (acc53 provisioning proběhl tam, kde je potřeba)', $newEmail !== '' && isset($accounts[$newEmail]));
    } finally {
        $anon->stop();
    }
} finally {
    $h->stop();
}

// Rychlost bootstrapu: normalized_person_name se volá desítkykrát na stránku – informativně (návrh úpravy bootstrap.php)
$golden = ['Vilém Testovací' => 'vilemtestovaci', 'Žaneta Řeháková' => 'zanetarehakova', '  Ondřej   Čermák ' => 'ondrejcermak', 'Ľubomír Šťastný' => 'lubomirstastny'];
$badNames = [];
foreach ($golden as $in => $out) if (normalized_person_name($in) !== $out) $badNames[] = $in;
$check('normalized_person_name: stabilní výstup pro české/slovenské jméno (zlatý vzorek)' . ($badNames ? ' – ' . implode(', ', $badNames) : ''), $badNames === []);
$t0 = hrtime(true);
for ($i = 0; $i < 200; $i++) normalized_person_name('Žaneta Řeháková');
$perCallMs = (hrtime(true) - $t0) / 1e6 / 200;
echo 'INFO  normalized_person_name: ' . number_format($perCallMs, 3) . " ms/volání (návrh v61: instance Transliterator jednou; cíl < 0,05 ms)\n";

echo "BEHAVIORAL {$state->behavioral}/{$state->checks}\n";
if ($state->failed > 0) { echo "V61_FOLLOWUPS_AUDIT_FAIL checks={$state->checks} failed={$state->failed}\n"; exit(1); }
echo "V61_FOLLOWUPS_AUDIT_OK checks={$state->checks} failed=0\n";
