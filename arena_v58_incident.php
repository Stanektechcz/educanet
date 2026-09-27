<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Aréna – Incidenty (ARN-03, inspirace SadServers).
 *
 * Učitel vyhlásí „incidentní směnu“ pro jednu třídu (okno v hodinách, výchozí 48 h). V ní si žák/tým může
 * kdykoli spustit libovolný z ≥ 6 scénářů; každý scénář má VLASTNÍ 15minutový odpočet od okamžiku VLASTNÍHO
 * spuštění (fér i pro pozdní příchozí – čas neběží od začátku směny, ale od kliknutí na „Spustit“).
 * Po technické opravě (nebo i po vypršení času) žák napíše krátký postmortem (příčina, oprava, prevence).
 * TEPRVE odevzdání postmortemu přidělí body – technické vyřešení samo o sobě body nedává (formativní důraz
 * na reflexi, ne jen na rychlost). Učitel vidí všechny postmortemy a může k nim napsat komentář.
 *
 * Úložiště: <storage>/arena_v58_incident.json.php = {sessions:[…]}. Pokusy (attempts) jsou vnořené v každé
 * směně: sessions[i].attempts[stateKey][scenarioId] = {started_at, paused_at, pause_accum, solved_at, hints,
 * postmortem:{cause,fix,prevention,submitted_at,points,teacher_note}}. Zápisy jen přes lab57_store_update.
 */

require_once __DIR__ . '/linux_v57_lab.php';
// arena57_roster()/arena57_snake_teams() jsou zamčené API (§6 V58_PLAN) pro sestavení týmů – načteno
// defenzivně (stejný vzor jako lab_v57_api.php/teacher.php), ať modul funguje i mimo běžné pořadí načítání.
if (is_file(__DIR__ . '/arena_v57.php')) require_once __DIR__ . '/arena_v57.php';

const ARENA58_INC_ID_RE = '/^[a-z0-9]{6,32}$/';
const ARENA58_INC_PACK = 'incident';
const ARENA58_INC_HIDDEN_CLASS = 'zzz_nikdy_trida_inc58';
const ARENA58_INC_MINUTES = 15;
const ARENA58_INC_SECONDS = ARENA58_INC_MINUTES * 60;
const ARENA58_INC_HOURS_MIN = 1;
const ARENA58_INC_HOURS_MAX = 336; // 14 dní
const ARENA58_INC_HOURS_DEFAULT = 48;
const ARENA58_INC_TEAM_MIN = 2;
const ARENA58_INC_TEAM_MAX = 4;
const ARENA58_INC_HINT_MIN = 0.1;
const ARENA58_INC_HINT_MAX = 0.5;
const ARENA58_INC_NAME_MODES = ['initials', 'full', 'anon'];
const ARENA58_INC_POSTMORTEM_MIN = 15; // znaků na pole, jen záchranná síť – hlavní kontrolu dělá cnt58_check_text
const ARENA58_INC_XP_PARTICIPATION = 15;
const ARENA58_INC_XP_SOLVED = 15;

// ---------------------------------------------------------------------------
// Úložiště a čas
// ---------------------------------------------------------------------------

function arena58_inc_path(): string
{
    return lab57_storage_dir() . '/arena_v58_incident.json.php';
}

function arena58_inc_now(): int
{
    return isset($GLOBALS['arena58_inc_now_override']) && is_int($GLOBALS['arena58_inc_now_override']) ? $GLOBALS['arena58_inc_now_override'] : time();
}

function arena58_inc_normalize(array $data): array
{
    $data['sessions'] = array_values(array_filter((array)($data['sessions'] ?? []), static fn($s): bool => is_array($s) && is_string($s['id'] ?? null)));
    return $data;
}

function arena58_inc_data(): array
{
    return arena58_inc_normalize(lab57_store_read(arena58_inc_path()));
}

function arena58_inc_update(callable $mutate): array
{
    return lab57_store_update(arena58_inc_path(), static fn(array $d): array => arena58_inc_normalize($mutate(arena58_inc_normalize($d))));
}

function arena58_inc_valid_id(string $id): bool
{
    return preg_match(ARENA58_INC_ID_RE, $id) === 1;
}

function arena58_inc_ts(mixed $iso): ?int
{
    if (!is_string($iso) || $iso === '') return null;
    $ts = strtotime($iso);
    return $ts === false ? null : $ts;
}

