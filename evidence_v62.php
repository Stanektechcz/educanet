<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/competencies_v62.php'; // COMP62_RETENTION_GRACE_DAYS i při volání z CLI retence

/**
 * EDUCANET v62 · záznam důkazu (append-only).
 *
 * Soubor storage/evidence_v62/<student_id>.json.php = {"v":1,"rows":[…],"meta":{"synced_at":<unix>}}.
 * Řádek: {student_id, competency, level 1–4, source, score 0–1, at (ISO 8601), artefact_ref, k}.
 * Žádný volný text – jen identifikátory ve vymezeném tvaru (soukromí nezletilých); artefact_ref je odkaz
 * na zdroj (lab:practice:sit-4, test:<id>:<téma> …), ne jeho obsah. Řádky se jen přidávají; duplicitu
 * (stejné source|artefact_ref|competency|at) zápis tiše přeskočí, takže opakovaná synchronizace je idempotentní.
 * Zápis výhradně přes storage_update (zámek přes čtení i zápis). Retence: ev62_retention_purge().
 */

const EV62_VERSION = 1;
/** Typy zdrojů (váhy viz mastery_v62.php). */
const EV62_SOURCES = ['test', 'project', 'lab', 'game', 'arena', 'lesson'];
const EV62_MAX_ROWS = 5000;
const EV62_ID_RE = '/^stu_[0-9a-f]{16}$/';
const EV62_COMP_RE = '/^[a-z][a-z0-9_]{2,39}$/';
const EV62_REF_RE = '/^[a-z0-9_]{2,12}:[A-Za-z0-9_.:\-]{1,90}$/';
const EV62_AT_RE = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+\-]\d{2}:\d{2}$/';

function ev62_dir(): string
{
    return STORAGE_DIR . '/evidence_v62';
}

function ev62_valid_id(string $studentId): bool
{
    return preg_match(EV62_ID_RE, $studentId) === 1;
}

function ev62_path(string $studentId): string
{
    if (!ev62_valid_id($studentId)) throw new InvalidArgumentException('Neplatné student_id.');
    return ev62_dir() . '/' . $studentId . '.json.php';
}

/** Hash pro deduplikaci. */
function ev62_key(string $source, string $ref, string $competency, string $at): string
{
    return sha1($source . '|' . $ref . '|' . $competency . '|' . $at);
}

/** Normalizovaný řádek, nebo null (neplatná pole se nikdy neukládají). */
function ev62_normalize(string $studentId, array $row): ?array
{
    $competency = is_string($row['competency'] ?? null) ? $row['competency'] : '';
    $source = is_string($row['source'] ?? null) ? $row['source'] : '';
    $ref = is_string($row['artefact_ref'] ?? null) ? $row['artefact_ref'] : '';
    $at = is_string($row['at'] ?? null) ? $row['at'] : '';
    $level = $row['level'] ?? null;
    $score = $row['score'] ?? null;
    if (!ev62_valid_id($studentId) || preg_match(EV62_COMP_RE, $competency) !== 1 || !in_array($source, EV62_SOURCES, true)) return null;
    if (preg_match(EV62_REF_RE, $ref) !== 1 || preg_match(EV62_AT_RE, $at) !== 1 || strtotime($at) === false) return null;
    if (!is_int($level) || $level < 1 || $level > 4 || !(is_int($score) || is_float($score)) || $score < 0 || $score > 1) return null;
    return ['student_id' => $studentId, 'competency' => $competency, 'level' => $level, 'source' => $source, 'score' => round((float)$score, 3),
        'at' => $at, 'artefact_ref' => $ref, 'k' => ev62_key($source, $ref, $competency, $at)];
}

/**
 * Přidá důkazy (jen nové). $syncedAt (unix) se zapíše do meta pro TTL synchronizace.
 * @param list<array<string,mixed>> $rows
 * @return array{added:int,duplicates:int,invalid:int}
 */
