<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – bc: lexer a parser (AST pro Lab58BcVm v linux_v58_cmd_extra_calc.php).
 * Jen data v paměti, žádné eval; chyby jako GNU bc 1.07.1 („(standard_in) N: syntax error“).
 */

// ---------------------------------------------------------------------------
// bc: lexer a parser (rekurzivní sestup, priority jako GNU bc: || && ! relace přiřazení +- */% ^ unární- ++--)
// ---------------------------------------------------------------------------

final class Lab58BcSyntax extends RuntimeException
{
}

final class Lab58BcRuntime extends RuntimeException
{
}

/** @return list<array{0:string,1:string,2:int}>|string tokeny [typ num|name|str|op|nl, text, řádek], nebo chyba „řádek:text“ */
function lab58_bc_lex(string $src): array|string
{
    $t = [];
    $line = 1;
    $n = strlen($src);
    for ($i = 0; $i < $n;) {
        $c = $src[$i];
        if ($c === '\\' && ($src[$i + 1] ?? '') === "\n") { $i += 2; $line++; continue; }
        if ($c === "\n") { $t[] = ['nl', "\n", $line++]; $i++; continue; }
        if ($c === ' ' || $c === "\t" || $c === "\r") { $i++; continue; }
        if ($c === '#') { while ($i < $n && $src[$i] !== "\n") $i++; continue; }
        if ($c === '/' && ($src[$i + 1] ?? '') === '*') {
            $end = strpos($src, '*/', $i + 2);
            if ($end === false) return $line . ':comment';
            $line += substr_count(substr($src, $i, $end - $i), "\n");
            $i = $end + 2;
            continue;
        }
        if ($c === '"') {
            $end = strpos($src, '"', $i + 1);
            if ($end === false) return $line . ':string';
            $t[] = ['str', substr($src, $i + 1, $end - $i - 1), $line];
            $line += substr_count(substr($src, $i, $end - $i), "\n");
            $i = $end + 1;
            continue;
        }
        if (preg_match('/[0-9A-F]+(?:\.[0-9A-F]*)?|\.[0-9A-F]+/A', $src, $m, 0, $i) === 1) { $t[] = ['num', $m[0], $line]; $i += strlen($m[0]); continue; }
        if (preg_match('/[a-z][a-z0-9_]*/A', $src, $m, 0, $i) === 1) { $t[] = ['name', $m[0], $line]; $i += strlen($m[0]); continue; }
        if ($c === '.') { $t[] = ['name', 'last', $line]; $i++; continue; }
        if (preg_match('/\+\+|--|[-+*\/%^]=|==|!=|<=|>=|&&|\|\||[-+*\/%^=<>!(){},;\[\]]/A', $src, $m, 0, $i) === 1) { $t[] = ['op', $m[0], $line]; $i += strlen($m[0]); continue; }
        return $line . ':illegal:' . $c;
    }
    $t[] = ['nl', "\n", $line];
    return $t;
}

final class Lab58BcParser
{
    public int $pos = 0;

    /** @param list<array{0:string,1:string,2:int}> $t */
    public function __construct(public array $t)
    {
    }

    public function peek(int $k = 0): array
    {
        return $this->t[$this->pos + $k] ?? ['eof', '', (int)($this->t[count($this->t) - 1][2] ?? 1)];
    }

    public function is(string $text, string $type = 'op'): bool
    {
        $tok = $this->peek();
        return $tok[0] === $type && $tok[1] === $text;
    }

    public function take(string $text): void
    {
        if (!$this->is($text)) throw new Lab58BcSyntax('syntax error');
        $this->pos++;
    }

    public function skipNl(): void
    {
        while ($this->peek()[0] === 'nl') $this->pos++;
    }

    private function need(?array $node): array
    {
        return $node ?? throw new Lab58BcSyntax('syntax error');
    }

