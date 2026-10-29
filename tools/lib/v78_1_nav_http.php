<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v78.1 · HTTP a prohlížečová část tools/v78_1_nav_audit.php (používá $tmp, $check, $map, $ROOT, V781_KEY).
 * Dev server nad dočasným úložištěm auditu; role učitele z EDUCANET_TEACHER_ROLE (režim legacy, jako v68).
 */

require_once $ROOT . '/src/teacher/t14_v77.php';
$cacheDir = $tmp . '-lm71';
@mkdir($cacheDir, 0700, true);
register_shutdown_function(static function () use ($cacheDir): void { edu_audit_remove_dir($cacheDir); });
$env = static fn(string $role): array => ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V781_KEY, 'EDUCANET_TEACHER_ROLE' => $role,
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$aside = static fn(string $html): string => preg_match('~<aside class="t68-side".*?</aside>~s', $html, $m) === 1 ? $m[0] : '';
$bottom = static fn(string $html): string => preg_match('~<nav class="ds-bottomnav ds-bottomnav--fixed n781-bottom" aria-label="Sekce cockpitu"><ul>.*?</ul></nav>~s', $html, $m) === 1 ? $m[0] : '';
$tabsIn = static function (string $html): array { preg_match_all('~href="\?tab=([a-z0-9_]+)(?:&amp;[^"]*)?"~', $html, $m); return $m[1]; };
$avail = teacher58_tabs();

