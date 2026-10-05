<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · adaptéry zdrojů důkazů (jen čtení, zdrojová data se nemění).
 *
 * Každý adaptér vrací kandidáty {source, ref, tags, score, at}; ev62_sync_student() je přes comp62_match()
 * převede na důkazy. Adaptéry jen čtou uložená data (stav labu, průběh lekcí, proudy výsledků, výzvy, týmové
 * hry, známky projektů) – nikdy nestaví svět labu ani nespouštějí relaci (invariant Linux Labu), nevolají
 * žádnou síť ani příkazy. Skóre labu klesá s nápovědami, ať zvládnutí neplyne z pouhého „proklikání“.
 * Typy zdrojů: test, project, lab, lesson, game, arena. Incidenty v58 patří mezi zdroje typu lab (rozhodnutí školy).
 */

const EV62_SYNC_TTL = 600;

require_once __DIR__ . '/teamgames_v64_roles.php'; // v64: přínos člena týmu pro skóre týmové hry

/** Paměť jednoho požadavku (proudy se čtou jednou na třídu, ne jednou na žáka). */
function ev62_memo(string $key, callable $compute)
{
    $store = &$GLOBALS['ev62_memo'];
    if (!is_array($store)) $store = [];
    if (!array_key_exists($key, $store)) $store[$key] = $compute();
    return $store[$key];
}

function ev62_memo_reset(): void
{
    $GLOBALS['ev62_memo'] = [];
}

function ev62_require_lab(): void
{
    if (!function_exists('lab57_level')) require_once __DIR__ . '/linux_v57_lab.php';
}

/** Tagy úrovně labu (pack:/cmd:) z deklarace úrovně – čte jen metadata, svět nestaví. @return list<string> */
function ev62_level_tags(string $levelId): array
{
    return ev62_memo('level|' . $levelId, static function () use ($levelId): array {
        ev62_require_lab();
        $level = lab57_level($levelId);
        if (!is_array($level)) return [];
        $tags = ['pack:' . (string)($level['pack'] ?? '')];
        foreach ((array)($level['commands'] ?? []) as $cmd) $tags[] = 'cmd:' . (string)$cmd;
        return $tags;
    });
}

function ev62_safe_ref_part(string $value): string
{
    return substr((string)preg_replace('/[^A-Za-z0-9_.\-]/', '_', $value), 0, 60);
}

/** Skóre vyřešené úlohy: nápovědy ubírají 0,1 za kus, nejméně 0,5. */
function ev62_lab_score(array $info): float
{
    return max(0.5, 1.0 - 0.1 * max(0, (int)($info['hints'] ?? 0)));
}

function ev62_lab_state(array $ctx): array
{
    return ev62_memo('labstate|' . $ctx['class'] . '|' . $ctx['key'], static function () use ($ctx): array {
        $path = STORAGE_DIR . '/linux_v57/' . preg_replace('/[^a-z0-9_]/i', '', (string)$ctx['class']) . '__' . sha1((string)$ctx['key']) . '.json.php';
        $solved = storage_read($path, false)['solved'] ?? [];
        return is_array($solved) ? $solved : [];
    });
}

/**
 * Kandidáti z vyřešených úrovní labu podle prefixu kontextu (practice, race, weekly, ctf, incident, review, tg).
 * @param array<string,string> $prefixSource prefix → typ zdroje
 */
function ev62_solved_candidates(array $ctx, array $prefixSource): array
{
    $out = [];
    foreach (ev62_lab_state($ctx) as $context => $levels) {
        $prefix = explode(':', (string)$context, 2)[0];
        if (!isset($prefixSource[$prefix]) || !is_array($levels)) continue;
        foreach ($levels as $levelId => $info) {
            if (!is_array($info)) continue;
            $out[] = ['source' => $prefixSource[$prefix], 'ref' => 'lab:' . ev62_safe_ref_part((string)$context) . ':' . ev62_safe_ref_part((string)$levelId),
                'tags' => ev62_level_tags((string)$levelId), 'score' => ev62_lab_score($info), 'at' => (string)($info['at'] ?? '')];
        }
    }
    return $out;
}

