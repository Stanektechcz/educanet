<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček úloh „ctf“ (ARN-02, inspirace picoCTF) + registrace kontextu ctf:<id>.
 *
 * 18 úloh v 5 kategoriích (forenzní analýza, kódování, web, linux, sítě). Balíček je schovaný z běžného
 * procvičování (classes => [ARENA58_CTF_HIDDEN_CLASS], třída, která nikdy neexistuje) – úlohy jsou dostupné
 * jen přes vyhlášenou akci CTF týdne (kontext ctf:<id>, logika v arena_v58_ctf.php). Reálná logika akcí
 * (přístup, body, žebříček) je v arena_v58_ctf.php – tenhle soubor se na ni odkazuje jen přes function_exists,
 * protože v pořadí načítání ještě nemusí být hotová (stejný vzor jako jádrový kontext „race“ → arena_v57.php).
 *
 * Bezpečnostní invariant: nic se nespouští, žádná síť. „curl“ na cvičný server je čistě simulace uvnitř
 * Lab57World (net.http), IP adresy jsou z RFC 5737 (192.0.2.0/24, TEST-NET-1).
 */

// ---------------------------------------------------------------------------
// Drobné šifrovací pomocníky (jen pro stavbu úloh – žádné externí knihovny)
// ---------------------------------------------------------------------------

