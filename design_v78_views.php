<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v78 · záložka „Design systém“ (modul `design78`, sekce Správa, jen administrátor, jen čtení).
 * Ukázka tokenů a komponent v78 na fiktivních datech: nic se neodesílá ani neukládá (žádný formulář, jen tlačítka type="button").
 * Cockpit česky. Každý výstup přes e(); ikony jsou pevné SVG s currentColor.
 */

require_once __DIR__ . '/design_v78.php';

/** Ikona (24 × 24, tah currentColor); neznámé jméno = prázdný řetězec. */
function ds78_icon(string $name): string
{
    $paths = [
        'home' => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'today' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'tasks' => '<path d="M9 6h11M9 12h11M9 18h11M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'more' => '<circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
        'warn' => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.5"/>',
        'error' => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>',
        'print' => '<path d="M7 9V3h10v6M7 17H4v-7h16v7h-3M7 14h10v7H7z"/>',
        'empty' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M9 14h6"/>',
    ];
    if (!isset($paths[$name])) return '';
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

function ds78_section(string $id, string $title, string $lead, string $body): string
{
    return '<section class="ds-section" aria-labelledby="' . e($id) . '"><h2 id="' . e($id) . '">' . e($title) . '</h2>'
        . ($lead !== '' ? '<p class="ds-muted">' . e($lead) . '</p>' : '') . $body . '</section>';
}

function ds78_ratio(?float $r): string
{
    return $r === null ? '–' : number_format($r, 2, ',', ' ') . ' : 1';
}

function ds78_tokens_html(array $sets): string
{
    $cells = '';
    foreach (ds78_color_tokens() as [$name, $label]) {
        $cells .= '<li class="ds-demo-swatch"><span class="ds-demo-swatch__chip" style="--sw: var(--' . e($name) . ')"></span><span><code>--' . e($name) . '</code><br>'
            . e($label) . '<br><span class="ds-muted ds-small">světlý ' . e((string)($sets['light'][$name] ?? '–')) . ' · tmavý ' . e((string)($sets['dark'][$name] ?? '–')) . '</span></span></li>';
    }
    $type = '';
    foreach ([['fs-2xl', 'Nadpis stránky'], ['fs-xl', 'Nadpis sekce'], ['fs-lg', 'Nadpis karty'], ['fs-base', 'Běžný text 16–18 px'], ['fs-sm', 'Popisek, nikdy pod 14 px']] as [$t, $l]) {
        $type .= '<p class="ds-demo-type" style="font-size: var(--' . e($t) . ')"><code>--' . e($t) . '</code> ' . e($l) . '</p>';
    }
    $space = '';
    foreach ([1 => 4, 2 => 8, 3 => 12, 4 => 16, 5 => 24, 6 => 32, 7 => 48] as $i => $px) {
        $space .= '<li><span class="ds-demo-space" style="width: var(--space-' . $i . ')"></span><code>--space-' . $i . '</code> ' . $px . ' px</li>';
    }
    return ds78_section('ds78-barvy', 'Barvy', 'Sémantické tokeny: komponenty nikdy nepoužívají pevnou barvu. Barvu školy stačí změnit v jednom souboru (assets/tokens-v78.css).', '<ul class="ds-demo-swatches">' . $cells . '</ul>')
        . ds78_section('ds78-typo', 'Typografie, mezery a tvar', 'Plynulá velikost písma (clamp), řádkování 1,5, mezery po 4 px. Na monitoru od 1920 px je písmo větší.',
            '<div class="ds-grid"><div class="ds-card">' . $type . '</div><div class="ds-card"><ul class="ds-demo-spaces">' . $space . '</ul></div>'
            . '<div class="ds-card"><p><span class="ds-demo-shape" style="border-radius: var(--radius-sm)"></span> <code>--radius-sm</code> 4 px</p><p><span class="ds-demo-shape" style="border-radius: var(--radius)"></span> <code>--radius</code> 8 px</p>'
            . '<p><span class="ds-demo-shape" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-2)"></span> <code>--radius-lg</code> + <code>--shadow-2</code></p></div></div>');
}

function ds78_contrast_html(array $rows): string
{
    $body = '';
    foreach ($rows as $r) {
        $badge = $r['ok'] ? '<span class="ds-badge is-ok">AA splněno</span>' : '<span class="ds-badge is-danger">Nesplněno</span>';
        $body .= '<tr><td data-label="Popředí"><code>--' . e($r['fg']) . '</code></td><td data-label="Pozadí"><code>--' . e($r['bg']) . '</code></td>'
            . '<td data-label="Druh">' . ($r['kind'] === 'text' ? 'text (≥ 4,5)' : 'prvek UI (≥ 3)') . '</td><td data-label="Světlý">' . e(ds78_ratio($r['light'])) . '</td>'
            . '<td data-label="Tmavý">' . e(ds78_ratio($r['dark'])) . '</td><td data-label="Stav">' . $badge . '</td></tr>';
    }
    $ok = count(array_filter($rows, static fn(array $r): bool => $r['ok']));
    return ds78_section('ds78-kontrast', 'Kontrast dvojic tokenů', $ok . ' z ' . count($rows) . ' dvojic splňuje WCAG 2.2 AA v obou režimech. Na úzké obrazovce se tabulka mění na karty (container query).',
        '<details class="ds-acc"><summary>Zobrazit všech ' . count($rows) . ' dvojic</summary><div class="ds-acc__body"><div class="ds-table-wrap"><table class="ds-table"><caption>Kontrast popředí a pozadí</caption><thead><tr><th scope="col">Popředí</th><th scope="col">Pozadí</th><th scope="col">Druh</th>'
        . '<th scope="col">Světlý</th><th scope="col">Tmavý</th><th scope="col">Stav</th></tr></thead><tbody>' . $body . '</tbody></table></div></div></details>');
}

function ds78_breakpoints_html(): string
{
    $rows = [
        ['xs', '320–479', 'malý mobil', '1 sloupec, spodní navigace'], ['sm', '480–767', 'mobil', '1 sloupec, větší karty, spodní navigace'],
        ['md', '768–1023', 'tablet na výšku', '2 sloupce, navigace v draweru'], ['lg', '1024–1365', 'notebook', 'boční navigace + obsah'],
        ['xl', '1366–1919', 'PC (výchozí pro cockpit učitele)', 'boční navigace + obsah + kontextový panel'], ['xxl', '≥ 1920', 'velký monitor, projektor', 'obsah max. 1600 px, větší písmo'],
    ];
    $body = '';
    foreach ($rows as [$t, $w, $d, $l]) $body .= '<tr><td data-label="Token"><code>' . e($t) . '</code></td><td data-label="Šířka (px)">' . e($w) . '</td><td data-label="Zařízení">' . e($d) . '</td><td data-label="Rozvržení">' . e($l) . '</td></tr>';
    $layout = '<div class="ds-layout ds-demo-layout" aria-label="Schéma rozvržení"><div class="ds-layout__nav ds-card">Boční navigace (lg+)</div><div class="ds-layout__main ds-card">Obsah</div><div class="ds-layout__aside ds-card">Kontextový panel (xl vpravo, jinak pod obsahem)</div></div>';
    return ds78_section('ds78-breakpointy', 'Breakpointy a rozvržení', 'Mobile-first. Učitelé pracují hlavně na PC, proto je cockpit laděný pro lg a xl, ale funguje od 320 px. Změňte šířku okna a sledujte schéma.',
        '<div class="ds-table-wrap"><table class="ds-table"><caption>Šířky zařízení</caption><thead><tr><th scope="col">Token</th><th scope="col">Šířka (px)</th><th scope="col">Zařízení</th><th scope="col">Rozvržení</th></tr></thead><tbody>'
        . $body . '</tbody></table></div>' . $layout);
}

function ds78_cards_html(): string
{
    $status = '';
    foreach ([['is-ok', 'check', 'Schváleno', '3.A · Lekce 6', 'Obsah lekce je schválený, žáci ho uvidí ve čtvrtek.'],
        ['is-warn', 'warn', 'Schválit do 7 dní', '2.A · Lekce 7', 'Termín T-14 je v pondělí.'],
        ['is-danger', 'error', 'Po termínu', '1.A · Lekce 6', 'Výuka je za 5 dní a lekce není schválená.'],
        ['is-info', 'info', 'Informace', 'Všechny třídy', 'Příští týden je ředitelské volno ve středu.']] as [$tone, $icon, $label, $title, $text]) {
        $status .= '<article class="ds-card ds-status ' . e($tone) . '"><p class="ds-status__label">' . ds78_icon($icon) . e($label) . '</p><h3 class="ds-card__title">' . e($title) . '</h3><p>' . e($text) . '</p></article>';
    }
    $card = '<article class="ds-card"><div class="ds-card__body ds-card__body--split"><div><h3 class="ds-card__title">Dnešní hodina: 3.A</h3><p class="ds-card__meta">Úterý 8:00–9:30 · učebna 204</p>'
        . '<p>Cíl: Na konci hodiny umím nastavit statickou adresu a ověřit spojení příkazem ping.</p></div><span class="ds-badge is-ok">schváleno</span></div>'
        . '<div class="ds-card__actions"><button type="button" class="ds-btn ds-btn--primary">Otevřít hodinu</button><button type="button" class="ds-btn ds-btn--secondary">' . ds78_icon('print') . 'Tisk</button></div></article>';
    $buttons = '<div class="ds-cluster"><button type="button" class="ds-btn ds-btn--primary">Primární</button><button type="button" class="ds-btn ds-btn--secondary">Sekundární</button>'
        . '<button type="button" class="ds-btn ds-btn--danger">Smazat</button><button type="button" class="ds-btn ds-btn--ghost">Textové</button>'
        . '<button type="button" class="ds-btn ds-btn--secondary ds-btn--icon" aria-label="Tisk">' . ds78_icon('print') . '</button><button type="button" class="ds-btn ds-btn--primary" disabled>Nedostupné</button></div>';
    $badges = '<div class="ds-cluster"><span class="ds-badge">neutrální</span><span class="ds-badge is-ok">hotovo</span><span class="ds-badge is-warn">čeká</span><span class="ds-badge is-danger">po termínu</span>'
        . '<span class="ds-badge is-info">nové</span><span class="ds-badge is-accent">3.A</span><span class="ds-badge ds-badge--count" aria-label="4 neschválené">4</span></div>';
    return ds78_section('ds78-karty', 'Karta a stavová karta', 'Stav nese vždy text a ikona, nikdy jen barva. Rozvržení karty se řídí šířkou karty (container query), ne okna.', '<div class="ds-grid">' . $card . $status . '</div>')
        . ds78_section('ds78-tlacitka', 'Tlačítka a odznaky', 'Cíl dotyku nejméně 44 × 44 px. Ikonové tlačítko má vždy aria-label.', '<div class="ds-stack">' . $buttons . $badges . '</div>');
}

function ds78_flow_html(): string
{
    $steps = [['Třída', 'hotovo', 'is-done'], ['Plán', 'aktivní', 'is-current'], ['Materiály', 'čeká', ''], ['Tisk a projektor', 'čeká', '']];
    $li = '';
    foreach ($steps as [$name, $state, $cls]) {
        $li .= '<li class="ds-step ' . e($cls) . '"' . ($cls === 'is-current' ? ' aria-current="step"' : '') . '><span class="ds-step__text">' . e($name) . '<span class="ds-step__state">' . e($state) . '</span></span></li>';
    }
    $stepper = '<div class="ds-stepper-wrap"><p class="ds-stepper__count">Krok 2 ze 4: Plán</p><ol class="ds-stepper" aria-label="Příprava hodiny">' . $li . '</ol></div>';
    $tabs = '<div class="ds-tabs" data-ds-tabs><div class="ds-tabs__list" role="tablist" aria-label="Pohled na třídu">';
    $panels = '';
    foreach ([['prehled', 'Přehled', 'Přehled třídy 3.A: 24 žáků, 3 potřebují pomoc.'], ['ukoly', 'Úkoly', '5 úkolů čeká na kontrolu.'], ['vysledky', 'Výsledky', 'Průměrná úspěšnost exit ticketu 78 %.']] as $i => [$id, $label, $text]) {
        $tabs .= '<button type="button" role="tab" class="ds-tab" id="ds78-tab-' . e($id) . '" aria-controls="ds78-panel-' . e($id) . '" aria-selected="' . ($i === 0 ? 'true' : 'false') . '"' . ($i === 0 ? '' : ' tabindex="-1"') . '>' . e($label) . '</button>';
        $panels .= '<div class="ds-tabs__panel" role="tabpanel" id="ds78-panel-' . e($id) . '" aria-labelledby="ds78-tab-' . e($id) . '" tabindex="0"><p>' . e($text) . '</p></div>';
    }
    $tabs .= '</div>' . $panels . '</div>';
    $acc = '<details class="ds-acc" open><summary>Plán po minutách</summary><div class="ds-acc__body"><p>0–10 min: opakování, 10–40 min: ukázka, 40–80 min: práce ve dvojicích, 80–90 min: exit ticket.</p></div></details>'
        . '<details class="ds-acc"><summary>Materiály</summary><div class="ds-acc__body"><p>Pracovní list, prezentace a úloha v Linux Labu.</p></div></details>'
        . '<details class="ds-acc"><summary>Poznámky pro zástup</summary><div class="ds-acc__body"><p>Plán B offline: úloha na papíře.</p></div></details>';
    return ds78_section('ds78-stepper', 'Stepper', 'Vícekrokový postup: číslo, název a stav kroku, „Krok 2 ze 4“. V úzkém kontejneru zůstane jen aktuální krok s popiskem.', $stepper)
        . ds78_section('ds78-zalozky', 'Záložky a akordeon', 'Záložky se ovládají i šipkami, Home a End. Akordeon je nativní <details>.', '<div class="ds-grid"><div class="ds-card">' . $tabs . '</div><div>' . $acc . '</div></div>');
}

function ds78_table_form_html(): string
{
    $rows = '';
    foreach ([['Adam Ukázkový', '3.A', '92 %', 'is-ok', 'hotovo'], ['Bára Vzorová', '3.A', '64 %', 'is-warn', 'potřebuje pomoc'], ['Cyril Příkladný', '3.A', '—', 'is-danger', 'neodevzdáno']] as [$n, $c, $s, $tone, $st]) {
        $rows .= '<tr><th scope="row" data-label="Žák">' . e($n) . '</th><td data-label="Třída">' . e($c) . '</td><td data-label="Úspěšnost">' . e($s) . '</td><td data-label="Stav"><span class="ds-badge ' . e($tone) . '">' . e($st) . '</span></td></tr>';
    }
    $table = '<div class="ds-table-wrap"><table class="ds-table"><caption>Exit ticket – fiktivní žáci</caption><thead><tr><th scope="col">Žák</th><th scope="col">Třída</th><th scope="col">Úspěšnost</th><th scope="col">Stav</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
    $form = '<div class="ds-card" role="group" aria-labelledby="ds78-form-title"><h3 id="ds78-form-title" class="ds-card__title">Poznámka k hodině</h3>'
        . '<div class="ds-errors" role="alert"><strong>Formulář obsahuje 1 chybu.</strong> <a href="#ds78-f-cil">Cíl hodiny chybí</a></div>'
        . '<div class="ds-field"><label class="ds-label" for="ds78-f-nazev">Název hodiny</label><input class="ds-input" id="ds78-f-nazev" type="text" value="Statická adresa" autocomplete="off" aria-describedby="ds78-f-nazev-h"><p class="ds-help" id="ds78-f-nazev-h">Krátce, jak to uvidí žáci.</p></div>'
        . '<div class="ds-field"><label class="ds-label" for="ds78-f-cil">Cíl hodiny <span class="ds-req" aria-hidden="true">*</span></label><input class="ds-input" id="ds78-f-cil" type="text" required aria-invalid="true" aria-describedby="ds78-f-cil-e ds78-f-cil-h"><p class="ds-error" id="ds78-f-cil-e">Napište cíl hodiny.</p><p class="ds-help" id="ds78-f-cil-h">Začněte „Na konci hodiny umím …“.</p></div>'
        . '<div class="ds-field"><label class="ds-label" for="ds78-f-pozn">Poznámka</label><textarea class="ds-input" id="ds78-f-pozn" rows="3"></textarea></div>'
        . '<label class="ds-check"><input type="checkbox" checked> Připomenout příští hodinu</label></div>';
    return ds78_section('ds78-tabulka', 'Tabulka → karty', 'Pod 768 px šířky kontejneru se řádky mění na karty s popisky (data-label). Hlavička tabulky zůstává pro čtečky.', $table)
        . ds78_section('ds78-formular', 'Pole formuláře', 'Nápověda pod polem, chyba u pole i souhrn nahoře s odkazem. Ukázka nic neodesílá.', $form);
}

function ds78_feedback_html(): string
{
    $empty = '<div class="ds-empty"><span class="ds-empty__icon">' . ds78_icon('empty') . '</span><h3>Zatím žádné odevzdané práce</h3><p>Práce se tu objeví, jakmile je žáci odevzdají. Můžete jim poslat připomínku.</p>'
        . '<button type="button" class="ds-btn ds-btn--primary">Připomenout třídě</button></div>';
    $toast = '<button type="button" class="ds-btn ds-btn--secondary" data-ds-toast="Uloženo. Změny uvidí žáci po obnovení stránky.">Ukázat toast</button>';
    $dialog = '<button type="button" class="ds-btn ds-btn--danger" data-ds-open="ds78-dialog">Otevřít dialog</button>'
        . '<dialog class="ds-dialog" id="ds78-dialog" aria-labelledby="ds78-dialog-title"><div class="ds-dialog__head"><h2 id="ds78-dialog-title">Vrátit lekci k úpravě?</h2>'
        . '<button type="button" class="ds-btn ds-btn--ghost ds-btn--icon" data-ds-close aria-label="Zavřít">' . ds78_icon('close') . '</button></div>'
        . '<div class="ds-dialog__body"><p>Žáci uvidí původní lekci, dokud ji znovu neschválíte. V ukázce se nic nezmění.</p></div>'
        . '<div class="ds-dialog__foot"><button type="button" class="ds-btn ds-btn--secondary" data-ds-close>Zrušit</button><button type="button" class="ds-btn ds-btn--danger" data-ds-close="ok">Vrátit k úpravě</button></div></dialog>';
    $skeleton = '<div class="ds-card" aria-busy="true"><span class="ds-sr">Načítám…</span><div class="ds-stack" aria-hidden="true"><span class="ds-skeleton ds-skeleton--title"></span><span class="ds-skeleton"></span><span class="ds-skeleton"></span><span class="ds-skeleton ds-skeleton--short"></span></div></div>';
    return ds78_section('ds78-stavy', 'Prázdný stav, toast, dialog a skeleton', 'Prázdný stav říká proč je prázdno a co udělat. Toast oznámí výsledek čtečce (role status). Dialog je nativní <dialog> (Esc zavře, fokus se vrátí).',
        '<div class="ds-grid">' . $empty . '<div class="ds-card ds-stack"><div class="ds-cluster">' . $toast . $dialog . '</div><p class="ds-muted ds-small">Toast zmizí po 5 s nebo křížkem.</p></div>' . $skeleton . '</div>');
}

function ds78_nav_html(): string
{
    $crumb = '<nav class="ds-breadcrumb" aria-label="Drobečková navigace (ukázka)"><ol><li><a href="?tab=design78">Cockpit</a></li><li><a href="?tab=design78">Výuka</a></li><li><span aria-current="page">Dnešní hodina</span></li></ol></nav>';
    $items = '';
    foreach ([['home', 'Domů', false, 0], ['today', 'Dnes', true, 0], ['tasks', 'Úlohy', false, 3], ['user', 'Profil', false, 0], ['more', 'Více', false, 0]] as [$icon, $label, $cur, $count]) {
        $items .= '<li><a href="?tab=design78"' . ($cur ? ' aria-current="page"' : '') . '>' . ds78_icon($icon) . '<span>' . e($label) . '</span>'
            . ($count > 0 ? '<span class="ds-badge ds-badge--count" aria-label="' . $count . ' nové">' . $count . '</span>' : '') . '</a></li>';
    }
    $bottom = '<div class="ds-demo-phone"><nav class="ds-bottomnav" aria-label="Spodní navigace (ukázka)"><ul>' . $items . '</ul></nav></div>';
    $links = '';
    foreach ([['Dnešní hodina', true], ['Středa: T-14', false], ['Hromadné schválení', false], ['Přehled třídy', false]] as [$label, $cur]) {
        $links .= '<li><a class="ds-navlink" href="?tab=design78"' . ($cur ? ' aria-current="page"' : '') . '>' . e($label) . '</a></li>';
    }
    $drawer = '<button type="button" class="ds-btn ds-btn--secondary" data-ds-open="ds78-drawer">' . ds78_icon('menu') . 'Otevřít drawer</button>'
        . '<dialog class="ds-drawer" id="ds78-drawer" aria-labelledby="ds78-drawer-title"><div class="ds-drawer__head"><h2 id="ds78-drawer-title">Menu (ukázka)</h2>'
        . '<button type="button" class="ds-btn ds-btn--ghost ds-btn--icon" data-ds-close aria-label="Zavřít menu">' . ds78_icon('close') . '</button></div>'
        . '<nav aria-label="Menu v draweru"><ul class="ds-drawer__nav">' . $links . '</ul></nav></dialog>';
    return ds78_section('ds78-navigace', 'Navigace', 'Drobečky na každé podstránce; spodní lišta pro mobil (aktivní položka barvou i proužkem); drawer pro tablet. Odkazy ukázky vedou zpět sem.',
        '<div class="ds-stack">' . $crumb . '<div class="ds-grid"><div>' . $bottom . '</div><div class="ds-card ds-stack"><ul class="ds-drawer__nav">' . $links . '</ul>' . $drawer . '</div></div></div>');
}

function ds78_render_tab(array $modules = [], string $classId = ''): void
{
    $sets = ds78_token_sets();
    echo '<div class="ds78 ds-demo" id="ds78"><header class="ds-demo-head"><p class="ds-eyebrow">Správa · design systém v78</p><h1>Design systém</h1>'
        . '<p>Tokeny a komponenty pro postupné sjednocení vzhledu cockpitu i žákovské části (mobil, tablet, notebook, PC; světlý i tmavý režim). '
        . 'Barvy vycházejí z identity školy a splňují WCAG 2.2 AA. Stránka je jen ukázka na fiktivních datech – nic se tu neukládá.</p>'
        . '<div class="ds-cluster"><span class="ds-badge is-accent">fáze A</span><span class="ds-badge is-info">jen administrátor</span></div></header>';
    echo ds78_tokens_html($sets), ds78_contrast_html(ds78_contrast_rows($sets)), ds78_breakpoints_html(), ds78_cards_html(), ds78_flow_html(), ds78_table_form_html(), ds78_feedback_html(), ds78_nav_html();
    echo ds78_section('ds78-lista', 'Lepicí akční lišta', 'Drží hlavní akce formuláře nebo hromadné volby u spodního okraje, dokud je sekce vidět. Na mobilu tlačítka zalomí pod sebe.',
        '<div class="ds-demo-sticky"><div class="ds-card"><p>Vybráno 2 z 12 lekcí ke schválení (fiktivní data). Při posunu stránky zůstává lišta dole, dokud neopustíte tuto sekci.</p><p class="ds-muted">Lišta má roli region a vlastní popisek.</p></div>'
        . '<div class="ds-actionbar" role="region" aria-label="Akční lišta (ukázka)"><span class="ds-actionbar__info">2 změny čekají</span>'
        . '<button type="button" class="ds-btn ds-btn--secondary">Zahodit</button><button type="button" class="ds-btn ds-btn--primary" data-ds-toast="Ukázka: nic se neuložilo.">Uložit</button></div></div>');
    echo '<div class="ds-toasts" role="status" aria-live="polite"></div></div>';
}
