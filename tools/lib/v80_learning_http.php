<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80 · HTTP a prohlížečová část tools/v80_learning_audit.php (používá $tmp, $check, $ROOT, $read, V80_KEY). Dočasné úložiště, fiktivní žáci
 * (student_directory.php), nic ostrého. Dev server s vývojovým vstupem žáka (?class=&student=) a učitelským klíčem; druhý server v režimu účtů (rozsah tříd).
 */

$cache = $tmp . '-lm71';
@mkdir($cache, 0700, true);
register_shutdown_function(static function () use ($cache): void { edu_audit_remove_dir($cache); });
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V80_KEY, 'EDUCANET_TEACHER_ROLE' => 'admin', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cache];
$list = array_values(array_map(static fn($k, $s): array => ['key' => (string)$k, 'label' => (string)$s['label']], array_keys(project_students_for_class('class_3a')), project_students_for_class('class_3a')));
[$A, $B] = [$list[2], $list[3]];
/** Stav žáka čerstvě ze souboru (zápisy dělá dev server, paměť požadavku CLI by byla zastaralá). */
$fresh = static function (string $ref): array { unset($GLOBALS['lr80_memo'], $GLOBALS['educanet_json_request_cache']); return lr80_state('class_3a', $ref); };   // i cache čtení (podpis mtime v sekundách + velikost)
/** Složená kontrola s výpisem nesplněných dílčích podmínek. */
$multi = static function (string $label, array $conds) use ($check): void { $bad = array_keys(array_filter($conds, static fn(bool $c): bool => !$c)); $check($label . ($bad ? ' [nesplněno: ' . implode(', ', $bad) . ']' : ''), $bad === []); };
$loc = static fn(array $r): string => (string)($r['headers']['Location'] ?? $r['headers']['location'] ?? '');
$path = static fn(array $r): string => '/' . ltrim((string)preg_replace('~^https?://[^/]+~', '', $loc($r)), '/');
$check('fixture: třída 3.A má aspoň 4 fiktivní žáky (student_directory.php)', count($list) >= 4 && $A['label'] !== '' && $B['label'] !== '');

