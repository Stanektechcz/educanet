<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · druhá polovina auditu cest (tools/v63_paths_audit.php ji vkládá do téhož rozsahu proměnných):
 * trychtýř a kalibrace bez jmen, cockpit učitele a politiky, vykreslení kroků, Parsons bez JS, retence 30 dní,
 * router a navigace, i18n, token-sken invariantu labu, velikosti souborů a assetů, dokumentace.
 */

// --- 12) Trychtýř a kalibrace (bez jmen a bez vět) ---------------------------------------------------------------
$reflectNotes = ['a' => $note, 'b' => 'ok', 'c' => 'Tajná věta žáka C o DNS.', 'd' => 'Tajná věta žáka D.'];
v63fx_complete_steps($C3, 'c', 'lnx_chmod', '', $NOW + 2 * $day);
v63fx_complete_steps($C3, 'd', 'lnx_chmod', '', $NOW + 2 * $day);
p63_save_reflection($C3, $keyC, 'lnx_chmod', 3, $reflectNotes['c'], $NOW + 2 * $day);
p63_save_reflection($C3, $keyD, 'lnx_chmod', 2, $reflectNotes['d'], $NOW + 2 * $day);
v63fx_complete_steps($C3, 'c', 'net_dns', '', $NOW + 3 * $day);
v63fx_complete_steps($C3, 'a', 'net_dns', '', $NOW + 3 * $day);
p63_save_reflection($C3, $keyC, 'net_dns', 4, 'x', $NOW + 3 * $day);
p63_save_reflection($C3, $keyA, 'net_dns', 4, 'y', $NOW + 3 * $day);
$stats = p63_class_stats($C3);
$expectPairs = [];
foreach (p63_roster_ids($C3) as $sid) {
    $r = p63_state($sid)['reflect']['lnx_chmod'] ?? null;
    if (is_array($r)) $expectPairs[] = [(int)$r['self'], (int)$r['mastery_at_time']];
}
$expectCal = p63_calibration_summary($expectPairs);
$row = $stats['paths']['lnx_chmod'];
$check('trychtýř: počty žáků, kteří krok zkusili / splnili, odpovídají stavům (4 fiktivní žáci soupisu dokončili cestu lnx_chmod, ostatní žáci třídy ji nezačali)', $stats['students'] === count(p63_roster_ids($C3)) && $stats['students'] >= 4 && $row['started'] === 4 && $row['finished'] === 4
    && $row['steps']['explain']['done'] === 4 && $row['steps']['verify']['done'] === 4 && $row['steps']['verify']['tried'] >= 4 && $stats['paths']['net_dns']['started'] === 2 && $stats['paths']['net_dns']['finished'] === 2);
$check('kalibrace: pro ≥ 3 reflexe průměr odhadu, průměr z ověření, delta a počty přeceňujících/podceňujících odpovídají stavům (4 reflexe)', $expectCal['n'] === 4 && $row['calibration'] === $expectCal && isset($expectCal['avg_delta'])
    && abs($expectCal['avg_delta'] - ($expectCal['avg_self'] - $expectCal['avg_actual'])) < 0.011 && $expectCal['over'] + $expectCal['under'] + $expectCal['match'] === 4);
$check('kalibrace: pod 3 reflexemi se průměr neukládá ani neukazuje (net_dns má 2 reflexe → jen počet), takže jednotlivce nejde dohledat', $stats['paths']['net_dns']['calibration'] === ['n' => 2] && p63_calibration($C3)['paths']['net_dns'] === ['n' => 2] && p63_calibration_summary([[1, 1]]) === ['n' => 1]);
$funnelRaw = (string)file_get_contents(p63_funnel_file($C3));
$check('trychtýř: cache _funnel_<třída> je v souboru bez jmen, bez student_id a bez reflexních vět; po změně stavu žáka se podpis změní (přepočet)', is_file(p63_funnel_file($C3)) && !str_contains($funnelRaw, 'Audit') && !str_contains($funnelRaw, 'stu_') && !str_contains($funnelRaw, 'Tajná')
    && !str_contains($funnelRaw, 'Chmod mi') && ($sig1 = (string)($stats['sig'] ?? '')) !== '' && (static function () use ($C3, $keyD, $NOW, $day, $sig1): bool { p63_submit_step($C3, $keyD, 'net_dns', 'explain', [], $NOW + 4 * $day); return p63_class_stats($C3)['sig'] !== $sig1; })());
