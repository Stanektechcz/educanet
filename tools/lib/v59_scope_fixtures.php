<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v59 · fixture a pomocníci pro tools/v59_teacher_scope_audit.php (AUTHZ58-07).
 * Vše běží nad dočasným úložištěm auditu (edu_audit_temp_storage) – nikdy nad ostrou storage/.
 * Značky: MK1A…MK4A (data třídy), MKNET/MKBOTH (otázky banky sítí/obou), CAL3A (výjimka kalendáře 3.A).
 * Jména žáků se nikdy nevypisují (jen se porovnávají s odpovědí serveru).
 */

const V59SF_PASSWORDS = [
    'admin' => 'Stribrny-Most-6284-Rak', 'a' => 'Zelena-Lod-4827-Kopec', 'b' => 'Cerveny-Vlak-3947-Hora',
    's' => 'Modry-Balon-7342-Vrch', 'e' => 'Hneda-Kotva-5823-Luka',
];
const V59SF_VOLATILE_RE = '#(^|/)(rate_limits\.json\.php|teacher_accounts_v59(_log|_noise|_locks)?\.json\.php|teacher_team_members\.json\.php)$|\.lock$|\.tmp\.#';

/** Vybere první položku (nebo $default), bez vyhazování. */
function v59sf_try(array &$errors, string $label, callable $fn, mixed $default = null): mixed
{
    try {
        return $fn();
    } catch (Throwable $e) {
        $errors[] = $label . ': ' . $e->getMessage();
        return $default;
    }
}

/** Připíše řádek do úložiště se seznamem (pod zámkem). */
function v59sf_push(string $file, array $row): void
{
    storage_update(STORAGE_DIR . '/' . $file, static function (array $rows) use ($row): array { $rows[] = $row; return array_values($rows); });
}

