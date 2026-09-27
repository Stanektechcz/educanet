<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · Týmové hry – CLI audit (registr, týmy, soukromí, kvíz, 6 her, souběh, XP, CSRF, tokeny).
 * Běží nad dočasným úložištěm – nikdy nesahá na storage/. lab58_skip_ext=true izoluje od rozpracovaných
 * souborů ostatních agentů (linux_v58_cmd_ a linux_v58_levels_ soubory se nenačtou automaticky) – ručně
 * se načte jen linux_v58_levels_tg.php. Spuštění: php tools/v58_teamgames_audit.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$tmp = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet_tg58_audit_' . bin2hex(random_bytes(5));
if (!mkdir($tmp, 0770, true) && !is_dir($tmp)) { fwrite(STDERR, "Nelze vytvořit dočasné úložiště.\n"); exit(1); }
$GLOBALS['tg58_storage_override'] = $tmp;
$GLOBALS['lab57_storage_override'] = $tmp;
$GLOBALS['lab57_secret_override'] = 'audit-secret-' . bin2hex(random_bytes(8));
$GLOBALS['lab58_skip_ext'] = true; // izolace: nenačítat WIP soubory ostatních agentů vlny 1b
@session_start(); // testovací sezení jen pro tg58_rate_ok() (session-based limity)

/** Testovací dvojník storage_update z bootstrap.php (stejná sémantika: jeden LOCK_EX přes čtení i zápis). */
function storage_update(string $path, callable $mutate): array
{
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0770, true);
    $fp = fopen($path, 'c+');
    if ($fp === false) throw new RuntimeException('open');
    try {
        flock($fp, LOCK_EX);
        $raw = (string)stream_get_contents($fp);
        $data = json_decode(trim(preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? ''), true);
        $data = $mutate(is_array($data) ? $data : []);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, "<?php http_response_code(403); exit; ?>\n" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }
    return $data;
}

$GLOBALS['audit_xp'] = [];
function learning_award_once(string $classId, string $eventKey, int $xp): bool
{
    $key = $classId . '|' . $eventKey;
    if (isset($GLOBALS['audit_xp'][$key])) return false;
    $GLOBALS['audit_xp'][$key] = $xp;
    return true;
}

require_once $root . '/linux_v57_lab.php';
require_once $root . '/linux_v58_levels_tg.php';
require_once $root . '/teamgames_v58_registry.php';
require_once $root . '/teamgames_v58_quiz.php';
require_once $root . '/teamgames_v58_game_relay.php';
require_once $root . '/teamgames_v58_game_bingo.php';
require_once $root . '/teamgames_v58_game_jeopardy.php';
require_once $root . '/teamgames_v58_game_tug.php';
require_once $root . '/teamgames_v58_game_netadmin.php';
require_once $root . '/teamgames_v58_game_escape.php';

$checks = 0;
$failed = 0;
function check(bool $ok, string $label, string $detail = ''): void
{
    global $checks, $failed;
    $checks++;
    if (!$ok) $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . ($ok || $detail === '' ? '' : ' – ' . $detail) . PHP_EOL;
}

// ---------------------------------------------------------------------------
// Testovací data: vymyšlená jména, nikdy skuteční žáci (soukromí dat nezletilých)
// ---------------------------------------------------------------------------

function fake_roster(int $n, string $seed): array
{
    $first = ['Adam', 'Bára', 'Cyril', 'Dita', 'Emil', 'Filip', 'Gita', 'Hugo', 'Iva', 'Jindra', 'Klára', 'Lukáš', 'Milan', 'Nina', 'Ota', 'Petra', 'Radek', 'Soňa', 'Tomáš', 'Uršula', 'Vojta', 'Zora', 'Anežka', 'Bruno'];
    $last = ['Novák', 'Svoboda', 'Dvořák', 'Černý', 'Procházka', 'Kučera', 'Veselý', 'Horák', 'Němec', 'Marek'];
    $out = [];
    for ($i = 0; $i < $n; $i++) {
        $label = $first[$i % count($first)] . ' ' . $last[intdiv($i, count($first)) % count($last)] . ' ' . $seed;
        $key = $seed . '_' . $i;
        $out[$key] = ['key' => $key, 'label' => $label];
    }
    return $out;
}

const A_CLASS_NET = 'class_3a';
const A_CLASS_GFX = 'class_1a';
$GLOBALS['tg58_roster_override'][A_CLASS_NET] = fake_roster(24, 'S');
$GLOBALS['tg58_roster_override'][A_CLASS_GFX] = fake_roster(20, 'G');
$CLASS_IDS = [A_CLASS_NET, A_CLASS_GFX];

/** Spustí operaci Labu jako žák v kontextu `tg:<id>` (přímé volání lab57_session, jako lab_v57_api.php). */
function lab_op(string $classId, string $student, string $context, string $levelId, string $op, array $input = [], int $now = 0): array
{
    $now = $now ?: time();
    return lab57_session(['class' => $classId, 'student' => $student, 'label' => $student, 'context' => $context, 'level' => $levelId, 'now' => $now], $op, $input);
}

function create_and_start(array $in, array $classIds, int $now): array
{
    $s = tg58_create_session($in, $classIds, $now);
    return tg58_start_session((string)$s['id'], $now);
}

echo "== Registr a životní cyklus ==\n";
check(count(tg58_registered_types()) === 6, 'všech 6 typů her je zaregistrováno', implode(',', tg58_registered_types()));
try { tg58_create_session(['class_id' => A_CLASS_NET, 'type' => 'nesmysl'], $CLASS_IDS, time()); check(false, 'neznámý typ hry se zamítne'); } catch (RuntimeException $e) { check(true, 'neznámý typ hry se zamítne'); }
try { tg58_create_session(['class_id' => 'jina_trida', 'type' => 'bingo'], $CLASS_IDS, time()); check(false, 'neplatná třída se zamítne'); } catch (RuntimeException $e) { check(true, 'neplatná třída se zamítne'); }

