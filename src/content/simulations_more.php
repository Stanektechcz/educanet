<?php

declare(strict_types=1);

return [
 'class_1a'=>[
  'responsive-series'=>['id'=>'gfx-responsive','type'=>'responsivepreview','title'=>'Responsive Preview Lab','lead'=>'Přepínej cílový formát, safe zone a zachování hierarchie. Náhled ukáže, co se při slepém ořezu ztratí.','task'=>'Připrav story variantu se zachovanou hierarchií a bezpečnou zónou.','prediction'=>['q'=>'Stačí master 1080×1350 jen oříznout na story?','options'=>['Ne, layout a crop se musí znovu posoudit.','Ano, vždy.'],'correct'=>0],'hints'=>['Přepni na story.','Nech alespoň 8 % safe zone.','Zachovej hierarchii headline → info → CTA.'],'conclusion'=>['q'=>'Co přenášíš mezi formáty?','options'=>['Systém a hierarchii.','Přesné souřadnice.'],'correct'=>0],'points'=>5],
 ],
 'class_2a'=>[
  'accessibility-design'=>['id'=>'ui-a11y','type'=>'accessibilityaudit','title'=>'Accessibility Audit Lab','lead'=>'Měň barvy, velikost a focus state. Simulace počítá kontrast a ukazuje, zda je interakce čitelná i bez ideálních podmínek.','task'=>'Nastav čitelný text, alespoň 16 px a viditelný focus ring.','prediction'=>['q'=>'Může design vypadat dobře a přesto být špatně použitelný?','options'=>['Ano.','Ne.'],'correct'=>0],'hints'=>['Zvedni kontrast nad 4,5:1.','Nastav text alespoň 16 px.','Zapni focus ring.'],'conclusion'=>['q'=>'Co je přístupný interaktivní prvek?','options'=>['Čitelný, srozumitelný a jasně ovladatelný.','Pouze barevně výrazný.'],'correct'=>0],'points'=>5],
 ],
 'class_3a'=>[
  'packet-analysis'=>['id'=>'net-packets','type'=>'packetflow','title'=>'Packet Journey Lab','lead'=>'Simuluj závadu v ARP, DNS nebo TCP a vyber filtr, který přinese nejrychlejší důkaz.','task'=>'Najdi DNS problém pomocí odpovídajícího filtru a evidence.','prediction'=>['q'=>'Je nejlepší začít capture bez filtru?','options'=>['Ne, nejdřív potřebuji otázku/hypotézu.','Ano, čím více paketů tím lépe.'],'correct'=>0],'hints'=>['Nastav závadu DNS.','Použij DNS filtr.','Hledej query bez odpovědi.'],'conclusion'=>['q'=>'Co je nejsilnější evidence DNS timeoutu?','options'=>['Query odešla, ale v očekávaném čase není response.','SYN-ACK na portu 443.','ARP reply od gateway.'],'correct'=>0],'points'=>5],
 ],
 'class_4a'=>[
  'slo-postmortem'=>['id'=>'ops-slo','type'=>'slobudget','title'=>'SLO & Error Budget Lab','lead'=>'Měň SLO, skutečnou dostupnost a rozhodnutí po incidentu. Simulace ukáže, zda má tým ještě prostor pro rizikový rollout.','task'=>'Nastav SLO 99,9 %, dostupnost 99,5 % a zvol stabilizační/postmortem režim.','prediction'=>['q'=>'Když je dostupnost pod SLO, má tým bez rozmyslu zrychlit rollout?','options'=>['Ne.','Ano.'],'correct'=>0],'hints'=>['Nastav SLO 99,9 %.','Sniž skutečnost na 99,5 %.','Zvol stabilizaci + postmortem.'],'conclusion'=>['q'=>'K čemu error budget slouží?','options'=>['Pomáhá balancovat spolehlivost a tempo změn.','Určuje cenu serveru.'],'correct'=>0],'points'=>5],
 ],
];