/** @return array{ids: array<string, array<string,string>>, missing: list<string>, errors: list<string>, student_3a: string, student_2a: string, label_3a_only: string} */
function v59sf_build_fixtures(array $modules): array
{
    $errors = [];
    $ids = [];
    $now = time();
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', $now + 86400);
    $students = [];
    foreach (['class_1a', 'class_2a', 'class_3a', 'class_4a'] as $c) $students[$c] = project_students_for_class($c);
    $student = static fn(string $c): string => (string)(array_key_first($students[$c]) ?? ($c . ':student:audit'));
    $student3a = $student('class_3a');
    $student2a = $student('class_2a');
    $graphicsLabels = [];
    foreach (['class_1a', 'class_2a'] as $c) foreach ($students[$c] as $row) $graphicsLabels[] = (string)($row['label'] ?? '');
    $label3a = '';
    foreach ($students['class_3a'] as $row) {
        $label = (string)($row['label'] ?? '');
        if (mb_strlen($label) < 6 || preg_match('/MK|Demo/u', $label)) continue;
        if (!array_filter($graphicsLabels, static fn(string $g): bool => str_contains($g, $label) || str_contains($label, $g))) { $label3a = $label; break; }
    }

    foreach (['3a' => 'class_3a', '2a' => 'class_2a', '4a' => 'class_4a'] as $k => $c) {
        $mk = 'MK' . strtoupper($k);
        $ids['race'][$k] = v59sf_try($errors, 'race ' . $k, static fn() => (string)arena57_create_race(['class_id' => $c, 'preset' => 'rozcvicka', 'title' => $mk . ' závod'], [$c], $now)['id']);
        $ids['match'][$k] = v59sf_try($errors, 'match ' . $k, static fn() => (string)robots58_create_match(['class_id' => $c, 'title' => $mk . ' zápas'], [$c], $now)['id']);
        $ids['tg58_game'][$k] = v59sf_try($errors, 'tg58 ' . $k, static fn() => (string)tg58_create_session(['class_id' => $c, 'type' => 'relay', 'title' => $mk . ' hra', 'line' => tg58_line_for_class($c)], [$c], $now)['id']);
        $ids['ctf_event'][$k] = v59sf_try($errors, 'ctf ' . $k, static fn() => (string)arena58_ctf_create_event(['class_ids' => [$c], 'title' => $mk . ' CTF'], [$c], $now)['id']);
        $ids['inc_session'][$k] = v59sf_try($errors, 'incident ' . $k, static fn() => (string)arena58_inc_create_session(['class_id' => $c, 'title' => $mk . ' incident'], [$c], $now)['id']);
        $ids['sess53'][$k] = v59sf_try($errors, 'sess53 ' . $k, static fn() => (string)sess53_open($c, $today, $modules, [], 'work', ['lesson_number' => 1, 'title' => $mk . ' hodina', 'goal' => ''])['id']);
        $ids['lab58e_level'][$k] = v59sf_try($errors, 'editor ' . $k, static fn() => (string)lab58e_save([
            'title' => $mk . ' úloha', 'story' => 'Příběh', 'task' => 'Úkol', 'learn' => '', 'type' => 'answer', 'difficulty' => 1, 'minutes' => 5, 'classes' => [$c],
            'hints' => [], 'files' => [], 'generators' => [], 'checks' => [], 'answer' => '42', 'solution' => ['submit 42'], 'status' => 'draft', 'checklist' => [], 'last_check' => null, 'created_by' => 'audit',
        ])['id']);
        $ids['demo_account'][$k] = v59sf_try($errors, 'demo ' . $k, static fn() => (string)teacher_demo_account_create($modules, $c, $mk . ' Demo')['email']);
        $ids['mkt60_item'][$k] = v59sf_try($errors, 'mkt60 ' . $k, static fn() => (string)mkt60_save_item('', ['type' => 'other', 'title' => $mk . ' obchod', 'price' => 5, 'classes' => [$c], 'active' => true], [$c], 'audit'));
        $ids['proj60_project'][$k] = v59sf_try($errors, 'proj60 project ' . $k, static fn() => (string)proj60_save('', ['title' => $mk . ' projekt', 'reward_type' => 'other', 'min_level' => 1, 'capacity' => 5, 'classes' => [$c], 'status' => 'open'], [$c], 'audit'));
        $ids['proj60_app'][$k] = v59sf_try($errors, 'proj60 app ' . $k, static function () use ($ids, $k, $c, $mk): string {
            $result = proj60_apply($c, $c . ':student:' . $mk . ' Zajemce', (string)$ids['proj60_project'][$k], $mk . ' motivace', 9);
            return (string)($result['application_id'] ?? '');
        });
        $ids['fb60_report'][$k] = v59sf_try($errors, 'fb60 ' . $k, static fn() => (string)fb60_submit($c, $c . ':student:' . $mk . ' Hlasitel', ['type' => 'bug', 'title' => $mk . ' hlášení', 'description' => $mk . ' popis chyby dostatečně dlouhý'])['id']);
        $key = $student($c);
        $ids['student_key'][$k] = $key;
        $ids['grade_target'][$k] = $key;
        $iso = date(DATE_ATOM, $now);
        $ids['intervention'][$k] = 'intervention_mk' . $k;
        v59sf_try($errors, 'intervention ' . $k, static function () use ($c, $k, $key, $mk, $tomorrow, $iso): void {
            v59sf_push('teacher_interventions.json.php', ['id' => 'intervention_mk' . $k, 'class_id' => $c, 'student_keys' => [$key], 'title' => $mk . ' intervence', 'problem' => 'Problém',
                'success_metric' => 'Cíl', 'review_due' => $tomorrow, 'status' => 'active', 'owner_key' => 'audit-owner', 'owner_label' => 'Audit', 'team_key' => teacher_team_key(),
                'baseline' => [], 'steps' => [], 'timeline' => [], 'created_at' => $iso, 'updated_at' => $iso, 'completed_at' => null]);
        });
        $slug = '';
        foreach (skill_all() as $skillSlug => $skill) { if (is_array($skill) && in_array((string)($skill['branch'] ?? ''), skill_relevant_branches($c), true)) { $slug = (string)$skillSlug; break; } }
        $ids['skill_evidence'][$k] = v59sf_try($errors, 'skill evidence ' . $k, static function () use ($c, $key, $slug, $k): string {
            $type = (string)array_key_first(skill_evidence_rules((array)skill_find($slug)));
            return (string)skill_add_evidence($c, $key, $slug, $type, 'mk-src-' . $k, 50, 100, 'pending', ['note' => 'MK' . strtoupper($k)], false)['id'];
        });
        $ids['skill_assignment'][$k] = 'skill_assignment_mk' . $k;
        v59sf_try($errors, 'skill assignment ' . $k, static function () use ($c, $k, $slug, $iso): void {
            v59sf_push('skill_assignments.json.php', ['id' => 'skill_assignment_mk' . $k, 'class_id' => $c, 'skill' => $slug, 'target_type' => 'class', 'target_key' => 'class',
                'assignment_type' => 'recommended', 'due_at' => null, 'status' => 'active', 'assigned_by' => 'Audit', 'created_at' => $iso, 'cancelled_at' => null]);
        });
        $ids['peer_feedback'][$k] = 'peer_feedback_mk' . $k;
        v59sf_try($errors, 'peer feedback ' . $k, static function () use ($c, $k, $key, $mk, $iso): void {
            v59sf_push('project_workspace_peer_feedback.json.php', ['id' => 'peer_feedback_mk' . $k, 'class_id' => $c, 'project_id' => '', 'group_id' => 'grp_mk' . $k, 'author_key' => $key,
                'target_key' => $key, 'text' => $mk . ' zpětná vazba', 'status' => 'visible', 'created_at' => $iso]);
        });
        $ids['ml_live'][$k] = 'ml_live_mk' . $k;
        v59sf_try($errors, 'ml live ' . $k, static function () use ($c, $k, $mk, $iso): void {
            ml_store_update('live', static function (array $rows) use ($c, $k, $mk, $iso): array {
                $rows[] = ['id' => 'ml_live_mk' . $k, 'class_id' => $c, 'question' => $mk . ' otázka', 'options' => ['Ano', 'Ne'], 'correct' => 0, 'status' => 'open', 'phase' => 'individual', 'responses' => [], 'created_at' => $iso];
                return array_values($rows);
            });
        });
        $ids['ml_scenario'][$k] = v59sf_try($errors, 'ml scenario ' . $k, static fn() => (string)ml_scenario_save(['class_id' => $c, 'title' => $mk . ' scénář', 'brief' => 'Situace v síti, kterou žák řeší krok za krokem.',
            'choice_0' => 'Zkontrolovat bránu', 'choice_1' => 'Restartovat počítač', 'correct' => 0, 'why' => 'Brána je první bod diagnostiky sítě.', 'student_key' => $key], false)['id']);
        $ids['ml_tip'][$k] = v59sf_try($errors, 'ml tip ' . $k, static fn() => (string)ml_tip_save($c, $key, 'audit', $mk . ' tip: vždy si nejdřív ověř adresu brány.', false)['id']);
        $groupId = 'grp_mk' . $k;
        $projectId = '';
        foreach ((array)(project_catalog()[$c] ?? []) as $project) { if (is_array($project) && (string)($project['type'] ?? '') === 'group') { $projectId = (string)($project['id'] ?? ''); break; } }
        v59sf_try($errors, 'group ' . $k, static function () use ($groupId, $c, $projectId, $mk, $key, $now): void {
            v59sf_push('project_groups.json.php', ['id' => $groupId, 'class_id' => $c, 'project_id' => $projectId, 'name' => $mk . ' tým', 'members' => [$key], 'member_keys' => [$key], 'created_at' => date(DATE_ATOM, $now)]);
        });
        $ids['group'][$k] = $groupId;
        $responseId = 'resp_mk' . $k;
        $storage = 'mk' . $k . '_' . bin2hex(random_bytes(4)) . '.png';
        v59sf_try($errors, 'intake ' . $k, static function () use ($responseId, $c, $mk, $storage, $now): void {
            file_put_contents(intake_v51_dir('uploads') . '/' . $storage, intake_v51_upload_header() . $mk . '-ARTIFACT');
            intake_v51_update('responses', static function (array $rows) use ($responseId, $c, $mk, $storage, $now): array {
                $rows[] = ['id' => $responseId, 'class_id' => $c, 'source' => 'audit', 'submitted_at' => date(DATE_ATOM, $now), 'answers' => ['about' => $mk . ' odpověď'],
                    'student' => ['first_name' => $mk, 'last_name' => 'Respondent', 'preferred_name' => $mk, 'seat_label' => 'R1-L1'],
                    'assessment' => ['type' => 'none', 'artifact' => ['storage_name' => $storage, 'mime' => 'image/png', 'original_name' => $mk . '.png']]];
                return $rows;
            });
        });
        $ids['intake_response'][$k] = $responseId;
        $actKey = $c . ':student:mkact' . $k;
        v59sf_try($errors, 'activation ' . $k, static function () use ($actKey, $c, $mk): void {
            intake_v51_update('activations', static function (array $rows) use ($actKey, $c, $mk): array {
                $rows[$actKey] = ['class_id' => $c, 'label' => $mk . ' Aktivace', 'code' => 'MKCODE' . strtoupper(substr($c, -2)), 'used_at' => null, 'used_email' => ''];
                return $rows;
            });
        });
        $ids['intake_activation'][$k] = $actKey;
        v59sf_try($errors, 'task ' . $k, static function () use ($c, $key, $mk, $tomorrow, $now): void {
            v59sf_push(basename(teacher_tasks_path()), ['id' => 'task_mk' . strtolower(substr($c, -2)), 'class_id' => $c, 'student_key' => $key, 'title' => $mk . ' úkol', 'status' => 'assigned',
                'due_at' => $tomorrow, 'priority' => 'normal', 'created_at' => date(DATE_ATOM, $now), 'updated_at' => date(DATE_ATOM, $now)]);
        });
        v59sf_try($errors, 'extra ' . $k, static function () use ($c, $mk, $now): void {
            storage_append('extra_submissions', ['id' => 'ex_' . $c, 'class_id' => $c, 'student_label' => $mk . ' Extra', 'status' => 'submitted', 'submitted_at' => date(DATE_ATOM, $now),
                'challenge_title' => $mk . ' výzva', 'max_points' => 25]);
        });
    }
    $ids['ctf_event']['mix'] = v59sf_try($errors, 'ctf mix', static fn() => (string)arena58_ctf_create_event(['class_ids' => ['class_2a', 'class_3a'], 'title' => 'MK3A CTF smíšená'], ['class_2a', 'class_3a'], $now)['id']);
    v59sf_try($errors, 'kalendář', static function () use ($now): void {
        adaptive_store_update('calendar_exceptions', static function (array $rows) use ($now): array {
            $rows[] = ['id' => 'cal_3a', 'class_id' => 'class_3a', 'date' => '2026-10-07', 'type' => 'trip', 'title' => 'Exkurze CAL3A', 'created_at' => date(DATE_ATOM, $now)];
            return array_values($rows);
        });
    });
    v59sf_try($errors, 'banka', static function () use ($now): void {
        tg58_update(tg58_bank_path(), static function (array $d) use ($now): array {
            $items = array_values(array_filter((array)($d['items'] ?? []), 'is_array'));
            foreach (['networks' => 'MKNET', 'graphics' => 'MK2A grafika', 'both' => 'MKBOTH'] as $line => $mk) {
                $items[] = ['id' => 'q_mk_' . $line, 'prompt' => $mk . ' otázka', 'category' => 'Audit', 'line' => $line, 'difficulty' => 1, 'type' => 'text', 'options' => [], 'answer' => 'ano', 'explain' => '', 'created_at' => tg58_iso($now)];
            }
            $d['items'] = $items;
            return $d;
        });
    });
    $ids['tg58_bank'] = ['networks' => 'q_mk_networks', 'graphics' => 'q_mk_graphics', 'both' => 'q_mk_both'];

    $missing = [];
    foreach (['race', 'match', 'tg58_game', 'ctf_event', 'inc_session', 'sess53', 'lab58e_level', 'demo_account', 'intervention', 'skill_evidence', 'skill_assignment',
        'peer_feedback', 'ml_live', 'ml_scenario', 'ml_tip', 'group', 'intake_response', 'intake_activation', 'mkt60_item',
        'proj60_project', 'proj60_app', 'fb60_report'] as $type) {
        if (($ids[$type]['3a'] ?? null) === null || teacher59_entity_classes($type, (string)$ids[$type]['3a']) !== ['class_3a']) $missing[] = $type;
    }
    return ['ids' => $ids, 'missing' => $missing, 'errors' => $errors, 'student_3a' => $student3a, 'student_2a' => $student2a, 'label_3a_only' => $label3a];
}

