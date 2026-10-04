<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · unikátní odznaky (inline SVG, bez obrázků, bez náhody za běhu).
 *
 * badge60_svg($id, $meta, $earned, $size) z id odznaku deterministicky odvodí tvar, paletu, vzor,
 * akcent a (podle kategorie) ikonu; rarita určuje rám a sadu tvarů. Stejné id + stejná meta = stejný
 * řetězec SVG. Nezískaný odznak je šedý obrys se zámkem. Žádné gradienty přes id ani clipPath, takže
 * se SVG dá bezpečně vložit vícekrát na jednu stránku. Čistá funkce – nic nečte ani neukládá.
 *
 * $meta: title, text, condition, rarity (common|rare|epic|legendary|mythic), mark, category (volitelně).
 */

const BADGE60_RARITIES = ['common', 'rare', 'epic', 'legendary', 'mythic'];

function badge60_rarity(string $rarity): string
{
    return in_array($rarity, BADGE60_RARITIES, true) ? $rarity : 'common';
}

function badge60_rarity_label(string $rarity): string
{
    return match (badge60_rarity($rarity)) {
        'rare' => tr('Vzácný'),
        'epic' => tr('Epický'),
        'legendary' => tr('Legendární'),
        'mythic' => tr('Mýtický'),
        default => tr('Běžný'),
    };
}

/** Ikony 24×24 (tahy, bez výplně). Klíč = kategorie; kategorie „level“ kreslí číslo, ne ikonu. */
function badge60_icons(): array
{
    return [
        'lab' => 'M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1z M7 10l3 2-3 2 M12 15h5',
        'network' => 'M12 6v4 M12 10l-6 6 M12 10l6 6 M10 4a2 2 0 1 0 4 0a2 2 0 1 0-4 0 M4 18a2 2 0 1 0 4 0a2 2 0 1 0-4 0 M16 18a2 2 0 1 0 4 0a2 2 0 1 0-4 0',
        'graphics' => 'M12 3l5 7-5 11-5-11z M11 10.5a1 1 0 1 0 2 0a1 1 0 1 0-2 0 M12 11.5V21',
        'arena' => 'M8 4h8v5a4 4 0 0 1-8 0z M8 6H5v1a3 3 0 0 0 3 3 M16 6h3v1a3 3 0 0 1-3 3 M12 13v4 M8 20h8 M10 17h4',
        'team' => 'M6 8a3 3 0 1 0 6 0a3 3 0 1 0-6 0 M3 20a6 6 0 0 1 12 0 M15 5.5a3 3 0 0 1 0 5.5 M17 14.3A6 6 0 0 1 21 20',
        'streak' => 'M12 3c1 3 5 5 5 10a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 0-3-1-5 1-9z',
        'feedback' => 'M9 9h6v6a3 3 0 0 1-6 0z M9 5l1.5 2 M15 5l-1.5 2 M5 10l4 1 M19 10l-4 1 M5 18l4-2 M19 18l-4-2 M12 12v6',
        'project' => 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z M3 11h18',
        'shop' => 'M5 8h14l-1 12H6z M9 8V6a3 3 0 0 1 6 0v2',
        'learn' => 'M4 4h7a1 1 0 0 1 1 1v15a3 3 0 0 0-3-3H4z M20 4h-7a1 1 0 0 0-1 1v15a3 3 0 0 1 3-3h5z',
        'xp' => 'M13 2L5 14h6l-1 8 8-12h-6z',
        'exam' => 'M6 3h12v18l-6-3-6 3z M9 10l2 2 4-4',
        'skill' => 'M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0 M8 12a4 4 0 1 0 8 0a4 4 0 1 0-8 0 M11 12a1 1 0 1 0 2 0a1 1 0 1 0-2 0',
        'security' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z M9 12l2 2 4-4',
    ];
}

