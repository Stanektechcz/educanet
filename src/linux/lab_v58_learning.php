<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – EDU-02 (heatmapa chyb), EDU-04 (mapa dovedností), LAB-08 (odznaky).
 * Čte jen přes veřejné funkce `linux_v57_lab.php` / `lab_v58_log.php` (LABCORE) a vlastní
 * `linux_v58_levels_review.php` (streak opakování) – nic nezapisuje, jen skládá pohledy pro
 * žáka (skill map, badges) a učitele (heatmapa, viz lab_v58_teacher.php).
 */

const LAB58_HEATMAP_MIN_STUDENTS = 3; // soukromí: jen agregace, buňka s < 3 žáky se nezobrazí
const LAB58_SKILL_MASTER_N = 5;       // příkaz je „zvládnutý“ po N správných použitích
const LAB58_BADGE_NO_HINTS_TARGET = 5;
const LAB58_BADGE_STREAK_TARGET = 7;

// ---------------------------------------------------------------------------
// EDU-02: heatmapa chyb třídy
// ---------------------------------------------------------------------------

/** Slovní doporučení k výkladu pro danou třídu chyby a příkaz. */
function lab58_heatmap_advice(string $errorClass, string $cmd): string
{
    return match ($errorClass) {
        'not_found' => 'Třída plete příkazy nebo dělá překlepy u „' . $cmd . '“ – zopakujte, kde se příkaz píše a co dělá.',
        'permission' => 'Časté „Permission denied“ u „' . $cmd . '“ – projděte práva souborů (ls -l) a kdy je potřeba sudo.',
        'no_such_file' => 'Časté chybné cesty u „' . $cmd . '“ – zopakujte pwd/ls a rozdíl absolutní/relativní cesty.',
        'bad_option' => 'Špatné přepínače u „' . $cmd . '“ – ukažte man ' . $cmd . ' a pár typických voleb.',
        'syntax' => 'Syntaktické chyby u „' . $cmd . '“ – projděte správný zápis příkazu na tabuli.',
        'usage' => 'Neúplné zadání u „' . $cmd . '“ – ukažte povinné argumenty (' . $cmd . ' --help).',
        'wrong_answer' => 'Časté špatné odpovědi/odevzdání u „' . $cmd . '“ – zopakujte zadání a co se vlastně kontroluje.',
        default => 'Časté chyby u „' . $cmd . '“ – stojí za krátké zopakování v hodině.',
    };
}

/**
 * Heatmapa „příkaz × třída chyby“ za dané období; jen buňky s >= 3 různými žáky (soukromí).
 * @return array{class:string,since:int,now:int,students_active:int,total_events:int,cells:list<array>,top:list<array>}
 */
function lab58_heatmap(string $classId, int $sinceTs, ?int $now = null): array
{
    $now ??= time();
    $empty = ['class' => $classId, 'since' => $sinceTs, 'now' => $now, 'students_active' => 0, 'total_events' => 0, 'cells' => [], 'top' => []];
    if (!function_exists('lab58_class_log_summary')) return $empty;
    $summary = lab58_class_log_summary($classId, $sinceTs, $now);

    $cells = [];
    foreach ((array)($summary['commands'] ?? []) as $cmd => $cell) {
        foreach ((array)($cell['errors'] ?? []) as $errClass => $err) {
            $students = (int)($err['students'] ?? 0);
            if ($students < LAB58_HEATMAP_MIN_STUDENTS) continue;
            $cells[] = ['command' => (string)$cmd, 'error_class' => (string)$errClass, 'count' => (int)($err['count'] ?? 0), 'students' => $students];
        }
    }
    usort($cells, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);
    $top = array_map(static fn(array $c): array => $c + ['advice' => lab58_heatmap_advice($c['error_class'], $c['command'])], array_slice($cells, 0, 5));

    return ['class' => $classId, 'since' => $sinceTs, 'now' => $now, 'students_active' => (int)($summary['students'] ?? 0), 'total_events' => (int)($summary['total'] ?? 0), 'cells' => $cells, 'top' => $top];
}

// ---------------------------------------------------------------------------
// EDU-04: mapa dovedností (kontrakt §3.4)
// ---------------------------------------------------------------------------