/** Účty: admin, A (1.A + 2.A grafika), B (3.A sítě + 4.A), asistent (3.A), E (bez tříd), F (čerstvé OTP). */
function v59sf_create_accounts(): array
{
    $spec = [
        'admin' => ['ada.admin', 'Ada Správcová', 'admin', []],
        'a' => ['alena.ucitelova', 'Alena Učitelová', 'teacher', [['class_id' => 'class_1a', 'subject_id' => '*'], ['class_id' => 'class_2a', 'subject_id' => 'graphics']]],
        'b' => ['bohdan.sitar', 'Bohdan Sítař', 'teacher', [['class_id' => 'class_3a', 'subject_id' => 'networks'], ['class_id' => 'class_4a', 'subject_id' => '*']]],
        's' => ['sona.asistentka', 'Soňa Asistentka', 'assistant', [['class_id' => 'class_3a', 'subject_id' => '*']]],
        'e' => ['emil.bezdrid', 'Emil Beztřídní', 'teacher', []],
        'f' => ['filip.novy', 'Filip Nový', 'teacher', [['class_id' => 'class_2a', 'subject_id' => '*']]],
    ];
    $out = ['id' => [], 'login' => [], 'pw' => V59SF_PASSWORDS, 'otp' => []];
    foreach ($spec as $who => [$login, $name, $role, $assign]) {
        $created = teacher59_account_create(['login' => $login, 'display_name' => $name, 'role' => $role, 'assignments' => $assign], 'cli');
        $id = (string)$created['account']['id'];
        $out['id'][$who] = $id;
        $out['login'][$who] = $login;
        $out['otp'][$who] = (string)$created['otp'];
        if ($who === 'f') continue;
        $hash = local_password_hash(V59SF_PASSWORDS[$who]);
        teacher59_account_update($id, static function (array $account) use ($hash): array {
            unset($account['otp']);
            return array_replace($account, ['password_hash' => $hash, 'must_change_password' => false, 'password_changed_at' => date(DATE_ATOM)]);
        });
    }
    teacher59_reset_cache();
    return $out;
}

