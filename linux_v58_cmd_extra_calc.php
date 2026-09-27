<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – kalkulačky: bc (GNU bc 1.07.1), expr (coreutils 9.1), factor.
 * Vlastní parser a vlastní aritmetika libovolné přesnosti (desítkové řetězce) – žádné eval,
 * žádné bcmath/gmp (nemusí být na serveru). Limity: nejvýš LAB58_BC_MAX_DIGITS číslic čísla,
 * LAB58_BC_MAX_LOOPS průchodů cyklem. Nic se nespouští, žádná síť.
 */

const LAB58_BC_MAX_DIGITS = 3000;
const LAB58_BC_MAX_LOOPS = 20000;

// ---------------------------------------------------------------------------
// Nezáporná celá čísla jako desítkové řetězce
// ---------------------------------------------------------------------------

function lab58_bn_trim(string $a): string
{
    $a = ltrim($a, '0');
    return $a === '' ? '0' : $a;
}

function lab58_bn_cmp(string $a, string $b): int
{
    return strlen($a) !== strlen($b) ? strlen($a) <=> strlen($b) : strcmp($a, $b) <=> 0;
}

/** @param list<int> $chunks bloky po 10^9 od nejnižšího */
function lab58_bn_join(array $chunks): string
{
    $s = '';
    for ($k = count($chunks) - 1; $k >= 0; $k--) $s .= str_pad((string)$chunks[$k], 9, '0', STR_PAD_LEFT);
    return lab58_bn_trim($s);
}

function lab58_bn_add(string $a, string $b): string
{
    $out = [];
    $carry = 0;
    for ($i = strlen($a), $j = strlen($b); $i > 0 || $j > 0 || $carry > 0; $i -= 9, $j -= 9) {
        $s = ($i > 0 ? (int)substr($a, max(0, $i - 9), min(9, $i)) : 0) + ($j > 0 ? (int)substr($b, max(0, $j - 9), min(9, $j)) : 0) + $carry;
        $carry = intdiv($s, 1000000000);
        $out[] = $s % 1000000000;
    }
    return lab58_bn_join($out);
}

/** a − b pro a ≥ b */
function lab58_bn_sub(string $a, string $b): string
{
    $out = [];
    $borrow = 0;
    for ($i = strlen($a), $j = strlen($b); $i > 0; $i -= 9, $j -= 9) {
        $d = (int)substr($a, max(0, $i - 9), min(9, $i)) - ($j > 0 ? (int)substr($b, max(0, $j - 9), min(9, $j)) : 0) - $borrow;
        $borrow = $d < 0 ? 1 : 0;
        $out[] = $d + $borrow * 1000000000;
    }
    return lab58_bn_join($out);
}

function lab58_bn_mul(string $a, string $b): string
{
    if ($a === '0' || $b === '0') return '0';
    $limbs = static function (string $s): array {
        $out = [];
        for ($i = strlen($s); $i > 0; $i -= 4) $out[] = (int)substr($s, max(0, $i - 4), min(4, $i));
        return $out;
    };
    $x = $limbs($a);
    $y = $limbs($b);
    $res = array_fill(0, count($x) + count($y) + 1, 0);
    foreach ($x as $i => $xi) {
        if ($xi === 0) continue;
        $carry = 0;
        foreach ($y as $j => $yj) {
            $t = $res[$i + $j] + $xi * $yj + $carry;
            $carry = intdiv($t, 10000);
            $res[$i + $j] = $t % 10000;
        }
        for ($k = $i + count($y); $carry > 0; $k++) {
            $t = $res[$k] + $carry;
            $carry = intdiv($t, 10000);
            $res[$k] = $t % 10000;
        }
    }
    $s = '';
    for ($k = count($res) - 1; $k >= 0; $k--) $s .= str_pad((string)$res[$k], 4, '0', STR_PAD_LEFT);
    return lab58_bn_trim($s);
}

