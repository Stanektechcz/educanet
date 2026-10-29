<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – rozšiřitelné jádro (registry, kontexty, události, filtry).
 *
 * Načítá ho linux_v57_lab.php (na konci, když už jsou definované všechny v57 funkce).
 * Tento soubor pak načte lab_v58_log.php a všechna rozšíření linux_v58_cmd_*.php
 * a linux_v58_levels_*.php z kořene projektu (pevný vzor jména, abecední pořadí).
 * Dokumentace API: docs/LAB_V58_API.md.
 *
 * BEZPEČNOSTNÍ INVARIANT platí i tady: nic se nespouští, žádná síť. Registry obsahují jen
 * jména funkcí a closures z vlastních souborů projektu – nikdy nic od žáka.
 */

require_once __DIR__ . '/lab_v58_log.php';

const LAB58_CMD_RE = '/^[a-z0-9][a-z0-9._+-]{0,31}$/';
const LAB58_ID_RE = '/^[a-z0-9-]{2,32}$/';
const LAB58_PACK_RE = '/^[a-z0-9-]{2,24}$/';
const LAB58_CTX_RE = '/^([a-z]{2,12}):([a-z0-9_-]{4,40})$/';
const LAB58_EXT_FILE_RE = '/^linux_v58_(cmd|levels)_[a-z0-9_]{1,40}\.php$/';
const LAB58_PLAYER_FIELDS = ['cwd', 'oldCwd', 'history', 'lastExit', 'env', 'aliases'];
const LAB58_CLOCK_MAX = 315360000; // ±10 let

// ---------------------------------------------------------------------------
// Registr (jeden na požadavek), verze pro zneplatnění cache
// ---------------------------------------------------------------------------

function &lab58_registry(): array
{
    static $reg = ['ver' => 1, 'commands' => [], 'manual' => [], 'packs' => [], 'pack_levels' => [], 'sources' => [], 'generators' => [], 'checks' => [],
        'contexts' => [], 'listeners' => [], 'filters' => [], 'extenders' => [], 'apt' => [], 'errors' => [], 'loaded' => []];
    return $reg;
}

function lab58_registry_version(): int
{
    return (int)lab58_registry()['ver'];
}

function lab58_bump(): void
{
    $r = &lab58_registry();
    $r['ver']++;
}

/** Chyba registrace se neprojeví žákovi: zaznamená se (audit ji hlásí) a zapíše do error_log. */
function lab58_reg_error(string $message): bool
{
    $r = &lab58_registry();
    if (!in_array($message, $r['errors'], true) && count($r['errors']) < 200) $r['errors'][] = $message;
    error_log('EDUCANET v58 lab ext: ' . $message);
    return false;
}

/** @return list<string> */
function lab58_registry_errors(): array
{
    return lab58_registry()['errors'];
}

// ---------------------------------------------------------------------------
// 1) Příkazy
// ---------------------------------------------------------------------------

/**
 * Zaregistruje příkaz. $handler = jméno PHP funkce fn(Lab57Proc $p, array $argv): int (jako v57).
 * $meta: package (?string, gating přes apt), bin (absolutní cesta, výchozí /usr/bin/<name>),
 * builtin (bool, vestavěný příkaz shellu – nehledá se v $PATH), help (bool, výchozí true =
 * „--help“ obslouží příručka), override (bool, jen pro vědomé nahrazení v57 příkazu).
 */
function lab58_register_command(string $name, string $handler, array $meta = []): bool
{
    if (preg_match(LAB58_CMD_RE, $name) !== 1) return lab58_reg_error("příkaz '$name': neplatné jméno");
    if (!function_exists($handler)) return lab58_reg_error("příkaz '$name': funkce $handler neexistuje (registruj až po její definici)");
    $r = &lab58_registry();
    if (isset($r['commands'][$name])) return lab58_reg_error("příkaz '$name' je už zaregistrovaný");
    if (isset(lab57_command_registry_core()[$name]) && empty($meta['override'])) return lab58_reg_error("příkaz '$name' už existuje ve v57 (použij meta override => true)");
    $bin = (string)($meta['bin'] ?? '/usr/bin/' . $name);
    if (preg_match('~^(/[a-z0-9._+-]+)+$~', $bin) !== 1) return lab58_reg_error("příkaz '$name': neplatná cesta bin '$bin'");
    $package = isset($meta['package']) ? (string)$meta['package'] : null;
    if ($package !== null && preg_match('/^[a-z0-9][a-z0-9.+-]{1,40}$/', $package) !== 1) return lab58_reg_error("příkaz '$name': neplatný balíček");
    $r['commands'][$name] = ['handler' => $handler, 'package' => $package, 'bin' => $bin, 'builtin' => !empty($meta['builtin']), 'help' => (bool)($meta['help'] ?? true)];
    lab58_bump();
    return true;
}

