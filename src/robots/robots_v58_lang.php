<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Robotí liga – mini-jazyk RoboScript (lexer, parser, překlad na PHP closures).
 *
 * Skript žáka se NIKDY nespouští jako PHP: parser z něj postaví strom (pole) a ten se přeloží na
 * uzávěry, které umí jen to, co jazyk povoluje (proměnné, if/elif/else, while, repeat, def, senzory, akce).
 * Každý příkaz a každá obrátka smyčky stojí jeden krok; po ROBOTS58_STEP_BUDGET krocích tah končí.
 *
 * Výjimka z limitu 800 řádků (interpret, viz CLAUDE.md): lexer + parser + překladač tvoří jeden celek,
 * který se testuje dohromady (tools/v58_robots_audit.php); rozdělení by jen přidalo sdílený stav mezi soubory.
 */

const ROBOTS58_MAX_BYTES = 4096;
const ROBOTS58_MAX_LINES = 200;
const ROBOTS58_MAX_DEPTH = 8;
const ROBOTS58_MAX_EXPR_DEPTH = 24;
const ROBOTS58_MAX_VARS = 32;
const ROBOTS58_MAX_KEEP = 16;
const ROBOTS58_MAX_FUNCS = 16;
const ROBOTS58_MAX_PARAMS = 6;
const ROBOTS58_MAX_CALL_DEPTH = 8;
const ROBOTS58_MAX_STR = 100;
const ROBOTS58_MAX_SAY = 40;
const ROBOTS58_MAX_REPEAT = 10000;
const ROBOTS58_STEP_BUDGET = 500;
const ROBOTS58_MAX_NUM = 1000000000;
const ROBOTS58_NAME_MAX = 24;

const ROBOTS58_KEYWORDS = ['if', 'elif', 'else', 'while', 'repeat', 'def', 'return', 'break', 'continue', 'pass', 'keep', 'and', 'or', 'not', 'true', 'false', 'none', 'move', 'step_to', 'pick', 'drop', 'repair', 'charge', 'wait', 'say'];
/** Akce ukončí tah robota; číslo = počet parametrů. */
const ROBOTS58_ACTIONS = ['move' => 1, 'step_to' => 1, 'pick' => 0, 'drop' => 0, 'repair' => 0, 'charge' => 0, 'wait' => 0];
const ROBOTS58_SENSORS = ['pos', 'energy', 'cargo', 'max_cargo', 'turn', 'turns_left', 'here', 'score'];
const ROBOTS58_DIRECTIONS = ['up', 'down', 'left', 'right'];
/** Vestavěné funkce: [min, max] parametrů. */
const ROBOTS58_BUILTINS = ['nearest' => [1, 1], 'distance' => [1, 1], 'look' => [1, 1], 'count' => [1, 1], 'random' => [1, 1], 'abs' => [1, 1], 'min' => [2, 2], 'max' => [2, 2], 'point' => [2, 2]];
/** Časté zvyky z jiných jazyků → tip. */
function robots58_habits(): array
{
    return [
        'print' => tr('Robot mluví příkazem say "text".'), 'for' => tr('Smyčka for tu není – použij repeat N: nebo while podmínka:'),
        'function' => tr('Funkci založíš slovem def: def jmeno():'), 'func' => tr('Funkci založíš slovem def: def jmeno():'),
        'end' => tr('Bloky se neukončují slovem end – stačí přestat odsazovat.'), 'then' => tr('Za podmínku patří dvojtečka, ne then.'),
        'var' => tr('Proměnnou založíš rovnou: x = 1'), 'let' => tr('Proměnnou založíš rovnou: x = 1'), 'len' => tr('Funkce len tu není.'),
        'input' => tr('Robot nečte klávesnici – rozhoduje se podle senzorů (here, look, nearest…).'),
    ];
}

final class Robots58SyntaxError extends RuntimeException
{
    public int $robotLine;
    public string $tip;

    public function __construct(int $line, string $message, string $tip = '')
    {
        parent::__construct($message);
        $this->robotLine = $line;
        $this->tip = $tip;
    }
}

final class Robots58RuntimeError extends RuntimeException
{
    public int $robotLine;
    public string $tip;

    public function __construct(int $line, string $message, string $tip = '')
    {
        parent::__construct($message);
        $this->robotLine = $line;
        $this->tip = $tip;
    }
}

/** Signál „robot provedl akci“ – jedna sdílená instance (bez nákladného sestavování trace). */
final class Robots58Halt extends Exception
{
    public static ?Robots58Halt $one = null;
}

/** Signál „vyčerpaný rozpočet kroků“. */
final class Robots58Budget extends Exception
{
    public static ?Robots58Budget $one = null;
}

/** Svět, který skriptu odpovídá na senzory a světové funkce (nearest, distance, look, count). */
interface Robots58World
{
    public function sense(int $robot, string $name): mixed;

    public function call(int $robot, string $name, array $args, int $line): mixed;
}

/** Běhový stav jednoho robota během tahu. Objekt se mezi tahy znovu používá. */
final class Robots58Ctx
{
    public array $g = [];
    public array $l = [];
    public array $kept = [];
    public array $funcs = [];
    public int $depth = 0;
    public int $steps = 0;
    public int $budget = ROBOTS58_STEP_BUDGET;
    public ?array $action = null;
    public ?string $say = null;
    public mixed $ret = null;
    public int $rng = 1;
    public int $robot = 0;
    public ?Robots58World $world = null;
}

final class Robots58Program
{
    public function __construct(public Closure $main, public array $funcs, public array $meta)
    {
    }
}

// ---------------------------------------------------------------------------
// Lexer
// ---------------------------------------------------------------------------

function robots58_tok_desc(array $tok): string
{
    return match ($tok[0]) {
        'NL' => tr('konec řádku'), 'EOF' => tr('konec skriptu'), 'INDENT' => tr('odsazení'), 'DEDENT' => tr('konec bloku'),
        'NUM' => tr('číslo {n}', ['n' => $tok[1]]), 'STR' => tr('text "{t}"', ['t' => mb_substr((string)$tok[1], 0, 20)]),
        default => tr('„{t}“', ['t' => $tok[1]]),
    };
}

