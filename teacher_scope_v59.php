<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v59 · AUTHZ58-07 – centrální rozsah učitele (třídy a předměty) a vynucení na vstupu teacher.php.
 *
 * Pravidlo: data vázaná na třídu vidí a mění jen učitel přiřazený ke třídě; sdílený obsah vázaný na předmět
 * (banka otázek tg58, editor úrovní) jen s daným předmětem; admin vše. V režimu legacy (bez souboru účtů)
 * jsou všechny guardy no-op a rozsah = všechny třídy (chování jako v58).
 *
 * Vynucení:
 *   teacher59_guard_post($action) – deny-by-default tabulka teacher59_action_policy() (přesné názvy → prefixy),
 *     kontrola class_id / class_ids[] / classes[] a tříd entity z požadavku (chybějící i cizí entita = 403).
 *   teacher59_guard_get($tab) – admin záložky, zvláštní GET (projektor, polling, tisk, export, artefakt),
 *     entity z parametrů (race, match, game, event, session, level, …) a neznámé GET parametry registru v58.
 *   Moduly, které agregují data více tříd, se mohou hlásit teacher59_declare_filtered('<značka>'); politika 'filter_marker'
 *   pak jejich zvláštní GET pustí i neadminům. Týdenní hádanka značku nepotřebuje – svá data filtruje sama (SEC59-10).
 *   GET entity se seznamem parametrů (např. hry|projektor: game|projektor) ověří KAŽDÝ přítomný parametr (SEC59-02).
 */

const TEACHER59_ADMIN_TABS = ['ucitele', 'identita', 'provoz', 'quality'];
const TEACHER59_SUBJECT_LABELS = ['graphics' => 'Grafika a webdesign', 'networks' => 'OS a sítě'];
const TEACHER59_JSON_GET_PARAMS = ['arena_poll', 'hadanka_poll', 'robots_poll', 'tg_poll', 'dohled_poll', 'v48_state'];

// ---------------------------------------------------------------------------
// Třídy, předměty, rozsah
// ---------------------------------------------------------------------------

/** Neomezená kopie modulů (zapamatuje ji první volání teacher59_scope_modules()). */
function teacher59_all_modules(?array $remember = null): array
{
    static $all = null;
    if ($remember !== null && $all === null) $all = $remember;
    if ($all !== null) return $all;
    $global = $GLOBALS['modules'] ?? null;
    return is_array($global) ? $global : [];
}

/** @return list<string> */
function teacher59_all_class_ids(): array
{
    $ids = array_map('strval', array_keys(teacher59_all_modules()));
    if ($ids === [] && function_exists('project_catalog')) $ids = array_map('strval', array_keys(project_catalog()));
    return $ids;
}

/** Kanonický předmět třídy: graphics | networks (subject_family), '' = neznámá třída. */
function teacher59_subject_of_class(string $classId): string
{
    $module = teacher59_all_modules()[$classId] ?? null;
    return is_array($module) ? subject_family($module) : '';
}

function teacher59_subject_label(string $subject): string
{
    return TEACHER59_SUBJECT_LABELS[$subject] ?? ($subject === '*' ? 'Všechny předměty třídy' : $subject);
}

function teacher59_is_admin(): bool
{
    if (teacher59_mode() === 'legacy') return true;
    $account = teacher59_current();
    return $account !== null && teacher59_session_state() === 'ok' && (string)($account['role'] ?? '') === 'admin';
}

/** @return array{all:bool, classes:array<string, list<string>>, subjects:list<string>} */
function teacher59_scope(): array
{
    if (teacher59_mode() === 'legacy') return ['all' => true, 'classes' => [], 'subjects' => []];
    $account = teacher59_current();
    if ($account === null || teacher59_session_state() !== 'ok') return ['all' => false, 'classes' => [], 'subjects' => []];
    return teacher59_account_scope($account);
}

/** Rozsah libovolného účtu bez relace (cron automatizací, převzetí dat); stav účtu neřeší. */
function teacher59_account_scope(array $account): array
{
    if ((string)($account['role'] ?? '') === 'admin') return ['all' => true, 'classes' => [], 'subjects' => []];
    $universe = teacher59_all_class_ids();
    $classes = [];
    $subjects = [];
    foreach ((array)($account['assignments'] ?? []) as $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $subjectId = (string)($row['subject_id'] ?? '*');
        $classSubject = teacher59_subject_of_class($classId);
        if (!in_array($classId, $universe, true) || $classSubject === '') continue;
        if ($subjectId !== '*' && $subjectId !== $classSubject) continue; // nesouhlasí s třídou → nic (fail closed)
        $classes[$classId][] = $classSubject;
        $subjects[$classSubject] = true;
    }
    return ['all' => false, 'classes' => $classes, 'subjects' => array_keys($subjects)];
}

/** @return list<string> povolené třídy (admin/legacy = všechny) */
function teacher59_allowed_class_ids(): array
{
    $scope = teacher59_scope();
    if ($scope['all']) return teacher59_all_class_ids();
    return array_values(array_filter(teacher59_all_class_ids(), static fn(string $c): bool => isset($scope['classes'][$c])));
}

