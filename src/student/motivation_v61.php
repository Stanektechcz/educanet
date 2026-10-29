<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Motivace (část D): denní/týdenní cíle, série a sezónní odznaky. BEZ VLIVU NA ZNÁMKY.
 *
 * Všechno se vyhodnocuje líně při zobrazení z dat, která už existují (vyřešené úrovně labu s časem
 * a nápovědami, události XP v profilu učení) – žádný cron, žádné nové zdroje pravdy.
 *
 * Odměna: malé XP, idempotentní přes klíč události goal:<den|týden>:<id> v profilu učení (stejný
 * mechanismus jako learning_award_once), se STROPEM: nejvýš MOT61_DAILY_XP_CAP XP za den a
 * MOT61_WEEKLY_XP_CAP XP za týden z cílů. Strop se ověřuje uvnitř zámku zápisu, takže ho nepřekročí ani
 * dva souběžné požadavky. Cíle nezapisují body ani známky.
 *
 * Série: dny bez školy (víkend, svátky, prázdniny – mot61_break_ranges() + záznamy holiday/break ze
 * školního roku) sérii nepřerušují a nepočítají se; přerušuje ji až vynechaný školní den.
 *
 * Sezónní odznaky: dvě pololetí školního roku (podle meta.start/meta.end ze school_year.php), pět odznaků
 * v každém. Získané se z dat dopočítají a při prvním zobrazení uloží (sbírka přežije změnu školního roku).
 * Vzhled je deterministický z id přes badges_v60.php.
 *
 * Úložiště: storage/motivation_v61.json.php { "<třída>|<klíč žáka>": { fav: [id obchodu], seasons: {id: datum} } }.
 */

const MOT61_STORE = 'motivation_v61.json.php';
const MOT61_DAILY_XP_CAP = 5;
const MOT61_WEEKLY_XP_CAP = 15;
const MOT61_FAV_MAX = 12;
const MOT61_STREAK_TARGET = 7;

function mot61_path(): string
{
    return STORAGE_DIR . '/' . MOT61_STORE;
}

// ---------------------------------------------------------------------------
// Kalendář školního roku (dny bez školy)
// ---------------------------------------------------------------------------

/**
 * Dny bez školy mimo víkendy: [od, do] včetně. ORIENTAČNÍ hodnoty pro školní rok 2026/27 – škola je
 * má před ostrým provozem porovnat s ředitelským volnem (viz docs a CHANGELOG_V61). Další dny se berou
 * ze school_year.php (záznamy kind = holiday | break).
 *
 * @return list<array{0:string,1:string}>
 */
function mot61_break_ranges(): array
{
    return [
        ['2026-09-28', '2026-09-28'], ['2026-10-28', '2026-10-28'], ['2026-10-29', '2026-10-30'], ['2026-11-17', '2026-11-17'],
        ['2026-12-23', '2027-01-03'], ['2027-01-29', '2027-01-29'], ['2027-02-22', '2027-02-28'],
        ['2027-04-01', '2027-04-02'], ['2027-04-05', '2027-04-05'], ['2027-05-01', '2027-05-01'], ['2027-05-08', '2027-05-08'],
        ['2027-07-01', '2027-08-31'],
    ];
}

function mot61_school_year(): array
{
    static $year = null;
    if ($year === null) {
        $file = __DIR__ . '/school_year.php';
        $loaded = is_file($file) ? require $file : [];
        $year = is_array($loaded) ? $loaded : [];
    }
    return $year;
}

/** @return array<string, true> jednotlivé dny bez školy ze školního roku */
function mot61_calendar_free_days(): array
{
    static $set = null;
    if ($set === null) {
        $set = [];
        foreach ((array)(mot61_school_year()['calendar'] ?? []) as $row) {
            if (is_array($row) && in_array((string)($row['kind'] ?? ''), ['holiday', 'break'], true) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($row['date'] ?? '')) === 1) {
                $set[(string)$row['date']] = true;
            }
        }
    }
    return $set;
}

function mot61_day_ts(string $ymd): int
{
    return (int)strtotime($ymd . ' 12:00:00');
}

function mot61_shift_day(string $ymd, int $days): string
{
    return date('Y-m-d', mot61_day_ts($ymd) + $days * 86400);
}

