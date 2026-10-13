<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/taskbank_v75_gen.php';
require_once __DIR__ . '/hints_v80_gfx.php';

/**
 * EDUCANET v80 · odstupňované nápovědy a vysvětlení konkrétní chyby k úlohám s variantami (ČISTÉ funkce, nic nespouští ani neukládá).
 *
 * hn80_hints($variant)              → 3 nápovědy: 1 = postrčení, 2 = postup, 3 = vyřešený krok (spočítané z čísel varianty),
 * hn80_diagnose($variant, $answers) → pole → vysvětlení chyby pro každé ŠPATNĚ vyplněné pole (podle generátoru: záměna, posun o oktet,
 *                                     zapomenuté −2, obrácené váhy r/w/x, palce místo cm …; jinak obecný postup pole).
 * Parametry varianty se čtou ze zadání a popisků (generátory v75/v76 se nemění). Texty jsou výukový obsah (česky, obal edu_content_lang_attr()).
 * Odpověď žáka se nikam neukládá – vysvětlení se spočítá hned po kontrole a drží se jen v relaci (tb75_last.why).
 */

const HN80_LEVELS = 3;
const HN80_WHY_MAX = 240;

/** @return list<string> přesně 3 nápovědy (prázdný seznam pro neznámou úlohu). */
function hn80_hints(array $v): array
{
    $task = (string)($v['task'] ?? '');
    $fn = 'hn80_h_' . $task;
    if (!preg_match('/^[a-z]+_[a-z]+$/', $task) || !function_exists($fn)) return [];
    $hints = array_values(array_map('strval', (array)$fn($v)));
    return count($hints) === HN80_LEVELS ? $hints : [];
}

/** @return array<string,string> id pole → vysvětlení (jen chybná a vyplněná pole). */
function hn80_diagnose(array $v, array $answers): array
{
    $task = (string)($v['task'] ?? '');
    $fn = 'hn80_d_' . $task;
    $out = [];
    foreach ((array)($v['fields'] ?? []) as $f) {
        $id = (string)$f['id'];
        $given = is_string($answers[$id] ?? null) ? trim(mb_substr((string)$answers[$id], 0, 120)) : '';
        if ($given === '' || hn80_same($task, $id, $given, (string)$f['answer'])) continue;
        $why = preg_match('/^[a-z]+_[a-z]+$/', $task) === 1 && function_exists($fn) ? (string)$fn($v, $id, $given) : '';
        $out[$id] = mb_substr($why !== '' ? $why : hn80_generic($f), 0, HN80_WHY_MAX);
    }
    return $out;
}

function hn80_generic(array $f): string
{
    return (string)$f['hint'] !== '' ? 'Zkontroluj postup u tohoto pole (' . $f['hint'] . ') a zkus nápovědu.' : 'Zkontroluj postup u tohoto pole a zkus nápovědu.';
}

/** Shoda odpovědí po normalizaci úlohy (stejná jako kontrola v75). */
function hn80_same(string $task, string $fid, string $a, string $b): bool
{
    return tb75_norm($task, $fid, $a) === tb75_norm($task, $fid, $b);
}

/** Pole varianty podle id. */
function hn80_field(array $v, string $id): array
{
    foreach ((array)$v['fields'] as $f) if ((string)$f['id'] === $id) return $f;
    return ['id' => $id, 'label' => '', 'answer' => '', 'hint' => ''];
}

/** Desetinné číslo pro text (česká čárka). */
function hn80_num(float $n): string
{
    return str_replace('.', ',', tb76_num($n));
}

/** Číslo z odpovědi (čárka i tečka), jinak null. */
function hn80_float(string $s): ?float
{
    $s = str_replace(',', '.', (string)preg_replace('/\s+|px|cm|mpx|mp/i', '', $s));
    return is_numeric($s) ? (float)$s : null;
}