function robots58_lex_fail(string $line, int $pos, int $ln): never
{
    $ch = $line[$pos];
    if ($ch === '"' || $ch === "'") throw new Robots58SyntaxError($ln, tr('Neukončený text – chybí zavírací uvozovka {ch}.', ['ch' => $ch]), tr('Text musí začínat i končit stejnou uvozovkou na jednom řádku: say "Ahoj"'));
    if (ord($ch) >= 128) {
        $char = mb_substr(substr($line, $pos), 0, 1);
        throw new Robots58SyntaxError($ln, tr('Znak „{ch}“ tu nejde použít.', ['ch' => $char]), tr('Názvy a příkazy piš bez diakritiky; český text patří do uvozovek.'));
    }
    $tips = ['!' => tr('Místo ! piš not.'), '&' => tr('Místo && piš and.'), '|' => tr('Místo || piš or.'), ';' => tr('Každý příkaz dej na vlastní řádek, středník není potřeba.'), '{' => tr('Bloky se dělají odsazením pod řádkem s dvojtečkou, ne složenými závorkami.'), '}' => tr('Bloky se dělají odsazením, ne složenými závorkami.'), '[' => tr('Seznamy RoboScript nemá.')];
    throw new Robots58SyntaxError($ln, tr('Neznámý znak „{ch}“.', ['ch' => $ch]), $tips[$ch] ?? tr('Povolené jsou písmena bez diakritiky, čísla, "text" a znaky + - * / % = < > ( ) , : .'));
}

/** @return list<array{0:string,1:mixed,2:int}> tokeny jednoho řádku (bez odsazení a komentáře) */
function robots58_lex_line(string $line, int $ln): array
{
    static $re = '/\G(?:(\d+)|([A-Za-z_][A-Za-z0-9_]*)|"([^"]*)"|\'([^\']*)\'|(==|!=|<=|>=|\+=|-=|[-+*\/%<>=(),:.]))/';
    $toks = [];
    $len = strlen($line);
    $pos = 0;
    while ($pos < $len) {
        $ch = $line[$pos];
        if ($ch === ' ') { $pos++; continue; }
        if ($ch === '#') break;
        if (preg_match($re, $line, $m, PREG_UNMATCHED_AS_NULL, $pos) !== 1) robots58_lex_fail($line, $pos, $ln);
        if ($m[1] !== null) {
            if (isset($line[$pos + strlen($m[1])]) && $line[$pos + strlen($m[1])] === '.' && ctype_digit($line[$pos + strlen($m[1]) + 1] ?? 'x')) {
                throw new Robots58SyntaxError($ln, tr('Desetinná čísla robot nezná.'), tr('Používej celá čísla, třeba 3 místo 2.5.'));
            }
            if (strlen($m[1]) > 10 || (int)$m[1] > ROBOTS58_MAX_NUM) throw new Robots58SyntaxError($ln, tr('Číslo {n} je moc velké.', ['n' => $m[1]]), tr('Největší povolené číslo je 1 000 000 000.'));
            $toks[] = ['NUM', (int)$m[1], $ln];
        } elseif ($m[2] !== null) {
            $word = $m[2];
            if (in_array($word, ROBOTS58_KEYWORDS, true)) { $toks[] = ['KW', $word, $ln]; }
            elseif (in_array(strtolower($word), ROBOTS58_KEYWORDS, true)) { throw new Robots58SyntaxError($ln, tr('Klíčová slova se píšou malými písmeny.'), tr('Napiš {lower} místo {word}.', ['lower' => strtolower($word), 'word' => $word])); }
            elseif (strlen($word) > ROBOTS58_NAME_MAX) { throw new Robots58SyntaxError($ln, tr('Název „{name}…“ je delší než {max} znaků.', ['name' => substr($word, 0, 30), 'max' => ROBOTS58_NAME_MAX]), tr('Zkrať ho – třeba cil místo muj_dlouhy_nazev_cile.')); }
            else { $toks[] = ['NAME', $word, $ln]; }
        } elseif ($m[3] !== null || $m[4] !== null) {
            $text = (string)($m[3] ?? $m[4]);
            if (!mb_check_encoding($text, 'UTF-8')) throw new Robots58SyntaxError($ln, tr('Text obsahuje neplatné znaky.'), tr('Přepiš ho znovu z klávesnice.'));
            if (mb_strlen($text) > ROBOTS58_MAX_STR) throw new Robots58SyntaxError($ln, tr('Text je delší než {max} znaků.', ['max' => ROBOTS58_MAX_STR]), tr('Zkrať ho.'));
            $toks[] = ['STR', $text, $ln];
        } else {
            $toks[] = ['OP', (string)$m[5], $ln];
        }
        $pos += strlen((string)$m[0]);
    }
    return $toks;
}

/** Rozdělí skript na tokeny včetně NL / INDENT / DEDENT (odsazení jako v Pythonu). */
function robots58_lex(string $src): array
{
    if (strlen($src) > ROBOTS58_MAX_BYTES) throw new Robots58SyntaxError(0, tr('Skript má {bytes} B, limit je 4 KB ({max} B).', ['bytes' => strlen($src), 'max' => ROBOTS58_MAX_BYTES]), tr('Zkrať ho – pomůžou funkce (def) a smyčky místo opakovaných řádků.'));
    if (!mb_check_encoding($src, 'UTF-8')) throw new Robots58SyntaxError(0, tr('Skript obsahuje neplatné znaky.'), tr('Vlož ho znovu jako obyčejný text.'));
    $src = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", '    '], $src);
    $lines = explode("\n", $src);
    if (count($lines) > ROBOTS58_MAX_LINES) throw new Robots58SyntaxError(ROBOTS58_MAX_LINES + 1, tr('Skript má víc než {max} řádků.', ['max' => ROBOTS58_MAX_LINES]), tr('Zkrať ho pomocí funkcí a smyček.'));
    $tokens = [];
    $indents = [0];
    $last = 1;
    foreach ($lines as $i => $raw) {
        $ln = $i + 1;
        $lineToks = robots58_lex_line($raw, $ln);
        if ($lineToks === []) continue;
        $last = $ln;
        $indent = strlen($raw) - strlen(ltrim($raw, ' '));
        if ($indent > end($indents)) {
            $indents[] = $indent;
            $tokens[] = ['INDENT', '', $ln];
        } else {
            while ($indent < end($indents)) { array_pop($indents); $tokens[] = ['DEDENT', '', $ln]; }
            if ($indent !== end($indents)) throw new Robots58SyntaxError($ln, tr('Odsazení nesedí s žádným blokem nad tímto řádkem.'), tr('Řádky jednoho bloku musí začínat stejným počtem mezer (doporučujeme 4 = klávesa Tab).'));
        }
        array_push($tokens, ...$lineToks);
        $tokens[] = ['NL', '', $ln];
    }
    while (count($indents) > 1) { array_pop($indents); $tokens[] = ['DEDENT', '', $last]; }
    $tokens[] = ['EOF', '', $last];
    return $tokens;
}

// ---------------------------------------------------------------------------
// Parser (rekurzivní sestup) → strom z polí
// ---------------------------------------------------------------------------

final class Robots58Parser
{
    private int $p = 0;
    private int $blockDepth = 0;
    private int $loops = 0;
    private ?string $func = null;
    private int $exprDepth = 0;
    public array $funcs = [];
    public array $assigned = [];
    public array $kept = [];
    public array $params = [];
    public array $reads = [];
    public array $calls = [];
    public int $actions = 0;

