<?php

declare(strict_types=1);

return [
    'class_1a' => [
        'spacing' => [
            'time'=>'7–10 min','level'=>'Základ','visual'=>['type'=>'compare','left'=>['Bez rytmu','8 / 27 / 13 / 41','vzdálenosti působí náhodně'],'right'=>['Systém','8 / 16 / 24 / 32','opakující se rytmus']],
            'mental'=>'Spacing vytváří vztahy. Malá mezera říká „patříme k sobě“, větší mezera „začíná nová skupina“.',
            'steps'=>['Najdi obsahové skupiny.','Zvol základní krok spacingu.','Uvnitř skupiny používej menší mezery.','Mezi skupinami použij větší mezery.','Zkontroluj rytmus jako thumbnail.'],
            'mistakes'=>['Každá mezera jiná bez důvodu.','Vyplnit každý prázdný prostor.','Zaměnit větší mezery za chybu kompozice.'],
            'check'=>['q'=>'Které pravidlo je dobrý start?','options'=>['Příbuzné prvky blíž, různé skupiny dál.','Všechny mezery stejně malé.','Prázdné místo vždy vyplnit.'],'correct'=>0,'why'=>'Proximity pomáhá oku poznat obsahové skupiny.'],
        ],
        'image-crop' => [
            'time'=>'8–12 min','level'=>'Základ','visual'=>['type'=>'compare','left'=>['Náhodný crop','subjekt uříznutý + text přes detail','pozornost nemá jasný cíl'],'right'=>['Řízený crop','focal point + klidná textová zóna','obraz a text spolupracují']],
            'mental'=>'Crop není technický detail. Určuje, co je nejdůležitější a kde vznikne prostor pro text.',
            'steps'=>['Najdi focal point.','Urči, kde musí být text.','Vyber crop pro konkrétní poměr stran.','Ověř, že důležitá část obrazu zůstala celá.','Zkontroluj kontrast textové zóny.'],
            'mistakes'=>['Automatický crop bez kontroly.','Text přes obličej nebo nejdetailnější část.','Stejný crop pro portrait i square.'],
            'check'=>['q'=>'Co řešíš jako první při ořezu?','options'=>['Focal point a cílový formát.','Exportní název souboru.','Počet vrstev.'],'correct'=>0,'why'=>'Výřez musí chránit hlavní obsah a fungovat v konkrétním poměru stran.'],
        ],
        'cta' => [
            'time'=>'6–9 min','level'=>'Základ','visual'=>['type'=>'compare','left'=>['Slabé CTA','více informací','nejasná akce'],'right'=>['Jasné CTA','REGISTRUJ SE →','konkrétní další krok']],
            'mental'=>'CTA uzavírá cestu oka: už vím, co se děje — teď vím, co mám udělat.',
            'steps'=>['Definuj jednu hlavní akci.','Napiš ji slovesem.','Dej CTA dostatečný kontrast.','Nech ho až po hlavní informaci.','Otestuj, zda ho najdeš během 3 sekund.'],
            'mistakes'=>['Tři stejně silná CTA.','Nejasný text „klikni zde“.','CTA jako nejméně čitelný prvek.'],
            'check'=>['q'=>'Které CTA je nejsrozumitelnější?','options'=>['REGISTRUJ SE →','Více zde','Něco udělej'],'correct'=>0,'why'=>'Konkrétní sloveso říká, jaká akce následuje.'],
        ],
        'preflight' => [
            'time'=>'7–10 min','level'=>'Základ','visual'=>['type'=>'export'],
            'mental'=>'Preflight znamená zastavit se před odevzdáním a zkontrolovat skutečný výstup, ne jen editor.',
            'steps'=>['Zkontroluj obsah a údaje.','Zkontroluj rozměr a formát.','Ověř čitelnost a okraje.','Otevři export mimo editor.','Pojmenuj soubor a teprve potom odevzdej.'],
            'mistakes'=>['Odevzdat screenshot.','Neotevřít finální export.','Přehlédnout špatné datum nebo QR.'],
            'check'=>['q'=>'Co je poslední krok před odevzdáním?','options'=>['Otevřít a zkontrolovat skutečný export.','Přidat další efekt.','Smazat zdroj.'],'correct'=>0,'why'=>'Výsledkem je exportovaný soubor, ne pracovní plocha editoru.'],
        ],
    ],
    'class_2a' => [
        'branding-system' => [
            'time'=>'9–13 min','level'=>'Střední','visual'=>['type'=>'layers','items'=>[['1','Typografie','stálá hierarchie'],['2','Barvy','hlavní + akcent'],['3','Obraz','crop / treatment'],['4','Grid','společné hrany'],['5','CTA','stejný charakter']]],
            'mental'=>'Vizuální systém je sada konstant. Formát se mění, identita zůstává rozpoznatelná.',
            'steps'=>['Sepiš 3–5 konstant kampaně.','Urči, co se smí měnit podle formátu.','Postav master návrh.','Adaptuj bez kopírování pozic.','Porovnej varianty vedle sebe.'],
            'mistakes'=>['Každý formát jiná typografie.','Považovat konzistenci za kopírování layoutu.','Přidávat další barvy při každé adaptaci.'],
            'check'=>['q'=>'Co má být při adaptaci nejstabilnější?','options'=>['Role prvků a vizuální systém.','Přesná pixelová pozice.','Stejný crop za každou cenu.'],'correct'=>0,'why'=>'Konzistence je o pravidlech a rolích, ne o identických souřadnicích.'],
        ],
        'photo-treatment' => [
            'time'=>'8–12 min','level'=>'Střední','visual'=>['type'=>'compare','left'=>['Bez treatmentu','text soutěží s detailem','slabá čitelnost'],'right'=>['Řízený obraz','crop + overlay + focal point','obraz podporuje zprávu']],
            'mental'=>'Obraz má sloužit komunikaci. Crop a treatment jsou stejně důležité jako výběr fotografie.',
            'steps'=>['Najdi focal point.','Vyber crop pro formát.','Najdi klidnou textovou zónu.','Případně použij overlay/tónování.','Ověř čitelnost i bez zoomu.'],
            'mistakes'=>['Text přes nejdetailnější oblast.','Přehnaný overlay, který zabije fotografii.','Stejný crop pro všechny formáty.'],
            'check'=>['q'=>'Proč měnit crop při adaptaci?','options'=>['Jiný poměr stran mění kompozici a prostor pro text.','Protože Canva to vyžaduje.','Aby byl soubor větší.'],'correct'=>0,'why'=>'Každý formát vytváří jiný prostor a jiné těžiště kompozice.'],
        ],
        'responsive-adaptation' => [
            'time'=>'10–14 min','level'=>'Střední','visual'=>['type'=>'flow','items'=>[['Master','1080×1350'],['Square','1080×1080'],['Story','1080×1920'],['Kontrola','hierarchie zůstává']]],
            'mental'=>'Responsive grafika zachovává zprávu a systém, ale znovu řeší layout pro nový prostor.',
            'steps'=>['Sepiš priority obsahu.','Zkopíruj design tokens, ne souřadnice.','Změň crop/kompozici.','Ověř CTA a safe area.','Exportuj každý formát samostatně.'],
            'mistakes'=>['Jen oříznout master.','Nechat text mimo safe area story.','Zmenšit vše rovnoměrně bez nové hierarchie.'],
            'check'=>['q'=>'Co při adaptaci NESMÍ být automatický cíl?','options'=>['Přesné zachování pozic.','Zachování role headline.','Zachování hlavní palety.'],'correct'=>0,'why'=>'Nový formát potřebuje nové rozmístění, ale stejné role a systém.'],
        ],
        'portfolio-presentation' => [
            'time'=>'8–12 min','level'=>'Střední','visual'=>['type'=>'sequence','items'=>[['Brief','co řeším'],['Systém','jaká pravidla'],['Master','hlavní návrh'],['Adaptace','důkaz flexibility'],['Rationale','proč to funguje']]],
            'mental'=>'Proces dává finálnímu vizuálu kontext a dokazuje, že rozhodnutí nejsou náhodná.',
            'steps'=>['Napiš cíl jednou větou.','Ukaž klíčová pravidla.','Ukaž master.','Přidej jednu adaptaci nebo A/B.','Vysvětli 2–3 rozhodnutí odbornými pojmy.'],
            'mistakes'=>['Prezentovat jen screenshot Canvy.','Obhajoba „líbí se mi to“.','Ukázat deset téměř stejných variant bez pointy.'],
            'check'=>['q'=>'Co patří do stručné obhajoby?','options'=>['Cíl + důvod klíčových rozhodnutí.','Seznam všech kliknutí v Canvě.','Jen osobní pocit.'],'correct'=>0,'why'=>'Obhajoba spojuje řešení s cílem a design principy.'],
        ],
    ],
    'class_3a' => [
        'vlan-basics' => [
            'time'=>'10–14 min','level'=>'Základ+','visual'=>['type'=>'flow','items'=>[['PC VLAN10','192.168.10.42'],['L3 gateway','routing / policy'],['Server VLAN20','192.168.20.20']]],
            'mental'=>'VLAN vytváří logické L2 hranice. Mezi VLAN musí provoz projít routováním, kde lze uplatnit bezpečnostní pravidla.',
            'steps'=>['Urči VLAN zdroje a cíle.','Porovnej subnety.','Zjisti L3 gateway.','Ověř inter-VLAN route/policy.','Validuj konkrétní službu.'],
            'mistakes'=>['Myslet si, že stejný switch = stejná síť.','Zapomenout na VLAN access port.','Testovat jen ping místo cílové služby.'],
            'check'=>['q'=>'PC ve VLAN10 chce server ve VLAN20. Co je potřeba?','options'=>['L3 routing mezi VLAN.','ARP přímo na vzdálený server bez routeru.','Jen DNS.'],'correct'=>0,'why'=>'Různé VLAN jsou oddělené L2 domény a komunikace mezi nimi potřebuje routing.'],
        ],
        'arp' => [
            'time'=>'8–11 min','level'=>'Základ+','visual'=>['type'=>'sequence','items'=>[['Host','Je cíl lokální?'],['ARP','Kdo má IP gateway?'],['Gateway','MAC odpověď'],['Ethernet','rámec na gateway']]],
            'mental'=>'ARP řeší lokální doručení rámce. Pro vzdálený cíl klient hledá MAC své gateway, ne MAC vzdáleného serveru.',
            'steps'=>['Rozhodni lokální/vzdálený cíl.','Pro lokální cíl hledej MAC cíle.','Pro vzdálený hledej MAC gateway.','Ověř ARP/neighbor cache.','Teprve potom sleduj L3 routing dál.'],
            'mistakes'=>['Čekat MAC vzdáleného internetového serveru v ARP cache.','Zaměnit ARP za DNS.','Ignorovat chybějící sousedství gateway.'],
            'check'=>['q'=>'Co bude mít klient v ARP cache při spojení na 8.8.8.8?','options'=>['MAC lokální gateway.','MAC serveru 8.8.8.8.','DNS TXT záznam.'],'correct'=>0,'why'=>'Ethernet rámec jde prvnímu lokálnímu hopu — gateway.'],
        ],
        'nat' => [
            'time'=>'9–13 min','level'=>'Základ+','visual'=>['type'=>'flow','items'=>[['192.168.10.42:51522','privátní klient'],['NAT/PAT','překlad'],['203.0.113.5:40012','veřejná strana'],['Internet','cílová služba']]],
            'mental'=>'NAT přepisuje adresní identitu na hranici sítě. PAT navíc používá porty, aby rozlišil více současných spojení.',
            'steps'=>['Urči privátní zdroj.','Najdi hraniční NAT zařízení.','Sleduj překlad zdrojové adresy/portu.','Zkontroluj návratovou tabulku.','Pro inbound provoz hledej explicitní mapování.'],
            'mistakes'=>['NAT = firewall.','NAT vyřeší automaticky příchozí server.','Překládat DNS jméno a NAT jako totéž.'],
            'check'=>['q'=>'Proč může více klientů sdílet jednu veřejnou IPv4?','options'=>['PAT rozlišuje spojení také porty.','Protože mají stejnou MAC.','DNS je spojí do jednoho.'],'correct'=>0,'why'=>'Překladová tabulka používá kombinaci adres a portů.'],
        ],
        'service-matrix' => [
            'time'=>'10–14 min','level'=>'Praktické','visual'=>['type'=>'ports','host'=>'server VLAN20','items'=>[['443','WEB','open'],['22','SSH admin only','local'],['53','DNS','open'],['3306','DB','closed']]],
            'mental'=>'Firewall pravidlo je konkrétní vztah zdroj → cíl → protokol/port → akce. Čím přesnější, tím lépe se vysvětluje a kontroluje.',
            'steps'=>['Sepiš potřebné toky.','Urči zdrojové subnety/VLAN.','Urči cílové služby a porty.','Povol jen potřebné kombinace.','Otestuj povolené i zakázané scénáře.'],
            'mistakes'=>['ALLOW any-any.','Testovat pravidlo jen z firewallu.','Neověřit, že zakázaný provoz je skutečně zakázaný.'],
            'check'=>['q'=>'Které pravidlo je přesnější?','options'=>['VLAN10 → WEB TCP/443 allow.','VLAN10 → všechny servery všechny porty allow.','Vypnout firewall.'],'correct'=>0,'why'=>'Princip nejmenších oprávnění povoluje jen potřebný tok.'],
        ],
    ],
    'class_4a' => [
        'reverse-proxy' => [
            'time'=>'10–14 min','level'=>'Pokročilé','visual'=>['type'=>'flow','items'=>[['Client','HTTPS :443'],['Nginx','TLS + routing'],['upstream','127.0.0.1:9000'],['App','HTTP response']]],
            'mental'=>'Reverse proxy odděluje veřejný vstup a interní aplikaci. 502 často říká „proxy žije, upstream neodpověděl správně“.',
            'steps'=>['Ověř klient → proxy.','Přečti status a proxy error log.','Ověř upstream adresu/port.','Otestuj upstream lokálně.','Oprav jednu věc a validuj z klienta.'],
            'mistakes'=>['Považovat 502 za DNS.','Restartovat vše bez měření.','Validovat jen localhost.'],
            'check'=>['q'=>'Nginx vrací 502. Co už víš?','options'=>['Klient dosáhl na proxy, problém může být mezi proxy a upstreamem.','DNS určitě neexistuje.','TCP/443 je určitě blokovaný.'],'correct'=>0,'why'=>'HTTP 502 je odpověď proxy, takže část cesty klient → proxy funguje.'],
        ],
        'tls-certificates' => [
            'time'=>'11–15 min','level'=>'Pokročilé','visual'=>['type'=>'layers','items'=>[['1','TCP/443','spojení'],['2','SNI','jméno webu'],['3','Certifikát','SAN + platnost'],['4','Chain','důvěra CA'],['5','HTTP','až potom aplikace']]],
            'mental'=>'TLS selhání je samostatná vrstva. Je možné mít funkční TCP, ale neplatný certifikát nebo špatný řetězec.',
            'steps'=>['Ověř TCP/443.','Použij správné SNI/jméno.','Zkontroluj SAN a expiraci.','Zkontroluj chain.','Po reloadu ověř certifikát zvenku.'],
            'mistakes'=>['Testovat jen IP a ignorovat SNI.','Obnovit certifikát, ale nereloadovat službu.','Kontrolovat pouze soubor na disku, ne prezentovaný certifikát.'],
            'check'=>['q'=>'Nový certifikát je na disku, klient stále vidí starý. Co ověříš?','options'=>['Zda služba načetla nový certifikát a co skutečně prezentuje na 443.','DHCP lease.','ARP vzdáleného klienta.'],'correct'=>0,'why'=>'Důležitý je certifikát prezentovaný aktivní službou.'],
        ],
        'logs-monitoring' => [
            'time'=>'10–15 min','level'=>'Pokročilé','visual'=>['type'=>'sequence','items'=>[['Alert','14:05 502 spike'],['Access log','request dorazil'],['Error log','connect refused upstream'],['App log','žádný request'],['Hypotéza','proxy → app']]],
            'mental'=>'Korelace více signálů zužuje prostor příčin rychleji než jeden obrovský log.',
            'steps'=>['Zapiš čas a scope.','Porovnej monitoring s requesty.','Najdi odpovídající error log.','Zkontroluj aplikační log ve stejném čase.','Po opravě sleduj metriky i nové logy.'],
            'mistakes'=>['grep bez časového rozsahu.','Hledat chybu jen v jednom logu.','Po opravě ignorovat monitoring.'],
            'check'=>['q'=>'Nginx access log request má, app log nic. Co to naznačuje?','options'=>['Problém může být mezi proxy a aplikací.','Klient určitě nemá DNS.','Aplikace request úspěšně zpracovala.'],'correct'=>0,'why'=>'Request do proxy dorazil, ale aplikace ho podle logu neviděla.'],
        ],
        'change-management' => [
            'time'=>'10–15 min','level'=>'Klíčové','visual'=>['type'=>'flow','items'=>[['Precheck','baseline'],['Change','jedna řízená změna'],['Validate','user path + metrics'],['Decision','keep / rollback'],['Document','co se stalo']]],
            'mental'=>'Bez rollbacku není produkční změna bezpečně naplánovaná. Validace musí být definovaná dřív než deploy.',
            'steps'=>['Definuj cíl a success criteria.','Ulož config/rollback.','Proveď omezenou změnu.','Validuj syntaxi i skutečný user flow.','Sleduj monitoring a rozhodni keep/rollback.'],
            'mistakes'=>['Rollback vymýšlet až po chybě.','Změnit více věcí bez baseline.','Za úspěch považovat jen „service active“.'],
            'check'=>['q'=>'Kdy má být připraven rollback?','options'=>['Před změnou.','Až po incidentu.','Jen když selže DNS.'],'correct'=>0,'why'=>'Rollback je součást plánu změny, ne improvizace po selhání.'],
        ],
    ],
];
