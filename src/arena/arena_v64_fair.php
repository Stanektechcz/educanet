<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v64 · arena_v64_fair.php – férovost her: ELO u soubojů 1v1, tři ligy, žebříček zlepšení, absolutní žebříček per akce.
 *
 * Rozhodnutí školy: start 1000, K=32 do 10 her pak 16 (pro souboj se bere průměr K obou hráčů → součet ELO se zachovává),
 * minimum 600, ligy bronz <950 / stříbro 950–1100 / zlato >1100, reset každý semestr (vzor robots58_semester).
 * Anti-farm: opakovaná dvojice v semestru ×0,5, více než 3 zápasy/den proti stejnému soupeři = 0. Doporučení soupeře ±150 (nezakazuje).
 * ELO ani liga nikdy nejdou do známek ani exportů známek. Ostatním žákům se ukazuje jen liga, ne číslo ELO.
 * Absolutní žebříček je výchozí vypnutý; učitel ho zapne u konkrétní akce (závod = hodnocení „žebříček“, CTF/robotí liga/týmová hra = přepínač).
 *
 * Úložiště: storage/arena_v64_ratings.json.php {ratings:{třída|klíč:{elo,games,semester,updated_at,hist}}, applied:{challenge_id:ts}, pairs:{pár:[ts…]}},
 *           storage/arena_v64_prefs.json.php {třída:{kind[:id]:true}}, storage/arena_v64_improve.json.php (cache po dnech).
 */

const FAIR64_START = 1000;
const FAIR64_MIN = 600;
const FAIR64_K_EARLY = 32;
const FAIR64_K_LATE = 16;
const FAIR64_EARLY_GAMES = 10;
const FAIR64_BRONZE_BELOW = 950;
const FAIR64_GOLD_ABOVE = 1100;
const FAIR64_RECOMMEND_RANGE = 150;
const FAIR64_MAX_PER_DAY = 3;
const FAIR64_BOARD_DAYS = 14;
const FAIR64_BOARD_TOP = 10;
const FAIR64_ABS_KINDS = ['ctf', 'robots', 'tg'];

function fair64_ratings_path(): string { return STORAGE_DIR . '/arena_v64_ratings.json.php'; }
function fair64_prefs_path(): string { return STORAGE_DIR . '/arena_v64_prefs.json.php'; }
function fair64_improve_path(): string { return STORAGE_DIR . '/arena_v64_improve.json.php'; }

/** Pololetí: září–leden = 1., únor–srpen = 2. (stejně jako robots58_semester). */
function fair64_semester(int $ts): string
{
    $month = (int)date('n', $ts);
    $year = (int)date('Y', $ts);
    if ($month >= 9) return $year . '-1';
    return ($year - 1) . ($month === 1 ? '-1' : '-2');
}

function fair64_elo_expected(float $a, float $b): float
{
    return 1.0 / (1.0 + 10 ** (($b - $a) / 400.0));
}

function fair64_k(int $games): int
{
    return $games < FAIR64_EARLY_GAMES ? FAIR64_K_EARLY : FAIR64_K_LATE;
}

/**
 * Nové ELO obou hráčů. $scoreA = 1 (A vyhrál) / 0 / 0,5; $weight 0–1 (anti-farm). K = průměr K obou hráčů → součet se zachovává (mimo dno 600).
 * @return array{0:int,1:int}
 */
function fair64_elo_update(int $ra, int $rb, int $gamesA, int $gamesB, float $scoreA, float $weight = 1.0): array
{
    $k = (fair64_k($gamesA) + fair64_k($gamesB)) / 2.0;
    $delta = $k * $weight * ($scoreA - fair64_elo_expected($ra, $rb));
    $newA = (int)round($ra + $delta);
    $newB = $ra + $rb - $newA;
    if ($newA < FAIR64_MIN) { $newA = FAIR64_MIN; }
    if ($newB < FAIR64_MIN) { $newB = FAIR64_MIN; }
    return [$newA, $newB];
}