/** @return array<string,string> jméno → funkce (jen v58 příkazy) */
function lab58_commands(): array
{
    return array_map(static fn(array $c): string => $c['handler'], lab58_registry()['commands']);
}

function lab58_command_meta(string $name): ?array
{
    return lab58_registry()['commands'][$name] ?? null;
}

/**
 * Balíček pro apt (install/remove/search/list). $info: version, description, size, installed_size,
 * bins (list příkazů), preinstalled (bool – je ve výchozím světě), removable (bool, výchozí true).
 */
function lab58_register_apt_package(string $name, array $info): bool
{
    if (preg_match('/^[a-z0-9][a-z0-9.+-]{1,40}$/', $name) !== 1) return lab58_reg_error("balíček '$name': neplatné jméno");
    $r = &lab58_registry();
    $r['apt'][$name] = [
        'version' => (string)($info['version'] ?? '1.0-1'), 'description' => (string)($info['description'] ?? 'simulovaný balíček'),
        'size' => (string)($info['size'] ?? '42.0 kB'), 'installed_size' => (string)($info['installed_size'] ?? '120 kB'),
        'bins' => array_values(array_map('strval', (array)($info['bins'] ?? []))), 'preinstalled' => !empty($info['preinstalled']), 'removable' => (bool)($info['removable'] ?? true),
    ];
    lab58_bump();
    return true;
}

/** @return array<string,array> balíčky v58 včetně automatických (balíček uvedený jen v meta příkazu) */
function lab58_apt_packages(): array
{
    $r = lab58_registry();
    $out = $r['apt'];
    foreach ($r['commands'] as $name => $cmd) {
        if ($cmd['package'] === null) continue;
        $out[$cmd['package']] ??= ['version' => '1.0-1', 'description' => 'simulovaný balíček', 'size' => '42.0 kB', 'installed_size' => '120 kB', 'bins' => [], 'preinstalled' => false, 'removable' => true];
        if (!in_array($name, $out[$cmd['package']]['bins'], true)) $out[$cmd['package']]['bins'][] = $name;
    }
    return $out;
}

/** @return array<string,string> předinstalované balíčky v58 (jméno → verze) */
function lab58_default_packages(): array
{
    static $cache = [];
    static $ver = -1;
    if ($ver === lab58_registry_version()) return $cache;
    $cache = [];
    foreach (lab58_apt_packages() as $name => $info) if ($info['preinstalled']) $cache[$name] = $info['version'];
    $ver = lab58_registry_version();
    return $cache;
}

// ---------------------------------------------------------------------------
// 2) Příručka
// ---------------------------------------------------------------------------

/**
 * $entries: ['commands' => [jméno => položka], 'concepts' => [...], 'categories' => [...]]
 * nebo přímo mapa jméno → položka (bere se jako commands). Položka příkazu jako v57
 * (cat, summary, synopsis, about, options, examples, related, level, in_lab, tip, warn)
 * + volitelně tldr (list [příkaz, popis]) a see_also (list jmen).
 * 'extend' => true u existujícího příkazu jen doplní tldr, see_also, examples a prázdný tip.
 */
function lab58_register_manual(array $entries): bool
{
    if (!isset($entries['commands']) && !isset($entries['concepts']) && !isset($entries['categories'])) $entries = ['commands' => $entries];
    $r = &lab58_registry();
    $r['manual'][] = $entries;
    lab58_bump();
    return true;
}

