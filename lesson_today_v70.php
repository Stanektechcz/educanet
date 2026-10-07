<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v70 · „Dnešní hodina“ v cockpitu učitele (logika bez výstupu, jen čtení).
 *
 * Skládá na jednu obrazovku to, co už v systému je, ale bylo rozházené po záložkách:
 *   rozvrh (school_year.php + výjimky kalendáře) → číslo lekce → obsah lekce (v56_lesson_bundle: téma, cíl, témata, kroky)
 *   → materiály (learning_resources z runtime cache + učitelské video v42) → výukové cesty v63 → kód hodiny v53
 *   → učitelské úkoly → postup žáků v lekci (progress_v56), signály zaostávání (v61) a zvládnutí kompetencí (v62).
 * Nic z dat žáků se nemění. Jediný zápis je odvoditelná cache zvládnutí v62 (m62_class, stejně jako záložka Kompetence);
 * trychtýř cest v63 se čte bez uložení cache (persist=false).
 * Rozsah: volající předá $modules už omezené na třídy učitele (teacher59_scope_modules); každá funkce ho drží znovu přes
 * lt70_class_allowed(). Neznámá nebo cizí třída = prázdný výsledek. Texty jsou česky (cockpit je vždy česky).
 */

require_once __DIR__ . '/lesson_model_v71.php';   // v71: obsah lekce jen přes jednotný model lm71 (konec paralelních loaderů)

const LT70_LESSONS_MAX = 28;
const LT70_RESOURCES_PER_TOPIC = 2;
const LT70_RESOURCES_MAX = 8;
const LT70_PREV_DONE_PERCENT = 75;
const LT70_TASKS_SOON_DAYS = 3;

function lt70_school_year(): array
{
    static $year = null;
    if ($year === null) {
        $loaded = require __DIR__ . '/school_year.php';
        $year = is_array($loaded) ? $loaded : [];
    }
    return $year;
}

function lt70_class_allowed(string $classId): bool
{
    if (preg_match('/^class_[a-z0-9]{1,8}$/', $classId) !== 1) return false;
    return !function_exists('teacher59_can_class') || teacher59_can_class($classId);
}

/** @return list<string> třídy v rozsahu seřazené podle začátku bloku v rozvrhu */
function lt70_classes(array $modules, array $schoolYear): array
{
    $ids = array_values(array_filter(array_map('strval', array_keys($modules)), 'lt70_class_allowed'));
    usort($ids, static fn(string $a, string $b): int => strcmp((string)adaptive_class_schedule($schoolYear, $a)['start'], (string)adaptive_class_schedule($schoolYear, $b)['start']) ?: strcmp($a, $b));
    return $ids;
}

/** Řádek kalendáře třídy pro datum (s výjimkami), nebo null. */
function lt70_calendar_row(array $schoolYear, string $classId, string $date): ?array
{
    foreach (adaptive_school_year_rows($schoolYear, $classId) as $row) {
        if (is_array($row) && (string)($row['date'] ?? '') === $date) return $row;
    }
    return null;
}

/** Nejbližší výuková hodina třídy od data (včetně), nebo null (konec roku). */
function lt70_next_teaching_row(array $schoolYear, string $classId, string $fromDate): ?array
{
    foreach (adaptive_school_year_rows($schoolYear, $classId) as $row) {
        if (!is_array($row) || (string)($row['status'] ?? '') !== 'teaching' || (int)($row['lesson_number'] ?? 0) <= 0) continue;
        if ((string)($row['date'] ?? '') >= $fromDate) return $row;
    }
    return null;
}

/**
 * Hodina, kterou cockpit ukáže: dnešní blok třídy, jinak nejbližší další. ?lesson=1–28 jen přepne náhled lekce.
 * @return array{date:string,is_today:bool,lesson:int,calendar_lesson:int,override:bool,note:string,status:string}
 */