function ev62_collect_lab_practice(array $ctx): array
{
    return ev62_solved_candidates($ctx, ['practice' => 'lab', 'review' => 'lab']);
}

function ev62_collect_arena_races(array $ctx): array
{
    return ev62_solved_candidates($ctx, ['race' => 'arena']);
}

/** Týdenní hádanka a CTF = aréna; incidenty v58 = lab (rozhodnutí školy). */
function ev62_collect_arena_v58(array $ctx): array
{
    return ev62_solved_candidates($ctx, ['weekly' => 'arena', 'ctf' => 'arena', 'incident' => 'lab']);
}

/** Id otázky → téma znalostní báze (kb) z modulu třídy. @return array<string,string> */
function ev62_question_topics(string $classId): array
{
    $modules = is_array($GLOBALS['modules'] ?? null) ? $GLOBALS['modules'] : [];
    $map = [];
    foreach ((array)($modules[$classId]['questions'] ?? []) as $q) {
        if (is_array($q) && is_string($q['id'] ?? null) && is_string($q['kb'] ?? null) && $q['kb'] !== '') $map[$q['id']] = $q['kb'];
    }
    return $map;
}

/** Lekce v56: přečtená teorie (nízké skóre) a výsledek testu lekce po tématech. */
function ev62_collect_v56_lessons(array $ctx): array
{
    $all = storage_read(STORAGE_DIR . '/progress_v56.json.php', false);
    $lessons = is_array($all[$ctx['class'] . '|' . $ctx['key']] ?? null) ? $all[$ctx['class'] . '|' . $ctx['key']] : [];
    $topics = ev62_question_topics((string)$ctx['class']);
    $out = [];
    foreach ($lessons as $no => $row) {
        if (!is_array($row)) continue;
        foreach ((array)($row['theory'] ?? []) as $topic => $at) {
            $out[] = ['source' => 'lesson', 'ref' => 'v56:l' . (int)$no . ':th:' . ev62_safe_ref_part((string)$topic), 'tags' => ['topic:' . $topic], 'score' => 0.5, 'at' => (string)$at];
        }
        $out = array_merge($out, ev62_lesson_test_candidates((int)$no, (array)($row['test'] ?? []), $topics, array_keys((array)($row['theory'] ?? []))));
    }
    return $out;
}

/** @param list<string|int> $theoryTopics */
function ev62_lesson_test_candidates(int $no, array $test, array $topics, array $theoryTopics): array
{
    $groups = [];
    foreach ((array)($test['detail'] ?? []) as $d) {
        if (!is_array($d)) continue;
        $topic = $topics[(string)($d['id'] ?? '')] ?? (string)($theoryTopics[0] ?? '');
        if ($topic === '') continue;
        $groups[$topic][] = !empty($d['ok']) ? 1.0 : 0.0;
    }
    $out = [];
    foreach ($groups as $topic => $marks) {
        $out[] = ['source' => 'lesson', 'ref' => 'v56:l' . $no . ':t:' . ev62_safe_ref_part((string)$topic), 'tags' => ['topic:' . $topic],
            'score' => array_sum($marks) / count($marks), 'at' => (string)($test['at'] ?? '')];
    }
    return $out;
}

/** Řádky proudu pro třídu (jednou za požadavek). */
function ev62_class_stream_rows(string $classId, string $stream): array
{
    return ev62_memo('stream|' . $stream . '|' . $classId, static fn(): array => storage_stream_rows($stream, static fn(array $r): bool => (string)($r['class_id'] ?? '') === $classId));
}

function ev62_row_is_student(array $row, array $ctx): bool
{
    $label = normalized_person_name((string)($row['student_label'] ?? ''));
    $mail = strtolower(trim((string)($row['student_email'] ?? '')));
    $myMail = strtolower(trim((string)($ctx['email'] ?? '')));
    return ($label !== '' && $label === normalized_person_name((string)$ctx['label'])) || ($mail !== '' && $mail === $myMail);
}

