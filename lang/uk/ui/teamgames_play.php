<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „teamgames_play“ (ukrajinština).
 * Zdroj: teamgames_v58_game_bingo.php, teamgames_v58_game_escape.php, teamgames_v58_game_jeopardy.php,
 * teamgames_v58_game_netadmin.php, teamgames_v58_game_relay.php, teamgames_v58_game_tug.php
 * (viz lang/domains_v59.php, PLAN_I18N.md B6e). Jen žákovské texty (instrukce, stavy, hlášky, chyby
 * hráčům); učitelské a projekční texty zůstávají česky. Vlastník: builder i18n_teamgames_play.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // Příkazové bingo
    'Zdarma' => 'Безкоштовно',
    'Terminál' => 'Термінал',
    'Tohle políčko na tvé kartě není.' => 'Цієї клітинки немає на твоїй картці.',
    'Políčko nebylo nalezeno.' => 'Клітинку не знайдено.',
    'Neznámá akce bingo.' => 'Невідома дія бінго.',
    'Příkazové bingo' => 'Бінго команд',

    // Úniková místnost
    'Síťař' => 'Мережевик',
    'Správce' => 'Адміністратор',
    'Detektiv' => 'Детектив',
    'Dokumentátor' => 'Документаліст',
    'Typograf' => 'Типограф',
    'Kolorista' => 'Колорист',
    'Kodér' => 'Кодер',
    'Art director' => 'Арт-директор',
    'Tým nebyl nalezen.' => 'Команду не знайдено.',
    'Tenhle tým už unikl.' => 'Ця команда вже втекла.',
    'Nápovědy pro tenhle tým došly.' => 'У цієї команди закінчились підказки.',
    'Tým dostal nápovědu (+{s} s k výslednému času).' => 'Команда отримала підказку (+{s} с до підсумкового часу).',
    'Tahle role se řeší v terminálu Labu.' => 'Ця роль вирішується в терміналі Лабораторії.',
    'Nemáš přiřazenou roli.' => 'У тебе немає призначеної ролі.',
    'Otázka se nenašla.' => 'Питання не знайдено.',
    'Moc pokusů o zámek za minutu – chvilku počkej.' => 'Забагато спроб відкрити замок за хвилину – трохи почекай.',
    'Zámek potřebuje {n} částí kódu (jednu od každé role).' => 'Замку потрібно {n} частин коду (по одній від кожної ролі).',
    'Neznámá akce únikové místnosti.' => 'Невідома дія квест-кімнати.',
    'Úniková místnost' => 'Квест-кімната',

    // Riskuj!
    'Otázka právě běží – nejdřív se musí uzavřít.' => 'Питання саме триває – спочатку воно має закритися.',
    'Teď je na tahu jiný tým.' => 'Зараз хід іншої команди.',
    'Neplatné pole tabule.' => 'Недійсна клітинка таблиці.',
    'Tohle pole je už zahrané.' => 'Цю клітинку вже зіграно.',
    'Pro tohle pole chybí otázka.' => 'Для цієї клітинки бракує питання.',
    'Teď neběží žádná otázka.' => 'Зараз жодне питання не триває.',
    'Čas na odpověď vypršel.' => 'Час на відповідь вийшов.',
    'Na tuhle otázku už jsi odpověděl(a).' => 'Ти вже відповів(-ла) на це питання.',
    'Neznámá akce Riskuj!.' => 'Невідома дія «Ризикуй!».',
    'Riskuj!' => 'Ризикуй!',

    // Správci sítě / webu
    'Tenhle uzel neexistuje.' => 'Цього вузла не існує.',
    'Tenhle uzel se ještě neobjevil.' => 'Цей вузол ще не з’явився.',
    'Tenhle uzel se opravuje v terminálu Labu.' => 'Цей вузол лагодиться в терміналі Лабораторії.',
    'Neznámá akce Správců sítě.' => 'Невідома дія адміністраторів мережі.',
    'Správci sítě / webu' => 'Адміністратори мережі/вебу',

    // Štafeta
    'Tenhle tým už doběhl.' => 'Ця команда вже фінішувала.',
    'Pomoc jde přivolat až po {m} minutách na úseku.' => 'Допомогу можна покликати аж через {m} хв на етапі.',
    'Na tomhle úseku už jste pomoc použili.' => 'На цьому етапі ви вже використали допомогу.',
    'Kterýkoli spoluhráč teď může pomoct – tým dostal +{s} s.' => 'Тепер допомогти може будь-хто з команди – команда отримала +{s} с.',
    'Tahle štafeta se odpovídá v terminálu Labu.' => 'На цьому етапі естафети відповідають у терміналі Лабораторії.',
    'Neznámá akce štafety.' => 'Невідома дія естафети.',
    'Štafeta' => 'Естафета',

    // Přetahovaná
    'Teď na tebe nečeká žádná otázka.' => 'Зараз на тебе не чекає жодне питання.',
    'Tak rychle to nejde – počkej pár vteřin.' => 'Так швидко не вийде – почекай кілька секунд.',
    'Neznámá akce přetahované.' => 'Невідома дія перетягування.',
    'Přetahovaná' => 'Перетягування',
];
