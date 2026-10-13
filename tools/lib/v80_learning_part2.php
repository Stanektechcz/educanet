<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80 · část 2 tools/v80_learning_audit.php (používá $check, $ROOT, $read, $tmp): čisté funkce a úložiště v dočasném adresáři.
 */

// ---------------------------------------------------------------- 2a) nápovědy a vysvětlení chyb (všechny generátory)
$hintBad = [];
$diagBad = [];
foreach (array_keys(TB75_TASKS) as $task) {
    for ($i = 0; $i < 150; $i++) {
        $v = tb75_variant($task, 'stu_' . sprintf('%016x', $i * 104729 + 7), 1 + $i % 6);
        $h = hn80_hints($v);
        if (count($h) !== 3 || count(array_unique($h)) !== 3 || in_array('', $h, true) || !str_starts_with($h[2], 'Vyřešený krok')) $hintBad[$task] = $i;
        $ok = array_column($v['fields'], 'answer', 'id');
        $wrong = array_map(static fn(string $a): string => $a . '7', $ok);
        $why = hn80_diagnose($v, $wrong);
        if (hn80_diagnose($v, $ok) !== [] || count($why) !== count($ok) || in_array('', $why, true) || max(array_map('mb_strlen', $why)) > HN80_WHY_MAX) $diagBad[$task] = $i;
    }
}
$check('nápovědy: 11 generátorů × 150 variant – vždy 3 různé nápovědy (postrčení → postup → vyřešený krok s čísly varianty)' . ($hintBad ? ' [' . json_encode($hintBad) . ']' : ''), $hintBad === []);
$check('vysvětlení chyby: správná odpověď nic nehlásí, každé chybné pole má neprázdné vysvětlení ≤ ' . HN80_WHY_MAX . ' znaků' . ($diagBad ? ' [' . json_encode($diagBad) . ']' : ''), $diagBad === []);
$S = 'stu_0123456789abcdef';
$dg = static function (string $task, callable $answers) use ($S): array { $v = tb75_variant($task, $S, 1); return hn80_diagnose($v, $answers($v, array_column($v['fields'], 'answer', 'id'))); };
$sub = $dg('net_subnet', static fn(array $v, array $a): array => ['network' => $a['broadcast'], 'broadcast' => $a['network'], 'hosts' => (string)((int)$a['hosts'] + 2)]);
$mask = $dg('net_mask', static fn(array $v, array $a): array => ['prefix' => (string)(32 - (int)$a['prefix'])]);
$ports = $dg('net_ports', static fn(array $v, array $a): array => ['p0' => $a['p0'] === '22' ? '80' : '22']);
$route = $dg('net_route', static fn(array $v, array $a): array => ['d0' => '10.0.0.1']);
$chm = $dg('lnx_chmod', static fn(array $v, array $a): array => ['oct' => strrev($a['oct']), 'after' => tb75_symbolic((int)hn80_sym_to_oct((string)hn80_chmod_params($v)['base']))]);
$col = $dg('gfx_color', static fn(array $v, array $a): array => ['rgb' => implode(',', array_reverse(explode(',', $a['rgb']))), 'inv' => '#' . ltrim((string)preg_replace('~^.*(#[0-9a-f]{6}).*$~', '$1', (string)$v['fields'][2]['label']), '#')]);
$units = $dg('web_units', static fn(array $v, array $a): array => ['em' => (string)(hn80_units_params($v)['em'] * 16), 'rem' => (string)(hn80_units_params($v)['rem'] * hn80_units_params($v)['parent']), 'pct' => (string)hn80_units_params($v)['pct']]);
$dpi = $dg('img_dpi', static fn(array $v, array $a): array => ['cm' => (string)round(hn80_dpi_params($v)['w'] / hn80_dpi_params($v)['dpi'], 1), 'mp' => (string)(hn80_dpi_params($v)['w'] * hn80_dpi_params($v)['h'])]);
$check('vysvětlení konkrétní chyby: záměna síť/broadcast, −2 adresy, spočítané nuly v masce, port jiné služby, výchozí trasa, obrácené číslice chmod, beze změny, pořadí R,G,B, původní barva u inverze, em z kořene, rem z rodiče, procenta bez přepočtu, palce místo cm, megapixel',
    str_contains($sub['network'], 'broadcast') && str_contains($sub['broadcast'], 'síťová adresa') && str_contains($sub['hosts'], 'odečti 2') && str_contains($mask['prefix'], 'nuly')
    && str_contains($ports['p0'], 'patří službě') && str_contains($route['d0'], 'Výchozí trasa') && str_contains($chm['oct'], 'obráceně') && str_contains($chm['after'], 'zůstal stejný')
    && str_contains($col['rgb'], 'pořadí') && str_contains($col['inv'], 'původní barva') && str_contains($units['em'], 'z kořene') && str_contains($units['rem'], 'ne rodiče') && str_contains($units['pct'], 'přepočítat')
    && str_contains($dpi['cm'], 'palcích') && str_contains($dpi['mp'], 'milionem'));
