<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v64 · economy_v64.php – férová ekonomika her.
 *
 * Rozhodnutí školy: hry dávají jen XP (žádné body, žádná třetí měna); marketplace zůstává na bodech (pts53),
 * Kč jen u projektů. Všechny herní zdroje XP (aréna, CTF, týdenní hádanka, incidenty, robotí liga, týmové hry)
 * sdílejí JEDEN denní strop (eco64_caps()['xp_day'] = 60 XP/den na žáka). Strop se počítá a zapisuje
 * v jednom storage_update_many spolu s profilem (souběžné požadavky ho neobejdou).
 *
 * Úložiště:
 *   storage/economy_v64/daily.json.php       – {days:{YYYY-MM-DD:{sha1(třída|klíč):xp}}} (jen poslední dny, bez jmen)
 *   storage/economy_v64_ledger/YYYY-MM.jsonl.php – append-only deník {at,class_id,sid_hash,currency,source,event_key,requested,granted,capped}
 * Vypnutí: EDUCANET_ECONOMY_V64=0 → chování v63 (přímý learning_award_once, bez stropu a deníku).
 */

const ECO64_XP_DAY_CAP = 60;
const ECO64_DAILY_KEEP_DAYS = 7;
const ECO64_LEDGER_STREAM = 'economy_v64_ledger';

function eco64_enabled(): bool
{
    // Bez jádra úložiště a přihlášení (izolované audity starších vrstev) se chová jako v63: přímé learning_award_once.
    return (string)getenv('EDUCANET_ECONOMY_V64') !== '0' && function_exists('storage_update_many') && function_exists('auth_is_signed_in');
}

/** Denní stropy podle měny (jediná měna her je XP). */
function eco64_caps(): array
{
    return ['xp_day' => ECO64_XP_DAY_CAP];
}

function eco64_daily_path(): string
{
    return STORAGE_DIR . '/economy_v64/daily.json.php';
}

function eco64_sid_hash(string $classId, string $studentKey): string
{
    return sha1($classId . '|' . $studentKey);
}

/** Vypočte, kolik z požadovaného množství projde stropem (čistá funkce). */
function eco64_apply_cap(int $used, int $requested, int $cap): int
{
    return max(0, min($requested, $cap - $used));
}

/** Smaže staré dny z čítače (zachová poslední ECO64_DAILY_KEEP_DAYS). */
function eco64_prune_days(array $days, string $today): array
{
    krsort($days, SORT_STRING);
    $days = array_slice($days, 0, ECO64_DAILY_KEEP_DAYS, true);
    return $days;
}

/**
 * Připíše XP z herního zdroje se stropem a idempotencí přes $eventKey.
 * @return array{granted:int,recorded:bool,capped:bool} recorded = událost je nově zaznamenaná (dříve neexistovala)
 */
function eco64_grant_detail(string $classId, string $studentKey, string $currency, string $source, string $eventKey, int $amount, ?int $now = null): array
{
    $amount = max(0, $amount);
    $none = ['granted' => 0, 'recorded' => false, 'capped' => false];
    if ($studentKey === '' || $currency !== 'xp') return $none;
    if (!eco64_enabled()) {
        $ok = function_exists('learning_award_once') && learning_award_once($classId, $eventKey, $amount);
        return ['granted' => $ok ? $amount : 0, 'recorded' => $ok, 'capped' => false];
    }
    $now = $now ?? time();
    $day = date('Y-m-d', $now);
    $sid = eco64_sid_hash($classId, $studentKey);
    $cap = (int)eco64_caps()['xp_day'];
    $res = auth_is_signed_in() ? eco64_commit_signed($classId, $eventKey, $amount, $day, $sid, $cap)
        : eco64_commit_anonymous($classId, $eventKey, $amount, $day, $sid, $cap);
    if ($res['recorded']) eco64_ledger($classId, $sid, $source, $eventKey, $amount, $res['granted'], $now);
    return $res;
}