/** Sloučená příručka (v57 + v58). v57_manual() vrací totéž. */
function lab58_manual(): array
{
    static $cache = null;
    static $ver = -1;
    if (is_array($cache) && $ver === lab58_registry_version()) return $cache;
    $manual = v57_manual_base();
    foreach (lab58_registry()['manual'] as $entries) {
        foreach ((array)($entries['categories'] ?? []) as $key => $cat) {
            if (!isset($manual['categories'][$key]) && is_array($cat) && isset($cat['label'])) $manual['categories'][$key] = $cat + ['icon' => '•', 'lead' => ''];
        }
        foreach ((array)($entries['concepts'] ?? []) as $key => $concept) {
            if (is_array($concept) && isset($concept['title'], $concept['about']) && !isset($manual['concepts'][$key])) $manual['concepts'][$key] = $concept + ['examples' => []];
        }
        foreach ((array)($entries['commands'] ?? []) as $name => $entry) {
            $name = (string)$name;
            if (!is_array($entry) || preg_match(LAB58_CMD_RE, $name) !== 1) { lab58_reg_error("příručka '$name': neplatná položka"); continue; }
            if (isset($manual['commands'][$name])) {
                if (empty($entry['extend'])) { lab58_reg_error("příručka '$name' už existuje (použij extend => true)"); continue; }
                $cur = $manual['commands'][$name];
                $cur['tldr'] = array_merge((array)($cur['tldr'] ?? []), (array)($entry['tldr'] ?? []));
                $cur['see_also'] = array_values(array_unique(array_merge((array)($cur['see_also'] ?? []), (array)($entry['see_also'] ?? []))));
                $cur['examples'] = array_merge((array)$cur['examples'], (array)($entry['examples'] ?? []));
                if (($cur['tip'] ?? '') === '' && isset($entry['tip'])) $cur['tip'] = (string)$entry['tip'];
                // v58: rozšíření, které příkaz opravdu implementuje, smí přepsat popis a volby převzaté z v57.
                if (!empty($entry['override'])) foreach (['summary', 'synopsis', 'about', 'options', 'warn'] as $k) if (array_key_exists($k, $entry)) $cur[$k] = $entry[$k];
                $manual['commands'][$name] = $cur;
                continue;
            }
            if (!isset($entry['summary'], $entry['synopsis'], $entry['about'], $entry['cat'])) { lab58_reg_error("příručka '$name': chybí cat/summary/synopsis/about"); continue; }
            $manual['commands'][$name] = $entry + ['options' => [], 'examples' => [], 'related' => [], 'level' => 1, 'in_lab' => isset(lab58_registry()['commands'][$name]), 'tip' => '', 'warn' => '', 'tldr' => [], 'see_also' => []];
            unset($manual['commands'][$name]['extend']);
        }
    }
    // v58: příkaz registrovaný rozšířením v labu opravdu běží → příznak in_lab i u položek převzatých z v57 (ssh, tar, crontab …).
    foreach (array_keys(lab58_registry()['commands']) as $cmdName) {
        if (isset($manual['commands'][$cmdName])) $manual['commands'][$cmdName]['in_lab'] = true;
    }
    $ver = lab58_registry_version();
    return $cache = $manual;
}

// ---------------------------------------------------------------------------
// 3) Balíčky a úrovně
// ---------------------------------------------------------------------------

/** @return array|string normalizovaný balíček, nebo chyba */
function lab58_pack_normalize(array $pack, int $order = 100): array|string
{
    $id = (string)($pack['id'] ?? '');
    if (preg_match(LAB58_PACK_RE, $id) !== 1) return "balíček '$id': neplatné id";
    if (trim((string)($pack['title'] ?? '')) === '') return "balíček '$id': chybí title";
    $classes = $pack['classes'] ?? null;
    if ($classes !== null) {
        if (!is_array($classes) || $classes === []) return "balíček '$id': classes musí být null nebo neprázdný seznam";
        foreach ($classes as $c) if (!is_string($c) || preg_match('/^[a-z0-9_]{2,40}$/', $c) !== 1) return "balíček '$id': neplatná třída v classes";
        $classes = array_values($classes);
    }
    $unlock = (string)($pack['unlock'] ?? 'sequential');
    if (!in_array($unlock, ['sequential', 'free'], true)) return "balíček '$id': unlock musí být sequential nebo free";
    $description = (string)($pack['description'] ?? ($pack['lead'] ?? ''));
    return ['id' => $id, 'title' => (string)$pack['title'], 'description' => $description, 'lead' => $description, 'order' => (int)($pack['order'] ?? $order),
        'classes' => $classes, 'unlock' => $unlock, 'badge' => $pack['badge'] ?? null, 'icon' => (string)($pack['icon'] ?? '›_'), 'tone' => (string)($pack['tone'] ?? 'teal'),
        'inspired' => (string)($pack['inspired'] ?? ''), 'source' => (string)($pack['source'] ?? 'v58')];
}

/** $levels = callable(): list<array> – volá se líně, jednou za požadavek. */
function lab58_register_pack(array $pack, callable $levels): bool
{
    $norm = lab58_pack_normalize($pack);
    if (is_string($norm)) return lab58_reg_error($norm);
    $r = &lab58_registry();
    if (isset(lab57_packs()[$norm['id']]) || $norm['id'] === 'free' || isset($r['packs'][$norm['id']])) return lab58_reg_error("balíček '{$norm['id']}' už existuje");
    $r['packs'][$norm['id']] = $norm;
    $r['pack_levels'][$norm['id']] = $levels;
    lab58_bump();
    return true;
}

/** $provider = callable(): list<level> | array{packs?:list<pack>, levels?:list<level>} (např. úrovně učitele z úložiště). */
function lab58_register_level_source(callable $provider): bool
{
    $r = &lab58_registry();
    $r['sources'][] = $provider;
    lab58_bump();
    return true;
}

