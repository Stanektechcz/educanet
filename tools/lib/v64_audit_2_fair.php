<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v64 audit · dávka 2: ELO, ligy, anti-farm, absolutní žebříček, zlepšení. Proměnné: $check, $tmp, $root. */
require_once __DIR__ . '/v64_sim_fixture.php';
foreach (['linux_v57_lab.php', 'arena_v57.php', 'arena_v60_challenge.php', 'robots_v58.php', 'arena_v58_ctf.php', 'identity_v58.php'] as $lib) require_once $root . '/' . $lib;

$cid = 'class_3a';
$T0 = 1_790_000_000;

// --- Simulace 1000 zápasů 30 hráčů ---------------------------------------------------------------
$sim = v64sim_run(30, 1000, 6412);
$rho = v64sim_spearman($sim['elo'], $sim['strength']);
$GLOBALS['v64_sim_numbers'] = sprintf('spearman=%.3f delta_last100=%.2f', $rho, $sim['delta_last100']);
$check('ELO simulace 1000 zápasů/30 hráčů: Spearman(ELO, skrytá síla) ≥ 0,8 (' . sprintf('%.3f', $rho) . ')', $rho >= 0.8);
$check('ELO simulace: průměrná změna ELO v posledních 100 zápasech < 8 (' . sprintf('%.2f', $sim['delta_last100']) . ')', $sim['delta_last100'] < 8.0);
$check('ELO simulace: součet ELO se zachovává (dokud nikdo nenarazí na dno 600)', $sim['sum_ok']);
$sim2 = v64sim_run(30, 1000, 6412);
$check('ELO simulace: deterministická při stejném seedu', $sim2['elo'] === $sim['elo']);

// --- Čisté funkce ---------------------------------------------------------------------------------
$check('ELO: očekávání 0,5 pro stejné hráče a součet očekávání = 1', abs(fair64_elo_expected(1000, 1000) - 0.5) < 1e-9 && abs(fair64_elo_expected(1200, 1000) + fair64_elo_expected(1000, 1200) - 1.0) < 1e-9);
[$w, $l] = fair64_elo_update(1000, 1000, 0, 0, 1.0);
$check('ELO: nováček K=32 → vítěz +16, poražený −16; po 10 hrách K=16 → ±8', $w === 1016 && $l === 984 && fair64_elo_update(1000, 1000, 12, 12, 1.0) === [1008, 992]);
$check('ELO: minimum 600 (poražený na dně neklesne)', fair64_elo_update(1200, 600, 20, 20, 1.0)[1] === 600);
$check('ELO: ligy bronz <950, stříbro 950–1100, zlato >1100 (hranice)', fair64_league(949) === 'bronze' && fair64_league(950) === 'silver' && fair64_league(1100) === 'silver' && fair64_league(1101) === 'gold');
$check('ELO: semestr jako robots58_semester (září–leden 1., únor–srpen 2.)', fair64_semester(strtotime('2026-10-05')) === robots58_semester_probe(strtotime('2026-10-05')) && fair64_semester(strtotime('2027-03-01')) === '2026-2');

function robots58_semester_probe(int $ts): string
{
    require_once dirname(__DIR__, 2) . '/robots_v58.php';
    return robots58_semester($ts);
}

// --- Ukládání výsledků, idempotence, anti-farm ----------------------------------------------------
$r1 = fair64_apply_result($cid, 'ch1', 'class_3a:a', 'class_3a:b', $T0);
$again = fair64_apply_result($cid, 'ch1', 'class_3a:a', 'class_3a:b', $T0 + 5);
$ra = fair64_rating($cid, 'class_3a:a', $T0);
$rb = fair64_rating($cid, 'class_3a:b', $T0);
$check('ELO: výsledek výzvy se zapíše jednou (idempotence přes challenge_id), součet 2000', $r1['applied'] && !$again['applied'] && $ra['games'] === 1 && $ra['elo'] + $rb['elo'] === 2000);
$r2 = fair64_apply_result($cid, 'ch2', 'class_3a:a', 'class_3a:b', $T0 + 60);
$check('anti-farm: opakovaná dvojice má váhu ×0,5', $r2['weight'] === 0.5);
$fourth = [];
foreach (['ch3', 'ch4', 'ch5'] as $i => $id) $fourth[] = fair64_apply_result($cid, $id, 'class_3a:a', 'class_3a:b', $T0 + 120 + $i)['weight'];
$before = fair64_rating($cid, 'class_3a:a', $T0)['elo'];
$check('anti-farm: 4. a další zápas téhož dne proti stejnému soupeři = váha 0 a ELO se nemění', $fourth[2] === 0.0 && fair64_rating($cid, 'class_3a:a', $T0)['elo'] === $before && fair64_apply_result($cid, 'ch6', 'class_3a:a', 'class_3a:b', $T0 + 200)['weight'] === 0.0);
$next = fair64_apply_result($cid, 'ch7', 'class_3a:a', 'class_3a:b', $T0 + 86400 + 10);
$check('anti-farm: další den se počítadlo za den vynuluje (opět ×0,5)', $next['weight'] === 0.5);
$sem2 = $T0 + 200 * 86400;
$check('ELO: nový semestr = reset na 1000 / 0 her (líně, bez zápisu)', fair64_semester($T0) !== fair64_semester($sem2) && fair64_rating($cid, 'class_3a:a', $sem2) === ['elo' => 1000, 'games' => 0, 'league' => 'silver']);
$check('ELO: sám se sebou nebo prázdný vítěz se nezapíše', !fair64_apply_result($cid, 'chx', 'class_3a:a', 'class_3a:a', $T0)['applied'] && !fair64_apply_result($cid, 'chy', '', 'class_3a:b', $T0)['applied']);

