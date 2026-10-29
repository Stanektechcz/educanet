<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 · F6 – přechod školního roku, rozlišení jmenovců a přejmenování žáka.
 *
 * Zásady: historická data zůstávají pod původními klíči (nic se nepřepisuje ani nemaže). Přenos XP, odznaků
 * a vyřešených úrovní labu = KOPIE do nového klíče žáka v nové třídě, a to jen když nový klíč ještě neexistuje.
 * Zápisy běží jen z CLI s --apply a se zálohou mladší než 1 h (tools/v58_rollover.php, tools/v58_identity.php).
 * Každý soubor se mění vlastním storage_update() – zámky se nevnořují.
 */

require_once __DIR__ . '/identity_v58.php';

const IDENTITY58_BACKUP_MAX_AGE = 3600;
const IDENTITY58_CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
const IDENTITY58_CODE_LENGTH = 6;
/** Události profilu, které nepatří ke kurzu třídy (lab, hry) – přenášejí se s XP. */
const IDENTITY58_CARRY_EVENT_PREFIXES = ['v57:', 'v58:', 'lab58', 'robots58:', 'tg58:'];

function identity58_rollover_defaults(): array
{
    return ['carry_xp' => true, 'carry_badges' => true, 'carry_lab' => true, 'course' => 'archive',
        'block_graduates' => true, 'new_codes' => true, 'repeat' => [], 'left' => []];
}

function identity58_default_year(?array $reg = null): int
{
    return identity58_school_year(identity58_normalize($reg ?? identity58_registry())) + 1;
}

// ---------------------------------------------------------------------------
// Záloha
// ---------------------------------------------------------------------------

function identity58_backup_dir(?string $override = null): string
{
    if ($override !== null && trim($override) !== '') return rtrim(str_replace('\\', '/', $override), '/');
    $env = trim((string)getenv('EDUCANET_BACKUP_DIR'));
    return $env !== '' ? rtrim(str_replace('\\', '/', $env), '/') : str_replace('\\', '/', dirname(__DIR__)) . '/educanet-backups';
}

function identity58_same_path(string $a, string $b): bool
{
    $norm = static fn(string $p): string => strtolower(rtrim(str_replace('\\', '/', (string)(realpath($p) ?: $p)), '/'));
    return $norm($a) === $norm($b);
}

/** Najde zálohu tools/backup_storage.php mladší než $maxAge s|zdrojem = aktuální STORAGE_DIR. */
function identity58_backup_check(string $dir, int $maxAge = IDENTITY58_BACKUP_MAX_AGE, ?int $now = null): array
{
    $now = $now ?? time();
    $dirs = glob(rtrim($dir, '/') . '/storage-*', GLOB_ONLYDIR) ?: [];
    rsort($dirs);
    foreach ($dirs as $candidate) {
        $raw = @file_get_contents($candidate . '/manifest.json');
        $manifest = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($manifest)) continue;
        $created = strtotime((string)($manifest['created_at'] ?? '')) ?: 0;
        if (!identity58_same_path((string)($manifest['source'] ?? ''), STORAGE_DIR)) continue;
        $age = $now - $created;
        if ($created > 0 && $age >= -60 && $age <= $maxAge) return ['ok' => true, 'path' => $candidate, 'age' => $age];
        return ['ok' => false, 'path' => $candidate, 'age' => $age, 'reason' => 'Poslední záloha je starší než ' . intdiv($maxAge, 60) . ' min.'];
    }
    return ['ok' => false, 'path' => null, 'age' => null, 'reason' => 'Chybí záloha úložiště (php tools/backup_storage.php).'];
}

// ---------------------------------------------------------------------------
// Plán přechodu roku
// ---------------------------------------------------------------------------

