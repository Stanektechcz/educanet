<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.1 · část 2 auditu (čisté funkce a úložiště nad dočasným úložištěm; používá $check, $tmp, $ROOT).
 * Fixture: tools/lib/v80_1_fixture.php (fiktivní žáci ze student_directory.php, vzor podle pořadí žáka a sloupce).
 */

$now = time();
$C = 'class_3a';
$module = is_array($GLOBALS['modules'][$C] ?? null) ? $GLOBALS['modules'][$C] : [];
$fx = v801_fx_seed($C, $now);
unset($GLOBALS['educanet_json_request_cache']);
$check('fixture: 3.A má aspoň 6 fiktivních žáků se stabilní identitou (student_id) a úlohy s variantami', count($fx) >= 6 && array_filter($fx, static fn(array $s): bool => $s['sid'] === '') === []);

// ---------------------------------------------------------------- mapa: správnost na fixture
$mk = cm801_build($C, $module, []);
$compIds = array_keys(comp62_competencies((string)comp62_subject_for_class($C)));
$wrong = [];
foreach ($fx as $s) foreach ($compIds as $j => $col) {
    $cell = $mk['cells'][$s['key']][$col] ?? null;
    $want = v801_fx_comp_level($s['i'], $j);
    if (!is_array($cell) || $cell['level'] !== $want || trim((string)$cell['text']) === '' || ($want === 'none') !== ($cell['value'] === null)) $wrong[] = $s['i'] . ':' . $col . '=' . ($cell['level'] ?? '?') . '/' . $want;
}
$check('mapa (kompetence): výchozí zdroj, ' . count($fx) . ' žáků × ' . count($compIds) . ' kompetencí, každá buňka má úroveň podle vzoru fixture a hodnotu textem' . ($wrong ? ' [' . implode(', ', array_slice($wrong, 0, 4)) . ']' : ''),
    $mk['state']['zdroj'] === 'komp' && $mk['sources'] === ['komp', 'ulohy'] && count($mk['students']) === count($fx) && count($mk['columns']) === count($compIds) && $wrong === []);
$sum = $mk['summary'][$compIds[0]] ?? [];
$exp = array_count_values(array_map(static fn(array $s): string => v801_fx_comp_level($s['i'], 0), $fx));
$check('mapa: souhrn sloupce za třídu = počty úrovní (umí / učí se / pomoc / bez dat)', array_sum($sum) === count($fx) && array_filter(array_keys(CM801_LEVELS), static fn(string $l): bool => (int)($sum[$l] ?? 0) !== (int)($exp[$l] ?? 0)) === []);
$mt = cm801_build($C, $module, ['zdroj' => 'ulohy']);
$tasks = tb75_tasks_for_class($C);
$wrongT = [];
foreach ($fx as $s) foreach ($tasks as $j => $t) {
    [$n, $ok] = v801_fx_task($s['i'], $j);
    $cell = $mt['cells'][$s['key']][$t] ?? null;
    if (!is_array($cell) || $cell['level'] !== v801_fx_task_level($s['i'], $j) || ($n > 0 && $cell['text'] !== $ok . ' z ' . $n . ' správně (' . (int)round($ok / $n * 100) . ' %)')) $wrongT[] = $s['i'] . ':' . $t;
}
$check('mapa (úlohy s variantami): každá buňka = správně / pokusy a úspěšnost v %, úroveň podle pravidel' . ($wrongT ? ' [' . implode(', ', array_slice($wrongT, 0, 4)) . ']' : ''), count($mt['columns']) === count($tasks) && $wrongT === []);
$col = $compIds[1];
$asc = cm801_build($C, $module, ['razeni' => $col, 'smer' => 'asc']);
$vals = array_map(static fn(array $s): ?int => $asc['cells'][$s['key']][$col]['value'], $asc['students']);
$known = array_values(array_filter($vals, static fn($v): bool => $v !== null));
$sorted = $known;
sort($sorted);
$firstNull = array_search(null, $vals, true);
$desc = cm801_build($C, $module, ['razeni' => 'prumer', 'smer' => 'desc']);
$avgs = array_values(array_filter(array_map(static fn(array $s): ?int => cm801_avg($desc['cells'][$s['key']]), $desc['students']), static fn($v): bool => $v !== null));
$check('řazení: podle sloupce vzestupně (nejslabší první, bez dat vždy na konci), podle průměru sestupně',
    $known === $sorted && ($firstNull === false || array_filter(array_slice($vals, (int)$firstNull), static fn($v): bool => $v !== null) === []) && $avgs === array_values(array_reverse(array_values((static function (array $a): array { sort($a); return $a; })($avgs)))));
