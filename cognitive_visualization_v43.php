<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCAnet v43 · Cognitive Visualization Engine
 *
 * Every structured lesson gets one concrete LabSpec. Renderers are reused by
 * cognitive family so the system remains maintainable, but the problem,
 * mental model, transfer and memory snapshot stay lesson-specific.
 */

function cv43_family(string $classId, string $topic, array $lesson = []): string
{
    $t = strtolower($topic . ' ' . (string)($lesson['title'] ?? '') . ' ' . implode(' ', array_map('strval',(array)($lesson['knowledge']??[]))));
    if (in_array($classId,['class_1a','class_2a'],true)) {
        $rules = [
            'accessibility'=>['accessibility','a11y','contrast','color & type'],
            'typography'=>['typography','type-','microcopy'],
            'responsive'=>['responsive','css-responsive','art-direction'],
            'components'=>['component','auto-layout','design-system','design-token','spacing-system'],
            'forms'=>['form-','forms-','error-recovery'],
            'information'=>['information-architecture','card-sorting','user flow','research framing'],
            'image'=>['image','crop','photo','raster','vector'],
            'handoff'=>['handoff','design-qa','preflight','export','production'],
            'usability'=>['usability','critique','feedback'],
            'motion'=>['motion','microinteraction','interaction-pattern'],
            'branding'=>['branding','identity','iconography','color-harmony'],
            'layout'=>['layout','hierarchy','composition','content-first','landing','hero'],
            'portfolio'=>['portfolio','case study','mastery'],
        ];
    } else {
        $rules = [
            'dns'=>['dns'], 'dhcp'=>['dhcp'], 'routing'=>['routing','cidr','subnet','vlsm','ip-address','ipv6'],
            'packet'=>['packet','tcp','arp'], 'observability'=>['monitoring','observability','golden-signals','logs-monitoring','http-observability','slo'],
            'filesystem'=>['filesystem','storage','disk'], 'permissions'=>['permission','users-'], 'systemd'=>['systemd','process'],
            'logs'=>['journal','logs'], 'ssh'=>['ssh'], 'firewall'=>['firewall','network-security'],
            'service'=>['service','web-service','binding'], 'automation'=>['shell','bash','cron','automation'],
            'proxy'=>['proxy','tls','load-balancing'], 'release'=>['release','rollback','change-management','canary'],
            'backup'=>['backup','restore'], 'iac'=>['infrastructure-as-code','config-drift'], 'performance'=>['performance','capacity'],
            'containers'=>['container'], 'incident'=>['incident','diagnostics','postmortem'],
        ];
    }
    foreach ($rules as $family=>$needles) foreach ($needles as $needle) if (str_contains($t,$needle)) return $family;
    return in_array($classId,['class_1a','class_2a'],true) ? 'layout' : 'incident';
}