$path = tb75_variant('lnx_path', $S, 1);
$pp = hn80_path_params($path);
$grepV = null;
for ($i = 0; $i < 60 && $grepV === null; $i++) {
    $g = tb75_variant('lnx_grep', $S, $i + 1);
    $q = hn80_grep_params($g);
    if (tb75_grep_count($q['lines'], $q['word'], true, false) !== tb75_grep_count($q['lines'], $q['word'], false, false)) $grepV = $g;
}
$gq = hn80_grep_params((array)$grepV);
$gw = $grepV !== null ? hn80_diagnose($grepV, ['g0' => (string)tb75_grep_count($gq['lines'], $gq['word'], true, false), 'g1' => (string)tb75_grep_count($gq['lines'], $gq['word'], false, false)]) : [];
$check('vysvětlení: cesta – výchozí složka a mezikrok („po N. příkazu“), relativní cesta bez „/“; grep – -c rozlišuje velikost písmen, -i ji ignoruje',
    $pp !== null && str_contains(hn80_diagnose($path, ['pwd' => $pp['start']])['pwd'] ?? '', 'výchozí') && (count($pp['states']) < 2 || $pp['states'][0] === end($pp['states']) || str_contains(hn80_diagnose($path, ['pwd' => $pp['states'][0]])['pwd'] ?? '', 'po 1. příkazu'))
    && str_contains(hn80_diagnose($path, ['pwd' => 'home/x'])['pwd'] ?? '', 'absolutní') && str_contains($gw['g0'] ?? '', 'velikost písmen') && str_contains($gw['g1'] ?? '', '-i'));
$specBad = 0;
for ($i = 0; $i < 300; $i++) {
    $v = tb75_variant('css_spec', 's' . $i, 1);
    if (implode(',', hn80_spec(hn80_selectors($v)[0])) !== $v['fields'][0]['answer']) $specBad++;
}
$lens = array_map(static fn(int $i): int => strlen((string)tb75_variant('lnx_path', 'x' . $i, 1 + $i % 4)['fields'][0]['answer']), range(0, 3999));
$check('specificita hn80_spec = klíč generátoru v 300 variantách; nejdelší odpověď cesty (' . max($lens) . ' znaků) se vejde do TB75_ANSWER_MAX = 120 a do maxlength formulářů (dřív 80 = některé varianty nešly vyřešit)',
    $specBad === 0 && max($lens) <= TB75_ANSWER_MAX && str_contains($read('stepper_v79_student.php'), 'maxlength="120"') && str_contains($read('taskbank_v75_views.php'), 'maxlength="120"') && !str_contains($read('stepper_v79_student.php'), 'maxlength="80"'));

// ---------------------------------------------------------------- 2b) rozložené opakování (čisté funkce)
$now = (int)strtotime('2026-11-04 10:15:00');
$s0 = lr80_normalize([]);
$s1 = lr80_rep_enqueue($s0, 'net_subnet', ['broadcast', 'hosts', '<x>'], $now);
$item = $s1['rep']['net_subnet'];
$tomorrow = (int)strtotime('2026-11-05 00:00:00');
$a = lr80_rep_schedule($item, true, $tomorrow + 3600);
$b = lr80_rep_schedule((array)$a['item'], true, (int)$a['item']['due_at'] + 60);
$c = lr80_rep_schedule((array)$b['item'], true, (int)$b['item']['due_at'] + 60);
$w = lr80_rep_schedule((array)$b['item'], false, (int)$b['item']['due_at'] + 60);
$check('opakování: chyba → zítra o půlnoci (ne za 24 h), pole jen id z whitelistu; správně → za 3 dny → za 7 dní → řada hotová; chyba v opakování → znovu od zítřka (stupeň 0)',
    $item['s'] === 0 && $item['due_at'] === $tomorrow && $item['f'] === ['broadcast', 'hosts'] && lr80_rep_due($s1, $now) === [] && lr80_rep_due($s1, $tomorrow) === ['net_subnet']
    && $a['days'] === 3 && $a['item']['s'] === 1 && $a['item']['due_at'] === (int)strtotime('2026-11-08 00:00:00') && $b['days'] === 7 && $b['item']['s'] === 2
    && $c['done'] && $c['item'] === null && $w['item']['s'] === 0 && $w['days'] === 1 && $w['item']['c'] === 3);
