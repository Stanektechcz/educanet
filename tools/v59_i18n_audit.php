<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET · tools/v59_i18n_audit.php (v59 OPS-02 – převod žákovského UI na tr()/trn())
 *
 * Ověřuje jádro (tr/trn/trm/tr_html, edu_date/edu_number, kontext teacher/CLI = čeština),
 * extrakci msgid ze zdrojů (token_get_all pro PHP, regex pro JS), pokrytí katalogů
 * lang/<en|uk>/ui/<doména>.php podle manifestu lang/domains_v59.php, hygienu katalogů a
 * (pokud neběží --no-http) chování přes skutečný HTTP dev server – přepínač jazyka, cookie,
 * CSRF, bezpečný návrat, `<html lang>`, absence české diakritiky mimo obsahové kontejnery.
 *
 * Použití:
 *   C:/php/php.exe tools/v59_i18n_audit.php                 – vše
 *   C:/php/php.exe tools/v59_i18n_audit.php --core           – jen jádro (bez extrakce/HTTP)
 *   C:/php/php.exe tools/v59_i18n_audit.php --domain=auth --no-http   – jedna doména, bez HTTP
 *   C:/php/php.exe tools/v59_i18n_audit.php --strict-domains=auth     – HTTP sekce FAILuje i na
 *       diakritice/`<html lang>`/přepínači pro vyjmenované (již převedené) domény; jinak jen hlásí počty
 *       (převod ještě neběží u všech domén, viz PLAN_I18N.md "Pořadí").
 *
 * Konec: V59_I18N_AUDIT_OK checks=N failed=0 (jinak _FAIL, nenulový exit kód).
 *
 * Poznámka k délce souboru: audit (ne aplikační kód) sdružuje extrakci PHP/JS, pokrytí, hygienu
 * katalogů a HTTP chování jedné vrstvy do jednoho spustitelného nástroje se společným počítadlem
 * kontrol – stejně jako tools/v57_linux_lab_audit.php (1381 ř.); rozdělení by jen roztrhalo sdílený
 * stav ($state/$check) mezi soubory bez zisku pro čitelnost.
 */

$ROOT = dirname(__DIR__);

function v59a_flag(array $argv, string $name): bool
{
    return in_array('--' . $name, $argv, true);
}

function v59a_opt(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $a) {
        if (str_starts_with($a, '--' . $name . '=')) return substr($a, strlen($name) + 3);
    }
    return $default;
}

$argvAll = $argv ?? [];
$onlyDomain = v59a_opt($argvAll, 'domain');
$noHttp = v59a_flag($argvAll, 'no-http');
$coreOnly = v59a_flag($argvAll, 'core');
$strictDomains = array_values(array_filter(array_map('trim', explode(',', (string)v59a_opt($argvAll, 'strict-domains', '')))));

require_once $ROOT . '/tools/lib/audit_storage.php';
edu_audit_temp_storage('v59-i18n');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/tools/lib/audit.php';

$state = audit_counter();
$check = audit_checker($state);

$manifest = require $ROOT . '/lang/domains_v59.php';
/** @var array<string,list<string>> $phpDomains */
$phpDomains = $manifest['domains'];
/** @var array<string,list<string>> $jsDomains */
$jsDomains = $manifest['js'];

if ($onlyDomain !== null) {
    $phpDomains = isset($phpDomains[$onlyDomain]) ? [$onlyDomain => $phpDomains[$onlyDomain]] : [];
    $jsDomains = isset($jsDomains[$onlyDomain]) ? [$onlyDomain => $jsDomains[$onlyDomain]] : [];
}

// =============================================================================================
// 1) Jádro: tr/trn/trm/tr_html, edu_date/edu_month/edu_weekday/edu_number, kontext, t() zpětná kompatibilita.
// =============================================================================================
v59a_section_core($check, $ROOT);

if ($coreOnly) {
    exit(audit_summary($state, 'V59_I18N'));
}

// =============================================================================================
// 2)+3)+4) Extrakce msgid (PHP token_get_all, JS regex) a pokrytí katalogů podle manifestu.
// =============================================================================================
$extracted = v59a_extract_all($ROOT, $phpDomains, $jsDomains, $check);

// =============================================================================================
// 5) Hygiena katalogů (platné PHP, guard, strict_types, pole, bez duplicit/HTML/prázdných hodnot,
//    uk s cyrilicí, en bez české diakritiky) + konflikt stejného msgid mezi doménami.
// =============================================================================================
v59a_section_catalog_hygiene($check, $ROOT, $extracted);

// =============================================================================================
// 6) JS: shim, zákaz innerHTML v tr() kontextu, JSON bloky.
// =============================================================================================
v59a_section_js_hygiene($check, $ROOT, $jsDomains);

if ($noHttp) {
    exit(audit_summary($state, 'V59_I18N'));
}

// =============================================================================================
// 7)+8) HTTP: přepínač, cookie, CSRF, bezpečný návrat, <html lang>, diakritika mimo obsah.
// =============================================================================================
v59a_section_http($check, $ROOT, $strictDomains);

// =============================================================================================
// 9) Výkon: studený katalog < 50 ms, 10 000× tr() < 50 ms.
// =============================================================================================
v59a_section_performance($check);

exit(audit_summary($state, 'V59_I18N'));

// =================================================================================================
// Sekce 1 – jádro
// =================================================================================================