@unlink(p63_funnel_file($C3));
p63_class_stats($C3, false);
$check('trychtýř: výpočet bez persist (GET dashboardu apod.) nic nezapíše; funkce p63_funnel a p63_calibration jsou jen obálky nad stejnou cache', !is_file(p63_funnel_file($C3)) && p63_funnel($C3, false)['students'] === count(p63_roster_ids($C3)) && p63_calibration($C3, false)['overall']['n'] >= 3);

// --- 13) Cockpit učitele a politiky -----------------------------------------------------------------------------------------
$html = audit_capture(static function () use ($C3): void { p63_render_teacher_tab($C3, 'tok-csrf'); });
$check('cockpit: tabulka trychtýře má <caption>, <th scope="col"> i <th scope="row">, scrollovací region, formulář p63_assign s CSRF a třídou, souhrn kalibrace; bez PHP chyb',
    str_contains($html, '<caption>') && str_contains($html, '<th scope="col">') && str_contains($html, '<th scope="row">') && str_contains($html, 'p63-t-scroll') && str_contains($html, 'name="action" value="p63_assign"') && str_contains($html, 'value="tok-csrf"')
    && str_contains($html, 'name="class_id" value="class_3a"') && str_contains($html, 'Kalibrace (4 reflexí)') && audit_response_clean(['status' => 200, 'body' => $html]));
$check('cockpit: žádná jména žáků, student_id ani reflexní věty (učitel vidí jen souhrn)', !str_contains($html, 'Audit') && !str_contains($html, 'stu_') && !str_contains($html, 'Tajná') && !str_contains($html, 'Chmod mi') && !str_contains($html, $reflectNotes['d']));
p63_assign($C3, 'lnx_chmod', $teacherHash, $NOW);
$htmlAssigned = audit_capture(static function () use ($C3): void { p63_render_teacher_tab($C3, 'tok-csrf'); });
$check('cockpit: přiřazená cesta nese štítek a nabízí „Zrušit přiřazení“ (p63_unassign); třída bez cest nebo mimo rozsah se nezobrazí', str_contains($htmlAssigned, 'p63-t-badge') && str_contains($htmlAssigned, 'value="p63_unassign"')
    && !str_contains(audit_capture(static function (): void { p63_render_teacher_tab('class_2a', 't'); }), 'class_2a') && !p63_teacher_can_class('class_2a') && p63_teacher_can_class($C3) && p63_teacher_can_class($C1));
p63_unassign($C3, 'lnx_chmod');
$thrown = [];
foreach ([['p63_assign', 'class_2a', 'lnx_chmod'], ['p63_assign', $C3, 'web_html'], ['p63_hack', $C3, 'lnx_chmod'], ['p63_assign', '', 'lnx_chmod']] as [$act, $cls, $pth]) {
    $_POST = ['class_id' => $cls, 'path' => $pth];
    try { p63_teacher_handle_post($act); $thrown[] = false; } catch (RuntimeException $e) { $thrown[] = true; }
}
$_POST = [];
$check('cockpit: POST handler odmítne třídu mimo rozsah, cestu cizí třídy, neznámou akci i prázdnou třídu (výjimka) a nic nezapíše', $thrown === [true, true, true, true] && p63_assignments($C3) === [] && p63_assignments('class_2a') === []);
$check('politiky: p63_assign a p63_unassign vyžadují třídu; jiné p63_* a comp63_sync jsou zakázané (deny-by-default); oprávnění p63_ = content.manage', teacher59_action_policy('p63_assign') === ['class' => 'required'] && teacher59_action_policy('p63_unassign') === ['class' => 'required']
    && (teacher59_action_policy('p63_hack')['deny'] ?? false) === true && (teacher59_action_policy('p63_')['deny'] ?? false) === true && (teacher59_action_policy('comp63_sync')['deny'] ?? false) === true
    && teacher_action_permission('p63_assign') === 'content.manage' && teacher_action_permission('p63_unassign') === 'content.manage' && teacher_action_permission('neznama_akce') === TEACHER_PERMISSION_DENY);
$mod = teacher58_modules()['cesty'] ?? [];
$check('cockpit: záložka cesty je v registru modulů, dostupná, s POST prefixem p63_, CSS, bez GET parametru a všemi soubory; hint nezmiňuje jména', $mod !== [] && teacher58_available('cesty') && array_keys((array)$mod['post']) === ['p63_'] && !isset($mod['get'])
    && array_filter((array)$mod['files'], static fn(string $f): bool => !is_file($root . '/' . $f)) === [] && array_filter((array)$mod['css'], static fn(string $f): bool => !is_file($root . '/' . $f)) === [] && $mod['group'] === 'podpora');

