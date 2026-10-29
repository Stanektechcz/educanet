<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Cyklus projektu (návrh → schválení → milníky → odevzdání → peer review → hodnocení → portfolio).
 *
 * Sidecar storage/projects_v65_cycle.json.php = {ref => záznam}. Klíč ref:
 *   cat:<třída>:<projekt>:<individual|group>:<id cíle>   katalogový projekt (project_assessments.php), cíl = klíč žáka / id týmu
 *   p60:<id přihlášky>                                  projekt klienta (projects_v60.php), přihláška musí být schválená
 * Nastavení projektu (režim cyklu, peer review, milníky): storage/projects_v65_meta.json.php.
 * Staré soubory (project_grades, project_workspace_*, projects_v60*) se tu nikdy nepřepisují; známku zapisuje
 * jen project_save_grade() z projects_v65_rubrics.php. Přechody stavů řeší čistá funkce proj65_transition().
 */

const PROJ65_MODES = ['full', 'small'];
/** Kroky cyklu. Malý projekt je o 3 kroky kratší: bez návrhu/schvalování (zadává učitel), bez milníků a bez peer review. */
const PROJ65_STEPS = [
    'full' => ['proposal', 'approved', 'in_progress', 'submitted', 'peer_review', 'graded', 'portfolio'],
    'small' => ['approved', 'submitted', 'graded', 'portfolio'],
];
const PROJ65_PITCH_MIN = 20;
const PROJ65_PITCH_MAX = 600;
const PROJ65_NOTE_MAX = 400;
const PROJ65_BULK_MAX = 50;
const PROJ65_PEER_MIN_SUBMISSIONS = 3;
const PROJ65_HISTORY_CAP = 30;
const PROJ65_REF_RE = '/^(cat:class_[a-z0-9]+:[A-Za-z0-9_\-]{1,60}:(individual|group):[A-Za-z0-9_:\-]{1,80}|p60:[A-Za-z0-9]{4,40})$/';

function proj65_path(string $name): string
{
    return STORAGE_DIR . '/projects_v65_' . preg_replace('/[^a-z_]/', '', $name) . '.json.php';
}

function proj65_state_label(string $state): string
{
    return ['proposal' => 'Návrh', 'rejected' => 'Vráceno k úpravě', 'approved' => 'Schváleno', 'in_progress' => 'Rozpracováno', 'submitted' => 'Odevzdáno',
        'peer_review' => 'Peer review', 'graded' => 'Ohodnoceno', 'portfolio' => 'V portfoliu'][$state] ?? $state;
}

function proj65_ref_cat(string $classId, string $projectId, string $targetType, string $targetId): string
{
    return 'cat:' . $classId . ':' . $projectId . ':' . $targetType . ':' . $targetId;
}

function proj65_ref_p60(string $applicationId): string
{
    return 'p60:' . $applicationId;
}

function proj65_ref_valid(string $ref): bool
{
    return preg_match(PROJ65_REF_RE, $ref) === 1;
}

/** Neprůhledné id záznamu pro URL a formuláře (nikdy klíč žáka). */
function proj65_cycle_id(string $ref): string
{
    return 'c65_' . substr(hash('sha256', $ref), 0, 16);
}

/** 12 hex znaků odvozených z ref – součást artefact_ref důkazu. */
function proj65_hash12(string $ref): string
{
    return substr(hash('sha256', $ref), 0, 12);
}

/** @return array{kind:string,class_id:string,project_id:string,target_type:string,target_id:string}|null */
function proj65_parse_ref(string $ref): ?array
{
    if (!proj65_ref_valid($ref)) return null;
    if (str_starts_with($ref, 'p60:')) {
        $app = function_exists('proj60_application') ? proj60_application(substr($ref, 4)) : null;
        if ($app === null) return ['kind' => 'p60', 'class_id' => '', 'project_id' => '', 'target_type' => 'individual', 'target_id' => ''];
        return ['kind' => 'p60', 'class_id' => (string)$app['class_id'], 'project_id' => (string)$app['project_id'], 'target_type' => 'individual', 'target_id' => (string)$app['student_key']];
    }
    $p = explode(':', $ref, 5);
    return ['kind' => 'cat', 'class_id' => $p[1], 'project_id' => $p[2], 'target_type' => $p[3], 'target_id' => $p[4]];
}

