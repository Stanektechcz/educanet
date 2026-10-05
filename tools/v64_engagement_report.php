<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v64 · tools/v64_engagement_report.php – hlavní metrika fáze 3: podíl aktivních žáků ze spodního kvartilu.
 *
 *   EDUCANET_STORAGE_DIR=<kopie storage> php tools/v64_engagement_report.php [--days=28] [--json]
 *
 * JEN ČTENÍ. Odmítne běžet nad ostrou storage/ (musí být nastaveno EDUCANET_STORAGE_DIR na kopii; kontrola proběhne
 * dřív, než se načte aplikace). Výstup bez jmen.
 * Definice: spodní kvartil třídy = nejnižších 25 % žáků podle celkových XP (profil učení); „aktivní“ = má aspoň jednu
 * XP událost v posledních N dnech (výchozí 28). Navíc se uvádí podíl těch, kdo mají událost z hry/arény.
 * Konec: V64_ENGAGEMENT_OK (nebo V64_ENGAGEMENT_REFUSED, nenulový exit kód).
 */

$v64eMain = basename((string)($argv[0] ?? '')) === basename(__FILE__);
if ($v64eMain) {
    $v64eEnv = getenv('EDUCANET_STORAGE_DIR');
    $v64eLive = str_replace('\\', '/', (string)realpath(dirname(__DIR__) . '/storage'));
    if (!is_string($v64eEnv) || $v64eEnv === '' || str_replace('\\', '/', (string)realpath($v64eEnv)) === $v64eLive) {
        fwrite(STDERR, "V64_ENGAGEMENT_REFUSED nastav EDUCANET_STORAGE_DIR na kopii dat (nikdy ostrá storage/).\n");
        exit(2);
    }
}

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/intake_v51.php';
require_once dirname(__DIR__) . '/motivation_v61.php';

const V64E_GAME_PREFIXES = ['v57:race:', 'v58:ctf:', 'v58:weekly:', 'v58:incident:', 'robots58:', 'tg58:'];

function v64e_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return null;
}

/** Spodní kvartil (nejnižších ceil(25 %) hodnot XP; vždy aspoň 1 žák). Čistá funkce. @param array<string,int> $xpByKey @return list<string> */
function v64e_bottom_quartile(array $xpByKey): array
{
    if ($xpByKey === []) return [];
    $keys = array_keys($xpByKey);
    usort($keys, static fn(string $a, string $b): int => ($xpByKey[$a] <=> $xpByKey[$b]) ?: strcmp(sha1($a), sha1($b)));
    return array_slice($keys, 0, max(1, (int)ceil(count($keys) / 4)));
}

/** Je událost v okně posledních $days dní? */
function v64e_recent(array $event, int $now, int $days): bool
{
    $t = strtotime((string)($event['at'] ?? ''));
    return $t !== false && $t >= $now - $days * 86400 && $t <= $now + 86400;
}

function v64e_is_game_event(string $eventKey): bool
{
    foreach (V64E_GAME_PREFIXES as $p) if (str_starts_with($eventKey, $p)) return true;
    return false;
}

/** @return array{students:int,quartile:int,active:int,active_game:int,share:float,share_game:float} */
function v64e_class_stats(array $profiles, array $learningKeys, int $now, int $days): array
{
    $xp = [];
    foreach ($learningKeys as $key) $xp[$key] = (int)($profiles[$key]['xp'] ?? 0);
    $q = v64e_bottom_quartile($xp);
    $active = 0;
    $activeGame = 0;
    foreach ($q as $key) {
        $recent = array_filter((array)($profiles[$key]['events'] ?? []), static fn($e): bool => is_array($e) && v64e_recent($e, $now, $days));
        if ($recent !== []) $active++;
        if (array_filter(array_keys($recent), static fn($k): bool => v64e_is_game_event((string)$k)) !== []) $activeGame++;
    }
    $n = count($q);
    return ['students' => count($xp), 'quartile' => $n, 'active' => $active, 'active_game' => $activeGame, 'share' => $n > 0 ? round($active / $n, 3) : 0.0, 'share_game' => $n > 0 ? round($activeGame / $n, 3) : 0.0];
}

if ($v64eMain) {
    $days = max(1, min(365, (int)(v64e_arg($argv, 'days') ?? 28)));
    $now = time();
    $profiles = storage_read(learning_profiles_path(), false);
    $out = [];
    foreach (array_keys($modules) as $classId) {
        $keys = [];
        foreach (array_keys(project_students_for_class((string)$classId)) as $sk) {
            $lk = mot61_learning_key((string)$classId, (string)$sk);
            if ($lk !== '') $keys[] = $lk;
        }
        if ($keys !== []) $out[(string)$classId] = v64e_class_stats($profiles, $keys, $now, $days);
    }
    $quartile = array_sum(array_column($out, 'quartile'));
    $active = array_sum(array_column($out, 'active'));
    $total = ['quartile' => $quartile, 'active' => $active, 'share' => $quartile > 0 ? round($active / $quartile, 3) : 0.0, 'days' => $days];
    if (in_array('--json', $argv, true)) {
        echo json_encode(['classes' => $out, 'total' => $total], JSON_PRETTY_PRINT) . "\n";
    } else {
        foreach ($out as $classId => $s) echo $classId . ' žáků=' . $s['students'] . ' kvartil=' . $s['quartile'] . ' aktivních=' . $s['active'] . ' podíl=' . $s['share'] . ' z_her=' . $s['share_game'] . "\n";
        echo 'CELKEM kvartil=' . $quartile . ' aktivních=' . $active . ' podíl=' . $total['share'] . ' okno=' . $days . " dní\n";
    }
    echo "V64_ENGAGEMENT_OK\n";
    exit(0);
}
