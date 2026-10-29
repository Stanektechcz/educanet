<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/widgets_v80.php';   // + learning_v80.php (opakování, stav cvičení)

/**
 * EDUCANET v80.1 · podpora žáka učitelem: rychlá zpětná vazba a přidělení procvičování (úložiště, pravidla, stav splnění).
 *
 * Úložiště po třídách storage/support_v80_1_<třída>.json.php (zápis jen storage_update):
 *   {"v":1,"fb":[{"id","ref","k":praise|next|fix,"t":text,"at","by"}],
 *          "as":[{"id","ref","k":task|set|rep|widget,"i":položka,"tasks":[úlohy],"base":{…},"at","by"}]}
 *   ref = tb75_student_ref() (student_id nebo pseudonym úloh v75, stejně jako v80) – žádné jméno ani e-mail; by = 16 hex z hashe účtu učitele.
 *   Text zpětné vazby píše učitel (max. TS801_TEXT_MAX znaků) a žák ho vidí tak, jak byl napsán (bez překladu).
 * Fráze učitele storage/support_v80_1_phrases.json.php = {"v":1,"teachers":{hash:[{"id","k","t"}]}} (jen text frází, bez dat žáků).
 * Splnění přidělení se počítá z dat žáka (úlohy v75, opakování a cvičení v80) – nic se nezapisuje navíc:
 *   task/set  každá úloha má po přidělení správně vyřešené kolo,   rep  každá úloha balíčku byla po přidělení zopakovaná (nebo řada skončila),
 *   widget    cvičení má víc vyřešených kol než při přidělení.
 * „rep“ zařadí úlohy do opakování v80 na dnešek (learning_v80_<třída>), takže se objeví i v Opakování chyb.
 * Retence: zpětná vazba a přidělení starší než TS801_RETENTION_DAYS se mažou při každém zápisu třídy; po odchodu žáka maže
 * ts801_retention_purge() (retention_v76.php → tools/v58_retention.php, řádek v80_1_data_purged=). Do URL ani logů nic nejde.
 */

const TS801_VERSION = 1;
const TS801_KINDS = ['praise' => 'Pochvala', 'next' => 'Další krok', 'fix' => 'Oprava'];
const TS801_PHRASES = [
    'praise' => ['Skvělá práce, je vidět, že tomu rozumíš.', 'Tohle se ti povedlo – jen tak dál.', 'Výborně, zvládl(a) jsi to samostatně.', 'Pěkný posun oproti minulé hodině.'],
    'next' => ['Zkus teď stejný typ úlohy s jinými čísly.', 'Vrať se ke kroku, kde ses zasekl(a), a zkus ho bez nápovědy.', 'Dokonči prosím opakování chyb na dnešek.', 'Vysvětli si postup vlastními slovy a pak úlohu vyřeš.'],
    'fix' => ['Zkontroluj výpočet ještě jednou – chyba je v detailu.', 'Pozor na rozdíl mezi síťovou adresou a broadcastem.', 'Přečti si zadání pozorně – odpověď neodpovídá otázce.', 'Porovnej svůj postup s vyřešeným krokem v nápovědě.'],
];
const TS801_TEXT_MAX = 400;
const TS801_PHRASE_MAX = 200;
const TS801_OWN_PHRASES_MAX = 30;
const TS801_FB_KEEP = 30;
const TS801_OPEN_MAX = 10;
const TS801_SELECT_MAX = 40;
const TS801_RETENTION_DAYS = 180;
const TS801_REP_AUTO = 3;
const TS801_ACTIONS = ['ts801_feedback_send', 'ts801_feedback_withdraw', 'ts801_assign', 'ts801_assign_cancel', 'ts801_phrase_add', 'ts801_phrase_delete', 'ts801_select'];
const TS801_CLASS_RE = '/^class_[a-z0-9]{1,10}$/';
const TS801_ID_RE = '/^(fb|as|ph)_[0-9a-f]{12}$/';

function ts801_path(string $classId): string
{
    if (preg_match(TS801_CLASS_RE, $classId) !== 1) throw new InvalidArgumentException('Neplatná třída.');
    return STORAGE_DIR . '/support_v80_1_' . $classId . '.json.php';
}

function ts801_phrases_path(): string
{
    return STORAGE_DIR . '/support_v80_1_phrases.json.php';
}

