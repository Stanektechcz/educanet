<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v68 · audit cockpitu učitele a administrátora (menu, sekce, přesměrování, přepínač vzhledu, vyřazené pohledy).
 *   1) mapa: každá záložka cockpitu je v teacher68_tab_map() právě jednou, ≤ 6 sekcí, žádná neznámá záložka mimo mapu/přesměrování,
 *   2) viditelnost podle role (admin / učitel / asistent): menu z HTTP = teacher68_visible_tabs, nepovolené záložky v menu nejsou, prázdná sekce se nevykreslí,
 *   3) staré ?tab= (control, class_overview, growth, skills, mastery, filters, automations) → 302 na novou sekci, cizí třída zůstává 403 (guard před přesměrováním),
 *   4) přepínač vzhledu: CSRF, whitelist hodnot, návrat jen ?tab=…(&class=…) (žádný open redirect), cookie jen light|dark|system, <html data-theme> a color-scheme,
 *   5) politiky teacher59 beze změny (snapshot hash) kromě teacher68_theme_set a čtecích GET politik ?student=, deny-by-default pro neznámé teacher68_*,
 *   6) vyřazené žákovské pohledy (v48_state, continue, one_task) → 302, soubor v48_state.php je v retired/v68 se shodným SHA-256,
 *   7) rozcestník žáka, další kroky a drobečky, Linux Lab beze změny (SHA-256 snímek).
 *   php tools/v68_cockpit_audit.php        Dočasné úložiště, fiktivní data. Konec: V68_COCKPIT_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v68-cockpit')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'ui_v67.php', 'app/redirects_v68.php'] as $lib) { require_once $ROOT . '/' . $lib; }

/** SHA-256 snímku politik teacher59: stav před v68 bez teacher68_theme_set a GET politik ?student=, od v69 bez POST akcí vyřazených záložek (skill_*, ml_live_*, ml_failure_inject, teacher_automation_*, teacher_notification_read*, teacher_saved_filter_pin/default/watch, teacher_review_ack, teacher_sla_policy_save, v50_teacher_growth_control); změna jiné politiky audit shodí. */
const V68C_POST_POLICY_SHA = '656e6ab3383dd1f14b02e3ee6799157ff5c9ac50f021808617245eea74afe55c';
const V68C_GET_POLICY_SHA = 'c27cfc36d68d8d73eaee7bed2a84f6ab478bc6f62ab6f17591982d6763699c2a';
const V68C_THEME_KEY = 'v68-audit-teacher-key';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));
$check('bez souboru účtů je režim legacy (audit běží v legacy; role z EDUCANET_TEACHER_ROLE)', teacher59_mode() === 'legacy');

// ---------------------------------------------------------------- 1) mapa
$map = teacher68_tab_map();
$sections = teacher68_sections();
$v58Keys = array_keys(teacher58_modules());
$check('mapa: nejvýše 6 sekcí a přesně Dnes / Třída a žáci / Výuka / Hodnocení / Hry a motivace / Správa',
    count($sections) <= 6 && array_column($sections, 'label') === ['Dnes', 'Třída a žáci', 'Výuka', 'Hodnocení', 'Hry a motivace', 'Správa']);
