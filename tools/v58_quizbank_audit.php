<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · TG-BANK – CLI audit kvízové banky (sítě + grafika).
 * Ověřuje schéma položek (shodné s tg58_quiz_normalize v teamgames_v58_quiz.php), jedinečnost
 * id a zadání, rozsahy délek textů, konzistenci line/category/type/answer, rozložení obtížností,
 * pestrost typů na kategorii a základní kontrolu obsahu (cnt58_check_text, je-li k dispozici).
 * Nepracuje se storage/ ani se session, nečte žádná data žáků. Spuštění: php tools/v58_quizbank_audit.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);

$checks = 0;
$failed = 0;
function check(bool $ok, string $label, string $detail = ''): void
{
    global $checks, $failed;
    $checks++;
    if (!$ok) $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . ($ok || $detail === '' ? '' : ' – ' . $detail) . PHP_EOL;
}

const V58QB_NET_CATEGORIES = ['linux', 'files', 'ip', 'dns', 'ports', 'procs', 'security', 'hw'];
const V58QB_GFX_CATEGORIES = ['color', 'type', 'formats', 'html', 'css', 'a11y', 'ux', 'license'];
const V58QB_TYPES = ['single', 'multi', 'bool', 'numeric', 'order', 'text', 'command'];
const V58QB_BLOCKED_WORDS = ['facebook', 'instagram', 'tiktok', 'whatsapp', 'snapchat', 'youtube', 'google', 'microsoft', 'apple', 'adobe', 'photoshop', 'windows', 'macos', 'kokot', 'kurva', 'piča', 'čurák', 'debil', 'blbec'];

/** Náhradní jednoduchý obsahový filtr – použije se, jen když CNT-03 (cnt58_check_text) není k dispozici. */
function v58qb_fallback_check_text(string $text): bool
{
    $low = mb_strtolower($text);
    foreach (V58QB_BLOCKED_WORDS as $word) {
        if ($word !== '' && mb_strpos($low, $word) !== false) return false;
    }
    return true;
}

function v58qb_content_ok(string $text): bool
{
    if (trim($text) === '') return true;
    if (function_exists('cnt58_check_text')) {
        $r = cnt58_check_text($text, 'teamgames_v58_bank');
        return !(is_array($r) && empty($r['ok']));
    }
    return v58qb_fallback_check_text($text);
}

// ---------------------------------------------------------------------------
// Validace jedné položky (rozdělená na menší kroky kvůli čitelnosti)
// ---------------------------------------------------------------------------

/** id/line/category musí spolu ladit: prefix odpovídá linii, prostřední segment id = pole category. */
function v58qb_validate_identity(array $it, string $expectLine, array $expectCats): string
{
    $id = (string)($it['id'] ?? '');
    if (preg_match('/^(net|gfx)\.[a-z0-9]+\.[0-9]{3}$/', $id) !== 1) return 'id neodpovídá formátu net./gfx.<slug>.NNN';
    if (preg_match('/^[a-z0-9][a-z0-9_.-]{1,63}$/', $id) !== 1) return 'id neprojde TG-CORE regexem';
    $prefix = $expectLine === 'networks' ? 'net.' : 'gfx.';
    if (!str_starts_with($id, $prefix)) return 'id prefix neodpovídá linii souboru';
    if (($it['line'] ?? null) !== $expectLine) return 'pole line neodpovídá souboru';
    if (!array_key_exists('classes', $it) || ($it['classes'] !== null && !is_array($it['classes']))) return 'classes musí být null nebo pole';
    $category = (string)($it['category'] ?? '');
    if (!in_array($category, $expectCats, true)) return 'neznámá kategorie „' . $category . '“';
    $idCat = explode('.', $id)[1] ?? '';
    if ($idCat !== $category) return 'kategorie v id neodpovídá poli category';
    return '';
}