/** Celý sidecar (ref => záznam). */
function proj65_all(): array
{
    return array_filter(storage_read(proj65_path('cycle'), false), 'is_array');
}

function proj65_get(string $ref): ?array
{
    $row = storage_read(proj65_path('cycle'), false)[$ref] ?? null;
    return is_array($row) ? $row : null;
}

function proj65_find_by_id(string $id): ?array
{
    if (preg_match('/^c65_[0-9a-f]{16}$/', $id) !== 1) return null;
    foreach (proj65_all() as $row) {
        if ((string)($row['id'] ?? '') === $id) return $row;
    }
    return null;
}

/** Nastavení projektu: režim cyklu (výchozí malý), peer review, milníky. */
function proj65_settings(string $classId, string $projectId): array
{
    $meta = storage_read(proj65_path('meta'), false);
    $row = is_array($meta['projects'][$classId . ':' . $projectId] ?? null) ? $meta['projects'][$classId . ':' . $projectId] : [];
    $default = in_array((string)($meta['default_mode'] ?? ''), PROJ65_MODES, true) ? (string)$meta['default_mode'] : 'small';
    $mode = in_array((string)($row['cycle_mode'] ?? ''), PROJ65_MODES, true) ? (string)$row['cycle_mode'] : $default;
    return ['cycle_mode' => $mode, 'peer' => $mode === 'full' && !empty($row['peer']), 'milestones' => $mode === 'full' && !empty($row['milestones'])];
}

function proj65_settings_save(string $classId, string $projectId, string $mode, bool $peer, bool $milestones): bool
{
    if (!in_array($mode, PROJ65_MODES, true) || project_find($classId, $projectId) === null) return false;
    storage_update(proj65_path('meta'), static function (array $meta) use ($classId, $projectId, $mode, $peer, $milestones): array {
        $meta['default_mode'] = in_array((string)($meta['default_mode'] ?? ''), PROJ65_MODES, true) ? $meta['default_mode'] : 'small';
        $meta['projects'][$classId . ':' . $projectId] = ['cycle_mode' => $mode, 'peer' => $mode === 'full' && $peer, 'milestones' => $mode === 'full' && $milestones, 'updated_at' => date(DATE_ATOM)];
        return $meta;
    });
    return true;
}

/**
 * Čistá funkce přechodů. $ctx: actor (student|teacher), milestones_on, peer_on, submitted_count, has_submission, has_grade, pitch_len.
 * @return array{ok:bool,error:?string}
 */
function proj65_transition(string $mode, string $from, string $to, array $ctx = []): array
{
    $fail = static fn(string $code): array => ['ok' => false, 'error' => $code];
    if (!in_array($mode, PROJ65_MODES, true)) return $fail('invalid_mode');
    $actor = (string)($ctx['actor'] ?? '');
    $student = $actor === 'student';
    $teacher = $actor === 'teacher';
    $rules = [
        'full' => [
            'proposal>approved' => 'teacher', 'proposal>rejected' => 'teacher', 'rejected>proposal' => 'student',
            'approved>in_progress' => 'student', 'approved>submitted' => 'student', 'in_progress>submitted' => 'student',
            'submitted>peer_review' => 'teacher', 'submitted>graded' => 'teacher', 'peer_review>graded' => 'teacher',
            'graded>approved' => 'teacher', 'graded>portfolio' => 'student', 'portfolio>graded' => 'student',
        ],
        'small' => [
            'approved>submitted' => 'student', 'submitted>graded' => 'teacher', 'graded>approved' => 'teacher',
            'graded>portfolio' => 'student', 'portfolio>graded' => 'student',
        ],
    ][$mode];
    $need = $rules[$from . '>' . $to] ?? null;
    if ($need === null) return $fail('invalid_transition');
    if (($need === 'student' && !$student) || ($need === 'teacher' && !$teacher)) return $fail('actor');
    if ($to === 'in_progress' && empty($ctx['milestones_on'])) return $fail('milestones_off');
    if ($to === 'proposal' && (int)($ctx['pitch_len'] ?? 0) < PROJ65_PITCH_MIN) return $fail('need_pitch');
    if ($to === 'submitted' && $from !== 'graded' && empty($ctx['has_submission'])) return $fail('need_submission');
    if ($to === 'graded' && $from !== 'portfolio' && empty($ctx['has_grade'])) return $fail('need_grade');
    if ($to === 'peer_review') {
        if (empty($ctx['peer_on'])) return $fail('peer_off');
        if ((int)($ctx['submitted_count'] ?? 0) < PROJ65_PEER_MIN_SUBMISSIONS) return $fail('peer_few');
    }
    return ['ok' => true, 'error' => null];
}

