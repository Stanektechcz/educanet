<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 1,
  'class' => 'class_3a',
  'lessons' => 
  array (
    1 => 
    array (
      'number' => 1,
      'lm71_source' => 'primary',
    ),
    2 => 
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
      'number' => 2,
      'lm71_source' => 'next',
    ),
    3 => 
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
      'lm71_source' => 'extended',
    ),
    4 => 
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
      'lm71_source' => 'extended',
    ),
    5 => 
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
      'lm71_source' => 'plus',
    ),
    6 => 
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
      'lm71_source' => 'plus',
    ),
    7 => 
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
      'lm71_source' => 'more',
    ),
    8 => 
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
      'lm71_source' => 'ecosystem',
    ),
    9 => 
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
      'lm71_source' => 'ecosystem',
    ),
    10 => 
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
      'lm71_source' => 'yearpack',
    ),
    11 => 
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
      'lm71_source' => 'yearpack',
    ),
    12 => 
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
      'lm71_source' => 'yearpack',
    ),
    13 => 
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
      'lm71_source' => 'yearpack',
    ),
    14 => 
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
      'lm71_source' => 'yearpack',
    ),
    15 => 
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
      'lm71_source' => 'yearpack',
    ),
    16 => 
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
      'lm71_source' => 'yearpack',
    ),
    17 => 
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
      'lm71_source' => 'yearpack',
    ),
    18 => 
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
      'lm71_source' => 'yearpack',
    ),
    19 => 
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
      'lm71_source' => 'v30',
    ),
    20 => 
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
      'lm71_source' => 'v30',
    ),
    21 => 
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
      'lm71_source' => 'v30',
    ),
    22 => 
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
      'lm71_source' => 'v30',
    ),
    23 => 
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
      'lm71_source' => 'v30',
    ),
    24 => 
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
      'lm71_source' => 'v30',
    ),
    25 => 
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
      'lm71_source' => 'v30',
    ),
    26 => 
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
      'lm71_source' => 'v30',
    ),
    27 => 
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
      'lm71_source' => 'v30',
    ),
    28 => 
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
      'lm71_source' => 'v30',
    ),
  ),
  'conflicts' => 
  array (
  ),
  'template_tasks' => 
  array (
    'fcfaee0c8cf0' => 10,
    '9840f242be1c' => 10,
    '29bcf19a752f' => 10,
    'ecdd13e619ae' => 10,
    '3da4ec25375b' => 10,
    'de9dbe43c2ef' => 10,
    'faa9348a9583' => 10,
    '2c621dfcc048' => 10,
    '6342205f34e7' => 10,
    'e16e397d010e' => 10,
  ),
  'overlay' => 
  array (
    'lessons' => 
    array (
    ),
    'days' => 
    array (
    ),
    'files' => 
    array (
    ),
  ),
  'sig' => 'ac09530107d415acebf614c00c70b5113c87b14e',
  'hash' => '283715346b3baf7ca6021ab840773f1a0cb7c4b7b862c1e248e99979b28273e3',
  'built_at' => '2026-10-07T22:52:57+02:00',
);
