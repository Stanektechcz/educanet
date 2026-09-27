<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v55 · studentská vrstva.
 *  - LVL prstenec v menu (XP do dalšího levelu),
 *  - sbírka odznaků s postupem v profilu,
 *  - časové okno vyučovacího bloku (začátek i konec),
 *  - krok za krokem průchod dnešní hodinou,
 *  - rozšiřující zadání, které se přizpůsobí zbývajícímu času, a skrytá váha hodnocení.
 */

const V55_RESERVE_MINUTES = 5;       // rezerva do konce vyučování
const V55_LOCK_VIEWS = ['hodina', 'test', 'kb_quiz'];

// ---------------------------------------------------------------------------
// Level a XP
// ---------------------------------------------------------------------------

/** Stav levelu pro prstenec v menu. */
function v55_level_state(string $classId): array
{
    $profile = learning_profile($classId);
    $xp = max(0, (int)($profile['xp'] ?? 0));
    $level = learning_level($xp);
    $current = (int)$level['current'];
    $next = max(1, (int)$level['next']);
    return [
        'xp' => $xp,
        'level' => (int)$level['level'],
        'current' => $current,
        'next' => $next,
        'remaining' => max(0, $next - $current),
        'percent' => max(0, min(100, (int)$level['percent'])),
    ];
}

// ---------------------------------------------------------------------------
// Odznaky a jejich získávání
// ---------------------------------------------------------------------------

/** Postup k odznaku: 0–100 % a slovní popis, ať žák vidí, co mu chybí. */
function v55_badge_progress(string $id, array $metrics, int $level): array
{
    $ratio = static function (float $have, float $need): array {
        $need = max(0.0001, $need);
        return [(int)round(min(1.0, $have / $need) * 100), (int)$have . ' / ' . (int)$need];
    };
    if (str_starts_with($id, 'level_')) {
        $target = max(1, (int)substr($id, 6));
        return $ratio((float)$level, (float)$target) + [2 => tr('Level {level} z {target}', ['level' => $level, 'target' => $target])];
    }
    return match ($id) {
        'knowledge_grandmaster' => $ratio((float)$metrics['kb'], (float)max(1, (int)$metrics['kb_total'])),
        'course_mastery' => $ratio((float)$metrics['lessons'], 28.0),
        'triple_distinction' => $ratio((float)$metrics['excellent_projects'], 3.0),
        'project_masterpiece' => $ratio((float)$metrics['masterpiece_projects'], 1.0),
        'challenge_distinction' => $ratio((float)$metrics['extra_distinction'], 1.0),
        'prestige_exam_certified' => $ratio((float)$metrics['special_exams'], 1.0),
        'prestige_exam_double' => $ratio((float)$metrics['special_exams'], 2.0),
        'prestige_exam_perfect' => $ratio((float)$metrics['special_exam_perfect'], 1.0),
        default => [0, ''],
    };
}

/** Kompletní sbírka odznaků: získané i ty, které se teprve sbírají. */
function v55_badge_collection(string $classId, array $module, array $simulationMap = []): array
{
    $profile = learning_profile($classId);
    $earned = is_array($profile['badges'] ?? null) ? $profile['badges'] : [];
    $defs = learning_badge_definitions();
    $metrics = learning_progress_metrics($classId, $module, $simulationMap);
    $level = (int)(learning_level((int)($profile['xp'] ?? 0))['level'] ?? 1);
    $out = [];
    foreach ($defs as $id => $def) {
        $isEarned = !empty($earned[$id]);
        $p = v55_badge_progress((string)$id, $metrics, $level);
        $percent = $isEarned ? 100 : (int)($p[0] ?? 0);
        // Vzdálené levelové milníky nezahlcují sbírku: ukážeme jen ten nejbližší nedosažený.
        $out[$id] = [
            'id' => (string)$id,
            'title' => (string)$def['title'],
            'mark' => (string)$def['mark'],
            'rarity' => (string)($def['rarity'] ?? 'epic'),
            'text' => (string)($def['text'] ?? ''),
            'condition' => (string)($def['condition'] ?? ''),
            'earned' => $isEarned,
            'earned_at' => $isEarned ? (string)($earned[$id]['earned_at'] ?? '') : '',
            'percent' => max(0, min(100, $percent)),
            'progress_label' => (string)($p[1] ?? ''),
        ];
    }
    // v58 LAB-08: odznaky za Linux dovednosti (odvozené ze stavu labu – nikdy dvakrát).
    if (!function_exists('lab58_badges') && function_exists('lab57_solved') && is_file(__DIR__ . '/lab_v58_learning.php')) require_once __DIR__ . '/lab_v58_learning.php';
    if (function_exists('lab58_badges')) {
        try {
            foreach (lab58_badges($classId, adaptive_student_key($classId)) as $lb) {
                $lid = 'lab_' . (string)($lb['id'] ?? '');
                $out[$lid] = [
                    'id' => $lid,
                    'title' => (string)($lb['title'] ?? ''),
                    'mark' => (string)($lb['icon'] ?? '›_'),
                    'rarity' => 'epic',
                    'text' => (string)($lb['hint'] ?? ''),
                    'condition' => (string)($lb['hint'] ?? ''),
                    'earned' => (bool)($lb['earned'] ?? false),
                    'earned_at' => '',
                    'percent' => max(0, min(100, (int)round(((float)($lb['progress'] ?? 0)) * 100))),
                    'progress_label' => '',
                ];
            }
        } catch (Throwable $e) {
            error_log('EDUCANET v58 odznaky labu: ' . $e->getMessage());
        }
    }
    return $out;
}

