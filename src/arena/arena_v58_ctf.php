<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Aréna – CTF týden (ARN-02, inspirace picoCTF).
 *
 * Sezónní soutěž (výchozí 2 týdny) nad balíčkem úloh „ctf“ (5 kategorií, registrace v linux_v58_levels_ctf.php).
 * Jedna „událost“ (CTF týden) může běžet pro víc tříd zároveň, ale KAŽDÁ třída má vlastní žebříček, vlastní
 * týmy a vlastní pořadí řešitelů – různé třídy mají různé osnovy, srovnávat je mezi sebou by nebylo fér.
 * Sdílené je jen zadání úloh a časové okno.
 *
 * Body: základ z úrovně (level.points) × podíl podle pořadí řešitelů VE TŘÍDĚ (dynamické bodování – čím dřív,
 * tím víc) mínus srážka za nápovědy, nikdy méň než 40 % základu. Kdo úlohu vyřeší jako úplně první ve všech
 * zapojených třídách, dostane navíc bonus „první krev“ ×1,25 (řeší to jádro Labu přes first_blood kontextu).
 * Přesné pořadí řešitelů se dopočítává až PO zápisu události (viz arena58_ctf_on_complete) – čte se ze
 * stejného souboru pod stejným zámkem, takže dvě řešení „naráz“ nikdy nedostanou stejné neplatné pořadí.
 *
 * Úložiště: <storage>/arena_v58_ctf.json.php = {events:[…]}. Zápisy jen přes lab57_store_update (LOCK_EX).
 * Události řešení: storage/linux_v58/ctx_ctf_<id>.events.json.php (výchozí cesta jádra pro kontext ctf:<id>).
 */

require_once __DIR__ . '/linux_v57_lab.php';
require_once __DIR__ . '/economy_v64.php';
require_once __DIR__ . '/arena_v64_fair.php';
// arena57_roster()/arena57_snake_teams()/arena57_public_name() jsou zamčené API (§6 V58_PLAN) – voláme je,
// ale nikdy je neupravujeme. Načteno defenzivně (stejný vzor jako lab_v57_api.php/teacher.php), aby modul
// fungoval i ve chvíli, kdy by ho něco natáhlo bez arena_v57.php v cestě (např. izolovaný audit/test).
if (is_file(__DIR__ . '/arena_v57.php')) require_once __DIR__ . '/arena_v57.php';

const ARENA58_CTF_ID_RE = '/^[a-z0-9]{6,32}$/';
const ARENA58_CTF_PACK = 'ctf';
// Balíček úloh CTF je „schovaný“ z běžného procvičování – patří jen třídě, která nikdy neexistuje.
// V kontextu ctf:<id> to neomezuje (lab57_access_error tam volá jen arena58_ctf_access), takže úlohy
// jsou dostupné výhradně přes CTF týden, ne omylem v Labu kdykoli.
const ARENA58_CTF_HIDDEN_CLASS = 'zzz_nikdy_trida_ctf58';
const ARENA58_CTF_DAYS_MIN = 1;
const ARENA58_CTF_DAYS_MAX = 21;
const ARENA58_CTF_DAYS_DEFAULT = 14;
const ARENA58_CTF_TEAM_MIN = 2;
const ARENA58_CTF_TEAM_MAX = 4;
const ARENA58_CTF_FLOOR = 0.4;
const ARENA58_CTF_HINT_MIN = 0.1;
const ARENA58_CTF_HINT_MAX = 0.5;
const ARENA58_CTF_FREEZE_OPTIONS = [0, 10, 30, 60, 120];
const ARENA58_CTF_NAME_MODES = ['initials', 'full', 'anon'];
const ARENA58_CTF_BOARD_TOP = 15;
const ARENA58_CTF_XP_PARTICIPATION = 20;
const ARENA58_CTF_XP_PODIUM = [1 => 30, 2 => 20, 3 => 10];

/** Popisky kategorií pro žákovský i učitelský pohled (klíč = level['category']). */
const ARENA58_CTF_CATEGORIES = [
    'forenzni' => ['label' => 'Forenzní analýza', 'icon' => '🔍', 'lead' => 'Logy, časová osa, stopy v systému.'],
    'kodovani' => ['label' => 'Kódování', 'icon' => '🔐', 'lead' => 'Base64, hex, ROT13, Caesar, XOR.'],
    'web' => ['label' => 'Web', 'icon' => '🌐', 'lead' => 'curl na cvičný server – hlavičky, cookies, skryté cesty.'],
    'linux' => ['label' => 'Linux', 'icon' => '🐧', 'lead' => 'Práva, find, grep, procesy.'],
    'site' => ['label' => 'Sítě', 'icon' => '📡', 'lead' => 'dig, traceroute, ss, porty.'],
];

// ---------------------------------------------------------------------------
// Úložiště a čas
// ---------------------------------------------------------------------------

function arena58_ctf_path(): string
{
    return lab57_storage_dir() . '/arena_v58_ctf.json.php';
}

function arena58_ctf_now(): int
{
    return isset($GLOBALS['arena58_ctf_now_override']) && is_int($GLOBALS['arena58_ctf_now_override']) ? $GLOBALS['arena58_ctf_now_override'] : time();
}