function arena58_inc_iso(?int $ts): ?string
{
    return $ts === null ? null : date(DATE_ATOM, $ts);
}

function arena58_inc_status(array $session, int $now): string
{
    $status = (string)($session['status'] ?? 'draft');
    if ($status === 'live') {
        $end = arena58_inc_ts($session['ends_at'] ?? null);
        if ($end !== null && $now >= $end) return 'finished';
    }
    return in_array($status, ['draft', 'live', 'finished'], true) ? $status : 'draft';
}

function arena58_inc_sessions(): array
{
    return arena58_inc_data()['sessions'];
}

function arena58_inc_session(string $id): ?array
{
    if (!arena58_inc_valid_id($id)) return null;
    foreach (arena58_inc_sessions() as $row) if ((string)$row['id'] === $id) return $row;
    return null;
}

/** @return list<array> směny třídy, nejnovější první */
function arena58_inc_sessions_for_class(string $classId): array
{
    $rows = array_values(array_filter(arena58_inc_sessions(), static fn(array $s): bool => (string)($s['class_id'] ?? '') === $classId));
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $rows;
}

function arena58_inc_live_for_class(string $classId): ?array
{
    $now = arena58_inc_now();
    $expired = false;
    foreach (arena58_inc_sessions() as $row) {
        if ((string)($row['class_id'] ?? '') === $classId && (string)($row['status'] ?? '') === 'live' && arena58_inc_status($row, $now) === 'finished') { $expired = true; break; }
    }
    if ($expired) {
        try {
            arena58_inc_update(static function (array $d) use ($now): array {
                foreach ($d['sessions'] as $i => $row) {
                    if ((string)($row['status'] ?? '') === 'live' && arena58_inc_status($row, $now) === 'finished') $d['sessions'][$i]['status'] = 'finished';
                }
                return $d;
            });
        } catch (Throwable $e) {
            error_log('EDUCANET v58 incidenty: ' . $e->getMessage());
        }
    }
    $rows = arena58_inc_sessions_for_class($classId);
    foreach ($rows as $row) if (arena58_inc_status($row, $now) === 'live') return $row;
    foreach ($rows as $row) if (arena58_inc_status($row, $now) === 'finished') return $row;
    return $rows[0] ?? null;
}

// ---------------------------------------------------------------------------
// Scénáře balíčku
// ---------------------------------------------------------------------------

function arena58_inc_levels(): array
{
    return function_exists('lab57_pack_levels') ? lab57_pack_levels(ARENA58_INC_PACK) : [];
}

function arena58_inc_all_level_ids(): array
{
    return array_map(static fn(array $l): string => (string)$l['id'], arena58_inc_levels());
}

function arena58_inc_level_ids(array $session): array
{
    return array_values(array_map('strval', (array)($session['scenario_ids'] ?? [])));
}

// ---------------------------------------------------------------------------
// Vytvoření a správa směny
// ---------------------------------------------------------------------------

function arena58_inc_parse_create(array $in, array $classIds): array
{
    $classId = is_string($in['class_id'] ?? null) ? $in['class_id'] : '';
    if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
    $scenarios = arena58_inc_all_level_ids();
    if ($scenarios === []) throw new RuntimeException('Balíček incidentů zatím nemá žádné scénáře.');
    $title = trim((string)preg_replace('/\s+/u', ' ', is_string($in['title'] ?? null) ? $in['title'] : '') ?? '');
    if ($title === '') $title = 'Incidentní směna';
    if (mb_strlen($title) > 80) throw new RuntimeException('Název může mít nejvýš 80 znaků.');
    $mode = (string)($in['mode'] ?? 'solo');
    if (!in_array($mode, ['solo', 'teams'], true)) throw new RuntimeException('Neplatný režim.');
    $teamSize = filter_var($in['team_size'] ?? 2, FILTER_VALIDATE_INT);
    if (!is_int($teamSize) || $teamSize < ARENA58_INC_TEAM_MIN || $teamSize > ARENA58_INC_TEAM_MAX) throw new RuntimeException('Velikost týmu musí být ' . ARENA58_INC_TEAM_MIN . '–' . ARENA58_INC_TEAM_MAX . '.');
    $hours = filter_var($in['duration_hours'] ?? ARENA58_INC_HOURS_DEFAULT, FILTER_VALIDATE_INT);
    if (!is_int($hours) || $hours < ARENA58_INC_HOURS_MIN || $hours > ARENA58_INC_HOURS_MAX) throw new RuntimeException('Délka směny musí být ' . ARENA58_INC_HOURS_MIN . '–' . ARENA58_INC_HOURS_MAX . ' hodin.');
    $penalty = filter_var($in['hint_penalty'] ?? 0.2, FILTER_VALIDATE_FLOAT);
    if (!is_float($penalty) || $penalty < ARENA58_INC_HINT_MIN - 1e-9 || $penalty > ARENA58_INC_HINT_MAX + 1e-9) throw new RuntimeException('Srážka za nápovědu musí být 10–50 %.');
    $names = (string)($in['names'] ?? 'initials');
    if (!in_array($names, ARENA58_INC_NAME_MODES, true)) throw new RuntimeException('Neplatné zobrazení jmen.');
    $flag = static fn(string $k): bool => in_array((string)($in[$k] ?? ''), ['1', 'on', 'true', 'yes'], true);
    return [
        'class_id' => $classId, 'title' => $title, 'scenario_ids' => $scenarios, 'mode' => $mode, 'team_size' => $teamSize, 'duration_hours' => $hours,
        'settings' => ['hints' => $flag('hints'), 'hint_penalty' => round($penalty, 2), 'names' => $names],
    ];
}

