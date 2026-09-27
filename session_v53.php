<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v53 · Hodina s invite kódem.
 * - učitel otevře hodinu pro třídu a datum, promítne kód,
 * - 1.A (bez dat): kód → registrace (jméno, příjmení, e-mail, místo) → seznamovací dotazník,
 * - ostatní třídy: kód → samostatná práce k dnešní lekci s odevzdáním a kontrolou učitele.
 */

function sess53_path(string $name): string
{
    return STORAGE_DIR . '/' . preg_replace('/[^a-z0-9_]/', '', $name) . '.json.php';
}

function sess53_all(): array
{
    $rows = load_php_json(sess53_path('lesson_sessions'));
    return is_array($rows) ? $rows : [];
}

function sess53_code(array $taken = []): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($i = 0; $i < 60; $i++) {
        $code = '';
        for ($n = 0; $n < 6; $n++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        if (!isset($taken[$code])) return $code;
    }
    throw new RuntimeException('Nepodařilo se vygenerovat kód hodiny.');
}

function sess53_find_by_code(string $code): ?array
{
    $code = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? ''));
    if ($code === '') return null;
    foreach (sess53_all() as $row) {
        if (is_array($row) && strtoupper((string)($row['code'] ?? '')) === $code) return $row;
    }
    return null;
}

function sess53_find(string $id): ?array
{
    foreach (sess53_all() as $row) if (is_array($row) && (string)($row['id'] ?? '') === $id) return $row;
    return null;
}

function sess53_for_class_date(string $classId, string $date): ?array
{
    foreach (sess53_all() as $row) {
        if (is_array($row) && (string)($row['class_id'] ?? '') === $classId && (string)($row['date'] ?? '') === $date) return $row;
    }
    return null;
}

/** Úkoly samostatné práce: primárně z kroků dnešní lekce, aby navazovaly na kurikulum. */
function sess53_tasks_from_lesson(array $lesson): array
{
    $tasks = [];
    foreach ((array)($lesson['steps'] ?? []) as $step) {
        if (!is_array($step)) continue;
        $title = trim((string)preg_replace('/^\d+\s*·\s*/u', '', (string)($step['title'] ?? '')));
        $detail = implode(' · ', array_slice(array_map('strval', (array)($step['tasks'] ?? [])), 0, 2));
        if ($detail === '' && !empty($step['question'])) $detail = (string)$step['question'];
        if ($title === '') continue;
        $tasks[] = ['title' => $title, 'detail' => $detail, 'time' => (string)($step['time'] ?? '')];
    }
    foreach ((array)($lesson['worksheet'] ?? []) as $w) {
        $w = trim((string)$w);
        if ($w !== '') $tasks[] = ['title' => $w, 'detail' => 'Zapiš do odevzdání.', 'time' => ''];
    }
    return $tasks;
}

/** Zadání hodiny = kroky projektu z v56, aby učitel viděl přesně to, co žák. */
function sess53_session_tasks(string $classId, array $modules, array $lesson): array
{
    $module = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    if (function_exists('v56_lesson_bundle')) {
        $no = max(1, (int)($lesson['number'] ?? 1));
        $bundle = v56_lesson_bundle($classId, $module, $no, (array)($GLOBALS['nextLessons'] ?? []), (array)($GLOBALS['extendedLessons'] ?? []));
        $tasks = [];
        foreach ((array)$bundle['steps'] as $step) {
            $tasks[] = [
                'title' => (string)$step['title'],
                'detail' => implode(' · ', array_slice(array_map('strval', (array)($step['tasks'] ?? [])), 0, 2)),
                'time' => (string)($step['time'] ?? ''),
            ];
        }
        if (count($tasks) >= 3) return $tasks;
    }
    $tasks = sess53_tasks_from_lesson($lesson);
    if (count($tasks) >= 3) return $tasks;
    return function_exists('v55_primary_tasks') ? v55_primary_tasks($classId, $module) : $tasks;
}

