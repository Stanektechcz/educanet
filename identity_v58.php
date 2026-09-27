<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 · F6 (ARC-06) – stabilní identita žáka a registr aliasů.
 *
 * Historická data se NEPŘEPISUJÍ. Všechny dosavadní klíče žáka (třída + normalizované jméno, účty, soubor labu)
 * se jen namapují na stabilní `student_id` ve tvaru `stu_<16 hex>`.
 *
 * Registr storage/identity_v58.json.php:
 *   students{stu_x: {class_id, label, cohort, status: active|archived|left, aliases[], created_at, updated_at,
 *                    demo?, split_from?, splits?[], history?[]}}
 *   alias_index{alias → stu_x}       – aktivní aliasy (jeden alias = jeden žák)
 *   dir_overlay{"class|norm" → [stu_x, …]} – řádky adresáře, které po přechodu roku patří už přesunutým žákům
 *   class_codes{class_id → kód}, class_codes_year – nové kódy tříd po přechodu roku (jinak platí modules.php)
 *   rollovers{rok → {status, applied_at, counts…}} – bez jmen
 *   conflicts[] – aliasy, o které se hlásí dva žáci (poslední sestavení)
 *   signature, built_at
 *
 * Typy aliasů: `dir:<class>|<norm>` (řádek adresáře), `<class>:student:<h24>` (project/social/adaptive/lab),
 * `<class>:s:<h24>` (learning/skill), `<class>:n:<h20>`, `<class>:l:<h24>`, `<class>:g:<h24>` (starší klíče profilu),
 * `local:<id>`, `google:<sub>`, `email:<e-mail>`, `lab:<class>__<sha1>` (soubor stavu Linux Labu).
 *
 * API: identity58_registry, identity58_build, identity58_ensure, identity58_id_for_alias, identity58_id_for_student,
 *      identity58_current_student_id, identity58_aliases, identity58_duplicates, identity58_directory_overlay,
 *      identity58_login_gate, identity58_assignment_allowed, identity58_class_codes, identity58_class_code.
 * Přechod roku, rozlišení jmenovců a přejmenování: identity_v58_rollover.php.
 */

const IDENTITY58_VERSION = 1;
const IDENTITY58_STATUSES = ['active', 'archived', 'left'];
/** Postup ročníků: null = ročník končí (archivace). */
const IDENTITY58_PROGRESSION = ['class_1a' => 'class_2a', 'class_2a' => 'class_3a', 'class_3a' => 'class_4a', 'class_4a' => null];
const IDENTITY58_MSG_BLOCKED = 'Tento účet je archivovaný (absolvent nebo odchod ze školy). Pokud je to omyl, obrať se na učitele.';

function identity58_path(): string
{
    return STORAGE_DIR . '/identity_v58.json.php';
}

function identity58_empty(): array
{
    return ['version' => IDENTITY58_VERSION, 'students' => [], 'alias_index' => [], 'dir_overlay' => [], 'class_codes' => [],
        'class_codes_year' => null, 'rollovers' => [], 'conflicts' => [], 'signature' => '', 'built_at' => null];
}

/** Doplní chybějící části registru (tolerantní ke starší nebo prázdné podobě). */
function identity58_normalize(array $data): array
{
    $out = array_replace(identity58_empty(), $data);
    foreach (['students', 'alias_index', 'dir_overlay', 'class_codes', 'rollovers', 'conflicts'] as $k) {
        if (!is_array($out[$k])) $out[$k] = [];
    }
    return $out;
}

function identity58_registry(): array
{
    return identity58_normalize(load_php_json(identity58_path()));
}

// ---------------------------------------------------------------------------
// Klíče žáka
// ---------------------------------------------------------------------------

function identity58_h24(string $classId, string $label): string
{
    return substr(hash('sha256', $classId . '|' . normalized_person_name($label)), 0, 24);
}

function identity58_dir_key(string $classId, string $label): string
{
    return $classId . '|' . normalized_person_name($label);
}

