<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v62 · tools/v62_evidence_backfill.php – zpětné vytvoření důkazů z historie (idempotentní).
 *
 *   php tools/v62_evidence_backfill.php [--class=class_3a] [--apply] [--json]
 *
 * Výchozí je --dry-run: nic se nezapíše, jen se vypíše statistika (kolik důkazů by vzniklo). --apply zapisuje
 * a ODMÍTNE běžet nad ostrou storage/ – je nutné nastavit EDUCANET_STORAGE_DIR na kopii. Opakované spuštění
 * nepřidá nic nového (deduplikace v ev62_append). Zdrojová data se nemění, výstup neobsahuje jména ani e-maily.
 * Konec: V62_BACKFILL_OK (nebo V62_BACKFILL_REFUSED / V62_BACKFILL_FAIL, nenulový exit kód).
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/intake_v51.php'; // roster z dotazníků (jako core skupina v index.php)
require_once dirname(__DIR__) . '/identity_v58.php';
require_once dirname(__DIR__) . '/competencies_v62.php';
require_once dirname(__DIR__) . '/evidence_v62.php';
require_once dirname(__DIR__) . '/evidence_v62_adapters.php';

function v62b_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return null;
}

/** Je úložiště ostré (projektová storage/ bez přesměrování)? */
function v62b_is_live_storage(): bool
{
    $live = str_replace('\\', '/', (string)realpath(dirname(__DIR__) . '/storage'));
    return str_replace('\\', '/', (string)realpath(STORAGE_DIR)) === $live;
}

/** @return array<string,int> */
function v62b_run_class(string $classId, bool $apply): array
{
    $subject = (string)comp62_subject_for_class($classId);
    $stat = ['students' => 0, 'no_identity' => 0, 'candidates' => 0, 'rows' => 0, 'new_rows' => 0, 'added' => 0, 'duplicates' => 0, 'unmapped' => 0];
    foreach (array_keys(project_students_for_class($classId)) as $key) {
        $ctx = ev62_student_context($classId, (string)$key);
        if ($ctx === null) { $stat['no_identity']++; continue; }
        $stat['students']++;
        $got = ev62_collect_student($ctx, $subject);
        $seen = array_flip(array_map(static fn(array $r): string => (string)($r['k'] ?? ''), ev62_read($ctx['id'])));
        $new = 0;
        foreach ($got['rows'] as $row) {
            $norm = ev62_normalize($ctx['id'], $row);
            if ($norm !== null && !isset($seen[$norm['k']])) { $seen[$norm['k']] = true; $new++; }
        }
        $stat['candidates'] += $got['candidates'];
        $stat['rows'] += count($got['rows']);
        $stat['new_rows'] += $new;
        $stat['unmapped'] += $got['unmapped'];
        if ($apply) {
            $res = ev62_append($ctx['id'], $got['rows'], time());
            $stat['added'] += $res['added'];
            $stat['duplicates'] += $res['duplicates'];
        }
    }
    return $stat;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) {
    $apply = in_array('--apply', $argv, true);
    $asJson = in_array('--json', $argv, true);
    $classId = v62b_arg($argv, 'class') ?? COMP62_PILOT_CLASSES[0];
    if (!comp62_enabled_for_class($classId)) { fwrite(STDERR, "V62_BACKFILL_FAIL třída mimo pilot\n"); exit(1); }
    if ($apply && v62b_is_live_storage()) {
        fwrite(STDERR, "V62_BACKFILL_REFUSED --apply nad ostrou storage/ je zakázáno; nastav EDUCANET_STORAGE_DIR na kopii.\n");
        exit(2);
    }
    try {
        $stat = v62b_run_class($classId, $apply);
    } catch (Throwable $e) {
        fwrite(STDERR, 'V62_BACKFILL_FAIL ' . get_class($e) . "\n");
        exit(1);
    }
    echo $asJson ? json_encode(['class' => $classId, 'apply' => $apply] + $stat, JSON_PRETTY_PRINT) . "\n"
        : ($apply ? 'APPLY' : 'DRYRUN') . ' class=' . $classId . ' ' . implode(' ', array_map(static fn($k, $v): string => $k . '=' . $v, array_keys($stat), $stat)) . "\n";
    echo "V62_BACKFILL_OK\n";
    exit(0);
}
