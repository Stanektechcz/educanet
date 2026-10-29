<?php

declare(strict_types=1);

return [
    'class_1a' => [
        [
            'id'=>'graphics_palette_icons','number'=>5,'title'=>'Lekce 5 · Paleta, ikony a konzistence','subtitle'=>'2 × 45 minut','goal'=>'Vytvořit malý vizuální jazyk z barev, ikon a obrazových pravidel a použít ho v jedné informační grafice.','knowledge'=>['color-harmony','iconography','image-composition'],
            'schedule'=>[['time'=>'0–12','title'=>'Paleta'],['time'=>'12–27','title'=>'Ikony'],['time'=>'27–42','title'=>'Obraz + focal point'],['time'=>'42–62','title'=>'Kompozice'],['time'=>'62–80','title'=>'Canva realizace'],['time'=>'80–90','title'=>'QA']],
            'steps'=>[
                ['id'=>'palette','time'=>'12 min','title'=>'01 · Paleta, která má role','kind'=>'knowledge','knowledge'=>['color-harmony'],'xp'=>25,'tasks'=>['Dokonči Color Harmony Lab.','Vyber background, text a accent.','Zapiš HEX hodnoty.']],
                ['id'=>'icons','time'=>'15 min','title'=>'02 · Jedna rodina ikon','kind'=>'knowledge','knowledge'=>['iconography'],'xp'=>25,'tasks'=>['Porovnej stroke/fill styl.','Vyber 3 ikony stejné rodiny.','Sjednoť jejich optickou velikost.']],
                ['id'=>'image','time'=>'15 min','title'=>'03 · Focal point a crop','kind'=>'knowledge','knowledge'=>['image-composition'],'xp'=>25,'tasks'=>['Najdi focal point.','Vyzkoušej dva cropy.','Vyber crop s místem pro text.']],
                ['id'=>'compose','time'=>'20 min','title'=>'04 · Informační karta','kind'=>'manual','xp'=>30,'tasks'=>['Vytvoř 1080×1350 informační kartu.','Použij 3 ikony max.','Zachovej jasné pořadí čtení.']],
                ['id'=>'canva','time'=>'18 min','title'=>'05 · Přenos do Canvy','kind'=>'manual','xp'=>30,'tasks'=>['Přenes systém do Canvy.','Nesnaž se „vylepšit“ každou ikonu jiným efektem.','Ověř thumbnail.']],
                ['id'=>'qa','time'=>'10 min','title'=>'06 · Konzistence audit','kind'=>'quiz','xp'=>20,'question'=>'Co nejvíc drží tři ikony pohromadě jako jeden systém?','options'=>['Stejný styl kresby, tloušťka a vizuální logika.','Každá ikona jiná barva a jiný stín.','Co největší počet detailů.'],'correct'=>0,'explanation'=>'Konzistence vzniká společnými pravidly, ne množstvím efektů.'],
            ],
        ],
        [
            'id'=>'graphics_redesign_case','number'=>6,'title'=>'Lekce 6 · Redesign: z chaosu na systém','subtitle'=>'2 × 45 minut','goal'=>'Analyzovat slabý návrh, formulovat problém a vytvořit nový plakát s jasně obhajitelnými rozhodnutími.','knowledge'=>['template-critique','color-harmony','image-composition','preflight'],
            'schedule'=>[['time'=>'0–15','title'=>'Diagnostika'],['time'=>'15–30','title'=>'Redesign plán'],['time'=>'30–48','title'=>'Skeleton'],['time'=>'48–68','title'=>'Canva redesign'],['time'=>'68–82','title'=>'A/B porovnání'],['time'=>'82–90','title'=>'Preflight']],
            'steps'=>[
                ['id'=>'critique','time'=>'15 min','title'=>'01 · Co je opravdu problém','kind'=>'knowledge','knowledge'=>['template-critique'],'xp'=>30,'tasks'=>['Najdi 3 konkrétní problémy.','Ke každému napiš důsledek pro diváka.','Neřeš zatím styl, jen funkci.']],
                ['id'=>'plan','time'=>'15 min','title'=>'02 · Plán redesignu','kind'=>'manual','xp'=>20,'tasks'=>['Urči první, druhou a třetí informaci.','Vyber paletu.','Urči obrazový princip.']],
                ['id'=>'skeleton','time'=>'18 min','title'=>'03 · Wireframe bez dekorací','kind'=>'manual','xp'=>25,'tasks'=>['Nakresli skeleton.','Ověř grid.','Ověř CTA.']],
                ['id'=>'build','time'=>'20 min','title'=>'04 · Canva redesign','kind'=>'manual','xp'=>30,'tasks'=>['Realizuj redesign.','Zachovej význam briefu.','Nepřidávej text, který nebyl potřeba.']],
                ['id'=>'compare','time'=>'14 min','title'=>'05 · A/B: staré vs. nové','kind'=>'quiz','xp'=>25,'question'=>'Co je nejlepší důkaz, že redesign funguje lépe?','options'=>['Jasnější hierarchie, čitelnost a splnění cíle briefu.','Víc gradientů a efektů.','Vyšší počet prvků.'],'correct'=>0,'explanation'=>'Redesign se hodnotí podle funkce a komunikace, ne podle množství dekorací.'],
                ['id'=>'preflight','time'=>'8 min','title'=>'06 · Finální preflight','kind'=>'knowledge','knowledge'=>['preflight'],'xp'=>20,'tasks'=>['Otevři export mimo editor.','Ověř údaje, CTA a rozměr.','Ulož before/after do jedné prezentace.']],
            ],
        ],
    ],
    'class_2a' => [
        [
            'id'=>'graphics_design_system','number'=>5,'title'=>'Lekce 5 · Design systém: spacing, komponenty, stavy','subtitle'=>'2 × 45 minut','goal'=>'Převést vizuální intuici do opakovatelného systému komponent a spacing tokenů pro jednoduché UI.','knowledge'=>['ui-spacing-system','component-consistency','contrast-color'],
            'schedule'=>[['time'=>'0–12','title'=>'Spacing tokens'],['time'=>'12–28','title'=>'Komponenty'],['time'=>'28–45','title'=>'Button states'],['time'=>'45–62','title'=>'Card system'],['time'=>'62–80','title'=>'Mini UI'],['time'=>'80–90','title'=>'Audit']],
            'steps'=>[
                ['id'=>'spacing','time'=>'12 min','title'=>'01 · Spacing tokeny','kind'=>'knowledge','knowledge'=>['ui-spacing-system'],'xp'=>25,'tasks'=>['Dokonči spacing simulaci.','Vyber 4–5 tokenů.','Použij je místo náhodných mezer.']],
                ['id'=>'components','time'=>'16 min','title'=>'02 · Komponenta jako pravidlo','kind'=>'knowledge','knowledge'=>['component-consistency'],'xp'=>25,'tasks'=>['Definuj button, card a tag.','Sjednoť radius a padding.','Nastav textové role.']],
                ['id'=>'states','time'=>'17 min','title'=>'03 · Stavy prvku','kind'=>'manual','xp'=>25,'tasks'=>['Navrhni default, hover a disabled.','Zachovej rozpoznatelnost akce.','Ověř kontrast textu.']],
                ['id'=>'cards','time'=>'17 min','title'=>'04 · Systém 3 karet','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř 3 obsahově různé karty.','Struktura musí být konzistentní.','Nepoužívej unikátní spacing pro každou kartu.']],
                ['id'=>'ui','time'=>'18 min','title'=>'05 · Mini dashboard','kind'=>'manual','xp'=>30,'tasks'=>['Poskládej header + 3 cards + CTA.','Použij pouze svůj systém.','Otestuj 1440 i 390 px.']],
                ['id'=>'audit','time'=>'10 min','title'=>'06 · System audit','kind'=>'quiz','xp'=>20,'question'=>'Kdy design systém skutečně pomáhá?','options'=>['Když stejné role vedou ke stejným pravidlům napříč obrazovkou.','Když má každý prvek vlastní náhodnou hodnotu.','Když je komponenta použitá právě jednou.'],'correct'=>0,'explanation'=>'Systém zrychluje rozhodování díky opakovatelnosti a konzistenci.'],
            ],
        ],
        [
            'id'=>'graphics_portfolio_proto','number'=>6,'title'=>'Lekce 6 · Portfolio case study + mikrointerakce','subtitle'=>'2 × 45 minut','goal'=>'Připravit krátkou case study vlastního projektu a navrhnout jednoduchou mikrointerakci, která má jasnou funkci.','knowledge'=>['portfolio-case-study','microinteraction-storyboard','portfolio-presentation'],
            'schedule'=>[['time'=>'0–15','title'=>'Case story'],['time'=>'15–30','title'=>'Before/after'],['time'=>'30–45','title'=>'Mikrointerakce'],['time'=>'45–62','title'=>'Storyboard'],['time'=>'62–80','title'=>'Prototype'],['time'=>'80–90','title'=>'Review']],
            'steps'=>[
                ['id'=>'story','time'=>'15 min','title'=>'01 · Problém → rozhodnutí → výsledek','kind'=>'knowledge','knowledge'=>['portfolio-case-study'],'xp'=>30,'tasks'=>['Napiš problém.','Vyber 2 klíčová rozhodnutí.','Ukaž výsledek.']],
                ['id'=>'before','time'=>'15 min','title'=>'02 · Evidence procesu','kind'=>'manual','xp'=>20,'tasks'=>['Připrav before/after.','Přidej jeden wireframe.','Přidej krátkou anotaci.']],
                ['id'=>'micro','time'=>'15 min','title'=>'03 · Proč animovat','kind'=>'knowledge','knowledge'=>['microinteraction-storyboard'],'xp'=>25,'tasks'=>['Vyber jednu akci: hover, success nebo loading.','Definuj trigger.','Definuj feedback.']],
                ['id'=>'board','time'=>'17 min','title'=>'04 · Storyboard 4 framy','kind'=>'manual','xp'=>25,'tasks'=>['Nakresli start.','Nakresli přechod.','Nakresli výsledek.','Uveď délku.']],
                ['id'=>'proto','time'=>'18 min','title'=>'05 · Rychlý prototyp','kind'=>'manual','xp'=>30,'tasks'=>['Vytvoř jednoduchý prototype/GIF/video.','Použij pohyb pouze pro feedback.','Ověř, že i bez animace zůstává stav pochopitelný.']],
                ['id'=>'review','time'=>'10 min','title'=>'06 · Portfolio review','kind'=>'knowledge','knowledge'=>['portfolio-presentation'],'xp'=>20,'tasks'=>['Uspořádej case study do 5 bloků.','Zkrať text.','Připrav 30s obhajobu.']],
            ],
        ],
    ],
    'class_3a' => [
        [
            'id'=>'net_ipv6_dns_records','number'=>5,'title'=>'Lekce 5 · IPv6 + DNS záznamy','subtitle'=>'2 × 45 minut','goal'=>'Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy.','knowledge'=>['ipv6-basics','dns-record-types'],
            'schedule'=>[['time'=>'0–15','title'=>'IPv6 formát'],['time'=>'15–30','title'=>'Prefix'],['time'=>'30–45','title'=>'A vs. AAAA'],['time'=>'45–62','title'=>'CNAME'],['time'=>'62–80','title'=>'Dual-stack incident'],['time'=>'80–90','title'=>'Exit']],
            'steps'=>[
                ['id'=>'ipv6','time'=>'15 min','title'=>'01 · IPv6 bez paniky','kind'=>'knowledge','knowledge'=>['ipv6-basics'],'xp'=>30,'tasks'=>['Projdi zkracování adres.','Najdi prefix /64.','Rozliš global a link-local.']],
                ['id'=>'prefix','time'=>'15 min','title'=>'02 · Co patří do /64','kind'=>'manual','xp'=>20,'tasks'=>['Porovnej dvě IPv6 adresy.','Urči síťovou část.','Vysvětli, co znamená /64.']],
                ['id'=>'records','time'=>'15 min','title'=>'03 · A / AAAA / CNAME','kind'=>'knowledge','knowledge'=>['dns-record-types'],'xp'=>30,'tasks'=>['Přiřaď typ záznamu k účelu.','Vysvětli A vs. AAAA.','Vysvětli alias CNAME.']],
                ['id'=>'cname','time'=>'17 min','title'=>'04 · Alias bez duplikace IP','kind'=>'manual','xp'=>25,'tasks'=>['Navrhni www → web.school.cz.','Zkontroluj, kam ukazuje cílové jméno.','Neduplikuj IP, pokud nepotřebuješ.']],
                ['id'=>'incident','time'=>'18 min','title'=>'05 · Dual-stack incident','kind'=>'quiz','xp'=>35,'question'=>'AAAA záznam ukazuje na nedostupnou IPv6 adresu, A záznam je správně. Co může uživatel pozorovat?','options'=>['Někteří klienti mohou zkoušet IPv6 a web může působit nedostupně nebo pomalu.','DNS se automaticky přepne na FTP.','IPv4 A záznam se smaže.'],'correct'=>0,'explanation'=>'Dual-stack klient může preferovat IPv6; chybný AAAA proto může způsobit reálný problém i při správném A.'],
                ['id'=>'exit','time'=>'10 min','title'=>'06 · Exit ticket','kind'=>'manual','xp'=>20,'tasks'=>['Napiš A/AAAA/CNAME vlastními slovy.','Uveď jeden příkaz pro ověření DNS.']],
            ],
        ],
        [
            'id'=>'net_monitoring_dhcp','number'=>6,'title'=>'Lekce 6 · Monitoring + DHCP reservations','subtitle'=>'2 × 45 minut','goal'=>'Přestat čekat na hlášení uživatele: měřit dostupnost služby a navrhnout stabilní DHCP adresaci pro známá zařízení.','knowledge'=>['monitoring-basics','dhcp-reservations'],
            'schedule'=>[['time'=>'0–15','title'=>'Co monitorovat'],['time'=>'15–30','title'=>'SLA signály'],['time'=>'30–45','title'=>'Reservation'],['time'=>'45–62','title'=>'Lease tabulka'],['time'=>'62–80','title'=>'Incident'],['time'=>'80–90','title'=>'Review']],
            'steps'=>[
                ['id'=>'monitor','time'=>'15 min','title'=>'01 · Host up ≠ služba OK','kind'=>'knowledge','knowledge'=>['monitoring-basics'],'xp'=>30,'tasks'=>['Porovnej ping, TCP a HTTP check.','Vyber správný check pro web.','Urči interval a timeout.']],
                ['id'=>'signals','time'=>'15 min','title'=>'02 · Signál bez šumu','kind'=>'manual','xp'=>20,'tasks'=>['Definuj 3 metriky.','Urči warning a critical.','Zabraň alertu z jednoho náhodného timeoutu.']],
                ['id'=>'reserve','time'=>'15 min','title'=>'03 · DHCP reservation','kind'=>'knowledge','knowledge'=>['dhcp-reservations'],'xp'=>30,'tasks'=>['Spáruj MAC a IP.','Zvol adresu mimo dynamický pool nebo podle politiky.','Vysvětli rozdíl reservation vs. ruční static IP.']],
                ['id'=>'leases','time'=>'17 min','title'=>'04 · Lease tabulka','kind'=>'manual','xp'=>25,'tasks'=>['Najdi zařízení podle MAC.','Zkontroluj konflikt.','Obnov lease klienta.']],
                ['id'=>'incident','time'=>'18 min','title'=>'05 · Monitoring hlásí web','kind'=>'quiz','xp'=>35,'question'=>'Ping serveru je OK, ale HTTP check vrací 503. Co je nejlepší první závěr?','options'=>['Síťová cesta k hostu funguje, ale aplikační služba není zdravá.','Server je vypnutý.','DNS určitě neexistuje.'],'correct'=>0,'explanation'=>'Ping ověřil dostupnost hostu; HTTP 503 je aplikační odpověď a posouvá diagnostiku výš.'],
                ['id'=>'review','time'=>'10 min','title'=>'06 · Review','kind'=>'manual','xp'=>20,'tasks'=>['Navrhni jeden monitoring check pro DNS.','Navrhni jednu reservation pro tiskárnu.']],
            ],
        ],
    ],
    'class_4a' => [
        [
            'id'=>'ops_http_lb','number'=>5,'title'=>'Lekce 5 · HTTP observability + load balancing','subtitle'=>'2 × 45 minut','goal'=>'Číst HTTP odpověď jako diagnostický důkaz a pochopit základní chování load balanceru při zdravém i degradovaném backendu.','knowledge'=>['http-observability','load-balancing'],
            'schedule'=>[['time'=>'0–15','title'=>'HTTP evidence'],['time'=>'15–30','title'=>'Headers'],['time'=>'30–45','title'=>'Load balancer'],['time'=>'45–62','title'=>'Health checks'],['time'=>'62–80','title'=>'Degradace'],['time'=>'80–90','title'=>'Postmortem']],
            'steps'=>[
                ['id'=>'http','time'=>'15 min','title'=>'01 · Status není jen číslo','kind'=>'knowledge','knowledge'=>['http-observability'],'xp'=>30,'tasks'=>['Rozliš 2xx/3xx/4xx/5xx.','Přečti Server, Location a Retry-After.','Urči, co odpověď dokazuje.']],
                ['id'=>'headers','time'=>'15 min','title'=>'02 · curl -I jako rychlá sonda','kind'=>'manual','xp'=>20,'tasks'=>['Interpretuj status.','Najdi redirect.','Najdi cache/trace header.']],
                ['id'=>'lb','time'=>'15 min','title'=>'03 · Rozdělení provozu','kind'=>'knowledge','knowledge'=>['load-balancing'],'xp'=>30,'tasks'=>['Přepínej round-robin/weighted.','Sleduj distribuci requestů.','Vyřaď unhealthy backend.']],
                ['id'=>'health','time'=>'17 min','title'=>'04 · Health check','kind'=>'manual','xp'=>25,'tasks'=>['Navrhni /health check.','Urči interval a fail threshold.','Nespoléhej jen na otevřený port.']],
                ['id'=>'degrade','time'=>'18 min','title'=>'05 · Jeden backend padá','kind'=>'quiz','xp'=>35,'question'=>'Load balancer stále posílá provoz na backend, který vrací 500. Co je potřeba zlepšit?','options'=>['Health check a pravidla vyřazení unhealthy backendu.','DNS TTL klienta.','Velikost SSH klíče.'],'correct'=>0,'explanation'=>'Load balancer musí mít signál, podle kterého nezdravý backend přestane dostávat běžný provoz.'],
                ['id'=>'post','time'=>'10 min','title'=>'06 · Mini postmortem','kind'=>'manual','xp'=>20,'tasks'=>['Napiš symptom.','Napiš důkaz.','Napiš preventivní kontrolu.']],
            ],
        ],
        [
            'id'=>'ops_canary_restore','number'=>6,'title'=>'Lekce 6 · Canary release + backup/restore drill','subtitle'=>'2 × 45 minut','goal'=>'Provést řízené nasazení na malou část provozu a nacvičit rozhodnutí rollback vs. restore na základě měřitelných signálů.','knowledge'=>['canary-release','backup-restore'],
            'schedule'=>[['time'=>'0–12','title'=>'Baseline'],['time'=>'12–28','title'=>'Canary 10 %'],['time'=>'28–45','title'=>'Metriky'],['time'=>'45–62','title'=>'Decision gate'],['time'=>'62–80','title'=>'Restore drill'],['time'=>'80–90','title'=>'Runbook']],
            'steps'=>[
                ['id'=>'baseline','time'=>'12 min','title'=>'01 · Než změníš produkci','kind'=>'manual','xp'=>20,'tasks'=>['Zapiš baseline error rate.','Zapiš latency p95.','Ověř poslední použitelný backup.']],
                ['id'=>'canary','time'=>'16 min','title'=>'02 · Canary místo big-bang','kind'=>'knowledge','knowledge'=>['canary-release'],'xp'=>30,'tasks'=>['Nastav 10 % provozu na v2.','Porovnej v1 vs. v2.','Definuj stop podmínku.']],
                ['id'=>'metrics','time'=>'17 min','title'=>'03 · Rozhodnutí podle dat','kind'=>'manual','xp'=>25,'tasks'=>['Sleduj error rate.','Sleduj latency.','Sleduj business/user check.']],
                ['id'=>'gate','time'=>'17 min','title'=>'04 · Promote nebo rollback','kind'=>'quiz','xp'=>35,'question'=>'Canary v2 má 4× vyšší error rate než baseline, latency je horší a trend trvá 5 minut. Co je nejbezpečnější další krok?','options'=>['Zastavit rollout a rollbackovat canary podle předem připraveného plánu.','Rozšířit v2 na 100 %, aby bylo více dat.','Smazat monitoring alert.'],'correct'=>0,'explanation'=>'Canary existuje právě proto, aby problém zastavil před plošným dopadem.'],
                ['id'=>'restore','time'=>'18 min','title'=>'05 · Restore drill','kind'=>'knowledge','knowledge'=>['backup-restore'],'xp'=>30,'tasks'=>['Vyber správný backup.','Ověř integritu.','Obnov do testovacího cíle.','Proveď funkční kontrolu.']],
                ['id'=>'runbook','time'=>'10 min','title'=>'06 · Runbook','kind'=>'manual','xp'=>20,'tasks'=>['Sepiš 5 kroků rollout/rollback.','Uveď ownera a success criteria.','Uveď restore checkpoint.']],
            ],
        ],
    ],
];
