<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · HTTP část tools/v77_http_audit.php (používá $tmp, $cacheDir, $check).
 *   a) bez účtů (klíč učitele, vývojový vstup žáka): Středa T-14, odznak, Hromadné schválení t14 + CSRF, Režim hodiny, žák L1 po schválení,
 *   b) v režimu účtů: učitel 1.A/2.A vidí jen své třídy (cizí ?class= 403), asistent 3.A jen čte.
 */

$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V77H_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$badge = static fn(string $body): int => preg_match('~<b class="t68-badge" aria-label="(\d+) (?:lekce|lekcí) po termínu T-14">~u', $body, $m) === 1 ? (int)$m[1] : 0;
$h = Harness::start($env);
try {
    audit_login_teacher($h, V77H_KEY, 'Audit Učitel');
    $page = $h->request('GET', '/teacher.php', ['tab' => 'streda77']);
    $body = (string)$page['body'];
    $expected = t1477_overdue_count(date('Y-m-d'));
    $check('Středa T-14: čistá odpověď 200, nadpis, CSS v77, ukazatele (po termínu, do 7 dní, 8–14 dní, schváleno), sekce 4 tříd, filtr tříd a tabulka s termínem schválení',
        audit_response_clean($page) && str_contains($body, '<h1>Středa: co schválit do 14 dní</h1>') && str_contains($body, 'assets/cockpit-v77.css') && str_contains($body, 'Schválit za 8–14 dní')
        && substr_count($body, 'class="c70-card c70-wide t1477-class') === 4 && str_contains($body, 'class="t1477-filter"') && str_contains($body, 'aria-current="page">Všechny třídy'));
    $check('odznak v menu: „Středa: T-14“ ukazuje počet lekcí po termínu (' . $expected . ') u záložky i u sekce Dnes; bez lekcí po termínu se nevykreslí',
        str_contains($body, '>Středa: T-14</span>') && $badge($body) === $expected && ($expected === 0 || substr_count($body, 'po termínu T-14">' . $expected . '</b>') >= 2));
    $one = (string)$h->request('GET', '/teacher.php', ['tab' => 'streda77', 'class' => 'class_3a'])['body'];
    $pending = t1477_class_plan('class_3a', date('Y-m-d'))['pending'];
    $check('filtr třídy: ?class=class_3a ukáže jen 3.A; „Schválit hromadně (' . $pending . ')“ vede do Hromadného schválení s rozsahem t14 a třídou',
        substr_count($one, 'class="c70-card c70-wide t1477-class') === 1 && str_contains($one, 'aria-current="page">3.A')
        && ($pending === 0 || str_contains($one, e(t1477_bulk_url('class_3a'))) && str_contains($one, 'Schválit hromadně (' . $pending . ')')));
    parse_str(substr(t1477_bulk_url('class_3a'), 1), $q);
    $bulk = $h->request('GET', '/teacher.php', $q);
    $bb = (string)$bulk['body'];
    $cand = ba74_candidates(['class_3a'], 't14', ba74_monday(date('Y-m-d')), true, true);
    $check('Hromadné schválení předvybrané z Středy: rozsah „termín T-14“ zaškrtnutý, třída 3.A, v náhledu přesně položky s výukou do 28 dní (' . count($cand['items']) . ')',
        audit_response_clean($bulk) && str_contains($bb, 'value="t14" checked') && str_contains($bb, 'Termín T-14: výuka do 4 týdnů') && substr_count($bb, 'name="items[]"') === count($cand['items'])
        && count($cand['items']) === count(array_filter($cand['items'], static fn(array $i): bool => $i['date'] >= date('Y-m-d') && $i['date'] <= date('Y-m-d', strtotime('+28 days')))));
    $items = [];
    preg_match_all('~name="items\[\]" value="([^"]+)"~', $bb, $mm);
    foreach ($mm[1] as $v) if (str_contains($v, '|lesson|')) $items[] = html_entity_decode($v);
    $noCsrf = $h->request('POST', '/teacher.php', ['action' => 'ba74_approve', 'confirm' => '1', 'class_ids' => ['class_3a'], 'items' => $items], ['follow_redirects' => false]);
    $ok = $h->request('POST', '/teacher.php', ['action' => 'ba74_approve', 'confirm' => '1', 'class_ids' => ['class_3a'], 'items' => $items, 'return_class' => 'class_3a', 'csrf' => (string)$h->csrfToken($bb)], ['follow_redirects' => true]);
    $after = (string)$h->request('GET', '/teacher.php', ['tab' => 'streda77'])['body'];
    $check('schválení z náhledu: bez CSRF 419; s CSRF se lekce 3.A do 28 dní schválí a odznak klesne o lekce 3.A po termínu (' . $badge($body) . ' → ' . $badge($after) . ')'
        . ($items === [] ? ' – okno T-14 je dnes prázdné, kontrola zápisu přeskočena' : ''),
        (int)$noCsrf['status'] === 419 && ($items === [] || str_contains((string)$ok['body'], 'Hromadné schválení:')) && $badge($after) === t1477_overdue_count(date('Y-m-d'), ['class_1a', 'class_2a', 'class_4a'])
        && t1477_class_plan('class_3a', date('Y-m-d'))['counts']['po_terminu'] === 0);
    $teach = $h->request('GET', '/teacher.php', ['tab' => 'teach', 'class' => 'class_3a', 'lesson' => '4']);
    $hod = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a']);
    $check('Režim hodiny a Dnešní hodina: odkaz na vyřazený MD kit zmizel (Režim hodiny → Tisk hodiny), Dnešní hodina odkazuje na lesson kit v aplikaci bez hlášky o chybějícím podkladu',
        audit_response_clean($teach) && !str_contains((string)$teach['body'], 'materials/lesson_kits') && str_contains((string)$teach['body'], 'tab=hodina_tisk&amp;class=class_3a&amp;lesson=4')
        && audit_response_clean($hod) && str_contains((string)$hod['body'], '#lesson-kit') && !str_contains((string)$hod['body'], 'Podklad v materiálech'));
    // --- schválení L1 3.A a pohled žáka
    $sch = (string)$h->request('GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a', 'lesson' => '1'])['body'];
    $prev = str_contains($sch, 'Lekce 1 · Start: IP plán učebny a důkaz, že síť funguje') && str_contains($sch, 'uvidí až po schválení');
    $h->request('POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '1', 'hash' => lc72_overlay_hash(lc72_overlay('class_3a', 1)), 'csrf' => (string)$h->csrfToken($sch)], ['follow_redirects' => true]);
    audit_login_student($h, 'class_3a', 'Audit Nikdo');
    $l1 = $h->request('GET', '/', ['view' => 'lekce', 'n' => '1']);
    $lb = (string)$l1['body'];
    $check('žák 3.A po schválení L1: náhled učitele s AI prompty z návrhu; žák vidí nový cíl, exit ticket a AI prompty z obsahu (s upozorněním na soukromí)',
        $prev && lc72_status('class_3a', 1)['status'] === 'schvaleno' && audit_response_clean($l1) && str_contains($lb, 'Na konci hodiny umím přečíst adresu') && str_contains($lb, 'value="lx72_answer"')
        && str_contains($lb, 'id="ai-prompty"') && str_contains($lb, e('Vysvětli mi jednoduše: Start: IP plán učebny a důkaz, že síť funguje')) && !str_contains($lb, 'materials/lesson_kits'));
    $l2 = (string)$h->request('GET', '/', ['view' => 'lekce', 'n' => '2'])['body'];
    $check('žák: neschválená L2 má AI prompty původní lekce (za běhu, shodné s vyřazeným kitem) a hlavička má LVL prstenec s CSS v55',
        str_contains($l2, e(aip77_original_prompts('class_3a', 2)[0]['title'])) && str_contains($l2, 'class="v55-lvl-ring"') && str_contains($l2, 'assets/student-v55.css'));
} finally {
    $h->stop();
}
// ---------------------------------------------------------------- b) režim účtů
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V77H_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
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
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'streda77']);
    $ab = (string)$aPage['body'];
    $aForeign = $as('a', 'GET', '/teacher.php', ['tab' => 'streda77', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A vidí ve Středě jen 1.A a 2.A; ?class=class_3a → 403', audit_response_clean($aPage) && substr_count($ab, 'class="c70-card c70-wide t1477-class') === 2
        && str_contains($ab, 'id="t1477-class_1a"') && !str_contains($ab, 'id="t1477-class_3a"') && (int)$aForeign['status'] === 403);
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'streda77']);
    $sBulk = $as('s', 'GET', '/teacher.php', ['tab' => 'hromadne74', 'f' => '1', 'rozsah' => 't14', 'class' => 'class_3a', 'co_lekce' => '1', 'co_svp' => '1']);
    $check('účty: asistent 3.A vidí Středu (jen 3.A) a náhled T-14 v Hromadném schválení jen pro čtení (bez formuláře ba74_approve)',
        audit_response_clean($sPage) && substr_count((string)$sPage['body'], 'class="c70-card c70-wide t1477-class') === 1 && audit_response_clean($sBulk)
        && !str_contains((string)$sBulk['body'], 'value="ba74_approve"'));
} finally {
    $h2->stop();
}
