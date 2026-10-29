<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · banka úloh s variantami pro grafiku a web (1.A, 2.A) – GENERÁTORY (čisté výpočty, nic nespouští ani nestahuje).
 *
 *   gfx_color – barvy: HEX ↔ RGB a doplňková (inverzní) barva,
 *   web_units – jednotky CSS: em (z písma rodiče), rem (kořen 16 px), % (ze šířky kontejneru) → px,
 *   css_spec  – specificita selektorů (a, b, c) a který ze tří selektorů vyhraje,
 *   img_dpi   – rozlišení a tisk: šířka výtisku v cm při daném DPI, potřebné pixely pro tisk a megapixely.
 * Tvar varianty je stejný jako v taskbank_v75_gen.php (prompt, data, fields s klíčem, explain); varianta je deterministická
 * ze semínka (student_id | úloha | kolo). Texty zadání jsou výukový obsah (česky). Normalizace odpovědí: tb76_norm().
 */

const TB76_TASKS = [
    'gfx_color' => ['title' => 'Barvy: HEX a RGB', 'area' => 'grafika', 'minutes' => 4],
    'web_units' => ['title' => 'Jednotky CSS: px, em, rem a %', 'area' => 'web', 'minutes' => 4],
    'css_spec' => ['title' => 'Selektory a specificita', 'area' => 'web', 'minutes' => 5],
    'img_dpi' => ['title' => 'Rozlišení obrázku a DPI', 'area' => 'grafika', 'minutes' => 5],
];
const TB76_ROOT_PX = 16;
const TB76_CM_PER_INCH = 2.54;

/** Varianta grafické úlohy (volá tb75_variant pro úlohy, které nezná). */
function tb76_generate(string $task, Closure $r): array
{
    return match ($task) {
        'gfx_color' => tb76_gen_color($r),
        'web_units' => tb76_gen_units($r),
        'css_spec' => tb76_gen_spec($r),
        'img_dpi' => tb76_gen_dpi($r),
        default => throw new InvalidArgumentException('Neznámá úloha.'),
    };
}

