<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Projekty podle levelu. Klient (mimo systém) zadá skutečnou zakázku, učitel ji zapíše do katalogu
 * s min. levelem; žák si přečte veřejné shrnutí vždy, detail a přihlášku jen po dosažení min. levelu.
 * Odměna typu 'kc' (peníze) VŽDY vyžaduje potvrzený souhlas zákonného zástupce – vynucuje se serverem
 * bez ohledu na to, co pošle formulář (viz proj60_item_from_input()).
 *
 * Klient nemá přístup do systému a nedostává žádné osobní údaje žáka – kontakt jen přes školu (viz
 * docs/PROJEKTY_V60.md). V úložišti se proto nikdy neukládají kontakty klienta ani částky jako čísla,
 * jen textová poznámka o odměně (reward_note).
 *
 * Úložiště:
 *   storage/projects_v60.json.php              – katalog {id => projekt}
 *   storage/projects_v60_applications.json.php – přihlášky {id => přihláška}
 * proud projects_v60_log (storage_append) – log rozhodnutí (jen pro dohled, bez osobních údajů navíc).
 */

const PROJ60_REWARD_TYPES = ['kc', 'portfolio', 'certificate', 'other'];
const PROJ60_STATUSES = ['draft', 'open', 'closed', 'done'];
const PROJ60_MOTIVATION_MAX = 500;
/** v65 · zámek projektu podle kompetencí (max 3; jen pilotní třídy kompetencí, jinde se ignoruje). */
const PROJ60_COMPETENCY_MAX = 3;
const PROJ60_COMPETENCY_STATES = ['rozpracovano' => 1, 'zvladnuto' => 2, 'upevneno' => 3];

/** Id kompetencí ze všech předmětů katalogu comp62 (pro validaci vstupu učitele). @return array<string,string> id → popis */
function proj60_known_competencies(): array
{
    require_once __DIR__ . '/competencies_v62.php';
    $out = [];
    foreach (array_keys(comp62_catalog()) as $subject) {
        foreach (comp62_competencies((string)$subject) as $id => $c) $out[(string)$id] = (string)$c['label'];
    }
    return $out;
}

/**
 * Požadované kompetence z formuláře: rc_id[] + rc_state[] (nebo hotové pole required_competencies). Vrací null při neplatném vstupu.
 * @return list<array{id:string,min_state:string}>|null
 */
function proj60_parse_required(array $input): ?array
{
    $rows = [];
    if (is_array($input['required_competencies'] ?? null)) {
        $rows = array_values(array_filter($input['required_competencies'], 'is_array'));
    } else {
        $ids = is_array($input['rc_id'] ?? null) ? array_values($input['rc_id']) : [];
        $states = is_array($input['rc_state'] ?? null) ? array_values($input['rc_state']) : [];
        foreach ($ids as $i => $id) $rows[] = ['id' => $id, 'min_state' => $states[$i] ?? 'zvladnuto'];
    }
    $known = proj60_known_competencies();
    $out = [];
    foreach ($rows as $row) {
        $id = is_string($row['id'] ?? null) ? trim($row['id']) : '';
        if ($id === '') continue;
        $state = is_string($row['min_state'] ?? null) ? $row['min_state'] : 'zvladnuto';
        if (!isset($known[$id]) || !isset(PROJ60_COMPETENCY_STATES[$state])) return null;
        $out[$id] = ['id' => $id, 'min_state' => $state];
    }
    return count($out) > PROJ60_COMPETENCY_MAX ? null : array_values($out);
}

/**
 * Chybějící požadované kompetence žáka (jen pilotní třída; jinde vždy prázdné). Nejistá identita = kompetence chybí.
 * @return list<string> id kompetencí
 */
function proj60_missing_competencies(string $classId, string $studentKey, array $project): array
{
    $required = array_values(array_filter((array)($project['required_competencies'] ?? []), 'is_array'));
    if ($required === []) return [];
    foreach (['competencies_v62.php', 'evidence_v62.php', 'mastery_v62.php'] as $lib) require_once __DIR__ . '/' . $lib;
    if (!comp62_enabled_for_class($classId)) return [];
    $map = (array)(m62_student($classId, $studentKey)['map'] ?? []);
    $missing = [];
    foreach ($required as $r) {
        $have = PROJ60_COMPETENCY_STATES[(string)($map[(string)$r['id']]['state'] ?? '')] ?? 0;
        if ($have < (PROJ60_COMPETENCY_STATES[(string)$r['min_state']] ?? 2)) $missing[] = (string)$r['id'];
    }
    return $missing;
}

function proj60_projects_path(): string
{
    return STORAGE_DIR . '/projects_v60.json.php';
}

function proj60_applications_path(): string
{
    return STORAGE_DIR . '/projects_v60_applications.json.php';
}