$area = cm801_build($C, $module, ['oblast' => 'linux']);
$bad = cm801_build($C, $module, ['zdroj' => '<script>', 'oblast' => 'x', 'od' => '99', 'do' => 'abc', 'razeni' => 'drop', 'smer' => 'x', 'zobrazeni' => 'svg']);
$check('filtry: oblast Linux = jen sloupce Linuxu; neplatné hodnoty (zdroj, oblast, lekce, řazení, směr, zobrazení) = výchozí stav (whitelist)',
    $area['columns'] !== [] && array_filter($area['columns'], static fn(array $c): bool => $c['group'] !== 'linux') === [] && count($area['columns']) < count($compIds)
    && $bad['state'] === ['zdroj' => 'komp', 'oblast' => '', 'od' => 0, 'do' => 0, 'razeni' => 'jmeno', 'smer' => 'asc', 'zobrazeni' => 'mapa']);
$topics = cm801_lesson_topics($C, $module);
$range = cm801_build($C, $module, ['od' => '1', 'do' => '4']);
$tagOk = true;
foreach ($range['columns'] as $id => $c) {
    $tags = (array)comp62_competencies('os_site')[$id]['tags'];
    $hit = false;
    for ($n = 1; $n <= 4; $n++) foreach ((array)($topics[$n] ?? []) as $t) $hit = $hit || in_array('topic:' . $t, $tags, true);
    $tagOk = $tagOk && $hit;
}
$swap = cm801_build($C, $module, ['od' => '9', 'do' => '3']);
$check('filtr lekcí: rozsah 1–4 = jen kompetence, jejichž témata se v lekcích 1–4 probírají (podle tagů topic:); obrácený rozsah se prohodí',
    count($range['columns']) >= 1 && count($range['columns']) <= count($compIds) && $tagOk && $swap['state']['od'] === 3 && $swap['state']['do'] === 9);

// ---------------------------------------------------------------- zpětná vazba
$refs = [];
foreach (array_slice($fx, 0, 3) as $s) $refs[$s['key']] = $s['ref'];
[$kA, $kB] = array_keys($refs);
$check('výběr žáků: jen klíče ze seznamu třídy (cizí třída a neznámý klíč se zahodí), duplicity jednou',
    ts801_student_refs($C, [$kA, $kA, 'class_1a:student:000000000000000000000000', 'x', $kB]) === [$kA => $refs[$kA], $kB => $refs[$kB]]);
$err = static function (callable $fn): string { try { $fn(); return ''; } catch (RuntimeException $e) { return $e->getMessage(); } };
$n = ts801_feedback_send($C, [$kA => $refs[$kA], $kB => $refs[$kB]], 'praise', "  Skvělá práce\x07!\r\n\r\n\r\n\r\nPokračuj.  ", 'aaaaaaaaaaaaaaaa', $now);
$d = ts801_data($C);
$check('zpětná vazba: uloží se 2 zprávy (ref žáka, druh, text, čas, hash učitele) – řídicí znaky pryč, max. 2 prázdné řádky, ořez mezer',
    $n === 2 && count($d['fb']) === 2 && $d['fb'][0]['t'] === "Skvělá práce!\n\nPokračuj." && $d['fb'][0]['k'] === 'praise' && preg_match(TS801_ID_RE, $d['fb'][0]['id']) === 1 && array_keys($d['fb'][0]) === ['id', 'ref', 'k', 't', 'at', 'by']);
