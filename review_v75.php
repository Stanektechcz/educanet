<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/taskbank_v75.php';

/**
 * EDUCANET v75 · „Opakování před písemkou“: učitel vybere rozsah lekcí → automatický balíček z chyb celé třídy v exit ticketech (v72)
 * a ze slabých míst v bance úloh (v75). Náhled se jen počítá; zveřejnění (op75_publish) uloží snímek balíčku, aby se žákům neměnil
 * pod rukama; op75_close ho skryje. Bez jmen – jen počty a podíly za třídu.
 * Úložiště storage/review_v75.json.php = {"v":1,"packs":{třída:{"from","to","created_at","by","status","exits":[…],"tasks":[…]}}} (jeden balíček na třídu).
 */

const RV75_ACTIONS = ['op75_publish', 'op75_close'];
const RV75_EXIT_MAX = 8;
const RV75_MIN_ERROR_PCT = 30;
const RV75_TASK_WEAK_PCT = 70;
/** Úlohy banky → témata lekcí, ke kterým patří (pro výběr podle rozsahu lekcí). */
const RV75_TASK_TOPICS = [
    'net_subnet' => ['ip-addressing', 'subnetting-vlsm', 'cidr', 'ipv6-basics'], 'net_mask' => ['ip-addressing', 'subnetting-vlsm', 'cidr'],
    'net_ports' => ['ports', 'service-matrix', 'binding', 'linux-firewall', 'reverse-proxy'], 'net_route' => ['routing', 'cidr', 'nat', 'vlan-basics', 'troubleshooting'],
    'lnx_path' => ['linux-filesystem', 'automation-shell'], 'lnx_chmod' => ['users-permissions', 'ssh-hardening'],
    'lnx_grep' => ['journal-logs', 'logs-monitoring', 'linux-file-troubleshooting', 'http-observability'],
];

function rv75_path(): string
{
    return STORAGE_DIR . '/review_v75.json.php';
}

/** Zveřejněný balíček třídy (nebo null). */
function rv75_pack(string $classId): ?array
{
    $data = function_exists('storage_read_request') ? storage_read_request(rv75_path(), false) : storage_read(rv75_path(), false);
    $p = ($data['packs'] ?? [])[$classId] ?? null;
    return is_array($p) && (string)($p['status'] ?? '') === 'published' ? $p : null;
}

/** Témata lekcí v rozsahu (balíček lekce, bez celého modelu). @return list<string> */
function rv75_topics(string $classId, array $module, int $from, int $to): array
{
    $keys = [];
    for ($n = $from; $n <= $to; $n++) {
        $bundle = function_exists('lm71_bundle') ? lm71_bundle($classId, $module, $n) : [];
        foreach (array_keys((array)($bundle['topics'] ?? [])) as $k) $keys[(string)$k] = true;
    }
    return array_keys($keys);
}

/**
 * Náhled balíčku (čistě výpočet, nic se neukládá).
 * @return array{from:int,to:int,answers:int,exits:list<array>,tasks:list<array>,topics:list<string>}
 */