function teacher59_can_class(string $classId): bool
{
    $scope = teacher59_scope();
    if ($scope['all']) return true;
    return $classId !== '' && isset($scope['classes'][$classId]);
}

function teacher59_account_can_class(array $account, string $classId): bool
{
    $scope = teacher59_account_scope($account);
    return $scope['all'] || ($classId !== '' && isset($scope['classes'][$classId]));
}

/** Třída řádku učitelských dat: class_id, jinak ?class= z uloženého odkazu (upozornění před v59); '' = bez třídy. */
function teacher59_row_class(array $row): string
{
    $classId = $row['class_id'] ?? null;
    if (is_string($classId) && $classId !== '') return $classId;
    $query = is_string($row['url'] ?? null) ? (string)parse_url((string)$row['url'], PHP_URL_QUERY) : '';
    if ($query === '') return '';
    parse_str($query, $params);
    return is_string($params['class'] ?? null) ? (string)$params['class'] : '';
}

/** Všechny třídy v rozsahu; prázdný seznam = jen admin (entita bez třídy je jinak nedostupná). */
function teacher59_can_classes(array $classIds): bool
{
    if (teacher59_scope()['all']) return true;
    if ($classIds === []) return false;
    foreach ($classIds as $classId) {
        if (!teacher59_can_class((string)$classId)) return false;
    }
    return true;
}

/** graphics | networks | both (obojí) | * (vše) */
function teacher59_can_subject(string $subject): bool
{
    $scope = teacher59_scope();
    if ($scope['all']) return true;
    if ($subject === 'both' || $subject === '*') return in_array('graphics', $scope['subjects'], true) && in_array('networks', $scope['subjects'], true);
    return in_array($subject, $scope['subjects'], true);
}

function teacher59_classes_ok(array $classIds, string $mode = 'all'): bool
{
    if ($mode !== 'any') return teacher59_can_classes($classIds);
    if (teacher59_scope()['all']) return true;
    foreach ($classIds as $classId) {
        if (teacher59_can_class((string)$classId)) return true;
    }
    return false;
}

function teacher59_assert_class(string $classId, ?bool $json = null): void
{
    if (!teacher59_can_class($classId)) teacher59_deny('class_out_of_scope', $json);
}

/** Omezí $modules na povolené třídy (legacy beze změny) a zapamatuje si neomezenou kopii. */
function teacher59_scope_modules(array $modules): array
{
    teacher59_all_modules($modules);
    if (teacher59_mode() === 'legacy') return $modules;
    return array_filter($modules, static fn($classId): bool => teacher59_can_class((string)$classId), ARRAY_FILTER_USE_KEY);
}

/** Mapa klíčovaná id třídy → jen povolené třídy. */
function teacher59_filter_class_map(array $map): array
{
    if (teacher59_mode() === 'legacy') return $map;
    return array_filter($map, static fn($classId): bool => teacher59_can_class((string)$classId), ARRAY_FILTER_USE_KEY);
}

/** Výchozí třída: požadovaná (je-li povolená), jinak class_2a (je-li povolená), jinak první povolená; '' = žádná. */
function teacher59_default_class(string $preferred = '', ?array $universe = null): string
{
    $universe = array_values(array_map('strval', $universe ?? teacher59_all_class_ids()));
    $allowed = array_values(array_filter($universe, 'teacher59_can_class'));
    if ($preferred !== '' && in_array($preferred, $allowed, true)) return $preferred;
    if (in_array('class_2a', $allowed, true)) return 'class_2a';
    return (string)($allowed[0] ?? '');
}

/** Třída pro přesměrování po chybě POST (nikdy nepovolená). */
function teacher59_redirect_class(string $posted): string
{
    return teacher59_default_class($posted);
}

/** Klíč vlastníka v3 (id účtu) pro teacher_tasks.php; null = použij dosavadní v2 klíč (legacy). */
function teacher59_owner_key(): ?string
{
    if (teacher59_mode() === 'legacy') return null;
    $account = teacher59_current();
    return ($account !== null && teacher59_session_state() === 'ok') ? teacher59_owner_key_for((string)$account['id']) : null;
}

/** Role pro oprávnění v46 (admin → *, teacher, assistant); null = legacy (role z týmu/secretu). */
function teacher59_role_for_v46(): ?string
{
    if (teacher59_mode() === 'legacy') return null;
    $account = teacher59_current();
    if ($account === null || teacher59_session_state() !== 'ok') return 'assistant';
    $role = (string)($account['role'] ?? 'assistant');
    return in_array($role, TEACHER59_ROLES, true) ? $role : 'assistant';
}

/** Modul, který agregovaná data sám omezuje rozsahem, se ohlásí značkou (odemkne jeho GET i neadminům). */
function teacher59_declare_filtered(string $marker): void
{
    $GLOBALS['teacher59_filtered_modules'][$marker] = true;
}

function teacher59_module_filtered(string $marker): bool
{
    return !empty($GLOBALS['teacher59_filtered_modules'][$marker]);
}

// ---------------------------------------------------------------------------
// Tabulka politik POST akcí (deny-by-default)
// ---------------------------------------------------------------------------

/**
 * Záznam: class required|optional (class_id/class_ids[]/classes[] se ověří vždy, když jsou v požadavku),
 * entity [parametr, typ], entity_required, mode all|any, admin, self, legacy_only, subject_param, entity_subject,
 * class_all_ok (hodnota class_id=all je přípustná).
 * @return array<string, array<string, mixed>>
 */
