<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · schvalování obsahu lekcí (obsahová stopa, vlna 1).
 *
 * Návrh obsahu (overlay lesson_content_v72_*.php, čte lm71) se žákům ukáže až po schválení učitelem s rozsahem třídy.
 * Schválení se váže na otisk obsahu (lc72_overlay_hash): když se overlay po schválení změní, stav je „změněno po schválení“
 * a žák vidí znovu původní lekci, dokud učitel novou verzi neschválí. Žák nikdy nevidí neschválený návrh.
 *
 * Úložiště storage/lesson_approvals_v72.json.php = {"v":1,"lessons":{třída:{číslo:{status,hash,at,by,note}}},"log":[…]}
 * – zápis jen přes storage_update (zámek přes čtení i zápis); log je append-only se stropem LC72_LOG_MAX.
 * Neobsahuje data žáků (jen učitel, třída, číslo lekce, otisk a důvod vrácení do LC72_NOTE_MAX znaků).
 *
 * POST akce (modul `schvalovani` v teacher_v58.php): lc72_approve, lc72_return – CSRF a ověření teacher.php,
 * politika teacher59 (třída povinná a v rozsahu), oprávnění content.manage (asistent jen čte). Neznámá lc72_* = zákaz.
 *
 * Rozhodnutí školy čekají (výchozí bezpečné hodnoty, viz CHANGELOG_V72.md): konstanty níže.
 */

/** Domácí příprava: nejvýš minut týdně na lekci (výchozí bezpečná hodnota, čeká na rozhodnutí školy). */
const LC72_HOMEWORK_MAX_MIN = 30;
/** Domácí příprava je vždy volitelná (čeká na rozhodnutí školy). */
const LC72_HOMEWORK_OPTIONAL = true;
/** Exit ticket je čistě formativní – nikdy nevstupuje do známky ani návrhu hodnocení (čeká na rozhodnutí školy). */
const LC72_EXIT_FORMATIVE_ONLY = true;
/** Vazba na ŠVP: dokud škola nedodá dokument, platí „chybí vazba“ (nevymýšlíme kódy ŠVP). */
const LC72_SVP_DEFAULT = 'chybí vazba';
/** Sekce „AI prompty pro studenta“ v materials/ se nikam nově neodkazují (čeká na rozhodnutí školy). */
const LC72_LINK_AI_PROMPTS = false;
/** Lekce vlny 1 (obsahová stopa v72). */
const LC72_WAVE1 = [5, 16];
const LC72_NOTE_MAX = 300;
const LC72_LOG_MAX = 500;
/** T-14: lekce, která se učí do tolika dní, má být schválená. */
const LC72_T14_DAYS = 14;
const LC72_ACTIONS = ['lc72_approve', 'lc72_return'];

require_once __DIR__ . '/lesson_model_v71.php';

function lc72_path(): string
{
    return STORAGE_DIR . '/lesson_approvals_v72.json.php';
}

/** Školní rok (kalendář lekcí) – jednou za požadavek. */
function lc72_school_year(): array
{
    static $year = null;
    if ($year === null) {
        $loaded = require __DIR__ . '/school_year.php';
        $year = is_array($loaded) ? $loaded : [];
    }
    return $year;
}

/** Overlay lekce ze souborů obsahové stopy (bez interního `_file`), prázdné pole = lekce nemá návrh. */
function lc72_overlay(string $classId, int $number): array
{
    if (!lm71_class_ok($classId) || $number < 1 || $number > LM71_LESSONS) return [];
    $ov = (array)(lm71_raw($classId)['overlay']['lessons'][$number] ?? []);
    unset($ov['_file']);
    return $ov;
}

/** Čísla lekcí třídy, které mají návrh obsahu. @return list<int> */
function lc72_overlay_lessons(string $classId): array
{
    if (!lm71_class_ok($classId)) return [];
    $numbers = array_map('intval', array_keys((array)(lm71_raw($classId)['overlay']['lessons'] ?? [])));
    sort($numbers);
    return $numbers;
}

/** Otisk obsahu návrhu (16 hex) – mění se s jakoukoli změnou obsahu; stav a datum revize se nepočítají. */
function lc72_overlay_hash(array $overlay): string
{
    unset($overlay['_file'], $overlay['status'], $overlay['reviewed_at']);
    $norm = static function ($v) use (&$norm) {
        if (!is_array($v)) return $v;
        if (array_is_list($v)) return array_map($norm, $v);
        ksort($v);
        return array_map($norm, $v);
    };
    return substr(hash('sha256', (string)json_encode($norm($overlay), JSON_UNESCAPED_UNICODE)), 0, 16);
}

