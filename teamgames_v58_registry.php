<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – registr herních typů a životní cyklus relace (lobby → running ↔ paused →
 * finished → archived). Každý modul teamgames_v58_game_*.php se zaregistruje přes tg58_register_game()
 * a dodá: default_settings, parse_settings, on_start, student_view, teacher_view, projector_view,
 * student_action, teacher_action. Chybějící klíč = daná operace vyhodí srozumitelnou chybu.
 */

require_once __DIR__ . '/teamgames_v58_core.php';

const TG58_GAME_CALLBACKS = ['default_settings', 'parse_settings', 'on_start', 'student_view', 'teacher_view', 'projector_view', 'student_action', 'teacher_action', 'summary'];

function &tg58_registry(): array
{
    static $reg = ['games' => []];
    return $reg;
}

/**
 * $spec (všechny volitelné, ale chybějící klíč = daná akce selže s hláškou):
 *   label:string, fixed_team_count:?int, default_duration_min:int,
 *   default_settings(): array
 *   parse_settings(array $in, array $ctx): array                                    ctx: class_id, line, now
 *   on_start(array $session, array $ctx): array                                     ctx: + teams (po sestavení)
 *   student_view(array $session, array $ctx): array                                 ctx: student_key, team_id, viewer_mode, now
 *   teacher_view(array $session, array $ctx): array                                 ctx: now
 *   projector_view(array $session, array $ctx): array                               ctx: now
 *   student_action(string $action, array $session, array $ctx, array $payload): array{session,response}
 *   teacher_action(string $action, array $session, array $ctx, array $payload): array{session,response}
 *   tick(array $session, int $now): array                                          volitelné; časem řízené
 *     uzavření (např. Riskuj! po vypršení okna otázky) – volá se před každým čtením/akcí (viz tg58_settle*)
 *   winners(array $session): list<string>                                          volitelné; id týmů pro
 *     bonusovou XP při ručním ukončení hry (tg58_end_session) – hry, které vítěze určují průběžně
 *     (Štafeta, Úniková místnost), si 'game'/'winners' nastavují samy a tenhle klíč nepotřebují
 *   summary(array $session): array                                                  krátký souhrn pro výpis her
 */
function tg58_register_game(string $type, array $spec): bool
{
    if (!in_array($type, TG58_TYPES, true)) { error_log("EDUCANET v58 tg registry: neznámý typ '$type'"); return false; }
    $r = &tg58_registry();
    if (isset($r['games'][$type])) { error_log("EDUCANET v58 tg registry: typ '$type' už je zaregistrovaný"); return false; }
    $r['games'][$type] = $spec + ['label' => $type, 'fixed_team_count' => null, 'default_duration_min' => 0];
    return true;
}

function tg58_game_spec(string $type): ?array
{
    return tg58_registry()['games'][$type] ?? null;
}

/** @return list<string> zaregistrované typy her (v pořadí TG58_TYPES) */
function tg58_registered_types(): array
{
    return array_values(array_filter(TG58_TYPES, static fn(string $t): bool => tg58_game_spec($t) !== null));
}

function tg58_game_call(string $type, string $key, array $args): mixed
{
    $spec = tg58_game_spec($type);
    if ($spec === null) throw new RuntimeException('Tahle hra není dostupná.');
    if (!isset($spec[$key]) || !is_callable($spec[$key])) throw new RuntimeException('Tahle hra ještě tuhle akci neumí (' . $key . ').');
    return ($spec[$key])(...$args);
}

// ---------------------------------------------------------------------------
// Vytvoření relace (validace společných polí; herně specifická pole řeší parse_settings)
// ---------------------------------------------------------------------------