function arena58_inc_create_session(array $in, array $classIds, int $now): array
{
    $spec = arena58_inc_parse_create($in, $classIds);
    $session = [
        'id' => bin2hex(random_bytes(6)), 'class_id' => $spec['class_id'], 'title' => $spec['title'], 'scenario_ids' => $spec['scenario_ids'],
        'mode' => $spec['mode'], 'team_size' => $spec['team_size'], 'teams' => [], 'duration_hours' => $spec['duration_hours'], 'status' => 'draft',
        'created_at' => date(DATE_ATOM, $now), 'starts_at' => null, 'ends_at' => null, 'settings' => $spec['settings'], 'attempts' => [],
    ];
    arena58_inc_update(static function (array $d) use ($session): array {
        $d['sessions'][] = $session;
        return $d;
    });
    return $session;
}

function arena58_inc_draft_teams(array $session): array
{
    if (!function_exists('arena57_roster') || !function_exists('arena57_snake_teams')) return [];
    $classId = (string)$session['class_id'];
    $roster = arena57_roster($classId);
    if (count($roster) < 2) return [];
    $points = [];
    foreach (array_keys($roster) as $key) $points[(string)$key] = (int)(lab57_student_summary($classId, (string)$key)['points'] ?? 0);
    return arena57_snake_teams($points, (int)$session['team_size']);
}

function arena58_inc_start_session(string $id, int $now): array
{
    $session = arena58_inc_session($id);
    if ($session === null) throw new RuntimeException('Směna nebyla nalezena.');
    if (arena58_inc_status($session, $now) !== 'draft') throw new RuntimeException('Spustit jde jen připravovaná směna.');
    $teams = $session['mode'] === 'teams' ? arena58_inc_draft_teams($session) : [];
    if ($session['mode'] === 'teams' && $teams === []) throw new RuntimeException('Na týmy je na soupisce třídy málo žáků.');
    $result = null;
    arena58_inc_update(static function (array $d) use ($id, $now, $teams, &$result): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $id) continue;
            if (arena58_inc_status($row, $now) !== 'draft') throw new RuntimeException('Spustit jde jen připravovaná směna.');
            $row['status'] = 'live';
            $row['starts_at'] = date(DATE_ATOM, $now);
            $row['ends_at'] = date(DATE_ATOM, $now + 3600 * (int)$row['duration_hours']);
            $row['teams'] = $teams;
            $d['sessions'][$i] = $result = $row;
        }
        return $d;
    });
    if ($result === null) throw new RuntimeException('Směna nebyla nalezena.');
    return $result;
}

function arena58_inc_mutate(string $id, callable $fn): ?array
{
    if (!arena58_inc_valid_id($id)) throw new RuntimeException('Směna nebyla nalezena.');
    $found = false;
    $result = null;
    arena58_inc_update(static function (array $d) use ($id, $fn, &$found, &$result): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $id) continue;
            $found = true;
            $result = $fn($row);
            if ($result === null) unset($d['sessions'][$i]); else $d['sessions'][$i] = $result;
            break;
        }
        return $d;
    });
    if (!$found) throw new RuntimeException('Směna nebyla nalezena.');
    return $result;
}

