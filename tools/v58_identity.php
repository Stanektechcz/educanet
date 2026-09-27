<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v58 · F6 – registr identity žáků (student_id) a kolize jmen.
 *
 * Použití:
 *   php tools/v58_identity.php                         přehled registru + podezřelé kolize (nic nezapisuje)
 *   php tools/v58_identity.php --build [--apply]       sestavení registru (bez --apply jen náhled počtů)
 *   php tools/v58_identity.php --show=<alias|stu_id>   student_id a aliasy jednoho žáka
 *   php tools/v58_identity.php --split=<stu_id> --label="Jan Novák (B)" [--account=<klíč účtu>] [--apply]
 *        rozliší jmenovce: nový žák s novým ID (+ volitelně jeho účet); sloučená historie zůstává u původního ID
 *   php tools/v58_identity.php --relabel=<stu_id> --label="Petr Svoboda (B)" [--apply]
 *        přejmenuje žáka (před přechodem roku kvůli jmenovci v cílové třídě); data se zkopírují do nových klíčů
 * Společné: --names / --no-names (jména jen na obrazovce – výchozí zapnuto jen na interaktivním terminálu),
 *           --backup-dir=<adresář> (split/relabel s --apply vyžadují zálohu mladší než 1 h).
 * Exit: 0 = OK, 1 = chyba, 2 = odmítnuto.
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/identity_v58_rollover.php';

function idt58_opt(array $argv, string $name): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    }
    return null;
}

function idt58_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

function idt58_names(array $argv): bool
{
    if (idt58_flag($argv, 'no-names')) return false;
    return idt58_flag($argv, 'names') || (function_exists('stream_isatty') && @stream_isatty(STDOUT));
}

function idt58_print_stats(array $stats): void
{
    foreach ($stats as $k => $v) echo '  ' . str_pad((string)$k, 18) . ' ' . (is_bool($v) ? ($v ? 'ano' : 'ne') : (string)$v) . "\n";
}

function idt58_overview(bool $names): void
{
    $reg = identity58_registry();
    $byStatus = [];
    $byClass = [];
    foreach ($reg['students'] as $stu) {
        if (!is_array($stu)) continue;
        $byStatus[(string)$stu['status']] = ($byStatus[(string)$stu['status']] ?? 0) + 1;
        if (($stu['status'] ?? '') === 'active') $byClass[(string)$stu['class_id']] = ($byClass[(string)$stu['class_id']] ?? 0) + 1;
    }
    ksort($byClass);
    $map = student_account_map();
    $withId = count(array_filter($map, static fn($r): bool => is_array($r) && !empty($r['student_id'])));
    echo "IDENTITY_REGISTRY students=" . count($reg['students']) . ' aliases=' . count($reg['alias_index'])
        . ' accounts=' . count($map) . ' accounts_with_id=' . $withId . ' built_at=' . (string)($reg['built_at'] ?? '-') . "\n";
    foreach ($byStatus as $s => $n) echo "  stav $s: $n\n";
    foreach ($byClass as $c => $n) echo "  třída $c: $n aktivních\n";
    foreach ($reg['rollovers'] as $year => $r) echo "  přechod roku $year: " . (string)($r['status'] ?? '?') . ' ' . (string)($r['applied_at'] ?? '') . "\n";
    $dups = identity58_duplicates($reg);
    echo 'IDENTITY_COLLISIONS count=' . count($dups) . "\n";
    foreach ($dups as $d) {
        echo '  [' . $d['severity'] . '] ' . $d['type'] . ' ' . $d['class_id'] . ' ' . implode(',', $d['stu_ids'])
            . ($names ? ' „' . $d['label'] . '“' : '') . ' – ' . $d['detail'] . "\n";
    }
}

function idt58_require_backup(array $argv): void
{
    $check = identity58_backup_check(identity58_backup_dir(idt58_opt($argv, 'backup-dir')));
    if (!$check['ok']) {
        echo 'IDENTITY_REFUSED ' . $check['reason'] . "\n";
        exit(2);
    }
}

$argv = $_SERVER['argv'] ?? [];
$names = idt58_names($argv);
$apply = idt58_flag($argv, 'apply');

try {
    if (idt58_flag($argv, 'build')) {
        $stats = identity58_build(!$apply);
        echo ($apply ? 'IDENTITY_BUILD_OK' : 'IDENTITY_BUILD_DRY_RUN') . "\n";
        idt58_print_stats($stats);
        exit(0);
    }
    if (($alias = idt58_opt($argv, 'show')) !== null) {
        $stuId = str_starts_with($alias, 'stu_') ? $alias : identity58_id_for_alias($alias);
        $stu = $stuId !== null ? identity58_student($stuId) : null;
        if ($stu === null) { echo "IDENTITY_NOT_FOUND\n"; exit(1); }
        echo "IDENTITY $stuId class=" . $stu['class_id'] . ' status=' . $stu['status'] . ' cohort=' . (string)($stu['cohort'] ?? '-')
            . ($names ? ' label=„' . $stu['label'] . '“' : '') . "\n";
        foreach (identity58_aliases($stuId) as $a) echo '  alias ' . $a . "\n";
        foreach ((array)($stu['retired_aliases'] ?? []) as $a) echo '  retired ' . $a . "\n";
        exit(0);
    }
    foreach (['split', 'relabel'] as $cmd) {
        if (($stuId = idt58_opt($argv, $cmd)) === null) continue;
        $label = trim((string)idt58_opt($argv, 'label'));
        if ($apply) idt58_require_backup($argv);
        $res = $cmd === 'split'
            ? identity58_split($stuId, $label, (string)idt58_opt($argv, 'account'), !$apply)
            : identity58_relabel($stuId, $label, !$apply);
        if (!$res['ok']) { echo 'IDENTITY_REFUSED ' . $res['error'] . "\n"; exit(2); }
        echo 'IDENTITY_' . strtoupper($cmd) . ($apply ? '_OK' : '_DRY_RUN') . "\n";
        idt58_print_stats(array_diff_key($res, ['ok' => 1]));
        exit(0);
    }
    idt58_overview($names);
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'IDENTITY_ERROR ' . $e->getMessage() . "\n");
    exit(1);
}
