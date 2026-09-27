<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 Převod UI – překlady ve stylu gettext (msgid = český text přímo ve zdroji).
 *
 * PHP:  tr('Uložit') · tr('Ahoj, {jmeno}!', ['jmeno' => $name]) · trn(['one' => '{n} bod', 'few' => '{n} body', 'other' => '{n} bodů'], $n)
 *       trm('Text') jen označí literál pro extrakci (vrací ho beze změny) – pro popisky v polích, které se vykreslí přes tr($label).
 *       Výsledek je surový text – do HTML VŽDY přes e().
 * Katalogy: lang/<en|uk>/ui/<domena>.php → return ['Český text' => 'Překlad', '{n} bodů' => ['one' => '{n} point', 'other' => '{n} points']];
 *       Plurálový msgid = tvar 'other' z českého pole. Čeština katalog nemá (zdroj = čeština).
 *       Chybí-li překlad → vrátí se čeština (nikdy surový klíč). Kontrola pokrytí: tools/v59_i18n_audit.php.
 * JS:   edu_tr_json(['js_lab']) vloží <script type="application/json" class="edu-tr-json" data-edu-tr="js_lab">
 *       (jedna doména = jeden blok, max. 1× za request; pro češtinu nic); EduI18n.tr('Český text', {n: 3}).
 * Obsah výuky zůstává česky: kontejner obsahu dostane edu_content_lang_attr() (lang="cs" při en/uk, WCAG 3.1.2);
 * edu_content_note_html() vykreslí vedle něj krátkou poznámku (jen při en/uk).
 * Datum/čas/čísla bez ext-intl: edu_date(), edu_month(), edu_weekday(), edu_number().
 * Učitelský cockpit je vždy česky (žákovské vstupní skripty jsou vyjmenované v EDU_LOCALE_COOKIE_SCRIPTS
 * v i18n_v58.php; jinde platí edu_locale_override, jinak čeština).
 *
 * Audit (jen v paměti, CLI): $GLOBALS['edu_tr_test_catalogs']['en'|'uk'] = [msgid => překlad, …] přepíše
 * skutečné katalogy pro tr()/trn(); $GLOBALS['edu_tr_collect'] = true sbírá chybějící msgid (bez CS
 * fallbacku úspěšně přeložené) do $GLOBALS['edu_tr_misses'][] – nikdy se nic z toho neloguje.
 */

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Adresář katalogů msgid pro jazyk. */
function edu_tr_dir(string $locale): string
{
    return __DIR__ . '/lang/' . $locale . '/ui';
}

/** Sloučený katalog msgid všech domén jazyka (líně, jednou za request; opcache drží pole v paměti). */
function edu_tr_catalog(string $locale): array
{
    $override = $GLOBALS['edu_tr_test_catalogs'][$locale] ?? null;
    if (is_array($override)) return $override;
    static $cache = [];
    if (array_key_exists($locale, $cache)) return $cache[$locale];
    if ($locale === 'cs' || !isset(EDU_LOCALES[$locale])) return $cache[$locale] = [];
    $merged = [];
    $files = glob(edu_tr_dir($locale) . '/*.php') ?: [];
    sort($files);
    foreach ($files as $file) {
        if (preg_match('/^[a-z0-9_]{1,40}\.php$/', basename($file)) !== 1) continue;
        $data = require $file;
        if (is_array($data)) $merged += $data;
    }
    return $cache[$locale] = $merged;
}

/** Katalog jedné domény (pro JS a audit). */
function edu_tr_domain(string $locale, string $domain): array
{
    if ($locale === 'cs' || !isset(EDU_LOCALES[$locale]) || preg_match('/^[a-z0-9_]{1,40}$/', $domain) !== 1) return [];
    $file = edu_tr_dir($locale) . '/' . $domain . '.php';
    $data = is_file($file) ? require $file : [];
    return is_array($data) ? $data : [];
}

/** Dosazení {param}. */
function edu_tr_format(string $text, array $params): string
{
    if ($params === []) return $text;
    $replace = [];
    foreach ($params as $name => $value) $replace['{' . $name . '}'] = (string)$value;
    return strtr($text, $replace);
}

/** Zaznamená chybějící/prázdný překlad pro audit (jen v paměti, jen když je sběr zapnutý; nikdy loguje). */
function edu_tr_record_miss(string $msgid): void
{
    if (($GLOBALS['edu_tr_collect'] ?? false) === true) {
        $GLOBALS['edu_tr_misses'][] = $msgid;
    }
}

