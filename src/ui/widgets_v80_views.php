<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/widgets_v80.php';
require_once __DIR__ . '/learning_v80_views.php';

/**
 * EDUCANET v80 · stránka Interaktivní cvičení (?view=cviceni, ?w=<cvičení>). Formulář funguje bez JS a kontroluje ho server;
 * assets/practice-v80.js jen živě přepočítává pomůcky. Šablony živých textů nese HTML (data-t, přeložené přes tr()), JS nemá vlastní texty.
 * Každý ovládací prvek má popisek, cíl ≥ 44 px a viditelný fokus; výsledek a živé výpočty jsou v oblasti aria-live.
 */

function wg80_title(string $w): string
{
    $ids = ['subnet' => trm('Kalkulačka podsítí'), 'chmod' => trm('Stavebnice oprávnění chmod'), 'color' => trm('Míchání barev HEX a RGB'), 'spec' => trm('Souboj specificity CSS')];
    $msgid = $ids[$w] ?? null;
    return $msgid !== null ? tr($msgid) : $w;
}

function wg80_desc(string $w): string
{
    return match ($w) {
        'subnet' => tr('Vyzkoušej si, jak prefix mění masku a velikost bloku, a pak urči síť a broadcast.'),
        'chmod' => tr('Zaškrtávej práva r, w, x a sleduj, jak se mění osmičkový zápis.'),
        'color' => tr('Posuvníky namíchej barvu a sleduj její HEX kód.'),
        'spec' => tr('Dva selektory proti sobě – spočítej specificitu a urči vítěze.'),
        default => '',
    };
}

/** Seznam cvičení. */
function wg80_render_list(string $classId, array $state): void
{
    echo '<h1>' . e(tr('Interaktivní cvičení')) . '</h1><p class="lr80-lead">' . e(tr('Krátká cvičení, ve kterých si věci vyzkoušíš rukama. Pomůcky počítají hned v prohlížeči, odpověď zkontroluje server. Nic se nepočítá do známky.')) . '</p><ul class="lr80-cards">';
    foreach (wg80_for_class($classId) as $i => $w) {
        $ok = (int)(($state['w'][$w] ?? [])['ok'] ?? 0);
        echo '<li class="ds-card lr80-card"><h2>' . e(wg80_title($w)) . '</h2><p>' . e(wg80_desc($w)) . '</p><p>'
            . ($i < 2 ? '<span class="ds-badge is-info">' . e(tr('k tvému předmětu')) . '</span> ' : '')
            . '<span class="ds-badge' . ($ok > 0 ? ' is-ok' : '') . '">' . e($ok > 0 ? tr('vyřešeno {n}×', ['n' => $ok]) : tr('zatím nezkoušeno')) . '</span></p>'
            . '<a class="ds-btn ds-btn--primary" href="?' . e(http_build_query(['view' => 'cviceni', 'w' => $w])) . '">' . e(tr('Otevřít cvičení')) . '</a></li>';
    }
    echo '</ul>';
}

/** Výsledek poslední kontroly (relace). */
function wg80_result_html(?array $last, array $variantFields): string
{
    if ($last === null) return '<div id="lr80-w-result" class="lr80-live" role="status" aria-live="polite"></div>';
    if (!empty($last['ok'])) {
        $html = '<p><strong>' . e(tr('Správně, všechno sedí.')) . '</strong> ' . e(tr('Níže je nový příklad.')) . '</p>';
    } elseif (!empty($last['shown'])) {
        $html = '<p><strong>' . e(tr('Tentokrát to nevyšlo – tady je řešení.')) . '</strong> ' . e(tr('Níže je nový příklad.')) . '</p>';
    } else {
        $html = '<p><strong>' . e(tr('Správně {good} z {all}.', ['good' => (int)$last['correct'], 'all' => (int)$last['all']])) . '</strong> ' . e(tr('Zbývá pokusů: {n}.', ['n' => (int)$last['left']])) . '</p>';
    }
    if (!empty($last['why'])) {
        $html .= '<ul class="lr80-why-list">';
        foreach ((array)$last['why'] as $text) $html .= '<li' . edu_content_lang_attr() . '>' . e((string)$text) . '</li>';
        $html .= '</ul>';
    }
    if ((string)($last['explain'] ?? '') !== '') $html .= '<p class="lr80-muted"' . edu_content_lang_attr() . '>' . e((string)$last['explain']) . '</p>';
    return '<div id="lr80-w-result" class="lr80-live lr80-result ' . (!empty($last['ok']) ? 'is-ok' : 'is-miss') . '" role="status" aria-live="polite" tabindex="-1">' . $html . '</div>';
}

