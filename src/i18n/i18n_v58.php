<?php

declare(strict_types=1);

/**
 * v58 · OPS-02 Vícejazyčnost UI (čeština výchozí, angličtina, ukrajinština).
 *
 * PHP:  t('domena.klic', ['jmeno' => 'Eva'], $pocet) → surový text (do HTML VŽDY přes e()).
 *       Katalogy: lang/<jazyk>/<domena>.php → return ['klic' => 'Text {jmeno}' | ['one' => …, 'few' => …, 'many' => …, 'other' => …]];
 *       Klíč v katalogu je bez prefixu domény. Chybějící překlad → čeština → samotný klíč (v dev režimu error_log).
 *       Plurály: cs one (1) / few (2–4) / other; uk one / few / many; en one / other. Parametr {n} = počet.
 * JS:   edu_i18n_json(['lab', 'games']) vloží <script type="application/json" id="edu-i18n">; assets/i18n-v58.js
 *       nabídne window.EduI18n.t('lab.klic', {n: 3}, 'Český text').
 * Jazyk: POST akce edu_set_lang (CSRF, app/actions/i18n.php) → cookie edu_lang (jen kód jazyka, žádné osobní údaje).
 * Obsah výuky (lekce, úrovně labu, manuál, kvízové otázky) zůstává česky; výstup simulovaného Linuxu je anglicky jako ve skutečnosti.
 *
 * v59: cookie platí jen na žákovských vstupech (EDU_LOCALE_COOKIE_SCRIPTS) – jinde (teacher.php, exporty,
 * CLI) je vždy čeština bez ohledu na cookie. edu_active_locales() (env EDUCANET_UI_LOCALES, výchozí
 * "cs,en,uk") řídí, které jazyky jsou nabízené a přijímané; čeština je vždy aktivní. edu_clear_locale()
 * maže cookie při odhlášení žáka (sdílené počítače).
 */

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

const EDU_LOCALES = ['cs' => 'Čeština', 'en' => 'English', 'uk' => 'Українська'];
const EDU_LOCALE_COOKIE = 'edu_lang';

/**
 * v59 · Vstupní skripty žáka, kde cookie jazyka platí (dev bypass i produkce). Jinde (teacher.php,
 * exporty, CLI audity) zůstává vždy čeština, i kdyby si prohlížeč cookie edu_lang nesl z jiné karty
 * (sdílený počítač, učitel přihlášený vedle žáka).
 */
const EDU_LOCALE_COOKIE_SCRIPTS = ['index.php', 'progress.php', 'lab_v57_api.php', 'robots_v58_api.php', 'teamgames_v58_api.php', 'arena_v58_events_api.php'];