function hn80_ip_ok(string $s): bool
{
    return filter_var(trim($s), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
}

// ---------------------------------------------------------------------------------------------- net_subnet

/** @return array{ip:string,p:int,size:int,k:int,step:int,x:int,lo:int,hi:int,mask:string,net:string,bc:string,hosts:int}|null */
function hn80_subnet_params(array $v): ?array
{
    if (preg_match('~(\d{1,3}(?:\.\d{1,3}){3})/(\d{1,2})~', (string)$v['prompt'], $m) !== 1) return null;
    $p = (int)$m[2];
    $s = tb75_subnet($m[1], $p);
    $k = min(3, intdiv($p, 8));
    $step = 2 ** (8 - ($p - 8 * $k));
    $x = (int)explode('.', $m[1])[$k];
    $lo = intdiv($x, $step) * $step;
    return ['ip' => $m[1], 'p' => $p, 'size' => 2 ** (32 - $p), 'k' => $k, 'step' => $step, 'x' => $x, 'lo' => $lo, 'hi' => $lo + $step - 1,
        'mask' => $s['mask'], 'net' => $s['network'], 'bc' => $s['broadcast'], 'hosts' => $s['hosts']];
}

function hn80_h_net_subnet(array $v): array
{
    $q = hn80_subnet_params($v);
    if ($q === null) return [];
    $o = $q['k'] + 1;
    return [
        'Síťová adresa je první adresa bloku, broadcast poslední. Nejdřív zjisti, kolik adres má blok s maskou /' . $q['p'] . '.',
        'Počet adres bloku = 2^(32 − ' . $q['p'] . '). ' . ($q['step'] >= 256 ? 'Maska končí na hranici oktetu: celý ' . $o . '. oktet patří zařízením (síť 0, broadcast 255).'
            : 'V ' . $o . '. oktetu, kde maska končí, jdou bloky po ' . $q['step'] . ' (0, ' . $q['step'] . ', ' . (2 * $q['step']) . ' …): najdi blok, do kterého patří číslo adresy.') . ' Pro zařízení zbývá velikost bloku − 2.',
        'Vyřešený krok: /' . $q['p'] . ' = ' . $q['mask'] . ', blok má ' . $q['size'] . ' adres. V ' . $o . '. oktetu jdou bloky po ' . $q['step'] . '; číslo ' . $q['x'] . ' leží v bloku ' . $q['lo'] . '–' . $q['hi'] . '.',
    ];
}

function hn80_d_net_subnet(array $v, string $fid, string $g): string
{
    $q = hn80_subnet_params($v);
    if ($q === null) return '';
    $o = $q['k'] + 1;
    $g = trim($g);
    $next = tb75_long2ip((tb75_ip2long($q['bc']) + 1) & 0xFFFFFFFF);
    if ($fid === 'network') {
        if ($g === $q['bc']) return 'Tohle je broadcast – poslední adresa bloku. Síť je první adresa bloku.';
        if ($g === $q['ip']) return 'To je adresa zařízení. Síť dostaneš, když čísla za maskou vynuluješ (zaokrouhlíš dolů na začátek bloku).';
        if (hn80_ip_ok($g) && $q['step'] >= 256) return 'Maska /' . $q['p'] . ' končí na hranici oktetu – v síťové adrese je ' . $o . '. oktet (a všechny za ním) 0.';
        if (hn80_ip_ok($g)) return 'Zkontroluj ' . $o . '. oktet: bloky jdou po ' . $q['step'] . ', číslo ' . $q['x'] . ' patří do bloku od ' . $q['lo'] . '. Oktety za ním jsou v síťové adrese 0.';
        return 'Síťová adresa se píše jako IPv4 (čtyři čísla 0–255 oddělená tečkou).';
    }
    if ($fid === 'broadcast') {
        if ($g === $q['net']) return 'Tohle je síťová adresa – první adresa bloku. Broadcast je poslední adresa bloku.';
        if ($g === $next) return 'To už je první adresa dalšího bloku – broadcast je o 1 menší.';
        if ($g === $q['ip']) return 'To je adresa zařízení. Broadcast je poslední adresa bloku (bity za maskou samé jedničky).';
        if (hn80_ip_ok($g)) return 'Broadcast = začátek bloku + velikost bloku − 1: v ' . $o . '. oktetu ' . $q['hi'] . ', oktety za ním 255.';
        return 'Broadcastová adresa se píše jako IPv4 (čtyři čísla 0–255 oddělená tečkou).';
    }
    $n = hn80_float($g);
    if ($n === (float)$q['size']) return 'Tohle je počet všech adres bloku. Síť a broadcast zařízení nedostanou – odečti 2.';
    if ($n === (float)($q['size'] - 1)) return 'Odečti obě rezervované adresy: síť i broadcast (−2).';
    if ($n === (float)(2 ** $q['p']) || $n === (float)(2 ** $q['p'] - 2)) return 'Exponent je počet bitů pro zařízení: 32 − ' . $q['p'] . ', ne samotný prefix.';
    return 'Počet použitelných adres = 2^(32 − ' . $q['p'] . ') − 2.';
}

// ---------------------------------------------------------------------------------------------- net_mask

function hn80_mask_bits(string $mask): int
{
    return substr_count(decbin(tb75_ip2long($mask)), '1');
}

function hn80_h_net_mask(array $v): array
{
    $a = preg_match('~/(\d+)~', (string)hn80_field($v, 'mask')['label'], $m) === 1 ? (int)$m[1] : 0;
    $q = intdiv($a, 8);
    $r = $a % 8;
    $val = 256 - 2 ** (8 - $r);
    return [
        'Prefix říká, kolik jedniček je v masce zleva (z 32 bitů).',
        'Celý oktet 255 = 8 jedniček. V neúplném oktetu: 128 = 1, 192 = 2, 224 = 3, 240 = 4, 248 = 5, 252 = 6, 254 = 7 jedniček; za ním jsou nuly.',
        'Vyřešený krok: /' . $a . ' = ' . $q . ' × 8 + ' . $r . ' → ' . $q . ' oktet' . ($q === 1 ? '' : ($q < 5 ? 'y' : 'ů')) . ' 255' . ($r > 0 ? ', pak oktet s ' . $r . ' jedničkami = ' . $val : '') . ', zbytek 0.',
    ];
}

function hn80_d_net_mask(array $v, string $fid, string $g): string
{
    if ($fid === 'mask') {
        $a = preg_match('~/(\d+)~', (string)hn80_field($v, 'mask')['label'], $m) === 1 ? (int)$m[1] : 0;
        if (!hn80_ip_ok($g)) return 'Maska se píše jako IPv4, např. 255.255.255.0.';
        $bits = hn80_mask_bits($g);
        if (abs($bits - $a) === 1) return 'Maska je o jednu jedničku vedle – /' . $a . ' má přesně ' . $a . ' jedniček zleva.';
        if (abs($bits - $a) === 8) return 'Posun o celý oktet: /' . $a . ' má ' . intdiv($a, 8) . ' celé oktety 255 (8 jedniček na oktet).';
        return 'Rozepiš ' . $a . ' = ' . intdiv($a, 8) . ' × 8 + ' . ($a % 8) . ': celé oktety 255, pak hodnota pro zbylé jedničky.';
    }
    $mask = preg_match('~(\d{1,3}(?:\.\d{1,3}){3})~', (string)hn80_field($v, 'prefix')['label'], $m) === 1 ? $m[1] : '0.0.0.0';
    $b = hn80_mask_bits($mask);
    $n = hn80_float(ltrim($g, '/'));
    if ($n === (float)(32 - $b)) return 'Spočítané jsou nuly. Prefix je počet jedniček zleva.';
    if ($n !== null && abs($n - $b) === 1.0) return 'Těsně vedle – přepočítej jedničky v neúplném oktetu (např. 240 = 4, 248 = 5).';
    if ($n !== null && (int)$n === intdiv($b, 8) * 8) return 'Celé oktety sedí, ale chybí jedničky v neúplném oktetu masky.';
    return 'Sečti jedničky: každé 255 = 8, neúplný oktet podle tabulky v nápovědě 2.';
}

// ---------------------------------------------------------------------------------------------- net_ports

function hn80_h_net_ports(array $v): array
{
    $f = hn80_field($v, 'p0');
    $name = preg_match('~služby (.+)$~u', (string)$f['label'], $m) === 1 ? $m[1] : '';
    return [
        'Většina dobře známých služeb má port pod 1024; databáze a vzdálená plocha mají vyšší čísla.',
        'Pomůcka: zabezpečená varianta má jiný port (HTTP → HTTPS); 2x = přístup a přenos (FTP, SSH, Telnet, SMTP); 1xx = pošta, čas a správa (POP3, NTP, IMAP, SNMP).',
        'Vyřešený krok: služba ' . $name . ' naslouchá na portu ' . $f['answer'] . '.',
    ];
}

function hn80_d_net_ports(array $v, string $fid, string $g): string
{
    $n = hn80_float($g);
    $name = preg_match('~služby (.+)$~u', (string)hn80_field($v, $fid)['label'], $m) === 1 ? $m[1] : '';
    if ($n === null) return 'Port je celé číslo 0–65535.';
    if ($name === 'HTTP' && (int)$n === 8080) return '8080 je jen častá náhrada pro vývoj; výchozí port HTTP je jiný.';
    $owner = array_search((int)$n, tb75_ports_table(), true);
    if (is_string($owner)) return 'Port ' . (int)$n . ' patří službě ' . $owner . ', ne ' . $name . '.';
    return 'Výchozí porty určuje IANA – zkus pomůcku z nápovědy 2.';
}

// ---------------------------------------------------------------------------------------------- net_route

/** @return list<array{0:string,1:string}> trasy z ukázky */
function hn80_routes(array $v): array
{
    $out = [];
    foreach ((array)$v['data'] as $row) if (preg_match('~^(\S+/\d+)\s+via\s+(\S+)~', (string)$row, $m) === 1) $out[] = [$m[1], $m[2]];
    return $out;
}

/** @return list<array{0:string,1:string,2:int}> trasy, do nichž cíl patří (síť, hop, délka prefixu) */
function hn80_route_matches(array $routes, string $ip): array
{
    $hits = [];
    foreach ($routes as [$net, $via]) {
        [$addr, $len] = explode('/', $net);
        $mask = tb75_mask((int)$len);
        if ((tb75_ip2long($ip) & $mask) === (tb75_ip2long($addr) & $mask)) $hits[] = [$net, $via, (int)$len];
    }
    usort($hits, static fn(array $a, array $b): int => $b[2] <=> $a[2]);
    return $hits;
}

function hn80_route_dest(array $v, string $fid): string
{
    return preg_match('~pro (\S+)$~u', (string)hn80_field($v, $fid)['label'], $m) === 1 ? $m[1] : '0.0.0.0';
}

function hn80_h_net_route(array $v): array
{
    $dest = hn80_route_dest($v, 'd0');
    $hits = hn80_route_matches(hn80_routes($v), $dest);
    $best = $hits[0] ?? ['0.0.0.0/0', '', 0];
    return [
        'Pro každý cíl hledej trasy, do jejichž sítě cíl patří. Když jich sedí víc, vyhrává ta s nejdelším prefixem.',
        'Prefix /16 porovnává první 2 oktety, /24 první 3. U /22 jdou bloky ve 3. oktetu po 4. Trasa 0.0.0.0/0 sedí vždy, ale má prefix 0 – použije se, jen když nic jiného nesedí.',
        'Vyřešený krok pro ' . $dest . ': sedí ' . implode(', ', array_map(static fn(array $h): string => $h[0], $hits)) . '; nejdelší je ' . $best[0] . ' → další skok ' . $best[1] . '.',
    ];
}

function hn80_d_net_route(array $v, string $fid, string $g): string
{
    $routes = hn80_routes($v);
    $dest = hn80_route_dest($v, $fid);
    $hits = hn80_route_matches($routes, $dest);
    $best = $hits[0] ?? null;
    $chosen = null;
    foreach ($routes as [$net, $via]) if ($via === trim($g)) $chosen = $net;
    if ($chosen === null || $best === null) return 'Vyber další skok z tabulky.';
    $hit = array_values(array_filter($hits, static fn(array $h): bool => $h[0] === $chosen));
    if ($hit === []) return 'Cíl ' . $dest . ' do sítě ' . $chosen . ' nepatří – porovnej oktety podle prefixu.';
    if ($chosen === '0.0.0.0/0') return 'Výchozí trasa se použije jen tehdy, když nesedí nic jiného – tady sedí ' . $best[0] . '.';
    return 'Trasa ' . $chosen . ' cíl obsahuje, ale ' . $best[0] . ' má delší prefix (/' . $best[2] . ') a vyhrává.';
}

// ---------------------------------------------------------------------------------------------- lnx_path

/** @return array{start:string,home:string,cmds:list<string>,states:list<string>}|null */
function hn80_path_params(array $v): ?array
{
    if (preg_match('~složce (\S+) \(uživatel ([a-z]+)\)~u', (string)$v['prompt'], $m) !== 1) return null;
    $cmds = array_values(array_filter(array_map(static fn(string $l): string => preg_match('~^\$ cd (.+)$~', $l, $c) === 1 ? $c[1] : '', (array)$v['data'])));
    $cwd = $m[1];
    $states = [];
    foreach ($cmds as $c) $states[] = $cwd = tb75_cd($cwd, $c, '/home/' . $m[2]);
    return ['start' => $m[1], 'home' => '/home/' . $m[2], 'cmds' => $cmds, 'states' => $states];
}

function hn80_h_lnx_path(array $v): array
{
    $q = hn80_path_params($v);
    if ($q === null || $q['cmds'] === []) return [];
    return [
        'Projdi příkazy jeden po druhém a po každém si zapiš, ve které složce jsi.',
        '„..“ = o úroveň výš, „.“ = zůstaň, „~“ = domovská složka ' . $q['home'] . ', cesta začínající „/“ začíná od kořene. Relativní cesta se připojí k aktuální složce.',
        'Vyřešený krok: po prvním příkazu „cd ' . $q['cmds'][0] . '“ jsi ve složce ' . $q['states'][0] . '.',
    ];
}

function hn80_d_lnx_path(array $v, string $fid, string $g): string
{
    $q = hn80_path_params($v);
    if ($q === null) return '';
    $g = strlen($g) > 1 ? rtrim(trim($g), '/') : trim($g);
    if (!str_starts_with($g, '/')) return 'pwd vypisuje absolutní cestu – začíná „/“.';
    if ($g === $q['start']) return 'To je výchozí složka – příkazy cd ji ale změnily.';
    $n = count($q['states']);
    foreach (array_slice($q['states'], 0, $n - 1) as $i => $s) if ($s === $g) return 'Tady jsi po ' . ($i + 1) . '. příkazu – pokračuj dalšími.';
    return 'Zkontroluj kroky s „..“ (každé ubere jednu složku) a „~“ (skok domů).';
}

// ---------------------------------------------------------------------------------------------- lnx_chmod

function hn80_sym_to_oct(string $sym): ?int
{
    if (preg_match('/^[r-][w-][x-][r-][w-][x-][r-][w-][x-]$/', $sym) !== 1) return null;
    $n = 0;
    foreach (str_split($sym) as $i => $ch) if ($ch !== '-') $n |= 1 << (8 - $i);
    return $n;
}

/** @return array{a:string,b:string,base:string,op:string}|null */
function hn80_chmod_params(array $v): ?array
{
    $a = preg_match('~pro ([rwx-]{9})~', (string)hn80_field($v, 'oct')['label'], $m1) === 1 ? $m1[1] : null;
    $b = preg_match('~pro (\d{3})~', (string)hn80_field($v, 'sym')['label'], $m2) === 1 ? $m2[1] : null;
    $ok = preg_match('~má ([rwx-]{9}), pak chmod ([ugo][+-][rwx])~', (string)hn80_field($v, 'after')['label'], $m3) === 1;
    return $a !== null && $b !== null && $ok ? ['a' => $a, 'b' => $b, 'base' => $m3[1], 'op' => $m3[2]] : null;
}

/** chmod u|g|o ± r|w|x nad 9 bity. */
function hn80_chmod_apply(int $mode, string $who, string $sign, string $perm): int
{
    $bit = ['r' => 4, 'w' => 2, 'x' => 1][$perm] << ['u' => 6, 'g' => 3, 'o' => 0][$who];
    return $sign === '+' ? $mode | $bit : $mode & ~$bit & 0777;
}

function hn80_h_lnx_chmod(array $v): array
{
    $q = hn80_chmod_params($v);
    if ($q === null) return [];
    $t = str_split($q['a'], 3);
    $d = static fn(string $tr): int => ($tr[0] === 'r' ? 4 : 0) + ($tr[1] === 'w' ? 2 : 0) + ($tr[2] === 'x' ? 1 : 0);
    return [
        'Každá trojice znaků (vlastník, skupina, ostatní) je jedna osmičková číslice: r = 4, w = 2, x = 1.',
        'Sečti hodnoty v trojici (rw- = 4 + 2 = 6). U chmod: u = vlastník, g = skupina, o = ostatní; „+“ právo přidá, „−“ ubere, ostatní trojice zůstanou.',
        'Vyřešený krok: ' . $q['a'] . ' rozděl na ' . implode(' | ', $t) . ' → první trojice ' . $t[0] . ' = ' . $d($t[0]) . '.',
    ];
}

function hn80_d_lnx_chmod(array $v, string $fid, string $g): string
{
    $q = hn80_chmod_params($v);
    if ($q === null) return '';
    $g = strtolower(trim($g));
    if ($fid === 'oct') {
        $want = sprintf('%03o', (int)hn80_sym_to_oct($q['a']));
        $g = preg_match('/^0[0-7]{3}$/', $g) === 1 ? substr($g, 1) : $g;
        if (preg_match('/^[0-7]{3}$/', $g) !== 1) return 'Osmičkový zápis má tři číslice 0–7 (vlastník, skupina, ostatní).';
        if ($g === strrev($want)) return 'Číslice jsou obráceně – pořadí je vlastník, skupina, ostatní.';
        $rev = implode('', array_map(static fn(string $tr): string => (string)(($tr[0] === 'r' ? 1 : 0) + ($tr[1] === 'w' ? 2 : 0) + ($tr[2] === 'x' ? 4 : 0)), str_split($q['a'], 3)));
        if ($g === $rev) return 'Váhy jsou r = 4, w = 2, x = 1 (ne obráceně).';
        return 'Sečti každou trojici zvlášť: r = 4, w = 2, x = 1.';
    }
    if (strlen($g) !== 9) return 'Symbolický zápis má 9 znaků (3 × rwx); chybějící právo je „-“.';
    if (preg_match('/^([rwx-]{3}){3}$/', $g) === 1 && hn80_sym_to_oct($g) === null) return 'V každé trojici je pořadí r, w, x (chybějící právo = „-“).';
    if ($fid === 'sym') return 'Rozlož každou číslici: 7 = rwx, 6 = rw-, 5 = r-x, 4 = r--, 0 = ---.';
    $base = (int)hn80_sym_to_oct($q['base']);
    [$who, $sign, $perm] = [$q['op'][0], $q['op'][1], $q['op'][2]];
    $gi = hn80_sym_to_oct($g);
    if ($gi === $base) return 'Soubor zůstal stejný – chmod ' . $q['op'] . ' mění jedno právo.';
    foreach (['u', 'g', 'o'] as $other) if ($other !== $who && $gi === hn80_chmod_apply($base, $other, $sign, $perm)) return 'Změna proběhla u jiné trojice – u = vlastník, g = skupina, o = ostatní.';
    if ($gi === hn80_chmod_apply($base, $who, $sign === '+' ? '-' : '+', $perm)) return '„+“ právo přidává, „−“ ho ubírá.';
    return 'Vezmi ' . $q['base'] . ' a změň jen jedno právo podle ' . $q['op'] . '.';
}

// ---------------------------------------------------------------------------------------------- lnx_grep

/** @return array{word:string,svc:string,lines:list<string>} */
function hn80_grep_params(array $v): array
{
    $w = preg_match('~-c "([^"]+)"~', (string)hn80_field($v, 'g0')['label'], $m1) === 1 ? $m1[1] : '';
    $s = preg_match('~-v "([^"]+)"~', (string)hn80_field($v, 'g2')['label'], $m2) === 1 ? $m2[1] : '';
    return ['word' => $w, 'svc' => $s, 'lines' => array_values(array_map('strval', (array)$v['data']))];
}

function hn80_h_lnx_grep(array $v): array
{
    $q = hn80_grep_params($v);
    $hits = array_values(array_filter($q['lines'], static fn(string $l): bool => str_contains($l, $q['word'])));
    return [
        'Každý řádek se započítá nejvýš jednou. Dávej pozor na velká a malá písmena.',
        'grep -c počítá řádky se shodou (rozlišuje velikost písmen), -i velikost písmen ignoruje, -v vybere řádky BEZ shody; wc -l spočítá řádky, které do něj přijdou.',
        'Vyřešený krok: přesně „' . $q['word'] . '“ obsahují řádky ' . ($hits === [] ? '(žádné)' : implode(', ', array_map(static fn(string $l): string => substr($l, 0, 5), $hits))) . ' → ' . count($hits) . '.',
    ];
}

function hn80_d_lnx_grep(array $v, string $fid, string $g): string
{
    $q = hn80_grep_params($v);
    $n = hn80_float($g);
    if ($n === null) return 'Odpověď je počet řádků (celé číslo).';
    $n = (int)$n;
    $cs = tb75_grep_count($q['lines'], $q['word'], false, false);
    $ci = tb75_grep_count($q['lines'], $q['word'], true, false);
    if ($fid === 'g0' && $n === $ci && $ci !== $cs) return 'grep -c bez -i rozlišuje velikost písmen – řádky s malými písmeny se nepočítají.';
    if ($fid === 'g1' && $n === $cs && $ci !== $cs) return '-i ignoruje velikost písmen – započítej i řádky s malými písmeny.';
    if ($fid === 'g2' && $n === tb75_grep_count($q['lines'], $q['svc'], false, false)) return '-v vybírá řádky, které slovo NEOBSAHUJÍ.';
    if ($fid === 'g2' && $n === count($q['lines'])) return 'Některé řádky slovo „' . $q['svc'] . '“ obsahují – ty -v vynechá.';
    return 'Spočítej řádky ukázky znovu jeden po druhém; každý řádek nejvýš jednou.';
}
