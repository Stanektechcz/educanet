<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · vazba na ŠVP v záložce „Schvalování lekcí“ (cockpit, vždy česky).
 *   svp72_section_html – sekce „Vazba na ŠVP“: zdroj (název dokumentu, odkaz, datum ověření), 28 lekcí třídy s výsledky,
 *                        stav a jeden formulář (lc72_svp_approve / lc72_svp_return, CSRF, třída v rozsahu, content.manage).
 *   svp72_lesson_html  – vazba jedné lekce v náhledu návrhu.
 * Externí odkaz jen přes safe_url() s rel="noopener noreferrer". Každý výstup přes e(); data žáků se tu nezobrazují.
 */

require_once dirname(__DIR__, 2) . '/src/assessment/svp_v72.php';

function svp72_chip(array $status): string
{
    $tone = ['schvaleno' => ' c70-chip-ok', 'vraceno' => ' c70-chip-bad', 'zmeneno' => ' c70-chip-warn', 'navrh' => ' c70-chip-warn', 'chybi' => ' c70-chip-bad'][$status['status']] ?? '';
    return '<span class="c70-chip' . $tone . '">' . e((string)$status['label']) . '</span>';
}

function svp72_source_html(): string
{
    $url = function_exists('safe_url') ? safe_url(SVP72_SOURCE['url']) : SVP72_SOURCE['url'];
    return '<p class="c70-muted">Zdroj: <a href="' . e($url) . '" rel="noopener noreferrer">' . e(SVP72_SOURCE['title']) . '</a> (' . e(SVP72_SOURCE['field']) . ', '
        . e(SVP72_SOURCE['school']) . ', platnost od ' . e(date('j. n. Y', (int)strtotime(SVP72_SOURCE['valid_from']))) . ', ' . e(SVP72_SOURCE['ref']) . '), ověřeno '
        . e(date('j. n. Y', (int)strtotime(SVP72_SOURCE['verified_at']))) . '.</p>';
}

/** Seznam výsledků lekce (předmět, ročník, strana, text) + označení jiného ročníku. */
function svp72_outcomes_list_html(string $classId, array $curriculum): string
{
    $year = SVP72_CLASS_YEAR[$classId] ?? 0;
    $html = '<ul class="c71-list">';
    foreach ((array)$curriculum['outcomes'] as $o) {
        $other = (int)$o['year'] !== $year ? ' <span class="c70-chip">' . (int)$o['year'] . '. ročník</span>' : '';
        $html .= '<li><strong>' . e((string)$o['subject_label']) . '</strong>' . $other . ': ' . e((string)$o['text']) . ' <span class="c70-muted">(s. ' . (int)$o['page'] . ', ' . e((string)$o['id']) . ')</span></li>';
    }
    return $html . '</ul>';
}

/** Vazba jedné lekce v náhledu návrhu obsahu. */
function svp72_lesson_html(string $classId, int $n): string
{
    $cur = svp72_curriculum($classId, $n);
    if ($cur === null) return '<h3>Vazba na ŠVP</h3><p class="c70-muted">' . e(LC72_SVP_DEFAULT) . '.</p>';
    return '<h3>Vazba na ŠVP</h3><p>' . svp72_chip(svp72_status($classId, $n)) . '</p>' . svp72_outcomes_list_html($classId, $cur)
        . '<p class="c70-muted"><a href="#svp72">Schválit nebo vrátit vazbu v sekci Vazba na ŠVP ↓</a></p>';
}

