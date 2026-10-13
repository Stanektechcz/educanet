<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v79 · HTTP a prohlížečová část tools/v79_stepper_audit.php (používá $tmp, $check, $ROOT, $read, V79S_KEY).
 * Dva dev servery nad DVĚMA dočasnými úložišti: A = cesta přes stepper (sp79_* + poslední krok), B = původní požadavek bez stepperu
 * (stejná pole jako před v79). Výsledek v úložišti se po normalizaci (časy, náhodná id) musí shodovat. Žádné ostré storage/.
 */

$cacheA = $tmp . '-lm71';
$tmpB = dirname($tmp) . '/educanet-audit-v79-stepper-b-' . bin2hex(random_bytes(6));
$cacheB = $tmpB . '-lm71';
foreach ([$cacheA, $tmpB, $cacheB] as $dir) @mkdir($dir, 0700, true);
register_shutdown_function(static function () use ($cacheA, $tmpB, $cacheB): void { foreach ([$cacheA, $tmpB, $cacheB] as $dir) edu_audit_remove_dir($dir); });
$env = static fn(string $dir, string $cache, string $role = 'admin'): array => ['EDUCANET_STORAGE_DIR' => $dir, 'EDUCANET_TEACHER_EXPORT_KEY' => V79S_KEY, 'EDUCANET_TEACHER_ROLE' => $role,
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cache];

/** Otisky souborů úložiště (rel → sha1). */
$hashes = static function (string $dir): array {
    $out = [];
    if (!is_dir($dir)) return $out;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) if ($f->isFile() && !str_ends_with($f->getFilename(), '.lock')) $out[str_replace(chr(92), '/', substr($f->getPathname(), strlen($dir) + 1))] = sha1_file($f->getPathname());
    ksort($out);
    return $out;
};
/** Obsah souboru úložiště bez časů a náhodných id (pro srovnání A vs. B). */
$norm = static function (mixed $v) use (&$norm): mixed {
    if (is_array($v)) {
        $keys = array_keys($v);
        $random = $keys !== [] && count(array_filter($keys, static fn($k): bool => is_string($k) && preg_match('/^[a-z]{0,6}_?[0-9a-f]{8,}$/', $k) === 1)) === count($keys);
        $out = [];
        foreach ($v as $k => $x) $out[$k] = is_string($k) && preg_match('/(^|_)(at|ts|time|created|updated)$/', $k) === 1 ? '*' : $norm($x);
        if ($random) {
            $out = array_values($out);
            usort($out, static fn($a, $b): int => strcmp((string)json_encode($a), (string)json_encode($b)));
        }
        return $out;
    }
    return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $v) === 1 ? '*' : $v;
};
$fileData = static function (string $path) use ($norm): mixed {
    $raw = (string)@file_get_contents($path);
    $p = strpos($raw, '?>');
    return $norm(json_decode(trim($p !== false ? substr($raw, $p + 2) : $raw), true));
};
/** Obsah souboru; u souborů pojmenovaných podle student_id (náhodné v každém úložišti) multimnožina obsahu celé složky. */
$content = static function (string $dir, string $rel) use ($fileData): mixed {
    if (preg_match('~^(.+)/stu_[0-9a-f]{16}\.json\.php$~', $rel, $m) !== 1) return $fileData($dir . '/' . $rel);
    $all = array_map($fileData, glob($dir . '/' . $m[1] . '/*.json.php') ?: []);
    usort($all, static fn($x, $y): int => strcmp((string)json_encode($x), (string)json_encode($y)));
    return $all;
};
/** Shoda souborů, které změnila poslední akce v A, se stejnými soubory v B. @return list<string> rozdílné soubory */
$same = static function (array $before, array $after, string $label) use ($tmp, $tmpB, $content): array {
    $changed = array_keys(array_diff_assoc($after, $before));
    $diff = [];
    foreach ($changed as $rel) if (!str_ends_with($rel, '.json.php') || $content($tmp, $rel) !== $content($tmpB, $rel)) $diff[] = $rel;
    return $changed === [] ? ['(' . $label . ': nic se nezměnilo)'] : $diff;
};
$loc = static fn(array $r): string => (string)($r['headers']['Location'] ?? $r['headers']['location'] ?? '');
$path = static fn(array $r): string => '/' . ltrim((string)preg_replace('~^https?://[^/]+~', '', $loc($r)), '/');   // teacher_redirect / redirect_to vrací relativní Location
$panels = static fn(string $b): int => preg_match_all('~data-sp79-panel="\d"~', $b);
$hiddenPanels = static fn(string $b): int => preg_match_all('~data-sp79-panel="\d" hidden~', $b);

