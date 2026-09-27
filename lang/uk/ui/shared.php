<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – каталог msgid домену «shared» (українська).
 * Спільні файли bootstrap.php, index.php, student_v55.php, sw.js (інтегратор); підготував
 * білдер SHARED-I18N для apply_shared_i18n.php. 12 msgid збігаються з іншими доменами
 * (auth/learn/hubs/study) – тут переклад МАЄ бути ідентичним (аудит стежить за конфліктами).
 * Переклад чекає на перевірку носієм мови (ROADMAP_V59.md, F4).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // --- bootstrap.php: pravidla hesla --------------------------------------
    'Heslo musí mít alespoň {min} znaků.' => 'Пароль має містити щонайменше {min} символів.',
    'Heslo je příliš dlouhé.' => 'Пароль занадто довгий.',
    'Heslo musí obsahovat alespoň jedno písmeno a jednu číslici.' => 'Пароль має містити щонайменше одну літеру й одну цифру.',
    'Bývalé společné školní heslo použít nejde. Vymysli si vlastní.' => 'Колишній спільний шкільний пароль використати не можна. Придумай свій власний.',
    'Tohle heslo je moc běžné a snadno se uhodne. Zkus delší spojení slov a čísel.' => 'Цей пароль занадто поширений і його легко вгадати. Спробуй довше поєднання слів і цифр.',
    'Heslo nesmí obsahovat tvoje jméno ani část e-mailu.' => "Пароль не повинен містити твоє ім'я чи частину електронної адреси.",
    'Nové heslo musí být jiné než jednorázové heslo z kartičky.' => 'Новий пароль має відрізнятися від одноразового пароля з картки.',

    // --- bootstrap.php: e-maily ----------------------------------------------
    'Ahoj {jmeno},' => 'Привіт, {jmeno},',
    'Ahoj,' => 'Привіт,',
    'Pro dokončení registrace do EDUCANET Learning Lab ověř školní e-mail:' => 'Щоб завершити реєстрацію в EDUCANET Learning Lab, підтверди шкільну електронну адресу:',
    'Odkaz platí 24 hodin. Pokud jsi registraci nevytvářel/a, zprávu ignoruj.' => 'Посилання дійсне 24 години. Якщо ти не створював(-ла) цю реєстрацію, проігноруй це повідомлення.',
    'Ověření EDUCANET Learning Lab účtu' => 'Підтвердження облікового запису EDUCANET Learning Lab',
    'Pro nastavení nového hesla do EDUCANET Learning Lab otevři:' => 'Щоб встановити новий пароль для EDUCANET Learning Lab, відкрий:',
    'Odkaz platí {min} minut a funguje jen jednou. Pokud jsi reset nepožadoval/a, zprávu ignoruj.' => 'Посилання дійсне {min} хвилин і працює лише один раз. Якщо ти не запитував(-ла) скидання, проігноруй це повідомлення.',
    'Obnovení hesla EDUCANET Learning Lab' => 'Скидання пароля EDUCANET Learning Lab',

    // --- bootstrap.php: přihlášení Google -------------------------------------
    'Neplatný Google token.' => 'Недійсний токен Google.',
    'Google token nelze přečíst.' => 'Не вдалося прочитати токен Google.',
    'Google token má neplatnou hlavičku.' => 'Токен Google має недійсний заголовок.',
    'Nelze ověřit podpis Google účtu. Zkus přihlášení znovu.' => 'Не вдалося перевірити підпис облікового запису Google. Спробуй увійти знову.',
    'Podpis Google účtu není platný.' => 'Підпис облікового запису Google недійсний.',
    'Neplatný vydavatel Google tokenu.' => 'Недійсний видавець токена Google.',
    'Google token není určen pro tuto aplikaci.' => 'Токен Google не призначений для цього застосунку.',
    'Google token expiroval.' => 'Термін дії токена Google минув.',
    'Použij školní Google Workspace účet @{domain}.' => 'Використай шкільний обліковий запис Google Workspace @{domain}.',

    // --- bootstrap.php: CSRF ---------------------------------------------------
    'Neplatný nebo expirovaný formulář. Obnov stránku a zkus to znovu.' => 'Форма недійсна або застаріла. Онови сторінку і спробуй ще раз.',

    // --- bootstrap.php: stavy odevzdání ---------------------------------------
    'Čeká na hodnocení' => 'Очікує на оцінювання',
    'Ohodnoceno' => 'Оцінено',
    'Vráceno k dopracování' => 'Повернуто на доопрацювання', // shodné s learn.php
    'Dopracování odevzdáno' => 'Доопрацювання здано',
    'Zrušeno' => 'Скасовано',
    'Neznámý stav' => 'Невідомий стан',

    // --- bootstrap.php: levely/odznaky/achievementy ---------------------------
    'Level {level} Milestone' => 'Level {level} Milestone',
    'Dosažen level {level} dlouhodobým studiem.' => 'Досягнуто рівня {level} завдяки тривалому навчанню.',
    'Dosáhni levelu {level}.' => 'Досягни рівня {level}.',
    'Bezchybný test' => 'Бездоганний тест',
    'Dokončen celý startovní test bez jediné chyby.' => 'Пройдено весь вступний тест без жодної помилки.',
    '100 % správných odpovědí v celém startovním testu.' => '100 % правильних відповідей у всьому вступному тесті.',
    'Challenge Distinction' => 'Challenge Distinction',
    'Dobrovolná rozšiřující challenge byla ohodnocena známkou 1.' => 'Добровільний розширений челендж оцінено на 1.',
    'Získej jedničku z dobrovolné Extra challenge.' => 'Отримай 1 за добровільний Extra challenge.',
    'Masterpiece' => 'Masterpiece',
    'Publikované projektové hodnocení alespoň 95 % se známkou 1.' => 'Опубліковане оцінювання проєкту щонайменше 95 % з оцінкою 1.',
    'Projekt alespoň 95 % bodů a výsledná známka 1.' => 'Проєкт щонайменше на 95 % балів і підсумкова оцінка 1.',
    'Triple Distinction' => 'Triple Distinction',
    'Tři publikované projekty za 1 s výsledkem alespoň 90 %.' => 'Три опубліковані проєкти на оцінку 1 з результатом щонайменше 90 %.',
    '3 výborné projektové výsledky (≥90 %, známka 1).' => '3 відмінні результати проєктів (≥90 %, оцінка 1).',
    'Knowledge Grandmaster' => 'Knowledge Grandmaster',
    'Dokončena celá Knowledge Base aktuálního ročníku.' => 'Завершено всю Knowledge Base поточного року.',
    'Dokonči všechny Knowledge materiály aktuálního ročníku.' => "Заверши всі матеріали Knowledge поточного року.",
    'Course Mastery' => 'Course Mastery',
    'Dokončena celá 28lekcová strukturovaná učební cesta aktuálního ročníku.' => 'Завершено весь структурований навчальний шлях із 28 уроків поточного року.',
    'Dokonči lekce 1–28 aktuálního ročníku.' => 'Заверши уроки 1–28 поточного року.',
    'Prestige Certified' => 'Prestige Certified',
    'Úspěšně dokončena první prestižní certifikační zkouška.' => 'Успішно складено перший престижний сертифікаційний іспит.',
    'Splň libovolnou prestižní zkoušku.' => 'Склади будь-який престижний іспит.',
    'Perfect Certification' => 'Perfect Certification',
    'Prestižní zkouška dokončena bez jediné chyby.' => 'Престижний іспит складено без жодної помилки.',
    'Získej 100 % v libovolné prestižní zkoušce.' => 'Отримай 100 % у будь-якому престижному іспиті.',
    'Double Certified' => 'Double Certified',
    'Úspěšně dokončeny dvě různé prestižní zkoušky.' => 'Успішно складено два різні престижні іспити.',
    'Splň dvě odlišné prestižní zkoušky.' => 'Склади два різні престижні іспити.',
    'Momentum' => 'Momentum',
    'Specialista' => 'Спеціаліст',
    'Expert' => 'Експерт', // shodné s hubs.php
    'Master' => 'Майстер',
    'Elite' => 'Еліта',
    'Architekt' => 'Архітектор',
    'Mentor' => 'Ментор',
    'Vanguard' => 'Авангард',
    'Legenda' => 'Легенда',
    'EDUCANET Icon' => 'EDUCANET Icon',
    'Rozjezd' => 'Старт',
    'Získej prvních 250 XP.' => 'Отримай перші 250 XP.',
    'Tisícovka' => 'Тисяча',
    'Nasbírej 1 000 XP.' => 'Набери 1000 XP.',
    'Tah na branku' => 'Удар по воротах',
    'Nasbírej 2 500 XP.' => 'Набери 2500 XP.',
    'Vytrvalec' => 'Витривалець',
    'Nasbírej 5 000 XP.' => 'Набери 5000 XP.',
    'Znalostní start' => 'Знаннєвий старт',
    'Dokonči 5 Knowledge lekcí.' => 'Заверши 5 уроків Knowledge.',
    'Knowledge Explorer' => 'Knowledge Explorer',
    'Dokonči 15 Knowledge lekcí.' => 'Заверши 15 уроків Knowledge.',
    'Deep Learner' => 'Deep Learner',
    'Dokonči 30 Knowledge lekcí.' => 'Заверши 30 уроків Knowledge.',
    'Experimentátor' => 'Експериментатор',
    'Dokonči 5 interaktivních simulací.' => 'Заверши 5 інтерактивних симуляцій.',
    'Lab Regular' => 'Lab Regular',
    'Dokonči 15 interaktivních simulací.' => 'Заверши 15 інтерактивних симуляцій.',
    'Pět aktivních dnů' => "П'ять активних днів",
    'Studuj v pěti různých dnech.' => "Навчайся впродовж п'яти різних днів.",
    'Studijní rytmus' => 'Навчальний ритм',
    'Studuj v deseti různých dnech.' => 'Навчайся впродовж десяти різних днів.',
    'Konzistence' => 'Послідовність',
    'Studuj ve dvaceti různých dnech.' => 'Навчайся впродовж двадцяти різних днів.',
    'Diagnostika hotová' => 'Діагностику завершено',
    'Dokonči celý startovní test.' => 'Заверши весь вступний тест.',
    'První hodnocený projekt' => 'Перший оцінений проєкт',
    'Získej první publikované projektové hodnocení.' => 'Отримай перше опубліковане оцінювання проєкту.',
    'Projektová série' => 'Серія проєктів',
    'Získej tři publikovaná projektová hodnocení.' => 'Отримай три опубліковані оцінювання проєктів.',
    'Výborný projekt' => 'Відмінний проєкт',
    'Získej z projektu známku 1.' => 'Отримай оцінку 1 за проєкт.',
    'Tři lekce za tebou' => 'Три уроки позаду',
    'Dokonči tři navazující lekce.' => 'Заверши три послідовні уроки.',
    'První třetina' => 'Перша третина',
    'Dokonči šest navazujících lekcí.' => 'Заверши шість послідовних уроків.',
    'První polovina kurzu' => 'Перша половина курсу',
    'Dokonči devět lekcí a uzavři první polovinu cesty.' => "Заверши дев'ять уроків і завершиш першу половину шляху.",
    'Druhá etapa' => 'Другий етап',
    'Dokonči dvanáct lekcí.' => 'Заверши дванадцять уроків.',
    'Core Path' => 'Core Path',
    'Dokonči prvních osmnáct lekcí ročníku.' => 'Заверши перших вісімнадцять уроків року.',
    'Deep Path' => 'Deep Path',
    'Dokonči dvacet čtyři strukturovaných lekcí.' => 'Заверши двадцять чотири структуровані уроки.',
    'Celá cesta' => 'Весь шлях',
    'Dokonči všech 28 strukturovaných lekcí ročníku.' => 'Заверши всі 28 структурованих уроків року.',
    'Study Buddy' => 'Study Buddy',
    'Propoj se s prvním spolužákem v rámci třídy.' => "Зв'яжись із першим однокласником у класі.",
    'Studijní kruh' => 'Навчальне коло',
    'Měj pět vzájemně potvrzených propojení se spolužáky.' => "Май п'ять взаємно підтверджених зв'язків з однокласниками.",
    'Team Player' => 'Team Player',
    'Staň se členem prvního uzamčeného projektového týmu.' => 'Стань учасником першої заблокованої проєктної команди.',
    'Team Builder' => 'Team Builder',
    'Založ lobby, které se promění v oficiální projektový tým.' => 'Створи лобі, яке перетвориться на офіційну проєктну команду.',

    // --- bootstrap.php: výjimky tým/lobby/profil ------------------------------
    'Tým musí mít alespoň dva studenty.' => 'Команда повинна мати щонайменше двох студентів.',
    'Student může být v jednom projektu pouze v jednom týmu. Upravte členství existujícího týmu.' => 'Студент може бути в одному проєкті лише в одній команді. Зміни склад наявної команди.',
    'Tým už má historii v Project Workspace. Kvůli auditu jej nemažte; upravte členství nebo vytvořte nový tým.' => 'Команда вже має історію в Project Workspace. Не видаляй її через аудит; зміни склад або створи нову команду.',
    'Studentský profil nebyl nalezen.' => 'Профіль студента не знайдено.', // shodné s hubs.php
    'Můžeš upravovat pouze svůj profil.' => 'Ти можеш редагувати лише свій профіль.',
    'Neplatná žádost o propojení.' => "Недійсний запит на зв'язок.",
    'Propojit lze pouze spolužáky ze stejné třídy.' => 'Зв\'язатися можна лише з однокласниками з того самого класу.',
    'Tato žádost už není dostupná.' => 'Цей запит більше не доступний.',
    'Toto propojení ti nepatří.' => "Цей зв'язок тобі не належить.",
    'Neplatná varianta rozdělení.' => 'Недійсний варіант поділу.',
    'Tento projekt není skupinový.' => 'Цей проєкт не є груповим.',
    'Student nebyl nalezen.' => 'Студента не знайдено.',
    'U tohoto projektu už jsi v týmu nebo aktivním lobby.' => 'У цьому проєкті ти вже в команді або в активному лобі.',
    'Vyber jednu z doporučených velikostí týmu.' => 'Обери один із рекомендованих розмірів команди.',
    'Zadej invite kód.' => 'Введи код запрошення.',
    'U tohoto projektu už jsi v týmu nebo lobby.' => 'У цьому проєкті ти вже в команді або в лобі.',
    'Toto lobby už je uzavřené.' => 'Це лобі вже закрите.',
    'Lobby je plné.' => 'Лобі заповнене.',
    'Invite kód nebyl nalezen pro tento projekt a třídu.' => 'Код запрошення для цього проєкту й класу не знайдено.',
    'Do tohoto lobby nepatříš.' => 'Ти не належиш до цього лобі.',
    'Lobby už neexistuje.' => 'Лобі більше не існує.',
    'Invite kód může změnit jen zakladatel lobby.' => 'Змінити код запрошення може лише засновник лобі.',
    'Lobby nebylo nalezeno.' => 'Лобі не знайдено.',
    'Tým může uzamknout jen zakladatel lobby.' => 'Заблокувати команду може лише засновник лобі.',
    'Pro tým jsou potřeba alespoň dva studenti.' => 'Для команди потрібно щонайменше двох студентів.',
    'Před uzamčením naplň zvolenou kapacitu týmu ({members} / {capacity}).' => 'Перед блокуванням заповни обрану місткість команди ({members} / {capacity}).',

    // --- index.php -------------------------------------------------------------
    'Nejdřív si nastav vlastní heslo.' => 'Спершу встанови власний пароль.', // shodné s auth/events/games/teamgames.php

    // --- student_v55.php: navigace (trm, pole zůstává česky) -------------------
    'Dnešní hodina' => 'Сьогоднішнє заняття', // shodné s auth/learn.php
    'Materiály' => 'Матеріали', // shodné s learn/study.php
    'Kalendář' => 'Календар', // shodné s auth/learn.php
    'Výsledky' => 'Результати', // shodné s auth/hubs/learn.php
    'Profil' => 'Профіль', // shodné s auth/hubs.php
    'Linux Lab' => 'Linux Lab',
    'Závod!' => 'Перегони!',
    'Hra!' => 'Гра!',

    // --- student_v55.php: postup u odznaku --------------------------------------
    'Level {level} z {target}' => 'Рівень {level} з {target}',

    // --- student_v55.php: rozšiřující zadání ------------------------------------
    'Rozšiřující zadání' => 'Розширене завдання',
    'Máš dost času na celé rozšiřující zadání i na jeho obhájení.' => 'У тебе достатньо часу на все розширене завдання і його захист.',
    'Zkrácené rozšiřující zadání' => 'Скорочене розширене завдання',
    'Zadání je zkrácené tak, abys ho stihl/a do konce hodiny.' => 'Завдання скорочено так, щоб ти встиг(-ла) закінчити його до кінця заняття.',
    'Krátký test' => 'Короткий тест',
    'Do konce hodiny zbývá málo času, dostáváš krátkou verzi.' => 'До кінця заняття залишилося мало часу, тому ти отримуєш коротку версію.',
    'Mikroúkol' => 'Мікрозавдання',
    'Stihneš jednu otázku na rozmyšlenou.' => 'Ти встигнеш одне питання на роздуми.',
    'Dnes už jen odevzdej' => 'Сьогодні просто здай',
    'Do konce hodiny zbývá méně než {min} minut. Bonus se dnes nezadává.' => 'До кінця заняття залишилося менше ніж {min} хвилин. Бонус сьогодні не задається.',
    'Varianta navíc' => 'Додатковий варіант',
    'Vytvoř druhou variantu dnešního výstupu s jiným kontrastem nebo jinou hierarchií a napiš, která funguje líp a proč.' => 'Створи другий варіант сьогоднішнього результату з іншим контрастом або іншою ієрархією і напиши, який працює краще і чому.',
    'Mobilní verze' => 'Мобільна версія',
    'Uprav výstup na šířku 375 px tak, aby zůstala čitelná hierarchie i hlavní CTA.' => 'Адаптуй результат до ширини 375 px так, щоб ієрархія і головний CTA залишилися читабельними.',
    'Kontrola přístupnosti' => 'Перевірка доступності',
    'Ověř kontrast textu (min. 4,5:1) a velikost klikacích prvků; zapiš, co jsi musel/a změnit.' => 'Перевір контраст тексту (мін. 4,5:1) і розмір клікабельних елементів; запиши, що довелося змінити.',
    'Krátká obhajoba' => 'Короткий захист',
    'Ve 3 větách vysvětli hlavní designové rozhodnutí tak, aby mu rozuměl i zadavatel bez znalosti grafiky.' => 'У 3 реченнях поясни головне дизайнерське рішення так, щоб його зрозумів навіть замовник без знань графіки.',
    'Druhý scénář' => 'Другий сценарій',
    'Zopakuj dnešní postup na jiném zadání (jiná síť, jiná služba) a zapiš rozdíly v konfiguraci.' => 'Повтори сьогоднішній алгоритм на іншому завданні (інша мережа, інша служба) і запиши відмінності в конфігурації.',
    'Diagnostika chyby' => 'Діагностика помилки',
    'Rozbij si vlastní konfiguraci jednou změnou, popiš příznak, najdi ho a oprav. Zapiš postup krok za krokem.' => 'Зламай власну конфігурацію однією зміною, опиши симптом, знайди його і виправ. Запиши процес крок за кроком.',
    'Důkaz funkčnosti' => 'Доказ працездатності',
    'Dolož výstup příkazu nebo screenshot, který prokazuje, že řešení opravdu funguje.' => 'Додай результат виконання команди або скриншот, який доводить, що рішення справді працює.',
    'Ve 3 větách vysvětli, proč jsi zvolil/a právě tohle řešení a co by se stalo při jiné volbě.' => 'У 3 реченнях поясни, чому ти обрав(-ла) саме це рішення і що сталося б за іншого вибору.',
    'Téma: {tema}.' => 'Тема: {tema}.',

    // --- student_v55.php: úkoly / plán hodiny ------------------------------------
    'Vstupní diagnostika' => 'Вхідна діагностика',
    'Krátký test bez známky. Ukáže, co už umíš, a nastaví ti tempo.' => 'Короткий тест без оцінки. Він покаже, що ти вже вмієш, і задасть темп.',
    'Projdi tutoriál lekce' => 'Пройди туторіал уроку', // shodné s auth.php
    'Animovaná ukázka, vysvětlení a interaktivní úkoly za body.' => 'Анімований приклад, пояснення та інтерактивні завдання на бали.',
    'Hlavní praktický blok' => 'Головний практичний блок',
    'Vytvoř dnešní grafický výstup podle zadání a ulož ho.' => 'Створи сьогоднішній графічний результат за завданням і збережи його.',
    'Odevzdej výstup' => 'Здай результат',
    'Vlož odkaz na Canvu/Figmu a krátce popiš svá rozhodnutí.' => 'Додай посилання на Canva/Figma і коротко опиши свої рішення.',
    'Projdi praktickou laboratoř dnešní lekce a dokonči všechny kroky.' => 'Пройди практичну лабораторну роботу сьогоднішнього уроку і виконай усі кроки.',
    'Odevzdej důkaz' => 'Здай доказ',
    'Vlož výstup příkazu nebo screenshot a krátce popiš postup.' => 'Додай результат команди або скриншот і коротко опиши процес.',
    'Krátký test bez známky. Ukáže ti, co už umíš, a podle toho se přizpůsobí tempo.' => 'Короткий тест без оцінки. Він покаже тобі, що ти вже вмієш, і відповідно підлаштує темп.',
    'Spustit test' => 'Почати тест', // shodné s auth.php
    'Test už mám hotový' => 'Я вже склав(-ла) тест',
    'Tutoriál lekce {n}' => 'Туторіал уроку {n}',
    'Animovaná ukázka situace, vysvětlení a interaktivní úkoly za body.' => 'Анімований приклад ситуації, пояснення та інтерактивні завдання на бали.',
    'Otevřít tutoriál' => 'Відкрити туторіал',
    'Samostatná práce' => 'Самостійна робота',
    'Odškrtávej si body, jak postupuješ. Postup se ti průběžně ukládá.' => 'Познач пункти, коли їх виконуєш. Прогрес зберігається безперервно.',
    'Odevzdání' => 'Здача', // shodné s hubs/learn.php
    'Krátký popis a odkaz na práci. Učitel ti dá známku a body.' => 'Короткий опис і посилання на роботу. Вчитель поставить тобі оцінку й бали.',
];