/** Startovní testy (po tématech) a praktické laboratoře z modulu třídy. */
function ev62_collect_practice_results(array $ctx): array
{
    $topics = ev62_question_topics((string)$ctx['class']);
    $out = [];
    foreach (ev62_class_stream_rows((string)$ctx['class'], 'practice_results') as $row) {
        if (!ev62_row_is_student($row, $ctx)) continue;
        $groups = [];
        foreach ((array)($row['answers'] ?? []) as $a) {
            $topic = is_array($a) ? ($topics[(string)($a['question_id'] ?? '')] ?? '') : '';
            if ($topic !== '') $groups[$topic][] = !empty($a['correct']) ? 1.0 : 0.0;
        }
        foreach ($groups as $topic => $marks) {
            $out[] = ['source' => 'test', 'ref' => 'test:' . ev62_safe_ref_part((string)($row['id'] ?? '')) . ':' . ev62_safe_ref_part((string)$topic), 'tags' => ['topic:' . $topic],
                'score' => array_sum($marks) / count($marks), 'at' => (string)($row['finished_at'] ?? '')];
        }
    }
    foreach (ev62_class_stream_rows((string)$ctx['class'], 'lab_results') as $row) {
        if (ev62_row_is_student($row, $ctx)) $out = array_merge($out, ev62_practice_lab_candidates($row));
    }
    return $out;
}

function ev62_practice_lab_candidates(array $row): array
{
    $groups = [];
    foreach ((array)($row['answers'] ?? []) as $a) {
        $topic = is_array($a) ? (string)($a['kb'] ?? '') : '';
        if ($topic === '') continue;
        $groups[$topic][] = max(0.5, 1.0 - 0.15 * max(0, (int)($a['attempts'] ?? 1) - 1) - 0.1 * max(0, (int)($a['hints_used'] ?? 0)));
    }
    $out = [];
    foreach ($groups as $topic => $marks) {
        $out[] = ['source' => 'lab', 'ref' => 'plab:' . ev62_safe_ref_part((string)($row['id'] ?? '')) . ':' . ev62_safe_ref_part((string)$topic), 'tags' => ['topic:' . $topic],
            'score' => array_sum($marks) / count($marks), 'at' => (string)($row['finished_at'] ?? '')];
    }
    return $out;
}

/** Souboje v Aréně (v60): vítěz 1,0, druhý účastník 0,6; tagy podle úrovně labu. */
function ev62_collect_arena_v60(array $ctx): array
{
    $rows = storage_read(STORAGE_DIR . '/arena_v60_challenges.json.php', false)['challenges'] ?? [];
    $out = [];
    foreach ((array)$rows as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $ctx['class'] || (string)($row['status'] ?? '') !== 'done') continue;
        if ((string)($row['from_key'] ?? '') !== $ctx['key'] && (string)($row['to_key'] ?? '') !== $ctx['key']) continue;
        $out[] = ['source' => 'arena', 'ref' => 'arena60:' . ev62_safe_ref_part((string)($row['id'] ?? '')), 'tags' => ev62_level_tags((string)($row['level_id'] ?? '')),
            'score' => (string)($row['winner_key'] ?? '') === $ctx['key'] ? 1.0 : ev62_arena60_loser_score($row, $ctx), 'at' => (string)($row['finished_at'] ?? '')];
    }
    return $out;
}

/** Počet příkazů žáka v dané úrovni mezi dvěma časy (log labu, jen čtení). */
function ev62_level_cmds(string $class, string $key, string $levelId, int $from, int $to): int
{
    $path = STORAGE_DIR . '/linux_v57/' . preg_replace('/[^a-z0-9_]/i', '', $class) . '__' . sha1($key) . '.json.php';
    $n = 0;
    foreach ((array)(storage_read($path, false)['log'] ?? []) as $e) {
        $t = is_array($e) ? strtotime((string)($e['t'] ?? '')) : false;
        if (is_array($e) && (string)($e['lvl'] ?? '') === $levelId && $t !== false && $t >= $from && $t <= $to) $n++;
    }
    return $n;
}

