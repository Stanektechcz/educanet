<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/evidence_v62.php';

/**
 * v65 · Důkazy kompetencí z publikovaného hodnocení projektu a retence osobních textů.
 *
 * Důkaz vzniká JEN při publikaci hodnocení učitelem (koncept, pitch ani peer review nikdy): zdroj project, úroveň 4 („tvoří“),
 * skóre (úroveň − 1) / 3 zprůměrované po kritériích téže kompetence, artefact_ref proj:v65_<hash12>_v<verze>. Nová publikace
 * (nová verze) přidá další řádek; mastery_v62 pro stejný základ bere jen nejvyšší verzi. Identita jen přes identity_v58
 * (ev62_student_context); při nejistotě se nezapíše nic a případ se započítá. Mimo pilot kompetencí (comp62) se důkaz nezapisuje.
 *
 * Retence (rozhodnutí školy): pitch, poznámky k odevzdání, peer texty, deník, rozdělení bodů a reflexe portfolia se uchovají do konce
 * studia a smažou se COMP62_RETENTION_GRACE_DAYS po stavu left/archived žáka (tools/v58_retention.php).
 */

function proj65_evidence_ref(array $record, array $version): string
{
    return 'proj:v65_' . proj65_hash12((string)$record['ref']) . '_v' . (int)$version['version'];
}

/** Skóre kompetence = průměr (úroveň − 1) / 3 přes kritéria dané kompetence. @return array<string,float> */
function proj65_competency_scores(array $version): array
{
    $sum = [];
    foreach ((array)($version['competencies'] ?? []) as $crit => $comp) {
        $lvl = (int)(($version['levels'] ?? [])[$crit] ?? 0);
        if ($lvl < 1 || $lvl > 4) continue;
        $sum[(string)$comp][] = ($lvl - 1) / 3;
    }
    return array_map(static fn(array $v): float => round(array_sum($v) / count($v), 3), $sum);
}

/**
 * Zapíše důkazy všem členům cíle. Nejistá identita = přeskočit a započítat.
 * @return array{emitted:int,duplicates:int,skipped:int,reason:?string}
 */
function proj65_emit_evidence(array $record, array $version): array
{
    $out = ['emitted' => 0, 'duplicates' => 0, 'skipped' => 0, 'reason' => null];
    $classId = (string)$record['class_id'];
    if (!function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId)) return array_replace($out, ['reason' => 'not_pilot']);
    $scores = proj65_competency_scores($version);
    if ($scores === []) return array_replace($out, ['reason' => 'no_competency']);
    $subject = comp62_subject_for_class($classId);
    $known = $subject !== null ? comp62_competencies($subject) : [];
    $rows = [];
    foreach ($scores as $comp => $score) {
        if (!isset($known[$comp])) continue;
        $rows[] = ['competency' => $comp, 'level' => 4, 'source' => 'project', 'score' => $score, 'at' => (string)$version['published_at'], 'artefact_ref' => proj65_evidence_ref($record, $version)];
    }
    foreach (proj65_member_keys($record) as $key) {
        $ctx = ev62_student_context($classId, $key);
        if ($ctx === null) { $out['skipped']++; $out['reason'] = 'identity'; continue; }
        $stat = ev62_append((string)$ctx['id'], array_map(static fn(array $r): array => ['student_id' => $ctx['id']] + $r, $rows));
        $out['emitted'] += $stat['added'];
        $out['duplicates'] += $stat['duplicates'];
    }
    return $out;
}

/** ID hodnocení řízených v65 (mají v sidecaru publikovanou verzi) – starší adaptér je přeskočí, aby se důkaz nezdvojil. @return array<string,true> */
function proj65_managed_grade_ids(string $classId): array
{
    $ids = [];
    foreach (proj65_all() as $row) {
        if ((string)$row['class_id'] !== $classId || (string)$row['kind'] !== 'cat' || (array)($row['versions'] ?? []) === []) continue;
        $ids[project_grade_record_id($classId, (string)$row['project_id'], (string)$row['target_type'], (string)$row['target_id'])] = true;
    }
    return $ids;
}