// ---------------------------------------------------------------- učitel podle role
foreach (['admin', 'teacher', 'assistant'] as $role) {
    $expected = teacher68_visible_tabs($role, true, $avail);   // legacy: oprávnění podle role, administrátorské moduly podle dostupnosti (jako v68)
    $tree = nav781_tree($expected);
    $h = Harness::start($env($role));
    try {
        audit_login_teacher($h, V781_KEY, 'Audit Navigace');
        $home = $h->request('GET', '/teacher.php', ['tab' => 'attention']);
        $body = (string)$home['body'];
        $menu = $aside($body);
        $bar = $bottom($body);
        $check('HTTP ' . $role . ': menu (boční panel = drawer) má přesně záložky role v pořadí sekcí (' . count($expected) . '), každou jednou',
            audit_response_clean($home) && $tabsIn($menu) === $expected && substr_count($menu, '<details class="n781-sec"') === count($tree) && str_contains($menu, 'aria-label="Hlavní navigace"'));
        preg_match_all('~<a href="(teacher\.php\?tab=sekce[^"]*)"~', $bar, $bl);
        $check('HTTP ' . $role . ': spodní lišta = ' . count($tree) . ' sekcí (rozcestníky s třídou) + Více (drawer), právě jedna aktivní (Dnes)',
            count($bl[1]) === count($tree) && substr_count($bar, 'data-n781-open') === 1 && str_contains($bar, '<span>Více</span>') && substr_count($bar, 'aria-current=') === 1
            && str_contains($bar, 'sekce=dnes&amp;class=class_2a" aria-current="true"'));
        $check('HTTP ' . $role . ': aria-current="page" právě jednou v menu (Co řešit dnes), drobečky + Zpět, Menu a vyhledávání v draweru, scrim',
            substr_count($menu, 'aria-current="page"') === 1 && str_contains($menu, 'href="?tab=attention&amp;class=class_2a" aria-current="page"') && substr_count($body, 'class="t68-crumbs"') === 1
            && str_contains($body, 'data-n781-back') && str_contains($body, 'class="t68-btn n781-menu" href="#t68-side" data-n781-open aria-controls="t68-side" aria-expanded="false"')
            && str_contains($menu, 'data-command-open data-n781-search') && str_contains($body, 'data-n781-scrim'));
        $hubsOk = true;
        foreach ($tree as $sid => $groups) {
            $hub = (string)$h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => $sid, 'class' => 'class_3a'])['body'];
            $want = array_merge(...array_values($groups));
            preg_match_all('~<ul class="t68-cards">(.*?)</ul>~s', $hub, $cards);
            $hubsOk = $hubsOk && $tabsIn(implode('', $cards[1])) === $want && str_contains($hub, '<h1>' . e(nav781_sections()[$sid]['label']) . '</h1>')
                && str_contains($bottom($hub), 'sekce=' . $sid . '&amp;class=class_3a" aria-current="page"');
        }
        $check('HTTP ' . $role . ': rozcestník každé sekce má karty všech jejích záložek (cesta xs/sm: ikona sekce → karta = 2 klepnutí), aktivní ikona = stránka', $hubsOk);
        $leak = array_values(array_diff($tabsIn($menu), $expected));
        $check('HTTP ' . $role . ': v menu není záložka, kterou role nesmí' . ($leak ? ' [' . implode(', ', $leak) . ']' : ''), $leak === []);
        if ($role !== 'admin') continue;

        // každá záložka: 200 bez chyby, drobečky s názvem záložky, Zpět, aria-current v menu, spodní lišta (mimo projektor/tisk/zástup)
        $bad = [];
        foreach ($expected as $t) {
            $r = $h->request('GET', '/teacher.php', ['tab' => $t, 'class' => 'class_3a']);
            $b = (string)$r['body'];
            $m = $aside($b);
            $ok = (int)$r['status'] === 200 && audit_response_clean($r) && substr_count($b, 'class="t68-crumbs"') === 1
                && str_contains($b, '<span aria-current="page">' . e($map[$t]['label']) . '</span></li></ol>')
                && str_contains($b, '<a class="n781-back" href="' . e(nav781_back_url($t, 'class_3a')) . '" data-n781-back>')
                && substr_count($m, 'aria-current="page"') === 1 && preg_match('~href="\?tab=' . preg_quote($t, '~') . '(?:&amp;[^"]*)?" aria-current="page"~', $m) === 1
                && (in_array($t, NAV781_BARE_TABS, true) ? $bottom($b) === '' : $bottom($b) !== '');
            if (!$ok) $bad[] = $t;
        }
        $check('HTTP admin: všech ' . count($expected) . ' záložek – 200, drobečky „Cockpit › sekce › záložka“, Zpět (bez JS na sekci s třídou), aria-current v menu, spodní lišta (ne na projektoru/tisku/zástupu)'
            . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
        $old = [];
        foreach (['trida' => 'Žáci', 'hodnoceni' => 'Výuka', 'hry' => 'Žáci'] as $oldId => $label) $old[] = str_contains((string)$h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => $oldId])['body'], '<h1>' . $label . '</h1>');
        $xyz = (string)$h->request('GET', '/teacher.php', ['tab' => 'sekce', 'sekce' => 'xyz'])['body'];
        $check('HTTP: staré odkazy rozcestníku (trida, hodnoceni, hry) vedou na novou sekci, neplatná sekce nic nevypíše', !in_array(false, $old, true) && !str_contains($xyz, 't68-cards') && str_contains($xyz, 'není pro vaši roli dostupná'));

        // odznaky – jednotné značky ve všech navigacích
        $t14 = t1477_overdue_count(date('Y-m-d'));
        $una = nav781_unapproved_count(date('Y-m-d'), c70_classes());
        $navHtml = $menu . $bar;
        preg_match_all('~<b class="t68-badge"[^>]*>~', $navHtml, $all);
        $uniform = array_filter($all[0], static fn(string $b): bool => preg_match('~^<b class="t68-badge" aria-label="[^"<>]+"(?: data-n781-b="hodina")?>$~u', $b) !== 1);
        $t14Label = $t14 . ' ' . nav781_plural($t14, 'lekce', 'lekce', 'lekcí') . ' po termínu T-14';
        $unaLabel = $una . ' ' . nav781_plural($una, 'neschválená lekce', 'neschválené lekce', 'neschválených lekcí') . ' s výukou do 4 týdnů';
        preg_match_all('~<b class="t68-badge" aria-label="([^"]+)"~u', $navHtml, $lbl);
        $check('[' . implode(' | ', array_unique($lbl[1])) . '] odznaky: jednotné značky <b class="t68-badge" aria-label> v menu i liště; T-14 (' . $t14 . ') u záložky, sekce Dnes a ikony Dnes; neschválené (' . $una . ') u Schvalování, sekce a ikony Výuka',
            $uniform === [] && ($t14 === 0 ? !str_contains($navHtml, 'po termínu T-14') : substr_count($navHtml, 'aria-label="' . $t14Label . '">' . $t14 . '</b>') === 3)
            && ($una === 0 ? !str_contains($navHtml, 's výukou do 4 týdnů') : substr_count($navHtml, 'aria-label="' . e($unaLabel) . '">' . $una . '</b>') === 3));
        $hod = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a'])['body'];
        $n = preg_match('~data-n781-help="(\d+)"~', $hod, $hm) === 1 ? (int)$hm[1] : -1;
        $next = $aside((string)$h->request('GET', '/teacher.php', ['tab' => 'prehled', 'class' => 'class_3a'])['body']);
        $other = $aside((string)$h->request('GET', '/teacher.php', ['tab' => 'prehled', 'class' => 'class_1a'])['body']);
        $check('odznak Komu pomoct: karta Dnešní hodiny 3.A uloží počet (' . $n . '); menu na další stránce 3.A ho ukáže u „Dnešní hodina“ (0 = bez odznaku), u jiné třídy ne',
            $n >= 0 && ($n === 0 ? !str_contains($next, 'data-n781-b="hodina"') : preg_match('~data-n781-tab="hodina"><span>Dnešní hodina</span><b class="t68-badge" aria-label="Komu dnes pomoct: ' . $n . ' [^"]+" data-n781-b="hodina">' . $n . '</b>~u', $next) === 1)
            && !str_contains($other, 'data-n781-b="hodina"') && !preg_match('~data-n781-help-label="[^"]*[<>]~', $hod));
        $proj = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina_projektor', 'class' => 'class_3a'])['body'];
        $check('projektor: bez spodní lišty a bez vyhledávání (v74), jen skrytý scrim', $bottom($proj) === '' && !str_contains($proj, 'data-n781-search') && !str_contains($proj, 'data-command-open'));
    } finally {
        $h->stop();
    }
}
$anon = Harness::start($env('admin'));
try {
    $login = (string)$anon->request('GET', '/teacher.php', ['tab' => 'attention'])['body'];
    $check('nepřihlášený: žádná navigace v78.1 (menu, lišta, odznaky)', str_contains($login, 'teacher_login') && !str_contains($login, 'n781-') && !str_contains($login, 't68-badge'));
} finally {
    $anon->stop();
}