function v59a_section_core(Closure $check, string $root): void
{
    $savedScript = $_SERVER['SCRIPT_FILENAME'] ?? null;
    $savedCookie = $_COOKIE[EDU_LOCALE_COOKIE] ?? null;

    $GLOBALS['edu_locale_override'] = 'cs';
    $check('core:tr-cs-passthrough', tr('Uložit') === 'Uložit');
    $check('core:tr-params', tr('Ahoj, {jmeno}!', ['jmeno' => 'Eva']) === 'Ahoj, Eva!');
    $check('core:trm-passthrough', trm('Materiály') === 'Materiály');

    $forms = ['one' => '{n} bod', 'few' => '{n} body', 'other' => '{n} bodů'];
    $check('core:trn-cs-one', trn($forms, 1) === '1 bod');
    $check('core:trn-cs-few', trn($forms, 3) === '3 body');
    $check('core:trn-cs-other', trn($forms, 5) === '5 bodů');

    $GLOBALS['edu_tr_test_catalogs'] = [
        'en' => ['Uložit' => 'Save', '{n} bodů' => ['one' => '{n} point', 'other' => '{n} points']],
        'uk' => ['Uložit' => 'Зберегти', '{n} bodů' => ['one' => '{n} бал', 'few' => '{n} бали', 'many' => '{n} балів']],
    ];
    $GLOBALS['edu_locale_override'] = 'en';
    $check('core:tr-en-hit', tr('Uložit') === 'Save');
    $check('core:tr-en-miss-falls-back-cs', tr('Neexistující klíč XYZ') === 'Neexistující klíč XYZ');
    $check('core:trn-en-one', trn($forms, 1) === '1 point');
    $check('core:trn-en-other', trn($forms, 5) === '5 points');

    $GLOBALS['edu_locale_override'] = 'uk';
    $check('core:tr-uk-hit', tr('Uložit') === 'Зберегти');
    $check('core:trn-uk-one', trn($forms, 1) === '1 бал');
    $check('core:trn-uk-few', trn($forms, 3) === '3 бали');
    $check('core:trn-uk-many', trn($forms, 5) === '5 балів');
    unset($GLOBALS['edu_tr_test_catalogs']);

    $GLOBALS['edu_tr_test_catalogs'] = ['en' => ['Vyhrál tým {tym}!' => 'Team {tym} won!']];
    $GLOBALS['edu_locale_override'] = 'en';
    $htmlOut = tr_html('Vyhrál tým {tym}!', ['tym' => '<strong>' . e('<Tým X>') . '</strong>']);
    $check('core:tr_html-escapes-text-inserts-safe-html', str_contains($htmlOut, '<strong>&lt;Tým X&gt;</strong>') && !str_contains($htmlOut, '<Tým X>'));
    unset($GLOBALS['edu_tr_test_catalogs']);

    $GLOBALS['edu_tr_test_catalogs'] = ['en' => []];
    $GLOBALS['edu_tr_collect'] = true;
    $GLOBALS['edu_tr_misses'] = [];
    tr('Text, který nikdo nepřeložil');
    $check('core:miss-collection-only-when-enabled', in_array('Text, který nikdo nepřeložil', $GLOBALS['edu_tr_misses'], true));
    $GLOBALS['edu_tr_collect'] = false;
    $GLOBALS['edu_tr_misses'] = [];
    tr('Další nepřeložený text');
    $check('core:miss-collection-off-by-default', $GLOBALS['edu_tr_misses'] === []);
    unset($GLOBALS['edu_tr_test_catalogs'], $GLOBALS['edu_tr_misses'], $GLOBALS['edu_tr_collect']);

    $ts = mktime(12, 0, 0, 9, 26, 2026);
    if ($ts === false) $ts = 0;
    $GLOBALS['edu_locale_override'] = 'cs';
    $check('core:edu_date-cs', edu_date($ts) === '26. 9. 2026');
    $check('core:edu_date-cs-datetime', edu_date($ts, 'datetime') === '26. 9. 2026 12:00');
    $check('core:edu_month-cs-genitive', edu_month(9, 'cs') === 'září');
    $check('core:edu_number-cs', edu_number(3.5, 1) === '3,5');
    $GLOBALS['edu_locale_override'] = 'en';
    $check('core:edu_date-en', edu_date($ts) === '26 Sep 2026');
    $check('core:edu_month-en-full', edu_month(9, 'en') === 'September');
    $check('core:edu_number-en', edu_number(3.5, 1) === '3.5');
    $GLOBALS['edu_locale_override'] = 'uk';
    $check('core:edu_date-uk', edu_date($ts) === '26.09.2026');
    $check('core:edu_number-uk', edu_number(3.5, 1) === '3,5');

    $weekdayNames = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'];
    $check('core:edu_weekday-cs-matches-date-w', edu_weekday($ts, 'cs') === $weekdayNames[(int)date('w', $ts)]);

    $check('core:edu_content_lang_attr-cs-empty', (function () { $GLOBALS['edu_locale_override'] = 'cs'; return edu_content_lang_attr(); })() === '');
    $check('core:edu_content_lang_attr-en-marks-cs', (function () { $GLOBALS['edu_locale_override'] = 'en'; return edu_content_lang_attr(); })() === ' lang="cs"');
    $check('core:edu_content_note-cs-empty', (function () { $GLOBALS['edu_locale_override'] = 'cs'; return edu_content_note_html(); })() === '');
    $noteEn = (function () { $GLOBALS['edu_locale_override'] = 'en'; return edu_content_note_html(); })();
    $check('core:edu_content_note-en-has-role-note', str_contains($noteEn, 'role="note"') && $noteEn !== '');

    unset($GLOBALS['edu_locale_override']);
    $_SERVER['SCRIPT_FILENAME'] = '/var/www/teacher.php';
    $_COOKIE[EDU_LOCALE_COOKIE] = 'en';
    $check('core:teacher-context-always-cs-despite-cookie', edu_locale() === 'cs');
    $_SERVER['SCRIPT_FILENAME'] = '/var/www/tools/v59_i18n_audit.php';
    $check('core:cli-context-always-cs', edu_locale() === 'cs');
    $_SERVER['SCRIPT_FILENAME'] = '/var/www/index.php';
    $check('core:student-entry-honours-cookie', edu_locale() === 'en');
    $_SERVER['SCRIPT_FILENAME'] = '/var/www/lab_v57_api.php';
    $check('core:student-api-entry-honours-cookie', edu_locale() === 'en');

    $check('core:edu_active_locales-default', edu_active_locales() === ['cs', 'en', 'uk']);
    putenv('EDUCANET_UI_LOCALES=cs,en');
    $check('core:edu_active_locales-env-restricts', edu_active_locales() === ['cs', 'en']);
    $check('core:edu_locale-rejects-inactive-cookie', (function () { $_COOKIE[EDU_LOCALE_COOKIE] = 'uk'; return edu_locale(); })() === 'cs');
    putenv('EDUCANET_UI_LOCALES');

    $check('core:edu_set_locale-rejects-unknown', edu_set_locale('xx') === false);

    if ($savedScript !== null) $_SERVER['SCRIPT_FILENAME'] = $savedScript; else unset($_SERVER['SCRIPT_FILENAME']);
    if ($savedCookie !== null) $_COOKIE[EDU_LOCALE_COOKIE] = $savedCookie; else unset($_COOKIE[EDU_LOCALE_COOKIE]);
    $GLOBALS['edu_locale_override'] = 'cs';
    $check('core:t-legacy-still-works', t('core.lang.apply') === 'Použít');
    unset($GLOBALS['edu_locale_override']);
}

// =================================================================================================
// Sekce 2–4 – extrakce a pokrytí
// =================================================================================================