/** Řádky s vlastníkem (v3 klíče účtů): týmové řádky B ve 3.A, vlastní řádky A ve 2.A i „zbylé“ řádky A ve 3.A. */
function v59sf_owner_rows(array $fx, array $acc): array
{
    $keyA = teacher59_owner_key_for($acc['id']['a']);
    $keyB = teacher59_owner_key_for($acc['id']['b']);
    $team = teacher_team_key();
    $now = date(DATE_ATOM);
    $tomorrow = date('Y-m-d', time() + 86400);
    $s3 = $fx['student_3a'];
    $s2 = $fx['student_2a'];
    $filter = static fn(string $id, string $c, string $owner, string $scope, string $name): array => ['id' => $id, 'owner_key' => $owner, 'owner_version' => 3, 'owner_label' => 'Audit',
        'scope' => $scope, 'team_key' => $scope === 'team' ? $team : '', 'name' => $name, 'class_id' => $c, 'status' => 'all', 'priority' => 'all', 'task_status' => 'all', 'task_due' => 'all',
        'rule_mode' => 'all', 'rules' => [], 'pinned' => true, 'default_for' => 'none', 'created_at' => $now, 'updated_at' => $now];
    v59sf_push('teacher_saved_filters.json.php', $filter('sf_mk3a', 'class_3a', $keyB, 'team', 'MK3A filtr'));
    v59sf_push('teacher_saved_filters.json.php', $filter('sf_mk3a_a', 'class_3a', $keyA, 'personal', 'MK3A starý filtr A'));
    v59sf_push('teacher_saved_filters.json.php', $filter('sf_mk2a', 'class_2a', $keyA, 'personal', 'MK2A filtr'));
    $fx['ids']['saved_filter'] = ['3a' => 'sf_mk3a', '2a' => 'sf_mk2a'];
    $followup = static fn(string $id, string $c, string $owner, string $scope, string $title, string $student): array => ['id' => $id, 'class_id' => $c, 'student_key' => $student, 'title' => $title,
        'detail' => '', 'due_at' => $tomorrow, 'priority' => 'high', 'status' => 'open', 'scope' => $scope, 'team_key' => $scope === 'team' ? $team : '', 'owner_key' => $owner,
        'owner_label' => 'Audit', 'created_at' => $now, 'updated_at' => $now, 'completed_at' => null];
    v59sf_push('teacher_followups.json.php', $followup('fu_mk3a', 'class_3a', $keyB, 'team', 'MK3A follow-up', $s3));
    v59sf_push('teacher_followups.json.php', $followup('fu_mk3a_a', 'class_3a', $keyA, 'personal', 'MK3A starý follow-up A', $s3));
    v59sf_push('teacher_followups.json.php', $followup('fu_mk2a', 'class_2a', $keyA, 'personal', 'MK2A follow-up', $s2));
    $fx['ids']['followup'] = ['3a' => 'fu_mk3a', '2a' => 'fu_mk2a'];
    $watch = static fn(string $id, string $c, string $owner, string $scope, string $student, string $reason): array => ['id' => $id, 'class_id' => $c, 'student_key' => $student, 'scope' => $scope,
        'team_key' => $scope === 'team' ? $team : '', 'reason' => $reason, 'priority' => 'high', 'owner_key' => $owner, 'owner_label' => 'Audit', 'created_at' => $now, 'updated_at' => $now];
    v59sf_push('teacher_watchlist.json.php', $watch('wl_mk3a', 'class_3a', $keyB, 'team', $s3, 'MK3A watch'));
    v59sf_push('teacher_watchlist.json.php', $watch('wl_mk2a', 'class_2a', $keyA, 'personal', $s2, 'MK2A watch'));
    $fx['ids']['watchlist'] = ['3a' => 'wl_mk3a', '2a' => 'wl_mk2a'];
    $undo = static fn(string $id, string $c, string $owner, string $summary): array => ['id' => $id, 'class_id' => $c, 'summary' => $summary, 'ops' => [], 'owner_key' => $owner, 'owner_label' => 'Audit',
        'team_key' => $team, 'created_at' => $now, 'expires_at' => date(DATE_ATOM, time() + 3600), 'used_at' => null];
    v59sf_push('teacher_bulk_undo.json.php', $undo('un_mk3a', 'class_3a', $keyA, 'MK3A undo'));
    v59sf_push('teacher_bulk_undo.json.php', $undo('un_mk2a', 'class_2a', $keyA, 'MK2A undo'));
    $fx['ids']['undo'] = ['3a' => 'un_mk3a', '2a' => 'un_mk2a'];
    foreach ([['oa_mk3a', 'class_3a', 'MK3A audit'], ['oa_mk2a', 'class_2a', 'MK2A audit']] as [$id, $c, $summary]) {
        v59sf_push('teacher_ops_audit.json.php', ['id' => $id, 'action' => 'audit.fixture', 'entity_type' => 'fixture', 'entity_id' => $id, 'class_id' => $c, 'summary' => $summary, 'meta' => [],
            'owner_key' => $keyA, 'owner_label' => 'Alena Učitelová', 'role' => 'teacher', 'team_key' => $team, 'created_at' => $now]);
    }
    return $fx;
}