/** Sbírka rozdělená na získané, rozpracované a zamčené (levelové milníky zkrácené). */
function v55_badge_board(string $classId, array $module, array $simulationMap = []): array
{
    $all = v55_badge_collection($classId, $module, $simulationMap);
    $earned = []; $inProgress = []; $locked = []; $levelPending = [];
    foreach ($all as $id => $b) {
        if ($b['earned']) { $earned[] = $b; continue; }
        if (str_starts_with($id, 'level_')) { $levelPending[] = $b; continue; }
        if ($b['percent'] > 0) $inProgress[] = $b; else $locked[] = $b;
    }
    usort($inProgress, static fn(array $a, array $b): int => $b['percent'] <=> $a['percent']);
    usort($levelPending, static fn(array $a, array $b): int => $b['percent'] <=> $a['percent']);
    if ($levelPending) array_unshift($inProgress, $levelPending[0]); // jen nejbližší levelový milník
    return ['earned' => $earned, 'progress' => $inProgress, 'locked' => $locked, 'total' => count($all)];
}

// ---------------------------------------------------------------------------
// Časové okno vyučovacího bloku
// ---------------------------------------------------------------------------

/** Začátek, konec a zbývající minuty dvouhodinového bloku třídy. */
function v55_block_window(array $schoolYear, string $classId, string $date = '', ?int $now = null): array
{
    $schedule = adaptive_class_schedule($schoolYear, $classId);
    $date = $date !== '' ? $date : date('Y-m-d');
    $start = trim((string)($schedule['start'] ?? ''));
    $end = trim((string)($schedule['end'] ?? ''));
    $now = $now ?? time();
    $startTs = $start !== '' ? strtotime($date . ' ' . $start) : 0;
    $endTs = $end !== '' ? strtotime($date . ' ' . $end) : 0;
    $total = ($startTs && $endTs) ? max(0, (int)round(($endTs - $startTs) / 60)) : 90;
    $remaining = $endTs ? (int)floor(($endTs - $now) / 60) : 0;
    return [
        'start' => $start,
        'end' => $end,
        'range' => ($start !== '' && $end !== '') ? $start . '–' . $end : '2 × 45 minut',
        'room' => (string)($schedule['room'] ?? ''),
        'periods' => array_map('intval', (array)($schedule['periods'] ?? [])),
        'start_ts' => (int)$startTs,
        'end_ts' => (int)$endTs,
        'total_minutes' => $total,
        'remaining_minutes' => $remaining,
        'running' => $startTs && $endTs && $now >= $startTs && $now < $endTs,
        'before' => $startTs && $now < $startTs,
        'after' => $endTs && $now >= $endTs,
    ];
}

/** Použitelné minuty = zbytek hodiny minus rezerva na uklizení a odevzdání. */
function v55_usable_minutes(int $remainingMinutes): int
{
    return max(0, $remainingMinutes - V55_RESERVE_MINUTES);
}

// ---------------------------------------------------------------------------
// Rozšiřující (bonusové) zadání podle zbývajícího času
// ---------------------------------------------------------------------------

