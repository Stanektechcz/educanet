<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · druhá část tools/v75_learning_audit.php (načítá se z něj; používá $check, $throws, $tmp, $modules, $ROOT):
 * banka úloh (nezávislý přepočet klíčů), úrovně v hodině, portfolio, opakování před písemkou a výkon knihovny (historie zvlášť).
 */

// ---------------------------------------------------------------- 3) banka úloh s variantami
$tasks = array_keys(TB75_TASKS);
$sA = 'stu_0123456789abcdef';
$sB = 'stu_fedcba9876543210';
$same = array_reduce($tasks, static fn(bool $ok, string $t): bool => $ok && tb75_variant($t, $sA, 1) === tb75_variant($t, $sA, 1), true);
$diffStudents = count(array_filter($tasks, static fn(string $t): bool => array_column(tb75_variant($t, $sA, 1)['fields'], 'answer') !== array_column(tb75_variant($t, $sB, 1)['fields'], 'answer') || tb75_variant($t, $sA, 1)['prompt'] !== tb75_variant($t, $sB, 1)['prompt']));
$diffRounds = count(array_filter($tasks, static fn(string $t): bool => tb75_variant($t, $sA, 1) !== tb75_variant($t, $sA, 2)));
$check('varianty: deterministické (stejný žák a kolo = stejné zadání), jiný žák ' . $diffStudents . '/7 a jiné kolo ' . $diffRounds . '/7 = jiné zadání', $same && $diffStudents >= 6 && $diffRounds >= 6);
$wrong = [];
$ports = ['HTTP' => 80, 'HTTPS' => 443, 'SSH' => 22, 'DNS' => 53, 'SMTP' => 25, 'IMAP' => 143, 'POP3' => 110, 'FTP (řízení)' => 21, 'Telnet' => 23, 'RDP' => 3389, 'MySQL' => 3306,
    'PostgreSQL' => 5432, 'NTP' => 123, 'SNMP' => 161, 'LDAP' => 389, 'DHCP (server)' => 67];