function teacher59_action_policies(): array
{
    static $table = null;
    if ($table !== null) return $table;
    $req = ['class' => 'required'];
    $ent = static fn(string $param, string $type, bool $required = true, string $mode = 'all'): array => ['class' => 'optional', 'entity' => [$param, $type], 'entity_required' => $required, 'mode' => $mode];
    $reqEnt = static fn(string $param, string $type, bool $required = true): array => ['class' => 'required', 'entity' => [$param, $type], 'entity_required' => $required, 'mode' => 'all'];
    $none = ['class' => 'optional'];
    $admin = ['class' => 'optional', 'admin' => true];
    $table = [];
    foreach (['teacher_saved_filter_save', 'teacher_bulk_mark', 'teacher_bulk_export', 'teacher_bulk_assign_task', 'teacher_note_add',
        'teacher_intervention_create', 'teacher_bulk_task_due', 'teacher_bulk_task_priority', 'teacher_bulk_remind', 'teacher_bulk_note',
        'teacher_bulk_resource', 'teacher_bulk_intervention', 'teacher_watchlist_save', 'teacher_followup_create', 'v48_orchestrate',
        'skill_teacher_evidence', 'skill_assign', 'teacher_curriculum_toggle', 'teacher_curriculum_lesson_toggle', 'teacher_curriculum_note',
        'v42_lesson_resource_save', 'v42_lesson_resource_remove', 'teacher_calendar_exception_save', 'teacher_calendar_exception_remove',
        'ml_live_start', 'ml_failure_inject', 'teacher_demo_account_create', 'v50_teacher_growth_control', 'intake_t_toggle', 'intake_t_save',
        'sess53_t_open', 'acc58_issue_one', 'acc58_issue_class', 'arena57_create', 'arena57_lab_toggle', 'robots58_create', 'tg58_create',
        'arena58_inc_create', 'lab58t_export',
        'comp62_sync', 'p63_assign', 'p63_unassign', 'v64_abs_board',
        // v65 · cyklus projektů: třída formuláře povinná a v rozsahu; handler navíc ověří, že záznam patří do POSTnuté třídy (kontrola po položkách u hromadného schválení)
        'proj65_t_settings', 'proj65_t_bulk_approve', 'proj65_t_reject', 'proj65_t_peer_open', 'proj65_t_moderate', 'proj65_t_apply_factor', 'proj65_t_rubric_clone', 'proj65_t_grade'] as $action) { // v62 · kompetence: ruční přepočet důkazů třídy – třída v rozsahu povinná; jiné comp62_* akce zůstávají zakázané; v63 · p63_assign/p63_unassign (výukové cesty) – třída povinná, jiné p63_* zakázané
        $table[$action] = $req;
    }
    $table['save_grade'] = $reqEnt('target_id', 'grade_target', false);
    $table['save_group'] = $reqEnt('group_id', 'group', false);
    foreach (['project_teacher_role_assign', 'project_teacher_role_remove', 'project_teacher_role_evaluation'] as $action) $table[$action] = $reqEnt('group_id', 'group', false);
    $table['ml_scenario_save'] = $reqEnt('id', 'ml_scenario', false);
    $table['intake_t_delete'] = $reqEnt('response_id', 'intake_response');
    $table['intake_t_regen'] = $reqEnt('student_key', 'intake_activation');
    foreach (['arena57_start', 'arena57_stop', 'arena57_extend', 'arena57_delete'] as $action) $table[$action] = $reqEnt('race', 'race');
    foreach (['robots58_close', 'robots58_run', 'robots58_delete'] as $action) $table[$action] = $reqEnt('match', 'match');
    foreach (['tg58_start', 'tg58_pause', 'tg58_resume', 'tg58_end', 'tg58_archive', 'tg58_delete'] as $action) $table[$action] = $reqEnt('game', 'tg58_game');
    foreach (['arena58_inc_start', 'arena58_inc_stop', 'arena58_inc_extend', 'arena58_inc_delete', 'arena58_inc_comment'] as $action) $table[$action] = $reqEnt('session', 'inc_session');
    $table['tg58_bank_add'] = $req + ['subject_param' => 'line'];
    $table['tg58_bank_delete'] = $none + ['entity_subject' => ['bank_id', 'tg58_bank']];
    $table['arena58_ctf_create'] = $req;
    foreach (['arena58_ctf_start', 'arena58_ctf_stop', 'arena58_ctf_extend', 'arena58_ctf_delete'] as $action) $table[$action] = $ent('event', 'ctf_event');
    $table['lab58e_save'] = ['class' => 'required', 'class_params' => ['classes'], 'entity' => ['id', 'lab58e_level'], 'entity_required' => false, 'mode' => 'all'];
    foreach (['lab58e_check', 'lab58e_publish', 'lab58e_unpublish', 'lab58e_delete'] as $action) $table[$action] = $ent('id', 'lab58e_level');
    $table['delete_group'] = $ent('group_id', 'group');
    $table['project_teacher_retro'] = $ent('group_id', 'group');
    $table['project_teacher_peer_moderate'] = $ent('feedback_id', 'peer_feedback');
    foreach (['teacher_saved_filter_delete', 'teacher_saved_filter_pin', 'teacher_saved_filter_default', 'teacher_saved_filter_watch', 'teacher_automation_save'] as $action) $table[$action] = $ent('filter_id', 'saved_filter');
    foreach (['teacher_intervention_step', 'teacher_intervention_status', 'teacher_intervention_note'] as $action) $table[$action] = $ent('intervention_id', 'intervention');
    $table['teacher_watchlist_delete'] = $ent('watchlist_id', 'watchlist');
    $table['teacher_followup_update'] = $ent('followup_id', 'followup');
    $table['teacher_bulk_undo'] = $ent('undo_id', 'undo');
    $table['skill_validation'] = $ent('evidence_id', 'skill_evidence');
    $table['skill_unassign'] = $ent('assignment_id', 'skill_assignment');
    $table['ml_live_phase'] = $ent('live_id', 'ml_live');
    $table['ml_scenario_state'] = $ent('scenario_id', 'ml_scenario');
    $table['ml_tip_state'] = $ent('tip_id', 'ml_tip');
    foreach (['teacher_demo_account_reset', 'teacher_demo_account_delete'] as $action) $table[$action] = $ent('demo_email', 'demo_account');
    foreach (['sess53_t_toggle', 'sess53_t_instructions', 'sess53_t_grade', 'v55_t_bonus_grade'] as $action) $table[$action] = $ent('session', 'sess53');
    foreach (['teacher_automation_delete', 'teacher_automation_run', 'teacher_notification_read', 'teacher_notification_read_all',
        'teacher_template_save', 'teacher_template_delete', 'teacher_message_template_save', 'teacher_message_template_delete',
        'teacher_ops_report_export'] as $action) {
        $table[$action] = $none;
    }
    $table['teacher_review_ack'] = $none + ['class_all_ok' => true];
    foreach (['teacher_sla_policy_save', 'intake_t_sync', 'arena58_weekly_override', 'arena58_weekly_reroll', 'arena58_weekly_reveal', 'identity58_plan'] as $action) {
        $table[$action] = $admin;
    }
    $table['teacher_team_member_role'] = $none + ['legacy_only' => true];
    // v60 · obchod bodů: nová položka vyžaduje classes[] (rozsah), úpravy existující jdou přes entity.
    $table['mkt60_save'] = ['class' => 'optional', 'class_params' => ['classes'], 'entity' => ['id', 'mkt60_item'], 'entity_required' => false, 'mode' => 'all'];
    foreach (['mkt60_activate', 'mkt60_deactivate'] as $action) $table[$action] = $ent('id', 'mkt60_item');
    $table['mkt60_refund'] = ['class' => 'optional', 'entity' => ['student_key', 'student_key'], 'entity_required' => true, 'mode' => 'all'];
    // v60 · projekty podle levelu: nový projekt vyžaduje classes[] (rozsah), úpravy jdou přes entity;
    // rozhodnutí o přihlášce (proj60_decide) se ověřuje přes třídy projektu, ke kterému přihláška patří.
    $table['proj60_save'] = ['class' => 'optional', 'class_params' => ['classes'], 'entity' => ['id', 'proj60_project'], 'entity_required' => false, 'mode' => 'all'];
    $table['proj60_status'] = $ent('id', 'proj60_project');
    $table['proj60_decide'] = $ent('application_id', 'proj60_app');
    // v60 · hlášení chyb: rozhodnutí smí jen učitel s rozsahem třídy hlášení (entita fb60_report).
    $table['fb60_decide'] = $ent('id', 'fb60_report');
    // v61 · přehled třídy: hromadné potvrzení hlášení – třída formuláře povinná a v rozsahu; každé id (ids[]) si handler
    // ověří zvlášť přes teacher59_entity_classes('fb60_report', …) a výsledek vrací po položkách.
    $table['ov61_bulk_confirm'] = $req;
    return $table;
}