$check('mapa: každá záložka patří do existující sekce a má popisek i nápovědu', array_reduce(array_keys($map), static fn(bool $c, string $t): bool => $c && isset($sections[$map[$t]['section']]) && $map[$t]['label'] !== '' && $map[$t]['hint'] !== '', true));
$check('mapa: každý modul teacher58_modules() je v mapě právě jednou', array_diff($v58Keys, array_keys($map)) === [] && count(array_unique(array_keys($map))) === count($map));
preg_match("/\\\$teacherAllowedTabs=\[([^\]]*)\]/", $read('teacher.php'), $m);
$allowed = array_map(static fn(string $s): string => trim($s, "' "), explode(',', (string)($m[1] ?? '')));
$orphans = array_values(array_diff($allowed, array_keys($map), array_keys(teacher68_tab_redirects()), ['ucet', 'sekce']));
$check('mapa: žádná záložka z teacher.php není mimo mapu / přesměrování' . ($orphans ? ' [' . implode(',', $orphans) . ']' : ''), $orphans === [] && count($allowed) > 20, false);
$redirects = teacher68_tab_redirects();
$check('přesměrování: staré záložky nejsou v mapě a cíl je záložka v mapě nebo rozcestník sekce',
    array_reduce(array_keys($redirects), static fn(bool $c, string $t): bool => $c && !isset($map[$t]) && (isset($map[$redirects[$t]]) || (str_starts_with($redirects[$t], 'sekce:') && isset($sections[substr($redirects[$t], 6)]))), true)
    && $redirects['control'] === 'prehled' && $redirects['class_overview'] === 'prehled' && $redirects['growth'] === 'cesty' && $redirects['skills'] === 'kompetence' && $redirects['mastery'] === 'kompetence'
    && $redirects['filters'] === 'sekce:sprava' && $redirects['automations'] === 'sekce:sprava');

// ---------------------------------------------------------------- 2) role
$avail = teacher58_tabs();   // v legacy režimu není modul „ucitele“ (jen v režimu účtů)
$admin = teacher68_visible_tabs('admin', true, $avail);
$teacher = teacher68_visible_tabs('teacher', true, $avail);
$assistant = teacher68_visible_tabs('assistant', true, $avail);
$check('role: administrátor vidí všechny záložky mapy (v legacy režimu bez účtů učitelů)', array_values(array_diff(array_keys($map), $admin)) === ['ucitele'] && count(teacher68_visible_tabs('admin', true, $v58Keys)) === count($map));
$check('role: učitel nemá Tým a role (roles.manage), ale má Demo účty a Editor úrovní', !in_array('team_admin', $teacher, true) && in_array('demo_accounts', $teacher, true) && in_array('editor', $teacher, true));
$check('role: asistent nevidí správu obsahu a účtů (editor, obsah, ekonomika, demo účty, přístupy, dotazník, tým a role)',
    array_intersect(['authoring', 'editor', 'ekonomika', 'demo_accounts', 'pristupy', 'intake', 'team_admin'], $assistant) === [] && in_array('attention', $assistant, true) && in_array('kompetence', $assistant, true));
$check('role: asistent ⊆ učitel ⊆ administrátor', array_diff($assistant, $teacher) === [] && array_diff($teacher, $admin) === []);
$check('role: admin-only záložky (Učitelé, Identita, Provoz, Kvalita dat) vidí jen administrátor',
    array_intersect(['ucitele', 'identita', 'provoz', 'quality'], teacher68_visible_tabs('teacher', false, array_values(array_diff($v58Keys, ['ucitele', 'identita', 'provoz'])))) === []);
$only = teacher68_visible_sections(['attention']);
$check('menu: prázdná sekce se nevykreslí (jediná záložka → jediná sekce)', array_keys($only) === ['dnes'] && count(teacher68_visible_sections($admin)) === 6 && teacher68_visible_sections($assistant) !== []);

// ---------------------------------------------------------------- 3) pure funkce: přesměrování, návrat, kroky, žák
$vis = $admin;
$check('redirect: control + třída → ?tab=prehled&class=…, neplatná třída se nepřenáší, cíl bez oprávnění → Dnes, filters → rozcestník Správa',
    teacher68_redirect_url('control', ['class' => 'class_3a'], $vis) === 'teacher.php?tab=prehled&class=class_3a'
    && teacher68_redirect_url('control', ['class' => 'class_3a&x=1'], $vis) === 'teacher.php?tab=prehled'
    && teacher68_redirect_url('growth', [], array_values(array_diff($vis, ['cesty']))) === 'teacher.php?tab=attention'
    && teacher68_redirect_url('filters', [], $vis) === 'teacher.php?tab=sekce&sekce=sprava' && teacher68_redirect_url('overview', [], $vis) === null);