$h = Harness::start($env);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
try {
    audit_login_student($h, 'class_3a', $A['label']);
    $refA = lr80_ref('class_3a', $A['key']);   // až po přihlášení: server založí identitu v58 (student_id) a ref úloh ji používá
    $get = static fn(string $q): array => $h->request('GET', '/?' . $q);
    $csrfOf = static fn(array $r): string => (string)$h->csrfToken((string)$r['body']);
    $post = static fn(array $f, string $csrf): array => $h->request('POST', '/', $f + ['csrf' => $csrf], ['follow_redirects' => false]);

    // ------------------------------------------------------------ stránky žáka
    $urls = ['pokrok' => 'view=pokrok', 'cviceni' => 'view=cviceni', 'subnet' => 'view=cviceni&w=subnet', 'chmod' => 'view=cviceni&w=chmod', 'color' => 'view=cviceni&w=color', 'spec' => 'view=cviceni&w=spec',
        'opakovat' => 'view=opakovat', 'ulohy' => 'view=ulohy', 'uloha' => 'view=ulohy&uloha=net_subnet&krok=2'];
    $pages = array_map($get, $urls);
    $dirty = array_keys(array_filter($pages, static fn(array $r): bool => !audit_response_clean($r)));
    $check('žák: Můj pokrok, cvičení (seznam + 4), opakování, úlohy a úloha v kroku 2 → 200 bez chyb PHP' . ($dirty ? ' [' . implode(', ', $dirty) . ']' : ''), $dirty === []);
    $themed = array_filter(['pokrok', 'cviceni', 'subnet', 'opakovat'], static fn(string $k): bool => preg_match('~<html lang="cs" data-theme="system"~', (string)$pages[$k]['body']) === 1
        && str_contains((string)$pages[$k]['body'], 'prefers-color-scheme: dark') && str_contains((string)$pages[$k]['body'], 'learning-v80.css') && str_contains((string)$pages[$k]['body'], 'components-v78.css'));
    $check('tmavý režim podle zařízení: nové stránky mají data-theme="system" a tmavou vrstvu jen při (prefers-color-scheme: dark); CSS v80 + komponenty v78, JS cvičení jen na stránce cvičení',
        count($themed) === 4 && str_contains((string)$pages['chmod']['body'], 'practice-v80.js') && !str_contains((string)$pages['pokrok']['body'], 'practice-v80.js'));
    $pk = (string)$pages['pokrok']['body'];
    $next = (string)substr($pk, (int)strpos($pk, 'class="ds-card lr80-next"'), 900);
    $others = array_filter(array_slice($list, 0, 8), static fn(array $s): bool => $s['key'] !== $A['key'] && str_contains($pk, $s['label']));
    $check('Můj pokrok: JEDEN Další krok (jediné primární tlačítko v kartě), přehled s pruhy a hodnotou textem, tabulková alternativa (caption, th scope), Co umím / Co procvičit, cíle, odznaky; žádná jména spolužáků ani žebříček',
        substr_count($next, 'ds-btn--primary') === 1 && !str_contains($pk, 'data-v55-page-cta') && substr_count($pk, 'class="ds-card lr80-next"') === 1 && substr_count($pk, 'class="lr80-bar__value"') >= 3 && str_contains($pk, '<details class="lr80-table"><summary>Zobrazit jako tabulku</summary>')
        && str_contains($pk, '<caption>Přehled pokroku</caption>') && str_contains($pk, '<th scope="row">Úlohy s variantami</th>') && str_contains($pk, 'id="lr80-know-h"') && str_contains($pk, 'id="lr80-prac-h"')
        && str_contains($pk, 'id="lr80-goals"') && preg_match_all('~class="lr80-badge( is-earned)?"~', $pk) === 5 && $others === [] && preg_match('~žebříč|pořadí ve třídě: \d~iu', strip_tags($pk)) !== 1 && str_contains($pk, 'aria-hidden="true"><span class="lr80-bar__fill"'));

    // ------------------------------------------------------------ nápověda (POST, jen počet)
    $u = (string)$pages['uloha']['body'];
    $csrf = $csrfOf($pages['uloha']);
    $before = lr80_hint_total($fresh($refA));
    $noCsrf = $h->request('POST', '/', ['action' => 'lr80_hint', 'key' => 'net_subnet', 'round' => '1'], ['follow_redirects' => false]);
    $r1 = $post(['action' => 'lr80_hint', 'key' => 'net_subnet', 'round' => '1'], $csrf);
    $g1 = (string)$h->request('GET', $path($r1))['body'];
    $check('nápověda: formulář v kroku Řešení (tlačítko „Ukázat nápovědu 1 ze 3“); POST bez CSRF → 419 a nic se nezapíše; s CSRF → Nápověda 1 ze 3 (postrčení), počet +1, adresa jen úloha a krok',
        str_contains($u, 'value="lr80_hint"') && str_contains($u, 'Ukázat nápovědu 1 ze 3') && (int)$noCsrf['status'] === 419 && $loc($r1) === '?view=ulohy&uloha=net_subnet&krok=2#lr80-hints'
        && str_contains($g1, 'Nápověda 1 ze 3: Postrčení') && str_contains($g1, 'Ukázat nápovědu 2 ze 3') && lr80_hint_total($fresh($refA)) === $before + 1);
    $post(['action' => 'lr80_hint', 'key' => 'net_subnet', 'round' => '1'], $csrf);
    $post(['action' => 'lr80_hint', 'key' => 'net_subnet', 'round' => '1'], $csrf);
    $r4 = $post(['action' => 'lr80_hint', 'key' => 'net_subnet', 'round' => '1'], $csrf);
    $g4 = (string)$h->request('GET', $path($r4))['body'];
    $bad = $post(['action' => 'lr80_hint', 'key' => 'net_subnet', 'round' => '7'], $csrf);
    $multi('nápověda: 3 úrovně (postup, vyřešený krok s čísly varianty), pak „Všechny nápovědy jsou odkryté“ a počet se už nezvýší; jiné kolo odmítnuto', ['level3' => str_contains($g4, 'Nápověda 3 ze 3: Vyřešený krok'),
        'all' => str_contains($g4, 'Všechny nápovědy jsou odkryté.'), 'count' => lr80_hint_total($fresh($refA)) === $before + 3, 'round' => str_contains((string)$h->request('GET', $path($bad))['body'], 'Nápověda je jen k otevřené variantě.')]);

    // ------------------------------------------------------------ chybná odpověď → vysvětlení + opakování
    $v = tb75_variant('net_subnet', tb75_student_ref('class_3a', $A['key']), 1);
    $wrong = $post(['action' => 'tb75_answer', 'task' => 'net_subnet', 'back' => 'ulohy', 'a' => ['network' => (string)$v['fields'][1]['answer'], 'broadcast' => (string)$v['fields'][0]['answer'], 'hosts' => '1']], $csrf);
    $w = (string)$h->request('GET', $path($wrong))['body'];
    $item = $fresh($refA)['rep']['net_subnet'] ?? null;
    $check('chyba v úloze → krok Kontrola s „Kde je chyba“ a vysvětlením konkrétní chyby (záměna síť/broadcast); úloha zařazená do opakování na zítřek; adresa bez odpovědí',
        str_contains($w, 'Kde je chyba') && str_contains($w, 'Tohle je broadcast – poslední adresa bloku.') && str_contains($w, 'Tohle je síťová adresa') && is_array($item) && $item['due_at'] === lr80_add_days(time(), 1)
        && !str_contains($loc($wrong), (string)$v['fields'][1]['answer']) && str_contains($loc($wrong), 'view=ulohy'));

    // ------------------------------------------------------------ splatné opakování
    storage_update(lr80_path('class_3a'), static function (array $d) use ($refA): array { $d['students'][$refA]['rep']['net_subnet']['due_at'] = time() - 60; return $d; });
    $list1 = (string)$get('view=ulohy')['body'];
    $rp = $get('view=opakovat&uloha=net_subnet');
    $rv = lr80_rep_variant('net_subnet', $refA, $fresh($refA)['rep']['net_subnet']);
    $ans = $post(['action' => 'lr80_rep_answer', 'task' => 'net_subnet', 'a' => array_column($rv['fields'], 'answer', 'id')], $csrfOf($rp));
    $ra = (string)$h->request('GET', $path($ans))['body'];
    $check('opakování: na Úlohách upoutávka „Na dnes k opakování: 1“ + Můj pokrok / cvičení / opakování; úloha opakování (jeden pokus, nápověda zvlášť) → správně „Další opakování za 3 dny“, pak „Naplánováno“',
        str_contains($list1, 'Na dnes k opakování: 1') && str_contains($list1, 'class="ds78 lr80-links"') && str_contains((string)$rp['body'], 'value="lr80_rep_answer"') && str_contains((string)$rp['body'], 'value="rep:net_subnet"')
        && str_contains($ra, 'Správně. Další opakování za 3 dny.') && str_contains($ra, 'Na dnes k opakování: 0') && str_contains($ra, 'Naplánováno') && $loc($ans) === '?view=opakovat');

    // ------------------------------------------------------------ cvičení kontrolovaná serverem (bez JS = obyčejný POST)
    $wc = $get('view=cviceni&w=chmod');
    $cv = wg80_variant('chmod', $refA, max(1, (int)(($fresh($refA)['w']['chmod'] ?? [])['r'] ?? 1)));
    $boxes = array_values(array_filter(WG80_PERMS, static fn(string $p, int $i): bool => ((int)$cv['data']['mode'] & (1 << (8 - $i))) !== 0, ARRAY_FILTER_USE_BOTH));
    $wb = (string)$wc['body'];
    $okc = $post(['action' => 'lr80_w_check', 'w' => 'chmod', 'p' => $boxes], $csrfOf($wc));
    $okb = (string)$h->request('GET', $path($okc))['body'];
    $col = wg80_variant('color', $refA, 1);
    $badc = $post(['action' => 'lr80_w_check', 'w' => 'color', 'r' => (string)((int)$col['fields']['r'] ^ 1), 'g' => $col['fields']['g'], 'b' => $col['fields']['b']], $csrfOf($wc));
    $bb = (string)$h->request('GET', $path($badc))['body'];
    $evil = $post(['action' => 'lr80_w_check', 'w' => '../x'], $csrfOf($wc));
    $check('cvičení chmod: 9 zaškrtávátek s popisky (vlastník/skupina/ostatní × r/w/x) + osmičkové pole, výstup aria-live; server přijme správnou kombinaci („Správně, všechno sedí“, nový příklad)',
        substr_count($wb, 'name="p[]"') === 9 && substr_count($wb, '<legend>') >= 3 && str_contains($wb, 'aria-live="polite" data-w80-perm') && str_contains($wb, 'data-w80-oct') && str_contains($okb, 'Správně, všechno sedí.')
        && str_contains($okb, 'Příklad 2') && $loc($okc) === '?view=cviceni&w=chmod#lr80-w-result');
    $check('cvičení barvy: chyba → „Správně 2 z 3“, vysvětlení složky a zbývající pokusy; neznámé cvičení → „Cvičení se nepodařilo najít“',
        str_contains($bb, 'Správně 2 z 3.') && str_contains($bb, 'červená (R)') && str_contains($bb, 'Zbývá pokusů: 2.') && str_contains((string)$h->request('GET', $path($evil))['body'], 'Cvičení se nepodařilo najít.'));
    $sv = wg80_variant('spec', $refA, 1);
    $sp = $post(['action' => 'lr80_w_check', 'w' => 'spec'] + $sv['fields'], $csrfOf($wc));
    $spBody = (string)$h->request('GET', $path($sp))['body'];   // výsledek je v relaci jen do prvního zobrazení
    $nv = wg80_variant('subnet', $refA, 1);
    $sn = $post(['action' => 'lr80_w_check', 'w' => 'subnet'] + $nv['fields'], $csrfOf($wc));
    $snBody = (string)$h->request('GET', $path($sn))['body'];
    $multi('cvičení souboj specificity a kalkulačka podsítí: správné odpovědi server přijme; stránka cvičení má živé výstupy s šablonou data-t a vstupy s popisky', [
        'spec' => str_contains($spBody, 'Správně, všechno sedí.'), 'subnet' => str_contains($snBody, 'Správně, všechno sedí.'),
        'calc' => str_contains((string)$pages['subnet']['body'], 'data-w80-calc data-t='), 'duel' => str_contains((string)$pages['spec']['body'], 'data-w80-duel data-t='), 'range' => str_contains((string)$pages['color']['body'], 'data-w80-range="r"')]);

    // ------------------------------------------------------------ cíle a soukromí
    $pg = $get('view=pokrok');
    $ga = $post(['action' => 'lr80_goal_add', 'goal_pick' => 'tasks:3'], $csrfOf($pg));
    $gb = (string)$h->request('GET', $path($ga))['body'];
    $gx = $post(['action' => 'lr80_goal_add', 'goal_pick' => 'tasks:99'], $csrfOf($pg));
    $check('cíle: výběr z nabídky (bez volného textu) → „Vyřešit 3 varianty úloh“ s pruhem a textem „0 z 3“; neplatná volba odmítnuta',
        str_contains((string)$pg['body'], 'name="goal_pick"') && !preg_match('~<textarea|name="goal_text"~', (string)$pg['body']) && str_contains($gb, 'Cíl je nastavený na tento týden.') && str_contains($gb, 'Vyřešit 3 varianty úloh')
        && str_contains($gb, '>0 z 3<') && str_contains((string)$h->request('GET', $path($gx))['body'], 'Vyber cíl z nabídky.'));
    $jarA = $jarSwap([]);
    audit_login_student($h, 'class_3a', $B['label']);
    $pb = (string)$get('view=pokrok')['body'];
    $jarSwap($jarA);
    $check('soukromí: jiný žák na své stránce Můj pokrok nevidí cizí cíle, odznaky ani jméno; data jen jeho (ref ze session, nic v URL)',
        !str_contains($pb, 'class="lr80-bars lr80-goals"') && str_contains($gb, 'class="lr80-bars lr80-goals"') && !str_contains($pb, $A['label']) && str_contains($pb, '<h1>Můj pokrok</h1>'));
    foreach (['en' => 'My progress', 'uk' => 'Мій прогрес'] as $lang => $title) {
        $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => $lang, 'return' => '?view=pokrok', 'csrf' => $csrfOf($get('view=pokrok'))]);
        $tr[$lang] = (string)$get('view=pokrok')['body'] . (string)$get('view=cviceni&w=chmod')['body'];
    }
    $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'cs', 'return' => '?view=pokrok', 'csrf' => $csrfOf($get('view=pokrok'))]);
    $check('i18n: Můj pokrok a cvičení v angličtině a ukrajinštině (nadpisy, tlačítka); zadání a nápovědy zůstávají česky s atributem lang',
        str_contains($tr['en'], '<h1>My progress</h1>') && str_contains($tr['en'], 'Next step') && str_contains($tr['en'], 'Owner (u)') && str_contains($tr['uk'], '<h1>Мій прогрес</h1>') && str_contains($tr['uk'], 'Власник (u)')
        && str_contains($tr['en'], 'lang="cs"'));

    // ------------------------------------------------------------ učitel (administrátor klíčem)
    $jarS = $jarSwap([]);
    audit_login_teacher($h, V80_KEY, 'Audit Pokrok');
    $t = $h->request('GET', '/teacher.php?tab=pokrok80&class=class_3a');
    $tb = (string)$t['body'];
    $jarSwap($jarS);
    $check('učitel: záložka Pokrok a podpora žáků (3.A) – tabulka žáků s cíli, odznaky, opakováním, počtem nápověd a cvičeními; nápovědy podle úloh; bez odpovědí žáků',
        audit_response_clean($t) && str_contains($tb, '<h1>Pokrok a podpora žáků</h1>') && str_contains($tb, '<th scope="row">' . e($A['label']) . '</th>') && str_contains($tb, 'vyřešené varianty úloh 0 z 3')
        && str_contains($tb, 'Kde žáci potřebují nápovědu') && str_contains($tb, 'Podsíť: síť, broadcast a počet adres</strong>: 3× nápověda') && !str_contains($tb, (string)$v['fields'][1]['answer']));
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- rozsah tříd (režim učitelských účtů)
require_once __DIR__ . '/v59_scope_fixtures.php';
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cache]);
try {
    audit_login_teacher_account($h2, $acc['login']['a'], $acc['pw']['a']);
    $own = $h2->request('GET', '/teacher.php?tab=pokrok80&class=class_1a');
    $foreign = $h2->request('GET', '/teacher.php?tab=pokrok80&class=class_3a');
    $check('rozsah: učitel 1.A/2.A vidí pokrok své třídy (200), cizí 3.A → 403 (deny-by-default přes teacher59_guard_get_check); v nabídce tříd jen jeho třídy',
        audit_response_clean($own) && (int)$foreign['status'] === 403 && !str_contains((string)$own['body'], '?tab=pokrok80&amp;class=class_3a'));
} finally {
    $h2->stop();
}