function tb76_hex(int $r, int $g, int $b): string
{
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function tb76_gen_color(Closure $r): array
{
    [$r1, $g1, $b1] = [$r(0, 255), $r(0, 255), $r(0, 255)];
    [$r2, $g2, $b2] = [$r(0, 255), $r(0, 255), $r(0, 255)];
    [$r3, $g3, $b3] = [$r(0, 255), $r(0, 255), $r(0, 255)];
    $hex2 = tb76_hex($r2, $g2, $b2);
    $hex3 = tb76_hex($r3, $g3, $b3);
    return ['prompt' => 'Převeď barvy mezi zápisem HEX a RGB. HEX zapisuj se šestnáctkovými číslicemi (např. #1a2b3c), RGB jako tři čísla 0–255 oddělená čárkou.',
        'fields' => [
            ['id' => 'hex', 'label' => 'HEX pro rgb(' . $r1 . ', ' . $g1 . ', ' . $b1 . ')', 'kind' => 'text', 'answer' => tb76_hex($r1, $g1, $b1), 'hint' => 'např. #1a2b3c'],
            ['id' => 'rgb', 'label' => 'RGB pro ' . $hex2, 'kind' => 'text', 'answer' => $r2 . ',' . $g2 . ',' . $b2, 'hint' => 'např. 26, 43, 60'],
            ['id' => 'inv', 'label' => 'Doplňková (inverzní) barva k ' . $hex3 . ' v HEX', 'kind' => 'text', 'answer' => tb76_hex(255 - $r3, 255 - $g3, 255 - $b3), 'hint' => 'každá složka = 255 − původní'],
        ],
        'explain' => 'Každý pár šestnáctkových číslic je jedna složka 0–255 (FF = 255). rgb(' . $r1 . ', ' . $g1 . ', ' . $b1 . ') = ' . tb76_hex($r1, $g1, $b1) . '; ' . $hex2 . ' = '
            . $r2 . ', ' . $g2 . ', ' . $b2 . '; inverze ' . $hex3 . ' = ' . tb76_hex(255 - $r3, 255 - $g3, 255 - $b3) . ' (255 − každá složka).'];
}

function tb76_num(float $n): string
{
    $s = number_format($n, 3, '.', '');
    return rtrim(rtrim($s, '0'), '.');
}

function tb76_gen_units(Closure $r): array
{
    $parent = [12, 14, 15, 18, 20, 24][$r(0, 5)];
    $em = [0.75, 1.25, 1.5, 2.0][$r(0, 3)];
    $rem = [0.5, 0.875, 1.25, 1.5, 2.5][$r(0, 4)];
    $width = 40 * $r(8, 30);
    $pct = [25, 30, 40, 50, 60, 75][$r(0, 5)];
    $emPx = $parent * $em;
    $remPx = TB76_ROOT_PX * $rem;
    $pctPx = $width * $pct / 100;
    return ['prompt' => 'Kořenové písmo stránky (html) má výchozích ' . TB76_ROOT_PX . ' px. Rodič prvku má font-size: ' . $parent . 'px a kontejner je široký ' . $width . ' px. Kolik pixelů vyjde?',
        'fields' => [
            ['id' => 'em', 'label' => 'padding: ' . tb76_num($em) . 'em (v px)', 'kind' => 'number', 'answer' => tb76_num($emPx), 'hint' => 'em = násobek písma rodiče'],
            ['id' => 'rem', 'label' => 'font-size: ' . tb76_num($rem) . 'rem (v px)', 'kind' => 'number', 'answer' => tb76_num($remPx), 'hint' => 'rem = násobek písma kořene'],
            ['id' => 'pct', 'label' => 'width: ' . $pct . '% (v px)', 'kind' => 'number', 'answer' => tb76_num($pctPx), 'hint' => 'procento šířky kontejneru'],
        ],
        'explain' => tb76_num($em) . 'em × ' . $parent . ' px = ' . tb76_num($emPx) . ' px; ' . tb76_num($rem) . 'rem × ' . TB76_ROOT_PX . ' px = ' . tb76_num($remPx) . ' px; '
            . $pct . ' % z ' . $width . ' px = ' . tb76_num($pctPx) . ' px. (U em platí písmo rodiče, u rem písmo kořene html.)'];
}

/** Selektor se zadaným počtem id (a), tříd/atributů/pseudotříd (b) a typů (c). */
function tb76_selector(Closure $r, int $a, int $b, int $c): string
{
    $ids = ['#menu', '#hlavni', '#kosik', '#paticka'];
    $bs = ['.karta', '.aktivni', '.tlacitko', '.nav', '[href]', '[type="email"]', ':hover', ':focus', ':first-child'];
    $types = ['nav', 'ul', 'li', 'a', 'button', 'p', 'main', 'section'];
    $parts = [];
    if ($a > 0) $parts[] = $ids[$r(0, count($ids) - 1)];
    for ($i = 0; $i < $c; $i++) $parts[] = $types[$r(0, count($types) - 1)];
    $tail = '';
    $used = [];
    for ($i = 0; $i < $b; $i++) {
        do { $tok = $bs[$r(0, count($bs) - 1)]; } while (isset($used[$tok]));
        $used[$tok] = true;
        $tail .= $tok;
    }
    $classes = array_filter(array_keys($used), static fn(string $t): bool => $t[0] !== ':');
    $pseudo = array_filter(array_keys($used), static fn(string $t): bool => $t[0] === ':');
    $parts[count($parts) - 1] .= implode('', $classes) . implode('', $pseudo);   // pseudotřídy až na konec složeného selektoru
    return implode(' ', $parts);
}

function tb76_gen_spec(Closure $r): array
{
    $specs = [];
    $tries = 0;
    do {
        $specs = [];
        for ($i = 0; $i < 3; $i++) $specs[] = [$r(0, 3) === 0 ? 1 : 0, $r(0, 3), $r(1, 3)];
        $keys = array_map(static fn(array $s): int => $s[0] * 100 + $s[1] * 10 + $s[2], $specs);
        $max = max($keys);
        $tries++;
    } while ((count(array_keys($keys, $max, true)) !== 1 || count(array_unique($keys)) !== 3) && $tries < 30);
    $sels = array_map(static fn(array $s): string => tb76_selector($r, $s[0], $s[1], $s[2]), $specs);
    $win = (int)array_search($max, $keys, true);
    $first = $specs[0];
    return ['prompt' => 'Tři pravidla nastavují stejnému odkazu jinou barvu. Spočítej specificitu prvního selektoru (id, třídy+atributy+pseudotřídy, typy) a urči, které pravidlo vyhraje.',
        'data' => array_map(static fn(string $s, int $i): string => ($i + 1) . ') ' . $s . ' { color: … }', $sels, array_keys($sels)),
        'fields' => [
            ['id' => 'spec', 'label' => 'Specificita selektoru 1 (a,b,c)', 'kind' => 'text', 'answer' => implode(',', $first), 'hint' => 'např. 0,2,1'],
            ['id' => 'win', 'label' => 'Které pravidlo vyhraje?', 'kind' => 'select', 'options' => ['1', '2', '3'], 'answer' => (string)($win + 1), 'hint' => 'číslo pravidla'],
        ],
        'explain' => 'Specificita: ' . implode('; ', array_map(static fn(string $s, array $p): string => $s . ' = (' . implode(',', $p) . ')', $sels, $specs))
            . '. Porovnává se zleva: víc id vyhrává vždy, při shodě rozhodují třídy, pak typy. Vyhrává pravidlo ' . ($win + 1) . '.'];
}

function tb76_gen_dpi(Closure $r): array
{
    $w = [1200, 1800, 2400, 3000, 3600, 4200][$r(0, 5)];
    $h = intdiv($w * 2, 3);
    $dpi = [150, 300][$r(0, 1)];
    $cm = $w / $dpi * TB76_CM_PER_INCH;
    $target = [10, 13, 15, 18, 21, 30][$r(0, 5)];
    $px = (int)round($target / TB76_CM_PER_INCH * 300);
    $mp = $w * $h / 1000000;
    return ['prompt' => 'Fotografie má rozměr ' . $w . ' × ' . $h . ' px. 1 palec = 2,54 cm.',
        'fields' => [
            ['id' => 'cm', 'label' => 'Šířka výtisku při ' . $dpi . ' DPI v cm (na 1 desetinné místo)', 'kind' => 'number', 'answer' => number_format($cm, 1, '.', ''), 'hint' => 'px ÷ DPI × 2,54'],
            ['id' => 'px', 'label' => 'Kolik pixelů na šířku potřebuješ pro tisk ' . $target . ' cm při 300 DPI (celé číslo)', 'kind' => 'number', 'answer' => (string)$px, 'hint' => 'cm ÷ 2,54 × 300'],
            ['id' => 'mp', 'label' => 'Kolik megapixelů má fotografie (na 1 desetinné místo)', 'kind' => 'number', 'answer' => number_format($mp, 1, '.', ''), 'hint' => 'šířka × výška ÷ 1 000 000'],
        ],
        'explain' => $w . ' px ÷ ' . $dpi . ' DPI = ' . tb76_num($w / $dpi) . ' palce × 2,54 = ' . number_format($cm, 1, ',', '') . ' cm; ' . $target . ' cm ÷ 2,54 × 300 ≈ ' . $px
            . ' px; ' . $w . ' × ' . $h . ' = ' . number_format($w * $h, 0, ',', ' ') . ' px ≈ ' . number_format($mp, 1, ',', '') . ' Mpx. Pro tisk se počítá s 300 DPI, pro web rozhodují jen pixely.'];
}

/** Normalizace odpovědi grafických úloh (null = úloha není grafická – rozhodne tb75_norm). */
function tb76_norm(string $task, string $fieldId, string $value): ?string
{
    if (!isset(TB76_TASKS[$task])) return null;
    $v = strtolower(trim((string)preg_replace('/\s+/u', '', $value)));
    if ($task === 'gfx_color' && in_array($fieldId, ['hex', 'inv'], true)) {
        $v = ltrim($v, '#');
        if (preg_match('/^[0-9a-f]{3}$/', $v) === 1) $v = $v[0] . $v[0] . $v[1] . $v[1] . $v[2] . $v[2];
        return '#' . $v;
    }
    if ($task === 'gfx_color' && $fieldId === 'rgb') return (string)preg_replace(['/^rgb\(/', '/\)$/', '/[;\/]/'], ['', '', ','], $v);
    if ($task === 'css_spec' && $fieldId === 'spec') return (string)preg_replace(['/^\(|\)$/', '/[;\-\/]/'], ['', ','], $v);
    $v = str_replace(',', '.', (string)preg_replace('/(px|cm|mpx|mp)$/', '', $v));
    if (is_numeric($v)) {
        if ($task === 'img_dpi' && in_array($fieldId, ['cm', 'mp'], true)) return number_format(round((float)$v, 1), 1, '.', '');
        if ($task === 'img_dpi' && $fieldId === 'px') return (string)(int)round((float)$v);
        return tb76_num((float)$v);
    }
    return $v;
}
