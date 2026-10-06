<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v67 · rozhraní žáka: tmavý režim a prázdné stavy.
 *
 * Tmavý režim (jen žákovská část a přihlášení; cockpit učitele zůstává světlý):
 *   - volba světlý / tmavý / podle systému, výchozí je „podle systému“,
 *   - uloží se do cookie edu_theme (jen hodnota light|dark|system, SameSite=Lax, 1 rok, bez osobních údajů),
 *   - server vykreslí <html data-theme="…"> a odkaz na assets/tokens-dark-v67.css (jen přepsané proměnné --ui-*),
 *     takže stránka nebliká: při „podle systému“ rozhoduje media dotaz prohlížeče, při „tmavý“ se soubor načte vždy,
 *     při „světlý“ se vůbec nenačte,
 *   - tmavý vzhled dostanou jen pohledy ze seznamu ui67_dark_views() (ověřené na tokenech v61); ostatní zůstávají
 *     světlé (color-scheme: light), protože starší vrstvy mají pevné barvy.
 * Prázdný stav ui67_empty_state(): jednotný blok „co tu bude + co s tím“ s odkazem na další krok (komponenta .ui-empty).
 */

const UI67_THEMES = ['system', 'light', 'dark'];
const UI67_THEME_COOKIE = 'edu_theme';
const UI67_THEME_COOKIE_TTL = 31536000;
const UI67_DARK_CSS = 'assets/tokens-dark-v67.css';
const UI68_STUDENT_DARK_CSS = 'assets/student-dark-v68.css';   // v68: generovaná tmavá vrstva starších CSS (tools/v68_dark_overlay.php), jen při tmavém motivu

/**
 * Pohledy ověřené pro tmavý režim (stavějí jen na tokenech v61). PRÁZDNÉ záměrně: v prohlížeči (390 a 1280 px) je ověřeno, že přihlášení,
 * přehled i profil mají ve starších vrstvách pevné světlé barvy (karta přihlášení, pozadí, štítky navigace) a tokeny samy nestačí.
 * Pohled sem přidej až po převodu jeho CSS na tokeny v61 a po kontrole v prohlížeči.
 */
const UI67_DARK_VIEWS = ['home', 'dashboard', 'profile'];

/** @return list<string> */
function ui67_dark_views(): array
{
    return UI67_DARK_VIEWS;
}

/** Zvolený motiv z cookie (whitelist); cokoli jiného = „podle systému“. */
function ui67_theme_pref(): string
{
    $raw = $_COOKIE[UI67_THEME_COOKIE] ?? null;
    return is_string($raw) && in_array($raw, UI67_THEMES, true) ? $raw : 'system';
}

/** Motiv platný pro daný pohled: pohled mimo seznam je vždy světlý. */
function ui67_effective_theme(string $view, ?string $pref = null, ?array $darkViews = null): string
{
    $pref ??= ui67_theme_pref();
    return in_array($view, $darkViews ?? ui67_dark_views(), true) ? $pref : 'light';
}

/** Atribut pro <html>: ` data-theme="light|dark|system"` (vždy přítomný, ať se dá motiv odvodit i bez cookie). */
function ui67_html_theme_attr(string $view): string
{
    return ' data-theme="' . e(ui67_effective_theme($view)) . '"';
}

/** Hodnota <meta name="color-scheme"> (řídí prvky prohlížeče: posuvníky, pole formulářů). */
function ui67_color_scheme(string $view, ?string $pref = null, ?array $darkViews = null): string
{
    return ['light' => 'light', 'dark' => 'dark', 'system' => 'light dark'][ui67_effective_theme($view, $pref, $darkViews)];
}

/** <link> na CSS přepínače vzhledu; jen na pohledech, kde se přepínač vykresluje (home, dashboard, profile) – ostatní stránky žáka nic nenavíc nestahují (rozpočet CSS v61). */
function ui67_assets_html(string $view = ''): string
{
    return in_array($view, ui67_dark_views(), true) ? '<link rel="stylesheet" href="' . e(asset_url('assets/ui-v67.css?v=67.0')) . '">' : '';
}

/**
 * Tmavé CSS pro pohled: prázdný řetězec pro světlý pohled/motiv.
 * „tmavý“ = obě šablony stylů napevno (tmavé tokeny + generovaná vrstva starších CSS, v68).
 * „podle systému“ = krátký skript v <head> přidá obě šablony jen tehdy, když systém preferuje tmavý vzhled (document.write → blokuje vykreslení,
 * takže stránka nebliká). Tím se velká tmavá vrstva (~400 KB) nestahuje uživatelům se světlým systémem a rozpočet CSS stránky se nezvyšuje;
 * bez JavaScriptu zůstane „podle systému“ světlé (tmavé tokeny bez tmavé vrstvy by dávaly nečitelnou směs).
 */
