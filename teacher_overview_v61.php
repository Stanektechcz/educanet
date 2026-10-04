<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v61 · Učitelský přehled třídy na jedné obrazovce (část E).
 *
 *   - kdo zaostává: žádná aktivita ≥ OV61_INACTIVE_DAYS dní (nebo žádná vůbec), nebo nízké mastery
 *     (< OV61_LOW_MASTERY % u žáka, který už nějakou dovednost začal),
 *   - co čeká: nová hlášení žáků, přihlášky na projekty (stav „interested“), nedávné nevrácené nákupy v obchodě,
 *   - hromadné potvrzení hlášení (jeden POST, každé id zvlášť přes fb60_decide, rozsah per položka),
 *   - export přehledu třídy do CSV (jen třída v rozsahu učitele, ochrana proti CSV injection, UTF-8 BOM).
 *
 * Poslední aktivita žáka = nejnovější z: profil učení (updated_at / události, bez odměn učitele fb60:*),
 * události Linux Labu (lab_v57_events), výsledky testů/labů/zkoušek (teacher_class_activity_index).
 * Rozsah: každá funkce, která čte data třídy, ji vrací jen pro třídy z teacher59_allowed_class_ids()
 * (v režimu bez účtů všechny); neznámá nebo cizí třída = prázdný výsledek.
 */

const OV61_INACTIVE_DAYS = 7;
const OV61_LOW_MASTERY = 40.0;
const OV61_PURCHASE_DAYS = 14;
const OV61_BULK_MAX = 50;
const OV61_LIST_MAX = 30;

/** @return list<string> třídy v rozsahu přihlášeného učitele */
function ov61_allowed_classes(): array
{
    $all = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
    if (!function_exists('teacher59_allowed_class_ids')) return $all;
    return array_values(array_intersect($all, teacher59_allowed_class_ids()));
}

function ov61_can_class(string $classId): bool
{
    return $classId !== '' && in_array($classId, ov61_allowed_classes(), true);
}

/** Vybere třídu k zobrazení: požadovanou, je-li v rozsahu, jinak první povolenou ('' = žádná). */
function ov61_pick_class(string $requested): string
{
    $allowed = ov61_allowed_classes();
    if (in_array($requested, $allowed, true)) return $requested;
    return $allowed[0] ?? '';
}

/** Posledních 24 hex znaků klíče žáka (`třída:student:H` i `třída:s:H`) – společný jmenovatel všech vrstev. */
function ov61_hash(string $studentKey): string
{
    return preg_match('/:(?:student|s):([a-f0-9]{24})$/D', $studentKey, $m) === 1 ? $m[1] : '';
}

function ov61_ts(mixed $value): int
{
    return is_string($value) && $value !== '' ? (int)(strtotime($value) ?: 0) : 0;
}

/**
 * Aktivita z profilu učení. Potvrzení hlášení učitelem zapisuje událost `fb60:*` a tím posune updated_at –
 * takový zápis se za aktivitu žáka nepočítá (rozpozná se shodou času události a updated_at).
 */
function ov61_profile_activity(array $profile): int
{
    $updated = ov61_ts($profile['updated_at'] ?? '');
    $lastOwn = 0;
    $lastTeacher = 0;
    foreach ((array)($profile['events'] ?? []) as $key => $event) {
        $at = is_array($event) ? ov61_ts($event['at'] ?? '') : 0;
        if (str_starts_with((string)$key, 'fb60:')) $lastTeacher = max($lastTeacher, $at);
        else $lastOwn = max($lastOwn, $at);
    }
    if ($lastTeacher > 0 && abs($updated - $lastTeacher) <= 5) return $lastOwn;
    return max($updated, $lastOwn);
}