function mot61_is_free_day(string $ymd): bool
{
    static $memo = [];
    return $memo[$ymd] ??= mot61_compute_free_day($ymd);
}

function mot61_compute_free_day(string $ymd): bool
{
    if ((int)date('N', mot61_day_ts($ymd)) >= 6) return true;
    if (isset(mot61_calendar_free_days()[$ymd])) return true;
    foreach (mot61_break_ranges() as [$from, $to]) {
        if ($ymd >= $from && $ymd <= $to) return true;
    }
    return false;
}

// ---------------------------------------------------------------------------
// Aktivita žáka z existujících dat
// ---------------------------------------------------------------------------

/** Klíč profilu učení odvozený z klíče žáka (class:student:<hash24> → class:s:<hash24>); '' = nelze. */
function mot61_learning_key(string $classId, string $studentKey): string
{
    if (preg_match('/^(class_[a-z0-9]+):student:([a-f0-9]{24})$/', $studentKey, $m) !== 1 || $m[1] !== $classId) return '';
    return $classId . ':s:' . $m[2];
}

function mot61_events_by_key(string $learningKey): array
{
    if ($learningKey === '') return [];
    $row = storage_read_request(learning_profiles_path())[$learningKey] ?? [];
    return is_array($row) && is_array($row['events'] ?? null) ? $row['events'] : [];
}

function mot61_is_goal_event(string $key): bool
{
    return str_starts_with($key, 'goal:');
}

/**
 * Aktivita po dnech: labs = vyřešené úrovně labu, nohint = z toho bez nápovědy, events = události XP mimo cíle.
 * @return array<string, array{labs:int, nohint:int, events:int}>
 */
function mot61_collect_activity(string $classId, string $studentKey, array $events): array
{
    $days = [];
    $bump = static function (string $day, string $field) use (&$days): void {
        $days[$day] ??= ['labs' => 0, 'nohint' => 0, 'events' => 0];
        $days[$day][$field]++;
    };
    foreach (lab57_solved($classId, $studentKey, 'practice') as $info) {
        $ts = is_array($info) ? strtotime((string)($info['at'] ?? '')) : false;
        if ($ts === false) continue;
        $day = date('Y-m-d', $ts);
        $bump($day, 'labs');
        if ((int)($info['hints'] ?? 0) === 0) $bump($day, 'nohint');
    }
    foreach ($events as $key => $event) {
        if (!is_array($event) || mot61_is_goal_event((string)$key)) continue;
        $ts = strtotime((string)($event['at'] ?? ''));
        if ($ts !== false) $bump(date('Y-m-d', $ts), 'events');
    }
    ksort($days);
    return $days;
}

// ---------------------------------------------------------------------------
// Série
// ---------------------------------------------------------------------------

/**
 * Aktuální série (po sobě jdoucí aktivní dny; volné dny se přeskakují, dnešek ještě nerozhoduje).
 * @param array<string,mixed> $activity den => data
 */
function mot61_current_streak(array $activity, string $today): int
{
    $streak = 0;
    $day = $today;
    for ($guard = 0; $guard < 900; $guard++) {
        if (isset($activity[$day])) {
            $streak++;
        } elseif ($day !== $today && !mot61_is_free_day($day)) {
            break;
        }
        $day = mot61_shift_day($day, -1);
    }
    return $streak;
}

/**
 * Nejdelší série od prvního aktivního dne do $until a den, kdy série poprvé dosáhla $target.
 * @return array{best:int, target_at:?string}
 */
function mot61_best_streak(array $activity, string $until, int $target = MOT61_STREAK_TARGET): array
{
    $dates = array_keys(array_filter($activity, static fn($k): bool => $k <= $until, ARRAY_FILTER_USE_KEY));
    if ($dates === []) return ['best' => 0, 'target_at' => null];
    sort($dates);
    $best = $run = 0;
    $targetAt = null;
    $day = $dates[0];
    for ($guard = 0; $day <= $until && $guard < 900; $guard++) {
        if (isset($activity[$day])) {
            $run++;
            $best = max($best, $run);
            if ($run >= $target && $targetAt === null) $targetAt = $day;
        } elseif (!mot61_is_free_day($day) && $day !== $until) {
            $run = 0;
        }
        $day = mot61_shift_day($day, 1);
    }
    return ['best' => $best, 'target_at' => $targetAt];
}

