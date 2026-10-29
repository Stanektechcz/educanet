<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Aréna – Týdenní hádanka (ARN-01): logika (bez HTML).
 *
 * Jedna golfová úloha z banky (linux_v58_levels_weekly.php) pro CELOU ŠKOLU, rotující každé pondělí
 * 0:00 (Europe/Prague) podle ISO týdne – klíč týdne má tvar RRRRtTT (např. 2026t39). Výběr je deterministický
 * (stabilní hash klíče týdne), učitel ho smí přeskočit/vybrat ručně (arena58_weekly_set_override).
 *
 * Body a čas žák dostává jako obvykle přes lab57_session() (kontext weekly:<týden>, viz linux_v58_levels_weekly.php);
 * žebříček ale řadí podle DÉLKY řešení (golf_len, pak čas) – to počítáme tady z lab57_events('weekly:<týden>').
 * Řešení (skutečný text příkazu spolužáků) je vidět až po uzávěrce týdne (nebo po ručním odhalení učitelem);
 * do té doby vrací žebříček jen jména + délku/čas, bez příkazu. Bezpečnostní invariant Labu platí beze změny –
 * tenhle soubor nic nespouští, jen čte uložené události a data.
 */

require_once __DIR__ . '/arena_v57.php';
require_once __DIR__ . '/economy_v64.php';

const ARENA58_WEEKLY_TZ = 'Europe/Prague';
const ARENA58_WEEKLY_ID_RE = '/^(\d{4})t(\d{2})$/';
const ARENA58_WEEKLY_BOARD_TOP = 10;
const ARENA58_WEEKLY_HISTORY = 8;
const ARENA58_WEEKLY_XP_PARTICIPATION = 15;
const ARENA58_WEEKLY_XP_PODIUM = [1 => 25, 2 => 15, 3 => 8];

// ---------------------------------------------------------------------------
// Úložiště (rotace, ruční výběr učitele, odhalení řešení)
// ---------------------------------------------------------------------------

function arena58_weekly_path(): string
{
    return lab57_storage_dir() . '/arena_v58_weekly.json.php';
}

function arena58_weekly_normalize(array $data): array
{
    $data['weeks'] = is_array($data['weeks'] ?? null) ? $data['weeks'] : [];
    return $data;
}

function arena58_weekly_data(): array
{
    return arena58_weekly_normalize(lab57_store_read(arena58_weekly_path()));
}

function arena58_weekly_update(callable $mutate): array
{
    return lab57_store_update(arena58_weekly_path(), static fn(array $d): array => arena58_weekly_normalize($mutate(arena58_weekly_normalize($d))));
}

// ---------------------------------------------------------------------------
// Rotace podle ISO týdne (pondělí 0:00 Europe/Prague)
// ---------------------------------------------------------------------------

function arena58_weekly_tz(): DateTimeZone
{
    return new DateTimeZone(ARENA58_WEEKLY_TZ);
}

function arena58_weekly_key(int $ts): string
{
    $dt = (new DateTimeImmutable('@' . $ts))->setTimezone(arena58_weekly_tz());
    return $dt->format('o') . 't' . $dt->format('W');
}

function arena58_weekly_valid_key(string $key): bool
{
    return preg_match(ARENA58_WEEKLY_ID_RE, $key) === 1;
}

/** @return array{start:int,end:int}|null Začátek (pondělí 0:00) a konec (další pondělí 0:00) daného ISO týdne. */
function arena58_weekly_bounds(string $key): ?array
{
    if (preg_match(ARENA58_WEEKLY_ID_RE, $key, $m) !== 1) return null;
    try {
        $start = new DateTimeImmutable(sprintf('%04d-W%02d-1', (int)$m[1], (int)$m[2]), arena58_weekly_tz());
    } catch (Throwable) {
        return null;
    }
    // Obrana proti neexistujícímu 53. týdnu apod. – PHP by datum „přetekl“ do jiného ISO týdne/roku.
    if ($start->format('o') !== $m[1] || $start->format('W') !== $m[2]) return null;
    return ['start' => $start->getTimestamp(), 'end' => $start->modify('+7 days')->getTimestamp()];
}

