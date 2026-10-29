<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v80 · nápovědy a vysvětlení chyb pro grafické úlohy v76 (gfx_color, web_units, css_spec, img_dpi) – čisté výpočty.
 * Rozhraní stejné jako v hints_v80.php: hn80_h_<úloha>($variant) → 3 nápovědy, hn80_d_<úloha>($variant, $pole, $odpověď) → vysvětlení.
 * Specificitu selektoru počítá hn80_spec() (id; třídy + atributy + pseudotřídy; typy) – stejná pravidla jako generátor tb76_selector().
 */

/** [r,g,b] z „#rrggbb“ nebo z „rgb(r, g, b)“ v textu; null = nenalezeno. */
function hn80_rgb_from(string $text): ?array
{
    if (preg_match('~#([0-9a-f]{6})\b~i', $text, $m) === 1) return array_map('hexdec', str_split($m[1], 2));
    if (preg_match('~rgb\((\d+),\s*(\d+),\s*(\d+)\)~', $text, $m) === 1) return [(int)$m[1], (int)$m[2], (int)$m[3]];
    return null;
}

/** Odpověď žáka jako [r,g,b] (HEX i tři čísla), jinak null. */
function hn80_rgb_given(string $g): ?array
{
    $g = strtolower(trim($g));
    if (preg_match('/^#?([0-9a-f]{6})$/', $g, $m) === 1) return array_map('hexdec', str_split($m[1], 2));
    if (preg_match('/^#?([0-9a-f]{3})$/', $g, $m) === 1) return array_map(static fn(string $c): int => (int)hexdec($c . $c), str_split($m[1]));
    if (preg_match('/^(?:rgb\()?\s*(\d{1,3})\s*[,;\/ ]\s*(\d{1,3})\s*[,;\/ ]\s*(\d{1,3})\s*\)?$/', $g, $m) === 1) return [(int)$m[1], (int)$m[2], (int)$m[3]];
    return null;
}

const HN80_RGB_NAMES = ['červená (R)', 'zelená (G)', 'modrá (B)'];

function hn80_h_gfx_color(array $v): array
{
    $rgb = hn80_rgb_from((string)hn80_field($v, 'hex')['label']) ?? [0, 0, 0];
    $r = $rgb[0];
    return [
        'HEX má tři dvojice šestnáctkových číslic: #RRGGBB. Každá dvojice je jedna složka 0–255.',
        'Dvojice = 16 × první číslice + druhá (a = 10, b = 11 … f = 15). Opačně: číslo vyděl 16 – podíl je první číslice, zbytek druhá. Inverze = 255 − každá složka.',
        'Vyřešený krok: červená ' . $r . ' ÷ 16 = ' . intdiv($r, 16) . ' zbytek ' . ($r % 16) . ' → „' . sprintf('%02x', $r) . '“.',
    ];
}

function hn80_d_gfx_color(array $v, string $fid, string $g): string
{
    $label = (string)hn80_field($v, $fid)['label'];
    $src = hn80_rgb_from($label);
    $given = hn80_rgb_given($g);
    if ($src === null) return '';
    if ($given === null) return $fid === 'rgb' ? 'RGB zapiš jako tři čísla 0–255 oddělená čárkou.' : 'HEX zapiš jako # a šest šestnáctkových číslic (0–9, a–f).';
    $want = $fid === 'inv' ? array_map(static fn(int $c): int => 255 - $c, $src) : $src;
    if ($fid === 'inv' && $given === $src) return 'To je původní barva – každou složku odečti od 255.';
    if ($fid === 'inv' && $given === array_map(static fn(int $c): int => (256 - $c) % 256, $src)) return 'Inverze je 255 − složka, ne 256 − složka.';
    sort($given);
    $sorted = $want;
    sort($sorted);
    $raw = hn80_rgb_given($g) ?? [];
    if ($given === $sorted && $raw !== $want) return 'Čísla sedí, ale jsou v jiném pořadí – pořadí je R, G, B.';
    if ($fid === 'rgb' && preg_match('~#([0-9a-f]{6})~i', $label, $m) === 1) {
        $dec = array_map('intval', str_split($m[1], 2));
        if ($raw === $dec) return 'Dvojice jsou šestnáctkově: „' . substr($m[1], 0, 2) . '“ = ' . hexdec(substr($m[1], 0, 1)) . ' × 16 + ' . hexdec(substr($m[1], 1, 1)) . ' = ' . $want[0] . ', ne ' . $dec[0] . '.';
    }
    foreach ($want as $i => $c) if (($raw[$i] ?? -1) !== $c) return 'Složka ' . HN80_RGB_NAMES[$i] . ' nesedí: ' . $c . ' = ' . intdiv($c, 16) . ' × 16 + ' . ($c % 16) . ' (' . sprintf('%02x', $c) . ').';
    return '';
}