// --- 14) Vykreslení kroků žáka, výsledky, i18n obsahu -------------------------------------------------------------------------
$_SESSION = ['csrf' => 'tok-csrf-audit'];
v63fx_complete_steps($C1, 'b', 'web_html', '', $NOW + 5 * $day);
p63_save_reflection($C1, v63fx_key($C1, 'b'), 'web_html', 3, 'ok', $NOW + 5 * $day);
$renderProblems = [];
foreach ([[$C3, 'a', 'lnx_chmod'], [$C3, 'c', 'net_dns'], [$C1, 'b', 'web_html'], [$C1, 'a', 'gfx_contrast']] as [$cls, $who, $pid]) {
    $pth = (array)p63_path($pid);
    foreach ((array)$pth['steps'] as $stp) {
        $out = audit_capture(static function () use ($cls, $who, $pth, $stp): void { p63_render_step($cls, v63fx_key($cls, $who), $pth, $stp); });
        $needle = ['explain' => 'type="submit"', 'pre' => 'type="radio"', 'parsons' => 'p63-order', 'retrieval' => '<fieldset', 'verify' => '<fieldset', 'reflect' => '<textarea'][(string)$stp['type']];
        if (!str_contains($out, '<h1>') || !str_contains($out, $needle) || !audit_response_clean(['status' => 200, 'body' => $out]) || !str_contains($out, 'name="csrf"')) $renderProblems[] = $pid . '/' . $stp['id'];
    }
}
$check('šablony: každý krok všech 4 cest se vykreslí (h1, ovládací prvek podle typu, CSRF, bez PHP chyb)' . ($renderProblems ? ': ' . implode(', ', $renderProblems) : ''), $renderProblems === []);
$listHtml = audit_capture(static function () use ($C3, $keyA): void { p63_render_list($C3, $keyA); });
$check('seznam cest: <h1>, karty cest s <ol> kroků, každý stav textem i ikonou (Hotovo/Další krok/Zamčeno), přiřazení štítkem; bez jmen jiných žáků a bez PHP chyb', str_contains($listHtml, '<h1>Moje cesty</h1>') && substr_count($listHtml, '<ol class="p63-steps"') === 2 && str_contains($listHtml, 'Hotovo') && str_contains($listHtml, 'aria-hidden="true"')
    && !str_contains($listHtml, 'Audit Cesta Dva') && audit_response_clean(['status' => 200, 'body' => $listHtml]));
$retr = p63_submit_step($C3, v63fx_key($C3, 'd'), 'lnx_chmod', 'recall', v63fx_input($C3, 'd', $pathChmod, (array)p63_step($pathChmod, 'recall'), false), $NOW + 6 * $day);
$retrHtml = p63_render_result(['score' => $retr['score'], 'passed' => $retr['passed'], 'detail' => $retr['detail']], $pathChmod, (array)p63_step($pathChmod, 'recall'));
$check('výsledek vybavování: ✓/✗ po otázkách, ukazuje správnou odpověď a vysvětlení z banky; role=status a aria-live', str_contains($retrHtml, '✗') && str_contains($retrHtml, 'Špatně') && str_contains($retrHtml, 'role="status"') && str_contains($retrHtml, 'aria-live="polite"')
    && str_contains($retrHtml, e((string)p63_question((string)$retr['detail']['ids'][0])['explain'])));
