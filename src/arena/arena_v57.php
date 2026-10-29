<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Aréna – třídní závod v Linux Labu (logika).
 *
 * Závod je přátelská časovaná soutěž třídy nad úrovněmi Labu. Všechny příkazy běží jen v simulátoru
 * linux_v57_* – nic se nespouští doopravdy. Body se počítají z událostí laboratoře
 * (storage/lab_v57_events.json.php, kind=solve, ctx=race:<id>) a jen do konce závodu.
 *
 * Úložiště: <storage>/arena_v57.json.php = {races:[…], classes:{<classId>:{lab_enabled}}}.
 * Každý zápis jde přes lab57_store_update (LOCK_EX drží čtení i zápis).
 * Soubor se načítá i bez bootstrap.php (CLI audit), proto jsou pomocníci aplikace chráněni function_exists.
 */

require_once __DIR__ . '/linux_v57_lab.php';
require_once __DIR__ . '/economy_v64.php'; // v64: společný strop XP z her

const ARENA57_ID_RE = '/^[a-z0-9]{6,32}$/';
const ARENA57_TEAM_NAMES = ['Tučňáci', 'Pakety', 'Jádra', 'Bajty', 'Routeři', 'Shelláci', 'Démoni', 'Pingři', 'Kořeny', 'Sokety', 'Rouráci', 'Kernelníci'];
const ARENA57_DURATION_MIN = 5;
const ARENA57_DURATION_MAX = 90;
const ARENA57_DURATION_CAP = 180;
const ARENA57_EXTEND_MIN = 5;
const ARENA57_MAX_LEVELS = 20;
const ARENA57_GOAL_MAX = 1000;
const ARENA57_STUCK_SECS = 480;
const ARENA57_STUCK_ACTIVE_SECS = 180;
const ARENA57_STUCK_MIN_CMDS = 3;
const ARENA57_XP_PARTICIPATION = 20;
const ARENA57_XP_PODIUM = [1 => 30, 2 => 20, 3 => 10];
const ARENA57_BOARD_TOP = 10;
const ARENA57_NAME_MODES = ['initials', 'full', 'anon'];
// v58 · ARN-06 (férové režimy): hodnocení závodu – výchozí žebříček, osobní rekord (jen vlastní výsledek
// a zlepšení proti minulým závodům, cizí pořadí nikde), nebo kategorie podle dřívějších bodů (jen sólo).
const ARENA57_RATING_MODES = ['zebricek', 'osobni_rekord', 'kategorie'];
const ARENA57_CATEGORY_COUNTS = [2, 3];

// ---------------------------------------------------------------------------
// Úložiště a čas
// ---------------------------------------------------------------------------

function arena57_path(): string
{
    return lab57_storage_dir() . '/arena_v57.json.php';
}

function arena57_now(): int
{
    return isset($GLOBALS['arena57_now_override']) && is_int($GLOBALS['arena57_now_override']) ? $GLOBALS['arena57_now_override'] : time();
}

function arena57_normalize(array $data): array
{
    $data['races'] = array_values(array_filter((array)($data['races'] ?? []), static fn($r): bool => is_array($r) && is_string($r['id'] ?? null)));
    $data['classes'] = is_array($data['classes'] ?? null) ? $data['classes'] : [];
    return $data;
}

function arena57_data(): array
{
    return arena57_normalize(lab57_store_read(arena57_path()));
}

/** Atomická úprava dat Arény (jeden zámek přes čtení i zápis). */
function arena57_update(callable $mutate): array
{
    return lab57_store_update(arena57_path(), static fn(array $d): array => arena57_normalize($mutate(arena57_normalize($d))));
}

function arena57_valid_id(string $raceId): bool
{
    return preg_match(ARENA57_ID_RE, $raceId) === 1;
}

function arena57_ts(mixed $iso): ?int
{
    if (!is_string($iso) || $iso === '') return null;
    $ts = strtotime($iso);
    return $ts === false ? null : $ts;
}

function arena57_iso(?int $ts): ?string
{
    return $ts === null ? null : date(DATE_ATOM, $ts);
}

/** Skutečný stav závodu: živý závod po uplynutí času je hotový, i když to ještě nikdo nezapsal. */
function arena57_status(array $race, int $now): string
{
    $status = (string)($race['status'] ?? 'draft');
    if ($status === 'live') {
        $end = arena57_ts($race['ends_at'] ?? null);
        if ($end !== null && $now >= $end) return 'finished';
    }
    return in_array($status, ['draft', 'live', 'finished'], true) ? $status : 'draft';
}

function arena57_races(): array
{
    return arena57_data()['races'];
}

function arena57_race(string $raceId): ?array
{
    if (!arena57_valid_id($raceId)) return null;
    foreach (arena57_races() as $race) {
        if ((string)$race['id'] === $raceId) return $race;
    }
    return null;
}

/** @return list<array> závody třídy, nejnovější první */
function arena57_races_for_class(string $classId): array
{
    $rows = array_values(array_filter(arena57_races(), static fn(array $r): bool => (string)($r['class_id'] ?? '') === $classId));
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $rows;
}

// ---------------------------------------------------------------------------
// Veřejné funkce volané integrací (index.php, student_v55.php, engine, API)
// ---------------------------------------------------------------------------

function arena57_lab_enabled(string $classId): bool
{
    $row = arena57_data()['classes'][$classId] ?? null;
    return !is_array($row) || !array_key_exists('lab_enabled', $row) || (bool)$row['lab_enabled'];
}

function arena57_set_lab_enabled(string $classId, bool $enabled): void
{
    arena57_update(static function (array $d) use ($classId, $enabled): array {
        $d['classes'][$classId] = ['lab_enabled' => $enabled] + (array)($d['classes'][$classId] ?? []);
        $d['classes'][$classId]['lab_enabled'] = $enabled;
        return $d;
    });
}

/** Živý závod třídy (nebo null). Závod, kterému vypršel čas, se tady rovnou zapíše jako hotový. */
function arena57_live_for_class(string $classId): ?array
{
    $now = arena57_now();
    $expired = false;
    $live = null;
    foreach (arena57_races() as $race) {
        if ((string)($race['class_id'] ?? '') !== $classId || (string)($race['status'] ?? '') !== 'live') continue;
        if (arena57_status($race, $now) === 'live') { $live ??= $race; continue; }
        $expired = true;
    }
    if ($expired) {
        try {
            arena57_update(static function (array $d) use ($now): array {
                foreach ($d['races'] as $i => $race) {
                    if ((string)($race['status'] ?? '') === 'live' && arena57_status($race, $now) === 'finished') $d['races'][$i]['status'] = 'finished';
                }
                return $d;
            });
        } catch (Throwable $e) {
            error_log('EDUCANET v57 aréna: ' . $e->getMessage());
        }
    }
    return $live;
}

