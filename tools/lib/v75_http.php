<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · HTTP část tools/v75_http_audit.php (načítá se z něj; používá $tmp, $cacheDir, $check, $label3, $label2).
 *   a) bez účtů (klíč učitele, vývojový vstup žáka): cockpit 5 záložek, schválení, žák úlohy / úroveň / portfolio / opakování, en/uk, tisk,
 *   b) v režimu účtů: cizí třída 403, asistent nezapisuje, stránka k tisku jen třída v rozsahu.
 */

$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V75H_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$redirect = static fn(array $r): bool => in_array((int)$r['status'], [302, 303], true);
$h = Harness::start($env);
try {
    audit_login_teacher($h, V75H_KEY, 'Audit Učitel');
    $bad = [];
    foreach ([['kompetence75', 'class_2a'], ['kompetence75', 'class_4a'], ['ulohy75', 'class_3a'], ['ulohy75', 'class_1a'], ['urovne75', 'class_3a'], ['portfolia75', 'class_3a'], ['opakovani75', 'class_3a'], ['hodina', 'class_3a']] as [$tab, $class]) {
        $p = $h->request('GET', '/teacher.php', ['tab' => $tab, 'class' => $class]);
        if (!audit_response_clean($p) || ($tab !== 'hodina' && !str_contains((string)$p['body'], 'assets/cockpit-v75.css'))) $bad[] = $tab . ':' . $class;
    }
    $check('cockpit: 5 záložek v75 (a Dnešní hodina) – čistá odpověď 200 s CSS v75' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
    $kp = (string)$h->request('GET', '/teacher.php', ['tab' => 'kompetence75', 'class' => 'class_4a'])['body'];
    $empty1a = (string)$h->request('GET', '/teacher.php', ['tab' => 'ulohy75', 'class' => 'class_1a'])['body'];
    $check('cockpit: Kompetence 4.A ukazují návrh (stav, katalog s ŠVP, pokrytí, náhled cest, potvrzení); Úlohy 1.A mají grafickou sadu (v76)', str_contains($kp, 'Návrh čeká na schválení')
        && str_contains($kp, 'ids26-sbe4-03') && str_contains($kp, 'Reverzní proxy a certifikát TLS') && str_contains($kp, 'name="confirm" value="1" required') && str_contains($empty1a, 'Barvy: HEX a RGB'));   // v76: 1.A má sadu úloh (změna limitu auditu)
    $csrf = (string)$h->csrfToken($kp);
    $no = $h->request('POST', '/teacher.php', ['action' => 'kp75_approve', 'class_id' => 'class_2a', 'confirm' => '1'], ['follow_redirects' => false]);
    $noConfirm = $h->request('POST', '/teacher.php', ['action' => 'kp75_approve', 'class_id' => 'class_2a', 'csrf' => $csrf, 'return_tab' => 'kompetence75'], ['follow_redirects' => false]);
    $mid = comp75_status('class_2a', storage_read(comp75_status_path(), false))['status'];
    $ok = $h->request('POST', '/teacher.php', ['action' => 'kp75_approve', 'class_id' => 'class_2a', 'confirm' => '1', 'csrf' => $csrf, 'return_tab' => 'kompetence75'], ['follow_redirects' => false]);
    $check('kp75_approve: bez CSRF 419, bez potvrzení nic; s potvrzením 2.A schválená (přesměrování zpět)', (int)$no['status'] === 419 && $mid === 'navrh' && $redirect($ok)
        && comp75_status('class_2a', storage_read(comp75_status_path(), false))['status'] === 'schvaleno');
    $ur = (string)$h->request('GET', '/teacher.php', ['tab' => 'urovne75', 'class' => 'class_3a'])['body'];
    $lessons = [];
    for ($i = 1; $i <= 16; $i++) $lessons[] = (string)$i;
    $h->request('POST', '/teacher.php', ['action' => 'lv75_approve', 'class_id' => 'class_3a', 'lessons' => ['1'], 'csrf' => (string)$h->csrfToken($ur), 'return_tab' => 'urovne75'], ['follow_redirects' => false]);
    $check('lv75_approve bez potvrzení nic neschválí (kontrola i na serveru)', lv75_status('class_3a', 1)['status'] !== 'schvaleno');
    $lvOk = $h->request('POST', '/teacher.php', ['action' => 'lv75_approve', 'class_id' => 'class_3a', 'lessons' => $lessons, 'confirm' => '1', 'csrf' => (string)$h->csrfToken($ur), 'return_tab' => 'urovne75'], ['follow_redirects' => false]);
    $check('urovne75: návrhy L1–L16 s tlačítkem hromadného schválení; lv75_approve schválí 16 lekcí 3.A', str_contains($ur, 'Schválit všechny čekající (28)') /* v76: L1–L28 */ && str_contains($ur, 'Zatím si nikdo úroveň nevybral') && $redirect($lvOk)
        && lv75_status('class_3a', 1)['status'] === 'schvaleno' && lv75_status('class_3a', 16)['status'] === 'schvaleno');

    // --- žák 3.A
    $h->request('GET', '/', ['class' => 'class_3a', 'student' => $label3]);
    $list = $h->request('GET', '/', ['view' => 'ulohy']);
    $lb = (string)$list['body'];
    $check('žák: Úlohy s variantami – 7 úloh, vysvětlení (jiná čísla, nehodnotí se), odkaz v menu Učení', audit_response_clean($list) && substr_count($lb, 'class="l75-card"') === 7 && str_contains($lb, 'opsat výsledek od souseda nejde')
        && str_contains($lb, '?view=ulohy') && str_contains($lb, 'assets/learn-v75.css'));
    $task = (string)$h->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'net_ports'])['body'];
    preg_match_all('~for="tb75-net_ports-(p\d)"[^>]*>Port služby ([^<]+)</label>~u', $task, $pm, PREG_SET_ORDER);
    $table = tb75_ports_table();
    $answers = [];
    foreach ($pm as $row) $answers[$row[1]] = (string)($table[html_entity_decode($row[2], ENT_QUOTES)] ?? '');
    $tCsrf = (string)$h->csrfToken($task);
    $noC = $h->request('POST', '/', ['action' => 'tb75_answer', 'task' => 'net_ports', 'a' => ['p0' => '1']], ['follow_redirects' => false]);
    $wrong = $h->request('POST', '/', ['action' => 'tb75_answer', 'task' => 'net_ports', 'a' => ['p0' => '1', 'p1' => '2', 'p2' => '3'], 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $partial = $h->request('POST', '/', ['action' => 'tb75_answer', 'task' => 'net_ports', 'a' => ['p0' => (string)($answers['p0'] ?? ''), 'p1' => '1', 'p2' => '2'], 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $check('žák: správně vyplněné pole zůstane po kontrole předvyplněné (jen ke čtení), chybná pole jsou prázdná', str_contains((string)$partial['body'], 'Správně 1 z 3 polí')
        && preg_match('~id="tb75-net_ports-p0"[^>]*value="' . preg_quote((string)($answers['p0'] ?? '-'), '~') . '" readonly~', (string)$partial['body']) === 1 && preg_match('~id="tb75-net_ports-p1"[^>]*readonly~', (string)$partial['body']) === 0);
    $right = $h->request('POST', '/', ['action' => 'tb75_answer', 'task' => 'net_ports', 'a' => $answers, 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $check('žák: úloha bez CSRF 419; chybná odpověď = „Správně 0 z 3“ a „zkus znovu“; správná (spočítaná z tabulky IANA) = „všechna pole sedí“ a Další varianta', (int)$noC['status'] === 419 && count($answers) === 3
        && str_contains((string)$wrong['body'], 'Správně 0 z 3 polí') && str_contains((string)$wrong['body'], 'zkus znovu') && str_contains((string)$right['body'], 'Správně, všechna pole sedí') && str_contains((string)$right['body'], 'value="tb75_next"'));
    $lesson = $h->request('GET', '/', ['view' => 'lekce', 'n' => '1']);
    $lsb = (string)$lesson['body'];
    $check('žák: lekce 1 nabízí Základ / Standard / Výzva s doporučením a vysvětleným důvodem (bez dat = Standard)', audit_response_clean($lesson) && str_contains($lsb, 'Vyber si úroveň')
        && substr_count($lsb, 'value="dif75_pick"') === 3 && str_contains($lsb, 'Zatím nemáme dost tvých výsledků'));
    $pick = $h->request('POST', '/', ['action' => 'dif75_pick', 'lesson' => '1', 'level' => 'vyzva', 'csrf' => (string)$h->csrfToken($lsb)], ['follow_redirects' => true]);
    $future = $h->request('POST', '/', ['action' => 'dif75_pick', 'lesson' => '28', 'level' => 'vyzva', 'csrf' => (string)$h->csrfToken($lsb)], ['follow_redirects' => false]);
    $check('žák: volba Výzvy se uloží („tvoje volba“), budoucí lekci zvolit nejde; učitel vidí rozložení bez jmen', str_contains((string)$pick['body'], 'tvoje volba') && (lv75_distribution('class_3a')[1]['vyzva'] ?? 0) === 1
        && $redirect($future) && !isset(lv75_distribution('class_3a')[28]));
    $pf = (string)$h->request('GET', '/', ['view' => 'portfolio'])['body'];
    $pinBad = $h->request('POST', '/', ['action' => 'pf75_s_pin', 'kind' => 'link', 'title' => 'Plakát', 'url' => 'http://example.org/x', 'csrf' => (string)$h->csrfToken($pf)], ['follow_redirects' => true]);
    $pinOk = $h->request('POST', '/', ['action' => 'pf75_s_pin', 'kind' => 'link', 'title' => 'Můj plakát v75', 'url' => 'https://example.org/plakat', 'reflection' => 'Hierarchie a kontrast.', 'csrf' => (string)$h->csrfToken($pf)], ['follow_redirects' => true]);
    $pinTask = $h->request('POST', '/', ['action' => 'pf75_s_pin', 'kind' => 'task', 'task' => 'net_ports', 'csrf' => (string)$h->csrfToken($pf)], ['follow_redirects' => true]);
    $check('žák: portfolio – sekce Připnuté práce (prázdný stav vysvětlený), http odmítnut, https odkaz a vyřešená úloha připnuté s reflexí, odkaz rel="noopener noreferrer"', str_contains($pf, 'Připnuté práce')
        && str_contains($pf, 'Zatím tu nic není') && str_contains((string)$pinBad['body'], 'musí začínat https://') && str_contains((string)$pinOk['body'], 'Můj plakát v75')
        && str_contains((string)$pinOk['body'], 'rel="noopener noreferrer"') && str_contains((string)$pinTask['body'], 'Porty a služby – vyřešeno 1×'));

    // --- učitel: portfolio, opakování, tisk
    $students = array_keys(project_students_for_class('class_3a'));
    $zak = 0;
    foreach ($students as $i => $k) if (pf75_pins('class_3a', (string)$k) !== []) $zak = $i + 1;
    $pt = (string)$h->request('GET', '/teacher.php', ['tab' => 'portfolia75', 'class' => 'class_3a', 'zak' => (string)$zak])['body'];
    preg_match('~name="pin" value="(p[0-9a-f]{10})"~', $pt, $pinId);
    $cm = $h->request('POST', '/teacher.php', ['action' => 'pf75_comment', 'class_id' => 'class_3a', 'zak' => (string)$zak, 'pin' => (string)($pinId[1] ?? ''), 'comment' => 'Pěkná hierarchie!', 'csrf' => (string)$h->csrfToken($pt), 'return_tab' => 'portfolia75'], ['follow_redirects' => false]);
    $print = $h->request('GET', '/teacher.php', ['tab' => 'portfolia75', 'class' => 'class_3a', 'zak' => (string)$zak, 'tisk' => '1']);
    $check('cockpit: portfolio žáka (pořadí v seznamu, ne jméno v URL) s reflexí; komentář uložen; verze k tisku = samostatná stránka s print-v75 bez navigace cockpitu',
        $zak > 0 && str_contains($pt, 'Můj plakát v75') && $redirect($cm) && str_contains((string)$print['body'], 'Komentář učitele:') && str_contains((string)$print['body'], 'assets/print-v75.css')
        && str_contains((string)$print['body'], 'data-pr75-print') && !str_contains((string)$print['body'], 'teacher-shell') && str_contains((string)($print['headers']['Cache-Control'] ?? ''), 'no-store'));
    $op = (string)$h->request('GET', '/teacher.php', ['tab' => 'opakovani75', 'class' => 'class_3a', 'od' => '5', 'do' => '16'])['body'];
    $pub = $h->request('POST', '/teacher.php', ['action' => 'op75_publish', 'class_id' => 'class_3a', 'od' => '5', 'do' => '16', 'csrf' => (string)$h->csrfToken($op), 'return_tab' => 'opakovani75'], ['follow_redirects' => false]);
    $opPrint = (string)$h->request('GET', '/teacher.php', ['tab' => 'opakovani75', 'class' => 'class_3a', 'od' => '5', 'do' => '16', 'tisk' => '1'])['body'];
    $check('cockpit: opakování L5–L16 – náhled (kroky 1–3, úlohy k procvičení), zveřejnění, verze k tisku s klíčem bez jmen', str_contains($op, '2. Náhled balíčku L5–L16') && str_contains($op, 'value="op75_publish"')
        && $redirect($pub) && rv75_pack('class_3a') !== null && str_contains($opPrint, 'Verze pro učitele s klíčem') && !str_contains($opPrint, e($label3)));
    $stuOp = $h->request('GET', '/', ['view' => 'opakovani']);
    $sob = (string)$stuOp['body'];
    $check('žák: Opakování před písemkou – rozsah, „nejde o tvoje chyby“, vlastní varianty úloh k procvičení, odkaz v menu', audit_response_clean($stuOp) && str_contains($sob, 'Lekce 5–16')
        && str_contains($sob, 'nejde o tvoje chyby') && str_contains($sob, 'value="tb75_answer"') && str_contains($sob, '?view=opakovani'));
    $pfAfter = (string)$h->request('GET', '/', ['view' => 'portfolio'])['body'];
    $check('žák: komentář učitele se ukáže u práce v portfoliu', str_contains($pfAfter, 'Komentář učitele:') && str_contains($pfAfter, 'Pěkná hierarchie!'));
    $lang = $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'en', 'return' => '?view=ulohy', 'csrf' => (string)$h->csrfToken($pfAfter)], ['follow_redirects' => true]);
    $en = (string)$h->request('GET', '/', ['view' => 'opakovani'])['body'];
    $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'uk', 'return' => '?view=ulohy', 'csrf' => (string)$h->csrfToken($en)], ['follow_redirects' => true]);
    $uk = (string)$h->request('GET', '/', ['view' => 'ulohy'])['body'];
    $h->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'cs', 'return' => '?view=ulohy', 'csrf' => (string)$h->csrfToken($uk)], ['follow_redirects' => true]);
    $check('jazyk: en „Tasks with variants“ / „Review before the test“, uk „Завдання з варіантами“; zadání úloh zůstává česky (lang="cs")', str_contains((string)$lang['body'], 'Tasks with variants')
        && str_contains($en, 'Review before the test') && str_contains($uk, 'Завдання з варіантами') && str_contains($en, 'lang="cs"'));

    // --- žák 2.A po schválení kompetencí
    $h->request('GET', '/', ['class' => 'class_2a', 'student' => $label2]);
    $paths = $h->request('GET', '/', ['view' => 'cesty']);
    $u2 = (string)$h->request('GET', '/', ['view' => 'ulohy'])['body'];
    $check('žák 2.A: po schválení má výukové cesty (Responzivní rozvržení, Audit přístupnosti, Typografická škála); úlohy 2.A mají grafickou sadu (v76)', audit_response_clean($paths)
        && str_contains((string)$paths['body'], 'Responzivní rozvržení') && str_contains((string)$paths['body'], 'Typografická škála') && str_contains($u2, 'Barvy: HEX a RGB'));   // v76: 2.A má sadu úloh (změna limitu auditu)
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- b) režim účtů: rozsah tříd a role
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V75H_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
$jars = [];
try {
    $as = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h2, $jarSwap, &$jars, $acc): array {
        if (!isset($jars[$who])) {
            $jarSwap([]);
            audit_login_teacher_account($h2, $acc['login'][$who], $acc['pw'][$who]);
            $jars[$who] = $jarSwap(null);
        }
        $jarSwap($jars[$who]);
        $r = $h2->request($method, $path, $fields, $headers);
        $jars[$who] = $jarSwap(null);
        return $r;
    };
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'urovne75', 'class' => 'class_1a']);
    $aCsrf = (string)$h2->csrfToken((string)$aPage['body']);
    $aGet = $as('a', 'GET', '/teacher.php', ['tab' => 'ulohy75', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $aPost = $as('a', 'POST', '/teacher.php', ['action' => 'lv75_return', 'class_id' => 'class_3a', 'lessons' => ['2'], 'note' => 'cizí', 'csrf' => $aCsrf], ['follow_redirects' => false]);
    $aPrint = $as('a', 'GET', '/teacher.php', ['tab' => 'opakovani75', 'class' => 'class_3a', 'od' => '5', 'do' => '6', 'tisk' => '1'], ['follow_redirects' => false]);
    $aKp = $as('a', 'POST', '/teacher.php', ['action' => 'kp75_return', 'class_id' => 'class_4a', 'note' => 'cizí', 'csrf' => $aCsrf], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A – Úlohy 3.A GET 403, lv75_return 3.A 403, tisk opakování 3.A 403, kp75_return 4.A 403; nic se nezmění', audit_response_clean($aPage) && (int)$aGet['status'] === 403
        && (int)$aPost['status'] === 403 && (int)$aPrint['status'] === 403 && (int)$aKp['status'] === 403 && lv75_status('class_3a', 2)['status'] === 'schvaleno' && comp75_status('class_4a')['status'] === 'navrh');
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'portfolia75', 'class' => 'class_3a', 'zak' => '1']);
    $sb = (string)$sPage['body'];
    $sPost = $as('s', 'POST', '/teacher.php', ['action' => 'lv75_return', 'class_id' => 'class_3a', 'lessons' => ['3'], 'note' => 'asistent', 'csrf' => (string)$h2->csrfToken($sb)], ['follow_redirects' => false]);
    $check('účty: asistent 3.A čte portfolia bez formuláře komentáře; lv75_return zamítnut oprávněním, nic se nezmění [' . (int)$sPost['status'] . ']', audit_response_clean($sPage)
        && !str_contains($sb, 'value="pf75_comment"') && in_array((int)$sPost['status'], [302, 303, 403], true) && lv75_status('class_3a', 3)['status'] === 'schvaleno');
    $bPage = $as('b', 'GET', '/teacher.php', ['tab' => 'kompetence75', 'class' => 'class_4a']);
    $bPost = $as('b', 'POST', '/teacher.php', ['action' => 'kp75_approve', 'class_id' => 'class_4a', 'confirm' => '1', 'csrf' => (string)$h2->csrfToken((string)$bPage['body']), 'return_tab' => 'kompetence75'], ['follow_redirects' => false]);
    $check('účty: učitel 3.A/4.A schválí kompetence 4.A (autor = jméno účtu)', audit_response_clean($bPage) && $redirect($bPost) && comp75_status('class_4a')['status'] === 'schvaleno' && comp75_status('class_4a')['by'] !== '');
} finally {
    $h2->stop();
}
