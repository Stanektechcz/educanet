<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/taskbank_v75.php';
require_once __DIR__ . '/hints_v80.php';

/**
 * EDUCANET v80 · podpora učení – úložiště a pravidla (rozložené opakování chyb, počty nápověd, stav cvičení, cíle).
 *
 * Úložiště po třídách storage/learning_v80_<třída>.json.php (zápis jen storage_update):
 *   {"v":1,"students":{ref:{
 *      "rep":  {úloha:{"s":0–2 (stupeň), "due_at":půlnoc dne opakování, "at":čas zařazení, "c":počet opakování, "f":[id chybných polí]}},
 *      "hint": {klíč:{"r":kolo, "n":odkrytá úroveň 0–3, "c":počet odkrytí celkem, "u":[kola s nápovědou]}},   klíč = úloha | rep:úloha
 *      "w":    {cvičení:{"r":kolo, "t":pokusy v kole, "ok":vyřešeno}},
 *      "log":  {"rep_ok_at":[časy úspěšných opakování], "w_ok_at":[časy vyřešených cvičení], "rep_done":počet dokončených řad 1-3-7},
 *      "goals":[{"id","k":druh,"n":cíl,"wk":týden ISO,"at","done_at"}], "last_at":poslední aktivita}}}
 * ref = tb75_student_ref() (student_id nebo pseudonym úloh v75) – žádné jméno, e-mail ani text odpovědi. Nápovědy se ukládají jen jako počty.
 * Opakování: chybně vyřešená úloha se vrátí za 1 den, po správné odpovědi za 3 dny, pak za 7 dní; po třetí správné odpovědi je řada hotová.
 * Chyba v opakování = znovu od 1 dne. Každé opakování má novou deterministickou variantu (semínko rep80|ref, kolo = c + 1).
 * Retence: záznam žáka bez aktivity déle než LR80_RETENTION_DAYS se maže při každém zápisu třídy; po odchodu žáka maže lr80_retention_purge()
 * (volá retention_v76.php → tools/v58_retention.php, stejné aliasy jako data v75).
 */

const LR80_VERSION = 1;
const LR80_REP_DAYS = [1, 3, 7];
const LR80_RETENTION_DAYS = 365;
const LR80_LOG_KEEP = 30;
const LR80_HINT_ROUNDS_KEEP = 10;
const LR80_CLASS_RE = '/^class_[a-z0-9]{1,10}$/';
const LR80_KEY_RE = '/^(rep:)?[a-z]{2,5}_[a-z]{2,8}$/';

function lr80_path(string $classId): string
{
    if (preg_match(LR80_CLASS_RE, $classId) !== 1) throw new InvalidArgumentException('Neplatná třída.');
    return STORAGE_DIR . '/learning_v80_' . $classId . '.json.php';
}

/** Data třídy (paměť požadavku; po zápisu se obnoví). */
function lr80_data(string $classId): array
{
    $memo = &$GLOBALS['lr80_memo'];
    if (!is_array($memo)) $memo = [];
    if (!array_key_exists($classId, $memo)) {
        $memo[$classId] = preg_match(LR80_CLASS_RE, $classId) === 1 ? storage_read(lr80_path($classId), false) : [];
    }
    return is_array($memo[$classId]) ? $memo[$classId] : [];
}

/** Normalizovaný stav žáka (chybějící klíče doplní). */
function lr80_normalize(array $raw): array
{
    $s = ['rep' => [], 'hint' => [], 'w' => [], 'log' => ['rep_ok_at' => [], 'w_ok_at' => [], 'rep_done' => 0], 'goals' => [], 'last_at' => 0];
    foreach (['rep', 'hint', 'w'] as $k) if (is_array($raw[$k] ?? null)) $s[$k] = $raw[$k];
    if (is_array($raw['log'] ?? null)) {
        $s['log']['rep_ok_at'] = array_values(array_map('intval', (array)($raw['log']['rep_ok_at'] ?? [])));
        $s['log']['w_ok_at'] = array_values(array_map('intval', (array)($raw['log']['w_ok_at'] ?? [])));
        $s['log']['rep_done'] = (int)($raw['log']['rep_done'] ?? 0);
    }
    $s['goals'] = array_values(array_filter((array)($raw['goals'] ?? []), 'is_array'));
    $s['last_at'] = (int)($raw['last_at'] ?? 0);
    return $s;
}