function cv43_family_template(string $family, string $classId): array
{
    $graphics = in_array($classId,['class_1a','class_2a'],true);
    $templates = [
        'layout'=>[
            'renderer'=>'design','problem'=>'Návrh obsahuje správné informace, ale oko neví, co má číst jako první.',
            'question'=>'Který zásah má nejdřív zlepšit orientaci bez přidávání další dekorace?',
            'options'=>['Změnit hierarchii velikostí, spacingu a pozice','Přidat další výraznou barvu','Zmenšit všechny mezery'], 'correct'=>0,
            'layers'=>[['id'=>'content','label'=>'Obsah','text'=>'Co musí uživatel opravdu najít.'],['id'=>'hierarchy','label'=>'Hierarchie','text'=>'Pořadí, ve kterém oko čte prvky.'],['id'=>'layout','label'=>'Layout','text'=>'Vztahy, zarovnání a rytmus.'],['id'=>'finish','label'=>'Vizuální finish','text'=>'Barva a detail až po vyřešení struktury.']],
            'timeline'=>['Najdi primární informaci','Označ sekundární obsah','Srovnej spacing a alignment','Ověř pohled z odstupu','Teprve potom dolaď detail'],
            'controls'=>[['id'=>'gap','label'=>'Spacing','min'=>4,'max'=>48,'value'=>12,'target'=>24,'unit'=>'px'],['id'=>'title','label'=>'Velikost nadpisu','min'=>18,'max'=>72,'value'=>32,'target'=>46,'unit'=>'px'],['id'=>'width','label'=>'Šířka obsahu','min'=>260,'max'=>760,'value'=>680,'target'=>520,'unit'=>'px']],
            'compare'=>['bad'=>'Všechny prvky mají téměř stejnou vizuální váhu.','good'=>'Primární zpráva vede, další informace mají jasně nižší prioritu.','why'=>'Hierarchie není jeden efekt; vzniká kombinací velikosti, váhy, prostoru, kontrastu a pozice.'],
            'model'=>['tokens'=>['Záměr uživatele','Primární informace','Sekundární informace','CTA','Vizuální detail'],'expected'=>['Záměr uživatele','Primární informace','Sekundární informace','CTA','Vizuální detail']],
        ],
        'typography'=>[
            'renderer'=>'design','problem'=>'Text je vizuálně efektní, ale při skutečném čtení se ztrácí rytmus a hierarchie.',
            'question'=>'Co máš upravit dřív než font efekty?',
            'options'=>['Velikost, řádkování, délku řádku a hierarchii','Přidat stín','Použít více fontů'], 'correct'=>0,
            'layers'=>[['id'=>'content','label'=>'Obsah','text'=>'Co text sděluje.'],['id'=>'hierarchy','label'=>'Typografická hierarchie','text'=>'Nadpis, perex, body a pomocné texty.'],['id'=>'rhythm','label'=>'Rytmus','text'=>'Line-height, mezery a délka řádku.'],['id'=>'font','label'=>'Font','text'=>'Volba řezu až v rámci funkčního systému.']],
            'timeline'=>['Urči role textu','Nastav hierarchii','Zkontroluj délku řádku','Vylaď line-height','Ověř reálný obsah'],
            'controls'=>[['id'=>'gap','label'=>'Řádkování','min'=>12,'max'=>40,'value'=>18,'target'=>26,'unit'=>'px'],['id'=>'title','label'=>'Nadpis','min'=>18,'max'=>72,'value'=>34,'target'=>48,'unit'=>'px'],['id'=>'width','label'=>'Délka řádku','min'=>280,'max'=>900,'value'=>820,'target'=>560,'unit'=>'px']],
            'compare'=>['bad'=>'Dlouhý řádek, malý rozdíl nadpis/body a stísněný rytmus.','good'=>'Čitelná délka řádku, stabilní rytmus a jednoznačná hierarchie.','why'=>'Typografie řídí čtení; font je pouze jedna část systému.'],
            'model'=>['tokens'=>['Obsah','Role textu','Hierarchie','Rytmus','Font'],'expected'=>['Obsah','Role textu','Hierarchie','Rytmus','Font']],
        ],
        'responsive'=>[
            'renderer'=>'design','problem'=>'Desktop vypadá dobře, ale na úzkém viewportu obsah přetéká a CTA mizí mimo první pohled.',
            'question'=>'Jaký je správný první krok?',
            'options'=>['Najít breakpoint podle chování obsahu a změnit layout','Jen zmenšit celý design na 70 %','Skrýt problematický obsah'], 'correct'=>0,
            'layers'=>[['id'=>'content','label'=>'Obsah','text'=>'Co musí zůstat dostupné.'],['id'=>'container','label'=>'Kontejner','text'=>'Dostupná šířka komponenty.'],['id'=>'rules','label'=>'Responsive pravidla','text'=>'Stack, wrap, min/max a breakpoint.'],['id'=>'art','label'=>'Art direction','text'=>'Crop a priorita obrazu podle prostoru.']],
            'timeline'=>['Zmenši viewport','Najdi první bod selhání','Změň pravidlo, ne obsah','Ověř extrémy','Otestuj touch/čitelnost'],
            'controls'=>[['id'=>'width','label'=>'Viewport','min'=>280,'max'=>1440,'value'=>1200,'target'=>390,'unit'=>'px'],['id'=>'gap','label'=>'Gap','min'=>4,'max'=>48,'value'=>28,'target'=>16,'unit'=>'px'],['id'=>'title','label'=>'Fluidní nadpis','min'=>22,'max'=>72,'value'=>58,'target'=>38,'unit'=>'px']],
            'compare'=>['bad'=>'Fixní dvousloupec přetéká na mobilu.','good'=>'Komponenta se skládá podle dostupného prostoru a zachovává prioritu obsahu.','why'=>'Responsive design není zmenšený desktop, ale sada pravidel pro různé podmínky.'],
            'model'=>['tokens'=>['Obsah','Container','Breakpoint','Layout rule','Art direction'],'expected'=>['Obsah','Container','Breakpoint','Layout rule','Art direction']],
        ],
        'accessibility'=>[
            'renderer'=>'design','problem'=>'Rozhraní působí čistě, ale část uživatelů nedokáže přečíst stav nebo ovládat prvek klávesnicí.',
            'question'=>'Co je správná diagnóza?',
            'options'=>['Ověřit kontrast, fokus, label a význam stavu','Přidat výraznější animaci','Zvětšit logo'], 'correct'=>0,
            'layers'=>[['id'=>'visual','label'=>'Vizuál','text'=>'Kontrast, velikost a stav.'],['id'=>'semantic','label'=>'Význam','text'=>'Label, role a srozumitelná chyba.'],['id'=>'keyboard','label'=>'Ovládání','text'=>'Fokus a pořadí klávesnice.'],['id'=>'motion','label'=>'Pohyb','text'=>'Informace nesmí záviset jen na animaci.']],
            'timeline'=>['Najdi bariéru','Ověř klávesnici','Změř kontrast','Zkontroluj label/stav','Ověř bez barvy/pohybu'],
            'controls'=>[['id'=>'contrast','label'=>'Kontrast','min'=>1,'max'=>10,'value'=>2,'target'=>5,'unit'=>':1'],['id'=>'title','label'=>'Text','min'=>12,'max'=>32,'value'=>14,'target'=>18,'unit'=>'px'],['id'=>'gap','label'=>'Touch target','min'=>24,'max'=>56,'value'=>28,'target'=>44,'unit'=>'px']],
            'compare'=>['bad'=>'Stav je sdělen jen barvou a focus není vidět.','good'=>'Stav je textově i vizuálně srozumitelný a ovladatelný klávesnicí.','why'=>'Přístupnost je vlastnost fungování rozhraní, ne dodatečná kosmetika.'],
            'model'=>['tokens'=>['Úkol uživatele','Semantika','Klávesnice','Vizuální stav','Reduced motion'],'expected'=>['Úkol uživatele','Semantika','Klávesnice','Vizuální stav','Reduced motion']],
        ],
        'components'=>[
            'renderer'=>'design','problem'=>'Stejný typ prvku má na třech obrazovkách jiné spacingy, radius a stavy.',
            'question'=>'Co opraví příčinu, ne jen screenshot?',
            'options'=>['Definovat tokeny, komponentu a varianty','Ruční doladění každé obrazovky','Přidat víc CSS selektorů'], 'correct'=>0,
            'layers'=>[['id'=>'tokens','label'=>'Tokeny','text'=>'Hodnoty mají jméno a smysl.'],['id'=>'component','label'=>'Komponenta','text'=>'Jedna struktura místo kopií.'],['id'=>'variants','label'=>'Varianty','text'=>'Explicitní stavy a velikosti.'],['id'=>'instances','label'=>'Instance','text'=>'Konkrétní použití bez driftu.']],
            'timeline'=>['Najdi opakování','Pojmenuj tokeny','Vytvoř komponentu','Přidej varianty','Stress-test obsahu'],
            'controls'=>[['id'=>'gap','label'=>'Spacing token','min'=>4,'max'=>40,'value'=>13,'target'=>16,'unit'=>'px'],['id'=>'title','label'=>'Type token','min'=>14,'max'=>40,'value'=>27,'target'=>24,'unit'=>'px'],['id'=>'radius','label'=>'Radius','min'=>0,'max'=>32,'value'=>19,'target'=>12,'unit'=>'px']],
            'compare'=>['bad'=>'Každá instance používá vlastní náhodné hodnoty.','good'=>'Instance sdílejí tokeny a varianty, změna pravidla se propíše konzistentně.','why'=>'Design systém minimalizuje náhodná rozhodnutí a drift.'],
            'model'=>['tokens'=>['Token','Component','Variant','Instance','QA'],'expected'=>['Token','Component','Variant','Instance','QA']],
        ],
        'forms'=>[
            'renderer'=>'design','problem'=>'Uživatel odešle formulář, dostane chybu, ale neví kde ani jak ji opravit.',
            'question'=>'Co musí recovery stav obsahovat?',
            'options'=>['Konkrétní chybu u pole, zachovaný vstup a další krok','Jen červený banner „chyba“','Vymazání formuláře a nový pokus'], 'correct'=>0,
            'layers'=>[['id'=>'input','label'=>'Vstup','text'=>'Srozumitelný label a očekávaný formát.'],['id'=>'validation','label'=>'Validace','text'=>'Kontrola ve správný čas.'],['id'=>'error','label'=>'Chyba','text'=>'Co je špatně a kde.'],['id'=>'recovery','label'=>'Recovery','text'=>'Jak to uživatel opraví bez ztráty práce.']],
            'timeline'=>['Vyplň formulář','Odešli','Najdi chybu','Oprav jen problém','Potvrď úspěch'],
            'controls'=>[['id'=>'gap','label'=>'Mezera label/pole','min'=>2,'max'=>32,'value'=>5,'target'=>10,'unit'=>'px'],['id'=>'title','label'=>'Text chyby','min'=>11,'max'=>24,'value'=>12,'target'=>16,'unit'=>'px'],['id'=>'contrast','label'=>'Kontrast stavu','min'=>1,'max'=>10,'value'=>2,'target'=>5,'unit'=>':1']],
            'compare'=>['bad'=>'Obecná chyba nahoře, pole bez vazby a ztracený vstup.','good'=>'Chyba je u konkrétního pole, popisuje opravu a zachovává data.','why'=>'Recovery je součást user flow, ne jen vizuální stav.'],
            'model'=>['tokens'=>['Label','Input','Validate','Error','Recovery','Success'],'expected'=>['Label','Input','Validate','Error','Recovery','Success']],
        ],
        'information'=>[
            'renderer'=>'map','problem'=>'Obsah existuje, ale struktura odráží interní názvy týmu místo úkolů uživatele.',
            'question'=>'Jak začít?',
            'options'=>['Seskupit obsah podle mentálního modelu a úkolů uživatele','Přidat další položky menu','Seřadit vše abecedně'], 'correct'=>0,
            'layers'=>[['id'=>'needs','label'=>'Potřeby','text'=>'Co uživatel hledá nebo chce udělat.'],['id'=>'content','label'=>'Obsah','text'=>'Skutečné informace a funkce.'],['id'=>'groups','label'=>'Skupiny','text'=>'Srozumitelné kategorie.'],['id'=>'navigation','label'=>'Navigace','text'=>'Cesta a názvy odpovídající očekávání.']],
            'timeline'=>['Sepiš obsah','Najdi uživatelské úkoly','Seskup kartami','Pojmenuj skupiny','Ověř jednoduchým taskem'],
            'controls'=>[],
            'compare'=>['bad'=>'Struktura kopíruje organizační oddělení.','good'=>'Struktura kopíruje způsob, jak uživatel přemýšlí o úkolu.','why'=>'IA snižuje nutnost hádat, kde informace jsou.'],
            'model'=>['tokens'=>['Uživatel','Úkol','Obsah','Skupina','Navigace'],'expected'=>['Uživatel','Úkol','Obsah','Skupina','Navigace']],
        ],
        'image'=>[
            'renderer'=>'design','problem'=>'Obraz vypadá správně v jednom formátu, ale crop nebo rozlišení selže v jiném použití.',
            'question'=>'Co máš definovat dřív než export?',
            'options'=>['Focal point, cílový poměr stran a potřebné rozlišení','Pouze maximální JPEG kvalitu','Jednu univerzální velikost pro vše'], 'correct'=>0,
            'layers'=>[['id'=>'source','label'=>'Zdroj','text'=>'Rozlišení a charakter obrazu.'],['id'=>'focal','label'=>'Focal point','text'=>'Co nesmí crop odstranit.'],['id'=>'crop','label'=>'Crop','text'=>'Kompozice v cílovém poměru.'],['id'=>'export','label'=>'Export','text'=>'Formát a rozměry pro reálné použití.']],
            'timeline'=>['Najdi focal point','Změň poměr stran','Zkontroluj crop','Ověř velikost výstupu','Exportuj pro konkrétní cíl'],
            'controls'=>[['id'=>'width','label'=>'Šířka výřezu','min'=>220,'max'=>900,'value'=>760,'target'=>520,'unit'=>'px'],['id'=>'focus','label'=>'Focal point','min'=>0,'max'=>100,'value'=>25,'target'=>58,'unit'=>'%'],['id'=>'quality','label'=>'Export quality','min'=>40,'max'=>100,'value'=>100,'target'=>82,'unit'=>'%']],
            'compare'=>['bad'=>'Jeden crop a jeden export pro všechny formáty.','good'=>'Focal point a art direction se přizpůsobují cílovému kontextu.','why'=>'Obrazový asset je součást layout systému, ne izolovaný soubor.'],
            'model'=>['tokens'=>['Zdroj','Focal point','Crop','Cílový slot','Export'],'expected'=>['Zdroj','Focal point','Crop','Cílový slot','Export']],
        ],
        'handoff'=>[
            'renderer'=>'map','problem'=>'Návrh vypadá správně v design nástroji, ale implementátor nezná pravidla ani stavy.',
            'question'=>'Co dělá handoff implementovatelným?',
            'options'=>['Pravidla, tokeny, stavy, responsive chování a acceptance criteria','Více screenshotů','Jedna poznámka „pixel perfect“'], 'correct'=>0,
            'layers'=>[['id'=>'intent','label'=>'Záměr','text'=>'Co komponenta řeší.'],['id'=>'rules','label'=>'Pravidla','text'=>'Layout, tokeny a chování.'],['id'=>'states','label'=>'Stavy','text'=>'Loading, error, empty, hover, focus.'],['id'=>'qa','label'=>'QA','text'=>'Jak ověřit, že implementace plní záměr.']],
            'timeline'=>['Pojmenuj záměr','Sepiš pravidla','Doplň stavy','Doplň responsive','Definuj QA kritéria'],
            'controls'=>[],
            'compare'=>['bad'=>'Handoff je obrázek bez pravidel.','good'=>'Implementátor zná záměr, tokeny, stavy i způsob validace.','why'=>'Kvalitní handoff předává systém rozhodnutí, ne jen pixely.'],
            'model'=>['tokens'=>['Intent','Rules','States','Responsive','QA'],'expected'=>['Intent','Rules','States','Responsive','QA']],
        ],
        'usability'=>[
            'renderer'=>'map','problem'=>'Tým hodnotí návrh podle osobních dojmů místo pozorování skutečného úkolu uživatele.',
            'question'=>'Jak získat použitelnou evidenci?',
            'options'=>['Dát konkrétní úkol, pozorovat chování a zapisovat evidence','Ptát se jen „líbí se ti to?“','Vysvětlovat uživateli správný postup'], 'correct'=>0,
            'layers'=>[['id'=>'task','label'=>'Úkol','text'=>'Konkrétní scénář bez nápovědy.'],['id'=>'observe','label'=>'Pozorování','text'=>'Co člověk opravdu udělal.'],['id'=>'evidence','label'=>'Evidence','text'=>'Kde váhal, chyboval nebo uspěl.'],['id'=>'change','label'=>'Změna','text'=>'Hypotéza pro další iteraci.']],
            'timeline'=>['Zadej task','Mlč a pozoruj','Zapiš momenty nejistoty','Odděl názor od evidence','Navrhni jednu změnu'],
            'controls'=>[],
            'compare'=>['bad'=>'„Nelíbí se mi to“ bez kontextu.','good'=>'„3/5 lidí nenašlo CTA do 20 s“ jako evidence problému.','why'=>'Design critique a usability test potřebují pozorovatelná kritéria.'],
            'model'=>['tokens'=>['Task','Observe','Evidence','Pattern','Iteration'],'expected'=>['Task','Observe','Evidence','Pattern','Iteration']],
        ],
        'motion'=>[
            'renderer'=>'design','problem'=>'Animace je efektní, ale nekomunikuje stav ani vztah mezi prvky.',
            'question'=>'Kdy má motion smysl?',
            'options'=>['Když vysvětluje změnu stavu, orientaci nebo příčinu/následek','Vždy, když je stránka prázdná','Když chceme skrýt pomalé načítání bez feedbacku'], 'correct'=>0,
            'layers'=>[['id'=>'state','label'=>'Stav','text'=>'Co se změnilo.'],['id'=>'origin','label'=>'Původ','text'=>'Odkud prvek přichází.'],['id'=>'motion','label'=>'Pohyb','text'=>'Vysvětluje vztah mezi stavy.'],['id'=>'reduced','label'=>'Reduced motion','text'=>'Stejná informace musí fungovat i bez animace.']],
            'timeline'=>['Urči změnu stavu','Definuj start/end','Zkrať pohyb na nutné minimum','Ověř reduced-motion','Otestuj pochopení bez efektu'],
            'controls'=>[['id'=>'duration','label'=>'Délka','min'=>80,'max'=>1200,'value'=>900,'target'=>260,'unit'=>'ms'],['id'=>'distance','label'=>'Vzdálenost','min'=>0,'max'=>160,'value'=>120,'target'=>24,'unit'=>'px']],
            'compare'=>['bad'=>'Dekorativní pohyb bez informační role.','good'=>'Krátký přechod vysvětluje, odkud nový stav vznikl.','why'=>'Motion má snižovat nejistotu, ne odvádět pozornost.'],
            'model'=>['tokens'=>['State A','Trigger','Transition','State B','Reduced motion'],'expected'=>['State A','Trigger','Transition','State B','Reduced motion']],
        ],
        'branding'=>[
            'renderer'=>'design','problem'=>'Jednotlivé výstupy vypadají dobře, ale značka se mezi formáty rozpadá.',
            'question'=>'Co tvoří konzistenci napříč formáty?',
            'options'=>['Pravidla pro typografii, barvu, obraz, spacing a komponenty','Stejné logo co největší na všem','Jeden přesný layout pro každý formát'], 'correct'=>0,
            'layers'=>[['id'=>'principles','label'=>'Principy','text'=>'Charakter značky a hierarchie.'],['id'=>'tokens','label'=>'Tokeny','text'=>'Barva, typografie, spacing.'],['id'=>'assets','label'=>'Assets','text'=>'Logo, obrazový styl, ikony.'],['id'=>'adapt','label'=>'Adaptace','text'=>'Stejný systém, jiné rozměry a priorita.']],
            'timeline'=>['Pojmenuj principy','Definuj tokeny','Ověř assety','Aplikuj na 3 formáty','Najdi nekonzistenci'],
            'controls'=>[['id'=>'gap','label'=>'Spacing','min'=>4,'max'=>40,'value'=>9,'target'=>16,'unit'=>'px'],['id'=>'title','label'=>'Display type','min'=>24,'max'=>72,'value'=>52,'target'=>44,'unit'=>'px']],
            'compare'=>['bad'=>'Každý formát řešen od nuly.','good'=>'Pravidla zůstávají, kompozice se přizpůsobí médiu.','why'=>'Identita je systém rozhodnutí, ne jedna šablona.'],
            'model'=>['tokens'=>['Princip','Token','Asset','Composition','Adaptation'],'expected'=>['Princip','Token','Asset','Composition','Adaptation']],
        ],
        'portfolio'=>[
            'renderer'=>'map','problem'=>'Portfolio ukazuje hezký výsledek, ale není z něj poznat problém, rozhodnutí ani růst.',
            'question'=>'Co má case study dokazovat?',
            'options'=>['Jaký problém byl, jaká evidence vedla k rozhodnutí a co se změnilo','Kolik mockupů se vejde na stránku','Pouze finální vizuál bez kontextu'], 'correct'=>0,
            'layers'=>[['id'=>'problem','label'=>'Problém','text'=>'Kontext a omezení.'],['id'=>'decision','label'=>'Rozhodnutí','text'=>'Proč byla zvolena cesta.'],['id'=>'iteration','label'=>'Iterace','text'=>'Co se nepovedlo a jak se návrh změnil.'],['id'=>'evidence','label'=>'Evidence','text'=>'Co výsledek dokládá o dovednosti.']],
            'timeline'=>['Pojmenuj problém','Vyber klíčové rozhodnutí','Ukaž jednu chybu/iteraci','Přidej výsledek','Připoj reflexi'],
            'controls'=>[],
            'compare'=>['bad'=>'Galerie finálních obrázků bez procesu.','good'=>'Příběh rozhodnutí s evidencí, iterací a výsledkem.','why'=>'Portfolio má dokazovat kompetenci, ne jen estetiku.'],
            'model'=>['tokens'=>['Problem','Evidence','Decision','Iteration','Outcome','Reflection'],'expected'=>['Problem','Evidence','Decision','Iteration','Outcome','Reflection']],
        ],
        'dns'=>[
            'renderer'=>'flow','problem'=>'Služba funguje přes IP adresu, ale ne přes hostname.',
            'question'=>'Který test má největší informační hodnotu jako první?',
            'options'=>['Ověřit DNS odpověď pro hostname','Restartovat webserver','Vypnout firewall'], 'correct'=>0,
            'layers'=>[['id'=>'name','label'=>'Jméno','text'=>'Aplikace pracuje s hostname.'],['id'=>'resolver','label'=>'Resolver','text'=>'Zjistí IP adresu.'],['id'=>'transport','label'=>'Síť','text'=>'Teprve potom vzniká spojení na IP.'],['id'=>'app','label'=>'Aplikace','text'=>'HTTP/HTTPS probíhá až po překladu jména.']],
            'timeline'=>['Aplikace použije hostname','Stub resolver pošle dotaz','Resolver získá odpověď','Klient má cílovou IP','Až potom naváže spojení'],
            'compare'=>['bad'=>'DNS → webová stránka','good'=>'DNS → IP → TCP/TLS → HTTP → obsah','why'=>'DNS nepřenáší web; pouze poskytuje informace potřebné k nalezení cíle.'],
            'model'=>['tokens'=>['Aplikace','Resolver','DNS odpověď','IP adresa','TCP/TLS','HTTP'],'expected'=>['Aplikace','Resolver','DNS odpověď','IP adresa','TCP/TLS','HTTP']],
        ],
        'dhcp'=>[
            'renderer'=>'flow','problem'=>'Nový klient se připojí do sítě, ale získá adresu 169.254.x.x a nedosáhne na gateway.',
            'question'=>'Co máš ověřit jako první?',
            'options'=>['Zda proběhla DHCP výměna Discover/Offer/Request/ACK','DNS cache','Restart webserveru'], 'correct'=>0,
            'layers'=>[['id'=>'discover','label'=>'Discover','text'=>'Klient hledá DHCP server.'],['id'=>'offer','label'=>'Offer','text'=>'Server nabízí parametry.'],['id'=>'request','label'=>'Request','text'=>'Klient žádá vybranou nabídku.'],['id'=>'ack','label'=>'ACK','text'=>'Server lease potvrzuje.']],
            'timeline'=>['DHCP Discover','DHCP Offer','DHCP Request','DHCP ACK','Klient nastaví IP/gateway/DNS'],
            'compare'=>['bad'=>'Klient má link, tedy musí mít i správnou IP konfiguraci.','good'=>'Link vrstva může fungovat, zatímco DHCP konfigurace selže.','why'=>'APIPA je symptom chybějící lease, ne důkaz chyby DNS.'],
            'model'=>['tokens'=>['Client','Discover','Offer','Request','ACK','Lease'],'expected'=>['Client','Discover','Offer','Request','ACK','Lease']],
        ],
        'routing'=>[
            'renderer'=>'flow','problem'=>'Lokální gateway odpovídá, ale cílová síť mimo subnet je nedostupná.',
            'question'=>'Který údaj rozhoduje o dalším hopu?',
            'options'=>['Routing table + prefix cíle','DNS TTL','Velikost MTU monitoru'], 'correct'=>0,
            'layers'=>[['id'=>'host','label'=>'Host','text'=>'Porovná cílovou IP se svými prefixy.'],['id'=>'route','label'=>'Route lookup','text'=>'Vybere nejdelší odpovídající prefix.'],['id'=>'gateway','label'=>'Next hop','text'=>'Předá rámec gateway.'],['id'=>'remote','label'=>'Remote network','text'=>'Další routery pokračují stejnou logikou.']],
            'timeline'=>['Host má cílovou IP','Longest-prefix match','Vybere next hop','ARP/ND pro next hop','Packet pokračuje'],
            'compare'=>['bad'=>'Každý vzdálený cíl se posílá „na internet“.','good'=>'Každý hop vybírá trasu podle routing table a prefixu.','why'=>'Routing je lokální rozhodnutí opakované na každém routeru.'],
            'model'=>['tokens'=>['Destination IP','Prefix match','Route','Next hop','Interface','Remote network'],'expected'=>['Destination IP','Prefix match','Route','Next hop','Interface','Remote network']],
        ],
        'packet'=>[
            'renderer'=>'flow','problem'=>'Aplikace hlásí timeout. Potřebuješ určit, ve které vrstvě komunikace se cesta zastavila.',
            'question'=>'Co ti dá packet/socket evidence?',
            'options'=>['Rozliší, zda selhal handshake, přenos nebo aplikační odpověď','Automaticky opraví konfiguraci','Nahradí potřebu hypotézy'], 'correct'=>0,
            'layers'=>[['id'=>'l2','label'=>'L2','text'=>'ARP/ND a doručení k next hopu.'],['id'=>'l3','label'=>'L3','text'=>'IP adresa a routing.'],['id'=>'l4','label'=>'L4','text'=>'TCP/UDP port a socket state.'],['id'=>'l7','label'=>'L7','text'=>'Aplikační protokol a odpověď.']],
            'timeline'=>['SYN odchází','SYN/ACK se vrací','ACK dokončí handshake','Aplikační request','Aplikační response'],
            'compare'=>['bad'=>'Timeout = „síť nefunguje“.','good'=>'Evidence ukáže konkrétní místo: routing, TCP handshake nebo aplikace.','why'=>'Stejný symptom může vzniknout v různých vrstvách.'],
            'model'=>['tokens'=>['L2 reachability','IP route','TCP state','Application request','Application response'],'expected'=>['L2 reachability','IP route','TCP state','Application request','Application response']],
        ],
        'observability'=>[
            'renderer'=>'metrics','problem'=>'Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors.',
            'question'=>'Co máš korelovat?',
            'options'=>['Metriky, logy, health a časovou osu změn','Jen průměrné CPU','Pouze poslední error log'], 'correct'=>0,
            'layers'=>[['id'=>'traffic','label'=>'Traffic','text'=>'Kolik práce systém dostává.'],['id'=>'errors','label'=>'Errors','text'=>'Kolik požadavků selhává.'],['id'=>'latency','label'=>'Latency','text'=>'Jak dlouho trvá odpověď.'],['id'=>'saturation','label'=>'Saturation','text'=>'Kde dochází kapacita.']],
            'timeline'=>['Baseline','Změna/deploy','Růst traffic','Saturace zdroje','Latency/errors rostou'],
            'compare'=>['bad'=>'Jedna metrika = příčina.','good'=>'Více signálů + časová korelace = podložená hypotéza.','why'=>'Observability pomáhá klást otázky nad systémem, ne jen sbírat grafy.'],
            'model'=>['tokens'=>['Traffic','Errors','Latency','Saturation','Change timeline'],'expected'=>['Traffic','Errors','Latency','Saturation','Change timeline']],
        ],
        'filesystem'=>[
            'renderer'=>'flow','problem'=>'Aplikace hlásí „No space left“, ale `df -h` stále ukazuje volnou kapacitu.',
            'question'=>'Co musíš ověřit vedle blokové kapacity?',
            'options'=>['Inody, mount point a místo zápisu','DNS TTL','TLS certifikát'], 'correct'=>0,
            'layers'=>[['id'=>'path','label'=>'Path','text'=>'Kam aplikace opravdu zapisuje.'],['id'=>'mount','label'=>'Mount','text'=>'Na jakém filesystemu cesta leží.'],['id'=>'blocks','label'=>'Blocks','text'=>'Datová kapacita filesystemu.'],['id'=>'inodes','label'=>'Inodes','text'=>'Počet souborových objektů může dojít dřív než GB.']],
            'timeline'=>['Aplikace otevře cestu','Kernel najde mount','Filesystem alokuje inode','Alokuje datové bloky','Write uspěje/selže'],
            'compare'=>['bad'=>'„Disk full“ znamená vždy 100 % v `df -h`.','good'=>'Selhat může kapacita, inody, permissions, mount nebo quota.','why'=>'Symptom pochází z konkrétního write path, ne z abstraktního „disku“.'],
            'model'=>['tokens'=>['Write path','Mount','Permissions','Inode','Data blocks'],'expected'=>['Write path','Mount','Permissions','Inode','Data blocks']],
        ],
        'permissions'=>[
            'renderer'=>'flow','problem'=>'Proces běží, soubor existuje, ale služba jej nedokáže přečíst.',
            'question'=>'Co musíš porovnat?',
            'options'=>['Identitu procesu s owner/group/mode/ACL cesty','DNS server','TCP congestion window'], 'correct'=>0,
            'layers'=>[['id'=>'process','label'=>'Process identity','text'=>'UID/GID procesu.'],['id'=>'path','label'=>'Path traversal','text'=>'Execute permission na adresářích.'],['id'=>'file','label'=>'File permissions','text'=>'Owner/group/other nebo ACL.'],['id'=>'access','label'=>'Access decision','text'=>'Kernel povolí nebo odmítne operaci.']],
            'timeline'=>['Proces má UID/GID','Projde adresářovou cestou','Kernel vyhodnotí oprávnění','Operace read/write','Allow / EACCES'],
            'compare'=>['bad'=>'Soubor existuje = proces ho může číst.','good'=>'Přístup závisí na identitě procesu a oprávněních celé cesty.','why'=>'Permissions jsou rozhodnutí kernelu pro konkrétní subjekt, objekt a operaci.'],
            'model'=>['tokens'=>['Process UID/GID','Directory path','Owner/group','Mode/ACL','Access decision'],'expected'=>['Process UID/GID','Directory path','Owner/group','Mode/ACL','Access decision']],
        ],
        'systemd'=>[
            'renderer'=>'flow','problem'=>'Služba se po restartu okamžitě vrací do failed nebo startuje ve špatném pořadí.',
            'question'=>'Kde hledat první důkaz?',
            'options'=>['Unit stav, dependencies a journal pro konkrétní start','DNS cache','Browser localStorage'], 'correct'=>0,
            'layers'=>[['id'=>'unit','label'=>'Unit','text'=>'Deklarace služby a jejího procesu.'],['id'=>'deps','label'=>'Dependencies','text'=>'Requires/After a pořadí.'],['id'=>'process','label'=>'Process','text'=>'Skutečný PID a exit status.'],['id'=>'journal','label'=>'Journal','text'=>'Časová evidence startu a selhání.']],
            'timeline'=>['systemd načte unit','Vyřeší dependencies','Spustí ExecStart','Proces běží/končí','Restart policy nebo failed'],
            'compare'=>['bad'=>'`systemctl start` = aplikace určitě funguje.','good'=>'Unit state, proces, socket a aplikační health jsou různé evidence.','why'=>'systemd řídí lifecycle procesu, ne garantovaný aplikační výsledek.'],
            'model'=>['tokens'=>['Unit','Dependencies','ExecStart','Process/PID','Journal','Health check'],'expected'=>['Unit','Dependencies','ExecStart','Process/PID','Journal','Health check']],
        ],
        'logs'=>[
            'renderer'=>'timeline','problem'=>'V logu je mnoho chyb, ale potřebuješ najít první událost, která změnila stav systému.',
            'question'=>'Jak pracovat s logy?',
            'options'=>['Sestavit časovou osu kolem symptomu a změny','Vybrat nejčervenější řádek','Číst od začátku souboru bez času'], 'correct'=>0,
            'layers'=>[['id'=>'change','label'=>'Change','text'=>'Deploy/config/restart.'],['id'=>'symptom','label'=>'Symptom','text'=>'První pozorovaný dopad.'],['id'=>'evidence','label'=>'Evidence','text'=>'Relevantní logy v časovém okně.'],['id'=>'verify','label'=>'Verification','text'=>'Co se stane po opravě.']],
            'timeline'=>['Known good','Změna konfigurace','První warning','První failure','Oprava + ověření'],
            'compare'=>['bad'=>'Jedna error message bez časového kontextu.','good'=>'Časová korelace změny, symptomu a dalších důkazů.','why'=>'Logy jsou evidence, ale příčina vzniká až jejich interpretací v kontextu.'],
            'model'=>['tokens'=>['Known good','Change','Symptom','Evidence window','Fix','Verification'],'expected'=>['Known good','Change','Symptom','Evidence window','Fix','Verification']],
        ],
        'ssh'=>[
            'renderer'=>'flow','problem'=>'TCP/22 odpovídá, ale SSH končí `Permission denied (publickey)`.',
            'question'=>'Co tato evidence už dokazuje?',
            'options'=>['Síť a sshd jsou dosažitelné; problém je v autentizační části','DNS určitě nefunguje','Firewall blokuje port 22'], 'correct'=>0,
            'layers'=>[['id'=>'network','label'=>'Network','text'=>'Dosažitelnost IP/portu.'],['id'=>'transport','label'=>'SSH transport','text'=>'Handshake a server banner.'],['id'=>'auth','label'=>'Authentication','text'=>'Uživatel, klíč, agent, authorized_keys.'],['id'=>'session','label'=>'Session','text'=>'Shell/SFTP až po úspěšné autentizaci.']],
            'timeline'=>['TCP connect','SSH handshake','Server nabízí auth metody','Client nabídne key','Server accept/reject','Session'],
            'compare'=>['bad'=>'Permission denied = firewall.','good'=>'Permission denied po handshake znamená, že cesta k sshd funguje a řešíme auth.','why'=>'Text chyby lokalizuje fázi protokolu.'],
            'model'=>['tokens'=>['TCP/22','SSH handshake','User','Client key','authorized_keys','Session'],'expected'=>['TCP/22','SSH handshake','User','Client key','authorized_keys','Session']],
        ],
        'firewall'=>[
            'renderer'=>'flow','problem'=>'Služba poslouchá na serveru, lokálně funguje, ale klient z jiné sítě se nepřipojí.',
            'question'=>'Co musíš odlišit?',
            'options'=>['Binding služby, routing a firewall policy/state','DNS od typografie','CPU od inode usage'], 'correct'=>0,
            'layers'=>[['id'=>'listener','label'=>'Listener','text'=>'Na jaké adrese/portu služba poslouchá.'],['id'=>'route','label'=>'Route','text'=>'Zda paket k serveru dorazí.'],['id'=>'policy','label'=>'Firewall policy','text'=>'Pravidlo pro směr, adresu, port a state.'],['id'=>'return','label'=>'Return path','text'=>'Odpověď musí mít platnou cestu zpět.']],
            'timeline'=>['Client SYN','Route to server','Firewall decision','Listener receives','SYN/ACK return'],
            'compare'=>['bad'=>'Open port v konfiguraci = dostupná služba.','good'=>'Dostupnost vyžaduje listener + route + policy + return path.','why'=>'Firewall je jen jedna část celé cesty.'],
            'model'=>['tokens'=>['Client','Route','Firewall rule/state','Listener','Return path'],'expected'=>['Client','Route','Firewall rule/state','Listener','Return path']],
        ],
        'service'=>[
            'renderer'=>'flow','problem'=>'Proces běží, ale uživatel stále dostává chybu nebo connection refused.',
            'question'=>'Co ověřit po process state?',
            'options'=>['Socket/binding, dependency a aplikační health','Jen PID','Barvu loga'], 'correct'=>0,
            'layers'=>[['id'=>'manager','label'=>'Service manager','text'=>'Unit/process lifecycle.'],['id'=>'process','label'=>'Process','text'=>'PID a exit status.'],['id'=>'socket','label'=>'Socket','text'=>'Kde proces opravdu poslouchá.'],['id'=>'health','label'=>'Application health','text'=>'Zda odpoví správný protokol a obsah.']],
            'timeline'=>['Unit start','Process spawn','Bind socket','Dependency ready','Health request','Valid response'],
            'compare'=>['bad'=>'Running process = funkční aplikace.','good'=>'Process, listener a application health jsou oddělené vrstvy.','why'=>'Každá vrstva potřebuje vlastní důkaz.'],
            'model'=>['tokens'=>['Unit','Process','Socket','Dependency','Health check','User request'],'expected'=>['Unit','Process','Socket','Dependency','Health check','User request']],
        ],
        'automation'=>[
            'renderer'=>'flow','problem'=>'Skript funguje při ručním spuštění, ale cron/automatizace někdy selže bez viditelné chyby.',
            'question'=>'Co z něj udělá bezpečnou automatizaci?',
            'options'=>['Explicitní vstupy, error handling, idempotence, log a exit status','Více `sleep`','Spouštět vše jako root'], 'correct'=>0,
            'layers'=>[['id'=>'input','label'=>'Inputs','text'=>'Explicitní cesty, proměnné a preconditions.'],['id'=>'action','label'=>'Action','text'=>'Jeden kontrolovaný krok.'],['id'=>'failure','label'=>'Failure handling','text'=>'Neskrývat chybu a nepokračovat naslepo.'],['id'=>'evidence','label'=>'Evidence','text'=>'Log/exit code a ověření výsledku.']],
            'timeline'=>['Validate inputs','Run idempotent action','Check result','Log evidence','Exit success/failure'],
            'compare'=>['bad'=>'Skript předpokládá prostředí a ignoruje chyby.','good'=>'Každý předpoklad je explicitní, chyba zastaví bezpečně a zůstane evidence.','why'=>'Automatizace násobí dobré i špatné předpoklady.'],
            'model'=>['tokens'=>['Precondition','Action','Check','Error path','Log','Exit status'],'expected'=>['Precondition','Action','Check','Error path','Log','Exit status']],
        ],
        'proxy'=>[
            'renderer'=>'flow','problem'=>'Reverse proxy vrací 502/504 nebo TLS chybu, ale klient se k proxy samotné dostane.',
            'question'=>'Jak rozdělit cestu?',
            'options'=>['Client↔proxy a proxy↔upstream ověřit odděleně','Restartovat DNS bez testu','Měnit frontend CSS'], 'correct'=>0,
            'layers'=>[['id'=>'client','label'=>'Client edge','text'=>'DNS/TCP/TLS k proxy.'],['id'=>'proxy','label'=>'Reverse proxy','text'=>'Host/path routing a TLS terminace.'],['id'=>'upstream','label'=>'Upstream connection','text'=>'Adresa, port, protocol, timeout.'],['id'=>'app','label'=>'Application','text'=>'Health a aplikační response.']],
            'timeline'=>['Client connects','TLS/HTTP request','Proxy selects upstream','Proxy connects upstream','App response','Proxy response'],
            'compare'=>['bad'=>'502 znamená, že nginx nefunguje.','good'=>'502 typicky znamená, že proxy request přijala, ale selhala cesta k upstreamu/odpovědi.','why'=>'Proxy rozděluje komunikaci na dva nezávisle ověřitelné úseky.'],
            'model'=>['tokens'=>['Client','DNS/TLS','Reverse proxy','Upstream socket','Application','Response'],'expected'=>['Client','DNS/TLS','Reverse proxy','Upstream socket','Application','Response']],
        ],
        'release'=>[
            'renderer'=>'timeline','problem'=>'Po deployi roste error rate. Potřebuješ rozhodnout, zda pokračovat, zastavit nebo rollbackovat.',
            'question'=>'Jaké rozhodnutí je bezpečné?',
            'options'=>['Porovnat předem definované health/SLO signály s rollback threshold','Čekat bez měření','Pokračovat, protože deploy doběhl bez chyby'], 'correct'=>0,
            'layers'=>[['id'=>'baseline','label'=>'Baseline','text'=>'Known-good stav před změnou.'],['id'=>'change','label'=>'Change','text'=>'Přesně známý scope deploye.'],['id'=>'observe','label'=>'Observe','text'=>'Health, errors, latency a business signal.'],['id'=>'rollback','label'=>'Rollback','text'=>'Předem připravený návrat a validace.']],
            'timeline'=>['Baseline','Deploy small scope','Observe','Threshold crossed?','Continue / rollback','Verify'],
            'compare'=>['bad'=>'Úspěšný CI job = bezpečný release.','good'=>'Release je změna + měření dopadu + připravený návrat.','why'=>'Bez rollback kritérií je rozhodnutí ovlivněné dojmem a tlakem.'],
            'model'=>['tokens'=>['Baseline','Change','Canary','Observe','Decision gate','Rollback/continue','Verify'],'expected'=>['Baseline','Change','Canary','Observe','Decision gate','Rollback/continue','Verify']],
        ],
        'backup'=>[
            'renderer'=>'timeline','problem'=>'Backup job je zelený, ale nikdo neověřil, zda lze data v požadovaném čase obnovit.',
            'question'=>'Co je skutečný důkaz funkční zálohy?',
            'options'=>['Úspěšný restore test splňující RPO/RTO','Existující ZIP soubor','Zelená ikona backup jobu'], 'correct'=>0,
            'layers'=>[['id'=>'source','label'=>'Source','text'=>'Co a v jaké konzistenci zálohujeme.'],['id'=>'backup','label'=>'Backup','text'=>'Kopie + metadata + retention.'],['id'=>'restore','label'=>'Restore','text'=>'Reálný návrat do izolovaného cíle.'],['id'=>'verify','label'=>'Verification','text'=>'Integrita, RPO/RTO a aplikační kontrola.']],
            'timeline'=>['Create backup','Store separately','Simulate loss','Restore','Validate integrity/app','Measure RPO/RTO'],
            'compare'=>['bad'=>'Backup exists = recovery guaranteed.','good'=>'Restore game day pravidelně ověřuje obnovitelnost a čas.','why'=>'Hodnota backupu se projeví až při úspěšné obnově.'],
            'model'=>['tokens'=>['Source','Backup','Independent storage','Loss event','Restore','Verify RPO/RTO'],'expected'=>['Source','Backup','Independent storage','Loss event','Restore','Verify RPO/RTO']],
        ],
        'iac'=>[
            'renderer'=>'flow','problem'=>'Produkce se liší od deklarované konfigurace, protože někdo provedl ruční změnu.',
            'question'=>'Jak minimalizovat drift?',
            'options'=>['Deklarovat stav, plánovat diff, reviewovat a aplikovat reprodukovatelně','Dokumentovat ruční změny v chatu','Zakázat monitoring'], 'correct'=>0,
            'layers'=>[['id'=>'desired','label'=>'Desired state','text'=>'Verzovaná deklarace.'],['id'=>'actual','label'=>'Actual state','text'=>'Co skutečně běží.'],['id'=>'diff','label'=>'Plan/diff','text'=>'Viditelný rozdíl před změnou.'],['id'=>'apply','label'=>'Apply + verify','text'=>'Kontrolovaná změna a následná validace.']],
            'timeline'=>['Read desired state','Read actual state','Compute diff','Review','Apply','Verify no drift'],
            'compare'=>['bad'=>'Ruční změny bez zdroje pravdy.','good'=>'Deklarovaný stav + diff + repeatable apply.','why'=>'IaC přesouvá infrastrukturu z paměti lidí do kontrolovaného systému změn.'],
            'model'=>['tokens'=>['Desired config','Actual config','Diff','Review','Apply','Verify'],'expected'=>['Desired config','Actual config','Diff','Review','Apply','Verify']],
        ],
        'performance'=>[
            'renderer'=>'metrics','problem'=>'Latency roste pod zátěží. Potřebuješ určit limitující zdroj místo náhodného škálování všeho.',
            'question'=>'Co hledáš?',
            'options'=>['Vztah load → saturation → latency/errors u konkrétního zdroje','Pouze nejvyšší CPU číslo','Největší log file'], 'correct'=>0,
            'layers'=>[['id'=>'load','label'=>'Load','text'=>'RPS/concurrency/throughput.'],['id'=>'resource','label'=>'Resource','text'=>'CPU, memory, IO, connections, queue.'],['id'=>'saturation','label'=>'Saturation','text'=>'Fronta a čekání na limitu.'],['id'=>'impact','label'=>'Impact','text'=>'Latency/errors a business dopad.']],
            'timeline'=>['Baseline load','Load grows','Resource approaches limit','Queue/saturation','Latency/errors rise'],
            'compare'=>['bad'=>'Vysoké CPU samo o sobě = bottleneck.','good'=>'Bottleneck je zdroj, jehož saturace koreluje s dopadem pod konkrétní zátěží.','why'=>'Capacity planning potřebuje model load, limitu a rezervy.'],
            'model'=>['tokens'=>['Load','Resource','Capacity limit','Saturation','Latency/errors','Headroom'],'expected'=>['Load','Resource','Capacity limit','Saturation','Latency/errors','Headroom']],
        ],
        'containers'=>[
            'renderer'=>'flow','problem'=>'Container běží, ale služba není dostupná z hosta nebo z jiné služby.',
            'question'=>'Co rozlišit?',
            'options'=>['Process, container network, binding/port publish a DNS/service discovery','Jen image tag','Jen host firewall'], 'correct'=>0,
            'layers'=>[['id'=>'process','label'=>'Process','text'=>'Co běží uvnitř containeru.'],['id'=>'network','label'=>'Container network','text'=>'Namespace a interní IP/DNS.'],['id'=>'binding','label'=>'Binding','text'=>'Na jaké adrese/portu proces poslouchá.'],['id'=>'publish','label'=>'Publish/route','text'=>'Jak se provoz dostane zvenku dovnitř.']],
            'timeline'=>['Container starts','Process binds','Service discovery/IP','Port publish/route','Client connect'],
            'compare'=>['bad'=>'Container running = port dostupný.','good'=>'Runtime stav, process listener a síťové zpřístupnění jsou různé vrstvy.','why'=>'Container přidává síťový namespace, ne magickou konektivitu.'],
            'model'=>['tokens'=>['Container','Process','Bind address','Container network','Published port/DNS','Client'],'expected'=>['Container','Process','Bind address','Container network','Published port/DNS','Client']],
        ],
        'incident'=>[
            'renderer'=>'incident','problem'=>'Je výpadek a tým má několik hypotéz. Největší riziko je měnit systém rychleji, než vzniká evidence.',
            'question'=>'Jaký je bezpečný první cyklus?',
            'options'=>['Symptom → hypotéza → nejmenší test → evidence → změna → validace','Restartovat vše','Provést několik změn současně'], 'correct'=>0,
            'layers'=>[['id'=>'symptom','label'=>'Symptom','text'=>'Co je pozorovatelně špatně.'],['id'=>'hypothesis','label'=>'Hypotéza','text'=>'Jedno testovatelné vysvětlení.'],['id'=>'evidence','label'=>'Evidence','text'=>'Nejmenší test s vysokou informační hodnotou.'],['id'=>'change','label'=>'Change','text'=>'Jedna reverzibilní změna.'],['id'=>'verify','label'=>'Verify','text'=>'Dopad změny proti původnímu symptomu.']],
            'timeline'=>['Stabilizuj/definuj symptom','Formuluj hypotézu','Proveď bezpečný test','Rozhodni podle evidence','Změň jednu věc','Ověř + dokumentuj'],
            'compare'=>['bad'=>'Více změn současně bez baseline.','good'=>'Malé reverzibilní kroky s důkazem před a po.','why'=>'Incident response optimalizuje rychlost učení o systému, ne počet provedených příkazů.'],
            'model'=>['tokens'=>['Symptom','Hypothesis','Test','Evidence','Change','Verification'],'expected'=>['Symptom','Hypothesis','Test','Evidence','Change','Verification']],
        ],
    ];
    if (isset($templates[$family])) return $templates[$family];
    if ($graphics) return $templates['layout'];
    return $templates['incident'];
}

