<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/ui/stepper_v79_views.php';

/**
 * EDUCANET v79 · Reflexe po hodině jako stepper: Stav → Kde jsme skončili → Co upravit → Hotovo (souhrn + uložení).
 * Jeden formulář s akcí lr73_save (stejná pole jako v73); Zpět/Další = sp79_next/sp79_back (koncept jen v relaci).
 * Validace kroků používá stejná pravidla jako lr73_validate (stav povinný, text ≤ 300 znaků, bez celého jména žáka). Cockpit česky.
 */

const LR79_LABELS = ['Stav', 'Kde jsme skončili', 'Co upravit', 'Hotovo'];

function sp79_def_reflexe(): array
{
    return [
        'perm' => 'content.manage',
        'fields' => [
            'class_id' => ['type' => 'enum', 'values' => LR73_CLASSES], 'lesson' => ['type' => 'int', 'range' => [1, 28]],
            'state' => ['type' => 'enum', 'values' => array_keys(LR73_STATES)],
            'stop_seg' => ['type' => 'int', 'range' => [0, 99]], 'stop_task' => ['type' => 'int', 'range' => [0, 99]],
            'picks_worked' => ['type' => 'enums', 'values' => array_keys(LR73_PICKS_WORKED)], 'picks_adjust' => ['type' => 'enums', 'values' => array_keys(LR73_PICKS_ADJUST)],
            'worked' => ['type' => 'text', 'max' => LR73_TEXT_MAX], 'adjust' => ['type' => 'text', 'max' => LR73_TEXT_MAX], 'next' => ['type' => 'text', 'max' => LR73_TEXT_MAX],
        ],
        'scope' => static fn(array $d): string => $d['class_id'] . '|' . $d['lesson'],
        'validate' => 'lr79_validate',
        'query' => static fn(array $d, int $step): array => ['class' => $d['class_id'], 'lesson' => $d['lesson']],
    ];
}

/** Chyby kroku (id pole → zpráva); stejná pravidla jako lr73_validate. @return array<string,string> */
function lr79_validate(int $step, array $d): array
{
    $err = [];
    if ($d['class_id'] === '' || $d['lesson'] === '' || $d['lesson'] === 'x') return ['lr73-state-cele' => 'Třída nebo lekce není platná – otevřete Reflexi hodiny znovu ze seznamu.'];
    if ($step === 1 && $d['state'] === '') $err['lr73-state-cele'] = 'Vyberte, jak hodina proběhla (odučeno celé / část / neodučeno).';
    if ($step === 2 && ($d['stop_seg'] === 'x' || $d['stop_task'] === 'x')) $err['lr73-stop-seg'] = 'Vyberte úsek nebo úkol ze seznamu.';
    if ($step === 3) {
        foreach (['worked' => 'Text „co fungovalo“', 'adjust' => 'Text „co upravit“', 'next' => 'Poznámka na příště'] as $key => $label) {
            try {
                $text = lr73_clean_text($d[$key], $label);
                if (lr73_roster_hit((string)$d['class_id'], $text)) $err['lr73-' . $key] = $label . ' obsahuje jméno žáka. Reflexe je o hodině – jména ani známky sem nepište.';
            } catch (RuntimeException $e) {
                $err['lr73-' . $key] = $e->getMessage();
            }
        }
    }
    return $err;
}

/** Krok, rozsah a koncept pro záložku (třída a lekce už vybrané). */
function lr79_state(string $classId, int $lesson): array
{
    $draft = sp79_draft_for('reflexe', $classId . '|' . $lesson);
    $flash = sp79_teacher_flash();
    $done = $flash !== null && $flash['type'] === 'ok' && $draft['data'] !== [] && (int)$draft['step'] === 4;
    $failed = $flash !== null && $flash['type'] === 'error' && $draft['data'] !== [] && (int)$draft['step'] === 4;
    $cur = $done ? 4 : sp79_current_step('reflexe', (int)$draft['max'], (int)$draft['step']);
    $errors = $cur === (int)$draft['step'] ? (array)$draft['errors'] : [];
    if ($done) sp79_draft_clear('reflexe');
    elseif ($errors !== []) sp79_draft_put('reflexe', ['errors' => []] + $draft);
    else sp79_track('reflexe', $draft, $cur);
    return ['draft' => $draft, 'data' => (array)$draft['data'], 'cur' => $cur, 'errors' => $errors, 'done' => $done, 'failed' => $failed, 'flash' => $flash];
}

function lr79_url(string $classId, int $lesson, int $step): string
{
    return '?' . http_build_query(['tab' => 'reflexe73', 'class' => $classId, 'lesson' => (string)$lesson, 'krok' => (string)$step]);
}

/** Pruh kroků Reflexe. */
function lr79_bar_html(string $classId, int $lesson, array $st): string
{
    return sp79_bar_html(LR79_LABELS, $st['cur'], $st['done'] ? 3 : max(1, (int)$st['draft']['max']), static fn(int $k): string => lr79_url($classId, $lesson, $k), sp79_words());
}

