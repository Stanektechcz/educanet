<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/learning_v80.php';

/**
 * EDUCANET v80 · interaktivní cvičení v prohlížeči (?view=cviceni): kalkulačka podsítí, stavebnice chmod, míchání barev, souboj specificity.
 *
 * Progresivní vylepšení: formulář funguje bez JS (pole, zaškrtávátka, výběr) a odpověď vždy kontroluje SERVER (lr80_widget_check).
 * JS (assets/practice-v80.js) jen živě přepočítává pomůcky (maska a velikost bloku, rwx ↔ osmičkově, náhled barvy a HEX, porovnání trojic)
 * – žádná síť, žádné spouštění, nic se neodesílá samo. Linux Lab se nepoužívá (invariant simulátoru: čisté výpočty, adresy RFC 1918/5737).
 * Varianta = deterministická funkce (cvičení | ref žáka | kolo). Stav: learning_v80 w[cvičení] = {r, t, ok}; časy vyřešení log.w_ok_at.
 * Chyba ve cvičení zařadí související úlohu banky do opakování (jen když ji třída má). Texty zadání jsou výukový obsah (česky).
 */

const WG80_WIDGETS = [
    'subnet' => ['title' => 'Kalkulačka podsítí', 'task' => 'net_subnet'],
    'chmod' => ['title' => 'Stavebnice oprávnění chmod', 'task' => 'lnx_chmod'],
    'color' => ['title' => 'Míchání barev HEX a RGB', 'task' => 'gfx_color'],
    'spec' => ['title' => 'Souboj specificity CSS', 'task' => 'css_spec'],
];
const WG80_TRIES = 3;
const WG80_PERMS = ['ur', 'uw', 'ux', 'gr', 'gw', 'gx', 'or', 'ow', 'ox'];

/** Pořadí cvičení pro třídu: nejdřív ta k předmětu třídy (síť a Linux / grafika a web). @return list<string> */
function wg80_for_class(string $classId): array
{
    $gfx = in_array($classId, ['class_1a', 'class_2a'], true);
    return $gfx ? ['color', 'spec', 'subnet', 'chmod'] : ['subnet', 'chmod', 'color', 'spec'];
}

/** Varianta cvičení: ['widget','round','prompt','fields'=>[id=>správná hodnota], 'data'=>…, 'explain']. */
function wg80_variant(string $widget, string $ref, int $round): array
{
    $r = tb75_rng('w80|' . $widget . '|' . $ref . '|' . $round);
    $v = match ($widget) {
        'subnet' => wg80_gen_subnet($r),
        'chmod' => wg80_gen_chmod($r),
        'color' => wg80_gen_color($r),
        'spec' => wg80_gen_spec($r),
        default => throw new InvalidArgumentException('Neznámé cvičení.'),
    };
    return ['widget' => $widget, 'round' => $round] + $v;
}

function wg80_gen_subnet(Closure $r): array
{
    $prefix = $r(20, 30);
    $ip = tb75_private_ip($r);
    $s = tb75_subnet($ip, $prefix);
    return ['prompt' => 'Adresa ' . $ip . '/' . $prefix . ': urči síťovou adresu, broadcast a počet adres pro zařízení. Kalkulačka vedle ukáže masku a velikost bloku pro libovolný prefix.',
        'data' => ['ip' => $ip, 'prefix' => $prefix], 'fields' => ['network' => $s['network'], 'broadcast' => $s['broadcast'], 'hosts' => (string)$s['hosts']],
        'explain' => '/' . $prefix . ' = ' . $s['mask'] . ', blok ' . (2 ** (32 - $prefix)) . ' adres: síť ' . $s['network'] . ', broadcast ' . $s['broadcast'] . ', pro zařízení ' . $s['hosts'] . '.'];
}

