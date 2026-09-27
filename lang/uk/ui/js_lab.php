<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „js_lab“ (українська). JS Linux Labu та Арени.
 * Власник: i18n builder B5.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // assets/linux-v57.js – редактор файлів (nano), повідомлення терміналу
    '^O Uložit' => '^O Зберегти',
    '^X Zavřít' => '^X Закрити',
    'Soubor je jen ke čtení.' => 'Файл лише для читання.',
    'Relace vypršela – obnov stránku (F5).' => 'Сесія закінчилась – онови сторінку (F5).',
    'Uloženo.' => 'Збережено.',
    'Chyba.' => 'Помилка.',
    '✔ Úroveň vyřešena.' => '✔ Рівень розв\'язано.',
    'Uložení se nezdařilo – zkus to znovu.' => 'Не вдалося зберегти – спробуй ще раз.',
    'Soubor patří správci – uprav ho přes sudo nano.' => 'Файл належить адміністратору – редагуй його через sudo nano.',
    'Příkaz selhal.' => 'Команда не виконалась.',
    'Spojení se serverem selhalo. Zkus to znovu.' => 'Не вдалося з\'єднатися із сервером. Спробуй ще раз.',
    'Opravdu vrátit úroveň do původního stavu? Postup se ztratí.' => 'Дійсно скинути рівень до початкового стану? Прогрес втратиться.',
    'Úroveň se vrátila do původního stavu.' => 'Рівень повернувся до початкового стану.',
    'Reset se nezdařil.' => 'Скидання не вдалося.',
    'Úroveň se nepodařilo načíst.' => 'Не вдалося завантажити рівень.',
    'Nepodařilo se spojit s Labem. Zkus obnovit stránku.' => 'Не вдалося з\'єднатися з Лабом. Спробуй оновити сторінку.',

    // assets/arena-v57.js – відлік, рейтинги учня, панель вчителя, проектор
    '(+{n} b{first})' => '(+{n} б{first})',
    'Bez týmů.' => 'Без команд.',
    'Běží' => 'Триває',
    'Data se nepodařilo načíst – obnov stránku.' => 'Не вдалося завантажити дані – онови сторінку.',
    'Data se nepodařilo načíst.' => 'Не вдалося завантажити дані.',
    'Kdo vyřeší první úlohu?' => 'Хто розв\'яже перше завдання?',
    'Na soupisce třídy zatím nikdo není.' => 'У списку класу поки нікого немає.',
    'Nejsi přihlášen(a) – obnov stránku.' => 'Ти не увійшов(-ла) – онови сторінку.',
    'Nikdo – zatím všichni postupují.' => 'Ніхто – поки всі просуваються.',
    'Připravený' => 'Готово',
    'Relace vypršela – obnov stránku.' => 'Сесія закінчилась – онови сторінку.',
    'Skončil' => 'Завершено',
    'Spojení se přerušilo, zkouším to znovu…' => 'З\'єднання перервалось, пробую ще раз…',
    'Zatím nic – bonus ×1,25 čeká.' => 'Поки нічого – бонус ×1,25 чекає.',
    'Zatím se nic nestalo.' => 'Поки нічого не сталося.',
    'Zbývá {mins}.' => 'Залишилось {mins}.',
    'Zbývá {n} sekund.' => ['one' => 'Залишилась {n} секунда.', 'few' => 'Залишилось {n} секунди.', 'many' => 'Залишилось {n} секунд.', 'other' => 'Залишилось {n} секунд.'],
    'Zobrazit výsledky →' => 'Показати результати →',
    'Závod skončil.' => 'Перегони завершено.',
    'nezapojen(a)' => 'не бере участі',
    'první vyřešil(a)' => 'першим(-ою) розв\'язав(-ла)',
    'první!' => 'перший(-а)!',
    'si vzal(a) nápovědu v' => 'взяв(-ла) підказку в',
    'vyřešil(a)' => 'розв\'язав(-ла)',
    'zadal(a) kód spolužáka v úloze' => 'ввів(-ла) код однокласника в завданні',
    'zkusil(a) cizí kód v' => 'спробував(-ла) чужий код у',
    '{min} min, {cmds} příkazů ({failed} s chybou)' => '{min} хв, {cmds} команд ({failed} з помилкою)',
    '{n} b, {m} úl.' => '{n} б, {m} зав.',
    'Čas vypršel.' => 'Час вийшов.',
    'Žádné – nikdo nezkoušel cizí kód.' => 'Жодного – ніхто не пробував чужий код.',
    '{n} minut' => ['one' => '{n} хвилина', 'few' => '{n} хвилини', 'many' => '{n} хвилин', 'other' => '{n} хвилин'],
    'Týmy se ukážou po startu.' => 'Команди з\'являться після старту.',
    'ty' => 'ти',
    'vyřešeno' => 'розв\'язано',
    '{done} / {target} vyřešených úloh' => '{done} / {target} розв\'язаних завдань',
    '{min} min {s} s' => '{min} хв {s} с',
    '{n} b' => '{n} б',
    '{n} úl.' => '{n} зав.',
    '{s} s' => '{s} с',
    'Zatím nikdo nemá body. Buď první!' => 'Поки ніхто не має балів. Будь першим(-ою)!',
    'Zatím nic – první vyřešení úlohy dává bonus ×1,25.' => 'Поки нічого – перше розв\'язання завдання дає бонус ×1,25.',

    // assets/lab-a11y-v58.js – доступність термінала
    'Bez odezvy' => 'Немає відповіді',
    'Bez textového obsahu.' => 'Без текстового вмісту.',
    'Cestu se nepodařilo zjistit.' => 'Не вдалося визначити маршрут.',
    'Chyba:' => 'Помилка:',
    'Switch (místní síť)' => 'Switch (локальна мережа)',
    'Tento počítač' => 'Цей комп\'ютер',
    'Terminál je zatím prázdný.' => 'Термінал поки порожній.',
    'Týmový režim' => 'Командний режим',
    'V pořádku' => 'Гаразд',
    'role: {role}' => 'роль: {role}',

    // assets/lab-explain-v58.js – панель "Пояснити вивід" (UI, самі пояснення лишаються контентом)
    'Zpráva simulátoru' => 'Повідомлення симулятора',
    'Tohle je nápověda nebo systémová zpráva simulátoru, ne přímý výstup příkazu.' => 'Це підказка або системне повідомлення симулятора, а не прямий вивід команди.',
    'Pro příkaz „{prikaz}“ zatím nemáme rychlé vysvětlení přímo tady – zkus příručku příkazů.' => 'Для команди «{prikaz}» тут поки немає швидкого пояснення – спробуй довідник команд.',
    'K tomuhle řádku se nepodařilo najít příkaz, ke kterému patří.' => 'Для цього рядка не вдалося знайти команду, до якої він належить.',
    'Příkaz {prikaz}' => 'Команда {prikaz}',
    'Výstup' => 'Вивід',
    'Vysvětlit řádek:' => 'Пояснити рядок:',
    '(prázdné)' => '(порожньо)',
    'Otevřít „{prikaz}“ v příručce →' => 'Відкрити «{prikaz}» у довіднику →',
];