/**
 * Varianta bonusu podle času, který žákovi reálně zbývá.
 * Váhu hodnocení žák nevidí – používá ji jen učitel a přepočet bodů.
 */
function v55_bonus_variant(int $remainingMinutes): array
{
    $usable = v55_usable_minutes($remainingMinutes);
    if ($usable >= 30) {
        return ['id' => 'extended', 'label' => tr('Rozšiřující zadání'), 'minutes' => min(40, $usable), 'tasks' => 4,
            'weight' => 1.0, 'note' => tr('Máš dost času na celé rozšiřující zadání i na jeho obhájení.')];
    }
    if ($usable >= 20) {
        return ['id' => 'standard', 'label' => tr('Zkrácené rozšiřující zadání'), 'minutes' => $usable, 'tasks' => 3,
            'weight' => 0.7, 'note' => tr('Zadání je zkrácené tak, abys ho stihl/a do konce hodiny.')];
    }
    if ($usable >= 12) {
        return ['id' => 'short', 'label' => tr('Krátký test'), 'minutes' => min(15, $usable), 'tasks' => 2,
            'weight' => 0.5, 'note' => tr('Do konce hodiny zbývá málo času, dostáváš krátkou verzi.')];
    }
    if ($usable >= 5) {
        return ['id' => 'micro', 'label' => tr('Mikroúkol'), 'minutes' => $usable, 'tasks' => 1,
            'weight' => 0.3, 'note' => tr('Stihneš jednu otázku na rozmyšlenou.')];
    }
    return ['id' => 'none', 'label' => tr('Dnes už jen odevzdej'), 'minutes' => 0, 'tasks' => 0,
        'weight' => 0.0, 'note' => tr('Do konce hodiny zbývá méně než {min} minut. Bonus se dnes nezadává.', ['min' => V55_RESERVE_MINUTES + 5])];
}

/** Slovní popis váhy pro učitele (žák ho nevidí). */
function v55_weight_label(float $weight): string
{
    if ($weight >= 1.0) return 'plná váha';
    if ($weight <= 0.0) return 'nehodnotí se';
    return 'váha ' . rtrim(rtrim(number_format($weight, 2, ',', ' '), '0'), ',');
}

/** Body za bonus = známka × váha, zaokrouhleno nahoru na celé body. */
function v55_bonus_points(int $grade, float $weight): int
{
    $base = pts53_points_for_grade($grade);
    if ($base <= 0 || $weight <= 0) return 0;
    return max(1, (int)round($base * $weight));
}

/** Zadání bonusu odvozené z lekce – počet úkolů odpovídá variantě. */
function v55_bonus_tasks(string $classId, array $module, array $lesson, array $variant): array
{
    $family = tut52_family($classId, $module);
    $topics = array_values(array_filter(array_map('strval', (array)($lesson['topics'] ?? []))));
    $topicTitle = static function (string $t) use ($module): string {
        return (string)($module['knowledgebase'][$t]['title'] ?? $t);
    };
    $pool = [];
    if ($family === 'graphics') {
        $pool[] = ['title' => tr('Varianta navíc'), 'detail' => tr('Vytvoř druhou variantu dnešního výstupu s jiným kontrastem nebo jinou hierarchií a napiš, která funguje líp a proč.'), 'minutes' => 12];
        $pool[] = ['title' => tr('Mobilní verze'), 'detail' => tr('Uprav výstup na šířku 375 px tak, aby zůstala čitelná hierarchie i hlavní CTA.'), 'minutes' => 10];
        $pool[] = ['title' => tr('Kontrola přístupnosti'), 'detail' => tr('Ověř kontrast textu (min. 4,5:1) a velikost klikacích prvků; zapiš, co jsi musel/a změnit.'), 'minutes' => 8];
        $pool[] = ['title' => tr('Krátká obhajoba'), 'detail' => tr('Ve 3 větách vysvětli hlavní designové rozhodnutí tak, aby mu rozuměl i zadavatel bez znalosti grafiky.'), 'minutes' => 6];
    } else {
        $pool[] = ['title' => tr('Druhý scénář'), 'detail' => tr('Zopakuj dnešní postup na jiném zadání (jiná síť, jiná služba) a zapiš rozdíly v konfiguraci.'), 'minutes' => 12];
        $pool[] = ['title' => tr('Diagnostika chyby'), 'detail' => tr('Rozbij si vlastní konfiguraci jednou změnou, popiš příznak, najdi ho a oprav. Zapiš postup krok za krokem.'), 'minutes' => 10];
        $pool[] = ['title' => tr('Důkaz funkčnosti'), 'detail' => tr('Dolož výstup příkazu nebo screenshot, který prokazuje, že řešení opravdu funguje.'), 'minutes' => 8];
        $pool[] = ['title' => tr('Krátká obhajoba'), 'detail' => tr('Ve 3 větách vysvětli, proč jsi zvolil/a právě tohle řešení a co by se stalo při jiné volbě.'), 'minutes' => 6];
    }
    if ($topics) {
        $pool[0]['detail'] .= ' ' . tr('Téma: {tema}.', ['tema' => $topicTitle($topics[0])]);
        if (isset($topics[1])) $pool[1]['detail'] .= ' ' . tr('Téma: {tema}.', ['tema' => $topicTitle($topics[1])]);
    }
    $count = max(0, (int)$variant['tasks']);
    // Krátké varianty berou nejdřív ty úkoly, které dávají smysl samostatně.
    if ($count <= 1) return array_slice([$pool[3], $pool[2]], 0, $count);
    if ($count === 2) return [$pool[2], $pool[3]];
    return array_slice($pool, 0, $count);
}