// ---------------------------------------------------------------------------
// Cíle
// ---------------------------------------------------------------------------

/** Definice cílů (≤ 3 denní + 2 týdenní). XP denních cílů dává dohromady právě strop dne. */
function mot61_goal_defs(): array
{
    return [
        'day' => [
            ['id' => 'lab2', 'metric' => 'labs', 'target' => 2, 'xp' => 2, 'label' => tr('Vyřeš 2 úrovně Linux Labu')],
            ['id' => 'nohint1', 'metric' => 'nohint', 'target' => 1, 'xp' => 2, 'label' => tr('Vyřeš úroveň labu bez nápovědy')],
            ['id' => 'learn1', 'metric' => 'events', 'target' => 1, 'xp' => 1, 'label' => tr('Splň aktivitu s XP (lekce, kvíz, cvičení)')],
        ],
        'week' => [
            ['id' => 'lab6', 'metric' => 'labs', 'target' => 6, 'xp' => 8, 'label' => tr('Vyřeš 6 úrovní Linux Labu')],
            ['id' => 'days3', 'metric' => 'days', 'target' => 3, 'xp' => 6, 'label' => tr('Buď aktivní ve 3 různých dnech')],
        ],
    ];
}

function mot61_week_key(string $day): string
{
    return date('o-\WW', mot61_day_ts($day));
}

function mot61_week_monday(string $day): string
{
    return mot61_shift_day($day, 1 - (int)date('N', mot61_day_ts($day)));
}

function mot61_goal_value(array $goal, array $activity, string $today): int
{
    if ($goal['metric'] === 'days') {
        $monday = mot61_week_monday($today);
        $n = 0;
        for ($i = 0; $i < 7; $i++) { if (isset($activity[mot61_shift_day($monday, $i)])) $n++; }
        return $n;
    }
    if (($goal['kind'] ?? '') === 'week') {
        $monday = mot61_week_monday($today);
        $sum = 0;
        for ($i = 0; $i < 7; $i++) { $sum += (int)($activity[mot61_shift_day($monday, $i)][$goal['metric']] ?? 0); }
        return $sum;
    }
    return (int)($activity[$today][$goal['metric']] ?? 0);
}

/** Součet XP z cílů s daným prefixem klíče (strop dne/týdne). */
function mot61_events_xp(array $events, string $prefix): int
{
    $sum = 0;
    foreach ($events as $key => $event) {
        if (str_starts_with((string)$key, $prefix) && is_array($event)) $sum += max(0, (int)($event['xp'] ?? 0));
    }
    return $sum;
}

/**
 * Připíše XP k profilu učení pod zámkem: událost nejvýš jednou a součet XP s prefixem $capPrefix nejvýš $cap.
 * @return int skutečně připsané XP (0 = už uděleno nebo strop vyčerpán)
 */
function mot61_award_xp(string $learningKey, string $eventKey, int $xp, string $capPrefix, int $cap): int
{
    if ($learningKey === '' || $xp <= 0 || !mot61_is_goal_event($eventKey) || !str_starts_with($eventKey, $capPrefix)) return 0;
    $granted = 0;
    storage_map_update(learning_profiles_path(), $learningKey, static function (?array $current) use ($eventKey, $xp, $capPrefix, $cap, &$granted): array {
        $profile = $current ?? learning_profile_default();
        $events = is_array($profile['events'] ?? null) ? $profile['events'] : [];
        if (isset($events[$eventKey])) return $profile;
        $room = $cap - mot61_events_xp($events, $capPrefix);
        $pay = min($xp, $room);
        if ($pay <= 0) return $profile;
        $events[$eventKey] = ['xp' => $pay, 'at' => date(DATE_ATOM)];
        $profile['events'] = $events;
        $profile['xp'] = max(0, (int)($profile['xp'] ?? 0) + $pay);
        $profile['version'] = (int)($profile['version'] ?? 0) + 1;
        $profile['updated_at'] = date(DATE_ATOM);
        $granted = $pay;
        return $profile;
    });
    return $granted;
}