/** Nastaví cookie jar dané role a vrátí harness (pro audit_login_teacher_account). */
function v59sf_harness_as(object $h, Closure $jarSwap, array $jars, string $who): object
{
    $jarSwap($jars[$who] ?? []);
    return $h;
}

/** @return array<string,string> relativní cesta => xxh128 (bez nestálých souborů: limity, log a soubor účtů, tým) */
function v59sf_snapshot(string $dir): array
{
    clearstatcache();
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($dir) + 1));
        if (preg_match(V59SF_VOLATILE_RE, $rel)) continue;
        $out[$rel] = (string)hash_file('xxh128', $file->getPathname());
    }
    ksort($out);
    return $out;
}

function v59sf_same(array $a, array $b): bool
{
    return $a === $b;
}

function v59sf_diff_note(array $a, array $b): string
{
    $changed = array_keys(array_diff_assoc($b, $a) + array_diff_key($a, $b));
    return $changed === [] ? '' : ' (změněno: ' . implode(', ', array_slice($changed, 0, 3)) . ')';
}

/** Doplňková pole, aby požadavek prošel validací formuláře (guard běží před nimi, tady jen pro realističnost). */
function v59sf_post_defaults(string $action, string $classId): array
{
    return match ($action) {
        'teacher_ops_report_export' => ['report_format' => 'json'],
        'fb60_decide' => ['decision' => 'reject'],
        'tg58_bank_add' => ['line' => 'graphics', 'prompt' => 'Audit otázka', 'answer' => 'ano'],
        'teacher_saved_filter_save' => ['filter_name' => 'Audit filtr'],
        'teacher_followup_create' => ['followup_title' => 'Audit follow-up'],
        default => [],
    };
}