/** @return array<string, array<string, mixed>> prefixy (až po přesných názvech) */
function teacher59_action_policy_prefixes(): array
{
    return [
        'teacher59_admin_' => ['class' => 'optional', 'admin' => true],
        'teacher59_self_' => ['class' => 'optional', 'self' => true],
    ];
}

function teacher59_action_policy(string $action): array
{
    $exact = teacher59_action_policies();
    if (isset($exact[$action])) return $exact[$action];
    foreach (teacher59_action_policy_prefixes() as $prefix => $policy) {
        if (str_starts_with($action, $prefix)) return $policy;
    }
    return ['deny' => true];
}

// ---------------------------------------------------------------------------
// Entity → třídy (existující findery; chybějící i cizí entita vede ke stejné 403)
// ---------------------------------------------------------------------------

function teacher59_find_row(array $rows, string $id): ?array
{
    if (isset($rows[$id]) && is_array($rows[$id])) return $rows[$id];
    foreach ($rows as $row) {
        if (is_array($row) && (string)($row['id'] ?? '') === $id) return $row;
    }
    return null;
}

/** @return list<string> */
function teacher59_row_classes(?array $row): array
{
    if ($row === null) return [];
    $out = [];
    if (is_string($row['class_id'] ?? null)) $out[] = $row['class_id'];
    foreach (['class_ids', 'classes'] as $key) {
        foreach ((array)($row[$key] ?? []) as $classId) if (is_string($classId)) $out[] = $classId;
    }
    return $out;
}