/** Data třídy (paměť požadavku; po zápisu se obnoví). */
function ts801_data(string $classId): array
{
    $memo = &$GLOBALS['ts801_memo'];
    if (!is_array($memo)) $memo = [];
    if (!array_key_exists($classId, $memo)) $memo[$classId] = preg_match(TS801_CLASS_RE, $classId) === 1 ? storage_read(ts801_path($classId), false) : [];
    $d = is_array($memo[$classId]) ? $memo[$classId] : [];
    return ['fb' => array_values(array_filter((array)($d['fb'] ?? []), 'is_array')), 'as' => array_values(array_filter((array)($d['as'] ?? []), 'is_array'))];
}

function ts801_id(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(6));
}

/** Hash přihlášeného učitele (16 hex z vlastnického klíče účtu v59; bez účtů společný „legacy“). */
function ts801_teacher_hash(): string
{
    $key = function_exists('teacher59_owner_key') ? (string)(teacher59_owner_key() ?? 'legacy') : 'legacy';
    return substr(hash('sha256', 'ts801|' . $key), 0, 16);
}

/** Text od učitele: bez řídicích znaků, max. 2 prázdné řádky, oříznutý na $max znaků. */
function ts801_clean_text(string $text, int $max): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = (string)preg_replace('/[^\P{C}\n]/u', '', $text);
    $text = (string)preg_replace("/\n{3,}/", "\n\n", $text);
    return trim(mb_substr(trim($text), 0, $max));
}

/** Atomická úprava dat třídy; současně smaže záznamy starší než retence. Vrací výsledek $fn přes $out. */
function ts801_update(string $classId, callable $fn, ?int $now = null): void
{
    $now ??= time();
    storage_update(ts801_path($classId), static function (array $d) use ($fn, $now): array {
        $limit = $now - TS801_RETENTION_DAYS * 86400;
        $keep = static fn(array $rows): array => array_values(array_filter($rows, static fn($r): bool => is_array($r) && (int)($r['at'] ?? 0) >= $limit));
        $d = ['v' => TS801_VERSION, 'fb' => $keep((array)($d['fb'] ?? [])), 'as' => $keep((array)($d['as'] ?? []))];
        return $fn($d, $now);
    });
    unset($GLOBALS['ts801_memo'][$classId]);
}

/** Klíče žáků z formuláře → [klíč => ref] jen pro žáky ze seznamu třídy (max. TS801_SELECT_MAX). */
function ts801_student_refs(string $classId, array $keys): array
{
    $roster = project_students_for_class($classId);
    $out = [];
    foreach ($keys as $key) {
        if (!is_string($key) || !isset($roster[$key]) || isset($out[$key])) continue;
        $out[$key] = tb75_student_ref($classId, $key);
        if (count($out) >= TS801_SELECT_MAX) break;
    }
    return $out;
}

// ---------------------------------------------------------------------------------------------- zpětná vazba

/** Pošle zpětnou vazbu vybraným žákům. @return int počet žáků */
function ts801_feedback_send(string $classId, array $refs, string $kind, string $text, string $by, ?int $now = null): int
{
    if (!isset(TS801_KINDS[$kind])) throw new RuntimeException('Vyberte druh zpětné vazby: pochvala, další krok, nebo oprava.');
    $text = ts801_clean_text($text, TS801_TEXT_MAX);
    if ($text === '') throw new RuntimeException('Vyberte připravenou frázi nebo napište text zprávy.');
    if ($refs === []) throw new RuntimeException('Vyberte aspoň jednoho žáka.');
    ts801_update($classId, static function (array $d, int $now) use ($refs, $kind, $text, $by): array {
        foreach (array_unique(array_values($refs)) as $ref) $d['fb'][] = ['id' => ts801_id('fb'), 'ref' => $ref, 'k' => $kind, 't' => $text, 'at' => $now, 'by' => $by];
        $count = [];
        for ($i = count($d['fb']) - 1; $i >= 0; $i--) {   // nejvýš TS801_FB_KEEP posledních zpráv na žáka
            $ref = (string)$d['fb'][$i]['ref'];
            $count[$ref] = ($count[$ref] ?? 0) + 1;
            if ($count[$ref] > TS801_FB_KEEP) unset($d['fb'][$i]);
        }
        $d['fb'] = array_values($d['fb']);
        return $d;
    }, $now);
    return count(array_unique(array_values($refs)));
}