/** Zdroj profilu (learning_profiles) a souboru labu pro žáka ve třídě $classId (podle jeho aliasů). */
function identity58_carry_sources(array $stu, string $classId, array $profiles): array
{
    $label = (string)($stu['label'] ?? '');
    $preferredProfile = $classId . ':s:' . identity58_h24($classId, $label);
    $profile = isset($profiles[$preferredProfile]) ? $preferredProfile : null;
    $lab = null;
    foreach ((array)($stu['aliases'] ?? []) as $alias) {
        $alias = (string)$alias;
        if ($profile === null && str_starts_with($alias, $classId . ':s:') && isset($profiles[$alias])) $profile = $alias;
        if ($lab === null && str_starts_with($alias, 'lab:' . $classId . '__') && is_file(identity58_lab_file($alias))) $lab = $alias;
    }
    return ['profile' => $profile, 'lab' => $lab];
}

function identity58_lab_file(string $labAlias): string
{
    return STORAGE_DIR . '/linux_v57/' . preg_replace('/[^a-z0-9_]/i', '', substr($labAlias, 4)) . '.json.php';
}

/** Klíče žáka v cílové třídě, které už patří někomu jinému (jmenovec) nebo mají cizí data. */
function identity58_target_conflicts(array $reg, string $stuId, string $toClass, string $label, array $profiles): array
{
    $out = [];
    foreach (identity58_keys_for($toClass, $label) as $alias) {
        $owner = $reg['alias_index'][$alias] ?? null;
        if (is_string($owner) && $owner !== $stuId) $out[$owner] = identity58_alias_type($alias);
    }
    $target = $toClass . ':s:' . identity58_h24($toClass, $label);
    // Profil v cílovém klíči bez známého vlastníka = cizí data (např. účet mimo registr).
    if (isset($profiles[$target]) && !$out && ($reg['alias_index'][$target] ?? null) !== $stuId) $out['?'] = 'profile';
    return $out;
}

/**
 * Plán přechodu do školního roku $year (rok začátku, např. 2027 = 2027/28). Nic nezapisuje.
 * Vrací ['year', 'already_applied', 'blockers'[], 'moves'[] (se jmény – jen pro obrazovku), 'counts'{…}, 'conflicts'[]].
 */