/** Všechny klíče, které aplikace odvozuje z třídy a jména (stejné vzorce jako bootstrap.php a skill_trees.php). */
function identity58_keys_for(string $classId, string $label): array
{
    $label = trim($label);
    if ($classId === '' || normalized_person_name($label) === '') return [];
    $h = identity58_h24($classId, $label);
    $project = $classId . ':student:' . $h;
    return [
        'dir:' . identity58_dir_key($classId, $label),
        $project,
        $classId . ':s:' . $h,
        $classId . ':n:' . substr(hash('sha256', strtolower($label)), 0, 20),
        'lab:' . preg_replace('/[^a-z0-9_]/i', '', $classId) . '__' . sha1($project),
    ];
}

/** Aliasy účtu podle klíče v mapě účtů (`local:<id>` nebo Google `sub`). */
function identity58_account_aliases(string $mapKey, string $classId, string $email = ''): array
{
    $out = [];
    if (str_starts_with($mapKey, 'local:')) {
        $id = substr($mapKey, 6);
        if ($id === '') return [];
        $out[] = $mapKey;
        if ($classId !== '') $out[] = $classId . ':l:' . substr(hash('sha256', $id), 0, 24);
    } elseif ($mapKey !== '') {
        $sub = str_starts_with($mapKey, 'google:') ? substr($mapKey, 7) : $mapKey;
        $out[] = 'google:' . $sub;
        if ($classId !== '') $out[] = $classId . ':g:' . substr(hash('sha256', $sub), 0, 24);
    }
    $email = strtolower(trim($email));
    if ($email !== '') $out[] = 'email:' . $email;
    return $out;
}

/** Školní rok (rok začátku) – po přechodu roku platí rok posledního přechodu, jinak podle data (září). */
function identity58_school_year(array $reg, ?int $now = null): int
{
    $years = array_map('intval', array_keys(array_filter($reg['rollovers'], static fn($r): bool => is_array($r) && ($r['status'] ?? '') === 'done')));
    if ($years) return max($years);
    $now = $now ?? time();
    $y = (int)date('Y', $now);
    return (int)date('n', $now) >= 9 ? $y : $y - 1;
}

/** Rok nástupu: class_2a ve školním roce 2026/27 → 2025. */
function identity58_cohort(string $classId, int $schoolYear): ?int
{
    return preg_match('/^class_(\d+)/', $classId, $m) ? $schoolYear - ((int)$m[1] - 1) : null;
}

function identity58_new_id(array $reg): string
{
    do { $id = 'stu_' . bin2hex(random_bytes(8)); } while (isset($reg['students'][$id]));
    return $id;
}

// ---------------------------------------------------------------------------
// Zdroje: adresář žáků (s překryvem po přechodu roku), mapa účtů, lokální účty
// ---------------------------------------------------------------------------

/**
 * Testovací náhrada adresáře (jen audit): $GLOBALS['identity58_directory_override'] nebo – jen v CLI – JSON soubor
 * z proměnné EDUCANET_IDENTITY58_DIRECTORY (audit tak nepracuje se skutečnými jmény žáků).
 */
function identity58_directory_override(): ?array
{
    if (isset($GLOBALS['identity58_directory_override']) && is_array($GLOBALS['identity58_directory_override'])) return $GLOBALS['identity58_directory_override'];
    $file = PHP_SAPI === 'cli' ? trim((string)getenv('EDUCANET_IDENTITY58_DIRECTORY')) : '';
    if ($file === '' || !is_file($file)) return null;
    $rows = json_decode((string)file_get_contents($file), true);
    return is_array($rows) ? $rows : null;
}

/**
 * Řádky adresáře. Po integraci aplikuje překryv student_directory() sám (řádky nesou `identity58_seen`);
 * jinak (a v auditu přes $GLOBALS['identity58_directory_override']) se překryv aplikuje tady.
 */
function identity58_directory_rows(?array $reg = null): array
{
    $rows = identity58_directory_override() ?? student_directory();
    foreach ($rows as $row) {
        if (is_array($row) && isset($row['identity58_seen'])) return $rows;
    }
    return identity58_directory_overlay($rows, $reg);
}