/** Čistá funkce: poražený dostane 0,4 + 0,2 × (jeho kroky / kroky vítěze, max 1) → 0,4–0,6 (dřív pevně 0,6). */
function ev62_arena60_loser_score_from(int $loserCmds, int $winnerCmds): float
{
    return round(0.4 + 0.2 * min(1.0, $loserCmds / max(1, $winnerCmds)), 3);
}

/** Skóre poraženého v souboji podle podílu kroků proti vítězi v okně souboje. */
function ev62_arena60_loser_score(array $row, array $ctx): float
{
    $from = (int)strtotime((string)($row['accepted_at'] ?? ''));
    $to = (int)strtotime((string)($row['finished_at'] ?? '')) + 1;
    $level = (string)($row['level_id'] ?? '');
    $winner = (string)($row['winner_key'] ?? '');
    if ($from <= 0 || $to <= 1 || $level === '' || $winner === '') return 0.4;
    return ev62_arena60_loser_score_from(ev62_level_cmds((string)$ctx['class'], (string)$ctx['key'], $level, $from, $to), ev62_level_cmds((string)$ctx['class'], $winner, $level, $from, $to));
}

/** Robotí liga v58: umístění v odehraném zápase → skóre 0,8 (1.) až 0,5 (poslední); tag robots:algo. Hra sama nikdy nedá „upevněno“ (váha hry, m62). */
function ev62_robots_score(int $place, int $participants): float
{
    return round(0.8 - 0.3 * (max(1, $place) - 1) / max(1, $participants - 1), 3);
}

function ev62_collect_robots_v58(array $ctx): array
{
    $out = [];
    foreach ((array)(storage_read(STORAGE_DIR . '/robots_v58.json.php', false)['matches'] ?? []) as $m) {
        if (!is_array($m) || (string)($m['class_id'] ?? '') !== $ctx['class'] || empty($m['results']) || (string)($m['ran_at'] ?? '') === '' || preg_match('/^[a-f0-9]{6,32}$/', (string)($m['id'] ?? '')) !== 1) continue;
        $robots = (array)($m['results']['robots'] ?? []);
        $mine = null;
        foreach ($robots as $r) if (is_array($r) && (string)($r['key'] ?? '') === $ctx['key']) { $mine = $r; break; }
        if ($mine === null) continue;
        $rank = (int)($mine['rank'] ?? 0);
        $n = count($robots);
        if ((string)($m['mode'] ?? '') === 'teams') {
            foreach ((array)($m['results']['teams'] ?? []) as $t) if (is_array($t) && (string)($t['team'] ?? '') === (string)($mine['team'] ?? '')) { $rank = (int)$t['rank']; $n = count((array)$m['results']['teams']); }
        }
        if ($rank < 1) continue;
        $out[] = ['source' => 'game', 'ref' => 'robots:' . $m['id'], 'tags' => ['robots:algo'], 'score' => ev62_robots_score($rank, $n), 'at' => (string)$m['ran_at']];
    }
    return $out;
}

/** Týmové hry v58: vyřešené úrovně v kontextu tg a účast v dohrané hře (skóre 0,6 – výsledek jednotlivce se neměří). */
function ev62_collect_teamgames_v58(array $ctx): array
{
    $out = ev62_solved_candidates($ctx, ['tg' => 'game']);
    $entries = storage_read(STORAGE_DIR . '/teamgames_v58_index.json.php', false)['entries'] ?? [];
    foreach ((array)$entries as $entry) {
        if (!is_array($entry) || (string)($entry['class_id'] ?? '') !== $ctx['class'] || !is_string($entry['id'] ?? null) || preg_match('/^[a-z0-9]{8,32}$/', $entry['id']) !== 1) continue;
        $session = storage_read(STORAGE_DIR . '/teamgames_v58/' . $entry['id'] . '.json.php', false);
        $candidate = ev62_teamgame_candidate($session, (string)$ctx['key']);
        if ($candidate !== null) $out[] = $candidate;
    }
    return $out;
}

