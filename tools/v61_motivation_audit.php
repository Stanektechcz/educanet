<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · audit části D (motivace a gamifikace, bez vlivu na známky).
 *   php tools/v61_motivation_audit.php
 *
 * 1) Série: víkend, svátek a prázdniny sérii nepřerušují, vynechaný školní den ano, dnešek ještě nerozhoduje.
 * 2) Cíle a odměny: idempotence klíče goal:<den|týden>:<id>, denní a týdenní strop XP (i pod souběžnou hrou
 *    s klíči), odměna jen za dnešek/tento týden, cíle nezapisují body, ≤ 3 denní + 2 týdenní.
 * 3) Sezónní odznaky: pololetí navazují bez mezery, id i vzhled bez kolizí s ostatními odznaky, postup a
 *    získání, uložení přežije změnu školního roku.
 * 4) Souboje: odveta respektuje opt-in a limity, jen účastník a jen jednou; přehled učitele je čtení
 *    (nic nezapíše), rozsah tříd vynucený přes HTTP (učitel, asistent, bez tříd), asistent a učitel
 *    nemají žádnou zapisující akci; statistiky žákovi jen s opt-inem a bez jmen.
 * 3) Obchod: náhled kosmetiky nic nezapíše (whitelist parametru), oblíbené (limit), sezónní nabídka na serveru
 *    i při nákupu, typy na bílé listině, nic neovlivní XP/body navíc/aréna; CSRF u všech POST přes HTTP.
 * Dočasné úložiště (edu_audit_temp_storage) + vestavěný server; fiktivní žáci, ostrou storage/ nikdy nečte ani nezapisuje.
 * Konec: V61_MOTIVATION_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace('\\', '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v61-motivation')), '/');
require_once $ROOT . '/bootstrap.php';   // před jakýmkoli výstupem (session_start)
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['app/lib.php', 'app/views/_layout.php', 'student_v55.php', 'student_v55_views.php', 'zero_friction_v50_6.php', 'unified_page_shell_v50_7.php', 'one_task_v50_5.php',
    'goal_navigator_v50_4.php', 'hands_on_learning_v50.php', 'independent_growth_v50.php', 'learning_v56.php', 'session_v53.php', 'tutorial_v52.php', 'linux_v57_lab.php',
    'arena_v57.php', 'teacher_operations_v46.php', 'teacher_operations_plus_v46_1.php', 'teacher_operations_control_v46_2.php', 'intake_v51.php', 'accounts_v53.php',
    'runtime_content.php', 'teacher_v58.php', 'robots_v58.php', 'teamgames_v58_teacher_views.php', 'arena_v58_ctf.php', 'arena_v58_incident.php', 'lab_v58_editor.php',
    'teacher_demo_accounts.php', 'teacher_accounts_v59_admin.php', 'teamgames_v58_projector_views.php', 'intake_v51_teacher.php', 'student_social_views.php', 'skill_views.php',
    'points_v53.php', 'points_v60.php', 'marketplace_v60.php', 'marketplace_v60_views.php', 'marketplace_v60_teacher_views.php', 'projects_v60.php', 'projects_v60_teacher_views.php',
    'feedback_v60.php', 'feedback_v60_teacher_views.php', 'profile_v60.php', 'profile_v60_views.php', 'lab_v58_learning.php', 'badges_v60.php', 'arena_v58_weekly.php',
    'arena_v60_challenge.php', 'arena_v60_challenge_views.php', 'motivation_v61.php', 'motivation_v61_views.php', 'arena_v61_duels.php', 'arena_v61_views.php',
    'arena_v61_teacher_views.php', 'marketplace_v61.php', 'marketplace_v61_views.php'] as $rel) {
    require_once $ROOT . '/' . $rel;
}
require_once __DIR__ . '/lib/v59_scope_fixtures.php';
$GLOBALS['nextLessons'] = [];
$GLOBALS['extendedLessons'] = [];
$GLOBALS['view'] = 'profile';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));

const V61M_CLASS = 'class_3a';
const V61M_OTHER = 'class_1a';
const V61M_NAMES_3A = ['Tereza Trojanová', 'Tomáš Trojan', 'Petr Třetí'];
const V61M_NAMES_1A = ['Jitka Jedličková', 'Jan Jedlička'];
$keyOf = static fn(string $class, string $label): string => social_student_key($class, $label);
$readText = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$fileHash = static fn(string $rel): string => is_file($tmp . '/' . $rel) ? (string)hash_file('xxh128', $tmp . '/' . $rel) : 'none';
$snapshot = static fn(): array => v59sf_snapshot($tmp);
$caught = static function (callable $fn): string {
    try { $fn(); } catch (Throwable $e) { return $e->getMessage(); }
    return '';
};

// ---------------------------------------------------------------- 0) soubory a invariant labu
$mine = ['motivation_v61.php', 'motivation_v61_views.php', 'arena_v61_duels.php', 'arena_v61_views.php', 'arena_v61_teacher_views.php', 'marketplace_v61.php', 'marketplace_v61_views.php', 'app/actions/motivation.php'];
$forbiddenCall = '/\b(exec|shell_exec|system|passthru|proc_open|popen|pcntl_exec|eval|assert|create_function|fsockopen|stream_socket_client|mail)\s*\(|`|\bcurl_|\bsocket_/';
$problems = [];
foreach ($mine as $rel) {
    $src = $readText($rel);
    if (!str_contains($src, 'declare(strict_types=1);') || !str_contains($src, 'http_response_code(403)')) $problems[] = $rel . ': strict_types/guard';
    if (preg_match($forbiddenCall, $src) === 1) $problems[] = $rel . ': zakázané volání';
    if (substr_count($src, "\n") > 800) $problems[] = $rel . ': > 800 řádků';
}
$check('nové soubory: strict_types, guard, bez exec/eval/sockety/curl, ≤ 800 řádků' . ($problems ? ' – ' . implode('; ', $problems) : ''), $problems === [], false);
$css = $readText('assets/motivation-v61.css');
$check('CSS motivace: prefers-reduced-motion, cíl dotyku 44 px, jen tokeny značky (žádná pevná barva #hex)', str_contains($css, 'prefers-reduced-motion') && str_contains($css, 'min-height: 44px') && preg_match('/#[0-9a-fA-F]{3,6}\b/', $css) !== 1, false);
$check('obchodní typy dál na bílé listině (cosmetic, content, lab_hint, other)', MKT60_TYPES === ['cosmetic', 'content', 'lab_hint', 'other']);
// Obchod nesmí volat nic, co mění XP/body/aréna (token sken volání).
$shopFiles = ['marketplace_v61.php', 'marketplace_v61_views.php', 'app/actions/motivation.php'];
$bad = [];
foreach ($shopFiles as $rel) {
    foreach (token_get_all($readText($rel)) as $tok) {
        if (is_array($tok) && $tok[0] === T_STRING && preg_match('/^(learning_award_once|learning_save_profile|mot61_award_xp|fb60_award_xp|pts53_award|pts60_|arena57_|arena60_challenge_|project_grade|teacher_grade)/', $tok[1]) === 1) $bad[] = $rel . ':' . $tok[1];
    }
}
$check('obchod/oblíbené/náhled nevolají nic, co mění XP, body, známky ani arénu' . ($bad ? ' – ' . implode(', ', $bad) : ''), $bad === [], false);