/** Jména příkazů, která se v ukázkách konceptu opravdu objevují a jsou v manuálu. */
function lab58_skill_concept_commands(array $concept, array $manualCommands): array
{
    $out = [];
    foreach ((array)($concept['examples'] ?? []) as $ex) {
        $line = is_array($ex) ? (string)($ex[0] ?? '') : '';
        foreach (preg_split('/[\s|;]+/', $line) ?: [] as $tok) {
            $tok = trim($tok, "\"'");
            if (preg_match('/^[a-z][a-z0-9._-]{0,31}$/', $tok) === 1 && isset($manualCommands[$tok])) $out[$tok] = true;
        }
    }
    return array_keys($out);
}

/** Spočítá uses/ok podle jména příkazu ze všech logů žáka (retence 30 dní, jako log samotný). */
function lab58_skill_usage(string $classId, string $studentKey): array
{
    $usage = [];
    if (!function_exists('lab58_log_read')) return $usage;
    foreach (lab58_log_read($classId, $studentKey) as $r) {
        if (($r['kind'] ?? '') !== 'cmd') continue;
        $cmd = function_exists('lab58_log_command_name') ? lab58_log_command_name((string)$r['line']) : '?';
        if ($cmd === '?' || $cmd === '') continue;
        $usage[$cmd]['uses'] = ($usage[$cmd]['uses'] ?? 0) + 1;
        if (($r['error_class'] ?? 'ok') === 'ok') $usage[$cmd]['ok'] = ($usage[$cmd]['ok'] ?? 0) + 1;
    }
    return $usage;
}

/** @return array{commands:array,concepts:array,summary:array{mastered:int,learning:int,total:int}} */
function lab58_skill_map(string $classId, string $studentKey): array
{
    $manual = function_exists('lab58_manual') ? lab58_manual() : ['commands' => [], 'concepts' => []];
    $usage = lab58_skill_usage($classId, $studentKey);

    $commands = [];
    $mastered = 0;
    $learning = 0;
    foreach ((array)($manual['commands'] ?? []) as $name => $entry) {
        if (!is_array($entry) || empty($entry['in_lab'])) continue;
        $uses = (int)($usage[$name]['uses'] ?? 0);
        $ok = (int)($usage[$name]['ok'] ?? 0);
        $state = $ok >= LAB58_SKILL_MASTER_N ? 'mastered' : ($uses > 0 ? 'learning' : 'new');
        if ($state === 'mastered') $mastered++; elseif ($state === 'learning') $learning++;
        $commands[(string)$name] = ['uses' => $uses, 'ok' => $ok, 'state' => $state];
    }

    $concepts = [];
    foreach ((array)($manual['concepts'] ?? []) as $id => $concept) {
        if (!is_array($concept)) continue;
        $related = lab58_skill_concept_commands($concept, $commands);
        $progress = 0.0;
        foreach ($related as $c) $progress += $commands[$c]['state'] === 'mastered' ? 1.0 : ($commands[$c]['state'] === 'learning' ? 0.4 : 0.0);
        $progress = $related === [] ? 0.0 : $progress / count($related);
        $state = $progress >= 0.99 ? 'mastered' : ($progress > 0 ? 'learning' : 'new');
        $concepts[(string)$id] = ['title' => (string)($concept['title'] ?? $id), 'state' => $state, 'progress' => round($progress, 2)];
    }

    return ['commands' => $commands, 'concepts' => $concepts, 'summary' => ['mastered' => $mastered, 'learning' => $learning, 'total' => count($commands)]];
}

// ---------------------------------------------------------------------------
// LAB-08: odznaky (kontrakt §3.4) – čistě odvozené ze stavu, žádný zápis
// ---------------------------------------------------------------------------

/** První existující balíček z kandidátů (LAB-02..07 dodávají paralelní agenti – přesné id se může lišit). */
function lab58_badge_first_pack(array $candidates): ?string
{
    if (!function_exists('lab58_pack')) return null;
    foreach ($candidates as $id) if (lab58_pack($id) !== null) return $id;
    return null;
}