$bad = ['//evil.example', 'https://evil.example', 'javascript:alert(1)', '?tab=x&class=../..', '?tab=a b', '?tab=teach&class=class_3a&evil=1', "?tab=x\r\nSet-Cookie:a=b", '?tab=', 'teacher.php?tab=attention'];
$leaks = array_filter($bad, static fn(string $r): bool => teacher68_return_url($r) !== 'teacher.php?tab=attention');
$check('návrat po přepnutí vzhledu: jen ?tab=[a-z0-9_]+(&class=class_x), jinak Dnes (žádný open redirect)' . ($leaks ? ' [' . implode('|', $leaks) . ']' : ''),
    $leaks === [] && teacher68_return_url('?tab=prehled&class=class_3a') === 'teacher.php?tab=prehled&class=class_3a' && teacher68_return_url('?tab=student360') === 'teacher.php?tab=student360');
$steps = static fn(string $tab, ?array $v = null): array => array_column(teacher68_next_steps($tab, $v ?? $admin, 'class_3a'), 'tab');
$check('další kroky: ráno → cesty + kompetence, kompetence → projekty, projekty → hodnocení, přehled → hlášení; skryté kroky se nezobrazí',
    array_intersect(['cesty', 'kompetence'], $steps('hodnoceni66')) === ['cesty', 'kompetence'] && $steps('kompetence') === ['projekty65'] && $steps('projekty65') === ['hodnoceni66'] && $steps('prehled') === ['hlaseni']
    && $steps('kompetence', array_values(array_diff($admin, ['projekty65']))) === [] && $steps('hlaseni') === []);
$hub = teacher68_student_hub_links('class_3a', 'class_3a:student:abc', $admin);
$labels = array_column($hub, 'label');
$check('rozcestník žáka: kompetence, cesty, projekty, návrhy hodnocení, intervence, lab – každý odkaz nese ?class= a ?student= (zakódováno)',
    $labels === ['Kompetence', 'Výukové cesty', 'Projekty', 'Návrhy hodnocení', 'Intervence', 'Linux Lab'] && array_reduce($hub, static fn(bool $c, array $l): bool => $c && str_contains($l['url'], 'class=class_3a') && str_contains($l['url'], 'student=class_3a%3Astudent%3Aabc'), true)
    && teacher68_student_hub_links('class_3a&x', 'k', $admin) === [] && count(teacher68_student_hub_links('class_3a', 'k', $assistant)) < count($hub) + 1);
$crumbs = teacher68_breadcrumb('kompetence', 'class_3a');
$check('drobečky: Cockpit › Hodnocení › Kompetence (poslední bez odkazu), neznámá záložka jen Cockpit', array_column($crumbs, 0) === ['Cockpit', 'Hodnocení', 'Kompetence'] && $crumbs[2][1] === null && count(teacher68_breadcrumb('neexistuje')) === 1);

// ---------------------------------------------------------------- 4) politiky teacher59 (snapshot)
$post = teacher59_action_policies();
unset($post['teacher68_theme_set']);
unset($post['lc72_approve'], $post['lc72_return']);   // v72: nové akce v novém modulu `schvalovani` – vyjmuté ze snímku stejně jako teacher68_theme_set (změna limitu auditu, BUILD_MANIFEST_V72.md)
ksort($post);
$get = teacher59_get_policies();
foreach (['kompetence', 'cesty', 'projekty65', 'hodnoceni66', 'labdata'] as $t) unset($get[$t . '|student']);
ksort($get);
$postSha = hash('sha256', json_encode($post));
$check('politiky teacher59: POST tabulka = snímek v69 (bez vyřazených akcí; kromě teacher68_theme_set) sha=' . $postSha, $postSha === V68C_POST_POLICY_SHA);
$check('politiky teacher59: GET tabulka beze změny (kromě čtecích ?student= politik v68)', hash('sha256', json_encode($get)) === V68C_GET_POLICY_SHA);
$check('nová politika: teacher68_theme_set pro všechny role (view), neznámá teacher68_* akce je zakázaná, GET ?student= vyžaduje třídu v rozsahu',
    teacher59_action_policy('teacher68_theme_set') === ['class' => 'optional'] && teacher59_action_policy('teacher68_other')['deny'] === true
    && teacher_action_permission('teacher68_theme_set') === 'view' && teacher_permission_for_role('assistant', 'view') && teacher_action_permission('teacher68_other') === 'view'
    && (teacher59_get_policies()['kompetence|student']['class'] ?? '') === 'required');