$s2 = lr80_rep_enqueue(lr80_rep_enqueue($s1, 'lnx_chmod', ['oct'], $now - 86400), 'nezname', ['x'], $now);
$v1 = lr80_rep_variant('net_subnet', $S, ['c' => 0]);
$check('opakování: varianta deterministická (stejné ref + cyklus = stejná čísla), jiná než v bance úloh i v dalším cyklu; neznámá úloha se nezařadí; „na dnes“ seřazené od nejstarší',
    $v1 === lr80_rep_variant('net_subnet', $S, ['c' => 0]) && $v1['prompt'] !== tb75_variant('net_subnet', $S, 1)['prompt'] && $v1['prompt'] !== lr80_rep_variant('net_subnet', $S, ['c' => 1])['prompt']
    && !isset($s2['rep']['nezname']) && lr80_rep_due($s2, $tomorrow + 10) === ['lnx_chmod', 'net_subnet'] && array_keys(lr80_rep_planned($s2, $now - 86400)) === ['lnx_chmod', 'net_subnet'] && array_keys(lr80_rep_planned($s2, $now)) === ['net_subnet']);

// ---------------------------------------------------------------- 2c) interaktivní cvičení (čisté funkce)
$wv = [];
foreach (array_keys(WG80_WIDGETS) as $wid) $wv[$wid] = wg80_variant($wid, $S, 1);
$okAll = array_filter($wv, static fn(array $v): bool => wg80_check($v, $v['fields'])['ok']);
$chmodAns = wg80_answers('chmod', ['p' => array_values(array_filter(WG80_PERMS, static fn(string $p, int $i): bool => ((int)$wv['chmod']['data']['mode'] & (1 << (8 - $i))) !== 0, ARRAY_FILTER_USE_BOTH))]);
$check('cvičení: varianty deterministické a jiné pro jiného žáka i kolo, správné hodnoty projdou kontrolou; chmod ze zaškrtávátek i z osmičkového pole (bez JS), odpovědi omezené whitelistem',
    count($okAll) === 4 && wg80_variant('color', $S, 1) === $wv['color'] && wg80_variant('color', 'stu_ffffffffffffffff', 1)['data'] !== $wv['color']['data'] && wg80_variant('color', $S, 2)['data'] !== $wv['color']['data']
    && $chmodAns['mode'] === $wv['chmod']['fields']['mode'] && wg80_answers('chmod', ['mode' => '0754'])['mode'] === '754' && wg80_answers('chmod', ['p' => ['ur', 'zz', ['x']]])['mode'] === '400'
    && wg80_answers('spec', ['win' => 'c'])['win'] === '' && mb_strlen(wg80_answers('subnet', ['network' => str_repeat('9', 99)])['network']) === 40);
$cw = wg80_diagnose($wv['chmod'], ['mode' => sprintf('%03o', (int)$wv['chmod']['data']['mode'] ^ 0011)]);
$cv = $wv['color'];
$pair = substr(ltrim((string)$cv['data']['hex'], '#'), 0, 2);
$cd = wg80_diagnose($cv, ['r' => ctype_digit($pair) ? (string)(int)$pair : '300', 'g' => $cv['fields']['g'], 'b' => $cv['fields']['b']]);
$sv = $wv['spec'];
$loser = $sv['fields']['win'] === 'a' ? 'b' : 'a';
$sd = wg80_diagnose($sv, ['sa' => $sv['fields']['sa'], 'sb' => $sv['fields']['sb'], 'win' => $loser]);
$subd = wg80_diagnose($wv['subnet'], ['network' => $wv['subnet']['fields']['broadcast'], 'broadcast' => $wv['subnet']['fields']['broadcast'], 'hosts' => $wv['subnet']['fields']['hosts']]);
$check('cvičení – vysvětlení chyby: chmod jmenuje skupinu a právo (chybí / je navíc), barva složku (šestnáctkově nebo rozsah 0–255), souboj porovnání trojic, podsíť záměnu broadcastu',
    str_contains($cw['mode'] ?? '', 'skupina') && str_contains($cw['mode'] ?? '', 'ostatní') && preg_match('~(je navíc|chybí)~u', $cw['mode'] ?? '') === 1
    && str_contains($cd['r'] ?? '', 'červená (R)') && str_contains($sd['win'] ?? '', '(') && str_contains($subd['network'] ?? '', 'broadcast'));