    public function __construct(private array $t)
    {
    }

    private function peek(int $o = 0): array
    {
        return $this->t[$this->p + $o] ?? $this->t[count($this->t) - 1];
    }

    private function next(): array
    {
        $tok = $this->peek();
        $this->p++;
        return $tok;
    }

    private function is(string $type, ?string $value = null, int $o = 0): bool
    {
        $tok = $this->peek($o);
        return $tok[0] === $type && ($value === null || $tok[1] === $value);
    }

    private function accept(string $type, string $value): bool
    {
        if (!$this->is($type, $value)) return false;
        $this->p++;
        return true;
    }

    private function fail(string $message, string $tip = '', ?array $tok = null): never
    {
        throw new Robots58SyntaxError((int)($tok ?? $this->peek())[2], $message, $tip);
    }

    public function program(): array
    {
        $body = [];
        while (!$this->is('EOF')) {
            if ($this->is('INDENT')) $this->fail(tr('Tenhle řádek je odsazený, ale nepatří pod žádné if/while/repeat/def.'), tr('Smaž mezery na začátku řádku, nebo nad něj dej řádek končící dvojtečkou.'));
            $stmt = $this->statement();
            if ($stmt !== null) $body[] = $stmt;
        }
        return $body;
    }

    private function reserved(array $tok): void
    {
        $name = (string)$tok[1];
        if (in_array($name, ROBOTS58_SENSORS, true)) $this->fail(tr('„{name}“ je senzor robota – jde jen číst, ne přepsat.', ['name' => $name]), tr('Vyber pro proměnnou jiný název, třeba moje_{name}.', ['name' => $name]), $tok);
        if (in_array($name, ROBOTS58_DIRECTIONS, true)) $this->fail(tr('„{name}“ je směr (up, down, left, right) – nejde přepsat.', ['name' => $name]), tr('Vyber jiný název proměnné.'), $tok);
        if (isset(ROBOTS58_BUILTINS[$name])) $this->fail(tr('„{name}“ je vestavěná funkce – nejde přepsat.', ['name' => $name]), tr('Vyber jiný název.'), $tok);
    }

    private function end(): void
    {
        if ($this->accept('NL', '')) return;
        $tok = $this->peek();
        $tip = ($tok[0] === 'OP' && $tok[1] === ':') ? tr('Dvojtečka patří jen na konec řádku s if, elif, else, while, repeat nebo def.') : tr('Na jeden řádek patří jeden příkaz.');
        $this->fail(tr('Za příkazem už nic dalšího nečekám, ale je tu {desc}.', ['desc' => robots58_tok_desc($tok)]), $tip);
    }

    private function statement(): ?array
    {
        $tok = $this->peek();
        $line = (int)$tok[2];
        if ($tok[0] === 'KW') {
            switch ($tok[1]) {
                case 'if': return $this->ifStmt();
                case 'while': $this->p++; $cond = $this->expr(); $this->loops++; $body = $this->block('while'); $this->loops--; return ['while', $cond, $body, $line];
                case 'repeat': $this->p++; $n = $this->expr(); $this->loops++; $body = $this->block('repeat'); $this->loops--; return ['repeat', $n, $body, $line];
                case 'def': $this->defStmt(); return null;
                case 'return':
                    $this->p++;
                    if ($this->func === null) $this->fail(tr('return patří jen dovnitř funkce (def).'), tr('Chceš-li skončit tah, použij akci wait.'), $tok);
                    $value = $this->is('NL') ? null : $this->expr();
                    $this->end();
                    return ['return', $value, $line];
                case 'break': case 'continue':
                    $this->p++;
                    if ($this->loops === 0) $this->fail(tr('{kw} patří jen dovnitř smyčky while nebo repeat.', ['kw' => $tok[1]]), '', $tok);
                    $this->end();
                    return [$tok[1], $line];
                case 'pass': $this->p++; $this->end(); return ['pass', $line];
                case 'keep': return $this->keepStmt();
                case 'say': $this->p++; $value = $this->expr(); $this->end(); return ['say', $value, $line];
                case 'elif': case 'else': $this->fail(tr('„{kw}“ tu nemá své if.', ['kw' => $tok[1]]), tr('elif/else musí následovat hned po bloku if a být odsazené stejně jako to if.'));
            }
            if (isset(ROBOTS58_ACTIONS[$tok[1]])) return $this->actionStmt();
            $this->fail(tr('Slovem „{kw}“ nemůže začínat řádek.', ['kw' => $tok[1]]), tr('Řádek začíná příkazem: akcí (move, pick…), if, while, repeat nebo přiřazením x = 1.'));
        }
        if ($tok[0] === 'NAME') {
            $next = $this->peek(1);
            if ($next[0] === 'OP' && in_array($next[1], ['=', '+=', '-='], true)) return $this->assign();
            if ($next[0] === 'OP' && $next[1] === '(') { $value = $this->expr(); $this->end(); return ['expr', $value, $line]; }
            $habit = robots58_habits()[(string)$tok[1]] ?? (robots58_suggest((string)$tok[1], ROBOTS58_KEYWORDS) ?: null);
            $this->fail(tr('Nerozumím řádku začínajícímu „{name}“.', ['name' => $tok[1]]), $habit ?? tr('Chceš-li uložit hodnotu, napiš {name} = …; funkci zavoláš se závorkami: {name}()', ['name' => $tok[1]]));
        }
        $this->fail(tr('Tady čekám příkaz, ale je tu {desc}.', ['desc' => robots58_tok_desc($tok)]), tr('Řádek začíná příkazem (move, if, x = 1 …).'));
    }

    private function block(string $kind): array
    {
        if (!$this->accept('OP', ':')) $this->fail(tr('Chybí dvojtečka „:“ na konci řádku s {kind}.', ['kind' => $kind]), tr('Správně třeba: {example}', ['example' => $kind === 'def' ? 'def jmeno():' : $kind . ' cargo > 0:']));
        if (!$this->is('NL')) {
            $tok = $this->peek();
            if ($tok[0] === 'KW' && in_array($tok[1], ['if', 'while', 'repeat', 'def'], true)) $this->fail(tr('Za dvojtečku na stejném řádku patří jen jednoduchý příkaz.'), tr('Dej {kw} na nový odsazený řádek.', ['kw' => $tok[1]]));
            $stmt = $this->statement();
            return $stmt === null ? [] : [$stmt];
        }
        $this->p++;
        if (!$this->is('INDENT')) $this->fail(tr('Pod řádkem s {kind} chybí odsazený blok.', ['kind' => $kind]), tr('Odsaď další řádek o 4 mezery (klávesa Tab v editoru).'));
        $this->p++;
        if (++$this->blockDepth > ROBOTS58_MAX_DEPTH) $this->fail(tr('Moc hluboké vnoření bloků (max {max}).', ['max' => ROBOTS58_MAX_DEPTH]), tr('Rozděl logiku do funkcí (def).'));
        $body = [];
        while (!$this->is('DEDENT') && !$this->is('EOF')) {
            $stmt = $this->statement();
            if ($stmt !== null) $body[] = $stmt;
        }
        $this->accept('DEDENT', '');
        $this->blockDepth--;
        return $body;
    }

