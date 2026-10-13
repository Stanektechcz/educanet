<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · záložka „Hromadné schválení“ (modul `hromadne74`, sekce Výuka) a karta v Schvalování lekcí. Cockpit česky.
 * v79 stepper (bulk_approval_v79.php): 1 Rozsah (týden / třída / celý rok, lekce a/nebo ŠVP) → 2 Náhled změn → 3 Potvrzení → 4 Výsledek po položkách
 * s vrácením (existující akce lc72_return / lc72_svp_return). GET jen ?class= (obecná kontrola rozsahu), ?rozsah=, ?tyden=, ?co_*=, ?vysledek=, ?krok=
 * – žádný GET parametr registru (krok má jen informativní politiku hromadne74|krok). Formuláře jen s oprávněním content.manage. Každý výstup přes e(); data žáků se nezobrazují.
 */

require_once __DIR__ . '/bulk_approval_v74.php';

function ba74_can_manage(): bool
{
    return function_exists('teacher_permission') && teacher_permission('content.manage');
}

function ba74_class_name(array $modules, string $class): string
{
    return (string)($modules[$class]['name'] ?? $class);
}

function ba74_render_tab(array $modules, string $classId, string $csrf): void
{
    require_once __DIR__ . '/bulk_approval_v79.php';   // v79: stepper Rozsah → Náhled změn → Potvrzení → Výsledek
    $classes = function_exists('c70_classes') ? c70_classes() : [];
    $today = date('Y-m-d');
    $scope = is_string($_GET['rozsah'] ?? null) && isset(BA74_SCOPES[(string)$_GET['rozsah']]) ? (string)$_GET['rozsah'] : 'tyden';
    $reqClass = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $class = in_array($reqClass, $classes, true) ? $reqClass : '';
    if ($scope === 'trida' && $class === '') $class = $classes[0] ?? '';
    $week = ba74_monday(is_string($_GET['tyden'] ?? null) ? (string)$_GET['tyden'] : $today);
    $submitted = isset($_GET['f']);
    $lessons = !$submitted || !empty($_GET['co_lekce']);
    $svp = !$submitted || !empty($_GET['co_svp']);
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · obsah lekcí</div><h1>Hromadné schválení</h1>'
        . '<p>Schvalte více lekcí a vazeb na ŠVP najednou ve čtyřech krocích: rozsah (týden, třída nebo celý rok) → náhled změn → potvrzení → výsledek po položkách s tlačítkem „Vrátit“. '
        . 'Lekce, které se učí do 14 dní (pravidlo T-14), jsou vždy nahoře.</p></div></section>';
    if ($classes === []) {
        echo function_exists('c70_empty_html') ? c70_empty_html('Nemáte přiřazenou žádnou třídu.', 'Hromadné schválení se ukáže, jakmile vám administrátor přidělí třídu.', []) : '';
        return;
    }
    $can = ba74_can_manage();
    $f = ['rozsah' => $scope, 'tyden' => $week, 'class' => $class, 'co_lekce' => $lessons ? '1' : '', 'co_svp' => $svp ? '1' : ''];
    $nothing = $submitted && !$lessons && !$svp;
    $st = ba79_state($f, $submitted && !$nothing, isset($_GET['vysledek']), $can);
    if ($nothing) $st['errors'] = ['ba74-co-lekce' => 'Zaškrtněte, co chcete schvalovat – obsah lekcí, vazby na ŠVP, nebo obojí.'];
    $w = sp79_words();
    echo sp79_bar_html(BA79_LABELS, $st['cur'], $st['reach'], static fn(int $k): string => ba79_url($f, $k), $w);
    $other = sp79_draft('hromadne');
    if (!$submitted && $st['cur'] === 1 && $other['data'] !== [] && (int)$other['step'] >= 2) {
        echo sp79_resume_html('Máte rozpracované hromadné schválení (krok ' . (int)$other['step'] . ' ze 4: ' . BA79_LABELS[(int)$other['step'] - 1] . ').', ba79_url(ba79_filter_from_data($other['data']), (int)$other['step']), 'Pokračovat ve schválení');
    }
    echo sp79_errors_html($st['errors'], $w);
    if ($st['cur'] === 4) {
        echo sp79_panel_open(4, 4, BA79_LABELS[3]) . ba74_result_html($modules, $csrf) . '<p class="c70-links"><a class="c70-link" href="' . e(ba79_url($f, 1)) . '">Schválit další (nový rozsah) →</a></p></section>';
        return;
    }
    echo sp79_panel_open(1, $st['cur'], BA79_LABELS[0]) . ba74_filter_html($modules, $classes, $scope, $class, $week, $lessons, $svp, $today, $st['errors']) . '</section>';
    if (!$submitted || $nothing) return;
    $pick = $scope === 'rok' ? ($class !== '' ? [$class] : $classes) : ($class !== '' ? [$class] : $classes);
    try {
        $cand = ba74_candidates($pick, in_array($scope, ['tyden', 't14'], true) ? $scope : 'vse', $week, $lessons, $svp, $today);   // v77: t14
    } catch (Throwable $e) {
        error_log('EDUCANET v74 hromadné schválení: ' . get_class($e));
        echo '<section class="c70-empty" role="status"><h2>Náhled se teď nepodařilo sestavit.</h2><p>Zkuste stránku obnovit.</p></section>';
        return;
    }
    echo ba74_preview_html($modules, $cand, $f, $csrf, $st);
}
/** Týdny s výukou (pondělí) od minulého týdne do konce roku, max 26. @return list<string> */
function ba74_week_options(array $classes, string $today): array
{
    $from = date('Y-m-d', (int)strtotime(ba74_monday($today) . ' -7 days'));
    $weeks = [ba74_monday($today) => true];
    foreach ($classes as $c) foreach (lc72_lesson_dates($c) as $date) if ($date !== '' && $date >= $from) $weeks[ba74_monday($date)] = true;
    $list = array_keys($weeks);
    sort($list);
    return array_slice($list, 0, 26);
}

