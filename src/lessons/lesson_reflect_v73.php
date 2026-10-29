<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · reflexe hodiny (modul `reflexe73`, POST akce lr73_save).
 *
 * Po hodině učitel jedním klepnutím zapíše stav (odučeno celé / část / neodučeno – jediné povinné pole), volitelně kde skončili
 * (úsek plánu nebo úkol), co fungovalo a co upravit (rychlé volby + text ≤ 300 znaků) a poznámku na příště (≤ 300 znaků).
 * Reflexe je o hodině, ne o žácích: formulář upozorňuje, že sem nepatří jména ani známky; text, který obsahuje celé jméno žáka
 * ze seznamu třídy, server odmítne. Dnešní hodina pak ukáže „Minule: skončili jste u …, poznámka: …“ a u neodučené hodiny
 * navrhne posun (jen návrh – kalendář se nemění).
 *
 * Úložiště storage/lesson_reflections_v73.json.php = {"v":1,"classes":{<třída>:{<id>:{lesson,date,state,stop_seg,stop_task,stop_label,
 *   picks_worked,picks_adjust,worked,adjust,next,author,at}}}} – zápis jen přes storage_update; autor = 16 hex z vlastnického klíče
 *   účtu v59 (žádné jméno ani login). Retence LR73_RETENTION_DAYS (purge při každém zápisu a nástrojem tools/v73_reflection_purge.php
 *   s --dry-run), strop LR73_MAX_PER_CLASS záznamů na třídu.
 * Čtení: autor + administrátor (výchozí, LR73_READERS_DEFAULT); škola může nastavit EDUCANET_REFLECTION_READERS=class_teachers
 *   (všichni učitelé s třídou v rozsahu). Zápis: politika lr73_save = třída povinná a v rozsahu, oprávnění content.manage
 *   (asistent jen čte), CSRF v teacher.php; handler třídu a lekci ověří znovu. Neznámá lr73_* akce je zakázaná.
 */

const LR73_STATES = ['cele' => 'odučeno celé', 'cast' => 'odučena část', 'neoduceno' => 'neodučeno'];
const LR73_TEXT_MAX = 300;
const LR73_RETENTION_DAYS = 365;
const LR73_MAX_PER_CLASS = 300;
const LR73_READERS_DEFAULT = 'author_admin';
const LR73_READERS_MODES = ['author_admin', 'class_teachers'];
const LR73_CLASSES = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
const LR73_NAME_MIN = 5;
const LR73_PICKS_WORKED = ['tempo' => 'tempo sedělo', 'dvojice' => 'práce ve dvojicích', 'lab' => 'Linux Lab / simulátor', 'ukazka' => 'ukázka na projektoru',
    'exit' => 'exit ticket', 'diskuse' => 'otázky a diskuse'];
const LR73_PICKS_ADJUST = ['cas' => 'málo času na úkoly', 'vyklad' => 'výklad byl dlouhý', 'zadani' => 'zadání nebylo jasné', 'technika' => 'technika / přihlášení',
    'tezke' => 'úkoly moc těžké', 'lehke' => 'úkoly moc lehké'];

function lr73_path(): string
{
    return STORAGE_DIR . '/lesson_reflections_v73.json.php';
}

/** Hash přihlášeného učitele (16 hex; bez účtů v59 společný „legacy“ klíč). */
function lr73_author_hash(): string
{
    $key = function_exists('teacher59_owner_key') ? (string)(teacher59_owner_key() ?? 'legacy') : 'legacy';
    return substr(hash('sha256', 'lr73|' . $key), 0, 16);
}

/** Kdo čte reflexe: author_admin (výchozí) | class_teachers (EDUCANET_REFLECTION_READERS). */
function lr73_readers_mode(): string
{
    $v = getenv('EDUCANET_REFLECTION_READERS');
    return is_string($v) && in_array(trim($v), LR73_READERS_MODES, true) ? trim($v) : LR73_READERS_DEFAULT;
}

function lr73_is_admin(): bool
{
    return function_exists('teacher59_is_admin') && teacher59_is_admin();
}

/** Smí aktuální učitel číst záznam? (autor, administrátor, nebo při class_teachers učitel s třídou v rozsahu) */
function lr73_can_read(array $entry, string $classId, ?string $authorHash = null, ?bool $admin = null): bool
{
    if ($admin ?? lr73_is_admin()) return true;
    if ((string)($entry['author'] ?? '') === ($authorHash ?? lr73_author_hash())) return true;
    return lr73_readers_mode() === 'class_teachers' && function_exists('teacher59_can_class') && teacher59_can_class($classId);
}

/** Všechny záznamy třídy (jen čtení). @return array<string,array> */
function lr73_entries(string $classId, ?array $data = null): array
{
    if (!in_array($classId, LR73_CLASSES, true)) return [];
    $data ??= storage_read(lr73_path(), false);
    $rows = is_array($data['classes'][$classId] ?? null) ? $data['classes'][$classId] : [];
    return array_filter($rows, 'is_array');
}

