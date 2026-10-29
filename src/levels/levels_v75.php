<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · diferenciace v hodině: každá hlavní aktivita lekce má úrovně Základ / Standard / Výzva.
 *
 * Obsah úrovní (levels_v75_content_<třída>.php, L1–L16 všech tříd) je NÁVRH – žák ho uvidí až po schválení učitelem
 * (záložka `urovne75`, akce lv75_approve / lv75_return; schválení drží otisk obsahu, změna obsahu = znovu návrh).
 * Doporučení úrovně: z posledních exit ticketů (v72) a úspěšnosti v bance úloh (v75) za 14 dní, vždy s vysvětleným důvodem;
 * žák si úroveň vybírá sám (doporučení je jen nabídka, nic se nevynucuje ani nehodnotí).
 * Úložiště: storage/levels_v75_status.json.php = {"v":1,"classes":{třída:{lekce:{status,hash,at,by,note}}}} a
 * storage/levels_v75_picks.json.php = {"v":1,"classes":{třída:{lekce:{hash žáka:{l,s,at}}}}} – bez jmen, zápis jen storage_update.
 */

const LV75_LEVELS = ['zaklad' => 'Základ', 'standard' => 'Standard', 'vyzva' => 'Výzva'];
const LV75_LESSONS_MAX = 28;   // v76: vlna 2 (L17–L28) v levels_v76_content_<třída>.php
const LV75_CLASS_FILES = ['class_1a' => 'src/levels/levels_v75_content_1a.php', 'class_2a' => 'src/levels/levels_v75_content_2a.php', 'class_3a' => 'src/levels/levels_v75_content_3a.php', 'class_4a' => 'src/levels/levels_v75_content_4a.php'];
/** v76: návrh úrovní L17–L28 (stejný tvar; čte lv75_content, schvaluje se stejně v záložce urovne75). */
const LV76_CLASS_FILES = ['class_1a' => 'src/levels/levels_v76_content_1a.php', 'class_2a' => 'src/levels/levels_v76_content_2a.php', 'class_3a' => 'src/levels/levels_v76_content_3a.php', 'class_4a' => 'src/levels/levels_v76_content_4a.php'];
const LV75_ACTIONS = ['lv75_approve', 'lv75_return'];
const LV75_NOTE_MAX = 300;
const LV75_RECENT_DAYS = 14;

function lv75_status_path(): string
{
    return STORAGE_DIR . '/levels_v75_status.json.php';
}

function lv75_picks_path(): string
{
    return STORAGE_DIR . '/levels_v75_picks.json.php';
}

/** Návrh úrovní třídy: lekce → aktivita a úrovně. @return array<int,array<string,mixed>> */
function lv75_content(string $classId): array
{
    static $memo = [];
    if (!isset(LV75_CLASS_FILES[$classId])) return [];
    if (isset($memo[$classId])) return $memo[$classId];
    $data = require dirname(__DIR__, 2) . '/' . LV75_CLASS_FILES[$classId];
    $wave2 = isset(LV76_CLASS_FILES[$classId]) && is_file(dirname(__DIR__, 2) . '/' . LV76_CLASS_FILES[$classId]) ? require dirname(__DIR__, 2) . '/' . LV76_CLASS_FILES[$classId] : [];
    $out = [];
    foreach ([(array)$data, (array)$wave2] as $part) foreach ($part as $n => $row) if (is_array($row) && (int)$n >= 1 && (int)$n <= LV75_LESSONS_MAX && !isset($out[(int)$n])) $out[(int)$n] = $row;
    ksort($out);
    return $memo[$classId] = $out;
}

function lv75_hash(array $row): string
{
    return substr(sha1((string)json_encode($row)), 0, 12);
}

/** Stav úrovní lekce: navrh | schvaleno | vraceno | zmeneno (schváleno, ale obsah se od té doby změnil – žák ho nevidí). */
function lv75_status(string $classId, int $lesson, ?array $data = null): array
{
    $content = lv75_content($classId)[$lesson] ?? null;
    if ($content === null) return ['status' => 'chybi', 'at' => '', 'by' => '', 'note' => ''];
    $row = (array)(((($data ?? storage_read(lv75_status_path(), false))['classes'] ?? [])[$classId] ?? [])[(string)$lesson] ?? []);
    $status = in_array((string)($row['status'] ?? ''), ['schvaleno', 'vraceno'], true) ? (string)$row['status'] : 'navrh';
    if ($status === 'schvaleno' && (string)($row['hash'] ?? '') !== lv75_hash($content)) $status = 'zmeneno';
    return ['status' => $status, 'at' => (string)($row['at'] ?? ''), 'by' => (string)($row['by'] ?? ''), 'note' => (string)($row['note'] ?? '')];
}