function arena58_inc_stop_session(string $id, int $now): array
{
    return (array)arena58_inc_mutate($id, static function (array $row) use ($now): array {
        if (arena58_inc_status($row, $now) !== 'live') throw new RuntimeException('Ukončit jde jen běžící směna.');
        $row['status'] = 'finished';
        $row['ends_at'] = date(DATE_ATOM, $now);
        $row['stopped_early'] = true;
        return $row;
    });
}

function arena58_inc_extend_session(string $id, int $now, int $hours = 24): array
{
    return (array)arena58_inc_mutate($id, static function (array $row) use ($hours): array {
        if ((string)$row['status'] !== 'live') throw new RuntimeException('Prodloužit jde jen běžící směna.');
        if ((int)$row['duration_hours'] + $hours > ARENA58_INC_HOURS_MAX) throw new RuntimeException('Směnu už nejde dál prodlužovat.');
        $row['duration_hours'] = (int)$row['duration_hours'] + $hours;
        $row['ends_at'] = date(DATE_ATOM, (int)arena58_inc_ts($row['ends_at']) + 3600 * $hours);
        return $row;
    });
}

function arena58_inc_delete_session(string $id): void
{
    arena58_inc_mutate($id, static function (array $row): ?array {
        if ((string)$row['status'] === 'live') throw new RuntimeException('Běžící směnu nejde smazat – nejdřív ji ukonči.');
        return null;
    });
}

// ---------------------------------------------------------------------------
// Tým / klíč stavu
// ---------------------------------------------------------------------------

function arena58_inc_team_for(array $session, string $studentKey): ?array
{
    foreach ((array)($session['teams'] ?? []) as $team) {
        if (in_array($studentKey, (array)($team['members'] ?? []), true)) return $team;
    }
    return null;
}

function arena58_inc_state_key(array $ctx): string
{
    $session = arena58_inc_session((string)$ctx['id']);
    if ($session === null || (string)($session['mode'] ?? 'solo') !== 'teams') return (string)$ctx['student'];
    $team = arena58_inc_team_for($session, (string)$ctx['student']);
    return $team !== null ? 'inctym:' . $session['id'] . ':' . (string)$team['id'] : (string)$ctx['student'];
}

// ---------------------------------------------------------------------------
// Pokusy (start / pauza / pokračování / stav / postmortem)
// ---------------------------------------------------------------------------

function arena58_inc_attempt(array $session, string $stateKey, string $scenarioId): ?array
{
    $row = $session['attempts'][$stateKey][$scenarioId] ?? null;
    return is_array($row) ? $row : null;
}

/** Zbývající sekundy odpočtu; null = ještě nezačalo. Pauza čas zastaví (přístupnost). */
function arena58_inc_remaining(array $attempt, int $now): ?int
{
    $started = (int)($attempt['started_at'] ?? 0);
    if ($started <= 0) return null;
    if (!empty($attempt['solved_at'])) return max(0, ARENA58_INC_SECONDS - ((int)$attempt['solved_at'] - $started) + (int)($attempt['pause_accum'] ?? 0));
    $pausedAt = $attempt['paused_at'] ?? null;
    $reference = is_int($pausedAt) ? $pausedAt : $now;
    $elapsed = max(0, $reference - $started - (int)($attempt['pause_accum'] ?? 0));
    return max(0, ARENA58_INC_SECONDS - $elapsed);
}

function arena58_inc_expired(array $attempt, int $now): bool
{
    return arena58_inc_remaining($attempt, $now) === 0 && empty($attempt['solved_at']);
}

/** Založí (nebo vrátí existující) pokus. Idempotentní – druhé kliknutí na „Spustit“ čas neresetuje. */
function arena58_inc_start_attempt(string $sessionId, string $classId, string $stateKey, string $scenarioId, int $now): array
{
    $session = arena58_inc_session($sessionId);
    if ($session === null || (string)$session['class_id'] !== $classId) throw new RuntimeException(tr('Směna nebyla nalezena.'));
    if (arena58_inc_status($session, $now) !== 'live') throw new RuntimeException(tr('Směna právě neběží.'));
    if (!in_array($scenarioId, arena58_inc_level_ids($session), true)) throw new RuntimeException(tr('Neznámý scénář.'));
    $result = null;
    arena58_inc_update(static function (array $d) use ($sessionId, $now, $stateKey, $scenarioId, &$result): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $sessionId) continue;
            $attempt = (array)($row['attempts'][$stateKey][$scenarioId] ?? []);
            if (empty($attempt['started_at'])) $attempt = ['started_at' => $now, 'paused_at' => null, 'pause_accum' => 0, 'solved_at' => null, 'hints' => 0, 'postmortem' => null];
            $row['attempts'][$stateKey][$scenarioId] = $attempt;
            $d['sessions'][$i] = $row;
            $result = $attempt;
        }
        return $d;
    });
    if ($result === null) throw new RuntimeException(tr('Směna nebyla nalezena.'));
    return $result;
}

