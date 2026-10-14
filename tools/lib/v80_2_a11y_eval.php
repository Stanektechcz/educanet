<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · vyhodnocení prohlížečových měření tools/v80_2_a11y_audit.php (používá $check, $d, $pages).
 * Každá kontrola vypíše nejvýš 4 ukázky porušení (stránka@šířka+režim: detail); výjimky jen z v802_exceptions().
 */

$rows = (array)($d['rows'] ?? []);
$zoom = (array)($d['zoom'] ?? []);
$focus = (array)($d['focus'] ?? []);
$byId = array_column($pages, null, 'id');
$n = count($pages);
$check('prohlížeč: skript doběhl (' . $n . ' stránek × 11 šířek × světlý/tmavý = ' . ($n * 22) . ' měření, ' . ($n * 2) . ' zvětšení, ' . ($n * 2) . ' průchodů Tab)' . (isset($d['error']) ? ' [' . $d['error'] . ']' : ''),
    !isset($d['error']) && count($rows) === $n * 22 && count($zoom) === $n * 2 && count($focus) === $n * 2);

/** Kontrola nad řádky: $bad(řádek) vrací popis porušení nebo ''. */
$rowCheck = static function (string $kind, string $label, array $list, Closure $bad) use ($check): void {
    $hits = [];
    $pagesHit = [];
    foreach ($list as $r) {
        if (v802_excepted($kind, (string)$r['page'])) continue;
        $why = $bad($r);
        if ($why === '') continue;
        $pagesHit[(string)$r['page']] = true;
        if (count($hits) < 4) $hits[] = $r['page'] . '@' . ($r['width'] ?? ($r['zoom'] ?? '') . '%') . (isset($r['scheme']) ? $r['scheme'][0] : '') . ': ' . $why;
    }
    $check($label . ($hits ? ' [' . count($pagesHit) . ' stránek: ' . implode('; ', $hits) . ']' : ''), $hits === []);
};

$rowCheck('status', 'prohlížeč: každá stránka 200 a bez přesměrování (např. na přihlášení)', $rows,
    static fn(array $r): string => (int)$r['status'] !== 200 || (str_contains((string)$r['url'], 'teacher.php') === false && str_contains((string)$byId[$r['page']]['url'], 'teacher.php')) ? 'status ' . $r['status'] . ' → ' . $r['url'] : '');
$rowCheck('hscroll', 'vodorovný posun: 0 na 11 šířkách (320–1920, 1366 × 600) ve světlém i tmavém režimu, učitel i žák', $rows,
    static fn(array $r): string => $r['hscroll'] ? 'šířka obsahu ' . $r['scrollW'] . ' px' : '');
$rowCheck('small', 'cíle dotyku ≥ 44 × 44 px (mimo odkazy v běžném textu – výjimka WCAG 2.5.8 „inline“)', $rows,
    static fn(array $r): string => (int)$r['smallCount'] > 0 ? $r['smallCount'] . '× ' . implode(', ', array_slice((array)$r['small'], 0, 2)) : '');
$rowCheck('tiny', 'písmo ≥ 14 px u každého viditelného textu', $rows,
    static fn(array $r): string => (int)$r['tinyCount'] > 0 ? $r['tinyCount'] . '× ' . implode(', ', array_slice((array)$r['tiny'], 0, 2)) : '');
$rowCheck('contrast', 'kontrast vykresleného textu (vzorky ≤ 600 prvků na stránku): ≥ 4,5 : 1, velký text ≥ 3 : 1', $rows,
    static fn(array $r): string => (int)$r['lowCount'] > 0 ? $r['lowCount'] . '× ' . implode(', ', array_slice((array)$r['lowC'], 0, 2)) : ((int)$r['sampled'] < 5 ? 'jen ' . $r['sampled'] . ' vzorků' : ''));
$rowCheck('lang', 'lang: <html lang> je cs, en nebo uk na každé stránce', $rows,
    static fn(array $r): string => in_array($r['lang'], ['cs', 'en', 'uk'], true) ? '' : 'lang="' . $r['lang'] . '"');
