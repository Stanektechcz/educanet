<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · HTTP část auditu tools/v73_lesson_prep_audit.php (načítá se z něj; používá jeho $ROOT, $tmp, $cacheDir, $check, $roster, $secret, $outputs, $q).
 *   a) bez účtů (klíč učitele): Tisk, Projektor, Zástup = 200, A4 strany, projektor bez odpovědí a bez jmen, zástup bez jmen s kontaktem
 *      z konfigurace; po skrytí jmen (tp721_self_names) tisk ukazuje „Žák N“; Dnešní hodina odkazuje na nové záložky,
 *   b) v režimu účtů: cizí třída 403 (obecná kontrola ?class=), asistent smí číst.
 * Jména žáků se do výstupu auditu nikdy nevypisují (jen počty).
 */

/** Jen obsah modulu (od kořene záložky po </main>): rámec cockpitu má paletu příkazů s žáky třídy, ta je mimo stránku tisku/projektoru. */
$content = static function (string $html): string {
    $start = min(array_filter([strpos($html, '<div class="lp73"'), strpos($html, '<div class="pj73"')], static fn($p): bool => $p !== false) ?: [0]);
    $end = strpos($html, '</main>', $start);
    return substr($html, $start, $end === false ? null : $end - $start);
};
$noNames = static function (string $html) use ($roster, $content): bool {
    $html = $content($html);
    foreach ($roster as $label) if (str_contains($html, e($label))) return false;
    return true;
};
audit_prewarm_accounts($GLOBALS['modules']);
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V73P_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir,
    'EDUCANET_SUBSTITUTION_CONTACT' => V73P_CONTACT];