/** @return array{root:int,parent:int,width:int,em:float,rem:float,pct:float}|null */
function hn80_units_params(array $v): ?array
{
    $p = (string)$v['prompt'];
    if (preg_match('~font-size: (\d+)px~', $p, $a) !== 1 || preg_match('~široký (\d+) px~u', $p, $b) !== 1) return null;
    $em = preg_match('~padding: ([\d.]+)em~', (string)hn80_field($v, 'em')['label'], $c) === 1 ? (float)$c[1] : 0.0;
    $rem = preg_match('~font-size: ([\d.]+)rem~', (string)hn80_field($v, 'rem')['label'], $d) === 1 ? (float)$d[1] : 0.0;
    $pct = preg_match('~width: (\d+)%~', (string)hn80_field($v, 'pct')['label'], $e) === 1 ? (float)$e[1] : 0.0;
    return ['root' => TB76_ROOT_PX, 'parent' => (int)$a[1], 'width' => (int)$b[1], 'em' => $em, 'rem' => $rem, 'pct' => $pct];
}

function hn80_h_web_units(array $v): array
{
    $q = hn80_units_params($v);
    if ($q === null) return [];
    return [
        'em se počítá z písma rodiče, rem z písma kořene (html), procenta ze šířky kontejneru.',
        'px = em × písmo rodiče; px = rem × ' . $q['root'] . '; px = procento ÷ 100 × šířka kontejneru.',
        'Vyřešený krok: ' . hn80_num($q['em']) . ' em × ' . $q['parent'] . ' px (písmo rodiče) = ' . hn80_num($q['em'] * $q['parent']) . ' px.',
    ];
}

function hn80_d_web_units(array $v, string $fid, string $g): string
{
    $q = hn80_units_params($v);
    $n = hn80_float($g);
    if ($q === null) return '';
    if ($n === null) return 'Odpověď je číslo v pixelech (desetinná čárka i tečka platí).';
    $eq = static fn(float $a, float $b): bool => abs($a - $b) < 0.01;
    if ($fid === 'em' && $eq($n, $q['em'] * $q['root'])) return 'Tohle je výpočet z kořene (jako rem). em bere písmo rodiče: ' . $q['parent'] . ' px.';
    if ($fid === 'rem' && $eq($n, $q['rem'] * $q['parent'])) return 'rem bere písmo kořene html (' . $q['root'] . ' px), ne rodiče.';
    if ($fid === 'pct' && $eq($n, $q['pct'])) return 'Procenta je potřeba přepočítat na px ze šířky kontejneru (' . $q['width'] . ' px).';
    if ($fid === 'pct' && $eq($n, $q['pct'] * $q['width'])) return 'Nezapomeň dělit stem: ' . hn80_num($q['pct']) . ' % = ' . hn80_num($q['pct'] / 100) . ' × šířka.';
    return ['em' => 'px = em × písmo rodiče (' . $q['parent'] . ' px).', 'rem' => 'px = rem × ' . $q['root'] . ' px.', 'pct' => 'px = procento ÷ 100 × ' . $q['width'] . ' px.'][$fid] ?? '';
}

/** Specificita selektoru jako [id, třídy+atributy+pseudotřídy, typy]. */
function hn80_spec(string $sel): array
{
    $a = preg_match_all('/#[\w-]+/u', $sel);
    $attr = preg_match_all('/\[[^\]]*\]/', $sel);
    $plain = (string)preg_replace('/\[[^\]]*\]/', '', $sel);
    $b = preg_match_all('/\.[\w-]+/u', $plain) + $attr + preg_match_all('/(?<!:):[\w-]+/', $plain);
    $c = 0;
    foreach (preg_split('/\s+/', trim($plain)) ?: [] as $part) if (preg_match('/^[a-z][a-z0-9]*/', $part) === 1) $c++;
    return [$a, $b, $c];
}

/** @return list<string> selektory z ukázky (1), 2), 3)) */
function hn80_selectors(array $v): array
{
    $out = [];
    foreach ((array)$v['data'] as $row) if (preg_match('~^\d\) (.+?) \{~u', (string)$row, $m) === 1) $out[] = $m[1];
    return $out;
}

function hn80_h_css_spec(array $v): array
{
    $sels = hn80_selectors($v);
    $ex = $sels[1] ?? ($sels[0] ?? '');
    $s = hn80_spec($ex);
    return [
        'Specificita se zapisuje (a, b, c): a = id (#), b = třídy, atributy a pseudotřídy (. [ ] :), c = názvy prvků.',
        'Porovnává se zleva: víc id vyhrává vždy; při shodě rozhoduje b, teprve potom c. Délka ani pořadí selektoru nerozhodují.',
        'Vyřešený krok (na selektoru 2): „' . $ex . '“ má ' . $s[0] . ' id, ' . $s[1] . ' tříd/atributů/pseudotříd a ' . $s[2] . ' typů → (' . implode(',', $s) . ').',
    ];
}