function arena57_race_access(string $raceId, string $classId, string $levelId, int $now): ?string
{
    $race = arena57_race($raceId);
    if ($race === null) return tr('Tenhle závod neexistuje.');
    if ((string)$race['class_id'] !== $classId) return tr('Tenhle závod patří jiné třídě.');
    if (!in_array($levelId, (array)($race['levels'] ?? []), true)) return tr('Tahle úloha do závodu nepatří.');
    if (arena57_status($race, $now) === 'draft') return tr('Závod ještě nezačal.');
    return null;
}

function arena57_lock_active(string $raceId): bool
{
    $race = arena57_race($raceId);
    if ($race === null || empty($race['settings']['lock_nav']) || arena57_status($race, arena57_now()) !== 'live') return false;
    if (function_exists('current_class_id') && is_array($GLOBALS['modules'] ?? null)) {
        $classId = current_class_id($GLOBALS['modules']);
        if (is_string($classId) && $classId !== (string)$race['class_id']) return false;
    }
    return true;
}

// ---------------------------------------------------------------------------
// Úrovně a předvolby
// ---------------------------------------------------------------------------

function arena57_presets(): array
{
    $presets = [
        'rozcvicka' => ['title' => 'Rozcvička', 'lead' => 'Start 1–4: kde jsem, skryté soubory, složky.', 'levels' => ['start-1', 'start-2', 'start-3', 'start-4'], 'minutes' => 10],
        'pruzkumnik' => ['title' => 'Průzkumník', 'lead' => 'Hledání ukrytých kódů v souborovém systému.', 'levels' => ['quest-1', 'quest-2', 'quest-3', 'quest-7', 'quest-4'], 'minutes' => 20],
        'sit' => ['title' => 'Síťový detektiv', 'lead' => 'Adresa, brána, DNS – diagnostika krok za krokem.', 'levels' => ['sit-1', 'sit-2', 'sit-4', 'sit-5', 'sit-6'], 'minutes' => 25],
        'opravna' => ['title' => 'Opravna serverů', 'lead' => 'Najdi příčinu v logu a oprav službu.', 'levels' => ['opr-3', 'opr-5', 'opr-1', 'opr-4', 'opr-6'], 'minutes' => 30],
        'golf' => ['title' => 'Shell golf', 'lead' => 'Jeden řádek, přesný výstup, skryté testy.', 'levels' => ['golf-1', 'golf-4', 'golf-2', 'golf-5'], 'minutes' => 15],
        'mix' => ['title' => 'Mix', 'lead' => 'Od každého balíčku kousek.', 'levels' => ['start-2', 'quest-2', 'kody-1', 'sit-1', 'opr-3', 'golf-1'], 'minutes' => 20],
        'custom' => ['title' => 'Vlastní výběr', 'lead' => 'Vyber úlohy ručně (zaškrtávátka níže).', 'levels' => [], 'minutes' => 20],
    ];
    foreach ($presets as $id => $preset) {
        $presets[$id]['levels'] = array_values(array_filter($preset['levels'], static fn(string $l): bool => lab57_level($l) !== null));
    }
    return $presets;
}

function arena57_level_title(string $levelId): string
{
    $level = lab57_level($levelId);
    return $level === null ? $levelId : (string)$level['title'];
}

// ---------------------------------------------------------------------------
// Správa závodů (čisté funkce; teacher_handle_post je jen obaluje)
// ---------------------------------------------------------------------------

/**
 * Ověří vstup formuláře „nový závod“. Vyhazuje RuntimeException s českou zprávou.
 * @param array $in       POST pole (class_id, title, preset, levels[], duration_min, mode, team_size, hints, hint_penalty, lock_nav, names, class_goal)
 * @param list<string> $classIds platné třídy
 */
function arena57_parse_create(array $in, array $classIds): array
{
    $classId = is_string($in['class_id'] ?? null) ? $in['class_id'] : '';
    if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
    $presets = arena57_presets();
    $presetId = is_string($in['preset'] ?? null) ? $in['preset'] : 'custom';
    if (!isset($presets[$presetId])) throw new RuntimeException('Neznámá předvolba závodu.');
    if ($presetId === 'custom') {
        $levels = [];
        foreach ((array)($in['levels'] ?? []) as $raw) {
            if (!is_string($raw) || preg_match('/^[a-z0-9-]{2,32}$/', $raw) !== 1) throw new RuntimeException('Neplatné označení úlohy.');
            if (lab57_level($raw) === null) throw new RuntimeException('Úloha „' . $raw . '“ neexistuje.');
            if (!in_array($raw, $levels, true)) $levels[] = $raw;
        }
    } else {
        $levels = $presets[$presetId]['levels'];
    }
    if ($levels === []) throw new RuntimeException('Vyber aspoň jednu úlohu.');
    if (count($levels) > ARENA57_MAX_LEVELS) throw new RuntimeException('Závod může mít nejvýš ' . ARENA57_MAX_LEVELS . ' úloh.');
    $title = trim(preg_replace('/\s+/u', ' ', is_string($in['title'] ?? null) ? $in['title'] : '') ?? '');
    if ($title === '') $title = (string)$presets[$presetId]['title'];
    if (mb_strlen($title) > 80) throw new RuntimeException('Název závodu může mít nejvýš 80 znaků.');
    $duration = filter_var($in['duration_min'] ?? $presets[$presetId]['minutes'], FILTER_VALIDATE_INT);
    if (!is_int($duration) || $duration < ARENA57_DURATION_MIN || $duration > ARENA57_DURATION_MAX) throw new RuntimeException('Délka závodu musí být ' . ARENA57_DURATION_MIN . '–' . ARENA57_DURATION_MAX . ' minut.');
    $mode = (string)($in['mode'] ?? 'solo');
    if (!in_array($mode, ['solo', 'teams'], true)) throw new RuntimeException('Neplatný režim závodu.');
    $teamSize = filter_var($in['team_size'] ?? 3, FILTER_VALIDATE_INT);
    if (!is_int($teamSize) || $teamSize < 2 || $teamSize > 4) throw new RuntimeException('Velikost týmu musí být 2–4.');
    $penalty = filter_var($in['hint_penalty'] ?? 0.2, FILTER_VALIDATE_FLOAT);
    if (!is_float($penalty) || $penalty < 0.1 - 1e-9 || $penalty > 0.5 + 1e-9) throw new RuntimeException('Srážka za nápovědu musí být 10–50 %.');
    $names = (string)($in['names'] ?? 'initials');
    if (!in_array($names, ARENA57_NAME_MODES, true)) throw new RuntimeException('Neplatné zobrazení jmen.');
    $rating = (string)($in['rating'] ?? 'osobni_rekord'); // v64: absolutní žebříček je výchozí vypnutý (učitel zapne volbou „žebříček“)
    if (!in_array($rating, ARENA57_RATING_MODES, true)) throw new RuntimeException('Neplatné hodnocení závodu.');
    if ($rating === 'kategorie' && $mode !== 'solo') throw new RuntimeException('Kategorie fungují jen v režimu jednotlivců (ne týmů).');
    $categoryCount = filter_var($in['category_count'] ?? 3, FILTER_VALIDATE_INT);
    if (!is_int($categoryCount) || !in_array($categoryCount, ARENA57_CATEGORY_COUNTS, true)) $categoryCount = 3;
    $goalRaw = $in['class_goal'] ?? 0;
    $goal = ($goalRaw === '' || $goalRaw === null) ? 0 : filter_var($goalRaw, FILTER_VALIDATE_INT);
    if (!is_int($goal) || $goal < 0 || $goal > ARENA57_GOAL_MAX) throw new RuntimeException('Cíl třídy musí být 0–' . ARENA57_GOAL_MAX . ' vyřešených úloh.');
    $flag = static fn(string $k): bool => in_array((string)($in[$k] ?? ''), ['1', 'on', 'true', 'yes'], true);
    return [
        'class_id' => $classId, 'title' => $title, 'preset' => $presetId, 'levels' => $levels, 'mode' => $mode, 'team_size' => $teamSize, 'duration_min' => $duration,
        'settings' => ['hints' => $flag('hints'), 'hint_penalty' => round($penalty, 2), 'lock_nav' => $flag('lock_nav'), 'names' => $names, 'class_goal' => $goal, 'rating' => $rating, 'category_count' => $categoryCount],
    ];
}