$h = Harness::start($env);
try {
    audit_login_teacher($h, V73P_KEY, 'Audit Učitel');
    $print = $h->request('GET', '/teacher.php', ['tab' => 'hodina_tisk', 'class' => 'class_3a', 'lesson' => '5']);
    $pb = (string)$print['body'];
    $check('HTTP Tisk: 200, CSS tisku a JS (defer), strana 1 plán (cíl, kritéria, plán po minutách, materiály, bezpečnost, ŠVP) a strana 2 pracovní list',
        audit_response_clean($print) && str_contains($pb, 'assets/lesson-print-v73.css') && str_contains($pb, 'assets/lesson-v73.js') && substr_count($pb, '<article class="lp73-page') >= 2
        && str_contains($pb, '<h3>Plán po minutách</h3>') && str_contains($pb, '<h4>Kritéria úspěchu</h4>') && str_contains($pb, '<h3>Bezpečnost</h3>') && str_contains($pb, '<h3>Výsledky vzdělávání (ŠVP)</h3>')
        && str_contains($pb, '<h3>Materiály a nástroje</h3>') && str_contains($pb, 'lp73-worksheet') && str_contains($pb, 'Lekce 5 · ') && !str_contains($pb, '<form method="post" action="teacher.php" class="c70-form'));
    $check('HTTP Tisk: kontrolní list třídy se jmény (výchozí „zobrazit“) [žáků: ' . count($roster) . ']', $roster === [] || (!$noNames($pb) && str_contains($pb, 'Kontrolní list třídy')));
    $proj = $h->request('GET', '/teacher.php', ['tab' => 'hodina_projektor', 'class' => 'class_3a', 'lesson' => '5']);
    $jb = (string)$proj['body'];
    preg_match('~<section class="pj73-exit".*?</section>~s', $jb, $exitHtml);
    $leak = array_filter($secret, static fn(string $s): bool => mb_strlen($s) >= 3 && str_contains((string)($exitHtml[0] ?? ''), e($s)));
    $explain = array_filter($secret, static fn(string $s): bool => mb_strlen($s) >= 25 && str_contains($jb, e($s)));
    $check('HTTP Projektor: 200, CSS projektoru, kód / výzva, cíl, úseky jako kotvy, zadání a exit ticket (' . count($q) . ' otázek) bez možností, odpovědí, vysvětlení a výstupů',
        audit_response_clean($proj) && str_contains($jb, 'assets/lesson-projector-v73.css') && str_contains($jb, 'data-pj73') && str_contains($jb, 'id="pj73-s0"') && str_contains($jb, 'href="#pj73-s0"')
        && str_contains($jb, 'Cíl hodiny') && $exitHtml !== [] && array_reduce($q, static fn(bool $ok, string $x): bool => $ok && str_contains($exitHtml[0], e($x)), true) && $leak === [] && $explain === []
        && array_filter($outputs, static fn(string $o): bool => str_contains($jb, e($o))) === [] && !preg_match('/správn[áé] odpověď|data-correct|"correct"/u', $jb));
    $check('HTTP Projektor: žádná jména žáků (veřejná obrazovka – jen počty)', $noNames($jb));
    $sub = $h->request('GET', '/teacher.php', ['tab' => 'hodina_zastup', 'class' => 'class_3a', 'lesson' => '5']);
    $sb = (string)$sub['body'];
    $check('HTTP Zástup: 200, jedna A4 – co žáci dělají, plán B offline, jak otevřít kód hodiny, bezpečnost, kontakt z konfigurace; poznámka o přístupu zastupujícího; bez jmen',
        audit_response_clean($sub) && substr_count($sb, '<article class="lp73-page') === 1 && str_contains($sb, '<h3>Co žáci dělají</h3>') && str_contains($sb, '<h3>Plán B offline</h3>')
        && str_contains($sb, '<h3>Jak otevřít kód hodiny</h3>') && str_contains($sb, '<h3>Bezpečnost</h3>') && str_contains($sb, e(V73P_CONTACT)) && str_contains($sb, 'Přístup zastupujícího do systému zatím není')
        && $noNames($sb) && !str_contains($sb, 'Kontrolní list'));
    $today = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a']);
    $check('HTTP Dnešní hodina: 200 a odkazy na Projektor, Tisk, Zástup a Reflexi', audit_response_clean($today) && str_contains((string)$today['body'], 'tab=hodina_projektor&amp;class=class_3a')
        && str_contains((string)$today['body'], 'tab=hodina_tisk&amp;class=class_3a') && str_contains((string)$today['body'], 'tab=hodina_zastup&amp;class=class_3a') && str_contains((string)$today['body'], 'tab=reflexe73&amp;class=class_3a'));
    $csrf = (string)$h->csrfToken((string)$today['body']);
    $hide = $h->request('POST', '/teacher.php', ['action' => 'tp721_self_names', 'names' => 'hidden', 'csrf' => $csrf, 'return_tab' => 'nastaveni_vyuky'], ['follow_redirects' => false]);
    $print2 = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina_tisk', 'class' => 'class_3a', 'lesson' => '5'])['body'];
    $check('HTTP Tisk po skrytí jmen: kontrolní list „Žák 1 …“ a žádné jméno ze seznamu třídy', in_array((int)$hide['status'], [302, 303], true) && $noNames($print2)
        && ($roster === [] || str_contains($print2, '<th scope="row">Žák 1</th>')) && str_contains($print2, 'Jména jsou skrytá'));
    $other = $h->request('GET', '/teacher.php', ['tab' => 'hodina_tisk', 'class' => 'class_3a', 'lesson' => '99', 'x' => '<script>']);
    $check('HTTP: neplatné ?lesson= a neznámý parametr se ignorují (lekce podle kalendáře), nic se neodráží', audit_response_clean($other) && !str_contains((string)$other['body'], '<script>'));
} finally {
    $h->stop();
}

$acc = v59sf_create_accounts();
$h2 = Harness::start($env);
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
    $aOwn = $as('a', 'GET', '/teacher.php', ['tab' => 'hodina_tisk', 'class' => 'class_1a']);
    $aForeign = [];
    foreach (['hodina_tisk', 'hodina_projektor', 'hodina_zastup'] as $t) $aForeign[$t] = (int)$as('a', 'GET', '/teacher.php', ['tab' => $t, 'class' => 'class_3a'], ['follow_redirects' => false])['status'];
    $check('účty: učitel 1.A/2.A vidí tisk své třídy (200), tisk / projektor / zástup cizí 3.A → 403', audit_response_clean($aOwn) && str_contains((string)$aOwn['body'], 'lp73-page')
        && $aForeign === ['hodina_tisk' => 403, 'hodina_projektor' => 403, 'hodina_zastup' => 403]);
    $sProj = $as('s', 'GET', '/teacher.php', ['tab' => 'hodina_projektor', 'class' => 'class_3a']);
    $sSub = $as('s', 'GET', '/teacher.php', ['tab' => 'hodina_zastup', 'class' => 'class_3a']);
    $check('účty: asistent 3.A smí projektor i zástup číst (200, bez jmen)', audit_response_clean($sProj) && audit_response_clean($sSub) && $noNames((string)$sProj['body']) && $noNames((string)$sSub['body']));
} finally {
    $h2->stop();
}