    private function ifStmt(): array
    {
        $line = (int)$this->next()[2];
        $branches = [[$this->expr(), $this->block('if')]];
        $else = null;
        while ($this->is('KW', 'elif')) { $this->p++; $branches[] = [$this->expr(), $this->block('elif')]; }
        if ($this->is('KW', 'else')) {
            $this->p++;
            if ($this->is('KW', 'if')) $this->fail(tr('Místo „else if“ piš „elif“.'), tr('Třeba: elif cargo > 0:'));
            $else = $this->block('else');
        }
        return ['if', $branches, $else, $line];
    }

    private function defStmt(): void
    {
        $tok = $this->next();
        if ($this->blockDepth > 0 || $this->func !== null) $this->fail(tr('Funkci (def) piš na úplný levý okraj, ne dovnitř jiného bloku.'), '', $tok);
        $nameTok = $this->next();
        if ($nameTok[0] !== 'NAME') $this->fail(tr('Za def patří název funkce.'), tr('Třeba: def jdi_domu():'), $nameTok);
        $name = (string)$nameTok[1];
        $this->reserved($nameTok);
        if (isset($this->funcs[$name])) $this->fail(tr('Funkce „{name}“ už existuje.', ['name' => $name]), tr('Každá funkce musí mít jiný název.'), $nameTok);
        if (count($this->funcs) >= ROBOTS58_MAX_FUNCS) $this->fail(tr('Funkcí může být nejvýš {max}.', ['max' => ROBOTS58_MAX_FUNCS]), '', $nameTok);
        if (!$this->accept('OP', '(')) $this->fail(tr('Za názvem funkce chybí závorka „(“.'), tr('Správně: def {name}():', ['name' => $name]));
        $params = [];
        if (!$this->is('OP', ')')) {
            do {
                $param = $this->next();
                if ($param[0] !== 'NAME') $this->fail(tr('Parametr funkce musí být název.'), tr('Třeba: def jdi(smer):'), $param);
                $this->reserved($param);
                if (in_array($param[1], $params, true)) $this->fail(tr('Parametr „{name}“ je tu dvakrát.', ['name' => $param[1]]), '', $param);
                $params[] = (string)$param[1];
            } while ($this->accept('OP', ','));
        }
        if (!$this->accept('OP', ')')) $this->fail(tr('Chybí zavírací závorka „)“ za parametry.'), tr('Správně: def {name}(a, b):', ['name' => $name]));
        if (count($params) > ROBOTS58_MAX_PARAMS) $this->fail(tr('Funkce může mít nejvýš {max} parametrů.', ['max' => ROBOTS58_MAX_PARAMS]), '', $nameTok);
        $this->funcs[$name] = [$params, [], (int)$tok[2]];
        foreach ($params as $param) $this->params[$param] = true;
        $this->func = $name;
        $body = $this->block('def');
        $this->func = null;
        $this->funcs[$name] = [$params, $body, (int)$tok[2]];
    }

    private function keepStmt(): array
    {
        $tok = $this->next();
        if ($this->func !== null) $this->fail(tr('keep patří mimo funkce – paměť robota zakládej v hlavní části skriptu.'), '', $tok);
        $nameTok = $this->next();
        if ($nameTok[0] !== 'NAME') $this->fail(tr('Za keep patří název proměnné.'), tr('Třeba: keep kroky = 0'), $nameTok);
        $this->reserved($nameTok);
        if (!$this->accept('OP', '=')) $this->fail(tr('keep potřebuje počáteční hodnotu.'), tr('Třeba: keep {name} = 0', ['name' => $nameTok[1]]));
        $value = $this->expr();
        $this->end();
        $this->kept[(string)$nameTok[1]] ??= (int)$tok[2];
        $this->assigned[(string)$nameTok[1]] ??= (int)$tok[2];
        return ['keep', (string)$nameTok[1], $value, (int)$tok[2]];
    }

    private function actionStmt(): array
    {
        $tok = $this->next();
        $kind = (string)$tok[1];
        $arg = null;
        if (ROBOTS58_ACTIONS[$kind] === 1) {
            if ($this->is('NL')) $this->fail(tr('{kind} potřebuje cíl.', ['kind' => $kind]), $kind === 'move' ? tr('Třeba: move up (nebo down, left, right).') : tr('Třeba: step_to nearest("packet")'));
            $arg = $this->expr();
        } elseif (!$this->is('NL')) {
            $this->fail(tr('{kind} nemá žádný parametr.', ['kind' => $kind]), tr('Napiš jen {kind} samotné.', ['kind' => $kind]));
        }
        $this->actions++;
        $this->end();
        return ['act', $kind, $arg, (int)$tok[2]];
    }

    private function assign(): array
    {
        $nameTok = $this->next();
        $op = (string)$this->next()[1];
        $this->reserved($nameTok);
        $name = (string)$nameTok[1];
        if (isset($this->funcs[$name])) $this->fail(tr('„{name}“ je název tvé funkce – proměnnou pojmenuj jinak.', ['name' => $name]), '', $nameTok);
        $value = $this->expr();
        $this->end();
        $this->assigned[$name] ??= (int)$nameTok[2];
        if ($op !== '=') $this->reads[$name] ??= (int)$nameTok[2];
        return ['assign', $name, $op, $value, (int)$nameTok[2]];
    }

    public function expr(): array
    {
        if (++$this->exprDepth > ROBOTS58_MAX_EXPR_DEPTH) $this->fail(tr('Výraz je moc složitý (moc vnořených závorek).'), tr('Rozděl ho do pomocných proměnných.'));
        $value = $this->orExpr();
        $this->exprDepth--;
        return $value;
    }

    private function orExpr(): array
    {
        $a = $this->andExpr();
        while ($this->is('KW', 'or')) { $line = (int)$this->next()[2]; $a = ['or', $a, $this->andExpr(), $line]; }
        return $a;
    }

    private function andExpr(): array
    {
        $a = $this->notExpr();
        while ($this->is('KW', 'and')) { $line = (int)$this->next()[2]; $a = ['and', $a, $this->notExpr(), $line]; }
        return $a;
    }