$check('zpětná vazba: neplatný druh, prázdný text a žádný žák → srozumitelná chyba, nic se nezapíše',
    $err(static fn() => ts801_feedback_send($C, [$kA => $refs[$kA]], 'xx', 'a', 'h', $now)) !== '' && $err(static fn() => ts801_feedback_send($C, [$kA => $refs[$kA]], 'next', " \x01 ", 'h', $now)) !== ''
    && $err(static fn() => ts801_feedback_send($C, [], 'next', 'a', 'h', $now)) !== '' && count(ts801_data($C)['fb']) === 2);
$long = ts801_feedback_send($C, [$kA => $refs[$kA]], 'next', str_repeat('á', 900), 'h', $now);
$fbA = array_values(array_filter(ts801_data($C)['fb'], static fn(array $r): bool => $r['ref'] === $refs[$kA]));
for ($i = 0; $i < TS801_FB_KEEP + 3; $i++) ts801_feedback_send($C, [$kB => $refs[$kB]], 'fix', 'Zpráva ' . $i, 'h', $now + $i);
$fbB = array_values(array_filter(ts801_data($C)['fb'], static fn(array $r): bool => $r['ref'] === $refs[$kB]));
$check('zpětná vazba: text oříznutý na ' . TS801_TEXT_MAX . ' znaků; na žáka nejvýš ' . TS801_FB_KEEP . ' posledních zpráv (nejstarší odpadají)',
    $long === 1 && mb_strlen((string)end($fbA)['t']) === TS801_TEXT_MAX && count($fbB) === TS801_FB_KEEP && end($fbB)['t'] === 'Zpráva ' . (TS801_FB_KEEP + 2));
$wid = (string)$fbA[0]['id'];
$check('stažení zprávy: smaže jen tu zprávu; neexistující nebo neplatné id = false', ts801_feedback_withdraw($C, $wid) && !ts801_feedback_withdraw($C, $wid) && !ts801_feedback_withdraw($C, '../x')
    && array_filter(ts801_data($C)['fb'], static fn(array $r): bool => $r['id'] === $wid) === []);

// ---------------------------------------------------------------- přidělení procvičování
$opts = ts801_assign_options($C);
$flat = array_merge(...array_values(array_map('array_keys', $opts)));
$check('nabídka přidělení: úlohy třídy, sady (Sítě, Linux), opakování (balíček + úlohy) a 4 cvičení; neplatná hodnota = null',
    in_array('task:net_subnet', $flat, true) && in_array('set:site', $flat, true) && in_array('set:linux', $flat, true) && in_array('rep:auto', $flat, true) && in_array('widget:chmod', $flat, true)
    && !in_array('task:gfx_color', $flat, true) && ts801_parse_item($C, 'task:gfx_color') === null && ts801_parse_item($C, 'task:net_subnet;x') === null && ts801_parse_item($C, 'set:nic') === null);
$fresh = static function () use ($C): void { unset($GLOBALS['lr80_memo'], $GLOBALS['ts801_memo'], $GLOBALS['educanet_json_request_cache']); };
$sA = $fx[0];
$status = static function (string $id) use ($C, $fresh): array {
    $fresh();
    foreach (ts801_data($C)['as'] as $a) if ($a['id'] === $id) return ts801_assignment_status($a, lr80_state($C, (string)$a['ref']), tb75_student_state($C, (string)$a['ref']));
    return [];
};
$last = static function () use ($C, $fresh): array { $fresh(); $all = ts801_data($C)['as']; return (array)end($all); };
$t0 = $now - 60;
$r1 = ts801_assign($C, [$sA['key'] => $sA['ref']], 'task:net_mask', 'h', $t0);
$aTask = $last();
$open = $status((string)$aTask['id']);
storage_update(tb75_path(), static function (array $d) use ($C, $sA, $now): array { $d['classes'][$C][$sA['ref']]['net_mask']['log'][] = ['r' => 9, 'ok' => 1, 'f' => [], 'at' => $now]; return $d; });
$done = $status((string)$aTask['id']);
$check('přidělení úlohy: čeká, dokud žák po přidělení nevyřeší kolo; po správném kole hotovo (počítá se z výsledků, nic navíc se nezapisuje); odkaz vede na úlohu',
    $r1 === ['assigned' => 1, 'skipped' => 0] && !$open['done'] && $done['done'] && $open['href'] === '?view=ulohy&uloha=net_mask');