/** Slovní popis oprávnění (vlastník / skupina / ostatní). */
function wg80_mode_words(int $mode): string
{
    $out = [];
    foreach (['vlastník' => 6, 'skupina' => 3, 'ostatní' => 0] as $who => $shift) {
        $d = ($mode >> $shift) & 7;
        $p = array_filter([$d & 4 ? 'čtení' : '', $d & 2 ? 'zápis' : '', $d & 1 ? 'spouštění' : '']);
        $out[] = $who . ': ' . ($p === [] ? 'nic' : implode(', ', $p));
    }
    return implode('; ', $out);
}

function wg80_gen_chmod(Closure $r): array
{
    $pool = [0640, 0644, 0600, 0750, 0755, 0700, 0711, 0664, 0754, 0740, 0751, 0604];
    $mode = $pool[$r(0, count($pool) - 1)];
    $words = $r(0, 1) === 1;
    return ['prompt' => $words ? 'Zaškrtni oprávnění podle popisu – ' . wg80_mode_words($mode) . '.' : 'Zaškrtni oprávnění tak, aby odpovídala zápisu chmod ' . sprintf('%03o', $mode) . '.',
        'data' => ['mode' => $mode, 'words' => $words], 'fields' => ['mode' => sprintf('%03o', $mode)],
        'explain' => sprintf('%03o', $mode) . ' = ' . tb75_symbolic($mode) . ' (' . wg80_mode_words($mode) . '); r = 4, w = 2, x = 1 v každé trojici.'];
}

function wg80_gen_color(Closure $r): array
{
    $rgb = [$r(0, 255), $r(0, 255), $r(0, 255)];
    $hex = tb76_hex(...$rgb);
    return ['prompt' => 'Namíchej barvu ' . $hex . ': nastav složky R, G a B (0–255). Náhled a HEX se mění hned, odpověď zkontroluje server.',
        'data' => ['hex' => $hex], 'fields' => ['r' => (string)$rgb[0], 'g' => (string)$rgb[1], 'b' => (string)$rgb[2]],
        'explain' => $hex . ' = rgb(' . implode(', ', $rgb) . '): každá dvojice šestnáctkových číslic je jedna složka (16 × první + druhá).'];
}

function wg80_gen_spec(Closure $r): array
{
    $tries = 0;
    do {
        $s1 = [$r(0, 4) === 0 ? 1 : 0, $r(0, 3), $r(1, 3)];
        $s2 = [$r(0, 4) === 0 ? 1 : 0, $r(0, 3), $r(1, 3)];
        $tries++;
    } while ($s1 === $s2 && $tries < 20);
    if ($s1 === $s2) $s2[1]++;
    $a = tb76_selector($r, ...$s1);
    $b = tb76_selector($r, ...$s2);
    $sa = hn80_spec($a);
    $sb = hn80_spec($b);
    return ['prompt' => 'Dvě pravidla nastavují stejnému prvku jinou barvu. Spočítej specificitu obou selektorů (a,b,c) a urči, které vyhraje.',
        'data' => ['a' => $a, 'b' => $b], 'fields' => ['sa' => implode(',', $sa), 'sb' => implode(',', $sb), 'win' => $sa > $sb ? 'a' : 'b'],
        'explain' => 'A „' . $a . '“ = (' . implode(',', $sa) . '), B „' . $b . '“ = (' . implode(',', $sb) . '). Porovnává se zleva: id, pak třídy/atributy/pseudotřídy, pak typy. Vyhrává ' . ($sa > $sb ? 'A' : 'B') . '.'];
}

/** Odpověď z formuláře → hodnoty polí (whitelist, délka). */
function wg80_answers(string $widget, array $post): array
{
    $str = static fn(string $k): string => is_string($post[$k] ?? null) ? mb_substr(trim((string)$post[$k]), 0, 40) : '';
    if ($widget === 'chmod') {
        $set = array_values(array_intersect(WG80_PERMS, array_map('strval', array_filter((array)($post['p'] ?? []), 'is_string'))));
        $n = 0;
        foreach (WG80_PERMS as $i => $p) if (in_array($p, $set, true)) $n |= 1 << (8 - $i);
        if ($set === []) return ['mode' => preg_match('/^0?[0-7]{3}$/', $str('mode')) === 1 ? substr($str('mode'), -3) : ''];   // bez JS: osmičkové pole
        return ['mode' => sprintf('%03o', $n)];
    }
    if ($widget === 'color') return ['r' => $str('r'), 'g' => $str('g'), 'b' => $str('b')];
    if ($widget === 'spec') return ['sa' => $str('sa'), 'sb' => $str('sb'), 'win' => in_array($str('win'), ['a', 'b'], true) ? $str('win') : ''];
    return ['network' => $str('network'), 'broadcast' => $str('broadcast'), 'hosts' => $str('hosts')];
}

