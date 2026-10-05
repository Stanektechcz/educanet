<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v64 audit · dávka 1: ekonomika (strop 60/den, idempotence, deník, rollback). Proměnné: $check, $tmp, $root. */

$classId = 'class_3a';
$NOW = 1_790_000_000;
$ledgerRows = static fn(): array => storage_stream_month_rows(ECO64_LEDGER_STREAM, date('Y-m', $NOW));
$_SESSION['local_user'] = ['id' => 'audit-u1', 'email' => 'audit1@educanet.cz'];
$_SESSION['student_label'] = 'Audit Zak Jedna';
$skey = 'class_3a:audit-zak-jedna';
$profileXp = static fn(): int => (int)(storage_read(learning_profiles_path())[learning_profile_key($classId)]['xp'] ?? 0);

$check('eco64: výchozí zapnuto, strop 60 XP/den', eco64_enabled() && eco64_caps()['xp_day'] === 60);
$check('eco64: čistá funkce stropu (0/50/60 → zbytek)', eco64_apply_cap(0, 50, 60) === 50 && eco64_apply_cap(50, 30, 60) === 10 && eco64_apply_cap(60, 5, 60) === 0);

$g1 = eco64_grant($classId, $skey, 'xp', 'arena57', 'audit:e1', 50, $NOW);
$g2 = eco64_grant($classId, $skey, 'xp', 'robots58', 'audit:e2', 30, $NOW);
$check('eco64: první grant 50, druhý omezen stropem na 10 (společný strop zdrojů)', $g1 === 50 && $g2 === 10 && $profileXp() === 60);
$again = eco64_grant($classId, $skey, 'xp', 'arena57', 'audit:e1', 50, $NOW);
$check('eco64: stejný eventKey podruhé = 0 XP, profil beze změny', $again === 0 && $profileXp() === 60);

$total = 0;
for ($i = 0; $i < 10000; $i++) {
    $total += eco64_grant($classId, $skey, 'xp', 'tg58', 'audit:spam' . $i, 7, $NOW);
}
$check('eco64: 10000 pokusů téhož dne ≤ 60 XP/den (profil stále 60)', $total === 0 && $profileXp() === 60);
$next = eco64_grant($classId, $skey, 'xp', 'tg58', 'audit:nextday', 25, $NOW + 86400);
$check('eco64: další den strop zase volný', $next === 25 && $profileXp() === 85);

$rows = $ledgerRows();
$raw = (string)file_get_contents(storage_stream_file(ECO64_LEDGER_STREAM, date('Y-m', $NOW)));
$check('eco64: deník nese jen povolená pole, sha1 místo klíče, žádná jména ani klíče žáka',
    $rows !== [] && array_diff(array_keys($rows[0]), ['at', 'class_id', 'sid_hash', 'currency', 'source', 'event_key', 'requested', 'granted', 'capped']) === []
    && !str_contains($raw, 'Audit Zak') && !str_contains($raw, 'audit-zak') && !str_contains($raw, '@') && $rows[0]['sid_hash'] === sha1($classId . '|' . $skey));
$check('eco64: omezený grant má v deníku capped=true a granted<requested', (bool)array_filter($rows, static fn(array $r): bool => $r['capped'] === true && $r['granted'] < $r['requested']));
$dailyRaw = (string)file_get_contents(eco64_daily_path());
$check('eco64: čítač denních XP je chráněný guardem a bez jmen', str_starts_with($dailyRaw, "<?php http_response_code(403); exit; ?>") && !str_contains($dailyRaw, 'Audit'));

// Souběh: dva „procesy“ nad stejným čítačem – strop musí držet (simulace po sobě, každý čte čerstvá data).
$_SESSION['student_label'] = 'Audit Zak Dva';
$_SESSION['local_user'] = ['id' => 'audit-u2', 'email' => 'audit2@educanet.cz'];
$k2 = 'class_3a:audit-zak-dva';
$sum = eco64_grant($classId, $k2, 'xp', 'a', 'audit:c1', 40, $NOW) + eco64_grant($classId, $k2, 'xp', 'b', 'audit:c2', 40, $NOW);
$check('eco64: dva zdroje po 40 XP téhož dne dají dohromady 60', $sum === 60);

// Anonymní (lokální) profil se stropem.
unset($_SESSION['local_user']);
$_SESSION['student_label'] = 'Audit Zak Tri';
$k3 = 'class_3a:audit-zak-tri';
$a = eco64_grant($classId, $k3, 'xp', 'a', 'audit:n1', 45, $NOW) + eco64_grant($classId, $k3, 'xp', 'b', 'audit:n2', 45, $NOW);
$check('eco64: nepřihlášený profil (session) dodržuje stejný strop 60', $a === 60);

// Odvozené API pro zdroje her.
$_SESSION['local_user'] = ['id' => 'audit-u4', 'email' => 'audit4@educanet.cz'];
$_SESSION['student_label'] = 'Audit Zak Ctyri';
$k4 = 'class_3a:audit-zak-ctyri';
$check('eco64_award: true při nové události, false při opakování', eco64_award($classId, $k4, 'robots58', 'audit:aw1', 20) === true && eco64_award($classId, $k4, 'robots58', 'audit:aw1', 20) === false);

// Rollback: EDUCANET_ECONOMY_V64=0 = chování v63 (přímý learning_award_once, bez stropu a deníku).
putenv('EDUCANET_ECONOMY_V64=0');
$before = count($ledgerRows());
$off = eco64_grant($classId, $k4, 'xp', 'x', 'audit:off1', 500, $NOW);
putenv('EDUCANET_ECONOMY_V64');
$check('eco64: EDUCANET_ECONOMY_V64=0 → bez stropu (500 XP projde jako ve v63) a bez zápisu do deníku', $off === 500 && count($ledgerRows()) === $before && !eco64_enabled() === false);

// Inflační report.
$rep = eco64_inflation_report($classId, date('Y-m', $NOW));
$check('eco64: report inflace (medián, p90, podíl omezených, změna m/m)', $rep['current']['students'] >= 2 && $rep['current']['capped_share'] > 0 && $rep['current']['p90'] >= $rep['current']['median'] && array_key_exists('median_change', $rep) && $rep['cap'] === 60);

// Napojení zdrojů: žádný herní zdroj nevolá learning_award_once napřímo.
$direct = [];
foreach (['arena_v57.php', 'arena_v58_ctf.php', 'arena_v58_weekly.php', 'arena_v58_incident.php', 'robots_v58.php', 'teamgames_v58_core.php'] as $f) {
    if (preg_match('/\blearning_award_once\(/', (string)file_get_contents($root . '/' . $f)) === 1) $direct[] = $f;
}
$check('eco64: všech 6 herních zdrojů XP jde přes eco64_award (žádné přímé learning_award_once)', $direct === [], false);
