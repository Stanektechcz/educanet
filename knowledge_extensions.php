<?php

declare(strict_types=1);

return [
    'class_1a' => [
        'spacing' => [
            'title' => 'Spacing a vizuální rytmus',
            'summary' => 'Mezery nejsou zbytečné místo. Určují, co patří k sobě, co je oddělené a jak klidně nebo chaoticky návrh působí.',
            'body' => [
                'Dobré mezery vytvářejí skupiny informací. Prvky, které spolu významově souvisejí, mají být obvykle blíž než prvky z různých skupin.',
                'Pracuj s malým systémem opakujících se vzdáleností místo náhodných hodnot. Například základní krok 8 px může vytvořit rytmus 8 / 16 / 24 / 32.',
                'Když návrh působí přeplněně, první oprava často není zmenšit všechen text, ale odstranit zbytečné prvky a zvětšit prostor mezi hlavními skupinami.',
            ],
            'example' => 'Headline → 24 px mezera → datum/místo → 12 px mezera → CTA. V rámci jedné skupiny používej menší mezery než mezi skupinami.',
        ],
        'image-crop' => [
            'title' => 'Výřez fotografie a focal point',
            'summary' => 'Fotografie není jen výplň pozadí. Výřez rozhoduje, kam se divák podívá a kolik prostoru zbývá pro text.',
            'body' => [
                'Nejdřív najdi focal point: obličej, produkt nebo akci, která nese hlavní význam. Při ořezu ho neschovávej a zbytečně neřež důležité části objektu.',
                'Text potřebuje klidnou zónu. Pokud je celý obraz detailní a kontrastní, pomůže jiný crop, overlay nebo samostatný textový blok.',
                'Stejná fotografie může potřebovat jiný výřez pro portrait, square a story. Adaptace není jen změna velikosti plátna.',
            ],
            'example' => 'Pro portrétní plakát nech subjekt v pravé třetině a vlevo vytvoř klidnější plochu pro headline.',
        ],
        'cta' => [
            'title' => 'CTA: co má divák udělat dál',
            'summary' => 'Call to action převádí pozornost na akci. Musí být jasné, krátké a vizuálně rozpoznatelné.',
            'body' => [
                'CTA má odpovědět na otázku „co teď?“. Používej konkrétní slovesa: Přihlas se, Kup vstupenku, Zjisti více, Přijď.',
                'CTA nemusí být největší prvek. Musí ale být snadno nalezitelný po přečtení hlavní zprávy.',
                'Když CTA soutěží s pěti dalšími akcenty, ztrácí funkci. Jedna hlavní akce je pro jednoduchý plakát bezpečný základ.',
            ],
            'example' => 'Místo „Více informací zde“ použij „REGISTRUJ SE →“ a dej mu jasný kontrast vůči pozadí.',
        ],
        'preflight' => [
            'title' => 'Preflight před odevzdáním',
            'summary' => 'Preflight je poslední kontrola před exportem: obsah, rozměr, čitelnost, zdroje a skutečný výstupní soubor.',
            'body' => [
                'Zkontroluj texty, datum, místo, odkazy a QR kód. Design může být krásný a přesto selhat kvůli špatnému údaji.',
                'Ověř finální rozměr a formát. Pro digitální výstup kontroluj pixelové rozměry, pro tisk navíc požadavky tiskárny.',
                'Export vždy otevři mimo editor. Teprve skutečný soubor je výsledek, který uvidí klient nebo učitel.',
            ],
            'example' => 'Export PNG 1080×1350 → otevřít mimo Canvu → zkontrolovat headline, datum, CTA, okraje a artefakty.',
        ],
    ],
    'class_2a' => [
        'branding-system' => [
            'title' => 'Mini vizuální systém a konzistence',
            'summary' => 'Kampaň není jeden hezký plakát. Je to sada pravidel, díky kterým různé formáty vypadají jako jedna značka.',
            'body' => [
                'Vizuální systém může být velmi malý: jedna typografická hierarchie, 2–4 barvy, pravidlo pro obraz, grid a styl CTA.',
                'Konzistence neznamená kopírovat stejné rozložení. Znamená zachovat rozpoznatelné vztahy při adaptaci na jiný formát.',
                'Nejdřív definuj konstanty kampaně a potom teprve navrhuj varianty. Jinak se každý formát začne chovat jako nový samostatný projekt.',
            ],
            'example' => 'Konstanty: černé pozadí, neonový akcent, grotesk headline, CTA capsule. Mění se crop a rozložení podle formátu.',
        ],
        'photo-treatment' => [
            'title' => 'Práce s fotografií v kampani',
            'summary' => 'Fotografie musí podporovat sdělení a typografii. Crop, overlay, kontrast a barevná úprava jsou součást systému.',
            'body' => [
                'Urči focal point a nenech text soutěžit s nejdetailnější částí fotografie. Když je potřeba, změň crop nebo vytvoř klidovou zónu.',
                'Barevný overlay může sjednotit různé fotografie, ale nesmí zničit čitelnost ani důležitý obsah obrazu.',
                'Při adaptaci mezi formáty měň crop vědomě. Automatické „fit“ často vytvoří slabou kompozici.',
            ],
            'example' => 'Portrait: subjekt vpravo + text vlevo. Square: bližší crop, headline nahoře, CTA pod obrazem.',
        ],
        'responsive-adaptation' => [
            'title' => 'Adaptace designu do více formátů',
            'summary' => 'Převod z plakátu na square nebo story není resize. Musíš znovu vyřešit priority, crop a prostor.',
            'body' => [
                'Nejdřív zachovej obsahovou hierarchii: hlavní zpráva, klíčová informace, CTA. Teprve potom řeš polohu jednotlivých prvků.',
                'V úzkém story formátu funguje jiné rozložení než v širokém banneru. Některé prvky mohou změnit pozici, ale neměly by změnit roli.',
                'Kontroluj každou variantu samostatně v reálné velikosti i jako thumbnail. Jedna master kompozice nemusí být optimální všude.',
            ],
            'example' => '1080×1350 master → 1080×1080: zvětši focal point, zkrať info blok, CTA ponech výrazné a zachovej paletu.',
        ],
        'portfolio-presentation' => [
            'title' => 'Prezentace procesu a obhajoba návrhu',
            'summary' => 'Silný projekt není jen finální obrázek. Užitečné je ukázat brief, systém, varianty a vysvětlit rozhodnutí.',
            'body' => [
                'Prezentuj problém a cíl dřív než hotový design. Divák potom chápe, proč konkrétní řešení dává smysl.',
                'Ukazuj jen relevantní mezikroky: například dva cropy, změnu hierarchie nebo porovnání variant A/B.',
                'Obhajoba nemá být „líbí se mi to“. Používej pojmy jako hierarchie, kontrast, čitelnost, konzistence a cílové médium.',
            ],
            'example' => '1. brief → 2. design tokens → 3. master plakát → 4. square adaptace → 5. export + krátké zdůvodnění.',
        ],
    ],
    'class_3a' => [
        'vlan-basics' => [
            'title' => 'VLAN: logické oddělení jedné fyzické sítě',
            'summary' => 'VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu.',
            'body' => [
                'Zařízení ve stejné VLAN se typicky chovají jako v jedné L2 síti. Mezi různými VLAN je potřeba routing přes L3 zařízení.',
                'Access port obvykle patří do jedné VLAN pro koncové zařízení. Trunk přenáší více VLAN mezi síťovými prvky pomocí tagování.',
                'Při troubleshootingu ověř nejen IP adresu, ale i to, zda port klienta nebo serveru patří do očekávané VLAN.',
            ],
            'example' => 'PC VLAN10 192.168.10.42/24 → server VLAN20 192.168.20.20/24: komunikace musí projít routerem/firewallem.',
        ],
        'arp' => [
            'title' => 'ARP: jak host najde MAC adresu dalšího hopu',
            'summary' => 'IPv4 host potřebuje pro lokální Ethernet rámec znát MAC adresu cíle nebo výchozí brány. ARP propojuje IP a MAC na lokálním segmentu.',
            'body' => [
                'Když je cíl ve stejné síti, host hledá MAC cílového zařízení. Když je cíl mimo subnet, hledá MAC výchozí brány.',
                'ARP funguje jen v lokálním broadcast segmentu. Router nepřeposílá běžný ARP dotaz do vzdálené sítě.',
                'ARP cache může pomoci ověřit, zda klient vůbec získal L2 souseda pro bránu nebo lokální server.',
            ],
            'example' => 'arp -a / ip neigh: pro vzdálený 8.8.8.8 neuvidíš MAC Googlu, ale MAC své lokální gateway.',
        ],
        'nat' => [
            'title' => 'NAT/PAT: privátní klient ven na internet',
            'summary' => 'NAT mění adresy mezi privátní a veřejnou částí sítě. PAT navíc rozlišuje více spojení pomocí portů.',
            'body' => [
                'Domácí a malé firemní sítě typicky používají privátní IPv4 adresy. Hraniční router při odchodu provozu překládá zdrojovou adresu.',
                'NAT nenahrazuje firewall a sám o sobě nevysvětluje všechny problémy s konektivitou. Je to jedna část cesty.',
                'Pro příchozí službu z internetu bývá potřeba explicitní pravidlo, například port forwarding nebo reverzní proxy podle architektury.',
            ],
            'example' => '192.168.10.42:51522 → veřejná 203.0.113.5:40012 → internet; router drží překladovou tabulku.',
        ],
        'service-matrix' => [
            'title' => 'Service matrix: kdo smí kam a na jaký port',
            'summary' => 'Místo pravidla „povolíme server“ je přesnější popsat zdroj, cíl, protokol a port. To je základ čitelného firewall návrhu.',
            'body' => [
                'Service matrix převádí požadavky na konkrétní toky: například USERS → WEB → TCP/443 = allow.',
                'Výchozí deny je bezpečnější model než plošné povolování všeho. Povolení má být co nejpřesnější a zdůvodnitelné.',
                'Při validaci testuj provoz z reálného zdroje. Localhost na serveru neověří pravidla mezi VLAN.',
            ],
            'example' => 'VLAN10 users → 192.168.20.20 TCP/443 ALLOW; VLAN10 users → 192.168.20.30 TCP/22 DENY; admin VLAN → SSH ALLOW.',
        ],
    ],
    'class_4a' => [
        'reverse-proxy' => [
            'title' => 'Reverse proxy a upstream služby',
            'summary' => 'Reverse proxy přijímá požadavek klienta a předává ho interní aplikaci. Odděluje veřejný vstup od aplikačního procesu.',
            'body' => [
                'Nginx nebo jiná proxy může terminovat TLS, směrovat podle host/path a předávat provoz na interní upstream.',
                'HTTP 502 často znamená, že proxy odpověděla klientovi, ale nedokázala získat platnou odpověď upstreamu.',
                'Diagnostika má oddělit klient → proxy a proxy → upstream. Lokální curl na upstream je velmi užitečný důkaz.',
            ],
            'example' => 'client → https://app.example.cz → nginx:443 → proxy_pass http://127.0.0.1:9000',
        ],
        'tls-certificates' => [
            'title' => 'TLS certifikát: jméno, řetězec a platnost',
            'summary' => 'Úspěšný TCP/443 ještě neznamená funkční HTTPS. TLS musí ověřit certifikát, jméno a důvěryhodný řetězec.',
            'body' => [
                'Certifikát musí být platný pro jméno, které klient skutečně používá. Kontroluje se SAN/CN, platnost a důvěryhodnost certifikačního řetězce.',
                'Po obnově certifikátu je potřeba ověřit, že služba skutečně načetla nový soubor a prezentuje ho klientům.',
                'Nástroje jako curl -v nebo openssl s_client pomohou rozlišit TCP problém od TLS problému.',
            ],
            'example' => 'openssl s_client -connect app.example.cz:443 -servername app.example.cz',
        ],
        'logs-monitoring' => [
            'title' => 'Logy a monitoring: korelace místo hádání',
            'summary' => 'Monitoring říká, že něco selhává. Logy pomáhají vysvětlit proč. Největší hodnotu mají, když je spojíš s časem, requestem a změnou.',
            'body' => [
                'Začni konkrétním časem incidentu a scope. Hledání v nekonečném logu bez hypotézy je pomalé a produkuje šum.',
                'Porovnávej metriky, access/error log a aplikační log. Jeden zdroj často ukazuje symptom, jiný příčinu.',
                'Po změně validuj nejen HTTP odpověď, ale i monitoring a nové logy, aby se chyba tiše nevracela.',
            ],
            'example' => '14:05 alert 502 → nginx error.log 14:05 connect() failed → app log bez requestu → problém mezi proxy a upstreamem.',
        ],
        'change-management' => [
            'title' => 'Change window, validace a rollback',
            'summary' => 'Produkční změna není jen příkaz. Potřebuje cíl, očekávaný dopad, validační plán a bezpečný návrat zpět.',
            'body' => [
                'Před změnou si definuj baseline: co funguje, jak to změříš a jaké hodnoty očekáváš po zásahu.',
                'Dělej jednu řízenou změnu nebo jasně ohraničený balík. Když změníš pět věcí najednou, ztrácíš možnost určit příčinu.',
                'Rollback musí být připravený dřív než změna. Nesmí vznikat ve stresu až po incidentu.',
            ],
            'example' => 'Precheck → backup config → deploy → syntax test → reload → external validation → metrics → rollback if SLO fails.',
        ],
    ],
];
