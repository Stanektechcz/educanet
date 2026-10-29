<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79_student.php';

/**
 * EDUCANET v79 · připnutí práce do portfolia po krocích: Odkaz → Reflexe → Uložit (souhrn + pf75_s_pin beze změny).
 * Koncept (druh, název, odkaz, úloha/projekt, reflexe) je jen v relaci žáka; validace kroků = stejná pravidla jako pf75_pin.
 * Texty přes tr() (doména stepper_v79). Odkaz na práci se v URL nikdy neobjeví.
 */

function pf79_labels(): array
{
    return [tr('Odkaz'), tr('Reflexe'), tr('Uložit')];
}

function sp79_def_portfolio(): array
{
    return [
        'fields' => [
            'kind' => ['type' => 'enum', 'values' => array_keys(PF75_KINDS)], 'title' => ['type' => 'text', 'max' => PF75_TITLE_MAX], 'url' => ['type' => 'text', 'max' => PF75_URL_MAX],
            'task' => ['type' => 'text', 'max' => 40], 'project' => ['type' => 'text', 'max' => 120], 'reflection' => ['type' => 'text', 'max' => PF75_TEXT_MAX],
        ],
        'scope' => static fn(array $d): string => 'pin',
        'validate' => 'pf79_validate',
        'query' => static fn(array $d, int $step): array => [],
    ];
}

/** Vyřešené úlohy a projekty žáka (kandidáti k připnutí). @return array{0:list<string>,1:list<array>} */
function pf79_candidates(string $classId, string $studentKey): array
{
    $state = tb75_student_state($classId, tb75_student_ref($classId, $studentKey));
    $solved = array_values(array_filter(tb75_tasks_for_class($classId), static fn(string $t): bool => tb75_entry($state, $t)['solved'] > 0));
    $projects = function_exists('port65_candidates') ? port65_candidates($classId, $studentKey) : [];
    return [$solved, $projects];
}

/** Chyby kroku – stejná pravidla jako pf75_pin. @return array<string,string> */
function pf79_validate(int $step, array $d): array
{
    if ($step === 2) return mb_strlen((string)$d['reflection']) > PF75_TEXT_MAX ? ['pf79-reflection' => tr('Reflexe může mít nejvýš {n} znaků.', ['n' => PF75_TEXT_MAX])] : [];
    if ($step !== 1) return [];
    if ($d['kind'] === '') return ['pf79-kind-link' => tr('Vyber, co chceš připnout.')];
    if ($d['kind'] === 'link') {
        $err = [];
        if (trim((string)$d['title']) === '') $err['pf75-title'] = tr('Doplň název práce.');
        if (pf75_url($d['url']) === '') $err['pf75-url'] = tr('Odkaz musí začínat https:// a vést na veřejnou stránku.');
        return $err;
    }
    $classId = (string)($GLOBALS['classId'] ?? '');
    $studentKey = $classId !== '' && function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    [$solved, $projects] = $studentKey !== '' ? pf79_candidates($classId, $studentKey) : [[], []];
    if ($d['kind'] === 'task') return in_array($d['task'], $solved, true) ? [] : ['pf75-task' => tr('Připnout jde jen vyřešenou úlohu.')];
    return in_array($d['project'], array_map(static fn(array $c): string => (string)($c['key'] ?? ''), $projects), true) ? [] : ['pf75-proj' => tr('Projekt se nepodařilo najít.')];
}

/** Uložila se práce z konceptu? (po odeslání pf75_s_pin) */
function pf79_saved(array $pins, array $d, int $since): bool
{
    foreach ($pins as $p) {
        if ((string)$p['kind'] !== $d['kind'] || (int)strtotime((string)($p['at'] ?? '')) < $since - 5) continue;
        if ($d['kind'] === 'link' ? (string)$p['url'] === pf75_url($d['url']) : (string)$p['ref'] === (string)($d['kind'] === 'task' ? $d['task'] : $d['project'])) return true;
    }
    return false;
}