function ui67_dark_link_html(string $view, ?string $pref = null, ?array $darkViews = null): string
{
    $theme = ui67_effective_theme($view, $pref, $darkViews);
    if ($theme === 'light') return '';
    $hrefs = [asset_url(UI67_DARK_CSS . '?v=67.0'), asset_url(UI68_STUDENT_DARK_CSS . '?v=68.0')];
    if ($theme === 'dark') {
        return '<link rel="stylesheet" href="' . e($hrefs[0]) . '"><link rel="stylesheet" href="' . e($hrefs[1]) . '">';
    }
    $json = json_encode($hrefs, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    // Ve skriptu záměrně není znak „<“ (String.fromCharCode(60)): jednoduché nástroje (strip_tags v auditech, e-mailové náhledy) by jinak odstranily půl stránky.
    return '<script>(function(){if(!window.matchMedia||!matchMedia("(prefers-color-scheme: dark)").matches)return;var lt=String.fromCharCode(60);' . $json
        . '.forEach(function(x){document.write(lt+\'link rel="stylesheet" href="\'+x+\'">\');});})();</script>';
}

/** Nastaví cookie motivu (jen platná hodnota; vrací, zda se nastavilo). */
function ui67_theme_set(string $theme): bool
{
    if (!in_array($theme, UI67_THEMES, true)) return false;
    if (!headers_sent()) {
        setcookie(UI67_THEME_COOKIE, $theme, ['expires' => time() + UI67_THEME_COOKIE_TTL, 'path' => '/', 'secure' => educanet_is_https(), 'httponly' => true, 'samesite' => 'Lax']);
    }
    $_COOKIE[UI67_THEME_COOKIE] = $theme;
    return true;
}

/** Návratová adresa po přepnutí motivu: jen relativní ?view=… (stejná pravidla jako u jazyka). */
function ui67_return_url(string $raw): string
{
    return preg_match('/^\?[a-zA-Z0-9_=&%.\-]{0,200}$/', $raw) === 1 ? $raw : '?view=dashboard';
}

/**
 * Přepínač vzhledu: formulář POST s CSRF, tři tlačítka (bez JavaScriptu), aktuální volba má aria-pressed.
 * Popisky přes tr(); skupina má název, aby čtečka oznámila, k čemu tlačítka patří.
 */
function ui67_theme_switch_html(string $csrf, string $returnUrl, string $view): string
{
    if (!in_array($view, ui67_dark_views(), true)) return '';   // přepínač jen tam, kde má tmavý vzhled smysl (home, dashboard, profile); jinde by klamal a stál by CSS navíc
    $current = ui67_theme_pref();
    $labels = ['system' => tr('Podle systému'), 'light' => tr('Světlý'), 'dark' => tr('Tmavý')];
    $html = '<form class="ui67-theme" method="post" action="index.php" role="group" aria-label="' . e(tr('Vzhled')) . '">'
        . '<input type="hidden" name="action" value="ui67_theme_set"><input type="hidden" name="csrf" value="' . e($csrf) . '">'
        . '<input type="hidden" name="return" value="' . e(ui67_return_url($returnUrl)) . '">';
    foreach ($labels as $value => $label) {
        $html .= '<button type="submit" name="theme" value="' . e($value) . '" aria-pressed="' . ($value === $current ? 'true' : 'false') . '">' . e($label) . '</button>';
    }
    return $html . '</form>';
}

/**
 * Prázdný stav: nadpis, krátké vysvětlení a (volitelně) tlačítko na další krok. Všechny texty musí být už přeložené
 * (volající použije tr()); odkaz jde přes safe_url(), takže se nikdy nevykreslí cizí schéma.
 */
function ui67_empty_state(string $title, string $hint, string $ctaHref = '', string $ctaLabel = '', string $headingTag = 'h3'): string
{
    $tag = in_array($headingTag, ['h2', 'h3', 'h4', 'p'], true) ? $headingTag : 'h3';
    $cta = '';
    if ($ctaHref !== '' && $ctaLabel !== '') {
        $href = str_starts_with($ctaHref, '?') ? $ctaHref : safe_url($ctaHref);
        if ($href !== '') $cta = '<a class="ui-btn ui-btn--secondary ui67-empty-cta" href="' . e($href) . '">' . e($ctaLabel) . '</a>';
    }
    return '<section class="ui-empty ui67-empty" data-ui67-empty><' . $tag . ' class="ui67-empty-title">' . e($title) . '</' . $tag . '><p>' . e($hint) . '</p>' . $cta . '</section>';
}