function arena58_inc_toggle_pause(string $sessionId, string $stateKey, string $scenarioId, bool $pause, int $now): array
{
    $result = null;
    arena58_inc_update(static function (array $d) use ($sessionId, $stateKey, $scenarioId, $pause, $now, &$result): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $sessionId) continue;
            $attempt = (array)($row['attempts'][$stateKey][$scenarioId] ?? []);
            if (empty($attempt['started_at'])) throw new RuntimeException(tr('Scénář ještě nezačal.'));
            if (!empty($attempt['solved_at'])) throw new RuntimeException(tr('Scénář je už vyřešený.'));
            if ($pause && empty($attempt['paused_at'])) {
                $attempt['paused_at'] = $now;
            } elseif (!$pause && !empty($attempt['paused_at'])) {
                $attempt['pause_accum'] = (int)($attempt['pause_accum'] ?? 0) + max(0, $now - (int)$attempt['paused_at']);
                $attempt['paused_at'] = null;
            }
            $row['attempts'][$stateKey][$scenarioId] = $attempt;
            $d['sessions'][$i] = $row;
            $result = $attempt;
        }
        return $d;
    });
    if ($result === null) throw new RuntimeException(tr('Směna nebyla nalezena.'));
    return $result;
}

/** Zavolá jádro Labu po vyřešení (checks úrovně prošly) – zaznamená technické vyřešení, ale BEZ bodů. */
function arena58_inc_on_complete(array $ctx, array $level, array $solveEvent): void
{
    $sessionId = (string)$ctx['id'];
    $stateKey = (string)$ctx['state_key'];
    $scenarioId = (string)$level['id'];
    $now = (int)$ctx['now'];
    $hints = (int)($solveEvent['hints'] ?? 0);
    arena58_inc_update(static function (array $d) use ($sessionId, $stateKey, $scenarioId, $now, $hints): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $sessionId) continue;
            $attempt = (array)($row['attempts'][$stateKey][$scenarioId] ?? []);
            if (empty($attempt['started_at'])) $attempt['started_at'] = $now;
            if (empty($attempt['solved_at'])) $attempt['solved_at'] = $now;
            $attempt['hints'] = $hints;
            $row['attempts'][$stateKey][$scenarioId] = $attempt;
            $d['sessions'][$i] = $row;
        }
        return $d;
    });
}

/**
 * Odevzdání postmortemu – TEPRVE TADY se přidělí body (technická oprava sama o sobě body nedává).
 * Body = základ úrovně × (1 − srážka × nápovědy), floor 40 %, jen když bylo technicky vyřešeno v čase;
 * po vypršení času se postmortem stále dá napsat (učitel ho ohodnotí formativně), ale body jsou 0.
 * @param array{cause:string,fix:string,prevention:string} $text
 */
