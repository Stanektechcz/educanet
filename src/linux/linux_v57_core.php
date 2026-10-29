<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – jádro simulátoru.
 *
 * BEZPEČNOSTNÍ INVARIANT: simulátor nikdy nic nespouští a nikam se nepřipojuje.
 * Žádné volání systémových příkazů ani síťových funkcí PHP. „Příkazy“ jen čtou a mění
 * data v paměti (PHP pole), která se ukládají do storage/. Audit
 * tools/v57_linux_lab_audit.php tento invariant kontroluje ve všech souborech linux_v57_*.
 */

const LAB57_SCHEMA = 3;
const LAB57_MAX_LINE = 1000;
const LAB57_MAX_OUTPUT = 120000;

/** Deterministický generátor (xorshift32) – stejné semínko = stejný svět pro audit i pro žáka. */
final class Lab57Rng
{
    private int $state;

    public function __construct(string $seed)
    {
        $this->state = ((int)hexdec(substr(hash('sha256', $seed), 0, 7))) | 1;
    }

    public function next(): int
    {
        $x = $this->state;
        $x ^= ($x << 13) & 0xFFFFFFFF;
        $x ^= ($x >> 17);
        $x ^= ($x << 5) & 0xFFFFFFFF;
        $this->state = $x & 0xFFFFFFFF;
        return $this->state;
    }

    public function int(int $min, int $max): int
    {
        if ($max <= $min) return $min;
        return $min + ($this->next() % ($max - $min + 1));
    }

    public function pick(array $items): mixed
    {
        $items = array_values($items);
        if ($items === []) return null;
        return $items[$this->int(0, count($items) - 1)];
    }

    public function shuffle(array $items): array
    {
        $items = array_values($items);
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = $this->int(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }
        return $items;
    }

    public function token(int $len = 10, string $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789'): string
    {
        $out = '';
        $n = strlen($alphabet);
        for ($i = 0; $i < $len; $i++) $out .= $alphabet[$this->next() % $n];
        return $out;
    }

    public function bytes(int $len): string
    {
        $out = '';
        for ($i = 0; $i < $len; $i++) $out .= chr($this->next() & 0xFF);
        return $out;
    }
}

/**
 * Virtuální souborový systém: plochá mapa cesta → uzel.
 * Uzel: ['t' => 'f'|'d', 'm' => práva (int), 'u' => vlastník, 'g' => skupina, 'mt' => čas,
 *        'c' => obsah, 's' => velikost (volitelně pro „velké“ soubory), 'x' => id simulovaného programu].
 * Ukládá se jen overlay (změny proti výchozímu světu úrovně), výchozí svět se staví deterministicky.
 */
final class Lab57Vfs
{
    /** @var array<string,array> */
    private array $nodes;
    /** @var array<string,array|null> */
    private array $overlay = [];
    /** @var array<string,array> */
    private array $base;
    /** @var array<string,array<string,true>>|null */
    private ?array $children = null;

    public function __construct(array $base, array $overlay = [])
    {
        $this->base = $base;
        $this->nodes = $base;
        foreach ($overlay as $path => $node) {
            $path = (string)$path;
            if ($node === null) {
                unset($this->nodes[$path]);
                $this->overlay[$path] = null;
            } elseif (is_array($node)) {
                $this->nodes[$path] = $node;
                $this->overlay[$path] = $node;
            }
        }
    }

    public static function normalize(string $path, string $cwd = '/'): string
    {
        if ($path === '') return $cwd;
        $abs = str_starts_with($path, '/') ? $path : rtrim($cwd, '/') . '/' . $path;
        $parts = [];
        foreach (explode('/', $abs) as $part) {
            if ($part === '' || $part === '.') continue;
            if ($part === '..') { array_pop($parts); continue; }
            $parts[] = $part;
        }
        return '/' . implode('/', $parts);
    }

    public static function dirname(string $abs): string
    {
        if ($abs === '/' || !str_contains($abs, '/')) return '/';
        $dir = substr($abs, 0, (int)strrpos($abs, '/'));
        return $dir === '' ? '/' : $dir;
    }

