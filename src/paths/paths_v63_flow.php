<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/paths_v63.php';

/**
 * EDUCANET v63 · výukové cesty (tok): odevzdání kroku, důkazy, reflexe a doporučení „Co dál“.
 *
 * p63_submit_step: identita žáka je vždy ze session volajícího (třída + klíč žáka), nikdy z parametru požadavku.
 * Zápis stavu je jediný storage_update pod zámkem (kontrola souběhu a denního limitu ověření uvnitř), důkaz jde
 * přes ev62_append (append-only, bez volného textu). p63_next jen čte (stav žáka + cache v62) – nezapisuje,
 * nevolá ev62_sync_student ani m62_class a výsledek si pamatuje po dobu požadavku.
 */

// ---------------------------------------------------------------------------
// Odevzdání kroku
// ---------------------------------------------------------------------------

function p63_fail(string $error): array
{
    return ['ok' => false, 'error' => $error, 'score' => 0.0, 'passed' => false, 'attempt' => 0, 'variant' => -1, 'detail' => [], 'evidence' => 0];
}

/** Kontext požadavku: cesta třídy, krok a student_id; při chybě ['error' => kód]. */
function p63_context(string $classId, string $studentKey, string $pathId, string $stepId): array
{
    $path = p63_path_for_class($classId, $pathId);
    $step = $path === null || preg_match(P63_STEP_RE, $stepId) !== 1 ? null : p63_step($path, $stepId);
    if ($path === null || $step === null) return ['error' => 'unknown_step'];
    $sid = p63_student_id($classId, $studentKey);
    return $sid === null ? ['error' => 'identity'] : ['path' => $path, 'step' => $step, 'sid' => $sid, 'class' => $classId];
}

/** Kolik pokusů o ověření už žák dnes udělal. */
function p63_verify_used(array $state, string $pathId, int $now): int
{
    $entry = p63_step_entry($state, $pathId, 'verify');
    return $entry['verify_day'] === date('Y-m-d', $now) ? $entry['verify_count'] : 0;
}

/** Kolik pokusů o ověření dnes zbývá. */
function p63_verify_left(array $state, string $pathId, ?int $now = null): int
{
    return max(0, P63_VERIFY_DAILY_LIMIT - p63_verify_used($state, $pathId, $now ?? time()));
}

function p63_grade_quiz_step(array $ctx, array $state, array $input, int $attempt): array
{
    $answers = is_array($input['a'] ?? null) ? array_values($input['a']) : [];
    if (array_filter($answers, static fn($v): bool => is_string($v) && trim($v) !== '') === []) return p63_fail('empty');
    $ids = p63_step_question_ids($ctx['path'], $ctx['step'], $ctx['sid'], $state, $attempt);
    $graded = p63_grade_questions($ids, $answers);
    $variant = (string)$ctx['step']['type'] === 'verify' ? p63_current_variant($ctx['path'], $ctx['step'], $ctx['sid'], $state) : -1;
    return ['ok' => true, 'score' => $graded['score'], 'passed' => $graded['score'] >= P63_PASS_SCORE, 'variant' => $variant,
        'detail' => ['ids' => $ids, 'ok' => $graded['detail'], 'correct' => $graded['correct'], 'total' => $graded['total']]];
}

function p63_grade_parsons_step(array $ctx, array $input, int $attempt): array
{
    $step = $ctx['step'];
    $order = is_string($input['order'] ?? null) && preg_match('/^\d{1,2}(,\d{1,2})*$/', (string)$input['order']) === 1 ? array_map('intval', explode(',', (string)$input['order'])) : [];
    $sig = is_string($input['sig'] ?? null) ? (string)$input['sig'] : '';
    $n = count((array)$step['lines']);
    $expected = p63_parsons_sign($order, (string)$ctx['path']['id'], (string)$step['id'], $attempt, p63_parsons_secret());
    if (!p63_valid_order($order, $n) || !hash_equals($expected, $sig)) return p63_fail('bad_order');
    $score = p63_grade_parsons(range(0, $n - 1), (array)($step['accept'] ?? []), $order);
    return ['ok' => true, 'score' => $score, 'passed' => $score >= 1.0, 'variant' => -1, 'detail' => ['order' => $order]];
}

function p63_grade_pre_step(array $ctx, array $state, array $input): array
{
    $cases = array_values((array)$ctx['step']['cases']);
    $variant = p63_current_variant($ctx['path'], $ctx['step'], $ctx['sid'], $state);
    $score = p63_grade_pre((array)($cases[$variant] ?? []), $input['choice'] ?? null);
    if ($score === null) return p63_fail('bad_choice');
    return ['ok' => true, 'score' => $score, 'passed' => $score >= 1.0, 'variant' => $variant, 'detail' => ['case' => (string)($cases[$variant]['id'] ?? ''), 'choice' => (int)$input['choice']]];
}

