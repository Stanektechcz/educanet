<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · nastavení výuky podle rozhodnutí školy (8. 10. 2026).
 *
 *   bod 4 – jména žáků: v učitelských pohledech, na tisku a projektoru jsou povolená; každý učitel si je může pro sebe
 *           skrýt (výchozí stav: zobrazená). Fáze C (tisk, projektor) volá tp721_names_hidden() / tp721_student_label().
 *   bod 9 – exit ticket: učitel třídy volí „jen zpětná vazba“ (výchozí) nebo „podklad ke známce“. Režim se zapisuje ke každé
 *           odpovědi (lx72_submit); do návrhu hodnocení v66 jdou jen odpovědi dané v režimu „podklad ke známce“ a jen dokud je
 *           ten režim ve třídě zapnutý (tp721_exit_graded_part → část „sumativní testy“, váhy 40/40/20 se nemění, návrh
 *           zapíná administrátor). Žák vidí režim před odpovědí.
 *
 * Úložiště storage/teacher_prefs_v72_1.json.php = {"v":1,"teachers":{<hash učitele>:{names_hidden,at}},"classes":{<třída>:{exit_mode,at,by}},"log":[…]}
 * – zápis jen přes storage_update; hash učitele = 16 hex z vlastnického klíče účtu v59 (žádné jméno, e-mail ani login);
 *   log je append-only se stropem TP721_LOG_MAX. Data žáků se sem neukládají.
 * POST akce (modul `nastaveni_vyuky`): tp721_self_names (každá role, jen vlastní nastavení), tp721_class_exit (třída povinná
 * a v rozsahu, oprávnění content.manage). CSRF a politiky ověřuje teacher.php; neznámá tp721_* akce je zakázaná.
 */

const TP721_EXIT_MODES = ['feedback' => 'jen zpětná vazba', 'grade' => 'podklad ke známce'];
const TP721_EXIT_DEFAULT = 'feedback';
const TP721_LOG_MAX = 200;
const TP721_ACTIONS = ['tp721_self_names', 'tp721_class_exit'];
const TP721_CLASSES = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];

function tp721_path(): string
{
    return STORAGE_DIR . '/teacher_prefs_v72_1.json.php';
}

/** Data nastavení (jednou za požadavek; po zápisu se obnoví). */
function tp721_data(bool $refresh = false): array
{
    static $data = null;
    if ($data === null || $refresh) {
        $read = function_exists('storage_read') ? storage_read(tp721_path(), false) : [];
        $data = is_array($read) ? $read : [];
    }
    return $data;
}

/** Hash přihlášeného učitele (16 hex; bez účtů v59 společný „legacy“ klíč). */
function tp721_teacher_hash(): string
{
    $key = function_exists('teacher59_owner_key') ? (string)(teacher59_owner_key() ?? 'legacy') : 'legacy';
    return substr(hash('sha256', 'tp721|' . $key), 0, 16);
}

/** Skrývá učitel jména žáků? Výchozí stav: ne (jména jsou zobrazená). */
function tp721_names_hidden(?string $teacherHash = null): bool
{
    $hash = $teacherHash ?? tp721_teacher_hash();
    return !empty(tp721_data()['teachers'][$hash]['names_hidden']);
}

/** Popisek žáka v učitelském pohledu: jméno, nebo při skrytí „Žák N“ (pořadí v daném výpisu, od 1). */
function tp721_student_label(string $label, int $index, ?bool $hidden = null): string
{
    return ($hidden ?? tp721_names_hidden()) ? 'Žák ' . max(1, $index) : $label;
}

/** Režim exit ticketu třídy: feedback (výchozí) | grade. */
function tp721_exit_mode(string $classId): string
{
    $mode = (string)(tp721_data()['classes'][$classId]['exit_mode'] ?? TP721_EXIT_DEFAULT);
    return isset(TP721_EXIT_MODES[$mode]) ? $mode : TP721_EXIT_DEFAULT;
}

/** Záznam o posledním nastavení režimu třídy (pro cockpit). @return array{mode:string,at:string,by:string} */
function tp721_exit_record(string $classId): array
{
    $row = tp721_data()['classes'][$classId] ?? null;
    return ['mode' => tp721_exit_mode($classId), 'at' => is_array($row) ? (string)($row['at'] ?? '') : '', 'by' => is_array($row) ? (string)($row['by'] ?? '') : ''];
}

/** Uloží volbu jmen (jen vlastní nastavení učitele). */
function tp721_set_names_hidden(bool $hidden, string $teacherHash, ?int $now = null): void
{
    if (preg_match('/^[0-9a-f]{16}$/', $teacherHash) !== 1) throw new RuntimeException('Neplatný učitel.');
    $at = date(DATE_ATOM, $now ?? time());
    storage_update(tp721_path(), static function (array $d) use ($hidden, $teacherHash, $at): array {
        $d['v'] = 1;
        $d['teachers'] = is_array($d['teachers'] ?? null) ? $d['teachers'] : [];
        $d['teachers'][$teacherHash] = ['names_hidden' => $hidden, 'at' => $at];
        return tp721_log($d, ['kind' => 'names', 'teacher' => $teacherHash, 'value' => $hidden ? 'hidden' : 'shown', 'at' => $at]);
    });
    tp721_data(true);
}