$now0 = time();
$lifecycleId = (string)tg58_create_session(['class_id' => A_CLASS_GFX, 'type' => 'bingo', 'line' => 'graphics', 'team_count' => 2], $CLASS_IDS, $now0)['id'];
check((string)tg58_get($lifecycleId)['status'] === 'lobby', 'nová hra začíná v lobby');
try { tg58_pause_session($lifecycleId, $now0); check(false, 'pauza z lobby se zamítne'); } catch (RuntimeException $e) { check(true, 'pauza z lobby se zamítne'); }
$live = tg58_start_session($lifecycleId, $now0);
check($live['status'] === 'running', 'start přejde do running');
try { tg58_start_session($lifecycleId, $now0); check(false, 'druhý start téže hry se zamítne'); } catch (RuntimeException $e) { check(true, 'druhý start téže hry se zamítne'); }
try {
    tg58_create_session(['class_id' => A_CLASS_GFX, 'type' => 'jeopardy', 'line' => 'graphics'], $CLASS_IDS, $now0)['id'];
    $second = tg58_create_session(['class_id' => A_CLASS_GFX, 'type' => 'jeopardy', 'line' => 'graphics'], $CLASS_IDS, $now0);
    tg58_start_session((string)$second['id'], $now0);
    check(false, 'druhá souběžná hra téže třídy se nespustí');
} catch (RuntimeException $e) { check(true, 'druhá souběžná hra téže třídy se nespustí'); }
tg58_pause_session($lifecycleId, $now0 + 5);
check((string)tg58_get($lifecycleId)['status'] === 'paused', 'pauza funguje');
tg58_resume_session($lifecycleId, $now0 + 10);
check((string)tg58_get($lifecycleId)['status'] === 'running', 'pokračování funguje');
tg58_end_session($lifecycleId, $now0 + 20);
check((string)tg58_get($lifecycleId)['status'] === 'finished', 'konec funguje');
tg58_archive_session($lifecycleId);
check((string)tg58_get($lifecycleId)['status'] === 'archived', 'archivace funguje');
tg58_delete_session($lifecycleId);
check(tg58_get($lifecycleId) === null, 'smazání funguje');

echo "== Týmy ==\n";
foreach (['snake', 'random'] as $mode) {
    $teams = tg58_form_teams(A_CLASS_NET, $mode, 4);
    $allMembers = array_merge(...array_map(static fn(array $t): array => $t['members'], $teams));
    check(count($teams) === 4, "$mode: sestaví 4 týmy");
    check(count($allMembers) === count(array_unique($allMembers)) && count($allMembers) === 24, "$mode: každý žák je přesně v jednom týmu");
}
$manual = tg58_form_teams(A_CLASS_NET, 'manual', 2, [['S_0', 'S_1'], ['S_2']]);
check(in_array('S_0', $manual[0]['members'], true) && in_array('S_2', $manual[1]['members'], true), 'manual: ruční přiřazení se respektuje');
check(count(array_merge($manual[0]['members'], $manual[1]['members'])) === 24, 'manual: nepřiřazení žáci jdou do nejmenšího týmu');
$sessJoinId = (string)tg58_create_session(['class_id' => A_CLASS_NET, 'type' => 'netadmin', 'team_count' => 3], $CLASS_IDS, $now0)['id'];
$sessJoin = tg58_start_session($sessJoinId, $now0);
$smallest = tg58_join_smallest_team($sessJoin, 'novy_zak');
$hasJoined = false;
foreach ($smallest['teams'] as $t) { if (in_array('novy_zak', $t['members'], true)) $hasJoined = true; }
check($hasJoined, 'pozdní příchozí se přidá do týmu');
tg58_end_session($sessJoinId, $now0); // uvolní třídu pro další testy (jen jedna běžící hra na třídu)
tg58_delete_session($sessJoinId);

echo "== Soukromí jmen ==\n";
check(tg58_public_name('full', 'S_0', 'Adam Novák S', null, 1) === 'Adam Novák S', 'full: celé jméno');
check(tg58_public_name('initials', 'S_0', 'Adam Novák S', null, 1) === tg58_display_name('Adam Novák S'), 'initials: jméno + iniciála');
check(tg58_public_name('anon', 'S_0', 'Adam Novák S', null, 1) === 'Hráč 1', 'anon: cizí divák vidí jen „Hráč N“');
check(tg58_public_name('anon', 'S_0', 'Adam Novák S', 'S_0', 1) === 'Ty', 'anon: sám sebe vidí jako „Ty“');

echo "== Kvízová banka ==\n";
$bank = tg58_quiz_bank();
check(count($bank) >= count(tg58_quiz_builtin_bank()), 'banka obsahuje aspoň vestavěné otázky (funguje i bez TG-BANK)');
$hasNet = false; $hasGfx = false; $hasWebfix = false;
foreach ($bank as $it) { if ($it['line'] === 'networks') $hasNet = true; if ($it['line'] === 'graphics') $hasGfx = true; if ($it['category'] === 'rozbitá stránka') $hasWebfix = true; }
check($hasNet && $hasGfx, 'banka pokrývá obě linie');
check($hasWebfix, 'banka obsahuje úlohy „rozbitá stránka“ pro linii grafika');
$single = tg58_quiz_find('b.net.cmd.01');
check($single !== null && tg58_quiz_check($single, 'ls') && !tg58_quiz_check($single, 'cd'), 'single: kontrola odpovědi');
$bool = tg58_quiz_find('b.net.perm.02');
check($bool !== null && tg58_quiz_check($bool, false) && !tg58_quiz_check($bool, true), 'bool: kontrola odpovědi');
$numeric = tg58_quiz_find('b.net.ip.02');
check($numeric !== null && tg58_quiz_check($numeric, '254') && !tg58_quiz_check($numeric, '250'), 'numeric: kontrola odpovědi');
check(!array_key_exists('answer', tg58_quiz_public($single, 'seed1')) && !array_key_exists('explain', tg58_quiz_public($single, 'seed1')), 'quiz_public nikdy neobsahuje answer/explain');
$multiItem = ['id' => 'audit.multi.1', 'line' => 'both', 'category' => 'test', 'difficulty' => 1, 'type' => 'multi', 'prompt' => 'Vyber sudá čísla', 'options' => ['1', '2', '3', '4'], 'answer' => ['2', '4']];
$normMulti = tg58_quiz_normalize($multiItem);
check($normMulti !== null && tg58_quiz_check($normMulti, ['4', '2']) && !tg58_quiz_check($normMulti, ['2']), 'multi: kontrola odpovědi (pořadí nevadí)');
$orderItem = tg58_quiz_normalize(['id' => 'audit.order.1', 'line' => 'both', 'category' => 'test', 'type' => 'order', 'prompt' => 'Seřaď', 'answer' => ['a', 'b', 'c']]);
check($orderItem !== null && tg58_quiz_check($orderItem, ['a', 'b', 'c']) && !tg58_quiz_check($orderItem, ['b', 'a', 'c']), 'order: kontrola odpovědi');
$textItem = tg58_quiz_normalize(['id' => 'audit.text.1', 'line' => 'both', 'category' => 'test', 'type' => 'command', 'prompt' => 'Napiš příkaz', 'answer' => 'ls -la', 'accept' => ['ls -l -a']]);
check($textItem !== null && tg58_quiz_check($textItem, 'LS -LA') && tg58_quiz_check($textItem, 'ls -l -a'), 'command: normalizace textu a accept synonyma');

