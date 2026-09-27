<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog JS msgid domény „js_games“ (ukrajinština).
 * assets/robots-v58.js (viz lang/domains_v59.php, PLAN_I18N.md B6e). Vkládá edu_tr_json_js().
 * Jen žákovské texty (editor skriptu, přehrávač, stav zápasů); učitelský/projektorový polling
 * (initPoll, "Stav zápasu se změnil…") zůstává česky. Vlastník: builder i18n_teamgames_play.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // Stavy robota (ACT)
    'čeká' => 'очікує',
    'jede' => 'їде',
    'zablokovaný' => 'заблокований',
    'zvedl(a) balíček' => 'підняв(-ла) пакунок',
    'doručil(a) náklad' => 'доставив(-ла) вантаж',
    'opravuje uzel' => 'лагодить вузол',
    'nabíjí se' => 'заряджається',
    'chyba ve skriptu' => 'помилка в скрипті',
    'přes limit kroků / chladne' => 'перевищено ліміт кроків / охолоджується',
    'málo energie' => 'мало енергії',
    'akce se nepovedla' => 'дія не вдалася',
    'dokončil(a) opravu uzlu' => 'завершив(-ла) ремонт вузла',

    // Přehrávač záznamu
    'Pauza' => 'Пауза',
    'Přehrát' => 'Відтворити',
    'Záznam' => 'Запис',
    '{n} tahů' => ['one' => '{n} хід', 'few' => '{n} ходи', 'many' => '{n} ходів', 'other' => '{n} ходів'],
    'TY' => 'ТИ',
    '{name} (č. {n})' => '{name} (№ {n})',
    ' (ty)' => ' (ти)',
    'Robot' => 'Робот',
    'Rozbil se uzel č. {n} – kdo ho opraví?' => 'Вузол № {n} зламався – хто його полагодить?',
    ' (+{n} b)' => ' (+{n} б)',
    'Tah {n} z {m}' => 'Хід {n} з {m}',
    'Tah {n} z {m}.' => 'Хід {n} з {m}.',
    'Tvůj robot: {points} bodů, energie {energy}, náklad {cargo}, {status}.' => 'Твій робот: {points} балів, енергія {energy}, вантаж {cargo}, {status}.',
    'A další události: {n}.' => 'І ще подій: {n}.',
    'Tah {n}: {text}' => 'Хід {n}: {text}',
    'Žádné události.' => 'Жодних подій.',
    'Statistiky tvého robota' => 'Статистика твого робота',
    'Body' => 'Бали',
    'Doručené balíčky' => 'Доставлені пакунки',
    'Opravy (dokončené uzly)' => 'Ремонти (завершені вузли)',
    'Nabíjení' => 'Заряджання',
    'Pohyby / zablokováno' => 'Рухи / заблоковано',
    'Spotřebovaná energie' => 'Витрачена енергія',
    'Efektivita (body na 100 energie)' => 'Ефективність (балів на 100 енергії)',
    'Průměr kroků skriptu na tah' => 'Середня кількість кроків скрипту за хід',
    'Chyby / přetečení kroků' => 'Помилки / перевищення кроків',
    'Žádné chyby ani varování. Pěkné!' => 'Жодних помилок чи попереджень. Чудово!',
    'Chyby a varování' => 'Помилки та попередження',
    ', celkem {n}×' => ', всього {n}×',
    'Řádek {n}: ' => 'Рядок {n}: ',
    ' Tip: {tip}' => ' Порада: {tip}',
    ' (poprvé v tahu {n}{extra})' => ' (вперше на ході {n}{extra})',
    'Ukaž řádek {n}' => 'Показати рядок {n}',
    'Na řádek {n}' => 'До рядка {n}',
    ' – příliš dlouhé!' => ' – задовго!',

    // Žákovská stránka (editor, odevzdání)
    'Server odpověděl nečekaně ({n}).' => 'Сервер відповів несподівано ({n}).',
    'Nepodařilo se spojit se serverem. Zkontroluj připojení.' => 'Не вдалося з’єднатися із сервером. Перевір з’єднання.',
    'Simuluji…' => 'Симулюю…',
    'Nepovedlo se.' => 'Не вийшло.',
    'Skript má chybu – oprav ji a zkus to znovu.' => 'У скрипті є помилка – виправ її і спробуй ще раз.',
    'Skript je v pořádku.' => 'Скрипт у порядку.',
    ' (cvičný soupeř {n})' => ' (тренувальний суперник {n})',
    'Hotovo: {points} bodů za {turns} tahů' => 'Готово: {points} балів за {turns} ходів',
    '. Chyby a tipy najdeš pod přehrávačem.' => '. Помилки та підказки знайдеш під програвачем.',
    'Koncept uložen v {time}.' => 'Чернетку збережено о {time}.',
    'Odevzdáno! Pokus č. {n} – počítá se poslední platný skript.' => 'Здано! Спроба № {n} – рахується останній дійсний скрипт.',
    'Nahradit tvůj skript ukázkou? Neuložené změny se ztratí.' => 'Замінити твій скрипт прикладом? Незбережені зміни буде втрачено.',
    'Ukázka načtena. Zkus ji otestovat a pak upravit.' => 'Приклад завантажено. Спробуй його протестувати, а потім зміни.',
    'Načítám záznam zápasu…' => 'Завантажую запис матчу…',
    'Záznam se nepodařilo načíst.' => 'Не вдалося завантажити запис.',
    ' Připsali jsme ti XP za zápas.' => ' Ми нарахували тобі XP за матч.',
    'Záznam zápasu je připravený v přehrávači.' => 'Запис матчу готовий у програвачі.',
    'Zatím žádný zápas.' => 'Поки що жодного матчу.',
    '{title} (do {time})' => '{title} (до {time})',

    // Stavy zápasu
    'Příprava – odevzdávej' => 'Триває – здавай своє рішення',
    'Uzávěrka – čeká na simulaci' => 'Дедлайн минув – очікує на симуляцію',
    'Odehráno' => 'Зіграно',

    // Tabulka výsledků a zápasy
    'Místo' => 'Місце',
    'Tým' => 'Команда',
    'Robotů' => 'Роботів',
    'Doručeno / opravy' => 'Доставлено / ремонтів',
    'Týmy' => 'Команди',
    'Každý sám za sebe' => 'Кожен сам за себе',
    'uzávěrka {time}' => 'дедлайн {time}',
    'odevzdáno {n}' => 'здано {n}',
    'Tvůj tým: {name}' => 'Твоя команда: {name}',
    ' – {mates}' => ' – {mates}',
    ' ({n} hráčů)' => ' ({n} гравців)',
    'Tvůj robot v zápase hrál.' => 'Твій робот грав у матчі.',
    'Do tohohle zápasu jsi neodevzdal(a).' => 'Ти не здав(-ла) рішення на цей матч.',
    'Tvoje odevzdání: {time} (pokus {n})' => 'Твоя здача: {time} (спроба {n})',
    'Zatím jsi neodevzdal(a).' => 'Ти ще не здав(-ла).',
    'Tvoje umístění: {n}. místo' => 'Твоє місце: {n}-те',
    'Výsledky – {title}' => 'Результати – {title}',
    'Přehrát zápas' => 'Переглянути матч',
    'V tomhle pololetí se ještě nehrálo.' => 'У цьому півріччі ще не грали.',

    // Ligová tabulka
    'Liga · {label}' => 'Ліга · {label}',
    'Ligová tabulka' => 'Турнірна таблиця ліги',
    'Pořadí' => 'Рейтинг',
    'Hráč' => 'Гравець',
    'Zápasy' => 'Матчі',
    'Výhry' => 'Перемоги',
    'Ligové body' => 'Бали ліги',
    'Body robotů' => 'Бали роботів',
];