/** Kompletní bonusové zadání pro žáka (bez váhy – ta se do šablony neposílá). */
function v55_bonus_assignment(string $classId, array $module, array $lesson, array $window): array
{
    $variant = v55_bonus_variant((int)$window['remaining_minutes']);
    return [
        'variant' => $variant['id'],
        'label' => $variant['label'],
        'minutes' => (int)$variant['minutes'],
        'note' => $variant['note'],
        'weight' => (float)$variant['weight'],
        'tasks' => v55_bonus_tasks($classId, $module, $lesson, $variant),
        'deadline_ts' => (int)$window['end_ts'] - V55_RESERVE_MINUTES * 60,
    ];
}

// ---------------------------------------------------------------------------
// Uložení bonusu
// ---------------------------------------------------------------------------

function v55_bonus_path(): string
{
    return STORAGE_DIR . '/bonus_v55.json.php';
}

function v55_bonus_all(): array
{
    $rows = load_php_json(v55_bonus_path());
    return is_array($rows) ? $rows : [];
}

function v55_bonus_get(string $sessionId, string $studentKey): array
{
    $all = v55_bonus_all();
    $row = $all[$sessionId][$studentKey] ?? null;
    return is_array($row) ? $row : [];
}

/** Start bonusu: zafixuje variantu i váhu podle času, kdy žák skutečně začal. */
function v55_bonus_start(string $sessionId, string $studentKey, array $assignment): array
{
    $all = v55_bonus_all();
    $existing = $all[$sessionId][$studentKey] ?? null;
    if (is_array($existing) && !empty($existing['variant'])) return $existing;
    $row = [
        'session_id' => $sessionId,
        'student_key' => $studentKey,
        'variant' => (string)$assignment['variant'],
        'label' => (string)$assignment['label'],
        'minutes' => (int)$assignment['minutes'],
        'weight' => (float)$assignment['weight'],
        'tasks' => $assignment['tasks'],
        'started_at' => date(DATE_ATOM),
        'deadline_ts' => (int)$assignment['deadline_ts'],
        'status' => 'open',
        'answers' => [],
    ];
    // Pod zámkem: kdo začal dřív, toho varianta platí; jiní žáci se nepřepíšou.
    storage_update(v55_bonus_path(), static function (array $all) use ($sessionId, $studentKey, &$row): array {
        $existing = $all[$sessionId][$studentKey] ?? null;
        if (is_array($existing) && !empty($existing['variant'])) { $row = $existing; return $all; }
        if (!isset($all[$sessionId]) || !is_array($all[$sessionId])) $all[$sessionId] = [];
        $all[$sessionId][$studentKey] = $row;
        return $all;
    });
    return $row;
}