function lr80_state(string $classId, string $ref, ?array $data = null): array
{
    return lr80_normalize((array)((($data ?? lr80_data($classId))['students'] ?? [])[$ref] ?? []));
}

/** Reference žáka (stejná jako v bance úloh v75). */
function lr80_ref(string $classId, string $studentKey): string
{
    return tb75_student_ref($classId, $studentKey);
}

/**
 * Atomická úprava stavu jednoho žáka: $fn(stav, now): stav. Současně smaže žáky bez aktivity > LR80_RETENTION_DAYS a zarovná cíle.
 * Vrací nový stav žáka.
 */
function lr80_update(string $classId, string $ref, callable $fn, ?int $now = null): array
{
    $now ??= time();
    $out = [];
    if (!function_exists('pg80_goals_settle')) require_once __DIR__ . '/progress_v80.php';   // cíle týdne se zarovnají při každém zápisu
    storage_update(lr80_path($classId), static function (array $d) use ($classId, $ref, $fn, $now, &$out): array {
        $students = is_array($d['students'] ?? null) ? $d['students'] : [];
        $limit = $now - LR80_RETENTION_DAYS * 86400;
        foreach ($students as $r => $st) if (!is_array($st) || (int)($st['last_at'] ?? 0) < $limit) unset($students[$r]);
        $state = $fn(lr80_normalize((array)($students[$ref] ?? [])), $now);
        $state['last_at'] = $now;
        $state = pg80_goals_settle($classId, $ref, $state, $now);
        $students[$ref] = $state;
        $out = $state;
        return ['v' => LR80_VERSION, 'students' => $students];
    });
    unset($GLOBALS['lr80_memo'][$classId]);
    return $out;
}


function lr80_add_days(int $now, int $days): int
{
    return (int)strtotime(date('Y-m-d', $now) . ' +' . $days . ' days 00:00:00');
}

// ---------------------------------------------------------------------------------------------- rozložené opakování

/** Zařadí (nebo vrátí na začátek) chybně vyřešenou úlohu do opakování. Čistá funkce nad stavem. */
function lr80_rep_enqueue(array $state, string $task, array $wrongFields, int $now): array
{
    if (!isset(TB75_TASKS[$task])) return $state;
    $item = is_array($state['rep'][$task] ?? null) ? $state['rep'][$task] : null;
    $due = lr80_add_days($now, LR80_REP_DAYS[0]);
    $fields = array_values(array_unique(array_merge((array)($item['f'] ?? []), array_values(array_filter(array_map('strval', $wrongFields), static fn(string $f): bool => preg_match('/^[a-z0-9]{1,12}$/', $f) === 1)))));
    $state['rep'][$task] = ['s' => 0, 'due_at' => $item !== null ? min((int)$item['due_at'], $due) : $due, 'at' => $item !== null ? (int)$item['at'] : $now,
        'c' => (int)($item['c'] ?? 0), 'f' => array_slice($fields, 0, 6)];
    return $state;
}

/** Úlohy k opakování dnes (nejstarší první). @return list<string> */
function lr80_rep_due(array $state, int $now): array
{
    $due = [];
    foreach ((array)$state['rep'] as $task => $item) {
        if (is_array($item) && isset(TB75_TASKS[(string)$task]) && (int)($item['due_at'] ?? PHP_INT_MAX) <= $now) $due[(string)$task] = (int)$item['due_at'];
    }
    asort($due);
    return array_keys($due);
}