for ($i = 0; $i < 150; $i++) {
    $seed = 'stu_' . substr(hash('sha256', 'seed' . $i), 0, 16);
    $v = tb75_variant('net_subnet', $seed, 1 + $i % 3);
    preg_match('~(\d+\.\d+\.\d+\.\d+)/(\d+)~', $v['prompt'], $mm);
    $long = ip2long($mm[1]);
    $mask = (int)$mm[2] === 0 ? 0 : (-1 << (32 - (int)$mm[2])) & 0xFFFFFFFF;
    $key = array_column($v['fields'], 'answer', 'id');
    if ($key['network'] !== long2ip($long & $mask) || $key['broadcast'] !== long2ip(($long & $mask) | (~$mask & 0xFFFFFFFF)) || (int)$key['hosts'] !== (1 << (32 - (int)$mm[2])) - 2
        || preg_match('/^(10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)/', $mm[1]) !== 1) $wrong[] = 'subnet:' . $i;
    $v = tb75_variant('net_mask', $seed, 1);
    foreach ($v['fields'] as $f) {
        preg_match('~/(\d+)|masku ([\d.]+)~u', $f['label'], $lm);
        if ($f['id'] === 'mask' && $f['answer'] !== long2ip(((int)$lm[1] === 0 ? 0 : (-1 << (32 - (int)$lm[1])) & 0xFFFFFFFF))) $wrong[] = 'mask:' . $i;
        if ($f['id'] === 'prefix' && (int)$f['answer'] !== substr_count(decbin(ip2long($lm[2])), '1')) $wrong[] = 'prefix:' . $i;
    }
    foreach (tb75_variant('net_ports', $seed, 1)['fields'] as $f) if ((int)$f['answer'] !== ($ports[substr($f['label'], strlen('Port služby '))] ?? -1)) $wrong[] = 'ports:' . $i;
    $v = tb75_variant('net_route', $seed, 1);
    $routes = array_map(static fn(string $l): array => preg_split('/\s+via\s+/', trim($l)), $v['data']);
    foreach ($v['fields'] as $f) {
        $dest = substr($f['label'], strlen('Další skok pro '));
        $best = -1; $hop = '';
        foreach ($routes as [$net, $via]) { [$a, $len] = explode('/', $net); $mk = (int)$len === 0 ? 0 : (-1 << (32 - (int)$len)) & 0xFFFFFFFF; if ((ip2long($dest) & $mk) === (ip2long($a) & $mk) && (int)$len > $best) { $best = (int)$len; $hop = $via; } }
        if ($f['answer'] !== $hop || !in_array($hop, (array)$f['options'], true)) $wrong[] = 'route:' . $i;
    }
    $v = tb75_variant('lnx_path', $seed, 1);
    preg_match('~složce (\S+) \(uživatel (\w+)\)~u', $v['prompt'], $pm);
    $cwd = explode('/', trim($pm[1], '/'));
    foreach ($v['data'] as $line) {
        $arg = substr($line, strlen('$ cd '));
        if ($arg === '~') { $cwd = ['home', $pm[2]]; continue; }
        if ($arg[0] === '/') $cwd = [];
        foreach (explode('/', $arg) as $seg) { if ($seg === '..') array_pop($cwd); elseif ($seg !== '' && $seg !== '.') $cwd[] = $seg; }
    }
    if ($v['fields'][0]['answer'] !== '/' . implode('/', $cwd)) $wrong[] = 'path:' . $i;
    $v = tb75_variant('lnx_chmod', $seed, 1);
    $sym = static function (string $oct): string { $o = ''; foreach (str_split($oct) as $d) { $d = (int)$d; $o .= ($d & 4 ? 'r' : '-') . ($d & 2 ? 'w' : '-') . ($d & 1 ? 'x' : '-'); } return $o; };
    $num = static function (string $s): string { $o = ''; foreach (str_split($s, 3) as $t) $o .= (string)(($t[0] === 'r' ? 4 : 0) + ($t[1] === 'w' ? 2 : 0) + ($t[2] === 'x' ? 1 : 0)); return $o; };
    $key = array_column($v['fields'], 'answer', 'id');
    $labels = array_column($v['fields'], 'label', 'id');
    preg_match('~má ([rwx-]{9}), pak chmod ([ugo])([+-])([rwx])~', $labels['after'], $cm);
    $bits = str_split($cm[1]);
    $pos = ['u' => 0, 'g' => 3, 'o' => 6][$cm[2]] + ['r' => 0, 'w' => 1, 'x' => 2][$cm[4]];
    $bits[$pos] = $cm[3] === '+' ? $cm[4] : '-';
    if ($key['oct'] !== $num(substr($labels['oct'], -9)) || $key['sym'] !== $sym(substr($labels['sym'], -3)) || $key['after'] !== implode('', $bits)) $wrong[] = 'chmod:' . $i;
    $v = tb75_variant('lnx_grep', $seed, 1);
    foreach ($v['fields'] as $f) {
        preg_match('~grep (-[civ]) "([^"]+)"~', $f['label'], $gm);
        $flags = PREG_GREP_INVERT * (int)($gm[1] === '-v');
        $hits = preg_grep('/' . preg_quote($gm[2], '/') . '/' . ($gm[1] === '-i' ? 'i' : ''), $v['data'], $flags);
        if ((int)$f['answer'] !== count($hits)) $wrong[] = 'grep:' . $i;
    }
}
$check('klíče variant: 150 semínek × 7 úloh přepočítaných nezávisle (ip2long, bitová maska, IANA porty, nejdelší prefix, zásobník cest, oktály, preg_grep); jen privátní adresy' . ($wrong ? ' [' . implode(', ', array_slice($wrong, 0, 6)) . ']' : ''), $wrong === []);
$v = tb75_variant('lnx_chmod', $sA, 1);
$ok = tb75_check($v, array_column($v['fields'], 'answer', 'id'));
$alt = array_column($v['fields'], 'answer', 'id');
$alt['oct'] = '0' . $alt['oct'];
$vp = tb75_variant('lnx_path', $sA, 1);
$check('kontrola odpovědí: klíč projde, „0644“ i koncové lomítko u cesty a mezery se tolerují, prázdné pole a chyba neprojdou', $ok['ok'] && tb75_check($v, $alt)['ok']
    && tb75_check($vp, ['pwd' => ' ' . $vp['fields'][0]['answer'] . '/ '])['ok'] && !tb75_check($v, ['oct' => $alt['oct']])['ok'] && !tb75_check($vp, ['pwd' => '/nikde'])['ok']);
