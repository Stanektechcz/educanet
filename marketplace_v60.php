<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Obchod bodů (marketplace). Katalog spravuje admin/učitel (jen své třídy), žák nakupuje za body
 * z peněženky (points_v53.php). Typy položek jsou na bílé listině a NIKDY neovlivňují známku, XP,
 * žebříček ani arénu/CTF – jde jen o kosmetiku profilu, evidenci obsahu/nápovědy nebo „ostatní“.
 *
 * Úložiště:
 *   storage/marketplace_v60.json.php      – katalog {id => položka}
 *   storage/profile_cosmetics_v60.json.php – aktivní kosmetika žáka {classId|studentKey => {frame, title}}
 *   proud marketplace_v60_log (storage_append) – log nákupů/vrácení/administrace (jen pro dohled).
 */

const MKT60_TYPES = ['cosmetic', 'content', 'lab_hint', 'other'];
const MKT60_COSMETIC_SLOTS = ['frame', 'title'];

function mkt60_catalog_path(): string
{
    return STORAGE_DIR . '/marketplace_v60.json.php';
}

function mkt60_cosmetics_path(): string
{
    return STORAGE_DIR . '/profile_cosmetics_v60.json.php';
}

/** Dohledový log nákupů. Selhání logu NIKDY neshodí už potvrzený nákup/vrácení (jen záznam do error_logu). */
function mkt60_log(array $record): void
{
    try {
        storage_append('marketplace_v60_log', $record);
    } catch (Throwable $e) {
        error_log('EDUCANET mkt60: log nelze zapsat (' . get_class($e) . ').');
    }
}

function mkt60_wallet_key(string $classId, string $studentKey): string
{
    return $classId . '|' . $studentKey;
}

/** Celý katalog (bez filtrace) – jen pro učitele/admina. */
function mkt60_catalog_all(): array
{
    return storage_read(mkt60_catalog_path());
}

/** Položky dostupné žákovi dané třídy: aktivní a (classes prázdné = pro všechny, nebo obsahuje $classId). */
function mkt60_catalog_for_class(string $classId): array
{
    $out = [];
    foreach (mkt60_catalog_all() as $id => $item) {
        if (!is_array($item) || empty($item['active'])) continue;
        $classes = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
        if ($classes !== [] && !in_array($classId, $classes, true)) continue;
        $out[(string)$id] = $item;
    }
    uasort($out, static fn(array $a, array $b): int => (int)($a['price'] ?? 0) <=> (int)($b['price'] ?? 0));
    return $out;
}

function mkt60_item(string $itemId): ?array
{
    $item = mkt60_catalog_all()[$itemId] ?? null;
    return is_array($item) ? $item : null;
}

function mkt60_item_valid_type(string $type): bool
{
    return in_array($type, MKT60_TYPES, true);
}

/** Odkaz na obsah: buď interní pohled (?view=…, jen bezpečné znaky), nebo externí https/http přes safe_url(). */
function mkt60_url_ok(string $url): bool
{
    if ($url === '') return true;
    if (str_starts_with($url, '?') && preg_match('/^[a-zA-Z0-9?=&_%-]+$/', $url) === 1) return true;
    return safe_url($url) !== '';
}

/** Výsledný href pro vykreslení odkazu na obsah (interní beze změny, jinak přes safe_url()). */
function mkt60_render_url(string $url): string
{
    if ($url === '' || str_starts_with($url, '?')) return $url;
    return safe_url($url);
}

/** Normalizuje/ověří vstup položky z formuláře (učitel/admin). Vrací null při neplatných datech. */
function mkt60_item_from_input(array $input, array $allowedClasses): ?array
{
    $type = (string)($input['type'] ?? '');
    $title = trim((string)($input['title'] ?? ''));
    $price = (int)($input['price'] ?? -1);
    if (!mkt60_item_valid_type($type) || $title === '' || $price < 0) return null;
    $stockRaw = $input['stock'] ?? null;
    $stock = ($stockRaw === null || $stockRaw === '') ? null : max(0, (int)$stockRaw);
    $classes = array_values(array_unique(array_filter((array)($input['classes'] ?? []), 'is_string')));
    foreach ($classes as $c) {
        if (!in_array($c, $allowedClasses, true)) return null; // učitel nesmí přiřadit cizí třídu
    }
    $slot = (string)($input['slot'] ?? '');
    if ($type === 'cosmetic' && !in_array($slot, MKT60_COSMETIC_SLOTS, true)) return null;
    $url = trim((string)($input['url'] ?? ''));
    if ($type === 'content' && $url !== '' && !mkt60_url_ok($url)) return null;
    return [
        'type' => $type,
        'title' => $title,
        'desc' => trim((string)($input['desc'] ?? '')),
        'price' => $price,
        'stock' => $stock,
        'classes' => $classes,
        'slot' => $type === 'cosmetic' ? $slot : '',
        'url' => $type === 'content' ? $url : '',
        'active' => !empty($input['active']),
    ];
}