/** Počet odevzdaných prací projektu ve třídě (odevzdáno, peer review, ohodnoceno, v portfoliu). */
function proj65_submitted_count(array $all, string $classId, string $projectId): int
{
    $n = 0;
    foreach ($all as $row) {
        if (is_array($row) && (string)($row['class_id'] ?? '') === $classId && (string)($row['project_id'] ?? '') === $projectId
            && in_array((string)($row['state'] ?? ''), ['submitted', 'peer_review', 'graded', 'portfolio'], true)) $n++;
    }
    return $n;
}

function proj65_has_grade(array $row): bool
{
    $versions = array_values(array_filter((array)($row['versions'] ?? []), 'is_array'));
    if ($versions === []) return false;
    $last = (string)($versions[count($versions) - 1]['published_at'] ?? '');
    return $last !== '' && $last >= (string)($row['submission']['at'] ?? '');
}

function proj65_context(array $row, array $all, string $actor): array
{
    $s = proj65_settings((string)$row['class_id'], (string)$row['project_id']);
    return ['actor' => $actor, 'milestones_on' => $s['milestones'], 'peer_on' => $s['peer'], 'has_submission' => !empty($row['submission']['at']),
        'has_grade' => proj65_has_grade($row), 'pitch_len' => u_strlen((string)($row['pitch'] ?? '')), 'submitted_count' => proj65_submitted_count($all, (string)$row['class_id'], (string)$row['project_id'])];
}

function proj65_push_history(array $row, string $state, string $by): array
{
    $hist = array_values((array)($row['history'] ?? []));
    $hist[] = ['state' => $state, 'at' => date(DATE_ATOM), 'by' => $by];
    $row['history'] = array_slice($hist, -PROJ65_HISTORY_CAP);
    $row['state'] = $state;
    $row['updated_at'] = date(DATE_ATOM);
    return $row;
}

/** Členové cíle: žák = jeho klíč, tým = member_keys týmu. @return list<string> */
function proj65_member_keys(array $row): array
{
    if ((string)($row['target_type'] ?? '') === 'group') {
        $g = project_group_find((string)$row['target_id']);
        return $g === null ? [] : array_values(array_map('strval', (array)($g['member_keys'] ?? [])));
    }
    return [(string)($row['target_id'] ?? '')];
}

function proj65_is_member(array $row, string $studentKey): bool
{
    return $studentKey !== '' && in_array($studentKey, proj65_member_keys($row), true);
}

/** Přechod jednoho záznamu pod zámkem. @return array{ok:bool,error:?string} */
function proj65_move(string $ref, string $to, string $actor, string $by): array
{
    $out = ['ok' => false, 'error' => 'not_found'];
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $to, $actor, $by, &$out): array {
        $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
        if ($row === null) return $all;
        $out = proj65_transition((string)$row['mode'], (string)$row['state'], $to, proj65_context($row, $all, $actor));
        if ($out['ok']) $all[$ref] = proj65_push_history($row, $to, $by);
        return $all;
    });
    return $out;
}