/** @return array{packs:array<string,array>,levels:list<array>} výsledky zdrojů (cache na verzi registru) */
function lab58_source_data(): array
{
    static $cache = null;
    static $ver = -1;
    if (is_array($cache) && $ver === lab58_registry_version()) return $cache;
    $packs = [];
    $levels = [];
    foreach (lab58_registry()['sources'] as $i => $provider) {
        try {
            $data = (array)$provider();
        } catch (Throwable $e) {
            lab58_reg_error('zdroj úrovní #' . $i . ': ' . $e->getMessage());
            continue;
        }
        $list = array_key_exists('levels', $data) || array_key_exists('packs', $data) ? (array)($data['levels'] ?? []) : $data;
        foreach ((array)($data['packs'] ?? []) as $pack) {
            $norm = is_array($pack) ? lab58_pack_normalize($pack + ['source' => 'store']) : 'zdroj: neplatný balíček';
            if (is_string($norm)) { lab58_reg_error($norm); continue; }
            if (isset(lab57_packs()[$norm['id']]) || isset(lab58_registry()['packs'][$norm['id']])) { lab58_reg_error("zdroj: balíček '{$norm['id']}' už existuje"); continue; }
            $packs[$norm['id']] = $norm;
        }
        foreach ($list as $level) if (is_array($level)) $levels[] = $level;
    }
    $ver = lab58_registry_version();
    return $cache = ['packs' => $packs, 'levels' => $levels];
}

/** Všechny balíčky (v57 + v58 + zdroje), seřazené podle order. */
function lab58_packs(): array
{
    static $cache = null;
    static $ver = -1;
    if (is_array($cache) && $ver === lab58_registry_version()) return $cache;
    $out = [];
    $i = 0;
    foreach (lab57_packs() as $id => $pack) {
        $i++;
        $norm = lab58_pack_normalize(['id' => $id, 'description' => $pack['lead'], 'order' => $i * 10, 'source' => 'v57'] + $pack);
        if (is_array($norm)) $out[$id] = $norm;
    }
    $out += lab58_registry()['packs'] + lab58_source_data()['packs'];
    uasort($out, static fn(array $a, array $b): int => [$a['order'], $a['id']] <=> [$b['order'], $b['id']]);
    $ver = lab58_registry_version();
    return $cache = $out;
}

function lab58_pack(string $id): ?array
{
    return lab58_packs()[$id] ?? null;
}

function lab58_pack_allows_class(string $packId, string $classId): bool
{
    $pack = lab58_pack($packId);
    if ($pack === null || $pack['classes'] === null) return true;
    return in_array($classId, $pack['classes'], true);
}

/** Balíčky viditelné pro třídu (classes === null nebo třída v seznamu), jen neprázdné. */
function lab58_packs_for_class(string $classId): array
{
    $out = [];
    foreach (lab58_packs() as $id => $pack) {
        if (!lab58_pack_allows_class((string)$id, $classId) || lab57_pack_levels((string)$id) === []) continue;
        $out[$id] = $pack;
    }
    return $out;
}

/** Úrovně v58 (balíčky + zdroje), normalizované; volá lab57_levels(). @return array<string,array> */
function lab58_extra_levels(array $existing): array
{
    $out = [];
    $add = static function (array $level, string $pack, int $no) use (&$out, $existing): void {
        $prepared = lab58_level_prepare($level, $pack, $no);
        if (is_string($prepared)) { lab58_reg_error($prepared); return; }
        if (isset($existing[$prepared['id']]) || isset($out[$prepared['id']])) { lab58_reg_error("úroveň '{$prepared['id']}' už existuje"); return; }
        $out[$prepared['id']] = $prepared;
    };
    foreach (lab58_registry()['pack_levels'] as $packId => $provider) {
        try {
            $list = array_values((array)$provider());
        } catch (Throwable $e) {
            lab58_reg_error("balíček '$packId': " . $e->getMessage());
            continue;
        }
        foreach ($list as $no => $level) if (is_array($level)) $add($level, (string)$packId, $no + 1);
    }
    $counts = [];
    foreach (lab58_source_data()['levels'] as $level) {
        $pack = (string)($level['pack'] ?? '');
        if (lab58_pack($pack) === null) { lab58_reg_error("úroveň '" . ($level['id'] ?? '?') . "': neznámý balíček '$pack'"); continue; }
        $counts[$pack] = ($counts[$pack] ?? 0) + 1;
        $add($level, $pack, $counts[$pack]);
    }
    return $out;
}