function ev62_append(string $studentId, array $rows, ?int $syncedAt = null): array
{
    $path = ev62_path($studentId);
    $stat = ['added' => 0, 'duplicates' => 0, 'invalid' => 0];
    storage_update($path, static function (array $data) use ($studentId, $rows, $syncedAt, &$stat): array {
        $list = array_values(array_filter((array)($data['rows'] ?? []), 'is_array'));
        $seen = [];
        foreach ($list as $existing) $seen[(string)($existing['k'] ?? '')] = true;
        foreach ($rows as $row) {
            $norm = is_array($row) ? ev62_normalize($studentId, $row) : null;
            if ($norm === null || count($list) >= EV62_MAX_ROWS) { $stat['invalid']++; continue; }
            if (isset($seen[$norm['k']])) { $stat['duplicates']++; continue; }
            $seen[$norm['k']] = true;
            $list[] = $norm;
            $stat['added']++;
        }
        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
        if ($syncedAt !== null) $meta['synced_at'] = $syncedAt;
        return ['v' => EV62_VERSION, 'rows' => $list, 'meta' => $meta];
    });
    return $stat;
}

/** @return list<array<string,mixed>> důkazy žáka (nejstarší první); chybějící soubor = prázdné pole */
function ev62_read(string $studentId): array
{
    if (!ev62_valid_id($studentId)) return [];
    return array_values(array_filter((array)(storage_read(ev62_path($studentId), false)['rows'] ?? []), 'is_array'));
}

/** Čas poslední synchronizace (unix), 0 = nikdy. */
function ev62_synced_at(string $studentId): int
{
    if (!ev62_valid_id($studentId)) return 0;
    return (int)(storage_read(ev62_path($studentId), false)['meta']['synced_at'] ?? 0);
}

/**
 * Retence: smaže důkazy (a cache zvládnutí) žáků, kteří mají stav left/archived déle než
 * COMP62_RETENTION_GRACE_DAYS. Bez data archivace se nemaže nic. Dry-run nic nemění.
 * @return array{files:int,purge:int,kept_unknown:int,ids:list<string>,dry_run:bool}
 */
function ev62_retention_purge(bool $dryRun, ?int $now = null): array
{
    $now ??= time();
    $out = ['files' => 0, 'purge' => 0, 'kept_unknown' => 0, 'ids' => [], 'dry_run' => $dryRun];
    if (!function_exists('identity58_student')) return $out;
    foreach (glob(ev62_dir() . '/stu_*.json.php') ?: [] as $file) {
        $id = basename($file, '.json.php');
        if (!ev62_valid_id($id)) continue;
        $out['files']++;
        $stu = identity58_student($id);
        if (!is_array($stu) || !in_array((string)($stu['status'] ?? 'active'), ['left', 'archived'], true)) continue;
        $since = strtotime((string)($stu['archived_at'] ?? '')) ?: 0;
        if ($since <= 0) { $out['kept_unknown']++; continue; }
        if ($now - $since < COMP62_RETENTION_GRACE_DAYS * 86400) continue;
        $out['purge']++;
        $out['ids'][] = $id;
        if (!$dryRun) ev62_delete_student_files($id);
    }
    return $out;
}

/** Smaže soubor pod výlučným zámkem (stejným jako storage_update), pak i zámkový soubor. */
function ev62_delete_locked(string $path): void
{
    $fp = storage_lock_exclusive($path);
    try {
        if (is_file($path)) @unlink($path);
    } finally {
        storage_unlock($fp, $path);
    }
    if (is_file($path . STORAGE_LOCK_SUFFIX)) @unlink($path . STORAGE_LOCK_SUFFIX);
}

/** Smaže důkazy, cache zvládnutí žáka a cache tříd pilotu (odvoditelné, přepočítají se bez smazaného žáka). */
function ev62_delete_student_files(string $studentId): void
{
    $paths = [ev62_path($studentId), STORAGE_DIR . '/mastery_v62/' . $studentId . '.json.php'];
    foreach (COMP62_PILOT_CLASSES as $classId) $paths[] = STORAGE_DIR . '/mastery_v62/_class_' . preg_replace('/[^A-Za-z0-9_]/', '', $classId) . '.json.php';
    foreach ($paths as $path) {
        if (is_file($path)) ev62_delete_locked($path);
    }
}