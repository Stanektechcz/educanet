<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · POST akce učitele (prefix proj65_t_, modul 'projekty65'). Rozsah tříd vynucuje teacher59_guard_post() (politiky v
 * teacher_scope_v59.php: třída formuláře povinná a v rozsahu), oprávnění projects.manage teacher_operations_v46.php.
 * Každý handler navíc ověří, že záznam patří do POSTnuté třídy (nejde podvrhnout cizí ref) a že ji učitel smí spravovat.
 * Vždy česky.
 */

function proj65_t_actor(): string
{
    return function_exists('teacher59_current_id') ? (string)(teacher59_current_id() ?? 'teacher') : 'teacher';
}

/** Povolí jen POSTnutou třídu, kterou učitel smí spravovat. */
function proj65_t_can_class(string $classId): callable
{
    return static fn(string $c): bool => $classId !== '' && $c === $classId && (!function_exists('teacher59_can_class') || teacher59_can_class($c));
}

function proj65_t_error_text(string $code): string
{
    return [
        'level_range' => 'Vyber úroveň 1–4 u každého kritéria.', 'level1_comment' => 'Úroveň 1 vyžaduje komentář (aspoň ' . PROJ65_L1_COMMENT_MIN . ' znaků).',
        'class_out_of_scope' => 'Záznam nepatří do této třídy nebo ho nesmíš spravovat.', 'invalid_transition' => 'Tenhle krok teď není možný.',
        'peer_few' => 'Peer review se přeskakuje: odevzdané jsou méně než ' . PROJ65_PEER_MIN_SUBMISSIONS . ' práce.', 'peer_off' => 'U projektu není peer review zapnuté.',
        'criteria_count' => 'Rubrika musí mít 3–5 kritérií.', 'competency_unknown' => 'Neznámá kompetence.', 'level_text' => 'Popis úrovní nesmí být prázdný (max. 200 znaků).',
        'criterion_text' => 'Název kritéria je povinný (max. 80 znaků), popis max. 300.', 'no_suggestion' => 'Není z čeho navrhnout (málo rozdělení bodů nebo tým < 3).',
        'not_found' => 'Záznam nebyl nalezen.',
    ][$code] ?? 'Akci se nepodařilo provést (' . $code . ').';
}

/** @return string text flash zprávy */
function proj65_teacher_run(string $action, string $classId): string
{
    $post = static fn(string $n): string => is_string($_POST[$n] ?? null) ? (string)$_POST[$n] : '';
    $can = proj65_t_can_class($classId);
    $by = proj65_t_actor();
    $res = static fn(array $r, string $ok): string => !empty($r['ok']) ? $ok : proj65_t_error_text((string)($r['error'] ?? 'invalid'));
    switch ($action) {
        case 'proj65_t_settings':
            if (!$can($classId)) return proj65_t_error_text('class_out_of_scope');
            return proj65_settings_save($classId, $post('project_id'), $post('cycle_mode'), !empty($_POST['peer']), !empty($_POST['milestones'])) ? 'Nastavení projektu je uložené.' : 'Nastavení se nepodařilo uložit.';
        case 'proj65_t_bulk_approve':
            $refs = is_array($_POST['refs'] ?? null) ? array_values(array_filter($_POST['refs'], 'is_string')) : [];
            $r = proj65_bulk_approve($refs, $can, $by);
            return 'Schváleno: ' . $r['approved'] . ', přeskočeno: ' . count($r['skipped']) . ($r['truncated'] ? ' (zpracováno prvních ' . PROJ65_BULK_MAX . ')' : '') . '.';
        case 'proj65_t_reject': return $res(proj65_reject($post('ref'), $can, $by), 'Návrh je vrácený k úpravě.');
        case 'proj65_t_peer_open': return $can($classId) ? $res(proj65_peer_open($classId, $post('project_id'), $by), 'Peer review je otevřené.') : proj65_t_error_text('class_out_of_scope');
        case 'proj65_t_moderate': return $res(proj65_review_moderate($post('review_id'), $post('decision'), $can, $by), 'Recenze je vyřízená.');
        case 'proj65_t_apply_factor': return $res(proj65_factor_apply($post('ref'), $can), 'Návrh faktoru přínosu je zapsaný do hodnocení týmu.');
        case 'proj65_t_rubric_clone':
            if (!$can($classId)) return proj65_t_error_text('class_out_of_scope');
            return $res(proj65_rubric_clone($classId, $post('project_id'), is_array($_POST['criteria'] ?? null) ? $_POST['criteria'] : [], $by), 'Rubrika je uložená.');
        case 'proj65_t_grade': return proj65_teacher_grade($post('ref'), $can, $by);
    }
    return 'Neznámá akce.';
}

function proj65_teacher_grade(string $ref, callable $can, string $by): string
{
    $record = proj65_get($ref);
    $rubric = $record !== null ? proj65_rubric_for($record) : null;
    if ($record === null || $rubric === null) return proj65_t_error_text('not_found');
    $levels = proj65_levels_from_input($rubric, $_POST['levels'] ?? []);
    $comments = is_array($_POST['comments'] ?? null) ? array_map(static fn($c): string => is_string($c) ? $c : '', $_POST['comments']) : [];
    $extra = ['teacher_comment' => $_POST['teacher_comment'] ?? '', 'strengths' => $_POST['strengths'] ?? '', 'next_step' => $_POST['next_step'] ?? '', 'grade' => $_POST['grade'] ?? ''];
    $r = proj65_grade($ref, $levels, $comments, $extra, ($_POST['publish'] ?? '') === '1', $can, $by);
    if (empty($r['ok'])) return proj65_t_error_text((string)$r['error']);
    $ev = is_array($r['evidence'] ?? null) ? ' Důkazy kompetencí: zapsáno ' . (int)$r['evidence']['emitted'] . ', přeskočeno ' . (int)$r['evidence']['skipped'] . '.' : '';
    return (($_POST['publish'] ?? '') === '1' ? 'Hodnocení je zveřejněné (verze ' . (int)$r['version'] . ').' : 'Koncept hodnocení je uložený.') . $ev;
}

/** Zpracuje POST prefixu proj65_t_ (voláno z teacher58_modules()['projekty65']['post']). */
function proj65_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    $_SESSION['flash'] = proj65_teacher_run($action, $classId);
    redirect_to('teacher.php?tab=projekty65' . ($classId !== '' ? '&class=' . rawurlencode($classId) : ''));
}