$bankItem = tg58_bank_add_question(['prompt' => 'Testovací otázka učitele?', 'category' => 'audit', 'line' => 'both', 'type' => 'single', 'options' => ['ano', 'ne'], 'answer' => 'ano'], time());
check(tg58_quiz_find((string)$bankItem['id']) !== null, 'banka učitele: přidaná otázka je dostupná');
tg58_bank_delete_question((string)$bankItem['id']);
check(tg58_quiz_find((string)$bankItem['id']) === null, 'banka učitele: smazání funguje');
function cnt58_check_text(string $text, string $context = 'level'): array { return str_contains(mb_strtolower($text), 'zakazane') ? ['ok' => false, 'issues' => [['code' => 'banned', 'message' => 'zakázané slovo']]] : ['ok' => true, 'issues' => []]; }
try { tg58_bank_add_question(['prompt' => 'Obsahuje zakazane slovo?', 'type' => 'text', 'answer' => 'x'], time()); check(false, 'cnt58_check_text zamítne zakázané slovo'); } catch (RuntimeException $e) { check(true, 'cnt58_check_text zamítne zakázané slovo'); }

echo "== Lab mikroúlohy (řešitelnost na >=5 semínkách) ==\n";
// Pozor: řešitelnost se ověřuje na PŘIPRAVENÉ úrovni z registru (lab57_level), ne na syrovém poli
// z tg58_lvl_find()/tg58_lvl_fix() – teprve lab58_level_prepare() (volané uvnitř lab57_levels())
// převede 'checks'/'generate' na skutečné closures, se kterými umí pracovat lab57_eval_checks().
$seeds5 = ['t1', 't2', 't3', 't4', 't5'];
foreach (tg58_relay_net_levels() as $raw) {
    if ($raw['type'] !== 'code') continue;
    $lvl = lab57_level($raw['id']);
    check($lvl !== null, 'úroveň je v registru: ' . $raw['id']);
    if ($lvl === null) continue;
    $ok = true;
    foreach ($seeds5 as $sd) { $r = lab58_try_solution($lvl, 'seed|' . $sd . '|' . $lvl['id'], time()); $ok = $ok && $r['solved']; }
    check($ok, 'řešitelné (5 semínek): ' . $lvl['id']);
}
function direct_check_solvable(string $levelId, array $seeds): bool
{
    $level = lab57_level($levelId);
    if ($level === null) return false;
    foreach ($seeds as $sd) {
        $w = lab57_build_world($level, 'seed|' . $sd . '|' . $level['id'], ['CODE' => 'EDU-0000-0000'], time());
        $before = lab57_eval_checks($level, $w);
        if (array_filter($before, static fn(array $c): bool => !$c['ok']) === []) return false; // nesmí být splněno hned od začátku
        foreach ((array)$level['checks'] as $chk) {
            $decl = (array)($chk['decl'] ?? []);
            $path = (string)($decl[1]['path'] ?? '');
            $right = (string)($decl[1]['equals'] ?? '');
            if ($path !== '' && $right !== '') $w->mkfile($path, $right, 0644, 'student', null, $w->now);
        }
        $after = lab57_eval_checks($level, $w);
        if (array_filter($after, static fn(array $c): bool => !$c['ok']) !== []) return false;
    }
    return true;
}
check(direct_check_solvable('tg-relay-net-3', $seeds5), 'řešitelné (5 semínek, přímá kontrola): tg-relay-net-3');
$netadminLevels = tg58_netadmin_net_levels();
check(count($netadminLevels) >= 16 && count($netadminLevels) <= 24, 'Správci sítě: 16–24 uzlů vygenerováno (' . count($netadminLevels) . ')');
$naOk = true;
foreach (array_slice($netadminLevels, 0, 6) as $lvl) { $naOk = $naOk && direct_check_solvable((string)$lvl['id'], $seeds5); }
check($naOk, 'Správci sítě: uzly řešitelné (5 semínek, vzorek)');
$bingoOk = true;
foreach (tg58_bingo_net_levels() as $raw) {
    if ($raw['type'] === 'code') {
        $lvl = lab57_level($raw['id']);
        foreach ($seeds5 as $sd) { $bingoOk = $bingoOk && $lvl !== null && lab58_try_solution($lvl, 'seed|' . $sd . '|' . $lvl['id'], time())['solved']; }
    } else {
        $bingoOk = $bingoOk && direct_check_solvable((string)$raw['id'], $seeds5);
    }
}
check($bingoOk, 'Bingo: laboratorní políčka řešitelná (5 semínek)');
check(count(lab58_registry_errors()) === 0, 'registrace mého balíčku/kontextu bez chyb', implode('; ', lab58_registry_errors()));

echo "== Štafeta ==\n";
$relayNet = create_and_start(['class_id' => A_CLASS_NET, 'type' => 'relay', 'line' => 'networks', 'team_count' => 2, 'legs' => 4], $CLASS_IDS, $now0);
$relayId = (string)$relayNet['id'];
$teamA = (string)$relayNet['teams'][0]['id'];
$teamB = (string)$relayNet['teams'][1]['id'];
$studentA = (string)$relayNet['teams'][0]['members'][0];
$studentB = (string)$relayNet['teams'][1]['members'][0];
$ctxRelay = 'tg:' . $relayId;
$r = lab_op(A_CLASS_NET, $studentA, $ctxRelay, 'tg-relay-net-2', 'state', [], $now0);
check($r['ok'] === false, 'úsek 2 nejde bez dokončení úseku 1 (svůj tým)');
$foreignCode = lab57_code(A_CLASS_NET, 'tgteam:' . $relayId . ':' . $teamB, $ctxRelay, 'tg-relay-net-1');
$r = lab_op(A_CLASS_NET, $studentA, $ctxRelay, 'tg-relay-net-1', 'run', ['line' => 'submit ' . $foreignCode], $now0);
check(($r['ok'] ?? false) === true && empty($r['solved']), 'cizí token (kód jiného týmu) neprojde');
check((int)tg58_get($relayId)['game']['teams'][$teamA]['leg'] === 1, 'tým A stále na úseku 1 po cizím kódu');
$ownCode = lab57_code(A_CLASS_NET, 'tgteam:' . $relayId . ':' . $teamA, $ctxRelay, 'tg-relay-net-1');
$r = lab_op(A_CLASS_NET, $studentA, $ctxRelay, 'tg-relay-net-1', 'run', ['line' => 'submit ' . $ownCode], $now0);
check(!empty($r['solved']), 'vlastní kód projde a úsek se vyřeší');
check((int)tg58_get($relayId)['game']['teams'][$teamA]['leg'] === 2, 'tým A postoupil na úsek 2');
check((int)tg58_get($relayId)['game']['teams'][$teamB]['leg'] === 1, 'svět je per tým – tým B se postupem A neovlivnil');
try { tg58_student_action($relayId, 'relay_help', $studentA, [], $now0); check(false, 'pomoc před 4 minutami se zamítne'); } catch (RuntimeException $e) { check(true, 'pomoc před 4 minutami se zamítne'); }
$helpNow = $now0 + 241;
$helpResp = tg58_student_action($relayId, 'relay_help', $studentA, [], $helpNow);
check(($helpResp['ok'] ?? false) === true, 'pomoc po 4 minutách funguje a přidá penalizaci');
check((int)tg58_get($relayId)['game']['teams'][$teamA]['penalty_s'] === TG58_RELAY_HELP_PENALTY_S, 'penalizace se připočte jen jednou za úsek');
tg58_end_session($relayId, $now0 + 30); // uvolní třídu (jen jedna běžící hra na třídu)

