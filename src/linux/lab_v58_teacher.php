<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – učitelská analytika (TCH-02 přehrávání, TCH-03 dohled, TCH-04 export).
 * Čte jen přes veřejné funkce jádra (`lab_v58_log.php`, `linux_v57_lab.php`) a `lab_v58_learning.php`.
 * Oprávnění: prefix POST akcí `lab58t_` je v `teacher_operations_v46.php` už namapovaný na `analytics.view`.
 * Třída (`$classId`) přichází z teacher.php stejně jako u ostatních záložek (validovaná proti katalogu tříd).
 */

const LAB58T_IDLE_ALERT_SECS = 300;   // bez postupu > 5 minut
const LAB58T_ERROR_WINDOW_MIN = 10;   // okno pro počítání chyb
const LAB58T_ERROR_ALERT_COUNT = 6;   // „mnoho chyb“ za posledních 10 minut
const LAB58T_ACTIVITY_WINDOW_SECS = 3600; // dohled ukazuje jen žáky aktivní poslední hodinu

// ---------------------------------------------------------------------------
// TCH-03: živý dohled
// ---------------------------------------------------------------------------

function lab58t_minutes(int $secs): int
{
    return (int)round(max(0, $secs) / 60);
}

/** @return array{generated_at:int,alerts:list<array{student:string,level:string,ctx:string,reason:string,idle_secs:int}>} */
function lab58t_supervision(string $classId, ?int $now = null): array
{
    $now ??= time();
    if (!function_exists('lab58_class_activity')) return ['generated_at' => $now, 'alerts' => []];
    $activity = lab58_class_activity($classId, $now - LAB58T_ACTIVITY_WINDOW_SECS, LAB58T_ERROR_WINDOW_MIN, $now);

    $alerts = [];
    foreach ($activity as $studentKey => $row) {
        if (!empty($row['solved'])) continue;
        $reasons = [];
        if ((int)$row['idle_secs'] >= LAB58T_IDLE_ALERT_SECS) $reasons[] = 'bez postupu ' . lab58t_minutes((int)$row['idle_secs']) . ' min';
        if ((int)$row['errors_recent'] >= LAB58T_ERROR_ALERT_COUNT) $reasons[] = (int)$row['errors_recent'] . ' chyb za ' . LAB58T_ERROR_WINDOW_MIN . ' min';
        if ($reasons === []) continue;
        $level = function_exists('lab57_level') ? lab57_level((string)$row['level']) : null;
        $alerts[] = [
            'student' => function_exists('adaptive_student_label') ? adaptive_student_label($classId, (string)$studentKey) : (string)$studentKey,
            'level' => $level !== null ? (string)$level['title'] : (string)$row['level'],
            'ctx' => (string)$row['ctx'],
            'reason' => implode(' · ', $reasons),
            'idle_secs' => (int)$row['idle_secs'],
        ];
    }
    usort($alerts, static fn(array $a, array $b): int => $b['idle_secs'] <=> $a['idle_secs']);
    return ['generated_at' => $now, 'alerts' => $alerts];
}