function lab58_badge_from_pack(string $id, string $title, string $icon, array $candidates, array $solved, string $hint): array
{
    $packId = lab58_badge_first_pack($candidates);
    $levels = $packId !== null && function_exists('lab57_pack_levels') ? lab57_pack_levels($packId) : [];
    $total = count($levels);
    $done = 0;
    foreach ($levels as $level) if (isset($solved[(string)$level['id']])) $done++;
    $progress = $total > 0 ? $done / $total : 0.0;
    return ['id' => $id, 'title' => $title, 'icon' => $icon, 'earned' => $total > 0 && $done >= $total, 'progress' => round($progress, 2), 'hint' => $hint];
}

function lab58_badge_no_hints(array $solved): array
{
    $count = 0;
    foreach ($solved as $info) if ((int)($info['hints'] ?? 0) === 0) $count++;
    $progress = min(1.0, $count / LAB58_BADGE_NO_HINTS_TARGET);
    return ['id' => 'no_hints', 'title' => tr('Bez nápovědy'), 'icon' => '🎯', 'earned' => $count >= LAB58_BADGE_NO_HINTS_TARGET, 'progress' => round($progress, 2), 'hint' => tr('Vyřeš {n} úloh bez jediné nápovědy.', ['n' => LAB58_BADGE_NO_HINTS_TARGET])];
}

function lab58_badge_diligent(string $classId, string $studentKey): array
{
    $streak = function_exists('lab58_review_streak_max') ? lab58_review_streak_max($classId, $studentKey) : 0;
    $progress = min(1.0, $streak / LAB58_BADGE_STREAK_TARGET);
    return ['id' => 'diligent', 'title' => tr('Pilný'), 'icon' => '🔥', 'earned' => $streak >= LAB58_BADGE_STREAK_TARGET, 'progress' => round($progress, 2), 'hint' => tr('Dokonči denní opakování {n} dní v řadě.', ['n' => LAB58_BADGE_STREAK_TARGET])];
}

/**
 * Odznaky za Linux dovednosti (kontrakt §3.4). Nikdy nic neuděluje ani neukládá – jen čte aktuální
 * stav (vyřešené úrovně, série opakování) a vrací earned/progress. Napojení na tabuli v55 viz INTEGRATION.md.
 * @return list<array{id:string,title:string,icon:string,earned:bool,progress:float,hint:string}>
 */
function lab58_badges(string $classId, string $studentKey): array
{
    $solved = function_exists('lab57_solved') ? lab57_solved($classId, $studentKey, 'practice') : [];
    return [
        lab58_badge_from_pack('explorer', tr('Průzkumník'), '🔍', ['quest'], $solved, tr('Vyřeš všechny úrovně balíčku Průzkumník.')),
        lab58_badge_from_pack('net_detective', tr('Síťový detektiv'), '📡', ['sit'], $solved, tr('Vyřeš všechny úrovně balíčku Síťový detektiv.')),
        lab58_badge_from_pack('golfer', tr('Golfista'), '⛳', ['golf'], $solved, tr('Vyřeš všechny úrovně balíčku Shell golf.')),
        lab58_badge_from_pack('fixer', tr('Opravář'), '🛠', ['opravna'], $solved, tr('Vyřeš všechny úrovně balíčku Opravna serverů.')),
        lab58_badge_from_pack('keymaster', tr('Klíčník'), '🔑', ['klice', 'klic', 'keys', 'ssh'], $solved, tr('Vyřeš všechny úrovně balíčku Klíče a přístupy.')),
        lab58_badge_from_pack('archivist', tr('Archivář'), '🗜', ['archivy', 'archiv', 'archives'], $solved, tr('Vyřeš všechny úrovně balíčku Archivy.')),
        lab58_badge_from_pack('timer', tr('Časovač'), '⏰', ['cron', 'planovac'], $solved, tr('Vyřeš všechny úrovně balíčku Plánovač cron.')),
        lab58_badge_from_pack('perms_admin', tr('Správce práv'), '🔐', ['prava', 'users', 'uzivatele'], $solved, tr('Vyřeš všechny úrovně balíčku Uživatelé a práva.')),
        lab58_badge_from_pack('historian', tr('Historik'), '📜', ['git'], $solved, tr('Vyřeš všechny úrovně balíčku Git v terminálu.')),
        lab58_badge_from_pack('term_designer', tr('Grafik terminálu'), '🖼', ['grafika'], $solved, tr('Vyřeš všechny úrovně balíčku Terminál pro grafiky.')),
        lab58_badge_diligent($classId, $studentKey),
        lab58_badge_no_hints($solved),
    ];
}
