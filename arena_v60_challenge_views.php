<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · ARN-07 – vykreslení výzev spolužákům v profilu (záložka „arena“, tlačítko na cizím profilu).
 * Volá ho profile_v60_views.php. Žádná zápisová logika tady – jen HTML nad daty z arena_v60_challenge.php.
 */

/** Vlastní profil – přepínač opt-in, příchozí/odchozí výzvy, historie. */
function arena60_render_profile_tab(string $classId, string $studentKey): void
{
    $now = arena57_now();
    $data = arena60_profile_data($classId, $studentKey, $now);

    echo '<section class="dashboard-panel p60-panel arena60-panel"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Souboje')) . '</div><h2>' . e(tr('Výzvy spolužákům')) . '</h2>'
        . '<p>' . e(tr('Vyzvi spolužáka na stejnou úlohu z Linux Labu – vyhrává, kdo ji vyřeší dřív. Body za souboj nejsou, jen výsledek v historii.')) . '</p></div></div>';

    echo '<form method="post" class="arena60-optin"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="arena60_optin_set">'
        . '<input type="hidden" name="on" value="' . ($data['optin'] ? '0' : '1') . '">'
        . '<span class="arena60-optin-state">' . e($data['optin'] ? tr('Přijímám výzvy od spolužáků: zapnuto') : tr('Přijímám výzvy od spolužáků: vypnuto')) . '</span>'
        . '<button class="btn ' . ($data['optin'] ? 'secondary' : 'primary') . '" type="submit">' . e($data['optin'] ? tr('Vypnout') : tr('Zapnout')) . '</button></form>';
    if (!$data['optin']) {
        echo '<p class="arena60-hint">' . e(tr('Dokud výzvy nezapneš, spolužáci tě vyzvat nemůžou.')) . '</p>';
    }

    echo '<div class="p60-stat-grid arena60-stats">'
        . '<div class="p60-stat"><strong>' . (int)$data['wins'] . '</strong><small>' . e(tr('výhry')) . '</small></div>'
        . '<div class="p60-stat"><strong>' . (int)$data['losses'] . '</strong><small>' . e(tr('prohry')) . '</small></div>'
        . '</div>';

    echo '<h3 class="p60-badge-group">' . e(tr('Příchozí výzvy')) . '</h3>';
    if (!$data['incoming']) {
        echo '<p class="p60-chart-empty">' . e(tr('Zatím tě nikdo nevyzval.')) . '</p>';
    } else {
        echo '<ul class="arena60-list">';
        foreach ($data['incoming'] as $row) {
            echo '<li><span>' . e(tr('{name} tě vyzval/a na úlohu „{title}“.', ['name' => $row['from_name'], 'title' => $row['level_title']])) . '</span>'
                . '<span class="arena60-actions">'
                . '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="arena60_challenge_respond"><input type="hidden" name="id" value="' . e((string)$row['id']) . '"><input type="hidden" name="accept" value="1"><button class="btn primary" type="submit">' . e(tr('Přijmout')) . '</button></form>'
                . '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="arena60_challenge_respond"><input type="hidden" name="id" value="' . e((string)$row['id']) . '"><input type="hidden" name="accept" value="0"><button class="btn secondary" type="submit">' . e(tr('Odmítnout')) . '</button></form>'
                . '</span></li>';
        }
        echo '</ul>';
    }

    echo '<h3 class="p60-badge-group">' . e(tr('Odeslané výzvy')) . '</h3>';
    if (!$data['outgoing']) {
        echo '<p class="p60-chart-empty">' . e(tr('Nemáš žádnou čekající výzvu.')) . '</p>';
    } else {
        echo '<ul class="arena60-list">';
        foreach ($data['outgoing'] as $row) {
            $status = (string)$row['status'] === 'accepted' ? tr('probíhá') : tr('čeká na odpověď');
            echo '<li><span>' . e(tr('{name} · „{title}“ · {status}', ['name' => $row['to_name'], 'title' => $row['level_title'], 'status' => $status])) . '</span>'
                . '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="arena60_challenge_cancel"><input type="hidden" name="id" value="' . e((string)$row['id']) . '"><button class="btn secondary" type="submit">' . e(tr('Zrušit')) . '</button></form></li>';
        }
        echo '</ul>';
    }

    echo '<h3 class="p60-badge-group">' . e(tr('Historie soubojů')) . '</h3>';
    if (!$data['history']) {
        echo '<p class="p60-chart-empty">' . e(tr('Zatím žádný dokončený souboj.')) . '</p>';
    } else {
        echo '<ul class="arena60-list">';
        foreach ($data['history'] as $row) {
            $opponent = (string)$row['from_key'] === $studentKey ? $row['to_name'] : $row['from_name'];
            $label = match ((string)$row['status']) {
                'done' => (string)$row['winner_key'] === $studentKey ? tr('výhra proti {name}', ['name' => $opponent]) : tr('prohra proti {name}', ['name' => $opponent]),
                'declined' => tr('{name} odmítl/a', ['name' => $opponent]),
                'cancelled' => tr('zrušeno'),
                default => tr('vypršelo'),
            };
            echo '<li><span>' . e($row['level_title'] . ' · ' . $label) . '</span></li>';
        }
        echo '</ul>';
    }

    if ($data['weekly']) {
        echo '<h3 class="p60-badge-group">' . e(tr('Týdenní hádanka – historie')) . '</h3><ul class="arena60-list">';
        foreach ($data['weekly'] as $w) {
            $txt = $w['played'] ? tr('{title}: vyřešeno ({len} znaků)', ['title' => $w['title'], 'len' => (int)$w['len']]) : tr('{title}: nehráno', ['title' => $w['title']]);
            echo '<li><span>' . e($txt) . '</span></li>';
        }
        echo '</ul>';
    }
    echo '</section>';
}

/** Cizí profil – tlačítko „Vyzvat“ + souhrnná čísla, žádné detaily jeho soubojů s jinými. */
function arena60_render_challenge_button(string $classId, string $myKey, string $targetKey): void
{
    $now = arena57_now();
    $summary = arena60_public_summary($classId, $targetKey, $now);
    echo '<div class="arena60-public"><div class="p60-stat-grid arena60-stats">'
        . '<div class="p60-stat"><strong>' . (int)$summary['total'] . '</strong><small>' . e(tr('soubojů')) . '</small></div>'
        . '<div class="p60-stat"><strong>' . (int)$summary['wins'] . '</strong><small>' . e(tr('výher')) . '</small></div>'
        . '</div>';
    [$ok, $reason] = arena60_can_challenge($classId, $myKey, $targetKey, $now);
    if ($ok) {
        echo '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="arena60_challenge_create"><input type="hidden" name="to_key" value="' . e($targetKey) . '"><button class="btn primary" type="submit">' . e(tr('Vyzvat na souboj')) . '</button></form>';
    } else {
        echo '<p class="arena60-hint">' . e($reason) . '</p>';
    }
    echo '</div>';
}