/** Doplní výchozí hodnoty, ověří tvar a převede deklarativní části (generate/checks/answer/solution) na closures. */
function lab58_level_prepare(array $level, string $packId, int $no): array|string
{
    $id = (string)($level['id'] ?? '');
    if (preg_match(LAB58_ID_RE, $id) !== 1 || $id === 'sandbox') return "úroveň '$id': neplatné id (a-z, 0-9, -, 2–32 znaků)";
    $level['pack'] ??= $packId;
    if ($level['pack'] !== $packId) return "úroveň '$id': pack '{$level['pack']}' nesedí s balíčkem '$packId'";
    foreach (['title', 'story', 'task'] as $key) if (trim((string)($level[$key] ?? '')) === '') return "úroveň '$id': chybí $key";
    if (!in_array($level['type'] ?? '', ['code', 'answer', 'check', 'golf'], true)) return "úroveň '$id': type musí být code|answer|check|golf";
    $level['no'] = $no;
    $level += ['v' => 1, 'difficulty' => 1, 'points' => 100, 'minutes' => 5, 'commands' => [], 'hints' => [], 'world' => [], 'topology' => false, 'learn' => ''];
    if (isset($level['generate'])) {
        if (!is_array($level['generate'])) return "úroveň '$id': generate musí být seznam [jméno, parametry]";
        $gen = array_values($level['generate']);
        $orig = $level['build'] ?? null;
        $level['build'] = static function (Lab57World $w, Lab57Rng $r) use ($gen, $orig): void {
            lab58_run_generators($w, $gen);
            if (is_callable($orig)) $orig($w, $r);
        };
    }
    if (isset($level['checks'])) {
        $checks = [];
        foreach ((array)$level['checks'] as $check) {
            $item = lab58_check_item($check);
            if (is_string($item)) return "úroveň '$id': $item";
            $checks[] = $item;
        }
        $level['checks'] = $checks;
    }
    if (is_string($level['answer'] ?? null)) {
        $tpl = $level['answer'];
        $level['answer'] = static fn(Lab57World $w): string => lab58_fill($w, $tpl);
    }
    if (is_array($level['solution'] ?? null)) {
        $lines = array_values(array_map('strval', $level['solution']));
        $level['solution'] = static fn(Lab57World $w): array => array_map(static fn(string $l): string => lab58_fill($w, $l), $lines);
    }
    if ($level['type'] === 'answer' && !is_callable($level['answer'] ?? null)) return "úroveň '$id': answer úroveň potřebuje answer";
    if ($level['type'] === 'check' && ($level['checks'] ?? []) === []) return "úroveň '$id': check úroveň potřebuje checks";
    if ($level['type'] === 'golf' && trim((string)($level['golf']['reference'] ?? '')) === '') return "úroveň '$id': golf úroveň potřebuje golf.reference";
    return $level;
}

/** Úprava úrovně pro roli hráče: level['roles'][role] přepíše story/task/hints/commands/learn. */
function lab58_level_for_role(array $level, ?string $role): array
{
    $level['role'] = $role;
    if ($role === null || !is_array($level['roles'][$role] ?? null)) return $level;
    foreach (['story', 'task', 'hints', 'commands', 'learn'] as $key) {
        if (array_key_exists($key, $level['roles'][$role])) $level[$key] = $level['roles'][$role][$key];
    }
    return $level;
}

// Deklarativní úrovně (generátory, kontroly, šablony) jsou v linux_v57_levels.php (sekce v58).

// ---------------------------------------------------------------------------
// 4) Kontexty (practice, race:<id>, další registrované)
// ---------------------------------------------------------------------------

const LAB58_CTX_CALLBACKS = ['access', 'levels', 'state_key', 'seed', 'points', 'on_complete', 'events_path', 'role', 'info', 'board'];

function lab58_register_context(string $prefix, array $spec): bool
{
    if (preg_match('/^[a-z]{2,12}$/', $prefix) !== 1) return lab58_reg_error("kontext '$prefix': neplatný prefix");
    $r = &lab58_registry();
    if (isset($r['contexts'][$prefix])) return lab58_reg_error("kontext '$prefix' už existuje");
    foreach (LAB58_CTX_CALLBACKS as $cb) {
        if (isset($spec[$cb]) && !is_callable($spec[$cb])) return lab58_reg_error("kontext '$prefix': $cb není callable");
    }
    $spec['label'] = (string)($spec['label'] ?? $prefix);
    $spec['first_blood'] = !empty($spec['first_blood']);
    $spec['reset'] = (bool)($spec['reset'] ?? true);
    $r['contexts'][$prefix] = $spec;
    lab58_bump();
    return true;
}

function lab58_context_spec(string $prefix): ?array
{
    return lab58_registry()['contexts'][$prefix] ?? null;
}

