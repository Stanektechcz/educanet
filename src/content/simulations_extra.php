<?php

declare(strict_types=1);

$graphics = [
    'contrast-color' => [
        'id' => 'gfx-contrast',
        'type' => 'contrast',
        'title' => 'Contrast Lab: je text opravdu čitelný?',
        'lead' => 'Měň barvu pozadí, textu a velikost písma. Sleduj orientační kontrastní poměr, náhled v grayscale a čitelnost v malém formátu.',
        'task' => 'Najdi kombinaci, která je čitelná v běžném i malém náhledu a nepoužívá barvu jako jediný nositel významu.',
        'prediction' => ['q' => 'Stačí, když jsou obě barvy hodně syté?', 'options' => ['Ano', 'Ne, důležitý je i rozdíl světlosti a velikost textu', 'Jen u plakátu'], 'correct' => 1],
        'hints' => ['Přepni náhled do grayscale. Pokud text téměř zmizí, sytost sama nepomohla.', 'Zkus velmi světlý text na tmavém pozadí nebo naopak.', 'Pro tento úkol dostaň orientační poměr alespoň na 4.5 : 1.'],
        'conclusion' => ['q' => 'Co je nejlepší závěr?', 'options' => ['Kontrast je jen otázka barevného vkusu.', 'Čitelnost vzniká kombinací kontrastu, velikosti, řezu a kontextu.', 'Neonové barvy jsou vždy nejčitelnější.'], 'correct' => 1],
        'points' => 5,
    ],
    'typography' => [
        'id' => 'gfx-type',
        'type' => 'typography',
        'title' => 'Type Lab: méně stylů, jasnější role',
        'lead' => 'Měň poměr titulku a body textu, řádkování a počet fontů. Náhled ti ukáže, kdy už systém působí roztříštěně.',
        'task' => 'Vytvoř tři čitelné textové úrovně s maximálně dvěma rodinami písem a dostatečným rozdílem mezi titulkem a běžným textem.',
        'prediction' => ['q' => 'Co obvykle zlepší plakát víc?', 'options' => ['Přidání čtvrtého dekorativního fontu', 'Jasnější rozdíl velikostí a konzistentní textové role', 'Náhodná změna zarovnání'], 'correct' => 1],
        'hints' => ['Nejdřív pracuj s velikostí a řezem, až potom s jiným fontem.', 'Headline by měl být jasně dominantní a body text pohodlně čitelný.', 'Zkus 1–2 fonty, headline alespoň 2× větší než body a line-height kolem 1.3–1.6.'],
        'conclusion' => ['q' => 'Co je typografický systém?', 'options' => ['Seznam všech fontů, které se mi líbí.', 'Opakovatelné role a vztahy mezi textovými úrovněmi.', 'Pouze velikost titulku.'], 'correct' => 1],
        'points' => 5,
    ],
    'raster-vector' => [
        'id' => 'gfx-raster-vector',
        'type' => 'rastervector',
        'title' => 'Scale Lab: kdy se logo rozsype?',
        'lead' => 'Porovnej rastrový a vektorový zdroj při zvětšení. Sleduj, jak se mění ostrost a kde dává který typ smysl.',
        'task' => 'Nastav velké zvětšení a vyber vhodný typ zdroje pro logo na plakátu i billboardu.',
        'prediction' => ['q' => 'Co se stane s malým PNG logem při extrémním zvětšení?', 'options' => ['Zůstane vždy dokonale ostré', 'Může se projevit pixelace', 'Automaticky se změní na SVG'], 'correct' => 1],
        'hints' => ['Raster má konečný počet pixelů.', 'Logo obvykle dává smysl držet ve vektoru.', 'Zkus zoom 800 % a porovnej PNG vs. SVG.'],
        'conclusion' => ['q' => 'Který závěr je správně?', 'options' => ['Vektor je vždy lepší i pro fotografie.', 'Raster a vektor řeší různé typy obsahu; logo typicky těží z vektoru.', 'PNG je vektorový formát.'], 'correct' => 1],
        'points' => 5,
    ],
    'color' => [
        'id' => 'gfx-output-color',
        'type' => 'colormode',
        'title' => 'Screen vs. print: co se stane s neonem?',
        'lead' => 'Přepínej mezi obrazovkovým a simulovaným tiskovým náhledem a sleduj, jak se extrémně syté barvy mohou změnit.',
        'task' => 'Vyber výstup pro web a následně simuluj tisk. Najdi paletu, která zůstává rozumně čitelná v obou režimech.',
        'prediction' => ['q' => 'Bude každá RGB barva na běžném tisku vypadat totožně?', 'options' => ['Ano', 'Ne', 'Jen modrá'], 'correct' => 1],
        'hints' => ['Obrazovka světlo vyzařuje, papír ho odráží.', 'Extrémně neonové RGB odstíny jsou dobrý příklad rozdílu.', 'Sniž saturaci neonu a sleduj stabilnější výsledek v print preview.'],
        'conclusion' => ['q' => 'Co má designér udělat před důležitým tiskem?', 'options' => ['Spoléhat jen na monitor.', 'Řídit se specifikací výstupu a kontrolovat proof/export.', 'Vždy převést vše do PNG.'], 'correct' => 1],
        'points' => 5,
    ],
];