/** Stáhne zpětnou vazbu (smaže zprávu). */
function ts801_feedback_withdraw(string $classId, string $id): bool
{
    $done = false;
    if (preg_match(TS801_ID_RE, $id) !== 1) return false;
    ts801_update($classId, static function (array $d) use ($id, &$done): array {
        $before = count($d['fb']);
        $d['fb'] = array_values(array_filter($d['fb'], static fn(array $r): bool => (string)($r['id'] ?? '') !== $id));
        $done = count($d['fb']) < $before;
        return $d;
    });
    return $done;
}

// ---------------------------------------------------------------------------------------------- přidělení procvičování

/** Nabídka přidělení pro třídu: hodnota „druh:položka“ => popisek, po skupinách. @return array<string,array<string,string>> */
function ts801_assign_options(string $classId): array
{
    $tasks = tb75_tasks_for_class($classId);
    $out = ['Úloha s variantami' => [], 'Sada úloh' => [], 'Opakování chyb' => [], 'Interaktivní cvičení' => []];
    foreach ($tasks as $t) $out['Úloha s variantami']['task:' . $t] = (string)TB75_TASKS[$t]['title'];
    foreach (ts801_sets($classId) as $area => $set) $out['Sada úloh']['set:' . $area] = $set['label'] . ' (' . count($set['tasks']) . ' úlohy)';
    if ($tasks !== []) {
        $out['Opakování chyb']['rep:auto'] = 'Balíček z nejslabších úloh každého žáka (' . TS801_REP_AUTO . ')';
        foreach ($tasks as $t) $out['Opakování chyb']['rep:' . $t] = 'Opakovat: ' . (string)TB75_TASKS[$t]['title'];
    }
    foreach (wg80_for_class($classId) as $w) $out['Interaktivní cvičení']['widget:' . $w] = (string)WG80_WIDGETS[$w]['title'];
    return array_filter($out);
}

/** Sady úloh třídy podle oblasti (Sítě, Linux, Grafika, Web a CSS). @return array<string,array{label:string,tasks:list<string>}> */
function ts801_sets(string $classId): array
{
    $labels = ['site' => 'Sítě', 'linux' => 'Linux', 'grafika' => 'Grafika', 'web' => 'Web a CSS'];
    $out = [];
    foreach (tb75_tasks_for_class($classId) as $t) {
        $area = (string)(TB75_TASKS[$t]['area'] ?? '');
        if (isset($labels[$area])) $out[$area] = ['label' => 'Sada ' . $labels[$area], 'tasks' => array_merge($out[$area]['tasks'] ?? [], [$t])];
    }
    return array_filter($out, static fn(array $s): bool => count($s['tasks']) >= 2);
}

/** Rozbor hodnoty z formuláře. @return array{k:string,i:string,tasks:list<string>}|null */
function ts801_parse_item(string $classId, string $value): ?array
{
    if (preg_match('/^(task|set|rep|widget):([a-z_]{2,20})$/', $value, $m) !== 1) return null;
    [$k, $i] = [$m[1], $m[2]];
    $tasks = tb75_tasks_for_class($classId);
    return match ($k) {
        'task' => in_array($i, $tasks, true) ? ['k' => 'task', 'i' => $i, 'tasks' => [$i]] : null,
        'set' => isset(ts801_sets($classId)[$i]) ? ['k' => 'set', 'i' => $i, 'tasks' => ts801_sets($classId)[$i]['tasks']] : null,
        'rep' => $tasks !== [] && ($i === 'auto' || in_array($i, $tasks, true)) ? ['k' => 'rep', 'i' => $i, 'tasks' => $i === 'auto' ? [] : [$i]] : null,
        'widget' => in_array($i, wg80_for_class($classId), true) ? ['k' => 'widget', 'i' => $i, 'tasks' => []] : null,
        default => null,
    };
}