/** Délkové limity a duplicity uvnitř položky (nezávislé na typu odpovědi). */
function v58qb_validate_text_limits(array $it): string
{
    $prompt = (string)($it['prompt'] ?? '');
    if ($prompt === '' || mb_strlen($prompt) > 240) return 'prompt: prázdný nebo přes 240 znaků';
    $options = $it['options'] ?? null;
    if (!is_array($options)) return 'options musí být pole';
    $norm = array_map(static fn($o): string => mb_strtolower(trim((string)$o)), $options);
    if (count($norm) !== count(array_unique($norm))) return 'duplicitní možnosti uvnitř položky';
    foreach ($options as $o) { if (mb_strlen((string)$o) > 90) return 'option přes 90 znaků'; }
    if (mb_strlen((string)($it['explain'] ?? '')) > 280) return 'explain přes 280 znaků';
    return '';
}

/** Platnost odpovědi podle typu – zrcadlí tg58_quiz_normalize() z teamgames_v58_quiz.php. */
function v58qb_validate_answer(string $type, mixed $answer, array $options): string
{
    switch ($type) {
        case 'single':
            if (!is_string($answer) || $options === [] || !in_array($answer, $options, true)) return 'answer(single) není mezi options';
            return mb_strlen($answer) > 90 ? 'answer(single) přes 90 znaků' : '';
        case 'multi':
            if (!is_array($answer) || $answer === [] || $options === []) return 'answer(multi) prázdný nebo bez options';
            foreach ($answer as $a) { if (!in_array($a, $options, true)) return 'answer(multi) obsahuje hodnotu mimo options'; }
            return '';
        case 'bool':
            return is_bool($answer) ? '' : 'answer(bool) musí být true/false';
        case 'numeric':
            return is_numeric($answer) ? '' : 'answer(numeric) není číslo';
        case 'order':
            if (!is_array($answer) || count($answer) < 2) return 'answer(order) musí mít aspoň 2 prvky';
            if (count($answer) !== count(array_unique(array_map('strval', $answer)))) return 'answer(order) není permutace (duplicitní prvek)';
            foreach ($answer as $a) { if (mb_strlen((string)$a) > 90) return 'answer(order) prvek přes 90 znaků'; }
            return '';
        case 'text':
        case 'command':
            return (is_string($answer) && trim($answer) !== '') ? '' : 'answer(text/command) je prázdný';
        default:
            return 'neznámý typ otázky';
    }
}

/** Zadání pro typy single/text/command nesmí doslovně obsahovat vlastní odpověď. */
function v58qb_answer_leaked(string $type, mixed $answer, string $prompt): bool
{
    if (!in_array($type, ['single', 'text', 'command'], true) || !is_string($answer) || mb_strlen($answer) < 3) return false;
    return mb_strpos(mb_strtolower($prompt), mb_strtolower($answer)) !== false;
}

/** Sloučená validace jedné položky. Vrací '' když je v pořádku, jinak krátký důvod pro FAIL detail. */
function v58qb_validate_item(array $it, string $expectLine, array $expectCats): string
{
    $reason = v58qb_validate_identity($it, $expectLine, $expectCats);
    if ($reason !== '') return $reason;
    $difficulty = $it['difficulty'] ?? null;
    if (!is_int($difficulty) || $difficulty < 1 || $difficulty > 3) return 'difficulty mimo rozsah 1..3';
    $type = (string)($it['type'] ?? '');
    if (!in_array($type, V58QB_TYPES, true)) return 'neplatný type';
    $reason = v58qb_validate_text_limits($it);
    if ($reason !== '') return $reason;
    $reason = v58qb_validate_answer($type, $it['answer'] ?? null, (array)($it['options'] ?? []));
    if ($reason !== '') return $reason;
    $timeS = $it['time_s'] ?? null;
    if (!is_int($timeS) || $timeS < 20 || $timeS > 45) return 'time_s mimo rozsah 20..45';
    if (($it['status'] ?? '') !== 'published') return 'status musí být published';
    if (v58qb_answer_leaked($type, $it['answer'] ?? null, (string)($it['prompt'] ?? ''))) return 'odpověď je prozrazená přímo v zadání';
    if (!v58qb_content_ok((string)($it['prompt'] ?? '')) || !v58qb_content_ok((string)($it['explain'] ?? ''))) return 'nevhodný obsah v prompt/explain';
    foreach ((array)($it['options'] ?? []) as $o) { if (!v58qb_content_ok((string)$o)) return 'nevhodný obsah v možnosti'; }
    return '';
}