/**
 * Vyhodnotí cíle k okamžiku $now a (když $award) odmění splněné. Čte jen existující data.
 * @return array{today:string, week:string, free_today:bool, streak:int, best:int, goals:array{day:list<array>,week:list<array>}, xp_today:int, xp_week:int, granted:int, activity:array}
 */
function mot61_overview(string $classId, string $studentKey, string $learningKey, array $events, int $now, bool $award): array
{
    $today = date('Y-m-d', $now);
    $week = mot61_week_key($today);
    $activity = mot61_collect_activity($classId, $studentKey, $events);
    $granted = 0;
    $goals = ['day' => [], 'week' => []];
    foreach (mot61_goal_defs() as $kind => $defs) {
        $scope = $kind === 'day' ? $today : $week;
        foreach ($defs as $def) {
            $def['kind'] = $kind;
            $value = mot61_goal_value($def, $activity, $today);
            $def['value'] = min($value, (int)$def['target']);
            $def['done'] = $value >= (int)$def['target'];
            $def['event'] = 'goal:' . $scope . ':' . $def['id'];
            $def['paid'] = isset($events[$def['event']]) && is_array($events[$def['event']]) ? (int)($events[$def['event']]['xp'] ?? 0) : null;
            if ($award && $def['done'] && $def['paid'] === null) {
                $pay = mot61_award_xp($learningKey, $def['event'], (int)$def['xp'], 'goal:' . $scope . ':', $kind === 'day' ? MOT61_DAILY_XP_CAP : MOT61_WEEKLY_XP_CAP);
                $granted += $pay;
                if ($pay > 0) $def['paid'] = $pay;
            }
            $goals[$kind][] = $def;
        }
    }
    $afterEvents = $granted > 0 ? mot61_events_by_key($learningKey) : $events;
    return [
        'today' => $today, 'week' => $week, 'free_today' => mot61_is_free_day($today),
        'streak' => mot61_current_streak($activity, $today), 'best' => mot61_best_streak($activity, $today)['best'],
        'goals' => $goals, 'granted' => $granted, 'activity' => $activity,
        'xp_today' => mot61_events_xp($afterEvents, 'goal:' . $today . ':'), 'xp_week' => mot61_events_xp($afterEvents, 'goal:' . $week . ':'),
    ];
}

/** Přehled pro přihlášeného žáka (session): odměňuje jen přihlášený žák, profil nepřihlášeného je jen v session. */
function mot61_overview_for_session(string $classId, int $now): ?array
{
    $studentKey = adaptive_student_key($classId);
    if ($studentKey === '') return null;
    $learningKey = learning_profile_key($classId);
    $signedIn = auth_is_signed_in();
    $events = $signedIn ? mot61_events_by_key($learningKey) : (array)(learning_profile($classId)['events'] ?? []);
    $overview = mot61_overview($classId, $studentKey, $learningKey, $events, $now, $signedIn);
    if ($overview['granted'] > 0) mot61_refresh_session_profile($classId, $learningKey);
    return $overview;
}

/** Po odměně obnoví zrcadlo profilu v session (stejně jako learning_award_once). */
function mot61_refresh_session_profile(string $classId, string $learningKey): void
{
    unset($GLOBALS['educanet_learning_profile_memo'], $GLOBALS['educanet_learning_refresh_memo']);
    $row = storage_read(learning_profiles_path())[$learningKey] ?? null;
    if (is_array($row) && isset($_SESSION) && is_array($_SESSION)) {
        $_SESSION['learning_profiles'][$learningKey] = learning_profile_remember($learningKey, $row);
    }
}

// ---------------------------------------------------------------------------
// Sezónní odznaky (pololetí)
// ---------------------------------------------------------------------------

/** @return list<array{id:string, n:int, from:string, to:string, label:string}> */
function mot61_seasons(): array
{
    $meta = (array)(mot61_school_year()['meta'] ?? []);
    $start = (string)($meta['start'] ?? '');
    $end = (string)($meta['end'] ?? '');
    if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $start, $m) !== 1 || preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) !== 1) return [];
    $year = (int)$m[1];
    $name = (string)($meta['school_year'] ?? ($year . '/' . ($year + 1)));
    return [
        ['id' => $year . '_1', 'n' => 1, 'from' => $start, 'to' => ($year + 1) . '-01-31', 'label' => tr('1. pololetí {year}', ['year' => $name])],
        ['id' => $year . '_2', 'n' => 2, 'from' => ($year + 1) . '-02-01', 'to' => $end, 'label' => tr('2. pololetí {year}', ['year' => $name])],
    ];
}