$check('souboj specificity: vítěz = vyšší trojice (hn80_spec), trojice A a B se nikdy nerovnají (200 variant)', (static function (): bool {
    for ($i = 0; $i < 200; $i++) {
        $v = wg80_variant('spec', 'r' . $i, 1);
        $a = hn80_spec($v['data']['a']);
        $b = hn80_spec($v['data']['b']);
        if ($a === $b || ($a > $b ? 'a' : 'b') !== $v['fields']['win']) return false;
    }
    return true;
})());

// ---------------------------------------------------------------- 2d) úložiště: háček úlohy, nápovědy, opakování, cvičení, cíle, odznaky, retence, soukromí
$students = project_students_for_class('class_3a');
$key = (string)array_keys($students)[0];
$key2 = (string)array_keys($students)[1];
$label = (string)$students[$key]['label'];
$ref = lr80_ref('class_3a', $key);
$t0 = (int)strtotime('2026-11-02 09:00:00');   // pondělí
$var = tb75_variant('net_subnet', tb75_student_ref('class_3a', $key), 1);
$res = tb75_submit('class_3a', $key, 'net_subnet', ['network' => '10.9.9.9', 'broadcast' => (string)$var['fields'][1]['answer'], 'hosts' => 'nevím Žák'], $t0);
lr80_on_task_result('class_3a', $key, 'net_subnet', (array)$res['result'], $t0);
$raw = (string)@file_get_contents(lr80_path('class_3a'));
$st = lr80_state('class_3a', $ref);
$check('háček úlohy: chybná pole → opakování (jen id polí, zítra), soubor learning_v80_class_3a bez jména žáka a bez textu odpovědi; klíč žáka = ref úloh v75',
    isset($st['rep']['net_subnet']) && $st['rep']['net_subnet']['f'] === ['network', 'hosts'] && $st['rep']['net_subnet']['due_at'] === lr80_add_days($t0, 1)
    && !str_contains($raw, $label) && !str_contains($raw, '10.9.9.9') && !str_contains($raw, 'nevím') && str_contains($raw, '"' . $ref . '"') && preg_match('/^<\?php http_response_code\(403\); exit; \?>/', $raw) === 1);
$l1 = lr80_hint_reveal('class_3a', $key, 'net_subnet', 1, $t0 + 60);
lr80_hint_reveal('class_3a', $key, 'net_subnet', 1, $t0 + 70);
lr80_hint_reveal('class_3a', $key, 'net_subnet', 1, $t0 + 80);
$l4 = lr80_hint_reveal('class_3a', $key, 'net_subnet', 1, $t0 + 90);
$st = lr80_state('class_3a', $ref);
$l5 = lr80_hint_reveal('class_3a', $key, 'net_subnet', 2, $t0 + 100);
$check('nápovědy se ukládají jen jako počty: 1 → 3 (víc ne), počet 3, kolo s nápovědou; nové kolo začne od 1 a počet roste; neplatný klíč / kolo 0 se odmítne',
    $l1 === 1 && $l4 === 3 && $st['hint']['net_subnet'] === ['r' => 1, 'n' => 3, 'c' => 3, 'u' => [1]] && $l5 === 1 && lr80_hint_total(lr80_state('class_3a', $ref)) === 4
    && lr80_hint_reveal('class_3a', $key, '../x', 1) === 0 && lr80_hint_reveal('class_3a', $key, 'net_subnet', 0) === 0 && lr80_hint_level(lr80_state('class_3a', $ref), 'net_subnet', 9) === 0);