/** @return array{0:string,1:string} [podíl, zbytek]; dělení nulou nevolat */
function lab58_bn_divmod(string $a, string $b): array
{
    if (lab58_bn_cmp($a, $b) < 0) return ['0', $a];
    $bp = (float)substr($b, 0, 15);
    $bl = strlen($b) - min(15, strlen($b));
    $q = '';
    $rem = '0';
    for ($i = 0, $n = strlen($a); $i < $n; $i++) {
        $rem = $rem === '0' ? $a[$i] : $rem . $a[$i];
        if (lab58_bn_cmp($rem, $b) < 0) { $q .= '0'; continue; }
        $rp = (float)substr($rem, 0, 16);
        $est = max(1, min(9, (int)floor($rp / $bp * 10 ** ((strlen($rem) - min(16, strlen($rem))) - $bl))));
        $prod = lab58_bn_mul($b, (string)$est);
        while (lab58_bn_cmp($prod, $rem) > 0) { $est--; $prod = lab58_bn_sub($prod, $b); }
        $rem = lab58_bn_sub($rem, $prod);
        while (lab58_bn_cmp($rem, $b) >= 0) { $est++; $rem = lab58_bn_sub($rem, $b); }
        $q .= (string)$est;
    }
    return [lab58_bn_trim($q), $rem];
}

function lab58_bn_isqrt(string $n): string
{
    if ($n === '0') return '0';
    $x = '1' . str_repeat('0', intdiv(strlen($n) + 1, 2));
    for ($guard = 0; $guard < 400; $guard++) {
        $y = lab58_bn_divmod(lab58_bn_add($x, lab58_bn_divmod($n, $x)[0]), '2')[0];
        if (lab58_bn_cmp($y, $x) >= 0) return $x;
        $x = $y;
    }
    return $x;
}

// ---------------------------------------------------------------------------
// Desetinná čísla bc: ['s' => ±1, 'd' => číslice, 'k' => scale]; pravidla scale jako GNU bc
// ---------------------------------------------------------------------------

function lab58_dec(int $sign, string $digits, int $scale): array
{
    $digits = lab58_bn_trim($digits);
    return ['s' => $digits === '0' || $sign >= 0 ? 1 : -1, 'd' => $digits, 'k' => max(0, $scale)];
}

function lab58_dec_int(int $v): array
{
    return lab58_dec($v < 0 ? -1 : 1, (string)abs($v), 0);
}

/** Zkrátí (k nule) nebo prodlouží počet desetinných míst. */
function lab58_dec_to(array $x, int $k): array
{
    if ($k >= $x['k']) return lab58_dec($x['s'], $x['d'] . str_repeat('0', $k - $x['k']), $k);
    $cut = $x['k'] - $k;
    return lab58_dec($x['s'], strlen($x['d']) > $cut ? substr($x['d'], 0, -$cut) : '0', $k);
}

function lab58_dec_add(array $x, array $y): array
{
    $k = max($x['k'], $y['k']);
    $x = lab58_dec_to($x, $k);
    $y = lab58_dec_to($y, $k);
    if ($x['s'] === $y['s']) return lab58_dec($x['s'], lab58_bn_add($x['d'], $y['d']), $k);
    $c = lab58_bn_cmp($x['d'], $y['d']);
    return $c >= 0 ? lab58_dec($x['s'], lab58_bn_sub($x['d'], $y['d']), $k) : lab58_dec($y['s'], lab58_bn_sub($y['d'], $x['d']), $k);
}

function lab58_dec_neg(array $x): array
{
    return lab58_dec(-$x['s'], $x['d'], $x['k']);
}

function lab58_dec_cmp(array $x, array $y): int
{
    $d = lab58_dec_add($x, lab58_dec_neg($y));
    return $d['d'] === '0' ? 0 : $d['s'];
}

function lab58_dec_mul(array $x, array $y, int $scale): array
{
    $full = $x['k'] + $y['k'];
    return lab58_dec_to(lab58_dec($x['s'] * $y['s'], lab58_bn_mul($x['d'], $y['d']), $full), min($full, max($scale, $x['k'], $y['k'])));
}