function tg58_parse_common(array $in, array $classIds, int $now): array
{
    $classId = is_string($in['class_id'] ?? null) ? $in['class_id'] : '';
    if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
    $type = is_string($in['type'] ?? null) ? $in['type'] : '';
    if (!in_array($type, tg58_registered_types(), true)) throw new RuntimeException('Neznámý typ hry.');
    $title = trim((string)preg_replace('/\s+/u', ' ', (string)($in['title'] ?? '')));
    if ($title === '') $title = (string)($in['default_title'] ?? tg58_game_spec($type)['label']);
    if (mb_strlen($title) > 80) throw new RuntimeException('Název hry může mít nejvýš 80 znaků.');
    $line = (string)($in['line'] ?? tg58_line_for_class($classId));
    if (!in_array($line, TG58_LINES, true)) throw new RuntimeException('Neplatná linie (sítě/grafika).');
    $teamMode = (string)($in['team_mode'] ?? 'snake');
    if (!in_array($teamMode, TG58_TEAM_MODES, true)) throw new RuntimeException('Neplatný způsob sestavení týmů.');
    $fixed = tg58_game_spec($type)['fixed_team_count'] ?? null;
    $teamCount = $fixed ?? max(TG58_MIN_TEAMS, min(TG58_MAX_TEAMS, (int)($in['team_count'] ?? 4)));
    $names = (string)($in['names'] ?? 'initials');
    if (!in_array($names, TG58_NAME_MODES, true)) throw new RuntimeException('Neplatné zobrazení jmen.');
    $duration = filter_var($in['duration_min'] ?? tg58_game_spec($type)['default_duration_min'], FILTER_VALIDATE_INT);
    if (!is_int($duration) || $duration < 0 || $duration > 180) throw new RuntimeException('Délka hry musí být 0–180 minut (0 = bez časového limitu).');
    return ['class_id' => $classId, 'type' => $type, 'title' => mb_substr($title, 0, 80), 'line' => $line, 'team_mode' => $teamMode, 'team_count' => $teamCount, 'names' => $names, 'duration_min' => $duration];
}

function tg58_create_session(array $in, array $classIds, int $now): array
{
    $common = tg58_parse_common($in, $classIds, $now);
    $ctx = ['class_id' => $common['class_id'], 'line' => $common['line'], 'now' => $now];
    $settings = tg58_game_call($common['type'], 'parse_settings', [$in, $ctx]);
    if (!is_array($settings)) throw new RuntimeException('Neplatné nastavení hry.');
    $session = [
        'id' => bin2hex(random_bytes(6)), 'class_id' => $common['class_id'], 'type' => $common['type'], 'title' => $common['title'], 'line' => $common['line'],
        'status' => 'lobby', 'created_at' => tg58_iso($now), 'started_at' => null, 'paused_at' => null, 'paused_total_s' => 0, 'finished_at' => null,
        'duration_s' => $common['duration_min'] * 60, 'names' => $common['names'], 'team_mode' => $common['team_mode'], 'team_count' => $common['team_count'],
        'teams' => [], 'settings' => $settings, 'game' => [], 'signals' => [],
    ];
    tg58_update(tg58_session_path($session['id']), static fn(array $d): array => $session);
    tg58_update(tg58_index_path(), static function (array $d) use ($session): array {
        $d['entries'] = array_values(array_filter((array)($d['entries'] ?? []), 'is_array'));
        $d['entries'][] = ['id' => $session['id'], 'class_id' => $session['class_id'], 'created_at' => $session['created_at']];
        return $d;
    });
    return $session;
}

// ---------------------------------------------------------------------------
// Životní cyklus: start / pauza / pokračování / konec / smazání
// ---------------------------------------------------------------------------

function tg58_start_session(string $id, int $now): array
{
    if (tg58_live_for_class((string)(tg58_get($id)['class_id'] ?? '')) !== null) throw new RuntimeException('Třída už má rozehranou jinou hru – nejdřív ji ukonči.');
    return tg58_mutate($id, static function (array $s) use ($now): array {
        if ((string)$s['status'] !== 'lobby') throw new RuntimeException('Spustit jde jen hra čekající v lobby.');
        $s['teams'] = tg58_form_teams((string)$s['class_id'], (string)$s['team_mode'], (int)$s['team_count']);
        $ctx = ['class_id' => (string)$s['class_id'], 'line' => (string)$s['line'], 'now' => $now, 'teams' => $s['teams']];
        $s = tg58_game_call((string)$s['type'], 'on_start', [$s, $ctx]);
        if (!is_array($s)) throw new RuntimeException('Hru se nepodařilo spustit.');
        $s['status'] = 'running';
        $s['started_at'] = tg58_iso($now);
        $s['paused_at'] = null;
        $s['paused_total_s'] = 0;
        return $s;
    });
}

function tg58_pause_session(string $id, int $now): array
{
    return tg58_mutate($id, static function (array $s) use ($now): array {
        if ((string)$s['status'] !== 'running') throw new RuntimeException('Pozastavit jde jen běžící hra.');
        $s['status'] = 'paused';
        $s['paused_at'] = tg58_iso($now);
        return $s;
    });
}