/** Zkratka: vrací připsané XP. */
function eco64_grant(string $classId, string $studentKey, string $currency, string $source, string $eventKey, int $amount, ?int $now = null): int
{
    return eco64_grant_detail($classId, $studentKey, $currency, $source, $eventKey, $amount, $now)['granted'];
}

/** Náhrada learning_award_once pro herní zdroje: true = událost nově zaznamenaná (stejná sémantika jako dřív). */
function eco64_award(string $classId, string $studentKey, string $source, string $eventKey, int $xp): bool
{
    return eco64_grant_detail($classId, $studentKey, 'xp', $source, $eventKey, $xp)['recorded'];
}

function eco64_commit_signed(string $classId, string $eventKey, int $amount, string $day, string $sid, int $cap): array
{
    $key = learning_profile_key($classId);
    $out = ['granted' => 0, 'recorded' => false, 'capped' => false];
    $paths = [learning_profiles_path(), eco64_daily_path()];
    $profilePath = $paths[0];
    $dailyPath = $paths[1];
    $saved = null;
    storage_update_many($paths, static function (array $data) use ($key, $eventKey, $amount, $day, $sid, $cap, $profilePath, $dailyPath, &$out, &$saved): array {
        $profiles = $data[$profilePath];
        $p = is_array($profiles[$key] ?? null) ? $profiles[$key] : learning_profile_default();
        $events = is_array($p['events'] ?? null) ? $p['events'] : [];
        if (isset($events[$eventKey])) return [];
        $daily = $data[$dailyPath];
        $days = is_array($daily['days'] ?? null) ? $daily['days'] : [];
        $used = (int)($days[$day][$sid] ?? 0);
        $granted = eco64_apply_cap($used, $amount, $cap);
        if ($granted === 0 && $amount > 0) {
            $out = ['granted' => 0, 'recorded' => false, 'capped' => true]; // strop vyčerpán: nic se nezapisuje (událost zůstane volná)
            return [];
        }
        $events[$eventKey] = ['xp' => $granted, 'at' => date(DATE_ATOM)];
        $p['events'] = $events;
        $p['xp'] = max(0, (int)($p['xp'] ?? 0) + $granted);
        $p['version'] = (int)($p['version'] ?? 0) + 1;
        $p['updated_at'] = date(DATE_ATOM);
        $profiles[$key] = $p;
        $days[$day][$sid] = $used + $granted;
        $daily['days'] = eco64_prune_days($days, $day);
        $saved = $p;
        $out = ['granted' => $granted, 'recorded' => true, 'capped' => $granted < $amount];
        return [$profilePath => $profiles, $dailyPath => $daily];
    });
    if (is_array($saved)) $_SESSION['learning_profiles'][$key] = learning_profile_remember($key, $saved);
    return $out;
}

/** Nepřihlášený (lokální/dev) profil žije v session: strop se rezervuje v čítači, XP zapíše learning_award_once. */
function eco64_commit_anonymous(string $classId, string $eventKey, int $amount, string $day, string $sid, int $cap): array
{
    $profile = learning_profile($classId);
    if (isset($profile['events'][$eventKey])) return ['granted' => 0, 'recorded' => false, 'capped' => false];
    $granted = 0;
    storage_update(eco64_daily_path(), static function (array $daily) use ($day, $sid, $amount, $cap, &$granted): array {
        $days = is_array($daily['days'] ?? null) ? $daily['days'] : [];
        $used = (int)($days[$day][$sid] ?? 0);
        $granted = eco64_apply_cap($used, $amount, $cap);
        if ($granted === 0) return $daily;
        $days[$day][$sid] = $used + $granted;
        $daily['days'] = eco64_prune_days($days, $day);
        return $daily;
    });
    if ($granted === 0 && $amount > 0) return ['granted' => 0, 'recorded' => false, 'capped' => true];
    $ok = learning_award_once($classId, $eventKey, $granted);
    return ['granted' => $ok ? $granted : 0, 'recorded' => $ok, 'capped' => $granted < $amount];
}

