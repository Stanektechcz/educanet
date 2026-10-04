<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · přeřazení starých dat učitele mezi účty (ROADMAP_V60 §2). Nástroj pro administrátora.
 *
 * Použití:
 *   php tools/v61_teacher_data_reassign.php --from-account=<id> --to-account=<id> [--store=filters,watchlist]
 *   php tools/v61_teacher_data_reassign.php --from-owner=<64 hex owner_key> --to-account=<id> ...
 *   přidej --apply          skutečně přepíše vlastníka (jinak jen náhled; --dry-run je výchozí)
 *   přidej --backup-now     před --apply nejdřív vytvoří zálohu storage/
 *   --backup-dir=<adresář>  kde hledat/vytvářet zálohy (výchozí EDUCANET_BACKUP_DIR nebo ../educanet-backups)
 *
 * Co dělá: u záznamů uložených pod vlastníkem „z“ (uložené filtry, watchlist, follow-upy, šablony, automatizace,
 * upozornění, poznámky, intervence) změní owner_key na vlastníka „na“. Záznamy tříd mimo rozsah cílového účtu se
 * nepřenášejí (zůstanou původnímu vlastníkovi) a jen se spočítají. Audit operací (historie) se nikdy nepřepisuje.
 * --apply bez čerstvé zálohy (mladší než 1 h, stejná storage/) se odmítne. Výstup a log neobsahují jména ani
 * obsah záznamů – jen počty a zkrácený otisk klíče.
 * Konec: REASSIGN_OK dry_run=0|1 rows=N skipped=M | REASSIGN_REFUSED … (exit 2) | REASSIGN_FAIL … (exit 1).
 */

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/teacher_accounts_v59.php';
require_once dirname(__DIR__) . '/teacher_scope_v59.php';
require_once __DIR__ . '/migrate.php';

/** @return array<string,string> krátký název => soubor úložiště */
function reassign61_stores(): array
{
    $out = [];
    foreach (array_keys(teacher59_reclaim_stores()) as $file) $out[str_replace(['teacher_', '.json.php'], '', $file)] = $file;
    return $out;
}

/** Soubory úložišť podle --store (prázdné = všechny); null = neznámý název. */
function reassign61_select_stores(string $list): ?array
{
    $all = reassign61_stores();
    if (trim($list) === '') return array_values($all);
    $files = [];
    foreach (explode(',', $list) as $name) {
        $name = trim($name);
        $file = $all[$name] ?? (in_array($name, $all, true) ? $name : null);
        if ($file === null) return null;
        $files[$file] = $file;
    }
    return array_values($files);
}

/** Odmítnutí s kódem 2 (chybný vstup/bezpečnostní pojistka), chyba prostředí je kód 1. */
function reassign61_result(bool $ok, int $code, string $line, int $rows = 0, int $skipped = 0, array $counts = []): array
{
    return ['ok' => $ok, 'code' => $code, 'line' => $line, 'rows' => $rows, 'skipped' => $skipped, 'counts' => $counts];
}

/**
 * Sestaví plán (a při $apply provede přeřazení pod jedním zámkem všech úložišť).
 * @param array{from_account?:string,from_owner?:string,to_account?:string,store?:string} $o
 */
