<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v64 audit · dávka 5: integrace (politiky, routy, retence, i18n, invariant labu, velikosti, rozpočet). Proměnné: $check, $tmp, $root. */
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'ops_v58.php', 'economy_v64_teacher_views.php', 'teamgames_v64_views.php', 'arena_v64_views.php', 'challenges_v64.php'] as $lib) require_once $root . '/' . $lib;

$v64Files = ['economy_v64.php', 'economy_v64_teacher_views.php', 'arena_v64_fair.php', 'arena_v64_views.php', 'teamgames_v64_roles.php', 'teamgames_v64_views.php', 'challenges_v64.php', 'app/actions/teamgames_v64.php', 'tools/v64_engagement_report.php'];

// --- Politiky učitele (deny-by-default) ---------------------------------------------------------------
$pol = teacher59_action_policy('v64_abs_board');
$check('politika: v64_abs_board má exact politiku se třídou povinnou a v rozsahu', ($pol['class'] ?? '') === 'required' && empty($pol['deny']));
$check('politika: neznámá akce v64_* je zakázaná (deny-by-default), stejně jako v64_abs_board_x', !empty(teacher59_action_policy('v64_neco')['deny']) && !empty(teacher59_action_policy('v64_abs_board_x')['deny']));
$check('oprávnění: v64_ = students.manage (asistent s jen čtením akci nedostane)', teacher_action_permission('v64_abs_board') === 'students.manage');
$mod = teacher58_modules()['ekonomika'] ?? null;
$check('registr: modul Ekonomika existuje, mapuje POST prefix v64_ a všechny jeho soubory jsou na disku', is_array($mod) && isset($mod['post']['v64_']) && array_reduce((array)$mod['files'], static fn(bool $c, string $f): bool => $c && is_file($root . '/' . $f), true) && teacher58_available('ekonomika'));
$check('registr: modul Ekonomika nemá zvláštní GET parametr (nic k autorizaci kromě class/month)', !isset($mod['get']));
$check('GET: parametr month ve tvaru YYYY-MM se hlídá v teacher59_guard_get_check (zdroj)', str_contains((string)file_get_contents($root . '/teacher_scope_v59.php'), "\$tab === 'ekonomika' && isset(\$get['month'])"), false);

// --- Akce bez oprávnění / s neplatným vstupem (handler) -----------------------------------------------
$_POST = ['class_id' => 'class_neexistuje', 'kind' => 'ctf', 'id' => 'x', 'on' => '1'];
$thrown = false;
try { eco64_teacher_handle_post('v64_abs_board'); } catch (RuntimeException $e) { $thrown = true; }
$check('handler: třída mimo rozsah učitele se odmítne (výjimka) a nic se nezapíše', $thrown && !fair64_absolute_board_enabled('ctf', 'class_neexistuje', 'x'));
$_POST = [];
$thrown2 = false;
try { eco64_teacher_handle_post('v64_neco'); } catch (RuntimeException $e) { $thrown2 = true; }
$check('handler: neznámá akce ekonomiky vyhodí výjimku', $thrown2);
ob_start();
eco64_render_teacher_tab('class_3a', 'tok');
$tab = (string)ob_get_clean();
$check('cockpit: záložka Ekonomika se vykreslí (report, přepínače, retrospektivy), česky, s CSRF v přepínači', str_contains($tab, 'Měsíční report inflace XP') && str_contains($tab, 'name="csrf"') && str_contains($tab, 'Retrospektivy týmových her'));

// --- Routy ---------------------------------------------------------------------------------------------
$routes = require $root . '/app/routes.php';
$hit = [];
foreach ((array)$routes['actions_class'] as $r) if (in_array('tg64_retro', (array)$r['match'], true)) $hit = $r;
$check('routa: akce tg64_retro vede na app/actions/teamgames_v64.php a ten existuje', ($hit['file'] ?? '') === 'actions/teamgames_v64.php' && is_file($root . '/app/' . $hit['file']));
$check('routa: dashboard načítá challenges_v64.php, profil kompetencí také', in_array('challenges_v64.php', (array)$routes['libs']['dashboard'], true) && in_array('challenges_v64.php', (array)$routes['libs']['competency'], true));