// ---------------------------------------------------------------- 4) prohlížeč
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$mode = (string)getenv('EDUCANET_V80_BROWSER');
if ($mode === 'skip') { echo 'SKIP  prohlížečová část (EDUCANET_V80_BROWSER=skip)' . PHP_EOL; return; }
$bh = Harness::start($env);
try {
    $shots = (string)getenv('EDUCANET_V80_SHOTS');
    $cmd = escapeshellarg($node) . ' ' . escapeshellarg(__DIR__ . '/v80_learning_browser.cjs') . ' ' . escapeshellarg($bh->baseUrl()) . ' ' . escapeshellarg($A['label']) . ($shots !== '' ? ' ' . escapeshellarg($shots) : '');
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn';
    if ($mode === 'require') $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_V80_BROWSER=require ji vynutí)' . PHP_EOL;
    return;
}
$rows = (array)($d['rows'] ?? []);
$check('prohlížeč: skript doběhl (8 stránek × 11 šířek × světlý/tmavý = 176 měření)' . (isset($d['error']) ? ' [' . $d['error'] . ']' : ''), !isset($d['error']) && count($rows) === 176);
$bad = array_filter($rows, static fn(array $r): bool => $r['hscroll'] || $r['smallCount'] > 0 || $r['tinyCount'] > 0);
$check('prohlížeč: 11 šířek (320–1920, 1366 × 600) × světlý/tmavý – 0× vodorovný posun, 0 cílů < 44 px, 0 textů < 14 px v obsahu v80'
    . ($bad ? ' [' . implode('; ', array_map(static fn(array $r): string => $r['page'] . '@' . $r['width'] . $r['scheme'][0] . ' h=' . (int)$r['hscroll'] . ' s=' . implode(',', (array)$r['small']) . ' t=' . implode(',', (array)$r['tiny']), array_slice($bad, 0, 4))) . ']' : ''), $bad === []);