// ---------------------------------------------------------------- žák: spodní lišta Domů · Dnes · Úlohy · Profil
$sh = Harness::start($env('admin'));
try {
    audit_login_student($sh, 'class_3a', 'Audit Navigace');
    $res = [];
    foreach (['dashboard' => '?view=dashboard', 'ulohy' => '?view=ulohy', 'calendar' => null, 'materialy' => ''] as $view => $active) {
        $r = $sh->request('GET', '/?view=' . $view);
        $b = (string)$r['body'];
        $has = preg_match('~<nav class="ui-bottomnav ds78 n781-sbar" aria-label="[^"]+" data-ui61-bottomnav><ul>(.*?)</ul></nav>~s', $b, $bar) === 1;
        preg_match_all('~<a[^>]*href="(\?view=[a-z_]+)"~', $bar[1] ?? '', $hrefs);
        $cur = preg_match_all('~aria-current="page"~', $bar[1] ?? '');
        $todayOk = in_array($hrefs[1][1] ?? '', ['?view=hodina', '?view=calendar'], true);
        $res[$view] = $has && audit_response_clean($r) && count($hrefs[1]) === 4 && $hrefs[1][0] === '?view=dashboard' && $todayOk && $hrefs[1][2] === '?view=ulohy' && $hrefs[1][3] === '?view=profile'
            && ($active === '' ? $cur === 0 : ($active === null ? $cur === 1 && str_contains($bar[1], 'href="' . $hrefs[1][1] . '" aria-current="page"') : $cur === 1 && str_contains($bar[1], 'href="' . $active . '" aria-current="page"')))
            && str_contains($b, 'assets/nav-student-v78-1.css') && str_contains($b, 'assets/tokens-v78.css');
    }
    $check('žák: spodní lišta Domů · Dnes · Úlohy · Profil (v78 .ds78), aktivní Domů / Úlohy / Dnes (kalendář), mimo lištu (Materiály) žádná' . json_encode(array_keys(array_filter($res, static fn(bool $v): bool => !$v))), !in_array(false, $res, true));
    $menu = (string)$sh->request('GET', '/?view=dashboard')['body'];
    $check('žák: ostatní cíle (Materiály, Výsledky, Lab …) zůstávají v menu účtu a v horním panelu (nav61), drobečky beze změny',
        str_contains($menu, 'class="nav61-drawer"') && str_contains($menu, 'href="?view=materialy"') && str_contains($menu, 'href="?view=vysledky"') && str_contains((string)$sh->request('GET', '/?view=materialy')['body'], 'class="ui-crumbs"'));
} finally {
    $sh->stop();
}
$GLOBALS['ui61_primary_nav'] = ['view' => 'profile', 'cid' => null, 'items' => [['href' => '?view=hodina', 'views' => ['hodina'], 'class' => 'is-today', 'active' => false]]];
$pi = nav61_bottom_items(null, 'profile');
$GLOBALS['ui61_primary_nav'] = ['view' => 'community', 'cid' => null, 'items' => []];
$ci = nav61_bottom_items(null, 'community');
$GLOBALS['ui61_primary_nav'] = null;
$check('žák (logika): na profilu je aktivní Profil; běží-li hodina, „Dnes“ vede do Dnešní hodiny se štítkem, jinak do kalendáře',
    array_column(array_filter($pi, static fn(array $i): bool => $i['active']), 'href') === ['?view=profile'] && $pi[1]['href'] === '?view=hodina' && $pi[1]['class'] === 'is-today'
    && $ci[1]['href'] === '?view=calendar' && array_column(array_filter($ci, static fn(array $i): bool => $i['active']), 'href') === ['?view=profile']);