/** Záznamy rozhodnutí: [třída => [číslo => záznam]]. */
function lc72_records(): array
{
    $data = storage_read(lc72_path(), false);
    return is_array($data['lessons'] ?? null) ? $data['lessons'] : [];
}

/**
 * Efektivní stav lekce: puvodni (bez návrhu) | navrh | schvaleno | vraceno | zmeneno (schváleno, ale obsah se pak změnil).
 * @return array{status:string,label:string,hash:string,at:string,by:string,note:string}
 */
function lc72_status(string $classId, int $number, ?array $records = null): array
{
    $ov = lc72_overlay($classId, $number);
    if ($ov === []) return ['status' => 'puvodni', 'label' => lc72_status_label('puvodni'), 'hash' => '', 'at' => '', 'by' => '', 'note' => ''];
    $hash = lc72_overlay_hash($ov);
    $rec = ($records ?? lc72_records())[$classId][(string)$number] ?? null;
    $status = 'navrh';
    if (is_array($rec)) {
        $same = (string)($rec['hash'] ?? '') === $hash;
        if ((string)($rec['status'] ?? '') === 'schvaleno') $status = $same ? 'schvaleno' : 'zmeneno';
        if ((string)($rec['status'] ?? '') === 'vraceno' && $same) $status = 'vraceno';
    }
    return ['status' => $status, 'label' => lc72_status_label($status), 'hash' => $hash, 'at' => is_array($rec) ? (string)($rec['at'] ?? '') : '',
        'by' => is_array($rec) ? (string)($rec['by'] ?? '') : '', 'note' => is_array($rec) && $status === 'vraceno' ? (string)($rec['note'] ?? '') : ''];
}

function lc72_status_label(string $status): string
{
    return ['puvodni' => 'původní obsah', 'navrh' => 'návrh (neschváleno)', 'schvaleno' => 'schváleno', 'vraceno' => 'vráceno k úpravě',
        'zmeneno' => 'změněno po schválení – schvalte znovu'][$status] ?? 'původní obsah';
}

/** Overlay, který smí vidět žák: jen schválená a nezměněná verze, jinak null (žák vidí původní lekci). */
function lc72_student_overlay(string $classId, int $number): ?array
{
    // Rychlá cesta (každé zobrazení lekce žákem): bez záznamu „schváleno“ se model lekce vůbec nenačítá.
    $rec = lc72_records()[$classId][(string)$number] ?? null;
    if (!is_array($rec) || (string)($rec['status'] ?? '') !== 'schvaleno') return null;
    $ov = lc72_overlay_light($classId, $number);
    return $ov !== [] && hash_equals((string)($rec['hash'] ?? ''), lc72_overlay_hash($ov)) ? $ov : null;   // jiný otisk = „změněno po schválení“
}

/**
 * Overlay lekce jen ze souborů obsahové stopy dané třídy (bez celé vrstvy modelu lm71 a její cache) – pro žáka.
 * Soubory jiné třídy (…_<kód jiné třídy>_… / …_<kód>.php) se přeskočí; výsledek je shodný s lc72_overlay() (hlídá audit v72).
 */
function lc72_overlay_light(string $classId, int $number): array
{
    static $memo = [];
    if (!lm71_class_ok($classId)) return [];
    if (!isset($memo[$classId])) {
        $others = array_map(static fn(string $c): string => substr($c, 6), array_diff(LM71_CLASSES, [$classId]));
        $acc = ['lessons' => [], 'days' => [], 'files' => []];
        foreach (lm71_overlay_files() as $file) {
            foreach ($others as $short) if (str_contains($file, '_' . $short . '_') || str_ends_with($file, '_' . $short . '.php')) continue 2;
            $loaded = require __DIR__ . '/' . $file;
            if (is_array($loaded)) $acc = lm71_overlay_merge($acc, $file, $loaded);
        }
        $memo[$classId] = (array)($acc['lessons'][$classId] ?? []);
    }
    $ov = (array)($memo[$classId][$number] ?? []);
    unset($ov['_file']);
    return $ov;
}