function arena58_weekly_current_key(int $now): string
{
    return arena58_weekly_key($now);
}

/** @return array<string,array> banka úloh podle id (viz linux_v58_levels_weekly.php) */
function arena58_weekly_bank_by_id(): array
{
    $out = [];
    foreach (lab58_weekly_bank() as $level) $out[(string)$level['id']] = $level;
    return $out;
}

/** Deterministický, ale nepředvídatelný výběr z banky pro daný týden (stabilní hash klíče, ne pořadí). */
function arena58_weekly_default_level_id(string $key): string
{
    $bank = array_keys(arena58_weekly_bank_by_id());
    if ($bank === []) return '';
    $idx = (int)(hexdec(substr(sha1('weekly-rotation|' . $key), 0, 8)) % count($bank));
    return $bank[$idx];
}

/** @return array{key:string,level:string,start:int,end:int,override:bool,revealed_early:bool}|null */
function arena58_weekly_week(string $key): ?array
{
    $bounds = arena58_weekly_bounds($key);
    if ($bounds === null) return null;
    $row = arena58_weekly_data()['weeks'][$key] ?? null;
    $bank = arena58_weekly_bank_by_id();
    $levelId = is_array($row) && is_string($row['level'] ?? null) && isset($bank[$row['level']]) ? $row['level'] : arena58_weekly_default_level_id($key);
    if (!isset($bank[$levelId])) return null;
    return [
        'key' => $key, 'level' => $levelId, 'start' => $bounds['start'], 'end' => $bounds['end'],
        'override' => is_array($row) && !empty($row['override']), 'revealed_early' => is_array($row) && !empty($row['revealed_early']),
    ];
}

function arena58_weekly_reveal_allowed(array $week, int $now): bool
{
    return $now >= (int)$week['end'] || !empty($week['revealed_early']);
}

/** Český plurál (1/2-4/5+): arena58_plural(3, 'den', 'dny', 'dní') → 'dny'. Sdílí ho views a záznam závodu. */
function arena58_plural(int $n, string $one, string $few, string $many): string
{
    if ($n === 1) return $one;
    return $n >= 2 && $n <= 4 ? $few : $many;
}

/** Odpočet do uzávěrky ve čtivém tvaru („3 dny 4 h“, „5 h 12 min“, „12 min“). */
function arena58_weekly_countdown_text(int $secs): string
{
    $secs = max(0, $secs);
    $days = intdiv($secs, 86400);
    $hours = intdiv($secs % 86400, 3600);
    $mins = intdiv($secs % 3600, 60);
    if ($days > 0) return trn(['one' => '{n} den {h} h', 'few' => '{n} dny {h} h', 'other' => '{n} dní {h} h'], $days, ['h' => $hours]);
    if ($hours > 0) return tr('{h} h {m} min', ['h' => $hours, 'm' => $mins]);
    return tr('{m} min', ['m' => $mins]);
}

// ---------------------------------------------------------------------------
// Kontext `weekly:<týden>` – callbacky volané z linux_v58_levels_weekly.php
// ---------------------------------------------------------------------------

function arena58_weekly_access(array $level, array $ctx): ?string
{
    $week = arena58_weekly_week((string)$ctx['id']);
    if ($week === null) return tr('Tahle hádanka neexistuje.');
    if ((int)$ctx['now'] < $week['start']) return tr('Tahle hádanka ještě nezačala.');
    if ($week['level'] !== (string)$level['id']) return tr('Tahle úloha do týdenní hádanky nepatří.');
    return null;
}

/** @return list<string> */
function arena58_weekly_levels_for(array $ctx): array
{
    $week = arena58_weekly_week((string)$ctx['id']);
    return $week === null ? [] : [$week['level']];
}

function arena58_weekly_info(array $ctx): ?array
{
    $week = arena58_weekly_week((string)$ctx['id']);
    if ($week === null) return null;
    return ['id' => $week['key'], 'title' => tr('Týdenní hádanka'), 'ends_at' => date(DATE_ATOM, $week['end']), 'settings' => ['hints' => true, 'hint_penalty' => 0.1]];
}