/** Sekce „Vazba na ŠVP“ pro všech 28 lekcí třídy. */
function svp72_section_html(string $classId, string $csrf): string
{
    try {
        $records = svp72_records();
        $rows = function_exists('lm71_curriculum_rows') ? lm71_curriculum_rows($classId) : [];
    } catch (Throwable $e) {
        error_log('EDUCANET v72.1 ŠVP: ' . get_class($e));
        return '<section class="c70-empty" id="svp72" role="status"><h2>Vazbu na ŠVP se teď nepodařilo načíst.</h2><p>Zkuste stránku obnovit.</p></section>';
    }
    $titles = [];
    foreach ($rows as $r) $titles[(int)($r['number'] ?? 0)] = (string)($r['title'] ?? '');
    $can = function_exists('teacher_permission') && teacher_permission('content.manage');
    $counts = ['navrh' => 0, 'schvaleno' => 0, 'vraceno' => 0, 'zmeneno' => 0, 'chybi' => 0];
    $body = '';
    for ($n = 1; $n <= 28; $n++) {
        $st = svp72_status($classId, $n, $records);
        $counts[$st['status']] = ($counts[$st['status']] ?? 0) + 1;
        $cur = svp72_curriculum($classId, $n);
        $pick = '';
        if ($can && $cur !== null) {
            $id = 'svp72-l' . $n;
            $pick = '<input type="checkbox" id="' . e($id) . '" name="svp[' . $n . ']" value="' . e($st['hash']) . '"' . ($st['status'] !== 'schvaleno' ? ' checked' : '') . '>'
                . '<label for="' . e($id) . '" class="c70-sr">Vybrat lekci ' . $n . '</label>';
        }
        $body .= '<tr><td data-label="Výběr">' . $pick . '</td><th scope="row">' . e('Lekce ' . $n . ($titles[$n] ?? '' ? ' · ' . preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', $titles[$n]) : '')) . '</th>'
            . '<td data-label="Výsledky ŠVP">' . ($cur === null ? '<span class="c70-muted">' . e(LC72_SVP_DEFAULT) . '</span>' : svp72_outcomes_list_html($classId, $cur)) . '</td>'
            . '<td data-label="Stav">' . svp72_chip($st) . ($st['note'] !== '' ? '<br><span class="c70-muted">' . e($st['note']) . '</span>' : '') . '</td></tr>';
    }
    $html = '<section class="c70-card c70-wide" id="svp72" aria-labelledby="svp72-h"><h2 id="svp72-h">Vazba na ŠVP</h2>'
        . '<p>Návrh, ke kterým výsledkům vzdělávání ŠVP lekce vede. Platí až po vašem schválení; obsah lekcí ani jejich schválení se tím nemění.</p>' . svp72_source_html()
        . '<p><span class="c70-chip c70-chip-ok">schváleno ' . $counts['schvaleno'] . '</span> <span class="c70-chip c70-chip-warn">návrh ' . ($counts['navrh'] + $counts['zmeneno']) . '</span>'
        . ' <span class="c70-chip">vráceno ' . $counts['vraceno'] . '</span> <span class="c70-chip">chybí vazba ' . $counts['chybi'] . '</span></p>';
    $table = '<div class="c72-scroll"><table class="c70-table"><caption class="c70-sr">Vazba lekcí na výsledky ŠVP</caption><thead><tr><th scope="col">Výběr</th><th scope="col">Lekce</th>'
        . '<th scope="col">Výsledky vzdělávání (ŠVP)</th><th scope="col">Stav</th></tr></thead><tbody>' . $body . '</tbody></table></div>';
    if (!$can) return $html . '<p class="c70-note" role="status">Máte jen čtení – vazbu schvaluje učitel třídy nebo administrátor.</p>' . $table . '</section>';
    return $html . '<form method="post" action="teacher.php" class="c70-form">'
        . '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="class_id" value="' . e($classId) . '"><input type="hidden" name="return_tab" value="schvalovani">'
        . $table
        . '<label for="svp72-note">Při vrácení: co ve vazbě upravit (nejvýš ' . SVP72_NOTE_MAX . ' znaků)</label><textarea id="svp72-note" name="note" rows="2" maxlength="' . SVP72_NOTE_MAX . '"></textarea>'
        . '<div class="c72-decide"><button class="btn primary" type="submit" name="action" value="lc72_svp_approve">Schválit vazbu vybraných lekcí</button>'
        . '<button class="btn secondary" type="submit" name="action" value="lc72_svp_return">Vrátit vybrané k úpravě</button></div></form></section>';
}
