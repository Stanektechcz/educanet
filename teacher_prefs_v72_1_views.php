<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · záložka „Nastavení výuky“ (modul `nastaveni_vyuky`, sekce Výuka; cockpit vždy česky).
 *   1) Jména žáků v přehledech, na tisku a projektoru – osobní volba učitele (tp721_self_names, každá role, výchozí: zobrazit).
 *   2) Exit ticket po třídách – „jen zpětná vazba“ / „podklad ke známce“ (tp721_class_exit, třída v rozsahu, content.manage;
 *      asistent jen čte).
 *   3) Přehled rozhodnutí školy z 8. 10. 2026 s odkazy.
 * GET jen ?class= (obecná kontrola rozsahu teacher59); žádný nový GET parametr registru. Každý výstup přes e(); data žáků se nezobrazují.
 */

require_once __DIR__ . '/teacher_prefs_v72_1.php';
if (is_file(__DIR__ . '/cockpit_v70.php')) require_once __DIR__ . '/cockpit_v70.php';

function tp721_render_tab(array $modules, string $classId, string $csrf): void
{
    $classes = function_exists('c70_classes') ? c70_classes() : [];
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · rozhodnutí školy</div><h1>Nastavení výuky</h1>'
        . '<p>Jména žáků v přehledech a režim exit ticketu ve vašich třídách. Změny se projeví hned.</p></div></section>';
    echo tp721_names_card_html($csrf);
    echo tp721_exit_card_html($classes, $modules, $csrf);
    echo tp721_decisions_html();
}

/** Karta 1: jména žáků (osobní nastavení). */
function tp721_names_card_html(string $csrf): string
{
    $hidden = tp721_names_hidden();
    $radio = static fn(string $value, string $label, bool $checked): string => '<label class="c70-radio" for="tp721-names-' . $value . '"><input type="radio" id="tp721-names-' . $value . '" name="names" value="' . $value . '"'
        . ($checked ? ' checked' : '') . '> ' . e($label) . '</label>';
    return '<section class="c70-card" aria-labelledby="tp721-names-h"><h2 id="tp721-names-h">Jména žáků v přehledech, na tisku a projektoru</h2>'
        . '<p>Škola jména povolila. Když je skryjete, uvidíte místo nich „Žák 1, Žák 2, …“ (třeba při promítání). Platí jen pro vás.</p>'
        . '<p><span class="c70-chip' . ($hidden ? ' c70-chip-warn' : ' c70-chip-ok') . '">' . ($hidden ? 'teď skrytá' : 'teď zobrazená') . '</span></p>'
        . '<form method="post" action="teacher.php" class="c70-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="tp721_self_names">'
        . '<input type="hidden" name="return_tab" value="nastaveni_vyuky"><fieldset class="c70-fieldset"><legend>Jména žáků</legend>'
        . $radio('shown', 'Zobrazit jména (výchozí)', !$hidden) . $radio('hidden', 'Skrýt jména', $hidden)
        . '</fieldset><button class="btn primary" type="submit">Uložit</button></form></section>';
}

/** Karta 2: režim exit ticketu po třídách. */
function tp721_exit_card_html(array $classes, array $modules, string $csrf): string
{
    $html = '<section class="c70-card c70-wide" aria-labelledby="tp721-exit-h"><h2 id="tp721-exit-h">Exit ticket: zpětná vazba, nebo podklad ke známce</h2>'
        . '<p><strong>Jen zpětná vazba</strong> (výchozí): odpovědi vidíte v Dnešní hodině, do hodnocení nevstupují. <strong>Podklad ke známce</strong>: odpovědi dané od přepnutí '
        . 'jdou do návrhu hodnocení v66 jako jedna položka části „Sumativní testy“ – váhy 40 / 40 / 20 se nemění a návrh se počítá, jen když ho ve třídě zapnul administrátor. '
        . 'Žák vidí režim ještě před odpovědí. Po přepnutí zpět na zpětnou vazbu se exit tickety do návrhu nepočítají.</p>';
    if ($classes === []) return $html . '<p class="c70-muted">Nemáte přiřazenou žádnou třídu.</p></section>';
    $can = function_exists('teacher_permission') && teacher_permission('content.manage');
    if (!$can) $html .= '<p class="c70-note" role="status">Máte jen čtení – režim mění učitel třídy nebo administrátor.</p>';
    $html .= '<div class="c72-scroll"><table class="c70-table"><caption class="c70-sr">Režim exit ticketu po třídách</caption><thead><tr><th scope="col">Třída</th><th scope="col">Režim</th>'
        . '<th scope="col">Návrh hodnocení</th><th scope="col">Změna</th></tr></thead><tbody>';
    foreach ($classes as $cid) {
        $rec = tp721_exit_record((string)$cid);
        $g66 = function_exists('g66_enabled') ? (g66_enabled((string)$cid) ? 'zapnutý' : 'vypnutý (zapíná administrátor)') : '—';
        $change = $rec['mode'] === 'grade' ? 'feedback' : 'grade';
        $cell = '<span class="c70-chip' . ($rec['mode'] === 'grade' ? ' c70-chip-warn' : '') . '">' . e(TP721_EXIT_MODES[$rec['mode']]) . '</span>';
        if ($rec['at'] !== '') $cell .= '<br><span class="c70-muted">' . e(date('j. n. Y H:i', (int)strtotime($rec['at']))) . ' · ' . e($rec['by']) . '</span>';
        $form = $can ? '<form method="post" action="teacher.php" class="c70-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="tp721_class_exit">'
            . '<input type="hidden" name="return_tab" value="nastaveni_vyuky"><input type="hidden" name="class_id" value="' . e((string)$cid) . '"><input type="hidden" name="mode" value="' . $change . '">'
            . '<button class="btn secondary" type="submit">Přepnout na „' . e(TP721_EXIT_MODES[$change]) . '“</button></form>' : '—';
        $html .= '<tr><th scope="row">' . e((string)($modules[$cid]['name'] ?? $cid)) . '</th><td data-label="Režim">' . $cell . '</td><td data-label="Návrh hodnocení">' . e($g66) . '</td><td data-label="Změna">' . $form . '</td></tr>';
    }
    return $html . '</tbody></table></div></section>';
}

/** Karta 3: rozhodnutí školy z 8. 10. 2026 (jen informace). */
function tp721_decisions_html(): string
{
    $link = static fn(string $tab, string $label, string $anchor = ''): string => '<a class="c70-link" href="' . e('?tab=' . $tab . $anchor) . '">' . e($label) . '</a>';
    return '<section class="c70-card" aria-labelledby="tp721-dec-h"><h2 id="tp721-dec-h">Rozhodnutí školy (8. 10. 2026)</h2><ul class="c71-list">'
        . '<li><strong>ŠVP:</strong> vazba lekcí na výsledky ŠVP „Inteligentní digitální systémy 2026“ je návrh – ' . $link('schvalovani', 'schvalte ji v sekci Vazba na ŠVP', '#svp72') . '.</li>'
        . '<li><strong>Domácí příprava:</strong> dobrovolná, žák ji vidí s označením „dobrovolné“.</li>'
        . '<li><strong>Jména žáků:</strong> povolená, s vaším přepínačem výše.</li>'
        . '<li><strong>AI prompty pro studenta:</strong> žák je má v lekci jako dobrovolné procvičení s upozorněním na soukromí (bez osobních a přihlašovacích údajů, s ověřením a uvedením použití AI).</li>'
        . '<li><strong>Exit ticket:</strong> podle vaší volby výše.</li></ul></section>';
}