/** null = dělení nulou */
function lab58_dec_div(array $x, array $y, int $scale): ?array
{
    if ($y['d'] === '0') return null;
    $shift = $scale + $y['k'] - $x['k'];
    $num = $x['d'] . ($shift > 0 ? str_repeat('0', $shift) : '');
    $den = $y['d'] . ($shift < 0 ? str_repeat('0', -$shift) : '');
    return lab58_dec($x['s'] * $y['s'], lab58_bn_divmod(lab58_bn_trim($num), $den)[0], $scale);
}

/** a % b = a − (a / b) · b, scale výsledku max(scale + scale(b), scale(a)) */
function lab58_dec_mod(array $x, array $y, int $scale): ?array
{
    $q = lab58_dec_div($x, $y, $scale);
    if ($q === null) return null;
    return lab58_dec_add($x, lab58_dec_neg(lab58_dec($q['s'] * $y['s'], lab58_bn_mul($q['d'], $y['d']), $q['k'] + $y['k'])));
}

/** @return array|string výsledek, nebo chyba ('div0', 'big') */
function lab58_dec_pow(array $x, int $e, int $scale): array|string
{
    if ($e === 0) return lab58_dec_int(1);
    $n = abs($e);
    if (strlen($x['d']) * $n > LAB58_BC_MAX_DIGITS * 2 && $x['d'] !== '1' && $x['d'] !== '0') return 'big';
    $r = lab58_dec_int(1);
    $b = $x;
    while ($n > 0) {
        if (($n & 1) === 1) $r = lab58_dec($r['s'] * $b['s'], lab58_bn_mul($r['d'], $b['d']), $r['k'] + $b['k']);
        $n >>= 1;
        if ($n > 0) $b = lab58_dec($b['s'] * $b['s'], lab58_bn_mul($b['d'], $b['d']), $b['k'] * 2);
    }
    if ($e < 0) return lab58_dec_div(lab58_dec_int(1), $r, $scale) ?? 'div0';
    return lab58_dec_to($r, min($x['k'] * $e, max($scale, $x['k'])));
}

function lab58_dec_sqrt(array $x, int $scale): ?array
{
    if ($x['s'] < 0 && $x['d'] !== '0') return null;
    $rs = max($scale, $x['k']);
    return lab58_dec(1, lab58_bn_isqrt($x['d'] . str_repeat('0', 2 * $rs - $x['k'])), $rs);
}

/** Výpis jako bc: bez nuly před tečkou (.5), obase 2–16, zalomení po 69 znacích zpětným lomítkem. */
function lab58_dec_format(array $x, int $obase = 10): string
{
    if ($x['d'] === '0') return '0';
    $digits = str_pad($x['d'], $x['k'] + 1, '0', STR_PAD_LEFT);
    $int = ltrim(substr($digits, 0, strlen($digits) - $x['k']), '0');
    $frac = $x['k'] > 0 ? substr($digits, -$x['k']) : '';
    if ($obase !== 10) {
        $conv = '';
        for ($v = $int === '' ? '0' : $int, $guard = 0; $v !== '0' && $guard < 20000; $guard++) {
            [$v, $r] = lab58_bn_divmod($v, (string)$obase);
            $conv = '0123456789ABCDEF'[(int)$r] . $conv;
        }
        $int = $conv;
        if ($frac !== '') {
            $f = lab58_dec(1, $frac, $x['k']);
            $out = '';
            for ($pw = '1'; strlen($pw) <= $x['k']; $pw = lab58_bn_mul($pw, (string)$obase)) {
                $f = lab58_dec(1, lab58_bn_mul($f['d'], (string)$obase), $f['k']);
                $whole = lab58_dec_to($f, 0)['d'];
                $out .= '0123456789ABCDEF'[(int)$whole];
                $f = lab58_dec_add($f, lab58_dec_neg(lab58_dec(1, $whole, 0)));
            }
            $frac = $out;
        }
    }
    $s = ($x['s'] < 0 ? '-' : '') . $int . ($frac !== '' ? '.' . $frac : '');
    return strlen($s) > 70 ? implode("\\\n", str_split($s, 69)) : $s;
}