$C3 = 'class_3a';
$keys3 = array_keys(project_students_for_class($C3));
$stuA = (string)($keys3[0] ?? 'class_3a:student:audit-a');
$stuB = (string)($keys3[1] ?? 'class_3a:student:audit-b');
$refA = tb75_student_ref($C3, $stuA);
$check('identita: ref žáka = student_id nebo pseudonym (nikdy jméno), různí žáci = různé ref', preg_match('/^(stu_[0-9a-f]{16}|h[0-9a-f]{24})$/', $refA) === 1 && $refA !== tb75_student_ref($C3, $stuB)
    && !in_array('net_subnet', tb75_tasks_for_class('class_1a'), true) && tb75_submit('class_1a', 'x', 'net_subnet', ['network' => '1'])['error'] === 'unknown_task');   // v76: 1.A má grafické úlohy (změna limitu auditu)
$cur = tb75_current($C3, $refA, 'net_subnet')['variant'];
$wrongAns = ['network' => 'tajny-vstup-zaka', 'broadcast' => '1.1.1.1', 'hosts' => '1'];
$r1 = tb75_submit($C3, $stuA, 'net_subnet', $wrongAns);
$r2 = tb75_submit($C3, $stuA, 'net_subnet', $wrongAns);
$r3 = tb75_submit($C3, $stuA, 'net_subnet', $wrongAns);
$r4 = tb75_submit($C3, $stuA, 'net_subnet', array_column($cur['fields'], 'answer', 'id'));
$raw = (string)file_get_contents(tb75_path());
$check('tok: 3 chybné pokusy → řešení ukázáno, další odevzdání stejné varianty odmítnuto (shown); odpovědi se neukládají (jen výsledek po polích)', $r1['ok'] && !$r1['result']['ok'] && $r3['entry']['shown']
    && $r4['error'] === 'shown' && !str_contains($raw, 'tajny-vstup-zaka') && tb75_submit($C3, $stuA, 'net_subnet', [])['error'] === 'empty');
$next = tb75_next($C3, $stuA, 'net_subnet');
$cur2 = tb75_current($C3, $refA, 'net_subnet');
$r5 = tb75_submit($C3, $stuA, 'net_subnet', array_column($cur2['variant']['fields'], 'answer', 'id'));
$check('tok: nová varianta (kolo 2) má jiná čísla, správná odpověď = vyřešeno, další odevzdání odmítnuto (solved); bez vyřešení nová varianta nejde', $next && $cur2['entry']['round'] === 2
    && $cur2['variant']['prompt'] !== $cur['prompt'] && $r5['ok'] && $r5['result']['ok'] && $r5['entry']['solved'] === 1 && tb75_submit($C3, $stuA, 'net_subnet', ['network' => 'x'])['error'] === 'solved'
    && !tb75_next($C3, $stuA, 'net_mask'));
$st = tb75_class_stats($C3);
$check('souhrn třídy: 4 pokusy, 1 správně (25 %), nejčastější chyba po polích; úspěšnost žáka za 14 dní', $st['net_subnet']['attempts'] === 4 && $st['net_subnet']['ok'] === 1 && $st['net_subnet']['rate'] === 25
    && ($st['net_subnet']['fields']['network']['wrong'] ?? 0) === 3 && tb75_student_recent($C3, $stuA) === ['attempts' => 4, 'ok' => 1] && $st['lnx_grep']['attempts'] === 0);

// ---------------------------------------------------------------- 4) úrovně v hodině
$lvBad = [];
foreach (array_keys(LV75_CLASS_FILES) as $c) {
    $content = lv75_content($c);
    if (array_slice(array_keys($content), 0, 16) !== range(1, 16)) $lvBad[] = $c . ':lekce';   // v76: L17–L28 hlídá tools/v76_content_audit.php
    foreach (array_slice($content, 0, 16, true) as $n => $row) {
        $texts = [];
        if (trim((string)($row['activity'] ?? '')) === '') $lvBad[] = $c . ':L' . $n;
        foreach (array_keys(LV75_LEVELS) as $k) {
            if (trim((string)($row[$k]['task'] ?? '')) === '' || trim((string)($row[$k]['check'] ?? '')) === '') $lvBad[] = $c . ':L' . $n . ':' . $k;
            $texts[] = (string)($row[$k]['task'] ?? '');
        }
        if (count(array_unique($texts)) !== 3) $lvBad[] = $c . ':L' . $n . ':shodné';
    }
}
$check('úrovně: L1–L16 všech 4 tříd = 64 lekcí × Základ / Standard / Výzva (zadání + „hotovo, když“), úrovně se liší' . ($lvBad ? ' [' . implode(', ', array_slice($lvBad, 0, 5)) . ']' : ''), $lvBad === []);
$check('úrovně: bez schválení je žák nevidí a volba se neuloží; vrácení bez poznámky výjimka', lv75_student_levels($C3, 3) === null && !lv75_pick($C3, $stuA, 3, 'vyzva') && !is_file(lv75_picks_path())
    && $throws(static fn() => lv75_decide('lv75_return', $C3, ['3'], '', 'T'), 'poznámkou') && $throws(static fn() => lv75_decide('lv75_approve', $C3, ['40'], '', 'T')));
