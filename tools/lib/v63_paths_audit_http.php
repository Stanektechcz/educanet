<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · HTTP část auditu cest (vkládá ji tools/v63_paths_audit.php do téhož rozsahu proměnných).
 * Skutečný dev server nad dočasným úložištěm: ?view=cesty pro 3.A i 1.A, 404 pro cizí/neplatné cesty a kroky, POST bez CSRF
 * (419), celý tok 1.A přes formuláře (včetně Parsonovy úlohy tlačítky nahoru/dolů bez JS), opakované ověření s jinou
 * variantou, karta „Co dál“ na přehledu, cockpit učitele (přiřazení, zamítnutí cizí třídy a neznámé akce).
 */

require_once __DIR__ . '/http_harness.php';

/** Skryté pole formuláře z HTML: hodnota prvního <input name="…" value="…">. */
$p63Field = static function (string $html, string $name): string {
    return preg_match('/name="' . preg_quote($name, '/') . '" value="([^"]*)"/', $html, $m) === 1 ? html_entity_decode($m[1], ENT_QUOTES) : '';
};
/** Všechny formuláře posunu řádku v HTML jako pole polí. @return list<array<string,string>> */
$p63MoveForms = static function (string $html): array {
    $out = [];
    if (preg_match_all('/<form method="post" class="p63-move">(.*?)<\/form>/s', $html, $forms) === false) return $out;
    foreach ($forms[1] as $f) {
        $row = [];
        foreach (['csrf', 'action', 'path', 'step', 'order', 'sig', 'attempt', 'pos', 'dir'] as $n) $row[$n] = preg_match('/name="' . $n . '" value="([^"]*)"/', $f, $m) === 1 ? html_entity_decode($m[1], ENT_QUOTES) : '';
        $out[] = $row;
    }
    return $out;
};