/** Kdo vlastní záložku (pro označení díry v souboru mimo builder). */
function v59sf_owner_note(string $tab, array $leak): string
{
    if ($leak === []) return '';
    $owners = [
        'arena' => 'arena_v57_views.php / arena_v58_weekly.php', 'roboti' => 'robots_v58_views.php (i18n)', 'ctf' => 'arena_v58_events_views.php (i18n)',
        'incidenty' => 'arena_v58_events_views.php (i18n)', 'hry' => 'teamgames_v58_teacher_views.php', 'editor' => 'lab_v58_editor_views.php', 'labdata' => 'lab_v58_teacher_views.php',
        'pristupy' => 'accounts_v58_views.php', 'intake' => 'intake_v51_teacher.php', 'session' => 'session_v53_teacher.php', 'calendar' => 'teacher_calendar.php',
        'overview' => 'teacher_overview_dashboard.php / teacher.php', 'demo_accounts' => 'teacher_demo_accounts.php', 'filters' => 'teacher_operations_views_v46.php / teacher_tasks.php',
        'ops_audit' => 'teacher_operations_plus_views_v46_1.php', 'control' => 'teacher_operations_control_views_v46_2.php', 'reports' => 'teacher_operations_control_views_v46_2.php',
        'communications' => 'teacher_operations_control_views_v46_2.php', 'attention' => 'teacher_operations_views_v46.php', 'workspace' => 'teacher_project_workspace_views.php',
        'skills' => 'teacher_skill_views.php', 'mastery' => 'mastery_learning_views_v41.php', 'authoring' => 'mastery_learning_views_v41.php', 'teach' => 'teacher.php (teach)',
        'identita' => 'identity_v58_views.php', 'provoz' => 'ops_v58_views.php',
    ];
    return ' – ' . implode(', ', $leak) . ' [DÍRA → ' . ($owners[$tab] ?? 'teacher.php') . ']';
}

