<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v67 · rozšířená kontrola provozu (týdenní health): záloha (stáří, šifrování), klíč záloh, čekající migrace,
 * poslední noční přepočet, APCu, session.use_strict_mode a display_errors. Čistě čtecí; výstup jsou jen stavy a počty
 * (žádné cesty k tajemstvím, žádné klíče). Volá ji tools/v61_weekly_health.php jako kontrolu `ops`.
 */

const OPS67_BACKUP_MAX_HOURS = 36;
const OPS67_NIGHTLY_MAX_HOURS = 36;

/** Nejnovější záloha v adresáři: ['at'=>unix,'encrypted'=>bool] nebo null. */
function ops67_latest_backup(string $dir): ?array
{
    $best = null;
    foreach (glob(rtrim($dir, '/\\') . '/storage-*') ?: [] as $path) {
        $encrypted = str_ends_with($path, '.edubak');
        if (!$encrypted && !is_dir($path)) continue;
        $at = (int)filemtime($path);
        if ($best === null || $at > $best['at']) $best = ['at' => $at, 'encrypted' => $encrypted];
    }
    return $best;
}

/** Počet čekajících migrací z migrations/ proti logu. */
function ops67_pending_migrations(): int
{
    $done = storage_read(STORAGE_DIR . '/migrations_v58.json.php', false);
    $n = 0;
    foreach (glob(__DIR__ . '/migrations/[0-9][0-9][0-9][0-9]_*.php') ?: [] as $file) {
        if (!isset($done[basename($file, '.php')])) $n++;
    }
    return $n;
}

/** @return list<array{name:string,status:string,detail:string}> */
function ops67_items(?int $now = null): array
{
    $now ??= time();
    $items = [];
    $add = static function (string $name, bool $ok, string $detail, string $badStatus = 'WARN') use (&$items): void { $items[] = ['name' => $name, 'status' => $ok ? 'PASS' : $badStatus, 'detail' => $detail]; };
    $dir = (string)getenv('EDUCANET_BACKUP_DIR');
    $backup = $dir !== '' && is_dir($dir) ? ops67_latest_backup($dir) : null;
    $age = $backup !== null ? intdiv($now - $backup['at'], 3600) : -1;
    $add('záloha_stáří', $backup !== null && $age <= OPS67_BACKUP_MAX_HOURS, $backup === null ? 'záloha nenalezena (EDUCANET_BACKUP_DIR)' : 'poslední záloha před ' . $age . ' h');
    $add('záloha_šifrovaná', $backup !== null && $backup['encrypted'], $backup === null ? 'bez zálohy' : ($backup['encrypted'] ? 'ano (.edubak)' : 'ne – nastav backup_key a --encrypt-if-key'));
    $add('backup_key', educanet_secret('backup_key') !== '', educanet_secret('backup_key') !== '' ? 'nastaven' : 'chybí (tools/backup_key_init.php)');
    $pending = ops67_pending_migrations();
    $add('migrace', $pending === 0, $pending === 0 ? 'žádné čekající' : $pending . ' čekajících (tools/migrate.php)');
    $nightly = (int)strtotime((string)(storage_read(STORAGE_DIR . '/v67_nightly.json.php', false)['last']['at'] ?? ''));
    $nAge = $nightly > 0 ? intdiv($now - $nightly, 3600) : -1;
    $add('noční_přepočet', $nightly > 0 && $nAge <= OPS67_NIGHTLY_MAX_HOURS, $nightly > 0 ? 'poslední před ' . $nAge . ' h' : 'zatím neproběhl (cron nightly)');
    $add('apcu', function_exists('educanet_apcu_enabled') && educanet_apcu_enabled(), function_exists('educanet_apcu_enabled') && educanet_apcu_enabled() ? 'zapnuto' : 'vypnuto (jen pomalejší, ne chyba)');
    $add('session.use_strict_mode', (string)ini_get('session.use_strict_mode') === '1' || PHP_SAPI === 'cli', 've webu ji bootstrap nastavuje na 1; v ini: ' . ((string)ini_get('session.use_strict_mode') ?: '0'));
    $display = strtolower((string)ini_get('display_errors'));
    $add('display_errors', in_array($display, ['', '0', 'off', 'false', 'stderr'], true), 'hodnota: ' . ($display === '' ? 'vypnuto' : $display) . ' (v produkci musí být vypnuto)');
    return $items;
}

/** Kontrola ve tvaru týdenního health (status, souhrn, počty, problémy). */
function ops67_health_check(?int $now = null): array
{
    $items = ops67_items($now);
    $bad = array_values(array_filter($items, static fn(array $i): bool => $i['status'] !== 'PASS'));
    return ['status' => $bad === [] ? 'PASS' : 'WARN', 'summary' => count($items) - count($bad) . '/' . count($items) . ' v pořádku',
        'counts' => ['ok' => count($items) - count($bad), 'warn' => count($bad)], 'issues' => array_map(static fn(array $i): string => $i['name'] . ': ' . $i['detail'], $bad)];
}