/** Normalizovaný text (ořez, bez řídicích znaků). */
function proj65_text(mixed $raw, int $max): string
{
    $text = is_string($raw) ? trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $raw) ?? '') : '';
    return u_substr($text, 0, $max);
}

/**
 * Žák zahájí cyklus katalogového projektu. Malý projekt začíná schválený (zadává učitel), plný návrhem s pitchem.
 * @return array{ok:bool,error:?string,id?:string}
 */
function proj65_start(string $classId, string $studentKey, string $projectId, string $pitch): array
{
    $project = project_find($classId, $projectId);
    if ($project === null || !isset(project_students_for_class($classId)[$studentKey])) return ['ok' => false, 'error' => 'invalid'];
    $type = (string)($project['type'] ?? 'individual');
    $targetId = $studentKey;
    if ($type === 'group') {
        $group = project_group_for_student($classId, $projectId, $studentKey);
        if ($group === null) return ['ok' => false, 'error' => 'no_team'];
        $targetId = (string)$group['id'];
    }
    $mode = proj65_settings($classId, $projectId)['cycle_mode'];
    $pitch = proj65_text($pitch, PROJ65_PITCH_MAX);
    if ($mode === 'full' && u_strlen($pitch) < PROJ65_PITCH_MIN) return ['ok' => false, 'error' => 'need_pitch'];
    $ref = proj65_ref_cat($classId, $projectId, $type, $targetId);
    return proj65_create($ref, ['kind' => 'cat', 'class_id' => $classId, 'project_id' => $projectId, 'target_type' => $type, 'target_id' => $targetId,
        'title' => (string)$project['title'], 'mode' => $mode, 'owner_key' => $studentKey, 'pitch' => $pitch]);
}

/** Cyklus projektu klienta pro schválenou přihlášku (jen její autor, malý režim s ohledem na jednoduchost). */
function proj65_start_p60(string $classId, string $studentKey, string $applicationId): array
{
    $app = function_exists('proj60_application') ? proj60_application($applicationId) : null;
    if ($app === null || (string)$app['status'] !== 'approved' || (string)$app['class_id'] !== $classId || (string)$app['student_key'] !== $studentKey) return ['ok' => false, 'error' => 'invalid'];
    $project = proj60_item((string)$app['project_id']);
    if ($project === null) return ['ok' => false, 'error' => 'invalid'];
    return proj65_create(proj65_ref_p60($applicationId), ['kind' => 'p60', 'class_id' => $classId, 'project_id' => (string)$app['project_id'], 'target_type' => 'individual',
        'target_id' => $studentKey, 'title' => (string)$project['title'], 'mode' => 'small', 'owner_key' => $studentKey, 'pitch' => '']);
}

function proj65_create(string $ref, array $base): array
{
    $out = ['ok' => false, 'error' => 'unknown'];
    $now = date(DATE_ATOM);
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $base, $now, &$out): array {
        if (isset($all[$ref])) { $out = ['ok' => false, 'error' => 'exists']; return $all; }
        $state = $base['mode'] === 'full' ? 'proposal' : 'approved';
        $all[$ref] = $base + ['ref' => $ref, 'id' => proj65_cycle_id($ref), 'state' => $state, 'submission' => null, 'versions' => [], 'draft' => null, 'splits' => [],
            'history' => [['state' => $state, 'at' => $now, 'by' => (string)$base['owner_key']]], 'created_at' => $now, 'updated_at' => $now];
        $out = ['ok' => true, 'error' => null, 'id' => proj65_cycle_id($ref)];
        return $all;
    });
    return $out;
}