function proj60_reward_valid(string $type): bool
{
    return in_array($type, PROJ60_REWARD_TYPES, true);
}

function proj60_status_valid(string $status): bool
{
    return in_array($status, PROJ60_STATUSES, true);
}

/** Celý katalog (bez filtrace) – jen pro učitele/admina. */
function proj60_all(): array
{
    return storage_read(proj60_projects_path());
}

function proj60_item(string $id): ?array
{
    $item = proj60_all()[$id] ?? null;
    return is_array($item) ? $item : null;
}

/** Otevřené projekty nabízené dané třídě (classes musí obsahovat $classId; prázdné classes = nikomu). */
function proj60_for_class(string $classId): array
{
    $out = [];
    foreach (proj60_all() as $id => $item) {
        if (!is_array($item) || (string)($item['status'] ?? '') !== 'open') continue;
        $classes = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
        if (!in_array($classId, $classes, true)) continue;
        $out[(string)$id] = $item;
    }
    uasort($out, static fn(array $a, array $b): int => (int)($a['min_level'] ?? 1) <=> (int)($b['min_level'] ?? 1));
    return $out;
}

/**
 * Veřejná verze projektu pro žáka (bez detail_private, pokud $hasLevel je false). Nikdy nevrací
 * detail_private, pokud žák nedosáhl min. levelu – detail se tedy neposílá ani do HTML.
 */
function proj60_public_view(array $item, bool $hasLevel): array
{
    $out = [
        'id' => (string)($item['id'] ?? ''),
        'title' => (string)($item['title'] ?? ''),
        'client_label' => (string)($item['client_label'] ?? ''),
        'summary_public' => (string)($item['summary_public'] ?? ''),
        'skills' => array_values(array_filter((array)($item['skills'] ?? []), 'is_string')),
        'min_level' => (int)($item['min_level'] ?? 1),
        'reward_type' => (string)($item['reward_type'] ?? 'other'),
        'reward_note' => (string)($item['reward_note'] ?? ''),
        'capacity' => (int)($item['capacity'] ?? 1),
        'deadline' => (string)($item['deadline'] ?? ''),
        'requires_guardian_consent' => !empty($item['requires_guardian_consent']),
        'has_level' => $hasLevel,
        'required_competencies' => array_values(array_filter((array)($item['required_competencies'] ?? []), 'is_array')),
    ];
    if ($hasLevel) {
        $out['detail_private'] = (string)($item['detail_private'] ?? '');
    }
    return $out;
}

/** Normalizuje/ověří vstup projektu (učitel/admin). Vrací null při neplatných datech. */
function proj60_item_from_input(array $input, array $allowedClasses): ?array
{
    $title = trim((string)($input['title'] ?? ''));
    $rewardType = (string)($input['reward_type'] ?? '');
    $minLevel = (int)($input['min_level'] ?? 0);
    $capacity = max(1, (int)($input['capacity'] ?? 1));
    $status = (string)($input['status'] ?? 'draft');
    if ($title === '' || !proj60_reward_valid($rewardType) || $minLevel < 1 || !proj60_status_valid($status)) return null;
    $classes = array_values(array_unique(array_filter((array)($input['classes'] ?? []), 'is_string')));
    foreach ($classes as $c) {
        if (!in_array($c, $allowedClasses, true)) return null; // učitel nesmí přiřadit cizí třídu
    }
    $deadline = trim((string)($input['deadline'] ?? ''));
    if ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) return null;
    $skills = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/u', (string)($input['skills'] ?? '')) ?: []), static fn(string $s): bool => $s !== ''));
    $required = proj60_parse_required($input);
    if ($required === null) return null; // neznámá kompetence, neplatný stav nebo víc než PROJ60_COMPETENCY_MAX
    return [
        'required_competencies' => $required,
        'title' => $title,
        'client_label' => trim((string)($input['client_label'] ?? '')),
        'summary_public' => trim((string)($input['summary_public'] ?? '')),
        'detail_private' => trim((string)($input['detail_private'] ?? '')),
        'skills' => array_slice($skills, 0, 12),
        'min_level' => $minLevel,
        'reward_type' => $rewardType,
        // Odměna 'kc' (peníze) VŽDY vyžaduje souhlas zákonného zástupce – server to vynutí bez ohledu
        // na to, co formulář pošle (checkbox jde ignorovat/zfalšovat, tady se to nesmí projevit).
        'reward_note' => trim((string)($input['reward_note'] ?? '')),
        'capacity' => $capacity,
        'deadline' => $deadline,
        'classes' => $classes,
        'status' => $status,
        'requires_guardian_consent' => $rewardType === 'kc' ? true : !empty($input['requires_guardian_consent']),
    ];
}

