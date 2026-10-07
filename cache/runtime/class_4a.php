<?php

declare(strict_types=1);

return array (
  'knowledgeTours' => 
  array (
    'cidr' => 
    array (
      'time' => '10–14 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'subnet',
        'address' => '10.20.30.77/27',
        'network' => '10.20.30.64',
        'hosts' => '10.20.30.65–94',
        'broadcast' => '10.20.30.95',
      ),
      'mental' => 'CIDR není školní počítání masek. Je to nástroj pro správné adresování, sumarizaci rout a rychlé rozhodnutí, co je lokální.',
      'steps' => 
      array (
        0 => 'Přelož prefix na velikost bloku.',
        1 => 'Najdi hranici bloku pro danou IP.',
        2 => 'Urči network/broadcast.',
        3 => 'Ověř rozsah použitelných hostů.',
        4 => 'Použij výsledek při route/firewall analýze.',
      ),
      'mistakes' => 
      array (
        0 => 'Počítat subnet „od nuly“ bez velikosti bloku.',
        1 => 'Zaměnit /27 za 27 hostů.',
        2 => 'Přidělit network nebo broadcast hostu.',
      ),
      'check' => 
      array (
        'q' => 'Do které sítě patří 10.20.30.77/27?',
        'options' => 
        array (
          0 => '10.20.30.64/27',
          1 => '10.20.30.77/27',
          2 => '10.20.30.0/24',
        ),
        'correct' => 0,
        'why' => '/27 má blok 32 adres; 77 spadá do intervalu 64–95.',
      ),
    ),
    'routing-advanced' => 
    array (
      'time' => '10–15 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Host',
            1 => 'route lookup',
          ),
          1 => 
          array (
            0 => 'Longest prefix',
            1 => 'nejkonkrétnější route',
          ),
          2 => 
          array (
            0 => 'Gateway/interface',
            1 => 'next hop',
          ),
          3 => 
          array (
            0 => 'Firewall',
            1 => 'policy',
          ),
          4 => 
          array (
            0 => 'Cíl',
            1 => 'service',
          ),
        ),
      ),
      'mental' => 'Routing není „použij default gateway“. Kernel vybírá nejkonkrétnější odpovídající route, potom next-hop/interface. Default je jen poslední možnost.',
      'steps' => 
      array (
        0 => 'Zapiš cílovou IP.',
        1 => 'Najdi všechny matching routes.',
        2 => 'Vyber route s nejdelším prefixem.',
        3 => 'Ověř next-hop a interface.',
        4 => 'Zkontroluj návratovou cestu — asymetrie může být stejně důležitá.',
      ),
      'mistakes' => 
      array (
        0 => 'Číst routing tabulku jen odshora dolů.',
        1 => 'Ignorovat specifičtější route.',
        2 => 'Zapomenout na return path nebo policy routing.',
      ),
      'check' => 
      array (
        'q' => 'Existuje route 10.20.0.0/16 a 10.20.30.0/24. Cíl je 10.20.30.77. Která vyhraje?',
        'options' => 
        array (
          0 => '/24',
          1 => '/16',
          2 => 'default',
        ),
        'correct' => 0,
        'why' => 'Longest-prefix match vybírá nejkonkrétnější odpovídající route.',
      ),
    ),
    'dns-advanced' => 
    array (
      'time' => '12–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Autorita',
            1 => 'A = 203.0.113.20, TTL 300',
          ),
          1 => 
          array (
            0 => 'Resolver A',
            1 => 'cache: stará IP',
          ),
          2 => 
          array (
            0 => 'Resolver B',
            1 => 'cache: nová IP',
          ),
          3 => 
          array (
            0 => 'Klienti',
            1 => 'dočasně různé výsledky',
          ),
        ),
      ),
      'mental' => 'Po změně DNS nemusí svět přepnout naráz. TTL řídí, jak dlouho může resolver cacheovat starou odpověď; různí klienti tak mohou chvíli vidět různé cíle.',
      'steps' => 
      array (
        0 => 'Zjisti autoritativní odpověď.',
        1 => 'Porovnej ji s běžným resolverem.',
        2 => 'Sleduj TTL.',
        3 => 'Ověř A/AAAA zvlášť.',
        4 => 'Při migraci vždy počítej s cache a rollback plánem.',
      ),
      'mistakes' => 
      array (
        0 => 'Měnit záznam opakovaně, protože jeden resolver ještě vrací starou hodnotu.',
        1 => 'Ignorovat AAAA při změně pouze A.',
        2 => 'Zaměnit TTL za dobu registrace domény.',
      ),
      'check' => 
      array (
        'q' => 'Autoritativní server vrací novou IP, ale jeden resolver starou s TTL 120. Co je pravděpodobné?',
        'options' => 
        array (
          0 => 'Resolver má ještě platnou cache.',
          1 => 'Nginx je určitě vypnutý.',
          2 => 'SSH permissions jsou špatně.',
        ),
        'correct' => 0,
        'why' => 'TTL říká, jak dlouho může být odpověď držena v cache.',
      ),
    ),
    'binding' => 
    array (
      'time' => '10–14 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => '127.0.0.1:8080',
          1 => 'jen loopback',
          2 => 'zvenku nedostupné',
        ),
        'right' => 
        array (
          0 => '0.0.0.0:8080',
          1 => 'všechna IPv4 rozhraní',
          2 => 'dostupnost řídí firewall',
        ),
      ),
      'mental' => 'Proces může běžet, port může být otevřený — ale jen na loopbacku. Binding určuje, na kterých lokálních adresách socket skutečně přijímá spojení.',
      'steps' => 
      array (
        0 => 'Najdi listening socket přes ss/netstat.',
        1 => 'Přečti bind adresu.',
        2 => 'Porovnej s IP, na kterou se připojuje klient/reverse proxy.',
        3 => 'Zvaž bezpečnost změny na širší binding.',
        4 => 'Doplň firewall scope místo bezhlavého „otevři všem“.',
      ),
      'mistakes' => 
      array (
        0 => 'Zaměnit 127.0.0.1 za „adresu serveru v LAN“.',
        1 => 'Změnit binding na 0.0.0.0 bez firewall omezení.',
        2 => 'Předpokládat, že běžící systemd service = dostupná služba.',
      ),
      'check' => 
      array (
        'q' => 'Aplikace naslouchá na 127.0.0.1:3000 a Nginx na jiném serveru se k ní připojuje. Výsledek?',
        'options' => 
        array (
          0 => 'Spojení z jiného serveru selže.',
          1 => 'Bude fungovat automaticky.',
          2 => 'Vyřeší to DNS TTL.',
        ),
        'correct' => 0,
        'why' => 'Loopback je lokální pouze pro daný host. Vzdálený stroj se na něj nedostane.',
      ),
    ),
    'tcp' => 
    array (
      'time' => '10–14 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Client',
            1 => 'SYN',
          ),
          1 => 
          array (
            0 => 'Server',
            1 => 'SYN-ACK',
          ),
          2 => 
          array (
            0 => 'Client',
            1 => 'ACK',
          ),
          3 => 
          array (
            0 => 'Obě strany',
            1 => 'ESTABLISHED',
          ),
        ),
      ),
      'mental' => 'TCP stav ti říká, jak daleko se spojení dostalo. „Connection refused“ a timeout jsou diagnosticky úplně jiné informace.',
      'steps' => 
      array (
        0 => 'Rozliš timeout vs. refused.',
        1 => 'Ověř listening socket.',
        2 => 'Sleduj SYN/SYN-ACK, pokud je potřeba packet capture.',
        3 => 'Ověř firewall/NAT cestu.',
        4 => 'Po navázání TCP teprve řeš TLS/HTTP.',
      ),
      'mistakes' => 
      array (
        0 => 'Házet timeout i refused do jednoho pytle.',
        1 => 'Řešit HTTP, když se neotevře TCP socket.',
        2 => 'Ignorovat backlog nebo stav procesu při přetížení.',
      ),
      'check' => 
      array (
        'q' => 'Klient okamžitě dostane „connection refused“. Co je typičtější než u timeoutu?',
        'options' => 
        array (
          0 => 'Cíl je dosažitelný, ale na portu nic neposlouchá nebo aktivně odmítá.',
          1 => 'DNS nikdy neodpověděl.',
          2 => 'Paket se ztratil někde bez odpovědi.',
        ),
        'correct' => 0,
        'why' => 'Refused obvykle znamená aktivní odpověď RST; timeout spíš absenci odpovědi.',
      ),
    ),
    'ssh-keys' => 
    array (
      'time' => '10–15 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Client',
            1 => 'private key',
          ),
          1 => 
          array (
            0 => 'Proof',
            1 => 'podpis challenge',
          ),
          2 => 
          array (
            0 => 'Server',
            1 => 'authorized_keys / public key',
          ),
          3 => 
          array (
            0 => 'Session',
            1 => 'přístup bez posílání private key',
          ),
        ),
      ),
      'mental' => 'Soukromý klíč neopouští klienta. Server ověřuje kryptografický důkaz proti uloženému veřejnému klíči.',
      'steps' => 
      array (
        0 => 'Ověř, který klíč klient skutečně nabízí.',
        1 => 'Použij ssh -vvv pro detailní průběh.',
        2 => 'Ověř authorized_keys na správném účtu.',
        3 => 'Ověř vlastníka a permissions.',
        4 => 'Po úspěchu zvaž omezení účtu/klíče podle účelu.',
      ),
      'mistakes' => 
      array (
        0 => 'Kopírovat private key na server.',
        1 => 'Debugovat firewall, když TCP/22 i handshake funguje a selže až autentizace.',
        2 => 'Ignorovat, pod jakým uživatelem se klient přihlašuje.',
      ),
      'check' => 
      array (
        'q' => 'Kde má zůstat soukromý SSH klíč?',
        'options' => 
        array (
          0 => 'Na klientovi a chráněný.',
          1 => 'Na veřejném webserveru.',
          2 => 'V DNS TXT záznamu.',
        ),
        'correct' => 0,
        'why' => 'Soukromý klíč je tajemství klienta; server potřebuje veřejnou část.',
      ),
    ),
    'permissions' => 
    array (
      'time' => '10–14 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'permissions',
        'rows' => 
        array (
          0 => 
          array (
            0 => 'Owner',
            1 => 'rw-',
            2 => '6',
          ),
          1 => 
          array (
            0 => 'Group',
            1 => '---',
            2 => '0',
          ),
          2 => 
          array (
            0 => 'Other',
            1 => '---',
            2 => '0',
          ),
        ),
        'mode' => '600',
      ),
      'mental' => 'Unix permissions jsou tři sady práv: owner, group, other. Pro tajné klíče je často podstatné, aby k nim nikdo další neměl přístup.',
      'steps' => 
      array (
        0 => 'Zjisti owner/group.',
        1 => 'Přečti rwx bity nebo číselný mód.',
        2 => 'Rozhodni, kdo práva skutečně potřebuje.',
        3 => 'Použij nejmenší nutná oprávnění.',
        4 => 'Ověř, že služba/SSH stále soubor přečte.',
      ),
      'mistakes' => 
      array (
        0 => 'Použít chmod 777 jako univerzální opravu.',
        1 => 'Měnit permissions bez kontroly ownera.',
        2 => 'Myslet si, že 600 znamená „600 uživatelů“.',
      ),
      'check' => 
      array (
        'q' => 'Co znamená chmod 600 private_key?',
        'options' => 
        array (
          0 => 'Owner může číst a zapisovat, ostatní nemají práva.',
          1 => 'Všichni mohou vše.',
          2 => 'Jen skupina může číst.',
        ),
        'correct' => 0,
        'why' => '6 = rw-, 0 = ---, 0 = ---.',
      ),
    ),
    'diagnostics' => 
    array (
      'time' => '12–18 min',
      'level' => 'Klíčové',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => '1',
            1 => 'Name',
            2 => 'dig / resolver',
          ),
          1 => 
          array (
            0 => '2',
            1 => 'Route',
            2 => 'ip route get',
          ),
          2 => 
          array (
            0 => '3',
            1 => 'Transport',
            2 => 'nc / ss',
          ),
          3 => 
          array (
            0 => '4',
            1 => 'TLS',
            2 => 'openssl/curl -v',
          ),
          4 => 
          array (
            0 => '5',
            1 => 'HTTP',
            2 => 'status + headers',
          ),
          5 => 
          array (
            0 => '6',
            1 => 'Service',
            2 => 'logs/systemctl',
          ),
        ),
      ),
      'mental' => 'Seniornější troubleshooting znamená nejen najít příčinu, ale umět říct, které hypotézy byly jednotlivými měřeními vyloučené.',
      'steps' => 
      array (
        0 => 'Definuj symptom a scope.',
        1 => 'Získej minimální reprodukci.',
        2 => 'Měř od hranice systému dovnitř nebo po vrstvách.',
        3 => 'U každého příkazu napiš, co potvrzuje a co nepotvrzuje.',
        4 => 'Proveď jednu řízenou změnu.',
        5 => 'Validuj službu z pohledu skutečného klienta a měj rollback.',
      ),
      'mistakes' => 
      array (
        0 => 'Sbírat obrovské logy bez hypotézy.',
        1 => 'Dělat změnu dřív než měření.',
        2 => 'Validovat jen localhost, když problém hlásí vzdálený klient.',
        3 => 'Nevytvořit rollback pro produkční zásah.',
      ),
      'check' => 
      array (
        'q' => 'curl localhost funguje, ale vzdálený klient ne. Co je silný další krok?',
        'options' => 
        array (
          0 => 'Ověřit binding, firewall a cestu z klienta.',
          1 => 'Přeinstalovat curl.',
          2 => 'Změnit DNS bez měření.',
        ),
        'correct' => 0,
        'why' => 'Lokální test ověřil aplikaci z hostu, ale ne externí binding ani síťovou cestu.',
      ),
    ),
    'reverse-proxy' => 
    array (
      'time' => '10–14 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Client',
            1 => 'HTTPS :443',
          ),
          1 => 
          array (
            0 => 'Nginx',
            1 => 'TLS + routing',
          ),
          2 => 
          array (
            0 => 'upstream',
            1 => '127.0.0.1:9000',
          ),
          3 => 
          array (
            0 => 'App',
            1 => 'HTTP response',
          ),
        ),
      ),
      'mental' => 'Reverse proxy odděluje veřejný vstup a interní aplikaci. 502 často říká „proxy žije, upstream neodpověděl správně“.',
      'steps' => 
      array (
        0 => 'Ověř klient → proxy.',
        1 => 'Přečti status a proxy error log.',
        2 => 'Ověř upstream adresu/port.',
        3 => 'Otestuj upstream lokálně.',
        4 => 'Oprav jednu věc a validuj z klienta.',
      ),
      'mistakes' => 
      array (
        0 => 'Považovat 502 za DNS.',
        1 => 'Restartovat vše bez měření.',
        2 => 'Validovat jen localhost.',
      ),
      'check' => 
      array (
        'q' => 'Nginx vrací 502. Co už víš?',
        'options' => 
        array (
          0 => 'Klient dosáhl na proxy, problém může být mezi proxy a upstreamem.',
          1 => 'DNS určitě neexistuje.',
          2 => 'TCP/443 je určitě blokovaný.',
        ),
        'correct' => 0,
        'why' => 'HTTP 502 je odpověď proxy, takže část cesty klient → proxy funguje.',
      ),
    ),
    'tls-certificates' => 
    array (
      'time' => '11–15 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => '1',
            1 => 'TCP/443',
            2 => 'spojení',
          ),
          1 => 
          array (
            0 => '2',
            1 => 'SNI',
            2 => 'jméno webu',
          ),
          2 => 
          array (
            0 => '3',
            1 => 'Certifikát',
            2 => 'SAN + platnost',
          ),
          3 => 
          array (
            0 => '4',
            1 => 'Chain',
            2 => 'důvěra CA',
          ),
          4 => 
          array (
            0 => '5',
            1 => 'HTTP',
            2 => 'až potom aplikace',
          ),
        ),
      ),
      'mental' => 'TLS selhání je samostatná vrstva. Je možné mít funkční TCP, ale neplatný certifikát nebo špatný řetězec.',
      'steps' => 
      array (
        0 => 'Ověř TCP/443.',
        1 => 'Použij správné SNI/jméno.',
        2 => 'Zkontroluj SAN a expiraci.',
        3 => 'Zkontroluj chain.',
        4 => 'Po reloadu ověř certifikát zvenku.',
      ),
      'mistakes' => 
      array (
        0 => 'Testovat jen IP a ignorovat SNI.',
        1 => 'Obnovit certifikát, ale nereloadovat službu.',
        2 => 'Kontrolovat pouze soubor na disku, ne prezentovaný certifikát.',
      ),
      'check' => 
      array (
        'q' => 'Nový certifikát je na disku, klient stále vidí starý. Co ověříš?',
        'options' => 
        array (
          0 => 'Zda služba načetla nový certifikát a co skutečně prezentuje na 443.',
          1 => 'DHCP lease.',
          2 => 'ARP vzdáleného klienta.',
        ),
        'correct' => 0,
        'why' => 'Důležitý je certifikát prezentovaný aktivní službou.',
      ),
    ),
    'logs-monitoring' => 
    array (
      'time' => '10–15 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Alert',
            1 => '14:05 502 spike',
          ),
          1 => 
          array (
            0 => 'Access log',
            1 => 'request dorazil',
          ),
          2 => 
          array (
            0 => 'Error log',
            1 => 'connect refused upstream',
          ),
          3 => 
          array (
            0 => 'App log',
            1 => 'žádný request',
          ),
          4 => 
          array (
            0 => 'Hypotéza',
            1 => 'proxy → app',
          ),
        ),
      ),
      'mental' => 'Korelace více signálů zužuje prostor příčin rychleji než jeden obrovský log.',
      'steps' => 
      array (
        0 => 'Zapiš čas a scope.',
        1 => 'Porovnej monitoring s requesty.',
        2 => 'Najdi odpovídající error log.',
        3 => 'Zkontroluj aplikační log ve stejném čase.',
        4 => 'Po opravě sleduj metriky i nové logy.',
      ),
      'mistakes' => 
      array (
        0 => 'grep bez časového rozsahu.',
        1 => 'Hledat chybu jen v jednom logu.',
        2 => 'Po opravě ignorovat monitoring.',
      ),
      'check' => 
      array (
        'q' => 'Nginx access log request má, app log nic. Co to naznačuje?',
        'options' => 
        array (
          0 => 'Problém může být mezi proxy a aplikací.',
          1 => 'Klient určitě nemá DNS.',
          2 => 'Aplikace request úspěšně zpracovala.',
        ),
        'correct' => 0,
        'why' => 'Request do proxy dorazil, ale aplikace ho podle logu neviděla.',
      ),
    ),
    'change-management' => 
    array (
      'time' => '10–15 min',
      'level' => 'Klíčové',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Precheck',
            1 => 'baseline',
          ),
          1 => 
          array (
            0 => 'Change',
            1 => 'jedna řízená změna',
          ),
          2 => 
          array (
            0 => 'Validate',
            1 => 'user path + metrics',
          ),
          3 => 
          array (
            0 => 'Decision',
            1 => 'keep / rollback',
          ),
          4 => 
          array (
            0 => 'Document',
            1 => 'co se stalo',
          ),
        ),
      ),
      'mental' => 'Bez rollbacku není produkční změna bezpečně naplánovaná. Validace musí být definovaná dřív než deploy.',
      'steps' => 
      array (
        0 => 'Definuj cíl a success criteria.',
        1 => 'Ulož config/rollback.',
        2 => 'Proveď omezenou změnu.',
        3 => 'Validuj syntaxi i skutečný user flow.',
        4 => 'Sleduj monitoring a rozhodni keep/rollback.',
      ),
      'mistakes' => 
      array (
        0 => 'Rollback vymýšlet až po chybě.',
        1 => 'Změnit více věcí bez baseline.',
        2 => 'Za úspěch považovat jen „service active“.',
      ),
      'check' => 
      array (
        'q' => 'Kdy má být připraven rollback?',
        'options' => 
        array (
          0 => 'Před změnou.',
          1 => 'Až po incidentu.',
          2 => 'Jen když selže DNS.',
        ),
        'correct' => 0,
        'why' => 'Rollback je součást plánu změny, ne improvizace po selhání.',
      ),
    ),
    'http-observability' => 
    array (
      'time' => '11–15 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Request',
            1 => 'GET /',
          ),
          1 => 
          array (
            0 => 'Status',
            1 => '503',
          ),
          2 => 
          array (
            0 => 'Headers',
            1 => 'Retry-After',
          ),
          3 => 
          array (
            0 => 'Trace',
            1 => 'x-request-id',
          ),
          4 => 
          array (
            0 => 'Latency',
            1 => '820 ms',
          ),
        ),
      ),
      'mental' => 'HTTP odpověď je diagnostický balíček: status + headers + čas + body.',
      'steps' => 
      array (
        0 => 'Získej curl -I/-v.',
        1 => 'Přečti status.',
        2 => 'Přečti důležité headers.',
        3 => 'Porovnej latency.',
        4 => 'Spoj trace ID s logy.',
      ),
      'mistakes' => 
      array (
        0 => 'Ignorovat headers.',
        1 => '200 = vše vždy zdravé.',
        2 => 'Měřit bez času.',
      ),
      'check' => 
      array (
        'q' => 'HTTP 503 dokazuje co?',
        'options' => 
        array (
          0 => 'Nějaká HTTP vrstva odpověděla, ale služba je nedostupná.',
          1 => 'DNS určitě selhal.',
          2 => 'TCP port je zavřený.',
        ),
        'correct' => 0,
        'why' => 'Status 503 je HTTP odpověď, takže cesta do HTTP vrstvy proběhla.',
      ),
    ),
    'load-balancing' => 
    array (
      'time' => '11–15 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Client',
            1 => 'requests',
          ),
          1 => 
          array (
            0 => 'LB',
            1 => 'policy + health',
          ),
          2 => 
          array (
            0 => 'web01',
            1 => 'healthy',
          ),
          3 => 
          array (
            0 => 'web02',
            1 => 'unhealthy',
          ),
        ),
      ),
      'mental' => 'LB rozhoduje kam poslat request; health check rozhoduje, kdo smí dostávat běžný provoz.',
      'steps' => 
      array (
        0 => 'Definuj pool.',
        1 => 'Nastav policy.',
        2 => 'Nastav health check.',
        3 => 'Simuluj failure.',
        4 => 'Ověř recovery.',
      ),
      'mistakes' => 
      array (
        0 => 'TCP open = app healthy.',
        1 => 'Žádný fail threshold.',
        2 => 'Unhealthy backend stále v rotaci.',
      ),
      'check' => 
      array (
        'q' => 'Co má LB udělat s backendem, který stabilně selhává health check?',
        'options' => 
        array (
          0 => 'Dočasně ho vyřadit z běžného provozu.',
          1 => 'Poslat mu více requestů.',
          2 => 'Změnit klientům DNS.',
        ),
        'correct' => 0,
        'why' => 'Health check chrání uživatele před známým nezdravým backendem.',
      ),
    ),
    'canary-release' => 
    array (
      'time' => '12–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Baseline',
            1 => 'v1',
          ),
          1 => 
          array (
            0 => '10 %',
            1 => 'v2',
          ),
          2 => 
          array (
            0 => 'Compare',
            1 => 'error + latency',
          ),
          3 => 
          array (
            0 => 'Gate',
            1 => 'promote/rollback',
          ),
        ),
      ),
      'mental' => 'Canary je experiment s omezeným dopadem a předem definovaným rozhodovacím pravidlem.',
      'steps' => 
      array (
        0 => 'Zapiš baseline.',
        1 => 'Definuj 10 %.',
        2 => 'Definuj metrics.',
        3 => 'Definuj stop podmínku.',
        4 => 'Rozhodni promote/rollback.',
      ),
      'mistakes' => 
      array (
        0 => 'Bez baseline.',
        1 => 'Rozšířit problém na 100 %.',
        2 => 'Rozhodovat pocitem.',
      ),
      'check' => 
      array (
        'q' => 'Kdy canary rollbackovat?',
        'options' => 
        array (
          0 => 'Když překročí předem definovanou stop podmínku.',
          1 => 'Až když si stěžuje celá škola.',
          2 => 'Nikdy.',
        ),
        'correct' => 0,
        'why' => 'Decision gate musí být objektivní a připravený před deployem.',
      ),
    ),
    'backup-restore' => 
    array (
      'time' => '12–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Backup',
            1 => 'snapshot/WAL',
          ),
          1 => 
          array (
            0 => 'Verify',
            1 => 'integrity',
          ),
          2 => 
          array (
            0 => 'Restore',
            1 => 'test target',
          ),
          3 => 
          array (
            0 => 'Validate',
            1 => 'data + app',
          ),
          4 => 
          array (
            0 => 'RPO/RTO',
            1 => 'fit',
          ),
        ),
      ),
      'mental' => 'Backup bez testovaného restore postupu je jen naděje.',
      'steps' => 
      array (
        0 => 'Vyber recovery point.',
        1 => 'Ověř backup.',
        2 => 'Obnov do bezpečného cíle.',
        3 => 'Validuj data i funkci.',
        4 => 'Zapiš skutečný čas a gap.',
      ),
      'mistakes' => 
      array (
        0 => 'Restore poprvé v produkčním incidentu.',
        1 => 'Neověřený backup.',
        2 => 'Ignorovat RPO/RTO.',
      ),
      'check' => 
      array (
        'q' => 'Co je RPO?',
        'options' => 
        array (
          0 => 'Maximální tolerovaná ztráta dat v čase.',
          1 => 'Maximální CPU.',
          2 => 'Port pro restore.',
        ),
        'correct' => 0,
        'why' => 'RPO určuje, jak stará data ještě mohou být přijatelná po obnově.',
      ),
    ),
    'slo-postmortem' => 
    array (
      'time' => '12–17 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'SLI',
            1 => 'co měříme',
          ),
          1 => 
          array (
            0 => 'SLO',
            1 => 'cílová úroveň',
          ),
          2 => 
          array (
            0 => 'Budget',
            1 => 'tolerované chyby',
          ),
          3 => 
          array (
            0 => 'Incident',
            1 => 'dopad',
          ),
          4 => 
          array (
            0 => 'Postmortem',
            1 => 'follow-up',
          ),
        ),
      ),
      'mental' => 'SLO převádí „má to fungovat dobře“ na měřitelný cíl a rozhodovací pravidlo.',
      'steps' => 
      array (
        0 => 'Vyber user-facing SLI.',
        1 => 'Nastav SLO a okno.',
        2 => 'Spočítej budget.',
        3 => 'Vyhodnoť dopad incidentu.',
        4 => 'Sepiš postmortem a follow-up.',
      ),
      'mistakes' => 
      array (
        0 => 'SLO bez měřitelného SLI.',
        1 => 'Postmortem hledající viníka místo systému.',
        2 => 'Follow-up bez vlastníka a termínu.',
      ),
      'check' => 
      array (
        'q' => 'Co je nejdůležitější výstup dobrého postmortemu?',
        'options' => 
        array (
          0 => 'Konkrétní ověřitelné follow-up akce, které snižují pravděpodobnost/opad opakování.',
          1 => 'Jméno člověka, který udělal poslední změnu.',
          2 => 'Co nejdelší dokument bez priorit.',
        ),
        'correct' => 0,
        'why' => 'Cílem je systémové zlepšení, ne hledání viníka.',
      ),
    ),
    'infrastructure-as-code' => 
    array (
      'time' => '14–20 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Code',
            1 => 'desired state',
          ),
          1 => 
          array (
            0 => 'Plan',
            1 => 'diff',
          ),
          2 => 
          array (
            0 => 'Review',
            1 => 'scope',
          ),
          3 => 
          array (
            0 => 'Apply',
            1 => 'change',
          ),
          4 => 
          array (
            0 => 'Validate',
            1 => 'evidence',
          ),
        ),
      ),
      'mental' => 'Bezpečná automatizace odděluje popis změny, kontrolu dopadu a samotné provedení.',
      'steps' => 
      array (
        0 => 'Popiš desired state.',
        1 => 'Vytvoř plan/diff.',
        2 => 'Zkontroluj scope.',
        3 => 'Aplikuj změnu.',
        4 => 'Validuj výsledek.',
      ),
      'mistakes' => 
      array (
        0 => 'Apply bez planu.',
        1 => 'Velký nečitelný change set.',
        2 => 'Ruční změna mimo zdroj pravdy.',
      ),
      'check' => 
      array (
        'q' => 'Proč je plan důležitý?',
        'options' => 
        array (
          0 => 'Ukáže očekávaný diff před změnou prostředí.',
          1 => 'Automaticky opraví všechny incidenty.',
          2 => 'Nahrazuje monitoring.',
        ),
        'correct' => 0,
        'why' => 'Plan dává prostor zkontrolovat scope a riziko.',
      ),
    ),
    'config-drift' => 
    array (
      'time' => '12–18 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Desired',
          1 => 'repo: 443',
        ),
        'right' => 
        array (
          0 => 'Actual',
          1 => 'runtime: 8443',
        ),
      ),
      'mental' => 'Drift je rozdíl mezi zdrojem pravdy a skutečným prostředím. Nejdřív vysvětli proč vznikl, potom ho naprav.',
      'steps' => 
      array (
        0 => 'Detekuj diff.',
        1 => 'Zjisti původ změny.',
        2 => 'Vyber zdroj pravdy.',
        3 => 'Sjednoť stav.',
        4 => 'Zabraň opakování.',
      ),
      'mistakes' => 
      array (
        0 => 'Přepsat drift bez analýzy.',
        1 => 'Ignorovat ruční hotfix.',
        2 => 'Nezapsat finální stav do repozitáře.',
      ),
      'check' => 
      array (
        'q' => 'Co je configuration drift?',
        'options' => 
        array (
          0 => 'Rozdíl mezi deklarovaným a skutečným stavem prostředí.',
          1 => 'Běžné kolísání latency.',
          2 => 'DNS TTL.',
        ),
        'correct' => 0,
        'why' => 'Drift znamená, že realita už neodpovídá požadované konfiguraci.',
      ),
    ),
    'performance-engineering' => 
    array (
      'time' => '14–20 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Latency',
            1 => 'p50 / p95 / p99',
          ),
          1 => 
          array (
            0 => 'Throughput',
            1 => 'RPS',
          ),
          2 => 
          array (
            0 => 'Errors',
            1 => 'rate',
          ),
          3 => 
          array (
            0 => 'Saturation',
            1 => 'CPU / queue / I/O',
          ),
        ),
      ),
      'mental' => 'Výkon je víc rozměrů současně. Hledej korelaci mezi uživatelským symptomem a saturací konkrétního zdroje.',
      'steps' => 
      array (
        0 => 'Změř baseline.',
        1 => 'Sleduj percentily.',
        2 => 'Najdi saturaci.',
        3 => 'Změň jednu věc.',
        4 => 'Znovu změř.',
      ),
      'mistakes' => 
      array (
        0 => 'Pouze průměr latency.',
        1 => 'Optimalizace bez baseline.',
        2 => 'Přidat CPU bez důkazu bottlenecku.',
      ),
      'check' => 
      array (
        'q' => 'Proč sledovat p95?',
        'options' => 
        array (
          0 => 'Odhalí špatnou zkušenost pomalejší části požadavků, kterou průměr může skrýt.',
          1 => 'Je vždy menší než p50.',
          2 => 'Nahrazuje error rate.',
        ),
        'correct' => 0,
        'why' => 'Percentily ukazují distribuci, ne jen průměr.',
      ),
    ),
    'capacity-planning' => 
    array (
      'time' => '13–19 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Load',
            1 => 'realistický mix',
          ),
          1 => 
          array (
            0 => 'Measure',
            1 => 'latency/errors',
          ),
          2 => 
          array (
            0 => 'Headroom',
            1 => 'rezerva',
          ),
          3 => 
          array (
            0 => 'Decision',
            1 => 'scale/optimize',
          ),
          4 => 
          array (
            0 => 'Retest',
            1 => 'ověření',
          ),
        ),
      ),
      'mental' => 'Kapacitu plánuj podle cílového zatížení a rezervy, ne podle absolutního maxima laboratorního testu.',
      'steps' => 
      array (
        0 => 'Definuj workload.',
        1 => 'Najdi bod degradace.',
        2 => 'Urči headroom.',
        3 => 'Vyber scale/optimize strategii.',
        4 => 'Retestuj při cílové zátěži.',
      ),
      'mistakes' => 
      array (
        0 => 'Load test s nereálným trafficem.',
        1 => 'Kapacita = maximum bez rezervy.',
        2 => 'Ignorovat failover scénář.',
      ),
      'check' => 
      array (
        'q' => 'Co je headroom?',
        'options' => 
        array (
          0 => 'Rezerva kapacity pro špičky, růst a výpadek části systému.',
          1 => 'Unused DNS cache.',
          2 => 'Rozdíl mezi HTTP 200 a 404.',
        ),
        'correct' => 0,
        'why' => 'Produkce potřebuje rezervu, ne provoz přesně na limitu.',
      ),
    ),
    'systemd-advanced' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'After určuje pořadí, Requires/Wants vyjadřují různé síly',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Restart loop může zatížit systém a skrýt původní chybu.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Drop-in override bývá bezpečnější než kopie celé vendor ',
          ),
        ),
      ),
      'mental' => 'Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.',
      'steps' => 
      array (
        0 => 'After určuje pořadí, Requires/Wants vyjadřují různé síly závislosti.',
        1 => 'Restart loop může zatížit systém a skrýt původní chybu.',
        2 => 'Drop-in override bývá bezpečnější než kopie celé vendor unit.',
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
        'q' => 'Co je hlavní princip tématu „systemd: dependencies a failure policy“?',
        'options' => 
        array (
          0 => 'Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'After určuje pořadí, Requires/Wants vyjadřují různé síly závislosti.',
      ),
    ),
    'storage-filesystems' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'df ukazuje filesystem, du hledá využití v adresářích; vý',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Inodes mohou dojít dřív než GB.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Mazání bez pochopení ownera procesu a retention policy m',
          ),
        ),
      ),
      'mental' => 'Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.',
      'steps' => 
      array (
        0 => 'df ukazuje filesystem, du hledá využití v adresářích; výsledky nemusí být stejné kvůli otevřeným souborům/mountům.',
        1 => 'Inodes mohou dojít dřív než GB.',
        2 => 'Mazání bez pochopení ownera procesu a retention policy může způsobit další incident.',
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
        'q' => 'Co je hlavní princip tématu „Storage a filesystems“?',
        'options' => 
        array (
          0 => 'Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'df ukazuje filesystem, du hledá využití v adresářích; výsledky nemusí být stejné kvůli otevřeným souborům/mountům.',
      ),
    ),
    'backup-strategy' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'RPO popisuje tolerovatelnou ztrátu dat v čase.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'RTO popisuje cílový čas obnovení služby.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Restore drill má probíhat do bezpečného testovacího cíle',
          ),
        ),
      ),
      'mental' => 'Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.',
      'steps' => 
      array (
        0 => 'RPO popisuje tolerovatelnou ztrátu dat v čase.',
        1 => 'RTO popisuje cílový čas obnovení služby.',
        2 => 'Restore drill má probíhat do bezpečného testovacího cíle a ověřit integritu i funkci.',
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
        'q' => 'Co je hlavní princip tématu „Backup strategie, RPO a RTO“?',
        'options' => 
        array (
          0 => 'Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'RPO popisuje tolerovatelnou ztrátu dat v čase.',
      ),
    ),
    'ssh-hardening' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Preferuj klíče a omezený management scope.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Firewall a SSH config měň s ověřeným rollbackem/console ',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Security změna potřebuje pozitivní i negativní validační',
          ),
        ),
      ),
      'mental' => 'Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.',
      'steps' => 
      array (
        0 => 'Preferuj klíče a omezený management scope.',
        1 => 'Firewall a SSH config měň s ověřeným rollbackem/console access.',
        2 => 'Security změna potřebuje pozitivní i negativní validační test.',
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
        'q' => 'Co je hlavní princip tématu „SSH hardening a bezpečný management“?',
        'options' => 
        array (
          0 => 'Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Preferuj klíče a omezený management scope.',
      ),
    ),
    'containers-basics' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Image je neměnný build artefakt, container konkrétní ins',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Volume slouží pro data, která mají přežít recreate.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Publikovaný host port neřeší špatný listener nebo neheal',
          ),
        ),
      ),
      'mental' => 'Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.',
      'steps' => 
      array (
        0 => 'Image je neměnný build artefakt, container konkrétní instance.',
        1 => 'Volume slouží pro data, která mají přežít recreate.',
        2 => 'Publikovaný host port neřeší špatný listener nebo nehealthy aplikaci uvnitř.',
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
        'q' => 'Co je hlavní princip tématu „Containers: image, runtime, volume a network“?',
        'options' => 
        array (
          0 => 'Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Image je neměnný build artefakt, container konkrétní instance.',
      ),
    ),
    'automation-shell' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Idempotentní druhý průchod nemá vytvářet další změny.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Dry-run/plan umožňuje zkontrolovat scope.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Automatizace bez guardů násobí chybu rychleji než ruční ',
          ),
        ),
      ),
      'mental' => 'Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.',
      'steps' => 
      array (
        0 => 'Idempotentní druhý průchod nemá vytvářet další změny.',
        1 => 'Dry-run/plan umožňuje zkontrolovat scope.',
        2 => 'Automatizace bez guardů násobí chybu rychleji než ruční práce.',
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
        'q' => 'Co je hlavní princip tématu „Bezpečná automatizace a idempotence“?',
        'options' => 
        array (
          0 => 'Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Idempotentní druhý průchod nemá vytvářet další změny.',
      ),
    ),
    'packet-diagnostics-advanced' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'SYN timeout, RST a SYN-ACK vedou k různým hypotézám.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Capture bez filtru rychle vytváří šum.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'TCP úspěch neznamená automaticky funkční TLS/HTTP.',
          ),
        ),
      ),
      'mental' => 'Packet capture má odpovědět na konkrétní otázku a musí být korelovaný se socket state na serveru.',
      'steps' => 
      array (
        0 => 'SYN timeout, RST a SYN-ACK vedou k různým hypotézám.',
        1 => 'Capture bez filtru rychle vytváří šum.',
        2 => 'TCP úspěch neznamená automaticky funkční TLS/HTTP.',
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
        'q' => 'Co je hlavní princip tématu „Pokročilá packet diagnostika“?',
        'options' => 
        array (
          0 => 'Packet capture má odpovědět na konkrétní otázku a musí být korelovaný se socket state na serveru.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'SYN timeout, RST a SYN-ACK vedou k různým hypotézám.',
      ),
    ),
    'incident-runbook' => 
    array (
      'time' => '10–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Mitigation může mít přednost před detailním root cause, ',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Timeline odděluje fakta od hypotéz.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Postmortem má konkrétní akce s ownerem a výsledkem.',
          ),
        ),
      ),
      'mental' => 'Incident response je strukturovaný proces: impact, role, evidence, mitigation, validation a follow-up.',
      'steps' => 
      array (
        0 => 'Mitigation může mít přednost před detailním root cause, pokud rychle obnoví službu bezpečným rollbackem.',
        1 => 'Timeline odděluje fakta od hypotéz.',
        2 => 'Postmortem má konkrétní akce s ownerem a výsledkem.',
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
        'q' => 'Co je hlavní princip tématu „Incident runbook a koordinace“?',
        'options' => 
        array (
          0 => 'Incident response je strukturovaný proces: impact, role, evidence, mitigation, validation a follow-up.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Mitigation může mít přednost před detailním root cause, pokud rychle obnoví službu bezpečným rollbackem.',
      ),
    ),
    'golden-signals' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál.',
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
    'container-networking' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Kontejner má vlastní network namespace; dostupnost služby závisí na bindu, port mappingu, síti a policy.',
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
    'rollback-strategy' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Rollback musí být připraven před změnou, mít jasný trigger a ověřitelný návrat do známého stavu.',
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
    'incident-command' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Při větším incidentu odděl rozhodování, technickou práci a komunikaci, aby tým nesoutěžil o pozornost.',
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
    'id' => 'production_change_window',
    'title' => 'Lekce 2 · Produkční change window: proxy, TLS, logy a rollback',
    'subtitle' => 'Dalších 2 × 45 minut',
    'goal' => 'Navázat na produkční incident a naučit se řízenou změnu: precheck, reverse proxy upstream, TLS certifikát, korelace logů, validace a rollback.',
    'unlock_note' => 'Odemkne se po dokončení prvního praktického incidentního labu.',
    'knowledge' => 
    array (
      0 => 'reverse-proxy',
      1 => 'tls-certificates',
      2 => 'logs-monitoring',
      3 => 'change-management',
      4 => 'diagnostics',
    ),
    'schedule' => 
    array (
      0 => 
      array (
        'time' => '0–12',
        'title' => 'Change plan + baseline',
        'text' => 'Definuj success criteria, precheck a rollback dřív než změnu.',
      ),
      1 => 
      array (
        'time' => '12–27',
        'title' => 'Reverse proxy',
        'text' => 'Ověř Nginx → upstream a čti 502 jako důkaz vrstvy.',
      ),
      2 => 
      array (
        'time' => '27–42',
        'title' => 'TLS renewal',
        'text' => 'Ověř SNI, SAN, chain a skutečně prezentovaný certifikát.',
      ),
      3 => 
      array (
        'time' => '42–58',
        'title' => 'Log correlation',
        'text' => 'Propoj monitoring, access/error log a app log podle času.',
      ),
      4 => 
      array (
        'time' => '58–76',
        'title' => 'Řízený deploy',
        'text' => 'Proveď change a validuj user path + metriky.',
      ),
      5 => 
      array (
        'time' => '76–86',
        'title' => 'Failure injection',
        'text' => 'Nový upstream port je špatně — rozhodni rollback vs. oprava.',
      ),
      6 => 
      array (
        'time' => '86–90',
        'title' => 'Post-change note',
        'text' => '4 věty: změna, důkaz, validace, rollback stav.',
      ),
    ),
    'topology' => 
    array (
      'title' => 'Production path',
      'lines' => 
      array (
        0 => 'Client → DNS → LB/Nginx :443',
        1 => '                  │ TLS termination',
        2 => '                  └→ app-v2 127.0.0.1:9000',
        3 => '                         │',
        4 => '                       database',
        5 => 'Monitoring → /health + /metrics',
      ),
    ),
    'steps' => 
    array (
      0 => 
      array (
        'id' => 'change_plan',
        'time' => '12 min',
        'title' => '01 · Change plan před změnou',
        'kind' => 'knowledge',
        'xp' => 30,
        'knowledge' => 
        array (
          0 => 'change-management',
        ),
        'intro' => 'Změna: přesun proxy upstreamu z app-v1:8000 na app-v2:9000 + nový TLS certifikát.',
        'tasks' => 
        array (
          0 => 'Dokonči Change Management Knowledge Tour.',
          1 => 'Napiš 3 prechecky.',
          2 => 'Napiš success criteria.',
          3 => 'Napiš jednoznačný rollback krok.',
        ),
      ),
      1 => 
      array (
        'id' => 'proxy',
        'time' => '15 min',
        'title' => '02 · Reverse proxy a upstream',
        'kind' => 'quiz',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'reverse-proxy',
        ),
        'demo' => 
        array (
          'label' => 'Evidence',
          'lines' => 
          array (
            0 => 'curl https://app.example.cz → HTTP/2 502',
            1 => 'nginx error.log → connect() failed (111) while connecting to upstream',
            2 => 'curl http://127.0.0.1:9000/health → connection refused',
          ),
        ),
        'question' => 'Kde je teď nejsilnější hypotéza?',
        'options' => 
        array (
          0 => 'DNS resolver klienta.',
          1 => 'Upstream aplikace / port mezi Nginx a app-v2.',
          2 => 'TLS certifikát klienta.',
        ),
        'correct' => 1,
        'explanation' => 'Klient dostal HTTP odpověď od Nginx. Proxy ale nedokáže spojit upstream a lokální health test to potvrzuje.',
      ),
      2 => 
      array (
        'id' => 'tls',
        'time' => '15 min',
        'title' => '03 · TLS renewal bez falešného pocitu bezpečí',
        'kind' => 'quiz',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'tls-certificates',
        ),
        'demo' => 
        array (
          'label' => 'openssl s_client',
          'lines' => 
          array (
            0 => 'subject=CN=app.example.cz',
            1 => 'notAfter=Sep 10 12:00:00 2026 GMT',
            2 => 'Verify return code: 0 (ok)',
          ),
        ),
        'question' => 'Datum je 11. září 2026. Jaký je závěr?',
        'options' => 
        array (
          0 => 'Certifikát je podle notAfter expirovaný a je potřeba nasadit nový.',
          1 => 'Certifikát je určitě platný dalších 30 dní.',
          2 => 'Stačí restartovat DNS.',
        ),
        'correct' => 0,
        'explanation' => 'notAfter je v minulosti. Po nasazení nového certifikátu je nutné ověřit, co služba skutečně prezentuje.',
        'tasks' => 
        array (
          0 => 'Po renew/reload znovu spusť externí TLS test.',
          1 => 'Zkontroluj SAN pro app.example.cz.',
        ),
      ),
      3 => 
      array (
        'id' => 'logs',
        'time' => '16 min',
        'title' => '04 · Korelace monitoringu a logů',
        'kind' => 'knowledge',
        'xp' => 30,
        'knowledge' => 
        array (
          0 => 'logs-monitoring',
        ),
        'intro' => 'Ve 14:05 po deployi vyskočí 502. Nečti celý log — začni časem a requestem.',
        'tasks' => 
        array (
          0 => 'Dokonči Logs & Monitoring Knowledge Tour.',
          1 => 'Najdi 14:05 v access logu.',
          2 => 'Najdi odpovídající error log.',
          3 => 'Porovnej app log ve stejném čase.',
          4 => 'Sepiš jednu hypotézu, kterou data vylučují.',
        ),
      ),
      4 => 
      array (
        'id' => 'deploy',
        'time' => '18 min',
        'title' => '05 · Deploy + validační matice',
        'kind' => 'manual',
        'xp' => 35,
        'knowledge' => 
        array (
          0 => 'diagnostics',
        ),
        'intro' => 'Upstream už odpovídá. Proveď change jako řízený postup.',
        'tasks' => 
        array (
          0 => 'nginx -t / syntax check',
          1 => 'reload bez zbytečného restartu',
          2 => 'curl externí URL a očekávej 200',
          3 => 'ověř TLS certifikát',
          4 => 'ověř /health a monitoring',
          5 => 'zkontroluj error rate 5–10 minut',
        ),
        'done_label' => 'Všech 6 validačních bodů je hotových',
      ),
      5 => 
      array (
        'id' => 'failure',
        'time' => '14 min',
        'title' => '06 · Failure injection: špatný port po deployi',
        'kind' => 'quiz',
        'xp' => 35,
        'intro' => 'Po změně se error rate zvedne na 35 %. Nginx config ukazuje proxy_pass http://127.0.0.1:9001, app poslouchá na 9000. Rollback je připravený a trvá 20 sekund.',
        'question' => 'Jaký je nejbezpečnější postup v produkci?',
        'options' => 
        array (
          0 => 'Nechat chybu běžet a dlouze zkoumat.',
          1 => 'Okamžitě obnovit známý funkční stav rollbackem, stabilizovat službu a teprve potom analyzovat/opravit change.',
          2 => 'Vypnout monitoring.',
        ),
        'correct' => 1,
        'explanation' => 'Při výrazném dopadu a připraveném rychlém rollbacku je prioritou obnova služby. Analýza může pokračovat po stabilizaci.',
        'tasks' => 
        array (
          0 => 'Po rollbacku ověř user path.',
          1 => 'Ověř návrat error rate k baseline.',
          2 => 'Zapiš chybný parametr do post-change note.',
        ),
      ),
    ),
    'finisher' => 
    array (
      'title' => 'Rychlík · Canary 10 %',
      'duration' => '15–25 min',
      'text' => 'Navrhni, jak bys stejnou změnu nasadil nejdřív pro 10 % provozu a podle jakých metrik bys rozhodl pokračovat/rollback.',
      'deliverables' => 
      array (
        0 => '3 success metrics',
        1 => '2 stop conditions',
        2 => 'krátký validační plán',
      ),
    ),
  ),
  'extendedLessons' => 
  array (
    0 => 
    array (
      'id' => 'prod_observability',
      'number' => 3,
      'title' => 'Lekce 3 · Observability: logy, metriky, health',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Získat jistotu v tom, jak z monitoringu a logů vytvořit důkaz místo intuice.',
      'knowledge' => 
      array (
        0 => 'logs-monitoring',
        1 => 'diagnostics',
        2 => 'reverse-proxy',
        3 => 'tls-certificates',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'SLI/SLO mindset',
        ),
        1 => 
        array (
          'time' => '12–30',
          'title' => 'Access/error log',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Health + metrics',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Proxy evidence',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Incident correlation',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Postmortem mini',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'signals',
          'time' => '12 min',
          'title' => '01 · Tři signály',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'logs-monitoring',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vyber latency, error rate a availability.',
            1 => 'Definuj baseline.',
          ),
        ),
        1 => 
        array (
          'id' => 'logs',
          'time' => '18 min',
          'title' => '02 · Korelace logů',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Najdi request v access logu.',
            1 => 'Najdi stejný čas v error logu.',
            2 => 'Najdi odpovídající app log.',
          ),
        ),
        2 => 
        array (
          'id' => 'health',
          'time' => '15 min',
          'title' => '03 · Health + metrics',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni /health.',
            1 => 'Navrhni 3 metriky.',
            2 => 'Nezveřejňuj /metrics všem.',
          ),
        ),
        3 => 
        array (
          'id' => 'proxy',
          'time' => '17 min',
          'title' => '04 · Proxy evidence',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Rozliš 502 od 404.',
            1 => 'Ověř upstream lokálně.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '18 min',
          'title' => '05 · Incident correlation',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Nginx vrací 502 a app health na upstream portu neodpovídá. Co je nejsilnější hypotéza?',
          'options' => 
          array (
            0 => 'Upstream aplikace/port mezi proxy a backendem.',
            1 => 'DNS klienta, protože HTTP odpověď už přišla.',
            2 => 'Barva terminálu.',
          ),
          'correct' => 0,
          'explanation' => '502 a nefunkční upstream health ukazují mezi proxy a backend.',
        ),
        5 => 
        array (
          'id' => 'postmortem',
          'time' => '10 min',
          'title' => '06 · Mini postmortem',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Co se stalo.',
            1 => 'Jaký byl důkaz.',
            2 => 'Jak tomu příště předejít.',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'id' => 'prod_release_drill',
      'number' => 4,
      'title' => 'Lekce 4 · Release drill: bezpečný deploy a rollback',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Projít release jako řízenou změnu: precheck, canary, validace, stop condition a rollback.',
      'knowledge' => 
      array (
        0 => 'change-management',
        1 => 'reverse-proxy',
        2 => 'tls-certificates',
        3 => 'logs-monitoring',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Change ticket',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Precheck',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Canary',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Validace',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Failure injection',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Rozhodnutí',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'ticket',
          'time' => '12 min',
          'title' => '01 · Change ticket',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'change-management',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Definuj scope.',
            1 => 'Success criteria.',
            2 => 'Rollback krok.',
          ),
        ),
        1 => 
        array (
          'id' => 'precheck',
          'time' => '16 min',
          'title' => '02 · Precheck',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Config syntax.',
            1 => 'Disk/CPU baseline.',
            2 => 'Health aktuální verze.',
            3 => 'Backup/rollback artefakt.',
          ),
        ),
        2 => 
        array (
          'id' => 'canary',
          'time' => '17 min',
          'title' => '03 · Canary 10 %',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Navrhni 10% routing.',
            1 => 'Definuj 3 metriky.',
            2 => 'Definuj 2 stop conditions.',
          ),
        ),
        3 => 
        array (
          'id' => 'validate',
          'time' => '17 min',
          'title' => '04 · Validace user path',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
            1 => 'tls-certificates',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'DNS.',
            1 => 'TLS.',
            2 => 'HTTP status.',
            3 => 'User flow.',
            4 => 'Monitoring.',
          ),
        ),
        4 => 
        array (
          'id' => 'failure',
          'time' => '18 min',
          'title' => '05 · Failure injection',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Po canary error rate stoupne z 0,5 % na 12 % a stop condition je 3 %. Co uděláš?',
          'options' => 
          array (
            0 => 'Zastavím rollout a vrátím canary do známého funkčního stavu.',
            1 => 'Pokračuji na 100 %, protože už jsme začali.',
            2 => 'Vypnu alert.',
          ),
          'correct' => 0,
          'explanation' => 'Stop condition existuje právě proto, aby se rozhodnutí nedělalo pod tlakem intuitivně.',
        ),
        5 => 
        array (
          'id' => 'decision',
          'time' => '10 min',
          'title' => '06 · Release note',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Rozhodnutí continue/rollback.',
            1 => 'Důkaz.',
            2 => 'Co změnit před dalším pokusem.',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'id' => 'ops_http_lb',
      'number' => 5,
      'title' => 'Lekce 5 · HTTP observability + load balancing',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Číst HTTP odpověď jako diagnostický důkaz a pochopit základní chování load balanceru při zdravém i degradovaném backendu.',
      'knowledge' => 
      array (
        0 => 'http-observability',
        1 => 'load-balancing',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'HTTP evidence',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Headers',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Load balancer',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Health checks',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Degradace',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Postmortem',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'http',
          'time' => '15 min',
          'title' => '01 · Status není jen číslo',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'http-observability',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Rozliš 2xx/3xx/4xx/5xx.',
            1 => 'Přečti Server, Location a Retry-After.',
            2 => 'Urči, co odpověď dokazuje.',
          ),
        ),
        1 => 
        array (
          'id' => 'headers',
          'time' => '15 min',
          'title' => '02 · curl -I jako rychlá sonda',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Interpretuj status.',
            1 => 'Najdi redirect.',
            2 => 'Najdi cache/trace header.',
          ),
        ),
        2 => 
        array (
          'id' => 'lb',
          'time' => '15 min',
          'title' => '03 · Rozdělení provozu',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'load-balancing',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přepínej round-robin/weighted.',
            1 => 'Sleduj distribuci requestů.',
            2 => 'Vyřaď unhealthy backend.',
          ),
        ),
        3 => 
        array (
          'id' => 'health',
          'time' => '17 min',
          'title' => '04 · Health check',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni /health check.',
            1 => 'Urči interval a fail threshold.',
            2 => 'Nespoléhej jen na otevřený port.',
          ),
        ),
        4 => 
        array (
          'id' => 'degrade',
          'time' => '18 min',
          'title' => '05 · Jeden backend padá',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Load balancer stále posílá provoz na backend, který vrací 500. Co je potřeba zlepšit?',
          'options' => 
          array (
            0 => 'Health check a pravidla vyřazení unhealthy backendu.',
            1 => 'DNS TTL klienta.',
            2 => 'Velikost SSH klíče.',
          ),
          'correct' => 0,
          'explanation' => 'Load balancer musí mít signál, podle kterého nezdravý backend přestane dostávat běžný provoz.',
        ),
        5 => 
        array (
          'id' => 'post',
          'time' => '10 min',
          'title' => '06 · Mini postmortem',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Napiš symptom.',
            1 => 'Napiš důkaz.',
            2 => 'Napiš preventivní kontrolu.',
          ),
        ),
      ),
    ),
    3 => 
    array (
      'id' => 'ops_canary_restore',
      'number' => 6,
      'title' => 'Lekce 6 · Canary release + backup/restore drill',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Provést řízené nasazení na malou část provozu a nacvičit rozhodnutí rollback vs. restore na základě měřitelných signálů.',
      'knowledge' => 
      array (
        0 => 'canary-release',
        1 => 'backup-restore',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Baseline',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Canary 10 %',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Metriky',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Decision gate',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Restore drill',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Runbook',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'baseline',
          'time' => '12 min',
          'title' => '01 · Než změníš produkci',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Zapiš baseline error rate.',
            1 => 'Zapiš latency p95.',
            2 => 'Ověř poslední použitelný backup.',
          ),
        ),
        1 => 
        array (
          'id' => 'canary',
          'time' => '16 min',
          'title' => '02 · Canary místo big-bang',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'canary-release',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Nastav 10 % provozu na v2.',
            1 => 'Porovnej v1 vs. v2.',
            2 => 'Definuj stop podmínku.',
          ),
        ),
        2 => 
        array (
          'id' => 'metrics',
          'time' => '17 min',
          'title' => '03 · Rozhodnutí podle dat',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sleduj error rate.',
            1 => 'Sleduj latency.',
            2 => 'Sleduj business/user check.',
          ),
        ),
        3 => 
        array (
          'id' => 'gate',
          'time' => '17 min',
          'title' => '04 · Promote nebo rollback',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Canary v2 má 4× vyšší error rate než baseline, latency je horší a trend trvá 5 minut. Co je nejbezpečnější další krok?',
          'options' => 
          array (
            0 => 'Zastavit rollout a rollbackovat canary podle předem připraveného plánu.',
            1 => 'Rozšířit v2 na 100 %, aby bylo více dat.',
            2 => 'Smazat monitoring alert.',
          ),
          'correct' => 0,
          'explanation' => 'Canary existuje právě proto, aby problém zastavil před plošným dopadem.',
        ),
        4 => 
        array (
          'id' => 'restore',
          'time' => '18 min',
          'title' => '05 · Restore drill',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'backup-restore',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Vyber správný backup.',
            1 => 'Ověř integritu.',
            2 => 'Obnov do testovacího cíle.',
            3 => 'Proveď funkční kontrolu.',
          ),
        ),
        5 => 
        array (
          'id' => 'runbook',
          'time' => '10 min',
          'title' => '06 · Runbook',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Sepiš 5 kroků rollout/rollback.',
            1 => 'Uveď ownera a success criteria.',
            2 => 'Uveď restore checkpoint.',
          ),
        ),
      ),
    ),
    4 => 
    array (
      'id' => 'ops_slo_postmortem',
      'number' => 7,
      'title' => 'Lekce 7 · SLO, error budget a postmortem',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést provozní data na rozhodnutí: kdy pokračovat v release, kdy stabilizovat a jak z incidentu vytvořit konkrétní follow-up.',
      'knowledge' => 
      array (
        0 => 'slo-postmortem',
        1 => 'http-observability',
        2 => 'canary-release',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'SLI/SLO',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Error budget',
        ),
        2 => 
        array (
          'time' => '30–47',
          'title' => 'Incident impact',
        ),
        3 => 
        array (
          'time' => '47–64',
          'title' => 'Timeline',
        ),
        4 => 
        array (
          'time' => '64–82',
          'title' => 'Postmortem',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Decision gate',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'slo',
          'time' => '15 min',
          'title' => '01 · SLI → SLO',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'slo-postmortem',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči SLO Lab.',
            1 => 'Vyber user-facing SLI.',
            2 => 'Nastav 30denní SLO.',
          ),
        ),
        1 => 
        array (
          'id' => 'budget',
          'time' => '15 min',
          'title' => '02 · Error budget',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Spočítej tolerovaný čas.',
            1 => 'Porovnej s incidentem.',
            2 => 'Rozhodni, zda je budget vyčerpán.',
          ),
        ),
        2 => 
        array (
          'id' => 'impact',
          'time' => '17 min',
          'title' => '03 · Dopad není jen uptime',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'http-observability',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči počet chybných requestů.',
            1 => 'Urči délku dopadu.',
            2 => 'Urči uživatelský symptom.',
          ),
        ),
        3 => 
        array (
          'id' => 'timeline',
          'time' => '17 min',
          'title' => '04 · Evidence timeline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď alert, deploy, symptom, rollback.',
            1 => 'Odděl fakta od domněnek.',
            2 => 'Označ decision point.',
          ),
        ),
        4 => 
        array (
          'id' => 'postmortem',
          'time' => '18 min',
          'title' => '05 · Blameless postmortem',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Root cause.',
            1 => 'Contributing factors.',
            2 => '3 follow-up akce s vlastníkem a termínem.',
          ),
        ),
        5 => 
        array (
          'id' => 'gate',
          'time' => '8 min',
          'title' => '06 · Release gate',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Error budget je vyčerpaný a nový release není urgentní. Jaký je nejlepší default?',
          'options' => 
          array (
            0 => 'Zpomalit rizikové změny a nejdřív obnovit spolehlivost.',
            1 => 'Přidat traffic na canary na 100 %.',
            2 => 'Vypnout SLO alert.',
          ),
          'correct' => 0,
          'explanation' => 'Error budget má ovlivnit tempo změn a dát prostor stabilizaci.',
        ),
      ),
    ),
    5 => 
    array (
      'id' => 'ops_iac_drift',
      'number' => 8,
      'title' => 'Lekce 8 · Infrastructure as Code + drift',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Řídit produkční konfiguraci jako verzovaný desired state, kontrolovat diff před změnou a bezpečně řešit configuration drift.',
      'knowledge' => 
      array (
        0 => 'infrastructure-as-code',
        1 => 'config-drift',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Desired state',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Plan',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Review',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Drift',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Reconcile',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Runbook',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'desired',
          'time' => '15 min',
          'title' => '01 · Desired state',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'infrastructure-as-code',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Plan → Apply Lab.',
            1 => 'Popiš desired state.',
            2 => 'Urči source of truth.',
          ),
        ),
        1 => 
        array (
          'id' => 'plan',
          'time' => '15 min',
          'title' => '02 · Plan / diff',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přečti diff.',
            1 => 'Urči blast radius.',
            2 => 'Označ neočekávanou změnu.',
          ),
        ),
        2 => 
        array (
          'id' => 'review',
          'time' => '15 min',
          'title' => '03 · Review gate',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozděl změnu na malý scope.',
            1 => 'Přidej success criteria.',
            2 => 'Připrav rollback.',
          ),
        ),
        3 => 
        array (
          'id' => 'drift',
          'time' => '17 min',
          'title' => '04 · Configuration drift',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'config-drift',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Drift Detection Lab.',
            1 => 'Porovnej desired/actual.',
            2 => 'Zjisti původ driftu.',
          ),
        ),
        4 => 
        array (
          'id' => 'reconcile',
          'time' => '18 min',
          'title' => '05 · Reconcile',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Vyber source of truth.',
            1 => 'Sjednoť runtime a repo.',
            2 => 'Validuj po změně.',
          ),
        ),
        5 => 
        array (
          'id' => 'runbook',
          'time' => '10 min',
          'title' => '06 · IaC runbook',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Jaký je bezpečný default před apply?',
          'options' => 
          array (
            0 => 'Zkontrolovat plan/diff, scope, rollback a success criteria.',
            1 => 'Spustit apply přímo v produkci bez review.',
            2 => 'Vypnout monitoring.',
          ),
          'correct' => 0,
          'explanation' => 'IaC snižuje riziko jen tehdy, když workflow obsahuje kontrolní body.',
        ),
      ),
    ),
    6 => 
    array (
      'id' => 'ops_performance_capacity',
      'number' => 9,
      'title' => 'Lekce 9 · Performance + capacity engineering',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Najít bottleneck pomocí latency/throughput/saturation a převést měření na realistický plán kapacity s headroomem.',
      'knowledge' => 
      array (
        0 => 'performance-engineering',
        1 => 'capacity-planning',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Baseline',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Percentiles',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Saturation',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Load test',
        ),
        4 => 
        array (
          'time' => '62–82',
          'title' => 'Capacity decision',
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
          'id' => 'baseline',
          'time' => '15 min',
          'title' => '01 · Performance baseline',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'performance-engineering',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Latency & Saturation Lab.',
            1 => 'Zapiš p50/p95/error rate.',
            2 => 'Najdi jeden resource signal.',
          ),
        ),
        1 => 
        array (
          'id' => 'percentiles',
          'time' => '15 min',
          'title' => '02 · Percentiles',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Porovnej p50 a p95.',
            1 => 'Najdi tail latency.',
            2 => 'Vysvětli uživatelský dopad.',
          ),
        ),
        2 => 
        array (
          'id' => 'saturation',
          'time' => '15 min',
          'title' => '03 · Bottleneck',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sleduj CPU/I/O/queue.',
            1 => 'Najdi korelaci s latency.',
            2 => 'Formuluj hypotézu.',
          ),
        ),
        3 => 
        array (
          'id' => 'load',
          'time' => '17 min',
          'title' => '04 · Capacity lab',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'capacity-planning',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Capacity & Headroom Lab.',
            1 => 'Najdi bod degradace.',
            2 => 'Definuj cílovou zátěž.',
          ),
        ),
        4 => 
        array (
          'id' => 'decision',
          'time' => '20 min',
          'title' => '05 · Scale decision',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Přidej headroom.',
            1 => 'Porovnej scale up/out/optimize.',
            2 => 'Otestuj failover scénář.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Systém splňuje SLO do 700 RPS, cíl je 900 RPS a potřebuješ 25 % rezervu. Co plyne?',
          'options' => 
          array (
            0 => 'Současná kapacita nestačí a je potřeba scale/optimalizace před cílovým provozem.',
            1 => 'Stačí ignorovat headroom.',
            2 => 'Snížit monitoring interval na nulu.',
          ),
          'correct' => 0,
          'explanation' => 'Kapacita musí pokrýt cíl i bezpečnou rezervu.',
        ),
      ),
    ),
    7 => 
    array (
      'id' => 'ops_systemd_dependencies',
      'number' => 10,
      'title' => 'Lekce 10 · systemd do hloubky: dependencies, restart policy, failure',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.',
      'knowledge' => 
      array (
        0 => 'systemd-advanced',
        1 => 'logs-monitoring',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Unit anatomy',
          'text' => 'Rozliš Unit/Service/Install.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Dependencies',
          'text' => 'Rozliš After/Wants/Requires konceptuálně.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Restart loop',
          'text' => 'Z logu zjisti proč proces padá.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Ordering',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Drop-in/override mindset',
          'text' => 'Navrhni minimální override místo kopie celého unit souboru.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Validate',
          'text' => 'daemon-reload konceptuálně.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'unit',
          'time' => '15 min',
          'title' => '01 · Unit anatomy',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'systemd-advanced',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš Unit/Service/Install.',
            1 => 'Najdi ExecStart, User a Restart.',
          ),
        ),
        1 => 
        array (
          'id' => 'deps',
          'time' => '15 min',
          'title' => '02 · Dependencies',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš After/Wants/Requires konceptuálně.',
            1 => 'Nakresli dependency chain služby.',
          ),
        ),
        2 => 
        array (
          'id' => 'failure',
          'time' => '15 min',
          'title' => '03 · Restart loop',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Z logu zjisti proč proces padá.',
            1 => 'Nezvyšuj restart aggressiveness před opravou příčiny.',
          ),
        ),
        3 => 
        array (
          'id' => 'after',
          'time' => '12 min',
          'title' => '04 · Ordering',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co typicky vyjadřuje After=?',
          'options' => 
          array (
            0 => 'Pořadí startu vůči jiné unit; samo o sobě nemusí vytvářet tvrdou závislost.',
            1 => 'Firewall allow rule.',
            2 => 'DNS priority.',
          ),
          'correct' => 0,
          'explanation' => 'Ordering a dependency nejsou totéž.',
        ),
        4 => 
        array (
          'id' => 'override',
          'time' => '15 min',
          'title' => '05 · Drop-in/override mindset',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni minimální override místo kopie celého unit souboru.',
            1 => 'Zapiš rollback.',
          ),
        ),
        5 => 
        array (
          'id' => 'validate',
          'time' => '15 min',
          'title' => '06 · Validate',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'daemon-reload konceptuálně.',
            1 => 'Status + log + health check po změně.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Unit anatomy.',
        1 => 'Dependency diagram.',
        2 => 'Failure evidence.',
        3 => 'Override + rollback.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej testovací unit.',
        1 => 'Důraz na evidence před restartem.',
      ),
    ),
    8 => 
    array (
      'id' => 'ops_storage_filesystems',
      'number' => 11,
      'title' => 'Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.',
      'knowledge' => 
      array (
        0 => 'storage-filesystems',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Storage layers',
          'text' => 'Device → partition/LV → filesystem → mount point.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'df/du mindset',
          'text' => 'Zjisti, který filesystem je plný.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Log growth',
          'text' => 'Najdi podezřelý růst logu.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Inodes',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Mount validation',
          'text' => 'Ověř správný mount point po reboot scénáři.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Prevence',
          'text' => 'Navrhni monitoring kapacity + retention.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'layers',
          'time' => '15 min',
          'title' => '01 · Storage layers',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'storage-filesystems',
          ),
          'tasks' => 
          array (
            0 => 'Device → partition/LV → filesystem → mount point.',
            1 => 'Rozliš kapacitu a inode.',
          ),
        ),
        1 => 
        array (
          'id' => 'measure',
          'time' => '15 min',
          'title' => '02 · df/du mindset',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zjisti, který filesystem je plný.',
            1 => 'Teprve potom hledej velké adresáře.',
          ),
        ),
        2 => 
        array (
          'id' => 'logs',
          'time' => '15 min',
          'title' => '03 · Log growth',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Najdi podezřelý růst logu.',
            1 => 'Nevymaž náhodně aktivní log bez pochopení služby.',
          ),
        ),
        3 => 
        array (
          'id' => 'inode',
          'time' => '12 min',
          'title' => '04 · Inodes',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Filesystem hlásí volné GB, ale nelze vytvořit soubor. Co může být problém?',
          'options' => 
          array (
            0 => 'Vyčerpané inodes / příliš mnoho souborů.',
            1 => 'DNS cache.',
            2 => 'TLS SAN.',
          ),
          'correct' => 0,
          'explanation' => 'Kapacita v bajtech není jediný limit filesystemu.',
        ),
        4 => 
        array (
          'id' => 'mount',
          'time' => '15 min',
          'title' => '05 · Mount validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř správný mount point po reboot scénáři.',
            1 => 'Zapiš bezpečný recovery postup.',
          ),
        ),
        5 => 
        array (
          'id' => 'prevent',
          'time' => '15 min',
          'title' => '06 · Prevence',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni monitoring kapacity + retention.',
            1 => 'Definuj threshold a akci.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Storage diagram.',
        1 => 'df vs du vs inode vysvětlení.',
        2 => 'Incident disk full – 5 kroků.',
        3 => 'Preventivní monitoring.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nedávej studentům mazat skutečné systémové logy.',
        1 => 'Pracujte s připravenou strukturou dat.',
      ),
    ),
    9 => 
    array (
      'id' => 'ops_backup_restore',
      'number' => 12,
      'title' => 'Lekce 12 · Backup strategie: RPO/RTO a restore drill',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.',
      'knowledge' => 
      array (
        0 => 'backup-strategy',
        1 => 'backup-restore',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'RPO/RTO',
          'text' => 'RPO = kolik dat smíš ztratit.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Co zálohovat',
          'text' => 'Data, konfigurace, metadata/secrets odděleně podle rizika.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Restore drill',
          'text' => 'Obnov do testovacího cíle.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Backup vs restore',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Integrity + app validation',
          'text' => 'Ověř soubory/databázi podle typu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Runbook',
          'text' => 'Napiš restore kroky, odpovědnosti a stop conditions.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'rpo',
          'time' => '15 min',
          'title' => '01 · RPO/RTO',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'backup-strategy',
          ),
          'tasks' => 
          array (
            0 => 'RPO = kolik dat smíš ztratit.',
            1 => 'RTO = jak dlouho může trvat obnova.',
          ),
        ),
        1 => 
        array (
          'id' => 'strategy',
          'time' => '15 min',
          'title' => '02 · Co zálohovat',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Data, konfigurace, metadata/secrets odděleně podle rizika.',
            1 => 'Definuj retention.',
          ),
        ),
        2 => 
        array (
          'id' => 'restore',
          'time' => '15 min',
          'title' => '03 · Restore drill',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Obnov do testovacího cíle.',
            1 => 'Nenič produkční data během testu.',
          ),
        ),
        3 => 
        array (
          'id' => 'backup',
          'time' => '12 min',
          'title' => '04 · Backup vs restore',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Kdy je backup skutečně důvěryhodný?',
          'options' => 
          array (
            0 => 'Když byl obnoven a výsledek ověřen.',
            1 => 'Když job skončil zelenou ikonou.',
            2 => 'Když je soubor velký.',
          ),
          'correct' => 0,
          'explanation' => 'Bez restore testu neznáš reálnou obnovitelnost.',
        ),
        4 => 
        array (
          'id' => 'integrity',
          'time' => '15 min',
          'title' => '05 · Integrity + app validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř soubory/databázi podle typu.',
            1 => 'Spusť aplikační health test.',
          ),
        ),
        5 => 
        array (
          'id' => 'runbook',
          'time' => '15 min',
          'title' => '06 · Runbook',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Napiš restore kroky, odpovědnosti a stop conditions.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'RPO/RTO.',
        1 => 'Backup matrix.',
        2 => 'Restore evidence.',
        3 => 'Runbook.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Vhodné pro simulovaný dataset.',
        1 => 'Uč rozdíl backup success vs business recovery.',
      ),
    ),
    10 => 
    array (
      'id' => 'ops_hardening',
      'number' => 13,
      'title' => 'Lekce 13 · Hardening Linux služby: SSH, firewall, aktualizace a minimální práva',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout bezpečné minimum služby bez „security by checkbox“ a současně zachovat ověřitelný přístup a rollback.',
      'knowledge' => 
      array (
        0 => 'ssh-hardening',
        1 => 'backup-strategy',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Attack surface',
          'text' => 'Sepiš vystavené služby.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'SSH baseline',
          'text' => 'Klíče, omezené účty a bezpečná práva.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Firewall scope',
          'text' => 'Povol management pouze z potřebné zóny/IP rozsahu.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Patch plan',
          'text' => 'Zjisti dopad aktualizace.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Lockout risk',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Security validation',
          'text' => 'Pozitivní test oprávněného přístupu.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'surface',
          'time' => '15 min',
          'title' => '01 · Attack surface',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'ssh-hardening',
          ),
          'tasks' => 
          array (
            0 => 'Sepiš vystavené služby.',
            1 => 'Odstraň/omez nepotřebné cesty přístupu.',
          ),
        ),
        1 => 
        array (
          'id' => 'ssh',
          'time' => '15 min',
          'title' => '02 · SSH baseline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Klíče, omezené účty a bezpečná práva.',
            1 => 'Nesdílej privátní klíče.',
          ),
        ),
        2 => 
        array (
          'id' => 'fw',
          'time' => '15 min',
          'title' => '03 · Firewall scope',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Povol management pouze z potřebné zóny/IP rozsahu.',
            1 => 'Připrav console/rollback cestu.',
          ),
        ),
        3 => 
        array (
          'id' => 'patch',
          'time' => '15 min',
          'title' => '04 · Patch plan',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zjisti dopad aktualizace.',
            1 => 'Definuj restart potřebu a validační test.',
          ),
        ),
        4 => 
        array (
          'id' => 'lockout',
          'time' => '12 min',
          'title' => '05 · Lockout risk',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co musíš řešit před zpřísněním vzdáleného SSH/firewall přístupu?',
          'options' => 
          array (
            0 => 'Ověřený alternativní přístup/rollback, aby ses nezamkl venku.',
            1 => 'Jen barvu promptu.',
            2 => 'TTL webového obrázku.',
          ),
          'correct' => 0,
          'explanation' => 'Hardening bez recovery plánu může vytvořit vlastní incident.',
        ),
        5 => 
        array (
          'id' => 'verify',
          'time' => '15 min',
          'title' => '06 · Security validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Pozitivní test oprávněného přístupu.',
            1 => 'Negativní test zakázané cesty.',
            2 => 'Audit log změny.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Attack surface list.',
        1 => 'SSH baseline.',
        2 => 'Firewall scope.',
        3 => 'Patch+rollback plán.',
        4 => 'Positive/negative validation.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Defenzivní lab; nepracovat s cizími systémy.',
        1 => 'Důraz na minimální scope a recovery.',
      ),
    ),
    11 => 
    array (
      'id' => 'ops_containers',
      'number' => 14,
      'title' => 'Lekce 14 · Containers: proces, image, volume a síť',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.',
      'knowledge' => 
      array (
        0 => 'containers-basics',
        1 => 'binding',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Container mental model',
          'text' => 'Image ≠ container.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Port mapping',
          'text' => 'Rozliš container port a host port.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Persistent data',
          'text' => 'Urči, která data musí přežít nový container.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Recreate',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Health + logs',
          'text' => 'Ověř status/health.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Incident',
          'text' => 'Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '15 min',
          'title' => '01 · Container mental model',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'containers-basics',
          ),
          'tasks' => 
          array (
            0 => 'Image ≠ container.',
            1 => 'Volume ≠ image layer.',
            2 => 'Port publish ≠ aplikace automaticky poslouchá.',
          ),
        ),
        1 => 
        array (
          'id' => 'ports',
          'time' => '15 min',
          'title' => '02 · Port mapping',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš container port a host port.',
            1 => 'Ověř bind/listener uvnitř služby.',
          ),
        ),
        2 => 
        array (
          'id' => 'volume',
          'time' => '15 min',
          'title' => '03 · Persistent data',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči, která data musí přežít nový container.',
            1 => 'Neukládej secrets do image.',
          ),
        ),
        3 => 
        array (
          'id' => 'restart',
          'time' => '12 min',
          'title' => '04 · Recreate',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co se typicky stane s daty uloženými jen ve writable layer containeru po jeho odstranění/recreate?',
          'options' => 
          array (
            0 => 'Mohou být ztracena; persistentní data patří do vhodného volume/storage.',
            1 => 'Automaticky se přesunou do DNS.',
            2 => 'Vždy se uloží do image registry.',
          ),
          'correct' => 0,
          'explanation' => 'Ephemeral runtime a persistent storage jsou oddělené koncepty.',
        ),
        4 => 
        array (
          'id' => 'health',
          'time' => '15 min',
          'title' => '05 · Health + logs',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř status/health.',
            1 => 'Přečti aplikační log před změnou.',
          ),
        ),
        5 => 
        array (
          'id' => 'incident',
          'time' => '15 min',
          'title' => '06 · Incident',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu.',
            1 => 'Navrhni nejmenší opravu + validaci.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Image/container/volume/network diagram.',
        1 => 'Port mapping.',
        2 => 'Persistent data decision.',
        3 => 'Container incident evidence.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Lze realizovat v Dockeru/Podmanu nebo čistě simulovat.',
        1 => 'Nezaváděj orchestraci dřív, než studenti chápou jednu instanci.',
      ),
    ),
    12 => 
    array (
      'id' => 'ops_automation_consistency',
      'number' => 15,
      'title' => 'Lekce 15 · Automatizace + configuration consistency',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.',
      'knowledge' => 
      array (
        0 => 'automation-shell',
        1 => 'config-drift',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Idempotentní změna',
          'text' => 'Nejdřív zjisti current state.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Preconditions',
          'text' => 'Ověř host, config a backup/rollback.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Drift',
          'text' => 'Porovnej deklarovaný a skutečný stav.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Automatizace',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Dry-run/plan',
          'text' => 'Vygeneruj plán změn.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Apply + validate',
          'text' => 'Po změně změř desired outcome.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'idempotent',
          'time' => '15 min',
          'title' => '01 · Idempotentní změna',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'automation-shell',
          ),
          'tasks' => 
          array (
            0 => 'Nejdřív zjisti current state.',
            1 => 'Pokud je desired state už splněn, nic zbytečně neměň.',
          ),
        ),
        1 => 
        array (
          'id' => 'guard',
          'time' => '15 min',
          'title' => '02 · Preconditions',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř host, config a backup/rollback.',
            1 => 'Při nesplněné podmínce bezpečně skonči.',
          ),
        ),
        2 => 
        array (
          'id' => 'drift',
          'time' => '15 min',
          'title' => '03 · Drift',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Porovnej deklarovaný a skutečný stav.',
            1 => 'Rozhodni, který je source of truth.',
          ),
        ),
        3 => 
        array (
          'id' => 'script',
          'time' => '12 min',
          'title' => '04 · Automatizace',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je horší než ruční postup?',
          'options' => 
          array (
            0 => 'Automatizace, která rychle a opakovaně provádí chybnou změnu bez guardů.',
            1 => 'Skript s dry-runem.',
            2 => 'Validace po změně.',
          ),
          'correct' => 0,
          'explanation' => 'Automatizace násobí dobré i špatné rozhodnutí.',
        ),
        4 => 
        array (
          'id' => 'dry',
          'time' => '15 min',
          'title' => '05 · Dry-run/plan',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vygeneruj plán změn.',
            1 => 'Zkontroluj scope před apply.',
          ),
        ),
        5 => 
        array (
          'id' => 'evidence',
          'time' => '15 min',
          'title' => '06 · Apply + validate',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Po změně změř desired outcome.',
            1 => 'Zapiš idempotentní druhý průchod bez změny.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Current vs desired state.',
        1 => 'Preconditions.',
        2 => 'Pseudo-script.',
        3 => 'Dry-run output.',
        4 => 'Validation.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Zadání drž defenzivní a v sandboxu.',
        1 => 'Hodnoť safe failure a idempotenci.',
      ),
    ),
    13 => 
    array (
      'id' => 'ops_packet_diagnostics',
      'number' => 16,
      'title' => 'Lekce 16 · Pokročilá síťová diagnostika: packet evidence + socket state',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Propojit packet capture, TCP stavy a serverový listener do jedné incidentní hypotézy.',
      'knowledge' => 
      array (
        0 => 'packet-diagnostics-advanced',
        1 => 'tcp',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Capture with question',
          'text' => 'Formuluj hypotézu před filtrem.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'SYN patterns',
          'text' => 'Rozliš timeout, RST a SYN-ACK.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Server socket',
          'text' => 'Porovnej capture s ss/lsof.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'RST',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Po TCP',
          'text' => 'Když TCP funguje, pokračuj TLS/HTTP podle symptomu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Incident timeline',
          'text' => 'Seřaď packet + server evidence podle času.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'capture',
          'time' => '15 min',
          'title' => '01 · Capture with question',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'packet-diagnostics-advanced',
          ),
          'tasks' => 
          array (
            0 => 'Formuluj hypotézu před filtrem.',
            1 => 'Zachyť jen potřebný provoz.',
          ),
        ),
        1 => 
        array (
          'id' => 'syn',
          'time' => '15 min',
          'title' => '02 · SYN patterns',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš timeout, RST a SYN-ACK.',
            1 => 'Propoj s firewallem/listenerem.',
          ),
        ),
        2 => 
        array (
          'id' => 'server',
          'time' => '15 min',
          'title' => '03 · Server socket',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Porovnej capture s ss/lsof.',
            1 => 'Ověř správný bind.',
          ),
        ),
        3 => 
        array (
          'id' => 'rst',
          'time' => '12 min',
          'title' => '04 · RST',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'SYN → okamžitý RST typicky znamená?',
          'options' => 
          array (
            0 => 'Cíl je dosažitelný, ale port/spojení je aktivně odmítnuté.',
            1 => 'DNS dotaz se nikdy neposlal.',
            2 => 'Klient nemá MAC adresu gateway.',
          ),
          'correct' => 0,
          'explanation' => 'RST je explicitní TCP odpověď.',
        ),
        4 => 
        array (
          'id' => 'tls',
          'time' => '15 min',
          'title' => '05 · Po TCP',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Když TCP funguje, pokračuj TLS/HTTP podle symptomu.',
            1 => 'Neskákej zpět k DHCP bez evidence.',
          ),
        ),
        5 => 
        array (
          'id' => 'timeline',
          'time' => '15 min',
          'title' => '06 · Incident timeline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď packet + server evidence podle času.',
            1 => 'Napiš root-cause boundary: co víš a co ještě ne.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Hypotéza.',
        1 => 'Packet pattern.',
        2 => 'Socket evidence.',
        3 => 'Layer boundary.',
        4 => 'Root cause nebo další test.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej předpřipravené pcapy nebo izolovaný lab.',
        1 => 'Nezachytávej citlivý provoz třetích osob.',
      ),
    ),
    14 => 
    array (
      'id' => 'ops_incident_runbook',
      'number' => 17,
      'title' => 'Lekce 17 · Incident response: runbook, komunikace a blameless postmortem',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Řídit incident podle severity, rolí, timeline, mitigation a následného postmortemu bez chaosu a hledání viníka.',
      'knowledge' => 
      array (
        0 => 'incident-runbook',
        1 => 'slo-postmortem',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Incident roles',
          'text' => 'Incident lead, investigator, communicator.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Severity + impact',
          'text' => 'Popiš uživatelský dopad.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Mitigation first',
          'text' => 'Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Timeline',
          'text' => 'Zapisuj fakta s časem.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Postmortem',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Follow-up',
          'text' => 'Každá akce má ownera, prioritu a ověřitelný výsledek.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'roles',
          'time' => '15 min',
          'title' => '01 · Incident roles',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'incident-runbook',
          ),
          'tasks' => 
          array (
            0 => 'Incident lead, investigator, communicator.',
            1 => 'Odděl koordinaci od hluboké diagnostiky.',
          ),
        ),
        1 => 
        array (
          'id' => 'severity',
          'time' => '15 min',
          'title' => '02 · Severity + impact',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Popiš uživatelský dopad.',
            1 => 'Nastav prioritu podle dopadu, ne technické zajímavosti.',
          ),
        ),
        2 => 
        array (
          'id' => 'mitigate',
          'time' => '15 min',
          'title' => '03 · Mitigation first',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby.',
            1 => 'Root cause může pokračovat po stabilizaci.',
          ),
        ),
        3 => 
        array (
          'id' => 'timeline',
          'time' => '15 min',
          'title' => '04 · Timeline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zapisuj fakta s časem.',
            1 => 'Odděl fakta a hypotézy.',
          ),
        ),
        4 => 
        array (
          'id' => 'blame',
          'time' => '12 min',
          'title' => '05 · Postmortem',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je cílem blameless postmortemu?',
          'options' => 
          array (
            0 => 'Pochopit systémové faktory a definovat konkrétní preventivní akce.',
            1 => 'Najít jednoho člověka k potrestání.',
            2 => 'Vyhnout se technickým detailům.',
          ),
          'correct' => 0,
          'explanation' => 'Postmortem má zlepšit systém a proces.',
        ),
        5 => 
        array (
          'id' => 'actions',
          'time' => '15 min',
          'title' => '06 · Follow-up',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Každá akce má ownera, prioritu a ověřitelný výsledek.',
            1 => 'Vyber 2 nejdůležitější.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Severity/impact.',
        1 => 'Role assignment.',
        2 => 'Timeline.',
        3 => 'Mitigation.',
        4 => 'Postmortem: 2 follow-up actions.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Použij Project Workspace role Leader/Developer/QA/Presenter pro týmový incident.',
        1 => 'Hodnoť rozhodování a komunikaci stejně jako techniku.',
      ),
    ),
    15 => 
    array (
      'id' => 'ops_reliability_capstone',
      'number' => 18,
      'title' => 'Lekce 18 · Capstone: produkční reliability drill',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.',
      'knowledge' => 
      array (
        0 => 'systemd-advanced',
        1 => 'storage-filesystems',
        2 => 'backup-strategy',
        3 => 'ssh-hardening',
        4 => 'containers-basics',
        5 => 'automation-shell',
        6 => 'packet-diagnostics-advanced',
        7 => 'incident-runbook',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Triage',
          'text' => 'Urči impact/severity.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Evidence matrix',
          'text' => 'DNS/TCP/TLS/HTTP.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Mitigation',
          'text' => 'Rozhodni rollback/fix/failover podle evidence.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Recovery validation',
          'text' => 'Ověř user path, health, error rate a security boundary.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Prevent repeat',
          'text' => 'Navrhni guard/monitor/automation, který problém zachytí nebo omezí.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Postmortem defense',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'triage',
          'time' => '15 min',
          'title' => '01 · Triage',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči impact/severity.',
            1 => 'Zmraz riskantní změny.',
            2 => 'Rozděl role týmu.',
          ),
        ),
        1 => 
        array (
          'id' => 'evidence',
          'time' => '15 min',
          'title' => '02 · Evidence matrix',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'DNS/TCP/TLS/HTTP.',
            1 => 'Service/containers/logs/storage.',
            2 => 'Vyber nejmenší test pro každou hypotézu.',
          ),
        ),
        2 => 
        array (
          'id' => 'mitigation',
          'time' => '15 min',
          'title' => '03 · Mitigation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozhodni rollback/fix/failover podle evidence.',
            1 => 'Zapiš stop condition.',
          ),
        ),
        3 => 
        array (
          'id' => 'recovery',
          'time' => '15 min',
          'title' => '04 · Recovery validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř user path, health, error rate a security boundary.',
            1 => 'Při obnově dat proveď integrity check.',
          ),
        ),
        4 => 
        array (
          'id' => 'automation',
          'time' => '15 min',
          'title' => '05 · Prevent repeat',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni guard/monitor/automation, který problém zachytí nebo omezí.',
            1 => 'Neautomatizuj neověřený fix.',
          ),
        ),
        5 => 
        array (
          'id' => 'final',
          'time' => '12 min',
          'title' => '06 · Postmortem defense',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co nejlépe dokazuje zvládnutí capstone?',
          'options' => 
          array (
            0 => 'Konzistentní evidence chain, bezpečná obnova, validace a konkrétní preventivní kroky.',
            1 => 'Co nejvíc spuštěných příkazů.',
            2 => 'Jedna správná náhodná změna.',
          ),
          'correct' => 0,
          'explanation' => 'Reliability je opakovatelný rozhodovací proces.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Incident timeline.',
        1 => 'Evidence matrix.',
        2 => 'Mitigation + rollback.',
        3 => 'Validation matrix.',
        4 => 'Postmortem.',
        5 => '2 preventive actions.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Závěrečný týmový drill 4.A.',
        1 => 'Učitel může během scénáře injectovat další symptom, ale musí zachovat řešitelnost z evidence.',
      ),
    ),
    16 => 
    array (
      'id' => 'v30_4a_19',
      'number' => 19,
      'title' => 'Lekce 19 · Golden signals lab',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.',
      'knowledge' => 
      array (
        0 => 'golden-signals',
        1 => 'logs-monitoring',
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
            0 => 'golden-signals',
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
            0 => 'golden-signals',
            1 => 'logs-monitoring',
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
      'id' => 'v30_4a_20',
      'number' => 20,
      'title' => 'Lekce 20 · Reverse proxy evidence',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Odlišit DNS/TLS/proxy/upstream závadu pomocí minimálního test chainu.',
      'knowledge' => 
      array (
        0 => 'reverse-proxy',
        1 => 'http-observability',
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
            0 => 'reverse-proxy',
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
            0 => 'reverse-proxy',
            1 => 'http-observability',
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
      'id' => 'v30_4a_21',
      'number' => 21,
      'title' => 'Lekce 21 · Container networking',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.',
      'knowledge' => 
      array (
        0 => 'container-networking',
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
            0 => 'container-networking',
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
            0 => 'container-networking',
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
      'id' => 'v30_4a_22',
      'number' => 22,
      'title' => 'Lekce 22 · Restore game day',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.',
      'knowledge' => 
      array (
        0 => 'backup-restore',
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
            0 => 'backup-restore',
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
            0 => 'backup-restore',
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
      'id' => 'v30_4a_23',
      'number' => 23,
      'title' => 'Lekce 23 · Hardening audit',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.',
      'knowledge' => 
      array (
        0 => 'ssh-hardening',
        1 => 'ssh-hardening',
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
            0 => 'ssh-hardening',
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
            0 => 'ssh-hardening',
            1 => 'ssh-hardening',
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
      'id' => 'v30_4a_24',
      'number' => 24,
      'title' => 'Lekce 24 · Safe change & rollback',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Definovat stop conditions, rollback trigger a následnou end-to-end validaci.',
      'knowledge' => 
      array (
        0 => 'rollback-strategy',
        1 => 'change-management',
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
            0 => 'rollback-strategy',
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
            0 => 'rollback-strategy',
            1 => 'change-management',
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
      'id' => 'v30_4a_25',
      'number' => 25,
      'title' => 'Lekce 25 · Drift & automation',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.',
      'knowledge' => 
      array (
        0 => 'config-drift',
        1 => 'infrastructure-as-code',
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
            0 => 'config-drift',
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
            0 => 'config-drift',
            1 => 'infrastructure-as-code',
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
      'id' => 'v30_4a_26',
      'number' => 26,
      'title' => 'Lekce 26 · Performance capacity',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.',
      'knowledge' => 
      array (
        0 => 'performance-engineering',
        1 => 'capacity-planning',
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
            0 => 'performance-engineering',
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
            0 => 'performance-engineering',
            1 => 'capacity-planning',
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
      'id' => 'v30_4a_27',
      'number' => 27,
      'title' => 'Lekce 27 · Incident command',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.',
      'knowledge' => 
      array (
        0 => 'incident-command',
        1 => 'slo-postmortem',
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
            0 => 'incident-command',
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
            0 => 'incident-command',
            1 => 'slo-postmortem',
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
      'id' => 'v30_4a_28',
      'number' => 28,
      'title' => 'Lekce 28 · Reliability mastery',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Propojit observability, safe change, incident response a recovery do závěrečného reliability drill.',
      'knowledge' => 
      array (
        0 => 'golden-signals',
        1 => 'rollback-strategy',
        2 => 'incident-command',
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
            0 => 'golden-signals',
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
            0 => 'golden-signals',
            1 => 'rollback-strategy',
            2 => 'incident-command',
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
    'binding' => 
    array (
      'id' => 'adv-binding',
      'type' => 'binding',
      'title' => 'Service Binding: localhost funguje, LAN ne',
      'lead' => 'Měň bind adresu a firewall. Sleduj, které rozhraní proces skutečně obsluhuje a odkud je dostupný.',
      'task' => 'Zpřístupni aplikaci pouze přes serverovou LAN adresu 10.20.0.30:8000 — ne přes všechny interface.',
      'prediction' => 
      array (
        'q' => 'Proces poslouchá na 127.0.0.1:8000. Dostane se k němu přímo jiný počítač v LAN?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Loopback 127.0.0.1 je lokální pouze pro samotný host.',
        1 => '0.0.0.0 by fungovalo, ale úkol chce minimální expozici.',
        2 => 'Bindni na 10.20.0.30 a povol TCP/8000 z LAN.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je klíčový závěr?',
        'options' => 
        array (
          0 => 'Běžící proces je automaticky dostupný odkudkoli.',
          1 => 'Dostupnost závisí na bind adrese i firewallu.',
          2 => 'Binding ovlivňuje jen DNS.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'tcp' => 
    array (
      'id' => 'adv-tcp',
      'type' => 'tcp',
      'title' => 'TCP Handshake: timeout vs. connection refused',
      'lead' => 'Přepínej stav služby a firewallu a sleduj SYN/SYN-ACK/RST. Nauč se číst symptom jako důkaz.',
      'task' => 'Nastav situaci, kdy klient dostane „connection refused“ místo timeoutu.',
      'prediction' => 
      array (
        'q' => 'Který symptom typicky víc odpovídá aktivnímu odmítnutí/uzavřenému portu?',
        'options' => 
        array (
          0 => 'Connection refused',
          1 => 'Nekonečný DNS lookup',
          2 => 'Úspěšný 200 OK',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'DROP paket zahodí a klient obvykle čeká. RST je aktivní odpověď.',
        1 => 'Nech síť cestu povolenou, ale vypni listener služby.',
        2 => 'Firewall = allow, service listening = ne.',
      ),
      'conclusion' => 
      array (
        'q' => 'Proč se timeout a refused liší?',
        'options' => 
        array (
          0 => 'Jde o stejnou věc.',
          1 => 'Refused znamená aktivní zamítnutí/RST; timeout znamená, že očekávaná odpověď nepřišla.',
          2 => 'Refused je DNS chyba.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'diagnostics' => 
    array (
      'id' => 'adv-production',
      'type' => 'production',
      'title' => 'Produkční incident: 502 po deployi',
      'lead' => 'Simulovaný incident od monitoringu po reverse proxy. Volíš diagnostické příkazy, sbíráš evidenci, stanovíš root cause a musíš ověřit opravu.',
      'task' => 'Najdi proč Nginx vrací 502 po deployi. Neprováděj změnu, dokud evidence neukáže konkrétní příčinu.',
      'prediction' => 
      array (
        'q' => 'HTTP 502 od Nginx nejvíc naznačuje problém kde?',
        'options' => 
        array (
          0 => 'Mezi proxy a upstream aplikací',
          1 => 'V DHCP klienta',
          2 => 'Ve fontu webu',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Nejdřív zjisti, co už funguje: DNS, TLS, Nginx odpověď. Pak testuj upstream.',
        1 => 'Porovnej port v Nginx upstream konfiguraci s portem, na kterém aplikace skutečně poslouchá.',
        2 => 'Aplikace poslouchá na 127.0.0.1:8000, Nginx je nakonfigurován na 127.0.0.1:9000.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký je root cause incidentu?',
        'options' => 
        array (
          0 => 'Nginx upstream míří na chybný port 9000 místo 8000.',
          1 => 'DNS vrací špatnou IP.',
          2 => 'Server nemá default route.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'cidr' => 
    array (
      'id' => 'adv-cidr-plan',
      'type' => 'cidrplan',
      'title' => 'CIDR Planner: nejmenší subnet pro 50 hostů',
      'lead' => 'Měň prefix a sleduj počet použitelných adres i velikost bloku. Cílem není „větší je lepší“, ale rozumné sizing rozhodnutí.',
      'task' => 'Vyber nejmenší běžný IPv4 subnet, který pojme alespoň 50 hostů.',
      'prediction' => 
      array (
        'q' => 'Stačí /27 pro 50 běžných host adres?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => '/27 má 32 adres celkem.',
        1 => '/26 má 64 adres celkem a typicky 62 použitelných host adres.',
        2 => 'Vyber /26.',
      ),
      'conclusion' => 
      array (
        'q' => 'Proč je /26 vhodnější než /24?',
        'options' => 
        array (
          0 => 'Je nejmenší z nabízených variant, která splní kapacitu.',
          1 => 'Protože má nejvíc adres.',
          2 => 'Protože používá port 26.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'routing-advanced' => 
    array (
      'id' => 'adv-lpm',
      'type' => 'lpm',
      'title' => 'Longest Prefix Match: kterou route vybere kernel?',
      'lead' => 'Pro jeden destination vidíš několik odpovídajících rout. Vyber tu nejkonkrétnější.',
      'task' => 'Pro 10.20.30.55 vyber route, kterou má použít longest-prefix match.',
      'prediction' => 
      array (
        'q' => 'Když odpovídá /8, /16 i /24, která route je nejkonkrétnější?',
        'options' => 
        array (
          0 => '/8',
          1 => '/16',
          2 => '/24',
        ),
        'correct' => 2,
      ),
      'hints' => 
      array (
        0 => 'Delší prefix znamená menší a konkrétnější síť.',
        1 => '10.20.30.55 patří do 10.20.30.0/24.',
        2 => 'Vyber /24.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jak se vybírá route mezi více shodami?',
        'options' => 
        array (
          0 => 'Nejkratší prefix',
          1 => 'Nejdelší odpovídající prefix',
          2 => 'Vždy default',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'dns-advanced' => 
    array (
      'id' => 'adv-dns-migrate',
      'type' => 'dnsmigrate',
      'title' => 'DNS Migration: plánuj TTL před změnou',
      'lead' => 'Simuluj migraci služby na novou IP. Sleduj několik resolverů, které mají cache různě starou.',
      'task' => 'Nastav přípravu migrace tak, aby maximální doba staré cache byla krátká.',
      'prediction' => 
      array (
        'q' => 'Kdy je nejvhodnější snížit TTL pro plánovanou migraci?',
        'options' => 
        array (
          0 => 'Až po změně IP',
          1 => 'S předstihem před migrací',
          2 => 'Nikdy',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Staré vysoké TTL může zůstat v cache i po změně.',
        1 => 'Sniž TTL ještě před přepnutím A záznamu.',
        2 => 'Nastav pre-TTL 60 s.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co TTL ovlivňuje?',
        'options' => 
        array (
          0 => 'Jak dlouho může resolver odpověď cachovat',
          1 => 'TCP window',
          2 => 'SSH permissions',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'ssh-keys' => 
    array (
      'id' => 'adv-ssh-auth',
      'type' => 'sshkeylab',
      'title' => 'SSH Key Lab: proč publickey neprojde?',
      'lead' => 'Měň permissions privátního klíče, username a shodu veřejného klíče. Sleduj, v jaké fázi autentizace spojení skončí.',
      'task' => 'Nastav bezpečnou a funkční autentizaci veřejným klíčem.',
      'prediction' => 
      array (
        'q' => 'Je 0644 vhodné oprávnění soukromého SSH klíče?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Soukromý klíč nemá být čitelný ostatními uživateli.',
        1 => 'Ověř také username a public key na serveru.',
        2 => '0600 + správný user + matching key.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co server potřebuje pro public-key auth?',
        'options' => 
        array (
          0 => 'Soukromý klíč klienta',
          1 => 'Odpovídající veřejný klíč a správného uživatele',
          2 => 'DNS TXT se soukromým klíčem',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'permissions' => 
    array (
      'id' => 'adv-permissions',
      'type' => 'permissionslab',
      'title' => 'Permissions Lab: minimum potřebných práv',
      'lead' => 'Nastav mód pro soubor, který může deploy upravovat, webová skupina číst a ostatní nemají mít přístup.',
      'task' => 'Vyber nejmenší oprávnění odpovídající owner rw, group r, other nic.',
      'prediction' => 
      array (
        'q' => 'Který mód odpovídá rw-r-----?',
        'options' => 
        array (
          0 => '777',
          1 => '644',
          2 => '640',
          3 => '600',
        ),
        'correct' => 2,
      ),
      'hints' => 
      array (
        0 => 'r=4, w=2, x=1.',
        1 => 'Owner rw = 6, group r = 4, other nic = 0.',
        2 => 'Vyber 640.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký princip je správný?',
        'options' => 
        array (
          0 => 'Dávat 777, aby vše fungovalo',
          1 => 'Použít nejmenší nutná oprávnění',
          2 => 'Permissions neřešit, pokud je firewall',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'http-observability' => 
    array (
      'id' => 'ops-http',
      'type' => 'httpprobe',
      'title' => 'HTTP Probe Lab',
      'lead' => 'Měň status, redirect a latency. Sleduj, co lze bezpečně tvrdit z jedné odpovědi.',
      'task' => 'Izoluj stav 503 s funkčním TCP a správně interpretuj Retry-After.',
      'prediction' => 
      array (
        'q' => '503 znamená, že HTTP vrstva odpověděla?',
        'options' => 
        array (
          0 => 'Ano.',
          1 => 'Ne.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Status je HTTP důkaz.',
        1 => 'Nastav 503.',
        2 => 'Přidej Retry-After.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je dobrý observability signál?',
        'options' => 
        array (
          0 => 'Status + headers + latency v čase.',
          1 => 'Jen screenshot stránky.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'load-balancing' => 
    array (
      'id' => 'ops-lb',
      'type' => 'loadbalance',
      'title' => 'Load Balancer Lab',
      'lead' => 'Měň váhy backendů a health. Animace rozděluje 20 requestů a ukazuje chyby.',
      'task' => 'Vyřaď unhealthy web02 a doruč všechny requesty zdravému backendu.',
      'prediction' => 
      array (
        'q' => 'Má unhealthy backend dál dostávat běžný traffic?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Health check řídí pool.',
        1 => 'Nastav web02 unhealthy.',
        2 => 'Enable remove unhealthy.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co chrání uživatele?',
        'options' => 
        array (
          0 => 'Health-aware routing.',
          1 => 'Více DNS záznamů bez kontroly.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'canary-release' => 
    array (
      'id' => 'ops-canary',
      'type' => 'canary',
      'title' => 'Canary Lab: promote nebo rollback',
      'lead' => 'Měň podíl v2 a error rate. Graf ukazuje dopad na uživatele a decision gate.',
      'task' => 'Najdi stav, kdy canary musí být rollbackována podle threshold.',
      'prediction' => 
      array (
        'q' => 'Proč začít 10 %?',
        'options' => 
        array (
          0 => 'Omezíš blast radius.',
          1 => 'Je to rychlejší DNS.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Sleduj error rate.',
        1 => 'Nastav threshold.',
        2 => 'Pokud v2 překročí limit, rollback.',
      ),
      'conclusion' => 
      array (
        'q' => 'Canary je hlavně co?',
        'options' => 
        array (
          0 => 'Řízený experiment s omezeným dopadem.',
          1 => 'Permanentní load balancer.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'backup-restore' => 
    array (
      'id' => 'ops-restore',
      'type' => 'backuprestore',
      'title' => 'Restore Drill Lab',
      'lead' => 'Vyber backup, recovery point a validační kroky. Simulace spočítá RPO/RTO dopad.',
      'task' => 'Vyber nejnovější ověřený recovery point a proveď testovací restore s validací.',
      'prediction' => 
      array (
        'q' => 'Je backup bez testu restore dostatečný?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Nejprve ověř integritu.',
        1 => 'Restore do test targetu.',
        2 => 'Ověř data i app.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je RPO?',
        'options' => 
        array (
          0 => 'Tolerovaná ztráta dat v čase.',
          1 => 'Doba CPU testu.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'slo-postmortem' => 
    array (
      'id' => 'ops-slo',
      'type' => 'slobudget',
      'title' => 'SLO & Error Budget Lab',
      'lead' => 'Měň SLO, skutečnou dostupnost a rozhodnutí po incidentu. Simulace ukáže, zda má tým ještě prostor pro rizikový rollout.',
      'task' => 'Nastav SLO 99,9 %, dostupnost 99,5 % a zvol stabilizační/postmortem režim.',
      'prediction' => 
      array (
        'q' => 'Když je dostupnost pod SLO, má tým bez rozmyslu zrychlit rollout?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Nastav SLO 99,9 %.',
        1 => 'Sniž skutečnost na 99,5 %.',
        2 => 'Zvol stabilizaci + postmortem.',
      ),
      'conclusion' => 
      array (
        'q' => 'K čemu error budget slouží?',
        'options' => 
        array (
          0 => 'Pomáhá balancovat spolehlivost a tempo změn.',
          1 => 'Určuje cenu serveru.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'infrastructure-as-code' => 
    array (
      'id' => 'ops-iac',
      'type' => 'canary',
      'title' => 'Plan → Apply Lab',
      'lead' => 'Porovnej malý reviewovatelný change set s neřízenou změnou a rozhodni, kdy je bezpečné provést apply.',
      'task' => 'Projdi změnu přes plan, review, apply a validaci.',
      'prediction' => 
      array (
        'q' => 'Je bezpečné provést apply bez kontroly diffu?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Nejdřív baseline.',
        1 => 'Zkontroluj scope.',
        2 => 'Měj rollback/restore cestu.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co dává IaC největší hodnotu?',
        'options' => 
        array (
          0 => 'Opakovatelnost, audit a review změn.',
          1 => 'Více ručních kroků.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'config-drift' => 
    array (
      'id' => 'ops-drift',
      'type' => 'monitoring',
      'title' => 'Drift Detection Lab',
      'lead' => 'Sleduj desired a actual konfiguraci, vyvolej drift a rozhodni, zda opravit runtime nebo zdroj pravdy.',
      'task' => 'Detekuj odchylku a vrať prostředí do jednoznačného stavu.',
      'prediction' => 
      array (
        'q' => 'Je každý drift automaticky chyba runtime?',
        'options' => 
        array (
          0 => 'Ne, nejdřív musím znát zdroj pravdy.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Porovnej expected vs actual.',
        1 => 'Zjisti historii změny.',
        2 => 'Sjednoť stav i repozitář.',
      ),
      'conclusion' => 
      array (
        'q' => 'Kdy je drift vyřešen?',
        'options' => 
        array (
          0 => 'Když desired a actual stav znovu souhlasí a změna je zdokumentovaná.',
          1 => 'Když alert vypnu.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'performance-engineering' => 
    array (
      'id' => 'ops-performance',
      'type' => 'httpprobe',
      'title' => 'Latency & Saturation Lab',
      'lead' => 'Měň zátěž a sleduj HTTP latency/error rate spolu s využitím zdroje. Hledej okamžik, kdy se systém začne saturovat.',
      'task' => 'Najdi bottleneck podle p95 a provozních signálů.',
      'prediction' => 
      array (
        'q' => 'Stačí sledovat jen průměrnou latency?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Sleduj p95.',
        1 => 'Porovnej error rate.',
        2 => 'Hledej korelaci se saturací.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je bottleneck?',
        'options' => 
        array (
          0 => 'Zdroj, jehož limit omezuje celý systém.',
          1 => 'Nejnovější server.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'capacity-planning' => 
    array (
      'id' => 'ops-capacity',
      'type' => 'loadbalance',
      'title' => 'Capacity & Headroom Lab',
      'lead' => 'Zvyšuj load, simuluj výpadek backendu a hledej kapacitu, která splní SLO i s rezervou.',
      'task' => 'Navrhni kapacitu pro cílový provoz plus headroom.',
      'prediction' => 
      array (
        'q' => 'Je bezpečné provozovat systém trvale na 100 % laboratorního maxima?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Najdi bod degradace.',
        1 => 'Přidej rezervu.',
        2 => 'Otestuj výpadek části backendů.',
      ),
      'conclusion' => 
      array (
        'q' => 'Proč potřebujeme headroom?',
        'options' => 
        array (
          0 => 'Pro špičky, růst a selhání části systému.',
          1 => 'Jen kvůli reportingu.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'systemd-advanced' => 
    array (
      'id' => 'yp-systemd-advanced',
      'type' => 'tcp',
      'title' => 'systemd: dependencies a failure policy · Lab',
      'lead' => 'Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.',
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
    'storage-filesystems' => 
    array (
      'id' => 'yp-storage-filesystems',
      'type' => 'backuprestore',
      'title' => 'Storage a filesystems · Lab',
      'lead' => 'Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.',
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
    'backup-strategy' => 
    array (
      'id' => 'yp-backup-strategy',
      'type' => 'backuprestore',
      'title' => 'Backup strategie, RPO a RTO · Lab',
      'lead' => 'Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.',
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
    'ssh-hardening' => 
    array (
      'id' => 'yp-ssh-hardening',
      'type' => 'sshkeylab',
      'title' => 'SSH hardening a bezpečný management · Lab',
      'lead' => 'Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.',
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
    'containers-basics' => 
    array (
      'id' => 'yp-containers-basics',
      'type' => 'binding',
      'title' => 'Containers: image, runtime, volume a network · Lab',
      'lead' => 'Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.',
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
    'automation-shell' => 
    array (
      'id' => 'yp-automation-shell',
      'type' => 'production',
      'title' => 'Bezpečná automatizace a idempotence · Lab',
      'lead' => 'Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.',
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
    'packet-diagnostics-advanced' => 
    array (
      'id' => 'yp-packet-diagnostics-advanced',
      'type' => 'packetflow',
      'title' => 'Pokročilá packet diagnostika · Lab',
      'lead' => 'Packet capture má odpovědět na konkrétní otázku a musí být korelovaný se socket state na serveru.',
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
    'incident-runbook' => 
    array (
      'id' => 'yp-incident-runbook',
      'type' => 'slobudget',
      'title' => 'Incident runbook a koordinace · Lab',
      'lead' => 'Incident response je strukturovaný proces: impact, role, evidence, mitigation, validation a follow-up.',
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
        'title' => 'Zámečky nikoho nezajímaj’ – HTTPS a certifikáty',
        'url' => 'https://www.youtube.com/watch?v=8_qX6ZThwZI',
        'youtube_id' => '8_qX6ZThwZI',
        'lang' => 'CZ',
        'duration' => '20 min',
        'level' => 'Středně pokročilý',
        'description' => 'Krátká česká přednáška o HTTPS, certifikátech a tom, co skutečně znamená zabezpečené spojení v prohlížeči.',
      ),
      1 => 
      array (
        'type' => 'reference',
        'title' => 'MDN · HTTP status codes',
        'meta' => 'Volitelná reference pro HTTP diagnostiku',
        'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status',
      ),
    ),
    'cidr' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Odkud se berou IP adresy?',
        'url' => 'https://www.youtube.com/watch?v=JYUpyOTnIYM',
        'youtube_id' => 'JYUpyOTnIYM',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Začátečník → středně pokročilý',
        'description' => 'Česká přednáška Ondřeje Caletky o adresním prostoru a přidělování IP adres; rozšiřuje pohled za lokální síť.',
      ),
    ),
    'routing-advanced' => 
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
    'dns-advanced' => 
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
    'binding' => 
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
    'tcp' => 
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
    'ssh-keys' => 
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
    'permissions' => 
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
    'diagnostics' => 
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
    'reverse-proxy' => 
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
    'tls-certificates' => 
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
      1 => 
      array (
        'type' => 'reference',
        'title' => 'Cloudflare · TLS handshake',
        'meta' => 'Vizuální doplnění TLS handshaku',
        'url' => 'https://www.cloudflare.com/learning/ssl/what-happens-in-a-tls-handshake/',
      ),
    ),
    'logs-monitoring' => 
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
    'change-management' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'http-observability' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
      1 => 
      array (
        'type' => 'reference',
        'title' => 'MDN · HTTP status codes',
        'meta' => 'Rychlá diagnostická reference',
        'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status',
      ),
    ),
    'load-balancing' => 
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
    'canary-release' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'backup-restore' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Proxmox Backup Server: obnova jednoho souboru',
        'url' => 'https://www.youtube.com/watch?v=54Sc77n4pDQ',
        'youtube_id' => '54Sc77n4pDQ',
        'lang' => 'CZ',
        'duration' => 'krátké · cca 5–10 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická ukázka skutečné obnovy souboru ze zálohy — důraz na restore, ne jen vytvoření backupu.',
      ),
    ),
    'slo-postmortem' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'infrastructure-as-code' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'config-drift' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Moderní logování: Vector a ECS',
        'url' => 'https://www.youtube.com/watch?v=lujVIbXpqLo',
        'youtube_id' => 'lujVIbXpqLo',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o moderním sběru a struktuře logů — vhodná pro korelaci, observability a incident response.',
      ),
    ),
    'performance-engineering' => 
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
    'capacity-planning' => 
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
    'systemd-advanced' => 
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
    'storage-filesystems' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Proxmox Backup Server: obnova jednoho souboru',
        'url' => 'https://www.youtube.com/watch?v=54Sc77n4pDQ',
        'youtube_id' => '54Sc77n4pDQ',
        'lang' => 'CZ',
        'duration' => 'krátké · cca 5–10 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická ukázka skutečné obnovy souboru ze zálohy — důraz na restore, ne jen vytvoření backupu.',
      ),
    ),
    'backup-strategy' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Proxmox Backup Server: obnova jednoho souboru',
        'url' => 'https://www.youtube.com/watch?v=54Sc77n4pDQ',
        'youtube_id' => '54Sc77n4pDQ',
        'lang' => 'CZ',
        'duration' => 'krátké · cca 5–10 min',
        'level' => 'Středně pokročilý',
        'description' => 'Česká praktická ukázka skutečné obnovy souboru ze zálohy — důraz na restore, ne jen vytvoření backupu.',
      ),
    ),
    'ssh-hardening' => 
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
    'containers-basics' => 
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
    'automation-shell' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'packet-diagnostics-advanced' => 
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
    'incident-runbook' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'golden-signals' => 
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
    'container-networking' => 
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
    'rollback-strategy' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Minority Reports – chyby z prohlížeče jako provozní evidence',
        'url' => 'https://www.youtube.com/watch?v=aB7i_jLWGFc',
        'youtube_id' => 'aB7i_jLWGFc',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.',
      ),
    ),
    'incident-command' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Chyť mě, když to dokážeš! Bezpečnostní monitoring s FOSS',
        'url' => 'https://www.youtube.com/watch?v=ATPBw6Dt6Ig',
        'youtube_id' => 'ATPBw6Dt6Ig',
        'lang' => 'CZ',
        'duration' => '50 min',
        'level' => 'Pokročilý',
        'description' => 'Česká přednáška o bezpečnostním monitoringu a skládání více signálů do použitelné provozní evidence.',
      ),
    ),
  ),
  '_sources_hash' => '2515a71fc18766cb957672597cc4f11e9d250fce27d350d3506a7ad643f968c7',
);
