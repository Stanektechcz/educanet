<?php

declare(strict_types=1);

return [
 'class_1a'=>[
  'editorial-typography-i'=>[
   'title'=>'Typografie I · Škála, čitelnost a dlouhý text',
   'summary'=>'Navazuje na základní typografii: vytvoř jasnou škálu nadpisů a textu, nastav délku řádku a rytmus odstavců tak, aby se obsah četl bez námahy.',
   'body'=>[
    'Typografický systém není výběr hezkého fontu. Je to sada rolí: display, nadpis, perex, body text, metadata a CTA. Každá role má vlastní velikost, řádkování a váhu.',
    'U delšího textu hlídej délku řádku, kontrast a line-height. Čtenář musí rychle rozpoznat, co je hlavní a co podpůrné.',
    'Místo náhodných velikostí používej malou škálu. Konzistentní poměry pomáhají udržet rytmus napříč plakátem, prezentací i jednoduchou stránkou.',
   ],
   'example'=>'Display 48 px → H2 30 px → body 18 px → metadata 14 px. Stejný systém použij na dvou různých stranách a ověř, že role zůstávají čitelné.',
  ],
  'layout-rhythm-i'=>[
   'title'=>'Layout I · Rytmus, whitespace a baseline',
   'summary'=>'Rozšiř grid o vertikální rytmus: nauč se používat whitespace jako aktivní nástroj a skládat prvky do předvídatelného systému.',
   'body'=>[
    'Grid určuje hlavní konstrukci, ale čitelnost vzniká i ve vertikálním směru. Opakující se spacing vytváří rytmus a usnadňuje rychlé skenování.',
    'Whitespace není prázdné místo k zaplnění. Odděluje skupiny, zdůrazňuje prioritu a dává prvkům prostor.',
    'Silný layout používá několik opakovaných vzdáleností místo desítek náhodných hodnot. To je první krok k pozdějším design tokenům.',
   ],
   'example'=>'Použij rytmus 8 / 16 / 24 / 40 px. Mezi headline a perexem 16 px, mezi sekcemi 40 px, uvnitř jedné skupiny 8 px.',
  ],
  'visual-story-i'=>[
   'title'=>'Vizuální příběh I · Sekvence, focal point a tempo',
   'summary'=>'Nauč se řídit pozornost v čase: co divák uvidí první, co druhé a jak obraz, text a prázdné místo vytvoří jednoduchou vizuální sekvenci.',
   'body'=>[
    'Vizuální příběh funguje i na jednom statickém plakátu. Divák postupně čte dominantní prvek, kontext a akci.',
    'Focal point musí být vědomý. Pokud soutěží tři prvky o první místo, příběh se rozpadá.',
    'Sekvenci můžeš řídit velikostí, směrem pohledu fotografie, kontrastem, zarovnáním a mezerami.',
   ],
   'example'=>'Fotografie vede pohled k headline, headline k datu a datum k CTA. Otestuj návrh v malém náhledu a sleduj, zda pořadí zůstává stejné.',
  ],
  'production-preflight-i'=>[
   'title'=>'Produkční workflow I · Verze, naming a preflight',
   'summary'=>'Připrav návrh tak, aby ho dokázal správně otevřít, upravit a exportovat i někdo jiný: názvy souborů, verze, rozměry a kontrola před odevzdáním.',
   'body'=>[
    'Produkční pořádek je součást kvality. Finální soubor pojmenuj předvídatelně a nepoužívej názvy typu final_final2.',
    'Před exportem ověř rozměr, barevný režim nebo účel, ořez, čitelnost a správnou variantu.',
    'Odděl pracovní zdroj od exportu. Udržuj jasnou verzi masteru a cílové formáty.',
   ],
   'example'=>'event-poster_master_v03 → event-poster_instagram_1080x1350_v03.png. Preflight: rozměr, text, crop, kontrast, název a export preset.',
  ],
 ],
 'class_2a'=>[
  'responsive-type-ii'=>[
   'title'=>'Typografie II · Responsivní škála a optické vyvážení',
   'summary'=>'Navazuje na Typografii I: přizpůsobuj typografické role různým viewportům, pracuj s minimem/maximem a opticky vyvažuj velikost, řádkování a délku řádku.',
   'body'=>[
    'Responsivní typografie není lineární zmenšení desktopu. Každá role má bezpečné minimum, ideální rozsah a maximum.',
    'Headline může na mobilu změnit počet řádků, ale musí zachovat prioritu vůči perexu a CTA.',
    'Při změně šířky kontroluj line-height, délku řádku, wrap a vztah textu k okolnímu layoutu.',
   ],
   'example'=>'Desktop display 64 px, tablet 52 px, mobil 40 px. Body text zůstává 16–18 px; upravuje se především šířka sloupce a line-height.',
  ],
  'design-tokens-ii'=>[
   'title'=>'Design systém II · Sémantické tokeny a varianty',
   'summary'=>'Přejdi od jednotlivých spacing hodnot a barev k pojmenovaným tokenům, které drží celý produkt konzistentní a usnadňují změny.',
   'body'=>[
    'Token není jen číslo. Sémantický token popisuje účel: surface-primary, text-muted, space-section nebo radius-control.',
    'Komponenta používá tokeny místo náhodných lokálních hodnot. Změna tokenu se pak propíše do všech relevantních částí.',
    'Varianty komponent zachycují stav a kontext bez duplikování celé komponenty.',
   ],
   'example'=>'Button/Primary používá color-action-primary, text-on-action a radius-control. Hover mění action-primary-hover, ne ručně vybranou novou modrou.',
  ],
  'interaction-patterns-ii'=>[
   'title'=>'Interakce II · Stavový model a motion feedback',
   'summary'=>'Navazuje na mikrointerakce: navrhni celý stavový model prvku a pohyb používej pouze jako srozumitelnou zpětnou vazbu.',
   'body'=>[
    'Interaktivní prvek má více stavů než default a hover. Počítej s focus, active, loading, success, error a disabled.',
    'Animace má vysvětlovat změnu nebo potvrdit akci. Pokud pouze zdobí a zpomaluje úkol, je zbytečná.',
    'Mysli na reduced motion a na to, aby byl stav pochopitelný i bez animace.',
   ],
   'example'=>'Submit: default → loading se spinnerem → success s potvrzením. Při chybě se objeví text + ikona, ne jen červená barva.',
  ],
  'design-critique-ii'=>[
   'title'=>'Art direction II · Kritika, argumentace a handoff',
   'summary'=>'Posuň prezentaci návrhu od „líbí/nelíbí“ k profesionální kritice: cíl, evidence, kompromis, rozhodnutí a jasný handoff.',
   'body'=>[
    'Kritika má začít cílem: pro koho návrh je a jaký problém řeší. Teprve potom hodnotíš vizuální rozhodnutí.',
    'Silná obhajoba spojuje rozhodnutí s evidencí: čitelnost, hierarchie, accessibility, konzistence nebo výsledek testu.',
    'Handoff musí obsahovat stavy, tokeny, exporty a poznámky k chování, ne pouze screenshot.',
   ],
   'example'=>'„CTA jsem zvýraznil kontrastem a whitespace, protože v 3s testu zanikalo. Na mobilu přechází pod text a zachovává touch target 44 px.“',
  ],
 ],
 'class_3a'=>[
  'subnetting-vlsm'=>[
   'title'=>'IPv4 II · VLSM a efektivní subnetting',
   'summary'=>'Navazuje na CIDR a základní subnetting: rozděl jednu síť podle reálných potřeb různě velkých segmentů a ověř, že se subnety nepřekrývají.',
   'body'=>[
    'VLSM dovoluje použít v jedné adresní oblasti různé prefixy. Začni největší potřebou a pokračuj k menším segmentům.',
    'Každý subnet musí mít správnou network adresu, broadcast a rozsah hostů. Překryv je návrhová chyba, ne kosmetika.',
    'Dobré adresování nechává rozumnou rezervu a zároveň není zbytečně plýtvavé.',
   ],
   'example'=>'192.168.10.0/24 rozděl pro 90, 40, 20 a 10 hostů: nejprve /25, potom /26, /27 a /28. Nakonec ověř hranice.',
  ],
  'network-security-basics'=>[
   'title'=>'Síťová bezpečnost I · Zóny, ACL a least privilege',
   'summary'=>'Přejdi od pouhé konektivity k bezpečné konektivitě: definuj zóny, povolené služby a minimální potřebný přístup mezi segmenty.',
   'body'=>[
    'Firewall pravidlo má vycházet z potřeby služby, ne z pohodlí. Preferuj konkrétní source, destination, protocol a port.',
    'Trust zóny pomáhají přemýšlet o směru komunikace: users, servers, management, guest a internet.',
    'Least privilege znamená povolit jen to, co je potřeba, a validovat, že ostatní přístup zůstává blokovaný.',
   ],
   'example'=>'USERS → WEB TCP/443 ALLOW; USERS → SERVERS ANY DENY; MGMT → SERVERS TCP/22 ALLOW. Po změně otestuj i negativní scénář.',
  ],
  'dns-dhcp-operations'=>[
   'title'=>'DNS & DHCP II · Cache, lease a provozní změny',
   'summary'=>'Navazuje na DNS a DHCP základy: pochop dopad TTL, cache, lease time a změn adresace na reálný provoz.',
   'body'=>[
    'DNS změna se neprojeví všem klientům okamžitě. Resolver může držet starou hodnotu do vypršení TTL.',
    'DHCP lease má vlastní časový cyklus. Změna poolu nebo rezervace se nemusí projevit bez renew/rebind.',
    'Při změně služby plánuj přechodné období a ověřuj stav z více míst, ne jen z jednoho klienta.',
   ],
   'example'=>'Před migrací sniž TTL, po změně sleduj starou/novou odpověď a po stabilizaci TTL vrať. U DHCP ověř lease tabulku a renew klienta.',
  ],
  'service-debug-chain'=>[
   'title'=>'Diagnostika služby II · DNS → TCP → TLS → HTTP',
   'summary'=>'Spoj předchozí síťové znalosti do jedné rychlé diagnostické cesty a rozhoduj podle evidence, ve které vrstvě se problém nachází.',
   'body'=>[
    'Každý test má odpovídat na jednu otázku. DNS testuje překlad jména, TCP spojení port, TLS certifikát a handshake, HTTP stav aplikace.',
    'Nezačínej změnou konfigurace. Nejprve zjisti první vrstvu, která se chová jinak než očekáváš.',
    'Po opravě opakuj stejnou cestu a přidej negativní kontrolu, aby změna neotevřela něco navíc.',
   ],
   'example'=>'dig app.school.cz → Test-NetConnection :443 → openssl s_client → curl -I. Čtyři krátké testy dávají jasnou lokalizaci závady.',
  ],
 ],
 'class_4a'=>[
  'infrastructure-as-code'=>[
   'title'=>'Infrastructure as Code I · Desired state, plan a review',
   'summary'=>'Přeneste provozní změny z ručních kroků do deklarativního, verzovaného workflow s plánem změn a kontrolovatelným review.',
   'body'=>[
    'IaC popisuje požadovaný stav v souborech, které lze verzovat, kontrolovat a znovu aplikovat.',
    'Bezpečný workflow odděluje plan od apply. Nejdřív vidíš, co se má změnit, potom teprve změnu provedeš.',
    'Změna musí být malá, reviewovatelná a ideálně idempotentní — opakované spuštění nemá vytvářet další nečekané změny.',
   ],
   'example'=>'Pull request mění firewall rule a DNS record. CI vytvoří plan, reviewer ověří scope, apply proběhne po schválení a následuje validace.',
  ],
  'config-drift'=>[
   'title'=>'Configuration Drift · Desired vs. actual state',
   'summary'=>'Nauč se odhalit rozdíl mezi deklarovanou konfigurací a skutečným stavem prostředí a bezpečně rozhodnout, co je zdrojem pravdy.',
   'body'=>[
    'Drift vzniká ruční změnou, neúplným deploymentem nebo externím zásahem. Výsledkem je prostředí, které se liší od repozitáře.',
    'Nejprve drift detekuj a vysvětli. Automatické přepsání bez znalosti příčiny může incident zhoršit.',
    'Po nápravě vrať změnu do zdroje pravdy, aby se drift neobjevil znovu.',
   ],
   'example'=>'Repo očekává port 443, produkce má ručně změněný 8443. Plan ukáže rozdíl; tým zjistí důvod a rozhodne, zda opravit runtime nebo deklaraci.',
  ],
  'performance-engineering'=>[
   'title'=>'Performance Engineering I · Latency, throughput a saturation',
   'summary'=>'Rozliš hlavní výkonové signály a nauč se hledat skutečné úzké hrdlo místo slepého přidávání výkonu.',
   'body'=>[
    'Latency říká, jak dlouho operace trvá. Throughput kolik práce systém zvládne. Saturation ukazuje, jak blízko je zdroj limitu.',
    'Průměr může schovat problém. Sleduj percentile jako p95/p99 a koreluj je s CPU, pamětí, I/O, frontou a externími závislostmi.',
    'Optimalizace bez baseline není měřitelná. Nejdřív benchmark, potom jedna změna a nové měření.',
   ],
   'example'=>'p50 = 80 ms, p95 = 950 ms při 90% CPU. Průměr vypadá přijatelně, ale část uživatelů má výrazně horší zkušenost.',
  ],
  'capacity-planning'=>[
   'title'=>'Capacity Planning · Headroom, load test a scale decision',
   'summary'=>'Převeď výkonová data na plán kapacity: kolik rezervy potřebuješ, kdy škálovat a jak poznat, že load test odpovídá realitě.',
   'body'=>[
    'Kapacita není maximum, při kterém systém ještě odpoví. Potřebuješ headroom pro špičky, selhání části infrastruktury a růst.',
    'Load test musí reprezentovat reálný mix požadavků a měřit nejen RPS, ale i latency a error rate.',
    'Scale up, scale out, cache nebo optimalizace mají různé náklady a rizika. Rozhodnutí opři o bottleneck a očekávaný růst.',
   ],
   'example'=>'Při 700 RPS roste p95 nad SLO a CPU je 85 %. Cíl 900 RPS + 25% headroom znamená, že současná kapacita nestačí.',
  ],
 ],
];
