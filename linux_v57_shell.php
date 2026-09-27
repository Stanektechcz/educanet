<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }


/**
 * EDUCANET v57 · Linux Lab – simulovaný bash.
 *
 * Lexer → parser → expanze → roury a přesměrování. Příkazy jsou PHP funkce nad světem
 * v paměti (Lab57World); nic se nespouští v operačním systému serveru.
 */

const LAB57_MAX_STEPS = 400;

/** Kontext jednoho běžícího příkazu: vstup, výstupní kanály, zda jde výstup na terminál. */
final class Lab57Proc
{
    public string $stdin = '';
    public bool $hasStdin = false;
    public bool $tty = false;
    public string $name = '';
    /** @var list<array{0:int,1:string}> */
    public array $chunks = [];

    public function __construct(public Lab57World $w)
    {
    }

    public function out(string $text): void
    {
        if ($text !== '') $this->chunks[] = [1, $text];
    }

    public function line(string $text = ''): void
    {
        $this->chunks[] = [1, $text . "\n"];
    }

    public function err(string $text): void
    {
        if ($text !== '') $this->chunks[] = [2, $text];
    }

    /** Chyba ve formátu „příkaz: zpráva“, vrací návratový kód. */
    public function fail(string $message, int $code = 1): int
    {
        $this->chunks[] = [2, $this->name . ': ' . $message . "\n"];
        return $code;
    }

    public function stdout(): string
    {
        $out = '';
        foreach ($this->chunks as [$fd, $text]) if ($fd === 1) $out .= $text;
        return $out;
    }
}

final class Lab57SyntaxError extends RuntimeException
{
}

/** @return array<string,string> jméno příkazu → PHP funkce (jen příkazy v57) */
function lab57_command_registry_core(): array
{
    static $registry = null;
    if (is_array($registry)) return $registry;
    $registry = [];
    foreach (['lab57_cmds_files', 'lab57_cmds_shell', 'lab57_cmds_text', 'lab57_cmds_sys', 'lab57_cmds_net', 'lab57_cmds_lab'] as $provider) {
        if (function_exists($provider)) $registry = array_merge($registry, $provider());
    }
    ksort($registry);
    return $registry;
}

/** @return array<string,string> jméno příkazu → PHP funkce (v57 + příkazy registrované přes lab58_register_command) */
function lab57_command_registry(): array
{
    static $registry = null;
    static $ver = -1;
    $current = function_exists('lab58_registry_version') ? lab58_registry_version() : 0;
    if (is_array($registry) && $ver === $current) return $registry;
    $registry = lab57_command_registry_core();
    if (function_exists('lab58_commands')) $registry = array_merge($registry, lab58_commands());
    ksort($registry);
    $ver = $current;
    return $registry;
}

/** Příkazy, které jsou dostupné až po instalaci balíčku (apt install …). */
function lab57_command_package(string $name): ?string
{
    $meta = function_exists('lab58_command_meta') ? lab58_command_meta($name) : null;
    if ($meta !== null) return $meta['package'];
    return ['cowsay' => 'cowsay', 'ifconfig' => 'net-tools', 'netstat' => 'net-tools'][$name] ?? null;
}

/** Cesta k „binárce“ příkazu (registrované příkazy si ji mohou zvolit, výchozí /usr/bin/<jméno>). */
function lab57_command_bin(string $name): string
{
    $meta = function_exists('lab58_command_meta') ? lab58_command_meta($name) : null;
    if ($meta !== null) return (string)$meta['bin'];
    return ($name === 'nginx' ? '/usr/sbin/' : '/usr/bin/') . $name;
}

/** Vestavěné příkazy shellu – nehledají se v $PATH. */
function lab57_builtins(): array
{
    static $list = null;
    static $ver = -1;
    $current = function_exists('lab58_registry_version') ? lab58_registry_version() : 0;
    if (is_array($list) && $ver === $current) return $list;
    $list = ['cd', 'export', 'unset', 'alias', 'unalias', 'type', 'history', 'help', 'exit', 'logout', 'source', '.', 'true', 'false', 'echo', 'printf', 'pwd', 'test', '[', 'kill', 'mise', 'hint', 'submit', 'check', 'answer', 'reset'];
    if (function_exists('lab58_registry')) {
        foreach (lab58_registry()['commands'] as $name => $meta) if ($meta['builtin'] && !in_array($name, $list, true)) $list[] = (string)$name;
    }
    $ver = $current;
    return $list;
}

/** Najde spustitelný soubor v $PATH (jako which). */
function lab57_which(Lab57World $w, string $name): ?string
{
    foreach (explode(':', (string)($w->envAll()['PATH'] ?? '')) as $dir) {
        if ($dir === '') continue;
        $abs = Lab57Vfs::normalize($name, $dir);
        $node = $w->fs->get($abs);
        if ($node !== null && ($node['t'] ?? '') === 'f' && ((int)($node['m'] ?? 0) & 0111) !== 0) return $abs;
    }
    return null;
}

// ---------------------------------------------------------------------------
// Lexer
// ---------------------------------------------------------------------------

/**
 * Rozloží řádek na tokeny: ['W', části] slovo, ['O', op] operátor, ['R', op] přesměrování.
 * Část slova: ['l', text, uvozovky?] | ['v', proměnná, uvozovky?] | ['s', $(…), uvozovky?] | ['a', $((…)), uvozovky?] | ['t', uživatel, false]
 */
