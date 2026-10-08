<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 2,
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
      5 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 5 · IPv6 a záznamy DNS',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím přečíst a zkrátit adresu IPv6, poznat lokální adresu linky a ověřit záznamy DNS typu A, AAAA a CNAME příkazem dig.',
          'success_criteria' => 
          array (
            0 => 'Zkrátím adresu IPv6 podle pravidel a určím prefix /64.',
            1 => 'V simulátoru najdu adresu fe80:: a vysvětlím, proč je jen pro danou linku.',
            2 => 'Z výstupu dig poznám, zda jméno má záznam A, AAAA, nebo neexistuje (NXDOMAIN).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_dns_dhcp',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'net_addressing',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Napíše na tabuli dlouhou adresu IPv6 a zeptá se, jak ji zkrátit.',
            'student' => 'Zkusí zkrácení a porovnají výsledky.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'IPv6 bez paniky',
            'teacher' => 'Vysvětlí pravidla zkracování a prefix /64.',
            'student' => 'Zkrátí 4 adresy a určí síťovou část.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 38,
            'phase' => 'Adresa linky v simulátoru',
            'teacher' => 'Ukáže ip -6 addr v Linux Labu.',
            'student' => 'Najdou adresu fe80:: a její rozsah (scope link).',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 55,
            'phase' => 'Záznamy A / AAAA / CNAME',
            'teacher' => 'Vysvětlí typy záznamů a alias.',
            'student' => 'Přiřadí záznamy k účelu a navrhnou alias www.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Ověření příkazem dig',
            'teacher' => 'Předvede čtení odpovědi dig (status, ANSWER).',
            'student' => 'Ověří tři jména v simulátoru a zapíší výsledky.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Incident dual-stack a exit ticket',
            'teacher' => 'Popíše incident: AAAA míří na nedostupnou adresu.',
            'student' => 'Diskutují dopad a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zkrať adresy IPv6 2001:0db8:0000:0000:0000:0000:0000:0001 a 2001:0db8:0a00:0000:0000:00ff:0000:0010 a u obou urči prefix /64.',
            'output' => 'Zkrácené tvary 2001:db8::1 a 2001:db8:a00::ff:0:10 a jejich prefix 2001:db8::/64 a 2001:db8:a00::/64.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'V Linux Labu spusť ip -6 addr a najdi adresu, která platí jen pro danou linku (link-local).',
            'output' => 'Adresa fe80::5054:ff:fe3a:7c17/64 se „scope link“.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ip -6 addr',
                'expect' => 'inet6 fe80::5054:ff:fe3a:7c17/64 scope link',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř příkazem dig jméno intranet.skola.test: jaký má záznam A a má i záznam AAAA?',
            'output' => 'A = 10.0.0.10 (TTL 3600), AAAA chybí (ANSWER: 0).',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig +short intranet.skola.test',
                'expect' => '10.0.0.10',
              ),
              1 => 
              array (
                'cmd' => 'dig intranet.skola.test AAAA',
                'expect' => 'ANSWER: 0',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Ověř neexistující jméno www.skola.lab a navrhni alias (CNAME) www.skola.test → intranet.skola.test.',
            'output' => 'status: NXDOMAIN + zápis aliasu bez duplikace IP.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig www.skola.lab',
                'expect' => 'status: NXDOMAIN',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartičku pravidel zkracování (vynechat úvodní nuly, jedna skupina nul = ::) a vzorový výstup dig s vyznačenými řádky.',
          'standard' => 'Zkracování, ověření v simulátoru a návrh aliasu podle zadání.',
          'challenge' => 'Vysvětlí, proč se :: smí v adrese použít jen jednou, a zapíše adresu s dvěma skupinami nul správně.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Tabule: tři žáci zkrátí adresu, třída hlasuje, zda je správně.',
            1 => 'Kontrola výstupu: každý ukáže v dig řádek status a ANSWER.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Zápis IPv6',
              'levels' => 
              array (
                0 => 'Nezkrátí.',
                1 => 'Zkrátí s chybou (dvakrát ::).',
                2 => 'Správně zkrátí a určí /64.',
                3 => 'Vysvětlí pravidla spolužákovi.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Záznamy DNS',
              'levels' => 
              array (
                0 => 'Nerozliší typy.',
                1 => 'Zná A, plete AAAA/CNAME.',
                2 => 'Přiřadí A, AAAA, CNAME k účelu.',
                3 => 'Navrhne alias bez duplikace IP.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Ověření v simulátoru',
              'levels' => 
              array (
                0 => 'Nespustí.',
                1 => 'Spustí, nevyčte výsledek.',
                2 => 'Vyčte A, AAAA i NXDOMAIN.',
                3 => 'Odhadne dopad chybějícího AAAA.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_dns_dhcp',
          'competence_label' => 'DNS a DHCP',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jaký je správný zkrácený zápis adresy 2001:0db8:0000:0000:0000:0000:0000:0001?',
              'options' => 
              array (
                0 => '2001:db8::1',
                1 => '2001:db8:0:1',
                2 => '2001::db8::1',
              ),
              'correct' => 0,
              'explanation' => 'Úvodní nuly se vynechají a jedna souvislá řada nul je ::.',
            ),
            1 => 
            array (
              'question' => 'K čemu slouží záznam AAAA?',
              'options' => 
              array (
                0 => 'Přesměruje jméno na jiné jméno.',
                1 => 'Přiřadí jménu adresu IPv6.',
                2 => 'Určí poštovní server domény.',
              ),
              'correct' => 1,
              'explanation' => 'A = IPv4, AAAA = IPv6, CNAME = alias.',
            ),
            2 => 
            array (
              'question' => 'dig vrátí „status: NXDOMAIN“. Co to znamená?',
              'options' => 
              array (
                0 => 'Server DNS neběží.',
                1 => 'Jméno existuje, ale nemá adresu IPv6.',
                2 => 'Dotazované jméno v DNS neexistuje.',
              ),
              'correct' => 2,
              'explanation' => 'NXDOMAIN = neexistující doména (jméno).',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na domácím počítači nebo telefonu najdi v nastavení sítě adresu začínající fe80:: a opiš si ji.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Sítě a DNS zkoušíme jen v simulátoru Linux Labu; skutečné servery školy neměníme.',
          1 => 'Příklady adres jsou dokumentační (2001:db8::/32, RFC 3849) nebo privátní.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: dvě zkrácení :: v jedné adrese – adresa pak není jednoznačná.',
          1 => 'Otázka do třídy: Proč adresa fe80:: nepomůže na internetu?',
          2 => 'Tempo: dig ukaž nejdřív s +short, plný výstup až potom – jinak se žáci ztratí.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci pracují v Linux Labu podle úkolů 2–4 (výstupy jsou v zadání), úkol 1 na papír.',
          1 => 'Plán B offline: zkracování adres a čtení vytištěného výstupu dig.',
        ),
        'glossary' => 
        array (
          0 => 'link-local',
          1 => 'dual-stack',
          2 => 'nxdomain',
        ),
        '_file' => 'lesson_content_v72_3a_a.php',
      ),
      6 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 6 · Monitoring služeb a rezervace DHCP',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím rozlišit „počítač odpovídá“ od „služba funguje“, navrhnout kontrolu dostupnosti webu a stabilní adresu pro známé zařízení přes rezervaci DHCP.',
          'success_criteria' => 
          array (
            0 => 'Vysvětlím rozdíl kontroly ping, TCP a HTTP a vyberu správnou pro web.',
            1 => 'Navrhnu varování a kritický stav tak, aby jeden náhodný výpadek nevyvolal poplach.',
            2 => 'Navrhnu rezervaci DHCP (MAC → IP) a vysvětlím rozdíl proti ručně nastavené adrese.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_dns_dhcp',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'net_security',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše situaci: ping na server jde, ale web nefunguje.',
            'student' => 'Hádají, kde může být problém.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Počítač ≠ služba',
            'teacher' => 'Porovná kontrolu ping, TCP a HTTP.',
            'student' => 'V simulátoru zastaví web a ověří, co která kontrola uvidí.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Signál bez šumu',
            'teacher' => 'Vysvětlí interval, timeout a práh.',
            'student' => 'Navrhnou 3 metriky a hranice varování a kritického stavu.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Rezervace DHCP',
            'teacher' => 'Vysvětlí zápůjčku adresy (lease) a rezervaci podle MAC.',
            'student' => 'V simulátoru najdou MAC, adresu a dobu zápůjčky.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Tabulka zápůjček',
            'teacher' => 'Rozdá vymyšlenou tabulku zápůjček s konfliktem.',
            'student' => 'Najdou zařízení podle MAC, odhalí konflikt a navrhnou rezervaci tiskárny.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Shrnutí a exit ticket',
            'teacher' => 'Shrne: měřit službu, ne jen počítač.',
            'student' => 'Navrhnou kontrolu pro DNS a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'V Linux Labu zastav web (sudo systemctl stop nginx) a ověř curl -I http://localhost; porovnej s tím, co by ukázal ping.',
            'output' => 'curl hlásí „Couldn\'t connect to server“, počítač přitom běží – ping by byl v pořádku.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; curl -I http://localhost',
                'expect' => 'Couldn\'t connect to server',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Navrhni tři metriky monitoringu webu (dostupnost HTTP, doba odpovědi, chybovost) s hranicí varování a kritického stavu a pravidlem „3 neúspěchy po sobě“.',
            'output' => 'Tabulka metrik a hranic.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Pomocí ip a zjisti MAC adresu, IPv4 adresu a zbývající dobu zápůjčky (valid_lft) rozhraní eth0.',
            'output' => 'MAC 52:54:00:3a:7c:17, adresa 10.0.0.23/24 „dynamic“, valid_lft v sekundách.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ip a',
                'expect' => 'inet 10.0.0.23/24 brd 10.0.0.255 scope global dynamic eth0',
              ),
              1 => 
              array (
                'cmd' => 'ip a',
                'expect' => 'link/ether 52:54:00:3a:7c:17',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'V tabulce zápůjček najdi konflikt adres a navrhni rezervaci pro tiskárnu mimo dynamický rozsah.',
            'output' => 'Zápis rezervace MAC → IP + zdůvodnění.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku „kontrola → co ověří → co neověří“ s polovinou vyplněnou.',
          'standard' => 'Monitoring, výstupy simulátoru a rezervace podle zadání.',
          'challenge' => 'Navrhne kontrolu DNS, která pozná, že jméno vrací špatnou adresu (ne jen žádnou).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlé hlasování: ping OK + HTTP 503 – kde je problém?',
            1 => 'Dvojice si vymění hranice metrik a hledají „poplach z jednoho výpadku“.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Volba kontroly',
              'levels' => 
              array (
                0 => 'Jen ping.',
                1 => 'TCP i HTTP bez rozdílu.',
                2 => 'Správná kontrola pro web se zdůvodněním.',
                3 => 'Navrhne i kontrolu DNS.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Hranice a šum',
              'levels' => 
              array (
                0 => 'Bez hranic.',
                1 => 'Hranice bez ochrany proti šumu.',
                2 => 'Varování, kritický stav a opakování.',
                3 => 'Zdůvodní interval a timeout.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rezervace DHCP',
              'levels' => 
              array (
                0 => 'Nerozumí.',
                1 => 'Ruční adresa uvnitř rozsahu.',
                2 => 'Rezervace MAC → IP mimo dynamický rozsah.',
                3 => 'Vysvětlí výhodu proti ruční adrese.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_dns_dhcp',
          'competence_label' => 'DNS a DHCP',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Ping na webový server odpovídá, ale kontrola HTTP hlásí chybu. Co je nejlepší první závěr?',
              'options' => 
              array (
                0 => 'Server je vypnutý.',
                1 => 'Síť k serveru funguje, problém je ve webové službě.',
                2 => 'Selhal DNS.',
              ),
              'correct' => 1,
              'explanation' => 'Ping ověřuje počítač, ne službu.',
            ),
            1 => 
            array (
              'question' => 'Jaký je hlavní rozdíl rezervace DHCP proti ručně nastavené adrese na zařízení?',
              'options' => 
              array (
                0 => 'Adresu přiděluje centrálně server DHCP podle MAC, správa je na jednom místě.',
                1 => 'Rezervace funguje jen pro IPv6.',
                2 => 'Ruční adresa se mění při každém restartu.',
              ),
              'correct' => 0,
              'explanation' => 'Rezervace = stabilní adresa, centrální správa.',
            ),
            2 => 
            array (
              'question' => 'Proč monitoring hlásí poplach až po třech neúspěšných kontrolách za sebou?',
              'options' => 
              array (
                0 => 'Aby se šetřila elektřina.',
                1 => 'Protože první dvě kontroly se nepočítají.',
                2 => 'Aby jeden náhodný výpadek nespustil falešný poplach.',
              ),
              'correct' => 2,
              'explanation' => 'Opakování odfiltruje šum.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v nastavení domácího routeru (jen prohlížení, nic neměň) najdi seznam připojených zařízení a jejich MAC adres.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Služby zastavujeme a měníme jen v simulátoru, ne na školní síti.',
          1 => 'Na domácím routeru nic neměň – jen se dívej, a jen se souhlasem rodičů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „ping jde = všechno jde“. Ukaž to na zastaveném nginx.',
          1 => 'Otázka do třídy: Co přesně ověřuje ping a co ne?',
          2 => 'Tempo: tabulka zápůjček je papírová – připrav ji vytištěnou, ušetří to 5 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1 a 3 v Linux Labu podle zadání (výstupy jsou uvedené), úkoly 2 a 4 na papír.',
          1 => 'Plán B offline: vytištěné výstupy ip a a tabulka zápůjček.',
        ),
        'glossary' => 
        array (
          0 => 'monitoring',
          1 => 'lease',
          2 => 'mac',
        ),
        '_file' => 'lesson_content_v72_3a_a.php',
      ),
      7 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 7 · Cesta paketu a čtení zachyceného provozu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím z důkazů o provozu rozlišit závadu v síti, v DNS, na úrovni TCP a v aplikaci a navrhnout nejkratší další test.',
          'success_criteria' => 
          array (
            0 => 'Rozliším „spojení odmítnuto“ (RST) od „vypršel čas“ (žádná odpověď).',
            1 => 'Napíšu zobrazovací filtr pro DNS a pro TCP port 443.',
            2 => 'Ke zjištěné závadě navrhnu test, který ji po opravě ověří.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_diagnose',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže záznam provozu se stovkami paketů.',
            'student' => 'Řeknou, proč hledat bez otázky nejde.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Cesta paketu',
            'teacher' => 'Předvede Packet Journey Lab (ARP → DNS → TCP → HTTP).',
            'student' => 'Zapíší hypotézu k ukázkové závadě.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Filtr podle otázky',
            'teacher' => 'Ukáže filtry dns a tcp.port == 443 (Wireshark).',
            'student' => 'Navrhnou filtry pro tři otázky.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 58,
            'phase' => 'Odmítnuto vs. vypršel čas',
            'teacher' => 'Vysvětlí SYN, SYN-ACK a RST.',
            'student' => 'V simulátoru vyzkouší nc na tři cíle a zapíší rozdíl.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 78,
            'phase' => 'Incident podle důkazů',
            'teacher' => 'Zadá incident „web nejde“ s připravenými důkazy.',
            'student' => 'Vyberou 3 nejkratší testy a určí příčinu.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Ověření a exit ticket',
            'teacher' => 'Zeptá se: jak poznáme, že oprava zabrala?',
            'student' => 'Navrhnou ověřovací test a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'K ukázkové závadě zapiš hypotézu ve tvaru „myslím, že selhává … protože …“ a důkaz, který ji ověří.',
            'output' => 'Hypotéza + potřebný důkaz.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Napiš zobrazovací filtr (display filter) Wireshark pro DNS (dns) a pro HTTPS (tcp.port == 443) a vysvětli, proč nechceš celý záznam.',
            'output' => 'Dva filtry + jedna věta zdůvodnění.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'V Linux Labu vyzkoušej nc -zv na 10.0.0.10 port 80, 10.0.0.10 port 443 a 10.0.0.99 port 443 a zapiš, co znamená každý výsledek.',
            'output' => 'succeeded = port otevřen; Connection refused = cíl odpověděl RST; Connection timed out = žádná odpověď.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 80',
                'expect' => 'succeeded',
              ),
              1 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 443',
                'expect' => 'Connection refused',
              ),
              2 => 
              array (
                'cmd' => 'nc -zv 10.0.0.99 443',
                'expect' => 'Connection timed out',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'V incidentu „web nejde“ vyber tři nejkratší testy, urči vrstvu závady a navrhni ověření po opravě.',
            'output' => 'Tři testy, vrstva závady, ověřovací test.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku „výsledek → význam“ (succeeded / refused / timed out) a vzorový filtr.',
          'standard' => 'Hypotéza, filtry, testy v simulátoru a incident podle zadání.',
          'challenge' => 'Popíše, jak by v záznamu provozu vypadal DNS dotaz bez odpovědi a jak NXDOMAIN.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Karty: učitel přečte výsledek nc, třída zvedne kartu „síť / port / služba“.',
            1 => 'Hypotéza nahlas: dá se ověřit jedním testem?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Hypotéza a důkaz',
              'levels' => 
              array (
                0 => 'Bez hypotézy.',
                1 => 'Hypotéza bez důkazu.',
                2 => 'Hypotéza + konkrétní důkaz.',
                3 => 'Vyloučí i alternativní hypotézu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Čtení TCP',
              'levels' => 
              array (
                0 => 'Nerozliší výsledky.',
                1 => 'Rozliší jen úspěch.',
                2 => 'Rozliší RST a vypršení času.',
                3 => 'Vysvětlí, kdo poslal RST a proč.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Postup v incidentu',
              'levels' => 
              array (
                0 => 'Zkouší všechno.',
                1 => 'Testy bez pořadí.',
                2 => 'Tři nejkratší testy a vrstva závady.',
                3 => 'Navrhne ověření po opravě.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_diagnose',
          'competence_label' => 'Diagnostika sítě',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'nc hlásí „Connection timed out“. Co to nejspíš znamená?',
              'options' => 
              array (
                0 => 'Port je otevřený.',
                1 => 'Na pokus o spojení nepřišla žádná odpověď (cíl nedostupný nebo provoz zahazuje filtr).',
                2 => 'Cíl spojení aktivně odmítl.',
              ),
              'correct' => 1,
              'explanation' => 'Vypršení času = ticho, odmítnutí = RST.',
            ),
            1 => 
            array (
              'question' => 'Který filtr ve Wiresharku ukáže jen provoz na portu 443?',
              'options' => 
              array (
                0 => 'tcp.port == 443',
                1 => 'dns',
                2 => 'arp',
              ),
              'correct' => 0,
              'explanation' => 'Filtr vychází z otázky: zajímá mě HTTPS.',
            ),
            2 => 
            array (
              'question' => 'Proč začínáme diagnostiku hypotézou, a ne spuštěním všech nástrojů?',
              'options' => 
              array (
                0 => 'Protože nástroje jsou placené.',
                1 => 'Protože hypotéza je povinná podle zákona.',
                2 => 'Hypotéza určí, jaký důkaz hledat, a zkrátí cestu k příčině.',
              ),
              'correct' => 2,
              'explanation' => 'Bez otázky se v datech utopíš.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přečti v dokumentaci Wireshark (sekce Display Filters) tři příklady filtrů a jeden si zapiš s vysvětlením.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zachytávat provoz smíme jen v izolovaném labu nebo na připravených záznamech – nikdy cizí provoz.',
          1 => 'Záznamy provozu mohou obsahovat citlivé údaje; nesdílej je mimo třídu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Používej předpřipravené záznamy nebo izolovaný lab; nezachytávej provoz třetích osob.',
          1 => 'Otázka do třídy: Kdo poslal RST – klient, nebo server?',
          2 => 'Tempo: simulátor je rychlý, incident nech na dvojice s časovačem.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkol 3 v Linux Labu (výsledky jsou v zadání), ostatní úkoly na papír.',
          1 => 'Plán B offline: vytištěný záznam provozu a karty s výsledky nc.',
        ),
        'glossary' => 
        array (
          0 => 'rst',
          1 => 'display-filter',
        ),
        '_file' => 'lesson_content_v72_3a_a.php',
      ),
      8 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 8 · IPv4 II: dělení sítě (VLSM) a síťové zóny',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím rozdělit síť 192.168.10.0/24 na podsítě různé velikosti a určit, jaká komunikace mezi zónami je opravdu potřeba.',
          'success_criteria' => 
          array (
            0 => 'Rozdělím /24 na podsítě pro 100, 50, 20 a 2 zařízení bez překryvu.',
            1 => 'Ke každé podsíti zapíšu bránu, rozsah a rezervu.',
            2 => 'Pro zóny USERS, SERVERS, MGMT navrhnu pravidla „povolit jen nutné“ a ověřím je pozitivním i negativním testem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_addressing',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'net_security',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže síť, kde jsou všichni v jedné velké podsíti.',
            'student' => 'Vyjmenují rizika.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 30,
            'phase' => 'VLSM plánovač',
            'teacher' => 'Předvede VLSM Planner Lab: od největší podsítě.',
            'student' => 'Rozdělí 192.168.10.0/24 pro 4 segmenty.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 30,
            'to' => 42,
            'phase' => 'Adresní plán',
            'teacher' => 'Ukáže tabulku plánu s bránou a rezervou.',
            'student' => 'Zapíší plán a zkontrolují hranice.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 57,
            'phase' => 'Zóny důvěry',
            'teacher' => 'Předvede Security Zones Lab.',
            'student' => 'Definují zóny a potřebné služby.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 78,
            'phase' => 'Pravidla a ověření',
            'teacher' => 'Zdůrazní test povoleného i zakázaného scénáře.',
            'student' => 'Napíšou pravidla a pozitivní i negativní testy.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Kontrola a exit ticket',
            'teacher' => 'Namátkou zkontroluje dva plány.',
            'student' => 'Opraví překryv, pokud je, a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Rozděl 192.168.10.0/24 pro segmenty USERS (100 zařízení), SERVERS (50), MGMT (20) a propoj (2) – začni od největšího.',
            'output' => 'USERS 192.168.10.0/25, SERVERS 192.168.10.128/26, MGMT 192.168.10.192/27, propoj 192.168.10.224/30, rezerva od .228.',
            'time' => '20 min',
          ),
          1 => 
          array (
            'text' => 'Ke každé podsíti zapiš první použitelnou adresu jako bránu (gateway), poslední použitelnou adresu a počet použitelných adres.',
            'output' => 'Tabulka: /25 = 126, /26 = 62, /27 = 30, /30 = 2 použitelné adresy; brány .1, .129, .193, .225.',
            'time' => '12 min',
          ),
          2 => 
          array (
            'text' => 'Pro zóny USERS, SERVERS a MGMT napiš pravidla (ACL): USERS → WEB 443 povolit, MGMT → SERVERS 22 povolit, ostatní provoz na servery zakázat.',
            'output' => 'Tabulka pravidel (zdroj, cíl, port, akce).',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Ke každému pravidlu napiš pozitivní a negativní test (co musí projít a co nesmí).',
            'output' => 'Šest testů s očekávaným výsledkem.',
            'time' => '21 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku velikostí podsítí (/24 až /30 s počty adres) a předvyplněný první řádek plánu.',
          'standard' => 'Úplný plán, pravidla a testy podle zadání.',
          'challenge' => 'Doplní pátý segment (Wi-Fi hosté, 25 zařízení) do rezervy a ověří, že se nepřekrývá.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola hranic: dvojice si vymění plány a hledají překryv.',
            1 => 'Otázka: proč testujeme i to, co má být zakázané?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Dělení sítě',
              'levels' => 
              array (
                0 => 'Překryvy.',
                1 => 'Bez překryvu, plýtvání adresami.',
                2 => 'Správné podsítě od největší.',
                3 => 'Doplní další segment do rezervy.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Adresní plán',
              'levels' => 
              array (
                0 => 'Chybí brány.',
                1 => 'Chyby v rozsazích.',
                2 => 'Brány, rozsahy a počty správně.',
                3 => 'Plán zdůvodní a je čitelný pro kolegu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Pravidla a ověření',
              'levels' => 
              array (
                0 => 'Povolit vše.',
                1 => 'Jen pozitivní testy.',
                2 => 'Minimální pravidla + pozitivní i negativní testy.',
                3 => 'Zapíše i důkazy testů.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_addressing',
          'competence_label' => 'Adresace sítí',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kolik použitelných adres zařízení má podsíť /26?',
              'options' => 
              array (
                0 => '64',
                1 => '30',
                2 => '62',
              ),
              'correct' => 2,
              'explanation' => '2^6 = 64 adres, minus adresa sítě a všesměrová = 62.',
            ),
            1 => 
            array (
              'question' => 'Proč se při VLSM přiděluje nejdřív největší podsíť?',
              'options' => 
              array (
                0 => 'Aby se podsítě nepřekrývaly a nevznikaly nevyužitelné mezery.',
                1 => 'Protože je nejdůležitější.',
                2 => 'Kvůli rychlosti routeru.',
              ),
              'correct' => 0,
              'explanation' => 'Velké bloky musí začínat na své hranici.',
            ),
            2 => 
            array (
              'question' => 'Co ověřuje negativní test pravidla firewallu?',
              'options' => 
              array (
                0 => 'Že povolená služba funguje.',
                1 => 'Že zakázaný provoz opravdu neprojde.',
                2 => 'Že firewall je zapnutý.',
              ),
              'correct' => 1,
              'explanation' => 'Bez negativního testu nevíš, zda pravidlo něco blokuje.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: rozděl 10.20.0.0/24 pro segmenty 60, 28 a 10 zařízení a zapiš brány.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Pravidla navrhujeme a ověřujeme jen v labu; školní síť ani firewall neměníme.',
          1 => 'Adresy jsou privátní (RFC 1918) – v praxi je přiděluje správce sítě.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: podsíť /25 začínající na .100. Ukaž hranice bloků na číselné ose.',
          1 => 'Otázka do třídy: Kdo potřebuje mluvit s kým – a proč?',
          2 => 'Tempo: VLSM zabere slabším žákům víc – pravidla mohou dostat s předvyplněnými zónami.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 na papír, výsledky úkolů 1–2 jsou v zadání ke kontrole.',
          1 => 'Plán B offline: celé dělení na číselné ose 0–255 na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'vlsm',
          1 => 'gateway',
          2 => 'acl',
        ),
        '_file' => 'lesson_content_v72_3a_a.php',
      ),
      9 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 9 · DNS a DHCP II: čas v síti a řetěz diagnostiky služby',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vysvětlit, proč se změna DNS neprojeví hned (TTL) a jak se obnovuje zápůjčka DHCP, a projít řetěz DNS → TCP → TLS → HTTP při diagnostice webu.',
          'success_criteria' => 
          array (
            0 => 'Z výstupu dig vyčtu TTL a odhadnu, jak dlouho může klient držet starou odpověď.',
            1 => 'Naplánuji změnu DNS: snížit TTL předem, ověřit, vrátit TTL.',
            2 => 'U incidentu najdu první vrstvu řetězu, ve které chybí důkaz.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_diagnose',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'net_dns_dhcp',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše: změnili jsme adresu webu a polovina třídy pořád vidí starý server.',
            'student' => 'Odhadnou proč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'TTL a cache',
            'teacher' => 'Předvede TTL & Lease Timeline Lab.',
            'student' => 'V simulátoru vyčtou TTL záznamu a spočítají, kdy cache vyprší.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 38,
            'phase' => 'Životní cyklus zápůjčky',
            'teacher' => 'Vysvětlí obnovu zápůjčky (lease): renew a rebind.',
            'student' => 'Popíšou, co se stane při změně rozsahu DHCP.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 50,
            'phase' => 'Řízená změna',
            'teacher' => 'Ukáže plán změny DNS s oknem ověření.',
            'student' => 'Napíšou plán změny ve třech krocích.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 50,
            'to' => 78,
            'phase' => 'Řetěz diagnostiky',
            'teacher' => 'Předvede Service Chain Lab a zadá cvičný incident.',
            'student' => 'Ověří DNS, TCP a HTTP v simulátoru a najdou první chybějící důkaz.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Oprava a exit ticket',
            'teacher' => 'Zeptá se na opakovaný test po opravě.',
            'student' => 'Navrhnou opravu a ověření a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spusť dig intranet.skola.test a vyčti z řádku odpovědi TTL; spočítej, jak dlouho může klient držet starou adresu po změně.',
            'output' => 'TTL 3600 s = až 1 hodina.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig intranet.skola.test',
                'expect' => '3600',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Napiš plán změny adresy webu: kdy snížit TTL, jak dlouho počkat, jak ověřit a kdy TTL vrátit.',
            'output' => 'Plán změny ve třech krocích s časy.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'V Linux Labu projdi řetěz pro intranet.skola.test: DNS (dig +short), TCP (nc -zv … 80) a HTTP (curl -I) a zapiš výsledek každé vrstvy.',
            'output' => 'DNS → 10.0.0.10, TCP 80 → succeeded, HTTP → 200 OK.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig +short intranet.skola.test',
                'expect' => '10.0.0.10',
              ),
              1 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 80',
                'expect' => 'succeeded',
              ),
              2 => 
              array (
                'cmd' => 'curl -I http://intranet.skola.test',
                'expect' => 'HTTP/1.1 200 OK',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'V cvičném incidentu (DNS i TCP 443 v pořádku, certifikát prošlý) urči vrstvu závady, navrhni opravu a opakovaný test.',
            'output' => 'Vrstva TLS, oprava (obnova certifikátu) a test.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu řetězu DNS → TCP → TLS → HTTP s příkazem u každé vrstvy.',
          'standard' => 'TTL, plán změny, řetěz v simulátoru a incident podle zadání.',
          'challenge' => 'Navrhne monitorovací kontrolu, která upozorní na certifikát 14 dní před vypršením.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kartičky vrstev: učitel popíše symptom, třída ukáže vrstvu.',
            1 => 'Plán změny: kde je v plánu krok „vrátit TTL“?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Čas v síti',
              'levels' => 
              array (
                0 => 'Nezná TTL.',
                1 => 'Zná TTL, nevyčte ho.',
                2 => 'Vyčte TTL a odhadne dopad.',
                3 => 'Vysvětlí i obnovu zápůjčky DHCP.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Plán změny',
              'levels' => 
              array (
                0 => 'Bez plánu.',
                1 => 'Změna bez ověření.',
                2 => 'Snížit TTL → ověřit → vrátit.',
                3 => 'Plán obsahuje i postup návratu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Řetěz diagnostiky',
              'levels' => 
              array (
                0 => 'Náhodné testy.',
                1 => 'Testy bez pořadí.',
                2 => 'Projde vrstvy a najde první chybějící důkaz.',
                3 => 'Navrhne monitoring dané vrstvy.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_diagnose',
          'competence_label' => 'Diagnostika sítě',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Záznam DNS má TTL 3600. Za jak dlouho nejpozději uvidí klient s uloženou odpovědí novou adresu?',
              'options' => 
              array (
                0 => 'Okamžitě.',
                1 => 'Nejpozději zhruba za hodinu, až mu vyprší uložená odpověď.',
                2 => 'Za 3600 dní.',
              ),
              'correct' => 1,
              'explanation' => 'TTL je v sekundách: 3600 s = 1 h.',
            ),
            1 => 
            array (
              'question' => 'Proč se TTL snižuje už několik hodin před plánovanou změnou adresy?',
              'options' => 
              array (
                0 => 'Aby si klienti starou odpověď nepamatovali dlouho a změna se projevila rychle.',
                1 => 'Aby DNS server méně pracoval.',
                2 => 'Protože nízké TTL zrychlí web.',
              ),
              'correct' => 0,
              'explanation' => 'Krátké TTL zkrátí přechodné období.',
            ),
            2 => 
            array (
              'question' => 'dig vrací správnou adresu, port 443 je otevřený, ale prohlížeč hlásí neplatný certifikát. Kde hledat?',
              'options' => 
              array (
                0 => 'V DHCP.',
                1 => 'V záznamu A.',
                2 => 'Ve vrstvě TLS (certifikát).',
              ),
              'correct' => 2,
              'explanation' => 'První chybějící důkaz je v TLS.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v prohlížeči klikni na zámek u adresy libovolného webu a zapiš, do kdy platí jeho certifikát.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Změny DNS a DHCP jen v labu; skutečné záznamy školy nikdy neměníme.',
          1 => 'Při prohlížení certifikátů se nic nestahuje ani neinstaluje.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „změnil jsem DNS, tak to hned platí“. Ukaž TTL ve výstupu.',
          1 => 'Otázka do třídy: Která vrstva řetězu je první, kde chybí důkaz?',
          2 => 'Tempo: řetěz v simulátoru je jádro – lab TTL lze zkrátit na 10 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1 a 3 v Linux Labu (výstupy v zadání), úkoly 2 a 4 na papír.',
          1 => 'Plán B offline: časová osa TTL na papíře a karty vrstev řetězu.',
        ),
        'glossary' => 
        array (
          0 => 'ttl',
          1 => 'lease',
          2 => 'tls',
        ),
        '_file' => 'lesson_content_v72_3a_a.php',
      ),
      10 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 10 · Příkazová řádka Linuxu: orientace bez klikání',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím se v Linuxu (v shellu) orientovat příkazy pwd, ls, cd, vytvářet, kopírovat a přesouvat soubory a vysvětlit rozdíl absolutní a relativní cesty (path).',
          'success_criteria' => 
          array (
            0 => 'Vysvětlím účel adresářů /home, /etc, /var, /tmp a /usr.',
            1 => 'Použiji absolutní i relativní cestu a vím, kde právě jsem (pwd).',
            2 => 'Před mazáním ověřím umístění a argument příkazu.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_navigation',
            'level' => 1,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže strom adresářů a zeptá se, kde se ukládá nastavení.',
            'student' => 'Tipují adresář.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Mapa souborového systému',
            'teacher' => 'Vysvětlí /home, /etc, /var, /tmp, /usr.',
            'student' => 'Ke každému adresáři zapíší, co obsahuje.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 40,
            'phase' => 'Navigace',
            'teacher' => 'Předvede pwd, ls -la, cd s absolutní a relativní cestou.',
            'student' => 'V Linux Labu projdou /var/log a vrátí se domů.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 60,
            'phase' => 'Práce se soubory',
            'teacher' => 'Ukáže mkdir, touch, cp, mv v testovacím adresáři.',
            'student' => 'Vytvoří adresář lab a zkopírují a přejmenují soubor.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 78,
            'phase' => 'Nejdřív zkoumat, pak měnit',
            'teacher' => 'Ukáže file a head před úpravou souboru.',
            'student' => 'Zjistí typ souboru a první řádky /etc/passwd.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Pravidlo před rm a exit ticket',
            'teacher' => 'Zformuluje se třídou bezpečnostní pravidlo.',
            'student' => 'Zapíší pravidlo a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spusť pwd hned po přihlášení, pak cd /var/log a ls; zapiš, kde jsi a jaké soubory tam jsou.',
            'output' => '/home/student; v /var/log jsou auth.log, dpkg.log, adresář nginx a syslog.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'pwd',
                'expect' => '/home/student',
              ),
              1 => 
              array (
                'cmd' => 'cd /var/log; ls',
                'expect' => 'dpkg.log',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Ve svém domovském adresáři vytvoř adresář lab, v něm soubor test.txt, zkopíruj ho jako kopie.txt a přejmenuj na zaloha.txt.',
            'output' => 'ls lab ukáže test.txt a zaloha.txt.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'cd ~; mkdir lab; touch lab/test.txt; cp lab/test.txt lab/kopie.txt; mv lab/kopie.txt lab/zaloha.txt; ls lab',
                'expect' => 'test.txt  zaloha.txt',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Napiš jednu absolutní a jednu relativní cestu k souboru lab/test.txt a vysvětli rozdíl.',
            'output' => '/home/student/lab/test.txt vs. lab/test.txt (z domovského adresáře).',
            'time' => '10 min',
          ),
          3 => 
          array (
            'text' => 'Než budeš cokoli měnit, zjisti typ souboru /etc/hosts (file) a první tři řádky /etc/passwd (head -n 3).',
            'output' => 'file: „Unicode text, UTF-8 text“; první řádek root:x:0:0:root:/root:/bin/bash.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'file /etc/hosts',
                'expect' => 'UTF-8 text',
              ),
              1 => 
              array (
                'cmd' => 'head -n 3 /etc/passwd',
                'expect' => 'root:x:0:0:root:/root:/bin/bash',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tahák příkazů s jedním příkladem ke každému a pracuje podle číslovaných kroků.',
          'standard' => 'Úkoly 1–4 v Linux Labu podle zadání.',
          'challenge' => 'Použije find ~/lab -name "*.txt" a vysvětlí, proč je výpis jiný než ls.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => '„Kde jsem?“: učitel napíše sérii cd, třída určí výsledné pwd.',
            1 => 'Ukázka příkazu: každý ukáže svůj výstup ls lab.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Orientace',
              'levels' => 
              array (
                0 => 'Neví, kde je.',
                1 => 'Používá jen cd bez kontroly.',
                2 => 'pwd, ls, cd s oběma typy cest.',
                3 => 'Vysvětlí cestu spolužákovi.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Práce se soubory',
              'levels' => 
              array (
                0 => 'Nezvládne.',
                1 => 'S chybami v cestě.',
                2 => 'mkdir, touch, cp, mv bez chyb.',
                3 => 'Ověřuje výsledek po každém kroku.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Bezpečný postup',
              'levels' => 
              array (
                0 => 'Mění naslepo.',
                1 => 'Ověří jen někdy.',
                2 => 'Před změnou zkoumá (file, head) a ověří cestu.',
                3 => 'Formuluje pravidlo pro ostatní.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_navigation',
          'competence_label' => 'Linux: orientace a soubory',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jsi v /home/student. Kam tě přenese příkaz cd ../..?',
              'options' => 
              array (
                0 => 'Do /home',
                1 => 'Do kořenového adresáře /',
                2 => 'Do /home/student/..',
              ),
              'correct' => 1,
              'explanation' => 'Dvakrát o úroveň výš: /home → /.',
            ),
            1 => 
            array (
              'question' => 'Ve kterém adresáři hledáš nastavení systému a služeb?',
              'options' => 
              array (
                0 => '/tmp',
                1 => '/home',
                2 => '/etc',
              ),
              'correct' => 2,
              'explanation' => '/etc = konfigurace.',
            ),
            2 => 
            array (
              'question' => 'Co uděláš jako první, než spustíš rm na soubor?',
              'options' => 
              array (
                0 => 'Ověřím pwd a přesnou cestu argumentu.',
                1 => 'Spustím ho dvakrát pro jistotu.',
                2 => 'Restartuji počítač.',
              ),
              'correct' => 0,
              'explanation' => 'Chyba v cestě může smazat jiná data.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu vyřeš první úroveň balíčku Start a zapiš si příkazy, které jsi použil.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Pracujeme jen v simulátoru a v testovacím adresáři lab.',
          1 => 'Destruktivní příkazy nikdy nad systémovými cestami – ani v simulátoru je nezkoušej „pro zábavu“.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Používej testovací adresář; nedávej destruktivní příklady nad skutečnými systémovými cestami.',
          1 => 'Otázka do třídy: Jak poznám, kde právě jsem?',
          2 => 'Tempo: barevný výpis ls může mást – ukaž, že modré jsou adresáře.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 v Linux Labu, očekávané výstupy jsou přímo v zadání.',
          1 => 'Plán B offline: strom adresářů na papíře a „hra na cd“ s kartičkami.',
        ),
        'glossary' => 
        array (
          0 => 'shell',
          1 => 'path',
        ),
        '_file' => 'lesson_content_v72_3a_a.php',
      ),
      11 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 11 · Uživatelé, skupiny a oprávnění',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím přečíst oprávnění z výpisu ls -l, nastavit chmod pro vlastníka, skupinu a ostatní a navrhnout nejmenší potřebná práva pro sdílený soubor.',
          'success_criteria' => 
          array (
            0 => 'Převedu zápis rw-r----- na význam i na číslo 640.',
            1 => 'Rozliším chmod (práva) a chown (vlastník a skupina).',
            2 => 'Pro sdílený soubor navrhnu skupinu místo práv pro všechny.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_users',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže, že student nemůže přečíst /var/log/auth.log.',
            'student' => 'Odhadnou, proč přístup chybí.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Vlastník, skupina, ostatní',
            'teacher' => 'Rozebere řádek ls -l (typ, tři trojice práv, vlastník, skupina).',
            'student' => 'Přečtou práva u tří souborů v /var/log.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'rwx a čísla',
            'teacher' => 'Vysvětlí r = 4, w = 2, x = 1.',
            'student' => 'Nastaví souboru tajne.conf práva 640 a ověří je.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 60,
            'phase' => 'Skupinový přístup',
            'teacher' => 'Předvede groupadd, usermod -aG a chown.',
            'student' => 'Vytvoří skupinu webteam a přiřadí jí soubor.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 78,
            'phase' => 'Nejmenší práva (least privilege)',
            'teacher' => 'Ukáže, proč chmod 777 není oprava.',
            'student' => 'Navrhnou práva pro konfiguraci, log a sdílený soubor.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Shrnutí a exit ticket',
            'teacher' => 'Shrne: kdo potřebuje co dělat?',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spusť ls -l /var/log a přečti práva souboru auth.log; vysvětli, proč ho student bez sudo nepřečte.',
            'output' => '-rw-r----- root adm: číst smí jen vlastník root a skupina adm.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ls -l /var/log',
                'expect' => '-rw-r----- 1 root adm',
              ),
              1 => 
              array (
                'cmd' => 'tail -n 3 /var/log/auth.log',
                'expect' => 'Permission denied',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Vytvoř soubor tajne.conf, nastav mu práva 640 a ověř je příkazy ls -l a stat.',
            'output' => '-rw-r----- a „Access: (0640/-rw-r-----)“.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'touch tajne.conf; chmod 640 tajne.conf; ls -l tajne.conf',
                'expect' => '-rw-r----- 1 student student',
              ),
              1 => 
              array (
                'cmd' => 'touch tajne.conf; chmod 640 tajne.conf; stat tajne.conf',
                'expect' => 'Access: (0640/-rw-r-----)',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Vytvoř skupinu webteam, přidej do ní uživatele student a změň skupinu souboru sdileny.txt na webteam.',
            'output' => 'id student ukáže skupinu webteam; ls -l ukáže skupinu webteam u souboru.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo groupadd webteam; sudo usermod -aG webteam student; id student',
                'expect' => '(webteam)',
              ),
              1 => 
              array (
                'cmd' => 'sudo groupadd webteam; touch sdileny.txt; sudo chown root:webteam sdileny.txt; ls -l sdileny.txt',
                'expect' => 'root webteam',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Navrhni práva (číslem i písmeny) pro konfigurační soubor s heslem, log služby a sdílený soubor týmu a každé zdůvodni.',
            'output' => 'Např. 600 nebo 640, 640, 664 se skupinou – se zdůvodněním.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku r = 4, w = 2, x = 1 a tři rozepsané příklady převodu.',
          'standard' => 'Čtení práv, chmod, skupina a návrh práv podle zadání.',
          'challenge' => 'Vysvětlí, co znamená právo x u adresáře a proč bez něj do adresáře nevstoupíš.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Převodní rychlovka: učitel řekne rw-r--r--, třída napíše 644.',
            1 => 'Dvojice: chmod, nebo chown? (učitel popisuje situace)',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Čtení práv',
              'levels' => 
              array (
                0 => 'Nepřečte ls -l.',
                1 => 'Přečte jen vlastníka.',
                2 => 'Přečte všechny tři trojice i číslo.',
                3 => 'Vysvětlí právo x u adresáře.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Nastavení práv',
              'levels' => 
              array (
                0 => 'Nezvládne.',
                1 => 'chmod s chybou.',
                2 => 'chmod 640 i chown se skupinou ověřené.',
                3 => 'Ověřuje stat i z pohledu jiného uživatele.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Nejmenší práva',
              'levels' => 
              array (
                0 => 'Navrhne 777.',
                1 => 'Práva příliš široká.',
                2 => 'Přiměřená práva se zdůvodněním.',
                3 => 'Model „kdo potřebuje co“ pro celý tým.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_users',
          'competence_label' => 'Linux: uživatelé a práva',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jaký číselný zápis odpovídá právům rw-r--r--?',
              'options' => 
              array (
                0 => '755',
                1 => '644',
                2 => '600',
              ),
              'correct' => 1,
              'explanation' => 'rw- = 6, r-- = 4, r-- = 4.',
            ),
            1 => 
            array (
              'question' => 'Soubor má vlastníka root a skupinu adm s právy rw-r-----. Kdo ho smí číst?',
              'options' => 
              array (
                0 => 'Root a členové skupiny adm.',
                1 => 'Všichni uživatelé.',
                2 => 'Jen root.',
              ),
              'correct' => 0,
              'explanation' => 'Druhá trojice r-- platí pro skupinu.',
            ),
            2 => 
            array (
              'question' => 'Tým potřebuje upravovat jeden sdílený soubor. Jaké řešení je nejlepší?',
              'options' => 
              array (
                0 => 'chmod 777 pro všechny.',
                1 => 'Sdílet heslo účtu root.',
                2 => 'Skupina týmu jako skupina souboru a právo zápisu pro skupinu.',
              ),
              'correct' => 2,
              'explanation' => 'Skupina = jen ti, kdo práva potřebují.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu vyřeš jednu úroveň balíčku Práva a zapiš použitý příkaz chmod.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Práva a uživatele měníme jen v simulátoru, ne na školních počítačích.',
          1 => 'Hesla v ukázkových souborech jsou vymyšlená; skutečná hesla do souborů nepíšeme.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Používej model „kdo potřebuje co dělat“ místo memorování čísel.',
          1 => 'Otázka do třídy: Proč chmod 777 problém spíš schová, než opraví?',
          2 => 'Tempo: propojení s dalšími lekcemi – SSH klíče (L14) potřebují právě tato práva.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), úkol 4 na papír.',
          1 => 'Plán B offline: karty s výpisy ls -l a převod na čísla.',
        ),
        'glossary' => 
        array (
          0 => 'least-privilege',
          1 => 'sudo',
        ),
        '_file' => 'lesson_content_v72_3a_b.php',
      ),
      12 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 12 · Procesy a systemd: služba není magie',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím najít běžící proces služby, přečíst stav služby v systemctl status a rozlišit restart, reload, start a enable.',
          'success_criteria' => 
          array (
            0 => 'Ve výpisu systemctl status najdu Active, Main PID a poslední řádky logu.',
            1 => 'Rozliším proces (PID) a jednotku služby (unit).',
            2 => 'Před reloadem ověřím konfiguraci (nginx -t) a vysvětlím, proč restart není první krok.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_services',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Zeptá se: web nejde, co uděláš jako první?',
            'student' => 'Většina řekne „restart“ – zapíšeme si to.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Proces a služba',
            'teacher' => 'Předvede ps aux a pgrep.',
            'student' => 'Najdou procesy nginx a jejich PID.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'systemctl status',
            'teacher' => 'Rozebere řádky Loaded, Active, Main PID a log.',
            'student' => 'Přečtou stav nginx a cron.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 58,
            'phase' => 'start / restart / reload / enable',
            'teacher' => 'Vysvětlí rozdíly a kontrolu konfigurace.',
            'student' => 'Ověří nginx -t a is-enabled.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 78,
            'phase' => 'Služba neběží',
            'teacher' => 'Zastaví nginx v simulátoru a zadá incident.',
            'student' => 'Ze statusu zjistí stav a navrhnou nejmenší další test.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Návrat k úvodu a exit ticket',
            'teacher' => 'Vrátí se k odpovědi „restart“ z úvodu.',
            'student' => 'Zdůvodní, proč nejdřív důkaz; exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Najdi procesy webového serveru příkazy ps aux a pgrep nginx a zapiš PID hlavního procesu.',
            'output' => 'Hlavní proces „nginx: master process“ s PID 591, pracovní proces 728.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'pgrep nginx',
                'expect' => '591',
              ),
              1 => 
              array (
                'cmd' => 'ps aux',
                'expect' => 'nginx: master process',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Přečti systemctl status nginx a zapiš hodnoty Loaded, Active a Main PID.',
            'output' => 'Loaded … enabled; Active: active (running); Main PID: 591 (nginx).',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'Active: active (running)',
              ),
              1 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'Main PID: 591 (nginx)',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř konfiguraci příkazem sudo nginx -t a zjisti, zda se služba spouští po startu (systemctl is-enabled nginx).',
            'output' => '„syntax is ok“ a „enabled“.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo nginx -t',
                'expect' => 'syntax is ok',
              ),
              1 => 
              array (
                'cmd' => 'systemctl is-enabled nginx',
                'expect' => 'enabled',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Zastav nginx, přečti jeho status a navrhni nejmenší další test, než cokoli restartuješ.',
            'output' => 'Active: inactive (dead) + navržený test (např. přečíst log služby).',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; systemctl status nginx',
                'expect' => 'inactive (dead)',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vytištěný výstup systemctl status s barevně označenými řádky.',
          'standard' => 'Úkoly 1–4 v Linux Labu podle zadání.',
          'challenge' => 'Vysvětlí, proč reload nepřeruší rozpracované požadavky a restart ano.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž řádek: učitel řekne „hlavní proces“, žáci ukážou Main PID.',
            1 => 'Situace: změnil jsem konfiguraci – restart, nebo reload?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Proces a služba',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Najde PID, neví vztah ke službě.',
                2 => 'Najde PID a přiřadí ho ke službě.',
                3 => 'Vysvětlí hlavní a pracovní procesy.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Čtení statusu',
              'levels' => 
              array (
                0 => 'Nepřečte.',
                1 => 'Jen Active.',
                2 => 'Loaded, Active, Main PID, log.',
                3 => 'Z logu odvodí další krok.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Akce se službou',
              'levels' => 
              array (
                0 => 'Vždy restart.',
                1 => 'Zná rozdíly, neověřuje konfiguraci.',
                2 => 'nginx -t před reloadem, ví co je enable.',
                3 => 'Navrhne nejmenší bezpečný zásah.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_services',
          'competence_label' => 'Linux: služby a logy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co dělá systemctl enable nginx?',
              'options' => 
              array (
                0 => 'Okamžitě službu restartuje.',
                1 => 'Nastaví automatické spuštění služby při startu systému.',
                2 => 'Smaže logy služby.',
              ),
              'correct' => 1,
              'explanation' => 'enable = po startu; start = teď.',
            ),
            1 => 
            array (
              'question' => 'Změnil jsi konfiguraci nginx. Co uděláš před reloadem?',
              'options' => 
              array (
                0 => 'Ověřím syntaxi příkazem nginx -t.',
                1 => 'Restartuji celý počítač.',
                2 => 'Nic, reload chybu sám opraví.',
              ),
              'correct' => 0,
              'explanation' => 'Chybná konfigurace by službu shodila.',
            ),
            2 => 
            array (
              'question' => 'Proč restart není dobrý první krok při výpadku služby?',
              'options' => 
              array (
                0 => 'Restart trvá hodiny.',
                1 => 'Restart je zakázaný.',
                2 => 'Změní stav dřív, než získáš důkazy, a může schovat příčinu.',
              ),
              'correct' => 2,
              'explanation' => 'Nejdřív status a log, pak zásah.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu zjisti stav služby cron a zapiš její Main PID.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Služby zastavujeme jen v simulátoru nebo testovacím virtuálním počítači.',
          1 => 'Na skutečném serveru by výpadek služby zasáhl uživatele – proto nejdřív důkaz, pak zásah.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Doporuč bezpečný lab; připomínej kontrolu syntaxe před reloadem webového serveru.',
          1 => 'Otázka do třídy: Co nám status řekl dřív, než jsme cokoli změnili?',
          2 => 'Tempo: incident (úkol 4) je klíčový – úkol 1 může být rychlý.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 v Linux Labu, výstupy jsou v zadání.',
          1 => 'Plán B offline: vytištěné výstupy status a ps a karty „restart / reload / enable“.',
        ),
        'glossary' => 
        array (
          0 => 'systemd-unit',
          1 => 'pid',
          2 => 'reload',
        ),
        '_file' => 'lesson_content_v72_3a_b.php',
      ),
      13 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 13 · Logy a journalctl: časová osa důkazů',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím použít logy jako cílený důkaz: filtrovat podle služby, času a závažnosti (severity) a propojit log se stavem služby a testem portu.',
          'success_criteria' => 
          array (
            0 => 'V záznamu logu najdu čas, zdroj, závažnost a zprávu.',
            1 => 'Omezím journalctl na jednu službu a časové okno.',
            2 => 'Napíšu hypotézu, kterou důkazy z logu potvrdí nebo vyloučí.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_services',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže 2000 řádků logu a zeptá se, kde začít.',
            'student' => 'Navrhnou strategii hledání.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Užitečný záznam',
            'teacher' => 'Rozebere řádek logu: čas, počítač, služba, zpráva.',
            'student' => 'Rozeberou tři řádky z auth.log.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Filtry',
            'teacher' => 'Předvede journalctl -u, -n, -p a --since.',
            'student' => 'Omezí log na nginx, ssh a na chyby.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 58,
            'phase' => 'Hledání v textovém logu',
            'teacher' => 'Ukáže sudo grep -c v auth.log.',
            'student' => 'Spočítají neúspěšná přihlášení.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 78,
            'phase' => 'Korelace',
            'teacher' => 'Zadá incident: zastavený web.',
            'student' => 'Propojí status, journal a test portu do časové osy.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Závěr a exit ticket',
            'teacher' => 'Zeptá se: co důkazy vylučují?',
            'student' => 'Napíšou vyloučenou hypotézu a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přečti poslední čtyři řádky auth.log (sudo tail -n 4) a u každého urči čas, službu a co se stalo.',
            'output' => 'Tabulka: čas – služba (systemd-logind, sudo) – událost.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo tail -n 4 /var/log/auth.log',
                'expect' => 'sudo:',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Omez journal na jednu službu (journalctl -u nginx -n 5, journalctl -u ssh -n 3) a na chyby (journalctl -p err -n 5).',
            'output' => 'Řádek „Started nginx.service …“, „Server listening on 0.0.0.0 port 22.“ a „-- No entries --“ u chyb.',
            'time' => '17 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'journalctl -u nginx -n 5',
                'expect' => 'Started nginx.service',
              ),
              1 => 
              array (
                'cmd' => 'journalctl -u ssh -n 3',
                'expect' => 'Server listening on 0.0.0.0 port 22.',
              ),
              2 => 
              array (
                'cmd' => 'journalctl -p err -n 5',
                'expect' => '-- No entries --',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Spočítej neúspěšná přihlášení v auth.log příkazem sudo grep -c "Failed password".',
            'output' => 'Výsledek 1.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep -c \'Failed password\' /var/log/auth.log',
                'expect' => '1',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Zastav nginx, pak sestav časovou osu ze systemctl status, journalctl -u nginx a curl -I http://localhost a napiš hypotézu, kterou důkazy vylučují.',
            'output' => 'Časová osa (Stopping → Stopped → curl selže) + vyloučená hypotéza (např. „chyba DNS“).',
            'time' => '25 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; journalctl -u nginx -n 5',
                'expect' => 'Stopped nginx.service',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tahák přepínačů journalctl (-u, -n, -p, --since) s jedním příkladem.',
          'standard' => 'Úkoly 1–4 v Linux Labu podle zadání.',
          'challenge' => 'Vysvětlí, proč „-- No entries --“ u chyb neznamená, že je systém zdravý.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rozbor řádku: učitel promítne řádek logu, třída určí čas, službu, zprávu.',
            1 => 'Filtr na zavolání: jak omezíš log jen na ssh?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Čtení záznamu',
              'levels' => 
              array (
                0 => 'Nepřečte.',
                1 => 'Jen zpráva.',
                2 => 'Čas, služba, závažnost, zpráva.',
                3 => 'Odliší příznak od příčiny.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Filtrování',
              'levels' => 
              array (
                0 => 'Čte celý log.',
                1 => 'Jeden filtr.',
                2 => 'Služba, počet, závažnost.',
                3 => 'Časové okno kolem incidentu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Korelace',
              'levels' => 
              array (
                0 => 'Bez osy.',
                1 => 'Osa z jednoho zdroje.',
                2 => 'Status + journal + test portu.',
                3 => 'Vyloučená hypotéza se zdůvodněním.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_services',
          'competence_label' => 'Linux: služby a logy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kterým přepínačem omezíš journalctl na jednu službu?',
              'options' => 
              array (
                0 => '-u',
                1 => '-n',
                2 => '-p',
              ),
              'correct' => 0,
              'explanation' => '-u = unit (jednotka služby); -n = počet řádků; -p = závažnost.',
            ),
            1 => 
            array (
              'question' => 'Při dlouhém logu je nejlepší začít:',
              'options' => 
              array (
                0 => 'Od prvního řádku po startu systému.',
                1 => 'Smazáním logu a čekáním na novou chybu.',
                2 => 'U času incidentu a u konkrétní služby.',
              ),
              'correct' => 2,
              'explanation' => 'Čas + služba = malé okno důkazů.',
            ),
            2 => 
            array (
              'question' => 'journalctl -p err vrátí „-- No entries --“. Co z toho plyne?',
              'options' => 
              array (
                0 => 'Systém je určitě v pořádku.',
                1 => 'Tento zdroj neobsahuje chyby – problém může být jinde nebo se nezaloguje jako chyba.',
                2 => 'Logování je vypnuté.',
              ),
              'correct' => 1,
              'explanation' => 'Absence chyby v logu není důkaz zdraví.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš pět řádků „časové osy“ svého rána (čas – událost) ve formátu logu.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Logy mohou obsahovat osobní údaje (jména uživatelů, adresy) – pracujeme jen s logy simulátoru.',
          1 => 'Logy skutečných systémů se nesdílejí mimo správu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Učte časovou korelaci napříč logy, ne čtení odshora.',
          1 => 'Otázka do třídy: Proč tenhle filtr používáš?',
          2 => 'Tempo: auth.log je čitelný jen přes sudo – to je i opakování z lekce 11.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 v Linux Labu, výstupy jsou v zadání.',
          1 => 'Plán B offline: vytištěné logy a skládání časové osy z kartiček.',
        ),
        'glossary' => 
        array (
          0 => 'journal',
          1 => 'severity',
        ),
        '_file' => 'lesson_content_v72_3a_b.php',
      ),
      14 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 14 · SSH a SFTP: bezpečný vzdálený přístup',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vytvořit pár klíčů SSH, odlišit soukromý a veřejný klíč, rozlišit chybu sítě od chyby přihlášení a bezpečně přenést soubor přes SFTP.',
          'success_criteria' => 
          array (
            0 => 'Vytvořím pár klíčů a ověřím, že soukromý klíč má práva 600.',
            1 => 'Podle chyby rozliším „síť nedostupná“ a „Permission denied“.',
            2 => 'Vím, který soubor je tajný a kam nepatří (projekt, odevzdání, chat).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_ssh',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže obrázek zámku a klíče: co je veřejné, co tajné?',
            'student' => 'Přiřadí pojmy veřejný a soukromý klíč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Cesta spojení SSH',
            'teacher' => 'Vysvětlí pořadí: jméno/IP → TCP 22 → přihlášení.',
            'student' => 'V simulátoru ověří, že port 22 na 10.0.0.10 odpovídá.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Klíče',
            'teacher' => 'Předvede ssh-keygen v simulátoru.',
            'student' => 'Vytvoří pár klíčů a prohlédnou práva souborů.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 58,
            'phase' => 'Permission denied',
            'teacher' => 'Ukáže přihlášení bez nahraného veřejného klíče.',
            'student' => 'Zapíší tři možné příčiny chyby přihlášení.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 78,
            'phase' => 'Nastavení serveru a SFTP',
            'teacher' => 'Ukáže sshd_config a SFTP přenos.',
            'student' => 'Najdou PermitRootLogin a popíšou bezpečný přenos.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Pravidla klíčů a exit ticket',
            'teacher' => 'Shrne pravidla nakládání s klíči.',
            'student' => 'Zapíší pravidla a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Ověř příkazem nc -zv 10.0.0.10 22, že na serveru odpovídá port SSH, dřív než budeš řešit přihlášení.',
            'output' => '„Connection … 22 port [tcp/ssh] succeeded!“',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 22',
                'expect' => 'succeeded',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Vytvoř pár klíčů ssh-keygen -t ed25519 -f ~/.ssh/id_lab -N \'\' a ověř práva obou souborů příkazem ls -l ~/.ssh.',
            'output' => 'id_lab má -rw------- (600), id_lab.pub má -rw-r--r-- (644).',
            'time' => '17 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ssh-keygen -t ed25519 -f ~/.ssh/id_lab -N \'\'; ls -l ~/.ssh',
                'expect' => '-rw------- 1 student student',
              ),
              1 => 
              array (
                'cmd' => 'ssh-keygen -t ed25519 -f ~/.ssh/id_lab -N \'\'',
                'expect' => 'Your public key has been saved in /home/student/.ssh/id_lab.pub',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Zkus ssh student@10.0.0.10 a podle chyby urči, zda jde o síť, nebo o přihlášení; zapiš tři možné příčiny.',
            'output' => '„Permission denied (publickey,password)“ = síť funguje, selhalo přihlášení (chybí veřejný klíč v ~/.ssh/authorized_keys na serveru, špatný uživatel, špatná práva klíče).',
            'time' => '16 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ssh student@10.0.0.10',
                'expect' => 'Permission denied (publickey,password)',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Přečti nastavení serveru (sudo cat /etc/ssh/sshd_config) a najdi, zda se smí přihlásit root; popiš bezpečný přenos souboru přes SFTP.',
            'output' => '„PermitRootLogin no“ + postup SFTP (ověřit cílovou cestu a práva).',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cat /etc/ssh/sshd_config',
                'expect' => 'PermitRootLogin no',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane diagram „síť → port → přihlášení“ a tabulku chyb s významem.',
          'standard' => 'Úkoly 1–4 v Linux Labu podle zadání.',
          'challenge' => 'Vysvětlí, k čemu je soubor known_hosts a co znamená varování o změně klíče serveru.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rozhodni: učitel přečte chybovou hlášku, třída určí „síť“ nebo „přihlášení“.',
            1 => 'Palec nahoru/dolů: smím poslat id_lab.pub spolužákovi? A id_lab?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Klíče a práva',
              'levels' => 
              array (
                0 => 'Nevytvoří pár.',
                1 => 'Vytvoří, nezná práva.',
                2 => 'Pár klíčů, soukromý klíč 600.',
                3 => 'Vysvětlí, proč SSH odmítá příliš otevřený klíč.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Diagnostika spojení',
              'levels' => 
              array (
                0 => 'Nerozliší chyby.',
                1 => 'Jen „nejde to“.',
                2 => 'Síť vs. přihlášení podle hlášky.',
                3 => 'Tři příčiny Permission denied.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Bezpečné návyky',
              'levels' => 
              array (
                0 => 'Sdílí soukromý klíč.',
                1 => 'Neví, co je tajné.',
                2 => 'Soukromý klíč nikam neposílá, root zakázaný.',
                3 => 'Navrhne pravidla pro tým.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_ssh',
          'competence_label' => 'Linux: SSH a klíče',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Port 22 odpovídá, ale SSH hlásí „Permission denied“. Kde je nejpravděpodobnější problém?',
              'options' => 
              array (
                0 => 'V síti mezi počítači.',
                1 => 'V DNS.',
                2 => 'V přihlášení: uživatel, klíč nebo jeho práva.',
              ),
              'correct' => 2,
              'explanation' => 'Síť je ověřená, selhalo ověření identity.',
            ),
            1 => 
            array (
              'question' => 'Který soubor po ssh-keygen nesmíš nikomu poslat?',
              'options' => 
              array (
                0 => 'id_lab (soukromý klíč).',
                1 => 'id_lab.pub (veřejný klíč).',
                2 => 'Oba můžeš sdílet.',
              ),
              'correct' => 0,
              'explanation' => 'Soukromý klíč = tvoje identita.',
            ),
            2 => 
            array (
              'question' => 'Jaká práva má mít soukromý klíč SSH?',
              'options' => 
              array (
                0 => '777, aby k němu SSH vždy mělo přístup.',
                1 => '600 – číst a zapisovat jen vlastník.',
                2 => '644 jako běžný soubor.',
              ),
              'correct' => 1,
              'explanation' => 'Příliš otevřený soukromý klíč SSH odmítne.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přečti si v manuálové stránce ssh-keygen (man ssh-keygen v Linux Labu) popis přepínače -t a zapiš dva typy klíčů.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Nikdy neodevzdáváme ani nesdílíme skutečné soukromé klíče – pracujeme jen s testovacími klíči v simulátoru.',
          1 => 'Do projektu ani repozitáře soukromý klíč nepatří.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Nikdy nevyžaduj reálné soukromé klíče žáků do odevzdání; pracuj s testovacími klíči a VM.',
          1 => 'Otázka do třídy: Co přesně dokazuje hláška Permission denied?',
          2 => 'Tempo: ssh-keygen v simulátoru je rychlý – čas dej diagnostice chyb.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 v Linux Labu, výstupy jsou v zadání.',
          1 => 'Plán B offline: karty chybových hlášek a diagram cesty spojení.',
        ),
        'glossary' => 
        array (
          0 => 'sftp',
          1 => 'authorized-keys',
          2 => 'known-hosts',
        ),
        '_file' => 'lesson_content_v72_3a_b.php',
      ),
      15 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 15 · Firewall a služby: co poslouchá a kdo se tam dostane',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím zjistit, které služby poslouchají a na jaké adrese, a propojit naslouchání, navázání na adresu (bind), firewall a test z klienta do jednoho diagnostického modelu.',
          'success_criteria' => 
          array (
            0 => 'Z výpisu ss -tln určím port a adresu, na které služba poslouchá.',
            1 => 'Vysvětlím rozdíl 127.0.0.1, 0.0.0.0 a konkrétní IP.',
            2 => 'Ke každému pravidlu firewallu napíšu pozitivní i negativní test.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_services',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'net_security',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše: služba běží, ale z jiného počítače se k ní nikdo nedostane.',
            'student' => 'Vyjmenují možné příčiny.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Naslouchání (listener) ≠ dostupnost',
            'teacher' => 'Ukáže vrstvy: proces → adresa → firewall → trasa.',
            'student' => 'Sepíšou tabulku služeb (služba, port, kdo smí).',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'ss a adresy',
            'teacher' => 'Předvede ss -tln a konfiguraci listen v nginx.',
            'student' => 'Určí porty a adresy služeb v simulátoru.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 57,
            'phase' => 'Pravidlo firewallu',
            'teacher' => 'Ukáže pravidlo „jen potřebný zdroj a služba“.',
            'student' => 'Navrhnou pravidla pro web a SSH bez „povolit vše“.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 78,
            'phase' => 'Pozitivní a negativní test',
            'teacher' => 'Zdůrazní test i zakázané cesty.',
            'student' => 'Otestují povolené a zakázané porty přes nc.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Shrnutí a exit ticket',
            'teacher' => 'Připomene návratovou cestu při změně firewallu.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Sepiš tabulku služeb serveru: služba, port, protokol, kdo k ní smí (web 80 pro všechny, SSH 22 jen správa).',
            'output' => 'Tabulka služeb se 4 řádky.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Spusť ss -tln a zapiš, na jakých adresách a portech poslouchají služby; ověř řádek listen v /etc/nginx/sites-enabled/default.',
            'output' => '0.0.0.0:22 a 0.0.0.0:80; „listen 80 default_server;“.',
            'time' => '17 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
              1 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:22',
              ),
              2 => 
              array (
                'cmd' => 'grep -n listen /etc/nginx/sites-enabled/default',
                'expect' => 'listen 80 default_server;',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Navrhni pravidla firewallu pro web a SSH tak, aby nevzniklo pravidlo „povolit vše odkudkoli“.',
            'output' => 'Dvě až tři pravidla (zdroj, port, akce) + výchozí zákaz.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Proveď pozitivní test (nc -zv 10.0.0.10 80) a negativní test (nc -zv 10.0.0.10 443) a zapiš, co každý dokazuje.',
            'output' => 'Port 80 succeeded, port 443 Connection refused.',
            'time' => '21 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 80',
                'expect' => 'succeeded',
              ),
              1 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 443',
                'expect' => 'Connection refused',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku „adresa naslouchání → kdo se připojí“ (127.0.0.1 / 0.0.0.0 / konkrétní IP).',
          'standard' => 'Tabulka služeb, ss, pravidla a testy podle zadání.',
          'challenge' => 'Popíše, jak by změnu firewallu na vzdáleném serveru provedl bez rizika, že se zamkne venku.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Karty adres: učitel ukáže 127.0.0.1:8080, třída řekne, kdo se připojí.',
            1 => 'Kontrola pravidel: najdi ve sousedově návrhu „povolit vše“.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Čtení naslouchání',
              'levels' => 
              array (
                0 => 'Nepřečte ss.',
                1 => 'Jen porty.',
                2 => 'Porty i adresy a jejich význam.',
                3 => 'Propojí s konfigurací služby.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Pravidla',
              'levels' => 
              array (
                0 => 'Povolit vše.',
                1 => 'Příliš široká.',
                2 => 'Jen potřebný zdroj a služba + výchozí zákaz.',
                3 => 'Plán návratu při chybě.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Ověření',
              'levels' => 
              array (
                0 => 'Bez testu.',
                1 => 'Jen pozitivní test.',
                2 => 'Pozitivní i negativní test.',
                3 => 'Testy ze správného zdroje se zdůvodněním.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_services',
          'competence_label' => 'Služby a porty',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Služba poslouchá jen na 127.0.0.1:8080. Co čekáš při pokusu z jiného počítače?',
              'options' => 
              array (
                0 => 'Spojení projde, když to firewall dovolí.',
                1 => 'Služba nebude přes síťové rozhraní dostupná, i když firewall port povoluje.',
                2 => 'DNS adresu automaticky změní.',
              ),
              'correct' => 1,
              'explanation' => '127.0.0.1 je jen lokální smyčka.',
            ),
            1 => 
            array (
              'question' => 'Co znamená v ss -tln adresa 0.0.0.0:80?',
              'options' => 
              array (
                0 => 'Služba poslouchá na portu 80 na všech adresách IPv4 počítače.',
                1 => 'Služba je vypnutá.',
                2 => 'Port 80 je zablokovaný.',
              ),
              'correct' => 0,
              'explanation' => '0.0.0.0 = všechna rozhraní IPv4.',
            ),
            2 => 
            array (
              'question' => 'Proč testovat i port, který má být zakázaný?',
              'options' => 
              array (
                0 => 'Aby se firewall zahřál.',
                1 => 'Protože pozitivní test nestačí na nic.',
                2 => 'Abys ověřil, že pravidlo opravdu blokuje, co blokovat má.',
              ),
              'correct' => 2,
              'explanation' => 'Negativní test dokazuje zákaz.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: nakresli diagram „klient → firewall → adresa naslouchání → proces“ pro webový server.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Firewall měníme jen v labu; na skutečném serveru vždy s přístupem přes konzoli a plánem návratu.',
          1 => 'Neskenujeme porty cizích počítačů ani školní sítě.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Používej pravidla jen v labu; nedělej změny na produkčním školním serveru.',
          1 => 'Otázka do třídy: Kdo všechno se dostane ke službě na 0.0.0.0?',
          2 => 'Tempo: simulátor nemá příkaz firewallu – pravidla navrhujeme na papír, testy děláme v simulátoru.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2 a 4 v Linux Labu (výstupy v zadání), úkoly 1 a 3 na papír.',
          1 => 'Plán B offline: karty adres a pravidel a vytištěný výstup ss.',
        ),
        'glossary' => 
        array (
          0 => 'bind',
          1 => 'listener',
          2 => 'firewall',
        ),
        '_file' => 'lesson_content_v72_3a_b.php',
      ),
      16 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 16 · Webová služba v Linuxu: od procesu k HTTP',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím ověřit webovou službu krok za krokem – proces, naslouchání, síť, DNS a odpověď HTTP – a sepsat z toho pětikrokový postup (runbook).',
          'success_criteria' => 
          array (
            0 => 'Projdu řetěz proces → port → síť → DNS → HTTP a u každého kroku mám důkaz.',
            1 => 'Rozliším lokální a vzdálený test a vím, co znamená rozdíl mezi nimi.',
            2 => 'Sepíšu runbook o 5 krocích, podle kterého ověří službu i spolužák.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_services',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'lnx_services',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže hlášku „web nejde“ od uživatele.',
            'student' => 'Vyjmenují, co všechno musí fungovat, aby web šel.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Řetěz služby',
            'teacher' => 'Nakreslí řetěz proces → port → síť → DNS → HTTP.',
            'student' => 'Ke každému článku přiřadí příkaz.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 40,
            'phase' => 'Lokální zdraví',
            'teacher' => 'Předvede systemctl status, ss a curl na localhost.',
            'student' => 'Ověří službu lokálně a zapíší stav HTTP.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 58,
            'phase' => 'Vzdálená cesta a DNS',
            'teacher' => 'Ukáže test přes jméno a adresu.',
            'student' => 'Ověří DNS a HTTP přes jméno intranet.skola.test.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 75,
            'phase' => 'Důkaz z HTTP',
            'teacher' => 'Vysvětlí, co dokazuje 502 u reverzní proxy (reverse proxy).',
            'student' => 'Rozeberou tři cvičné odpovědi HTTP.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Runbook a exit ticket',
            'teacher' => 'Zadá runbook o 5 krocích.',
            'student' => 'Sepíšou runbook a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Ověř lokálně: běží služba (systemctl status nginx), poslouchá port 80 (ss -tln) a odpovídá HTTP (curl -I http://localhost)?',
            'output' => 'active (running), 0.0.0.0:80, „HTTP/1.1 200 OK“ a „Server: nginx/1.22.1“.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'active (running)',
              ),
              1 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => 'Server: nginx/1.22.1',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Ověř cestu přes jméno: dig +short intranet.skola.test a curl -I http://intranet.skola.test.',
            'output' => '10.0.0.10 a „HTTP/1.1 200 OK“.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig +short intranet.skola.test',
                'expect' => '10.0.0.10',
              ),
              1 => 
              array (
                'cmd' => 'curl -I http://intranet.skola.test',
                'expect' => 'HTTP/1.1 200 OK',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Rozeber tři cvičné odpovědi (200, 404, 502 od reverzní proxy) a u každé napiš, co dokazuje a kde hledat dál.',
            'output' => 'Tabulka stav → co dokazuje → další krok.',
            'time' => '17 min',
          ),
          3 => 
          array (
            'text' => 'Sepiš runbook o 5 krocích „ověření webové služby“ od procesu po klienta, s příkazem a očekávaným výsledkem u každého kroku.',
            'output' => 'Runbook o 5 krocích.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kostru runbooku s nadpisy kroků a doplňuje příkazy a výsledky.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Doplní do runbooku větev „lokálně funguje, vzdáleně ne“ s testy navázání na adresu a firewallu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Řetěz na tabuli: učitel škrtne článek, třída řekne, jaký symptom uvidí uživatel.',
            1 => 'Runbook ve dvojici: spolužák podle něj projde službu bez dotazů?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Řetěz služby',
              'levels' => 
              array (
                0 => 'Náhodné testy.',
                1 => 'Část řetězu.',
                2 => 'Celý řetěz s důkazy.',
                3 => 'Najde první chybějící článek v incidentu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Lokální vs. vzdálený test',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Jen lokální test.',
                2 => 'Oba testy a význam rozdílu.',
                3 => 'Navrhne testy pro větev „vzdáleně nejde“.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Runbook',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Kroky bez výsledků.',
                2 => '5 kroků s příkazem a výsledkem.',
                3 => 'Použitelný spolužákem bez dotazů.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_services',
          'competence_label' => 'Služby a porty',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'curl -I http://localhost vrací 200 OK, ale z jiného počítače se web nenačte. Kde hledáš nejdřív?',
              'options' => 
              array (
                0 => 'V adrese, na které služba poslouchá, ve firewallu a trase.',
                1 => 'V obsahu stránky.',
                2 => 'V oprávněních souboru index.html.',
              ),
              'correct' => 0,
              'explanation' => 'Lokálně funguje – problém je mezi klientem a službou.',
            ),
            1 => 
            array (
              'question' => 'Reverzní proxy vrací 502. Co to dokazuje?',
              'options' => 
              array (
                0 => 'DNS nefunguje.',
                1 => 'Klient nemá IP adresu.',
                2 => 'Proxy je dosažitelná, ale nedostala platnou odpověď od služby za ní.',
              ),
              'correct' => 2,
              'explanation' => '502 = problém mezi proxy a upstreamem.',
            ),
            2 => 
            array (
              'question' => 'K čemu je runbook?',
              'options' => 
              array (
                0 => 'K zálohování serveru.',
                1 => 'Aby postup ověření nebo opravy zvládl kdokoli z týmu stejně a krok za krokem.',
                2 => 'K vypnutí monitoringu.',
              ),
              'correct' => 1,
              'explanation' => 'Runbook = opakovatelný postup.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: dopiš do runbooku jeden krok „co dělat, když krok 3 selže“.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Webovou službu zkoušíme v simulátoru nebo připraveném labu; nezávisíme na veřejném DNS.',
          1 => 'Neprovádíme testy proti cizím serverům.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Může být simulované prostředí nebo předpřipravený lab; nezávislé na veřejném DNS.',
          1 => 'Otázka do třídy: Který článek řetězu ověřuje tenhle příkaz?',
          2 => 'Tempo: runbook je hlavní výstup – nech na něj aspoň 15 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.',
          1 => 'Plán B offline: řetěz služby z kartiček a runbook na papír.',
        ),
        'glossary' => 
        array (
          0 => 'runbook',
          1 => 'reverse-proxy',
          2 => 'upstream',
        ),
        '_file' => 'lesson_content_v72_3a_b.php',
      ),
    ),
    'days' => 
    array (
    ),
    'files' => 
    array (
      0 => 'lesson_content_v72_1a_a.php',
      1 => 'lesson_content_v72_1a_b.php',
      2 => 'lesson_content_v72_2a_a.php',
      3 => 'lesson_content_v72_2a_b.php',
      4 => 'lesson_content_v72_3a_a.php',
      5 => 'lesson_content_v72_3a_b.php',
      6 => 'lesson_content_v72_4a_a.php',
      7 => 'lesson_content_v72_4a_b.php',
    ),
  ),
  'sig' => 'a91d3ceb5c7f0a2bf8dc3e13dec665db2f11bca6',
  'hash' => '5f5c1e10c2f18275fc1e2539d421c0d23a117e3049c3b66059d0e748d74a418b',
  'built_at' => '2026-10-08T07:14:26+02:00',
);