/** Naplánovaná (ještě ne splatná) opakování: úloha → půlnoc dne. @return array<string,int> */
function lr80_rep_planned(array $state, int $now): array
{
    $out = [];
    foreach ((array)$state['rep'] as $task => $item) if (is_array($item) && (int)($item['due_at'] ?? 0) > $now) $out[(string)$task] = (int)$item['due_at'];
    asort($out);
    return $out;
}

/** Varianta opakování (deterministická, jiná než v bance úloh). */
function lr80_rep_variant(string $task, string $ref, array $item): array
{
    return tb75_variant($task, 'rep80|' . $ref, (int)($item['c'] ?? 0) + 1);
}

/** Nový stupeň po odpovědi. @return array{item:?array,done:bool,days:int} (item null = řada dokončena) */
function lr80_rep_schedule(array $item, bool $ok, int $now): array
{
    $c = (int)($item['c'] ?? 0) + 1;
    if (!$ok) return ['item' => ['s' => 0, 'due_at' => lr80_add_days($now, LR80_REP_DAYS[0]), 'at' => (int)($item['at'] ?? $now), 'c' => $c, 'f' => (array)($item['f'] ?? [])], 'done' => false, 'days' => LR80_REP_DAYS[0]];
    $s = (int)($item['s'] ?? 0) + 1;
    if ($s >= count(LR80_REP_DAYS)) return ['item' => null, 'done' => true, 'days' => 0];
    return ['item' => ['s' => $s, 'due_at' => lr80_add_days($now, LR80_REP_DAYS[$s]), 'at' => (int)($item['at'] ?? $now), 'c' => $c, 'f' => (array)($item['f'] ?? [])], 'done' => false, 'days' => LR80_REP_DAYS[$s]];
}

/**
 * Odpověď na opakování (jen splatná položka, jeden pokus za den). Vrací ok, error (not_due|unknown_task|empty), result, variant, days, done, why.
 */
function lr80_rep_submit(string $classId, string $studentKey, string $task, array $answers, ?int $now = null): array
{
    $now ??= time();
    if (!in_array($task, tb75_tasks_for_class($classId), true)) return ['ok' => false, 'error' => 'unknown_task'];
    if (array_filter($answers, static fn($v): bool => is_string($v) && trim($v) !== '') === []) return ['ok' => false, 'error' => 'empty'];
    $answers = array_map(static fn($v): string => is_string($v) ? mb_substr($v, 0, TB75_ANSWER_MAX) : '', $answers);
    $ref = lr80_ref($classId, $studentKey);
    $res = ['ok' => false, 'error' => 'not_due'];
    lr80_update($classId, $ref, static function (array $s, int $now) use ($task, $answers, $ref, &$res): array {
        $item = is_array($s['rep'][$task] ?? null) ? $s['rep'][$task] : null;
        if ($item === null || (int)$item['due_at'] > $now) return $s;
        $variant = lr80_rep_variant($task, $ref, $item);
        $check = tb75_check($variant, $answers);
        $next = lr80_rep_schedule($item, $check['ok'], $now);
        if ($next['item'] === null) unset($s['rep'][$task]); else $s['rep'][$task] = $next['item'];
        if ($check['ok']) $s['log']['rep_ok_at'] = array_slice(array_merge($s['log']['rep_ok_at'], [$now]), -LR80_LOG_KEEP);
        if ($next['done']) $s['log']['rep_done']++;
        $res = ['ok' => true, 'error' => null, 'result' => $check, 'variant' => $variant, 'days' => $next['days'], 'done' => $next['done'],
            'why' => $check['ok'] ? [] : hn80_diagnose($variant, $answers)];
        return $s;
    }, $now);
    return $res;
}

