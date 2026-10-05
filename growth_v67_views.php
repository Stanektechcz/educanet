<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v67 · profil žáka: záložka „Můj růst“ (cíle, příběh růstu, sdílení), SVG přehled oblastí a veřejné pohledy.
 *
 * Texty přes tr() (doména growth_v67); názvy kompetencí z katalogu přes comp62_t_label(), názvy cest a projektů jsou obsah
 * a zůstávají česky (atribut lang). Vizuál je z komponent v61 (.ui-card, .ui-btn, .ui-empty); assets/growth-v67.css doplňuje
 * jen osu a pruhy. Všechny odkazy a formuláře jsou funkční bez JavaScriptu.
 */

require_once __DIR__ . '/growth_v67.php';
require_once __DIR__ . '/competency_v62_views.php';

function grow67_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/growth-v67.css?v=67.0')) . '">' . "\n";
}

function grow67_area_label(string $areaId, string $fallback): string
{
    $map = ['site' => trm('Sítě'), 'linux' => trm('Linux'), 'web' => trm('Web'), 'grafika' => trm('Grafika')];
    $text = $map[$areaId] ?? null;
    return $text !== null ? tr($text) : $fallback;
}

function grow67_state_text(string $state): string
{
    return comp62_state_view($state)['text'];
}

function grow67_date_html(int $ts): string
{
    return '<time datetime="' . e(date('Y-m-d', $ts)) . '">' . e(date('j. n. Y', $ts)) . '</time>';
}

/** Stavy kompetencí po oblastech: pruh v SVG + tabulka pro čtečky (informace není jen barva). */
function grow67_areas_html(string $subject, array $map): string
{
    $areas = [];
    foreach (comp62_competencies($subject) as $id => $c) {
        $state = (string)($map[$id]['state'] ?? 'neovereno');
        $areas[$c['area']]['label'] = grow67_area_label((string)$c['area'], (string)$c['area_label']);
        $areas[$c['area']]['n'][$state] = ($areas[$c['area']]['n'][$state] ?? 0) + 1;
        $areas[$c['area']]['total'] = ($areas[$c['area']]['total'] ?? 0) + 1;
    }
    if ($areas === []) return '';
    $rows = '';
    $bars = '';
    $states = ['upevneno' => tr('Upevněno'), 'zvladnuto' => tr('Zvládnuto'), 'rozpracovano' => tr('Rozpracováno'), 'neovereno' => tr('Zatím neověřeno')];
    foreach ($areas as $a) {
        $x = 0.0;
        $rects = '';
        $cells = '';
        $desc = [];
        foreach ($states as $state => $stateLabel) {
            $n = (int)($a['n'][$state] ?? 0);
            $cells .= '<td>' . $n . '</td>';
            $desc[] = $stateLabel . ' ' . $n;
            if ($n === 0) continue;
            $w = round($n / $a['total'] * 100, 2);
            $rects .= '<rect class="g67-seg g67-s-' . e($state) . '" x="' . $x . '" y="0" width="' . $w . '" height="8"></rect>';
            $x += $w;
        }
        $bars .= '<li class="g67-row"><span class="g67-lbl">' . e($a['label']) . '</span><svg class="g67-bar" role="img" aria-label="' . e($a['label'] . ': ' . implode(', ', $desc)) . '" viewBox="0 0 100 8" preserveAspectRatio="none">' . $rects . '</svg></li>';
        $rows .= '<tr><th scope="row">' . e($a['label']) . '</th>' . $cells . '</tr>';
    }
    $title = tr('Kompetence podle oblastí');
    $legend = '';
    foreach ($states as $state => $stateLabel) $legend .= '<span class="g67-key g67-s-' . e($state) . '">' . e($stateLabel) . '</span>';
    $head = '';
    foreach ($states as $stateLabel) $head .= '<th scope="col">' . e($stateLabel) . '</th>';
    return '<section class="ui-card g67-areas" aria-labelledby="g67-areas-t"><h2 id="g67-areas-t">' . e($title) . '</h2>'
        . '<ul class="g67-rows">' . $bars . '</ul><p class="g67-legend">' . $legend . '</p>'
        . '<details><summary>' . e(tr('Zobrazit jako tabulku')) . '</summary><div class="ui-table-wrap"><table class="ui-table"><caption class="sr-only">' . e($title) . '</caption><thead><tr><th scope="col">' . e(tr('Oblast')) . '</th>' . $head . '</tr></thead><tbody>' . $rows . '</tbody></table></div></details></section>';
}