/** Akce nad 3.A, které B (a Admin) musí guardem projít (ne-destruktivní; výsledek řeší modul). */
function v59sf_allowed_posts(array $id): array
{
    $c3 = ['class_id' => 'class_3a'];
    return [
        ['b', 'teacher_bulk_mark', $c3 + ['student_keys' => [], 'marker' => 'none']],
        ['b', 'teacher_followup_update', ['followup_id' => $id['followup']['3a'], 'followup_mode' => 'tomorrow']],
        ['b', 'teacher_saved_filter_pin', ['filter_id' => $id['saved_filter']['3a']]],
        ['b', 'arena57_extend', $c3 + ['race' => $id['race']['3a']]],
        ['b', 'robots58_close', $c3 + ['match' => $id['match']['3a']]],
        ['b', 'tg58_pause', $c3 + ['game' => $id['tg58_game']['3a']]],
        ['b', 'arena58_inc_extend', $c3 + ['session' => $id['inc_session']['3a']]],
        ['b', 'arena58_ctf_extend', ['event' => $id['ctf_event']['3a']]],
        ['b', 'sess53_t_instructions', ['session' => $id['sess53']['3a'], 'instructions' => 'Pokyny auditu']],
        ['b', 'teacher_intervention_note', ['intervention_id' => $id['intervention']['3a'], 'intervention_note' => 'Poznámka']],
        ['b', 'skill_validation', ['evidence_id' => $id['skill_evidence']['3a'], 'decision' => 'approve']],
        ['b', 'ml_tip_state', ['tip_id' => $id['ml_tip']['3a'], 'state' => 'hidden']],
        ['b', 'lab58e_check', ['id' => $id['lab58e_level']['3a']]],
        ['b', 'intake_t_regen', $c3 + ['student_key' => $id['intake_activation']['3a']]],
        ['b', 'teacher_demo_account_reset', ['demo_email' => $id['demo_account']['3a']]],
        ['b', 'tg58_bank_delete', $c3 + ['bank_id' => $id['tg58_bank']['networks']]],
        ['b', 'project_teacher_peer_moderate', ['feedback_id' => $id['peer_feedback']['3a'], 'moderation' => 'hide']],
        ['admin', 'arena58_ctf_extend', ['event' => $id['ctf_event']['mix']]],
        ['admin', 'intake_t_toggle', ['class_id' => 'class_4a']],
        ['admin', 'teacher_sla_policy_save', ['sla_task_high' => '3']],
        ['admin', 'tg58_bank_delete', ['class_id' => 'class_2a', 'bank_id' => $id['tg58_bank']['both']]],
    ];
}