function arena58_ctf_rot13(string $s): string
{
    return strtr($s, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', 'NOPQRSTUVWXYZABCDEFGHIJKLMnopqrstuvwxyzabcdefghijklm');
}

/** Caesarova šifra s posunem +3 (klasický „Suetoniův“ posun): A→D, ..., X→A, Y→B, Z→C. */
function arena58_ctf_caesar3(string $s): string
{
    return strtr($s, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', 'DEFGHIJKLMNOPQRSTUVWXYZABCdefghijklmnopqrstuvwxyzabc');
}

// ---------------------------------------------------------------------------
// Kontext ctf:<id> – tenké provázání na arena_v58_ctf.php
// ---------------------------------------------------------------------------

lab58_register_context('ctf', [
    'label' => 'CTF týden',
    'access' => static fn(array $level, array $ctx): ?string => function_exists('arena58_ctf_access') ? arena58_ctf_access($level, $ctx) : 'CTF týden momentálně není dostupný.',
    'levels' => static function (array $ctx): ?array {
        if (!function_exists('arena58_ctf_event') || !function_exists('arena58_ctf_level_ids')) return null;
        $event = arena58_ctf_event((string)$ctx['id']);
        return $event !== null ? arena58_ctf_level_ids($event) : [];
    },
    'state_key' => static fn(array $ctx): string => function_exists('arena58_ctf_state_key') ? arena58_ctf_state_key($ctx) : (string)$ctx['student'],
    'info' => static fn(array $ctx): ?array => function_exists('arena58_ctf_info') ? arena58_ctf_info($ctx) : null,
    'on_complete' => static function (array $ctx, array $level, array $event): void {
        if (function_exists('arena58_ctf_on_complete')) arena58_ctf_on_complete($ctx, $level, $event);
    },
    'board' => static fn(array $ctx): array => function_exists('arena58_ctf_board_cb') ? arena58_ctf_board_cb($ctx) : [],
    'first_blood' => true,
]);

// ---------------------------------------------------------------------------
// Balíček a úlohy
// ---------------------------------------------------------------------------

lab58_register_pack(
    [
        'id' => 'ctf', 'title' => 'CTF týden', 'description' => 'Sezónní soutěž ve vyhledávání vlajek – dostupná jen během vyhlášené akce.',
        'order' => 80, 'classes' => [defined('ARENA58_CTF_HIDDEN_CLASS') ? ARENA58_CTF_HIDDEN_CLASS : 'zzz_nikdy_trida_ctf58'],
        'unlock' => 'free', 'badge' => ['id' => 'ctf58', 'label' => 'Lovec vlajek', 'icon' => '🚩'], 'icon' => '🚩', 'tone' => 'violet', 'inspired' => 'picoCTF', 'source' => 'v58',
    ],
    static function (): array {
        return [
            // ---------------------------------------------------------- FORENZNÍ ANALÝZA
            [
                'id' => 'ctf-for-1', 'pack' => 'ctf', 'type' => 'code', 'category' => 'forenzni', 'title' => 'Přihlašovací deník',
                'difficulty' => 1, 'points' => 100, 'minutes' => 5,
                'story' => 'Bezpečnostní tým ti poslal výřez z přihlašovacího logu serveru. Většina pokusů o přihlášení selhala – ale jeden prošel.',
                'task' => 'Najdi řádek s úspěšným přihlášením (Accepted) a odešli klíč, který je u něj napsaný.',
                'commands' => ['grep', 'cat'],
                'hints' => ['Neúspěšné pokusy mají v logu slovo „Failed“, úspěšný „Accepted“.', 'grep Accepted ukoly/pristupy.log'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $ip = '203.0.113.' . $r->int(10, 250);
                    $users = ['root', 'admin', 'ucet', 'test', 'www-data', 'zaloha'];
                    $n = $r->int(26, 36);
                    $lines = [];
                    for ($i = 0; $i < $n; $i++) $lines[] = 'sshd[' . $r->int(1000, 9999) . ']: Failed password for ' . (string)$r->pick($users) . ' from 198.51.100.' . $r->int(2, 250) . ' port ' . $r->int(1024, 65000) . ' ssh2';
                    array_splice($lines, $r->int(4, $n - 3), 0, ['sshd[' . $r->int(1000, 9999) . ']: Accepted password for admin from ' . $ip . ' port ' . $r->int(1024, 65000) . ' ssh2 klic=' . $w->code()]);
                    $out = '';
                    foreach ($lines as $i => $line) $out .= date('M d H:i:s', $w->now - (count($lines) - $i) * 41) . ' lab-pc ' . $line . "\n";
                    lab57_home_file($w, 'ukoly/pristupy.log', $out);
                },
                'solution' => static fn(Lab57World $w): array => ['grep Accepted ukoly/pristupy.log', 'submit ' . $w->code()],
                'learn' => 'V bezpečnostních logách hledáš jehlu v kupce sena. grep s klíčovým slovem (Failed/Accepted) ji najde okamžitě.',
            ],
            [
                'id' => 'ctf-for-2', 'pack' => 'ctf', 'type' => 'code', 'category' => 'forenzni', 'title' => 'Podezřelý požadavek',
                'difficulty' => 1, 'points' => 100, 'minutes' => 5,
                'story' => 'Ve výřezu z webového logu je spousta běžných návštěv – a jeden požadavek s divným parametrem v adrese.',
                'task' => 'Najdi řádek s parametrem klic= v adrese a odešli jeho hodnotu.',
                'commands' => ['grep', 'cat'],
                'hints' => ['Zkus grep na text "klic=" v souboru s logem.', 'grep klic= ukoly/pristup.log'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $paths = ['/index.html', '/o-nas.html', '/kontakt.html', '/produkty.html', '/style.css', '/img/logo.png', '/favicon.ico'];
                    $rows = [];
                    $n = $r->int(28, 40);
                    for ($i = 0; $i < $n; $i++) $rows[] = ['h' => $r->int(8, 17), 'm' => $r->int(0, 59), 's' => $r->int(0, 59), 'ip' => '10.0.' . $r->int(1, 254) . '.' . $r->int(1, 254), 'path' => (string)$r->pick($paths)];
                    $rows[] = ['h' => $r->int(1, 4), 'm' => $r->int(0, 59), 's' => $r->int(0, 59), 'ip' => '198.51.100.' . $r->int(2, 250), 'path' => '/skryta-' . $r->token(6) . '/?klic=' . $w->code()];
                    usort($rows, static fn(array $a, array $b): int => [$a['h'], $a['m'], $a['s']] <=> [$b['h'], $b['m'], $b['s']]);
                    $out = '';
                    foreach ($rows as $row) $out .= sprintf("%s - - [24/Nov/2025:%02d:%02d:%02d +0100] \"GET %s HTTP/1.1\" 200 %d \"-\" \"Mozilla/5.0\"\n", $row['ip'], $row['h'], $row['m'], $row['s'], $row['path'], $r->int(200, 4200));
                    lab57_home_file($w, 'ukoly/pristup.log', $out);
                },
                'solution' => static fn(Lab57World $w): array => ['grep klic= ukoly/pristup.log', 'submit ' . $w->code()],
                'learn' => 'Weblogy mají tisíce řádků. Hledej podle toho, co je NEOBVYKLÉ – divný parametr, cizí IP, noční čas.',
            ],
            [
                'id' => 'ctf-for-3', 'pack' => 'ctf', 'type' => 'code', 'category' => 'forenzni', 'title' => 'Otočený log',
                'difficulty' => 2, 'points' => 150, 'minutes' => 6,
                'story' => 'Aktuální log je čistý – ale logy se pravidelně „otáčí“ (rotují) a stará verze zůstává vedle s příponou .1.',
                'task' => 'Najdi předchozí (otočený) log a odešli klíč, který je v něm zapsaný.',
                'commands' => ['ls', 'cat'],
                'hints' => ['ls -la ukáže i soubory, které bys na první pohled přehlédl(a).', 'Otočený soubor má stejné jméno + .1 na konci.', 'cat ukoly/zabezpeceni.log.1'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $current = '';
                    for ($i = 0; $i < $r->int(10, 16); $i++) $current .= '[INFO] kontrola OK #' . $i . "\n";
                    lab57_home_file($w, 'ukoly/zabezpeceni.log', $current . "[INFO] log byl otočen (rotace)\n");
                    lab57_home_file($w, 'ukoly/zabezpeceni.log.1', "[INFO] běžný provoz\n[WARN] neobvyklý přístup, klíč: " . $w->code() . "\n[INFO] konec předchozího záznamu\n");
                },
                'solution' => static fn(Lab57World $w): array => ['ls -la ukoly', 'cat ukoly/zabezpeceni.log.1', 'submit ' . $w->code()],
                'learn' => 'Logy se rotují, aby nezaplnily disk (logrotate). Stopa ale často zůstává ve staré, „otočené“ kopii.',
            ],
            // ---------------------------------------------------------- KÓDOVÁNÍ
            [
                'id' => 'ctf-kod-1', 'pack' => 'ctf', 'type' => 'code', 'category' => 'kodovani', 'title' => 'Base64 z obou stran',
                'difficulty' => 1, 'points' => 100, 'minutes' => 4,
                'story' => 'Soubor zprava.b64 je zakódovaný v Base64 – ale dvakrát po sobě.',
                'task' => 'Dekóduj zprávu (dvakrát) a odešli kód.',
                'commands' => ['base64', 'cat'],
                'hints' => ['base64 -d dekóduje jednu vrstvu. Zkus to dvakrát za sebou v rouře.', "base64 -d zprava.b64 | base64 -d"],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    lab57_home_file($w, 'zprava.b64', base64_encode(base64_encode('Vlajka: ' . $w->code() . "\n")) . "\n");
                },
                'solution' => static fn(Lab57World $w): array => ['base64 -d zprava.b64 | base64 -d', 'submit ' . $w->code()],
                'learn' => 'Roura (|) posílá výstup jednoho příkazu na vstup dalšího – tak se dá „obalování“ postupně sundat.',
            ],
            [
                'id' => 'ctf-kod-2', 'pack' => 'ctf', 'type' => 'code', 'category' => 'kodovani', 'title' => 'Zachycený paket',
                'difficulty' => 1, 'points' => 100, 'minutes' => 4,
                'story' => 'Zachycená data ze sítě jsou zapsaná po bajtech v šestnáctkové (hex) soustavě – soubor paket.hex.',
                'task' => 'Převeď hex zpět na text a odešli kód.',
                'commands' => ['xxd', 'cat'],
                'hints' => ['Dva znaky 0–9/a–f = jeden bajt (např. 41 = A).', 'xxd -r -p paket.hex'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    lab57_home_file($w, 'paket.hex', bin2hex('Payload: ' . $w->code() . "\n") . "\n");
                },
                'solution' => static fn(Lab57World $w): array => ['xxd -r -p paket.hex', 'submit ' . $w->code()],
                'learn' => 'Hex je čitelný zápis bajtů – síťové analyzátory (Wireshark apod.) ho ukazují běžně.',
            ],
            [
                'id' => 'ctf-kod-3', 'pack' => 'ctf', 'type' => 'code', 'category' => 'kodovani', 'title' => 'ROT13 hláška',
                'difficulty' => 1, 'points' => 100, 'minutes' => 4,
                'story' => 'Vzkaz v souboru vzkaz.rot13 je posunutý o 13 písmen v abecedě. Stejný posun ho zase vrátí.',
                'task' => 'Rozšifruj vzkaz a odešli kód.',
                'commands' => ['tr', 'cat'],
                'hints' => ["Posun o 13 zpátky je tr 'A-Za-z' 'N-ZA-Mn-za-m'.", "tr 'A-Za-z' 'N-ZA-Mn-za-m' < vzkaz.rot13"],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    lab57_home_file($w, 'vzkaz.rot13', arena58_ctf_rot13('Gratuluji, kod je ' . $w->code()) . "\n");
                },
                'solution' => static fn(Lab57World $w): array => ["tr 'A-Za-z' 'N-ZA-Mn-za-m' < vzkaz.rot13", 'submit ' . $w->code()],
                'learn' => 'ROT13 je Caesarova šifra s posunem 13 – nic nechrání, jen ukazuje princip substituce.',
            ],
            [
                'id' => 'ctf-kod-4', 'pack' => 'ctf', 'type' => 'code', 'category' => 'kodovani', 'title' => 'Caesar posunutý o tři',
                'difficulty' => 2, 'points' => 150, 'minutes' => 5,
                'story' => 'Tenhle vzkaz používá klasický Caesarův posun (jako to dělal už římský vojevůdce) – každé písmeno je posunuté o 3 dopředu.',
                'task' => 'Rozšifruj soubor tajenka.txt (posuň písmena o 3 zpátky) a odešli kód.',
                'commands' => ['tr', 'cat'],
                'hints' => ['Posun +3 dopředu se vrátí posunem -3 (= +23).', "tr 'D-ZA-Cd-za-c' 'A-Za-z' < tajenka.txt"],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    lab57_home_file($w, 'tajenka.txt', arena58_ctf_caesar3('Heslo zni: ' . $w->code()) . "\n");
                },
                'solution' => static fn(Lab57World $w): array => ["tr 'D-ZA-Cd-za-c' 'A-Za-z' < tajenka.txt", 'submit ' . $w->code()],
                'learn' => 'Caesarova šifra má jen 25 možných posunů – dá se rozlousknout i hrubou silou (zkoušením všech).',
            ],
            [
                'id' => 'ctf-kod-5', 'pack' => 'ctf', 'type' => 'code', 'category' => 'kodovani', 'title' => 'XOR s krátkým klíčem',
                'difficulty' => 3, 'points' => 200, 'minutes' => 10,
                'story' => 'Zpráva je zašifrovaná operací XOR s krátkým klíčem (2 čísla, opakují se dokola). Soubor sifra.txt má klíč napsaný přímo v sobě.',
                'task' => 'Ruční výpočet: XOR platí, že (a XOR b) XOR b = a. Vezmi si kalkulačku, XORuj každý bajt zprávy s odpovídajícím číslem klíče (klíč se opakuje: 1., 2., 1., 2., …) a výsledná čísla přečti jako ASCII znaky. Odešli kód, který se objeví.',
                'commands' => ['cat'],
                'hints' => [
                    'Bajty jsou čísla 0–255. XOR dvou čísel spočítá i kalkulačka s funkcí XOR/BIN, nebo tabulkově po bitech.',
                    'Příklad: pokud šifrový bajt je 101 a klíč 84, pak 101 XOR 84 = 53, což je znak „5“ v tabulce ASCII.',
                    'Klíč se opakuje po dvou číslech: 1. bajt s prvním číslem klíče, 2. bajt s druhým, 3. bajt zase s prvním…',
                ],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $k1 = $r->int(10, 99);
                    $k2 = $r->int(10, 99);
                    $key = [$k1, $k2];
                    $msg = 'Vlajka: ' . $w->code();
                    $bytes = [];
                    foreach (array_values(unpack('C*', $msg) ?: []) as $i => $b) $bytes[] = $b ^ $key[$i % 2];
                    lab57_home_file($w, 'sifra.txt', "Klic (2 cisla, opakuji se): $k1 $k2\nZasifrovana zprava (desitkove bajty, oddelene mezerou):\n" . implode(' ', $bytes) . "\n");
                },
                'solution' => static fn(Lab57World $w): array => ['cat sifra.txt', 'submit ' . $w->code()],
                'learn' => 'XOR je vlastní sám sobě inverzí – proto je tak oblíbený v jednoduchých šifrách i kontrolních součtech.',
            ],
            // ---------------------------------------------------------- WEB
            [
                'id' => 'ctf-web-1', 'pack' => 'ctf', 'type' => 'code', 'category' => 'web', 'title' => 'Skrytá hlavička',
                'difficulty' => 1, 'points' => 100, 'minutes' => 5,
                'story' => 'Cvičný server cviciny.skola.test vypadá nudně – ale odpověď má i hlavičky, které se běžně nezobrazí.',
                'task' => 'Zjisti hlavičky odpovědi serveru a odešli hodnotu hlavičky X-Vlajka.',
                'commands' => ['curl'],
                'hints' => ['Jen hlavičky (bez těla stránky) ukáže curl -I.', 'curl -I http://cviciny.skola.test'],
                'world' => ['hostname' => 'ucebna-pc'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void { $w->mem['web1_ip'] = '192.0.2.' . $r->int(10, 240); },
                'net' => static function (Lab57World $w): void {
                    $ip = (string)($w->mem['web1_ip'] ?? '192.0.2.50');
                    $w->net['nodes']['ctfweb1'] = ['ip' => $ip, 'name' => 'cviciny.skola.test', 'kind' => 'server', 'label' => 'Cvičný server', 'zone' => 'inet', 'ttl' => 64, 'lat' => 9.0, 'ports' => [80 => 'http'], 'hidden' => true, 'x' => 600, 'y' => 60];
                    $w->net['dns']['cviciny.skola.test'] = ['A' => [$ip]];
                    $w->net['http'][$ip . ':80'] = ['/' => [200, 'text/html', "<html><body><h1>Cvičný server</h1><p>Nic zajímavého tu není… nebo je?</p></body></html>\n", ['X-Vlajka' => $w->code()]]];
                },
                'solution' => static fn(Lab57World $w): array => ['curl -I http://cviciny.skola.test', 'submit ' . $w->code()],
                'learn' => 'Servery posílají hlavičky, které prohlížeč běžně schová. curl -I (nebo -i) je odhalí.',
            ],
            [
                'id' => 'ctf-web-2', 'pack' => 'ctf', 'type' => 'code', 'category' => 'web', 'title' => 'Cookie s tajemstvím',
                'difficulty' => 2, 'points' => 150, 'minutes' => 6,
                'story' => 'Po „přihlášení“ na prihlaseni.skola.test ti server pošle cookie – v ní je schovaný kód.',
                'task' => 'Otevři /prihlaseni s hlavičkami a přečti hodnotu cookie.',
                'commands' => ['curl'],
                'hints' => ['curl -i ukáže hlavičky i tělo stránky. Hledej řádek Set-Cookie.', 'curl -i http://prihlaseni.skola.test/prihlaseni'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void { $w->mem['web2_ip'] = '192.0.2.' . $r->int(10, 240); },
                'net' => static function (Lab57World $w): void {
                    $ip = (string)($w->mem['web2_ip'] ?? '192.0.2.51');
                    $w->net['nodes']['ctfweb2'] = ['ip' => $ip, 'name' => 'prihlaseni.skola.test', 'kind' => 'server', 'label' => 'Přihlašovací server', 'zone' => 'inet', 'ttl' => 64, 'lat' => 9.4, 'ports' => [80 => 'http'], 'hidden' => true, 'x' => 600, 'y' => 100];
                    $w->net['dns']['prihlaseni.skola.test'] = ['A' => [$ip]];
                    $w->net['http'][$ip . ':80'] = [
                        '/' => [200, 'text/html', "<html><body><p>Vítej. Přihlas se na /prihlaseni.</p></body></html>\n"],
                        '/prihlaseni' => [200, 'text/html', "<html><body>Přihlášení proběhlo. Tady je tvoje session.</body></html>\n", ['Set-Cookie' => 'session=' . $w->code() . '; Path=/']],
                    ];
                },
                'solution' => static fn(Lab57World $w): array => ['curl -i http://prihlaseni.skola.test/prihlaseni', 'submit ' . $w->code()],
                'learn' => 'Cookies se posílají v hlavičce Set-Cookie. Prohlížeč je schová do „paměti“, ale síťově jsou vidět jako čitelný text.',
            ],
            [
                'id' => 'ctf-web-3', 'pack' => 'ctf', 'type' => 'code', 'category' => 'web', 'title' => 'Co nesmíš vidět',
                'difficulty' => 2, 'points' => 150, 'minutes' => 7,
                'story' => 'Server tajny.skola.test má soubor robots.txt, který říká vyhledávačům, co nemají procházet. Jenže to prozradí, kde se co skrývá.',
                'task' => 'Přečti si robots.txt, najdi zakázanou cestu a otevři ji.',
                'commands' => ['curl'],
                'hints' => ['curl http://tajny.skola.test/robots.txt', 'Řádek Disallow: ukazuje cestu, kterou nemá procházet ani vyhledávač.'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $w->mem['web3_ip'] = '192.0.2.' . $r->int(10, 240);
                    $w->mem['web3_token'] = $r->token(8);
                },
                'net' => static function (Lab57World $w): void {
                    $ip = (string)($w->mem['web3_ip'] ?? '192.0.2.52');
                    $token = (string)($w->mem['web3_token'] ?? 'skryto01');
                    $w->net['nodes']['ctfweb3'] = ['ip' => $ip, 'name' => 'tajny.skola.test', 'kind' => 'server', 'label' => 'Tajný server', 'zone' => 'inet', 'ttl' => 64, 'lat' => 9.8, 'ports' => [80 => 'http'], 'hidden' => true, 'x' => 600, 'y' => 140];
                    $w->net['dns']['tajny.skola.test'] = ['A' => [$ip]];
                    $w->net['http'][$ip . ':80'] = [
                        '/' => [200, 'text/html', "<html><body>Nic tu není.</body></html>\n"],
                        '/robots.txt' => [200, 'text/plain', "User-agent: *\nDisallow: /$token/\n"],
                        '/' . $token . '/' => [200, 'text/html', '<html><body>Vlajka: ' . $w->code() . "</body></html>\n"],
                    ];
                },
                'solution' => static fn(Lab57World $w): array => ['curl http://tajny.skola.test/robots.txt', 'curl http://tajny.skola.test/' . (string)($w->mem['web3_token'] ?? '') . '/', 'submit ' . $w->code()],
                'learn' => 'robots.txt je jen zdvořilá prosba pro vyhledávače, ne zámek. „Utajení cesty“ není bezpečnost.',
            ],
            // ---------------------------------------------------------- LINUX
            [
                'id' => 'ctf-lin-1', 'pack' => 'ctf', 'type' => 'code', 'category' => 'linux', 'title' => 'Zamčený soubor',
                'difficulty' => 1, 'points' => 100, 'minutes' => 4,
                'story' => 'Soubor je tvůj, ale nemáš k němu vůbec žádná práva – ani ty jako vlastník ho nepřečteš.',
                'task' => 'Oprav práva souboru ukoly/tajemstvi.txt tak, abys ho mohl(a) přečíst, a odešli kód.',
                'commands' => ['ls', 'chmod', 'cat'],
                'hints' => ['ls -l ukoly ukáže práva --------- (nikdo nic).', 'chmod u+r ukoly/tajemstvi.txt (nebo chmod 600).'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void { lab57_home_file($w, 'ukoly/tajemstvi.txt', 'Vlajka: ' . $w->code() . "\n", 0000); },
                'solution' => static fn(Lab57World $w): array => ['ls -l ukoly', 'chmod u+r ukoly/tajemstvi.txt', 'cat ukoly/tajemstvi.txt', 'submit ' . $w->code()],
                'learn' => 'Bez práva „r“ soubor nepřečte ani vlastník. chmod u+r přidá právo číst jen vlastníkovi.',
            ],
            [
                'id' => 'ctf-lin-2', 'pack' => 'ctf', 'type' => 'code', 'category' => 'linux', 'title' => 'Jehla v kupce sena',
                'difficulty' => 2, 'points' => 150, 'minutes' => 6,
                'story' => 'Ve složce ~/data je spousta starých záznamů rozházených po podsložkách. Jeden soubor jménem vlajka.txt je schovaný hluboko uvnitř.',
                'task' => 'Najdi soubor vlajka.txt a odešli kód, který obsahuje.',
                'commands' => ['find', 'cat'],
                'hints' => ['find prohledá celý strom složek najednou: find ~/data -name vlajka.txt', 'Až najdeš cestu, přečti ji: cat <cesta>'],
                'generate' => [
                    ['decoys', ['dir' => '~/data/2024/q1', 'count' => 10, 'name' => 'zaznam-{N}.log']],
                    ['decoys', ['dir' => '~/data/2024/q2', 'count' => 10, 'name' => 'zaznam-{N}.log']],
                    ['code_file', ['dirs' => ['~/data/2025/archiv/leden', '~/data/2025/archiv/unor', '~/data/2025/zaloha'], 'names' => ['vlajka.txt']]],
                ],
                'solution' => ['find ~/data -name vlajka.txt', 'cat {f:code_path}', 'submit {CODE}'],
                'learn' => 'find prohledává celý strom složek najednou podle jména, velikosti nebo stáří – ruční procházení by trvalo věčnost.',
            ],
            [
                'id' => 'ctf-lin-3', 'pack' => 'ctf', 'type' => 'code', 'category' => 'linux', 'title' => 'Podezřelý proces',
                'difficulty' => 2, 'points' => 150, 'minutes' => 5,
                'story' => 'Na počítači běží proces, který nemá co dělat – a jeho příkazová řádka prozrazuje víc, než by měla.',
                'task' => 'Najdi proces, jehož příkaz obsahuje slovo „klic“, a odešli kód z něj.',
                'commands' => ['ps', 'grep'],
                'hints' => ['ps aux vypíše všechny procesy i s celým příkazem, kterým byly spuštěné.', 'ps aux | grep klic'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $pid = $r->int(3100, 3400);
                    $w->procs[$pid] = ['user' => 'student', 'cmd' => '/usr/bin/python3 tezba.py --klic=' . $w->code(), 'cpu' => 61.2, 'mem' => 2.1, 'vsz' => 41200, 'rss' => 15400, 'tty' => 'pts/2', 'stat' => 'R', 'start' => $w->now - 900, 'service' => null];
                },
                'solution' => static fn(Lab57World $w): array => ['ps aux | grep klic', 'submit ' . $w->code()],
                'learn' => 'Celou příkazovou řádku procesu (i s parametry) uvidíš v ps aux – proto se hesla nikdy nepředávají jako argument.',
            ],
            // ---------------------------------------------------------- SÍTĚ
            [
                'id' => 'ctf-net-1', 'pack' => 'ctf', 'type' => 'answer', 'category' => 'site', 'title' => 'Kolik je to skoků',
                'difficulty' => 1, 'points' => 100, 'minutes' => 4,
                'story' => 'Cesta k www.example.com vede přes několik routerů. Každý z nich je jeden „skok“ (hop).',
                'task' => 'Kolik řádků vypíše traceroute k www.example.com? Odpověz: answer <číslo>',
                'commands' => ['traceroute', 'answer'],
                'hints' => ['traceroute ukáže každý router na cestě, po jednom řádku.', 'Poslední řádek je cíl – počítá se taky.'],
                'answer_format' => 'celé číslo',
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $extra = $r->int(1, 4);
                    $path = (array)$w->net['wan_path'];
                    for ($i = 1; $i <= $extra; $i++) {
                        $id = 'ctfhop' . $i;
                        $w->net['nodes'][$id] = ['ip' => '192.0.2.' . (100 + $i), 'name' => 'r' . $i . '.poskytovatel.test', 'kind' => 'router', 'label' => 'Router ' . $i, 'zone' => 'wan', 'ttl' => 255, 'lat' => 3.0 + $i, 'x' => 400 + $i * 20, 'y' => 140, 'hidden' => true];
                        array_splice($path, 1, 0, [$id]);
                    }
                    $w->net['wan_path'] = $path;
                },
                'answer' => static fn(Lab57World $w): string => (string)(count((array)$w->net['wan_path']) + 1),
                'solution' => static fn(Lab57World $w): array => ['traceroute www.example.com', 'answer ' . (string)(count((array)$w->net['wan_path']) + 1)],
                'learn' => 'Každý router na cestě sníží TTL paketu o jedna. traceroute toho využívá, aby „osahal“ celou trasu.',
            ],
            [
                'id' => 'ctf-net-2', 'pack' => 'ctf', 'type' => 'code', 'category' => 'site', 'title' => 'Otevřené dveře',
                'difficulty' => 2, 'points' => 150, 'minutes' => 7,
                'story' => 'Na tomhle počítači poslouchá malá služba na portu mezi 31000 a 31010. Když jí pošleš správné slovo, vrátí vlajku.',
                'task' => 'Zjisti port služby a pošli jí text „vlajka“. Odešli kód, který ti vrátí.',
                'commands' => ['ss', 'nc', 'echo'],
                'hints' => ['Poslouchající porty ukáže ss -tln.', 'Text pošleš rourou: echo vlajka | nc localhost <port>'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $port = $r->int(31000, 31010);
                    $w->mem['net2_port'] = $port;
                    $w->mem['listen'] = [$port => ['proc' => 'strazce', 'user' => 'student', 'pid' => 2900, 'addr' => '127.0.0.1']];
                    $w->procs[2900] = ['user' => 'student', 'cmd' => '/usr/local/bin/strazce --port ' . $port, 'cpu' => 0.1, 'mem' => 0.2, 'vsz' => 9000, 'rss' => 3000, 'tty' => '?', 'stat' => 'S', 'start' => $w->now - 600, 'service' => null];
                },
                'net' => static function (Lab57World $w): void {
                    $port = (int)($w->mem['net2_port'] ?? 31000);
                    $code = $w->code();
                    $w->netServices['127.0.0.1:' . $port] = static fn(string $in, Lab57World $world): string => trim($in) === 'vlajka' ? "Tady je: $code\n" : "Neznamy prikaz. Posli: vlajka\n";
                },
                'solution' => static fn(Lab57World $w): array => ['ss -tln', 'echo vlajka | nc localhost ' . (int)($w->mem['net2_port'] ?? 31000), 'submit ' . $w->code()],
                'learn' => 'ss -tln vypíše naslouchající porty. nc (netcat) se k nim umí připojit a poslat/přijmout data – „švýcarský nůž“ sítí.',
            ],
            [
                'id' => 'ctf-net-3', 'pack' => 'ctf', 'type' => 'code', 'category' => 'site', 'title' => 'DNS vede k vlajce',
                'difficulty' => 2, 'points' => 150, 'minutes' => 6,
                'story' => 'Jméno vlajkovy-server.skola.test schovává IP adresu vlajkového serveru.',
                'task' => 'Zjisti IP adresu přes dig a stáhni z ní vlajku.',
                'commands' => ['dig', 'curl'],
                'hints' => ['dig +short vlajkovy-server.skola.test vrátí jen adresu.', 'curl http://<adresa>'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void { $w->mem['net3_ip'] = '192.0.2.' . $r->int(10, 240); },
                'net' => static function (Lab57World $w): void {
                    $ip = (string)($w->mem['net3_ip'] ?? '192.0.2.60');
                    $w->net['nodes']['ctfnet3'] = ['ip' => $ip, 'name' => 'vlajkovy-server.skola.test', 'kind' => 'server', 'label' => 'Vlajkový server', 'zone' => 'inet', 'ttl' => 64, 'lat' => 11.0, 'ports' => [80 => 'http'], 'hidden' => true, 'x' => 600, 'y' => 220];
                    $w->net['dns']['vlajkovy-server.skola.test'] = ['A' => [$ip]];
                    $w->net['http'][$ip . ':80'] = ['/' => [200, 'text/plain', 'Vlajka: ' . $w->code() . "\n"]];
                },
                'solution' => static fn(Lab57World $w): array => ['dig +short vlajkovy-server.skola.test', 'curl http://vlajkovy-server.skola.test/', 'submit ' . $w->code()],
                'learn' => 'dig +short je nejrychlejší způsob, jak zjistit jen IP adresu jména bez ostatních podrobností.',
            ],
            // ---------------------------------------------------------- ŘETĚZENÍ (bonus, odemyká se až po dvou úlohách)
            [
                'id' => 'ctf-chain-1', 'pack' => 'ctf', 'type' => 'code', 'category' => 'linux', 'title' => 'Bonusová skládačka',
                'difficulty' => 2, 'points' => 120, 'minutes' => 4, 'chain_after' => ['ctf-for-1', 'ctf-kod-1'],
                'story' => 'Tahle úloha se odemkne, až vyřešíš „Přihlašovací deník“ a „Base64 z obou stran“. Máš je za sebou? Skvěle, pokračuj.',
                'task' => 'V ~/bonus najdeš soubor se vzkazem. Přečti ho a odešli kód.',
                'commands' => ['cat'],
                'hints' => ['Žádné dekódování tentokrát netřeba – stačí soubor otevřít.'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void { lab57_home_file($w, 'bonus/posledni-kousek.txt', 'Skvela prace, slozil(a) jsi to! Kod: ' . $w->code() . "\n"); },
                'solution' => static fn(Lab57World $w): array => ['cat bonus/posledni-kousek.txt', 'submit ' . $w->code()],
                'learn' => 'Řetězení úloh odměňuje postup napříč kategoriemi, ne jen jednu specializaci.',
            ],
        ];
    }
);
