<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „badges“ (angličtina) – unikátní odznaky (badges_v60.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Běžný' => 'Common',
    'Vzácný' => 'Rare',
    'Epický' => 'Epic',
    'Legendární' => 'Legendary',
    'Mýtický' => 'Mythic',
    'Odznak' => 'Badge',
    '{title} – {rarity}, získáno' => '{title} – {rarity}, earned',
    '{title} – {rarity}, zamčeno' => '{title} – {rarity}, locked',
    'Jak získat: {how}' => 'How to earn: {how}',
    'Jak získat:' => 'How to earn:',
    '✓ Získáno' => '✓ Earned',
    'Postup {percent} %' => 'Progress {percent}%',
    '{percent} %' => '{percent}%',
];