function v55_bonus_submit(string $sessionId, string $studentKey, array $answers): array
{
    $row = [];
    storage_update(v55_bonus_path(), static function (array $all) use ($sessionId, $studentKey, $answers, &$row): array {
        $row = is_array($all[$sessionId][$studentKey] ?? null) ? $all[$sessionId][$studentKey] : [];
        if (!$row) throw new RuntimeException('Bonusové zadání nebylo spuštěno.');
        $row['answers'] = array_map(static fn($a): string => intake_v51_text((string)$a, 3000), array_values($answers));
        $row['status'] = 'submitted';
        $row['submitted_at'] = date(DATE_ATOM);
        $row['late'] = !empty($row['deadline_ts']) && time() > (int)$row['deadline_ts'];
        $all[$sessionId][$studentKey] = $row;
        return $all;
    });
    return $row;
}

/** Učitelovo hodnocení bonusu: body se přepočtou známkou × skrytou váhou. */
function v55_bonus_grade(string $sessionId, string $studentKey, int $grade, string $comment, string $classId): array
{
    $grade = max(1, min(5, $grade));
    $row = [];
    storage_update(v55_bonus_path(), static function (array $all) use ($sessionId, $studentKey, $grade, $comment, &$row): array {
        $row = is_array($all[$sessionId][$studentKey] ?? null) ? $all[$sessionId][$studentKey] : [];
        if (!$row) throw new RuntimeException('Bonusové zadání nebylo nalezeno.');
        $weight = (float)($row['weight'] ?? 1.0);
        $row['grade'] = $grade;
        $row['weight_label'] = v55_weight_label($weight);
        $row['points'] = v55_bonus_points($grade, $weight);
        $row['teacher_comment'] = intake_v51_text($comment, 1200);
        $row['reviewed_at'] = date(DATE_ATOM);
        $all[$sessionId][$studentKey] = $row;
        return $all;
    });
    if ((int)$row['points'] > 0) {
        pts53_award($classId, $studentKey, 'bonus:' . $sessionId, (int)$row['points'], 'Rozšiřující zadání');
    }
    return $row;
}

// ---------------------------------------------------------------------------
// Krok za krokem dnešní hodinou
// ---------------------------------------------------------------------------

/** Úkoly pro úvodní dvouhodinový blok (lekce 1 nemá kroky z kurikula). */
function v55_primary_tasks(string $classId, array $module): array
{
    $family = tut52_family($classId, $module);
    $note = trim((string)($module['lesson_note'] ?? ''));
    $tasks = [
        ['title' => tr('Vstupní diagnostika'), 'detail' => tr('Krátký test bez známky. Ukáže, co už umíš, a nastaví ti tempo.'), 'time' => '10 min'],
        ['title' => tr('Projdi tutoriál lekce'), 'detail' => tr('Animovaná ukázka, vysvětlení a interaktivní úkoly za body.'), 'time' => '20 min'],
    ];
    if ($family === 'graphics') {
        $tasks[] = ['title' => tr('Hlavní praktický blok'), 'detail' => tr('Vytvoř dnešní grafický výstup podle zadání a ulož ho.'), 'time' => '35 min'];
        $tasks[] = ['title' => tr('Odevzdej výstup'), 'detail' => tr('Vlož odkaz na Canvu/Figmu a krátce popiš svá rozhodnutí.'), 'time' => '10 min'];
    } else {
        $tasks[] = ['title' => tr('Hlavní praktický blok'), 'detail' => tr('Projdi praktickou laboratoř dnešní lekce a dokonči všechny kroky.'), 'time' => '35 min'];
        $tasks[] = ['title' => tr('Odevzdej důkaz'), 'detail' => tr('Vlož výstup příkazu nebo screenshot a krátce popiš postup.'), 'time' => '10 min'];
    }
    if ($note !== '') $tasks[2]['detail'] = $note;
    return $tasks;
}