function arena57_create_race(array $in, array $classIds, int $now): array
{
    $spec = arena57_parse_create($in, $classIds);
    $race = [
        'id' => bin2hex(random_bytes(6)), 'class_id' => $spec['class_id'], 'title' => $spec['title'], 'preset' => $spec['preset'], 'levels' => $spec['levels'],
        'mode' => $spec['mode'], 'team_size' => $spec['team_size'], 'teams' => [], 'categories' => [], 'hide_rank' => [], 'duration_min' => $spec['duration_min'], 'status' => 'draft',
        'created_at' => date(DATE_ATOM, $now), 'started_at' => null, 'ends_at' => null, 'settings' => $spec['settings'],
    ];
    arena57_update(static function (array $d) use ($race): array {
        $d['races'][] = $race;
        return $d;
    });
    return $race;
}

/** Soupiska třídy: klíč žáka => ['label' => celé jméno]. */
function arena57_roster(string $classId): array
{
    $override = $GLOBALS['arena57_roster_override'][$classId] ?? null;
    if (is_array($override)) return $override;
    if (!function_exists('project_students_for_class')) return [];
    try {
        return project_students_for_class($classId);
    } catch (Throwable $e) {
        error_log('EDUCANET v57 aréna soupiska: ' . $e->getMessage());
        return [];
    }
}

/**
 * Vyvážené týmy „hadím“ draftem podle bodů z procvičování (1-2-3-3-2-1…), aby silní nebyli spolu.
 * @param array<string,int> $pointsByKey
 */
function arena57_snake_teams(array $pointsByKey, int $teamSize): array
{
    $keys = array_keys($pointsByKey);
    shuffle($keys); // shoda bodů se rozhodne náhodně, ne podle abecedy
    usort($keys, static fn($a, $b): int => $pointsByKey[$b] <=> $pointsByKey[$a]);
    $count = max(1, (int)ceil(count($keys) / max(2, $teamSize)));
    $names = ARENA57_TEAM_NAMES;
    shuffle($names);
    $teams = [];
    for ($i = 0; $i < $count; $i++) {
        $name = $names[$i % count($names)] . ($i >= count($names) ? ' ' . (intdiv($i, count($names)) + 1) : '');
        $teams[] = ['id' => 't' . ($i + 1), 'name' => $name, 'members' => []];
    }
    foreach ($keys as $pos => $key) {
        $round = intdiv($pos, $count);
        $slot = $pos % $count;
        $teams[$round % 2 === 0 ? $slot : $count - 1 - $slot]['members'][] = (string)$key;
    }
    return $teams;
}

/**
 * v58 · ARN-06: rozdělení do 2–3 kategorií podle bodů z procvičování (souvislá pásma, ne promíchané jako týmy) –
 * žák soutěží s podobně zkušenými spolužáky, ne s celou třídou. Jen pro sólo závody s rating=kategorie.
 * @param array<string,int> $pointsByKey
 */
function arena57_snake_categories(array $pointsByKey, int $count): array
{
    $keys = array_keys($pointsByKey);
    shuffle($keys); // shoda bodů se rozhodne náhodně, ne podle abecedy
    usort($keys, static fn($a, $b): int => $pointsByKey[$b] <=> $pointsByKey[$a]);
    $n = count($keys);
    $count = max(1, min($count, max(1, $n)));
    $groups = [];
    for ($i = 0; $i < $count; $i++) {
        $from = (int)floor($n * $i / $count);
        $to = (int)floor($n * ($i + 1) / $count);
        $groups[] = ['id' => 'k' . ($i + 1), 'label' => 'Skupina ' . ($i + 1), 'members' => array_map('strval', array_slice($keys, $from, $to - $from))];
    }
    return array_values(array_filter($groups, static fn(array $g): bool => $g['members'] !== []));
}

function arena57_start_race(string $raceId, int $now, ?array $roster = null): array
{
    $race = arena57_race($raceId);
    if ($race === null) throw new RuntimeException('Závod nebyl nalezen.');
    if (arena57_status($race, $now) !== 'draft') throw new RuntimeException('Spustit jde jen připravený závod.');
    $teams = [];
    $categories = [];
    if ($race['mode'] === 'teams') {
        $roster ??= arena57_roster((string)$race['class_id']);
        if (count($roster) < 2) throw new RuntimeException('Na týmový závod jsou potřeba aspoň dva žáci na soupisce třídy.');
        $points = [];
        foreach (array_keys($roster) as $key) $points[(string)$key] = (int)(lab57_student_summary((string)$race['class_id'], (string)$key)['points'] ?? 0);
        $teams = arena57_snake_teams($points, (int)$race['team_size']);
    } elseif ((string)($race['settings']['rating'] ?? 'zebricek') === 'kategorie') {
        $roster ??= arena57_roster((string)$race['class_id']);
        if (count($roster) < 2) throw new RuntimeException('Na kategorie jsou potřeba aspoň dva žáci na soupisce třídy.');
        $points = [];
        foreach (array_keys($roster) as $key) $points[(string)$key] = (int)(lab57_student_summary((string)$race['class_id'], (string)$key)['points'] ?? 0);
        $categories = arena57_snake_categories($points, (int)($race['settings']['category_count'] ?? 3));
    }
    $result = null;
    arena57_update(static function (array $d) use ($raceId, $race, $now, $teams, $categories, &$result): array {
        foreach ($d['races'] as $other) {
            if ((string)$other['class_id'] === (string)$race['class_id'] && (string)$other['id'] !== $raceId && arena57_status($other, $now) === 'live') {
                throw new RuntimeException('Třída už má živý závod „' . (string)$other['title'] . '“. Nejdřív ho ukonči.');
            }
        }
        foreach ($d['races'] as $i => $row) {
            if ((string)$row['id'] !== $raceId) continue;
            if (arena57_status($row, $now) !== 'draft') throw new RuntimeException('Spustit jde jen připravený závod.');
            $row['status'] = 'live';
            $row['started_at'] = date(DATE_ATOM, $now);
            $row['ends_at'] = date(DATE_ATOM, $now + 60 * (int)$row['duration_min']);
            $row['teams'] = $teams;
            $row['categories'] = $categories;
            $d['races'][$i] = $result = $row;
        }
        return $d;
    });
    if ($result === null) throw new RuntimeException('Závod nebyl nalezen.');
    return $result;
}