function lt70_slot(array $schoolYear, string $classId, string $today, int $lessonParam): array
{
    $todayRow = lt70_calendar_row($schoolYear, $classId, $today);
    $teachingToday = is_array($todayRow) && (string)($todayRow['status'] ?? '') === 'teaching' && (int)($todayRow['lesson_number'] ?? 0) > 0;
    $row = $teachingToday ? $todayRow : lt70_next_teaching_row($schoolYear, $classId, $today);
    $note = '';
    if (is_array($todayRow) && !$teachingToday) {
        $note = trim((string)($todayRow['calendar_note'] ?? ($todayRow['title'] ?? '')));
        if ($note === '') $note = 'Dnes se podle kalendáře neučí.';
    }
    $calendarLesson = is_array($row) ? (int)$row['lesson_number'] : (function_exists('v56_current_lesson_number') ? v56_current_lesson_number($classId, $schoolYear) : 1);
    $calendarLesson = max(1, min(LT70_LESSONS_MAX, $calendarLesson));
    $override = $lessonParam >= 1 && $lessonParam <= LT70_LESSONS_MAX && $lessonParam !== $calendarLesson;
    return [
        'date' => is_array($row) ? (string)$row['date'] : '',
        'is_today' => $teachingToday,
        'lesson' => $override ? $lessonParam : $calendarLesson,
        'calendar_lesson' => $calendarLesson,
        'override' => $override,
        'note' => $note,
        'status' => $teachingToday ? 'today' : (is_array($row) ? 'next' : 'none'),
    ];
}

/** Třída, kterou ukázat: požadovaná (v rozsahu), jinak ta, jejíž dnešní blok právě běží nebo přijde, jinak první. */
function lt70_pick_class(array $classes, string $requested, array $schoolYear, string $today, string $time): string
{
    if ($requested !== '' && in_array($requested, $classes, true)) return $requested;
    foreach ($classes as $classId) {
        $schedule = adaptive_class_schedule($schoolYear, $classId);
        $row = lt70_calendar_row($schoolYear, $classId, $today);
        if (is_array($row) && (string)($row['status'] ?? '') === 'teaching' && (string)$schedule['end'] !== '' && $time <= (string)$schedule['end']) return $classId;
    }
    return $classes[0] ?? '';
}

/**
 * Obsah lekce pro učitele. v71: přes jednotný model lm71_lesson() – žákovská část (téma, cíl, témata, kroky, test) je
 * dál v56_lesson_bundle nad stejnými zdroji, navíc plán po minutách, poznámky, pracovní list, kritéria a úplnost.
 */
function lt70_lesson(string $classId, array $module, int $lessonNo): array
{
    $m = lm71_lesson($classId, $lessonNo, $module);
    return [
        'number' => (int)$m['number'], 'title' => (string)$m['title'], 'goal' => (string)$m['goal']['student'],
        'topics' => $m['topics'], 'steps' => $m['steps'], 'questions' => (int)$m['questions'], 'tools' => $m['tools'], 'family' => (string)$m['family'],
        'success_criteria' => $m['goal']['success_criteria'], 'timeline' => $m['timeline'], 'teacher_notes' => $m['teacher_notes'], 'worksheet' => $m['worksheet'],
        'exit_ticket' => $m['exit_ticket'], 'differentiation' => $m['differentiation'], 'completeness' => $m['completeness'], 'meta' => $m['meta'],
    ];
}

/**
 * Materiály k lekci: učitelské video k lekci (v42), pak zdroje k tématům lekce (learning_resources), jinak výchozí zdroje třídy.
 * URL se tu jen přebírá; při výpisu ji ověří safe_url().
 * @return list<array{type:string,title:string,url:string,meta:string,topic:string,teacher:bool}>
 */