function arena58_ctf_normalize(array $data): array
{
    $data['events'] = array_values(array_filter((array)($data['events'] ?? []), static fn($e): bool => is_array($e) && is_string($e['id'] ?? null)));
    return $data;
}

function arena58_ctf_data(): array
{
    return arena58_ctf_normalize(lab57_store_read(arena58_ctf_path()));
}

function arena58_ctf_update(callable $mutate): array
{
    return lab57_store_update(arena58_ctf_path(), static fn(array $d): array => arena58_ctf_normalize($mutate(arena58_ctf_normalize($d))));
}

function arena58_ctf_valid_id(string $id): bool
{
    return preg_match(ARENA58_CTF_ID_RE, $id) === 1;
}

function arena58_ctf_ts(mixed $iso): ?int
{
    if (!is_string($iso) || $iso === '') return null;
    $ts = strtotime($iso);
    return $ts === false ? null : $ts;
}

function arena58_ctf_iso(?int $ts): ?string
{
    return $ts === null ? null : date(DATE_ATOM, $ts);
}

function arena58_ctf_status(array $event, int $now): string
{
    $status = (string)($event['status'] ?? 'draft');
    if ($status === 'live') {
        $end = arena58_ctf_ts($event['ends_at'] ?? null);
        if ($end !== null && $now >= $end) return 'finished';
    }
    return in_array($status, ['draft', 'live', 'finished'], true) ? $status : 'draft';
}

function arena58_ctf_events(): array
{
    return arena58_ctf_data()['events'];
}

function arena58_ctf_event(string $id): ?array
{
    if (!arena58_ctf_valid_id($id)) return null;
    foreach (arena58_ctf_events() as $event) {
        if ((string)$event['id'] === $id) return $event;
    }
    return null;
}

/** @return list<array> události, kde je třída zapojená, nejnovější první */
function arena58_ctf_events_for_class(string $classId): array
{
    $rows = array_values(array_filter(arena58_ctf_events(), static fn(array $e): bool => in_array($classId, (array)($e['class_ids'] ?? []), true)));
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $rows;
}

/** Nejrelevantnější akce pro žáka: běžící > naposledy skončená > připravovaná. Zastaralý „live“ se rovnou zapíše jako finished. */
function arena58_ctf_live_for_class(string $classId): ?array
{
    $now = arena58_ctf_now();
    $expired = false;
    foreach (arena58_ctf_events() as $event) {
        if (in_array($classId, (array)($event['class_ids'] ?? []), true) && (string)($event['status'] ?? '') === 'live' && arena58_ctf_status($event, $now) === 'finished') { $expired = true; break; }
    }
    if ($expired) {
        try {
            arena58_ctf_update(static function (array $d) use ($now): array {
                foreach ($d['events'] as $i => $event) {
                    if ((string)($event['status'] ?? '') === 'live' && arena58_ctf_status($event, $now) === 'finished') $d['events'][$i]['status'] = 'finished';
                }
                return $d;
            });
        } catch (Throwable $e) {
            error_log('EDUCANET v58 CTF: ' . $e->getMessage());
        }
    }
    $events = arena58_ctf_events_for_class($classId);
    foreach ($events as $event) if (arena58_ctf_status($event, $now) === 'live') return $event;
    foreach ($events as $event) if (arena58_ctf_status($event, $now) === 'finished') return $event;
    return $events[0] ?? null;
}

// ---------------------------------------------------------------------------
// Úlohy balíčku (registruje linux_v58_levels_ctf.php)
// ---------------------------------------------------------------------------

/** @return list<array> všechny úlohy CTF balíčku, v pořadí zadání (no) */
function arena58_ctf_levels(): array
{
    return function_exists('lab57_pack_levels') ? lab57_pack_levels(ARENA58_CTF_PACK) : [];
}

/** @return list<string> */
function arena58_ctf_all_level_ids(): array
{
    return array_map(static fn(array $l): string => (string)$l['id'], arena58_ctf_levels());
}

/** @return list<string> ID úloh patřících do konkrétní akce (snímek z okamžiku vytvoření) */
function arena58_ctf_level_ids(array $event): array
{
    return array_values(array_map('strval', (array)($event['levels'] ?? [])));
}

// ---------------------------------------------------------------------------
// Vytvoření a správa akce
// ---------------------------------------------------------------------------

/**
 * @param array $in POST pole: title, class_ids[], mode, team_size, duration_days, hints, hint_penalty, names, freeze_before_end_min
 * @param list<string> $classIds platné třídy
 */
