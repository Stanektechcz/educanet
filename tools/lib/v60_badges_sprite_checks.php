<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60.2 · kontroly SVG spritu odznaků (volá tools/v60_badges_audit.php): jednoznačnost vůči inline podobě,
 * každý odkaz <use> má právě jednu definici, sprite se vypíše jednou, velikost, kontrast ikon a shoda palet s CSS.
 * @param Closure $check kontrola z audit_checker()
 * @param array<string,array> $all všechny odznaky id => meta
 */
function v60_badges_sprite_checks(Closure $check, string $root, array $all): void
{
    badge60_sprite_store('drain');
    $inlineHtml = '';
    $spriteHtml = '';
    $inlineGroups = [];
    $spriteGroups = [];
    foreach ($all as $id => $meta) {
        badge60_sprite_mode(false);
        $inline = badge60_svg((string)$id, $meta, true, 64, true);
        badge60_sprite_mode(true);
        $sprite = badge60_svg((string)$id, $meta, true, 64, true) . badge60_svg((string)$id, $meta, false, 64, true);
        $inlineHtml .= $inline . badge60_svg((string)$id, $meta, false, 64, true);
        $spriteHtml .= $sprite;
        $inlineGroups[$inline][] = $id;
        $spriteGroups[badge60_svg((string)$id, $meta, true, 64, true)][] = $id;
    }
    $flush = badge60_sprite_flush();
    $second = badge60_sprite_flush();
    badge60_sprite_mode(false);

    $check('sprite: odkazy <use> jsou ve spritovém režimu (žádné vlastní tvary v kartě)', str_contains($spriteHtml, '<use href="#b60s-') && !preg_match('~<(polygon|path|rect|circle)\b~', $spriteHtml));
    $check('sprite: unikátnost (' . count($spriteGroups) . ' různých SVG) je stejná jako u inline podoby (' . count($inlineGroups) . ')', count($spriteGroups) === count($inlineGroups));
    preg_match_all('~<use href="#(b60s-[a-z0-9-]+)"~', $spriteHtml . $flush, $refs);
    preg_match_all('~<(?:symbol|polygon|path|rect|circle) [^>]*id="(b60s-[a-z0-9-]+)"~', $flush, $defs);
    $missing = array_diff(array_unique($refs[1]), $defs[1]);
    $check('sprite: každý odkaz #b60s-… má definici (chybí ' . count($missing) . ')', $missing === []);
    $check('sprite: žádné id není definováno dvakrát', count($defs[1]) === count(array_unique($defs[1])));
    $check('sprite: druhý flush je prázdný (vypíše se jednou za stránku)', $second === '' && str_contains($flush, 'class="b60-sprite"') && str_contains($flush, 'aria-hidden="true"'));
    $check('sprite: karty jsou výrazně menší než inline SVG (' . strlen($spriteHtml) . ' + ' . strlen($flush) . ' vs ' . strlen($inlineHtml) . ' B)', strlen($spriteHtml) + strlen($flush) < strlen($inlineHtml) * 0.6);
    $check('sprite: výpis bez odznaků je prázdný řetězec', badge60_sprite_flush() === '');

    $css = (string)file_get_contents($root . '/assets/profile-v60.css');
    $paletteOk = true;
    $contrastOk = true;
    foreach (badge60_palettes() as $i => [$light, $dark, $ink]) {
        $paletteOk = $paletteOk && preg_match('~' . ($i === 0 ? '\.b60' : '\.b60p' . $i) . '\{[^}]*' . preg_quote($light, '~') . '~i', $css) === 1 && preg_match('~' . ($i === 0 ? '\.b60' : '\.b60p' . $i) . '\{[^}]*' . preg_quote($dark, '~') . '~i', $css) === 1;
        $contrastOk = $contrastOk && (audit_contrast_ratio($ink ? '#12212b' : '#ffffff', $ink ? $light : $dark) ?? 0) >= 3.0;
    }
    $check('paleta každého odznaku (10) je v CSS (.b60pN) s toutéž světlou i tmavou barvou', $paletteOk);
    $check('ikona na odznaku má ≥ 3 : 1 proti obrysu/podkladu (WCAG 1.4.11) u všech palet', $contrastOk);
    $check('ikona zámku na zamčeném odznaku ≥ 3 : 1', (audit_contrast_ratio('#5d6a74', '#eef2f4') ?? 0) >= 3.0);
}