/** @return array<string,int> hash žáka => čas poslední aktivity (0 = žádná) pro jednu třídu */
function ov61_activity_map(string $classId, array $students): array
{
    if (!ov61_can_class($classId)) return [];
    $map = [];
    foreach (array_keys($students) as $key) {
        $hash = ov61_hash((string)$key);
        if ($hash !== '') $map[$hash] = 0;
    }
    $bump = static function (string $hash, int $ts) use (&$map): void {
        if ($ts > 0 && isset($map[$hash]) && $ts > $map[$hash]) $map[$hash] = $ts;
    };
    foreach (storage_read(learning_profiles_path()) as $key => $profile) {
        if (!is_array($profile) || !str_starts_with((string)$key, $classId . ':s:')) continue;
        $bump(ov61_hash((string)$key), ov61_profile_activity($profile));
    }
    foreach (storage_read(STORAGE_DIR . '/lab_v57_events.json.php') as $event) {
        if (!is_array($event) || (string)($event['class_id'] ?? '') !== $classId) continue;
        $bump(ov61_hash((string)($event['student_key'] ?? '')), ov61_ts($event['at'] ?? ''));
    }
    if (function_exists('teacher_class_activity_index')) {
        foreach (teacher_class_activity_index($classId) as $key => $row) $bump(ov61_hash((string)$key), (int)($row['last_activity'] ?? 0));
    }
    return $map;
}

/** @return array<string,array{overall:float,started:int}> hash žáka => mastery (jen žáci, kteří něco začali, mají started > 0) */
function ov61_mastery_map(string $classId): array
{
    if (!ov61_can_class($classId) || !function_exists('skill_class_matrix')) return [];
    $out = [];
    foreach (skill_class_matrix($classId) as $row) {
        $hash = ov61_hash((string)($row['student_key'] ?? ''));
        if ($hash === '') continue;
        $started = 0;
        foreach ((array)($row['branches'] ?? []) as $branch) $started += (int)($branch['skills_started'] ?? 0);
        $out[$hash] = ['overall' => (float)($row['overall'] ?? 0), 'started' => $started];
    }
    return $out;
}

/**
 * Pravidla zaostávání pro jednoho žáka (čistá funkce).
 * @return list<string> důvody (prázdné = nezaostává)
 */
function ov61_lag_reasons(int $lastActivity, ?float $mastery, bool $hasMasteryData, int $now): array
{
    $reasons = [];
    if ($lastActivity <= 0) {
        $reasons[] = 'Zatím žádná aktivita';
    } else {
        $days = max(0, (int)floor(($now - $lastActivity) / 86400));
        if ($days >= OV61_INACTIVE_DAYS) $reasons[] = 'Bez aktivity ' . $days . ' ' . ($days >= 5 ? 'dní' : ($days === 1 ? 'den' : 'dny'));
    }
    if ($hasMasteryData && $mastery !== null && $mastery < OV61_LOW_MASTERY) {
        $reasons[] = 'Nízké mastery (' . number_format($mastery, 0, ',', ' ') . ' %)';
    }
    return $reasons;
}

/** @return array<string,string> hash žáka => jméno (z kmenového soupisu třídy) */
function ov61_labels(array $students): array
{
    $out = [];
    foreach ($students as $key => $student) {
        $hash = ov61_hash((string)$key);
        if ($hash !== '') $out[$hash] = (string)($student['label'] ?? '');
    }
    return $out;
}

/**
 * Žáci třídy s poslední aktivitou, mastery a důvody zaostávání (nejdřív ti, kdo zaostávají, pak podle jména).
 * @return list<array{hash:string,label:string,last:int,days:?int,mastery:?float,lagging:bool,reasons:list<string>}>
 */