function lt70_materials(string $classId, int $lessonNo, array $topics): array
{
    if (!lt70_class_allowed($classId)) return [];
    $resources = (array)(runtime_content_load_classes([$classId])['learningResources'][$classId] ?? []);
    $out = [];
    $add = static function (array $r, string $topic, bool $teacher) use (&$out): void {
        $url = trim((string)($r['url'] ?? ''));
        if ($url === '' || isset($out[$url])) return;
        $meta = implode(' · ', array_filter([(string)($r['lang'] ?? ''), (string)($r['duration'] ?? ''), (string)($r['level'] ?? ($r['meta'] ?? ''))]));
        $out[$url] = ['type' => (string)($r['type'] ?? 'link'), 'title' => (string)($r['title'] ?? 'Materiál'), 'url' => $url, 'meta' => $meta, 'topic' => $topic, 'teacher' => $teacher];
    };
    if (function_exists('v42_lesson_resource_override')) {
        $override = v42_lesson_resource_override($classId, ['number' => $lessonNo]);
        if (is_array($override)) $add($override, 'Video od učitele', true);
    }
    foreach ($topics as $topic) {
        foreach (array_slice(array_values(array_filter((array)($resources[$topic['key']] ?? []), 'is_array')), 0, LT70_RESOURCES_PER_TOPIC) as $r) $add($r, $topic['title'], false);
    }
    if (count($out) < 2) {
        foreach (array_filter((array)($resources['_default'] ?? []), 'is_array') as $r) $add($r, 'Obecně k předmětu', false);
    }
    return array_slice(array_values($out), 0, LT70_RESOURCES_MAX);
}

/**
 * Výukové cesty v63 třídy: přiřazené, související s tématy lekce (tagy kompetence topic:<téma>), počty začátků a dokončení.
 * @return array{enabled:bool,rows:list<array<string,mixed>>}
 */
function lt70_paths(string $classId, array $topics): array
{
    if (!lt70_class_allowed($classId) || !function_exists('p63_enabled_for_class') || !p63_enabled_for_class($classId)) return ['enabled' => false, 'rows' => []];
    $assigned = p63_assignments($classId);
    $stats = (array)(p63_class_stats($classId, false)['paths'] ?? []);
    $subject = comp62_subject_for_class($classId);
    $competencies = $subject !== null ? comp62_competencies($subject) : [];
    $tags = array_map(static fn(array $t): string => 'topic:' . $t['key'], $topics);
    $rows = [];
    foreach (p63_paths_for_class($classId) as $id => $path) {
        $competency = $competencies[(string)($path['competency'] ?? '')] ?? null;
        $rows[] = [
            'id' => (string)$id, 'title' => (string)($path['title'] ?? $id), 'goal' => (string)($path['goal'] ?? ''), 'minutes' => (int)($path['minutes'] ?? 0),
            'assigned' => isset($assigned[$id]), 'related' => is_array($competency) && array_intersect($tags, (array)$competency['tags']) !== [],
            'competency' => is_array($competency) ? (string)$competency['label'] : '',
            'started' => (int)($stats[$id]['started'] ?? 0), 'finished' => (int)($stats[$id]['finished'] ?? 0),
        ];
    }
    usort($rows, static fn(array $a, array $b): int => ((int)$b['related'] <=> (int)$a['related']) ?: ((int)$b['assigned'] <=> (int)$a['assigned']) ?: strnatcasecmp($a['title'], $b['title']));
    return ['enabled' => true, 'rows' => $rows];
}

/**
 * Hodina s kódem (v53) pro třídu a datum.
 * @return array{exists:bool,open:bool,code:string,title:string,kind:string,joined:int,submitted:int,graded:int,status:array<string,string>}
 */