$n = lv75_decide('lv75_approve', $C3, array_map('strval', range(1, 16)), '', 'Audit Učitel');
$check('úrovně: hromadné schválení 16 lekcí → žák vidí volbu; změněný obsah = „změněno od schválení“ a žák ho nevidí', $n === 16 && lv75_student_levels($C3, 3) !== null
    && lv75_status($C3, 3, ['classes' => [$C3 => ['3' => ['status' => 'schvaleno', 'hash' => 'xxxxxxxxxxxx']]]])['status'] === 'zmeneno' && lv75_status($C3, 3)['status'] === 'schvaleno');
$sug = static fn(array $e, int $att, int $ok): string => lv75_suggest_from($e, ['attempts' => $att, 'ok' => $ok])['level'];
$check('doporučení: bez dat Standard (data=false); 0/2 exit → Základ; 3/3 → Výzva; 1/2 → Standard; úlohy 40 % → Základ; jen úlohy 100 % → Výzva; 1 exit bez úloh → Standard',
    $sug([], 0, 0) === 'standard' && lv75_suggest_from([], ['attempts' => 0, 'ok' => 0])['data'] === false && $sug([false, false], 0, 0) === 'zaklad' && $sug([true, true, true], 0, 0) === 'vyzva'
    && $sug([true, false], 0, 0) === 'standard' && $sug([true, true], 10, 4) === 'zaklad' && $sug([], 5, 5) === 'vyzva' && $sug([true], 0, 0) === 'standard');
$pickA = lv75_pick($C3, $stuA, 3, 'zaklad');
$pickB = lv75_pick($C3, $stuB, 3, 'standard');
lv75_pick($C3, $stuB, 3, 'vyzva');
$dist = lv75_distribution($C3);
$check('volba a rozložení: 2 žáci, změna volby přepíše (bez duplicit), počty po úrovních, „podle doporučení“; neplatná úroveň odmítnuta; soubor bez jmen', $pickA && $pickB
    && ($dist[3] ?? []) === ['zaklad' => 1, 'standard' => 0, 'vyzva' => 1, 'total' => 2, 'followed' => (int)(lv75_suggest($C3, $stuA, 3)['level'] === 'zaklad') + (int)(lv75_suggest($C3, $stuB, 3)['level'] === 'vyzva')]
    && !lv75_pick($C3, $stuA, 3, 'expert') && !str_contains((string)file_get_contents(lv75_picks_path()), $stuA));

// ---------------------------------------------------------------- 5) portfolio
$pin = static fn(array $in, string $who = '') => pf75_pin($C3, $who !== '' ? $who : $stuA, $in);
$okLink = $pin(['kind' => 'link', 'title' => '<b>Můj web</b>', 'url' => 'https://example.org/muj-web', 'reflection' => "Naučil jsem se <script>x</script> grid.\x07"]);
$errs = [$pin(['kind' => 'link', 'title' => 'X', 'url' => 'http://example.org/'])['error'], $pin(['kind' => 'link', 'title' => 'X', 'url' => 'javascript:alert(1)'])['error'],
    $pin(['kind' => 'link', 'title' => 'X', 'url' => 'https://user:pw@example.org/'])['error'], $pin(['kind' => 'link', 'title' => '', 'url' => 'https://example.org/b'])['error'],
    $pin(['kind' => 'link', 'title' => 'X', 'url' => 'https://example.org/muj-web'])['error'], $pin(['kind' => 'task', 'task' => 'net_mask'])['error'], $pin(['kind' => 'x'])['error']];