/**
 * Překryv adresáře podle registru (patch do student_directory()). Bez přechodu roku a bez rozlišených jmenovců
 * vrací stejné řádky (jen s příznakem). Přesunutý žák dostane novou třídu, archivovaný/odešlý zmizí ze seznamu,
 * rozlišený jmenovec (split) se přidá jako vlastní řádek a zdvojený řádek jmenovce se odebere.
 */
function identity58_directory_overlay(array $rows, ?array $reg = null): array
{
    $reg = $reg ?? identity58_registry();
    $students = $reg['students'];
    $overlay = $reg['dir_overlay'];
    $used = [];
    $seenPerKey = [];
    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        $key = identity58_dir_key($classId, $label);
        $row['identity58_seen'] = 1;
        $n = $used[$key] ?? 0;
        $stuId = null;
        if (isset($overlay[$key][$n])) { $stuId = (string)$overlay[$key][$n]; $used[$key] = $n + 1; }
        if ($stuId === null) {
            $candidate = $reg['alias_index']['dir:' . $key] ?? null;
            $seenPerKey[$key] = ($seenPerKey[$key] ?? 0) + 1;
            // Rozlišený jmenovec: zdvojený řádek se nahradí vlastním řádkem nového žáka (viz níže).
            $splits = is_string($candidate) ? count((array)($students[$candidate]['splits'] ?? [])) : 0;
            if ($splits > 0 && $seenPerKey[$key] > 1 && $seenPerKey[$key] - 1 <= $splits) continue;
            if (is_string($candidate)) {
                $row['identity58_stu'] = $candidate;
                if (is_array($students[$candidate] ?? null)) $row = identity58_overlay_label($row, $students[$candidate]);
            }
            $out[] = $row;
            continue;
        }
        $stu = $students[$stuId] ?? null;
        if (!is_array($stu)) { $out[] = $row; continue; }
        if (($stu['status'] ?? 'active') !== 'active') continue;
        $row['identity58_src_class'] = $classId;
        $row['identity58_stu'] = $stuId;
        $row['class_id'] = (string)$stu['class_id'];
        $out[] = identity58_overlay_label($row, $stu);
    }
    foreach ($students as $stuId => $stu) {
        if (!is_array($stu) || empty($stu['split_from']) || ($stu['status'] ?? 'active') !== 'active') continue;
        $out[] = ['class_id' => (string)$stu['class_id'], 'first_name' => (string)$stu['label'], 'last_name' => '',
            'identity58_seen' => 1, 'identity58_stu' => (string)$stuId];
    }
    return $out;
}

/** Přejmenovaný žák (relabel kvůli jmenovci) se v seznamu třídy ukáže pod novým jménem. */
function identity58_overlay_label(array $row, array $stu): array
{
    if (empty($stu['label_history'])) return $row;
    $current = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
    if (normalized_person_name($current) === normalized_person_name((string)$stu['label'])) return $row;
    return array_replace($row, ['first_name' => (string)$stu['label'], 'last_name' => '', 'identity58_src_label' => $current]);
}

// ---------------------------------------------------------------------------
// Sestavení registru (čistý výpočet + zápis)
// ---------------------------------------------------------------------------

/** Přidá alias žákovi; alias patřící jinému žákovi se nepřevezme (zaznamená se konflikt). */
function identity58_with_alias(array $reg, string $stuId, string $alias, array &$stats): array
{
    if ($alias === '') return $reg;
    $owner = $reg['alias_index'][$alias] ?? null;
    if ($owner === $stuId) return $reg;
    if (is_string($owner) && isset($reg['students'][$owner])) {
        $pair = [$owner, $stuId];
        sort($pair);
        $type = identity58_alias_type($alias);
        $reg['conflicts'][$type . ':' . implode(':', $pair)] = ['type' => $type, 'stu_ids' => $pair];
        return $reg;
    }
    $reg['alias_index'][$alias] = $stuId;
    $aliases = (array)($reg['students'][$stuId]['aliases'] ?? []);
    if (!in_array($alias, $aliases, true)) {
        $aliases[] = $alias;
        $reg['students'][$stuId]['aliases'] = $aliases;
    }
    $stats['aliases_added']++;
    return $reg;
}