/** Konstanta z programu v soustavě ibase (číslice 0–9 a A–F). */
function lab58_dec_parse(string $text, int $ibase): array
{
    [$int, $frac] = array_pad(explode('.', $text, 2), 2, '');
    if ($ibase === 10) return lab58_dec(1, ($int === '' ? '0' : $int) . $frac, strlen($frac));
    $v = '0';
    foreach (str_split($int === '' ? '0' : $int) as $ch) $v = lab58_bn_add(lab58_bn_mul($v, (string)$ibase), (string)min(15, (int)hexdec($ch)));
    $x = lab58_dec(1, $v, 0);
    if ($frac === '') return $x;
    $num = '0';
    $den = '1';
    foreach (str_split($frac) as $ch) { $num = lab58_bn_add(lab58_bn_mul($num, (string)$ibase), (string)min(15, (int)hexdec($ch))); $den = lab58_bn_mul($den, (string)$ibase); }
    return lab58_dec_add($x, lab58_dec_div(lab58_dec(1, $num, 0), lab58_dec(1, $den, 0), strlen($frac)) ?? lab58_dec_int(0));
}

// ---------------------------------------------------------------------------
// bc: vyhodnocení
// ---------------------------------------------------------------------------

final class Lab58BcVm
{
    public array $vars = [];
    public int $scale = 0;
    public int $ibase = 10;
    public int $obase = 10;
    public ?array $last = null;
    public string $out = '';
    public string $err = '';
    public int $loops = 0;
    public int $adr = 0;
    public bool $quit = false;

    public function __construct(public bool $mathlib)
    {
        if ($mathlib) $this->scale = 20;
    }

    public function fail(string $message): never
    {
        throw new Lab58BcRuntime($message);
    }

    public function warn(string $message): void
    {
        $this->err .= 'Runtime warning (func=(main), adr=' . $this->adr . '): ' . $message . "\n";
    }

    /** @return string|null 'break' | 'continue' | null */
    public function run(array $st): ?string
    {
        $this->adr++;
        switch ($st[0]) {
            case 'str':
                $this->out .= $st[1];
                return null;
            case 'expr':
                $v = $this->ev($st[1]);
                if ($st[1][0] !== 'assign') { $this->out .= lab58_dec_format($v, $this->obase) . "\n"; $this->last = $v; }
                return null;
            case 'print':
                foreach ($st[1] as $item) {
                    if ($item[0] === 'str') { $this->out .= strtr($item[1], ['\\n' => "\n", '\\t' => "\t", '\\q' => '"', '\\\\' => '\\', '\\a' => "\x07", '\\b' => "\x08", '\\f' => "\f", '\\r' => "\r"]); continue; }
                    $this->last = $this->ev($item);
                    $this->out .= lab58_dec_format($this->last, $this->obase);
                }
                return null;
            case 'block':
                foreach ($st[1] as $s) {
                    $r = $this->run($s);
                    if ($r !== null || $this->quit) return $r;
                }
                return null;
            case 'if':
                if ($this->truth($st[1])) return $this->run($st[2]);
                return $st[3] !== null ? $this->run($st[3]) : null;
            case 'while': case 'for':
                $isFor = $st[0] === 'for';
                if ($isFor && $st[1] !== null) $this->ev($st[1]);
                $cond = $isFor ? $st[2] : $st[1];
                while ($cond === null || $this->truth($cond)) {
                    if (++$this->loops > LAB58_BC_MAX_LOOPS) $this->fail('simulace zastavila cyklus po ' . LAB58_BC_MAX_LOOPS . ' průchodech');
                    $r = $this->run($isFor ? $st[4] : $st[2]);
                    if ($this->quit || $r === 'break') break;
                    if ($isFor && $st[3] !== null) $this->ev($st[3]);
                }
                return null;
            case 'break': case 'continue':
                return $st[0];
            default:
                $this->quit = true;
                return null;
        }
    }

