<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Nahlášení chyby / návrh vylepšení (?view=hlaseni). Žák pošle hlášení, učitel ho potvrdí
 * (body + XP), zamítne, označí jako duplicitu nebo hotové. Odměna se za jedno hlášení udělí
 * NEJVÝŠE JEDNOU (klíč `fb60:<id>` v peněžence bodů i v událostech XP), i při opakovaném potvrzení.
 *
 * Úložiště: storage/feedback_v60.json.php – mapa {id => hlášení}; každé RMW jde přes storage_update.
 * Soukromí: ukládá se jen třída, klíč žáka (ne jméno), typ, titulek, popis a název stránky (whitelist).
 * Žádné soubory, žádná data z URL kromě názvu pohledu. Identitu žáka volající bere ze session.
 */

const FB60_TYPES = ['bug', 'improvement'];
const FB60_STATUSES = ['new', 'confirmed', 'rejected', 'duplicate', 'done'];
const FB60_TITLE_MIN = 5;
const FB60_TITLE_MAX = 120;
const FB60_DESC_MIN = 20;
const FB60_DESC_MAX = 2000;
const FB60_NOTE_MAX = 500;
const FB60_DAILY_LIMIT = 5;
const FB60_MAX_POINTS = 10;
const FB60_MAX_XP = 200;
const FB60_XP_PRESETS = [0, 10, 20, 30, 50, 80, 100];

function fb60_path(): string
{
    return STORAGE_DIR . '/feedback_v60.json.php';
}

/** Předvolby odměny podle typu (učitel je může při potvrzení změnit, XP mimo předvolby jen admin). */
function fb60_reward_defaults(): array
{
    return ['bug' => ['points' => 5, 'xp' => 50], 'improvement' => ['points' => 3, 'xp' => 30]];
}

/** Bílá listina názvů pohledů (?view=), ze kterých lze hlášení předvyplnit. Prázdné = bez stránky. */
function fb60_pages(): array
{
    return ['dashboard', 'intake', 'my_intake', 'continue', 'one_task', 'visual_lab', 'hands_on', 'goal_nav', 'growth', 'growth_path',
        'skill_passport', 'peer_lab', 'cognitive_lab', 'lesson_kit', 'lesson_slides', 'materialy', 'vysledky', 'lekce', 'lab', 'prikazy',
        'roboti', 'ctf', 'incident', 'hry', 'hadanka', 'obchod', 'projekty', 'hodina', 'course', 'calendar', 'tutorial', 'topics', 'tools',
        'knowledgebase', 'kb_lesson', 'kb_quiz', 'graphics_studio', 'study', 'mistakes', 'study_loop', 'skills', 'skill_branch',
        'skill_detail', 'mastery_challenge', 'mastery_result', 'profile', 'community', 'project_lobbies', 'project_workspace',
        'prestige_exams', 'project_results', 'project_result', 'review', 'recovery', 'create_challenge', 'case_study', 'test', 'result',
        'practice', 'practice_done', 'extra_challenge', 'graphics_guide', 'course_lesson', 'next_lesson', 'privacy', 'hlaseni'];
}

function fb60_page_clean(string $page): string
{
    $page = trim($page);
    return in_array($page, fb60_pages(), true) ? $page : '';
}

/** Očistí text od řídicích znaků; jednořádkový text sloučí bílé znaky. Nikdy nevrací null. */
function fb60_text(mixed $value, bool $multiline): string
{
    $text = is_string($value) ? str_replace(["\r\n", "\r"], "\n", $value) : '';
    $text = preg_replace('/[^\P{C}\n]+/u', '', $text) ?? '';
    if (!$multiline) return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    return trim(preg_replace("/\n{3,}/", "\n\n", $text) ?? '');
}

function fb60_title_key(string $title): string
{
    return mb_strtolower(preg_replace('/\s+/u', ' ', trim($title)) ?? $title, 'UTF-8');
}

