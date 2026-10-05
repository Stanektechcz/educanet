<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/grading_v66.php';

/**
 * EDUCANET v66 · žákovský pohled „Moje hodnocení“ (?view=hodnoceni, texty přes tr()).
 *
 * Žák vidí řetězec důkazů vždy (je-li hodnocení ve třídě zapnuté), známku až po převzetí učitelem. Nevidí štítky integrity,
 * ani žádná data spolužáků. Výukový obsah (názvy kompetencí) se převádí stejně jako v mapě kompetencí.
 */

function g66v_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/assessment-v66.css?v=66.0')) . '">' . "\n";
}

function g66v_verbal(int $grade): string
{
    return match ($grade) {
        1 => tr('Výborně zvládnuto'),
        2 => tr('Chvalitebně zvládnuto'),
        3 => tr('Základy zvládnuty'),
        4 => tr('Zvládnuto jen minimum'),
        5 => tr('Zatím nezvládnuto'),
        default => '',
    };
}

function g66v_source_label(string $source): string
{
    return match ($source) {
        'summative_test' => tr('Sumativní testy'),
        'mastery' => tr('Zvládnutí kompetencí'),
        'project_v65' => tr('Projekty'),
        default => $source,
    };
}

/** Odkaz „Moje hodnocení“ pro mapu kompetencí; prázdný řetězec, když je hodnocení ve třídě vypnuté. */
function g66v_link_html(string $classId): string
{
    if (!g66_enabled($classId)) return '';
    return '<p class="c62-intro"><a href="?view=hodnoceni">' . e(tr('Moje hodnocení: z čeho se skládá')) . '</a></p>';
}

function g66v_test_label(string $ref): string
{
    if (preg_match('/^v56:l(\d+)$/', $ref, $m) === 1) return tr('Test lekce {n}', ['n' => (int)$m[1]]);
    return tr('Ověření cesty');
}

/** Jedna položka řetězce důkazů. */
function g66v_chain_li(array $c): string
{
    $score = edu_number((float)$c['score'] * 100, 0) . ' %';
    $label = (string)$c['label'];
    if ($c['source'] === 'summative_test') $label = g66v_test_label((string)$c['ref']);
    elseif ($c['source'] === 'mastery') $label = comp62_t_label(substr((string)$c['ref'], 5), $label);
    elseif ($c['source'] === 'project_v65') $label = tr('Hodnocení projektu');
    $state = isset($c['state']) ? ' · ' . comp62_state_view((string)$c['state'])['text'] : '';
    $when = (string)$c['at'] !== '' && ($ts = strtotime((string)$c['at'])) ? ' · ' . edu_date((int)$ts) : '';
    $ev = '';
    foreach ((array)($c['evidence'] ?? []) as $e) {
        $ets = strtotime((string)$e['at']);
        $ev .= '<li>' . e(comp62_t_source((string)$e['source']) . ' · ' . edu_number((float)$e['score'] * 100, 0) . ' %' . ($ets ? ' · ' . edu_date((int)$ets) : '')) . '</li>';
    }
    return '<li><strong>' . e($label) . '</strong> <span class="a66-tag">' . e(g66v_source_label((string)$c['source'])) . '</span> ' . e($score . $state . $when) . ($ev !== '' ? '<ul>' . $ev . '</ul>' : '') . '</li>';
}

function g66v_parts_table(array $proposal): string
{
    $rows = '';
    foreach ((array)$proposal['parts'] as $source => $part) {
        $result = $part['pct'] === null ? tr('zatím bez podkladů') : edu_number((float)$part['pct'], 0) . ' %';
        $rows .= '<tr><th scope="row">' . e(g66v_source_label((string)$source)) . '</th><td>' . (int)$part['weight'] . ' %</td><td>' . e($result) . '</td></tr>';
    }
    return '<div class="a66-scroll" tabindex="0" role="region" aria-label="' . e(tr('Z čeho se hodnocení skládá')) . '"><table class="a66-table"><caption class="a66-sr">' . e(tr('Z čeho se hodnocení skládá')) . '</caption>'
        . '<thead><tr><th scope="col">' . e(tr('Zdroj')) . '</th><th scope="col">' . e(tr('Váha')) . '</th><th scope="col">' . e(tr('Tvůj výsledek')) . '</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
}

function g66v_grade_block(string $classId, string $studentKey): string
{
    $sid = g66_student_id($classId, $studentKey);
    $acc = $sid !== null ? g66_accepted($classId, $sid) : null;
    if ($acc === null) return '<section class="a66-section" aria-labelledby="a66-grade"><h2 id="a66-grade">' . e(tr('Známka')) . '</h2><p>' . e(tr('Učitel zatím žádnou známku nepřevzal. Až se to stane, uvidíš ji tady.')) . '</p></section>';
    return '<section class="a66-section" aria-labelledby="a66-grade"><h2 id="a66-grade">' . e(tr('Známka')) . '</h2><p class="a66-grade">' . (int)$acc['grade'] . '</p><p>' . e(g66v_verbal((int)$acc['grade']))
        . ' · ' . e(tr('převzal učitel {date}', ['date' => edu_date((int)$acc['at'])])) . '</p></section>';
}

function g66v_render(string $classId, string $studentKey): void
{
    g66v_assets();
    echo '<div class="a66-student"><h1>' . e(tr('Moje hodnocení')) . '</h1>';
    $proposal = g66_proposal($classId, $studentKey);
    if ($proposal === null) {
        echo '<p>' . e(tr('V tvé třídě se hodnocení z testů, kompetencí a projektů zatím nepočítá.')) . '</p><p><a class="ui-link" href="?view=dashboard">' . e(tr('Zpět na přehled')) . '</a></p></div>';
        return;
    }
    echo '<p class="a66-note">' . e(tr('Tady vidíš, z čeho se skládá podklad pro hodnocení. Hry, aréna a body do něj nepatří. Rozhoduje učitel.')) . '</p>';
    echo g66v_grade_block($classId, $studentKey) . '<section class="a66-section" aria-labelledby="a66-parts"><h2 id="a66-parts">' . e(tr('Z čeho se skládá')) . '</h2>' . g66v_parts_table($proposal) . '</section>';
    echo '<section class="a66-section" aria-labelledby="a66-chain"><h2 id="a66-chain">' . e(tr('Důkazy')) . '</h2>';
    if ($proposal['chain'] === []) echo ui67_empty_state(tr('Zatím není dost podkladů'), tr('Až budeš mít výsledky sumativního testu, ověřené kompetence nebo hodnocený projekt, objeví se tady.'), '?view=cesty', tr('Otevřít moje cesty'));
    else echo '<ul class="a66-chain">' . implode('', array_map('g66v_chain_li', $proposal['chain'])) . '</ul>';
    echo '</section><p><a class="ui-link" href="?view=dashboard">' . e(tr('Zpět na přehled')) . '</a></p></div>';
}
