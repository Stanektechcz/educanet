<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Kontrola obsahu vlastních úloh a otázek (CNT-03).
 *
 * Kontrakt (docs/V58_PLAN.md §3): cnt58_check_text(string $text, string $context = 'level'): array
 * → ['ok' => bool, 'issues' => [['code' => string, 'message' => string], …]].
 * Každá položka navíc nese 'severity' => 'block'|'warn' (rozšíření nad rámec kontraktu – 'ok' je
 * false, jen když je aspoň jedna položka 'block'; 'warn' smí učitel po zvážení uložit/zveřejnit,
 * ale vidí ho v náhledu). Volající, kteří čtou jen 'code'/'message', fungují beze změny.
 *
 * Bez závislostí na editoru (lab_v58_editor*.php) – volají ji i týmové hry a incidenty
 * (TG-CORE/TG-BANK, ARENA-B) přes function_exists('cnt58_check_text') u vlastních otázek/textů.
 *
 * Bezpečnostní invariant: čistě textová analýza (regulární výrazy, porovnání řetězců) – nic
 * se nespouští, nikam se nepřipojuje, nic se neukládá.
 *
 * Seznam zakázaných výrazů je záměrně jen datem níže (normalizované kmeny bez diakritiky),
 * ne rozepsaný v komentářích/dokumentaci – ať se text úloh dá v klidu code-review bez nutnosti
 * to zobrazovat všude znovu.
 */

// ---------------------------------------------------------------------------
// Normalizace textu (diakritika, leetspeak, opakovaná písmena)
// ---------------------------------------------------------------------------

const CNT58_DIACRITICS_MAP = [
    'á' => 'a', 'ä' => 'a', 'ǎ' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'å' => 'a',
    'č' => 'c', 'ć' => 'c', 'ç' => 'c',
    'ď' => 'd', 'đ' => 'd',
    'é' => 'e', 'ě' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
    'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
    'ľ' => 'l', 'ĺ' => 'l', 'ł' => 'l',
    'ň' => 'n', 'ń' => 'n', 'ñ' => 'n',
    'ó' => 'o', 'ô' => 'o', 'ò' => 'o', 'ö' => 'o', 'õ' => 'o', 'ő' => 'o',
    'ř' => 'r',
    'š' => 's', 'ś' => 's', 'ş' => 's',
    'ť' => 't', 'ţ' => 't',
    'ú' => 'u', 'ů' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u',
    'ý' => 'y', 'ÿ' => 'y',
    'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
];

const CNT58_LEET_MAP = ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '8' => 'b', '@' => 'a', '$' => 's', '!' => 'i', '+' => 't', '|' => 'i'];

/** Lowercase + bez diakritiky (obecné skládání pro hledání klíčových slov). */
function cnt58_fold(string $s): string
{
    return strtr(mb_strtolower($s, 'UTF-8'), CNT58_DIACRITICS_MAP);
}

/** cnt58_fold + leetspeak náhrady + sbalení 3+ opakovaných znaků (kuuurva → kurva). Pro kmeny zakázaných slov. */
function cnt58_normalize_word(string $s): string
{
    $s = strtr(cnt58_fold($s), CNT58_LEET_MAP);
    return (string)preg_replace('/(.)\1{2,}/u', '$1', $s);
}

// ---------------------------------------------------------------------------
// Zakázané výrazy: normalizované kmeny (prefix po normalizaci), po kategoriích jen pro přehled
// v kódu – ne pro výstup. Kmen = začátek slova, aby „chovná“ nespadlo pod kmen „hovn“ apod.
// ---------------------------------------------------------------------------

function cnt58_forbidden_stems(): array
{
    static $stems = null;
    if ($stems !== null) return $stems;
    $vulgarity = ['kurv', 'hovn', 'srac', 'kokot', 'prdel', 'zmrd', 'curak', 'kunda'];
    $insults = ['debil', 'kreten', 'idiot', 'blbec', 'hlupak', 'magor', 'cvok', 'tupec', 'hajzl', 'svine'];
    $violence = ['zabit', 'zabij', 'zavrazd', 'mucit', 'znasiln', 'ublizit', 'zmlat', 'vyhrozo'];
    $drugs = ['marihuan', 'pervitin', 'heroin', 'kokain', 'extaze', 'hasis', 'feten', 'fetova'];
    return $stems = array_values(array_unique(array_merge($vulgarity, $insults, $violence, $drugs)));
}