function arena58_ctf_parse_create(array $in, array $classIds): array
{
    $selected = array_values(array_unique(array_map('strval', (array)($in['class_ids'] ?? []))));
    $selected = array_values(array_filter($selected, static fn(string $c): bool => in_array($c, $classIds, true)));
    if ($selected === []) throw new RuntimeException('Vyber aspoň jednu třídu.');
    $levels = arena58_ctf_all_level_ids();
    if ($levels === []) throw new RuntimeException('CTF balíček zatím nemá žádné úlohy.');
    $title = trim((string)preg_replace('/\s+/u', ' ', is_string($in['title'] ?? null) ? $in['title'] : '') ?? '');
    if ($title === '') $title = 'CTF týden';
    if (mb_strlen($title) > 80) throw new RuntimeException('Název akce může mít nejvýš 80 znaků.');
    $mode = (string)($in['mode'] ?? 'solo');
    if (!in_array($mode, ['solo', 'teams'], true)) throw new RuntimeException('Neplatný režim.');
    $teamSize = filter_var($in['team_size'] ?? 3, FILTER_VALIDATE_INT);
    if (!is_int($teamSize) || $teamSize < ARENA58_CTF_TEAM_MIN || $teamSize > ARENA58_CTF_TEAM_MAX) throw new RuntimeException('Velikost týmu musí být ' . ARENA58_CTF_TEAM_MIN . '–' . ARENA58_CTF_TEAM_MAX . '.');
    $days = filter_var($in['duration_days'] ?? ARENA58_CTF_DAYS_DEFAULT, FILTER_VALIDATE_INT);
    if (!is_int($days) || $days < ARENA58_CTF_DAYS_MIN || $days > ARENA58_CTF_DAYS_MAX) throw new RuntimeException('Délka akce musí být ' . ARENA58_CTF_DAYS_MIN . '–' . ARENA58_CTF_DAYS_MAX . ' dní.');
    $penalty = filter_var($in['hint_penalty'] ?? 0.2, FILTER_VALIDATE_FLOAT);
    if (!is_float($penalty) || $penalty < ARENA58_CTF_HINT_MIN - 1e-9 || $penalty > ARENA58_CTF_HINT_MAX + 1e-9) throw new RuntimeException('Srážka za nápovědu musí být 10–50 %.');
    $names = (string)($in['names'] ?? 'initials');
    if (!in_array($names, ARENA58_CTF_NAME_MODES, true)) throw new RuntimeException('Neplatné zobrazení jmen.');
    $freeze = filter_var($in['freeze_before_end_min'] ?? 0, FILTER_VALIDATE_INT);
    if (!is_int($freeze) || !in_array($freeze, ARENA58_CTF_FREEZE_OPTIONS, true)) throw new RuntimeException('Neplatné zmrazení žebříčku.');
    $flag = static fn(string $k): bool => in_array((string)($in[$k] ?? ''), ['1', 'on', 'true', 'yes'], true);
    return [
        'title' => $title, 'class_ids' => $selected, 'levels' => $levels, 'mode' => $mode, 'team_size' => $teamSize, 'duration_days' => $days,
        'settings' => ['hints' => $flag('hints'), 'hint_penalty' => round($penalty, 2), 'names' => $names, 'freeze_before_end_min' => $freeze],
    ];
}

function arena58_ctf_create_event(array $in, array $classIds, int $now): array
{
    $spec = arena58_ctf_parse_create($in, $classIds);
    $event = [
        'id' => bin2hex(random_bytes(6)), 'title' => $spec['title'], 'class_ids' => $spec['class_ids'], 'levels' => $spec['levels'],
        'mode' => $spec['mode'], 'team_size' => $spec['team_size'], 'teams' => [], 'duration_days' => $spec['duration_days'], 'status' => 'draft',
        'created_at' => date(DATE_ATOM, $now), 'starts_at' => null, 'ends_at' => null, 'settings' => $spec['settings'],
    ];
    arena58_ctf_update(static function (array $d) use ($event): array {
        $d['events'][] = $event;
        return $d;
    });
    return $event;
}

/** Vyrovnané týmy pro každou zapojenou třídu zvlášť (hadí draft podle bodů v Labu – shodné s Arénou). */
function arena58_ctf_draft_teams(array $event): array
{
    if (!function_exists('arena57_roster') || !function_exists('arena57_snake_teams')) return [];
    $teams = [];
    foreach ((array)$event['class_ids'] as $classId) {
        $roster = arena57_roster((string)$classId);
        if (count($roster) < 2) { $teams[$classId] = []; continue; }
        $points = [];
        foreach (array_keys($roster) as $key) $points[(string)$key] = (int)(lab57_student_summary((string)$classId, (string)$key)['points'] ?? 0);
        $teams[$classId] = arena57_snake_teams($points, (int)$event['team_size']);
    }
    return $teams;
}

function arena58_ctf_start_event(string $id, int $now): array
{
    $event = arena58_ctf_event($id);
    if ($event === null) throw new RuntimeException('CTF akce nebyla nalezena.');
    if (arena58_ctf_status($event, $now) !== 'draft') throw new RuntimeException('Spustit jde jen připravovaná akce.');
    $teams = $event['mode'] === 'teams' ? arena58_ctf_draft_teams($event) : [];
    if ($event['mode'] === 'teams') {
        foreach ($teams as $classId => $classTeams) {
            if ($classTeams === []) throw new RuntimeException('Na týmy je ve třídě ' . (function_exists('teacher_class_label') ? teacher_class_label((string)$classId) : $classId) . ' málo žáků na soupisce.');
        }
    }
    $result = null;
    arena58_ctf_update(static function (array $d) use ($id, $now, $teams, &$result): array {
        foreach ($d['events'] as $i => $row) {
            if ((string)$row['id'] !== $id) continue;
            if (arena58_ctf_status($row, $now) !== 'draft') throw new RuntimeException('Spustit jde jen připravovaná akce.');
            $row['status'] = 'live';
            $row['starts_at'] = date(DATE_ATOM, $now);
            $row['ends_at'] = date(DATE_ATOM, $now + 86400 * (int)$row['duration_days']);
            $row['teams'] = $teams;
            $d['events'][$i] = $result = $row;
        }
        return $d;
    });
    if ($result === null) throw new RuntimeException('CTF akce nebyla nalezena.');
    return $result;
}