function ov61_students(string $classId, ?int $now = null): array
{
    if (!ov61_can_class($classId)) return [];
    $now ??= time();
    $students = project_students_for_class($classId);
    $activity = ov61_activity_map($classId, $students);
    $mastery = ov61_mastery_map($classId);
    $rows = [];
    foreach (ov61_labels($students) as $hash => $label) {
        $last = (int)($activity[$hash] ?? 0);
        $m = $mastery[$hash] ?? null;
        $hasData = $m !== null && $m['started'] > 0;
        $reasons = ov61_lag_reasons($last, $hasData ? $m['overall'] : null, $hasData, $now);
        $rows[] = ['hash' => $hash, 'label' => $label, 'last' => $last, 'days' => $last > 0 ? max(0, (int)floor(($now - $last) / 86400)) : null,
            'mastery' => $hasData ? round($m['overall'], 1) : null, 'lagging' => $reasons !== [], 'reasons' => $reasons];
    }
    usort($rows, static fn(array $a, array $b): int => ((int)$b['lagging'] <=> (int)$a['lagging']) ?: strnatcasecmp($a['label'], $b['label']));
    return $rows;
}

/**
 * Nová hlášení třídy (stav „new“). Rozsah drží fb60_list(): třída musí být v povolených třídách.
 * @return list<array<string,mixed>>
 */
function ov61_pending_reports(string $classId): array
{
    if (!ov61_can_class($classId) || !function_exists('fb60_list')) return [];
    $isAdmin = function_exists('teacher59_is_admin') && teacher59_is_admin();
    return fb60_list([$classId], $isAdmin, ['status' => 'new', 'class' => $classId]);
}

/** @return list<array<string,mixed>> přihlášky na projekty ve třídě čekající na rozhodnutí (stav „interested“) */
function ov61_pending_applications(string $classId): array
{
    if (!ov61_can_class($classId) || !function_exists('proj60_applications_for_classes')) return [];
    $rows = [];
    foreach (proj60_applications_for_classes([$classId]) as $app) {
        if ((string)($app['class_id'] ?? '') === $classId && (string)($app['status'] ?? '') === 'interested') $rows[] = $app;
    }
    return $rows;
}

/**
 * Nevrácené nákupy v obchodě za posledních OV61_PURCHASE_DAYS dní (kandidáti na vrácení).
 * @return list<array{hash:string,title:string,cost:int,at:string}>
 */
