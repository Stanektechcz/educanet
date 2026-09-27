<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Týmové hry – Linux Lab mikroúlohy pro linii „sítě“ (3.A/4.A).
 *
 * Registruje skrytý balíček `tg-lab` (viditelný jen přes kontext `tg:<id_hry>` – v běžném procvičování
 * se nezobrazí, protože `classes` míří na třídu, která ve škole neexistuje) a kontext `tg` podle
 * docs/LAB_V58_API.md §4: přístup, sdílené světy týmu (Štafeta, Úniková místnost) i vlastní kopie
 * světa na žáka (Správci sítě), semínko a napojení na tg58_mutate() při vyřešení úlohy.
 *
 * Linie „grafika“ (1.A/2.A) tyhle úlohy neřeší v simulátoru – používá kvízovou banku a nativní úlohy
 * „rozbitá stránka“ z teamgames_v58_quiz.php (viz docs/V58_PLAN.md §4, poznámka pro 1.A/2.A).
 *
 * Bezpečnostní invariant platí beze změny: jen deklarativní generátory/kontroly z jádra (file, decoys,
 * file_contains) – žádné vlastní spouštění, žádná síť.
 */

require_once __DIR__ . '/teamgames_v58_core.php';

const TG58_LAB_RELAY_LEGS = 4;
const TG58_LAB_NETADMIN_TYPES = ['dns', 'firewall', 'disk', 'config'];
const TG58_LAB_NETADMIN_PER_TYPE = 5;

// ---------------------------------------------------------------------------
// Stavební kostky úrovní (viz docs/LAB_V58_API.md §3 – generate/checks se vyhodnotí líně)
// ---------------------------------------------------------------------------

/** Úroveň „najdi a odevzdej kód“ – bezpečná stavební kostka z vestavěných generátorů file/decoys/code_file. */
function tg58_lvl_find(string $id, string $title, string $story, string $task, string $dir, int $decoys, int $points = 60): array
{
    return [
        'id' => $id, 'type' => 'code', 'title' => $title, 'difficulty' => 1, 'points' => $points, 'minutes' => 4,
        'story' => $story, 'task' => $task . ' Až kód najdeš, odevzdej ho: submit EDU-XXXX-XXXX',
        'hints' => ['Zkus: find ' . $dir . ' -type f', 'Zkus: grep -rl EDU- ' . $dir . ' 2>/dev/null'],
        'generate' => [['decoys', ['dir' => $dir, 'count' => max(1, min(40, $decoys)), 'name' => 'zaznam-{N}.txt']], ['code_file', ['dirs' => [$dir], 'names' => ['vysledek-{TOKEN}.txt']]]],
        'solution' => ['cat {f:code_path}', 'submit {CODE}'],
    ];
}

/** Úroveň „najdi a oprav obsah souboru“ – bezpečná stavební kostka z generátoru file + kontroly file_contains. */
function tg58_lvl_fix(string $id, string $title, string $story, string $task, string $path, string $wrong, string $right, int $points = 70): array
{
    // Soubory mimo domovský adresář žáka patří rootovi (viz lab58_gen_write) – zápis jde jen přes sudo.
    $needsSudo = !str_starts_with($path, '/home/student/');
    $editHint = $needsSudo
        ? 'Tenhle soubor patří rootovi, zapiš do něj přes: echo "nová hodnota" | sudo tee ' . $path
        : 'Uprav soubor: nano ' . $path . ' (ulož Ctrl+O, Enter, zavři Ctrl+X)';
    return [
        'id' => $id, 'type' => 'check', 'title' => $title, 'difficulty' => 2, 'points' => $points, 'minutes' => 5,
        'story' => $story, 'task' => $task,
        'hints' => ['Podívej se na obsah: cat ' . $path, $editHint],
        'generate' => [['file', ['path' => $path, 'content' => $wrong]]],
        'checks' => [['file_contains', ['path' => $path, 'equals' => $right, 'label' => 'Soubor ' . $path . ' má opravenou hodnotu']]],
        'solution' => tg58_echo_lines_solution($path, $right, $needsSudo),
    ];
}