/** Kategorie odznaku: explicitní meta.category, jinak podle předpony id (pořadí = priorita). */
function badge60_category(string $id, array $meta = []): string
{
    $explicit = (string)($meta['category'] ?? '');
    if ($explicit === 'level' || isset(badge60_icons()[$explicit])) return $explicit;
    $prefixes = [
        'lab_' => 'lab', 'level_' => 'level', 'skill_network' => 'network', 'skill_infrastructure' => 'network',
        'skill_security' => 'security', 'skill_linux' => 'lab', 'skill_design' => 'graphics', 'skill_typography' => 'graphics',
        'skill_uiux' => 'graphics', 'skill_product' => 'graphics', 'skill_' => 'skill', 'project_' => 'project',
        'triple_' => 'project', 'prestige_' => 'exam', 'exam_' => 'exam', 'challenge_' => 'exam', 'diagnostic_' => 'exam',
        'knowledge_' => 'learn', 'course_' => 'learn', 'kb_' => 'learn', 'xp_' => 'xp', 'sim_' => 'lab',
        'active_' => 'streak', 'streak' => 'streak', 'friend_' => 'team', 'team_' => 'team', 'arena' => 'arena',
        'feedback' => 'feedback', 'bug' => 'feedback', 'shop' => 'shop', 'mkt' => 'shop',
    ];
    foreach ($prefixes as $prefix => $category) {
        if (str_starts_with($id, $prefix)) return $category;
    }
    return 'skill';
}

/** Dvojice barev {světlá, tmavá, tmavá ikona?} z brand tokenů Educanet + doplňkové odstíny. */
function badge60_palettes(): array
{
    return [
        ['#00a8b9', '#05616c', false], ['#ec6b10', '#a8440a', false], ['#f3b21f', '#b87a00', true],
        ['#2a8c9b', '#12212b', false], ['#8b6ff0', '#43299a', false], ['#e5557f', '#9c1f47', false],
        ['#2fb47c', '#0b6b49', false], ['#4a8fe0', '#1c4a92', false], ['#f3b21f', '#c4550a', true],
        ['#00a8b9', '#c4550a', false],
    ];
}

function badge60_shape_pool(string $rarity): array
{
    $pools = [
        'common' => ['circle', 'squircle', 'hexagon'],
        'rare' => ['hexagon', 'octagon', 'diamond', 'squircle'],
        'epic' => ['shield', 'seal12', 'diamond', 'octagon'],
        'legendary' => ['shield', 'star6', 'seal12', 'seal16'],
        'mythic' => ['star8', 'seal16', 'star6'],
    ];
    return $pools[$rarity];
}

/** Deterministické rozhodnutí o vzhledu odznaku (bez náhody; jen SHA-256 z id). */
function badge60_variant(string $id, array $meta = []): array
{
    $rarity = badge60_rarity((string)($meta['rarity'] ?? 'common'));
    $hash = hash('sha256', 'badge60|' . $id);
    $pick = static fn(int $offset, int $count): int => hexdec(substr($hash, $offset, 4)) % $count;
    $pool = badge60_shape_pool($rarity);
    $category = badge60_category($id, $meta);
    return [
        'rarity' => $rarity,
        'category' => $category,
        'shape' => $pool[$pick(0, count($pool))],
        'palette' => $pick(4, count(badge60_palettes())),
        'pattern' => $pick(8, 5),
        'accent' => $pick(12, 5),
        'mark' => $category === 'level' ? mb_substr((string)($meta['mark'] ?? ''), 0, 3) : '',
    ];
}

/** Podpis kombinace (pro audit kolizí). */
function badge60_signature(array $variant): string
{
    return implode('|', [$variant['rarity'], $variant['category'], $variant['shape'], $variant['palette'], $variant['pattern'], $variant['accent'], $variant['mark']]);
}

function badge60_num(float $value): string
{
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}

/** Body pravidelného n-úhelníku / hvězdy (inner < outer) ve viewBoxu 100×100. */
function badge60_points(int $count, float $outer, float $inner): string
{
    $points = [];
    $steps = $inner > 0.0 ? $count * 2 : $count;
    for ($i = 0; $i < $steps; $i++) {
        $radius = ($inner > 0.0 && $i % 2 === 1) ? $inner : $outer;
        $angle = deg2rad(-90 + $i * 360 / $steps);
        $points[] = badge60_num(50 + $radius * cos($angle)) . ',' . badge60_num(50 + $radius * sin($angle));
    }
    return implode(' ', $points);
}

/** Geometrie tvaru ve viewBoxu 100×100; $attrs = ostatní atributy prvku (barvy, transformace, id). */
function badge60_shape_element(string $shape, string $attrs): string
{
    $poly = ['hexagon' => [6, 46, 0], 'octagon' => [8, 46, 0], 'star6' => [6, 47, 30], 'star8' => [8, 47, 32], 'seal12' => [12, 47, 41], 'seal16' => [16, 47, 42]];
    if (isset($poly[$shape])) {
        return '<polygon points="' . badge60_points($poly[$shape][0], (float)$poly[$shape][1], (float)$poly[$shape][2]) . '" ' . $attrs . '/>';
    }
    return match ($shape) {
        'squircle' => '<rect x="6" y="6" width="88" height="88" rx="26" ' . $attrs . '/>',
        'diamond' => '<polygon points="50,3 96,50 50,97 4,50" ' . $attrs . '/>',
        'shield' => '<path d="M50 4 88 16v30c0 24-18 40-38 50C30 86 12 70 12 46V16z" ' . $attrs . '/>',
        default => '<circle cx="50" cy="50" r="46" ' . $attrs . '/>',
    };
}

