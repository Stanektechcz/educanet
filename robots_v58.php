<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Robotí liga – úložiště, zápasy, týmy, odevzdání, výsledky, liga a XP.
 *
 * Úložiště (vše s ochranným prvním řádkem, zápisy jen přes storage_update):
 *   <storage>/robots_v58.json.php                 {matches:[…]}
 *   <storage>/robots_v58/match_<id>.json.php       {subs:{<žák>:{code, at, label, bytes, n}}}   – skripty zápasu
 *   <storage>/robots_v58/drafts_<třída>.json.php   {<žák>:{code, at}}                            – koncepty
 *   <storage>/robots_v58/replay_<id>.json.php      {robots:[…], stats:[…], replay_json:"…"}     – záznam zápasu
 * Klíče žáků jsou jen na serveru; ven jdou jména podle soukromí zápasu (iniciály / celá / anonymně).
 * XP se připisuje výhradně v požadavku daného žáka (learning_award_once drží profil v jeho session).
 */

require_once __DIR__ . '/robots_v58_game.php';
require_once __DIR__ . '/economy_v64.php';
require_once __DIR__ . '/arena_v64_fair.php';

const ROBOTS58_ID_RE = '/^[a-z0-9]{8,32}$/';
const ROBOTS58_TEAMS_MIN = 2;
const ROBOTS58_TEAMS_MAX = 4;
const ROBOTS58_TURNS_DEFAULT = 300;
const ROBOTS58_TURNS_MIN = 50;
const ROBOTS58_TURNS_MAX = 500;
const ROBOTS58_TRAINING_TURNS = [50, 100, 150, 300];
const ROBOTS58_TRAINING_MAPS = 5;
const ROBOTS58_NAME_MODES = ['initials', 'full', 'anon'];
const ROBOTS58_XP_PARTICIPATION = 20;
const ROBOTS58_XP_PODIUM = [1 => 30, 2 => 20, 3 => 10];
const ROBOTS58_LEAGUE_POINTS = [1 => 10, 2 => 7, 3 => 5];
const ROBOTS58_LEAGUE_PARTICIPATION = 3;
const ROBOTS58_TEAM_CORNERS = [0, 3, 1, 2];
const ROBOTS58_TEAM_NAMES = ['Tučňáci', 'Pakety', 'Jádra', 'Bajty', 'Routeři', 'Shelláci', 'Démoni', 'Pingři'];
const ROBOTS58_BOARD_TOP = 10;

// ---------------------------------------------------------------------------
// Úložiště a čas
// ---------------------------------------------------------------------------

function robots58_now(): int
{
    return is_int($GLOBALS['robots58_now_override'] ?? null) ? $GLOBALS['robots58_now_override'] : time();
}

function robots58_dir(): string
{
    if (is_string($GLOBALS['robots58_storage_override'] ?? null)) return $GLOBALS['robots58_storage_override'];
    return defined('STORAGE_DIR') ? (string)STORAGE_DIR : __DIR__ . '/storage';
}

function robots58_path(string $kind = 'main', string $id = ''): string
{
    $id = preg_replace('/[^a-z0-9_]/i', '', $id) ?? '';
    return match ($kind) {
        'match' => robots58_dir() . '/robots_v58/match_' . $id . '.json.php',
        'drafts' => robots58_dir() . '/robots_v58/drafts_' . $id . '.json.php',
        'replay' => robots58_dir() . '/robots_v58/replay_' . $id . '.json.php',
        default => robots58_dir() . '/robots_v58.json.php',
    };
}

/** Čtení pod sdíleným zámkem jádra úložiště (DAT58-04); bez jádra (izolované audity) prosté čtení. */
function robots58_read(string $path): array
{
    if (function_exists('storage_read')) return storage_read($path, false);
    if (!is_file($path)) return [];
    $raw = (string)file_get_contents($path);
    $data = json_decode(trim(preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? ''), true);
    return is_array($data) ? $data : [];
}

/** Atomická úprava (jeden zámek přes čtení i zápis) – vždy přes storage_update z bootstrap.php. */
function robots58_update(string $path, callable $mutate): array
{
    if (!function_exists('storage_update')) throw new RuntimeException('Chybí úložiště aplikace (storage_update).');
    return storage_update($path, $mutate);
}