    private function notExpr(): array
    {
        if (!$this->is('KW', 'not')) return $this->cmp();
        $line = (int)$this->next()[2];
        if (++$this->exprDepth > ROBOTS58_MAX_EXPR_DEPTH) $this->fail(tr('Výraz je moc složitý.'), '');
        $inner = $this->notExpr();
        $this->exprDepth--;
        return ['not', $inner, $line];
    }

    private function cmp(): array
    {
        $a = $this->add();
        $tok = $this->peek();
        if ($tok[0] === 'OP' && in_array($tok[1], ['==', '!=', '<', '<=', '>', '>='], true)) {
            $this->p++;
            $a = ['bin', (string)$tok[1], $a, $this->add(), (int)$tok[2]];
            $again = $this->peek();
            if ($again[0] === 'OP' && in_array($again[1], ['==', '!=', '<', '<=', '>', '>='], true)) $this->fail(tr('Porovnání se nedají řetězit (a < b < c).'), tr('Použij and: a < b and b < c'));
        } elseif ($tok[0] === 'OP' && $tok[1] === '=') {
            $this->fail(tr('Jedno = je přiřazení. Pro porovnání piš ==.'), tr('Třeba: if cargo == 3:'));
        }
        return $a;
    }

    private function add(): array
    {
        $a = $this->mul();
        while ($this->is('OP', '+') || $this->is('OP', '-')) { $tok = $this->next(); $a = ['bin', (string)$tok[1], $a, $this->mul(), (int)$tok[2]]; }
        return $a;
    }

    private function mul(): array
    {
        $a = $this->unary();
        while ($this->is('OP', '*') || $this->is('OP', '/') || $this->is('OP', '%')) { $tok = $this->next(); $a = ['bin', (string)$tok[1], $a, $this->unary(), (int)$tok[2]]; }
        return $a;
    }

    private function unary(): array
    {
        if (!$this->is('OP', '-')) return $this->postfix();
        $line = (int)$this->next()[2];
        if (++$this->exprDepth > ROBOTS58_MAX_EXPR_DEPTH) $this->fail(tr('Výraz je moc složitý.'), '');
        $inner = $this->unary();
        $this->exprDepth--;
        return $inner[0] === 'num' ? ['num', -$inner[1]] : ['neg', $inner, $line];
    }

    private function postfix(): array
    {
        $value = $this->primary();
        while ($this->is('OP', '.')) {
            $line = (int)$this->next()[2];
            $attr = $this->next();
            if ($attr[0] !== 'NAME' || !in_array($attr[1], ['x', 'y'], true)) $this->fail(tr('Za tečkou může být jen x nebo y.'), tr('Třeba: pos.x nebo cil.y'), $attr);
            $value = ['attr', $value, (string)$attr[1], $line];
        }
        return $value;
    }

    private function primary(): array
    {
        $tok = $this->next();
        if ($tok[0] === 'NUM') return ['num', $tok[1]];
        if ($tok[0] === 'STR') return ['str', $tok[1]];
        if ($tok[0] === 'KW') {
            if ($tok[1] === 'true' || $tok[1] === 'false') return ['bool', $tok[1] === 'true'];
            if ($tok[1] === 'none') return ['none'];
            if (isset(ROBOTS58_ACTIONS[$tok[1]]) || $tok[1] === 'say') $this->fail(tr('„{kw}“ je akce, ne hodnota – patří na samostatný řádek.', ['kw' => $tok[1]]), '', $tok);
        }
        if ($tok[0] === 'NAME') {
            $name = (string)$tok[1];
            if ($this->is('OP', '(')) return $this->call($tok);
            if (in_array($name, ROBOTS58_SENSORS, true)) return ['sensor', $name];
            if (in_array($name, ROBOTS58_DIRECTIONS, true)) return ['str', $name];
            $this->reads[$name] ??= (int)$tok[2];
            return ['name', $name, (int)$tok[2]];
        }
        if ($tok[0] === 'OP' && $tok[1] === '(') {
            $value = $this->expr();
            if (!$this->accept('OP', ')')) $this->fail(tr('Chybí zavírací závorka „)“.'), tr('Každá „(“ potřebuje svou „)“.'));
            return $value;
        }
        $this->fail(tr('Tady čekám hodnotu (číslo, "text", proměnnou…), ale je tu {desc}.', ['desc' => robots58_tok_desc($tok)]), '', $tok);
    }

    private function call(array $tok): array
    {
        $this->p++;
        $args = [];
        if (!$this->is('OP', ')')) {
            do { $args[] = $this->expr(); } while ($this->accept('OP', ','));
        }
        if (!$this->accept('OP', ')')) $this->fail(tr('Chybí zavírací závorka „)“ za parametry funkce {name}.', ['name' => $tok[1]]), tr('Parametry odděl čárkou: max(a, b)'));
        $this->calls[] = [(string)$tok[1], count($args), (int)$tok[2]];
        return ['call', (string)$tok[1], $args, (int)$tok[2]];
    }
}

// ---------------------------------------------------------------------------
// Kontroly po parsování a veřejné API
// ---------------------------------------------------------------------------

function robots58_suggest(string $name, array $candidates): string
{
    $best = '';
    $bestDistance = 3;
    foreach ($candidates as $candidate) {
        $candidate = (string)$candidate;
        $distance = levenshtein(strtolower($name), strtolower($candidate));
        if ($distance < $bestDistance && $distance < strlen($name)) { $bestDistance = $distance; $best = $candidate; }
    }
    return $best !== '' ? tr('Nemyslel(a) jsi „{name}“?', ['name' => $best]) : '';
}