function cv43_lab_spec(string $classId, array $lesson, array $module): array
{
    $topics=array_values(array_filter(array_map('strval',(array)($lesson['knowledge']??[]))));
    $topic=(string)($topics[0]??array_key_first((array)($module['knowledgebase']??[]))??'');
    $family=cv43_family($classId,$topic,$lesson); $base=cv43_family_template($family,$classId);
    $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];
    $title=(string)($lesson['title']??('Lekce '.(int)($lesson['number']??0)));
    $goal=trim((string)($lesson['goal']??''));
    $summary=trim((string)($article['summary']??''));
    $problem=(string)$base['problem'];
    if($goal!=='') $problem .= ' V této lekci je cílem: ' . rtrim($goal,'.') . '.';
    $vocab=[]; foreach(array_slice($topics,0,5) as $i=>$key){$a=is_array($module['knowledgebase'][$key]??null)?$module['knowledgebase'][$key]:[];$vocab[]=['term'=>(string)($a['title']??$key),'definition'=>trim((string)($a['summary']??'Klíčový pojem této lekce.')),'layer'=>(string)($base['layers'][$i%max(1,count($base['layers']))]['id']??'')];}
    if(!$vocab && $topic!=='') $vocab[]=['term'=>$topic,'definition'=>$summary!==''?$summary:'Klíčový pojem této lekce.','layer'=>''];
    $number=(int)($lesson['number']??0);
    // Correct choice must not always occupy the same visual position. Rotate
    // deterministically per class/lesson so screenshots remain stable while
    // students cannot exploit a fixed answer slot.
    $optionCount=count((array)$base['options']);
    if($optionCount>1){
        $classOffset=array_search($classId,['class_1a','class_2a','class_3a','class_4a'],true);if($classOffset===false)$classOffset=0;
        $shift=($number+(int)$classOffset)%$optionCount;
        $opts=array_values((array)$base['options']);
        $base['options']=array_merge(array_slice($opts,$shift),array_slice($opts,0,$shift));
        $base['correct']=(((int)$base['correct']-$shift)%$optionCount+$optionCount)%$optionCount;
    }
    $memorySentence=$summary!==''?$summary:($goal!==''?$goal:'Nejdřív rozliš vrstvy problému, potom zvol nejmenší test a závěr podlož evidencí.');
    $trap=$base['compare']['bad'];
    $transfer = in_array($classId,['class_1a','class_2a'],true)
        ? 'Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.'
        : 'Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.';
    return array_merge($base,[
        'id'=>$classId.'-L'.str_pad((string)$number,2,'0',STR_PAD_LEFT), 'class_id'=>$classId,'lesson_number'=>$number,'lesson_title'=>$title,
        'goal'=>$goal,'topic'=>$topic,'family'=>$family,'problem'=>$problem,'transfer'=>$transfer,'vocabulary'=>$vocab,
        'memory'=>['sentence'=>$memorySentence,'trap'=>$trap,'retrieval'=>'Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.'],
        'reverse'=>['result'=>$base['compare']['bad'],'prompt'=>'Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?','clues'=>array_slice(array_map(static fn($l)=>(string)$l['label'].': '.(string)$l['text'],(array)$base['layers']),0,3)],
        'teacher'=>['pause'=>'Zastav model těsně před výsledkem a nech studenty předpovědět další stav.','ask'=>'Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?','misconception'=>(string)$base['compare']['bad']],
    ]);
}

