<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.1 · HTTP a prohlížečová část tools/v80_1_teacher_audit.php (používá $tmp, $check, $ROOT, $fx, $now, V801_KEY). Dočasné úložiště s fixture
 * z části 2, fiktivní žáci (student_directory.php). Dev server s učitelským klíčem a vývojovým vstupem žáka; druhý server v režimu účtů (rozsah tříd, asistent).
 */

$cache = $tmp . '-lm71';
@mkdir($cache, 0700, true);
register_shutdown_function(static function () use ($cache): void { edu_audit_remove_dir($cache); });
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V801_KEY, 'EDUCANET_TEACHER_ROLE' => 'admin', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cache];
$multi = static function (string $label, array $conds) use ($check): void { $bad = array_keys(array_filter($conds, static fn(bool $c): bool => !$c)); $check($label . ($bad ? ' [nesplněno: ' . implode(', ', $bad) . ']' : ''), $bad === []); };
$fresh = static function (): void { unset($GLOBALS['lr80_memo'], $GLOBALS['ts801_memo'], $GLOBALS['educanet_json_request_cache']); };
[$A, $B, $Cs] = [$fx[0], $fx[1], $fx[2]];
$stored = static function () use ($fresh): array { $fresh(); return ts801_data('class_3a'); };

$h = Harness::start($env);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
try {
    $login = audit_login_teacher($h, V801_KEY, 'Audit Mapa');
    $tget = static fn(string $q): array => $h->request('GET', '/teacher.php?' . $q);
    $tpost = static fn(array $f): array => $h->request('POST', '/teacher.php', $f, ['follow_redirects' => false]);
    $csrfOf = static fn(array $r): string => (string)$h->csrfToken((string)$r['body']);
    $r = $tget('tab=mapa801&class=class_3a');
    $b = (string)$r['body'];
    $cols = count(comp62_competencies('os_site'));
    $multi('učitel: Mapa třídy 3.A (kompetence) – 200 bez chyb, tabulka s popiskem, lepicí sloupce, ' . count($fx) . ' řádků, každá buňka s ikonou a hodnotou textem, klik = detail žáka, souhrn třídy', [
        'clean' => audit_response_clean($r), 'h1' => str_contains($b, '<h1>Mapa třídy</h1>'), 'caption' => str_contains($b, '<caption id="cm801-cap">Mapa třídy – kompetence: ' . cm801_count(count($fx), 'žák', 'žáci', 'žáků') . ' × ' . cm801_count($cols, 'sloupec', 'sloupce', 'sloupců') . '</caption>'),
        'rows' => substr_count($b, 'name="students[]"') === count($fx), 'cells' => preg_match_all('~<td class="cm801-c is-(strong|ok|mid|low|none)" data-label="[^"]+"><a href="\?tab=student360&amp;class=class_3a&amp;student=class_3a%3Astudent%3A[0-9a-f]{24}"><span aria-hidden="true">[^<]+</span> (\d+ %|—)<span class="c70-sr"> [^<]+</span></a></td>~u', $b) === count($fx) * $cols,
        'names' => str_contains($b, '>' . e($A['label']) . '</a></th>'), 'legend' => str_contains($b, 'class="cm801-legend"'), 'foot' => str_contains($b, '<th scope="row">Třída</th>'), 'region' => str_contains($b, 'class="cm801-wrap" role="region" tabindex="0"'),
        'css' => str_contains($b, 'classmap-v80-1.css') && str_contains($b, 'classmap-v80-1.js'), 'sort' => str_contains($b, '<th scope="col" aria-sort="ascending"><a class="cm801-sort"'),
    ]);
    $t2 = $tget('tab=mapa801&class=class_3a&zdroj=ulohy&oblast=linux&razeni=lnx_chmod&smer=desc&zobrazeni=tabulka');
    $b2 = (string)$t2['body'];
    $lin = count(array_filter(TB75_TASKS, static fn(array $t, string $k): bool => $t['area'] === 'linux' && in_array($k, tb75_tasks_for_class('class_3a'), true), ARRAY_FILTER_USE_BOTH));
    $bogus = $tget('tab=mapa801&class=class_3a&zdroj=%3Cscript%3E&od=99&razeni=x%22%3E&zobrazeni=svg');
    $check('filtry přes GET: úlohy + oblast Linux + řazení sestupně + tabulka (jen text, bez barev); neplatné hodnoty → výchozí mapa bez chyby a bez odrazu vstupu',
        audit_response_clean($t2) && str_contains($b2, 'class="cm801-table is-plain"') && str_contains($b2, 'úlohy s variantami: ' . cm801_count(count($fx), 'žák', 'žáci', 'žáků') . ' × ' . cm801_count($lin, 'sloupec', 'sloupce', 'sloupců') . ', oblast Linux') && str_contains($b2, 'aria-sort="descending"')
        && preg_match('~ z \d+ správně \(\d+ %\)|nezkoušeno~u', $b2) === 1 && audit_response_clean($bogus) && str_contains((string)$bogus['body'], 'kompetence: ' . cm801_count(count($fx), 'žák', 'žáci', 'žáků')) && !str_contains((string)$bogus['body'], '<script>') && !str_contains((string)$bogus['body'], 'x&quot;&gt;') && !str_contains((string)$bogus['body'], 'razeni=x'));

    // ------------------------------------------------------------ skrytá jména (tp721)
    $set = static fn(string $v) => $tpost(['action' => 'tp721_self_names', 'names' => $v, 'return_tab' => 'nastaveni_vyuky', 'csrf' => $csrfOf($tget('tab=nastaveni_vyuky'))]);
    $set('hidden');
    $hb = (string)$tget('tab=mapa801&class=class_3a')['body'];
    $set('shown');
    $leak = array_filter($fx, static fn(array $s): bool => str_contains($hb, e($s['label'])));
    $check('skrytá jména (Nastavení výuky): mapa ukazuje „Žák N“ (pořadí podle jména), žádné jméno žáka v HTML; po zobrazení jména zpět',
        str_contains($hb, '>Žák 1</a></th>') && str_contains($hb, 'Vybrat: Žák ' . count($fx)) && $leak === [] && str_contains((string)$tget('tab=mapa801&class=class_3a')['body'], e($A['label'])));

    // ------------------------------------------------------------ zpětná vazba a přidělení (POST)
    $form = ['class_id' => 'class_3a', 'return_tab' => 'mapa801', 'students' => [$A['key'], $B['key']], 'fraze' => 'b_praise_0', 'text' => '', 'druh' => 'next', 'action' => 'ts801_feedback_send'];
    $no = $tpost($form);
    $nothing = $stored()['fb'] === [];
    $ok = $tpost($form + ['csrf' => $csrfOf($tget('tab=mapa801&class=class_3a'))]);
    $fb = $stored()['fb'];
    $after = (string)$tget('tab=mapa801&class=class_3a')['body'];
    $multi('zpětná vazba: POST bez CSRF → 419 a nic se neuloží; s CSRF → 303 zpět na mapu, 2 zprávy (pochvala z fráze), hláška bez jmen, mapa ukazuje „zpráva …“', [
        '419' => (int)$no['status'] === 419 && $nothing, '303' => (int)$ok['status'] === 303 && str_contains((string)($ok['headers']['Location'] ?? $ok['headers']['location'] ?? ''), 'tab=mapa801'),
        'stored' => count($fb) === 2 && $fb[0]['k'] === 'praise' && $fb[0]['t'] === TS801_PHRASES['praise'][0], 'flash' => str_contains($after, 'Zpětná vazba (pochvala) odeslána 2 žákům.'),
        'column' => str_contains($after, 'zpráva ' . date('j. n.')),
    ]);
    $own = $tpost(['class_id' => 'class_3a', 'return_tab' => 'mapa801', 'students' => [$A['key']], 'fraze' => 'b_praise_1', 'text' => "Vlastní <b>text</b>\nřádek 2", 'druh' => 'fix', 'action' => 'ts801_feedback_send', 'csrf' => $csrfOf($tget('tab=mapa801&class=class_3a'))]);
    $fb = $stored()['fb'];
    $check('zpětná vazba: upravený / vlastní text má přednost před frází, druh z výběru; v seznamu Odesláno je text escapovaný', (int)$own['status'] === 303 && (string)end($fb)['t'] === "Vlastní <b>text</b>\nřádek 2" && (string)end($fb)['k'] === 'fix'
        && str_contains((string)$tget('tab=mapa801&class=class_3a')['body'], 'Vlastní &lt;b&gt;text&lt;/b&gt;<br />'));
    $as = $tpost(['class_id' => 'class_3a', 'return_tab' => 'mapa801', 'students' => [$A['key']], 'polozka' => 'widget:chmod', 'action' => 'ts801_assign', 'csrf' => $csrfOf($tget('tab=mapa801&class=class_3a'))]);
    $as2 = $tpost(['class_id' => 'class_3a', 'return_tab' => 'mapa801', 'students' => [$A['key'], 'class_1a:student:000000000000000000000000'], 'polozka' => 'task:net_route', 'action' => 'ts801_assign', 'csrf' => $csrfOf($tget('tab=mapa801&class=class_3a'))]);
    $rows = $stored()['as'];
    $check('přidělení: POST ts801_assign → 303; cvičení chmod a úloha pro žáka A, cizí klíč se zahodí', (int)$as['status'] === 303 && (int)$as2['status'] === 303 && count($rows) === 2 && $rows[0]['k'] === 'widget' && $rows[1]['i'] === 'net_route'
        && array_unique(array_column($rows, 'ref')) === [$A['ref']]);
    $sel = $tpost(['class_id' => 'class_3a', 'return_tab' => 'hodina', 'students' => [$B['key'], $Cs['key']], 'action' => 'ts801_select', 'csrf' => $csrfOf($tget('tab=mapa801&class=class_3a'))]);
    $sb = (string)$tget('tab=mapa801&class=class_3a')['body'];
    $check('výběr z Komu dnes pomoct (ts801_select): klíče jen v POST, v mapě předvybraní žáci (zaškrtnutí), URL bez dat žáka',
        (int)$sel['status'] === 303 && !str_contains((string)($sel['headers']['Location'] ?? $sel['headers']['location'] ?? ''), 'student') && preg_match_all('~ checked data-cm801-pick~', $sb) === 2 && str_contains($sb, 'value="' . e($B['key']) . '" checked'));
    $jarT = $jarSwap([]);

    // ------------------------------------------------------------ žák
    audit_login_student($h, 'class_3a', $A['label']);
    $sget = static fn(string $q): array => $h->request('GET', '/?' . $q);
    $p = $sget('view=pokrok');
    $pb = (string)$p['body'];
    $nextCard = (string)substr($pb, (int)strpos($pb, 'class="ds-card lr80-next"'), 900);
    $multi('žák A: Můj pokrok – Další krok „Od učitele“ (nejstarší přidělení = cvičení chmod) s jediným primárním tlačítkem; sekce Od učitele se zprávou a přidělením', [
        'clean' => audit_response_clean($p), 'eyebrow' => str_contains($nextCard, 'Další krok · Od učitele'), 'what' => str_contains($nextCard, 'Stavebnice oprávnění chmod'), 'href' => str_contains($nextCard, 'href="?view=cviceni&amp;w=chmod"'),
        'one' => substr_count($nextCard, 'ds-btn--primary') === 1, 'section' => str_contains($pb, '<h2 id="ts801-h">Od učitele</h2>'), 'fb' => str_contains($pb, e(TS801_PHRASES['praise'][0])) && str_contains($pb, 'Vlastní &lt;b&gt;text&lt;/b&gt;<br />'),
        'task' => str_contains($pb, 'Směrovací tabulka'),
    ]);
    $v = wg80_variant('chmod', lr80_ref('class_3a', $A['key']), max(1, (int)((lr80_state('class_3a', lr80_ref('class_3a', $A['key']))['w']['chmod'] ?? [])['r'] ?? 1)));
    $wc = $h->request('POST', '/', ['action' => 'lr80_w_check', 'w' => 'chmod', 'mode' => (string)$v['fields']['mode'], 'csrf' => (string)$h->csrfToken((string)$sget('view=cviceni&w=chmod')['body'])], ['follow_redirects' => false]);
    $p2 = (string)$sget('view=pokrok')['body'];
    $next2 = (string)substr($p2, (int)strpos($p2, 'class="ds-card lr80-next"'), 900);
    $check('žák A: po vyřešení cvičení je přidělení „hotovo“ samo (z výsledku), Další krok přejde na další přidělení (Směrovací tabulka)',
        (int)$wc['status'] === 302 && preg_match('~Stavebnice oprávnění chmod</span> <span class="ds-badge is-ok">hotovo~u', $p2) === 1 && str_contains($next2, 'Směrovací tabulka') && str_contains($next2, 'Od učitele'));
    foreach (['en' => ['From your teacher', 'Next step · From your teacher'], 'uk' => ['Від учителя', 'Наступний крок · Від учителя']] as $lang => [$h2t, $eye]) {
        $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => $lang, 'return' => '?view=pokrok', 'csrf' => (string)$h->csrfToken((string)$sget('view=pokrok')['body'])]);
        $tr[$lang] = (string)$sget('view=pokrok')['body'];
        $trOk[$lang] = str_contains($tr[$lang], '>' . $h2t . '</h2>') && str_contains($tr[$lang], $eye) && str_contains($tr[$lang], 'lang="cs">Vlastní &lt;b&gt;');
    }
    $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'cs', 'return' => '?view=pokrok', 'csrf' => (string)$h->csrfToken((string)$sget('view=pokrok')['body'])]);
    $check('i18n: „Od učitele“ a Další krok v angličtině a ukrajinštině; text zprávy učitele beze změny s lang="cs"', ($trOk['en'] ?? false) && ($trOk['uk'] ?? false));
    $prik = $sget('view=prikazy');
    $pkb = (string)$prik['body'];
    $multi('Linux příkazy: ' . round(strlen($pkb) / 1024, 1) . ' kB HTML ≤ 59 kB (rozpočet v61 62 kB, rezerva ≥ 3 kB); drawer a jazyk v menu účtu jen jako prázdné sloty (JS je naklonuje); menu má cvičení a opakování', [
        'clean' => audit_response_clean($prik), 'size' => strlen($pkb) <= V801_PRIKAZY_MAX, 'drawer' => preg_match('~<div class="nav61-drawer" data-nav61-skip="[^"]*" data-nav61-head="v56-menu-label"></div>~', $pkb) === 1,
        'lang' => str_contains($pkb, '<div class="v56-menu-lang" data-nav61-lang></div>') && substr_count($pkb, 'class="edu-lang-switcher"') === 1, 'menu' => str_contains($pkb, 'href="?view=cviceni">') && str_contains($pkb, 'href="?view=opakovat">'),
        'manual' => str_contains($pkb, 'lab57-manual'),
    ]);
    $jarSwap([]);
    audit_login_student($h, 'class_3a', $Cs['label']);
    $pc = (string)$sget('view=pokrok')['body'];
    $check('soukromí: žák C (jen předvybraný, bez zprávy) nevidí zprávy ani přidělení žáka A; sekce Od učitele se mu nevykreslí', !str_contains($pc, 'ts801-od-ucitele') && !str_contains($pc, 'Vlastní &lt;b&gt;') && !str_contains($pc, 'Od učitele'));
    $jarSwap($jarT);
    $sent = (string)$tget('tab=mapa801&class=class_3a')['body'];
    $wd = $tpost(['class_id' => 'class_3a', 'return_tab' => 'mapa801', 'id' => (string)$stored()['fb'][0]['id'], 'action' => 'ts801_feedback_withdraw', 'csrf' => $csrfOf($tget('tab=mapa801&class=class_3a'))]);
    $check('učitel: v „Odesláno a přiděleno“ stav přidělení (hotovo / čeká) a zprávy; stažení zprávy (POST) ji odebere',
        str_contains($sent, 'Stavebnice oprávnění chmod <span class="ds-badge is-ok">hotovo</span>') && str_contains($sent, 'Zrušit přidělení') && (int)$wd['status'] === 303 && count($stored()['fb']) === 2);
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- 4) prohlížeč
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$mode = (string)getenv('EDUCANET_V801_BROWSER');
if ($mode === 'skip') echo 'SKIP  prohlížečová část (EDUCANET_V801_BROWSER=skip)' . PHP_EOL;
else (static function () use ($check, $env, $node, $nodePath, $mode, $A): void {   // před režimem účtů (prohlížeč se přihlašuje učitelským klíčem)
$bh = Harness::start($env);
try {
    $shots = (string)getenv('EDUCANET_V801_SHOTS');
    $cmd = escapeshellarg($node) . ' ' . escapeshellarg(__DIR__ . '/v80_1_teacher_browser.cjs') . ' ' . escapeshellarg($bh->baseUrl()) . ' ' . escapeshellarg(V801_KEY) . ' ' . escapeshellarg($A['label']) . ($shots !== '' ? ' ' . escapeshellarg($shots) : '');
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    $errOut = is_resource($proc) ? (string)stream_get_contents($pipes[2]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn ' . substr(trim($errOut), 0, 200);
    if ($mode === 'require') $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_V801_BROWSER=require ji vynutí)' . PHP_EOL;
    return;
}
$rows = (array)($d['rows'] ?? []);
$check('prohlížeč: skript doběhl (4 stránky × 11 šířek × světlý/tmavý = 88 měření)' . (isset($d['error']) ? ' [' . $d['error'] . ']' : ''), !isset($d['error']) && count($rows) === 88);
$bad = array_filter($rows, static fn(array $r): bool => $r['hscroll'] || $r['smallCount'] > 0 || $r['tinyCount'] > 0);
$check('prohlížeč: 11 šířek (320–1920, 1366 × 600) × světlý/tmavý – 0× vodorovný posun stránky, 0 cílů < 44 px, 0 textů < 14 px v obsahu v80.1'
    . ($bad ? ' [' . implode('; ', array_map(static fn(array $r): string => $r['page'] . '@' . $r['width'] . $r['scheme'][0] . ' h=' . (int)$r['hscroll'] . ' s=' . implode(',', (array)$r['small']) . ' t=' . implode(',', (array)$r['tiny']), array_slice($bad, 0, 4))) . ']' : ''), $bad === []);
$dark = array_filter($rows, static fn(array $r): bool => $r['scheme'] === 'dark' && $r['page'] !== 'prikazy' && !$r['darkBg']);   // Linux příkazy nemají tmavý režim (mimo UI67_DARK_VIEWS) – zůstávají světlé
$light = array_filter($rows, static fn(array $r): bool => $r['scheme'] === 'light' && $r['darkBg']);
$check('prohlížeč: tmavé zařízení → tmavá mapa i Můj pokrok, světlé → světlé; Linux příkazy (bez tmavého režimu) světlé vždy' . ($dark ? ' [' . implode(', ', array_map(static fn(array $r): string => $r['page'] . '@' . $r['width'], array_slice($dark, 0, 4))) . ']' : ''), $dark === [] && $light === []);
$in = (array)($d['interact'] ?? []);
$check('interakce: lepicí hlavička i první sloupec při posunu mapy, klik na buňku → detail žáka, Vybrat všechny → počet (aria-live), fráze → text a druh, „kdo potřebuje pomoc“ vybere jen žáky s „!“ ' . json_encode($in),
    ($in['stickyCol'] ?? false) && ($in['stickyHead'] ?? false) && ($in['cellNav'] ?? false) && ($in['selectAll'] ?? false) && ($in['phrase'] ?? false) && ($in['weak'] ?? false));
$mn = (array)($d['menu'] ?? []);
$check('menu účtu žáka (390 px): drawer naklonovaný z horní lišty (skupiny + odkazy vč. Interaktivní cvičení a Opakování chyb, bez cílů spodní lišty), jazyk v menu, bez chyb JS ' . json_encode($mn),
    ($mn['labels'] ?? 0) >= 4 && ($mn['links'] ?? 0) >= 15 && ($mn['cviceni'] ?? false) && !($mn['bottomDup'] ?? true) && ($mn['lang'] ?? false) && ($mn['errors'] ?? 1) === 0);
if (!empty($d['shots'])) echo 'INFO  snímky: ' . count((array)$d['shots']) . ' (největší ' . round((int)($d['maxShot'] ?? 0) / 1024) . ' kB)' . PHP_EOL;
})();