/** @return 'bronze'|'silver'|'gold' */
function fair64_league(int $elo): string
{
    if ($elo < FAIR64_BRONZE_BELOW) return 'bronze';
    return $elo > FAIR64_GOLD_ABOVE ? 'gold' : 'silver';
}

function fair64_league_label(string $league): string
{
    return match ($league) { 'gold' => tr('Zlatá liga'), 'silver' => tr('Stříbrná liga'), default => tr('Bronzová liga') };
}

function fair64_row_default(int $now): array
{
    return ['elo' => FAIR64_START, 'games' => 0, 'semester' => fair64_semester($now), 'updated_at' => date(DATE_ATOM, $now), 'hist' => []];
}

/** Řádek hodnocení; z jiného semestru se líně resetuje (bez zápisu). */
function fair64_row(array $ratings, string $classId, string $key, int $now): array
{
    $row = $ratings[$classId . '|' . $key] ?? null;
    if (!is_array($row) || (string)($row['semester'] ?? '') !== fair64_semester($now)) return fair64_row_default($now);
    return $row + fair64_row_default($now);
}

function fair64_rating(string $classId, string $key, ?int $now = null): array
{
    $now ??= time();
    $row = fair64_row((array)(storage_read(fair64_ratings_path())['ratings'] ?? []), $classId, $key, $now);
    return ['elo' => (int)$row['elo'], 'games' => (int)$row['games'], 'league' => fair64_league((int)$row['elo'])];
}

/** Váha zápasu podle anti-farmu: 0 (4. a další za den), 0,5 (opakovaná dvojice v semestru), jinak 1. */
function fair64_pair_weight(array $stamps, int $now): float
{
    $today = date('Y-m-d', $now);
    $n = 0;
    foreach ($stamps as $ts) if (date('Y-m-d', (int)$ts) === $today) $n++;
    if ($n >= FAIR64_MAX_PER_DAY) return 0.0;
    return $stamps === [] ? 1.0 : 0.5;
}

/**
 * Zapíše výsledek souboje (idempotentně podle challenge_id). Vrací ['applied'=>bool,'weight'=>float].
 * Pád kdekoli jinde výsledek neztratí – volá se i opakovaně při dalším „sweep“.
 */
function fair64_apply_result(string $classId, string $challengeId, string $winnerKey, string $loserKey, ?int $now = null): array
{
    $now ??= time();
    $out = ['applied' => false, 'weight' => 0.0];
    if ($challengeId === '' || $winnerKey === '' || $loserKey === '' || $winnerKey === $loserKey) return $out;
    storage_update(fair64_ratings_path(), static function (array $d) use ($classId, $challengeId, $winnerKey, $loserKey, $now, &$out): array {
        $d['ratings'] = is_array($d['ratings'] ?? null) ? $d['ratings'] : [];
        $d['applied'] = is_array($d['applied'] ?? null) ? $d['applied'] : [];
        $d['pairs'] = is_array($d['pairs'] ?? null) ? $d['pairs'] : [];
        if (isset($d['applied'][$challengeId])) return $d;
        $pair = $classId . '|' . implode('~', (function (array $k): array { sort($k, SORT_STRING); return $k; })([$winnerKey, $loserKey]));
        $stamps = array_values(array_filter((array)($d['pairs'][$pair] ?? []), static fn($t): bool => fair64_semester((int)$t) === fair64_semester($now)));
        $weight = fair64_pair_weight($stamps, $now);
        $w = fair64_row($d['ratings'], $classId, $winnerKey, $now);
        $l = fair64_row($d['ratings'], $classId, $loserKey, $now);
        if ($weight > 0) {
            [$nw, $nl] = fair64_elo_update((int)$w['elo'], (int)$l['elo'], (int)$w['games'], (int)$l['games'], 1.0, $weight);
            foreach ([[$winnerKey, $w, $nw], [$loserKey, $l, $nl]] as [$key, $row, $elo]) {
                $row['elo'] = $elo;
                $row['games'] = (int)$row['games'] + 1;
                $row['updated_at'] = date(DATE_ATOM, $now);
                $row['hist'] = array_slice(array_merge((array)$row['hist'], [[$now, $elo]]), -40);
                $d['ratings'][$classId . '|' . $key] = $row;
            }
        }
        $stamps[] = $now;
        $d['pairs'][$pair] = array_slice($stamps, -30);
        $d['applied'][$challengeId] = $now;
        if (count($d['applied']) > 5000) $d['applied'] = array_slice($d['applied'], -4000, null, true);
        $out = ['applied' => $weight > 0, 'weight' => $weight];
        return $d;
    });
    return $out;
}

