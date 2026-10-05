<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v64 · Týmové hry: role, individuální přínos, retrospektiva.
 *
 * Role navigátor / operátor / kontrolor se mezi koly (hrami téže třídy) otáčejí. V MVP jsou rotační role
 * ve hrách escape, relay a netadmin; funkční role únikové místnosti (Síťař, Typograf …) se na ně mapují přes tg64_role_alias().
 * Přínos každého žáka se měří ze záznamu akcí (tg64_actor_note): správné odpovědi/uzly, chybné pokusy a signály týmu.
 * Kdo pro tým nic neudělal, má přínos 0 – nemůže „jet na černo“ na výsledku ostatních; přínos je jen informativní
 * (důkaz s nízkou vahou ve v62, nikdy známka). Retrospektiva po hře: 3 otázky, max 280 znaků, vidí ji jen učitel s rozsahem třídy
 * (ne asistent); ukládá se jen sha1 klíče žáka, ne jméno.
 *
 * Úložiště: storage/teamgames_v64_retro.json.php {session_id:{class_id,team_id→{sid_hash:{at,a:[3 textů]}}}} (retence po školní rok).
 */

const TG64_ROLES = ['navigator', 'operator', 'checker'];
const TG64_ROLE_GAMES = ['escape', 'relay', 'netadmin'];
const TG64_RETRO_MAX = 280;
const TG64_SIGNAL_WEIGHT = 0.5;
const TG64_SIGNAL_CAP = 2;
const TG64_SCORE_BASE = 0.3;
const TG64_SCORE_SPAN = 0.5;
const TG64_ACTOR_CAP = 200;

function tg64_retro_path(): string { return STORAGE_DIR . '/teamgames_v64_retro.json.php'; }

function tg64_role_label(string $role): string
{
    return match ($role) { 'navigator' => tr('Navigátor'), 'operator' => tr('Operátor'), 'checker' => tr('Kontrolor'), default => $role };
}

function tg64_role_hint(string $role): string
{
    return match ($role) {
        'navigator' => tr('Čteš zadání nahlas a navrhuješ postup.'),
        'operator' => tr('Zadáváš odpovědi a příkazy.'),
        'checker' => tr('Kontroluješ výsledek, než ho odešlete.'),
        default => '',
    };
}

/** Role členů týmu pro dané kolo: pořadí v týmu + kolo se otáčí. @param list<string> $members @return array<string,string> */
function tg64_assign_roles(array $members, int $round): array
{
    $out = [];
    $n = count(TG64_ROLES);
    foreach (array_values($members) as $i => $key) $out[(string)$key] = TG64_ROLES[(($i + max(0, $round)) % $n)];
    return $out;
}

/** Pořadí hry mezi hrami třídy s rotací rolí (podle času vzniku); stejné pro všechny členy. */
function tg64_round_of(array $session): int
{
    if (!in_array((string)($session['type'] ?? ''), TG64_ROLE_GAMES, true) || !function_exists('tg58_sessions_for_class')) return 0;
    $mine = (string)($session['created_at'] ?? '');
    $n = 0;
    foreach (tg58_sessions_for_class((string)($session['class_id'] ?? '')) as $s) {
        if (!in_array((string)($s['type'] ?? ''), TG64_ROLE_GAMES, true) || (string)($s['id'] ?? '') === (string)($session['id'] ?? '')) continue;
        if ((string)($s['created_at'] ?? '') < $mine) $n++;
    }
    return $n;
}

/** Role žáka ve hře (rotační) nebo null, pokud hra role nemá / žák není v týmu. */
function tg64_role_for(array $session, string $studentKey): ?string
{
    if (!in_array((string)($session['type'] ?? ''), TG64_ROLE_GAMES, true)) return null;
    $teamId = function_exists('tg58_team_of') ? tg58_team_of($session, $studentKey) : null;
    $team = $teamId !== null ? tg58_team($session, $teamId) : null;
    return $team === null ? null : (tg64_assign_roles((array)$team['members'], tg64_round_of($session))[$studentKey] ?? null);
}

/** Funkční role únikové místnosti → rotační role (zobrazení „co děláš“). */
function tg64_role_alias(string $escapeRole): ?string
{
    return ['sitar' => 'operator', 'spravce' => 'operator', 'detektiv' => 'navigator', 'dokumentator' => 'checker',
        'typograf' => 'operator', 'kolorista' => 'navigator', 'koder' => 'operator', 'art_director' => 'checker'][$escapeRole] ?? null;
}

// ---------------------------------------------------------------------------
// Přínos (actor_key)
// ---------------------------------------------------------------------------

/** Zapíše do session akci žáka: kind ok|wrong|signal. Čistá funkce nad polem session. */
function tg64_actor_note(array $session, string $studentKey, string $kind): array
{
    if ($studentKey === '' || !in_array($kind, ['ok', 'wrong', 'signal'], true)) return $session;
    $row = is_array($session['contrib'][$studentKey] ?? null) ? $session['contrib'][$studentKey] : ['ok' => 0, 'wrong' => 0, 'signal' => 0];
    $row[$kind] = min(TG64_ACTOR_CAP, (int)($row[$kind] ?? 0) + 1);
    $session['contrib'][$studentKey] = $row;
    return $session;
}

/** Z odpovědi akce (klíč 'correct') odvodí zápis přínosu. */
function tg64_actor_from_response(array $session, string $studentKey, array $response): array
{
    if (!array_key_exists('correct', $response)) return $session;
    return tg64_actor_note($session, $studentKey, $response['correct'] === true ? 'ok' : 'wrong');
}