function arena58_inc_submit_postmortem(string $sessionId, string $classId, string $stateKey, string $scenarioId, array $text, int $now): array
{
    $session = arena58_inc_session($sessionId);
    if ($session === null || (string)$session['class_id'] !== $classId) throw new RuntimeException(tr('Směna nebyla nalezena.'));
    foreach (['cause', 'fix', 'prevention'] as $field) {
        $value = trim((string)($text[$field] ?? ''));
        if (mb_strlen($value) < ARENA58_INC_POSTMORTEM_MIN) throw new RuntimeException(tr('Pole „{pole}“ je moc krátké – napiš aspoň pár vět.', ['pole' => arena58_inc_field_label($field)]));
        if (function_exists('cnt58_check_text')) {
            $check = cnt58_check_text($value, 'postmortem');
            if (is_array($check) && empty($check['ok'])) {
                $msg = (string)($check['issues'][0]['message'] ?? tr('Text neprošel kontrolou obsahu.'));
                throw new RuntimeException($msg);
            }
        }
    }
    $level = function_exists('lab57_level') ? lab57_level($scenarioId) : null;
    if ($level === null) throw new RuntimeException(tr('Neznámý scénář.'));
    $penalty = (float)($session['settings']['hint_penalty'] ?? 0.2);
    $result = null;
    arena58_inc_update(static function (array $d) use ($sessionId, $stateKey, $scenarioId, $text, $now, $level, $penalty, &$result): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $sessionId) continue;
            $attempt = (array)($row['attempts'][$stateKey][$scenarioId] ?? []);
            if (empty($attempt['started_at'])) throw new RuntimeException(tr('Scénář jsi ještě nespustil(a).'));
            if (is_array($attempt['postmortem'] ?? null)) throw new RuntimeException(tr('Postmortem už jsi odevzdal(a).'));
            $solved = !empty($attempt['solved_at']);
            $points = $solved ? (int)lab57_level_points($level, (int)($attempt['hints'] ?? 0), ['settings' => ['hint_penalty' => $penalty]]) : 0;
            $attempt['postmortem'] = [
                'cause' => mb_substr(trim((string)$text['cause']), 0, 2000), 'fix' => mb_substr(trim((string)$text['fix']), 0, 2000), 'prevention' => mb_substr(trim((string)$text['prevention']), 0, 2000),
                'submitted_at' => date(DATE_ATOM, $now), 'points' => $points, 'teacher_note' => null,
            ];
            $row['attempts'][$stateKey][$scenarioId] = $attempt;
            $d['sessions'][$i] = $row;
            $result = $attempt;
        }
        return $d;
    });
    if ($result === null) throw new RuntimeException(tr('Směna nebyla nalezena.'));
    return $result;
}

function arena58_inc_field_label(string $field): string
{
    return match ($field) {
        'cause' => tr('Příčina'),
        'fix' => tr('Oprava'),
        'prevention' => tr('Prevence'),
        default => $field,
    };
}

function arena58_inc_teacher_comment(string $sessionId, string $stateKey, string $scenarioId, string $note): void
{
    arena58_inc_update(static function (array $d) use ($sessionId, $stateKey, $scenarioId, $note): array {
        foreach ($d['sessions'] as $i => $row) {
            if ((string)$row['id'] !== $sessionId) continue;
            $attempt = (array)($row['attempts'][$stateKey][$scenarioId] ?? []);
            if (!is_array($attempt['postmortem'] ?? null)) throw new RuntimeException('Žák zatím postmortem neodevzdal.');
            $attempt['postmortem']['teacher_note'] = mb_substr(trim($note), 0, 1000);
            $row['attempts'][$stateKey][$scenarioId] = $attempt;
            $d['sessions'][$i] = $row;
        }
        return $d;
    });
}

// ---------------------------------------------------------------------------
// Kontext incident:<id> – přístup, body (vždy 0 – hlídá to postmortem)
// ---------------------------------------------------------------------------

function arena58_inc_access(array $level, array $ctx): ?string
{
    $session = arena58_inc_session((string)$ctx['id']);
    if ($session === null) return tr('Tahle incidentní směna neexistuje.');
    $classId = (string)$ctx['class'];
    if ((string)$session['class_id'] !== $classId) return tr('Tahle směna není pro tvou třídu.');
    if (!in_array((string)$level['id'], arena58_inc_level_ids($session), true)) return tr('Tenhle scénář není součástí směny.');
    $status = arena58_inc_status($session, (int)$ctx['now']);
    if ($status === 'draft') return tr('Směna ještě nezačala.');
    if ($session['mode'] === 'teams' && arena58_inc_team_for($session, (string)$ctx['student']) === null) return tr('Ještě nejsi zařazen(a) do týmu – ozvi se učiteli.');
    $attempt = arena58_inc_attempt($session, (string)$ctx['state_key'], (string)$level['id']);
    if ($attempt === null) return tr('Nejdřív klikni na „Spustit scénář“ na stránce incidentu.');
    // FAIR58-09: během pauzy stojí čas – terminál nesmí přijímat příkazy (jinak by se opravovalo „zadarmo“).
    if (!empty($attempt['paused_at'])) return tr('Scénář je pozastavený – nejdřív ho na stránce incidentu obnov.');
    if (arena58_inc_expired($attempt, (int)$ctx['now'])) return tr('Čas na tenhle incident vypršel (15 minut). Napiš postmortem – i bez dokončení opravy se z něj nejvíc naučíš.');
    return null;
}