function hn80_d_css_spec(array $v, string $fid, string $g): string
{
    $sels = hn80_selectors($v);
    if ($sels === []) return '';
    if ($fid === 'spec') {
        $want = hn80_spec($sels[0]);
        $parts = array_map('intval', preg_split('/[^0-9]+/', trim($g, " ()")) ?: []);
        if (count($parts) !== 3) return 'Zapiš tři čísla oddělená čárkou: id, třídy+atributy+pseudotřídy, typy (např. 0,2,1).';
        $pseudo = preg_match_all('/(?<!:):[\w-]+/', (string)preg_replace('/\[[^\]]*\]/', '', $sels[0]));
        if ($pseudo > 0 && $parts === [$want[0], $want[1] - $pseudo, $want[2] + $pseudo]) return 'Pseudotřídy (:hover, :focus …) patří do b k třídám, ne k typům.';
        if ($parts[0] + $parts[1] + $parts[2] !== $want[0] + $want[1] + $want[2]) return 'Některá část selektoru chybí nebo je navíc – projdi ho znak po znaku: #, . [ ] :, názvy prvků.';
        return 'Rozřaď části selektoru do správných skupin: # → a; . [ ] : → b; názvy prvků → c.';
    }
    $specs = array_map('hn80_spec', $sels);
    $pick = (int)$g - 1;
    if (!isset($specs[$pick])) return 'Vyber číslo pravidla 1–3.';
    $lens = array_map(static fn(array $s): int => array_sum($s), $specs);
    $winner = 0;
    foreach ($specs as $i => $s) if ($s > $specs[$winner]) $winner = $i;
    if ($lens[$pick] === max($lens) && $pick !== $winner) return 'Nerozhoduje délka selektoru, ale (a, b, c) zleva – pravidlo ' . ($winner + 1) . ' má (' . implode(',', $specs[$winner]) . ').';
    if ($specs[$pick][2] > $specs[$winner][2] && $specs[$pick][0] === $specs[$winner][0]) return 'Třída (b) má přednost před libovolným počtem typů (c).';
    return 'Porovnej (a, b, c) všech tří pravidel zleva: pravidlo ' . ($pick + 1) . ' má (' . implode(',', $specs[$pick]) . ').';
}

/** @return array{w:int,h:int,dpi:int,cm:int}|null */
function hn80_dpi_params(array $v): ?array
{
    if (preg_match('~rozměr (\d+) × (\d+) px~u', (string)$v['prompt'], $m) !== 1) return null;
    $dpi = preg_match('~při (\d+) DPI~', (string)hn80_field($v, 'cm')['label'], $d) === 1 ? (int)$d[1] : 300;
    $cm = preg_match('~tisk (\d+) cm~u', (string)hn80_field($v, 'px')['label'], $c) === 1 ? (int)$c[1] : 0;
    return ['w' => (int)$m[1], 'h' => (int)$m[2], 'dpi' => $dpi, 'cm' => $cm];
}

function hn80_h_img_dpi(array $v): array
{
    $q = hn80_dpi_params($v);
    if ($q === null) return [];
    return [
        'DPI = kolik pixelů připadá na 1 palec výtisku; 1 palec = 2,54 cm.',
        'Šířka v cm = px ÷ DPI × 2,54; potřebné px = cm ÷ 2,54 × DPI; megapixely = šířka × výška ÷ 1 000 000.',
        'Vyřešený krok: ' . $q['w'] . ' px ÷ ' . $q['dpi'] . ' DPI = ' . hn80_num($q['w'] / $q['dpi']) . ' palce.',
    ];
}

function hn80_d_img_dpi(array $v, string $fid, string $g): string
{
    $q = hn80_dpi_params($v);
    $n = hn80_float($g);
    if ($q === null) return '';
    if ($n === null) return 'Odpověď je číslo (desetinná čárka i tečka platí).';
    $near = static fn(float $a, float $b): bool => abs($a - $b) <= max(0.06, abs($b) * 0.01);
    if ($fid === 'cm') {
        if ($near($n, $q['w'] / $q['dpi'])) return 'Tohle je šířka v palcích – vynásob ji 2,54.';
        if ($near($n, $q['w'] / $q['dpi'] / TB76_CM_PER_INCH)) return 'Palce na centimetry se násobí 2,54, ne dělí.';
        if ($near($n, $q['w'] / ($q['dpi'] === 300 ? 150 : 300) * TB76_CM_PER_INCH)) return 'Použij DPI ze zadání (' . $q['dpi'] . ').';
        return 'cm = ' . $q['w'] . ' ÷ ' . $q['dpi'] . ' × 2,54 (zaokrouhli na 1 desetinné místo).';
    }
    if ($fid === 'px') {
        if ($near($n, $q['cm'] * 300.0)) return 'Centimetry je potřeba nejdřív převést na palce (÷ 2,54).';
        if ($near($n, $q['cm'] * TB76_CM_PER_INCH * 300)) return 'Na palce se dělí 2,54, ne násobí.';
        return 'px = ' . $q['cm'] . ' ÷ 2,54 × 300 (celé číslo).';
    }
    if ($near($n, ($q['w'] + $q['h']) / 1000000) || $near($n, (float)($q['w'] + $q['h']))) return 'Pixely obrázku se násobí (šířka × výška), nesčítají.';
    if ($near($n, $q['w'] * $q['h'] / 1000) || $near($n, (float)($q['w'] * $q['h']))) return 'Megapixel = 1 000 000 pixelů – vyděl milionem.';
    return 'Mpx = ' . $q['w'] . ' × ' . $q['h'] . ' ÷ 1 000 000 (na 1 desetinné místo).';
}