/** Kontrola vstupu bez zápisu. @return array{ok:bool,error:?string,clean:array} */
function fb60_validate(array $input): array
{
    $type = is_string($input['type'] ?? null) ? (string)$input['type'] : '';
    $title = fb60_text($input['title'] ?? '', false);
    $desc = fb60_text($input['description'] ?? '', true);
    $clean = ['type' => $type, 'title' => $title, 'description' => $desc, 'page' => fb60_page_clean(is_string($input['page'] ?? null) ? (string)$input['page'] : '')];
    $error = null;
    if (!in_array($type, FB60_TYPES, true)) $error = 'type';
    elseif (mb_strlen($title, 'UTF-8') < FB60_TITLE_MIN) $error = 'title_short';
    elseif (mb_strlen($title, 'UTF-8') > FB60_TITLE_MAX) $error = 'title_long';
    elseif (mb_strlen($desc, 'UTF-8') < FB60_DESC_MIN) $error = 'desc_short';
    elseif (mb_strlen($desc, 'UTF-8') > FB60_DESC_MAX) $error = 'desc_long';
    return ['ok' => $error === null, 'error' => $error, 'clean' => $clean];
}

/**
 * Uloží nové hlášení žáka. Limit (5/den), duplicitní titulek a zápis proběhnou pod jedním zámkem.
 * $studentKey MUSÍ pocházet ze session (adaptive_student_key), nikdy z formuláře.
 * @return array{ok:bool,error:?string,id:?string}
 */
function fb60_submit(string $classId, string $studentKey, array $input): array
{
    if ($classId === '' || $studentKey === '') return ['ok' => false, 'error' => 'identity', 'id' => null];
    $valid = fb60_validate($input);
    if (!$valid['ok']) return ['ok' => false, 'error' => $valid['error'], 'id' => null];
    $clean = $valid['clean'];
    $result = ['ok' => false, 'error' => 'storage', 'id' => null];
    storage_update(fb60_path(), static function (array $all) use ($classId, $studentKey, $clean, &$result): array {
        $today = date('Y-m-d');
        $titleKey = fb60_title_key($clean['title']);
        $todayCount = 0;
        foreach ($all as $row) {
            if (!is_array($row) || ($row['student_key'] ?? '') !== $studentKey || ($row['class_id'] ?? '') !== $classId) continue;
            if (fb60_title_key((string)($row['title'] ?? '')) === $titleKey) { $result = ['ok' => false, 'error' => 'duplicate', 'id' => null]; return $all; }
            if (str_starts_with((string)($row['created_at'] ?? ''), $today)) $todayCount++;
        }
        if ($todayCount >= FB60_DAILY_LIMIT) { $result = ['ok' => false, 'error' => 'limit', 'id' => null]; return $all; }
        $id = 'fb60_' . bin2hex(random_bytes(6));
        $now = date(DATE_ATOM);
        $all[$id] = ['id' => $id, 'class_id' => $classId, 'student_key' => $studentKey, 'type' => $clean['type'], 'title' => $clean['title'],
            'description' => $clean['description'], 'page' => $clean['page'], 'status' => 'new', 'reward_points' => 0, 'reward_xp' => 0,
            'decided_by' => '', 'decided_at' => '', 'teacher_note' => '', 'created_at' => $now, 'updated_at' => $now];
        $result = ['ok' => true, 'error' => null, 'id' => $id];
        return $all;
    });
    return $result;
}

function fb60_item(string $id): ?array
{
    $row = storage_read(fb60_path())[$id] ?? null;
    return is_array($row) ? $row : null;
}

/** Hlášení JEN daného žáka (třída + klíč ze session), nejnovější první. */
function fb60_for_student(string $classId, string $studentKey): array
{
    if ($classId === '' || $studentKey === '') return [];
    $rows = array_filter(storage_read(fb60_path()), static fn($r): bool => is_array($r) && ($r['class_id'] ?? '') === $classId && ($r['student_key'] ?? '') === $studentKey);
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
    return array_values($rows);
}

/** Kolik hlášení může žák dnes ještě poslat. */
function fb60_remaining_today(string $classId, string $studentKey): int
{
    $today = date('Y-m-d');
    $used = count(array_filter(fb60_for_student($classId, $studentKey), static fn(array $r): bool => str_starts_with((string)($r['created_at'] ?? ''), $today)));
    return max(0, FB60_DAILY_LIMIT - $used);
}