function identity58_rollover_plan(int $year, array $opts = [], ?array $reg = null, ?array $profiles = null): array
{
    $opts = array_replace(identity58_rollover_defaults(), $opts);
    $reg = identity58_normalize($reg ?? identity58_registry());
    $profiles = $profiles ?? load_php_json(STORAGE_DIR . '/learning_profiles.json.php');
    $state = $reg['rollovers'][(string)$year] ?? null;
    $already = is_array($state) && ($state['status'] ?? '') === 'done';
    $blockers = [];
    $doneYears = array_map('intval', array_keys(array_filter($reg['rollovers'], static fn($r): bool => is_array($r) && ($r['status'] ?? '') === 'done')));
    if ($year < 2000 || $year > 2100) $blockers[] = 'Neplatný rok.';
    if (!$already && $doneYears && $year <= max($doneYears)) $blockers[] = 'Rok ' . $year . ' není novější než poslední přechod (' . max($doneYears) . ').';
    $repeat = array_flip(array_map('strval', (array)$opts['repeat']));
    $left = array_flip(array_map('strval', (array)$opts['left']));
    foreach (array_keys($repeat + $left) as $id) {
        if (!isset($reg['students'][$id])) $blockers[] = 'Neznámé student_id v --repeat/--left: ' . $id;
    }
    $counts = ['move' => [], 'archive' => 0, 'repeat' => 0, 'left' => 0, 'demo_skipped' => 0, 'unknown_class' => 0,
        'carry_profile' => 0, 'carry_lab' => 0, 'conflicts' => 0, 'active' => 0];
    $moves = [];
    $conflicts = [];
    foreach ($reg['students'] as $stuId => $stu) {
        if (!is_array($stu) || ($stu['status'] ?? 'active') !== 'active') continue;
        $counts['active']++;
        $stuId = (string)$stuId;
        $from = (string)$stu['class_id'];
        $label = (string)$stu['label'];
        if (!empty($stu['demo'])) { $counts['demo_skipped']++; continue; }
        if (isset($left[$stuId])) $action = 'left';
        elseif (isset($repeat[$stuId])) $action = 'repeat';
        elseif (!array_key_exists($from, IDENTITY58_PROGRESSION)) { $counts['unknown_class']++; continue; }
        else $action = IDENTITY58_PROGRESSION[$from] === null ? 'archive' : 'move';
        $to = $action === 'move' ? (string)IDENTITY58_PROGRESSION[$from] : $from;
        $row = ['stu_id' => $stuId, 'label' => $label, 'from' => $from, 'to' => $to, 'action' => $action, 'profile_src' => null, 'lab_src' => null];
        if ($action === 'move') {
            $src = identity58_carry_sources($stu, $from, $profiles);
            $row['profile_src'] = $src['profile'];
            $row['lab_src'] = $src['lab'];
            $counts['move'][$from . '→' . $to] = ($counts['move'][$from . '→' . $to] ?? 0) + 1;
            if ($src['profile'] !== null && ($opts['carry_xp'] || $opts['carry_badges'])) $counts['carry_profile']++;
            if ($src['lab'] !== null && $opts['carry_lab']) $counts['carry_lab']++;
            foreach (identity58_target_conflicts($reg, $stuId, $to, $label, $profiles) as $other => $type) {
                $conflicts[] = ['stu_id' => $stuId, 'label' => $label, 'to' => $to, 'other' => $other === '?' ? null : (string)$other, 'type' => $type];
            }
        } else {
            $counts[$action]++;
        }
        $moves[] = $row;
    }
    $counts['conflicts'] = count($conflicts);
    if ($conflicts) $blockers[] = 'Jmenovci v cílové třídě (' . count($conflicts) . ') – nejdřív je rozliš (tools/v58_identity.php --relabel).';
    ksort($counts['move']);
    return ['year' => $year, 'already_applied' => $already, 'blockers' => $blockers, 'options' => $opts,
        'moves' => $moves, 'counts' => $counts, 'conflicts' => $conflicts, 'new_codes' => (bool)$opts['new_codes'] ? array_keys(IDENTITY58_PROGRESSION) : []];
}

/** Chybějící háčky aplikace (INTEGRATION.md) – bez nich by přechod roku změnil jen registr, ne seznamy tříd. */
function identity58_integration_missing(): array
{
    $missing = [];
    $rows = student_directory();
    $marked = false;
    foreach ($rows as $row) { if (is_array($row) && isset($row['identity58_seen'])) { $marked = true; break; } }
    if ($rows && !$marked) $missing[] = 'student_directory() → identity58_directory_overlay()';
    if (!function_exists('acc58_login_gate') || acc58_login_gate(['login_blocked' => true, 'password_hash' => 'x', 'must_change_password' => false]) !== IDENTITY58_MSG_BLOCKED) {
        $missing[] = 'acc58_login_gate() → identity58_login_gate()';
    }
    return $missing;
}

// ---------------------------------------------------------------------------
// Přenos dat (kopie do nového klíče; zdroj zůstává)
// ---------------------------------------------------------------------------