/** Záznamy, které aktuální učitel smí číst, od nejnovějšího. @return list<array> */
function lr73_visible(string $classId, ?string $authorHash = null, ?bool $admin = null, ?array $data = null): array
{
    $out = [];
    foreach (lr73_entries($classId, $data) as $id => $row) if (lr73_can_read($row, $classId, $authorHash, $admin)) $out[] = $row + ['id' => (string)$id];
    usort($out, static fn(array $a, array $b): int => strcmp((string)$b['date'], (string)$a['date']) ?: strcmp((string)$b['at'], (string)$a['at']));
    return $out;
}

/** Poslední viditelná reflexe před dnešní hodinou (dřívější den, nebo dnes dřívější lekce). */
function lr73_latest_before(string $classId, string $today, int $currentLesson, ?string $authorHash = null, ?bool $admin = null): ?array
{
    foreach (lr73_visible($classId, $authorHash, $admin) as $row) {
        if ((string)$row['date'] < $today || ((string)$row['date'] === $today && (int)$row['lesson'] < $currentLesson)) return $row;
    }
    return null;
}

/** Text reflexe: bez řídicích znaků, jedna mezera, limit znaků (delší se odmítne, ne ořízne). */
function lr73_clean_text(mixed $value, string $label): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', (string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', is_string($value) ? $value : '')));
    if (mb_strlen($text) > LR73_TEXT_MAX) throw new RuntimeException($label . ' je delší než ' . LR73_TEXT_MAX . ' znaků – zkraťte ho.');
    return $text;
}

/** Obsahuje text celé jméno žáka ze seznamu třídy? (reflexe je o hodině, ne o žácích) */
function lr73_roster_hit(string $classId, string $text): bool
{
    if ($text === '' || !function_exists('project_students_for_class')) return false;
    $hay = mb_strtolower($text);
    foreach (project_students_for_class($classId) as $s) {
        $label = mb_strtolower(trim((string)($s['label'] ?? '')));
        if (mb_strlen($label) >= LR73_NAME_MIN && str_contains($hay, $label)) return true;
    }
    return false;
}

/** Vybrané rychlé volby (jen známé klíče). @return list<string> */
function lr73_picks(mixed $value, array $allowed): array
{
    $out = [];
    foreach (is_array($value) ? $value : [] as $v) if (is_string($v) && isset($allowed[$v]) && !in_array($v, $out, true)) $out[] = $v;
    return $out;
}

/**
 * Ověří vstup formuláře proti modelu lekce (úseky a úkoly). Výjimka = česká hláška pro učitele.
 * @param array{timeline:list<array>,tasks:list<array>} $lesson
 */
function lr73_validate(array $input, string $classId, array $lesson): array
{
    $state = is_string($input['state'] ?? null) ? (string)$input['state'] : '';
    if (!isset(LR73_STATES[$state])) throw new RuntimeException('Vyberte, jak hodina proběhla (odučeno celé / část / neodučeno).');
    $seg = is_string($input['stop_seg'] ?? null) && preg_match('/^\d{1,2}$/', (string)$input['stop_seg']) === 1 ? (int)$input['stop_seg'] : -1;
    $task = is_string($input['stop_task'] ?? null) && preg_match('/^\d{1,2}$/', (string)$input['stop_task']) === 1 ? (int)$input['stop_task'] : -1;
    $timeline = array_values((array)($lesson['timeline'] ?? []));
    $tasks = array_values((array)($lesson['tasks'] ?? []));
    if ($seg >= count($timeline)) $seg = -1;
    if ($task >= count($tasks)) $task = -1;
    $stop = [];
    if ($seg >= 0) $stop[] = (int)$timeline[$seg]['from'] . '–' . (int)$timeline[$seg]['to'] . ' min · ' . (string)$timeline[$seg]['phase'];
    if ($task >= 0) $stop[] = 'úkol ' . ($task + 1) . ': ' . mb_strimwidth((string)($tasks[$task]['text'] ?? ''), 0, 80, '…');
    $texts = [];
    foreach (['worked' => 'Text „co fungovalo“', 'adjust' => 'Text „co upravit“', 'next' => 'Poznámka na příště'] as $key => $label) {
        $texts[$key] = lr73_clean_text($input[$key] ?? '', $label);
        if (lr73_roster_hit($classId, $texts[$key])) throw new RuntimeException($label . ' obsahuje jméno žáka. Reflexe je o hodině – jména ani známky sem nepište.');
    }
    return ['state' => $state, 'stop_seg' => $seg, 'stop_task' => $task, 'stop_label' => mb_substr(implode(' · ', $stop), 0, 160),
        'picks_worked' => lr73_picks($input['picks_worked'] ?? [], LR73_PICKS_WORKED), 'picks_adjust' => lr73_picks($input['picks_adjust'] ?? [], LR73_PICKS_ADJUST)] + $texts;
}