function wg80_input(string $name, string $label, string $mode = 'text', string $help = '', string $extra = ''): string
{
    $id = 'w80-' . $name;
    return '<div class="ds-field"><label class="ds-label" for="' . e($id) . '">' . e($label) . '</label><input class="ds-input" id="' . e($id) . '" name="' . e($name) . '" type="text" inputmode="' . e($mode) . '"'
        . ' autocomplete="off" spellcheck="false" maxlength="40"' . ($help !== '' ? ' aria-describedby="' . e($id) . '-h"' : '') . $extra . '>' . ($help !== '' ? '<p class="ds-help" id="' . e($id) . '-h"' . edu_content_lang_attr() . '>' . e($help) . '</p>' : '') . '</div>';
}

function wg80_subnet_html(array $v): string
{
    $p = (int)$v['data']['prefix'];
    $mask = tb75_long2ip(tb75_mask($p));
    $k = min(3, intdiv($p, 8));
    $t = tr('Maska {mask} · blok {size} adres · v {o}. oktetu bloky po {step}');
    $out = strtr($t, ['{mask}' => $mask, '{size}' => (string)(2 ** (32 - $p)), '{o}' => (string)($k + 1), '{step}' => (string)(2 ** (8 - ($p - 8 * $k)))]);
    return '<fieldset class="lr80-tool"><legend>' . e(tr('Kalkulačka (pomůcka)')) . '</legend><div class="ds-field"><label class="ds-label" for="w80-pfx">' . e(tr('Prefix (počet jedniček v masce)')) . '</label>'
        . '<input class="ds-input lr80-num" id="w80-pfx" type="number" inputmode="numeric" min="0" max="32" value="' . $p . '" data-w80-prefix></div>'
        . '<output class="lr80-out" for="w80-pfx" aria-live="polite" data-w80-calc data-t="' . e($t) . '">' . e($out) . '</output></fieldset>'
        . wg80_input('network', tr('Síťová adresa'), 'decimal', 'např. 192.168.1.0') . wg80_input('broadcast', tr('Broadcastová adresa'), 'decimal', 'např. 192.168.1.255') . wg80_input('hosts', tr('Použitelných adres'), 'numeric');
}

function wg80_chmod_html(array $v): string
{
    $groups = ['u' => tr('Vlastník (u)'), 'g' => tr('Skupina (g)'), 'o' => tr('Ostatní (o)')];
    $perms = ['r' => tr('čtení (r)'), 'w' => tr('zápis (w)'), 'x' => tr('spouštění (x)')];
    $html = '<div class="lr80-chmod">';
    foreach ($groups as $g => $label) {
        $html .= '<fieldset class="lr80-perm"><legend>' . e($label) . '</legend>';
        foreach ($perms as $p => $pl) $html .= '<label class="ds-check lr80-check"><input type="checkbox" name="p[]" value="' . e($g . $p) . '" data-w80-bit="' . (int)(1 << (8 - array_search($g . $p, WG80_PERMS, true))) . '"> ' . e($pl) . '</label>';
        $html .= '</fieldset>';
    }
    $t = tr('Osmičkově {oct} · symbolicky {sym}');
    return $html . '</div><output class="lr80-out" aria-live="polite" data-w80-perm data-t="' . e($t) . '">' . e(strtr($t, ['{oct}' => '000', '{sym}' => '---------'])) . '</output>'
        . wg80_input('mode', tr('Nebo napiš osmičkově (zaškrtávátka se nastaví sama)'), 'numeric', '', ' pattern="0?[0-7]{3}" data-w80-oct');
}

function wg80_color_html(array $v): string
{
    $hex = (string)$v['data']['hex'];
    $html = '<div class="lr80-color"><div class="lr80-swatches"><span class="lr80-swatch" style="background:' . e($hex) . '" aria-hidden="true"></span><span>' . e(tr('Cílová barva')) . ' <code>' . e($hex) . '</code></span>'
        . '<span class="lr80-swatch" data-w80-swatch aria-hidden="true"></span><span>' . e(tr('Tvoje barva')) . '</span></div>';
    foreach (['r' => tr('Červená (R)'), 'g' => tr('Zelená (G)'), 'b' => tr('Modrá (B)')] as $c => $label) {
        $html .= '<div class="ds-field lr80-chan"><label class="ds-label" for="w80-' . $c . '">' . e($label) . '</label><input class="ds-input lr80-num" id="w80-' . $c . '" name="' . $c . '" type="number" inputmode="numeric" min="0" max="255" value="0" data-w80-chan="' . $c . '">'
            . '<input class="lr80-range" type="range" min="0" max="255" value="0" aria-label="' . e(tr('{barva} – posuvník', ['barva' => $label])) . '" data-w80-range="' . $c . '"></div>';
    }
    $t = tr('Tvoje barva: {hex} · rgb({r}, {g}, {b})');
    return $html . '</div><output class="lr80-out" aria-live="polite" data-w80-color data-t="' . e($t) . '">' . e(strtr($t, ['{hex}' => '#000000', '{r}' => '0', '{g}' => '0', '{b}' => '0'])) . '</output>';
}