function p63_grade_step(array $ctx, array $state, array $input): array
{
    $attempt = p63_step_entry($state, (string)$ctx['path']['id'], (string)$ctx['step']['id'])['attempts'] + 1;
    return match ((string)$ctx['step']['type']) {
        'explain' => ['ok' => true, 'score' => 1.0, 'passed' => true, 'variant' => -1, 'detail' => []],
        'retrieval', 'verify' => p63_grade_quiz_step($ctx, $state, $input, $attempt),
        'parsons' => p63_grade_parsons_step($ctx, $input, $attempt),
        'pre' => p63_grade_pre_step($ctx, $state, $input),
        default => p63_fail('bad_type'),
    };
}

/** Uloží výsledek pokusu pod zámkem; kontroluje souběh (počet pokusů) a denní limit ověření. @return array{ok:bool,error:string,attempt:int} */
function p63_apply_result(array $ctx, array $graded, int $attempt, int $now): array
{
    $pathId = (string)$ctx['path']['id'];
    $stepId = (string)$ctx['step']['id'];
    $isVerify = (string)$ctx['step']['type'] === 'verify';
    $res = ['ok' => false, 'error' => 'conflict', 'attempt' => $attempt];
    storage_update(p63_state_path($ctx['sid']), static function (array $raw) use ($ctx, $graded, $attempt, $now, $pathId, $stepId, $isVerify, &$res): array {
        $state = p63_state_normalize($raw);
        $entry = p63_step_entry($state, $pathId, $stepId);
        if ($entry['attempts'] !== $attempt - 1) return $raw;
        $used = p63_verify_used($state, $pathId, $now);
        if ($isVerify && $used >= P63_VERIFY_DAILY_LIMIT) { $res['error'] = 'limit'; return $raw; }
        $state['paths'][$pathId][$stepId] = ['status' => ($entry['status'] === 'done' || $graded['passed']) ? 'done' : 'tried', 'attempts' => $attempt,
            'best' => max($entry['best'], (float)$graded['score']), 'last_variant' => $graded['variant'] >= 0 ? (int)$graded['variant'] : $entry['last_variant'], 'at' => $now,
            'verify_day' => $isVerify ? date('Y-m-d', $now) : $entry['verify_day'], 'verify_count' => $isVerify ? $used + 1 : $entry['verify_count']];
        $competency = (string)$ctx['path']['competency'];
        $current = is_array($state['spaced'][$competency] ?? null) ? $state['spaced'][$competency] : null;
        if ($isVerify && $graded['passed'] && $current === null) $state['spaced'][$competency] = p63_spaced_schedule(null, false, $now);
        if ($stepId === 'spaced') $state['spaced'][$competency] = p63_spaced_schedule($current, (bool)$graded['passed'], $now);
        $res = ['ok' => true, 'error' => '', 'attempt' => $attempt];
        return $state;
    });
    return $res;
}

/** Zapíše důkaz do v62 (jen třídy v pilotu a hodnocené kroky; bez volného textu). Vrací počet nových řádků. */
function p63_record_evidence(string $classId, string $studentId, array $path, array $step, array $graded, int $attempt, int $now): int
{
    if (!p63_writes_evidence($classId) || (string)$step['type'] === 'explain') return 0;
    $competency = (string)$step['competency'];
    if (!isset(comp62_competencies((string)comp62_subject_for_class($classId))[$competency])) return 0;
    $isVerify = (string)$step['type'] === 'verify';
    $ref = 'p63:' . $path['id'] . ':' . $step['id'] . ':' . ($isVerify ? 'v' . ((int)$graded['variant'] + 1) : 'a' . $attempt);
    $row = ['competency' => $competency, 'level' => (int)$step['level'], 'source' => $isVerify ? 'test' : 'lesson', 'score' => (float)$graded['score'], 'at' => date(DATE_ATOM, $now), 'artefact_ref' => $ref];
    return (int)ev62_append($studentId, [$row])['added'];
}

/**
 * Odevzdá krok. Identita (třída, klíč žáka) je vždy ze session volajícího, ne z parametru požadavku.
 * @return array{ok:bool,error:string,score:float,passed:bool,attempt:int,variant:int,detail:array,evidence:int}
 */