// ---------------------------------------------------------------- 5) HTTP: menu podle role, přesměrování, přepínač, vyřazené pohledy
$expect = ['admin' => $admin, 'teacher' => $teacher, 'assistant' => $assistant];
$menuTabs = static function (string $html): array {
    if (preg_match('~<aside class="t68-side".*?</aside>~s', $html, $m) !== 1) return [];
    preg_match_all('~href="\?tab=([a-z0-9_]+)(?:&amp;[^"]*)?"~', $m[0], $mm);
    return $mm[1];
};
$cookieOf = static fn(array $r): string => implode("\n", array_filter(array_map('strval', (array)($r['raw_headers'] ?? [])), static fn(string $h): bool => stripos($h, 'Set-Cookie: edu_theme') === 0));
foreach ($expect as $role => $expectedTabs) {
    $h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V68C_THEME_KEY, 'EDUCANET_TEACHER_ROLE' => $role, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1']);
    try {
        $login = audit_login_teacher($h, V68C_THEME_KEY, 'Audit Učitel');
        $page = $h->request('GET', '/teacher.php?tab=attention');
        $tabs = $menuTabs((string)$page['body']);
        $check('HTTP ' . $role . ': stránka Dnes je čistá a menu = teacher68_visible_tabs (' . count($tabs) . ' odkazů)', audit_response_clean($page) && $tabs !== [] && $tabs === array_values(array_intersect($expectedTabs, $tabs)) && count($tabs) === count($expectedTabs)
            && array_diff($tabs, $expectedTabs) === [] && count(array_unique($tabs)) === count($tabs));
        $check('HTTP ' . $role . ': menu nemá víc než 6 sekcí, drobečky a odkaz „Přeskočit na obsah“ jsou v HTML', preg_match_all('~<details class="t68-sec"~', (string)$page['body']) <= 12 && substr_count((string)$page['body'], 'class="t68-crumbs"') === 1 && str_contains((string)$page['body'], 'class="t68-skip"'));
        if ($role === 'admin') {
            $statuses = [];
            foreach (array_keys($redirects) as $old) {
                $r = $h->request('GET', '/teacher.php', ['tab' => $old, 'class' => 'class_3a'], ['follow_redirects' => false]);
                $statuses[$old] = [(int)$r['status'], (string)($r['headers']['Location'] ?? '')];
            }
            $okRedirect = array_reduce(array_keys($redirects), static fn(bool $c, string $o): bool => $c && $statuses[$o][0] === 302 && str_starts_with($statuses[$o][1], 'teacher.php?tab='), true);
            $check('HTTP: staré záložky vrací 302 na novou sekci (control/class_overview → prehled, growth → cesty, skills/mastery → kompetence, filters/automations → rozcestník Správa)',
                $okRedirect && str_contains($statuses['control'][1], 'tab=prehled') && str_contains($statuses['growth'][1], 'tab=cesty') && str_contains($statuses['skills'][1], 'tab=kompetence') && str_contains($statuses['filters'][1], 'tab=sekce&sekce=sprava'));
            $hub = $h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'sprava']);
            $check('HTTP: rozcestník sekce Správa vykreslí karty záložek, neplatná sekce nic nevypíše', audit_response_clean($hub) && str_contains((string)$hub['body'], 't68-cards') && str_contains((string)$hub['body'], 'Audit operací')
                && !str_contains((string)$h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'xyz'])['body'], 't68-cards'));
            $s360 = $h->request('GET', '/teacher.php', ['tab' => 'student360', 'class' => 'class_3a']);
            $check('HTTP: stránka bez studenta ve třídě nespadne (detail žáka)', in_array((int)$s360['status'], [200], true) && !preg_match('/Fatal error|Uncaught/', (string)$s360['body']));
            // přepínač vzhledu
            $csrf = (string)$h->csrfToken((string)$page['body']);
            $noCsrf = $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => 'dark', 'return' => '?tab=attention'], ['follow_redirects' => false]);
            $check('přepínač: bez CSRF → 419 a žádné cookie', (int)$noCsrf['status'] === 419 && $cookieOf($noCsrf) === '');
            $okSet = $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => 'dark', 'return' => '?tab=prehled&class=class_3a', 'csrf' => $csrf], ['follow_redirects' => false]);
            $check('přepínač: platná volba → 303 zpět na ?tab=prehled&class=class_3a a cookie edu_theme=dark', (int)$okSet['status'] === 303 && ($okSet['headers']['Location'] ?? '') === 'teacher.php?tab=prehled&class=class_3a' && str_contains($cookieOf($okSet), 'edu_theme=dark'));
            $evil = $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => 'light', 'return' => '//evil.example/x', 'csrf' => $csrf], ['follow_redirects' => false]);
            $check('přepínač: návrat na cizí adresu se nepřijme (Location zůstává uvnitř cockpitu)', (int)$evil['status'] === 303 && ($evil['headers']['Location'] ?? '') === 'teacher.php?tab=attention' && str_contains($cookieOf($evil), 'edu_theme=light'));
            $badTheme = $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => '<script>', 'return' => '?tab=attention', 'csrf' => $csrf], ['follow_redirects' => false]);
            $check('přepínač: neplatná hodnota → žádné cookie s touto hodnotou', $cookieOf($badTheme) === '' || !str_contains($cookieOf($badTheme), 'script'));
            foreach (['dark' => ['dark', true, false], 'light' => ['light', false, false], 'system' => ['system', true, true]] as $pref => [$attr, $darkLink, $media]) {
                $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => $pref, 'return' => '?tab=attention', 'csrf' => $csrf], ['follow_redirects' => false]);
                $body = (string)$h->request('GET', '/teacher.php?tab=attention')['body'];
                $hasLink = str_contains($body, 'tokens-dark-v67.css') && str_contains($body, 'teacher-dark-v68.css');
                $linkMedia = preg_match('~<link[^>]*teacher-dark-v68\.css[^>]*media="\(prefers-color-scheme: dark\)"~', $body) === 1;
                $check('motiv ' . $pref . ': <html data-theme="' . $attr . '">, meta color-scheme, tmavé CSS ' . ($darkLink ? 'načtené' : 'nenačtené') . ($media ? ' s media dotazem' : ''),
                    str_contains($body, '<html lang="cs" data-theme="' . $attr . '">') && preg_match('~<meta name="color-scheme" content="' . ($pref === 'system' ? 'light dark' : $pref) . '">~', $body) === 1 && $hasLink === $darkLink && $linkMedia === $media
                    && preg_match('~aria-pressed="true"[^>]*>' . ['dark' => 'Tmavý', 'light' => 'Světlý', 'system' => 'Systém'][$pref] . '~', $body) === 1);
            }
            $ucet = $h->request('GET', '/teacher.php?tab=nenizadna');
            $check('neznámá záložka se vrátí na Dnes (ne na přehled školy)', audit_response_clean($ucet) && str_contains((string)$ucet['body'], 'Co mám dnes řešit'));
        }
    } finally {
        $h->stop();
    }
}
$anon = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V68C_THEME_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1']);
try {
    $loginPage = $anon->request('GET', '/teacher.php');
    $anonSet = $anon->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => 'dark', 'return' => '?tab=attention', 'csrf' => (string)$anon->csrfToken((string)$loginPage['body'])], ['follow_redirects' => false]);
    $check('nepřihlášený učitel nemůže přepínat vzhled přes cockpit (žádné cookie, přesměrování na přihlášení)', in_array((int)$anonSet['status'], [302, 303], true) && $cookieOf($anonSet) === '');
    $anonTab = $anon->request('GET', '/teacher.php?tab=control', [], ['follow_redirects' => false]);
    $check('nepřihlášený: ?tab=control nepřesměrovává a neukazuje data (přihlašovací karta)', (int)$anonTab['status'] === 200 && str_contains((string)$anonTab['body'], 'teacher_login') && !str_contains((string)$anonTab['body'], 't68-nav'));
    // vyřazené žákovské pohledy
    audit_login_student($anon, 'class_3a', 'Audit Žák');
    $locs = [];
    foreach (['v48_state' => '?view=v48_state&lesson=1', 'continue' => '?view=continue', 'one_task' => '?view=one_task&task=course', 'one_task_kb' => '?view=one_task&task=kb&topic=ip_adresy'] as $k => $url) {
        $r = $anon->request('GET', '/' . $url, [], ['follow_redirects' => false]);
        $locs[$k] = [(int)$r['status'], (string)($r['headers']['Location'] ?? '')];
    }
    $check('žák: ?view=v48_state a ?view=continue a ?view=one_task → 302 na ?view=dashboard (kb s platným tématem → kb_lesson)',
        $locs['v48_state'] === [302, '?view=dashboard'] && $locs['continue'] === [302, '?view=dashboard'] && $locs['one_task'] === [302, '?view=dashboard'] && $locs['one_task_kb'] === [302, '?view=kb_lesson&topic=ip_adresy']);
    $post = $anon->request('POST', '/', ['action' => 'neexistuje', 'view' => 'continue'], ['follow_redirects' => false]);
    $check('žák: POST na vyřazený pohled se nepřesměrovává (CSRF platí dál)', (int)$post['status'] === 419);
} finally {
    $anon->stop();
}