/** Volby (checkboxy) s hodnotami z konceptu. */
function lr79_picks_html(string $name, array $options, string $legend, array $checked): string
{
    $html = '<fieldset class="c70-fieldset lr73-picks"><legend>' . e($legend) . '</legend>';
    foreach ($options as $key => $label) {
        $id = 'lr73-' . $name . '-' . $key;
        $html .= '<label class="lr73-pick" for="' . e($id) . '"><input type="checkbox" id="' . e($id) . '" name="' . e($name) . '[]" value="' . e((string)$key) . '"'
            . (in_array((string)$key, $checked, true) ? ' checked' : '') . '> ' . e((string)$label) . '</label>';
    }
    return $html . '</fieldset>';
}

function lr79_textarea_html(string $name, string $label, string $hint, string $value, array $errors): string
{
    $id = 'lr73-' . $name;
    return '<label class="lr73-field" for="' . e($id) . '"><span>' . e($label) . '</span><textarea id="' . e($id) . '" name="' . e($name) . '" maxlength="' . LR73_TEXT_MAX
        . '" rows="2" data-sp79-msg="' . e('Text je delší než ' . LR73_TEXT_MAX . ' znaků – zkraťte ho.') . '"' . sp79_aria($errors, $id, 'lr73-privacy ' . $id . '-h') . '>' . e($value) . '</textarea>'
        . '<small id="' . e($id) . '-h">' . e($hint) . ' Nejvýš ' . LR73_TEXT_MAX . ' znaků.</small></label>' . sp79_field_error($errors, $id);
}

/** Popisky vyplněného konceptu pro souhrn. */
function lr79_summary_rows(array $d, array $lesson, string $classId, int $n): array
{
    $timeline = array_values((array)$lesson['timeline']);
    $tasks = array_values((array)$lesson['tasks']);
    $stop = [];
    if ($d['stop_seg'] !== '' && isset($timeline[(int)$d['stop_seg']])) $stop[] = (int)$timeline[(int)$d['stop_seg']]['from'] . '–' . (int)$timeline[(int)$d['stop_seg']]['to'] . ' min · ' . (string)$timeline[(int)$d['stop_seg']]['phase'];
    if ($d['stop_task'] !== '' && isset($tasks[(int)$d['stop_task']])) $stop[] = 'úkol ' . ((int)$d['stop_task'] + 1) . ': ' . mb_strimwidth((string)$tasks[(int)$d['stop_task']]['text'], 0, 80, '…');
    $picks = static fn(array $keys, array $all): string => implode(', ', array_map(static fn(string $k): string => (string)($all[$k] ?? $k), $keys));
    $u = static fn(int $k): string => lr79_url($classId, $n, $k);
    return [
        ['Jak hodina proběhla', LR73_STATES[$d['state']] ?? '', 1, $u(1)],
        ['Kde jsme skončili', implode(' · ', $stop), 2, $u(2)],
        ['Co fungovalo', trim($picks((array)$d['picks_worked'], LR73_PICKS_WORKED) . ((string)$d['worked'] !== '' ? ' – ' . $d['worked'] : ''), ' –'), 3, $u(3)],
        ['Co upravit', trim($picks((array)$d['picks_adjust'], LR73_PICKS_ADJUST) . ((string)$d['adjust'] !== '' ? ' – ' . $d['adjust'] : ''), ' –'), 3, $u(3)],
        ['Poznámka na příště', (string)$d['next'], 3, $u(3)],
    ];
}