/** Popis jedné události časové osy (HTML). $own = vlastní profil (smí vidět názvy projektů). */
function grow67_event_html(array $e, string $classId, array $competencies, array $projectTitles, bool $own): string
{
    $type = (string)$e['type'];
    if ($type === 'competency' && isset($competencies[(string)$e['ref']])) {
        $label = comp62_t_label((string)$e['ref'], (string)$competencies[(string)$e['ref']]['label']);
        $head = $e['state'] === 'upevneno' ? tr('Upevněno') : tr('Zvládnuto');
        return e($head) . ': ' . e($label) . (!empty($e['badge']) ? ' <span class="ui-tag g67-badge">' . e(tr('odznak')) . '</span>' : '');
    }
    if ($type === 'path') {
        $path = function_exists('p63_path_for_class') ? p63_path_for_class($classId, (string)$e['ref']) : null;
        return $path === null ? '' : e(tr('Dokončená cesta')) . ': <span' . edu_content_lang_attr() . '>' . e((string)$path['title']) . '</span>';
    }
    if ($type === 'project' && $own && isset($projectTitles[(string)$e['ref']])) {
        return e(tr('Ohodnocený projekt')) . ': <span' . edu_content_lang_attr() . '>' . e($projectTitles[(string)$e['ref']]) . '</span>';
    }
    return '';
}

/** Časová osa (HTML); $own = false vynechá projekty. Prázdná osa = prázdný stav s krokem. */
function grow67_timeline_html(string $classId, string $studentKey, bool $own): string
{
    $subject = (string)comp62_subject_for_class($classId);
    $competencies = comp62_competencies($subject);
    $titles = [];
    if ($own && function_exists('proj65_for_student')) {
        foreach (proj65_for_student($classId, $studentKey) as $row) $titles[(string)($row['id'] ?? '')] = (string)($row['title'] ?? '');
    }
    $items = '';
    foreach (grow67_timeline($classId, $studentKey) as $e) {
        $html = grow67_event_html($e, $classId, $competencies, $titles, $own);
        if ($html !== '') $items .= '<li class="g67-event g67-t-' . e((string)$e['type']) . '"><span class="g67-date">' . grow67_date_html((int)$e['at']) . '</span><span class="g67-text">' . $html . '</span></li>';
    }
    if ($items === '' && !$own) return '<p class="c62-empty">' . e(tr('Zatím tu nic není.')) . '</p>';
    if ($items === '') {
        return ui67_empty_state(tr('Příběh růstu zatím nezačal'), tr('Milníky se objeví, jakmile ti některá kompetence dosáhne stavu Zvládnuto, dokončíš cestu nebo projekt.'), '?view=cesty', tr('Otevřít moje cesty'));
    }
    return '<ol class="g67-timeline">' . $items . '</ol>';
}

function grow67_form_open(string $action, string $class = ''): string
{
    return '<form method="post" action="index.php"' . ($class !== '' ? ' class="' . e($class) . '"' : '') . '><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">';
}

