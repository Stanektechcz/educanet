<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „marketplace“ (angličtina) – obchod bodů žáka.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Obchod' => 'Shop',
    'Nakup si za body' => 'Spend your points',
    'Body získáváš za úkoly a známky. Obchod nikdy neovlivní tvoji známku, XP ani žebříček.' => 'You earn points for tasks and grades. The shop never affects your grade, XP or leaderboard.',
    'bodů k utracení' => 'points to spend',
    '{n} bodů' => ['one' => '{n} point', 'other' => '{n} points'],
    'Obchod je zatím prázdný. Zeptej se učitele, kdy přidá první položky.' => 'The shop is empty for now. Ask your teacher when the first items will be added.',
    'Nabídka obchodu' => 'Shop offer',
    'Kosmetika profilu' => 'Profile cosmetics',
    'Extra obsah' => 'Extra content',
    'Nápověda do labu' => 'Lab hint',
    'Ostatní' => 'Other',
    'Skladem: {n}' => 'In stock: {n}',
    'Vyprodáno' => 'Sold out',
    'Koupit' => 'Buy',
    'Nemáš dost bodů.' => "You don't have enough points.",
    'Moje nákupy' => 'My purchases',
    'Historie nákupů v obchodě' => 'Shop purchase history',
    'Zatím jsi nic nekoupil/a.' => "You haven't bought anything yet.",
    'Vráceno učitelem' => 'Refunded by the teacher',
    'Použít na profilu' => 'Use on profile',
    'Vypnout' => 'Turn off',
    'Otevřít obsah' => 'Open content',
    'Nákup proběhl. Zůstatek: {n} bodů.' => 'Purchase completed. Balance: {n} points.',
    'Nákup se nepodařil.' => 'Purchase failed.',
    'Kosmetiku se nepodařilo nastavit.' => 'Could not set the cosmetic item.',
    'Zlatý rámeček' => 'Gold frame',
    'Ozdobný rámeček kolem avataru.' => 'A decorative frame around your avatar.',
    'Neonový rámeček' => 'Neon frame',
    'Zářivý rámeček kolem avataru.' => 'A glowing frame around your avatar.',
    'Titulek: Legenda třídy' => 'Title: Class legend',
    'Titulek vedle jména v profilu.' => 'A title shown next to your name in the profile.',
    'Extra lekce: rychlé zkratky' => 'Extra lesson: quick shortcuts',
    'Doplňková lekce nad rámec výuky.' => 'A bonus lesson beyond the regular curriculum.',
    'Filtr nabídky' => 'Filter offer',
    'Všechno' => 'All',
    'Oblíbené ({n})' => 'Favorites ({n})',
    'Oblíbené ✓' => 'Favorite ✓',
    'Do oblíbených' => 'Add to favorites',
    'Náhled' => 'Preview',
    'Sezónní · do {date}' => 'Seasonal · until {date}',
    'Sezónní · od {date}' => 'Seasonal · from {date}',
    'Takhle by to vypadalo na tvém profilu' => 'This is how it would look on your profile',
    'Zavřít náhled' => 'Close preview',
    '{title} · {n} bodů. Náhled nic nekupuje.' => '{title} · {n} points. The preview does not buy anything.',
    'Zatím nemáš žádné oblíbené položky.' => 'You have no favorite items yet.',
    'Sezónní nabídka' => 'Seasonal offer',
    'Stálá nabídka' => 'Regular offer',
];