/** Formulář reflexe po krocích (lr73_save). Koncept z relace předvyplní všechny kroky. */
function lr79_form_html(string $classId, array $lesson, string $date, string $csrf, array $st): string
{
    $n = (int)$lesson['number'];
    $cur = (int)$st['cur'];
    $e = $st['errors'];
    $d = (array)$st['data'] + ['state' => '', 'stop_seg' => '', 'stop_task' => '', 'picks_worked' => [], 'picks_adjust' => [], 'worked' => '', 'adjust' => '', 'next' => ''];
    $w = sp79_words();
    $html = sp79_errors_html($e, $w) . '<form method="post" action="teacher.php" class="c70-form lr73-form sp79-form" data-sp79><input type="hidden" name="csrf" value="' . e($csrf) . '">'
        . '<input type="hidden" name="action" value="lr73_save"><input type="hidden" name="return_tab" value="reflexe73">'
        . '<input type="hidden" name="class_id" value="' . e($classId) . '"><input type="hidden" name="lesson" value="' . $n . '">' . sp79_hidden_html('reflexe', $cur);
    $html .= sp79_panel_open(1, $cur, LR79_LABELS[0]) . '<fieldset class="c70-fieldset lr73-state" data-sp79-group' . (isset($e['lr73-state-cele']) ? ' aria-describedby="lr73-state-cele-err"' : '') . '><legend>Jak hodina proběhla? <span class="c70-muted">(povinné, stačí jedno klepnutí)</span></legend>';
    foreach (LR73_STATES as $key => $label) {
        $html .= '<label class="lr73-choice" for="lr73-state-' . $key . '"><input type="radio" id="lr73-state-' . $key . '" name="state" value="' . $key . '" required'
            . ($d['state'] === $key ? ' checked' : '') . ($key === 'cele' ? ' data-sp79-msg="Vyberte, jak hodina proběhla (odučeno celé / část / neodučeno)."' : '') . '> ' . e($label) . '</label>';
    }
    $html .= '</fieldset>' . sp79_field_error($e, 'lr73-state-cele') . '</section>';
    $html .= sp79_panel_open(2, $cur, LR79_LABELS[1]) . '<p class="c70-muted">Nepovinné – pomůže příště navázat. Stačí úsek plánu, nebo úkol.</p><div class="lr73-row"><label class="lr73-field" for="lr73-stop-seg"><span>Kde jsme skončili – úsek plánu</span><select id="lr73-stop-seg" name="stop_seg"' . sp79_aria($e, 'lr73-stop-seg') . '><option value="">— nevyplněno —</option>';
    foreach (array_values($lesson['timeline']) as $i => $s) $html .= '<option value="' . $i . '"' . ($d['stop_seg'] === (string)$i ? ' selected' : '') . '>' . (int)$s['from'] . '–' . (int)$s['to'] . ' min · ' . e(mb_strimwidth((string)$s['phase'], 0, 60, '…')) . '</option>';
    $html .= '</select></label><label class="lr73-field" for="lr73-stop-task"><span>… nebo úkol</span><select id="lr73-stop-task" name="stop_task"><option value="">— nevyplněno —</option>';
    foreach (array_values($lesson['tasks']) as $i => $t) $html .= '<option value="' . $i . '"' . ($d['stop_task'] === (string)$i ? ' selected' : '') . '>' . ($i + 1) . '. ' . e(mb_strimwidth((string)$t['text'], 0, 70, '…')) . '</option>';
    $html .= '</select></label></div>' . sp79_field_error($e, 'lr73-stop-seg') . '</section>';
    $html .= sp79_panel_open(3, $cur, LR79_LABELS[2])
        . '<p class="c70-note" id="lr73-privacy">Reflexe je o hodině, ne o žácích: <strong>nepište jména žáků ani známky.</strong> Text s celým jménem žáka ze seznamu třídy se neuloží.</p>'
        . lr79_picks_html('picks_worked', LR73_PICKS_WORKED, 'Co fungovalo', (array)$d['picks_worked']) . lr79_textarea_html('worked', 'Co fungovalo – vlastními slovy (nepovinné)', 'Např. „ukázka dig +short pomohla“.', (string)$d['worked'], $e)
        . lr79_picks_html('picks_adjust', LR73_PICKS_ADJUST, 'Co upravit', (array)$d['picks_adjust']) . lr79_textarea_html('adjust', 'Co upravit – vlastními slovy (nepovinné)', 'Např. „úkol 3 zkrátit o 5 min“.', (string)$d['adjust'], $e)
        . lr79_textarea_html('next', 'Poznámka na příště (nepovinné)', 'Ukáže se v Dnešní hodině jako „Minule“.', (string)$d['next'], $e) . '</section>';
    $html .= sp79_panel_open(4, $cur, LR79_LABELS[3]) . '<p>Zkontrolujte souhrn a uložte. Upravit jde kterýkoli krok – nic se neztratí.</p>'
        . sp79_summary_html(lr79_summary_rows($d, $lesson, $classId, $n), $w)
        . '<p class="c70-muted">Datum hodiny: ' . e(lt70_date($date)) . ' (podle kalendáře). Stejnou lekci můžete uložit znovu – přepíše se vaše poslední reflexe.</p></section>';
    return $html . sp79_nav_html('teacher', $cur, 4, LR79_LABELS, $w, '<button type="submit" class="ds-btn ds-btn--primary lr73-submit" data-sp79-validate>Uložit reflexi</button>') . '</form>';
}

/** Výsledek po uložení (krok Hotovo): stav a další kroky. */
function lr79_done_html(string $classId, int $n): string
{
    $links = array_filter([lt70_tab_link('hodina', $classId, 'Dnešní hodina'), $n < LT70_LESSONS_MAX ? lt70_tab_link('reflexe73', $classId, 'Reflexe lekce ' . ($n + 1), ['lesson' => $n + 1]) : '']);
    return sp79_status_html('ok', 'Hotovo – reflexe lekce ' . $n . ' je uložená.', 'Dnešní hodina ji příště ukáže jako „Minule“. Uloženou reflexi vidíte v seznamu vpravo.',
        $links !== [] ? '<p class="c70-links">' . implode(' · ', $links) . '</p>' : '');
}