/** Jeden cíl: stav, týdenní kontrola, odebrání. */
function grow67_goal_html(array $g, array $competencies, string $currentState): string
{
    $c = $competencies[$g['competency']] ?? null;
    if ($c === null) return '';
    $week = grow67_week_key(time());
    $checkedNow = false;
    $last = null;
    foreach ($g['checks'] as $ck) {
        $last = $ck;
        if ((string)($ck['week'] ?? '') === $week) $checkedNow = true;
    }
    $reached = $g['reached_at'] !== null || grow67_rank($currentState) >= grow67_rank($g['target']);
    $status = $reached ? tr('Cíl splněn') : tr('Pracuješ na tom');
    $lastText = $last !== null ? tr('Poslední kontrola: {date}', ['date' => date('j. n. Y', (int)strtotime((string)$last['at']))]) : tr('Zatím bez kontroly.');
    $label = comp62_t_label((string)$g['competency'], (string)$c['label']);
    $view = comp62_state_view($currentState);
    $check = $checkedNow
        ? '<p class="g67-note">' . e(tr('Tento týden zkontrolováno.')) . '</p>'
        : grow67_form_open('grow67_goal_check') . '<input type="hidden" name="goal" value="' . e($g['id']) . '"><button class="ui-btn ui-btn--primary" type="submit">' . e(tr('Zkontrolovat pokrok')) . '</button></form>';
    return '<li class="g67-goal ui-card"><h3>' . e($label) . '</h3>'
        . '<p class="g67-meta"><span class="c62-state c62-s-' . e($currentState) . '"><span aria-hidden="true">' . e($view['icon']) . '</span> ' . e($view['text']) . '</span> · '
        . e(tr('Cíl: {state}', ['state' => grow67_state_text($g['target'])])) . ' · <strong>' . e($status) . '</strong></p><p class="g67-note">' . e($lastText) . '</p>'
        . '<div class="g67-actions">' . $check
        . grow67_form_open('grow67_goal_remove') . '<input type="hidden" name="goal" value="' . e($g['id']) . '"><button class="ui-btn ui-btn--quiet" type="submit">' . e(tr('Odebrat cíl')) . '</button></form></div></li>';
}

function grow67_add_form_html(array $state, array $competencies): string
{
    if (count($state['goals']) >= GROW67_MAX_GOALS) return '<p class="g67-note">' . e(tr('Máš tři cíle. Další přidáš, až některý odebereš.')) . '</p>';
    $used = array_column($state['goals'], 'competency');
    $options = '';
    foreach ($competencies as $id => $c) {
        if (!in_array($id, $used, true)) $options .= '<option value="' . e($id) . '">' . e(comp62_t_label((string)$id, (string)$c['label'])) . '</option>';
    }
    return grow67_form_open('grow67_goal_add', 'g67-add')
        . '<p><label class="ui-label" for="g67-comp">' . e(tr('Kompetence')) . '</label><select class="ui-select" id="g67-comp" name="competency" required>' . $options . '</select></p>'
        . '<p><label class="ui-label" for="g67-target">' . e(tr('Chci dosáhnout')) . '</label><select class="ui-select" id="g67-target" name="target">'
        . '<option value="zvladnuto">' . e(tr('Zvládnuto')) . '</option><option value="upevneno">' . e(tr('Upevněno')) . '</option></select></p>'
        . '<button class="ui-btn ui-btn--secondary" type="submit">' . e(tr('Přidat cíl')) . '</button></form>';
}

function grow67_share_form_html(array $public): string
{
    if (!grow67_public_allowed()) return '<p class="g67-note">' . e(tr('Sdílení se spolužáky je na téhle škole vypnuté.')) . '</p>';
    return grow67_form_open('grow67_share_set', 'g67-share') . '<fieldset><legend>' . e(tr('Co smějí vidět spolužáci')) . '</legend>'
        . '<p class="g67-note">' . e(tr('Výchozí je nic. Cíle nevidí nikdo kromě tebe, projekty také ne.')) . '</p>'
        . '<label class="g67-check"><input type="checkbox" name="share_competencies" value="1"' . ($public['competencies'] ? ' checked' : '') . '> ' . e(tr('Co už umím (Zvládnuto a Upevněno)')) . '</label>'
        . '<label class="g67-check"><input type="checkbox" name="share_timeline" value="1"' . ($public['timeline'] ? ' checked' : '') . '> ' . e(tr('Příběh růstu (kompetence a cesty)')) . '</label>'
        . '</fieldset><button class="ui-btn ui-btn--secondary" type="submit">' . e(tr('Uložit sdílení')) . '</button></form>';
}