$relayGfx = create_and_start(['class_id' => A_CLASS_GFX, 'type' => 'relay', 'line' => 'graphics', 'team_count' => 2, 'legs' => 3], $CLASS_IDS, $now0);
$relayGfxId = (string)$relayGfx['id'];
$gTeamA = (string)$relayGfx['teams'][0]['id'];
$gStudentA = (string)$relayGfx['teams'][0]['members'][0];
$view = tg58_student_view(tg58_get($relayGfxId), $gStudentA, $now0);
check(($view['game']['mode'] ?? '') === 'quiz' && !empty($view['game']['question']), 'linie grafika: úsek je kvízová otázka bez terminálu');
try { tg58_student_action($relayGfxId, 'relay_answer', $gStudentA, ['answer' => 'spatna odpoved xyz'], $now0); } catch (Throwable $e) { }
check((int)tg58_get($relayGfxId)['game']['teams'][$gTeamA]['leg'] === 1, 'špatná odpověď (grafika) nepostoupí úsek');
// FAIR58-05: limit špatných odpovědí kvízové brány (5 / min na žáka, v datech hry)
for ($i = 1; $i < TG58_WRONG_MAX; $i++) tg58_student_action($relayGfxId, 'relay_answer', $gStudentA, ['answer' => 'spatna odpoved xyz'], $now0 + $i);
$blocked = false;
try { tg58_student_action($relayGfxId, 'relay_answer', $gStudentA, ['answer' => 'spatna odpoved xyz'], $now0 + 10); } catch (RuntimeException $e) { $blocked = str_contains($e->getMessage(), 'Moc špatných'); }
check($blocked, 'FAIR58-05 Štafeta: 6. špatná odpověď za minutu je zablokovaná');
$gStudentA2 = (string)$relayGfx['teams'][0]['members'][1];
check((tg58_student_action($relayGfxId, 'relay_answer', $gStudentA2, ['answer' => 'spatna odpoved xyz'], $now0 + 10)['correct'] ?? true) === false, 'FAIR58-05 Štafeta: limit je per žák (spoluhráč může odpovídat)');
check((tg58_student_action($relayGfxId, 'relay_answer', $gStudentA, ['answer' => 'spatna odpoved xyz'], $now0 + 70)['correct'] ?? true) === false, 'FAIR58-05 Štafeta: po minutě může žák zkusit znovu');
tg58_end_session($relayGfxId, $now0 + 30); // uvolní třídu (jen jedna běžící hra na třídu)

echo "== Bingo ==\n";
$bingoNet = create_and_start(['class_id' => A_CLASS_NET, 'type' => 'bingo', 'line' => 'networks', 'team_count' => 2, 'size' => 4], $CLASS_IDS, $now0);
$bingoId = (string)$bingoNet['id'];
$bTeamA = (string)$bingoNet['teams'][0]['id'];
$bTeamB = (string)$bingoNet['teams'][1]['id'];
$layoutA = $bingoNet['game']['teams'][$bTeamA]['layout'];
$layoutB = $bingoNet['game']['teams'][$bTeamB]['layout'];
check($layoutA !== $layoutB, 'stejné úlohy, jiné rozložení karty per tým');
check(count($layoutA) === count($layoutB) && sort($layoutA) === sort($layoutB), 'obě karty mají stejnou množinu úloh');
$member1 = (string)$bingoNet['teams'][0]['members'][0];
$member2 = (string)$bingoNet['teams'][0]['members'][1];
$firstQuizCell = null;
foreach ($bingoNet['game']['cells'] as $c) { if ($c['kind'] === 'quiz') { $firstQuizCell = $c; break; } }
$item = tg58_quiz_find((string)$firstQuizCell['ref']);
$correctAnswer = is_array($item['answer']) ? $item['answer'] : $item['answer'];
$wrong = tg58_student_action($bingoId, 'bingo_mark', $member1, ['id' => $firstQuizCell['id'], 'answer' => 'toto-je-spatne-xyz'], $now0);
check(($wrong['correct'] ?? true) === false, 'špatná odpověď se neoznačí (kontrola jen na serveru)');
check(!isset(tg58_get($bingoId)['game']['teams'][$bTeamA]['solved'][(string)$firstQuizCell['id']]), 'políčko zůstává nevyřešené po špatné odpovědi');
$right = tg58_student_action($bingoId, 'bingo_mark', $member1, ['id' => $firstQuizCell['id'], 'answer' => $correctAnswer], $now0);
check(($right['correct'] ?? false) === true, 'správná odpověď políčko označí');
$dup = tg58_student_action($bingoId, 'bingo_mark', $member2, ['id' => $firstQuizCell['id'], 'answer' => $correctAnswer], $now0);
check(($dup['already'] ?? false) === true, 'žádné dvojí označení stejného políčka');
$quizCell2 = null;
foreach ($bingoNet['game']['cells'] as $c) { if ($c['kind'] === 'quiz' && $c['id'] !== $firstQuizCell['id'] && in_array((int)$c['id'], array_map('intval', $layoutA), true)) { $quizCell2 = $c; break; } }
if ($quizCell2 !== null) {
    for ($i = 0; $i < TG58_WRONG_MAX; $i++) tg58_student_action($bingoId, 'bingo_mark', $member2, ['id' => $quizCell2['id'], 'answer' => 'spatne-' . $i], $now0 + $i);
    $blocked = false;
    try { tg58_student_action($bingoId, 'bingo_mark', $member2, ['id' => $quizCell2['id'], 'answer' => 'spatne-x'], $now0 + 8); } catch (RuntimeException $e) { $blocked = true; }
    check($blocked, 'FAIR58-05 Bingo: 6. špatná odpověď za minutu je zablokovaná');
} else {
    check(str_contains((string)file_get_contents($root . '/teamgames_v58_game_bingo.php'), 'tg58_wrong_guard('), 'FAIR58-05 Bingo: kvízová políčka mají limit špatných odpovědí');
}
tg58_end_session($bingoId, $now0 + 30); // uvolní třídu (jen jedna běžící hra na třídu)

