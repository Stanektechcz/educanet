<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Portfolio žáka: výběr prací, reflexe, náhled pro tisk a lokální export HTML.
 * Export je samostatný soubor (attachment): inline CSS, bez JS, bez odkazů a bez externích zdrojů, bez soukromé poznámky učitele a peer textů.
 * Portfolio se nesdílí odkazem – existuje jen stažení a tisk.
 */

function port65v_render_page(string $classId, string $studentKey, string $flash): void
{
    p65v_assets();
    $candidates = port65_candidates($classId, $studentKey);
    $sel = port65_selection(port65_student_id($classId, $studentKey), $candidates);
    echo '<div class="p65"><section class="p65-card p65-noprint"><div class="eyebrow">' . e(tr('Portfolio')) . '</div><h1>' . e(tr('Moje portfolio')) . '</h1>'
        . '<p>' . e(tr('Vyber práce, ke kterým jsi hrdý/á, a ke každé napiš krátkou reflexi. Portfolio je jen tvoje a nesdílí se odkazem.')) . '</p>'
        . '<p class="p65-inline"><a class="btn secondary" href="?view=projekt65">' . e(tr('Moje projekty')) . '</a>'
        . '<a class="btn primary" href="?view=portfolio_export">' . e(tr('Stáhnout jako HTML')) . '</a>'
        . '<button type="button" class="btn secondary p65-print" data-p65-print>' . e(tr('Vytisknout nebo uložit jako PDF')) . '</button></p></section>';
    if ($flash !== '') echo '<div class="notice" role="status">' . e($flash) . '</div>';
    if ($candidates === []) echo ui67_empty_state(tr('Zatím nemáš žádnou ohodnocenou práci, kterou by šlo zařadit.'), tr('Až učitel ohodnotí tvůj projekt, můžeš ho sem zařadit a připsat k němu reflexi.'), '?view=projekt65', tr('Průběh projektů'));
    echo '<section class="p65-grid p65-noprint" aria-label="' . e(tr('Výběr prací')) . '">';
    foreach ($candidates as $c) port65v_render_choice($c, $sel[$c['key']]);
    echo '</section>';
    $model = port65_view_model($classId, $studentKey);
    echo '<section class="p65-card" aria-labelledby="p65-prev"><h2 id="p65-prev">' . e(tr('Náhled portfolia')) . '</h2>';
    if ($model === []) echo '<p>' . e(tr('Zatím nemáš nic vybráno.')) . '</p>';
    foreach ($model as $item) echo port65v_item_html($item, true);
    echo '</section></div>';
}

function port65v_render_choice(array $c, array $state): void
{
    $inner = '<input type="hidden" name="key" value="' . e((string)$c['key']) . '">'
        . '<label><input type="checkbox" name="selected" value="1"' . (!empty($state['selected']) ? ' checked' : '') . '> ' . e(tr('Zařadit do portfolia')) . '</label>'
        . p65v_textarea('reflection', tr('Krátká reflexe: co jsem se naučil/a a co bych příště udělal/a jinak'), PORT65_REFLECTION_MAX, (string)$state['reflection'], false)
        . '<button class="btn primary" type="submit">' . e(tr('Uložit')) . '</button>';
    echo '<article class="p65-card"><h3>' . e((string)$c['title']) . '</h3><p><span class="p65-badge">' . e((string)$c['type'] === 'featured' ? tr('Featured projekt') : tr('Ohodnocený projekt')) . '</span></p>'
        . p65v_form('proj65_s_portfolio_save', $inner) . '</article>';
}

/** HTML jedné položky (stránka i export). Žádné odkazy, žádné peer texty, žádná soukromá poznámka. */
function port65v_item_html(array $item, bool $onPage): string
{
    $html = '<article class="' . ($onPage ? 'p65-pf-item' : 'item') . '"><h3>' . e((string)$item['title']) . '</h3>';
    foreach ((array)($item['rubric']['criteria'] ?? []) as $c) {
        $lvl = (int)(($item['levels'] ?? [])[$c['id']] ?? 0);
        if ($lvl < 1) continue;
        $html .= '<div class="row"><span' . edu_content_lang_attr() . '>' . e((string)$c['title']) . '</span> <strong>' . $lvl . ' · ' . e(p65v_level_label($lvl)) . '</strong></div>';
    }
    if ((string)$item['strengths'] !== '') $html .= '<p><strong>' . e(tr('Silné stránky')) . ':</strong> <span' . edu_content_lang_attr() . '>' . e((string)$item['strengths']) . '</span></p>';
    if ((string)$item['next_step'] !== '') $html .= '<p><strong>' . e(tr('Další krok')) . ':</strong> <span' . edu_content_lang_attr() . '>' . e((string)$item['next_step']) . '</span></p>';
    if (!empty($item['skills'])) $html .= '<p>' . e(implode(', ', array_map('strval', (array)$item['skills']))) . '</p>';
    if ((string)$item['reflection'] !== '') $html .= '<p><strong>' . e(tr('Reflexe')) . ':</strong> ' . e((string)$item['reflection']) . '</p>';
    return $html . '</article>';
}

/** Samostatný dokument: inline CSS (vč. @media print), bez skriptů, odkazů a externích zdrojů. */
function port65v_export_html(array $model, string $studentLabel): string
{
    $css = 'body{font-family:system-ui,Arial,sans-serif;max-width:760px;margin:24px auto;padding:0 16px;color:#111;line-height:1.5}h1{margin-bottom:4px}'
        . '.item{border:1px solid #999;border-radius:10px;padding:12px 16px;margin:16px 0;break-inside:avoid}.row{display:flex;justify-content:space-between;gap:12px;border-bottom:1px dashed #bbb;padding:3px 0}'
        . '.meta{color:#444}@media print{body{margin:0;max-width:none}.item{border-color:#000}}';
    $html = '<!DOCTYPE html><html lang="' . e(function_exists('edu_locale') ? edu_locale() : 'cs') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . e(tr('Portfolio')) . ' – ' . e($studentLabel) . '</title><style>' . $css . '</style></head><body>'
        . '<h1>' . e(tr('Portfolio')) . '</h1><p class="meta">' . e($studentLabel) . ' · ' . e(date('j. n. Y')) . '</p>';
    if ($model === []) $html .= '<p>' . e(tr('Zatím nemáš nic vybráno.')) . '</p>';
    foreach ($model as $item) $html .= port65v_item_html($item, false);
    return $html . '</body></html>';
}

/** Odešle export jako stažení a skončí (jen GET z vlastní session; žádný odkaz ke sdílení). */
function port65v_send_export(string $classId, string $studentKey): never
{
    $html = port65v_export_html(port65_view_model($classId, $studentKey), adaptive_student_label($classId, $studentKey));
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="portfolio-' . date('Y-m-d') . '.html"');
    header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    echo $html;
    exit;
}