/** Vytvoří/upraví projekt. $id = '' → nový. Vrací id, nebo null při neplatných datech. */
function proj60_save(string $id, array $input, array $allowedClasses, string $actor): ?string
{
    $data = proj60_item_from_input($input, $allowedClasses);
    if ($data === null) return null;
    $result = null;
    storage_update(proj60_projects_path(), static function (array $all) use (&$result, $id, $data, $actor): array {
        $itemId = $id !== '' && isset($all[$id]) ? $id : 'proj' . bin2hex(random_bytes(5));
        $existing = is_array($all[$itemId] ?? null) ? $all[$itemId] : [];
        $all[$itemId] = array_replace($existing, $data, [
            'id' => $itemId,
            'created_by' => (string)($existing['created_by'] ?? $actor),
            'created_at' => (string)($existing['created_at'] ?? date(DATE_ATOM)),
            'updated_at' => date(DATE_ATOM),
        ]);
        $result = $itemId;
        return $all;
    });
    return $result;
}

function proj60_set_status(string $id, string $status): bool
{
    if (!proj60_status_valid($status)) return false;
    $ok = false;
    storage_map_update(proj60_projects_path(), $id, static function (?array $item) use (&$ok, $status): ?array {
        if ($item === null) return null;
        $ok = true;
        $item['status'] = $status;
        $item['updated_at'] = date(DATE_ATOM);
        return $item;
    });
    return $ok;
}

function proj60_application(string $id): ?array
{
    $row = storage_read(proj60_applications_path())[$id] ?? null;
    return is_array($row) ? $row : null;
}

/** Počet schválených přihlášek (obsazenost kapacity). */
function proj60_approved_count(array $applications, string $projectId): int
{
    $n = 0;
    foreach ($applications as $app) {
        if (is_array($app) && (string)($app['project_id'] ?? '') === $projectId && (string)($app['status'] ?? '') === 'approved') $n++;
    }
    return $n;
}

/** @return ?array vlastní přihláška žáka na daný projekt (bez ohledu na stav), nebo null. */
function proj60_find_own(array $applications, string $classId, string $studentKey, string $projectId): ?array
{
    foreach ($applications as $app) {
        if (!is_array($app)) continue;
        if ((string)($app['project_id'] ?? '') === $projectId && (string)($app['class_id'] ?? '') === $classId && (string)($app['student_key'] ?? '') === $studentKey) {
            return $app;
        }
    }
    return null;
}

/**
 * Přihlásí žáka o projekt. Server znovu ověří vše, co viděl klient (level, třídu, kapacitu, termín,
 * stav projektu, duplicitu) – žádné z toho nepřebírá z formuláře. Atomicky přes storage_update.
 * @return array{ok:bool, error?:string}
 */
function proj60_apply(string $classId, string $studentKey, string $projectId, string $motivation, int $currentLevel): array
{
    $motivation = trim(mb_substr($motivation, 0, PROJ60_MOTIVATION_MAX));
    if ($classId === '' || $studentKey === '' || $projectId === '') return ['ok' => false, 'error' => 'invalid_request'];
    $project = proj60_item($projectId);
    if ($project === null || (string)($project['status'] ?? '') !== 'open') return ['ok' => false, 'error' => 'project_unavailable'];
    $classes = array_values(array_filter((array)($project['classes'] ?? []), 'is_string'));
    if (!in_array($classId, $classes, true)) return ['ok' => false, 'error' => 'wrong_class'];
    if ($currentLevel < (int)$project['min_level']) return ['ok' => false, 'error' => 'level_too_low'];
    $deadline = (string)($project['deadline'] ?? '');
    if ($deadline !== '' && $deadline < date('Y-m-d')) return ['ok' => false, 'error' => 'deadline_passed'];
    if (proj60_missing_competencies($classId, $studentKey, $project) !== []) return ['ok' => false, 'error' => 'competency_missing'];

    $outcome = ['ok' => false, 'error' => 'unknown'];
    storage_update(proj60_applications_path(), function (array $apps) use (&$outcome, $classId, $studentKey, $projectId, $motivation, $currentLevel, $project): array {
        // Znovu čerstvě: projekt se mezitím mohl zavřít/naplnit.
        $fresh = proj60_item($projectId);
        if ($fresh === null || (string)($fresh['status'] ?? '') !== 'open') {
            $outcome = ['ok' => false, 'error' => 'project_unavailable'];
            return $apps;
        }
        if (proj60_missing_competencies($classId, $studentKey, $fresh) !== []) { // zámek znovu nad čerstvým projektem (učitel ho mohl mezitím změnit)
            $outcome = ['ok' => false, 'error' => 'competency_missing'];
            return $apps;
        }
        $existing = proj60_find_own($apps, $classId, $studentKey, $projectId);
        if ($existing !== null && (string)($existing['status'] ?? '') !== 'withdrawn') {
            $outcome = ['ok' => false, 'error' => 'already_applied'];
            return $apps;
        }
        if (proj60_approved_count($apps, $projectId) >= (int)$fresh['capacity']) {
            $outcome = ['ok' => false, 'error' => 'capacity_full'];
            return $apps;
        }
        $id = 'papp' . bin2hex(random_bytes(6));
        $apps[$id] = [
            'id' => $id,
            'project_id' => $projectId,
            'class_id' => $classId,
            'student_key' => $studentKey,
            'motivation' => $motivation,
            'status' => 'interested',
            'level_at_apply' => $currentLevel,
            'consent_confirmed_by_teacher' => false,
            'consent_confirmed_by' => null,
            'decided_by' => null,
            'at' => date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM),
        ];
        $outcome = ['ok' => true, 'error' => null, 'application_id' => $id];
        return $apps;
    });
    return $outcome;
}