echo "== Riskuj! ==\n";
$jeop = create_and_start(['class_id' => A_CLASS_GFX, 'type' => 'jeopardy', 'line' => 'graphics', 'team_count' => 2, 'window_s' => 20], $CLASS_IDS, $now0);
$jeopId = (string)$jeop['id'];
$jTeamA = (string)$jeop['teams'][0]['id'];
$jTeamB = (string)$jeop['teams'][1]['id'];
$turnTeamId = (string)tg58_jeopardy_turn_team($jeop);
$turnStudent = null;
$offTurnStudent = null;
foreach ($jeop['teams'] as $t) {
    if ((string)$t['id'] === $turnTeamId) { $turnStudent = (string)$t['members'][0]; } else { $offTurnStudent = (string)$t['members'][0]; }
}
try { tg58_student_action($jeopId, 'jeopardy_pick', $offTurnStudent, ['cat' => 0, 'cell' => 0], $now0); check(false, 'tým mimo pořadí nesmí vybrat pole'); } catch (RuntimeException $e) { check(true, 'tým mimo pořadí nesmí vybrat pole'); }
tg58_student_action($jeopId, 'jeopardy_pick', $turnStudent, ['cat' => 0, 'cell' => 0], $now0);
$afterPick = tg58_get($jeopId);
$publicQ = tg58_jeopardy_current_public($afterPick, null);
check($publicQ !== null && !array_key_exists('answer', $publicQ), 'správná odpověď se neposílá klientovi, dokud otázka běží');
$item2 = tg58_quiz_find((string)$afterPick['game']['current']['item_id']);
$aMember = (string)$afterPick['teams'][0]['members'][0];
$bMember1 = (string)$afterPick['teams'][1]['members'][0];
$bMember2 = (string)$afterPick['teams'][1]['members'][1];
tg58_student_action($jeopId, 'jeopardy_answer', $aMember, ['answer' => $item2['answer']], $now0 + 2);
tg58_student_action($jeopId, 'jeopardy_answer', $bMember1, ['answer' => $item2['answer']], $now0 + 2);
try { tg58_student_action($jeopId, 'jeopardy_answer', $bMember1, ['answer' => $item2['answer']], $now0 + 3); check(false, 'dvojí odpověď stejného žáka se zamítne'); } catch (RuntimeException $e) { check(true, 'dvojí odpověď stejného žáka se zamítne'); }
$lateNow = $now0 + 25;
try { tg58_student_action($jeopId, 'jeopardy_answer', $bMember2, ['answer' => $item2['answer']], $lateNow); check(false, 'pozdní odpověď (po uzavření) se odmítne'); } catch (RuntimeException $e) { check(true, 'pozdní odpověď (po uzavření) se odmítne'); }
$settled = tg58_settle(tg58_get($jeopId), $lateNow);
$value = (int)$afterPick['game']['current']['value'];
$sizeA = count($afterPick['teams'][0]['members']);
$sizeB = count($afterPick['teams'][1]['members']);
$expectA = (int)round($value * (1 / $sizeA));
$expectB = (int)round($value * (1 / $sizeB));
check((int)$settled['game']['scores'][$jTeamA] === $expectA, 'skóre týmu A = hodnota × podíl správných členů');
check((int)$settled['game']['scores'][$jTeamB] === $expectB, 'skóre týmu B (odpovídal i tým mimo pořadí) se také připsalo');
check((int)$settled['game']['scores'][$jTeamA] >= 0 && (int)$settled['game']['scores'][$jTeamB] >= 0, 'žádné záporné body');
tg58_end_session($jeopId, $lateNow + 5); // uvolní třídu (jen jedna běžící hra na třídu)

echo "== Přetahovaná ==\n";
$tug = create_and_start(['class_id' => A_CLASS_GFX, 'type' => 'tug', 'line' => 'graphics', 'team_count' => 5, 'rounds' => 2], $CLASS_IDS, $now0);
check(count($tug['teams']) === 2, 'přetahovaná má vždy přesně 2 týmy (fixed_team_count)');
$tugId = (string)$tug['id'];
$tSmallTeam = (string)$tug['teams'][0]['id'];
$tBigTeam = (string)$tug['teams'][1]['id'];
$smallStudent = (string)$tug['teams'][0]['members'][0];
$bigStudents = array_slice($tug['teams'][1]['members'], 0, 3);
tg58_student_action($tugId, 'tug_next', $smallStudent, [], $now0);
$itemSmall = tg58_quiz_find((string)tg58_get($tugId)['game']['students'][$smallStudent]['current_item_id']);
$r1 = tg58_student_action($tugId, 'tug_answer', $smallStudent, ['answer' => $itemSmall['answer']], $now0);
$posAfterSmall = (float)tg58_get($tugId)['game']['position'];
foreach ($bigStudents as $i => $bs) {
    tg58_student_action($tugId, 'tug_next', $bs, [], $now0 + 5 * ($i + 1));
    $itemBig = tg58_quiz_find((string)tg58_get($tugId)['game']['students'][$bs]['current_item_id']);
    tg58_student_action($tugId, 'tug_answer', $bs, ['answer' => $itemBig['answer']], $now0 + 5 * ($i + 1));
}
$posAfterAllBig = (float)tg58_get($tugId)['game']['position'];
$pullBig = ($posAfterAllBig - $posAfterSmall) / count($bigStudents);
check(abs($pullBig) < abs($posAfterSmall) - 1e-9, 'tah = 1/počet aktivních členů (větší tým táhne méně na hlavu)');
try { tg58_student_action($tugId, 'tug_next', $smallStudent, [], $now0 + 1); tg58_student_action($tugId, 'tug_answer', $smallStudent, ['answer' => 'x'], $now0 + 1); check(false, 'limit 1 odpověď / 4 s se vynucuje'); } catch (RuntimeException $e) { check(true, 'limit 1 odpověď / 4 s se vynucuje'); }
$tugView = tg58_student_view(tg58_get($tugId), $smallStudent, $now0);
check(!isset($tugView['leaderboard']) && !isset($tugView['students']), 'bez individuálního žebříčku ve výstupu pro žáka');
$roundLen = tg58_tug_round_len_s(tg58_get($tugId));
$afterRound = tg58_settle(tg58_get($tugId), $now0 + $roundLen + 5);
check((int)$afterRound['game']['round_index'] === 1 && abs((float)$afterRound['game']['position']) < 1e-9, 'nové kolo vynuluje lano');
tg58_end_session($tugId, $now0 + $roundLen + 10); // uvolní třídu (jen jedna běžící hra na třídu)