// ---------------------------------------------------------------- 1) série a dny bez školy
$act = static fn(array $days): array => array_fill_keys($days, ['labs' => 1, 'nohint' => 0, 'events' => 0]);
$check('běžná středa není volný den; sobota, vánoční prázdniny a léto ano', !mot61_is_free_day('2026-10-14') && mot61_is_free_day('2026-10-17') && mot61_is_free_day('2026-12-28') && mot61_is_free_day('2027-07-15'));
$check('kalendář školního roku dodá svátek a prázdninovou středu (school_year.php)', isset(mot61_calendar_free_days()['2026-10-28']) && isset(mot61_calendar_free_days()['2027-02-24']));
$check('víkend sérii nepřeruší: pátek + pondělí = 2', mot61_current_streak($act(['2026-10-09', '2026-10-12']), '2026-10-12') === 2);
$check('vynechaný školní den sérii přeruší (po–st bez út = 1)', mot61_current_streak($act(['2026-10-12', '2026-10-14']), '2026-10-14') === 1);
$check('dnešek bez aktivity sérii ještě neruší (pondělí aktivní, úterý zatím ne = 1)', mot61_current_streak($act(['2026-10-12']), '2026-10-13') === 1);
$check('včerejší školní den bez aktivity = série 0', mot61_current_streak($act(['2026-10-12']), '2026-10-14') === 0);
$check('vánoční prázdniny sérii nepřeruší (22. 12. + 4. 1. = 2)', mot61_current_streak($act(['2026-12-22', '2027-01-04']), '2027-01-04') === 2);
$check('svátek a podzimní prázdniny sérii nepřeruší (27. 10. + 2. 11. = 2)', mot61_current_streak($act(['2026-10-27', '2026-11-02']), '2026-11-02') === 2);
$check('o víkendu (neděle) se série drží i bez aktivity', mot61_current_streak($act(['2026-10-09']), '2026-10-11') === 1);
$week = $act(['2026-09-14', '2026-09-15', '2026-09-16', '2026-09-17', '2026-09-18', '2026-09-21', '2026-09-22']);
$best = mot61_best_streak($week, '2026-09-30');
$check('nejdelší série přes víkend = 7 a den dosažení cíle série je 22. 9.', $best['best'] === 7 && $best['target_at'] === '2026-09-22');
$gap = $act(['2026-09-14', '2026-09-15', '2026-09-16', '2026-09-18', '2026-09-21']);
$check('mezera ve školním dni sérii ukončí (nejdelší = 3, cíl 7 nedosažen)', mot61_best_streak($gap, '2026-09-30') === ['best' => 3, 'target_at' => null]);