ts801_assign($C, [$sA['key'] => $sA['ref']], 'set:linux', 'h', $t0);
$aSet = $last();
$setOpen = $status((string)$aSet['id']);
$check('přidělení sady: všechny úlohy oblasti, postup „N z M“, odkaz na první nesplněnou', $aSet['tasks'] === ts801_sets($C)['linux']['tasks'] && !$setOpen['done'] && $setOpen['total'] === count($aSet['tasks']) && $setOpen['href'] === '?view=ulohy&uloha=' . $setOpen['next']);
$fresh();
$okBefore = (int)((lr80_state($C, $sA['ref'])['w']['chmod'] ?? [])['ok'] ?? 0);
ts801_assign($C, [$sA['key'] => $sA['ref']], 'widget:chmod', 'h', $t0);
$aW = $last();
$wOpen = $status((string)$aW['id']);
lr80_update($C, $sA['ref'], static function (array $s): array { $s['w']['chmod'] = ['r' => 5, 't' => 0, 'ok' => (int)(($s['w']['chmod'] ?? [])['ok'] ?? 0) + 1]; return $s; }, $now);
$wDone = $status((string)$aW['id']);
$check('přidělení cvičení: výchozí stav uložen při přidělení (' . $okBefore . ' vyřešených), hotovo po dalším vyřešeném kole; odkaz na cvičení', (int)$aW['base']['ok'] === $okBefore && !$wOpen['done'] && $wDone['done'] && $wOpen['href'] === '?view=cviceni&w=chmod');
$sB = $fx[1];
$res = ts801_assign($C, [$sB['key'] => $sB['ref']], 'rep:auto', 'h', $now);
$aRep = $last();
$fresh();
$lrB = lr80_state($C, $sB['ref']);
$dueOk = array_filter((array)$aRep['tasks'], static fn(string $t): bool => (int)(($lrB['rep'][$t] ?? [])['due_at'] ?? PHP_INT_MAX) <= $now) === (array)$aRep['tasks'];
$repOpen = $status((string)$aRep['id']);
$tFirst = (string)$aRep['tasks'][0];
lr80_update($C, $sB['ref'], static function (array $s) use ($tFirst): array { $s['rep'][$tFirst]['c'] = (int)$s['rep'][$tFirst]['c'] + 1; return $s; }, $now);
$repHalf = $status((string)$aRep['id']);
$check('přidělení opakování: balíček ' . TS801_REP_AUTO . ' nejslabších úloh žáka zařazený do opakování v80 na dnešek (objeví se i v Opakování chyb); po zopakování úlohy postup +1',
    $res['assigned'] === 1 && count((array)$aRep['tasks']) === TS801_REP_AUTO && $dueOk && in_array($tFirst, lr80_rep_due($lrB, $now), true) && !$repOpen['done'] && $repHalf['have'] === $repOpen['have'] + 1 && $repOpen['href'] === '?view=opakovat');
for ($i = 0; $i < TS801_OPEN_MAX; $i++) ts801_assign($C, [$sB['key'] => $sB['ref']], 'widget:spec', 'h', $now);
$over = ts801_assign($C, [$sB['key'] => $sB['ref'], $sA['key'] => $sA['ref']], 'widget:color', 'h', $now);
$check('limit: žák s ' . TS801_OPEN_MAX . ' nesplněnými přiděleními se přeskočí (ostatní dostanou), neplatná položka = chyba', $over === ['assigned' => 1, 'skipped' => 1]
    && $err(static fn() => ts801_assign($C, [$sA['key'] => $sA['ref']], 'widget:x', 'h', $now)) !== '');
$cancelId = (string)$last()['id'];
$check('zrušení přidělení: smaže jen záznam přidělení', ts801_assign_cancel($C, $cancelId) && !ts801_assign_cancel($C, $cancelId) && array_filter(ts801_data($C)['as'], static fn(array $a): bool => $a['id'] === $cancelId) === []);