/** Nejslabší úlohy žáka pro balíček opakování (úspěšnost, pak chyby třídy, pak pořadí sady). @return list<string> */
function ts801_weak_tasks(string $classId, array $tbState, array $classStats): array
{
    $rates = [];
    foreach (tb75_tasks_for_class($classId) as $t) {
        $log = (array)(($tbState[$t] ?? [])['log'] ?? []);
        if ($log === []) continue;
        $ok = count(array_filter($log, static fn($l): bool => is_array($l) && !empty($l['ok'])));
        if ($ok < count($log)) $rates[$t] = $ok / count($log);
    }
    asort($rates);
    $pick = array_keys($rates);
    if (count($pick) < TS801_REP_AUTO) {
        $byClass = array_filter($classStats, static fn(array $s): bool => (int)$s['attempts'] > 0);
        uasort($byClass, static fn(array $a, array $b): int => $a['rate'] <=> $b['rate']);
        $pick = array_merge($pick, array_keys($byClass), tb75_tasks_for_class($classId));
    }
    return array_slice(array_values(array_unique($pick)), 0, TS801_REP_AUTO);
}

/**
 * Přidělí procvičování vybraným žákům. Žák s TS801_OPEN_MAX otevřenými přiděleními se přeskočí. „rep“ zařadí úlohy do opakování v80 na dnešek
 * (jeden zápis souboru learning_v80 pro všechny žáky). @return array{assigned:int,skipped:int}
 */
function ts801_assign(string $classId, array $refs, string $value, string $by, ?int $now = null): array
{
    $now ??= time();
    $item = ts801_parse_item($classId, $value);
    if ($item === null) throw new RuntimeException('Vyberte, co chcete přidělit (úlohu, sadu, opakování nebo cvičení).');
    if ($refs === []) throw new RuntimeException('Vyberte aspoň jednoho žáka.');
    $refs = array_values(array_unique(array_values($refs)));
    $open = [];
    $lrData = lr80_data($classId);
    $tbClass = (array)((tb75_data()['classes'] ?? [])[$classId] ?? []);
    foreach (ts801_data($classId)['as'] as $a) if (!ts801_assignment_status($a, lr80_state($classId, (string)$a['ref'], $lrData), (array)($tbClass[(string)$a['ref']] ?? []))['done']) $open[(string)$a['ref']] = ($open[(string)$a['ref']] ?? 0) + 1;
    $all = count($refs);
    $refs = array_values(array_filter($refs, static fn(string $r): bool => ($open[$r] ?? 0) < TS801_OPEN_MAX));
    $skipped = $all - count($refs);
    $tasksByRef = [];
    if ($item['k'] === 'rep') {
        $stats = $item['i'] === 'auto' ? tb75_class_stats($classId, null, ['classes' => [$classId => $tbClass]]) : [];
        foreach ($refs as $ref) $tasksByRef[$ref] = $item['i'] === 'auto' ? ts801_weak_tasks($classId, (array)($tbClass[$ref] ?? []), $stats) : $item['tasks'];
        $base = ts801_rep_push($classId, $tasksByRef, $now);
    }
    $rows = [];
    foreach ($refs as $ref) {
        $b = match ($item['k']) {
            'rep' => ['c' => $base[$ref] ?? []],
            'widget' => ['ok' => (int)((lr80_state($classId, $ref, $lrData)['w'][$item['i']] ?? [])['ok'] ?? 0)],
            default => [],
        };
        $rows[] = ['id' => ts801_id('as'), 'ref' => $ref, 'k' => $item['k'], 'i' => $item['i'], 'tasks' => $tasksByRef[$ref] ?? $item['tasks'], 'base' => $b, 'at' => $now, 'by' => $by];
    }
    if ($rows !== []) ts801_update($classId, static function (array $d) use ($rows): array { $d['as'] = array_merge($d['as'], $rows); return $d; }, $now);
    return ['assigned' => count($rows), 'skipped' => $skipped];
}

/**
 * Zařadí úlohy do opakování v80 na dnešek (stupeň a počet opakování se zachovají, jen termín je nejpozději dnes). Jeden storage_update.
 * @param array<string,list<string>> $tasksByRef @return array<string,array<string,int>> ref => [úloha => počet opakování při zařazení]
 */