$en = require $ROOT . '/lang/en/ui/nav_v61.php';
$uk = require $ROOT . '/lang/uk/ui/nav_v61.php';
$check('žák: popisky lišty přes tr() s překladem en/uk (Domů, Dnes, Úlohy, Profil)', ($en['Úlohy'] ?? '') === 'Tasks' && ($uk['Úlohy'] ?? '') !== '' && isset($en['Domů'], $en['Dnes'], $en['Profil'], $uk['Domů'], $uk['Dnes'], $uk['Profil'])
    && str_contains($read('src/ui/nav_v61.php'), "trm('Úlohy')"));

// ---------------------------------------------------------------- prohlížeč (DOM 11 šířek, klávesnice)
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$require = getenv('EDUCANET_NAV_BROWSER') === 'require';
if (getenv('EDUCANET_NAV_BROWSER') === 'skip') {
    echo 'SKIP  prohlížečová část (EDUCANET_NAV_BROWSER=skip)' . PHP_EOL;
    return;
}
$bh = Harness::start($env('admin'));
try {
    $cmd = escapeshellarg($node) . ' ' . escapeshellarg(__DIR__ . '/v78_1_nav_browser.cjs') . ' ' . escapeshellarg($bh->baseUrl()) . ' ' . escapeshellarg(V781_KEY);
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn';
    if ($require) $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_NAV_BROWSER=require ji vynutí)' . PHP_EOL;
    return;
}
$check('prohlížeč: skript doběhl bez chyby', !isset($d['error']) && count($d['teacher'] ?? []) === 66 && count($d['student'] ?? []) === 22);
$rows = array_merge($d['teacher'] ?? [], $d['student'] ?? []);
$bad = array_filter($rows, static fn(array $r): bool => $r['hscroll'] || $r['smallCount'] > 0 || $r['fontsBelow14'] > 0);
$check('prohlížeč: 11 šířek (320–1920, 1366 × 600) × světlý/tmavý – učitel (úvod, rozcestník, Dnešní hodina) i žák: 0× vodorovný posun, 0 cílů navigace < 44 px, 0 textů < 14 px'
    . ($bad ? ' [' . implode('; ', array_map(static fn(array $r): string => $r['page'] . '@' . $r['width'] . $r['scheme'] . ' h=' . (int)$r['hscroll'] . ' s=' . $r['smallCount'] . ' f=' . $r['fontsBelow14'], array_slice($bad, 0, 5))) . ']' : ''), $bad === []);
$layout = array_filter($d['teacher'] ?? [], static function (array $r): bool {
    $w = (int)$r['width'];
    return $r['bottom'] !== ($w < 768) || $r['menuBtn'] !== ($w >= 768 && $w < 1024) || $r['side'] !== ($w >= 1024) || $r['currentInTree'] !== 1 || $r['currentInBottom'] !== 1;
});
$check('prohlížeč: xs/sm spodní lišta, md tlačítko Menu (drawer), lg+ boční panel; aria-current právě jednou v menu i v liště', $layout === []);
$stud = array_filter($d['student'] ?? [], static fn(array $r): bool => $r['studentBottom'] !== ((int)$r['width'] < 768) || $r['studentCurrent'] !== 1);
$check('prohlížeč: žák – spodní lišta jen pod 768 px, právě jedna aktivní položka', $stud === []);
$kb = $d['keyboard'] ?? [];
$check('klávesnice: drawer (Více 375 px, Menu 834 px) se otevře Enterem s fokusem uvnitř, 45× Tab + 8× Shift+Tab neopustí drawer, zbytek stránky inert, Esc zavře a vrátí fokus (aria-expanded=false)',
    count($kb) === 2 && array_filter($kb, static fn(array $k): bool => !$k['opened'] || $k['escaped'] !== 0 || !$k['inert'] || !$k['closed']) === []);