function ba74_filter_html(array $modules, array $classes, string $scope, string $class, string $week, bool $lessons, bool $svp, string $today, array $errors = []): string
{
    $html = '<form method="get" action="teacher.php" class="c70-card c70-wide ba74-filter" aria-label="Co schválit" data-sp79>'
        . '<input type="hidden" name="tab" value="hromadne74"><input type="hidden" name="f" value="1"><input type="hidden" name="krok" value="2"><fieldset class="ba74-radios"><legend>Rozsah</legend>';
    foreach (BA74_SCOPES as $k => $label) {
        $html .= '<label class="ba74-radio"><input type="radio" name="rozsah" value="' . e($k) . '"' . ($k === $scope ? ' checked' : '') . '> ' . e(ucfirst($label)) . '</label>';
    }
    $weekOpts = '';
    foreach (ba74_week_options($classes, $today) as $w) {
        $end = date('j. n. Y', (int)strtotime($w . ' +6 days'));
        $weekOpts .= '<option value="' . e($w) . '"' . ($w === $week ? ' selected' : '') . '>' . e(date('j. n.', (int)strtotime($w)) . ' – ' . $end . ($w === ba74_monday($today) ? ' (tento týden)' : '')) . '</option>';
    }
    $classOpts = '<option value="">Všechny moje třídy</option>';
    foreach ($classes as $c) $classOpts .= '<option value="' . e($c) . '"' . ($c === $class ? ' selected' : '') . '>' . e(ba74_class_name($modules, $c)) . '</option>';
    $html .= '</fieldset><div class="ba74-row"><label>Týden výuky (pro rozsah „týden“)<select name="tyden">' . $weekOpts . '</select></label>'
        . '<label>Třída<select name="class">' . $classOpts . '</select></label></div>'
        . '<fieldset class="ba74-radios"' . (isset($errors['ba74-co-lekce']) ? ' aria-describedby="ba74-co-lekce-err"' : '') . '><legend>Co</legend><label class="ba74-radio"><input type="checkbox" id="ba74-co-lekce" name="co_lekce" value="1"' . ($lessons ? ' checked' : '') . '> Obsah lekcí</label>'
        . '<label class="ba74-radio"><input type="checkbox" name="co_svp" value="1"' . ($svp ? ' checked' : '') . '> Vazby na ŠVP</label></fieldset>' . sp79_field_error($errors, 'ba74-co-lekce')
        . '<p class="c70-muted">U rozsahu „jedna třída“ bez výběru se použije první třída. Nic se neschválí, dokud v kroku 3 nepotvrdíte.</p>'
        . '<div class="ds78 sp79-w"><div class="sp79-nav"><button class="ds-btn ds-btn--primary" type="submit" data-sp79-validate>Další: Náhled změn</button></div></div></form>';
    return $html;
}
function ba74_when(array $it): string
{
    if ($it['date'] === '') return 'mimo kalendář';
    $when = date('j. n. Y', (int)strtotime((string)$it['date']));
    if ($it['days'] !== null) $when .= $it['days'] >= 0 ? ' (za ' . (int)$it['days'] . ' d)' : ' (proběhlo)';
    return $when;
}