/** Přeložený text (msgid = český text). */
function tr(string $cs, array $params = []): string
{
    $locale = edu_locale();
    if ($locale !== 'cs') {
        $hit = edu_tr_catalog($locale)[$cs] ?? null;
        if (is_string($hit) && $hit !== '') return edu_tr_format($hit, $params);
        // Katalog má pro msgid plurálové tvary (stejný msgid jinde volá trn()) – tvar podle {n}.
        if (is_array($hit) && isset($params['n']) && is_numeric($params['n'])) {
            $value = $hit[edu_plural_form($locale, (int)$params['n'])] ?? $hit['other'] ?? $hit['many'] ?? null;
            if (is_string($value) && $value !== '') return edu_tr_format($value, $params);
        }
        edu_tr_record_miss($cs);
    }
    return edu_tr_format($cs, $params);
}

/** Plurál: $csForms = ['one' => …, 'few' => …, 'other' => …] (česky); {n} = $n. */
function trn(array $csForms, int $n, array $params = []): string
{
    if (!array_key_exists('n', $params)) $params['n'] = $n;
    $msgid = (string)($csForms['other'] ?? end($csForms) ?: '');
    $locale = edu_locale();
    if ($locale !== 'cs') {
        $hit = edu_tr_catalog($locale)[$msgid] ?? null;
        if (is_array($hit)) {
            $form = edu_plural_form($locale, $n);
            $value = $hit[$form] ?? $hit['other'] ?? $hit['many'] ?? null;
            if (is_string($value) && $value !== '') return edu_tr_format($value, $params);
        } elseif (is_string($hit) && $hit !== '') {
            return edu_tr_format($hit, $params);
        }
        edu_tr_record_miss($msgid);
    }
    $form = edu_plural_form('cs', $n);
    return edu_tr_format((string)($csForms[$form] ?? $msgid), $params);
}

/**
 * Věta s vloženým HTML: text se přeloží a escapuje, {param} se nahradí HOTOVÝM bezpečným HTML.
 * tr_html('Vyhrál tým {tym}!', ['tym' => '<strong>' . e($team) . '</strong>']) – hodnoty MUSÍ být už escapované (e()).
 * Vrací HTML (neobalovat znovu e()).
 */
function tr_html(string $cs, array $safeHtml = []): string
{
    // Stejné escapování jako e() z bootstrap.php, ale bez závislosti na něm (knihovny labu/arény běží i bez bootstrapu).
    $html = htmlspecialchars(tr($cs), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if ($safeHtml === []) return $html;
    $replace = [];
    foreach ($safeHtml as $name => $value) $replace['{' . $name . '}'] = (string)$value;
    return strtr($html, $replace);
}

/** Označí literál pro extrakci (popisky v polích); vrací ho beze změny – vykreslí se přes tr($label). */
function trm(string $cs): string
{
    return $cs;
}

/**
 * Český výukový obsah (název lekce, předmětu, akce kalendáře…) vložený do přeloženého UI: při en/uk
 * <span lang="cs">…</span> (WCAG 3.1.2), v češtině jen escapovaný text – výstup stejný jako e(). Vrací HTML.
 */
function edu_cs(string $text): string
{
    $html = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return edu_locale() === 'cs' ? $html : '<span lang="cs">' . $html . '</span>';
}

/** Atribut jazyka pro kontejner výukového obsahu, který zůstává česky (WCAG 3.1.2). */
function edu_content_lang_attr(): string
{
    return edu_locale() === 'cs' ? '' : ' lang="cs"';
}

/** Poznámka „Učební obsah je zatím jen česky.“ vedle obsahového kontejneru; prázdný řetězec v češtině. */
function edu_content_note_html(): string
{
    if (edu_locale() === 'cs') return '';
    return '<p class="edu-content-note" role="note">' . e(t('core.lang.content_note')) . '</p>';
}

/** Název měsíce v 2. pádě (cs: „26. září“); en/uk vrací plný název měsíce (uk se ve formátech dat nepoužívá). */
function edu_month(int $month, ?string $locale = null): string
{
    $locale = $locale ?? edu_locale();
    $index = max(1, min(12, $month)) - 1;
    $names = match ($locale) {
        'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'uk' => ['січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'],
        default => ['ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince'],
    };
    return $names[$index] ?? '';
}

/** Krátký název měsíce pro en-GB („Sep“); pro ostatní jazyky číslo měsíce (numerický formát data). */
function edu_month_short(int $month, ?string $locale = null): string
{
    $locale = $locale ?? edu_locale();
    if ($locale !== 'en') return (string)max(1, min(12, $month));
    $index = max(1, min(12, $month)) - 1;
    return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][$index] ?? '';
}

