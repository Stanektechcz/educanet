<?php

declare(strict_types=1);

/**
 * EDUCANET v81 · modernější vzhled – sdílené stavební prvky (jen prezentace, žádná data ani zápisy).
 *   i81()            ikona ze spritu assets/icons-v81.svg (currentColor, aria-hidden, focusable=false),
 *   v81_head_html()  hlavička stránky: nadpis, jedna věta o účelu, jedna primární akce (+ max. 2 sekundární),
 *   v81_state_html() prázdný / úspěšný / chybový stav s ikonou (role=status, chyba role=alert),
 *   v81_hint_html()  první návštěva: „Jak to funguje“ (skrytá bez JS; JS ji ukáže, dokud ji uživatel nezavře – pamatuje si jen „zavřeno“),
 *   v81_density_html() přepínač Komfortní / Kompaktní pro husté tabulky cockpitu (jen JS, localStorage v try/catch),
 *   v81_assets_html() / v81_cockpit_assets_html() odkazy na CSS a JS vrstvy.
 * Texty cockpitu jsou česky bez tr(); prvky žáka dostávají přeložené texty od volajícího (tr()).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

const UI81_ICONS = ['home', 'sun', 'tasks', 'target', 'review', 'folder', 'chart', 'arrow', 'info', 'ok', 'warn', 'error', 'close', 'bulb', 'clock', 'users',
    'calendar', 'rows', 'flag', 'book', 'print', 'screen', 'inbox', 'spark'];
const UI81_SPRITE = 'assets/icons-v81.svg';
/** Pohledy žáka s vrstvou v81 (hlavička, nápověda, toast). Domů má navíc assets/home-v81.css. */
const UI81_STUDENT_VIEWS = ['dashboard', 'pokrok'];
/** Záložky cockpitu s přepínačem hustoty (husté tabulky). */
const UI81_DENSITY_TABS = ['class_results', 'streda77'];

/** Odkaz z kódu: relativní adresa aplikace (?…, soubor.php?…) projde, absolutní jen http(s) přes safe_url(), jinak prázdný řetězec. */
function ui81_href(string $url): string
{
    if (preg_match('~^(?:\?|[a-z0-9_-]+\.php(?:\?|$))[^\s"<>]*$~i', $url) === 1) return $url;
    return function_exists('safe_url') && preg_match('~^https?://~i', $url) === 1 ? (string)safe_url($url) : '';
}

/** Verze souboru pro ?v= (čas změny; jako asset_url žáka). */
function ui81_ver(string $rel): string
{
    $f = __DIR__ . '/' . $rel;
    return is_file($f) ? (string)filemtime($f) : '81';
}

/** Ikona ze spritu. Neznámý název = prázdný řetězec (nic se neinterpoluje). */
function i81(string $name, string $class = ''): string
{
    if (!in_array($name, UI81_ICONS, true)) return '';
    static $href = null;
    $href ??= UI81_SPRITE . '?v=' . ui81_ver(UI81_SPRITE);
    return '<svg class="i81' . ($class !== '' ? ' ' . e($class) : '') . '" aria-hidden="true" focusable="false" width="20" height="20"><use href="' . e($href) . '#i-' . $name . '"/></svg>';
}

/**
 * Hlavička stránky. $actions = [[url, popisek, 'primary'|'secondary', ikona?], …] – nejvýš jedna primární (další se vykreslí jako sekundární).
 * $btn = 'ds' (tlačítka ds-btn, cockpit) nebo 'legacy' (btn primary/secondary, žák).
 */
function v81_head_html(string $title, string $lead, array $actions = [], string $eyebrow = '', string $icon = '', string $btn = 'ds', string $extraClass = ''): string
{
    $html = '<header class="v81-head ds78' . ($extraClass !== '' ? ' ' . e($extraClass) : '') . '"><div class="v81-head__text">';
    if ($eyebrow !== '') $html .= '<p class="v81-head__eyebrow">' . i81($icon !== '' ? $icon : 'sun') . '<span>' . e($eyebrow) . '</span></p>';
    $html .= '<h1>' . e($title) . '</h1>' . ($lead !== '' ? '<p class="v81-head__lead">' . e($lead) . '</p>' : '') . '</div>';
    if ($actions !== []) {
        $html .= '<div class="v81-head__actions">';
        $primaryUsed = false;
        foreach (array_slice($actions, 0, 3) as $a) {
            $url = ui81_href((string)($a[0] ?? ''));
            if ($url === '' || $url === '#') continue;
            $primary = ($a[2] ?? '') === 'primary' && !$primaryUsed;
            $primaryUsed = $primaryUsed || $primary;
            $cls = $btn === 'legacy' ? ('btn ' . ($primary ? 'primary' : 'secondary')) : ('ds-btn ' . ($primary ? 'ds-btn--primary' : 'ds-btn--secondary'));
            $html .= '<a class="' . $cls . ' v81-btn" href="' . e($url) . '">' . (isset($a[3]) ? i81((string)$a[3]) : '') . '<span>' . e((string)($a[1] ?? '')) . '</span></a>';
        }
        $html .= '</div>';
    }
    return $html . '</header>';
}