function teacher59_student_key_class(string $key): string
{
    return preg_match('/^(class_[a-z0-9]+):student:/', $key, $m) === 1 ? $m[1] : '';
}

function teacher59_storage_rows(string $file): array
{
    return storage_read(STORAGE_DIR . '/' . $file, false);
}

/** @return list<string>|null null = entita neexistuje / nejde ověřit */
function teacher59_entity_resolve(string $type, string $id): ?array
{
    $call = static fn(string $fn, string $arg): ?array => function_exists($fn) ? $fn($arg) : null;
    $rowOf = static fn(string $file): ?array => teacher59_find_row(teacher59_storage_rows($file), $id);
    switch ($type) {
        case 'group': $row = $call('project_group_find', $id); break;
        case 'grade_target':
            $row = $call('project_group_find', $id);
            if ($row === null) { $class = teacher59_student_key_class($id); return $class !== '' ? [$class] : null; }
            break;
        case 'student_key': $class = teacher59_student_key_class($id); return $class !== '' ? [$class] : null;
        case 'grade_record':
            $row = function_exists('project_grade_indexes') ? (project_grade_indexes()['by_id'][$id] ?? null) : null;
            $row = is_array($row) ? $row : null;
            break;
        case 'intervention': $row = $call('teacher_ops_intervention_find', $id); break;
        case 'saved_filter': $row = $rowOf('teacher_saved_filters.json.php'); break;
        case 'watchlist': $row = $rowOf('teacher_watchlist.json.php'); break;
        case 'followup': $row = $rowOf('teacher_followups.json.php'); break;
        case 'undo': $row = $rowOf('teacher_bulk_undo.json.php'); break;
        case 'peer_feedback': $row = $rowOf('project_workspace_peer_feedback.json.php'); break;
        case 'skill_evidence': $row = $rowOf('skill_evidence.json.php'); break;
        case 'skill_assignment': $row = $rowOf('skill_assignments.json.php'); break;
        case 'ml_live': case 'ml_scenario': case 'ml_tip':
            $name = ['ml_live' => 'ml_live', 'ml_scenario' => 'ml_scenarios', 'ml_tip' => 'ml_class_tips'][$type];
            $rows = function_exists('adaptive_store') ? adaptive_store($name) : teacher59_storage_rows('adaptive_' . $name . '.json.php');
            $row = teacher59_find_row($rows, $id);
            break;
        case 'demo_account':
            $email = function_exists('local_email_normalize') ? local_email_normalize($id) : strtolower(trim($id));
            $row = local_accounts()[$email] ?? null;
            if (!is_array($row) || empty($row['is_test_account'])) return null;
            $binding = student_account_map()['local:' . (string)($row['id'] ?? '')] ?? null;
            if (!isset($row['class_id']) && is_array($binding)) $row['class_id'] = (string)($binding['class_id'] ?? '');
            break;
        case 'intake_response': $row = function_exists('intake_v51_responses') ? teacher59_find_row(intake_v51_responses(), $id) : null; break;
        case 'intake_activation':
            $row = function_exists('intake_v51_activations') ? (intake_v51_activations()[$id] ?? null) : null;
            $row = is_array($row) ? $row : null;
            break;
        case 'sess53': $row = $call('sess53_find', $id); break;
        case 'race': $row = $call('arena57_race', $id); break;
        case 'match': $row = $call('robots58_match', $id); break;
        case 'tg58_game': $row = $call('tg58_get', $id); break;
        case 'ctf_event': $row = $call('arena58_ctf_event', $id); break;
        case 'inc_session': $row = $call('arena58_inc_session', $id); break;
        case 'lab58e_level': $row = $call('lab58e_get', $id); break;
        // v60 · obchod bodů: položka katalogu má vlastní classes[]; nákup patří třídě rozpadlé z klíče peněženky.
        case 'mkt60_item':
            $row = function_exists('mkt60_item') ? mkt60_item($id) : null;
            break;
        // v60 · projekty podle levelu: projekt má vlastní classes[]; přihláška patří třídám svého projektu.
        case 'proj60_project':
            $row = function_exists('proj60_item') ? proj60_item($id) : null;
            break;
        // v60 · hlášení chyb a návrhů: patří třídě žáka, který ho poslal.
        case 'fb60_report':
            $row = function_exists('fb60_item') ? fb60_item($id) : null;
            break;
        case 'proj60_app':
            $app = function_exists('proj60_application') ? proj60_application($id) : null;
            if (!is_array($app)) return null;
            $project = function_exists('proj60_item') ? proj60_item((string)($app['project_id'] ?? '')) : null;
            $row = is_array($project) ? ['classes' => $project['classes'] ?? []] : null;
            break;
        default: return null;
    }
    return is_array($row) ? teacher59_row_classes($row) : null;
}