// --- Retence -------------------------------------------------------------------------------------------
$patterns = array_column(ops58_retention_policy(), 'pattern');
$check('retence: deník ekonomiky se archivuje po školním roce (politika ops58)', in_array('economy_v64_ledger/*.jsonl.php', $patterns, true));
$check('retence: v58_retention.php volá tg64_retro_purge (retrospektivy po školním roce)', str_contains((string)file_get_contents($root . '/tools/v58_retention.php'), 'tg64_retro_purge('), false);

// --- i18n ----------------------------------------------------------------------------------------------
$manifest = require $root . '/lang/domains_v59.php';
$games64 = (array)($manifest['domains']['games64'] ?? []);
$check('i18n: doména games64 je v manifestu a obsahuje všechny žákovské soubory v64', $games64 !== [] && array_diff(['arena_v64_fair.php', 'arena_v64_views.php', 'teamgames_v64_roles.php', 'teamgames_v64_views.php', 'app/actions/teamgames_v64.php', 'challenges_v64.php'], $games64) === []);
$en = require $root . '/lang/en/ui/games64.php';
$uk = require $root . '/lang/uk/ui/games64.php';
$check('i18n: katalogy en a uk mají stejné klíče a žádný překlad není prázdný', array_keys($en) === array_keys($uk) && count($en) >= 40 && !in_array('', $en, true) && !in_array('', $uk, true));
$check('i18n: výsledek tr() v angličtině i ukrajinštině pro ligu (ne česky)', (function () use ($en, $uk): bool {
    return $en['Zlatá liga'] === 'Gold league' && $uk['Zlatá liga'] !== 'Zlatá liga';
})());

// --- Invariant labu: žádné spouštění / síť ----------------------------------------------------------------
$bad = [];
$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'stream_socket_client', 'mail', 'curl_init', 'gethostbyname', 'dns_get_record'];
foreach ($v64Files as $f) {
    if (!is_file($root . '/' . $f)) continue;
    foreach (token_get_all((string)file_get_contents($root . '/' . $f)) as $t) {
        if (is_array($t) && in_array($t[0], [T_STRING, T_EVAL, T_EXIT], true) && in_array(strtolower($t[1]), $forbidden, true)) $bad[] = $f . ':' . $t[1];
        if ($t === '`') $bad[] = $f . ':backtick';
    }
}
$check('invariant: soubory v64 nevolají exec/eval/síť/DNS/mail ani nemají zpětné apostrofy (token-sken)' . ($bad ? ' [' . implode(',', $bad) . ']' : ''), $bad === []);
$ctrl = (string)file_get_contents($root . '/linux_v57_lab.php') . (string)file_get_contents($root . '/lab_v57_api.php');
$check('invariant: linux_v57_*/lab_v57_api.php v64 nijak nezmínily (fair64/eco64/tg64/ch64 tam nejsou)', preg_match('/\b(fair64|eco64|tg64|ch64)_/', $ctrl) !== 1, false);

// --- Guardy, velikosti, rozpočet ----------------------------------------------------------------------
$sizeOk = true;
$guardOk = true;
foreach ($v64Files as $f) {
    if (!is_file($root . '/' . $f)) continue;
    $src = (string)file_get_contents($root . '/' . $f);
    if (count(explode("\n", $src)) > 800) $sizeOk = false;
    if (!str_starts_with($src, "<?php\n\ndeclare(strict_types=1);") && !str_starts_with($src, "<?php\n\ndeclare(strict_types=1);\n") && !str_contains(substr($src, 0, 120), 'declare(strict_types=1)')) $guardOk = false;
    if (!str_contains($src, "PHP_SAPI !== 'cli'") && !str_contains($src, '_SERVER[\'SCRIPT_FILENAME\']')) $guardOk = false;
}
$check('soubory v64: ≤ 800 řádků, strict_types a guard proti přímému volání', $sizeOk && $guardOk);
$cssSize = filesize($root . '/assets/arena-v64.css');
$check('rozpočet: assets/arena-v64.css ≤ 4 kB (celkem CSS+JS fáze ≤ +20 kB, žádný JS asset, žádné CDN)', $cssSize <= 4096 && !is_file($root . '/assets/arena-v64.js') && !preg_match('#https?://#', (string)file_get_contents($root . '/assets/arena-v64.css')), false);
$check('rozpočet: CSS karty výzvy týdne se nepřidává na dashboard (karta jen přes p63_card_assets)', !str_contains((string)file_get_contents($root . '/challenges_v64.php'), 'arena-v64.css'), false);