/** Vytvoří (nebo vrátí) hodinu pro třídu a datum. */
function sess53_open(string $classId, string $date, array $modules, array $lesson, string $kind, array $options = []): array
{
    $existing = sess53_for_class_date($classId, $date);
    if ($existing) return $existing;
    $taken = [];
    foreach (sess53_all() as $row) if (is_array($row) && !empty($row['code'])) $taken[(string)$row['code']] = true;
    $session = [
        'id' => 'ses_' . bin2hex(random_bytes(6)),
        'class_id' => $classId,
        'date' => $date,
        'code' => sess53_code($taken),
        'kind' => in_array($kind, ['intake', 'work'], true) ? $kind : 'work',
        'lesson_number' => (int)($options['lesson_number'] ?? 0),
        'title' => (string)($options['title'] ?? ($lesson['title'] ?? 'Hodina')),
        'goal' => (string)($options['goal'] ?? ($lesson['goal'] ?? '')),
        'instructions' => (string)($options['instructions'] ?? ''),
        'tasks' => $kind === 'work' ? sess53_session_tasks($classId, $modules, $lesson) : [],
        'open' => true,
        'created_at' => date(DATE_ATOM),
    ];
    // v58: zápis pod zámkem; když hodinu mezitím otevřel někdo jiný, vrátí se ta jeho.
    storage_update(sess53_path('lesson_sessions'), static function (array $rows) use (&$session, $classId, $date): array {
        foreach ($rows as $row) {
            if (is_array($row) && (string)($row['class_id'] ?? '') === $classId && (string)($row['date'] ?? '') === $date) { $session = $row; return $rows; }
            if (is_array($row) && (string)($row['code'] ?? '') === (string)$session['code']) throw new RuntimeException('Kód hodiny se právě použil jinde, zkuste to znovu.');
        }
        $rows[] = $session;
        return $rows;
    });
    return $session;
}

function sess53_update(string $id, callable $mutate): ?array
{
    if (sess53_find($id) === null) return null;
    $out = null;
    // Jeden zámek drží čtení, úpravu i zápis – souběžná změna jiné hodiny se neztratí.
    storage_update(sess53_path('lesson_sessions'), static function (array $rows) use ($id, $mutate, &$out): array {
        foreach ($rows as $i => $row) {
            if (!is_array($row) || (string)($row['id'] ?? '') !== $id) continue;
            $rows[$i] = $mutate($row);
            $out = $rows[$i];
            break;
        }
        return $rows;
    });
    return $out;
}

// ---------------------------------------------------------------------------
// Odevzdání
// ---------------------------------------------------------------------------

function sess53_submissions(string $sessionId): array
{
    $all = load_php_json(sess53_path('lesson_session_work'));
    $rows = is_array($all[$sessionId] ?? null) ? $all[$sessionId] : [];
    uasort($rows, static fn(array $a, array $b): int => strnatcasecmp((string)($a['label'] ?? ''), (string)($b['label'] ?? '')));
    return $rows;
}

function sess53_submission(string $sessionId, string $studentKey): array
{
    $rows = sess53_submissions($sessionId);
    return is_array($rows[$studentKey] ?? null) ? $rows[$studentKey] : ['checks' => [], 'note' => '', 'link' => '', 'status' => 'open'];
}

function sess53_submit(string $sessionId, string $studentKey, string $label, array $data): array
{
    $row = [];
    storage_update(sess53_path('lesson_session_work'), static function (array $all) use ($sessionId, $studentKey, $label, $data, &$row): array {
        $rows = is_array($all[$sessionId] ?? null) ? $all[$sessionId] : [];
        $current = is_array($rows[$studentKey] ?? null) ? $rows[$studentKey] : [];
        $row = array_merge($current, [
            'student_key' => $studentKey,
            'label' => $label,
            'checks' => array_values(array_map('strval', (array)($data['checks'] ?? []))),
            'note' => intake_v51_text($data['note'] ?? '', 4000),
            // Jen http/https – učitel odkaz dostane jako href.
            'link' => safe_url(intake_v51_text($data['link'] ?? '', 500)),
            'status' => (string)($data['status'] ?? 'submitted'),
            'updated_at' => date(DATE_ATOM),
        ]);
        if (empty($row['started_at'])) $row['started_at'] = date(DATE_ATOM);
        if (!empty($data['artifact'])) $row['artifact'] = $data['artifact'];
        $rows[$studentKey] = $row;
        $all[$sessionId] = $rows;
        return $all;
    });
    return $row;
}

function sess53_teacher_feedback(string $sessionId, string $studentKey, string $comment, ?int $grade): void
{
    storage_update(sess53_path('lesson_session_work'), static function (array $all) use ($sessionId, $studentKey, $comment, $grade): array {
        if (!is_array($all[$sessionId] ?? null) || !is_array($all[$sessionId][$studentKey] ?? null)) throw new RuntimeException('Odevzdání nebylo nalezeno.');
        $all[$sessionId][$studentKey]['teacher_comment'] = intake_v51_text($comment, 1200);
        if ($grade !== null && $grade >= 1 && $grade <= 5) {
            $all[$sessionId][$studentKey]['grade'] = $grade;
            // v53: známka se přepočítá na motivační body (1 = 3 body, 2 = 2 body, 3 = 1 bod).
            $all[$sessionId][$studentKey]['points'] = pts53_points_for_grade($grade);
        }
        $all[$sessionId][$studentKey]['reviewed_at'] = date(DATE_ATOM);
        return $all;
    });
    if ($grade !== null) {
        $session = sess53_find($sessionId);
        if ($session) pts53_award($session['class_id'], $studentKey, 'session:' . $sessionId, pts53_points_for_grade($grade), 'Samostatná práce · ' . (string)$session['title']);
    }
}