function eco64_ledger(string $classId, string $sid, string $source, string $eventKey, int $requested, int $granted, int $now): void
{
    try {
        storage_append_many(ECO64_LEDGER_STREAM, [[
            'at' => date(DATE_ATOM, $now), 'class_id' => $classId, 'sid_hash' => $sid, 'currency' => 'xp',
            'source' => preg_replace('/[^a-z0-9_:]/', '', strtolower($source)), 'event_key' => substr(preg_replace('/[^A-Za-z0-9_:\-]/', '', $eventKey), 0, 80),
            'requested' => $requested, 'granted' => $granted, 'capped' => $granted < $requested,
        ]], date('Y-m', $now));
    } catch (Throwable $e) {
        error_log('EDUCANET v64 deník ekonomiky: ' . get_class($e));
    }
}

/** Měsíc YYYY-MM nebo aktuální (neplatný vstup se nahradí). */
function eco64_month(?string $month): string
{
    return $month !== null && preg_match(STORAGE_MONTH_RE, $month) === 1 ? $month : date('Y-m');
}

function eco64_prev_month(string $month): string
{
    return date('Y-m', (int)strtotime($month . '-01 -1 month'));
}

function eco64_percentile(array $sorted, float $p): float
{
    $n = count($sorted);
    if ($n === 0) return 0.0;
    $i = (int)ceil($p * $n) - 1;
    return (float)$sorted[max(0, min($n - 1, $i))];
}

/** Souhrn měsíce jedné třídy: žáci, medián/p90 XP z her, podíl omezených událostí. */
function eco64_month_stats(string $classId, string $month): array
{
    $perStudent = [];
    $events = 0;
    $capped = 0;
    foreach (storage_stream_month_rows(ECO64_LEDGER_STREAM, $month) as $r) {
        if ((string)($r['class_id'] ?? '') !== $classId) continue;
        $events++;
        if (!empty($r['capped'])) $capped++;
        $sid = (string)($r['sid_hash'] ?? '');
        $perStudent[$sid] = ($perStudent[$sid] ?? 0) + (int)($r['granted'] ?? 0);
    }
    $vals = array_values($perStudent);
    sort($vals);
    $total = array_sum($vals);
    return ['students' => count($vals), 'events' => $events, 'capped_events' => $capped, 'capped_share' => $events > 0 ? round($capped / $events, 3) : 0.0,
        'median' => eco64_percentile($vals, 0.5), 'p90' => eco64_percentile($vals, 0.9), 'total' => $total];
}

/** Počet nákupů v marketplace (body pts53) ve třídě a měsíci – bez jmen. */
function eco64_purchases(string $classId, string $month): int
{
    $n = 0;
    foreach (storage_stream_month_rows('marketplace_v60_log', $month) as $r) {
        if ((string)($r['class_id'] ?? '') === $classId && (string)($r['type'] ?? '') === 'buy' && str_starts_with((string)($r['at'] ?? ''), $month)) $n += max(1, (int)($r['qty'] ?? 1));
    }
    return $n;
}

/** Měsíční report inflace XP pro učitele (třída + měsíc, změna proti předchozímu měsíci). */
function eco64_inflation_report(string $classId, ?string $month = null): array
{
    $month = eco64_month($month);
    $cur = eco64_month_stats($classId, $month);
    $prev = eco64_month_stats($classId, eco64_prev_month($month));
    $change = $prev['median'] > 0 ? round(($cur['median'] - $prev['median']) / $prev['median'], 3) : null;
    return ['month' => $month, 'class_id' => $classId, 'current' => $cur, 'previous' => $prev, 'median_change' => $change,
        'purchases' => eco64_purchases($classId, $month), 'cap' => (int)eco64_caps()['xp_day']];
}