// ---------------------------------------------------------------- rozsah tříd a asistent (režim učitelských účtů)
require_once __DIR__ . '/v59_scope_fixtures.php';
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cache]);
$jar2 = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
try {
    $count = static fn(): int => count($stored()['fb']);
    $before = $count();
    audit_login_teacher_account($h2, $acc['login']['a'], $acc['pw']['a']);
    $ownA = $h2->request('GET', '/teacher.php?tab=mapa801&class=class_1a');
    $foreign = $h2->request('GET', '/teacher.php?tab=mapa801&class=class_3a');
    $csrfA = (string)$h2->csrfToken((string)$ownA['body']);
    $postF = $h2->request('POST', '/teacher.php', ['action' => 'ts801_feedback_send', 'class_id' => 'class_3a', 'students' => [$A['key']], 'fraze' => 'b_next_0', 'return_tab' => 'mapa801', 'csrf' => $csrfA], ['follow_redirects' => false]);
    $jar2([]);
    audit_login_teacher_account($h2, $acc['login']['s'], $acc['pw']['s']);
    $asst = $h2->request('GET', '/teacher.php?tab=mapa801&class=class_3a');
    $postS = $h2->request('POST', '/teacher.php', ['action' => 'ts801_feedback_send', 'class_id' => 'class_3a', 'students' => [$A['key']], 'fraze' => 'b_next_0', 'return_tab' => 'mapa801', 'csrf' => (string)$h2->csrfToken((string)$asst['body'])], ['follow_redirects' => false]);
    $asst2 = (string)$h2->request('GET', '/teacher.php?tab=mapa801&class=class_3a')['body'];
    $jar2([]);
    audit_login_teacher_account($h2, $acc['login']['b'], $acc['pw']['b']);
    $mb = $h2->request('GET', '/teacher.php?tab=mapa801&class=class_3a');
    $postB = $h2->request('POST', '/teacher.php', ['action' => 'ts801_feedback_send', 'class_id' => 'class_3a', 'students' => [$A['key']], 'fraze' => 'b_next_0', 'return_tab' => 'mapa801', 'csrf' => (string)$h2->csrfToken((string)$mb['body'])], ['follow_redirects' => false]);
    $multi('rozsah: učitel 1.A/2.A vidí mapu své třídy, cizí 3.A → 403 a POST do 3.A → 403 bez zápisu; asistent 3.A mapu jen čte (bez formuláře), POST zamítnut bez zápisu; učitel 3.A pošle', [
        'ownA' => audit_response_clean($ownA), 'foreign403' => (int)$foreign['status'] === 403, 'postA403' => (int)$postF['status'] === 403,
        'asstRead' => audit_response_clean($asst) && !str_contains((string)$asst['body'], 'name="students[]"') && str_contains((string)$asst['body'], 'asistent mapu jen čte'),
        'asstDenied' => (int)$postS['status'] === 303 && str_contains($asst2, 'teacher-flash error'), 'noWrite' => $count() === $before + 1, 'teacherB' => (int)$postB['status'] === 303,
    ]);
} finally {
    $h2->stop();
}

