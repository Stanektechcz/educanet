<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Obchod: sezónní nabídka (platnost od–do) a náhled kosmetiky před nákupem.
 *
 * Sezónnost je jen VIDITELNOST položky v nabídce: server ji vynucuje při výpisu i při nákupu (mkt60_buy),
 * takže po skončení sezóny nejde koupit ani přes starý formulář. Už koupená kosmetika zůstává použitelná.
 * Nic z toho neovlivní známku, XP, žebříček ani arénu – typy položek zůstávají na bílé listině MKT60_TYPES.
 * Náhled je čisté GET čtení: nic nezapisuje, nic neodečítá; parametr ?nahled=<id> je whitelist (id musí být
 * kosmetická položka z aktuální nabídky třídy).
 */

const MKT61_ID_PATTERN = '/^[A-Za-z0-9_]{1,40}$/';

/** Platné datum Y-m-d, jinak ''. */
function mkt61_date(mixed $value): string
{
    $text = is_string($value) ? trim($value) : '';
    $d = $text !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $text) : false;
    return $d !== false && $d->format('Y-m-d') === $text ? $text : '';
}

/**
 * Sezónní okno z formuláře: ['season_from' => …, 'season_to' => …] ('' = bez omezení z dané strany),
 * null při neplatném vstupu (nesmyslné datum, konec před začátkem).
 */
function mkt61_season_from_input(array $input): ?array
{
    $rawFrom = trim((string)($input['season_from'] ?? ''));
    $rawTo = trim((string)($input['season_to'] ?? ''));
    $from = mkt61_date($rawFrom);
    $to = mkt61_date($rawTo);
    if (($rawFrom !== '' && $from === '') || ($rawTo !== '' && $to === '')) return null;
    if ($from !== '' && $to !== '' && $to < $from) return null;
    return ['season_from' => $from, 'season_to' => $to];
}

/** Je položka v nabídce k datu $today (výchozí dnes)? Bez sezónních dat je vždy. */
function mkt61_item_in_season(array $item, ?string $today = null): bool
{
    $today ??= date('Y-m-d');
    $from = mkt61_date($item['season_from'] ?? '');
    $to = mkt61_date($item['season_to'] ?? '');
    return ($from === '' || $today >= $from) && ($to === '' || $today <= $to);
}

function mkt61_is_seasonal(array $item): bool
{
    return mkt61_date($item['season_from'] ?? '') !== '' || mkt61_date($item['season_to'] ?? '') !== '';
}

/** Kosmetická položka z aktuální nabídky třídy, kterou lze zobrazit v náhledu; jinak null. */
function mkt61_preview_item(string $classId, mixed $itemId): ?array
{
    if (!is_string($itemId) || preg_match(MKT61_ID_PATTERN, $itemId) !== 1) return null;
    $item = mkt60_catalog_for_class($classId)[$itemId] ?? null;
    return is_array($item) && (string)($item['type'] ?? '') === 'cosmetic' && in_array((string)($item['slot'] ?? ''), MKT60_COSMETIC_SLOTS, true)
        ? ['id' => $itemId] + $item : null;
}