$students3 = array_values(project_students_for_class('class_3a'));
$label3 = (string)($students3[0]['label'] ?? '');
$key3 = (string)(array_keys(project_students_for_class('class_3a'))[0] ?? '');
$check('fixture: třída 3.A má žáky v seznamu (fiktivní)', $label3 !== '' && $key3 !== '');

$hA = Harness::start($env($tmp, $cacheA));
$hB = Harness::start($env($tmpB, $cacheB));
try {
    audit_login_teacher($hA, V79S_KEY, 'Audit Stepper');
    audit_login_teacher($hB, V79S_KEY, 'Audit Stepper');
    $tok = static fn(object $h, string $q): array => [$r = $h->request('GET', '/teacher.php?' . $q), (string)$h->csrfToken((string)$r['body'])];
    $post = static fn(object $h, array $f, string $csrf, string $url = '/teacher.php'): array => $h->request('POST', $url, $f + ['csrf' => $csrf], ['follow_redirects' => false]);

    // ------------------------------------------------------------ 3) Reflexe po hodině
    [$p, $csrf] = $tok($hA, 'tab=reflexe73&class=class_3a&lesson=4');
    $b = (string)$p['body'];
    $check('Reflexe: pruh „Krok 1 ze 4: Stav“ (hotovo/aktuální/čeká), 4 panely (3 skryté), jeden formulář lr73_save, souhrn chyb role=alert, CSS/JS v79',
        audit_response_clean($p) && str_contains($b, 'Krok 1 ze 4: <strong>Stav</strong>') && $panels($b) === 4 && $hiddenPanels($b) === 3 && substr_count($b, 'value="lr73_save"') === 1
        && str_contains($b, 'id="sp79-errors" role="alert" aria-live="assertive"') && str_contains($b, 'stepper-v79.css') && str_contains($b, 'stepper-v79.js'));
    // Nav. tlačítko posílá name=action až za skrytým action formuláře (prohlížeč pošle obě, PHP vezme poslední) – tady proto akce kroku první v poli.
    $baseR = ['action' => 'lr73_save', 'return_tab' => 'reflexe73', 'class_id' => 'class_3a', 'lesson' => '4', 'sp79_flow' => 'reflexe'];
    $dataR = ['state' => 'cast', 'stop_seg' => '1', 'picks_worked' => ['dvojice'], 'picks_adjust' => ['cas'], 'worked' => 'Práce ve dvojicích.', 'adjust' => 'Kratší výklad.', 'next' => 'Začít opakováním.'];
    $s0 = $hashes($tmp);
    $e1 = $post($hA, ['sp79_step' => '1', 'action' => 'sp79_next'] + $baseR, $csrf);
    $g1 = (string)$hA->request('GET', $path($e1))['body'];
    $check('Reflexe: Další bez stavu → zpět na krok 1, chyba u pole (id …-err, aria-describedby) i v souhrnu s odkazem na pole (bez JS)',
        str_contains($loc($e1), 'krok=1') && str_contains($g1, '<a href="#lr73-state-cele">Vyberte, jak hodina proběhla') && str_contains($g1, 'id="lr73-state-cele-err"')
        && str_contains($g1, 'aria-describedby="lr73-state-cele-err"') && !str_contains($g1, 'data-sp79-errors hidden'));
    $n1 = $post($hA, ['sp79_step' => '1', 'action' => 'sp79_next'] + $baseR + $dataR, $csrf);
    $n2 = $post($hA, ['sp79_step' => '2', 'action' => 'sp79_next'] + $baseR + $dataR, $csrf);
    $bk = $post($hA, ['sp79_step' => '3', 'action' => 'sp79_back'] + $baseR + $dataR, $csrf);
    $g2 = (string)$hA->request('GET', $path($bk))['body'];
    $check('Reflexe: Další → krok 2 → 3, Zpět → krok 2 a všechna data zůstanou (stav, úsek, volby i texty dalších kroků)',
        str_contains($loc($n1), 'krok=2') && str_contains($loc($n2), 'krok=3') && str_contains($loc($bk), 'krok=2') && str_contains($g2, 'Krok 2 ze 4')
        && str_contains($g2, 'value="cast" required checked') && preg_match('~<option value="1" selected>~', $g2) === 1 && str_contains($g2, 'value="cas" checked') && str_contains($g2, '>Začít opakováním.</textarea>'));
    $long = $post($hA, ['sp79_step' => '3', 'action' => 'sp79_next'] + $baseR + ['worked' => str_repeat('x', 301)] + $dataR, $csrf);
    $g3 = (string)$hA->request('GET', $path($long))['body'];
    $n3 = $post($hA, ['sp79_step' => '3', 'action' => 'sp79_next'] + $baseR + $dataR, $csrf);
    $g4 = (string)$hA->request('GET', $path($n3))['body'];
    $check('Reflexe: text 301 znaků = chyba kroku 3 (text se neztratí); platný krok 3 → Hotovo se souhrnem všech kroků a odkazy Upravit',
        str_contains($loc($long), 'krok=3') && str_contains($g3, 'je delší než 300 znaků') && str_contains($g3, str_repeat('x', 301) . '</textarea>') && str_contains($loc($n3), 'krok=4')
        && str_contains($g4, 'Krok 4 ze 4') && str_contains($g4, '<dt>Jak hodina proběhla</dt><dd>odučena část') && str_contains($g4, 'Kratší výklad.') && substr_count($g4, 'class="sp79-edit"') >= 4);
    $check('koncept nic nezapisuje do úložiště (Další / Zpět jen relace)', $hashes($tmp) === $s0);
    $roster = $post($hA, $baseR + ['sp79_step' => '4'] + ['next' => 'Pomoct: ' . $label3] + $dataR, $csrf);
    $g5 = (string)$hA->request('GET', $path($roster))['body'];
    $check('Reflexe: chyba posledního kroku (jméno žáka v textu) → nic se neuloží, krok Hotovo zůstane s poznámkou a koncept (data) se neztratí',
        $hashes($tmp) === $s0 && str_contains($g5, 'Reflexi se nepodařilo uložit.') && str_contains($g5, 'Krok 4 ze 4') && str_contains($g5, 'Kratší výklad.'));
    $before = $hashes($tmp);
    $fin = $post($hA, $baseR + ['sp79_step' => '4'] + $dataR, $csrf);
    $g6 = (string)$hA->request('GET', $path($fin))['body'];
    [, $csrfB] = $tok($hB, 'tab=reflexe73&class=class_3a&lesson=4');
    $legacy = $post($hB, ['action' => 'lr73_save', 'return_tab' => 'reflexe73', 'class_id' => 'class_3a', 'lesson' => '4'] + $dataR, $csrfB);
    $d = $same($before, $hashes($tmp), 'reflexe');
    $check('Reflexe: uložení ze stepperu = stejná akce lr73_save, stejné přesměrování a STEJNÝ záznam v úložišti jako původní formulář' . ($d ? ' [' . implode(', ', $d) . ']' : ''),
        $d === [] && $path($fin) === $path($legacy) && str_contains($g6, 'Hotovo – reflexe lekce 4 je uložená.') && str_contains($g6, 'Na příště:</strong> Začít opakováním.'));

    // ------------------------------------------------------------ 2) Hromadné schválení
    $q = 'tab=hromadne74&f=1&rozsah=trida&class=class_3a&co_lekce=1&co_svp=1';
    [$p, $csrf] = $tok($hA, $q . '&krok=2');
    $b = (string)$p['body'];
    preg_match_all('~name="items\[\]" value="([^"]+)"~', $b, $mm);
    $items = array_slice(array_map('html_entity_decode', $mm[1]), 0, 2);
    $check('Hromadné: krok 2 Náhled změn (Rozsah hotovo, Potvrzení/Výsledek čekají), tabulka s výběrem, potvrzení ve skrytém kroku 3',
        audit_response_clean($p) && str_contains($b, 'Krok 2 ze 4: <strong>Náhled změn</strong>') && count($items) === 2 && $hiddenPanels($b) === 2 && str_contains($b, 'name="confirm" value="1" required'));
    $baseH = ['action' => 'ba74_approve', 'return_tab' => 'hromadne74', 'return_class' => 'class_3a', 'class_ids' => ['class_3a'], 'sp79_flow' => 'hromadne',
        'ba_rozsah' => 'trida', 'ba_tyden' => ba74_monday(date('Y-m-d')), 'ba_class' => 'class_3a', 'co_lekce' => '1', 'co_svp' => '1'];
    $s0 = $hashes($tmp);
    $e = $post($hA, ['sp79_step' => '2', 'action' => 'sp79_next'] + $baseH, $csrf);
    $ge = (string)$hA->request('GET', $path($e))['body'];
    $n = $post($hA, ['sp79_step' => '2', 'action' => 'sp79_next', 'items' => $items] + $baseH, $csrf);
    $gn = (string)$hA->request('GET', $path($n))['body'];
    $bk = $post($hA, ['sp79_step' => '3', 'action' => 'sp79_back', 'items' => $items] + $baseH, $csrf);
    $gb = (string)$hA->request('GET', $path($bk))['body'];
    $check('Hromadné: Další bez výběru = chyba „aspoň jednu položku“; se 2 položkami Potvrzení se souhrnem (2 položky) a „Schválit vybrané (2)“; Zpět drží výběr',
        str_contains($ge, '<a href="#ba79-items">Vyberte aspoň jednu položku') && str_contains($loc($n), 'krok=3') && str_contains($gn, 'Krok 3 ze 4') && str_contains($gn, '<dt>Vybráno ke schválení</dt><dd>2 položky')
        && str_contains($gn, 'Schválit vybrané (2)') && str_contains($loc($bk), 'krok=2') && substr_count($gb, '" checked><span class="c70-sr">') === 2 && $hashes($tmp) === $s0);
    $before = $hashes($tmp);
    $fin = $post($hA, $baseH + ['sp79_step' => '3', 'items' => $items, 'confirm' => '1'], $csrf);
    $gr = (string)$hA->request('GET', $path($fin))['body'];
    [, $csrfB] = $tok($hB, $q);
    $legacy = $post($hB, ['action' => 'ba74_approve', 'class_ids' => ['class_3a'], 'items' => $items, 'return_tab' => 'hromadne74', 'return_class' => 'class_3a', 'confirm' => '1'], $csrfB);
    $d = $same($before, $hashes($tmp), 'hromadne');
    $check('Hromadné: potvrzení = ba74_approve se stejným výsledkem v úložišti jako původní formulář; Výsledek (krok 4) 2 z 2 s tlačítky Vrátit' . ($d ? ' [' . implode(', ', $d) . ']' : ''),
        $d === [] && $path($fin) === $path($legacy) && str_contains($gr, 'Krok 4 ze 4') && str_contains($gr, '2 z 2 v pořádku') && str_contains($gr, 'Vráceno po hromadném schválení'));

    // ------------------------------------------------------------ 4) Opakování před písemkou
    $bad = (string)$hA->request('GET', '/teacher.php?tab=opakovani75&class=class_3a&od=9&do=5&krok=2')['body'];
    $check('Opakování: rozsah „do < od“ = zůstává krok 1 (Témata), chyba u pole i v souhrnu, zadané hodnoty zůstanou ve formuláři (GET bez JS)',
        str_contains($bad, 'Krok 1 ze 3') && str_contains($bad, '<a href="#rv75-do">') && str_contains($bad, 'id="rv75-do-err"') && preg_match('~id="rv75-od"[^>]*value="9"~', $bad) === 1);
    [$p, $csrf] = $tok($hA, 'tab=opakovani75&class=class_3a&od=5&do=16');
    $b = (string)$p['body'];
    $g3 = (string)$hA->request('GET', '/teacher.php?tab=opakovani75&class=class_3a&od=5&do=16&krok=3')['body'];
    $check('Opakování: rozsah → krok 2 Náhled balíčku, krok 3 Zveřejnit se souhrnem (rozsah, otázky, úlohy, co se stane) a původním formulářem op75_publish',
        audit_response_clean($p) && str_contains($b, 'Krok 2 ze 3: <strong>Náhled balíčku</strong>') && str_contains($g3, 'Krok 3 ze 3') && str_contains($g3, '<dt>Rozsah lekcí</dt><dd>L5–L16')
        && str_contains($g3, 'value="op75_publish"') && str_contains($g3, 'Zveřejnit žákům'));
    $before = $hashes($tmp);
    $fin = $post($hA, ['action' => 'op75_publish', 'class_id' => 'class_3a', 'od' => '5', 'do' => '16', 'return_tab' => 'opakovani75'], $csrf);
    $gp = (string)$hA->request('GET', $path($fin))['body'];
    [, $csrfB] = $tok($hB, 'tab=opakovani75&class=class_3a&od=5&do=16');
    $legacy = $post($hB, ['action' => 'op75_publish', 'class_id' => 'class_3a', 'od' => '5', 'do' => '16', 'return_tab' => 'opakovani75'], $csrfB);
    $d = $same($before, $hashes($tmp), 'opakovani');
    $check('Opakování: zveřejnění = op75_publish se stejným výsledkem jako dřív; po něm krok 3 „Hotovo – opakování L5–L16 je zveřejněné“' . ($d ? ' [' . implode(', ', $d) . ']' : ''),
        $d === [] && $path($fin) === $path($legacy) && str_contains($gp, 'Hotovo – opakování L5–L16 je zveřejněné.') && str_contains($gp, 'Krok 3 ze 3'));

    // ------------------------------------------------------------ 7) Admin – nový školní rok
    [$p, $csrf] = $tok($hA, 'tab=identita');
    $s0 = $hashes($tmp);
    $baseI = ['action' => 'identity58_plan', 'sp79_flow' => 'identita'];
    $e = $post($hA, ['sp79_step' => '2', 'year' => 'abc', 'action' => 'sp79_next'] + $baseI, $csrf);
    $ge = (string)$hA->request('GET', $path($e))['body'];
    $n = $post($hA, ['sp79_step' => '2', 'year' => '2027', 'action' => 'sp79_next'] + $baseI, $csrf);
    $gn = (string)$hA->request('GET', $path($n))['body'];
    $fin = $post($hA, $baseI + ['sp79_step' => '3', 'year' => '2027'], $csrf);
    $gp = (string)$hA->request('GET', $path($fin))['body'];
    $check('Identita: krok 1 Registr a kolize; neplatný rok = chyba kroku 2; rok 2027 → Souhrn „2027/2028“; potvrzení = identity58_plan → ?plan=2027 → krok 4 Plán a účty (odkazy Přístupy, Učitelé, CLI)',
        audit_response_clean($p) && str_contains((string)$p['body'], 'Krok 1 ze 4: <strong>Registr a kolize</strong>') && str_contains($ge, '<a href="#idt58-year">') && str_contains($gn, '<dt>Nový školní rok</dt><dd>2027/2028')
        && str_contains($path($fin), 'tab=identita&plan=2027') && str_contains($gp, 'Krok 4 ze 4') && str_contains($gp, 'Plán pro školní rok 2027/2028') && str_contains($gp, 'tools/v58_rollover.php --dry-run --year=2027')
        && str_contains($gp, '?tab=pristupy') && $hashes($tmp) === $s0);

    // ------------------------------------------------------------ 1) Příprava hodiny
    $p1 = $hA->request('GET', '/teacher.php?tab=priprava79');
    $pe = (string)$hA->request('GET', '/teacher.php?tab=priprava79&class=class_9z&krok=2')['body'];
    $p2 = (string)$hA->request('GET', '/teacher.php?tab=priprava79&class=class_3a&lesson=5&krok=2')['body'];
    $p4 = (string)$hA->request('GET', '/teacher.php?tab=priprava79&class=class_3a&lesson=5&krok=4')['body'];
    $pr = (string)$hA->request('GET', '/teacher.php?tab=priprava79')['body'];
    $check('Příprava: krok 1 Třída (GET formulář bez JS), neznámá třída = chyba u pole, krok 2 Plán (tabulka po minutách), krok 4 souhrn + Projektor / Tisk / Zástup se třídou a lekcí; návrat pokračuje (relace)',
        audit_response_clean($p1) && str_contains((string)$p1['body'], 'Krok 1 ze 4: <strong>Třída</strong>') && str_contains($pe, '<a href="#pp79-class-0">') && str_contains($p2, 'Krok 2 ze 4')
        && str_contains($p2, 'Plán hodiny po minutách') && str_contains($p4, '<dt>Lekce</dt><dd>Lekce 5') && str_contains($p4, 'tab=hodina_projektor&amp;class=class_3a&amp;lesson=5')
        && str_contains($p4, 'tab=hodina_tisk&amp;class=class_3a&amp;lesson=5') && str_contains($pr, 'Pokračujete v rozpracované přípravě') && str_contains($pr, 'Krok 4 ze 4'));

    // ------------------------------------------------------------ 5) Žák – úloha s variantou
    foreach ([$hA, $hB] as $h) $h->request('GET', '/', ['class' => 'class_3a', 'student' => $label3]);
    $t1 = $hA->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'net_ports']);
    $tb = (string)$t1['body'];
    $tCsrf = (string)$hA->csrfToken($tb);
    $tCsrfB = (string)$hB->csrfToken((string)$hB->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'net_ports'])['body']);
    $clamped = (string)$hA->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'net_ports', 'krok' => '4'])['body'];
    $check('Úloha: krok 1 Zadání (Řešení s formulářem tb75_answer ve skrytém panelu), „Krok N ze 4“ přeložitelné; krok 4 bez pokusu nejde (zůstane krok 2)',
        audit_response_clean($t1) && str_contains($tb, 'Krok 1 ze 4: <strong>Zadání</strong>') && $panels($tb) === 4 && str_contains($tb, 'value="tb75_answer"') && str_contains($clamped, 'Krok 2 ze 4'));
    $wrong = ['action' => 'tb75_answer', 'task' => 'net_ports', 'back' => 'ulohy', 'a' => ['p0' => '1', 'p1' => '2', 'p2' => '3']];
    $before = $hashes($tmp);
    $w = $hA->request('POST', '/', $wrong + ['csrf' => $tCsrf], ['follow_redirects' => true]);
    $hB->request('POST', '/', $wrong + ['csrf' => $tCsrfB], ['follow_redirects' => true]);
    $d = $same($before, $hashes($tmp), 'uloha');
    $check('Úloha: kontrola = tb75_answer beze změny (stejný záznam pokusu jako dřív) → krok 3 Kontrola „Správně 0 z 3 polí“ s odkazem Opravit odpovědi' . ($d ? ' [' . implode(', ', $d) . ']' : ''),
        $d === [] && str_contains((string)$w['body'], 'Krok 3 ze 4') && str_contains((string)$w['body'], 'Správně 0 z 3 polí') && str_contains((string)$w['body'], 'Opravit odpovědi'));
    $ref = tb75_student_ref('class_3a', $key3);
    $answers = array_column((array)tb75_current('class_3a', $ref, 'net_ports')['variant']['fields'], 'answer', 'id');
    $r = $hA->request('POST', '/', ['action' => 'tb75_answer', 'task' => 'net_ports', 'back' => 'ulohy', 'a' => $answers, 'csrf' => $tCsrf], ['follow_redirects' => true]);
    $t4 = (string)$hA->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'net_ports', 'krok' => '4'])['body'];
    $check('Úloha: správné řešení → Kontrola „všechna pole sedí“ s klíčem; Co dál = Další varianta (tb75_next), Připnout do portfolia, Všechny úlohy',
        str_contains((string)$r['body'], 'Správně, všechna pole sedí') && str_contains($t4, 'Krok 4 ze 4') && str_contains($t4, 'value="tb75_next"') && str_contains($t4, 'Připnout do portfolia'));

    // ------------------------------------------------------------ 6) Žák – připnutí práce (Odkaz → Reflexe → Uložit)
    $pf = (string)$hA->request('GET', '/', ['view' => 'portfolio'])['body'];
    $pCsrf = (string)$hA->csrfToken($pf);
    $baseP = ['action' => 'pf75_s_pin', 'sp79_flow' => 'portfolio'];
    $dataP = ['kind' => 'link', 'title' => 'Plakát kontrast', 'url' => 'https://example.org/plakat', 'reflection' => 'Hierarchie a kontrast.'];
    $s0 = $hashes($tmp);
    $e1 = $hA->request('POST', '/', ['sp79_step' => '1', 'action' => 'sp79_s_next', 'csrf' => $pCsrf] + $baseP, ['follow_redirects' => true]);
    $e2 = $hA->request('POST', '/', ['sp79_step' => '1', 'action' => 'sp79_s_next', 'csrf' => $pCsrf, 'url' => 'http://example.org/x'] + $baseP + $dataP, ['follow_redirects' => true]);
    $n1 = $hA->request('POST', '/', ['sp79_step' => '1', 'action' => 'sp79_s_next', 'csrf' => $pCsrf] + $baseP + $dataP, ['follow_redirects' => false]);
    $n2 = $hA->request('POST', '/', ['sp79_step' => '2', 'action' => 'sp79_s_next', 'csrf' => $pCsrf] + $baseP + $dataP, ['follow_redirects' => false]);
    $bk = $hA->request('POST', '/', ['sp79_step' => '3', 'action' => 'sp79_s_back', 'csrf' => $pCsrf] + $baseP + $dataP, ['follow_redirects' => true]);
    $s3 = (string)$hA->request('GET', '/', ['view' => 'portfolio', 'krok' => '3'])['body'];
    $check('Portfolio: krok 1 Odkaz – bez volby chyba, http odkaz chyba u pole a název zůstane; Další → Reflexe → Uložit (souhrn), Zpět drží reflexi; koncept nic nezapisuje; URL bez dat žáka',
        str_contains($pf, 'Krok 1 ze 3: <strong>Odkaz</strong>') && str_contains((string)$e1['body'], '<a href="#pf79-kind-link">') && str_contains((string)$e2['body'], '<a href="#pf75-url">')
        && str_contains((string)$e2['body'], 'value="Plakát kontrast"') && str_contains($loc($n1), 'krok=2') && str_contains($loc($n2), 'krok=3') && !str_contains($loc($n2), 'example.org')
        && str_contains((string)$bk['body'], 'Krok 2 ze 3') && str_contains((string)$bk['body'], '>Hierarchie a kontrast.</textarea>') && str_contains($s3, '<dt>Odkaz</dt><dd>https://example.org/plakat') && $hashes($tmp) === $s0);
    $before = $hashes($tmp);
    $fin = $hA->request('POST', '/', $baseP + ['sp79_step' => '3', 'csrf' => $pCsrf] + $dataP, ['follow_redirects' => true]);
    $pfB = (string)$hB->request('GET', '/', ['view' => 'portfolio'])['body'];
    $hB->request('POST', '/', ['action' => 'pf75_s_pin', 'csrf' => (string)$hB->csrfToken($pfB)] + $dataP, ['follow_redirects' => true]);
    $d = $same($before, $hashes($tmp), 'portfolio');
    $check('Portfolio: Uložit = pf75_s_pin se stejným záznamem v portfoliu jako původní formulář; potom „Hotovo – práce je připnutá“' . ($d ? ' [' . implode(', ', $d) . ']' : ''),
        $d === [] && str_contains((string)$fin['body'], 'Hotovo – práce je připnutá v portfoliu.') && str_contains((string)$fin['body'], 'Plakát kontrast'));
    $hA->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'en', 'return' => '?view=portfolio', 'csrf' => $pCsrf], ['follow_redirects' => true]);
    $en = (string)$hA->request('GET', '/', ['view' => 'portfolio'])['body'];
    $enT = (string)$hA->request('GET', '/', ['view' => 'ulohy', 'uloha' => 'net_ports'])['body'];
    $hA->request('POST', '/', ['action' => 'edu_set_lang', 'lang' => 'uk', 'return' => '?view=portfolio', 'csrf' => (string)$hA->csrfToken($en)], ['follow_redirects' => true]);
    $uk = (string)$hA->request('GET', '/', ['view' => 'portfolio'])['body'];
    $check('žák en/uk: pruh kroků a texty stepperu přeložené (Step 1 of 3 / Крок 1 з 3, Link / Посилання)', str_contains($en, 'Step 1 of 3') && str_contains($en, 'Pin a new piece of work')
        && str_contains($enT, 'Step ') && str_contains($uk, 'Крок 1 з 3') && str_contains($uk, 'Закріпити нову роботу'));
} finally {
    $hA->stop();
    $hB->stop();
}

