<?php

declare(strict_types=1);

return array (
  'knowledgeTours' => 
  array (
    'ip-addressing' => 
    array (
      'time' => '7–10 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'subnet',
        'address' => '192.168.50.42/24',
        'network' => '192.168.50.0',
        'hosts' => '192.168.50.1–254',
        'broadcast' => '192.168.50.255',
      ),
      'mental' => 'IP adresa říká „kde zařízení je“, prefix říká „která část adresy patří síti“. Než začneš řešit internet, musíš vědět, ve které síti klient skutečně sedí.',
      'steps' => 
      array (
        0 => 'Najdi IP adresu klienta.',
        1 => 'Přečti prefix/masku a urč síťovou část.',
        2 => 'Rozhodni, zda je cíl ve stejné síti.',
        3 => 'Pokud není, hledej výchozí bránu.',
        4 => 'Až potom řeš DNS nebo konkrétní službu.',
      ),
      'mistakes' => 
      array (
        0 => 'Zaměnit /24 za počet zařízení.',
        1 => 'Považovat každou 192.168.x.x adresu za „internetovou“.',
        2 => 'Ignorovat adresu 169.254.x.x, která často signalizuje problém s automatickou konfigurací.',
      ),
      'check' => 
      array (
        'q' => 'Klient má 192.168.50.42/24 a server 192.168.50.80/24. Musí paket kvůli tomu přes router?',
        'options' => 
        array (
          0 => 'Ano, vždy.',
          1 => 'Ne, jsou ve stejném subnetu.',
          2 => 'Jen pokud jde o HTTPS.',
        ),
        'correct' => 1,
        'why' => 'Obě adresy patří do sítě 192.168.50.0/24, takže mohou komunikovat lokálně.',
      ),
    ),
    'dns' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Prohlížeč',
            1 => 'portal.example.cz',
          ),
          1 => 
          array (
            0 => 'Resolver',
            1 => 'Kde je A záznam?',
          ),
          2 => 
          array (
            0 => 'DNS',
            1 => '192.0.2.40',
          ),
          3 => 
          array (
            0 => 'Klient',
            1 => 'Připoj se na 192.0.2.40',
          ),
        ),
      ),
      'mental' => 'DNS je telefonní seznam s cache. Když IP funguje a doména ne, aplikace často není problém — klient jen neumí přeložit jméno na správnou adresu.',
      'steps' => 
      array (
        0 => 'Ověř, jaký DNS server klient používá.',
        1 => 'Zkus překlad jména přes nslookup/dig.',
        2 => 'Porovnej vrácenou IP s očekávanou.',
        3 => 'Když je odpověď stará, přemýšlej o cache/TTL.',
        4 => 'Teprve potom měň DNS konfiguraci.',
      ),
      'mistakes' => 
      array (
        0 => 'Restartovat webserver, i když selhává samotný překlad jména.',
        1 => 'Myslet si, že DNS = internet.',
        2 => 'Přehlédnout, že různé resolvery mohou mít dočasně různé odpovědi.',
      ),
      'check' => 
      array (
        'q' => 'Web na 192.0.2.40 funguje, ale portal.example.cz ne. Co ověříš jako první?',
        'options' => 
        array (
          0 => 'DNS překlad.',
          1 => 'Grafickou kartu.',
          2 => 'SSH klíč.',
        ),
        'correct' => 0,
        'why' => 'Spojení na IP ukazuje, že cesta k hostu může být v pořádku. Rozdíl je právě překlad názvu.',
      ),
    ),
    'dhcp' => 
    array (
      'time' => '7–10 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Klient',
            1 => 'DISCOVER',
          ),
          1 => 
          array (
            0 => 'DHCP',
            1 => 'OFFER',
          ),
          2 => 
          array (
            0 => 'Klient',
            1 => 'REQUEST',
          ),
          3 => 
          array (
            0 => 'DHCP',
            1 => 'ACK + IP, GW, DNS',
          ),
        ),
      ),
      'mental' => 'DHCP není jen „přidělovač IP“. Klientovi předává celý startovní balíček: adresu, masku/prefix, bránu a DNS.',
      'steps' => 
      array (
        0 => 'Podívej se na aktuální konfiguraci.',
        1 => 'Hledej neočekávanou/APIPA adresu.',
        2 => 'Ověř linku/VLAN a dostupnost DHCP.',
        3 => 'Zkontroluj pool a případné konflikty.',
        4 => 'Po opravě obnov lease a znovu ověř konfiguraci.',
      ),
      'mistakes' => 
      array (
        0 => 'Ručně nastavit náhodnou statickou IP bez znalosti subnetu.',
        1 => 'Řešit DNS dřív, než klient vůbec získá použitelnou IP.',
        2 => 'Zapomenout, že DHCP může předat i špatnou bránu nebo DNS.',
      ),
      'check' => 
      array (
        'q' => 'Windows klient po připojení dostane 169.254.32.18. Co je nejpravděpodobnější?',
        'options' => 
        array (
          0 => 'Klient nedostal očekávanou konfiguraci z DHCP.',
          1 => 'DNS funguje perfektně.',
          2 => 'Je to veřejná adresa ISP.',
        ),
        'correct' => 0,
        'why' => '169.254.0.0/16 je link-local/APIPA rozsah používaný při problému s běžnou automatickou konfigurací.',
      ),
    ),
    'ports' => 
    array (
      'time' => '6–9 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'ports',
        'host' => 'server01',
        'items' => 
        array (
          0 => 
          array (
            0 => '22',
            1 => 'SSH',
            2 => 'open',
          ),
          1 => 
          array (
            0 => '80',
            1 => 'HTTP',
            2 => 'closed',
          ),
          2 => 
          array (
            0 => '443',
            1 => 'HTTPS',
            2 => 'open',
          ),
          3 => 
          array (
            0 => '3306',
            1 => 'DB',
            2 => 'local',
          ),
        ),
      ),
      'mental' => 'IP tě dovede k počítači. Port tě dovede ke konkrétní službě na tom počítači. „Server pingnu“ proto neznamená „web funguje“.',
      'steps' => 
      array (
        0 => 'Zjisti cílovou IP.',
        1 => 'Zjisti cílový port služby.',
        2 => 'Ověř, zda služba naslouchá.',
        3 => 'Ověř, na jaké adrese naslouchá.',
        4 => 'Ověř firewall a síťovou cestu z klienta.',
      ),
      'mistakes' => 
      array (
        0 => 'Zaměnit port za fyzický konektor.',
        1 => 'Považovat otevřený port za důkaz bezpečnosti.',
        2 => 'Ignorovat rozdíl mezi 127.0.0.1:PORT a 0.0.0.0:PORT.',
      ),
      'check' => 
      array (
        'q' => 'Ping funguje, ale TCP/443 ne. Co z toho plyne?',
        'options' => 
        array (
          0 => 'Síť je určitě bez chyby.',
          1 => 'Musíš ověřit službu, binding, firewall a cestu pro TCP/443.',
          2 => 'DNS je určitě špatně.',
        ),
        'correct' => 1,
        'why' => 'ICMP a TCP/443 jsou rozdílné protokoly/cesty kontroly. Ping sám dostupnost webové služby nepotvrdí.',
      ),
    ),
    'ssh-sftp' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Notebook',
            1 => 'SSH klient',
          ),
          1 => 
          array (
            0 => 'TCP/22',
            1 => 'šifrovaný kanál',
          ),
          2 => 
          array (
            0 => 'Server',
            1 => 'sshd',
          ),
          3 => 
          array (
            0 => 'SFTP',
            1 => 'přenos souborů',
          ),
        ),
      ),
      'mental' => 'SSH je bezpečný vzdálený kanál. SFTP běží uvnitř stejného SSH světa — není to klasické FTP s jinou ikonou.',
      'steps' => 
      array (
        0 => 'Ověř DNS/IP serveru.',
        1 => 'Ověř TCP/22.',
        2 => 'Ověř, že sshd běží a naslouchá.',
        3 => 'Rozliš problém s konektivitou od autentizace.',
        4 => 'U SFTP ověř oprávnění k cílovým souborům/adresářům.',
      ),
      'mistakes' => 
      array (
        0 => 'Hledat port 21 pro SFTP.',
        1 => 'Sdílet soukromý SSH klíč.',
        2 => 'Měnit heslo, když se klient na port 22 vůbec nedostane.',
      ),
      'check' => 
      array (
        'q' => 'SFTP typicky používá:',
        'options' => 
        array (
          0 => 'SSH a TCP/22.',
          1 => 'HTTP a TCP/80.',
          2 => 'ICMP.',
        ),
        'correct' => 0,
        'why' => 'SFTP je subsystém/protokol přenášený přes SSH spojení.',
      ),
    ),
    'https' => 
    array (
      'time' => '10–14 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => '1',
            1 => 'DNS',
            2 => 'jméno → IP',
          ),
          1 => 
          array (
            0 => '2',
            1 => 'TCP',
            2 => 'spojení na 443',
          ),
          2 => 
          array (
            0 => '3',
            1 => 'TLS',
            2 => 'certifikát + šifrování',
          ),
          3 => 
          array (
            0 => '4',
            1 => 'HTTP',
            2 => 'GET /',
          ),
          4 => 
          array (
            0 => '5',
            1 => 'Aplikace',
            2 => 'odpověď',
          ),
        ),
      ),
      'mental' => 'HTTPS není jedna věc. Je to řetězec vrstev. Když víš, ve které vrstvě se chyba objeví, výrazně zkrátíš troubleshooting.',
      'steps' => 
      array (
        0 => 'Přelož jméno přes DNS.',
        1 => 'Ověř TCP spojení na 443.',
        2 => 'Podívej se na TLS handshake/certifikát.',
        3 => 'Ověř HTTP stavový kód.',
        4 => 'Až potom řeš obsah aplikace.',
      ),
      'mistakes' => 
      array (
        0 => 'Považovat 502 za DNS chybu.',
        1 => 'Považovat validní DNS za důkaz funkčního webu.',
        2 => 'Ignorovat certifikát nebo časovou platnost.',
      ),
      'check' => 
      array (
        'q' => 'DNS vrací správnou IP a TCP/443 se otevře, ale TLS handshake selže. Kde hledáš?',
        'options' => 
        array (
          0 => 'Certifikát/TLS konfigurace.',
          1 => 'DHCP pool.',
          2 => 'Monitor.',
        ),
        'correct' => 0,
        'why' => 'První dvě vrstvy už máme doložené. Chyba nastává při TLS.',
      ),
    ),
    'icmp' => 
    array (
      'time' => '7–10 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Ping',
          1 => 'ICMP reachability',
          2 => '„Host odpovídá?“',
        ),
        'right' => 
        array (
          0 => 'TCP test',
          1 => 'Konkrétní port',
          2 => '„Služba je dostupná?“',
        ),
      ),
      'mental' => 'Ping je rychlé měření jedné části problému. Je užitečný, ale není rozsudek nad celou sítí ani aplikací.',
      'steps' => 
      array (
        0 => 'Pingni lokální bránu.',
        1 => 'Pingni známou IP mimo subnet.',
        2 => 'Porovnej s testem konkrétního TCP portu.',
        3 => 'Traceroute použij, když potřebuješ vidět cestu/hopy.',
        4 => 'Výsledek vždy interpretuj v kontextu firewall pravidel.',
      ),
      'mistakes' => 
      array (
        0 => '„Ping nejde = server je mrtvý.“ ICMP může být blokovaný.',
        1 => '„Ping jde = web funguje.“ Nemusí.',
        2 => 'Použít traceroute jako jediný důkaz root cause.',
      ),
      'check' => 
      array (
        'q' => 'Server neodpovídá na ping, ale HTTPS funguje. Je to možné?',
        'options' => 
        array (
          0 => 'Ano, ICMP může být filtrováno.',
          1 => 'Ne, je to fyzikálně nemožné.',
        ),
        'correct' => 0,
        'why' => 'Firewall může blokovat ICMP a současně povolit TCP/443.',
      ),
    ),
    'routing' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Klient',
            1 => '192.168.10.42/24',
          ),
          1 => 
          array (
            0 => 'Gateway',
            1 => '192.168.10.1',
          ),
          2 => 
          array (
            0 => 'Router',
            1 => 'route lookup',
          ),
          3 => 
          array (
            0 => 'Cíl',
            1 => '10.20.0.15',
          ),
        ),
      ),
      'mental' => 'Host nejprve rozhodne: je cíl lokální, nebo vzdálený? Pokud vzdálený, předá paket výchozí bráně. Routing je série těchto rozhodnutí.',
      'steps' => 
      array (
        0 => 'Urči subnet klienta.',
        1 => 'Porovnej cílovou IP se subnetem.',
        2 => 'Pokud je cíl mimo, najdi default route.',
        3 => 'Ověř dostupnost gateway.',
        4 => 'Na více routerech sleduj, kam vede další hop.',
      ),
      'mistakes' => 
      array (
        0 => 'Považovat default gateway za DNS server.',
        1 => 'Nastavit bránu mimo klientův lokální subnet.',
        2 => 'Měnit routu bez kontroly, zda cíl není lokální.',
      ),
      'check' => 
      array (
        'q' => 'Klient 192.168.10.42/24 chce na 10.20.0.15. Komu předá rámec jako první?',
        'options' => 
        array (
          0 => 'Výchozí bráně v lokální síti.',
          1 => 'Přímo MAC adrese vzdáleného serveru.',
          2 => 'DNS serveru.',
        ),
        'correct' => 0,
        'why' => 'Cíl je mimo lokální /24, proto klient použije route přes gateway.',
      ),
    ),
    'troubleshooting' => 
    array (
      'time' => '10–15 min',
      'level' => 'Klíčové',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => '1',
            1 => 'Lokální stav',
            2 => 'IP, link, Wi‑Fi/VLAN',
          ),
          1 => 
          array (
            0 => '2',
            1 => 'Brána',
            2 => 'lokální cesta',
          ),
          2 => 
          array (
            0 => '3',
            1 => 'IP konektivita',
            2 => 'známý cíl',
          ),
          3 => 
          array (
            0 => '4',
            1 => 'DNS',
            2 => 'jméno → IP',
          ),
          4 => 
          array (
            0 => '5',
            1 => 'Služba',
            2 => 'port/proces',
          ),
          5 => 
          array (
            0 => '6',
            1 => 'Aplikace',
            2 => 'HTTP, práva, data',
          ),
        ),
      ),
      'mental' => 'Dobré řešení incidentu není seznam náhodných příkazů. Je to řízené zužování prostoru možných příčin: hypotéza → měření → důkaz.',
      'steps' => 
      array (
        0 => 'Přepiš symptom bez domněnky.',
        1 => 'Najdi nejnižší vrstvu, kterou můžeš ověřit.',
        2 => 'Proveď jeden test, který něco skutečně rozliší.',
        3 => 'Z výsledku vytvoř další hypotézu.',
        4 => 'Změnu proveď až ve chvíli, kdy víš proč.',
        5 => 'Nakonec ověř původní symptom i vedlejší dopady.',
      ),
      'mistakes' => 
      array (
        0 => 'Restartovat „pro jistotu“ a tím zničit důkazy.',
        1 => 'Měnit více věcí najednou.',
        2 => 'Přestat po prvním úspěšném pingu.',
        3 => 'Neověřit stav po opravě.',
      ),
      'check' => 
      array (
        'q' => 'Jaký je nejlepší další krok po zjištění, že klient má správnou IP a pingne gateway?',
        'options' => 
        array (
          0 => 'Vybrat další test podle symptomu — např. IP cíl, DNS nebo port.',
          1 => 'Přeinstalovat OS.',
          2 => 'Náhodně změnit DNS i firewall zároveň.',
        ),
        'correct' => 0,
        'why' => 'Troubleshooting má navazovat na to, co už bylo prokázáno, a další test má rozlišit zbývající hypotézy.',
      ),
    ),
    'vlan-basics' => 
    array (
      'time' => '10–14 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'PC VLAN10',
            1 => '192.168.10.42',
          ),
          1 => 
          array (
            0 => 'L3 gateway',
            1 => 'routing / policy',
          ),
          2 => 
          array (
            0 => 'Server VLAN20',
            1 => '192.168.20.20',
          ),
        ),
      ),
      'mental' => 'VLAN vytváří logické L2 hranice. Mezi VLAN musí provoz projít routováním, kde lze uplatnit bezpečnostní pravidla.',
      'steps' => 
      array (
        0 => 'Urči VLAN zdroje a cíle.',
        1 => 'Porovnej subnety.',
        2 => 'Zjisti L3 gateway.',
        3 => 'Ověř inter-VLAN route/policy.',
        4 => 'Validuj konkrétní službu.',
      ),
      'mistakes' => 
      array (
        0 => 'Myslet si, že stejný switch = stejná síť.',
        1 => 'Zapomenout na VLAN access port.',
        2 => 'Testovat jen ping místo cílové služby.',
      ),
      'check' => 
      array (
        'q' => 'PC ve VLAN10 chce server ve VLAN20. Co je potřeba?',
        'options' => 
        array (
          0 => 'L3 routing mezi VLAN.',
          1 => 'ARP přímo na vzdálený server bez routeru.',
          2 => 'Jen DNS.',
        ),
        'correct' => 0,
        'why' => 'Různé VLAN jsou oddělené L2 domény a komunikace mezi nimi potřebuje routing.',
      ),
    ),
    'arp' => 
    array (
      'time' => '8–11 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Host',
            1 => 'Je cíl lokální?',
          ),
          1 => 
          array (
            0 => 'ARP',
            1 => 'Kdo má IP gateway?',
          ),
          2 => 
          array (
            0 => 'Gateway',
            1 => 'MAC odpověď',
          ),
          3 => 
          array (
            0 => 'Ethernet',
            1 => 'rámec na gateway',
          ),
        ),
      ),
      'mental' => 'ARP řeší lokální doručení rámce. Pro vzdálený cíl klient hledá MAC své gateway, ne MAC vzdáleného serveru.',
      'steps' => 
      array (
        0 => 'Rozhodni lokální/vzdálený cíl.',
        1 => 'Pro lokální cíl hledej MAC cíle.',
        2 => 'Pro vzdálený hledej MAC gateway.',
        3 => 'Ověř ARP/neighbor cache.',
        4 => 'Teprve potom sleduj L3 routing dál.',
      ),
      'mistakes' => 
      array (
        0 => 'Čekat MAC vzdáleného internetového serveru v ARP cache.',
        1 => 'Zaměnit ARP za DNS.',
        2 => 'Ignorovat chybějící sousedství gateway.',
      ),
      'check' => 
      array (
        'q' => 'Co bude mít klient v ARP cache při spojení na 8.8.8.8?',
        'options' => 
        array (
          0 => 'MAC lokální gateway.',
          1 => 'MAC serveru 8.8.8.8.',
          2 => 'DNS TXT záznam.',
        ),
        'correct' => 0,
        'why' => 'Ethernet rámec jde prvnímu lokálnímu hopu — gateway.',
      ),
    ),
    'nat' => 
    array (
      'time' => '9–13 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => '192.168.10.42:51522',
            1 => 'privátní klient',
          ),
          1 => 
          array (
            0 => 'NAT/PAT',
            1 => 'překlad',
          ),
          2 => 
          array (
            0 => '203.0.113.5:40012',
            1 => 'veřejná strana',
          ),
          3 => 
          array (
            0 => 'Internet',
            1 => 'cílová služba',
          ),
        ),
      ),
      'mental' => 'NAT přepisuje adresní identitu na hranici sítě. PAT navíc používá porty, aby rozlišil více současných spojení.',
      'steps' => 
      array (
        0 => 'Urči privátní zdroj.',
        1 => 'Najdi hraniční NAT zařízení.',
        2 => 'Sleduj překlad zdrojové adresy/portu.',
        3 => 'Zkontroluj návratovou tabulku.',
        4 => 'Pro inbound provoz hledej explicitní mapování.',
      ),
      'mistakes' => 
      array (
        0 => 'NAT = firewall.',
        1 => 'NAT vyřeší automaticky příchozí server.',
        2 => 'Překládat DNS jméno a NAT jako totéž.',
      ),
      'check' => 
      array (
        'q' => 'Proč může více klientů sdílet jednu veřejnou IPv4?',
        'options' => 
        array (
          0 => 'PAT rozlišuje spojení také porty.',
          1 => 'Protože mají stejnou MAC.',
          2 => 'DNS je spojí do jednoho.',
        ),
        'correct' => 0,
        'why' => 'Překladová tabulka používá kombinaci adres a portů.',
      ),
    ),
    'service-matrix' => 
    array (
      'time' => '10–14 min',
      'level' => 'Praktické',
      'visual' => 
      array (
        'type' => 'ports',
        'host' => 'server VLAN20',
        'items' => 
        array (
          0 => 
          array (
            0 => '443',
            1 => 'WEB',
            2 => 'open',
          ),
          1 => 
          array (
            0 => '22',
            1 => 'SSH admin only',
            2 => 'local',
          ),
          2 => 
          array (
            0 => '53',
            1 => 'DNS',
            2 => 'open',
          ),
          3 => 
          array (
            0 => '3306',
            1 => 'DB',
            2 => 'closed',
          ),
        ),
      ),
      'mental' => 'Firewall pravidlo je konkrétní vztah zdroj → cíl → protokol/port → akce. Čím přesnější, tím lépe se vysvětluje a kontroluje.',
      'steps' => 
      array (
        0 => 'Sepiš potřebné toky.',
        1 => 'Urči zdrojové subnety/VLAN.',
        2 => 'Urči cílové služby a porty.',
        3 => 'Povol jen potřebné kombinace.',
        4 => 'Otestuj povolené i zakázané scénáře.',
      ),
      'mistakes' => 
      array (
        0 => 'ALLOW any-any.',
        1 => 'Testovat pravidlo jen z firewallu.',
        2 => 'Neověřit, že zakázaný provoz je skutečně zakázaný.',
      ),
      'check' => 
      array (
        'q' => 'Které pravidlo je přesnější?',
        'options' => 
        array (
          0 => 'VLAN10 → WEB TCP/443 allow.',
          1 => 'VLAN10 → všechny servery všechny porty allow.',
          2 => 'Vypnout firewall.',
        ),
        'correct' => 0,
        'why' => 'Princip nejmenších oprávnění povoluje jen potřebný tok.',
      ),
    ),
    'ipv6-basics' => 
    array (
      'time' => '11–15 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Global',
            1 => '2001:db8::/32',
            2 => 'routovatelná',
          ),
          1 => 
          array (
            0 => 'LAN',
            1 => '/64',
            2 => 'prefix',
          ),
          2 => 
          array (
            0 => 'Host',
            1 => '::42',
            2 => 'interface',
          ),
          3 => 
          array (
            0 => 'Link-local',
            1 => 'fe80::/10',
            2 => 'lokální',
          ),
        ),
      ),
      'mental' => 'IPv6 čti jako prefix sítě + interface část. Neuč se celou adresu nazpaměť.',
      'steps' => 
      array (
        0 => 'Najdi prefix.',
        1 => 'Zkrať nuly.',
        2 => 'Rozliš global/link-local.',
        3 => 'Porovnej dvě /64 adresy.',
        4 => 'Ověř ip -6 addr.',
      ),
      'mistakes' => 
      array (
        0 => 'Dvakrát použít ::.',
        1 => 'Zaměnit /64 za počet hostů jako u IPv4.',
        2 => 'Ignorovat link-local gateway.',
      ),
      'check' => 
      array (
        'q' => 'Co typicky znamená /64 v LAN?',
        'options' => 
        array (
          0 => 'Prvních 64 bitů je prefix sítě.',
          1 => '64 hostů.',
          2 => 'TCP port 64.',
        ),
        'correct' => 0,
        'why' => 'Prefix length udává počet bitů síťové části.',
      ),
    ),
    'dns-record-types' => 
    array (
      'time' => '10–14 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'www',
            1 => 'CNAME',
          ),
          1 => 
          array (
            0 => 'web.school.cz',
            1 => 'A + AAAA',
          ),
          2 => 
          array (
            0 => 'IPv4',
            1 => '203.0.113.50',
          ),
          3 => 
          array (
            0 => 'IPv6',
            1 => '2001:db8::50',
          ),
        ),
      ),
      'mental' => 'Typ záznamu říká, jaký vztah DNS popisuje.',
      'steps' => 
      array (
        0 => 'Urči A.',
        1 => 'Urči AAAA.',
        2 => 'Urči CNAME.',
        3 => 'Ověř dig/nslookup.',
        4 => 'Porovnej TTL.',
      ),
      'mistakes' => 
      array (
        0 => 'CNAME s IP hodnotou.',
        1 => 'AAAA pro IPv4.',
        2 => 'Ignorovat chybný AAAA v dual-stack.',
      ),
      'check' => 
      array (
        'q' => 'Který záznam mapuje jméno na IPv6?',
        'options' => 
        array (
          0 => 'AAAA',
          1 => 'A',
          2 => 'CNAME',
        ),
        'correct' => 0,
        'why' => 'AAAA je adresní záznam pro IPv6.',
      ),
    ),
    'monitoring-basics' => 
    array (
      'time' => '11–15 min',
      'level' => 'Praktické',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Ping',
            1 => 'host',
          ),
          1 => 
          array (
            0 => 'TCP',
            1 => 'port',
          ),
          2 => 
          array (
            0 => 'HTTP',
            1 => 'protokol',
          ),
          3 => 
          array (
            0 => 'User flow',
            1 => 'funkce',
          ),
        ),
      ),
      'mental' => 'Čím výš monitoruješ, tím lépe měříš skutečnou zkušenost uživatele.',
      'steps' => 
      array (
        0 => 'Vyber cíl.',
        1 => 'Zvol check.',
        2 => 'Nastav interval/timeout.',
        3 => 'Nastav threshold.',
        4 => 'Ověř alert.',
      ),
      'mistakes' => 
      array (
        0 => 'Ping jako jediný web monitoring.',
        1 => 'Alert po jednom packet loss.',
        2 => 'Bez success criteria.',
      ),
      'check' => 
      array (
        'q' => 'Co nejlépe ověří web z pohledu uživatele?',
        'options' => 
        array (
          0 => 'HTTPS request s očekávaným statusem/obsahem.',
          1 => 'Pouze ping.',
          2 => 'ARP cache.',
        ),
        'correct' => 0,
        'why' => 'Aplikační check ověřuje skutečnou službu.',
      ),
    ),
    'dhcp-reservations' => 
    array (
      'time' => '9–13 min',
      'level' => 'Praktické',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'MAC',
            1 => 'AA:BB...',
          ),
          1 => 
          array (
            0 => 'DHCP',
            1 => 'reservation',
          ),
          2 => 
          array (
            0 => 'IP',
            1 => '192.168.10.50',
          ),
          3 => 
          array (
            0 => 'Options',
            1 => 'GW + DNS',
          ),
        ),
      ),
      'mental' => 'Reservation kombinuje stabilní adresu s centrální DHCP správou.',
      'steps' => 
      array (
        0 => 'Najdi MAC/ID.',
        1 => 'Vyber IP podle plánu.',
        2 => 'Zapiš reservation.',
        3 => 'Renew klienta.',
        4 => 'Ověř lease.',
      ),
      'mistakes' => 
      array (
        0 => 'IP koliduje s jiným static zařízením.',
        1 => 'Špatná MAC.',
        2 => 'Klient neobnoví lease.',
      ),
      'check' => 
      array (
        'q' => 'Jaká je výhoda reservation?',
        'options' => 
        array (
          0 => 'Stabilní IP a centrální správa DHCP options.',
          1 => 'Nemusí existovat DHCP server.',
          2 => 'Automaticky vypne firewall.',
        ),
        'correct' => 0,
        'why' => 'Adresa zůstává řízená serverem, ale může být stabilní pro konkrétní zařízení.',
      ),
    ),
    'packet-analysis' => 
    array (
      'time' => '12–16 min',
      'level' => 'Praktické',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'ARP',
            1 => 'kdo má gateway?',
          ),
          1 => 
          array (
            0 => 'DNS',
            1 => 'jaká je IP?',
          ),
          2 => 
          array (
            0 => 'TCP',
            1 => 'SYN/SYN-ACK',
          ),
          3 => 
          array (
            0 => 'App',
            1 => 'request/response',
          ),
        ),
      ),
      'mental' => 'Capture čti jako časovou osu důkazů. Nejdřív filtruj podle otázky, kterou chceš zodpovědět.',
      'steps' => 
      array (
        0 => 'Definuj symptom.',
        1 => 'Vyber relevantní filtr.',
        2 => 'Najdi request.',
        3 => 'Najdi odpověď nebo její absenci.',
        4 => 'Propoj packet evidence s další diagnostikou.',
      ),
      'mistakes' => 
      array (
        0 => 'Capture bez filtru a bez hypotézy.',
        1 => 'Zaměnit RST za timeout.',
        2 => 'Z jednoho paketu usoudit celý root cause.',
      ),
      'check' => 
      array (
        'q' => 'SYN odejde a ihned přijde RST. Co to nejlépe znamená?',
        'options' => 
        array (
          0 => 'Cíl je dosažitelný, ale daný port/spojení je odmítnuté.',
          1 => 'DNS určitě nefunguje.',
          2 => 'Klient nemá IP adresu.',
        ),
        'correct' => 0,
        'why' => 'RST je aktivní TCP odpověď; síťová cesta alespoň do cíle funguje.',
      ),
    ),
    'subnetting-vlsm' => 
    array (
      'time' => '14–19 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'subnet',
        'network' => '192.168.10.0/24',
        'segments' => 
        array (
          0 => '90 hostů',
          1 => '40 hostů',
          2 => '20 hostů',
          3 => '10 hostů',
        ),
      ),
      'mental' => 'VLSM řeš od největšího segmentu a po každém přidělení posuň další volnou hranici.',
      'steps' => 
      array (
        0 => 'Seřaď segmenty podle velikosti.',
        1 => 'Vyber prefix pro největší.',
        2 => 'Zapiš network/broadcast.',
        3 => 'Pokračuj další volnou adresou.',
        4 => 'Ověř překryv a rezervu.',
      ),
      'mistakes' => 
      array (
        0 => 'Začít nejmenším subnetem.',
        1 => 'Zapomenout hranici bloku.',
        2 => 'Překrývající se rozsahy.',
      ),
      'check' => 
      array (
        'q' => 'Jaký je bezpečný postup VLSM?',
        'options' => 
        array (
          0 => 'Přidělovat od největšího požadavku a hlídat hranice každého bloku.',
          1 => 'Přidělovat náhodně.',
          2 => 'Všem dát /24.',
        ),
        'correct' => 0,
        'why' => 'Tím minimalizuješ fragmentaci a chyby.',
      ),
    ),
    'network-security-basics' => 
    array (
      'time' => '13–18 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'ports',
        'items' => 
        array (
          0 => 
          array (
            0 => 'USERS → WEB',
            1 => '443',
            2 => 'allow',
          ),
          1 => 
          array (
            0 => 'USERS → SERVERS',
            1 => 'ANY',
            2 => 'deny',
          ),
          2 => 
          array (
            0 => 'MGMT → SERVERS',
            1 => '22',
            2 => 'allow',
          ),
        ),
      ),
      'mental' => 'Bezpečný návrh definuje nejen co fungovat má, ale i co fungovat nesmí.',
      'steps' => 
      array (
        0 => 'Definuj zóny.',
        1 => 'Sepiš potřebné služby.',
        2 => 'Vytvoř konkrétní allow.',
        3 => 'Přidej deny.',
        4 => 'Otestuj pozitivní i negativní cestu.',
      ),
      'mistakes' => 
      array (
        0 => 'ANY→ANY allow.',
        1 => 'Pravidlo bez source scope.',
        2 => 'Po změně testovat jen povolený scénář.',
      ),
      'check' => 
      array (
        'q' => 'Co nejlépe odpovídá least privilege?',
        'options' => 
        array (
          0 => 'Povolit přesně nutný tok a ostatní nechat blokované.',
          1 => 'Povolit vše interně.',
          2 => 'Použít jen jeden společný VLAN.',
        ),
        'correct' => 0,
        'why' => 'Minimální scope snižuje dopad chyby i útoku.',
      ),
    ),
    'dns-dhcp-operations' => 
    array (
      'time' => '12–17 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'TTL',
            1 => 'cache',
          ),
          1 => 
          array (
            0 => 'DNS change',
            1 => 'nová IP',
          ),
          2 => 
          array (
            0 => 'Lease',
            1 => 'DHCP čas',
          ),
          3 => 
          array (
            0 => 'Renew',
            1 => 'nová konfigurace',
          ),
        ),
      ),
      'mental' => 'Změna konfigurace a změna pozorovaná klientem nejsou vždy ve stejný okamžik.',
      'steps' => 
      array (
        0 => 'Zjisti TTL/lease.',
        1 => 'Naplánuj změnu.',
        2 => 'Sleduj starý i nový stav.',
        3 => 'Vynucuj renew jen když je potřeba.',
        4 => 'Po stabilizaci vrať normální hodnoty.',
      ),
      'mistakes' => 
      array (
        0 => 'Čekat okamžitou propagaci.',
        1 => 'Ignorovat cache.',
        2 => 'Snížit TTL až po změně.',
      ),
      'check' => 
      array (
        'q' => 'Proč klient stále vidí starou DNS adresu?',
        'options' => 
        array (
          0 => 'Může mít validní cache do vypršení TTL.',
          1 => 'DNS vždy používá DHCP port.',
          2 => 'TCP automaticky drží starý A record celý den.',
        ),
        'correct' => 0,
        'why' => 'TTL určuje dobu platnosti cacheované odpovědi.',
      ),
    ),
    'service-debug-chain' => 
    array (
      'time' => '14–19 min',
      'level' => 'Praktické+',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'DNS',
            1 => 'jméno → IP',
          ),
          1 => 
          array (
            0 => 'TCP',
            1 => 'port reachable',
          ),
          2 => 
          array (
            0 => 'TLS',
            1 => 'handshake + cert',
          ),
          3 => 
          array (
            0 => 'HTTP',
            1 => 'status + app',
          ),
        ),
      ),
      'mental' => 'Najdi první vrstvu, která nedává očekávaný důkaz. Tam začíná tvoje další hypotéza.',
      'steps' => 
      array (
        0 => 'Ověř DNS.',
        1 => 'Ověř TCP port.',
        2 => 'Ověř TLS.',
        3 => 'Ověř HTTP.',
        4 => 'Po opravě zopakuj chain.',
      ),
      'mistakes' => 
      array (
        0 => 'Začít restartem všeho.',
        1 => 'Zaměnit ping za test služby.',
        2 => 'Ignorovat negativní kontrolu.',
      ),
      'check' => 
      array (
        'q' => 'DNS i TCP/443 fungují, TLS handshake selže. Kde hledat dál?',
        'options' => 
        array (
          0 => 'Certifikát, TLS konfigurace nebo proxy terminace.',
          1 => 'DHCP pool klienta.',
          2 => 'ARP tabulka tiskárny.',
        ),
        'correct' => 0,
        'why' => 'Evidence lokalizuje problém do vrstvy TLS.',
      ),
    ),
    'linux-filesystem' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => '/etc obvykle obsahuje konfiguraci, /var proměnlivá provo',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Absolutní cesta začíná v /, relativní v aktuálním pracov',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Před destruktivní operací kontroluj scope pomocí pwd a p',
          ),
        ),
      ),
      'mental' => 'Filesystem je hierarchie začínající v / a administrátor potřebuje rozumět cestám dřív, než začne měnit soubory.',
      'steps' => 
      array (
        0 => '/etc obvykle obsahuje konfiguraci, /var proměnlivá provozní data a logy, /home uživatelská data.',
        1 => 'Absolutní cesta začíná v /, relativní v aktuálním pracovním adresáři.',
        2 => 'Před destruktivní operací kontroluj scope pomocí pwd a přesné cesty.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Linux filesystem a CLI“?',
        'options' => 
        array (
          0 => 'Filesystem je hierarchie začínající v / a administrátor potřebuje rozumět cestám dřív, než začne měnit soubory.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => '/etc obvykle obsahuje konfiguraci, /var proměnlivá provozní data a logy, /home uživatelská data.',
      ),
    ),
    'users-permissions' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'r/w/x mají jiný význam pro soubor a adresář.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Skupina je běžný způsob, jak sdílet přístup bez world-wr',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'chmod mění mode, chown vlastnictví.',
          ),
        ),
      ),
      'mental' => 'Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.',
      'steps' => 
      array (
        0 => 'r/w/x mají jiný význam pro soubor a adresář.',
        1 => 'Skupina je běžný způsob, jak sdílet přístup bez world-writable oprávnění.',
        2 => 'chmod mění mode, chown vlastnictví.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Uživatelé, skupiny a oprávnění“?',
        'options' => 
        array (
          0 => 'Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'r/w/x mají jiný význam pro soubor a adresář.',
      ),
    ),
    'processes-systemd' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'systemctl status spojuje stav, PID a poslední logy.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Restart ukončí a znovu spustí proces; reload může načíst',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Enable řeší boot-time activation, ne nutně okamžité spuš',
          ),
        ),
      ),
      'mental' => 'Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.',
      'steps' => 
      array (
        0 => 'systemctl status spojuje stav, PID a poslední logy.',
        1 => 'Restart ukončí a znovu spustí proces; reload může načíst konfiguraci bez plného restartu.',
        2 => 'Enable řeší boot-time activation, ne nutně okamžité spuštění.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Procesy a systemd“?',
        'options' => 
        array (
          0 => 'Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'systemctl status spojuje stav, PID a poslední logy.',
      ),
    ),
    'journal-logs' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Začni časovým oknem a konkrétní unit.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Severity je vodítko, ne absolutní pravda.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Koreluj log s měřením portu, health checkem a změnovou h',
          ),
        ),
      ),
      'mental' => 'Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.',
      'steps' => 
      array (
        0 => 'Začni časovým oknem a konkrétní unit.',
        1 => 'Severity je vodítko, ne absolutní pravda.',
        2 => 'Koreluj log s měřením portu, health checkem a změnovou historií.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „journalctl a provozní logy“?',
        'options' => 
        array (
          0 => 'Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Začni časovým oknem a konkrétní unit.',
      ),
    ),
    'ssh-keys-ops' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Private key zůstává tajný, public key se instaluje na se',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Příliš široká práva privátního klíče mohou být klientem ',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Timeout, connection refused a permission denied jsou růz',
          ),
        ),
      ),
      'mental' => 'SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.',
      'steps' => 
      array (
        0 => 'Private key zůstává tajný, public key se instaluje na server.',
        1 => 'Příliš široká práva privátního klíče mohou být klientem odmítnuta.',
        2 => 'Timeout, connection refused a permission denied jsou různé evidence.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „SSH klíče a vzdálená správa“?',
        'options' => 
        array (
          0 => 'SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Private key zůstává tajný, public key se instaluje na server.',
      ),
    ),
    'linux-firewall' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => '0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Firewall pravidlo má mít co nejmenší source/destination/',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Validuj povolený i zakázaný scénář.',
          ),
        ),
      ),
      'mental' => 'Dostupnost služby vzniká kombinací listeneru, bind adresy, routingu a firewall policy.',
      'steps' => 
      array (
        0 => '0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0.0.1 jen lokálně.',
        1 => 'Firewall pravidlo má mít co nejmenší source/destination/service scope.',
        2 => 'Validuj povolený i zakázaný scénář.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Linux firewall a service exposure“?',
        'options' => 
        array (
          0 => 'Dostupnost služby vzniká kombinací listeneru, bind adresy, routingu a firewall policy.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => '0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0.0.1 jen lokálně.',
      ),
    ),
    'web-service-linux' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Lokální curl odlišuje problém aplikace od vzdálené síťov',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'HTTP status je evidence aplikační/proxy vrstvy.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Po opravě testuj původní uživatelskou cestu, ne pouze lo',
          ),
        ),
      ),
      'mental' => 'End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.',
      'steps' => 
      array (
        0 => 'Lokální curl odlišuje problém aplikace od vzdálené síťové cesty.',
        1 => 'HTTP status je evidence aplikační/proxy vrstvy.',
        2 => 'Po opravě testuj původní uživatelskou cestu, ne pouze localhost.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Linux webová služba: end-to-end cesta“?',
        'options' => 
        array (
          0 => 'End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Lokální curl odlišuje problém aplikace od vzdálené síťové cesty.',
      ),
    ),
    'shell-cron' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Cron/timer běží v jiném prostředí než interaktivní shell',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Secrets nepatří do zdrojového skriptu.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Naplánování není důkaz úspěchu; ověř reálný výsledek.',
          ),
        ),
      ),
      'mental' => 'Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.',
      'steps' => 
      array (
        0 => 'Cron/timer běží v jiném prostředí než interaktivní shell; PATH a working directory mohou být jiné.',
        1 => 'Secrets nepatří do zdrojového skriptu.',
        2 => 'Naplánování není důkaz úspěchu; ověř reálný výsledek.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Shell automatizace a plánování“?',
        'options' => 
        array (
          0 => 'Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Cron/timer běží v jiném prostředí než interaktivní shell; PATH a working directory mohou být jiné.',
      ),
    ),
    'linux-file-troubleshooting' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Než měníš práva nebo mažeš data, zjisti cestu, vlastníka, mount, kapacitu a skutečný symptom.',
      'steps' => 
      array (
        0 => 'Přepiš symptom bez domněnky o příčině.',
        1 => 'Vytvoř dvě nejpravděpodobnější hypotézy.',
        2 => 'Vyber nejmenší příkaz/test, který je rozliší.',
        3 => 'Zapiš, co výsledek potvrzuje a co ještě nepotvrzuje.',
        4 => 'Proveď nejmenší bezpečnou změnu a end-to-end validaci.',
      ),
      'mistakes' => 
      array (
        0 => 'Začít restartem nebo plošnou změnou bez důkazu.',
        1 => 'Považovat jeden úspěšný test za důkaz celé cesty.',
        2 => 'Neověřit po opravě negativní scénář nebo rollback.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Symptom',
          1 => 'Hypotéza',
          2 => 'Nejmenší test',
          3 => 'Důkaz',
          4 => 'Bezpečná změna',
          5 => 'Validace',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'ssh-key-operations' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'SSH klíč je dvojice identity; diagnostika odděluje síť, výběr klíče, oprávnění a serverovou autorizaci.',
      'steps' => 
      array (
        0 => 'Přepiš symptom bez domněnky o příčině.',
        1 => 'Vytvoř dvě nejpravděpodobnější hypotézy.',
        2 => 'Vyber nejmenší příkaz/test, který je rozliší.',
        3 => 'Zapiš, co výsledek potvrzuje a co ještě nepotvrzuje.',
        4 => 'Proveď nejmenší bezpečnou změnu a end-to-end validaci.',
      ),
      'mistakes' => 
      array (
        0 => 'Začít restartem nebo plošnou změnou bez důkazu.',
        1 => 'Považovat jeden úspěšný test za důkaz celé cesty.',
        2 => 'Neověřit po opravě negativní scénář nebo rollback.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Symptom',
          1 => 'Hypotéza',
          2 => 'Nejmenší test',
          3 => 'Důkaz',
          4 => 'Bezpečná změna',
          5 => 'Validace',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'stateful-firewall' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Stavový firewall rozlišuje nové a navazující spojení a umožňuje přesnější least-privilege pravidla.',
      'steps' => 
      array (
        0 => 'Přepiš symptom bez domněnky o příčině.',
        1 => 'Vytvoř dvě nejpravděpodobnější hypotézy.',
        2 => 'Vyber nejmenší příkaz/test, který je rozliší.',
        3 => 'Zapiš, co výsledek potvrzuje a co ještě nepotvrzuje.',
        4 => 'Proveď nejmenší bezpečnou změnu a end-to-end validaci.',
      ),
      'mistakes' => 
      array (
        0 => 'Začít restartem nebo plošnou změnou bez důkazu.',
        1 => 'Považovat jeden úspěšný test za důkaz celé cesty.',
        2 => 'Neověřit po opravě negativní scénář nebo rollback.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Symptom',
          1 => 'Hypotéza',
          2 => 'Nejmenší test',
          3 => 'Důkaz',
          4 => 'Bezpečná změna',
          5 => 'Validace',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'bash-error-handling' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Skript má kontrolovat předpoklady, selhat čitelně, logovat a být bezpečně opakovatelný.',
      'steps' => 
      array (
        0 => 'Přepiš symptom bez domněnky o příčině.',
        1 => 'Vytvoř dvě nejpravděpodobnější hypotézy.',
        2 => 'Vyber nejmenší příkaz/test, který je rozliší.',
        3 => 'Zapiš, co výsledek potvrzuje a co ještě nepotvrzuje.',
        4 => 'Proveď nejmenší bezpečnou změnu a end-to-end validaci.',
      ),
      'mistakes' => 
      array (
        0 => 'Začít restartem nebo plošnou změnou bez důkazu.',
        1 => 'Považovat jeden úspěšný test za důkaz celé cesty.',
        2 => 'Neověřit po opravě negativní scénář nebo rollback.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Symptom',
          1 => 'Hypotéza',
          2 => 'Nejmenší test',
          3 => 'Důkaz',
          4 => 'Bezpečná změna',
          5 => 'Validace',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
  ),
  'nextLesson' => 
  array (
    'id' => 'network_small_office',
    'title' => 'Lekce 2 · Navrhni a ověř malou kancelářskou síť',
    'subtitle' => 'Dalších 2 × 45 minut',
    'goal' => 'Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.',
    'unlock_note' => 'Odemkne se po dokončení prvního praktického labu.',
    'knowledge' => 
    array (
      0 => 'vlan-basics',
      1 => 'arp',
      2 => 'routing',
      3 => 'nat',
      4 => 'service-matrix',
    ),
    'schedule' => 
    array (
      0 => 
      array (
        'time' => '0–12',
        'title' => 'Topologie + VLAN',
        'text' => 'Rozděl users a servers do dvou logických sítí.',
      ),
      1 => 
      array (
        'time' => '12–25',
        'title' => 'ARP a první hop',
        'text' => 'Rozhodni, jakou MAC klient hledá pro lokální a vzdálený cíl.',
      ),
      2 => 
      array (
        'time' => '25–40',
        'title' => 'Inter-VLAN routing',
        'text' => 'Sleduj paket mezi USERS a SERVERS.',
      ),
      3 => 
      array (
        'time' => '40–52',
        'title' => 'NAT ven',
        'text' => 'Sleduj privátní klient → veřejná adresa → internet.',
      ),
      4 => 
      array (
        'time' => '52–72',
        'title' => 'Service matrix + firewall',
        'text' => 'Povol web, zakaž běžným uživatelům SSH, ponech správu admin VLAN.',
      ),
      5 => 
      array (
        'time' => '72–85',
        'title' => 'Incident',
        'text' => 'Web z USERS nejde, admin SSH funguje — najdi chybu v policy.',
      ),
      6 => 
      array (
        'time' => '85–90',
        'title' => 'Exit ticket',
        'text' => 'Popiš cestu paketu jednou větou po vrstvách.',
      ),
    ),
    'topology' => 
    array (
      'title' => 'OfficeLab',
      'lines' => 
      array (
        0 => 'USERS VLAN10 192.168.10.0/24',
        1 => '  PC01 192.168.10.42 → GW 192.168.10.1',
        2 => '                 │ inter-VLAN routing / firewall',
        3 => 'SERVERS VLAN20 192.168.20.0/24',
        4 => '  DNS .53   WEB .20:443   SFTP .30:22',
        5 => '                 │ NAT/PAT',
        6 => '              Internet',
      ),
    ),
    'steps' => 
    array (
      0 => 
      array (
        'id' => 'vlan',
        'time' => '12 min',
        'title' => '01 · Rozděl síť do VLAN',
        'kind' => 'knowledge',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'vlan-basics',
        ),
        'intro' => 'USERS a SERVERS mají být oddělené.',
        'tasks' => 
        array (
          0 => 'Dokonči VLAN Knowledge Tour.',
          1 => 'USERS: VLAN10 / 192.168.10.0/24.',
          2 => 'SERVERS: VLAN20 / 192.168.20.0/24.',
          3 => 'Urči gateway .1 v každém subnetu.',
        ),
      ),
      1 => 
      array (
        'id' => 'arp',
        'time' => '13 min',
        'title' => '02 · ARP a první hop',
        'kind' => 'quiz',
        'xp' => 20,
        'knowledge' => 
        array (
          0 => 'arp',
        ),
        'intro' => 'PC01 192.168.10.42/24 komunikuje s WEB 192.168.20.20/24.',
        'question' => 'Čí MAC adresu bude PC01 hledat přes ARP jako první?',
        'options' => 
        array (
          0 => 'MAC webserveru 192.168.20.20.',
          1 => 'MAC své gateway 192.168.10.1.',
          2 => 'MAC DNS serveru.',
        ),
        'correct' => 1,
        'explanation' => 'Cíl je mimo lokální /24, proto Ethernet rámec směřuje na MAC lokální gateway.',
      ),
      2 => 
      array (
        'id' => 'routing',
        'time' => '15 min',
        'title' => '03 · Sleduj inter-VLAN routing',
        'kind' => 'quiz',
        'xp' => 20,
        'knowledge' => 
        array (
          0 => 'routing',
        ),
        'demo' => 
        array (
          'label' => 'ip route',
          'lines' => 
          array (
            0 => '192.168.10.0/24 dev vlan10',
            1 => '192.168.20.0/24 dev vlan20',
            2 => 'default via 203.0.113.1',
          ),
        ),
        'question' => 'Která route se použije pro 192.168.20.20?',
        'options' => 
        array (
          0 => '192.168.20.0/24 dev vlan20',
          1 => 'default route',
          2 => '192.168.10.0/24',
        ),
        'correct' => 0,
        'explanation' => 'Přímo připojená /24 route je konkrétnější než default route.',
        'tasks' => 
        array (
          0 => 'Nakresli cestu PC01 → GW VLAN10 → router/firewall → VLAN20 → WEB.',
        ),
      ),
      3 => 
      array (
        'id' => 'nat',
        'time' => '12 min',
        'title' => '04 · Privátní klient ven přes NAT',
        'kind' => 'knowledge',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'nat',
        ),
        'intro' => 'Teď PC01 otevírá veřejný web 1.1.1.1:443.',
        'tasks' => 
        array (
          0 => 'Dokonči NAT Knowledge Tour.',
          1 => 'Najdi místo, kde se mění zdrojová privátní adresa.',
          2 => 'Vysvětli, proč odpověď najde cestu zpět přes překladovou tabulku.',
        ),
      ),
      4 => 
      array (
        'id' => 'matrix',
        'time' => '20 min',
        'title' => '05 · Service matrix a firewall',
        'kind' => 'knowledge',
        'xp' => 30,
        'knowledge' => 
        array (
          0 => 'service-matrix',
        ),
        'intro' => 'Navrhni minimální pravidla pro USERS a ADMIN.',
        'demo' => 
        array (
          'label' => 'Požadavky',
          'lines' => 
          array (
            0 => 'USERS → WEB :443 = povolit',
            1 => 'USERS → DNS :53 = povolit',
            2 => 'USERS → SFTP :22 = zakázat',
            3 => 'ADMIN → SFTP :22 = povolit',
          ),
        ),
        'tasks' => 
        array (
          0 => 'Dokonči Service Matrix Knowledge Tour.',
          1 => 'Sepiš 4 řádky source → destination → service → action.',
          2 => 'Zkontroluj, že nemáš any-any allow.',
        ),
      ),
      5 => 
      array (
        'id' => 'incident',
        'time' => '18 min',
        'title' => '06 · Incident: web nejde, SSH admin funguje',
        'kind' => 'quiz',
        'xp' => 35,
        'intro' => 'Stav: DNS funguje, PC pingne gateway. Admin VLAN se na SFTP připojí. USERS mají TCP/443 na WEB timeout. Firewall obsahuje: VLAN10 → VLAN20 TCP/22 DENY; VLAN10 → VLAN20 ANY DENY; VLAN99 → VLAN20 TCP/22 ALLOW.',
        'question' => 'Jaká je nejpravděpodobnější oprava?',
        'options' => 
        array (
          0 => 'Vypnout firewall.',
          1 => 'Přidat před obecný deny přesné VLAN10 → WEB TCP/443 ALLOW a validovat z PC01.',
          2 => 'Povolit VLAN10 → VLAN20 ANY.',
        ),
        'correct' => 1,
        'explanation' => 'Potřebuješ nejmenší cílené pravidlo pro požadovanou službu. Plošné povolení zbytečně rozšiřuje přístup.',
        'tasks' => 
        array (
          0 => 'Po opravě otestuj TCP/443 z USERS.',
          1 => 'Ověř, že SSH z USERS zůstává blokované.',
          2 => 'Zapiš, co důkazy potvrzují.',
        ),
      ),
    ),
    'finisher' => 
    array (
      'title' => 'Rychlík · Guest VLAN',
      'duration' => '15–20 min',
      'text' => 'Přidej VLAN30 GUEST 192.168.30.0/24. Hosté mají internet + veřejné DNS, ale žádný přístup do SERVERS.',
      'deliverables' => 
      array (
        0 => 'subnet + gateway',
        1 => '3 firewall pravidla',
        2 => '2 validační testy',
      ),
    ),
  ),
  'extendedLessons' => 
  array (
    0 => 
    array (
      'id' => 'net_services_lab',
      'number' => 3,
      'title' => 'Lekce 3 · DNS + DHCP jako služby',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit DNS a DHCP jako běžící služby, jejich konfiguraci, porty, logy a systematickou validaci.',
      'knowledge' => 
      array (
        0 => 'dns',
        1 => 'dhcp',
        2 => 'ports',
        3 => 'troubleshooting',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Služby a porty',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'DHCP lease',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'DNS záznam',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Validace klienta',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Incident',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Exit ticket',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'ports',
          'time' => '12 min',
          'title' => '01 · Kdo na čem poslouchá',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'ports',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči DNS port 53.',
            1 => 'Urči DHCP UDP 67/68.',
            2 => 'Vysvětli rozdíl mezi službou a portem.',
          ),
        ),
        1 => 
        array (
          'id' => 'dhcp',
          'time' => '16 min',
          'title' => '02 · Lease od začátku do konce',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'dhcp',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Projdi DORA.',
            1 => 'Urči gateway a DNS předané klientovi.',
          ),
        ),
        2 => 
        array (
          'id' => 'dns',
          'time' => '17 min',
          'title' => '03 · A záznam',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'dns',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř/interpretuj A záznam.',
            1 => 'Ověř odpověď přes nslookup/dig.',
          ),
        ),
        3 => 
        array (
          'id' => 'client',
          'time' => '17 min',
          'title' => '04 · Validace z klienta',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'ipconfig /all nebo ip addr.',
            1 => 'ping gateway.',
            2 => 'nslookup jména.',
            3 => 'TCP test služby.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '18 min',
          'title' => '05 · Incident: lease ano, jméno ne',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Klient má platnou IP, gateway funguje, ping 1.1.1.1 funguje, ale nslookup timeout. Kde začít?',
          'options' => 
          array (
            0 => 'DNS server / jeho dosažitelnost a konfigurace.',
            1 => 'Měnit grafickou kartu.',
            2 => 'Přeinstalovat celý OS.',
          ),
          'correct' => 0,
          'explanation' => 'Síťová cesta funguje, problém je v překladu jmen.',
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '10 min',
          'title' => '06 · Exit ticket',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Napiš 4krokový postup diagnostiky.',
            1 => 'U každého kroku napiš, co výsledkem dokazuješ.',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'id' => 'net_monitor_harden',
      'number' => 4,
      'title' => 'Lekce 4 · Monitoring + základní hardening',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Rozlišit dostupnost, zdraví služby a bezpečné minimum: monitoring, logy, firewall a účty.',
      'knowledge' => 
      array (
        0 => 'service-matrix',
        1 => 'troubleshooting',
        2 => 'ssh-sftp',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Co monitorovat',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Health check',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Logy',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Firewall',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'SSH minimum',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Incident report',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'monitor',
          'time' => '12 min',
          'title' => '01 · Dostupnost vs. zdraví',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'troubleshooting',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Definuj 3 metriky.',
            1 => 'Rozliš ping od HTTP health checku.',
          ),
        ),
        1 => 
        array (
          'id' => 'health',
          'time' => '16 min',
          'title' => '02 · Health endpoint',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni /health kontrolu.',
            1 => 'Definuj expected status.',
          ),
        ),
        2 => 
        array (
          'id' => 'logs',
          'time' => '17 min',
          'title' => '03 · Čti log podle času',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'troubleshooting',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Najdi čas incidentu.',
            1 => 'Spoj request s chybou.',
          ),
        ),
        3 => 
        array (
          'id' => 'firewall',
          'time' => '17 min',
          'title' => '04 · Minimum firewall pravidel',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'service-matrix',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Povol jen potřebné služby.',
            1 => 'Ověř blokaci nepotřebného portu.',
          ),
        ),
        4 => 
        array (
          'id' => 'ssh',
          'time' => '18 min',
          'title' => '05 · SSH minimum',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'ssh-sftp',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Použij klíč místo sdíleného hesla.',
            1 => 'Ověř permissions klíče.',
          ),
        ),
        5 => 
        array (
          'id' => 'report',
          'time' => '10 min',
          'title' => '06 · Incident report',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co patří do dobrého incident reportu?',
          'options' => 
          array (
            0 => 'Symptom, důkaz, příčina, změna a ověření.',
            1 => 'Pouze věta „už to funguje“.',
            2 => 'Seznam všech příkazů bez závěru.',
          ),
          'correct' => 0,
          'explanation' => 'Report má zachytit rozhodovací řetězec a ověření výsledku.',
        ),
      ),
    ),
    2 => 
    array (
      'id' => 'net_ipv6_dns_records',
      'number' => 5,
      'title' => 'Lekce 5 · IPv6 + DNS záznamy',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy.',
      'knowledge' => 
      array (
        0 => 'ipv6-basics',
        1 => 'dns-record-types',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'IPv6 formát',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Prefix',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'A vs. AAAA',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'CNAME',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Dual-stack incident',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Exit',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'ipv6',
          'time' => '15 min',
          'title' => '01 · IPv6 bez paniky',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'ipv6-basics',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Projdi zkracování adres.',
            1 => 'Najdi prefix /64.',
            2 => 'Rozliš global a link-local.',
          ),
        ),
        1 => 
        array (
          'id' => 'prefix',
          'time' => '15 min',
          'title' => '02 · Co patří do /64',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Porovnej dvě IPv6 adresy.',
            1 => 'Urči síťovou část.',
            2 => 'Vysvětli, co znamená /64.',
          ),
        ),
        2 => 
        array (
          'id' => 'records',
          'time' => '15 min',
          'title' => '03 · A / AAAA / CNAME',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'dns-record-types',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přiřaď typ záznamu k účelu.',
            1 => 'Vysvětli A vs. AAAA.',
            2 => 'Vysvětli alias CNAME.',
          ),
        ),
        3 => 
        array (
          'id' => 'cname',
          'time' => '17 min',
          'title' => '04 · Alias bez duplikace IP',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni www → web.school.cz.',
            1 => 'Zkontroluj, kam ukazuje cílové jméno.',
            2 => 'Neduplikuj IP, pokud nepotřebuješ.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '18 min',
          'title' => '05 · Dual-stack incident',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'AAAA záznam ukazuje na nedostupnou IPv6 adresu, A záznam je správně. Co může uživatel pozorovat?',
          'options' => 
          array (
            0 => 'Někteří klienti mohou zkoušet IPv6 a web může působit nedostupně nebo pomalu.',
            1 => 'DNS se automaticky přepne na FTP.',
            2 => 'IPv4 A záznam se smaže.',
          ),
          'correct' => 0,
          'explanation' => 'Dual-stack klient může preferovat IPv6; chybný AAAA proto může způsobit reálný problém i při správném A.',
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '10 min',
          'title' => '06 · Exit ticket',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Napiš A/AAAA/CNAME vlastními slovy.',
            1 => 'Uveď jeden příkaz pro ověření DNS.',
          ),
        ),
      ),
    ),
    3 => 
    array (
      'id' => 'net_monitoring_dhcp',
      'number' => 6,
      'title' => 'Lekce 6 · Monitoring + DHCP reservations',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Přestat čekat na hlášení uživatele: měřit dostupnost služby a navrhnout stabilní DHCP adresaci pro známá zařízení.',
      'knowledge' => 
      array (
        0 => 'monitoring-basics',
        1 => 'dhcp-reservations',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Co monitorovat',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'SLA signály',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Reservation',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Lease tabulka',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Incident',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Review',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'monitor',
          'time' => '15 min',
          'title' => '01 · Host up ≠ služba OK',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'monitoring-basics',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Porovnej ping, TCP a HTTP check.',
            1 => 'Vyber správný check pro web.',
            2 => 'Urči interval a timeout.',
          ),
        ),
        1 => 
        array (
          'id' => 'signals',
          'time' => '15 min',
          'title' => '02 · Signál bez šumu',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Definuj 3 metriky.',
            1 => 'Urči warning a critical.',
            2 => 'Zabraň alertu z jednoho náhodného timeoutu.',
          ),
        ),
        2 => 
        array (
          'id' => 'reserve',
          'time' => '15 min',
          'title' => '03 · DHCP reservation',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'dhcp-reservations',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Spáruj MAC a IP.',
            1 => 'Zvol adresu mimo dynamický pool nebo podle politiky.',
            2 => 'Vysvětli rozdíl reservation vs. ruční static IP.',
          ),
        ),
        3 => 
        array (
          'id' => 'leases',
          'time' => '17 min',
          'title' => '04 · Lease tabulka',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Najdi zařízení podle MAC.',
            1 => 'Zkontroluj konflikt.',
            2 => 'Obnov lease klienta.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '18 min',
          'title' => '05 · Monitoring hlásí web',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Ping serveru je OK, ale HTTP check vrací 503. Co je nejlepší první závěr?',
          'options' => 
          array (
            0 => 'Síťová cesta k hostu funguje, ale aplikační služba není zdravá.',
            1 => 'Server je vypnutý.',
            2 => 'DNS určitě neexistuje.',
          ),
          'correct' => 0,
          'explanation' => 'Ping ověřil dostupnost hostu; HTTP 503 je aplikační odpověď a posouvá diagnostiku výš.',
        ),
        5 => 
        array (
          'id' => 'review',
          'time' => '10 min',
          'title' => '06 · Review',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Navrhni jeden monitoring check pro DNS.',
            1 => 'Navrhni jednu reservation pro tiskárnu.',
          ),
        ),
      ),
    ),
    4 => 
    array (
      'id' => 'net_packet_analysis',
      'number' => 7,
      'title' => 'Lekce 7 · Packet journey + Wireshark mindset',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Použít packet-level evidence k rozlišení lokální, DNS, TCP a aplikační závady bez bezhlavého spouštění všech nástrojů.',
      'knowledge' => 
      array (
        0 => 'packet-analysis',
        1 => 'dns-record-types',
        2 => 'monitoring-basics',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Packet journey',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Filtry',
        ),
        2 => 
        array (
          'time' => '30–47',
          'title' => 'ARP/DNS',
        ),
        3 => 
        array (
          'time' => '47–64',
          'title' => 'TCP',
        ),
        4 => 
        array (
          'time' => '64–82',
          'title' => 'Incident',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'journey',
          'time' => '15 min',
          'title' => '01 · Cesta paketu',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'packet-analysis',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Packet Journey Lab.',
            1 => 'Rozliš ARP/DNS/TCP evidence.',
            2 => 'Zapiš hypotézu.',
          ),
        ),
        1 => 
        array (
          'id' => 'filters',
          'time' => '15 min',
          'title' => '02 · Filtr není dekorace',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Navrhni filtr pro DNS.',
            1 => 'Navrhni filtr pro TCP/443.',
            2 => 'Vysvětli, proč nechceš celý capture.',
          ),
        ),
        2 => 
        array (
          'id' => 'dns',
          'time' => '17 min',
          'title' => '03 · Query bez response',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Interpretuj DNS timeout.',
            1 => 'Odliš NXDOMAIN.',
            2 => 'Navrhni další test.',
          ),
        ),
        3 => 
        array (
          'id' => 'tcp',
          'time' => '17 min',
          'title' => '04 · SYN, SYN-ACK, RST',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš timeout vs. reject.',
            1 => 'Urči otevřený port.',
            2 => 'Vysvětli RST.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '18 min',
          'title' => '05 · Evidence-first incident',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Vyber 3 nejkratší testy.',
            1 => 'Urči root cause.',
            2 => 'Navrhni validaci po opravě.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co znamená SYN → RST?',
          'options' => 
          array (
            0 => 'Cíl odpovídá, ale spojení/port odmítá.',
            1 => 'DNS query timeout.',
            2 => 'Klient nemá gateway.',
          ),
          'correct' => 0,
          'explanation' => 'RST je aktivní TCP odpověď a odlišuje se od tichého timeoutu.',
        ),
      ),
    ),
    5 => 
    array (
      'id' => 'net_vlsm_security',
      'number' => 8,
      'title' => 'Lekce 8 · IPv4 II: VLSM + síťové zóny',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout efektivní adresní plán a současně definovat minimální potřebnou komunikaci mezi zónami.',
      'knowledge' => 
      array (
        0 => 'subnetting-vlsm',
        1 => 'network-security-basics',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–16',
          'title' => 'VLSM',
        ),
        1 => 
        array (
          'time' => '16–31',
          'title' => 'Address plan',
        ),
        2 => 
        array (
          'time' => '31–46',
          'title' => 'Zones',
        ),
        3 => 
        array (
          'time' => '46–63',
          'title' => 'ACL policy',
        ),
        4 => 
        array (
          'time' => '63–82',
          'title' => 'Validation',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'vlsm',
          'time' => '16 min',
          'title' => '01 · VLSM planner',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'subnetting-vlsm',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči VLSM Planner Lab.',
            1 => 'Seřaď segmenty podle velikosti.',
            2 => 'Ověř hranice.',
          ),
        ),
        1 => 
        array (
          'id' => 'plan',
          'time' => '15 min',
          'title' => '02 · Address plan',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přiděl 4 subnety.',
            1 => 'Zapiš gateway.',
            2 => 'Nech rozumnou rezervu.',
          ),
        ),
        2 => 
        array (
          'id' => 'zones',
          'time' => '15 min',
          'title' => '03 · Trust zones',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'network-security-basics',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Security Zones Lab.',
            1 => 'Definuj USERS/SERVERS/MGMT.',
            2 => 'Sepiš potřebné služby.',
          ),
        ),
        3 => 
        array (
          'id' => 'policy',
          'time' => '17 min',
          'title' => '04 · ACL/service policy',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Povol USERS→WEB 443.',
            1 => 'Povol MGMT→SERVERS 22.',
            2 => 'Blokuj ostatní serverový provoz.',
          ),
        ),
        4 => 
        array (
          'id' => 'validation',
          'time' => '19 min',
          'title' => '05 · Positive + negative validation',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Ověř povolený HTTPS.',
            1 => 'Ověř blokovaný SSH z USERS.',
            2 => 'Ověř management SSH.',
            3 => 'Zapiš důkazy.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co je správná validace firewall změny?',
          'options' => 
          array (
            0 => 'Otestovat povolené i zakázané scénáře.',
            1 => 'Jen ping gateway.',
            2 => 'Jen restart firewallu.',
          ),
          'correct' => 0,
          'explanation' => 'Musíš potvrdit funkčnost i zachování bezpečnostních hranic.',
        ),
      ),
    ),
    6 => 
    array (
      'id' => 'net_dns_dhcp_debug',
      'number' => 9,
      'title' => 'Lekce 9 · DNS/DHCP II + service debugging',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit časové chování cache/lease a spojit DNS, TCP, TLS a HTTP do rychlé evidence-first diagnostiky služby.',
      'knowledge' => 
      array (
        0 => 'dns-dhcp-operations',
        1 => 'service-debug-chain',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'TTL',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Lease',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Change plan',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Debug chain',
        ),
        4 => 
        array (
          'time' => '62–82',
          'title' => 'Incident',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'ttl',
          'time' => '15 min',
          'title' => '01 · DNS cache timeline',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'dns-dhcp-operations',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči TTL & Lease Timeline Lab.',
            1 => 'Vysvětli TTL.',
            2 => 'Naplánuj DNS změnu.',
          ),
        ),
        1 => 
        array (
          'id' => 'lease',
          'time' => '15 min',
          'title' => '02 · DHCP lease lifecycle',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Urči lease time.',
            1 => 'Popiš renew/rebind.',
            2 => 'Vysvětli dopad změny poolu.',
          ),
        ),
        2 => 
        array (
          'id' => 'change',
          'time' => '15 min',
          'title' => '03 · Controlled change',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sniž TTL před změnou.',
            1 => 'Definuj validační okno.',
            2 => 'Naplánuj návrat TTL.',
          ),
        ),
        3 => 
        array (
          'id' => 'chain',
          'time' => '17 min',
          'title' => '04 · Service debug chain',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'service-debug-chain',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Service Chain Lab.',
            1 => 'Sepiš 4 krátké testy.',
            2 => 'Urči první chybějící důkaz.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '20 min',
          'title' => '05 · Incident drill',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Ověř DNS.',
            1 => 'Ověř TCP/443.',
            2 => 'Ověř TLS.',
            3 => 'Ověř HTTP.',
            4 => 'Navrhni opravu a retest.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'DNS i TCP/443 fungují, ale certifikát je expirovaný. Kde je závada?',
          'options' => 
          array (
            0 => 'V TLS/certifikační vrstvě.',
            1 => 'V DHCP poolu.',
            2 => 'V ARP cache klienta.',
          ),
          'correct' => 0,
          'explanation' => 'Předchozí vrstvy už mají pozitivní evidence.',
        ),
      ),
    ),
    7 => 
    array (
      'id' => 'os_linux_cli',
      'number' => 10,
      'title' => 'Lekce 10 · Linux CLI + filesystem: orientace bez klikání',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Orientovat se v Linux filesystemu, používat pwd/ls/cd/cp/mv/rm bezpečně a vysvětlit rozdíl absolutní a relativní cesty.',
      'knowledge' => 
      array (
        0 => 'linux-filesystem',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Filesystem mapa',
          'text' => 'Rozliš /home, /etc, /var, /tmp a /usr.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Navigace',
          'text' => 'Použij pwd, ls -la, cd.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Práce se soubory',
          'text' => 'Vytvoř adresář lab.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Cesty',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Inspect before change',
          'text' => 'Použij file/stat/cat nebo head podle typu dat.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'fs',
          'time' => '15 min',
          'title' => '01 · Filesystem mapa',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'linux-filesystem',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš /home, /etc, /var, /tmp a /usr.',
            1 => 'Najdi domovský adresář uživatele.',
          ),
        ),
        1 => 
        array (
          'id' => 'nav',
          'time' => '15 min',
          'title' => '02 · Navigace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Použij pwd, ls -la, cd.',
            1 => 'Vyzkoušej relativní i absolutní cestu.',
          ),
        ),
        2 => 
        array (
          'id' => 'files',
          'time' => '15 min',
          'title' => '03 · Práce se soubory',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř adresář lab.',
            1 => 'Zkopíruj a přesuň testovací soubor.',
            2 => 'Před rm ověř pwd a cestu.',
          ),
        ),
        3 => 
        array (
          'id' => 'path',
          'time' => '12 min',
          'title' => '04 · Cesty',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Která cesta začínající / je absolutní?',
          'options' => 
          array (
            0 => '/var/log/nginx/error.log',
            1 => '../logs/error.log',
            2 => './error.log',
          ),
          'correct' => 0,
          'explanation' => 'Absolutní cesta začíná od root filesystemu.',
        ),
        4 => 
        array (
          'id' => 'inspect',
          'time' => '15 min',
          'title' => '05 · Inspect before change',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Použij file/stat/cat nebo head podle typu dat.',
            1 => 'Needituj konfiguraci naslepo.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Proč je před destruktivním příkazem důležité ověřit pwd a argument?',
          'options' => 
          array (
            0 => 'Chyba v cestě může zasáhnout jiná data než zamýšlíš.',
            1 => 'Protože pwd restartuje shell.',
            2 => 'Kvůli DNS.',
          ),
          'correct' => 0,
          'explanation' => 'Bezpečná práce v CLI stojí na kontrole scope změny.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Napiš význam 5 adresářů.',
        1 => '5 příkazů + co vrací.',
        2 => 'Příklad absolutní/relativní cesty.',
        3 => 'Bezpečnostní pravidlo před rm.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej sandbox/testovací adresář.',
        1 => 'Nedávej destruktivní příklady nad skutečnými systémovými cestami.',
      ),
    ),
    8 => 
    array (
      'id' => 'os_users_permissions',
      'number' => 11,
      'title' => 'Lekce 11 · Uživatelé, skupiny a oprávnění',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.',
      'knowledge' => 
      array (
        0 => 'users-permissions',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Owner / Group / Other',
          'text' => 'Přečti ls -l.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'rwx',
          'text' => 'Převeď rw-r----- na význam.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Skupinový přístup',
          'text' => 'Navrhni skupinu webteam.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Least privilege',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Vlastnictví',
          'text' => 'Vysvětli rozdíl chmod a chown.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '15 min',
          'title' => '01 · Owner / Group / Other',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'users-permissions',
          ),
          'tasks' => 
          array (
            0 => 'Přečti ls -l.',
            1 => 'Rozliš owner, group a ostatní.',
          ),
        ),
        1 => 
        array (
          'id' => 'mode',
          'time' => '15 min',
          'title' => '02 · rwx',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Převeď rw-r----- na význam.',
            1 => 'Navrhni oprávnění pro konfigurační soubor s tajným obsahem.',
          ),
        ),
        2 => 
        array (
          'id' => 'group',
          'time' => '15 min',
          'title' => '03 · Skupinový přístup',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni skupinu webteam.',
            1 => 'Odděl read-only a write potřebu.',
          ),
        ),
        3 => 
        array (
          'id' => 'least',
          'time' => '12 min',
          'title' => '04 · Least privilege',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Které oprávnění je bezpečnější pro soubor, který má číst vlastník i skupina, ale měnit jen vlastník?',
          'options' => 
          array (
            0 => '640',
            1 => '777',
            2 => '666',
          ),
          'correct' => 0,
          'explanation' => '640 dává owner rw, group r a others nic.',
        ),
        4 => 
        array (
          'id' => 'ownership',
          'time' => '15 min',
          'title' => '05 · Vlastnictví',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vysvětli rozdíl chmod a chown.',
            1 => 'Uveď, kdy použít skupinu místo world-write.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Proč není chmod 777 univerzální oprava?',
          'options' => 
          array (
            0 => 'Dává všem zbytečně široká práva a maskuje skutečný model přístupu.',
            1 => 'Protože zakazuje čtení.',
            2 => 'Protože mění IP adresu.',
          ),
          'correct' => 0,
          'explanation' => 'Oprávnění se mají odvíjet od potřebných rolí.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Rozepiš 3 permission strings.',
        1 => 'Navrhni mode pro config/log/shared file.',
        2 => 'Vysvětli chmod vs chown.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej model „kdo potřebuje co dělat“.',
        1 => 'Propoj s budoucím SSH a webserverem.',
      ),
    ),
    9 => 
    array (
      'id' => 'os_processes_systemd',
      'number' => 12,
      'title' => 'Lekce 12 · Procesy + systemd: služba není magie',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.',
      'knowledge' => 
      array (
        0 => 'processes-systemd',
        1 => 'journal-logs',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Proces a služba',
          'text' => 'Použij ps/top nebo ekvivalent.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'systemctl status',
          'text' => 'Přečti Active, Main PID a poslední log řádky.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'start/restart/reload',
          'text' => 'Vysvětli rozdíl restart a reload.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Enable vs start',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Failed service',
          'text' => 'Z statusu najdi první konkrétní chybu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'process',
          'time' => '15 min',
          'title' => '01 · Proces a služba',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'processes-systemd',
          ),
          'tasks' => 
          array (
            0 => 'Použij ps/top nebo ekvivalent.',
            1 => 'Rozliš PID a service unit.',
          ),
        ),
        1 => 
        array (
          'id' => 'status',
          'time' => '15 min',
          'title' => '02 · systemctl status',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přečti Active, Main PID a poslední log řádky.',
            1 => 'Nezačínej restartem.',
          ),
        ),
        2 => 
        array (
          'id' => 'actions',
          'time' => '15 min',
          'title' => '03 · start/restart/reload',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vysvětli rozdíl restart a reload.',
            1 => 'Před reloadem ověř konfiguraci, pokud služba umí syntax check.',
          ),
        ),
        3 => 
        array (
          'id' => 'enable',
          'time' => '12 min',
          'title' => '04 · Enable vs start',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co dělá enable typicky?',
          'options' => 
          array (
            0 => 'Nastaví automatické spuštění služby při odpovídajícím boot targetu; nemusí ji právě teď spustit.',
            1 => 'Vymaže logy služby.',
            2 => 'Otevře firewall port.',
          ),
          'correct' => 0,
          'explanation' => 'Enable a runtime start jsou dvě odlišné věci.',
        ),
        4 => 
        array (
          'id' => 'fail',
          'time' => '15 min',
          'title' => '05 · Failed service',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Z statusu najdi první konkrétní chybu.',
            1 => 'Navrhni nejmenší další test.',
          ),
          'knowledge' => 
          array (
            0 => 'journal-logs',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Proč je „restartuj to“ slabá první diagnostika?',
          'options' => 
          array (
            0 => 'Mění stav dřív, než získáš evidence, a může skrýt příčinu.',
            1 => 'Restart je vždy pomalý.',
            2 => 'Restart funguje jen na Windows.',
          ),
          'correct' => 0,
          'explanation' => 'Diagnostika má nejprve pozorovat a lokalizovat problém.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Process vs service.',
        1 => 'systemctl status – 5 polí k přečtení.',
        2 => 'Restart vs reload vs enable.',
        3 => 'Incident: služba failed – další test.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Doporuč bezpečný lokální lab/VM.',
        1 => 'Připomínej syntax check před reloadem webserveru.',
      ),
    ),
    10 => 
    array (
      'id' => 'os_logs_journal',
      'number' => 13,
      'title' => 'Lekce 13 · Logy a journalctl: časová osa důkazů',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Použít logy jako cílený zdroj evidence podle služby, času a severity místo čtení tisíců řádků.',
      'knowledge' => 
      array (
        0 => 'journal-logs',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Co je užitečný log',
          'text' => 'Najdi timestamp, source/service, severity a message.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Filtruj',
          'text' => 'Omez log na konkrétní unit/službu.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Sleduj změnu',
          'text' => 'Spusť follow/tail jen při reprodukci testovacího problému.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Noise',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Korelace',
          'text' => 'Propoj service status + journal + port test.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'log',
          'time' => '15 min',
          'title' => '01 · Co je užitečný log',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'journal-logs',
          ),
          'tasks' => 
          array (
            0 => 'Najdi timestamp, source/service, severity a message.',
            1 => 'Odliš symptom od root cause.',
          ),
        ),
        1 => 
        array (
          'id' => 'filter',
          'time' => '15 min',
          'title' => '02 · Filtruj',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Omez log na konkrétní unit/službu.',
            1 => 'Omez časové okno kolem incidentu.',
          ),
        ),
        2 => 
        array (
          'id' => 'follow',
          'time' => '15 min',
          'title' => '03 · Sleduj změnu',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Spusť follow/tail jen při reprodukci testovacího problému.',
            1 => 'Zapiš změnu před/po.',
          ),
        ),
        3 => 
        array (
          'id' => 'noise',
          'time' => '12 min',
          'title' => '04 · Noise',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je nejlepší při dlouhém logu?',
          'options' => 
          array (
            0 => 'Začít incidentním časem a konkrétní službou.',
            1 => 'Číst celý log od začátku systému.',
            2 => 'Vymazat log a čekat.',
          ),
          'correct' => 0,
          'explanation' => 'Filtr snižuje šum a chrání kauzalitu.',
        ),
        4 => 
        array (
          'id' => 'correlate',
          'time' => '15 min',
          'title' => '05 · Korelace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Propoj service status + journal + port test.',
            1 => 'Napiš jednu hypotézu, kterou evidence vylučuje.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co znamená, že log neobsahuje chybu?',
          'options' => 
          array (
            0 => 'Samo o sobě to nedokazuje, že systém je v pořádku; možná sleduješ špatnou vrstvu nebo zdroj.',
            1 => 'Služba je určitě zdravá.',
            2 => 'Firewall je vypnutý.',
          ),
          'correct' => 0,
          'explanation' => 'Absence záznamu je slabší evidence než cílený pozitivní test.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Incident timestamp.',
        1 => '3 filtry logu.',
        2 => 'Hypotéza potvrzena/vyloučena.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Učte časovou korelaci napříč logy.',
        1 => 'Student má vysvětlit, proč konkrétní filtr používá.',
      ),
    ),
    11 => 
    array (
      'id' => 'os_ssh_keys',
      'number' => 14,
      'title' => 'Lekce 14 · SSH/SFTP: bezpečný vzdálený přístup',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Nastavit a ověřit SSH klíčové přihlášení, rozlišit autentizaci od síťové dostupnosti a použít SFTP bezpečně.',
      'knowledge' => 
      array (
        0 => 'ssh-keys-ops',
        1 => 'ssh-sftp',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Cesta SSH spojení',
          'text' => 'Ověř DNS/IP, TCP/22 a teprve potom autentizaci.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Klíče',
          'text' => 'Rozliš private/public key.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Permissions',
          'text' => 'Ověř bezpečné oprávnění privátního klíče.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Permission denied',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'SFTP',
          'text' => 'Přeneste testovací soubor.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'path',
          'time' => '15 min',
          'title' => '01 · Cesta SSH spojení',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'ssh-keys-ops',
          ),
          'tasks' => 
          array (
            0 => 'Ověř DNS/IP, TCP/22 a teprve potom autentizaci.',
            1 => 'Rozliš timeout a Permission denied.',
          ),
        ),
        1 => 
        array (
          'id' => 'keys',
          'time' => '15 min',
          'title' => '02 · Klíče',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš private/public key.',
            1 => 'Private key nesdílej ani neukládej do projektu.',
          ),
        ),
        2 => 
        array (
          'id' => 'permissions',
          'time' => '15 min',
          'title' => '03 · Permissions',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř bezpečné oprávnění privátního klíče.',
            1 => 'Zkontroluj authorized_keys vlastníka.',
          ),
        ),
        3 => 
        array (
          'id' => 'deny',
          'time' => '12 min',
          'title' => '04 · Permission denied',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'TCP/22 funguje, ale SSH vrací Permission denied. Kde je nejsilnější hypotéza?',
          'options' => 
          array (
            0 => 'Autentizace/uživatel/klíč, ne základní síťová dostupnost.',
            1 => 'DHCP server.',
            2 => 'Monitor počítače.',
          ),
          'correct' => 0,
          'explanation' => 'Aplikace už odpověděla, takže síťová cesta k SSH existuje.',
        ),
        4 => 
        array (
          'id' => 'sftp',
          'time' => '15 min',
          'title' => '05 · SFTP',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přeneste testovací soubor.',
            1 => 'Ověř cílovou cestu a práva.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Který soubor je tajný?',
          'options' => 
          array (
            0 => 'Private key.',
            1 => 'Public key.',
            2 => 'known_hosts bez dalšího kontextu.',
          ),
          'correct' => 0,
          'explanation' => 'Private key chrání identitu a nesmí se sdílet.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'SSH diagnostická cesta.',
        1 => 'Private vs public key.',
        2 => '3 příčiny Permission denied.',
        3 => 'SFTP transfer check.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nikdy nevyžaduj reálné studentské private keys do odevzdání.',
        1 => 'Pracuj s testovacími klíči/VM.',
      ),
    ),
    12 => 
    array (
      'id' => 'os_firewall_services',
      'number' => 15,
      'title' => 'Lekce 15 · Firewall + služby: co poslouchá a kdo se tam dostane',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Propojit listener, bind adresu, firewall a client test do jednoho diagnostického modelu.',
      'knowledge' => 
      array (
        0 => 'linux-firewall',
        1 => 'ports',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Listener ≠ dostupnost',
          'text' => 'Rozliš proces poslouchá / bind address / firewall / routa.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'ss/lsof mindset',
          'text' => 'Zjisti port a bind adresu.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Firewall rule',
          'text' => 'Povol jen potřebný source/service.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Bind',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Positive + negative test',
          'text' => 'Ověř povolenou cestu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'layers',
          'time' => '15 min',
          'title' => '01 · Listener ≠ dostupnost',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'linux-firewall',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš proces poslouchá / bind address / firewall / routa.',
            1 => 'Sepiš service matrix.',
          ),
        ),
        1 => 
        array (
          'id' => 'listen',
          'time' => '15 min',
          'title' => '02 · ss/lsof mindset',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zjisti port a bind adresu.',
            1 => 'Rozliš 127.0.0.1 vs 0.0.0.0 vs konkrétní IP.',
          ),
        ),
        2 => 
        array (
          'id' => 'rule',
          'time' => '15 min',
          'title' => '03 · Firewall rule',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Povol jen potřebný source/service.',
            1 => 'Nevytvářej any-any pravidlo.',
          ),
        ),
        3 => 
        array (
          'id' => 'localhost',
          'time' => '12 min',
          'title' => '04 · Bind',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Služba poslouchá pouze 127.0.0.1:8080. Co očekáváš z jiného stroje?',
          'options' => 
          array (
            0 => 'Nebude přímo dostupná přes síťové rozhraní, i kdyby firewall port dovoloval.',
            1 => 'Bude vždy dostupná.',
            2 => 'DNS se automaticky změní.',
          ),
          'correct' => 0,
          'explanation' => 'Loopback bind omezuje listener na lokální host.',
        ),
        4 => 
        array (
          'id' => 'validate',
          'time' => '15 min',
          'title' => '05 · Positive + negative test',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř povolenou cestu.',
            1 => 'Ověř, že zakázaná cesta stále nefunguje.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je správná firewall validace?',
          'options' => 
          array (
            0 => 'Otestovat povolený i zakázaný scénář z relevantního zdroje.',
            1 => 'Otestovat jen ping.',
            2 => 'Zkontrolovat pouze syntaxi pravidla.',
          ),
          'correct' => 0,
          'explanation' => 'Bez negativního testu nevíš, zda jsi neotevřel příliš mnoho.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Service matrix 4 řádky.',
        1 => 'Listener/bind evidence.',
        2 => 'Firewall allow + deny test.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej sandbox pravidla; nedělej změny na produkčním školním serveru.',
        1 => 'Důraz na rollback/console access v reálné správě.',
      ),
    ),
    13 => 
    array (
      'id' => 'os_web_service',
      'number' => 16,
      'title' => 'Lekce 16 · Linux webová služba: od procesu k HTTP',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Nasadit nebo analyzovat jednoduchou webovou službu a ověřit process → listener → firewall → DNS → HTTP.',
      'knowledge' => 
      array (
        0 => 'web-service-linux',
        1 => 'dns',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Service chain',
          'text' => 'Proces běží.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Local health',
          'text' => 'Otestuj localhost/health nebo lokální curl.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Remote path',
          'text' => 'Otestuj službu z klienta.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'HTTP evidence',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'DNS name',
          'text' => 'Namapuj testovací jméno nebo interpretuj záznam.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Runbook',
          'text' => 'Napiš 5 kroků ověření služby od lokálního procesu po klienta.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'chain',
          'time' => '15 min',
          'title' => '01 · Service chain',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'web-service-linux',
          ),
          'tasks' => 
          array (
            0 => 'Proces běží.',
            1 => 'Listener existuje.',
            2 => 'Síťová cesta.',
            3 => 'DNS.',
            4 => 'HTTP odpověď.',
          ),
        ),
        1 => 
        array (
          'id' => 'local',
          'time' => '15 min',
          'title' => '02 · Local health',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Otestuj localhost/health nebo lokální curl.',
            1 => 'Zapiš HTTP status.',
          ),
        ),
        2 => 
        array (
          'id' => 'remote',
          'time' => '15 min',
          'title' => '03 · Remote path',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Otestuj službu z klienta.',
            1 => 'Při rozdílu local/remote lokalizuj bind/firewall/routing.',
          ),
        ),
        3 => 
        array (
          'id' => '502',
          'time' => '12 min',
          'title' => '04 · HTTP evidence',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Když reverse proxy vrátí 502, co to dokazuje?',
          'options' => 
          array (
            0 => 'Proxy je dosažitelná, ale má problém komunikovat s upstreamem nebo dostat validní odpověď.',
            1 => 'DNS vždy selhalo.',
            2 => 'Klient nemá IP.',
          ),
          'correct' => 0,
          'explanation' => 'HTTP 502 vzniká na proxy/gateway vrstvě.',
        ),
        4 => 
        array (
          'id' => 'dns',
          'time' => '15 min',
          'title' => '05 · DNS name',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Namapuj testovací jméno nebo interpretuj záznam.',
            1 => 'Ověř, že jméno míří na správný endpoint.',
          ),
        ),
        5 => 
        array (
          'id' => 'doc',
          'time' => '15 min',
          'title' => '06 · Runbook',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Napiš 5 kroků ověření služby od lokálního procesu po klienta.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Service chain diagram.',
        1 => 'Local/remote test.',
        2 => 'HTTP status evidence.',
        3 => '5krokový runbook.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Může být simulované prostředí, Docker/VM nebo předpřipravený lab.',
        1 => 'Nezáviset na veřejném DNS.',
      ),
    ),
    14 => 
    array (
      'id' => 'os_shell_cron',
      'number' => 17,
      'title' => 'Lekce 17 · Shell + cron: malá automatizace s logem a bezpečným selháním',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.',
      'knowledge' => 
      array (
        0 => 'shell-cron',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Skript jako opakovatelný postup',
          'text' => 'Použij jasný vstup, výstup a exit status.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Guard clauses',
          'text' => 'Před změnou ověř podmínky.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Log output',
          'text' => 'Přidej timestamp a výsledek.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Cron/system timer',
          'text' => 'Naplánuj testovací úlohu.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Cron problém',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Ověření',
          'text' => 'Prokaž, že job proběhl.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'script',
          'time' => '15 min',
          'title' => '01 · Skript jako opakovatelný postup',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'shell-cron',
          ),
          'tasks' => 
          array (
            0 => 'Použij jasný vstup, výstup a exit status.',
            1 => 'Neukládej hesla přímo do skriptu.',
          ),
        ),
        1 => 
        array (
          'id' => 'safe',
          'time' => '15 min',
          'title' => '02 · Guard clauses',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Před změnou ověř podmínky.',
            1 => 'Při chybě skonči s nenulovým exit code.',
          ),
        ),
        2 => 
        array (
          'id' => 'log',
          'time' => '15 min',
          'title' => '03 · Log output',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přidej timestamp a výsledek.',
            1 => 'Rozliš stdout/stderr konceptuálně.',
          ),
        ),
        3 => 
        array (
          'id' => 'schedule',
          'time' => '15 min',
          'title' => '04 · Cron/system timer',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Naplánuj testovací úlohu.',
            1 => 'Zohledni pracovní adresář a PATH.',
          ),
        ),
        4 => 
        array (
          'id' => 'cron',
          'time' => '12 min',
          'title' => '05 · Cron problém',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Skript funguje ručně, ale ne z cronu. Co ověřit mezi prvními?',
          'options' => 
          array (
            0 => 'PATH, pracovní adresář, uživatele a logovaný stderr.',
            1 => 'Barvu terminálu.',
            2 => 'DNS TTL webu.',
          ),
          'correct' => 0,
          'explanation' => 'Naplánované prostředí se může lišit od interaktivního shellu.',
        ),
        5 => 
        array (
          'id' => 'verify',
          'time' => '15 min',
          'title' => '06 · Ověření',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Prokaž, že job proběhl.',
            1 => 'Ověř výsledek, ne jen existenci schedule.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Pseudo/real script 8–20 řádků.',
        1 => 'Guard condition.',
        2 => 'Log sample.',
        3 => 'Schedule.',
        4 => 'Verification evidence.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej neškodné úlohy (např. health report, kopie test souboru).',
        1 => 'Neautomatizujte destruktivní příkazy.',
      ),
    ),
    15 => 
    array (
      'id' => 'os_net_capstone',
      'number' => 18,
      'title' => 'Lekce 18 · Capstone: nefunguje služba – Linux + síť v jednom incidentu',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Systematicky vyřešit kombinovaný incident od klienta přes DNS/síť/firewall až po systemd službu a doložit root cause.',
      'knowledge' => 
      array (
        0 => 'linux-filesystem',
        1 => 'processes-systemd',
        2 => 'journal-logs',
        3 => 'ssh-keys-ops',
        4 => 'linux-firewall',
        5 => 'web-service-linux',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Symptom + scope',
          'text' => 'Zapiš co nefunguje, komu a od kdy.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Síťová evidence',
          'text' => 'IP/gateway/DNS.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Server evidence',
          'text' => 'Service status.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Nejmenší oprava',
          'text' => 'Změň jen potvrzenou příčinu.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Validace',
          'text' => 'Ověř původní user path.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Incident note',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'triage',
          'time' => '15 min',
          'title' => '01 · Symptom + scope',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zapiš co nefunguje, komu a od kdy.',
            1 => 'Odděl client vs server symptom.',
          ),
        ),
        1 => 
        array (
          'id' => 'network',
          'time' => '15 min',
          'title' => '02 · Síťová evidence',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'IP/gateway/DNS.',
            1 => 'Port test.',
            2 => 'Poznamenej první selhávající vrstvu.',
          ),
        ),
        2 => 
        array (
          'id' => 'server',
          'time' => '15 min',
          'title' => '03 · Server evidence',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Service status.',
            1 => 'Listener.',
            2 => 'Logy kolem incidentu.',
          ),
        ),
        3 => 
        array (
          'id' => 'fix',
          'time' => '15 min',
          'title' => '04 · Nejmenší oprava',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Změň jen potvrzenou příčinu.',
            1 => 'Připrav rollback.',
          ),
        ),
        4 => 
        array (
          'id' => 'validate',
          'time' => '15 min',
          'title' => '05 · Validace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř původní user path.',
            1 => 'Ověř službu lokálně.',
            2 => 'Ověř, že security policy zůstala zachována.',
          ),
        ),
        5 => 
        array (
          'id' => 'post',
          'time' => '12 min',
          'title' => '06 · Incident note',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co patří do stručného incident záznamu?',
          'options' => 
          array (
            0 => 'Symptom, evidence, root cause, změna a validační důkaz.',
            1 => 'Jen „restart hotov“.',
            2 => 'Seznam všech příkazů bez závěru.',
          ),
          'correct' => 0,
          'explanation' => 'Záznam má umožnit pochopit rozhodnutí a ověřit opravu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Incident timeline.',
        1 => 'Hypotézy a testy.',
        2 => 'Root cause.',
        3 => 'Oprava + rollback.',
        4 => '3 validační důkazy.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Vhodné jako mastery/capstone 3.A.',
        1 => 'Hodnoť diagnostický proces, ne rychlost náhodného nalezení chyby.',
      ),
    ),
    16 => 
    array (
      'id' => 'v30_3a_19',
      'number' => 19,
      'title' => 'Lekce 19 · Filesystem incident',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Diagnostikovat permission/disk/mount problém bez změn naslepo.',
      'knowledge' => 
      array (
        0 => 'linux-file-troubleshooting',
        1 => 'linux-filesystem',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'linux-file-troubleshooting',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'linux-file-troubleshooting',
            1 => 'linux-filesystem',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    17 => 
    array (
      'id' => 'v30_3a_20',
      'number' => 20,
      'title' => 'Lekce 20 · systemd dependency lab',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Rozlišit problém procesu, unit konfigurace, dependency a restart policy.',
      'knowledge' => 
      array (
        0 => 'processes-systemd',
        1 => 'processes-systemd',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'processes-systemd',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'processes-systemd',
            1 => 'processes-systemd',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    18 => 
    array (
      'id' => 'v30_3a_21',
      'number' => 21,
      'title' => 'Lekce 21 · Journal forensic',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Najít relevantní incidentní okno a spojit log se změnou a symptomem.',
      'knowledge' => 
      array (
        0 => 'journal-logs',
        1 => 'journal-logs',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'journal-logs',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'journal-logs',
            1 => 'journal-logs',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    19 => 
    array (
      'id' => 'v30_3a_22',
      'number' => 22,
      'title' => 'Lekce 22 · SSH keys in practice',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Oddělit TCP dostupnost, výběr identity, oprávnění a serverovou autorizaci.',
      'knowledge' => 
      array (
        0 => 'ssh-key-operations',
        1 => 'ssh-keys-ops',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'ssh-key-operations',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'ssh-key-operations',
            1 => 'ssh-keys-ops',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    20 => 
    array (
      'id' => 'v30_3a_23',
      'number' => 23,
      'title' => 'Lekce 23 · DNS evidence chain',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Postavit DNS diagnostiku od resolveru přes záznam po aplikační test.',
      'knowledge' => 
      array (
        0 => 'dns',
        1 => 'dns-record-types',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'dns',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'dns',
            1 => 'dns-record-types',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    21 => 
    array (
      'id' => 'v30_3a_24',
      'number' => 24,
      'title' => 'Lekce 24 · Stateful firewall lab',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Navrhnout least-privilege pravidlo a potvrdit pozitivní i negativní test.',
      'knowledge' => 
      array (
        0 => 'stateful-firewall',
        1 => 'linux-firewall',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'stateful-firewall',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'stateful-firewall',
            1 => 'linux-firewall',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    22 => 
    array (
      'id' => 'v30_3a_25',
      'number' => 25,
      'title' => 'Lekce 25 · Safe Bash automation',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Napsat opakovatelný skript s kontrolou vstupů, chybami a logem.',
      'knowledge' => 
      array (
        0 => 'bash-error-handling',
        1 => 'shell-cron',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'bash-error-handling',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'bash-error-handling',
            1 => 'shell-cron',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    23 => 
    array (
      'id' => 'v30_3a_26',
      'number' => 26,
      'title' => 'Lekce 26 · Service recovery drill',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.',
      'knowledge' => 
      array (
        0 => 'service-debug-chain',
        1 => 'web-service-linux',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'service-debug-chain',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'service-debug-chain',
            1 => 'web-service-linux',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    24 => 
    array (
      'id' => 'v30_3a_27',
      'number' => 27,
      'title' => 'Lekce 27 · Team incident lab',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.',
      'knowledge' => 
      array (
        0 => 'stateful-firewall',
        1 => 'bash-error-handling',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'stateful-firewall',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'stateful-firewall',
            1 => 'bash-error-handling',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    25 => 
    array (
      'id' => 'v30_3a_28',
      'number' => 28,
      'title' => 'Lekce 28 · Mastery review',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Zopakovat nejslabší skills a dokončit praktické mastery evidence před závěrečným projektem.',
      'knowledge' => 
      array (
        0 => 'linux-file-troubleshooting',
        1 => 'ssh-key-operations',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'linux-file-troubleshooting',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'linux-file-troubleshooting',
            1 => 'ssh-key-operations',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
  ),
  'simulations' => 
  array (
    'dhcp' => 
    array (
      'id' => 'net-dhcp',
      'type' => 'dhcp',
      'title' => 'DHCP DORA: proč klient skončil na 169.254.x.x?',
      'lead' => 'Rozbij a oprav automatickou konfiguraci. Sleduj DORA handshake a výsledek ipconfig v reálném čase.',
      'task' => 'Oprav síť tak, aby klient dostal platnou adresu, bránu a DNS z DHCP.',
      'prediction' => 
      array (
        'q' => 'Klient má 169.254.41.18. Kterou vrstvu má smysl řešit nejdřív?',
        'options' => 
        array (
          0 => 'TLS certifikát',
          1 => 'DHCP / lokální síťovou konfiguraci',
          2 => 'CSS webu',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Dívej se na posloupnost DISCOVER → OFFER → REQUEST → ACK. Kde se komunikace zastavila?',
        1 => 'DHCP server musí být dostupný ve správné VLAN a musí mít volnou adresu.',
        2 => 'Nastav server ON, správnou VLAN a volný pool.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co 169.254.x.x nejčastěji znamená v této simulaci?',
        'options' => 
        array (
          0 => 'Klient nedostal očekávanou DHCP konfiguraci.',
          1 => 'DNS funguje.',
          2 => 'Je to veřejná IP.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'ports' => 
    array (
      'id' => 'net-reachability',
      'type' => 'reachability',
      'title' => 'Ping ≠ služba: odděl hosta od HTTPS',
      'lead' => 'Měň ICMP, webovou službu a firewall. Okamžitě uvidíš, že ping a HTTPS odpovídají na jiné otázky.',
      'task' => 'Nastav stav, kdy ping selže, ale HTTPS přesto funguje.',
      'prediction' => 
      array (
        'q' => 'Může web fungovat, i když ping neodpovídá?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Ping používá ICMP. HTTPS typicky TCP/443.',
        1 => 'Nech webovou službu běžet a TCP/443 povolený. Zablokuj jen ICMP.',
        2 => 'ICMP = blokovat, HTTPS služba = běží, TCP/443 = povolit.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co dokazuje úspěšný ping?',
        'options' => 
        array (
          0 => 'Že HTTPS určitě funguje.',
          1 => 'Že cílový host odpověděl na ICMP; nic víc o konkrétní službě.',
          2 => 'Že DNS je správně.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'troubleshooting' => 
    array (
      'id' => 'net-troubleshoot',
      'type' => 'troubleshoot3',
      'title' => 'Incident: „IP funguje, intranet ne“',
      'lead' => 'Máš jen symptom. Vol příkazy jako administrátor a z důkazů postupně zužuj příčinu. Incident je schválně řešitelný bez restartu.',
      'task' => 'Najdi root cause s co nejmenším počtem smysluplných testů a navrhni validaci opravy.',
      'prediction' => 
      array (
        'q' => 'Když web funguje přes IP, ale ne přes jméno, která hypotéza je nejsilnější?',
        'options' => 
        array (
          0 => 'DNS problém',
          1 => 'Vadný monitor',
          2 => 'GPU ovladač',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Nejdřív potvrď, že klient má použitelnou IP a cestu k serveru.',
        1 => 'Pak porovnej výsledek přes IP s výsledkem přes DNS jméno.',
        2 => 'Spusť ipconfig, ping brány a nslookup. Root cause je chybný DNS server klienta.',
      ),
      'conclusion' => 
      array (
        'q' => 'Který root cause odpovídá evidence?',
        'options' => 
        array (
          0 => 'Klient používá chybný DNS resolver.',
          1 => 'HTTPS port serveru je zavřený.',
          2 => 'DHCP pool je vyčerpaný.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'ip-addressing' => 
    array (
      'id' => 'net-subnet',
      'type' => 'subnetlab',
      'title' => 'Subnet Lab: jde paket přímo, nebo přes gateway?',
      'lead' => 'Měň prefix a sleduj, zda klient považuje cíl za lokální. Síťová maska není dekorace — rozhoduje o cestě paketu.',
      'task' => 'Nastav prefix tak, aby 192.168.10.42 a 192.168.10.200 byly ve stejné lokální síti.',
      'prediction' => 
      array (
        'q' => 'Budou .42/25 a .200/25 ve stejném subnetu?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => '/25 rozdělí poslední oktet na bloky 0–127 a 128–255.',
        1 => 'Zdroj .42 je v prvním /25, cíl .200 ve druhém.',
        2 => 'Použij /24.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co rozhoduje, zda host pošle paket přímo?',
        'options' => 
        array (
          0 => 'Jen podobnost IP adres',
          1 => 'IP adresa spolu s prefixem/maskou',
          2 => 'DNS jméno',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'dns' => 
    array (
      'id' => 'net-dns-cache',
      'type' => 'dnscache',
      'title' => 'DNS Cache Lab: proč někdo stále vidí starý server?',
      'lead' => 'Změň TTL a virtuální čas. Sleduj authoritative odpověď a resolver cache jako dva různé zdroje pravdy.',
      'task' => 'Dostaň klienta na novou IP tím, že necháš starou cache vypršet.',
      'prediction' => 
      array (
        'q' => 'Může resolver po změně A záznamu dočasně vracet starou IP?',
        'options' => 
        array (
          0 => 'Ano, pokud má stále platnou cache',
          1 => 'Ne, změna je okamžitá všude',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'TTL určuje, jak dlouho může být odpověď cachovaná.',
        1 => 'Porovnej elapsed time s TTL.',
        2 => 'Nastav čas alespoň na hodnotu TTL.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je správný závěr?',
        'options' => 
        array (
          0 => 'Každá stará odpověď znamená rozbitý authoritative DNS.',
          1 => 'Platná cache může dočasně vracet starou hodnotu.',
          2 => 'TTL je číslo TCP portu.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'https' => 
    array (
      'id' => 'net-https-stack',
      'type' => 'httpsstack',
      'title' => 'HTTPS Stack: která vrstva selhala?',
      'lead' => 'Zapínej a vypínej DNS, TCP, TLS a HTTP. Prohlížeč ukáže symptom, ale ty musíš určit vrstvu.',
      'task' => 'Vytvoř stav, kdy DNS i TCP fungují, ale HTTPS selže na TLS.',
      'prediction' => 
      array (
        'q' => 'Proběhne HTTP požadavek před TLS handshake?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne, u HTTPS nejdřív vznikne TLS spojení',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Nech DNS i TCP v pořádku.',
        1 => 'Rozbij pouze certifikát/TLS.',
        2 => 'DNS ON, TCP ON, TLS OFF.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jak troubleshootovat HTTPS nejlépe?',
        'options' => 
        array (
          0 => 'Po vrstvách: DNS → TCP → TLS → HTTP/aplikace',
          1 => 'Vždy restartovat webserver',
          2 => 'Jen pingem',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'routing' => 
    array (
      'id' => 'net-routing',
      'type' => 'routinglab',
      'title' => 'Gateway Lab: kudy paket opustí subnet?',
      'lead' => 'Měň default gateway a sleduj, zda je pro klienta vůbec lokálně dosažitelná.',
      'task' => 'Nastav platnou gateway pro klienta 192.168.10.42/24.',
      'prediction' => 
      array (
        'q' => 'Může být default gateway 192.168.20.1 pro klienta 192.168.10.42/24 bez dalšího triku?',
        'options' => 
        array (
          0 => 'Běžně ano',
          1 => 'Běžně ne — není v lokálním /24',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Klient musí gateway nejdřív doručit ethernetový rámec.',
        1 => 'Gateway má být na lokálním segmentu klienta.',
        2 => 'Vyber 192.168.10.1.',
      ),
      'conclusion' => 
      array (
        'q' => 'K čemu je default gateway?',
        'options' => 
        array (
          0 => 'K cestě do jiných sítí',
          1 => 'K převodu domén',
          2 => 'K šifrování TLS',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'ipv6-basics' => 
    array (
      'id' => 'net-ipv6',
      'type' => 'ipv6subnet',
      'title' => 'IPv6 Lab: /64 a zkracování',
      'lead' => 'Měň prefix a kompresi adresy. Vizuál zvýrazní síťovou a interface část.',
      'task' => 'Nastav /64 a správně rozpoznej dvě adresy ve stejné síti.',
      'prediction' => 
      array (
        'q' => 'Znamená /64 přesně 64 hostů?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Prefix je počet bitů síťové části.',
        1 => 'Porovnej první 64 bitů.',
        2 => 'Použij /64.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co sdílí hosté v jednom /64?',
        'options' => 
        array (
          0 => 'Stejný 64bitový síťový prefix.',
          1 => 'Stejný TCP port.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'dns-record-types' => 
    array (
      'id' => 'net-dns-records',
      'type' => 'dnsrecords',
      'title' => 'DNS Records Lab',
      'lead' => 'Vyber A, AAAA nebo CNAME pro různé úlohy a sleduj výslednou resolving chain.',
      'task' => 'Nastav www jako CNAME a cílovému hostu A + AAAA.',
      'prediction' => 
      array (
        'q' => 'Co obsahuje CNAME?',
        'options' => 
        array (
          0 => 'Jiné DNS jméno.',
          1 => 'IPv4 adresu.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Alias ukazuje na jméno.',
        1 => 'A je IPv4.',
        2 => 'AAAA je IPv6.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co může pokazit chybný AAAA?',
        'options' => 
        array (
          0 => 'IPv6 cestu dual-stack klienta.',
          1 => 'SSH permissions.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'monitoring-basics' => 
    array (
      'id' => 'net-monitor',
      'type' => 'monitoring',
      'title' => 'Monitoring Lab: co vlastně měříš?',
      'lead' => 'Přepínej ping, TCP a HTTP check a simuluj různé závady. Sleduj, který check incident skutečně odhalí.',
      'task' => 'Nastav monitoring webu tak, aby odhalil stav HTTP 503 i při živém hostu.',
      'prediction' => 
      array (
        'q' => 'Stačí ping pro web?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Host může žít a app selhat.',
        1 => 'Přidej HTTPS check.',
        2 => 'Očekávej HTTP 200.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký check je nejblíž uživateli?',
        'options' => 
        array (
          0 => 'Aplikační HTTP check.',
          1 => 'ARP tabulka.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'dhcp-reservations' => 
    array (
      'id' => 'net-dhcp-reserve',
      'type' => 'dhcpreserve',
      'title' => 'Reservation Lab',
      'lead' => 'Měň MAC, pool a reservation. Simulace ukáže, zda klient dostane stabilní adresu bez konfliktu.',
      'task' => 'Rezervuj tiskárně 192.168.10.50 bez kolize s dynamickým poolem.',
      'prediction' => 
      array (
        'q' => 'Je reservation totéž jako ruční static IP na klientovi?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Reservation je na DHCP serveru.',
        1 => 'Vyber správnou MAC.',
        2 => 'Vyber IP podle policy.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co získáš reservation?',
        'options' => 
        array (
          0 => 'Stabilní lease a centrální options.',
          1 => 'Automatický firewall.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'packet-analysis' => 
    array (
      'id' => 'net-packets',
      'type' => 'packetflow',
      'title' => 'Packet Journey Lab',
      'lead' => 'Simuluj závadu v ARP, DNS nebo TCP a vyber filtr, který přinese nejrychlejší důkaz.',
      'task' => 'Najdi DNS problém pomocí odpovídajícího filtru a evidence.',
      'prediction' => 
      array (
        'q' => 'Je nejlepší začít capture bez filtru?',
        'options' => 
        array (
          0 => 'Ne, nejdřív potřebuji otázku/hypotézu.',
          1 => 'Ano, čím více paketů tím lépe.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Nastav závadu DNS.',
        1 => 'Použij DNS filtr.',
        2 => 'Hledej query bez odpovědi.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je nejsilnější evidence DNS timeoutu?',
        'options' => 
        array (
          0 => 'Query odešla, ale v očekávaném čase není response.',
          1 => 'SYN-ACK na portu 443.',
          2 => 'ARP reply od gateway.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'subnetting-vlsm' => 
    array (
      'id' => 'net-vlsm',
      'type' => 'subnetlab',
      'title' => 'VLSM Planner Lab',
      'lead' => 'Rozděluj /24 mezi různě velké segmenty a sleduj hranice bloků, překryv a nevyužitou rezervu.',
      'task' => 'Navrhni subnety pro 90, 40, 20 a 10 hostů bez překryvu.',
      'prediction' => 
      array (
        'q' => 'Je bezpečné začít nejmenším segmentem?',
        'options' => 
        array (
          0 => 'Ne, nejdřív největší.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => '90 hostů potřebuje /25.',
        1 => 'Pokračuj další hranicí.',
        2 => 'Ověř broadcast každého bloku.',
      ),
      'conclusion' => 
      array (
        'q' => 'Proč řadit od největšího?',
        'options' => 
        array (
          0 => 'Snižuje riziko fragmentace a překryvu.',
          1 => 'Kvůli DNS.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'network-security-basics' => 
    array (
      'id' => 'net-zones',
      'type' => 'routinglab',
      'title' => 'Security Zones Lab',
      'lead' => 'Měň pravidla mezi USERS, SERVERS a MGMT a sleduj, které služby zůstanou dostupné nebo zablokované.',
      'task' => 'Povol HTTPS uživatelům, SSH jen managementu a ostatní serverový provoz blokuj.',
      'prediction' => 
      array (
        'q' => 'Je ANY→ANY allow dobrý default?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Definuj source i destination.',
        1 => 'Povol jen konkrétní port.',
        2 => 'Ověř negativní scénář.',
      ),
      'conclusion' => 
      array (
        'q' => 'Least privilege znamená?',
        'options' => 
        array (
          0 => 'Jen minimální nutný přístup.',
          1 => 'Povolit vše uvnitř sítě.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'dns-dhcp-operations' => 
    array (
      'id' => 'net-dns-dhcp-ii',
      'type' => 'dnscache',
      'title' => 'TTL & Lease Timeline Lab',
      'lead' => 'Měň TTL a sleduj, kdy klient uvidí nový DNS stav; porovnej to s obnovou DHCP lease.',
      'task' => 'Naplánuj změnu tak, aby stará cache nepřekvapila uživatele.',
      'prediction' => 
      array (
        'q' => 'Uvidí všichni nový A record okamžitě?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Sniž TTL před změnou.',
        1 => 'Sleduj cache expiry.',
        2 => 'U DHCP použij renew, když je třeba.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co řídí životnost DNS cache?',
        'options' => 
        array (
          0 => 'TTL.',
          1 => 'TCP window.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'service-debug-chain' => 
    array (
      'id' => 'net-debug-chain',
      'type' => 'packetflow',
      'title' => 'Service Chain Lab',
      'lead' => 'Simuluj závadu v DNS, TCP, TLS nebo HTTP a vyber nejkratší testovací cestu k prvnímu chybějícímu důkazu.',
      'task' => 'Najdi vrstvu závady bez zbytečných restartů.',
      'prediction' => 
      array (
        'q' => 'Má smysl začít restartem všeho?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni DNS.',
        1 => 'Pak port.',
        2 => 'Potom TLS a HTTP.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je cílem diagnostického chainu?',
        'options' => 
        array (
          0 => 'Najít první vrstvu, která selhává.',
          1 => 'Spustit co nejvíc příkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'linux-filesystem' => 
    array (
      'id' => 'yp-linux-filesystem',
      'type' => 'permissionslab',
      'title' => 'Linux filesystem a CLI · Lab',
      'lead' => 'Filesystem je hierarchie začínající v / a administrátor potřebuje rozumět cestám dřív, než začne měnit soubory.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'users-permissions' => 
    array (
      'id' => 'yp-users-permissions',
      'type' => 'permissionslab',
      'title' => 'Uživatelé, skupiny a oprávnění · Lab',
      'lead' => 'Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'processes-systemd' => 
    array (
      'id' => 'yp-processes-systemd',
      'type' => 'tcp',
      'title' => 'Procesy a systemd · Lab',
      'lead' => 'Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'journal-logs' => 
    array (
      'id' => 'yp-journal-logs',
      'type' => 'monitoring',
      'title' => 'journalctl a provozní logy · Lab',
      'lead' => 'Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'ssh-keys-ops' => 
    array (
      'id' => 'yp-ssh-keys-ops',
      'type' => 'sshkeylab',
      'title' => 'SSH klíče a vzdálená správa · Lab',
      'lead' => 'SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'linux-firewall' => 
    array (
      'id' => 'yp-linux-firewall',
      'type' => 'reachability',
      'title' => 'Linux firewall a service exposure · Lab',
      'lead' => 'Dostupnost služby vzniká kombinací listeneru, bind adresy, routingu a firewall policy.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'web-service-linux' => 
    array (
      'id' => 'yp-web-service-linux',
      'type' => 'binding',
      'title' => 'Linux webová služba: end-to-end cesta · Lab',
      'lead' => 'End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'shell-cron' => 
    array (
      'id' => 'yp-shell-cron',
      'type' => 'production',
      'title' => 'Shell automatizace a plánování · Lab',
      'lead' => 'Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
  ),
  'learningResources' => 
  array (
    '_default' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
      1 => 
      array (
        'type' => 'course',
        'title' => 'Cisco Skills for All · Networking',
        'meta' => 'Volitelná doplňková praxe',
        'url' => 'https://skillsforall.com/',
      ),
    ),
    'ip-addressing' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
    ),
    'dns' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
      1 => 
      array (
        'type' => 'reference',
        'title' => 'Cloudflare · What is DNS?',
        'meta' => 'Vizuální doplnění DNS lookupu',
        'url' => 'https://www.cloudflare.com/learning/dns/what-is-dns/',
      ),
    ),
    'dhcp' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'ports' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
    ),
    'ssh-sftp' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'SSH nejen pro vzdálenou správu Linuxu',
        'url' => 'https://www.youtube.com/watch?v=mWB2ralMAG0',
        'youtube_id' => 'mWB2ralMAG0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká přednáška o SSH, vzdáleném terminálu, přenosu souborů, tunelování a praktickém použití.',
      ),
    ),
    'https' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Zámečky nikoho nezajímaj’ – HTTPS a certifikáty',
        'url' => 'https://www.youtube.com/watch?v=8_qX6ZThwZI',
        'youtube_id' => '8_qX6ZThwZI',
        'lang' => 'CZ',
        'duration' => '20 min',
        'level' => 'Středně pokročilý',
        'description' => 'Krátká česká přednáška o HTTPS, certifikátech a tom, co skutečně znamená zabezpečené spojení v prohlížeči.',
      ),
    ),
    'icmp' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'routing' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'troubleshooting' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'vlan-basics' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Pokročilejší síťování v Linuxu',
        'url' => 'https://www.youtube.com/watch?v=KI4ojd68IMA',
        'youtube_id' => 'KI4ojd68IMA',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Český výklad pokročilejšího linuxového síťování vhodný pro routing, rozhraní a provozní diagnostiku.',
      ),
    ),
    'arp' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Wireshark a modely TCP/IP a ISO/OSI',
        'url' => 'https://www.youtube.com/watch?v=VRhbOaw8sXA',
        'youtube_id' => 'VRhbOaw8sXA',
        'lang' => 'CZ',
        'duration' => 'cca 39 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická ukázka Wiresharku, packet capture a návaznosti síťových vrstev TCP/IP a ISO/OSI.',
      ),
    ),
    'nat' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
    ),
    'service-matrix' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Moderní linuxový firewall s nftables',
        'url' => 'https://www.youtube.com/watch?v=7h5PMWbmXIo',
        'youtube_id' => '7h5PMWbmXIo',
        'lang' => 'CZ',
        'duration' => '49 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o stavovém firewallu, filtrování provozu a moderním nftables — vhodná pro pravidla a service matrix.',
      ),
    ),
    'ipv6-basics' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Seminář IPv6: deset let poté',
        'url' => 'https://www.youtube.com/watch?v=JJvqBH02GiA',
        'youtube_id' => 'JJvqBH02GiA',
        'lang' => 'CZ',
        'duration' => 'delší seminář',
        'level' => 'Středně pokročilý',
        'description' => 'Český odborný seminář k IPv6; pro tuto lekci stačí vybrané části věnované adresaci a provozu IPv6.',
      ),
    ),
    'dns-record-types' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
      1 => 
      array (
        'type' => 'reference',
        'title' => 'Cloudflare · What is DNS?',
        'meta' => 'Typy DNS záznamů a lookup',
        'url' => 'https://www.cloudflare.com/learning/dns/what-is-dns/',
      ),
    ),
    'monitoring-basics' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Automatizace monitoringu serverů snadno a rychle',
        'url' => 'https://www.youtube.com/watch?v=sZajyA4vUOA',
        'youtube_id' => 'sZajyA4vUOA',
        'lang' => 'CZ',
        'duration' => '20 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická přednáška o tom, co a jak sledovat, aby monitoring opravdu zachytil problém služby.',
      ),
    ),
    'dhcp-reservations' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'packet-analysis' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Wireshark a modely TCP/IP a ISO/OSI',
        'url' => 'https://www.youtube.com/watch?v=VRhbOaw8sXA',
        'youtube_id' => 'VRhbOaw8sXA',
        'lang' => 'CZ',
        'duration' => 'cca 39 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická ukázka Wiresharku, packet capture a návaznosti síťových vrstev TCP/IP a ISO/OSI.',
      ),
    ),
    'subnetting-vlsm' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak fungují IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=J1nudfAQCDE',
        'youtube_id' => 'J1nudfAQCDE',
        'lang' => 'CZ',
        'duration' => '35 min',
        'level' => 'Začátečník',
        'description' => 'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.',
      ),
    ),
    'network-security-basics' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Moderní linuxový firewall s nftables',
        'url' => 'https://www.youtube.com/watch?v=7h5PMWbmXIo',
        'youtube_id' => '7h5PMWbmXIo',
        'lang' => 'CZ',
        'duration' => '49 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o stavovém firewallu, filtrování provozu a moderním nftables — vhodná pro pravidla a service matrix.',
      ),
    ),
    'dns-dhcp-operations' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'service-debug-chain' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'linux-filesystem' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'SSH nejen pro vzdálenou správu Linuxu',
        'url' => 'https://www.youtube.com/watch?v=mWB2ralMAG0',
        'youtube_id' => 'mWB2ralMAG0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká přednáška o SSH, vzdáleném terminálu, přenosu souborů, tunelování a praktickém použití.',
      ),
    ),
    'users-permissions' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'SSH nejen pro vzdálenou správu Linuxu',
        'url' => 'https://www.youtube.com/watch?v=mWB2ralMAG0',
        'youtube_id' => 'mWB2ralMAG0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká přednáška o SSH, vzdáleném terminálu, přenosu souborů, tunelování a praktickém použití.',
      ),
    ),
    'processes-systemd' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Automatizace monitoringu serverů snadno a rychle',
        'url' => 'https://www.youtube.com/watch?v=sZajyA4vUOA',
        'youtube_id' => 'sZajyA4vUOA',
        'lang' => 'CZ',
        'duration' => '20 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická přednáška o tom, co a jak sledovat, aby monitoring opravdu zachytil problém služby.',
      ),
    ),
    'journal-logs' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Centralized logs ElasticSearch way',
        'url' => 'https://www.youtube.com/watch?v=m6zpzczf2p8',
        'youtube_id' => 'm6zpzczf2p8',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o centralizaci, parsování a vyhledávání logů z aplikací a infrastruktury.',
      ),
    ),
    'ssh-keys-ops' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'SSH nejen pro vzdálenou správu Linuxu',
        'url' => 'https://www.youtube.com/watch?v=mWB2ralMAG0',
        'youtube_id' => 'mWB2ralMAG0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká přednáška o SSH, vzdáleném terminálu, přenosu souborů, tunelování a praktickém použití.',
      ),
    ),
    'linux-firewall' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Moderní linuxový firewall s nftables',
        'url' => 'https://www.youtube.com/watch?v=7h5PMWbmXIo',
        'youtube_id' => '7h5PMWbmXIo',
        'lang' => 'CZ',
        'duration' => '49 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o stavovém firewallu, filtrování provozu a moderním nftables — vhodná pro pravidla a service matrix.',
      ),
    ),
    'web-service-linux' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nginx v roli web serveru',
        'url' => 'https://www.youtube.com/watch?v=MRpKBh7J0eo',
        'youtube_id' => 'MRpKBh7J0eo',
        'lang' => 'CZ',
        'duration' => 'cca 50 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká přednáška o Nginxu, webserveru a reverse proxy — vhodná pro binding, upstream a provozní kontext.',
      ),
    ),
    'shell-cron' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Automatizace monitoringu serverů snadno a rychle',
        'url' => 'https://www.youtube.com/watch?v=sZajyA4vUOA',
        'youtube_id' => 'sZajyA4vUOA',
        'lang' => 'CZ',
        'duration' => '20 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická přednáška o tom, co a jak sledovat, aby monitoring opravdu zachytil problém služby.',
      ),
    ),
    'linux-file-troubleshooting' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nefunguje internet: jak najít příčinu výpadku připojení',
        'url' => 'https://www.youtube.com/watch?v=gGwsOpNOmr0',
        'youtube_id' => 'gGwsOpNOmr0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.',
      ),
    ),
    'ssh-key-operations' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'SSH nejen pro vzdálenou správu Linuxu',
        'url' => 'https://www.youtube.com/watch?v=mWB2ralMAG0',
        'youtube_id' => 'mWB2ralMAG0',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká přednáška o SSH, vzdáleném terminálu, přenosu souborů, tunelování a praktickém použití.',
      ),
    ),
    'stateful-firewall' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Moderní linuxový firewall s nftables',
        'url' => 'https://www.youtube.com/watch?v=7h5PMWbmXIo',
        'youtube_id' => '7h5PMWbmXIo',
        'lang' => 'CZ',
        'duration' => '49 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o stavovém firewallu, filtrování provozu a moderním nftables — vhodná pro pravidla a service matrix.',
      ),
    ),
    'bash-error-handling' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Pokročilejší síťování v Linuxu',
        'url' => 'https://www.youtube.com/watch?v=KI4ojd68IMA',
        'youtube_id' => 'KI4ojd68IMA',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Český výklad pokročilejšího linuxového síťování vhodný pro routing, rozhraní a provozní diagnostiku.',
      ),
    ),
  ),
  '_sources_hash' => '6ddbbf7917bc6e2c894db06395cdcab1e04ec8b24784653cded74124379eb6e5',
);
