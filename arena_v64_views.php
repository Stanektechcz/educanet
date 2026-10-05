<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/arena_v64_fair.php';

/**
 * EDUCANET v64 · žákovské šablony férovosti: moje liga, doporučení soupeři, žebříček zlepšení.
 * Ostatním se ukazuje jen liga (nikdy číslo ELO); jména podle soukromí arény (výchozí iniciály).
 * Rozhodnutí o ELO/lize není nikdy součástí známek.
 */

function fair64_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/arena-v64.css?v=64.0')) . '">' . "\n";
}

function fair64_league_hint(string $league): string
{
    return match ($league) {
        'gold' => tr('Jsi mezi nejlepšími ve třídě. Zkus pomoct spolužákům s úlohou.'),
        'silver' => tr('Držíš střed. Každý souboj tě posouvá, výhra i prohra.'),
        default => tr('Začínáš. Soupeři blízko tvé ligy ti dají nejlepší trénink.'),
    };
}

/** Panel „Moje liga“ na profilu žáka (Aréna). */
function fair64_render_league_panel(string $classId, string $studentKey, array $classmateKeys): void
{
    fair64_assets();
    $r = fair64_rating($classId, $studentKey);
    $near = array_slice(fair64_recommend($classId, $studentKey, $classmateKeys), 0, 3);
    $roster = function_exists('arena57_roster') ? arena57_roster($classId) : [];
    echo '<section class="fair64-card" aria-labelledby="fair64-league"><h3 id="fair64-league">' . e(tr('Moje liga')) . '</h3>'
        . '<p class="fair64-league fair64-' . e($r['league']) . '"><strong>' . e(fair64_league_label($r['league'])) . '</strong></p>'
        . '<p>' . e(fair64_league_hint($r['league'])) . '</p>'
        . '<p class="fair64-note">' . e(tr('Liga se každé pololetí začíná znovu a nikdy se nepočítá do známek.')) . '</p>';
    if ($near !== []) {
        echo '<h4>' . e(tr('Vyrovnaní soupeři')) . '</h4><ul class="fair64-list">';
        foreach ($near as $key) echo '<li><a href="?view=profile&amp;student=' . e(rawurlencode((string)$key)) . '">' . e(function_exists('lab57_display_name') ? lab57_display_name((string)($roster[$key]['label'] ?? '')) : '?') . '</a></li>';
        echo '</ul><p class="fair64-note">' . e(tr('Doporučení je jen rada – vyzvat můžeš kohokoli.')) . '</p>';
    }
    echo '</section>';
}

/** Žebříček „osobní zlepšení“ za 14 dní (jen kladné, bez čísel). */
function fair64_render_improvement_panel(string $classId, string $studentKey): void
{
    fair64_assets();
    $rows = fair64_improvement_board($classId, $studentKey);
    echo '<section class="fair64-card" aria-labelledby="fair64-improve"><h3 id="fair64-improve">' . e(tr('Kdo se za 14 dní nejvíc posunul')) . '</h3>';
    if ($rows === []) {
        echo '<p>' . e(tr('Zatím tu nikdo není. Vyřeš úlohu nebo si zahraj souboj a zlepšení se ukáže tady.')) . '</p></section>';
        return;
    }
    echo '<ol class="fair64-list">';
    foreach ($rows as $r) echo '<li' . ($r['me'] ? ' aria-current="true"' : '') . '>' . e($r['name']) . ' <span class="fair64-badge fair64-' . e($r['league']) . '">' . e(fair64_league_label($r['league'])) . '</span></li>';
    echo '</ol><p class="fair64-note">' . e(tr('Počítá se jen zlepšení, ne výchozí úroveň.')) . '</p></section>';
}