$net3 = [
    'ip-addressing' => [
        'id'=>'net-subnet','type'=>'subnetlab','title'=>'Subnet Lab: jde paket přímo, nebo přes gateway?','lead'=>'Měň prefix a sleduj, zda klient považuje cíl za lokální. Síťová maska není dekorace — rozhoduje o cestě paketu.','task'=>'Nastav prefix tak, aby 192.168.10.42 a 192.168.10.200 byly ve stejné lokální síti.','prediction'=>['q'=>'Budou .42/25 a .200/25 ve stejném subnetu?','options'=>['Ano','Ne'],'correct'=>1],'hints'=>['/25 rozdělí poslední oktet na bloky 0–127 a 128–255.','Zdroj .42 je v prvním /25, cíl .200 ve druhém.','Použij /24.'],'conclusion'=>['q'=>'Co rozhoduje, zda host pošle paket přímo?','options'=>['Jen podobnost IP adres','IP adresa spolu s prefixem/maskou','DNS jméno'],'correct'=>1],'points'=>5,
    ],
    'dns' => [
        'id'=>'net-dns-cache','type'=>'dnscache','title'=>'DNS Cache Lab: proč někdo stále vidí starý server?','lead'=>'Změň TTL a virtuální čas. Sleduj authoritative odpověď a resolver cache jako dva různé zdroje pravdy.','task'=>'Dostaň klienta na novou IP tím, že necháš starou cache vypršet.','prediction'=>['q'=>'Může resolver po změně A záznamu dočasně vracet starou IP?','options'=>['Ano, pokud má stále platnou cache','Ne, změna je okamžitá všude'],'correct'=>0],'hints'=>['TTL určuje, jak dlouho může být odpověď cachovaná.','Porovnej elapsed time s TTL.','Nastav čas alespoň na hodnotu TTL.'],'conclusion'=>['q'=>'Co je správný závěr?','options'=>['Každá stará odpověď znamená rozbitý authoritative DNS.','Platná cache může dočasně vracet starou hodnotu.','TTL je číslo TCP portu.'],'correct'=>1],'points'=>5,
    ],
    'https' => [
        'id'=>'net-https-stack','type'=>'httpsstack','title'=>'HTTPS Stack: která vrstva selhala?','lead'=>'Zapínej a vypínej DNS, TCP, TLS a HTTP. Prohlížeč ukáže symptom, ale ty musíš určit vrstvu.','task'=>'Vytvoř stav, kdy DNS i TCP fungují, ale HTTPS selže na TLS.','prediction'=>['q'=>'Proběhne HTTP požadavek před TLS handshake?', 'options'=>['Ano','Ne, u HTTPS nejdřív vznikne TLS spojení'],'correct'=>1],'hints'=>['Nech DNS i TCP v pořádku.','Rozbij pouze certifikát/TLS.','DNS ON, TCP ON, TLS OFF.'],'conclusion'=>['q'=>'Jak troubleshootovat HTTPS nejlépe?','options'=>['Po vrstvách: DNS → TCP → TLS → HTTP/aplikace','Vždy restartovat webserver','Jen pingem'],'correct'=>0],'points'=>5,
    ],
    'routing' => [
        'id'=>'net-routing','type'=>'routinglab','title'=>'Gateway Lab: kudy paket opustí subnet?','lead'=>'Měň default gateway a sleduj, zda je pro klienta vůbec lokálně dosažitelná.','task'=>'Nastav platnou gateway pro klienta 192.168.10.42/24.','prediction'=>['q'=>'Může být default gateway 192.168.20.1 pro klienta 192.168.10.42/24 bez dalšího triku?', 'options'=>['Běžně ano','Běžně ne — není v lokálním /24'],'correct'=>1],'hints'=>['Klient musí gateway nejdřív doručit ethernetový rámec.','Gateway má být na lokálním segmentu klienta.','Vyber 192.168.10.1.'],'conclusion'=>['q'=>'K čemu je default gateway?', 'options'=>['K cestě do jiných sítí','K převodu domén','K šifrování TLS'],'correct'=>0],'points'=>5,
    ],
];