function arena58_inc_info(array $ctx): ?array
{
    return arena58_inc_session((string)$ctx['id']);
}

// ---------------------------------------------------------------------------
// Žákovský pohled – data
// ---------------------------------------------------------------------------

/** @return list<array> scénáře s pager hláškou a stavem pokusu pro daný stateKey */
function arena58_inc_scenario_rows(array $session, string $stateKey, int $now): array
{
    $out = [];
    foreach (arena58_inc_levels() as $level) {
        $id = (string)$level['id'];
        if (!in_array($id, arena58_inc_level_ids($session), true)) continue;
        $attempt = arena58_inc_attempt($session, $stateKey, $id);
        $remaining = $attempt !== null ? arena58_inc_remaining($attempt, $now) : null;
        $out[] = [
            'id' => $id, 'title' => (string)$level['title'], 'pager' => (string)($level['pager'] ?? ''), 'difficulty' => (int)($level['difficulty'] ?? 1),
            'started' => $attempt !== null, 'paused' => $attempt !== null && !empty($attempt['paused_at']), 'solved' => $attempt !== null && !empty($attempt['solved_at']),
            'expired' => $attempt !== null && arena58_inc_expired($attempt, $now), 'remaining' => $remaining,
            'postmortem' => $attempt['postmortem'] ?? null,
        ];
    }
    return $out;
}

// ---------------------------------------------------------------------------
// XP (jednou za scénář, jen v požadavku daného žáka)
// ---------------------------------------------------------------------------