/** JSON polling (GET, 10 s) pro podsekci Dohled. Volá teacher.php po ověření přihlášení učitele. */
function lab58t_teacher_poll(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $classId = (string)($_GET['class'] ?? '');
    // v59 · AUTHZ58-07: navíc třída v rozsahu přihlášeného učitele (legacy = všechny).
    if (!function_exists('teacher_export_authenticated') || !teacher_export_authenticated() || !isset($GLOBALS['modules'][$classId])
        || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) {
        http_response_code(403);
        echo json_encode(['ok' => false]);
        exit;
    }
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    echo json_encode(['ok' => true] + lab58t_supervision($classId), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------------------------------------------------------------------------
// TCH-02: přehrávání relace
// ---------------------------------------------------------------------------

/** Úrovně, ke kterým má žák uložený log (nejnovější první) – pro výběr v UI. */
function lab58t_student_levels(string $classId, string $studentKey): array
{
    if (!function_exists('lab58_log_dir')) return [];
    $out = [];
    foreach (glob(lab58_log_dir($classId, $studentKey) . '/*.jsonl.php') ?: [] as $file) {
        $levelId = basename($file, '.jsonl.php');
        $level = function_exists('lab57_level') ? lab57_level($levelId) : null;
        $out[] = ['id' => $levelId, 'title' => $level !== null ? (string)$level['title'] : $levelId, 'mtime' => (int)filemtime($file)];
    }
    usort($out, static fn(array $a, array $b): int => $b['mtime'] <=> $a['mtime']);
    return $out;
}

/** Časová osa příkazů a výstupů jednoho žáka v jedné úrovni; jen žáci vybrané třídy (retence 30 dní z logu). */
function lab58t_replay(string $classId, string $studentKey, string $levelId): array
{
    $empty = ['level' => $levelId, 'title' => $levelId, 'events' => []];
    if (!function_exists('project_students_for_class') || !isset(project_students_for_class($classId)[$studentKey])) return $empty;
    if (!function_exists('lab58_log_read')) return $empty;

    $events = [];
    foreach (lab58_log_read($classId, $studentKey, $levelId) as $r) {
        $events[] = [
            'ts' => (int)$r['ts'], 'kind' => (string)$r['kind'], 'line' => (string)$r['line'], 'exit' => (int)$r['exit'],
            'error_class' => (string)$r['error_class'], 'out' => (string)$r['out'], 'hint' => (int)$r['hint'], 'points' => (int)$r['points'],
        ];
    }
    $level = function_exists('lab57_level') ? lab57_level($levelId) : null;
    return ['level' => $levelId, 'title' => $level !== null ? (string)$level['title'] : $levelId, 'events' => $events];
}

// ---------------------------------------------------------------------------
// TCH-04: export CSV
// ---------------------------------------------------------------------------

function lab58t_export_row(string $classId, string $studentKey, string $label, string $modeLabel): array
{
    $summary = function_exists('lab57_student_summary') ? lab57_student_summary($classId, $studentKey) : ['count' => 0, 'points' => 0, 'solved' => [], 'log' => []];
    $hints = 0;
    foreach ((array)($summary['solved'] ?? []) as $info) $hints += (int)($info['hints'] ?? 0);
    $lastLog = (array)($summary['log'] ?? []);
    $lastAt = $lastLog !== [] ? (string)(end($lastLog)['t'] ?? '') : '';
    $badges = function_exists('lab58_badges') ? lab58_badges($classId, $studentKey) : [];
    $earned = count(array_filter($badges, static fn(array $b): bool => !empty($b['earned'])));
    $streak = function_exists('lab58_review_streak') ? lab58_review_streak($classId, $studentKey) : 0;
    return [
        $label, (int)($summary['count'] ?? 0), (int)($summary['points'] ?? 0), $hints,
        $lastAt !== '' ? date('Y-m-d H:i', (int)strtotime($lastAt)) : '', $earned . '/' . count($badges), $streak, $modeLabel,
    ];
}

/** Jedna řádka CSV (oddělovač ';', uvozovky se zdvojují) – bez fopen()/php:// streamu (zakázáno bezpečnostním auditem labu). */
function lab58t_csv_row(array $fields): string
{
    $cells = array_map(static function ($v): string {
        $v = (string)$v;
        $needsQuotes = str_contains($v, ';') || str_contains($v, '"') || str_contains($v, "\n") || str_contains($v, "\r");
        return $needsQuotes ? '"' . str_replace('"', '""', $v) . '"' : $v;
    }, $fields);
    return implode(';', $cells) . "\r\n";
}

/** CSV UTF-8 s BOM, bez e-mailů, ochrana proti CSV injection (csv_safe_cell z bootstrap.php). */
function lab58t_export_csv(string $classId, string $mode): void
{
    if (!function_exists('project_students_for_class') || !isset($GLOBALS['modules'][$classId])) throw new RuntimeException('Neplatná třída.');
    if (function_exists('teacher59_can_class') && !teacher59_can_class($classId)) throw new RuntimeException('K této třídě nemáte přístup.'); // v59
    $mode = $mode === 'sumativni' ? 'sumativni' : 'formativni';
    $modeLabel = $mode === 'sumativni' ? 'Sumativní' : 'Formativní';
    $className = function_exists('teacher_class_label') ? teacher_class_label($classId) : $classId;

    $rows = [];
    foreach (project_students_for_class($classId) as $studentKey => $info) {
        $label = (string)($info['label'] ?? $studentKey);
        $rows[] = lab58t_export_row($classId, (string)$studentKey, $label, $modeLabel);
    }
    usort($rows, static fn(array $a, array $b): int => strcmp((string)$a[0], (string)$b[0]));

    $safeClass = trim((string)preg_replace('/[^a-z0-9]+/i', '-', strtolower($className)), '-');
    $filename = 'educanet-lab-' . ($safeClass !== '' ? $safeClass : 'trida') . '-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    $safe = static fn($v): mixed => is_string($v) && function_exists('csv_safe_cell') ? csv_safe_cell($v) : $v;
    $csv = "\xEF\xBB\xBF" . lab58t_csv_row(['Žák', 'Vyřešené úrovně', 'Body', 'Nápovědy', 'Poslední aktivita', 'Odznaky', 'Série opakování (dní)', 'Typ hodnocení']);
    foreach ($rows as $row) $csv .= lab58t_csv_row(array_map($safe, $row));
    echo $csv;
}

// ---------------------------------------------------------------------------
// POST akce (CSRF a oprávnění analytics.view ověřil teacher.php)
// ---------------------------------------------------------------------------

function lab58t_teacher_handle_post(string $action, array $modules): void
{
    if ($action !== 'lab58t_export') return;
    $classId = (string)($_POST['class_id'] ?? '');
    if (!isset($modules[$classId])) throw new RuntimeException('Neplatná třída.');
    lab58t_export_csv($classId, (string)($_POST['mode'] ?? 'formativni'));
    exit;
}