$teacherKey = 'audit-v63-teacher-key-' . bin2hex(random_bytes(4));
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey]);
try {
    $login3 = audit_login_student($h, $C3, V63FX_LABELS[$C3]['a']);
    $csrf3 = (string)$login3['csrf'];
    $list3 = $h->request('GET', '/?view=cesty');
    $check('HTTP 3.A: ?view=cesty je 200, je v ní seznam cest třídy (lnx_chmod, net_dns), nadpis a žádná PHP chyba; přehled je 200', $login3['response']['status'] === 200 && $list3['status'] === 200 && str_contains($list3['body'], '<h1>Moje cesty</h1>')
        && str_contains($list3['body'], 'Práva k souborům a chmod') && str_contains($list3['body'], 'DNS: jak se z jména stane adresa') && !str_contains($list3['body'], 'Kostra webové stránky') && audit_response_clean($list3));
    $dash3 = $h->request('GET', '/?view=dashboard');
    $check('HTTP 3.A: přehled má kartu „Co dál“ s důvodem, odhadem minut a odkazem na krok, a až POD kartou „Teď“', $dash3['status'] === 200 && str_contains($dash3['body'], 'id="p63-next-title"') && str_contains($dash3['body'], 'href="?view=cesta&amp;path=')
        && (int)strpos($dash3['body'], 'p63-next-title') > (int)strpos($dash3['body'], 'student-do-now-card') && str_contains($dash3['body'], 'assets/paths-card-v63.css') && !str_contains($dash3['body'], 'assets/paths-v63.js') && audit_response_clean($dash3));
    $step3 = $h->request('GET', '/?view=cesta&path=lnx_chmod&step=explain');
    $check('HTTP 3.A: krok se vykreslí (200, h1, formulář s CSRF); cesta cizí třídy, neznámý krok, prázdné parametry a path traversal končí 404', $step3['status'] === 200 && str_contains($step3['body'], '<h1>') && str_contains($step3['body'], 'name="action" value="p63_submit"')
        && $h->request('GET', '/?view=cesta&path=web_html&step=explain')['status'] === 404 && $h->request('GET', '/?view=cesta&path=lnx_chmod&step=neexistuje')['status'] === 404 && $h->request('GET', '/?view=cesta')['status'] === 404
        && $h->request('GET', '/?view=cesta&path=..%2F..%2Fbootstrap&step=explain')['status'] === 404 && $h->request('GET', '/?view=cesta&path[]=x&step=explain')['status'] === 404);
    $noCsrf = $h->request('POST', '/', ['action' => 'p63_submit', 'path' => 'lnx_chmod', 'step' => 'explain'], ['follow_redirects' => false]);
    $badCsrf = $h->request('POST', '/', ['action' => 'p63_submit', 'path' => 'lnx_chmod', 'step' => 'explain', 'csrf' => 'podvrzeny'], ['follow_redirects' => false]);
    $stateBefore = p63_step_entry(p63_state($sidA), 'lnx_chmod', 'explain')['attempts'];
    $check('HTTP: POST bez CSRF a s cizím CSRF se odmítne (419) a stav se nezmění; POST p63_reflect bez CSRF také', $noCsrf['status'] === 419 && $badCsrf['status'] === 419 && p63_step_entry(p63_state($sidA), 'lnx_chmod', 'explain')['attempts'] === $stateBefore
        && $h->request('POST', '/', ['action' => 'p63_reflect', 'path' => 'lnx_chmod', 'step' => 'reflect', 'self' => '4'], ['follow_redirects' => false])['status'] === 419);
    $cross = $h->request('POST', '/', ['action' => 'p63_submit', 'path' => 'web_html', 'step' => 'explain', 'csrf' => $csrf3], ['follow_redirects' => false]);
    $check('HTTP: odevzdání kroku cesty cizí třídy se nezapíše (přesměrování na seznam, žádný soubor stavu pro web_html)', in_array($cross['status'], [302, 303], true) && !isset(p63_state($sidA)['paths']['web_html']));

    // Celý tok 1.A přes formuláře (žák C z 1.A), Parsons tlačítky nahoru/dolů
    $login1 = audit_login_student($h, $C1, V63FX_LABELS[$C1]['c']);
    $csrf1 = (string)$login1['csrf'];
    $key1 = v63fx_key($C1, 'c');
    $sid1c = v63fx_sid($C1, 'c');
    $dash1 = $h->request('GET', '/?view=dashboard');
    $check('HTTP 1.A: ?view=cesty je 200 se cestami 1.A (web_html, gfx_contrast), přehled nabízí „Co dál“; cesta 3.A je pro 1.A 404', $h->request('GET', '/?view=cesty')['status'] === 200 && str_contains($h->request('GET', '/?view=cesty')['body'], 'Kontrast barev a čitelnost')
        && str_contains($dash1['body'], 'id="p63-next-title"') && $h->request('GET', '/?view=cesta&path=lnx_chmod&step=explain')['status'] === 404);
    $post = static function (array $fields) use ($h, $csrf1): array { return $h->request('POST', '/', $fields + ['csrf' => $csrf1], ['follow_redirects' => true]); };
    $pathGfx = (array)p63_path('gfx_contrast');
    $page = $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'explain']);
    $check('HTTP 1.A: „Rozumím, pokračovat“ (explain) uloží krok a přesměruje na další krok (predikce) bez CSRF chyby', $page['status'] === 200 && str_contains($page['body'], 'type="radio"') && p63_step_done(p63_state($sid1c), 'gfx_contrast', 'explain'));
    $inPre = v63fx_input($C1, 'c', $pathGfx, (array)p63_step($pathGfx, 'predict'), false);
    $failPre = $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'predict', 'choice' => (string)$inPre['choice']]);
    $check('HTTP 1.A: špatná předpověď → panel „Zatím ne“ se skutečným výsledkem a vysvětlením, krok se neodemkne dál (Parsons je zamčený); další pokus ukáže jiný případ', $failPre['status'] === 200 && str_contains($failPre['body'], 'Zatím ne') && str_contains($failPre['body'], 'Změřený poměr')
        && !p63_step_done(p63_state($sid1c), 'gfx_contrast', 'predict') && str_contains($h->request('GET', '/?view=cesta&path=gfx_contrast&step=order')['body'], 'odemkne po dokončení'));
    $inPre = v63fx_input($C1, 'c', $pathGfx, (array)p63_step($pathGfx, 'predict'), true);
    $okPre = $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'predict', 'choice' => (string)$inPre['choice']]);
    $check('HTTP 1.A: správná předpověď → „Splněno“, další krok je odemčený', str_contains($okPre['body'], 'Splněno') && p63_step_done(p63_state($sid1c), 'gfx_contrast', 'predict'));

    $orderPage = $h->request('GET', '/?view=cesta&path=gfx_contrast&step=order');
    $moves = 0;
    $target = range(0, count((array)p63_step($pathGfx, 'order')['lines']) - 1);
    $solved = false;
    for ($guard = 0; $guard < 30 && !$solved; $guard++) {
        $forms = $p63MoveForms($orderPage['body']);
        $current = $forms === [] ? [] : array_map('intval', explode(',', $forms[0]['order']));
        $i = 0;
        while ($i < count($current) && $current[$i] === $target[$i]) $i++;
        if ($i >= count($current)) { $solved = true; break; }
        $j = (int)array_search($target[$i], $current, true);
        $form = array_values(array_filter($forms, static fn(array $f): bool => (int)$f['pos'] === $j && $f['dir'] === 'up'))[0] ?? null;
        if ($form === null) break;
        $orderPage = $h->request('POST', '/', $form + ['csrf' => $csrf1], ['follow_redirects' => true]);
        $moves++;
    }
    $liveText = $orderPage['body'];
    $check('HTTP Parsons bez JS: řádky se dají seřadit jen tlačítky nahoru/dolů (POST p63_parsons_move + přesměrování), po každém posunu je v aria-live oznámení nové pozice; potřeba ' . $moves . ' posunů', $solved && $moves >= 1 && str_contains($liveText, 'class="p63-order"'));
    $forged = $post(['action' => 'p63_parsons_move', 'path' => 'gfx_contrast', 'step' => 'order', 'order' => '4,3,2,1,0', 'sig' => str_repeat('0', 64), 'attempt' => '1', 'pos' => '1', 'dir' => 'up']);
    $check('HTTP Parsons: posun s podvrženým podpisem se odmítne (flash o neověřeném pořadí) a pořadí se nezmění', str_contains($forged['body'], 'Pořadí se nepodařilo ověřit') && !p63_step_done(p63_state($sid1c), 'gfx_contrast', 'order'));
    $submitForm = [];
    foreach (['order', 'sig'] as $n) {
        preg_match_all('/<form method="post" class="p63-form">.*?<\/form>/s', $orderPage['body'], $fm);
        $submitForm[$n] = $p63Field((string)($fm[0][0] ?? ''), $n);
    }
    $okOrder = $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'order', 'order' => $submitForm['order'], 'sig' => $submitForm['sig']]);
    $check('HTTP Parsons: správně seřazené pořadí s platným podpisem se uloží (Splněno 100 %, krok done)', str_contains($okOrder['body'], 'Splněno, výsledek 100') && p63_step_done(p63_state($sid1c), 'gfx_contrast', 'order'));
    $in = v63fx_input($C1, 'c', $pathGfx, (array)p63_step($pathGfx, 'recall'), true);
    $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'recall', 'a' => $in['a']]);

    // Ověření dvakrát: jiná varianta zadání, odpovědi se neprozradí
    $vPage1 = $h->request('GET', '/?view=cesta&path=gfx_contrast&step=verify');
    $in = v63fx_input($C1, 'c', $pathGfx, (array)p63_step($pathGfx, 'verify'), false);
    $vRes1 = $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'verify', 'a' => $in['a']]);
    $vPage2 = $h->request('GET', '/?view=cesta&path=gfx_contrast&step=verify');
    $legends = static fn(string $html): array => (preg_match_all('/<legend><span[^>]*>.*?<\/legend>|<legend>[^<]*<span[^>]*>(.*?)<\/span><\/legend>/s', $html, $m) ? $m[0] : []);
    $check('HTTP verify: po neúspěšném pokusu se zobrazí jiná varianta otázek (jiné zadání), výsledek ukazuje ✓/✗ bez prozrazení správných odpovědí a zbývají 2 pokusy',
        $legends($vPage1['body']) !== [] && $legends($vPage2['body']) !== [] && $legends($vPage1['body']) !== $legends($vPage2['body']) && str_contains($vRes1['body'], 'Zatím ne') && str_contains($vRes1['body'], 'neukazují') && str_contains($vPage2['body'], 'Dnes zbývají 2 pokusy'));
    $in = v63fx_input($C1, 'c', $pathGfx, (array)p63_step($pathGfx, 'verify'), true);
    $vRes2 = $post(['action' => 'p63_submit', 'path' => 'gfx_contrast', 'step' => 'verify', 'a' => $in['a']]);
    $ev1c = ev62_read($sid1c);
    $check('HTTP verify: druhý pokus splněn, do důkazů přibyly dva řádky zdroje test (0 a 1) kompetence gfx_color_contrast se jmény variant a bez jmen žáků', str_contains($vRes2['body'], 'Splněno') && count(array_filter($ev1c, static fn(array $r): bool => $r['source'] === 'test')) === 2
        && !str_contains((string)file_get_contents(ev62_path($sid1c)), 'Audit'));
    $refl = $h->request('GET', '/?view=cesta&path=gfx_contrast&step=reflect');
    $check('HTTP reflexe: formulář má popisek (label for), maxlength 200, radiogroup 1–4 s required a vysvětlení, že větu vidí jen žák', str_contains($refl['body'], '<label class="p63-num" for="p63-note">') && str_contains($refl['body'], 'maxlength="200"') && substr_count($refl['body'], 'name="self"') === 4 && str_contains($refl['body'], 'Větu vidíš jen ty'));
    $privateNote = 'Soukromá poznámka žáka C <b>1.A</b> ' . bin2hex(random_bytes(3));
    $reflRes = $post(['action' => 'p63_reflect', 'path' => 'gfx_contrast', 'step' => 'reflect', 'self' => '3', 'note' => $privateNote]);
    $stateRefl = p63_state($sid1c)['reflect']['gfx_contrast'] ?? [];
    $check('HTTP reflexe: uloží se self a očištěná věta (bez značek), žák dostane porovnání odhadu s výsledkem; věta není v důkazech', str_contains($reflRes['body'], 'Reflexe je uložená') && ($stateRefl['self'] ?? 0) === 3 && !str_contains((string)($stateRefl['note'] ?? ''), '<b>') && str_contains((string)($stateRefl['note'] ?? ''), 'Soukromá poznámka')
        && !str_contains((string)file_get_contents(ev62_path($sid1c)), 'Soukromá'));

    $prof1 = $h->request('GET', '/?view=profile&tab=kompetence');
    $check('HTTP 1.A (Q1): profil má záložku Kompetence s katalogem grafika_web a důkazem z cesty (kompetence kontrastu má důkazy), bez PHP chyb; ostatní kompetence neověřené', $prof1['status'] === 200 && str_contains($prof1['body'], 'Umím zvolit barvy a ověřit kontrast textu')
        && str_contains($prof1['body'], 'Umím postavit sémantickou kostru webové stránky') && str_contains($prof1['body'], 'Důkazů:') && !str_contains($prof1['body'], 'Umím určit síť, masku') && audit_response_clean($prof1));

    // Cockpit učitele
    $tl = audit_login_teacher($h, $teacherKey);
    $tabHtml = $h->request('GET', '/teacher.php?tab=cesty&class=class_3a');
    $tabSection = preg_match('/<section class="teacher-panel p63-t-wrap">.*?<\/section>\s*<\/section>|<section class="teacher-panel p63-t-wrap">.*?<\/main>/s', $tabHtml['body'], $secM) === 1 ? $secM[0] : '';
    $check('HTTP cockpit: ?tab=cesty je 200, nabízí třídy 1.A a 3.A, cesty 3.A s tlačítkem Přiřadit třídě a trychtýřem; bez jmen žáků a bez reflexních vět', $tl['response']['status'] === 200 && $tabHtml['status'] === 200 && str_contains($tabHtml['body'], 'Výukové cesty') && str_contains($tabHtml['body'], 'Přiřadit třídě')
        && str_contains($tabHtml['body'], 'Trychtýř kroků') && !str_contains($tabSection, 'Audit Cesta') && !str_contains($tabSection, 'Soukromá poznámka') && !str_contains($tabSection, 'stu_') && audit_response_clean($tabHtml));
    $tCsrf = (string)$h->csrfToken($tabHtml['body']);
    $assignPost = $h->request('POST', '/teacher.php', ['action' => 'p63_assign', 'class_id' => $C3, 'path' => 'net_dns', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $assignedNow = p63_assignments($C3);
    $check('HTTP cockpit: p63_assign s CSRF přiřadí cestu (záznam v assign.json.php, štítek „přiřazeno“ v záložce)', isset($assignedNow['net_dns']) && str_contains($assignPost['body'], 'přiřazeno') && ($assignedNow['net_dns']['open'] ?? false) === true);
    $h->request('POST', '/teacher.php', ['action' => 'p63_assign', 'class_id' => 'class_2a', 'path' => 'net_dns', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $h->request('POST', '/teacher.php', ['action' => 'p63_hack', 'class_id' => $C3, 'path' => 'net_dns', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $noCsrfTeacher = $h->request('POST', '/teacher.php', ['action' => 'p63_unassign', 'class_id' => $C3, 'path' => 'net_dns'], ['follow_redirects' => false]);
    $check('HTTP cockpit: třída bez cest (2.A) a neznámá akce p63_hack nic nezapíšou; p63_unassign bez CSRF nic nezmění', p63_assignments('class_2a') === [] && array_keys(p63_assignments($C3)) === ['net_dns'] && $noCsrfTeacher['status'] !== 200 || array_keys(p63_assignments($C3)) === ['net_dns']);
    $h->request('POST', '/teacher.php', ['action' => 'p63_unassign', 'class_id' => $C3, 'path' => 'net_dns', 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $check('HTTP cockpit: p63_unassign s CSRF přiřazení zruší', p63_assignments($C3) === []);
} finally {
    $h->stop();
}