function arena58_inc_award_xp(string $classId, string $studentKey): void
{
    if ($studentKey === '' || !function_exists('learning_award_once')) return;
    $now = arena58_inc_now();
    try {
        foreach (arena58_inc_sessions_for_class($classId) as $session) {
            $stateKey = (string)($session['mode'] ?? 'solo') === 'teams' ? ((arena58_inc_team_for($session, $studentKey)['id'] ?? null) !== null ? 'inctym:' . $session['id'] . ':' . arena58_inc_team_for($session, $studentKey)['id'] : $studentKey) : $studentKey;
            foreach (arena58_inc_level_ids($session) as $scenarioId) {
                $attempt = arena58_inc_attempt($session, $stateKey, $scenarioId);
                if ($attempt === null || empty($attempt['postmortem'])) continue;
                $xp = ARENA58_INC_XP_PARTICIPATION + (((int)($attempt['postmortem']['points'] ?? 0)) > 0 ? ARENA58_INC_XP_SOLVED : 0);
                learning_award_once($classId, 'v58:incident:' . $session['id'] . ':' . $scenarioId, $xp);
            }
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v58 incidenty XP: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------------------------
// Učitel: data panelu
// ---------------------------------------------------------------------------

function arena58_inc_teacher_data(string $sessionId, int $now): ?array
{
    $session = arena58_inc_session($sessionId);
    if ($session === null) return null;
    $classId = (string)$session['class_id'];
    $roster = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    $name = static fn(string $key): string => trim((string)($roster[$key]['label'] ?? '')) !== '' ? (string)$roster[$key]['label'] : $key;
    $rows = [];
    foreach ((array)$session['attempts'] as $stateKey => $byScenario) {
        foreach ((array)$byScenario as $scenarioId => $attempt) {
            if (!is_array($attempt)) continue;
            $level = function_exists('lab57_level') ? lab57_level((string)$scenarioId) : null;
            $rows[] = [
                'state_key' => (string)$stateKey, 'name' => str_starts_with((string)$stateKey, 'inctym:') ? 'Tým' : $name((string)$stateKey),
                'scenario' => (string)($level['title'] ?? $scenarioId), 'scenario_id' => (string)$scenarioId,
                'started_at' => arena58_inc_iso((int)($attempt['started_at'] ?? 0)) , 'solved' => !empty($attempt['solved_at']),
                'expired' => arena58_inc_expired($attempt, $now), 'hints' => (int)($attempt['hints'] ?? 0),
                'postmortem' => $attempt['postmortem'] ?? null,
            ];
        }
    }
    usort($rows, static fn(array $a, array $b): int => strcmp((string)$b['started_at'], (string)$a['started_at']));
    return [
        'ok' => true, 'session' => ['id' => $sessionId, 'title' => (string)$session['title'], 'status' => arena58_inc_status($session, $now), 'starts_at' => $session['starts_at'] ?? null, 'ends_at' => $session['ends_at'] ?? null, 'mode' => (string)$session['mode'], 'now' => $now],
        'attempts' => $rows, 'pending_review' => count(array_filter($rows, static fn(array $r): bool => is_array($r['postmortem']) && $r['postmortem']['teacher_note'] === null)),
    ];
}

function arena58_inc_json_out(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/** GET teacher.php?tab=incidenty&inc_poll=1&session=<id> */
function arena58_inc_teacher_poll(): void
{
    $id = is_string($_GET['session'] ?? null) ? $_GET['session'] : '';
    try {
        $data = arena58_inc_valid_id($id) ? arena58_inc_teacher_data($id, arena58_inc_now()) : null;
    } catch (Throwable $e) {
        error_log('EDUCANET v58 incidenty poll: ' . $e->getMessage());
        arena58_inc_json_out(['ok' => false, 'error' => 'Data se nepodařilo načíst.'], 500);
        return;
    }
    if ($data === null) { arena58_inc_json_out(['ok' => false, 'error' => 'Směna nebyla nalezena.'], 404); return; }
    arena58_inc_json_out($data);
}

// ---------------------------------------------------------------------------
// Učitel: POST akce
// ---------------------------------------------------------------------------

function arena58_inc_teacher_handle_post(string $action, array $modules): void
{
    $classIds = array_map('strval', array_keys($modules));
    $classId = is_string($_POST['class_id'] ?? null) ? $_POST['class_id'] : '';
    $sessionId = is_string($_POST['session'] ?? null) ? $_POST['session'] : '';
    $now = arena58_inc_now();
    $done = static function (string $message, string $cid, ?string $sessionId = null, string $type = 'ok'): never {
        if (function_exists('teacher_flash')) teacher_flash($message, $type);
        $params = ['tab' => 'incidenty', 'class' => $cid];
        if ($sessionId !== null) $params['session'] = $sessionId;
        if (function_exists('teacher_redirect')) teacher_redirect($params);
        exit;
    };
    $sessionOfClass = static function () use ($sessionId, $classId, $classIds): array {
        if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
        $session = arena58_inc_session($sessionId);
        if ($session === null || (string)$session['class_id'] !== $classId) throw new RuntimeException('Směna nebyla nalezena.');
        return $session;
    };

    switch ($action) {
        case 'arena58_inc_create':
            $session = arena58_inc_create_session($_POST, $classIds, $now);
            if (!empty($_POST['start_now'])) {
                $session = arena58_inc_start_session((string)$session['id'], $now);
                $done('Směna „' . $session['title'] . '“ běží.', $classId, (string)$session['id']);
            }
            $done('Směna „' . $session['title'] . '“ je připravená.', $classId, (string)$session['id']);
        case 'arena58_inc_start':
            $session = arena58_inc_start_session((string)$sessionOfClass()['id'], $now);
            $done('Směna „' . $session['title'] . '“ běží do ' . date('j. n. H:i', (int)arena58_inc_ts($session['ends_at'])) . '.', $classId, (string)$session['id']);
        case 'arena58_inc_stop':
            $session = arena58_inc_stop_session((string)$sessionOfClass()['id'], $now);
            $done('Směna „' . $session['title'] . '“ je ukončená.', $classId, (string)$session['id']);
        case 'arena58_inc_extend':
            $session = arena58_inc_extend_session((string)$sessionOfClass()['id'], $now);
            $done('Směna prodloužena o 24 h.', $classId, (string)$session['id']);
        case 'arena58_inc_delete':
            $session = $sessionOfClass();
            arena58_inc_delete_session((string)$session['id']);
            $done('Směna „' . $session['title'] . '“ byla smazána.', $classId);
        case 'arena58_inc_comment':
            $session = $sessionOfClass();
            arena58_inc_teacher_comment((string)$session['id'], (string)($_POST['state_key'] ?? ''), (string)($_POST['scenario'] ?? ''), (string)($_POST['note'] ?? ''));
            $done('Komentář uložen.', $classId, (string)$session['id']);
        default:
            throw new RuntimeException('Neznámá akce Incidentů.');
    }
}