/** Společná úprava jednoho závodu pod zámkem; $fn vrací upravený závod, nebo null = smazat. */
function arena57_mutate_race(string $raceId, int $now, callable $fn): ?array
{
    if (!arena57_valid_id($raceId)) throw new RuntimeException('Závod nebyl nalezen.');
    $found = false;
    $result = null;
    arena57_update(static function (array $d) use ($raceId, $now, $fn, &$found, &$result): array {
        foreach ($d['races'] as $i => $row) {
            if ((string)$row['id'] !== $raceId) continue;
            $found = true;
            $result = $fn($row, arena57_status($row, $now));
            if ($result === null) unset($d['races'][$i]); else $d['races'][$i] = $result;
            break;
        }
        return $d;
    });
    if (!$found) throw new RuntimeException('Závod nebyl nalezen.');
    return $result;
}

function arena57_stop_race(string $raceId, int $now): array
{
    return (array)arena57_mutate_race($raceId, $now, static function (array $row, string $status) use ($now): array {
        if ($status === 'finished' && (string)$row['status'] === 'live') { $row['status'] = 'finished'; return $row; }
        if ($status !== 'live') throw new RuntimeException('Ukončit jde jen běžící závod.');
        $row['status'] = 'finished';
        $row['ends_at'] = date(DATE_ATOM, $now);
        $row['stopped_early'] = true;
        return $row;
    });
}

function arena57_extend_race(string $raceId, int $now, int $minutes = ARENA57_EXTEND_MIN): array
{
    return (array)arena57_mutate_race($raceId, $now, static function (array $row, string $status) use ($minutes): array {
        if ($status !== 'live') throw new RuntimeException('Prodloužit jde jen běžící závod.');
        if ((int)$row['duration_min'] + $minutes > ARENA57_DURATION_CAP) throw new RuntimeException('Závod už nejde dál prodlužovat (nejvýš ' . ARENA57_DURATION_CAP . ' minut).');
        $row['duration_min'] = (int)$row['duration_min'] + $minutes;
        $row['ends_at'] = date(DATE_ATOM, (int)arena57_ts($row['ends_at']) + 60 * $minutes);
        return $row;
    });
}

function arena57_delete_race(string $raceId, int $now): void
{
    arena57_mutate_race($raceId, $now, static function (array $row, string $status): ?array {
        if ($status === 'live') throw new RuntimeException('Běžící závod nejde smazat – nejdřív ho ukonči.');
        return null;
    });
}

// ---------------------------------------------------------------------------
// Bodování
// ---------------------------------------------------------------------------

/** Události závodu (řešení, nápovědy, cizí kódy) do konce závodu, seřazené podle času. */
function arena57_race_events(array $race, ?array $events = null): array
{
    $events ??= lab57_events('race:' . (string)$race['id']);
    $ctx = 'race:' . (string)$race['id'];
    $end = arena57_ts($race['ends_at'] ?? null);
    $out = [];
    foreach ($events as $ev) {
        if (!is_array($ev) || ($ev['ctx'] ?? '') !== $ctx || (string)($ev['class_id'] ?? '') !== (string)$race['class_id']) continue;
        $at = arena57_ts($ev['at'] ?? null);
        if ($at === null || ($end !== null && $at > $end)) continue;
        $ev['ts'] = $at;
        $out[] = $ev;
    }
    usort($out, static fn(array $a, array $b): int => $a['ts'] <=> $b['ts']);
    return $out;
}

/** Započítaná řešení: první řešení každé úlohy závodu každým žákem. */
function arena57_counted_solves(array $race, array $raceEvents): array
{
    $levels = array_flip((array)($race['levels'] ?? []));
    $seen = [];
    $out = [];
    foreach ($raceEvents as $ev) {
        if (($ev['kind'] ?? '') !== 'solve' || !isset($levels[(string)($ev['level'] ?? '')])) continue;
        $key = (string)($ev['student_key'] ?? '') . '|' . (string)$ev['level'];
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = $ev;
    }
    return $out;
}

/** Pořadí: víc bodů vyhrává, při shodě dřívější poslední řešení. Řádky bez bodů pořadí nemají. */
function arena57_rank(array $rows): array
{
    usort($rows, static fn(array $a, array $b): int => ($b['points'] <=> $a['points']) ?: (($a['last'] ?: PHP_INT_MAX) <=> ($b['last'] ?: PHP_INT_MAX)) ?: strnatcasecmp((string)$a['label'], (string)$b['label']));
    $prev = null;
    foreach ($rows as $i => $row) {
        if ((int)$row['points'] <= 0) { $rows[$i]['rank'] = null; continue; }
        $same = $prev !== null && $prev['points'] === $row['points'] && $prev['last'] === $row['last'];
        $rows[$i]['rank'] = $same ? $prev['rank'] : $i + 1;
        $prev = $rows[$i];
    }
    return $rows;
}

function arena57_student_standings(array $solves): array
{
    $rows = [];
    foreach ($solves as $s) {
        $key = (string)($s['student_key'] ?? '');
        $rows[$key] ??= ['key' => $key, 'label' => '', 'points' => 0, 'solved' => 0, 'last' => 0, 'levels' => []];
        $rows[$key]['points'] += (int)($s['points'] ?? 0);
        $rows[$key]['solved']++;
        $rows[$key]['last'] = max($rows[$key]['last'], (int)$s['ts']);
        $rows[$key]['levels'][(string)$s['level']] = (int)($s['points'] ?? 0);
        if ((string)($s['label'] ?? '') !== '') $rows[$key]['label'] = (string)$s['label'];
    }
    return arena57_rank(array_values($rows));
}