/** @return array{prefix:string,id:string}|null */
function lab58_context_parse(string $context): ?array
{
    if ($context === 'practice') return ['prefix' => 'practice', 'id' => ''];
    if (preg_match(LAB58_CTX_RE, $context, $m) !== 1 || $m[1] === 'practice') return null;
    return ['prefix' => $m[1], 'id' => $m[2]];
}

/** Platný kontext pro API: practice, nebo prefix:id s registrovaným prefixem. */
function lab58_context_valid(string $context): bool
{
    $parsed = lab58_context_parse($context);
    return $parsed !== null && lab58_context_spec($parsed['prefix']) !== null;
}

/** Zavolá callback kontextu; výjimka se zaloguje a vrátí se $default. */
function lab58_ctx_call(array $ctx, string $cb, array $args, mixed $default): mixed
{
    $spec = lab58_context_spec((string)($ctx['prefix'] ?? ''));
    if ($spec === null || !isset($spec[$cb])) return $default;
    try {
        return ($spec[$cb])(...$args);
    } catch (Throwable $e) {
        error_log('EDUCANET v58 lab context ' . ($ctx['prefix'] ?? '?') . '.' . $cb . ': ' . $e->getMessage());
        return $default;
    }
}

/** Doplní ctx o prefix, id, state_key, role, shared (volá lab57_session). */
function lab58_ctx_prepare(array $ctx): array
{
    $parsed = lab58_context_parse((string)($ctx['context'] ?? '')) ?? ['prefix' => '', 'id' => ''];
    $ctx = $parsed + $ctx;
    $ctx['now'] = (int)($ctx['now'] ?? time());
    $student = (string)($ctx['student'] ?? '');
    $key = (string)lab58_ctx_call($ctx, 'state_key', [$ctx], $student);
    $ctx['state_key'] = $key !== '' ? $key : $student;
    $ctx['shared'] = $ctx['state_key'] !== $student;
    $role = lab58_ctx_call($ctx, 'role', [$ctx], null);
    $ctx['role'] = is_string($role) && preg_match('/^[a-z0-9_-]{1,24}$/', $role) === 1 ? $role : null;
    $ctx['player'] = substr(sha1($student), 0, 12);
    return $ctx;
}

/** Přístup mimo practice (practice řeší lab57_access_error). */
function lab58_context_access(array $level, array $ctx): ?string
{
    $parsed = lab58_context_parse((string)($ctx['context'] ?? ''));
    if ($parsed === null || lab58_context_spec($parsed['prefix']) === null) return tr('Neznámý režim laboratoře.');
    $ctx = $parsed + $ctx;
    $ctx['now'] = (int)($ctx['now'] ?? time());
    $allowed = lab58_ctx_call($ctx, 'levels', [$ctx], null);
    if (is_array($allowed) && !in_array((string)$level['id'], array_map('strval', $allowed), true)) return tr('Tahle úloha do tohoto režimu nepatří.');
    $error = lab58_ctx_call($ctx, 'access', [$level, $ctx], tr('Režim laboratoře teď není dostupný.'));
    return is_string($error) ? $error : null;
}

function lab58_context_info(array $ctx): ?array
{
    $info = lab58_ctx_call($ctx, 'info', [$ctx], null);
    return is_array($info) ? $info : null;
}

function lab58_context_seed(array $ctx, array $level): string
{
    $default = lab57_seed((string)$ctx['class'], (string)$ctx['state_key'], (string)$ctx['context'], $level);
    $seed = lab58_ctx_call($ctx, 'seed', [$ctx, $level], $default);
    return is_string($seed) && $seed !== '' ? $seed : $default;
}

function lab58_context_points(array $level, int $hints, array $ctx): int
{
    $points = lab58_ctx_call($ctx, 'points', [$level, $hints, $ctx], null);
    $points = is_int($points) ? $points : lab57_level_points($level, $hints, $ctx['info'] ?? null);
    return max(0, (int)lab58_filter('level_points', $points, ['level' => $level, 'hints' => $hints, 'ctx' => $ctx]));
}

function lab58_context_events_path(string $context): string
{
    $parsed = lab58_context_parse($context);
    if ($parsed === null) return lab57_storage_dir() . '/lab_v57_events.json.php';
    $path = lab58_ctx_call($parsed + ['context' => $context], 'events_path', [$context], null);
    if (is_string($path) && $path !== '') return $path;
    return lab57_storage_dir() . '/linux_v58/ctx_' . $parsed['prefix'] . '_' . preg_replace('/[^a-z0-9_-]/', '', $parsed['id']) . '.events.json.php';
}

