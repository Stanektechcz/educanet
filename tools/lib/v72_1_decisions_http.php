<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · HTTP část auditu tools/v72_1_decisions_audit.php (načítá se z něj; používá jeho $ROOT, $tmp, $cacheDir, $check).
 *   a) bez účtů (klíč učitele, dev bypass žáka): záložka Nastavení výuky, CSRF, volba jmen, režim exit ticketu, vazba na ŠVP, AI prompty u žáka,
 *   b) v režimu účtů: cizí třída 403, asistent jen čte (režim nezmění), ale svou volbu jmen uložit smí.
 * Vše nad dočasným úložištěm auditu.
 */

$tpHash = static fn(string $ownerKey): string => substr(hash('sha256', 'tp721|' . $ownerKey), 0, 16);
audit_prewarm_accounts($GLOBALS['modules']);
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V721_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
try {
    audit_login_teacher($h, V721_KEY, 'Audit Učitel');
    $page = $h->request('GET', '/teacher.php', ['tab' => 'nastaveni_vyuky']);
    $body = (string)$page['body'];
    $check('HTTP: záložka Nastavení výuky – čistá odpověď, CSS v72, volba jmen (výchozí „zobrazit“) a přepínač režimu pro každou třídu',
        audit_response_clean($page) && str_contains($body, '<h1>Nastavení výuky</h1>') && str_contains($body, 'assets/cockpit-v72.css') && str_contains($body, 'value="tp721_self_names"')
        && preg_match('~id="tp721-names-shown"[^>]*checked~', $body) === 1 && substr_count($body, 'value="tp721_class_exit"') === 4 && str_contains($body, 'Rozhodnutí školy (8. 10. 2026)'));
    $csrf = (string)$h->csrfToken($body);
    $noCsrf = $h->request('POST', '/teacher.php', ['action' => 'tp721_self_names', 'names' => 'hidden'], ['follow_redirects' => false]);
    $okNames = $h->request('POST', '/teacher.php', ['action' => 'tp721_self_names', 'names' => 'hidden', 'csrf' => $csrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    tp721_data(true);
    $check('HTTP: tp721_self_names bez CSRF → 419; s CSRF skryje jména jen pro tohoto učitele (přesměrování zpět)', (int)$noCsrf['status'] === 419 && in_array((int)$okNames['status'], [302, 303], true)
        && str_contains((string)($okNames['headers']['Location'] ?? ''), 'tab=nastaveni_vyuky') && tp721_names_hidden($tpHash('legacy')));
    $today = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a'])['body'];
    $hasRows = preg_match('~aria-labelledby="c70-students">(.*?)</section>~s', $today, $stuHtml) === 1 && str_contains($stuHtml[1], 'data-label="Žák"');
    $roster = array_values(array_map(static fn(array $s): string => (string)$s['label'], project_students_for_class('class_3a')));
    $check('HTTP: Dnešní hodina se skrytými jmény ukazuje „Žák N“ místo jmen ze seznamu třídy [řádky žáků: ' . ($hasRows ? count($roster) : 'žádné') . ']', !$hasRows || (str_contains($stuHtml[1], '>Žák 1<')
        && array_reduce($roster, static fn(bool $ok, string $l): bool => $ok && ($l === '' || !str_contains($stuHtml[1], '>' . e($l) . '<')), true)));
    $unknown = $h->request('POST', '/teacher.php', ['action' => 'tp721_class_delete', 'class_id' => 'class_3a', 'csrf' => $csrf], ['follow_redirects' => false]);
    $badMode = $h->request('POST', '/teacher.php', ['action' => 'tp721_class_exit', 'class_id' => 'class_3a', 'mode' => 'exam', 'csrf' => $csrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    $grade = $h->request('POST', '/teacher.php', ['action' => 'tp721_class_exit', 'class_id' => 'class_3a', 'mode' => 'grade', 'csrf' => $csrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    tp721_data(true);
    $check('HTTP: neznámá tp721_* akce a neznámý režim nic nezmění; tp721_class_exit s CSRF přepne 3.A na „podklad ke známce“ (ostatní třídy beze změny)',
        (int)$unknown['status'] !== 200 && in_array((int)$badMode['status'], [302, 303], true) && in_array((int)$grade['status'], [302, 303], true)
        && tp721_exit_mode('class_3a') === 'grade' && tp721_exit_mode('class_4a') === 'feedback' && tp721_exit_record('class_3a')['by'] === 'Audit Učitel');
    $sch = (string)$h->request('GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_4a'])['body'];
    $h4 = svp72_status('class_4a', 4)['hash'];
    $check('HTTP: Schvalování lekcí má sekci Vazba na ŠVP (zdroj s rel noopener, 28 lekcí s výběrem, tlačítka schválit / vrátit)', str_contains($sch, 'id="svp72"') && substr_count($sch, 'name="svp[') === 28
        && str_contains($sch, 'value="lc72_svp_approve"') && str_contains($sch, 'value="lc72_svp_return"') && str_contains($sch, 'rel="noopener noreferrer"') && str_contains($sch, 'value="' . $h4 . '"'));
    $svpPost = $h->request('POST', '/teacher.php', ['action' => 'lc72_svp_approve', 'class_id' => 'class_4a', 'svp' => ['4' => $h4], 'csrf' => (string)$h->csrfToken($sch), 'return_tab' => 'schvalovani'], ['follow_redirects' => false]);
    $check('HTTP: lc72_svp_approve s CSRF schválí vazbu 4.A L4, ostatní lekce zůstanou návrh', in_array((int)$svpPost['status'], [302, 303], true) && svp72_status('class_4a', 4)['status'] === 'schvaleno'
        && svp72_status('class_4a', 5)['status'] === 'navrh');
    audit_login_student($h, 'class_3a', 'Audit Čtvrťák');
    $year = require $ROOT . '/school_year.php';
    $cur = $h->request('GET', '/', ['view' => 'lekce', 'n' => (string)v56_current_lesson_number('class_3a', $year)]);
    $check('HTTP žák: aktuální lekce má dobrovolné AI prompty s upozorněním na soukromí (čistá odpověď)', audit_response_clean($cur) && str_contains((string)$cur['body'], 'Procvič si s AI (dobrovolné)')
        && str_contains((string)$cur['body'], 'Nevkládej přihlašovací údaje školy'));
} finally {
    $h->stop();
}

$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V721_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
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
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'nastaveni_vyuky']);
    $aPost = $as('a', 'POST', '/teacher.php', ['action' => 'tp721_class_exit', 'class_id' => 'class_4a', 'mode' => 'grade', 'csrf' => (string)$h2->csrfToken((string)$aPage['body'])], ['follow_redirects' => false]);
    tp721_data(true);
    $check('účty: učitel 1.A/2.A vidí v Nastavení výuky jen své třídy; režim cizí třídy 4.A → 403 a nezmění se', audit_response_clean($aPage) && substr_count((string)$aPage['body'], 'value="tp721_class_exit"') === 2
        && preg_match('/value="class_[34]a"/', (string)$aPage['body']) !== 1 && (int)$aPost['status'] === 403 && tp721_exit_mode('class_4a') === 'feedback');
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'nastaveni_vyuky']);
    $sCsrf = (string)$h2->csrfToken((string)$sPage['body']);
    $sExit = $as('s', 'POST', '/teacher.php', ['action' => 'tp721_class_exit', 'class_id' => 'class_3a', 'mode' => 'feedback', 'csrf' => $sCsrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    $sNames = $as('s', 'POST', '/teacher.php', ['action' => 'tp721_self_names', 'names' => 'hidden', 'csrf' => $sCsrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    tp721_data(true);
    $check('účty: asistent 3.A režim jen čte (bez formuláře, POST zamítnut, 3.A zůstává „podklad ke známce“), svou volbu jmen uložit smí',
        audit_response_clean($sPage) && str_contains((string)$sPage['body'], 'Máte jen čtení') && !str_contains((string)$sPage['body'], 'value="tp721_class_exit"') && in_array((int)$sExit['status'], [302, 303, 403], true)
        && tp721_exit_mode('class_3a') === 'grade' && in_array((int)$sNames['status'], [302, 303], true) && tp721_names_hidden($tpHash(teacher59_owner_key_for($acc['id']['s']))) && !tp721_names_hidden($tpHash(teacher59_owner_key_for($acc['id']['b']))));
} finally {
    $h2->stop();
}