/** true, když aktuální vstupní skript smí číst cookie jazyka (viz EDU_LOCALE_COOKIE_SCRIPTS). */
function edu_locale_cookie_allowed_here(): bool
{
    return in_array(basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')), EDU_LOCALE_COOKIE_SCRIPTS, true);
}

/**
 * Jazyky nabízené k výběru a platné pro cookie – env EDUCANET_UI_LOCALES (výchozí "cs,en,uk"),
 * čárkou oddělený seznam kódů z EDU_LOCALES; čeština je vždy zahrnuta bez ohledu na hodnotu.
 * Umožňuje škole dočasně skrýt rozpracovaný jazyk (např. jen "cs,en" před revizí ukrajinštiny).
 */
function edu_active_locales(): array
{
    $raw = (string)(getenv('EDUCANET_UI_LOCALES') ?: 'cs,en,uk');
    $active = ['cs'];
    foreach (explode(',', $raw) as $code) {
        $code = trim($code);
        if ($code !== '' && isset(EDU_LOCALES[$code]) && !in_array($code, $active, true)) $active[] = $code;
    }
    return $active;
}

/** Aktivní jazyk: přepis pro testy → cookie (jen na žákovských vstupech, jen aktivní jazyk) → čeština. */
function edu_locale(): string
{
    $forced = $GLOBALS['edu_locale_override'] ?? null;
    if (is_string($forced) && isset(EDU_LOCALES[$forced])) return $forced;
    if (!edu_locale_cookie_allowed_here()) return 'cs';
    $cookie = (string)($_COOKIE[EDU_LOCALE_COOKIE] ?? '');
    return in_array($cookie, edu_active_locales(), true) ? $cookie : 'cs';
}

/** Tvar plurálu podle jazyka (CLDR, celá čísla). */
function edu_plural_form(string $locale, int $n): string
{
    $n = abs($n);
    if ($locale === 'cs') return $n === 1 ? 'one' : (($n >= 2 && $n <= 4) ? 'few' : 'other');
    if ($locale === 'uk') {
        $m10 = $n % 10;
        $m100 = $n % 100;
        if ($m10 === 1 && $m100 !== 11) return 'one';
        if ($m10 >= 2 && $m10 <= 4 && !($m100 >= 12 && $m100 <= 14)) return 'few';
        return 'many';
    }
    return $n === 1 ? 'one' : 'other';
}

/** Katalog domény pro jazyk (líně, jednou za request). */
function edu_i18n_catalog(string $locale, string $domain): array
{
    static $cache = [];
    $key = $locale . '/' . $domain;
    if (array_key_exists($key, $cache)) return $cache[$key];
    if (!isset(EDU_LOCALES[$locale]) || preg_match('/^[a-z0-9_]{1,32}$/', $domain) !== 1) return $cache[$key] = [];
    $file = __DIR__ . '/lang/' . $locale . '/' . $domain . '.php';
    $data = is_file($file) ? require $file : [];
    return $cache[$key] = is_array($data) ? $data : [];
}

/** Hodnota překladu (řetězec nebo pole plurálů) s návratem do češtiny; null = chybí úplně. */
function edu_i18n_raw(string $key): string|array|null
{
    $dot = strpos($key, '.');
    if ($dot === false || $dot === 0) return null;
    $domain = substr($key, 0, $dot);
    $sub = substr($key, $dot + 1);
    $locale = edu_locale();
    $value = edu_i18n_catalog($locale, $domain)[$sub] ?? null;
    if ($value === null && $locale !== 'cs') $value = edu_i18n_catalog('cs', $domain)[$sub] ?? null;
    return is_string($value) || is_array($value) ? $value : null;
}

/** Přeložený text; {param} se nahradí, {n} = $count. Výsledek je surový text – escapuj přes e(). */
function t(string $key, array $params = [], ?int $count = null): string
{
    $value = edu_i18n_raw($key);
    if ($value === null) {
        if (function_exists('educanet_dev_bypass_enabled') && educanet_dev_bypass_enabled()) error_log('EDUCANET i18n: chybí klíč ' . $key);
        return $key;
    }
    if (is_array($value)) {
        $form = edu_plural_form(edu_locale(), (int)($count ?? 0));
        $value = $value[$form] ?? $value['other'] ?? $value['many'] ?? (string)(reset($value) ?: '');
    }
    if ($count !== null && !array_key_exists('n', $params)) $params['n'] = $count;
    $replace = [];
    foreach ($params as $name => $param) $replace['{' . $name . '}'] = (string)$param;
    return strtr((string)$value, $replace);
}

/** JSON katalogů pro JS (klíče s prefixem domény) – vkládá se jako <script type="application/json" id="edu-i18n">. */
function edu_i18n_json(array $domains): string
{
    $messages = [];
    foreach ($domains as $domain) {
        $domain = (string)$domain;
        $base = edu_i18n_catalog('cs', $domain);
        $local = edu_locale() === 'cs' ? [] : edu_i18n_catalog(edu_locale(), $domain);
        foreach ($local + $base as $sub => $value) $messages[$domain . '.' . $sub] = $value;
    }
    $json = json_encode(['locale' => edu_locale(), 'messages' => $messages], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    return '<script type="application/json" id="edu-i18n">' . ($json === false ? '{}' : $json) . '</script>';
}

/** Bezpečná návratová adresa po přepnutí jazyka (jen relativní ?view=…, jinak domovská stránka). */
function edu_i18n_return_url(string $raw): string
{
    return preg_match('/^\?[a-zA-Z0-9_=&%.\-]{0,200}$/', $raw) === 1 ? $raw : '?view=dashboard';
}

/** Nastaví jazyk (volá POST akce edu_set_lang po ověření CSRF); jen jazyk aktivní podle EDUCANET_UI_LOCALES. */
function edu_set_locale(string $locale): bool
{
    if (!in_array($locale, edu_active_locales(), true)) return false;
    if (!headers_sent()) {
        // v59 (SEC59-15): session cookie – na sdíleném počítači jazyk nepřežije zavření prohlížeče (mateřština je citlivý údaj).
        setcookie(EDU_LOCALE_COOKIE, $locale, ['expires' => 0, 'path' => '/', 'secure' => educanet_is_https(), 'httponly' => true, 'samesite' => 'Lax']);
    }
    $_COOKIE[EDU_LOCALE_COOKIE] = $locale;
    return true;
}

/** Smaže cookie jazyka (volá se při odhlášení žáka – sdílené počítače mohou prozradit mateřský jazyk). */
function edu_clear_locale(): void
{
    if (!headers_sent()) {
        setcookie(EDU_LOCALE_COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => educanet_is_https(), 'httponly' => true, 'samesite' => 'Lax']);
    }
    unset($_COOKIE[EDU_LOCALE_COOKIE]);
}

/**
 * Přepínač jazyka (formulář POST s CSRF, bez autosubmitu – WCAG 3.2.2). $returnUrl = relativní adresa,
 * kam se vrátit. Popisek je vždy trojjazyčný „Jazyk · Language · Мова“ (žák ho musí přečíst, ať je
 * rozhraní v kterémkoli jazyce); nabízí jen jazyky z edu_active_locales(), uk je označena „(beta)“.
 */
function edu_lang_switcher_html(string $csrf, string $returnUrl = '?view=dashboard'): string
{
    $html = '<form class="edu-lang-switcher" method="post" action="index.php">'
        . '<input type="hidden" name="action" value="edu_set_lang">'
        . '<input type="hidden" name="csrf" value="' . e($csrf) . '">'
        . '<input type="hidden" name="return" value="' . e(edu_i18n_return_url($returnUrl)) . '">'
        . '<label class="edu-lang-switcher__label"><span class="edu-lang-label">'
        . '<span lang="cs">Jazyk</span> · <span lang="en">Language</span> · <span lang="uk">Мова</span>'
        . '</span> <select name="lang" class="edu-lang-switcher__select" data-edu-lang-select>';
    foreach (edu_active_locales() as $code) {
        $name = (string)(EDU_LOCALES[$code] ?? $code) . ($code === 'uk' ? ' (beta)' : '');
        $html .= '<option value="' . e($code) . '"' . ($code === edu_locale() ? ' selected' : '') . ' lang="' . e($code) . '">' . e($name) . '</option>';
    }
    return $html . '</select></label> <button type="submit" class="edu-lang-switcher__apply edu-lang-apply">' . e(t('core.lang.apply')) . '</button></form>';
}

/**
 * v63 · Segmentovaný přepínač jazyka pro přihlašovací stránku: stejný POST edu_set_lang s CSRF jako
 * edu_lang_switcher_html(), ale každý jazyk je samostatné tlačítko (kód + název), bez JavaScriptu.
 * Aktivní jazyk má aria-current="true". Skupina má trojjazyčný název (žák ho přečte v kterémkoli jazyce).
 */
function edu_lang_segmented_html(string $csrf, string $returnUrl = '?view=home'): string
{
    $html = '<form class="login63-lang" method="post" action="index.php" role="group" aria-label="Jazyk · Language · Мова">'
        . '<input type="hidden" name="action" value="edu_set_lang">'
        . '<input type="hidden" name="csrf" value="' . e($csrf) . '">'
        . '<input type="hidden" name="return" value="' . e(edu_i18n_return_url($returnUrl)) . '">';
    foreach (edu_active_locales() as $code) {
        $current = $code === edu_locale();
        $html .= '<button type="submit" name="lang" value="' . e($code) . '" lang="' . e($code) . '"'
            . ($current ? ' aria-current="true"' : '') . '>'
            . '<b aria-hidden="true">' . e(strtoupper($code)) . '</b><span>' . e((string)(EDU_LOCALES[$code] ?? $code)) . '</span></button>';
    }
    return $html . '</form>';
}
