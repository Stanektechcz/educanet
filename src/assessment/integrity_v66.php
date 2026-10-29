<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v66 · integrita testů: jen štítek „k ověření“ pro učitele, nikdy trest.
 *
 * Dva signály nad prvními pokusy žáků v jednom testu:
 *   speed  celková doba kratší než I66_FAST_SECONDS_PER_ITEM sekund na otázku (jen když se doba zná),
 *   match  Jaccardova shoda množin špatných odpovědí (položka + zvolená špatná možnost) dvou žáků ≥ I66_JACCARD_MIN
 *          a oba mají aspoň I66_MIN_WRONG špatných odpovědí (jinak by shodu způsobila náhoda).
 * Funkce nic neměnící: skóre, pokus ani návrh hodnocení se kvůli signálu nikdy neupraví. Žák ho nevidí.
 * Úložiště storage/assessment_v66/integrity_flags.json.php drží jen zkrácený hash student_id (ne jména); partner shody
 * se neukládá. Retence: záznamy se mažou po skončení školního roku (i66_retention_purge).
 */

const I66_FAST_SECONDS_PER_ITEM = 5;
const I66_JACCARD_MIN = 0.8;
const I66_MIN_WRONG = 4;
const I66_MAX_FLAGS_PER_CLASS = 300;

function i66_path(): string
{
    return STORAGE_DIR . '/assessment_v66/integrity_flags.json.php';
}

/** Zkrácený hash student_id (neprůhledný identifikátor v záznamech). */
function i66_hash(string $studentId): string
{
    return substr(hash('sha256', 'i66|' . $studentId), 0, 16);
}

/** Pokus byl odevzdán podezřele rychle (doba musí být známá a pokus musí mít položky). */
function i66_is_fast(array $attempt): bool
{
    $dur = $attempt['dur'] ?? null;
    $n = count((array)($attempt['items'] ?? []));
    return is_int($dur) && $n > 0 && $dur >= 0 && $dur < I66_FAST_SECONDS_PER_ITEM * $n;
}

/** Množina špatných odpovědí pokusu: "<položka>|<zvolená možnost>" (jen odpovědi se známou volbou). @return list<string> */
function i66_wrong_set(array $attempt): array
{
    $out = [];
    foreach ((array)($attempt['items'] ?? []) as $i) {
        if (is_array($i) && empty($i['ok']) && is_string($i['sel'] ?? null) && $i['sel'] !== '') $out[(string)$i['item'] . '|' . $i['sel']] = true;
    }
    return array_keys($out);
}

/** Jaccardova podobnost dvou množin (0–1); dvě prázdné množiny = 0. @param list<string> $a @param list<string> $b */
function i66_jaccard(array $a, array $b): float
{
    $union = count(array_unique(array_merge($a, $b)));
    return $union === 0 ? 0.0 : count(array_intersect($a, $b)) / $union;
}

/**
 * Štítky „k ověření“ z pokusů jedné třídy. @param list<array<string,mixed>> $attempts normalizované pokusy (a66_attempts)
 * @return list<array{kind:string,test:string,sid_hash:string,at:int}>
 */
function i66_scan(array $attempts): array
{
    $flags = [];
    $byTest = [];
    $when = static fn(array $a): int => (int)$a['at'] > 0 ? (int)$a['at'] : time(); // bez času pokusu platí čas skenu (jinak by záznam nešel nikdy odvodit k retenci)
    foreach (function_exists('a66_first_attempts') ? a66_first_attempts($attempts) : $attempts as $a) {
        $sid = (string)$a['sid'];
        $byTest[(string)$a['test']][] = $a;
        if (i66_is_fast($a)) $flags['speed|' . $a['test'] . '|' . $sid] = ['kind' => 'speed', 'test' => (string)$a['test'], 'sid_hash' => i66_hash($sid), 'at' => $when($a)];
    }
    foreach ($byTest as $test => $rows) {
        $sets = [];
        foreach ($rows as $i => $a) $sets[$i] = i66_wrong_set($a);
        $n = count($rows);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                if (count($sets[$i]) < I66_MIN_WRONG || count($sets[$j]) < I66_MIN_WRONG || i66_jaccard($sets[$i], $sets[$j]) < I66_JACCARD_MIN) continue;
                foreach ([$i, $j] as $k) $flags['match|' . $test . '|' . $rows[$k]['sid']] = ['kind' => 'match', 'test' => (string)$test, 'sid_hash' => i66_hash((string)$rows[$k]['sid']), 'at' => $when($rows[$k])];
            }
        }
    }
    ksort($flags);
    return array_values($flags);
}

/** Nahradí štítky třídy výsledkem skenu (idempotentní). @param list<array<string,mixed>> $flags */
function i66_store(string $classId, array $flags): int
{
    $flags = array_slice($flags, 0, I66_MAX_FLAGS_PER_CLASS);
    storage_update(i66_path(), static function (array $data) use ($classId, $flags): array {
        if ($flags === []) unset($data[$classId]);
        else $data[$classId] = $flags;
        return $data;
    });
    return count($flags);
}

/** @return list<array{kind:string,test:string,sid_hash:string,at:int}> uložené štítky třídy */
function i66_flags(string $classId): array
{
    $rows = storage_read(i66_path(), false)[$classId] ?? [];
    return array_values(array_filter(is_array($rows) ? $rows : [], 'is_array'));
}

/** Začátek školního roku (1. 9.) platného v čase $now. */
function i66_school_year_start(?int $now = null): int
{
    $now ??= time();
    $year = (int)date('Y', $now);
    return (int)mktime(0, 0, 0, 9, 1, (int)date('n', $now) >= 9 ? $year : $year - 1);
}

/**
 * Retence: štítky z minulého školního roku se po jeho skončení mažou. Dry-run nic nemění.
 * @return array{purge:int,kept:int,dry_run:bool}
 */
function i66_retention_purge(bool $dryRun, ?int $now = null): array
{
    $limit = i66_school_year_start($now);
    $out = ['purge' => 0, 'kept' => 0, 'dry_run' => $dryRun];
    $apply = static function (array $data) use ($limit, &$out): array {
        $out = ['purge' => 0, 'kept' => 0, 'dry_run' => $out['dry_run']];
        foreach ($data as $classId => $rows) {
            $keep = array_values(array_filter((array)$rows, static fn($r): bool => is_array($r) && (int)($r['at'] ?? 0) >= $limit));
            $out['purge'] += count((array)$rows) - count($keep);
            $out['kept'] += count($keep);
            if ($keep === []) unset($data[$classId]);
            else $data[$classId] = $keep;
        }
        return $data;
    };
    if ($dryRun) $apply(storage_read(i66_path(), false));
    else storage_update(i66_path(), $apply);
    return $out;
}