function pf79_url(int $step): string
{
    return '?' . http_build_query(['view' => 'portfolio', 'krok' => (string)$step]) . '#sp79-top';
}

/** Stepper připnutí (místo tří rozbalovacích formulářů v75). */
function pf79_render(string $classId, string $studentKey, array $pins, string $csrf): string
{
    $draft = sp79_draft_for('portfolio', 'pin');
    $d = (array)$draft['data'] + ['kind' => '', 'title' => '', 'url' => '', 'task' => '', 'project' => '', 'reflection' => ''];
    $flash = (string)($GLOBALS['flash'] ?? '');
    $sent = $draft['data'] !== [] && (int)$draft['step'] === 3 && $flash !== '';
    $done = $sent && pf79_saved($pins, $d, (int)$draft['at']);
    $cur = $done ? 3 : sp79_current_step('portfolio', max(1, (int)$draft['max']), (int)$draft['step']);
    $e = $cur === (int)$draft['step'] ? (array)$draft['errors'] : [];
    if ($done) sp79_draft_clear('portfolio');
    elseif ($e !== []) sp79_draft_put('portfolio', ['errors' => []] + $draft);
    else sp79_track('portfolio', $draft, $cur);
    $labels = pf79_labels();
    $w = sp79s_words();
    $html = sp79_student_assets_html() . '<div class="pf79 p65-noprint"><h3>' . e(tr('Připnout novou práci')) . '</h3>' . sp79_bar_html($labels, $cur, $done ? 3 : max(1, (int)$draft['max']), static fn(int $k): string => pf79_url($k), $w);
    if ($done) {
        return $html . sp79_status_html('ok', tr('Hotovo – práce je připnutá v portfoliu.'), tr('Najdeš ji v seznamu výše. Reflexi můžeš kdykoli upravit.'), '<p><a class="ui-link" href="' . e(pf79_url(1)) . '">' . e(tr('Připnout další práci')) . '</a></p>') . '</div>';
    }
    if ($sent) $html .= sp79_status_html('danger', tr('Práci se nepodařilo připnout.'), tr('Důvod je nahoře na stránce. Údaje zůstaly – oprav první krok a zkus to znovu.'));
    [$solved, $projects] = pf79_candidates($classId, $studentKey);
    $html .= sp79_errors_html($e, $w) . '<form method="post" class="l75-form" data-sp79>' . $csrf . '<input type="hidden" name="action" value="pf75_s_pin">' . sp79_hidden_html('portfolio', $cur);
    $radio = static function (string $kind, string $label) use ($d, $e): string {
        return '<label class="pf79-kind" for="pf79-kind-' . $kind . '"><input type="radio" id="pf79-kind-' . $kind . '" name="kind" value="' . $kind . '"' . ($d['kind'] === $kind ? ' checked' : '')
            . ($kind === 'link' ? ' required data-sp79-msg="' . e(tr('Vyber, co chceš připnout.')) . '"' . sp79_aria($e, 'pf79-kind-link') : '') . '> ' . e($label) . '</label>';
    };
    $html .= sp79_panel_open(1, $cur, $labels[0]) . '<fieldset class="pf79-kinds"><legend>' . e(tr('Co chceš připnout?')) . '</legend>' . $radio('link', tr('Odkaz na grafiku nebo web'))
        . ($solved !== [] ? $radio('task', tr('Vyřešenou úlohu')) : '') . ($projects !== [] ? $radio('project', tr('Projekt')) : '') . '</fieldset>' . sp79_field_error($e, 'pf79-kind-link')
        . '<fieldset class="pf79-group"><legend>' . e(tr('Odkaz na práci')) . '</legend>'
        . '<label for="pf75-title">' . e(tr('Název práce')) . '</label><input id="pf75-title" name="title" maxlength="' . PF75_TITLE_MAX . '" value="' . e((string)$d['title']) . '"' . sp79_aria($e, 'pf75-title') . '>' . sp79_field_error($e, 'pf75-title')
        . '<label for="pf75-url">' . e(tr('Odkaz (jen https://…)')) . '</label><input id="pf75-url" name="url" type="text" inputmode="url" autocomplete="off" maxlength="' . PF75_URL_MAX . '" placeholder="https://" value="' . e((string)$d['url']) . '"' . sp79_aria($e, 'pf75-url', 'pf75-url-h') . '>'
        . '<small id="pf75-url-h" class="l75-muted">' . e(tr('Jen veřejná stránka přes https. Nevkládej odkazy s heslem ani osobními údaji.')) . '</small>' . sp79_field_error($e, 'pf75-url') . '</fieldset>';
    if ($solved !== []) {
        $html .= '<fieldset class="pf79-group"><legend>' . e(tr('Vyřešená úloha')) . '</legend><label for="pf75-task">' . e(tr('Úloha')) . '</label><select id="pf75-task" name="task"' . sp79_aria($e, 'pf75-task') . '>';
        foreach ($solved as $t) $html .= '<option value="' . e($t) . '"' . ($d['task'] === $t ? ' selected' : '') . '>' . e(tb75_t_title($t)) . '</option>';
        $html .= '</select>' . sp79_field_error($e, 'pf75-task') . '</fieldset>';
    }
    if ($projects !== []) {
        $html .= '<fieldset class="pf79-group"><legend>' . e(tr('Projekt')) . '</legend><label for="pf75-proj">' . e(tr('Projekt')) . '</label><select id="pf75-proj" name="project"' . sp79_aria($e, 'pf75-proj') . '>';
        foreach ($projects as $c) $html .= '<option value="' . e((string)$c['key']) . '"' . ($d['project'] === (string)$c['key'] ? ' selected' : '') . '>' . e((string)$c['title']) . '</option>';
        $html .= '</select>' . sp79_field_error($e, 'pf75-proj') . '</fieldset>';
    }
    $html .= '</section>' . sp79_panel_open(2, $cur, $labels[1]) . '<label for="pf79-reflection">' . e(tr('Co ses naučil/a? (nepovinné, nejvýš {n} znaků)', ['n' => PF75_TEXT_MAX])) . '</label>'
        . '<textarea id="pf79-reflection" name="reflection" rows="4" maxlength="' . PF75_TEXT_MAX . '"' . sp79_aria($e, 'pf79-reflection', 'pf79-reflection-h') . '>' . e((string)$d['reflection']) . '</textarea>'
        . '<small id="pf79-reflection-h" class="l75-muted">' . e(tr('Třeba: co bylo nejtěžší, co bys příště udělal/a jinak.')) . '</small>' . sp79_field_error($e, 'pf79-reflection') . '</section>';
    $what = match ($d['kind']) { 'task' => tb75_t_title((string)$d['task']), 'project' => (string)(array_column($projects, 'title', 'key')[$d['project']] ?? ''), default => (string)$d['title'] };
    $rows = [[tr('Co připínáš'), $d['kind'] !== '' ? pf75_t_kind((string)$d['kind']) : '', 1, pf79_url(1)], [tr('Práce'), $what, 1, pf79_url(1)]];
    if ($d['kind'] === 'link') $rows[] = [tr('Odkaz'), (string)$d['url'], 1, pf79_url(1)];
    $rows[] = [tr('Reflexe'), (string)$d['reflection'], 2, pf79_url(2)];
    $html .= sp79_panel_open(3, $cur, $labels[2]) . '<p>' . e(tr('Zkontroluj souhrn. Vidíš to jen ty a tvoji učitelé.')) . '</p>' . sp79_summary_html($rows, $w) . '</section>';
    return $html . sp79_nav_html('student', $cur, 3, $labels, $w, '<button type="submit" class="ds-btn ds-btn--primary" data-sp79-validate>' . e(tr('Připnout')) . '</button>') . '</form></div>';
}