$dark = array_filter($rows, static fn(array $r): bool => $r['scheme'] === 'dark' && (!$r['darkBg'] || !$r['lightText']));
$light = array_filter($rows, static fn(array $r): bool => $r['scheme'] === 'light' && $r['darkBg']);
$check('prohlížeč: tmavé zařízení → tmavé pozadí a světlý text na všech stránkách v80, světlé zařízení → světlé (bez ručního přepínače)', $dark === [] && $light === []);
$kb = (array)($d['keyboard'] ?? []);
$check('klávesnice + aria-live: chmod mezerník → osmičkově se změní, napsané 750 nastaví zaškrtávátka; barva šipkou na posuvníku → HEX i číslo; prefix 26 → maska 255.255.255.192; trojice → vítěz; Tab na nápovědu + Enter → Nápověda 1; viditelný fokus'
    . ' ' . json_encode($kb), ($kb['chmodSpace'] ?? false) && ($kb['chmodOct'] ?? false) && ($kb['colorArrow'] ?? false) && ($kb['subnet'] ?? false) && ($kb['duel'] ?? false) && ($kb['hintKey'] ?? false) && ($kb['focusRing'] ?? false));
if (!empty($d['shots'])) echo 'INFO  snímky: ' . count((array)$d['shots']) . ' (největší ' . round((int)($d['maxShot'] ?? 0) / 1024) . ' kB)' . PHP_EOL;
