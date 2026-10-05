<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · žákovská záložka „Kompetence“ v profilu (jen vlastní profil, jen pilotní třída).
 *
 * Mapa „Umím / Učím se / Zatím ne“. Stav je vždy i textem a ikonou (ne jen barvou). Žák vidí jen sebe a jen
 * to, z čeho se skládá jeho stav (typy zdrojů a počet důkazů) – žádné pořadí ani srovnání se spolužáky.
 * Texty přes tr() (doména competency); názvy kompetencí z katalogu se překládají přes comp62_t_label().
 */

require_once __DIR__ . '/competencies_v62.php';

function comp62_student_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/competency-v62.css?v=62.0')) . '">' . "\n";
}

/** Názvy kompetencí pro žáka (stejné řetězce jako comp62_catalog(); hlídá to audit v62). @return array<string,string> */
function comp62_label_msgids(): array
{
    return [
        'net_addressing' => trm('Umím určit síť, masku a adresu zařízení'),
        'net_dns_dhcp' => trm('Umím vysvětlit DNS a DHCP a ověřit je v praxi'),
        'net_diagnose' => trm('Umím krok za krokem diagnostikovat síťový problém'),
        'net_services' => trm('Umím rozlišit porty a služby a ověřit, co poslouchá'),
        'net_security' => trm('Umím popsat základy zabezpečení sítě a firewallu'),
        'lnx_navigation' => trm('Umím se pohybovat v shellu a pracovat se soubory'),
        'lnx_text' => trm('Umím filtrovat a zpracovat text v příkazové řádce'),
        'lnx_users' => trm('Umím spravovat uživatele a oprávnění'),
        'lnx_services' => trm('Umím spravovat služby a číst systémové logy'),
        'lnx_ssh' => trm('Umím se bezpečně připojit přes SSH a klíče'),
        'lnx_automation' => trm('Umím automatizovat úlohy skriptem a cronem'),
        'web_html_structure' => trm('Umím postavit sémantickou kostru webové stránky'),
        'web_css_layout' => trm('Umím rozmístit prvky pomocí CSS a přizpůsobit stránku úzkému displeji'),
        'web_a11y' => trm('Umím posoudit přístupnost webu a opravit běžné chyby'),
        'web_ux' => trm('Umím navrhnout srozumitelné ovládání a text výzvy k akci'),
        'gfx_color_contrast' => trm('Umím zvolit barvy a ověřit kontrast textu'),
        'gfx_typography' => trm('Umím zvolit a skloubit písma pro čitelný text'),
        'gfx_formats' => trm('Umím vybrat správný grafický formát a export'),
        'gfx_composition' => trm('Umím vystavět kompozici s jasnou hierarchií'),
    ];
}

function comp62_t_label(string $id, string $fallback): string
{
    $msgid = comp62_label_msgids()[$id] ?? null;
    return $msgid !== null ? tr($msgid) : $fallback;
}

function comp62_t_source(string $source): string
{
    return [
        'test' => tr('test'), 'project' => tr('projekt'), 'lab' => tr('lab'),
        'game' => tr('hra'), 'arena' => tr('aréna'), 'lesson' => tr('lekce'),
    ][$source] ?? $source;
}

function comp62_t_level(int $level): string
{
    return [1 => tr('pamatuje'), 2 => tr('použije'), 3 => tr('analyzuje'), 4 => tr('tvoří')][$level] ?? tr('pamatuje');
}

/** @return array{icon:string,text:string} ikona a text stavu */
function comp62_state_view(string $state): array
{
    return [
        'upevneno' => ['icon' => '✓✓', 'text' => tr('Upevněno')],
        'zvladnuto' => ['icon' => '✓', 'text' => tr('Zvládnuto')],
        'rozpracovano' => ['icon' => '◐', 'text' => tr('Rozpracováno')],
    ][$state] ?? ['icon' => '○', 'text' => tr('Zatím neověřeno')];
}

/** Skupina mapy podle stavu: umim / ucim / zatim. */
function comp62_group_of(string $state): string
{
    return in_array($state, ['zvladnuto', 'upevneno'], true) ? 'umim' : ($state === 'rozpracovano' ? 'ucim' : 'zatim');
}