/** Záložka „Můj růst“ vlastního profilu. */
function grow67_render_growth_tab(string $classId, string $studentKey): void
{
    if (!grow67_enabled($classId)) return;
    grow67_assets();
    comp62_student_assets();
    $subject = (string)comp62_subject_for_class($classId);
    $competencies = comp62_competencies($subject);
    $state = grow67_state(grow67_student_id($classId, $studentKey));
    $goals = '';
    foreach ($state['goals'] as $g) $goals .= grow67_goal_html($g, $competencies, grow67_current_state($classId, $studentKey, (string)$g['competency']));
    echo '<div class="g67-wrap"><p class="c62-intro">' . e(tr('Tady vidíš, jak ses posunul/a, a můžeš si stanovit až tři cíle. Cíle i příběh vidíš jen ty, pokud je nesdílíš.')) . '</p>'
        . '<section class="g67-section" aria-labelledby="g67-goals-t"><h2 id="g67-goals-t">' . e(tr('Moje cíle')) . '</h2>'
        . ($goals !== '' ? '<ul class="g67-goals">' . $goals . '</ul>' : ui67_empty_state(tr('Zatím nemáš žádný cíl'), tr('Vyber kompetenci, kterou chceš zvládnout, a jednou týdně zkontroluj, jak ti to jde.')))
        . grow67_add_form_html($state, $competencies) . '</section>'
        . '<section class="g67-section" aria-labelledby="g67-tl-t"><h2 id="g67-tl-t">' . e(tr('Příběh růstu')) . '</h2>' . grow67_timeline_html($classId, $studentKey, true) . '</section>'
        . '<section class="g67-section" aria-labelledby="g67-share-t"><h2 id="g67-share-t">' . e(tr('Sdílení')) . '</h2>' . grow67_share_form_html($state['public']) . '</section></div>';
}

/** Cizí profil: jen „Umím“, a jen když to žák sdílí. */
function grow67_render_public_competencies(string $classId, string $targetKey): void
{
    if (!grow67_enabled($classId) || !grow67_public_flags($classId, $targetKey)['competencies']) return;
    comp62_student_assets();
    grow67_assets();
    $subject = (string)comp62_subject_for_class($classId);
    $map = m62_student($classId, $targetKey)['map'];
    $items = '';
    foreach (comp62_competencies($subject) as $id => $c) {
        $state = (string)($map[$id]['state'] ?? 'neovereno');
        if (comp62_group_of($state) === 'umim') $items .= '<li class="c62-item"><span class="c62-icon c62-s-' . e($state) . '" aria-hidden="true">' . e(comp62_state_view($state)['icon']) . '</span><span><span class="c62-name">' . e(comp62_t_label((string)$id, (string)$c['label'])) . '</span><span class="c62-meta"><span class="c62-state c62-s-' . e($state) . '">' . e(grow67_state_text($state)) . '</span></span></span></li>';
    }
    echo '<div class="g67-wrap">' . comp62_render_group('umim', tr('Co už umí'), $items) . '</div>';
}

/** Cizí profil: sdílená časová osa bez projektů. */
function grow67_render_public_timeline(string $classId, string $targetKey): void
{
    if (!grow67_enabled($classId) || !grow67_public_flags($classId, $targetKey)['timeline']) return;
    comp62_student_assets();
    grow67_assets();
    echo '<div class="g67-wrap"><section class="g67-section" aria-labelledby="g67-tl-t"><h2 id="g67-tl-t">' . e(tr('Příběh růstu')) . '</h2>' . grow67_timeline_html($classId, $targetKey, false) . '</section></div>';
}
