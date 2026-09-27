<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „events“ (ukrajinština).
 * CTF týden a Incidenty – žákovské UI (viz lang/domains_v59.php, PLAN_I18N.md B6c).
 * Klíč = přesně český text ze zdroje. Vlastník: i18n_events (B6c).
 * Neformální tykání („ти“); revize rodilým mluvčím před zveřejněním (ROADMAP_V59.md).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // arena_v58_events_views.php – CTF týden (žák)
    'CTF týden' => 'Тиждень CTF',
    'Zatím tu není žádná vyhlášená akce. Zeptej se učitele, kdy začne další CTF týden.' => 'Поки що немає оголошеної події. Запитай вчителя, коли почнеться наступний тиждень CTF.',
    'Zpět do Labu' => 'Назад до Лабу',
    'Běží' => 'Триває',
    'Skončil' => 'Завершено',
    'Připravuje se' => 'Готується',
    'týmy' => 'команди',
    'každý sám za sebe' => 'кожен сам за себе',
    ' · žebříček je teď na chvíli zmrazený, ať je konec napínavý' => ' · таблиця лідерів зараз ненадовго заморожена, щоб фінал був цікавішим',
    'CTF týden · {stav}' => 'Тиждень CTF · {stav}',
    '5 kategorií · {mod} · body klesají s počtem řešitelů (1.–3. místo 100 %, dál míň, nikdy pod 40 %) · první řešitel v celé akci dostane bonus ×1,25{zmrazeno}' => '5 категорій · {mod} · бали зменшуються з кількістю тих, хто вже розв\'язав (1–3 місце 100 %, далі менше, але не нижче 40 %) · перший, хто розв\'яже в усій акції, отримає бонус ×1,25{zmrazeno}',
    'Zbývá' => 'Залишилось',
    'místo' => 'місце',
    'bodů' => 'балів',
    'můj tým' => 'моя команда',
    'CTF týden ještě nezačal. Sleduj termín spuštění – dá ti vědět učitel.' => 'Тиждень CTF ще не почався. Стеж за датою старту – вчитель тобі повідомить.',
    'Úlohy podle kategorie' => 'Завдання за категорією',
    'nejdřív: {uloha}' => 'спершу: {uloha}',
    '✓ vyřešeno ({body} b)' => '✓ розв\'язано ({body} б.)',
    '{body} b základ' => '{body} б. база',
    'Terminál se právě připravuje, zkus stránku obnovit.' => 'Термінал саме готується, спробуй оновити сторінку.',
    'Vyber si úlohu' => 'Вибери завдання',
    'Vyber kategorii vlevo a pusť se do první úlohy. Nápovědy jsou {stav_np}, každá stojí {procent} % bodů dané úlohy.' => 'Вибери категорію зліва і берись за перше завдання. Підказки {stav_np}, кожна коштує {procent} % балів завдання.',
    'zapnuté' => 'увімкнені',
    'vypnuté' => 'вимкнені',
    'Žebříček CTF' => 'Таблиця лідерів CTF',
    'Týmy' => 'Команди',
    'Zatím nikdo nemá body.' => 'Поки ніхто не має балів.',
    '{n} b' => '{n} б',
    'Pořadí' => 'Рейтинг',
    'Zatím nikdo nemá body. Buď první!' => 'Поки ніхто не має балів. Будь першим(-ою)!',
    'Terminál je simulace – nic se nespouští doopravdy. Vlajky jsou jen tvoje – kód spolužáka nikdy neprojde.' => 'Термінал – це симуляція, нічого насправді не запускається. Прапорці тільки твої – код однокласника ніколи не спрацює.',

    // arena_v58_events_views.php – Incidenty (žák)
    'Incidenty' => 'Інциденти',
    'Zatím tu není žádná vyhlášená směna. Zeptej se učitele, kdy začne další.' => 'Поки що немає оголошеної зміни. Запитай вчителя, коли почнеться наступна.',
    'Směna běží' => 'Зміна триває',
    'Směna skončila' => 'Зміна завершилася',
    'Incidenty · {stav}' => 'Інциденти · {stav}',
    'Každý scénář má vlastní odpočet {min} minut od chvíle, kdy ho spustíš. Body dostaneš až za postmortem – i nedokončená oprava se počítá, pokud napíšeš, cos zkusil(a).' => 'Кожен сценарій має власний відлік {min} хв від моменту запуску. Бали ти отримаєш лише за розбір інциденту – навіть незавершене виправлення враховується, якщо напишеш, що пробував(ла).',
    'Směna ještě nezačala.' => 'Зміна ще не почалася.',
    'Postmortem odevzdán' => 'Розбір надіслано',
    '+{body} b' => '+{body} б.',
    'bez bodů (nestihl se opravit v čase)' => 'без балів (не встиг(ла) виправити вчасно)',
    'Technicky vyřešeno – napiš postmortem pro body' => 'Технічно розв\'язано – напиши розбір, щоб отримати бали',
    'Čas vypršel – napiš postmortem' => 'Час вийшов – напиши розбір',
    'Rozjeto{pauza}, zbývá {cas}' => 'Розпочато{pauza}, залишилось {cas}',
    ' (pauza)' => ' (пауза)',
    'Ještě nezačato' => 'Ще не почато',
    'Pokračovat' => 'Продовжити',
    'Otevřít scénář' => 'Відкрити сценарій',
    'Terminál je simulace – nic se nespouští doopravdy.' => 'Термінал – це симуляція, нічого насправді не запускається.',

    // arena_v58_events_views.php – Incidenty (detail scénáře, žák)
    'Jakmile klikneš na Spustit, začne běžet tvůj vlastní odpočet {min} minut.' => 'Щойно натиснеш «Почати», запуститься твій власний відлік на {min} хв.',
    'Spustit scénář' => 'Почати сценарій',
    'Pauza' => 'Пауза',
    'Technicky vyřešeno! Teď napiš krátký postmortem, ať dostaneš body.' => 'Технічно розв\'язано! Тепер напиши короткий розбір, щоб отримати бали.',
    'Čas vypršel. Postmortem pořád napiš – i z nedokončené opravy je co se učit.' => 'Час вийшов. Розбір все одно напиши – навіть із незавершеного виправлення є чого повчитися.',
    'Tvůj postmortem' => 'Твій розбір',
    'Příčina:' => 'Причина:',
    'Oprava:' => 'Виправлення:',
    'Prevence:' => 'Профілактика:',
    'Poznámka učitele:' => 'Примітка вчителя:',
    'Příčina (co se pokazilo?)' => 'Причина (що пішло не так?)',
    'Oprava (co jsi udělal(a)?)' => 'Виправлення (що ти зробив(ла)?)',
    'Prevence (jak tomu příště předejít?)' => 'Профілактика (як цього уникнути наступного разу?)',
    'Odeslat postmortem' => 'Надіслати розбір',
    'Zpět na seznam scénářů' => 'Назад до списку сценаріїв',

    // arena_v58_ctf.php – přístup a stav (žák)
    'Tahle CTF akce neexistuje.' => 'Цієї події CTF не існує.',
    'Tahle CTF akce není pro tvou třídu.' => 'Ця подія CTF не для твого класу.',
    'Tahle úloha není součástí CTF týdne.' => 'Це завдання не є частиною тижня CTF.',
    'CTF týden ještě nezačal. Sleduj termín spuštění.' => 'Тиждень CTF ще не почався. Стеж за датою старту.',
    'Ještě nejsi zařazen(a) do týmu – ozvi se učiteli.' => 'Тебе ще не додано до команди – скажи вчителю.',
    'Nejdřív vyřeš úlohu „{uloha}“.' => 'Спершу розв\'яжи завдання «{uloha}».',
    'Žák' => 'Учень',
    'CTF akce nebyla nalezena.' => 'Цю подію CTF не знайдено.',

    // arena_v58_incident.php – pokusy, postmortem, přístup (žák)
    'Směna nebyla nalezena.' => 'Зміну не знайдено.',
    'Směna právě neběží.' => 'Зміна зараз не триває.',
    'Neznámý scénář.' => 'Невідомий сценарій.',
    'Scénář ještě nezačal.' => 'Сценарій ще не почався.',
    'Scénář je už vyřešený.' => 'Сценарій вже розв\'язано.',
    'Pole „{pole}“ je moc krátké – napiš aspoň pár vět.' => 'Поле «{pole}» закоротке – напиши хоча б кілька речень.',
    'Text neprošel kontrolou obsahu.' => 'Текст не пройшов перевірку вмісту.',
    'Scénář jsi ještě nespustil(a).' => 'Сценарій ти ще не запустив(ла).',
    'Postmortem už jsi odevzdal(a).' => 'Розбір ти вже надіслав(ла).',
    'Příčina' => 'Причина',
    'Oprava' => 'Виправлення',
    'Prevence' => 'Профілактика',
    'Tahle incidentní směna neexistuje.' => 'Цієї зміни інцидентів не існує.',
    'Tahle směna není pro tvou třídu.' => 'Ця зміна не для твого класу.',
    'Tenhle scénář není součástí směny.' => 'Цей сценарій не є частиною зміни.',
    'Nejdřív klikni na „Spustit scénář“ na stránce incidentu.' => 'Спершу натисни «Почати сценарій» на сторінці інциденту.',
    'Scénář je pozastavený – nejdřív ho na stránce incidentu obnov.' => 'Сценарій на паузі – спершу віднови його на сторінці інциденту.',
    'Čas na tenhle incident vypršel (15 minut). Napiš postmortem – i bez dokončení opravy se z něj nejvíc naučíš.' => 'Час на цей інцидент вийшов (15 хвилин). Напиши розбір – навіть без завершеного виправлення це найкраще навчить тебе.',

    // arena_v58_events_api.php – chyby JSON API (žák)
    'Použij POST.' => 'Використай POST.',
    'Relace vypršela. Obnov stránku.' => 'Сесія закінчилася. Онови сторінку.',
    'Nejsi přihlášen(a) ke třídě.' => 'Ти не увійшов(-ла) до класу.',
    'Nejdřív si nastav vlastní heslo.' => 'Спершу встанови власний пароль.',
    'Linux Lab je pro tvou třídu vypnutý.' => 'Linux Lab вимкнено для твого класу.',
    'Neplatná směna.' => 'Недійсна зміна.',
    'Neplatný scénář.' => 'Недійсний сценарій.',
    'Text je příliš dlouhý.' => 'Текст задовгий.',
    'Neznámá operace.' => 'Невідома операція.',
    'Nastala chyba, zkus to znovu.' => 'Сталася помилка, спробуй ще раз.',
];