/**
 * Referenční řešení pro „oprav obsah souboru“ – přepíše $path na $content po řádcích (1. řádek
 * přepíše, další připojí), ať audit (`lab58_try_solution`) umí úlohu ověřit na >=5 semínkách.
 * Soubory rootovi (mimo ~student) jde zapsat jen přes `echo … | sudo tee` (stejný vzor jako
 * nápověda příkazu tee v linux_v58_cmd_tldr_b.php). Statické řetězce (žádné {f:...}/{CODE}
 * šablony), takže preparace (lab58_level_prepare) je jen zabalí do uzávěry beze změny.
 */
function tg58_echo_lines_solution(string $path, string $content, bool $sudo = false): array
{
    $lines = explode("\n", rtrim($content, "\n"));
    $out = [];
    foreach ($lines as $i => $line) {
        $safe = str_replace(['\\', '"', '$', '`'], ['\\\\', '\\"', '\\$', '\\`'], $line);
        $redir = $sudo ? ('| sudo tee ' . ($i === 0 ? '' : '-a ') . $path) : (($i === 0 ? '>' : '>>') . ' ' . $path);
        $out[] = 'echo "' . $safe . '" ' . $redir;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Štafeta – 4 úseky (najdi → vyfiltruj → oprav → dokonči), sdílený svět týmu
// ---------------------------------------------------------------------------

function tg58_relay_net_leg_ids(): array
{
    return ['tg-relay-net-1', 'tg-relay-net-2', 'tg-relay-net-3', 'tg-relay-net-4'];
}

function tg58_relay_net_levels(): array
{
    return [
        tg58_lvl_find('tg-relay-net-1', 'Štafeta 1/4 · Najdi spojení', 'Tým přebírá server po předchozí směně.', 'Najdi v /var/log záznam s kódem prvního uzlu.', '/var/log', 10),
        tg58_lvl_find('tg-relay-net-2', 'Štafeta 2/4 · Vyfiltruj log', 'Log má tentokrát víc šumu – bude potřeba filtrovat.', 'Prohledej /var/log a /srv/zalohy a najdi ten pravý kód (zkus grep).', '/srv/zalohy', 18),
        tg58_lvl_fix('tg-relay-net-3', 'Štafeta 3/4 · Oprav stav', 'Monitoring hlásí, že stavový soubor je špatně.', 'Otevři /home/student/stafeta/stav.txt a nastav hodnotu na „OPRAVENO“.', '/home/student/stafeta/stav.txt', "stav: chyba\n", "stav: OPRAVENO\n"),
        tg58_lvl_find('tg-relay-net-4', 'Štafeta 4/4 · Poslední úsek', 'Poslední úsek – kód je schovaný hlouběji.', 'Najdi finální kód v /opt/aplikace (zkus find s parametrem -name).', '/opt/aplikace', 24),
    ];
}

function tg58_relay_leg_no(string $levelId): ?int
{
    return preg_match('/^tg-relay-net-([1-9][0-9]?)$/', $levelId, $m) === 1 ? (int)$m[1] : null;
}

// ---------------------------------------------------------------------------
// Správci sítě – mapa uzlů, každý řešitel má vlastní kopii světa (state_key = žák, výchozí)
// ---------------------------------------------------------------------------

function tg58_netadmin_flavor(string $type): array
{
    return match ($type) {
        'dns' => ['title' => 'DNS uzel', 'story' => 'Uzel neumí přeložit jméno serveru.', 'path' => '/etc/hosts.d/web.conf', 'wrong' => "web.skola.cz -> 203.0.113.99\n", 'right' => "web.skola.cz -> 203.0.113.20\n", 'task' => 'Oprav záznam tak, aby web.skola.cz mířilo na 203.0.113.20.'],
        'firewall' => ['title' => 'Firewall uzel', 'story' => 'Pravidlo firewallu blokuje i legitimní provoz.', 'path' => '/etc/firewall.d/rules.conf', 'wrong' => "port 22: deny\nport 443: allow\n", 'right' => "port 22: allow\nport 443: allow\n", 'task' => 'Uprav pravidlo tak, aby port 22 (SSH) povoloval provoz.'],
        'disk' => ['title' => 'Diskový uzel', 'story' => 'Server hlásí špatně nastavenou kvótu disku.', 'path' => '/etc/quota.d/srv.conf', 'wrong' => "kvota: 0GB\n", 'right' => "kvota: 20GB\n", 'task' => 'Nastav kvótu disku na 20GB.'],
        default => ['title' => 'Konfigurační uzel', 'story' => 'Služba běží se starou konfigurací.', 'path' => '/etc/app.d/service.conf', 'wrong' => "verze: stara\nstav: vypnuto\n", 'right' => "verze: nova\nstav: zapnuto\n", 'task' => 'Přepni službu na verzi „nova“ a stav „zapnuto“.'],
    };
}

function tg58_netadmin_net_node_id(string $type, int $n): string
{
    return 'tg-netadmin-net-' . $type . '-' . $n;
}

/** Pořadí uzlů na mapě (prokládané typy kvůli pestrosti), useknuté na $count (16–24 dá docs/V58_PLAN.md §4). */
function tg58_netadmin_net_node_ids(int $count): array
{
    $ids = [];
    for ($n = 1; $n <= TG58_LAB_NETADMIN_PER_TYPE; $n++) {
        foreach (TG58_LAB_NETADMIN_TYPES as $type) $ids[] = tg58_netadmin_net_node_id($type, $n);
    }
    return array_slice($ids, 0, max(1, $count));
}

function tg58_netadmin_net_levels(): array
{
    $out = [];
    foreach (TG58_LAB_NETADMIN_TYPES as $type) {
        for ($n = 1; $n <= TG58_LAB_NETADMIN_PER_TYPE; $n++) {
            $f = tg58_netadmin_flavor($type);
            $id = tg58_netadmin_net_node_id($type, $n);
            $out[] = tg58_lvl_fix($id, $f['title'] . ' ' . $n, $f['story'], $f['task'], $f['path'], $f['wrong'], $f['right'], 40);
        }
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Bingo – malé samostatné úlohy pro pár políček karty (linie sítě)
// ---------------------------------------------------------------------------

function tg58_bingo_net_cell_ids(): array
{
    return ['tg-bingo-net-1', 'tg-bingo-net-2', 'tg-bingo-net-3'];
}

function tg58_bingo_net_levels(): array
{
    return [
        tg58_lvl_find('tg-bingo-net-1', 'Bingo · Skrytý soubor', 'Na políčku bingo tě čeká krátký úkol v terminálu.', 'Najdi skrytý soubor s kódem ve své domovské složce (zkus ls -a).', '/home/student/bingo', 6, 30),
        tg58_lvl_fix('tg-bingo-net-2', 'Bingo · Oprav práva', 'Skript nejde spustit, protože nemá správná práva.', 'Nastav souboru /home/student/bingo/skript.sh spustitelná práva 755 a napiš „opraveno“ do /home/student/bingo/hotovo.txt.', '/home/student/bingo/hotovo.txt', "cekam\n", "opraveno\n", 30),
        tg58_lvl_find('tg-bingo-net-3', 'Bingo · Zpráva v logu', 'V logu je schovaná krátká zpráva s kódem.', 'Projdi /var/log/bingo a najdi soubor s kódem.', '/var/log/bingo', 8, 30),
    ];
}

// ---------------------------------------------------------------------------
// Úniková místnost – jeden sdílený svět týmu, role s asymetrickými informacemi
// ---------------------------------------------------------------------------

const TG58_ESCAPE_ROLES_NET = ['sitar', 'spravce', 'detektiv', 'dokumentator'];

function tg58_escape_net_level(): array
{
    return [
        'id' => 'tg-escape-net-1', 'type' => 'check', 'title' => 'Výpadek serverovny', 'difficulty' => 2, 'points' => 40, 'minutes' => 20,
        'story' => 'Malá serverovna má výpadek. Každý z vás vidí jinou stopu – informace si řekněte nahlas, aplikace chat nemá.',
        'task' => 'Najdi svou stopu (viz níže) a nahlas ji týmu. Až budete mít všechny čtyři části, poskládejte kód v panelu „Zámek“ v aplikaci.',
        'hints' => [],
        'generate' => [
            ['file', ['path' => '/home/student/site/network.log', 'content' => "Provozní log spoje\nlinka: aktivní\nsegment SITAR: [[FRAG:SITAR]]\n"]],
            ['decoys', ['dir' => '/home/student/site/logs', 'count' => 10, 'name' => 'log-{N}.txt']],
            ['file', ['path' => '/home/student/etc/app.conf.bak', 'content' => "# zaloha konfigurace\nsegment SPRAVCE: [[FRAG:SPRAVCE]]\n"]],
            ['decoys', ['dir' => '/home/student/etc/zalohy', 'count' => 10, 'name' => 'zaloha-{N}.bak']],
            ['file', ['path' => '/home/student/var/log/pristupy-podezrele.txt', 'content' => "Podezrely pristup k systemu\nsegment DETEKTIV: [[FRAG:DETEKTIV]]\n"]],
            ['decoys', ['dir' => '/home/student/var/archiv', 'count' => 10, 'name' => 'zaznam-{N}.log']],
            ['file', ['path' => '/home/student/dokumenty/hlaseni-rozpracovane.txt', 'content' => "Rozpracovane hlaseni o vypadku\nsegment DOKUMENTATOR: [[FRAG:DOKUMENTATOR]]\n"]],
            ['decoys', ['dir' => '/home/student/dokumenty/stare', 'count' => 10, 'name' => 'stare-{N}.txt']],
        ],
        'checks' => [['file_contains', ['path' => '/home/student/pripraveno.txt', 'contains' => 'PRIPRAVENO', 'label' => 'Tým napsal PRIPRAVENO do ~/pripraveno.txt, až měl všechny stopy pohromadě']]],
        'solution' => ['echo PRIPRAVENO > /home/student/pripraveno.txt'],
        'roles' => [
            'sitar' => ['task' => 'Jsi Síťař. Najdi provozní log spojení (network.log) a přečti svůj segment kódu nahlas týmu.', 'hints' => ['Zkus: find ~/site -name "*.log"', 'Zkus: cat ~/site/network.log']],
            'spravce' => ['task' => 'Jsi Správce. Najdi zálohu konfigurace (app.conf.bak) a přečti svůj segment kódu nahlas týmu.', 'hints' => ['Zkus: find ~/etc -name "*.bak"', 'Zkus: cat ~/etc/app.conf.bak']],
            'detektiv' => ['task' => 'Jsi Detektiv. Najdi podezřelý přístup v logu a přečti svůj segment kódu nahlas týmu.', 'hints' => ['Zkus: find ~/var -iname "*podez*"']],
            'dokumentator' => ['task' => 'Jsi Dokumentátor. Najdi rozpracované hlášení, přečti svůj segment a poskládej všechny 4 části v panelu „Zámek“. Až bude tým připravený, napiš do ~/pripraveno.txt slovo PRIPRAVENO.', 'hints' => ['Zkus: find ~/dokumenty -name "*hlaseni*"', 'Zámek otevřeš v panelu hry, ne v terminálu.']],
        ],
    ];
}

/**
 * FAIR58-05: fragment role = HMAC(tajemství serveru, hra|tým|role), 4 znaky. Nejde dopočítat bez tajemství
 * a každý tým má jiný kód. Ve sdíleném světě je jen zástupný text [[FRAG:ROLE]] (stavba světa nezávisí na roli),
 * skutečnou hodnotu dosadí až filtr výstupu – a jen hráči s danou rolí. null = tajemství není k dispozici.
 */
function tg58_escape_fragment(string $gameId, string $teamId, string $role): ?string
{
    if (!function_exists('lab57_secret') && is_file(__DIR__ . '/linux_v57_lab.php')) require_once __DIR__ . '/linux_v57_lab.php';
    if (!function_exists('lab57_secret')) return null;
    try {
        return strtoupper(substr(hash_hmac('sha256', $gameId . '|' . $teamId . '|' . $role, lab57_secret()), 0, 4));
    } catch (Throwable $e) {
        error_log('EDUCANET v58 tg escape fragment: ' . $e->getMessage());
        return null;
    }
}

const TG58_ESCAPE_FRAG_RE = '/\[\[FRAG:(SITAR|SPRAVCE|DETEKTIV|DOKUMENTATOR)\]\]/';
const TG58_ESCAPE_NET_LABELS = ['SITAR' => 'Síťař', 'SPRAVCE' => 'Správce', 'DETEKTIV' => 'Detektiv', 'DOKUMENTATOR' => 'Dokumentátor'];

/** Dosadí do textu fragment vlastní role; cizí segmenty zůstanou skryté. */
function tg58_escape_mask_text(string $text, ?string $role, ?string $fragment): string
{
    if (!str_contains($text, '[[FRAG:')) return $text;
    return (string)preg_replace_callback(TG58_ESCAPE_FRAG_RE, static function (array $m) use ($role, $fragment): string {
        if ($role !== null && $fragment !== null && strtoupper($role) === $m[1]) return $fragment;
        return '[skryto – tenhle segment vidí jen ' . TG58_ESCAPE_NET_LABELS[$m[1]] . ']';
    }, $text);
}

/** Filtr run_response / state_payload: v Únikové místnosti přepíše všechny texty odpovědi podle role hráče. */
function tg58_escape_filter_output(mixed $value, array $args): mixed
{
    $ctx = (array)($args['ctx'] ?? []);
    if (!is_array($value) || (string)($ctx['prefix'] ?? '') !== 'tg' || (string)(($args['level'] ?? [])['id'] ?? '') !== 'tg-escape-net-1') return $value;
    $role = is_string($ctx['role'] ?? null) ? (string)$ctx['role'] : null;
    $fragment = null;
    if ($role !== null) {
        ['team' => $teamId] = tg58_lab_lookup($ctx);
        $fragment = $teamId !== null ? tg58_escape_fragment((string)$ctx['id'], (string)$teamId, $role) : null;
    }
    array_walk_recursive($value, static function (&$v) use ($role, $fragment): void {
        if (is_string($v)) $v = tg58_escape_mask_text($v, $role, $fragment);
    });
    return $value;
}

// ---------------------------------------------------------------------------
// Registrace balíčku (skrytý z běžného procvičování) a kontextu `tg`
// ---------------------------------------------------------------------------

function tg58_lab_all_levels(): array
{
    return array_merge(tg58_relay_net_levels(), tg58_netadmin_net_levels(), tg58_bingo_net_levels(), [tg58_escape_net_level()]);
}

/** Typ hry a tým žáka pro daný běh `tg:<id>` – null, pokud hra neexistuje nebo žák není v týmu. */
function tg58_lab_lookup(array $ctx): array
{
    $session = tg58_get((string)$ctx['id']);
    if ($session === null) return ['session' => null, 'team' => null];
    $team = tg58_team_of($session, (string)$ctx['student']);
    return ['session' => $session, 'team' => $team];
}

function tg58_lab_access(array $level, array $ctx): ?string
{
    ['session' => $session, 'team' => $teamId] = tg58_lab_lookup($ctx);
    if ($session === null) return 'Tahle hra neexistuje.';
    if ((string)$session['class_id'] !== (string)$ctx['class']) return 'Tahle hra patří jiné třídě.';
    if (!in_array(tg58_status($session, (int)$ctx['now']), ['running', 'paused'], true)) return 'Hra teď neběží.';
    if ($teamId === null) return 'Nejsi v žádném týmu téhle hry.';
    $type = (string)$session['type'];
    $levelId = (string)$level['id'];
    if ($type === 'relay') {
        $legNo = tg58_relay_leg_no($levelId);
        if ($legNo === null) return 'Tahle úloha do štafety nepatří.';
        $current = (int)($session['game']['teams'][$teamId]['leg'] ?? 1);
        if ($legNo !== $current) return $legNo < $current ? 'Tenhle úsek už máte za sebou – pokračujte dalším.' : 'Tenhle úsek si tým odemkne až po zvládnutí předchozího.';
        return null;
    }
    if ($type === 'netadmin') {
        $nodes = (array)($session['game']['node_ids'] ?? []);
        if (!in_array($levelId, $nodes, true)) return 'Tenhle uzel do hry nepatří.';
        $wave = (int)($session['game']['node_wave'][$levelId] ?? 1);
        $elapsedWave = 1 + intdiv((int)tg58_elapsed($session, (int)$ctx['now']), 300);
        if ($wave > $elapsedWave) return 'Tenhle uzel se ještě neobjevil – další vlna přijde brzy.';
        return null;
    }
    if ($type === 'bingo') {
        if (!in_array($levelId, tg58_bingo_net_cell_ids(), true)) return 'Tahle úloha do bingo karty nepatří.';
        return null;
    }
    if ($type === 'escape') {
        if ($levelId !== 'tg-escape-net-1') return 'Tahle úloha do únikové místnosti nepatří.';
        return null;
    }
    return 'Tenhle typ hry úlohy Labu nepoužívá.';
}

function tg58_lab_state_key(array $ctx): string
{
    ['session' => $session, 'team' => $teamId] = tg58_lab_lookup($ctx);
    $student = (string)($ctx['student'] ?? '');
    if ($session === null || $teamId === null) return $student;
    // Sdílený svět týmu: Štafeta (baton mezi úseky) a Úniková místnost (role se stejnou serverovnou).
    if (in_array((string)$session['type'], ['relay', 'escape'], true)) return 'tgteam:' . (string)$ctx['id'] . ':' . $teamId;
    // Správci sítě a Bingo: každý řešitel má vlastní kopii uzlu/políčka (výchozí = student).
    return $student;
}

function tg58_lab_role(array $ctx): ?string
{
    ['session' => $session, 'team' => $teamId] = tg58_lab_lookup($ctx);
    if ($session === null || $teamId === null || (string)$session['type'] !== 'escape') return null;
    $role = (string)($session['game']['role_of'][(string)$ctx['student']] ?? '');
    return in_array($role, TG58_ESCAPE_ROLES_NET, true) ? $role : null;
}

/** Po vyřešení úlohy: posune štafetu, zapíše vlastnictví uzlu Správců sítě (atomicky, přes tg58_mutate). */
function tg58_lab_on_complete(array $ctx, array $level, array $event): void
{
    $gameId = (string)$ctx['id'];
    $session = tg58_get($gameId);
    if ($session === null) return;
    $type = (string)$session['type'];
    $studentKey = (string)$ctx['student'];
    $levelId = (string)$level['id'];
    $now = (int)$ctx['now'];
    try {
        if ($type === 'relay' && function_exists('tg58_relay_on_leg_complete')) {
            tg58_relay_on_leg_complete($gameId, $studentKey, $levelId, $now);
        } elseif ($type === 'netadmin' && function_exists('tg58_netadmin_on_node_complete')) {
            tg58_netadmin_on_node_complete($gameId, $studentKey, $levelId, $now);
        }
        // Bingo a Úniková místnost: samotné dokončení úlohy v Labu není potřeba dál zpracovávat –
        // bingo políčko označuje žák přes JSON API (ověří si stejnou lab57_solved()), únik otevírá
        // nativní zámek v teamgames_v58_game_escape.php (fragmenty = HMAC se serverovým tajemstvím).
    } catch (Throwable $e) {
        error_log('EDUCANET v58 tg lab on_complete: ' . $e->getMessage());
    }
}

function tg58_register_lab_pack(): void
{
    if (!function_exists('lab58_register_pack')) return;
    lab58_register_pack(
        ['id' => 'tg-lab', 'title' => 'Týmové hry', 'description' => 'Mikroúlohy pro týmové hry třídy.', 'order' => 900, 'classes' => [TG58_HIDDEN_CLASS], 'unlock' => 'free', 'icon' => '⛳', 'tone' => 'violet'],
        'tg58_lab_all_levels'
    );
    lab58_register_context('tg', [
        'label' => 'Týmová hra', 'access' => 'tg58_lab_access', 'state_key' => 'tg58_lab_state_key', 'role' => 'tg58_lab_role',
        'on_complete' => 'tg58_lab_on_complete', 'reset' => false,
    ]);
    if (function_exists('lab58_add_filter')) {
        lab58_add_filter('run_response', 'tg58_escape_filter_output');
        lab58_add_filter('state_payload', 'tg58_escape_filter_output');
    }
}

if (function_exists('lab58_register_pack') && function_exists('lab58_register_context')) tg58_register_lab_pack();