function lab57_lex(string $src): array
{
    $tokens = [];
    $parts = [];
    $inWord = false;
    $n = strlen($src);
    $i = 0;
    $flush = static function () use (&$tokens, &$parts, &$inWord): void {
        if ($inWord) $tokens[] = ['W', $parts];
        $parts = [];
        $inWord = false;
    };
    $lit = static function (string $text, bool $quoted) use (&$parts, &$inWord): void {
        $inWord = true;
        $last = count($parts) - 1;
        if ($last >= 0 && $parts[$last][0] === 'l' && $parts[$last][2] === $quoted) {
            $parts[$last][1] .= $text;
            return;
        }
        $parts[] = ['l', $text, $quoted];
    };
    while ($i < $n) {
        $c = $src[$i];
        if ($c === ' ' || $c === "\t" || $c === "\r") { $flush(); $i++; continue; }
        if ($c === "\n") { $flush(); $tokens[] = ['O', ';']; $i++; continue; }
        if ($c === '#' && !$inWord) break;
        if (!$inWord && ($c === '1' || $c === '2') && ($src[$i + 1] ?? '') === '>') {
            if ($c === '2' && substr($src, $i + 1, 3) === '>&1') { $tokens[] = ['R', '2>&1']; $i += 4; continue; }
            if ($c === '1' && substr($src, $i + 1, 3) === '>&2') { $tokens[] = ['R', '>&2']; $i += 4; continue; }
            if (substr($src, $i + 1, 2) === '>>') { $tokens[] = ['R', $c === '2' ? '2>>' : '>>']; $i += 3; continue; }
            $tokens[] = ['R', $c === '2' ? '2>' : '>'];
            $i += 2;
            continue;
        }
        if (($c === '<' || $c === '>') && ($src[$i + 1] ?? '') === '(') {
            throw new Lab57SyntaxError(tr('náhradu procesů <(…) simulace nepodporuje – ulož výstup do souboru a porovnej soubory'));
        }
        if (strpos('|&;<>', $c) !== false) {
            $flush();
            $two = substr($src, $i, 2);
            $three = substr($src, $i, 3);
            if ($two === '||') { $tokens[] = ['O', '||']; $i += 2; }
            elseif ($two === '&&') { $tokens[] = ['O', '&&']; $i += 2; }
            elseif ($three === '&>>') { $tokens[] = ['R', '&>>']; $i += 3; }
            elseif ($two === '&>') { $tokens[] = ['R', '&>']; $i += 2; }
            elseif ($c === '|') { $tokens[] = ['O', '|']; $i++; }
            elseif ($c === '&') { $tokens[] = ['O', '&']; $i++; }
            elseif ($c === ';') { $tokens[] = ['O', ';']; $i++; }
            elseif ($three === '>&2') { $tokens[] = ['R', '>&2']; $i += 3; }
            elseif ($two === '>>') { $tokens[] = ['R', '>>']; $i += 2; }
            elseif ($c === '>') { $tokens[] = ['R', '>']; $i++; }
            elseif ($three === '<<<') { $tokens[] = ['R', '<<<']; $i += 3; }
            elseif ($two === '<<') { throw new Lab57SyntaxError(tr('here-dokument (<<) simulace nepodporuje – použij echo … | příkaz nebo <<<')); }
            else { $tokens[] = ['R', '<']; $i++; }
            continue;
        }
        if ($c === "'") {
            $end = strpos($src, "'", $i + 1);
            if ($end === false) throw new Lab57SyntaxError("unexpected EOF while looking for matching `''");
            $lit(substr($src, $i + 1, $end - $i - 1), true);
            $i = $end + 1;
            continue;
        }
        if ($c === '"') {
            $i++;
            $inWord = true;
            $buf = '';
            $closed = false;
            while ($i < $n) {
                $d = $src[$i];
                if ($d === '"') { $closed = true; $i++; break; }
                if ($d === '\\' && $i + 1 < $n && strpos("\"\\\$`", $src[$i + 1]) !== false) { $buf .= $src[$i + 1]; $i += 2; continue; }
                if ($d === '$' || $d === '`') {
                    [$part, $len] = lab57_lex_dollar($src, $i);
                    if ($part !== null) {
                        if ($buf !== '') { $lit($buf, true); $buf = ''; }
                        $part[2] = true;
                        $parts[] = $part;
                        $i += $len;
                        continue;
                    }
                }
                $buf .= $d;
                $i++;
            }
            if (!$closed) throw new Lab57SyntaxError('unexpected EOF while looking for matching `"\'');
            $lit($buf, true);
            continue;
        }
        if ($c === '\\') {
            if ($i + 1 < $n) { $lit($src[$i + 1], true); $i += 2; } else { $i++; }
            continue;
        }
        if ($c === '$' || $c === '`') {
            [$part, $len] = lab57_lex_dollar($src, $i);
            if ($part !== null) { $inWord = true; $parts[] = $part; $i += $len; continue; }
        }
        if ($c === '~' && !$inWord) {
            $j = $i + 1;
            while ($j < $n && preg_match('/[A-Za-z0-9_.-]/', $src[$j])) $j++;
            $next = $j < $n ? $src[$j] : '';
            if ($next === '' || $next === '/' || strpos(" \t;|&<>", $next) !== false) {
                $inWord = true;
                $parts[] = ['t', substr($src, $i + 1, $j - $i - 1), false];
                $i = $j;
                continue;
            }
        }
        $lit($c, false);
        $i++;
    }
    $flush();
    return $tokens;
}