/** Normalizace pro porovnání (stejná pravidla jako banka úloh). */
function wg80_norm(string $widget, string $fid, string $value): string
{
    $v = strtolower(trim((string)preg_replace('/\s+/u', '', $value)));
    if ($widget === 'spec' && $fid !== 'win') return (string)preg_replace(['/^\(|\)$/', '/[;\-\/]/'], ['', ','], $v);
    if ($widget === 'color') return is_numeric($v) ? (string)(int)$v : $v;
    return $v;
}

/** @return array{ok:bool,fields:array<string,bool>,correct:int} */
function wg80_check(array $variant, array $answers): array
{
    $fields = [];
    foreach ($variant['fields'] as $id => $want) {
        $given = (string)($answers[$id] ?? '');
        $fields[$id] = $given !== '' && wg80_norm((string)$variant['widget'], (string)$id, $given) === wg80_norm((string)$variant['widget'], (string)$id, (string)$want);
    }
    $correct = count(array_filter($fields));
    return ['ok' => $correct === count($fields), 'fields' => $fields, 'correct' => $correct];
}

/** Vysvětlení chyb (id pole → text). Čistá funkce. */
function wg80_diagnose(array $v, array $answers): array
{
    $check = wg80_check($v, $answers);
    $out = [];
    foreach ($check['fields'] as $id => $ok) {
        $given = (string)($answers[$id] ?? '');
        if ($ok || $given === '') continue;
        $out[$id] = match ((string)$v['widget']) {
            'subnet' => hn80_d_net_subnet(['prompt' => $v['data']['ip'] . '/' . $v['data']['prefix'], 'fields' => []], (string)$id, $given),
            'chmod' => wg80_why_chmod((int)$v['data']['mode'], $given),
            'color' => wg80_why_color($v, (string)$id, $given, $answers),
            default => wg80_why_spec($v, (string)$id, $given),
        };
    }
    return $out;
}

function wg80_why_chmod(int $want, string $given): string
{
    if (preg_match('/^[0-7]{3}$/', $given) !== 1) return 'Zaškrtni aspoň jedno právo.';
    $g = octdec($given);
    if ($given === strrev(sprintf('%03o', $want))) return 'Trojice jsou prohozené – pořadí je vlastník, skupina, ostatní.';
    $names = ['u' => 'vlastník', 'g' => 'skupina', 'o' => 'ostatní'];
    $perm = ['r' => 'čtení (r)', 'w' => 'zápis (w)', 'x' => 'spouštění (x)'];
    $parts = [];
    foreach (WG80_PERMS as $i => $p) {
        $bit = 1 << (8 - $i);
        if (($want & $bit) === ($g & $bit)) continue;
        $parts[] = $names[$p[0]] . ' – ' . $perm[$p[1]] . (($want & $bit) ? ' chybí' : ' je navíc');
    }
    return 'Nesedí: ' . implode('; ', $parts) . '.';
}

