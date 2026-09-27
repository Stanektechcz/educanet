<?php

declare(strict_types=1);

/**
 * v58 · Jednorázová hesla žáků z příkazové řádky.
 *   php tools/v58_issue_passwords.php --class=class_3a          – nová hesla třídě (jen žáci bez vlastního hesla)
 *   php tools/v58_issue_passwords.php --all                     – totéž pro všechny třídy
 *   php tools/v58_issue_passwords.php --class=… --include-own   – i žákům s vlastním heslem (reset celé třídy!)
 *   php tools/v58_issue_passwords.php --class=… --dry-run       – jen spočítá, nic nemění
 *   php tools/v58_issue_passwords.php --migrate [--dry-run]     – migrace ze sdíleného hesla (vrací jen počty)
 *   --quiet                                                      – nevypisuje hesla, jen počty
 * Hesla se vypisují jen do terminálu (tabulka). Nepřesměrovávejte výstup do souboru v web rootu.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require_once $root . '/accounts_v53.php';

$args = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $arg, $m)) $args[$m[1]] = $m[2] ?? '1';
}
$dryRun = isset($args['dry-run']);
$quiet = isset($args['quiet']);

if (isset($args['migrate'])) {
    $stats = acc58_migrate($dryRun);
    if (!$dryRun) {
        storage_update(acc58_state_path(), static function (array $s) use ($stats): array {
            if ((int)($s['migration_version'] ?? 0) >= ACC58_MIGRATION_VERSION) return array_replace($s, ['last_check_at' => date(DATE_ATOM)]);
            return array_replace($s, ['migration_version' => ACC58_MIGRATION_VERSION, 'migrated_at' => date(DATE_ATOM),
                'issued' => (int)$stats['issued'], 'plaintext_removed' => (int)$stats['plaintext_removed'], 'via' => 'cli']);
        });
    }
    echo ($dryRun ? 'MIGRACE (zkouška, nic se nezměnilo)' : 'MIGRACE HOTOVA') . PHP_EOL;
    foreach (['accounts' => 'účtů celkem', 'issued' => 'nová jednorázová hesla', 'kept_own' => 'vlastní heslo (beze změny)', 'kept_pending' => 'platné jednorázové (beze změny)',
        'kept_expired' => 'vypršelé (beze změny)', 'plaintext_removed' => 'odstraněno initial_password'] as $key => $label) {
        echo '  ' . str_pad((string)$stats[$key], 5, ' ', STR_PAD_LEFT) . '  ' . $label . PHP_EOL;
    }
    if (!$dryRun && $stats['issued'] > 0) echo 'Kartičky vytiskněte v učitelském rozhraní: teacher.php?tab=pristupy' . PHP_EOL;
    exit(0);
}

$classIds = isset($args['all']) ? array_map('strval', array_keys($modules)) : (isset($args['class']) ? [(string)$args['class']] : []);
if (!$classIds) {
    fwrite(STDERR, 'Použití: --class=<id> | --all  [--include-own] [--dry-run] [--quiet]  nebo  --migrate [--dry-run]' . PHP_EOL);
    exit(2);
}
$includeOwn = isset($args['include-own']);
$total = 0;
foreach ($classIds as $classId) {
    if (!isset($modules[$classId])) { fwrite(STDERR, 'Neznámá třída ' . $classId . PHP_EOL); exit(1); }
    $name = (string)$modules[$classId]['name'];
    if ($dryRun) {
        $n = count(array_filter(acc58_class_rows($classId), static fn(array $r): bool => $includeOwn || $r['status'] !== 'own'));
        echo $name . ': vydalo by se ' . $n . ' hesel (zkouška, nic se nezměnilo)' . PHP_EOL;
        $total += $n;
        continue;
    }
    $rows = acc58_issue_for_class($classId, $includeOwn, 'cli');
    $total += count($rows);
    echo strtoupper($name) . ' · ' . (string)$modules[$classId]['subject'] . ' – nová hesla: ' . count($rows) . PHP_EOL;
    if ($quiet) continue;
    foreach ($rows as $email => $row) {
        echo '  ' . str_pad((string)$row['label'], 26) . str_pad((string)$email, 36) . str_pad((string)$row['password'], 30) . 'do ' . date('j. n. Y', (int)$row['expires_at']) . PHP_EOL;
    }
    echo PHP_EOL;
}
echo 'Celkem: ' . $total . ($dryRun ? ' (zkouška)' : '') . PHP_EOL;
exit(0);