/** Stav: $kind = empty|ok|error|info; $actions jako u hlavičky (jen odkazy). */
function v81_state_html(string $kind, string $title, string $text = '', array $actions = [], string $tag = 'h2'): string
{
    $kind = in_array($kind, ['empty', 'ok', 'error', 'info'], true) ? $kind : 'empty';
    $icon = ['empty' => 'inbox', 'ok' => 'ok', 'error' => 'error', 'info' => 'info'][$kind];
    $tag = in_array($tag, ['h2', 'h3', 'p'], true) ? $tag : 'h2';
    $html = '<div class="v81-state ds78 is-' . $kind . '" role="' . ($kind === 'error' ? 'alert' : 'status') . '"><span class="v81-state__icon">' . i81($icon) . '</span><div>'
        . '<' . $tag . ' class="v81-state__title">' . e($title) . '</' . $tag . '>' . ($text !== '' ? '<p>' . e($text) . '</p>' : '');
    $links = '';
    foreach ($actions as $a) {
        $url = ui81_href((string)($a[0] ?? ''));
        if ($url !== '' && $url !== '#') $links .= '<a class="v81-state__link" href="' . e($url) . '">' . e((string)($a[1] ?? '')) . i81('arrow') . '</a>';
    }
    return $html . ($links !== '' ? '<p class="v81-state__actions">' . $links . '</p>' : '') . '</div></div>';
}

/**
 * Nápověda při první návštěvě. $id = krátký identifikátor stránky (a-z0-9-), $items = 2–4 krátké body.
 * Bez JS zůstává skrytá (atribut hidden) – JS ji ukáže, dokud ji uživatel nezavře (localStorage „v81-hint-<id>“ = 1, v try/catch).
 */
function v81_hint_html(string $id, string $title, array $items, string $closeLabel = 'Rozumím, skrýt nápovědu', string $regionLabel = 'Nápověda'): string
{
    if (preg_match('/^[a-z0-9-]{2,32}$/', $id) !== 1 || $items === []) return '';
    $html = '<aside class="v81-hint ds78" data-v81-hint="' . $id . '" aria-label="' . e($regionLabel) . '" hidden><span class="v81-hint__icon">' . i81('bulb') . '</span><div class="v81-hint__body">'
        . '<h2 class="v81-hint__title">' . e($title) . '</h2><ol>';
    foreach (array_slice($items, 0, 4) as $item) $html .= '<li>' . e((string)$item) . '</li>';
    return $html . '</ol></div><button type="button" class="v81-hint__close" data-v81-hint-close aria-label="' . e($closeLabel) . '">' . i81('close') . '</button></aside>';
}

/** Přepínač hustoty tabulek Komfortní / Kompaktní (skrytý bez JS; volba v localStorage na tomto počítači a pro tohoto učitele, bez zápisu na server). */
function v81_density_html(): string
{
    $who = function_exists('tp721_teacher_hash') ? substr(tp721_teacher_hash(), 0, 8) : 'x';
    return '<div class="v81-density ds78" role="group" aria-label="Hustota tabulek" data-v81-density-wrap data-v81-who="' . e($who) . '" hidden>' . i81('rows')
        . '<span class="v81-density__label">Hustota</span>'
        . '<button type="button" class="v81-density__btn" data-v81-density="comfortable" aria-pressed="true">Komfortní</button>'
        . '<button type="button" class="v81-density__btn" data-v81-density="compact" aria-pressed="false">Kompaktní</button></div>';
}

/** Inline skript do <head>: kompaktní hustota se nastaví před vykreslením (bez probliknutí); jen na záložkách s přepínačem. */
function v81_density_head_js(): string
{
    $who = function_exists('tp721_teacher_hash') ? substr(tp721_teacher_hash(), 0, 8) : 'x';
    return '<script>try{if(localStorage.getItem("v81-density-' . e($who) . '")==="compact")document.documentElement.setAttribute("data-v81-density","compact")}catch(e){}</script>';
}

/** Odkazy CSS + JS v81 pro žáka (do <head>); $home přidá CSS domovské stránky. */
function v81_assets_html(bool $home = false): string
{
    $out = '<link rel="stylesheet" href="assets/ui-v81.css?v=' . ui81_ver('assets/ui-v81.css') . '">';
    if ($home) $out .= '<link rel="stylesheet" href="assets/home-v81.css?v=' . ui81_ver('assets/home-v81.css') . '">';
    return $out . '<script src="assets/ui-v81.js?v=' . ui81_ver('assets/ui-v81.js') . '" defer></script>';
}

/** Odkazy CSS + JS v81 pro cockpit (na konec <head>, po rámci a opravách v80.2). */
function v81_cockpit_assets_html(): string
{
    return '<link rel="stylesheet" href="assets/ui-v81.css?v=' . ui81_ver('assets/ui-v81.css') . '"><link rel="stylesheet" href="assets/cockpit-v81.css?v=' . ui81_ver('assets/cockpit-v81.css') . '">'
        . '<script src="assets/ui-v81.js?v=' . ui81_ver('assets/ui-v81.js') . '" defer></script>';
}
