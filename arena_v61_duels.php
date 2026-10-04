<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Souboje v Aréně – učitelský přehled, statistiky třídy a odveta jedním klikem.
 *
 * Staví na arena_v60_challenge.php a jeho pravidlech: výzvu smí poslat jen spolužák ze stejné třídy, adresát
 * musí mít zapnuté „přijímám výzvy“ (opt-in), platí limit čekajících výzev a pauza mezi výzvami stejnému
 * adresátovi. Odveta je obyčejná nová výzva (arena60_challenge_create) – žádná zkratka kolem pravidel,
 * navíc jen k dokončenému souboji, jen účastníkem a nejvýš jedna odveta na souboj. Za souboj se nedávají body.
 *
 * Učitelský přehled je jen ČTENÍ (nic nezapisuje, ani „úklid“ prošlých výzev): stav se dopočítá v paměti.
 * Rozsah tříd vynucuje volající (teacher59_allowed_class_ids); sem se předávají jen povolené třídy.
 * Statistiky pro žáky jsou souhrnná čísla bez jmen a vidí je jen žák, který sám výzvy přijímá (opt-in).
 */

require_once __DIR__ . '/arena_v60_challenge.php';

const ARENA61_TEACHER_ROW_LIMIT = 200;

/** Stav souboje k okamžiku $now bez zápisu: prošlé čekající = expired, přijaté s nalezeným vítězem = done. */
function arena61_effective(array $row, int $now): array
{
    $status = (string)($row['status'] ?? '');
    $winner = (string)($row['winner_key'] ?? '');
    if ($status === 'pending' && $now > (int)($row['expires_at'] ?? 0)) return ['expired', ''];
    if ($status === 'accepted') {
        $found = arena60_detect_winner((string)$row['class_id'], $row);
        if ($found !== null) return ['done', $found];
        if ($now > (int)($row['expires_at'] ?? 0)) return ['expired', ''];
    }
    return [$status, $status === 'done' ? $winner : ''];
}

/**
 * Souboje povolených tříd (nejnovější první), jen pro čtení.
 * @param list<string> $classIds
 * @return list<array<string,mixed>>
 */
function arena61_rows(array $classIds, int $now, string $statusFilter = ''): array
{
    $allowed = array_flip($classIds);
    $rows = [];
    foreach (arena60_data()['challenges'] as $row) {
        if (!is_array($row) || !isset($allowed[(string)($row['class_id'] ?? '')])) continue;
        [$status, $winner] = arena61_effective($row, $now);
        if ($statusFilter !== '' && $status !== $statusFilter) continue;
        $rows[] = $row + ['effective' => $status, 'effective_winner' => $winner];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $rows;
}

/** Počty podle stavu, zapnuté výzvy a oblíbené úlohy jedné třídy (jen čísla, žádná jména). */
function arena61_class_stats(string $classId, int $now): array
{
    $counts = ['pending' => 0, 'accepted' => 0, 'done' => 0, 'declined' => 0, 'cancelled' => 0, 'expired' => 0];
    $levels = [];
    foreach (arena61_rows([$classId], $now) as $row) {
        $status = (string)$row['effective'];
        $counts[$status] = ($counts[$status] ?? 0) + 1;
        $levels[(string)$row['level_id']] = ($levels[(string)$row['level_id']] ?? 0) + 1;
    }
    arsort($levels);
    $answered = $counts['accepted'] + $counts['done'] + $counts['declined'];
    return [
        'total' => array_sum($counts), 'counts' => $counts, 'optin' => count(array_filter((array)(arena60_data()['optin'][$classId] ?? []))),
        'roster' => count(arena57_roster($classId)), 'accept_percent' => $answered > 0 ? (int)round(($counts['accepted'] + $counts['done']) / $answered * 100) : null,
        'top_levels' => array_slice($levels, 0, 3, true),
    ];
}

/** Klíče soubojů, ke kterým už odveta proběhla (jedna odveta na souboj). @return array<string,true> */
function arena61_rematched_ids(string $classId): array
{
    $set = [];
    foreach (arena60_class_challenges($classId) as $row) {
        if (is_string($row['rematch_of'] ?? null) && $row['rematch_of'] !== '') $set[$row['rematch_of']] = true;
    }
    return $set;
}

/** Soupeř ze souboje, nebo '' když $studentKey v souboji nebyl. */
function arena61_opponent(array $row, string $studentKey): string
{
    if ((string)$row['from_key'] === $studentKey) return (string)$row['to_key'];
    return (string)$row['to_key'] === $studentKey ? (string)$row['from_key'] : '';
}

/**
 * Odveta k dokončenému souboji: stejná pravidla jako výzva (opt-in soupeře, limity), jen účastník, jednou.
 * @throws RuntimeException srozumitelný důvod pro žáka
 */
function arena61_rematch(string $classId, string $studentKey, string $challengeId, int $now): array
{
    arena60_sweep_expired($classId, $now);
    arena60_sweep_results($classId, $now);
    $row = preg_match('/^[a-f0-9]{16}$/', $challengeId) === 1 ? arena60_find($classId, $challengeId) : null;
    $opponent = $row !== null ? arena61_opponent($row, $studentKey) : '';
    if ($row === null || $opponent === '') throw new RuntimeException(tr('Výzva nebyla nalezena.'));
    if ((string)$row['status'] !== 'done') throw new RuntimeException(tr('Odvetu jde poslat jen po dokončeném souboji.'));
    if (isset(arena61_rematched_ids($classId)[$challengeId])) throw new RuntimeException(tr('K tomuto souboji už odveta proběhla.'));
    $new = arena60_challenge_create($classId, $studentKey, $opponent, $now);
    arena60_update(static function (array $d) use ($new, $challengeId): array {
        foreach ($d['challenges'] as $i => $r) {
            if ((string)($r['id'] ?? '') === (string)$new['id']) $d['challenges'][$i]['rematch_of'] = $challengeId;
        }
        return $d;
    });
    return $new + ['rematch_of' => $challengeId];
}