/** Přínos 0–1 člena týmu: podíl na vyřešeném (násobený počtem členů, strop 1) × přesnost. Bez práce = 0. */
function tg64_contribution(array $session, string $teamId, string $studentKey): float
{
    $team = null;
    foreach ((array)($session['teams'] ?? []) as $t) if (is_array($t) && (string)($t['id'] ?? '') === $teamId) $team = $t;
    if ($team === null || !in_array($studentKey, (array)$team['members'], true)) return 0.0;
    $members = array_values(array_map('strval', (array)$team['members']));
    $units = static function (string $key) use ($session): float {
        $c = (array)($session['contrib'][$key] ?? []);
        return (int)($c['ok'] ?? 0) + TG64_SIGNAL_WEIGHT * min(TG64_SIGNAL_CAP, (int)($c['signal'] ?? 0));
    };
    $total = 0.0;
    foreach ($members as $m) $total += $units($m);
    $mine = $units($studentKey);
    if ($total <= 0 || $mine <= 0) return 0.0;
    $c = (array)($session['contrib'][$studentKey] ?? []);
    $ok = (int)($c['ok'] ?? 0);
    $wrong = (int)($c['wrong'] ?? 0);
    $accuracy = $ok + $wrong > 0 ? $ok / ($ok + $wrong) : 1.0;
    return round(min(1.0, ($mine / $total) * count($members)) * (0.5 + 0.5 * $accuracy), 3);
}

/** Skóre týmové hry jako důkaz s nízkou vahou: 0,3 + 0,5 × přínos (max 0,8). */
function tg64_evidence_score(float $contribution): float
{
    return round(min(0.8, TG64_SCORE_BASE + TG64_SCORE_SPAN * max(0.0, min(1.0, $contribution))), 3);
}

// ---------------------------------------------------------------------------
// Retrospektiva (3 otázky)
// ---------------------------------------------------------------------------

/** @return list<string> */
function tg64_retro_questions(): array
{
    return [tr('Co nám jako týmu šlo dobře?'), tr('Kde jsme ztratili nejvíc času?'), tr('Co uděláme příště jinak?')];
}

function tg64_retro_clean(mixed $text): string
{
    $s = is_string($text) ? preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/u', '', $text) : '';
    return mb_substr(trim((string)$s), 0, TG64_RETRO_MAX);
}

/** Uloží retrospektivu žáka po dohrané hře (přepíše jeho předchozí). @param array<int,mixed> $answers @throws RuntimeException */
function tg64_retro_save(string $classId, string $sessionId, string $teamId, string $studentKey, array $answers, ?int $now = null): void
{
    $clean = [];
    for ($i = 0; $i < 3; $i++) $clean[] = tg64_retro_clean($answers[$i] ?? '');
    if (implode('', $clean) === '') throw new RuntimeException(tr('Napiš aspoň jednu větu.'));
    if (preg_match('/^[A-Za-z0-9_-]{4,40}$/', $sessionId) !== 1 || $studentKey === '') throw new RuntimeException(tr('Neplatná hra.'));
    $now ??= time();
    $sid = sha1($classId . '|' . $studentKey);
    storage_update(tg64_retro_path(), static function (array $d) use ($classId, $sessionId, $teamId, $sid, $clean, $now): array {
        $d[$sessionId]['class_id'] = $classId;
        $d[$sessionId]['teams'][$teamId][$sid] = ['at' => date(DATE_ATOM, $now), 'a' => $clean];
        return $d;
    });
}

function tg64_retro_done(string $classId, string $sessionId, string $teamId, string $studentKey): bool
{
    return isset(storage_read(tg64_retro_path())[$sessionId]['teams'][$teamId][sha1($classId . '|' . $studentKey)]);
}

/** Smí aktuální učitel retrospektivy číst? Jen s rozsahem třídy a ne asistent (v legacy režimu bez účtů ano). */
function tg64_retro_teacher_can(string $classId): bool
{
    if (!function_exists('teacher59_can_class') || !teacher59_can_class($classId)) return false;
    if (function_exists('teacher59_mode') && teacher59_mode() === 'legacy') return true;
    $account = function_exists('teacher59_current') ? teacher59_current() : null;
    return is_array($account) && (string)($account['role'] ?? '') !== 'assistant' && (string)($account['role'] ?? '') !== '';
}

/** Retrospektivy hry pro učitele: [tým => [[3 odpovědi]…]] bez jmen; prázdné pole bez oprávnění. */
function tg64_retro_for_teacher(string $sessionId): array
{
    $row = storage_read(tg64_retro_path())[$sessionId] ?? null;
    if (!is_array($row) || !tg64_retro_teacher_can((string)($row['class_id'] ?? ''))) return [];
    $out = [];
    foreach ((array)($row['teams'] ?? []) as $teamId => $entries) {
        foreach ((array)$entries as $e) $out[(string)$teamId][] = array_values((array)($e['a'] ?? []));
    }
    return $out;
}

/** Retence: smaže retrospektivy starší než školní rok (cutoff = ts); vrací počet smazaných her. */
function tg64_retro_purge(int $cutoff, bool $dryRun = false): int
{
    $n = 0;
    $apply = static function (array $d) use ($cutoff, &$n): array {
        foreach ($d as $sid => $row) {
            $latest = 0;
            foreach ((array)($row['teams'] ?? []) as $entries) foreach ((array)$entries as $e) $latest = max($latest, (int)strtotime((string)($e['at'] ?? '')));
            if ($latest < $cutoff) { unset($d[$sid]); $n++; }
        }
        return $d;
    };
    if ($dryRun) $apply(storage_read(tg64_retro_path()));
    else storage_update(tg64_retro_path(), $apply);
    return $n;
}