function ev62_teamgame_candidate(array $session, string $studentKey): ?array
{
    if (!in_array((string)($session['status'] ?? ''), ['finished', 'archived'], true) || (string)($session['finished_at'] ?? '') === '') return null;
    foreach ((array)($session['teams'] ?? []) as $team) {
        if (is_array($team) && in_array($studentKey, array_map('strval', (array)($team['members'] ?? [])), true)) {
            $score = !empty($session['contrib']) && function_exists('tg64_contribution') ? tg64_evidence_score(tg64_contribution($session, (string)$team['id'], $studentKey)) : 0.6; // v64: 0,3 + 0,5 × přínos (max 0,8); starší hry bez záznamu přínosu 0,6
            return ['source' => 'game', 'ref' => 'tg:' . ev62_safe_ref_part((string)$session['id']), 'tags' => ['tg:' . (string)($session['line'] ?? '')], 'score' => $score, 'at' => (string)$session['finished_at']];
        }
    }
    return null;
}

/** Projekty: zveřejněné hodnocení učitele (body / maximum). V MVP bez tagů → ve statistice „nenamapováno“. */
function ev62_collect_projects(array $ctx): array
{
    $groups = [];
    foreach (project_groups() as $g) {
        if (is_array($g) && in_array($ctx['key'], array_map('strval', (array)($g['member_keys'] ?? [])), true)) $groups[(string)($g['id'] ?? '')] = true;
    }
    $out = [];
    foreach (project_grade_records_for_class((string)$ctx['class']) as $r) {
        if (!in_array((string)($r['status'] ?? ''), ['published', 'returned'], true)) continue;
        $mine = ((string)($r['target_type'] ?? '') === 'individual' && (string)($r['target_id'] ?? '') === $ctx['key'])
            || ((string)($r['target_type'] ?? '') === 'group' && isset($groups[(string)($r['target_id'] ?? '')]));
        $max = (float)($r['max_points'] ?? 0);
        if (!$mine || $max <= 0) continue;
        $out[] = ['source' => 'project', 'ref' => 'proj:' . ev62_safe_ref_part((string)($r['id'] ?? '')), 'tags' => [],
            'score' => min(1.0, max(0.0, (float)($r['points'] ?? 0) / $max)), 'at' => (string)($r['published_at'] ?? $r['updated_at'] ?? '')];
    }
    return $out;
}

/**
 * Registr adaptérů: název → ['source' => hlavní typ, 'cheap' => levný (jen čtení malých souborů), 'collect' => callable].
 * @return array<string,array{source:string,cheap:bool,collect:callable}>
 */
function ev62_adapters(): array
{
    return [
        'lab_practice' => ['source' => 'lab', 'cheap' => true, 'collect' => 'ev62_collect_lab_practice'],
        'v56_lessons' => ['source' => 'lesson', 'cheap' => true, 'collect' => 'ev62_collect_v56_lessons'],
        'practice_results' => ['source' => 'test', 'cheap' => false, 'collect' => 'ev62_collect_practice_results'],
        'arena_v57_races' => ['source' => 'arena', 'cheap' => true, 'collect' => 'ev62_collect_arena_races'],
        'arena_v58' => ['source' => 'arena', 'cheap' => true, 'collect' => 'ev62_collect_arena_v58'],
        'arena_v60' => ['source' => 'arena', 'cheap' => false, 'collect' => 'ev62_collect_arena_v60'],
        'teamgames_v58' => ['source' => 'game', 'cheap' => false, 'collect' => 'ev62_collect_teamgames_v58'],
        'robots_v58' => ['source' => 'game', 'cheap' => false, 'collect' => 'ev62_collect_robots_v58'],
        'projects' => ['source' => 'project', 'cheap' => false, 'collect' => 'ev62_collect_projects'],
    ];
}

/**
 * Kandidáty převede na důkazy podle katalogu. @param list<array<string,mixed>> $candidates
 * @return array{rows:list<array<string,mixed>>,unmapped:int}
 */