/** Název dne v týdnu podle jazyka (0 = neděle, jako date('w')). */
/**
 * Krátký název dne (ISO 1 = pondělí … 7 = neděle) z vlastní tabulky, ne z katalogu – zkratky jako „Po“ nebo „Ne“
 * kolidují s jinými msgid („po“ = after, „ne“ = no).
 */
function edu_weekday_short(int $isoDay, ?string $locale = null): string
{
    static $days = [
        'cs' => ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'],
        'en' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'uk' => ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'],
    ];
    $list = $days[$locale ?? edu_locale()] ?? $days['cs'];
    return $list[max(1, min(7, $isoDay)) - 1];
}

function edu_weekday(int $ts, ?string $locale = null): string
{
    $locale = $locale ?? edu_locale();
    $names = match ($locale) {
        'en' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        'uk' => ['неділя', 'понеділок', 'вівторок', 'середа', 'четвер', "п'ятниця", 'субота'],
        default => ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
    };
    return $names[(int)date('w', $ts)] ?? '';
}

/** Desetinné číslo bez ext-intl: cs/uk desetinná čárka, en tečka; bez oddělovače tisíců. */
function edu_number(float $n, int $decimals = 0): string
{
    $sep = edu_locale() === 'en' ? '.' : ',';
    return number_format($n, $decimals, $sep, '');
}

/**
 * Datum/čas bez ext-intl: $style je date (výchozí) | date_short | datetime | time | weekday | day_month.
 * cs „26. 9. 2026“, en-GB „26 Sep 2026“, uk „26.09.2026“; čas vždy 24hodinový „H:i“.
 */
function edu_date(int $ts, string $style = 'date'): string
{
    $locale = edu_locale();
    $day = (int)date('j', $ts);
    $month = (int)date('n', $ts);
    $year = (int)date('Y', $ts);
    $time = date('H:i', $ts);
    $dayMonth = match ($locale) {
        'en' => $day . ' ' . edu_month_short($month, 'en'),
        'uk' => sprintf('%02d.%02d', $day, $month),
        default => $day . '. ' . $month . '.',
    };
    $full = $locale === 'uk' ? ($dayMonth . '.' . $year) : ($dayMonth . ' ' . $year);
    return match ($style) {
        'date_short' => $dayMonth,
        'time' => $time,
        'datetime' => $full . ' ' . $time,
        'weekday' => edu_weekday($ts, $locale),
        'day_month' => $locale === 'uk' ? $dayMonth : ($day . '. ' . edu_month($month, $locale)),
        default => $full,
    };
}

/** Kód jazyka pro <html lang>. */
function edu_html_lang(): string
{
    return edu_locale();
}

/**
 * Vloží katalog(y) msgid pro JS jako `<script type="application/json" class="edu-tr-json" data-edu-tr="doména">`
 * – jedna doména nejvýš jednou za request (další volání se stejnou doménou nic nepřidá), pro češtinu nic.
 * JS (assets/i18n-v58.js) líně slučuje všechny bloky `.edu-tr-json`; jazyk čte z `<html lang>`, ne odsud.
 */
function edu_tr_json(array $domains): string
{
    $locale = edu_locale();
    if ($locale === 'cs') return '';
    static $emitted = [];
    $html = '';
    foreach ($domains as $domain) {
        $domain = (string)$domain;
        if (isset($emitted[$domain])) continue;
        $emitted[$domain] = true;
        $messages = edu_tr_domain($locale, $domain);
        if ($messages === []) continue;
        $json = json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) continue;
        $html .= '<script type="application/json" class="edu-tr-json" data-edu-tr="' . e($domain) . '">' . $json . '</script>';
    }
    return $html;
}

/** JSON všech JS domén (lang/<jazyk>/ui/js_*.php) – vkládá _layout.php do <head> žákovských stránek. */
function edu_tr_json_js(): string
{
    $domains = [];
    $locale = edu_locale();
    if ($locale !== 'cs') {
        foreach (glob(edu_tr_dir($locale) . '/js_*.php') ?: [] as $file) $domains[] = basename($file, '.php');
        sort($domains);
    }
    return edu_tr_json($domains);
}
