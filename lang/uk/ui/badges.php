<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „badges“ (ukrajinština) – unikátní odznaky (badges_v60.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Běžný' => 'Звичайний',
    'Vzácný' => 'Рідкісний',
    'Epický' => 'Епічний',
    'Legendární' => 'Легендарний',
    'Mýtický' => 'Міфічний',
    'Odznak' => 'Значок',
    '{title} – {rarity}, získáno' => '{title} – {rarity}, отримано',
    '{title} – {rarity}, zamčeno' => '{title} – {rarity}, заблоковано',
    'Jak získat: {how}' => 'Як отримати: {how}',
    'Jak získat:' => 'Як отримати:',
    '✓ Získáno' => '✓ Отримано',
    'Postup {percent} %' => 'Прогрес {percent} %',
    '{percent} %' => '{percent} %',
];
