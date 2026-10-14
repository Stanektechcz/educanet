<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/support_v80_1.php';

/**
 * EDUCANET v80.1 · žák: „Od učitele“ na stránce Můj pokrok – přidělené procvičování (první nesplněné je Další krok) a zpětná vazba.
 * Rozhraní přes tr() (cs/en/uk); text zpětné vazby je napsaný učitelem a zobrazuje se beze změny (obal edu_content_lang_attr()).
 * Identita žáka jen ze session (volá pg80_render_page); nic se nezapisuje.
 */

/** Název přiděleného procvičování pro žáka. */
function ts801_item_title(array $a): string
{
    $i = (string)($a['i'] ?? '');
    $sets = ['site' => tr('Sítě'), 'linux' => tr('Linux'), 'grafika' => tr('Grafika'), 'web' => tr('Web a CSS')];
    return match ((string)($a['k'] ?? '')) {
        'task' => tb75_t_title($i),
        'set' => tr('Sada úloh: {nazev}', ['nazev' => $sets[$i] ?? $i]),
        'rep' => $i === 'auto' ? tr('Opakování: tvoje nejslabší úlohy') : tr('Opakování: {nazev}', ['nazev' => tb75_t_title($i)]),
        'widget' => wg80_title($i),
        default => $i,
    };
}

/** Text tlačítka podle druhu přidělení. */
function ts801_item_button(array $a): string
{
    return match ((string)($a['k'] ?? '')) {
        'rep' => tr('Zopakovat'),
        'widget' => tr('Otevřít cvičení'),
        default => tr('Procvičit úlohu'),
    };
}

/** Další krok z nejstaršího nesplněného přidělení (nebo null). */
function ts801_next_step(array $teacher): ?array
{
    $a = $teacher['open'][0] ?? null;
    if (!is_array($a)) return null;
    return ['href' => (string)$a['status']['href'], 'kind' => 'teacher', 'arg' => ts801_item_title($a), 'button' => ts801_item_button($a)];
}

/** Sekce „Od učitele“ (nic se nevykreslí, když učitel nic neposlal). */
function ts801_section_html(array $teacher): string
{
    if ($teacher['feedback'] === [] && $teacher['open'] === [] && $teacher['done'] === []) return '';
    $kinds = ['praise' => tr('Pochvala'), 'next' => tr('Další krok'), 'fix' => tr('Oprava')];
    $html = '<section class="ds-card lr80-sec" id="ts801-od-ucitele" aria-labelledby="ts801-h"><h2 id="ts801-h">' . e(tr('Od učitele')) . '</h2>';
    if ($teacher['open'] !== [] || $teacher['done'] !== []) {
        $html .= '<h3>' . e(tr('Procvičování')) . '</h3><ul class="lr80-list">';
        foreach (array_merge($teacher['open'], $teacher['done']) as $a) {
            $st = $a['status'];
            $title = '<span' . edu_content_lang_attr() . '>' . e(ts801_item_title($a)) . '</span>';
            $progress = $st['total'] > 1 ? ' ' . e(tr('{n} z {m}', ['n' => $st['have'], 'm' => $st['total']])) : '';
            $html .= '<li>' . ($st['done'] ? $title : '<a href="' . e((string)$st['href']) . '">' . $title . '</a>')
                . ' <span class="ds-badge ' . ($st['done'] ? 'is-ok' : 'is-warn') . '">' . e($st['done'] ? tr('hotovo') : tr('čeká')) . '</span>' . $progress
                . ' <span class="lr80-muted">' . e(tr('přiděleno {datum}', ['datum' => edu_date((int)$a['at'])])) . '</span></li>';
        }
        $html .= '</ul>';
    }
    if ($teacher['feedback'] !== []) {
        $html .= '<h3>' . e(tr('Zpětná vazba')) . '</h3><ul class="lr80-list">';
        foreach ($teacher['feedback'] as $f) {
            $html .= '<li><span class="ds-badge is-info">' . e($kinds[(string)$f['k']] ?? '') . '</span> <span class="lr80-muted">' . e(edu_date((int)$f['at'])) . '</span>'
                . '<p' . edu_content_lang_attr() . '>' . nl2br(e((string)$f['t'])) . '</p></li>';
        }
        $html .= '</ul>';
    }
    return $html . '<p class="lr80-muted">' . e(tr('Zprávy a přidělení vidíš jen ty a učitelé tvé třídy. Splnění se pozná samo z tvých výsledků.')) . '</p></section>';
}