// ---------------------------------------------------------------- fixture: žáci a data
function v61m_seed_roster(string $dir, array $byClass): void
{
    @mkdir($dir . '/intake', 0700, true);
    $rows = [];
    $i = 0;
    foreach ($byClass as $class => $labels) {
        foreach ($labels as $label) {
            [$first, $last] = explode(' ', $label, 2);
            $rows[] = ['id' => 'fx61m_' . $i++, 'class_id' => $class, 'submitted_at' => date(DATE_ATOM), 'student' => ['first_name' => $first, 'last_name' => $last, 'preferred_name' => '', 'seat_id' => '', 'seat_label' => ''], 'answers' => [], 'assessment' => [], 'source' => 'fixture', 'imported_at' => date(DATE_ATOM)];
        }
    }
    file_put_contents($dir . '/intake/responses.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode($rows, JSON_UNESCAPED_UNICODE));
}
v61m_seed_roster($tmp, [V61M_CLASS => V61M_NAMES_3A, V61M_OTHER => V61M_NAMES_1A]);
[$nameA, $nameB, $nameC] = V61M_NAMES_3A;
[$kA, $kB, $kC] = [$keyOf(V61M_CLASS, $nameA), $keyOf(V61M_CLASS, $nameB), $keyOf(V61M_CLASS, $nameC)];
$roster = arena57_roster(V61M_CLASS);
$check('fixture: fiktivní žáci 3.A jsou v adresáři třídy', isset($roster[$kA], $roster[$kB], $roster[$kC]));
$lkA = mot61_learning_key(V61M_CLASS, $kA);
$check('klíč profilu učení odvozený z klíče žáka (class:s:…) a odmítnutí cizího tvaru', preg_match('/^class_3a:s:[a-f0-9]{24}$/', $lkA) === 1 && mot61_learning_key(V61M_CLASS, 'class_1a:student:' . str_repeat('a', 24)) === '' && mot61_learning_key(V61M_CLASS, 'x') === '');
$learnPath = learning_profiles_path();
$setProfile = static function (string $lk, array $profile) use ($learnPath): void {
    storage_map_update($learnPath, $lk, static fn(?array $p): array => array_replace(learning_profile_default(), $p ?? [], $profile));
};
$profileOf = static fn(string $lk): array => (array)(storage_read($learnPath, false)[$lk] ?? []);
$setSolved = static function (string $class, string $key, array $solved): void {
    lab57_store_update(lab57_state_path($class, $key), static function (array $d) use ($solved): array { $d['solved']['practice'] = $solved; return $d; });
};
$setProfile($lkA, ['xp' => 100, 'events' => []]);
$now = (int)strtotime('2026-10-14 12:00:00');   // středa, vyučovací den, týden 2026-W42
$setSolved(V61M_CLASS, $kA, ['a1' => ['at' => '2026-10-14T09:00:00+02:00', 'hints' => 0], 'a2' => ['at' => '2026-10-14T09:30:00+02:00', 'hints' => 2]]);
$walletBefore = pts53_balance(V61M_CLASS, $kA);

// ---------------------------------------------------------------- 2) cíle, odměny, strop
$defs = mot61_goal_defs();
$check('≤ 3 denní a ≤ 2 týdenní cíle; součet XP denních cílů ≤ denní strop, týdenních ≤ týdenní strop',
    count($defs['day']) <= 3 && count($defs['week']) <= 2 && array_sum(array_column($defs['day'], 'xp')) <= MOT61_DAILY_XP_CAP && array_sum(array_column($defs['week'], 'xp')) <= MOT61_WEEKLY_XP_CAP);
$o0 = mot61_overview(V61M_CLASS, $kA, $lkA, mot61_events_by_key($lkA), $now, false);
$byId = static function (array $o, string $kind, string $id): array { foreach ($o['goals'][$kind] as $g) { if ($g['id'] === $id) return $g; } return []; };
$check('vyhodnocení bez odměny: dvě úrovně dnes = lab2 splněn, bez nápovědy = splněn, aktivita s XP ne; XP profilu se nezměnilo',
    $byId($o0, 'day', 'lab2')['done'] === true && $byId($o0, 'day', 'nohint1')['done'] === true && $byId($o0, 'day', 'learn1')['done'] === false && $o0['granted'] === 0 && (int)$profileOf($lkA)['xp'] === 100);
$o1 = mot61_overview(V61M_CLASS, $kA, $lkA, mot61_events_by_key($lkA), $now, true);
$check('odměna: +4 XP za dva splněné denní cíle, události goal:2026-10-14:lab2/nohint1 v profilu', $o1['granted'] === 4 && (int)$profileOf($lkA)['xp'] === 104 && isset($profileOf($lkA)['events']['goal:2026-10-14:lab2'], $profileOf($lkA)['events']['goal:2026-10-14:nohint1']));
$o2 = mot61_overview(V61M_CLASS, $kA, $lkA, mot61_events_by_key($lkA), $now, true);
$check('idempotence: opakované zobrazení nic nepřipíše (XP zůstává 104, 2 události cílů)', $o2['granted'] === 0 && (int)$profileOf($lkA)['xp'] === 104 && count(array_filter(array_keys($profileOf($lkA)['events']), 'mot61_is_goal_event')) === 2);
$check('opakovaný přímý pokus o stejný klíč vrátí 0 a XP nezmění', mot61_award_xp($lkA, 'goal:2026-10-14:lab2', 2, 'goal:2026-10-14:', MOT61_DAILY_XP_CAP) === 0 && (int)$profileOf($lkA)['xp'] === 104);
$events = $profileOf($lkA)['events'];
$events['quiz:audit'] = ['xp' => 10, 'at' => '2026-10-14T10:00:00+02:00'];
$setProfile($lkA, ['events' => $events, 'xp' => 114]);
$o3 = mot61_overview(V61M_CLASS, $kA, $lkA, mot61_events_by_key($lkA), $now, true);
$check('aktivita s XP dnes splní třetí cíl (+1 XP); součet za den = strop 5', $o3['granted'] === 1 && $o3['xp_today'] === MOT61_DAILY_XP_CAP && (int)$profileOf($lkA)['xp'] === 115);
$check('DENNÍ STROP: další klíč téhož dne se při vyčerpaném stropu nepřipíše (0 XP, XP profilu beze změny)', mot61_award_xp($lkA, 'goal:2026-10-14:bonus', 3, 'goal:2026-10-14:', MOT61_DAILY_XP_CAP) === 0 && (int)$profileOf($lkA)['xp'] === 115 && !isset($profileOf($lkA)['events']['goal:2026-10-14:bonus']));
$check('klíč mimo předponu stropu a klíč, který není cíl, se odmítnou', mot61_award_xp($lkA, 'goal:2026-10-15:x', 1, 'goal:2026-10-14:', 5) === 0 && mot61_award_xp($lkA, 'quiz:x', 1, 'quiz:', 5) === 0 && mot61_award_xp('', 'goal:2026-10-14:y', 1, 'goal:2026-10-14:', 5) === 0);
$check('goal události se nepočítají jako aktivita (žák jen s goal událostmi: série 0, cíl aktivity nesplněn)', (static function () use ($keyOf, $setProfile): bool {
    $k = $keyOf(V61M_CLASS, 'Petr Třetí'); $lk = mot61_learning_key(V61M_CLASS, $k);
    $setProfile($lk, ['xp' => 5, 'events' => ['goal:2026-10-14:lab2' => ['xp' => 2, 'at' => '2026-10-14T08:00:00+02:00']]]);
    $o = mot61_overview(V61M_CLASS, $k, $lk, mot61_events_by_key($lk), (int)strtotime('2026-10-14 12:00:00'), false);
    return $o['streak'] === 0 && $o['goals']['day'][2]['done'] === false;
})());
// týdenní cíle: 6 úrovní + 3 aktivní dny v týdnu W42 (po 12., út 13., st 14. 10.)
$setSolved(V61M_CLASS, $kA, ['a1' => ['at' => '2026-10-14T09:00:00+02:00', 'hints' => 0], 'a2' => ['at' => '2026-10-14T09:30:00+02:00', 'hints' => 2],
    'b1' => ['at' => '2026-10-12T09:00:00+02:00', 'hints' => 1], 'b2' => ['at' => '2026-10-12T09:10:00+02:00', 'hints' => 1], 'b3' => ['at' => '2026-10-12T09:20:00+02:00', 'hints' => 1], 'b4' => ['at' => '2026-10-13T09:20:00+02:00', 'hints' => 1]]);
$o4 = mot61_overview(V61M_CLASS, $kA, $lkA, mot61_events_by_key($lkA), $now, true);
$check('týdenní cíle: 6 úrovní (+8 XP) a 3 aktivní dny (+6 XP) v týdnu W42, xp_week = 14 ≤ strop 15', $o4['granted'] === 14 && $o4['xp_week'] === 14 && isset($profileOf($lkA)['events']['goal:2026-W42:lab6'], $profileOf($lkA)['events']['goal:2026-W42:days3']));
$check('TÝDENNÍ STROP: zbývající 1 XP se připíše jen jednou, další klíč už 0', mot61_award_xp($lkA, 'goal:2026-W42:extra', 5, 'goal:2026-W42:', MOT61_WEEKLY_XP_CAP) === 1 && mot61_award_xp($lkA, 'goal:2026-W42:extra2', 5, 'goal:2026-W42:', MOT61_WEEKLY_XP_CAP) === 0);
$xpFinal = (int)$profileOf($lkA)['xp'];
$o5 = mot61_overview(V61M_CLASS, $kA, $lkA, mot61_events_by_key($lkA), (int)strtotime('2026-10-15 12:00:00'), true);
$check('odměna jen za dnešek: následující den bez nové aktivity nic nepřipíše (včerejší cíle se zpětně neodměňují)', $o5['granted'] === 0 && (int)$profileOf($lkA)['xp'] === $xpFinal);
$check('cíle nezapisují body ani známky (zůstatek peněženky beze změny, žádný soubor známek)', pts53_balance(V61M_CLASS, $kA) === $walletBefore && !is_file($tmp . '/project_grades.json.php'));
$check('série z existujících dat: aktivita 12.–14. 10. = 3 dny v sérii', mot61_current_streak($o4['activity'], '2026-10-14') === 3 && $o4['streak'] === 3);

// ---------------------------------------------------------------- 3) sezónní odznaky
$seasons = mot61_seasons();
$sy = (array)mot61_school_year()['meta'];
$check('dvě pololetí pokrývají školní rok bez mezery a překryvu (začátek, konec, navazují o den)', count($seasons) === 2 && $seasons[0]['from'] === $sy['start'] && $seasons[1]['to'] === $sy['end'] && mot61_shift_day($seasons[0]['to'], 1) === $seasons[1]['from']);
$seasonDefs = mot61_season_badge_defs();
$check('10 sezónních odznaků (2 pololetí × 5), unikátní id', count($seasonDefs) === 10 && count(array_unique(array_keys($seasonDefs))) === 10);
$all = [];
foreach (learning_badge_definitions() as $id => $def) $all[(string)$id] = (array)$def;
foreach (lab58_badges(V61M_CLASS, 'audit-student') as $lb) $all['lab_' . (string)$lb['id']] = ['title' => (string)$lb['title'], 'text' => (string)$lb['hint'], 'rarity' => 'epic', 'category' => 'lab'];
foreach (learning_achievement_definitions() as $id => $def) $all['ach_' . (string)$id] = ['title' => (string)$def['title'], 'text' => (string)$def['text'], 'mark' => (string)($def['mark'] ?? ''), 'rarity' => 'common', 'category' => profile60_achievement_category((string)($def['kind'] ?? ''))];
$overlap = array_intersect(array_keys($all), array_keys($seasonDefs));
$union = $all + $seasonDefs;
$bySignature = [];
foreach ($union as $id => $meta) $bySignature[badge60_signature(badge60_variant((string)$id, $meta))][] = (string)$id;
$collisions = array_filter($bySignature, static fn(array $ids): bool => count($ids) > 1 && count(array_filter($ids, static fn(string $i): bool => str_starts_with($i, 'season_'))) > 0);
$check('sezónní odznaky nekolidují s ostatními (' . count($union) . ' odznaků, id i kombinace tvar/kategorie/paleta/vzor/akcent/rarita)' . ($collisions ? ' – kolize: ' . json_encode(array_values($collisions)) : ''), $overlap === [] && $collisions === []);
$svgA = []; $svgDet = true;
foreach ($seasonDefs as $id => $meta) { $svg = badge60_svg($id, $meta, true, 64); $svgDet = $svgDet && $svg === badge60_svg($id, $meta, true, 64); $svgA[preg_replace('~<title>.*?</title>|aria-label="[^"]*"~', '', $svg)][] = $id; }
$check('sezónní odznaky: deterministický SVG a žádné dva stejně vypadající', $svgDet && count($svgA) === 10);
$check('karta získaného i zamčeného sezónního odznaku se vykreslí (název, rarita, postup)', (static function () use ($seasonDefs): bool {
    $id = array_key_first($seasonDefs);
    return str_contains(badge60_card($id, $seasonDefs[$id], true), 'is-earned') && str_contains(badge60_card($id, $seasonDefs[$id], false, 40), '40');
})());
$idp = 'season_' . $seasons[0]['id'] . '_';
$d20 = [];
for ($i = 0; $i < 20; $i++) $d20[] = date('Y-m-d', strtotime('2026-09-14 +' . $i . ' days'));
$goalEvents = [];
for ($i = 0; $i < 15; $i++) $goalEvents['goal:2026-09-' . (14 + $i) . ':g' . $i] = ['xp' => 1, 'at' => date('Y-m-d', strtotime('2026-09-14 +' . $i . ' days')) . 'T10:00:00+02:00'];
$p20 = mot61_season_progress($act($d20), $goalEvents, '2026-10-20', []);
$check('20 aktivních dnů + 15 úrovní + série + 15 cílů = všech 5 odznaků pololetí vč. „Hvězdy pololetí“', count(array_filter(array_keys($p20), static fn(string $id): bool => str_starts_with($id, $idp) && $p20[$id]['earned'])) === 5 && $p20[$idp . 'active']['earned_at'] === '2026-10-03');
$p19 = mot61_season_progress($act(array_slice($d20, 0, 19)), [], '2026-10-20', []);
$check('19 dnů = odznak „Pravidelný“ není získán a ukazuje 95 %', !$p19[$idp . 'active']['earned'] && $p19[$idp . 'active']['percent'] === 95 && !$p19[$idp . 'champion']['earned']);
$p7 = mot61_season_progress($week, [], '2026-10-20', []);
$check('série 7 školních dnů přes víkend získá „V tahu“; s mezerou ne', $p7[$idp . 'streak']['earned'] && !mot61_season_progress($gap, [], '2026-10-20', [])[$idp . 'streak']['earned']);
$check('budoucí pololetí nic nezíská ani při aktivitě v prvním', !array_filter(array_keys($p20), static fn(string $id): bool => str_starts_with($id, 'season_' . $seasons[1]['id']) && $p20[$id]['earned']));
$pStored = mot61_season_progress([], [], '2026-10-20', [$idp . 'lab' => '2026-09-30']);
$check('uložený odznak přežije změnu dat (sbírka se neztratí)', $pStored[$idp . 'lab']['earned'] && $pStored[$idp . 'lab']['earned_at'] === '2026-09-30');
mot61_season_sync(V61M_CLASS, $kA, $p20);
$rowA = mot61_student_row(V61M_CLASS, $kA);
$hashStore = $fileHash(MOT61_STORE);
mot61_season_sync(V61M_CLASS, $kA, $p20);
$check('uložení sbírky: 5 odznaků v úložišti; druhé volání nic nezapíše', count((array)($rowA['seasons'] ?? [])) === 5 && $hashStore === $fileHash(MOT61_STORE) && $hashStore !== 'none');

// ---------------------------------------------------------------- 4) souboje: odveta, opt-in, limity, přehled
$levelId = (string)(lab57_pack_levels((string)array_key_first(lab57_packs()))[0]['id'] ?? 'lvl');
$addDuel = static function (string $id, string $class, string $from, string $to, string $status, array $extra = []) use ($levelId): void {
    arena60_update(static function (array $d) use ($id, $class, $from, $to, $status, $extra, $levelId): array {
        $d['challenges'][] = $extra + ['id' => $id, 'class_id' => $class, 'from_key' => $from, 'to_key' => $to, 'level_id' => $levelId, 'status' => $status, 'created_at' => date(DATE_ATOM, time() - 400000),
            'expires_at' => time() + 86400, 'accepted_at' => date(DATE_ATOM, time() - 399000), 'winner_key' => $status === 'done' ? $from : null, 'finished_at' => null];
        return $d;
    });
};
$rows = static fn(): array => arena60_data()['challenges'];
$t = time();
$addDuel('aaaaaaaaaaaaaaaa', V61M_CLASS, $kA, $kB, 'done');
$m = $caught(fn() => arena61_rematch(V61M_CLASS, $kA, 'aaaaaaaaaaaaaaaa', $t));
$check('odveta bez opt-inu soupeře se odmítne a nic nezapíše', $m === tr('Tenhle spolužák výzvy zatím nepřijímá.') && count($rows()) === 1);
arena60_optin_set(V61M_CLASS, $kB, true);
$new = arena61_rematch(V61M_CLASS, $kA, 'aaaaaaaaaaaaaaaa', $t);
$check('odveta po opt-inu: nová čekající výzva A → B s odkazem na původní souboj', count($rows()) === 2 && $new['from_key'] === $kA && $new['to_key'] === $kB && $new['status'] === 'pending' && ($rows()[1]['rematch_of'] ?? '') === 'aaaaaaaaaaaaaaaa');
$check('jedna odveta na souboj (druhý pokus i od soupeře se odmítne)', $caught(fn() => arena61_rematch(V61M_CLASS, $kA, 'aaaaaaaaaaaaaaaa', $t)) === tr('K tomuto souboji už odveta proběhla.') && $caught(fn() => arena61_rematch(V61M_CLASS, $kB, 'aaaaaaaaaaaaaaaa', $t)) === tr('K tomuto souboji už odveta proběhla.') && count($rows()) === 2);
$check('odveta jen účastníkem (spolužák mimo souboj: „nenalezeno“) a jen k dokončenému souboji', $caught(fn() => arena61_rematch(V61M_CLASS, $kC, 'aaaaaaaaaaaaaaaa', $t)) === tr('Výzva nebyla nalezena.')
    && $caught(fn() => arena61_rematch(V61M_CLASS, $kA, (string)$new['id'], $t)) === tr('Odvetu jde poslat jen po dokončeném souboji.') && $caught(fn() => arena61_rematch(V61M_CLASS, $kA, '../x', $t)) === tr('Výzva nebyla nalezena.'));
$addDuel('cccccccccccccccc', V61M_CLASS, $kA, $kB, 'done');
$check('odveta respektuje pauzu mezi výzvami stejnému adresátovi (24 h)', $caught(fn() => arena61_rematch(V61M_CLASS, $kA, 'cccccccccccccccc', $t)) === tr('Tomuhle spolužákovi jsi výzvu poslal/a nedávno, zkus to znovu za chvíli.') && count($rows()) === 3);
arena60_optin_set(V61M_CLASS, $kC, true);
$addDuel('dddddddddddddddd', V61M_CLASS, $kA, $kC, 'done');
foreach (['e1', 'e2'] as $n) $addDuel(str_repeat($n[1], 16), V61M_CLASS, $kA, $kB, 'pending');   // A má 3 čekající (1 odveta + 2)
$check('odveta respektuje limit čekajících výzev (3)', $caught(fn() => arena61_rematch(V61M_CLASS, $kA, 'dddddddddddddddd', $t)) === tr('Máš rozeslané už {n} čekající výzvy, počkej na odpověď.', ['n' => ARENA60_MAX_OPEN_SENT]));
arena60_optin_set(V61M_CLASS, $kC, false);
$check('po vypnutí opt-inu soupeře odveta nejde (opt-in platí kdykoli)', $caught(fn() => arena61_rematch(V61M_CLASS, $kA, 'dddddddddddddddd', $t)) === tr('Tenhle spolužák výzvy zatím nepřijímá.'));
$check('výhry/body: souboj ani odveta nezapíše body (peněženka beze změny)', pts53_balance(V61M_CLASS, $kA) === $walletBefore);
// přehled učitele = čtení
$addDuel('f1f1f1f1f1f1f1f1', V61M_OTHER, $keyOf(V61M_OTHER, V61M_NAMES_1A[0]), $keyOf(V61M_OTHER, V61M_NAMES_1A[1]), 'done');
$addDuel('9999999999999999', V61M_CLASS, $kB, $kC, 'pending', ['expires_at' => time() - 100]);
$hashArena = $fileHash('arena_v60_challenges.json.php');
$r3 = arena61_rows([V61M_CLASS], $t);
$r1 = arena61_rows([V61M_OTHER], $t);
$expired = array_values(array_filter($r3, static fn(array $r): bool => $r['id'] === '9999999999999999'))[0] ?? [];
$check('přehled učitele: jen povolené třídy (3.A nevidí souboj 1.A a naopak)', $r3 !== [] && count(array_filter($r3, static fn(array $r): bool => $r['class_id'] !== V61M_CLASS)) === 0 && count($r1) === 1 && $r1[0]['class_id'] === V61M_OTHER && arena61_rows([], $t) === []);
$check('přehled dopočítá prošlou výzvu v paměti a NIC nezapíše (úložiště beze změny, stav v souboru zůstal pending)', ($expired['effective'] ?? '') === 'expired' && $hashArena === $fileHash('arena_v60_challenges.json.php') && (arena60_find(V61M_CLASS, '9999999999999999')['status'] ?? '') === 'pending');
$stats = arena61_class_stats(V61M_CLASS, $t);
$check('statistiky třídy: počty podle stavu, zapnuté výzvy a velikost třídy', $stats['counts']['done'] === 3 && $stats['counts']['pending'] === 3 && $stats['counts']['expired'] === 1 && $stats['optin'] === 1 && $stats['roster'] >= 3 && $stats['total'] === 7 && array_key_exists('top_levels', $stats));
$_GET = ['duel_class' => V61M_CLASS, 'duel_status' => 'neexistuje'];
$check('filtr přehledu: třída mimo rozsah i neznámý stav se ignorují (rozsah se nerozšíří)', arena61_teacher_filters([V61M_OTHER]) === ['class' => '', 'status' => ''] && arena61_teacher_filters([V61M_CLASS, V61M_OTHER])['class'] === V61M_CLASS);
$_GET = [];
$html = audit_capture(static fn() => arena61_render_teacher_tab(V61M_CLASS, 'x'));
$check('učitelský přehled se vykreslí česky s plnými jmény a nic nezapíše', str_contains($html, 'Souboje v Aréně') && str_contains($html, 'Trojan') && str_contains($html, 'Jedličk') && $hashArena === $fileHash('arena_v60_challenges.json.php'));
$_SESSION['student_label'] = $nameC;
$none = audit_capture(static fn() => arena61_render_class_stats(V61M_CLASS, $kC));
arena60_optin_set(V61M_CLASS, $kA, true);
$withStats = audit_capture(static fn() => arena61_render_class_stats(V61M_CLASS, $kA));
$check('statistiky třídy pro žáka: bez opt-inu nic, s opt-inem souhrn bez jmen', $none === '' && str_contains($withStats, 'dokončených') && !str_contains($withStats, 'Trojan') && !str_contains($withStats, 'Jedličk'));

// ---------------------------------------------------------------- 5) obchod: sezóna, náhled, oblíbené
$hashArena = $fileHash('arena_v60_challenges.json.php');
$today = date('Y-m-d');
$mk = static fn(array $in): ?string => mkt60_save_item('', $in + ['type' => 'cosmetic', 'slot' => 'frame', 'price' => 10, 'classes' => [V61M_CLASS], 'active' => true, 'desc' => ''], [V61M_CLASS, V61M_OTHER], 'audit');
$idNow = (string)$mk(['title' => 'Sezónní rámeček', 'season_from' => date('Y-m-d', time() - 86400), 'season_to' => date('Y-m-d', time() + 86400)]);
$idOld = (string)$mk(['title' => 'Skončená sezóna', 'season_from' => '2020-01-01', 'season_to' => '2020-02-01']);
$idFuture = (string)$mk(['title' => 'Budoucí sezóna', 'season_from' => '2099-01-01']);
$idPlain = (string)$mk(['title' => 'Stálý titulek', 'slot' => 'title']);
$idContent = (string)$mk(['title' => 'Extra obsah', 'type' => 'content', 'url' => '?view=materialy', 'slot' => '']);
$idOther = (string)mkt60_save_item('', ['type' => 'cosmetic', 'slot' => 'frame', 'title' => 'Jen pro 1.A', 'price' => 5, 'classes' => [V61M_OTHER], 'active' => true], [V61M_OTHER], 'audit');
$offer = mkt60_catalog_for_class(V61M_CLASS);
$check('nabídka třídy: aktuální sezónní a stálé položky ano; skončená, budoucí a cizí třída ne', isset($offer[$idNow], $offer[$idPlain], $offer[$idContent]) && !isset($offer[$idOld], $offer[$idFuture], $offer[$idOther]));
$check('sezónní okno je včetně obou krajů a bez dat platí vždy', mkt61_item_in_season(['season_from' => '2026-10-14', 'season_to' => '2026-10-20'], '2026-10-14') && mkt61_item_in_season(['season_from' => '2026-10-14', 'season_to' => '2026-10-20'], '2026-10-20')
    && !mkt61_item_in_season(['season_from' => '2026-10-14'], '2026-10-13') && !mkt61_item_in_season(['season_to' => '2026-10-20'], '2026-10-21') && mkt61_item_in_season([]));
$check('neplatná sezóna (konec před začátkem, nesmyslné datum) i typ mimo bílou listinu se uložit nedají', mkt60_save_item('', ['type' => 'cosmetic', 'slot' => 'frame', 'title' => 'x', 'price' => 1, 'classes' => [V61M_CLASS], 'season_from' => '2026-12-01', 'season_to' => '2026-11-01'], [V61M_CLASS], 'a') === null
    && mkt60_save_item('', ['type' => 'cosmetic', 'slot' => 'frame', 'title' => 'x', 'price' => 1, 'classes' => [V61M_CLASS], 'season_from' => '31.2.2026'], [V61M_CLASS], 'a') === null
    && mkt60_save_item('', ['type' => 'xp', 'title' => 'x', 'price' => 1, 'classes' => [V61M_CLASS]], [V61M_CLASS], 'a') === null && mkt60_save_item('', ['type' => 'grade', 'title' => 'x', 'price' => 1, 'classes' => [V61M_CLASS]], [V61M_CLASS], 'a') === null);
$xpBefore = (int)$profileOf($lkA)['xp'];
foreach (range(1, 4) as $n) pts53_award(V61M_CLASS, $kA, 'audit' . $n, 10, 'Audit');
$bal = pts53_balance(V61M_CLASS, $kA);
$buyOld = mkt60_buy(V61M_CLASS, $kA, $idOld, 1, 'aabbccdd00112233');
$check('nákup položky mimo sezónu server odmítne (item_unavailable) a nestrhne body', !$buyOld['ok'] && $buyOld['error'] === 'item_unavailable' && pts53_balance(V61M_CLASS, $kA) === $bal);
$buyNow = mkt60_buy(V61M_CLASS, $kA, $idNow, 1, 'aabbccdd00112244');
$check('nákup sezónní položky v sezóně projde a strhne jen cenu; XP profilu se nezmění', $buyNow['ok'] && pts53_balance(V61M_CLASS, $kA) === $bal - 10 && (int)$profileOf($lkA)['xp'] === $xpBefore);
$check('náhled: jen kosmetika z aktuální nabídky třídy (whitelist id)', mkt61_preview_item(V61M_CLASS, $idNow)['id'] === $idNow && mkt61_preview_item(V61M_CLASS, $idPlain) !== null
    && mkt61_preview_item(V61M_CLASS, $idContent) === null && mkt61_preview_item(V61M_CLASS, $idOld) === null && mkt61_preview_item(V61M_CLASS, $idOther) === null
    && mkt61_preview_item(V61M_CLASS, '../etc') === null && mkt61_preview_item(V61M_CLASS, ['x']) === null && mkt61_preview_item(V61M_CLASS, null) === null);
$_SESSION['student_label'] = $nameC;
$_GET = ['nahled' => $idNow];
$before = $snapshot();
$preview = audit_capture(static fn() => mkt61_render_preview(V61M_CLASS, $kC));
$_GET = [];
$check('náhled kosmetiky vykreslí avatar s rámečkem a NIC nezapíše (úložiště beze změny)', str_contains($preview, 'Takhle by to vypadalo') && str_contains($preview, 'has-frame') && str_contains($preview, 'Sezónní rámeček') && $before === $snapshot());
$_GET = ['nahled' => '../x'];
$check('náhled s neplatným parametrem nevykreslí nic', audit_capture(static fn() => mkt61_render_preview(V61M_CLASS, $kC)) === '');
$_GET = [];
$ids = [];
foreach (range(1, MOT61_FAV_MAX + 1) as $n) $ids[] = (string)$mk(['title' => 'Oblíbená ' . $n, 'type' => 'other', 'slot' => '']);
$results = [];
foreach ($ids as $id) $results[] = mot61_fav_toggle(V61M_CLASS, $kC, $id);
$check('oblíbené: max ' . MOT61_FAV_MAX . ' (poslední = full), odebrání funguje i po naplnění', count(array_keys($results, 'added', true)) === MOT61_FAV_MAX && end($results) === 'full' && count(mot61_fav_list(V61M_CLASS, $kC)) === MOT61_FAV_MAX
    && mot61_fav_toggle(V61M_CLASS, $kC, $ids[0]) === 'removed' && count(mot61_fav_list(V61M_CLASS, $kC)) === MOT61_FAV_MAX - 1);
$check('oblíbené: neznámé, skončené, cizí třídy i neplatné id se nepřidá; seznam je jen žákův', mot61_fav_toggle(V61M_CLASS, $kB, $idOld) === 'unknown' && mot61_fav_toggle(V61M_CLASS, $kB, $idOther) === 'unknown' && mot61_fav_toggle(V61M_CLASS, $kB, '../x') === 'unknown'
    && mot61_fav_toggle(V61M_CLASS, $kB, 'neexistuje') === 'unknown' && mot61_fav_toggle(V61M_CLASS, '', $idNow) === 'unknown' && mot61_fav_list(V61M_CLASS, $kB) === []);
$check('obchod nezměnil XP ani arénu (profil XP stejné, výzvy beze změny)', (int)$profileOf($lkA)['xp'] === $xpBefore && $hashArena === $fileHash('arena_v60_challenges.json.php'));
v61m_http($ROOT, $tmp, $check, $keyOf, $kA, $kB, $nameA, $nameB, $ids, $idNow, $idPlain, $idOld, $idContent, $snapshot, $fileHash);

echo "BEHAVIORAL {$state->behavioral}/{$state->checks}\n";
if ($state->failed > 0) { echo "V61_MOTIVATION_AUDIT_FAIL checks={$state->checks} failed={$state->failed}\n"; exit(1); }
echo "V61_MOTIVATION_AUDIT_OK checks={$state->checks} failed=0\n";

/** HTTP část: žák (CSRF, odveta, náhled, oblíbené) a učitelé (rozsah, čtení, asistent). */
function v61m_http(string $ROOT, string $tmp, Closure $check, Closure $keyOf, string $kA, string $kB, string $nameA, string $nameB, array $favIds, string $idNow, string $idPlain, string $idOld, string $idContent, Closure $snapshot, Closure $fileHash): void
{
    $arenaRows = static fn(): array => arena60_data()['challenges'];
    // Pro HTTP: souboj A–B dokončený, B přijímá výzvy, bez předchozí výzvy A → B (čistá pauza), A bez čekajících.
    arena60_update(static function (array $d) use ($kA, $kB): array {
        $d['challenges'] = [['id' => 'b1b1b1b1b1b1b1b1', 'class_id' => V61M_CLASS, 'from_key' => $kA, 'to_key' => $kB, 'level_id' => (string)(lab57_pack_levels((string)array_key_first(lab57_packs()))[0]['id'] ?? 'lvl'), 'status' => 'done',
            'created_at' => date(DATE_ATOM, time() - 400000), 'expires_at' => time() + 86400, 'accepted_at' => date(DATE_ATOM, time() - 399000), 'winner_key' => $kA, 'finished_at' => null]];
        $d['optin'] = [V61M_CLASS => [$kB => true]];
        return $d;
    });
    $acc = v59sf_create_accounts();
    audit_prewarm_accounts($GLOBALS['modules']);
    $h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
    try {
        $login = audit_login_student($h, V61M_CLASS, $nameA);
        $csrf = (string)$login['csrf'];
        $dash = (string)$login['response']['body'];
        $check('HTTP: přehled žáka má kartu „Dnešní cíle“ (≤ 3 denní + 2 týdenní ukazatele) bez PHP chyby', (int)$login['response']['status'] === 200 && str_contains($dash, 'Dnešní cíle') && preg_match_all('/<li class="mot61-goal(?: is-done)?">/', $dash) >= 4 && preg_match_all('/<li class="mot61-goal(?: is-done)?">/', $dash) <= 5 && audit_response_clean($login['response']));
        $profile = $h->request('GET', '/?view=profile&tab=prehled');
        $arena = $h->request('GET', '/?view=profile&tab=arena');
        $check('HTTP: profil Přehled ukazuje cíle a záložka Arena tlačítko Odveta u dokončeného souboje', str_contains((string)$profile['body'], 'Dnešní cíle') && str_contains((string)$arena['body'], 'value="arena61_rematch"') && audit_response_clean($arena));
        $odznaky = $h->request('GET', '/?view=profile&tab=odznaky');
        $locked = $h->request('GET', '/?view=profile&tab=odznaky&zamcene=1');
        $check('HTTP: záložka Odznaky ukazuje sezónní sbírku s postupem; zamčené karty až na ?zamcene=1 (ne v základní stránce)', str_contains((string)$odznaky['body'], 'Odznaky pololetí') && str_contains((string)$odznaky['body'], 'role="progressbar"') && substr_count((string)$odznaky['body'], 'season_') === 0 && substr_count((string)$locked['body'], 'data-b60-state="locked"') > substr_count((string)$odznaky['body'], 'data-b60-state="locked"') && audit_response_clean($odznaky) && audit_response_clean($locked));
        $before = $arenaRows();
        $noToken = $h->request('POST', '/', ['action' => 'arena61_rematch', 'id' => 'b1b1b1b1b1b1b1b1'], ['follow_redirects' => false]);
        $badToken = $h->request('POST', '/', ['action' => 'arena61_rematch', 'id' => 'b1b1b1b1b1b1b1b1', 'csrf' => 'neplatny'], ['follow_redirects' => false]);
        php_json_cache_forget(STORAGE_DIR . '/arena_v60_challenges.json.php');
        $check('CSRF: odveta bez tokenu i s cizím tokenem = 419 a nic se nezapíše', (int)$noToken['status'] === 419 && (int)$badToken['status'] === 419 && $arenaRows() === $before);
        $ok = $h->request('POST', '/', ['action' => 'arena61_rematch', 'id' => 'b1b1b1b1b1b1b1b1', 'csrf' => $csrf], ['follow_redirects' => false]);
        $after = $arenaRows();
        $check('HTTP: odveta s CSRF vytvoří jednu novou výzvu A → B s rematch_of; identita ze session', in_array((int)$ok['status'], [302, 303], true) && count($after) === 2 && $after[1]['from_key'] === $kA && $after[1]['to_key'] === $kB && ($after[1]['rematch_of'] ?? '') === 'b1b1b1b1b1b1b1b1');
        $h->request('POST', '/', ['action' => 'arena61_rematch', 'id' => 'b1b1b1b1b1b1b1b1', 'csrf' => $csrf], ['follow_redirects' => false]);
        $check('HTTP: opakovaná odveta se stejným id nic nepřidá', count($arenaRows()) === 2);
        $h->request('POST', '/', ['action' => 'arena61_rematch', 'id' => 'b1b1b1b1b1b1b1b1', 'csrf' => $csrf, 'from_key' => $kB], ['follow_redirects' => false]);
        $check('HTTP: identita se z parametru nebere (podvržené from_key nic nezmění)', count($arenaRows()) === 2 && $arenaRows()[1]['from_key'] === $kA);
        // obchod
        $warm = $h->request('GET', '/?view=obchod');
        $snapBefore = $snapshot();
        $prev = $h->request('GET', '/?view=obchod&nahled=' . rawurlencode($idNow));
        $bad = $h->request('GET', '/?view=obchod&nahled=' . rawurlencode($idContent));
        $bad2 = $h->request('GET', '/?view=obchod&nahled=..%2Fx');
        $snapAfter = $snapshot();
        $check('HTTP: náhled kosmetiky se zobrazí, nic nezapíše (úložiště beze změny); obsah a neplatné id náhled nedají', (int)$prev['status'] === 200 && str_contains((string)$prev['body'], 'Takhle by to vypadalo') && $snapBefore === $snapAfter
            && !str_contains((string)$bad['body'], 'Takhle by to vypadalo') && !str_contains((string)$bad2['body'], 'Takhle by to vypadalo') && audit_response_clean($prev));
        $check('HTTP: obchod ukazuje sezónní nabídku a skrývá skončenou i budoucí položku', str_contains((string)$warm['body'], 'Sezónní nabídka') && str_contains((string)$warm['body'], 'Sezónní rámeček') && !str_contains((string)$warm['body'], 'Skončená sezóna') && !str_contains((string)$warm['body'], 'Budoucí sezóna'));
        $favBefore = mot61_fav_list(V61M_CLASS, $kA);
        $nf = $h->request('POST', '/', ['action' => 'mot61_fav_toggle', 'item_id' => $idPlain], ['follow_redirects' => false]);
        $check('CSRF: oblíbené bez tokenu = 419 a seznam se nezměnil', (int)$nf['status'] === 419 && mot61_fav_list(V61M_CLASS, $kA) === $favBefore);
        $h->request('POST', '/', ['action' => 'mot61_fav_toggle', 'item_id' => $idPlain, 'csrf' => $csrf], ['follow_redirects' => false]);
        $h->request('POST', '/', ['action' => 'mot61_fav_toggle', 'item_id' => $idOld, 'csrf' => $csrf], ['follow_redirects' => false]);
        $favOnly = $h->request('GET', '/?view=obchod&oblibene=1');
        $check('HTTP: oblíbená položka se uloží (jen platná), filtr ?oblibene=1 ukáže jen oblíbené', mot61_fav_list(V61M_CLASS, $kA) === [$idPlain] && str_contains((string)$favOnly['body'], 'Stálý titulek') && substr_count((string)$favOnly['body'], '<article class="mkt60-card">') === 1);
    } finally {
        $h->stop();
    }
    v61m_http_teachers($ROOT, $tmp, $check, $acc, $snapshot);
}

/** Učitelé: rozsah tříd v přehledu soubojů, jen čtení, žádná zapisující akce pro učitele ani asistenta. */
function v61m_http_teachers(string $ROOT, string $tmp, Closure $check, array $acc, Closure $snapshot): void
{
    $sharedKey = (string)getenv('EDUCANET_TEACHER_EXPORT_KEY');
    $h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => $sharedKey, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
    $jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
    $jars = [];
    $csrf = [];
    $req = static function (string $who, string $method, string $path, array $fields = []) use ($h, $jarSwap, &$jars): array {
        $jarSwap($jars[$who] ?? []);
        try { return $h->request($method, $path, $fields, ['follow_redirects' => false]); } finally { $jars[$who] = $jarSwap(null); }
    };
    try {
        foreach (['admin', 'a', 'b', 's', 'e'] as $who) {
            $jarSwap($jars[$who] ?? []);
            audit_login_teacher_account($h, $acc['login'][$who], $acc['pw'][$who]);
            $jars[$who] = $jarSwap(null);
            $csrf[$who] = (string)$h->csrfToken((string)$req($who, 'GET', '/teacher.php?tab=ucet')['body']);
        }
        $page = static fn(string $who, string $query): array => $req($who, 'GET', '/teacher.php?tab=souboje' . $query);
        $admin = $page('admin', '&class=class_3a');
        $teacherB = $page('b', '&class=class_3a');
        $teacherA = $page('a', '&class=class_1a');
        $teacherAx = $page('a', '&class=class_1a&duel_class=class_3a');
        $assistant = $page('s', '&class=class_3a');
        $nobody = $page('e', '');
        $has = static fn(array $r, string $needle): bool => str_contains((string)$r['body'], $needle);
        $check('HTTP učitel: admin vidí souboje 3.A i 1.A, 200 bez PHP chyby', (int)$admin['status'] === 200 && $has($admin, 'Souboje v Aréně') && $has($admin, 'Trojan') && $has($admin, 'Jedličk') && audit_response_clean($admin));
        $check('HTTP učitel: učitel 3.A+4.A vidí jen svoje (Trojan ano, Jedlick ne)', (int)$teacherB['status'] === 200 && $has($teacherB, 'Trojan') && !$has($teacherB, 'Jedličk'));
        $check('HTTP učitel: učitel 1.A+2.A nevidí souboje 3.A (ani přes filtr duel_class=class_3a)', (int)$teacherA['status'] === 200 && $has($teacherA, 'Jedličk') && !$has($teacherA, 'Trojan') && (int)$teacherAx['status'] === 200 && !$has($teacherAx, 'Trojan'));
        $check('HTTP učitel: asistent (3.A) souboje 3.A vidí, jiné třídy ne; učitel bez tříd nevidí žádné jméno', (int)$assistant['status'] === 200 && $has($assistant, 'Trojan') && !$has($assistant, 'Jedličk') && !$has($nobody, 'Trojan') && !$has($nobody, 'Jedličk'));
        $before = $snapshot();
        $statuses = [];
        foreach (['admin', 'a', 'b', 's'] as $who) {
            foreach (['arena61_rematch', 'mot61_fav_toggle'] as $action) {
                $statuses[$who . ':' . $action] = (int)$req($who, 'POST', '/teacher.php', ['action' => $action, 'csrf' => $csrf[$who], 'id' => 'b1b1b1b1b1b1b1b1', 'item_id' => 'x'])['status'];
            }
        }
        $check('HTTP učitel: žádná zapisující akce souboje/obchodu pro učitele, admina ani asistenta (deny-by-default 403) a úložiště beze změny', count(array_filter($statuses, static fn(int $s): bool => $s === 403)) === count($statuses) && $before === $snapshot());
        $before2 = $snapshot();
        foreach (['admin', 'a', 'b', 's', 'e'] as $who) { $page($who, '&duel_status=done'); }
        $check('HTTP učitel: opakované čtení přehledu (i s filtrem stavu) nic nezapíše', $before2 === $snapshot());
    } finally {
        $h->stop();
    }
}