function robots58_check(Robots58Parser $p): array
{
    $warnings = [];
    foreach ($p->calls as [$name, $argc, $line]) {
        if (isset(ROBOTS58_BUILTINS[$name])) {
            [$min, $max] = ROBOTS58_BUILTINS[$name];
            if ($argc < $min || $argc > $max) throw new Robots58SyntaxError($line, tr('Funkce {name} chce {range} parametr(y), dostala {argc}.', ['name' => $name, 'range' => $min . ($max !== $min ? '–' . $max : ''), 'argc' => $argc]), tr('Podívej se do taháku jazyka.'));
        } elseif (isset($p->funcs[$name])) {
            if (count($p->funcs[$name][0]) !== $argc) throw new Robots58SyntaxError($line, tr('Tvoje funkce {name} má {want} parametr(ů), zavolal(a) jsi ji s {argc}.', ['name' => $name, 'want' => count($p->funcs[$name][0]), 'argc' => $argc]), '');
        } else {
            $tip = robots58_habits()[$name] ?? robots58_suggest($name, array_merge(array_keys(ROBOTS58_BUILTINS), array_keys($p->funcs)));
            throw new Robots58SyntaxError($line, tr('Neznám funkci „{name}“.', ['name' => $name]), $tip !== '' ? $tip : tr('Funkci nejdřív založ: def {name}():', ['name' => $name]));
        }
    }
    $known = $p->assigned + $p->params;
    foreach ($p->reads as $name => $line) {
        if (isset($known[$name])) continue;
        if (isset($p->funcs[$name])) throw new Robots58SyntaxError($line, tr('„{name}“ je funkce – volej ji se závorkami: {name}()', ['name' => $name]), '');
        $tip = robots58_suggest($name, array_merge(array_keys($known), ROBOTS58_SENSORS, ROBOTS58_DIRECTIONS));
        throw new Robots58SyntaxError($line, tr('Neznám jméno „{name}“.', ['name' => $name]), $tip !== '' ? $tip : tr('Proměnnou nejdřív naplň: {name} = 0. Text dej do uvozovek: "{name}"', ['name' => $name]));
    }
    if (count($known) > ROBOTS58_MAX_VARS) throw new Robots58SyntaxError(1, tr('Skript používá {n} proměnných, limit je {max}.', ['n' => count($known), 'max' => ROBOTS58_MAX_VARS]), tr('Používej méně pomocných proměnných.'));
    if (count($p->kept) > ROBOTS58_MAX_KEEP) throw new Robots58SyntaxError((int)array_values($p->kept)[ROBOTS58_MAX_KEEP], tr('Paměť robota pojme nejvýš {max} proměnných keep.', ['max' => ROBOTS58_MAX_KEEP]), '');
    if ($p->actions === 0) $warnings[] = ['line' => 1, 'message' => tr('Skript neobsahuje žádnou akci (move, step_to, pick…) – robot bude jen stát.'), 'tip' => tr('Přidej aspoň jednu akci, třeba move right.')];
    return $warnings;
}

/**
 * Zkontroluje a přeloží skript.
 * @return array{ok:bool, error:?array, warnings:list<array>, program:?Robots58Program, stats:array}
 */
function robots58_parse(string $src, bool $compile = true): array
{
    $stats = ['bytes' => strlen($src), 'lines' => substr_count($src, "\n") + 1];
    try {
        $parser = new Robots58Parser(robots58_lex($src));
        $body = $parser->program();
        $warnings = robots58_check($parser);
        $meta = ['funcs' => count($parser->funcs), 'vars' => count($parser->assigned + $parser->params), 'keep' => count($parser->kept)];
        $program = $compile ? robots58_compile($body, $parser->funcs, $meta) : null;
        return ['ok' => true, 'error' => null, 'warnings' => $warnings, 'program' => $program, 'stats' => $stats + $meta];
    } catch (Robots58SyntaxError $e) {
        return ['ok' => false, 'error' => ['line' => $e->robotLine, 'message' => $e->getMessage(), 'tip' => $e->tip], 'warnings' => [], 'program' => null, 'stats' => $stats];
    }
}

/** Chybová hláška pro člověka: „Řádek 3: … Tip: …“. */
function robots58_error_text(array $error): string
{
    $line = (int)($error['line'] ?? 0);
    $text = (string)$error['message'];
    if ($line > 0) { $text = tr('Řádek {line}: {text}', ['line' => $line, 'text' => $text]); }
    if (($error['tip'] ?? '') !== '') { $text = tr('{text} Tip: {tip}', ['text' => $text, 'tip' => $error['tip']]); }
    return $text;
}

// ---------------------------------------------------------------------------
// Hodnoty a operátory
// ---------------------------------------------------------------------------

function robots58_truthy(mixed $v): bool
{
    return $v !== null && $v !== false && $v !== 0 && $v !== '';
}

function robots58_text(mixed $v): string
{
    if (is_string($v)) return $v;
    if (is_bool($v)) return $v ? 'true' : 'false';
    if ($v === null) return 'none';
    if (is_array($v)) return '(' . (int)$v[0] . ', ' . (int)$v[1] . ')';
    return (string)$v;
}

function robots58_int(mixed $v, int $line, string $what): int
{
    if (!is_int($v)) throw new Robots58RuntimeError($line, tr('{what} jde jen s čísly, dostal jsem {type}.', ['what' => $what, 'type' => robots58_type($v)]), tr('Zkontroluj, co je v proměnných (senzor here je text, pos je pozice).'));
    return $v;
}

function robots58_type(mixed $v): string
{
    return match (true) { is_int($v) => tr('číslo'), is_string($v) => tr('text'), is_bool($v) => 'true/false', is_array($v) => tr('pozici'), default => 'none' };
}

function robots58_num(int|float $r, int $line): int
{
    if (!is_int($r) || $r > ROBOTS58_MAX_NUM || $r < -ROBOTS58_MAX_NUM) throw new Robots58RuntimeError($line, tr('Výsledek je moc velké číslo (limit ±1 000 000 000).'), '');
    return $r;
}

function robots58_c_bin(string $op, Closure $a, Closure $b, int $line): Closure
{
    switch ($op) {
        case '+':
            return static function (Robots58Ctx $c) use ($a, $b, $line) {
                $x = $a($c);
                $y = $b($c);
                if (is_int($x) && is_int($y)) return robots58_num($x + $y, $line);
                if (!is_string($x) && !is_string($y)) throw new Robots58RuntimeError($line, tr('Sčítat jde čísla, nebo text s něčím (spojení textu).'), '');
                $s = robots58_text($x) . robots58_text($y);
                if (mb_strlen($s) > ROBOTS58_MAX_STR) throw new Robots58RuntimeError($line, tr('Text je delší než {max} znaků.', ['max' => ROBOTS58_MAX_STR]), '');
                return $s;
            };
        case '-': return static fn(Robots58Ctx $c) => robots58_num(robots58_int($a($c), $line, tr('Odčítat')) - robots58_int($b($c), $line, tr('Odčítat')), $line);
        case '*': return static fn(Robots58Ctx $c) => robots58_num(robots58_int($a($c), $line, tr('Násobit')) * robots58_int($b($c), $line, tr('Násobit')), $line);
        case '/': case '%':
            return static function (Robots58Ctx $c) use ($a, $b, $line, $op): int {
                $x = robots58_int($a($c), $line, tr('Dělit'));
                $y = robots58_int($b($c), $line, tr('Dělit'));
                if ($y === 0) throw new Robots58RuntimeError($line, tr('Dělení nulou.'), tr('Před dělením zkontroluj, že dělitel není 0.'));
                if ($op === '/') return intdiv($x, $y) - ((($x % $y) !== 0 && (($x < 0) !== ($y < 0))) ? 1 : 0);
                $r = $x % $y;
                return ($r !== 0 && (($r < 0) !== ($y < 0))) ? $r + $y : $r;
            };
        case '==': return static fn(Robots58Ctx $c): bool => $a($c) === $b($c);
        case '!=': return static fn(Robots58Ctx $c): bool => $a($c) !== $b($c);
        case '<': return static fn(Robots58Ctx $c): bool => robots58_int($a($c), $line, tr('Porovnávat < >')) < robots58_int($b($c), $line, tr('Porovnávat < >'));
        case '<=': return static fn(Robots58Ctx $c): bool => robots58_int($a($c), $line, tr('Porovnávat < >')) <= robots58_int($b($c), $line, tr('Porovnávat < >'));
        case '>': return static fn(Robots58Ctx $c): bool => robots58_int($a($c), $line, tr('Porovnávat < >')) > robots58_int($b($c), $line, tr('Porovnávat < >'));
        default: return static fn(Robots58Ctx $c): bool => robots58_int($a($c), $line, tr('Porovnávat < >')) >= robots58_int($b($c), $line, tr('Porovnávat < >'));
    }
}

