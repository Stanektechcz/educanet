<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v71 · karty výukových dnů bez čísla lekce (jen čtení, bez výstupu).
 *
 * Školní rok má 28 lekcí a 11 výukových dnů bez lekce (seznamovací blok, projektové checkpointy, mastery clinic,
 * capstone sprinty, obhajoby, retrospektiva, portfolio, uzavření). Dnešní hodina pro ně dřív neměla obsah.
 * Karta = zjednodušený model dne: cíl, průběh (90 min), co mají žáci mít, co učitel kontroluje, vazba na
 * `class_tail_focus` (téma závěru roku třídy) a na projekty v65. Ve v71 jde o základní karty; plný obsah přijde
 * s obsahovou stopou (vlna 2) jako overlay `days` v lesson_content_v7N_*.php.
 */

const CD71_DAY_KINDS = ['intro', 'project', 'mastery', 'assessment', 'reflection', 'portfolio', 'close'];

/** Obsah karet podle druhu dne (a u projektových dnů podle pořadí slotu). @return array<string,array<string,mixed>> */
function cd71_templates(): array
{
    return [
        'intro' => [
            'goal' => 'Žáci znají pravidla kurzu, umí se přihlásit a vyplnili vstupní diagnostiku; vím, odkud každý startuje.',
            'flow' => [[0, 15, 'Seznámení a pravidla', 'Jak výuka poběží, kde jsou materiály, co je hodnoceno a co ne.'],
                [15, 35, 'Přihlášení a nástroje', 'Každý se přihlásí, projde přehled a otevře nástroje předmětu.'],
                [35, 70, 'Vstupní diagnostika', 'Seznamovací dotazník a krátká diagnostika bez známky.'],
                [70, 90, 'Domluva a první krok', 'Shrnutí, otázky a co si žáci připraví na lekci 1.']],
            'bring' => ['Přihlašovací údaje (kartička s jednorázovým heslem).', 'Nabitý notebook nebo místo v učebně.'],
            'check' => ['Všichni se přihlásili (záložka Přístupy).', 'Dotazník vyplnilo co nejvíc žáků (záložka Seznamovací dotazník).', 'Nikdo neodchází bez fungujícího účtu.'],
        ],
        'project' => [
            'goal' => 'Tým posune projekt o ověřitelný kus práce a ví, co udělá do příště.',
            'flow' => [[0, 10, 'Stand-up', 'Každý tým: co je hotovo, co dnes, co blokuje.'],
                [10, 70, 'Práce v týmech', 'Souvislá práce na projektu, učitel obchází týmy podle potřeby.'],
                [70, 85, 'Checkpoint', 'Tým ukáže důkaz postupu (soubor, odkaz, výstup příkazu) a zapíše další krok.'],
                [85, 90, 'Úklid a plán', 'Uložit práci, rozdělit úkoly do příště.']],
            'bring' => ['Rozpracovaný projekt a přístup ke sdíleným souborům.', 'Seznam úkolů týmu (kdo co dělá).'],
            'check' => ['Každý tým má zapsaný důkaz postupu.', 'Role v týmu jsou rozdělené a nikdo nestojí.', 'Blokující problémy jsou zapsané a mají vlastníka.'],
        ],
        'mastery' => [
            'goal' => 'Každý žák dorovná jednu mezeru v kompetencích nebo si ověří zvládnutí před závěrem roku.',
            'flow' => [[0, 10, 'Mapa mezer', 'Žáci si podle přehledu kompetencí vyberou, co dorovnat.'],
                [10, 70, 'Stanoviště', 'Opakování po skupinách: podpora / standard / výzva.'],
                [70, 85, 'Ověření', 'Krátká praktická zkouška vybrané kompetence.'],
                [85, 90, 'Zápis', 'Co jsem dorovnal/a a jakým důkazem to prokazuji.']],
            'bring' => ['Vlastní přehled kompetencí (profil).', 'Jednu práci, kterou chtějí zlepšit.'],
            'check' => ['Každý žák má vybranou jednu kompetenci k dorovnání.', 'Ověření má konkrétní důkaz, ne jen „umím to“.'],
        ],
        'assessment' => [
            'goal' => 'Týmy obhájí výsledek a rozhodnutí; hodnotí se důkazy a role, ne množství práce.',
            'flow' => [[0, 10, 'Pravidla obhajob', 'Pořadí, čas na tým, kritéria a otázky publika.'],
                [10, 75, 'Obhajoby', 'Každý tým: cíl, výsledek, klíčové rozhodnutí, důkaz, co by udělal jinak.'],
                [75, 90, 'Zpětná vazba', 'Silná stránka a jeden konkrétní další krok pro každý tým.']],
            'bring' => ['Hotový výstup projektu a krátkou prezentaci.', 'Důkazy (soubory, výstupy, záznam testu).'],
            'check' => ['Každý člen týmu mluví o své roli.', 'Rubrika je známá předem a použitá u všech týmů stejně.', 'Zpětná vazba obsahuje silnou stránku i další krok.'],
        ],
        'reflection' => [
            'goal' => 'Žáci pojmenují, co se naučili, co jim pomohlo a co příště změní; dají si férovou zpětnou vazbu.',
            'flow' => [[0, 15, 'Osobní ohlédnutí', 'Self review: co umím teď a na začátku roku jsem neuměl/a.'],
                [15, 50, 'Týmová retrospektiva', 'Keep / Improve / Try pro každý tým.'],
                [50, 80, 'Peer feedback', 'Konkrétní zpětná vazba spolužákům podle vzoru.'],
                [80, 90, 'Shrnutí', 'Co si třída bere do dalšího roku.']],
            'bring' => ['Své výstupy z roku (profil, projekty).'],
            'check' => ['Zpětná vazba je konkrétní a respektující.', 'Každý tým má zapsané Keep / Improve / Try.'],
        ],
        'portfolio' => [
            'goal' => 'Každý žák vybere ověřené výstupy do portfolia a popíše svůj podíl na nich.',
            'flow' => [[0, 10, 'Co patří do portfolia', 'Kritéria výběru a ukázka dobrého popisu.'],
                [10, 70, 'Výběr a popis', 'Výběr 2–3 výstupů, popis role, rozhodnutí a důkazu.'],
                [70, 90, 'Showcase', 'Krátké ukázky ve dvojicích nebo před třídou.']],
            'bring' => ['Přístup k projektům a profilu.'],
            'check' => ['Vybrané výstupy jsou ověřené (hodnocené nebo s důkazem).', 'Popis role je pravdivý a vlastními slovy.', 'Obrázky a podklady mají jasný původ a licenci.'],
        ],
        'close' => [
            'goal' => 'Uzavřít rok: přehled zvládnutých kompetencí, osobní souhrn růstu a zpětná vazba ke kurzu.',
            'flow' => [[0, 20, 'Přehled roku', 'Co jsme probrali a kde třída skončila.'],
                [20, 50, 'Osobní souhrn', 'Každý žák si zapíše tři největší posuny a jeden cíl.'],
                [50, 75, 'Zpětná vazba ke kurzu', 'Anonymní a konkrétní: co zachovat, co změnit.'],
                [75, 90, 'Uzavření', 'Poděkování a doporučení na prázdniny.']],
            'bring' => ['Profil s kompetencemi a portfoliem.'],
            'check' => ['Zpětnou vazbu ke kurzu odevzdal co nejvyšší podíl třídy.', 'Nikdo neodchází bez osobního souhrnu.'],
        ],
    ];
}

