<?php
declare(strict_types=1);

return [
 'class_1a' => [
  [
   'id'=>'graphics_color_type_system','number'=>3,'title'=>'Lekce 3 · Barva + typografický systém','subtitle'=>'2 × 45 minut','goal'=>'Z jednoho plakátu vytvořit čitelný systém barev a typografie, který funguje v mobilním náhledu i na větším formátu.','knowledge'=>['contrast-color','typography','spacing','export'],
   'schedule'=>[['time'=>'0–15','title'=>'Kontrastní laboratoř'],['time'=>'15–30','title'=>'Typografické role'],['time'=>'30–45','title'=>'A/B skeleton'],['time'=>'45–65','title'=>'Canva varianta'],['time'=>'65–80','title'=>'QA + export'],['time'=>'80–90','title'=>'Reflexe']],
   'steps'=>[
    ['id'=>'contrast','time'=>'15 min','title'=>'01 · Kontrast jako funkce','kind'=>'knowledge','knowledge'=>['contrast-color'],'xp'=>25,'tasks'=>['Dokonči Knowledge Tour kontrastu.','Ověř dvě kombinace text/pozadí.','Vyber jednu kombinaci pro hlavní text a jednu pro akcent.']],
    ['id'=>'type_roles','time'=>'15 min','title'=>'02 · Tři role textu','kind'=>'knowledge','knowledge'=>['typography','spacing'],'xp'=>25,'tasks'=>['Definuj headline, info a CTA.','Použij maximálně dvě rodiny písem.','Nastav opakující se spacing.']],
    ['id'=>'ab','time'=>'15 min','title'=>'03 · A/B skeleton','kind'=>'manual','xp'=>20,'tasks'=>['Vytvoř dvě varianty stejného obsahu.','Varianta B musí změnit kompozici, ne text.','Vyber lepší variantu podle thumbnail testu.']],
    ['id'=>'canva','time'=>'20 min','title'=>'04 · Přenos do Canvy','kind'=>'manual','xp'=>30,'tasks'=>['Přenes vybraný skeleton.','Dodrž paletu a typografické role.','Přidej obraz pouze pokud podporuje hlavní sdělení.']],
    ['id'=>'qa','time'=>'15 min','title'=>'05 · QA + export','kind'=>'quiz','knowledge'=>['export'],'xp'=>25,'question'=>'Co je nejspolehlivější závěrečná kontrola?','options'=>['Otevřít skutečný export mimo editor a zkontrolovat ho v cílové velikosti.','Přidat ještě jeden efekt.','Zkontrolovat pouze názvy vrstev.'],'correct'=>0,'explanation'=>'Kontroluješ to, co skutečně dostane divák.','tasks'=>['Exportuj finální soubor.','Otevři jej mimo editor.']],
    ['id'=>'reflection','time'=>'10 min','title'=>'06 · Vysvětli rozhodnutí','kind'=>'manual','xp'=>20,'tasks'=>['Napiš 3 věty: barva, typografie, spacing.','Uveď jednu věc, kterou bys při další verzi změnil/a.']],
   ],
  ],
  [
   'id'=>'graphics_mini_identity','number'=>4,'title'=>'Lekce 4 · Mini vizuální identita','subtitle'=>'2 × 45 minut','goal'=>'Navrhnout malý vizuální systém pro fiktivní školní akci a aplikovat ho na plakát + sociální post.','knowledge'=>['hierarchy','composition','color','preflight'],
   'schedule'=>[['time'=>'0–12','title'=>'Brief'],['time'=>'12–28','title'=>'Mini brand kit'],['time'=>'28–45','title'=>'Master plakát'],['time'=>'45–65','title'=>'Social post'],['time'=>'65–82','title'=>'Konzistence'],['time'=>'82–90','title'=>'Preflight']],
   'steps'=>[
    ['id'=>'brief','time'=>'12 min','title'=>'01 · Brief bez šablony','kind'=>'manual','xp'=>20,'tasks'=>['Zapiš cílovou skupinu.','Zapiš hlavní sdělení.','Zapiš jednu požadovanou akci uživatele.']],
    ['id'=>'kit','time'=>'16 min','title'=>'02 · Mini brand kit','kind'=>'knowledge','knowledge'=>['hierarchy','composition','color'],'xp'=>30,'tasks'=>['Vyber 3 barvy max.','Definuj 2 typografické role.','Definuj styl CTA a obrazový princip.']],
    ['id'=>'master','time'=>'17 min','title'=>'03 · Master plakát','kind'=>'manual','xp'=>25,'tasks'=>['Postav master 1080×1350.','Ověř hierarchii a grid.','Nepoužívej více prvků, než potřebuje sdělení.']],
    ['id'=>'social','time'=>'20 min','title'=>'04 · Sociální adaptace','kind'=>'manual','xp'=>30,'tasks'=>['Převeď systém do 1080×1080.','Změň layout, ne identitu.','Zachovej CTA a hlavní sdělení.']],
    ['id'=>'consistency','time'=>'17 min','title'=>'05 · Audit konzistence','kind'=>'quiz','xp'=>25,'question'=>'Co musí být mezi formáty stejné?','options'=>['Vizuální pravidla a role prvků; přesné pozice stejné být nemusí.','Každá souřadnice prvků.','Počet řádků textu.'],'correct'=>0,'explanation'=>'Identita drží pravidly, ne kopírováním souřadnic.'],
    ['id'=>'preflight','time'=>'8 min','title'=>'06 · Preflight','kind'=>'knowledge','knowledge'=>['preflight'],'xp'=>20,'tasks'=>['Otevři oba exporty mimo editor.','Ověř rozměry a texty.','Zapiš jednu větu, co systém drží pohromadě.']],
   ],
  ],
 ],
 'class_2a' => [
  [
   'id'=>'graphics_ui_hero','number'=>3,'title'=>'Lekce 3 · UI hero sekce: grafika potkává web','subtitle'=>'2 × 45 minut','goal'=>'Převést principy plakátu do responzivní hero sekce webu a pochopit hierarchii, CTA, grid a adaptaci.','knowledge'=>['hierarchy','composition','contrast-color','responsive-adaptation'],
   'schedule'=>[['time'=>'0–12','title'=>'Rozbor hero'],['time'=>'12–28','title'=>'Desktop grid'],['time'=>'28–45','title'=>'Desktop návrh'],['time'=>'45–62','title'=>'Mobile adaptace'],['time'=>'62–80','title'=>'CTA + kontrast'],['time'=>'80–90','title'=>'Prezentace']],
   'steps'=>[
    ['id'=>'analyze','time'=>'12 min','title'=>'01 · Co musí hero sdělit','kind'=>'manual','xp'=>20,'tasks'=>['Headline jednou větou.','Doplňující text max. 2 řádky.','Jedno primární CTA.']],
    ['id'=>'grid','time'=>'16 min','title'=>'02 · Desktop grid','kind'=>'knowledge','knowledge'=>['composition'],'xp'=>25,'tasks'=>['Navrhni 12sloupcový grid.','Urči textovou a obrazovou zónu.']],
    ['id'=>'desktop','time'=>'17 min','title'=>'03 · Desktop 1440 px','kind'=>'manual','xp'=>25,'tasks'=>['Postav hero v Canvě/Figmě.','Ověř hierarchii a kontrast.']],
    ['id'=>'mobile','time'=>'17 min','title'=>'04 · Mobile 390 px','kind'=>'knowledge','knowledge'=>['responsive-adaptation'],'xp'=>30,'tasks'=>['Přeskup layout pro mobil.','Zachovej obsahové priority.']],
    ['id'=>'cta','time'=>'18 min','title'=>'05 · CTA a stav kontrastu','kind'=>'quiz','knowledge'=>['contrast-color'],'xp'=>25,'question'=>'Co je u CTA nejdůležitější?','options'=>['Aby byla rozpoznatelná jako akce a měla dostatečný kontrast.','Aby měla nejvíc efektů na stránce.','Aby byla vždy největším prvkem.'],'correct'=>0,'explanation'=>'CTA musí být jasná a čitelná, ale stále respektovat celkovou hierarchii.'],
    ['id'=>'present','time'=>'10 min','title'=>'06 · Mini case','kind'=>'manual','xp'=>20,'tasks'=>['Ukaž desktop + mobile.','Vysvětli jednu změnu layoutu a proč byla nutná.']],
   ],
  ],
  [
   'id'=>'graphics_motion_storyboard','number'=>4,'title'=>'Lekce 4 · Motion storyboard pro sociální sítě','subtitle'=>'2 × 45 minut','goal'=>'Navrhnout 4–6 snímků krátkého motion/story videa dřív, než se otevře animační software.','knowledge'=>['hierarchy','typography','responsive-adaptation','portfolio-presentation'],
   'schedule'=>[['time'=>'0–10','title'=>'Cíl a timing'],['time'=>'10–25','title'=>'Storyboard'],['time'=>'25–42','title'=>'Keyframes'],['time'=>'42–60','title'=>'Typografie v čase'],['time'=>'60–78','title'=>'Prototype'],['time'=>'78–90','title'=>'Review']],
   'steps'=>[
    ['id'=>'goal','time'=>'10 min','title'=>'01 · Jedna zpráva / 6 sekund','kind'=>'manual','xp'=>20,'tasks'=>['Definuj jedinou zprávu.','Definuj CTA.','Zvol délku 6–10 s.']],
    ['id'=>'story','time'=>'15 min','title'=>'02 · Storyboard','kind'=>'manual','xp'=>25,'tasks'=>['Nakresli 4–6 framů.','Každý frame má jediný hlavní úkol.']],
    ['id'=>'keyframes','time'=>'17 min','title'=>'03 · Keyframes','kind'=>'knowledge','knowledge'=>['hierarchy','typography'],'xp'=>25,'tasks'=>['Ověř čitelnost každého klíčového framu.','Zachovej typografické role.']],
    ['id'=>'time','time'=>'18 min','title'=>'04 · Hierarchie v čase','kind'=>'quiz','xp'=>25,'question'=>'Co znamená hierarchie v motionu?','options'=>['Řídíš nejen velikost a kontrast, ale i okamžik, kdy se informace objeví.','Vše musí být vidět od první sekundy.','Každý frame má mít jiný font.'],'correct'=>0,'explanation'=>'Čas je další osa hierarchie.'],
    ['id'=>'prototype','time'=>'18 min','title'=>'05 · Rychlý prototyp','kind'=>'manual','xp'=>30,'tasks'=>['Vytvoř jednoduchou animaci/prototyp.','Nepřidávej efekt bez funkce.','Ověř CTA na posledním framu.']],
    ['id'=>'review','time'=>'12 min','title'=>'06 · Review','kind'=>'knowledge','knowledge'=>['portfolio-presentation'],'xp'=>20,'tasks'=>['Exportuj krátké video/GIF nebo storyboard PDF.','Vysvětli timing jedné informace.']],
   ],
  ],
 ],
 'class_3a' => [
  [
   'id'=>'net_services_lab','number'=>3,'title'=>'Lekce 3 · DNS + DHCP jako služby','subtitle'=>'2 × 45 minut','goal'=>'Pochopit DNS a DHCP jako běžící služby, jejich konfiguraci, porty, logy a systematickou validaci.','knowledge'=>['dns','dhcp','ports','troubleshooting'],
   'schedule'=>[['time'=>'0–12','title'=>'Služby a porty'],['time'=>'12–28','title'=>'DHCP lease'],['time'=>'28–45','title'=>'DNS záznam'],['time'=>'45–62','title'=>'Validace klienta'],['time'=>'62–80','title'=>'Incident'],['time'=>'80–90','title'=>'Exit ticket']],
   'steps'=>[
    ['id'=>'ports','time'=>'12 min','title'=>'01 · Kdo na čem poslouchá','kind'=>'knowledge','knowledge'=>['ports'],'xp'=>25,'tasks'=>['Urči DNS port 53.','Urči DHCP UDP 67/68.','Vysvětli rozdíl mezi službou a portem.']],
    ['id'=>'dhcp','time'=>'16 min','title'=>'02 · Lease od začátku do konce','kind'=>'knowledge','knowledge'=>['dhcp'],'xp'=>25,'tasks'=>['Projdi DORA.','Urči gateway a DNS předané klientovi.']],
    ['id'=>'dns','time'=>'17 min','title'=>'03 · A záznam','kind'=>'knowledge','knowledge'=>['dns'],'xp'=>25,'tasks'=>['Vytvoř/interpretuj A záznam.','Ověř odpověď přes nslookup/dig.']],
    ['id'=>'client','time'=>'17 min','title'=>'04 · Validace z klienta','kind'=>'manual','xp'=>25,'tasks'=>['ipconfig /all nebo ip addr.','ping gateway.','nslookup jména.','TCP test služby.']],
    ['id'=>'incident','time'=>'18 min','title'=>'05 · Incident: lease ano, jméno ne','kind'=>'quiz','xp'=>35,'question'=>'Klient má platnou IP, gateway funguje, ping 1.1.1.1 funguje, ale nslookup timeout. Kde začít?','options'=>['DNS server / jeho dosažitelnost a konfigurace.','Měnit grafickou kartu.','Přeinstalovat celý OS.'],'correct'=>0,'explanation'=>'Síťová cesta funguje, problém je v překladu jmen.'],
    ['id'=>'exit','time'=>'10 min','title'=>'06 · Exit ticket','kind'=>'manual','xp'=>20,'tasks'=>['Napiš 4krokový postup diagnostiky.','U každého kroku napiš, co výsledkem dokazuješ.']],
   ],
  ],
  [
   'id'=>'net_monitor_harden','number'=>4,'title'=>'Lekce 4 · Monitoring + základní hardening','subtitle'=>'2 × 45 minut','goal'=>'Rozlišit dostupnost, zdraví služby a bezpečné minimum: monitoring, logy, firewall a účty.','knowledge'=>['service-matrix','troubleshooting','ssh-sftp'],
   'schedule'=>[['time'=>'0–12','title'=>'Co monitorovat'],['time'=>'12–28','title'=>'Health check'],['time'=>'28–45','title'=>'Logy'],['time'=>'45–62','title'=>'Firewall'],['time'=>'62–80','title'=>'SSH minimum'],['time'=>'80–90','title'=>'Incident report']],
   'steps'=>[
    ['id'=>'monitor','time'=>'12 min','title'=>'01 · Dostupnost vs. zdraví','kind'=>'knowledge','knowledge'=>['troubleshooting'],'xp'=>25,'tasks'=>['Definuj 3 metriky.','Rozliš ping od HTTP health checku.']],
    ['id'=>'health','time'=>'16 min','title'=>'02 · Health endpoint','kind'=>'manual','xp'=>25,'tasks'=>['Navrhni /health kontrolu.','Definuj expected status.']],
    ['id'=>'logs','time'=>'17 min','title'=>'03 · Čti log podle času','kind'=>'knowledge','knowledge'=>['troubleshooting'],'xp'=>25,'tasks'=>['Najdi čas incidentu.','Spoj request s chybou.']],
    ['id'=>'firewall','time'=>'17 min','title'=>'04 · Minimum firewall pravidel','kind'=>'knowledge','knowledge'=>['service-matrix'],'xp'=>30,'tasks'=>['Povol jen potřebné služby.','Ověř blokaci nepotřebného portu.']],
    ['id'=>'ssh','time'=>'18 min','title'=>'05 · SSH minimum','kind'=>'knowledge','knowledge'=>['ssh-sftp'],'xp'=>30,'tasks'=>['Použij klíč místo sdíleného hesla.','Ověř permissions klíče.']],
    ['id'=>'report','time'=>'10 min','title'=>'06 · Incident report','kind'=>'quiz','xp'=>20,'question'=>'Co patří do dobrého incident reportu?','options'=>['Symptom, důkaz, příčina, změna a ověření.','Pouze věta „už to funguje“.','Seznam všech příkazů bez závěru.'],'correct'=>0,'explanation'=>'Report má zachytit rozhodovací řetězec a ověření výsledku.'],
   ],
  ],
 ],
 'class_4a' => [
  [
   'id'=>'prod_observability','number'=>3,'title'=>'Lekce 3 · Observability: logy, metriky, health','subtitle'=>'2 × 45 minut','goal'=>'Získat jistotu v tom, jak z monitoringu a logů vytvořit důkaz místo intuice.','knowledge'=>['logs-monitoring','diagnostics','reverse-proxy','tls-certificates'],
   'schedule'=>[['time'=>'0–12','title'=>'SLI/SLO mindset'],['time'=>'12–30','title'=>'Access/error log'],['time'=>'30–45','title'=>'Health + metrics'],['time'=>'45–62','title'=>'Proxy evidence'],['time'=>'62–80','title'=>'Incident correlation'],['time'=>'80–90','title'=>'Postmortem mini']],
   'steps'=>[
    ['id'=>'signals','time'=>'12 min','title'=>'01 · Tři signály','kind'=>'knowledge','knowledge'=>['logs-monitoring'],'xp'=>25,'tasks'=>['Vyber latency, error rate a availability.','Definuj baseline.']],
    ['id'=>'logs','time'=>'18 min','title'=>'02 · Korelace logů','kind'=>'manual','xp'=>30,'tasks'=>['Najdi request v access logu.','Najdi stejný čas v error logu.','Najdi odpovídající app log.']],
    ['id'=>'health','time'=>'15 min','title'=>'03 · Health + metrics','kind'=>'manual','xp'=>25,'tasks'=>['Navrhni /health.','Navrhni 3 metriky.','Nezveřejňuj /metrics všem.']],
    ['id'=>'proxy','time'=>'17 min','title'=>'04 · Proxy evidence','kind'=>'knowledge','knowledge'=>['reverse-proxy'],'xp'=>30,'tasks'=>['Rozliš 502 od 404.','Ověř upstream lokálně.']],
    ['id'=>'incident','time'=>'18 min','title'=>'05 · Incident correlation','kind'=>'quiz','xp'=>35,'question'=>'Nginx vrací 502 a app health na upstream portu neodpovídá. Co je nejsilnější hypotéza?','options'=>['Upstream aplikace/port mezi proxy a backendem.','DNS klienta, protože HTTP odpověď už přišla.','Barva terminálu.'],'correct'=>0,'explanation'=>'502 a nefunkční upstream health ukazují mezi proxy a backend.'],
    ['id'=>'postmortem','time'=>'10 min','title'=>'06 · Mini postmortem','kind'=>'manual','xp'=>20,'tasks'=>['Co se stalo.','Jaký byl důkaz.','Jak tomu příště předejít.']],
   ],
  ],
  [
   'id'=>'prod_release_drill','number'=>4,'title'=>'Lekce 4 · Release drill: bezpečný deploy a rollback','subtitle'=>'2 × 45 minut','goal'=>'Projít release jako řízenou změnu: precheck, canary, validace, stop condition a rollback.','knowledge'=>['change-management','reverse-proxy','tls-certificates','logs-monitoring'],
   'schedule'=>[['time'=>'0–12','title'=>'Change ticket'],['time'=>'12–28','title'=>'Precheck'],['time'=>'28–45','title'=>'Canary'],['time'=>'45–62','title'=>'Validace'],['time'=>'62–80','title'=>'Failure injection'],['time'=>'80–90','title'=>'Rozhodnutí']],
   'steps'=>[
    ['id'=>'ticket','time'=>'12 min','title'=>'01 · Change ticket','kind'=>'knowledge','knowledge'=>['change-management'],'xp'=>25,'tasks'=>['Definuj scope.','Success criteria.','Rollback krok.']],
    ['id'=>'precheck','time'=>'16 min','title'=>'02 · Precheck','kind'=>'manual','xp'=>25,'tasks'=>['Config syntax.','Disk/CPU baseline.','Health aktuální verze.','Backup/rollback artefakt.']],
    ['id'=>'canary','time'=>'17 min','title'=>'03 · Canary 10 %','kind'=>'manual','xp'=>30,'tasks'=>['Navrhni 10% routing.','Definuj 3 metriky.','Definuj 2 stop conditions.']],
    ['id'=>'validate','time'=>'17 min','title'=>'04 · Validace user path','kind'=>'knowledge','knowledge'=>['reverse-proxy','tls-certificates'],'xp'=>30,'tasks'=>['DNS.','TLS.','HTTP status.','User flow.','Monitoring.']],
    ['id'=>'failure','time'=>'18 min','title'=>'05 · Failure injection','kind'=>'quiz','xp'=>35,'question'=>'Po canary error rate stoupne z 0,5 % na 12 % a stop condition je 3 %. Co uděláš?','options'=>['Zastavím rollout a vrátím canary do známého funkčního stavu.','Pokračuji na 100 %, protože už jsme začali.','Vypnu alert.'],'correct'=>0,'explanation'=>'Stop condition existuje právě proto, aby se rozhodnutí nedělalo pod tlakem intuitivně.'],
    ['id'=>'decision','time'=>'10 min','title'=>'06 · Release note','kind'=>'manual','xp'=>20,'tasks'=>['Rozhodnutí continue/rollback.','Důkaz.','Co změnit před dalším pokusem.']],
   ],
  ],
 ],
];
