<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v79.1 · přehlednost obsahu – sdílené stavební bloky cockpitu (jen prezentace, nic neukládá).
 *   - ux791_empty_html(): vysvětlený prázdný stav (co tu bude, proč je prázdno, tlačítko co dělat) na komponentě v78 `.ds-empty`,
 *   - dt791_*: tabulka s řazením, filtrem a stránkováním po 50 na serveru (GET ?razeni, ?smer, ?filtr, ?strana – informativní,
 *     registrované v teacher59_get_policies(); data se jen řadí a filtrují v paměti, nic se nezapisuje),
 *   - ind791_bars_html(): ukazatel (pruhy) s textovými popisky a tabulkovou alternativou pro čtečky.
 * Všechny texty česky (cockpit), každý výstup přes e(). Barvy jen tokeny v78 (assets/content-v79-1.css).
 */

const DT791_PER_PAGE = 50;
const DT791_FILTER_MAX = 80;
/** GET parametry tabulky (stejné na každé záložce s tabulkou dt791). */
const DT791_PARAMS = ['razeni', 'smer', 'filtr', 'strana'];
/** Záložky s tabulkou dt791 – pro každou jsou v teacher59_get_policies() záznamy `<tab>|<param>` (informativní, jen ?class= v rozsahu). */
const DT791_TABS = ['prehled', 'schvalovani', 'class_results'];

/**
 * Prázdný stav: nadpis (co tu bude), proč je prázdno a 1–2 akce (odkaz jako tlačítko; první primární).
 * @param list<array{0:string,1:string}> $actions [url (relativní ?…/#…), popisek]
 */
function ux791_empty_html(string $title, string $why, array $actions = [], string $tag = 'h2', string $id = ''): string
{
    $tag = in_array($tag, ['h2', 'h3', 'p'], true) ? $tag : 'h2';
    $btns = [];
    foreach (array_values($actions) as $i => [$url, $label]) {
        $href = ux791_href((string)$url);
        if ($href === '' || $href === '#' || (string)$label === '') continue;
        $btns[] = '<a class="ds-btn ' . ($i === 0 ? 'ds-btn--primary' : 'ds-btn--secondary') . '" href="' . e($href) . '">' . e((string)$label) . '</a>';
    }
    return '<div class="ds78 ux791-empty"' . ($id !== '' ? ' id="' . e($id) . '"' : '') . '><div class="ds-empty" role="status">'
        . '<svg class="ds-empty__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M4 7h16v12H4zM4 7l2-3h12l2 3M9 12h6"/></svg>'
        . '<' . $tag . ' class="ux791-empty__title">' . e($title) . '</' . $tag . '><p>' . e($why) . '</p>'
        . ($btns !== [] ? '<p class="ux791-empty__actions">' . implode(' ', $btns) . '</p>' : '') . '</div></div>';
}

/** Odkaz akce: vnitřní relativní adresa (?… nebo #…, jen bezpečné znaky) projde, jinak safe_url() (http/https); cokoli jiného = prázdný řetězec. */
function ux791_href(string $url): string
{
    if (preg_match('~^[?#][A-Za-z0-9_\-=&%.+:/#]*$~', $url) === 1) return $url;
    return function_exists('safe_url') && preg_match('~^https?://~i', $url) === 1 ? safe_url($url) : '';
}

/** URL záložky (?tab=…&class=…) se zachováním kontextu – pro akce prázdných stavů. */
function ux791_tab_url(string $tab, string $classId = '', array $extra = []): string
{
    return '?' . http_build_query(array_filter(['tab' => $tab, 'class' => $classId] + $extra, static fn($v): bool => $v !== '' && $v !== null));
}

// ---------------------------------------------------------------------------------------------------------------- tabulka

/** Text bez diakritiky a malými písmeny (porovnání filtru a řazení jmen). */
function dt791_fold(string $s): string
{
    static $map = ['á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ů' => 'u',
        'ý' => 'y', 'ž' => 'z', 'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ľ' => 'l', 'ĺ' => 'l', 'ŕ' => 'r', 'і' => 'i', 'ї' => 'i', 'є' => 'e', 'ґ' => 'g'];
    return strtr(mb_strtolower($s, 'UTF-8'), $map);
}

/**
 * Stav tabulky z GET (whitelist): řazení jen podle povoleného sloupce, směr asc|desc, filtr ≤ 80 znaků bez řídicích znaků, strana 1–9999.
 * @param list<string> $sortable
 * @return array{sort:string,dir:string,q:string,page:int,default_sort:string,default_dir:string}
 */
