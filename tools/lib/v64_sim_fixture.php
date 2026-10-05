<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v64 · fixtura pro audit: simulace zápasů 1v1 se skrytou silou hráčů (mt_srand → deterministická).
 * Vybírá soupeře náhodně a výsledek losuje z logistického modelu skryté síly. Používá jen čisté funkce fair64_*.
 */

/** Spearmanova pořadová korelace (bez remíz ve skryté síle). */
function v64sim_spearman(array $a, array $b): float
{
    $rank = static function (array $v): array {
        $idx = array_keys($v);
        usort($idx, static fn($x, $y): int => $v[$x] <=> $v[$y]);
        $r = [];
        foreach ($idx as $pos => $k) $r[$k] = $pos + 1;
        return $r;
    };
    $ra = $rank($a);
    $rb = $rank($b);
    $n = count($a);
    $d2 = 0;
    foreach ($ra as $k => $x) $d2 += ($x - $rb[$k]) ** 2;
    return 1 - 6 * $d2 / ($n * ($n * $n - 1));
}

/**
 * @return array{elo:array<int,int>,strength:array<int,int>,delta_last100:float,sum_ok:bool,matches:int}
 */
function v64sim_run(int $players, int $matches, int $seed): array
{
    mt_srand($seed);
    $strength = [];
    $elo = [];
    $games = [];
    for ($i = 0; $i < $players; $i++) {
        $strength[$i] = 700 + mt_rand(0, 600);
        $elo[$i] = FAIR64_START;
        $games[$i] = 0;
    }
    $sum0 = array_sum($elo);
    $sumOk = true;
    $deltas = [];
    for ($m = 0; $m < $matches; $m++) {
        $a = mt_rand(0, $players - 1);
        $b = mt_rand(0, $players - 2);
        if ($b >= $a) $b++;
        $pA = fair64_elo_expected((float)$strength[$a], (float)$strength[$b]);
        $aWins = (mt_rand() / mt_getrandmax()) < $pA;
        [$na, $nb] = fair64_elo_update($elo[$a], $elo[$b], $games[$a], $games[$b], $aWins ? 1.0 : 0.0);
        if ($m >= $matches - 100) { $deltas[] = abs($na - $elo[$a]); $deltas[] = abs($nb - $elo[$b]); }
        $elo[$a] = $na; $elo[$b] = $nb;
        $games[$a]++; $games[$b]++;
        if (min($elo) > FAIR64_MIN && array_sum($elo) !== $sum0) $sumOk = false;
    }
    return ['elo' => $elo, 'strength' => $strength, 'delta_last100' => array_sum($deltas) / max(1, count($deltas)), 'sum_ok' => $sumOk, 'matches' => $matches];
}