function ov61_recent_purchases(string $classId, ?int $now = null): array
{
    if (!ov61_can_class($classId) || !function_exists('pts53_path')) return [];
    $since = ($now ?? time()) - OV61_PURCHASE_DAYS * 86400;
    $catalog = function_exists('mkt60_catalog_all') ? mkt60_catalog_all() : [];
    $rows = [];
    foreach (storage_read(pts53_path()) as $walletKey => $wallet) {
        if (!is_array($wallet) || !str_starts_with((string)$walletKey, $classId . '|')) continue;
        $hash = ov61_hash(substr((string)$walletKey, strlen($classId) + 1));
        foreach ((array)($wallet['purchases'] ?? []) as $purchaseKey => $purchase) {
            if (!is_array($purchase) || !str_starts_with((string)$purchaseKey, 'mkt:')) continue;
            if (ov61_ts($purchase['at'] ?? '') < $since || isset($wallet['awards']['refund:' . $purchaseKey])) continue;
            $item = is_array($catalog[(string)($purchase['item_id'] ?? '')] ?? null) ? $catalog[(string)$purchase['item_id']] : null;
            $rows[] = ['hash' => $hash, 'title' => $item !== null ? (string)$item['title'] : (string)($purchase['reason'] ?? ''),
                'cost' => (int)($purchase['cost'] ?? 0), 'at' => (string)($purchase['at'] ?? '')];
        }
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
    return $rows;
}

/** Souhrn třídy pro obrazovku i CSV. */
function ov61_overview(string $classId, ?int $now = null): array
{
    $students = ov61_students($classId, $now);
    return [
        'class_id' => $classId, 'students' => $students,
        'lagging' => array_values(array_filter($students, static fn(array $r): bool => $r['lagging'])),
        'reports' => ov61_pending_reports($classId), 'applications' => ov61_pending_applications($classId),
        'purchases' => ov61_recent_purchases($classId, $now),
    ];
}

// ---------------------------------------------------------------------------
// Hromadné potvrzení hlášení
// ---------------------------------------------------------------------------

/**
 * Hromadné potvrzení: každé id zvlášť (rozsah → stav → fb60_decide s výchozí odměnou podle typu).
 * Idempotentní: už potvrzené hlášení se nezmění a odměna se nepřipíše znovu (fb60_decide odměnu drží jen jednou).
 * @param list<mixed> $ids
 * @return list<array{id:string,title:string,outcome:string,text:string}> výsledek po položkách
 */
function ov61_bulk_confirm(array $ids, string $classId, string $actor, bool $isAdmin): array
{
    $clean = [];
    foreach ($ids as $id) {
        if (is_string($id) && preg_match('/^fb60_[a-f0-9]{12}$/D', $id) === 1) $clean[$id] = true;
    }
    $results = [];
    foreach (array_slice(array_keys($clean), 0, OV61_BULK_MAX) as $id) $results[] = ov61_confirm_one((string)$id, $classId, $actor, $isAdmin);
    return $results;
}

function ov61_result(string $id, string $title, string $outcome, string $text): array
{
    return ['id' => $id, 'title' => $title, 'outcome' => $outcome, 'text' => $text];
}

function ov61_confirm_one(string $id, string $classId, string $actor, bool $isAdmin): array
{
    $classes = function_exists('teacher59_entity_classes') ? teacher59_entity_classes('fb60_report', $id) : null;
    if ($classes === null) return ov61_result($id, '', 'missing', 'Hlášení neexistuje.');
    if (function_exists('teacher59_classes_ok') && !teacher59_classes_ok($classes, 'all')) return ov61_result($id, '', 'forbidden', 'Mimo váš rozsah tříd – nezměněno.');
    $row = fb60_item($id);
    $title = $row !== null ? (string)($row['title'] ?? '') : '';
    if ($row === null) return ov61_result($id, '', 'missing', 'Hlášení neexistuje.');
    if ((string)$row['class_id'] !== $classId) return ov61_result($id, $title, 'wrong_class', 'Patří do jiné třídy než vybraná – nezměněno.');
    $status = (string)($row['status'] ?? 'new');
    if ($status === 'confirmed') return ov61_result($id, $title, 'already', 'Už potvrzeno dříve – odměna se znovu nepřipíše.');
    if ($status !== 'new') return ov61_result($id, $title, 'skipped', 'Už vyřízeno jiným rozhodnutím – nezměněno.');
    $defaults = fb60_reward_defaults()[(string)($row['type'] ?? '')] ?? ['points' => 3, 'xp' => 30];
    $decision = fb60_decide($id, 'confirm', (int)$defaults['points'], (int)$defaults['xp'], '', $actor, $isAdmin);
    if (!$decision['ok']) return ov61_result($id, $title, 'error', 'Potvrzení se nepodařilo.');
    return ov61_result($id, $title, 'confirmed', 'Potvrzeno: ' . (int)$decision['points'] . ' b a ' . (int)$decision['xp'] . ' XP.');
}

/** Počty výsledků podle typu pro souhrnnou hlášku. */
function ov61_bulk_summary(array $results): string
{
    if ($results === []) return 'Nebylo vybráno žádné hlášení.';
    $labels = ['confirmed' => 'potvrzeno', 'already' => 'už dříve potvrzeno', 'skipped' => 'už vyřízeno', 'forbidden' => 'mimo rozsah',
        'wrong_class' => 'jiná třída', 'missing' => 'nenalezeno', 'error' => 'chyba'];
    $counts = [];
    foreach ($results as $r) $counts[$r['outcome']] = ($counts[$r['outcome']] ?? 0) + 1;
    $parts = [];
    foreach ($labels as $key => $label) if (!empty($counts[$key])) $parts[] = $counts[$key] . '× ' . $label;
    return 'Hromadné potvrzení: ' . implode(', ', $parts) . '.';
}

/** POST akce modulu (prefix ov61_). Oprávnění a rozsah třídy už ověřil teacher.php (politika + content.manage). */
function ov61_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if ($action === 'ov61_bulk_confirm') {
        $ids = is_array($_POST['ids'] ?? null) ? array_values($_POST['ids']) : [];
        $actor = function_exists('teacher59_current_id') ? (teacher59_current_id() ?? 'teacher') : 'teacher';
        $results = ov61_bulk_confirm($ids, $classId, $actor, function_exists('teacher59_is_admin') && teacher59_is_admin());
        $_SESSION['flash'] = ov61_bulk_summary($results);
        $_SESSION['ov61_result'] = array_slice($results, 0, OV61_BULK_MAX);
    }
    redirect_to('teacher.php?tab=prehled&class=' . rawurlencode($classId));
}