function lt70_session(string $classId, string $date): array
{
    $empty = ['exists' => false, 'open' => false, 'code' => '', 'title' => '', 'kind' => '', 'joined' => 0, 'submitted' => 0, 'graded' => 0, 'status' => []];
    if (!lt70_class_allowed($classId) || $date === '' || !function_exists('sess53_for_class_date')) return $empty;
    $session = sess53_for_class_date($classId, $date);
    if (!is_array($session)) return $empty;
    $status = [];
    $submitted = 0;
    $graded = 0;
    foreach (sess53_submissions((string)$session['id']) as $key => $row) {
        if (!is_array($row)) continue;
        $done = (string)($row['status'] ?? '') === 'submitted';
        $submitted += $done ? 1 : 0;
        $graded += isset($row['grade']) ? 1 : 0;
        $hash = lt70_hash((string)$key);
        if ($hash !== '') $status[$hash] = $done ? 'odevzdal/a' : 'pracuje';
    }
    return ['exists' => true, 'open' => !empty($session['open']), 'code' => (string)($session['code'] ?? ''), 'title' => (string)($session['title'] ?? ''),
        'kind' => (string)($session['kind'] ?? 'work'), 'joined' => count($status), 'submitted' => $submitted, 'graded' => $graded, 'status' => $status];
}

/** Posledních 24 hex znaků klíče žáka (`třída:student:H` i `třída:s:H`). */
function lt70_hash(string $studentKey): string
{
    return preg_match('/:(?:student|s):([a-f0-9]{24})$/D', $studentKey, $m) === 1 ? $m[1] : '';
}

/**
 * Učitelské úkoly třídy (teacher_tasks): aktivní, po termínu, do 3 dnů a nejčastější zadání.
 * @return array{active:int,overdue:int,soon:int,by_hash:array<string,array{active:int,overdue:int}>,top:list<array{title:string,count:int,due:string}>}
 */
function lt70_tasks(string $classId, string $today): array
{
    $out = ['active' => 0, 'overdue' => 0, 'soon' => 0, 'by_hash' => [], 'top' => []];
    if (!lt70_class_allowed($classId) || !function_exists('teacher_task_rows')) return $out;
    $soon = date('Y-m-d', (int)strtotime($today . ' +' . LT70_TASKS_SOON_DAYS . ' days'));
    $titles = [];
    foreach (teacher_task_rows() as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId || (string)($row['status'] ?? 'assigned') !== 'assigned') continue;
        $due = substr((string)($row['due_at'] ?? ''), 0, 10);
        $late = $due !== '' && $due < $today;
        $out['active']++;
        $out['overdue'] += $late ? 1 : 0;
        $out['soon'] += (!$late && $due !== '' && $due <= $soon) ? 1 : 0;
        $hash = lt70_hash((string)($row['student_key'] ?? ''));
        if ($hash !== '') {
            $out['by_hash'][$hash] ??= ['active' => 0, 'overdue' => 0];
            $out['by_hash'][$hash]['active']++;
            $out['by_hash'][$hash]['overdue'] += $late ? 1 : 0;
        }
        $title = trim((string)($row['title'] ?? 'Úkol'));
        $titles[$title] ??= ['title' => $title, 'count' => 0, 'due' => $due];
        $titles[$title]['count']++;
        if ($due !== '' && ($titles[$title]['due'] === '' || $due < $titles[$title]['due'])) $titles[$title]['due'] = $due;
    }
    usort($titles, static fn(array $a, array $b): int => ($b['count'] <=> $a['count']) ?: strcmp($a['due'], $b['due']));
    $out['top'] = array_slice(array_values($titles), 0, 5);
    return $out;
}

/** Postup žáků v lekcích (progress_v56) podle hashe žáka: hash => [číslo lekce => řádek]. */
function lt70_progress_by_hash(string $classId): array
{
    $all = function_exists('v56_progress_path') ? load_php_json(v56_progress_path()) : [];
    $out = [];
    foreach (is_array($all) ? $all : [] as $key => $rows) {
        if (!is_array($rows) || !str_starts_with((string)$key, $classId . '|')) continue;
        $hash = lt70_hash(substr((string)$key, strlen($classId) + 1));
        if ($hash !== '') $out[$hash] = $rows;
    }
    return $out;
}