function identity58_carried_profile(array $old, array $opts, int $year, string $fromClass, int $now, bool $withCourse = false): array
{
    $xp = !empty($opts['carry_xp']) ? max(0, (int)($old['xp'] ?? 0)) : 0;
    $events = [];
    if (!empty($opts['carry_xp'])) {
        foreach ((array)($old['events'] ?? []) as $key => $event) {
            foreach (IDENTITY58_CARRY_EVENT_PREFIXES as $prefix) {
                if ($withCourse || str_starts_with((string)$key, $prefix)) { $events[$key] = $event; break; }
            }
        }
        if ($xp > 0) $events['identity58:carry:' . $year] = ['xp' => 0, 'carried_xp' => $xp, 'at' => date(DATE_ATOM, $now)];
    }
    $badges = !empty($opts['carry_badges']);
    return [
        'xp' => $xp, 'events' => $events,
        'kb' => $withCourse ? (array)($old['kb'] ?? []) : [], 'studio' => $withCourse ? (array)($old['studio'] ?? []) : [],
        'journey' => $withCourse ? (array)($old['journey'] ?? []) : [],
        'badges' => $badges ? (array)($old['badges'] ?? []) : [], 'achievements' => $badges ? (array)($old['achievements'] ?? []) : [],
        'updated_at' => date(DATE_ATOM, $now), 'carried_from' => ['year' => $year, 'class_id' => $fromClass],
    ];
}

/** $items: [['src' => klíč profilu, 'target' => nový klíč, 'from' => třída], …]. Existující cíl se nepřepíše. */
function identity58_carry_profiles(array $items, array $opts, int $year, int $now, bool $withCourse = false): int
{
    $items = array_values(array_filter($items, static fn(array $i): bool => !empty($i['src']) && $i['src'] !== $i['target']));
    if (!$items || (empty($opts['carry_xp']) && empty($opts['carry_badges']) && !$withCourse)) return 0;
    $copied = 0;
    storage_update(STORAGE_DIR . '/learning_profiles.json.php', static function (array $all) use ($items, $opts, $year, $now, $withCourse, &$copied): array {
        foreach ($items as $i) {
            if (!is_array($all[$i['src']] ?? null) || isset($all[$i['target']])) continue;
            $all[$i['target']] = identity58_carried_profile($all[$i['src']], $opts, $year, (string)$i['from'], $now, $withCourse);
            $copied++;
        }
        return $all;
    });
    return $copied;
}

/** Kopie vyřešených úrovní labu (kontext practice) a statistik; rozpracované instance a log zůstávají v historii. */
function identity58_carry_lab(string $labAlias, string $toClass, string $toLabel, int $year, string $fromClass, int $now): bool
{
    $src = identity58_lab_file($labAlias);
    if (!is_file($src)) return false;
    $old = load_php_json($src);
    $target = STORAGE_DIR . '/linux_v57/' . preg_replace('/[^a-z0-9_]/i', '', $toClass) . '__'
        . sha1($toClass . ':student:' . identity58_h24($toClass, $toLabel)) . '.json.php';
    if (is_file($target) && filesize($target) > 0) return false;
    $copied = false;
    storage_update($target, static function (array $d) use ($old, $year, $fromClass, $now, &$copied): array {
        if ($d) return $d;
        $copied = true;
        return ['v' => 1, 'solved' => ['practice' => (array)($old['solved']['practice'] ?? [])], 'stats' => (array)($old['stats'] ?? []),
            'carried_from' => ['year' => $year, 'class_id' => $fromClass, 'at' => date(DATE_ATOM, $now)]];
    });
    return $copied;
}

// ---------------------------------------------------------------------------
// Provedení přechodu roku
// ---------------------------------------------------------------------------

function identity58_generate_codes(array $classIds, array $taken): array
{
    $codes = [];
    $max = strlen(IDENTITY58_CODE_ALPHABET) - 1;
    foreach ($classIds as $classId) {
        do {
            $code = '';
            for ($i = 0; $i < IDENTITY58_CODE_LENGTH; $i++) $code .= IDENTITY58_CODE_ALPHABET[random_int(0, $max)];
        } while (isset($taken[$code]));
        $taken[$code] = true;
        $codes[(string)$classId] = $code;
    }
    return $codes;
}

