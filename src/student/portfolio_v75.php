<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/assessment/taskbank_v75.php';

/**
 * EDUCANET v75 · portfolio žáka – připnuté práce (navazuje na výběr projektů v65 na stránce ?view=portfolio).
 *
 * Žák připne: odkaz na vlastní grafiku nebo web (jen https s doménou, bez přihlašovacích údajů), výsledek úlohy s variantami
 * (jen vyřešená úloha) nebo projekt z v65. Ke každé práci krátká reflexe (≤ 400 znaků, bez značek). Učitel s třídou v rozsahu
 * může ke každé práci napsat komentář (≤ 400 znaků). Vidí jen žák sám a jeho učitelé v rozsahu – žádné sdílení odkazem,
 * žádné veřejné záložky profilu. Tisk / PDF přes prohlížeč (bez externích knihoven).
 * Úložiště storage/portfolio_v75/<ref>.json.php = {"v":1,"pins":{id:{kind,title,url,ref,reflection,at,updated_at,comment}}},
 * ref = student_id (identita v58) nebo pseudonym (tb75_student_ref). Zápis jen storage_update.
 */

const PF75_KINDS = ['link' => 'Odkaz na práci', 'task' => 'Výsledek úlohy', 'project' => 'Projekt'];
const PF75_MAX = 12;
const PF75_TEXT_MAX = 400;
const PF75_TITLE_MAX = 120;
const PF75_URL_MAX = 500;
const PF75_STUDENT_ACTIONS = ['pf75_s_pin', 'pf75_s_reflect', 'pf75_s_unpin'];

function pf75_path(string $ref): string
{
    if (preg_match('/^(stu_[0-9a-f]{16}|h[0-9a-f]{24})$/', $ref) !== 1) throw new InvalidArgumentException('Neplatný žák.');
    return STORAGE_DIR . '/portfolio_v75/' . $ref . '.json.php';
}

/** Připnuté práce žáka, od nejnovější. @return array<string,array<string,mixed>> */
function pf75_pins(string $classId, string $studentKey): array
{
    $pins = array_filter((array)(storage_read(pf75_path(tb75_student_ref($classId, $studentKey)), false)['pins'] ?? []), 'is_array');
    uasort($pins, static fn(array $a, array $b): int => strcmp((string)($b['at'] ?? ''), (string)($a['at'] ?? '')));
    return $pins;
}

/** Čistý text: bez značek a řídicích znaků, zdvojené mezery pryč, oříznutý. */
function pf75_text(mixed $value, int $max): string
{
    $t = is_string($value) ? strip_tags($value) : '';
    $t = (string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $t);
    return mb_substr(trim((string)preg_replace('/[ \t]+/u', ' ', $t)), 0, $max);
}

/** Odkaz jen https s doménou, bez přihlašovacích údajů a mezer; jinak ''. */
function pf75_url(mixed $value): string
{
    $url = is_string($value) ? trim($value) : '';
    if ($url === '' || strlen($url) > PF75_URL_MAX || preg_match('/\s/u', $url) === 1) return '';
    $p = parse_url($url);
    if (!is_array($p) || strtolower((string)($p['scheme'] ?? '')) !== 'https' || !isset($p['host']) || isset($p['user']) || isset($p['pass'])) return '';
    return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', (string)$p['host']) === 1 ? $url : '';
}

/**
 * Připnutí práce. $in: kind, title, url (link), task (task), project (klíč kandidáta v65). Vrací ['ok'=>bool,'error'=>?string,'id'=>?string].
 * Chyby: kind, title, url, task (není vyřešená), project, full, duplicate.
 */