/** Splněné fáze lekce (výklad, test, projekt, odevzdání) z řádku postupu; čistá funkce. */
function lt70_phases_done(array $row, array $topicKeys, int $stepCount): int
{
    $theory = (array)($row['theory'] ?? []);
    $read = count(array_filter($topicKeys, static fn(string $t): bool => !empty($theory[$t])));
    $steps = 0;
    foreach ((array)($row['project'] ?? []) as $done) $steps += $done ? 1 : 0;
    return (int)($topicKeys !== [] && $read >= count($topicKeys)) + (int)!empty($row['test']['passed'])
        + (int)($stepCount > 0 && $steps >= $stepCount) + (int)!empty($row['submit']['done']);
}

/** Zvládnutí kompetencí v62 podle student_id (jen pilotní třídy). @return array{enabled:bool,total:int,by_id:array<string,int>} */
function lt70_mastery(string $classId): array
{
    if (!lt70_class_allowed($classId) || !function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId) || !function_exists('m62_class')) return ['enabled' => false, 'total' => 0, 'by_id' => []];
    $subject = comp62_subject_for_class($classId);
    $total = $subject !== null ? count(comp62_competencies($subject)) : 0;
    $byId = [];
    foreach ((array)(m62_class($classId)['students'] ?? []) as $id => $map) {
        $byId[(string)$id] = count(array_filter((array)$map, static fn($c): bool => is_array($c) && in_array((string)($c['state'] ?? ''), ['zvladnuto', 'upevneno'], true)));
    }
    return ['enabled' => $total > 0, 'total' => $total, 'by_id' => $byId];
}

/**
 * Žáci třídy pro dnešní hodinu: postup v lekci, minulá lekce, signály v61, zvládnutí v62, úkoly a stav v hodině.
 * „Potřebuje pomoc“ = signál zaostávání v61 (neaktivita / nízké mastery) nebo nedokončená minulá lekce (< 75 % fází).
 * @return list<array<string,mixed>> nejdřív ti, kdo potřebují pomoc, pak podle jména
 */
function lt70_students(string $classId, array $module, array $lesson, array $session, array $tasks, int $now): array
{
    if (!lt70_class_allowed($classId)) return [];
    $students = project_students_for_class($classId);
    if ($students === []) return [];
    $progress = lt70_progress_by_hash($classId);
    $topicKeys = array_column($lesson['topics'], 'key');
    $prev = (int)$lesson['number'] > 1 ? lt70_lesson($classId, $module, (int)$lesson['number'] - 1) : null;
    $signals = [];
    $active = [];
    if (function_exists('ov61_students')) foreach (ov61_students($classId, $now) as $row) {
        $signals[(string)$row['hash']] = (array)$row['reasons'];
        if ((int)($row['last'] ?? 0) > 0) $active[(string)$row['hash']] = true;
    }
    $mastery = lt70_mastery($classId);
    $rows = [];
    foreach ($students as $key => $student) {
        $hash = lt70_hash((string)$key);
        $label = (string)($student['label'] ?? '');
        $lessonRows = (array)($progress[$hash] ?? []);
        $done = lt70_phases_done((array)($lessonRows[(string)$lesson['number']] ?? []), $topicKeys, count($lesson['steps']));
        $prevPct = $prev === null ? null : (int)round(lt70_phases_done((array)($lessonRows[(string)$prev['number']] ?? []), array_column($prev['topics'], 'key'), count($prev['steps'])) / 4 * 100);
        // v71: „zatím bez aktivity“ ≠ „potřebuje pomoc“ – žák bez jakýchkoli dat (v61 bez aktivity, žádný postup v lekcích,
        // nepracuje v hodině) nedostane signál zaostávání; učitel ho vidí zvlášť jako „bez dat“.
        $idle = !isset($active[$hash]) && $lessonRows === [] && (string)($session['status'][$hash] ?? '') === '';
        $reasons = $idle ? [] : (array)($signals[$hash] ?? []);
        if (!$idle && $prevPct !== null && $prevPct < LT70_PREV_DONE_PERCENT) $reasons[] = 'Minulá lekce hotová na ' . $prevPct . ' %';
        $studentId = $mastery['enabled'] && function_exists('identity58_id_for_student') ? identity58_id_for_student($classId, $label) : null;
        $rows[] = [
            'key' => (string)$key, 'hash' => $hash, 'label' => $label, 'percent' => (int)round($done / 4 * 100), 'phases' => $done, 'prev' => $prevPct,
            'reasons' => array_values(array_unique($reasons)), 'behind' => $reasons !== [], 'idle' => $idle,
            'mastered' => $studentId !== null ? (int)($mastery['by_id'][$studentId] ?? 0) : null, 'mastery_total' => (int)$mastery['total'],
            'tasks' => (int)($tasks['by_hash'][$hash]['active'] ?? 0), 'overdue' => (int)($tasks['by_hash'][$hash]['overdue'] ?? 0),
            'session' => (string)($session['status'][$hash] ?? ''),
        ];
    }
    usort($rows, static fn(array $a, array $b): int => ((int)$b['behind'] <=> (int)$a['behind']) ?: strnatcasecmp($a['label'], $b['label']));
    return $rows;
}