/** Zapíše do mapy účtů a lokálních účtů novou třídu / archivaci podle plánu (jen řádky se student_id z plánu). */
function identity58_apply_accounts(array $byStu, array $opts, int $now): array
{
    $stats = ['map' => 0, 'local' => 0];
    $patch = static function (array $row, array $m, bool $local) use ($opts, $now): array {
        if ($m['action'] === 'move') return array_replace($row, ['class_id' => $m['to']]);
        if ($m['action'] === 'archive' || $m['action'] === 'left') {
            $extra = ['status' => $m['action'] === 'archive' ? 'archived' : 'left', 'archived_at' => date(DATE_ATOM, $now)];
            if ($local && !empty($opts['block_graduates'])) $extra['login_blocked'] = true;
            return array_replace($row, $extra);
        }
        return $row;
    };
    storage_update(STORAGE_DIR . '/student_accounts.json.php', static function (array $rows) use ($byStu, $patch, &$stats): array {
        foreach ($rows as $key => $row) {
            $m = is_array($row) ? ($byStu[(string)($row['student_id'] ?? '')] ?? null) : null;
            if ($m === null) continue;
            $new = $patch($row, $m, false);
            if ($new !== $row) { $rows[$key] = $new; $stats['map']++; }
        }
        return $rows;
    });
    storage_update(local_accounts_path(), static function (array $rows) use ($byStu, $patch, &$stats): array {
        foreach ($rows as $email => $row) {
            $m = is_array($row) ? ($byStu[(string)($row['student_id'] ?? '')] ?? null) : null;
            if ($m === null) continue;
            $new = $patch($row, $m, true);
            if ($new !== $row) { $rows[$email] = $new; $stats['local']++; }
        }
        return $rows;
    });
    return $stats;
}

/** Registr po přechodu: nové třídy a aliasy, archivace, překryv adresáře, nové kódy, záznam roku (bez jmen). */
function identity58_apply_registry(array $reg, array $plan, array $opts, array $codes, array $summary, int $now): array
{
    $stats = ['created' => 0, 'aliases_added' => 0];
    $year = (int)$plan['year'];
    foreach ($plan['moves'] as $m) {
        $stuId = (string)$m['stu_id'];
        if (!is_array($reg['students'][$stuId] ?? null)) continue;
        $stu = $reg['students'][$stuId];
        $history = (array)($stu['history'] ?? []);
        $history[] = ['year' => $year, 'event' => $m['action'], 'from' => $m['from'], 'to' => $m['to'], 'at' => date(DATE_ATOM, $now)];
        $stu['history'] = $history;
        $stu['updated_at'] = date(DATE_ATOM, $now);
        if ($m['action'] !== 'repeat') {
            // Řádek adresáře „stará třída|jméno“ teď patří tomuto žákovi (překryv); alias se uvolní pro nové jmenovce.
            $dirAlias = 'dir:' . identity58_dir_key((string)$m['from'], (string)$m['label']);
            $inOverlay = false;
            foreach ($reg['dir_overlay'] as $list) { if (in_array($stuId, (array)$list, true)) { $inOverlay = true; break; } }
            if (!$inOverlay && ($reg['alias_index'][$dirAlias] ?? null) === $stuId) {
                $reg['dir_overlay'][identity58_dir_key((string)$m['from'], (string)$m['label'])][] = $stuId;
            }
            if (($reg['alias_index'][$dirAlias] ?? null) === $stuId) {
                unset($reg['alias_index'][$dirAlias]);
                $stu['aliases'] = array_values(array_diff((array)$stu['aliases'], [$dirAlias]));
                $stu['retired_aliases'] = array_values(array_unique(array_merge((array)($stu['retired_aliases'] ?? []), [$dirAlias])));
            }
        }
        if ($m['action'] === 'move') $stu['class_id'] = $m['to'];
        if ($m['action'] === 'archive' || $m['action'] === 'left') {
            $stu['status'] = $m['action'] === 'archive' ? 'archived' : 'left';
            $stu['archived_at'] = date(DATE_ATOM, $now);
            if (!empty($opts['block_graduates'])) $stu['login_blocked'] = true;
        }
        $reg['students'][$stuId] = $stu;
        if ($m['action'] === 'move') {
            foreach (identity58_keys_for((string)$m['to'], (string)$m['label']) as $alias) $reg = identity58_with_alias($reg, $stuId, $alias, $stats);
        }
    }
    if ($codes) { $reg['class_codes'] = $codes; $reg['class_codes_year'] = $year; }
    $reg['rollovers'][(string)$year] = ['status' => 'done', 'applied_at' => date(DATE_ATOM, $now), 'counts' => $summary,
        'options' => array_diff_key($opts, ['repeat' => 1, 'left' => 1]) + ['repeat' => count((array)$opts['repeat']), 'left' => count((array)$opts['left'])]];
    return $reg;
}