// ---------------------------------------------------------------- žák: pohled, Další krok
$fresh();
$view = ts801_student_view($C, $sA['key'], $now);
$next = ts801_next_step($view);
$viewC = ts801_student_view($C, $fx[2]['key'], $now);
$check('žák: vidí jen své zprávy a přidělení (nejstarší nesplněné = Další krok „Od učitele“ s odkazem); hotová přidělení zvlášť; jiný žák nic cizího',
    $view['open'] !== [] && $next !== null && $next['kind'] === 'teacher' && $next['href'] === (string)$view['open'][0]['status']['href'] && count($view['done']) >= 2
    && array_filter($view['feedback'], static fn(array $f): bool => $f['ref'] !== $sA['ref']) === [] && $viewC['open'] === [] && $viewC['feedback'] === []);
$html = ts801_section_html($view);
$check('žák: sekce „Od učitele“ – název přidělení, stav textem (čeká / hotovo), datum; text zprávy beze změny a escapovaný', str_contains($html, '<h2 id="ts801-h">Od učitele</h2>') && str_contains($html, 'čeká') && str_contains($html, 'hotovo') && str_contains($html, str_repeat('á', 20)) && str_contains($html, 'lang="cs"') === (edu_locale() !== 'cs'));

// ---------------------------------------------------------------- soukromí a retence
$raw = (string)file_get_contents(ts801_path($C));
$names = array_filter($fx, static fn(array $s): bool => str_contains($raw, $s['label']) || str_contains($raw, $s['key']));
$check('soukromí: úložiště podpory obsahuje jen ref žáka (student_id), žádná jména ani klíče žáků; hash učitele, ne jméno', $names === [] && !str_contains($raw, 'Audit') && str_contains($raw, $sA['ref']));
$form = in74_support_form_html($C, [['key' => $kA], ['key' => $kB]]);
$check('Komu dnes pomoct → Mapa třídy: POST ts801_select s klíči jen v těle formuláře (CSRF), ne v URL', str_contains($form, 'name="action" value="ts801_select"') && str_contains($form, 'name="csrf"') && substr_count($form, 'name="students[]"') === 2 && !str_contains($form, 'href='));
ts801_update($C, static function (array $d) use ($refs, $kA, $now): array { $d['fb'][] = ['id' => 'fb_000000000001', 'ref' => $refs[$kA], 'k' => 'next', 't' => 'stará', 'at' => $now - (TS801_RETENTION_DAYS + 1) * 86400, 'by' => 'h']; return $d; }, $now - (TS801_RETENTION_DAYS + 1) * 86400);
$hadOld = array_filter(ts801_data($C)['fb'], static fn(array $r): bool => $r['id'] === 'fb_000000000001') !== [];
ts801_feedback_send($C, [$kB => $refs[$kB]], 'next', 'nová', 'h', $now);
$check('retence: zprávy a přidělení starší než ' . TS801_RETENTION_DAYS . ' dní se smažou při dalším zápisu třídy', $hadOld && array_filter(ts801_data($C)['fb'], static fn(array $r): bool => $r['id'] === 'fb_000000000001') === []);
$before = count(array_filter(array_merge(ts801_data($C)['fb'], ts801_data($C)['as']), static fn(array $r): bool => $r['ref'] === $sB['ref']));
$dry = ts801_retention_purge([$sB['ref']], true);
$fresh();
$mid = count(array_filter(array_merge(ts801_data($C)['fb'], ts801_data($C)['as']), static fn(array $r): bool => $r['ref'] === $sB['ref']));
$apply = ts801_retention_purge([$sB['ref']], false);
$fresh();
$after = count(array_filter(array_merge(ts801_data($C)['fb'], ts801_data($C)['as']), static fn(array $r): bool => $r['ref'] === $sB['ref']));
$check('retence po odchodu žáka: náhled jen počítá (' . $dry . '), --apply smaže všechny záznamy žáka a ostatní zůstanou', $before > 0 && $dry === $before && $mid === $before && $apply === $before && $after === 0 && ts801_data($C)['fb'] !== []);
// úklid před HTTP částí: data části 2 pryč (HTTP začíná s čistou podporou, fixture mapy zůstává)
@unlink(ts801_path($C));
$fresh();