function ts801_rep_push(string $classId, array $tasksByRef, int $now): array
{
    $base = [];
    $today = lr80_add_days($now, 0);
    storage_update(lr80_path($classId), static function (array $d) use ($tasksByRef, $now, $today, &$base): array {
        $students = is_array($d['students'] ?? null) ? $d['students'] : [];
        foreach ($tasksByRef as $ref => $tasks) {
            $s = lr80_normalize((array)($students[$ref] ?? []));
            foreach ($tasks as $task) {
                if (!isset(TB75_TASKS[$task])) continue;
                $item = is_array($s['rep'][$task] ?? null) ? $s['rep'][$task] : ['s' => 0, 'due_at' => $today, 'at' => $now, 'c' => 0, 'f' => []];
                $item['due_at'] = min((int)($item['due_at'] ?? $today), $today);
                $s['rep'][$task] = $item;
                $base[$ref][$task] = (int)($item['c'] ?? 0);
            }
            if ((int)$s['last_at'] === 0) $s['last_at'] = $now;   // nový záznam žáka (retence v80 počítá od poslední aktivity)
            $students[$ref] = $s;
        }
        return ['v' => LR80_VERSION, 'students' => $students];
    });
    unset($GLOBALS['lr80_memo'][$classId]);
    return $base;
}

/** Zruší přidělení (smaže záznam; data žáka v úlohách a opakování zůstávají). */
function ts801_assign_cancel(string $classId, string $id): bool
{
    $done = false;
    if (preg_match(TS801_ID_RE, $id) !== 1) return false;
    ts801_update($classId, static function (array $d) use ($id, &$done): array {
        $before = count($d['as']);
        $d['as'] = array_values(array_filter($d['as'], static fn(array $r): bool => (string)($r['id'] ?? '') !== $id));
        $done = count($d['as']) < $before;
        return $d;
    });
    return $done;
}

/** Úloha má po čase $at správně vyřešené kolo? */
function ts801_task_solved_since(array $tbState, string $task, int $at): bool
{
    foreach ((array)(($tbState[$task] ?? [])['log'] ?? []) as $l) if (is_array($l) && !empty($l['ok']) && (int)($l['at'] ?? 0) >= $at) return true;
    return false;
}

/**
 * Stav přidělení (čistá funkce): done, have/total, odkaz pro žáka a první nesplněná úloha.
 * @return array{done:bool,have:int,total:int,href:string,next:string}
 */
function ts801_assignment_status(array $a, array $lrState, array $tbState): array
{
    $k = (string)($a['k'] ?? '');
    $at = (int)($a['at'] ?? 0);
    $tasks = array_values(array_filter(array_map('strval', (array)($a['tasks'] ?? [])), static fn(string $t): bool => isset(TB75_TASKS[$t])));
    if ($k === 'widget') {
        $w = (string)($a['i'] ?? '');
        $done = (int)(($lrState['w'][$w] ?? [])['ok'] ?? 0) > (int)(($a['base'] ?? [])['ok'] ?? 0);
        return ['done' => $done, 'have' => $done ? 1 : 0, 'total' => 1, 'href' => '?' . http_build_query(['view' => 'cviceni', 'w' => $w]), 'next' => $w];
    }
    $have = 0;
    $next = '';
    foreach ($tasks as $t) {
        if ($k === 'rep') {
            $item = $lrState['rep'][$t] ?? null;
            $ok = !is_array($item) || (int)($item['c'] ?? 0) > (int)((($a['base'] ?? [])['c'] ?? [])[$t] ?? 0);
        } else {
            $ok = ts801_task_solved_since($tbState, $t, $at);
        }
        if ($ok) $have++; elseif ($next === '') $next = $t;
    }
    $href = $k === 'rep' ? '?view=opakovat' : '?' . http_build_query(['view' => 'ulohy', 'uloha' => $next !== '' ? $next : (string)($tasks[0] ?? '')]);
    return ['done' => $tasks !== [] && $have === count($tasks), 'have' => $have, 'total' => count($tasks), 'href' => $href, 'next' => $next];
}

/** Pro žáka: zpětná vazba (nejnovější první) a přidělení (otevřená nejstarší první, hotová za posledních 14 dní). */
function ts801_student_view(string $classId, string $studentKey, ?int $now = null): array
{
    $now ??= time();
    $ref = tb75_student_ref($classId, $studentKey);
    $data = ts801_data($classId);
    $fb = array_values(array_filter($data['fb'], static fn(array $r): bool => (string)$r['ref'] === $ref));
    usort($fb, static fn(array $x, array $y): int => (int)$y['at'] <=> (int)$x['at']);
    $mine = array_values(array_filter($data['as'], static fn(array $r): bool => (string)$r['ref'] === $ref));
    if ($fb === [] && $mine === []) return ['feedback' => [], 'open' => [], 'done' => []];
    $lr = lr80_state($classId, $ref);
    $tb = tb75_student_state($classId, $ref);
    $open = [];
    $done = [];
    foreach ($mine as $a) {
        $st = ts801_assignment_status($a, $lr, $tb);
        if (!$st['done']) $open[] = $a + ['status' => $st];
        elseif ((int)$a['at'] >= $now - 14 * 86400) $done[] = $a + ['status' => $st];
    }
    usort($open, static fn(array $x, array $y): int => (int)$x['at'] <=> (int)$y['at']);
    return ['feedback' => array_slice($fb, 0, 10), 'open' => $open, 'done' => $done];
}