function robots58_builtin(string $name, array $v, Robots58Ctx $c, int $line): mixed
{
    switch ($name) {
        case 'abs': return abs(robots58_int($v[0], $line, 'abs'));
        case 'min': return min(robots58_int($v[0], $line, 'min'), robots58_int($v[1], $line, 'min'));
        case 'max': return max(robots58_int($v[0], $line, 'max'), robots58_int($v[1], $line, 'max'));
        case 'point': return [robots58_int($v[0], $line, 'point'), robots58_int($v[1], $line, 'point')];
        case 'random':
            $n = robots58_int($v[0], $line, 'random');
            if ($n < 1 || $n > 1000000) throw new Robots58RuntimeError($line, tr('random(n) chce n od 1 do 1 000 000.'), tr('random(4) vrátí 0, 1, 2 nebo 3.'));
            $s = $c->rng;
            $s ^= ($s << 13) & 0xFFFFFFFF; $s ^= $s >> 17; $s ^= ($s << 5) & 0xFFFFFFFF;
            $c->rng = $s & 0xFFFFFFFF;
            return $c->rng % $n;
        default:
            if ($c->world === null) throw new Robots58RuntimeError($line, tr('Senzory tu nejsou k dispozici.'), '');
            return $c->world->call($c->robot, $name, $v, $line);
    }
}

// ---------------------------------------------------------------------------
// Překlad stromu na uzávěry
// ---------------------------------------------------------------------------

function robots58_c_expr(array $n): Closure
{
    switch ($n[0]) {
        case 'num': case 'str': case 'bool': $v = $n[1]; return static fn(Robots58Ctx $c) => $v;
        case 'none': return static fn(Robots58Ctx $c) => null;
        case 'sensor': $name = $n[1]; return static fn(Robots58Ctx $c) => $c->world === null ? null : $c->world->sense($c->robot, $name);
        case 'name':
            [, $name, $line] = $n;
            return static function (Robots58Ctx $c) use ($name, $line) {
                if ($c->depth > 0 && array_key_exists($name, $c->l)) return $c->l[$name];
                if (array_key_exists($name, $c->g)) return $c->g[$name];
                throw new Robots58RuntimeError($line, tr('Proměnná „{name}“ ještě nemá hodnotu.', ['name' => $name]), tr('Přiřaď jí hodnotu dřív, než ji použiješ (třeba {name} = 0), nebo použij keep.', ['name' => $name]));
            };
        case 'and': $a = robots58_c_expr($n[1]); $b = robots58_c_expr($n[2]); return static fn(Robots58Ctx $c): bool => robots58_truthy($a($c)) && robots58_truthy($b($c));
        case 'or': $a = robots58_c_expr($n[1]); $b = robots58_c_expr($n[2]); return static fn(Robots58Ctx $c): bool => robots58_truthy($a($c)) || robots58_truthy($b($c));
        case 'not': $a = robots58_c_expr($n[1]); return static fn(Robots58Ctx $c): bool => !robots58_truthy($a($c));
        case 'neg': $a = robots58_c_expr($n[1]); $line = $n[2]; return static fn(Robots58Ctx $c): int => -robots58_int($a($c), $line, tr('Minus'));
        case 'bin': return robots58_c_bin($n[1], robots58_c_expr($n[2]), robots58_c_expr($n[3]), $n[4]);
        case 'attr':
            $a = robots58_c_expr($n[1]);
            $i = $n[2] === 'x' ? 0 : 1;
            $line = $n[3];
            return static function (Robots58Ctx $c) use ($a, $i, $line): int {
                $v = $a($c);
                if (!is_array($v)) throw new Robots58RuntimeError($line, tr('Hodnota {type} nemá .x ani .y.', ['type' => robots58_type($v)]), tr('Jen pozice (pos, nearest(...), point(x, y)) má .x a .y. Zkontroluj, že nearest nevrátil none.'));
                return $v[$i];
            };
        case 'call': return robots58_c_call($n[1], array_map('robots58_c_expr', $n[2]), $n[3]);
    }
    throw new LogicException('Neznámý uzel výrazu.');
}

function robots58_c_call(string $name, array $args, int $line): Closure
{
    if (isset(ROBOTS58_BUILTINS[$name])) {
        return static function (Robots58Ctx $c) use ($name, $args, $line) {
            $v = [];
            foreach ($args as $arg) $v[] = $arg($c);
            return robots58_builtin($name, $v, $c, $line);
        };
    }
    return static function (Robots58Ctx $c) use ($name, $args, $line) {
        if ($c->depth >= ROBOTS58_MAX_CALL_DEPTH) throw new Robots58RuntimeError($line, tr('Funkce se volají moc hluboko (max {max} do sebe).', ['max' => ROBOTS58_MAX_CALL_DEPTH]), tr('Funkce nejspíš volá sama sebe bez konce – přidej podmínku, kdy má přestat.'));
        if (++$c->steps > $c->budget) throw Robots58Budget::$one;
        [$params, $body] = $c->funcs[$name];
        $locals = [];
        foreach ($args as $i => $arg) $locals[$params[$i]] = $arg($c);
        $saved = $c->l;
        $c->l = $locals;
        $c->depth++;
        $sig = $body($c);
        $c->depth--;
        $c->l = $saved;
        if ($sig !== 3) return null;
        $value = $c->ret;
        $c->ret = null;
        return $value;
    };
}

/** Blok: vrací signál 0 = dál, 1 = break, 2 = continue, 3 = return. */
function robots58_c_block(array $stmts): Closure
{
    $cs = array_map('robots58_c_stmt', $stmts);
    if (count($cs) === 1) return $cs[0];
    return static function (Robots58Ctx $c) use ($cs): int {
        foreach ($cs as $stmt) {
            $sig = $stmt($c);
            if ($sig !== 0) return $sig;
        }
        return 0;
    };
}