/** Úrovně lekce pro žáka – jen schválené (jinak null). */
function lv75_student_levels(string $classId, int $lesson): ?array
{
    if (!is_file(lv75_status_path())) return null;   // bez schválení žádné čtení úložiště
    if (lv75_status($classId, $lesson, function_exists('storage_read_request') ? storage_read_request(lv75_status_path(), false) : null)['status'] !== 'schvaleno') return null;
    return lv75_content($classId)[$lesson] ?? null;
}

/** Schválení / vrácení úrovní lekcí (handler ověřil rozsah). Vrací počet změněných lekcí. */
function lv75_decide(string $action, string $classId, array $lessons, string $note, string $by, ?int $now = null): int
{
    if (!in_array($action, LV75_ACTIONS, true)) throw new RuntimeException('Neznámá akce.');
    $content = lv75_content($classId);
    $lessons = array_values(array_unique(array_filter(array_map('intval', $lessons), static fn(int $n): bool => isset($content[$n]))));
    if ($lessons === []) throw new RuntimeException('Vyberte aspoň jednu lekci s návrhem úrovní.');
    $note = mb_substr(trim((string)preg_replace('/\s+/u', ' ', strip_tags($note))), 0, LV75_NOTE_MAX);
    if ($action === 'lv75_return' && $note === '') throw new RuntimeException('Napište krátce, co upravit – návrh se vrací s poznámkou.');
    $at = date(DATE_ATOM, $now ?? time());
    $by = mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, 60);
    storage_update(lv75_status_path(), static function (array $d) use ($action, $classId, $lessons, $content, $note, $at, $by): array {
        $classes = array_filter((array)($d['classes'] ?? []), 'is_array');
        foreach ($lessons as $n) {
            $classes[$classId][(string)$n] = ['status' => $action === 'lv75_approve' ? 'schvaleno' : 'vraceno', 'hash' => lv75_hash($content[$n]), 'at' => $at, 'by' => $by, 'note' => $note];
        }
        return ['v' => 1, 'classes' => $classes];
    });
    return count($lessons);
}

function lv75_student_hash(string $studentKey): string
{
    return substr(hash('sha256', 'lv75|' . $studentKey), 0, 16);
}

/**
 * Doporučená úroveň s vysvětlením (čistě z výsledků žáka, nic se neukládá).
 * $exits = list<bool> poslední odpovědi exit ticketů (nejnovější poslední), $tasks = ['attempts','ok'] z banky úloh.
 * @return array{level:string,reason:string,data:bool}
 */
function lv75_suggest_from(array $exits, array $tasks): array
{
    $exits = array_slice(array_values($exits), -3);
    $okExit = count(array_filter($exits));
    $rate = $tasks['attempts'] >= 4 ? (int)round($tasks['ok'] / max(1, $tasks['attempts']) * 100) : null;
    $parts = [];
    if ($exits !== []) $parts[] = ['exit', $okExit, count($exits)];
    if ($rate !== null) $parts[] = ['tasks', $rate, $tasks['attempts']];
    if ($parts === []) return ['level' => 'standard', 'reason' => 'none', 'data' => false, 'exit_ok' => 0, 'exit_n' => 0, 'rate' => null];
    $weak = (count($exits) >= 2 && $okExit * 2 < count($exits)) || ($rate !== null && $rate < 50);   // méně než polovina správně
    $strong = !$weak && (count($exits) < 2 || $okExit === count($exits)) && ($rate === null || $rate >= 80) && (count($exits) >= 2 || $rate !== null);
    $level = $weak ? 'zaklad' : ($strong ? 'vyzva' : 'standard');
    return ['level' => $level, 'reason' => $level, 'data' => true, 'exit_ok' => $okExit, 'exit_n' => count($exits), 'rate' => $rate];
}