/** Kandidáti pro adaptér v62 (pull cesta, stejné ref i čas jako push → deduplikace). @return list<array<string,mixed>> */
function proj65_evidence_candidates(array $ctx): array
{
    if (!comp62_enabled_for_class((string)$ctx['class'])) return [];
    $out = [];
    foreach (proj65_all() as $row) {
        if ((string)$row['class_id'] !== (string)$ctx['class'] || !proj65_is_member($row, (string)$ctx['key'])) continue;
        foreach (array_values(array_filter((array)($row['versions'] ?? []), 'is_array')) as $version) {
            $scores = proj65_competency_scores($version);
            if ($scores === []) continue;
            $out[] = ['source' => 'project', 'ref' => proj65_evidence_ref($row, $version), 'tags' => [], 'direct' => $scores,
                'score' => round(array_sum($scores) / count($scores), 3), 'at' => (string)$version['published_at']];
        }
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Retence
// ---------------------------------------------------------------------------

/** Žáci ve stavu left/archived déle než COMP62_RETENTION_GRACE_DAYS: stu_id → klíče žáka (class:student:hash). @return array<string,list<string>> */
function proj65_retention_candidates(?int $now = null): array
{
    $now ??= time();
    $out = [];
    if (!function_exists('identity58_registry')) return $out;
    foreach (identity58_registry()['students'] as $id => $stu) {
        if (!is_array($stu) || !ev62_valid_id((string)$id) || !in_array((string)($stu['status'] ?? 'active'), ['left', 'archived'], true)) continue;
        $since = strtotime((string)($stu['archived_at'] ?? '')) ?: 0;
        if ($since <= 0 || $now - $since < COMP62_RETENTION_GRACE_DAYS * 86400) continue;
        $keys = array_values(array_filter(array_map('strval', (array)($stu['aliases'] ?? [])), static fn(string $a): bool => preg_match('/^class_[a-z0-9]+:student:[0-9a-f]{24}$/', $a) === 1));
        $out[(string)$id] = $keys;
    }
    return $out;
}

/**
 * Smaže osobní texty a reflexe odešlých žáků. Dry-run nic nemění.
 * @return array{students:int,portfolio:int,pitch:int,peer:int,diary:int,splits:int,dry_run:bool}
 */
function proj65_retention_purge(bool $dryRun, ?int $now = null): array
{
    $cand = proj65_retention_candidates($now);
    $res = ['students' => count($cand), 'portfolio' => 0, 'pitch' => 0, 'peer' => 0, 'diary' => 0, 'splits' => 0, 'dry_run' => $dryRun];
    if ($cand === []) return $res;
    $keys = array_values(array_unique(array_merge(...array_values($cand))));
    foreach (array_keys($cand) as $id) {
        $file = STORAGE_DIR . '/portfolio_v65/' . $id . '.json.php';
        if (!is_file($file)) continue;
        $res['portfolio']++;
        if (!$dryRun) ev62_delete_locked($file);
    }
    $scrub = static function (array $all) use ($keys, &$res): array {
        foreach ($all as $ref => $row) {
            if (!is_array($row)) continue;
            $mine = array_intersect(proj65_member_keys($row), $keys) !== [] || in_array((string)($row['owner_key'] ?? ''), $keys, true);
            if ($mine && ((string)($row['pitch'] ?? '') !== '' || (string)($row['submission']['note'] ?? '') !== '')) {
                $res['pitch']++;
                $all[$ref]['pitch'] = '';
                if (is_array($row['submission'] ?? null)) $all[$ref]['submission']['note'] = '';
            }
            foreach ((array)($row['splits'] ?? []) as $giver => $given) {
                if (in_array((string)$giver, $keys, true)) { unset($all[$ref]['splits'][$giver]); $res['splits']++; continue; }
                foreach (array_intersect(array_keys((array)$given), $keys) as $k) { unset($all[$ref]['splits'][$giver][$k]); }
            }
        }
        return $all;
    };
    $cycle = proj65_path('cycle');
    if ($dryRun) $scrub(storage_read($cycle, false)); else storage_update($cycle, $scrub);
    $cycleRows = storage_read($cycle, false);
    $dropPeer = static function (array $peer) use ($keys, $cycleRows, &$res): array {
        foreach ($peer as $id => $r) {
            if (!is_array($r)) continue;
            $author = is_array($cycleRows[$r['ref'] ?? ''] ?? null) ? proj65_member_keys($cycleRows[$r['ref']]) : [];
            if (in_array((string)($r['reviewer_key'] ?? ''), $keys, true) || array_intersect($author, $keys) !== []) { unset($peer[$id]); $res['peer']++; }
        }
        return $peer;
    };
    $peerPath = proj65_path('peer');
    if ($dryRun) $dropPeer(storage_read($peerPath, false)); else storage_update($peerPath, $dropPeer);
    $res['diary'] = proj65_diary_purge($keys, $dryRun);
    return $res;
}

/** Přepíše měsíční soubory deníku bez záznamů zadaných žáků (pod výlučným zámkem, atomicky). Vrací počet odstraněných záznamů. */
function proj65_diary_purge(array $studentKeys, bool $dryRun): int
{
    $removed = 0;
    foreach (storage_stream_months('projects_v65_diary') as $month) {
        $file = storage_stream_file('projects_v65_diary', $month);
        $lock = storage_lock_exclusive($file);
        try {
            $raw = storage_read_file_raw($file);
            $rows = $raw === null ? [] : storage_parse_lines($raw, 'projects_v65_diary/' . $month);
            $keep = array_values(array_filter($rows, static fn(array $r): bool => !in_array((string)($r['student_key'] ?? ''), $studentKeys, true)));
            $dropped = count($rows) - count($keep);
            if ($dropped > 0 && !$dryRun) {
                $payload = STORAGE_GUARD_LINE . implode('', array_map('storage_encode_line', $keep));
                storage_atomic_replace($file, $payload);
            }
            $removed += $dropped;
        } finally {
            storage_unlock($lock, $file);
        }
        unset($GLOBALS['educanet_stream_cache'][storage_path_key($file)]);
    }
    return $removed;
}