// Kmeny, které mají v informatickém kontextu neškodný druhý význam (zabít/zabije proces = ukončit
// ho, anglicky „kill“) – u nich se násilná interpretace bere jen bez kontextových slov níže.
const CNT58_TECH_EXEMPT_STEMS = ['zabit', 'zabij'];
const CNT58_TECH_CONTEXT_STEMS = ['proces', 'pid', 'aplika', 'sluzb', 'program', 'uzel', 'server', 'skript', 'ukol', 'system', 'sit', 'kill'];

function cnt58_is_tech_exempt(string $stem, string $foldedText): bool
{
    if (!in_array($stem, CNT58_TECH_EXEMPT_STEMS, true)) return false;
    foreach (CNT58_TECH_CONTEXT_STEMS as $ctx) if (str_contains($foldedText, $ctx)) return true;
    return false;
}

/** @return list<array{code:string,message:string,severity:string}> */
function cnt58_scan_forbidden_words(string $text): array
{
    $issues = [];
    $folded = cnt58_fold($text);
    $tokens = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $normTokens = array_map('cnt58_normalize_word', $tokens);
    $hit = null;
    foreach (cnt58_forbidden_stems() as $stem) {
        if (cnt58_is_tech_exempt($stem, $folded)) continue;
        foreach ($normTokens as $token) {
            if (str_starts_with($token, $stem)) { $hit = $stem; break 2; }
        }
    }
    // Rozestupová obchvatka (k.u.r.v.a, k u r v a): spoj sousední jednopísmenné/dvoupísmenné
    // úseky a zkontroluj i to – ale jen tam, kde by samotné tokeny kmen nenašly.
    if ($hit === null) {
        $short = '';
        foreach ($normTokens as $token) $short .= mb_strlen($token) <= 2 ? $token : ' ';
        foreach (explode(' ', $short) as $run) {
            if ($run === '') continue;
            foreach (cnt58_forbidden_stems() as $stem) {
                if (cnt58_is_tech_exempt($stem, $folded)) continue;
                if (str_contains($run, $stem)) { $hit = $stem; break 2; }
            }
        }
    }
    if ($hit !== null) {
        $issues[] = ['code' => 'forbidden_word', 'message' => 'Text obsahuje výraz, který do výukového obsahu nepatří (vulgarismus, urážka, násilí nebo drogy).', 'severity' => 'block'];
    }
    return $issues;
}

// ---------------------------------------------------------------------------
// Osobní údaje: e-mail, telefon, rodné číslo (blokace), adresa (varování – nejmíň jistá)
// ---------------------------------------------------------------------------