function dt791_state(array $sortable, string $defaultSort, string $defaultDir = 'asc', ?array $get = null): array
{
    $get ??= $_GET;
    $sort = is_string($get['razeni'] ?? null) && in_array($get['razeni'], $sortable, true) ? (string)$get['razeni'] : $defaultSort;
    $dirRaw = is_string($get['smer'] ?? null) ? (string)$get['smer'] : '';
    $dir = in_array($dirRaw, ['asc', 'desc'], true) ? $dirRaw : ($sort === $defaultSort ? $defaultDir : 'asc');
    $q = is_string($get['filtr'] ?? null) ? trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$get['filtr'])) : '';
    $q = mb_substr($q, 0, DT791_FILTER_MAX, 'UTF-8');
    $page = is_string($get['strana'] ?? null) && preg_match('/^\d{1,4}$/', (string)$get['strana']) === 1 ? max(1, (int)$get['strana']) : 1;
    return ['sort' => $sort, 'dir' => $dir, 'q' => $q, 'page' => $page, 'default_sort' => $defaultSort, 'default_dir' => $defaultDir];
}

/**
 * Filtr, řazení a stránka nad řádky v paměti. Řádek = pole s klíči 'sort' (hodnoty sloupců: int|float|string|null) a 'text' (text pro filtr).
 * Null se řadí vždy na konec; při shodě rozhoduje původní pořadí (stabilní).
 * @return array{rows:list<array>,total:int,matched:int,page:int,pages:int,from:int,to:int}
 */
function dt791_apply(array $rows, array $state, int $perPage = DT791_PER_PAGE): array
{
    $total = count($rows);
    $rows = array_values($rows);
    if ($state['q'] !== '') {
        $needle = dt791_fold($state['q']);
        $rows = array_values(array_filter($rows, static fn(array $r): bool => str_contains(dt791_fold((string)($r['text'] ?? '')), $needle)));
    }
    $key = $state['sort'];
    $sign = $state['dir'] === 'desc' ? -1 : 1;
    $indexed = [];
    foreach ($rows as $i => $r) $indexed[] = [$i, $r];
    usort($indexed, static function (array $a, array $b) use ($key, $sign): int {
        $va = $a[1]['sort'][$key] ?? null;
        $vb = $b[1]['sort'][$key] ?? null;
        if ($va === null || $vb === null) return ($va === null) <=> ($vb === null) ?: $a[0] <=> $b[0];
        $cmp = is_string($va) || is_string($vb) ? strnatcmp(dt791_fold((string)$va), dt791_fold((string)$vb)) : ($va <=> $vb);
        return $cmp !== 0 ? $sign * $cmp : $a[0] <=> $b[0];
    });
    $rows = array_column($indexed, 1);
    $matched = count($rows);
    $pages = max(1, (int)ceil($matched / max(1, $perPage)));
    $page = min($state['page'], $pages);
    $slice = array_slice($rows, ($page - 1) * $perPage, $perPage);
    $from = $matched === 0 ? 0 : ($page - 1) * $perPage + 1;
    return ['rows' => $slice, 'total' => $total, 'matched' => $matched, 'page' => $page, 'pages' => $pages, 'from' => $from, 'to' => $from === 0 ? 0 : $from + count($slice) - 1];
}

/** Odkaz se změnou stavu tabulky (výchozí hodnoty se do URL nepíšou; kotva na tabulku). */
function dt791_url(array $base, array $state, array $change, string $anchor = ''): string
{
    $s = array_replace($state, $change);
    $params = $base;
    if ($s['sort'] !== $s['default_sort'] || $s['dir'] !== $s['default_dir']) { $params['razeni'] = $s['sort']; $params['smer'] = $s['dir']; }
    if ($s['q'] !== '') $params['filtr'] = $s['q'];
    if ($s['page'] > 1) $params['strana'] = (string)$s['page'];
    return '?' . http_build_query(array_filter($params, static fn($v): bool => $v !== '' && $v !== null)) . ($anchor !== '' ? '#' . $anchor : '');
}

/**
 * Záhlaví sloupce: řaditelný sloupec = odkaz (aria-sort na aktivním, šipka jen dekorace, směr textem pro čtečky).
 * @param array{key:string,label:string,sort?:bool,num?:bool} $col
 */
function dt791_th(array $col, array $base, array $state, string $anchor): string
{
    if (empty($col['sort'])) return '<th scope="col">' . e($col['label']) . '</th>';
    $active = $state['sort'] === $col['key'];
    $next = $active && $state['dir'] === 'asc' ? 'desc' : 'asc';
    $aria = $active ? ' aria-sort="' . ($state['dir'] === 'asc' ? 'ascending' : 'descending') . '"' : '';
    $arrow = $active ? ($state['dir'] === 'asc' ? '▲' : '▼') : '↕';
    $hint = $active ? ($state['dir'] === 'asc' ? 'seřazeno vzestupně, kliknutím sestupně' : 'seřazeno sestupně, kliknutím vzestupně') : 'seřadit';
    return '<th scope="col"' . $aria . '><a class="dt791-sort" href="' . e(dt791_url($base, $state, ['sort' => $col['key'], 'dir' => $next, 'page' => 1], $anchor)) . '">'
        . e($col['label']) . ' <span aria-hidden="true">' . $arrow . '</span><span class="ds-sr"> – ' . e($hint) . '</span></a></th>';
}