/** Doporučení soupeře: spolužáci do ±150 ELO (nejbližší první); ostatní se nezakazují. @param list<string> $candidateKeys */
function fair64_recommend(string $classId, string $myKey, array $candidateKeys, ?int $now = null): array
{
    $now ??= time();
    $ratings = (array)(storage_read(fair64_ratings_path())['ratings'] ?? []);
    $mine = (int)fair64_row($ratings, $classId, $myKey, $now)['elo'];
    $near = [];
    foreach ($candidateKeys as $key) {
        if ($key === $myKey) continue;
        $diff = abs((int)fair64_row($ratings, $classId, $key, $now)['elo'] - $mine);
        if ($diff <= FAIR64_RECOMMEND_RANGE) $near[$key] = $diff;
    }
    asort($near);
    return array_keys($near);
}

// ---------------------------------------------------------------------------
// Absolutní žebříček: výchozí vypnutý, učitel zapíná u konkrétní akce
// ---------------------------------------------------------------------------

function fair64_pref_key(string $kind, string $id): string
{
    return $kind . ($id !== '' ? ':' . $id : '');
}

/** @param string $kind race|ctf|robots|tg; race se řídí hodnocením závodu („žebříček“), ostatní přepínačem učitele. */
function fair64_absolute_board_enabled(string $kind, string $classId, string $id = ''): bool
{
    if ($kind === 'race') {
        $race = function_exists('arena57_race') ? arena57_race($id) : null;
        return is_array($race) && (string)($race['settings']['rating'] ?? 'osobni_rekord') === 'zebricek';
    }
    if (!in_array($kind, FAIR64_ABS_KINDS, true)) return false;
    if (isset($GLOBALS['fair64_prefs_override'])) return !empty($GLOBALS['fair64_prefs_override'][$classId][fair64_pref_key($kind, $id)]); // jen izolované audity bez jádra úložiště
    if (!function_exists('storage_read')) return false;
    $prefs = storage_read(fair64_prefs_path());
    return !empty($prefs[$classId][fair64_pref_key($kind, $id)]);
}

function fair64_absolute_board_set(string $classId, string $kind, string $id, bool $on): bool
{
    if (!in_array($kind, FAIR64_ABS_KINDS, true) || preg_match('/^[A-Za-z0-9_-]{0,40}$/', $id) !== 1) return false;
    storage_update(fair64_prefs_path(), static function (array $d) use ($classId, $kind, $id, $on): array {
        $key = fair64_pref_key($kind, $id);
        if ($on) { $d[$classId][$key] = true; } else { unset($d[$classId][$key]); }
        return $d;
    });
    return true;
}

/** Bez zapnutého absolutního žebříčku zůstane v řádcích jen žák sám (vlastní pozice). */
function fair64_only_me(array $rows): array
{
    return array_values(array_filter($rows, static fn($r): bool => is_array($r) && !empty($r['me'])));
}

// ---------------------------------------------------------------------------
// Žebříček „osobní zlepšení“ (jen kladné, iniciály, cache po dnech)
// ---------------------------------------------------------------------------

/** Součet skóre zvládnutí kompetencí (0 pro neověřené). Čistá funkce nad výstupem m62_compute. */
function fair64_mastery_total(array $map): float
{
    $t = 0.0;
    foreach ($map as $row) $t += is_array($row) && is_numeric($row['score'] ?? null) ? (float)$row['score'] : 0.0;
    return $t;
}

/** ELO před $days dny z historie (poslední hodnota ≤ hranice, jinak start). */
function fair64_elo_at(array $hist, int $ts): int
{
    $v = FAIR64_START;
    foreach ($hist as $h) if (is_array($h) && (int)$h[0] <= $ts) $v = (int)$h[1];
    return $v;
}

