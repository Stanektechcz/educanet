<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 migrace 0001 (DAT-03): „pouze přidávané“ JSON soubory → měsíční proudy JSONL.
 *
 * Pro každý registrovaný proud (storage_streams()) s existujícím legacy souborem:
 *  1. pod zámkem legacy souboru přečte všechny záznamy,
 *  2. rozdělí je do měsíců podle časových polí (bez času → měsíc poslední úpravy souboru),
 *  3. připojí je do storage/<proud>/<YYYY-MM>.jsonl.php – záznamy, které už v proudu jsou
 *     (stejný JSON, počítáno jako multimnožina), přeskočí → opakované spuštění nic nezdvojí,
 *  4. ověří, že každý legacy záznam je v proudu, a teprve pak legacy soubor PŘESUNE
 *     (nesmaže) do storage/_migrated_v58/<soubor>.
 * Idempotentní: bez legacy souboru proud nic nedělá. Dry-run nic nezapisuje.
 */

return [
    'id' => '0001_streams_jsonl',
    'description' => 'Pouze přidávané soubory (výsledky, odevzdání, logy událostí) převést na měsíční JSONL proudy.',
    'files' => array_values(array_map(static fn(array $d): string => (string)$d['legacy'], storage_streams())),
    'up' => static function (bool $dryRun): array {
        $report = ['changed' => 0, 'details' => []];
        foreach (storage_streams() as $stream => $def) {
            $legacy = STORAGE_DIR . '/' . $def['legacy'];
            if (!is_file($legacy)) {
                if (!$dryRun) storage_schema_bump([$stream => 2]); // bez legacy dat je proud rovnou ve verzi 2
                continue;
            }
            $records = array_values(array_filter(storage_read($legacy), 'is_array'));
            $fallbackMonth = date('Y-m', (int)(@filemtime($legacy) ?: time()));
            // Multimnožina záznamů, které už v proudu jsou (obnova po přerušené migraci).
            $existing = [];
            foreach (storage_stream_months($stream) as $month) {
                foreach (storage_stream_month_rows($stream, $month) as $row) {
                    $h = hash('sha256', storage_encode_line($row));
                    $existing[$h] = ($existing[$h] ?? 0) + 1;
                }
            }
            $byMonth = [];
            $skipped = 0;
            foreach ($records as $record) {
                $h = hash('sha256', storage_encode_line($record));
                if (($existing[$h] ?? 0) > 0) { $existing[$h]--; $skipped++; continue; }
                $byMonth[storage_record_month($record, (array)$def['time']) ?? $fallbackMonth][] = $record;
            }
            $toAppend = array_sum(array_map('count', $byMonth));
            $report['details'][] = sprintf('%s: legacy=%d, připojit=%d, už v proudu=%d, měsíců=%d', $stream, count($records), $toAppend, $skipped, count($byMonth));
            $report['changed']++;
            if ($dryRun) continue;
            ksort($byMonth, SORT_STRING);
            foreach ($byMonth as $month => $rows) storage_append_many($stream, $rows, (string)$month);
            // Ověření: každý legacy záznam musí být v proudu (multimnožina), jinak se legacy soubor nepřesune.
            $inStream = [];
            foreach (storage_stream_months($stream) as $month) {
                unset($GLOBALS['educanet_stream_cache']);
                foreach (storage_stream_month_rows($stream, $month) as $row) {
                    $h = hash('sha256', storage_encode_line($row));
                    $inStream[$h] = ($inStream[$h] ?? 0) + 1;
                }
            }
            foreach ($records as $record) {
                $h = hash('sha256', storage_encode_line($record));
                if (($inStream[$h] ?? 0) < 1) throw new RuntimeException('Migrace ' . $stream . ': záznam chybí v proudu, legacy soubor zůstává.');
                $inStream[$h]--;
            }
            $target = STORAGE_DIR . '/_migrated_v58/' . $def['legacy'];
            if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0770, true) && !is_dir(dirname($target))) throw new RuntimeException('Nelze vytvořit adresář _migrated_v58.');
            if (is_file($target)) $target .= '.' . date('YmdHis');
            if (!@rename($legacy, $target)) {
                if (!copy($legacy, $target) || hash_file('sha256', $legacy) !== hash_file('sha256', $target)) throw new RuntimeException('Migrace ' . $stream . ': legacy soubor nelze přesunout.');
                @unlink($legacy);
            }
            php_json_cache_forget($legacy);
            storage_schema_bump([$stream => 2]);
        }
        return $report;
    },
];