/** Kroky 2 (Náhled změn) a 3 (Potvrzení) v jednom formuláři ba74_approve; výběr položek mezi kroky drží koncept v relaci. */
function ba74_preview_html(array $modules, array $cand, array $f, string $csrf, array $st): string
{
    $items = array_slice($cand['items'], 0, BA74_MAX);
    $cur = (int)$st['cur'];
    $t14 = count(array_filter($cand['items'], static fn(array $i): bool => $i['t14']));
    $nLessons = count(array_filter($cand['items'], static fn(array $i): bool => $i['kind'] === 'lesson'));
    $kpis = function_exists('c70_kpis_html') ? c70_kpis_html([
        ['K rozhodnutí', (string)count($cand['items']), 'návrh nebo změna po schválení', ''],
        ['Učí se do 14 dní', (string)$t14, 'pravidlo T-14 – nahoře', $t14 > 0 ? 'warn' : 'ok'],
        ['Obsah lekcí', (string)$nLessons, 'žáci uvidí nový obsah', ''],
        ['Vazby na ŠVP', (string)(count($cand['items']) - $nLessons), 'potvrzení výsledků ŠVP', ''],
        ['Vrácené k úpravě', (string)$cand['returned'], 'hromadně se neschvalují', ''],
    ], 'Souhrn náhledu') : '';
    $open = sp79_panel_open(2, $cur, BA79_LABELS[1]) . '<div class="c70-card c70-wide ba74-preview">' . $kpis;
    if ($cand['items'] === []) {
        return $open . '<div class="c70-empty" role="status"><h3>V tomto výběru nic nečeká na schválení.</h3><p>' . e('Už schváleno: ' . $cand['approved'] . ', vráceno k úpravě: ' . $cand['returned']
            . '. Vrácené lekce zkontrolujte a schvalte jednotlivě v záložce Schvalování lekcí.') . '</p><p><a class="c70-link" href="' . e('?tab=schvalovani' . ($f['class'] !== '' ? '&class=' . rawurlencode($f['class']) : '')) . '">Schvalování lekcí →</a> · <a class="c70-link" href="' . e(ba79_url($f, 1)) . '">Změnit rozsah</a></p></div></div></section>';
    }
    $html = $open;
    if (count($cand['items']) > BA74_MAX) $html .= '<p class="c70-note" role="status">Výběr má ' . count($cand['items']) . ' položek; najednou jde schválit nejvýš ' . BA74_MAX . ' – zužte ho na týden nebo třídu.</p>';
    $can = ba74_can_manage();
    $chosenKeys = $st['items'];
    $html .= '<p class="c70-muted">Zkontrolujte, co se změní' . ($can ? ', a odškrtněte, co zatím schvalovat nechcete' : '') . '. Celý náhled lekce otevřete odkazem v řádku.</p>';
    $html .= '<div class="c72-scroll"><table class="c70-table ba74-table" id="ba79-items" tabindex="-1"><caption class="c70-sr">Položky ke schválení, nejdřív lekce do 14 dní</caption><thead><tr>'
        . ($can ? '<th scope="col">Schválit</th>' : '') . '<th scope="col">Třída a lekce</th><th scope="col">Co</th><th scope="col">Výuka</th><th scope="col">Nyní → po schválení</th><th scope="col">Co se změní</th></tr></thead><tbody>';
    $chosen = [];
    foreach ($items as $it) {
        $value = $it['class'] . '|' . $it['lesson'] . '|' . $it['kind'] . '|' . $it['hash'];
        $on = $chosenKeys === null || in_array($value, $chosenKeys, true);
        if ($on) $chosen[] = $it;
        $label = ba74_class_name($modules, $it['class']) . ' · ' . (preg_match('/^Lekce\s+\d+/u', (string)$it['title']) === 1 ? $it['title'] : 'L' . $it['lesson'] . ' ' . $it['title']);
        $html .= '<tr' . ($it['t14'] ? ' class="is-behind"' : '') . '>' . ($can ? '<td data-label="Schválit"><label class="ba74-check"><input type="checkbox" name="items[]" value="' . e($value) . '"' . ($on ? ' checked' : '') . '><span class="c70-sr">' . e($label) . '</span></label></td>' : '')
            . '<th scope="row">' . e($label) . '</th><td data-label="Co">' . e($it['kind'] === 'lesson' ? 'Obsah lekce' : 'Vazba na ŠVP') . '</td>'
            . '<td data-label="Výuka">' . e(ba74_when($it)) . ($it['t14'] ? ' <span class="c70-chip c70-chip-warn">T-14</span>' : '') . '</td>'
            . '<td data-label="Nyní → po">' . '<span class="c70-chip c70-chip-warn">' . e((string)$it['label']) . '</span> → <span class="c70-chip c70-chip-ok">schváleno</span></td>'
            . '<td data-label="Co se změní">' . e((string)$it['detail']) . ' <a class="c70-link" href="' . e('?tab=schvalovani&class=' . rawurlencode($it['class']) . '&lesson=' . (int)$it['lesson'] . '#lc72-preview') . '">Celý náhled<span class="c70-sr"> ' . e($label) . '</span></a></td></tr>';
    }
    $html .= '</tbody></table></div>' . sp79_field_error((array)$st['errors'], 'ba79-items') . '</div>';
    if (!$can) return $html . '<p class="c70-note" role="status">Máte jen čtení – schvalovat může učitel třídy nebo administrátor.</p></section>';
    $hidden = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="ba74_approve"><input type="hidden" name="return_tab" value="hromadne74"><input type="hidden" name="return_class" value="' . e($f['class']) . '">';
    foreach (array_values(array_unique(array_column($items, 'class'))) as $c) $hidden .= '<input type="hidden" name="class_ids[]" value="' . e($c) . '">';
    $hidden .= sp79_hidden_html('hromadne', $cur) . '<input type="hidden" name="ba_rozsah" value="' . e($f['rozsah']) . '"><input type="hidden" name="ba_tyden" value="' . e($f['tyden']) . '"><input type="hidden" name="ba_class" value="' . e($f['class']) . '">'
        . '<input type="hidden" name="co_lekce" value="' . e($f['co_lekce']) . '"><input type="hidden" name="co_svp" value="' . e($f['co_svp']) . '">';
    $confirm = sp79_panel_open(3, $cur, BA79_LABELS[2]) . '<div class="c70-card c70-wide ba74-confirm-card">' . ($chosen === [] ? '<p class="c70-note" role="status">Nemáte vybranou žádnou položku – vraťte se k náhledu.</p>' : ba79_confirm_html($modules, $chosen, $f))
        . '<div class="ba74-confirm"><label class="ba74-radio"><input type="checkbox" name="confirm" value="1" required id="ba74-confirm" data-sp79-msg="Potvrďte zaškrtnutím, že jste náhled zkontrolovali."> Náhled jsem zkontroloval/a. Schválené lekce uvidí žáci hned (vrátit je jde v kroku 4).</label></div></div></section>';
    return '<form method="post" action="teacher.php" class="c70-form ba74-form sp79-form" data-sp79>' . $hidden . $html . '</section>' . $confirm
        . sp79_nav_html('teacher', $cur, 3, BA79_LABELS, sp79_words(), '<button class="ds-btn ds-btn--primary" type="submit" data-sp79-validate>Schválit vybrané (' . count($chosen) . ')</button>') . '</form>';
}
/** Krok 4 (v79): výsledek posledního hromadného schválení (session) s vrácením jednotlivých položek existujícími akcemi. */
function ba74_result_html(array $modules, string $csrf): string
{
    $res = is_array($_SESSION['ba74_result'] ?? null) ? $_SESSION['ba74_result'] : null;
    if ($res === null) return '<p class="c70-muted" role="status">Výsledek posledního schválení už není k dispozici (platí jen v této relaci).</p>';
    $rows = array_values(array_filter((array)($res['rows'] ?? []), 'is_array'));
    $ok = count(array_filter($rows, static fn(array $r): bool => !empty($r['ok'])));
    $html = '<section class="c70-card c70-wide ba74-result" aria-labelledby="ba74-step3"><h3 id="ba74-step3">Výsledek: ' . $ok . ' z ' . count($rows) . ' v pořádku</h3>'
        . '<p class="c70-muted">Omylem schválenou položku vrátíte tlačítkem „Vrátit“ – použije se běžné vrácení k úpravě (žáci pak znovu vidí původní lekci).</p>'
        . '<div class="c72-scroll"><table class="c70-table ba74-table"><caption class="c70-sr">Výsledek po položkách</caption><thead><tr><th scope="col">Položka</th><th scope="col">Výsledek</th><th scope="col">Vrátit</th></tr></thead><tbody>';
    $can = ba74_can_manage();
    foreach ($rows as $r) {
        $label = $r['class'] !== '' ? ba74_class_name($modules, (string)$r['class']) . ' · L' . (int)$r['lesson'] . ' · ' . ((string)$r['kind'] === 'lesson' ? 'obsah lekce' : 'vazba na ŠVP') : 'neplatná položka';
        $undo = '';
        if ($can && !empty($r['ok']) && (string)$r['class'] !== '') {
            $isLesson = (string)$r['kind'] === 'lesson';
            $undo = '<form method="post" action="teacher.php" class="c70-form ba74-undo"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="class_id" value="' . e((string)$r['class']) . '">'
                . '<input type="hidden" name="return_tab" value="schvalovani"><input type="hidden" name="action" value="' . ($isLesson ? 'lc72_return' : 'lc72_svp_return') . '">'
                . ($isLesson ? '<input type="hidden" name="lesson" value="' . (int)$r['lesson'] . '"><input type="hidden" name="hash" value="' . e((string)$r['hash']) . '">'
                    : '<input type="hidden" name="svp[' . (int)$r['lesson'] . ']" value="' . e((string)$r['hash']) . '">')
                . '<label class="c70-sr" for="ba74-n-' . e((string)$r['class']) . '-' . (int)$r['lesson'] . '-' . e((string)$r['kind']) . '">Důvod vrácení – ' . e($label) . '</label>'
                . '<input id="ba74-n-' . e((string)$r['class']) . '-' . (int)$r['lesson'] . '-' . e((string)$r['kind']) . '" name="note" maxlength="300" required value="Vráceno po hromadném schválení – zkontrolovat.">'
                . '<button class="btn secondary small" type="submit">Vrátit<span class="c70-sr"> ' . e($label) . '</span></button></form>';
        }
        $html .= '<tr><th scope="row">' . e($label) . '</th><td data-label="Výsledek"><span class="c70-chip ' . (!empty($r['ok']) ? 'c70-chip-ok">✓ ' : 'c70-chip-bad">✗ ') . e((string)$r['message']) . '</span></td><td data-label="Vrátit">' . ($undo !== '' ? $undo : '—') . '</td></tr>';
    }
    return $html . '</tbody></table></div></section>';
}