    public function truth(array $n): bool
    {
        return $this->ev($n)['d'] !== '0';
    }

    public function get(string $name): array
    {
        return match ($name) {
            'scale' => lab58_dec_int($this->scale), 'ibase' => lab58_dec_int($this->ibase), 'obase' => lab58_dec_int($this->obase),
            'last' => $this->last ?? lab58_dec_int(0), default => $this->vars[$name] ?? lab58_dec_int(0),
        };
    }

    public function set(string $name, array $v): array
    {
        if (!in_array($name, ['scale', 'ibase', 'obase'], true)) {
            if ($name === 'last') $this->last = $v;
            else $this->vars[$name] = $v;
            return $v;
        }
        $int = lab58_dec_to($v, 0);
        $n = strlen($int['d']) > 6 ? 999999 * $int['s'] : (int)$int['d'] * $int['s'];
        [$lo, $hi] = ['scale' => [0, 1000], 'ibase' => [2, 16], 'obase' => [2, 16]][$name];
        if ($n < $lo || $n > $hi) {
            $this->warn($name . ($n < $lo ? ' too small, set to ' . $lo : ' too large, set to ' . $hi));
            $n = max($lo, min($hi, $n));
        }
        $this->{$name} = $n;
        return lab58_dec_int($n);
    }

    public function ev(array $n): array
    {
        switch ($n[0]) {
            case 'num': return lab58_dec_parse($n[1], $this->ibase);
            case 'var': return $this->get($n[1]);
            case 'neg': return lab58_dec_neg($this->ev($n[1]));
            case 'not': return lab58_dec_int($this->truth($n[1]) ? 0 : 1);
            case 'or': return lab58_dec_int($this->truth($n[1]) || $this->truth($n[2]) ? 1 : 0);
            case 'and': return lab58_dec_int($this->truth($n[1]) && $this->truth($n[2]) ? 1 : 0);
            case 'rel':
                $c = lab58_dec_cmp($this->ev($n[2]), $this->ev($n[3]));
                return lab58_dec_int(match ($n[1]) { '<' => $c < 0, '<=' => $c <= 0, '>' => $c > 0, '>=' => $c >= 0, '==' => $c === 0, default => $c !== 0 } ? 1 : 0);
            case 'bin': return $this->arith($n[1], $this->ev($n[2]), $this->ev($n[3]));
            case 'assign':
                $v = $this->ev($n[3]);
                return $this->set($n[2], $n[1] === '=' ? $v : $this->arith($n[1][0], $this->get($n[2]), $v));
            case 'inc':
                $old = $this->get($n[3]);
                $new = $this->set($n[3], lab58_dec_add($old, lab58_dec_int($n[1] === '++' ? 1 : -1)));
                return $n[2] === 'pre' ? $new : $old;
            default:
                return $this->call($n[1], $n[2]);
        }
    }

    public function arith(string $op, array $a, array $b): array
    {
        if ($op === '^') {
            if ($b['k'] > 0) $this->warn('non-zero scale in exponent');
            $e = lab58_dec_to($b, 0);
            $r = strlen($e['d']) > 9 ? 'big' : lab58_dec_pow($a, (int)$e['d'] * $e['s'], $this->scale);
            if ($r === 'div0') $this->fail('Divide by zero');
        } else {
            $r = match ($op) {
                '+' => lab58_dec_add($a, $b),
                '-' => lab58_dec_add($a, lab58_dec_neg($b)),
                '*' => lab58_dec_mul($a, $b, $this->scale),
                '/' => lab58_dec_div($a, $b, $this->scale) ?? $this->fail('Divide by zero'),
                default => lab58_dec_mod($a, $b, $this->scale) ?? $this->fail('Modulo by zero'),
            };
        }
        if (is_string($r) || strlen($r['d']) > LAB58_BC_MAX_DIGITS) $this->fail('simulace počítá nejvýš s ' . LAB58_BC_MAX_DIGITS . ' číslicemi');
        return $r;
    }