// --- Doporučení soupeře ---------------------------------------------------------------------------
$near = fair64_recommend($cid, 'class_3a:a', ['class_3a:a', 'class_3a:b', 'class_3a:neznamy'], $T0);
$check('doporučení: soupeři do ±150 ELO, sám sebe nikdy; neznámý (1000) je mezi nimi, nic se nezakazuje', !in_array('class_3a:a', $near, true) && in_array('class_3a:neznamy', $near, true));

// --- Hook v arena60_sweep_results ------------------------------------------------------------------
$src = (string)file_get_contents($root . '/arena_v60_challenge.php');
$check('hook: arena60_sweep_results předává dokončené výzvy do fair64_record_challenge (mimo zámek výzev)', str_contains($src, 'fair64_record_challenge($classId, $row, $now)') && str_contains($src, '$finished[] ='), false);
fair64_record_challenge($cid, ['id' => 'chh', 'winner_key' => 'class_3a:c', 'from_key' => 'class_3a:c', 'to_key' => 'class_3a:d'], $T0);
fair64_record_challenge($cid, ['id' => 'chh', 'winner_key' => 'class_3a:c', 'from_key' => 'class_3a:c', 'to_key' => 'class_3a:d'], $T0);
$check('hook: fair64_record_challenge je idempotentní (1 hra, ne 2)', fair64_rating($cid, 'class_3a:c', $T0)['games'] === 1 && fair64_rating($cid, 'class_3a:d', $T0)['elo'] < 1000);

// --- Absolutní žebříček per akce -------------------------------------------------------------------
$check('absolutní žebříček: výchozí vypnutý (ctf/robots/tg)', !fair64_absolute_board_enabled('ctf', $cid, 'e1') && !fair64_absolute_board_enabled('robots', $cid) && !fair64_absolute_board_enabled('tg', $cid, 's1'));
fair64_absolute_board_set($cid, 'ctf', 'e1', true);
$check('absolutní žebříček: učitel zapne u konkrétní akce, jiná akce zůstane vypnutá', fair64_absolute_board_enabled('ctf', $cid, 'e1') && !fair64_absolute_board_enabled('ctf', $cid, 'e2') && !fair64_absolute_board_set($cid, 'neznamy', 'x', true) && !fair64_absolute_board_set($cid, 'ctf', '../x', true));
fair64_absolute_board_set($cid, 'ctf', 'e1', false);
$check('absolutní žebříček: lze zase vypnout', !fair64_absolute_board_enabled('ctf', $cid, 'e1'));
$check('žák bez zapnutého žebříčku vidí jen svůj řádek', fair64_only_me([['name' => 'A. B.', 'me' => false], ['name' => 'Ty', 'me' => true]]) === [['name' => 'Ty', 'me' => true]]);
$presetIds = array_values(array_filter(array_keys(arena57_presets()), static fn($p): bool => $p !== 'custom'));
$parsed = arena57_parse_create(['class_id' => $cid, 'preset' => $presetIds[0]], [$cid]);
$check('závod: výchozí hodnocení je osobní rekord (žebříček jen na přání učitele)', ($parsed['settings']['rating'] ?? '') === 'osobni_rekord');
$robotsRows = robots58_public_league($cid, robots58_semester($T0), null, false);
$check('robotí liga: žák bez zapnutí vidí jen vlastní řádek (bez viewer = prázdno)', $robotsRows['rows'] === []);

// --- Žebříček zlepšení -----------------------------------------------------------------------------
$check('zlepšení: skóre jen kladné (záporné a nulové = 0)', fair64_improvement_score(-30, 0.0) === 0 && fair64_improvement_score(20, 0.5) === 70 && fair64_improvement_score(0, -1.0) === 0);
$check('zlepšení: ELO před 14 dny z historie (poslední záznam ≤ hranice, jinak start)', fair64_elo_at([[100, 1010], [200, 1050]], 150) === 1010 && fair64_elo_at([[100, 1010]], 50) === 1000);
$check('zlepšení: součet zvládnutí ignoruje neověřené', fair64_mastery_total(['a' => ['score' => 0.5], 'b' => ['score' => null]]) === 0.5);