/**
 * Prvek tvaru (škálovaný okolo středu, s posunem dy). Ve spritovém režimu je geometrie definovaná jednou
 * (id b60s-s-<tvar>) a tady jen <use> s barvami/transformací (dědí se do geometrie).
 */
function badge60_shape(string $shape, string $attrs, float $scale = 1.0, float $dy = 0.0): string
{
    // translate(50 50+dy) scale(s) translate(-50 -50) zapsané jako jediná matice (kratší značky ve spritu).
    $transform = ($scale !== 1.0 || $dy !== 0.0)
        ? ' transform="matrix(' . badge60_num($scale) . ' 0 0 ' . badge60_num($scale) . ' ' . badge60_num(50 - 50 * $scale) . ' ' . badge60_num(50 + $dy - 50 * $scale) . ')"' : '';
    $join = $shape === 'diamond' ? 'stroke-linejoin="round" ' : '';
    if (!badge60_sprite_mode()) return badge60_shape_element($shape, $join . $attrs . $transform);
    badge60_define('b60s-s-' . $shape, static fn(): string => badge60_shape_element($shape, 'id="b60s-s-' . $shape . '"'));
    return '<use href="#b60s-s-' . $shape . '" ' . $join . $attrs . $transform . '/>';
}

/** Rám podle rarity (jen tahy okolo těla odznaku). */
function badge60_frame_for(string $shape, string $rarity, bool $earned): string
{
    if (!$earned) return badge60_shape($shape, 'fill="none" stroke="#98a6af" stroke-width="2.5" stroke-dasharray="5 4"', 0.97);
    $ring = static fn(string $color, string $width, float $scale, string $extra = ''): string
        => badge60_shape($shape, 'fill="none" stroke="' . $color . '" stroke-width="' . $width . '" ' . $extra, $scale);
    return match ($rarity) {
        'rare' => $ring('#00a8b9', '3', 0.97),
        'epic' => $ring('#ec6b10', '3', 0.97) . $ring('#ec6b10', '1.2', 0.925),
        'legendary' => $ring('#f3b21f', '4', 0.97) . $ring('#ec6b10', '1.5', 0.925, 'stroke-dasharray="2 3"'),
        'mythic' => $ring('#ec6b10', '4', 0.97) . $ring('#f3b21f', '2', 0.93)
            . '<path fill="#f3b21f" d="M8 2l2 6-2 6-2-6zM92 2l2 6-2 6-2-6zM8 86l2 6-2 6-2-6zM92 86l2 6-2 6-2-6z"/>',
        default => $ring('#93a4ae', '2', 0.97),
    };
}

/** Jemný vzor uvnitř těla (bez clipPath: prstence a tečky uvnitř tvaru). */
function badge60_pattern(int $pattern, string $shape): string
{
    $line = 'fill="none" stroke="#fff" stroke-opacity=".38" stroke-width="1.2"';
    if ($pattern === 1) return badge60_shape($shape, $line, 0.66);
    if ($pattern === 2) return badge60_shape($shape, $line, 0.66) . badge60_shape($shape, $line, 0.58);
    if ($pattern !== 3 && $pattern !== 4) return '';
    $count = $pattern === 3 ? 4 : 6;
    $dots = '';
    for ($i = 0; $i < $count; $i++) {
        $angle = deg2rad(-90 + $i * 360 / $count + ($pattern === 3 ? 45 : 0));
        $dots .= '<circle cx="' . badge60_num(50 + 31 * cos($angle)) . '" cy="' . badge60_num(50 + 31 * sin($angle)) . '" r="1.7" fill="#fff" fill-opacity=".55"/>';
    }
    return $dots;
}

/** Akcent (tečka na obvodu). $stroke = atribut(y) obrysu: inline stroke="#…", ve spritu class="b60a". */
function badge60_accent(int $accent, string $stroke): string
{
    $spots = [1 => [50, 25], 2 => [75, 50], 3 => [50, 75], 4 => [25, 50]];
    if (!isset($spots[$accent])) return '';
    return '<circle cx="' . $spots[$accent][0] . '" cy="' . $spots[$accent][1] . '" r="3" fill="#fff" ' . $stroke . ' stroke-width="1.4"/>';
}