function ev62_candidates_to_rows(string $studentId, string $subject, array $candidates): array
{
    $competencies = comp62_competencies($subject);
    $rows = [];
    $unmapped = 0;
    foreach ($candidates as $c) {
        $ts = strtotime((string)$c['at']);
        if ($ts === false) continue;
        $matched = comp62_match($subject, (array)$c['tags'], (string)$c['ref']);
        if ($matched === []) { $unmapped++; continue; }
        foreach ($matched as $competency) {
            $rows[] = ['student_id' => $studentId, 'competency' => $competency, 'level' => (int)$competencies[$competency]['level'], 'source' => (string)$c['source'],
                'score' => (float)$c['score'], 'at' => date(DATE_ATOM, $ts), 'artefact_ref' => (string)$c['ref']];
        }
    }
    return ['rows' => $rows, 'unmapped' => $unmapped];
}

/** Identita žáka pro adaptéry, nebo null (neznámý žák / bez student_id). @return array<string,string>|null */
function ev62_student_context(string $classId, string $studentKey): ?array
{
    $student = project_students_for_class($classId)[$studentKey] ?? null;
    $studentId = is_array($student) ? identity58_id_for_student($classId, (string)$student['label']) : null;
    if ($studentId === null || !ev62_valid_id($studentId)) return null;
    return ['class' => $classId, 'key' => $studentKey, 'label' => (string)$student['label'], 'email' => (string)($student['email'] ?? ''), 'id' => $studentId];
}

/**
 * Jen čtení: sesbírá kandidáty ze všech (nebo jen levných) adaptérů a převede je na důkazy. Nic nezapisuje.
 * @return array{rows:list<array<string,mixed>>,unmapped:int,candidates:int,adapters:array<string,int>}
 */
function ev62_collect_student(array $ctx, string $subject, bool $cheapOnly = false): array
{
    $candidates = [];
    $perAdapter = [];
    foreach (ev62_adapters() as $name => $adapter) {
        if ($cheapOnly && !$adapter['cheap']) continue;
        try {
            $got = array_values(array_filter((array)($adapter['collect'])($ctx), static fn($c): bool => is_array($c) && (string)($c['at'] ?? '') !== ''));
        } catch (Throwable $e) {
            error_log('EDUCANET v62 adaptér ' . $name . ': ' . get_class($e));
            $got = [];
        }
        $perAdapter[$name] = count($got);
        $candidates = array_merge($candidates, $got);
    }
    $mapped = ev62_candidates_to_rows((string)$ctx['id'], $subject, $candidates);
    return ['rows' => $mapped['rows'], 'unmapped' => $mapped['unmapped'], 'candidates' => count($candidates), 'adapters' => $perAdapter];
}

/**
 * Synchronizuje důkazy jednoho žáka (TTL EV62_SYNC_TTL, $force ji obejde).
 * Mimo pilot no-op; v režimu jen pro čtení nic nezapisuje; bez student_id se žák přeskočí.
 * @return array<string,mixed> statistika (skipped = důvod přeskočení)
 */
function ev62_sync_student(string $classId, string $studentKey, bool $force = false, bool $cheapOnly = false, ?int $now = null): array
{
    $now ??= time();
    $subject = comp62_subject_for_class($classId);
    if (!comp62_enabled_for_class($classId) || $subject === null) return ['skipped' => 'pilot'];
    if (storage_readonly()) return ['skipped' => 'readonly'];
    $ctx = ev62_student_context($classId, $studentKey);
    if ($ctx === null) return ['skipped' => 'identity'];
    if (!$force && $now - ev62_synced_at($ctx['id']) < EV62_SYNC_TTL) return ['skipped' => 'fresh'];
    $got = ev62_collect_student($ctx, $subject, $cheapOnly);
    $stat = ev62_append($ctx['id'], $got['rows'], $now);
    return $stat + ['candidates' => $got['candidates'], 'unmapped' => $got['unmapped'], 'adapters' => $got['adapters']];
}
