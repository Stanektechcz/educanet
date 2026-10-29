<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – příručka (CNT-02), část A: příkazy v57 od a do l.
 * Čistá data: 'tldr' (rychlé příklady, fungují v pískovišti) a 'see_also' přes 'extend' => true;
 * příkazy registru, které v57 příručka neměla, dostávají plnou položku (se 'extend' => true, takže
 * se bezpečně sloučí, i kdyby ji mezitím přidal jiný modul). Ověřuje tools/v58_sim_b_audit.php.
 */

lab58_register_manual(['commands' => [
    'answer' => ['extend' => true, 'tldr' => [['answer /home/student', 'odešle odpověď na otázku úlohy'], ['mise', 'připomene, v jakém tvaru má odpověď být']], 'see_also' => ['mise', 'hint', 'submit']],
    'apt' => ['extend' => true, 'tldr' => [['apt list --installed', 'vypíše nainstalované balíčky'], ['apt search tree', 'hledá balíček podle názvu a popisu'], ['sudo apt install cowsay', 'nainstaluje balíček (jen správce)']], 'see_also' => ['apt-get', 'sudo', 'which']],
    'awk' => ['extend' => true, 'tldr' => [["awk -F, '{print \$2}' data/zaci.csv", 'vypíše 2. sloupec CSV'], ["awk -F, 'NR>1 {s+=\$4} END {print s}' data/zaci.csv", 'sečte body ve 4. sloupci'], ["awk '{print NF}' poznamky.txt", 'počet slov na každém řádku']], 'see_also' => ['cut', 'sed', 'column']],
    'base64' => ['extend' => true, 'tldr' => [['base64 -d data/zprava.b64', 'dekóduje zprávu'], ['echo ahoj | base64', 'zakóduje text']], 'see_also' => ['xxd', 'md5sum', 'strings']],
    'cat' => ['extend' => true, 'tldr' => [['cat vitej.txt', 'vypíše soubor'], ['cat -n poznamky.txt', 'vypíše s čísly řádků'], ['cat poznamky.txt vitej.txt > spojene.txt', 'spojí dva soubory do jednoho']], 'see_also' => ['less', 'head', 'tail', 'tee']],
    'cd' => ['extend' => true, 'tldr' => [['cd data', 'vstoupí do podsložky'], ['cd ..', 'o úroveň výš'], ['cd -', 'zpět do předchozí složky']], 'see_also' => ['pwd', 'ls', 'realpath']],
    'check' => ['extend' => true, 'tldr' => [['check', 'ukáže, které podmínky úlohy už platí']], 'see_also' => ['mise', 'hint']],
    'chmod' => ['extend' => true, 'tldr' => [['chmod +x projekty/skripty/zaloha.sh', 'udělá ze souboru spustitelný skript'], ['chmod 600 poznamky.txt', 'soubor smí číst a měnit jen vlastník'], ['chmod -R go-w projekty', 'ostatním vezme právo zápisu v celé složce']], 'see_also' => ['chown', 'ls', 'stat']],
    'chown' => ['extend' => true, 'tldr' => [['sudo chown root poznamky.txt', 'změní vlastníka souboru'], ['sudo chown -R student:student projekty', 'vlastník i skupina celé složky']], 'see_also' => ['chmod', 'id', 'sudo']],
    'clear' => ['extend' => true, 'tldr' => [['clear', 'vyčistí obrazovku (také Ctrl+L)']], 'see_also' => ['history']],
    'cp' => ['extend' => true, 'tldr' => [['cp vitej.txt kopie.txt', 'zkopíruje soubor'], ['cp -r projekty zaloha', 'zkopíruje celou složku'], ['cp -v data/*.txt /tmp/', 'zkopíruje víc souborů a vypíše co']], 'see_also' => ['mv', 'rm', 'rename']],
    'curl' => ['extend' => true, 'tldr' => [['curl -I http://intranet.skola.test', 'jen hlavičky odpovědi serveru'], ['curl -s http://intranet.skola.test', 'stáhne stránku a vypíše ji'], ['curl -L -o stranka.html http://www.example.com', 'uloží stránku, následuje přesměrování']], 'see_also' => ['wget', 'ping', 'dig']],
    'cut' => ['extend' => true, 'tldr' => [['cut -d, -f2 data/zaci.csv', 'druhý sloupec CSV'], ['cut -c1-5 poznamky.txt', 'prvních 5 znaků každého řádku']], 'see_also' => ['awk', 'paste', 'column']],
    'date' => ['extend' => true, 'tldr' => [['date', 'aktuální datum a čas'], ['date +%Y-%m-%d', 'datum ve tvaru ROK-MĚSÍC-DEN'], ['date +%H:%M', 'jen hodiny a minuty']], 'see_also' => ['uptime', 'time']],
    'df' => ['extend' => true, 'tldr' => [['df -h', 'volné místo na discích čitelně'], ['df -h /', 'jen kořenový oddíl']], 'see_also' => ['du', 'lsblk', 'mount']],
    'diff' => ['extend' => true, 'tldr' => [['diff vitej.txt poznamky.txt', 'ukáže rozdílné řádky dvou souborů']], 'see_also' => ['comm', 'md5sum']],
    'dig' => ['extend' => true, 'tldr' => [['dig +short example.com', 'jen IP adresa domény'], ['dig example.com MX', 'poštovní servery domény'], ['dig @8.8.8.8 example.com', 'zeptá se konkrétního DNS serveru']], 'see_also' => ['nslookup', 'host', 'ping']],
    'du' => ['extend' => true, 'tldr' => [['du -sh projekty', 'celková velikost složky'], ['du -ah data', 'velikost každého souboru ve složce']], 'see_also' => ['df', 'ls', 'find']],
    'echo' => ['extend' => true, 'tldr' => [['echo Ahoj $USER', 'vypíše text s proměnnou'], ['echo -e "a\tb"', 'povolí \t a \n'], ['echo "nový řádek" >> poznamky.txt', 'připíše řádek na konec souboru']], 'see_also' => ['printf', 'tee', 'cat']],
    'env' => ['extend' => true, 'tldr' => [['env', 'vypíše proměnné prostředí'], ['env | grep PATH', 'najde konkrétní proměnnou']], 'see_also' => ['export', 'printenv', 'unset']],
    'exit' => ['extend' => true, 'tldr' => [['exit', 'ukončí shell (ve skriptu ukončí skript)']], 'see_also' => ['logout', 'bash']],
    'export' => ['extend' => true, 'tldr' => [['export JMENO=Ema', 'nastaví proměnnou i pro spouštěné programy'], ['export -p', 'vypíše exportované proměnné']], 'see_also' => ['env', 'printenv', 'unset']],
    'file' => ['extend' => true, 'tldr' => [['file obrazky/logo.png', 'pozná typ souboru podle obsahu'], ['file data/*', 'typy všech souborů ve složce']], 'see_also' => ['stat', 'identify', 'strings']],
    'find' => ['extend' => true, 'tldr' => [["find . -name '*.txt'", 'soubory podle jména'], ['find ~ -size +1M', 'soubory větší než 1 MiB'], ["find data -type f -exec wc -l {} \\;", 'na každý nalezený soubor spustí příkaz']], 'see_also' => ['ls', 'grep', 'xargs', 'realpath']],
    'free' => ['extend' => true, 'tldr' => [['free -h', 'obsazení paměti čitelně']], 'see_also' => ['top', 'uptime', 'lscpu']],
    'grep' => ['extend' => true, 'tldr' => [['grep TODO poznamky.txt', 'řádky se slovem TODO'], ['grep -in síť poznamky.txt', 'bez ohledu na velikost písmen, s čísly řádků'], ['grep -r "h1" projekty', 'hledá ve všech souborech složky']], 'see_also' => ['egrep', 'sed', 'awk', 'find']],
    'head' => ['extend' => true, 'tldr' => [['head -n 3 poznamky.txt', 'první 3 řádky'], ['head -c 16 obrazky/logo.png | xxd', 'prvních 16 bajtů (hlavička souboru)']], 'see_also' => ['tail', 'cat', 'less']],
    'help' => ['extend' => true, 'tldr' => [['help', 'přehled příkazů laboratoře']], 'see_also' => ['man', 'mise']],
    'hint' => ['extend' => true, 'tldr' => [['hint', 'další nápověda k úloze (ubírá body)']], 'see_also' => ['mise', 'check']],
    'history' => ['extend' => true, 'tldr' => [['history 5', 'posledních 5 příkazů'], ['history | grep ls', 'najde dřívější příkaz']], 'see_also' => ['clear', 'alias']],
    'host' => ['extend' => true, 'tldr' => [['host example.com', 'přeloží jméno na adresu'], ['host 8.8.8.8', 'zpětný dotaz na jméno']], 'see_also' => ['dig', 'nslookup']],
    'hostname' => ['extend' => true, 'tldr' => [['hostname', 'jméno počítače'], ['hostname -I', 'jeho IP adresy']], 'see_also' => ['uname', 'ip', 'whoami']],
    'id' => ['extend' => true, 'tldr' => [['id', 'UID, GID a skupiny aktuálního uživatele'], ['id root', 'totéž pro jiného uživatele']], 'see_also' => ['whoami', 'groups', 'sudo']],
    'ifconfig' => ['extend' => true, 'tldr' => [['sudo apt install net-tools', 'doinstaluje starý nástroj ifconfig'], ['ifconfig', 'rozhraní a adresy (starší náhrada za ip a)']], 'see_also' => ['ip', 'netstat']],
    'ip' => ['extend' => true, 'tldr' => [['ip a', 'adresy všech rozhraní'], ['ip r', 'směrovací tabulka a výchozí brána'], ['ip -br a', 'stručný přehled rozhraní']], 'see_also' => ['ping', 'ss', 'hostname']],
    'journalctl' => ['extend' => true, 'tldr' => [['journalctl -u nginx -n 5', 'posledních 5 záznamů služby'], ['journalctl -p err', 'jen chyby']], 'see_also' => ['systemctl', 'tail', 'grep']],
    'kill' => ['extend' => true, 'tldr' => [['kill 1487', 'slušně ukončí proces podle PID (signál 15)'], ['kill -9 1487', 'násilně ukončí proces']], 'see_also' => ['ps', 'pkill', 'pgrep']],
    'less' => ['extend' => true, 'tldr' => [['less data/access.log', 'prohlížení dlouhého souboru (q = konec)']], 'see_also' => ['more', 'cat', 'head']],
    'ls' => ['extend' => true, 'tldr' => [['ls -la', 'podrobný výpis i se skrytými soubory'], ['ls -lhS obrazky', 'podle velikosti, čitelné jednotky'], ['ls -lt', 'nejnovější soubory nahoře']], 'see_also' => ['tree', 'find', 'stat', 'du']],
    'htop' => ['extend' => true, 'tldr' => [['top', 'v laboratoři místo htop použij top']], 'see_also' => ['top', 'ps']],
    'ln' => ['extend' => true, 'tldr' => [['ln -s /var/www/html web', 'vytvoří symbolický odkaz (v laboratoři jen přehled)']], 'see_also' => ['readlink', 'realpath', 'cp']],
    'lsblk' => ['extend' => true, 'examples' => [['lsblk -f', 'souborové systémy a jejich UUID']], 'tldr' => [['lsblk', 'disky a oddíly ve stromu'], ['lsblk -d -o NAME,SIZE,MODEL', 'jen celé disky s modelem']], 'see_also' => ['df', 'mount', 'du']],
]]);

