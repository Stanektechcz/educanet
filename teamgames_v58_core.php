<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – úložiště, životní cyklus relace, týmy, soukromí jmen, signály, XP.
 *
 * Relace hry prochází stavy lobby → running ↔ paused → finished → archived. Herní logika jednotlivých
 * her (Štafeta, Bingo, Riskuj!, Přetahovaná, Správci sítě/webu, Úniková místnost) je v samostatných
 * modulech teamgames_v58_game_*.php a registruje se přes teamgames_v58_registry.php.
 *
 * Úložiště (vše s ochranným prvním řádkem, zápisy jen přes tg58_update = storage_update):
 *   storage/teamgames_v58_index.json.php   {entries:[{id,class_id,type,created_at}, …]}  – jen pro výpis
 *   storage/teamgames_v58/<id>.json.php    plný stav relace (zdroj pravdy)
 *   storage/teamgames_v58_bank.json.php    {items:[…otázky učitele…]}
 * Klíče žáků (studentKey z arena57_roster / project_students_for_class) jsou jen na serveru; ven jdou
 * jména podle nastavení soukromí relace (iniciály / celé jméno / anonymně) – stejně jako v Aréně v57.
 */

const TG58_ID_RE = '/^[a-z0-9]{8,32}$/';
const TG58_TEAM_ID_RE = '/^t[0-9]{1,2}$/';
const TG58_TYPES = ['relay', 'bingo', 'jeopardy', 'tug', 'netadmin', 'escape'];
const TG58_STATUSES = ['lobby', 'running', 'paused', 'finished', 'archived'];
const TG58_NAME_MODES = ['initials', 'full', 'anon'];
const TG58_LINES = ['networks', 'graphics'];
const TG58_TEAM_MODES = ['snake', 'random', 'manual'];
const TG58_TEAM_NAMES = ['Tučňáci', 'Pakety', 'Jádra', 'Bajty', 'Routeři', 'Shelláci', 'Démoni', 'Pingři', 'Kořeny', 'Sokety'];
/** Třída, která neexistuje v reálné škole – skryje balíčky Labu určené jen pro týmové hry z běžného procvičování. */
const TG58_HIDDEN_CLASS = '__tg58_hidden__';
const TG58_SIGNALS = ['help' => 'Potřebujeme pomoc', 'done' => 'Máme to!', 'check' => 'Zkontrolujte nás'];
const TG58_SIGNAL_COOLDOWN_S = 15;
const TG58_SIGNAL_LOG_MAX = 40;
const TG58_XP_PARTICIPATION = 15;
const TG58_XP_WIN = 10;
const TG58_MIN_TEAMS = 2;
const TG58_MAX_TEAMS = 8;
const TG58_WRONG_MAX = 5;          // FAIR58-05: špatné odpovědi kvízových bran na žáka …
const TG58_WRONG_WINDOW_S = 60;    // … za minutu

// ---------------------------------------------------------------------------
// Úložiště a čas
// ---------------------------------------------------------------------------

function tg58_now(): int
{
    return is_int($GLOBALS['tg58_now_override'] ?? null) ? $GLOBALS['tg58_now_override'] : time();
}

function tg58_storage_dir(): string
{
    if (is_string($GLOBALS['tg58_storage_override'] ?? null)) return $GLOBALS['tg58_storage_override'];
    if (function_exists('lab57_storage_dir')) return lab57_storage_dir();
    return defined('STORAGE_DIR') ? STORAGE_DIR : __DIR__ . '/storage';
}

function tg58_index_path(): string
{
    return tg58_storage_dir() . '/teamgames_v58_index.json.php';
}

function tg58_session_path(string $id): string
{
    $id = (string)preg_replace('/[^a-z0-9]/', '', $id);
    return tg58_storage_dir() . '/teamgames_v58/' . $id . '.json.php';
}

function tg58_bank_path(): string
{
    return tg58_storage_dir() . '/teamgames_v58_bank.json.php';
}