/** @return list<string>|null třídy entity; null = neexistuje nebo nejde ověřit (→ 403) */
function teacher59_entity_classes(string $type, string $id): ?array
{
    if ($id === '') return null;
    try {
        $classes = teacher59_entity_resolve($type, $id);
    } catch (Throwable $e) {
        error_log('EDUCANET v59 rozsah (' . $type . '): ' . $e->getMessage());
        return null;
    }
    if ($classes === null) return null;
    return array_values(array_unique(array_filter(array_map('strval', $classes), static fn(string $c): bool => $c !== '')));
}

/** Předmět sdíleného obsahu (banka otázek tg58: graphics | networks | both); null = neexistuje. */
function teacher59_entity_subject(string $type, string $id): ?string
{
    if ($type !== 'tg58_bank' || $id === '' || !function_exists('tg58_bank_path') || !function_exists('tg58_read')) return null;
    $item = teacher59_find_row((array)(tg58_read(tg58_bank_path())['items'] ?? []), $id);
    if ($item === null) return null;
    $line = (string)($item['line'] ?? '');
    return in_array($line, ['graphics', 'networks'], true) ? $line : 'both';
}

// ---------------------------------------------------------------------------
// Guardy
// ---------------------------------------------------------------------------

/** Třídy z POST (class_id, class_ids[], classes[]); null = neplatný tvar parametru. */
function teacher59_request_classes(array $input, array $policy): ?array
{
    $out = [];
    $classId = $input['class_id'] ?? null;
    if ($classId !== null) {
        if (!is_string($classId)) return null;
        if ($classId !== '' && !(!empty($policy['class_all_ok']) && $classId === 'all')) $out[] = $classId;
    }
    foreach (['class_ids', 'classes'] as $key) {
        if (!array_key_exists($key, $input)) continue;
        if (!is_array($input[$key])) return null;
        foreach ($input[$key] as $value) {
            if (!is_string($value)) return null;
            if ($value !== '') $out[] = $value;
        }
    }
    return array_values(array_unique($out));
}

/** Důvod zamítnutí POST akce, nebo null (povoleno). Čistá funkce – bez výstupu. */
function teacher59_guard_post_check(string $action, array $post): ?string
{
    if (teacher59_mode() === 'legacy') return null;
    if (teacher59_session_state() !== 'ok') return 'no_session';
    $policy = teacher59_action_policy($action);
    if (!empty($policy['deny'])) return 'unknown_action';
    if (!empty($policy['legacy_only'])) return 'legacy_only';
    $admin = teacher59_is_admin();
    if (!empty($policy['admin']) && !$admin) return 'admin_only';
    if ($admin || !empty($policy['self'])) return null;
    $classes = teacher59_request_classes($post, $policy);
    if ($classes === null) return 'bad_class_param';
    if (!empty($policy['class_params'])) {
        $own = teacher59_request_classes(array_intersect_key($post, array_flip((array)$policy['class_params'])), $policy);
        if ($own === null || $own === []) return 'missing_class';
    }
    if ($classes === [] && ($policy['class'] ?? 'optional') === 'required') return 'missing_class';
    foreach ($classes as $classId) {
        if (!teacher59_can_class($classId)) return 'class_out_of_scope';
    }
    if (isset($policy['entity'])) {
        [$param, $type] = $policy['entity'];
        $id = is_string($post[$param] ?? null) ? trim((string)$post[$param]) : '';
        if ($id === '' && !empty($policy['entity_required'])) return 'missing_entity';
        if ($id !== '') {
            $entityClasses = teacher59_entity_classes((string)$type, $id);
            if ($entityClasses === null) return 'entity_not_found';
            if (!teacher59_classes_ok($entityClasses, (string)($policy['mode'] ?? 'all'))) return 'entity_out_of_scope';
        }
    }
    if (isset($policy['subject_param'])) {
        $line = is_string($post[$policy['subject_param']] ?? null) ? (string)$post[$policy['subject_param']] : '';
        if (!teacher59_can_subject(in_array($line, ['graphics', 'networks'], true) ? $line : 'both')) return 'subject_out_of_scope';
    }
    if (isset($policy['entity_subject'])) {
        [$param, $type] = $policy['entity_subject'];
        $subject = teacher59_entity_subject((string)$type, is_string($post[$param] ?? null) ? (string)$post[$param] : '');
        if ($subject === null) return 'entity_not_found';
        if (!teacher59_can_subject($subject)) return 'subject_out_of_scope';
    }
    return null;
}

/** První příkaz v try bloku teacher.php – zamítnutí končí 403 (HTML/JSON); v legacy no-op. */
function teacher59_guard_post(string $action): void
{
    if (teacher59_mode() === 'legacy') return;
    $reason = teacher59_guard_post_check($action, $_POST);
    if ($reason === 'legacy_only') throw new RuntimeException('Role se nastavují v záložce Učitelé.');
    if ($reason !== null) teacher59_deny($reason);
    // Formulář bez class_id: přesměrování po akci vede do třídy entity (ne do výchozí class_2a).
    $policy = teacher59_action_policy($action);
    if (isset($policy['entity']) && !is_string($_POST['class_id'] ?? null)) {
        $id = is_string($_POST[$policy['entity'][0]] ?? null) ? (string)$_POST[$policy['entity'][0]] : '';
        $classes = $id !== '' ? (teacher59_entity_classes((string)$policy['entity'][1], $id) ?? []) : [];
        $allowed = array_values(array_filter($classes, 'teacher59_can_class'));
        if ($allowed !== []) $_POST['class_id'] = $allowed[0];
    }
}