$net4 = [
    'cidr' => [
        'id'=>'adv-cidr-plan','type'=>'cidrplan','title'=>'CIDR Planner: nejmenší subnet pro 50 hostů','lead'=>'Měň prefix a sleduj počet použitelných adres i velikost bloku. Cílem není „větší je lepší“, ale rozumné sizing rozhodnutí.','task'=>'Vyber nejmenší běžný IPv4 subnet, který pojme alespoň 50 hostů.','prediction'=>['q'=>'Stačí /27 pro 50 běžných host adres?', 'options'=>['Ano','Ne'],'correct'=>1],'hints'=>['/27 má 32 adres celkem.','/26 má 64 adres celkem a typicky 62 použitelných host adres.','Vyber /26.'],'conclusion'=>['q'=>'Proč je /26 vhodnější než /24?', 'options'=>['Je nejmenší z nabízených variant, která splní kapacitu.','Protože má nejvíc adres.','Protože používá port 26.'],'correct'=>0],'points'=>5,
    ],
    'routing-advanced' => [
        'id'=>'adv-lpm','type'=>'lpm','title'=>'Longest Prefix Match: kterou route vybere kernel?','lead'=>'Pro jeden destination vidíš několik odpovídajících rout. Vyber tu nejkonkrétnější.','task'=>'Pro 10.20.30.55 vyber route, kterou má použít longest-prefix match.','prediction'=>['q'=>'Když odpovídá /8, /16 i /24, která route je nejkonkrétnější?', 'options'=>['/8','/16','/24'],'correct'=>2],'hints'=>['Delší prefix znamená menší a konkrétnější síť.','10.20.30.55 patří do 10.20.30.0/24.','Vyber /24.'],'conclusion'=>['q'=>'Jak se vybírá route mezi více shodami?', 'options'=>['Nejkratší prefix','Nejdelší odpovídající prefix','Vždy default'],'correct'=>1],'points'=>5,
    ],
    'dns-advanced' => [
        'id'=>'adv-dns-migrate','type'=>'dnsmigrate','title'=>'DNS Migration: plánuj TTL před změnou','lead'=>'Simuluj migraci služby na novou IP. Sleduj několik resolverů, které mají cache různě starou.','task'=>'Nastav přípravu migrace tak, aby maximální doba staré cache byla krátká.','prediction'=>['q'=>'Kdy je nejvhodnější snížit TTL pro plánovanou migraci?', 'options'=>['Až po změně IP','S předstihem před migrací','Nikdy'],'correct'=>1],'hints'=>['Staré vysoké TTL může zůstat v cache i po změně.','Sniž TTL ještě před přepnutím A záznamu.','Nastav pre-TTL 60 s.'],'conclusion'=>['q'=>'Co TTL ovlivňuje?', 'options'=>['Jak dlouho může resolver odpověď cachovat','TCP window','SSH permissions'],'correct'=>0],'points'=>5,
    ],
    'ssh-keys' => [
        'id'=>'adv-ssh-auth','type'=>'sshkeylab','title'=>'SSH Key Lab: proč publickey neprojde?','lead'=>'Měň permissions privátního klíče, username a shodu veřejného klíče. Sleduj, v jaké fázi autentizace spojení skončí.','task'=>'Nastav bezpečnou a funkční autentizaci veřejným klíčem.','prediction'=>['q'=>'Je 0644 vhodné oprávnění soukromého SSH klíče?', 'options'=>['Ano','Ne'],'correct'=>1],'hints'=>['Soukromý klíč nemá být čitelný ostatními uživateli.','Ověř také username a public key na serveru.','0600 + správný user + matching key.'],'conclusion'=>['q'=>'Co server potřebuje pro public-key auth?', 'options'=>['Soukromý klíč klienta','Odpovídající veřejný klíč a správného uživatele','DNS TXT se soukromým klíčem'],'correct'=>1],'points'=>5,
    ],
    'permissions' => [
        'id'=>'adv-permissions','type'=>'permissionslab','title'=>'Permissions Lab: minimum potřebných práv','lead'=>'Nastav mód pro soubor, který může deploy upravovat, webová skupina číst a ostatní nemají mít přístup.','task'=>'Vyber nejmenší oprávnění odpovídající owner rw, group r, other nic.','prediction'=>['q'=>'Který mód odpovídá rw-r-----?', 'options'=>['777','644','640','600'],'correct'=>2],'hints'=>['r=4, w=2, x=1.','Owner rw = 6, group r = 4, other nic = 0.','Vyber 640.'],'conclusion'=>['q'=>'Jaký princip je správný?', 'options'=>['Dávat 777, aby vše fungovalo','Použít nejmenší nutná oprávnění','Permissions neřešit, pokud je firewall'],'correct'=>1],'points'=>5,
    ],
];

return [
    'class_1a' => $graphics,
    'class_2a' => $graphics,
    'class_3a' => $net3,
    'class_4a' => $net4,
];