/** Pět odznaků pololetí: metrika, cíl, rarita, kategorie ikony. */
function mot61_season_kinds(): array
{
    return [
        'active' => ['metric' => 'days', 'target' => 20, 'rarity' => 'common', 'category' => 'learn', 'title' => tr('Pravidelný'), 'text' => tr('Byl/a jsi aktivní ve 20 různých dnech pololetí.')],
        'lab' => ['metric' => 'labs', 'target' => 15, 'rarity' => 'rare', 'category' => 'lab', 'title' => tr('Terminálový'), 'text' => tr('Vyřešil/a jsi 15 úrovní Linux Labu za pololetí.')],
        'streak' => ['metric' => 'streak', 'target' => MOT61_STREAK_TARGET, 'rarity' => 'rare', 'category' => 'streak', 'title' => tr('V tahu'), 'text' => tr('Dosáhl/a jsi série 7 aktivních školních dnů za sebou.')],
        'goals' => ['metric' => 'goals', 'target' => 15, 'rarity' => 'epic', 'category' => 'xp', 'title' => tr('Plnitel cílů'), 'text' => tr('Splnil/a jsi 15 denních nebo týdenních cílů.')],
        'champion' => ['metric' => 'all', 'target' => 4, 'rarity' => 'legendary', 'category' => 'arena', 'title' => tr('Hvězda pololetí'), 'text' => tr('Získal/a jsi všechny ostatní odznaky pololetí.')],
    ];
}

/** @return array<string, array<string,mixed>> id odznaku => meta pro badge60_card (title, text, condition, rarity, category) */
function mot61_season_badge_defs(): array
{
    $defs = [];
    foreach (mot61_seasons() as $season) {
        foreach (mot61_season_kinds() as $kind => $def) {
            $defs['season_' . $season['id'] . '_' . $kind] = [
                'title' => tr('{title} · {n}. pololetí', ['title' => $def['title'], 'n' => $season['n']]), 'text' => $def['text'], 'condition' => $def['text'],
                'rarity' => $def['rarity'], 'category' => $def['category'], 'season' => $season['id'], 'kind' => $kind,
            ];
        }
    }
    return $defs;
}

/** Hodnoty metrik v rozsahu pololetí (jen dny do $today) + den dosažení cíle pro earned_at. */
function mot61_season_metrics(array $activity, array $events, array $season, string $today): array
{
    $until = min($season['to'], $today);
    $inSeason = array_filter($activity, static fn($k): bool => $k >= $season['from'] && $k <= $until, ARRAY_FILTER_USE_KEY);
    $labs = 0;
    foreach ($inSeason as $row) $labs += (int)$row['labs'];
    $goalDays = [];
    foreach ($events as $key => $event) {
        $ts = mot61_is_goal_event((string)$key) && is_array($event) ? strtotime((string)($event['at'] ?? '')) : false;
        $day = $ts !== false ? date('Y-m-d', $ts) : '';
        if ($day !== '' && $day >= $season['from'] && $day <= $until) $goalDays[] = $day;
    }
    sort($goalDays);
    $streak = mot61_best_streak($inSeason, $until);
    return ['days' => count($inSeason), 'labs' => $labs, 'streak' => $streak['best'], 'goals' => count($goalDays), 'at' => [
        'days' => array_keys($inSeason)[(int)mot61_season_kinds()['active']['target'] - 1] ?? null, 'streak' => $streak['target_at'], 'goals' => $goalDays[(int)mot61_season_kinds()['goals']['target'] - 1] ?? null,
        'labs' => mot61_season_lab_day($inSeason, (int)mot61_season_kinds()['lab']['target']),
    ]];
}

function mot61_season_lab_day(array $inSeason, int $target): ?string
{
    $sum = 0;
    foreach ($inSeason as $day => $row) {
        $sum += (int)$row['labs'];
        if ($sum >= $target) return (string)$day;
    }
    return null;
}