/** Háček po odevzdání úlohy v75: chybná pole → opakování; každé odevzdání zarovná cíle. Selhání nesmí zablokovat úlohu. */
function lr80_on_task_result(string $classId, string $studentKey, string $task, array $result, ?int $now = null): void
{
    try {
        $wrong = array_keys(array_filter((array)($result['fields'] ?? []), static fn($ok): bool => !$ok));
        lr80_update($classId, lr80_ref($classId, $studentKey), static fn(array $s, int $now): array => $wrong !== [] ? lr80_rep_enqueue($s, $task, $wrong, $now) : $s, $now);
    } catch (Throwable $e) {
        error_log('EDUCANET v80 učení: opakování se nezapsalo (' . get_class($e) . ')');
    }
}

// ---------------------------------------------------------------------------------------------- nápovědy (jen počty)

/** Odkrytá úroveň nápovědy pro kolo (jiné kolo = 0). */
function lr80_hint_level(array $state, string $key, int $round): int
{
    $h = $state['hint'][$key] ?? null;
    return is_array($h) && (int)($h['r'] ?? 0) === $round ? max(0, min(HN80_LEVELS, (int)($h['n'] ?? 0))) : 0;
}

/** Odkryje další nápovědu (max. 3) a započítá ji. @return int nová úroveň (0 = neplatný klíč) */
function lr80_hint_reveal(string $classId, string $studentKey, string $key, int $round, ?int $now = null): int
{
    if (preg_match(LR80_KEY_RE, $key) !== 1 || $round < 1) return 0;
    $level = 0;
    lr80_update($classId, lr80_ref($classId, $studentKey), static function (array $s) use ($key, $round, &$level): array {
        $h = is_array($s['hint'][$key] ?? null) ? $s['hint'][$key] : ['r' => $round, 'n' => 0, 'c' => 0, 'u' => []];
        if ((int)($h['r'] ?? 0) !== $round) $h = ['r' => $round, 'n' => 0, 'c' => (int)($h['c'] ?? 0), 'u' => (array)($h['u'] ?? [])];
        if ((int)$h['n'] < HN80_LEVELS) {
            $h['n'] = (int)$h['n'] + 1;
            $h['c'] = (int)$h['c'] + 1;
            $h['u'] = array_slice(array_values(array_unique(array_merge(array_map('intval', (array)$h['u']), [$round]))), -LR80_HINT_ROUNDS_KEEP);
        }
        $level = (int)$h['n'];
        $s['hint'][$key] = $h;
        return $s;
    }, $now);
    return $level;
}

/** Počet použitých nápověd celkem (pro učitele: jen čísla). */
function lr80_hint_total(array $state): int
{
    return array_sum(array_map(static fn($h): int => is_array($h) ? (int)($h['c'] ?? 0) : 0, (array)$state['hint']));
}

/** Kolo úlohy bylo vyřešené s nápovědou? */
function lr80_round_hinted(array $state, string $key, int $round): bool
{
    return in_array($round, array_map('intval', (array)(($state['hint'][$key] ?? [])['u'] ?? [])), true);
}

// ---------------------------------------------------------------------------------------------- retence po odchodu žáka

/** Smaže záznamy žáků ($refs) ze všech souborů tříd; dry-run jen spočítá. @return int počet smazaných záznamů */
function lr80_retention_purge(array $refs, bool $dryRun): int
{
    if ($refs === [] || !defined('STORAGE_DIR')) return 0;
    $set = array_flip(array_map('strval', $refs));
    $n = 0;
    foreach (glob(STORAGE_DIR . '/learning_v80_class_*.json.php') ?: [] as $path) {
        $scrub = static function (array $d) use ($set, &$n): array {
            foreach (array_keys((array)($d['students'] ?? [])) as $ref) {
                if (!isset($set[(string)$ref])) continue;
                $n++;
                unset($d['students'][$ref]);
            }
            return $d;
        };
        if ($dryRun) $scrub(storage_read($path, false)); else storage_update($path, $scrub);
    }
    unset($GLOBALS['lr80_memo']);
    return $n;
}