/** Zpřesnění projektových dnů podle slotu (checkpointy a capstone sprinty). @return array<int,array{goal:string,check:list<string>}> */
function cd71_project_slots(): array
{
    return [
        29 => ['goal' => 'Checkpoint 1: tým má zadání, rozdělené role a první funkční návrh.', 'check' => ['Brief a kritéria úspěchu jsou zapsané.', 'Role jsou rozdělené a každý ví, co dodá.']],
        30 => ['goal' => 'Checkpoint 2: hlavní část projektu funguje a je ověřená prvním testem.', 'check' => ['Existuje funkční jádro, ne jen návrh.', 'Tým má zapsaný první test a jeho výsledek.']],
        31 => ['goal' => 'Checkpoint 3: projekt je kompletní na úrovni „hotovo, ale neuhlazené“; začíná QA.', 'check' => ['Chybí jen dokončovací práce.', 'QA seznam je sepsaný a rozdělený.']],
        33 => ['goal' => 'Capstone sprint 1: souvislá týmová práce na závěrečném projektu, učitel koučuje podle potřeby.', 'check' => ['Tým pracuje podle plánu sprintu.', 'Rizika jsou zapsaná včas, ne až na konci.']],
        34 => ['goal' => 'Capstone sprint 2: integrace, QA, dokumentace a příprava obhajoby.', 'check' => ['Finální výstup prošel QA.', 'Dokumentace a podklady k obhajobě jsou hotové.']],
    ];
}

/** Soubor školního roku (jednou za požadavek), když není načtená Dnešní hodina v70. */
function cd71_school_year(): array
{
    static $year = null;
    if ($year === null) {
        $loaded = require __DIR__ . '/school_year.php';
        $year = is_array($loaded) ? $loaded : [];
    }
    return $year;
}

/** Druh dne z řádku kalendáře (neznámý druh → projekt). */
function cd71_day_kind(array $row): string
{
    $kind = (string)($row['kind'] ?? '');
    return in_array($kind, CD71_DAY_KINDS, true) ? $kind : 'project';
}