const BADGE60_LOCK_PATH = 'M7 11V8a5 5 0 0 1 10 0v3 M6 11h12v9H6z M12 15v2';

/**
 * Ikona na odznaku: nejdřív tmavší obrys (kontrast bílé ikony na světlém podkladu ≥ 3:1), pak vlastní tah.
 * $outline / $main = atributy tahů (inline stroke="#…", ve spritu class="b60o" / class="b60g"); $outline '' = bez obrysu.
 */
function badge60_glyph_markup(string $path, string $outline, string $main): string
{
    return '<g transform="matrix(1.5 0 0 1.5 32 32)" fill="none" stroke-linecap="round" stroke-linejoin="round">'
        . ($outline !== '' ? '<path d="' . $path . '" stroke-width="3.8" ' . $outline . '/>' : '')
        . '<path d="' . $path . '" stroke-width="2" ' . $main . '/></g>';
}

/** Nápis levelu (číslo/znak) – obrys přes paint-order, aby šel bílý text přečíst i na světlém podkladu. */
function badge60_level_text(string $mark, string $paint): string
{
    $size = [1 => 34, 2 => 30, 3 => 24][mb_strlen($mark)] ?? 24;
    return '<text x="50" y="52" text-anchor="middle" dominant-baseline="central" font-family="system-ui,Segoe UI,Arial,sans-serif" font-weight="800" font-size="' . $size . '" ' . $paint . '>' . e($mark) . '</text>';
}

/** Střed odznaku (inline režim): ikona kategorie, číslo (level) nebo zámek. */
function badge60_glyph(array $variant, bool $earned, string $color, string $outlineColor = ''): string
{
    $main = 'stroke="' . $color . '"';
    $outline = $outlineColor !== '' ? 'stroke="' . $outlineColor . '"' : '';
    if (!$earned) return badge60_glyph_markup(BADGE60_LOCK_PATH, '', $main);
    if ($variant['category'] === 'level' && $variant['mark'] !== '') {
        return badge60_level_text($variant['mark'], 'fill="' . $color . '"' . ($outlineColor !== '' ? ' stroke="' . $outlineColor . '" stroke-width="3.5" paint-order="stroke" stroke-linejoin="round"' : ''));
    }
    return badge60_glyph_markup(badge60_icons()[$variant['category']], $outline, $main);
}

/** Přístupný název: „Titul – rarita, získáno“ / „… zamčeno. Jak získat: podmínka“. */
function badge60_label(array $meta, string $rarity, bool $earned): string
{
    $title = trim((string)($meta['title'] ?? '')) ?: tr('Odznak');
    $rarityLabel = badge60_rarity_label($rarity);
    if ($earned) return tr('{title} – {rarity}, získáno', ['title' => $title, 'rarity' => $rarityLabel]);
    $how = trim((string)($meta['condition'] ?? $meta['text'] ?? ''));
    return tr('{title} – {rarity}, zamčeno', ['title' => $title, 'rarity' => $rarityLabel]) . ($how !== '' ? '. ' . tr('Jak získat: {how}', ['how' => $how]) : '');
}

/**
 * Režim spritu: karty obsahují jen <svg><use href="#b60s-…"></svg>, definice tvarů/rámů/vzorů/ikon se
 * vypíší jednou přes badge60_sprite_flush() (profil ho volá na konci stránky). Barvy dodává třída palety
 * (b60p0–b60p9) a CSS proměnné z assets/profile-v60.css. Mimo profil (dashboard) zůstává samostatné inline SVG.
 */
function badge60_sprite_mode(?bool $on = null): bool
{
    static $mode = false;
    if ($on !== null) $mode = $on;
    return $mode;
}

/** Registr použitých symbolů (id => markup). 'drain' vrátí a vyprázdní (jeden výpis = jedna stránka). */
function badge60_sprite_store(string $op, string $id = '', string $markup = ''): array
{
    static $symbols = [];
    if ($op === 'set') { $symbols[$id] = $markup; return []; }
    if ($op === 'has') return isset($symbols[$id]) ? [1] : [];
    if ($op === 'drain') { $out = $symbols; $symbols = []; return $out; }
    return $symbols;
}

