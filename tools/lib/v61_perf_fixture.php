<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · deterministická fiktivní třída pro měření výkonu (vymyšlená jména, žádná skutečná data).
 *
 *   v61_perf_roster(30)            – seznam jmen (pevné pořadí, bez náhody),
 *   v61_perf_seed($modules, $dir)  – do DOČASNÉHO úložiště $dir zapíše soupis třídy, účty, XP, body, výsledky testů
 *                                    a vyřešené úrovně labu; vrátí údaje pro přihlášení prvního žáka.
 * Volat až po `require bootstrap.php` s EDUCANET_STORAGE_DIR na dočasném adresáři (edu_audit_temp_storage()).
 */

const V61_PERF_CLASS = 'class_3a';

/** @return list<string> */
function v61_perf_roster(int $count = 30): array
{
    $first = ['Adam', 'Barbora', 'Cyril', 'Dagmar', 'Emil', 'Františka', 'Gustav', 'Hana', 'Ivo', 'Jarmila', 'Karel', 'Lenka', 'Matyáš', 'Nina', 'Oskar'];
    $last = ['Zkušební', 'Testovací', 'Ukázkový', 'Modelový'];
    $out = [];
    for ($i = 0; $i < $count; $i++) {
        $out[] = $first[$i % count($first)] . ' ' . $last[intdiv($i, count($first)) % count($last)];
    }
    return $out;
}

function v61_perf_write_roster(string $dir, array $labels): void
{
    @mkdir($dir . '/intake', 0700, true);
    $rows = [];
    foreach ($labels as $i => $label) {
        [$firstName, $lastName] = explode(' ', $label, 2);
        $rows[] = ['id' => 'fx61p_' . $i, 'class_id' => V61_PERF_CLASS, 'submitted_at' => date(DATE_ATOM), 'student' => ['first_name' => $firstName, 'last_name' => $lastName, 'preferred_name' => '', 'seat_id' => '', 'seat_label' => ''], 'answers' => [], 'assessment' => [], 'source' => 'fixture', 'imported_at' => date(DATE_ATOM)];
    }
    file_put_contents($dir . '/intake/responses.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode($rows, JSON_UNESCAPED_UNICODE));
}

/** Historie jednoho žáka: XP + události, odznaky, body, výsledky testů, vyřešené úrovně labu. */
function v61_perf_seed_student(string $classId, string $label, int $n, array $badgeIds, array $levelIds): void
{
    $key = social_student_key($classId, $label);
    $events = [];
    for ($d = 0; $d < 20; $d++) $events["fx:$d"] = ['xp' => 15 + (($d * 37 + $n * 11) % 80), 'at' => date(DATE_ATOM, strtotime("-$d days"))];
    $badges = [];
    foreach (array_slice($badgeIds, 0, 4 + $n % 12) as $b) $badges[$b] = ['at' => date(DATE_ATOM, strtotime('-3 days'))];
    storage_update(STORAGE_DIR . '/learning_profiles.json.php', static function (array $all) use ($key, $events, $badges, $n): array {
        $all[$key] = ['xp' => 300 + $n * 70, 'events' => $events, 'kb' => array_fill_keys(array_map(static fn(int $i): string => "kb$i", range(1, 3 + $n % 7)), true), 'studio' => [], 'journey' => [], 'badges' => $badges, 'achievements' => [], 'version' => 3, 'updated_at' => date(DATE_ATOM)];
        return $all;
    });
    for ($i = 1; $i <= 6; $i++) pts53_award($classId, $key, "fx$i", 3 + ($i + $n) % 6, 'Fiktivní bod ' . $i);
    $now = time();
    for ($t = 0; $t < 4; $t++) {
        storage_append('practice_results', ['id' => 'fx_' . $n . '_' . $t, 'class_id' => $classId, 'student_label' => $label, 'student_email' => '', 'auth_key' => '', 'google_sub' => '', 'started_at' => date(DATE_ATOM, $now - 86400 * ($t + 1) - 600), 'finished_at' => date(DATE_ATOM, $now - 86400 * ($t + 1)), 'score' => 5 + ($n + $t) % 5, 'max_score' => 10, 'answers' => []]);
    }
    $solved = [];
    foreach (array_slice($levelIds, 0, 3 + $n % 9) as $lv) $solved[$lv] = ['at' => date(DATE_ATOM)];
    lab57_store_update(lab57_state_path($classId, $key), static function (array $d) use ($solved): array { $d['solved']['practice'] = $solved; return $d; });
}

/**
 * @return array{class:string,label:string,email:string,id:string,labels:list<string>}
 */
function v61_perf_seed(array $modules, string $dir, int $count = 30): array
{
    foreach (['accounts_v53.php', 'intake_v51.php', 'points_v53.php', 'linux_v57_lab.php'] as $lib) require_once dirname(__DIR__, 2) . '/' . $lib;
    $labels = v61_perf_roster($count);
    v61_perf_write_roster($dir, $labels);
    acc53_provision_all($modules);
    storage_update(local_accounts_path(), static function (array $accounts): array {
        foreach ($accounts as $email => $a) { if (is_array($a)) $accounts[$email]['must_change_password'] = false; }
        return $accounts;
    });
    $badgeIds = function_exists('learning_badge_definitions') ? array_keys(learning_badge_definitions()) : [];
    $levelIds = [];
    foreach (array_keys(lab57_packs()) as $pid) foreach (lab57_pack_levels((string)$pid) as $lv) $levelIds[] = (string)$lv['id'];
    foreach ($labels as $n => $label) v61_perf_seed_student(V61_PERF_CLASS, $label, $n, $badgeIds, $levelIds);
    foreach (['runtime_content.php', 'tutorial_v52.php', 'session_v53.php'] as $lib) require_once dirname(__DIR__, 2) . '/' . $lib;
    require_once __DIR__ . '/audit_storage.php';
    $schoolYear = require dirname(__DIR__, 2) . '/school_year.php';
    edu_audit_open_sessions([V61_PERF_CLASS => $modules[V61_PERF_CLASS]], $schoolYear, date('Y-m-d'), []);
    $first = $labels[0];
    $acc = null;
    foreach (storage_read(local_accounts_path()) as $a) {
        if (is_array($a) && (string)($a['student_label'] ?? '') === $first) { $acc = $a; break; }
    }
    return ['class' => V61_PERF_CLASS, 'label' => $first, 'email' => (string)($acc['email'] ?? ''), 'id' => (string)($acc['id'] ?? 'fx'), 'labels' => $labels];
}