/** Retence a strop (čistá funkce): smaže záznamy starší než $days dní a nad strop na třídu. @return array{0:array,1:int} */
function lr73_purge(array $data, int $now, int $days = LR73_RETENTION_DAYS): array
{
    $limit = date('Y-m-d', $now - $days * 86400);
    $removed = 0;
    foreach ((array)($data['classes'] ?? []) as $classId => $rows) {
        $keep = [];
        foreach (is_array($rows) ? $rows : [] as $id => $row) {
            if (!is_array($row) || (string)($row['date'] ?? '') < $limit) { $removed++; continue; }
            $keep[(string)$id] = $row;
        }
        uasort($keep, static fn(array $a, array $b): int => strcmp((string)$b['date'], (string)$a['date']) ?: strcmp((string)$b['at'], (string)$a['at']));
        $removed += max(0, count($keep) - LR73_MAX_PER_CLASS);
        $data['classes'][$classId] = array_slice($keep, 0, LR73_MAX_PER_CLASS, true);
    }
    return [$data, $removed];
}

/** Uloží (nebo přepíše vlastní) reflexi lekce k datu. Vrací id záznamu. */
function lr73_save(string $classId, int $lesson, string $date, array $entry, string $authorHash, ?int $now = null): string
{
    if (!in_array($classId, LR73_CLASSES, true) || $lesson < 1 || $lesson > 28 || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1 || preg_match('/^[0-9a-f]{16}$/', $authorHash) !== 1) {
        throw new RuntimeException('Neplatná třída, lekce nebo datum reflexe.');
    }
    $now ??= time();
    $id = $lesson . '-' . $date . '-' . $authorHash;
    $row = ['lesson' => $lesson, 'date' => $date] + $entry + ['author' => $authorHash, 'at' => date(DATE_ATOM, $now)];
    storage_update(lr73_path(), static function (array $d) use ($classId, $id, $row, $now): array {
        $d['v'] = 1;
        $d['classes'][$classId][$id] = $row;
        return lr73_purge($d, $now)[0];
    });
    return $id;
}

/** Odučené hodiny třídy do data (výukové řádky kalendáře). @return list<array{date:string,lesson:int}> */
function lr73_taught(string $classId, string $today): array
{
    $out = [];
    if (!function_exists('adaptive_school_year_rows') || !function_exists('lt70_school_year')) return $out;
    foreach (adaptive_school_year_rows(lt70_school_year(), $classId) as $row) {
        if (!is_array($row) || (string)($row['status'] ?? '') !== 'teaching' || (int)($row['lesson_number'] ?? 0) <= 0) continue;
        if ((string)($row['date'] ?? '') <= $today) $out[] = ['date' => (string)$row['date'], 'lesson' => (int)$row['lesson_number']];
    }
    return $out;
}

/** Datum hodiny lekce: poslední výukový den s touto lekcí do dneška, jinak dnešek. */
function lr73_lesson_date(string $classId, int $lesson, string $today): string
{
    $date = $today;
    foreach (lr73_taught($classId, $today) as $row) if ($row['lesson'] === $lesson) $date = $row['date'];
    return $date;
}

/** Výchozí lekce reflexe: poslední odučená (do dneška), jinak lekce podle kalendáře. */
function lr73_default_lesson(string $classId, string $today, int $fallback): int
{
    $taught = lr73_taught($classId, $today);
    return $taught !== [] ? (int)end($taught)['lesson'] : $fallback;
}

/** Metrika: podíl odučených hodin s reflexí (z viditelných záznamů). @return array{taught:int,reflected:int,pct:int} */
function lr73_coverage(string $classId, string $today, ?array $visible = null): array
{
    $taught = lr73_taught($classId, $today);
    $done = [];
    foreach ($visible ?? lr73_visible($classId) as $row) if ((string)$row['state'] !== 'neoduceno') $done[(string)$row['date']] = true;
    $hit = count(array_filter($taught, static fn(array $t): bool => isset($done[$t['date']])));
    return ['taught' => count($taught), 'reflected' => $hit, 'pct' => $taught !== [] ? (int)round(100 * $hit / count($taught)) : 0];
}

/** POST lr73_save (politiku, CSRF a oprávnění už ověřil teacher.php). */
function lr73_teacher_handle_post(string $action): void
{
    if ($action !== 'lr73_save') throw new RuntimeException('Neznámá akce reflexe.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!in_array($classId, LR73_CLASSES, true) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    $lesson = is_string($_POST['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_POST['lesson']) === 1 ? (int)$_POST['lesson'] : 0;
    if ($lesson < 1 || $lesson > 28) throw new RuntimeException('Neplatná lekce.');
    $query = ['tab' => 'reflexe73', 'class' => $classId, 'lesson' => (string)$lesson];
    $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
    try {
        $entry = lr73_validate($_POST, $classId, lm71_lesson($classId, $lesson, $module));
        lr73_save($classId, $lesson, lr73_lesson_date($classId, $lesson, date('Y-m-d')), $entry, lr73_author_hash());
        $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => 'Reflexe lekce ' . $lesson . ' je uložená. Dnešní hodina ji příště ukáže jako „Minule“.'];
    } catch (RuntimeException $e) {
        $_SESSION['teacher_export_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
    }
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