/** Doporučení pro žáka v lekci: poslední 3 exit tickety před lekcí + banka úloh za 14 dní. */
function lv75_suggest(string $classId, string $studentKey, int $lesson, ?int $now = null): array
{
    $exits = [];
    if (function_exists('lx72_answers') && function_exists('lx72_student_hash')) {
        $answers = lx72_answers($classId);
        $hash = lx72_student_hash($studentKey);
        for ($n = max(1, $lesson - 6); $n < $lesson; $n++) {
            $row = $answers[(string)$n][$hash] ?? null;
            if (is_array($row)) $exits[] = (int)($row['ok'] ?? 0) === 1;
        }
    }
    $tasks = function_exists('tb75_student_recent') ? tb75_student_recent($classId, $studentKey, LV75_RECENT_DAYS, $now) : ['attempts' => 0, 'ok' => 0];
    return lv75_suggest_from($exits, $tasks);
}

/** Uložená volba žáka v lekci (nebo null). */
function lv75_pick_of(string $classId, string $studentKey, int $lesson): ?array
{
    $row = ((((storage_read(lv75_picks_path(), false))['classes'] ?? [])[$classId] ?? [])[(string)$lesson] ?? [])[lv75_student_hash($studentKey)] ?? null;
    return is_array($row) ? $row : null;
}

/** Volba úrovně žákem (jen schválená lekce). */
function lv75_pick(string $classId, string $studentKey, int $lesson, string $level, ?int $now = null): bool
{
    if (!isset(LV75_LEVELS[$level]) || $studentKey === '' || lv75_student_levels($classId, $lesson) === null) return false;
    $suggested = lv75_suggest($classId, $studentKey, $lesson, $now)['level'];
    $hash = lv75_student_hash($studentKey);
    storage_update(lv75_picks_path(), static function (array $d) use ($classId, $lesson, $level, $suggested, $hash, $now): array {
        $d['v'] = 1;
        $d['classes'][$classId][(string)$lesson][$hash] = ['l' => $level, 's' => $suggested, 'at' => $now ?? time()];
        return $d;
    });
    return true;
}

/**
 * Rozložení voleb ve třídě po lekcích (bez jmen): lekce → [zaklad, standard, vyzva, total, followed].
 * @return array<int,array{zaklad:int,standard:int,vyzva:int,total:int,followed:int}>
 */
function lv75_distribution(string $classId): array
{
    $out = [];
    foreach ((array)((storage_read(lv75_picks_path(), false)['classes'] ?? [])[$classId] ?? []) as $lesson => $rows) {
        $c = ['zaklad' => 0, 'standard' => 0, 'vyzva' => 0, 'total' => 0, 'followed' => 0];
        foreach ((array)$rows as $r) {
            if (!is_array($r) || !isset(LV75_LEVELS[(string)($r['l'] ?? '')])) continue;
            $c[(string)$r['l']]++;
            $c['total']++;
            $c['followed'] += (int)((string)$r['l'] === (string)($r['s'] ?? ''));
        }
        $out[(int)$lesson] = $c;
    }
    ksort($out);
    return $out;
}

/** POST lv75_* (CSRF, politiku a oprávnění content.manage ověřil teacher.php). */
function lv75_teacher_handle_post(string $action): void
{
    if (!in_array($action, LV75_ACTIONS, true)) throw new RuntimeException('Neznámá akce úrovní.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!isset(LV75_CLASS_FILES[$classId]) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    $lessons = is_array($_POST['lessons'] ?? null) ? array_values(array_filter($_POST['lessons'], static fn($v): bool => is_string($v) && preg_match('/^\d{1,2}$/', $v) === 1)) : [];
    if ($action === 'lv75_approve' && (string)($_POST['confirm'] ?? '') !== '1') throw new RuntimeException('Schválení je potřeba potvrdit zaškrtnutím.');
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $n = lv75_decide($action, $classId, $lessons, is_string($_POST['note'] ?? null) ? (string)$_POST['note'] : '', $by);
    $_SESSION['teacher_export_flash'] = $n < 1
        ? ['type' => 'error', 'message' => 'Nic se nezměnilo – zaškrtněte potvrzení (u vrácení napište poznámku), nebo už lekce tento stav mají.']
        : ['type' => 'ok', 'message' => $action === 'lv75_approve'
            ? 'Schválené lekce: ' . $n . ' – žáci v nich uvidí volbu Základ / Standard / Výzva.' : 'Vrácené k úpravě: ' . $n . ' – žáci je nevidí.'];
    $query = ['tab' => 'urovne75', 'class' => $classId];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
