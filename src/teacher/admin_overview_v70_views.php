<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v70 · vykreslení záložky „Přehled správy“ (jen administrátor, jen čtení, žádný formulář).
 * Hesla, jednorázová hesla, IP adresy ani klíče se nikde nevypisují – jen počty, role, loginy a kódy událostí.
 */

require_once __DIR__ . '/cockpit_v70.php';
require_once __DIR__ . '/admin_overview_v70.php';

function ad70_bytes(?int $bytes): string
{
    if ($bytes === null) return '—';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $v = (float)$bytes;
    while ($v >= 1024 && $i < count($units) - 1) { $v /= 1024; $i++; }
    return number_format($v, $i === 0 ? 0 : 1, ',', ' ') . ' ' . $units[$i];
}

function ad70_link(string $tab, string $label): string
{
    return c70_tab_visible($tab) ? '<a class="c70-link" href="' . e('?tab=' . rawurlencode($tab)) . '">' . e($label) . '</a>' : '';
}

function ad70_render_tab(): void
{
    if (function_exists('teacher59_is_admin') && !teacher59_is_admin()) {
        echo c70_empty_html('Přehled správy je jen pro administrátora.', 'Pro změnu účtu nebo tříd kontaktujte administrátora školy.', [['attention', 'Zpět na Dnes']]);
        return;
    }
    try {
        $o = ad70_overview(true);
    } catch (Throwable $e) {
        error_log('EDUCANET v70 přehled správy: ' . $e->getMessage());
        echo c70_empty_html('Přehled správy se nepodařilo sestavit.', 'Podrobnosti najdete v záložkách Provoz a Kvalita dat.', [['provoz', 'Provoz'], ['quality', 'Kvalita dat']]);
        return;
    }
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Správa · školní rok ' . e($o['ops']['school_year']) . '</div><h1>Přehled správy</h1>'
        . '<p>Účty a role učitelů, pokrytí tříd, zdraví provozu a kvalita dat na jedné stránce. Jen náhled – nic se odsud nemění ani nespouští.</p></div></section>';
    echo c70_kpis_html(ad70_kpi_items($o), 'Souhrn správy');
    echo '<div class="c70-grid"><div class="c70-col">' . ad70_recommendations_html($o) . ad70_accounts_html($o) . '</div>'
        . '<div class="c70-col c70-side">' . ad70_ops_html($o) . ad70_data_html($o) . ad70_events_html($o) . '</div></div>';
}

function ad70_recommendations_html(array $o): string
{
    $html = '<section class="c70-card" aria-labelledby="ad70-todo"><h2 id="ad70-todo">Co udělat</h2>';
    if ($o['recommendations'] === []) return $html . '<p class="c70-ok">Vše v pořádku – žádné doporučení.</p></section>';
    $html .= '<ul class="c70-todo">';
    foreach ($o['recommendations'] as $r) {
        $tone = $r['level'] === 'bad' ? 'Vážné' : 'Upozornění';
        $html .= '<li class="is-' . e($r['level']) . '"><span class="c70-chip ' . ($r['level'] === 'bad' ? 'c70-chip-bad' : 'c70-chip-warn') . '">' . e($tone) . '</span> ' . e($r['text']);
        if ($r['cmd'] !== '') $html .= ' <code>' . e($r['cmd']) . '</code>';
        $link = $r['tab'] !== '' ? ad70_link($r['tab'], 'Otevřít') : '';
        $html .= ($link !== '' ? ' ' . $link : '') . '</li>';
    }
    return $html . '</ul></section>';
}

function ad70_accounts_html(array $o): string
{
    $a = $o['accounts'];
    $html = '<section class="c70-card" aria-labelledby="ad70-acc"><h2 id="ad70-acc">Účty a role</h2>';
    if ($a['mode'] !== 'accounts') {
        $why = $a['mode'] === 'broken' ? 'Úložiště účtů nejde přečíst, přihlášení je proto zablokované. Spusťte kontrolu prostředí (tools/preflight.php).'
            : 'Cockpit používá sdílený učitelský klíč: všichni přihlášení mají stejnou roli a vidí všechny třídy. Účty s rolemi (administrátor, učitel, asistent) a rozsahem tříd zapnete příkazem tools/v59_teacher_accounts.php (viz INSTALL.md, v59).';
        return $html . '<p>' . e($why) . '</p>' . (ad70_link('team_admin', 'Tým a role (sdílený klíč)') !== '' ? '<p>' . ad70_link('team_admin', 'Tým a role (sdílený klíč)') . '</p>' : '') . '</section>';
    }
    $html .= '<ul class="c70-facts"><li><span>Administrátoři</span><strong>' . (int)$a['roles']['admin'] . '</strong></li><li><span>Učitelé</span><strong>' . (int)$a['roles']['teacher'] . '</strong></li>'
        . '<li><span>Asistenti</span><strong>' . (int)$a['roles']['assistant'] . '</strong></li><li><span>Deaktivované</span><strong>' . (int)$a['disabled'] . '</strong></li>'
        . '<li><span>Čeká na 1. přihlášení</span><strong>' . (int)$a['otp'] . '</strong></li><li><span>Nikdy nepřihlášení</span><strong>' . (int)$a['never'] . '</strong></li>'
        . '<li><span>Dočasně zamčené</span><strong>' . (int)$a['locked'] . '</strong></li></ul>';
    $html .= '<table class="c70-table c70-compact"><caption>Pokrytí tříd (aktivní účty, bez administrátorů)</caption><thead><tr><th scope="col">Třída</th><th scope="col">Učitelé</th><th scope="col">Asistenti</th></tr></thead><tbody>';
    foreach ($a['coverage'] as $classId => $c) {
        $html .= '<tr' . ($c['teacher'] === 0 ? ' class="is-behind"' : '') . '><th scope="row" data-label="Třída">' . e(ad70_class_label($classId)) . '</th><td data-label="Učitelé">' . (int)$c['teacher'] . ($c['teacher'] === 0 ? ' <span class="c70-chip c70-chip-warn">bez učitele</span>' : '') . '</td><td data-label="Asistenti">' . (int)$c['assistant'] . '</td></tr>';
    }
    $link = ad70_link('ucitele', 'Spravovat účty, role a třídy →');
    return $html . '</tbody></table>' . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
}

