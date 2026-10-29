<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Robotí liga – svět, pravidla, simulace a záznam (replay).
 *
 * Datacentrum 24 × 16: regály (#), 4 základny v rozích (2 × 2), 4 nabíječky, 16 míst pro datové balíčky
 * a 4 uzly, které se občas rozbijí. Mapa se generuje ze semínka po čtvrtinách a zrcadlí se podle obou os,
 * takže všechny rohy jsou si rovné. Pořadí robotů se každý tah promíchá ze semínka zápasu.
 * Roboti se nemohou poškodit ani krást: soutěží se sběrem, opravami a efektivitou.
 * Nic se tu nespouští mimo vlastní interpret RoboScriptu (robots_v58_lang.php).
 */

require_once __DIR__ . '/robots_v58_lang.php';

const ROBOTS58_W = 24;
const ROBOTS58_H = 16;
const ROBOTS58_ENERGY_MAX = 100;
const ROBOTS58_CARGO_MAX = 3;
const ROBOTS58_CHARGE = 20;
const ROBOTS58_REPAIR_COST = 2;
const ROBOTS58_PTS_PACKET = 3;
const ROBOTS58_PTS_REPAIR = 2;
const ROBOTS58_NODE_HP = 3;
const ROBOTS58_PACKET_RESPAWN = 30;
const ROBOTS58_GHOST_AFTER = 3;
const ROBOTS58_LOW_ENERGY = 5;
const ROBOTS58_SAY_TURNS = 4;
const ROBOTS58_KEYFRAME = 50;
const ROBOTS58_MAX_ISSUES = 12;
/** Přehřátí: po 3 vyčerpaných rozpočtech za sebou robot 10 tahů chladne (skript neběží). */
const ROBOTS58_OVERHEAT_AFTER = 3;
const ROBOTS58_COOLDOWN = 10;
const ROBOTS58_TURNS_MAX_SIM = 500;
/** Kódy akcí v záznamu (stejné čte assets/robots-v58.js). */
const ROBOTS58_ACT = ['wait' => 0, 'move' => 1, 'blocked' => 2, 'pick' => 3, 'deliver' => 4, 'repair' => 5, 'charge' => 6, 'error' => 7, 'budget' => 8, 'empty' => 9, 'fail' => 10, 'fixed' => 11];
/** Pořadí směrů pro volbu kroku v „rámci“ rohu – zrcadlení drží stejné rozhodnutí v zrcadlených rozích. */
const ROBOTS58_DIR_ORDER = [['up', 'left', 'down', 'right'], ['up', 'right', 'down', 'left'], ['down', 'left', 'up', 'right'], ['down', 'right', 'up', 'left']];
const ROBOTS58_BAD_PREFIX = ['kurv', 'prdel', 'hovn', 'srac', 'kokot', 'curak', 'debil', 'idiot', 'kreten', 'mrd', 'zmrd', 'jeb', 'vyjeb', 'buzn', 'fuck', 'shit', 'bitch', 'cunt', 'nigg', 'fag', 'retard', 'whore', 'negr', 'cigos', 'picus', 'picovin'];
const ROBOTS58_BAD_EXACT = ['pica', 'pice', 'pici', 'picu', 'pico', 'kys'];
const ROBOTS58_BAD_JOINED = ['kurva', 'kokot', 'zmrd', 'fuck', 'cunt', 'nigg', 'jebat', 'picus'];

// ---------------------------------------------------------------------------
// Deterministická náhoda (xorshift32) – stejné semínko = stejný zápas na PHP 8.1 i 8.4
// ---------------------------------------------------------------------------

function robots58_seed_of(string $text): int
{
    $v = (int)hexdec(substr(hash('sha256', $text), 0, 8));
    return $v === 0 ? 0x9E3779B9 : $v;
}

function robots58_next(int &$s): int
{
    $s ^= ($s << 13) & 0xFFFFFFFF;
    $s ^= $s >> 17;
    $s ^= ($s << 5) & 0xFFFFFFFF;
    $s &= 0xFFFFFFFF;
    if ($s === 0) $s = 0x9E3779B9;
    return $s;
}

function robots58_rand(int &$s, int $n): int
{
    return robots58_next($s) % max(1, $n);
}

function robots58_shuffle(array $items, int $seed): array
{
    $s = $seed;
    for ($i = count($items) - 1; $i > 0; $i--) {
        $j = robots58_rand($s, $i + 1);
        [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
    }
    return $items;
}

// ---------------------------------------------------------------------------
// Mapa
// ---------------------------------------------------------------------------

/** @return array{grid:string, packets:list<int>, nodes:list<int>} */
function robots58_map(int $seed): array
{
    for ($attempt = 0; $attempt < 60; $attempt++) {
        $map = robots58_try_map(robots58_seed_of('map:' . $seed . ':' . $attempt), $attempt < 59);
        if ($map !== null) return $map;
    }
    throw new RuntimeException(tr('Mapu se nepodařilo vytvořit.'));
}

function robots58_try_map(int $s, bool $walls): ?array
{
    $q = array_fill(0, 8, array_fill(0, 12, '.'));
    $protected = static fn(int $x, int $y): bool => $x <= 3 && $y <= 3;
    for ($y = 0; $y < 2; $y++) for ($x = 0; $x < 2; $x++) $q[$y][$x] = 'B';
    $q[2 + robots58_rand($s, 5)][4 + robots58_rand($s, 6)] = '+';
    $segments = $walls ? 6 + robots58_rand($s, 3) : 0;
    for ($k = 0, $placed = 0; $k < 40 && $placed < $segments; $k++) {
        $horizontal = robots58_rand($s, 2) === 0;
        $len = 2 + robots58_rand($s, 3);
        $x0 = 2 + robots58_rand($s, 10);
        $y0 = 1 + robots58_rand($s, 7);
        $cells = [];
        for ($i = 0; $i < $len; $i++) $cells[] = $horizontal ? [$x0 + $i, $y0] : [$x0, $y0 + $i];
        $ok = true;
        foreach ($cells as [$x, $y]) { if ($x > 11 || $y > 7 || $protected($x, $y) || $q[$y][$x] !== '.') { $ok = false; break; } }
        if (!$ok) continue;
        foreach ($cells as [$x, $y]) $q[$y][$x] = '#';
        $placed++;
    }
    $slots = [];
    $node = null;
    for ($tries = 0; $tries < 400 && (count($slots) < 4 || $node === null); $tries++) {
        $x = robots58_rand($s, 12);
        $y = robots58_rand($s, 8);
        if ($q[$y][$x] !== '.' || $protected($x, $y) || in_array([$x, $y], $slots, true) || [$x, $y] === $node) continue;
        if (count($slots) < 4 && $x + $y >= 5) $slots[] = [$x, $y];
        elseif ($node === null && $x + $y >= 8) $node = [$x, $y];
    }
    if (count($slots) < 4 || $node === null) return null;
    return robots58_mirror($q, $slots, $node);
}

/** Z jedné čtvrtiny poskládá celou mapu (zrcadlení podle obou os) a ověří, že je celá průchozí. */
function robots58_mirror(array $q, array $slots, array $node): ?array
{
    $grid = '';
    for ($y = 0; $y < ROBOTS58_H; $y++) {
        for ($x = 0; $x < ROBOTS58_W; $x++) $grid .= $q[$y < 8 ? $y : 15 - $y][$x < 12 ? $x : 23 - $x];
    }
    $at = static fn(array $p, int $corner): int => (($corner & 2) ? 15 - $p[1] : $p[1]) * ROBOTS58_W + (($corner & 1) ? 23 - $p[0] : $p[0]);
    $packets = [];
    foreach ($slots as $slot) for ($corner = 0; $corner < 4; $corner++) $packets[] = $at($slot, $corner);
    $nodes = [];
    for ($corner = 0; $corner < 4; $corner++) $nodes[] = $at($node, $corner);
    $start = 2 * ROBOTS58_W + 2;
    $seen = [$start => true];
    $queue = [$start];
    for ($h = 0; $h < count($queue); $h++) {
        $cur = $queue[$h];
        $x = $cur % ROBOTS58_W;
        foreach ([$cur - ROBOTS58_W, $cur + ROBOTS58_W, $x > 0 ? $cur - 1 : -1, $x < ROBOTS58_W - 1 ? $cur + 1 : -1] as $nb) {
            if ($nb < 0 || $nb >= ROBOTS58_W * ROBOTS58_H || isset($seen[$nb]) || $grid[$nb] === '#') continue;
            $seen[$nb] = true;
            $queue[] = $nb;
        }
    }
    if (count($seen) !== ROBOTS58_W * ROBOTS58_H - substr_count($grid, '#')) return null;
    return ['grid' => $grid, 'packets' => $packets, 'nodes' => $nodes];
}

function robots58_corner_of(int $idx): int
{
    return ($idx % ROBOTS58_W >= 12 ? 1 : 0) + (intdiv($idx, ROBOTS58_W) >= 8 ? 2 : 0);
}

// ---------------------------------------------------------------------------
// Filtr zpráv say
// ---------------------------------------------------------------------------

/** @return array{0:string,1:bool} [text, byl skryt filtrem] */
function robots58_say_clean(string $text): array
{
    $text = trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text));
    $text = mb_substr($text, 0, ROBOTS58_MAX_SAY);
    $norm = strtr(mb_strtolower($text), ['á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z', '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '@' => 'a', '$' => 's', '7' => 't']);
    $words = preg_split('/[^a-z]+/', $norm, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    foreach ($words as $word) {
        if (in_array($word, ROBOTS58_BAD_EXACT, true)) return [tr('(zpráva skryta filtrem)'), true];
        foreach (ROBOTS58_BAD_PREFIX as $bad) { if (str_starts_with($word, $bad)) return [tr('(zpráva skryta filtrem)'), true]; }
    }
    $joined = implode('', $words);
    foreach (ROBOTS58_BAD_JOINED as $bad) { if (str_contains($joined, $bad)) return [tr('(zpráva skryta filtrem)'), true]; }
    return [$text, false];
}

// ---------------------------------------------------------------------------
// Simulace
// ---------------------------------------------------------------------------

final class Robots58Sim implements Robots58World
{
    public string $grid;
    public array $step = [];
    public array $packetSlots;
    public array $packetOn = [];
    public array $packetAt = [];
    public array $packetBack = [];
    public array $nodeSlots;
    public array $nodeLeft = [];
    public array $nodeAt = [];
    public array $nodeBreak = [];
    public array $nodeFixes = [];
    public array $chargers = [];
    public array $baseTiles = [[], [], [], []];
    public array $pos = [];
    public array $en = [];
    public array $cargo = [];
    public array $score = [];
    public array $corner = [];
    public array $still = [];
    public array $heat = [];
    public array $cool = [];
    public array $mem = [];
    public array $sayText = [];
    public array $sayUntil = [];
    public array $occ = [];
    public array $progs = [];
    public array $ctxs = [];
    public array $stats = [];
    public array $firstCount = [];
    public int $turn = 0;
    public int $n = 0;
    private array $fc = [];
    private array $prev = [];
    private array $key = [];
    private array $deltas = [];
    private array $dP = [];
    private array $dN = [];
    private array $dM = [];

    /** @param list<array{program:?Robots58Program, corner:int}> $robots */
    public function __construct(array $map, public int $seed, public int $turns, array $robots)
    {
        $this->grid = $map['grid'];
        $this->packetSlots = $map['packets'];
        $this->nodeSlots = $map['nodes'];
        $cells = ROBOTS58_W * ROBOTS58_H;
        for ($i = 0; $i < $cells; $i++) {
            $x = $i % ROBOTS58_W;
            $this->step[$i] = ['up' => $i >= ROBOTS58_W ? $i - ROBOTS58_W : -1, 'down' => $i + ROBOTS58_W < $cells ? $i + ROBOTS58_W : -1, 'left' => $x > 0 ? $i - 1 : -1, 'right' => $x < ROBOTS58_W - 1 ? $i + 1 : -1];
            if ($this->grid[$i] === '+') $this->chargers[] = $i;
            if ($this->grid[$i] === 'B') $this->baseTiles[robots58_corner_of($i)][] = $i;
        }
        foreach ($this->packetSlots as $slot => $idx) { $this->packetOn[$slot] = true; $this->packetAt[$idx] = $slot; $this->packetBack[$slot] = 0; }
        $firstBreak = 12 + robots58_seed_of('node:' . $seed) % 16;
        foreach ($this->nodeSlots as $slot => $idx) { $this->nodeLeft[$slot] = 0; $this->nodeBreak[$slot] = $firstBreak; $this->nodeFixes[$slot] = 0; }
        $perCorner = [0, 0, 0, 0];
        foreach (array_values($robots) as $i => $robot) {
            $corner = max(0, min(3, (int)$robot['corner']));
            $tiles = $this->baseTiles[$corner];
            $this->pos[$i] = $tiles[[3, 2, 1, 0][$perCorner[$corner]++ % 4]];
            $this->en[$i] = ROBOTS58_ENERGY_MAX;
            $this->cargo[$i] = $this->score[$i] = $this->still[$i] = $this->sayUntil[$i] = $this->firstCount[$i] = $this->heat[$i] = $this->cool[$i] = 0;
            $this->corner[$i] = $corner;
            $this->mem[$i] = [];
            $this->sayText[$i] = '';
            $this->occ[$this->pos[$i]][$i] = true;
            $this->progs[$i] = $robot['program'] ?? null;
            $ctx = new Robots58Ctx();
            $ctx->world = $this;
            $ctx->robot = $i;
            $this->ctxs[$i] = $ctx;
            $this->stats[$i] = ['points' => 0, 'delivered' => 0, 'picks' => 0, 'repairs' => 0, 'fixed' => 0, 'charges' => 0, 'moves' => 0, 'blocked' => 0, 'energy_used' => 0, 'errors' => 0, 'budget' => 0, 'fails' => 0, 'idle' => 0, 'steps' => 0, 'issues' => []];
        }
        $this->n = count($this->pos);
    }

    /** BFS vzdálenosti od políčka (řetězec: 1 bajt na políčko, 255 = nedosažitelné). Cizí základny jsou slepé listy. */
    public function field(int $src): string
    {
        if (isset($this->fc[$src])) return $this->fc[$src];
        $d = array_fill(0, ROBOTS58_W * ROBOTS58_H, 255);
        $d[$src] = 0;
        $srcBase = $this->grid[$src] === 'B' ? robots58_corner_of($src) : -1;
        $queue = [$src];
        for ($h = 0; $h < count($queue); $h++) {
            $cur = $queue[$h];
            if ($cur !== $src && $this->grid[$cur] === 'B' && robots58_corner_of($cur) !== $srcBase) continue;
            $next = min(254, $d[$cur] + 1);
            foreach ($this->step[$cur] as $nb) {
                if ($nb >= 0 && $d[$nb] === 255 && $this->grid[$nb] !== '#') { $d[$nb] = $next; $queue[] = $nb; }
            }
        }
        $out = '';
        foreach ($d as $v) $out .= chr($v);
        return $this->fc[$src] = $out;
    }

    private function frameKey(int $idx, int $corner): int
    {
        $x = $idx % ROBOTS58_W;
        $y = intdiv($idx, ROBOTS58_W);
        return (($corner & 2) ? 15 - $y : $y) * ROBOTS58_W + (($corner & 1) ? 23 - $x : $x);
    }

    private function idxOf(mixed $p): int
    {
        if (!is_array($p) || !is_int($p[0] ?? null) || !is_int($p[1] ?? null)) return -1;
        if ($p[0] < 0 || $p[1] < 0 || $p[0] >= ROBOTS58_W || $p[1] >= ROBOTS58_H) return -1;
        return $p[1] * ROBOTS58_W + $p[0];
    }

    private function tileKind(int $i, int $idx, bool $look): string
    {
        if ($idx < 0) return 'edge';
        $ch = $this->grid[$idx];
        if ($ch === '#') return 'wall';
        if ($look && isset($this->occ[$idx])) {
            foreach ($this->occ[$idx] as $j => $_) { if ($j !== $i) return 'robot'; }
        }
        if (isset($this->packetAt[$idx])) return 'packet';
        if (isset($this->nodeAt[$idx])) return 'node';
        if ($ch === '+') return 'charger';
        if ($ch === 'B') return robots58_corner_of($idx) === $this->corner[$i] ? 'base' : 'other_base';
        return 'empty';
    }

    public function sense(int $robot, string $name): mixed
    {
        return match ($name) {
            'pos' => [$this->pos[$robot] % ROBOTS58_W, intdiv($this->pos[$robot], ROBOTS58_W)],
            'energy' => $this->en[$robot],
            'cargo' => $this->cargo[$robot],
            'max_cargo' => ROBOTS58_CARGO_MAX,
            'turn' => $this->turn,
            'turns_left' => $this->turns - $this->turn,
            'score' => $this->score[$robot],
            'here' => $this->tileKind($robot, $this->pos[$robot], false),
            default => null,
        };
    }

    private function kindArg(mixed $kind, int $line, string $fn): string
    {
        if (!in_array($kind, ['packet', 'node', 'charger', 'base'], true)) throw new Robots58RuntimeError($line, tr('{fn} zná jen "packet", "node", "charger" a "base".', ['fn' => $fn]), tr('Třeba: {fn}("packet") – nezapomeň na uvozovky.', ['fn' => $fn]));
        return $kind;
    }

    public function call(int $robot, string $name, array $args, int $line): mixed
    {
        switch ($name) {
            case 'look':
                if (!in_array($args[0], ROBOTS58_DIRECTIONS, true)) throw new Robots58RuntimeError($line, tr('look čeká směr up, down, left nebo right.'), tr('Třeba: look(up)'));
                return $this->tileKind($robot, $this->step[$this->pos[$robot]][$args[0]], true);
            case 'nearest':
                return $this->nearest($robot, $this->kindArg($args[0], $line, 'nearest'));
            case 'count':
                $kind = $this->kindArg($args[0], $line, 'count');
                return match ($kind) { 'packet' => count($this->packetAt), 'node' => count($this->nodeAt), 'charger' => count($this->chargers), default => count($this->baseTiles[$this->corner[$robot]]) };
            case 'distance':
                if ($args[0] === null) return -1;
                if (!is_array($args[0])) throw new Robots58RuntimeError($line, tr('distance čeká pozici, dostal {type}.', ['type' => robots58_type($args[0])]), tr('Třeba: distance(nearest("charger"))'));
                $idx = $this->idxOf($args[0]);
                if ($idx < 0) return -1;
                $d = ord($this->field($this->pos[$robot])[$idx]);
                return $d === 255 ? -1 : $d;
        }
        throw new Robots58RuntimeError($line, tr('Neznámá funkce {name}.', ['name' => $name]), '');
    }

    private function nearest(int $i, string $kind): ?array
    {
        $targets = match ($kind) { 'packet' => array_keys($this->packetAt), 'node' => array_keys($this->nodeAt), 'charger' => $this->chargers, default => $this->baseTiles[$this->corner[$i]] };
        $field = $this->field($this->pos[$i]);
        $best = -1;
        $bestD = 255;
        $bestK = PHP_INT_MAX;
        foreach ($targets as $t) {
            $d = ord($field[$t]);
            if ($d >= 255 || $d > $bestD) continue;
            $k = $this->frameKey($t, $this->corner[$i]);
            if ($d < $bestD || $k < $bestK) { $best = $t; $bestD = $d; $bestK = $k; }
        }
        return $best < 0 ? null : [$best % ROBOTS58_W, intdiv($best, ROBOTS58_W)];
    }

    private function blockedAt(int $i, int $dest): bool
    {
        $ch = $this->grid[$dest];
        if ($ch === 'B' || $ch === '+' || !isset($this->occ[$dest])) return false;
        foreach ($this->occ[$dest] as $j => $_) { if ($j !== $i && $this->still[$j] < ROBOTS58_GHOST_AFTER) return true; }
        return false;
    }

    private function issue(int $i, int $line, string $message, string $tip = ''): void
    {
        $issues = &$this->stats[$i]['issues'];
        if (isset($issues[$message])) { $issues[$message]['count']++; return; }
        if (count($issues) < ROBOTS58_MAX_ISSUES) $issues[$message] = ['turn' => $this->turn, 'line' => $line, 'message' => $message, 'tip' => $tip, 'count' => 1];
    }

    private function moveTo(int $i, int $dest, int $line): int
    {
        if ($dest < 0 || $this->grid[$dest] === '#') { $this->stats[$i]['blocked']++; $this->issue($i, $line, tr('Robot narazil do regálu nebo okraje mapy.'), tr('Před pohybem se podívej: if look(up) != "wall":')); return 2; }
        if ($this->grid[$dest] === 'B' && robots58_corner_of($dest) !== $this->corner[$i]) { $this->stats[$i]['blocked']++; $this->issue($i, $line, tr('Do cizí základny vjet nejde.'), tr('Balíčky vozíš na svou základnu: nearest("base").')); return 2; }
        if ($this->en[$i] < 1) { $this->issue($i, $line, tr('Vybitá baterie – robot se nepohne.'), tr('Hlídej energy a včas jeď nabíjet: step_to nearest("charger"), pak charge.')); return 9; }
        if ($this->blockedAt($i, $dest)) { $this->stats[$i]['blocked']++; return 2; }
        unset($this->occ[$this->pos[$i]][$i]);
        if ($this->occ[$this->pos[$i]] === []) unset($this->occ[$this->pos[$i]]);
        $this->occ[$dest][$i] = true;
        $this->pos[$i] = $dest;
        $this->en[$i]--;
        $this->stats[$i]['moves']++;
        $this->stats[$i]['energy_used']++;
        return 1;
    }

    private function stepToward(int $i, int $target): int
    {
        $field = $this->field($target);
        $cur = ord($field[$this->pos[$i]]);
        $candidates = [];
        $minD = 255;
        $foreign = false;
        foreach (ROBOTS58_DIR_ORDER[$this->corner[$i]] as $dir) {
            $nb = $this->step[$this->pos[$i]][$dir];
            if ($nb < 0 || $this->grid[$nb] === '#') continue;
            $d = ord($field[$nb]);
            if ($d >= 255 || $d >= $cur || $d > $minD) continue;
            if ($this->grid[$nb] === 'B' && robots58_corner_of($nb) !== $this->corner[$i]) { $foreign = true; continue; }
            if ($d < $minD) { $minD = $d; $candidates = []; }
            $candidates[] = $nb;
        }
        foreach ($candidates as $nb) { if (!$this->blockedAt($i, $nb)) return $nb; }
        return $candidates[0] ?? ($foreign ? -2 : -1);
    }

    /** Provede akci robota; vrací kód akce pro záznam. */
    private function act(int $i, array $res): int
    {
        if ($res['status'] === 'error') { $this->stats[$i]['errors']++; $this->issue($i, (int)$res['error']['line'], (string)$res['error']['message'], (string)$res['error']['tip']); return 7; }
        if ($res['status'] === 'budget') { $this->stats[$i]['budget']++; $this->issue($i, 0, (string)$res['error']['message'], (string)$res['error']['tip']); return 8; }
        if ($res['status'] === 'end') { $this->stats[$i]['idle']++; return 0; }
        [$kind, $arg, $line] = $res['action'];
        $pos = $this->pos[$i];
        $fail = function (string $message, string $tip = '') use ($i, $line): int { $this->stats[$i]['fails']++; $this->issue($i, $line, $message, $tip); return 10; };
        switch ($kind) {
            case 'wait': $this->stats[$i]['idle']++; return 0;
            case 'move': return $this->moveTo($i, $this->step[$pos][$arg], $line);
            case 'step_to':
                if ($arg === null) return $fail(tr('step_to dostal none – cíl neexistuje (třeba už nejsou žádné balíčky).'), tr('Než vyrazíš, zkontroluj: if cil != none:'));
                $target = $this->idxOf($arg);
                if ($target < 0) return $fail(tr('step_to: cíl je mimo mapu (x 0–23, y 0–15).'));
                if ($target === $pos) return $fail(tr('step_to: robot už na cíli stojí.'), tr('Na místě proveď akci (pick, drop, repair, charge).'));
                $next = $this->stepToward($i, $target);
                if ($next < 0) { $this->stats[$i]['blocked']++; $this->issue($i, $line, $next === -2 ? tr('Do cizí základny vjet nejde.') : tr('Cíl je nedosažitelný.'), $next === -2 ? tr('Balíčky vozíš na svou základnu: nearest("base").') : ''); return 2; }
                return $this->moveTo($i, $next, $line);
            case 'pick':
                if (!isset($this->packetAt[$pos])) return $fail(tr('pick: tady žádný balíček není.'), tr('Zkontroluj nejdřív: if here == "packet":'));
                if ($this->cargo[$i] >= ROBOTS58_CARGO_MAX) return $fail(tr('pick: náklad je plný ({max}).', ['max' => ROBOTS58_CARGO_MAX]), tr('Nejdřív balíčky doruč na základnu a proveď drop.'));
                $slot = $this->packetAt[$pos];
                unset($this->packetAt[$pos]);
                $this->packetOn[$slot] = false;
                $this->packetBack[$slot] = $this->turn + ROBOTS58_PACKET_RESPAWN;
                $this->dP[] = [$slot, 0];
                $this->cargo[$i]++;
                $this->stats[$i]['picks']++;
                return 3;
            case 'drop':
                if ($this->grid[$pos] !== 'B' || robots58_corner_of($pos) !== $this->corner[$i]) return $fail(tr('drop: balíčky se odevzdávají jen na vlastní základně.'), tr('Cesta domů: step_to nearest("base")'));
                if ($this->cargo[$i] === 0) return $fail(tr('drop: nemáš co odevzdat.'));
                $this->score[$i] += $this->cargo[$i] * ROBOTS58_PTS_PACKET;
                $this->stats[$i]['delivered'] += $this->cargo[$i];
                $this->cargo[$i] = 0;
                return 4;
            case 'repair': return $this->repair($i, $pos, $line, $fail);
            case 'charge':
                if ($this->grid[$pos] !== '+') return $fail(tr('charge: nabíjet jde jen na nabíječce.'), tr('step_to nearest("charger"), pak charge.'));
                $this->en[$i] = min(ROBOTS58_ENERGY_MAX, $this->en[$i] + ROBOTS58_CHARGE);
                $this->stats[$i]['charges']++;
                return 6;
        }
        return 0;
    }

    private function repair(int $i, int $pos, int $line, Closure $fail): int
    {
        if (!isset($this->nodeAt[$pos])) return $fail(tr('repair: tady není rozbitý uzel.'), tr('Najdi ho: step_to nearest("node")'));
        if ($this->en[$i] < ROBOTS58_REPAIR_COST) { $this->issue($i, $line, tr('Na opravu chybí energie (stojí {cost}).', ['cost' => ROBOTS58_REPAIR_COST]), tr('Nabij se na nabíječce.')); return 9; }
        $slot = $this->nodeAt[$pos];
        $this->nodeLeft[$slot]--;
        $this->en[$i] -= ROBOTS58_REPAIR_COST;
        $this->stats[$i]['energy_used'] += ROBOTS58_REPAIR_COST;
        $this->score[$i] += ROBOTS58_PTS_REPAIR;
        $this->stats[$i]['repairs']++;
        $this->dN[] = [$slot, $this->nodeLeft[$slot]];
        if ($this->nodeLeft[$slot] > 0) return 5;
        unset($this->nodeAt[$pos]);
        $this->nodeFixes[$slot]++;
        $this->nodeBreak[$slot] = $this->turn + 35 + robots58_seed_of('node:' . $this->seed . ':' . $this->nodeFixes[$slot]) % 20;
        $this->stats[$i]['fixed']++;
        return 11;
    }

    private function robotTurn(int $i): int
    {
        $prog = $this->progs[$i];
        $posBefore = $this->pos[$i];
        $enBefore = $this->en[$i];
        if ($prog === null) {
            $code = 0;
        } elseif ($this->cool[$i] > 0) {
            $this->cool[$i]--;
            $this->stats[$i]['idle']++;
            $code = 8;
        } else {
            $c = $this->ctxs[$i];
            $c->g = $this->mem[$i];
            $c->kept = array_fill_keys(array_keys($this->mem[$i]), true);
            $c->rng = (($this->seed ^ ($this->turn * 0x9E3779B1) ^ ($i * 0x85EBCA77)) & 0xFFFFFFFF) ?: 1;
            $res = robots58_run_turn($prog, $c);
            $this->stats[$i]['steps'] += $c->steps;
            $this->mem[$i] = array_intersect_key($c->g, $c->kept);
            if ($c->say !== null) $this->say($i, $c->say);
            $code = $this->act($i, $res);
            $this->heat[$i] = $res['status'] === 'budget' ? $this->heat[$i] + 1 : 0;
            if ($this->heat[$i] >= ROBOTS58_OVERHEAT_AFTER) {
                $this->heat[$i] = 0;
                $this->cool[$i] = ROBOTS58_COOLDOWN;
                $this->issue($i, 0, tr('Procesor se přehřál (3× po sobě vyčerpaný rozpočet kroků) – robot {n} tahů chladne.', ['n' => ROBOTS58_COOLDOWN]), tr('Najdi smyčku, která nikdy neskončí.'));
            }
        }
        if ($this->en[$i] === $enBefore && $this->en[$i] < ROBOTS58_LOW_ENERGY) $this->en[$i]++;
        $this->still[$i] = $this->pos[$i] === $posBefore ? $this->still[$i] + 1 : 0;
        return $code;
    }

    private function say(int $i, string $text): void
    {
        [$clean, $filtered] = robots58_say_clean($text);
        if ($filtered) $this->issue($i, 0, tr('Zprávu say skryl filtr slušnosti.'), tr('Piš zprávy, které můžeš ukázat celé třídě.'));
        $this->sayUntil[$i] = $this->turn + ROBOTS58_SAY_TURNS;
        if ($clean === $this->sayText[$i]) return;
        $this->sayText[$i] = $clean;
        $this->dM[] = [$i, $clean];
    }

    private function worldUpdate(): void
    {
        foreach ($this->packetSlots as $slot => $idx) {
            if ($this->packetOn[$slot] || $this->packetBack[$slot] > $this->turn) continue;
            $this->packetOn[$slot] = true;
            $this->packetAt[$idx] = $slot;
            $this->dP[] = [$slot, 1];
        }
        foreach ($this->nodeSlots as $slot => $idx) {
            if ($this->nodeLeft[$slot] > 0 || $this->nodeBreak[$slot] > $this->turn) continue;
            $this->nodeLeft[$slot] = ROBOTS58_NODE_HP;
            $this->nodeAt[$idx] = $slot;
            $this->nodeBreak[$slot] = PHP_INT_MAX;
            $this->dN[] = [$slot, ROBOTS58_NODE_HP];
        }
    }

    private function keyframe(): array
    {
        $robots = [];
        for ($i = 0; $i < $this->n; $i++) $robots[] = [$this->pos[$i], $this->en[$i], $this->cargo[$i], $this->score[$i]];
        return ['r' => $robots, 'p' => implode('', array_map(static fn(bool $on): string => $on ? '1' : '0', $this->packetOn)), 'n' => array_values($this->nodeLeft), 'm' => $this->sayText];
    }

    private function stepTurn(): void
    {
        $this->dP = $this->dN = $this->dM = [];
        $this->worldUpdate();
        $order = robots58_shuffle(range(0, max(0, $this->n - 1)), robots58_seed_of('order:' . $this->seed . ':' . $this->turn));
        if ($this->n > 0) $this->firstCount[$order[0]]++;
        $acts = array_fill(0, $this->n, 0);
        foreach ($this->n > 0 ? $order : [] as $i) $acts[$i] = $this->robotTurn($i);
        for ($i = 0; $i < $this->n; $i++) {
            if ($this->sayText[$i] !== '' && $this->sayUntil[$i] <= $this->turn) { $this->sayText[$i] = ''; $this->dM[] = [$i, '']; }
        }
        $rows = [];
        for ($i = 0; $i < $this->n; $i++) {
            $row = [$this->pos[$i], $acts[$i], $this->en[$i], $this->cargo[$i], $this->score[$i]];
            if ($acts[$i] !== 0 || $row !== ($this->prev[$i] ?? null)) $rows[] = array_merge([$i], $row);
            $this->prev[$i] = [$row[0], 0, $row[2], $row[3], $row[4]];
        }
        $this->deltas[$this->turn] = [$rows, $this->dP, $this->dN, $this->dM];
        if ($this->turn % ROBOTS58_KEYFRAME === 0) $this->key[$this->turn] = $this->keyframe();
    }

    /** Odehraje celý zápas. @return array{replay:array, hash:string, stats:list<array>, scores:list<int>} */
    public function run(): array
    {
        $this->key[0] = $this->keyframe();
        for ($i = 0; $i < $this->n; $i++) $this->prev[$i] = [$this->pos[$i], 0, $this->en[$i], 0, 0];
        for ($t = 1; $t <= $this->turns; $t++) { $this->turn = $t; $this->stepTurn(); }
        $deltas = array_values($this->deltas);
        $hash = hash('sha256', json_encode([$this->grid, $this->key, $deltas], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        $stats = [];
        for ($i = 0; $i < $this->n; $i++) {
            $st = $this->stats[$i];
            $st['points'] = $this->score[$i];
            $st['issues'] = array_values($st['issues']);
            $st['efficiency'] = $st['energy_used'] > 0 ? (int)round(100 * $this->score[$i] / $st['energy_used']) : 0;
            $st['avg_steps'] = $this->turns > 0 ? (int)round($st['steps'] / $this->turns) : 0;
            $stats[] = $st;
        }
        $replay = [
            'v' => 1, 'w' => ROBOTS58_W, 'h' => ROBOTS58_H, 'grid' => $this->grid, 'packets' => $this->packetSlots, 'nodes' => $this->nodeSlots,
            'corners' => array_values($this->corner), 'turns' => $this->turns, 'keyEvery' => ROBOTS58_KEYFRAME, 'key' => $this->key, 'd' => $deltas, 'hash' => $hash,
        ];
        return ['replay' => $replay, 'hash' => $hash, 'stats' => $stats, 'scores' => array_values($this->score)];
    }
}

/**
 * Připraví a odehraje simulaci.
 * @param list<array{code:string, corner:int}> $entries
 * @return array{replay:array, hash:string, stats:list<array>, scores:list<int>, parse:list<array>, ms:int}
 */
function robots58_simulate(int $seed, int $turns, array $entries): array
{
    $started = hrtime(true);
    $cache = [];
    $robots = [];
    $parse = [];
    foreach ($entries as $entry) {
        $code = (string)$entry['code'];
        $key = sha1($code);
        $cache[$key] ??= robots58_parse($code);
        $parse[] = ['ok' => $cache[$key]['ok'], 'error' => $cache[$key]['error']];
        $robots[] = ['program' => $cache[$key]['program'], 'corner' => (int)$entry['corner']];
    }
    $sim = new Robots58Sim(robots58_map($seed), $seed, max(1, min(ROBOTS58_TURNS_MAX_SIM, $turns)), $robots);
    $out = $sim->run();
    $out['parse'] = $parse;
    $out['first_count'] = $sim->firstCount;
    $out['ms'] = (int)round((hrtime(true) - $started) / 1e6);
    return $out;
}