function rv75_build(string $classId, array $module, int $from, int $to): array
{
    $from = max(1, min(28, $from));
    $to = max($from, min(28, $to));
    $exits = [];
    $answers = 0;
    $all = function_exists('lx72_answers') ? lx72_answers($classId) : [];
    for ($n = $from; $n <= $to; $n++) {
        $ticket = function_exists('lx72_normalize') && function_exists('lc72_overlay') ? lx72_normalize(lc72_overlay($classId, $n)) : null;
        if ($ticket === null) continue;
        $per = [];
        foreach ((array)($all[(string)$n] ?? []) as $row) {
            if (!is_array($row)) continue;
            $vi = (int)($row['v'] ?? -1);
            if (!isset($ticket['variants'][$vi])) continue;
            $answers++;
            $per[$vi]['total'] = ($per[$vi]['total'] ?? 0) + 1;
            $per[$vi]['wrong'] = ($per[$vi]['wrong'] ?? 0) + ((int)($row['ok'] ?? 0) === 1 ? 0 : 1);
        }
        foreach ($per as $vi => $c) {
            $pct = (int)round($c['wrong'] / max(1, $c['total']) * 100);
            if ($c['wrong'] < 1 || $pct < RV75_MIN_ERROR_PCT) continue;
            $v = $ticket['variants'][$vi];
            $exits[] = ['lesson' => $n, 'question' => $v['question'], 'options' => $v['options'], 'correct' => $v['correct'], 'explanation' => $v['explanation'],
                'wrong' => $c['wrong'], 'total' => $c['total'], 'pct' => $pct];
        }
    }
    usort($exits, static fn(array $a, array $b): int => ($b['pct'] <=> $a['pct']) ?: ($b['wrong'] <=> $a['wrong']) ?: ($a['lesson'] <=> $b['lesson']));
    $topics = rv75_topics($classId, $module, $from, $to);
    $tasks = [];
    $stats = tb75_class_stats($classId);
    foreach (tb75_tasks_for_class($classId) as $t) {
        if (array_intersect(RV75_TASK_TOPICS[$t] ?? [], $topics) === []) continue;
        $s = $stats[$t];
        if ($s['attempts'] > 0 && $s['rate'] >= RV75_TASK_WEAK_PCT) continue;
        $tasks[] = ['task' => $t, 'attempts' => $s['attempts'], 'rate' => $s['rate'], 'untried' => $s['attempts'] === 0];
    }
    usort($tasks, static fn(array $a, array $b): int => ((int)$a['untried'] <=> (int)$b['untried']) ?: ($a['rate'] <=> $b['rate']));
    return ['from' => $from, 'to' => $to, 'answers' => $answers, 'exits' => array_slice($exits, 0, RV75_EXIT_MAX), 'tasks' => $tasks, 'topics' => $topics];
}

/** Zveřejní snímek balíčku (přepíše předchozí balíček třídy). */
function rv75_publish(string $classId, array $pack, string $by, ?int $now = null): void
{
    $row = ['from' => $pack['from'], 'to' => $pack['to'], 'exits' => $pack['exits'], 'tasks' => array_column($pack['tasks'], 'task'), 'answers' => $pack['answers'],
        'created_at' => date(DATE_ATOM, $now ?? time()), 'by' => mb_substr($by, 0, 60), 'status' => 'published'];
    storage_update(rv75_path(), static function (array $d) use ($classId, $row): array {
        $d['v'] = 1;
        $d['packs'][$classId] = $row;
        return $d;
    });
}

function rv75_close(string $classId): bool
{
    $done = false;
    storage_update(rv75_path(), static function (array $d) use ($classId, &$done): array {
        if (!is_array($d['packs'][$classId] ?? null)) return $d;
        $d['packs'][$classId]['status'] = 'closed';
        $done = true;
        return $d;
    });
    return $done;
}

/** Rozsah lekcí z požadavku (od–do, 1–28). @return array{0:int,1:int} */
function rv75_range(array $src): array
{
    $from = is_string($src['od'] ?? null) && preg_match('/^\d{1,2}$/', (string)$src['od']) === 1 ? (int)$src['od'] : 0;
    $to = is_string($src['do'] ?? null) && preg_match('/^\d{1,2}$/', (string)$src['do']) === 1 ? (int)$src['do'] : 0;
    return [max(0, min(28, $from)), max(0, min(28, $to))];
}

/** POST op75_* (CSRF, politika a content.manage v teacher.php). */
function rv75_teacher_handle_post(string $action): void
{
    if (!in_array($action, RV75_ACTIONS, true)) throw new RuntimeException('Neznámá akce opakování.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!lm71_class_ok($classId) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    [$from, $to] = rv75_range($_POST);
    if ($action === 'op75_close') {
        $ok = rv75_close($classId);
        $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $ok ? 'Opakování je skryté – žáci ho už nevidí.' : 'Třída nemá zveřejněné opakování.'];
    } else {
        if ($from < 1 || $to < $from) throw new RuntimeException('Vyberte rozsah lekcí od–do.');
        $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
        $pack = rv75_build($classId, $module, $from, $to);
        if ($pack['exits'] === [] && $pack['tasks'] === []) throw new RuntimeException('Balíček je prázdný – v rozsahu nejsou chyby z exit ticketů ani úlohy k procvičení.');
        rv75_publish($classId, $pack, function_exists('teacher_display_name') ? teacher_display_name() : 'učitel');
        $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => 'Opakování L' . $from . '–L' . $to . ' je zveřejněné. Žáci ho najdou v menu Učení → Opakování před písemkou.'];
    }
    $query = ['tab' => 'opakovani75', 'class' => $classId] + ($from > 0 ? ['od' => (string)$from, 'do' => (string)max($from, $to)] : []);
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