function pf75_pin(string $classId, string $studentKey, array $in, ?int $now = null): array
{
    $kind = is_string($in['kind'] ?? null) && isset(PF75_KINDS[$in['kind']]) ? (string)$in['kind'] : '';
    if ($kind === '') return ['ok' => false, 'error' => 'kind'];
    $row = ['kind' => $kind, 'title' => '', 'url' => '', 'ref' => '', 'reflection' => pf75_text($in['reflection'] ?? '', PF75_TEXT_MAX)];
    if ($kind === 'link') {
        $row['url'] = pf75_url($in['url'] ?? '');
        $row['title'] = pf75_text($in['title'] ?? '', PF75_TITLE_MAX);
        if ($row['url'] === '') return ['ok' => false, 'error' => 'url'];
        if ($row['title'] === '') return ['ok' => false, 'error' => 'title'];
    } elseif ($kind === 'task') {
        $task = is_string($in['task'] ?? null) ? (string)$in['task'] : '';
        $e = tb75_entry(tb75_student_state($classId, tb75_student_ref($classId, $studentKey)), $task);
        if (!in_array($task, tb75_tasks_for_class($classId), true) || $e['solved'] < 1) return ['ok' => false, 'error' => 'task'];
        $row['ref'] = $task;
        $row['title'] = TB75_TASKS[$task]['title'] . ' – vyřešeno ' . $e['solved'] . '× (vlastní varianty)';
    } else {
        $key = is_string($in['project'] ?? null) ? (string)$in['project'] : '';
        $cands = function_exists('port65_candidates') ? port65_candidates($classId, $studentKey) : [];
        $hit = null;
        foreach ($cands as $c) if ((string)($c['key'] ?? '') === $key) $hit = $c;
        if ($hit === null) return ['ok' => false, 'error' => 'project'];
        $row['ref'] = $key;
        $row['title'] = pf75_text((string)($hit['title'] ?? 'Projekt'), PF75_TITLE_MAX);
    }
    $ref = tb75_student_ref($classId, $studentKey);
    $out = ['ok' => false, 'error' => null, 'id' => null];
    $at = date(DATE_ATOM, $now ?? time());
    storage_update(pf75_path($ref), static function (array $d) use ($row, $at, &$out): array {
        $pins = array_filter((array)($d['pins'] ?? []), 'is_array');
        if (count($pins) >= PF75_MAX) { $out['error'] = 'full'; return $d; }
        foreach ($pins as $p) if ((string)$p['kind'] === $row['kind'] && ($row['url'] !== '' ? (string)$p['url'] === $row['url'] : (string)$p['ref'] === $row['ref'])) { $out['error'] = 'duplicate'; return $d; }
        $id = 'p' . bin2hex(random_bytes(5));
        $pins[$id] = $row + ['at' => $at, 'updated_at' => $at, 'comment' => null];
        $out = ['ok' => true, 'error' => null, 'id' => $id];
        return ['v' => 1, 'pins' => $pins];
    });
    return $out;
}

function pf75_valid_id(string $id): bool
{
    return preg_match('/^p[0-9a-f]{10}$/', $id) === 1;
}

/** Úprava reflexe nebo odebrání (jen vlastní práce). */
function pf75_update(string $classId, string $studentKey, string $id, ?string $reflection, ?int $now = null): bool
{
    if (!pf75_valid_id($id)) return false;
    $done = false;
    storage_update(pf75_path(tb75_student_ref($classId, $studentKey)), static function (array $d) use ($id, $reflection, $now, &$done): array {
        $pins = array_filter((array)($d['pins'] ?? []), 'is_array');
        if (!isset($pins[$id])) return $d;
        if ($reflection === null) unset($pins[$id]);
        else $pins[$id] = ['reflection' => pf75_text($reflection, PF75_TEXT_MAX), 'updated_at' => date(DATE_ATOM, $now ?? time())] + $pins[$id];
        $done = true;
        return ['v' => 1, 'pins' => $pins];
    });
    return $done;
}

/** Komentář učitele k práci (prázdný text = smazat komentář). Handler ověřil třídu v rozsahu. */
function pf75_comment(string $classId, string $studentKey, string $id, string $text, string $by, ?int $now = null): bool
{
    if (!pf75_valid_id($id)) return false;
    $text = pf75_text($text, PF75_TEXT_MAX);
    $done = false;
    storage_update(pf75_path(tb75_student_ref($classId, $studentKey)), static function (array $d) use ($id, $text, $by, $now, &$done): array {
        $pins = array_filter((array)($d['pins'] ?? []), 'is_array');
        if (!isset($pins[$id])) return $d;
        $pins[$id]['comment'] = $text === '' ? null : ['text' => $text, 'by' => mb_substr($by, 0, 60), 'at' => date(DATE_ATOM, $now ?? time())];
        $done = true;
        return ['v' => 1, 'pins' => $pins];
    });
    return $done;
}

/** POST pf75_comment (CSRF, politika a oprávnění projects.manage v teacher.php; žák podle pořadí v seznamu třídy). */
function pf75_teacher_handle_post(string $action): void
{
    if ($action !== 'pf75_comment') throw new RuntimeException('Neznámá akce portfolia.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if ((function_exists('teacher59_can_class') && !teacher59_can_class($classId)) || !function_exists('project_students_for_class')) throw new RuntimeException('Třída není v rozsahu.');
    $index = is_string($_POST['zak'] ?? null) && preg_match('/^\d{1,3}$/', (string)$_POST['zak']) === 1 ? (int)$_POST['zak'] : 0;
    $keys = array_keys(project_students_for_class($classId));
    if ($index < 1 || !isset($keys[$index - 1])) throw new RuntimeException('Žák nebyl nalezen.');
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $ok = pf75_comment($classId, (string)$keys[$index - 1], is_string($_POST['pin'] ?? null) ? (string)$_POST['pin'] : '', is_string($_POST['comment'] ?? null) ? (string)$_POST['comment'] : '', $by);
    $_SESSION['teacher_export_flash'] = ['type' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Komentář je uložený – žák ho uvidí u své práce v portfoliu.' : 'Práce nebyla nalezena (žák ji mezitím mohl odebrat).'];
    $query = ['tab' => 'portfolia75', 'class' => $classId, 'zak' => (string)$index];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
