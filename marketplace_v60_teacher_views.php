<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Učitelský registr obchodu (modul 'obchod' v teacher58_modules()). Rozsah tříd a role vynucuje
 * teacher59_guard_post()/teacher_require_permission() dřív, než sem dorazí požadavek (viz teacher.php).
 * Vlastní funkce zde tedy JEN pracují s daty – žádnou autorizaci si znovu neřeší (kromě dvojité kontroly
 * u mkt60_save, kde se classes[] navíc ořežou na to, co učitel smí, kdyby guard prošel jinak nastaveně).
 */

function mkt60_teacher_allowed_classes(): array
{
    return function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : [];
}

/** Zpracuje POST prefixu mkt60_ (voláno z teacher58_modules()['obchod']['post']). */
function mkt60_teacher_handle_post(string $action): void
{
    $allowed = mkt60_teacher_allowed_classes();
    if ($action === 'mkt60_save') {
        $id = is_string($_POST['id'] ?? null) ? (string)$_POST['id'] : '';
        $classes = array_values(array_filter((array)($_POST['classes'] ?? []), 'is_string'));
        $input = $_POST;
        $input['classes'] = $classes;
        $itemId = mkt60_save_item($id, $input, $allowed, teacher59_current_id() ?? 'teacher');
        $_SESSION['flash'] = $itemId !== null ? tr('Položka obchodu byla uložena.') : tr('Položku se nepodařilo uložit – zkontroluj údaje.');
    } elseif ($action === 'mkt60_activate' || $action === 'mkt60_deactivate') {
        $id = is_string($_POST['id'] ?? null) ? (string)$_POST['id'] : '';
        mkt60_set_active($id, $action === 'mkt60_activate');
        $_SESSION['flash'] = tr('Stav položky byl změněn.');
    } elseif ($action === 'mkt60_refund') {
        $studentKey = is_string($_POST['student_key'] ?? null) ? (string)$_POST['student_key'] : '';
        $purchaseKey = is_string($_POST['purchase_key'] ?? null) ? (string)$_POST['purchase_key'] : '';
        $classId = function_exists('teacher59_student_key_class') ? teacher59_student_key_class($studentKey) : '';
        $ok = $classId !== '' && pts60_refund($classId, $studentKey, $purchaseKey, tr('Vráceno učitelem v obchodě.'));
        if ($ok) mkt60_log(['type' => 'refund', 'class_id' => $classId, 'student_key' => $studentKey, 'purchase_key' => $purchaseKey, 'at' => date(DATE_ATOM)]);
        $_SESSION['flash'] = $ok ? tr('Nákup byl vrácen.') : tr('Vrácení se nepodařilo.');
    } elseif ($action === 'mkt60_seed') {
        mkt60_seed_if_empty(teacher59_current_id() ?? 'teacher');
        $_SESSION['flash'] = tr('Ukázkový katalog byl vložen (jen pokud byl obchod prázdný).');
    }
    redirect_to('teacher.php?tab=obchod');
}

