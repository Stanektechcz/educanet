<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · HTTP část auditu tools/v74_cockpit_audit.php (načítá se z něj; používá jeho $tmp, $cacheDir, $check, $noNames, $roster).
 *   a) bez účtů (klíč učitele): paleta bez jmen v HTML všech hlavních záložek, žádná na projektoru / tisku / zástupu a přihlášení,
 *      JSON jen po přihlášení (no-store, bez e-mailů), skrytá jména → prázdný seznam; Dnešní hodina s kartou „Komu dnes pomoct“;
 *      hromadné schválení: náhled, CSRF 419, bez potvrzení nic, s potvrzením výsledek po položkách a vrácení; Týdenní souhrn,
 *   b) v režimu účtů: JSON učitele 1.A/2.A jen jeho třídy, cizí třída v hromadném schválení 403, asistent bez formuláře a bez zápisu.
 */

$palette = static function (string $html): string {
    $start = strpos($html, 'data-command-backdrop');
    if ($start === false) return '';
    $end = strpos($html, '</footer>', $start);
    return substr($html, $start, ($end === false ? strlen($html) : $end) - $start);
};
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V74C_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$h = Harness::start($env);
try {
    $login = $h->request('GET', '/teacher.php');
    $anonJson = $h->request('GET', '/teacher.php', ['tab' => 'tyden74', 'paleta' => '1'], ['extra' => ['Accept' => 'application/json']]);
    $check('HTTP bez přihlášení: přihlašovací stránka bez palety a bez jmen; JSON palety nevydá žádného žáka',
        !str_contains((string)$login['body'], 'data-command-backdrop') && $noNames((string)$login['body']) && $noNames((string)$anonJson['body']) && !str_contains((string)$anonJson['body'], 'student360'));
    audit_login_teacher($h, V74C_KEY, 'Audit Učitel');
    $bad = [];
    foreach ([['attention', ''], ['hodina', 'class_3a'], ['tyden74', ''], ['hromadne74', ''], ['materialy74', 'class_3a'], ['kompetence', 'class_3a'], ['schvalovani', 'class_3a'], ['prehled', 'class_3a']] as [$tab, $class]) {
        $p = $h->request('GET', '/teacher.php', array_filter(['tab' => $tab, 'class' => $class]));
        $pal = $palette((string)$p['body']);
        if (!audit_response_clean($p) || $pal === '' || !$noNames($pal) || str_contains($pal, '@') || !str_contains($pal, 'data-palette-src')) $bad[] = $tab;
    }
    $check('HTTP: paleta v HTML 8 hlavních záložek bez jmen a e-mailů žáků (jen záložky a třídy, zdroj JSON)' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
    $bad = [];
    foreach (['hodina_projektor', 'hodina_tisk', 'hodina_zastup'] as $tab) {
        $p = $h->request('GET', '/teacher.php', ['tab' => $tab, 'class' => 'class_3a']);
        if (!audit_response_clean($p) || str_contains((string)$p['body'], 'data-command-backdrop') || str_contains((string)$p['body'], 'data-command-open')) $bad[] = $tab;
    }
    $proj = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina_projektor', 'class' => 'class_3a'])['body'];
    $check('HTTP: projektor, tisk a zástup bez palety i tlačítka ⌘K; projektor bez jediného jména' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === [] && $noNames($proj));
    $json = $h->request('GET', '/teacher.php', ['tab' => 'tyden74', 'paleta' => '1'], ['extra' => ['Accept' => 'application/json']]);
    $d = json_decode((string)$json['body'], true);
    $check('HTTP: JSON palety po přihlášení – application/json, no-store, všichni žáci tříd v rozsahu (' . count($roster) . '), odkaz na detail žáka, bez e-mailů',
        (int)$json['status'] === 200 && str_contains(strtolower((string)($json['headers']['Content-Type'] ?? '')), 'application/json') && str_contains((string)($json['headers']['Cache-Control'] ?? ''), 'no-store')
        && is_array($d) && $d['ok'] === true && $d['names_hidden'] === false && count($d['items']) === count($roster) && !str_contains((string)$json['body'], '@')
        && count((array)$d['pages']) > 10 && $noNames((string)json_encode($d['pages'], JSON_UNESCAPED_UNICODE)));
    $today = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a']);
    $tb = (string)$today['body'];
    $check('HTTP: Dnešní hodina má kartu „Komu dnes pomoct“ (Jak vybíráme?, CSS v74) a dál materiály, žáky a souhrn', audit_response_clean($today) && str_contains($tb, 'Komu dnes pomoct')
        && str_contains($tb, 'Jak vybíráme?') && str_contains($tb, 'assets/cockpit-v74.css') && str_contains($tb, 'Materiály k lekci') && str_contains($tb, 'Souhrn hodiny'));
    $csrf = (string)$h->csrfToken($tb);
    $hide = $h->request('POST', '/teacher.php', ['action' => 'tp721_self_names', 'names' => 'hidden', 'csrf' => $csrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    $hidden = json_decode((string)$h->request('GET', '/teacher.php', ['tab' => 'tyden74', 'paleta' => '1'], ['extra' => ['Accept' => 'application/json']])['body'], true);
    $hodinaHidden = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a'])['body'];
    $h->request('POST', '/teacher.php', ['action' => 'tp721_self_names', 'names' => 'shown', 'csrf' => $csrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    $check('HTTP: po skrytí jmen (Nastavení výuky) JSON palety žádné jméno nevrátí (stránky a třídy ano) a Dnešní hodina (včetně karty) je bez jmen',
        in_array((int)$hide['status'], [302, 303], true) && is_array($hidden) && $hidden['names_hidden'] === true && $hidden['items'] === [] && count((array)$hidden['pages']) > 10 && $noNames($hodinaHidden));
    $prev = $h->request('GET', '/teacher.php', ['tab' => 'hromadne74', 'f' => '1', 'rozsah' => 'trida', 'class' => 'class_3a', 'co_lekce' => '1', 'co_svp' => '1']);
    $pb = (string)$prev['body'];
    preg_match_all('~name="items\[\]" value="([^"]+)"~', $pb, $mm);
    $items = array_slice($mm[1] ?? [], 0, 2);
    $check('HTTP: náhled hromadného schválení – kroky 1–3, ukazatele (T-14), tabulka s otisky, povinné potvrzení a tlačítko; bez jmen žáků',
        audit_response_clean($prev) && str_contains($pb, 'Náhled a potvrzení') && str_contains($pb, 'name="confirm" value="1" required') && str_contains($pb, 'value="ba74_approve"')
        && str_contains($pb, 'name="class_ids[]" value="class_3a"') && count($items) === 2 && str_contains($pb, 'Učí se do 14 dní') && $noNames($pb));
    $csrf = (string)$h->csrfToken($pb);
    $post = ['action' => 'ba74_approve', 'class_ids' => ['class_3a'], 'items' => $items, 'return_tab' => 'hromadne74', 'return_class' => 'class_3a'];
    $approvedCount = static fn(): int => count(array_filter((array)(storage_read(lc72_path(), false)['log'] ?? []), static fn(array $l): bool => !empty($l['bulk'])));
    $logBefore = $approvedCount();
    $noCsrf = $h->request('POST', '/teacher.php', $post, ['follow_redirects' => false]);
    $noConfirm = $h->request('POST', '/teacher.php', $post + ['csrf' => $csrf], ['follow_redirects' => false]);
    $logMid = $approvedCount();
    $ok = $h->request('POST', '/teacher.php', $post + ['csrf' => $csrf, 'confirm' => '1'], ['follow_redirects' => false]);
    $result = (string)$h->request('GET', '/teacher.php', ['tab' => 'hromadne74', 'vysledek' => '1', 'class' => 'class_3a'])['body'];
    $check('HTTP: ba74_approve bez CSRF 419, bez potvrzení nic neschválí; s potvrzením 303 na výsledek: 2 z 2 po položkách s tlačítky „Vrátit“ (lc72_return / lc72_svp_return)',
        (int)$noCsrf['status'] === 419 && in_array((int)$noConfirm['status'], [302, 303], true) && $logMid === $logBefore && in_array((int)$ok['status'], [302, 303], true)
        && str_contains((string)($ok['headers']['Location'] ?? ''), 'vysledek=1') && $approvedCount() === $logBefore + 2 && str_contains($result, '2 z 2 v pořádku')
        && (str_contains($result, 'value="lc72_return"') || str_contains($result, 'value="lc72_svp_return"')) && str_contains($result, 'Vráceno po hromadném schválení'));
    $sch = (string)$h->request('GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a'])['body'];
    $wk = $h->request('GET', '/teacher.php', ['tab' => 'tyden74']);
    $check('HTTP: Schvalování lekcí má kartu „Hromadné schválení“ s odkazem; Týdenní souhrn 200 s CSS v74, tiskem (lesson-v73.js) a bez jmen žáků v obsahu',
        str_contains($sch, 'ba74-entry') && str_contains($sch, 'tab=hromadne74') && audit_response_clean($wk) && str_contains((string)$wk['body'], 'Týdenní souhrn')
        && str_contains((string)$wk['body'], 'assets/lesson-v73.js') && str_contains((string)$wk['body'], 'assets/cockpit-v74.css')
        && $noNames(substr((string)$wk['body'], 0, (int)strpos((string)$wk['body'], 'data-command-backdrop'))));
} finally {
    $h->stop();
}

$acc = v59sf_create_accounts();
$h2 = Harness::start($env);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
$jars = [];
try {
    $as = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h2, $jarSwap, &$jars, $acc): array {
        if (!isset($jars[$who])) { $jarSwap([]); audit_login_teacher_account($h2, $acc['login'][$who], $acc['pw'][$who]); $jars[$who] = $jarSwap(null); }
        $jarSwap($jars[$who]);
        $r = $h2->request($method, $path, $fields, $headers);
        $jars[$who] = $jarSwap(null);
        return $r;
    };
    $aJson = json_decode((string)$as('a', 'GET', '/teacher.php', ['tab' => 'tyden74', 'paleta' => '1'], ['extra' => ['Accept' => 'application/json']])['body'], true);
    $urls = implode(' ', array_column((array)($aJson['items'] ?? []), 'url'));
    $own = count(project_students_for_class('class_1a')) + count(project_students_for_class('class_2a'));
    $check('účty: JSON palety učitele 1.A/2.A obsahuje jen žáky jeho tříd (' . $own . '), žádný z 3.A/4.A', is_array($aJson) && count((array)$aJson['items']) === $own
        && !str_contains($urls, 'class=class_3a') && !str_contains($urls, 'class=class_4a'));
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'hromadne74']);
    $aForeign = $as('a', 'GET', '/teacher.php', ['tab' => 'hromadne74', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $aCsrf = (string)$h2->csrfToken((string)$aPage['body']);
    $item3a = 'class_3a|7|lesson|' . lc72_status('class_3a', 7)['hash'];
    $aPost = $as('a', 'POST', '/teacher.php', ['action' => 'ba74_approve', 'class_ids' => ['class_3a'], 'items' => [$item3a], 'confirm' => '1', 'csrf' => $aCsrf], ['follow_redirects' => false]);
    $aSneak = $as('a', 'POST', '/teacher.php', ['action' => 'ba74_approve', 'class_ids' => ['class_1a'], 'items' => [$item3a], 'confirm' => '1', 'csrf' => $aCsrf, 'return_tab' => 'hromadne74'], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A – hromadné schválení cizí třídy GET i POST → 403; položka 3.A podstrčená pod 1.A se neschválí (výsledek po položkách), stránka bez odkazů na 3.A/4.A',
        audit_response_clean($aPage) && (int)$aForeign['status'] === 403 && (int)$aPost['status'] === 403 && in_array((int)$aSneak['status'], [302, 303], true)
        && lc72_status('class_3a', 7)['status'] !== 'schvaleno' && preg_match('/[?&;]class=class_[34]a\b|value="class_[34]a"/', (string)$aPage['body']) !== 1);
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'hromadne74', 'f' => '1', 'rozsah' => 'trida', 'class' => 'class_3a', 'co_lekce' => '1']);
    $sPost = $as('s', 'POST', '/teacher.php', ['action' => 'ba74_approve', 'class_ids' => ['class_3a'], 'items' => [$item3a], 'confirm' => '1', 'csrf' => (string)$h2->csrfToken((string)$sPage['body'])], ['follow_redirects' => false]);
    $check('účty: asistent 3.A vidí náhled jen ke čtení (bez formuláře ba74_approve), POST zamítnut oprávněním a nic se neschválí [' . (int)$sPost['status'] . ']', audit_response_clean($sPage)
        && str_contains((string)$sPage['body'], 'Máte jen čtení') && !str_contains((string)$sPage['body'], 'value="ba74_approve"') && in_array((int)$sPost['status'], [302, 303, 403], true)
        && lc72_status('class_3a', 7)['status'] !== 'schvaleno');
    $bPage = (string)$as('b', 'GET', '/teacher.php', ['tab' => 'tyden74'])['body'];
    $check('účty: učitel 3.A/4.A – Týdenní souhrn jen s jeho třídami a paleta bez jmen', str_contains($bPage, 'Týdenní souhrn') && !str_contains($bPage, 'id="wk74-class_1a"')
        && str_contains($bPage, 'id="wk74-class_3a"') && $noNames($palette($bPage)));
} finally {
    $h2->stop();
}