// ---------------------------------------------------------------------------
// CSV export
// ---------------------------------------------------------------------------

/** Buňka CSV bez možnosti spuštění vzorce v tabulkovém procesoru (=, +, -, @, tabulátor, CR na začátku). */
function ov61_csv_cell(mixed $value): string
{
    $text = str_replace(["\r\n", "\r", "\n"], ' ', (string)$value);
    return $text !== '' && in_array($text[0], ['=', '+', '-', '@', "\t"], true) ? "'" . $text : $text;
}

/** @return list<list<string>> řádky CSV (první je záhlaví) pro jednu třídu; jen třída v rozsahu */
function ov61_csv_rows(string $classId, ?int $now = null): array
{
    $header = ['Třída', 'Žák', 'Poslední aktivita', 'Dní bez aktivity', 'Mastery (%)', 'Zaostává', 'Důvod', 'Nová hlášení', 'Přihlášky na projekty', 'Nevrácené nákupy (14 dní)'];
    if (!ov61_can_class($classId)) return [$header];
    $data = ov61_overview($classId, $now);
    $count = static function (array $rows) use ($data): array {
        $out = [];
        foreach ($rows as $row) {
            $hash = isset($row['hash']) ? (string)$row['hash'] : ov61_hash((string)($row['student_key'] ?? ''));
            if ($hash !== '') $out[$hash] = ($out[$hash] ?? 0) + 1;
        }
        return $out;
    };
    $reports = $count($data['reports']);
    $apps = $count($data['applications']);
    $buys = $count($data['purchases']);
    $rows = [$header];
    foreach ($data['students'] as $s) {
        $rows[] = [$classId, $s['label'], $s['last'] > 0 ? date('Y-m-d', $s['last']) : '', $s['days'] === null ? '' : (string)$s['days'],
            $s['mastery'] === null ? '' : number_format((float)$s['mastery'], 1, ',', ''), $s['lagging'] ? 'ano' : 'ne', implode('; ', $s['reasons']),
            (string)($reports[$s['hash']] ?? 0), (string)($apps[$s['hash']] ?? 0), (string)($buys[$s['hash']] ?? 0)];
    }
    return $rows;
}

/** Tělo CSV: UTF-8 BOM, oddělovač „;“ (české Excel), všechny buňky přes ov61_csv_cell(). */
function ov61_csv_body(string $classId, ?int $now = null): string
{
    $fp = fopen('php://temp', 'r+');
    fwrite($fp, "\xEF\xBB\xBF");
    foreach (ov61_csv_rows($classId, $now) as $row) fputcsv($fp, array_map('ov61_csv_cell', $row), ';', '"', '');
    rewind($fp);
    $body = (string)stream_get_contents($fp);
    fclose($fp);
    return $body;
}

/** GET ?tab=prehled&export=1&class=… (politika `prehled|export`: třída povinná a v rozsahu). */
function ov61_export_csv(string $classId): never
{
    if (!ov61_can_class($classId)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Tato třída není ve vašem rozsahu.');
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="prehled-' . preg_replace('/[^a-z0-9]/', '', substr($classId, 6)) . '-' . date('Ymd') . '.csv"');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo ov61_csv_body($classId);
    exit;
}