function mkt60_render_teacher_tab(string $classId, string $csrf): void
{
    $allowed = mkt60_teacher_allowed_classes();
    $isAdmin = function_exists('teacher59_is_admin') && teacher59_is_admin();
    $items = mkt60_catalog_all();
    if (!$isAdmin) {
        // Neadmin vidí jen položky, jejichž VŠECHNY třídy jsou v jeho rozsahu (žádná cizí značka/data v seznamu).
        $items = array_filter($items, static function (array $item) use ($allowed): bool {
            $itemClasses = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
            return $itemClasses !== [] && array_diff($itemClasses, $allowed) === [];
        });
    }
    echo '<section class="teacher-panel"><h2>' . e(tr('Obchod bodů')) . '</h2>';
    echo '<form method="post" class="t-inline-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="mkt60_seed"><button class="btn secondary" type="submit">' . e(tr('Vložit ukázkový katalog (jen když je prázdný)')) . '</button></form>';

    echo '<h3>' . e(tr('Nová položka')) . '</h3>';
    mkt60_render_item_form('', [], $allowed, $csrf);

    echo '<h3>' . e(tr('Katalog')) . '</h3><table class="teacher-table"><thead><tr><th>' . e(tr('Název')) . '</th><th>' . e(tr('Typ')) . '</th><th>' . e(tr('Cena')) . '</th><th>' . e(tr('Sklad')) . '</th><th>' . e(tr('Třídy')) . '</th><th>' . e(tr('Stav')) . '</th><th></th></tr></thead><tbody>';
    foreach ($items as $id => $item) {
        if (!is_array($item)) continue;
        $itemClasses = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
        echo '<tr><td>' . e((string)$item['title']) . '</td><td>' . e((string)$item['type']) . '</td><td>' . (int)$item['price'] . '</td><td>' . ($item['stock'] === null ? '∞' : (int)$item['stock']) . '</td><td>' . e($itemClasses === [] ? tr('všechny') : implode(', ', $itemClasses)) . '</td><td>' . (empty($item['active']) ? e(tr('vypnuto')) : e(tr('aktivní'))) . '</td><td>';
        if ($itemClasses !== [] && array_diff($itemClasses, $allowed) === []) {
            echo '<form method="post" class="t-inline-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="id" value="' . e((string)$id) . '"><input type="hidden" name="action" value="' . (empty($item['active']) ? 'mkt60_activate' : 'mkt60_deactivate') . '"><button class="btn secondary small" type="submit">' . e(empty($item['active']) ? tr('Zapnout') : tr('Vypnout')) . '</button></form>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';

    mkt60_render_refund_form($csrf);
    echo '</section>';
}

function mkt60_render_item_form(string $id, array $item, array $allowed, string $csrf): void
{
    $classes = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
    echo '<form method="post" class="t-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="mkt60_save"><input type="hidden" name="id" value="' . e($id) . '">'
        . '<label>' . e(tr('Název')) . ' <input type="text" name="title" required value="' . e((string)($item['title'] ?? '')) . '"></label>'
        . '<label>' . e(tr('Typ')) . ' <select name="type"><option value="cosmetic">' . e(tr('Kosmetika')) . '</option><option value="content">' . e(tr('Obsah')) . '</option><option value="lab_hint">' . e(tr('Nápověda do labu')) . '</option><option value="other">' . e(tr('Ostatní')) . '</option></select></label>'
        . '<label>' . e(tr('Cena')) . ' <input type="number" name="price" min="0" required value="' . (int)($item['price'] ?? 0) . '"></label>'
        . '<label>' . e(tr('Sklad (prázdné = neomezeno)')) . ' <input type="number" name="stock" min="0"></label>'
        . '<label>' . e(tr('Slot kosmetiky (jen typ Kosmetika)')) . ' <select name="slot"><option value="frame">' . e(tr('Rámeček')) . '</option><option value="title">' . e(tr('Titulek')) . '</option></select></label>'
        . '<label>' . e(tr('Odkaz na obsah (jen typ Obsah)')) . ' <input type="text" name="url" value="' . e((string)($item['url'] ?? '')) . '"></label>'
        . '<label>' . e(tr('Popis')) . ' <textarea name="desc">' . e((string)($item['desc'] ?? '')) . '</textarea></label>'
        . '<fieldset><legend>' . e(tr('Třídy (prázdné = jen admin)')) . '</legend>';
    foreach ($allowed as $c) {
        echo '<label class="t-check"><input type="checkbox" name="classes[]" value="' . e($c) . '"' . (in_array($c, $classes, true) ? ' checked' : '') . '> ' . e($c) . '</label>';
    }
    echo '</fieldset><label class="t-check"><input type="checkbox" name="active" checked> ' . e(tr('Aktivní')) . '</label>'
        . '<button class="btn primary" type="submit">' . e(tr('Uložit')) . '</button></form>';
}

function mkt60_render_refund_form(string $csrf): void
{
    echo '<h3>' . e(tr('Vrátit nákup')) . '</h3>'
        . '<form method="post" class="t-inline-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="mkt60_refund">'
        . '<label>' . e(tr('Klíč žáka (class_x:student:…)')) . ' <input type="text" name="student_key" required></label>'
        . '<label>' . e(tr('Klíč nákupu (mkt:…)')) . ' <input type="text" name="purchase_key" required></label>'
        . '<button class="btn secondary" type="submit">' . e(tr('Vrátit body')) . '</button></form>';
}