function arena58_ctf_mutate(string $id, callable $fn): ?array
{
    if (!arena58_ctf_valid_id($id)) throw new RuntimeException('CTF akce nebyla nalezena.');
    $found = false;
    $result = null;
    arena58_ctf_update(static function (array $d) use ($id, $fn, &$found, &$result): array {
        foreach ($d['events'] as $i => $row) {
            if ((string)$row['id'] !== $id) continue;
            $found = true;
            $result = $fn($row);
            if ($result === null) unset($d['events'][$i]); else $d['events'][$i] = $result;
            break;
        }
        return $d;
    });
    if (!$found) throw new RuntimeException('CTF akce nebyla nalezena.');
    return $result;
}

function arena58_ctf_stop_event(string $id, int $now): array
{
    return (array)arena58_ctf_mutate($id, static function (array $row) use ($now): array {
        if (arena58_ctf_status($row, $now) !== 'live') throw new RuntimeException('Ukončit jde jen běžící akce.');
        $row['status'] = 'finished';
        $row['ends_at'] = date(DATE_ATOM, $now);
        $row['stopped_early'] = true;
        return $row;
    });
}

function arena58_ctf_extend_event(string $id, int $now, int $days = 1): array
{
    return (array)arena58_ctf_mutate($id, static function (array $row) use ($days): array {
        if ((string)$row['status'] !== 'live') throw new RuntimeException('Prodloužit jde jen běžící akce.');
        if ((int)$row['duration_days'] + $days > ARENA58_CTF_DAYS_MAX) throw new RuntimeException('Akce už nejde dál prodlužovat.');
        $row['duration_days'] = (int)$row['duration_days'] + $days;
        $row['ends_at'] = date(DATE_ATOM, (int)arena58_ctf_ts($row['ends_at']) + 86400 * $days);
        return $row;
    });
}

function arena58_ctf_delete_event(string $id): void
{
    arena58_ctf_mutate($id, static function (array $row): ?array {
        if ((string)$row['status'] === 'live') throw new RuntimeException('Běžící akci nejde smazat – nejdřív ji ukonči.');
        return null;
    });
}

// ---------------------------------------------------------------------------
// Kontext ctf:<id> – přístup, klíč stavu, body, on_complete, board (volá linux_v58_levels_ctf.php)
// ---------------------------------------------------------------------------

function arena58_ctf_team_for(array $event, string $classId, string $studentKey): ?array
{
    foreach ((array)($event['teams'][$classId] ?? []) as $team) {
        if (in_array($studentKey, (array)($team['members'] ?? []), true)) return $team;
    }
    return null;
}

function arena58_ctf_access(array $level, array $ctx): ?string
{
    $event = arena58_ctf_event((string)$ctx['id']);
    if ($event === null) return tr('Tahle CTF akce neexistuje.');
    $classId = (string)$ctx['class'];
    if (!in_array($classId, (array)$event['class_ids'], true)) return tr('Tahle CTF akce není pro tvou třídu.');
    if (!in_array((string)$level['id'], arena58_ctf_level_ids($event), true)) return tr('Tahle úloha není součástí CTF týdne.');
    $status = arena58_ctf_status($event, (int)$ctx['now']);
    if ($status === 'draft') return tr('CTF týden ještě nezačal. Sleduj termín spuštění.');
    if ($event['mode'] === 'teams' && arena58_ctf_team_for($event, $classId, (string)$ctx['student']) === null) return tr('Ještě nejsi zařazen(a) do týmu – ozvi se učiteli.');
    $chain = array_values(array_map('strval', (array)($level['chain_after'] ?? [])));
    if ($chain !== []) {
        $solved = function_exists('lab57_solved') ? lab57_solved($classId, (string)$ctx['state_key'], (string)$ctx['context']) : [];
        foreach ($chain as $need) {
            if (isset($solved[$need])) continue;
            $needLevel = function_exists('lab57_level') ? lab57_level($need) : null;
            return tr('Nejdřív vyřeš úlohu „{uloha}“.', ['uloha' => (string)($needLevel['title'] ?? $need)]);
        }
    }
    return null;
}

function arena58_ctf_state_key(array $ctx): string
{
    $event = arena58_ctf_event((string)$ctx['id']);
    if ($event === null || (string)($event['mode'] ?? 'solo') !== 'teams') return (string)$ctx['student'];
    $classId = (string)$ctx['class'];
    $team = arena58_ctf_team_for($event, $classId, (string)$ctx['student']);
    return $team !== null ? 'ctfteam:' . $event['id'] . ':' . $classId . ':' . (string)$team['id'] : (string)$ctx['student'];
}

function arena58_ctf_info(array $ctx): ?array
{
    return arena58_ctf_event((string)$ctx['id']);
}

/**
 * Dopočítá skutečné pořadí řešitele VE TŘÍDĚ podle pořadí zápisu v souboru událostí (atomicky, pod stejným
 * zámkem, kterým lab57_event_add() událost zapsal) a přepíše uložené body. Díky tomu je pořadí vždy správné,
 * i když by dva žáci odeslali řešení ve stejnou vteřinu – žádný odhad předem, jen jedno rozhodné počítání.
 */