// ------------------------------------------------------------ oprávnění: asistent (jen čtení) koncept neuloží
$hS = Harness::start($env($tmp, $cacheA, 'assistant'));
try {
    audit_login_teacher($hS, V79S_KEY, 'Audit Asistent');
    $sp = $hS->request('GET', '/teacher.php?tab=reflexe73&class=class_3a&lesson=9');
    $sPost = $hS->request('POST', '/teacher.php', ['action' => 'sp79_next', 'sp79_flow' => 'reflexe', 'sp79_step' => '1', 'class_id' => 'class_3a', 'lesson' => '9', 'state' => 'cele', 'csrf' => (string)$hS->csrfToken((string)$sp['body'])], ['follow_redirects' => true]);
    $check('asistent: Reflexe jen ke čtení (bez formuláře a stepperu), sp79_next odmítnut (oprávnění toku content.manage) a koncept nevznikne',
        str_contains((string)$sp['body'], 'Máte jen čtení') && !str_contains((string)$sp['body'], 'value="lr73_save"') && str_contains((string)$sPost['body'], 'Máte jen čtení – tento postup dokončí učitel s oprávněním.'));
} finally {
    $hS->stop();
}

// ------------------------------------------------------------ 4) prohlížeč
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$mode = (string)getenv('EDUCANET_STEPPER_BROWSER');
if ($mode === 'skip') { echo 'SKIP  prohlížečová část (EDUCANET_STEPPER_BROWSER=skip)' . PHP_EOL; return; }
$bh = Harness::start($env($tmp, $cacheA));
try {
    $shots = (string)getenv('EDUCANET_STEPPER_SHOTS');
    $cmd = escapeshellarg($node) . ' ' . escapeshellarg(__DIR__ . '/v79_stepper_browser.cjs') . ' ' . escapeshellarg($bh->baseUrl()) . ' ' . escapeshellarg(V79S_KEY) . ' ' . escapeshellarg($label3) . ($shots !== '' ? ' ' . escapeshellarg($shots) : '');
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn';
    if ($mode === 'require') $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_STEPPER_BROWSER=require ji vynutí)' . PHP_EOL;
    return;
}
$rows = (array)($d['rows'] ?? []);
$check('prohlížeč: skript doběhl bez chyby (9 stránek toků × 11 šířek)', !isset($d['error']) && count($rows) === 99);
$bad = array_filter($rows, static fn(array $r): bool => $r['hscroll'] || $r['smallCount'] > 0 || !$r['stepper']);
$check('prohlížeč: 11 šířek (320–1920, 1366 × 600) – 0× vodorovný posun, 0 cílů stepperu a polí kroku < 44 px, stepper vždy vidět'
    . ($bad ? ' [' . implode('; ', array_map(static fn(array $r): string => $r['page'] . '@' . $r['width'] . ' h=' . (int)$r['hscroll'] . ' s=' . $r['smallCount'] . ' ' . implode(',', (array)$r['small']), array_slice($bad, 0, 4))) . ']' : ''), $bad === []);
$compact = array_filter($rows, static fn(array $r): bool => $r['mini'] !== ($r['barWidth'] < 480) || $r['list'] === $r['mini']);
$check('prohlížeč: kompaktní stepper („2/4“ + název) jen při kontejneru pod 480 px, jinak celý pruh kroků', $compact === []);
$kb = (array)($d['keyboard'] ?? []);
$check('klávesnice + JS: Enter na „Další“ bez stavu → souhrn chyb s fokusem a aria-invalid (bez odeslání); mezerník vybere stav, Enter → krok 2 s fokusem na nadpisu kroku; Zpět (Enter) → krok 1, stav zůstane',
    ($kb['blocked'] ?? false) && ($kb['summaryFocused'] ?? false) && ($kb['invalid'] ?? false) && ($kb['step2'] ?? false) && ($kb['headingFocused'] ?? false) && ($kb['backKept'] ?? false));
if (!empty($d['shots'])) echo 'INFO  snímky: ' . count((array)$d['shots']) . PHP_EOL;