    public static function basename(string $abs): string
    {
        if ($abs === '/') return '/';
        $pos = strrpos($abs, '/');
        return $pos === false ? $abs : substr($abs, $pos + 1);
    }

    public function get(string $abs): ?array { return $this->nodes[$abs] ?? null; }
    public function exists(string $abs): bool { return isset($this->nodes[$abs]); }
    public function isDir(string $abs): bool { return ($this->nodes[$abs]['t'] ?? '') === 'd'; }
    public function isFile(string $abs): bool { return ($this->nodes[$abs]['t'] ?? '') === 'f'; }

    /** @return list<string> jména potomků (bez cesty), seřazená */
    public function children(string $dir): array
    {
        if ($this->children === null) {
            $this->children = [];
            foreach ($this->nodes as $path => $_) {
                if ($path === '/') continue;
                $this->children[self::dirname((string)$path)][self::basename((string)$path)] = true;
            }
        }
        $names = array_map('strval', array_keys($this->children[$dir] ?? []));
        sort($names, SORT_STRING);
        return $names;
    }

    public function set(string $abs, array $node): void
    {
        $this->nodes[$abs] = $node;
        $this->overlay[$abs] = $node;
        if ($this->children !== null && $abs !== '/') $this->children[self::dirname($abs)][self::basename($abs)] = true;
    }

    public function delete(string $abs): void
    {
        unset($this->nodes[$abs]);
        if (isset($this->base[$abs])) $this->overlay[$abs] = null;
        else unset($this->overlay[$abs]);
        if ($this->children !== null) unset($this->children[self::dirname($abs)][self::basename($abs)]);
    }

    /** @return list<string> cesta a všichni potomci, rodiče dřív než děti */
    public function tree(string $abs): array
    {
        if (!isset($this->nodes[$abs])) return [];
        $out = [$abs];
        if (($this->nodes[$abs]['t'] ?? '') === 'd') {
            $prefix = $abs === '/' ? '/' : $abs . '/';
            foreach (array_keys($this->nodes) as $path) {
                $path = (string)$path;
                if ($path !== $abs && str_starts_with($path, $prefix)) $out[] = $path;
            }
        }
        sort($out, SORT_STRING);
        return $out;
    }

    public function deleteTree(string $abs): void
    {
        foreach (array_reverse($this->tree($abs)) as $path) $this->delete($path);
    }

    public static function size(array $node): int
    {
        if (($node['t'] ?? '') === 'd') return 4096;
        return isset($node['s']) ? (int)$node['s'] : strlen((string)($node['c'] ?? ''));
    }

    /** Zafixuje aktuální stav jako výchozí svět úrovně (po sestavení úrovně, před aplikací změn žáka). */
    public function seal(): void
    {
        $this->base = $this->nodes;
        $this->overlay = [];
        $this->children = null;
    }

    /** Aplikuje uložené změny žáka na zafixovaný svět. */
    public function applyOverlay(array $overlay): void
    {
        foreach ($overlay as $path => $node) {
            $path = (string)$path;
            if ($node === null) {
                if (!isset($this->base[$path])) continue;
                unset($this->nodes[$path]);
                $this->overlay[$path] = null;
            } elseif (is_array($node)) {
                $this->nodes[$path] = $node;
                $this->overlay[$path] = $node;
            }
        }
        $this->children = null;
    }

    /** @return array<string,array|null> */
    public function overlay(): array { return $this->overlay; }

    /** @return array<string,array> */
    public function all(): array { return $this->nodes; }