/** Žák po zamítnutí upraví pitch a pošle ho znovu (rejected → proposal). */
function proj65_pitch_resubmit(string $ref, string $studentKey, string $pitch): array
{
    $out = ['ok' => false, 'error' => 'not_found'];
    $pitch = proj65_text($pitch, PROJ65_PITCH_MAX);
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $studentKey, $pitch, &$out): array {
        $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
        if ($row === null || !proj65_is_member($row, $studentKey)) return $all;
        $row['pitch'] = $pitch;
        $out = proj65_transition((string)$row['mode'], (string)$row['state'], 'proposal', ['actor' => 'student', 'pitch_len' => u_strlen($pitch)]);
        if ($out['ok']) $all[$ref] = proj65_push_history($row, 'proposal', $studentKey);
        return $all;
    });
    return $out;
}

/** Odevzdání (odkaz + poznámka); i znovu po vrácení k přepracování – tím vzniká další verze hodnocení. */
function proj65_submit(string $ref, string $studentKey, string $url, string $note): array
{
    $safe = $url === '' ? '' : safe_url($url);
    if ($url !== '' && $safe === '') return ['ok' => false, 'error' => 'bad_url'];
    $note = proj65_text($note, PROJ65_NOTE_MAX);
    if ($safe === '' && $note === '') return ['ok' => false, 'error' => 'need_submission'];
    $out = ['ok' => false, 'error' => 'not_found'];
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $studentKey, $safe, $note, &$out): array {
        $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
        if ($row === null || !proj65_is_member($row, $studentKey)) return $all;
        $row['submission'] = ['url' => u_substr($safe, 0, 300), 'note' => $note, 'at' => date(DATE_ATOM), 'by' => $studentKey];
        $out = proj65_transition((string)$row['mode'], (string)$row['state'], 'submitted', ['actor' => 'student', 'has_submission' => true]);
        if ($out['ok']) $all[$ref] = proj65_push_history($row, 'submitted', $studentKey);
        return $all;
    });
    return $out;
}

/**
 * Hromadné schválení návrhů (nejvýš PROJ65_BULK_MAX položek); rozsah třídy se ověřuje u KAŽDÉ položky.
 * @param list<string> $refs @param callable(string):bool $canClass
 * @return array{approved:int,skipped:array<string,string>,truncated:bool}
 */
function proj65_bulk_approve(array $refs, callable $canClass, string $by): array
{
    $refs = array_values(array_unique(array_filter(array_map('strval', $refs), 'proj65_ref_valid')));
    $truncated = count($refs) > PROJ65_BULK_MAX;
    $refs = array_slice($refs, 0, PROJ65_BULK_MAX);
    $res = ['approved' => 0, 'skipped' => [], 'truncated' => $truncated];
    storage_update(proj65_path('cycle'), static function (array $all) use ($refs, $canClass, $by, &$res): array {
        foreach ($refs as $ref) {
            $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
            if ($row === null) { $res['skipped'][$ref] = 'not_found'; continue; }
            if (!$canClass((string)$row['class_id'])) { $res['skipped'][$ref] = 'class_out_of_scope'; continue; }
            $t = proj65_transition((string)$row['mode'], (string)$row['state'], 'approved', proj65_context($row, $all, 'teacher'));
            if (!$t['ok']) { $res['skipped'][$ref] = (string)$t['error']; continue; }
            $all[$ref] = proj65_push_history($row, 'approved', $by);
            $res['approved']++;
        }
        return $all;
    });
    return $res;
}

/** Odmítnutí návrhu učitelem (jedna položka, třída se ověřuje voláním). */
function proj65_reject(string $ref, callable $canClass, string $by): array
{
    $out = ['ok' => false, 'error' => 'not_found'];
    storage_update(proj65_path('cycle'), static function (array $all) use ($ref, $canClass, $by, &$out): array {
        $row = is_array($all[$ref] ?? null) ? $all[$ref] : null;
        if ($row === null) return $all;
        if (!$canClass((string)$row['class_id'])) { $out = ['ok' => false, 'error' => 'class_out_of_scope']; return $all; }
        $out = proj65_transition((string)$row['mode'], (string)$row['state'], 'rejected', proj65_context($row, $all, 'teacher'));
        if ($out['ok']) $all[$ref] = proj65_push_history($row, 'rejected', $by);
        return $all;
    });
    return $out;
}