    public function call(string $name, array $args): array
    {
        $math = ['s', 'c', 'a', 'l', 'e'];
        if (!in_array($name, array_merge(['sqrt', 'length', 'scale'], $this->mathlib ? $math : []), true)) $this->fail('Function ' . $name . ' not defined.');
        if (count($args) !== 1) $this->fail('Parameter number mismatch');
        $v = $this->ev($args[0]);
        return match ($name) {
            'sqrt' => lab58_dec_sqrt($v, $this->scale) ?? $this->fail('Square root of a negative number'),
            'length' => lab58_dec_int($v['d'] === '0' ? 1 : max(strlen($v['d']), $v['k'])),
            'scale' => lab58_dec_int($v['k']),
            default => lab58_bc_mathlib($name, $v, $this->scale),
        };
    }
}

// ---------------------------------------------------------------------------
// bc -l: s(x), c(x), a(x), l(x), e(x) řadami se strážnými číslicemi, výsledek oříznutý na scale
// ---------------------------------------------------------------------------

function lab58_bc_series(array $x, array $x2, int $ws, int $start, int $step, bool $alternate, bool $factorial): array
{
    $sum = $x;
    $term = $x;
    $sign = 1;
    for ($i = $start; $i < 4000; $i += $step) {
        $term = lab58_dec_mul($term, $x2, $ws);
        if ($factorial) $term = (array)lab58_dec_div($term, lab58_dec_int(($i - 1) * $i), $ws);
        $t = $factorial ? $term : (array)lab58_dec_div($term, lab58_dec_int($i), $ws);
        if ($t['d'] === '0') break;
        $sign = $alternate ? -$sign : 1;
        $sum = lab58_dec_add($sum, $sign < 0 ? lab58_dec_neg($t) : $t);
    }
    return $sum;
}