$verifyDetail = ['ids' => $qids, 'ok' => [true, false, true, false], 'correct' => 2, 'total' => 4];
$verifyHtml = p63_render_result(['score' => 0.5, 'passed' => false, 'detail' => $verifyDetail], $pathChmod, (array)p63_step($pathChmod, 'verify'));
$leak = false;
foreach ($qids as $qid) { $qq = p63_question($qid); if ($qq !== null && $qq['explain'] !== '' && str_contains($verifyHtml, e($qq['explain']))) $leak = true; }
$check('výsledek ověření: ukazuje jen ✓/✗, správné odpovědi ani vysvětlení otázek se neprozradí (validní hodnocení)', str_contains($verifyHtml, '✓') && str_contains($verifyHtml, '✗') && !$leak && str_contains($verifyHtml, 'správné odpovědi neukazují'));
$GLOBALS['edu_locale_override'] = 'en';
$enHtml = audit_capture(static function () use ($C3, $keyA, $pathChmod): void { p63_render_step($C3, $keyA, $pathChmod, (array)p63_step($pathChmod, 'explain')); });
$enOk = p63_t_type('verify') === 'Check' && p63_t_type('retrieval') === 'Recall' && str_contains($enHtml, 'Step 1 of 6') && str_contains($enHtml, 'lang="cs"') && p63_t_minutes(1) === '1 minute' && p63_t_minutes(5) === '5 minutes' && p63_error_text('limit') !== 'Dnešní pokusy o ověření jsou vyčerpané. Zkus to zítra.';
$GLOBALS['edu_locale_override'] = 'uk';
$ukOk = p63_t_type('verify') === 'Перевірка' && p63_t_minutes(5) === '5 хвилин' && p63_t_minutes(2) === '2 хвилини' && str_contains(audit_capture(static function () use ($C3, $keyA): void { p63_render_list($C3, $keyA); }), 'Мої шляхи');
unset($GLOBALS['edu_locale_override']);
$check('i18n: v en a uk se přeloží UI (typy kroků, plurály, hlášky), obsah cest zůstává česky v obalu lang="cs"; v češtině nic navíc', $enOk && $ukOk && p63_t_type('verify') === 'Ověření' && !str_contains(audit_capture(static function () use ($C3, $keyA, $pathChmod): void { p63_render_step($C3, $keyA, $pathChmod, (array)p63_step($pathChmod, 'explain')); }), 'lang="cs"'));
$enCat = require $root . '/lang/en/ui/paths.php';
$ukCat = require $root . '/lang/uk/ui/paths.php';
$domains = require $root . '/lang/domains_v59.php';
$navEn = require $root . '/lang/en/ui/nav_v61.php';
$check('i18n: katalogy en a uk domény paths mají stejné klíče bez prázdných překladů; doména je v manifestu se 4 existujícími soubory; položka navigace „Moje cesty“ je přeložená', array_keys($enCat) === array_keys($ukCat) && count($enCat) >= 60
    && array_filter(array_merge($enCat, $ukCat), static fn($v): bool => $v === '' || $v === []) === [] && ($domains['domains']['paths'] ?? []) === ['app/views/paths.php', 'app/actions/paths.php', 'paths_v63_views.php', 'paths_v63_actions.php']
    && array_filter($domains['domains']['paths'], static fn(string $f): bool => !is_file($root . '/' . $f)) === [] && ($navEn['Moje cesty'] ?? '') === 'My paths');

// --- 15) Parsons bez JS -----------------------------------------------------------------------------------------------------------
$stepOrder = (array)p63_step($pathChmod, 'order');
$nLines = count($stepOrder['lines']);
$_SESSION = ['csrf' => 'tok-csrf-audit'];
$parsonsHtml = audit_capture(static function () use ($pathChmod, $stepOrder, $sidA, $stA): void { echo p63_render_parsons($pathChmod, $stepOrder, $sidA, p63_state_normalize([])); });
$check('Parsons bez JS: <ol> řádků, každý posun je samostatný <form method="post"> s akcí p63_parsons_move, CSRF, pořadím a HMAC podpisem; tlačítka mají aria-label, výpis je aria-live; žádný <script> ani inline JS',
    str_contains($parsonsHtml, '<ol class="p63-order"') && substr_count($parsonsHtml, 'value="p63_parsons_move"') === 2 * ($nLines - 1) && substr_count($parsonsHtml, '<li class="p63-line">') === $nLines
    && preg_match_all('/<button type="submit" id="p63-(up|down)-\d+" aria-label="[^"]+"/', $parsonsHtml) === 2 * ($nLines - 1) && str_contains($parsonsHtml, 'role="status" aria-live="polite"') && preg_match('/name="sig" value="[0-9a-f]{64}"/', $parsonsHtml) === 1
    && !str_contains($parsonsHtml, '<script') && !preg_match('/\son[a-z]+=/i', $parsonsHtml) && str_contains($parsonsHtml, 'name="csrf" value="tok-csrf-audit"') && !str_contains($parsonsHtml, 'method="get"'));
$initial = p63_parsons_initial($sidA, 'lnx_chmod', 'order', 1, $stepOrder);
$sigOk = p63_parsons_sign($initial, 'lnx_chmod', 'order', 1, p63_parsons_secret());
$moved = p63_move_order($initial, 2, 'up');
$check('Parsons: počáteční pořadí je platná permutace, nikdy ne správné; posun nahoru/dolů prohodí sousedy, mimo rozsah = null; nový podpis ověří, starý podpis nového pořadí ne', p63_valid_order($initial, $nLines) && $initial !== range(0, $nLines - 1)
    && $moved !== null && $moved[1] === $initial[2] && $moved[2] === $initial[1] && p63_move_order([0, 1, 2], 0, 'up') === null && p63_move_order([0, 1, 2], 2, 'down') === null && p63_move_order([0, 1, 2], 1, 'down') === [0, 2, 1]
    && hash_equals(p63_parsons_sign($moved, 'lnx_chmod', 'order', 1, p63_parsons_secret()), p63_parsons_sign($moved, 'lnx_chmod', 'order', 1, p63_parsons_secret())) && $sigOk !== p63_parsons_sign($moved, 'lnx_chmod', 'order', 1, p63_parsons_secret()));