/** Záznamy cyklu žáka (vlastní + týmové), nejnovější první. @return list<array<string,mixed>> */
function proj65_for_student(string $classId, string $studentKey): array
{
    $out = [];
    foreach (proj65_all() as $row) {
        if ((string)($row['class_id'] ?? '') === $classId && proj65_is_member($row, $studentKey)) $out[] = $row;
    }
    usort($out, static fn(array $a, array $b): int => strcmp((string)$b['updated_at'], (string)$a['updated_at']));
    return $out;
}

/** Záznamy třídy pro učitele. @return list<array<string,mixed>> */
function proj65_for_class(string $classId): array
{
    $out = array_values(array_filter(proj65_all(), static fn(array $r): bool => (string)($r['class_id'] ?? '') === $classId));
    usort($out, static fn(array $a, array $b): int => strcmp((string)$b['updated_at'], (string)$a['updated_at']));
    return $out;
}

/** Zahájení peer review projektu učitelem: stav submitted → peer_review a přidělení recenzentů (jedna transakce). */
function proj65_peer_open(string $classId, string $projectId, string $by): array
{
    $settings = proj65_settings($classId, $projectId);
    $out = ['ok' => false, 'error' => 'peer_off', 'moved' => 0];
    if (!$settings['peer']) return $out;
    storage_update_many([proj65_path('cycle'), proj65_path('peer')], static function (array $data) use ($classId, $projectId, $by, &$out): array {
        $cycle = $data[proj65_path('cycle')];
        $peer = $data[proj65_path('peer')];
        $count = proj65_submitted_count($cycle, $classId, $projectId);
        if ($count < PROJ65_PEER_MIN_SUBMISSIONS) { $out = ['ok' => false, 'error' => 'peer_few', 'moved' => 0]; return $data; }
        $authors = [];
        foreach ($cycle as $ref => $row) {
            if (is_array($row) && (string)$row['class_id'] === $classId && (string)$row['project_id'] === $projectId && (string)$row['state'] === 'submitted') $authors[(string)$ref] = proj65_member_keys($row);
        }
        $pool = proj65_peer_pool($cycle, $classId, $projectId);
        $peer = proj65_peer_assign($peer, $authors, $pool, $classId, $projectId);
        foreach (array_keys($authors) as $ref) $cycle[$ref] = proj65_push_history($cycle[$ref], 'peer_review', $by);
        $out = ['ok' => true, 'error' => null, 'moved' => count($authors)];
        return [proj65_path('cycle') => $cycle, proj65_path('peer') => $peer];
    });
    return $out;
}

/**
 * v66: zveřejněná hodnocení projektů žáka jako poměr 0–1 (čtení pro návrh hodnocení). Počítá osobní i týmová hodnocení třídy;
 * záznam bez maxima se přeskočí. @return list<array{id:string,ratio:float,at:string}>
 */
function proj65_published_ratios(string $classId, string $studentKey): array
{
    $groups = [];
    foreach (project_groups() as $g) {
        if (is_array($g) && in_array($studentKey, array_map('strval', (array)($g['member_keys'] ?? [])), true)) $groups[(string)($g['id'] ?? '')] = true;
    }
    $out = [];
    foreach (project_grade_records_for_class($classId) as $r) {
        if (!in_array((string)($r['status'] ?? ''), ['published', 'returned'], true)) continue;
        $mine = ((string)($r['target_type'] ?? '') === 'individual' && (string)($r['target_id'] ?? '') === $studentKey)
            || ((string)($r['target_type'] ?? '') === 'group' && isset($groups[(string)($r['target_id'] ?? '')]));
        $max = (float)($r['max_points'] ?? 0);
        if (!$mine || $max <= 0) continue;
        $out[] = ['id' => (string)($r['id'] ?? ''), 'ratio' => round(min(1.0, max(0.0, (float)($r['points'] ?? 0) / $max)), 3), 'at' => (string)($r['published_at'] ?? $r['updated_at'] ?? '')];
    }
    return $out;
}