/** @return list<array{code:string,message:string,severity:string}> */
function cnt58_scan_personal_data(string $text): array
{
    $issues = [];
    if (preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $text) === 1) {
        $issues[] = ['code' => 'personal_email', 'message' => 'Text obsahuje e-mailovou adresu.', 'severity' => 'block'];
    }
    // Oddělovač jen mezera/pomlčka (bez tečky): "255.255.255.0"/"192.168.1.10" (IPv4, i s /maskou)
    // nesmí spadnout pod telefon – tečka je v síťových/verzovacích textech běžná, u čísel psaných
    // se skutečnou tečkou (datum, IP) jde prakticky vždy o něco jiného než telefon.
    if (preg_match('/(\+\d{2,3}[\s-]?)?\b\d{3}[\s-]\d{3}[\s-]\d{3}\b/', $text) === 1) {
        $issues[] = ['code' => 'personal_phone', 'message' => 'Text obsahuje telefonní číslo.', 'severity' => 'block'];
    } elseif (preg_match('/\b\d{9}\b/', $text) === 1) {
        $issues[] = ['code' => 'personal_phone_maybe', 'message' => 'Text obsahuje devítimístné číslo – zkontroluj, jestli to není telefon.', 'severity' => 'warn'];
    }
    // Rodné číslo: RRMMDD(/)XXX(X); měsíc 01–12 (muži) nebo 51–62 (ženy, +50).
    if (preg_match('#\b\d{2}(?:0[1-9]|1[0-2]|5[1-9]|6[0-2])(?:0[1-9]|[12]\d|3[01])/?\d{3,4}\b#', $text) === 1) {
        $issues[] = ['code' => 'personal_birth_number', 'message' => 'Text vypadá jako rodné číslo.', 'severity' => 'block'];
    }
    $folded = cnt58_fold($text);
    $hasAddressWord = false;
    foreach (['ulic', 'namest', 'bydlist', 'trvale bydliste'] as $addrStem) {
        if (str_contains($folded, $addrStem)) { $hasAddressWord = true; break; }
    }
    $hasPostcode = preg_match('/\b\d{3}\s?\d{2}\b/', $text) === 1;
    $hasHouseNo = preg_match('#\b\d{1,4}/\d{1,4}\b#', $text) === 1;
    if ($hasAddressWord && ($hasPostcode || $hasHouseNo)) {
        $issues[] = ['code' => 'personal_address', 'message' => 'Text může obsahovat skutečnou adresu (ulice, PSČ nebo číslo popisné) – zkontroluj.', 'severity' => 'warn'];
    }
    return $issues;
}

// ---------------------------------------------------------------------------
// Veřejné API (CNT-03)
// ---------------------------------------------------------------------------

/**
 * Zkontroluje text vlastní úlohy/otázky. $context: 'level'|'hint'|'question'|'story'|… (jen
 * pro srozumitelnější logy/rozlišení volajícího, logika je pro všechny kontexty stejná).
 * @return array{ok:bool,issues:list<array{code:string,message:string,severity:string}>}
 */
function cnt58_check_text(string $text, string $context = 'level'): array
{
    $text = trim($text);
    if ($text === '') return ['ok' => true, 'issues' => []];
    $issues = array_merge(cnt58_scan_forbidden_words($text), cnt58_scan_personal_data($text));
    $ok = array_filter($issues, static fn(array $i): bool => ($i['severity'] ?? 'warn') === 'block') === [];
    unset($context);
    return ['ok' => $ok, 'issues' => $issues];
}

/** Souhrnná kontrola víc textových polí najednou (pohodlnostní obálka nad cnt58_check_text pro editor). */
function cnt58_check_fields(array $fields, string $context = 'level'): array
{
    $issues = [];
    foreach ($fields as $label => $text) {
        if (!is_string($text) || trim($text) === '') continue;
        foreach (cnt58_check_text($text, $context)['issues'] as $issue) {
            $issue['field'] = (string)$label;
            $issues[] = $issue;
        }
    }
    $ok = array_filter($issues, static fn(array $i): bool => ($i['severity'] ?? 'warn') === 'block') === [];
    return ['ok' => $ok, 'issues' => $issues];
}

/**
 * Checklist pro učitele před zveřejněním (věci, které automatická kontrola nedokáže spolehlivě
 * ověřit – stereotypy, skutečné osoby…). Vrací statický seznam pro UI editoru.
 * @return list<array{id:string,label:string}>
 */
function cnt58_checklist_items(): array
{
    return [
        ['id' => 'vyukova', 'label' => 'Úloha je výuková a má jasný cíl (co se žák naučí).'],
        ['id' => 'nevulgarni', 'label' => 'Text neobsahuje vulgarismy, urážky ani násilí.'],
        ['id' => 'bez_stereotypu', 'label' => 'Text neobsahuje genderové ani jiné stereotypy.'],
        ['id' => 'bez_osob', 'label' => 'Text nezmiňuje skutečné osoby, značky ani školy jinde než jako fiktivní „školní/cvičný server“.'],
        ['id' => 'bez_udaju', 'label' => 'Text neobsahuje osobní údaje (skutečné jméno, adresu, telefon, e-mail, rodné číslo).'],
    ];
}