function cv43_all_lab_specs(): array
{
    static $cache=null;if(is_array($cache))return $cache;
    global $modules,$nextLessons,$extendedLessons;
    $out=[];foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){foreach(v42_lessons_for_class($classId) as $lesson){$spec=cv43_lab_spec($classId,$lesson,$modules[$classId]);$out[$spec['id']]=$spec;}}
    return $cache=$out;
}

function cv43_attempt_rows(): array { return adaptive_store('cv43_attempts'); }
function cv43_model_rows(): array { return adaptive_store('cv43_models'); }
function cv43_model_key(string $classId,string $studentKey,int $lessonNumber,string $phase='current'): string {return $classId.'|'.$studentKey.'|L'.$lessonNumber.'|'.$phase;}

function cv43_record_attempt(string $classId,string $studentKey,array $lesson,string $kind,bool $correct,array $meta=[]): array
{
    $spec=cv43_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$id='cv43_'.bin2hex(random_bytes(7));
    $lessonNumber=cv43_lesson_number($lesson);$row=['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>$lessonNumber,'lab_id'=>$spec['id'],'topic'=>$spec['topic'],'family'=>$spec['family'],'kind'=>$kind,'correct'=>$correct,'meta'=>$meta,'created_at'=>date(DATE_ATOM)];
    adaptive_store_update('cv43_attempts',static function(array $rows) use($id,$row): array {$rows[$id]=$row;if(count($rows)>4000)$rows=array_slice($rows,-3500,null,true);return $rows;});
    if(function_exists('ml_record_learning_event') && $spec['topic']!=='') ml_record_learning_event($classId,$studentKey,(string)$spec['topic'],'cognitive_'.$kind,$correct,['confidence'=>(int)($meta['confidence']??0),'lesson'=>$lessonNumber,'cognitive'=>true]);
    return $row;
}

function cv43_lesson_number(array $lesson): int
{
    $number=(int)($lesson['number']??0);
    // The canonical lesson-2 records in next_lessons.php predate the explicit number field.
    // Extended lessons already carry their number, so a missing value safely means lesson 2.
    return $number>0?$number:2;
}

function cv43_prediction_submit(string $classId,string $studentKey,array $lesson,int $answer,int $confidence=0): array
{
    $spec=cv43_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$correct=$answer===(int)$spec['correct'];
    cv43_record_attempt($classId,$studentKey,$lesson,'prediction',$correct,['answer'=>$answer,'confidence'=>$confidence]);
    return ['correct'=>$correct,'why'=>$correct?'Dobře. Teď sleduj, jak se tato volba projeví v jednotlivých vrstvách.':'Tahle volba řeší spíš následek nebo jinou vrstvu. Porovnej ji s problémem a vyber test s vyšší informační hodnotou.','correct_index'=>(int)$spec['correct']];
}

function cv43_model_submit(string $classId,string $studentKey,array $lesson,array $sequence,string $phase='current'): array
{
    $spec=cv43_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$expected=array_values(array_map('strval',(array)$spec['model']['expected']));$sequence=array_values(array_map('strval',$sequence));$correct=$sequence===$expected;
    $lessonNumber=cv43_lesson_number($lesson);$key=cv43_model_key($classId,$studentKey,$lessonNumber,$phase);storage_map_update(adaptive_store_path('cv43_models'),$key,static fn(?array $current): array => ['id'=>$key,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>$lessonNumber,'phase'=>$phase,'sequence'=>$sequence,'correct'=>$correct,'updated_at'=>date(DATE_ATOM)]);
    cv43_record_attempt($classId,$studentKey,$lesson,'mental_model',$correct,['phase'=>$phase,'sequence'=>$sequence]);
    $firstDiff=null;$max=max(count($expected),count($sequence));for($i=0;$i<$max;$i++){if(($expected[$i]??null)!==($sequence[$i]??null)){$firstDiff=$i;break;}}
    return ['correct'=>$correct,'expected'=>$expected,'first_diff'=>$firstDiff,'why'=>$correct?'Model odpovídá toku této lekce. Teď ho použij v jiné situaci.':'Model ještě nesedí. Zaměř se na první místo, kde se pořadí rozchází – systém ti nebere možnost pokus opravit.'];
}

function cv43_student_lab_state(string $classId,string $studentKey,array $lesson): array
{
    $lessonNumber=cv43_lesson_number($lesson);$models=cv43_model_rows();$current=$models[cv43_model_key($classId,$studentKey,$lessonNumber,'current')]??null;
    $attempts=[];foreach(cv43_attempt_rows() as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey&&(int)($r['lesson_number']??0)===$lessonNumber)$attempts[]=$r;
    return ['model'=>is_array($current)?$current:null,'attempts'=>$attempts,'prediction_done'=>count(array_filter($attempts,static fn($r)=>(string)($r['kind']??'')==='prediction'))>0];
}

function cv43_teacher_class_lab_summary(string $classId,int $lessonNumber): array
{
    $out=['attempts'=>0,'prediction_correct'=>0,'model_correct'=>0,'families'=>[]];
    foreach(cv43_attempt_rows() as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(int)($r['lesson_number']??0)!==$lessonNumber)continue;$out['attempts']++;$family=(string)($r['family']??'');$out['families'][$family]=($out['families'][$family]??0)+1;if((string)($r['kind']??'')==='prediction'&&!empty($r['correct']))$out['prediction_correct']++;if((string)($r['kind']??'')==='mental_model'&&!empty($r['correct']))$out['model_correct']++;}
    return $out;
}
