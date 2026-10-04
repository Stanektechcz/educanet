<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/paths_v63_flow.php';

/**
 * EDUCANET v63 · obsluha POST akcí výukových cest (logika pro app/actions/paths.php): odevzdání kroku, posun řádku
 * Parsonovy úlohy bez JS a reflexe. Funkce končí přesměrováním (never); identita je ze session volajícího.
 */

/** Text chyby pro žáka podle kódu z p63_submit_step / p63_save_reflection. */
function p63_error_text(string $error): string
{
    return [
        'limit' => tr('Dnešní pokusy o ověření jsou vyčerpané. Zkus to zítra.'),
        'locked' => tr('Tenhle krok se odemkne po dokončení předchozích kroků.'),
        'empty' => tr('Nejdřív vyber nebo napiš odpověď.'),
        'bad_order' => tr('Pořadí se nepodařilo ověřit. Zkus to znovu.'),
        'bad_choice' => tr('Vyber jednu z nabízených možností.'),
        'identity' => tr('Zatím tě v soupisu třídy nevidíme, postup se proto neuloží.'),
        'verify_first' => tr('Reflexe je dostupná po splněném ověření.'),
        'bad_self' => tr('Vyber, jak moc to umíš (1 až 4).'),
    ][$error] ?? tr('Krok se nepodařilo uložit. Zkus to znovu.');
}

/** Pořadí po posunu řádku na pozici $pos o jedno nahoru/dolů; null = mimo rozsah. @param list<int> $order @return list<int>|null */
function p63_move_order(array $order, int $pos, string $dir): ?array
{
    $to = $dir === 'up' ? $pos - 1 : $pos + 1;
    if ($pos < 0 || $pos >= count($order) || $to < 0 || $to >= count($order)) return null;
    [$order[$pos], $order[$to]] = [$order[$to], $order[$pos]];
    return $order;
}

/** URL kroku (jen id z katalogu) s volitelným fragmentem. */
function p63_step_url(string $pathId, string $stepId, string $fragment = ''): string
{
    return '?view=cesta&path=' . rawurlencode($pathId) . '&step=' . rawurlencode($stepId) . ($fragment !== '' ? '#' . $fragment : '');
}

/** Odevzdání kroku: výsledek se uloží do session (zobrazí se jednou na stránce kroku). */
function p63_action_submit(string $classId, string $studentKey, array $path, array $step): never
{
    $pathId = (string)$path['id'];
    $stepId = (string)$step['id'];
    $input = ['a' => is_array($_POST['a'] ?? null) ? $_POST['a'] : [], 'choice' => $_POST['choice'] ?? null,
        'order' => is_string($_POST['order'] ?? null) ? $_POST['order'] : '', 'sig' => is_string($_POST['sig'] ?? null) ? $_POST['sig'] : ''];
    $res = p63_submit_step($classId, $studentKey, $pathId, $stepId, $input);
    if (!$res['ok']) {
        $_SESSION['flash'] = p63_error_text((string)$res['error']);
        redirect_to(p63_step_url($pathId, $stepId));
    }
    unset($_SESSION['p63_order'][$pathId . '|' . $stepId]);
    if ((string)$step['type'] === 'explain') {
        $ids = p63_step_ids($path);
        $next = $ids[(int)array_search($stepId, $ids, true) + 1] ?? null;
        redirect_to($next !== null ? p63_step_url($pathId, (string)$next) : '?view=cesty');
    }
    $_SESSION['p63_result'] = ['path' => $pathId, 'step' => $stepId, 'score' => $res['score'], 'passed' => $res['passed'], 'detail' => $res['detail']];
    redirect_to(p63_step_url($pathId, $stepId, 'p63-result'));
}

/** Posun jednoho řádku Parsonovy úlohy: pořadí musí mít platný podpis a odpovídat aktuálnímu pokusu. */
function p63_action_move(string $classId, string $studentKey, array $path, array $step): never
{
    $pathId = (string)$path['id'];
    $stepId = (string)$step['id'];
    $sid = p63_student_id($classId, $studentKey);
    $attempt = (int)($_POST['attempt'] ?? 0);
    $order = is_string($_POST['order'] ?? null) && preg_match('/^\d{1,2}(,\d{1,2})*$/', (string)$_POST['order']) === 1 ? array_map('intval', explode(',', (string)$_POST['order'])) : [];
    $sig = is_string($_POST['sig'] ?? null) ? (string)$_POST['sig'] : '';
    $lines = array_values((array)($step['lines'] ?? []));
    $current = $sid === null ? 0 : p63_step_entry(p63_state($sid), $pathId, $stepId)['attempts'] + 1;
    $valid = $sid !== null && (string)$step['type'] === 'parsons' && $attempt === $current && p63_valid_order($order, count($lines))
        && hash_equals(p63_parsons_sign($order, $pathId, $stepId, $attempt, p63_parsons_secret()), $sig);
    $moved = $valid ? p63_move_order($order, (int)($_POST['pos'] ?? -1), (string)($_POST['dir'] ?? '')) : null;
    if ($moved === null) {
        $_SESSION['flash'] = p63_error_text('bad_order');
        redirect_to(p63_step_url($pathId, $stepId));
    }
    $_SESSION['p63_order'][$pathId . '|' . $stepId] = ['attempt' => $attempt, 'order' => $moved, 'sig' => p63_parsons_sign($moved, $pathId, $stepId, $attempt, p63_parsons_secret())];
    $dir = (string)$_POST['dir'];
    $to = (int)$_POST['pos'] + ($dir === 'up' ? -1 : 1);
    $_SESSION['p63_live'] = tr('Řádek „{line}“ je teď na pozici {pos} z {n}.', ['line' => (string)$lines[$moved[$to]], 'pos' => $to + 1, 'n' => count($moved)]);
    $focus = $dir === 'up' ? ($to > 0 ? 'up' : 'down') : ($to < count($moved) - 1 ? 'down' : 'up');
    redirect_to(p63_step_url($pathId, $stepId, 'p63-' . $focus . '-' . $to));
}

/** Uložení reflexe (sebehodnocení 1–4 + věta; věta zůstává jen v souboru žáka). */
function p63_action_reflect(string $classId, string $studentKey, array $path, array $step): never
{
    $pathId = (string)$path['id'];
    $res = p63_save_reflection($classId, $studentKey, $pathId, (int)($_POST['self'] ?? 0), is_string($_POST['note'] ?? null) ? (string)$_POST['note'] : '');
    if (!$res['ok']) $_SESSION['flash'] = p63_error_text((string)$res['error']);
    else $_SESSION['p63_result'] = ['path' => $pathId, 'step' => (string)$step['id'], 'reflect' => ['self' => $res['self'], 'actual' => $res['actual'], 'delta' => $res['delta']]];
    redirect_to(p63_step_url($pathId, (string)$step['id'], $res['ok'] ? 'p63-result' : ''));
}