function lab58_register_core_contexts(): void
{
    lab58_register_context('practice', [
        'label' => tr('Procvičování'),
        'events_path' => static fn(string $context): string => lab57_storage_dir() . '/lab_v57_events.json.php',
    ]);
    lab58_register_context('race', [
        'label' => tr('Závod'),
        'first_blood' => true,
        'access' => static fn(array $level, array $ctx): ?string => function_exists('arena57_race_access') ? arena57_race_access((string)$ctx['id'], (string)$ctx['class'], (string)$level['id'], (int)$ctx['now']) : tr('Závody nejsou dostupné.'),
        'info' => static fn(array $ctx): ?array => function_exists('arena57_race') ? arena57_race((string)$ctx['id']) : null,
        'points' => static fn(array $level, int $hints, array $ctx): int => lab57_level_points($level, $hints, $ctx['info'] ?? null),
        'events_path' => static fn(string $context): string => lab57_storage_dir() . '/linux_v57/race_' . preg_replace('/[^a-z0-9]/', '', substr($context, 5)) . '.events.json.php',
        'board' => static fn(array $ctx): array => function_exists('arena57_board') ? arena57_board((string)$ctx['id'], (string)$ctx['student'], (string)$ctx['class']) : [],
    ]);
}

// ---------------------------------------------------------------------------
// Sdílený svět týmu: stav hráče (cwd, historie, …) zvlášť pro každého
// ---------------------------------------------------------------------------

function lab58_player_state(Lab57World $w): array
{
    $out = [];
    foreach (LAB58_PLAYER_FIELDS as $field) $out[$field] = $w->{$field};
    return $out;
}

function lab58_player_apply(Lab57World $w, array $row, string $player, array $fresh): void
{
    $saved = (array)($row['players'][$player] ?? []);
    foreach (LAB58_PLAYER_FIELDS as $field) {
        $value = array_key_exists($field, $saved) ? $saved[$field] : $fresh[$field];
        $w->{$field} = is_int($w->{$field}) ? (int)$value : (is_array($w->{$field}) ? (array)$value : (string)$value);
    }
}

function lab58_player_export(Lab57World $w, array $row, string $player): array
{
    $state = lab58_player_state($w);
    $state['history'] = array_slice((array)$state['history'], -200);
    $state['seen'] = $w->now;
    $row['players'][$player] = $state;
    if (count($row['players']) > 8) {
        uasort($row['players'], static fn(array $a, array $b): int => (int)($b['seen'] ?? 0) <=> (int)($a['seen'] ?? 0));
        $row['players'] = array_slice($row['players'], 0, 8, true);
    }
    return $row;
}

// ---------------------------------------------------------------------------
// 5) Události a 8) filtry
// ---------------------------------------------------------------------------

/** $fn(array $payload): void. Událost '*' dostane všechny. Výjimka posluchače neshodí běh. */
function lab58_on(string $event, callable $fn): bool
{
    if (preg_match('/^([a-z_]{2,24}|\*)$/', $event) !== 1) return lab58_reg_error("událost '$event': neplatné jméno");
    $r = &lab58_registry();
    $r['listeners'][$event][] = $fn;
    return true;
}