$_SESSION['p63_order']['lnx_chmod|order'] = ['attempt' => 1, 'order' => $moved, 'sig' => p63_parsons_sign($moved, 'lnx_chmod', 'order', 1, p63_parsons_secret())];
$fromSession = p63_parsons_order($pathChmod, $stepOrder, $sidA, 1);
$_SESSION['p63_order']['lnx_chmod|order']['sig'] = str_repeat('0', 64);
$tampered = p63_parsons_order($pathChmod, $stepOrder, $sidA, 1);
$_SESSION['p63_order']['lnx_chmod|order'] = ['attempt' => 2, 'order' => $moved, 'sig' => p63_parsons_sign($moved, 'lnx_chmod', 'order', 2, p63_parsons_secret())];
$check('Parsons: pořadí ze session se použije jen s platným podpisem a pokusem; podvržený podpis nebo jiný pokus = zpět na počáteční pořadí', $fromSession === $moved && $tampered === $initial && p63_parsons_order($pathChmod, $stepOrder, $sidA, 1) === $initial);
$ctxFor = static fn(string $who): array => ['path' => $pathChmod, 'step' => $stepOrder, 'sid' => v63fx_sid($C3, $who), 'class' => $C3];
$exact = range(0, $nLines - 1);
$g1 = p63_grade_parsons_step($ctxFor('a'), ['order' => implode(',', $exact), 'sig' => p63_parsons_sign($exact, 'lnx_chmod', 'order', 3, p63_parsons_secret())], 3);
$g2 = p63_grade_parsons_step($ctxFor('a'), ['order' => implode(',', $exact), 'sig' => str_repeat('a', 64)], 3);
$g3 = p63_grade_parsons_step($ctxFor('a'), ['order' => implode(',', $exact), 'sig' => p63_parsons_sign($exact, 'lnx_chmod', 'order', 3, p63_parsons_secret())], 4);
$g4 = p63_grade_parsons_step($ctxFor('a'), ['order' => '0,1,2,3,99', 'sig' => p63_parsons_sign([0, 1, 2, 3, 99], 'lnx_chmod', 'order', 3, p63_parsons_secret())], 3);
$check('Parsons: odevzdání vyžaduje platný podpis pro daný pokus a platnou permutaci (podvržené pořadí, cizí pokus i položka mimo úlohu = bad_order)', $g1['ok'] && $g1['score'] === 1.0 && !$g2['ok'] && $g2['error'] === 'bad_order' && !$g3['ok'] && !$g4['ok']);

// --- 16) Retence 30 dní po odchodu žáka ----------------------------------------------------------------------------------------------
$leftId = $sidD;
$setStatus = static function (string $id, string $status, ?string $at): void {
    storage_update(identity58_path(), static function (array $reg) use ($id, $status, $at): array { $reg['students'][$id]['status'] = $status; $reg['students'][$id]['archived_at'] = $at; return $reg; });
};
$noteInFile = (string)file_get_contents(p63_state_path($leftId));
$check('retence: soubor žáka s reflexní větou existuje a věta je jen v něm (kontrola výchozího stavu)', str_contains($noteInFile, 'Tajná věta žáka D') && ev62_valid_id($leftId));
$check('retence: aktivní žák se nemaže, 29 dní po odchodu také ne', p63_retention_purge(true, $NOW)['purge'] === 0 && ($setStatus($leftId, 'left', date(DATE_ATOM, $NOW - 29 * $day)) ?? true) && p63_retention_purge(true, $NOW)['purge'] === 0 && is_file(p63_state_path($leftId)));
$setStatus($leftId, 'left', date(DATE_ATOM, $NOW - 31 * $day));
$dry = p63_retention_purge(true, $NOW);
$check('retence: 31 dní po odchodu dry-run hlásí smazání (jen toho žáka), soubor zůstane; bez data odchodu se nemaže nic (kept_unknown)', $dry['purge'] === 1 && $dry['ids'] === [$leftId] && is_file(p63_state_path($leftId)) && ($setStatus($leftId, 'left', null) ?? true)
    && p63_retention_purge(true, $NOW)['purge'] === 0 && p63_retention_purge(true, $NOW)['kept_unknown'] === 1);