function comp62_render_item(array $c, array $info): string
{
    $view = comp62_state_view((string)$info['state']);
    $meta = tr('Úroveň: {level}', ['level' => comp62_t_level((int)$c['level'])]);
    if ((int)$info['n'] > 0) {
        $sources = implode(', ', array_map('comp62_t_source', (array)$info['sources']));
        $meta .= ' · ' . tr('Důkazů: {n} ({sources})', ['n' => (int)$info['n'], 'sources' => $sources]);
    }
    return '<li class="c62-item"><span class="c62-icon c62-s-' . e((string)$info['state']) . '" aria-hidden="true">' . e($view['icon']) . '</span>'
        . '<span><span class="c62-name">' . e(comp62_t_label((string)$c['id'], (string)$c['label'])) . '</span>'
        . '<span class="c62-meta"><span class="c62-state c62-s-' . e((string)$info['state']) . '">' . e($view['text']) . '</span> · ' . e($meta) . '</span></span></li>';
}

function comp62_render_group(string $id, string $title, string $items): string
{
    return '<section class="c62-group" aria-labelledby="c62-g-' . e($id) . '"><h3 id="c62-g-' . e($id) . '">' . e($title) . '</h3>'
        . ($items !== '' ? '<ul class="c62-list">' . $items . '</ul>' : '<p class="c62-empty">' . e(tr('Zatím tu nic není.')) . '</p>') . '</section>';
}

/** Vykreslí záložku. Mimo pilotní třídu nic (volající to ošetřuje taky). */
function comp62_render_student_tab(string $classId, string $studentKey): void
{
    $subject = comp62_subject_for_class($classId);
    if (!comp62_enabled_for_class($classId) || $subject === null) return;
    require_once __DIR__ . '/evidence_v62.php';
    require_once __DIR__ . '/evidence_v62_adapters.php';
    require_once __DIR__ . '/mastery_v62.php';
    comp62_student_assets();
    if (function_exists('grow67_assets')) grow67_assets(); // v67: pruhy oblastí (SVG)
    ev62_sync_student($classId, $studentKey, false, true); // GET: jen levné zdroje, plná sync = cron/comp62_sync
    $mastery = m62_student($classId, $studentKey);
    require_once __DIR__ . '/challenges_v64.php';
    if ($mastery['id'] !== null) ch64_award_competency_badges((string)$mastery['id'], (array)$mastery['map'], time()); // v64: odznaky jen za milníky kompetencí
    $items = ['umim' => '', 'ucim' => '', 'zatim' => ''];
    foreach (comp62_competencies($subject) as $id => $c) {
        $info = $mastery['map'][$id] ?? ['state' => 'neovereno', 'n' => 0, 'sources' => []];
        $items[comp62_group_of((string)$info['state'])] .= comp62_render_item($c, $info);
    }
    $total = count(comp62_competencies($subject));
    $known = substr_count($items['umim'], '<li ');
    $gradingLink = '';
    if (is_file(__DIR__ . '/grading_v66_views.php')) { // v66: odkaz na „Moje hodnocení“ jen ve třídách se zapnutým návrhem hodnocení
        require_once __DIR__ . '/grading_v66_views.php';
        $gradingLink = g66v_link_html($classId);
    }
    echo '<div class="c62-wrap"><p class="c62-intro">' . e(tr('Mapa ukazuje, co už umíš podle testů, lekcí a úloh v labu. Hra sama zvládnutí nedá, je to jen trénink. Mapu vidíš jen ty.')) . '</p>'
        . '<ul class="c62-summary"><li>' . e(tr('Umím {n} z {total} kompetencí', ['n' => $known, 'total' => $total])) . '</li></ul>'
        . (function_exists('grow67_areas_html') ? grow67_areas_html($subject, (array)$mastery['map']) : '')
        . comp62_render_group('umim', tr('Umím'), $items['umim'])
        . comp62_render_group('ucim', tr('Učím se'), $items['ucim'])
        . comp62_render_group('zatim', tr('Zatím ne'), $items['zatim'])
        . ch64_render_badges($classId, $studentKey)
        . $gradingLink
        . '</div>';
}