/** Holá definice (např. geometrie tvaru) bez obalu <symbol>; vytvoří se až při prvním použití. */
function badge60_define(string $id, callable $markup): void
{
    if (badge60_sprite_store('has', $id) === []) badge60_sprite_store('set', $id, $markup());
}

/** <use> na symbol; definici (vytvořenou až při prvním použití) zaregistruje. */
function badge60_use(string $id, callable $inner): string
{
    if (badge60_sprite_store('has', $id) === []) {
        badge60_sprite_store('set', $id, '<symbol id="' . $id . '" viewBox="0 0 100 100">' . $inner() . '</symbol>');
    }
    return '<use href="#' . $id . '"/>';
}

/** Skrytý sprite se všemi dosud použitými symboly; prázdný řetězec, pokud se žádný odznak nevykreslil. */
function badge60_sprite_flush(): string
{
    $symbols = badge60_sprite_store('drain');
    if ($symbols === []) return '';
    ksort($symbols);
    return '<svg class="b60-sprite" width="0" height="0" aria-hidden="true" focusable="false">' . implode('', $symbols) . '</svg>';
}

/** Vnitřek odznaku ve spritu: rám, tělo, vzor, akcent, ikona/číslo/zámek – každé jako samostatný symbol. */
function badge60_sprite_body(array $v, bool $earned): string
{
    $shape = (string)$v['shape'];
    if (!$earned) {
        return badge60_use('b60s-l-' . $shape, static fn(): string => badge60_frame_for($shape, 'common', false) . badge60_shape($shape, 'class="b60d"', 0.88) . badge60_shape($shape, 'class="b60l"', 0.77, -1.2) . badge60_glyph_markup(BADGE60_LOCK_PATH, '', 'class="b60g"'));
    }
    $out = badge60_use('b60s-f-' . $shape . '-' . $v['rarity'], static fn(): string => badge60_frame_for($shape, (string)$v['rarity'], true));
    $out .= badge60_use('b60s-b-' . $shape, static fn(): string => badge60_shape($shape, 'class="b60d"', 0.88) . badge60_shape($shape, 'class="b60l"', 0.77, -1.2));
    $pattern = (int)$v['pattern'];
    if ($pattern >= 1 && $pattern <= 4) {
        $pid = $pattern <= 2 ? 'b60s-p-' . $shape . '-' . $pattern : 'b60s-p-' . $pattern;
        $out .= badge60_use($pid, static fn(): string => badge60_pattern($pattern, $shape));
    }
    $accent = (int)$v['accent'];
    if ($accent >= 1 && $accent <= 4) {
        $out .= badge60_use('b60s-a-' . $accent, static fn(): string => badge60_accent($accent, 'class="b60a"'));
    }
    if ($v['category'] === 'level' && $v['mark'] !== '') return $out . badge60_level_text((string)$v['mark'], 'class="b60t"');
    $category = (string)$v['category'];
    return $out . badge60_use('b60s-g-' . $category, static fn(): string => badge60_glyph_markup(badge60_icons()[$category], 'class="b60o"', 'class="b60g"'));
}

/**
 * Kompletní <svg> odznaku. $decorative = true → aria-hidden (název je hned vedle v textu karty).
 * Ve spritovém režimu (profil) jen odkazy <use>; jinak samostatné SVG s vlastními tvary.
 */
function badge60_svg(string $badgeId, array $meta, bool $earned, int $size = 64, bool $decorative = false): string
{
    $size = max(24, min(256, $size));
    $v = badge60_variant($badgeId, $meta);
    $label = badge60_label($meta, $v['rarity'], $earned);
    [$light, $dark, $inkIcon] = badge60_palettes()[$v['palette']];
    $a11y = $decorative ? ' aria-hidden="true"' : ' role="img" aria-label="' . e($label) . '"';
    $sprite = badge60_sprite_mode();
    $out = '<svg class="b60 b60-' . e($v['rarity']) . ($earned ? ' is-earned' : ' is-locked') . ($sprite ? ' b60p' . (int)$v['palette'] : '') . '" viewBox="0 0 100 100" width="' . $size . '" height="' . $size . '"' . $a11y . '>';
    if (!$decorative || !$sprite) $out .= '<title>' . e($label) . '</title>';
    if ($sprite) return $out . badge60_sprite_body($v, $earned) . '</svg>';
    $out .= badge60_frame_for($v['shape'], $v['rarity'], $earned);
    $out .= badge60_shape($v['shape'], 'fill="' . ($earned ? $dark : '#c9d2d8') . '"', 0.88);
    $out .= badge60_shape($v['shape'], 'fill="' . ($earned ? $light : '#eef2f4') . '"', 0.77, -1.2);
    if ($earned) $out .= badge60_pattern((int)$v['pattern'], $v['shape']) . badge60_accent((int)$v['accent'], 'stroke="' . $dark . '"');
    $out .= badge60_glyph($v, $earned, $earned ? ($inkIcon ? '#12212b' : '#ffffff') : '#5d6a74', $earned && !$inkIcon ? $dark : '');
    return $out . '</svg>';
}

