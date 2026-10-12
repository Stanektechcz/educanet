<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · HTTP část tools/v76_http_audit.php (používá $tmp, $cacheDir, $check, $label3, $label1, $sid3).
 *   a) bez účtů (klíč učitele, vývojový vstup žáka): záložky plan76 / svp76 / kompetence, tisk, CSV, pozorování (CSRF 419), žák 1.A úlohy a export portfolia,
 *   b) v režimu účtů: cizí třída 403 (tisk, CSV, pozorování), asistent pozorování nezapíše.
 */

require_once dirname(__DIR__, 2) . '/observation_v76.php';
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V76H_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$redirect = static fn(array $r): bool => in_array((int)$r['status'], [302, 303], true);
$h = Harness::start($env);
try {
    audit_login_teacher($h, V76H_KEY, 'Audit Učitel');
    $bad = [];
    $pages = [];
    foreach ([['plan76', 'class_3a', []], ['plan76', 'class_1a', []], ['svp76', 'class_3a', ['pololeti' => '1']], ['svp76', 'class_4a', ['pololeti' => '2']], ['kompetence', 'class_3a', []]] as [$tab, $class, $extra]) {
        $p = $h->request('GET', '/teacher.php', ['tab' => $tab, 'class' => $class] + $extra);
        $pages[$tab . ':' . $class] = (string)$p['body'];
        if (!audit_response_clean($p) || !str_contains((string)$p['body'], 'assets/cockpit-v76.css')) $bad[] = $tab . ':' . $class;
    }
    $check('cockpit: Plán vs. skutečnost (3.A, 1.A), Plnění ŠVP (3.A, 4.A) a Kompetence – čistá odpověď 200 s CSS v76' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
    $pl = $pages['plan76:class_3a'];
    $check('plan76: vysvětlení, ukazatele (zpoždění, prognóza „stihnete X z 28“), tabulka 28 lekcí se stavy, odkazy na reflexi a kalendář, verze k tisku',
        str_contains($pl, '<h1>Plán vs. skutečnost</h1>') && str_contains($pl, 'Zpoždění vůči plánu') && preg_match('/stihnete \d+ z 28/u', $pl) === 1
        && substr_count($pl, '<tr><th scope="row">') === 28 && str_contains($pl, 'tisk=1') && str_contains($pl, 'Jak dohnat') && str_contains($pl, 'Nic se samo nezapisuje'));
    $sv = $pages['svp76:class_3a'];
    $check('svp76: pololetí (aria-current), ukazatele (naplněno, mezery, čeká na schválení vazby), tabulka výsledků s předmětem a lekcemi, zdroj ŠVP, CSV a tisk',
        str_contains($sv, '<h1>Plnění ŠVP</h1>') && str_contains($sv, 'aria-current="page">1. pololetí') && str_contains($sv, 'Čeká na schválení vazby') && str_contains($sv, 'ids26-')
        && str_contains($sv, 'EDU2026/938') && str_contains($sv, 'csv=1') && str_contains($sv, 'Schválit vazby na ŠVP'));
    $pp = $h->request('GET', '/teacher.php', ['tab' => 'plan76', 'class' => 'class_3a', 'tisk' => '1']);
    $sp = $h->request('GET', '/teacher.php', ['tab' => 'svp76', 'class' => 'class_3a', 'pololeti' => '1', 'tisk' => '1']);
    $csv = $h->request('GET', '/teacher.php', ['tab' => 'svp76', 'class' => 'class_3a', 'pololeti' => '1', 'csv' => '1']);
    $csvBody = (string)$csv['body'];
    $check('tisk a CSV: samostatné stránky (print-v75/v76, tlačítko tisku, bez navigace cockpitu, no-store, bez jmen žáků); CSV text/csv s BOM, středníkem a bez jmen',
        (int)$pp['status'] === 200 && str_contains((string)$pp['body'], 'assets/print-v76.css') && str_contains((string)$pp['body'], 'data-pr75-print') && !str_contains((string)$pp['body'], 'teacher-shell')
        && str_contains((string)($pp['headers']['Cache-Control'] ?? ''), 'no-store') && (int)$sp['status'] === 200 && str_contains((string)$sp['body'], 'Plnění ŠVP · 3.A · 1. pololetí')
        && str_contains((string)($csv['headers']['Content-Type'] ?? ''), 'text/csv') && str_starts_with($csvBody, "\xEF\xBB\xBFtrida;pololeti;") && !str_contains($csvBody, $label3)
        && !str_contains((string)$pp['body'], $label3) && !str_contains((string)$sp['body'], $label3));
    $km = $pages['kompetence:class_3a'];
    $idx = (int)array_search($sid3, array_column(ob76_students('class_3a'), 'id'), true) + 1;
    $no = $h->request('POST', '/teacher.php', ['action' => 'ob76_add', 'class_id' => 'class_3a', 'zak' => (string)$idx, 'competency' => 'net_dns_dhcp', 'vysledek' => 'umi'], ['follow_redirects' => false]);
    $ok = $h->request('POST', '/teacher.php', ['action' => 'ob76_add', 'class_id' => 'class_3a', 'zak' => (string)$idx, 'competency' => 'net_dns_dhcp', 'vysledek' => 'uci', 'csrf' => (string)$h->csrfToken($km)], ['follow_redirects' => true]);
    $obs = array_values(array_filter(ev62_read($sid3), static fn(array $r): bool => $r['source'] === 'observation'));
    $check('pozorování jedním klikem: formulář (žák, kompetence, 3 tlačítka výsledku) v Kompetencích; bez CSRF 419; s CSRF zápis důkazu (učí se = 0,5) a potvrzení učiteli',
        str_contains($km, 'Zapsat pozorování jedním klikem') && substr_count($km, 'name="vysledek"') === 3 && str_contains($km, 'value="ob76_add"') && (int)$no['status'] === 419
        && str_contains((string)$ok['body'], 'Pozorování zapsáno') && count($obs) === 1 && abs((float)$obs[0]['score'] - 0.5) < 1e-9);
    // --- žák 1.A: grafické úlohy a export portfolia
    $h->request('GET', '/', ['class' => 'class_1a', 'student' => $label1]);
    $list = $h->request('GET', '/', ['view' => 'ulohy']);
    $lb = (string)$list['body'];
    $check('žák 1.A: Úlohy s variantami – 4 grafické úlohy (Barvy: HEX a RGB, Jednotky CSS, Selektory a specificita, Rozlišení a DPI), odkaz v menu',
        audit_response_clean($list) && substr_count($lb, 'class="l75-card"') === 4 && str_contains($lb, 'Barvy: HEX a RGB') && str_contains($lb, 'Rozlišení obrázku a DPI') && str_contains($lb, '?view=ulohy'));
    $ref1 = tb75_student_ref('class_1a', v63fx_key('class_1a', 'a'));
    $variant = tb75_current('class_1a', $ref1, 'gfx_color')['variant'];
    $task = (string)$h->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'gfx_color'])['body'];
    $ans = array_column($variant['fields'], 'answer', 'id');
    $right = $h->request('POST', '/', ['action' => 'tb75_answer', 'task' => 'gfx_color', 'a' => ['hex' => strtoupper($ans['hex']), 'rgb' => str_replace(',', ', ', $ans['rgb']), 'inv' => ltrim($ans['inv'], '#')],
        'csrf' => (string)$h->csrfToken($task)], ['follow_redirects' => true]);
    $check('žák 1.A: zadání ukazuje přesně variantu ze serveru; odpověď v jiném zápisu (VELKÉ HEX, „r, g, b“, bez #) projde – „všechna pole sedí“',
        str_contains($task, e((string)$variant['fields'][0]['label'])) && str_contains((string)$right['body'], 'Správně, všechna pole sedí'));
    $pf = (string)$h->request('GET', '/', ['view' => 'portfolio'])['body'];
    $h->request('POST', '/', ['action' => 'pf75_s_pin', 'kind' => 'task', 'task' => 'gfx_color', 'reflection' => 'HEX umím převést.', 'csrf' => (string)$h->csrfToken($pf)], ['follow_redirects' => true]);
    $exp = $h->request('GET', '/', ['view' => 'portfolio_export']);
    $eb = (string)$exp['body'];
    $check('žák: export portfolia (HTML ke stažení) obsahuje připnuté práce s reflexí, bez odkazů a skriptů (CSP default-src none)', (int)$exp['status'] === 200
        && str_contains((string)($exp['headers']['Content-Disposition'] ?? ''), 'attachment') && str_contains($eb, 'Připnuté práce') && str_contains($eb, 'HEX umím převést.')
        && preg_match('/<a\s|<script/i', $eb) !== 1);
} finally {
    $h->stop();
}
// ---------------------------------------------------------------- b) režim účtů: rozsah tříd a role
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V76H_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
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
    $before = count(ev62_read($sid3));
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'plan76', 'class' => 'class_1a']);
    $aCsrf = (string)$h2->csrfToken((string)$aPage['body']);
    $aPrint = $as('a', 'GET', '/teacher.php', ['tab' => 'plan76', 'class' => 'class_3a', 'tisk' => '1'], ['follow_redirects' => false]);
    $aCsv = $as('a', 'GET', '/teacher.php', ['tab' => 'svp76', 'class' => 'class_3a', 'csv' => '1'], ['follow_redirects' => false]);
    $aObs = $as('a', 'POST', '/teacher.php', ['action' => 'ob76_add', 'class_id' => 'class_3a', 'zak' => '1', 'competency' => 'net_dns_dhcp', 'vysledek' => 'umi', 'csrf' => $aCsrf], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A – Plán 1.A 200; tisk plánu 3.A 403, CSV ŠVP 3.A 403, pozorování 3.A 403; nic se nezapíše', audit_response_clean($aPage) && (int)$aPrint['status'] === 403
        && (int)$aCsv['status'] === 403 && (int)$aObs['status'] === 403 && count(ev62_read($sid3)) === $before);
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'kompetence', 'class' => 'class_3a']);
    $sb = (string)$sPage['body'];
    $sObs = $as('s', 'POST', '/teacher.php', ['action' => 'ob76_add', 'class_id' => 'class_3a', 'zak' => '1', 'competency' => 'net_dns_dhcp', 'vysledek' => 'umi', 'csrf' => (string)$h2->csrfToken($sb)], ['follow_redirects' => false]);
    $check('účty: asistent 3.A vidí mapu kompetencí bez formuláře pozorování; ob76_add zamítnut oprávněním (grading.manage), nic se nezapíše [' . (int)$sObs['status'] . ']',
        audit_response_clean($sPage) && !str_contains($sb, 'value="ob76_add"') && str_contains($sb, 'asistent mapu jen čte') && in_array((int)$sObs['status'], [302, 303, 403], true) && count(ev62_read($sid3)) === $before);
    $bCsv = $as('b', 'GET', '/teacher.php', ['tab' => 'svp76', 'class' => 'class_3a', 'pololeti' => '2', 'csv' => '1'], ['follow_redirects' => false]);
    $check('účty: učitel 3.A/4.A stáhne CSV plnění ŠVP 3.A za 2. pololetí', (int)$bCsv['status'] === 200 && str_contains((string)($bCsv['headers']['Content-Type'] ?? ''), 'text/csv')
        && str_contains((string)$bCsv['body'], '2. pololetí'));
} finally {
    $h2->stop();
}