function p63_submit_step(string $classId, string $studentKey, string $pathId, string $stepId, array $input, ?int $now = null): array
{
    $now ??= time();
    $ctx = p63_context($classId, $studentKey, $pathId, $stepId);
    if (isset($ctx['error'])) return p63_fail((string)$ctx['error']);
    $state = p63_state($ctx['sid']);
    if (!p63_step_open($ctx['path'], $state, $stepId)) return p63_fail('locked');
    if ((string)$ctx['step']['type'] === 'verify' && p63_verify_left($state, $pathId, $now) <= 0) return p63_fail('limit');
    $graded = p63_grade_step($ctx, $state, $input);
    if (!$graded['ok']) return p63_fail((string)($graded['error'] ?? 'invalid'));
    $attempt = p63_step_entry($state, $pathId, $stepId)['attempts'] + 1;
    $applied = p63_apply_result($ctx, $graded, $attempt, $now);
    if (!$applied['ok']) return p63_fail($applied['error']);
    $evidence = p63_record_evidence($classId, $ctx['sid'], $ctx['path'], $ctx['step'], $graded, $attempt, $now);
    p63_memo_reset();
    return ['ok' => true, 'error' => '', 'score' => (float)$graded['score'], 'passed' => (bool)$graded['passed'], 'attempt' => $attempt, 'variant' => (int)$graded['variant'],
        'detail' => (array)$graded['detail'], 'evidence' => $evidence];
}

// ---------------------------------------------------------------------------
// Reflexe a kalibrace
// ---------------------------------------------------------------------------

/**
 * Uloží reflexi (sebehodnocení 1–4 + věta). Věta zůstává jen v souboru žáka – ne v důkazech, exportu ani logu.
 * Vyžaduje splněné ověření (kalibrace porovnává sebehodnocení s jeho výsledkem).
 * @return array{ok:bool,error:string,self:int,actual:int,delta:int}
 */
function p63_save_reflection(string $classId, string $studentKey, string $pathId, int $self, string $note, ?int $now = null): array
{
    $now ??= time();
    $ctx = p63_context($classId, $studentKey, $pathId, 'reflect');
    if (isset($ctx['error'])) return ['ok' => false, 'error' => (string)$ctx['error'], 'self' => 0, 'actual' => 0, 'delta' => 0];
    if ($self < 1 || $self > 4) return ['ok' => false, 'error' => 'bad_self', 'self' => 0, 'actual' => 0, 'delta' => 0];
    $res = ['ok' => false, 'error' => 'verify_first', 'self' => 0, 'actual' => 0, 'delta' => 0];
    storage_update(p63_state_path($ctx['sid']), static function (array $raw) use ($pathId, $self, $note, $now, &$res): array {
        $state = p63_state_normalize($raw);
        $verify = p63_step_entry($state, $pathId, 'verify');
        if ($verify['status'] !== 'done') return $raw;
        $actual = p63_actual_level($verify['best']);
        $attempts = p63_step_entry($state, $pathId, 'reflect')['attempts'] + 1;
        $state['reflect'][$pathId] = ['self' => $self, 'mastery_at_time' => $actual, 'note' => p63_clean_note($note), 'at' => $now];
        $state['paths'][$pathId]['reflect'] = ['status' => 'done', 'attempts' => $attempts, 'best' => 1.0, 'last_variant' => -1, 'at' => $now, 'verify_day' => '', 'verify_count' => 0];
        $res = ['ok' => true, 'error' => '', 'self' => $self, 'actual' => $actual, 'delta' => $self - $actual];
        return $state;
    });
    p63_memo_reset();
    return $res;
}

// ---------------------------------------------------------------------------
// „Co dál“ (jedno doporučení s důvodem, jen čtení)
// ---------------------------------------------------------------------------

/** Cesta, jejíž kompetence odpovídá; null = žádná. */
function p63_path_for_competency(array $paths, string $competency): ?array
{
    foreach ($paths as $path) {
        if ((string)$path['competency'] === $competency) return $path;
    }
    return null;
}

/** Doporučení: kind, reason (kód), path, step, competency, minutes (> 0), title, step_title, href. */
function p63_recommend(string $kind, array $path, string $stepId, string $reason): array
{
    $step = p63_step($path, $stepId) ?? ['title' => '', 'minutes' => 3];
    return ['kind' => $kind, 'reason' => $reason, 'path' => (string)$path['id'], 'step' => $stepId, 'competency' => (string)$path['competency'], 'minutes' => max(1, (int)($step['minutes'] ?? 3)),
        'title' => (string)$path['title'], 'step_title' => (string)($step['title'] ?? ''), 'href' => '?view=cesta&path=' . rawurlencode((string)$path['id']) . '&step=' . rawurlencode($stepId)];
}