/** Týmy: každá úloha se týmu počítá jednou – s nejvyšším bodovým ziskem mezi členy. */
function arena57_team_standings(array $race, array $solves): array
{
    $teamOf = [];
    $teams = [];
    foreach ((array)($race['teams'] ?? []) as $team) {
        $teams[(string)$team['id']] = ['id' => (string)$team['id'], 'label' => (string)$team['name'], 'members' => array_values((array)$team['members']), 'best' => []];
        foreach ((array)$team['members'] as $member) $teamOf[(string)$member] = (string)$team['id'];
    }
    foreach ($solves as $s) {
        $tid = $teamOf[(string)($s['student_key'] ?? '')] ?? null;
        if ($tid === null) continue;
        $lvl = (string)$s['level'];
        $cur = $teams[$tid]['best'][$lvl] ?? null;
        $pts = (int)($s['points'] ?? 0);
        if ($cur === null || $pts > $cur['points']) $teams[$tid]['best'][$lvl] = ['points' => $pts, 'ts' => (int)$s['ts']];
    }
    $rows = [];
    foreach ($teams as $team) {
        $points = array_sum(array_column($team['best'], 'points'));
        $last = $team['best'] === [] ? 0 : max(array_column($team['best'], 'ts'));
        $rows[] = ['key' => $team['id'], 'label' => $team['label'], 'members' => $team['members'], 'points' => (int)$points, 'solved' => count($team['best']), 'last' => (int)$last, 'levels' => array_map(static fn(array $b): int => $b['points'], $team['best'])];
    }
    return arena57_rank($rows);
}

/** v58 · ARN-06: každá kategorie má vlastní žebříček jednotlivců (žák soutěží jen se svou skupinou). */
function arena57_category_standings(array $race, array $solves): array
{
    $out = [];
    foreach ((array)($race['categories'] ?? []) as $cat) {
        $members = array_map('strval', (array)($cat['members'] ?? []));
        $catSolves = array_values(array_filter($solves, static fn(array $s): bool => in_array((string)($s['student_key'] ?? ''), $members, true)));
        $out[] = ['id' => (string)$cat['id'], 'label' => (string)($cat['label'] ?? ''), 'members' => $members, 'rows' => arena57_student_standings($catSolves)];
    }
    return $out;
}

/** Vše, co se počítá z událostí: řešení, pořadí žáků, týmů i kategorií, cíl třídy. */
function arena57_compute(array $race, ?array $events = null): array
{
    $raceEvents = arena57_race_events($race, $events);
    $solves = arena57_counted_solves($race, $raceEvents);
    $goal = (int)($race['settings']['class_goal'] ?? 0);
    return [
        'events' => $raceEvents,
        'solves' => $solves,
        'students' => arena57_student_standings($solves),
        'teams' => ($race['mode'] ?? 'solo') === 'teams' ? arena57_team_standings($race, $solves) : [],
        'categories' => (string)($race['settings']['rating'] ?? 'zebricek') === 'kategorie' ? arena57_category_standings($race, $solves) : [],
        'goal' => ['target' => $goal, 'done' => count($solves), 'pct' => $goal > 0 ? min(100, (int)floor(100 * count($solves) / $goal)) : 0],
    ];
}

// ---------------------------------------------------------------------------
// Jména podle nastavení soukromí
// ---------------------------------------------------------------------------

function arena57_full_label(string $key, string $eventLabel, array $roster): string
{
    $label = trim((string)($roster[$key]['label'] ?? ''));
    return $label !== '' ? $label : ($eventLabel !== '' ? $eventLabel : tr('Žák'));
}

/** Jméno pro žáky / projektor: initials = „Adam K.“, full = celé jméno, anon = jen divák vidí sebe, ostatní „Hráč N“. */
function arena57_public_name(string $mode, string $key, string $eventLabel, array $roster, ?int $rank, ?string $viewerKey, int $fallbackNo = 0): string
{
    $full = arena57_full_label($key, $eventLabel, $roster);
    if ($mode === 'full') return $full;
    if ($mode === 'anon' && ($viewerKey === null || $viewerKey !== $key)) return tr('Hráč {n}', ['n' => $rank ?? $fallbackNo]);
    return lab57_display_name($full);
}

function arena57_remaining(array $race, int $now): int
{
    if (arena57_status($race, $now) !== 'live') return 0;
    return max(0, (int)arena57_ts($race['ends_at'] ?? null) - $now);
}

function arena57_race_meta(array $race, int $now): array
{
    return [
        'id' => (string)$race['id'], 'title' => (string)$race['title'], 'status' => arena57_status($race, $now), 'mode' => (string)$race['mode'],
        'started_at' => $race['started_at'] ?? null, 'ends_at' => $race['ends_at'] ?? null, 'now' => $now, 'remaining' => arena57_remaining($race, $now),
        'duration_min' => (int)$race['duration_min'], 'names' => (string)($race['settings']['names'] ?? 'initials'), 'hints' => !empty($race['settings']['hints']),
        'lock_nav' => !empty($race['settings']['lock_nav']), 'levels' => count((array)$race['levels']),
    ];
}

/**
 * Veřejné (soukromí respektující) řádky pořadí; $viewerKey = null pro projektor.
 * v58 · ARN-06: žák, který zvolil „Nechci vidět pořadí“ (race.hide_rank[klíč]), zmizí z pořadí ostatních
 * (i na projektoru) – svůj vlastní řádek přitom pořád vidí, pokud se dívá sám na sebe.
 */
function arena57_public_rows(array $race, array $standings, array $roster, ?string $viewerKey, int $limit): array
{
    $mode = (string)($race['settings']['names'] ?? 'initials');
    $hidden = (array)($race['hide_rank'] ?? []);
    $rows = [];
    foreach ($standings as $row) {
        if ($row['rank'] === null) continue;
        $isMe = $viewerKey !== null && $row['key'] === $viewerKey;
        if (!$isMe && !empty($hidden[$row['key']])) continue;
        if (count($rows) >= $limit && !$isMe) continue;
        $rows[] = ['rank' => $row['rank'], 'name' => arena57_public_name($mode, $row['key'], $row['label'], $roster, $row['rank'], $viewerKey), 'points' => $row['points'], 'solved' => $row['solved'], 'last' => arena57_iso($row['last'] ?: null), 'me' => $isMe];
    }
    return $rows;
}

/** v58 · ARN-06: veřejné řádky kategorií (stejná pravidla soukromí jako arena57_public_rows). */
function arena57_public_categories(array $race, array $categories, array $roster, ?string $viewerKey): array
{
    $mode = (string)($race['settings']['names'] ?? 'initials');
    $hidden = (array)($race['hide_rank'] ?? []);
    $out = [];
    foreach ($categories as $cat) {
        $mine = $viewerKey !== null && in_array($viewerKey, (array)$cat['members'], true);
        $rows = [];
        foreach ($cat['rows'] as $row) {
            if ($row['rank'] === null) continue;
            $isMe = $viewerKey !== null && $row['key'] === $viewerKey;
            if (!$isMe && !empty($hidden[$row['key']])) continue;
            $rows[] = ['rank' => $row['rank'], 'name' => arena57_public_name($mode, $row['key'], $row['label'], $roster, $row['rank'], $viewerKey), 'points' => $row['points'], 'solved' => $row['solved'], 'me' => $isMe];
        }
        $out[] = ['id' => (string)$cat['id'], 'label' => (string)$cat['label'], 'size' => count((array)$cat['members']), 'mine' => $mine, 'rows' => $rows];
    }
    return $out;
}