$pins = pf75_pins($C3, $stuA);
$first = reset($pins);
$check('portfolio: odkaz https se uloží (název a reflexe bez značek a řídicích znaků); http, javascript:, přihlašovací údaje, prázdný název, duplicita, nevyřešená úloha a neznámý druh odmítnuty',
    $okLink['ok'] && $errs === ['url', 'url', 'url', 'title', 'duplicate', 'task', 'kind'] && count($pins) === 1 && $first['title'] === 'Můj web' && !str_contains($first['reflection'], '<') && !str_contains($first['reflection'], "\x07"));
$okTask = $pin(['kind' => 'task', 'task' => 'net_subnet', 'reflection' => 'Podsítě']);
$check('portfolio: vyřešená úloha jde připnout (název z banky, počet vyřešení); soubor žáka v dočasném úložišti; jiný žák nic nevidí', $okTask['ok'] && count(pf75_pins($C3, $stuA)) === 2
    && str_contains((string)pf75_pins($C3, $stuA)[$okTask['id']]['title'], 'vyřešeno 1×') && str_starts_with(pf75_path($refA), $tmp . '/portfolio_v75/') && pf75_pins($C3, $stuB) === []);
$check('portfolio: komentář učitele (bez značek), smazání prázdným textem, reflexe a odebrání jen vlastní práce; neplatné id odmítnuto', pf75_comment($C3, $stuA, (string)$okTask['id'], '<i>Výborně</i>', 'Audit Učitel')
    && pf75_pins($C3, $stuA)[$okTask['id']]['comment']['text'] === 'Výborně' && pf75_update($C3, $stuA, (string)$okTask['id'], 'Nová reflexe') && !pf75_update($C3, $stuB, (string)$okTask['id'], 'cizí')
    && !pf75_comment($C3, $stuA, '../x', 'x', 'T') && pf75_update($C3, $stuA, (string)$okTask['id'], null) && count(pf75_pins($C3, $stuA)) === 1);
for ($i = 0; $i < PF75_MAX + 2; $i++) $last = $pin(['kind' => 'link', 'title' => 'Práce ' . $i, 'url' => 'https://example.org/p' . $i], $stuB);
$check('portfolio: strop ' . PF75_MAX . ' prací na žáka (další = full)', count(pf75_pins($C3, $stuB)) === PF75_MAX && $last['error'] === 'full');

// ---------------------------------------------------------------- 6) opakování před písemkou
$ticketLessons = [];
for ($l = 5; $l <= 16 && count($ticketLessons) < 2; $l++) if (lx72_normalize(lc72_overlay($C3, $l)) !== null) $ticketLessons[] = $l;
$answers = [];
foreach ($ticketLessons as $j => $l) for ($k = 0; $k < 6; $k++) $answers[(string)$l]['h' . $j . $k] = ['v' => 0, 'c' => 1, 'ok' => $k < ($j === 0 ? 1 : 5) ? 1 : 0, 'at' => time(), 'm' => 'f'];
storage_update(lx72_path($C3), static fn(array $d): array => ['v' => 1, 'answers' => $answers]);
$pack = rv75_build($C3, (array)($modules[$C3] ?? []), 5, 16);
$check('opakování: z odpovědí exit ticketů vybere otázky s ≥ 30 % chyb (L' . ($ticketLessons[0] ?? '?') . ' 5 z 6 špatně ano, L' . ($ticketLessons[1] ?? '?') . ' 1 z 6 ne), seřazené podle chybovosti, se správnou odpovědí',
    count($ticketLessons) === 2 && $pack['answers'] === 12 && count($pack['exits']) === 1 && $pack['exits'][0]['lesson'] === $ticketLessons[0] && $pack['exits'][0]['pct'] === 83 && isset($pack['exits'][0]['options'][$pack['exits'][0]['correct']]));
$taskIds = array_column($pack['tasks'], 'task');
$check('opakování: úlohy k tématům rozsahu (3.A L5–L16: podsítě, porty, cesty, chmod, grep…), slabé a nezkoušené první, žádná úloha mimo témata', $taskIds !== [] && in_array('net_subnet', $taskIds, true)
    && array_diff($taskIds, tb75_tasks_for_class($C3)) === [] && rv75_build('class_1a', (array)($modules['class_1a'] ?? []), 1, 4)['tasks'] === [] && rv75_build($C3, [], 30, 2)['from'] === 28);