function robots58_c_stmt(array $n): Closure
{
    switch ($n[0]) {
        case 'assign': return robots58_c_assign($n[1], $n[2], robots58_c_expr($n[3]), $n[4]);
        case 'keep':
            $name = $n[1];
            $e = robots58_c_expr($n[2]);
            return static function (Robots58Ctx $c) use ($name, $e): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                if (!isset($c->kept[$name])) { $c->g[$name] = $e($c); $c->kept[$name] = true; }
                return 0;
            };
        case 'if':
            $branches = [];
            foreach ($n[1] as [$cond, $body]) $branches[] = [robots58_c_expr($cond), robots58_c_block($body)];
            $else = $n[2] === null ? null : robots58_c_block($n[2]);
            return static function (Robots58Ctx $c) use ($branches, $else): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                foreach ($branches as [$cond, $body]) {
                    if (robots58_truthy($cond($c))) return $body($c);
                }
                return $else === null ? 0 : $else($c);
            };
        case 'while':
            $cond = robots58_c_expr($n[1]);
            $body = robots58_c_block($n[2]);
            return static function (Robots58Ctx $c) use ($cond, $body): int {
                while (true) {
                    if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                    if (!robots58_truthy($cond($c))) return 0;
                    $sig = $body($c);
                    if ($sig === 1) return 0;
                    if ($sig === 3) return 3;
                }
            };
        case 'repeat':
            $count = robots58_c_expr($n[1]);
            $body = robots58_c_block($n[2]);
            $line = $n[3];
            return static function (Robots58Ctx $c) use ($count, $body, $line): int {
                $times = $count($c);
                if (!is_int($times) || $times < 0 || $times > ROBOTS58_MAX_REPEAT) throw new Robots58RuntimeError($line, tr('repeat chce celé číslo od 0 do {max}.', ['max' => ROBOTS58_MAX_REPEAT]), tr('Třeba: repeat 3:'));
                for ($i = 0; $i < $times; $i++) {
                    if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                    $sig = $body($c);
                    if ($sig === 1) return 0;
                    if ($sig === 3) return 3;
                }
                return 0;
            };
        case 'return':
            $e = $n[1] === null ? null : robots58_c_expr($n[1]);
            return static function (Robots58Ctx $c) use ($e): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                $c->ret = $e === null ? null : $e($c);
                return 3;
            };
        case 'break': case 'continue':
            $sig = $n[0] === 'break' ? 1 : 2;
            return static function (Robots58Ctx $c) use ($sig): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                return $sig;
            };
        case 'pass': return static function (Robots58Ctx $c): int { if (++$c->steps > $c->budget) throw Robots58Budget::$one; return 0; };
        case 'say':
            $e = robots58_c_expr($n[1]);
            return static function (Robots58Ctx $c) use ($e): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                $c->say = mb_substr(robots58_text($e($c)), 0, ROBOTS58_MAX_SAY);
                return 0;
            };
        case 'act':
            $kind = $n[1];
            $arg = $n[2] === null ? null : robots58_c_expr($n[2]);
            $line = $n[3];
            return static function (Robots58Ctx $c) use ($kind, $arg, $line): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                $value = $arg === null ? null : $arg($c);
                if ($kind === 'move' && !in_array($value, ROBOTS58_DIRECTIONS, true)) throw new Robots58RuntimeError($line, tr('move čeká směr up, down, left nebo right, dostal {type}.', ['type' => robots58_type($value)]), tr('Třeba: move up'));
                if ($kind === 'step_to' && $value !== null && !is_array($value)) throw new Robots58RuntimeError($line, tr('step_to čeká pozici, dostal {type}.', ['type' => robots58_type($value)]), tr('Třeba: step_to nearest("packet") nebo step_to point(5, 3)'));
                $c->action = [$kind, $value, $line];
                throw Robots58Halt::$one;
            };
        case 'expr':
            $e = robots58_c_expr($n[1]);
            return static function (Robots58Ctx $c) use ($e): int {
                if (++$c->steps > $c->budget) throw Robots58Budget::$one;
                $e($c);
                return 0;
            };
    }
    throw new LogicException('Neznámý uzel příkazu.');
}

function robots58_c_assign(string $name, string $op, Closure $e, int $line): Closure
{
    return static function (Robots58Ctx $c) use ($name, $op, $e, $line): int {
        if (++$c->steps > $c->budget) throw Robots58Budget::$one;
        $value = $e($c);
        $local = $c->depth > 0 && !isset($c->kept[$name]);
        if ($op !== '=') {
            $old = $local ? ($c->l[$name] ?? null) : ($c->g[$name] ?? null);
            if (!is_int($old) || !is_int($value)) throw new Robots58RuntimeError($line, tr('{op} jde jen s čísly (a proměnná už musí mít hodnotu).', ['op' => $op]), tr('Třeba: kroky = 0 a potom kroky += 1'));
            $value = robots58_num($op === '+=' ? $old + $value : $old - $value, $line);
        }
        if ($local) $c->l[$name] = $value; else $c->g[$name] = $value;
        return 0;
    };
}

function robots58_compile(array $body, array $funcs, array $meta): Robots58Program
{
    Robots58Halt::$one ??= new Robots58Halt('akce');
    Robots58Budget::$one ??= new Robots58Budget('rozpočet');
    $compiled = [];
    foreach ($funcs as $name => [$params, $fbody]) $compiled[$name] = [$params, robots58_c_block($fbody)];
    return new Robots58Program(robots58_c_block($body), $compiled, $meta);
}

/**
 * Jeden tah skriptu: běží od začátku, dokud robot neprovede akci nebo nevyčerpá kroky.
 * @return array{status:string, action:?array, error:?array}  status: action | end | budget | error
 */
function robots58_run_turn(Robots58Program $prog, Robots58Ctx $c): array
{
    $c->funcs = $prog->funcs;
    $c->l = [];
    $c->depth = 0;
    $c->steps = 0;
    $c->action = null;
    $c->say = null;
    $c->ret = null;
    try {
        ($prog->main)($c);
        return ['status' => 'end', 'action' => null, 'error' => null];
    } catch (Robots58Halt $h) {
        return ['status' => 'action', 'action' => $c->action, 'error' => null];
    } catch (Robots58Budget $b) {
        return ['status' => 'budget', 'action' => null, 'error' => ['line' => 0, 'message' => tr('Skript se nezastavil do {budget} kroků – robot tenhle tah čeká.', ['budget' => $c->budget]), 'tip' => tr('Nemáš nekonečnou smyčku? Každá smyčka musí jednou skončit nebo provést akci.')]];
    } catch (Robots58RuntimeError $e) {
        return ['status' => 'error', 'action' => null, 'error' => ['line' => $e->robotLine, 'message' => $e->getMessage(), 'tip' => $e->tip]];
    }
}