/** Karta v záložce Schvalování lekcí: kolik čeká v této třídě a odkaz na hromadné schválení (T-14 zvýrazněné). */
function ba74_entry_html(string $class): string
{
    if (!function_exists('c70_tab_visible') || !c70_tab_visible('hromadne74')) return '';
    try {
        $cand = ba74_candidates([$class], 'vse', ba74_monday(date('Y-m-d')), true, true);
    } catch (Throwable $e) {
        return '';
    }
    $n = count($cand['items']);
    $t14 = count(array_filter($cand['items'], static fn(array $i): bool => $i['t14']));
    $url = '?' . http_build_query(['tab' => 'hromadne74', 'rozsah' => 'trida', 'class' => $class, 'f' => '1', 'co_lekce' => '1', 'co_svp' => '1', 'krok' => '2']);
    $css = '<link rel="stylesheet" href="' . e(function_exists('teacher68_css_href') ? teacher68_css_href('assets/cockpit-v74.css') : 'assets/cockpit-v74.css') . '">';   // modul `schvalovani` má CSS beze změny (audit v72)
    return $css . '<section class="c70-card c70-wide ba74-entry' . ($t14 > 0 ? ' is-warn' : '') . '" aria-labelledby="ba74-entry-h"><h2 id="ba74-entry-h">Hromadné schválení</h2>'
        . '<p>' . ($n === 0 ? 'V této třídě nic nečeká na rozhodnutí.' : e('Čeká ' . $n . ' ' . ($n === 1 ? 'položka' : ($n < 5 ? 'položky' : 'položek')) . ' (obsah lekcí a vazby na ŠVP)' . ($t14 > 0 ? ', z toho ' . $t14 . ' do 14 dní (T-14).' : '.')))
        . '</p>' . ($n > 0 ? '<p><a class="btn primary" href="' . e($url) . '">Schválit více najednou →</a> <span class="c70-muted">náhled, potvrzení, výsledek a vrácení po položkách</span></p>' : '') . '</section>';
}