/** Statické pokrytí: akce z teacher.php a handlerů mají politiku, GET parametry registru také, moduly mají guard. */
function v59sf_static_coverage(string $root): array
{
    $read = static fn(string $path): string => (string)@file_get_contents($path);
    $actions = [];
    $collect = static function (string $code) use (&$actions): void {
        foreach (['/\$action\s*===\s*\'([a-z0-9_]+)\'/', '/\$action\s*!==\s*\'([a-z0-9_]+)\'/', '/case\s+\'([a-z0-9_]+)\'\s*:/'] as $re) {
            preg_match_all($re, $code, $m);
            foreach ($m[1] as $a) $actions[$a] = true;
        }
        preg_match_all('/in_array\(\$action\s*,\s*\[([^\]]*)\]/', $code, $m);
        foreach ($m[1] as $list) { preg_match_all('/\'([a-z0-9_]+)\'/', $list, $mm); foreach ($mm[1] as $a) $actions[$a] = true; }
    };
    $teacherSrc = $read($root . '/teacher.php');
    $postBlock = (string)strstr($teacherSrc, "if (\$action === 'teacher_login')");
    $collect(substr($postBlock, 0, max(0, (int)strpos($postBlock, '$rawFlash'))));
    foreach (['intake_v51_teacher.php' => 'intake_v51_teacher_handle_post', 'session_v53_teacher.php' => 'sess53_teacher_handle_post', 'arena_v57.php' => 'arena57_teacher_handle_post',
        'accounts_v58_views.php' => 'acc58_teacher_apply', 'arena_v58_weekly.php' => 'arena58_weekly_teacher_handle_post', 'robots_v58.php' => 'robots58_teacher_handle_post',
        'teamgames_v58_teacher_views.php' => 'tg58_teacher_handle_post', 'arena_v58_ctf.php' => 'arena58_ctf_teacher_handle_post', 'arena_v58_incident.php' => 'arena58_inc_teacher_handle_post',
        'lab_v58_teacher.php' => 'lab58t_teacher_handle_post', 'lab_v58_editor.php' => 'lab58e_teacher_handle_post', 'identity_v58_views.php' => 'identity58_teacher_handle_post'] as $file => $fn) {
        $body = (string)strstr($read($root . '/' . $file), 'function ' . $fn . '(');
        $next = strpos($body, "\nfunction ", 10);
        $collect($next === false ? $body : substr($body, 0, $next));
    }
    unset($actions['teacher_login'], $actions['teacher_logout']);
    $denied = array_values(array_filter(array_keys($actions), static fn(string $a): bool => !empty(teacher59_action_policy($a)['deny'])));
    $getMissing = [];
    foreach (teacher58_modules() as $tab => $mod) {
        foreach (array_keys((array)($mod['get'] ?? [])) as $param) if (!isset(teacher59_get_policies()[$tab . '|' . $param])) $getMissing[] = $tab . '|' . $param;
    }
    $guardsMissing = [];
    foreach (['teacher_operations_plus_v46_1.php', 'teacher_operations_control_v46_2.php', 'teacher_operations_plus_views_v46_1.php', 'teacher_operations_views_v46.php', 'teacher_tasks.php',
        'teacher_class_dashboard.php', 'teacher_overview_dashboard.php', 'teacher_calendar.php', 'accounts_v58_views.php', 'lab_v58_teacher.php', 'intake_v51_teacher.php',
        'session_v53_teacher.php', 'teacher_demo_accounts.php', 'lab_v58_editor.php', 'lab_v58_editor_views.php', 'export_extra_csv.php', 'teamgames_v58_teacher_views.php'] as $file) {
        $src = $read($root . '/' . $file);
        if (!preg_match("/function_exists\('teacher59_(can_class|can_classes|can_subject|is_admin|owner_key|filter_class_map|mode)'\)/", $src)) $guardsMissing[] = $file;
    }
    return ['count' => count($actions), 'denied' => $denied, 'get_missing' => $getMissing, 'guards_missing' => $guardsMissing];
}