/** Zvláštní GET (tab|parametr) – každý GET parametr registru v58 musí mít záznam, jinak zákaz. */
function teacher59_get_policies(): array
{
    return [
        'teach|v48_state' => ['class' => 'required'],
        'pristupy|print' => ['class' => 'required'],
        'arena|arena_poll' => ['entity' => ['race', 'race']],
        // SEC59-10: data hádanky filtruje arena58_weekly_teacher_data() (by_class/top přes teacher59_can_class) – každý učitel.
        'arena|hadanka_poll' => [],
        'arena|zaznam' => ['entity' => ['race', 'race']],
        'arena|projector' => ['entity' => ['race', 'race']],
        'roboti|robots_poll' => ['entity' => ['match', 'match']],
        'roboti|projector' => ['entity' => ['match', 'match']],
        'hry|tg_poll' => ['entity' => ['game', 'tg58_game']],
        'hry|projektor' => ['entity' => ['game|projektor', 'tg58_game']],
        'labdata|export' => ['class' => 'required'],
        'labdata|dohled_poll' => ['class' => 'required'],
        'intake|artifact' => ['entity' => ['artifact', 'intake_response']],
        'intake|export' => ['class' => 'required'],
        'ucitele|karticka' => ['admin' => true],
        // v61: export přehledu třídy do CSV – třída povinná a v rozsahu (ov61_export_csv ji ověřuje znovu).
        'prehled|export' => ['class' => 'required'],
    ];
}

/** Entity v GET parametrech záložek (kontrolují se, kdykoli jsou v požadavku). */
function teacher59_get_entity_params(): array
{
    return [
        'arena|race' => ['race', 'all'], 'roboti|match' => ['match', 'all'], 'hry|game' => ['tg58_game', 'all'],
        'ctf|event' => ['ctf_event', 'all'], 'incidenty|session' => ['inc_session', 'all'], 'editor|level' => ['lab58e_level', 'all'],
        'groups|edit_group' => ['group', 'all'], 'workspace|group' => ['group', 'all'], 'grade|target' => ['grade_target', 'all'],
        'interventions|intervention' => ['intervention', 'all'], 'history|record' => ['grade_record', 'all'],
    ];
}

function teacher59_get_entity_id(array $get, string $params): string
{
    return (string)(teacher59_get_entity_ids($get, $params)[0] ?? '');
}

/** Všechna id entity z uvedených parametrů (hodnota '1' = příznak, ne id); null = parametr v nečekaném tvaru. */
function teacher59_get_entity_ids(array $get, string $params): ?array
{
    $ids = [];
    foreach (explode('|', $params) as $param) {
        if (!array_key_exists($param, $get)) continue;
        $value = $get[$param];
        if (!is_string($value)) return null;
        if ($value !== '' && $value !== '1') $ids[] = $value;
    }
    return array_values(array_unique($ids));
}

/** Důvod zamítnutí GET, nebo null. Čistá funkce – bez výstupu. */
function teacher59_guard_get_check(string $tab, array $get): ?string
{
    if (teacher59_mode() === 'legacy') return null;
    if (teacher59_session_state() !== 'ok') return 'no_session';
    $admin = teacher59_is_admin();
    if (in_array($tab, TEACHER59_ADMIN_TABS, true) && !$admin) return 'admin_only';
    $policies = teacher59_get_policies();
    if (function_exists('teacher58_is_tab') && teacher58_is_tab($tab)) {
        foreach (array_keys((array)(teacher58_modules()[$tab]['get'] ?? [])) as $param) {
            if (isset($get[$param]) && !isset($policies[$tab . '|' . $param])) return 'unknown_get';
        }
    }
    if ($tab === 'ekonomika' && isset($get['month']) && (!is_string($get['month']) || preg_match(STORAGE_MONTH_RE, (string)$get['month']) !== 1)) return 'unknown_get'; // v64: month jen YYYY-MM
    if ($admin) return null;
    foreach ($policies as $key => $policy) {
        [$policyTab, $param] = explode('|', $key, 2);
        if ($policyTab !== $tab || !isset($get[$param])) continue;
        if (!empty($policy['admin'])) return 'admin_only';
        if (isset($policy['filter_marker']) && !teacher59_module_filtered((string)$policy['filter_marker'])) return 'module_not_scoped';
        if (($policy['class'] ?? '') === 'required') {
            $classId = is_string($get['class'] ?? null) ? (string)$get['class'] : '';
            if (!teacher59_can_class($classId)) return 'class_out_of_scope';
        }
        if (isset($policy['entity'])) {
            // SEC59-02: každý přítomný parametr musí vést na entitu v rozsahu (modul může vykreslovat podle kteréhokoli).
            $ids = teacher59_get_entity_ids($get, (string)$policy['entity'][0]);
            if ($ids === null || $ids === []) return 'entity_not_found';
            foreach ($ids as $id) {
                $classes = teacher59_entity_classes((string)$policy['entity'][1], $id);
                if ($classes === null) return 'entity_not_found';
                if (!teacher59_can_classes($classes)) return 'entity_out_of_scope';
            }
        }
    }
    $classId = $get['class'] ?? null;
    if (is_string($classId) && $classId !== '' && in_array($classId, teacher59_all_class_ids(), true) && !teacher59_can_class($classId)) return 'class_out_of_scope';
    foreach (teacher59_get_entity_params() as $key => [$type, $mode]) {
        [$paramTab, $param] = explode('|', $key, 2);
        if ($paramTab !== $tab || !is_string($get[$param] ?? null) || $get[$param] === '') continue;
        $classes = teacher59_entity_classes($type, (string)$get[$param]);
        if ($classes === null) return 'entity_not_found';
        if (!teacher59_classes_ok($classes, $mode)) return 'entity_out_of_scope';
    }
    $student = $get['student'] ?? null;
    if (is_string($student) && ($studentClass = teacher59_student_key_class($student)) !== '' && !teacher59_can_class($studentClass)) return 'entity_out_of_scope';
    return null;
}