/** Rozpracovaná (nedokončená) cesta s nejčerstvější aktivitou. */
function p63_active_path(array $paths, array $state): ?array
{
    $best = null;
    $bestAt = -1;
    foreach ($paths as $path) {
        $progress = p63_path_progress($path, $state);
        if (!$progress['started'] || $progress['finished']) continue;
        $at = max(array_map(static fn(string $s): int => p63_step_entry($state, (string)$path['id'], $s)['at'], p63_step_ids($path)));
        if ($at > $bestAt) { $best = $path; $bestAt = $at; }
    }
    return $best;
}

/** Cesta cache zvládnutí v62 (stejná jako m62_cache_path; mastery_v62.php se kvůli dashboardu nenačítá, shodu hlídá audit). */
function p63_mastery_cache_path(string $studentId): string
{
    return STORAGE_DIR . '/mastery_v62/' . preg_replace('/[^A-Za-z0-9_]/', '', $studentId) . '.json.php';
}

/** Zvládnutí kompetencí ze CACHE v62 (jen čtení, bez synchronizace a přepočtu); prázdné pole, když cache není. */
function p63_mastery_cache(string $studentId): array
{
    $map = storage_read(p63_mastery_cache_path($studentId), false)['map'] ?? [];
    return is_array($map) ? $map : [];
}

/** Nejslabší nedokončená cesta podle cache v62: rozpracováno (nejnižší skóre), jinak první neověřená. @return array{path:array,reason:string}|null */
function p63_weak_path(array $paths, array $state, array $map): ?array
{
    $weak = null;
    $weakScore = 2.0;
    $unverified = null;
    foreach ($paths as $path) {
        if (p63_path_progress($path, $state)['finished']) continue;
        $info = $map[(string)$path['competency']] ?? ['state' => 'neovereno', 'score' => null];
        if ((string)$info['state'] === 'rozpracovano' && (float)($info['score'] ?? 0) < $weakScore) { $weak = $path; $weakScore = (float)$info['score']; }
        if ((string)$info['state'] === 'neovereno' && $unverified === null) $unverified = $path;
    }
    return $weak !== null ? ['path' => $weak, 'reason' => 'weak'] : ($unverified !== null ? ['path' => $unverified, 'reason' => 'unverified'] : null);
}

/** Další krok cesty (první nehotový). */
function p63_next_step_id(array $path, array $state): string
{
    return (string)(p63_path_progress($path, $state)['next'] ?? p63_step_ids($path)[0]);
}

/**
 * Jedno doporučení pro dashboard, nebo null (třída bez cest / žák bez identity / vše hotové a nic k opakování).
 * Pořadí: splatné opakování → rozpracovaná cesta → přiřazená cesta → nejslabší kompetence (cache v62) → další cesta.
 * Vždy vrací důvod (reason) a odhad v minutách. Paměť jen po dobu požadavku.
 */
function p63_next(string $classId, string $studentKey, ?int $now = null): ?array
{
    $now ??= time();
    $memoKey = $classId . '|' . $studentKey . '|' . $now;
    if (array_key_exists($memoKey, $GLOBALS['p63_memo'] ?? [])) return $GLOBALS['p63_memo'][$memoKey];
    return $GLOBALS['p63_memo'][$memoKey] = p63_next_compute($classId, $studentKey, $now);
}

function p63_next_compute(string $classId, string $studentKey, int $now): ?array
{
    $paths = p63_paths_for_class($classId);
    $sid = $paths === [] ? null : p63_student_id($classId, $studentKey);
    if ($sid === null) return null;
    $state = p63_state($sid);
    foreach (p63_spaced_due($state['spaced'], $now) as $competency) {
        $path = p63_path_for_competency($paths, $competency);
        if ($path !== null && p63_step($path, 'spaced') !== null) return p63_recommend('spaced', $path, 'spaced', 'spaced');
    }
    $active = p63_active_path($paths, $state);
    if ($active !== null) return p63_recommend('continue', $active, p63_next_step_id($active, $state), 'continue');
    foreach (array_keys($state['assigned']) as $pathId) {
        $path = $paths[(string)$pathId] ?? null;
        if ($path !== null && !p63_path_progress($path, $state)['finished']) return p63_recommend('assigned', $path, p63_next_step_id($path, $state), 'assigned');
    }
    $weak = p63_weak_path($paths, $state, p63_mastery_cache($sid));
    if ($weak !== null) return p63_recommend('weak', $weak['path'], p63_next_step_id($weak['path'], $state), $weak['reason']);
    foreach ($paths as $path) {
        if (!p63_path_progress($path, $state)['finished']) return p63_recommend('start', $path, p63_next_step_id($path, $state), 'next');
    }
    return null;
}