$early = lr80_rep_submit('class_3a', $key, 'net_subnet', ['network' => 'x'], $t0 + 3600);
$due = lr80_add_days($t0, 1) + 3600;
$rv = lr80_rep_variant('net_subnet', $ref, lr80_state('class_3a', $ref)['rep']['net_subnet']);
$good = lr80_rep_submit('class_3a', $key, 'net_subnet', array_column($rv['fields'], 'answer', 'id'), $due);
$again = lr80_rep_submit('class_3a', $key, 'net_subnet', array_column($rv['fields'], 'answer', 'id'), $due + 60);
$st = lr80_state('class_3a', $ref);
$check('opakování v úložišti: před termínem odmítnuto (not_due), v termínu správně → za 3 dny a čas úspěchu v logu; druhý pokus týž den odmítnut (jeden pokus za den)',
    $early['error'] === 'not_due' && $good['ok'] && $good['result']['ok'] && $good['days'] === 3 && $again['error'] === 'not_due' && $st['rep']['net_subnet']['s'] === 1 && $st['log']['rep_ok_at'] === [$due]);
$wr = wg80_submit('class_3a', $key, 'chmod', ['p' => ['ur']], $t0 + 200);
$wv2 = wg80_variant('color', $ref, 1);
$wo = wg80_submit('class_3a', $key, 'color', $wv2['fields'], $t0 + 300);
$st = lr80_state('class_3a', $ref);
$check('cvičení v úložišti: chyba = pokus +1 a související úloha (lnx_chmod) do opakování; správně = nové kolo a čas v logu; 3 chyby = řešení a nové kolo',
    $wr['ok'] && !$wr['result']['ok'] && $st['w']['chmod'] === ['r' => 1, 't' => 1, 'ok' => 0] && isset($st['rep']['lnx_chmod']) && $wo['result']['ok'] && $st['w']['color']['r'] === 2 && $st['log']['w_ok_at'] === [$t0 + 300]
    && (static function () use ($key, $t0): bool { wg80_submit('class_3a', $key, 'chmod', ['p' => ['ur']], $t0 + 210); $x = wg80_submit('class_3a', $key, 'chmod', ['p' => ['ur']], $t0 + 220); return $x['shown'] && lr80_state('class_3a', lr80_ref('class_3a', $key))['w']['chmod'] === ['r' => 2, 't' => 0, 'ok' => 0]; })());
$ga = pg80_goal_add('class_3a', $key, 'widgets', 2, $t0 + 400);
$gd = pg80_goal_add('class_3a', $key, 'widgets', 4, $t0 + 410);
$gx = pg80_goal_add('class_3a', $key, 'tasks', 7, $t0 + 420);
pg80_goal_add('class_3a', $key, 'tasks', 3, $t0 + 430);
pg80_goal_add('class_3a', $key, 'review', 1, $t0 + 440);
$gl = pg80_goal_add('class_3a', $key, 'nohint', 1, $t0 + 450);
$st = lr80_state('class_3a', $ref);
$goals = pg80_goals_week($st, $t0 + 500);
$check('cíle: druh a číslo jen z nabídky, max. 3 na týden, jeden od druhu; plnění z dat týdne (cvičení 1 z 2, opakování 1 z 1 → done_at při zápisu); žádný volný text',
    $ga['ok'] && $gd['error'] === 'duplicate' && $gx['error'] === 'kind' && $gl['error'] === 'limit' && count($goals) === 3 && array_column($goals, 'k') === ['widgets', 'tasks', 'review']
    && pg80_goal_progress($goals[0], $st, tb75_student_state('class_3a', $ref)) === 1 && (int)$goals[2]['done_at'] > 0 && (int)$goals[0]['done_at'] === 0
    && array_keys($goals[0]) === ['id', 'k', 'n', 'wk', 'at', 'done_at'] && preg_match('/^g_[0-9a-f]{8}$/', (string)$goals[0]['id']) === 1);
$check('cíle: odebrání vlastního cíle; cizí / neplatné id nic nesmaže', pg80_goal_remove('class_3a', $key, (string)$goals[1]['id'], $t0 + 600) && !pg80_goal_remove('class_3a', $key, 'g_00000000', $t0 + 600)
    && !pg80_goal_remove('class_3a', $key2, (string)$goals[0]['id'], $t0 + 600) && count(pg80_goals_week(lr80_state('class_3a', $ref), $t0 + 700)) === 2);