/** CSRF: token z formuláře / hlavičky musí přesně odpovídat tokenu v session (hash_equals). */
function robots58_csrf_ok(mixed $token): bool
{
    $expected = $_SESSION['csrf'] ?? null;
    return is_string($token) && $token !== '' && is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

function robots58_valid_id(string $id): bool
{
    return preg_match(ROBOTS58_ID_RE, $id) === 1;
}

function robots58_iso(?int $ts): ?string
{
    return $ts === null ? null : date(DATE_ATOM, $ts);
}

function robots58_ts(mixed $iso): ?int
{
    if (!is_string($iso) || $iso === '') return null;
    $ts = strtotime($iso);
    return $ts === false ? null : $ts;
}

/** Pololetí: září–leden = 1., únor–srpen = 2. (klíč „2026-1“, rok = začátek školního roku). */
function robots58_semester(int $ts): string
{
    $month = (int)date('n', $ts);
    $year = (int)date('Y', $ts);
    if ($month >= 9) return $year . '-1';
    return ($year - 1) . ($month === 1 ? '-1' : '-2');
}

function robots58_semester_label(string $semester): string
{
    [$year, $half] = array_map('intval', explode('-', $semester) + [0, 1]);
    return tr('{year} · {half}. pololetí', ['year' => $year . '/' . substr((string)($year + 1), 2), 'half' => $half]);
}

// ---------------------------------------------------------------------------
// Ukázkové skripty (od nejjednoduššího) – slouží i jako cvičný soupeř
// ---------------------------------------------------------------------------

function robots58_examples(): array
{
    return [
        'start' => ['title' => '1 · První kroky', 'text' => "# První kroky: dojeď pro balíček a přivez ho domů.\n# Skript běží každý tah znovu odshora, dokud robot neudělá jednu akci.\nif here == \"packet\" and cargo < max_cargo:\n    pick\nelif cargo > 0:\n    if here == \"base\":\n        drop\n    else:\n        step_to nearest(\"base\")\nelse:\n    step_to nearest(\"packet\")\n"],
        'collector' => ['title' => '2 · Sběrač s nabíjením', 'text' => "# Sběrač: vozí plný náklad a hlídá baterii.\nif energy < 15 and here != \"charger\":\n    say \"Jedu nabíjet\"\n    step_to nearest(\"charger\")\nelif here == \"charger\" and energy < 90:\n    charge\nelif here == \"packet\" and cargo < max_cargo:\n    pick\nelif cargo == max_cargo or (cargo > 0 and nearest(\"packet\") == none):\n    if here == \"base\":\n        drop\n    else:\n        step_to nearest(\"base\")\nelse:\n    cil = nearest(\"packet\")\n    if cil != none:\n        step_to cil\n    else:\n        wait\n"],
        'repair' => ['title' => '3 · Opravář s funkcí', 'text' => "# Opravář: rozbitý uzel má přednost, jinak sbírá.\ndef dojed(cil):\n    if cil == none:\n        wait\n    step_to cil\n\nuzel = nearest(\"node\")\nif energy < 12 and here != \"charger\":\n    dojed(nearest(\"charger\"))\nelif here == \"charger\" and energy < 80:\n    charge\nelif here == \"node\":\n    repair\nelif uzel != none and distance(uzel) < 10:\n    dojed(uzel)\nelif here == \"packet\" and cargo < max_cargo:\n    pick\nelif cargo > 0 and here == \"base\":\n    drop\nelif cargo > 0:\n    dojed(nearest(\"base\"))\nelse:\n    dojed(nearest(\"packet\"))\n"],
        'memory' => ['title' => '4 · Pokročilý: paměť a plán', 'text' => "# Pokročilý: paměť (keep) přežije mezi tahy, funkce vrací hodnotu.\nkeep cesty = 0\nkeep rezim = \"sber\"\n\ndef stihnu_domu():\n    return distance(nearest(\"base\")) + 2 < turns_left\n\nif rezim == \"sber\" and (cargo == max_cargo or not stihnu_domu()):\n    rezim = \"domu\"\nif energy < 10 + distance(nearest(\"charger\")) and here != \"charger\":\n    rezim = \"nabit\"\n\nif rezim == \"nabit\":\n    if here == \"charger\":\n        if energy >= 90:\n            rezim = \"sber\"\n        charge\n    step_to nearest(\"charger\")\nelif rezim == \"domu\":\n    if here == \"base\":\n        cesty += 1\n        rezim = \"sber\"\n        say \"Cesta č. \" + cesty\n        drop\n    step_to nearest(\"base\")\nelif here == \"node\":\n    repair\nelif here == \"packet\":\n    pick\nelse:\n    cil = nearest(\"packet\")\n    if cil == none:\n        cil = nearest(\"node\")\n    if cil == none:\n        rezim = \"domu\"\n        wait\n    step_to cil\n"],
    ];
}

// ---------------------------------------------------------------------------
// Zápasy
// ---------------------------------------------------------------------------

function robots58_matches(): array
{
    $rows = robots58_read(robots58_path())['matches'] ?? [];
    return array_values(array_filter(is_array($rows) ? $rows : [], static fn($m): bool => is_array($m) && is_string($m['id'] ?? null)));
}

function robots58_match(string $id): ?array
{
    if (!robots58_valid_id($id)) return null;
    foreach (robots58_matches() as $match) { if ($match['id'] === $id) return $match; }
    return null;
}

/** @return list<array> zápasy třídy, nejnovější první */
function robots58_matches_for_class(string $classId): array
{
    $rows = array_values(array_filter(robots58_matches(), static fn(array $m): bool => (string)($m['class_id'] ?? '') === $classId));
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $rows;
}

/** open = příprava (lze odevzdávat) · closed = uzávěrka, čeká na simulaci · finished = odehráno */
function robots58_status(array $match, int $now): string
{
    if (!empty($match['results'])) return 'finished';
    if (!empty($match['closed_at'])) return 'closed';
    $deadline = robots58_ts($match['deadline'] ?? null);
    return $deadline !== null && $now >= $deadline ? 'closed' : 'open';
}

function robots58_mutate_match(string $id, callable $fn): array
{
    $result = null;
    robots58_update(robots58_path(), static function (array $d) use ($id, $fn, &$result): array {
        $d['matches'] = is_array($d['matches'] ?? null) ? $d['matches'] : [];
        foreach ($d['matches'] as $k => $match) {
            if (is_array($match) && ($match['id'] ?? '') === $id) { $d['matches'][$k] = $result = $fn($match); return $d; }
        }
        throw new RuntimeException('Zápas nebyl nalezen.');
    });
    return $result;
}

function robots58_roster(string $classId): array
{
    if (function_exists('arena57_roster')) return arena57_roster($classId);
    $override = $GLOBALS['robots58_roster_override'][$classId] ?? null;
    if (is_array($override)) return $override;
    return function_exists('project_students_for_class') ? project_students_for_class($classId) : [];
}

/** Týmy vyvážené hadím draftem (Aréna v57), nejvýš 4 – každý tým má svůj roh mapy. */
function robots58_form_teams(string $classId, int $count): array
{
    $roster = robots58_roster($classId);
    if (count($roster) < 2) throw new RuntimeException('Na týmový zápas jsou potřeba aspoň dva žáci na soupisce třídy.');
    $points = [];
    foreach (array_keys($roster) as $key) $points[(string)$key] = function_exists('lab57_student_summary') ? (int)(lab57_student_summary($classId, (string)$key)['points'] ?? 0) : 0;
    $size = max(2, (int)ceil(count($points) / $count));
    if (function_exists('arena57_snake_teams')) {
        $teams = arena57_snake_teams($points, $size);
    } else {
        $keys = array_keys($points);
        $n = (int)ceil(count($keys) / $size);
        $teams = [];
        for ($i = 0; $i < $n; $i++) $teams[] = ['name' => ROBOTS58_TEAM_NAMES[$i], 'members' => []];
        foreach ($keys as $pos => $key) $teams[intdiv($pos, $n) % 2 === 0 ? $pos % $n : $n - 1 - $pos % $n]['members'][] = (string)$key;
    }
    $out = [];
    foreach (array_slice(array_values($teams), 0, ROBOTS58_TEAMS_MAX) as $i => $team) {
        $out[] = ['id' => 't' . ($i + 1), 'name' => (string)$team['name'], 'corner' => ROBOTS58_TEAM_CORNERS[$i], 'members' => array_values(array_map('strval', (array)$team['members']))];
    }
    return $out;
}

/** Ověří formulář učitele a založí zápas ve fázi přípravy. */
function robots58_create_match(array $in, array $classIds, int $now): array
{
    $classId = is_string($in['class_id'] ?? null) ? $in['class_id'] : '';
    if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
    $title = trim(preg_replace('/\s+/u', ' ', (string)($in['title'] ?? '')) ?? '');
    $title = $title === '' ? 'Zápas robotů' : mb_substr($title, 0, 60);
    $mode = ($in['mode'] ?? '') === 'teams' ? 'teams' : 'solo';
    $teamCount = max(ROBOTS58_TEAMS_MIN, min(ROBOTS58_TEAMS_MAX, (int)($in['teams'] ?? 2)));
    $turns = max(ROBOTS58_TURNS_MIN, min(ROBOTS58_TURNS_MAX, (int)($in['turns'] ?? ROBOTS58_TURNS_DEFAULT)));
    $names = in_array($in['names'] ?? '', ROBOTS58_NAME_MODES, true) ? (string)$in['names'] : 'initials';
    $deadline = robots58_ts(is_string($in['deadline'] ?? null) ? str_replace('T', ' ', $in['deadline']) : null);
    if ($deadline === null) $deadline = $now + 45 * 60;
    if ($deadline <= $now) throw new RuntimeException('Uzávěrka musí být v budoucnosti.');
    if ($deadline > $now + 30 * 86400) throw new RuntimeException('Uzávěrka může být nejpozději za 30 dní.');
    $match = [
        'id' => bin2hex(random_bytes(6)), 'class_id' => $classId, 'title' => $title, 'mode' => $mode, 'team_count' => $mode === 'teams' ? $teamCount : 0,
        'turns' => $turns, 'names' => $names, 'seed' => random_int(1, 2147483646), 'deadline' => robots58_iso($deadline),
        'created_at' => robots58_iso($now), 'semester' => robots58_semester($now), 'closed_at' => null, 'ran_at' => null,
        'teams' => $mode === 'teams' ? robots58_form_teams($classId, $teamCount) : [], 'results' => null,
    ];
    robots58_update(robots58_path(), static function (array $d) use ($match): array {
        $d['matches'] = array_values(array_filter((array)($d['matches'] ?? []), 'is_array'));
        $d['matches'][] = $match;
        return $d;
    });
    return $match;
}

function robots58_close_match(string $id, int $now): array
{
    return robots58_mutate_match($id, static function (array $m) use ($now): array {
        if (robots58_status($m, $now) === 'finished') throw new RuntimeException('Zápas už je odehraný.');
        $m['closed_at'] ??= robots58_iso($now);
        return $m;
    });
}

function robots58_delete_match(string $id): void
{
    robots58_update(robots58_path(), static function (array $d) use ($id): array {
        $d['matches'] = array_values(array_filter((array)($d['matches'] ?? []), static fn($m): bool => is_array($m) && ($m['id'] ?? '') !== $id));
        return $d;
    });
    foreach (['match', 'replay'] as $kind) {
        $path = robots58_path($kind, $id);
        if (is_file($path)) unlink($path);
    }
}

function robots58_team_of(array $match, string $studentKey): ?int
{
    foreach ((array)($match['teams'] ?? []) as $i => $team) { if (in_array($studentKey, (array)$team['members'], true)) return (int)$i; }
    return null;
}

// ---------------------------------------------------------------------------
// Koncepty a odevzdání
// ---------------------------------------------------------------------------

function robots58_draft(string $classId, string $studentKey): ?array
{
    $row = robots58_read(robots58_path('drafts', $classId))[$studentKey] ?? null;
    return is_array($row) && is_string($row['code'] ?? null) ? $row : null;
}

function robots58_save_draft(string $classId, string $studentKey, string $code, int $now): array
{
    if (strlen($code) > ROBOTS58_MAX_BYTES) throw new RuntimeException(tr('Skript je delší než 4 KB.'));
    $row = ['code' => $code, 'at' => robots58_iso($now)];
    robots58_update(robots58_path('drafts', $classId), static function (array $d) use ($studentKey, $row): array {
        $d[$studentKey] = $row;
        return $d;
    });
    return $row;
}

/** @return array<string,array> odevzdání zápasu (klíč žáka => {code, at, label, bytes, n}) */
function robots58_submissions(string $matchId): array
{
    $subs = robots58_read(robots58_path('match', $matchId))['subs'] ?? [];
    return is_array($subs) ? array_filter($subs, static fn($s): bool => is_array($s) && is_string($s['code'] ?? null)) : [];
}

/** Odevzdá skript do zápasu. Platí poslední platné odevzdání; skript s chybou se odmítne. */
function robots58_submit(string $classId, string $studentKey, string $label, string $matchId, string $code, int $now): array
{
    $match = robots58_match($matchId);
    if ($match === null || (string)$match['class_id'] !== $classId) return ['ok' => false, 'error' => tr('Zápas nebyl nalezen.')];
    if (robots58_status($match, $now) !== 'open') return ['ok' => false, 'error' => tr('Odevzdávání do tohoto zápasu je už uzavřené.')];
    if (strlen($code) > ROBOTS58_MAX_BYTES) return ['ok' => false, 'error' => tr('Skript je delší než 4 KB.')];
    $parsed = robots58_parse($code, false);
    if (!$parsed['ok']) return ['ok' => false, 'error' => tr('Skript má chybu, takže ho nejde odevzdat. Tvoje poslední platné odevzdání zůstává.'), 'parse' => $parsed['error']];
    $row = ['code' => $code, 'at' => robots58_iso($now), 'label' => mb_substr($label, 0, 80), 'bytes' => strlen($code)];
    $saved = robots58_update(robots58_path('match', $matchId), static function (array $d) use ($studentKey, $row): array {
        $d['subs'] = is_array($d['subs'] ?? null) ? $d['subs'] : [];
        $row['n'] = (int)($d['subs'][$studentKey]['n'] ?? 0) + 1;
        $d['subs'][$studentKey] = $row;
        return $d;
    });
    if ($match['mode'] === 'teams' && robots58_team_of($match, $studentKey) === null) robots58_join_smallest_team($matchId, $studentKey);
    robots58_save_draft($classId, $studentKey, $code, $now);
    return ['ok' => true, 'at' => $row['at'], 'n' => (int)$saved['subs'][$studentKey]['n'], 'warnings' => $parsed['warnings']];
}

/** Žák, který nebyl na soupisce při zakládání týmů, se přidá do nejmenšího týmu. */
function robots58_join_smallest_team(string $matchId, string $studentKey): void
{
    robots58_mutate_match($matchId, static function (array $m) use ($studentKey): array {
        if (robots58_team_of($m, $studentKey) !== null || empty($m['teams'])) return $m;
        $best = 0;
        foreach ($m['teams'] as $i => $team) { if (count($team['members']) < count($m['teams'][$best]['members'])) $best = $i; }
        $m['teams'][$best]['members'][] = $studentKey;
        return $m;
    });
}

// ---------------------------------------------------------------------------
// Simulace zápasu a výsledky
// ---------------------------------------------------------------------------

/** Pořadí s dělenými místy: shodné body = stejné místo. */
function robots58_rank(array $rows, string $field = 'points'): array
{
    usort($rows, static fn(array $a, array $b): int => ($b[$field] <=> $a[$field]) ?: strcmp((string)($a['order'] ?? ''), (string)($b['order'] ?? '')));
    $rank = 0;
    $prev = null;
    foreach ($rows as $i => $row) {
        if ($prev === null || $row[$field] !== $prev) $rank = $i + 1;
        $rows[$i]['rank'] = $rank;
        $prev = $row[$field];
    }
    return $rows;
}

/** Roboti zápasu v deterministickém pořadí (hash semínka a klíče – ne abeceda, ne čas odevzdání). */
function robots58_match_robots(array $match, array $subs): array
{
    $robots = [];
    foreach ($subs as $key => $sub) {
        $key = (string)$key;
        $team = $match['mode'] === 'teams' ? robots58_team_of($match, $key) : null;
        if ($match['mode'] === 'teams' && $team === null) continue;
        $robots[] = ['key' => $key, 'label' => (string)($sub['label'] ?? ''), 'code' => (string)$sub['code'], 'team' => $team, 'order' => hash('sha256', $match['seed'] . ':' . $key)];
    }
    usort($robots, static fn(array $a, array $b): int => strcmp($a['order'], $b['order']));
    foreach ($robots as $i => $robot) {
        $robots[$i]['corner'] = $robot['team'] !== null ? (int)$match['teams'][$robot['team']]['corner'] : $i % 4;
    }
    return $robots;
}

/** Učitel spustí simulaci: uzavře odevzdávání, odehraje zápas, uloží záznam a výsledky. */
function robots58_run_match(string $id, int $now): array
{
    $before = robots58_match($id);
    if ($before === null) throw new RuntimeException('Zápas nebyl nalezen.');
    if (robots58_match_robots($before, robots58_submissions($id)) === []) throw new RuntimeException('Zatím nikdo neodevzdal skript – není koho pustit do arény. Odevzdávání zůstává otevřené.');
    $match = robots58_close_match($id, $now);
    $robots = robots58_match_robots($match, robots58_submissions($id));
    $sim = robots58_simulate((int)$match['seed'], (int)$match['turns'], array_map(static fn(array $r): array => ['code' => $r['code'], 'corner' => $r['corner']], $robots));
    $rows = [];
    foreach ($robots as $i => $robot) {
        $st = $sim['stats'][$i];
        $rows[] = ['key' => $robot['key'], 'label' => $robot['label'], 'team' => $robot['team'], 'order' => $robot['order'], 'no' => $i + 1, 'points' => (int)$st['points'], 'delivered' => (int)$st['delivered'], 'repairs' => (int)$st['repairs'], 'errors' => (int)$st['errors'] + (int)$st['budget']];
    }
    $teams = [];
    foreach ((array)$match['teams'] as $t => $team) {
        $mine = array_filter($rows, static fn(array $r): bool => $r['team'] === $t);
        $teams[] = ['team' => $t, 'name' => (string)$team['name'], 'points' => array_sum(array_column($mine, 'points')), 'robots' => count($mine), 'size' => count($team['members']), 'order' => (string)$t];
    }
    $results = ['robots' => robots58_rank($rows), 'teams' => $teams === [] ? [] : robots58_rank($teams), 'hash' => $sim['hash'], 'ms' => $sim['ms'], 'count' => count($rows)];
    $store = ['robots' => array_map(static fn(array $r): array => ['key' => $r['key'], 'label' => $r['label'], 'team' => $r['team']], $robots), 'stats' => $sim['stats'], 'hash' => $sim['hash'], 'replay_json' => json_encode($sim['replay'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)];
    robots58_update(robots58_path('replay', $id), static fn(array $d): array => $store);
    return robots58_mutate_match($id, static function (array $m) use ($results, $now): array {
        if (!empty($m['results'])) throw new RuntimeException('Zápas už byl odehraný.');
        $m['results'] = $results;
        $m['ran_at'] = robots58_iso($now);
        return $m;
    });
}

/** Místo žáka v zápase (v týmech podle týmu), null = nehrál. */
function robots58_place(array $match, string $studentKey): ?int
{
    foreach ((array)($match['results']['robots'] ?? []) as $row) {
        if ($row['key'] !== $studentKey) continue;
        if ($match['mode'] !== 'teams') return (int)$row['rank'];
        foreach ((array)$match['results']['teams'] as $team) { if ($team['team'] === $row['team']) return (int)$team['rank']; }
    }
    return null;
}

// ---------------------------------------------------------------------------
// Jména podle soukromí
// ---------------------------------------------------------------------------

function robots58_initials(string $label): string
{
    if (function_exists('lab57_display_name')) return lab57_display_name($label);
    $parts = preg_split('/\s+/u', trim($label)) ?: [];
    if ($parts === [] || $parts[0] === '') return tr('Žák');
    $first = (string)array_shift($parts);
    return trim($first . ($parts !== [] ? ' ' . mb_substr((string)end($parts), 0, 1) . '.' : ''));
}

function robots58_label(string $key, string $label, array $roster): string
{
    $name = trim((string)($roster[$key]['label'] ?? ''));
    return $name !== '' ? $name : ($label !== '' ? $label : tr('Žák'));
}

/** full = celé jméno, initials = „Adam K.“, anon = „Robot N“ (divák vidí sebe jako „Ty“). */
function robots58_public_name(string $mode, string $key, string $label, ?string $viewerKey, int $no): string
{
    if ($viewerKey !== null && $viewerKey === $key && $mode === 'anon') return tr('Ty');
    if ($mode === 'full') return $label;
    if ($mode === 'anon') return tr('Robot {n}', ['n' => $no]);
    return robots58_initials($label);
}

/** Veřejné výsledky zápasu (bez klíčů žáků). $viewerKey = null pro projektor, $teacher = celá jména. */
function robots58_public_results(array $match, ?string $viewerKey, bool $teacher = false, int $limit = ROBOTS58_BOARD_TOP): array
{
    $res = (array)($match['results'] ?? []);
    $roster = robots58_roster((string)$match['class_id']);
    $mode = $teacher ? 'full' : (string)($match['names'] ?? 'initials');
    $rows = [];
    foreach ((array)($res['robots'] ?? []) as $row) {
        $me = $viewerKey !== null && $row['key'] === $viewerKey;
        if (count($rows) >= $limit && !$me && !$teacher) continue;
        $rows[] = ['rank' => (int)$row['rank'], 'no' => (int)$row['no'], 'name' => robots58_public_name($mode, (string)$row['key'], robots58_label((string)$row['key'], (string)$row['label'], $roster), $viewerKey, (int)$row['no']), 'team' => $row['team'] !== null ? (string)$match['teams'][$row['team']]['name'] : '', 'points' => (int)$row['points'], 'delivered' => (int)$row['delivered'], 'repairs' => (int)$row['repairs'], 'me' => $me];
    }
    $teams = [];
    foreach ((array)($res['teams'] ?? []) as $team) {
        $mine = $viewerKey !== null && robots58_team_of($match, $viewerKey) === $team['team'];
        $teams[] = ['rank' => (int)$team['rank'], 'name' => (string)$team['name'], 'points' => (int)$team['points'], 'robots' => (int)$team['robots'], 'size' => (int)$team['size'], 'me' => $mine];
    }
    return ['robots' => $rows, 'teams' => $teams, 'count' => (int)($res['count'] ?? count($rows))];
}

/** Záznam pro přehrávač; jména podle soukromí, vlastní statistiky jen divákovi (učitel vidí všechny). */
function robots58_replay_payload(string $matchId, ?string $viewerKey, bool $teacher = false): ?array
{
    $match = robots58_match($matchId);
    if ($match === null || empty($match['results'])) return null;
    $store = robots58_read(robots58_path('replay', $matchId));
    $replay = json_decode((string)($store['replay_json'] ?? ''), true);
    if (!is_array($replay)) return null;
    $roster = robots58_roster((string)$match['class_id']);
    $mode = $teacher ? 'full' : (string)($match['names'] ?? 'initials');
    $robots = [];
    $mine = null;
    foreach ((array)$store['robots'] as $i => $robot) {
        $me = $viewerKey !== null && $robot['key'] === $viewerKey;
        if ($me) $mine = $i;
        $team = $robot['team'] !== null ? (int)$robot['team'] : null;
        $robots[] = ['name' => robots58_public_name($mode, (string)$robot['key'], robots58_label((string)$robot['key'], (string)$robot['label'], $roster), $viewerKey, $i + 1), 'team' => $team, 'me' => $me];
    }
    $replay['robots'] = $robots;
    $replay['teams'] = array_map(static fn(array $t): array => ['name' => (string)$t['name'], 'corner' => (int)$t['corner']], (array)$match['teams']);
    $replay['title'] = (string)$match['title'];
    return ['replay' => $replay, 'results' => robots58_public_results($match, $viewerKey, $teacher), 'me' => $mine === null ? null : ['no' => $mine + 1, 'stats' => $store['stats'][$mine] ?? null], 'all_stats' => $teacher ? (array)$store['stats'] : null];
}

// ---------------------------------------------------------------------------
// Liga za pololetí
// ---------------------------------------------------------------------------

function robots58_league(string $classId, string $semester): array
{
    $rows = [];
    foreach (robots58_matches_for_class($classId) as $match) {
        if (($match['semester'] ?? '') !== $semester || empty($match['results'])) continue;
        foreach ((array)$match['results']['robots'] as $robot) {
            $key = (string)$robot['key'];
            $place = robots58_place($match, $key);
            $rows[$key] ??= ['key' => $key, 'label' => (string)$robot['label'], 'matches' => 0, 'league' => 0, 'points' => 0, 'wins' => 0];
            $rows[$key]['matches']++;
            $rows[$key]['league'] += ROBOTS58_LEAGUE_POINTS[(int)$place] ?? ROBOTS58_LEAGUE_PARTICIPATION;
            $rows[$key]['points'] += (int)$robot['points'];
            $rows[$key]['wins'] += $place === 1 ? 1 : 0;
        }
    }
    $rows = array_values($rows);
    usort($rows, static fn(array $a, array $b): int => ($b['league'] <=> $a['league']) ?: ($b['points'] <=> $a['points']) ?: strcmp(sha1($a['key']), sha1($b['key'])));
    foreach ($rows as $i => $row) {
        $same = $i > 0 && $row['league'] === $rows[$i - 1]['league'] && $row['points'] === $rows[$i - 1]['points'];
        $rows[$i]['rank'] = $same ? $rows[$i - 1]['rank'] : $i + 1;
    }
    return $rows;
}

/** Liga pro žáka / projektor (soukromí podle posledního zápasu třídy, výchozí iniciály). */
function robots58_public_league(string $classId, string $semester, ?string $viewerKey, bool $teacher = false): array
{
    $latest = robots58_matches_for_class($classId)[0] ?? null;
    $mode = $teacher ? 'full' : (string)($latest['names'] ?? 'initials');
    $roster = robots58_roster($classId);
    $out = [];
    foreach (robots58_league($classId, $semester) as $i => $row) {
        $me = $viewerKey !== null && $row['key'] === $viewerKey;
        if (!$teacher && count($out) >= 15 && !$me) continue;
        $out[] = ['rank' => (int)$row['rank'], 'name' => robots58_public_name($mode, $row['key'], robots58_label($row['key'], $row['label'], $roster), $viewerKey, $i + 1), 'matches' => $row['matches'], 'league' => $row['league'], 'points' => $row['points'], 'wins' => $row['wins'], 'me' => $me];
    }
    if (!$teacher && !fair64_absolute_board_enabled('robots', $classId)) $out = fair64_only_me($out); // v64: žák vidí jen sebe a svou ligu
    return ['semester' => $semester, 'label' => robots58_semester_label($semester), 'rows' => $out];
}

// ---------------------------------------------------------------------------
// XP: vyzvednutí výhradně v požadavku žáka (profil žije v jeho session)
// ---------------------------------------------------------------------------

function robots58_xp_for(?int $place): int
{
    return $place === null ? 0 : ROBOTS58_XP_PARTICIPATION + (ROBOTS58_XP_PODIUM[$place] ?? 0);
}

/** 20 XP za účast + 30/20/10 za 1./2./3. místo (v týmech podle týmu); každý zápas jen jednou. Vrací počet připsaných zápasů. */
function robots58_claim_xp(string $classId, string $studentKey): int
{
    if ($studentKey === '' || !function_exists('learning_award_once')) return 0;
    $sessionOk = session_status() === PHP_SESSION_ACTIVE;
    $done = $sessionOk && is_array($_SESSION['robots58_xp'] ?? null) ? $_SESSION['robots58_xp'] : [];
    $awarded = 0;
    try {
        foreach (robots58_matches_for_class($classId) as $match) {
            $id = (string)$match['id'];
            if (isset($done[$id]) || empty($match['results'])) continue;
            $xp = robots58_xp_for(robots58_place($match, $studentKey));
            if ($xp > 0 && eco64_award($classId, $studentKey, 'robots58', 'robots58:' . $id, $xp)) $awarded++;
            $done[$id] = 1;
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v58 roboti XP: ' . $e->getMessage());
    }
    if ($sessionOk) $_SESSION['robots58_xp'] = $done;
    return $awarded;
}

// ---------------------------------------------------------------------------
// Trénink, stav pro žáka, rate limit
// ---------------------------------------------------------------------------

/** Trénink: skript žáka (roh vlevo nahoře) na tréninkové mapě, volitelně s cvičným soupeřem (ukázka „Sběrač“). */
function robots58_training(string $code, int $turns, int $mapNo, bool $sparring): array
{
    $turns = in_array($turns, ROBOTS58_TRAINING_TURNS, true) ? $turns : 150;
    $mapNo = max(1, min(ROBOTS58_TRAINING_MAPS, $mapNo));
    $parsed = robots58_parse($code, false);
    if (!$parsed['ok']) return ['ok' => false, 'parse' => $parsed['error'], 'error' => robots58_error_text($parsed['error'])];
    $entries = [['code' => $code, 'corner' => 0]];
    if ($sparring) $entries[] = ['code' => robots58_examples()['collector']['text'], 'corner' => 3];
    $sim = robots58_simulate(robots58_seed_of('training:' . $mapNo), $turns, $entries);
    $replay = $sim['replay'];
    $replay['robots'] = [['name' => tr('Ty'), 'team' => null, 'me' => true]];
    if ($sparring) $replay['robots'][] = ['name' => tr('Cvičný robot'), 'team' => null, 'me' => false];
    $replay['teams'] = [];
    $replay['title'] = tr('Trénink · mapa {n}', ['n' => $mapNo]);
    return ['ok' => true, 'replay' => $replay, 'me' => ['no' => 1, 'stats' => $sim['stats'][0]], 'sparring' => $sparring ? (int)$sim['stats'][1]['points'] : null, 'warnings' => $parsed['warnings'], 'ms' => $sim['ms']];
}

function robots58_team_info(array $match, string $studentKey): ?array
{
    $t = robots58_team_of($match, $studentKey);
    if ($t === null) return null;
    $team = $match['teams'][$t];
    $mode = (string)($match['names'] ?? 'initials');
    $roster = robots58_roster((string)$match['class_id']);
    $mates = [];
    if ($mode !== 'anon') {
        foreach ((array)$team['members'] as $key) {
            if ($key !== $studentKey) $mates[] = robots58_public_name($mode, (string)$key, robots58_label((string)$key, '', $roster), $studentKey, 0);
        }
    }
    return ['name' => (string)$team['name'], 'corner' => (int)$team['corner'], 'size' => count($team['members']), 'mates' => $mates];
}

/** Stav Robotí ligy pro žáka (API op=state i první vykreslení). Verze se mění jen se změnou obsahu. */
function robots58_student_state(string $classId, string $studentKey, int $now): array
{
    $matches = [];
    foreach (array_slice(robots58_matches_for_class($classId), 0, 12) as $m) {
        $status = robots58_status($m, $now);
        $finished = $status === 'finished';
        $subs = $finished ? [] : robots58_submissions((string)$m['id']);
        $mine = $subs[$studentKey] ?? null;
        $place = $finished ? robots58_place($m, $studentKey) : null;
        $matches[] = [
            'id' => (string)$m['id'], 'title' => (string)$m['title'], 'status' => $status, 'deadline' => (string)$m['deadline'], 'mode' => (string)$m['mode'],
            'turns' => (int)$m['turns'], 'submitted' => $finished ? (int)($m['results']['count'] ?? 0) : count($subs), 'played' => $place !== null, 'my' => $mine === null ? null : ['at' => (string)$mine['at'], 'n' => (int)($mine['n'] ?? 1), 'bytes' => (int)($mine['bytes'] ?? 0)],
            'team' => $m['mode'] === 'teams' ? robots58_team_info($m, $studentKey) : null,
            'place' => $place,
            'results' => $finished ? robots58_public_results($m, $studentKey) : null,
        ];
    }
    $state = ['matches' => $matches, 'league' => robots58_public_league($classId, robots58_semester($now), $studentKey)];
    $state['version'] = substr(sha1((string)json_encode($state)), 0, 16);
    return $state;
}

/** Klouzavé okno v session: nejvýš $max požadavků za $window s. */
function robots58_rate_ok(string $bucket, int $max, int $window, int $now): bool
{
    $list = is_array($_SESSION['robots58_rl'][$bucket] ?? null) ? $_SESSION['robots58_rl'][$bucket] : [];
    $list = array_values(array_filter($list, static fn($t): bool => is_int($t) && $t > $now - $window));
    if (count($list) >= $max) return false;
    $list[] = $now;
    $_SESSION['robots58_rl'][$bucket] = $list;
    return true;
}

// ---------------------------------------------------------------------------
// Učitel: data panelu, polling a POST akce (přihlášení ověřuje teacher.php)
// ---------------------------------------------------------------------------

/** Stav odevzdání pro učitele: celá jména, čas, počet pokusů, platnost; kód jen učiteli. */
function robots58_teacher_submissions(array $match): array
{
    $subs = robots58_submissions((string)$match['id']);
    $roster = robots58_roster((string)$match['class_id']);
    $rows = [];
    foreach (array_unique(array_merge(array_map('strval', array_keys($roster)), array_map('strval', array_keys($subs)))) as $key) {
        $sub = $subs[$key] ?? null;
        $team = robots58_team_of($match, $key);
        $parse = $sub !== null ? robots58_parse((string)$sub['code'], false) : null;
        $rows[] = ['name' => robots58_label($key, (string)($sub['label'] ?? ''), $roster), 'team' => $team !== null ? (string)$match['teams'][$team]['name'] : '', 'at' => $sub['at'] ?? null, 'n' => (int)($sub['n'] ?? 0), 'bytes' => (int)($sub['bytes'] ?? 0), 'valid' => $parse !== null && $parse['ok'], 'code' => $sub !== null ? (string)$sub['code'] : null];
    }
    usort($rows, static fn(array $a, array $b): int => (($b['at'] !== null) <=> ($a['at'] !== null)) ?: strnatcasecmp($a['name'], $b['name']));
    return $rows;
}

function robots58_poll_status(array $match, int $now): array
{
    $data = ['status' => robots58_status($match, $now), 'submitted' => count(robots58_submissions((string)$match['id'])), 'ran_at' => $match['ran_at'] ?? null];
    $data['version'] = substr(sha1((string)json_encode($data)), 0, 16);
    return $data;
}

function robots58_json_out(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/** GET teacher.php?tab=roboti&robots_poll=1&match=<id>&v=<verze> – projektor a panel učitele. */
function robots58_teacher_poll(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    $id = is_string($_GET['match'] ?? null) ? $_GET['match'] : '';
    $since = is_string($_GET['v'] ?? null) ? $_GET['v'] : '';
    $match = robots58_match($id);
    if ($match === null) { robots58_json_out(['ok' => false, 'error' => 'Zápas nebyl nalezen.'], 404); return; }
    $data = robots58_poll_status($match, robots58_now());
    robots58_json_out($since === $data['version'] ? ['ok' => true, 'changed' => false, 'version' => $data['version']] : ['ok' => true, 'changed' => true] + $data);
}

function robots58_teacher_handle_post(string $action, array $modules): void
{
    if (!robots58_csrf_ok($_POST['csrf'] ?? null)) throw new RuntimeException('Formulář vypršel. Obnov stránku a zkus to znovu.');
    $classIds = array_map('strval', array_keys($modules));
    $classId = is_string($_POST['class_id'] ?? null) ? $_POST['class_id'] : '';
    $matchId = is_string($_POST['match'] ?? null) ? $_POST['match'] : '';
    $now = robots58_now();
    $done = static function (string $message, string $cid, ?string $match = null): never {
        if (function_exists('teacher_flash')) teacher_flash($message);
        $params = ['tab' => 'roboti', 'class' => $cid] + ($match !== null ? ['match' => $match] : []);
        if (function_exists('teacher_redirect')) teacher_redirect($params);
        header('Location: teacher.php?' . http_build_query($params));
        exit;
    };
    $own = static function () use ($matchId, $classId, $classIds): array {
        if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
        $match = robots58_match($matchId);
        if ($match === null || (string)$match['class_id'] !== $classId) throw new RuntimeException('Zápas nebyl nalezen.');
        return $match;
    };
    switch ($action) {
        case 'robots58_create':
            $m = robots58_create_match($_POST, $classIds, $now);
            $done('Zápas „' . $m['title'] . '“ je otevřený. Žáci odevzdávají skripty do ' . date('j. n. H:i', (int)robots58_ts($m['deadline'])) . '.', $classId, (string)$m['id']);
        case 'robots58_close':
            $m = robots58_close_match((string)$own()['id'], $now);
            $done('Odevzdávání do „' . $m['title'] . '“ je uzavřené. Teď můžeš spustit simulaci.', $classId, (string)$m['id']);
        case 'robots58_run':
            $m = robots58_run_match((string)$own()['id'], $now);
            $done('Simulace hotová – robotů v aréně: ' . (int)$m['results']['count'] . ', ' . (int)$m['turns'] . ' tahů (' . (int)$m['results']['ms'] . ' ms). Pusť záznam na projektor.', $classId, (string)$m['id']);
        case 'robots58_delete':
            $m = $own();
            robots58_delete_match((string)$m['id']);
            $done('Zápas „' . $m['title'] . '“ byl smazán.', $classId);
        default:
            throw new RuntimeException('Neznámá akce Robotí ligy.');
    }
}

/** Pro navigaci (student_v55.php): nejnovější zápas třídy ve fázi přípravy, nebo null. Jen čte. */
function robots58_open_for_class(string $classId): ?array
{
    $now = robots58_now();
    foreach (robots58_matches_for_class($classId) as $match) {
        if (robots58_status($match, $now) === 'open') return ['id' => (string)$match['id'], 'title' => (string)$match['title'], 'deadline' => (string)$match['deadline']];
    }
    return null;
}