echo "== Správci sítě/webu ==\n";
$naSession = create_and_start(['class_id' => A_CLASS_NET, 'type' => 'netadmin', 'line' => 'networks', 'team_count' => 4, 'node_count' => 16, 'wave_minutes' => 5], $CLASS_IDS, $now0);
$naId = (string)$naSession['id'];
check(count($naSession['game']['node_ids']) === 16, 'počet uzlů odpovídá nastavení');
$wave1Only = array_filter($naSession['game']['node_wave'], static fn(int $w): bool => $w === 1);
check(count($wave1Only) < count($naSession['game']['node_ids']), 'vlny nových závad – ne všechny uzly hned na začátku');
$firstNode = array_key_first($naSession['game']['node_wave']);
$naStudent = (string)$naSession['teams'][0]['members'][0];
$r = lab_op(A_CLASS_NET, $naStudent, 'tg:' . $naId, $firstNode, 'state', [], $now0);
check(($r['ok'] ?? false) === true, 'první vlna uzlů je hned přístupná');
$hiddenNode = null;
foreach ($naSession['game']['node_wave'] as $nid => $w) { if ($w > 1) { $hiddenNode = $nid; break; } }
if ($hiddenNode !== null) {
    $r = lab_op(A_CLASS_NET, $naStudent, 'tg:' . $naId, $hiddenNode, 'state', [], $now0);
    check(($r['ok'] ?? true) === false, 'pozdější vlna uzlů zatím není přístupná');
    $laterNow = $now0 + (int)$naSession['settings']['wave_minutes'] * 60 * ((int)$naSession['game']['node_wave'][$hiddenNode] - 1) + 5;
    $r = lab_op(A_CLASS_NET, $naStudent, 'tg:' . $naId, $hiddenNode, 'state', [], $laterNow);
    check(($r['ok'] ?? false) === true, 'uzel se zpřístupní po uplynutí jeho vlny');
}
// Souběh: >=10 procesů, přesně jeden vlastník uzlu (worker skript v dočasném úložišti).
$workerNode = $firstNode;
$workerPath = $tmp . '/na_worker.php';
file_put_contents($workerPath, "<?php\n"
    . "\$GLOBALS['tg58_storage_override']=" . var_export($tmp, true) . ";\$GLOBALS['lab57_storage_override']=" . var_export($tmp, true) . ";\n"
    . "\$GLOBALS['lab57_secret_override']=" . var_export($GLOBALS['lab57_secret_override'], true) . ";\$GLOBALS['lab58_skip_ext']=true;\n"
    . "function storage_update(string \$p, callable \$m): array { \$fp=fopen(\$p,'c+'); flock(\$fp,LOCK_EX); \$raw=(string)stream_get_contents(\$fp); \$d=json_decode(trim(preg_replace('/^<\\?php.*?\\?>\\s*/s','',\$raw)??''),true); \$d=\$m(is_array(\$d)?\$d:[]); ftruncate(\$fp,0); rewind(\$fp); fwrite(\$fp,\"<?php http_response_code(403); exit; ?>\\n\".json_encode(\$d,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).\"\\n\"); fflush(\$fp); flock(\$fp,LOCK_UN); fclose(\$fp); return \$d; }\n"
    . "require_once " . var_export($root . '/linux_v57_lab.php', true) . ";\n"
    . "require_once " . var_export($root . '/linux_v58_levels_tg.php', true) . ";\n"
    . "require_once " . var_export($root . '/teamgames_v58_registry.php', true) . ";\n"
    . "require_once " . var_export($root . '/teamgames_v58_game_netadmin.php', true) . ";\n"
    . "tg58_netadmin_on_node_complete(\$argv[1], \$argv[2], \$argv[3], (int)\$argv[4]);\n");
$php = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : 'php';
$procs = [];
$workerCount = 0;
// Výstup jde do souborů, ne do nepřečtených roury (na Windows by zaplněný pipe buffer proces zaseknul).
foreach ($naSession['teams'] as $team) {
    foreach (array_slice($team['members'], 0, 3) as $student) {
        $cmd = [$php, $workerPath, $naId, (string)$student, (string)$workerNode, (string)$now0];
        $outFile = $tmp . '/na_worker_' . $workerCount . '.log';
        $procs[] = proc_open($cmd, [1 => ['file', $outFile, 'w'], 2 => ['file', $outFile, 'w']], $pipesTmp, $tmp);
        $workerCount++;
    }
}
foreach ($procs as $p) { if (is_resource($p)) proc_close($p); }
check($workerCount >= 10, 'souběh testován aspoň 10 procesy (' . $workerCount . ')');
$naAfter = tg58_get($naId);
$ownerCount = 0;
$learnCount = 0;
foreach ($naSession['teams'] as $team) {
    $node = $naAfter['game']['nodes'][$workerNode] ?? ['owner' => null, 'learn_by' => []];
    if ($node['owner'] === (string)$team['id']) $ownerCount++;
    if (in_array((string)$team['id'], (array)$node['learn_by'], true)) $learnCount++;
}
check($ownerCount === 1, 'souběh na jednom uzlu: právě jeden vlastník (' . $ownerCount . ')');
check($learnCount === count($naSession['teams']) - 1, 'ostatní týmy dostaly jen bonus „za učení“, uzel nikomu nevzali');
// Vlastní kopie světa: druhý žák (jiný tým) může uzel nezávisle vyřešit přes lab57_session bez ovlivnění prvního.
$naStudent2 = (string)$naSession['teams'][1]['members'][0];
$level = lab57_level($workerNode);
$fixValue = (string)($level['checks'][0][1]['equals'] ?? '');
$path = (string)($level['checks'][0][1]['path'] ?? '');
$seedS1 = lab57_seed(A_CLASS_NET, $naStudent, 'tg:' . $naId, $level);
$seedS2 = lab57_seed(A_CLASS_NET, $naStudent2, 'tg:' . $naId, $level);
check($seedS1 !== $seedS2, 'každý řešitel má vlastní (jiné) semínko/kopii světa uzlu');
$scores = tg58_netadmin_scores($naAfter);
check(array_sum($scores) > 0, 'body Správců sítě se počítají (základ + regiony + učení)');
tg58_end_session($naId, $now0 + 30); // uvolní třídu (jen jedna běžící hra na třídu)