$rowCheck('headings', 'nadpisy: viditelný h1 jako první nadpis a žádná přeskočená úroveň (h2 → h4)', array_filter($rows, static fn(array $r): bool => $r['scheme'] === 'light'),
    static fn(array $r): string => (int)$r['h1'] < 1 || (int)$r['firstH'] !== 1 || $r['skips'] !== [] ? 'h1=' . $r['h1'] . ' první=h' . $r['firstH'] . ' ' . implode(',', (array)$r['skips']) : '');
$rowCheck('landmarks', 'landmarky: právě jeden <main>, víc navigací má popisek (aria-label), obsah mimo landmarky není, žádný kladný tabindex', array_filter($rows, static fn(array $r): bool => $r['scheme'] === 'light'),
    static fn(array $r): string => (int)$r['mains'] !== 1 || (int)$r['navUnlabeled'] > 0 || $r['outside'] !== [] || (int)$r['posTab'] > 0 || !$r['title']
        ? 'main=' . $r['mains'] . ' nav bez popisku=' . $r['navUnlabeled'] . ' mimo=' . implode(',', (array)$r['outside']) . ' tabindex>0=' . $r['posTab'] : '');
$rowCheck('dark', 'tmavý režim: stránka s tmavým režimem je na tmavém zařízení tmavá a na světlém světlá; ostatní zůstávají světlé (žák: podle zařízení)', $rows,
    static function (array $r) use ($byId): string {
        $want = $r['scheme'] === 'dark' && $byId[$r['page']]['dark'];
        return (bool)$r['darkBg'] !== $want ? 'pozadí ' . ($r['darkBg'] ? 'tmavé' : 'světlé') . ', čekáno ' . ($want ? 'tmavé' : 'světlé') : '';
    });
$rowCheck('zoom', 'reflow: zvětšení 200 % (640 × 512) a 400 % (320 × 256 CSS px) bez vodorovného posunu, pevné lišty zakrývají ≤ 50 % výšky', $zoom,
    static fn(array $r): string => $r['hscroll'] || (float)$r['share'] > 0.5 ? ($r['hscroll'] ? 'posun ' : '') . 'lišty ' . (int)round((float)$r['share'] * 100) . ' %' : '');
$rowCheck('focus', 'fokus (Tab × 25 na 1280 a 390 px): vždy viditelný obrys nebo stín, prvek na obrazovce, pořadí bez skoků zpět (1 návrat na začátek dovolen)', $focus,
    static fn(array $r): string => (int)$r['count'] < 3 || (int)$r['back'] > 1 || (int)$r['noRingCount'] > 0 || (int)$r['hiddenCount'] > 0
        ? 'prvků ' . $r['count'] . ', zpět ' . $r['back'] . ', bez obrysu ' . implode(',', (array)$r['noRing']) . ', mimo obrazovku ' . implode(',', (array)$r['hidden']) : '');
$errors = (array)($d['errors'] ?? []);
$check('JS: žádná chyba skriptu na žádné stránce' . ($errors ? ' [' . implode('; ', array_slice($errors, 0, 3)) . ']' : ''), $errors === []);
$shotList = (array)($d['shots'] ?? []);
if ($shotList !== []) {
    $want = count(array_filter($pages, static fn(array $p): bool => $p['shots'])) * 4 * 2;
    $check('snímky: ' . count($shotList) . ' z ' . $want . ' (375/768/1280/1920 × světlý/tmavý), největší ' . round((int)($d['maxShot'] ?? 0) / 1024) . ' kB ≤ 150 kB', count($shotList) === $want && (int)($d['maxShot'] ?? 0) <= 150 * 1024);
}
$sampled = array_sum(array_map(static fn(array $r): int => (int)$r['sampled'], $rows));
echo 'INFO  kontrast: změřeno ' . $sampled . ' textových prvků na ' . count($rows) . ' vykresleních; výjimky: ' . json_encode(v802_exceptions(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