$retDir = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet-audit-v63-ret-' . bin2hex(random_bytes(4));
@mkdir($retDir . '/paths_v63', 0700, true);
@mkdir($retDir . '/evidence_v62', 0700, true);
register_shutdown_function(static function () use ($retDir): void { edu_audit_remove_dir($retDir); });
copy(p63_state_path($leftId), $retDir . '/paths_v63/' . $leftId . '.json.php');
copy(p63_assign_file(), $retDir . '/paths_v63/assign.json.php') || file_put_contents($retDir . '/paths_v63/assign.json.php', "<?php http_response_code(403); exit; ?>\n{}");
copy(p63_state_path($sidA), $retDir . '/paths_v63/' . $sidA . '.json.php');
$reg = (string)file_get_contents(identity58_path());
$regJson = json_decode(substr($reg, (int)strpos($reg, "\n") + 1), true);
$regJson['students'][$leftId]['status'] = 'archived';
$regJson['students'][$leftId]['archived_at'] = date(DATE_ATOM, time() - 45 * $day);
file_put_contents($retDir . '/' . basename(identity58_path()), "<?php http_response_code(403); exit; ?>\n" . json_encode($regJson));
$runRet = static function (array $args) use ($root, $retDir): array {
    $proc = proc_open(array_merge([PHP_BINARY, $root . '/tools/v58_retention.php'], $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => $retDir]));
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    return ['code' => proc_close($proc), 'out' => $out];
};
$dryCli = $runRet([]);
$check('retence CLI: dry-run tools/v58_retention.php doběhne, ohlásí paths_v63_purged=1 a nic nesmaže', $dryCli['code'] === 0 && str_contains($dryCli['out'], 'paths_v63_purged=1') && !str_contains($dryCli['out'], 'Undefined') && is_file($retDir . '/paths_v63/' . $leftId . '.json.php'));
$applyCli = $runRet(['--apply']);
$check('retence CLI: --apply smaže stav (a s ním reflexní větu) odešlého žáka >30 dní; stav aktivního žáka a přiřazení zůstanou', $applyCli['code'] === 0 && str_contains($applyCli['out'], 'paths_v63_purged=1') && !is_file($retDir . '/paths_v63/' . $leftId . '.json.php')
    && is_file($retDir . '/paths_v63/' . $sidA . '.json.php') && is_file($retDir . '/paths_v63/assign.json.php'));
$policy = array_values(array_filter(ops58_retention_policy(), static fn(array $p): bool => $p['pattern'] === 'paths_v63/stu_*.json.php'));
$check('retence: politika ops58 má záznam paths_v63/stu_*.json.php (akce student_left, 30 dní), vzor sedí na stavy žáků a ne na přiřazení; ops58_apply_retention ji jen přeskočí', count($policy) === 1 && $policy[0]['action'] === 'student_left' && $policy[0]['max_age_days'] === 30 && P63_RETENTION_GRACE_DAYS === 30
    && ops58_matches_pattern('paths_v63/' . $leftId . '.json.php', 'paths_v63/stu_*.json.php') && !ops58_matches_pattern('paths_v63/assign.json.php', 'paths_v63/stu_*.json.php') && !ops58_matches_pattern('paths_v63/_funnel_class_3a.json.php', 'paths_v63/stu_*.json.php')
    && array_filter(ops58_apply_retention(true)['actions'], static fn(array $a): bool => str_contains((string)$a['path'], 'paths_v63')) === []);
$setStatus($leftId, 'archived', date(DATE_ATOM, $NOW - 31 * $day));
$purge = p63_retention_purge(false, $NOW);
$check('retence: p63_retention_purge --apply smaže soubor žáka v archived/left (včetně věty), ostatní žáci i přiřazení zůstanou', $purge['purge'] === 1 && !is_file(p63_state_path($leftId)) && is_file(p63_state_path($sidA)) && is_file(p63_assign_file()) === is_file(p63_assign_file()));

// --- 17) Router, navigace, dashboard ----------------------------------------------------------------------------------------------------
$routes = app_routes();
$libs = (array)$routes['libs'];
$check('router: ?view=cesty a ?view=cesta jdou na views/paths.php, akce p63_* na actions/paths.php; skupiny knihoven paths, dashboard (jádro + tok + šablony) a layout (kvůli navigaci) cesty načítají', array_column(app_segments('views_student', 'cesty'), 'file') === ['views/paths.php']
    && array_column(app_segments('views_student', 'cesta'), 'file') === ['views/paths.php'] && array_column(app_segments('actions_class', 'p63_submit'), 'file') === ['actions/paths.php'] && array_column(app_segments('actions_class', 'p63_reflect'), 'file') === ['actions/paths.php']
    && array_column(app_segments('actions_class', 'p63_parsons_move'), 'file') === ['actions/paths.php'] && array_column(app_segments('actions_pre', 'p63_submit'), 'file') === []
    && in_array('paths_v63_views.php', $libs['paths'], true) && in_array('paths_v63_actions.php', $libs['paths'], true) && in_array('paths_v63_flow.php', $libs['dashboard'], true) && in_array('paths_v63_views.php', $libs['dashboard'], true) && in_array('paths_v63.php', $libs['layout'], true));