/** Stáhnutí vlastní přihlášky žákem (jen interested/approved → withdrawn). */
function proj60_withdraw(string $classId, string $studentKey, string $applicationId): bool
{
    $ok = false;
    storage_map_update(proj60_applications_path(), $applicationId, static function (?array $app) use (&$ok, $classId, $studentKey): ?array {
        if ($app === null) return null;
        if ((string)($app['class_id'] ?? '') !== $classId || (string)($app['student_key'] ?? '') !== $studentKey) return $app;
        if (!in_array((string)($app['status'] ?? ''), ['interested', 'approved'], true)) return $app;
        $ok = true;
        $app['status'] = 'withdrawn';
        $app['updated_at'] = date(DATE_ATOM);
        return $app;
    });
    return $ok;
}

/** Přihlášky žáka spárované s projekty – pro „Moje přihlášky“. */
function proj60_my_applications(string $classId, string $studentKey): array
{
    $catalog = proj60_all();
    $out = [];
    foreach (storage_read(proj60_applications_path()) as $app) {
        if (!is_array($app)) continue;
        if ((string)($app['class_id'] ?? '') !== $classId || (string)($app['student_key'] ?? '') !== $studentKey) continue;
        $project = is_array($catalog[$app['project_id']] ?? null) ? $catalog[$app['project_id']] : null;
        $out[] = [
            'id' => (string)$app['id'],
            'project_id' => (string)$app['project_id'],
            'title' => $project !== null ? (string)$project['title'] : tr('Projekt už není dostupný'),
            'status' => (string)$app['status'],
            'at' => (string)$app['at'],
        ];
    }
    usort($out, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
    return $out;
}

/** Rozhodnutí učitele o přihlášce. U 'kc'/requires_guardian_consent lze schválit jen s potvrzeným souhlasem. */
function proj60_decide(string $applicationId, string $decision, bool $consentConfirmed, string $actor): bool
{
    if (!in_array($decision, ['approved', 'rejected'], true)) return false;
    $ok = false;
    storage_update(proj60_applications_path(), function (array $apps) use (&$ok, $applicationId, $decision, $consentConfirmed, $actor): array {
        $app = is_array($apps[$applicationId] ?? null) ? $apps[$applicationId] : null;
        if ($app === null) return $apps;
        $project = proj60_item((string)$app['project_id']);
        if ($project === null) return $apps;
        if ($decision === 'approved' && !empty($project['requires_guardian_consent']) && !$consentConfirmed) {
            return $apps; // schválení bez ověřeného souhlasu zákonného zástupce se odmítá
        }
        $app['status'] = $decision;
        $app['decided_by'] = $actor;
        $app['updated_at'] = date(DATE_ATOM);
        if ($decision === 'approved' && !empty($project['requires_guardian_consent'])) {
            $app['consent_confirmed_by_teacher'] = true;
            $app['consent_confirmed_by'] = $actor;
        }
        $apps[$applicationId] = $app;
        $ok = true;
        return $apps;
    });
    return $ok;
}

/** Přihlášky pro projekty ve zvolených třídách (učitelský přehled, filtrováno voláním funkce). */
function proj60_applications_for_classes(array $classIds): array
{
    $catalog = proj60_all();
    $out = [];
    foreach (storage_read(proj60_applications_path()) as $app) {
        if (!is_array($app)) continue;
        $project = is_array($catalog[$app['project_id']] ?? null) ? $catalog[$app['project_id']] : null;
        if ($project === null) continue;
        $projectClasses = array_values(array_filter((array)($project['classes'] ?? []), 'is_string'));
        if (array_intersect($projectClasses, $classIds) === []) continue;
        $out[] = $app + ['project' => $project];
    }
    usort($out, static fn(array $a, array $b): int => strcmp((string)$b['at'], (string)$a['at']));
    return $out;
}