// ---------------------------------------------------------------- 6) vyřazení souborů, Linux Lab, CSS
$retired = $ROOT . '/retired/v68/app/views/v48_state.php';
$check('vyřazení: app/views/v48_state.php neexistuje, kopie je v retired/v68 (SHA-256 shodné se zálohou), route neexistuje',
    !is_file($ROOT . '/app/views/v48_state.php') && is_file($retired) && strlen((string)hash_file('sha256', $retired)) === 64 && !str_contains($read('app/routes.php'), "'match' => ['v48_state']") && !str_contains($read('index.php'), 'views/v48_state'));
$check('vyřazení (v69): one_task.php je v retired/v69 (ne v app/views), docs/V68_KANDIDATI_VYRAZENI.md existuje', !is_file($ROOT . '/app/views/one_task.php') && is_file($ROOT . '/retired/v69/app/views/one_task.php') && is_file($ROOT . '/docs/V68_KANDIDATI_VYRAZENI.md'));
$hashes = require __DIR__ . '/lib/v67_lab_hashes.php';
$changed = array_keys(array_filter($hashes, static fn(string $sha, string $f): bool => !is_file($ROOT . '/' . $f) || hash_file('sha256', $ROOT . '/' . $f) !== $sha, ARRAY_FILTER_USE_BOTH));
$check('Linux Lab (v57/v58) beze změny – SHA-256 ' . count($hashes) . ' souborů' . ($changed ? ' [změněno: ' . implode(', ', $changed) . ']' : ''), $changed === [] && count($hashes) > 50);
$css = $read('assets/teacher-shell-v68.css');
$check('rámec: boční panel od 1024 px, na mobilu <details> menu, cíle ≥ 44 px, reduced-motion, focus-visible (text zdroje)',
    str_contains($css, '@media (min-width: 1024px)') && preg_match('~\.t68-menu \{ display: none; \}~', $css) === 1 && substr_count($css, 'min-height: 44px') >= 8 && str_contains($css, 'prefers-reduced-motion') && str_contains($css, ':focus-visible'), false);
$check('rámec: CSS cockpitu nepoužívá pevné barvy (jen var(--ui-*)/var(--kN))', preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', preg_replace('~/\*.*?\*/~s', '', $css)) !== 1);

exit(audit_summary($state, 'V68_COCKPIT'));