/** Plán hodiny: jeden krok za druhým, vždy právě jeden aktivní. */
function v55_lesson_plan(string $classId, array $module, array $session, array $submission, ?array $completedTest): array
{
    $lessonNo = max(1, (int)($session['lesson_number'] ?? 1));
    $tasks = (array)($session['tasks'] ?? []);
    $checks = array_map('strval', (array)($submission['checks'] ?? []));
    $steps = [];

    $testDone = is_array($completedTest);
    $steps[] = [
        'id' => 'test',
        'title' => tr('Vstupní diagnostika'),
        'lead' => tr('Krátký test bez známky. Ukáže ti, co už umíš, a podle toho se přizpůsobí tempo.'),
        'done' => $testDone,
        'action' => $testDone ? null : ['kind' => 'post', 'action' => 'start_test', 'label' => tr('Spustit test')],
        'skip_label' => tr('Test už mám hotový'),
    ];
    $steps[] = [
        'id' => 'tutorial',
        'title' => tr('Tutoriál lekce {n}', ['n' => $lessonNo]),
        'lead' => tr('Animovaná ukázka situace, vysvětlení a interaktivní úkoly za body.'),
        'done' => !empty($submission['tutorial_done']),
        'action' => ['kind' => 'link', 'href' => tut52_tutorial_url($lessonNo), 'label' => tr('Otevřít tutoriál')],
        'confirm' => 'v55_tutorial_done',
    ];
    $steps[] = [
        'id' => 'work',
        'title' => tr('Samostatná práce'),
        'lead' => tr('Odškrtávej si body, jak postupuješ. Postup se ti průběžně ukládá.'),
        'done' => $tasks !== [] && count(array_filter($checks, static fn($c) => $c !== '')) >= count($tasks),
        'action' => null,
        'tasks' => $tasks,
    ];
    $steps[] = [
        'id' => 'submit',
        'title' => tr('Odevzdání'),
        'lead' => tr('Krátký popis a odkaz na práci. Učitel ti dá známku a body.'),
        'done' => (string)($submission['status'] ?? '') === 'submitted',
        'action' => null,
    ];
    $current = null;
    foreach ($steps as $i => $s) {
        if ($current === null && empty($s['done'])) $current = $i;
    }
    $doneCount = count(array_filter($steps, static fn(array $s): bool => !empty($s['done'])));
    return [
        'steps' => $steps,
        'current' => $current,          // null = vše hotovo
        'done' => $doneCount,
        'total' => count($steps),
        'complete' => $current === null,
    ];
}

/** Označí dokončení kroku, který nemá vlastní server-side důkaz (tutoriál). */
function v55_mark_step(string $sessionId, string $studentKey, string $label, string $step): void
{
    $sub = sess53_submission($sessionId, $studentKey);
    $data = [
        'checks' => (array)($sub['checks'] ?? []),
        'note' => (string)($sub['note'] ?? ''),
        'link' => (string)($sub['link'] ?? ''),
        'status' => (string)($sub['status'] ?? 'open'),
    ];
    $row = sess53_submit($sessionId, $studentKey, $label, $data);
    storage_update(sess53_path('lesson_session_work'), static function (array $all) use ($sessionId, $studentKey, $step, $row): array {
        if (!isset($all[$sessionId]) || !is_array($all[$sessionId])) $all[$sessionId] = [];
        $current = is_array($all[$sessionId][$studentKey] ?? null) ? $all[$sessionId][$studentKey] : $row;
        $all[$sessionId][$studentKey] = array_merge($current, [$step . '_done' => true, $step . '_done_at' => date(DATE_ATOM)]);
        return $all;
    });
}

// ---------------------------------------------------------------------------
// Menu, zámek a CTA
// ---------------------------------------------------------------------------

/** Menu má právě pět položek: dnešní hodina, materiály, kalendář, výsledky, profil. */
/**
 * v58: živá aktivita třídy pro navigaci – závod Arény, týmová hra, otevřený zápas Robotí ligy.
 * Moduly her se načtou jen když existují (levné jádro bez dalších závislostí); chyba modulu navigaci nerozbije.
 * @return array{href:string,label:string}|null
 */
function v58_nav_live_activity(string $classId, bool $labOn): ?array
{
    if ($labOn && function_exists('arena57_live_for_class')) {
        $race = arena57_live_for_class($classId);
        if (is_array($race)) return ['href' => '?view=lab&zavod=' . rawurlencode((string)$race['id']), 'label' => trm('Závod!')];
    }
    // Týmová hra: jádro týmových her je samostatné (bez dalších závislostí), načte se jen když existuje.
    if (!function_exists('tg58_live_for_class') && is_file(__DIR__ . '/teamgames_v58_core.php')) require_once __DIR__ . '/teamgames_v58_core.php';
    if (function_exists('tg58_live_for_class')) {
        try { $game = tg58_live_for_class($classId); } catch (Throwable $e) { error_log('EDUCANET v58 navigace: ' . $e->getMessage()); $game = null; }
        if (is_array($game)) return ['href' => '?view=hry&hra=' . rawurlencode((string)($game['id'] ?? '')), 'label' => trm('Hra!')];
    }
    return null;
}