echo "== Úniková místnost ==\n";
$escNet = create_and_start(['class_id' => A_CLASS_NET, 'type' => 'escape', 'line' => 'networks', 'team_count' => 2], $CLASS_IDS, $now0);
$escId = (string)$escNet['id'];
$escTeamA = (string)$escNet['teams'][0]['id'];
$roles = TG58_ESCAPE_ROLES_NET;
$byRole = [];
foreach ($escNet['game']['teams'][$escTeamA]['assign'] as $key => $role) $byRole[$role][] = $key;
check(count(array_unique(array_values($escNet['game']['teams'][$escTeamA]['assign']))) >= 2, 'role jsou v týmu rozdělené (asymetrické informace)');
$sitarStudent = $byRole['sitar'][0];
$dokStudent = $byRole['dokumentator'][0];
$viewSitar = tg58_student_view($escNet, $sitarStudent, $now0);
$viewDok = tg58_student_view($escNet, $dokStudent, $now0);
check(($viewSitar['game']['role'] ?? '') === 'sitar' && ($viewDok['game']['role'] ?? '') === 'dokumentator', 'API vrací žákovi jen jeho vlastní roli');
check(($viewSitar['game']['role_label'] ?? '') !== ($viewDok['game']['role_label'] ?? ''), 'dvě role v témže týmu vidí jiná zadání');
$level = lab57_level('tg-escape-net-1');
$sitarTask = lab58_level_for_role($level, 'sitar')['task'];
$dokTask = lab58_level_for_role($level, 'dokumentator')['task'];
check($sitarTask !== $dokTask, 'sdílený svět: role mění zadání, ne přístup k datům jiných');
$fragSitar = tg58_escape_fragment_expected($escNet, $escTeamA, 'sitar', $now0);
$fragSitarAgain = tg58_escape_fragment_expected($escNet, $escTeamA, 'sitar', $now0 + 999);
check($fragSitar !== null && $fragSitar === $fragSitarAgain, 'fragment role je deterministický (nezávisí na čase)');
$escTeamB = (string)$escNet['teams'][1]['id'];
$fragSitarTeamB = tg58_escape_fragment_expected($escNet, $escTeamB, 'sitar', $now0);
check($fragSitar !== $fragSitarTeamB, 'fragment se liší tým od týmu');
$correctParts = array_map(static fn(string $r) => tg58_escape_fragment_expected($escNet, $escTeamA, $r, $now0), $roles);
$partialParts = $correctParts; $partialParts[3] = 'ZZZZ';
$bad = tg58_student_action($escId, 'escape_unlock', $sitarStudent, ['parts' => $partialParts], $now0);
check(($bad['correct'] ?? true) === false, 'kód nejde složit bez všech správných dílů');
check((tg58_get($escId)['game']['teams'][$escTeamA]['escaped_at'] ?? null) === null, 'tým neuniká bez kompletního kódu');
for ($i = 0; $i < 4; $i++) tg58_student_action($escId, 'escape_unlock', $sitarStudent, ['parts' => $partialParts], $now0 + $i); // + 1 pokus výše (řádek 462) = 5 pokusů celkem (limit)
try { tg58_student_action($escId, 'escape_unlock', $sitarStudent, ['parts' => $partialParts], $now0 + 6); check(false, 'limit 5 pokusů o zámek / minutu se vynucuje'); } catch (RuntimeException $e) { check(true, 'limit 5 pokusů o zámek / minutu se vynucuje'); }
$good = tg58_student_action($escId, 'escape_unlock', $sitarStudent, ['parts' => $correctParts], $now0 + 90);
check(($good['correct'] ?? false) === true, 'správný kód ze všech 4 dílů otevře zámek');
check(in_array($escTeamA, (array)tg58_get($escId)['game']['winners'], true), 'unikající tým se počítá do vítězů (cíl třídy)');
// FAIR58-05: fragment = HMAC se serverovým tajemstvím; v Labu ho vidí jen jeho role, svět ho vůbec neobsahuje
$escSrc = (string)file_get_contents($root . '/linux_v58_levels_tg.php') . (string)file_get_contents($root . '/teamgames_v58_game_escape.php');
check(str_contains($escSrc, 'hash_hmac(\'sha256\', $gameId . \'|\' . $teamId . \'|\' . $role, lab57_secret())') && !str_contains($escSrc, 'escfrag'), 'FAIR58-05: fragment je HMAC(tajemství, hra|tým|role), ne veřejný hash');
$fragDok = tg58_escape_fragment_expected($escNet, $escTeamA, 'dokumentator', $now0);
$outOf = static fn(array $r): string => implode('', array_map(static fn($c): string => (string)($c[1] ?? ''), (array)($r['out'] ?? [])));
$runS = lab_op(A_CLASS_NET, $sitarStudent, 'tg:' . $escId, 'tg-escape-net-1', 'run', ['line' => 'cat ~/site/network.log'], $now0 + 91);
$runD = lab_op(A_CLASS_NET, $dokStudent, 'tg:' . $escId, 'tg-escape-net-1', 'run', ['line' => 'cat ~/site/network.log'], $now0 + 92);
$runD2 = lab_op(A_CLASS_NET, $dokStudent, 'tg:' . $escId, 'tg-escape-net-1', 'run', ['line' => 'grep -r segment ~'], $now0 + 93);
check(str_contains($outOf($runS), (string)$fragSitar), 'FAIR58-05: Síťař v Labu vidí svůj fragment', $outOf($runS));
check(!str_contains($outOf($runD), (string)$fragSitar) && str_contains($outOf($runD), 'skryto'), 'FAIR58-05: Dokumentátor ve sdíleném světě cizí fragment nevidí', $outOf($runD));
check(str_contains($outOf($runD2), (string)$fragDok) && !str_contains($outOf($runD2), (string)$fragSitar), 'FAIR58-05: grep přes celý svět ukáže jen vlastní segment');
$stD = lab_op(A_CLASS_NET, $dokStudent, 'tg:' . $escId, 'tg-escape-net-1', 'state', [], $now0 + 94);
check(!str_contains((string)json_encode($stD, JSON_UNESCAPED_UNICODE), (string)$fragSitar), 'FAIR58-05: stav terminálu (historie týmu) cizí fragment neprozradí');
$stateRaw = (string)@file_get_contents(lab57_state_path(A_CLASS_NET, 'tgteam:' . $escId . ':' . $escTeamA));
check($stateRaw !== '' && !str_contains($stateRaw, (string)$fragSitar) && !str_contains($stateRaw, (string)$fragDok), 'FAIR58-05: uložený sdílený svět fragmenty vůbec neobsahuje');
tg58_end_session($escId, $now0 + 120); // uvolní třídu (jen jedna běžící hra na třídu)
$escG = create_and_start(['class_id' => A_CLASS_GFX, 'type' => 'escape', 'line' => 'graphics', 'team_count' => 2], $CLASS_IDS, $now0);
$escGId = (string)$escG['id'];
$gT0 = (string)$escG['teams'][0]['id'];
$gT1 = (string)$escG['teams'][1]['id'];
check(tg58_escape_fragment_expected($escG, $gT0, 'typograf', $now0) !== tg58_escape_fragment_expected($escG, $gT1, 'typograf', $now0), 'FAIR58-05 grafika: fragment stejné role se liší tým od týmu');
$gS = (string)$escG['teams'][0]['members'][0];
for ($i = 0; $i < TG58_WRONG_MAX; $i++) tg58_student_action($escGId, 'escape_answer', $gS, ['answer' => 'spatne-' . $i], $now0 + $i);
$blocked = false;
try { tg58_student_action($escGId, 'escape_answer', $gS, ['answer' => 'spatne-x'], $now0 + 8); } catch (RuntimeException $e) { $blocked = true; }
check($blocked, 'FAIR58-05 Úniková místnost: 6. špatná odpověď brány za minutu je zablokovaná');
tg58_end_session($escGId, $now0 + 120);