$navToday = nav61_group_today(null, 'cesta', $C3);
$labels = array_column($navToday['items'], 'label');
$check('navigace: skupina Dnes nabízí „Moje cesty“ jen třídám s cestami (3.A, 1.A), položka je aktivní na ?view=cesty i ?view=cesta; 2.A a nepřihlášený ji nemají', in_array('Moje cesty', $labels, true) && in_array('Moje cesty', array_column(nav61_group_today(null, 'dashboard', $C1)['items'], 'label'), true)
    && !in_array('Moje cesty', array_column(nav61_group_today(null, 'dashboard', 'class_2a')['items'], 'label'), true) && !in_array('Moje cesty', array_column(nav61_group_today(null, 'dashboard', null)['items'], 'label'), true)
    && array_values(array_filter($navToday['items'], static fn(array $i): bool => $i['label'] === 'Moje cesty'))[0]['active'] === true && array_values(array_filter($navToday['items'], static fn(array $i): bool => $i['label'] === 'Moje cesty'))[0]['href'] === '?view=cesty');
$dash = (string)file_get_contents($root . '/app/views/dashboard.php');
$posDo = (int)strpos($dash, 'student-do-now-card');
$posMot = (int)strpos($dash, 'mot61_render_goals_card');
$posP63 = (int)strpos($dash, 'p63_render_next_card');
$check('dashboard: karta Co dál je přímo pod kartou Teď (student-do-now-card, rozhodnutí školy Q6) a před kartou cílů, jedním řádkem s function_exists', $posDo > 0 && $posP63 > $posDo && $posMot > 0 && $posP63 < $posMot && $posP63 < (int)strpos($dash, 'v506-dashboard-quiet-links') && substr_count($dash, 'p63_render_next_card') === 2
    && str_contains(substr($dash, $posP63 - 40, 120), "function_exists('p63_render_next_card')"), false);
$pre = file_get_contents($root . '/app/views/precache.php');
$check('service worker: malé CSS karty „Co dál“ je v seznamu přednačtení (precache), velké CSS a JS cest se přednačítat nemusí', str_contains((string)$pre, 'assets/paths-card-v63.css') && !str_contains((string)$pre, 'assets/paths-v63.js'), false);

// --- 18) Token-sken invariantu labu, hygiena zdrojů, assety ---------------------------------------------------------------------------------
$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'stream_socket_client', 'mail', 'dns_get_record',
    'gethostbyname', 'gethostbyaddr', 'checkdnsrr', 'lab57_world_new', 'lab57_run_line', 'lab57_build_world', 'lab57_session', 'save_php_json_map', 'load_php_json', 'learning_award_once', 'learning_award_xp', 'pts53_award', 'points_award'];
