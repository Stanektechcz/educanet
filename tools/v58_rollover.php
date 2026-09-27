<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v58 · F6 – přechod na nový školní rok.
 *
 *   php tools/v58_rollover.php --dry-run [--year=2027] [volby]   plán (nic nezmění)
 *   php tools/v58_rollover.php --apply   [--year=2027] [volby]   provedení (záloha mladší než 1 h je povinná)
 *
 * Postup: 4.A → archiv (absolventi), 3.A → 4.A, 2.A → 3.A, 1.A → 2.A; nové kódy tříd; XP, odznaky a vyřešené
 * úrovně labu se zkopírují do nových klíčů žáka; data kurzu zůstávají historií ročníku (nic se nemaže).
 * Volby (výchozí = doporučení):
 *   --year=RRRR               rok začátku nového školního roku (výchozí: aktuální + 1)
 *   --no-carry-xp             nepřenášet XP
 *   --no-carry-badges         nepřenášet odznaky a úspěchy
 *   --no-carry-lab            nepřenášet vyřešené úrovně Linux Labu
 *   --allow-graduate-login    absolventi se smí dál přihlásit (bez třídy); výchozí = přihlášení zablokovat
 *   --keep-codes              ponechat staré kódy tříd (nedoporučeno)
 *   --repeat=stu_a,stu_b      opakují ročník (zůstávají ve třídě)
 *   --left=stu_c              odešli ze školy (archiv, přihlášení zablokováno)
 *   --backup-dir=<adresář>    kde hledat zálohu (výchozí EDUCANET_BACKUP_DIR / ../educanet-backups)
 *   --names / --no-names      jména žáků jen na obrazovce (výchozí: jen na interaktivním terminálu)
 *   --allow-missing-integration  jen pro audit: provést i bez háčků aplikace (INTEGRATION.md)
 * Exit: 0 = OK, 1 = chyba, 2 = odmítnuto.
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/accounts_v53.php';
require_once dirname(__DIR__) . '/identity_v58_rollover.php';

function ro58_opt(array $argv, string $name): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    }
    return null;
}

function ro58_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

function ro58_list(?string $value): array
{
    return array_values(array_filter(array_map('trim', explode(',', (string)$value)), static fn(string $v): bool => $v !== ''));
}

function ro58_print_plan(array $plan, array $backup, array $missing, bool $names): void
{
    $c = $plan['counts'];
    $o = $plan['options'];
    echo 'ROLLOVER_PLAN year=' . $plan['year'] . ' (' . $plan['year'] . '/' . substr((string)($plan['year'] + 1), 2) . ') stav='
        . ($plan['already_applied'] ? 'uz-provedeno' : 'novy') . "\n";
    foreach ($c['move'] as $transition => $n) echo "  přesun $transition: $n\n";
    echo '  archivace absolventů: ' . $c['archive'] . ' | opakují: ' . $c['repeat'] . ' | odchod: ' . $c['left']
        . ' | demo beze změny: ' . $c['demo_skipped'] . ' | neznámá třída: ' . $c['unknown_class'] . "\n";
    echo '  přenos profilu (XP ' . ($o['carry_xp'] ? 'ano' : 'ne') . ', odznaky ' . ($o['carry_badges'] ? 'ano' : 'ne') . '): ' . $c['carry_profile']
        . ' | přenos labu (' . ($o['carry_lab'] ? 'ano' : 'ne') . '): ' . $c['carry_lab'] . "\n";
    echo "  kurz: archivuje se (zůstává historií ročníku)\n";
    echo '  nové kódy tříd: ' . ($plan['new_codes'] ? count($plan['new_codes']) . ' (vygenerují se při --apply)' : 'ne') . "\n";
    echo '  absolventi: ' . ($o['block_graduates'] ? 'přihlášení zablokováno' : 'přihlášení povoleno (bez třídy)') . "\n";
    echo '  záloha: ' . ($backup['ok'] ? 'OK (' . intdiv((int)$backup['age'], 60) . ' min)' : 'CHYBÍ – ' . $backup['reason']) . "\n";
    echo '  háčky aplikace: ' . ($missing ? 'chybí: ' . implode('; ', $missing) : 'OK') . "\n";
    echo '  jmenovci v cílové třídě: ' . count($plan['conflicts']) . "\n";
    foreach ($plan['conflicts'] as $k) {
        echo '    ' . $k['stu_id'] . ' → ' . $k['to'] . ' (klíč ' . $k['type'] . ', patří ' . ($k['other'] ?? 'neznámému účtu') . ')'
            . ($names ? ' „' . $k['label'] . '“' : '') . "\n";
    }
    foreach ($plan['blockers'] as $b) echo '  BLOKUJE: ' . $b . "\n";
    if (!$names) return;
    echo "  žáci (jen obrazovka):\n";
    foreach ($plan['moves'] as $m) echo '    ' . $m['stu_id'] . '  ' . str_pad($m['action'], 8) . ' ' . $m['from'] . ' → ' . $m['to'] . '  ' . $m['label'] . "\n";
}

