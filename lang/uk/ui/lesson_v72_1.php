<?php

declare(strict_types=1);

/**
 * v72.1 · katalog msgid domény „lesson_v72_1“ (uk): dobrovolné AI prompty v lekci a upozornění na soukromí.
 * Klíč = přesně český text ze zdroje (ai_prompts_v72_1.php). Samotné prompty jsou výukový obsah a zůstávají česky.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Procvič si s AI (dobrovolné)' => 'Потренуйся з ШІ (добровільно)',
    'Než začneš:' => 'Перш ніж почати:',
    'Použij jen AI nástroj, který povolila škola nebo tvůj učitel.' => 'Користуйся лише тим інструментом ШІ, який дозволила школа або твій учитель.',
    'Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.' => 'Не вводь особисті дані – свої чи чужі: ім’я, e-mail, телефон, адресу, фото чи клас.',
    'Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.' => 'Не вводь шкільні дані для входу: паролі, одноразові коди, код уроку чи посилання з EDUCANET.',
    'Výstup AI vždy ověř – v programu, v simulátoru nebo u učitele. AI se plete i s jistotou.' => 'Завжди перевіряй відповідь ШІ – у програмі, у симуляторі або в учителя. ШІ помиляється навіть упевнено.',
    'Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil sám.' => 'Якщо ШІ допоміг тобі зі здаваною роботою, напиши це: який інструмент, для чого і що ти змінив сам.',
    'Prompt označ a zkopíruj do AI nástroje. Prompty jsou česky – AI ti může odpovídat i v jiném jazyce, když ji o to požádáš.' => 'Виділи промпт і скопіюй його в інструмент ШІ. Промпти чеською – ШІ може відповідати іншою мовою, якщо ти попросиш.',
];