    /** Jeden příkaz, nebo null na konci vstupu. */
    public function statement(): ?array
    {
        while ($this->peek()[0] === 'nl' || $this->is(';')) $this->pos++;
        $tok = $this->peek();
        if ($tok[0] === 'eof') return null;
        if ($tok[0] === 'str') { $this->pos++; return ['str', $tok[1]]; }
        if ($this->is('{')) {
            $this->pos++;
            $body = [];
            while (true) {
                while ($this->peek()[0] === 'nl' || $this->is(';')) $this->pos++;
                if ($this->is('}')) { $this->pos++; return ['block', $body]; }
                $body[] = $this->need($this->statement());
            }
        }
        if ($tok[0] === 'name') {
            switch ($tok[1]) {
                case 'if': case 'while':
                    $this->pos++;
                    $this->take('(');
                    $cond = $this->expr();
                    $this->take(')');
                    $this->skipNl();
                    $body = $this->need($this->statement());
                    if ($tok[1] === 'while') return ['while', $cond, $body];
                    $save = $this->pos;
                    $this->skipNl();
                    if ($this->is('else', 'name')) { $this->pos++; $this->skipNl(); return ['if', $cond, $body, $this->need($this->statement())]; }
                    $this->pos = $save;
                    return ['if', $cond, $body, null];
                case 'for':
                    $this->pos++;
                    $this->take('(');
                    $parts = [];
                    foreach ([';', ';', ')'] as $end) {
                        $parts[] = $this->is($end) ? null : $this->expr();
                        $this->take($end);
                    }
                    $this->skipNl();
                    return ['for', $parts[0], $parts[1], $parts[2], $this->need($this->statement())];
                case 'break': case 'continue': case 'quit': case 'halt':
                    $this->pos++;
                    return [$tok[1]];
                case 'print':
                    $this->pos++;
                    $items = [];
                    do {
                        $item = $this->peek();
                        if ($item[0] === 'str') { $this->pos++; $items[] = ['str', $item[1]]; }
                        else $items[] = $this->expr();
                        $more = $this->is(',');
                        if ($more) $this->pos++;
                    } while ($more);
                    return ['print', $items];
                case 'define': case 'return': case 'auto':
                    throw new Lab58BcSyntax('define');
            }
        }
        return ['expr', $this->expr()];
    }

    public function expr(): array
    {
        $l = $this->andExpr();
        while ($this->is('||')) { $this->pos++; $l = ['or', $l, $this->andExpr()]; }
        return $l;
    }

    public function andExpr(): array
    {
        $l = $this->notExpr();
        while ($this->is('&&')) { $this->pos++; $l = ['and', $l, $this->notExpr()]; }
        return $l;
    }

    public function notExpr(): array
    {
        if ($this->is('!')) { $this->pos++; return ['not', $this->notExpr()]; }
        $l = $this->assign();
        foreach (['<', '<=', '>', '>=', '==', '!='] as $op) {
            if ($this->is($op)) { $this->pos++; return ['rel', $op, $l, $this->assign()]; }
        }
        return $l;
    }

    public function assign(): array
    {
        $tok = $this->peek();
        $next = $this->peek(1);
        if ($tok[0] === 'name' && $next[0] === 'op' && in_array($next[1], ['=', '+=', '-=', '*=', '/=', '%=', '^='], true)) {
            $this->pos += 2;
            return ['assign', $next[1], $tok[1], $this->assign()];
        }
        return $this->additive();
    }

    public function additive(): array
    {
        $l = $this->term();
        while ($this->is('+') || $this->is('-')) { $op = $this->peek()[1]; $this->pos++; $l = ['bin', $op, $l, $this->term()]; }
        return $l;
    }

    public function term(): array
    {
        $l = $this->power();
        while ($this->is('*') || $this->is('/') || $this->is('%')) { $op = $this->peek()[1]; $this->pos++; $l = ['bin', $op, $l, $this->power()]; }
        return $l;
    }

    public function power(): array
    {
        $base = $this->unary();
        if (!$this->is('^')) return $base;
        $this->pos++;
        return ['bin', '^', $base, $this->power()];
    }

    public function unary(): array
    {
        if ($this->is('-')) { $this->pos++; return ['neg', $this->unary()]; }
        if ($this->is('++') || $this->is('--')) {
            $op = $this->peek()[1];
            $this->pos++;
            $name = $this->peek();
            if ($name[0] !== 'name') throw new Lab58BcSyntax('syntax error');
            $this->pos++;
            return ['inc', $op, 'pre', $name[1]];
        }
        $tok = $this->peek();
        $this->pos++;
        if ($tok[0] === 'num') return ['num', $tok[1]];
        if ($tok[0] === 'op' && $tok[1] === '(') { $e = $this->expr(); $this->take(')'); return $e; }
        if ($tok[0] !== 'name' || in_array($tok[1], ['if', 'else', 'while', 'for', 'break', 'continue', 'quit', 'halt', 'print', 'define', 'return', 'auto'], true)) throw new Lab58BcSyntax('syntax error');
        if ($this->is('(')) {
            $this->pos++;
            $args = [];
            if (!$this->is(')')) {
                $args[] = $this->expr();
                while ($this->is(',')) { $this->pos++; $args[] = $this->expr(); }
            }
            $this->take(')');
            return ['call', $tok[1], $args];
        }
        if ($this->is('++') || $this->is('--')) { $op = $this->peek()[1]; $this->pos++; return ['inc', $op, 'post', $tok[1]]; }
        return ['var', $tok[1]];
    }
}
