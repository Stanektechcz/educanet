<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog JS msgid domény „js_teamgames“ (ukrajinština).
 * assets/teamgames-v58.js (viz lang/domains_v59.php). Vkládá edu_tr_json_js() na žákovské stránce
 * (teamgames_v58_views.php). Jen žákovský panel hry; učitelský/projektorový panel zůstává česky.
 * Vlastník: builder i18n_teamgames. Před zveřejněním zkontroluje rodilý mluvčí (ROADMAP_V59.md).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Běží' => 'Триває',
    'Pauza' => 'Пауза',
    'Skončila' => 'Завершена',
    'Archiv' => 'Архів',
    'Připravuje se' => 'Готується',

    'Zbývá {n} minut.' => ['one' => 'Залишилась {n} хвилина.', 'few' => 'Залишилось {n} хвилини.', 'many' => 'Залишилось {n} хвилин.', 'other' => 'Залишилось {n} хвилин.'],
    'Zbývá {n} sekund.' => ['one' => 'Залишилась {n} секунда.', 'few' => 'Залишилось {n} секунди.', 'many' => 'Залишилось {n} секунд.', 'other' => 'Залишилось {n} секунд.'],
    'Čas vypršel.' => 'Час вийшов.',

    'Data se nepodařilo načíst – obnov stránku.' => 'Не вдалося завантажити дані – онови сторінку.',
    'Spojení se přerušilo, zkouším to znovu…' => "З'єднання перервалось, пробую ще раз…",
    'Relace vypršela – obnov stránku.' => 'Сесія закінчилась – онови сторінку.',
    'Nejsi přihlášen(a) – obnov stránku.' => 'Ти не увійшов(-ла) – онови сторінку.',
    'Data se nepodařilo načíst.' => 'Не вдалося завантажити дані.',
    'Signál se nepodařilo odeslat.' => 'Не вдалося надіслати сигнал.',

    'ano' => 'так',
    '{min} min {s} s' => '{min} хв {s} с',
    '{s} s' => '{s} с',
    '{body} b.' => '{body} б.',

    'Čekej na další otázku…' => 'Чекай на наступне питання…',
    'Odeslat' => 'Надіслати',
    'Zapiš pořadí čísly oddělenými čárkou (1 = první z nabídky výše).' => 'Запиши порядок числами через кому (1 = перший з варіантів вище).',
    'Pořadí, např. 2,1,3' => 'Порядок, напр. 2,1,3',
    'Odpověď' => 'Відповідь',
    'Správně!' => 'Правильно!',
    'Zatím ne, zkus to znovu.' => 'Поки що ні, спробуй ще раз.',

    'Úsek {usek} z {celkem} – hotovo!' => 'Етап {usek} з {celkem} – готово!',
    'Úsek {usek} z {celkem}' => 'Етап {usek} з {celkem}',
    'Váš tým doběhl do cíle. Skvělá práce!' => 'Твоя команда добігла до фінішу. Чудова робота!',
    'Řeš úkol v terminálu výše{nazev}. Po vyřešení úsek sám postoupí.' => 'Розв’яжи завдання в терміналі вище{nazev}. Після розв’язання етап автоматично просунеться.',
    'Poprosit o pomoc spoluhráče (+30 s)' => 'Попросити допомогу товариша (+30 с)',

    'Body: {body} · celé řady: {rady} (s bonusem: {bonus})' => 'Бали: {body} · повних рядів: {rady} (з бонусом: {bonus})',
    'Bonus za řadu se počítá, až políčko vyřeší každý člen týmu aspoň jednou.' => 'Бонус за ряд рахується, коли клітинку розв’яже кожен член команди хоча б раз.',
    'Zdarma' => 'Безкоштовно',

    'Na tahu: {tym} (vy!)' => 'Хід команди: {tym} (це ви!)',
    'Na tahu: {tym}' => 'Хід команди: {tym}',
    'Hodnota: {body} b.' => 'Вартість: {body} б.',
    'Odpověď je odeslaná, čekej na uzavření otázky.' => 'Відповідь надіслано, чекай на закриття питання.',
    '(výsledek se ukáže po uzavření otázky)' => '(результат зʼявиться після закриття питання)',
    '{kategorie}, {body} bodů' => '{kategorie}, {body} балів',
    'Čekej, až váš tým bude na tahu.' => 'Чекай, поки настане хід твоєї команди.',
    'Poslední otázka ({kategorie}): {odpoved} – {vysvetleni}' => 'Останнє питання ({kategorie}): {odpoved} – {vysvetleni}',
    'Poslední otázka ({kategorie}): {odpoved}' => 'Останнє питання ({kategorie}): {odpoved}',

    'Kolo {kolo} z {celkem} · zbývá {cas}' => 'Раунд {kolo} з {celkem} · залишилось {cas}',
    'Kolo {kolo} z {celkem}' => 'Раунд {kolo} з {celkem}',
    'Vyrovnaný stav.' => 'Рівний стан.',
    'Váš tým vede o {procenta} %.' => 'Твоя команда веде на {procenta} %.',
    'Soupeř vede o {procenta} %.' => 'Суперник веде на {procenta} %.',
    'Přetahovaná – {stav}' => 'Перетягування – {stav}',
    'Připravuje se další otázka…' => 'Готується наступне питання…',

    'Vaše body: {body} · vlna {vlna}/{celkem}' => 'Твої бали: {body} · хвиля {vlna}/{celkem}',
    'Uzel {cislo} ({vlastnik})' => 'Вузол {cislo} ({vlastnik})',
    'Uzel {cislo}' => 'Вузол {cislo}',

    'Váš tým unikl! 🎉' => 'Твоя команда втекла! 🎉',
    'Tvoje role: {role}' => 'Твоя роль: {role}',
    'Najdi svou stopu v terminálu výše a řekni ji nahlas týmu.' => 'Знайди свою підказку в терміналі вище і скажи її вголос команді.',
    'Tvoje část kódu: ' => 'Твоя частина коду: ',
    'Řekni ji nahlas týmu – appka chat nemá.' => 'Скажи її вголос команді – у застосунку немає чату.',
    'Použít týmovou nápovědu (+120 s, zbývá {n})' => 'Використати командну підказку (+120 с, залишилось {n})',
    'Zámek' => 'Замок',
    'Poskládejte 4 části kódu v pořadí: {poradi}.' => 'Складіть 4 частини коду в порядку: {poradi}.',
    'Otevřít zámek' => 'Відкрити замок',
    'Zámek se otevřel!' => 'Замок відкрився!',
    'Kód nesedí – zkontrolujte to s týmem.' => 'Код не збігається – перевірте це з командою.',

    'Tým: {jmeno}' => 'Команда: {jmeno}',
    'spoluhráči: {jmena}' => 'товариші по команді: {jmena}',
    'Zatím nejsi v žádném týmu.' => 'Ти ще не в жодній команді.',
    'Zatím žádné.' => 'Поки що немає.',
];
