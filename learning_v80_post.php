<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/progress_v80.php';

/**
 * EDUCANET v80 · POST akce žáka (router app/routes.php → app/actions/learning_v80.php; CSRF a třídu ověřil index.php):
 *   lr80_hint        – odkrytí další nápovědy (úloha: aktuální otevřené kolo; opakování: jen splatná položka); ukládá jen počet,
 *   lr80_rep_answer  – odpověď na splatné opakování (jeden pokus za den, výsledek v relaci lr80_rep_last),
 *   lr80_w_check     – kontrola interaktivního cvičení na serveru (výsledek v relaci lr80_w_last),
 *   lr80_goal_add / lr80_goal_remove – osobní cíl na tento týden (druh a číslo z nabídky, žádný volný text).
 * Identita žáka jen ze session (adaptive_student_key); do URL jde nejvýš id úlohy nebo cvičení (žádná data žáka).
 */

const LR80_ACTIONS = ['lr80_hint', 'lr80_rep_answer', 'lr80_w_check', 'lr80_goal_add', 'lr80_goal_remove'];

function lr80_post_str(string $key, int $max = 40): string
{
    return is_string($_POST[$key] ?? null) ? mb_substr(trim((string)$_POST[$key]), 0, $max) : '';
}

function lr80_handle_post(string $action, string $classId, ?int $now = null): void
{
    $now ??= time();
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    if ($studentKey === '' || !in_array($action, LR80_ACTIONS, true)) { $_SESSION['flash'] = tr('Akci se nepodařilo provést.'); redirect_to('?view=dashboard'); }
    match ($action) {
        'lr80_hint' => lr80_post_hint($classId, $studentKey, $now),
        'lr80_rep_answer' => lr80_post_rep($classId, $studentKey, $now),
        'lr80_w_check' => lr80_post_widget($classId, $studentKey, $now),
        default => lr80_post_goal($action, $classId, $studentKey, $now),
    };
}

function lr80_post_hint(string $classId, string $studentKey, int $now): never
{
    $key = lr80_post_str('key');
    $round = (int)lr80_post_str('round', 6);
    $rep = str_starts_with($key, 'rep:');
    $task = $rep ? substr($key, 4) : $key;
    $tasks = tb75_tasks_for_class($classId);
    if (!in_array($task, $tasks, true)) { $_SESSION['flash'] = tr('Úlohu se nepodařilo najít.'); redirect_to('?view=ulohy'); }
    $ref = lr80_ref($classId, $studentKey);
    if ($rep) {
        $item = lr80_state($classId, $ref)['rep'][$task] ?? null;
        $ok = is_array($item) && (int)$item['due_at'] <= $now && (int)($item['c'] ?? 0) + 1 === $round;
        $back = '?' . http_build_query(['view' => 'opakovat', 'uloha' => $task]) . '#lr80-hints';
    } else {
        $e = tb75_entry(tb75_student_state($classId, $ref), $task);
        $ok = $e['round'] === $round && !($e['last_ok'] && $e['tries'] > 0) && !$e['shown'];
        $back = '?' . http_build_query(['view' => 'ulohy', 'uloha' => $task, 'krok' => '2']) . '#lr80-hints';
    }
    if (!$ok || lr80_hint_reveal($classId, $studentKey, $key, $round, $now) === 0) $_SESSION['flash'] = tr('Nápověda je jen k otevřené variantě.');
    redirect_to($back);
}

function lr80_post_rep(string $classId, string $studentKey, int $now): never
{
    $task = lr80_post_str('task');
    $answers = is_array($_POST['a'] ?? null) ? array_filter($_POST['a'], 'is_string') : [];
    $res = lr80_rep_submit($classId, $studentKey, $task, $answers, $now);
    if (!$res['ok']) {
        $_SESSION['flash'] = match ((string)$res['error']) { 'empty' => tr('Vyplň aspoň jedno pole.'), 'not_due' => tr('Tahle úloha dnes opakování nemá.'), default => tr('Úlohu se nepodařilo najít.') };
        redirect_to($res['error'] === 'empty' ? '?' . http_build_query(['view' => 'opakovat', 'uloha' => $task]) : '?view=opakovat');
    }
    $v = $res['variant'];
    $_SESSION['lr80_rep_last'] = ['task' => $task, 'ok' => $res['result']['ok'], 'days' => $res['days'], 'done' => $res['done'], 'why' => $res['why'],
        'fields' => array_map(static fn(array $f): array => ['id' => (string)$f['id'], 'label' => (string)$f['label'], 'answer' => (string)$f['answer']], $v['fields']), 'explain' => (string)$v['explain']];
    redirect_to('?view=opakovat');
}

function lr80_post_widget(string $classId, string $studentKey, int $now): never
{
    $w = lr80_post_str('w');
    $res = wg80_submit($classId, $studentKey, $w, $_POST, $now);
    if (!$res['ok']) {
        $_SESSION['flash'] = $res['error'] === 'empty' ? tr('Vyplň aspoň jedno pole.') : tr('Cvičení se nepodařilo najít.');
        redirect_to(isset(WG80_WIDGETS[$w]) ? '?' . http_build_query(['view' => 'cviceni', 'w' => $w]) : '?view=cviceni');
    }
    $v = $res['variant'];
    $_SESSION['lr80_w_last'] = ['w' => $w, 'ok' => $res['result']['ok'], 'correct' => $res['result']['correct'], 'all' => count($v['fields']), 'fields' => $res['result']['fields'],
        'why' => $res['why'], 'shown' => $res['shown'], 'left' => $res['left'], 'explain' => $res['result']['ok'] || $res['shown'] ? (string)$v['explain'] : ''];
    redirect_to('?' . http_build_query(['view' => 'cviceni', 'w' => $w]) . '#lr80-w-result');
}

function lr80_post_goal(string $action, string $classId, string $studentKey, int $now): never
{
    if ($action === 'lr80_goal_add') {
        [$kind, $n] = array_pad(explode(':', lr80_post_str('goal_pick', 16), 2), 2, '');
        $res = pg80_goal_add($classId, $studentKey, $kind, ctype_digit($n) ? (int)$n : 0, $now);
        $_SESSION['flash'] = $res['ok'] ? tr('Cíl je nastavený na tento týden.') : ($res['error'] === 'duplicate' ? tr('Tenhle druh cíle už na tento týden máš.')
            : ($res['error'] === 'limit' ? tr('Na týden můžeš mít nejvýš 3 cíle.') : tr('Vyber cíl z nabídky.')));
    } else {
        $_SESSION['flash'] = pg80_goal_remove($classId, $studentKey, lr80_post_str('goal', 12), $now) ? tr('Cíl je odebraný.') : tr('Cíl se nepodařilo najít.');
    }
    redirect_to('?view=pokrok#lr80-goals');
}