/** Souhrn pro profil (jen vlastní). @return array{total:int,confirmed:int,pending:int,points:int,xp:int} */
function fb60_summary(string $classId, string $studentKey): array
{
    $sum = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'points' => 0, 'xp' => 0];
    foreach (fb60_for_student($classId, $studentKey) as $row) {
        $sum['total']++;
        $status = (string)($row['status'] ?? 'new');
        if ($status === 'new') $sum['pending']++;
        if ($status === 'confirmed' || ((int)($row['reward_points'] ?? 0) > 0 && $status === 'done')) {
            $sum['confirmed']++;
            $sum['points'] += (int)($row['reward_points'] ?? 0);
            $sum['xp'] += (int)($row['reward_xp'] ?? 0);
        }
    }
    return $sum;
}

/**
 * Hlášení pro učitele: admin vše, ostatní jen třídy z $allowedClasses. Filtry: status, type, class.
 * Nové nahoře, pak nejnovější.
 */
function fb60_list(array $allowedClasses, bool $isAdmin, array $filters = []): array
{
    $rows = [];
    foreach (storage_read(fb60_path()) as $row) {
        if (!is_array($row)) continue;
        $class = (string)($row['class_id'] ?? '');
        if (!$isAdmin && !in_array($class, $allowedClasses, true)) continue;
        if (($filters['status'] ?? '') !== '' && ($row['status'] ?? '') !== $filters['status']) continue;
        if (($filters['type'] ?? '') !== '' && ($row['type'] ?? '') !== $filters['type']) continue;
        if (($filters['class'] ?? '') !== '' && $class !== $filters['class']) continue;
        $rows[] = $row;
    }
    usort($rows, static function (array $a, array $b): int {
        $na = ($a['status'] ?? '') === 'new' ? 0 : 1;
        $nb = ($b['status'] ?? '') === 'new' ? 0 : 1;
        return $na <=> $nb ?: strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
    });
    return $rows;
}

/** Klíč profilu učení žáka odvozený z klíče žáka (`class:student:<hash24>` → `class:s:<hash24>`); '' = nelze. */
function fb60_learning_key(string $classId, string $studentKey): string
{
    if (preg_match('/^(class_[a-z0-9]+):student:([a-f0-9]{24})$/', $studentKey, $m) !== 1 || $m[1] !== $classId) return '';
    return $classId . ':s:' . $m[2];
}

/**
 * Idempotentně připíše XP profilu žáka (událost $eventKey se započítá nejvýš jednou). Stejné pravidlo
 * jako learning_award_once(), jen pro cizí profil (učitel rozhoduje, žák není v session).
 * @return bool true = XP je v profilu (nově i dřív), false = profil žáka nejde určit
 */
function fb60_award_xp(string $classId, string $studentKey, string $eventKey, int $xp): bool
{
    $xp = max(0, min(FB60_MAX_XP, $xp));
    if ($xp === 0) return true;
    $key = fb60_learning_key($classId, $studentKey);
    if ($key === '' || $eventKey === '') return false;
    storage_map_update(learning_profiles_path(), $key, static function (?array $current) use ($eventKey, $xp): array {
        $p = $current ?? learning_profile_default();
        $events = is_array($p['events'] ?? null) ? $p['events'] : [];
        if (isset($events[$eventKey])) return $p;
        $events[$eventKey] = ['xp' => $xp, 'at' => date(DATE_ATOM)];
        $p['events'] = $events;
        $p['xp'] = max(0, (int)($p['xp'] ?? 0) + $xp);
        $p['version'] = (int)($p['version'] ?? 0) + 1;
        $p['updated_at'] = date(DATE_ATOM);
        return $p;
    });
    return true;
}

/** Povolené přechody stavů (potvrzení znovu = idempotentní znovupoužití uložené odměny). */
function fb60_transition_allowed(string $from, string $to): bool
{
    $map = ['new' => ['confirmed', 'rejected', 'duplicate', 'done'], 'confirmed' => ['confirmed', 'done'],
        'rejected' => ['confirmed', 'duplicate', 'rejected'], 'duplicate' => ['confirmed', 'rejected', 'duplicate'], 'done' => ['done']];
    return in_array($to, $map[$from] ?? [], true);
}