function tg58_resume_session(string $id, int $now): array
{
    return tg58_mutate($id, static function (array $s) use ($now): array {
        if ((string)$s['status'] !== 'paused') throw new RuntimeException('Pokračovat jde jen u pozastavené hry.');
        $pausedAt = tg58_ts($s['paused_at'] ?? null) ?? $now;
        $s['paused_total_s'] = (int)($s['paused_total_s'] ?? 0) + max(0, $now - $pausedAt);
        $s['paused_at'] = null;
        $s['status'] = 'running';
        return $s;
    });
}

function tg58_end_session(string $id, int $now): array
{
    return tg58_mutate($id, static function (array $s) use ($now): array {
        if (!in_array((string)$s['status'], ['running', 'paused'], true)) throw new RuntimeException('Ukončit jde jen běžící nebo pozastavená hra.');
        $s['status'] = 'finished';
        $s['finished_at'] = tg58_iso($now);
        $spec = tg58_game_spec((string)$s['type']);
        if ($spec !== null && !empty($spec['winners']) && is_callable($spec['winners'])) {
            $winners = ($spec['winners'])($s);
            if (is_array($winners)) $s['game']['winners'] = array_values(array_unique(array_merge((array)($s['game']['winners'] ?? []), array_map('strval', $winners))));
        }
        return $s;
    });
}

function tg58_archive_session(string $id): array
{
    return tg58_mutate($id, static function (array $s): array {
        if ((string)$s['status'] !== 'finished') throw new RuntimeException('Archivovat jde jen dohraná hra.');
        $s['status'] = 'archived';
        return $s;
    });
}

function tg58_delete_session(string $id): void
{
    $session = tg58_get($id);
    if ($session === null) throw new RuntimeException('Hra nebyla nalezena.');
    if (in_array((string)$session['status'], ['running', 'paused'], true)) throw new RuntimeException('Běžící hru nejde smazat – nejdřív ji ukonči.');
    tg58_update(tg58_index_path(), static function (array $d) use ($id): array {
        $d['entries'] = array_values(array_filter((array)($d['entries'] ?? []), static fn($e): bool => is_array($e) && (string)($e['id'] ?? '') !== $id));
        return $d;
    });
    $path = tg58_session_path($id);
    if (is_file($path)) unlink($path);
}

// ---------------------------------------------------------------------------
// Časem řízené uzavření (jen hry, které si zaregistrují volitelný klíč 'tick', např. Riskuj! – uzavření
// otázky po vypršení okna). $fn(array $session, int $now): array – čistá funkce, mutaci provede volající
// pod zámkem tg58_mutate. Chybí-li 'tick', hra se řídí jen akcemi (nic se nevolá).
// ---------------------------------------------------------------------------

function tg58_settle_inline(array $session, int $now): array
{
    $spec = tg58_game_spec((string)($session['type'] ?? ''));
    if ($spec === null || empty($spec['tick']) || !is_callable($spec['tick']) || !in_array(tg58_status($session, $now), ['running', 'paused'], true)) return $session;
    $out = ($spec['tick'])($session, $now);
    return is_array($out) ? $out : $session;
}

/** Pro pohledy (čtení): uzavře případnou vypršelou otázku/kolo pod krátkým zámkem, než se sestaví JSON. */
function tg58_settle(array $session, int $now): array
{
    $spec = tg58_game_spec((string)($session['type'] ?? ''));
    if ($spec === null || empty($spec['tick'])) return $session;
    try {
        return tg58_mutate((string)$session['id'], static fn(array $s): array => tg58_settle_inline($s, $now));
    } catch (Throwable $e) {
        error_log('EDUCANET v58 tg settle: ' . $e->getMessage());
        return $session;
    }
}

// ---------------------------------------------------------------------------
// Pohledy a akce (tenká vrstva nad tg58_game_call – doplní společný kontext)
// ---------------------------------------------------------------------------

function tg58_common_ctx(array $session, int $now): array
{
    return ['now' => $now, 'status' => tg58_status($session, $now), 'remaining' => tg58_remaining($session, $now), 'elapsed' => tg58_elapsed($session, $now)];
}