function wg80_spec_html(array $v): string
{
    $html = '<div class="lr80-duel">';
    foreach (['a' => 'A', 'b' => 'B'] as $k => $name) $html .= '<div class="lr80-duel__side"><p class="lr80-duel__name">' . e(tr('Pravidlo {x}', ['x' => $name])) . '</p><pre class="lr80-code"><code>' . e((string)$v['data'][$k]) . ' { color: … }</code></pre></div>';
    $html .= '</div>' . wg80_input('sa', tr('Specificita A (a,b,c)'), 'text', 'např. 0,2,1', ' data-w80-spec="a"') . wg80_input('sb', tr('Specificita B (a,b,c)'), 'text', 'např. 1,0,2', ' data-w80-spec="b"')
        . '<fieldset class="lr80-perm"><legend>' . e(tr('Které pravidlo vyhraje?')) . '</legend>';
    foreach (['a' => 'A', 'b' => 'B'] as $k => $name) $html .= '<label class="ds-check lr80-check"><input type="radio" name="win" value="' . $k . '"> ' . e(tr('Pravidlo {x}', ['x' => $name])) . '</label>';
    $t = tr('Podle tvých čísel vyhrává {x}.');
    return $html . '</fieldset><output class="lr80-out" aria-live="polite" data-w80-duel data-t="' . e($t) . '" data-empty="' . e(tr('Zapiš obě trojice ve tvaru 0,1,2 – porovnám je za tebe.')) . '" data-tie="' . e(tr('Podle tvých čísel je to remíza – zkontroluj počty.')) . '">'
        . e(tr('Zapiš obě trojice ve tvaru 0,1,2 – porovnám je za tebe.')) . '</output>';
}

/** Stránka jednoho cvičení nebo seznam. */
function wg80_render_page(string $classId, string $studentKey, string $w): void
{
    $ref = lr80_ref($classId, $studentKey);
    $state = lr80_state($classId, $ref);
    echo lr80_assets_html(isset(WG80_WIDGETS[$w])) . '<div class="ds78 lr80-page lr80-widgets">';
    if (!isset(WG80_WIDGETS[$w])) {
        wg80_render_list($classId, $state);
        echo '</div>';
        return;
    }
    $round = max(1, (int)(($state['w'][$w] ?? [])['r'] ?? 1));
    $v = wg80_variant($w, $ref, $round);
    $last = is_array($_SESSION['lr80_w_last'] ?? null) && (string)($_SESSION['lr80_w_last']['w'] ?? '') === $w ? $_SESSION['lr80_w_last'] : null;
    echo '<p><a class="ui-link" href="?view=cviceni">← ' . e(tr('Interaktivní cvičení')) . '</a></p><h1>' . e(wg80_title($w)) . '</h1>'
        . wg80_result_html($last, $v['fields'])
        . '<section class="ds-card lr80-sec" aria-labelledby="lr80-w-h"><h2 id="lr80-w-h">' . e(tr('Příklad {n}', ['n' => $round])) . '</h2><p' . edu_content_lang_attr() . '>' . e((string)$v['prompt']) . '</p>'
        . '<form method="post" class="lr80-form" data-w80="' . e($w) . '">' . lr80_csrf_html('lr80_w_check') . '<input type="hidden" name="w" value="' . e($w) . '">'
        . match ($w) { 'subnet' => wg80_subnet_html($v), 'chmod' => wg80_chmod_html($v), 'color' => wg80_color_html($v), default => wg80_spec_html($v) }
        . '<div class="lr80-actions"><button class="ds-btn ds-btn--primary" type="submit">' . e(tr('Zkontrolovat')) . '</button></div></form>'
        . '<p class="lr80-note">' . e(tr('Pokusů na příklad: {n}. Chyba se ti vrátí v opakování, aby sis ji opravil(a).', ['n' => WG80_TRIES])) . '</p></section></div>';
}