function lab58_emit(string $event, array $payload): void
{
    $payload['event'] = $event;
    $listeners = lab58_registry()['listeners'];
    foreach (array_merge($listeners[$event] ?? [], $listeners['*'] ?? []) as $fn) {
        try {
            $fn($payload);
        } catch (Throwable $e) {
            error_log('EDUCANET v58 lab listener ' . $event . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
        }
    }
}

/** $fn(mixed $value, array $args): mixed – vrací upravenou hodnotu. */
function lab58_add_filter(string $name, callable $fn): bool
{
    if (preg_match('/^[a-z_]{2,32}$/', $name) !== 1) return lab58_reg_error("filtr '$name': neplatné jméno");
    $r = &lab58_registry();
    $r['filters'][$name][] = $fn;
    return true;
}

function lab58_filter(string $name, mixed $value, array $args = []): mixed
{
    foreach (lab58_registry()['filters'][$name] ?? [] as $fn) {
        try {
            $value = $fn($value, $args);
        } catch (Throwable $e) {
            error_log('EDUCANET v58 lab filter ' . $name . ': ' . $e->getMessage());
        }
    }
    return $value;
}

// ---------------------------------------------------------------------------
// 7) Svět: rozšiřovače, simulovaný čas, účty
// ---------------------------------------------------------------------------

/** $fn(Lab57World $w, array $level, Lab57Rng $rng): void – po základním světě, před build úrovně. Nesmí záviset na roli. */
function lab58_register_world_extender(callable $fn): bool
{
    $r = &lab58_registry();
    $r['extenders'][] = $fn;
    lab58_bump();
    return true;
}

function lab58_extend_world(Lab57World $w, array $level): void
{
    foreach (lab58_registry()['extenders'] as $i => $fn) {
        try {
            $fn($w, $level, new Lab57Rng($w->seed . '|ext|' . $i));
        } catch (Throwable $e) {
            error_log('EDUCANET v58 lab world extender #' . $i . ': ' . $e->getMessage());
        }
    }
}

/** Simulovaný čas světa = čas požadavku + ext['clock_offset']. */
function lab58_now(Lab57World $w): int
{
    return $w->clockBase + (int)($w->ext['clock_offset'] ?? 0);
}

/** Posune simulované hodiny (např. příkaz timewarp). Posun se ukládá ve stavu úrovně. */
function lab58_clock_advance(Lab57World $w, int $seconds): int
{
    $offset = max(-LAB58_CLOCK_MAX, min(LAB58_CLOCK_MAX, (int)($w->ext['clock_offset'] ?? 0) + $seconds));
    $w->ext['clock_offset'] = $offset;
    $w->now = lab58_now($w);
    return $w->now;
}

function lab58_accounts_hash(Lab57World $w): string
{
    return sha1(json_encode([$w->users, $w->groups]) ?: '');
}

function lab58_role(Lab57World $w): ?string
{
    $role = $w->session['role'] ?? null;
    return is_string($role) ? $role : null;
}

// ---------------------------------------------------------------------------
// Session žáka: DAT-04 (session_write_close v API) – krátké znovuotevření pro XP
// ---------------------------------------------------------------------------

function lab58_with_session(callable $fn): mixed
{
    $reopened = false;
    if (!empty($GLOBALS['lab58_session_closed']) && session_status() === PHP_SESSION_NONE && !headers_sent()) $reopened = @session_start();
    try {
        return $fn();
    } finally {
        if ($reopened) session_write_close();
    }
}

// ---------------------------------------------------------------------------
// Kontrola řešitelnosti bez úložiště (editor učitele, audity)
// ---------------------------------------------------------------------------

/** Spustí referenční řešení v paměti (jako lab57_session, bez zápisu). @return array{solved:bool,cmds:list<string>,transcript:list<array>} */
function lab58_try_solution(array $level, string $seed, int $now, string $code = 'EDU-TEST-0000'): array
{
    $codes = ['CODE' => $code];
    $w = lab57_build_world($level, $seed, $codes, $now);
    $cmds = is_callable($level['solution'] ?? null) ? array_values(array_map('strval', ($level['solution'])($w))) : [];
    $row = ['started' => $now, 'hints' => 0, 'cmds' => 0];
    $solved = false;
    $transcript = [];
    foreach ($cmds as $line) {
        $active = &lab57_active();
        $active = ['level' => $level, 'row' => $row, 'result' => [], 'codes' => $codes, 'seed' => $seed, 'now' => $now, 'race' => null, 'foreign' => null];
        $run = lab57_run_line($w, $line);
        $row = $active['row'];
        $result = $active['result'];
        $active = [];
        $checks = $level['type'] === 'check' ? lab57_eval_checks($level, $w) : [];
        if (!empty($result['solve']) || ($checks !== [] && array_filter($checks, static fn(array $c): bool => !$c['ok']) === [])) $solved = true;
        $transcript[] = ['line' => $line, 'exit' => $run['exit'], 'out' => mb_substr(implode('', array_column($run['chunks'], 1)), 0, 500)];
        if ($solved) break;
    }
    return ['solved' => $solved, 'cmds' => $cmds, 'transcript' => $transcript];
}

// ---------------------------------------------------------------------------
// Načtení rozšíření
// ---------------------------------------------------------------------------

/** Načte linux_v58_cmd_*.php, pak linux_v58_levels_*.php (abecedně). Chyba souboru neshodí aplikaci. */
function lab58_load_extensions(): void
{
    $r = &lab58_registry();
    if (!empty($GLOBALS['lab58_skip_ext'])) return;
    foreach (['cmd', 'levels'] as $kind) {
        $files = glob(__DIR__ . '/linux_v58_' . $kind . '_*.php') ?: [];
        sort($files, SORT_STRING);
        foreach ($files as $file) {
            $base = basename($file);
            if (preg_match(LAB58_EXT_FILE_RE, $base) !== 1 || !is_file($file) || isset($r['loaded'][$base])) continue;
            $r['loaded'][$base] = true;
            try {
                require_once $file;
            } catch (Throwable $e) {
                lab58_reg_error('rozšíření ' . $base . ': ' . $e->getMessage());
            }
        }
    }
}

/** @return list<string> načtené soubory rozšíření */
function lab58_loaded_extensions(): array
{
    return array_keys(lab58_registry()['loaded']);
}

lab58_register_core_contexts();
lab58_register_core_decl();
lab58_log_register_listeners();
lab58_load_extensions();