/** Skóre zlepšení za okno: kladný přírůstek ELO + 100× přírůstek zvládnutí; záporné a nulové se nezobrazí. */
function fair64_improvement_score(int $eloGain, float $masteryGain): int
{
    return (int)round(max(0, $eloGain) + 100 * max(0.0, $masteryGain));
}

function fair64_mastery_gain(string $classId, string $key, int $now): float
{
    if (!function_exists('m62_compute') || !function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId)) return 0.0;
    $subject = comp62_subject_for_class($classId);
    $student = project_students_for_class($classId)[$key] ?? null;
    $id = is_array($student) ? identity58_id_for_student($classId, (string)$student['label']) : null;
    if ($subject === null || $id === null || !ev62_valid_id($id)) return 0.0;
    $ids = array_keys(comp62_competencies($subject));
    $rows = ev62_read($id);
    $then = $now - FAIR64_BOARD_DAYS * 86400;
    $old = array_values(array_filter($rows, static fn($r): bool => is_array($r) && (int)strtotime((string)($r['at'] ?? '')) <= $then));
    return fair64_mastery_total(m62_compute($rows, $now, $ids)) - fair64_mastery_total(m62_compute($old, $then, $ids));
}

/** Žebříček zlepšení třídy (cache po dnech). Řádky {name,league,me}: bez čísel, jen kladné zlepšení. */
function fair64_improvement_board(string $classId, string $viewerKey, ?int $now = null): array
{
    $now ??= time();
    $day = date('Y-m-d', $now);
    $cache = storage_read(fair64_improve_path(), false);
    $rows = ($cache[$classId]['day'] ?? '') === $day && is_array($cache[$classId]['rows'] ?? null) ? $cache[$classId]['rows'] : null;
    if ($rows === null) {
        $rows = fair64_improvement_compute($classId, $now);
        if (!storage_readonly()) storage_update(fair64_improve_path(), static function (array $d) use ($classId, $day, $rows): array { $d[$classId] = ['day' => $day, 'rows' => $rows]; return $d; });
    }
    $roster = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    $out = [];
    foreach (array_slice($rows, 0, FAIR64_BOARD_TOP) as $r) {
        $key = (string)$r['key'];
        $label = (string)($roster[$key]['label'] ?? '');
        $out[] = ['name' => $key === $viewerKey ? tr('Ty') : (function_exists('lab57_display_name') ? lab57_display_name($label) : '?'), 'league' => (string)$r['league'], 'me' => $key === $viewerKey];
    }
    return $out;
}

function fair64_improvement_compute(string $classId, int $now): array
{
    $ratings = (array)(storage_read(fair64_ratings_path())['ratings'] ?? []);
    $roster = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    $then = $now - FAIR64_BOARD_DAYS * 86400;
    $rows = [];
    foreach (array_keys($roster) as $key) {
        $row = fair64_row($ratings, $classId, (string)$key, $now);
        $eloGain = (int)$row['elo'] - fair64_elo_at((array)$row['hist'], $then);
        $score = fair64_improvement_score($eloGain, fair64_mastery_gain($classId, (string)$key, $now));
        if ($score > 0) $rows[] = ['key' => (string)$key, 'score' => $score, 'league' => fair64_league((int)$row['elo'])];
    }
    usort($rows, static fn(array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp(sha1($a['key']), sha1($b['key'])));
    return $rows;
}

/** Hook z arena60_sweep_results: výsledek dokončené výzvy → ELO. Nikdy nehází výjimku. */
function fair64_record_challenge(string $classId, array $challenge, int $now): void
{
    try {
        $winner = (string)($challenge['winner_key'] ?? '');
        $from = (string)($challenge['from_key'] ?? '');
        $to = (string)($challenge['to_key'] ?? '');
        $loser = $winner === $from ? $to : $from;
        fair64_apply_result($classId, (string)($challenge['id'] ?? ''), $winner, $loser, $now);
    } catch (Throwable $e) {
        error_log('EDUCANET v64 ELO: ' . get_class($e));
    }
}
