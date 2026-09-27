<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_hubs“ (українська).
 * JS: assets/one-task-v50-5.js, assets/hands-on-v50.js (viz lang/domains_v59.php).
 * Vlastník: i18n builder B4. Potřebuje revizi rodilým mluvčím (ROADMAP_V59.md).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Server nevrátil platnou odpověď.' => 'Сервер не повернув коректну відповідь.',
    'Krok se nepodařilo uložit.' => 'Не вдалося зберегти крок.',
    'Hotovo. Krok je uložený.' => 'Готово. Крок збережено.',
    'Další krok →' => 'Наступний крок →',
    'Opravit a ověřit znovu →' => 'Виправити й перевірити знову →',
    'Nejdřív dokonči všechny části tohoto jediného kroku.' => 'Спочатку заверши всі частини цього кроку.',
    'Vyber jednu odpověď a potom ji ověř.' => 'Обери одну відповідь і перевір її.',
    'Ověřuji…' => 'Перевіряю…',
    'Tento závěr ještě nesedí. Vrať se k důkazům a zkus to znovu.' => 'Цей висновок ще не підтверджується. Повернися до доказів і спробуй ще раз.',
    '✓ Správně. Další krok je připravený.' => '✓ Правильно. Наступний крок готовий.',
    'Krok se nepodařilo ověřit.' => 'Крок не вдалося перевірити.',
    'Ještě to nesedí. Zkus si princip vysvětlit jinak.' => 'Поки що не сходиться. Спробуй пояснити принцип інакше.',
    '✓ Krok vysvětlení je hotový.' => '✓ Крок пояснення завершено.',
    'Ukládám…' => 'Зберігаю…',
    'Uloženo' => 'Збережено',
    'Uložení se nezdařilo' => 'Збереження не вдалося',
    'Uzel' => 'Вузол',
    'Bez dalších metadat.' => 'Без додаткових метаданих.',
    'Znovu od začátku ↻' => 'Знову спочатку ↻',
];
