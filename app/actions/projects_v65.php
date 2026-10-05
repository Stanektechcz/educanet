<?php

declare(strict_types=1);

/**
 * POST akce žáka pro cyklus projektu a portfolio (v65), prefix proj65_s_. Router: app/routes.php ('actions_class').
 * CSRF, třídu i identitu žáka zajistil index.php; klíč žáka se bere vždy ze session, nikdy z formuláře.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** @return array{0:string,1:?string} [flash, id záznamu pro návrat na detail] */
function p65a_handle(string $action, string $classId, string $key): array
{
    $post = static fn(string $name): string => is_string($_POST[$name] ?? null) ? (string)$_POST[$name] : '';
    $record = proj65_find_by_id($post('id'));
    if ($record !== null && ((string)$record['class_id'] !== $classId || !proj65_is_member($record, $key))) $record = null;
    $ref = (string)($record['ref'] ?? '');
    $back = $record !== null ? (string)$record['id'] : null;
    $done = static fn(array $r, string $okText): array => [$r['ok'] ? $okText : p65v_error_text((string)$r['error']), $back];
    switch ($action) {
        case 'proj65_s_start':
            $r = proj65_start($classId, $key, $post('project_id'), $post('pitch'));
            return [$r['ok'] ? tr('Projekt je rozjetý.') : p65v_error_text((string)$r['error']), $r['ok'] ? (string)$r['id'] : null];
        case 'proj65_s_start_p60':
            $r = proj65_start_p60($classId, $key, $post('application_id'));
            return [$r['ok'] ? tr('Projekt je rozjetý.') : p65v_error_text((string)$r['error']), $r['ok'] ? (string)$r['id'] : null];
        case 'proj65_s_pitch': return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_pitch_resubmit($ref, $key, $post('pitch')), tr('Návrh je odeslaný znovu.'));
        case 'proj65_s_begin': return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_move($ref, 'in_progress', 'student', $key), tr('Můžeš pracovat s milníky.'));
        case 'proj65_s_submit': return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_submit($ref, $key, $post('url'), $post('note')), tr('Práce je odevzdaná.'));
        case 'proj65_s_ms_add':
            $tasks = is_array($_POST['task_ids'] ?? null) ? array_values(array_filter($_POST['task_ids'], 'is_string')) : [];
            return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_ms_add($ref, $key, $post('title'), $tasks), tr('Milník je přidaný.'));
        case 'proj65_s_ms_move': return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_ms_move($post('ms_id'), $key, $post('status')), tr('Milník je přesunutý.'));
        case 'proj65_s_diary': return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_diary_add($ref, $key, $post('text')), tr('Zápis je uložený.'));
        case 'proj65_s_split':
            $points = is_array($_POST['points'] ?? null) ? $_POST['points'] : [];
            return $record === null ? [p65v_error_text('invalid'), null] : $done(proj65_split_save($ref, $key, $points), tr('Rozdělení je uložené.'));
        case 'proj65_s_review':
            $r = proj65_review_save($post('review_id'), $key, $_POST['levels'] ?? [], $post('strength'), $post('suggestion'));
            return [$r['ok'] ? tr('Recenze je odeslaná. Zveřejní se po schválení učitelem.') : p65v_error_text((string)$r['error']), null];
        case 'proj65_s_portfolio_save':
            $r = port65_save_item($classId, $key, $post('key'), $post('selected') === '1', $post('reflection'));
            return [$r['ok'] ? tr('Portfolio je uložené.') : p65v_error_text((string)$r['error']), 'portfolio'];
    }
    return [p65v_error_text('invalid'), null];
}

if (str_starts_with((string)$action, 'proj65_s_')) {
    [$p65Flash, $p65Back] = p65a_handle((string)$action, (string)$classId, adaptive_student_key((string)$classId));
    $_SESSION['flash'] = $p65Flash;
    redirect_to($p65Back === 'portfolio' ? '?view=portfolio' : ($p65Back !== null ? '?view=projekt65&id=' . rawurlencode($p65Back) : '?view=projekt65'));
}