/** Před intake_v51_teacher_handle_get v teacher.php; v legacy no-op. */
function teacher59_guard_get(string $tab): void
{
    if (teacher59_mode() === 'legacy') return;
    $reason = teacher59_guard_get_check($tab, $_GET);
    if ($reason !== null) teacher59_deny($reason);
}

// ---------------------------------------------------------------------------
// Odpovědi 403 / 503
// ---------------------------------------------------------------------------

function teacher59_wants_json(): bool
{
    foreach (TEACHER59_JSON_GET_PARAMS as $param) {
        if (isset($_GET[$param])) return true;
    }
    if ((string)($_GET['tab'] ?? '') === 'hry' && isset($_GET['game']) && isset($_GET['v'])) return true;
    return str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
        || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

/** Třídy z požadavku pro log zamítnutí (bez id žáků). */
function teacher59_request_class_ids(): array
{
    $out = [];
    foreach ([$_POST['class_id'] ?? null, $_GET['class'] ?? null] as $value) {
        if (is_string($value) && preg_match('/^class_[a-z0-9]{1,8}$/', $value)) $out[] = $value;
    }
    foreach ((array)($_POST['class_ids'] ?? []) as $value) {
        if (is_string($value) && preg_match('/^class_[a-z0-9]{1,8}$/', $value)) $out[] = $value;
    }
    return array_values(array_unique($out));
}

function teacher59_page(string $title, string $bodyHtml): string
{
    return '<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . e($title) . ' · EDUCANET</title><link rel="stylesheet" href="assets/app.css?v=46"><link rel="stylesheet" href="assets/teacher.css?v=50.1">'
        . '<link rel="stylesheet" href="assets/teacher-accounts-v59.css?v=59.0"></head><body class="teacher-app"><main class="teacher-shell t59-standalone">'
        . $bodyHtml . '</main></body></html>';
}

/** 403 – stránka nebo JSON {ok:false}; zapíše scope_denied do logu. */
function teacher59_deny(string $reason, ?bool $json = null, string $message = ''): never
{
    $json ??= teacher59_wants_json();
    $message = $message !== '' ? $message : ($reason === 'admin_only' ? 'Tahle část je jen pro administrátora.' : 'K této třídě nebo položce nemáte přístup.');
    teacher59_log('scope_denied', teacher59_current_id(), null, teacher59_request_class_ids(), $reason);
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        http_response_code(403);
        teacher59_no_store();
        header('Content-Type: ' . ($json ? 'application/json' : 'text/html') . '; charset=utf-8');
    }
    if ($json) {
        echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo teacher59_page('Přístup odepřen', '<section class="t59-card" role="alert"><div class="eyebrow">Přístup odepřen</div><h1>Sem nemáte přístup</h1>'
        . '<p>' . e($message) . ($reason === 'admin_only' ? ' Potřebujete-li k ní přístup, požádejte administrátora.' : ' Pokud jde o omyl, požádejte administrátora o přiřazení třídy.') . '</p>'
        . '<p><a class="btn primary t59-btn" href="teacher.php">Zpět na přehled</a></p></section>');
    exit;
}

/** 503 – úložiště účtů nejde přečíst, nebo chybí a účty jsou povinné (nikdy se nepřepne zpět na sdílený klíč). */
function teacher59_render_unavailable(): never
{
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        http_response_code(503);
        teacher59_no_store();
        header('Content-Type: text/html; charset=utf-8');
    }
    echo teacher59_page('Učitelské rozhraní je nedostupné', '<section class="t59-card" role="alert"><div class="eyebrow">Údržba</div>'
        . '<h1>Učitelské přihlášení je dočasně nedostupné</h1>'
        . (is_file(teacher59_accounts_path())
            ? '<p>Úložiště učitelských účtů nejde přečíst. Správce ho obnoví ze zálohy (soubor <code>storage/teacher_accounts_v59.json.php</code>).'
            : '<p>Učitelské účty ještě nejsou založené. Správce na serveru spustí <code>tools/v59_teacher_accounts.php create-admin</code> (první nasazení), '
                . 'nebo obnoví soubor <code>storage/teacher_accounts_v59.json.php</code> ze zálohy.')
        . ' Sdílený klíč se z bezpečnostních důvodů automaticky nezapíná.</p></section>');
    exit;
}