function lab58_bc_mathlib(string $fn, array $x, int $scale): array
{
    $one = lab58_dec_int(1);
    $ws = $scale + 12;
    if ($fn === 'e') {
        $neg = $x['s'] < 0;
        $x = $neg ? lab58_dec_neg($x) : $x;
        if (lab58_dec_cmp($x, lab58_dec_int(5000)) > 0) throw new Lab58BcRuntime('simulace počítá nejvýš s ' . LAB58_BC_MAX_DIGITS . ' číslicemi');
        $k = 0;
        for ($y = $x; lab58_dec_cmp($y, $one) > 0; $k++) $y = lab58_dec_to(lab58_dec_mul($y, lab58_dec_parse('0.5', 10), 60), 60);
        $ws += $k + (int)ceil((int)lab58_dec_to($x, 0)['d'] * 0.4343) + ($neg ? (int)ceil((int)lab58_dec_to($x, 0)['d'] * 0.4343) : 0);
        $y = (array)lab58_dec_div($x, lab58_dec(1, lab58_bn_mul('1', (string)(2 ** min($k, 60))), 0), $ws);
        $sum = lab58_dec_add($one, $y);
        $term = $y;
        for ($i = 2; $i < 4000; $i++) {
            $term = (array)lab58_dec_div(lab58_dec_mul($term, $y, $ws), lab58_dec_int($i), $ws);
            if ($term['d'] === '0') break;
            $sum = lab58_dec_add($sum, $term);
        }
        for ($j = 0; $j < $k; $j++) $sum = lab58_dec_mul($sum, $sum, $ws);
        return lab58_dec_to($neg ? (array)lab58_dec_div($one, $sum, $scale) : $sum, $scale);
    }
    if ($fn === 'l') {
        if ($x['s'] < 0 || $x['d'] === '0') return lab58_dec_add($one, lab58_dec_neg(lab58_dec(1, '1' . str_repeat('0', $scale), 0)));
        $ws += 8;
        $k = 0;
        while ($k < 400 && (lab58_dec_cmp($x, lab58_dec_int(2)) >= 0 || lab58_dec_cmp($x, lab58_dec_parse('0.5', 10)) <= 0)) { $x = (array)lab58_dec_sqrt($x, $ws); $k++; }
        $u = (array)lab58_dec_div(lab58_dec_add($x, lab58_dec_neg($one)), lab58_dec_add($x, $one), $ws);
        $sum = lab58_bc_series($u, lab58_dec_mul($u, $u, $ws), $ws, 3, 2, false, false);
        return lab58_dec_to(lab58_dec_mul($sum, lab58_dec(1, lab58_bn_mul('1', (string)(2 ** min($k + 1, 62))), 0), $ws), $scale);
    }
    if ($fn === 'a') {
        $neg = $x['s'] < 0;
        $x = $neg ? lab58_dec_neg($x) : $x;
        $m = 1;
        while ($m < (1 << 30) && lab58_dec_cmp($x, lab58_dec_parse('0.2', 10)) > 0) {
            $x = (array)lab58_dec_div($x, lab58_dec_add($one, (array)lab58_dec_sqrt(lab58_dec_add($one, lab58_dec_mul($x, $x, $ws)), $ws)), $ws);
            $m *= 2;
        }
        $r = lab58_dec_mul(lab58_bc_series($x, lab58_dec_mul($x, $x, $ws), $ws, 3, 2, true, false), lab58_dec_int($m), $ws);
        return lab58_dec_to($neg ? lab58_dec_neg($r) : $r, $scale);
    }
    if (lab58_dec_cmp(lab58_dec(1, $x['d'], $x['k']), lab58_dec_int(1000000)) > 0) throw new Lab58BcRuntime('simulace počítá nejvýš s ' . LAB58_BC_MAX_DIGITS . ' číslicemi');
    $pi = lab58_dec_mul(lab58_dec_int(4), lab58_bc_mathlib('a', $one, $ws + 4), $ws + 4);
    if ($fn === 'c') $x = lab58_dec_add($x, (array)lab58_dec_div($pi, lab58_dec_int(2), $ws + 4));
    $twopi = lab58_dec_mul(lab58_dec_int(2), $pi, $ws + 4);
    $n = lab58_dec_to((array)lab58_dec_div($x, $twopi, $ws), 0);
    $x = lab58_dec_add($x, lab58_dec_neg(lab58_dec_mul($n, $twopi, $ws + 4)));
    return lab58_dec_to(lab58_bc_series($x, lab58_dec_mul($x, $x, $ws), $ws, 3, 2, true, true), $scale);
}

// ---------------------------------------------------------------------------
// bc – příkaz
// ---------------------------------------------------------------------------