/** Vytvoří/upraví položku katalogu. $id = '' → nová položka. Vrací id, nebo null při neplatných datech. */
function mkt60_save_item(string $id, array $input, array $allowedClasses, string $actor): ?string
{
    $data = mkt60_item_from_input($input, $allowedClasses);
    if ($data === null) return null;
    $result = null;
    storage_update(mkt60_catalog_path(), static function (array $all) use (&$result, $id, $data, $actor): array {
        $itemId = $id !== '' && isset($all[$id]) ? $id : 'mkt' . bin2hex(random_bytes(5));
        $existing = is_array($all[$itemId] ?? null) ? $all[$itemId] : [];
        $all[$itemId] = array_replace($existing, $data, [
            'id' => $itemId,
            'created_by' => (string)($existing['created_by'] ?? $actor),
            'created_at' => (string)($existing['created_at'] ?? date(DATE_ATOM)),
            'updated_at' => date(DATE_ATOM),
        ]);
        $result = $itemId;
        return $all;
    });
    return $result;
}

function mkt60_set_active(string $id, bool $active): bool
{
    $ok = false;
    storage_map_update(mkt60_catalog_path(), $id, static function (?array $item) use (&$ok, $active): ?array {
        if ($item === null) return null;
        $ok = true;
        $item['active'] = $active;
        $item['updated_at'] = date(DATE_ATOM);
        return $item;
    });
    return $ok;
}

/**
 * Nákup: request_id zajišťuje idempotenci (opakované volání se stejným request_id nic nestrhne navíc).
 * Sklad a body se ověřují a odečítají atomicky pod jedním zámkem (storage_update_many přes katalog i
 * peněženku) – nikdy nejde do záporných hodnot. @return array{ok:bool, error?:string, balance:int}
 */
function mkt60_buy(string $classId, string $studentKey, string $itemId, int $qty, string $requestId): array
{
    if ($studentKey === '' || $itemId === '' || $requestId === '' || $qty < 1 || $qty > 20) {
        return ['ok' => false, 'error' => 'invalid_request', 'balance' => pts53_balance($classId, $studentKey)];
    }
    $purchaseKey = 'mkt:' . $itemId . ':' . $requestId;
    $walletPath = pts53_path();
    $catalogPath = mkt60_catalog_path();
    $walletKey = mkt60_wallet_key($classId, $studentKey);
    $outcome = ['ok' => false, 'error' => 'unknown', 'balance' => 0];
    storage_update_many([$catalogPath, $walletPath], function (array $data) use (&$outcome, $catalogPath, $walletPath, $walletKey, $itemId, $qty, $classId, $purchaseKey): array {
        $catalog = $data[$catalogPath];
        $item = is_array($catalog[$itemId] ?? null) ? $catalog[$itemId] : null;
        $walletRow = is_array($data[$walletPath][$walletKey] ?? null) ? $data[$walletPath][$walletKey] : [];
        $walletRow = array_replace(['earned' => 0, 'spent' => 0, 'awards' => [], 'purchases' => []], $walletRow);
        if (isset($walletRow['purchases'][$purchaseKey])) {
            $outcome = ['ok' => true, 'error' => null, 'balance' => max(0, (int)$walletRow['earned'] - (int)$walletRow['spent'])];
            return $data;
        }
        if ($item === null || empty($item['active']) || !mkt60_item_valid_type((string)($item['type'] ?? ''))) {
            $outcome = ['ok' => false, 'error' => 'item_unavailable', 'balance' => max(0, (int)$walletRow['earned'] - (int)$walletRow['spent'])];
            return $data;
        }
        $classes = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
        if ($classes !== [] && !in_array($classId, $classes, true)) {
            $outcome = ['ok' => false, 'error' => 'item_unavailable', 'balance' => max(0, (int)$walletRow['earned'] - (int)$walletRow['spent'])];
            return $data;
        }
        $stock = $item['stock'] ?? null;
        if ($stock !== null && (int)$stock < $qty) {
            $outcome = ['ok' => false, 'error' => 'out_of_stock', 'balance' => max(0, (int)$walletRow['earned'] - (int)$walletRow['spent'])];
            return $data;
        }
        $cost = (int)$item['price'] * $qty;
        $balance = max(0, (int)$walletRow['earned'] - (int)$walletRow['spent']);
        if ($balance < $cost) {
            $outcome = ['ok' => false, 'error' => 'insufficient_points', 'balance' => $balance];
            return $data;
        }
        if ($stock !== null) {
            $catalog[$itemId]['stock'] = max(0, (int)$stock - $qty);
        }
        $walletRow['purchases'][$purchaseKey] = ['cost' => $cost, 'reason' => 'Obchod: ' . (string)$item['title'], 'item_id' => $itemId, 'qty' => $qty, 'at' => date(DATE_ATOM)];
        $walletRow['spent'] = array_sum(array_map(static fn($p) => (int)($p['cost'] ?? 0), (array)$walletRow['purchases']));
        $walletRow['updated_at'] = date(DATE_ATOM);
        $data[$catalogPath] = $catalog;
        $data[$walletPath][$walletKey] = $walletRow;
        $outcome = ['ok' => true, 'error' => null, 'balance' => max(0, (int)$walletRow['earned'] - (int)$walletRow['spent'])];
        return $data;
    });
    if ($outcome['ok']) {
        mkt60_log(['type' => 'buy', 'class_id' => $classId, 'student_key' => $studentKey, 'item_id' => $itemId, 'qty' => $qty, 'purchase_key' => $purchaseKey, 'at' => date(DATE_ATOM)]);
    }
    return $outcome;
}