function arena58_ctf_tier_fraction(int $rank): float
{
    if ($rank <= 3) return 1.0;
    if ($rank <= 8) return 0.8;
    if ($rank <= 15) return 0.6;
    return ARENA58_CTF_FLOOR;
}

function arena58_ctf_on_complete(array $ctx, array $level, array $solveEvent): void
{
    $event = arena58_ctf_event((string)$ctx['id']);
    if ($event === null) return;
    $penalty = (float)($event['settings']['hint_penalty'] ?? 0.2);
    $path = function_exists('lab58_context_events_path') ? lab58_context_events_path((string)$ctx['context']) : lab57_events_path((string)$ctx['context']);
    $classId = (string)$ctx['class'];
    $levelId = (string)$level['id'];
    $solveId = (string)($solveEvent['id'] ?? '');
    $base = (int)($level['points'] ?? 100);
    lab57_store_update($path, static function (array $rows) use ($classId, $levelId, $solveId, $base, $penalty): array {
        $seen = [];
        $rank = 0;
        $idx = null;
        foreach ($rows as $i => $row) {
            if (!is_array($row) || ($row['kind'] ?? '') !== 'solve' || (string)($row['level'] ?? '') !== $levelId || (string)($row['class_id'] ?? '') !== $classId) continue;
            $skey = (string)($row['student_key'] ?? '');
            if (!isset($seen[$skey])) { $seen[$skey] = true; $rank++; }
            if ((string)($row['id'] ?? '') === $solveId) { $idx = $i; break; }
        }
        if ($idx === null) return $rows;
        $hints = (int)($rows[$idx]['hints'] ?? 0);
        $frac = max(ARENA58_CTF_FLOOR, arena58_ctf_tier_fraction($rank) - $penalty * $hints);
        $pts = (int)round($base * $frac);
        if (!empty($rows[$idx]['first'])) $pts = (int)round($pts * 1.25);
        $floorPts = (int)round($base * ARENA58_CTF_FLOOR);
        $rows[$idx]['points'] = max($floorPts, $pts);
        $rows[$idx]['rank_in_class'] = $rank;
        return $rows;
    });
}

// ---------------------------------------------------------------------------
// Pořadí (vlastní implementace – nezávisí na neuzamčeném vnitřku arena_v57.php)
// ---------------------------------------------------------------------------

/** Víc bodů vyhrává, při shodě dřívější poslední řešení, pak abecedně. Bez bodů = bez pořadí. */
function arena58_ctf_rank(array $rows): array
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

function arena58_ctf_student_standings(array $solves): array
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
    return arena58_ctf_rank(array_values($rows));
}