    public function isBase(string $abs): bool { return isset($this->base[$abs]); }
}

// ---------------------------------------------------------------------------
// Uložení overlaye (binární obsah → base64, aby šel zapsat do JSON)
// ---------------------------------------------------------------------------

function lab57_overlay_encode(array $overlay): array
{
    $out = [];
    foreach ($overlay as $path => $node) {
        if (!is_array($node)) { $out[$path] = null; continue; }
        $content = (string)($node['c'] ?? '');
        if ($content !== '' && !mb_check_encoding($content, 'UTF-8')) {
            unset($node['c']);
            $node['c64'] = base64_encode($content);
        }
        $out[$path] = $node;
    }
    return $out;
}

function lab57_overlay_decode(array $raw): array
{
    $out = [];
    foreach ($raw as $path => $node) {
        if (!is_array($node)) { $out[(string)$path] = null; continue; }
        if (isset($node['c64'])) {
            $node['c'] = (string)base64_decode((string)$node['c64'], true);
            unset($node['c64']);
        }
        $out[(string)$path] = $node;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Formátování
// ---------------------------------------------------------------------------

function lab57_mode_string(array $node): string
{
    $mode = (int)($node['m'] ?? 0644);
    $type = ($node['t'] ?? 'f') === 'd' ? 'd' : '-';
    $bits = '';
    foreach ([6, 3, 0] as $shift) {
        $v = ($mode >> $shift) & 7;
        $bits .= ($v & 4 ? 'r' : '-') . ($v & 2 ? 'w' : '-') . ($v & 1 ? 'x' : '-');
    }
    if ($mode & 01000) $bits[8] = $bits[8] === 'x' ? 't' : 'T';
    if ($mode & 04000) $bits[2] = $bits[2] === 'x' ? 's' : 'S';
    if ($mode & 02000) $bits[5] = $bits[5] === 'x' ? 's' : 'S';
    return $type . $bits;
}

function lab57_octal(int $mode): string
{
    return sprintf('%04o', $mode & 07777);
}

function lab57_human_size(int $bytes): string
{
    if ($bytes < 1024) return (string)$bytes;
    $value = (float)$bytes;
    foreach (['K', 'M', 'G', 'T'] as $unit) {
        $value /= 1024;
        if ($value < 1024 || $unit === 'T') {
            if ($value < 10) {
                $rounded = ceil($value * 10) / 10;
                return ($rounded >= 10 ? (string)(int)$rounded : number_format($rounded, 1, '.', '')) . $unit;
            }
            return (string)(int)ceil($value) . $unit;
        }
    }
    return (string)$bytes;
}

function lab57_ls_date(int $ts, int $now): string
{
    $month = date('M', $ts);
    $day = str_pad(date('j', $ts), 2, ' ', STR_PAD_LEFT);
    if (abs($now - $ts) > 15552000) return $month . ' ' . $day . '  ' . date('Y', $ts);
    return $month . ' ' . $day . ' ' . date('H:i', $ts);
}

// ---------------------------------------------------------------------------
// Regulární výrazy (grep/sed/awk). Vzory od žáků se převádí na PCRE a vyhodnocují
// s nízkým limitem zpětného navracení, aby jeden vzor nemohl zahltit server.
// ---------------------------------------------------------------------------

function lab57_regex(string $pattern, string $flavor = 'bre', bool $icase = false, bool $word = false, bool $whole = false): ?string
{
    $body = lab57_regex_body($pattern, $flavor);
    if ($word) $body = '(?<![\p{L}\p{N}_])(?:' . $body . ')(?![\p{L}\p{N}_])';
    if ($whole) $body = '^(?:' . $body . ')$';
    $regex = '~' . $body . '~u' . ($icase ? 'i' : '');
    if (@preg_match($regex, '') === false) {
        $regex = '~' . $body . '~' . ($icase ? 'i' : '');
        if (@preg_match($regex, '') === false) return null;
    }
    return $regex;
}

/** Převede vzor BRE/ERE/pevný řetězec na tělo PCRE (bez oddělovačů). */
function lab57_regex_body(string $pattern, string $flavor): string
{
    if ($flavor === 'fixed') {
        $body = preg_quote($pattern, '~');
    } elseif ($flavor === 'ere') {
        $body = str_replace('~', '\~', $pattern);
    } else {
        // BRE: \| \+ \? \( \) \{ \} jsou speciální, bez lomítka jde o obyčejné znaky.
        $body = '';
        $len = strlen($pattern);
        for ($i = 0; $i < $len; $i++) {
            $ch = $pattern[$i];
            if ($ch === '\\' && $i + 1 < $len) {
                $next = $pattern[$i + 1];
                $body .= str_contains('|+?(){}', $next) ? $next : '\\' . $next;
                $i++;
                continue;
            }
            if (str_contains('|+?(){}', $ch)) { $body .= '\\' . $ch; continue; }
            if ($ch === '~') { $body .= '\~'; continue; }
            $body .= $ch;
        }
    }
    // POSIX třídy [[:digit:]] apod. PCRE zná také, jen je potřeba zachovat hranaté závorky.
    return $body;
}

function lab57_glob_regex(string $glob): string
{
    $out = '';
    $len = strlen($glob);
    for ($i = 0; $i < $len; $i++) {
        $ch = $glob[$i];
        if ($ch === '*') { $out .= '.*'; continue; }
        if ($ch === '?') { $out .= '.'; continue; }
        if ($ch === '[') {
            $end = strpos($glob, ']', $i + 1);
            if ($end !== false) {
                $set = substr($glob, $i + 1, $end - $i - 1);
                if ($set !== '' && $set[0] === '!') $set = '^' . substr($set, 1);
                $out .= '[' . str_replace(['\\', '~'], ['\\\\', '\~'], $set) . ']';
                $i = $end;
                continue;
            }
        }
        $out .= preg_quote($ch, '~');
    }
    return '~^' . $out . '$~s';
}

function lab57_glob_match(string $glob, string $name): bool
{
    return preg_match(lab57_glob_regex($glob), $name) === 1;
}

function lab57_has_glob(string $text): bool
{
    return strpbrk($text, '*?[') !== false;
}

/** Je obsah čitelný text? (UTF-8 bez nulových bajtů a s minimem řídicích znaků.) */
function lab57_is_text(string $content): bool
{
    if ($content === '') return true;
    if (str_contains($content, "\0")) return false;
    if (!mb_check_encoding($content, 'UTF-8')) return false;
    $control = (int)preg_match_all('/[\x01-\x08\x0E-\x1F\x7F]/', $content);
    return $control <= max(1, (int)(strlen($content) / 50));
}

/** Nejbližší známý příkaz k překlepu (Levenshtein ≤ 2). */
function lab57_suggest(string $word, array $known): ?string
{
    $best = null;
    $bestScore = 3;
    foreach ($known as $candidate) {
        $candidate = (string)$candidate;
        if ($candidate === $word || abs(strlen($candidate) - strlen($word)) > 2) continue;
        $score = levenshtein($word, $candidate);
        if ($score < $bestScore) { $best = $candidate; $bestScore = $score; }
    }
    return $best;
}

/** Rozdělí text na řádky; prázdný řádek po koncovém \n se nepočítá. */
function lab57_lines(string $text): array
{
    if ($text === '') return [];
    $lines = explode("\n", $text);
    if (end($lines) === '') array_pop($lines);
    return $lines;
}

function lab57_join(array $lines): string
{
    return $lines === [] ? '' : implode("\n", $lines) . "\n";
}

/** Rozbalí krátké volby: -la → -l -a. Volby s hodnotou (-n5) rozdělí na -n 5. */
function lab57_split_flags(array $args, array $withValue = []): array
{
    $out = [];
    $stop = false;
    foreach ($args as $arg) {
        $arg = (string)$arg;
        if ($stop || $arg === '-' || !str_starts_with($arg, '-') || str_starts_with($arg, '--')) {
            if ($arg === '--') $stop = true;
            $out[] = $arg;
            continue;
        }
        if (strlen($arg) === 2 || preg_match('/^-\d+$/', $arg)) { $out[] = $arg; continue; }
        $chars = substr($arg, 1);
        $len = strlen($chars);
        for ($i = 0; $i < $len; $i++) {
            $flag = '-' . $chars[$i];
            if (in_array($flag, $withValue, true) && $i + 1 < $len) {
                $out[] = $flag;
                $out[] = substr($chars, $i + 1);
                break;
            }
            $out[] = $flag;
        }
    }
    return $out;
}

/** Porovnání jmen jako ls v locale C.UTF-8 s ignorováním tečky na začátku a velikosti písmen. */
function lab57_name_cmp(string $a, string $b): int
{
    $ka = strtolower(ltrim($a, '.'));
    $kb = strtolower(ltrim($b, '.'));
    return $ka === $kb ? strcmp($a, $b) : strcmp($ka, $kb);
}