/**
 * Provede přechod roku. Odmítne bez zálohy mladší než 1 h, s jmenovci v cílové třídě nebo bez háčků aplikace
 * (pokud $allowMissingIntegration = false). Druhé spuštění pro stejný rok nic nezmění.
 */
function identity58_rollover_apply(int $year, array $opts, string $backupDir, bool $allowMissingIntegration = false, ?int $now = null): array
{
    $now = $now ?? time();
    $opts = array_replace(identity58_rollover_defaults(), $opts);
    $backup = identity58_backup_check($backupDir, IDENTITY58_BACKUP_MAX_AGE, $now);
    if (!$backup['ok']) return ['ok' => false, 'error' => (string)$backup['reason']];
    if (!$allowMissingIntegration && ($missing = identity58_integration_missing())) {
        return ['ok' => false, 'error' => 'Chybí háčky aplikace: ' . implode('; ', $missing) . ' (viz INTEGRATION.md).'];
    }
    identity58_build(false);
    $plan = identity58_rollover_plan($year, $opts);
    if ($plan['already_applied']) return ['ok' => true, 'noop' => true, 'plan' => $plan];
    if ($plan['blockers']) return ['ok' => false, 'error' => implode(' ', $plan['blockers']), 'plan' => $plan];

    storage_update(identity58_path(), static function (array $d) use ($year, $now): array {
        $d = identity58_normalize($d);
        $d['rollovers'][(string)$year] = ['status' => 'in_progress', 'started_at' => date(DATE_ATOM, $now)];
        return $d;
    });
    $items = [];
    $labCopied = 0;
    $byStu = [];
    foreach ($plan['moves'] as $m) {
        $byStu[(string)$m['stu_id']] = $m;
        if ($m['action'] !== 'move') continue;
        $items[] = ['src' => $m['profile_src'], 'target' => $m['to'] . ':s:' . identity58_h24($m['to'], $m['label']), 'from' => $m['from']];
        if ($opts['carry_lab'] && $m['lab_src'] !== null && identity58_carry_lab((string)$m['lab_src'], $m['to'], $m['label'], $year, $m['from'], $now)) $labCopied++;
    }
    $profilesCopied = identity58_carry_profiles($items, $opts, $year, $now);
    $accounts = identity58_apply_accounts($byStu, $opts, $now);
    $summary = $plan['counts'] + ['profiles_copied' => $profilesCopied, 'lab_copied' => $labCopied, 'accounts_map' => $accounts['map'], 'accounts_local' => $accounts['local']];
    unset($summary['conflicts']);
    $codes = [];
    storage_update(identity58_path(), static function (array $d) use ($plan, $opts, $summary, $now, &$codes): array {
        $d = identity58_normalize($d);
        if ($opts['new_codes']) {
            $taken = array_flip(array_map('strval', $d['class_codes']));
            foreach ($GLOBALS['modules'] ?? [] as $module) { if (is_array($module) && isset($module['code'])) $taken[strtoupper((string)$module['code'])] = true; }
            $codes = identity58_generate_codes($plan['new_codes'], $taken);
        }
        return identity58_apply_registry($d, $plan, $opts, $codes, $summary, $now);
    });
    identity58_build(false);
    return ['ok' => true, 'noop' => false, 'plan' => $plan, 'summary' => $summary, 'codes' => $codes, 'backup' => $backup['path']];
}

