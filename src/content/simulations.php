<?php

declare(strict_types=1);

/*
 * High-impact interactive simulations used inside Knowledge Tour.
 * Scoring is motivational only; it is stored locally in the browser.
 */
return [
    'class_2a' => [
        'hierarchy' => [
            'id' => 'gfx-hierarchy',
            'type' => 'hierarchy',
            'title' => 'Zachraň plakát: vizuální hierarchie',
            'lead' => 'Uprav poměry prvků tak, aby divák během dvou sekund pochopil, co je akce, kdy je a co má udělat.',
            'task' => 'Nastav jasné pořadí: název akce → datum/místo → CTA → doplňující informace.',
            'prediction' => ['q' => 'Co má být na plakátu nejsilnější první vizuální bod?', 'options' => ['Doplňkový text', 'Název akce', 'Logo nástroje'], 'correct' => 1],
            'hints' => [
                'Představ si plakát jako malý náhled na mobilu. Co musí být čitelné i bez detailů?',
                'Zvětši rozdíl mezi titulkem a běžným textem a dej důležitým prvkům více prostoru.',
                'Pro tento úkol zkus titulek alespoň 54 px, info nejvýše 28 px, CTA alespoň 20 px a vertikální spacing alespoň 16 px.',
            ],
            'conclusion' => ['q' => 'Jaký je nejlepší závěr?', 'options' => ['Hierarchie je jen velikost fontu.', 'Hierarchie vzniká kombinací velikosti, kontrastu, váhy, pozice a prostoru.', 'Každý prvek má být stejně výrazný.'], 'correct' => 1],
            'points' => 5,
        ],
        'composition' => [
            'id' => 'gfx-grid',
            'type' => 'grid',
            'title' => 'Grid Lab: postav pořádek z chaosu',
            'lead' => 'Měň počet sloupců, okraje a mezery. Sleduj, jak se stejný obsah začne nebo přestane vizuálně držet pohromadě.',
            'task' => 'Vytvoř layout s jasnými společnými hranami, dostatkem whitespace a konzistentními rozestupy.',
            'prediction' => ['q' => 'Co grid řeší nejlépe?', 'options' => ['Náhodně zvětšuje obrázky', 'Vytváří konzistentní vztahy a zarovnání', 'Automaticky vybírá barvy'], 'correct' => 1],
            'hints' => [
                'Sleduj levé a pravé hrany bloků. Kolik různých neviditelných linií vytváříš?',
                'Větší okraj a konzistentní gutter často pomohou víc než další dekorace.',
                'Pro tento úkol použij alespoň 4 sloupce, margin 20+ px, gutter 12+ px a zapnuté zarovnání.',
            ],
            'conclusion' => ['q' => 'Proč layout působí profesionálněji?', 'options' => ['Protože je všechno uprostřed.', 'Protože prvky sdílejí systém zarovnání a spacing.', 'Protože má co nejvíc objektů.'], 'correct' => 1],
            'points' => 5,
        ],
        'export' => [
            'id' => 'gfx-export',
            'type' => 'export',
            'title' => 'Export Lab: kvalita vs. velikost',
            'lead' => 'Připrav webový hero obrázek. Hledej rovnováhu mezi ostrostí, datovou velikostí a vhodným formátem.',
            'task' => 'Pro webový hero zvol rozumný formát, šířku a kvalitu tak, aby byl výstup ostrý a zbytečně těžký nebyl.',
            'prediction' => ['q' => 'Je pro web vždy nejlepší největší možný soubor?', 'options' => ['Ano', 'Ne, cílem je vhodný kompromis kvality a velikosti', 'Jen když je PNG'], 'correct' => 1],
            'hints' => [
                'Pro fotografický nebo smíšený webový obsah obvykle nepotřebuješ bezztrátový PNG.',
                'Pro běžný hero stačí rozumná šířka kolem 1920 px a kvalita kolem 80 %.',
                'Zkus WebP nebo JPG, 1920 px a kvalitu 75–90 %.',
            ],
            'conclusion' => ['q' => 'Co je správná exportní strategie?', 'options' => ['Vždy 100 % kvalita a největší rozlišení.', 'Formát a parametry volím podle cílového média a ověřím kvalitu v reálné velikosti.', 'Vše exportuji jako PDF.'], 'correct' => 1],
            'points' => 5,
        ],
    ],
    'class_3a' => [
        'dhcp' => [
            'id' => 'net-dhcp',
            'type' => 'dhcp',
            'title' => 'DHCP DORA: proč klient skončil na 169.254.x.x?',
            'lead' => 'Rozbij a oprav automatickou konfiguraci. Sleduj DORA handshake a výsledek ipconfig v reálném čase.',
            'task' => 'Oprav síť tak, aby klient dostal platnou adresu, bránu a DNS z DHCP.',
            'prediction' => ['q' => 'Klient má 169.254.41.18. Kterou vrstvu má smysl řešit nejdřív?', 'options' => ['TLS certifikát', 'DHCP / lokální síťovou konfiguraci', 'CSS webu'], 'correct' => 1],
            'hints' => [
                'Dívej se na posloupnost DISCOVER → OFFER → REQUEST → ACK. Kde se komunikace zastavila?',
                'DHCP server musí být dostupný ve správné VLAN a musí mít volnou adresu.',
                'Nastav server ON, správnou VLAN a volný pool.',
            ],
            'conclusion' => ['q' => 'Co 169.254.x.x nejčastěji znamená v této simulaci?', 'options' => ['Klient nedostal očekávanou DHCP konfiguraci.', 'DNS funguje.', 'Je to veřejná IP.'], 'correct' => 0],
            'points' => 5,
        ],
        'ports' => [
            'id' => 'net-reachability',
            'type' => 'reachability',
            'title' => 'Ping ≠ služba: odděl hosta od HTTPS',
            'lead' => 'Měň ICMP, webovou službu a firewall. Okamžitě uvidíš, že ping a HTTPS odpovídají na jiné otázky.',
            'task' => 'Nastav stav, kdy ping selže, ale HTTPS přesto funguje.',
            'prediction' => ['q' => 'Může web fungovat, i když ping neodpovídá?', 'options' => ['Ano', 'Ne'], 'correct' => 0],
            'hints' => [
                'Ping používá ICMP. HTTPS typicky TCP/443.',
                'Nech webovou službu běžet a TCP/443 povolený. Zablokuj jen ICMP.',
                'ICMP = blokovat, HTTPS služba = běží, TCP/443 = povolit.',
            ],
            'conclusion' => ['q' => 'Co dokazuje úspěšný ping?', 'options' => ['Že HTTPS určitě funguje.', 'Že cílový host odpověděl na ICMP; nic víc o konkrétní službě.', 'Že DNS je správně.'], 'correct' => 1],
            'points' => 5,
        ],
        'troubleshooting' => [
            'id' => 'net-troubleshoot',
            'type' => 'troubleshoot3',
            'title' => 'Incident: „IP funguje, intranet ne“',
            'lead' => 'Máš jen symptom. Vol příkazy jako administrátor a z důkazů postupně zužuj příčinu. Incident je schválně řešitelný bez restartu.',
            'task' => 'Najdi root cause s co nejmenším počtem smysluplných testů a navrhni validaci opravy.',
            'prediction' => ['q' => 'Když web funguje přes IP, ale ne přes jméno, která hypotéza je nejsilnější?', 'options' => ['DNS problém', 'Vadný monitor', 'GPU ovladač'], 'correct' => 0],
            'hints' => [
                'Nejdřív potvrď, že klient má použitelnou IP a cestu k serveru.',
                'Pak porovnej výsledek přes IP s výsledkem přes DNS jméno.',
                'Spusť ipconfig, ping brány a nslookup. Root cause je chybný DNS server klienta.',
            ],
            'conclusion' => ['q' => 'Který root cause odpovídá evidence?', 'options' => ['Klient používá chybný DNS resolver.', 'HTTPS port serveru je zavřený.', 'DHCP pool je vyčerpaný.'], 'correct' => 0],
            'points' => 5,
        ],
    ],
    'class_4a' => [
        'binding' => [
            'id' => 'adv-binding',
            'type' => 'binding',
            'title' => 'Service Binding: localhost funguje, LAN ne',
            'lead' => 'Měň bind adresu a firewall. Sleduj, které rozhraní proces skutečně obsluhuje a odkud je dostupný.',
            'task' => 'Zpřístupni aplikaci pouze přes serverovou LAN adresu 10.20.0.30:8000 — ne přes všechny interface.',
            'prediction' => ['q' => 'Proces poslouchá na 127.0.0.1:8000. Dostane se k němu přímo jiný počítač v LAN?', 'options' => ['Ano', 'Ne'], 'correct' => 1],
            'hints' => [
                'Loopback 127.0.0.1 je lokální pouze pro samotný host.',
                '0.0.0.0 by fungovalo, ale úkol chce minimální expozici.',
                'Bindni na 10.20.0.30 a povol TCP/8000 z LAN.',
            ],
            'conclusion' => ['q' => 'Co je klíčový závěr?', 'options' => ['Běžící proces je automaticky dostupný odkudkoli.', 'Dostupnost závisí na bind adrese i firewallu.', 'Binding ovlivňuje jen DNS.'], 'correct' => 1],
            'points' => 5,
        ],
        'tcp' => [
            'id' => 'adv-tcp',
            'type' => 'tcp',
            'title' => 'TCP Handshake: timeout vs. connection refused',
            'lead' => 'Přepínej stav služby a firewallu a sleduj SYN/SYN-ACK/RST. Nauč se číst symptom jako důkaz.',
            'task' => 'Nastav situaci, kdy klient dostane „connection refused“ místo timeoutu.',
            'prediction' => ['q' => 'Který symptom typicky víc odpovídá aktivnímu odmítnutí/uzavřenému portu?', 'options' => ['Connection refused', 'Nekonečný DNS lookup', 'Úspěšný 200 OK'], 'correct' => 0],
            'hints' => [
                'DROP paket zahodí a klient obvykle čeká. RST je aktivní odpověď.',
                'Nech síť cestu povolenou, ale vypni listener služby.',
                'Firewall = allow, service listening = ne.',
            ],
            'conclusion' => ['q' => 'Proč se timeout a refused liší?', 'options' => ['Jde o stejnou věc.', 'Refused znamená aktivní zamítnutí/RST; timeout znamená, že očekávaná odpověď nepřišla.', 'Refused je DNS chyba.'], 'correct' => 1],
            'points' => 5,
        ],
        'diagnostics' => [
            'id' => 'adv-production',
            'type' => 'production',
            'title' => 'Produkční incident: 502 po deployi',
            'lead' => 'Simulovaný incident od monitoringu po reverse proxy. Volíš diagnostické příkazy, sbíráš evidenci, stanovíš root cause a musíš ověřit opravu.',
            'task' => 'Najdi proč Nginx vrací 502 po deployi. Neprováděj změnu, dokud evidence neukáže konkrétní příčinu.',
            'prediction' => ['q' => 'HTTP 502 od Nginx nejvíc naznačuje problém kde?', 'options' => ['Mezi proxy a upstream aplikací', 'V DHCP klienta', 'Ve fontu webu'], 'correct' => 0],
            'hints' => [
                'Nejdřív zjisti, co už funguje: DNS, TLS, Nginx odpověď. Pak testuj upstream.',
                'Porovnej port v Nginx upstream konfiguraci s portem, na kterém aplikace skutečně poslouchá.',
                'Aplikace poslouchá na 127.0.0.1:8000, Nginx je nakonfigurován na 127.0.0.1:9000.',
            ],
            'conclusion' => ['q' => 'Jaký je root cause incidentu?', 'options' => ['Nginx upstream míří na chybný port 9000 místo 8000.', 'DNS vrací špatnou IP.', 'Server nemá default route.'], 'correct' => 0],
            'points' => 5,
        ],
    ],
];
