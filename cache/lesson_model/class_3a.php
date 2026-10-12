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
      17 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 17 · Shell a cron: malá automatizace s logem',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím napsat krátký skript zálohy, který zapisuje do logu, končí srozumitelným návratovým kódem, a naplánovat ho v cronu se správným zápisem času.',
          'success_criteria' => 
          array (
            0 => 'Skript zapisuje začátek a konec práce do souboru logu.',
            1 => 'Umím přečíst návratový kód ($?) a vysvětlím, co znamená 0 a nenulová hodnota.',
            2 => 'Zapíšu cron výraz pro „každý den ve 2:30“ a vysvětlím všech pět polí.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_automation',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: tichá záloha',
            'teacher' => 'Popíše zálohu, která měsíc „běžela“, ale nic nezálohovala, protože nikdo neviděl chybu.',
            'student' => 'Navrhnou, co by chybu odhalilo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Návratový kód',
            'teacher' => 'Ukáže $? po úspěšném a neúspěšném příkazu.',
            'student' => 'Vyzkoušejí návratové kódy v Linux Labu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Skript s logem',
            'teacher' => 'Předvede zápis do logu přes >> a archiv tar s datem v názvu.',
            'student' => 'Napíší skript zálohy s logem.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 62,
            'phase' => 'Cron',
            'teacher' => 'Rozebere /etc/crontab a pět polí času.',
            'student' => 'Zapíší cron výrazy pro tři situace.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Bezpečné selhání',
            'teacher' => 'Ukáže kontrolu existence adresáře před zálohou.',
            'student' => 'Doplní do skriptu kontrolu a hlášku do logu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: automatizace bez logu je slepá.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spusť jeden úspěšný a jeden neúspěšný příkaz a po každém vypiš návratový kód příkazem echo $?.',
            'output' => 'Po false je kód 1, po ls neexistujícího adresáře 2, po úspěchu 0.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'false; echo $?',
                'expect' => '1',
              ),
              1 => 
              array (
                'cmd' => 'ls /neexistuje; echo $?',
                'expect' => 'No such file or directory',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Vytvoř archiv adresáře data s datem v názvu a zapiš začátek a konec zálohy do souboru zaloha.log.',
            'output' => 'Soubor zaloha-RRRR-MM-DD.tar.gz a log se dvěma řádky.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'mkdir -p data; echo a > data/x.txt; tar -czf zaloha-$(date +%F).tar.gz data; ls',
                'expect' => '.tar.gz',
              ),
              1 => 
              array (
                'cmd' => 'echo start >> zaloha.log; echo konec >> zaloha.log; cat zaloha.log',
                'expect' => 'konec',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Přečti systémový /etc/crontab, vysvětli pět polí času a zapiš cron výrazy pro: každý den ve 2:30, každou neděli ve 23:00 a každých 15 minut.',
            'output' => '30 2 * * *, 0 23 * * 0 a */15 * * * * s vysvětlením polí.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'cat /etc/crontab',
                'expect' => 'm h dom mon dow user',
              ),
              1 => 
              array (
                'cmd' => 'crontab -l',
                'expect' => 'no crontab for student',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Doplň do skriptu kontrolu, že zdrojový adresář existuje; když chybí, zapiš do logu chybu a skonči nenulovým kódem.',
            'output' => 'Řádek typu [ -d data ] || { echo "chybí data" >> zaloha.log; exit 1; } a ověření.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => '[ -d /zaloha ] || echo \'chybi adresar\'',
                'expect' => 'chybi adresar',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kostru skriptu s komentáři a tabulku polí cronu; doplňuje příkazy.',
          'standard' => 'Návratové kódy, skript s logem a archivem, cron výrazy a kontrola adresáře.',
          'challenge' => 'Navrhne rotaci záloh (ponechat posledních 7 archivů) a popíše, jak ji ověří.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: echo $? vypsalo 2 – uspěl příkaz?',
            1 => 'Kartičky: učitel řekne čas, dvojice zapíše cron výraz.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Návratový kód',
              'levels' => 
              array (
                0 => 'Nezná.',
                1 => 'Ví, že 0 je úspěch.',
                2 => 'Čte $? a používá ho ve skriptu.',
                3 => 'Vysvětlí, proč skript končí nenulovým kódem.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Skript s logem',
              'levels' => 
              array (
                0 => 'Bez logu.',
                1 => 'Log přepisuje (>).',
                2 => 'Log připisuje (>>) začátek i konec.',
                3 => 'Log s datem a výsledkem každého kroku.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Cron',
              'levels' => 
              array (
                0 => 'Nezná pole.',
                1 => 'Pole zná, výrazy s chybou.',
                2 => 'Tři správné výrazy.',
                3 => 'Vysvětlí, jak ověří, že úloha opravdu běží.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_automation',
          'competence_label' => 'Linux: automatizace skriptem a cronem',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který cron výraz spustí úlohu každý den ve 2:30?',
              'options' => 
              array (
                0 => '2 30 * * *',
                1 => '30 2 * * *',
                2 => '* * 2 30 *',
              ),
              'correct' => 1,
              'explanation' => 'Pořadí polí: minuta, hodina, den v měsíci, měsíc, den v týdnu.',
            ),
            1 => 
            array (
              'question' => 'Co znamená, když echo $? po příkazu vypíše 0?',
              'options' => 
              array (
                0 => 'Předchozí příkaz skončil úspěšně.',
                1 => 'Příkaz nic nevypsal.',
                2 => 'Příkaz skončil chybou číslo 0.',
              ),
              'correct' => 0,
              'explanation' => 'Návratový kód 0 = úspěch, nenulový = chyba.',
            ),
            2 => 
            array (
              'question' => 'Proč skript zálohy zapisuje do logu pomocí >> a ne >?',
              'options' => 
              array (
                0 => '>> je rychlejší.',
                1 => '> nefunguje v cronu.',
                2 => '>> připisuje na konec, > by log pokaždé přepsal.',
              ),
              'correct' => 2,
              'explanation' => 'Historie logu zůstane zachovaná.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zapiš cron výraz pro úlohu každé pondělí v 7:15 a ověř ho v nápovědě crontab v Linux Labu.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Skripty a cron zkoušíme jen v simulátoru Linux Labu, ne na školních počítačích.',
          1 => 'Do skriptů ani logů nepíšeme hesla; zálohujeme jen ukázková data.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: zaměněné pořadí minuty a hodiny v cronu – nech žáky číst pole nahlas.',
          1 => 'Otázka do třídy: Jak poznáš ráno, že noční záloha opravdu proběhla?',
          2 => 'Tempo: simulátor nepodporuje řídicí struktury ve skriptech – podmínku zapisuj jako [ … ] || …; if/else ukaž na tabuli.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu podle zadání (výstupy jsou v zadání), úkol 4 na papír.',
          1 => 'Plán B offline: tabulka polí cronu a kartičky s časy; skript zálohy napsaný do sešitu.',
        ),
        'glossary' => 
        array (
          0 => 'shell',
          1 => 'retention',
        ),
        '_file' => 'lesson_content_v76_3a_a.php',
      ),
      18 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 18 · Závěrečný incident: nefunguje služba – Linux i síť',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vyřešit incident „web nejde“ postupem od důkazu k nápravě: stav služby, naslouchající port, konfigurace, oprava a ověření, a sepsat krátký záznam incidentu.',
          'success_criteria' => 
          array (
            0 => 'Každý krok podložím výstupem příkazu (systemctl, ss, curl).',
            1 => 'Před restartem ověřím konfiguraci (nginx -t).',
            2 => 'Záznam incidentu má příznak, příčinu, opravu, ověření a prevenci.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_services',
            'level' => 3,
          ),
          1 => 
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
            'phase' => 'Hlášení incidentu',
            'teacher' => 'Přečte hlášení „intranet nejde“ a rozdá role ve dvojici (řešitel, zapisovatel).',
            'student' => 'Zapíší příznak a čas.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 30,
            'phase' => 'Sběr důkazů',
            'teacher' => 'Připomene pořadí: služba → port → konfigurace → aplikace.',
            'student' => 'Zjistí stav služby a naslouchající porty.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 30,
            'to' => 50,
            'phase' => 'Příčina a oprava',
            'teacher' => 'Hlídá, aby oprava byla nejmenší možná.',
            'student' => 'Ověří konfiguraci a službu spustí.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 65,
            'phase' => 'Ověření',
            'teacher' => 'Vyžaduje ověření z pohledu uživatele (curl).',
            'student' => 'Ověří odpověď webu a zkontrolují log.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Záznam incidentu',
            'teacher' => 'Ukáže osnovu záznamu bez hledání viníka.',
            'student' => 'Sepíší záznam incidentu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne postup od důkazu k nápravě.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'V simulátoru zastav nginx a jako řešitel zjisti stav služby a odpověď webu na localhost.',
            'output' => 'systemctl is-active vrátí inactive a curl hlásí, že se nemůže připojit.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx',
                'expect' => 'inactive',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; curl -I http://localhost',
                'expect' => 'Couldn\'t connect to server',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zjisti, které porty server poslouchá, a ověř konfiguraci webového serveru dřív, než ho spustíš.',
            'output' => 'ss -tln ukáže porty 22 a 80, nginx -t hlásí syntax is ok.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:22',
              ),
              1 => 
              array (
                'cmd' => 'sudo nginx -t',
                'expect' => 'syntax is ok',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Spusť službu a ověř opravu z pohledu uživatele i v logu služby.',
            'output' => 'curl -I vrací HTTP/1.1 200 OK a journal obsahuje Started nginx.service.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; curl -I http://localhost',
                'expect' => 'HTTP/1.1 200 OK',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; journalctl -u nginx -n 5',
                'expect' => 'Started nginx.service',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Sepiš záznam incidentu: příznak, časová osa, příčina, oprava, ověření a jedno opatření prevence – bez jmen a hledání viníka.',
            'output' => 'Záznam incidentu na půl strany.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu s pořadím příkazů (systemctl → ss → nginx -t → curl) a šablonu záznamu.',
          'standard' => 'Celý incident podle postupu a záznam.',
          'challenge' => 'Navrhne kontrolu (monitoring), která by výpadek ohlásila dřív než uživatel.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Zapisovatel ukáže, který příkaz dokládá každý krok.',
            1 => 'Učitel se zeptá: proč nginx -t před startem?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Důkazy',
              'levels' => 
              array (
                0 => 'Hádá.',
                1 => 'Jeden důkaz.',
                2 => 'Každý krok podložený výstupem.',
                3 => 'Důkazy seřazené od uživatele ke službě.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Oprava',
              'levels' => 
              array (
                0 => 'Restartuje naslepo.',
                1 => 'Restart bez kontroly konfigurace.',
                2 => 'nginx -t, start a ověření.',
                3 => 'Nejmenší oprava se zdůvodněním.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Záznam incidentu',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen oprava.',
                2 => 'Příznak, příčina, oprava, ověření, prevence.',
                3 => 'Časová osa a měřitelné opatření.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_diagnose',
          'competence_label' => 'Diagnostika síťového problému',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co uděláš před restartem webového serveru po změně konfigurace?',
              'options' => 
              array (
                0 => 'Smažu log, ať je přehledný.',
                1 => 'Restartuji celý server.',
                2 => 'Ověřím konfiguraci příkazem nginx -t.',
              ),
              'correct' => 2,
              'explanation' => 'Chybná konfigurace by službu po restartu shodila.',
            ),
            1 => 
            array (
              'question' => 'curl -I http://localhost hlásí „Couldn’t connect to server“. Co ověříš jako první?',
              'options' => 
              array (
                0 => 'Jestli služba běží a poslouchá na portu 80.',
                1 => 'Jestli funguje DNS na internetu.',
                2 => 'Jestli má uživatel správné heslo.',
              ),
              'correct' => 0,
              'explanation' => 'Spojení odmítnuto = nikdo neposlouchá.',
            ),
            2 => 
            array (
              'question' => 'Co do záznamu incidentu nepatří?',
              'options' => 
              array (
                0 => 'Ověření opravy.',
                1 => 'Jméno člověka, který chybu způsobil.',
                2 => 'Opatření prevence.',
              ),
              'correct' => 1,
              'explanation' => 'Záznam hledá příčinu v systému, ne viníka.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu vyřeš jednu úroveň s nefunkční službou a zapiš důkaz, který tě dovedl k příčině.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Služby zastavujeme a spouštíme jen v simulátoru Linux Labu.',
          1 => 'Záznam incidentu neobsahuje jména, hesla ani skutečné adresy.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: restart jako první krok – nech žáky nejdřív zapsat důkaz.',
          1 => 'Otázka do třídy: Který výstup by přesvědčil kolegu, že je opraveno?',
          2 => 'Tempo: když incident trvá, záznam dokončete jako domácí dobrovolnou část, ověření ale v hodině.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: dvojice plní úkoly 1–3 v Linux Labu (výstupy v zadání), úkol 4 na papír.',
          1 => 'Plán B offline: karty s výpisy systemctl, ss a curl; žáci podle nich sestaví postup a záznam.',
        ),
        'glossary' => 
        array (
          0 => 'listener',
          1 => 'runbook',
        ),
        '_file' => 'lesson_content_v76_3a_a.php',
      ),
      19 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 19 · Incident souborového systému: kde zmizelo místo?',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím zjistit zaplnění disku, najít největší adresáře a soubory a rozlišit, jestli problém způsobilo místo, nebo počet souborů (inody).',
          'success_criteria' => 
          array (
            0 => 'Z výpisu df -h přečtu zaplnění a přípojný bod oddílu.',
            1 => 'Příkazy du a find najdu, co zabírá nejvíc místa v /var/log.',
            2 => 'Vysvětlím, co je inode a proč může dojít, i když místo zbývá.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_navigation',
            'level' => 3,
          ),
          1 => 
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
            'phase' => 'Start: „No space left on device“',
            'teacher' => 'Ukáže hlášku a zeptá se, co všechno mohlo dojít.',
            'student' => 'Navrhnou hypotézy.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'df a lsblk',
            'teacher' => 'Vysvětlí oddíly, přípojné body (mount point) a sloupce df.',
            'student' => 'Přečtou zaplnění a najdou, kde je připojený kořen.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'du a find',
            'teacher' => 'Předvede hledání velkých adresářů a souborů.',
            'student' => 'Najdou největší položky v /var/log.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 60,
            'phase' => 'Inody',
            'teacher' => 'Vysvětlí inode a ukáže ho ve výpisu stat.',
            'student' => 'Najdou číslo inode souboru a popíší, kdy dojdou.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Bezpečný úklid',
            'teacher' => 'Ukáže, proč se nemaže log služby, která do něj právě píše.',
            'student' => 'Navrhnou bezpečný úklid a retenci logů.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne postup: df → du → find → bezpečný úklid.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přečti zaplnění disků příkazem df -h a v lsblk najdi, který oddíl je připojený jako kořen /.',
            'output' => '/dev/sda1 připojený na /, zaplnění v procentech a volné místo.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'df -h',
                'expect' => '/dev/sda1',
              ),
              1 => 
              array (
                'cmd' => 'lsblk',
                'expect' => 'sda1',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zjisti velikost položek v /var/log a najdi všechny soubory s příponou .log.',
            'output' => 'du ukáže /var/log/nginx jako největší; find vypíše čtyři soubory .log.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'du -sh /var/log/*',
                'expect' => '/var/log/nginx',
              ),
              1 => 
              array (
                'cmd' => 'find /var/log -type f -name \'*.log\'',
                'expect' => '/var/log/nginx/error.log',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Najdi ve výpisu stat číslo inode souboru /etc/hosts a vysvětli, co by se stalo, kdyby inody došly.',
            'output' => 'Řádek Inode: 120501 a vysvětlení: nový soubor nejde vytvořit, i když místo zbývá.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'stat /etc/hosts',
                'expect' => 'Inode: 120501',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Navrhni bezpečný postup úklidu plného /var/log: co smažeš, co zkomprimuješ, jak nastavíš retenci a jak ověříš, že služba dál zapisuje.',
            'output' => 'Postup ve 4–5 krocích s ověřením.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane popsaný výpis df -h se zvýrazněnými sloupci a pořadí příkazů.',
          'standard' => 'Zaplnění, hledání velkých položek, inody a návrh úklidu.',
          'challenge' => 'Vysvětlí, proč po smazání otevřeného logu místo nepřibude, dokud služba soubor nezavře.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže řádek df a třída řekne zaplnění a přípojný bod.',
            1 => 'Dvojice: místo, nebo inody? (učitel popisuje situace)',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Zaplnění disku',
              'levels' => 
              array (
                0 => 'Nečte df.',
                1 => 'Přečte procenta.',
                2 => 'Oddíl, přípojný bod, zaplnění.',
                3 => 'Spojí s lsblk a odhadne růst.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Hledání příčiny',
              'levels' => 
              array (
                0 => 'Nehledá.',
                1 => 'Jen du.',
                2 => 'du i find s filtrem.',
                3 => 'Najde příčinu i dobu vzniku.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Bezpečný úklid',
              'levels' => 
              array (
                0 => 'Smaže vše.',
                1 => 'Maže bez ověření.',
                2 => 'Komprese, retence a ověření.',
                3 => 'Navrhne prevenci (rotace, upozornění).',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_navigation',
          'competence_label' => 'Linux: shell a práce se soubory',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Disk hlásí „No space left on device“, ale df -h ukazuje 40 % volného místa. Co ověříš?',
              'options' => 
              array (
                0 => 'Rychlost disku.',
                1 => 'Jestli nedošly inody (df -i).',
                2 => 'Jestli je zapnutý firewall.',
              ),
              'correct' => 1,
              'explanation' => 'Každý soubor potřebuje inode; může jich dojít dřív než místa.',
            ),
            1 => 
            array (
              'question' => 'Kterým příkazem zjistíš, kolik místa zabírají jednotlivé položky v /var/log?',
              'options' => 
              array (
                0 => 'du -sh /var/log/*',
                1 => 'ls /var/log',
                2 => 'pwd /var/log',
              ),
              'correct' => 0,
              'explanation' => 'du sčítá velikost obsahu adresářů.',
            ),
            2 => 
            array (
              'question' => 'Proč nemazat log, do kterého služba právě zapisuje?',
              'options' => 
              array (
                0 => 'Protože log je vždy prázdný.',
                1 => 'Protože to zakazuje df.',
                2 => 'Protože místo se uvolní až po zavření souboru službou a ztratíš historii.',
              ),
              'correct' => 2,
              'explanation' => 'Bezpečnější je komprese a rotace logů.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu najdi příkazem find všechny soubory větší než 1 kB v /var/log a zapiš, co v nich je.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Úklid a mazání zkoušíme jen v simulátoru; na skutečném systému se nejdřív zálohuje.',
          1 => 'Logy mohou obsahovat osobní údaje – nesdílíme je mimo hodinu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: rm -rf /var/log/* jako „oprava“ – zdůrazni ztrátu důkazů i historie.',
          1 => 'Otázka do třídy: Jak se dozvíte o plném disku dřív, než ho nahlásí uživatel?',
          2 => 'Tempo: inody stačí vysvětlit na jednom výpisu stat; víc času dej návrhu úklidu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), úkol 4 písemně.',
          1 => 'Plán B offline: vytištěné výpisy df, du a stat s otázkami.',
        ),
        'glossary' => 
        array (
          0 => 'inode',
          1 => 'mount-point',
        ),
        '_file' => 'lesson_content_v76_3a_a.php',
      ),
      20 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 20 · Služby a jejich závislosti v systemd',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vypsat služby systému, zjistit, které selhaly, rozlišit stav „běží“ a „povolena po startu“ a vysvětlit, proč služba závisí na síti nebo jiné službě.',
          'success_criteria' => 
          array (
            0 => 'Z výpisu list-units najdu běžící služby a v --failed selhané.',
            1 => 'Rozliším is-active (běží teď) a is-enabled (spustí se po startu).',
            2 => 'Ke službě nginx popíšu, na čem závisí (síť, konfigurace, port).',
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
            'phase' => 'Start: po restartu nejede',
            'teacher' => 'Popíše situaci: web běžel, po restartu serveru ne.',
            'student' => 'Hádají, co se změnilo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Seznam služeb',
            'teacher' => 'Předvede systemctl list-units a --failed.',
            'student' => 'Vypíší služby a zjistí, která selhala.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Active vs. enabled',
            'teacher' => 'Vysvětlí rozdíl mezi stavem teď a po startu.',
            'student' => 'Ověří is-active a is-enabled u nginx a ssh.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Závislosti',
            'teacher' => 'Nakreslí graf: síť → nginx → web; ssh → správa.',
            'student' => 'Nakreslí závislosti služeb svého serveru.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Scénář po restartu',
            'teacher' => 'Zadá scénář: služba je active, ale disabled.',
            'student' => 'Navrhnou opravu a ověření.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: běží ≠ spustí se.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vypiš služby systému a zjisti, jestli některá selhala.',
            'output' => 'list-units ukáže cron, nginx, ssh a journald; --failed hlásí 0 loaded units listed.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl list-units --type=service',
                'expect' => 'nginx.service',
              ),
              1 => 
              array (
                'cmd' => 'systemctl --failed',
                'expect' => '0 loaded units listed',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Ověř u nginx, jestli běží a jestli se spustí po startu; potom službu zastav a stav ověř znovu.',
            'output' => 'is-enabled = enabled; po stop je is-active inactive, ale enabled zůstává.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl is-enabled nginx',
                'expect' => 'enabled',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx',
                'expect' => 'inactive',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Přečti stav služby ssh a najdi v něm hlavní proces (Main PID) a řádek, který říká, že je povolená po startu.',
            'output' => 'Main PID: 180 (sshd) a Loaded: … enabled.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl status ssh',
                'expect' => 'Main PID: 180',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Nakresli graf závislostí služeb serveru (síť, nginx, ssh, cron, journald) a popiš scénář „po restartu web nejede“ s opravou a ověřením.',
            'output' => 'Graf závislostí a postup: is-enabled → enable → restart serveru → ověření.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku stavů (active/inactive, enabled/disabled) s příklady.',
          'standard' => 'Seznam služeb, stavy, status a graf závislostí.',
          'challenge' => 'Vysvětlí, co je drop-in soubor a proč se úprava jednotky dělá raději v něm než v originálu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže dvojici stavů (active + disabled), třída řekne, co se stane po restartu.',
            1 => 'Dvojice: kde ve výpisu status je informace o povolení?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Přehled služeb',
              'levels' => 
              array (
                0 => 'Nevypíše.',
                1 => 'Vypíše bez porozumění.',
                2 => 'Najde běžící i selhané služby.',
                3 => 'Pozná neobvyklou službu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Stav služby',
              'levels' => 
              array (
                0 => 'Zaměňuje.',
                1 => 'Zná jen active.',
                2 => 'Rozliší active a enabled.',
                3 => 'Navrhne ověření po restartu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Závislosti',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Seznam bez vazeb.',
                2 => 'Graf s vazbami.',
                3 => 'Odvodí z grafu pořadí kontroly.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_services',
          'competence_label' => 'Linux: služby a systémové logy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Služba je active, ale disabled. Co se stane po restartu serveru?',
              'options' => 
              array (
                0 => 'Spustí se dvakrát.',
                1 => 'Nespustí se sama.',
                2 => 'Smaže se.',
              ),
              'correct' => 1,
              'explanation' => 'disabled = po startu se automaticky nespustí.',
            ),
            1 => 
            array (
              'question' => 'Který příkaz ukáže služby, které selhaly?',
              'options' => 
              array (
                0 => 'systemctl --failed',
                1 => 'ls /failed',
                2 => 'ps aux --failed',
              ),
              'correct' => 0,
              'explanation' => 'systemctl --failed vypíše jednotky ve stavu failed.',
            ),
            2 => 
            array (
              'question' => 'Co znamená stav „enabled“ u služby?',
              'options' => 
              array (
                0 => 'Služba právě běží.',
                1 => 'Služba má chybu.',
                2 => 'Služba se spustí automaticky při startu systému.',
              ),
              'correct' => 2,
              'explanation' => 'enabled se týká startu, ne aktuálního běhu.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu zjisti stav služby cron (běží? povolená?) a zapiš oba výstupy.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Služby měníme jen v simulátoru Linux Labu.',
          1 => 'Na skutečném serveru se službám mění stav jen v domluveném okně změn.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „běží, tak je to v pořádku“ – po restartu může služba chybět, když není enabled.',
          1 => 'Otázka do třídy: Jak ověříte chování po restartu bez skutečného restartu produkce?',
          2 => 'Tempo: drop-in je jen pro výzvu; standard je active vs. enabled a graf.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), graf na papír.',
          1 => 'Plán B offline: kartičky služeb a šipky závislostí na lavici.',
        ),
        'glossary' => 
        array (
          0 => 'systemd-unit',
          1 => 'drop-in',
        ),
        '_file' => 'lesson_content_v76_3a_a.php',
      ),
      21 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 21 · Forenzní čtení logů: co se stalo a kdy',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím z logů služby a přístupového logu webu sestavit časovou osu události, spočítat odpovědi podle stavových kódů a oddělit fakt od domněnky.',
          'success_criteria' => 
          array (
            0 => 'Z journalctl přečtu, kdy se služba zastavila a znovu spustila.',
            1 => 'Z přístupového logu spočítám odpovědi podle stavového kódu (200, 403, 404).',
            2 => 'Časová osa obsahuje jen fakta s časem a zdrojem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_services',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'lnx_text',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: kdo za to může?',
            'teacher' => 'Přečte stížnost „web byl ráno pomalý a hlásil chyby“.',
            'student' => 'Zapíší, jaké důkazy by potřebovali.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 28,
            'phase' => 'Journal služby',
            'teacher' => 'Předvede journalctl -u a -n.',
            'student' => 'Najdou zastavení a start služby.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 28,
            'to' => 48,
            'phase' => 'Přístupový log',
            'teacher' => 'Rozebere řádek access logu a pole stavového kódu.',
            'student' => 'Spočítají odpovědi podle kódů.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 48,
            'to' => 62,
            'phase' => 'Podezřelé požadavky',
            'teacher' => 'Ukáže filtr na 403 a opakované adresy.',
            'student' => 'Najdou požadavky na /admin.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 82,
            'phase' => 'Časová osa',
            'teacher' => 'Ukáže sloupce čas – zdroj – fakt.',
            'student' => 'Sestaví časovou osu a oddělí domněnky.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: fakt má čas a zdroj.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zastav v simulátoru nginx a v journalu služby najdi záznamy o zastavení.',
            'output' => 'Řádky Stopping a Stopped nginx.service s časem.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; journalctl -u nginx -n 5',
                'expect' => 'Stopped nginx.service',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Spočítej odpovědi webu podle stavového kódu z přístupového logu (pole 9) pomocí awk, sort a uniq -c.',
            'output' => '15× 200, 6× 403, 9× 404.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo awk \'{print $9}\' /var/log/nginx/access.log | sort | uniq -c',
                'expect' => '9 404',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Vypiš první dva požadavky, na které server odpověděl 403, a zapiš, na jakou stránku mířily.',
            'output' => 'Požadavky GET /admin z adres 10.0.0.23 a 10.0.0.44.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep \' 403 \' /var/log/nginx/access.log | head -n 2',
                'expect' => 'GET /admin',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Sestav časovou osu ve sloupcích čas – zdroj – fakt a pod ni zvlášť napiš domněnky, které je třeba ověřit.',
            'output' => 'Časová osa s aspoň 5 fakty a seznam 2 domněnek.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane rozebraný řádek access logu s očíslovanými poli a hotový příkaz pro počítání kódů.',
          'standard' => 'Journal, počty kódů, filtr 403 a časová osa.',
          'challenge' => 'Najde, ze které adresy přišlo nejvíc chybových odpovědí (awk + sort + uniq -c).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže řádek logu, třída řekne čas, adresu, stránku a kód.',
            1 => 'Dvojice: je tvrzení fakt, nebo domněnka?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Čtení journalu',
              'levels' => 
              array (
                0 => 'Nečte.',
                1 => 'Najde jen poslední řádek.',
                2 => 'Najde zastavení i start s časem.',
                3 => 'Spojí s jinými logy.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Analýza access logu',
              'levels' => 
              array (
                0 => 'Nečte.',
                1 => 'Ruční počítání.',
                2 => 'awk + sort + uniq -c.',
                3 => 'Rozdělení podle adres i stránek.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Časová osa',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Fakta bez zdroje.',
                2 => 'Fakta s časem a zdrojem, domněnky zvlášť.',
                3 => 'Domněnky s návrhem ověření.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_text',
          'competence_label' => 'Linux: práce s textem v příkazové řádce',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co dělá roura awk "{print $9}" log | sort | uniq -c?',
              'options' => 
              array (
                0 => 'Smaže devátý řádek logu.',
                1 => 'Seřadí log podle času.',
                2 => 'Vypíše 9. pole každého řádku a spočítá, kolikrát se která hodnota opakuje.',
              ),
              'correct' => 2,
              'explanation' => 'uniq -c počítá stejné sousední řádky, proto se nejdřív řadí.',
            ),
            1 => 
            array (
              'question' => 'Které tvrzení je fakt z logu?',
              'options' => 
              array (
                0 => 'V 08:23:55 přišel z 10.0.0.23 požadavek GET /admin s odpovědí 403.',
                1 => 'Někdo se pokoušel web hacknout.',
                2 => 'Server je pomalý kvůli útoku.',
              ),
              'correct' => 0,
              'explanation' => 'Fakt má čas, zdroj a obsah; zbytek jsou domněnky.',
            ),
            2 => 
            array (
              'question' => 'Jak zobrazíš posledních 5 záznamů journalu služby nginx?',
              'options' => 
              array (
                0 => 'tail nginx',
                1 => 'journalctl -u nginx -n 5',
                2 => 'cat /nginx/5',
              ),
              'correct' => 1,
              'explanation' => '-u vybere jednotku, -n počet řádků.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu zjisti, kolik požadavků v access logu skončilo kódem 404 a na které stránky mířily.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Logy jsou ze simulátoru; adresy 10.0.0.x jsou laboratorní.',
          1 => 'Skutečné logy mohou obsahovat osobní údaje – čte je jen oprávněná osoba a nesdílí je.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: závěr „útok“ z jednoho 403 – veď k oddělení faktů a domněnek.',
          1 => 'Otázka do třídy: Co bychom ještě potřebovali vědět, abychom domněnku potvrdili?',
          2 => 'Tempo: časová osa je výstup hodiny; když awk trvá, dej hotový příkaz a soustřeď se na výklad.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), časová osa na papír.',
          1 => 'Plán B offline: vytištěný výřez logu a ruční sestavení časové osy.',
        ),
        'glossary' => 
        array (
          0 => 'journal',
          1 => 'severity',
        ),
        '_file' => 'lesson_content_v76_3a_a.php',
      ),
      22 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 22 · SSH klíče v praxi: vytvořit, chránit, ověřit',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vytvořit pár klíčů ed25519, zkontrolovat práva soukromého klíče, vysvětlit roli souborů authorized_keys a known_hosts a ověřit, že server nepovoluje přihlášení roota.',
          'success_criteria' => 
          array (
            0 => 'Soukromý klíč má práva 600 a nikdy ho nikomu neposílám.',
            1 => 'Rozliším authorized_keys (na serveru, veřejné klíče) a known_hosts (u klienta, otisky serverů).',
            2 => 'V konfiguraci SSH najdu PermitRootLogin a vysvětlím jeho hodnotu.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_ssh',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: heslo, nebo klíč?',
            'teacher' => 'Porovná přihlášení heslem a klíčem z pohledu útočníka.',
            'student' => 'Vyjmenují rizika hesel.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 28,
            'phase' => 'Vytvoření klíče',
            'teacher' => 'Předvede ssh-keygen -t ed25519.',
            'student' => 'Vytvoří pár klíčů a prohlédnou si veřejnou část.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 28,
            'to' => 45,
            'phase' => 'Práva a ochrana',
            'teacher' => 'Vysvětlí práva 600 a heslo ke klíči (passphrase).',
            'student' => 'Ověří práva souborů v ~/.ssh.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 62,
            'phase' => 'authorized_keys a known_hosts',
            'teacher' => 'Nakreslí, kde který soubor leží a co obsahuje.',
            'student' => 'Doplní schéma klient–server.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Konfigurace serveru',
            'teacher' => 'Ukáže sshd_config a port 22.',
            'student' => 'Ověří dostupnost SSH a nastavení PermitRootLogin.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: soukromý klíč zůstává u tebe.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vytvoř pár klíčů ed25519 a vypiš veřejnou část klíče.',
            'output' => 'Soubory id_ed25519 a id_ed25519.pub; veřejný klíč začíná ssh-ed25519.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N \'\'; cat ~/.ssh/id_ed25519.pub',
                'expect' => 'ssh-ed25519 AAAA',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Ověř práva souborů v ~/.ssh a vysvětli, proč soukromý klíč smí číst jen vlastník.',
            'output' => 'id_ed25519 má -rw------- (600), veřejný klíč -rw-r--r-- (644).',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N \'\'; ls -l ~/.ssh',
                'expect' => '-rw------- 1 student student',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Nakresli schéma klient–server a doplň, kde leží soukromý klíč, veřejný klíč v authorized_keys a otisk serveru v known_hosts.',
            'output' => 'Schéma se třemi soubory a šipkou, co se kam kopíruje.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Ověř, že na serveru 10.0.0.10 poslouchá SSH, a v konfiguraci najdi nastavení přihlášení roota.',
            'output' => 'nc hlásí succeeded na portu 22, sshd_config obsahuje PermitRootLogin no.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 22',
                'expect' => 'succeeded',
              ),
              1 => 
              array (
                'cmd' => 'sudo grep -n PermitRootLogin /etc/ssh/sshd_config',
                'expect' => 'PermitRootLogin no',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane schéma s prázdnými políčky a tabulku práv 600/644.',
          'standard' => 'Klíče, práva, schéma a kontrola konfigurace.',
          'challenge' => 'Vysvětlí, co se stane při změně otisku serveru v known_hosts a jak postupovat bezpečně.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: který soubor smíš poslat správci serveru?',
            1 => 'Dvojice: ukaž ve výpisu ls -l, kdo smí číst soukromý klíč.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Vytvoření klíče',
              'levels' => 
              array (
                0 => 'Nezvládne.',
                1 => 'Klíč bez porozumění.',
                2 => 'Pár klíčů a rozlišení částí.',
                3 => 'Vysvětlí volbu ed25519 a passphrase.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Ochrana klíče',
              'levels' => 
              array (
                0 => 'Posílá soukromý klíč.',
                1 => 'Práva nezkontroluje.',
                2 => 'Práva 600 ověřená.',
                3 => 'Navrhne postup při úniku klíče.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Konfigurace serveru',
              'levels' => 
              array (
                0 => 'Nezná.',
                1 => 'Najde port.',
                2 => 'Port i PermitRootLogin s vysvětlením.',
                3 => 'Navrhne další zpevnění.',
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
              'question' => 'Který soubor pošleš správci serveru, aby ti povolil přihlášení klíčem?',
              'options' => 
              array (
                0 => 'id_ed25519',
                1 => 'known_hosts',
                2 => 'id_ed25519.pub',
              ),
              'correct' => 2,
              'explanation' => 'Posílá se jen veřejná část; soukromý klíč zůstává u tebe.',
            ),
            1 => 
            array (
              'question' => 'Jaká práva má mít soukromý klíč?',
              'options' => 
              array (
                0 => '600 – číst a zapisovat jen vlastník.',
                1 => '777 – všichni všechno.',
                2 => '644 – číst může každý.',
              ),
              'correct' => 0,
              'explanation' => 'Soukromý klíč nesmí číst nikdo jiný.',
            ),
            2 => 
            array (
              'question' => 'Co obsahuje soubor known_hosts u klienta?',
              'options' => 
              array (
                0 => 'Hesla k serverům.',
                1 => 'Otisky serverů, ke kterým ses už připojil(a).',
                2 => 'Veřejné klíče uživatelů serveru.',
              ),
              'correct' => 1,
              'explanation' => 'Chrání před podvrženým serverem.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zapiš si postup, co uděláš, když ti unikne soukromý klíč (tři kroky).',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Klíče vytváříme jen v simulátoru; skutečné soukromé klíče nikdy nevkládáme do dokumentů, chatů ani AI nástrojů.',
          1 => 'Ke školním serverům se připojujeme jen s povolením správce.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: žák pošle soubor bez přípony .pub – zdůrazni, že soukromý klíč neopouští počítač.',
          1 => 'Otázka do třídy: Proč je přihlášení roota přes SSH zakázané?',
          2 => 'Tempo: navazuje na práva z L11 a SSH z L14 – připomeň je rychle, neopakuj.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1, 2 a 4 v Linux Labu (výstupy v zadání), schéma na papír.',
          1 => 'Plán B offline: schéma klient–server s kartičkami „soukromý / veřejný / otisk“.',
        ),
        'glossary' => 
        array (
          0 => 'authorized-keys',
          1 => 'known-hosts',
        ),
        '_file' => 'lesson_content_v76_3a_a.php',
      ),
      23 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 23 · Řetěz důkazů DNS: od jména k odpovědi webu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím ověřit cestu od doménového jména k odpovědi webu krok za krokem – záznam v /etc/hosts nebo DNS, adresa, port, HTTP – a u každého kroku ukázat důkaz.',
          'success_criteria' => 
          array (
            0 => 'Přeložím jméno na adresu dvěma nástroji (dig, host) a vysvětlím rozdíl odpovědi NOERROR a NXDOMAIN.',
            1 => 'Vím, že /etc/hosts má přednost před DNS, a umím ho přečíst.',
            2 => 'Řetěz důkazů má čtyři články: jméno → adresa → port → HTTP odpověď.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_dns_dhcp',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'net_diagnose',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: „je to DNS“',
            'teacher' => 'Ukáže hlášení „intranet nejde“ a tři různé příčiny se stejným příznakem.',
            'student' => 'Navrhnou, jak příčiny rozlišit.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Jméno na adresu',
            'teacher' => 'Předvede dig +short, host a sekci ANSWER.',
            'student' => 'Přeloží jméno intranetu na adresu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'NXDOMAIN a /etc/hosts',
            'teacher' => 'Ukáže neexistující jméno a statický záznam v /etc/hosts.',
            'student' => 'Porovnají odpovědi a najdou záznam v /etc/hosts.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 60,
            'phase' => 'Port a HTTP',
            'teacher' => 'Připomene nc -zv a curl -I.',
            'student' => 'Ověří port 80 a odpověď webu.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Řetěz důkazů',
            'teacher' => 'Ukáže tabulku článek – příkaz – důkaz – závěr.',
            'student' => 'Sestaví řetěz pro intranet a pro neexistující jméno.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: každý článek má vlastní důkaz.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přelož jméno intranet.skola.test na adresu příkazy dig +short a host.',
            'output' => 'Oba nástroje vrátí 10.0.0.10.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig +short intranet.skola.test',
                'expect' => '10.0.0.10',
              ),
              1 => 
              array (
                'cmd' => 'host intranet.skola.test',
                'expect' => 'has address 10.0.0.10',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zeptej se DNS na jméno www.skola.lab a najdi stav odpovědi; potom najdi statický záznam intranetu v /etc/hosts.',
            'output' => 'status: NXDOMAIN a řádek 10.0.0.10 intranet.skola.test intranet.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig www.skola.lab',
                'expect' => 'status: NXDOMAIN',
              ),
              1 => 
              array (
                'cmd' => 'cat /etc/hosts',
                'expect' => 'intranet.skola.test intranet',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř port 80 na adrese intranetu a hlavičky odpovědi webu.',
            'output' => 'nc hlásí succeeded, curl -I vrací HTTP/1.1 200 OK.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 80',
                'expect' => 'succeeded',
              ),
              1 => 
              array (
                'cmd' => 'curl -I http://intranet.skola.test',
                'expect' => 'HTTP/1.1 200 OK',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Sestav řetěz důkazů ve sloupcích článek – příkaz – důkaz – závěr pro intranet a pro jméno www.skola.lab.',
            'output' => 'Dvě tabulky po čtyřech článcích; u www.skola.lab řetěz končí u NXDOMAIN.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku řetězu s vyplněnými příkazy; doplňuje důkazy a závěry.',
          'standard' => 'Překlad jména, NXDOMAIN, /etc/hosts, port, HTTP a dva řetězy.',
          'challenge' => 'Vysvětlí, proč dotaz na záznam AAAA vrací prázdnou odpověď se stavem NOERROR a co to znamená pro dual-stack.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže výstup dig, třída řekne stav a adresu.',
            1 => 'Dvojice: který článek řetězu chybí v kolegově tabulce?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Překlad jména',
              'levels' => 
              array (
                0 => 'Nezvládne.',
                1 => 'Jeden nástroj.',
                2 => 'dig i host se správnou adresou.',
                3 => 'Vysvětlí sekce odpovědi dig.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'NXDOMAIN a hosts',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Pozná NXDOMAIN.',
                2 => 'NXDOMAIN i přednost /etc/hosts.',
                3 => 'Navrhne, kdy hosts použít a kdy ne.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Řetěz důkazů',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Neúplný.',
                2 => 'Čtyři články s důkazy.',
                3 => 'Řetěz pro chybový i funkční případ.',
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
              'question' => 'Odpověď dig obsahuje „status: NXDOMAIN“. Co z toho plyne?',
              'options' => 
              array (
                0 => 'Server neodpověděl.',
                1 => 'Jméno v DNS neexistuje.',
                2 => 'Port 80 je zavřený.',
              ),
              'correct' => 1,
              'explanation' => 'NXDOMAIN = doména neexistuje; síť i server DNS fungují.',
            ),
            1 => 
            array (
              'question' => 'Jméno je v /etc/hosts i v DNS s jinou adresou. Kterou systém použije běžně?',
              'options' => 
              array (
                0 => 'Adresu z /etc/hosts.',
                1 => 'Adresu z DNS.',
                2 => 'Náhodně jednu z nich.',
              ),
              'correct' => 0,
              'explanation' => 'Výchozí pořadí: nejdřív soubor hosts, pak DNS.',
            ),
            2 => 
            array (
              'question' => 'Jméno se přeloží, port 80 odpovídá, ale curl vrací chybu 500. Kde hledáš dál?',
              'options' => 
              array (
                0 => 'V DNS.',
                1 => 'V kabelu.',
                2 => 'V aplikaci a jejím logu.',
              ),
              'correct' => 2,
              'explanation' => 'Síťové články fungují; chyba je ve webové aplikaci.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu sestav řetěz důkazů pro jméno localhost a zapiš, kterým článkem se liší od intranetu.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'DNS a síť zkoušíme jen v simulátoru; jména a adresy jsou laboratorní (skola.test, 10.0.0.x).',
          1 => 'Úpravy /etc/hosts na skutečném počítači dělá jen správce.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „je to DNS“ bez důkazu – každý článek musí mít výstup příkazu.',
          1 => 'Otázka do třídy: Jak rozlišíte nefunkční DNS od neexistujícího jména?',
          2 => 'Tempo: AAAA a dual-stack jen jako výzva; standard je řetěz se čtyřmi články.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), úkol 4 na papír.',
          1 => 'Plán B offline: vytištěné výstupy dig, host, nc a curl; žáci sestaví řetěz.',
        ),
        'glossary' => 
        array (
          0 => 'nxdomain',
          1 => 'dual-stack',
        ),
        '_file' => 'lesson_content_v76_3a_b.php',
      ),
      24 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 24 · Stavový firewall: co je otevřené a proč',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím zjistit, které služby na serveru poslouchají, rozlišit odmítnuté a nedostupné spojení a navrhnout pravidla stavového firewallu podle principu nejmenších práv.',
          'success_criteria' => 
          array (
            0 => 'Z výpisu ss -tln určím porty a adresy, na kterých služby poslouchají.',
            1 => 'Rozliším „Connection refused“ (port zavřený) a „Connection timed out“ (zahozeno nebo nedostupné).',
            2 => 'Navrhnu pravidla: výchozí zákaz příchozích, povolené jen potřebné služby a odpovědi na navázaná spojení.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'net_security',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'net_services',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: otevřené dveře',
            'teacher' => 'Přirovná porty k dveřím budovy a zeptá se, které mají být zamčené.',
            'student' => 'Vyjmenují služby, které server opravdu potřebuje.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Kdo poslouchá',
            'teacher' => 'Předvede ss -tln a rozdíl 0.0.0.0 a 127.0.0.1.',
            'student' => 'Zjistí naslouchající porty.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Refused vs. timeout',
            'teacher' => 'Vysvětlí odpověď RST a zahozený paket.',
            'student' => 'Otestují tři cíle a výsledky zapíší.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 60,
            'phase' => 'Stavový firewall',
            'teacher' => 'Vysvětlí stav spojení (nové, navázané) a pořadí pravidel.',
            'student' => 'Seřadí kartičky pravidel do správného pořadí.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Návrh pravidel',
            'teacher' => 'Zadá server s webem a SSH jen ze správcovské sítě.',
            'student' => 'Navrhnou tabulku pravidel a test každého pravidla.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: výchozí zákaz, výjimky s důvodem.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zjisti příkazem ss -tln, které porty server poslouchá a na jaké adrese.',
            'output' => 'Porty 22 a 80 na 0.0.0.0 – dostupné ze všech rozhraní.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Otestuj nc -zv tři cíle: 10.0.0.10 port 22, 10.0.0.10 port 443 a 10.0.0.99 port 80, a zapiš, co znamená každý výsledek.',
            'output' => '22 succeeded, 443 Connection refused (zavřený port), 10.0.0.99 Connection timed out (nedostupné nebo zahozené).',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 22',
                'expect' => 'succeeded',
              ),
              1 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 443',
                'expect' => 'Connection refused',
              ),
              2 => 
              array (
                'cmd' => 'nc -zv 10.0.0.99 80',
                'expect' => 'Connection timed out',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Seřaď kartičky pravidel stavového firewallu do správného pořadí a zdůvodni, proč je výchozí zákaz až na konci.',
            'output' => 'Pořadí: navázaná spojení povolit → SSH ze sítě správců → HTTP pro všechny → ostatní zakázat.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni tabulku pravidel pro server s webem (80) a SSH jen ze sítě 10.0.0.0/24 a ke každému pravidlu napiš test, který ho ověří.',
            'output' => 'Tabulka pravidlo – důvod – test (např. nc -zv z povolené a nepovolené adresy).',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku výsledků nc s vysvětlením a kartičky pravidel s nápovědou pořadí.',
          'standard' => 'Naslouchající porty, testy nc, pořadí pravidel a návrh tabulky.',
          'challenge' => 'Popíše, jak by změnil(a) naslouchání služby na 127.0.0.1 a proč je to lepší než jen pravidlo firewallu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte výsledek nc, třída zvedne kartu „zavřeno / zahozeno / otevřeno“.',
            1 => 'Dvojice: proč nestačí pravidlo „zakázat vše“ na začátku seznamu?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Naslouchající služby',
              'levels' => 
              array (
                0 => 'Nečte ss.',
                1 => 'Najde porty.',
                2 => 'Porty i adresy naslouchání.',
                3 => 'Navrhne omezení naslouchání.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Výsledky testů',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Rozliší úspěch a neúspěch.',
                2 => 'Refused vs. timeout s vysvětlením.',
                3 => 'Spojí s RST a zahozeným paketem.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Pravidla firewallu',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Špatné pořadí.',
                2 => 'Správné pořadí a nejmenší práva.',
                3 => 'Každé pravidlo má test.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_security',
          'competence_label' => 'Zabezpečení sítě a firewall',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'nc hlásí „Connection refused“. Co to nejspíš znamená?',
              'options' => 
              array (
                0 => 'Počítač je vypnutý.',
                1 => 'Kabel je odpojený.',
                2 => 'Počítač odpověděl, ale na portu nic neposlouchá.',
              ),
              'correct' => 2,
              'explanation' => 'Odmítnutí (RST) posílá dostupný počítač se zavřeným portem.',
            ),
            1 => 
            array (
              'question' => 'Proč stavový firewall povoluje „navázaná spojení“?',
              'options' => 
              array (
                0 => 'Aby prošly odpovědi na spojení, která jsme sami zahájili.',
                1 => 'Aby prošlo všechno.',
                2 => 'Aby se zrychlil DNS.',
              ),
              'correct' => 0,
              'explanation' => 'Odpovědi patří k povolenému spojení, nové příchozí spojení ne.',
            ),
            2 => 
            array (
              'question' => 'Služba poslouchá na 0.0.0.0:80. Co to znamená?',
              'options' => 
              array (
                0 => 'Služba je vypnutá.',
                1 => 'Služba přijímá spojení na všech rozhraních serveru.',
                2 => 'Služba přijímá jen z localhostu.',
              ),
              'correct' => 1,
              'explanation' => '0.0.0.0 = všechna rozhraní IPv4.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš pravidla firewallu pro domácí server, na kterém běží jen web, a ke každému jeden test.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Skenujeme a testujeme porty jen v simulátoru; skenování cizích sítí je bez povolení zakázané.',
          1 => 'Pravidla firewallu na skutečném serveru mění jen správce s plánem návratu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „firewall vyřeší vše“ – nejdřív ať služba neposlouchá tam, kde nemusí.',
          1 => 'Otázka do třídy: Jak poznáte z druhé strany, jestli port zavřel firewall, nebo nikdo neposlouchá?',
          2 => 'Tempo: kartičky pravidel jsou nejrychlejší způsob, jak pochopit pořadí – nevynechávej je.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papíře.',
          1 => 'Plán B offline: kartičky pravidel a tabulka výsledků nc.',
        ),
        'glossary' => 
        array (
          0 => 'firewall',
          1 => 'rst',
        ),
        '_file' => 'lesson_content_v76_3a_b.php',
      ),
      25 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 25 · Bezpečné skripty v Bashu: uvozovky, kontroly a nanečisto',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím napsat skript, který správně pracuje s názvy souborů s mezerou, kontroluje předpoklady před nebezpečnou akcí, zapisuje chyby do logu a umí běžet nanečisto.',
          'success_criteria' => 
          array (
            0 => 'Proměnné s názvy souborů dávám do uvozovek ("$F").',
            1 => 'Před mazáním ověřím, že cesta existuje a není prázdná.',
            2 => 'Skript má režim nanečisto, který akce jen vypíše.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_automation',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: skript, který smazal víc',
            'teacher' => 'Popíše skutečný typ chyby: prázdná proměnná v příkazu mazání.',
            'student' => 'Hledají, která kontrola chyběla.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Uvozovky',
            'teacher' => 'Ukáže soubor s mezerou s uvozovkami a bez nich.',
            'student' => 'Vyzkoušejí rozdíl v simulátoru.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Kontroly předpokladů',
            'teacher' => 'Předvede test -f, test -d a ukončení při chybě.',
            'student' => 'Doplní kontroly do skriptu.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 58,
            'phase' => 'Chyby do logu',
            'teacher' => 'Ukáže přesměrování 2> a návratový kód.',
            'student' => 'Zapíší chybový výstup do souboru.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 80,
            'phase' => 'Nanečisto',
            'teacher' => 'Ukáže režim, kdy skript místo akce vypíše příkaz (echo).',
            'student' => 'Přidají režim nanečisto a otestují ho.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne čtyři pojistky.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vytvoř soubor s mezerou v názvu jednou s uvozovkami kolem proměnné a jednou bez nich a porovnej výsledek ls.',
            'output' => 'S uvozovkami vznikne jeden soubor „muj soubor.txt“, bez nich dva soubory „muj“ a „soubor.txt“.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'F=\'muj soubor.txt\'; touch "$F"; ls',
                'expect' => '\'muj soubor.txt\'',
              ),
              1 => 
              array (
                'cmd' => 'touch muj soubor.txt; ls',
                'expect' => 'muj  soubor.txt',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Ověř existenci souboru testem a výsledek vypiš; zkus totéž s neexistující cestou.',
            'output' => 'test -f /etc/hosts && echo existuje vypíše existuje; u chybějící cesty se nic nestane.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'test -f /etc/hosts && echo existuje',
                'expect' => 'existuje',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Přesměruj chybový výstup neúspěšného příkazu do souboru chyby.log a obsah zobraz.',
            'output' => 'chyby.log obsahuje hlášku „No such file or directory“.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ls /neexistuje 2> chyby.log; cat chyby.log',
                'expect' => 'No such file or directory',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Přidej do skriptu úklidu režim nanečisto: místo smazání starých záloh příkaz jen vypíše (echo rm …) a skript ukáže, co by udělal.',
            'output' => 'Výpis „rm -rf /tmp/stare-zalohy“ bez skutečného smazání a vysvětlení, kdy se režim vypíná.',
            'time' => '22 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'echo rm -rf /tmp/stare-zalohy',
                'expect' => 'rm -rf /tmp/stare-zalohy',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kostru skriptu s vyznačenými místy pro uvozovky a kontroly.',
          'standard' => 'Uvozovky, kontroly, log chyb a režim nanečisto.',
          'challenge' => 'Popíše, co dělá set -euo pipefail ve skutečném Bashi a kterou chybu z úvodu hodiny by zachytilo.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže řádek skriptu, třída najde chybějící uvozovky.',
            1 => 'Dvojice: co by skript smazal, kdyby byla proměnná prázdná?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Uvozovky',
              'levels' => 
              array (
                0 => 'Bez uvozovek.',
                1 => 'Jen někde.',
                2 => 'Všechny proměnné s cestou v uvozovkách.',
                3 => 'Vysvětlí rozdělení slov shellem.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Kontroly',
              'levels' => 
              array (
                0 => 'Žádné.',
                1 => 'Jedna kontrola.',
                2 => 'Kontroly před každou nebezpečnou akcí.',
                3 => 'Kontrola prázdné proměnné i cesty.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Nanečisto a log',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen log.',
                2 => 'Režim nanečisto a log chyb.',
                3 => 'Výchozí je nanečisto, ostrý běh přepínačem.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_automation',
          'competence_label' => 'Linux: automatizace skriptem a cronem',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč psát proměnnou s cestou jako "$F" v uvozovkách?',
              'options' => 
              array (
                0 => 'Aby se název s mezerou nerozdělil na víc slov.',
                1 => 'Aby skript běžel rychleji.',
                2 => 'Uvozovky jsou jen pro hezčí zápis.',
              ),
              'correct' => 0,
              'explanation' => 'Shell bez uvozovek dělí text podle mezer.',
            ),
            1 => 
            array (
              'question' => 'K čemu je režim nanečisto (dry-run)?',
              'options' => 
              array (
                0 => 'Skript se spustí dvakrát.',
                1 => 'Skript ukáže, co by udělal, ale nic nezmění.',
                2 => 'Skript smaže jen polovinu souborů.',
              ),
              'correct' => 1,
              'explanation' => 'Výpis plánovaných akcí dovolí chybu chytit předem.',
            ),
            2 => 
            array (
              'question' => 'Co dělá přesměrování 2> chyby.log?',
              'options' => 
              array (
                0 => 'Spustí příkaz dvakrát.',
                1 => 'Zapíše běžný výstup do souboru.',
                2 => 'Zapíše chybový výstup do souboru chyby.log.',
              ),
              'correct' => 2,
              'explanation' => '2 je číslo chybového výstupu (stderr).',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi v jednom svém skriptu proměnnou bez uvozovek a oprav ji.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Mazací příkazy zkoušíme jen v simulátoru a v režimu nanečisto.',
          1 => 'Do skriptů nepíšeme hesla ani tokeny; citlivé hodnoty patří do chráněných souborů mimo skript.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: rm -rf "$DIR/" s prázdnou proměnnou – ukaž, proč je kontrola prázdné hodnoty nutná.',
          1 => 'Otázka do třídy: Kterou pojistku byste chtěli mít, kdyby skript běžel v noci bez dozoru?',
          2 => 'Tempo: simulátor nepodporuje set ani if ve skriptech – pojistky ukazuj přes test a ||, set -euo pipefail jen výkladem.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 v Linux Labu podle zadání (výstupy jsou v zadání).',
          1 => 'Plán B offline: vytištěný chybný skript, žáci v něm najdou a opraví čtyři problémy.',
        ),
        'glossary' => 
        array (
          0 => 'dry-run',
          1 => 'path',
        ),
        '_file' => 'lesson_content_v76_3a_b.php',
      ),
      26 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 26 · Cvičení obnovy služby: síť, port, TLS a HTTP',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím projít diagnostiku služby po vrstvách – dosažitelnost, směrování, port, šifrované spojení a HTTP – a obnovit službu tak, aby oprava byla ověřená z pohledu uživatele.',
          'success_criteria' => 
          array (
            0 => 'Ověřím dosažitelnost (ping) a výchozí bránu (ip route).',
            1 => 'Rozliším, že HTTP na portu 80 funguje, ale HTTPS na portu 443 není nastavené.',
            2 => 'Po obnově služby ukážu odpověď 200 a záznam o startu v logu.',
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
            'phase' => 'Zadání cvičení',
            'teacher' => 'Rozdá scénář: uživatelé hlásí, že intranet nejde, a role ve dvojici.',
            'student' => 'Zapíší příznak a hypotézy.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Síť a směrování',
            'teacher' => 'Připomene ping a ip route.',
            'student' => 'Ověří dosažitelnost a bránu.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Porty a TLS',
            'teacher' => 'Vysvětlí, že HTTPS potřebuje službu na 443 a certifikát.',
            'student' => 'Porovnají curl na http a https.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 62,
            'phase' => 'Obnova služby',
            'teacher' => 'V simulátoru zastaví nginx a hlídá postup.',
            'student' => 'Najdou zastavenou službu a obnoví ji.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Ověření a záznam',
            'teacher' => 'Vyžaduje důkaz z pohledu uživatele.',
            'student' => 'Ověří odpověď a zapíší runbook pro příště.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne pořadí vrstev.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Ověř dosažitelnost serveru 10.0.0.10 a výchozí bránu svého počítače.',
            'output' => 'ping: 0% packet loss; ip route: default via 10.0.0.1.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ping -c 2 10.0.0.10',
                'expect' => '0% packet loss',
              ),
              1 => 
              array (
                'cmd' => 'ip route',
                'expect' => 'default via 10.0.0.1',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Porovnej odpověď intranetu přes http a přes https a vysvětli rozdíl.',
            'output' => 'http vrací 200 OK, https se nepřipojí na port 443 – šifrované spojení na serveru není nastavené.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://intranet.skola.test',
                'expect' => '200 OK',
              ),
              1 => 
              array (
                'cmd' => 'curl -I https://intranet.skola.test',
                'expect' => 'port 443',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'V simulátoru zastav nginx, zjisti stav služby a obnov ji; ověř odpověď webu.',
            'output' => 'Po zastavení inactive, po startu curl -I http://localhost vrací 200 OK.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx',
                'expect' => 'inactive',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; curl -I http://localhost',
                'expect' => '200 OK',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Napiš krátký runbook „intranet nejde“ s pořadím kontrol od sítě po HTTP a s ověřením po opravě.',
            'output' => 'Runbook o 5–7 krocích s příkazem a očekávaným výsledkem.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu vrstev (síť → port → TLS → HTTP) s příkazy.',
          'standard' => 'Diagnostika po vrstvách, obnova služby a runbook.',
          'challenge' => 'Popíše, co by bylo potřeba pro HTTPS (certifikát, port 443, přesměrování) a jak by ho ověřil(a).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte výstup, třída určí vrstvu, ke které patří.',
            1 => 'Výměna runbooků: zvládl by ho spolužák bez otázek?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Diagnostika po vrstvách',
              'levels' => 
              array (
                0 => 'Náhodně.',
                1 => 'Dvě vrstvy.',
                2 => 'Síť, port, TLS i HTTP s důkazy.',
                3 => 'Vrstvy seřazené podle pravděpodobnosti.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Obnova',
              'levels' => 
              array (
                0 => 'Neobnoví.',
                1 => 'Obnoví bez ověření.',
                2 => 'Obnova ověřená odpovědí 200.',
                3 => 'I záznam v logu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Runbook',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez očekávaných výsledků.',
                2 => '5–7 kroků s příkazem a výsledkem.',
                3 => 'Použitelný kýmkoli z týmu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_diagnose',
          'competence_label' => 'Diagnostika síťového problému',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'http://intranet.skola.test funguje, https://intranet.skola.test hlásí, že se nelze připojit na port 443. Co z toho plyne?',
              'options' => 
              array (
                0 => 'DNS nefunguje.',
                1 => 'Server je vypnutý.',
                2 => 'Na serveru neběží šifrovaná služba na portu 443.',
              ),
              'correct' => 2,
              'explanation' => 'Jméno i port 80 fungují; chybí jen HTTPS.',
            ),
            1 => 
            array (
              'question' => 'ping na server projde. Co tím víš jistě?',
              'options' => 
              array (
                0 => 'Server je dosažitelný po síti.',
                1 => 'Webová služba běží.',
                2 => 'Certifikát je platný.',
              ),
              'correct' => 0,
              'explanation' => 'ping ověřuje síť, ne služby.',
            ),
            2 => 
            array (
              'question' => 'Co patří do runbooku pro opakovaný problém?',
              'options' => 
              array (
                0 => 'Jméno toho, kdo problém způsobil.',
                1 => 'Kroky s příkazy, očekávaným výsledkem a ověřením po opravě.',
                2 => 'Jen věta „restartuj server“.',
              ),
              'correct' => 1,
              'explanation' => 'Runbook musí zvládnout kdokoli z týmu.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přepiš svůj runbook tak, aby ho zvládl spolužák z jiné třídy, a nech ho ho přečíst.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Služby zastavujeme jen v simulátoru Linux Labu; adresy jsou laboratorní.',
          1 => 'Runbook neobsahuje hesla ani přístupové údaje.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „ping funguje, takže web taky“ – ukaž, že každá vrstva má vlastní důkaz.',
          1 => 'Otázka do třídy: Která vrstva selhala, když jméno funguje, ale port ne?',
          2 => 'Tempo: obnova služby je jádro; TLS stačí jako zjištění, nastavení certifikátu je téma 4. ročníku.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), runbook na papír.',
          1 => 'Plán B offline: karty vrstev a vytištěné výstupy; žáci sestaví postup.',
        ),
        'glossary' => 
        array (
          0 => 'tls',
          1 => 'gateway',
        ),
        '_file' => 'lesson_content_v76_3a_b.php',
      ),
      27 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 27 · Týmový incident: role, komunikace a důkazy',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím v týmu vyřešit incident s rozdělenými rolemi (vedoucí, vyšetřovatel, komunikace, zapisovatel), sdílet průběžné stavy a uzavřít incident se záznamem a ověřením.',
          'success_criteria' => 
          array (
            0 => 'Tým má rozdělené role a vedoucí rozhoduje o dalším kroku.',
            1 => 'Komunikace posílá stav každých 10 minut: co víme, co děláme, kdy další zpráva.',
            2 => 'Uzavření obsahuje ověření a jedno opatření prevence.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_services',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'lnx_text',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Role a scénář',
            'teacher' => 'Rozdá scénář „web vrací chyby a uživatelé si stěžují“ a karty rolí.',
            'student' => 'Rozdělí role ve čtveřici.',
            'form' => 've skupinách',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 35,
            'phase' => 'Vyšetřování',
            'teacher' => 'Hraje uživatele a vedení, posílá dotazy.',
            'student' => 'Vyšetřovatel sbírá důkazy, zapisovatel vede časovou osu.',
            'form' => 've skupinách',
          ),
          2 => 
          array (
            'from' => 35,
            'to' => 45,
            'phase' => 'Stavová zpráva',
            'teacher' => 'Ukáže tvar zprávy co víme – co děláme – kdy příště.',
            'student' => 'Komunikace pošle první stavovou zprávu.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Náprava a ověření',
            'teacher' => 'Hlídá, aby vedoucí schválil nápravu.',
            'student' => 'Tým navrhne nápravu a ověří ji.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Uzavření a rozbor',
            'teacher' => 'Vede krátký rozbor bez hledání viníka.',
            'student' => 'Sepíší záznam a opatření.',
            'form' => 've skupinách',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne, co role přinesly.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Jako vyšetřovatel zjisti, kolik požadavků v přístupovém logu skončilo kódem 404, a ověř, jestli služba běží.',
            'output' => '9 odpovědí 404; nginx active (running).',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo awk \'$9 == 404\' /var/log/nginx/access.log | wc -l',
                'expect' => '9',
              ),
              1 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'active (running)',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Přečti poslední řádky chybového logu webového serveru a zapiš, jestli obsahují chybu, nebo jen informaci.',
            'output' => 'Řádek [notice] … signal process started – informace, ne chyba.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo tail -n 3 /var/log/nginx/error.log',
                'expect' => '[notice]',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Jako komunikace napiš dvě stavové zprávy pro uživatele ve tvaru co víme – co děláme – kdy další zpráva.',
            'output' => 'Dvě zprávy do tří vět bez technického žargonu.',
            'time' => '12 min',
          ),
          3 => 
          array (
            'text' => 'Jako zapisovatel sestav časovou osu incidentu a s týmem napište uzavření: příčina, náprava, ověření a jedno opatření prevence.',
            'output' => 'Časová osa a záznam uzavření incidentu.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane roli zapisovatele s předtištěnou tabulkou časové osy.',
          'standard' => 'Práce v roli podle karty a společné uzavření.',
          'challenge' => 'Jako vedoucí incidentu rozhoduje o krocích a na konci zhodnotí, které rozhodnutí bylo nejrizikovější.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte stavovou zprávu a třída posoudí, jestli obsahuje všechny tři části.',
            1 => 'Kontrola časové osy: má každý záznam čas a zdroj?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Role',
              'levels' => 
              array (
                0 => 'Všichni dělají totéž.',
                1 => 'Role nejasné.',
                2 => 'Role dodržené.',
                3 => 'Vedoucí koordinuje a rozhoduje s důkazy.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Komunikace',
              'levels' => 
              array (
                0 => 'Žádná.',
                1 => 'Technický žargon.',
                2 => 'Stav ve tvaru co víme – co děláme – kdy příště.',
                3 => 'Pravidelně a srozumitelně.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Uzavření',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez ověření.',
                2 => 'Příčina, náprava, ověření, prevence.',
                3 => 'Opatření je měřitelné.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'lnx_services',
          'competence_label' => 'Linux: služby a systémové logy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co patří do stavové zprávy během incidentu?',
              'options' => 
              array (
                0 => 'Co víme, co děláme a kdy přijde další zpráva.',
                1 => 'Kdo chybu způsobil.',
                2 => 'Celý výpis logu.',
              ),
              'correct' => 0,
              'explanation' => 'Uživatel potřebuje stav a očekávání.',
            ),
            1 => 
            array (
              'question' => 'Proč má incident jednoho vedoucího?',
              'options' => 
              array (
                0 => 'Aby ostatní nemuseli pracovat.',
                1 => 'Aby se rozhodovalo jednotně a kroky se nepřekrývaly.',
                2 => 'Protože vedoucí ví vše nejlépe.',
              ),
              'correct' => 1,
              'explanation' => 'Jedno rozhodovací místo brání chaosu.',
            ),
            2 => 
            array (
              'question' => 'Řádek chybového logu má úroveň [notice]. Co to znamená?',
              'options' => 
              array (
                0 => 'Služba spadla.',
                1 => 'Disk je plný.',
                2 => 'Jde o informaci, ne o chybu.',
              ),
              'correct' => 2,
              'explanation' => 'Úroveň závažnosti rozlišuje informace a chyby.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přepiš jednu stavovou zprávu z hodiny tak, aby jí rozuměl člověk bez technických znalostí.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Incident probíhá jen v simulátoru; žádné skutečné systémy ani data.',
          1 => 'Rozbor hledá příčiny v systému, ne viníka; role se rozdělují dobrovolně.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: všichni ve skupině píší příkazy a nikdo nezapisuje – trvej na rolích.',
          1 => 'Otázka do třídy: Kterou informaci jste potřebovali a nikdo ji neměl?',
          2 => 'Tempo: stavové zprávy vynucuj časovačem každých 10 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: skupiny pracují podle karet rolí a úkolů 1–4 (výstupy v zadání).',
          1 => 'Plán B offline: incident na kartách s výpisy, role a časová osa na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'severity',
          1 => 'journal',
        ),
        '_file' => 'lesson_content_v76_3a_b.php',
      ),
      28 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 28 · Opakování a ověření dovedností ročníku',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím v okruzích Linux, služby a síť ukázat ověřeným příkazem, co zvládám, najít svou nejslabší kompetenci a naplánovat, jak ji zlepším.',
          'success_criteria' => 
          array (
            0 => 'V každém z pěti okruhů vyřeším jednu úlohu s důkazem z výstupu příkazu.',
            1 => 'V mapě kompetencí označím silné stránky a jednu mezeru.',
            2 => 'Napíšu konkrétní plán procvičení (úroveň Linux Labu nebo cesta) na příští měsíc.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'lnx_users',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'net_addressing',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: mapa roku',
            'teacher' => 'Promítne kompetence ročníku a pět okruhů opakování.',
            'student' => 'Odhadnou, ve kterém okruhu jsou nejsilnější.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 55,
            'phase' => 'Stanoviště',
            'teacher' => 'Hlídá čas stanovišť (9 min) a důkazy.',
            'student' => 'Projdou pět stanovišť: uživatelé, soubory, služby, síť, logy.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 55,
            'to' => 70,
            'phase' => 'Sebehodnocení',
            'teacher' => 'Vysvětlí hodnocení podle důkazů, ne pocitu.',
            'student' => 'Vyplní mapu kompetencí.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 70,
            'to' => 82,
            'phase' => 'Plán procvičení',
            'teacher' => 'Ukáže, jak vybrat úroveň Linux Labu nebo cestu.',
            'student' => 'Napíší plán na měsíc.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 82,
            'to' => 86,
            'phase' => 'Sdílení',
            'teacher' => 'Vybere tři anonymní plány.',
            'student' => 'Poslouchají příklady dobrých plánů.',
            'form' => 'frontálně',
          ),
          5 => 
          array (
            'from' => 86,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne rok.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Stanoviště uživatelé: zjisti, ve kterých skupinách je uživatel student a co znamená skupina sudo.',
            'output' => 'id student: skupiny student, sudo, lab; sudo = smí spouštět příkazy jako správce.',
            'time' => '9 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'id student',
                'expect' => '27(sudo)',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Stanoviště soubory: přečti práva souborů v /var/log a urči, kdo smí číst auth.log.',
            'output' => 'auth.log: -rw-r----- root adm – číst smí root a skupina adm.',
            'time' => '9 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ls -l /var/log',
                'expect' => '-rw-r----- 1 root adm',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Stanoviště síť: zjisti adresu sítě a bránu ze směrovací tabulky a urči, kolik adres má síť /24.',
            'output' => 'Síť 10.0.0.0/24, brána 10.0.0.1, 256 adres (254 pro zařízení).',
            'time' => '9 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ip route',
                'expect' => '10.0.0.0/24 dev eth0',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Vyplň mapu kompetencí ročníku (důkaz ze stanoviště nebo z roku) a napiš plán procvičení nejslabší kompetence na příští měsíc.',
            'output' => 'Mapa kompetencí a plán: co, kde (úroveň labu nebo cesta), kdy, jak poznám zlepšení.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane karty stanovišť s nápovědou prvního příkazu.',
          'standard' => 'Stanoviště, mapa kompetencí a plán.',
          'challenge' => 'Na stanovišti síť rozdělí 10.0.0.0/24 na čtyři podsítě a zapíše jejich rozsahy.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Dvojice si na stanovišti navzájem potvrzují důkaz.',
            1 => 'Učitel přečte plán a třída posoudí, jestli je ověřitelný.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Úlohy na stanovištích',
              'levels' => 
              array (
                0 => 'Méně než 2.',
                1 => '2–3 s nápovědou.',
                2 => 'Všech 5 s důkazem.',
                3 => 'S vysvětlením výstupu spolužákovi.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Sebehodnocení',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Podle pocitu.',
                2 => 'Podle důkazů s jednou mezerou.',
                3 => 'Mezery seřazené podle důležitosti.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Plán procvičení',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Obecný.',
                2 => 'Konkrétní úroveň nebo cesta a termín.',
                3 => 'S měřítkem zlepšení.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'net_addressing',
          'competence_label' => 'Adresace sítě',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kolik adres je k dispozici pro zařízení v síti /24?',
              'options' => 
              array (
                0 => '256',
                1 => '254',
                2 => '24',
              ),
              'correct' => 1,
              'explanation' => '256 adres minus adresa sítě a broadcast.',
            ),
            1 => 
            array (
              'question' => 'Uživatel je ve skupině sudo. Co to znamená?',
              'options' => 
              array (
                0 => 'Smí spouštět příkazy s právy správce (po ověření).',
                1 => 'Je automaticky root.',
                2 => 'Nesmí se přihlásit.',
              ),
              'correct' => 0,
              'explanation' => 'sudo dává dočasná práva správce pro jednotlivé příkazy.',
            ),
            2 => 
            array (
              'question' => 'Který plán procvičení je nejlepší?',
              'options' => 
              array (
                0 => 'Zlepším se v sítích.',
                1 => 'Budu víc opakovat.',
                2 => 'Do 15. 5. vyřeším 3 úrovně labu „Práva“ a ověřím se exit ticketem.',
              ),
              'correct' => 2,
              'explanation' => 'Konkrétní úkol, termín a způsob ověření.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: splň první krok svého plánu procvičení a zapiš výsledek.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Stanoviště běží v simulátoru Linux Labu; adresy jsou laboratorní.',
          1 => 'Mapa kompetencí je osobní – vidí ji žák a jeho učitel, nesdílí se.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: sebehodnocení podle pocitu („umím“) – vyžaduj důkaz ze stanoviště nebo z roku.',
          1 => 'Otázka do třídy: Která dovednost z letoška vás nejvíc překvapila?',
          2 => 'Tempo: stanoviště po 9 minutách; kdo je hotový dřív, plní výzvu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: stanoviště 1–3 v Linux Labu (výstupy v zadání), mapa a plán na papír.',
          1 => 'Plán B offline: vytištěné výpisy pro stanoviště a mapa kompetencí na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'sudo',
          1 => 'vlsm',
        ),
        '_file' => 'lesson_content_v76_3a_b.php',
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
      8 => 'lesson_content_v76_1a_a.php',
      9 => 'lesson_content_v76_1a_b.php',
      10 => 'lesson_content_v76_2a_a.php',
      11 => 'lesson_content_v76_2a_b.php',
      12 => 'lesson_content_v76_3a_a.php',
      13 => 'lesson_content_v76_3a_b.php',
      14 => 'lesson_content_v76_4a_a.php',
      15 => 'lesson_content_v76_4a_b.php',
    ),
  ),
  'sig' => '2d75c86284779fb9b76df1d5328dfa465a4d5c6c',
  'hash' => 'b6839f22ed2e37169161256429f65bbfb5d3a98c87d9959be325e99dbd7acf8a',
  'built_at' => '2026-10-12T21:50:29+02:00',
);