function v55_primary_nav(?string $classId, string $view): array
{
    $hasSession = false;
    if (is_string($classId) && $classId !== '') {
        $row = sess53_for_class_date($classId, date('Y-m-d'));
        $hasSession = is_array($row) && !empty($row['open']);
    }
    $items = [
        ['href' => '?view=hodina', 'label' => trm('Dnešní hodina'), 'mark' => '●', 'views' => ['hodina', 'intake']],
        ['href' => '?view=materialy', 'label' => trm('Materiály'), 'mark' => '◫', 'views' => ['materialy', 'lekce', 'course', 'topics', 'tools', 'knowledgebase', 'kb_lesson', 'tutorial']],
        ['href' => '?view=calendar', 'label' => trm('Kalendář'), 'mark' => '◷', 'views' => ['calendar']],
        ['href' => '?view=vysledky', 'label' => trm('Výsledky'), 'mark' => '✓', 'views' => ['vysledky', 'project_results', 'project_result', 'skills']],
        ['href' => '?view=profile', 'label' => trm('Profil'), 'mark' => '◆', 'views' => ['profile', 'community']],
    ];
    // v57: Linux Lab (terminál, mise, třídní závody). Když běží závod, týmová hra nebo zápas robotů, položka pulzuje (v58).
    $labOn = !function_exists('arena57_lab_enabled') || !is_string($classId) || $classId === '' || arena57_lab_enabled($classId);
    $live = is_string($classId) && $classId !== '' ? v58_nav_live_activity($classId, $labOn) : null;
    if ($labOn || $live !== null) {
        $items[] = ['href' => $live['href'] ?? '?view=lab', 'label' => $live['label'] ?? trm('Linux Lab'), 'mark' => '›_', 'views' => ['lab', 'prikazy', 'roboti', 'hry', 'hadanka', 'ctf', 'incident'], 'live' => $live !== null];
    }
    foreach ($items as $i => $item) {
        $active = in_array($view, (array)$item['views'], true);
        $class = $active ? 'is-active' : (!empty($item['live']) ? 'is-live' : '');
        if ((string)$item['href'] === '?view=hodina') {
            if (!$hasSession) { $class = 'is-muted'; }
            elseif (!$active) { $class = 'is-today'; }
        }
        $items[$i]['class'] = $class;
        $items[$i]['active'] = $active; // v58 A11Y-A6: pro aria-current="page" v _layout.php
    }
    return $items;
}

/** Během testu a rozpracované samostatné práce je odchod ze stránky zamčený. */
function v55_lock_active(string $view): bool
{
    if (in_array($view, ['test', 'kb_quiz'], true)) return true;
    // v57: během třídního závodu s uzamčenou navigací nejde z Labu odejít jinam.
    if ($view === 'lab' && isset($_GET['zavod']) && is_string($_GET['zavod']) && function_exists('arena57_lock_active')) return arena57_lock_active($_GET['zavod']);
    if ($view !== 'hodina') return false;
    $classId = current_class_id($GLOBALS['modules'] ?? []);
    if (!is_string($classId) || $classId === '') return false;
    $session = sess53_for_class_date($classId, date('Y-m-d'));
    if (!is_array($session) || empty($session['open'])) return false;
    if ((string)($session['kind'] ?? '') !== 'work') return false;
    $studentKey = adaptive_student_key($classId);
    if ($studentKey === '') return false;
    $sub = sess53_submission((string)$session['id'], $studentKey);
    $bonus = v55_bonus_get((string)$session['id'], $studentKey);
    $bonusOpen = $bonus !== [] && (string)($bonus['status'] ?? '') === 'open';
    return (string)($sub['status'] ?? '') !== 'submitted' || $bonusOpen;
}

/** Po přihlášení jde žák rovnou do dnešní hodiny, pokud je otevřená. */
function v55_after_login_url(array $modules): string
{
    $classId = current_class_id($modules);
    if (!is_string($classId) || $classId === '') return '?view=dashboard';
    $session = sess53_for_class_date($classId, date('Y-m-d'));
    if (!is_array($session) || empty($session['open'])) return '?view=dashboard';
    return (string)($session['kind'] ?? '') === 'intake' ? '?view=intake' : '?view=hodina';
}