function ad70_ops_html(array $o): string
{
    $ops = $o['ops'];
    $health = ['PASS' => 'v pořádku', 'WARN' => 'upozornění', 'FAIL' => 'chyba', 'none' => 'zatím neproběhla'][$ops['health']] ?? $ops['health'];
    $html = '<section class="c70-card" aria-labelledby="ad70-ops"><h2 id="ad70-ops">Provoz</h2><ul class="c70-facts">'
        . '<li><span>Úložiště</span><strong>' . e(ad70_bytes($ops['size'])) . '</strong></li><li><span>Souborů</span><strong>' . e($ops['files'] === null ? '—' : (string)$ops['files']) . '</strong></li>'
        . '<li><span>Volné místo</span><strong>' . e(ad70_bytes($ops['free'] === null ? null : (int)$ops['free'])) . '</strong></li>'
        . '<li><span>Poslední záloha</span><strong>' . e($ops['backup_at'] !== '' ? $ops['backup_at'] : 'chybí') . '</strong></li>'
        . '<li><span>Týdenní kontrola</span><strong>' . e($health) . '</strong></li><li><span>PHP</span><strong>' . e(PHP_VERSION) . '</strong></li>'
        . '<li><span>Rozšíření PHP</span><strong>' . e($ops['missing_ext'] === [] ? 'vše dostupné' : 'chybí ' . implode(', ', $ops['missing_ext'])) . '</strong></li></ul>';
    $link = ad70_link('provoz', 'Detail provozu, retence a top soubory →');
    return $html . ($link !== '' ? '<p>' . $link . '</p>' : '') . '</section>';
}

function ad70_data_html(array $o): string
{
    $q = $o['data']['quality'];
    $id = $o['data']['identity'];
    $html = '<section class="c70-card" aria-labelledby="ad70-data"><h2 id="ad70-data">Data žáků</h2><ul class="c70-facts">';
    $html .= is_array($q) ? '<li><span>Kvalita dat</span><strong>' . ($q['high'] + $q['medium'] + $q['low'] === 0 ? 'bez nálezů' : e($q['high'] . ' vážné · ' . $q['medium'] . ' střední · ' . $q['low'] . ' drobné')) . '</strong></li>' : '';
    $html .= is_array($id) ? '<li><span>Registr identit</span><strong>' . (int)$id['active'] . ' aktivních z ' . (int)$id['total'] . '</strong></li><li><span>Kolize jmen</span><strong>' . (int)$id['duplicates'] . '</strong></li>' : '';
    $links = array_filter([ad70_link('quality', 'Kvalita dat'), ad70_link('identita', 'Identita a nový rok'), ad70_link('ops_audit', 'Audit operací')]);
    return $html . '</ul>' . ($links !== [] ? '<p class="c70-actions">' . implode(' ', $links) . '</p>' : '') . '</section>';
}

function ad70_events_html(array $o): string
{
    $events = $o['accounts']['events'];
    if ($o['accounts']['mode'] !== 'accounts') return '';
    $html = '<section class="c70-card" aria-labelledby="ad70-ev"><h2 id="ad70-ev">Poslední události účtů</h2>';
    if ($events === []) return $html . '<p class="c70-muted">Zatím žádné události.</p></section>';
    $html .= '<ul class="c70-linklist">';
    foreach ($events as $ev) {
        $ts = strtotime($ev['at']) ?: 0;
        $html .= '<li><time datetime="' . e($ev['at']) . '">' . e($ts > 0 ? date('j. n. H:i', $ts) : '—') . '</time> ' . e($ev['event']) . ($ev['target'] !== '' ? ' · <code>' . e($ev['target']) . '</code>' : '') . '</li>';
    }
    return $html . '</ul></section>';
}