const SESS53_REGISTER_EXISTS = 'Pro tohle jméno nebo e-mail už ve třídě účet je. Přihlas se heslem z kartičky od učitele (nebo svým vlastním), případně se obrať na učitele.';
const SESS53_REGISTER_CLOSED = 'Účet se dá založit jen v otevřené seznamovací hodině. Zeptej se učitele.';
// v59 i18n: konstanty výše zůstávají doslovné (audity je porovnávají == RuntimeException::getMessage()).
// trm() jen zaregistruje stejný text jako msgid; překlad se aplikuje až na výstupu přes tr($e->getMessage()).
trm('Pro tohle jméno nebo e-mail už ve třídě účet je. Přihlas se heslem z kartičky od učitele (nebo svým vlastním), případně se obrať na učitele.');
trm('Účet se dá založit jen v otevřené seznamovací hodině. Zeptej se učitele.');
const SESS53_REGISTER_LIMIT = 40;       // pokusů o registraci z jedné IP …
const SESS53_REGISTER_WINDOW = 900;     // … za 15 minut (celá třída za školní NAT se vejde)

/** Registrace žáka 1.A přes kód hodiny: jméno, e-mail, místo → účet + přihlášení. */
function sess53_join_register(array $session, array $modules, array $post): void
{
    // v58 · SEC58-01: kódem hodiny se registruje JEN v otevřené seznamovací hodině a jen nepřihlášený
    // návštěvník. Přihlášený (nebo kód pracovní hodiny) nesmí založit účet „jako spolužák“.
    if ((string)($session['kind'] ?? '') !== 'intake' || empty($session['open'])) throw new RuntimeException(SESS53_REGISTER_CLOSED);
    if (auth_user() !== null) throw new RuntimeException(tr('Už jsi přihlášený/á. Do hodiny vstup tlačítkem Vstoupit.'));
    $classId = (string)$session['class_id'];
    $first = intake_v51_text($post['first_name'] ?? '', 60);
    $last = intake_v51_text($post['last_name'] ?? '', 60);
    $email = local_email_normalize((string)($post['email'] ?? ''));
    $seatId = intake_v51_text($post['seat_id'] ?? '', 20);
    if ($first === '' || $last === '') throw new RuntimeException(tr('Vyplň jméno i příjmení.'));
    $label = $first . ' ' . $last;
    if ($email === '') $email = acc53_email($label, local_accounts());
    if (!local_email_is_allowed($email)) throw new RuntimeException(tr('Použij školní e-mail @{domain}.', ['domain' => google_workspace_domain()]));
    $classes = intake_v51_classes($modules);
    $class = $classes[$classId] ?? null;
    if (!$class || !intake_v51_find_seat($class, $seatId)) throw new RuntimeException(tr('Vyber místo, kde sedíš.'));

    // v58: kód hodiny zakládá jen NOVÉ účty. Do existujícího účtu se jde výhradně heslem
    // (jednorázovým z kartičky nebo vlastním) – dřív stačilo znát jméno a sdílené heslo.
    // Jméno, které už ve třídě účet má (školní i propojený Google), se znovu nezakládá – jednotná
    // hláška neprozradí, jestli existuje e-mail, nebo jméno.
    if (isset(local_accounts()[$email]) || acc58_link_allowed($classId, $label, 'local:sess53-pending-registration') !== null) {
        throw new RuntimeException(SESS53_REGISTER_EXISTS);
    }
    $res = acc53_ensure_account($label, $classId, ['email' => $email, 'by' => 'system']);
    if (empty($res['created'])) throw new RuntimeException(SESS53_REGISTER_EXISTS);
    $account = $res['account'];
    session_regenerate_id(true);
    $_SESSION['local_user'] = local_account_public($account);
    unset($_SESSION['google_user']);
    // Účet vznikl právě v této session → při nastavení vlastního hesla se neptáme na jednorázové.
    acc58_mark_session_proven($email);
    bind_auth_account(auth_user() ?? [], $classId, $label);
    $_SESSION['intake_seat'] = $seatId;
    $_SESSION['sess53_joined'] = (string)$session['id'];
    acc53_touch_login($email);
}