function identity58_alias_type(string $alias): string
{
    if (preg_match('/^(dir|local|google|email|lab):/', $alias, $m)) return $m[1];
    if (preg_match('/^[a-z0-9_]+:(student|s|n|l|g):/', $alias, $m)) return 'key_' . $m[1];
    return 'other';
}

/**
 * Najde nebo založí žáka pro (třída, jméno). Pořadí: uložené student_id → aliasy účtu → řádek adresáře (dir:).
 * Vrací [registr, stu_id].
 */
function identity58_resolve(array $reg, string $classId, string $label, array $accountAliases, ?string $preferred, array $flags, int $now, array &$stats): array
{
    $keys = identity58_keys_for($classId, $label);
    $stuId = ($preferred !== null && isset($reg['students'][$preferred])) ? $preferred : null;
    // Hledá se jen podle účtu a řádku adresáře (dir:). Datové klíče (třída + jméno) se opakují mezi ročníky,
    // proto se podle nich nikdy nepřiřazuje – jen se přidají (a případná kolize se nahlásí).
    foreach (array_merge($accountAliases, array_slice($keys, 0, 1)) as $alias) {
        if ($stuId !== null) break;
        $hit = $reg['alias_index'][$alias] ?? null;
        if (is_string($hit) && isset($reg['students'][$hit])) $stuId = $hit;
    }
    if ($stuId === null) {
        $stuId = identity58_new_id($reg);
        $reg['students'][$stuId] = [
            'class_id' => $classId, 'label' => $label, 'cohort' => identity58_cohort($classId, identity58_school_year($reg, $now)),
            'status' => 'active', 'aliases' => [], 'created_at' => date(DATE_ATOM, $now),
        ];
        $stats['created']++;
    }
    if (!empty($flags['demo'])) $reg['students'][$stuId]['demo'] = true;
    // Klíče ze jména jen pro aktuální třídu žáka (u přesunutého žáka je řádek účtu už v nové třídě).
    $stuClass = (string)($reg['students'][$stuId]['class_id'] ?? '');
    foreach ($accountAliases as $alias) $reg = identity58_with_alias($reg, $stuId, $alias, $stats);
    if ($stuClass === $classId) {
        foreach ($keys as $alias) $reg = identity58_with_alias($reg, $stuId, $alias, $stats);
    }
    return [$reg, $stuId];
}

/**
 * Čistý výpočet sestavení. Vstupy se předávají, takže funkce nic nečte ani nezapisuje.
 * Vrací ['registry', 'stats', 'map_patches' => [mapKey => stu], 'local_patches' => [email => stu]].
 */
function identity58_compute(array $reg, array $dirRows, array $accountMap, array $localAccounts, int $now): array
{
    $reg = identity58_normalize($reg);
    $reg['conflicts'] = [];
    $stats = ['created' => 0, 'aliases_added' => 0, 'directory_rows' => 0, 'accounts' => 0, 'unlinked_accounts' => 0];
    foreach ($dirRows as $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        if ($classId === '' || normalized_person_name($label) === '') continue;
        $stats['directory_rows']++;
        $preferred = isset($row['identity58_stu']) ? (string)$row['identity58_stu'] : null;
        [$reg] = identity58_resolve($reg, $classId, $label, [], $preferred, [], $now, $stats);
    }
    $mapPatches = [];
    $mapStu = [];
    foreach ($accountMap as $mapKey => $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $label = trim((string)($row['student_label'] ?? ''));
        if ($classId === '' || $label === '') { $stats['unlinked_accounts']++; continue; }
        $stats['accounts']++;
        $aliases = identity58_account_aliases((string)$mapKey, $classId, (string)($row['email'] ?? ''));
        $preferred = isset($row['student_id']) ? (string)$row['student_id'] : null;
        $local = $localAccounts[strtolower((string)($row['email'] ?? ''))] ?? null;
        $demo = is_array($local) && !empty($local['demo_account']);
        [$reg, $stuId] = identity58_resolve($reg, $classId, $label, $aliases, $preferred, ['demo' => $demo], $now, $stats);
        $mapStu[(string)$mapKey] = $stuId;
        if ((string)($row['student_id'] ?? '') !== $stuId) $mapPatches[(string)$mapKey] = $stuId;
    }
    $localPatches = [];
    foreach ($localAccounts as $email => $acc) {
        if (!is_array($acc)) continue;
        $id = (string)($acc['id'] ?? '');
        $classId = (string)($acc['class_id'] ?? '');
        $label = trim((string)($acc['student_label'] ?? ''));
        if ($id === '' || $classId === '' || $label === '') continue;
        $stuId = $mapStu['local:' . $id] ?? null;
        if ($stuId === null) {
            $preferred = isset($acc['student_id']) ? (string)$acc['student_id'] : null;
            [$reg, $stuId] = identity58_resolve($reg, $classId, $label, identity58_account_aliases('local:' . $id, $classId, (string)$email),
                $preferred, ['demo' => !empty($acc['demo_account'])], $now, $stats);
        }
        if ((string)($acc['student_id'] ?? '') !== $stuId) $localPatches[(string)$email] = $stuId;
    }
    $reg['conflicts'] = array_values($reg['conflicts']);
    ksort($reg['alias_index']);
    $stats['students'] = count($reg['students']);
    $stats['aliases'] = count($reg['alias_index']);
    $stats['conflicts'] = count($reg['conflicts']);
    $stats['map_patches'] = count($mapPatches);
    $stats['local_patches'] = count($localPatches);
    return ['registry' => $reg, 'stats' => $stats, 'map_patches' => $mapPatches, 'local_patches' => $localPatches];
}

