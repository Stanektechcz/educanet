<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · „AI prompty pro studenta“ v lekci (rozhodnutí školy 8. 10. 2026, bod 8: ponechat, upravit, vylepšit).
 *
 * Zdroj promptů: v77 skládá prompty původní lekce za běhu (ai_prompts_v77.php) ze stejných dat, ze kterých dřív vznikala sekce
 * „## 6. AI prompty pro studenta“ v šablonových lesson kitech (vyřazené ve v77, výstup shodný – hlídá v77_content_audit):
 * název, cíl, témata, úkol – žádný neschválený návrh obsahu ani odpovědi testů. Lekce se schváleným obsahem (L1–L4, L17–L28)
 * berou prompty z obsahu (ai_prompts_v76.php).
 *   aip721_prompts()               – prompty lekce, cache za požadavek; aip721_parse() = parser sekce v Markdownu (reporty, testy),
 *   aip721_render_student_block()  – žák: <details> „Procvič si s AI (dobrovolné)“ s upozorněním na soukromí (tr(), en/uk)
 *                                    a prompty (výukový obsah česky, edu_content_lang_attr), bez JS a bez odkazů na AI služby,
 *   aip721_teacher_preview_html()  – učitel: náhled v záložce Schvalování lekcí (česky).
 * Každý výstup přes e().
 */

const AIP721_SECTION = '## 6. AI prompty pro studenta';
const AIP721_MAX_PROMPTS = 8;
const AIP721_MAX_LEN = 1200;

/**
 * Prompty ze sekce 6 v Markdownu (čistá funkce nad textem; formát dřívějších lesson kitů). @return list<array{title:string,text:string}>
 */
function aip721_parse(string $markdown): array
{
    $start = strpos($markdown, AIP721_SECTION);
    if ($start === false) return [];
    $rest = substr($markdown, $start + strlen(AIP721_SECTION));
    $end = preg_match('/\n## /', $rest, $m, PREG_OFFSET_CAPTURE) === 1 ? (int)$m[0][1] : strlen($rest);
    $section = substr($rest, 0, $end);
    preg_match_all('/^###\s+(.+?)\s*\n+```text\n(.*?)\n```/msu', $section, $all, PREG_SET_ORDER);
    $out = [];
    foreach ($all as $row) {
        $title = trim((string)preg_replace('/^\d+\.\s*/u', '', trim($row[1])));
        $text = trim($row[2]);
        if ($title === '' || $text === '' || mb_strlen($text) > AIP721_MAX_LEN) continue;
        $out[] = ['title' => $title, 'text' => $text];
        if (count($out) >= AIP721_MAX_PROMPTS) break;
    }
    return $out;
}

/**
 * Prompty lekce (cache za požadavek). v76/v77: lekce L1–L4 a L17–L28 se schváleným obsahem berou prompty z tohoto obsahu
 * (ai_prompts_v76.php); $draft = true (náhled učitele) z návrhu. Jinak prompty původní lekce (ai_prompts_v77.php).
 * @return list<array{title:string,text:string}>
 */
function aip721_prompts(string $classId, int $number, bool $draft = false): array
{
    static $memo = [];
    $key = $classId . '#' . $number . ($draft ? '#d' : '');
    if (isset($memo[$key])) return $memo[$key];
    if (!function_exists('aip76_lesson_in_scope')) require_once __DIR__ . '/ai_prompts_v76.php';
    if (aip76_lesson_in_scope($number)) {
        $fromContent = aip76_prompts_for($classId, $number, $draft);
        if ($fromContent !== null) return $memo[$key] = $fromContent;
    }
    if (!function_exists('aip77_original_prompts')) require_once __DIR__ . '/ai_prompts_v77.php';
    return $memo[$key] = aip77_original_prompts($classId, $number);
}

/** Upozornění pro žáka (UI texty přes tr(), překlad en/uk v doméně lesson_v72_1). @return list<string> */
function aip721_privacy_lines(): array
{
    return [
        tr('Použij jen AI nástroj, který povolila škola nebo tvůj učitel.'),
        tr('Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.'),
        tr('Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.'),
        tr('Výstup AI vždy ověř – v programu, v simulátoru nebo u učitele. AI se plete i s jistotou.'),
        tr('Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil sám.'),
    ];
}

/** Žákovský blok v lekci (pod exit ticketem). Nic nevypíše, pokud lekce prompty nemá. */
function aip721_render_student_block(string $classId, int $lessonNo): void
{
    if (!defined('LC72_LINK_AI_PROMPTS') || LC72_LINK_AI_PROMPTS !== true) return;
    $prompts = aip721_prompts($classId, $lessonNo);
    if ($prompts === []) return;
    $lang = function_exists('edu_content_lang_attr') ? edu_content_lang_attr() : '';
    $css = 'assets/lesson-v72.css?v=72.1';
    echo '<link rel="stylesheet" href="' . e(function_exists('asset_url') ? asset_url($css) : $css) . '">';   // i u lekce bez schváleného bloku v72
    echo '<details class="lx72-card lx72-ai" id="ai-prompty"><summary><strong>' . e(tr('Procvič si s AI (dobrovolné)')) . '</strong></summary>'
        . '<p class="lx72-lead">' . e(tr('Než začneš:')) . '</p><ul class="lx72-list">';
    foreach (aip721_privacy_lines() as $line) echo '<li>' . e($line) . '</li>';
    echo '</ul><p class="lx72-muted">' . e(tr('Prompt označ a zkopíruj do AI nástroje. Prompty jsou česky – AI ti může odpovídat i v jiném jazyce, když ji o to požádáš.')) . '</p>';
    foreach ($prompts as $i => $p) {
        echo '<h3 class="lx72-ai-title"' . $lang . '>' . e(($i + 1) . '. ' . $p['title']) . '</h3>'
            . '<pre class="lx72-prompt" tabindex="0"' . $lang . '>' . e($p['text']) . '</pre>';
    }
    echo '</details>';
}

/** Náhled pro učitele (Schvalování lekcí, česky). */
function aip721_teacher_preview_html(string $classId, int $n): string
{
    $prompts = aip721_prompts($classId, $n, true);   // v76/v77: u L1–L4 a L17–L28 prompty z návrhu, které žák dostane po schválení
    if ($prompts === []) return '';
    $wave2 = function_exists('aip76_lesson_in_scope') && aip76_lesson_in_scope($n) && aip76_prompts_for($classId, $n, true) !== null;
    $html = '<h3>AI prompty pro studenta</h3><p class="c70-muted">Žák je vidí v lekci jako dobrovolné procvičení s upozorněním na soukromí. '
        . ($wave2 ? 'Prompty se skládají z tohoto návrhu (název, cíl, kritéria, úkoly, glosář) – žák je uvidí až po schválení; do té doby má prompty z původní lekce.</p>'
            : 'Nejsou součástí schvalovaného obsahu (bez odpovědí, z názvu, cíle a témat lekce).</p>')
        . '<details class="c71-steps"><summary>Zobrazit ' . count($prompts) . ' promptů</summary><ol class="c71-list">';
    foreach ($prompts as $p) $html .= '<li><strong>' . e($p['title']) . '</strong><br><span class="c70-muted">' . e($p['text']) . '</span></li>';
    return $html . '</ol></details>';
}
