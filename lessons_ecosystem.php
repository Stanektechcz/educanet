<?php

declare(strict_types=1);

return [
 'class_1a'=>[
  [
   'id'=>'graphics_editorial_system','number'=>8,'title'=>'Lekce 8 · Typografie I + Layout I','subtitle'=>'2 × 45 minut','goal'=>'Převést základní znalost typografie a gridu do čitelného editorial layoutu s jasnou škálou, rytmem a whitespace.','knowledge'=>['editorial-typography-i','layout-rhythm-i'],
   'schedule'=>[['time'=>'0–15','title'=>'Typografické role'],['time'=>'15–30','title'=>'Škála a line-height'],['time'=>'30–45','title'=>'Rytmus'],['time'=>'45–62','title'=>'Editorial layout'],['time'=>'62–82','title'=>'Canva/Figma build'],['time'=>'82–90','title'=>'QA']],
   'steps'=>[
    ['id'=>'roles','time'=>'15 min','title'=>'01 · Typografické role','kind'=>'knowledge','knowledge'=>['editorial-typography-i'],'xp'=>30,'tasks'=>['Dokonči Type Scale Lab.','Pojmenuj 4 textové role.','Nastav jasný rozdíl headline/body.']],
    ['id'=>'scale','time'=>'15 min','title'=>'02 · Škála a čitelnost','kind'=>'manual','xp'=>20,'tasks'=>['Nastav velikosti rolí.','Ověř line-height.','Zkrať příliš dlouhý řádek.']],
    ['id'=>'rhythm','time'=>'15 min','title'=>'03 · Vertikální rytmus','kind'=>'knowledge','knowledge'=>['layout-rhythm-i'],'xp'=>30,'tasks'=>['Dokonči Vertical Rhythm Lab.','Vyber spacing škálu.','Odděl skupiny a sekce.']],
    ['id'=>'layout','time'=>'17 min','title'=>'04 · Editorial layout','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř dvousloupcový layout.','Použij whitespace jako aktivní prvek.','Srovnej hlavní hrany.']],
    ['id'=>'build','time'=>'20 min','title'=>'05 · Realizace','kind'=>'manual','xp'=>30,'tasks'=>['Přeneste layout do Canvy/Figmy.','Použij jen definované role.','Zachovej spacing systém.']],
    ['id'=>'qa','time'=>'8 min','title'=>'06 · Čitelnost','kind'=>'quiz','xp'=>20,'question'=>'Co nejlépe ukazuje, že typografický systém funguje?','options'=>['Role textu jsou rychle rozpoznatelné a rytmus se opakuje napříč layoutem.','Každý blok používá jinou velikost.','Na stránce nezůstalo žádné prázdné místo.'],'correct'=>0,'explanation'=>'Systém je čitelný, opakovatelný a předvídatelný.'],
   ],
  ],
  [
   'id'=>'graphics_story_delivery','number'=>9,'title'=>'Lekce 9 · Vizuální příběh + produkční workflow','subtitle'=>'2 × 45 minut','goal'=>'Spojit obraz, text a CTA do řízené cesty pozornosti a odevzdat výstup ve správně verzovaném, zkontrolovaném formátu.','knowledge'=>['visual-story-i','production-preflight-i'],
   'schedule'=>[['time'=>'0–15','title'=>'Focal point'],['time'=>'15–30','title'=>'Sekvence'],['time'=>'30–47','title'=>'Obraz + text'],['time'=>'47–65','title'=>'Varianta'],['time'=>'65–82','title'=>'Preflight'],['time'=>'82–90','title'=>'Delivery']],
   'steps'=>[
    ['id'=>'focus','time'=>'15 min','title'=>'01 · První pohled','kind'=>'knowledge','knowledge'=>['visual-story-i'],'xp'=>30,'tasks'=>['Dokonči Visual Story Lab.','Urči focal point.','Zapiš plánovanou cestu oka.']],
    ['id'=>'sequence','time'=>'15 min','title'=>'02 · Sekvence čtení','kind'=>'manual','xp'=>20,'tasks'=>['Seřaď headline/info/CTA.','Odstraň jednu konkurující dominantu.','Ověř 3s test.']],
    ['id'=>'image','time'=>'17 min','title'=>'03 · Obraz podporuje sdělení','kind'=>'manual','xp'=>25,'tasks'=>['Uprav crop.','Zkontroluj směr pohledu/linie.','Ověř čitelnost textu přes obraz.']],
    ['id'=>'variant','time'=>'18 min','title'=>'04 · Druhá varianta','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř B variantu stejného obsahu.','Změň art direction, ne význam.','Vyber lepší variantu podle cíle.']],
    ['id'=>'preflight','time'=>'17 min','title'=>'05 · Produkční preflight','kind'=>'knowledge','knowledge'=>['production-preflight-i'],'xp'=>30,'tasks'=>['Dokonči Preflight Lab.','Pojmenuj master a export.','Ověř rozměr, crop, kontrast a export.']],
    ['id'=>'delivery','time'=>'8 min','title'=>'06 · Delivery check','kind'=>'quiz','xp'=>20,'question'=>'Který soubor je nejlepší finální odevzdání?','options'=>['Jasně pojmenovaný export správného rozměru plus zachovaný editovatelný master.','final_final2.png bez zdrojového souboru.','Screenshot editoru.'],'correct'=>0,'explanation'=>'Produkční workflow musí být dohledatelné a znovu upravitelné.'],
   ],
  ],
 ],
 'class_2a'=>[
  [
   'id'=>'graphics_type_system_ii','number'=>8,'title'=>'Lekce 8 · Typografie II + Design systém II','subtitle'=>'2 × 45 minut','goal'=>'Navázat na první ročník a převést typografii, spacing a komponenty do responsivního systému řízeného tokeny.','knowledge'=>['responsive-type-ii','design-tokens-ii'],
   'schedule'=>[['time'=>'0–15','title'=>'Responsive type'],['time'=>'15–30','title'=>'Viewport audit'],['time'=>'30–45','title'=>'Token layers'],['time'=>'45–62','title'=>'Components'],['time'=>'62–82','title'=>'Mini system'],['time'=>'82–90','title'=>'QA']],
   'steps'=>[
    ['id'=>'type','time'=>'15 min','title'=>'01 · Responsive typografie','kind'=>'knowledge','knowledge'=>['responsive-type-ii'],'xp'=>30,'tasks'=>['Dokonči Responsive Type Lab.','Definuj desktop/mobile role.','Ověř headline wrap.']],
    ['id'=>'audit','time'=>'15 min','title'=>'02 · Viewport audit','kind'=>'manual','xp'=>20,'tasks'=>['Ověř 390/768/1440 px.','Najdi nečitelný stav.','Uprav line-height nebo width.']],
    ['id'=>'tokens','time'=>'15 min','title'=>'03 · Semantic tokens','kind'=>'knowledge','knowledge'=>['design-tokens-ii'],'xp'=>30,'tasks'=>['Dokonči Semantic Token Lab.','Odděl primitive/semantic.','Pojmenuj spacing a color tokeny.']],
    ['id'=>'components','time'=>'17 min','title'=>'04 · Varianty komponent','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř Button varianty.','Napoj tokeny.','Přidej hover/focus/disabled.']],
    ['id'=>'system','time'=>'20 min','title'=>'05 · Mini design system','kind'=>'manual','xp'=>35,'tasks'=>['Sestav 3 komponenty.','Použij jeden type scale.','Dokumentuj tokeny a stavy.']],
    ['id'=>'qa','time'=>'8 min','title'=>'06 · System QA','kind'=>'quiz','xp'=>20,'question'=>'Co je nejsilnější známka design systému?','options'=>['Změna tokenu se předvídatelně propíše do relevantních komponent.','Každá komponenta má vlastní náhodné hodnoty.','Všechny obrazovky jsou pixelově stejné.'],'correct'=>0,'explanation'=>'Systém vytváří sdílená pravidla a snižuje lokální duplicity.'],
   ],
  ],
  [
   'id'=>'graphics_interaction_handoff','number'=>9,'title'=>'Lekce 9 · Interakce II + profesionální handoff','subtitle'=>'2 × 45 minut','goal'=>'Navrhnout kompletní stavový model interakce, přidat smysluplný motion feedback a obhájit řešení pomocí evidence.','knowledge'=>['interaction-patterns-ii','design-critique-ii'],
   'schedule'=>[['time'=>'0–15','title'=>'State model'],['time'=>'15–30','title'=>'Motion'],['time'=>'30–47','title'=>'Prototype'],['time'=>'47–62','title'=>'Reduced motion'],['time'=>'62–80','title'=>'Critique'],['time'=>'80–90','title'=>'Handoff']],
   'steps'=>[
    ['id'=>'states','time'=>'15 min','title'=>'01 · Stavový model','kind'=>'knowledge','knowledge'=>['interaction-patterns-ii'],'xp'=>30,'tasks'=>['Dokonči State & Motion Lab.','Sepiš default/loading/success/error.','Přidej focus/disabled.']],
    ['id'=>'motion','time'=>'15 min','title'=>'02 · Motion jako feedback','kind'=>'manual','xp'=>20,'tasks'=>['Definuj trigger.','Nastav krátký feedback.','Odstraň zbytečný pohyb.']],
    ['id'=>'prototype','time'=>'17 min','title'=>'03 · Prototype','kind'=>'manual','xp'=>30,'tasks'=>['Propoj stavy.','Otestuj success/error.','Zkontroluj čitelnost feedbacku.']],
    ['id'=>'reduced','time'=>'15 min','title'=>'04 · Reduced motion','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř variantu bez pohybu.','Zachovej význam stavu.','Ověř keyboard focus.']],
    ['id'=>'critique','time'=>'18 min','title'=>'05 · Design critique','kind'=>'knowledge','knowledge'=>['design-critique-ii'],'xp'=>30,'tasks'=>['Dokonči Design Critique Lab.','Formuluj cíl a evidence.','Přidej konkrétní doporučení.']],
    ['id'=>'handoff','time'=>'10 min','title'=>'06 · Handoff','kind'=>'quiz','xp'=>20,'question'=>'Co má obsahovat handoff interaktivní komponenty?','options'=>['Stavy, tokeny, chování, accessibility poznámky a očekávaný feedback.','Jen jeden screenshot default stavu.','Pouze název komponenty.'],'correct'=>0,'explanation'=>'Implementace potřebuje znát celý stavový model, ne jen statický vzhled.'],
   ],
  ],
 ],
 'class_3a'=>[
  [
   'id'=>'net_vlsm_security','number'=>8,'title'=>'Lekce 8 · IPv4 II: VLSM + síťové zóny','subtitle'=>'2 × 45 minut','goal'=>'Navrhnout efektivní adresní plán a současně definovat minimální potřebnou komunikaci mezi zónami.','knowledge'=>['subnetting-vlsm','network-security-basics'],
   'schedule'=>[['time'=>'0–16','title'=>'VLSM'],['time'=>'16–31','title'=>'Address plan'],['time'=>'31–46','title'=>'Zones'],['time'=>'46–63','title'=>'ACL policy'],['time'=>'63–82','title'=>'Validation'],['time'=>'82–90','title'=>'Exit']],
   'steps'=>[
    ['id'=>'vlsm','time'=>'16 min','title'=>'01 · VLSM planner','kind'=>'knowledge','knowledge'=>['subnetting-vlsm'],'xp'=>30,'tasks'=>['Dokonči VLSM Planner Lab.','Seřaď segmenty podle velikosti.','Ověř hranice.']],
    ['id'=>'plan','time'=>'15 min','title'=>'02 · Address plan','kind'=>'manual','xp'=>25,'tasks'=>['Přiděl 4 subnety.','Zapiš gateway.','Nech rozumnou rezervu.']],
    ['id'=>'zones','time'=>'15 min','title'=>'03 · Trust zones','kind'=>'knowledge','knowledge'=>['network-security-basics'],'xp'=>30,'tasks'=>['Dokonči Security Zones Lab.','Definuj USERS/SERVERS/MGMT.','Sepiš potřebné služby.']],
    ['id'=>'policy','time'=>'17 min','title'=>'04 · ACL/service policy','kind'=>'manual','xp'=>25,'tasks'=>['Povol USERS→WEB 443.','Povol MGMT→SERVERS 22.','Blokuj ostatní serverový provoz.']],
    ['id'=>'validation','time'=>'19 min','title'=>'05 · Positive + negative validation','kind'=>'manual','xp'=>35,'tasks'=>['Ověř povolený HTTPS.','Ověř blokovaný SSH z USERS.','Ověř management SSH.','Zapiš důkazy.']],
    ['id'=>'exit','time'=>'8 min','title'=>'06 · Exit','kind'=>'quiz','xp'=>20,'question'=>'Co je správná validace firewall změny?','options'=>['Otestovat povolené i zakázané scénáře.','Jen ping gateway.','Jen restart firewallu.'],'correct'=>0,'explanation'=>'Musíš potvrdit funkčnost i zachování bezpečnostních hranic.'],
   ],
  ],
  [
   'id'=>'net_dns_dhcp_debug','number'=>9,'title'=>'Lekce 9 · DNS/DHCP II + service debugging','subtitle'=>'2 × 45 minut','goal'=>'Pochopit časové chování cache/lease a spojit DNS, TCP, TLS a HTTP do rychlé evidence-first diagnostiky služby.','knowledge'=>['dns-dhcp-operations','service-debug-chain'],
   'schedule'=>[['time'=>'0–15','title'=>'TTL'],['time'=>'15–30','title'=>'Lease'],['time'=>'30–45','title'=>'Change plan'],['time'=>'45–62','title'=>'Debug chain'],['time'=>'62–82','title'=>'Incident'],['time'=>'82–90','title'=>'Exit']],
   'steps'=>[
    ['id'=>'ttl','time'=>'15 min','title'=>'01 · DNS cache timeline','kind'=>'knowledge','knowledge'=>['dns-dhcp-operations'],'xp'=>30,'tasks'=>['Dokonči TTL & Lease Timeline Lab.','Vysvětli TTL.','Naplánuj DNS změnu.']],
    ['id'=>'lease','time'=>'15 min','title'=>'02 · DHCP lease lifecycle','kind'=>'manual','xp'=>20,'tasks'=>['Urči lease time.','Popiš renew/rebind.','Vysvětli dopad změny poolu.']],
    ['id'=>'change','time'=>'15 min','title'=>'03 · Controlled change','kind'=>'manual','xp'=>25,'tasks'=>['Sniž TTL před změnou.','Definuj validační okno.','Naplánuj návrat TTL.']],
    ['id'=>'chain','time'=>'17 min','title'=>'04 · Service debug chain','kind'=>'knowledge','knowledge'=>['service-debug-chain'],'xp'=>30,'tasks'=>['Dokonči Service Chain Lab.','Sepiš 4 krátké testy.','Urči první chybějící důkaz.']],
    ['id'=>'incident','time'=>'20 min','title'=>'05 · Incident drill','kind'=>'manual','xp'=>35,'tasks'=>['Ověř DNS.','Ověř TCP/443.','Ověř TLS.','Ověř HTTP.','Navrhni opravu a retest.']],
    ['id'=>'exit','time'=>'8 min','title'=>'06 · Exit','kind'=>'quiz','xp'=>20,'question'=>'DNS i TCP/443 fungují, ale certifikát je expirovaný. Kde je závada?','options'=>['V TLS/certifikační vrstvě.','V DHCP poolu.','V ARP cache klienta.'],'correct'=>0,'explanation'=>'Předchozí vrstvy už mají pozitivní evidence.'],
   ],
  ],
 ],
 'class_4a'=>[
  [
   'id'=>'ops_iac_drift','number'=>8,'title'=>'Lekce 8 · Infrastructure as Code + drift','subtitle'=>'2 × 45 minut','goal'=>'Řídit produkční konfiguraci jako verzovaný desired state, kontrolovat diff před změnou a bezpečně řešit configuration drift.','knowledge'=>['infrastructure-as-code','config-drift'],
   'schedule'=>[['time'=>'0–15','title'=>'Desired state'],['time'=>'15–30','title'=>'Plan'],['time'=>'30–45','title'=>'Review'],['time'=>'45–62','title'=>'Drift'],['time'=>'62–80','title'=>'Reconcile'],['time'=>'80–90','title'=>'Runbook']],
   'steps'=>[
    ['id'=>'desired','time'=>'15 min','title'=>'01 · Desired state','kind'=>'knowledge','knowledge'=>['infrastructure-as-code'],'xp'=>30,'tasks'=>['Dokonči Plan → Apply Lab.','Popiš desired state.','Urči source of truth.']],
    ['id'=>'plan','time'=>'15 min','title'=>'02 · Plan / diff','kind'=>'manual','xp'=>25,'tasks'=>['Přečti diff.','Urči blast radius.','Označ neočekávanou změnu.']],
    ['id'=>'review','time'=>'15 min','title'=>'03 · Review gate','kind'=>'manual','xp'=>25,'tasks'=>['Rozděl změnu na malý scope.','Přidej success criteria.','Připrav rollback.']],
    ['id'=>'drift','time'=>'17 min','title'=>'04 · Configuration drift','kind'=>'knowledge','knowledge'=>['config-drift'],'xp'=>30,'tasks'=>['Dokonči Drift Detection Lab.','Porovnej desired/actual.','Zjisti původ driftu.']],
    ['id'=>'reconcile','time'=>'18 min','title'=>'05 · Reconcile','kind'=>'manual','xp'=>35,'tasks'=>['Vyber source of truth.','Sjednoť runtime a repo.','Validuj po změně.']],
    ['id'=>'runbook','time'=>'10 min','title'=>'06 · IaC runbook','kind'=>'quiz','xp'=>20,'question'=>'Jaký je bezpečný default před apply?','options'=>['Zkontrolovat plan/diff, scope, rollback a success criteria.','Spustit apply přímo v produkci bez review.','Vypnout monitoring.'],'correct'=>0,'explanation'=>'IaC snižuje riziko jen tehdy, když workflow obsahuje kontrolní body.'],
   ],
  ],
  [
   'id'=>'ops_performance_capacity','number'=>9,'title'=>'Lekce 9 · Performance + capacity engineering','subtitle'=>'2 × 45 minut','goal'=>'Najít bottleneck pomocí latency/throughput/saturation a převést měření na realistický plán kapacity s headroomem.','knowledge'=>['performance-engineering','capacity-planning'],
   'schedule'=>[['time'=>'0–15','title'=>'Baseline'],['time'=>'15–30','title'=>'Percentiles'],['time'=>'30–45','title'=>'Saturation'],['time'=>'45–62','title'=>'Load test'],['time'=>'62–82','title'=>'Capacity decision'],['time'=>'82–90','title'=>'Exit']],
   'steps'=>[
    ['id'=>'baseline','time'=>'15 min','title'=>'01 · Performance baseline','kind'=>'knowledge','knowledge'=>['performance-engineering'],'xp'=>30,'tasks'=>['Dokonči Latency & Saturation Lab.','Zapiš p50/p95/error rate.','Najdi jeden resource signal.']],
    ['id'=>'percentiles','time'=>'15 min','title'=>'02 · Percentiles','kind'=>'manual','xp'=>20,'tasks'=>['Porovnej p50 a p95.','Najdi tail latency.','Vysvětli uživatelský dopad.']],
    ['id'=>'saturation','time'=>'15 min','title'=>'03 · Bottleneck','kind'=>'manual','xp'=>25,'tasks'=>['Sleduj CPU/I/O/queue.','Najdi korelaci s latency.','Formuluj hypotézu.']],
    ['id'=>'load','time'=>'17 min','title'=>'04 · Capacity lab','kind'=>'knowledge','knowledge'=>['capacity-planning'],'xp'=>30,'tasks'=>['Dokonči Capacity & Headroom Lab.','Najdi bod degradace.','Definuj cílovou zátěž.']],
    ['id'=>'decision','time'=>'20 min','title'=>'05 · Scale decision','kind'=>'manual','xp'=>35,'tasks'=>['Přidej headroom.','Porovnej scale up/out/optimize.','Otestuj failover scénář.']],
    ['id'=>'exit','time'=>'8 min','title'=>'06 · Exit','kind'=>'quiz','xp'=>20,'question'=>'Systém splňuje SLO do 700 RPS, cíl je 900 RPS a potřebuješ 25 % rezervu. Co plyne?','options'=>['Současná kapacita nestačí a je potřeba scale/optimalizace před cílovým provozem.','Stačí ignorovat headroom.','Snížit monitoring interval na nulu.'],'correct'=>0,'explanation'=>'Kapacita musí pokrýt cíl i bezpečnou rezervu.'],
   ],
  ],
 ],
];