function lab58_cmd_bc(Lab57Proc $p, array $argv): int
{
    $math = false;
    $quiet = false;
    $files = [];
    foreach (array_slice($argv, 1) as $a) {
        if ($a === '-v' || $a === '--version') { $p->out("bc 1.07.1\nCopyright 1991-1994, 1997, 1998, 2000, 2004, 2006, 2008, 2012-2017 Free Software Foundation, Inc.\n"); return 0; }
        if (preg_match('/^(-[lqswi]+|--(mathlib|quiet|standard|warn|interactive))$/', $a) === 1) {
            $math = $math || $a === '--mathlib' || ($a[1] !== '-' && str_contains($a, 'l'));
            $quiet = $quiet || $a === '--quiet' || ($a[1] !== '-' && str_contains($a, 'q'));
            continue;
        }
        $usage = "usage: bc [options] [file ...]\n  -h  --help         print this usage and exit\n  -i  --interactive  force interactive mode\n  -l  --mathlib      use the predefined math routines\n"
            . "  -q  --quiet        don't print initial banner\n  -s  --standard     non-standard bc constructs are errors\n  -w  --warn         warn about non-standard bc constructs\n  -v  --version      print version information and exit\n";
        if ($a === '-h') { $p->out($usage); return 0; }
        if (strlen($a) > 1 && $a[0] === '-') {
            $bad = str_starts_with($a, '--') ? '' : (string)(ltrim(substr($a, 1), 'lqswi') . '?')[0];
            $p->err(($bad === '' ? "bc: unrecognized option '" . $a . "'\n" : "bc: invalid option -- '" . $bad . "'\n") . $usage);
            $p->w->tip(tr('bc zná hlavně -l (matematická knihovna, 20 desetinných míst). → man bc'));
            return 1;
        }
        $files[] = $a;
    }
    $src = '';
    foreach ($files as $f) {
        $err = null;
        $c = $p->w->readFile($f, $err);
        if ($c === null) { $p->err("File $f is unavailable.\n"); lab57_error_tip($p->w, (string)$err, $f); return 1; }
        $src .= $c . "\n";
    }
    if ($p->hasStdin) {
        $src .= $p->stdin;
    } elseif ($files === []) {
        if (!$quiet) $p->out("bc 1.07.1\nCopyright 1991-1994, 1997, 1998, 2000, 2004, 2006, 2008, 2012-2017 Free Software Foundation, Inc.\nThis is free software with ABSOLUTELY NO WARRANTY.\nFor details type `warranty'. \n");
        $p->w->tip(tr("bc v simulaci nečeká na klávesnici – pošli mu výraz rourou: echo '2+3' | bc, desetinná místa: echo 'scale=2; 10/3' | bc → man bc"));
        return 0;
    }
    $tokens = lab58_bc_lex($src);
    if (is_string($tokens)) {
        [$line, $kind, $ch] = array_pad(explode(':', $tokens, 3), 3, '');
        $p->err('(standard_in) ' . $line . ': ' . ($kind === 'illegal' ? 'illegal character: ' . $ch : 'EOF encountered in a ' . $kind) . "\n");
        $p->w->tip(tr('bc zná čísla, + - * / % ^, proměnné (malými písmeny), scale=, sqrt(). Velká písmena jsou jen číslice A–F. → man bc'));
        return 1;
    }
    $parser = new Lab58BcParser($tokens);
    $vm = new Lab58BcVm($math);
    $status = 0;
    $size = 0;
    while (!$vm->quit && $size <= LAB57_MAX_OUTPUT) {
        try {
            $st = $parser->statement();
            $next = $parser->peek();
            if ($st !== null && $next[0] !== 'nl' && $next[0] !== 'eof' && !$parser->is(';')) throw new Lab58BcSyntax('syntax error');
        } catch (Lab58BcSyntax $e) {
            $p->err('(standard_in) ' . $parser->peek()[2] . ': ' . ($e->getMessage() === 'define' ? 'syntax error (vlastní funkce define simulace nepodporuje)' : 'syntax error') . "\n");
            $p->w->tip(tr("Zkontroluj výraz – např. chybějící číslo za operátorem nebo závorku. Příklad: echo 'scale=3; 22/7' | bc → man bc"));
            $status = 1;
            while (!in_array($parser->peek()[0], ['nl', 'eof'], true)) $parser->pos++;
            continue;
        }
        if ($st === null) break;
        try {
            $vm->run($st);
        } catch (Lab58BcRuntime $e) {
            $vm->err .= 'Runtime error (func=(main), adr=' . $vm->adr . '): ' . $e->getMessage() . "\n";
        }
        $size += strlen($vm->out);
        $p->out($vm->out);
        $p->err($vm->err);
        $vm->out = '';
        $vm->err = '';
    }
    return $status;
}

lab58_register_command('bc', 'lab58_cmd_bc', ['package' => 'bc']);
lab58_register_apt_package('bc', ['version' => '1.07.1-3+b1', 'description' => 'GNU bc arbitrary precision calculator language', 'size' => '109 kB', 'installed_size' => '264 kB', 'bins' => ['bc'], 'preinstalled' => true]);