// ---------------------------------------------------------------------------
// Jmenovci: rozlišení (split) a přejmenování (relabel)
// ---------------------------------------------------------------------------

function identity58_label_problem(array $reg, string $classId, string $label, string $exceptStu = ''): ?string
{
    $label = trim($label);
    if (normalized_person_name($label) === '' || u_strlen($label) > 80) return 'Zadej jméno (nejvýš 80 znaků).';
    foreach (identity58_keys_for($classId, $label) as $alias) {
        $owner = $reg['alias_index'][$alias] ?? null;
        if (is_string($owner) && $owner !== $exceptStu) return 'Toto jméno už ve třídě patří jinému žákovi – zvol jiné rozlišení, např. „(B)“.';
    }
    return null;
}

/**
 * Rozliší jmenovce: vznikne NOVÝ žák (nové student_id) s rozlišeným jménem; volitelně na něj přejde účet $accountKey
 * (klíč mapy účtů: local:<id> nebo Google sub). Sloučená historická data zůstávají u původního ID (nelze je
 * automaticky rozdělit). $dryRun = true nic nezapíše.
 */
function identity58_split(string $stuId, string $newLabel, string $accountKey, bool $dryRun): array
{
    $reg = identity58_registry();
    $stu = $reg['students'][$stuId] ?? null;
    if (!is_array($stu) || ($stu['status'] ?? 'active') !== 'active') return ['ok' => false, 'error' => 'Aktivní žák s tímto student_id neexistuje.'];
    $classId = (string)$stu['class_id'];
    $newLabel = trim($newLabel);
    if (($problem = identity58_label_problem($reg, $classId, $newLabel)) !== null) return ['ok' => false, 'error' => $problem];
    $map = student_account_map();
    $moveAliases = [];
    $email = '';
    if ($accountKey !== '') {
        $row = $map[$accountKey] ?? null;
        if (!is_array($row) || (string)($row['student_id'] ?? '') !== $stuId) return ['ok' => false, 'error' => 'Účet nepatří k tomuto žákovi.'];
        $email = strtolower((string)($row['email'] ?? ''));
        $moveAliases = identity58_account_aliases($accountKey, $classId, $email);
    }
    $plan = ['ok' => true, 'dry_run' => $dryRun, 'from' => $stuId, 'class_id' => $classId, 'moves_account' => $accountKey !== ''];
    if ($dryRun) return $plan;
    $newId = '';
    $now = time();
    storage_update(identity58_path(), static function (array $d) use ($stuId, $classId, $newLabel, $moveAliases, $now, &$newId): array {
        $d = identity58_normalize($d);
        $newId = identity58_new_id($d);
        $old = $d['students'][$stuId];
        $d['students'][$newId] = ['class_id' => $classId, 'label' => $newLabel, 'cohort' => $old['cohort'] ?? null, 'status' => 'active',
            'aliases' => [], 'created_at' => date(DATE_ATOM, $now), 'split_from' => $stuId];
        $old['splits'] = array_values(array_unique(array_merge((array)($old['splits'] ?? []), [$newId])));
        $old['aliases'] = array_values(array_diff((array)$old['aliases'], $moveAliases));
        $d['students'][$stuId] = $old;
        $stats = ['created' => 0, 'aliases_added' => 0];
        foreach ($moveAliases as $alias) unset($d['alias_index'][$alias]);
        foreach (array_merge($moveAliases, identity58_keys_for($classId, $newLabel)) as $alias) $d = identity58_with_alias($d, $newId, $alias, $stats);
        return $d;
    });
    if ($accountKey !== '') identity58_relabel_accounts([$accountKey => true], $email !== '' ? [$email => true] : [], $newLabel, $newId);
    identity58_build(false);
    return $plan + ['new_id' => $newId];
}