/** Týmy: úloha se týmu počítá jednou (nejvyšší zisk mezi členy, když by měl někdo víc pokusů z jiného zdroje). */
function arena58_ctf_team_standings(array $classTeams, array $solves): array
{
    $teamOf = [];
    $teams = [];
    foreach ($classTeams as $team) {
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
    return arena58_ctf_rank($rows);
}

/**
 * Vypočítá žebříček TŘÍDY z eventů (state_key je student, nebo tým – dynamické tiery jsou ale vždy
 * per-student, protože body přepočítal arena58_ctf_on_complete podle žáka, ne podle týmu).
 * $cutoffTs: nejpozdější čas, který se ještě počítá (zmrazení žebříčku před koncem pro žáky).
 */
function arena58_ctf_compute(array $event, string $classId, int $cutoffTs): array
{
    $rows = function_exists('lab57_events') ? lab57_events('ctf:' . (string)$event['id']) : [];
    $seen = [];
    $solves = [];
    foreach ($rows as $row) {
        if (!is_array($row) || ($row['kind'] ?? '') !== 'solve' || (string)($row['class_id'] ?? '') !== $classId) continue;
        $at = arena58_ctf_ts($row['at'] ?? null);
        if ($at === null || $at > $cutoffTs) continue;
        $key = (string)($row['student_key'] ?? '') . '|' . (string)($row['level'] ?? '');
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $row['ts'] = $at;
        $solves[] = $row;
    }
    $classTeams = (array)($event['teams'][$classId] ?? []);
    return [
        'solves' => $solves,
        'students' => arena58_ctf_student_standings($solves),
        'teams' => ($event['mode'] ?? 'solo') === 'teams' ? arena58_ctf_team_standings($classTeams, $solves) : [],
    ];
}

function arena58_ctf_effective_cutoff(array $event, int $now, bool $forStudent): int
{
    $end = arena58_ctf_ts($event['ends_at'] ?? null) ?? $now;
    $status = arena58_ctf_status($event, $now);
    $freezeMin = (int)($event['settings']['freeze_before_end_min'] ?? 0);
    if ($forStudent && $status === 'live' && $freezeMin > 0) {
        $freezeAt = $end - $freezeMin * 60;
        if ($now >= $freezeAt) return max(0, $freezeAt);
    }
    return min($now, $end);
}

// ---------------------------------------------------------------------------
// Jména podle soukromí (volá jen zamčenou fci arena57_public_name – frozen API)
// ---------------------------------------------------------------------------

function arena58_ctf_full_label(string $key, string $eventLabel, array $roster): string
{
    $label = trim((string)($roster[$key]['label'] ?? ''));
    return $label !== '' ? $label : ($eventLabel !== '' ? $eventLabel : tr('Žák'));
}

function arena58_ctf_public_name(string $mode, string $key, string $eventLabel, array $roster, ?int $rank, ?string $viewerKey, int $fallbackNo): string
{
    if (function_exists('arena57_public_name')) return arena57_public_name($mode, $key, $eventLabel, $roster, $rank, $viewerKey, $fallbackNo);
    return arena58_ctf_full_label($key, $eventLabel, $roster);
}

function arena58_ctf_public_rows(array $event, array $standings, array $roster, ?string $viewerKey, int $limit): array
{
    $mode = (string)($event['settings']['names'] ?? 'initials');
    $rows = [];
    foreach ($standings as $row) {
        if ($row['rank'] === null) continue;
        $isMe = $viewerKey !== null && $row['key'] === $viewerKey;
        if (count($rows) >= $limit && !$isMe) continue;
        $rows[] = ['rank' => $row['rank'], 'name' => arena58_ctf_public_name($mode, $row['key'], $row['label'], $roster, $row['rank'], $viewerKey, $row['rank']), 'points' => $row['points'], 'solved' => $row['solved'], 'me' => $isMe];
    }
    return $rows;
}

function arena58_ctf_public_teams(array $teams, ?string $viewerKey): array
{
    $out = [];
    foreach ($teams as $team) {
        $out[] = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved'], 'size' => count($team['members']), 'me' => $viewerKey !== null && in_array($viewerKey, $team['members'], true)];
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Board (žák) – volá i context 'board' callback z lab_v57_api.php?op=board
// ---------------------------------------------------------------------------

function arena58_ctf_board(string $eventId, string $classId, string $studentKey): array
{
    $event = arena58_ctf_event($eventId);
    $now = arena58_ctf_now();
    if ($event === null || !in_array($classId, (array)$event['class_ids'], true)) return ['error' => tr('CTF akce nebyla nalezena.'), 'status' => 'missing', 'now' => $now];
    $status = arena58_ctf_status($event, $now);
    $cutoff = arena58_ctf_effective_cutoff($event, $now, true);
    $frozen = $status === 'live' && $cutoff < $now;
    $calc = arena58_ctf_compute($event, $classId, $cutoff);
    $roster = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    $viewerKey = $event['mode'] === 'teams' ? (arena58_ctf_team_for($event, $classId, $studentKey)['id'] ?? null) : $studentKey;
    // U týmů se v žebříčku srovnávají týmy, takže "moje" hledáme podle team id, ne podle studenta.
    $viewerRowKey = $event['mode'] === 'teams' ? null : $studentKey;
    $absolute = fair64_absolute_board_enabled('ctf', $classId, $eventId);
    $me = ['rank' => null, 'points' => 0, 'solved' => 0];
    foreach ($calc['students'] as $row) {
        if ($row['key'] === $studentKey) { $me = ['rank' => $row['rank'], 'points' => $row['points'], 'solved' => $row['solved']]; break; }
    }
    $myTeam = null;
    foreach ($calc['teams'] as $team) {
        if (in_array($studentKey, $team['members'], true)) { $myTeam = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved']]; break; }
    }
    return [
        'event' => ['id' => (string)$event['id'], 'title' => (string)$event['title'], 'status' => $status, 'ends_at' => $event['ends_at'] ?? null, 'mode' => (string)$event['mode'], 'now' => $now, 'frozen' => $frozen],
        'rows' => $absolute ? arena58_ctf_public_rows($event, $calc['students'], $roster, $viewerRowKey, ARENA58_CTF_BOARD_TOP) : fair64_only_me(arena58_ctf_public_rows($event, $calc['students'], $roster, $viewerRowKey, PHP_INT_MAX)),
        'teams' => $event['mode'] === 'teams' ? ($absolute ? arena58_ctf_public_teams($calc['teams'], $viewerKey) : fair64_only_me(arena58_ctf_public_teams($calc['teams'], $viewerKey))) : [],
        'absolute' => $absolute,
        'me' => $me, 'my_team' => $myTeam,
    ];
}

function arena58_ctf_board_cb(array $ctx): array
{
    return arena58_ctf_board((string)$ctx['id'], (string)$ctx['class'], (string)$ctx['student']);
}

// ---------------------------------------------------------------------------
// Úlohy pro žákovský pohled (podle kategorie, se stavem zamčeno/vyřešeno)
// ---------------------------------------------------------------------------

/** @return array<string,array{meta:array,items:list<array>}> úlohy seskupené podle kategorie pro daného žáka/tým */
function arena58_ctf_task_rows(array $event, string $classId, string $stateKey): array
{
    $solved = function_exists('lab57_solved') ? lab57_solved($classId, $stateKey, 'ctf:' . (string)$event['id']) : [];
    $out = [];
    foreach (arena58_ctf_levels() as $level) {
        $id = (string)$level['id'];
        if (!in_array($id, arena58_ctf_level_ids($event), true)) continue;
        $cat = (string)($level['category'] ?? 'ostatni');
        $out[$cat] ??= ['meta' => ARENA58_CTF_CATEGORIES[$cat] ?? ['label' => 'Ostatní', 'icon' => '•', 'lead' => ''], 'items' => []];
        $chain = array_values(array_map('strval', (array)($level['chain_after'] ?? [])));
        $lockedBy = null;
        foreach ($chain as $need) { if (!isset($solved[$need])) { $needLevel = lab57_level($need); $lockedBy = (string)($needLevel['title'] ?? $need); break; } }
        $out[$cat]['items'][] = [
            'id' => $id, 'title' => (string)$level['title'], 'difficulty' => (int)($level['difficulty'] ?? 1), 'points' => (int)($level['points'] ?? 100),
            'solved' => isset($solved[$id]), 'earned' => (int)($solved[$id]['points'] ?? 0), 'locked' => $lockedBy,
        ];
    }
    ksort($out);
    return $out;
}

// ---------------------------------------------------------------------------
// XP po skončení akce (jednou na akci, jako v57 aréna)
// ---------------------------------------------------------------------------

function arena58_ctf_took_part(array $event, string $classId, string $studentKey): bool
{
    foreach (function_exists('lab57_events') ? lab57_events('ctf:' . (string)$event['id']) : [] as $ev) {
        if (is_array($ev) && (string)($ev['class_id'] ?? '') === $classId && (string)($ev['student_key'] ?? '') === $studentKey) return true;
    }
    return false;
}

function arena58_ctf_award_finished_xp(string $classId, string $studentKey): void
{
    if ($studentKey === '' || !function_exists('learning_award_once')) return;
    $now = arena58_ctf_now();
    $sessionOk = session_status() === PHP_SESSION_ACTIVE;
    $done = $sessionOk && is_array($_SESSION['arena58_ctf_xp'] ?? null) ? $_SESSION['arena58_ctf_xp'] : [];
    try {
        foreach (arena58_ctf_events_for_class($classId) as $event) {
            $eventId = (string)$event['id'];
            if (isset($done[$eventId]) || arena58_ctf_status($event, $now) !== 'finished') continue;
            if (arena58_ctf_took_part($event, $classId, $studentKey)) {
                $cutoff = arena58_ctf_effective_cutoff($event, $now, false);
                $calc = arena58_ctf_compute($event, $classId, $cutoff);
                $rank = null;
                $source = $event['mode'] === 'teams' ? $calc['teams'] : $calc['students'];
                foreach ($source as $row) {
                    $hit = $event['mode'] === 'teams' ? in_array($studentKey, $row['members'], true) : $row['key'] === $studentKey;
                    if ($hit) { $rank = $row['rank']; break; }
                }
                eco64_award($classId, $studentKey, 'ctf58', 'v58:ctf:' . $eventId, ARENA58_CTF_XP_PARTICIPATION + (ARENA58_CTF_XP_PODIUM[(int)$rank] ?? 0));
            }
            $done[$eventId] = 1;
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v58 CTF XP: ' . $e->getMessage());
    }
    if ($sessionOk) $_SESSION['arena58_ctf_xp'] = $done;
}

// ---------------------------------------------------------------------------
// Učitel: data panelu (vlastní jména, bez zmrazení)
// ---------------------------------------------------------------------------

function arena58_ctf_teacher_data(string $eventId, string $classId, int $now): ?array
{
    $event = arena58_ctf_event($eventId);
    if ($event === null || !in_array($classId, (array)$event['class_ids'], true)) return null;
    $cutoff = arena58_ctf_effective_cutoff($event, $now, false);
    $calc = arena58_ctf_compute($event, $classId, $cutoff);
    $roster = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    $name = static fn(string $key, string $label = ''): string => arena58_ctf_full_label($key, $label, $roster);
    $status = arena58_ctf_status($event, $now);

    $board = [];
    foreach ($calc['students'] as $row) {
        $board[] = ['rank' => $row['rank'], 'name' => $name($row['key'], $row['label']), 'points' => $row['points'], 'solved' => $row['solved']];
    }
    $teams = [];
    foreach ($calc['teams'] as $team) {
        $teams[] = ['rank' => $team['rank'], 'name' => $team['label'], 'points' => $team['points'], 'solved' => $team['solved'], 'members' => array_map($name, $team['members'])];
    }

    $rows = function_exists('lab57_events') ? lab57_events('ctf:' . $eventId) : [];
    $alerts = [];
    $feed = [];
    foreach (array_reverse($rows) as $ev) {
        if (!is_array($ev) || (string)($ev['class_id'] ?? '') !== $classId) continue;
        $kind = (string)($ev['kind'] ?? '');
        $item = ['at' => (string)($ev['at'] ?? ''), 'kind' => $kind, 'name' => $name((string)($ev['student_key'] ?? ''), (string)($ev['label'] ?? '')), 'level' => function_exists('lab57_level') ? (string)(lab57_level((string)($ev['level'] ?? ''))['title'] ?? $ev['level'] ?? '') : (string)($ev['level'] ?? ''), 'points' => (int)($ev['points'] ?? 0), 'first' => !empty($ev['first'])];
        if ($kind === 'foreign_code') $alerts[] = $item;
        if (in_array($kind, ['solve', 'foreign_code'], true) && count($feed) < 25) $feed[] = $item;
    }

    $levels = [];
    foreach (arena58_ctf_level_ids($event) as $levelId) {
        $level = function_exists('lab57_level') ? lab57_level($levelId) : null;
        if ($level === null) continue;
        $solves = array_values(array_filter($calc['solves'], static fn(array $s): bool => (string)$s['level'] === $levelId));
        $first = $solves[0] ?? null;
        $levels[] = [
            'id' => $levelId, 'title' => (string)$level['title'], 'category' => (string)($level['category'] ?? ''), 'solves' => count($solves),
            'current_tier' => (int)round(100 * arena58_ctf_tier_fraction(count($solves) + 1)), 'first' => $first !== null ? $name((string)$first['student_key'], (string)($first['label'] ?? '')) : null,
        ];
    }

    return [
        'ok' => true, 'event' => ['id' => $eventId, 'title' => (string)$event['title'], 'status' => $status, 'starts_at' => $event['starts_at'] ?? null, 'ends_at' => $event['ends_at'] ?? null, 'mode' => (string)$event['mode'], 'now' => $now],
        'board' => $board, 'teams' => $teams, 'levels' => $levels, 'alerts' => array_slice($alerts, 0, 20), 'feed' => $feed,
        'participants' => count(array_filter($calc['students'], static fn(array $r): bool => $r['rank'] !== null || $r['solved'] > 0)),
    ];
}

function arena58_ctf_json_out(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/** GET teacher.php?tab=ctf&ctf_poll=1&event=<id>&class=<id> */
function arena58_ctf_teacher_poll(): void
{
    $eventId = is_string($_GET['event'] ?? null) ? $_GET['event'] : '';
    $classId = is_string($_GET['class'] ?? null) ? $_GET['class'] : '';
    try {
        $data = arena58_ctf_valid_id($eventId) ? arena58_ctf_teacher_data($eventId, $classId, arena58_ctf_now()) : null;
    } catch (Throwable $e) {
        error_log('EDUCANET v58 CTF poll: ' . $e->getMessage());
        arena58_ctf_json_out(['ok' => false, 'error' => 'Data se nepodařilo načíst.'], 500);
        return;
    }
    if ($data === null) { arena58_ctf_json_out(['ok' => false, 'error' => 'Akce nebyla nalezena.'], 404); return; }
    arena58_ctf_json_out($data);
}

// ---------------------------------------------------------------------------
// Učitel: POST akce (CSRF a přihlášení ověřuje teacher.php; oprávnění students.manage přes prefix arena58_ctf_)
// ---------------------------------------------------------------------------

function arena58_ctf_teacher_handle_post(string $action, array $modules): void
{
    $classIds = array_map('strval', array_keys($modules));
    $classId = is_string($_POST['class_id'] ?? null) ? $_POST['class_id'] : ($classIds[0] ?? '');
    $eventId = is_string($_POST['event'] ?? null) ? $_POST['event'] : '';
    $now = arena58_ctf_now();
    $done = static function (string $message, string $cid, ?string $event = null, string $type = 'ok'): never {
        if (function_exists('teacher_flash')) teacher_flash($message, $type);
        $params = ['tab' => 'ctf', 'class' => $cid];
        if ($event !== null) $params['event'] = $event;
        if (function_exists('teacher_redirect')) teacher_redirect($params);
        exit;
    };
    $eventOfClass = static function () use ($eventId, $classId, $classIds): array {
        if (!in_array($classId, $classIds, true)) throw new RuntimeException('Neplatná třída.');
        $event = arena58_ctf_event($eventId);
        if ($event === null || !in_array($classId, (array)$event['class_ids'], true)) throw new RuntimeException('CTF akce nebyla nalezena.');
        return $event;
    };

    switch ($action) {
        case 'arena58_ctf_create':
            $event = arena58_ctf_create_event($_POST, $classIds, $now);
            if (!empty($_POST['start_now'])) {
                $event = arena58_ctf_start_event((string)$event['id'], $now);
                $done('CTF týden „' . $event['title'] . '“ běží do ' . date('j. n.', (int)arena58_ctf_ts($event['ends_at'])) . '.', $classId, (string)$event['id']);
            }
            $done('CTF týden „' . $event['title'] . '“ je připravený. Spusť ho, až budou třídy připravené.', $classId, (string)$event['id']);
        case 'arena58_ctf_start':
            $event = arena58_ctf_start_event((string)$eventOfClass()['id'], $now);
            $done('CTF týden „' . $event['title'] . '“ běží do ' . date('j. n. H:i', (int)arena58_ctf_ts($event['ends_at'])) . '.', $classId, (string)$event['id']);
        case 'arena58_ctf_stop':
            $event = arena58_ctf_stop_event((string)$eventOfClass()['id'], $now);
            $done('CTF týden „' . $event['title'] . '“ je ukončený.', $classId, (string)$event['id']);
        case 'arena58_ctf_extend':
            $event = arena58_ctf_extend_event((string)$eventOfClass()['id'], $now);
            $done('Akce prodloužena o den (konec ' . date('j. n.', (int)arena58_ctf_ts($event['ends_at'])) . ').', $classId, (string)$event['id']);
        case 'arena58_ctf_delete':
            $event = $eventOfClass();
            arena58_ctf_delete_event((string)$event['id']);
            $done('CTF týden „' . $event['title'] . '“ byl smazán.', $classId);
        default:
            throw new RuntimeException('Neznámá akce CTF.');
    }
}