/** Datum výuky lekce podle kalendáře třídy (s výjimkami), '' = není v kalendáři. */
function lc72_lesson_dates(string $classId): array
{
    $out = [];
    foreach (adaptive_school_year_rows(lc72_school_year(), $classId) as $row) {
        $n = (int)($row['lesson_number'] ?? 0);
        if ($n >= 1 && !isset($out[$n])) $out[$n] = (string)($row['date'] ?? '');
    }
    return $out;
}

/**
 * Přehled lekcí s návrhem pro třídu: číslo, název, datum, stav, úplnost, T-14 (dní do výuky, upozornění).
 * @return list<array<string,mixed>>
 */
function lc72_overview(string $classId, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $records = lc72_records();
    $dates = lc72_lesson_dates($classId);
    $out = [];
    foreach (lc72_overlay_lessons($classId) as $n) {
        $model = lm71_lesson($classId, $n);
        $st = lc72_status($classId, $n, $records);
        $date = (string)($dates[$n] ?? '');
        $days = $date !== '' ? (int)floor(((int)strtotime($date) - (int)strtotime($today)) / 86400) : null;
        $out[] = ['number' => $n, 'title' => (string)$model['title'], 'date' => $date, 'days' => $days, 'status' => $st,
            'completeness' => $model['completeness'], 't14' => $days !== null && $days >= 0 && $days <= LC72_T14_DAYS && $st['status'] !== 'schvaleno'];
    }
    return $out;
}

/**
 * Rozhodnutí učitele (čistá validace + zápis přes storage_update). $hash = otisk verze, kterou učitel viděl.
 * @return array{status:string,hash:string}
 */
function lc72_decide(string $action, string $classId, int $number, string $hash, string $note, string $by, ?int $now = null): array
{
    if (!in_array($action, LC72_ACTIONS, true)) throw new RuntimeException('Neznámá akce schvalování.');
    $ov = lc72_overlay($classId, $number);
    if ($ov === []) throw new RuntimeException('Lekce ' . $number . ' nemá návrh obsahu ke schválení.');
    $current = lc72_overlay_hash($ov);
    if (!hash_equals($current, $hash)) throw new RuntimeException('Obsah lekce ' . $number . ' se mezitím změnil. Zkontrolujte novou verzi a rozhodněte znovu.');
    $note = trim((string)preg_replace('/\s+/u', ' ', $note));
    if ($action === 'lc72_return' && $note === '') throw new RuntimeException('Napište krátce, co je potřeba upravit (nejvýš ' . LC72_NOTE_MAX . ' znaků).');
    if (mb_strlen($note) > LC72_NOTE_MAX) throw new RuntimeException('Důvod vrácení může mít nejvýš ' . LC72_NOTE_MAX . ' znaků.');
    $status = $action === 'lc72_approve' ? 'schvaleno' : 'vraceno';
    $at = date(DATE_ATOM, $now ?? time());
    $by = mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, 60);
    $record = ['status' => $status, 'hash' => $current, 'at' => $at, 'by' => $by, 'note' => $status === 'vraceno' ? $note : ''];
    storage_update(lc72_path(), static function (array $d) use ($classId, $number, $record): array {
        $d['v'] = 1;
        $d['lessons'] = is_array($d['lessons'] ?? null) ? $d['lessons'] : [];
        $d['lessons'][$classId][(string)$number] = $record;
        $log = array_values(array_filter((array)($d['log'] ?? []), 'is_array'));
        $log[] = ['class' => $classId, 'lesson' => $number, 'status' => $record['status'], 'hash' => $record['hash'], 'at' => $record['at'], 'by' => $record['by']];
        $d['log'] = array_slice($log, -LC72_LOG_MAX);
        return $d;
    });
    return ['status' => $status, 'hash' => $current];
}

/** POST lc72_approve / lc72_return (politiku, CSRF a oprávnění už ověřil teacher.php). */
function lc72_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    $number = is_string($_POST['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_POST['lesson']) === 1 ? (int)$_POST['lesson'] : 0;
    if (!lm71_class_ok($classId) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $res = lc72_decide($action, $classId, $number, is_string($_POST['hash'] ?? null) ? (string)$_POST['hash'] : '', is_string($_POST['note'] ?? null) ? (string)$_POST['note'] : '', $by);
    $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $res['status'] === 'schvaleno'
        ? 'Lekce ' . $number . ' je schválená. Žáci teď uvidí cíl, exit ticket a domácí přípravu.'
        : 'Lekce ' . $number . ' je vrácená k úpravě. Žáci dál vidí původní obsah.'];
    $query = ['tab' => 'schvalovani', 'class' => $classId, 'lesson' => (string)$number];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