function arena58_weekly_on_complete(array $ctx, array $level, array $event): void
{
    // Žebříček i XP se počítají líně z lab57_events() při dotazu (arena58_weekly_board/claim_xp) – tady není co ukládat navíc.
}

// ---------------------------------------------------------------------------
// Řešení a žebříčky (z lab57_events(weekly:<týden>))
// ---------------------------------------------------------------------------

/** Nejkratší (pak nejrychlejší, pak nejdřívější) řešení každého žáka v daném týdnu do zadané uzávěrky. */
function arena58_weekly_solves(string $weekKey, string $levelId, int $cutoff): array
{
    $best = [];
    foreach (lab57_events('weekly:' . $weekKey) as $ev) {
        if (!is_array($ev) || ($ev['kind'] ?? '') !== 'solve' || (string)($ev['level'] ?? '') !== $levelId) continue;
        $at = arena57_ts($ev['at'] ?? null);
        $len = $ev['golf_len'] ?? null;
        $key = (string)($ev['student_key'] ?? '');
        if ($at === null || $at > $cutoff || !is_int($len) || $key === '') continue;
        $cand = ['student_key' => $key, 'class_id' => (string)($ev['class_id'] ?? ''), 'label' => (string)($ev['label'] ?? ''), 'len' => $len, 'secs' => (int)($ev['secs'] ?? 0), 'at' => $at, 'cmd' => (string)($ev['golf_cmd'] ?? '')];
        $cur = $best[$key] ?? null;
        if ($cur === null || ([$cand['len'], $cand['secs'], $cand['at']] <=> [$cur['len'], $cur['secs'], $cur['at']]) < 0) $best[$key] = $cand;
    }
    return array_values($best);
}

function arena58_weekly_attempts(string $weekKey, string $levelId, string $studentKey): int
{
    $n = 0;
    foreach (lab57_events('weekly:' . $weekKey) as $ev) {
        if (is_array($ev) && ($ev['kind'] ?? '') === 'solve' && (string)($ev['level'] ?? '') === $levelId && (string)($ev['student_key'] ?? '') === $studentKey) $n++;
    }
    return $n;
}

/** Pořadí: kratší řešení vyhrává, při shodě rychlejší, pak dřívější. */
function arena58_weekly_rank(array $rows): array
{
    usort($rows, static fn(array $a, array $b): int => [$a['len'], $a['secs'], $a['at']] <=> [$b['len'], $b['secs'], $b['at']]);
    foreach ($rows as $i => $row) $rows[$i]['rank'] = $i + 1;
    return $rows;
}

/** @param list<array> $solves @return list<array> seřazené řádky ve zvoleném rozsahu (třída, nebo null = celá škola) */
function arena58_weekly_scope_rows(array $solves, ?string $classId): array
{
    $filtered = $classId === null ? $solves : array_values(array_filter($solves, static fn(array $s): bool => $s['class_id'] === $classId));
    return arena58_weekly_rank($filtered);
}

/** Jméno podle aktuální soupisky třídy (pád na jméno z události, když žák už není na soupisce). */
function arena58_weekly_label(string $classId, string $studentKey, string $fallback): string
{
    static $rosters = [];
    if (!array_key_exists($classId, $rosters)) $rosters[$classId] = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    return function_exists('arena57_full_label') ? arena57_full_label($studentKey, $fallback, $rosters[$classId]) : ($fallback !== '' ? $fallback : tr('Žák'));
}

/** Veřejné řádky žebříčku pro žáky: vždy jen iniciály, cizí řešení (cmd) jen po odemčení. */
function arena58_weekly_public_rows(array $ranked, string $viewerKey, bool $unlocked, int $limit = ARENA58_WEEKLY_BOARD_TOP): array
{
    $rows = [];
    foreach ($ranked as $row) {
        $isMe = $row['student_key'] === $viewerKey;
        if (count($rows) >= $limit && !$isMe) continue;
        $rows[] = [
            'rank' => $row['rank'], 'name' => lab57_display_name($row['label'] !== '' ? $row['label'] : tr('Žák')),
            'len' => $row['len'], 'secs' => $row['secs'], 'me' => $isMe, 'cmd' => ($unlocked || $isMe) && $row['cmd'] !== '' ? $row['cmd'] : null,
        ];
    }
    return $rows;
}