function arena57_public_teams(array $race, array $teams, array $roster, ?string $viewerKey): array
{
    $mode = (string)($race['settings']['names'] ?? 'initials');
    $out = [];
    foreach ($teams as $team) {
        $mine = $viewerKey !== null && in_array($viewerKey, $team['members'], true);
        $members = [];
        if ($mine && $mode !== 'anon') {
            foreach ($team['members'] as $m) $members[] = arena57_public_name($mode, (string)$m, '', $roster, null, $viewerKey);
        }
        $out[] = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved'], 'size' => count($team['members']), 'me' => $mine, 'members' => $members];
    }
    return $out;
}

/** Feed „první krve“ (kdo první vyřešil úlohu) – se jmény podle soukromí. */
function arena57_public_feed(array $race, array $calc, array $roster, ?string $viewerKey, int $limit = 8): array
{
    $mode = (string)($race['settings']['names'] ?? 'initials');
    $rankOf = [];
    foreach ($calc['students'] as $row) $rankOf[$row['key']] = $row['rank'];
    // v58 ARN-06: kdo zvolil „Nechci vidět pořadí“, v přehledu „první krev“ pro ostatní nepoužije jméno (jen „spolužák“).
    $hidden = (array)($race['hide_rank'] ?? []);
    $feed = [];
    foreach (array_reverse($calc['solves']) as $s) {
        if (empty($s['first'])) continue;
        $key = (string)$s['student_key'];
        $isMe = $viewerKey !== null && $key === $viewerKey;
        $name = !empty($hidden[$key]) && !$isMe ? tr('Spolužák') : arena57_public_name($mode, $key, (string)($s['label'] ?? ''), $roster, $rankOf[$key] ?? null, $viewerKey);
        $feed[] = ['at' => (string)$s['at'], 'level' => arena57_level_title((string)$s['level']), 'name' => $name, 'me' => $isMe];
        if (count($feed) >= $limit) break;
    }
    return $feed;
}

/**
 * v58 · ARN-06: nejlepší bodový výsledek žáka v jiných DOKONČENÝCH závodech téže třídy (pro režim
 * „osobní rekord“ – „zlepšení proti svým minulým závodům“). Neprozrazuje nic o výsledcích spolužáků.
 */
function arena57_previous_best(string $classId, string $studentKey, string $excludeRaceId, int $now): array
{
    $best = 0;
    $title = null;
    foreach (arena57_races_for_class($classId) as $race) {
        if ((string)$race['id'] === $excludeRaceId || arena57_status($race, $now) !== 'finished') continue;
        foreach (arena57_compute($race)['students'] as $row) {
            if ($row['key'] === $studentKey && (int)$row['points'] > $best) { $best = (int)$row['points']; $title = (string)$race['title']; }
        }
    }
    return ['points' => $best, 'race_title' => $title];
}

