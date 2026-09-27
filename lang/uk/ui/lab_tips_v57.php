<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „lab_tips_v57“ (ukrajinština).
 * Nápovědy simulátoru Linuxu ($w->tip() volané z linux_v57_world.php, linux_v57_cmd_files.php,
 * linux_v57_cmd_net.php, linux_v57_cmd_shell.php, linux_v57_cmd_sys.php, linux_v57_cmd_text.php).
 * Tipy jsou UI nápověda a překládají se; výstup simulovaných programů (stdout/stderr) zůstává anglicky.
 * Klíč = přesně český text ze zdroje. Vlastník: i18n builder TIP1.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // linux_v57_world.php
    'Soubor by byl větší než 1 MB – na tvém cvičném disku pro něj už není místo.' => 'Файл був би більшим за 1 МБ – на твоєму навчальному диску для нього вже немає місця.',
    // linux_v57_cmd_files.php
    'Neznámá volba. Seznam voleb ukáže man {prikaz} nebo {prikaz} --help.' => 'Невідома опція. Список опцій покаже man {prikaz} або {prikaz} --help.',
    '{prikaz} bez jména souboru čeká na text z klávesnice. V simulaci mu ho pošli rourou (např. cat soubor | {prikaz}) nebo napiš jméno souboru.' => 'Команда {prikaz} без імені файлу чекає на текст із клавіатури. У симуляції передай його через канал (наприклад, cat файл | {prikaz}) або вкажи ім\'я файлу.',
    '{prikaz} v simulaci ukáže celý soubor najednou. Ve skutečném terminálu listuješ mezerníkem a končíš klávesou q.' => 'У симуляції {prikaz} показує весь файл одразу. У справжньому терміналі гортаєш пробілом і виходиш клавішею q.',
    'tail -f by čekal na nové řádky. Simulace vypíše konec souboru a hned skončí.' => 'tail -f чекав би на нові рядки. Симуляція виведе кінець файлу й одразу завершиться.',
    'Nadřazená složka neexistuje. Celou cestu najednou vytvoří mkdir -p {cesta}' => 'Батьківської папки не існує. Весь шлях одразу створить mkdir -p {cesta}',
    'rmdir maže jen prázdné složky. Složku i s obsahem smaže rm -r {cesta} (opatrně!).' => 'rmdir видаляє лише порожні папки. Папку разом із вмістом видалить rm -r {cesta} (обережно!).',
    'Smazání celého systému laboratoř nedovolí ani v simulaci. Úroveň vrátíš do původního stavu příkazem reset.' => 'Видалити всю систему лабораторія не дозволить навіть у симуляції. Рівень повернеш у початковий стан командою reset.',
    'Složku smažeš rm -r {cil}, prázdnou také rmdir {cil}.' => 'Папку видалиш командою rm -r {cil}, порожню також rmdir {cil}.',
    'Složku zkopíruješ s volbou -r: cp -r {zdroj} {cil}' => 'Папку скопіюєш з опцією -r: cp -r {zdroj} {cil}',
    'Práva může měnit jen vlastník souboru nebo správce (sudo chmod …).' => 'Права може змінювати лише власник файлу або адміністратор (sudo chmod …).',
    'Vlastníka souboru mění jen správce systému: sudo chown {spec} {soubor}' => 'Власника файлу змінює лише адміністратор системи: sudo chown {spec} {soubor}',
    // linux_v57_cmd_net.php
    'Jméno „{jmeno}“ nešlo přeložit na IP adresu – žádný DNS server neodpověděl. Zkus ping na IP adresu (např. 1.1.1.1) a podívej se do /etc/resolv.conf.' => 'Ім\'я «{jmeno}» не вдалося перетворити на IP-адресу – жоден DNS-сервер не відповів. Спробуй ping на IP-адресу (наприклад, 1.1.1.1) і перевір /etc/resolv.conf.',
    'DNS server odpověděl, že jméno „{jmeno}“ neexistuje. Zkontroluj překlep.' => 'DNS-сервер відповів, що імені «{jmeno}» не існує. Перевір, чи немає одруку.',
    'Síťovou konfiguraci mění správce: sudo ip addr …' => 'Мережеву конфігурацію змінює адміністратор: sudo ip addr …',
    'Rozhraní zapíná a vypíná správce: sudo ip link set {rozhrani} {stav}' => 'Інтерфейс вмикає і вимикає адміністратор: sudo ip link set {rozhrani} {stav}',
    'Směrovací tabulku mění správce: sudo ip route {argumenty}' => 'Таблицю маршрутизації змінює адміністратор: sudo ip route {argumenty}',
    'Tahle trasa už existuje. Zobrazíš je ip route; nahradit ji jde přes ip route replace.' => 'Цей маршрут уже існує. Побачиш маршрути через ip route; замінити його можна через ip route replace.',
    'Brána musí ležet ve stejné síti jako tvoje rozhraní (ip a ukáže adresu a masku).' => 'Шлюз має бути в тій самій мережі, що й твій інтерфейс (ip a покаже адресу й маску).',
    'ifconfig je starší nástroj z balíčku net-tools. Dnes se používá ip a (adresy) a ip route (trasy).' => 'ifconfig – старіший інструмент із пакета net-tools. Сьогодні використовують ip a (адреси) та ip route (маршрути).',
    'Skutečný ping běží, dokud ho nezastavíš Ctrl+C. Simulace pošle 4 pakety (jako ping -c 4).' => 'Справжній ping працює, доки не зупиниш його Ctrl+C. Симуляція надішле 4 пакети (як ping -c 4).',
    'Počítač nezná cestu do cílové sítě. Zkontroluj ip route – chybí výchozí brána (default via …)? Nebo je rozhraní vypnuté (ip link)?' => 'Комп\'ютер не знає шляху до цільової мережі. Перевір ip route – чи не бракує типового шлюзу (default via …)? Або інтерфейс вимкнено (ip link)?',
    'Cíl (nebo brána) v místní síti neodpovídá. Je zapojený kabel a zapnuté rozhraní? Zkus ip link a ip neigh.' => 'Ціль (або шлюз) у локальній мережі не відповідає. Кабель підключено, а інтерфейс увімкнено? Спробуй ip link і ip neigh.',
    'Na ping neodpovídá, ale to ještě neznamená, že nefunguje – některé servery ICMP blokují. Zkus curl nebo nc na jejich port.' => 'На ping не відповідає, але це ще не означає, що не працює – деякі сервери блокують ICMP. Спробуй curl або nc на їхній порт.',
    'Pakety se někde po cestě ztrácí. Kde přesně, ukáže traceroute {cil}.' => 'Пакети десь губляться по дорозі. Де саме, покаже traceroute {cil}.',
    'Hvězdičky * * * znamenají, že od tohoto skoku už nepřišla odpověď. Problém je mezi posledním odpovídajícím routerem a dalším. (Skutečný traceroute by zkoušel až 30 skoků.)' => 'Зірочки * * * означають, що від цього переходу відповідь уже не прийшла. Проблема між останнім маршрутизатором, що відповів, і наступним. (Справжній traceroute пробував би до 30 переходів.)',
    'Žádný DNS server z /etc/resolv.conf neodpověděl. Je adresa serveru správná a dostupná (ping)?' => 'Жоден DNS-сервер із /etc/resolv.conf не відповів. Адреса сервера правильна і доступна (ping)?',
    'Počítač odpověděl, ale na portu {port} nic neposlouchá (Connection refused). Běží služba? Poslouchá na jiném portu? Zkus ss -tlnp nebo systemctl status.' => 'Комп\'ютер відповів, але на порту {port} ніхто не слухає (Connection refused). Служба працює? Слухає на іншому порту? Спробуй ss -tlnp або systemctl status.',
    'Síť je nedosažitelná – zkontroluj ip route a ip link.' => 'Мережа недосяжна – перевір ip route і ip link.',
    'Spojení vypršelo – odpověď nepřišla. Pomůže ping a traceroute na {cil}.' => 'З\'єднання вичерпало час – відповідь не прийшла. Допоможе ping і traceroute на {cil}.',
    'Které procesy porty drží, uvidíš jen jako správce: sudo ss -tulpn' => 'Які процеси тримають порти, побачиш лише як адміністратор: sudo ss -tulpn',
    'netstat je starší nástroj z balíčku net-tools, dnes se používá ss -tulpn.' => 'netstat – старіший інструмент із пакета net-tools, сьогодні використовують ss -tulpn.',
    'Služba čeká na tvůj vstup. Pošli ho rourou: echo "text" | nc {hostitel} {port}' => 'Служба чекає на твоє введення. Надішли його через канал: echo "text" | nc {hostitel} {port}',
    'Spojení je otevřené, ale služba nic neposlala. Zkus jí něco poslat rourou: echo "ahoj" | nc {hostitel} {port}' => 'З\'єднання відкрите, але служба нічого не надіслала. Спробуй надіслати їй щось через канал: echo "привіт" | nc {hostitel} {port}',
    // linux_v57_cmd_shell.php
    'Nemyslel(a) jsi man {navrh}?' => 'Можливо, ти мав(-ла) на увазі man {navrh}?',
    'Napiš sudo před konkrétní příkaz, např. sudo systemctl restart nginx. Tak je vždy vidět, co děláš jako správce.' => 'Пиши sudo перед конкретною командою, наприклад sudo systemctl restart nginx. Так завжди видно, що ти робиш як адміністратор.',
    'V této úrovni nejsi správce počítače, takže sudo nesmíš použít. Úkol jde vyřešit bez něj.' => 'У цьому рівні ти не адміністратор комп\'ютера, тож sudo використовувати не можна. Завдання можна виконати без нього.',
    'Napiš sudo před konkrétní příkaz, např. sudo nano /etc/hosts.' => 'Пиши sudo перед конкретною командою, наприклад sudo nano /etc/hosts.',
    'V laboratoři terminál zůstává otevřený – můžeš psát dál.' => 'У лабораторії термінал залишається відкритим – можеш друкувати далі.',
    'Nový interaktivní shell simulace nespouští – už v jednom jsi. Skript spustíš: bash soubor.sh' => 'Новий інтерактивний shell симуляція не запускає – ти вже в ньому. Скрипт запустиш так: bash файл.sh',
    // linux_v57_cmd_sys.php
    'Nejčastěji se používá ps aux (všechny procesy) nebo ps -ef.' => 'Найчастіше використовують ps aux (усі процеси) або ps -ef.',
    'top v simulaci ukáže jeden snímek. Ve skutečném terminálu se obnovuje a ukončíš ho klávesou q.' => 'У симуляції top показує один знімок. У справжньому терміналі він оновлюється, а виходиш клавішею q.',
    'Cizí proces (jiného uživatele) může ukončit jen správce: sudo kill {pid}' => 'Чужий процес (іншого користувача) може завершити лише адміністратор: sudo kill {pid}',
    'Proces s tímto PID neexistuje. Aktuální čísla procesů ukáže ps aux.' => 'Процесу з таким PID не існує. Актуальні номери процесів покаже ps aux.',
    'Seznam služeb ukáže systemctl list-units --type=service.' => 'Список служб покаже systemctl list-units --type=service.',
    'Službu spouští a zastavuje správce systému: sudo systemctl {akce} {jednotka}' => 'Службу запускає й зупиняє адміністратор системи: sudo systemctl {akce} {jednotka}',
    'Služba nenaběhla. Důvod najdeš v logu: journalctl -u {jednotka} -n 20{extra}' => 'Служба не запустилася. Причину знайдеш у журналі: journalctl -u {jednotka} -n 20{extra}',
    ' – nebo zkontroluj konfiguraci: sudo nginx -t' => ' – або перевір конфігурацію: sudo nginx -t',
    'journalctl -f by čekal na nové záznamy; simulace vypíše poslední a skončí.' => 'journalctl -f чекав би на нові записи; симуляція виведе останні й завершиться.',
    'nginx ukazuje soubor a číslo řádku s chybou. Otevři ho (sudo nano …) a oprav přesně ten řádek.' => 'nginx показує файл і номер рядка з помилкою. Відкрий його (sudo nano …) і виправ саме цей рядок.',
    'Webový server běží jako služba. Ovládej ho přes sudo systemctl start|stop|restart nginx.' => 'Вебсервер працює як служба. Керуй ним через sudo systemctl start|stop|restart nginx.',
    'Instalaci softwaru dělá správce systému: sudo apt {argumenty}' => 'Встановлення програм робить адміністратор системи: sudo apt {argumenty}',
    'Balíčky se stahují z internetu. Nejdřív oprav připojení (ping, ip route, /etc/resolv.conf).' => 'Пакунки завантажуються з інтернету. Спочатку виправ з\'єднання (ping, ip route, /etc/resolv.conf).',
    'Balíček s tímto jménem neexistuje. Hledat můžeš: apt search {balicek}' => 'Пакунка з такою назвою не існує. Шукати можна так: apt search {balicek}',
    // linux_v57_cmd_text.php
    'tr čte jen ze vstupu (roury). Použij třeba: cat soubor | tr a-z A-Z' => 'tr читає лише зі входу (каналу). Спробуй, наприклад: cat файл | tr a-z A-Z',
    'Simulace awk zná pole ($1, $NF), NR, NF, vzory /text/ a porovnání, print/printf, proměnné a bloky BEGIN/END. Pole a cykly zatím ne.' => 'Симуляція awk розуміє поля ($1, $NF), NR, NF, шаблони /текст/ і порівняння, print/printf, змінні та блоки BEGIN/END. Масиви й цикли поки що ні.',
];