rv75_publish($C3, $pack, 'Audit Učitel');
$pub = rv75_pack($C3);
$check('opakování: zveřejnění uloží snímek (rozsah, otázky, úlohy, bez jmen), skrytí ho schová; 2.A bez balíčku', is_array($pub) && $pub['from'] === 5 && $pub['tasks'] === $taskIds && rv75_pack('class_2a') === null
    && !preg_match('/class_3a:student|@/', (string)file_get_contents(rv75_path())) && rv75_close($C3) && rv75_pack($C3) === null && !rv75_close('class_2a'));

// ---------------------------------------------------------------- 7) výkon: knihovna materiálů (historie zvlášť), karta „Komu dnes pomoct“
$in = ['type' => 'video', 'title' => 'Výkon v75', 'url' => 'https://example.org/v75', 'lang' => 'CZ', 'topics' => 'dns', 'state' => 'aktivni'];
$item = mat74_add($C3, $in, 'Audit');
mat74_update($C3, (string)$item['id'], ['title' => 'Výkon v75 b'] + $in, 1, 'Audit');
$main = storage_read(mat74_path(), false);
$check('knihovna: zápis ukládá historii do materials_v74_history.json.php, hlavní soubor bez klíče history; historie 2 verze', !isset($main['history']) && count(mat74_history((string)$item['id'])) === 2
    && is_file(mat74_history_path()) && count((array)(storage_read(mat74_history_path(), false)['history'][(string)$item['id']] ?? [])) === 2);
storage_update_many([mat74_path(), mat74_history_path()], static function (array $all): array {
    $legacy = $all[mat74_path()];
    $legacy['history'] = $all[mat74_history_path()]['history'];
    return [mat74_path() => $legacy, mat74_history_path() => ['v' => 1, 'history' => []]];
});
$mig = require $ROOT . '/migrations/0005_materials_history_v75.php';
$legacyRead = count(mat74_history((string)$item['id']));
$dry = $mig['up'](true);
$sizeBefore = filesize(mat74_path());
$up = $mig['up'](false);
clearstatcache();
$again = $mig['up'](false);
$check('migrace 0005: stará data (historie v hlavním souboru) se čtou dál, náhled nic nezmění, provedení přesune historii (hlavní soubor menší), opakování nic nezmění', $legacyRead === 2 && $dry['changed'] === 0
    && $up['changed'] === 1 && !isset(storage_read(mat74_path(), false)['history']) && filesize(mat74_path()) < $sizeBefore && $again['changed'] === 0 && count(mat74_history((string)$item['id'])) === 2);
$down = $mig['down'](false);
$check('migrace 0005: rollback vrátí historii do hlavního souboru (stav v74) beze ztráty', count((array)(storage_read(mat74_path(), false)['history'][(string)$item['id']] ?? [])) === 2 && count(mat74_history((string)$item['id'])) === 2);
$mig['up'](false);
$shapeOk = true;
foreach ([[$C3, 4], [$C3, 9], ['class_1a', 7], ['class_4a', 12]] as [$c, $nn]) {
    $l = lt70_lesson($c, (array)($modules[$c] ?? []), $nn);
    $s = in74_lesson_shape($c, (array)($modules[$c] ?? []), $nn);
    if (array_column($l['topics'], 'key') !== array_column($s['topics'], 'key') || count($l['steps']) !== count($s['steps'])) $shapeOk = false;
}
$t0 = hrtime(true);
for ($i = 0; $i < 20; $i++) in74_lesson_shape($C3, (array)($modules[$C3] ?? []), 5);
$tShape = (hrtime(true) - $t0) / 1e6;
$t0 = hrtime(true);
for ($i = 0; $i < 20; $i++) lt70_lesson($C3, (array)($modules[$C3] ?? []), 5);
$tFull = (hrtime(true) - $t0) / 1e6;
$check('výkon „Komu dnes pomoct“: trend bere témata a kroky z balíčku lekce – shodné s modelem lekce, rychlejší (' . round($tShape, 2) . ' ms vs ' . round($tFull, 2) . ' ms / 20×)', $shapeOk && $tShape < $tFull);