// ---------------------------------------------------------------------------
// Vlastní běh auditu
// ---------------------------------------------------------------------------

try {
    // Skutečný obsahový filtr CNT-03 (staví ho paralelně jiný agent) – když existuje, v58qb_content_ok()
    // ho použije přes function_exists místo záložního filtru. Bezpečné require_once (konstanty uvnitř),
    // chráněné proti tomu, že soubor ještě nemusí existovat nebo se souběžně mění.
    $contentLibPath = $root . '/lab_v58_content.php';
    if (is_file($contentLibPath)) {
        try {
            require_once $contentLibPath;
        } catch (Throwable $e) {
            // Nekritické – bez ní audit jen spadne zpět na vlastní jednoduchý filtr.
        }
        if (function_exists('cnt58_check_text')) {
            $probe = cnt58_check_text('Toto je naprosto běžná otázka o počítačové síti.', 'teamgames_v58_bank');
            check(is_array($probe) && array_key_exists('ok', $probe) && $probe['ok'] === true, 'CNT-03 (cnt58_check_text) je načtená a vrací očekávaný tvar výsledku pro neškodný text');
        }
    }
    // Bez CNT-03 se v58qb_content_ok() automaticky přepne na záložní filtr – to není chyba auditu.

    $netPath = $root . '/teamgames_v58_bank_net.php';
    $gfxPath = $root . '/teamgames_v58_bank_gfx.php';
    check(is_file($netPath), 'soubor teamgames_v58_bank_net.php existuje');
    check(is_file($gfxPath), 'soubor teamgames_v58_bank_gfx.php existuje');

    $net1 = is_file($netPath) ? include $netPath : null;
    $gfx1 = is_file($gfxPath) ? include $gfxPath : null;
    check(is_array($net1), 'teamgames_v58_bank_net.php vrací pole (return [...])');
    check(is_array($gfx1), 'teamgames_v58_bank_gfx.php vrací pole (return [...])');

    // Souhrnné i dílčí soubory smí být v jednom requestu includované vícekrát (TG-CORE je includuje
    // znovu po každém tg58_quiz_bank_forget()) – proto nesmí obsahovat funkce/třídy/konstanty.
    $net2 = is_array($net1) ? include $netPath : null;
    $gfx2 = is_array($gfx1) ? include $gfxPath : null;
    check(is_array($net2) && count($net2) === count($net1), 'net bank: opakovaný include je bezpečný (žádné funkce/konstanty)');
    check(is_array($gfx2) && count($gfx2) === count($gfx1), 'gfx bank: opakovaný include je bezpečný (žádné funkce/konstanty)');

    $net = array_values(array_filter((array)$net1, 'is_array'));
    $gfx = array_values(array_filter((array)$gfx1, 'is_array'));
    $all = array_merge($net, $gfx);

    check(count($all) >= 300 && count($all) <= 450, 'celkový počet otázek v cílovém rozmezí (300–450)', 'actual=' . count($all));
    check(count($net) >= 150, 'síťová linie má aspoň 150 otázek (cíl ≈192)', 'actual=' . count($net));
    check(count($gfx) >= 150, 'grafická linie má aspoň 150 otázek (cíl ≈192)', 'actual=' . count($gfx));

    // --- Schéma jednotlivých položek + sběr statistik ---------------------------
    $idSeen = [];
    $idDupExample = '';
    $promptSeen = [];
    $promptDupExample = '';
    $byCat = [];
    $byCatTypes = [];
    $diffByLine = ['networks' => [1 => 0, 2 => 0, 3 => 0], 'graphics' => [1 => 0, 2 => 0, 3 => 0]];
    $schemaFails = 0;
    foreach ([['networks', $net, V58QB_NET_CATEGORIES], ['graphics', $gfx, V58QB_GFX_CATEGORIES]] as [$line, $items, $cats]) {
        foreach ($items as $it) {
            $id = (string)($it['id'] ?? ('(bez id, linie ' . $line . ')'));
            $reason = v58qb_validate_item($it, $line, $cats);
            check($reason === '', 'položka ' . $id . ' splňuje schéma', $reason);
            if ($reason !== '') $schemaFails++;

            $cat = (string)($it['category'] ?? '?');
            $byCat[$cat] = ($byCat[$cat] ?? 0) + 1;
            $byCatTypes[$cat][(string)($it['type'] ?? '?')] = true;
            $d = $it['difficulty'] ?? null;
            if (is_int($d) && isset($diffByLine[$line][$d])) $diffByLine[$line][$d]++;

            $normId = mb_strtolower($id);
            if (isset($idSeen[$normId]) && $idDupExample === '') $idDupExample = $id;
            $idSeen[$normId] = true;

            $normPrompt = mb_strtolower(trim((string)preg_replace('/\s+/u', ' ', (string)($it['prompt'] ?? ''))));
            if ($normPrompt !== '') {
                if (isset($promptSeen[$normPrompt]) && $promptDupExample === '') $promptDupExample = $id;
                $promptSeen[$normPrompt] = true;
            }
        }
    }
    check($schemaFails === 0, 'všech ' . count($all) . ' položek splňuje schéma bez výjimky', 'chybných=' . $schemaFails);
    check($idDupExample === '', 'všechna id v bance jsou jedinečná', 'první duplicita: ' . $idDupExample);
    check($promptDupExample === '', 'všechna zadání (prompt) v bance jsou jedinečná', 'první duplicita u: ' . $promptDupExample);

    // --- Počty na kategorii a pestrost typů --------------------------------------
    foreach (['networks' => V58QB_NET_CATEGORIES, 'graphics' => V58QB_GFX_CATEGORIES] as $line => $cats) {
        foreach ($cats as $cat) {
            $n = $byCat[$cat] ?? 0;
            check($n >= 20 && $n <= 25, 'kategorie ' . $line . '/' . $cat . ': počet otázek 20–25', 'actual=' . $n);
            $typesN = count($byCatTypes[$cat] ?? []);
            check($typesN >= 3, 'kategorie ' . $line . '/' . $cat . ': aspoň 3 různé typy otázek', 'actual=' . $typesN);
        }
    }

    // --- Rozložení obtížnosti cca 40/40/20 % na linii ----------------------------
    foreach ($diffByLine as $line => $counts) {
        $total = array_sum($counts);
        check($total > 0, 'linie ' . $line . ': má nějaké otázky pro výpočet obtížnosti', 'total=0');
        if ($total === 0) continue;
        $p1 = $counts[1] / $total; $p2 = $counts[2] / $total; $p3 = $counts[3] / $total;
        check($p1 >= 0.25 && $p1 <= 0.55, 'linie ' . $line . ': podíl obtížnosti 1 zhruba 40 % (tolerance 25–55 %)', round($p1 * 100) . ' %');
        check($p2 >= 0.25 && $p2 <= 0.55, 'linie ' . $line . ': podíl obtížnosti 2 zhruba 40 % (tolerance 25–55 %)', round($p2 * 100) . ' %');
        check($p3 >= 0.05 && $p3 <= 0.35, 'linie ' . $line . ': podíl obtížnosti 3 zhruba 20 % (tolerance 5–35 %)', round($p3 * 100) . ' %');
    }

    // --- Volitelná zpětná kontrola proti reálnému konzumentovi (TG-CORE), pokud už existuje ---
    $quizPath = $root . '/teamgames_v58_quiz.php';
    $corePath = $root . '/teamgames_v58_core.php';
    if (is_file($corePath) && is_file($quizPath)) {
        try {
            require_once $corePath;
            require_once $quizPath;
        } catch (Throwable $e) {
            // TG-CORE se může stavět souběžně – bonusová kontrola je jen doplňková, nesmí shodit audit.
        }
        if (function_exists('tg58_quiz_normalize')) {
            $rejected = 0;
            foreach ($all as $it) { if (tg58_quiz_normalize($it) === null) $rejected++; }
            check($rejected === 0, 'TG-CORE (tg58_quiz_normalize) přijímá všechny položky beze zbytku', 'odmítnuto=' . $rejected . '/' . count($all));
        }
    }
} catch (Throwable $e) {
    check(false, 'neočekávaná výjimka během auditu', $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
}

echo ($failed === 0 ? 'V58_QUIZBANK_AUDIT_OK' : 'V58_QUIZBANK_AUDIT_FAIL') . ' checks=' . $checks . ' failed=' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