function wg80_why_color(array $v, string $id, string $given, array $answers): string
{
    $hex = ltrim((string)$v['data']['hex'], '#');
    $i = ['r' => 0, 'g' => 1, 'b' => 2][$id];
    $pair = substr($hex, 2 * $i, 2);
    $n = is_numeric($given) ? (int)$given : -1;
    $name = HN80_RGB_NAMES[$i];
    if ($n < 0 || $n > 255) return 'Složka ' . $name . ' je číslo 0–255.';
    if ($pair === sprintf('%02d', $n) || (string)$n === ltrim($pair, '0')) return 'Složka ' . $name . ': „' . $pair . '“ je šestnáctkově – první číslice × 16 + druhá, ne desítkově.';
    $want = array_map('intval', $v['fields']);
    $got = array_map(static fn(string $k): int => is_numeric($answers[$k] ?? null) ? (int)$answers[$k] : -1, ['r', 'g', 'b']);
    $sw = $want;
    sort($sw);
    $sg = $got;
    sort($sg);
    if ($sw === $sg) return 'Čísla sedí, ale v jiném pořadí – HEX je #RRGGBB.';
    return 'Složka ' . $name . ' nesedí: přepočítej „' . $pair . '“ (první číslice × 16 + druhá; a = 10 … f = 15).';
}

function wg80_why_spec(array $v, string $id, string $given): string
{
    if ($id === 'win') {
        $sa = hn80_spec((string)$v['data']['a']);
        $sb = hn80_spec((string)$v['data']['b']);
        $longer = array_sum($sa) > array_sum($sb) ? 'a' : 'b';
        if ($given === $longer && $given !== $v['fields']['win']) return 'Nerozhoduje délka selektoru, ale (a, b, c) zleva: id > třídy > typy.';
        return 'Porovnej trojice zleva: (' . implode(',', $sa) . ') proti (' . implode(',', $sb) . ').';
    }
    $sel = (string)$v['data'][$id === 'sa' ? 'a' : 'b'];
    return hn80_d_css_spec(['data' => ['1) ' . $sel . ' { color: … }'], 'fields' => []], 'spec', $given);
}

/**
 * Odevzdání cvičení. Vrací ['ok'=>bool,'error'=>?string,'result','variant','why','shown','left'].
 * Správně = nové kolo; po WG80_TRIES chybách se ukáže řešení a začne nové kolo. Chyba = související úloha do opakování.
 */
function wg80_submit(string $classId, string $studentKey, string $widget, array $post, ?int $now = null): array
{
    if (!isset(WG80_WIDGETS[$widget])) return ['ok' => false, 'error' => 'unknown'];
    $answers = wg80_answers($widget, $post);
    if (array_filter($answers, static fn(string $a): bool => $a !== '') === []) return ['ok' => false, 'error' => 'empty'];
    $ref = lr80_ref($classId, $studentKey);
    $task = (string)WG80_WIDGETS[$widget]['task'];
    $res = ['ok' => false, 'error' => 'unknown'];
    lr80_update($classId, $ref, static function (array $s, int $now) use ($widget, $answers, $ref, $classId, $task, &$res): array {
        $w = is_array($s['w'][$widget] ?? null) ? $s['w'][$widget] : ['r' => 1, 't' => 0, 'ok' => 0];
        $round = max(1, (int)($w['r'] ?? 1));
        $variant = wg80_variant($widget, $ref, $round);
        $check = wg80_check($variant, $answers);
        $tries = (int)($w['t'] ?? 0) + 1;
        $shown = !$check['ok'] && $tries >= WG80_TRIES;
        $s['w'][$widget] = $check['ok'] || $shown ? ['r' => $round + 1, 't' => 0, 'ok' => (int)($w['ok'] ?? 0) + ($check['ok'] ? 1 : 0)] : ['r' => $round, 't' => $tries, 'ok' => (int)($w['ok'] ?? 0)];
        if ($check['ok']) $s['log']['w_ok_at'] = array_slice(array_merge($s['log']['w_ok_at'], [$now]), -LR80_LOG_KEEP);
        if (!$check['ok'] && in_array($task, tb75_tasks_for_class($classId), true)) $s = lr80_rep_enqueue($s, $task, [], $now);
        $res = ['ok' => true, 'error' => null, 'result' => $check, 'variant' => $variant, 'why' => $check['ok'] ? [] : wg80_diagnose($variant, $answers),
            'shown' => $shown, 'left' => max(0, WG80_TRIES - $tries)];
        return $s;
    }, $now);
    return $res;
}