function reassign61_run(array $o, bool $apply): array
{
    $to = teacher59_account((string)($o['to_account'] ?? ''));
    if ($to === null) return reassign61_result(false, 2, 'REASSIGN_REFUSED cílový účet neexistuje');
    $fromAccount = (string)($o['from_account'] ?? '');
    $fromOwner = (string)($o['from_owner'] ?? '');
    if (($fromAccount === '') === ($fromOwner === '')) return reassign61_result(false, 2, 'REASSIGN_REFUSED zadej právě jedno z --from-account / --from-owner');
    $old = $fromAccount !== '' ? teacher59_owner_key_for($fromAccount) : strtolower($fromOwner);
    if (preg_match('/^[a-f0-9]{64}$/D', $old) !== 1) return reassign61_result(false, 2, 'REASSIGN_REFUSED --from-owner musí být 64 hexadecimálních znaků');
    $new = teacher59_owner_key_for((string)$to['id']);
    if (hash_equals($old, $new)) return reassign61_result(false, 2, 'REASSIGN_REFUSED zdroj a cíl jsou stejný účet');
    $files = reassign61_select_stores((string)($o['store'] ?? ''));
    if ($files === null) return reassign61_result(false, 2, 'REASSIGN_REFUSED neznámé úložiště v --store (' . implode(', ', array_keys(reassign61_stores())) . ')');
    $paths = [];
    foreach ($files as $file) if (is_file(STORAGE_DIR . '/' . $file)) $paths[$file] = STORAGE_DIR . '/' . $file;
    $counts = array_fill_keys(array_keys($paths), 0);
    $skipped = 0;
    if ($paths === []) return reassign61_result(true, 0, 'REASSIGN_OK dry_run=' . ($apply ? '0' : '1') . ' rows=0 skipped=0');
    storage_update_many(array_values($paths), static function (array $data) use ($paths, $old, $new, $to, $apply, &$counts, &$skipped): array {
        $out = [];
        foreach ($paths as $file => $path) {
            $rows = (array)($data[$path] ?? []);
            foreach ($rows as $k => $row) {
                if (!is_array($row) || !hash_equals((string)($row['owner_key'] ?? ''), $old)) continue;
                $classId = teacher59_row_class($row);
                if ($classId !== '' && !teacher59_account_can_class($to, $classId)) { $skipped++; continue; }
                $counts[$file]++;
                $row['owner_key'] = $new;
                if (array_key_exists('owner_label', $row)) $row['owner_label'] = (string)$to['display_name'];
                if ($file === 'teacher_saved_filters.json.php') $row['owner_version'] = 3;
                $rows[$k] = $row;
            }
            if ($apply && $counts[$file] > 0) $out[$path] = $rows;
        }
        return $out; // náhled vrací [] = beze změny (storage_update_many nic nezapíše)
    });
    $total = array_sum($counts);
    if ($apply) {
        $perStore = [];
        foreach ($counts as $file => $n) if ($n > 0) $perStore[str_replace(['teacher_', '.json.php'], '', $file)] = $n;
        teacher59_log('reassigned', 'cli', (string)$to['id'], [], 'rows:' . $total . ' skipped:' . $skipped, ['old_key' => substr(hash('sha256', $old), 0, 16), 'counts' => $perStore, 'skipped_out_of_scope' => $skipped]);
    }
    return reassign61_result(true, 0, 'REASSIGN_OK dry_run=' . ($apply ? '0' : '1') . ' rows=' . $total . ' skipped=' . $skipped, $total, $skipped, $counts);
}

/** Čerstvá záloha této storage (≤ 1 h) nebo null; s $makeNow ji nejdřív vytvoří. */
function reassign61_backup(string $backupDir, bool $makeNow): ?string
{
    if ($makeNow) backup_storage_run(STORAGE_DIR, $backupDir, 14, false, null, true);
    return migrate_fresh_backup($backupDir, STORAGE_DIR);
}

function reassign61_cli(array $argv): int
{
    $opt = static function (string $name) use ($argv): string {
        foreach ($argv as $a) if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
        return '';
    };
    $apply = in_array('--apply', $argv, true);
    if ($apply && in_array('--dry-run', $argv, true)) { fwrite(STDERR, "REASSIGN_REFUSED --apply a --dry-run nelze zadat zároveň\n"); return 2; }
    if ($apply) {
        $env = rtrim((string)getenv('EDUCANET_BACKUP_DIR'), '/\\');
        $dir = $opt('backup-dir') !== '' ? $opt('backup-dir') : ($env !== '' ? $env : dirname(dirname(__DIR__)) . '/educanet-backups');
        try { $backup = reassign61_backup($dir, in_array('--backup-now', $argv, true)); }
        catch (Throwable $e) { fwrite(STDERR, 'REASSIGN_FAIL záloha se nepodařila: ' . get_class($e) . "\n"); return 1; }
        if ($backup === null) { fwrite(STDERR, "REASSIGN_REFUSED chybí záloha storage/ mladší než 1 h (přidej --backup-now nebo spusť tools/backup_storage.php)\n"); return 2; }
        echo 'Záloha: ' . basename($backup) . "\n";
    }
    try {
        $result = reassign61_run(['from_account' => $opt('from-account'), 'from_owner' => $opt('from-owner'), 'to_account' => $opt('to-account'), 'store' => $opt('store')], $apply);
    } catch (Throwable $e) {
        fwrite(STDERR, 'REASSIGN_FAIL ' . get_class($e) . "\n");
        return 1;
    }
    foreach ($result['counts'] as $file => $n) echo '  ' . str_pad(str_replace(['teacher_', '.json.php'], '', (string)$file), 28) . $n . "\n";
    if ($result['ok'] && !$apply) echo "Náhled – nic se nezapsalo. Pro provedení přidej --apply (vyžaduje čerstvou zálohu).\n";
    fwrite($result['ok'] ? STDOUT : STDERR, $result['line'] . "\n");
    return $result['code'];
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(reassign61_cli($argv));