/** Uloží režim exit ticketu třídy (třídu a oprávnění ověřuje volající). */
function tp721_set_exit_mode(string $classId, string $mode, string $by, ?int $now = null): void
{
    if (!in_array($classId, TP721_CLASSES, true)) throw new RuntimeException('Neplatná třída.');
    if (!isset(TP721_EXIT_MODES[$mode])) throw new RuntimeException('Neznámý režim exit ticketu.');
    $at = date(DATE_ATOM, $now ?? time());
    $by = mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, 60);
    storage_update(tp721_path(), static function (array $d) use ($classId, $mode, $by, $at): array {
        $d['v'] = 1;
        $d['classes'] = is_array($d['classes'] ?? null) ? $d['classes'] : [];
        $d['classes'][$classId] = ['exit_mode' => $mode, 'at' => $at, 'by' => $by];
        return tp721_log($d, ['kind' => 'exit_mode', 'class' => $classId, 'value' => $mode, 'at' => $at, 'by' => $by]);
    });
    tp721_data(true);
}

/** Append-only log se stropem (čistá funkce). */
function tp721_log(array $d, array $entry): array
{
    $log = array_values(array_filter((array)($d['log'] ?? []), 'is_array'));
    $log[] = $entry;
    $d['log'] = array_slice($log, -TP721_LOG_MAX);
    return $d;
}

/** POST tp721_self_names / tp721_class_exit (politiku, CSRF a oprávnění už ověřil teacher.php). */
function tp721_teacher_handle_post(string $action): void
{
    $query = ['tab' => 'nastaveni_vyuky'];
    if ($action === 'tp721_self_names') {
        $value = is_string($_POST['names'] ?? null) ? (string)$_POST['names'] : '';
        if (!in_array($value, ['shown', 'hidden'], true)) throw new RuntimeException('Vyberte, zda jména zobrazit, nebo skrýt.');
        tp721_set_names_hidden($value === 'hidden', tp721_teacher_hash());
        $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $value === 'hidden'
            ? 'Jména žáků se vám teď v přehledech, na tisku a projektoru nezobrazují (místo nich „Žák 1, 2, …“).'
            : 'Jména žáků se vám v přehledech, na tisku a projektoru zobrazují.'];
    } elseif ($action === 'tp721_class_exit') {
        $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
        if (!in_array($classId, TP721_CLASSES, true) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
        $mode = is_string($_POST['mode'] ?? null) ? (string)$_POST['mode'] : '';
        tp721_set_exit_mode($classId, $mode, function_exists('teacher_display_name') ? teacher_display_name() : 'učitel');
        $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $mode === 'grade'
            ? 'Exit ticket je ve třídě podkladem ke známce. Žáci to uvidí před odpovědí; počítají se jen odpovědi od této chvíle.'
            : 'Exit ticket je ve třídě jen zpětnou vazbou – do návrhu hodnocení nevstupuje.'];
        $query['class'] = $classId;
    } else {
        throw new RuntimeException('Neznámá akce nastavení výuky.');
    }
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}

/**
 * Exit tickety jako podklad ke známce pro návrh hodnocení v66 (část „sumativní testy“): jen když je ve třídě režim „grade“
 * a jen odpovědi zapsané v tomto režimu (pole m = g). Bez takových odpovědí null. Nic nezapisuje.
 * @return array{pct:float,n:int,chain:array<string,mixed>}|null
 */
function tp721_exit_graded_part(string $classId, string $studentKey): ?array
{
    if (!in_array($classId, TP721_CLASSES, true) || $studentKey === '' || tp721_exit_mode($classId) !== 'grade') return null;
    $file = storage_read(STORAGE_DIR . '/lesson_exit_v72_' . $classId . '.json.php', false);
    $hash = substr(hash('sha256', 'lx72|' . $studentKey), 0, 20);   // = lx72_student_hash()
    $rows = [];
    foreach ((array)($file['answers'] ?? []) as $lesson => $byStudent) {
        $row = is_array($byStudent) ? ($byStudent[$hash] ?? null) : null;
        if (is_array($row) && (string)($row['m'] ?? '') === 'g') $rows[] = ['lesson' => (int)$lesson, 'ok' => (int)($row['ok'] ?? 0) === 1, 'at' => (string)($row['at'] ?? '')];
    }
    if ($rows === []) return null;
    usort($rows, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
    $correct = count(array_filter($rows, static fn(array $r): bool => $r['ok']));
    $ratio = round($correct / count($rows), 3);
    $evidence = array_map(static fn(array $r): array => ['ref' => 'lx72:l' . str_pad((string)$r['lesson'], 2, '0', STR_PAD_LEFT), 'source' => 'exit_ticket', 'score' => $r['ok'] ? 1.0 : 0.0, 'at' => $r['at']], array_slice($rows, 0, 3));
    return ['pct' => round($ratio * 100, 2), 'n' => count($rows), 'chain' => ['source' => 'summative_test', 'ref' => 'lx72:exit', 'label' => 'Exit tickety (podklad ke známce, ' . count($rows) . ' odpovědí)',
        'score' => $ratio, 'at' => $rows[0]['at'], 'evidence' => $evidence]];
}