/** Zkrácený popis odznaku (stránka Odznaky musí zůstat lehká); celé znění je v učitelských podkladech a po získání. */
const BADGE60_TEXT_MAX = 64;
function badge60_short(string $text, int $max = BADGE60_TEXT_MAX): string
{
    $text = trim($text);
    if (u_strlen($text) <= $max) return $text;
    $cut = u_substr($text, 0, $max - 1);
    $space = strrpos($cut, " ");
    return rtrim(($space !== false && $space > (int)($max * 0.5)) ? substr($cut, 0, $space) : $cut, " ,.;:") . "…";
}

/**
 * Karta odznaku pro mřížku/showcase. $meta navíc: percent (0–100, u zamčených), earned_at (Y-m-d…).
 * Třída v55-badge-bar zůstává kvůli kompatibilitě s auditem v55.
 */
function badge60_card(string $id, array $meta, bool $earned, int $percent = 0): string
{
    $v = badge60_variant($id, $meta);
    $title = (string)($meta['title'] ?? '');
    $text = badge60_short($earned ? (string)($meta['text'] ?? '') : (string)($meta['condition'] ?? $meta['text'] ?? ''));
    $html = '<article class="b60-card ' . ($earned ? 'is-earned' : 'is-locked') . ' rarity-' . e($v['rarity']) . '" data-b60-state="' . ($earned ? 'earned' : 'locked') . '">';
    $html .= '<div class="b60-art">' . badge60_svg($id, $meta, $earned, 72, true) . '</div><div class="b60-body">';
    $html .= '<strong>' . e($title) . '</strong><span class="b60-rarity">' . e(badge60_rarity_label($v['rarity'])) . '</span>';
    if ($text !== '') $html .= '<small>' . ($earned ? '' : e(tr('Jak získat:')) . ' ') . e($text) . '</small>';
    if ($earned) {
        $when = (string)($meta['earned_at'] ?? '');
        $html .= '<span class="b60-state ok">' . e(tr('✓ Získáno')) . ($when !== '' && strtotime($when) ? ' · ' . e(date('j. n. Y', (int)strtotime($when))) : '') . '</span>';
    } else {
        $percent = max(0, min(100, $percent));
        $html .= '<span class="v55-badge-bar b60-bar" role="img" aria-label="' . e(tr('Postup {percent} %', ['percent' => $percent])) . '"><i style="width:' . $percent . '%"></i></span>'
            . '<span class="b60-state">' . e(tr('{percent} %', ['percent' => $percent])) . '</span>';
    }
    return $html . '</div></article>';
}

/**
 * Řádek zamčeného odznaku (sbalený seznam): jen název, rarita a podmínka – bez SVG, ať je stránka lehká.
 * Jedinečný vzhled odznaku se ukáže, až ho žák získá (nebo když je rozpracovaný).
 */
function badge60_row(string $id, array $meta): string
{
    $v = badge60_variant($id, $meta);
    $how = badge60_short((string)($meta['condition'] ?? $meta['text'] ?? ''));
    return '<li class="b60-row rarity-' . e($v['rarity']) . '" data-b60-state="locked"><strong>' . e((string)($meta['title'] ?? '')) . '</strong>'
        . '<span class="b60-rarity">' . e(badge60_rarity_label($v['rarity'])) . '</span>' . ($how !== '' ? '<small>' . e($how) . '</small>' : '') . '</li>';
}

/** Barvy rámečku avataru podle id kosmetiky (klíčová slova, jinak deterministicky z hashe). */
function badge60_frame_colors(string $itemId): array
{
    $palettes = badge60_palettes();
    if (str_contains($itemId, 'gold')) return [$palettes[2][0], $palettes[1][0]];
    if (str_contains($itemId, 'neon')) return [$palettes[0][0], $palettes[6][0]];
    $pick = hexdec(substr(hash('sha256', 'frame60|' . $itemId), 0, 4)) % count($palettes);
    return [$palettes[$pick][0], $palettes[($pick + 3) % count($palettes)][0]];
}
