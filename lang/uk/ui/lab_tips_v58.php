<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „lab_tips_v58“ (ukrajinština, tykání).
 *
 * Nápovědy simulátoru Linuxu ($w->tip(...)) pro nové příkazy v58 (archivy, cron, extra nástroje,
 * ImageMagick/exiftool/rename, git, ssh/scp, uživatelé a práva). Výstup simulovaných programů
 * (stdout/stderr) zůstává anglicky beze změny – tento katalog překládá jen text nápovědy „💡“.
 * Příkazy, volby, cesty a jména proměnných zůstávají v {param} beze změny.
 * Před zveřejněním zkontroluje rodilý mluvčí (viz ROADMAP_V59.md).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // linux_v58_cmd_git.php
    'Tady není žádný repozitář gitu. Přejdi do složky projektu (např. cd ~/web – podívej se ls), nebo založ nový: git init.' => 'Тут немає репозиторію git. Перейди в папку проєкту (наприклад, cd ~/web – подивись ls) або створи новий: git init.',
    'V laboratoři není síť ani vzdálený server. Historii zkoumej lokálně: git log, git show, git diff.' => 'У лабораторії немає мережі чи віддаленого сервера. Досліджуй історію локально: git log, git show, git diff.',
    'Simulace gitu umí: {seznam}.' => 'Симуляція git підтримує: {seznam}.',
    'Překlep? Zkus git {similar}.' => 'Одруківка? Спробуй git {similar}.',
    'Seznam podpříkazů vypíše git help, podrobnosti man git.' => 'git help покаже список підкоманд, man git – подробиці.',
    'Git potřebuje vědět, kdo změnu dělá: git config --global user.name "Tvé Jméno" a git config --global user.email tvuj@email.cz' => 'Git має знати, хто робить зміну: git config --global user.name "Твоє Імʼя" і git config --global user.email tvij@email.cz',
    'Tuhle volbu git {sub} nezná (nebo ji simulace nepodporuje). Přehled: git {sub} -h nebo man git.' => 'git {sub} не знає цю опцію (або симуляція її не підтримує). Огляд: git {sub} -h або man git.',
    'Git nezná revizi ani soubor „{arg}“. Commity ukáže git log --oneline, větve git branch -a, značky git tag. Smazaný soubor zadej za --: git log -- {arg}' => 'Git не знає ревізії чи файлу „{arg}“. Коміти покаже git log --oneline, гілки git branch -a, теги git tag. Видалений файл вкажи після --: git log -- {arg}',
    'Klíč má tvar sekce.jméno, např. user.name nebo user.email.' => 'Ключ має вигляд секція.імʼя, наприклад user.name або user.email.',
    'Skutečný git by teď otevřel editor pro zprávu commitu. V simulaci ji napiš rovnou: git commit -m "Co jsem změnil(a)"' => 'Справжній git зараз відкрив би редактор для повідомлення коміту. У симуляції напиши його одразу: git commit -m "Що я змінив(ла)"',

    // linux_v58_cmd_git_more.php
    'Neuložené změny by přepnutí přepsalo. Ulož je commitem (git commit -am "…"), odlož (git stash), nebo zahoď (git restore <soubor>) – a přepni znovu.' => 'Перемикання перезапише твої незбережені зміни. Збережи їх комітом (git commit -am "…"), відклади (git stash) або відкинь (git restore <файл>) – і перемкнись знову.',
    'Větev „{name}“ neexistuje. Seznam větví: git branch -a; novou založíš git switch -c {name}.' => 'Гілка „{name}“ не існує. Список гілок: git branch -a; нову створиш git switch -c {name}.',
    'Skutečný git by otevřel editor pro popis značky. Zadej ho rovnou: git tag -a {name} -m "Popis vydání"' => 'Справжній git відкрив би редактор для опису тегу. Введи його одразу: git tag -a {name} -m "Опис випуску"',
    'Nejdřív ulož nebo zahoď rozdělané změny (git status), pak zkus git stash {sub} znovu.' => 'Спочатку збережи або відкинь незавершені зміни (git status), потім спробуй git stash {sub} знову.',
    'Soubor se změnil v commitu i ve stashi. Simulace konflikt neslučuje – obsah stashe ukáže git stash show -p.' => 'Файл змінився і в коміті, і у stash. Симуляція конфлікти не зливає – вміст stash покаже git stash show -p.',
    'V laboratoři není internet. Klonuj z místního repozitáře ve složce, např.: git clone /srv/git/web.git' => 'У лабораторії немає інтернету. Клонуй із локального репозиторію в папці, наприклад: git clone /srv/git/web.git',
    'Na cestě „{url}“ repozitář není. Zkontroluj ji (ls /srv/git).' => 'За шляхом „{url}“ репозиторію немає. Перевір його (ls /srv/git).',

    // linux_v58_cmd_cron.php
    'crontab -e normálně otevře plán v $EDITOR a po uložení ho zkontroluje. V laboratoři uprav plán a ulož ho; nebo naplánuj z připraveného souboru: crontab muj-plan.txt.' => 'crontab -e зазвичай відкриває розклад у $EDITOR і перевіряє його після збереження. У лабораторії відредагуй розклад і збережи його; або встанови з готового файлу: crontab muj-plan.txt.',
    'Chyba v plánu: {chyba}. Formát řádku: „minuta hodina den měsíc den_v_týdnu  příkaz".' => 'Помилка в розкладі: {chyba}. Формат рядка: „хвилина година день місяць день_тижня  команда".',
    'crontab -l vypíše plán, crontab -e ho upraví, crontab soubor.txt ho nastaví z připraveného souboru.' => 'crontab -l покаже розклад, crontab -e відредагує його, crontab soubor.txt встановить його з готового файлу.',
    'Použij timewarp +30m, +2h, +1d nebo přesný čas timewarp 06:25 – posune simulované hodiny a spustí naplánované úlohy cronu.' => 'Використай timewarp +30m, +2h, +1d або точний час timewarp 06:25 – це переведе симульований годинник вперед і запустить заплановані завдання cron.',
    'Zadej posun jako +30m, +2h, +90s, +1d, nebo cílový čas HH:MM (např. 06:25).' => 'Введи зсув як +30m, +2h, +90s, +1d, або цільовий час ГГ:ХХ (наприклад, 06:25).',

    // linux_v58_cmd_extra_sys.php
    'whereis zná -b (jen programy), -m (jen manuálové stránky). → man whereis' => 'whereis розуміє -b (лише програми), -m (лише сторінки довідки). → man whereis',
    'pgrep najde čísla procesů podle jména: pgrep ssh, s názvy pgrep -l ssh. → man pgrep' => 'pgrep знаходить номери процесів за назвою: pgrep ssh, з назвами pgrep -l ssh. → man pgrep',
    'Napiš, co hledáš: pgrep nginx (čísla procesů), pgrep -a nginx (i s příkazem). → man pgrep' => 'Напиши, що шукаєш: pgrep nginx (номери процесів), pgrep -a nginx (і з командою). → man pgrep',
    'Připojování disků smí jen správce (sudo mount …). Seznam připojených svazků vypíše samotné mount nebo df -h.' => 'Монтувати диски може лише адміністратор (sudo mount …). Список змонтованих томів покаже саме mount або df -h.',
    'V laboratoři není žádný další disk ani flash disk k připojení – co existuje, ukáže lsblk.' => 'У лабораторії немає іншого диска чи флешки для монтування – що існує, покаже lsblk.',
    'watch -n 5 příkaz spouští příkaz každých 5 s. → man watch' => 'watch -n 5 команда запускає команду кожні 5 с. → man watch',
    'watch v simulaci proběhne jen jednou. Skutečný watch by příkaz spouštěl každých {sec} s a obnovoval obrazovku, dokud ho neukončíš Ctrl+C.' => 'У симуляції watch виконується лише раз. Справжній watch запускав би команду кожні {sec} с і оновлював екран, доки ти не завершиш його Ctrl+C.',
    'Mezi čísly a operátory musí být mezery (expr 3 + 4). Hvězdičku a závorky chraň před shellem: expr 3 \\* 4, expr \\( 1 + 2 \\) \\* 3. → man expr' => 'Між числами й операторами мають бути пробіли (expr 3 + 4). Захисти зірочку та дужки від оболонки: expr 3 \\* 4, expr \\( 1 + 2 \\) \\* 3. → man expr',
    'expr počítá jen s celými čísly; na desetinná čísla použij bc. → man expr' => 'expr працює лише з цілими числами; для дробових чисел використай bc. → man expr',

    // linux_v58_cmd_extra.php
    'Příkaz dostal víc argumentů, než umí. Správné použití ukáže man {name}.' => 'Команда отримала більше аргументів, ніж уміє обробити. Правильне використання покаже man {name}.',
    '„{path}“ není symbolický odkaz (v laboratoři odkazy nejsou). Celou cestu vypíše readlink -f {path} nebo realpath {path}.' => '„{path}“ не є символьним посиланням (у лабораторії посилань немає). Повний шлях покаже readlink -f {path} або realpath {path}.',
    'seq čeká čísla: seq 5, seq 2 10, seq 0 5 100 (od, krok, do). → man seq' => 'seq очікує числа: seq 5, seq 2 10, seq 0 5 100 (від, крок, до). → man seq',
    'Simulace vypíše nejvýš {max} čísel.' => 'Симуляція виводить щонайбільше {max} чисел.',
    'Simulace zamíchá nejvýš {max} čísel.' => 'Симуляція перемішує щонайбільше {max} чисел.',
    'shuf -r by bez -n psal donekonečna – simulace skončí po 1000 řádcích.' => 'shuf -r без -n писав би нескінченно – симуляція зупиниться після 1000 рядків.',
    '{name} porovnává dva seřazené soubory: {name} a.txt b.txt (seřadíš je příkazem sort). → man {name}' => '{name} порівнює два відсортовані файли: {name} a.txt b.txt (відсортуй їх командою sort). → man {name}',
    'comm potřebuje seřazené vstupy. Seřaď je: sort a.txt > a2.txt (nebo comm <(sort a) <(sort b) v bashi). → man comm' => 'comm потребує відсортованих вхідних даних. Відсортуй їх: sort a.txt > a2.txt (або comm <(sort a) <(sort b) у bash). → man comm',
    'join potřebuje oba soubory seřazené podle spojovacího pole (sort -k1). → man join' => 'join потребує, щоб обидва файли були відсортовані за полем зʼєднання (sort -k1). → man join',
    'Výstupních souborů by bylo moc (simulace jich vytvoří nejvýš {max}). Zvětši kusy: split -l 500 nebo -b 1M. → man split' => 'Вихідних файлів було б забагато (симуляція створює щонайбільше {max}). Збільш шматки: split -l 500 або -b 1M. → man split',
    'yes by psal donekonečna (do Ctrl+C) – simulace skončila po {n} řádcích. Typicky se posílá rourou: yes | příkaz.' => 'yes писав би нескінченно (до Ctrl+C) – симуляція зупинилася після {n} рядків. Зазвичай його передають через конвеєр: yes | команда.',

    // linux_v58_cmd_extra_calc.php
    'bc zná hlavně -l (matematická knihovna, 20 desetinných míst). → man bc' => 'bc розуміє насамперед -l (математична бібліотека, 20 десяткових знаків). → man bc',
    "bc v simulaci nečeká na klávesnici – pošli mu výraz rourou: echo '2+3' | bc, desetinná místa: echo 'scale=2; 10/3' | bc → man bc" => "У симуляції bc не чекає на клавіатуру – передай йому вираз через конвеєр: echo '2+3' | bc, десяткові знаки: echo 'scale=2; 10/3' | bc → man bc",
    'bc zná čísla, + - * / % ^, proměnné (malými písmeny), scale=, sqrt(). Velká písmena jsou jen číslice A–F. → man bc' => 'bc розуміє числа, + - * / % ^, змінні (малими літерами), scale=, sqrt(). Великі літери – це лише цифри A–F. → man bc',
    "Zkontroluj výraz – např. chybějící číslo za operátorem nebo závorku. Příklad: echo 'scale=3; 22/7' | bc → man bc" => "Перевір вираз – наприклад, пропущене число після оператора або дужку. Приклад: echo 'scale=3; 22/7' | bc → man bc",

    // linux_v58_cmd_media_im.php
    '„{name}“ není obrázek (nebo je poškozený). Co v souboru doopravdy je, ukáže file {name}. → man identify' => '„{name}“ – не зображення (або він пошкоджений). Що насправді у файлі, покаже file {name}. → man identify',
    'identify zná hlavně -verbose a -format "%w x %h". → man identify' => 'identify розуміє насамперед -verbose і -format "%w x %h". → man identify',
    'Tuhle volbu simulace ImageMagicku nezná (nebo má překlep). Časté volby: -resize, -quality, -strip, -crop, -rotate, -thumbnail. → man {tool}' => 'Симуляція ImageMagick не знає цю опцію (або в ній одруківка). Поширені опції: -resize, -quality, -strip, -crop, -rotate, -thumbnail. → man {tool}',
    'Volba {raw} potřebuje hodnotu, např. {example}. → man {tool}' => 'Опція {raw} потребує значення, наприклад {example}. → man {tool}',
    'Geometrie se píše např. 50%, 800x600, 800x nebo x600.' => 'Геометрія записується, наприклад, 50%, 800x600, 800x або x600.',
    'Okraj se píše v pixelech, např. {name} 10 nebo 10x5.' => 'Рамка задається в пікселях, наприклад {name} 10 або 10x5.',
    'Úhel se píše ve stupních, např. -rotate 90.' => 'Кут задається у градусах, наприклад -rotate 90.',
    'Barevný prostor je např. Gray nebo sRGB.' => 'Колірний простір – наприклад, Gray або sRGB.',
    'Typ obrázku je např. Grayscale, TrueColor nebo Palette.' => 'Тип зображення – наприклад, Grayscale, TrueColor або Palette.',
    'Počet barev je celé číslo, např. -colors 16.' => 'Кількість кольорів – ціле число, наприклад -colors 16.',
    'Bitová hloubka je obvykle 8 nebo 16.' => 'Бітова глибина зазвичай 8 або 16.',
    'Hodnota pro -alpha je např. off, remove nebo on.' => 'Значення для -alpha – наприклад, off, remove або on.',
    'Barva se píše např. white, #ff8800 nebo "rgb(255,136,0)".' => 'Колір записується, наприклад, як white, #ff8800 або "rgb(255,136,0)".',
    '{tip} → man {tool}' => '{tip} → man {tool}',
    'Kvalita je číslo 1–100, např. -quality 80.' => 'Якість – це число 1–100, наприклад -quality 80.',
    'Hustota se píše v DPI, např. -density 300.' => 'Щільність задається у DPI, наприклад -density 300.',
    'Velikost plátna se píše ŠÍŘKAxVÝŠKA, např. -size 800x600.' => 'Розмір полотна записується ШИРИНАxВИСОТА, наприклад -size 800x600.',
    'Jednotky jsou PixelsPerInch nebo PixelsPerCentimeter.' => 'Одиниці – PixelsPerInch або PixelsPerCentimeter.',
    '-trim ořezává okraje podle barvy pixelů – simulace pixely nemá, rozměry proto zůstanou stejné.' => '-trim обрізає краї за кольором пікселів – у симуляції пікселів немає, тому розміри залишаться незмінними.',
    'Výřez se píše ŠÍŘKAxVÝŠKA+X+Y, např. -crop 800x600+100+50.' => 'Обрізка записується ШИРИНАxВИСОТА+X+Y, наприклад -crop 800x600+100+50.',
    'Bez +X+Y dělí -crop obrázek na dlaždice – tady by jich bylo moc. Nezapomněl(a) jsi posun, např. -crop 800x600+0+0?' => 'Без +X+Y -crop розбиває зображення на плитки – тут їх було б забагато. Ти не забув(ла) зсув, наприклад -crop 800x600+0+0?',
    'Debian ImageMagicku zápis PDF/PS z bezpečnostních důvodů zakazuje (/etc/ImageMagick-6/policy.xml). Na tisk ulož PNG nebo JPEG.' => 'Debian забороняє ImageMagick записувати PDF/PS з міркувань безпеки (/etc/ImageMagick-6/policy.xml). Для друку збережи PNG або JPEG.',
    'Z rastru (pixelů) se vektor jen tak neudělá – vektorizaci dělá např. Inkscape (Trace Bitmap).' => 'З растру (пікселів) вектор просто так не зробиш – векторизацію виконує, наприклад, Inkscape (Trace Bitmap).',
    'Tenhle formát simulace neumí zapsat. Zkus .jpg, .png, .webp, .gif, .ico, .bmp nebo .tif.' => 'Симуляція не вміє записувати цей формат. Спробуй .jpg, .png, .webp, .gif, .ico, .bmp або .tif.',
    'Ikona smí mít nejvýš 256×256 px. Nejdřív ji zmenši: -resize 32x32' => 'Іконка може мати щонайбільше 256×256 px. Спочатку зменш її: -resize 32x32',
    'Složku pro -path musíš nejdřív vytvořit: mkdir -p {path}' => 'Папку для -path потрібно спочатку створити: mkdir -p {path}',
    'Na konci příkazu musí být jméno výstupního souboru: convert vstup.jpg -resize 50% vystup.jpg → man convert' => 'У кінці команди має бути назва вихідного файлу: convert vstup.jpg -resize 50% vystup.jpg → man convert',
    'convert potřebuje vstupní i výstupní soubor: convert vstup.png vystup.jpg → man convert' => 'convert потребує вхідного і вихідного файлу: convert vstup.png vystup.jpg → man convert',
    'mogrify potřebuje jména souborů, např. mogrify -resize 50% *.jpg → man mogrify' => 'mogrify потребує назв файлів, наприклад mogrify -resize 50% *.jpg → man mogrify',
    'Simulace zpracuje najednou nejvýš {max} souborů.' => 'Симуляція обробляє одночасно щонайбільше {max} файлів.',
    'Příkaz magick {sub} simulace nemá. Umí: magick vstup [volby] výstup, magick identify, magick mogrify. → man magick' => 'Команди magick {sub} у симуляції немає. Вона підтримує: magick вхід [опції] вихід, magick identify, magick mogrify. → man magick',

    // linux_v58_cmd_media_tools.php
    'exiftool nechal zálohu původního souboru s příponou _original – i v ní jsou stará metadata. Bez zálohy: -overwrite_original.' => 'exiftool залишив резервну копію оригінального файлу з розширенням _original – у ній теж старі метадані. Без резервної копії: -overwrite_original.',
    'exiftool vypíše metadata: exiftool foto.jpg. Polohu GPS smažeš: exiftool -gps:all= foto.jpg → man exiftool' => 'exiftool покаже метадані: exiftool foto.jpg. GPS-позицію видалиш: exiftool -gps:all= foto.jpg → man exiftool',
    'Značka se píše např. -Make, -GPSLatitude nebo -gps:all; zápis -Artist="Jméno". → man exiftool' => 'Тег пишеться, наприклад, -Make, -GPSLatitude або -gps:all; запис -Artist="Імʼя". → man exiftool',
    'Na konec přidej soubor nebo složku, např. exiftool -gps:all fotky/ → man exiftool' => 'У кінець додай файл або папку, наприклад exiftool -gps:all fotky/ → man exiftool',
    'rename zná hlavně -n (jen ukázat, co by udělal), -v (vypsat) a -f (přepsat existující). → man rename' => 'rename розуміє насамперед -n (лише показати, що б зробив), -v (вивести) і -f (перезаписати наявний). → man rename',
    "Použití: rename 's/staré/nové/' soubory – nejdřív zkus s -n. → man rename" => "Використання: rename 's/старе/нове/' файли – спочатку спробуй з -n. → man rename",
    "Výraz pro rename se píše jako v Perlu: 's/co/čím/' (g = všechny výskyty, i = bez ohledu na velikost). Dej ho do apostrofů. → man rename" => "Вираз для rename пишеться як у Perl: 's/що/на що/' (g = усі входження, i = без урахування регістру). Візьми його в лапки. → man rename",
    'Regulární výraz má chybu (třeba neuzavřenou závorku). → man rename' => 'У регулярному виразі є помилка (наприклад, незакрита дужка). → man rename',

    // linux_v58_cmd_archive.php
    'tar používá kombinaci voleb: -c vytvoř, -x rozbal, -t vypiš, -z gzip, -j bzip2, -v ukaž, -f soubor.' => 'tar використовує комбінацію опцій: -c створити, -x розпакувати, -t показати список, -z gzip, -j bzip2, -v показати, -f файл.',
    'Zadej jméno archivu volbou -f, např. tar -czf zaloha.tar.gz slozka.' => 'Вкажи назву архіву опцією -f, наприклад tar -czf zaloha.tar.gz slozka.',
    'Soubor nevypadá jako tar. Zjisti formát příkazem file {file} a použij odpovídající nástroj.' => 'Файл не схожий на tar. Визнач формат командою file {file} і використай відповідний інструмент.',
    '{name} potřebuje jméno souboru, např. {example}.' => '{name} потребує назви файлу, наприклад {example}.',
    'Soubor není ve formátu gzip. Ověř ho příkazem file {file}.' => 'Файл не у форматі gzip. Перевір його командою file {file}.',
    'Soubor není ve formátu bzip2. Ověř ho příkazem file {file}.' => 'Файл не у форматі bzip2. Перевір його командою file {file}.',
    'Použití: zip archiv.zip soubor… nebo zip -r archiv.zip slozka' => 'Використання: zip archiv.zip soubor… або zip -r archiv.zip slozka',
    'Soubor není ve formátu ZIP. Ověř ho příkazem file {archive}.' => 'Файл не у форматі ZIP. Перевір його командою file {archive}.',
    'Terminál je neinteraktivní – přepis povol volbou -o: unzip -o {archive}.' => 'Термінал неінтерактивний – дозволь перезапис опцією -o: unzip -o {archive}.',

    // linux_v58_cmd_ssh.php
    'Podporované typy klíčů: ed25519 (doporučeno), rsa, ecdsa. Např. ssh-keygen -t ed25519.' => 'Підтримувані типи ключів: ed25519 (рекомендовано), rsa, ecdsa. Наприклад, ssh-keygen -t ed25519.',
    'Klíč už existuje. Zvol jiný název volbou -f, nebo starý nejdřív smaž (rm {file} {file}.pub).' => 'Ключ уже існує. Вибери іншу назву опцією -f, або спочатку видали старий (rm {file} {file}.pub).',
    'Přístupová fráze (-N) chrání soukromý klíč, kdyby ho někdo získal. V laboratoři ji nepotřebuješ – klíč je jen fiktivní.' => 'Парольна фраза (-N) захищає приватний ключ, якщо його хтось отримає. У лабораторії вона тобі не потрібна – ключ лише умовний.',
    'Na tenhle počítač se v laboratoři nedá přihlásit (nemá cvičný účet). Použij hosta ze zadání úlohy.' => 'На цей комп’ютер у лабораторії увійти не можна (немає навчального облікового запису). Використай хост із завдання.',
    "Terminál je neinteraktivní. Spusť příkaz rovnou: ssh {user}@{host} 'ls -la'  (nebo 'cat kod.txt')." => "Термінал неінтерактивний. Запусти команду одразу: ssh {user}@{host} 'ls -la'  (або 'cat kod.txt').",
    'Jméno „{host}“ nešlo přeložit na IP. Zkontroluj překlep nebo /etc/hosts.' => 'Імʼя „{host}“ не вдалося перетворити на IP. Перевір одруківку або /etc/hosts.',
    'Na portu {port} nikdo neposlouchá. Běží na cíli SSH server? Zkus nc -zv {host} {port}.' => 'На порту {port} ніхто не слухає. Чи працює на цілі SSH-сервер? Спробуй nc -zv {host} {port}.',
    'Spojení vypršelo. Ověř dostupnost: ping {host} a traceroute {host}.' => 'Час зʼєднання вичерпано. Перевір доступність: ping {host} і traceroute {host}.',
    'Otisk serveru se změnil oproti tomu v known_hosts. V laboratoři to znamená, že úroveň server přeinstalovala. Když je to očekávané, starý záznam smaž: ssh-keygen -R {host} a přihlas se znovu.' => 'Відбиток сервера змінився порівняно з тим, що в known_hosts. У лабораторії це означає, що рівень перевстановив сервер. Якщо це очікувано, видали старий запис: ssh-keygen -R {host} і увійди знову.',
    'Soukromý klíč smí číst jen ty. Oprav práva: chmod 600 {path} (a chmod 700 {dir}).' => 'Приватний ключ можеш читати лише ти. Виправ права: chmod 600 {path} (і chmod 700 {dir}).',
    'Nemáš klíč, kterým by tě server přijal. Vygeneruj si ho (ssh-keygen -t ed25519) a nahraj na server (ssh-copy-id {user}@{host}).' => 'У тебе немає ключа, який прийняв би сервер. Згенеруй його (ssh-keygen -t ed25519) і завантаж на сервер (ssh-copy-id {user}@{host}).',
    'Server tvůj klíč nezná a heslo v neinteraktivním terminálu zadat nejde. Nahraj svůj veřejný klíč: ssh-copy-id {user}@{host}.' => 'Сервер не знає твій ключ, а пароль у неінтерактивному терміналі ввести не можна. Завантаж свій публічний ключ: ssh-copy-id {user}@{host}.',
    'Server přijímá jen klíče. Ověř, že tvůj veřejný klíč je v ~/.ssh/authorized_keys na serveru (ssh-copy-id {user}@{host}).' => 'Сервер приймає лише ключі. Перевір, що твій публічний ключ є в ~/.ssh/authorized_keys на сервері (ssh-copy-id {user}@{host}).',
    'Na lokální kopírování použij cp. scp přenáší mezi počítači: scp soubor user@host:~/' => 'Для локального копіювання використай cp. scp передає між компʼютерами: scp soubor user@host:~/',
    'Nemáš veřejný klíč. Nejdřív si ho vygeneruj: ssh-keygen -t ed25519.' => 'У тебе немає публічного ключа. Спочатку згенеруй його: ssh-keygen -t ed25519.',
    'ssh-copy-id nahrává klíč po přihlášení heslem. Tenhle server ho v laboratoři nepřijímá – klíč přenes přes scp do ~/.ssh/authorized_keys.' => 'ssh-copy-id завантажує ключ після входу паролем. Цей сервер у лабораторії його не приймає – перенеси ключ через scp у ~/.ssh/authorized_keys.',
    'Ve skutečnosti se ssh-copy-id jednou zeptá na heslo účtu. V laboratoři ho za tebe potvrdí, aby ses mohl(a) hned přihlásit klíčem.' => 'У реальності ssh-copy-id один раз запитав би пароль облікового запису. У лабораторії його підтверджують за тебе, щоб ти міг(могла) одразу увійти ключем.',

    // linux_v58_cmd_users.php
    'Účty a skupiny spravuje jen správce systému. Zkus příkaz zopakovat se sudo, např. sudo {name} …' => 'Облікові записи й групи керує лише адміністратор системи. Спробуй повторити команду з sudo, наприклад sudo {name} …',
    'Do skupiny přidáš člena: sudo gpasswd -a uzivatel skupina; odebereš: sudo gpasswd -d uzivatel skupina.' => 'Додати учасника до групи: sudo gpasswd -a uzivatel skupina; видалити: sudo gpasswd -d uzivatel skupina.',
    'Heslo v laboratoři nastavíš neinteraktivně přes stdin: echo "{target}:NoveHeslo123" | sudo chpasswd. Skutečné heslo se nikde neukládá – jen příznak, že je nastavené.' => 'У лабораторії пароль встановлюють неінтерактивно через stdin: echo "{target}:NoveHeslo123" | sudo chpasswd. Справжній пароль ніде не зберігається – лише ознака, що він встановлений.',
    'chpasswd čte dvojice uzivatel:heslo ze standardního vstupu: echo "student:Tajne123" | sudo chpasswd.' => 'chpasswd читає пари користувач:пароль зі стандартного вводу: echo "student:Tajne123" | sudo chpasswd.',
    'Údaje o hesle jiného uživatele smí číst jen správce: sudo chage -l {user}.' => 'Дані про пароль іншого користувача може читати лише адміністратор: sudo chage -l {user}.',
    'V laboratoři se na jiného uživatele nepřepneš heslem – terminál je neinteraktivní. Jako správce spusť konkrétní příkaz přes sudo a svá oprávnění si ověř příkazem sudo -l.' => 'У лабораторії не можна перемкнутися на іншого користувача паролем – термінал неінтерактивний. Як адміністратор, запусти конкретну команду через sudo, а свої права перевір командою sudo -l.',
    'getent umí v laboratoři databáze passwd a group, např. getent passwd student.' => 'У лабораторії getent розуміє бази passwd і group, наприклад getent passwd student.',
    'visudo -c ověří syntaxi souborů sudoers, aniž bys je otevíral(a).' => 'visudo -c перевіряє синтаксис файлів sudoers, не відкриваючи їх.',
    'Masku zadej osmičkově, např. umask 022 (nové soubory 644) nebo umask 077 (soukromé soubory).' => 'Введи маску у вісімковій системі, наприклад umask 022 (нові файли 644) або umask 077 (приватні файли).',
];