/** Čtení pod sdíleným zámkem jádra úložiště (DAT58-04; RMW vždy přes tg58_update). Bez jádra (izolované audity) prosté čtení. */
function tg58_read(string $path): array
{
    if (function_exists('storage_read')) return storage_read($path, false);
    if (!is_file($path)) return [];
    $raw = (string)file_get_contents($path);
    $raw = preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Atomická úprava (jeden zámek přes čtení i zápis) – vždy přes storage_update z bootstrap.php. */
function tg58_update(string $path, callable $mutate): array
{
    if (!function_exists('storage_update')) throw new RuntimeException('Chybí úložiště aplikace (storage_update).');
    return storage_update($path, $mutate);
}

function tg58_csrf_ok(mixed $token): bool
{
    $expected = $_SESSION['csrf'] ?? null;
    return is_string($token) && $token !== '' && is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

function tg58_valid_id(string $id): bool
{
    return preg_match(TG58_ID_RE, $id) === 1;
}

function tg58_iso(?int $ts): ?string
{
    return $ts === null ? null : date(DATE_ATOM, $ts);
}

function tg58_ts(mixed $iso): ?int
{
    if (!is_string($iso) || $iso === '') return null;
    $ts = strtotime($iso);
    return $ts === false ? null : $ts;
}

function tg58_json_out(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/** Krátký otisk dat pro polling (`since=` → `{changed:false}` beze změny). */
function tg58_version_of(array $data): string
{
    return substr(sha1((string)json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR)), 0, 16);
}

/** Klouzavé okno v session žáka: nejvýš $max požadavků za $window s (vlastní pokusy, ne cizí). */
function tg58_rate_ok(string $bucket, int $max, int $window, int $now): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) return true;
    $list = is_array($_SESSION['tg58_rl'][$bucket] ?? null) ? $_SESSION['tg58_rl'][$bucket] : [];
    $list = array_values(array_filter($list, static fn($t): bool => is_int($t) && $t > $now - $window));
    if (count($list) >= $max) { $_SESSION['tg58_rl'][$bucket] = $list; return false; }
    $list[] = $now;
    $_SESSION['tg58_rl'][$bucket] = $list;
    return true;
}

/**
 * FAIR58-05: limit špatných odpovědí kvízových bran (Štafeta, Bingo, Úniková místnost) – max. TG58_WRONG_MAX
 * za TG58_WRONG_WINDOW_S na žáka. Evidence je v datech hry (pod zámkem relace), ne v session – nejde obejít
 * druhým zařízením ani smazáním cookies. Vyhodí výjimku s časem do dalšího pokusu.
 */
function tg58_wrong_guard(array $session, string $studentKey, int $now): void
{
    $list = tg58_wrong_recent($session, $studentKey, $now);
    if (count($list) < TG58_WRONG_MAX) return;
    $wait = max(1, min($list) + TG58_WRONG_WINDOW_S - $now);
    throw new RuntimeException(tr('Moc špatných odpovědí za sebou – další pokus za {s} s. Poraďte se v týmu.', ['s' => (string)$wait]));
}

/** Zapíše špatnou odpověď žáka do dat hry (vrací upravenou relaci; staré záznamy se čistí). */
function tg58_wrong_record(array $session, string $studentKey, int $now): array
{
    $list = tg58_wrong_recent($session, $studentKey, $now);
    $list[] = $now;
    $session['game']['wrong'][tg58_wrong_key($studentKey)] = $list;
    return $session;
}

function tg58_wrong_recent(array $session, string $studentKey, int $now): array
{
    $list = (array)($session['game']['wrong'][tg58_wrong_key($studentKey)] ?? []);
    return array_values(array_filter(array_map('intval', $list), static fn(int $t): bool => $t > $now - TG58_WRONG_WINDOW_S));
}

function tg58_wrong_key(string $studentKey): string
{
    return substr(sha1($studentKey), 0, 16);
}

// ---------------------------------------------------------------------------
// Drobní pomocníci pro pohledy (sdílené žákem/učitelem/projektorem – všechny tři require_once core.php)
// ---------------------------------------------------------------------------