/**
 * Postup všech sezónních odznaků: id => [earned, percent, value, target, earned_at].
 * @param array<string,string> $stored už uložené získané odznaky (id => datum) – přetrvají i po změně školního roku
 */
function mot61_season_progress(array $activity, array $events, string $today, array $stored): array
{
    $out = [];
    foreach (mot61_seasons() as $season) {
        $metrics = $season['from'] <= $today ? mot61_season_metrics($activity, $events, $season, $today) : ['days' => 0, 'labs' => 0, 'streak' => 0, 'goals' => 0, 'at' => []];
        $earnedCount = 0;
        $latest = '';
        foreach (mot61_season_kinds() as $kind => $def) {
            $id = 'season_' . $season['id'] . '_' . $kind;
            $metric = (string)$def['metric'];
            $value = $metric === 'all' ? $earnedCount : (int)($metrics[$metric] ?? 0);
            $earned = $value >= (int)$def['target'] || isset($stored[$id]);
            $at = $stored[$id] ?? (string)($metrics['at'][$metric] ?? ($metric === 'all' ? $latest : $today));
            if ($metric !== 'all' && $earned) { $earnedCount++; $latest = max($latest, $at); }
            $out[$id] = ['earned' => $earned, 'value' => min($value, (int)$def['target']), 'target' => (int)$def['target'], 'percent' => (int)floor(min($value, (int)$def['target']) / max(1, (int)$def['target']) * 100), 'earned_at' => $earned ? $at : '', 'season' => $season['id']];
        }
    }
    return $out;
}

function mot61_student_row(string $classId, string $studentKey): array
{
    $row = storage_read_request(mot61_path())[$classId . '|' . $studentKey] ?? [];
    return is_array($row) ? $row : [];
}

/** Uloží nově získané sezónní odznaky (idempotentně; zapisuje jen při změně). */
function mot61_season_sync(string $classId, string $studentKey, array $progress): void
{
    $stored = (array)(mot61_student_row($classId, $studentKey)['seasons'] ?? []);
    $new = [];
    foreach ($progress as $id => $row) {
        if (!empty($row['earned']) && !isset($stored[$id])) $new[$id] = $row['earned_at'] !== '' ? $row['earned_at'] : date('Y-m-d');
    }
    if ($new === []) return;
    storage_map_update(mot61_path(), $classId . '|' . $studentKey, static function (?array $row) use ($new): array {
        $row = is_array($row) ? $row : [];
        $row['seasons'] = array_merge($new, (array)($row['seasons'] ?? []));
        return $row;
    });
}

// ---------------------------------------------------------------------------
// Oblíbené položky obchodu
// ---------------------------------------------------------------------------

/** @return list<string> */
function mot61_fav_list(string $classId, string $studentKey): array
{
    return array_values(array_filter((array)(mot61_student_row($classId, $studentKey)['fav'] ?? []), 'is_string'));
}

/**
 * Přepne oblíbenou položku. Přidat jde jen položku z aktuální nabídky třídy (a v sezóně); odebrat vždy.
 * @return string added | removed | full | unknown
 */
function mot61_fav_toggle(string $classId, string $studentKey, string $itemId): string
{
    if ($studentKey === '' || preg_match('/^[A-Za-z0-9_]{1,40}$/', $itemId) !== 1) return 'unknown';
    $offer = mkt60_catalog_for_class($classId);
    $result = 'unknown';
    storage_map_update(mot61_path(), $classId . '|' . $studentKey, static function (?array $row) use ($itemId, $offer, &$result): array {
        $row = is_array($row) ? $row : [];
        $fav = array_values(array_filter((array)($row['fav'] ?? []), 'is_string'));
        if (in_array($itemId, $fav, true)) {
            $fav = array_values(array_diff($fav, [$itemId]));
            $result = 'removed';
        } elseif (!isset($offer[$itemId])) {
            $result = 'unknown';
        } elseif (count($fav) >= MOT61_FAV_MAX) {
            $result = 'full';
        } else {
            $fav[] = $itemId;
            $result = 'added';
        }
        $row['fav'] = $fav;
        return $row;
    });
    return $result;
}