/** Levná signatura zdrojů (adresář + velikost/čas souborů účtů) pro identity58_ensure(). */
function identity58_source_signature(): string
{
    clearstatcache();
    $parts = [];
    foreach (identity58_directory_rows() as $row) {
        if (is_array($row)) $parts[] = (string)($row['class_id'] ?? '') . '|' . normalized_person_name(trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')));
    }
    foreach ([STORAGE_DIR . '/student_accounts.json.php', local_accounts_path()] as $file) {
        $parts[] = is_file($file) ? (string)filesize($file) . '@' . (string)filemtime($file) : '-';
    }
    return substr(hash('sha256', implode("\n", $parts)), 0, 32);
}

/** Porovnání registrů bez časových razítek sestavení. */
function identity58_same(array $a, array $b): bool
{
    unset($a['built_at'], $a['signature'], $b['built_at'], $b['signature']);
    return json_encode($a) === json_encode($b);
}

/**
 * Sestaví registr z adresáře, mapy účtů a lokálních účtů. Idempotentní: druhé spuštění nic nezmění.
 * $dryRun = true: nic nezapíše, jen vrátí statistiky. Jinak zapíše registr a doplní `student_id`
 * do mapy účtů a lokálních účtů (jen chybějící nebo neplatné, každý soubor zvlášť, zámky se nevnořují).
 */
function identity58_build(bool $dryRun): array
{
    $now = time();
    $dirRows = identity58_directory_rows();
    $map = student_account_map();
    $locals = local_accounts();
    if ($dryRun) {
        $res = identity58_compute(identity58_registry(), $dirRows, $map, $locals, $now);
        return $res['stats'] + ['dry_run' => true, 'changed' => !identity58_same(identity58_registry(), $res['registry'])];
    }
    $res = null;
    $changed = false;
    storage_update(identity58_path(), static function (array $data) use ($dirRows, $map, $locals, $now, &$res, &$changed): array {
        $old = identity58_normalize($data);
        $res = identity58_compute($old, $dirRows, $map, $locals, $now);
        $changed = !identity58_same($old, $res['registry']);
        return $changed ? array_replace($res['registry'], ['built_at' => date(DATE_ATOM, $now)]) : $old;
    });
    identity58_patch_accounts($res['map_patches'], $res['local_patches']);
    $signature = identity58_source_signature();
    storage_update(identity58_path(), static fn(array $d): array => array_replace(identity58_normalize($d), ['signature' => $signature]));
    return $res['stats'] + ['dry_run' => false, 'changed' => $changed];
}

/** Zapíše student_id do mapy účtů a lokálních účtů (jen existující řádky; nic jiného nemění). */
function identity58_patch_accounts(array $mapPatches, array $localPatches): void
{
    if ($mapPatches) {
        storage_update(STORAGE_DIR . '/student_accounts.json.php', static function (array $rows) use ($mapPatches): array {
            foreach ($mapPatches as $key => $stuId) {
                if (is_array($rows[$key] ?? null)) $rows[$key]['student_id'] = $stuId;
            }
            return $rows;
        });
    }
    if ($localPatches) {
        storage_update(local_accounts_path(), static function (array $rows) use ($localPatches): array {
            foreach ($localPatches as $email => $stuId) {
                if (is_array($rows[$email] ?? null)) $rows[$email]['student_id'] = $stuId;
            }
            return $rows;
        });
    }
}

/** Háček pro provisioning a request: sestaví registr jen při změně zdrojů (porovnání signatury). */
function identity58_ensure(): bool
{
    if (identity58_registry()['signature'] === identity58_source_signature()) return false;
    identity58_build(false);
    return true;
}

// ---------------------------------------------------------------------------
// Vyhledávání
// ---------------------------------------------------------------------------

function identity58_id_for_alias(string $alias): ?string
{
    $alias = trim($alias);
    if ($alias === '') return null;
    $index = identity58_registry()['alias_index'];
    $hit = $index[$alias] ?? null;
    if (!is_string($hit) && ctype_digit($alias)) $hit = $index['google:' . $alias] ?? null;
    if (!is_string($hit) && str_contains($alias, '@')) $hit = $index['email:' . strtolower($alias)] ?? null;
    return is_string($hit) ? $hit : null;
}

function identity58_id_for_student(string $classId, string $label): ?string
{
    foreach (identity58_keys_for($classId, $label) as $alias) {
        $hit = identity58_id_for_alias($alias);
        if ($hit !== null) return $hit;
    }
    return null;
}

/** student_id přihlášeného žáka: účet (mapa účtů) → třída a jméno ze session. */
function identity58_current_student_id(): ?string
{
    $user = function_exists('auth_user') ? auth_user() : null;
    if (is_array($user)) {
        $key = auth_assignment_key($user);
        $alias = str_starts_with($key, 'local:') ? $key : 'google:' . $key;
        if ($key !== '' && ($hit = identity58_id_for_alias($alias)) !== null) return $hit;
    }
    $classId = (string)($_SESSION['next_class_id'] ?? '');
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    return $classId !== '' && $label !== '' ? identity58_id_for_student($classId, $label) : null;
}

function identity58_student(string $stuId): ?array
{
    $row = identity58_registry()['students'][$stuId] ?? null;
    return is_array($row) ? $row : null;
}

function identity58_aliases(string $stuId): array
{
    return array_values(array_map('strval', (array)(identity58_student($stuId)['aliases'] ?? [])));
}

// ---------------------------------------------------------------------------
// Kolize jmen (jen hlášení – nic se automaticky neslučuje ani nerozděluje)
// ---------------------------------------------------------------------------

/**
 * Podezřelé případy:
 *  directory_namesake – ve stejné třídě víc řádků adresáře se stejným jménem (sdílejí klíč → sloučená data),
 *  shared_account     – jeden žák má víc školních účtů (dva lidé pod jedním klíčem, nebo zdvojený účet),
 *  alias_conflict     – klíč už patří jinému žákovi (typicky jmenovec z minulého roku ve stejné třídě),
 *  same_name          – aktivní žáci se stejným jménem v různých třídách (informace, např. ruční přesun).
 * Vrací [['type', 'severity' (high|medium|info), 'class_id', 'label', 'stu_ids', 'detail'], …].
 */
function identity58_duplicates(?array $reg = null, ?array $dirRows = null): array
{
    $reg = $reg ?? identity58_registry();
    $students = $reg['students'];
    $dirRows = $dirRows ?? identity58_directory_rows($reg);
    $out = [];
    $groups = [];
    foreach ($dirRows as $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        $groups[identity58_dir_key($classId, $label)][] = ['class_id' => $classId, 'label' => $label];
    }
    foreach ($groups as $key => $rows) {
        if (count($rows) < 2) continue;
        $stuId = (string)($reg['alias_index']['dir:' . $key] ?? '');
        $out[] = ['type' => 'directory_namesake', 'severity' => 'high', 'class_id' => $rows[0]['class_id'], 'label' => $rows[0]['label'],
            'stu_ids' => array_values(array_filter([$stuId])), 'detail' => count($rows) . ' řádky adresáře se stejným jménem sdílejí jeden klíč – data jsou sloučená.'];
    }
    foreach ($students as $stuId => $stu) {
        if (!is_array($stu) || ($stu['status'] ?? 'active') !== 'active') continue;
        $accounts = array_filter((array)($stu['aliases'] ?? []), static fn($a): bool => str_starts_with((string)$a, 'local:'));
        if (count($accounts) > 1) {
            $out[] = ['type' => 'shared_account', 'severity' => 'high', 'class_id' => (string)$stu['class_id'], 'label' => (string)$stu['label'],
                'stu_ids' => [(string)$stuId], 'detail' => count($accounts) . ' školní účty vedou na stejného žáka – ověř, zda nejde o dva jmenovce.'];
        }
    }
    foreach ($reg['conflicts'] as $c) {
        $ids = array_values(array_filter((array)($c['stu_ids'] ?? []), static fn($id): bool => isset($students[$id])));
        if (count($ids) < 2) continue;
        $b = $students[$ids[1]];
        $out[] = ['type' => 'alias_conflict', 'severity' => 'high', 'class_id' => (string)$b['class_id'], 'label' => (string)$b['label'],
            'stu_ids' => $ids, 'detail' => 'Klíč (' . (string)($c['type'] ?? '') . ') už patří jinému žákovi – jmenovec by viděl cizí data. Rozliš jméno.'];
    }
    $byName = [];
    foreach ($students as $stuId => $stu) {
        if (is_array($stu) && ($stu['status'] ?? 'active') === 'active' && empty($stu['demo'])) $byName[normalized_person_name((string)$stu['label'])][] = (string)$stuId;
    }
    foreach ($byName as $ids) {
        $classes = array_unique(array_map(static fn(string $id): string => (string)$students[$id]['class_id'], $ids));
        if (count($ids) < 2 || count($classes) < 2) continue;
        $out[] = ['type' => 'same_name', 'severity' => 'info', 'class_id' => implode(', ', $classes), 'label' => (string)$students[$ids[0]]['label'],
            'stu_ids' => $ids, 'detail' => 'Stejné jméno ve více třídách (jiní žáci, nebo ruční přesun bez přechodu roku).'];
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Háčky pro přihlášení a kódy tříd
// ---------------------------------------------------------------------------

/** Pro acc58_login_gate(): archivovaný / odešlý účet se nepřihlásí. */
function identity58_login_gate(array $account): ?string
{
    if (!empty($account['login_blocked'])) return IDENTITY58_MSG_BLOCKED;
    $stuId = (string)($account['student_id'] ?? '');
    if ($stuId === '') return null;
    $stu = identity58_student($stuId);
    return is_array($stu) && ($stu['status'] ?? 'active') !== 'active' && !empty($stu['login_blocked']) ? IDENTITY58_MSG_BLOCKED : null;
}

/** Pro try_restore_auth_assignment() a bind_auth_account(): archivovaný řádek mapy účtů neobnoví třídu. */
function identity58_assignment_allowed(array $mapRow): bool
{
    if (in_array((string)($mapRow['status'] ?? 'active'), ['archived', 'left'], true)) return false;
    $stuId = (string)($mapRow['student_id'] ?? '');
    if ($stuId === '') return true;
    $stu = identity58_student($stuId);
    return !is_array($stu) || ($stu['status'] ?? 'active') === 'active';
}

/** Nové kódy tříd po přechodu roku (prázdné pole = platí kódy z modules.php). */
function identity58_class_codes(): array
{
    return array_map('strval', identity58_registry()['class_codes']);
}

function identity58_class_code(string $classId, string $default): string
{
    return identity58_class_codes()[$classId] ?? $default;
}
