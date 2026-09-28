<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Vykreslení obchodu bodů (žák) – volá ho app/views/marketplace.php.
 */

function marketplace60_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/marketplace-v60.css?v=60.0')) . '">' . "\n";
}

function marketplace60_type_label(string $type): string
{
    return [
        'cosmetic' => tr('Kosmetika profilu'),
        'content' => tr('Extra obsah'),
        'lab_hint' => tr('Nápověda do labu'),
        'other' => tr('Ostatní'),
    ][$type] ?? $type;
}

function marketplace60_render_shop(string $classId, string $studentKey): void
{
    marketplace60_assets();
    $balance = pts53_balance($classId, $studentKey);
    $items = mkt60_catalog_for_class($classId);
    echo '<section class="mkt60-hero"><div><div class="eyebrow">' . e(tr('Obchod')) . '</div><h1>' . e(tr('Nakup si za body')) . '</h1>'
        . '<p>' . e(tr('Body získáváš za úkoly a známky. Obchod nikdy neovlivní tvoji známku, XP ani žebříček.')) . '</p></div>'
        . '<div class="mkt60-balance"><strong>' . (int)$balance . '</strong><span>' . e(tr('bodů k utracení')) . '</span></div></section>';

    if ($items === []) {
        echo '<section class="dashboard-panel mkt60-empty"><p>' . e(tr('Obchod je zatím prázdný. Zeptej se učitele, kdy přidá první položky.')) . '</p></section>';
    } else {
        echo '<section class="mkt60-grid" aria-label="' . e(tr('Nabídka obchodu')) . '">';
        foreach ($items as $id => $item) {
            marketplace60_render_item((string)$id, $item, $classId, $studentKey, $balance);
        }
        echo '</section>';
    }

    marketplace60_render_purchases($classId, $studentKey);
}

function marketplace60_render_item(string $id, array $item, string $classId, string $studentKey, int $balance): void
{
    $price = (int)$item['price'];
    $stock = $item['stock'] ?? null;
    $soldOut = $stock !== null && (int)$stock <= 0;
    $canAfford = $balance >= $price;
    echo '<article class="mkt60-card">'
        . '<span class="mkt60-type">' . e(marketplace60_type_label((string)$item['type'])) . '</span>'
        . '<h2>' . e((string)$item['title']) . '</h2>';
    if ((string)$item['desc'] !== '') echo '<p>' . e((string)$item['desc']) . '</p>';
    echo '<div class="mkt60-card-foot"><strong>' . e(tr('{n} bodů', ['n' => $price])) . '</strong>';
    if ($stock !== null) echo '<small>' . e(tr('Skladem: {n}', ['n' => (int)$stock])) . '</small>';
    if ($soldOut) {
        echo '<span class="mkt60-soldout">' . e(tr('Vyprodáno')) . '</span>';
    } else {
        echo '<form method="post" class="mkt60-buy-form">'
            . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
            . '<input type="hidden" name="action" value="mkt60_buy">'
            . '<input type="hidden" name="item_id" value="' . e($id) . '">'
            . '<input type="hidden" name="request_id" value="' . e(bin2hex(random_bytes(8))) . '">'
            . '<button class="btn primary" type="submit"' . (!$canAfford ? ' disabled aria-disabled="true"' : '') . '>' . e(tr('Koupit')) . '</button>'
            . '</form>';
        if (!$canAfford) echo '<small class="mkt60-note">' . e(tr('Nemáš dost bodů.')) . '</small>';
    }
    echo '</div></article>';
}

function marketplace60_render_purchases(string $classId, string $studentKey): void
{
    $purchases = mkt60_my_purchases($classId, $studentKey);
    echo '<section class="dashboard-panel mkt60-purchases"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Moje nákupy')) . '</div><h2>' . e(tr('Historie nákupů v obchodě')) . '</h2></div></div>';
    if ($purchases === []) {
        echo '<p class="mkt60-note">' . e(tr('Zatím jsi nic nekoupil/a.')) . '</p></section>';
        return;
    }
    $cosmetics = mkt60_cosmetics($classId, $studentKey);
    echo '<ul class="mkt60-purchase-list">';
    foreach ($purchases as $p) {
        echo '<li><div><strong>' . e((string)$p['title']) . '</strong><small>' . e(date('d.m.Y H:i', strtotime((string)$p['at']) ?: time())) . ' · ' . e(tr('{n} bodů', ['n' => (int)$p['cost']])) . '</small>'
            . ($p['refunded'] ? '<span class="mkt60-refunded">' . e(tr('Vráceno učitelem')) . '</span>' : '') . '</div>';
        if (!$p['refunded'] && $p['type'] === 'cosmetic' && $p['item_id'] !== '') {
            $item = mkt60_item((string)$p['item_id']);
            $slot = $item !== null ? (string)($item['slot'] ?? '') : '';
            if ($slot !== '') {
                $active = ($cosmetics[$slot] ?? null) === $p['item_id'];
                echo '<form method="post" class="mkt60-inline-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
                    . '<input type="hidden" name="action" value="mkt60_cosmetic_set"><input type="hidden" name="slot" value="' . e($slot) . '">'
                    . '<input type="hidden" name="item_id" value="' . e($active ? '' : (string)$p['item_id']) . '">'
                    . '<button class="btn secondary small" type="submit">' . e($active ? tr('Vypnout') : tr('Použít na profilu')) . '</button></form>';
            }
        }
        if (!$p['refunded'] && $p['type'] === 'content' && (string)$p['url'] !== '') {
            echo '<a class="btn secondary small" href="' . e(mkt60_render_url((string)$p['url'])) . '">' . e(tr('Otevřít obsah')) . '</a>';
        }
        echo '</li>';
    }
    echo '</ul></section>';
}