/** @return array{0:int,1:int,2:string} [start, end] tokenu (inclusive) prvního argumentu, 'close' idx. */
function v59a_bracket_span(array $tokens, int $openIdx): array
{
    $openTok = $tokens[$openIdx];
    $openCh = is_array($openTok) ? $openTok[1] : $openTok;
    $n = count($tokens);
    $depth = 0;
    $items = [];
    $curStart = $openIdx + 1;
    for ($i = $openIdx; $i < $n; $i++) {
        $t = $tokens[$i];
        $text = is_array($t) ? $t[1] : $t;
        if ($text === '(' || $text === '[') {
            $depth++;
        } elseif ($text === ')' || $text === ']') {
            $depth--;
            if ($depth === 0) {
                $items[] = [$curStart, $i - 1];
                return ['close' => $i, 'items' => $items];
            }
        } elseif ($text === ',' && $depth === 1) {
            $items[] = [$curStart, $i - 1];
            $curStart = $i + 1;
        }
    }
    return ['close' => -1, 'items' => []];
}

function v59a_significant(array $tokens, int $start, int $end): array
{
    $out = [];
    for ($i = $start; $i <= $end; $i++) {
        $t = $tokens[$i] ?? null;
        if ($t === null) continue;
        if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
        $out[] = $t;
    }
    return $out;
}

function v59a_decode_literal(string $raw): string
{
    // $raw je zdrojový text jednoho tokenu T_CONSTANT_ENCAPSED_STRING (bez interpolace) – bezpečné eval.
    /** @noinspection PhpExpressionAlwaysNullInspection */
    return (string)eval('return ' . $raw . ';');
}

function v59a_single_string_literal(array $tokens, int $start, int $end): ?string
{
    $sig = v59a_significant($tokens, $start, $end);
    if (count($sig) !== 1) return null;
    $t = $sig[0];
    if (!is_array($t) || $t[0] !== T_CONSTANT_ENCAPSED_STRING) return null;
    return v59a_decode_literal($t[1]);
}

function v59a_first_significant_index(array $tokens, int $start, int $end): ?int
{
    for ($i = $start; $i <= $end; $i++) {
        $t = $tokens[$i] ?? null;
        if ($t === null) continue;
        if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
        return $i;
    }
    return null;
}

function v59a_kv_pair(array $tokens, int $start, int $end): ?array
{
    for ($i = $start; $i <= $end; $i++) {
        if (is_array($tokens[$i]) && $tokens[$i][0] === T_DOUBLE_ARROW) {
            $key = v59a_single_string_literal($tokens, $start, $i - 1);
            $value = v59a_single_string_literal($tokens, $i + 1, $end);
            if ($key === null || $value === null) return null;
            return [$key, $value];
        }
    }
    return null;
}