$tbFake = ['net_mask' => ['round' => 3, 'tries' => 1, 'solved' => 2, 'last_ok' => true, 'log' => [['r' => 1, 'ok' => 0, 'at' => $t0 - 9 * 86400], ['r' => 1, 'ok' => 0, 'at' => $t0 - 9 * 86400 + 5], ['r' => 1, 'ok' => 1, 'at' => $t0 - 8 * 86400],
    ['r' => 2, 'ok' => 1, 'at' => $t0 - 86400], ['r' => 3, 'ok' => 1, 'at' => $t0 - 3600], ['r' => 4, 'ok' => 1, 'at' => $t0 - 60]]]];
$sFake = lr80_normalize(['hint' => ['net_mask' => ['r' => 1, 'n' => 2, 'c' => 2, 'u' => [1]]], 'log' => ['rep_ok_at' => [$t0], 'rep_done' => 1], 'goals' => [['id' => 'g_1', 'k' => 'tasks', 'n' => 3, 'wk' => 'x', 'at' => $t0, 'done_at' => $t0]]]);
$badges = pg80_badges($sFake, $tbFake, $t0);
$none = pg80_badges(lr80_normalize([]), [], $t0);
$check('odznaky za zlepšení (bez pořadí): opravená chyba, celá řada 1-3-7, lepší úspěšnost než minulý týden (≥ 3 pokusy), vyřešeno bez nápovědy po kole s nápovědou, splněný cíl; nový žák nemá žádný',
    array_keys($badges) === PG80_BADGES && count(array_filter(array_column($badges, 'earned'))) === 5 && array_filter(array_column($none, 'earned')) === []);
$data = lr80_data('class_3a');
$row = pg80_teacher_row('class_3a', $ref, $data, tb75_data(), $t0 + 800);
$check('souhrn pro učitele: jen čísla (k opakování, ve frontě, nápovědy, cvičení, cíle, odznaky) – žádné odpovědi ani texty žáka',
    $row['hints'] === 4 && $row['queued'] === 2 && $row['widgets'] === 1 && count($row['goals']) === 2 && is_int($row['badges']) && !preg_match('/10\.9\.9\.9|nevím/u', (string)json_encode($row, JSON_UNESCAPED_UNICODE)));
lr80_update('class_3a', 'hold_0000', static fn(array $s): array => $s, $t0 - 400 * 86400);
lr80_update('class_3a', 'hshort', static fn(array $s): array => $s, $t0 + 900);
$dry = lr80_retention_purge([$ref], true);
$keep = lr80_data('class_3a');
$gone = lr80_retention_purge([$ref], false);
$after = lr80_data('class_3a');
$check('retence: žák bez aktivity přes ' . LR80_RETENTION_DAYS . ' dní se smaže při zápisu třídy; po odchodu (r76 → lr80_retention_purge) dry-run jen spočítá, apply smaže jen jeho záznam; retention_v76 vrací počet „learning“',
    !isset($keep['students']['hold_0000']) && $dry === 1 && isset($keep['students'][$ref]) && $gone === 1 && !isset($after['students'][$ref]) && isset($after['students']['hshort'])
    && str_contains($read('retention_v76.php'), "\$res['learning'] = lr80_retention_purge(") && str_contains($read('tools/v58_retention.php'), 'v80_data_purged=')
    && (static function (): bool { $r = r76_retention_purge(true); return array_key_exists('learning', $r); })());
$h = pg80_exit_rows('class_3a', $key);
storage_update(STORAGE_DIR . '/lesson_exit_v72_class_3a.json.php', static fn(array $d): array => ['v' => 1, 'answers' => ['3' => [substr(hash('sha256', 'lx72|' . $key), 0, 20) => ['v' => 0, 'c' => 1, 'ok' => 0, 'at' => 'x']], '2' => [substr(hash('sha256', 'lx72|' . $key), 0, 20) => ['ok' => 1]]]]);
require_once $ROOT . '/lesson_exit_v72.php';
$check('exit tickety v Můj pokrok: čte jen soubor v72 se stejným hashem žáka jako lx72_student_hash (bez modelu lekcí), lekce seřazené, správnost bez obsahu odpovědi',
    $h === [] && pg80_exit_rows('class_3a', $key) === [2 => true, 3 => false] && lx72_student_hash($key) === substr(hash('sha256', 'lx72|' . $key), 0, 20) && pg80_exit_rows('../x', $key) === []);