// ---------------------------------------------------------------------------------------------- fráze učitele

/** Fráze učitele: vestavěné + vlastní. @return array<string,list<array{id:string,t:string,own:bool}>> */
function ts801_phrases(string $teacherHash): array
{
    $out = [];
    foreach (TS801_PHRASES as $k => $list) foreach ($list as $i => $t) $out[$k][] = ['id' => 'b_' . $k . '_' . $i, 't' => $t, 'own' => false];
    $own = (array)((storage_read(ts801_phrases_path(), false)['teachers'] ?? [])[$teacherHash] ?? []);
    foreach ($own as $p) if (is_array($p) && isset(TS801_KINDS[(string)($p['k'] ?? '')])) $out[(string)$p['k']][] = ['id' => (string)$p['id'], 't' => (string)$p['t'], 'own' => true];
    return $out;
}

function ts801_phrase_add(string $teacherHash, string $kind, string $text): bool
{
    if (!isset(TS801_KINDS[$kind])) throw new RuntimeException('Vyberte druh fráze.');
    $text = ts801_clean_text(str_replace("\n", ' ', $text), TS801_PHRASE_MAX);
    if ($text === '') throw new RuntimeException('Napište text fráze.');
    $added = false;
    storage_update(ts801_phrases_path(), static function (array $d) use ($teacherHash, $kind, $text, &$added): array {
        $list = array_values(array_filter((array)(($d['teachers'] ?? [])[$teacherHash] ?? []), 'is_array'));
        if (count($list) >= TS801_OWN_PHRASES_MAX || in_array($text, array_column($list, 't'), true)) return $d;
        $list[] = ['id' => ts801_id('ph'), 'k' => $kind, 't' => $text];
        $d['v'] = TS801_VERSION;
        $d['teachers'][$teacherHash] = $list;
        $added = true;
        return $d;
    });
    return $added;
}

function ts801_phrase_delete(string $teacherHash, string $id): bool
{
    $done = false;
    if (preg_match(TS801_ID_RE, $id) !== 1) return false;
    storage_update(ts801_phrases_path(), static function (array $d) use ($teacherHash, $id, &$done): array {
        $list = array_values(array_filter((array)(($d['teachers'] ?? [])[$teacherHash] ?? []), static fn($p): bool => is_array($p) && (string)($p['id'] ?? '') !== $id));
        $done = count($list) < count((array)(($d['teachers'] ?? [])[$teacherHash] ?? []));
        $d['teachers'][$teacherHash] = $list;
        return $d;
    });
    return $done;
}

// ---------------------------------------------------------------------------------------------- retence po odchodu žáka

/** Smaže zpětnou vazbu a přidělení žáků ($refs) ze všech tříd; dry-run jen spočítá. @return int počet smazaných záznamů */
function ts801_retention_purge(array $refs, bool $dryRun): int
{
    if ($refs === [] || !defined('STORAGE_DIR')) return 0;
    $set = array_flip(array_map('strval', $refs));
    $n = 0;
    foreach (glob(STORAGE_DIR . '/support_v80_1_class_*.json.php') ?: [] as $path) {
        $scrub = static function (array $d) use ($set, &$n): array {
            foreach (['fb', 'as'] as $k) {
                $rows = (array)($d[$k] ?? []);
                $keep = array_values(array_filter($rows, static fn($r): bool => !is_array($r) || !isset($set[(string)($r['ref'] ?? '')])));
                $n += count($rows) - count($keep);
                $d[$k] = $keep;
            }
            return $d;
        };
        if ($dryRun) $scrub(storage_read($path, false)); else storage_update($path, $scrub);
    }
    unset($GLOBALS['ts801_memo']);
    return $n;
}