/** Posledních N týdnů (bez aktuálního, nedokončeného) – vlastní výsledek žáka pro pocit pokroku (osobní rekord). */
function arena58_weekly_history(string $classId, string $studentKey, int $now, int $limit = ARENA58_WEEKLY_HISTORY): array
{
    $out = [];
    $key = arena58_weekly_current_key($now);
    for ($i = 0; $i <= $limit; $i++) {
        $week = arena58_weekly_week($key);
        if ($week === null) break;
        if ($i > 0) {
            $level = lab57_level($week['level']);
            $solves = arena58_weekly_solves($key, $week['level'], $week['end']);
            $mine = null;
            foreach ($solves as $s) if ($s['student_key'] === $studentKey) $mine = $s;
            $ranked = arena58_weekly_scope_rows($solves, $classId);
            $rank = null;
            foreach ($ranked as $r) if ($r['student_key'] === $studentKey) $rank = $r['rank'];
            $out[] = ['week' => $key, 'title' => (string)($level['title'] ?? $week['level']), 'played' => $mine !== null, 'len' => $mine['len'] ?? null, 'rank_class' => $rank];
        }
        $bounds = arena58_weekly_bounds($key);
        if ($bounds === null) break;
        $key = arena58_weekly_key($bounds['start'] - 1);
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Board (op=board) – volá lab_v57_api.php přes registrovaný kontext
// ---------------------------------------------------------------------------

function arena58_weekly_board(array $ctx): array
{
    $now = (int)($ctx['now'] ?? arena57_now());
    $weekKey = (string)($ctx['id'] ?? '');
    $week = arena58_weekly_week($weekKey);
    if ($week === null) return ['error' => tr('Hádanka nebyla nalezena.')];
    $level = lab57_level($week['level']);
    $classId = (string)($ctx['class'] ?? '');
    $viewerKey = (string)($ctx['student'] ?? '');
    $unlocked = arena58_weekly_reveal_allowed($week, $now);
    $solves = arena58_weekly_solves($weekKey, $week['level'], min($now, $week['end']));
    foreach ($solves as $i => $s) $solves[$i]['label'] = arena58_weekly_label($s['class_id'], $s['student_key'], $s['label']);

    $classRanked = arena58_weekly_scope_rows($solves, $classId);
    $schoolRanked = arena58_weekly_scope_rows($solves, null);
    $find = static function (array $ranked, string $key): ?array {
        foreach ($ranked as $r) if ($r['student_key'] === $key) return $r;
        return null;
    };
    $myClass = $find($classRanked, $viewerKey);
    $mySchool = $find($schoolRanked, $viewerKey);

    return [
        'week' => $weekKey, 'title' => (string)($level['title'] ?? ''), 'task' => (string)($level['task'] ?? ''),
        'status' => $now >= $week['end'] ? 'closed' : 'open', 'starts_at' => date(DATE_ATOM, $week['start']), 'ends_at' => date(DATE_ATOM, $week['end']), 'now' => $now,
        'remaining' => max(0, $week['end'] - $now),
        'me' => [
            'solved' => $myClass !== null, 'len' => $myClass['len'] ?? null, 'secs' => $myClass['secs'] ?? null,
            'attempts' => arena58_weekly_attempts($weekKey, $week['level'], $viewerKey),
            'rank_class' => $myClass['rank'] ?? null, 'rank_school' => $mySchool['rank'] ?? null,
        ],
        'class' => ['rows' => arena58_weekly_public_rows($classRanked, $viewerKey, $unlocked), 'participants' => count($classRanked)],
        'school' => ['rows' => arena58_weekly_public_rows($schoolRanked, $viewerKey, $unlocked), 'participants' => count($schoolRanked)],
        'solutions_unlocked' => $unlocked,
        'history' => arena58_weekly_history($classId, $viewerKey, $now),
    ];
}

// ---------------------------------------------------------------------------
// XP po uzávěrce (vyzvednutí žákem, stejný vzor jako arena57_award_finished_xp)
// ---------------------------------------------------------------------------

function arena58_weekly_took_part(string $weekKey, string $levelId, string $studentKey, int $cutoff): bool
{
    foreach (lab57_events('weekly:' . $weekKey) as $ev) {
        if (!is_array($ev) || (string)($ev['student_key'] ?? '') !== $studentKey || (string)($ev['level'] ?? '') !== $levelId) continue;
        $at = arena57_ts($ev['at'] ?? null);
        if ($at !== null && $at <= $cutoff) return true;
    }
    return false;
}

/** Projde uzavřené týdny (dosud nevyzvednuté v téhle relaci) a připíše XP za účast/umístění ve třídě. */
function arena58_weekly_claim_xp(string $classId, string $studentKey): void
{
    if ($studentKey === '' || !function_exists('learning_award_once')) return;
    $now = arena57_now();
    $sessionOk = session_status() === PHP_SESSION_ACTIVE;
    $done = $sessionOk && is_array($_SESSION['arena58_weekly_xp'] ?? null) ? $_SESSION['arena58_weekly_xp'] : [];
    try {
        $key = arena58_weekly_current_key($now);
        for ($i = 0; $i < ARENA58_WEEKLY_HISTORY; $i++) {
            $week = arena58_weekly_week($key);
            if ($week === null) break;
            if ($now >= $week['end'] && !isset($done[$key])) {
                if (arena58_weekly_took_part($key, $week['level'], $studentKey, $week['end'])) {
                    $ranked = arena58_weekly_scope_rows(arena58_weekly_solves($key, $week['level'], $week['end']), $classId);
                    $rank = null;
                    foreach ($ranked as $r) if ($r['student_key'] === $studentKey) $rank = $r['rank'];
                    eco64_award($classId, $studentKey, 'weekly58', 'v58:weekly:' . $key, ARENA58_WEEKLY_XP_PARTICIPATION + (ARENA58_WEEKLY_XP_PODIUM[(int)$rank] ?? 0));
                }
                $done[$key] = 1;
            }
            $bounds = arena58_weekly_bounds($key);
            if ($bounds === null) break;
            $key = arena58_weekly_key($bounds['start'] - 1);
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v58 týdenní hádanka XP: ' . $e->getMessage());
    }
    if ($sessionOk) $_SESSION['arena58_weekly_xp'] = $done;
}

// ---------------------------------------------------------------------------
// Učitel: přehled, přeskočení/vlastní výběr, odhalení
// ---------------------------------------------------------------------------

/** Živá data pro učitele (i před uzávěrkou – učitel smí vidět postup a případně poradit). */
function arena58_weekly_teacher_data(int $now): array
{
    $key = arena58_weekly_current_key($now);
    $week = arena58_weekly_week($key);
    if ($week === null) return ['week' => $key, 'level' => null];
    $level = lab57_level($week['level']);
    $solves = arena58_weekly_solves($key, $week['level'], $now);
    $ranked = arena58_weekly_rank($solves);
    foreach ($ranked as $i => $row) $ranked[$i]['label'] = arena58_weekly_label($row['class_id'], $row['student_key'], $row['label']);
    $byClass = [];
    foreach ($ranked as $row) $byClass[$row['class_id']][] = $row;
    $top = array_slice($ranked, 0, 10);
    if (function_exists('teacher59_can_class')) {
        $byClass = array_filter($byClass, static fn(string $classId): bool => teacher59_can_class($classId), ARRAY_FILTER_USE_KEY);
        $top = array_values(array_filter($top, static fn(array $row): bool => teacher59_can_class((string)$row['class_id'])));
        if (function_exists('teacher59_declare_filtered')) teacher59_declare_filtered('arena58_weekly_scoped');
    }
    return [
        'week' => $key, 'level' => $level, 'start' => $week['start'], 'end' => $week['end'],
        'override' => $week['override'], 'revealed_early' => $week['revealed_early'],
        'participants' => count($ranked), 'median_len' => arena57_median(array_column($ranked, 'len')),
        'top' => $top, 'by_class' => $byClass, 'bank' => arena58_weekly_bank_by_id(),
    ];
}

function arena58_weekly_set_override(?string $weekKey, ?string $levelId, int $now): void
{
    $key = ($weekKey !== null && $weekKey !== '') ? $weekKey : arena58_weekly_current_key($now);
    if (!arena58_weekly_valid_key($key)) throw new RuntimeException('Neplatný týden.');
    if ($levelId !== null && $levelId !== '' && !isset(arena58_weekly_bank_by_id()[$levelId])) throw new RuntimeException('Tahle hádanka v bance neexistuje.');
    arena58_weekly_update(static function (array $d) use ($key, $levelId): array {
        if ($levelId === null || $levelId === '') { unset($d['weeks'][$key]); return $d; }
        $d['weeks'][$key] = ['level' => $levelId, 'override' => true, 'revealed_early' => !empty($d['weeks'][$key]['revealed_early'] ?? false)];
        return $d;
    });
}

function arena58_weekly_reroll(string $weekKey, int $now): string
{
    $bank = array_keys(arena58_weekly_bank_by_id());
    if (count($bank) < 2) throw new RuntimeException('V bance není dost hádanek na přeskočení.');
    $current = arena58_weekly_week($weekKey);
    $candidates = array_values(array_diff($bank, [(string)($current['level'] ?? '')]));
    $pick = (string)$candidates[random_int(0, count($candidates) - 1)];
    arena58_weekly_set_override($weekKey, $pick, $now);
    return $pick;
}

function arena58_weekly_set_revealed(string $weekKey, bool $revealed, int $now): void
{
    if (!arena58_weekly_valid_key($weekKey)) throw new RuntimeException('Neplatný týden.');
    arena58_weekly_update(static function (array $d) use ($weekKey, $revealed, $now): array {
        $row = (array)($d['weeks'][$weekKey] ?? []);
        $row['revealed_early'] = $revealed;
        $row['level'] ??= arena58_weekly_default_level_id($weekKey);
        $d['weeks'][$weekKey] = $row;
        return $d;
    });
}

/** GET teacher.php?tab=arena&rezim=hadanka&hadanka_poll=1 (přihlášení ověřuje teacher.php). */
function arena58_weekly_teacher_poll(): void
{
    try {
        $data = arena58_weekly_teacher_data(arena57_now());
    } catch (Throwable $e) {
        error_log('EDUCANET v58 týdenní hádanka poll: ' . $e->getMessage());
        arena57_json_out(['ok' => false, 'error' => 'Data hádanky se nepodařilo načíst.'], 500);
        return;
    }
    arena57_json_out(['ok' => true] + $data);
}

/** Učitel: POST akce (CSRF ověřil teacher.php). */
function arena58_weekly_teacher_handle_post(string $action, array $modules): void
{
    $classIds = array_map('strval', array_keys($modules));
    $classId = is_string($_POST['class_id'] ?? null) && in_array($_POST['class_id'], $classIds, true) ? (string)$_POST['class_id'] : (string)($classIds[0] ?? '');
    $now = arena57_now();
    $done = static function (string $message, string $cid, string $type = 'ok'): never {
        if (function_exists('teacher_flash')) teacher_flash($message, $type);
        if (function_exists('teacher_redirect')) teacher_redirect(['tab' => 'arena', 'rezim' => 'hadanka', 'class' => $cid]);
        exit;
    };
    switch ($action) {
        case 'arena58_weekly_override':
            $week = (string)($_POST['week'] ?? '');
            $level = (string)($_POST['level_id'] ?? '');
            arena58_weekly_set_override($week === '' ? null : $week, $level === '' ? null : $level, $now);
            $done($level === '' ? 'Hádanka se vrátila k výchozímu výběru.' : 'Hádanka pro tenhle týden byla změněna.', $classId);
        case 'arena58_weekly_reroll':
            arena58_weekly_reroll((string)($_POST['week'] ?? arena58_weekly_current_key($now)), $now);
            $done('Vybrána jiná hádanka z banky.', $classId);
        case 'arena58_weekly_reveal':
            arena58_weekly_set_revealed((string)($_POST['week'] ?? arena58_weekly_current_key($now)), (string)($_POST['revealed'] ?? '1') === '1', $now);
            $done('Řešení spolužáků je teď odemčené pro rozbor ve třídě.', $classId);
        default:
            throw new RuntimeException('Neznámá akce týdenní hádanky.');
    }
}