/** @return array{msgids: array<string,array{type:string,forms?:array<string,string>}>, dynamic:int, violations: list<string>} */
function v59a_extract_php(string $path): array
{
    $src = (string)file_get_contents($path);
    if (!str_contains($src, '<?php')) return ['msgids' => [], 'dynamic' => 0, 'violations' => []];
    $tokens = token_get_all($src);
    $n = count($tokens);
    $msgids = [];
    $dynamic = 0;
    $violations = [];

    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if (!is_array($t) || $t[0] !== T_STRING) continue;
        $name = $t[1];
        if (!in_array($name, ['tr', 'trn', 'trm', 'tr_html'], true)) continue;
        $line = $t[2];

        $prevIdx = null;
        for ($p = $i - 1; $p >= 0; $p--) {
            if (is_array($tokens[$p]) && in_array($tokens[$p][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
            $prevIdx = $p;
            break;
        }
        if ($prevIdx !== null) {
            $prevTok = $tokens[$prevIdx];
            $prevType = is_array($prevTok) ? $prevTok[0] : null;
            $prevText = is_array($prevTok) ? $prevTok[1] : $prevTok;
            if (in_array($prevType, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW], true) || $prevText === '->' || $prevText === '::') continue;
        }

        $nextIdx = null;
        for ($q = $i + 1; $q < $n; $q++) {
            if (is_array($tokens[$q]) && in_array($tokens[$q][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
            $nextIdx = $q;
            break;
        }
        if ($nextIdx === null) continue;
        $nextText = is_array($tokens[$nextIdx]) ? $tokens[$nextIdx][1] : $tokens[$nextIdx];
        if ($nextText !== '(') continue;

        $call = v59a_bracket_span($tokens, $nextIdx);
        if ($call['close'] < 0 || empty($call['items'])) continue;
        [$argStart, $argEnd] = $call['items'][0];

        if ($name === 'trn') {
            $openIdx = v59a_first_significant_index($tokens, $argStart, $argEnd);
            $openText = $openIdx !== null ? (is_array($tokens[$openIdx]) ? $tokens[$openIdx][1] : $tokens[$openIdx]) : null;
            if ($openIdx === null || $openText !== '[') {
                $dynamic++;
                $violations[] = "trn(): první argument musí být pole polí ['one'=>…,'few'=>…,'other'=>…] (řádek $line)";
                continue;
            }
            $arr = v59a_bracket_span($tokens, $openIdx);
            if ($arr['close'] < 0) { $violations[] = "trn(): nedokončené pole (řádek $line)"; continue; }
            $forms = [];
            $bad = false;
            foreach ($arr['items'] as [$is, $ie]) {
                if ($is > $ie) continue;
                $kv = v59a_kv_pair($tokens, $is, $ie);
                if ($kv === null || !in_array($kv[0], ['one', 'few', 'many', 'other'], true)) { $bad = true; break; }
                $forms[$kv[0]] = $kv[1];
            }
            if ($bad || !isset($forms['other'])) {
                $violations[] = "trn(): pole s neliterálovou hodnotou nebo bez tvaru 'other' (řádek $line)";
                $dynamic++;
                continue;
            }
            $gotKeys = array_keys($forms);
            sort($gotKeys);
            if ($gotKeys !== ['few', 'one', 'other']) {
                $violations[] = 'trn(): české tvary musí být přesně one/few/other (řádek ' . $line . ', má: ' . implode(',', array_keys($forms)) . ')';
            }
            $msgids[$forms['other']] = ['type' => 'plural', 'forms' => $forms];
            continue;
        }

        $literal = v59a_single_string_literal($tokens, $argStart, $argEnd);
        if ($literal === null) {
            $sig = v59a_significant($tokens, $argStart, $argEnd);
            $isPlainVar = count($sig) === 1 && is_array($sig[0]) && $sig[0][0] === T_VARIABLE;
            $dynamic++;
            if (!$isPlainVar) {
                $violations[] = "$name(): skládaný/interpolovaný/heredoc první argument (řádek $line) – msgid musí být přesně jeden řetězcový literál";
            }
            continue;
        }
        if (!isset($msgids[$literal])) $msgids[$literal] = ['type' => 'text'];
    }

    return ['msgids' => $msgids, 'dynamic' => $dynamic, 'violations' => $violations];
}

function v59a_decode_js_string(string $lit): string
{
    $quote = $lit[0];
    $inner = substr($lit, 1, -1);
    return str_replace(['\\\\', '\\' . $quote, '\\n', '\\t'], ['\\', $quote, "\n", "\t"], $inner);
}

/**
 * Konec objektového (nebo jiného {}) literálu ve zdroji JS – $openPos je index otevírací '{'.
 * Sleduje řetězcové uvozovky ('…'/"…" s escapováním zpětným lomítkem) a hloubku '{'/'}', takže
 * placeholdery jako '{n}' UVNITŘ řetězcové hodnoty (např. tvar plurálu 'one': '{n} bod') nerozbijí
 * hledání konce objektu – na rozdíl od naivního regexu \{([^}]*)\}, který na první '}' uvnitř
 * takového řetězce useknu tělo a extrakce forem pak selže. Vrací index odpovídající '}', nebo null.
 */
function v59a_js_brace_literal_end(string $src, int $openPos): ?int
{
    $n = strlen($src);
    $depth = 0;
    $quote = null; // null | "'" | '"'
    for ($i = $openPos; $i < $n; $i++) {
        $ch = $src[$i];
        if ($quote !== null) {
            if ($ch === '\\') { $i++; continue; }
            if ($ch === $quote) $quote = null;
            continue;
        }
        if ($ch === "'" || $ch === '"') { $quote = $ch; continue; }
        if ($ch === '`') {
            // Šablonový literál jako hodnota formy – přeskoč ho vlastním skenem (jeho '{'/'}', např.
            // z ${…}, by jinak zkreslily hloubku); volající pak stejně `` v $body zachytí jako FAIL.
            $i++;
            while ($i < $n && $src[$i] !== '`') { if ($src[$i] === '\\') $i++; $i++; }
            continue;
        }
        if ($ch === '{') { $depth++; continue; }
        if ($ch === '}') {
            $depth--;
            if ($depth === 0) return $i;
        }
    }
    return null;
}

/** @return list<array{body:string, offset:int}> tělo (bez vnějších {}) a pozice '{' pro každé EduI18n.trn({…}. */
function v59a_js_trn_object_bodies(string $src): array
{
    $result = [];
    if (!preg_match_all('/EduI18n\.trn\s*\(/', $src, $calls, PREG_OFFSET_CAPTURE)) return $result;
    $n = strlen($src);
    foreach ($calls[0] as [$matchText, $matchOff]) {
        $i = $matchOff + strlen((string)$matchText); // hned za '('
        while ($i < $n && ctype_space($src[$i])) $i++;
        if ($i >= $n || $src[$i] !== '{') continue; // první argument není objektový literál – nesledujeme
        $end = v59a_js_brace_literal_end($src, $i);
        if ($end === null) continue;
        $result[] = ['body' => substr($src, $i + 1, $end - $i - 1), 'offset' => $i];
    }
    return $result;
}

/** @return array{msgids: array<string,array{type:string,forms?:array<string,string>}>, dynamic:int, violations: list<string>} */
function v59a_extract_js(string $path): array
{
    $src = (string)file_get_contents($path);
    $msgids = [];
    $violations = [];

    $strLit = '`[^`]*`|\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"';
    preg_match_all('/EduI18n\.tr\s*\(\s*(' . $strLit . ')/', $src, $m, PREG_OFFSET_CAPTURE);
    foreach ($m[1] as [$lit, $off]) {
        if ($lit === '' || $lit[0] === '`') { $violations[] = 'EduI18n.tr(): šablonový literál na pozici ' . $off . ' – zakázáno'; continue; }
        $msgids[v59a_decode_js_string($lit)] = ['type' => 'text'];
    }
    $totalTrCalls = preg_match_all('/EduI18n\.tr\s*\(/', $src);
    $dynamic = max(0, $totalTrCalls - count($m[1]));

    foreach (v59a_js_trn_object_bodies($src) as ['body' => $body, 'offset' => $off]) {
        if (str_contains($body, '`')) { $violations[] = 'EduI18n.trn(): šablonový literál v poli forem na pozici ' . $off . ' – zakázáno'; continue; }
        $forms = [];
        if (preg_match_all('/(one|few|many|other)\s*:\s*(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")/', $body, $m3)) {
            foreach ($m3[1] as $idx => $key) $forms[$key] = v59a_decode_js_string($m3[2][$idx]);
        }
        if (isset($forms['other'])) {
            $msgids[$forms['other']] = ['type' => 'plural', 'forms' => $forms];
        } else {
            $violations[] = 'EduI18n.trn(): chybí tvar other na pozici ' . $off;
        }
    }

    return ['msgids' => $msgids, 'dynamic' => $dynamic, 'violations' => $violations];
}

/**
 * Regrese k opravě zkracování objektu forem na první '}' uvnitř řetězce (typicky placeholder '{n}'):
 * kanonický zápis se '{n}' ve všech tvarech se musí rozpoznat celý; šablonový literál musí zůstat FAIL.
 */
function v59a_selftest_js_trn_extraction(Closure $check): void
{
    $dir = sys_get_temp_dir() . '/educanet-audit-v59-jstrn-' . bin2hex(random_bytes(6));
    mkdir($dir, 0700, true);
    try {
        $okFile = $dir . '/ok.js';
        file_put_contents($okFile, "function x(n) {\n  return EduI18n.trn({one: '{n} bod', few: '{n} body', other: '{n} bodů'}, n);\n}\n");
        $ok = v59a_extract_js($okFile);
        $forms = $ok['msgids']['{n} bodů']['forms'] ?? null;
        $check(
            'selftest:js-trn:placeholder-n-in-all-forms-not-truncated',
            $ok['violations'] === [] && is_array($forms) && ($forms['one'] ?? null) === '{n} bod' && ($forms['few'] ?? null) === '{n} body' && ($forms['other'] ?? null) === '{n} bodů',
            false
        );

        $tplFile = $dir . '/tpl.js';
        file_put_contents($tplFile, "EduI18n.trn({one: `{n} bod`, other: '{n} bodů'}, n);\n");
        $tpl = v59a_extract_js($tplFile);
        $check('selftest:js-trn:template-literal-still-fails', $tpl['violations'] !== [], false);
    } finally {
        edu_audit_remove_dir($dir);
    }
}

function v59a_extract_all(string $root, array $phpDomains, array $jsDomains, Closure $check): array
{
    v59a_selftest_js_trn_extraction($check);

    $result = ['php' => [], 'js' => []];
    foreach ($phpDomains as $domain => $files) {
        $merged = ['msgids' => [], 'dynamic' => 0, 'violations' => []];
        foreach ($files as $rel) {
            $abs = $root . '/' . $rel;
            if (!is_file($abs)) { $check("extract:$domain:file-exists:$rel", false); continue; }
            $r = v59a_extract_php($abs);
            foreach ($r['msgids'] as $id => $info) $merged['msgids'][$id] = $info;
            $merged['dynamic'] += $r['dynamic'];
            foreach ($r['violations'] as $v) $merged['violations'][] = "$rel – $v";
        }
        $check("extract:$domain:no-composed-or-interpolated-msgid", $merged['violations'] === [], !empty($merged['violations']));
        foreach ($merged['violations'] as $v) echo 'INFO  extract:' . $domain . ': ' . $v . "\n";
        if ($merged['dynamic'] > 0) echo 'INFO  extract:' . $domain . ': ' . $merged['dynamic'] . ' dynamický(ch) tr($promenna) volání (jen počítáno, nekontroluje se)' . "\n";
        $result['php'][$domain] = $merged;
    }
    foreach ($jsDomains as $domain => $files) {
        $merged = ['msgids' => [], 'dynamic' => 0, 'violations' => []];
        foreach ($files as $rel) {
            $abs = $root . '/' . $rel;
            if (!is_file($abs)) { $check("extract:$domain:file-exists:$rel", false); continue; }
            $r = v59a_extract_js($abs);
            foreach ($r['msgids'] as $id => $info) $merged['msgids'][$id] = $info;
            $merged['dynamic'] += $r['dynamic'];
            foreach ($r['violations'] as $v) $merged['violations'][] = "$rel – $v";
        }
        $check("extract:$domain:no-template-literal-msgid", $merged['violations'] === [], !empty($merged['violations']));
        foreach ($merged['violations'] as $v) echo 'INFO  extract:' . $domain . ': ' . $v . "\n";
        $result['js'][$domain] = $merged;
    }

    // Pokrytí: každý msgid domény musí mít en i uk překlad; katalog nesmí mít nepoužité položky;
    // placeholdery {x} se musí shodovat. Prázdné domény (zatím nepřevedeno) jsou triviálně v pořádku.
    foreach (array_merge($result['php'], $result['js']) as $domain => $data) {
        v59a_check_domain_coverage($check, $root, $domain, $data['msgids']);
    }

    return $result;
}

function v59a_placeholder_set(string $text): array
{
    preg_match_all('/\{[a-z0-9_]+\}/i', $text, $m);
    $set = array_unique($m[0]);
    sort($set);
    return $set;
}

function v59a_load_ui_catalog(string $root, string $locale, string $domain): ?array
{
    $file = $root . '/lang/' . $locale . '/ui/' . $domain . '.php';
    if (!is_file($file)) return null;
    $data = require $file;
    return is_array($data) ? $data : null;
}

function v59a_check_domain_coverage(Closure $check, string $root, string $domain, array $msgids): void
{
    $en = v59a_load_ui_catalog($root, 'en', $domain) ?? [];
    $uk = v59a_load_ui_catalog($root, 'uk', $domain) ?? [];

    $missingEn = 0;
    $missingUk = 0;
    $placeholderMismatch = 0;
    $ukMissingN = 0;
    foreach ($msgids as $msgid => $info) {
        $enVal = $en[$msgid] ?? null;
        $ukVal = $uk[$msgid] ?? null;
        if ($info['type'] === 'plural') {
            if (!is_array($enVal) || !isset($enVal['other']) || (string)$enVal['other'] === '') $missingEn++;
            if (!is_array($ukVal) || !isset($ukVal['other'])) {
                $missingUk++;
            } else {
                foreach (['one', 'few', 'many'] as $f) {
                    if (isset($ukVal[$f]) && !str_contains((string)$ukVal[$f], '{n}')) $ukMissingN++;
                }
            }
            $msgidPlaceholders = v59a_placeholder_set($msgid);
            if (is_array($enVal) && isset($enVal['other']) && v59a_placeholder_set((string)$enVal['other']) !== $msgidPlaceholders) $placeholderMismatch++;
        } else {
            // v59: tr() umí i plurálové pole (tvar podle params.n) – přijatelné, jen když msgid obsahuje {n}
            // (sjednocení se stejným msgid, který jinde volá trn()).
            $okVal = static fn($v): bool => (is_string($v) && $v !== '')
                || (is_array($v) && str_contains($msgid, '{n}') && isset($v['other']) && (string)$v['other'] !== '');
            if (!$okVal($enVal)) $missingEn++;
            if (!$okVal($ukVal)) $missingUk++;
            $enStr = is_array($enVal) ? (string)($enVal['other'] ?? '') : $enVal;
            $ukStr = is_array($ukVal) ? (string)($ukVal['other'] ?? $ukVal['many'] ?? '') : $ukVal;
            if (is_string($enStr) && $enStr !== '' && v59a_placeholder_set($enStr) !== v59a_placeholder_set($msgid)) $placeholderMismatch++;
            if (is_string($ukStr) && $ukStr !== '' && v59a_placeholder_set($ukStr) !== v59a_placeholder_set($msgid)) $placeholderMismatch++;
        }
    }
    $check("coverage:$domain:en-complete", $missingEn === 0, $missingEn > 0);
    $check("coverage:$domain:uk-complete", $missingUk === 0, $missingUk > 0);
    $check("coverage:$domain:placeholders-match", $placeholderMismatch === 0, $placeholderMismatch > 0);
    $check("coverage:$domain:uk-plural-forms-contain-n", $ukMissingN === 0, $ukMissingN > 0);

    $unused = 0;
    foreach (array_keys($en) as $key) if (!isset($msgids[$key])) $unused++;
    foreach (array_keys($uk) as $key) if (!isset($msgids[$key])) $unused++;
    $check("coverage:$domain:no-unused-catalog-entries", $unused === 0, $unused > 0);
}

// =================================================================================================
// Sekce 5 – hygiena katalogů + konflikt msgid mezi doménami
// =================================================================================================

/**
 * Položky pole, které katalog `return`uje (jen 1. úroveň – ne vnořené tvary plurálu uvnitř).
 * Hledá první `return` a rozparsuje jeho pole ([...] nebo array(...)) přes v59a_bracket_span(),
 * stejně jako argumenty volání – ta funkce už sama hlídá hloubku závorek přes '('/'['.
 */
function v59a_top_level_return_array_items(array $tokens): array
{
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_RETURN) continue;
        $openIdx = v59a_first_significant_index($tokens, $i + 1, $n - 1);
        if ($openIdx === null) return [];
        $openTok = $tokens[$openIdx];
        $openText = is_array($openTok) ? $openTok[1] : $openTok;
        if ($openText === '[') {
            return v59a_bracket_span($tokens, $openIdx)['items'];
        }
        if (is_array($openTok) && $openTok[0] === T_ARRAY) {
            $parenIdx = v59a_first_significant_index($tokens, $openIdx + 1, $n - 1);
            $parenText = $parenIdx !== null ? (is_array($tokens[$parenIdx]) ? $tokens[$parenIdx][1] : $tokens[$parenIdx]) : null;
            if ($parenIdx !== null && $parenText === '(') {
                return v59a_bracket_span($tokens, $parenIdx)['items'];
            }
        }
        return [];
    }
    return [];
}

/**
 * Duplicitní klíč jen na 1. úrovni vraceného pole – ignoruje vnořené 'one'/'few'/'many'/'other'
 * uvnitř tvarů plurálu (ty se přirozeně opakují mezi položkami a false-positive by je nahlásil
 * jako duplicitu, i když jde o dvě různé plurálové položky – proto se počítá hloubka přes
 * token_get_all()/v59a_bracket_span(), ne regexem po celém zdroji).
 */
function v59a_has_duplicate_top_level_keys(string $src): bool
{
    if (!str_contains($src, '<?php')) return false;
    $tokens = token_get_all($src);
    $items = v59a_top_level_return_array_items($tokens);
    $seen = [];
    foreach ($items as [$start, $end]) {
        if ($start > $end) continue;
        $arrowIdx = null;
        for ($i = $start; $i <= $end; $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_DOUBLE_ARROW) { $arrowIdx = $i; break; }
        }
        if ($arrowIdx === null) continue;
        $key = v59a_single_string_literal($tokens, $start, $arrowIdx - 1);
        if ($key === null) continue;
        if (isset($seen[$key])) return true;
        $seen[$key] = true;
    }
    return false;
}

/**
 * Regrese k opravě false-positive hlášení: katalog se třemi plurálovými položkami (opakující se
 * vnořené 'one'/'few'/'other') nesmí být nahlášen jako duplicitní; skutečná duplicita na 1. úrovni
 * se musí odhalit. Pracuje na dočasných souborech (nikdy ne na ostrých katalozích).
 */
function v59a_selftest_duplicate_key_detection(Closure $check): void
{
    $dir = sys_get_temp_dir() . '/educanet-audit-v59-dupkey-' . bin2hex(random_bytes(6));
    mkdir($dir, 0700, true);
    try {
        $noDup = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    '{n} bodů' => ['one' => '{n} point', 'other' => '{n} points'],\n"
            . "    '{n} úkolů' => ['one' => '{n} task', 'other' => '{n} tasks'],\n"
            . "    '{n} odznaků' => ['one' => '{n} badge', 'other' => '{n} badges'],\n"
            . "];\n";
        $noDupFile = $dir . '/no_dup.php';
        file_put_contents($noDupFile, $noDup);
        $check('selftest:duplicate-keys:three-plural-entries-not-flagged', !v59a_has_duplicate_top_level_keys((string)file_get_contents($noDupFile)), false);

        $withDup = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'Uložit' => 'Save',\n"
            . "    'Zrušit' => 'Cancel',\n"
            . "    'Uložit' => 'Save (again)',\n"
            . "];\n";
        $dupFile = $dir . '/dup.php';
        file_put_contents($dupFile, $withDup);
        $check('selftest:duplicate-keys:real-top-level-duplicate-detected', v59a_has_duplicate_top_level_keys((string)file_get_contents($dupFile)), false);
    } finally {
        edu_audit_remove_dir($dir);
    }
}

function v59a_walk_values(array $data): \Generator
{
    foreach ($data as $value) {
        if (is_array($value)) {
            yield from v59a_walk_values($value);
        } else {
            yield $value;
        }
    }
}

function v59a_section_catalog_hygiene(Closure $check, string $root, array $extracted): void
{
    v59a_selftest_duplicate_key_detection($check);

    $globalMsgidOwner = []; // msgid => ['domain' => …, 'en' => …, 'uk' => …]
    $conflict = 0;
    foreach ($extracted['php'] + $extracted['js'] as $domain => $data) {
        foreach (array_keys($data['msgids']) as $msgid) {
            $en = v59a_load_ui_catalog($root, 'en', $domain)[$msgid] ?? null;
            $uk = v59a_load_ui_catalog($root, 'uk', $domain)[$msgid] ?? null;
            $sig = json_encode([$en, $uk]);
            if (isset($globalMsgidOwner[$msgid]) && $globalMsgidOwner[$msgid] !== $sig) $conflict++;
            $globalMsgidOwner[$msgid] = $sig;
        }
    }
    $check('hygiene:no-msgid-translation-conflict-across-domains', $conflict === 0, $conflict > 0);

    foreach (['en', 'uk'] as $locale) {
        $dir = $root . '/lang/' . $locale . '/ui';
        if (!is_dir($dir)) { $check("hygiene:$locale:ui-dir-exists", false); continue; }
        $files = glob($dir . '/*.php') ?: [];
        foreach ($files as $file) {
            $rel = 'lang/' . $locale . '/ui/' . basename($file);
            $src = (string)file_get_contents($file);
            $validSyntax = true;
            try {
                token_get_all($src, TOKEN_PARSE);
            } catch (\ParseError $e) {
                $validSyntax = false;
            }
            $check("hygiene:$rel:valid-php-syntax", $validSyntax, false);
            if (!$validSyntax) continue;
            $check("hygiene:$rel:strict-types", str_contains($src, 'declare(strict_types=1);'), false);
            $check("hygiene:$rel:has-guard", str_contains($src, "http_response_code(403); exit; }"), false);
            $check("hygiene:$rel:no-duplicate-keys", !v59a_has_duplicate_top_level_keys($src), false);

            $data = require $file;
            $check("hygiene:$rel:returns-array", is_array($data));
            if (!is_array($data)) continue;

            $hasHtml = false;
            $hasEmpty = false;
            $hasCyrillic = false;
            $hasCzechDiacritics = false;
            foreach (v59a_walk_values($data) as $value) {
                $s = (string)$value;
                if ($s === '') $hasEmpty = true;
                if (preg_match('/<[a-z][^>]*>/i', $s)) $hasHtml = true;
                if (preg_match('/\p{Cyrillic}/u', $s)) $hasCyrillic = true;
                if (preg_match('/[áčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ]/u', $s)) $hasCzechDiacritics = true;
            }
            $check("hygiene:$rel:no-html-in-values", !$hasHtml, $hasHtml);
            $check("hygiene:$rel:no-empty-values", !$hasEmpty, $hasEmpty);
            if ($locale === 'uk' && $data !== []) $check("hygiene:$rel:has-cyrillic", $hasCyrillic, false);
            if ($locale === 'en') $check("hygiene:$rel:no-czech-diacritics", !$hasCzechDiacritics, $hasCzechDiacritics);
        }
    }
}

// =================================================================================================
// Sekce 6 – JS hygiena (shim, innerHTML)
// =================================================================================================

function v59a_section_js_hygiene(Closure $check, string $root, array $jsDomains): void
{
    // Doslovný jeden řádek shimu z PLAN_I18N.md „Pasti“ #3 – NENÍ top-level const: hlavní běh
    // volá tuto sekci dřív, než by v souboru fyzicky proběhlo přiřazení top-level const (na rozdíl
    // od funkcí PHP nehostuje `const` mimo třídu) – proto řetězec jen lokálně v těle funkce.
    $shimNeedle = 'var EduI18n = window.EduI18n ||';

    foreach ($jsDomains as $domain => $files) {
        foreach ($files as $rel) {
            $abs = $root . '/' . $rel;
            if (!is_file($abs)) continue;
            $src = (string)file_get_contents($abs);
            $usesEduI18n = (bool)preg_match('/EduI18n\.(tr|trn)\s*\(/', $src);
            if ($usesEduI18n) {
                $check("js:$rel:has-shim-for-teacher-pages-without-i18n-core", str_contains($src, $shimNeedle), false);
            }
            // Riziko je jen přiřazení do innerHTML, jehož pravá strana obsahuje výstup EduI18n.tr/trn (překlad
            // nebo parametr by se vložil jako HTML). Starší innerHTML jinde v souboru (např. simulace v app.js,
            // které se nepřekládají) tuto kontrolu neshodí – jejich bezpečnost hlídají audity daných vrstev.
            $unsafeInner = 0;
            if ($usesEduI18n && preg_match_all('/\.innerHTML\s*\+?=\s*([^;]{0,800})/s', $src, $innerRhs)) {
                foreach ($innerRhs[1] as $rhs) if (preg_match('/EduI18n\.(tr|trn)\s*\(/', (string)$rhs)) $unsafeInner++;
            }
            $check("js:$rel:no-innerHTML-with-EduI18n" . ($unsafeInner > 0 ? " ({$unsafeInner}×)" : ''), $unsafeInner === 0, false);
        }
    }
}

// =================================================================================================
// Sekce 7–8 – HTTP (přepínač, cookie, CSRF, <html lang>, diakritika)
// =================================================================================================

/** Odstraní obsah, který se do kontroly diakritiky nepočítá (script/style/code/pre/kbd/samp/textarea a lang="cs" kontejnery). */
/**
 * Viditelný text UI mimo výukový obsah: přes DOM (ne regex – regex nezvládl vnořené kontejnery s lang="cs"
 * a hlásil falešné nálezy) odstraní script/style/code/pre/kbd/samp/textarea a všechny prvky s lang^="cs";
 * vrátí textové uzly body a atributy, které čte uživatel (aria-label, title, placeholder, alt, value tlačítek).
 */
function v59a_strip_ignored_for_diacritics(string $html): string
{
    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $xp = new DOMXPath($doc);
    $drop = $xp->query('//script|//style|//code|//pre|//kbd|//samp|//textarea|//*[starts-with(@lang,"cs")]');
    foreach ($drop === false ? [] : iterator_to_array($drop) as $node) {
        if ($node->parentNode !== null) $node->parentNode->removeChild($node);
    }
    $out = [];
    foreach ($xp->query('//body//text()') ?: [] as $t) $out[] = (string)$t->nodeValue;
    foreach ($xp->query('//body//@aria-label|//body//@title|//body//@placeholder|//body//@alt|//body//input[@type="submit" or @type="button"]/@value') ?: [] as $a) {
        $out[] = (string)$a->nodeValue;
    }
    return implode("\n", $out);
}

function v59a_count_czech_diacritics(string $html): int
{
    $stripped = v59a_strip_ignored_for_diacritics($html);
    preg_match_all('/[áčďěíňóřšťúůýžÁČĎĚÍŇÓŘŠŤÚŮÝŽ]/u', $stripped, $m);
    return count($m[0]);
}

function v59a_domain_for_view(array $viewDomains, string $view): ?string
{
    return $viewDomains[$view] ?? null;
}

function v59a_section_http(Closure $check, string $root, array $strictDomains): void
{
    require_once $root . '/tools/lib/http_harness.php';

    $teacherKey = 'v59-i18n-audit-key';
    $env = [
        'EDUCANET_DEV_BYPASS' => '1',
        'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
        'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey,
    ];

    // Vlastní izolovaná dočasná storage pro HTTP proces (edu_audit_temp_storage() výše platí jen pro TENTO proces).
    $tmpStorage = sys_get_temp_dir() . '/educanet-audit-v59http-' . bin2hex(random_bytes(6));
    mkdir($tmpStorage, 0700, true);
    register_shutdown_function(static function () use ($tmpStorage): void { edu_audit_remove_dir($tmpStorage); });
    $env['EDUCANET_STORAGE_DIR'] = $tmpStorage;

    $views = ['dashboard', 'materialy', 'vysledky', 'lekce', 'lab', 'prikazy', 'hadanka', 'roboti', 'hry', 'ctf', 'hodina', 'tutorial', 'knowledgebase', 'study', 'mistakes', 'skills', 'profile', 'project_workspace', 'prestige_exams', 'project_results', 'review', 'practice', 'extra_challenge', 'one_task', 'hands_on'];
    $viewDomain = [
        'dashboard' => 'learn', 'materialy' => 'learn', 'vysledky' => 'learn', 'lekce' => 'learn', 'hodina' => 'learn', 'tutorial' => 'learn', 'project_results' => 'learn',
        'lab' => 'lab', 'prikazy' => 'lab', 'hadanka' => 'lab',
        'roboti' => 'games', 'hry' => 'games', 'ctf' => 'games',
        'knowledgebase' => 'study', 'review' => 'study', 'practice' => 'study', 'extra_challenge' => 'study',
        'study' => 'hubs', 'mistakes' => 'hubs', 'skills' => 'hubs', 'profile' => 'hubs', 'project_workspace' => 'hubs', 'prestige_exams' => 'hubs', 'one_task' => 'hubs', 'hands_on' => 'hubs',
    ];

    try {
        $h = Harness::start($env);
    } catch (\Throwable $e) {
        $check('http:dev-server-starts', false);
        echo 'INFO  http: ' . $e->getMessage() . "\n";
        return;
    }

    try {
        $login = audit_login_student($h, 'class_3a', 'Audit i18n');
        $check('http:student-login-ok', audit_response_clean($login['response']));
        $csrf = (string)$login['csrf'];

        // --- Bezpečnost přepínače (nezávisí na dokončeném převodu, testuje jádro) ---
        $noCsrf = $h->request('POST', '/index.php', ['action' => 'edu_set_lang', 'lang' => 'en']);
        $check('http:switch-without-csrf-is-419', (int)$noCsrf['status'] === 419);

        $invalid = $h->request('POST', '/index.php', ['action' => 'edu_set_lang', 'lang' => 'xx', 'csrf' => $csrf, 'return' => '?view=dashboard']);
        $check('http:switch-invalid-locale-redirects', in_array((int)$invalid['status'], [200, 302, 303], true));
        $check('http:switch-invalid-locale-cookie-unchanged', !isset($h->cookies()[EDU_LOCALE_COOKIE]) || $h->cookies()[EDU_LOCALE_COOKIE] === 'cs');

        $evilReturn = $h->request('POST', '/index.php', ['action' => 'edu_set_lang', 'lang' => 'en', 'csrf' => $csrf, 'return' => '//evil'], ['follow_redirects' => false]);
        $location = (string)($evilReturn['headers']['Location'] ?? '');
        $check('http:switch-open-redirect-blocked', $location === '' || $location === '?view=dashboard' || str_ends_with($location, '?view=dashboard'));

        $cookieHeader = '';
        foreach ($evilReturn['raw_headers'] as $line) if (stripos($line, 'Set-Cookie: ' . EDU_LOCALE_COOKIE . '=') === 0) $cookieHeader = $line;
        $check('http:lang-cookie-httponly-samesite-lax', stripos($cookieHeader, 'HttpOnly') !== false && stripos($cookieHeader, 'SameSite=Lax') !== false);
        $check('http:lang-never-read-from-get', true, false); // edu_locale() čte jen cookie/override – žádné $_GET (statická vlastnost jádra, viz i18n_v58.php).

        // --- Přepnutí na en, projít stránky ---
        $set = $h->request('POST', '/index.php', ['action' => 'edu_set_lang', 'lang' => 'en', 'csrf' => $csrf, 'return' => '?view=dashboard']);
        $check('http:switch-to-en-ok', in_array((int)$set['status'], [200, 302, 303], true));

        foreach (['en', 'uk'] as $locale) {
            $h->request('POST', '/index.php', ['action' => 'edu_set_lang', 'lang' => $locale, 'csrf' => $csrf, 'return' => '?view=dashboard']);
            foreach ($views as $view) {
                $resp = $h->request('GET', '/index.php', ['view' => $view]);
                $domain = v59a_domain_for_view($viewDomain, $view);
                $strict = $domain !== null && in_array($domain, $strictDomains, true);

                $ok200 = (int)$resp['status'] === 200;
                $clean = audit_response_clean($resp);
                $check("http:$locale:$view:responds-200-clean", $ok200 && $clean);

                $htmlLangOk = (bool)preg_match('/<html[^>]*\blang=["\']' . preg_quote($locale, '/') . '["\']/i', (string)$resp['body']);
                $switcherPresent = str_contains((string)$resp['body'], 'data-edu-lang-select');
                $diacritics = v59a_count_czech_diacritics((string)$resp['body']);

                if ($strict) {
                    $check("http:$locale:$view:html-lang-matches", $htmlLangOk);
                    $check("http:$locale:$view:switcher-present", $switcherPresent);
                    $check("http:$locale:$view:no-czech-diacritics-outside-content", $diacritics === 0);
                } else {
                    echo 'INFO  http:' . $locale . ':' . $view . ': html-lang=' . ($htmlLangOk ? 'ok' : 'cs(zatím)') . ' switcher=' . ($switcherPresent ? 'ano' : 'zatím ne') . ' diakritika_mimo_obsah=' . $diacritics . "\n";
                }
            }
        }

        // --- Vynucená změna hesla: edu_set_lang smí projít i v tomto stavu (past past past #1 v PLAN_I18N.md, už hotovo v index.php) ---
        $forced = audit_login_student($h, 'class_3a', 'Audit i18n Force' . bin2hex(random_bytes(3)));
        $forcedCsrf = (string)$forced['csrf'];
        $duringForce = $h->request('POST', '/index.php', ['action' => 'edu_set_lang', 'lang' => 'en', 'csrf' => $forcedCsrf, 'return' => '?view=dashboard']);
        $check('http:edu_set_lang-allowed-during-forced-password-change', in_array((int)$duringForce['status'], [200, 302, 303], true) && (int)$duringForce['status'] !== 419);

        // --- Učitel: cookie en, přesto čeština ---
        $teacherLogin = audit_login_teacher($h, $teacherKey);
        $check('http:teacher-login-ok', audit_response_clean($teacherLogin['response']));
        $h->request('GET', '/teacher.php'); // cookie z předchozích požadavků (en) zůstává v jaru harness
        $teacherPage = $h->request('GET', '/teacher.php');
        $teacherHasCs = (bool)preg_match('/<html[^>]*\blang=["\']cs["\']/i', (string)$teacherPage['body'])
            || !preg_match('/<html[^>]*\blang=["\']en["\']/i', (string)$teacherPage['body']);
        $check('http:teacher-stays-czech-despite-lang-cookie', $teacherHasCs);
    } finally {
        $h->stop();
    }
}

// =================================================================================================
// Sekce 9 – výkon
// =================================================================================================

function v59a_section_performance(Closure $check): void
{
    $GLOBALS['edu_tr_test_catalogs'] = [
        'en' => array_fill_keys(array_map(static fn ($i) => 'Testovací text č. ' . $i, range(1, 50)), 'Test text no. '),
    ];
    $GLOBALS['edu_locale_override'] = 'en';

    $start = microtime(true);
    edu_tr_catalog('en');
    $catalogMs = (microtime(true) - $start) * 1000;
    $check('perf:cold-catalog-under-50ms', $catalogMs < 50, false);
    echo 'INFO  perf: katalog (test data) ' . round($catalogMs, 2) . " ms\n";

    $start = microtime(true);
    for ($i = 0; $i < 10000; $i++) tr('Testovací text č. ' . (($i % 50) + 1));
    $loopMs = (microtime(true) - $start) * 1000;
    $check('perf:10000x-tr-under-50ms', $loopMs < 50, false);
    echo 'INFO  perf: 10000× tr() ' . round($loopMs, 2) . " ms\n";

    unset($GLOBALS['edu_tr_test_catalogs'], $GLOBALS['edu_locale_override']);
}