/** Odměna mimo předvolby smí zadat jen admin; jinak se XP srovná na nejbližší nižší předvolbu. */
function fb60_clamp_reward(int $points, int $xp, bool $isAdmin): array
{
    $points = max(1, min(FB60_MAX_POINTS, $points));
    $xp = max(0, min(FB60_MAX_XP, $xp));
    if (!$isAdmin && !in_array($xp, FB60_XP_PRESETS, true)) {
        $lower = array_filter(FB60_XP_PRESETS, static fn(int $p): bool => $p <= $xp);
        $xp = $lower === [] ? 0 : max($lower);
    }
    return ['points' => $points, 'xp' => $xp];
}

/**
 * Rozhodnutí učitele. Volající MUSÍ předtím ověřit oprávnění a rozsah třídy (teacher59_guard_post).
 * Potvrzení: odměna se uloží při PRVNÍM potvrzení a dál se nemění; body i XP se připisují idempotentně
 * s klíčem `fb60:<id>`, takže opakované potvrzení nic nepřidá (a případně dopíše, co se dřív nezapsalo).
 * @return array{ok:bool,error:?string,status:string,points:int,xp:int,xp_applied:bool}
 */
function fb60_decide(string $id, string $decision, int $points, int $xp, string $note, string $actor, bool $isAdmin): array
{
    $target = ['confirm' => 'confirmed', 'reject' => 'rejected', 'duplicate' => 'duplicate', 'done' => 'done'][$decision] ?? '';
    $fail = static fn(string $error): array => ['ok' => false, 'error' => $error, 'status' => '', 'points' => 0, 'xp' => 0, 'xp_applied' => false];
    $note = fb60_text($note, true);
    if ($target === '') return $fail('decision');
    if (mb_strlen($note, 'UTF-8') > FB60_NOTE_MAX) return $fail('note_long');
    $reward = fb60_clamp_reward($points, $xp, $isAdmin);
    $error = null;
    $row = null;
    storage_update(fb60_path(), static function (array $all) use ($id, $target, $note, $actor, $reward, &$error, &$row): array {
        $cur = is_array($all[$id] ?? null) ? $all[$id] : null;
        if ($cur === null) { $error = 'not_found'; return $all; }
        $from = (string)($cur['status'] ?? 'new');
        if (!fb60_transition_allowed($from, $target)) { $error = ((int)($cur['reward_points'] ?? 0) > 0) ? 'rewarded' : 'transition'; return $all; }
        $rewarded = (int)($cur['reward_points'] ?? 0) > 0;
        if ($target === 'confirmed' && !$rewarded) { $cur['reward_points'] = $reward['points']; $cur['reward_xp'] = $reward['xp']; }
        $cur['status'] = $target;
        $cur['teacher_note'] = $note;
        $cur['decided_by'] = mb_substr($actor, 0, 80, 'UTF-8');
        $cur['decided_at'] = date(DATE_ATOM);
        $cur['updated_at'] = $cur['decided_at'];
        $all[$id] = $cur;
        $row = $cur;
        return $all;
    });
    if ($error !== null || $row === null) return $fail($error ?? 'not_found');
    $xpApplied = $target === 'confirmed' ? fb60_apply_rewards($row) : false;
    return ['ok' => true, 'error' => null, 'status' => $target, 'points' => (int)$row['reward_points'], 'xp' => (int)$row['reward_xp'], 'xp_applied' => $xpApplied];
}

/** Připíše uloženou odměnu hlášení (body + XP), obojí idempotentně. @return bool XP je v profilu */
function fb60_apply_rewards(array $row): bool
{
    $class = (string)$row['class_id'];
    $student = (string)$row['student_key'];
    $key = 'fb60:' . (string)$row['id'];
    $reason = ($row['type'] ?? '') === 'bug' ? 'Potvrzené hlášení chyby' : 'Potvrzený návrh vylepšení';
    pts53_award($class, $student, $key, (int)$row['reward_points'], $reason);
    return fb60_award_xp($class, $student, $key, (int)$row['reward_xp']);
}