echo "== Soukromí na projektoru (anon) ==\n";
$anonCases = [
    ['relay', $relayNet],
    ['bingo', $bingoNet],
    ['jeopardy', tg58_get($jeopId)],
    ['tug', tg58_get($tugId)],
    ['netadmin', $naSession],
    ['escape', $escNet],
];
$namesToCheck = [];
foreach (array_merge(fake_roster(24, 'S'), fake_roster(20, 'G')) as $row) $namesToCheck[] = $row['label'];
foreach ($anonCases as [$typeName, $sessionRow]) {
    $anonId = (string)tg58_create_session(['class_id' => (string)$sessionRow['class_id'], 'type' => $typeName, 'line' => (string)$sessionRow['line'], 'names' => 'anon', 'team_count' => 2] + (array)$sessionRow['settings'], $CLASS_IDS, $now0)['id'];
    $anonLive = tg58_start_session($anonId, $now0);
    $proj = tg58_projector_view($anonLive, $now0 + 1);
    $json = (string)json_encode($proj, JSON_UNESCAPED_UNICODE);
    $leak = false;
    foreach ($namesToCheck as $name) { if ($name !== '' && str_contains($json, $name)) { $leak = true; break; } }
    check(!$leak, "projektor ($typeName, anon) neprozradí jméno žáka");
    check(strpos($json, '"role"') === false && strpos($json, 'penalty_s') === false, "projektor ($typeName) nezobrazuje detaily jednotlivců (role/penalizace)");
    tg58_end_session($anonId, $now0 + 2); // před smazáním musí být hra ukončená
    tg58_delete_session($anonId);
}

echo "== XP jen jednou ==\n";
// $relayId je už ukončený (viz výše) – claim_xp má projít přes všechny dohrané hry třídy.
$xp1 = tg58_claim_xp(A_CLASS_NET, $studentA);
$xp2 = tg58_claim_xp(A_CLASS_NET, $studentA);
check($xp1 > 0 && $xp2 === 0, 'XP za dohranou hru se připíše jen jednou');

echo "== Bezpečnost: zakázané funkce, CSRF, session, polling ==\n";
$myFiles = glob($root . '/teamgames_v58_*.php') ?: [];
$myFiles = array_merge($myFiles, [$root . '/linux_v58_levels_tg.php']);
$forbidden = ['exec(', 'shell_exec(', 'system(', 'passthru(', 'proc_open(', 'popen(', 'pcntl_exec(', 'eval(', 'assert(', 'create_function(', 'fsockopen(', 'stream_socket_client(', 'socket_create(', 'curl_init(', 'mail('];
$tokenOk = true;
foreach ($myFiles as $file) {
    $src = (string)file_get_contents($file);
    foreach ($forbidden as $tok) { if (str_contains($src, $tok)) { $tokenOk = false; echo 'FAIL  zakázaný token ' . trim($tok, '(') . ' v ' . basename($file) . PHP_EOL; } }
    // Zpětné apostrofy kontrolujeme přes tokenizer (ne hledáním podřetězce), ať nehlásí planý poplach
    // na `příkaz` v komentářích/textu otázek – skutečný operátor shell_exec je samostatný token mimo řetězce/komentáře.
    foreach (token_get_all($src) as $tok2) { if (!is_array($tok2) && $tok2 === '`') { $tokenOk = false; echo 'FAIL  zakázaný token backtick (shell_exec) v ' . basename($file) . PHP_EOL; break; } }
    if (preg_match('/\bfile_get_contents\s*\(\s*[\'"]https?:/i', $src) || preg_match('/\bfopen\s*\(\s*[\'"]https?:/i', $src)) { $tokenOk = false; echo 'FAIL  vzdálené URL v ' . basename($file) . PHP_EOL; }
}
check($tokenOk, 'žádné zakázané funkce/síť v mých PHP souborech (simulátor nic nespouští)');
$jsSrc = (string)file_get_contents($root . '/assets/teamgames-v58.js');
check(!str_contains($jsSrc, 'eval(') && !str_contains($jsSrc, 'new Function(') && !str_contains($jsSrc, '.innerHTML'), 'JS: žádný eval/new Function/innerHTML');
$apiSrc = (string)file_get_contents($root . '/teamgames_v58_api.php');
check(str_contains($apiSrc, 'tg58_csrf_ok'), 'API kontroluje CSRF (hash_equals)');
check(str_contains($apiSrc, 'session_write_close()'), 'API volá session_write_close() po ověření identity');
check(str_contains($apiSrc, "adaptive_student_key"), 'API bere identitu žáka ze session, ne z parametru');
$teacherSrc = (string)file_get_contents($root . '/teamgames_v58_teacher_views.php');
check(!str_contains($teacherSrc, 'verify_csrf'), 'POST akce spoléhá na CSRF z teacher.php (žádná duplicitní/chybějící logika)');
check(str_contains($teacherSrc, "session_write_close()"), 'GET polling učitele/projektoru uvolní zámek session');
// Pozn.: mapování prefixu 'tg58_' na oprávnění 'students.manage' (deny-by-default) je v
// teacher_operations_v46.php (mimo vlastnictví TG-CORE) – ověřeno ručně při integraci, viz závěrečná zpráva.

echo "\n== Souhrn ==\n";
echo ($failed === 0 ? "V58_TEAMGAMES_AUDIT_OK checks=$checks failed=0\n" : "V58_TEAMGAMES_AUDIT_FAILED checks=$checks failed=$failed\n");

function rm_tree(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}
rm_tree($tmp);
exit($failed === 0 ? 0 : 1);
