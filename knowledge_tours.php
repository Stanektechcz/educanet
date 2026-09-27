<?php

declare(strict_types=1);

/*
 * Enrichment metadata for the visual Knowledge Tour.
 * The canonical explanations remain in modules.php; this file adds a mental model,
 * visual demo, guided steps, pitfalls and a self-check without changing test answers.
 */

return [
    'class_3a' => [
        'ip-addressing' => [
            'time' => '7–10 min', 'level' => 'Základ', 'visual' => ['type' => 'subnet', 'address' => '192.168.50.42/24', 'network' => '192.168.50.0', 'hosts' => '192.168.50.1–254', 'broadcast' => '192.168.50.255'],
            'mental' => 'IP adresa říká „kde zařízení je“, prefix říká „která část adresy patří síti“. Než začneš řešit internet, musíš vědět, ve které síti klient skutečně sedí.',
            'steps' => ['Najdi IP adresu klienta.', 'Přečti prefix/masku a urč síťovou část.', 'Rozhodni, zda je cíl ve stejné síti.', 'Pokud není, hledej výchozí bránu.', 'Až potom řeš DNS nebo konkrétní službu.'],
            'mistakes' => ['Zaměnit /24 za počet zařízení.', 'Považovat každou 192.168.x.x adresu za „internetovou“.', 'Ignorovat adresu 169.254.x.x, která často signalizuje problém s automatickou konfigurací.'],
            'check' => ['q' => 'Klient má 192.168.50.42/24 a server 192.168.50.80/24. Musí paket kvůli tomu přes router?', 'options' => ['Ano, vždy.', 'Ne, jsou ve stejném subnetu.', 'Jen pokud jde o HTTPS.'], 'correct' => 1, 'why' => 'Obě adresy patří do sítě 192.168.50.0/24, takže mohou komunikovat lokálně.'],
        ],
        'dns' => [
            'time' => '8–12 min', 'level' => 'Základ', 'visual' => ['type' => 'sequence', 'items' => [['Prohlížeč','portal.example.cz'],['Resolver','Kde je A záznam?'],['DNS','192.0.2.40'],['Klient','Připoj se na 192.0.2.40']]],
            'mental' => 'DNS je telefonní seznam s cache. Když IP funguje a doména ne, aplikace často není problém — klient jen neumí přeložit jméno na správnou adresu.',
            'steps' => ['Ověř, jaký DNS server klient používá.', 'Zkus překlad jména přes nslookup/dig.', 'Porovnej vrácenou IP s očekávanou.', 'Když je odpověď stará, přemýšlej o cache/TTL.', 'Teprve potom měň DNS konfiguraci.'],
            'mistakes' => ['Restartovat webserver, i když selhává samotný překlad jména.', 'Myslet si, že DNS = internet.', 'Přehlédnout, že různé resolvery mohou mít dočasně různé odpovědi.'],
            'check' => ['q' => 'Web na 192.0.2.40 funguje, ale portal.example.cz ne. Co ověříš jako první?', 'options' => ['DNS překlad.', 'Grafickou kartu.', 'SSH klíč.'], 'correct' => 0, 'why' => 'Spojení na IP ukazuje, že cesta k hostu může být v pořádku. Rozdíl je právě překlad názvu.'],
        ],
        'dhcp' => [
            'time' => '7–10 min', 'level' => 'Základ', 'visual' => ['type' => 'sequence', 'items' => [['Klient','DISCOVER'],['DHCP','OFFER'],['Klient','REQUEST'],['DHCP','ACK + IP, GW, DNS']]],
            'mental' => 'DHCP není jen „přidělovač IP“. Klientovi předává celý startovní balíček: adresu, masku/prefix, bránu a DNS.',
            'steps' => ['Podívej se na aktuální konfiguraci.', 'Hledej neočekávanou/APIPA adresu.', 'Ověř linku/VLAN a dostupnost DHCP.', 'Zkontroluj pool a případné konflikty.', 'Po opravě obnov lease a znovu ověř konfiguraci.'],
            'mistakes' => ['Ručně nastavit náhodnou statickou IP bez znalosti subnetu.', 'Řešit DNS dřív, než klient vůbec získá použitelnou IP.', 'Zapomenout, že DHCP může předat i špatnou bránu nebo DNS.'],
            'check' => ['q' => 'Windows klient po připojení dostane 169.254.32.18. Co je nejpravděpodobnější?', 'options' => ['Klient nedostal očekávanou konfiguraci z DHCP.', 'DNS funguje perfektně.', 'Je to veřejná adresa ISP.'], 'correct' => 0, 'why' => '169.254.0.0/16 je link-local/APIPA rozsah používaný při problému s běžnou automatickou konfigurací.'],
        ],
        'ports' => [
            'time' => '6–9 min', 'level' => 'Základ', 'visual' => ['type' => 'ports', 'host' => 'server01', 'items' => [['22','SSH','open'],['80','HTTP','closed'],['443','HTTPS','open'],['3306','DB','local']]],
            'mental' => 'IP tě dovede k počítači. Port tě dovede ke konkrétní službě na tom počítači. „Server pingnu“ proto neznamená „web funguje“.',
            'steps' => ['Zjisti cílovou IP.', 'Zjisti cílový port služby.', 'Ověř, zda služba naslouchá.', 'Ověř, na jaké adrese naslouchá.', 'Ověř firewall a síťovou cestu z klienta.'],
            'mistakes' => ['Zaměnit port za fyzický konektor.', 'Považovat otevřený port za důkaz bezpečnosti.', 'Ignorovat rozdíl mezi 127.0.0.1:PORT a 0.0.0.0:PORT.'],
            'check' => ['q' => 'Ping funguje, ale TCP/443 ne. Co z toho plyne?', 'options' => ['Síť je určitě bez chyby.', 'Musíš ověřit službu, binding, firewall a cestu pro TCP/443.', 'DNS je určitě špatně.'], 'correct' => 1, 'why' => 'ICMP a TCP/443 jsou rozdílné protokoly/cesty kontroly. Ping sám dostupnost webové služby nepotvrdí.'],
        ],
        'ssh-sftp' => [
            'time' => '8–12 min', 'level' => 'Základ', 'visual' => ['type' => 'flow', 'items' => [['Notebook','SSH klient'],['TCP/22','šifrovaný kanál'],['Server','sshd'],['SFTP','přenos souborů']]],
            'mental' => 'SSH je bezpečný vzdálený kanál. SFTP běží uvnitř stejného SSH světa — není to klasické FTP s jinou ikonou.',
            'steps' => ['Ověř DNS/IP serveru.', 'Ověř TCP/22.', 'Ověř, že sshd běží a naslouchá.', 'Rozliš problém s konektivitou od autentizace.', 'U SFTP ověř oprávnění k cílovým souborům/adresářům.'],
            'mistakes' => ['Hledat port 21 pro SFTP.', 'Sdílet soukromý SSH klíč.', 'Měnit heslo, když se klient na port 22 vůbec nedostane.'],
            'check' => ['q' => 'SFTP typicky používá:', 'options' => ['SSH a TCP/22.', 'HTTP a TCP/80.', 'ICMP.'], 'correct' => 0, 'why' => 'SFTP je subsystém/protokol přenášený přes SSH spojení.'],
        ],
        'https' => [
            'time' => '10–14 min', 'level' => 'Základ+', 'visual' => ['type' => 'layers', 'items' => [['1','DNS','jméno → IP'],['2','TCP','spojení na 443'],['3','TLS','certifikát + šifrování'],['4','HTTP','GET /'],['5','Aplikace','odpověď']]],
            'mental' => 'HTTPS není jedna věc. Je to řetězec vrstev. Když víš, ve které vrstvě se chyba objeví, výrazně zkrátíš troubleshooting.',
            'steps' => ['Přelož jméno přes DNS.', 'Ověř TCP spojení na 443.', 'Podívej se na TLS handshake/certifikát.', 'Ověř HTTP stavový kód.', 'Až potom řeš obsah aplikace.'],
            'mistakes' => ['Považovat 502 za DNS chybu.', 'Považovat validní DNS za důkaz funkčního webu.', 'Ignorovat certifikát nebo časovou platnost.'],
            'check' => ['q' => 'DNS vrací správnou IP a TCP/443 se otevře, ale TLS handshake selže. Kde hledáš?', 'options' => ['Certifikát/TLS konfigurace.', 'DHCP pool.', 'Monitor.'], 'correct' => 0, 'why' => 'První dvě vrstvy už máme doložené. Chyba nastává při TLS.'],
        ],
        'icmp' => [
            'time' => '7–10 min', 'level' => 'Základ', 'visual' => ['type' => 'compare', 'left' => ['Ping','ICMP reachability','„Host odpovídá?“'], 'right' => ['TCP test','Konkrétní port','„Služba je dostupná?“']],
            'mental' => 'Ping je rychlé měření jedné části problému. Je užitečný, ale není rozsudek nad celou sítí ani aplikací.',
            'steps' => ['Pingni lokální bránu.', 'Pingni známou IP mimo subnet.', 'Porovnej s testem konkrétního TCP portu.', 'Traceroute použij, když potřebuješ vidět cestu/hopy.', 'Výsledek vždy interpretuj v kontextu firewall pravidel.'],
            'mistakes' => ['„Ping nejde = server je mrtvý.“ ICMP může být blokovaný.', '„Ping jde = web funguje.“ Nemusí.', 'Použít traceroute jako jediný důkaz root cause.'],
            'check' => ['q' => 'Server neodpovídá na ping, ale HTTPS funguje. Je to možné?', 'options' => ['Ano, ICMP může být filtrováno.', 'Ne, je to fyzikálně nemožné.'], 'correct' => 0, 'why' => 'Firewall může blokovat ICMP a současně povolit TCP/443.'],
        ],
        'routing' => [
            'time' => '8–12 min', 'level' => 'Základ+', 'visual' => ['type' => 'flow', 'items' => [['Klient','192.168.10.42/24'],['Gateway','192.168.10.1'],['Router','route lookup'],['Cíl','10.20.0.15']]],
            'mental' => 'Host nejprve rozhodne: je cíl lokální, nebo vzdálený? Pokud vzdálený, předá paket výchozí bráně. Routing je série těchto rozhodnutí.',
            'steps' => ['Urči subnet klienta.', 'Porovnej cílovou IP se subnetem.', 'Pokud je cíl mimo, najdi default route.', 'Ověř dostupnost gateway.', 'Na více routerech sleduj, kam vede další hop.'],
            'mistakes' => ['Považovat default gateway za DNS server.', 'Nastavit bránu mimo klientův lokální subnet.', 'Měnit routu bez kontroly, zda cíl není lokální.'],
            'check' => ['q' => 'Klient 192.168.10.42/24 chce na 10.20.0.15. Komu předá rámec jako první?', 'options' => ['Výchozí bráně v lokální síti.', 'Přímo MAC adrese vzdáleného serveru.', 'DNS serveru.'], 'correct' => 0, 'why' => 'Cíl je mimo lokální /24, proto klient použije route přes gateway.'],
        ],
        'troubleshooting' => [
            'time' => '10–15 min', 'level' => 'Klíčové', 'visual' => ['type' => 'layers', 'items' => [['1','Lokální stav','IP, link, Wi‑Fi/VLAN'],['2','Brána','lokální cesta'],['3','IP konektivita','známý cíl'],['4','DNS','jméno → IP'],['5','Služba','port/proces'],['6','Aplikace','HTTP, práva, data']]],
            'mental' => 'Dobré řešení incidentu není seznam náhodných příkazů. Je to řízené zužování prostoru možných příčin: hypotéza → měření → důkaz.',
            'steps' => ['Přepiš symptom bez domněnky.', 'Najdi nejnižší vrstvu, kterou můžeš ověřit.', 'Proveď jeden test, který něco skutečně rozliší.', 'Z výsledku vytvoř další hypotézu.', 'Změnu proveď až ve chvíli, kdy víš proč.', 'Nakonec ověř původní symptom i vedlejší dopady.'],
            'mistakes' => ['Restartovat „pro jistotu“ a tím zničit důkazy.', 'Měnit více věcí najednou.', 'Přestat po prvním úspěšném pingu.', 'Neověřit stav po opravě.'],
            'check' => ['q' => 'Jaký je nejlepší další krok po zjištění, že klient má správnou IP a pingne gateway?', 'options' => ['Vybrat další test podle symptomu — např. IP cíl, DNS nebo port.', 'Přeinstalovat OS.', 'Náhodně změnit DNS i firewall zároveň.'], 'correct' => 0, 'why' => 'Troubleshooting má navazovat na to, co už bylo prokázáno, a další test má rozlišit zbývající hypotézy.'],
        ],
    ],
    'class_4a' => [
        'cidr' => [
            'time' => '10–14 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'subnet', 'address' => '10.20.30.77/27', 'network' => '10.20.30.64', 'hosts' => '10.20.30.65–94', 'broadcast' => '10.20.30.95'],
            'mental' => 'CIDR není školní počítání masek. Je to nástroj pro správné adresování, sumarizaci rout a rychlé rozhodnutí, co je lokální.',
            'steps' => ['Přelož prefix na velikost bloku.', 'Najdi hranici bloku pro danou IP.', 'Urči network/broadcast.', 'Ověř rozsah použitelných hostů.', 'Použij výsledek při route/firewall analýze.'],
            'mistakes' => ['Počítat subnet „od nuly“ bez velikosti bloku.', 'Zaměnit /27 za 27 hostů.', 'Přidělit network nebo broadcast hostu.'],
            'check' => ['q' => 'Do které sítě patří 10.20.30.77/27?', 'options' => ['10.20.30.64/27', '10.20.30.77/27', '10.20.30.0/24'], 'correct' => 0, 'why' => '/27 má blok 32 adres; 77 spadá do intervalu 64–95.'],
        ],
        'routing-advanced' => [
            'time' => '10–15 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'flow', 'items' => [['Host','route lookup'],['Longest prefix','nejkonkrétnější route'],['Gateway/interface','next hop'],['Firewall','policy'],['Cíl','service']]],
            'mental' => 'Routing není „použij default gateway“. Kernel vybírá nejkonkrétnější odpovídající route, potom next-hop/interface. Default je jen poslední možnost.',
            'steps' => ['Zapiš cílovou IP.', 'Najdi všechny matching routes.', 'Vyber route s nejdelším prefixem.', 'Ověř next-hop a interface.', 'Zkontroluj návratovou cestu — asymetrie může být stejně důležitá.'],
            'mistakes' => ['Číst routing tabulku jen odshora dolů.', 'Ignorovat specifičtější route.', 'Zapomenout na return path nebo policy routing.'],
            'check' => ['q' => 'Existuje route 10.20.0.0/16 a 10.20.30.0/24. Cíl je 10.20.30.77. Která vyhraje?', 'options' => ['/24', '/16', 'default'], 'correct' => 0, 'why' => 'Longest-prefix match vybírá nejkonkrétnější odpovídající route.'],
        ],
        'dns-advanced' => [
            'time' => '12–16 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'sequence', 'items' => [['Autorita','A = 203.0.113.20, TTL 300'],['Resolver A','cache: stará IP'],['Resolver B','cache: nová IP'],['Klienti','dočasně různé výsledky']]],
            'mental' => 'Po změně DNS nemusí svět přepnout naráz. TTL řídí, jak dlouho může resolver cacheovat starou odpověď; různí klienti tak mohou chvíli vidět různé cíle.',
            'steps' => ['Zjisti autoritativní odpověď.', 'Porovnej ji s běžným resolverem.', 'Sleduj TTL.', 'Ověř A/AAAA zvlášť.', 'Při migraci vždy počítej s cache a rollback plánem.'],
            'mistakes' => ['Měnit záznam opakovaně, protože jeden resolver ještě vrací starou hodnotu.', 'Ignorovat AAAA při změně pouze A.', 'Zaměnit TTL za dobu registrace domény.'],
            'check' => ['q' => 'Autoritativní server vrací novou IP, ale jeden resolver starou s TTL 120. Co je pravděpodobné?', 'options' => ['Resolver má ještě platnou cache.', 'Nginx je určitě vypnutý.', 'SSH permissions jsou špatně.'], 'correct' => 0, 'why' => 'TTL říká, jak dlouho může být odpověď držena v cache.'],
        ],
        'binding' => [
            'time' => '10–14 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'compare', 'left' => ['127.0.0.1:8080','jen loopback','zvenku nedostupné'], 'right' => ['0.0.0.0:8080','všechna IPv4 rozhraní','dostupnost řídí firewall']],
            'mental' => 'Proces může běžet, port může být otevřený — ale jen na loopbacku. Binding určuje, na kterých lokálních adresách socket skutečně přijímá spojení.',
            'steps' => ['Najdi listening socket přes ss/netstat.', 'Přečti bind adresu.', 'Porovnej s IP, na kterou se připojuje klient/reverse proxy.', 'Zvaž bezpečnost změny na širší binding.', 'Doplň firewall scope místo bezhlavého „otevři všem“.'],
            'mistakes' => ['Zaměnit 127.0.0.1 za „adresu serveru v LAN“.', 'Změnit binding na 0.0.0.0 bez firewall omezení.', 'Předpokládat, že běžící systemd service = dostupná služba.'],
            'check' => ['q' => 'Aplikace naslouchá na 127.0.0.1:3000 a Nginx na jiném serveru se k ní připojuje. Výsledek?', 'options' => ['Spojení z jiného serveru selže.', 'Bude fungovat automaticky.', 'Vyřeší to DNS TTL.'], 'correct' => 0, 'why' => 'Loopback je lokální pouze pro daný host. Vzdálený stroj se na něj nedostane.'],
        ],
        'tcp' => [
            'time' => '10–14 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'sequence', 'items' => [['Client','SYN'],['Server','SYN-ACK'],['Client','ACK'],['Obě strany','ESTABLISHED']]],
            'mental' => 'TCP stav ti říká, jak daleko se spojení dostalo. „Connection refused“ a timeout jsou diagnosticky úplně jiné informace.',
            'steps' => ['Rozliš timeout vs. refused.', 'Ověř listening socket.', 'Sleduj SYN/SYN-ACK, pokud je potřeba packet capture.', 'Ověř firewall/NAT cestu.', 'Po navázání TCP teprve řeš TLS/HTTP.'],
            'mistakes' => ['Házet timeout i refused do jednoho pytle.', 'Řešit HTTP, když se neotevře TCP socket.', 'Ignorovat backlog nebo stav procesu při přetížení.'],
            'check' => ['q' => 'Klient okamžitě dostane „connection refused“. Co je typičtější než u timeoutu?', 'options' => ['Cíl je dosažitelný, ale na portu nic neposlouchá nebo aktivně odmítá.', 'DNS nikdy neodpověděl.', 'Paket se ztratil někde bez odpovědi.'], 'correct' => 0, 'why' => 'Refused obvykle znamená aktivní odpověď RST; timeout spíš absenci odpovědi.'],
        ],
        'ssh-keys' => [
            'time' => '10–15 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'flow', 'items' => [['Client','private key'],['Proof','podpis challenge'],['Server','authorized_keys / public key'],['Session','přístup bez posílání private key']]],
            'mental' => 'Soukromý klíč neopouští klienta. Server ověřuje kryptografický důkaz proti uloženému veřejnému klíči.',
            'steps' => ['Ověř, který klíč klient skutečně nabízí.', 'Použij ssh -vvv pro detailní průběh.', 'Ověř authorized_keys na správném účtu.', 'Ověř vlastníka a permissions.', 'Po úspěchu zvaž omezení účtu/klíče podle účelu.'],
            'mistakes' => ['Kopírovat private key na server.', 'Debugovat firewall, když TCP/22 i handshake funguje a selže až autentizace.', 'Ignorovat, pod jakým uživatelem se klient přihlašuje.'],
            'check' => ['q' => 'Kde má zůstat soukromý SSH klíč?', 'options' => ['Na klientovi a chráněný.', 'Na veřejném webserveru.', 'V DNS TXT záznamu.'], 'correct' => 0, 'why' => 'Soukromý klíč je tajemství klienta; server potřebuje veřejnou část.'],
        ],
        'permissions' => [
            'time' => '10–14 min', 'level' => 'Pokročilé', 'visual' => ['type' => 'permissions', 'rows' => [['Owner','rw-','6'],['Group','---','0'],['Other','---','0']], 'mode' => '600'],
            'mental' => 'Unix permissions jsou tři sady práv: owner, group, other. Pro tajné klíče je často podstatné, aby k nim nikdo další neměl přístup.',
            'steps' => ['Zjisti owner/group.', 'Přečti rwx bity nebo číselný mód.', 'Rozhodni, kdo práva skutečně potřebuje.', 'Použij nejmenší nutná oprávnění.', 'Ověř, že služba/SSH stále soubor přečte.'],
            'mistakes' => ['Použít chmod 777 jako univerzální opravu.', 'Měnit permissions bez kontroly ownera.', 'Myslet si, že 600 znamená „600 uživatelů“.'],
            'check' => ['q' => 'Co znamená chmod 600 private_key?', 'options' => ['Owner může číst a zapisovat, ostatní nemají práva.', 'Všichni mohou vše.', 'Jen skupina může číst.'], 'correct' => 0, 'why' => '6 = rw-, 0 = ---, 0 = ---.'],
        ],
        'diagnostics' => [
            'time' => '12–18 min', 'level' => 'Klíčové', 'visual' => ['type' => 'layers', 'items' => [['1','Name','dig / resolver'],['2','Route','ip route get'],['3','Transport','nc / ss'],['4','TLS','openssl/curl -v'],['5','HTTP','status + headers'],['6','Service','logs/systemctl']]],
            'mental' => 'Seniornější troubleshooting znamená nejen najít příčinu, ale umět říct, které hypotézy byly jednotlivými měřeními vyloučené.',
            'steps' => ['Definuj symptom a scope.', 'Získej minimální reprodukci.', 'Měř od hranice systému dovnitř nebo po vrstvách.', 'U každého příkazu napiš, co potvrzuje a co nepotvrzuje.', 'Proveď jednu řízenou změnu.', 'Validuj službu z pohledu skutečného klienta a měj rollback.'],
            'mistakes' => ['Sbírat obrovské logy bez hypotézy.', 'Dělat změnu dřív než měření.', 'Validovat jen localhost, když problém hlásí vzdálený klient.', 'Nevytvořit rollback pro produkční zásah.'],
            'check' => ['q' => 'curl localhost funguje, ale vzdálený klient ne. Co je silný další krok?', 'options' => ['Ověřit binding, firewall a cestu z klienta.', 'Přeinstalovat curl.', 'Změnit DNS bez měření.'], 'correct' => 0, 'why' => 'Lokální test ověřil aplikaci z hostu, ale ne externí binding ani síťovou cestu.'],
        ],
    ],
    'class_2a' => [
        'hierarchy' => [
            'time' => '7–10 min', 'level' => 'Základ', 'visual' => ['type' => 'poster', 'mode' => 'hierarchy'],
            'mental' => 'Hierarchie říká oku, co má vidět první, druhé a třetí. Když je všechno stejně důležité, ve výsledku není důležité nic.',
            'steps' => ['Urči jedinou hlavní zprávu.', 'Vyber dominantní prvek — obvykle headline nebo obraz.', 'Sekundární informace zmenši a seskup.', 'CTA nebo klíčový údaj dej na čitelné místo.', 'Zkontroluj návrh z dálky / jako malý náhled.'],
            'mistakes' => ['Všechen text stejně velký.', 'Pět různých důrazů najednou.', 'Důležitý datum/CTA schovaný mezi dekoracemi.'],
            'check' => ['q' => 'Co je cílem vizuální hierarchie?', 'options' => ['Řídit pořadí, ve kterém divák informace vnímá.', 'Použít co nejvíc fontů.', 'Vyplnit každý prázdný prostor.'], 'correct' => 0, 'why' => 'Hierarchie vytváří jasnou cestu oka přes obsah.'],
        ],
        'composition' => [
            'time' => '8–12 min', 'level' => 'Základ', 'visual' => ['type' => 'grid', 'columns' => 6],
            'mental' => 'Grid není vězení. Je to skrytá konstrukce, která pomáhá zarovnat prvky a vytvořit rytmus. Negativní prostor dává obsahu prostor dýchat.',
            'steps' => ['Zvol okraje.', 'Nastav jednoduchý grid/sloupce.', 'Zarovnej příbuzné prvky.', 'Použij whitespace jako aktivní prvek.', 'Zkontroluj rovnováhu a těžiště kompozice.'],
            'mistakes' => ['Lepit prvky náhodně k sobě.', 'Bát se prázdného prostoru.', 'Mít téměř stejné, ale ne přesné zarovnání.'],
            'check' => ['q' => 'K čemu je grid?', 'options' => ['K systematickému zarovnání a rytmu.', 'K automatickému výběru barev.', 'Jen pro tabulky.'], 'correct' => 0, 'why' => 'Grid pomáhá držet konzistenci pozic a proporcí.'],
        ],
        'contrast-color' => [
            'time' => '8–12 min', 'level' => 'Základ', 'visual' => ['type' => 'color', 'mode' => 'contrast'],
            'mental' => 'Kontrast vytváří rozdíl, díky kterému věci rozeznáme a pochopíme jejich důležitost. Barva je jen jeden z nástrojů kontrastu.',
            'steps' => ['Zkontroluj světlost textu vůči pozadí.', 'Omez paletu na hlavní + podpůrné barvy.', 'Použij akcent pro jednu důležitou věc.', 'Nespoléhej jen na barvu pro význam.', 'Otestuj návrh i v malém náhledu.'],
            'mistakes' => ['Šedý text na šedém pozadí.', 'Každý prvek jinou výraznou barvou.', 'Červená/zelená jako jediný nositel informace.'],
            'check' => ['q' => 'Silný kontrast je nejdůležitější hlavně pro:', 'options' => ['Čitelnost a jasný důraz.', 'Počet vrstev v souboru.', 'Velikost PDF.'], 'correct' => 0, 'why' => 'Dostatečný kontrast pomáhá textu i důležitým prvkům vystoupit.'],
        ],
        'typography' => [
            'time' => '9–13 min', 'level' => 'Základ+', 'visual' => ['type' => 'poster', 'mode' => 'type'],
            'mental' => 'Typografie není výběr „hezkého fontu“. Je to práce s velikostí, řezem, řádkováním, délkou řádku a vztahem textových úrovní.',
            'steps' => ['Vyber 1–2 rodiny písem.', 'Nastav jasný rozdíl headline/body.', 'Uprav řádkování a délku řádku.', 'Zarovnávej konzistentně.', 'Kontroluj českou diakritiku a čitelnost.'],
            'mistakes' => ['Čtyři dekorativní fonty v jednom plakátu.', 'Příliš malý body text.', 'Těsné řádkování nebo dlouhé řádky.'],
            'check' => ['q' => 'Kolik písem je pro jednoduchý školní plakát obvykle bezpečný start?', 'options' => ['1–2 dobře kombinované rodiny.', 'Nejméně 6.', 'Každý řádek jiné.'], 'correct' => 0, 'why' => 'Omezený počet písem pomáhá konzistenci a hierarchii.'],
        ],
        'raster-vector' => [
            'time' => '8–12 min', 'level' => 'Základ', 'visual' => ['type' => 'pixels'],
            'mental' => 'Raster je mřížka pixelů — skvělá pro fotografie. Vektor popisuje tvary matematicky — skvělý pro loga, ikony a škálovatelné ilustrace.',
            'steps' => ['Rozhodni, zda pracuješ s fotografií nebo tvarem/logem.', 'U rasteru hlídej rozlišení.', 'U vektoru hlídej křivky a výplně.', 'Exportuj do formátu podle cíle.', 'Nenech malé rasterové logo zvětšovat do billboardu.'],
            'mistakes' => ['Považovat PNG za vektor.', 'Vložit JPG logo a čekat nekonečnou ostrost.', 'Použít SVG pro fotografii.'],
            'check' => ['q' => 'Který typ je vhodnější pro logo, které se má zvětšovat bez ztráty kvality?', 'options' => ['Vektor.', 'Nízké JPG.', 'Screenshot.'], 'correct' => 0, 'why' => 'Vektor se škáluje bez závislosti na původním počtu pixelů.'],
        ],
        'color' => [
            'time' => '8–12 min', 'level' => 'Základ+', 'visual' => ['type' => 'color', 'mode' => 'rgb-cmyk'],
            'mental' => 'RGB je světlo na obrazovce, CMYK je model běžného čtyřbarevného tisku. Stejná „barva“ nemusí v obou světech vypadat stejně.',
            'steps' => ['Zjisti, zda výstup míří na obrazovku nebo tisk.', 'Pracuj v odpovídajícím barevném prostoru/workflow.', 'U tisku počítej s omezenějším gamutem.', 'Pro kritický tisk používej správný profil/soft proof podle procesu.', 'Vždy zkontroluj finální export.'],
            'mistakes' => ['Navrhnout neonovou RGB barvu a čekat stejný běžný CMYK tisk.', 'Převést vše do CMYK i pro web bez důvodu.', 'Ignorovat profil tiskárny/procesu.'],
            'check' => ['q' => 'Webový banner typicky připravíš v:', 'options' => ['RGB.', 'CMYK jako povinnost.', 'Pouze černobíle.'], 'correct' => 0, 'why' => 'Digitální obrazovky pracují s RGB světlem.'],
        ],
        'export' => [
            'time' => '8–12 min', 'level' => 'Základ+', 'visual' => ['type' => 'export'],
            'mental' => 'Dobrý návrh může zničit špatný export. Výstup je součást designu: rozměr, rozlišení, komprese, formát a cílové médium.',
            'steps' => ['Zjisti cílový rozměr a médium.', 'Pro raster zkontroluj dostatek pixelů.', 'Vyber správný formát.', 'Nastav rozumnou kompresi.', 'Otevři exportovaný soubor a skutečně ho zkontroluj.'],
            'mistakes' => ['Odevzdat screenshot editoru místo exportu.', 'Zvětšovat malý obrázek a považovat to za vyšší kvalitu.', 'Použít obří PNG tam, kde stačí kvalitní JPG/WebP.'],
            'check' => ['q' => 'Co uděláš jako poslední krok po exportu?', 'options' => ['Otevřu a zkontroluji skutečný export.', 'Smažu zdrojový soubor.', 'Změním font bez dalšího exportu.'], 'correct' => 0, 'why' => 'Kontrola výsledného souboru zachytí chybějící prvky, špatné rozměry, artefakty i barvy.'],
        ],
        'assets' => [
            'time' => '8–12 min', 'level' => 'Základ+', 'visual' => ['type' => 'assets'],
            'mental' => 'Obrázek na internetu není automaticky „zdarma k použití“. Designér řeší zároveň kvalitu zdroje, licenci, souhlas i dohledatelnost původu.',
            'steps' => ['Zjisti, odkud asset pochází.', 'Přečti licenci/podmínky.', 'Ověř povolené použití a případnou atribuci.', 'Ukládej si zdroj/odkaz/licenci.', 'Pro školní/portfolio projekt preferuj vlastní nebo jasně licencované materiály.'],
            'mistakes' => ['Google Images = fotobanka zdarma.', 'Odstranit watermark.', 'Ignorovat licenci u fontu nebo ikony.'],
            'check' => ['q' => 'Našel jsi fotografii přes Google Images. Co je správný další krok?', 'options' => ['Dohledat původní zdroj a licenci.', 'Automaticky ji použít.', 'Odstranit watermark.'], 'correct' => 0, 'why' => 'Vyhledávač není licence; je potřeba zjistit původ a podmínky použití.'],
        ],
    ],
];