// Příkazy z registru, které příručka v57 neměla (a–l).
lab58_register_manual(['commands' => [
    'alias' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'zaklady', 'level' => 2,
        'summary' => 'Vytvoří zkratku (alias) pro delší příkaz, nebo vypíše existující.',
        'synopsis' => "alias [JMÉNO='příkaz']",
        'about' => 'Alias je přezdívka příkazu: po alias ll=\'ls -alF\' stačí psát ll. Bez argumentů vypíše všechny aliasy. Platí jen v aktuálním terminálu – natrvalo se zapisují do ~/.bashrc.',
        'examples' => [['alias', 'vypíše všechny aliasy'], ["alias ll='ls -alF'", 'vytvoří zkratku ll']],
        'tldr' => [["alias gs='git status'", 'zkratka pro častý příkaz'], ['unalias ll', 'zkratku zase zruší']],
        'related' => ['unalias', 'type'], 'see_also' => ['history'], 'tip' => 'Kolem příkazu s mezerami dej apostrofy, jinak alias vezme jen první slovo.',
    ],
    'apt-get' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'balicky', 'level' => 2,
        'summary' => 'Starší (a ve skriptech stále oblíbená) podoba správce balíčků apt.',
        'synopsis' => 'apt-get update | apt-get install BALÍČEK | apt-get remove BALÍČEK',
        'about' => 'apt-get dělá totéž co apt, jen má stabilní výstup vhodný do skriptů. Instalace a odebírání balíčků vyžaduje práva správce (sudo).',
        'options' => [['update', 'stáhne seznamy balíčků'], ['install BALÍČEK', 'nainstaluje balíček'], ['remove BALÍČEK', 'odebere balíček'], ['-y', 'na otázky odpoví ano']],
        'examples' => [['sudo apt-get update', 'obnoví seznam balíčků'], ['sudo apt-get install -y cowsay', 'nainstaluje balíček bez ptaní']],
        'tldr' => [['sudo apt-get remove -y cowsay', 'balíček zase odebere']],
        'related' => ['apt', 'sudo'], 'see_also' => ['which'],
    ],
    'bash' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'zaklady', 'level' => 2,
        'summary' => 'Příkazový interpret (shell) – spustí skript nebo jeden příkaz.',
        'synopsis' => 'bash [skript.sh [argumenty]] | bash -c "příkaz"',
        'about' => 'bash je program, který čte a vykonává tvoje příkazy. bash skript.sh spustí skript i bez práva x, bash -c spustí zadaný řetězec jako příkaz. V simulaci skripty neumí if/for/while.',
        'options' => [['-c "příkaz"', 'provede zadaný příkaz']],
        'examples' => [['bash projekty/skripty/pozdrav.sh', 'spustí skript'], ["bash -c 'echo ahoj; pwd'", 'provede dva příkazy']],
        'tldr' => [['sh projekty/skripty/pozdrav.sh', 'totéž přes sh']],
        'related' => ['sh', 'source'], 'see_also' => ['chmod', 'exit'],
    ],
    'cowsay' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'balicky', 'level' => 1,
        'summary' => 'Kravička v terminálu řekne tvůj text (ukázka instalace balíčku).',
        'synopsis' => 'cowsay TEXT',
        'about' => 'Zábavný program, na kterém si vyzkoušíš instalaci softwaru: nejdřív sudo apt install cowsay, pak cowsay Ahoj. Umí číst i text z roury.',
        'examples' => [['cowsay Ahoj', 'kráva řekne Ahoj'], ['cowsay "Linux je super"', 'víc slov dej do uvozovek']],
        'tldr' => [['sudo apt install cowsay', 'nejdřív ho nainstaluj']],
        'related' => ['apt', 'echo'], 'see_also' => ['sudo'],
    ],
    'egrep' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'text', 'level' => 2,
        'summary' => 'grep s rozšířenými regulárními výrazy (totéž jako grep -E).',
        'synopsis' => 'egrep [volby] VZOR [soubor…]',
        'about' => 'Zastaralá zkratka pro grep -E: v rozšířených výrazech fungují | (nebo), + a ? bez zpětného lomítka. V nových skriptech piš raději grep -E.',
        'examples' => [["egrep 'TODO|Síť' poznamky.txt", 'řádky s TODO nebo Síť']],
        'tldr' => [["grep -E 'TODO|Síť' poznamky.txt", 'doporučená podoba téhož']],
        'related' => ['grep', 'fgrep'], 'see_also' => ['sed'],
    ],
    'false' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'zaklady', 'level' => 2,
        'summary' => 'Nedělá nic a skončí neúspěchem (návratový kód 1).',
        'synopsis' => 'false',
        'about' => 'Hodí se při zkoušení podmínek && a || a ve skriptech. Opakem je true (kód 0).',
        'examples' => [['false; echo $?', 'vypíše 1'], ['false || echo jiná cesta', 'po neúspěchu se provede příkaz za ||']],
        'related' => ['true', 'test'], 'see_also' => ['echo'],
    ],
    'fgrep' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'text', 'level' => 2,
        'summary' => 'grep s pevným řetězcem – tečky ani hvězdičky nejsou speciální (grep -F).',
        'synopsis' => 'fgrep [volby] TEXT [soubor…]',
        'about' => 'Zastaralá zkratka pro grep -F: hledá přesně zadaný text, takže se znaky . * [ nemusí escapovat. Nově piš grep -F.',
        'examples' => [['fgrep -n TODO poznamky.txt', 'přesný text s čísly řádků']],
        'tldr' => [['grep -F "." data/zaci.csv', 'hledá opravdovou tečku, ne „libovolný znak“']],
        'related' => ['grep', 'egrep'], 'see_also' => ['find'],
    ],
    'groups' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'prava', 'level' => 2,
        'summary' => 'Vypíše skupiny, do kterých uživatel patří.',
        'synopsis' => 'groups [uživatel]',
        'about' => 'Skupiny rozhodují o přístupu k souborům a o tom, kdo smí používat sudo (skupina sudo). Stejné údaje ukazuje i id.',
        'examples' => [['groups', 'moje skupiny'], ['groups root', 'skupiny jiného uživatele']],
        'related' => ['id', 'whoami'], 'see_also' => ['chown', 'sudo'],
    ],
    'logout' => [
        'extend' => true, 'in_lab' => true, 'cat' => 'zaklady', 'level' => 1,
        'summary' => 'Odhlásí z přihlašovacího shellu (jako exit).',
        'synopsis' => 'logout',
        'about' => 'Ukončí přihlášení v terminálu. V laboratoři terminál zůstane otevřený, takže můžeš psát dál.',
        'examples' => [['logout', 'odhlášení']],
        'related' => ['exit'], 'see_also' => ['whoami'],
    ],
]]);

// Rychlé tahy k položkám výše, které je zatím neměly.
lab58_register_manual([
    'false' => ['extend' => true, 'tldr' => [['false || echo "selhalo"', 'false vždy skončí chybou (kód 1)'], ['false; echo $?', 'vypíše návratový kód 1']]],
    'groups' => ['extend' => true, 'tldr' => [['groups', 'skupiny, do kterých patříš'], ['groups root', 'skupiny jiného uživatele']]],
    'logout' => ['extend' => true, 'tldr' => [['logout', 'odhlásí přihlašovací shell (jinak použij exit)']]],
]);
