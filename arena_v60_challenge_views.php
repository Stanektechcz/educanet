<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · ARN-07 – vykreslení výzev spolužákům v profilu (záložka „arena“, tlačítko na cizím profilu).
 * Volá ho profile_v60_views.php. Žádná zápisová logika tady – jen HTML nad daty z arena_v60_challenge.php.
 * Vzhled sdílí design systém profilu (profile_v60_ui.php, assets/profile-v60.css).
 */

require_once __DIR__ . '/profile_v60_ui.php';

/** Skrytá pole POST formuláře výzvy (CSRF + akce + id). */
function arena60_form_open(string $action, string $id = '', array $extra = []): string
{
    $html = '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="' . e($action) . '">';
    if ($id !== '') $html .= '<input type="hidden" name="id" value="' . e($id) . '">';
    foreach ($extra as $name => $value) { $html .= '<input type="hidden" name="' . e((string)$name) . '" value="' . e((string)$value) . '">'; }
    return $html;
}

/** Hlavní úkol záložky: příchozí výzvy (žlutě), jinak výzva spolužákovi. */
function arena60_task(array $data): string
{
    $n = count($data['incoming']);
    if ($n > 0) {
        return profile60_task(tr('Čeká na tebe'), trn(['one' => '{n} nová výzva', 'few' => '{n} nové výzvy', 'other' => '{n} nových výzev'], $n), tr('Přijmi výzvu, nebo ji odmítni níže.'), '', '', 'yellow');
    }
    return profile60_task(tr('Souboje'), tr('Vyzvi spolužáka'), tr('Vyzvi spolužáka na stejnou úlohu z Linux Labu – vyhrává, kdo ji vyřeší dřív. Body za souboj nejsou, jen výsledek v historii.'), tr('Najít spolužáka'), '?view=community');
}

function arena60_render_incoming(array $rows): void
{
    echo '<h3 class="p60-subhead">' . e(tr('Příchozí výzvy')) . '</h3>';
    if (!$rows) { echo profile60_empty(tr('Zatím tě nikdo nevyzval.')); return; }
    echo '<ul class="arena60-list">';
    foreach ($rows as $row) {
        echo '<li><span>' . e(tr('{name} tě vyzval/a na úlohu „{title}“.', ['name' => $row['from_name'], 'title' => $row['level_title']])) . '</span><span class="arena60-actions">'
            . arena60_form_open('arena60_challenge_respond', (string)$row['id'], ['accept' => '1']) . '<button class="btn primary" type="submit">' . e(tr('Přijmout')) . '</button></form>'
            . arena60_form_open('arena60_challenge_respond', (string)$row['id'], ['accept' => '0']) . '<button class="btn secondary" type="submit">' . e(tr('Odmítnout')) . '</button></form></span></li>';
    }
    echo '</ul>';
}

function arena60_render_outgoing(array $rows): void
{
    echo '<h3 class="p60-subhead">' . e(tr('Odeslané výzvy')) . '</h3>';
    if (!$rows) { echo profile60_empty(tr('Nemáš žádnou čekající výzvu.')); return; }
    echo '<ul class="arena60-list">';
    foreach ($rows as $row) {
        $status = (string)$row['status'] === 'accepted' ? tr('probíhá') : tr('čeká na odpověď');
        echo '<li><span>' . e(tr('{name} · „{title}“ · {status}', ['name' => $row['to_name'], 'title' => $row['level_title'], 'status' => $status])) . '</span>'
            . arena60_form_open('arena60_challenge_cancel', (string)$row['id']) . '<button class="btn secondary" type="submit">' . e(tr('Zrušit')) . '</button></form></li>';
    }
    echo '</ul>';
}

function arena60_render_history(array $rows, string $studentKey): void
{
    echo '<h3 class="p60-subhead">' . e(tr('Historie soubojů')) . '</h3>';
    if (!$rows) { echo profile60_empty(tr('Zatím žádný dokončený souboj.')); return; }
    echo '<ul class="arena60-list">';
    foreach ($rows as $row) {
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

/** Vlastní profil – příchozí/odeslané výzvy, historie, opt-in přepínač a týdenní hádanka. */
function arena60_render_profile_tab(string $classId, string $studentKey): void
{
    $data = arena60_profile_data($classId, $studentKey, arena57_now());
    echo arena60_task($data);
    echo profile60_panel_open(tr('Výzvy spolužákům'), tr('Souboje'));
    echo profile60_stats([[(string)(int)$data['wins'], tr('výhry'), 'teal'], [(string)(int)$data['losses'], tr('prohry'), 'orange']]);
    arena60_render_incoming($data['incoming']);
    arena60_render_outgoing($data['outgoing']);
    arena60_render_history($data['history'], $studentKey);
    echo profile60_panel_close();
    echo profile60_panel_open(tr('Nastavení výzev'));
    echo arena60_form_open('arena60_optin_set', '', ['on' => $data['optin'] ? '0' : '1']) . '<div class="arena60-optin"><span class="arena60-optin-state">'
        . e($data['optin'] ? tr('Přijímám výzvy od spolužáků: zapnuto') : tr('Přijímám výzvy od spolužáků: vypnuto')) . '</span>'
        . '<button class="btn ' . ($data['optin'] ? 'secondary' : 'primary') . '" type="submit">' . e($data['optin'] ? tr('Vypnout') : tr('Zapnout')) . '</button></div></form>';
    if (!$data['optin']) echo '<p class="arena60-hint">' . e(tr('Dokud výzvy nezapneš, spolužáci tě vyzvat nemůžou.')) . '</p>';
    echo profile60_panel_close();
    if ($data['weekly']) arena60_render_weekly($data['weekly']);
}

function arena60_render_weekly(array $weekly): void
{
    echo profile60_panel_open(tr('Týdenní hádanka – historie')) . '<ul class="arena60-list">';
    foreach ($weekly as $w) {
        $txt = $w['played'] ? tr('{title}: vyřešeno ({len} znaků)', ['title' => $w['title'], 'len' => (int)$w['len']]) : tr('{title}: nehráno', ['title' => $w['title']]);
        echo '<li><span>' . e($txt) . '</span></li>';
    }
    echo '</ul>' . profile60_panel_close();
}

/** Cizí profil – tlačítko „Vyzvat“ + souhrnná čísla, žádné detaily jeho soubojů s jinými. */
function arena60_render_challenge_button(string $classId, string $myKey, string $targetKey): void
{
    $summary = arena60_public_summary($classId, $targetKey, arena57_now());
    echo profile60_panel_open(tr('Souboje'), tr('Aréna')) . profile60_stats([[(string)(int)$summary['total'], tr('soubojů'), 'teal'], [(string)(int)$summary['wins'], tr('výher'), 'yellow']]);
    [$ok, $reason] = arena60_can_challenge($classId, $myKey, $targetKey, arena57_now());
    if ($ok) {
        echo arena60_form_open('arena60_challenge_create', '', ['to_key' => $targetKey]) . '<button class="btn primary" type="submit">' . e(tr('Vyzvat na souboj')) . '</button></form>';
    } else {
        echo '<p class="arena60-hint">' . e($reason) . '</p>';
    }
    echo profile60_panel_close();
}