/** Souhrnné ukazatele hodiny (čistá funkce nad řádky žáků, úkoly a hodinou). @return array<string,int> */
function lt70_kpis(array $students, array $tasks, array $session): array
{
    $count = count($students);
    return [
        'students' => $count,
        'done' => count(array_filter($students, static fn(array $s): bool => (int)$s['phases'] >= 4)),
        'started' => count(array_filter($students, static fn(array $s): bool => (int)$s['phases'] > 0)),
        'behind' => count(array_filter($students, static fn(array $s): bool => (bool)$s['behind'])),
        'idle' => count(array_filter($students, static fn(array $s): bool => !empty($s['idle']))),
        'avg' => $count > 0 ? (int)round(array_sum(array_column($students, 'percent')) / $count) : 0,
        'tasks' => (int)$tasks['active'], 'overdue' => (int)$tasks['overdue'],
        'joined' => (int)$session['joined'], 'submitted' => (int)$session['submitted'],
    ];
}

/** Celý model stránky „Dnešní hodina“ pro jednu třídu. */
function lt70_model(array $modules, string $requestedClass, int $lessonParam, ?int $now = null): array
{
    $now ??= time();
    $year = lt70_school_year();
    $today = date('Y-m-d', $now);
    $classes = lt70_classes($modules, $year);
    $classId = lt70_pick_class($classes, $requestedClass, $year, $today, date('H:i', $now));
    if ($classId === '') return ['class' => '', 'classes' => [], 'today' => $today];
    $module = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    $slot = lt70_slot($year, $classId, $today, $lessonParam);
    $lesson = lt70_lesson($classId, $module, $slot['lesson']);
    $session = lt70_session($classId, $slot['is_today'] ? $today : '');
    $tasks = lt70_tasks($classId, $today);
    $students = lt70_students($classId, $module, $lesson, $session, $tasks, $now);
    return [
        'class' => $classId, 'classes' => $classes, 'today' => $today, 'module' => $module, 'schedule' => adaptive_class_schedule($year, $classId),
        'slot' => $slot, 'lesson' => $lesson, 'materials' => lt70_materials($classId, $slot['lesson'], $lesson['topics']),
        'paths' => lt70_paths($classId, $lesson['topics']), 'session' => $session, 'tasks' => $tasks, 'students' => $students,
        'kpis' => lt70_kpis($students, $tasks, $session), 'schedules' => array_combine($classes, array_map(static fn(string $c): array => adaptive_class_schedule($year, $c), $classes)),
    ];
}