function tg58_h(string $value): string
{
    return function_exists('e') ? e($value) : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tg58_status_label(string $status): string
{
    return match ($status) { 'running' => tr('Běží'), 'paused' => tr('Pauza'), 'finished' => tr('Skončila'), 'archived' => tr('Archiv'), default => tr('Připravuje se') };
}

function tg58_type_label(string $type): string
{
    return function_exists('tg58_game_spec') ? (string)(tg58_game_spec($type)['label'] ?? $type) : $type;
}

function tg58_secs_text(?int $s): string
{
    if ($s === null) return '–';
    return $s >= 60 ? intdiv($s, 60) . ' min ' . ($s % 60) . ' s' : $s . ' s';
}

/** Český popisek sloupce generické tabulky výsledků (jinak by se ukázal syrový interní klíč). */
function tg58_col_label(string $key): string
{
    return match ($key) {
        'team' => 'Tým', 'leg' => 'Úsek', 'legs_total' => 'Úseků celkem', 'finished' => 'Dohráno', 'time_s' => 'Čas', 'penalty_s' => 'Penalizace',
        'points' => 'Body', 'solved' => 'Vyřešeno', 'cells_total' => 'Políček celkem', 'rows_complete' => 'Řad', 'bonus_rows' => 'Řad s bonusem', 'everyone_contributed' => 'Přispěli všichni',
        'escaped' => 'Unikli', 'hints_used' => 'Nápovědy', 'roles' => 'Role',
        default => $key,
    };
}

/** Bezpečný text buňky generické tabulky (bool → ano/–, seznam → čárkami, jinak řetězec). */
function tg58_cell_text(mixed $v): string
{
    if (is_bool($v)) return $v ? 'ano' : '–';
    if (is_array($v)) return $v === [] ? '–' : implode(', ', array_map('strval', $v));
    if ($v === null) return '–';
    return (string)$v;
}

// ---------------------------------------------------------------------------
// Relace: čtení, výpis, atomická úprava
// ---------------------------------------------------------------------------

function tg58_index_entries(): array
{
    $rows = (array)(tg58_read(tg58_index_path())['entries'] ?? []);
    return array_values(array_filter($rows, static fn($r): bool => is_array($r) && is_string($r['id'] ?? null)));
}

function tg58_get(string $id): ?array
{
    if (!tg58_valid_id($id)) return null;
    $data = tg58_read(tg58_session_path($id));
    return isset($data['id']) ? $data : null;
}

/** @return list<array> relace třídy, nejnovější první (čte jen relace patřící dané třídě podle indexu) */
function tg58_sessions_for_class(string $classId): array
{
    $ids = array_values(array_filter($ids = array_map(static fn(array $e): string => (string)$e['id'], array_filter(tg58_index_entries(), static fn(array $e): bool => (string)($e['class_id'] ?? '') === $classId))));
    $rows = [];
    foreach ($ids as $id) {
        $s = tg58_get($id);
        if ($s !== null) $rows[] = $s;
    }
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return $rows;
}

/** Atomická úprava jedné relace pod zámkem (RMW). $fn dostane a vrátí relaci; RuntimeException zápis zruší. */
function tg58_mutate(string $id, callable $fn): array
{
    if (!tg58_valid_id($id)) throw new RuntimeException('Hra nebyla nalezena.');
    $path = tg58_session_path($id);
    $result = null;
    tg58_update($path, static function (array $d) use ($fn, &$result): array {
        if (!isset($d['id'])) throw new RuntimeException('Hra nebyla nalezena.');
        $result = $fn($d);
        if (!is_array($result)) throw new RuntimeException('Neplatný stav hry.');
        return $result;
    });
    return (array)$result;
}

/** Aktuální (samoopravný) stav: běžící hra s vypršeným časem se tady rovnou vidí jako 'finished'. */
function tg58_status(array $session, int $now): string
{
    $status = (string)($session['status'] ?? 'lobby');
    if ($status === 'running' && tg58_remaining($session, $now) === 0) return 'finished';
    return in_array($status, TG58_STATUSES, true) ? $status : 'lobby';
}

function tg58_elapsed(array $session, int $now): int
{
    $started = tg58_ts($session['started_at'] ?? null);
    if ($started === null) return 0;
    $pausedTotal = (int)($session['paused_total_s'] ?? 0);
    $status = (string)($session['status'] ?? '');
    if ($status === 'paused') {
        $pausedAt = tg58_ts($session['paused_at'] ?? null) ?? $now;
        return max(0, $pausedAt - $started - $pausedTotal);
    }
    return max(0, $now - $started - $pausedTotal);
}

/** null = hra bez pevného časového limitu (končí ručně nebo splněním cíle). */
function tg58_remaining(array $session, int $now): ?int
{
    $duration = (int)($session['duration_s'] ?? 0);
    if ($duration <= 0) return null;
    return max(0, $duration - tg58_elapsed($session, $now));
}

/** Živá (running/paused) relace třídy, nebo null. Vypršelou running relaci si tu rovnou zapíšeme jako finished. */
function tg58_live_for_class(string $classId): ?array
{
    $now = tg58_now();
    $live = null;
    foreach (tg58_sessions_for_class($classId) as $session) {
        $status = tg58_status($session, $now);
        if ($status === 'running' || $status === 'paused') { $live ??= $session; continue; }
        if ($status === 'finished' && (string)($session['status'] ?? '') === 'running') {
            try { tg58_finish_session((string)$session['id'], $now); } catch (Throwable $e) { error_log('EDUCANET v58 tg finish: ' . $e->getMessage()); }
        }
    }
    return $live;
}

function tg58_finish_session(string $id, int $now): array
{
    return tg58_mutate($id, static function (array $s) use ($now): array {
        if (in_array((string)$s['status'], ['finished', 'archived'], true)) return $s;
        $s['status'] = 'finished';
        $s['finished_at'] = tg58_iso($now);
        return $s;
    });
}

// ---------------------------------------------------------------------------
// Řádek/třída, tým a soukromí jmen (stejné principy jako Aréna v57 / Robotí liga)
// ---------------------------------------------------------------------------

/** Výchozí linie podle třídy: 1.A/2.A = grafika a webdesign (bez síťové teorie), jinak sítě. Lze přepsat v nastavení hry. */
function tg58_line_for_class(string $classId): string
{
    return in_array($classId, ['class_1a', 'class_2a'], true) ? 'graphics' : 'networks';
}

function tg58_roster(string $classId): array
{
    $override = $GLOBALS['tg58_roster_override'][$classId] ?? null;
    if (is_array($override)) return $override;
    if (function_exists('arena57_roster')) return arena57_roster($classId);
    return function_exists('project_students_for_class') ? project_students_for_class($classId) : [];
}

/** Vyvážené týmy hadím draftem podle bodů z Labu (shoda = náhodně), nebo náhodně/ručně. */
function tg58_form_teams(string $classId, string $mode, int $teamCount, array $manual = []): array
{
    $roster = tg58_roster($classId);
    if (count($roster) < TG58_MIN_TEAMS) throw new RuntimeException('Na týmovou hru jsou potřeba aspoň dva žáci na soupisce třídy.');
    $teamCount = max(TG58_MIN_TEAMS, min(TG58_MAX_TEAMS, $teamCount, count($roster)));
    $names = TG58_TEAM_NAMES;
    shuffle($names);
    $blank = static function (int $n) use ($names): array {
        $out = [];
        for ($i = 0; $i < $n; $i++) $out[] = ['id' => 't' . ($i + 1), 'name' => $names[$i % count($names)], 'members' => []];
        return $out;
    };
    if ($mode === 'manual') {
        $teams = $blank(max($teamCount, count($manual)));
        $seen = [];
        foreach ($manual as $i => $memberKeys) {
            if (!isset($teams[$i])) continue;
            foreach ((array)$memberKeys as $key) {
                $key = (string)$key;
                if ($key === '' || isset($seen[$key]) || !isset($roster[$key])) continue;
                $seen[$key] = true;
                $teams[$i]['members'][] = $key;
            }
        }
        foreach (array_keys($roster) as $key) {
            $key = (string)$key;
            if (isset($seen[$key])) continue;
            $best = 0;
            foreach ($teams as $i => $t) { if (count($t['members']) < count($teams[$best]['members'])) $best = $i; }
            $teams[$best]['members'][] = $key;
        }
        return array_values(array_filter($teams, static fn(array $t): bool => $t['members'] !== []));
    }
    $points = [];
    foreach (array_keys($roster) as $key) $points[(string)$key] = function_exists('lab57_student_summary') ? (int)(lab57_student_summary($classId, (string)$key)['points'] ?? 0) : 0;
    if ($mode === 'random' || !function_exists('arena57_snake_teams')) {
        $keys = array_keys($points);
        shuffle($keys);
        $teams = $blank($teamCount);
        foreach ($keys as $i => $key) $teams[$i % $teamCount]['members'][] = $key;
        return $teams;
    }
    $size = max(2, (int)ceil(count($points) / $teamCount));
    $teams = arena57_snake_teams($points, $size);
    $out = [];
    foreach (array_slice(array_values($teams), 0, TG58_MAX_TEAMS) as $i => $team) {
        $out[] = ['id' => 't' . ($i + 1), 'name' => (string)$team['name'], 'members' => array_values(array_map('strval', (array)$team['members']))];
    }
    return $out;
}

function tg58_team_of(array $session, string $studentKey): ?string
{
    foreach ((array)($session['teams'] ?? []) as $team) {
        if (in_array($studentKey, (array)($team['members'] ?? []), true)) return (string)$team['id'];
    }
    return null;
}

function tg58_team(array $session, string $teamId): ?array
{
    foreach ((array)($session['teams'] ?? []) as $team) { if ((string)$team['id'] === $teamId) return $team; }
    return null;
}

/** Pozdní příchozí (žák bez týmu, který se ke hře připojí po startu) jde do právě nejmenšího týmu. */
function tg58_join_smallest_team(array $session, string $studentKey): array
{
    if (tg58_team_of($session, $studentKey) !== null || (array)($session['teams'] ?? []) === []) return $session;
    $teams = (array)$session['teams'];
    $best = 0;
    foreach ($teams as $i => $t) { if (count((array)$t['members']) < count((array)$teams[$best]['members'])) $best = $i; }
    $teams[$best]['members'][] = $studentKey;
    $session['teams'] = $teams;
    return $session;
}

function tg58_display_name(string $label): string
{
    if (function_exists('lab57_display_name')) return lab57_display_name($label);
    $parts = preg_split('/\s+/u', trim($label)) ?: [];
    if ($parts === [] || $parts[0] === '') return tr('Žák');
    $first = (string)array_shift($parts);
    return trim($first . ($parts !== [] ? ' ' . mb_substr((string)end($parts), 0, 1) . '.' : ''));
}

function tg58_label(string $key, string $fallback, array $roster): string
{
    $name = trim((string)($roster[$key]['label'] ?? ''));
    return $name !== '' ? $name : ($fallback !== '' ? $fallback : tr('Žák'));
}

/** full = celé jméno; initials = „Adam K.“; anon = „Hráč N“ (divák vidí sebe jako „Ty“). Učitel/projektor: $viewerKey = null. */
function tg58_public_name(string $mode, string $key, string $label, ?string $viewerKey, int $no): string
{
    if ($viewerKey !== null && $viewerKey === $key && $mode === 'anon') return tr('Ty');
    if ($mode === 'full') return $label;
    if ($mode === 'anon') return tr('Hráč {cislo}', ['cislo' => (string)$no]);
    return tg58_display_name($label);
}

/** Jméno týmu je vždy veřejné (nikdy neprozradí jednotlivce), i v anonymním režimu. */
function tg58_team_label(array $session, string $teamId): string
{
    $team = tg58_team($session, $teamId);
    return $team !== null ? (string)$team['name'] : $teamId;
}

/** Členové týmu pro zobrazení žákovi vlastnímu týmu (nikdy cizímu, v anon režimu vůbec). */
function tg58_team_mates(array $session, string $teamId, string $viewerKey): array
{
    $mode = (string)($session['names'] ?? 'initials');
    if ($mode === 'anon') return [];
    $team = tg58_team($session, $teamId);
    if ($team === null) return [];
    $roster = tg58_roster((string)$session['class_id']);
    $out = [];
    foreach ((array)$team['members'] as $key) {
        $key = (string)$key;
        if ($key === $viewerKey) continue;
        $out[] = tg58_public_name($mode, $key, tg58_label($key, '', $roster), $viewerKey, 0);
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Signály (žádný chat – jen předdefinované signály, s krátkým cooldownem)
// ---------------------------------------------------------------------------

function tg58_send_signal(string $id, string $teamId, string $studentKey, string $kind, int $now): array
{
    if (!isset(TG58_SIGNALS[$kind])) throw new RuntimeException(tr('Neznámý signál.'));
    return tg58_mutate($id, static function (array $s) use ($teamId, $studentKey, $kind, $now): array {
        if (tg58_team_of($s, $studentKey) !== $teamId) throw new RuntimeException(tr('Signál patří jen vlastnímu týmu.'));
        $log = array_values(array_filter((array)($s['signals'] ?? []), 'is_array'));
        foreach (array_reverse($log) as $row) {
            if ((string)($row['team_id'] ?? '') !== $teamId) continue;
            $at = tg58_ts($row['at'] ?? null) ?? 0;
            if ($now - $at < TG58_SIGNAL_COOLDOWN_S) throw new RuntimeException(tr('Tenhle tým právě poslal signál – počkej chvilku.'));
            break;
        }
        $log[] = ['team_id' => $teamId, 'student_key' => $studentKey, 'kind' => $kind, 'at' => tg58_iso($now)];
        $s['signals'] = array_slice($log, -TG58_SIGNAL_LOG_MAX);
        return $s;
    });
}

/** Signály pro zobrazení: $forTeam = null vidí všechny (projektor/učitel), jinak jen daný tým. */
function tg58_public_signals(array $session, ?string $forTeam, int $limit = 10): array
{
    // Literální tr() volání pro msgid extrakci – hodnoty musí přesně odpovídat TG58_SIGNALS (výše v tomto souboru).
    $signalLabels = ['help' => tr('Potřebujeme pomoc'), 'done' => tr('Máme to!'), 'check' => tr('Zkontrolujte nás')];
    $out = [];
    foreach (array_reverse((array)($session['signals'] ?? [])) as $row) {
        if (!is_array($row) || ($forTeam !== null && (string)($row['team_id'] ?? '') !== $forTeam)) continue;
        $kind = (string)$row['kind'];
        $out[] = ['team' => tg58_team_label($session, (string)$row['team_id']), 'kind' => $kind, 'label' => $signalLabels[$kind] ?? (TG58_SIGNALS[$kind] ?? $kind), 'at' => (string)$row['at']];
        if (count($out) >= $limit) break;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// XP: vyzvednutí výhradně v požadavku žáka (learning_award_once drží profil v jeho session)
// ---------------------------------------------------------------------------

/** 15 XP za účast v dohrané hře, +10 když žákův tým vyhrál (podle 'winners' nastavených hrou). Každá hra jen jednou. */
function tg58_xp_for(array $session, string $studentKey): int
{
    $teamId = tg58_team_of($session, $studentKey);
    $won = $teamId !== null && in_array($teamId, (array)($session['game']['winners'] ?? []), true);
    return TG58_XP_PARTICIPATION + ($won ? TG58_XP_WIN : 0);
}

function tg58_took_part(array $session, string $studentKey): bool
{
    if (tg58_team_of($session, $studentKey) !== null) return true;
    foreach ((array)($session['signals'] ?? []) as $row) { if ((string)($row['student_key'] ?? '') === $studentKey) return true; }
    return false;
}

/** Vyzvedne XP za všechny dohrané hry třídy, které žák ještě nemá – vrací počet nově připsaných her. */
function tg58_claim_xp(string $classId, string $studentKey): int
{
    if ($studentKey === '' || !function_exists('learning_award_once')) return 0;
    $sessionOk = session_status() === PHP_SESSION_ACTIVE;
    $done = $sessionOk && is_array($_SESSION['tg58_xp'] ?? null) ? $_SESSION['tg58_xp'] : [];
    $awarded = 0;
    try {
        foreach (tg58_sessions_for_class($classId) as $session) {
            $id = (string)$session['id'];
            if (isset($done[$id]) || tg58_status($session, tg58_now()) !== 'finished' && (string)$session['status'] !== 'finished') continue;
            if (!tg58_took_part($session, $studentKey)) { $done[$id] = 1; continue; }
            $xp = tg58_xp_for($session, $studentKey);
            if ($xp > 0 && learning_award_once($classId, 'tg58:' . $id, $xp)) $awarded++;
            $done[$id] = 1;
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v58 týmové hry XP: ' . $e->getMessage());
    }
    if ($sessionOk) $_SESSION['tg58_xp'] = $done;
    return $awarded;
}

// ---------------------------------------------------------------------------
// Pro navigaci (student_v55.php): živá hra třídy, jen čte.
// ---------------------------------------------------------------------------

function tg58_open_for_class(string $classId): ?array
{
    $session = tg58_live_for_class($classId);
    if ($session === null) return null;
    return ['id' => (string)$session['id'], 'title' => (string)$session['title'], 'type' => (string)$session['type']];
}