function tg58_student_view(array $session, string $studentKey, int $now): array
{
    $session = tg58_settle($session, $now);
    $teamId = tg58_team_of($session, $studentKey);
    $ctx = tg58_common_ctx($session, $now) + ['student_key' => $studentKey, 'team_id' => $teamId];
    $base = [
        'id' => (string)$session['id'], 'type' => (string)$session['type'], 'title' => (string)$session['title'], 'status' => $ctx['status'],
        'remaining' => $ctx['remaining'], 'names' => (string)$session['names'],
        'team' => $teamId !== null ? ['id' => $teamId, 'name' => tg58_team_label($session, $teamId), 'mates' => tg58_team_mates($session, $teamId, $studentKey)] : null,
        'teams_total' => count((array)$session['teams']), 'signals' => $teamId !== null ? tg58_public_signals($session, $teamId, 6) : [],
        'signal_kinds' => TG58_SIGNALS,
    ];
    if ($ctx['status'] === 'lobby') return $base + ['lobby' => true];
    if ($teamId === null && $ctx['status'] !== 'finished') return $base + ['waiting' => tr('Hra běží, ale zatím nejsi v žádném týmu. Zkus stránku obnovit.')];
    $game = tg58_game_call((string)$session['type'], 'student_view', [$session, $ctx]);
    return $base + ['game' => is_array($game) ? $game : []];
}

function tg58_teacher_view(array $session, int $now): array
{
    $session = tg58_settle($session, $now);
    $ctx = tg58_common_ctx($session, $now);
    $base = [
        'id' => (string)$session['id'], 'type' => (string)$session['type'], 'title' => (string)$session['title'], 'status' => $ctx['status'],
        'remaining' => $ctx['remaining'], 'elapsed' => $ctx['elapsed'], 'names' => (string)$session['names'], 'line' => (string)$session['line'],
        'teams' => array_map(static fn(array $t): array => ['id' => $t['id'], 'name' => $t['name'], 'size' => count((array)$t['members'])], (array)$session['teams']),
        'signals' => tg58_public_signals($session, null, 20),
    ];
    if ($ctx['status'] === 'lobby') return $base;
    $game = tg58_game_call((string)$session['type'], 'teacher_view', [$session, $ctx]);
    return $base + ['game' => is_array($game) ? $game : []];
}

function tg58_projector_view(array $session, int $now): array
{
    $session = tg58_settle($session, $now);
    $ctx = tg58_common_ctx($session, $now);
    $base = [
        'id' => (string)$session['id'], 'type' => (string)$session['type'], 'title' => (string)$session['title'], 'status' => $ctx['status'],
        'remaining' => $ctx['remaining'], 'names' => (string)$session['names'],
        'teams' => array_map(static fn(array $t): array => ['id' => $t['id'], 'name' => $t['name'], 'size' => count((array)$t['members'])], (array)$session['teams']),
        'signals' => tg58_public_signals($session, null, 8),
    ];
    if (in_array($ctx['status'], ['lobby'], true)) return $base;
    $game = tg58_game_call((string)$session['type'], 'projector_view', [$session, $ctx]);
    return $base + ['game' => is_array($game) ? $game : []];
}

/** Studentská akce pod zámkem relace (atomické čtení-změna-zápis, jeden zámek). Vrací odpověď pro klienta. */
function tg58_student_action(string $id, string $action, string $studentKey, array $payload, int $now): array
{
    $response = null;
    tg58_mutate($id, static function (array $s) use ($action, $studentKey, $payload, $now, &$response): array {
        if (!in_array(tg58_status($s, $now), ['running', 'paused'], true)) throw new RuntimeException(tr('Hra teď neběží.'));
        $s = tg58_settle_inline($s, $now);
        $teamId = tg58_team_of($s, $studentKey);
        if ($teamId === null) { $s = tg58_join_smallest_team($s, $studentKey); $teamId = tg58_team_of($s, $studentKey); }
        $ctx = tg58_common_ctx($s, $now) + ['student_key' => $studentKey, 'team_id' => $teamId];
        $out = tg58_game_call((string)$s['type'], 'student_action', [$action, $s, $ctx, $payload]);
        if (!is_array($out) || !isset($out['session'])) throw new RuntimeException(tr('Akci se nepodařilo zpracovat.'));
        $response = (array)($out['response'] ?? ['ok' => true]);
        return tg64_actor_from_response((array)$out['session'], $studentKey, $response); // v64: zápis přínosu žáka (actor_key)
    });
    return (array)$response;
}

function tg58_teacher_action(string $id, string $action, array $payload, int $now): array
{
    $response = null;
    tg58_mutate($id, static function (array $s) use ($action, $payload, $now, &$response): array {
        $s = tg58_settle_inline($s, $now);
        $ctx = tg58_common_ctx($s, $now);
        $out = tg58_game_call((string)$s['type'], 'teacher_action', [$action, $s, $ctx, $payload]);
        if (!is_array($out) || !isset($out['session'])) throw new RuntimeException('Akci se nepodařilo zpracovat.');
        $response = (array)($out['response'] ?? ['ok' => true]);
        return (array)$out['session'];
    });
    return (array)$response;
}