/** @return array{0:?array,1:int} část slova a počet spotřebovaných znaků */
function lab57_lex_dollar(string $src, int $i): array
{
    $n = strlen($src);
    if ($src[$i] === '`') {
        $end = strpos($src, '`', $i + 1);
        if ($end === false) throw new Lab57SyntaxError('unexpected EOF while looking for matching ``\'');
        return [['s', substr($src, $i + 1, $end - $i - 1), false], $end - $i + 1];
    }
    $next = $src[$i + 1] ?? '';
    if ($next === '(' && ($src[$i + 2] ?? '') === '(') {
        $depth = 0;
        for ($j = $i + 3; $j < $n - 1; $j++) {
            if ($src[$j] === '(') { $depth++; continue; }
            if ($src[$j] === ')') {
                if ($depth === 0 && $src[$j + 1] === ')') return [['a', substr($src, $i + 3, $j - $i - 3), false], $j + 2 - $i];
                $depth--;
            }
        }
        throw new Lab57SyntaxError("unexpected EOF while looking for matching `))'");
    }
    if ($next === '(') {
        $depth = 0;
        $quote = '';
        for ($j = $i + 2; $j < $n; $j++) {
            $ch = $src[$j];
            if ($quote !== '') { if ($ch === $quote) $quote = ''; continue; }
            if ($ch === "'" || $ch === '"') { $quote = $ch; continue; }
            if ($ch === '(') { $depth++; continue; }
            if ($ch === ')') {
                if ($depth === 0) return [['s', substr($src, $i + 2, $j - $i - 2), false], $j + 1 - $i];
                $depth--;
            }
        }
        throw new Lab57SyntaxError("unexpected EOF while looking for matching `)'");
    }
    if ($next === '{') {
        $end = strpos($src, '}', $i + 2);
        if ($end === false) throw new Lab57SyntaxError("unexpected EOF while looking for matching `}'");
        $name = substr($src, $i + 2, $end - $i - 2);
        if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*|[0-9]+|[?$#@*])$/', $name)) throw new Lab57SyntaxError('${' . $name . '}: bad substitution');
        return [['v', $name, false], $end - $i + 1];
    }
    if ($next !== '' && preg_match('/[A-Za-z_]/', $next) === 1) {
        preg_match('/[A-Za-z_][A-Za-z0-9_]*/A', $src, $m, 0, $i + 1);
        return [['v', (string)$m[0], false], strlen((string)$m[0]) + 1];
    }
    if ($next !== '' && strpos('?$#@*0123456789', $next) !== false) return [['v', $next, false], 2];
    return [null, 1];
}

// ---------------------------------------------------------------------------
// Parser
// ---------------------------------------------------------------------------

/** @return list<array{andor:list<array{op:?string,pipe:array}>,bg:bool}> */
function lab57_parse(array $tokens): array
{
    $pos = 0;
    $n = count($tokens);
    $items = [];
    while ($pos < $n) {
        if ($tokens[$pos][0] === 'O' && $tokens[$pos][1] === ';') {
            if ($items === []) throw new Lab57SyntaxError("syntax error near unexpected token `;'");
            $pos++;
            continue;
        }
        if ($tokens[$pos][0] === 'O') throw new Lab57SyntaxError('syntax error near unexpected token `' . $tokens[$pos][1] . "'");
        $andor = [];
        $op = null;
        while (true) {
            $andor[] = ['op' => $op, 'pipe' => lab57_parse_pipeline($tokens, $pos)];
            if ($pos < $n && $tokens[$pos][0] === 'O' && in_array($tokens[$pos][1], ['&&', '||'], true)) {
                $op = (string)$tokens[$pos][1];
                $pos++;
                if ($pos >= $n) throw new Lab57SyntaxError('syntax error: unexpected end of file');
                continue;
            }
            break;
        }
        $bg = false;
        if ($pos < $n && $tokens[$pos][0] === 'O') {
            $t = (string)$tokens[$pos][1];
            if ($t === '&') { $bg = true; $pos++; }
            elseif ($t === ';') { $pos++; }
            else throw new Lab57SyntaxError("syntax error near unexpected token `$t'");
        }
        $items[] = ['andor' => $andor, 'bg' => $bg];
    }
    return $items;
}

function lab57_parse_pipeline(array $tokens, int &$pos): array
{
    $n = count($tokens);
    $neg = false;
    if ($pos < $n && $tokens[$pos][0] === 'W' && lab57_word_is($tokens[$pos][1], '!')) { $neg = true; $pos++; }
    $cmds = [];
    while (true) {
        $cmds[] = lab57_parse_command($tokens, $pos);
        if ($pos < $n && $tokens[$pos][0] === 'O' && $tokens[$pos][1] === '|') {
            $pos++;
            if ($pos >= $n) throw new Lab57SyntaxError('syntax error: unexpected end of file');
            continue;
        }
        break;
    }
    return ['neg' => $neg, 'cmds' => $cmds];
}

function lab57_parse_command(array $tokens, int &$pos): array
{
    $n = count($tokens);
    $cmd = ['assign' => [], 'words' => [], 'redirs' => []];
    while ($pos < $n) {
        [$type, $val] = $tokens[$pos];
        if ($type === 'O') break;
        if ($type === 'R') {
            $pos++;
            if (in_array($val, ['2>&1', '>&2'], true)) { $cmd['redirs'][] = [$val, null]; continue; }
            if ($pos >= $n || $tokens[$pos][0] !== 'W') {
                $near = $pos < $n ? (string)$tokens[$pos][1] : 'newline';
                throw new Lab57SyntaxError("syntax error near unexpected token `$near'");
            }
            $cmd['redirs'][] = [$val, $tokens[$pos][1]];
            $pos++;
            continue;
        }
        if ($cmd['words'] === [] && ($assign = lab57_word_assignment($val)) !== null) {
            $cmd['assign'][] = $assign;
            $pos++;
            continue;
        }
        $cmd['words'][] = $val;
        $pos++;
    }
    if ($cmd['words'] === [] && $cmd['assign'] === [] && $cmd['redirs'] === []) {
        $near = $pos < $n ? (string)$tokens[$pos][1] : 'newline';
        throw new Lab57SyntaxError("syntax error near unexpected token `$near'");
    }
    return $cmd;
}

function lab57_word_is(array $parts, string $text): bool
{
    return count($parts) === 1 && $parts[0][0] === 'l' && $parts[0][2] === false && $parts[0][1] === $text;
}

/** @return array{0:string,1:array}|null jméno proměnné a části hodnoty */
function lab57_word_assignment(array $parts): ?array
{
    if ($parts === [] || $parts[0][0] !== 'l' || $parts[0][2] !== false) return null;
    if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/s', (string)$parts[0][1], $m)) return null;
    $value = array_slice($parts, 1);
    if ($m[2] !== '' || $value === []) array_unshift($value, ['l', (string)$m[2], $m[2] === '']);
    return [(string)$m[1], $value];
}

function lab57_word_text(?array $parts): string
{
    $out = '';
    foreach ((array)$parts as $part) {
        $out .= match ($part[0]) {
            'l' => (string)$part[1],
            'v' => '$' . $part[1],
            's' => '$(' . $part[1] . ')',
            'a' => '$((' . $part[1] . '))',
            default => '~' . $part[1],
        };
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Expanze: proměnné, ~, $(…), $((…)), rozdělení na pole, globy
// ---------------------------------------------------------------------------

/** @return list<string> */
function lab57_expand(Lab57World $w, array $parts, bool $glob = true): array
{
    $fields = [[]];
    $keep = [false];
    $cur = 0;
    foreach ($parts as $part) {
        [$kind, $value, $quoted] = $part;
        if ($kind === 'l') {
            $fields[$cur][] = [(string)$value, (bool)$quoted];
            if ($quoted) $keep[$cur] = true;
            continue;
        }
        if ($kind === 't') {
            $user = (string)$value;
            $home = $user === '' ? (string)($w->envAll()['HOME'] ?? $w->home()) : (isset($w->users[$user]) ? $w->home($user) : '~' . $user);
            $fields[$cur][] = [$home, true];
            $keep[$cur] = true;
            continue;
        }
        $text = match ($kind) {
            'v' => lab57_var($w, (string)$value),
            's' => lab57_subst($w, (string)$value),
            default => lab57_arith($w, (string)$value),
        };
        if ($quoted) {
            $fields[$cur][] = [$text, true];
            $keep[$cur] = true;
            continue;
        }
        $pieces = preg_split('/[ \t\n]+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($pieces === []) continue;
        $startsWs = strpbrk($text[0], " \t\n") !== false;
        foreach ($pieces as $k => $piece) {
            if ($k > 0 || ($startsWs && $fields[$cur] !== [])) { $fields[] = []; $keep[] = false; $cur++; }
            $fields[$cur][] = [$piece, false];
        }
        if (strpbrk(substr($text, -1), " \t\n") !== false) { $fields[] = []; $keep[] = false; $cur++; }
    }
    $out = [];
    foreach ($fields as $idx => $segments) {
        if ($segments === [] && !$keep[$idx]) continue;
        $plain = '';
        $pattern = '';
        $hasGlob = false;
        foreach ($segments as [$text, $quoted]) {
            $plain .= $text;
            if ($quoted) {
                $pattern .= addcslashes($text, '\\*?[]');
            } else {
                $pattern .= $text;
                if (lab57_has_glob($text)) $hasGlob = true;
            }
        }
        if ($glob && $hasGlob) {
            $matches = lab57_glob($w, $pattern);
            if ($matches !== []) { foreach ($matches as $match) $out[] = $match; continue; }
        }
        $out[] = $plain;
    }
    return $out;
}

function lab57_var(Lab57World $w, string $name): string
{
    if (ctype_digit($name)) return $name === '0' ? 'bash' : (string)($w->args[(int)$name - 1] ?? '');
    return match ($name) {
        '?' => (string)$w->lastExit,
        '$' => '1487',
        '#' => (string)count($w->args),
        '@', '*' => implode(' ', $w->args),
        'RANDOM' => (string)((($w->steps + 1) * 7919 + crc32($w->seed . count($w->history))) % 32768),
        'UID', 'EUID' => (string)$w->uid($w->effectiveUser()),
        'PPID' => '1480',
        'SECONDS' => '1800',
        default => (string)($w->envAll()[$name] ?? ''),
    };
}

function lab57_subst(Lab57World $w, string $src): string
{
    if ($w->depth >= 4) { $w->stray[] = [2, tr('bash: příliš hluboké vnoření $(…)') . "\n"]; return ''; }
    $w->depth++;
    $term = [];
    try {
        lab57_exec_source($w, $src, $term);
    } catch (Lab57SyntaxError $e) {
        $w->stray[] = [2, 'bash: command substitution: ' . $e->getMessage() . "\n"];
    } finally {
        $w->depth--;
    }
    $out = '';
    foreach ($term as [$fd, $text]) {
        if ($fd === 1) $out .= $text;
        else $w->stray[] = [$fd, $text];
    }
    return rtrim($out, "\n");
}

function lab57_arith(Lab57World $w, string $expr): string
{
    try {
        return (string)(new Lab57Arith($w, $expr))->evaluate();
    } catch (Throwable $e) {
        $w->stray[] = [2, 'bash: ' . trim($expr) . ': ' . $e->getMessage() . "\n"];
        return '0';
    }
}

/** Celočíselná aritmetika $((…)) včetně zápisu soustav: 2#1010, 16#ff, 0x1f, 017. */
final class Lab57Arith
{
    /** @var list<array{0:string,1:string}> */
    private array $tokens = [];
    private int $pos = 0;

    public function __construct(private Lab57World $w, string $expr)
    {
        preg_match_all('/\s*(?:(\d+#[0-9A-Za-z@_]+)|(0[xX][0-9A-Fa-f]+)|(\d+)|([A-Za-z_][A-Za-z0-9_]*)|(\*\*|<=|>=|==|!=|&&|\|\||[-+*\/%()<>!]))/A', $expr, $m, PREG_SET_ORDER);
        $consumed = 0;
        foreach ($m as $match) {
            $consumed += strlen($match[0]);
            if (($match[1] ?? '') !== '') $this->tokens[] = ['n', $match[1]];
            elseif (($match[2] ?? '') !== '') $this->tokens[] = ['n', $match[2]];
            elseif (($match[3] ?? '') !== '') $this->tokens[] = ['n', $match[3]];
            elseif (($match[4] ?? '') !== '') $this->tokens[] = ['i', $match[4]];
            else $this->tokens[] = ['o', (string)$match[5]];
        }
        if (trim(substr($expr, $consumed)) !== '') throw new RuntimeException('syntax error in expression');
    }

    public function evaluate(): int
    {
        if ($this->tokens === []) return 0;
        $value = $this->orExpr();
        if ($this->pos < count($this->tokens)) throw new RuntimeException('syntax error in expression (error token is "' . $this->tokens[$this->pos][1] . '")');
        return $value;
    }

    private function peek(): ?string
    {
        $t = $this->tokens[$this->pos] ?? null;
        return $t !== null && $t[0] === 'o' ? $t[1] : null;
    }

    private function orExpr(): int
    {
        $v = $this->andExpr();
        while ($this->peek() === '||') { $this->pos++; $r = $this->andExpr(); $v = ($v !== 0 || $r !== 0) ? 1 : 0; }
        return $v;
    }

    private function andExpr(): int
    {
        $v = $this->cmpExpr();
        while ($this->peek() === '&&') { $this->pos++; $r = $this->cmpExpr(); $v = ($v !== 0 && $r !== 0) ? 1 : 0; }
        return $v;
    }

    private function cmpExpr(): int
    {
        $v = $this->addExpr();
        while (in_array($this->peek(), ['==', '!=', '<', '>', '<=', '>='], true)) {
            $op = (string)$this->peek();
            $this->pos++;
            $r = $this->addExpr();
            $v = match ($op) { '==' => (int)($v === $r), '!=' => (int)($v !== $r), '<' => (int)($v < $r), '>' => (int)($v > $r), '<=' => (int)($v <= $r), default => (int)($v >= $r) };
        }
        return $v;
    }

    private function addExpr(): int
    {
        $v = $this->mulExpr();
        while (in_array($this->peek(), ['+', '-'], true)) {
            $op = $this->peek();
            $this->pos++;
            $r = $this->mulExpr();
            $v = $op === '+' ? $v + $r : $v - $r;
        }
        return $v;
    }

    private function mulExpr(): int
    {
        $v = $this->powExpr();
        while (in_array($this->peek(), ['*', '/', '%'], true)) {
            $op = $this->peek();
            $this->pos++;
            $r = $this->powExpr();
            if ($op !== '*' && $r === 0) throw new RuntimeException('division by 0 (error token is "0")');
            $v = match ($op) { '*' => $v * $r, '/' => intdiv($v, $r), default => $v % $r };
        }
        return $v;
    }

    private function powExpr(): int
    {
        $v = $this->unary();
        if ($this->peek() === '**') {
            $this->pos++;
            $r = $this->powExpr();
            if ($r < 0) throw new RuntimeException('exponent less than 0');
            $result = $v ** min($r, 62);
            $v = is_int($result) ? $result : PHP_INT_MAX;
        }
        return $v;
    }

    private function unary(): int
    {
        $op = $this->peek();
        if ($op === '-' || $op === '+' || $op === '!') {
            $this->pos++;
            $v = $this->unary();
            return $op === '-' ? -$v : ($op === '!' ? (int)($v === 0) : $v);
        }
        return $this->primary();
    }

    private function primary(): int
    {
        $t = $this->tokens[$this->pos] ?? null;
        if ($t === null) throw new RuntimeException('syntax error: operand expected');
        $this->pos++;
        if ($t[0] === 'o' && $t[1] === '(') {
            $v = $this->orExpr();
            if ($this->peek() !== ')') throw new RuntimeException("missing `)'");
            $this->pos++;
            return $v;
        }
        if ($t[0] === 'i') {
            $raw = trim(lab57_var($this->w, $t[1]));
            return preg_match('/^-?\d+$/', $raw) === 1 ? (int)$raw : 0;
        }
        if ($t[0] === 'n') return self::number($t[1]);
        throw new RuntimeException('syntax error: operand expected (error token is "' . $t[1] . '")');
    }

    private static function number(string $raw): int
    {
        if (str_contains($raw, '#')) {
            [$base, $digits] = explode('#', $raw, 2);
            $base = (int)$base;
            if ($base < 2 || $base > 36) throw new RuntimeException('invalid arithmetic base (error token is "' . $raw . '")');
            $digits = strtolower($digits);
            if (preg_match('/^[0-9a-z]+$/', $digits) !== 1) throw new RuntimeException('value too great for base (error token is "' . $raw . '")');
            foreach (str_split($digits) as $d) {
                $val = ctype_digit($d) ? (int)$d : ord($d) - 87;
                if ($val >= $base) throw new RuntimeException('value too great for base (error token is "' . $raw . '")');
            }
            return (int)base_convert($digits, $base, 10);
        }
        if (preg_match('/^0[xX]/', $raw) === 1) return (int)hexdec(substr($raw, 2));
        if (strlen($raw) > 1 && $raw[0] === '0') {
            if (preg_match('/^[0-7]+$/', $raw) !== 1) throw new RuntimeException('value too great for base (error token is "' . $raw . '")');
            return (int)octdec($raw);
        }
        return (int)$raw;
    }
}

// ---------------------------------------------------------------------------
// Globy nad virtuálním souborovým systémem
// ---------------------------------------------------------------------------

/** @return list<string> seřazené shody (relativní vzor → relativní cesty) */
function lab57_glob(Lab57World $w, string $pattern): array
{
    $absolute = str_starts_with($pattern, '/');
    $components = array_values(array_filter(explode('/', $pattern), static fn($c) => $c !== ''));
    if ($components === []) return [];
    $results = [[$absolute ? '/' : $w->cwd, $absolute ? '/' : '']];
    $last = count($components) - 1;
    foreach ($components as $k => $component) {
        $next = [];
        $isGlob = lab57_component_has_glob($component);
        foreach ($results as [$dirAbs, $disp]) {
            $sep = ($disp === '' || str_ends_with($disp, '/')) ? '' : '/';
            if (!$isGlob) {
                $literal = preg_replace('/\\\\(.)/s', '$1', $component) ?? $component;
                $abs = Lab57Vfs::normalize($literal, $dirAbs);
                if (!$w->fs->exists($abs)) continue;
                if ($k < $last && !$w->fs->isDir($abs)) continue;
                $next[] = [$abs, $disp . $sep . $literal];
                continue;
            }
            $dirNode = $w->fs->get($dirAbs);
            if ($dirNode === null || ($dirNode['t'] ?? '') !== 'd' || !$w->can($dirNode, 'r')) continue;
            $regex = lab57_component_regex($component);
            foreach ($w->fs->children($dirAbs) as $name) {
                if ($name[0] === '.' && $component[0] !== '.') continue;
                if (preg_match($regex, $name) !== 1) continue;
                $abs = ($dirAbs === '/' ? '' : $dirAbs) . '/' . $name;
                if ($k < $last && !$w->fs->isDir($abs)) continue;
                $next[] = [$abs, $disp . $sep . $name];
            }
        }
        $results = $next;
        if ($results === []) return [];
    }
    $out = array_map(static fn($r) => (string)$r[1], $results);
    usort($out, 'lab57_name_cmp');
    return $out;
}

function lab57_component_has_glob(string $component): bool
{
    $len = strlen($component);
    for ($i = 0; $i < $len; $i++) {
        if ($component[$i] === '\\') { $i++; continue; }
        if (strpos('*?[', $component[$i]) !== false) return true;
    }
    return false;
}

function lab57_component_regex(string $component): string
{
    $out = '';
    $len = strlen($component);
    for ($i = 0; $i < $len; $i++) {
        $ch = $component[$i];
        if ($ch === '\\' && $i + 1 < $len) { $out .= preg_quote($component[$i + 1], '~'); $i++; continue; }
        if ($ch === '*') { $out .= '.*'; continue; }
        if ($ch === '?') { $out .= '.'; continue; }
        if ($ch === '[') {
            $end = strpos($component, ']', $i + 1);
            if ($end !== false) {
                $set = substr($component, $i + 1, $end - $i - 1);
                if ($set !== '' && ($set[0] === '!' || $set[0] === '^')) $set = '^' . substr($set, 1);
                $out .= '[' . str_replace(['\\', '~'], ['\\\\', '\~'], $set) . ']';
                $i = $end;
                continue;
            }
        }
        $out .= preg_quote($ch, '~');
    }
    return '~^' . $out . '$~s';
}

// ---------------------------------------------------------------------------
// Provádění
// ---------------------------------------------------------------------------

function lab57_exec_source(Lab57World $w, string $src, array &$term): int
{
    return lab57_exec_items($w, lab57_parse(lab57_lex($src)), $term);
}

function lab57_exec_items(Lab57World $w, array $items, array &$term): int
{
    $status = $w->lastExit;
    foreach ($items as $item) {
        if (isset($w->effects['exit']) || $w->steps > LAB57_MAX_STEPS) break;
        if ($item['bg']) {
            $term[] = [2, '[1] ' . $w->newPid() . "\n"];
            $w->tip(tr('Úlohy na pozadí (&) simulace spouští hned v popředí.'));
        }
        $status = lab57_exec_andor($w, $item['andor'], $term);
    }
    return $status;
}

function lab57_exec_andor(Lab57World $w, array $andor, array &$term): int
{
    $status = 0;
    foreach ($andor as $idx => $entry) {
        if ($idx > 0 && $entry['op'] === '&&' && $status !== 0) continue;
        if ($idx > 0 && $entry['op'] === '||' && $status === 0) continue;
        $status = lab57_exec_pipeline($w, $entry['pipe'], $term);
        if (isset($w->effects['exit'])) break;
    }
    return $status;
}

function lab57_exec_pipeline(Lab57World $w, array $pipe, array &$term): int
{
    $input = null;
    $status = 0;
    $count = count($pipe['cmds']);
    foreach ($pipe['cmds'] as $i => $cmd) {
        [$status, $captured] = lab57_exec_simple($w, $cmd, $input, $i === $count - 1, $term);
        $input = $captured;
    }
    if ($pipe['neg']) $status = $status === 0 ? 1 : 0;
    $w->lastExit = $status;
    return $status;
}

/** @return array{0:int,1:string} návratový kód a zachycený stdout pro další příkaz v rouře */
function lab57_exec_simple(Lab57World $w, array $cmd, ?string $input, bool $last, array &$term): array
{
    $w->steps++;
    if ($w->steps > LAB57_MAX_STEPS) {
        $term[] = [2, tr('bash: simulace zastavila příliš dlouhý řetězec příkazů') . "\n"];
        return [1, ''];
    }
    $argv = [];
    foreach ($cmd['words'] as $word) foreach (lab57_expand($w, $word) as $field) $argv[] = $field;
    $assigns = [];
    foreach ($cmd['assign'] as [$name, $valueParts]) $assigns[$name] = implode(' ', lab57_expand($w, $valueParts, false));
    foreach ($w->stray as $chunk) $term[] = $chunk;
    $w->stray = [];

    if ($argv !== [] && isset($w->aliases[$argv[0]])) {
        $expanded = preg_split('/\s+/', trim((string)$w->aliases[$argv[0]])) ?: [];
        $argv = array_merge($expanded, array_slice($argv, 1));
    }

    $fd1 = $last ? 'term' : 'pipe';
    $fd2 = 'term';
    $stdin = $input;
    $isSudo = ($argv[0] ?? '') === 'sudo';
    foreach ($cmd['redirs'] as [$op, $targetParts]) {
        if ($op === '2>&1') { $fd2 = $fd1; continue; }
        if ($op === '>&2') { $fd1 = $fd2; continue; }
        $fields = lab57_expand($w, (array)$targetParts);
        foreach ($w->stray as $chunk) $term[] = $chunk;
        $w->stray = [];
        if (count($fields) !== 1) {
            $term[] = [2, 'bash: ' . lab57_word_text($targetParts) . ": ambiguous redirect\n"];
            return [1, ''];
        }
        $target = $fields[0];
        if ($op === '<<<') { $stdin = $target . "\n"; continue; }
        if ($op === '<') {
            $err = null;
            $content = $w->readFile($target, $err);
            if ($content === null) {
                $term[] = [2, "bash: $target: $err\n"];
                lab57_error_tip($w, (string)$err, $target);
                return [1, ''];
            }
            $stdin = $content;
            continue;
        }
        $abs = $w->abs($target);
        $append = in_array($op, ['>>', '2>>', '&>>'], true);
        if ($abs !== '/dev/null') {
            $err = null;
            if (!$w->writeFile($target, '', $append, $err)) {
                $term[] = [2, "bash: $target: $err\n"];
                lab57_error_tip($w, (string)$err, $target);
                if ($isSudo && $err === 'Permission denied') $w->tip(tr('Přesměrování > provádí tvůj shell ještě před sudo, takže nemá práva správce. Použij: echo … | sudo tee {cil}', ['cil' => $target]));
                return [1, ''];
            }
        }
        $dest = $abs === '/dev/null' ? 'null' : 'file:' . $abs;
        if ($op === '>' || $op === '>>') $fd1 = $dest;
        elseif ($op === '2>' || $op === '2>>') $fd2 = $dest;
        else { $fd1 = $dest; $fd2 = $dest; }
    }

    if ($argv === []) {
        foreach ($assigns as $name => $value) $w->env[$name] = $value;
        return [0, ''];
    }

    $p = new Lab57Proc($w);
    $p->stdin = $stdin ?? '';
    $p->hasStdin = $stdin !== null;
    $p->tty = $fd1 === 'term';
    $status = lab57_dispatch($p, $argv);

    $captured = '';
    $files = [];
    foreach ($p->chunks as [$fd, $text]) {
        $dest = $fd === 1 ? $fd1 : $fd2;
        if ($dest === 'term') $term[] = [$fd, $text];
        elseif ($dest === 'pipe') $captured .= $text;
        elseif (str_starts_with($dest, 'file:')) $files[substr($dest, 5)] = ($files[substr($dest, 5)] ?? '') . $text;
    }
    foreach ($files as $path => $text) {
        $err = null;
        $wasRoot = $w->root;
        $w->root = false;
        if (!$w->writeFile((string)$path, $text, true, $err)) $term[] = [2, "bash: $path: $err\n"];
        $w->root = $wasRoot;
    }
    return [$status, $captured];
}

/** Spustí příkaz (vestavěný, z registru, nebo soubor podle cesty). */
function lab57_dispatch(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $name = (string)($argv[0] ?? '');
    $p->name = $name;
    if ($name === '') return 0;
    if (str_contains($name, '/')) return lab57_exec_path($p, $argv);
    $registry = lab57_command_registry();
    if (isset($registry[$name])) {
        $package = lab57_command_package($name);
        if ($package !== null && !isset($w->packages[$package])) {
            $p->err("bash: $name: command not found\n");
            $w->tip(tr('Příkaz {prikaz} není nainstalovaný. Nainstaluješ ho: sudo apt install {balicek}', ['prikaz' => $name, 'balicek' => $package]));
            return 127;
        }
        if (!in_array($name, lab57_builtins(), true) && lab57_which($w, $name) === null) {
            $p->err("bash: $name: command not found\n");
            $w->tip(tr('Program {jmeno} se nenašel v žádné složce z $PATH ({cesta}). Zkontroluj echo $PATH nebo jestli soubor nechybí.', ['jmeno' => $name, 'cesta' => $w->envAll()['PATH'] ?? '']));
            return 127;
        }
        $helpByManual = function_exists('lab58_command_meta') ? (bool)(lab58_command_meta($name)['help'] ?? true) : true;
        if ($helpByManual && in_array('--help', $argv, true) && !in_array($name, ['submit', 'answer', 'echo'], true) && lab57_manual_help($p, $name)) return 0;
        $fn = $registry[$name];
        return (int)$fn($p, array_values(array_map('strval', $argv)));
    }
    $p->err("bash: $name: command not found\n");
    lab57_not_found_tip($w, $name);
    return 127;
}

function lab57_exec_path(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $path = (string)$argv[0];
    $abs = $w->abs($path);
    if (!$w->canTraverse($abs)) { $p->err("bash: $path: Permission denied\n"); return 126; }
    $node = $w->fs->get($abs);
    if ($node === null) {
        $p->err("bash: $path: No such file or directory\n");
        lab57_error_tip($w, 'No such file or directory', $path);
        return 127;
    }
    if (($node['t'] ?? '') === 'd') { $p->err("bash: $path: Is a directory\n"); return 126; }
    if (!$w->can($node, 'x')) {
        $p->err("bash: $path: Permission denied\n");
        $w->tip(tr('Soubor nemá právo ke spuštění (x). Přidáš ho příkazem chmod +x {cesta} – nebo ho spusť přes bash {cesta}.', ['cesta' => $path]));
        return 126;
    }
    $x = (string)($node['x'] ?? '');
    if (str_starts_with($x, 'bin:')) {
        $argv[0] = substr($x, 4);
        return lab57_dispatch($p, $argv);
    }
    if ($x !== '' && isset($w->programs[$x])) return (int)($w->programs[$x])($p, $argv);
    $content = (string)($node['c'] ?? '');
    if (!lab57_is_text($content)) { $p->err("bash: $path: cannot execute binary file: Exec format error\n"); return 126; }
    if (!$w->can($node, 'r')) { $p->err("bash: $path: Permission denied\n"); return 126; }
    return lab57_run_script($p, $content, array_slice($argv, 1), $path);
}

/** Skript = řádky příkazů bez řídicích struktur (if/for/while simulace zatím nepodporuje). */
function lab57_run_script(Lab57Proc $p, string $content, array $args, string $name): int
{
    $w = $p->w;
    if ($w->depth >= 4) { $p->err(tr('bash: {jmeno}: příliš hluboké vnoření skriptů', ['jmeno' => $name]) . "\n"); return 1; }
    $savedArgs = $w->args;
    $w->args = array_values(array_map('strval', $args));
    $w->depth++;
    $status = 0;
    foreach (explode("\n", $content) as $no => $line) {
        $trim = trim($line);
        if ($trim === '' || str_starts_with($trim, '#')) continue;
        if (preg_match('/^(if|then|else|elif|fi|for|while|until|do|done|case|esac|function)\b/', $trim) === 1 || preg_match('/^\w+\s*\(\)\s*\{?$/', $trim) === 1) {
            $p->err(tr('{jmeno}: řádek {cislo}: řídicí struktury (if/for/while) simulace ve skriptech zatím nepodporuje', ['jmeno' => $name, 'cislo' => $no + 1]) . "\n");
            $status = 2;
            break;
        }
        $term = [];
        try {
            $status = lab57_exec_source($w, $trim, $term);
        } catch (Lab57SyntaxError $e) {
            $p->err(tr('{jmeno}: řádek {cislo}: {zprava}', ['jmeno' => $name, 'cislo' => $no + 1, 'zprava' => $e->getMessage()]) . "\n");
            $status = 2;
            break;
        }
        foreach ($term as [$fd, $text]) {
            if ($fd === 1) $p->out($text);
            else $p->err($text);
        }
        if (isset($w->effects['exit'])) {
            $status = (int)$w->effects['exit'];
            unset($w->effects['exit']);
            break;
        }
    }
    $w->depth--;
    $w->args = $savedArgs;
    return $status;
}

/** Spustí vnořený příkaz (např. find -exec) a výstup připojí k rodiči. */
function lab57_subrun(Lab57Proc $parent, array $argv, string $stdin = '', bool $hasStdin = false): int
{
    $sub = new Lab57Proc($parent->w);
    $sub->stdin = $stdin;
    $sub->hasStdin = $hasStdin;
    $sub->tty = $parent->tty;
    $status = lab57_dispatch($sub, $argv);
    foreach ($sub->chunks as $chunk) $parent->chunks[] = $chunk;
    return $status;
}

// ---------------------------------------------------------------------------
// Vstupní bod: jeden řádek z terminálu
// ---------------------------------------------------------------------------

/**
 * @return array{chunks:list<array{0:int,1:string}>,exit:int,echo:?string}
 */
function lab57_run_line(Lab57World $w, string $line): array
{
    $line = str_replace("\r", '', $line);
    if (strlen($line) > LAB57_MAX_LINE) {
        return ['chunks' => [[2, tr('bash: příkaz je příliš dlouhý (nejvýš {n} znaků)', ['n' => LAB57_MAX_LINE]) . "\n"]], 'exit' => 1, 'echo' => null];
    }
    if (trim($line) === '') return ['chunks' => [], 'exit' => $w->lastExit, 'echo' => null];
    $echo = null;
    if (str_contains($line, '!')) {
        $error = null;
        $expanded = lab57_history_expand($w, $line, $error);
        if ($expanded === null) return ['chunks' => [[2, 'bash: ' . $error . ": event not found\n"]], 'exit' => 1, 'echo' => null];
        if ($expanded !== $line) { $echo = $expanded; $line = $expanded; }
    }
    if (!str_starts_with($line, ' ')) {
        $w->history[] = $line;
        if (count($w->history) > 500) $w->history = array_slice($w->history, -500);
    }
    $w->steps = 0;
    $w->tips = [];
    $w->effects = [];
    $w->stray = [];
    $w->depth = 0;
    $w->root = false;
    $oldLimit = (string)ini_get('pcre.backtrack_limit');
    ini_set('pcre.backtrack_limit', '100000');
    $term = [];
    try {
        $status = lab57_exec_source($w, $line, $term);
    } catch (Lab57SyntaxError $e) {
        $term[] = [2, 'bash: ' . $e->getMessage() . "\n"];
        $status = 2;
    } finally {
        ini_set('pcre.backtrack_limit', $oldLimit);
        $w->root = false;
    }
    foreach ($w->stray as $chunk) $term[] = $chunk;
    $w->stray = [];
    unset($w->effects['exit']);
    $w->lastExit = $status;
    $size = 0;
    $limited = [];
    foreach ($term as $chunk) {
        $size += strlen($chunk[1]);
        if ($size > LAB57_MAX_OUTPUT) {
            $limited[] = [2, "\n" . tr('… výstup je příliš dlouhý, simulace ho zkrátila. Zkus | head nebo | less.') . "\n"];
            break;
        }
        $limited[] = $chunk;
    }
    return ['chunks' => $limited, 'exit' => $status, 'echo' => $echo];
}

/** Rozbalí !!, !n, !-n a !prefix podle historie (mimo apostrofy). */
function lab57_history_expand(Lab57World $w, string $line, ?string &$error): ?string
{
    $out = '';
    $n = strlen($line);
    $inSingle = false;
    for ($i = 0; $i < $n; $i++) {
        $c = $line[$i];
        if ($c === "'") { $inSingle = !$inSingle; $out .= $c; continue; }
        if ($c !== '!' || $inSingle || $i + 1 >= $n) { $out .= $c; continue; }
        $next = $line[$i + 1];
        if ($next === '!') {
            $last = end($w->history);
            if ($last === false) { $error = '!!'; return null; }
            $out .= (string)$last;
            $i++;
            continue;
        }
        if (preg_match('/-?\d+/A', $line, $m, 0, $i + 1) === 1) {
            $num = (int)$m[0];
            $idx = $num < 0 ? count($w->history) + $num : $num - 1;
            if (!isset($w->history[$idx])) { $error = '!' . $m[0]; return null; }
            $out .= (string)$w->history[$idx];
            $i += strlen($m[0]);
            continue;
        }
        if (preg_match('/[A-Za-z][A-Za-z0-9_.-]*/A', $line, $m, 0, $i + 1) === 1) {
            $found = null;
            for ($h = count($w->history) - 1; $h >= 0; $h--) {
                if (str_starts_with((string)$w->history[$h], $m[0])) { $found = (string)$w->history[$h]; break; }
            }
            if ($found === null) { $error = '!' . $m[0]; return null; }
            $out .= $found;
            $i += strlen($m[0]);
            continue;
        }
        $out .= $c;
    }
    return $out;
}

function lab57_prompt(Lab57World $w): string
{
    $home = $w->home();
    $cwd = $w->cwd;
    $shown = $cwd === $home ? '~' : (str_starts_with($cwd, $home . '/') ? '~' . substr($cwd, strlen($home)) : $cwd);
    return $w->user . '@' . $w->hostname . ':' . $shown . ($w->user === 'root' ? '# ' : '$ ');
}

// ---------------------------------------------------------------------------
// České vysvětlivky k chybám (nejsou součástí výstupu příkazu)
// ---------------------------------------------------------------------------

function lab57_error_tip(Lab57World $w, string $error, string $path): void
{
    // v58: odkaz „→ man příkaz“ jen na stránky, které Příručka opravdu má
    $manRef = static fn(string $cmd): string => function_exists('v57_manual') && isset(v57_manual()['commands'][$cmd]) ? ' → man ' . $cmd : '';
    $tip = match ($error) {
        'No such file or directory' => tr('Cesta „{cesta}“ neexistuje. Zkontroluj překlep (velká/malá písmena) a podívej se příkazem ls. Tabulátor doplňuje názvy.{manref}', ['cesta' => $path, 'manref' => $manRef('ls')]),
        'Permission denied' => tr('Na „{cesta}“ nemáš oprávnění. Práva uvidíš přes ls -l; správce systému může použít sudo.{manref}', ['cesta' => $path, 'manref' => $manRef('chmod')]),
        'Is a directory' => tr('„{cesta}“ je složka, ne soubor. Do složky vstoupíš příkazem cd, obsah vypíšeš ls.{manref}', ['cesta' => $path, 'manref' => $manRef('cd')]),
        'Not a directory' => tr('Část cesty „{cesta}“ je soubor, ne složka.{manref}', ['cesta' => $path, 'manref' => $manRef('ls')]),
        default => '',
    };
    if ($tip !== '') $w->tip($tip);
}

function lab57_not_found_tip(Lab57World $w, string $name): void
{
    $manRef = static fn(string $cmd): string => function_exists('v57_manual') && isset(v57_manual()['commands'][$cmd]) ? ' → man ' . $cmd : '';
    if (in_array($name, ['vim', 'vi', 'emacs'], true)) {
        $w->tip(tr('Editor {jmeno} v laboratoři není. Použij nano: nano soubor.txt (uložení Ctrl+O, konec Ctrl+X).{manref}', ['jmeno' => $name, 'manref' => $manRef('nano')]));
        return;
    }
    $windows = ['dir' => 'ls', 'cls' => 'clear', 'ipconfig' => 'ip a', 'copy' => 'cp', 'del' => 'rm', 'move' => 'mv', 'ren' => 'mv', 'tracert' => 'traceroute', 'md' => 'mkdir', 'findstr' => 'grep', 'tasklist' => 'ps aux', 'taskkill' => 'kill'];
    if (isset($windows[strtolower($name)])) {
        $linux = $windows[strtolower($name)];
        $w->tip(tr('„{jmeno}“ je příkaz z Windows. V Linuxu použij: {prikaz}{manref}', ['jmeno' => $name, 'prikaz' => $linux, 'manref' => $manRef(explode(' ', $linux)[0])]));
        return;
    }
    if (function_exists('v57_manual')) {
        $manual = (array)(v57_manual()['commands'] ?? []);
        if (isset($manual[$name]) && empty($manual[$name]['in_lab'])) {
            $w->tip(tr('Příkaz {jmeno} v laboratoři není, ale v Příručce najdeš, k čemu slouží.{manref}', ['jmeno' => $name, 'manref' => $manRef($name)]));
            return;
        }
    }
    $suggest = lab57_suggest($name, array_merge(array_keys(lab57_command_registry()), array_keys($w->aliases)));
    if ($suggest !== null) {
        $w->tip(tr('Neznámý příkaz. Nemyslel(a) jsi „{navrh}“?{manref}', ['navrh' => $suggest, 'manref' => $manRef($suggest)]));
        return;
    }
    $w->tip(tr('Tenhle příkaz simulace nezná. Seznam příkazů vypíše help, popis příkazu man <příkaz>.'));
}
