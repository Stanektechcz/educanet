<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v67 · výkon přehledu žáka.
 *
 * 1) Dovednosti (skill_trees.php) se při každém načtení přehledu přepočítávaly od nuly. Teď se přepočet dělá
 *    tam, kde se výsledek mění (odevzdání testu, dokončení lekce, publikace projektu – shutdown háček v index.php)
 *    a v nočním přepočtu; přehled jen porovná otisk vstupů (úroveň, dokončená témata, známky projektů) s uloženým
 *    a při shodě načte hotové řádky z úložiště (skill67_prime). Při neshodě (první návštěva, změna katalogu) se
 *    přepočítá jako dřív, takže data nikdy nezůstanou zastaralá.
 * 2) perf67_memo(): paměť v rámci jednoho požadavku pro drahé výpočty, které se volají víckrát (platí do dalšího zápisu).
 *
 * Otisk se ukládá do storage/skill_sync_v67.json.php (mapa klíč žáka → otisk) výhradně přes storage_update;
 * v režimu EDUCANET_STORAGE_READONLY se nic nezapisuje.
 */

/** Akce, po kterých se má přepočítat postup v dovednostech (přesná jména a předpony). */
const PERF67_SYNC_ACTIONS = ['answer', 'continue_test', 'abort_test', 'practice_answer', 'continue_practice', 'abort_practice', 'submit_extra', 'submit_graphics', 'tut52_score'];
const PERF67_SYNC_PREFIXES = ['v56_', 'v55_', 'proj65_s_', 'p63_', 'ml_', 'project_', 'v50_', 'v504_'];

function skill67_stamp_path(): string
{
    return STORAGE_DIR . '/skill_sync_v67.json.php';
}

/** Je akce taková, že může změnit výsledky učení? */
function perf67_action_changes_results(string $action): bool
{
    if (in_array($action, PERF67_SYNC_ACTIONS, true)) return true;
    foreach (PERF67_SYNC_PREFIXES as $prefix) {
        if (str_starts_with($action, $prefix)) return true;
    }
    return false;
}

/** Otisk vstupů přepočtu dovedností žáka; čte jen to, co přehled stejně načítá (profil je v paměti požadavku). */
function skill67_stamp(string $classId): string
{
    $profile = learning_profile($classId);
    $kb = [];
    foreach ((array)($profile['kb'] ?? []) as $topic => $row) {
        if (is_array($row) && !empty($row['complete'])) $kb[] = (string)$topic . (!empty($row['check']) ? '+' : '');
    }
    sort($kb);
    $level = (int)(learning_level((int)($profile['xp'] ?? 0))['level'] ?? 1);
    $catalog = (string)(@filemtime(__DIR__ . '/skill_catalog.php') ?: 0) . ':' . (string)(@filemtime(__DIR__ . '/skill_trees.php') ?: 0);
    return sha1($classId . '|' . $level . '|' . implode(',', $kb) . '|' . (string)storage_signature(STORAGE_DIR . '/project_grades.json.php') . '|' . $catalog);
}

/** Zapíše otisk pro žáka (nic neudělá v režimu jen pro čtení nebo při shodě). */
function skill67_stamp_save(string $studentKey, string $stamp): void
{
    if ($studentKey === '' || storage_readonly()) return;
    storage_update(skill67_stamp_path(), static function (array $d) use ($studentKey, $stamp): array {
        if (($d['stamps'][$studentKey] ?? '') === $stamp) return $d;
        $d['v'] = 1;
        $d['stamps'][$studentKey] = $stamp;
        return $d;
    });
}

/**
 * Naplní paměť požadavku dovedností z uložených řádků, pokud otisk sedí. Vrací true = hotovo bez přepočtu.
 * Řádky mají stejný tvar jako výstup skill_recalculate_all()/skill_recalculate_branches(), takže navazující volání
 * skill_progress_map(), skill_branch_progress_map() a skill_next_recommendation() už nepřepočítávají.
 */
function skill67_prime(string $classId): bool
{
    $studentKey = skill_current_student_key($classId);
    if ($studentKey === '') return false;
    $stamp = skill67_stamp($classId);
    if ((string)(storage_read(skill67_stamp_path(), false)['stamps'][$studentKey] ?? '') !== $stamp) return false;
    $progress = [];
    $rows = skill_progress_rows();
    foreach (skill_relevant_skills($classId) as $skill) {
        $row = $rows[$studentKey . '|' . (string)$skill['slug']] ?? null;
        if (!is_array($row)) return false;
        $progress[(string)$skill['slug']] = $row;
    }
    $branches = [];
    $branchRows = skill_branch_rows();
    foreach ((array)(skill_curriculum($classId)['branches'] ?? []) as $branch => $weight) {
        $row = $branchRows[$studentKey . '|' . (string)$branch] ?? null;
        if (!is_array($row)) return false;
        $branches[(string)$branch] = $row;
    }
    if ($progress === [] || $branches === []) return false;
    $GLOBALS['skill_runtime_progress'][$classId . '|' . $studentKey] = $progress;
    $GLOBALS['skill_runtime_branches'][$classId . '|' . $studentKey] = $branches;
    return true;
}

/** Přepočet dovedností a uložení otisku; volá ho akce po změně výsledku, přehled při neshodě a noční přepočet. */
function skill67_sync_now(string $classId): void
{
    skill_sync_existing_learning($classId);
    skill67_stamp_save(skill_current_student_key($classId), skill67_stamp($classId));
}

/** Přehled: nejdřív levná cesta (otisk sedí), jinak přepočet jako dřív. */
function skill67_dashboard_ready(string $classId): void
{
    if (!skill67_prime($classId)) skill67_sync_now($classId);
}

/** Shutdown háček: po akci, která mění výsledek, přepočítá dovednosti (po odeslání odpovědi, pokud to jde). */
function perf67_register_post_sync(array $modules, string $action): void
{
    if (!perf67_action_changes_results($action)) return;
    register_shutdown_function(static function () use ($modules): void {
        try {
            $classId = current_class_id($modules);
            if (!is_string($classId) || $classId === '' || !function_exists('skill_sync_existing_learning')) return;
            if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
            skill67_sync_now($classId);
            // Odznaky a úspěchy se po akci obnoví tady (přehled žáka je při GET už nepočítá).
            $simulations = is_array($GLOBALS['simulations'][$classId] ?? null) ? $GLOBALS['simulations'][$classId] : [];
            if (isset($modules[$classId]) && function_exists('learning_refresh_badges')) learning_refresh_badges($classId, $modules[$classId], $simulations);
        } catch (Throwable $e) {
            error_log('EDUCANET v67 přepočet dovedností: ' . get_class($e));
        }
    });
}

/**
 * Paměť v rámci požadavku: $compute se zavolá jednou na klíč, dokud se nic nezapsalo (storage_epoch() beze změny).
 * @template T
 * @param callable():T $compute
 * @return T
 */
function perf67_memo(string $key, callable $compute)
{
    $store = &$GLOBALS['educanet_perf67_memo'];
    if (!is_array($store)) $store = [];
    $epoch = storage_epoch();
    $hit = $store[$key] ?? null;
    if (is_array($hit) && $hit[0] === $epoch) return $hit[1];
    $value = $compute();
    $store[$key] = [$epoch, $value];
    return $value;
}