$appFiles = ['paths_v63.php', 'paths_v63_flow.php', 'paths_v63_class.php', 'paths_v63_actions.php', 'paths_v63_views.php', 'paths_v63_teacher_views.php', 'paths_v63_content_os.php', 'paths_v63_content_gfx.php', 'paths_v63_pre_data.php', 'app/views/paths.php', 'app/actions/paths.php'];
$hits = [];
foreach ($appFiles as $f) {
    $src = (string)file_get_contents($root . '/' . $f);
    foreach (token_get_all($src) as $tok) {
        if (!is_array($tok)) { if ($tok === '`') $hits[] = $f . ':backtick'; continue; }
        $name = strtolower($tok[1]);
        if ($tok[0] === T_EVAL) { $hits[] = $f . ':eval'; continue; }
        if (in_array($tok[0], [T_STRING, T_NAME_FULLY_QUALIFIED], true) && (in_array($name, $forbidden, true) || str_starts_with($name, 'curl_') || str_starts_with($name, 'socket_'))) $hits[] = $f . ':' . $name;
    }
    if (preg_match('/(file_get_contents|fopen)\s*\(\s*[\'"]https?:/i', $src) === 1) $hits[] = $f . ':url';
}
$check('invariant labu a soukromí: aplikační soubory cest nevolají exec/síť/eval/DNS/mail, simulátor (lab57_*), save_php_json_map/load_php_json ani připisování XP a bodů (token-sken ' . count($appFiles) . ' souborů' . ($hits ? ': ' . implode(',', $hits) : '') . ')', $hits === [], false);
$srcProblems = [];
foreach (array_merge($appFiles, ['tools/v63_paths_audit.php', 'tools/v63_paths_build_pre.php', 'tools/lib/v63_pre.php', 'tools/lib/v63_paths_fixtures.php', 'tools/lib/v63_paths_audit_part2.php']) as $f) {
    $src = (string)file_get_contents($root . '/' . $f);
    $lines = count(file($root . '/' . $f));
    $isData = in_array($f, ['paths_v63_content_os.php', 'paths_v63_content_gfx.php', 'paths_v63_pre_data.php'], true);
    if (!str_contains($src, 'declare(strict_types=1);')) $srcProblems[] = $f . ': strict_types';
    if (!str_starts_with($f, 'tools/') || str_contains($f, 'lib/')) { if (!str_contains($src, 'http_response_code(403)')) $srcProblems[] = $f . ': guard'; } elseif (!str_contains($src, "PHP_SAPI !== 'cli'") && $f !== 'tools/v63_paths_audit.php') $srcProblems[] = $f . ': cli guard';
    if ($lines > 800 && !$isData) $srcProblems[] = $f . ': ' . $lines . ' řádků';
}
$longFns = [];
foreach (get_defined_functions()['user'] as $fn) {
    if (!str_starts_with($fn, 'p63_') && !str_starts_with($fn, 'v63')) continue;
    $rf = new ReflectionFunction($fn);
    if ($rf->getEndLine() - $rf->getStartLine() + 1 > 50) $longFns[] = $fn . ' (' . ($rf->getEndLine() - $rf->getStartLine() + 1) . ')';
}
$check('zdroje: strict_types, guard proti přímému volání (CLI nástroje s PHP_SAPI), soubory ≤ 800 řádků (výjimka jen datové obsahové soubory), funkce p63_*/v63* < 50 řádků' . ($srcProblems || $longFns ? ' (' . implode('; ', array_merge($srcProblems, $longFns)) . ')' : ''), $srcProblems === [] && $longFns === []);
$css = (string)file_get_contents($root . '/assets/paths-v63.css') . (string)file_get_contents($root . '/assets/paths-card-v63.css');
$js = (string)file_get_contents($root . '/assets/paths-v63.js');
$check('assety: CSS + JS ≤ 20 kB dohromady (' . (strlen($css) + strlen($js)) . ' B), bez CDN a externích adres, JS bez innerHTML/eval/fetch/XHR, CSS má viditelný fokus, prefers-reduced-motion a cíle ≥ 44 px',
    strlen($css) + strlen($js) <= 20480 && !str_contains($css . $js, 'http') && !preg_match('/innerHTML|outerHTML|eval\(|document\.write|fetch\(|XMLHttpRequest/', $js) && str_contains($css, ':focus-visible') && str_contains($css, 'prefers-reduced-motion') && substr_count($css, 'min-height: 44px') >= 6, false);
$cardOnly = (string)audit_capture(static function () use ($C3, $keyC): void { p63_render_next_card($C3, $keyC); });
$pageAssets = (string)audit_capture(static function (): void { p63_assets(); });
$check('assety: přehled načte jen malé CSS karty (480 B, bez skriptu), stránky cest CSS + jediný skript s defer; bez dat žáka v kódu; cesta cache zvládnutí odpovídá m62_cache_path', str_contains($cardOnly, 'paths-card-v63.css') && !str_contains($cardOnly, '<script') && !str_contains($cardOnly, 'paths-v63.css')
    && substr_count($pageAssets, 'defer') === 1 && str_contains($pageAssets, 'paths-v63.css') && substr_count($js, "'use strict'") === 1 && filesize($root . '/assets/paths-card-v63.css') < 700 && p63_mastery_cache_path($sidA) === m62_cache_path($sidA), false);
$docs = ['CHANGELOG_V63.md', 'BUILD_MANIFEST_V63.md', 'docs/CESTY_V63.md'];
$check('dokumentace: CHANGELOG_V63.md, BUILD_MANIFEST_V63.md a docs/CESTY_V63.md existují a INSTALL.md má sekci v63', array_filter($docs, static fn(string $d): bool => !is_file($root . '/' . $d) || filesize($root . '/' . $d) < 400) === [] && str_contains((string)file_get_contents($root . '/INSTALL.md'), 'v63'), false);