/**
 * Filtr a řazení (GET formulář se zachováním záložky a třídy) + počet výsledků. Bez JS funguje odesláním. Řazení je i tady (výběr sloupce a směru),
 * protože na mobilu (karty) není řádek záhlaví vidět.
 * @param list<array{key:string,label:string,sort?:bool}> $cols
 */
function dt791_controls_html(string $id, string $label, array $base, array $state, array $res, string $placeholder = '', array $cols = []): string
{
    $hidden = '';
    foreach ($base as $k => $v) if ((string)$v !== '') $hidden .= '<input type="hidden" name="' . e((string)$k) . '" value="' . e((string)$v) . '">';
    $sortable = array_values(array_filter($cols, static fn(array $c): bool => !empty($c['sort'])));
    $sortHtml = '';
    if ($sortable !== []) {
        $opts = in_array($state['default_sort'], array_column($sortable, 'key'), true) ? ''
            : '<option value="' . e($state['default_sort']) . '"' . (in_array($state['sort'], array_column($sortable, 'key'), true) ? '' : ' selected') . '>Výchozí pořadí</option>';
        foreach ($sortable as $c) $opts .= '<option value="' . e($c['key']) . '"' . ($state['sort'] === $c['key'] ? ' selected' : '') . '>' . e($c['label']) . '</option>';
        $sortHtml = '<div class="ds-field dt791-sortf"><label class="ds-label" for="' . e($id) . '-s">Řadit podle</label><select class="ds-input" id="' . e($id) . '-s" name="razeni">' . $opts . '</select></div>'
            . '<div class="ds-field dt791-sortf"><label class="ds-label" for="' . e($id) . '-d">Směr</label><select class="ds-input" id="' . e($id) . '-d" name="smer">'
            . '<option value="asc"' . ($state['dir'] === 'asc' ? ' selected' : '') . '>Vzestupně (A–Z, 0–9)</option><option value="desc"' . ($state['dir'] === 'desc' ? ' selected' : '') . '>Sestupně (Z–A, 9–0)</option></select></div>';
    } elseif ($state['sort'] !== $state['default_sort'] || $state['dir'] !== $state['default_dir']) {
        $hidden .= '<input type="hidden" name="razeni" value="' . e($state['sort']) . '"><input type="hidden" name="smer" value="' . e($state['dir']) . '">';
    }
    $count = $res['matched'] === 0 ? 'Nic neodpovídá filtru.' : 'Zobrazeno ' . $res['from'] . '–' . $res['to'] . ' z ' . $res['matched'] . ($res['matched'] !== $res['total'] ? ' (celkem ' . $res['total'] . ')' : '');
    $clear = $state['q'] !== '' ? ' <a class="ds-btn ds-btn--ghost" href="' . e(dt791_url($base, $state, ['q' => '', 'page' => 1], $id)) . '">Zrušit filtr</a>' : '';
    return '<form class="dt791-filter" method="get" action="teacher.php" role="search" aria-label="' . e('Filtr: ' . $label) . '">' . $hidden
        . '<div class="ds-field dt791-field"><label class="ds-label" for="' . e($id) . '-q">Filtrovat</label>'
        . '<input class="ds-input" id="' . e($id) . '-q" type="search" name="filtr" inputmode="search" autocomplete="off" maxlength="' . DT791_FILTER_MAX . '" value="' . e($state['q']) . '"'
        . ($placeholder !== '' ? ' placeholder="' . e($placeholder) . '"' : '') . ' aria-describedby="' . e($id) . '-n"></div>' . $sortHtml
        . '<button class="ds-btn ds-btn--secondary" type="submit">Použít</button>' . $clear
        . '<p class="ds-help dt791-count" id="' . e($id) . '-n" role="status">' . e($count) . '</p></form>';
}