$argv = $_SERVER['argv'] ?? [];
$dry = ro58_flag($argv, 'dry-run');
$apply = ro58_flag($argv, 'apply');
if ($dry === $apply) {
    fwrite(STDERR, "Zadej právě jednu z voleb --dry-run nebo --apply.\n");
    exit(1);
}
$yearArg = ro58_opt($argv, 'year');
if ($yearArg !== null && !preg_match('/^\d{4}$/', $yearArg)) {
    fwrite(STDERR, "Neplatný rok (--year=RRRR).\n");
    exit(1);
}
$names = !ro58_flag($argv, 'no-names') && (ro58_flag($argv, 'names') || (function_exists('stream_isatty') && @stream_isatty(STDOUT)));
$opts = [
    'carry_xp' => !ro58_flag($argv, 'no-carry-xp'),
    'carry_badges' => !ro58_flag($argv, 'no-carry-badges'),
    'carry_lab' => !ro58_flag($argv, 'no-carry-lab'),
    'block_graduates' => !ro58_flag($argv, 'allow-graduate-login'),
    'new_codes' => !ro58_flag($argv, 'keep-codes'),
    'repeat' => ro58_list(ro58_opt($argv, 'repeat')),
    'left' => ro58_list(ro58_opt($argv, 'left')),
];
$backupDir = identity58_backup_dir(ro58_opt($argv, 'backup-dir'));

try {
    // Náhled pracuje s registrem sestaveným jen v paměti – nic se nezapíše.
    $preview = identity58_compute(identity58_registry(), identity58_directory_rows(), student_account_map(), local_accounts(), time());
    $year = $yearArg !== null ? (int)$yearArg : identity58_default_year($preview['registry']);
    $plan = identity58_rollover_plan($year, $opts, $preview['registry']);
    $backup = identity58_backup_check($backupDir);
    $missing = identity58_integration_missing();
    ro58_print_plan($plan, $backup, $missing, $names);
    if ($dry) {
        echo "ROLLOVER_DRY_RUN_OK\n";
        exit(0);
    }
    $res = identity58_rollover_apply($year, $opts, $backupDir, ro58_flag($argv, 'allow-missing-integration'));
    if (!$res['ok']) {
        echo 'ROLLOVER_REFUSED ' . $res['error'] . "\n";
        exit(2);
    }
    if (!empty($res['noop'])) {
        echo "ROLLOVER_ALREADY_APPLIED year=$year (nic se nezměnilo)\n";
        exit(0);
    }
    foreach ($res['codes'] as $classId => $code) echo "  nový kód $classId: $code\n";
    echo 'ROLLOVER_APPLY_OK year=' . $year . ' profiles=' . $res['summary']['profiles_copied'] . ' lab=' . $res['summary']['lab_copied']
        . ' accounts=' . ($res['summary']['accounts_map'] + $res['summary']['accounts_local']) . "\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'ROLLOVER_ERROR ' . $e->getMessage() . "\n");
    exit(1);
}