/** Výukové dny bez lekce v základním kalendáři (bez výjimek) – očekává se 11. @return list<array<string,mixed>> */
function cd71_calendar_days(array $schoolYear): array
{
    $out = [];
    foreach ((array)($schoolYear['calendar'] ?? []) as $row) {
        if (!is_array($row) || (string)($row['status'] ?? '') !== 'teaching' || (int)($row['lesson_number'] ?? 0) > 0) continue;
        $out[] = $row;
    }
    return $out;
}

/**
 * Den bez lekce, který Dnešní hodina ukáže jako první: dnešní výukový den bez čísla lekce, jinak nejbližší výukový den,
 * pokud přijde dřív než další lekce (nebo už žádná lekce nezbývá). Řádky jsou efektivní (s výjimkami kalendáře).
 * @return array<string,mixed>|null řádek kalendáře + klíč `is_today`
 */
function cd71_next_day_row(array $rows, string $today): ?array
{
    foreach ($rows as $row) {
        if (!is_array($row) || (string)($row['status'] ?? '') !== 'teaching' || (string)($row['date'] ?? '') < $today) continue;
        if ((int)($row['lesson_number'] ?? 0) > 0) return null;
        return $row + ['is_today' => (string)$row['date'] === $today];
    }
    return null;
}

/**
 * Karta dne bez lekce (čistá funkce nad řádkem kalendáře a souborem školního roku).
 * @return array<string,mixed>
 */
function cd71_day_card(string $classId, array $row, ?array $schoolYear = null): array
{
    $kind = cd71_day_kind($row);
    $slot = (int)($row['slot'] ?? 0);
    $tpl = cd71_templates()[$kind];
    if ($kind === 'project' && isset(cd71_project_slots()[$slot])) {
        $tpl['goal'] = cd71_project_slots()[$slot]['goal'];
        $tpl['check'] = array_merge(cd71_project_slots()[$slot]['check'], $tpl['check']);
    }
    if ($schoolYear === null) $schoolYear = function_exists('lt70_school_year') ? lt70_school_year() : cd71_school_year();
    $focus = trim((string)($schoolYear['class_tail_focus'][$classId] ?? ''));
    $flow = [];
    foreach ($tpl['flow'] as [$from, $to, $phase, $text]) $flow[] = ['from' => $from, 'to' => $to, 'minutes' => $to - $from, 'phase' => $phase, 'text' => $text];
    return [
        'id' => $classId . '_d' . str_pad((string)$slot, 2, '0', STR_PAD_LEFT), 'class_id' => $classId, 'number' => null, 'day_kind' => $kind, 'slot' => $slot,
        'date' => (string)($row['date'] ?? ''), 'title' => trim((string)($row['calendar_label'] ?? ($row['title'] ?? 'Výukový den'))),
        'description' => trim((string)($row['description'] ?? '')), 'goal' => $tpl['goal'], 'flow' => $flow, 'bring' => $tpl['bring'], 'check' => $tpl['check'],
        'focus' => $focus, 'capstone' => trim((string)explode('·', $focus)[0]),
        'projects' => in_array($kind, ['project', 'assessment', 'portfolio'], true),
        'meta' => ['source' => 'calendar_days_v71.php', 'status' => 'zakladni', 'overlay' => ''],
    ];
}

/** Overlay dne z obsahové stopy (čistá funkce). */
function cd71_apply_overlay(array $card, array $ov): array
{
    foreach (['title', 'goal', 'description'] as $key) if (isset($ov[$key]) && is_string($ov[$key]) && trim($ov[$key]) !== '') $card[$key] = trim($ov[$key]);
    foreach (['bring' => 'materials', 'check' => 'assessment'] as $key => $alt) {
        $list = $ov[$key] ?? ($ov[$alt] ?? null);
        if (is_array($list)) $card[$key] = array_values(array_filter(array_map(static fn($v): string => trim(is_array($v) ? (string)($v['text'] ?? '') : (string)$v), $list), static fn(string $v): bool => $v !== ''));
    }
    if (is_array($ov['timeline'] ?? null)) {
        $flow = [];
        foreach ($ov['timeline'] as $seg) {
            if (!is_array($seg) || !isset($seg['from'], $seg['to']) || (int)$seg['to'] <= (int)$seg['from']) continue;
            $flow[] = ['from' => (int)$seg['from'], 'to' => (int)$seg['to'], 'minutes' => (int)$seg['to'] - (int)$seg['from'], 'phase' => (string)($seg['phase'] ?? ''), 'text' => (string)($seg['teacher'] ?? ($seg['text'] ?? ''))];
        }
        if ($flow !== []) $card['flow'] = $flow;
    }
    $card['meta']['status'] = in_array((string)($ov['status'] ?? ''), ['navrh', 'schvaleno'], true) ? (string)$ov['status'] : 'navrh';
    $card['meta']['overlay'] = (string)($ov['_file'] ?? '');
    return $card;
}