/** Nákupy žáka (mkt: prefix) spárované s katalogem – pro „Moje nákupy“. */
function mkt60_my_purchases(string $classId, string $studentKey): array
{
    $wallet = pts53_wallet($classId, $studentKey);
    $catalog = mkt60_catalog_all();
    $out = [];
    foreach ((array)$wallet['purchases'] as $key => $p) {
        if (!str_starts_with((string)$key, 'mkt:')) continue;
        $itemId = (string)($p['item_id'] ?? '');
        $item = is_array($catalog[$itemId] ?? null) ? $catalog[$itemId] : null;
        $out[] = [
            'key' => (string)$key,
            'item_id' => $itemId,
            'title' => $item !== null ? (string)$item['title'] : (string)($p['reason'] ?? $itemId),
            'type' => $item !== null ? (string)$item['type'] : '',
            'qty' => (int)($p['qty'] ?? 1),
            'cost' => (int)($p['cost'] ?? 0),
            'at' => (string)($p['at'] ?? ''),
            'refunded' => pts60_is_refunded($classId, $studentKey, (string)$key),
            'url' => $item !== null ? (string)($item['url'] ?? '') : '',
        ];
    }
    usort($out, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
    return $out;
}

/** Aktuální kosmetika žáka {frame:?string, title:?string}. */
function mkt60_cosmetics(string $classId, string $studentKey): array
{
    $row = storage_read(mkt60_cosmetics_path())[mkt60_wallet_key($classId, $studentKey)] ?? [];
    $row = is_array($row) ? $row : [];
    return ['frame' => is_string($row['frame'] ?? null) ? $row['frame'] : null, 'title' => is_string($row['title'] ?? null) ? $row['title'] : null];
}

/** Aktivuje vlastněnou kosmetiku ($slot = frame|title), nebo ji vypne ($itemId = ''). */
function mkt60_set_cosmetic(string $classId, string $studentKey, string $slot, string $itemId): bool
{
    if (!in_array($slot, MKT60_COSMETIC_SLOTS, true)) return false;
    if ($itemId !== '') {
        $item = mkt60_item($itemId);
        if ($item === null || (string)($item['type'] ?? '') !== 'cosmetic' || (string)($item['slot'] ?? '') !== $slot) return false;
        $owned = false;
        foreach (array_keys((array)pts53_wallet($classId, $studentKey)['purchases']) as $key) {
            if (str_starts_with((string)$key, 'mkt:' . $itemId . ':')) { $owned = true; break; }
        }
        if (!$owned) return false;
    }
    storage_map_update(mkt60_cosmetics_path(), mkt60_wallet_key($classId, $studentKey), static function (?array $row) use ($slot, $itemId): array {
        $row = is_array($row) ? $row : [];
        $row[$slot] = $itemId === '' ? null : $itemId;
        return $row;
    });
    return true;
}

/** Ukázkový katalog vloží jen tehdy, když katalog ještě neexistuje (soubor chybí/prázdný). */
function mkt60_seed_if_empty(string $actor): bool
{
    if (mkt60_catalog_all() !== []) return false;
    $seed = [
        'mkt_frame_gold' => ['type' => 'cosmetic', 'title' => tr('Zlatý rámeček'), 'desc' => tr('Ozdobný rámeček kolem avataru.'), 'price' => 30, 'stock' => null, 'classes' => [], 'slot' => 'frame', 'url' => '', 'active' => true],
        'mkt_frame_neon' => ['type' => 'cosmetic', 'title' => tr('Neonový rámeček'), 'desc' => tr('Zářivý rámeček kolem avataru.'), 'price' => 30, 'stock' => null, 'classes' => [], 'slot' => 'frame', 'url' => '', 'active' => true],
        'mkt_title_legend' => ['type' => 'cosmetic', 'title' => tr('Titulek: Legenda třídy'), 'desc' => tr('Titulek vedle jména v profilu.'), 'price' => 40, 'stock' => null, 'classes' => [], 'slot' => 'title', 'url' => '', 'active' => true],
        'mkt_extra_lesson' => ['type' => 'content', 'title' => tr('Extra lekce: rychlé zkratky'), 'desc' => tr('Doplňková lekce nad rámec výuky.'), 'price' => 20, 'stock' => null, 'classes' => [], 'slot' => '', 'url' => '?view=materialy', 'active' => true],
    ];
    storage_update(mkt60_catalog_path(), static function (array $all) use ($seed, $actor): array {
        if ($all !== []) return $all;
        foreach ($seed as $id => $item) {
            $all[$id] = array_replace($item, ['id' => $id, 'created_by' => $actor, 'created_at' => date(DATE_ATOM), 'updated_at' => date(DATE_ATOM)]);
        }
        return $all;
    });
    return true;
}