// --- Soukromí: ELO/liga nikdy ve známkách a exportech ---------------------------------------------------
$gradeFiles = glob($root . '/{project_grade*,grades*,teacher_export*}.php', GLOB_BRACE) ?: [];
$leak = [];
foreach ($gradeFiles as $f) if (preg_match('/\b(fair64|eco64)_/', (string)file_get_contents($f)) === 1) $leak[] = basename($f);
$check('ELO a liga se nikdy nepoužívají ve známkách ani exportech známek (' . count($gradeFiles) . ' souborů zkontrolováno)', $leak === []);
$view = '';
$_SESSION['local_user'] = ['id' => 'audit-u9', 'email' => 'a9@educanet.cz'];
ob_start();
fair64_render_league_panel('class_3a', 'class_3a:audit-x', ['class_3a:audit-y']);
$view = (string)ob_get_clean();
$check('soukromí: panel ligy zobrazí jen název ligy, ne číslo ELO (žádné 4místné číslo 6xx–1xxx)', str_contains($view, 'liga') && preg_match('/\b(1[0-9]{3}|[6-9][0-9]{2})\b/', strip_tags($view)) !== 1);

// --- Metrika spodního kvartilu (tools/v64_engagement_report.php) ------------------------------------------
require_once $root . '/tools/v64_engagement_report.php';
$xp = ['a' => 50, 'b' => 10, 'c' => 30, 'd' => 0, 'e' => 90, 'f' => 70, 'g' => 20, 'h' => 5];
$check('metrika: spodní kvartil z 8 žáků jsou 2 nejnižší podle XP (d, h) a vždy aspoň 1 žák', v64e_bottom_quartile($xp) === ['d', 'h'] && count(v64e_bottom_quartile(['x' => 1])) === 1 && v64e_bottom_quartile([]) === []);
$nowE = 1_790_000_000;
$ev = static fn(int $daysAgo): array => ['xp' => 10, 'at' => date(DATE_ATOM, $nowE - $daysAgo * 86400)];
$profilesE = ['k1' => ['xp' => 0, 'events' => []], 'k2' => ['xp' => 10, 'events' => ['v57:race:r1' => $ev(3)]], 'k3' => ['xp' => 500, 'events' => ['tg58:t1' => $ev(1)]], 'k4' => ['xp' => 400, 'events' => []],
    'k5' => ['xp' => 5, 'events' => ['lesson:x' => $ev(2)]], 'k6' => ['xp' => 300, 'events' => []], 'k7' => ['xp' => 200, 'events' => []], 'k8' => ['xp' => 8, 'events' => ['v57:race:r0' => $ev(90)]]];
$st = v64e_class_stats($profilesE, ['k1', 'k2', 'k3', 'k4', 'k5', 'k6', 'k7', 'k8'], $nowE, 28);
$check('metrika: kvartil k1,k5 (nejméně XP): aktivní je jen k5 (lekce v okně), z her nikdo; k8 s událostí před 90 dny není aktivní', $st['quartile'] === 2 && $st['active'] === 1 && $st['active_game'] === 0 && $st['share'] === 0.5 && v64e_class_stats($profilesE, ['k1', 'k2', 'k8', 'k3'], $nowE, 28)['active_game'] === 0);
$st2 = v64e_class_stats($profilesE, ['k1', 'k2', 'k3', 'k4'], $nowE, 28);
$check('metrika: událost ze hry (v57:race) ve spodním kvartilu se počítá do podílu z her (k1 bez XP, k2 s hrou: kvartil = k1 → 0)', $st2['quartile'] === 1 && $st2['active_game'] === 0 && v64e_is_game_event('robots58:x') && !v64e_is_game_event('lesson:x'));
$proc = proc_open([PHP_BINARY, $root . '/tools/v64_engagement_report.php'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => '']));
$refOut = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
$refCode = proc_close($proc);
$check('metrika: nástroj bez EDUCANET_STORAGE_DIR (ostrá storage/) odmítne běžet dřív, než načte aplikaci (kód 2)', $refCode === 2 && str_contains($refOut, 'V64_ENGAGEMENT_REFUSED'));
