<?php

declare(strict_types=1);

/**
 * POST v55_* – tutoriál a bonus v hodině.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (str_starts_with($action, 'v55_')) {
    $v55Class = current_class_id($modules);
    if ($v55Class === null) { $_SESSION['flash'] = tr('Nejdřív se přihlas.'); redirect_to('?view=home'); }
    $v55Session = sess53_find((string)($_POST['session'] ?? ''));
    if (!is_array($v55Session) || (string)$v55Session['class_id'] !== $v55Class) { $_SESSION['flash'] = tr('Hodina nebyla nalezena.'); redirect_to('?view=dashboard'); }
    $v55Key = adaptive_student_key((string)$v55Class);
    $v55Label = trim((string)($_SESSION['student_label'] ?? ''));
    if ($v55Key === '') { $_SESSION['flash'] = tr('Nepodařilo se určit studentský profil.'); redirect_to('?view=dashboard'); }
    try {
        if ($action === 'v55_tutorial_done') {
            v55_mark_step((string)$v55Session['id'], $v55Key, $v55Label, 'tutorial');
            $_SESSION['flash'] = tr('Tutoriál odškrtnutý. Pokračuj samostatnou prací.');
        } elseif ($action === 'v55_bonus_start') {
            $v55Window = v55_block_window($schoolYear, (string)$v55Class, (string)$v55Session['date']);
            $v55Assignment = v55_bonus_assignment((string)$v55Class, $modules[$v55Class], ['topics' => [], 'title' => (string)$v55Session['title']], $v55Window);
            if ((string)$v55Assignment['variant'] === 'none') {
                $_SESSION['flash'] = tr('Do konce hodiny už nezbývá dost času, bonus se dnes nezadává.');
            } else {
                v55_bonus_start((string)$v55Session['id'], $v55Key, $v55Assignment);
                $_SESSION['flash'] = tr('Rozšiřující zadání je spuštěné. Máš na něj {n} minut.', ['n' => (int)$v55Assignment['minutes']]);
            }
        } elseif ($action === 'v55_bonus_submit') {
            v55_bonus_submit((string)$v55Session['id'], $v55Key, (array)($_POST['answers'] ?? []));
            $_SESSION['flash'] = tr('Bonus je odevzdaný. Výsledek uvidíš, jakmile ho učitel projde.');
        }
    } catch (Throwable $e) {
        $_SESSION['flash'] = $e->getMessage();
    }
    redirect_to('?view=hodina');
}
