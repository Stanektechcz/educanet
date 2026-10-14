<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · retence dat v75 po odchodu žáka (volá tools/v58_retention.php, náhled bez --apply nic nemění).
 *
 * 30 dní (COMP62_RETENTION_GRACE_DAYS) po stavu left/archived v registru identit v58 se maže:
 *   - připnuté práce portfolia: storage/portfolio_v75/<ref>.json.php (ref = student_id nebo pseudonym úloh tb75),
 *   - výsledky úloh s variantami: záznam žáka v storage/taskbank_v75.json.php (classes[třída][ref]),
 *   - volby úrovní v hodině: záznam žáka v storage/levels_v75_picks.json.php (classes[třída][lekce][hash žáka]),
 *   - v80: podpora učení (opakování chyb, počty nápověd, cvičení, cíle): záznam žáka v storage/learning_v80_<třída>.json.php (klíč = stejné ref jako úlohy).
 *   - v80.1: zpětná vazba a přidělení procvičování od učitele: záznamy žáka v storage/support_v80_1_<třída>.json.php (stejné ref).
 * Kandidáti = stejné pravidlo jako v65 (proj65_retention_candidates: stav, datum archivace, aliasy třída:student:hash).
 * Soubor se maže pod výlučným zámkem (ev62_delete_locked), sdílené soubory přes storage_update. Vrací jen počty – žádná jména.
 */

/** Reference žáka v datech v75: student_id a pseudonymy tb75 pro každý alias třída:student:hash. @return list<string> */
function r76_refs(string $studentId, array $aliases): array
{
    $refs = [$studentId];
    foreach ($aliases as $alias) {
        if (preg_match('/^(class_[a-z0-9]+):student:[0-9a-f]{24}$/', (string)$alias, $m) !== 1) continue;
        $refs[] = 'h' . substr(hash('sha256', 'tb75|' . $m[1] . '|' . $alias), 0, 24);
    }
    return array_values(array_unique($refs));
}

/** Hashe voleb úrovní (lv75_student_hash) pro aliasy žáka. @return list<string> */
function r76_level_hashes(array $aliases): array
{
    return array_values(array_unique(array_map(static fn(string $a): string => substr(hash('sha256', 'lv75|' . $a), 0, 16), array_map('strval', $aliases))));
}

/**
 * Pročistí data v75 odešlých žáků. Dry-run jen spočítá. v80.1: i zpětná vazba a přidělení procvičování od učitele (support).
 * @return array{students:int,portfolio:int,tasks:int,levels:int,learning:int,support:int,dry_run:bool}
 */
function r76_retention_purge(bool $dryRun, ?int $now = null): array
{
    $cand = function_exists('proj65_retention_candidates') ? proj65_retention_candidates($now) : [];
    $res = ['students' => count($cand), 'portfolio' => 0, 'tasks' => 0, 'levels' => 0, 'learning' => 0, 'support' => 0, 'dry_run' => $dryRun];
    if ($cand === []) return $res;
    $refs = [];
    $hashes = [];
    foreach ($cand as $id => $aliases) {
        $refs = array_merge($refs, r76_refs((string)$id, (array)$aliases));
        $hashes = array_merge($hashes, r76_level_hashes((array)$aliases));
    }
    foreach (array_unique($refs) as $ref) {
        $file = STORAGE_DIR . '/portfolio_v75/' . $ref . '.json.php';
        if (!is_file($file)) continue;
        $res['portfolio']++;
        if (!$dryRun) ev62_delete_locked($file);
    }
    $refSet = array_flip($refs);
    $scrubTasks = static function (array $d) use ($refSet, &$res): array {
        foreach ((array)($d['classes'] ?? []) as $classId => $students) {
            foreach (array_keys((array)$students) as $ref) {
                if (!isset($refSet[(string)$ref])) continue;
                $res['tasks']++;
                unset($d['classes'][$classId][$ref]);
            }
        }
        return $d;
    };
    $taskPath = STORAGE_DIR . '/taskbank_v75.json.php';
    if (is_file($taskPath)) { if ($dryRun) $scrubTasks(storage_read($taskPath, false)); else storage_update($taskPath, $scrubTasks); }
    $hashSet = array_flip($hashes);
    $scrubLevels = static function (array $d) use ($hashSet, &$res): array {
        foreach ((array)($d['classes'] ?? []) as $classId => $lessons) {
            foreach ((array)$lessons as $lesson => $picks) {
                foreach (array_keys((array)$picks) as $h) {
                    if (!isset($hashSet[(string)$h])) continue;
                    $res['levels']++;
                    unset($d['classes'][$classId][$lesson][$h]);
                }
            }
        }
        return $d;
    };
    $levelPath = STORAGE_DIR . '/levels_v75_picks.json.php';
    if (is_file($levelPath)) { if ($dryRun) $scrubLevels(storage_read($levelPath, false)); else storage_update($levelPath, $scrubLevels); }
    require_once __DIR__ . '/learning_v80.php';   // v80: podpora učení (stejné ref jako úlohy v75)
    $res['learning'] = lr80_retention_purge(array_values(array_unique($refs)), $dryRun);
    require_once __DIR__ . '/support_v80_1.php';   // v80.1: zpětná vazba a přidělení od učitele (stejné ref jako úlohy v75)
    $res['support'] = ts801_retention_purge(array_values(array_unique($refs)), $dryRun);
    return $res;
}