/** Stránkování (jen když je víc stran): Předchozí · čísla stran (okno 5) · Další. */
function dt791_pager_html(array $base, array $state, array $res, string $anchor): string
{
    if ($res['pages'] <= 1) return '';
    $p = $res['page'];
    $link = static fn(int $n, string $text, string $extra = ''): string => '<a class="ds-btn ds-btn--secondary dt791-page" href="' . e(dt791_url($base, $state, ['page' => $n], $anchor)) . '"' . $extra . '>' . $text . '</a>';
    $html = '<nav class="dt791-pager" aria-label="Stránkování"><p class="ds-help">Strana ' . $p . ' z ' . $res['pages'] . '</p><ul>';
    if ($p > 1) $html .= '<li>' . $link($p - 1, 'Předchozí<span class="ds-sr"> strana</span>') . '</li>';
    for ($n = max(1, $p - 2); $n <= min($res['pages'], $p + 2); $n++) {
        $html .= '<li>' . ($n === $p ? '<span class="dt791-page is-current" aria-current="page">' . $n . '</span>' : $link($n, '<span class="ds-sr">Strana </span>' . $n)) . '</li>';
    }
    if ($p < $res['pages']) $html .= '<li>' . $link($p + 1, 'Další<span class="ds-sr"> strana</span>') . '</li>';
    return $html . '</ul></nav>';
}

/**
 * Celá tabulka: filtr, posuvná oblast (md+ lepicí hlavička a první sloupec, xs/sm karty přes data-label), stránkování, prázdný výsledek filtru.
 * @param list<array{key:string,label:string,sort?:bool}> $cols  první sloupec = záhlaví řádku (th scope=row)
 * @param list<array{sort:array,text:string,cells:list<string>,class?:string}> $rows  cells = hotové HTML buněk (už escapované)
 */
function dt791_table_html(string $id, string $caption, array $cols, array $rows, array $base, array $state, string $placeholder = ''): string
{
    $res = dt791_apply($rows, $state);
    $html = '<div class="ds78 dt791" id="' . e($id) . '">' . dt791_controls_html($id, $caption, $base, $state, $res, $placeholder, $cols);
    if ($res['matched'] === 0) {
        return $html . ux791_empty_html('Filtru nic neodpovídá', 'Zkuste kratší nebo jiný text. Filtr hledá ve všech sloupcích tabulky.', [[dt791_url($base, $state, ['q' => '', 'page' => 1], $id), 'Zrušit filtr']], 'p') . '</div>';
    }
    $html .= '<div class="ds-table-wrap dt791-wrap" role="region" tabindex="0" aria-labelledby="' . e($id) . '-cap"><table class="dt791-table"><caption id="' . e($id) . '-cap">' . e($caption)
        . ($res['pages'] > 1 ? '<span class="ds-sr"> – strana ' . $res['page'] . ' z ' . $res['pages'] . '</span>' : '') . '</caption><thead><tr>';
    foreach ($cols as $col) $html .= dt791_th($col, $base, $state, $id);
    $html .= '</tr></thead><tbody>';
    foreach ($res['rows'] as $r) {
        $html .= '<tr' . (!empty($r['class']) ? ' class="' . e((string)$r['class']) . '"' : '') . '>';
        foreach (array_values($cols) as $i => $col) {
            $cell = (string)($r['cells'][$i] ?? '');
            $html .= $i === 0 ? '<th scope="row">' . $cell . '</th>' : '<td data-label="' . e($col['label']) . '">' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table></div>' . dt791_pager_html($base, $state, $res, $id) . '</div>';
}

// ---------------------------------------------------------------------------------------------------------------- ukazatele

/**
 * Ukazatel jako vodorovné pruhy: každý řádek má viditelný popisek i hodnotu textem (barva nenese informaci sama),
 * pruh je jen dekorace (aria-hidden) a pod ním je tabulka pro čtečky se stejnými čísly.
 * @param list<array{0:string,1:int|float,2:string}> $rows [popisek, hodnota, text hodnoty]
 */
function ind791_bars_html(string $caption, array $rows, string $valueLabel = 'Počet', ?float $max = null): string
{
    $max ??= (float)max(1, ...array_map(static fn(array $r): float => (float)$r[1], $rows ?: [[0, 1, '']]));
    $html = '<div class="ds78 ind791"><ul class="ind791-bars" aria-hidden="true">';
    $table = '<table class="ds-sr"><caption>' . e($caption) . '</caption><thead><tr><th scope="col">Položka</th><th scope="col">' . e($valueLabel) . '</th></tr></thead><tbody>';
    foreach ($rows as [$label, $value, $text]) {
        $pct = $max > 0 ? max(0, min(100, (int)round((float)$value / $max * 100))) : 0;
        $html .= '<li><span class="ind791-label">' . e((string)$label) . '</span><span class="ind791-track"><i style="width:' . $pct . '%"></i></span><span class="ind791-value">' . e((string)$text) . '</span></li>';
        $table .= '<tr><th scope="row">' . e((string)$label) . '</th><td>' . e((string)$text) . '</td></tr>';
    }
    return $html . '</ul>' . $table . '</tbody></table></div>';
}