/** Změní jméno žáka v mapě účtů a lokálních účtech (a přiřadí student_id). */
function identity58_relabel_accounts(array $mapKeys, array $emails, string $label, string $stuId): void
{
    storage_update(STORAGE_DIR . '/student_accounts.json.php', static function (array $rows) use ($mapKeys, $label, $stuId): array {
        foreach ($rows as $key => $row) {
            if (is_array($row) && (isset($mapKeys[(string)$key]) || (string)($row['student_id'] ?? '') === $stuId && !$mapKeys)) {
                $rows[$key] = array_replace($row, ['student_label' => $label, 'student_id' => $stuId]);
            }
        }
        return $rows;
    });
    storage_update(local_accounts_path(), static function (array $rows) use ($emails, $label, $stuId, $mapKeys): array {
        foreach ($rows as $email => $row) {
            if (is_array($row) && (isset($emails[(string)$email]) || (string)($row['student_id'] ?? '') === $stuId && !$mapKeys)) {
                $rows[$email] = array_replace($row, ['student_label' => $label, 'student_id' => $stuId]);
            }
        }
        return $rows;
    });
}

/**
 * Přejmenuje žáka (např. „Petr Svoboda“ → „Petr Svoboda (B)“) kvůli jmenovci. Nové klíče dostanou kopii
 * profilu i labu ze starých klíčů téže třídy (včetně kurzu), staré klíče zůstávají jako historie.
 */
function identity58_relabel(string $stuId, string $newLabel, bool $dryRun): array
{
    $reg = identity58_registry();
    $stu = $reg['students'][$stuId] ?? null;
    if (!is_array($stu) || ($stu['status'] ?? 'active') !== 'active') return ['ok' => false, 'error' => 'Aktivní žák s tímto student_id neexistuje.'];
    $classId = (string)$stu['class_id'];
    $newLabel = trim($newLabel);
    if (($problem = identity58_label_problem($reg, $classId, $newLabel, $stuId)) !== null) return ['ok' => false, 'error' => $problem];
    $src = identity58_carry_sources($stu, $classId, load_php_json(STORAGE_DIR . '/learning_profiles.json.php'));
    $plan = ['ok' => true, 'dry_run' => $dryRun, 'stu_id' => $stuId, 'class_id' => $classId, 'profile' => $src['profile'] !== null, 'lab' => $src['lab'] !== null];
    if ($dryRun) return $plan;
    $now = time();
    $year = identity58_school_year($reg, $now);
    $all = ['carry_xp' => true, 'carry_badges' => true];
    identity58_carry_profiles([['src' => $src['profile'], 'target' => $classId . ':s:' . identity58_h24($classId, $newLabel), 'from' => $classId]], $all, $year, $now, true);
    if ($src['lab'] !== null) identity58_carry_lab((string)$src['lab'], $classId, $newLabel, $year, $classId, $now);
    storage_update(identity58_path(), static function (array $d) use ($stuId, $classId, $newLabel, $now): array {
        $d = identity58_normalize($d);
        $stats = ['created' => 0, 'aliases_added' => 0];
        $old = (string)$d['students'][$stuId]['label'];
        $d['students'][$stuId]['label'] = $newLabel;
        $d['students'][$stuId]['updated_at'] = date(DATE_ATOM, $now);
        $d['students'][$stuId]['history'] = array_merge((array)($d['students'][$stuId]['history'] ?? []), [['event' => 'relabel', 'at' => date(DATE_ATOM, $now)]]);
        $d['students'][$stuId]['label_history'] = array_values(array_unique(array_merge((array)($d['students'][$stuId]['label_history'] ?? []), [$old])));
        foreach (identity58_keys_for($classId, $newLabel) as $alias) $d = identity58_with_alias($d, $stuId, $alias, $stats);
        return $d;
    });
    identity58_relabel_accounts([], [], $newLabel, $stuId);
    identity58_build(false);
    return $plan;
}
