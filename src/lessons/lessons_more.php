<?php

declare(strict_types=1);

return [
 'class_1a'=>[[
  'id'=>'graphics_responsive_series','number'=>7,'title'=>'Lekce 7 · Jeden design, tři formáty','subtitle'=>'2 × 45 minut','goal'=>'Převést jeden plakát do postu, square a story tak, aby zůstala hierarchie, čitelnost a vizuální identita.','knowledge'=>['responsive-series','preflight'],
  'schedule'=>[['time'=>'0–15','title'=>'Responsive princip'],['time'=>'15–30','title'=>'Master audit'],['time'=>'30–48','title'=>'Square'],['time'=>'48–66','title'=>'Story'],['time'=>'66–82','title'=>'Canva build'],['time'=>'82–90','title'=>'QA']],
  'steps'=>[
   ['id'=>'principle','time'=>'15 min','title'=>'01 · Co se mezi formáty nesmí ztratit','kind'=>'knowledge','knowledge'=>['responsive-series'],'xp'=>30,'tasks'=>['Dokonči Responsive Preview Lab.','Sepiš 3 invarianty návrhu.','Urči safe zone.']],
   ['id'=>'audit','time'=>'15 min','title'=>'02 · Audit masteru','kind'=>'manual','xp'=>20,'tasks'=>['Urči headline/info/CTA.','Ověř thumbnail.','Najdi prvek, který bude při změně formátu problém.']],
   ['id'=>'square','time'=>'18 min','title'=>'03 · Square adaptace','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř 1080×1080.','Změň crop, ne identitu.','Ověř CTA.']],
   ['id'=>'story','time'=>'18 min','title'=>'04 · Story adaptace','kind'=>'manual','xp'=>25,'tasks'=>['Vytvoř 1080×1920.','Dodrž safe zone.','Přeskup info blok.']],
   ['id'=>'build','time'=>'16 min','title'=>'05 · Canva série','kind'=>'manual','xp'=>30,'tasks'=>['Sjednoť paletu a typografii.','Exportuj 3 formáty.','Pojmenuj soubory konzistentně.']],
   ['id'=>'qa','time'=>'8 min','title'=>'06 · Multi-format QA','kind'=>'quiz','xp'=>20,'question'=>'Co je nejlepší důkaz, že série drží pohromadě?','options'=>['Stejné role, hierarchie a vizuální pravidla i při odlišném layoutu.','Úplně stejné souřadnice všech prvků.','V každém formátu jiný styl.'],'correct'=>0,'explanation'=>'Konzistence je systém vztahů, ne kopie pixelů.'],
  ],
 ]],
 'class_2a'=>[[
  'id'=>'graphics_accessibility','number'=>7,'title'=>'Lekce 7 · Accessibility & responsive UI','subtitle'=>'2 × 45 minut','goal'=>'Navrhnout jednoduchou responzivní UI sekci, která zůstává čitelná, ovladatelná a srozumitelná v různých stavech.','knowledge'=>['accessibility-design','ui-spacing-system','component-consistency'],
  'schedule'=>[['time'=>'0–15','title'=>'Accessibility audit'],['time'=>'15–30','title'=>'Contrast + focus'],['time'=>'30–48','title'=>'Desktop'],['time'=>'48–65','title'=>'Mobile'],['time'=>'65–82','title'=>'States'],['time'=>'82–90','title'=>'Review']],
  'steps'=>[
   ['id'=>'audit','time'=>'15 min','title'=>'01 · Accessibility audit','kind'=>'knowledge','knowledge'=>['accessibility-design'],'xp'=>30,'tasks'=>['Dokonči Accessibility Audit Lab.','Ověř kontrast.','Ověř focus.']],
   ['id'=>'tokens','time'=>'15 min','title'=>'02 · Čitelnost + spacing','kind'=>'knowledge','knowledge'=>['ui-spacing-system'],'xp'=>20,'tasks'=>['Nastav textové role.','Zvol spacing tokeny.','Udrž click target.']],
   ['id'=>'desktop','time'=>'18 min','title'=>'03 · Desktop komponenta','kind'=>'manual','xp'=>25,'tasks'=>['Navrhni card + CTA.','Použij komponentní pravidla.','Přidej focus state.']],
   ['id'=>'mobile','time'=>'17 min','title'=>'04 · Mobile adaptace','kind'=>'manual','xp'=>25,'tasks'=>['Přepni na 390 px.','Zachovej pořadí čtení.','Ověř text i touch targety.']],
   ['id'=>'states','time'=>'17 min','title'=>'05 · Error / disabled / focus','kind'=>'manual','xp'=>30,'tasks'=>['Navrhni 3 stavy.','Nepoužívej jen barvu.','Ověř konzistenci.']],
   ['id'=>'review','time'=>'8 min','title'=>'06 · Accessibility review','kind'=>'quiz','xp'=>20,'question'=>'Co je největší chyba u error stavu?','options'=>['Význam je sdělen pouze červenou barvou bez textu nebo ikony.','Text má dostatečný kontrast.','Focus je viditelný.'],'correct'=>0,'explanation'=>'Stav nesmí záviset jen na vnímání barvy.'],
  ],
 ]],
 'class_3a'=>[[
  'id'=>'net_packet_analysis','number'=>7,'title'=>'Lekce 7 · Packet journey + Wireshark mindset','subtitle'=>'2 × 45 minut','goal'=>'Použít packet-level evidence k rozlišení lokální, DNS, TCP a aplikační závady bez bezhlavého spouštění všech nástrojů.','knowledge'=>['packet-analysis','dns-record-types','monitoring-basics'],
  'schedule'=>[['time'=>'0–15','title'=>'Packet journey'],['time'=>'15–30','title'=>'Filtry'],['time'=>'30–47','title'=>'ARP/DNS'],['time'=>'47–64','title'=>'TCP'],['time'=>'64–82','title'=>'Incident'],['time'=>'82–90','title'=>'Exit']],
  'steps'=>[
   ['id'=>'journey','time'=>'15 min','title'=>'01 · Cesta paketu','kind'=>'knowledge','knowledge'=>['packet-analysis'],'xp'=>30,'tasks'=>['Dokonči Packet Journey Lab.','Rozliš ARP/DNS/TCP evidence.','Zapiš hypotézu.']],
   ['id'=>'filters','time'=>'15 min','title'=>'02 · Filtr není dekorace','kind'=>'manual','xp'=>20,'tasks'=>['Navrhni filtr pro DNS.','Navrhni filtr pro TCP/443.','Vysvětli, proč nechceš celý capture.']],
   ['id'=>'dns','time'=>'17 min','title'=>'03 · Query bez response','kind'=>'manual','xp'=>25,'tasks'=>['Interpretuj DNS timeout.','Odliš NXDOMAIN.','Navrhni další test.']],
   ['id'=>'tcp','time'=>'17 min','title'=>'04 · SYN, SYN-ACK, RST','kind'=>'manual','xp'=>25,'tasks'=>['Rozliš timeout vs. reject.','Urči otevřený port.','Vysvětli RST.']],
   ['id'=>'incident','time'=>'18 min','title'=>'05 · Evidence-first incident','kind'=>'manual','xp'=>35,'tasks'=>['Vyber 3 nejkratší testy.','Urči root cause.','Navrhni validaci po opravě.']],
   ['id'=>'exit','time'=>'8 min','title'=>'06 · Exit ticket','kind'=>'quiz','xp'=>20,'question'=>'Co znamená SYN → RST?','options'=>['Cíl odpovídá, ale spojení/port odmítá.','DNS query timeout.','Klient nemá gateway.'],'correct'=>0,'explanation'=>'RST je aktivní TCP odpověď a odlišuje se od tichého timeoutu.'],
  ],
 ]],
 'class_4a'=>[[
  'id'=>'ops_slo_postmortem','number'=>7,'title'=>'Lekce 7 · SLO, error budget a postmortem','subtitle'=>'2 × 45 minut','goal'=>'Převést provozní data na rozhodnutí: kdy pokračovat v release, kdy stabilizovat a jak z incidentu vytvořit konkrétní follow-up.','knowledge'=>['slo-postmortem','http-observability','canary-release'],
  'schedule'=>[['time'=>'0–15','title'=>'SLI/SLO'],['time'=>'15–30','title'=>'Error budget'],['time'=>'30–47','title'=>'Incident impact'],['time'=>'47–64','title'=>'Timeline'],['time'=>'64–82','title'=>'Postmortem'],['time'=>'82–90','title'=>'Decision gate']],
  'steps'=>[
   ['id'=>'slo','time'=>'15 min','title'=>'01 · SLI → SLO','kind'=>'knowledge','knowledge'=>['slo-postmortem'],'xp'=>30,'tasks'=>['Dokonči SLO Lab.','Vyber user-facing SLI.','Nastav 30denní SLO.']],
   ['id'=>'budget','time'=>'15 min','title'=>'02 · Error budget','kind'=>'manual','xp'=>25,'tasks'=>['Spočítej tolerovaný čas.','Porovnej s incidentem.','Rozhodni, zda je budget vyčerpán.']],
   ['id'=>'impact','time'=>'17 min','title'=>'03 · Dopad není jen uptime','kind'=>'knowledge','knowledge'=>['http-observability'],'xp'=>25,'tasks'=>['Urči počet chybných requestů.','Urči délku dopadu.','Urči uživatelský symptom.']],
   ['id'=>'timeline','time'=>'17 min','title'=>'04 · Evidence timeline','kind'=>'manual','xp'=>25,'tasks'=>['Seřaď alert, deploy, symptom, rollback.','Odděl fakta od domněnek.','Označ decision point.']],
   ['id'=>'postmortem','time'=>'18 min','title'=>'05 · Blameless postmortem','kind'=>'manual','xp'=>35,'tasks'=>['Root cause.','Contributing factors.','3 follow-up akce s vlastníkem a termínem.']],
   ['id'=>'gate','time'=>'8 min','title'=>'06 · Release gate','kind'=>'quiz','xp'=>20,'question'=>'Error budget je vyčerpaný a nový release není urgentní. Jaký je nejlepší default?','options'=>['Zpomalit rizikové změny a nejdřív obnovit spolehlivost.','Přidat traffic na canary na 100 %.','Vypnout SLO alert.'],'correct'=>0,'explanation'=>'Error budget má ovlivnit tempo změn a dát prostor stabilizaci.'],
  ],
 ]],
];
