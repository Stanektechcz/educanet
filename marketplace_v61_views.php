<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Obchod – vykreslení: náhled kosmetiky na profilu, oblíbené položky, sezónní nabídka.
 * Volá je marketplace_v60_views.php. Jen HTML (vše přes e()); náhled nic nezapisuje.
 */

require_once __DIR__ . '/marketplace_v61.php';
require_once __DIR__ . '/motivation_v61.php';
require_once __DIR__ . '/badges_v60.php';

/** Žák vidí jen oblíbené (?oblibene=1 – whitelist hodnoty). */
function mkt61_only_favorites(): bool
{
    return ($_GET['oblibene'] ?? '') === '1';
}

/** Rozdělí nabídku na sezónní (s platností od–do) a stálou; při filtru ?oblibene=1 nechá jen oblíbené. @return array{0:array,1:array} */
function mkt61_split_offer(array $items, array $favorites): array
{
    $seasonal = $regular = [];
    foreach ($items as $id => $item) {
        if (mkt61_only_favorites() && !in_array((string)$id, $favorites, true)) continue;
        if (mkt61_is_seasonal($item)) { $seasonal[$id] = $item; } else { $regular[$id] = $item; }
    }
    return [$seasonal, $regular];
}

/** Lišta nad nabídkou: všechny / oblíbené (odkazy, aktuální stav přes aria-current). */
function mkt61_toolbar(int $favoriteCount): string
{
    $only = mkt61_only_favorites();
    return '<nav class="mot61-btn-row" aria-label="' . e(tr('Filtr nabídky')) . '">'
        . '<a class="ui-btn ui-btn--' . ($only ? 'secondary' : 'primary') . '" href="?view=obchod"' . ($only ? '' : ' aria-current="page"') . '>' . e(tr('Všechno')) . '</a>'
        . '<a class="ui-btn ui-btn--' . ($only ? 'primary' : 'secondary') . '" href="?view=obchod&amp;oblibene=1"' . ($only ? ' aria-current="page"' : '') . '>' . e(tr('Oblíbené ({n})', ['n' => $favoriteCount])) . '</a></nav>';
}

/** Tlačítko oblíbené (POST s CSRF) a odkaz na náhled kosmetiky u karty položky. */
function mkt61_item_actions(string $id, array $item, array $favorites): string
{
    $isFav = in_array($id, $favorites, true);
    $name = (string)($item['title'] ?? '');
    $html = '<form method="post" class="mot61-fav-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="mot61_fav_toggle">'
        . '<input type="hidden" name="item_id" value="' . e($id) . '">' . (mkt61_only_favorites() ? '<input type="hidden" name="only_fav" value="1">' : '') . '<button class="ui-btn ui-btn--quiet mot61-fav" type="submit" aria-pressed="' . ($isFav ? 'true' : 'false') . '">'
        . e($isFav ? tr('Oblíbené ✓') : tr('Do oblíbených')) . '<span class="mot61-sr"> ' . e($name) . '</span></button></form>';
    if ((string)($item['type'] ?? '') === 'cosmetic') {
        $html .= ' <a class="ui-btn ui-btn--quiet" href="?view=obchod&amp;nahled=' . e(rawurlencode($id)) . '#nahled">' . e(tr('Náhled')) . '<span class="mot61-sr"> ' . e($name) . '</span></a>';
    }
    return $html;
}

/** Štítek sezónní platnosti („do 31. 1. 2027“). */
function mkt61_season_note(array $item): string
{
    $to = mkt61_date($item['season_to'] ?? '');
    $from = mkt61_date($item['season_from'] ?? '');
    if ($to !== '') return '<span class="mot61-season-note">' . e(tr('Sezónní · do {date}', ['date' => edu_date(mot61_day_ts($to))])) . '</span>';
    return $from !== '' ? '<span class="mot61-season-note">' . e(tr('Sezónní · od {date}', ['date' => edu_date(mot61_day_ts($from))])) . '</span>' : '';
}

/** Náhled kosmetiky na vlastním profilu (avatar s rámečkem / titulkem). Nic nekupuje ani nezapisuje. */
function mkt61_render_preview(string $classId, string $studentKey): void
{
    $item = mkt61_preview_item($classId, $_GET['nahled'] ?? null);
    if ($item === null) return;
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/profile-v60.css?v=60.2')) . '">' . "\n";
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    $current = mkt60_cosmetics($classId, $studentKey);
    $frameId = (string)$item['slot'] === 'frame' ? (string)$item['id'] : (string)($current['frame'] ?? '');
    $titleId = (string)$item['slot'] === 'title' ? (string)$item['id'] : (string)($current['title'] ?? '');
    $titleItem = $titleId !== '' ? mkt60_item($titleId) : null;
    $colors = $frameId !== '' ? badge60_frame_colors($frameId) : [];
    $style = $colors ? ' style="--p60-fa: ' . e($colors[0]) . '; --p60-fb: ' . e($colors[1]) . '"' : '';
    echo '<section class="mot61-card ui-card" id="nahled" tabindex="-1" aria-labelledby="mot61-preview-title"><header class="ui-card-head"><div><p class="ui-eyebrow">' . e(tr('Náhled')) . '</p>'
        . '<h2 id="mot61-preview-title">' . e(tr('Takhle by to vypadalo na tvém profilu')) . '</h2></div><a class="ui-link" href="?view=obchod">' . e(tr('Zavřít náhled')) . '</a></header>'
        . '<div class="mot61-preview"><span class="p60-avatar-frame' . ($colors ? ' has-frame' : '') . '"' . $style . '><span class="p60-avatar xl" aria-hidden="true">' . e(u_substr($label !== '' ? $label : '?', 0, 1)) . '</span></span>'
        . '<div><p class="mot61-preview-name">' . e($label) . '</p>' . ($titleItem !== null ? '<p class="mot61-preview-title">' . e((string)($titleItem['title'] ?? '')) . '</p>' : '')
        . '<p class="mot61-foot">' . e(tr('{title} · {n} bodů. Náhled nic nekupuje.', ['title' => (string)$item['title'], 'n' => (int)$item['price']])) . '</p></div></div></section>';
}