/** Žebříček pro žáka (API op=board). v58 · ARN-06: respektuje rating (žebříček/osobní rekord/kategorie) a hide_rank. */
function arena57_board(string $raceId, string $viewerKey, string $classId): array
{
    $race = arena57_race($raceId);
    $now = arena57_now();
    if ($race === null || (string)$race['class_id'] !== $classId) return ['error' => tr('Závod nebyl nalezen.'), 'status' => 'missing', 'now' => $now];
    $calc = arena57_compute($race);
    $roster = arena57_roster($classId);
    $rating = (string)($race['settings']['rating'] ?? 'zebricek');
    $hideForViewer = $rating === 'osobni_rekord' || !empty($race['hide_rank'][$viewerKey]);
    $me = ['rank' => null, 'points' => 0, 'solved' => 0, 'levels' => []];
    foreach ($calc['students'] as $row) {
        if ($row['key'] === $viewerKey) { $me = ['rank' => $row['rank'], 'points' => $row['points'], 'solved' => $row['solved'], 'levels' => array_keys($row['levels'])]; break; }
    }
    $myTeam = null;
    foreach ($calc['teams'] as $team) {
        if (in_array($viewerKey, $team['members'], true)) { $myTeam = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved']]; break; }
    }
    if ($hideForViewer) {
        $me['rank'] = null;
        if ($myTeam !== null) $myTeam['rank'] = null;
    }
    return [
        'race' => arena57_race_meta($race, $now),
        'status' => arena57_status($race, $now), 'ends_at' => $race['ends_at'] ?? null, 'now' => $now, 'remaining' => arena57_remaining($race, $now),
        'rating' => $rating, 'rank_hidden' => $hideForViewer,
        // Osobní rekord / vlastní skrytí pořadí: cizí pořadí se neposílá vůbec – prázdné pole, ne jen skryté v UI.
        'rows' => $hideForViewer ? [] : arena57_public_rows($race, $calc['students'], $roster, $viewerKey, ARENA57_BOARD_TOP),
        'teams' => $hideForViewer ? [] : arena57_public_teams($race, $calc['teams'], $roster, $viewerKey),
        'categories' => $hideForViewer ? [] : arena57_public_categories($race, $calc['categories'], $roster, $viewerKey),
        'me' => $me, 'my_team' => $myTeam,
        'previous_best' => $rating === 'osobni_rekord' ? arena57_previous_best($classId, $viewerKey, $raceId, $now) : null,
        'goal' => $calc['goal'],
        'feed' => $hideForViewer ? [] : arena57_public_feed($race, $calc, $roster, $viewerKey),
        'participants' => count(array_filter($calc['students'], static fn(array $r): bool => $r['rank'] !== null)),
    ];
}

/**
 * v58 · ARN-06: přepne žákovu volbu „Nechci vidět pořadí“ pro tenhle závod (na sobě, jen vlastní CSRF požadavek).
 * @return bool nový stav (true = pořadí teď skryté)
 */
function arena57_toggle_hide_rank(string $raceId, string $classId, string $studentKey): bool
{
    if (!arena57_valid_id($raceId) || $studentKey === '') return false;
    $newState = false;
    arena57_update(static function (array $d) use ($raceId, $classId, $studentKey, &$newState): array {
        foreach ($d['races'] as $i => $race) {
            if ((string)$race['id'] !== $raceId || (string)$race['class_id'] !== $classId) continue;
            $hidden = (array)($race['hide_rank'] ?? []);
            $newState = empty($hidden[$studentKey]);
            if ($newState) $hidden[$studentKey] = true; else unset($hidden[$studentKey]);
            $d['races'][$i]['hide_rank'] = $hidden;
            break;
        }
        return $d;
    });
    return $newState;
}

// ---------------------------------------------------------------------------
// XP po skončení závodu
// ---------------------------------------------------------------------------

function arena57_took_part(array $race, string $studentKey, array $raceEvents): bool
{
    foreach ($raceEvents as $ev) {
        if ((string)($ev['student_key'] ?? '') === $studentKey) return true;
    }
    $start = arena57_ts($race['started_at'] ?? null) ?? 0;
    $end = arena57_ts($race['ends_at'] ?? null) ?? PHP_INT_MAX;
    foreach ((array)(lab57_student_summary((string)$race['class_id'], $studentKey)['log'] ?? []) as $row) {
        $t = arena57_ts($row['t'] ?? null);
        if (($row['ctx'] ?? '') === 'race:' . $race['id'] && $t !== null && $t >= $start && $t <= $end) return true;
    }
    return false;
}

/** XP za skončené závody: 20 za účast, +30/+20/+10 za 1./2./3. místo (v týmech podle týmu). Každý závod jen jednou. */
function arena57_award_finished_xp(string $classId, string $studentKey): void
{
    if ($studentKey === '' || !function_exists('learning_award_once')) return;
    $now = arena57_now();
    $sessionOk = session_status() === PHP_SESSION_ACTIVE;
    $done = $sessionOk && is_array($_SESSION['arena57_xp'] ?? null) ? $_SESSION['arena57_xp'] : [];
    try {
        foreach (arena57_races() as $race) {
            $raceId = (string)$race['id'];
            if ((string)$race['class_id'] !== $classId || isset($done[$raceId]) || arena57_status($race, $now) !== 'finished') continue;
            $calc = arena57_compute($race, lab57_events('race:' . $raceId));
            if (arena57_took_part($race, $studentKey, $calc['events'])) {
                $rank = null;
                $source = $race['mode'] === 'teams' ? $calc['teams'] : $calc['students'];
                foreach ($source as $row) {
                    $hit = $race['mode'] === 'teams' ? in_array($studentKey, $row['members'], true) : $row['key'] === $studentKey;
                    if ($hit) { $rank = $row['rank']; break; }
                }
                eco64_award($classId, $studentKey, 'arena57', 'v57:race:' . $raceId, ARENA57_XP_PARTICIPATION + (ARENA57_XP_PODIUM[(int)$rank] ?? 0));
            }
            $done[$raceId] = 1;
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v57 aréna XP: ' . $e->getMessage());
    }
    if ($sessionOk) $_SESSION['arena57_xp'] = $done;
}

function arena57_xp_for(array $race, ?int $rank): int
{
    return ARENA57_XP_PARTICIPATION + (ARENA57_XP_PODIUM[(int)$rank] ?? 0);
}

// ---------------------------------------------------------------------------
// Učitel: data živého panelu
// ---------------------------------------------------------------------------

function arena57_median(array $values): ?int
{
    if ($values === []) return null;
    sort($values);
    $n = count($values);
    return (int)round($n % 2 ? $values[intdiv($n, 2)] : ($values[$n / 2 - 1] + $values[$n / 2]) / 2);
}

/** Data pro živý panel učitele a projektor (učitel vidí celá jména, projektor jen blok public). */
function arena57_teacher_data(string $raceId, int $now): ?array
{
    $race = arena57_race($raceId);
    if ($race === null) return null;
    $classId = (string)$race['class_id'];
    $calc = arena57_compute($race);
    $roster = arena57_roster($classId);
    $status = arena57_status($race, $now);
    $start = arena57_ts($race['started_at'] ?? null);
    $ctx = 'race:' . $raceId;
    $name = static fn(string $key, string $label = ''): string => arena57_full_label($key, $label, $roster);

    $labels = [];
    foreach ($calc['events'] as $ev) $labels[(string)$ev['student_key']] = (string)($ev['label'] ?? '');
    $participants = array_unique(array_merge(array_map('strval', array_keys($roster)), array_keys($labels)));
    $byKey = [];
    foreach ($calc['students'] as $row) $byKey[$row['key']] = $row;

    $board = [];
    $stuck = [];
    foreach ($participants as $key) {
        $row = $byKey[$key] ?? ['key' => $key, 'label' => $labels[$key] ?? '', 'points' => 0, 'solved' => 0, 'last' => 0, 'rank' => null, 'levels' => []];
        $cmds = [];
        if ($start !== null) {
            $end = min($now, arena57_ts($race['ends_at'] ?? null) ?? $now);
            foreach ((array)(lab57_student_summary($classId, $key)['log'] ?? []) as $log) {
                $t = arena57_ts($log['t'] ?? null);
                if (($log['ctx'] ?? '') === $ctx && $t !== null && $t >= $start && $t <= $end) $cmds[] = $log + ['ts' => $t];
            }
        }
        $lastCmd = $cmds === [] ? null : end($cmds);
        $board[] = ['rank' => $row['rank'], 'name' => $name($key, (string)$row['label']), 'points' => $row['points'], 'solved' => $row['solved'], 'last' => arena57_iso($row['last'] ?: null), 'cmds' => count($cmds), 'active' => $cmds !== [] || $row['solved'] > 0, 'level' => $lastCmd ? arena57_level_title((string)$lastCmd['lvl']) : null];
        if ($status === 'live' && $start !== null && $lastCmd !== null) {
            $progressAt = max($start, (int)$row['last']);
            $since = array_values(array_filter($cmds, static fn(array $c): bool => $c['ts'] > $progressAt));
            if ($now - $progressAt >= ARENA57_STUCK_SECS && count($since) >= ARENA57_STUCK_MIN_CMDS && $now - (int)$lastCmd['ts'] <= ARENA57_STUCK_ACTIVE_SECS) {
                $stuck[] = [
                    'name' => $name($key, (string)$row['label']), 'minutes' => intdiv($now - $progressAt, 60), 'cmds' => count($since),
                    'failed' => count(array_filter($since, static fn(array $c): bool => (int)($c['exit'] ?? 0) !== 0)),
                    'level' => arena57_level_title((string)$lastCmd['lvl']), 'recent' => array_map(static fn(array $c): string => (string)$c['cmd'], array_slice($since, -3)),
                ];
            }
        }
    }
    usort($board, static fn(array $a, array $b): int => (($a['rank'] ?? PHP_INT_MAX) <=> ($b['rank'] ?? PHP_INT_MAX)) ?: ((int)$b['active'] <=> (int)$a['active']) ?: strnatcasecmp($a['name'], $b['name']));
    usort($stuck, static fn(array $a, array $b): int => $b['minutes'] <=> $a['minutes']);

    $levels = [];
    foreach ((array)$race['levels'] as $levelId) {
        $solves = array_values(array_filter($calc['solves'], static fn(array $s): bool => (string)$s['level'] === (string)$levelId));
        $hints = count(array_filter($calc['events'], static fn(array $e): bool => ($e['kind'] ?? '') === 'hint' && (string)($e['level'] ?? '') === (string)$levelId));
        $first = $solves[0] ?? null;
        $levels[] = ['id' => (string)$levelId, 'title' => arena57_level_title((string)$levelId), 'solves' => count($solves), 'median_secs' => arena57_median(array_map(static fn(array $s): int => (int)($s['secs'] ?? 0), $solves)), 'hints' => $hints, 'first' => $first ? $name((string)$first['student_key'], (string)($first['label'] ?? '')) : null];
    }

    $alerts = [];
    $feed = [];
    foreach (array_reverse($calc['events']) as $ev) {
        $kind = (string)($ev['kind'] ?? '');
        $item = ['at' => (string)$ev['at'], 'kind' => $kind, 'name' => $name((string)$ev['student_key'], (string)($ev['label'] ?? '')), 'level' => arena57_level_title((string)($ev['level'] ?? '')), 'points' => (int)($ev['points'] ?? 0), 'first' => !empty($ev['first'])];
        if ($kind === 'foreign_code') $alerts[] = $item;
        if (count($feed) < 20) $feed[] = $item;
    }

    $publicTeams = [];
    foreach ($calc['teams'] as $team) {
        $publicTeams[] = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved'], 'size' => count($team['members'])];
    }
    $teams = [];
    foreach ($calc['teams'] as $team) {
        $teams[] = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved'], 'members' => array_map(static fn($m): string => $name((string)$m, $labels[(string)$m] ?? ''), $team['members'])];
    }

    // v58 · ARN-06: kategorie s celými jmény pro učitele (žák je vidí jen s iniciálami přes arena57_public_categories).
    $categories = [];
    foreach ($calc['categories'] as $cat) {
        $categories[] = [
            'id' => $cat['id'], 'label' => $cat['label'], 'size' => count($cat['members']),
            'rows' => array_map(static fn(array $r): array => ['rank' => $r['rank'], 'name' => $name($r['key'], (string)$r['label']), 'points' => $r['points'], 'solved' => $r['solved']], array_values(array_filter($cat['rows'], static fn(array $r): bool => $r['rank'] !== null))),
        ];
    }

    return [
        'ok' => true,
        'race' => arena57_race_meta($race, $now) + ['class_id' => $classId, 'rating' => (string)($race['settings']['rating'] ?? 'zebricek')],
        'remaining' => arena57_remaining($race, $now),
        'board' => $board,
        'teams' => $teams,
        'categories' => $categories,
        'levels' => $levels,
        'stuck' => $stuck,
        'alerts' => array_slice($alerts, 0, 20),
        'feed' => $feed,
        'goal' => $calc['goal'],
        'public' => [
            'rating' => (string)($race['settings']['rating'] ?? 'zebricek'),
            'rows' => arena57_public_rows($race, $calc['students'], $roster, null, ARENA57_BOARD_TOP),
            'teams' => $publicTeams,
            'categories' => arena57_public_categories($race, $calc['categories'], $roster, null),
            'feed' => arena57_public_feed($race, $calc, $roster, null, 6),
            'goal' => $calc['goal'],
        ],
    ];
}

function arena57_json_out(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/** GET teacher.php?tab=arena&arena_poll=1&race=<id> (přihlášení ověřuje teacher.php). */
function arena57_teacher_poll(): void
{
    $raceId = is_string($_GET['race'] ?? null) ? $_GET['race'] : '';
    try {
        $data = arena57_valid_id($raceId) ? arena57_teacher_data($raceId, arena57_now()) : null;
    } catch (Throwable $e) {
        error_log('EDUCANET v57 aréna poll: ' . $e->getMessage());
        arena57_json_out(['ok' => false, 'error' => tr('Data závodu se nepodařilo načíst.')], 500);
        return;
    }
    if ($data === null) { arena57_json_out(['ok' => false, 'error' => tr('Závod nebyl nalezen.')], 404); return; }
    arena57_json_out($data);
}

// ---------------------------------------------------------------------------
// Učitel: POST akce (CSRF ověřil teacher.php)
// ---------------------------------------------------------------------------

function arena57_teacher_handle_post(string $action, array $modules): void
{
    // v58 · ARN-01: týdenní hádanka má vlastní akce (jiný tvar dat), jen se sem zavěsí za stejný dispatch.
    if (str_starts_with($action, 'arena58_weekly_')) {
        if (function_exists('arena58_weekly_teacher_handle_post')) { arena58_weekly_teacher_handle_post($action, $modules); }
        return;
    }
    $classIds = array_map('strval', array_keys($modules));
    $classId = is_string($_POST['class_id'] ?? null) ? $_POST['class_id'] : '';
    $raceId = is_string($_POST['race'] ?? null) ? $_POST['race'] : '';
    $now = arena57_now();
    $label = static fn(string $cid): string => function_exists('teacher_class_label') ? teacher_class_label($cid) : $cid;
    $done = static function (string $message, string $cid, ?string $race = null, string $type = 'ok'): never {
        if (function_exists('teacher_flash')) teacher_flash($message, $type);
        $params = ['tab' => 'arena', 'class' => $cid];
        if ($race !== null) $params['race'] = $race;
        if (function_exists('teacher_redirect')) teacher_redirect($params);
        exit;
    };
    $raceOfClass = static function () use ($raceId, $classId, $classIds): array {
        if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
        $race = arena57_race($raceId);
        if ($race === null || (string)$race['class_id'] !== $classId) throw new RuntimeException('Závod nebyl nalezen.');
        return $race;
    };

    switch ($action) {
        case 'arena57_create':
            $race = arena57_create_race($_POST, $classIds, $now);
            if (!empty($_POST['start_now'])) {
                $race = arena57_start_race((string)$race['id'], $now);
                $done('Závod „' . $race['title'] . '“ běží! Konec v ' . date('H:i', (int)arena57_ts($race['ends_at'])) . '.', $classId, (string)$race['id']);
            }
            $done('Závod „' . $race['title'] . '“ je připravený. Spusť ho, až bude třída připravená.', $classId, (string)$race['id']);
        case 'arena57_start':
            $race = $raceOfClass();
            $race = arena57_start_race((string)$race['id'], $now);
            $done('Závod „' . $race['title'] . '“ běží! Konec v ' . date('H:i', (int)arena57_ts($race['ends_at'])) . '.' . ($race['mode'] === 'teams' ? ' Týmy: ' . count($race['teams']) . '.' : ''), $classId, (string)$race['id']);
        case 'arena57_stop':
            $race = arena57_stop_race((string)$raceOfClass()['id'], $now);
            $done('Závod „' . $race['title'] . '“ je ukončený. Výsledky vidí žáci hned.', $classId, (string)$race['id']);
        case 'arena57_extend':
            $race = arena57_extend_race((string)$raceOfClass()['id'], $now);
            $done('Závod prodloužen o ' . ARENA57_EXTEND_MIN . ' minut (konec v ' . date('H:i', (int)arena57_ts($race['ends_at'])) . ').', $classId, (string)$race['id']);
        case 'arena57_delete':
            $race = $raceOfClass();
            arena57_delete_race((string)$race['id'], $now);
            $done('Závod „' . $race['title'] . '“ byl smazán.', $classId);
        case 'arena57_lab_toggle':
            if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
            $enabled = (string)($_POST['enabled'] ?? '') === '1';
            arena57_set_lab_enabled($classId, $enabled);
            $done('Linux Lab je pro ' . $label($classId) . ' ' . ($enabled ? 'zapnutý.' : 'vypnutý.'), $classId);
        default:
            throw new RuntimeException('Neznámá akce Arény.');
    }
}
